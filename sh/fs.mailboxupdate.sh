#!/bin/bash
cd /var/www/lmr-prod/www
php run.php mails mailboxupdate > /var/www/lmr-prod/log/fs_mails_mailboxupdate.log
