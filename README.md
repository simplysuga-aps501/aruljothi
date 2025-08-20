To start with this project execute the below commands. 

git clone git@github-aps:simplysuga-aps501/aruljothi.git aruljothi

cd aruljothi/aruljo

cp .env.example .env

composer install --no-dev --optimize-autoloader

php artisan key:generate

chmod -R 775 ../aruljo/storage ../aruljo/bootstrap/cache

php -S 127.0.0.1:8000 -t public_html 


-- How to assign roles via tinker
php artisan tinker

$user = App\Models\User::where(‘name’,’Kavin’)->first();

$user->assignRole(‘owner’);


--Commands to run backup 
For DB alone:
php artisan backup:run --only-db

For full backup:
php artisan backup:run


