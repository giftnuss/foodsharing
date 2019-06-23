#!/bin/bash
cd /var/www/production/current/
FS_ENV=prod php run.php Maintenance daily
