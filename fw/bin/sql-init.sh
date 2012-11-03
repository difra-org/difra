#!/bin/bash

BASEPATH=`dirname $0`/../..

sql=`$BASEPATH/fw/bin/sql-getall.sh`

sites=`ls $BASEPATH/sites`
echo This script will delete ALL database data!
echo
echo "Server:   `hostname`"
echo "Path:     `cd $BASEPATH;pwd`"
echo "Projects: $sites"
echo
read -p "Are you sure? Type YES to continue: "
if [[ ! $REPLY =~ ^[Yy][Ee][Ss]$ ]] ; then
	echo Cancelled.
	exit 1
fi

for site in $BASEPATH/sites/*; do
	if [ -f $site/config.php ]; then
		db=`echo "<?php
			\\$config = include( '$site/config.php' );
			echo \\$config['db']['username'];
			echo ' ';
			echo \\$config['db']['password'];
			echo ' ';
			echo \\$config['db']['database'];" | php`
		user=`echo $db|awk '{print \$1}'`
		pass=`echo $db|awk '{print \$2}'`
		base=`echo $db|awk '{print \$3}'`

		tables=`mysql -u$user -p$pass $base -e 'show tables'|awk '{ print $1}'|grep -v '^Tables'`
		for t in $tables; do
			mysql -u$user -p$pass $base -e "SET FOREIGN_KEY_CHECKS = 0;drop table $t"
		done

		mysql -u$user -p$pass $base -e "$sql"
	else
		echo "WARNING: Can't find $site/config.php"
	fi
done

echo Done.
