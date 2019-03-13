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

		$stmt = $this->database->prepare('SELECT * FROM users WHERE id=:id');
		$stmt->bindParam(':id', $this->getUserID());
		$stmt->closeCursor();
		$stmt->execute();
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

		$stmt = $this->database->prepare('SELECT username FROM users WHERE id=:id');
		$stmt->bindParam(':id', $id);
		$stmt->execute();
		$username = $stmt->fetch()['username'];
		return $username;
	}

	public function get_id_by_username($username) {
		if (!preg_match('/^[a-z][A-Z][!@#$%^&*()_+\\\[\]\'"]$/', $id)) return null;

		$stmt = $this->database->prepare('SELECT id FROM users WHERE username=:username');
		$stmt->bindParam(':username', $username);
		$stmt->execute();
		$id = $stmt->fetch()['id'];
		return $id;
	}

	public function getUserID() {
		if (!isset($_SESSION['AUTHSSES'])) return 0;

		$sess = $this->database->prepare('SELECT COUNT(*) AS count FROM user_sessions WHERE hash=:hash');
		$sess->bindParam(':hash', $_SESSION['AUTHSSES']);
		$sess->closeCursor();
		$sess->execute();
		$count = $sess->fetch()['count'];

		$stmt = $this->database->prepare('SELECT username FROM user_sessions WHERE hash=:hash');
		$stmt->bindParam(':hash', $_SESSION['AUTHSSES']);
		$stmt->closeCursor();
		$stmt->execute();
		$username = $stmt->fetch()['username'];

		$user = $this->database->prepare('SELECT id FROM users WHERE username=:username');
		$user->bindParam(':username', $username);
		$user->execute();
		$id = $user->fetch()['id'];
		return ($count > 0 ? $id : 0);
	}

	public function logoutUser() {
		if (!isset($_SESSION['AUTHSSES'])) return false;
		$stmt = $this->database->prepare('DELETE FROM user_sessions WHERE hash=:hash AND username=:uname');
		$stmt->bindValue(':hash', $_SESSION['AUTHSSES']);
		$stmt->bindValue(':uname', $this->getUsernameByID($this->getUserID()));
		$stmt->execute();
		return true;
	}

	public function blockUser($daysCount = 1) {
		if (!isset($_SESSION['AUTHSSES'])) return false;

		$stmt = $this->database->prepare('UPDATE users SET isBlocked=:block, blockExpiry=:exp');
		$stmt->bindValue(':block', 1);
		$stmt->bindValue(':exp', time()*60*60*24*$daysCount);
		$stmt->closeCursor();
		$stmt->execute();
		return true;
	}

	public function isUserBlocked($userID) {
		if (!isset($_SESSION['AUTHSSES'])) return false;

		$stmt = $this->database->prepare('SELECT isBlocked, blockExpiry FROM users WHERE userID=:id');
		$stmt->bindValue(':id', $userID);
		$stmt->closeCursor();
		$stmt->execute();
		$row = $stmt->fetch();
		return array('block'=>$row['isBlocked'], 'exp'=>$row['blockExpiry']);
	}

	public function isOnline() {
		if (!isset($_SESSION['AUTHSSES'])) return false;

		$sess = $this->database->prepare('SELECT COUNT(*) AS count FROM user_sessions WHERE hash=:hash');
		$sess->bindParam(':hash', $_SESSION['AUTHSSES']);
		$sess->closeCursor(); // ??
		$sess->execute();
		$row = $sess->fetch();
		return ($row['count'] > 0 ? true : false);
	}

	public function authenticateSession() {
		if (!isset($_SESSION['AUTHSSES'])) return false;
		$session = $this->protect_xss($_SESSION['AUTHSSES']);
		//$sess = $this->database->getOne('SELECT * FROM user_sessions WHERE hash=?s', $session);
		$sess = $this->database->prepare('SELECT COUNT(*) AS count FROM user_sessions WHERE hash=:hash');
		$sess->bindParam(':hash', $session);
		$sess->closeCursor(); // ??
		$sess->execute();
		$count = $sess->fetch()['count'];

		$stmt = $this->database->prepare('SELECT ip FROM user_sessions WHERE hash=:hash');
		$stmt->bindParam(':hash', $session);
		$stmt->closeCursor();
		$stmt->execute();
		$ipAddress = $stmt->fetch()['ip'];

		return ($count > 0 ? ($ipAddress == $_SERVER['REMOTE_ADDR'] ? true : false) : false);
	}

	public function createAuthentication($username, $country = 'Unknown') {
		if (isset($_SESSION['AUTHSSES'])) return false;

		$hash = bin2hex(random_bytes(32));
		srand(time());
		$id = rand(1000, 9999);

		$stmt = $this->database->prepare('INSERT INTO user_sessions SET id=:id, username=:username, hash=:hash, ip=:ip, country=:country, time=:time');
		$stmt->bindParam(':id', $id);
		$stmt->bindParam(':username', $username);
		$stmt->bindParam(':hash', $hash);
		$stmt->bindValue(':ip', $_SERVER['REMOTE_ADDR']);
		$stmt->bindParam(':country', $country);
		$stmt->bindValue(':time', time());
		$stmt->closeCursor(); // ??

		if ($stmt->execute()) {
			$_SESSION['AUTHSSES'] = $hash;
			return true;
		}
		return false;
	}

	public function editProfile($val = array()) {
		$username = $val['username'];
		$password = $val['password'];
		$cpassword = $val['cpassword'];
		$email = $val['email'];
		$profileImage = $val['pimage'];

		if (!empty($username)) {
			// Check if username is available.
			// Change username.
		}
		if (!empty($password) && !empty($cpassword)) {
			// Change password.
		}
		if (!empty($email)) {
			// Check if email is available.
			// Change email.
		}
		if (!empty($profileImage)) {
			// Delete previous image and update with new one.
		}
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

	/*
	 * @return array
	**/
	public function getUserRow() {
		if (!$this->isOnline() || !$this->authenticateSession())
			return null;

			$stmt = $this->pdo->prepare('SELECT * FROM users WHERE id=:id');
			$stmt->bindParam(':id', $this->getUserID());
			$stmt->closeCursor();
			$stmt->execute();
			return $stmt->fetch();
	}

}


?>
