#!/bin/sh

php artisan migrate --force
php artisan db:seed --class=HacerAdminSeeder --force

apache2-foreground
