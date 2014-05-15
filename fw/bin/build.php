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
system( "rm -rf $tmp/fw/bin $tmp/fw/doc" );

echo 'Building PHP heap.' . PHP_EOL;

// collect files for encoding
$include = array(
	$tmp . '/fw/lib'
);
$exclude = array(
	$tmp . '/fw/lib/libs/esapi',
	$tmp . '/fw/lib/libs/less',
	$tmp . '/fw/lib/encode'
);
$files = array();
$findFiles = function( $dir ) {
	global $files, $exclude, $findFiles;

	if( in_array( $dir, $exclude ) ) {
		return;
	}
	$scan = scandir( $dir );
	foreach( $scan as $file ) {
		if( $file == '.' or $file == '..' ) {
			continue;
		}
		if( is_dir( $dir . '/' . $file ) ) {
			$findFiles( $dir . '/' . $file );
			continue;
		}
		if( substr( $file, -4 ) != '.php' ) {
			continue;
		}
		$files[] = $dir . '/' . $file;
	}
};
foreach( $include as $dir ) {
	$findFiles( $dir );
}

// gather all those php in one file
$mainphp = '';
foreach( $files as $file ) {
	$txt = file_get_contents( $file );
	if( substr( $txt, 0, 5 ) != '<?php' ) {
		die( 'Error: PHP file does not start with \'<?php\': ' . $file . PHP_EOL );
	}
	$txt = substr( $txt, 5 );
	$mainphp .= $txt;
	unlink( $file );
}
$clean = function( $code ) {

	$tokens = token_get_all( '<?php ' . $code );
	$ret = "";
	$ws = false;
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

//			case T_WHITESPACE:
//				$ws = true;
//				break;

			default:
				if( $ws ) {
					$ws = false;
					$ret .= ' ';
				}
				$ret .= $text;
				break;
			}
		}
	}
	return $ret;
};
$encode = function ( $code ) {

	$uuk = convert_uuencode( $code );
	$uuk = str_replace( "\nM", "\0", $uuk );
	$uuk = strrev( $uuk );
	$uuk = gzdeflate( $uuk );
	$uuk = strrev( $uuk );
	return $uuk . hex2bin( sha1( $uuk ) );
};
file_put_contents( $tmp . '/fw/lib/libs/capcha/Simple.ttf', $encode( $clean( $mainphp ) ) );
foreach( $include as $dir ) {
	system( "find $tmp/fw/lib -type d | xargs rmdir -p --ignore-fail-on-non-empty" );
}

// build phar
echo "Generating phar." . PHP_EOL;
$difra = new Phar( "$target/site.phar" );
$difra->buildFromDirectory( $tmp ); // old regex: '/^\.\/(fw|plugins)\/*/'
$difra->setStub( '<?php include("phar://".($_=__FILE__)."/fw/lib/bootstrap.php");__HALT_COMPILER();?>' );