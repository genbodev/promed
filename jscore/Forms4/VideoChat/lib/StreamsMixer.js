Ext6.define('videoChat.lib.StreamsMixer', {
	video: null,
	image: null,
	canvas: null,
	context: null,
	rendering: false,

	inputVideoStream: null,
	inputAudioStream: null,

	interimStream: null,
	outputStream: null,

	streamPromise: null,

	constructor: function(conf) {
		var me = this;

		me.interimStream = new MediaStream();

		me.canvas = document.createElement('canvas');
		me.context = me.canvas.getContext('2d');

		me.video = document.createElement('video');
		me.video.srcObject = me.interimStream;
		me.video.muted = true;
		me.video.onplay = function() {
			me.startRender();
		};
		me.video.onpause = function() {
			me.stopRender();
		};

		if (conf) {
			me.setStream(conf);
		}
	},

	setStream: function(conf) {
		var me = this;

		if (!conf) {
			return;
		}
		if (me.streamPromise) {
			return me.streamPromise;
		}

		me.video.pause();

		me.image = null;

		if (conf instanceof MediaStream) {
			me.inputVideoStream = conf;
			me.inputAudioStream = conf;
		} else {
			if (conf.video) me.inputVideoStream = conf.video;
			if (conf.audio) me.inputAudioStream = conf.audio;
		}

		me.interimStream.getTracks().forEach(function(track) {
			me.interimStream.removeTrack(track);
		});

		if (me.inputVideoStream) {
			me.inputVideoStream.getVideoTracks().forEach(function(track) {
				me.interimStream.addTrack(track);
			});
		}
		if (me.inputAudioStream) {
			me.inputAudioStream.getAudioTracks().forEach(function(track) {
				me.interimStream.addTrack(track);
			});
		}

		me.streamPromise = null;
		var playPromise = me.video.play();
		
		if (!playPromise || me.video.readyState > 1) {
			return Promise.resolve(me.getOutputStream());
		} else if (playPromise) {
			me.streamPromise = playPromise.then(function() {
				me.streamPromise = null;
				return me.getOutputStream();
			});
		}
		
		return me.streamPromise;
	},

	setImage: function(image) {
		var me = this;

		me.video.pause();
		me.image = image;
		
		me.video.play();

		return me.getOutputStream();
	},

	getOutputStream: function() {
		var me = this;

		if (!me.outputStream) {
			me.outputStream = me.canvas.captureStream();

			me.interimStream.getAudioTracks().forEach(function(track) {
				me.outputStream.addTrack(track);
			});
		}

		return me.outputStream;
	},

	stop: function() {
		var me = this;

		me.video.pause();

		me.interimStream.getTracks().forEach(function(track) {
			me.interimStream.removeTrack(track);
		});

		if (me.outputStream) {
			me.outputStream.getTracks().forEach(function(track) {
				me.outputStream.removeTrack(track);
			});
			me.outputStream = null;
		}
	},

	/**
	 * @protected
	 */
	startRender: function() {
		var me = this;
		if (!me.rendering) {
			me.rendering = true;
			me.render();
		}
	},

	/**
	 * @protected
	 */
	stopRender: function() {
		var me = this;
		if (me.rendering) {
			me.rendering = false;
		}
	},

	/**
	 * @protected
	 */
	render: function() {
		var me = this;

		if (!me.rendering) {
			me.canvas.width = 0;
			me.canvas.height = 0;
			return;
		}

		if (me.image) {
			if (me.image != me.prevImage) {
				me.prevImage = me.image;
				me.canvas.width = me.image.width;
				me.canvas.height = me.image.height;
				me.context.drawImage(me.image, 0, 0);
			}
		} else {
			if (me.canvas.width != me.video.videoWidth) {
				me.canvas.width = me.video.videoWidth;
			}
			if (me.canvas.height != me.video.videoHeight) {
				me.canvas.height = me.video.videoHeight;
			}

			if (me.canvas.width > 0 && me.canvas.height > 0) {
				me.context.drawImage(me.video, 0, 0);
			}
		}

		setTimeout(me.render.bind(me), 1000/60);
	}
});