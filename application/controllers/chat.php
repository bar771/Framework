<?php
namespace Framework\Controllers;

use Framework\Util;

Util::loadModel('user');
Util::loadModel('security');

use Framework\Controller as BaseObject;

use Framework\Models\Security;
use Framework\Models\User;

class Chat extends BaseObject {
	public $user = null;

	function __construct() {
		parent::__construct();
		$this->user = new User($this->database);
	}

	function init() {
		parent::init();

		$this->setTitle('Chat');
		$this->renderView('chat');
	}

	function load_xhr($url, $method = 'GET', $params = array()) {
		if (!isset($url)) 
			return NULL;
		
		switch ($method){
			case 'GET':
			case 'POST':
				break;
			default:
				return NULL;
		}
		
		$params = (count($params) > 0 ? http_build_query($params) : 'undefined');
		
		return '
			<script type="text/javascript">
			function post_xhr(receive, form){
				var xhr = new XMLHttpRequest();
				xhr.open("'.$method.'", "'.$url.'", true);
				if (form)
					xhr.setRequestHeader("Content-type", "x-www-form-urlencoded");
				else
					xhr.setRequestHeader("Content-type", "application/json");
				xhr.onreadystatechange = function () {
					if (xhr.readyState === 4 && xhr.status === 200)
						receive(xhr.responseText);
				};
				if (form)
					xhr.send('.$params.');
				else
					xhr.send(JSON.stringify('.$params.'));
			}
			</script>
		';
	}

	function loadMessages()
	{}

}

?>
