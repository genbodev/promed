Ext6.define('usluga.components.models.EvnAggModel', {
	extend: 'Ext6.data.Model',
	alias: 'model.EvnAggModel',
	idProperty: 'EvnAgg_id',
	fields: [
		{
			name: 'accessType',
			type: 'string'
		}, {
			name: 'EvnAgg_id',
			type: 'int'
		}, {
			name: 'EvnAgg_pid',
			type: 'int'
		}, {
			name: 'Person_id',
			type: 'int'
		}, {
			name: 'PersonEvn_id',
			type: 'int'
		}, {
			name: 'Server_id',
			type: 'int'
		}, {
			name: 'AggType_id',
			type: 'int'
		}, {
			name: 'AggWhen_id',
			type: 'int'
		}, {
			name: 'AggType_Name',
			type: 'string'
		}, {
			name: 'AggWhen_Name',
			type: 'string'
		}, {
			name: 'EvnAgg_setDate',
			type: 'date',
			dateFormat: 'd.m.Y',
			dateWriteFormat: 'Y.m.d'
		}, {
			name: 'EvnAgg_setTime',
			type: 'string'
		}],

	proxy: {
		type: 'ajax',
		api: {
			destroy: '/?c=EvnAgg&m=deleteEvnAgg',
			create: '/?c=EvnAgg&m=saveEvnAgg',
			update: '/?c=EvnAgg&m=saveEvnAgg'
		},
		actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
		url: '/?c=EvnAgg&m=loadEvnAggGrid',
		reader: {
			type: 'json'
		},
		writer: 'QueryStringWriter'
	}
});