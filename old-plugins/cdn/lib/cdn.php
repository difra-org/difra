<?php

namespace Difra\Plugins;
use Difra;

class CDN {

	private $settings = array( 'timeout'    => 3,
				   'cachetime'  => 10,
				   'failtime'   => 20,
				   'selecttime' => 24 );

	static public function getInstance() {
		static $_instance = null;
		return $_instance ? $_instance : $_instance = new self;
	}

	/**
	 * Возвращает список всех хостов в CDN
	 *
	 * @param bool $withFailed
	 *
	 * @return array
	 */
	public function getHosts( $withFailed = false ) {

		$db    = \Difra\MySQL::getInstance();
		$where = '';
		if( !$withFailed ) {
			$where = " WHERE `status`='ok' ";
		}

		$res = $db->fetch( "SELECT * FROM `cdn_hosts` " . $where . " ORDER BY `host` ASC" );

		return $res;
	}

	/**
	 * Возвращает xml со списком хостов в CDN
	 *
	 * @param \DOMNode$node
	 * @param bool    $withFailed
	 */
	public function getHostsXML( $node, $withFailed = false ) {

		$hosts = $this->getHosts( $withFailed );
		if( empty( $hosts ) ) {
			$node->appendChild( $node->ownerDocument->createElement( 'empty' ) );
			return;
		}

		foreach( $hosts as $data ) {
			$hostNode = $node->appendChild( $node->ownerDocument->createElement( 'host' ) );
			foreach( $data as $key=> $value ) {

				$hostNode->setAttribute( $key, $value );
			}
		}
	}

	/**
	 * Удаляет хост
	 * @param integer $id
	 */
	public function delete( $id ) {

		$db = \Difra\MySQL::getInstance();
		$db->query( "DELETE FROM `cdn_hosts` WHERE `id`='" . intval( $id ) . "'" );
		$this->_cleanWork();
	}

	/**
	 * Возвращает данные хоста
	 * @param integer $id
	 *
	 * @return array
	 */
	public function getHost( $id ) {

		$db = \Difra\MySQL::getInstance();
		return $db->fetchRow( "SELECT * FROM `cdn_hosts` WHERE `id`='" . intval( $id ) . "'" );
	}

	/**
	 * Возвращает данные хоста в xml
	 *
	 * @param \DOMNode $node
	 * @param          $id
	 */
	public function getHostXML( $node, $id ) {

		$hostData = $this->getHost( $id );
		if( empty( $hostData ) ) {
			$node->appendChild( $node->ownerDocument->createElement( 'empty' ) );
			return;
		}

		foreach( $hostData as $key=> $value ) {
			$node->setAttribute( $key, $value );
		}
	}

	/**
	 * Проверят хост
	 * @param string  $host
	 * @param integer $port
	 *
	 * @return string
	 */
	public function checkHost( $host, $port ) {

		$hostString = $host . '/status.txt';
		$this->_getSettings();
		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_HEADER, 0 );

