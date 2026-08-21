FROM richarvey/nginx-php-fpm:latest

USER root

# 1. Cài đặt Node.js phiên bản mới nhất (Node 22 LTS) từ NodeSource
RUN apk add --no-cache curl && \
    curl -fsSL https://deb.nodesource.com/setup_22.x | sh - || \
    # Nếu lệnh trên lỗi trên Alpine, ta dùng cách cài thủ công từ binary:
    apk add --no-cache nodejs npm

# Cập nhật npm lên bản mới nhất
RUN npm install -g npm@latest

# Copy source code
COPY . .

# Image config
ENV SKIP_COMPOSER 1
ENV WEBROOT /var/www/html/public
ENV PHP_ERRORS_STDERR 1
ENV RUN_SCRIPTS 1
ENV REAL_IP_HEADER 1

# Laravel config
ENV APP_ENV production
ENV APP_DEBUG false
ENV LOG_CHANNEL stderr

# Allow composer to run as root
ENV COMPOSER_ALLOW_SUPERUSER 1

# Đảm bảo quyền truy cập thư mục (thường gặp lỗi permissions trong Laravel)
RUN chown -R www:www /var/www/html

CMD ["/start.sh"]