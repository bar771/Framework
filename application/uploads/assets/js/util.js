// Allow voice recording- messenger.
//https://stackoverflow.com/questions/25101037/record-audio-using-the-users-microphone-with-html5#25131338
//https://github.com/pieroxy/lz-string

/*
 * @param String url
 * @param String \ JSONObject param
 * @param function(String \ JSONObject) receive
 * @param Boolean form
 * Asynchronous method.
**/
function post_xhr(url, param, receive, form){
	var xhr = new XMLHttpRequest();
	xhr.open("POST", url, true);
	if (form)
		xhr.setRequestHeader('Content-type', 'x-www-form-urlencoded');
	else
		xhr.setRequestHeader("Content-type", "application/json");
	xhr.onreadystatechange = function () {
		if (xhr.readyState === 4 && xhr.status === 200)
			receive(xhr.responseText); //JSON.parse(xhr.responseText)
	};
	if (form)
		xhr.send(param);
	else
		xhr.send(JSON.stringify(param));
}

function dataLoading(dataFetching, delay) {
	var waiting = setInterval(function() {
	var marginHeight = window.scrollMaxY - 200;
	if (window.scrollY >= marginHeight) {
		dataFetching();
	}
	}, delay);
}

function force_ssl(url) {
	if (document.location.protocol == 'https:')
		url = url.replace("http://", "https://");
	return url;
}

function getLinks(div, callback) {
	var links = div.getElementsByTagName('a');
	if (links.length < 1) return;

	for(var i=0; i<links.length; i++) {
		callback(links[i]);
	}
}

//<input type='file' id='image' onchange='convertFile()' placeholder='Upload image..' />
function convertFile() {
	var files = document.getElementById('image').files;
	if (files.length > 0) {
		getBase64(files[0]);
	}
}

function getBase64(file) {
	var reader = new FileReader();
	reader.readAsDataURL(file);
	reader.onload = function () {
		//console.log(reader.result);

		var f = document.createElement('input');
		f.type = 'hidden';
		f.name = 'image';
		f.value = reader.result;
		document.getElementById('form-post').appendChild(f);
	};
	reader.onerror = function (error) {
		console.log('Error: ', error);
	};
}

//<input type='file' onchange='uploadVideos(this)' value='Upload video..' />
function uploadVideos(e) {
	if (window.FileReader) {
		function dragEvent (ev) {
			ev.stopPropagation ();
			ev.preventDefault ();
			if (ev.type == 'drop') {
				var reader = new FileReader ();
				reader.onloadend = function(ev) { e.value += this.result; }; // debug..
				reader.readAsText (ev.dataTransfer.files[0]);
			}
		}

		e.addEventListener('dragenter', dragEvent, false);
		e.addEventListener('dragover', dragEvent, false);
		e.addEventListener('drop', dragEvent, false);
	}else
		console.log('FileReader is not supported.');
}
