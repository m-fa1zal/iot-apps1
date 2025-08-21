web: php artisan serve --host=0.0.0.0 --port=$PORT
mqtt: php artisan mqtt:listen
worker: php artisan queue:work --sleep=3 --tries=3 --max-time=3600