FROM registry.gitlab.com/foodsharing-dev/images:php

WORKDIR /app

COPY composer.json .
COPY composer.lock .

RUN composer install
