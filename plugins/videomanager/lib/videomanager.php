<?php

namespace Difra\Plugins;
use Difra;
/**
 * Статусы:
 * 0 - не обработан
 * 1 - в очереди
 * 2 - обрабатывается
 * 3 - готов
 *
 * Папки:
 * dirIn - входящие видео файлы
 * dirOut - перекодированные видео файлы
 * thumbsDir - папка куда складываются превьюшки видео файла
 * postersDir - папка с постерами
 * httpThumbs - url превьющек видео
 * httpPosters - url с постерами
 *
 * Пути можно комбинировать, например thumbsDir и postersDir могут находится в одном месте
 *
 */
class videoManager {

	// пути к папкам
	private $dirIn = null;
	private $dirOut = null;

	private $thumbsDir = null;
	private $postersDir = null;
	private $httpThumbs = null;
	private $httpPosters = null;

	private $videoSizes = array( '720', '480' );
	private $videoExtensions = array( 'avi', 'mpg', 'mpeg', 'flv', 'mp4', 'mov', 'mp2', 'm4v', 'mkv', 'dv' );
	private $videoOutExtensions = array( 'webm', 'ogv', 'm4v', 'mp4', 'flv' );

	static public function getInstance() {
		static $_instance = null;
		return $_instance ? $_instance : $_instance = new self;
	}

	private function __construct() {

		//TODO: Сделать полноценную конфигурацию когда появится определенность с настройками

		// временные пути к папкам
		$this->dirIn = DIR_DATA . 'videoIn';
		$this->dirOut = DIR_DATA . 'videoOut';
		$this->thumbsDir = $this->dirOut . '/thumbs';
		$this->postersDir = $this->dirOut . '/posters';

		$this->httpPosters = 'videoOut/posters/';
		$this->httpThumbs = 'videoOut/thumbs/';
	}

	/**
	 * Устанавливает ноду с данными о том где лежат постеры и превьющки
	 */
	public function getHttpPath() {

		$rootNode = \Difra\Action::getInstance()->controller->root;
		$videoManagerNode = $rootNode->appendChild( $rootNode->ownerDocument->createElement( 'video-preview' ) );
		$videoManagerNode->setAttribute( 'thumbs', '/' . $this->httpThumbs );
		$videoManagerNode->setAttribute( 'posters', '/' . $this->httpPosters );
	}

	/**
	 * Удаляет из входящей папки файл
	 * @param $name
	 */
	public function deleteFile( $name ) {

		if( file_exists( $this->dirIn . '/' . $name ) ) {

			return unlink( $this->dirIn . '/' . $name );
		}
		return false;
	}

	/**
	 * Возвращает XML с данными видео во входящей папке
	 * @param \DOMNode $node
	 */
	public function getInVideosXML( $node ) {

		if( !is_dir( $this->dirIn ) ) {

			$errorNode = $node->appendChild( $node->ownerDocument->createElement( 'error' ) );
			$errorNode->setAttribute( 'type', 'badInDir' );
			return;
		}

		// возможно и не нужно чистить кэш, но у меня переодически наблюдались некие глюки при чтении директории.
		clearstatcache();
		$dh = dir( $this->dirIn );

		while( false !== ( $file = $dh->read() ) ) {

			if( $file!='.' && $file!='..' ) {

				$fileNode = $node->appendChild( $node->ownerDocument->createElement( 'file' ) );
				$fileNode->setAttribute( 'name', $file );
				$tmp = pathinfo( $this->dirIn . '/' . $file );
				if( !in_array( $tmp['extension'], $this->videoExtensions ) ) {
					$fileNode->setAttribute( 'trash', true );
				}
				$fileNode->setAttribute( 'size', sprintf( "%.2f", filesize( $this->dirIn . '/' . $file ) / 1048576 ) );
			}
		}
		$dh->close();
	}

	/**
	 * Возвращает XML с добавленными в базу видео
	 * @param \DOMNOde $node
	 */
	public function getAddedVideosXML( $node ) {

		if( ! is_dir( $this->dirOut ) ) {

			$errorNode = $node->appendChild( $node->ownerDocument->createElement( 'error' ) );
			$errorNode->setAttribute( 'type', 'badOutDir' );
			return;
		}

		$db = \Difra\MySQL::getInstance();
		$query = "SELECT `id`, `video`, `status`, `name`, `date`, `thumbs`, `length`, `hasPoster`, `original_file` FROM `videos`
				WHERE `site`='" . \Difra\Site::getInstance()->getHost() . "' ORDER BY `date` ASC";
		$db->fetchXML( $node, $query );
	}

