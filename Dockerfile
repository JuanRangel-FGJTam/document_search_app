FROM php:8.2-fpm

# Arguments defined in docker-compose.yml
ARG user
ARG uid

# Install system dependencies
RUN apt-get update && apt-get install -y \
    apt-utils \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    iputils-ping \
    pkg-config \
    libssl-dev \
    gnupg2 

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd zip

RUN curl -fsSL https://deb.nodesource.com/setup_22.x | bash -
# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN apt-get update
# Install NodeJS
RUN apt-get install -y nodejs

RUN chmod +x /home

# Create system user to run Composer and Artisan Commands
RUN useradd -G www-data,root -u $uid -d /home/$user $user
RUN mkdir -p /home/$user/.composer && \
    chown -R $user:$user /home/$user

# Set working directory
WORKDIR /var/www/html

USER $user
