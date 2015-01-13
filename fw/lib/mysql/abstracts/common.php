<?php

namespace Difra\MySQL\Abstracts;

/**
 * Абстрактный класс для реализации адаптеров к MySQL
 * Class Common
 *
 * @package Difra\MySQL
 */
abstract class Common {

	/** @var array|null */
	protected $config = null;
	/** @var bool */
	protected $connected = null;
	/** @var string|null */
	protected $error = null;
	/** @var int */
	public $queries = 0;

	/**
	 * Абстрактные методы

	 */

	/**
	 * Инициализация соединения с базой
	 */
	abstract protected function realConnect();

	/**
	 * Отправка запроса в базу
	 *
	 * @param string $query
	 */
	abstract protected function realQuery( $query );

	/**
	 * Получение данных из базы
	 *
	 * @param string $query
	 * @param bool   $replica
	 *
	 * @return array|null
	 */
	abstract protected function realFetch( $query, $replica = false );

	/**
	 * Обезопасивает строку для помещения в SQL-запрос
	 *
	 * @param $string
	 *
	 * @return string
	 */
	abstract protected function realEscape( $string );

	/**
	 * Возвращает id (primary key) последней вставленной строки
	 *
	 * @return int
	 */
	abstract protected function getLastId();

	/**
	 * Возвращает количество строк, затронутых последним запросом
	 *
	 * @return int
	 */
	abstract protected function getAffectedRows();

	/**
	 * Начать транзакцию
	 */
	protected function transactionStart() {
	}

	/**
	 * Закончить транзакцию
	 */
	protected function transactionCommit() {
	}

	/**
	 * Отменить транзакцию
	 */
	protected function transactionCancel() {
	}

	/**
	 * Функционал

	 */

	/**
	 * Устанавливает соединение с базой
	 *
	 * @throws \Difra\Exception
	 * @return void
	 */
	protected function connect() {

		if( $this->connected === true ) {
			return;
		} elseif( $this->connected === false ) {
			throw new \Difra\Exception( 'MySQL connection is not available' );
		}
		$this->connected = false;
		try {
			$this->realConnect();
		} catch( \Difra\Exception $ex ) {
			$ex->notify();
			throw new \Difra\Exception( 'MySQL connection is not available: ' . $ex->getMessage() );
		}
		$this->connected = true;
	}

	/**
	 * Сделать запрос в базу
	 *
	 * @throws \Difra\Exception
	 *
	 * @param string|array $query SQL-запрос
	 *
	 * @return void
	 */
	public function query( $query ) {

		if( !is_array( $query ) ) {
			$this->connect();
			$this->realQuery( $query );
			$this->queries++;
			\Difra\Debugger::addDBLine( 'MySQL', $query );
		} else {
			try {
				$this->transactionStart();
				foreach( $query as $subQuery ) {
					$this->query( $subQuery );
				}
				$this->transactionCommit();
			} catch( \Difra\Exception $ex ) {
				$this->transactionCancel();
				throw new \Difra\Exception( 'MySQL transaction failed because of ' . $ex->getMessage() );
			}
		}
	}

	/**
	 * Возвращает результат запроса
	 *
	 * @param string $query   SQL-запрос
	 * @param bool   $replica Позволить читать данные из реплики
	 *
	 * @return array
	 */
	public function fetch( $query, $replica = false ) {

		$this->connect();
		\Difra\Debugger::addDBLine( 'MySQL', $query );
		$this->queries++;
		return $this->realFetch( $query, $replica );
	}

	/**
	 * Безопасно «обернуть» строку для SQL-запроса
	 *
	 * @param string|array $data Строка или массив строк
	 *
	 * @return string|array
	 */
	public function escape( $data ) {

		$this->connect();
		if( !is_array( $data ) ) {
			return $this->realEscape( (string)$data );
		}
		$t = array();
		foreach( $data as $k => $v ) {
			$t[$this->escape( $k )] = $this->escape( (string)$v );
		}
		return $t;
	}

	/**
	 * Определение, доступен ли модуль
	 *
	 * @return bool
	 */
	public static function isAvailable() {

		return false;
	}

