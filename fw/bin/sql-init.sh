#!/bin/bash

BASEPATH=`dirname $0`/../..
PLUGINS=$BASEPATH/plugins

for plugin in `ls $PLUGINS`; do
	name="$PLUGINS/$plugin/bin/db.sql";
	if [ -f $name ]; then
		cat $name
		echo ""
	fi
done

