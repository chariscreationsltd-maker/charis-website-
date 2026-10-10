-- Fix: get_public_testimonials (website review cards)
-- Reads ONLY approved reviews from the clients table. Returns name, comment,
-- rating, eventType, date and photo (null for now). Phone, email and notes
-- never leave CharisOS.

drop function if exists public.get_public_testimonials(integer);

create function public.get_public_testimonials(p_limit integer default 6)
returns jsonb
language sql
stable
security definer
set search_path = public
as $$
  select jsonb_build_object(
    'success', true,
    'testimonials', coalesce(jsonb_agg(t), '[]'::jsonb)
  )
  from (
    select
      c.name,
      c.review_text                 as comment,
      c.review_rating               as rating,
      c.review_event_type           as "eventType",
      nullif(c.review_date::text, '') as date,
      null::text                    as photo
    from clients c
    where c.review_approved is true
      and coalesce(trim(c.review_text), '') <> ''
    order by nullif(c.review_date::text, '') desc nulls last, c.id desc
    limit least(greatest(coalesce(p_limit, 6), 1), 12)
  ) t;
$$;

revoke all on function public.get_public_testimonials(integer) from public;
grant execute on function public.get_public_testimonials(integer) to anon, authenticated;
notify pgrst, 'reload schema';

-- Check: should show {"success": true, "testimonials": [...]}
select public.get_public_testimonials(6);
