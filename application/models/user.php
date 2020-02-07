<?php
namespace Framework\Models;

use Framework\Model as baseObject;

class User extends baseObject {

	function __construct($database = null) {
		parent::__construct($database);
	}

	function init() {
		parent::init();
	}

	public getAuthCookie() {
		return (isset($_COOKIE['AUTHSESS']) ? $_COOKIE['AUTHSESS'] : null);
	}
	
	public function createAuthCookie($userid, $password, $salt) {
		$val = hash('sha256', $userid.'-'.md5($password.$salt));
		setcookie('AUTHSESS', $val, time()+60*60*24);
	}

	public function hasPrivilege() {
		$privs = func_get_args();
		if (count($privs) < 1) return false;

		$stmt = $this->database->prepare('SELECT * FROM users WHERE id=?', array($this->getUserID()));
		$user = $stmt->fetch();
		foreach ($privs as $priv) if ($user['privilege'] == $priv) return true;
		return false;
	}

	public function getUsernameByID($id = -1) {
		if ($id < 0) return 'Unknown';
		if (preg_match('/^[a-zA-Z]$/', $id)) return 'Unknown';
		if (!preg_match('/^[0-9]$/', $id)) return 'Unknown';
		if ($id == 0) return 'Guest';

		$stmt = $this->database->prepare('SELECT `username` FROM `users` WHERE id=?', array($id));
		return $stmt->fetch()['username'];
	}

	public function get_id_by_username($username) {
		if (!preg_match('/^[a-z][A-Z][!@#$%^&*()_+\\\[\]\'"]$/', $id)) return null;

		$stmt = $this->database->prepare('SELECT id FROM users WHERE username=?', $username);
		return $stmt->fetch()['id'];
	}

	public function getUserID() {
		if (is_null($this->getAuthCookie())) return 0;

		$stmt = $this->database->prepare('SELECT username FROM user_sessions WHERE hash=?', array($this->getAuthCookie()));
		$row = $stmt->fetch();
		
		$username = $row['username'];
		$count = $row->rowCount();

		$user = $this->database->prepare('SELECT id FROM users WHERE username=?', array($username));
		$id = $user->fetch()['id'];
		return ($count > 0 ? $id : 0);
	}

	public function logoutUser() {
		if (is_null($this->getAuthCookie())) return false;
		$stmt = $this->database->prepare('DELETE FROM user_sessions WHERE hash=? AND username=?', array($this->getAuthCookie(), $this->getUsernameByID($this->getUserID())));
		return true;
	}

	public function blockUser($daysCount = 1) {
		if (is_null($this->getAuthCookie())) return false;

		$stmt = $this->database->prepare('UPDATE users SET isBlocked=?, blockExpiry=?', array(1, time()*60*60*24*$daysCount));
		return true;
	}

	public function isUserBlocked($userID) {
		if (is_null($this->getAuthCookie())) return false;

		$stmt = $this->database->prepare('SELECT isBlocked, blockExpiry FROM users WHERE userID=?', array($userID));
		$row = $stmt->fetch();
		return array('block'=>$row['isBlocked'], 'exp'=>$row['blockExpiry']);
	}

	public function isOnline() {
		if (is_null($this->getAuthCookie())) return false;

		$sess = $this->database->prepare('SELECT COUNT(*) AS count FROM user_sessions WHERE hash=?', array($_SESSION['AUTHSSES']));
		$row = $sess->fetch();
		return ($row['count'] > 0 ? true : false);
	}

	public function authenticateSession() {
		if (is_null($this->getAuthCookie())) return false;

		$session = $this->protect_xss($this->getAuthCookie());
		$sess = $this->database->prepare('SELECT COUNT(*) AS count FROM user_sessions WHERE hash=?', array($session));
		$count = $sess->fetch()['count'];

		$stmt = $this->database->prepare('SELECT ip FROM user_sessions WHERE hash=?', array($session));
		$ipAddress = $stmt->fetch()['ip'];

		// 
		return ($count > 0 ? ($ipAddress == $_SERVER['REMOTE_ADDR'] ? true : false) : false);
	}

	public function createAuthentication($username, $country = 'Unknown') {
		if (is_null($this->getAuthCookie())) return false;

		$hash = bin2hex(random_bytes(32));
		srand(time());
		$id = rand(1000, 9999);

		// TODO: create authentication.
	}

	public function doLogin($username, $password)
	{
		// Username, Password & Salt.
	}
}

?>
