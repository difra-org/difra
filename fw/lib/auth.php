<?php

namespace Difra;

use Difra\Envi\Session;

/**
 * Class Auth
 * Auth adapter. User logins, logouts and user sessions.
 * This is a layer between actual auth and framework code.
 * It allows to write any auth plugins without direct calls to them and without registering multiple boring handlers.
 *
 * @package Difra
 */
class Auth
{
	public $logged = false;
	public $id = null;
	public $data = null;
	public $moderator = false;
	public $additionals = null;
	public $type = 'user';

	/**
	 * Singleton
	 *
	 * @return Auth
	 */
	static public function getInstance()
	{
		static $_instance = null;
		return $_instance ? $_instance : $_instance = new self;
	}

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->load();
	}

	/**
	 * Get auth data as XML node
	 *
	 * @param \DOMNode|\DOMElement $node
	 */
	public function getAuthXML($node)
	{
		$authNode = $node->appendChild($node->ownerDocument->createElement('auth'));
		if (!$this->logged) {
			$authNode->appendChild($node->ownerDocument->createElement('unauthorized'));
			return;
		} else {
			/** @var \DOMElement $subNode */
			$subNode = $authNode->appendChild($node->ownerDocument->createElement('authorized'));
			$subNode->setAttribute('id', $this->id);
			$subNode->setAttribute('userid', $this->getId());
			$subNode->setAttribute('moderator', $this->moderator);
			$subNode->setAttribute('type', $this->type);
			if (!empty($this->additionals)) {
				foreach ($this->additionals as $k => $v) {
					$subNode->setAttribute($k, $v);
				}
			}
		}
	}

	/**
	 * Log in
	 *
	 * @param int   $userId
	 * @param array $data
	 * @param array $additionals
	 */
	public function login($userId, $data = null, $additionals = null)
	{
		$this->id = $userId;
		$this->data = $data;
		$this->additionals = $additionals;
		$this->logged = true;
		$this->save();
	}

	/**
	 * Log out
	 */
	public function logout()
	{
		$this->id = $this->data = $this->additionals = null;
		$this->logged = false;
		$this->save();
	}

	/**
	 * Update user session
	 */
	public function update()
	{
		$this->save();
	}

	/**
	 * Save auth data in session
	 */
	private function save()
	{
        Session::start();
		if ($this->logged) {
			$_SESSION['auth'] = [
				'id'          => $this->id,
				'data'        => $this->data,
				'additionals' => $this->additionals
			];
		} else {
			if (isset($_SESSION['auth'])) {
				unset($_SESSION['auth']);
			}
		}
	}

	/**
	 * Get auth data from session
	 *
	 * @return bool
	 */
	private function load()
	{
		if (!isset($_SESSION['auth'])) {
			return false;
		}
		$this->id = $_SESSION['auth']['id'];
		$this->data = $_SESSION['auth']['data'];
		$this->additionals = $_SESSION['auth']['additionals'];
		$this->moderator = ($_SESSION['auth']['data']['moderator'] == 1) ? true : false;
		$this->type = isset($_SESSION['auth']['data']['type']) ? $_SESSION['auth']['data']['type'] : 'user';
		return $this->logged = true;
	}

	/**
	 * Get current user's id. Or null if user is not authorized.
	 *
	 * @return int|null
	 */
	public function getId()
	{
		return isset($this->data['id']) ? $this->data['id'] : null;
	}

	/**
	 * Get user type
	 *
	 * @return string|null
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * Is user authorized?
	 *
	 * @return bool
	 */
	public function isLogged()
	{
		return $this->logged;
	}

	/**
	 * Throws exception if user is not authorized.
	 * For fast authorization check in methods, when methodnameAuthAction is not what you want.
	 */
	public function required()
	{
		if (!$this->logged) {
			throw new exception('Authorization required');
		}
	}

	/**
	 * Set user data
	 *
	 * @param array $additionals
	 */
	public function setAdditionals($additionals)
	{
		$this->additionals = $additionals;
		$this->save();
	}

	/**
	 * Get user data
	 *
	 * @return array
	 */
	public function getAdditionals()
	{
		return $this->additionals;
	}

	/**
	 * Is user a moderator?
	 * TODO: remove this.
	 *
	 * @return bool
	 */
	public function isModerator()
	{
		return $this->moderator;
	}
}
