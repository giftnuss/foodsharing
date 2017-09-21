#!/bin/sh
git fetch gitlab && git checkout -f gitlab/master && composer install --no-dev
./generate-revision.sh
