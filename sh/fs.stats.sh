#!/bin/bash
cd /var/www/lmr-prod/www/
FS_ENV=prod php run.php stats bezirke > /var/www/lmr-prod/log/fs_stats_bezirke.log
FS_ENV=prod php run.php stats betriebe > /var/www/lmr-prod/log/fs_stats_betriebe.log
FS_ENV=prod php run.php stats foodsaver > /var/www/lmr-prod/log/fs_stats_foodsaver.log
