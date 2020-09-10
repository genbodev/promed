
Ext.define('common.DispatcherDirectWP.store.EmergencyTeamStatusStore', {
    extend: 'Ext.data.Store',
	storeId: 'EmergencyTeamStatus',
	model: 'common.DispatcherDirectWP.model.EmergencyTeamStatus',
	autoLoad: true,
	stripeRows: true,
	proxy: {
		type: 'ajax',
		url: '/?c=EmergencyTeam4E&m=loadEmergencyTeamStatuses',
		reader: {
			type: 'json',
			successProperty: 'success',
			root: 'data'
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