FROM registry.gitlab.com/foodsharing-dev/images:php

WORKDIR /app

COPY composer.json .

RUN composer install
