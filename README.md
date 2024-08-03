# Bible Craw

Bible Craw là một dự án PHP để thu thập và quản lý dữ liệu Kinh Thánh từ  
```sh
    https://www.bible.com/
```

## Yêu cầu hệ thống

- PHP >= 8.1
- Composer

## Cài đặt

1. Clone repository:

    ```sh
    git clone https://github.com/pyhteam/Bible-Craw.git
    cd Bible-Craw
    ```

2. Cài đặt các phụ thuộc bằng Composer:

    ```sh
    composer install
    ```

## Cấu trúc thư mục

- `api/`: Chứa các script PHP để xây dựng và lấy dữ liệu Kinh Thánh.
- `data/`: Chứa dữ liệu Kinh Thánh đã thu thập.
- `pages/`: Chứa các trang PHP để hiển thị và thu thập dữ liệu Kinh Thánh.
- `vendor/`: Chứa các thư viện bên thứ ba được cài đặt bởi Composer.

## Sử dụng

### Chạy dự án

Để chạy dự án, bạn có thể sử dụng PHP built-in server:

```sh
php -S localhost:8000
