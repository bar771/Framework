function swapDiv(elementID) {
	var element = document.getElementById(elementID);
	var elements = document.getElementsByClassName('container')[0].getElementsByTagName('div');
	for (var i=0; i<elements.length; i++) {
		var e = elements[i];
		if (e.style.display == 'block') {
			e.style.display = 'none';
		}
	}
	element.style.display = 'block';
}