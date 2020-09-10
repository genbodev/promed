Ext6.define('videoChat.lib.Engine', {
	singleton: true,
	alternateClassName: 'sw.Promed.VideoChat',
	requires: [
		'videoChat.OfferWindow',
		'videoChat.lib.Connection',
		'videoChat.lib.EventManager',
		'videoChat.lib.ContactManager',
		'videoChat.lib.StreamsMixer',
		'videoChat.lib.ScreenCapture',
		'videoChat.lib.RecorderFactory',
		'videoChat.store.Message'
	],

	user: null,
	options: {},
	settings: {},
	devices: {video: [], audio: []},
	status: null,
	callType: null,
	socket: null,
	events: null,
	room: null,
	dialog: null,
	recorder: null,
	connections: [],

	messageNotificationTpl: new Ext6.XTemplate([
		'<p style="font-weight: bold;">',
			'{SurName} {FirName} {SecName}',
		'</p>',
		'<p>',
			'{text}',
		'</p>'
	]),

	showError: function(error) {
		if (error.name.inlist(['AbortError'])) {
			return;
		}
		log(error);
		Ext6.Msg.alert(langs('Ошибка'), error.name+': '+error.message);
	},

	constructor: function() {
		var me = this;

		me.dialog = Ext6.create('videoChat.OfferWindow');
		me.eventManager = Ext6.create('videoChat.lib.EventManager');
		me.contactManager = Ext6.create('videoChat.lib.ContactManager', me);
		me.mixer = Ext6.create('videoChat.lib.StreamsMixer');
		me.screenCapture = videoChat.lib.ScreenCapture;
		me.recorderFactory = videoChat.lib.RecorderFactory;
		me.observeUserList = [];

		me.messageStore = Ext6.create('videoChat.store.Message', {
			storeId: 'videoChatMessage'
		});

		me.addEvent = me.eventManager.add.bind(me.eventManager);
		me.removeEvent = me.eventManager.remove.bind(me.eventManager);
		me.fireEvent = me.eventManager.fire.bind(me.eventManager);

		var userKey = getGlobalOptions().pmuser_id;
		me.contactManager.getRecord(userKey).then(function(user) {
			me.user = user;
			me.init();
		});
		
		me.addEvent('setStatus', function(status, oldStatus, cause) {
			if (status == 'connect' && String(oldStatus).inlist(['waitAnswer','income'])) {
				me.saveCall();
			}
		}, me);
	},

	init: function(serverAddress) {
		var me = this;
		var address = null;

		me.options = getGlobalOptions().VideoChat || {};

		if (me.options.enable && me.options.host) {
			address = me.options.host;
		}
		if (serverAddress) {
			address = serverAddress;
		}

		if (!address) return;

		me.loadSettings(function(settings) {
			me.settings = settings || {};
			me.getPlugedDevices().then(function() {
				me.disconnectSocket();
				me.connectSocket(address);
			}).catch(me.showError);
		});
	},

	notifyMessages: function(messages) {
		var me = this;
		if (Ext6.isEmpty(messages)) return;
		if (!Ext6.isArray(messages)) messages = [messages];
		messages.forEach(me.notifyMessage.bind(me));
	},

	notifyMessage: function(message) {
		var me = this;
		if (!(message instanceof videoChat.model.Message)) {
			message = Ext6.create('videoChat.model.Message', message);
		}
		if (!message.get('pmUser_sid') || message.get('pmUser_sid') == me.user.get('id')) {
			return;
		}
		me.contactManager.getRecord(message.get('pmUser_sid')).then(function(user) {
			var lengthLimit = 60;
			var text = message.get('text');
			if (message.get('file_name')) {
				text = message.get('file_name');
			}
			if (text.length > lengthLimit + 3) {
				text = text.substr(0, lengthLimit) + '...';
			}
			var html = me.messageNotificationTpl.apply({
				SurName: user.get('SurName'),
				FirName: user.get('FirName'),
				SecName: user.get('SecName'),
				text: text
			});
			sw4.showInfoMsg({type: 'info', text: html});
		});
	},

	isContact: function(user) {
		return user instanceof videoChat.model.Contact;
	},

	setStatus: function(status, cause) {
		var me = this;
		var oldStatus = me.status;

		me.status = status;
		me.fireEvent('setStatus', status, oldStatus, cause);
	},

	getStatus: function() {
		return this.status || 'disconnected';
	},

	loadSettings: function(callback) {
		var me = this;
		callback = callback || Ext6.emptyFn;

		Ext6.Ajax.request({
			url: '/?c=VideoChat&m=getVideoSettings',
			callback: function(options, success, response) {
				var responseData = Ext6.JSON.decode(response.responseText);

				if (responseData.success) {
					callback(responseData.settings);
				} else {
					callback(null);
				}
			}
		});
	},

	setSettings: function(settings, callback) {
		var me = this;
		callback = callback || Ext6.emptyFn;

		var params = {
			settings: Ext6.JSON.encode(settings)
		};

		Ext6.Ajax.request({
			url: '/?c=VideoChat&m=setVideoSettings',
			params: params,
			success: function(response) {
				var responseData = Ext6.JSON.decode(response.responseText);

				if (responseData.success) {
					me.settings = Ext6.apply(me.settings, settings);
				}
				callback(me.settings);
			},
			failure: function(response) {
				callback(null);
			}
		});
	},

	getSettings: function() {
		return Ext6.apply({}, this.settings);
	},

	getVideoDevices: function() {
		return this.devices.video.slice();
	},

	getAudioDevices: function() {
		return this.devices.audio.slice();
	},

	getCurrentVideoDevice: function(data) {
		if (!data.stream) return null;
		var me = this;
		var videoLabel = null;

		if (data.stream.getVideoTracks().length > 0) {
			videoLabel = data.stream.getVideoTracks()[0].label;
		}
		return me.devices.video.find(function(device) {
			return device.label == videoLabel;
		});
	},

	getCurrentAudioDevice: function(data) {
		if (!data.stream) return null;
		var me = this;
		var audioLabel = null;

		if (data.stream.getAudioTracks().length > 0) {
			audioLabel = data.stream.getAudioTracks()[0].label;
		}
		return me.devices.audio.find(function(device) {
			return device.label == audioLabel;
		});
	},

	refreshDevices: function(data) {
		if (!data.devices) return;
		var me = this;
		var oldVideoDevices = me.getVideoDevices();
		var oldAudioDevices = me.getAudioDevices();

		me.devices.video = [];
		me.devices.audio = [];

		data.devices.forEach(function(device) {
			if (device.kind == 'videoinput') {
				var oldDevice = oldVideoDevices.find(function(oldDevice){
					return oldDevice.deviceId = device.deviceId;
				});
				if (!device.label && oldDevice && oldDevice.label) {
					device.label = oldDevice.label;
				}
				me.devices.video.push(device);
			}
			if (device.kind == 'audioinput') {
				var oldDevice = oldAudioDevices.find(function(oldDevice){
					return oldDevice.deviceId = device.deviceId;
				});
				if (!device.label && oldDevice && oldDevice.label) {
					device.label = oldDevice.label;
				}
				me.devices.audio.push(device);
			}
		});

		return me.devices;
	},

	_getPlugedDevices: function(options) {
		var me = this;
		options = options || {};
		if (!options.withStream) {
			return navigator.mediaDevices.enumerateDevices().then(function(devices) {
				me.gettingDevice = false;
				return {devices: devices};
			});
		} else {
			//Нужно запросить доступ к видео и/или аудио устройству
			var constraints = {
				audio: false,
				video: false
			};

			//Здесь можно настраивать качество видео, создаваемого потока
			if (options.video) {
				constraints.video = me.settings.Camera ? {
					deviceId: me.settings.Camera/*,
					 width: 1280,
					 height: 720*/
				} : true;
			}
			if (options.audio) {
				constraints.audio = me.settings.Micro ? {
					deviceId: me.settings.Micro
				} : true;
			}

			return navigator.mediaDevices.getUserMedia(constraints).then(function(stream) {
				return navigator.mediaDevices.enumerateDevices().then(function(devices) {
					return {stream: stream, devices: devices};
				});
			});
		}
	},

	getPlugedDevices: function(options) {
		var me = this;
		return me._getPlugedDevices(options || {}).then(function(data) {
			data.devices = me.refreshDevices(data);
			return data;
		});
	},

	getTracks: function(stream, kind) {
		var me = this;
		if (!stream || (kind && !kind.inlist(['video','audio']))) {
			return [];
		}
		return stream.getTracks().filter(function(track) {
			return !kind || track.kind == kind;
		});
	},

	stopStream: function(data) {
		var me = this;
		if (!data.stream || !data.stream.active) {
			return;
		}
		data.stream.getTracks().forEach(function(track) {
			track.stop();
		});
		data.stream = null;
	},

	stopVideo: function(data) {
		var me = this;
		if (!data.video) return;
		me.stopStream({stream: data.video.srcObject});
		data.video.srcObject = null;
	},

	isMuted: function(kind) {
		var me = this;
		return me.getTracks(me.stream, kind).every(function(track) {
			return !track.enabled;
		});
	},

	setMuted: function(kind, muted) {
		var me = this;
		if (!me.room) return;

		me.getTracks(me.stream, kind).forEach(function(track) {
			track.enabled = !muted;
		});

		me.onSetMuted(me.user, kind, muted);
		me.socket.emit('setMuted', me.room, kind, muted);
	},

	onSetMuted: function(user, kind, muted) {
		var me = this;
		var _kindArr = kind?[kind]:['video','audio','screen'];
		_kindArr.forEach(function(_kind) {
			var _muted = (user != me.user)?muted:me.isMuted(_kind);
			me.fireEvent('setMuted', user, _kind, _muted);
		});
	},

	toggleMute: function(kind) {
		this.setMuted(kind, !this.isMuted(kind));
	},

	mute: function(kind) {
		this.setMuted(kind, true);
	},

	unmute: function(kind) {
		this.setMuted(kind);
	},

	getConnection: function(user) {
		var me = this;
		if (me.isContact(user)) {
			return me.connections.find(function(connection) {
				return connection.user == user;
			});
		} else {
			return me.connections.find(function(connection) {
				return connection.user.get('id') == user;
			});
		}
	},

	getStream: function(video, audio) {
		var me = this;
		if (me.stream) {
			return Promise.resolve(me.stream);
		}
		if (me.gettingStream) {
			return me.gettingStream;
		}
		me.gettingStream = me.getPlugedDevices({
			withStream: true,
			video: video,
			audio: audio
		}).then(function(data) {
			me.gettingStream = null;
			me.stream = data.stream;

			return me.stream;
		});
		return me.gettingStream;
	},

	startScreenSharing: function() {
		var me = this;
		if (!me.stream) return;

		me.fireEvent('screenSharing', true);

		me.screenCapture.getConstraints().then(function(constraints) {
			return navigator.mediaDevices.getUserMedia(constraints);
		}).then(function(stream) {
			me.screenStream = stream;
			return me.mixer.setStream({video: me.screenStream});
		}).then(function() {
			me.unmute('screen');
		}).catch(function(error) {
			me.fireEvent('screenSharing', false);
			if (error == 'permission-denied') return;
			Ext6.Msg.alert('Ошибка', error);
		});
	},

	stopScreenSharing: function() {
		var me = this;
		if (!me.screenStream) return;

		me.mute('screen');
		me.stopStream({stream: me.screenStream});
		me.screenStream = null;
		if (me.stream) {
			me.mixer.setStream({video: me.stream});
		}
		me.fireEvent('screenSharing', false);
	},

	toggleScreenSharing: function() {
		var me = this;

		if (!me.screenStream) {
			me.startScreenSharing();
		} else {
			me.stopScreenSharing();
		}
	},

	startShowImage: function(image) {
		var me = this;
		me.mixer.setImage(image);
		
		/*var wnd = getWnd('swVideoChatWindow');
		html2canvas(wnd.selfContactViewPanel.el.dom).then(function(canvas) {
			image.src = canvas.toDataURL();
			me.mixer.setImage(image);
		});*/
		
		me.unmute('screen');
	},

	stopShowImage: function() {
		var me = this;
		me.mute('screen');
		me.mixer.setImage(null);
	},
	
	isRecording: function() {
		var me = this;
		return me.recorder && me.recorder.getState() == 'recording';
	},
	
	startRecording: function(callback) {
		callback = callback || Ext6.emptyFn;
		var me = this;
		
		if (me.recorder || me.getStatus() != 'call') {
			callback(false);
			return;
		}
		
		var streams = [
			me.mixer.getOutputStream(),
			...me.connections.map(connection => connection.stream)
		];
		
		var config = {
			room: me.room,
			type: 'video',
			video: {width: 1280, height: 1024},
			frameInterval: 1
		};
		
		me.recorder = me.recorderFactory.create(streams, config);
		me.recorder.startRecording();
		callback(true);
	},
	
	stopRecording: function(callback) {
		callback = callback || Ext6.emptyFn;
		var me = this;
		
		if (!me.recorder) {
			callback(false);
			return;
		}
		
		me.recorder.stopRecording(function() {
			me.saveRecord();			
			me.recorder.destroy();
			me.recorder = null;
			callback(true);
		});
	},
	
	saveRecord: function() {
		var me = this;
		
		if (!me.recorder || me.recorder.getState() == 'inactive') {
			return;
		}
		
		var room = me.recorder.room;
		var blob = me.recorder.getBlob();
		var reader = new FileReader;
		
		reader.onload = function() {
			var recordBuffer = reader.result;
			
			Ext6.Ajax.request({
				url: '/?c=VideoChat&m=saveCallRecord',
				binary: true,
				method: 'POST',
				headers: {'Content-Type': null},
				params: {room: room},
				binaryData: recordBuffer
			});
		};
		
		reader.readAsArrayBuffer(blob);
	},
	
	saveCall: function() {
		var me = this;
		var params = {};
		
		if (!me.callType && me.connections.length == 0) {
			return;
		}
		
		var pmUser_ids = me.connections.map(function(connection) {
			return connection.user.get('id');
		}).concat(me.user.get('id'));
		
		params.pmUser_iid = me.user.get('id');
		params.pmUser_ids = Ext6.encode(pmUser_ids);
		params.callType = me.callType;
		params.room = me.room;
		
		Ext6.Ajax.request({
			url: '/?c=VideoChat&m=saveCall',
			params: params,
			success: function(response) {
				me.socket.emit('callStarted', me.room);
			},
			failure: function() {
				me.hangup('fail');
			}
		});
	},
	
	updateCall: function(params) {
		var me = this;
		
		Ext6.Ajax.request({
			url: '/?c=VideoChat&m=updateCall',
			params: params
		});
	},

	call: function(users, video, audio) {
		var me = this;
		if (Ext6.isEmpty(users)) return;
		if (!Ext6.isArray(users)) users = [users];

		if (!me.callType) {
			me.callType = video?'videocall':audio?'audiocall':null;
		}

		if (me.getStatus() == 'free') {
			me.setStatus('waitAnswer');
		}

		var userKeys = [];
		users.forEach(function(user) {
			if (!me.getConnection(user)) {
				userKeys.push(user.get('id'));
				user.set(me.callType, true);
				new videoChat.lib.Connection(user);
			}
		});

		if (userKeys.length == 0) {
			return;
		}

		var getRoom = function(room) {
			me.room = room;
		};

		me.getStream(video, audio).then(function(stream) {
			if (!me.room) {
				me.socket.emit('createRoom', userKeys, getRoom);
			} else {
				me.socket.emit('invite', me.room, userKeys);
			}
		}).catch(function(error) {
			me.setStatus(null);
			me.showError(error);
		});
	},
	
	endCall: function() {
		var me = this;
		
		me.updateCall({
			room: me.room, 
			endDT: new Date()
		});
	},

	connectSocket: function(address) {
		var me = this;
		var socket = me.socket = io(address, {forceNew: true});

		socket.on('connect', function() {
			socket.emit('init', {
				pmUser_id: me.user.get('id'),
				hasCamera: !Ext6.isEmpty(me.settings.Camera),
				hasMicro: !Ext6.isEmpty(me.settings.Micro)
			}, function(success) {
				if (!success) {
					me.disconnectSocket();
					return;
				}

				if (me.connections.length == 0) {
					me.setStatus('free');
				}
				me.observeUsers();
				me.fireEvent('connect');
			});
		});

		socket.on('disconnect', function(cause) {
			me.fireEvent('disconnect');
			me.contactManager.globalStore.each(function(record) {
				record.set('Status', 'offline');
			});
			me.hangup('disconnect');
		});

		socket.on('observeUsersEvent', function(type, data) {
			me.fireEvent('observeUsers', type, data);
		});

		socket.on('message', function(message) {
			me.notifyMessages(me.messageStore.add(message));
		});

		socket.on('setMuted', function(userKey, kind, muted) {
			me.contactManager.getRecord(userKey).then(function(user) {
				user.set(kind+'muted', muted);
				me.onSetMuted(user, kind, muted);
			});
		});

		socket.on('invite', function(room, inviter) {
			if (me.getStatus() != 'free') {
				socket.emit('leave', room, 'busy');
				return;
			}

			me.room = room;
			me.setStatus('income');

			me.contactManager.getRecord(inviter).then(function(user) {
				me.dialog.show({
					userInfo: user.data,
					accept: function() {
						var wnd = getWnd('swVideoChatWindow');
						if (!wnd.isVisible()) wnd.show();
						socket.emit('joinRoom', me.room);
					},
					refuse: function() {
						me.hangup('refuse');
					}
				});
			});
		});

		socket.on('connectTo', function(userKey) {
			if (userKey == me.user.get('id')) {
				return;
			}

			me.contactManager.getRecord(userKey).then(function(user) {
				var connection = new videoChat.lib.Connection(user);

				me.getStream(me.callType == 'videocall', true).then(function(stream) {
					connection.initPeer();

					return me.mixer.setStream(me.stream);
				}).then(function(mixedStream) {
					mixedStream.getTracks().forEach(function (track) {
						connection.peer.addTrack(track, mixedStream);
					});

					return connection.peer.createOffer({iceRestart: true});
				}).then(function(offer) {
					connection.peer.setLocalDescription(offer);
					var offerObj = {offer: offer, video: me.callType == 'videocall', audio: true};
					me.socket.emit('offer', userKey, offerObj);
				}).catch(function(error) {
					connection.close('fail');
					me.showError(error);
				});
			});
		});

		socket.on('offer', function(userKey, offerObj) {
			if (offerObj.reset) {
				var connection = me.getConnection(userKey);
				connection.resetDescription = true;
				connection.peer.setRemoteDescription(offerObj.offer);
				return;
			}

			me.contactManager.getRecord(userKey).then(function(user) {
				var connection = new videoChat.lib.Connection(user);
				me.callType = offerObj.video?'videocall':'audiocall';
				user.set(me.callType, true);

				me.getStream(offerObj.video, offerObj.audio).then(function(stream) {
					connection.initPeer();

					return me.mixer.setStream(me.stream);
				}).then(function(mixedStream) {
					mixedStream.getTracks().forEach(function(track) {
						connection.peer.addTrack(track, mixedStream);
					});

					connection.peer.setRemoteDescription(offerObj.offer);

					return connection.peer.createAnswer();
				}).then(function(answer) {
					connection.peer.setLocalDescription(answer);
					socket.emit('answer', userKey, answer);
					connection.sendCandidates();
				}).catch(function(error) {
					connection.close('fail');
					me.showError(error);
				});
			});
		});

		socket.on('answer', function(userKey, answer) {
			var connection = me.getConnection(userKey);
			connection.user.set(me.callType, true);

			if (!connection || !connection.candidates[0]) {
				if (connection) {
					connection.close('fail');
				} else if (me.connections.length == 0) {
					me.hangup('fail');
				}
				//todo: сообщение о невозможности соединения
				return;
			}

			connection.peer.setRemoteDescription(answer);
			connection.sendCandidates();
		});

		socket.on('iceCandidate', function(userKey, candidate) {
			var connection = me.getConnection(userKey);
			if (!connection || !connection.peer) return;
			connection.peer.addIceCandidate(candidate).catch(me.showError);
		});
		
		socket.on('callStarted', function(room) {
			me.setStatus('call');
			
			if (me.callType == 'videocall') {
				me.unmute('video');
			}
			me.unmute('audio');
			me.unmute('screen');
		});

		socket.on('leave', function(userKey, cause) {
			var connection = me.getConnection(userKey);
			if (connection) {
				if (me.connections.length == 1) {
					me.endCall();
				}
				connection.close(cause);
			} else {
				me.contactManager.getRecord(userKey).then(function(user) {
					user.set('videocall', false);
					user.set('audiocall', false);
					if (me.getStatus() == 'income') {
						me.hangup(cause);
					}
				});
			}
		});
	},

	disconnectSocket: function() {
		var me = this;
		if (!me.socket) return;
		me.socket.disconnect();
		me.socket = null;
	},

	observeUsers: function(userList) {
		var me = this;
		if (!me.socket || !me.status) return;
		if (!Ext6.isArray(userList)) {
			var records = me.contactManager.getObservable();
			userList = records.map(record => record.get('id'));
		}
		me.socket.emit('observeUsers', userList);
	},

	clearObserveUsers: function() {
		var me = this;
		if (!me.socket || !me.status) return;
		me.socket.emit('observeUsers', []);
	},

	hangup: function(cause) {
		var me = this;

		if (!me.socket || me.getStatus() == 'free') {
			return;
		}

		if (cause == 'notFound') {
			Ext6.Msg.alert('Сообщение', 'Собеседник не найден');
		}
		if (cause == 'fail') {
			Ext6.Msg.alert('Сообщение', 'Не удалось соединиться');
		}
		if (cause == 'busy') {
			Ext6.Msg.alert('Сообщение', 'Контакт занят');
		}
		if (me.status == 'waitAnswer' && cause == 'refuse') {
			Ext6.Msg.alert('Сообщение', 'Вызов отклонен');
		}
		
		me.stopRecording();

		if (me.mixer) me.mixer.stop();
		me.stopStream({stream: me.stream});
		me.connections.slice().forEach(function(connection) {
			connection.close(cause, false);
		});

		me.socket.emit('leave', me.room, cause);
		me.connections = [];
		me.stream = null;
		me.gettingStream = null;
		me.room = null;
		me.callType = null;

		me.stopScreenSharing();

		if (cause == 'disconnect') {
			me.setStatus(null, cause);
		} else {
			me.setStatus('free', cause);
		}

		me.dialog.hide();
	}
});