<?php
namespace Framework\Models;

use Framework\Model as baseObject;

class Security extends baseObject {

	function __construct($database = null) {
		parent::__construct($database);
	}

	/*
	 * <input type="hidden" name="securityToken" value="<?php echo $thiis->securityToken(); ?\>">
	**/
	public function securityToken() {
		if (!isset($_SESSION['token']) || count($_POST) === 0 && count($_GET) === 0) $_SESSION['token'] = bin2hex(random_bytes(32));
        return $_SESSION['token'];
    }

    /*
     * if ($this->check_securityToken($_POST['securityToken'])) {}
    **/
    public function check_securityToken($token) {
        return (htmlspecialchars(trim($token)) === $this->securityToken());
    }

    /*
     * https://developers.google.com/recaptcha/docs/verify
    **/
	public function reCaptcha() {
		$secret = '';
		$res = $_POST['g-recaptcha-response'];
		$url = 'https://www.google.com/recaptcha/api/siteverify?secret='.$secret.'&response='.$res;
		$json = json_decode(file_get_contents($url));
		return $json->{'success'};
	}

	public function proxy($url = '') {

		if (!isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
			header('HTTP/1.0 403 Forbidden');
			die('You are not allowed to access this file.');
		}

		header('Content-Type: application/json');
		$json = json_decode(file_get_contents($url));
		return $json;
	}

	public static function protect_xss($str) {
		return htmlspecialchars(htmlentities(stripslashes(rtrim($str))), ENT_QUOTES, 'UTF-8');
	}

	public function generatePassword($length) {
		mt_srand(time());
		$chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	    $str = '';
	    for ($i=0; $i<$length; $i++) $str .= $chars[mt_rand(0, strlen($chars)-1)];
	    return $str;
	}
}

class AES {
	private $keys;
	private $tags, $iv;

	public function encryptMessage($text, $key, $iv) {
		$cipharedtext = openssl_encrypt($text, 'aes-128-gcm', $key, 0, $iv, $tag);
		return array($cipharedtext, $tag);
	}

	public function decryptMessage($crypttext, $key, $iv, $tag) {
		return openssl_decrypt($crypttext, 'aes-128-gcm', $key, 0, $iv, $tag);
	}

	// iv and key.
	public function generateKeys($seed = '', $keyLength = 32) {
		$ivlen = openssl_cipher_iv_length('aes-128-gcm');
	    $iv = bin2hex(openssl_random_pseudo_bytes($ivlen));
		if (!empty($seed))
			$key = $this->randomKey($seed, $keyLength);
		else
			$key = bin2hex(openssl_random_pseudo_bytes(32)); // bin2hex ?
		return array($key, $iv);
	}

	public function randomKey($seed = '', $length = 32) {
		$seed = crc32($seed) % 1000 + 1000;
		mt_srand($seed);
		$chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	    $str = '';
	    for ($i=0; $i<$length; $i++) $str .= $chars[mt_rand(0, strlen($chars)-1)];
		return bin2hex($str);
	}
}

class RSA {
	public function generateKeysPair() {
		$res = openssl_pkey_new(array(
    		"digest_alg" => "sha512",
    		"private_key_bits" => 4096,
    		"private_key_type" => OPENSSL_KEYTYPE_RSA,
		));
		openssl_pkey_export($res, $privKey);
		$pubKey = openssl_pkey_get_details($res);
		$pubKey = $pubKey["key"];
		return array($pubKey, $privKey);
	}

	public function encryptMessage($str, $pubKey) {
		if (openssl_public_encrypt($str, $encrypted, $pubKey))
            return base64_encode($encrypted);
	}

	public function decryptMessage($data, $privKey) {
		if (openssl_private_decrypt(base64_decode($data), $decrypted, $privKey)) {
            return $decrypted;
		}
		return null;
	}
}

?>
