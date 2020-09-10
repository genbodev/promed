Ext6.define('videoChat.lib.RecorderFactory', {
	singleton: true,

	create: function(mediaStream, config) {
		var recorder = new RecordRTC(mediaStream, config);
		
		recorder.room = config.room;
		
		return recorder;
	}
});