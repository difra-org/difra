#!/bin/bash

TYPES="php|xsl|js|css"
BASEPATH=`dirname $0`/../..

for file in `find $BASEPATH -type f|grep -v .svn|egrep -i "*.($TYPES)\$"` ; do
	svn propset svn:eol-style LF $file
done

