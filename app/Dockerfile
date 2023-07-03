FROM php:8.2-cli
RUN apt-get update && apt-get install -y \
    git \
    zip \
    unzip \
    libzip-dev \
    wget \
    && docker-php-ext-install zip
RUN wget https://raw.githubusercontent.com/composer/getcomposer.org/76a7060ccb93902cd7576b67264ad91c8a2700e2/web/installer -O - -q | php -- -quiet \
    && mv composer.phar /usr/local/bin/composer
COPY . /app
WORKDIR /app
# CMD ["php", "/app/main.php", "input.json"] # コマンドの例
