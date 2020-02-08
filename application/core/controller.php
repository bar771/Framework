<?php
namespace Framework;

use Framework\Util;
use Framework\Database;
use Framework\Models\Security;

class Controller {
	protected $database;

	private $title = '';
	private $vars = array();

	/*
	 * @param string $param
	 * Constructor method
	**/
	function __construct($param = '') {
		$opt = array('db' => DB_NAME, 'host' => DB_HOST, 'user' => DB_USER, 'password' => DB_PASS);
		$this->database = new Database($opt);
	}

	public function init() {
	}

	/*
	 * @param string $view
	 * @return boolean
	**/
	protected function renderView($view) {
		$path = Util::getFile('views/'.$view.'.php');
		if (!empty($path)) {
			return include $path;
		}
		return false;
	}

	/*
	 * @param string $str
	 * @return object
	**/
	public static function getRequest($str) {
		return (isset($_GET[$str]) ? Security::protect_xss($_GET[$str]) : null);
	}

	/*
	 * @param string $str
	 * @return object
	**/
	public static function postRequest($str) {
		return (isset($_POST[$str]) ? Security::protect_xss($_POST[$str]) : null);
	}

	/*
	 * @return string
	**/
	public function getReferer() {
		return (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '/');
	}

	/*
	 * @return string
	**/
	public function getTitle() {
		return WEBSITE_NAME.'&nbsp;-&nbsp;'.$this->title;
	}

	/*
	 * @param string $title
	**/
	public function setTitle($title) {
		$this->title = $title;
	}

	/*
	 * @return array
	**/
	public function getVars() {
		return $this->vars;
	}

	/*
	 * @param array $vars
	**/
	public function setVars($vars) {
		$this->vars = $vars;
	}

	// Park wildcard.domain or domain to the folder of the framework.
	public function getHTTPHost() {
		return $_SERVER['HTTP_HOST'];
	}

	function getSubDomain($domain = 'example.com'){
		$sub = $this->getHTTPHost();
		if (preg_match('/((.*).'.$domain.')/', $sub)) 
			return explode('.', $sub)[0]; // siteurl
	}

	function getDomain() {
		$domain = $this->getHTTPHost();
		if (preg_match('/((.*).(com|net|co.il))|(www.(.*).(com|net|co.il))/', $domain)) 
			return $domain;
	}
}

?>
