FROM php:8.5-apache

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

RUN apt-get update \
    && apt-get install -y --no-install-recommends $PHPIZE_DEPS libonig-dev git unzip \
    && docker-php-ext-install pdo_mysql mbstring \
    && apt-get purge -y --auto-remove $PHPIZE_DEPS libonig-dev \
    && rm -rf /var/lib/apt/lists/* \
    && a2enmod rewrite \
    && sed -ri -e "s!/var/www/html!${APACHE_DOCUMENT_ROOT}!g" /etc/apache2/sites-available/*.conf \
    && sed -ri -e "s!/var/www/!${APACHE_DOCUMENT_ROOT}!g" /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf \
    && sed -ri -e 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

WORKDIR /var/www/html

COPY docker/php/start-app.sh /usr/local/bin/start-app
COPY . .

RUN chmod +x /usr/local/bin/start-app
