FROM php:php:8.2-fpm

WORKDIR /app

COPY package*.json ./

RUN npm install

COPY . . 

RUN composer install 

RUN npm run build

CMD php bin/console server:start

