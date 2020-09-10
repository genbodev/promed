Ext6.define('usluga.components.models.OperAnestModel', {
	extend: 'Ext6.data.Model',
	alias: 'model.OperAnestModel',
	idProperty: 'EvnUslugaOperAnest_id',
	fields: [
		{
			name: 'accessType',
			type: 'string'
		},{
			name: 'EvnUslugaOperAnest_id',
			type: 'int'
		}, {
			name: 'EvnUslugaOperAnest_pid',
			type: 'int'
		}, {
			name: 'AnesthesiaClass_id',
			type: 'int'
		}, {
			name: 'AnesthesiaClass_Code',
			type: 'string'
		}, {
			name: 'AnesthesiaClass_Name',
			type: 'string'
		}
	],

	proxy: {
		type: 'ajax',
		api: {
			destroy: '/?c=EvnUslugaOperAnest&m=deleteEvnUslugaOperAnest',
			create: '/?c=EvnUslugaOperAnest&m=saveEvnUslugaOperAnest',
			update: '/?c=EvnUslugaOperAnest&m=saveEvnUslugaOperAnest'
		},
		actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
		url: '/?c=EvnUslugaOperAnest&m=loadEvnUslugaOperAnestGrid',
		reader: {
			type: 'json'
		},
		writer: 'QueryStringWriter'
	}
});