		curl_setopt( $ch, CURLOPT_TIMEOUT, $this->settings['timeout'] );
		curl_setopt( $ch, CURLOPT_FAILONERROR, 1 );
		curl_setopt( $ch, CURLOPT_USERAGENT, 'Difra cdn checker' );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $ch, CURLOPT_URL, $hostString );
		curl_setopt( $ch, CURLOPT_PORT, $port );

		$ret = strtolower( trim( curl_exec( $ch ) ) );
		switch( $ret ) {
		case 'ok':
			$return = 'ok';
			$query  = "UPDATE `cdn_hosts` SET `status` = 'ok', `checked` = NOW()";
			break;
		case 'busy':
			$return = 'busy';
			$query  = "UPDATE `cdn_hosts` SET `status` = 'busy', `checked` = NOW(), `failed` = NOW()";
			break;
		default:
			$return = 'fail';
			$query  = "UPDATE `cdn_hosts` SET `status` = 'fail', `checked` = NOW(), `failed` = NOW()";
			break;
		}

		curl_close( $ch );

		$db = \Difra\MySQL::getInstance();
		$query .= " WHERE `host`='" . $db->escape( $host ) . "' AND `port`='" . intval( $port ) . "'";
		$db->query( $query );
		$this->_cleanWork();

		return $return;
	}

	/**
	 * Проверяет все CDN хосты
	 */
	public function checkHosts() {

		$db    = \Difra\MySQL::getInstance();
		$query = "SELECT `id`, `host`, `port` FROM `cdn_hosts`";
		$res   = $db->fetch( $query );

		if( !empty( $res ) ) {

			$this->_getSettings();
			$ch = curl_init();

			$testResults = array();

			curl_setopt( $ch, CURLOPT_HEADER, 0 );

			curl_setopt( $ch, CURLOPT_TIMEOUT, $this->settings['timeout'] );
			curl_setopt( $ch, CURLOPT_FAILONERROR, 1 );
			curl_setopt( $ch, CURLOPT_USERAGENT, 'Difra cdn checker' );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );

			foreach( $res as $data ) {

				$hostString = $data['host'] . '/status.txt';

				curl_setopt( $ch, CURLOPT_URL, $hostString );
				curl_setopt( $ch, CURLOPT_PORT, $data['port'] );
				$testResults[$data['id']] = strtolower( trim( curl_exec( $ch ) ) );
			}
			curl_close( $ch );

			// разбираем результатики
			$query = array();
			if( !empty( $testResults ) ) {

				foreach( $testResults as $id => $result ) {

					switch( $result ) {
					case 'ok':
						$query[] = "UPDATE `cdn_hosts` SET `status` = 'ok', `checked` = NOW()
									WHERE `id` = '" . intval( $id ) . "'";
						break;
					case 'busy':
						$query[] = "UPDATE `cdn_hosts` SET `status` = 'busy', `checked` = NOW(), `failed` = NOW()
									WHERE `id` = '" . intval( $id ) . "'";
						break;
					default:
						$query[] = "UPDATE `cdn_hosts` SET `status` = 'fail', `checked` = NOW(), `failed` = NOW()
									WHERE `id` = '" . intval( $id ) . "'";
						break;
					}
				}

				// апдейтим статусы cdn хостов
				$db->query( $query );
				$this->_cleanWork();
			}
		}
	}

	/**
	 * Добалвяет хост в базу
	 * @param $host
	 * @param $port
	 *
	 * @return bool
	 */
	public function addHost( $host, $port ) {

		$db = \Difra\MySQL::getInstance();
		// проверяем есть ли уже такой хост
		$res = $db->fetchRow( "SELECT `id` FROM `cdn_hosts` WHERE `host` = '" . $db->escape( $host ) . "' AND `port` = '" . intval( $port ) . "'" );
		if( !empty( $res ) ) {
			return false;
		}

		$query = "INSERT INTO `cdn_hosts` (`host`, `port`) VALUES ('" . $db->escape( $host ) . "', '" . intval( $port ) . "')";
		$db->query( $query );
		$this->_cleanWork();
		return true;
	}

	public function saveHost( $id, $host, $port ) {

		$db = \Difra\MySQL::getInstance();
		$db->query( "UPDATE `cdn_hosts` SET `host` = '" . $db->escape( $host ) . "', `port`='" . intval( $port ) .
			    "' WHERE `id`='" . intval( $id ) . "'" );
		$this->_cleanWork();
	}

	private function _getSettings() {

		$tmp = \Difra\Config::getInstance()->get( 'CDN' );
		if( !empty( $tmp ) ) {
			$this->settings = $tmp;
		}
	}

	/**
	 * Возвращает xml с настройками cdn
	 *
	 * @param \DOMNode $node
	 */
	public function getSettingsXML( $node ) {

		$this->_getSettings();
		foreach( $this->settings as $key=> $value ) {
			$node->setAttribute( $key, $value );
		}
	}

	/**
	 * Сохраняет настройки для CDN
	 *
	 * @param array $settingsArray
	 */
	public function saveSettings( $settingsArray ) {

		if( !empty( $settingsArray ) ) {
			\Difra\Config::getInstance()->set( 'CDN', $settingsArray );
		}
	}

	/**
	 * Очищает временную таблицу
	 */
	private function _cleanWork() {

		\Difra\MySQL::getInstance()->query( "DELETE FROM `cdn_hosts_work`" );
	}

	/**
	 * Апдейтит дату выбора хоста и запоминает его в кэше
	 * @param integer $id
	 * @param         $host
	 */
	private function _updateSelected( $id, $host ) {

		$db = \Difra\MySQL::getInstance();
		$this->_getSettings();
		$db->query( "UPDATE `cdn_hosts_work` SET `selected` = NOW() WHERE `id`='" . intval( $id ) . "'" );
		//\Difra\Cache::getInstance()->put( 'cdn_host', array( 'id' => $id, 'host' => $host ), $this->settings['cachetime'] * 60 );
	}

	/**
	 * Выбирает рабочий cdn хост
	 */
	public function selectHost() {

		$this->_getSettings();

		$db    = \Difra\MySQL::getInstance();
		$res   = null;
		$stage = null;

		// stage 1 — select from 'ok' hosts with softer load of new cdn hosts
		try {
			$queryWrk = "SELECT `id`, `host`, `port` FROM `cdn_hosts_work`
				WHERE (`status`='ok'
				OR `failed`<DATE_SUB(NOW(),INTERVAL " . intval( $this->settings['failtime'] ) . " MINUTE))
				AND `created`<DATE_SUB(NOW(),INTERVAL " . intval( rand( 0, 24 ) ) . " HOUR)
				ORDER BY `selected`
				LIMIT 1";

			$res      = $db->fetchRow( $queryWrk );
			if( !$res ) {
				$db->query( 'REPLACE INTO `cdn_hosts_work` SELECT * FROM `cdn_hosts`' );
				$res = $db->fetchRow( $queryWrk );
			}
			$stage = 1;
		} catch( \Difra\Exception $ex ) {
		}

		// stage 2 — select from 'ok' hosts without softer load of new cdn hosts
		if( !$res ) {
			try {
				$query = "SELECT `id`, `host`, `port` FROM `cdn_hosts_work`
				WHERE `status`='ok'
				AND `failed`<DATE_SUB(NOW(),INTERVAL " . intval( $this->settings['failtime'] ) . " MINUTE)
				ORDER BY `selected`
				LIMIT 1";
				$res   = $db->fetchRow( $query );
				$stage = 2;
			} catch( \Difra\Exception $ex ) {
			}
		}

		// stage 3 — select from busy hosts
		if( !$res ) {
			try {
				$query = "SELECT `id`, `host`, `port` FROM `cdn_hosts_work`
				WHERE `status`='busy'
				ORDER BY `selected` DESC
				LIMIT 1";
				$res   = $db->fetchRow( $query );
				$stage = 3;
			} catch( \Difra\Exception $ex ) {
			}
		}

		// stage 4 - select from hosts that haven't been checked for a long time
		if( !$res ) {
			try {
				$query = "SELECT `id`, `host`, `port` FROM `cdn_hosts`
				WHERE `failed`<DATE_SUB(NOW(),INTERVAL " . intval( $this->settings['failtime'] ) . " MINUTE)
				ORDER BY `selected`
				LIMIT 1";
				$res   = $db->fetchRow( $query );
				$stage = 4;
			} catch( \Difra\Exception $ex ) {
			}
		}

		if( !empty( $res ) ) {
			$rootNode = \Difra\Action::getInstance()->controller->root;
			$rootNode->setAttribute( 'cdn_host', $res['host'] . ':' . $res['port'] );
			$rootNode->setAttribute( 'cdn_host_id', $res['id'] );
			$this->_updateSelected( $res['id'], $res['host'] . ':' . $res['port'] );
			if( \Difra\Debugger::getInstance()->isEnabled() ) {
				$rootNode->setAttribute( 'cdn_stage', $stage );
			}
			return;
		}
	}
}
