FROM php:8.2-fpm-alpine

RUN set -ex; \
	docker-php-ext-install -j "$(nproc)" \
		fileinfo \
	;

COPY --from=composer /usr/bin/composer /usr/bin/composer

USER 1000

RUN composer install
