Ext6.define('videoChat.store.Message', {
	extend: 'Ext6.data.Store',
	model: 'videoChat.model.Message',
	proxy: {
		type: 'ajax',
		url: '/?c=VideoChat&m=loadMessageList',
		reader: {type: 'json', rootProperty: 'data'}
	},
	sorters: ['dt']
});