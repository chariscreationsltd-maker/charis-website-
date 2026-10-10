-- 02 · CharisOS: website editor (chariscreationsltd.com/editor/)
-- Run once in Supabase: SQL Editor > New query > paste > Run. Safe to run again.
--
-- What it adds
--   site_content           one row per website page: the owner's published changes
--   site_content_history   every publish, so any earlier version can be restored
--   site-media bucket      public photo store for images uploaded in the editor
--   site_editor_allowed()  who may publish: CharisOS profiles with app_role Owner
--   get_site_content()     public: what the website lays over its built-in text and photos
--   site_editor_me()       signed-in: name, role and whether this account may publish
--   save_site_content()    Owner: publish a page's changes
--   list_site_content_history(), restore_site_content()   Owner: undo
--
-- How the data looks
--   site_content.data is a flat object of "path": value pairs, e.g.
--     { "hero.headline": "Stories told beautifully",
--       "gallery.items.2.photo": "https://<project>.supabase.co/storage/v1/object/public/site-media/projects/abc_1600x1067-1600.webp",
--       "galleries.wedding.photos": ["https://…-1600.webp", "…"] }
--   The website ignores any path that isn't already in its own content files,
--   and rejects script links and images from anywhere but this bucket.
--
-- Nothing here touches existing CharisOS tables. The two new tables have RLS on
-- and no policies: they are reached only through the functions below.

-- ── Who may publish ───────────────────────────────────────────
-- Owner only (as run by CharisOS, October 2026).
create or replace function public.site_editor_allowed()
returns boolean
language sql
stable
security definer
set search_path = public
as $$
  select exists (
    select 1 from profiles
    where id = auth.uid()
      and app_role = 'Owner'
  );
$$;

-- ── Tables ────────────────────────────────────────────────────
create table if not exists public.site_content (
  page        text primary key check (page in ('site', 'home', 'projects', 'galleries', 'services')),
  data        jsonb not null default '{}'::jsonb check (jsonb_typeof(data) = 'object'),
  updated_at  timestamptz not null default now(),
  updated_by  uuid references auth.users(id) on delete set null
);

create table if not exists public.site_content_history (
  id             bigserial primary key,
  page           text not null,
  data           jsonb not null,
  saved_at       timestamptz not null default now(),
  saved_by       uuid references auth.users(id) on delete set null,
  saved_by_name  text not null default ''
);
create index if not exists site_content_history_page_idx on public.site_content_history (page, id desc);

alter table public.site_content         enable row level security;
alter table public.site_content_history enable row level security;
revoke all on public.site_content, public.site_content_history from anon, authenticated;

-- ── Public read (the website, every 5 minutes at most) ────────
create or replace function public.get_site_content()
returns json
language sql
stable
security definer
set search_path = public
as $$
  select json_build_object(
    'success',    true,
    'pages',      coalesce((select json_object_agg(page, data) from site_content), '{}'::json),
    'updated_at', (select max(updated_at) from site_content)
  );
$$;

-- ── Who am I (the editor shows this after sign-in) ────────────
create or replace function public.site_editor_me()
returns json
language sql
stable
security definer
set search_path = public
as $$
  select json_build_object(
    'signed_in', auth.uid() is not null,
    'name',      coalesce((select name from profiles where id = auth.uid()), ''),
    'role',      coalesce((select app_role from profiles where id = auth.uid()), ''),
    'can_edit',  public.site_editor_allowed()
  );
$$;

-- ── Publish ───────────────────────────────────────────────────
create or replace function public.save_site_content(p_page text, p_data jsonb)
returns json
language plpgsql
volatile
security definer
set search_path = public
as $$
declare
  v_name text;
  v_bad  text;
begin
  if not public.site_editor_allowed() then
    raise exception 'Only the Owner account can change the website.' using errcode = '42501';
  end if;
  if p_page not in ('site', 'home', 'projects', 'galleries', 'services') then
    raise exception 'Unknown page: %', p_page using errcode = '22023';
  end if;
  if p_data is null or jsonb_typeof(p_data) <> 'object' then
    raise exception 'Changes must be an object of path: value pairs.' using errcode = '22023';
  end if;
  if octet_length(p_data::text) > 400000 then
    raise exception 'Too much content in one publish.' using errcode = '22023';
  end if;
  select k into v_bad from jsonb_object_keys(p_data) k where k !~ '^[A-Za-z0-9][A-Za-z0-9_.]{0,199}$' limit 1;
  if v_bad is not null then
    raise exception 'Not a valid field: %', v_bad using errcode = '22023';
  end if;

  select name into v_name from profiles where id = auth.uid();

  insert into site_content (page, data, updated_at, updated_by)
  values (p_page, p_data, now(), auth.uid())
  on conflict (page) do update
    set data = excluded.data, updated_at = excluded.updated_at, updated_by = excluded.updated_by;

  insert into site_content_history (page, data, saved_by, saved_by_name)
  values (p_page, p_data, auth.uid(), coalesce(v_name, ''));

  -- Keep the 50 most recent versions per page.
  delete from site_content_history h
  where h.page = p_page
    and h.id not in (select id from site_content_history where page = p_page order by id desc limit 50);

  return json_build_object('success', true, 'page', p_page, 'fields', (select count(*) from jsonb_object_keys(p_data)));