	/**
	 * Конструктор
	 */
	public function __construct() {

		$this->config = \Difra\Config::getInstance()->get( 'db' );
		if( empty( $this->config['hostname'] ) ) {
			$this->config['hostname'] = '';
		}
		if( empty( $this->config['username'] ) ) {
			$this->config['username'] = \Difra\Envi::getSite();
		}
		if( empty( $this->config['password'] ) ) {
			$this->config['password'] = '';
		}
		if( empty( $this->config['database'] ) ) {
			$this->config['database'] = \Difra\Envi::getSite();
		}
	}

	/**
	 * Проверка наличия соединения с базой
	 *
	 * @return bool
	 */
	public function isConnected() {

		try {
			$this->connect();
		} catch( \Difra\Exception $ex ) {
			return false;
		}
		return $this->connected ? true : false;
	}

	/**
	 * Возврат текста ошибки
	 *
	 * @return string|null
	 */
	public function getError() {

		return $this->error;
	}

	/**
	 * Возвращает результаты запроса в ассоциативном массиве id => row
	 *
	 * @param string $query   SQL-запрос
	 * @param bool   $replica Позволить читать данные из реплики
	 *
	 * @return array
	 */
	public function fetchWithId( $query,
		/** @noinspection PhpUnusedParameterInspection */
				     $replica = false ) {

		$this->connect();
		$result = $this->fetch( $query );
		$sorted = array();
		if( !empty( $result ) ) {
			foreach( $result as $row ) {
				$sorted[$row['id']] = $row;
			}
		}
		return $sorted;
	}

	/**
	 * Берет значения из массива и возвращает их в виде дерева XML
	 *
	 * @param \DOMElement|\DOMNode $node
	 * @param                      $row
	 *
	 * @return bool
	 */
	private function getRowAsXML( $node, $row ) {

		if( empty( $row ) ) {
			return false;
		}
		foreach( $row as $k => $v ) {
			if( trim( $v ) and preg_match( '/^(i|s|a|o|d)(.*);/si', $v ) ) { // serialize!
				$arr = @unserialize( $v );
				$subnode = $node->appendChild( $node->ownerDocument->createElement( $k ) );
				$this->getRowAsXML( $subnode, $arr );
			} else {
				$node->setAttribute( $k, $v );
			}
		}
		return true;
	}

	/**
	 * Возвращает одну строку результатов запроса
	 *
	 * @param string $query   SQL-запрос
	 * @param bool   $replica Позволить читать данные из реплики
	 *
	 * @return array|bool
	 */
	public function fetchRow( $query, $replica = false ) {

		$data = $this->fetch( $query, $replica );
		return isset( $data[0] ) ? $data[0] : false;
	}

	/**
	 * Возвращает одно значение из результатов запроса
	 *
	 * @param string $query   SQL-запрос
	 * @param bool   $replica Позволить читать данные из реплики
	 *
	 * @return mixed|null
	 */
	public function fetchOne( $query, $replica = false ) {

		$data = $this->fetchRow( $query, $replica );
		return !empty( $data ) ? array_shift( $data ) : null;
	}

	/**
	 * Возвращает результат SQL-запроса в виде дерева XML
	 *
	 * @param \DOMNode $node    XML-Нода
	 * @param string   $query   Запрос
	 * @param bool     $replica Позволить читать данные из реплики
	 *
	 * @return bool
	 */
	public function fetchXML( $node, $query, $replica = false ) {

		$data = $this->fetch( $query, $replica );
		if( empty( $data ) ) {
			return false;
		}
		foreach( $data as $row ) {
			$subnode = $node->appendChild( $node->ownerDocument->createElement( 'item' ) );
			$this->getRowAsXML( $subnode, $row );
		}
		return true;
	}

	/**
	 * Возвращает строку из базы данных в XML
	 *
	 * @param \DOMElement $node
	 * @param string      $query
	 * @param bool        $replica
	 *
	 * @return bool
	 */
	public function fetchRowXML( $node, $query, $replica = false ) {

		$row = $this->fetchRow( $query, $replica );
		return $this->getRowAsXML( $node, $row );
	}

	/**
	 * Возвращает found_rows()
	 *
	 * @return int
	 */
	public function getFoundRows() {

		return $this->fetchOne( "SELECT FOUND_ROWS()" );
	}

	/**
	 * Определяет доступность mysqlnd
	 *
	 * @return bool
	 */
	protected function isND() {

		static $nd = null;
		return $nd ? $nd : $nd = extension_loaded( 'mysqlnd' );
	}
}