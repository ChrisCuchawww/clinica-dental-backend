#!/bin/sh

php artisan migrate --force

apache2-foreground
