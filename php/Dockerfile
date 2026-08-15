FROM php:8.2-fpm

## Copy composer.lock and composer.json into the working directory
#COPY ../src/composer.lock ../src/composer.json /var/www/html/

# Set working directory
WORKDIR /var/www/html/
# Install dependencies for the operating system software
RUN echo "Acquire::Check-Valid-Until \"false\";\nAcquire::Check-Date \"false\";" | cat > /etc/apt/apt.conf.d/10no--check-valid-until
RUN apt-get update && apt-get install -y \
    build-essential \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libldap2-dev \
    locales \
    zip \
    jpegoptim optipng pngquant gifsicle \
    vim \
    libzip-dev \
    unzip \
    git \
    libonig-dev \
    curl

RUN apt-get clean && rm -rf /var/lib/apt/lists/*

RUN apt-get update && apt-get install -y wget \
    && wget http://ftp.debian.org/debian/pool/main/liba/libaio/libaio1_0.3.112-9_amd64.deb \
    && apt-get install -y ./libaio1_0.3.112-9_amd64.deb \
    && rm -rf ./libaio1_0.3.112-9_amd64.deb \
    && rm -rf /var/lib/apt/lists/*

# Install extensions for php
RUN docker-php-ext-install pdo_mysql mbstring zip exif pcntl
RUN docker-php-ext-install zip
RUN docker-php-ext-configure gd
RUN docker-php-ext-install gd
RUN pecl install -o -f redis && docker-php-ext-enable redis
RUN echo 'vm.overcommit_memory = 1' >> /etc/sysctl.conf

RUN docker-php-ext-configure gd --enable-gd --with-freetype --with-jpeg
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*


# Install composer (php package manager)
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copy existing application directory contents to the working directory
COPY ./src /var/www/html

## Assign permissions of the working directory to the www-data user
RUN chown -R www-data:www-data /var/www/html/
RUN chmod -R 775 /var/www/html/bootstrap
RUN chmod -R 777 /var/www/html/storage

# Expose port 9000 and start php-fpm server (for FastCGI Process Manager)
EXPOSE 9000
CMD ["php-fpm"]
