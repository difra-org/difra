#!/bin/bash

mkdir branches
mkdir branches/current
mkdir branches/current/bin
mkdir branches/current/externals
mkdir branches/current/lib
mkdir branches/current/plugins
mkdir branches/current/sites
mkdir tags
mkdir trunk
ln -s ../externals/base/lib/sys branches/current/lib/sys

svn add branches tags trunk
svn ci -m 'Directory tree automatically created'
cd branches/current/externals

cat > .tmp.ext << EOF
base		https://svn.cybergaia.net/base
base-plugins	https://svn.cybergaia.net/base-plugins
EOF
svn propset svn:externals . -F .tmp.ext
rm .tmp.ext

svn ci -m 'Base framework external created'
cd ..
svn up
svn export externals/base/sites/default sites/default
cd sites/default/templates
rm -rf common
ln -s ../../../externals/base/sites/default/templates/common
cd ../htdocs/js
rm -rf common
ln -s ../../../../externals/base/sites/default/templates/js/common
cd ../../../..
svn add sites/default
svn ci -m 'Created default site'

