var delaySecond = 1.5;
var arr = [
	"Customer 1", "Customer 2", "Customer 3",
	"Customer 4", "Customer 5"
];
var i = 0;
var scroll = function(div) {
	div.innerHTML = this.arr[i++];
	setInterval(function() {
		div.innerHTML = this.arr[i];
		if (i < arr.length-1) 
			i ++;
		else 
			i = 0;
	}, 1000*delaySecond);
}