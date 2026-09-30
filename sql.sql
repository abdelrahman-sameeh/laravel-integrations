use laravel_integrations;


select * from users;


update users
set google_id=NULL
where id=3;



delete from users
where id=3;
