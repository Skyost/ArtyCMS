var HttpClient = function() {
	var httpRequest = new XMLHttpRequest();
	httpRequest.timeout = 4000;
	this.get = function(url, callback) {
		httpRequest.onreadystatechange = function() {
			if(httpRequest.readyState == 4 && httpRequest.status == 200) {
				callback(httpRequest.responseText);
			}
		}
		httpRequest.open('GET', url, true);
		httpRequest.setRequestHeader("If-Modified-Since", "Sat, 1 Jan 2005 00:00:00 GMT");
		httpRequest.send();
	}
	this.post = function(url, params, callback) {
		httpRequest.onreadystatechange = function() {
			if(httpRequest.readyState == 4 && httpRequest.status == 200) {
				callback(httpRequest.responseText);
			}
		}
		httpRequest.open('POST', url, true);			
		httpRequest.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
		httpRequest.send(params);
	}
};

// Images :

var loaded = [];

$('#articles img').waypoint(function() {
	loadArt($(this)[0].element);
});

function loadArt(art) {
	if($.inArray(art.id, loaded) > -1) {
		return;
	}
	art.src = art.getAttribute('data-src');
	loaded.push(art.id);
}

// Shoutbox :

var shoutboxMessages = document.getElementById('shoutbox-messages');

window.setInterval(function() {
	new HttpClient().get('messages.json'/* + '?nocache=' + Math.random()*/, function(response) {
		var messages = JSON.parse(response);
		var html = '';
		for(var i = 0; i < messages.length; i++) {
			html += ('<strong>' + messages[i].username + '</strong><p>' + messages[i].message + '</p>');
		}
		shoutboxMessages.innerHTML = html;
	});
}, 2000);

function sendMessage(event) {
	if((event.keyCode? event.keyCode : event.charCode) != 13) {
		return;
	}
	var username = document.getElementById('shoutbox-username');
	var message = document.getElementById('shoutbox-message');
	new HttpClient().post('shoutbox.php', 'action=0&username=' + htmlEncode(username.value) + '&message=' + htmlEncode(message.value), function(response) {
		console.log(response);
		username.value = '';
		message.value = '';
	});
}

function htmlEncode(text) {
	return document.createElement('a').appendChild(document.createTextNode(text)).parentNode.innerHTML;
};

// Header :

var headerTitle = document.getElementById('header-title');

headerTitle.setScaledFont = function(f) {
	var s = this.offsetWidth, fs = s * f;
	this.style.fontSize = fs + '%';
	return this;
};

headerTitle.setScaledFont(1);

window.onresize = function() {
	headerTitle.setScaledFont(1);
}

// Other things :

var articles = document.getElementById('articles');

var lines = articles.getElementsByClassName('line');
articles.removeChild(lines[lines.length - 1]);

function disconnect() {
	Cookies.remove('username');
	Cookies.remove('password');
	window.location.reload();
}