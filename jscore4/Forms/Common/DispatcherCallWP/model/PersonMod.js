
Ext.define('common.DispatcherCallWP.model.PersonMod', {
	extend: 'Ext.data.Model',	
	fields: [	
	{
		name: 'Person_id',
		type: 'int'
	},
	{
		name: 'Server_id',
		type: 'int'
	},
	{
		name: 'PersonEvn_id',
		type: 'int'
	},
	{
		name: 'PersonSurName_SurName',
		type: 'string'
	},
	{
		name: 'PersonFirName_FirName',
		type: 'string'
	},
	{
		name: 'PersonSecName_SecName',
		type: 'string'
	},
	{
		name: 'Polis_Ser',
		type: 'int'
	},
	{
		name: 'Polis_Num',
		type: 'string'
	},

	{
		name: 'Polis_EdNum',
		type: 'string'
	},

	{
		name: 'Person_Age',
		type: 'int'
	},

	{
		name: 'UAddress_AddressText',
		type: 'string'
	},	
	{
		name: 'PAddress_AddressText',
		type: 'string'
	},	
	{
		name: 'PersonBirthDay_BirthDay',
		type: 'string'
	},

	{
		name: 'Person_deadDT',
		type: 'string'
	},

	{
		name: 'Sex_id',
		type: 'int'
	},

	{
		name: 'Lpu_Nick',
		type: 'string'
	},

	{
		name: 'CmpLpu_id',
		type: 'int'
	},

	{
		name: 'Person_isOftenCaller',
		type: 'int'
	},
	{
		name: 'countCloseCards',
		type: 'int'
	},

	{
		name: 'Person_IsRefuse',
		type: 'string'
	},

	{
		name: 'Person_IsDead',
		type: 'string'
	},

	{
		name: 'Person_IsFedLgot',
		type: 'string'
	},

	{
		name: 'Person_IsRegLgot',
		type: 'string'
	},

	{
		name: 'Person_Is7Noz',
		type: 'string'
	},

	{
		name: 'Person_IsBDZ',
		type: 'string'
	},

	{
		name: 'PersonCard_IsDms',
		type: 'string'
	}
	
	]
});