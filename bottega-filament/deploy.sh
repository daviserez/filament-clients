git pull origin main
composer install --optimize-autoloader --no-dev --no-interaction
php artisan config:cache
php artisan view:cache
php artisan filament:assets
php artisan migrate

# Installation
# php artisan key:generate

# Run migrations & seed data
# php artisan db:seed

# cp .env.example .env
