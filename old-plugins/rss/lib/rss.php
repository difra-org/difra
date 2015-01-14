<?php

namespace Difra\Plugins;

use Difra\Envi;
use Difra\Libs\Images;
use Difra\Locales;
use Difra\Plugins\Catalog\Item\Image;

class rss {

	private $settings = array( 'onLine' => 1, 'title' => null, 'link' => null, 'description' => null, 'copyright' => null,
					'ttl' => 120, 'size' => 20, 'image' => 1, 'cache' => 1 );

	/**
	 * @var \DOMDocument null
	 */
	private $rssDoc = null;
	/**
	 * @var \DOMNode null
	 */
	private $rss = null;
	/**
	 * @var \DOMNOde null
	 */
	private $channel = null;
	/**
	 * флаг, определяет есть ли закэшированные данные rss
	 * @var bool
	 */
	private $cached = false;


	public function __construct() {

		$this->settings['link'] = 'http://' . Envi::getHost();
	}

	/**
	 * Создаёт базовую rss со всеми настройками
	 *
	 * @static
	 *
	 * @param bool $ignoreCache
	 *
	 * @return rss
	 */
	public static function make( $ignoreCache = false ) {

		$rss = new self;

		$rss->getSettings();

		if( $rss->settings['onLine'] == 0 ) {
			return false;
		}

		// проверяем кэш
		$cachedRss = $rss->getCache();
		if( !empty( $cachedRss ) && !$ignoreCache ) {
			$rss->xml = $cachedRss;
			$rss->cached = true;
			return $rss;
		}

		$rss->rssDoc = new \DOMDocument;
		$rss->rssDoc->formatOutput = true;
		$rss->rssDoc->encoding = 'utf-8';

		$rss->rss = $rss->rssDoc->appendChild( $rss->rssDoc->createElement( 'rss' ) );
		$rss->rss->setAttribute( 'version', '2.0' );

		$rss->channel = $rss->rss->appendChild( $rss->rssDoc->createElement( 'channel' ) );

		$rss->channel->appendChild( $rss->rssDoc->createElement( 'title', $rss->settings['title'] ) );
		$rss->channel->appendChild( $rss->rssDoc->createElement( 'link',  $rss->settings['link'] ) );
		$rss->channel->appendChild( $rss->rssDoc->createElement( 'language', Envi\Setup::getLocale() ) );
		if( !empty( $rss->settings['description'] ) ) {
			$rss->channel->appendChild( $rss->rssDoc->createElement( 'description', $rss->settings['description'] ) );
		}
		$rss->channel->appendChild( $rss->rssDoc->createElement( 'ttl', $rss->settings['ttl'] ) );
		if( !empty( $rss->settings['copyright'] ) ) {
			$copyRight = 'Copyright ' . date( 'Y' ) . ', ' . $rss->settings['copyright'];
			$rss->channel->appendChild( $rss->rssDoc->createElement( 'copyright', $copyRight ) );
		}

		$pubDate = date( 'r' );
		$rss->channel->appendChild( $rss->rssDoc->createElement( 'pubDate', $pubDate ) );
		$rss->rssImage();

		return $rss;

	}

	private function getCache() {

		return \Difra\Cache::getInstance()->get( 'rss' );
	}

	private function rssImage() {

		if( $this->settings['image']==0 ) {
			return;
		}

		$imagePath = DIR_DATA . 'rss/';
		if( file_exists( $imagePath . 'rsslogo.png' ) ) {

			$Locales = \Difra\Locales::getInstance();
			$mainHost = Envi::getHost();

			$sizes = getimagesize( $imagePath . 'rssLogo.png' );

			$imageNode = $this->channel->appendChild( $this->rssDoc->createElement( 'image' ) );
			$imageNode->appendChild( $this->rssDoc->createElement( 'title', $Locales->getXPath( 'rss/imageTitle' ) ) );
			$imageNode->appendChild( $this->rssDoc->createElement( 'link', 'http://' . $mainHost ) );
			$imageNode->appendChild( $this->rssDoc->createElement( 'url', 'http://' . $mainHost . '/rss/rsslogo.png' ) );
			$imageNode->appendChild( $this->rssDoc->createElement( 'width', $sizes[0] ) );
			$imageNode->appendChild( $this->rssDoc->createElement( 'height', $sizes[1] ) );
		}
	}


	public static function deleteLogo() {
		@unlink( DIR_DATA . 'rss/rsslogo.png' );
	}

	public function setItem( $itemArray ) {

		if( empty( $itemArray ) || $this->cached == true ) {
			return false;
		}

		$itemNode = $this->channel->appendChild( $this->rssDoc->createElement( 'item' ) );
		foreach( $itemArray as $key => $value ) {
			if( $key == 'description' ) {
				$cdNode = $itemNode->appendChild( $this->rssDoc->createElement( $key ) );
				$cdNode->appendChild( $this->rssDoc->createCDATASection( $value ) );
			} else {
				if( $key == 'pubDate' ) {
					$value = date( 'r', strtotime( $value ) );
				}
				if( $value != '' ) {
					$itemNode->appendChild( $this->rssDoc->createElement( $key, $value ) );
				}
			}
		}
		return true;
	}

	public function get() {

		if( $this->cached == true ) {
			return $this->xml;
		}

		$outPut = $this->rssDoc->saveXml();
		if( $this->settings['cache'] == 1 ) {
			\Difra\Cache::getInstance()->put( 'rss', $outPut, $this->settings['ttl'] * 60 );
		}

		return $outPut;
	}

	public static function saveSettings( $settings ) {

		if( !empty( $settings ) ) {

			if( isset( $settings['logo'] ) ) {

				$logoImage = $settings['logo']->val();
				unset( $settings['logo'] );

				$Images = Images::getInstance();

				@mkdir( DIR_DATA . 'rss', 0777, true );

				try {
					$rawImg = $Images->createThumbnail( $logoImage, 256, 256, 'png' );

				} catch( \Difra\Exception $ex ) {
					throw new \Difra\Exception( 'Bad image format.' );
				}

				try {
					file_put_contents( DIR_DATA . 'rss' . '/rsslogo.png', $rawImg );
				} catch( \Difra\Exception $ex ) {
					throw new \Difra\Exception( "Can't save image" );
				}
			}

			\Difra\Config::getInstance()->set( 'rss', $settings );
		}
	}

	/**
	 * Возвращает xml с настройками канала
	 * @static
	 * @param \DOMNOde $node
	 */
	public static function getSettingsXML( $node ) {

		$Rss = new self;
		$rssSettings = \Difra\Config::getInstance()->get( 'rss' );
		if( !empty( $rssSettings ) ) {
			foreach( $rssSettings as $key=>$value ) {
				if( $value!=='' ) {
					$Rss->settings[$key] = $value;
				}
			}
		}

		foreach( $Rss->settings as $key=>$value ) {
			$node->setAttribute( $key, $value );
		}

		if( file_exists( DIR_DATA . 'rss/rsslogo.png' ) ) {
			$node->setAttribute( 'logo', true );
		}
	}

	private function getSettings() {

		$rssSettings = \Difra\Config::getInstance()->get( 'rss' );
		if( ! empty( $rssSettings ) ) {
			foreach( $rssSettings as $key => $value ) {
				if( $value !== '' ) {
					$this->settings[$key] = $value;
				}
			}
		}
	}

	public function getSize() {

		return $this->settings['size'];
	}

	public function checkCached() {

		return $this->cached;
	}

}