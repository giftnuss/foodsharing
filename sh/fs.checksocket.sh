#!/bin/sh

# check if running
if ( ps aux |grep "[p]hp run.php mails" )
then
	echo "Socket is running..."
else
	echo "Socket NOT running! Restarting..."
	cd /var/www/lmr-prod/www
	php run.php mails > /var/www/lmr-prod/log/fs_mails_socket.log
fi

exit 0 