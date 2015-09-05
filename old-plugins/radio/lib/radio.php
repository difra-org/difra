<?php

namespace Difra\Plugins;

class Radio
{
	const BAD_CONFIG_PATH = 'unableToSaveConfig';
	const NO_ICECAST = 'noIceCastConfig';
	const NO_READ_ICESAST = 'noReadIceCast';
	private $validBitrate = [64, 128, 160, 192, 256, 320];
	private $validSampleRate = [22100, 44100, 48000, 96000, 192000];

	public static function getInstance()
	{

		static $_instance = null;
		return $_instance ? $_instance : $_instance = new self;
	}

	/**
	 * Добавляет треки на ротацию канала
	 * @param array $tracks
	 * @param string $channel
	 * @return bool
	 */
	public function addToChannel($tracks, $channel)
	{

		$db = \Difra\MySQL::getInstance();

		// забираем продолжительность для треков
		$tracks = array_map('intval', $tracks);
		$res = $db->fetchWithId("SELECT `id`, `playtime`, `group_id` FROM `tracks` WHERE `id` IN (" .
								implode(',', $tracks) . ")");

		$insertArray = [];
		foreach ($res as $data) {

			$insertArray[] = "('" . intval($data['id']) . "', '" . $db->escape($channel) . "', '" .
							 $this->_stringToTime($data['playtime']) . "', '" . intval($data['group_id']) . "')";
		}

		if (!empty($insertArray)) {
			$query = "INSERT IGNORE INTO `radio_tracks` (`id`, `channel`, `duration`, `group_id`) VALUES " .
					 implode(', ', $insertArray);
			$db->query($query);
			return true;
		}
		return false;
	}

	/**
	 * Возвращает список каналов по трекам в XML
	 * @param array $tracks
	 * @param \DOMNode $node
	 * @return void
	 */
	public function getTracksChannelsXML($tracks, $node)
	{

		$db = \Difra\MySQL::getInstance();
		$tracks = array_map('intval', $tracks);
		$channelsXML = $node->appendChild($node->ownerDocument->createElement('tracksInChannels'));
		$db->fetchXML($channelsXML,
			"SELECT `id`, `channel` FROM `radio_tracks` WHERE `id` IN (" . implode(', ', $tracks) . ")");
	}

	private function _stringToTime($stringTime)
	{

		$xTime = explode(':', $stringTime);
		return $xTime[0] * 60 + $xTime[1];
	}

	/**
	 * Возвращает объект радиоканала
	 * @param string $channelName
	 * @return \Difra\Plugins\Radio\Channel instance
	 */
	public function getChannel($channelName)
	{

		return \Difra\Plugins\Radio\Channel::get($channelName);
	}

	/**
	 * Возвращает список всех каналов
	 * @return array
	 */
	public function getChannels()
	{

		$db = \Difra\MySQL::getInstance();
		$q = "SELECT `mount`, `id` FROM `radio_channels` ORDER BY `mount` ASC";
		$res = $db->fetch($q);
		$channelsArray = [];
		if (!empty($res)) {
			foreach ($res as $data) {
				$channelsArray[$data['id']] = $data['mount'];
			}
			return $channelsArray;
		}
		return false;
	}

	/**
	 * @param \DOMElement $node
	 */
	public function getChannelsXML($node)
	{

		$channels = $this->getChannels();
		foreach ($channels as $k => $data) {
			/** @var \DOMElement $channelItem */
			$channelItem = $node->appendChild($node->ownerDocument->createElement('channel'));
			$channelItem->setAttribute('name', $data);
			$channelItem->setAttribute('id', $k);
		}
	}

	/**
	 * Проверяет точку монтирования на наличие в списке каналов
	 * @param string $name
	 * @return bool
	 */
	public function checkMount($name)
	{

		$db = \Difra\MySQL::getInstance();
		$q = "SELECT `id` FROM `radio_channels` WHERE `mount`='" . $db->escape($name) . "'";
		$r = $db->fetchRow($q);
		return isset($r['id']) ? true : false;
	}

	/**
	 * Проверяет валидность битрейта
	 * @param $bitrate
	 * @return bool
	 */
	public function checkBitrate($bitrate)
	{

		return in_array($bitrate, $this->validBitrate);
	}

	/**
	 * Проверяет валидность сэмплрейта
	 * @param $sampleRate
	 * @return bool
	 */
	public function checkSampleRate($sampleRate)
	{

		return in_array($sampleRate, $this->validSampleRate);
	}

