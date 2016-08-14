#!/bin/bash
cd /var/www/lmr-prod/www/
FS_ENV=prod php run.php maintenance daily > /var/www/lmr-prod/log/fs_maintenance_daily.log
