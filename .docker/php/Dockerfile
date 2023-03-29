FROM php:8.2-fpm-alpine

RUN set -ex; \
	docker-php-ext-install -j "$(nproc)" \
		fileinfo \
	;

USER 1000

EXPOSE 9000
CMD ["php-fpm"]
