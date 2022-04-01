FROM webdevops/php:8.0-alpine

LABEL maintainer="Andre Breier <breier.de@gmail.com>" version="1.0"

ENV WEB_DOCUMENT_ROOT=/app \
    APPLICATION_PATH=/app \
    php.variables_order=EGPCS \
    php.short_open_tag=On

COPY --chown=application:application ./hello-world /app

RUN su application -c '(cd /app && composer install --no-dev)'

WORKDIR /app

ENTRYPOINT [ "/app/bin/console", "lambda:serve" ]