	/**
	 * Создаёт новый радиоканал
	 * @param array $data
	 * @return bool|string
	 */
	public function createChannel($data)
	{

		$db = \Difra\MySQL::getInstance();

		if (empty($data)) {
			return false;
		}

		$insertData = [];
		foreach ($data as $key => $value) {
			$insertData[] = " `" . $db->escape($key) . "`='" . $db->escape($value) . "'";
		}

		$result = $this->makeIces($data);
		if ($result !== true) {
			return $result;
		}

		$query = "INSERT INTO `radio_channels` SET " . implode(', ', $insertData);
		$db->query($query);

		$result = $this->makeIceCast();
		if ($result !== true) {
			return $result;
		}

		return true;
	}

	/**
	 * Создаёт конфиги для ices
	 * @param array $data
	 * @return bool|string
	 */
	public function makeIces($data)
	{

		if (empty($data)) {
			return false;
		}

		// проверяем возможность записи конфига
		$savePath = DIR_DATA . 'radio/ices/';
		$modulePath = $savePath . 'modules/';

		if (!is_dir($modulePath)) {
			if (!mkdir($modulePath, 0777, true)) {
				return self::BAD_CONFIG_PATH;
			}
		}

		if (!is_dir($savePath) || !is_writable($savePath) || !is_dir($modulePath) || !is_writable($modulePath)) {
			return self::BAD_CONFIG_PATH;
		}

		// собираем конфиг для ices

		$configXML = new \DOMDocument();
		$configXML->formatOutput = true;
		$root =
			$configXML->appendChild($configXML->createElementNS('http://www.icecast.org/projects/ices',
				'ices:Configuration'));

		$playListNode = $root->appendChild($configXML->createElement('Playlist'));
		$playListNode->appendChild($configXML->createElement('File', 'playlist.txt'));
		$playListNode->appendChild($configXML->createElement('Randomize', '1'));
		$playListNode->appendChild($configXML->createElement('Type', 'perl'));
		$playListNode->appendChild($configXML->createElement('Module', $data['mount']));
		$playListNode->appendChild($configXML->createElement('Crossfade', '5'));

		$ExecutionNode = $root->appendChild($configXML->createElement('Execution'));
		$ExecutionNode->appendChild($configXML->createElement('Background', '1'));
		$ExecutionNode->appendChild($configXML->createElement('Verbose', '0'));
		$ExecutionNode->appendChild($configXML->createElement('BaseDirectory', '/usr/share/icecast2'));

		$StreamNode = $root->appendChild($configXML->createElement('Stream'));
		$ServerNode = $StreamNode->appendChild($configXML->createElement('Server'));

		$ServerNode->appendChild($configXML->createElement('Hostname', $data['hostname']));
		$ServerNode->appendChild($configXML->createElement('Port', $data['port']));
		$ServerNode->appendChild($configXML->createElement('Password', $data['password']));
		$ServerNode->appendChild($configXML->createElement('Protocol', 'http'));

		$StreamNode->appendChild($configXML->createElement('Mountpoint', '/' . $data['mount'] . '-nonstop'));
		$StreamNode->appendChild($configXML->createElement('Name', $data['name']));
		$StreamNode->appendChild($configXML->createElement('Genre', $data['genre']));
		$StreamNode->appendChild($configXML->createElement('Description', $data['description']));
		$StreamNode->appendChild($configXML->createElement('URL', $data['url']));
		$StreamNode->appendChild($configXML->createElement('Public', '0'));
		$StreamNode->appendChild($configXML->createElement('Bitrate', $data['bitrate']));
		$StreamNode->appendChild($configXML->createElement('Reencode', $data['reencode']));
		$StreamNode->appendChild($configXML->createElement('Samplerate', $data['samplerate']));
		$StreamNode->appendChild($configXML->createElement('Channels', '2'));

		$configXML->save($savePath . $data['mount'] . '.conf');

		// собираем перловый модуль для ices

		$rotationPath = DIR_PLUGINS . 'radio/bin/rotation.php';

		$perlText = 'use strict;';
		$perlText .= "\n" . 'use XML::Simple;';
		$perlText .= "\n" . 'my $title = \'none\';';
		$perlText .= "\n" . 'sub ices_init {';
		$perlText .= "\n\t" . 'print "Perl subsystem Initializing:\n";';
		$perlText .= "\n\t" . 'return 1;';
		$perlText .= "\n" . '}';
		$perlText .= "\n" . 'sub ices_shutdown {';
		$perlText .= "\n\t" . 'print "Perl subsystem shutting down:\n";';
		$perlText .= "\n" . '}';
		$perlText .= "\n" . 'sub ices_get_next {';
		$perlText .= "\n\t" . 'print "Perl subsystem quering for new track:\n";';
		$perlText .= "\n\t" . 'my $res = `php-cgi -q ' . $rotationPath . ' name=' . $data['mount'] . '`;';
		$perlText .= "\n\t" . 'my $simple = XML::Simple->new();';
		$perlText .= "\n\t" . 'my $data = $simple->XMLin( $res );';
		$perlText .= "\n\t" . '$title = $data->{title};';
		$perlText .= "\n\t" . 'return $data->{filename};';
		$perlText .= "\n" . '}';
		$perlText .= "\n" . 'sub ices_get_metadata {';
		$perlText .= "\n\t" . 'print "Track title: " + $title;';
		$perlText .= "\n\t" . 'return $title;';
		$perlText .= "\n" . '}';
		$perlText .= "\n" . 'sub ices_get_lineno {';
		$perlText .= "\n\t" . 'return 1;';
		$perlText .= "\n" . '}';
		$perlText .= "\n" . 'return 1;';

		$fh = fopen($modulePath . $data['mount'] . '.pm', "w");
		fwrite($fh, $perlText);
		fclose($fh);

		return true;
	}

