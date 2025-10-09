# Gunakan base image FrankenPHP dengan PHP 8.3 (Debian bookworm untuk stabilitas)
FROM dunglas/frankenphp:1-php8.3-bookworm

WORKDIR /app

# Install dependensi dasar untuk unixodbc dan Microsoft ODBC Driver
RUN apt-get update && apt-get install -y --no-install-recommends \
    gnupg2 \
    apt-transport-https \
    ca-certificates \
    curl \
    unixodbc \
    unixodbc-dev \
    libgssapi-krb5-2 \
    zlib1g-dev \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Periksa versi Debian untuk memastikan kompatibilitas (9, 10, 11, 12)
RUN /bin/bash -c 'if ! [[ "9 10 11 12" == *"$(grep VERSION_ID /etc/os-release | cut -d "\"" -f 2 | cut -d "." -f 1)"* ]]; then \
        echo "Debian $(grep VERSION_ID /etc/os-release | cut -d "\"" -f 2 | cut -d "." -f 1) is not currently supported."; \
        exit 1; \
    fi'

# Download dan install Microsoft repository package untuk Debian
RUN curl -sSL -O https://packages.microsoft.com/config/debian/12/packages-microsoft-prod.deb \
    && dpkg -i packages-microsoft-prod.deb \
    && rm packages-microsoft-prod.deb \
    && apt-get update \
    && ACCEPT_EULA=Y apt-get install -y --no-install-recommends msodbcsql18 mssql-tools18 \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Tambahkan path mssql-tools18 ke lingkungan (untuk sqlcmd/bcp)
ENV PATH="$PATH:/opt/mssql-tools18/bin"

# Install ekstensi PHP standar via install-php-extensions
RUN install-php-extensions \
    apcu \
    bcmath \
    brotli \
    curl \
    dba \
    dom \
    exif \
    fileinfo \
    gd \
    iconv \
    intl \
    mbstring \
    mysqli \
    mysqlnd \
    opcache \
    openssl \
    pcntl \
    pdo \
    pdo_mysql \
    pdo_pgsql \
    pdo_sqlite \
    pgsql \
    posix \
    readline \
    redis \
    sockets \
    sodium \
    sqlite3 \
    tokenizer \
    xml \
    xmlreader \
    xmlwriter \
    zip \
    zlib \
    zstd

# Install ekstensi PECL manual (xlswriter, sqlsrv, pdo_sqlsrv)
RUN pecl install xlswriter sqlsrv pdo_sqlsrv \
    && docker-php-ext-enable xlswriter sqlsrv pdo_sqlsrv \
    && { \
        echo "extension=xlswriter.so"; \
        echo "extension=sqlsrv.so"; \
        echo "extension=pdo_sqlsrv.so"; \
    } > /usr/local/etc/php/conf.d/docker-php-ext-pecl.ini

# Install Composer
RUN curl -sSL https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copy kode Laravel ke /app (untuk production; gunakan volume untuk dev)
# Add user for laravel application
RUN groupadd -g 1000 www
RUN useradd -u 1000 -ms /bin/bash -g www www

# Copy existing application directory contents
RUN chown -R www:www /app

# Salin file aplikasi (ini akan dilewati saat menggunakan volume, tapi bagus untuk build production)
COPY --chown=www:www . /app

# Change current user to www
USER www

# Install Composer dependencies (untuk production: --no-dev; untuk dev: hilangkan)
# Install dependensi Composer
# Hapus vendor dulu untuk memastikan instalasi bersih saat build
RUN rm -rf vendor && composer install --no-dev --optimize-autoloader

RUN cp .env.example .env

# Generate key dan optimize (opsional, bisa di-run manual)
# RUN php artisan key:generate --force && php artisan config:cache && php artisan route:cache && php artisan view:cache
RUN php artisan key:generate --force

# Expose ports untuk HTTP, HTTPS, dan HTTP/3
# EXPOSE 80 443 443/udp
EXPOSE 3000

# Konfigurasi FrankenPHP untuk Laravel tanpa Octane
# ENV FRANKENPHP_CONFIG="worker ./public/index.php"

# Jalankan FrankenPHP langsung
CMD ["frankenphp", "php-server", "-r", "/app/public", "--listen", ":3000"]