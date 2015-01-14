<?php

namespace Difra\Plugins\Radio;
use Difra;

/**
 * Человеков я люблю, всех их в ямку соберу! И бетоном, и бетоном, и бетоном их залью! (с)
 */

class Channel {

	// warnings
	const OK_ROTATION           = 'playing';
	const NO_ARTIST_TO_ROTATION = 'noArtist';
	const NO_SONGS_TO_ROTATION  = 'noSongs';
	const EMPTY_ROTATION        = 'empty';
	const MANUAL_ROTATION       = 'manual';
	const SMALL_PLAYLIST        = 'small';

	// errors
	const BAD_PLAYLIST_GENERATION = 'badGeneration';

	/**
	 * Минимальное кол-во песен в плей-листе канала до повторения композиции в минутах
	 * @var int
	 */
	private $minSongInQuery = 120;
	/**
	 * Минимальное кол-во исполнителей в плей-листе до повторения исполнителя в минутах
	 * @var int
	 */
	private $minArtistInQuery = 60;

	/**
	 * Название точки монтирования канала
	 * @var string
	 */
	private $name = null;

	/**
	 * Текстовое название канала
	 * @var string
	 */
	private $channelName = null;

	/**
	 * Статус работы ротации канала
	 * @var string null
	 */
	private $status = null;

	/**
	 * Сохранять ли дебаг статус ротации в sql
	 *
	 * @var bool
	 */
	private $saveStatus = false;

	/**
	 * Временные данные о работе канала
	 * @var array
	 */
	private $tempData = null;

	/**
	 * Является ли канал эфирным
	 * @var int
	 */
	private $onLine = 0;

	/**
	 * Кол-во треков в плей-листе / кол-во итераций генерации плей-листа
	 * @var int
	 */
	private $tracksCount = 1;

	/**
	 * Настройки канала из базы
	 * @var array
	 */
	private $rawSettings = array();

	/**
	 * Устанавливает параметры ротации канала
	 * @param string $channel
	 *
	 * @return void
	 */
	private function _getChannelSettings( $channel ) {

		$db  = \Difra\MySQL::getInstance();
		$res = $db->fetchRow( "SELECT *	FROM `radio_channels` WHERE `mount`='" . $db->escape( $channel ) . "'" );
		if( empty( $res ) ) {
			return;
		}

		// устнвонвка настроек канала
		$this->minArtistInQuery = $res['minArtistInQuery'];
		$this->minSongInQuery   = $res['minSongInQuery'];
		$this->tracksCount      = $res['tracksCount'];
		$this->onLine           = $res['onLine'];
		$this->saveStatus       = $res['debug'];
		$this->channelName      = $res['name'];
		$this->rawSettings      = $res;
	}

	public static function get( $channelName ) {

		$channels = \Difra\Plugins\Radio::getInstance()->getChannels();
		if( !in_array( $channelName, $channels ) ) {
			return false;
		}

		$Channel       = new self;
		$Channel->name = $channelName;
		$Channel->_getChannelSettings( $Channel->name );
		$Channel->_getData();

		return $Channel;
	}

	private function _getData() {

		$db  = \Difra\MySQL::getInstance();
		$res = $db->fetch( "SELECT `name`, `data` FROM `radio_temp_data` WHERE `channel`='" . $this->name . "'" );
		if( !empty( $res ) ) {

			$tempDataArray = array();
			foreach( $res as $data ) {
				$tempDataArray[$data['name']] = $data['data'];
			}

			$this->tempData = $tempDataArray;
		}
	}

