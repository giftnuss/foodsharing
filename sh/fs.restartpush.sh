#!/bin/bash
kill $(ps aux | grep '[p]hp run.php push' | awk '{print $2}')
cd /var/www/lmr-prod/www/
php run.php push
