#!/bin/bash
cd /var/www/lmr-prod/www/
php run.php stats bezirke > /var/www/lmr-prod/log/fs_stats_bezirke.log
php run.php stats betriebe > /var/www/lmr-prod/log/fs_stats_betriebe.log
php run.php stats foodsaver > /var/www/lmr-prod/log/fs_stats_foodsaver.log
