#!/bin/bash
cd /var/www/lmr-prod/www/
php run.php maintenance daily > /var/www/lmr-prod/log/fs_maintenance_daily.log
