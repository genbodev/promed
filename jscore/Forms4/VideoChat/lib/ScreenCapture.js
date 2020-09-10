Ext6.define('videoChat.lib.ScreenCapture', {
	singleton: true,

	constructor: function() {
		var me = this;

		log('ScreenCapture constructor');

		me.getScreenId = function(callback, custom_parameter) {
			if(navigator.userAgent.indexOf('Edge') !== -1 && (!!navigator.msSaveOrOpenBlob || !!navigator.msSaveBlob)) {
				// microsoft edge => navigator.getDisplayMedia(screen_constraints).then(onSuccess, onFailure);
				callback({
					video: true
				});
				return;
			}

			if (!!navigator.mozGetUserMedia) {
				callback(null, 'firefox', {
					video: {mediaSource: 'screen'}
				});
				return;
			}

			window.addEventListener('message', onIFrameCallback);

			function onIFrameCallback(event) {
				if (!event.data) return;

				if (event.data.chromeMediaSourceId) {
					if (event.data.chromeMediaSourceId === 'PermissionDeniedError') {
						callback('permission-denied');
					} else {
						callback(null, event.data.chromeMediaSourceId, getScreenConstraints(null, event.data.chromeMediaSourceId, event.data.canRequestAudioTrack));
					}

					// this event listener is no more needed
					window.removeEventListener('message', onIFrameCallback);
				}

				if (event.data.chromeExtensionStatus) {
					callback(event.data.chromeExtensionStatus, null, getScreenConstraints(event.data.chromeExtensionStatus));

					// this event listener is no more needed
					window.removeEventListener('message', onIFrameCallback);
				}
			}

			if(!custom_parameter) {
				setTimeout(postGetSourceIdMessage, 100);
			}
			else {
				setTimeout(function() {
					postGetSourceIdMessage(custom_parameter);
				}, 100);
			}
		};

		function getScreenConstraints(error, sourceId, canRequestAudioTrack) {
			var screen_constraints = {
				audio: false,
				video: {
					mandatory: {
						chromeMediaSource: error ? 'screen' : 'desktop',
						//maxWidth: window.screen.width > 1920 ? window.screen.width : 1920,
						//maxHeight: window.screen.height > 1080 ? window.screen.height : 1080
					},
					optional: []
				}
			};

			if(!!canRequestAudioTrack) {
				screen_constraints.audio = {
					mandatory: {
						chromeMediaSource: error ? 'screen' : 'desktop',
						// echoCancellation: true
					},
					optional: []
				};
			}

			if (sourceId) {
				screen_constraints.video.mandatory.chromeMediaSourceId = sourceId;

				if(screen_constraints.audio && screen_constraints.audio.mandatory) {
					screen_constraints.audio.mandatory.chromeMediaSourceId = sourceId;
				}
			}

			return screen_constraints;
		}

		function postGetSourceIdMessage(custom_parameter) {
			if (!iframe) {
				loadIFrame(function() {
					postGetSourceIdMessage(custom_parameter);
				});
				return;
			}

			if (!iframe.isLoaded) {
				setTimeout(function() {
					postGetSourceIdMessage(custom_parameter);
				}, 100);
				return;
			}

			if(!custom_parameter) {
				iframe.contentWindow.postMessage({
					captureSourceId: true
				}, '*');
			}
			else if(!!custom_parameter.forEach) {
				iframe.contentWindow.postMessage({
					captureCustomSourceId: custom_parameter
				}, '*');
			}
			else {
				iframe.contentWindow.postMessage({
					captureSourceIdWithAudio: true
				}, '*');
			}
		}

		var iframe;

		function loadIFrame(loadCallback) {
			if (iframe) {
				loadCallback();
				return;
			}

			iframe = document.createElement('iframe');
			iframe.onload = function() {
				iframe.isLoaded = true;

				loadCallback();
			};
			iframe.src = 'https://www.webrtc-experiment.com/getSourceId/';
			iframe.style.display = 'none';
			(document.body || document.documentElement).appendChild(iframe);
		}

		me.getChromeExtensionStatus = function(callback) {
			// for Firefox:
			if (!!navigator.mozGetUserMedia) {
				callback('installed-enabled');
				return;
			}

			window.addEventListener('message', onIFrameCallback);

			function onIFrameCallback(event) {
				if (!event.data) return;

				if (event.data.chromeExtensionStatus) {
					callback(event.data.chromeExtensionStatus);

					// this event listener is no more needed
					window.removeEventListener('message', onIFrameCallback);
				}
			}

			setTimeout(postGetChromeExtensionStatusMessage, 100);
		};

		function postGetChromeExtensionStatusMessage() {
			if (!iframe) {
				loadIFrame(postGetChromeExtensionStatusMessage);
				return;
			}

			if (!iframe.isLoaded) {
				setTimeout(postGetChromeExtensionStatusMessage, 100);
				return;
			}

			iframe.contentWindow.postMessage({
				getChromeExtensionStatus: true
			}, '*');
		}
	},

	getConstraints: function() {
		var me = this;

		return new Promise(function(resolve) {
			me.getScreenId(function(error, sourceId, screen_constraints) {
				if (error) throw new Error(error);
				resolve(screen_constraints);
			});
		});
	}
});