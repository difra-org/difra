#!/bin/bash

#
# This software cannot be used, distributed or modified, completely or partially, without written permission by copyright holder.
#
# Copyright © A-Jam Studio
# License: http://ajamstudio.com/difra/license
#

BASEPATH=`dirname $0`/../..
PLUGINS=$BASEPATH/plugins

echo "SET FOREIGN_KEY_CHECKS = 0;"

for plugin in `ls $PLUGINS`; do
	name="$PLUGINS/$plugin/bin/db.sql";
	if [ -f $name ]; then
		cat $name
		echo ""
	fi
done

name="$BASEPATH/bin/db.sql";
if [ -f $name ]; then
	cat $name
	echo ""
fi

echo "SET FOREIGN_KEY_CHECKS = 1;"
