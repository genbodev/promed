Ext6.define('usluga.components.models.OperBrigModel', {
	extend: 'Ext6.data.Model',
	alias: 'model.OperBrigModel',
	idProperty: 'EvnUslugaOperBrig_id',
	fields: [
		{
			name: 'accessType',
			type: 'string'
		}, {
			name: 'EvnUslugaOperBrig_id',
			type: 'int'
		}, {
			name: 'EvnUslugaOperBrig_pid',
			type: 'int'
		}, {
			name: 'MedPersonal_id',
			type: 'int'
		}, {
			name: 'MedStaffFact_id',
			type: 'int'
		}, {
			name: 'SurgType_Code',
			type: 'int'
		}, {
			name: 'SurgType_id',
			type: 'int'
		}, {
			name: 'MedPersonal_Code',
			type: 'string'
		}, {
			name: 'MedPersonal_Fio',
			type: 'string'
		}, {
			name: 'SurgType_Name',
			type: 'string'
		}, {
			name: 'EvnUslugaOper_setDate',
			type: 'date',
			dateFormat: 'd.m.Y',
			dateWriteFormat: 'Y.m.d'
		}],

	proxy: {
		type: 'ajax',
		api: {
			destroy: '/?c=EvnUslugaOperBrig&m=deleteEvnUslugaOperBrig',
			create: '/?c=EvnUslugaOperBrig&m=saveEvnUslugaOperBrig',
			update: '/?c=EvnUslugaOperBrig&m=saveEvnUslugaOperBrig'
		},
		actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
		url: '/?c=EvnUslugaOperBrig&m=loadEvnUslugaOperBrigGrid',
		reader: {
			type: 'json'
		},
		writer: 'QueryStringWriter'
	}
});