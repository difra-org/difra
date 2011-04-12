#!/bin/bash

mkdir bin
mkdir externals
mkdir lib
mkdir plugins
mkdir sites
ln -s ../externals/difra/lib/sys lib/sys

svn add bin externals lib plugins sites
svn ci -m 'Created directory tree'
cd externals

cat > .tmp.ext << EOF
difra		https://svn.cybergaia.net/difra/trunk
difra-plugins	https://svn.cybergaia.net/difra-plugins
EOF
svn propset svn:externals . -F .tmp.ext
rm .tmp.ext

svn ci -m 'Created Difra externals'
cd ..
svn up
svn export externals/difra/sites/default sites/default
cd sites/default/templates
rm -rf common
ln -s ../../../externals/difra/sites/default/templates/common
cd ../htdocs/js
rm -rf common
ln -s ../../../../externals/difra/sites/default/htdocs/js/common
cd ../css
rm -rf adm.css
ln -s ../../../../externals/difra/sites/default/htdocs/css/adm.css
cd ../../../..
svn add sites/default
svn ci -m 'Created default site'

