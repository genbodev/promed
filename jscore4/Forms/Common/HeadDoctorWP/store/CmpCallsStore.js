Ext.define('common.HeadDoctorWP.store.CmpCallsStore', {
    extend: 'Ext.data.Store',
	storeId: 'HeadDoctorWP_CmpCallsStore',
	model: 'common.HeadDoctorWP.model.CmpCallCard',
	autoLoad: false,
	stripeRows: true,
	groupField: 'CmpGroup_id',
	proxy: {
		type: 'ajax',
		url: '/?c=CmpCallCard4E&m=loadSMPHeadDoctorWorkPlace',
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