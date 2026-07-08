# Local development container for the legacy main site (website_root),
# used to exercise the login -> MyCurriculum session handoff end to end.
FROM php:8.2-apache

RUN docker-php-ext-install pdo_mysql mysqli \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && a2enmod rewrite

# The legacy code reads and writes headers late; keep output buffering on
RUN echo "output_buffering=4096" > /usr/local/etc/php/conf.d/apex-legacy.ini \
    && echo "error_reporting=E_ALL & ~E_DEPRECATED & ~E_NOTICE & ~E_WARNING" >> /usr/local/etc/php/conf.d/apex-legacy.ini \
    && echo "display_errors=Off" >> /usr/local/etc/php/conf.d/apex-legacy.ini \
    && echo "log_errors=On" >> /usr/local/etc/php/conf.d/apex-legacy.ini
