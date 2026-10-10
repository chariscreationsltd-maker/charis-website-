-- 05 · CharisOS: "Rate your team member" ratings from the website (Our Team page)
-- Run once in Supabase: SQL Editor > New query > paste > Run. Safe to run again.
--
-- The website sends each rating through its own server (never straight from
-- the visitor's browser), with a one-way fingerprint of the visitor so the same
-- person can rate a team member only once a day. Comments are private: only
-- staff in CharisOS can read them. The public only ever gets the average and
-- the number of ratings per team member.

create table if not exists public.team_ratings (
  id           bigserial primary key,
  member       text not null check (char_length(member) between 2 and 80),
  rating       smallint not null check (rating between 1 and 5),
  comment      text not null default '' check (char_length(comment) <= 1000),
  fingerprint  text not null default '' check (char_length(fingerprint) <= 64),
  source       text not null default 'website',
  created_at   timestamptz not null default now()
);
create index if not exists team_ratings_member_idx on public.team_ratings (member, created_at desc);
create index if not exists team_ratings_fp_idx on public.team_ratings (fingerprint, member, created_at desc);

alter table public.team_ratings enable row level security;
revoke all on public.team_ratings from anon, authenticated;

-- Staff (Owner/Admin/PA) can read ratings and comments inside CharisOS.
drop policy if exists "team_ratings_staff_read" on public.team_ratings;
create policy "team_ratings_staff_read" on public.team_ratings for select to authenticated using (public.is_admin());
grant select on public.team_ratings to authenticated;

-- Save one rating. Called by the website server with the publishable key.
create or replace function public.submit_team_rating(p_member text, p_rating int, p_comment text default '', p_fingerprint text default '')
returns jsonb
language plpgsql
volatile
security definer
set search_path = public
as $$
declare
  v_member text := left(trim(coalesce(p_member, '')), 80);
  v_comment text := left(trim(coalesce(p_comment, '')), 1000);
  v_fp text := left(trim(coalesce(p_fingerprint, '')), 64);
begin
  if char_length(v_member) < 2 then
    return jsonb_build_object('success', false, 'error', 'member');
  end if;
  if p_rating is null or p_rating < 1 or p_rating > 5 then
    return jsonb_build_object('success', false, 'error', 'rating');
  end if;
  -- Once a day per visitor per team member.
  if v_fp <> '' and exists (
    select 1 from team_ratings where fingerprint = v_fp and member = v_member and created_at > now() - interval '24 hours'
  ) then
    return jsonb_build_object('success', false, 'error', 'already_rated');
  end if;
  -- Overall brake: at most 30 ratings a day from one visitor.
  if v_fp <> '' and (select count(*) from team_ratings where fingerprint = v_fp and created_at > now() - interval '24 hours') >= 30 then
    return jsonb_build_object('success', false, 'error', 'limit');
  end if;

  insert into team_ratings (member, rating, comment, fingerprint) values (v_member, p_rating, v_comment, v_fp);
  return jsonb_build_object('success', true);
end;
$$;

-- Public summary: average and count per team member (no comments).
create or replace function public.get_team_ratings()
returns jsonb
language sql
stable
security definer
set search_path = public
as $$
  select jsonb_build_object(
    'success', true,
    'ratings', coalesce(jsonb_object_agg(member, jsonb_build_object('avg', avg_rating, 'count', n)), '{}'::jsonb)
  )
  from (
    select member, round(avg(rating)::numeric, 1) as avg_rating, count(*) as n
    from team_ratings
    group by member
  ) s;
$$;

revoke all on function public.submit_team_rating(text, int, text, text) from public;
revoke all on function public.get_team_ratings() from public;
grant execute on function public.submit_team_rating(text, int, text, text) to anon, authenticated;
grant execute on function public.get_team_ratings() to anon, authenticated;
notify pgrst, 'reload schema';

-- Check: should show {"success": true, "ratings": {}}
select public.get_team_ratings();
