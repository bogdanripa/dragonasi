window.ws = {};

window.ws.init = function(scope, cb) {
	window.ws.scope = scope;
	var host = "ws://snoop.photos:9000/echobot"; // SET THIS TO YOUR SERVER
	try {
		window.ws.socket = new WebSocket(host);
		//console.log('WebSocket - status '+window.ws.socket.readyState);
		window.ws.socket.onopen    = function(msg) { 
							   //console.log("Welcome - status "+this.readyState);
							   cb();
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

		   console.log("Received: "+msg); 
	   };
		window.ws.socket.onclose   = function(msg) { 
							   console.log("Disconnected - status "+this.readyState); 
						   };
	}
	catch(ex){ 
		console.log(ex); 
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
		console.log("Goodbye!");
		websocket.onclose = function () {};
		window.ws.socket.close();
		window.ws.socket=null;
	}
}

window.ws.reconnect = function() {
	window.ws.quit();
	window.ws.init();
}

window.onbeforeunload = function() {
	alert(1);
	window.ws.quit;
}
