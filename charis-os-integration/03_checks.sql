-- 03 · Checks to run after 01 and 02. Read-only: changes nothing.
-- Run each block in Supabase SQL Editor and compare with the expected result.

-- A. Every function the website calls exists and anon can run the public ones.
--    Expected: 8 rows. anon_can_run is true for get_public_testimonials,
--    get_site_content and submit_website_lead, false for the rest.
select p.proname as function,
       pg_get_function_identity_arguments(p.oid) as args,
       has_function_privilege('anon', p.oid, 'execute') as anon_can_run,
       has_function_privilege('authenticated', p.oid, 'execute') as signed_in_can_run,
       p.prosecdef as security_definer
from pg_proc p join pg_namespace n on n.oid = p.pronamespace
where n.nspname = 'public'
  and p.proname in ('get_public_testimonials', 'submit_website_lead', 'get_site_content', 'site_editor_me',
                    'site_editor_allowed', 'save_site_content', 'list_site_content_history', 'restore_site_content')
order by 1;

-- B. Who can publish the website. Expected: the owner's account (and any
--    other Owner/Admin). If the owner is missing, fix their role in CharisOS
--    or with: update profiles set app_role = 'Owner' where id = '<their auth user id>';
select p.name, u.email, p.app_role
from profiles p join auth.users u on u.id = p.id
where p.app_role in ('Owner', 'Admin')
order by p.app_role, p.name;

-- C. The photo bucket. Expected: one row, public = true.
select id, public, file_size_limit, allowed_mime_types from storage.buckets where id = 'site-media';

-- D. Photo bucket rules. Expected: 4 rows (select, insert, update, delete).
select policyname, cmd from pg_policies
where schemaname = 'storage' and tablename = 'objects' and policyname like 'site_media_%'
order by 1;

-- E. Nobody can read the editor tables directly (RLS on, no policies).
--    Expected: both rows rls_on = true, policies = 0.
select c.relname as table, c.relrowsecurity as rls_on,
       (select count(*) from pg_policies x where x.tablename = c.relname) as policies
from pg_class c join pg_namespace n on n.oid = c.relnamespace
where n.nspname = 'public' and c.relname in ('site_content', 'site_content_history');

-- F. What the website will receive. Expected: {"success": true, "pages": {...}}
select public.get_site_content();
select public.get_public_testimonials(6);

-- G. Put submit_website_lead into the CharisOS repo. It is live (the contact
--    widget uses it) but its SQL is not in the repo. Copy the output into a
--    file such as sql/submit_website_lead.sql and commit it.
select pg_get_functiondef(p.oid)
from pg_proc p join pg_namespace n on n.oid = p.pronamespace
where n.nspname = 'public' and p.proname = 'submit_website_lead';
