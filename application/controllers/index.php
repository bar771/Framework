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

		$this->setTitle('Home page');
		$this->renderView('index');
	}

	// https://github.com/ilCapo01/websmaking/blob/master/public_html/includes/user.php
	function mysites($siteID = -1) {
		$this->setTitle('');
		if ($siteID == -1) {
			// Show all sites.

			$this->renderView('allSites');
		}

		// Manage specific site.

		$this->setVars(['siteID' => $siteID]);
		$this->renderView('siteManagement');
	}

	function login() {
		$uname = Security::protect_xss($_POST['username']);
		$pword = Security::protect_xss($_POST['password']);

		$errors = '';
		if (empty($errors)) {
			$redirect = $_GET['redirect'];

			// Log user in.

			header('location: '.
				(isset($_GET['redirect']) ? WEBSITE_DOMAIN.urldecode($_GET['redirect']) : '/'));
			die;
		}

	}

	function register() {
		$uname = Security::protect_xss($_POST['username']);
		$pword = Security::protect_xss($_POST['password']);
		$mail = Security::protect_xss($_POST['email']);

		$errors = '';
		if (empty($errors)) {
			$this->database->prepare('INSERT INTO `users` (username,password,email,time,lastlogin) VALUES ()',
				array());
				header('location: /'); die;
		}
		header('location: '.HTTP_REFERER); die;
	}

}

?>
