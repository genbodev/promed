Ext.define('common.HeadDoctorWP.store.MedPersonalStore', {
    extend: 'Ext.data.Store',
	autoLoad: true,
	storeId: 'medPersonalStoreHD',
	fields: [
		{name: 'MedPersonal_id', type:'int'},
		{name: 'MedPersonal_Code', type:'int'},
		{name: 'MedPersonal_Fio', type:'string'},
		{name: 'PostMed_Name', type:'string'},
		{name: 'MedStaffFact_Stavka', type:'string'},
		{name: 'MedStaffFact_id', type:'int'},
		{name: 'WorkData_begDate', type:'string'},
		{name: 'WorkData_endDate', type:'string'},
		{name: 'LpuBuilding_Name', type:'string'}
	],
	proxy: {
		limitParam: undefined,
		startParam: undefined,
		paramName: undefined,
		pageParam: undefined,
		//noCache:false,
		type: 'ajax',
		url: '/?c=MedPersonal4E&m=getMedPersonalCombo',
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