	private function _setTrackStat( $trackId ) {

		$db = \Difra\MySQL::getInstance();

		// узнать id группы трека
		$groupData = $db->fetchRow( "SELECT `group_id` FROM `radio_tracks`
						WHERE `id`='" . intval( $trackId ) . "' AND `channel`='" . $this->name . "'" );
		if( !empty( $groupData ) ) {
			$query = array();
			// апдейт трека
			$query[] = "UPDATE `radio_tracks`
				SET `lastPlayed` = NOW(), `played` = `played` + 1, `plays` = `plays` + 1
				WHERE `id` = '" . intval( $trackId ) . "' AND `channel` = '" . $this->name . "'";
			// апдейт артиста
			$query[] = "UPDATE `radio_tracks` SET `lastPlayedArtist` = NOW()
					WHERE `group_id` = '" . $groupData['group_id'] . "' AND `channel`='" . $this->name . "'";

			$db->query( $query );
		}
	}

	private function _getTrackForPlayList( $freeGet = false ) {

		$db = \Difra\MySQL::getInstance();

		$where = " AND rt.`lastPlayed`<NOW()-" . intval( $this->minSongInQuery ) . "*60 ";
		if( !$freeGet ) {
			$where = " AND (rt.`lastPlayed`<NOW()-" . intval( $this->minSongInQuery ) . "*60
					AND rt.`lastPlayedArtist`<NOW()-" . intval( $this->minArtistInQuery ) . "*60) ";
		}

		// совсем не то что хотелось :(
		// Надо будет еще прикинуть, как строить плей-лист со всеми ограничениями и не по одной песне.
		$query = "SELECT rt.`id`, rt.`group_id`, t.`filename`, t.`title`, g.`name`, rt.`duration`, (1 + rt.`weight`)/(1 + rt.`plays`) AS `weight`
				FROM `radio_tracks` rt
				LEFT JOIN `tracks` AS `t` ON t.`id`=rt.`id`
				LEFT JOIN `groups` AS `g` ON g.`id`=t.`group_id`
				WHERE rt.`channel`='" . $this->name . "'" . $where .
			 "ORDER BY `weight` DESC, RAND()
				LIMIT 1";
		$res   = $db->fetchRow( $query );

		return $res;
	}

	private function _getRandomTrack() {

		$db = \Difra\MySQL::getInstance();

		$query = "SELECT rt.`id`, rt.`group_id`, t.`filename`, t.`title`, g.`name`, rt.`duration`, (1 + rt.`weight`)/(1 + rt.`plays`) AS `weight`
				FROM `radio_tracks` rt
				LEFT JOIN `tracks` AS `t` ON t.`id`=rt.`id`
				LEFT JOIN `groups` AS `g` ON g.`id`=t.`group_id`
				WHERE rt.`channel`='" . $this->name . "'
				ORDER BY RAND()
				LIMIT 1";
		return $db->fetchRow( $query );
	}

