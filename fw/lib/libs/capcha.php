<?php

namespace Difra\Libs;

class Capcha {

	private $key = false;
	private $sizeX = 140;
	private $sizeY = 40;
	private $keyLength = 5;

	/**
	 * Конструктор
	 */
	public function __construct() {

		\Difra\Site::getInstance()->sessionStart();
		$this->key = isset( $_SESSION['capcha_key'] ) ? $_SESSION['capcha_key'] : false;
	}

	/**
	 * Синглтон
	 * @return Capcha
	 */
	static function getInstance() {

		static $_instance = null;
		return $_instance ? $_instance : $_instance = new self;
	}

	/**
	 * Проверка введенного значения капчи
	 * @param string $inKey
	 *
	 * @return bool
	 */
	public function verifyKey( $inKey ) {

		return $this->key and strtoupper( $this->key ) == strtoupper( $inKey );
	}

	/**
	 * Создаёт изображение заданного размера с заданным текстом
	 * @param int    $sizeX
	 * @param int    $sizeY
	 * @param string $text
	 *
	 * @return \Imagick
	 */
	public function mkCapcha( $sizeX, $sizeY, $text ) {

		// init image
		$image = new \Imagick();
		$image->newImage( $sizeX, $sizeY, new \ImagickPixel( 'white' ) );
		$image->setImageFormat( 'png' );

		$method = 'grayblur';

		switch( $method ) {
		case 'graynoise':
			$draw = new \ImagickDraw();
			$draw->setFontSize( 35 );
			$draw->setFontWeight( 900 );
			$draw->setGravity( \imagick::GRAVITY_CENTER );
			$image->addNoiseImage( \imagick::NOISE_LAPLACIAN );
			$image->annotateImage( $draw, 0, 0, 0, $text );
			$image->charcoalImage( 2, 1.5 );
			$image->addNoiseImage( \imagick::NOISE_LAPLACIAN );
			$image->gaussianBlurImage( 1, 1 );
			break;
		case 'grayblur':
			$draw  = new \ImagickDraw();
			$order = array();
			for( $i = 0; $i < strlen( $text ); $i++ ) {
				$order[$i] = $i;
			}
			shuffle( $order );
			for( $j = 0; $j < 2; $j++ ) {
				shuffle( $order );
				$image->gaussianBlurImage( 15, 3 );
				for( $n = 0; $n < strlen( $text ); $n++ ) {
					$i = $order[$n];
					$draw->setFont( __DIR__ . '/capcha/DejaVuSans.ttf' );
					$draw->setFontSize( $j
								    ? rand( $sizeY * 3 / 5, $sizeY * 5 / 6 )
								    : rand( $sizeY * 4 / 6,
									    $sizeY * 5 / 6 ) );
					$draw->setFontWeight( rand( 100, 900 ) );
					$draw->setGravity( \imagick::GRAVITY_CENTER );
					$image->annotateImage( $draw,
							       ( $i - strlen( $text ) / 2 ) * $sizeX / ( strlen( $text ) + 2.3 ),
							       0,
							       rand( -25, 25 ),
							       $text{$i} );
					$image->gaussianBlurImage( 1, 1 );
				}
			}
			break;
		}
		return $image;
	}

	/**
	 * Генерирует случайный текст для капчи
	 * @param $len
	 *
	 * @return string
	 */
	public function genKey( $len ) {

		$a     = '';
		$chars = 'ACDEFGHJKLNPRUVXYacdhknpsuvxyz3467';
		for( $i = 0; $i < $len; $i++ ) {
			$a .= $chars{rand( 0, strlen( $chars ) - 1 )};
		}
		$bad =
			array(
				'mm',
				'ww',
				'mw',
				'wm',
				'huy',
				'fuck',
				'suka',
				'huj',
				'hui',
				'blya',
				'blia',
				'blja',
				'pidor',
				'sex',
				'suck',
				'cyka',
				'pee',
				'pizd',
				'pi3d',
				'nu3g'
			);
		$upA = strtolower( $a );
		foreach( $bad as $b ) {
			if( false !== strpos( $upA, $b ) ) {
				return $this->genKey( $len );
			}
		}
		return $a;
	}

	/**
	 * Гененрирует ключ и создаёт капчу
	 * @return \Imagick
	 */
	public function viewCapcha() {

		$this->key = $this->genKey( $this->keyLength );
		$data      = $this->mkCapcha( $this->sizeX, $this->sizeY, $this->key );
		\Difra\Site::getInstance()->sessionStart();
		$_SESSION['capcha_key'] = $this->key;
		return $data;
	}

	/**
	 * Установка размеров капчи для $this->viewCapcha()
	 *
	 * @param int $sizeX
	 * @param int $sizeY
	 */
	public function setSize( $sizeX, $sizeY ) {

		$this->sizeX = $sizeX;
		$this->sizeY = $sizeY;
	}

	/**
	 * Установка длины ключа для $this->viewCapcha()
	 *
	 * @param $n
	 */
	public function setKeyLength( $n ) {

		$this->keyLength = $n;
	}
}

