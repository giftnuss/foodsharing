from php:7

RUN apt-get update

RUN apt-get install -y apt-utils

RUN apt-get install -y \
  libfreetype6-dev \
  libjpeg62-turbo-dev \
  libmcrypt-dev \
  libpng12-dev \
  libcurl4-gnutls-dev

RUN docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/
RUN docker-php-ext-install -j$(nproc) iconv mcrypt mysqli gd curl zip
RUN pecl install redis
RUN docker-php-ext-enable redis

WORKDIR /app
COPY php.ini /usr/local/etc/php/

EXPOSE 9000

CMD ["php", "-S", "0.0.0.0:9000"]
