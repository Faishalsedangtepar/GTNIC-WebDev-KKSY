FROM node:22-alpine AS assets
WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY . .
RUN npm run build

FROM serversideup/php:8.3-fpm-nginx
ENV AUTORUN_ENABLED=true
COPY --chown=www-data:www-data . /var/www/html
RUN mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
RUN composer install --no-dev --optimize-autoloader --no-interaction
COPY --from=assets --chown=www-data:www-data /app/public/build /var/www/html/public/build