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

	function load_xhr() {
		return '
			<script type="text/javascript">
			function post_xhr(url, param, receive, form){
				var xhr = new XMLHttpRequest();
				xhr.open("POST", url, true);
				if (form)
					xhr.setRequestHeader("Content-type", "x-www-form-urlencoded");
				else
					xhr.setRequestHeader("Content-type", "application/json");
				xhr.onreadystatechange = function () {
					if (xhr.readyState === 4 && xhr.status === 200)
						receive(xhr.responseText);
				};
				if (form)
					xhr.send(param);
				else
					xhr.send(JSON.stringify(param));
			}
			</script>
		';
	}

	function loadMessages()
	{}

}

?>
