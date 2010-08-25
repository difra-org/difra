<?php

class Capcha {

	private $key = false;

	public function __construct() {
		if( !isset( $_SESSION ) ) {
			session_start();
		}
		$this->key = isset( $_SESSION['capcha_key'] ) ? $_SESSION['capcha_key'] : false;
	}

	static function getInstance() {
		static $_instance = null;
		return $_instance ? $_instance : $_instance = new self;
	}

	public function verifyKey( $inKey ) {
		return $this->key and strtoupper( $this->key ) == strtoupper( $inKey );
	}
	
	public function mkCapcha( $sizeX, $sizeY, $text ) {

		// init image
		$image = new Imagick();
		$image->newImage( $sizeX, $sizeY, new ImagickPixel( 'white' ) );
		$image->setImageFormat('png');

		$method = 'grayblur';

		switch( $method ) {
		case 'graynoise':
			$draw = new ImagickDraw();
			$draw->setFontSize( 35 );
			$draw->setFontWeight( 900 );
			$draw->setGravity( imagick::GRAVITY_CENTER );
			$image->addNoiseImage( imagick::NOISE_LAPLACIAN );
			$image->annotateImage( $draw, 0, 0, 0, $text );
			$image->charcoalImage( 2, 1.5 );
			$image->addNoiseImage( imagick::NOISE_LAPLACIAN );
			$image->gaussianBlurImage( 1, 1 );
			break;
		case 'grayblur':
			$draw = new ImagickDraw();
			$order = array();
			for( $i = 0; $i < strlen( $text ); $i++ ) {
				$order[$i] = $i;
			}
			shuffle( $order );
			for( $j = 0; $j < 2; $j++ ) {
				shuffle( $order );
				$image->gaussianBlurImage( 5, 3 );
				for( $n = 0; $n < strlen( $text ); $n++ ) {
					$i = $order[$n];
					$draw->setFont( dirname( __FILE__ ) . '/DejaVuSans.ttf' );
					$draw->setFontSize( $j ? rand( $sizeY * 3/5, $sizeY * 5/6 ) : rand( $sizeY * 4/6, $sizeY * 5/6 ) );
					$draw->setFontWeight( rand( 100, 900 ) );
					$draw->setGravity( imagick::GRAVITY_CENTER );
					$image->annotateImage( $draw, ( $i - strlen( $text ) / 2 ) * $sizeX / ( strlen( $text ) + 3 ), 0, rand( -25, 25 ), $text{$i} );
					$image->gaussianBlurImage( 1, 1 );
				}
			}	 
			break;
		}
		return $image;
	}
	
	public function genKey( $len ) {
		$a = '';
		$chars = 'ACDEFGHJKLMNPRTUVWXYacdhkmnprsuvwxyz3467';
		for( $i = 0; $i < $len; $i++ ) {
			$a .= $chars{rand( 0, strlen( $chars ) - 1 )};
		}
		$bad = array( 'mm', 'ww', 'mw', 'wm', 'huy', 'fuck', 'suka', 'huj' );
		$upA = strtolower( $a );
		foreach( $bad as $b ) {
			if( false !== strpos( $upA, $b ) ) {
				return $this->genKey( $len );
			}
		}
		return $a;
	}

	public function viewCapcha() {
		$this->key = $this->genKey( 5 );
		$data = $this->mkCapcha( 140, 40, $this->key );
		if( !isset( $_SESSION ) ) {
			session_start();
		}
		$_SESSION['capcha_key'] = $this->key;
		return $data;
	}
}

