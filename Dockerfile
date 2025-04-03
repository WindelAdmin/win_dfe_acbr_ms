FROM php:8.4-apache

LABEL maintainer="ti@projetoacbr.com.br" \
      version="1.0" \
      description="Imagem PHP Apache com suporte a Laravel e ACBrLib"

WORKDIR /var/www/html

RUN apt-get update && apt-get install -y --no-install-recommends \
    unzip curl git libxml2-dev libonig-dev libssl-dev libpq-dev libzip-dev libffi-dev vim subversion \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install pdo pdo_pgsql bcmath zip ffi mbstring xml

RUN curl -sS https://getcomposer.org/installer | php \
    && mv composer.phar /usr/local/bin/composer

RUN a2enmod rewrite

COPY php.ini /usr/local/etc/php/
COPY openssl-legacy.cnf /etc/ssl/
ENV OPENSSL_CONF=/etc/ssl/openssl-legacy.cnf

RUN echo "ServerName windfeacbrms-development.up.railway.app" >> /etc/apache2/apache2.conf
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf && \
    sed -i 's|Directory /var/www/html|Directory /var/www/html/public|g' /etc/apache2/apache2.conf

COPY --chown=www-data:www-data . /var/www/html

RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

USER www-data
RUN composer install --no-dev --optimize-autoloader

RUN php artisan config:cache && \
    php artisan route:cache && \
    php artisan view:cache

RUN php artisan migrate --force || true

USER root
EXPOSE 80
CMD ["apache2-foreground"]
