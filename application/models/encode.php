<?php
namespace Framework\Models;

use Framework\Model as baseObject;

class Encode extends baseObject {

  private $fields = array();

	function __construct($database = null) {
		parent::__construct($database);
	}

	function init() {
		parent::init();
	}

  static function AES()
	{
		return new AES();
	}

	static function RSA()
	{
		return new RSA();
	}
}

private class AES {
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
			$key = bin2hex(openssl_random_pseudo_bytes($keyLength));
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

private class RSA {
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
