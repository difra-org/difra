#!/bin/bash

TYPES="php|xsl|js|css"
BASEPATH=`dirname $0`/../..

for file in `find -type f|grep -v .svn|egrep -i "*.($TYPES)\$"` ; do
	svn propset svn:eol-style LF $file
done

#for file in `find|grep -v .svn` ; do
#	svn propdel svn:mergeinfo $file
#done

