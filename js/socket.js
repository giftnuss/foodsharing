var sock = {
	connect: function(sid)
	{
		console.log("connect...");
		console.log(sid);
		//var io.connect('fs.local', {resource: '/chat'});
		var socket = io.connect(location.host, {path: '/chat/socket.io'});
		socket.on("connect",function() {	
			console.log("connected");
			socket.emit("register", sid);
			console.log("tried to register!!!");
		});
		socket.on('conv', function(data) {
			
			switch(data.m)
			{
				case 'push':
					conv.push(JSON.parse(data.o));
					break;
			}
		});
	}
};
