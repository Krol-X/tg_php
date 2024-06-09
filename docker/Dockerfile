FROM php:8.2.0-fpm-alpine

# Install nginx
RUN apk add --no-cache nginx

# Configure non-root user
ARG PUID=1000
ARG PGID=1000
RUN apk --no-cache add shadow && \
    groupmod -o -g ${PGID} www-data && \
    usermod -o -u ${PUID} -g www-data www-data

# Copy source code and nginx config
COPY ./ /var/www/
COPY ./docker/nginx.conf /etc/nginx/nginx.conf

# Set working directory
WORKDIR /var/www

# Expose port 8080 for Nginx
EXPOSE 8080

# Start Nginx and PHP-FPM
CMD nginx && php-fpm