end;
$$;

-- ── History and restore ───────────────────────────────────────
create or replace function public.list_site_content_history(p_page text)
returns json
language plpgsql
stable
security definer
set search_path = public
as $$
begin
  if not public.site_editor_allowed() then
    raise exception 'Only the Owner account can see website history.' using errcode = '42501';
  end if;
  return coalesce((
    select json_agg(json_build_object(
             'id', h.id, 'saved_at', h.saved_at, 'saved_by_name', h.saved_by_name,
             'changes', (select count(*) from jsonb_object_keys(h.data))
           ) order by h.id desc)
    from (select * from site_content_history where page = p_page order by id desc limit 30) h
  ), '[]'::json);
end;
$$;

create or replace function public.restore_site_content(p_id bigint)
returns json
language plpgsql
volatile
security definer
set search_path = public
as $$
declare
  v_row site_content_history%rowtype;
begin
  if not public.site_editor_allowed() then
    raise exception 'Only the Owner account can change the website.' using errcode = '42501';
  end if;
  select * into v_row from site_content_history where id = p_id;
  if not found then
    raise exception 'That version no longer exists.' using errcode = '22023';
  end if;
  return public.save_site_content(v_row.page, v_row.data);
end;
$$;

revoke all on function public.site_editor_allowed()                 from public;
revoke all on function public.get_site_content()                    from public;
revoke all on function public.site_editor_me()                      from public;
revoke all on function public.save_site_content(text, jsonb)        from public;
revoke all on function public.list_site_content_history(text)       from public;
revoke all on function public.restore_site_content(bigint)          from public;
-- Supabase grants new functions to anon by default; take that back for the editor-only ones.
revoke all on function public.site_editor_allowed()                 from anon;
revoke all on function public.site_editor_me()                      from anon;
revoke all on function public.save_site_content(text, jsonb)        from anon;
revoke all on function public.list_site_content_history(text)       from anon;
revoke all on function public.restore_site_content(bigint)          from anon;
grant execute on function public.get_site_content()                 to anon, authenticated;
grant execute on function public.site_editor_allowed()              to authenticated;
grant execute on function public.site_editor_me()                   to authenticated;
grant execute on function public.save_site_content(text, jsonb)     to authenticated;
grant execute on function public.list_site_content_history(text)    to authenticated;
grant execute on function public.restore_site_content(bigint)       to authenticated;

-- ── Photo store ───────────────────────────────────────────────
-- Public bucket: anyone can view a photo by its address (the website needs
-- that); only Owner/Admin can add, replace or delete. 8 MB per file is far
-- above what the editor uploads (it resizes to 1600px WebP in the browser).
insert into storage.buckets (id, name, public, file_size_limit, allowed_mime_types)
values ('site-media', 'site-media', true, 8388608, array['image/webp', 'image/jpeg', 'image/png'])
on conflict (id) do update
  set public = true, file_size_limit = excluded.file_size_limit, allowed_mime_types = excluded.allowed_mime_types;

drop policy if exists "site_media_insert" on storage.objects;
drop policy if exists "site_media_update" on storage.objects;
drop policy if exists "site_media_delete" on storage.objects;
drop policy if exists "site_media_select" on storage.objects;
create policy "site_media_select" on storage.objects for select to authenticated
  using (bucket_id = 'site-media' and public.site_editor_allowed());
create policy "site_media_insert" on storage.objects for insert to authenticated
  with check (bucket_id = 'site-media' and public.site_editor_allowed());
create policy "site_media_update" on storage.objects for update to authenticated
  using (bucket_id = 'site-media' and public.site_editor_allowed())
  with check (bucket_id = 'site-media' and public.site_editor_allowed());
create policy "site_media_delete" on storage.objects for delete to authenticated
  using (bucket_id = 'site-media' and public.site_editor_allowed());

-- Make the new functions visible to the API straight away.
notify pgrst, 'reload schema';
