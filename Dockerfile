# Stage 1: Cài đặt PHP Dependencies qua Composer
FROM composer:2.7 AS vendor

WORKDIR /app

COPY database/ database/
COPY composer.json composer.json
COPY composer.lock composer.lock

# Cài đặt gói PHP tối ưu cho môi trường Production
RUN composer install \
    --ignore-platform-reqs \
    --no-interaction \
    --no-plugins \
    --no-scripts \
    --prefer-dist

# Stage 2: Xây dựng môi trường chạy ứng dụng chính
FROM php:8.2-fpm-alpine

# Cài đặt Nginx và các extension PHP cần thiết cho Laravel
RUN apk add --no-cache \
    nginx \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip \
    git \
    curl \
    oniguruma-dev \
    $PHPIZE_DEPS \
    && docker-php-ext-install pmbstring exif pcntl bcmath gd pdo_mysql

# Dọn dẹp bộ nhớ đệm apk để giảm dung lượng
RUN rm -rf /var/cache/apk/*

# Copy cấu hình Nginx vào container
COPY nginx.conf /etc/nginx/http.d/default.conf

# Thiết lập thư mục làm việc bên trong container
WORKDIR /var/www/html

# Copy toàn bộ mã nguồn của bạn vào container
COPY . .

# Copy thư mục vendor từ Stage 1 sang Stage 2
COPY --from=vendor /app/vendor/ ./vendor/

# Cấp quyền ghi cho các thư mục lưu trữ cache của Laravel
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Mở cổng mạng 80 để tiếp nhận truy cập
EXPOSE 80

# Chạy file script entrypoint khi container khởi động
ENTRYPOINT ["/var/www/html/entrypoint.sh"]
