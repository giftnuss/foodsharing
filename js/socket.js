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
					if(GET('page') == 'msg')
					{
						msg.push(JSON.parse(data.o));
					}
					else
					{
						conv.push(JSON.parse(data.o));
					}
					break;
			}
		});
		
		socket.on('info', function(data) {
			switch(data.m)
			{
				case 'badge':
					info.badge('info',data.o.count);
					break;
			}
		});
		
		socket.on('basket', function(data) {
			switch(data.m)
			{
				case 'badge':
					info.badge('basket',data.o.count);
					break;
			}
		});
	}
};