	/**
	 * Создаёт конфиг для icecast'а
	 * @return bool|string
	 */
	public function makeIceCast()
	{

		$originalConfig = DIR_PLUGINS . 'radio/bin/icecast.xml';
		$savePath = DIR_DATA . 'radio/';

		if (!is_dir($savePath)) {
			if (!mkdir($savePath, 0777, true)) {
				return self::BAD_CONFIG_PATH;
			}
		}

		if (!file_exists($originalConfig)) {
			return self::NO_ICECAST;
		}

		if (!is_readable($originalConfig)) {
			return self::NO_READ_ICESAST;
		}

		$originalXML = new \DOMDocument('1.0', 'UTF-8');
		$originalXML->preserveWhiteSpace = false;
		$originalXML->substituteEntities = true;
		$originalXML->formatOutput = true;
		$originalXML->load($originalConfig);

		// забираем данные о каналах
		$db = \Difra\MySQL::getInstance();
		$res = $db->fetch("SELECT `mount`, `password` FROM `radio_channels` WHERE `onLine`=1");
		if (!empty($res)) {

			/** @var \DOMElement $root */
			$root = $originalXML->getElementsByTagName('icecast')->item(0);
			/** @var \DOMElement $limitsNode */
			$limitsNode = $root->getElementsByTagName('limits')->item(0);
			$oldSourcesNode = $limitsNode->getElementsByTagName('sources')->item(0);
			$newSourcesNode = $root->appendChild($originalXML->createElement('sources', count($res) * 2));
			$limitsNode->replaceChild($newSourcesNode, $oldSourcesNode);

			foreach ($res as $data) {
				$mountNode = $root->appendChild($originalXML->createElement('mount'));
				$mountNode->appendChild($originalXML->createElement('mount-name', '/' . $data['mount']));
				$mountNode->appendChild($originalXML->createElement('password', $data['password']));
				$mountNode->appendChild($originalXML->createElement('charset', 'UTF-8'));
				$mountNode->appendChild($originalXML->createElement('fallback-mount',
					'/' . $data['mount'] . '-nonstop'));
				$mountNode->appendChild($originalXML->createElement('fallback-override', '1'));

				$fallBackNode = $root->appendChild($originalXML->createElement('mount'));
				$fallBackNode->appendChild($originalXML->createElement('mount-name',
					'/' . $data['mount'] . '-nonstop'));
				$fallBackNode->appendChild($originalXML->createElement('password', $data['password']));
				$fallBackNode->appendChild($originalXML->createElement('charset', 'UTF-8'));
			}
		}

		$originalXML->save($savePath . 'icecast.xml');
		return true;
	}

	/**
	 * Удаляет канал
	 * @param string $mountPoint
	 */
	public function deleteChannel($mountPoint)
	{

		$db = \Difra\MySQL::getInstance();
		$q = "DELETE FROM `radio_channels` WHERE `mount`='" . $db->escape($mountPoint) . "'";
		$db->query($q);
	}
}

