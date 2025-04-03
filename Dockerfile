FROM php:8.2-cli

# ติดตั้ง MySQL driver ถ้าใช้ฐานข้อมูล
RUN docker-php-ext-install pdo pdo_mysql

WORKDIR /app
COPY . /app

EXPOSE 10000

CMD ["php", "-S", "0.0.0.0:10000", "-t", "."]
