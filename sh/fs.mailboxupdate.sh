#!/bin/bash
cd /home/deploy/production-deploy/current/
FS_ENV=prod php run.php Mails mailboxupdate
