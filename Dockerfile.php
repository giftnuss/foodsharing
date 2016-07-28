from php:5.6-fpm

RUN apt-get update

RUN apt-get install -y apt-utils git

RUN apt-get install -y \
  libfreetype6-dev \
  libjpeg62-turbo-dev \
  libmcrypt-dev \
  libpng12-dev \
  libcurl4-gnutls-dev

RUN docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/
RUN docker-php-ext-install -j$(nproc) iconv mcrypt mysqli gd curl zip

# phpredis

# pecl version only supports newer php versions
#RUN pecl install redis
#RUN docker-php-ext-enable redis

RUN curl -L -o \
  /tmp/phpredis.tar.gz \
  https://github.com/phpredis/phpredis/archive/2.2.7.tar.gz

RUN tar xf /tmp/phpredis.tar.gz -C /tmp
RUN mkdir -p /usr/src/php/ext
RUN mv /tmp/phpredis-2.2.7 /usr/src/php/ext/redis
RUN echo redis >> /usr/src/php-available-exts
RUN docker-php-ext-install redis

RUN rm /tmp/phpredis.tar.gz

RUN \
  curl -sS https://getcomposer.org/installer | \
  php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /app

COPY composer.json .
COPY composer.lock .

RUN composer install

RUN apt-get install -y vim

RUN rm /usr/local/etc/php-fpm.d/*

COPY docker-conf/php/php.ini /usr/local/etc/php/
COPY docker-conf/php/fpm.conf /usr/local/etc/php-fpm.d/fpm.conf

