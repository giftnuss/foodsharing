#!/bin/bash
cd /var/www/lmr-prod/www/
php run.php maintenance membackup
service memcached restart
php run.php maintenance memrestore

