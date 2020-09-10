var constants = require('./constants');
var bodyParser = require('body-parser');
var app = require('express')();
var server = require('http').createServer(app);
var io = require('socket.io')(server);

//utils-------------------------------------------------------
var log = function() {
	var dt = new Date();
	var time = dt.toLocaleTimeString();
	time += '.'+('000'+dt.getMilliseconds()).slice(-3);
	console.log.apply(null, [time, ...arguments]);
};

var sockets = {};

var uid = function() {
	return (Math.random() * new Date().getTime()).toString(36).replace(/\./g, '-');
};

var List = function(converter) {
	var me = this;

	me.items = [];
	me.keys = [];

	me.converter = function(item) {
		return item;
	};
	if (typeof converter == 'function') {
		me.converter = converter;
	}

	me.add = function(item) {
		var _item = me.get(item.key);
		if (_item) me.remove(_item);
		_item = me.converter.apply(me, arguments);
		me.items.push(_item);
		me.keys.push(_item.key);
		return _item;
	};

	me.addItems = function(items) {
		items.forEach(function(item){me.add(item)});
	};

	me.remove = function(item, cause) {
		var index = me.items.indexOf(item);
		if (index >= 0) {
			me.items.splice(index, 1);
			me.keys.splice(index, 1);
		}
		if (item.onRemove) {
			item.onRemove(cause);
		}
	};

	me.get = function(key) {
		var index = me.keys.indexOf(key);
		return me.items[index];
	};
	me.filter = function(fn) {
		return me.items.filter(fn);
	};
	me.count = function() {
		return me.items.length;
	};
};

var observeResponse = function(key, user) {
	return user ? {
		pmUser_id: key,
		status: user.status || 'offline',
		hasCamera: user.data.hasCamera || false,
		hasMicro: user.data.hasMicro || false
	} : {
		pmUser_id: key,
		status: 'offline',
		hasCamera: false,
		hasMicro: false
	};
};

var User = function(key, data, socket) {
	var me = this;

	me.key = Number(key);
	me.data = data;
	me.socket = socket;
	me.status = 'offline';
	me.observeKeys = [];

	me.requestObserveAll = function() {
		var response = me.observeKeys.map(function(key) {
			return observeResponse(key, users.get(key));
		});
		socket.emit('observeUsersEvent', 'request', response);
	};

	me.changeStatus = function(status) {
		me.status = status;

		var response = observeResponse(me.key, me);

		users.filter(function(user) {
			return user != me && user.observeKeys.includes(me.key);
		}).forEach(function(user) {
			user.socket.emit('observeUsersEvent', 'change', response);
		});
	};
};

var users = new List(function(key, data, socket) {
	return new User(key, data, socket);
});

var initNewUser = function(socket, data, confirm) {
	socket.user = users.add(data.pmUser_id, data, socket);
	socket.user.changeStatus('online');
	log('init', socket.user.key, socket.handshake.address);
	confirm(true);
};

var notInitUser = function(socket, data, confirm) {
	log('not init', data.pmUser_id, socket.handshake.address);
	confirm(false);
};

var disconnectUser = function(user) {
	user.socket.disconnect();
	user.changeStatus('offline');
	users.remove(user);
	log('disconnect', user.socket.user.key);
};

var initExistedUser = function(user, socket, data, confirm) {
	disconnectUser(user);
	initNewUser(socket, data, confirm);
};

var processAddress = function(address) {
	if (address == '::1') {
		return '::ffff:127.0.0.1';
	}
	return address;
};

//socket.io-server----------------------------------------------
io.on('connection', function(socket) {
	socket.on('init', function(data, confirm) {
		var user = users.get(Number(data.pmUser_id));

		if (!user) {
			initNewUser(socket, data, confirm);
		} else if (processAddress(user.socket.handshake.address) == processAddress(socket.handshake.address)) {
			initExistedUser(user, socket, data, confirm);
		} else {
			notInitUser(socket, data, confirm);
		}
	});

	socket.on('disconnect', function() {
		if (!socket.user) return;
		socket.user.changeStatus('offline');
		users.remove(socket.user);
		log('disconnect', socket.user.key);
	});

	socket.on('createRoom', function(userKeys, returnRoom) {
		var room = uid();
		socket.join(room, function() {
			returnRoom(room);

			users.filter(function(user) {
				return userKeys.map(key => Number(key)).includes(user.key);
			}).forEach(function(user) {
				user.socket.join(room+'.tmp', function() {
					user.socket.emit('invite', room, socket.user.key);
				});
			});
		});
	});

	socket.on('invite', function(room, userKeys) {
		users.filter(function(user) {
			return userKeys.map(key => Number(key)).includes(user.key);
		}).forEach(function(user) {
			user.socket.join(room+'.tmp', function() {
				user.socket.emit('invite', room, socket.user.key);
			});
		});
	});

	socket.on('joinRoom', function(room) {
		socket.join(room, function() {
			socket.leave(room+'.tmp', function() {
				io.to(room).emit('connectTo', socket.user.key);
			});
		});
	});

	socket.on('offer', function(userKey, offerObj) {
		var user = users.get(Number(userKey));
		user.socket.emit('offer', socket.user.key, offerObj);
	});

	socket.on('answer', function(userKey, answer) {
		var user = users.get(userKey);
		user.socket.emit('answer', socket.user.key, answer);
	});

	socket.on('iceCandidate', function(userKey, candidate) {
		var user = users.get(Number(userKey));
		if (!user) return;
		user.socket.emit('iceCandidate', socket.user.key, candidate);
	});

	socket.on('leave', function(room, cause) {
		if (!socket.user) return;
		socket.leave(room, function() {
			io.to(room).emit('leave', socket.user.key, cause);
		});
		socket.leave(room+'.tmp', function() {
			io.to(room+'.tmp').emit('leave', socket.user.key, cause);
		});
	});

	socket.on('setMuted', function(room, kind, muted) {
		io.to(room).emit('setMuted', socket.user.key, kind, muted);
	});

	socket.on('callStarted', function(room) {
		io.to(room).emit('callStarted', room);
	});

	socket.on('observeUsers', function(keys) {
		if (!socket.user) return;
		if (Array.isArray(keys)) {
			socket.user.observeKeys = keys.map(function(key) {
				return Number(key);
			});
		};
		socket.user.requestObserveAll();
	});
});


//http-server----------------------------------------------
app.use(bodyParser.json({limit: '500mb', extended: true}));
app.use(bodyParser.urlencoded({limit: '500mb', extended: true}));

app.post('/message', function(req, res) {
	var keys = req.body.userKeys;
	var message = req.body.message;

	if (!Array.isArray(keys)) {
		keys = [keys];
	}

	keys.push(message.pmUser_sid);

	keys.forEach(function(key) {
		var user = users.get(Number(key));
		if (user) user.socket.emit('message', message);
	});

	res.end();
});

server.listen(constants.socketPort);
log('node-video-chat started on port '+constants.socketPort);