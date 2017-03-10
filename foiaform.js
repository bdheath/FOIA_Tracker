

	function letterClose() {
		var a = document.getElementById('letterdivblocker');
		a.style.height = '0px';
		a.style.width = '0px';
		var a = document.getElementById('shadow');
		a.style.height = '0px';
		a.style.width = '0px';
		a.style.backgroundColor='';
		var a = document.getElementById('letterdiv');
		a.style.heihgt = '0px';
		a.style.width = '0px';
		a.style.padding = '0px';
		a.innerHTML = '';
	}

	function letterStart(fid) {
		var s = document.getElementById('shadow');
		s.style.position = 'absolute';
		var b = document.getElementById('bodyarea');
		var pos = findPos(b);
		s.style.backgroundColor = 'black';
		s.style.opacity = 0;
		s.style.filter = 'alpha(opacity=0)';
		s.style.top = pos[1];
		s.style.left = pos[0];
		s.style.height = b.offsetHeight;
		s.style.width = b.offsetWidth;
		fadeInFastPartial('shadow',0,.6);
		var a = document.getElementById('letterdiv');
		a.style.top = pos[1] + 30 + 'px';
		a.style.width = b.offsetWidth - 100 + 'px';
		a.style.position = 'absolute';
		a.style.height = '500px';
		a.style.left = pos[0] + 40;
		a.style.backgroundColor = 'white';
		a.style.padding = '10px';
		var a = document.getElementById('letterdivblocker');
		a.style.top = pos[1] + 30 + 'px';
		a.style.width = b.offsetWidth - 100 + 'px';
		a.style.position = 'absolute';
		a.style.height = '500px';
		a.style.left = pos[0] + 40;
		fadeInFastPartial('letterdiv',0,1);
		sndReqLetter('makearea','&foia_id='+fid+'&title='+escape(document.getElementById('title').value));
	}

	function defaultGreeting() {
		document.getElementById('greeting').value = 'Sir or Madam';
		letterGen();
	}

	function letterGen() {
		var data = '&template_id=' + document.getElementById('template_id').value +
			'&rs=' + escape(document.getElementById('rs').value) +
			'&address=' + escape(document.getElementById('address').value) +
			'&greeting=' + escape(document.getElementById('greeting').value) +
			'&foia_id=' + document.getElementById('foia_id_ltr').value +
			'&template_id=' + document.getElementById('template_id').value +
			'&highlight=' + document.getElementById('highlight').checked;

		if(document.getElementById('rs').value != '') {
			document.getElementById('records_sought').value = '' + document.getElementById('rs').value;
			anychanges = 1;
		}
		sndReqLetter('lettergen',data);

	}

	function sndReqLetter(action, data) {
		var httpRequest = createRequestObject();
		httpRequest.onreadystatechange = function() {
			if(httpRequest.readyState == 4) {
				var response = httpRequest.responseText;
				var update = response.split('|');
	//			document.write(response);
				document.getElementById(update[0]).innerHTML = update[1];
				if(update[2] == 'updateforms') {
					if(document.getElementById('records_sought').value != '') {
						document.getElementById('rs').value = document.getElementById('records_sought').value;
						setTimeout("letterGen()", 1000);
					}
					letterGen();

				}
			}
		}
		var header = 'Content-Type:application/x-www-form-urlencoded; charset=UTF-8';
		httpRequest.open('post','foia-ajax-foiaform-lettergen.php?type=' + action);
		httpRequest.setRequestHeader(header.split(':')[0],header.split(':')[1]);
		httpRequest.send(data);
	}