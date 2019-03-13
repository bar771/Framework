<?php
namespace Framework\Controllers;

use Framework\Util;

Util::loadModel('user');
Util::loadModel('security');

use Framework\Controller as BaseObject;

use Framework\Models\Security;
use Framework\Models\User;

class Index extends BaseObject {
	public $user = null;

	function __construct() {
		parent::__construct();
		$this->user = new User($this->database);
	}

	function init() {
		parent::init();

		$returnURL = 'return';
		$campaign = 'campaign';
		if ($this->getRequest($returnURL) != null) {
			// Redirect to other website.
			header('location: '.urldecode($this->getRequest($returnURL)));
		}
		else if ($this->getRequest($campaign) != null) {
		}

		$this->setTitle('Home page');
		$this->renderView('index');
	}

	/*
	 * @return void
	**/
	function login() {
		if ($this->user->authenticateSession() || $this->user->isOnline())
			header('Content-Type: /');
		$this->setTitle('Login');
		$this->renderView('login');
	}

	/*
	 * @return void
	**/
	function register() {
		if ($this->user->authenticateSession() || $this->user->isOnline())
			header('Content-Type: /');
		$this->setTitle('Register');
		$this->renderView('register');
	}

	/*
	 * @return void
	**/
	function logout() {
		if (!$this->user->authenticateSession() || !$this->user->isOnline())
			header('Content-Type: /');

		if (USER_REFERER == '/process/logout')
			header('location: /');
		header('location: '.USER_REFERER);
	}

	/*
	 * @param integer $userID
	 * @return void
	**/
	function profile($userID = 0) {
		$this->setTitle('Profile');
		$this->renderView('profile');
	}
}

?>