	private function _setStatus( $status ) {

		$this->status = $status;
		if( $this->saveStatus == 1 ) {
			$db = \Difra\MySQL::getInstance();
			$db->query( "REPLACE INTO `radio_temp_data` (`channel`, `name`, `data`)
					VALUES ('" . $this->name . "', 'status', '" . $db->escape( $this->status ) . "')" );
		}
	}

	private function _getLastPosition() {

		$db = \Difra\MySQL::getInstance();
		$r  = $db->fetchRow( "SELECT MAX(`position`) AS `position` FROM `radio_playlist` WHERE `channel` = '" . $this->name . "'" );
		if( $r['position'] > 0 ) {
			return $r['position'];
		}
		return 0;
	}

	private function _generatePlayList() {

		$db    = \Difra\MySQL::getInstance();
		$track = $this->_getTrackForPlayList();

		// если нет песен с исполнителем, которого еще не играли
		if( empty( $track ) ) {
			$this->_setStatus( self::NO_ARTIST_TO_ROTATION );
			$track = $this->_getTrackForPlayList( true );

			// если вообще нет песен по правилам ротации
			if( empty( $track ) ) {

				$this->_setStatus( self::NO_SONGS_TO_ROTATION );
				$track = $this->_getRandomTrack();

				// ошибка ротации. Выбирать вообще нечего.
				if( empty( $track ) ) {
					$this->_setStatus( self::BAD_PLAYLIST_GENERATION );
					return false;
				}
			}
		} else {
			$this->_setStatus( self::OK_ROTATION );
		}

		$position = $this->_getLastPosition() + 1;

		// апдейт трека для соблюдения правил ротации
		$query[] = "UPDATE `radio_tracks` SET `lastPlayed` = NOW() WHERE `id` = '" . $track['id'] . "' AND `channel`='" . $this->name . "'";
		$query[] = "UPDATE `radio_tracks` SET `lastPlayedArtist` = NOW()
				WHERE `group_id` = '" . $track['group_id'] . "' AND `channel`='" . $this->name . "'";

		$title = $track['title'] = $track['name'] . ' - ' . $track['title'];

		$query[] = "INSERT INTO `radio_playlist` (`position`, `channel`, `id`, `group_id`, `filename`, `title`, `duration`)
				VALUES('" . intval( $position ) . "', '" . $this->name . "', '" . intval( $track['id'] ) . "', '" .
			   intval( $track['group_id'] ) . "', '" . $db->escape( $track['filename'] ) . "', '" .
			   $db->escape( $title ) . "', '" . intval( $track['duration'] ) . "')";
		$db->query( $query );
		$track['position'] = $position;

		// многотрековость в плейлисте
		$count = $this->_getPlayListCount();
		if( $count < $this->tracksCount ) {
			// вот тебе и рекурсия. (
			$this->_generatePlayList();
		}

		return $track;
	}

	/**
	 * Возвращает ёмкость канала в виде кол-во треков и время прослушивания
	 * return array
	 */
	public function getCapacity() {

		$db  = \Difra\MySQL::getInstance();
		$res = $db->fetchRow( "SELECT COUNT(`id`) AS `count`, SUM(`duration`) AS `duration`
					FROM `radio_tracks`
					WHERE `channel`='" . $this->name . "'" );

		if( !empty( $res ) && $res['count'] > 0 ) {

			$res['duration'] = $value = date( "H:i:s", mktime( 0, 0, $res['duration'] ) );
		} else {
			$res = null;
		}

		return $res;
	}

	/**
	 * @param \DOMNode $node
	 */
	public function getXML( $node ) {

		/** @var \DOMElement $channelNode */
		$channelNode = $node->appendChild( $node->ownerDocument->createElement( 'channel' ) );
		$channelNode->setAttribute( 'name', $this->name );
		$channelNode->setAttribute( 'minSongInQuery', $this->minSongInQuery );
		$channelNode->setAttribute( 'minArtistInQuery', $this->minArtistInQuery );

		$capacity = $this->getCapacity();
		if( !is_null( $capacity ) ) {
			$channelNode->setAttribute( 'track_count', $capacity['count'] );
			$channelNode->setAttribute( 'duration', $capacity['duration'] );
		} else {
			$channelNode->setAttribute( 'emptyChannel', true );
		}

		if( !empty( $this->tempData ) ) {
			if( isset( $this->tempData['status'] ) ) {
				$channelNode->setAttribute( 'status', $this->tempData['status'] );
			}
			if( isset( $this->tempData['currentPlay'] ) ) {
				/** @var \DOMElement $cPlayNode */
				$cPlayNode = $channelNode->appendChild( $node->ownerDocument->createElement( 'currentPlay' ) );
				$cPlayData = unserialize( $this->tempData['currentPlay'] );
				foreach( $cPlayData as $k=> $value ) {
					if( $k == 'duration' ) {
						$value = date( "i:s", mktime( 0, 0, $value ) );
					}
					$cPlayNode->setAttribute( $k, $value );
				}
			}
		}
	}

	public function getPlayList() {

		$db = \Difra\MySQL::getInstance();
		return $db->fetch( "SELECT * FROM `radio_playlist` WHERE `channel` = '" . $db->escape( $this->name ) . "'" );
	}

	/**
	 * Возвращает XML с текущим плейлистом
	 *
	 * @param \DOMNode $node
	 */
	public function getPlayListXML( $node ) {

		$db  = \Difra\MySQL::getInstance();
		$res = $db->fetch( "SELECT `id`, `position`, `title`, `duration`
						FROM `radio_playlist` WHERE `channel` = '" . $this->name . "' ORDER BY `position` ASC" );
		if( !empty( $res ) ) {
			foreach( $res as $data ) {
				/** @var \DOMElement $itemXml */
				$itemXml = $node->appendChild( $node->ownerDocument->createElement( 'item' ) );
				foreach( $data as $key=> $value ) {
					if( $key == 'duration' ) {
						$value = date( 'i:s', mktime( 0, 0, $value ) );
					}
					$itemXml->setAttribute( $key, $value );
				}
			}
		}
	}

	/**
	 * Возвращает xml с параметрами канала
	 *
	 * @param \DOMNode$node
	 */
	public function getSettingsXML( $node ) {

		if( !empty( $this->rawSettings ) ) {
			foreach( $this->rawSettings as $key=> $value ) {
				$node->appendChild( $node->ownerDocument->createElement( $key, $value ) );
			}
		}
	}

	/**
	 * Устанавливает настройки для канала
	 * @param string $mount
	 * @param array  $data
	 *
	 * @return bool|string
	 */
	public function setSettings( $mount, $data ) {

		$db    = \Difra\MySQL::getInstance();
		$Radio = \Difra\Plugins\Radio::getInstance();

		if( empty( $data ) ) {
			return false;
		}

		$insertData = array();
		foreach( $data as $key => $value ) {
			$insertData[] = " `" . $db->escape( $key ) . "`='" . $db->escape( $value ) . "'";
		}

		$result = $Radio->makeIces( $data );
		if( $result !== true ) {
			return $result;
		}

		$query = "UPDATE `radio_channels` SET " . implode( ', ', $insertData ) . " WHERE `mount`='" . $db->escape( $mount ) . "'";
		$db->query( $query );

		$result = $Radio->makeIceCast();
		return $result;
	}

	/**
	 * Возвращает кол-во треков в текущем плейтисле
	 * @return int
	 */
	private function _getPlayListCount() {

		$db = \Difra\MySQL::getInstance();
		$r  = $db->fetchRow( "SELECT COUNT(`id`) AS `trackCount` FROM `radio_playlist` WHERE `channel`='" . $this->name . "'" );
		return $r['trackCount'];
	}

	/**
	 * Возвращает строчку, с треком который надо играть, для ices.pm
	 *
	 * @return bool|string
	 */
	public function getTrackToPlay() {

		$db = \Difra\MySQL::getInstance();

		// кол-во треков в текущем листе
		$trackCount = $this->_getPlayListCount();

		$track = $db->fetchRow( "SELECT * FROM `radio_playlist` WHERE `channel`='" . $this->name . "' ORDER BY `position` ASC LIMIT 1" );
		if( empty( $track ) ) {

			$track = $this->_generatePlayList();
		} else {
			// если кол-во треков слишком маленькое
			if( $trackCount < $this->tracksCount ) {

				$this->_generatePlayList();

				if( $this->status == '' || $this->status == self::OK_ROTATION ) {
					$this->_setStatus( self::SMALL_PLAYLIST );
				}
			}
			// $this->_setStatus( self::MANUAL_ROTATION );
		}

		if( empty( $track ) ) {
			$this->_setStatus( self::BAD_PLAYLIST_GENERATION );
			return false;
		}

		// played+1
		$this->_setTrackStat( $track['id'] );

		// set currentplay
		$nowPlayArray = array( 'title' => $track['title'], 'duration' => $track['duration'], 'start' => time(), 'stop' => time() + $track['duration'] );

		$query[] = "REPLACE INTO `radio_temp_data` (`channel`, `name`, `data`)
				VALUES ('" . $this->name . "', 'currentPlay', '" . $db->escape( serialize( $nowPlayArray ) ) . "')";
		$query[] = "DELETE FROM `radio_playlist` WHERE `channel`='" . $this->name . "' AND `position`='" . intval( $track['position'] ) . "'";
		$query[] = "UPDATE `radio_playlist` SET `position` = `position`-1 WHERE `channel` = '" . $this->name . "' ORDER BY `position` ASC";
		$db->query( $query );

		$fileName = DIR_ROOT . 'music/' . $track['filename'] . '.mp3';
		return '<root><title>' . $track['title'] . '</title><filename>' . $fileName . '</filename></root>';
	}

	/**
	 * Возвращает XML с библиотекой канала
	 *
	 * @param \DOMElement $node
	 * @param string      $sortMode
	 */
	public function getLibraryXML( $node, $sortMode = null ) {

		switch( $sortMode ) {

		case 'name':
			$order = " ORDER BY g.`name` ASC, t.`title` ASC";
			break;
		case 'weight':
			$order = " ORDER BY rt.`weight` DESC";
			break;

		case 'last':
		default:
			$order    = " ORDER BY rt.`lastPlayed` ASC";
			$sortMode = 'last';
			break;
		}

		$node->setAttribute( 'sort', $sortMode );
		$node->setAttribute( 'minSongInQuery', $this->minSongInQuery );

		$db  = \Difra\MySQL::getInstance();
		$res = $db->fetch( "SELECT rt.`id`, t.`title`, g.`name`, rt.`weight`, rt.`played`, rt.`plays`, rt.`lastPlayed`, rt.`duration`,
					IF( UNIX_TIMESTAMP( rt.`lastPlayed` )>0, UNIX_TIMESTAMP(NOW())-UNIX_TIMESTAMP( rt.`lastPlayed` ), NULL) AS `tdiff`
					FROM `radio_tracks` rt
					LEFT JOIN `tracks` AS `t` ON t.`id`=rt.`id`
					LEFT JOIN `groups` AS `g` ON g.`id`=rt.`group_id`
					WHERE rt.`channel`='" . $this->name . "'" . $order );

		if( !empty( $res ) ) {

			$idArray = array();
			foreach( $res as $data ) {
				$idArray[] = $data['id'];
			}

			$tags = \Difra\Plugins\Tags::getInstance()->getMassive( 'tracks', $idArray );

			foreach( $res as $data ) {
				/** @var \DOMElement $itemXml */
				$itemXml = $node->appendChild( $node->ownerDocument->createElement( 'track' ) );
				foreach( $data as $key=> $value ) {
					if( $key == 'duration' ) {
						$value = date( 'i:s', mktime( 0, 0, $value ) );
					}
					if( $key == 'tdiff' && $value > 0 ) {
						$value = floor( $value / 60 );
					}
					$itemXml->setAttribute( $key, $value );
				}
				if( isset( $tags[$data['id']] ) ) {
					$tagString = '';
					foreach( $tags[$data['id']] as $tag ) {
						$tagString .= $tag . ', ';
					}
					$tagString = rtrim( $tagString, " ," );
					$itemXml->setAttribute( 'tags', $tagString );
				}
			}
		} else {
			$node->appendChild( $node->ownerDocument->createElement( 'empty' ) );
		}
	}

	/**
	 * Удаляет трек из библиотеки канала
	 * @param int $id
	 */
	public function removeFromLibrary( $id ) {

		$db = \Difra\MySQL::getInstance();
		$db->query( "DELETE FROM `radio_tracks` WHERE `channel` = '" . $this->name . "' AND `id`='" . intval( $id ) . "'" );
	}

	/**
	 * Устанавливает вес композиции
	 * @param int $id
	 * @param int $weight
	 */
	public function setTrackWeight( $id, $weight ) {

		$db = \Difra\MySQL::getInstance();
		$db->query( "UPDATE `radio_tracks` SET `weight` = '" . intval( $weight ) .
			    "' WHERE `channel`='" . $this->name . "' AND `id`='" . intval( $id ) . "'" );
	}

	/**
	 * Устанавливает плейлист канала
	 * @param array $order
	 */
	public function setPlayList( $order ) {

		$db    = \Difra\MySQL::getInstance();
		$order = array_map( 'intval', $order );

		$tracks = $db->fetchWithId( "SELECT rt.`id`, rt.`duration`, t.`title`, g.`name`, t.`filename`, rt.`group_id`
						FROM `radio_tracks` rt
						LEFT JOIN `tracks` AS `t` ON t.`id` = rt.`id`
						LEFT JOIN `groups` AS `g` ON g.`id` = rt.`group_id`
						WHERE rt.`channel`='" . $this->name . "' AND rt.`id` IN (" . implode( ', ', $order ) . ")" );
		if( !empty( $tracks ) ) {

			$p           = 1;
			$valuesArray = array();
			foreach( $order as $trackId ) {

				$title         = $tracks[$trackId]['name'] . ' - ' . $tracks[$trackId]['title'];
				$valuesArray[] = "('" . $p . "', '" . $this->name . "', '" . $trackId . "', '" . $tracks[$trackId]['group_id'] . "',
							'" . $tracks[$trackId]['filename'] . "', '" . $db->escape( $title ) . "', '" .
						 intval( $tracks[$trackId]['duration'] ) . "')";
				$p++;
			}

			$db->query( "DELETE FROM `radio_playlist` WHERE `channel` = '" . $this->name . "'" );

			$query = "REPLACE INTO `radio_playlist` (`position`, `channel`, `id`, `group_id`, `filename`, `title`, `duration`)
					VALUES " . implode( ', ', $valuesArray );
			$db->query( $query );
		}
	}

	/**
	 * Возвращает xml с последними игравшими исполнителями
	 *
	 * @param \DOMElement $node
	 */
	public function getLastPlayedArtistsXML( $node ) {

		$db = \Difra\MySQL::getInstance();

		// показываем ограничения
		$node->setAttribute( 'minArtistInQuery', $this->minArtistInQuery );

		$query = "SELECT rt.`lastPlayedArtist`, rt.`group_id`, g.`name`,
				IF( UNIX_TIMESTAMP( rt.`lastPlayedArtist` )>0, UNIX_TIMESTAMP(NOW())-UNIX_TIMESTAMP( rt.`lastPlayedArtist` ), NULL) AS `tdiff`
				FROM `radio_tracks` rt
				LEFT JOIN `groups` AS `g` ON g.`id`=rt.`group_id`
				WHERE `channel`='" . $this->name . "'
				GROUP BY rt.`group_id`
				ORDER BY rt.`lastPlayedArtist` ASC";

		$res = $db->fetch( $query );
		if( !empty( $res ) ) {

			foreach( $res as $data ) {
				/** @var \DOMElement $itemNode */
				$itemNode = $node->appendChild( $node->ownerDocument->createElement( 'artist' ) );
				foreach( $data as $key=> $value ) {
					if( $key == 'tdiff' && $value > 0 ) {
						$value = floor( $value / 60 );
					}
					$itemNode->setAttribute( $key, $value );
				}
			}
		} else {
			$node->appendChild( $node->ownerDocument->createElement( 'empty' ) );
		}
	}
}
 
