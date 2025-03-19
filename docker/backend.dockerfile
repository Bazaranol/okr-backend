FROM php:8.1-apache

# Устанавливаем необходимые пакеты для PostgreSQL
RUN apt-get update && apt-get install -y libpq-dev && docker-php-ext-install pdo pdo_pgsql pgsql

# Настроим Apache, если нужно
RUN a2enmod rewrite

# Копируем проект в контейнер
COPY . /var/www/html

RUN find /var/www/html -type f -exec chmod 644 {} \; && \
    find /var/www/html -type d -exec chmod 755 {} \;

RUN chmod -R 755 /var/www/html

# Настроим рабочую директорию
WORKDIR /var/www/html

# Установим composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Открываем порты
EXPOSE 80
