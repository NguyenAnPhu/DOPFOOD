#!/bin/sh

# Thoát ngay lập tức nếu có lệnh nào bị lỗi
set -e

# Tối ưu hóa Laravel (Dọn dẹp và tạo cache mới)
echo "Optimizing Laravel..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Chạy migration database (tự động chạy mà không cần hỏi xác nhận)
echo "Running migrations..."
php artisan migrate --force

# Bật PHP-FPM ở chế độ chạy ngầm (background)
echo "Starting PHP-FPM..."
php-fpm -D

# Khởi chạy Nginx ở chế độ chạy chính (foreground) để giữ container luôn hoạt động
echo "Starting Nginx..."
nginx -g "daemon off;"
