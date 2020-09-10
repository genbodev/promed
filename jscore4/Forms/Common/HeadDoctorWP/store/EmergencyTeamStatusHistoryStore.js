Ext.define('common.HeadDoctorWP.store.EmergencyTeamStatusHistoryStore', {
    extend: 'Ext.data.Store',
	autoLoad: false,
	storeId: 'emergencyTeamStatusHistoryStore',
	fields: [
		{name: 'EmergencyTeamStatusHistory_insDT', type:'string'},
		{name: 'EmergencyTeamStatus_Name', type:'string'},
		{name: 'CmpCallCard_Ngod', type:'string'}
	],
	proxy: {
		limitParam: 100,
		startParam: undefined,
		paramName: undefined,
		pageParam: undefined,
		//noCache:false,
		type: 'ajax',
		url: '/?c=EmergencyTeam4E&m=loadEmergencyTeamStatusesHistory',
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