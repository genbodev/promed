Ext6.define('usluga.associatedwindows.models.AggFormModel', {
	extend: 'Ext6.data.Model',
	alias: 'model.AggFormModel',
	idProperty: 'EvnAgg_id',
	fields: [
		{name: 'accessType'},
		{name: 'EvnAgg_id'},
		{name: 'EvnAgg_pid'},
		{name: 'Person_id'},
		{name: 'PersonEvn_id'},
		{name: 'Server_id'},
		{name: 'EvnAgg_setDate', type: 'date', dateFormat: 'd.m.Y' },
		{name: 'EvnAgg_setTime'},
		{name: 'AggType_id'},
		{name: 'AggWhen_id'}
	]
});