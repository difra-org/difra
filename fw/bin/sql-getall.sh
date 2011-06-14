#!/bin/bash

BASEPATH=`dirname $0`/../..
PLUGINS=$BASEPATH/plugins

name="$BASEPATH/bin/db.sql";
if [ -f $name ]; then
	cat $name
	echo ""
fi

echo "SET FOREIGN_KEY_CHECKS = 0;"
for plugin in `ls $PLUGINS`; do
	name="$PLUGINS/$plugin/bin/db.sql";
	if [ -f $name ]; then
		cat $name
		echo ""
	fi
done
echo "SET FOREIGN_KEY_CHECKS = 1;"
