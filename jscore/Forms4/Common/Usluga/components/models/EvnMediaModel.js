Ext6.define('usluga.components.models.EvnMediaModel', {
	extend: 'Ext6.data.Model',
	alias: 'model.EvnMediaModel',
	idProperty: 'EvnMediaData_id',
	fields: [
		{ name: 'EvnMediaData_id', type: 'int' },
		{ name: 'EvnMediaData_FileName', type: 'string' },
		{ name: 'EvnMediaData_FilePath', type: 'string' },
		{ name: 'EvnMediaData_Comment', type: 'string' }
	],
	proxy: {
		type: 'ajax',
		api: {
			destroy: '/?c=EvnMediaFiles&m=remove'
		},
		actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
		url: '/?c=EvnMediaFiles&m=loadEvnMediaDataPanel',
		reader: {
			type: 'json',
			rootProperty: 'data'
		},
		writer: 'QueryStringWriter'
	}
});