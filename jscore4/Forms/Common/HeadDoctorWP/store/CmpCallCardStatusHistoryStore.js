Ext.define('common.HeadDoctorWP.store.CmpCallCardStatusHistoryStore', {
    extend: 'Ext.data.Store',
	autoLoad: false,
	storeId: 'сmpCallCardStatusHistoryHistoryStore',
	fields: [
		{name: 'CmpCallCardStatus_insDT', type:'string'},
		{name: 'CmpCallCardStatusType_Name', type:'string'},
		{name: 'pmUser_FIO', type:'string'},
		{name: 'CmpCallCard_id', type:'int'}
	],
	proxy: {
		limitParam: 100,
		startParam: undefined,
		paramName: undefined,
		pageParam: undefined,
		//noCache:false,
		type: 'ajax',
		url: '/?c=CmpCallCard4E&m=loadCmpCallCardStatusHistory',
		reader: {
			type: 'json',
			successProperty: 'success',
			root: 'data'
		},
		actionMethods: {
			create : 'POST',
			read   : 'POST',
			update : 'POST',
			destroy: 'POST'
		}
	}
});