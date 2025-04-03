FROM php:8.2-fpm

ARG user
ARG uid

# Install system dependencies
RUN apt-get update && apt-get install -y \
    curl \
    ca-certificates \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libcurl4-openssl-dev \
    pkg-config \
    zip \
    unzip \
    libssl-dev \
    gnupg2 && \
    apt-get clean && rm -rf /var/lib/apt/lists/*

# Prepare repos
RUN curl -fsSL https://packages.microsoft.com/keys/microsoft.asc | gpg --dearmor -o /etc/apt/keyrings/microsoft.gpg
RUN echo "deb [arch=amd64, signed-by=/etc/apt/keyrings/microsoft.gpg] https://packages.microsoft.com/debian/12/prod bookworm main" | tee /etc/apt/sources.list.d/mssql-release.list

# Update and install necessary packages for mssql driver
RUN apt-get update && apt-get install -y --no-install-recommends unixodbc-dev unixodbc \
    && sed -i 's,^\(MinProtocol[ ]*=\).*,\1'TLSv1.0',g' /etc/ssl/openssl.cnf \
    && sed -i 's,^\(CipherString[ ]*=\).*,\1'DEFAULT@SECLEVEL=1',g' /etc/ssl/openssl.cnf

# Install MS ODBC Driver for SQL Server - Debian 12
RUN ACCEPT_EULA=Y apt-get -y --no-install-recommends install msodbcsql18 \
    && pecl install sqlsrv-5.12.0 \
    && pecl install pdo_sqlsrv-5.12.0 \
    && echo "extension=pdo_sqlsrv.so" >> `php --ini | grep "Scan for additional .ini files" | sed -e "s|.*:\s*||"`/30-pdo_sqlsrv.ini \
    && echo "extension=sqlsrv.so" >> `php --ini | grep "Scan for additional .ini files" | sed -e "s|.*:\s*||"`/30-sqlsrv.ini \
    && apt clean; rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/*

RUN docker-php-ext-install pdo pdo_mysql

# Create system user to run Composer and Artisan Commands
RUN useradd -l -G www-data,root -u $uid -d /home/$user $user && \
    mkdir -p /home/$user/.composer && chown -R $user:$user /home/$user

WORKDIR /var/www/html

RUN chown -R $user:www-data /var/www/html

USER $user

CMD ["php", "artisan", "schedule:work"]
