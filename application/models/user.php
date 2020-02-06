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

	public function hasPrivilege() {
		$funcs = func_get_args();

		$stmt = $this->database->prepare('SELECT * FROM users WHERE id=?', array($this->getUserID()));
		$user = $stmt->fetch();
		foreach ($funcs as $func)
			if ($user['privilege'] == $func)
				return true;
		return false;
	}

	public function getUsernameByID($id = -1) {
		if ($id < 0) return null;
		if (preg_match('/^[a-zA-Z]$/', $id)) return null;
		if ($id == 0) return 'Guest';
		if (!preg_match('/^[0-9]$/', $id)) return 'Unknown';

		$stmt = $this->database->prepare('SELECT username FROM users WHERE id=?', array($id));
		$username = $stmt->fetch()['username'];
		return $username;
	}

	public function get_id_by_username($username) {
		if (!preg_match('/^[a-z][A-Z][!@#$%^&*()_+\\\[\]\'"]$/', $id)) return null;

		$stmt = $this->database->prepare('SELECT id FROM users WHERE username=?', $username);
		$id = $stmt->fetch()['id'];
		return $id;
	}

	public function getUserID() {
		if (!isset($_SESSION['AUTHSSES'])) return 0;

		$sess = $this->database->prepare('SELECT COUNT(*) AS count FROM user_sessions WHERE hash=?', array($_SESSION['AUTHSSES']));
		$count = $sess->fetch()['count'];

		$stmt = $this->database->prepare('SELECT username FROM user_sessions WHERE hash=?', array($_SESSION['AUTHSSES']));
		$username = $stmt->fetch()['username'];

		$user = $this->database->prepare('SELECT id FROM users WHERE username=:username', array($username));
		$id = $user->fetch()['id'];
		return ($count > 0 ? $id : 0);
	}

	public function logoutUser() {
		if (!isset($_SESSION['AUTHSSES'])) return false;
		$stmt = $this->database->prepare('DELETE FROM user_sessions WHERE hash=? AND username=?', array($_SESSION['AUTHSSES'], $this->getUsernameByID($this->getUserID())));
		$stmt->execute();
		return true;
	}

	public function blockUser($daysCount = 1) {
		if (!isset($_SESSION['AUTHSSES'])) return false;

		$stmt = $this->database->prepare('UPDATE users SET isBlocked=?, blockExpiry=?', array(1, time()*60*60*24*$daysCount));
		return true;
	}

	public function isUserBlocked($userID) {
		if (!isset($_SESSION['AUTHSSES'])) return false;

		$stmt = $this->database->prepare('SELECT isBlocked, blockExpiry FROM users WHERE userID=?', array($userID));
		$row = $stmt->fetch();
		return array('block'=>$row['isBlocked'], 'exp'=>$row['blockExpiry']);
	}

	public function isOnline() {
		if (!isset($_SESSION['AUTHSSES'])) return false;

		$sess = $this->database->prepare('SELECT COUNT(*) AS count FROM user_sessions WHERE hash=?', array($_SESSION['AUTHSSES']));
		$row = $sess->fetch();
		return ($row['count'] > 0 ? true : false);
	}

	public function authenticateSession() {
		if (!isset($_SESSION['AUTHSSES'])) return false;
		$session = $this->protect_xss($_SESSION['AUTHSSES']);
		$sess = $this->database->prepare('SELECT COUNT(*) AS count FROM user_sessions WHERE hash=?', array($session));
		$count = $sess->fetch()['count'];

		$stmt = $this->database->prepare('SELECT ip FROM user_sessions WHERE hash=?', array($session));
		$ipAddress = $stmt->fetch()['ip'];

		return ($count > 0 ? ($ipAddress == $_SERVER['REMOTE_ADDR'] ? true : false) : false);
	}

	public function createAuthentication($username, $country = 'Unknown') {
		if (isset($_SESSION['AUTHSSES'])) return false;

		$hash = bin2hex(random_bytes(32));
		srand(time());
		$id = rand(1000, 9999);

		$stmt = $this->database->prepare('INSERT INTO user_sessions SET id=?, username=?, hash=?, ip=?, country=?, time=?', array($id, $username, $hash, $_SERVER['REMOTE_ADDR'], $country, time()));

		if ($stmt->execute()) {
			$_SESSION['AUTHSSES'] = $hash;
			return true;
		}
		return false;
	}

	public function doLogin($val = array())
	{
		$uname = $val['username'];
		$pword = $val['password'];

		$salt = $this->database->getUserByUsername($username)['salt'];
		$password = hash('sha256', $salt.md5($password));

		$stmt = $this->database->prepare('SELECT COUNT(*) AS count FROM users WHERE username=:username AND password=:password');
		$stmt->bindParam(':username', $username);
		$stmt->bindParam(':password', $password);
		$stmt->closeCursor(); // ??
		$stmt->execute();
		$row = $stmt->fetch();
		return ($row['count'] < 1 ? false : true);
	}

	public function doRegister($val = array())
	{
		$username = $val['username'];
		$password = $val['password'];
		$email = $val['email'];

		$salt = uniqid();
		$password = hash('sha256', $salt.md5($password));
		$curTime = time();

		$stmt = $this->pdo->prepare('INSERT INTO users SET id=:id, username=:username, password=:password, salt=:salt, email=:email, privilege=:privilege, lastlogin=:lastlogin, time=:time, ipAddress=:ipAddress');
		$stmt->bindValue(':id', ($this->database->getRowsCount('users')+1));
		$stmt->bindParam(':username', $username);
		$stmt->bindParam(':password', $password);
		$stmt->bindParam(':salt', $salt); // random sequence of chars. 16 byte long?
		$stmt->bindParam(':email', $email);
		$stmt->bindValue(':privilege', 0);
		$stmt->bindValue(':lastlogin', '');
		$stmt->bindValue(':time', time());
		$stmt->bindValue(':ipAddress', $_SERVER['REMOTE_ADDR']);
		$stmt->closeCursor(); // ??
		return $stmt->execute();
	}
}

?>
