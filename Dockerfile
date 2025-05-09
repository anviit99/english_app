FROM php:8.2

# Cài các extension Laravel cần
RUN apt-get update && apt-get install -y \
    unzip \
    git \
    curl \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql mbstring gd xml

# Cài composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Laravel project nằm trong base, nên WORKDIR là đây
WORKDIR /var/www/html

COPY . .

# Cài packages không bao gồm dev
RUN composer install --no-dev --optimize-autoloader

# Xóa cache cũ và tạo key + cache mới
RUN php artisan config:clear \
    && php artisan route:clear \
    && php artisan view:clear \
    && php artisan key:generate --force \
    && php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache

# Cấp quyền cho Laravel ghi file
RUN chown -R www-data:www-data storage bootstrap/cache

EXPOSE 8000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
