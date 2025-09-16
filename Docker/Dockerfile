FROM php:8.4-fpm

# Install necessary packages for PHP extensions, Nginx, and Supervisor
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    zlib1g-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libicu-dev \
    libcurl4-openssl-dev \
    nginx \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions you may need (pdo_mysql, zip, etc.)
RUN docker-php-ext-install pdo pdo_mysql zip bcmath intl

# Install Xdebug via PECL
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

# Install Composer globally
RUN curl -sS https://getcomposer.org/installer | php -- \
    --install-dir=/usr/local/bin --filename=composer

# Add a non-root user matching the host's UID/GID to prevent permission issues.
ARG UID=1000
ARG GID=1000
RUN groupadd -g $GID dockeruser \
    && useradd -u $UID -g $GID -m dockeruser \
    && mkdir -p /var/www/html \
    && chown -R dockeruser:dockeruser /var/www/html

# Copy the custom nginx config to the container
COPY ./Docker/nginx/default.conf /etc/nginx/sites-available/default

# Set up directories for logs
RUN mkdir -p /var/log/nginx \
    && chown -R dockeruser:dockeruser /var/log/nginx /var/lib/nginx

# Ensure Nginx runs as non root
RUN sed -i -e '/^user /d' \
           -e 's|pid /run/nginx.pid|pid /tmp/nginx.pid|' \
           -e 's|error_log /var/log/nginx/error.log|error_log /tmp/error.log|' \
           /etc/nginx/nginx.conf

# Set working directory for PHP-FPM and Nginx (default directories)
WORKDIR /var/www/html

# Switch to the dockeruser so files are created with proper permissions
USER dockeruser

# Expose port 80 for Nginx
EXPOSE 80 9000

#CMD php-fpm -D && nginx -g 'daemon off;'
CMD ["/bin/sh", "-c", "php-fpm -D && nginx -g 'daemon off;'"]