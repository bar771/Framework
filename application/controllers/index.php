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

	function login() {
		$errors = '';
		
		$uname = Security::protect_xss($_POST['username']);
		$pword = Security::protect_xss($_POST['password']);

		if (empty($errors)) {

			$redirect = $_GET['redirect'];
			
			// Log in.

			header('location: '.
				(isset($_GET['redirect']) ? WEBSITE_DOMAIN.urldecode($_GET['redirect']) : '/'));
			die;
		}

	}

	function register() {
		$uname = Security::protect_xss($_POST['username']);
		$pword = Security::protect_xss($_POST['password']);
		$mail = Security::protect_xss($_POST['email']);

		$salt = '';

		$this->database->prepare('INSERT INTO `users` (username,password,email,salt,time,lastlogin) VALUES ()', 
			array());


	}

}

?>
