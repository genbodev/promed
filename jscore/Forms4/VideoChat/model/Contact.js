Ext6.define('videoChat.model.Contact', {
	extend: 'Ext.data.Model',
	alias: 'model.videochatcontact',
	fields: [
		{name: 'id', mapping: 'pmUser_id'},
		{name: 'pmUser_id', type: 'int'},
		{name: 'SurName', type: 'string'},
		{name: 'FirName', type: 'string'},
		{name: 'SecName', type: 'string'},
		{name: 'FullName', type: 'string'},
		{name: 'Login', type: 'string'},
		{name: 'Avatar', type: 'string'},
		{name: 'Status', type: 'string', defaultValue: 'none'},
		{name: 'hasCamera', type: 'bool'},
		{name: 'hasMicro', type: 'bool'},
		{name: 'videocall', type: 'bool'},
		{name: 'audiocall', type: 'bool'},
		{name: 'videomuted', type: 'bool', defaultValue: false},
		{name: 'audiomuted', type: 'bool', defaultValue: false},
		{name: 'screenmuted', type: 'bool', defaultValue: true},
		{name: 'LpuList'}
	]
});