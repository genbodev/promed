
Ext6.define('EvnPLDispDop13PrescrStoreModel', {
	extend: 'Ext6.data.Model',
	alias: 'model.EvnPLDispDop13PrescrStoreModel',
	idProperty: 'UslugaComplex_id',
	fields: [
		{ name: 'object', type: 'string'},
		{ name: 'UslugaComplex_id', type: 'int'},
		{ name: 'UslugaComplex_Name', type: 'string'},
		{ name: 'MedService_id', type: 'int'},
		{ name: 'Resource_id', type: 'int'},
		{ name: 'EvnPrescr_IsExec', type: 'int'},
		{ name: 'EvnUslugaPar_id', type: 'int'},
		{ name: 'Lpu_id', type: 'int'},
		{ name: 'MedService_Nick', type: 'string'},
		{ name: 'MedService_Name', type: 'string'},
		{ name: 'LpuSection_Name', type: 'string'},
		{ name: 'EvnStatus_SysNick', type: 'string'},
		{ name: 'Lpu_Nick', type: 'string'},
		{ name: 'LpuUnit_id', type: 'int'},
		{ name: 'LpuSection_id', type: 'int'},
		{ name: 'LpuSectionProfile_id', type: 'int'},
		{ name: 'formatTime', type: 'string'},
		{ name: 'formatDate', type: 'string'},
		{ name: 'timetable', type: 'string'},
		{ name: 'EvnUsluga_Date', type: 'date', format: 'd.m.Y', dateFormat: 'd.m.Y'},//дата выполненной услуги
		{ name: 'TimetableResource_begTime', type: 'date', dateFormat: 'd.m.Y H:i'},//дата бирки ресурса
		{ name: 'TimetableMedService_begTime', type: 'date', dateFormat: 'd.m.Y H:i'}//дата бирки службы
	],
});

Ext6.define('common.EMK.EvnPLDispDop.store.PrescrStore', {
	extend: 'Ext6.data.Store',
	alias: 'store.EvnPLDispDop13PrescrStore',
	model: 'EvnPLDispDop13PrescrStoreModel',
	proxy: {
		type: 'ajax',
		actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
		url: '/?c=EvnPLDispDop13&m=loadEvnPLDispDop13PrescrList',
		reader: {
			type: 'json',
			rootProperty: 'data'
		},
		extraParams: '{prescrExtraParams}'
	}
	// Пока сортировка не нужна
	/*,
	sorters: [
		'DopDispInfoConsent_id'
	]*/
});