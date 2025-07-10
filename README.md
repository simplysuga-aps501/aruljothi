To start with this project execute the below commands. 

git clone git@github-aps:simplysuga-aps501/aruljothi.git aruljothi

cd aruljothi/aruljo

cp .env.example .env

composer install --no-dev --optimize-autoloader

php artisan key:generate

chmod -R 775 ../aruljo/storage ../aruljo/bootstrap/cache

php -S 127.0.0.1:8000 -t public_html 
