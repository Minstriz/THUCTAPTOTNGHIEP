# Base image PHP 8.2
FROM php:8.2-cli

# Cài đặt các extension Laravel cần
RUN apt-get update && apt-get install -y \
    unzip git curl libpng-dev libjpeg-dev libfreetype6-dev libonig-dev libxml2-dev zip \
    && docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd

# Cài Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Đặt thư mục làm việc
WORKDIR /app

# Copy toàn bộ mã nguồn vào container
COPY . .

# Cài dependency của Laravel
RUN composer install --no-dev --optimize-autoloader

# Tạo key ứng dụng (bỏ qua lỗi nếu có)
RUN php artisan key:generate || true

# Cache cấu hình và route
RUN php artisan config:cache || true && php artisan route:cache || true

# Mở port 10000 (Render yêu cầu)
EXPOSE 10000

# Lệnh chạy server
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=10000"]
