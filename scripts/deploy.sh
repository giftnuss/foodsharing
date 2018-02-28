#!/bin/sh
git fetch gitlab && git checkout -f gitlab/master && composer install --no-dev
sudo -u www-data rm -rf tmp/.views-cache tmp/di-cache.php
./scripts/generate-revision.sh
