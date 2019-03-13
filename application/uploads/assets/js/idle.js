var inactivityTime = function () {
    "use strict";
	var t = undefined;
    
    function logout() {
        //location.href = '';
        var xmlhttp = new XMLHttpRequest();
        xmlhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                var result = confirm(this.responseText);
                if (result || !result) {
                    location.reload();
                }
            }
        };
        xmlhttp.open("GET", "/process/logout", true);
        xmlhttp.send();
    }

    function resetTimer() {
        clearTimeout(t);
        t = setTimeout(logout, 60000);
    }
    
    window.onload = resetTimer;
    document.onmousemove = resetTimer;
    document.onkeypress = resetTimer;
};

window.addEventListener('load', inactivityTime);