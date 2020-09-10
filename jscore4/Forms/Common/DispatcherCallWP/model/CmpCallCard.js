Ext.define('common.DispatcherCallWP.model.CmpCallCard', {
    extend: 'Ext.data.Model',
	idProperty: 'CmpCallCard_id',
	fields: [
		{
			name: 'CmpCallCard_id',
			type: 'int'
		},
		{
			name: 'CmpCallCard_prmDate',
			type: 'date'
		},
		{
			name: 'CmpCallCard_Numv',
			type: 'int'
		},
		{
			name: 'CmpCallCard_Ngod',
			type: 'int'
		},
		{
			name: 'Person_FIO',
			type: 'string'
		},
		{
			name: 'personAgeText',
			type: 'string'
		},
		{
			name: 'Adress_Name',
			type: 'string'
		},
		{
			name: 'CmpCallType_Name',
			type: 'string'
		},
		{
			name: 'CmpCallCard_IsExtraText',
			type: 'string'
		},
		{
			name: 'CmpReason_Name',
			type: 'string'
		},
		{
			name: 'CmpCallCardStatusType_id',
			type: 'int'
		},
		{
			name: 'CmpCallCardStatusType_Name',
			type: 'string'
		},
		{
			name: 'CmpCallCard_Comm',
			type: 'string'
		},
		{
			name: 'CmpCallCard_IsExtra',
			type: 'string'
		},
		{
			name: 'CmpGroup_id',
			type: 'int'
		},

	]
});
