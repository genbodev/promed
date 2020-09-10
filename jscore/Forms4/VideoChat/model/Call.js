var calcDuration = function(value, record) {
	var begDT = record.get('begDT');
	var endDT = record.get('endDT');
	
	if (!begDT || !endDT) {
		return null;
	}
	
	var duration = (endDT.getTime() - begDT.getTime()) / 1000;
	
	var hours = Math.floor(duration / 3600);
	duration -= hours * 3600;
	
	var minutes = Math.floor(duration / 60);
	duration -= minutes * 60;
	
	var seconds = duration;
	
	return [hours, minutes, seconds].map(function(timepart) {
		return ('0' + timepart).slice(-2);
	}).join(':');
};

var convertCallType = function(value, record) {
	switch(record.get('callTypeNick')) {
		case 'videocall': return 'Видео вызов';
		case 'audiocall': return 'Аудио вызов';
		default: return null;
	}
};

Ext6.define('videoChat.model.Call', {
	extend: 'Ext.data.Model',
	alias: 'model.videochatcall',
	fields: [
		{name: 'id', type: 'int'},
		{name: 'begDT', type: 'date'},
		{name: 'endDT', type: 'date'},
		{name: 'duration', type: 'string', convert: calcDuration, depends: ['begDT', 'endDT']},
		{name: 'callTypeNick', type: 'string', mapping: 'callType'},
		{name: 'callTypeName', type: 'string', convert: convertCallType},
		{name: 'pmUser_iid', type: 'int'},
		{name: 'pmUser_ids'}
	]
});