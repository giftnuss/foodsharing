#!/bin/sh
# check if running
processes=$( ps aux |grep -e "[p]hp run.php mails[ ]*$" )
if [ -z "$processes" ]
then
	echo "Socket NOT running! Restarting..."
	cd /var/www/lmr-prod/www
	php run.php mails > /var/www/lmr-prod/log/fs_mails_socket.log
fi

exit 0

