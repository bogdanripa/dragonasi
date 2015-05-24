window.ws = {};

window.ws.init = function(scope, cb) {
	if (scope) {
		window.ws.scope = scope;
	}
	if (cb) {
		window.ws.cb = cb;
	}
	window.ws.closing = false;

	var host = "ws://snoop.photos:9000/echobot"; // SET THIS TO YOUR SERVER
	try {
		window.ws.socket = new WebSocket(host);
		console.log('WebSocket - status '+window.ws.socket.readyState);
		window.ws.socket.onopen    = function(msg) { 
							   //console.log("Welcome - status "+this.readyState);
							   window.ws.cb();
						   };
		window.ws.socket.onmessage = function(msg) {
			try {
				oMsg = JSON.parse(msg.data);
			} catch(e) {
				alert(msg.data);
				return;
			}
			switch(oMsg.command) {
				case 'connected':
					window.ws.scope.$broadcast('groupSelected', oMsg.group);
					break;
				case 'disconnected':
					window.ws.scope.$broadcast('groupDisconnected', oMsg.group);
					break;
				case 'allPlayersReady':
					window.ws.scope.$broadcast('allPlayersReady', oMsg.group);
					break;
				default:
					window.ws.scope.$broadcast(oMsg.command, oMsg);
					break;
			}

		   console.log("Received: "+msg.data); 
	   };
		window.ws.socket.onclose   = function(msg) { 
			 console.log("Disconnected - status " + this.readyState); 
			 if (!window.ws.closing) {
				 window.ws.scope.$broadcast('restart');
			}
		};
	}
	catch(ex){ 
		console.log(ex);
		setTimeout(window.ws.init, 1000);
	}

}

window.ws.send = function(msg){
	if(!msg) { 
		alert("Message can not be empty"); 
		return; 
	}
	try { 
		window.ws.socket.send(msg); 
		console.log('Sent: '+msg); 
	} catch(ex) { 
		console.log(ex); 
	}
}

window.ws.quit = function(){	
	if (window.ws.socket != null) {
		window.ws.closing = true;
		console.log("Goodbye!");
		window.ws.send('{"command": "disconnect"}');
		window.ws.socket.close();
		window.ws.socket=null;
	}
}

window.ws.reconnect = function() {
	window.ws.quit();
	window.ws.init();
}

window.onbeforeunload = function() {
	window.ws.quit();
}