	/**
	 * Добавляет видео в базу
	 * @param $file
	 * @param $name
	 * @param null $poster
	 * @return bool|string
	 */
	public function addVideo( $file, $name, $poster = null ) {

		$hasPoster = 0;
		$db = \Difra\MySQL::getInstance();
		$videoHash = sha1( uniqid() );

		if( !is_null( $poster ) ) {
			$hasPoster = 1;

			if( !is_dir( $this->postersDir ) ) {
				return 'badPosterDir';
			}

			foreach( $this->videoSizes as $size ) {

				$res = @file_put_contents( $this->postersDir . '/' . $videoHash . '_' . $size . '_0' . '.png',
								\Difra\Libs\Images::getInstance()->createThumbnail( $poster, $size, $size, 'png' ) );
				if( $res === false ) {
					return 'badPosterSave';
				}
			}

			// отдельно сохраняем маленькую превьюшку для админки
			$res = @file_put_contents( $this->postersDir . '/' . $videoHash . '_thumb.png',
							\Difra\Libs\Images::getInstance()->createThumbnail( $poster, 78, 78, 'png' ) );
			if( $res === false ) {
				return 'badPosterSave';
			}
		}

		$query = "INSERT INTO `videos` (`video`, `site`, `name`, `original_file`, `date`, `status`, `hasPoster`)
				VALUES ('" . $videoHash . "', '" . \Difra\Site::getInstance()->getHost() . "', '" . $db->escape( $name ) . "', '" .
						$db->escape( $file ) . "', NOW(), 0, '" . intval( $hasPoster ) . "')";
		$db->query( $query );
		return true;
	}

	/**
	 * Удаляет уже добавленное видео в базу
	 * @param $id
	 */
	public function deleteAddedVideo( $id ) {

		$db = \Difra\MySQL::getInstance();
		$videoData = $db->fetchRow( "SELECT `video`, `original_file`, `status`, `thumbs` FROM `videos` WHERE `id`='" . intval( $id ) . "'" );
		if( empty( $videoData ) ) {
			return false;
		}

		if( $videoData['status']<2 ) {
			// статус - не обработан, в очереди

			unlink( $this->dirIn .'/' . $videoData['original_file'] );

		}elseif( $videoData['status']>2 ) {
			// статус - обработан

			if( $videoData['thumbs']>0 ) {

				// удаляем превьюшки
				for( $i=1; $i<=$videoData['thumbs']; $i++ ) {
					if( file_exists( $this->thumbsDir . '/' . $videoData['video'] . '_' . $i . '.png' ) ) {

						unlink( $this->thumbsDir . '/' . $videoData['video'] . '_' . $i . '.png' );
					}
				}
			}

			// удаляем результирующее видео
			//TODO: возможно будет другая структура папок в videoOut
			$vOutDir = '/' . substr( $videoData['video'], 0, 2 ) . '/';
			foreach( $this->videoOutExtensions as $ext ) {
				foreach( $this->videoSizes as $size ) {
					if( file_exists( $this->dirOut . $vOutDir . $videoData['video'] . '_' . $size . '.' . $ext ) ) {
						unlink( $this->dirOut . $vOutDir . $videoData['video'] . '_' . $size . '.' . $ext );
					}
				}
			}
		}

		// удаляем постер
		if( file_exists( $this->postersDir . '/' . $videoData['video'] . '_720_0.png' ) ) {
			foreach( $this->videoSizes as $size ) {
				unlink( $this->postersDir . '/' . $videoData['video'] . '_' . $size . '_0.png' );
			}
			unlink( $this->postersDir . '/' . $videoData['video'] . '_thumb.png' );
		}

		$db->query( "DELETE FROM `videos` WHERE `id`='" . intval( $id ) . "'" );
		return true;
	}

	/**
	 * Устанавливает статус видео
	 * @param $id
	 * @param $status
	 */
	public function changeStatus( $id, $status ) {

		$db = \Difra\MySQL::getInstance();
		$db->query( "UPDATE `videos` SET `status`='" . intval( $status ) . "' WHERE `id`='" . intval( $id ) . "'" );
	}

	/**
	 * Сохраняет постер для видео
	 * @param $video
	 * @param $poster
	 */
	public function savePoster( $video, $poster ) {

		$db = \Difra\MySQL::getInstance();
		$video = $db->escape( $video );

		if( ! is_dir( $this->postersDir ) ) {
			return 'badPosterDir';
		}

		foreach( $this->videoSizes as $size ) {

			$res = @file_put_contents( $this->postersDir . '/' . $video . '_' . $size . '_0' . '.png', \Difra\Libs\Images::getInstance()
					->createThumbnail( $poster, $size, $size, 'png' ) );
			if( $res === false ) {
				return 'badPosterSave';
			}
		}

		// отдельно сохраняем маленькую превьюшку для админки
		$res = @file_put_contents(
			$this->postersDir . '/' . $video . '_thumb.png', \Difra\Libs\Images::getInstance()->createThumbnail( $poster, 78, 78, 'png' ) );
		if( $res === false ) {
			return 'badPosterSave';
		}

		$db->query( "UPDATE `videos` SET `hasPoster`=1 WHERE `video`='" . $video . "'" );
		return true;
	}

	/**
	 * Меняет название добавленного в базу видео
	 * @param $id
	 * @param $name
	 */
	public function changeName( $id, $name ) {

		$db = \Difra\MySQL::getInstance();
		$db->query( "UPDATE `videos` SET `name`='" . $db->escape( $name ) . "' WHERE `id`='" . intval( $id ) . "'" );
	}
}

