-- 01 · CharisOS: public testimonials for the website and testimonials-widget.html
-- Run once in Supabase: SQL Editor > New query > paste > Run.
--
-- Returns ONLY approved reviews and ONLY these fields:
-- name, comment, rating, eventType, date. Phone numbers, emails and notes
-- never leave the clients table. The clients table itself stays staff-only.

create or replace function public.get_public_testimonials(p_limit int default 6)
returns json
language sql
stable
security definer
set search_path = public
as $$
  select json_build_object(
    'success', true,
    'testimonials', coalesce(json_agg(t), '[]'::json)
  )
  from (
    select
      c.name,
      c.review_text            as comment,
      c.review_rating          as rating,
      c.review_event_type      as "eventType",
      c.review_date::text      as date
    from clients c
    where c.review_approved is true
      and coalesce(trim(c.review_text), '') <> ''
    order by nullif(c.review_date::text, '') desc nulls last, c.id desc
    limit least(greatest(coalesce(p_limit, 6), 1), 12)
  ) t;
$$;

revoke all on function public.get_public_testimonials(int) from public;
grant execute on function public.get_public_testimonials(int) to anon, authenticated;
