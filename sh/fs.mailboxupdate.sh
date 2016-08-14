#!/bin/bash
cd /var/www/lmr-prod/www
FS_ENV=prod php run.php mails mailboxupdate > /var/www/lmr-prod/log/fs_mails_mailboxupdate.log
