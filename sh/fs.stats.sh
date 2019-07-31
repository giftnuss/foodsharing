#!/bin/bash
cd /var/www/production/current/
FS_ENV=prod php run.php Stats bezirke
FS_ENV=prod php run.php Stats betriebe
FS_ENV=prod php run.php Stats foodsaver
