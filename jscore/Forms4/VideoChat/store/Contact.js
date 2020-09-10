Ext6.define('videoChat.store.Contact', {
	extend: 'Ext6.data.Store',
	managing: false,
	model: 'videoChat.model.Contact',
	proxy: {
		type: 'ajax',
		url: '/?c=VideoChat&m=loadPMUserContactList',
		reader: {
			type: 'json', 
			rootProperty: 'data', 
			totalProperty: 'totalCount'
		}
	},
	sorters: [
		'SurName',
		'FirName',
		'SecName'
	]
});