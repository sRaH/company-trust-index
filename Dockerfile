# syntax=docker/dockerfile:1.7

FROM --platform=$BUILDPLATFORM node:22-bookworm-slim AS assets

WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY assets ./assets
COPY vite.config.js ./
RUN npm run build

FROM --platform=$BUILDPLATFORM composer:latest AS vendor

WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --no-scripts \
    --prefer-dist

FROM webdevops/php-nginx:8.5 AS runtime

ENV APP_ENV=prod \
    APP_DEBUG=0 \
    WEB_DOCUMENT_ROOT=/app/public

WORKDIR /app
COPY .env ./.env
COPY --from=vendor /app/vendor ./vendor
COPY bin ./bin
COPY --from=vendor /app/composer.json ./composer.json
COPY --from=vendor /app/composer.lock ./composer.lock
COPY config ./config
COPY migrations ./migrations
COPY public ./public
COPY src ./src
COPY templates ./templates
COPY translations ./translations
COPY --from=assets /app/public/build ./public/build

RUN mkdir -p var/cache var/log \
    && rm public/index_test.php \
    && chown -R application:application var public

EXPOSE 80

HEALTHCHECK --interval=30s --timeout=5s --start-period=20s --retries=3 \
    CMD curl --fail http://127.0.0.1/ || exit 1
