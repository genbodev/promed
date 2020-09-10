Ext6.define('videoChat.lib.Connection', {
	user: null,
	peer: null,
	stream: null,
	candidates: null,

	engine: null,

	constructor: function(user) {
		var me = this;

		if (!sw.Promed.VideoChat) {
			throw new Error('sw.Promed.VideoChat is undefined');
		}

		me.user = user;
		me.engine = sw.Promed.VideoChat;
		me.candidates = [];

		var connections = me.engine.connections;
		var index = connections.findIndex(conn => conn.user == user);
		if (index >= 0) {
			connections[index] = me;
		} else {
			connections.push(me);
		}

		me.engine.addEvent('observeUsers', me.onObserve, me);
	},

	onObserve: function(type, data) {
		var me = this;
		if (type == 'change' &&
			data.pmUser_id == me.user.get('id') &&
			data.status != 'online'
		) {
			me.close();
		}
	},

	initPeer: function(config) {
		var me = this;
		config = Ext6.apply({iceServers: []}, config);

		if (config.iceServers.length == 0 && me.engine.options.iceServers) {
			var iceServers = Ext6.isArray(me.engine.options.iceServers)
				?me.engine.options.iceServers:[me.engine.options.iceServers];
			config.iceServers = iceServers;
		}

		var options = {
			optional: [
				{DtlsSrtpKeyAgreement: true}
			]
		};

		me.peer = new RTCPeerConnection(config, options);

		me.peer.onicecandidate = function (event) {
			me.candidates.push(event.candidate);
			me.sendCandidate(event.candidate);
		};

		me.peer.oniceconnectionstatechange = function(event) {
			if (me.peer.iceConnectionState == 'failed') {
				me.close('fail');
			}
			if (me.peer.iceConnectionState == 'connected') {
				if (me.engine.getStatus() != 'call') {
					me.engine.setStatus('connect');
				}
				me.engine.fireEvent('connectUser', me);
			}
			if (me.peer.iceConnectionState == 'disconnected') {
				me.close('disconnect');
			}
			if (me.peer.iceConnectionState == 'closed') {
				me.close();
			}
		};

		me.peer.ontrack = function(event) {
			me.stream = event.streams[0];
		};

		return me.peer;
	},

	sendCandidate: function(candidate) {
		var me = this;
		me.engine.socket.emit('iceCandidate', me.user.get('id'), candidate);
	},

	sendCandidates: function() {
		var me = this;
		me.candidates.forEach(function(candidate) {
			me.sendCandidate(candidate);
		});
	},

	stopStreams: function() {
		var me = this;
		var streams = [];

		if (me.peer && me.peer.iceConnectionState != 'closed') {
			streams = streams.concat(
				me.peer.getRemoteStreams()
			);
		}

		streams.forEach(function(stream) {
			me.engine.stopStream({stream: stream})
		});
	},

	close: function(cause, hangup = true) {
		var me = this;
		var connections = me.engine.connections;

		me.user.set('videocall', false);
		me.user.set('audiocall', false);

		me.stopStreams();
		if (me.peer) me.peer.close();

		var index = connections.findIndex(conn => conn == me);
		if (index >= 0) connections.splice(index, 1);

		if (connections.length == 0 && hangup) {
			me.engine.hangup(cause);
		}

		me.engine.fireEvent('disconnectUser', me);
		me.engine.removeEvent('observeUsers', me.onObserve, me);
	}
});