<?php

/**
 * This software cannot be used, distributed or modified, completely or partially, without written permission by copyright holder.
 *
 * @copyright © A-Jam Studio
 * @license   http://ajamstudio.com/difra/license
 */

$src = __DIR__ . '/../..';
$tmp = __DIR__ . '/../../build';
$target = __DIR__ . '/../../release';

// clean
echo 'Cleaning.' . PHP_EOL;
system( "rm -rf $tmp $target" );

// create build environment
echo 'Copying files.' . PHP_EOL;
system( "mkdir $tmp" );
system( "mkdir $target" );
system( "mkdir $target/conf" );
system( "mkdir $target/data" );
system( "mkdir $target/htdocs" );

// copy files to tmp
system( "cp -af $src/fw $tmp" );
system( "if [ -e $src/config.php ]; then cp -af $src/config.php $tmp; fi" );
system( "if [ -e $src/controllers ]; then cp -af $src/controllers $tmp; fi" );
system( "if [ -e $src/css ]; then cp -af $src/css $tmp; fi" );
system( "if [ -e $src/js ]; then cp -af $src/js $tmp; fi" );
system( "if [ -e $src/lib ]; then cp -af $src/lib $tmp; fi" );
system( "if [ -e $src/locale ]; then cp -af $src/locale $tmp; fi" );
system( "if [ -e $src/menu ]; then cp -af $src/menu $tmp; fi" );
system( "if [ -e $src/plugins ]; then cp -af $src/plugins $tmp; fi" );
system( "if [ -e $src/sites ]; then cp -af $src/sites $tmp; fi" );
system( "if [ -e $src/xslt ]; then cp -af $src/xslt $tmp; fi" );

// copy files to target
system( "if [ -e $src/bin/nginx.conf ]; then cp -af $src/bin/nginx.conf $target/conf/nginx.s.inc; fi" );
system( "if [ -e $src/htdocs ]; then cp -af $src/htdocs/* $target/htdocs/; fi" );

// move some files from tmp to target
system( "mv $tmp/fw/bin/nginx.conf $target/conf/nginx.d.inc" );
system( "cp -af $tmp/fw/htdocs/* $target/htdocs/" );

// delete some files in tmp
system( "rm -rf $tmp/fw/bin $tmp/fw/doc $tmp/fw/lib/encode" );

// delete .svn folders
system( "find $tmp/ -name .svn|xargs rm -rf" );
system( "find $target/ -name .svn|xargs rm -rf" );

echo 'Building PHP heap.' . PHP_EOL;

// collect files for encoding
$include = array(
	'fw/lib'
);
$exclude = array(
	'fw/lib/libs/esapi',
	'fw/lib/libs/less',
	'fw/lib/encode',
	'fw/lib/bootstrap.php'
);
$files = array();
$findFiles = function( $dir ) {

	global $files, $exclude, $findFiles, $tmp;

	if( in_array( $dir, $exclude ) ) {
		return;
	}
	$scan = scandir( "$tmp/$dir" );
	foreach( $scan as $file ) {
		if( $file == '.' or $file == '..' ) {
			continue;
		}
		if( is_dir( "$tmp/$dir/$file" ) ) {
			$findFiles( "$dir/$file" );
			continue;
		}
		if( substr( $file, -4 ) != '.php' ) {
			continue;
		}
		$files[] = "$dir/$file";
	}
};
foreach( $include as $dir ) {
	$findFiles( $dir );
}

$clean = function ( $code ) {

	$tokens = token_get_all( $code );
	$ret = "";
	unset( $tokens[0] ); // remove '<?php'
	foreach( $tokens as $token ) {
		if( is_string( $token ) ) {
			$ret .= $token;
		} else {
			list( $id, $text ) = $token;

			switch( $id ) {
			case T_COMMENT:
			case T_DOC_COMMENT:
				break;

			default:
				$ret .= $text;
				break;
			}
		}
	}
	return $ret;
};

//$mainphp = '';
$phpfs = array();
// gather all those php in one file
foreach( $files as $file ) {
	$txt = file_get_contents( "$tmp/$file" );
	if( substr( $txt, 0, 5 ) != '<?php' ) {
		die( 'Error: PHP file does not start with \'<?php\': ' . $file . PHP_EOL );
	}
//	$mainphp .= $txt;
	$phpfs[$file] = $clean( $txt );
	unlink( "$tmp/$file" );
}

$encode = function ( $code ) {

	$uuk = convert_uuencode( $code );
	$uuk = str_replace( "\nM", "\0", $uuk );
	$uuk = strrev( $uuk );
	$uuk = gzdeflate( $uuk );
	$uuk = strrev( $uuk );
	return $uuk . hex2bin( sha1( $uuk ) );
};
file_put_contents( $tmp . '/fw/lib/libs/capcha/Simple.ttf', $encode( var_export( $phpfs, true ) ) );
foreach( $include as $dir ) {
	system( "find $tmp/fw/lib -type d | xargs rmdir -p --ignore-fail-on-non-empty" );
}

// generate loader
$loader = include( "$src/fw/lib/encode/obfuscate.php" );
file_put_contents( "$tmp/fw/lib/data.php", $loader );

// build phar
echo "Generating phar." . PHP_EOL;
$difra = new Phar( "$target/site.phar" );
$difra->buildFromDirectory( $tmp ); // old regex: '/^\.\/(fw|plugins)\/*/'
$difra->setStub( '<?php include("phar://".($_=__FILE__)."/fw/lib/data.php");__HALT_COMPILER();?>' );