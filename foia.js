// Common Javascript functions for eFOIA

	function fadeIn(elm,op) {
		if (op < 1) {
			op = op + .05;
			document.getElementById(elm).style.filter='alpha(opacity='+op*100+')';
			document.getElementById(elm).style.opacity = op;
			setTimeout("fadeIn('"+elm+"',"+op+")",25);
		}
	}

	function fadeInFast(elm,op) {
		if (op < 1) {
			op = op + .2;
			document.getElementById(elm).style.filter='alpha(opacity='+op*100+')';
			document.getElementById(elm).style.opacity = op;
			setTimeout("fadeInFast('"+elm+"',"+op+")",25);
		}
	}

	function fadeInFastPartial(elm,op,stopval) {
		if (op < stopval) {
			op = op + .2;
			document.getElementById(elm).style.filter='alpha(opacity='+op*100+')';
			document.getElementById(elm).style.opacity = op;
			setTimeout("fadeInFastPartial('"+elm+"',"+op+"," + stopval + ")",25);
		}
	}

	function createRequestObject() {
	    var ro;
	    var browser = navigator.appName;
	    if(browser == "Microsoft Internet Explorer"){
	        ro = new ActiveXObject("Microsoft.XMLHTTP");
	    }else{
	        ro = new XMLHttpRequest();
	    }
	    return ro;
	}

	function fadeClose(elm,op) {
		if(op > 0) {
			op = op - .1;
			document.getElementById(elm).style.opacity = op;
			document.getElementById(elm).style.filter='alpha(opacity='+op*100+')';
			setTimeout("fadeClose('"+elm+"',"+op+")",25);
		} else {
			//document.getElementById(elm).innerHTML = '';
			document.getElementById(elm).style.opacity = 1;
		}
	}
