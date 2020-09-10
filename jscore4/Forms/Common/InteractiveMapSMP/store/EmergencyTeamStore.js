Ext.define('common.InteractiveMapSMP.store.EmergencyTeamStore', {
	extend: 'Ext.data.Store',
	storeId: 'InteractiveMapSMP_EmergencyTeamStore',
	model: 'common.InteractiveMapSMP.model.EmergencyTeam',
	autoLoad: false,
	stripeRows: true,
	// groupField: 'EmergencyTeamBuildingName',
	sorters: [
	],
	proxy: {
		type: 'ajax',
		url: '/?c=EmergencyTeam4E&m=loadEmergencyTeamOperEnvForInteractiveMap',
		reader: {
			type: 'json',
			root: 'data',
			successProperty: 'success'
		},
		limitParam: undefined,
		startParam: undefined,
		paramName: undefined,
		pageParam: undefined,
		actionMethods: {
			create : 'POST',
			read   : 'POST',
			update : 'POST',
			destroy: 'POST'
		}
	}
});