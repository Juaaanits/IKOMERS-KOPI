FROM php:8.2-apache

# Enable mysqli extension for MariaDB/MySQL.
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli

# Copy app into the container (use volumes in compose for live dev).
WORKDIR /var/www/html
COPY . .

# Apache runs on 80 inside the container.
EXPOSE 80
