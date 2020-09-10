Ext6.define('videoChat.model.Message', {
	extend: 'Ext.data.Model',
	alias: 'model.videochatmessage',
	fields: [
		{name: 'id', type: 'int'},
		{name: 'pmUser_sid', type: 'int'},
		{name: 'text', type: 'string'},
		{name: 'file_name', type: 'string'},
		{name: 'dt', type: 'date'}
	]
});