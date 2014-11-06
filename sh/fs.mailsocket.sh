#!/bin/bash
cd /var/www/lmr-prod/www
php run.php mails stop > /var/www/lmr-prod/log/fs_mails_socket.log
sleep 3
kill $(ps aux | grep '[p]hp run.php mails' | awk '{print $2}')
php run.php mails > /var/www/lmr-prod/log/fs_mails_socket.log
