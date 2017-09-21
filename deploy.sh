#!/bin/sh
git fetch gitlab && git checkout -f gitlab/master && composer install --no-dev
REV=$((git rev-list HEAD --max-count=1))
echo "<?php" > revision.inc.php
echo "define('SRC_REVISION', '"$REV"');" >> revision.inc.php
