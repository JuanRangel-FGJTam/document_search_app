FROM php:8.2-alpine

RUN docker-php-ext-install pdo pdo_mysql

WORKDIR /var/www/html

CMD ["php", "artisan", "schedule:work"]
