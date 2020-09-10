Ext.define('common.InteractiveMapSMP.store.CmpCallCardStore', {
	extend: 'Ext.data.Store',
	storeId: 'InteractiveMapSMP_CmpCallCardStore',
	model: 'common.InteractiveMapSMP.model.CmpCallCard',
	autoLoad: false,
	stripeRows: true,
	proxy: {
		type: 'ajax',
		url: '/?c=CmpCallCard4E&m=loadSMPInteractiveMapWorkPlace',
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