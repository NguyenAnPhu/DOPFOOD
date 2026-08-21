FROM node:22-alpine AS frontend-builder
WORKDIR /app
COPY package*.json ./
RUN npm install
COPY . .
RUN npm run build

FROM serversideup/php:8.3-fpm-nginx

USER root

WORKDIR /var/www/html

COPY . .

COPY --from=frontend-builder /app/public/build /var/www/html/public/build

RUN composer install --no-dev --optimize-autoloader --no-interaction

RUN chown -R webapp:webapp /var/www/html/storage /var/www/html/bootstrap/cache

USER webapp