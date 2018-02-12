#!/bin/bash
cd /var/www/lmr-prod/www/
php run.php Maintenance hourly > /var/www/lmr-prod/log/fs_maintenance_hourly.log
