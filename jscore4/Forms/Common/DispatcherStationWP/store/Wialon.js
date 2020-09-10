

Ext.define('common.DispatcherStationWP.store.Wialon', {
    extend: 'Ext.data.Store',
	storeId: 'DispatcherStationWP_Wialon',
	autoLoad: false,
	stripeRows: true,
	model: 'common.DispatcherStationWP.model.WialonMod',
	proxy: {
		limitParam: undefined,
		startParam: undefined,
		paramName: undefined,
		pageParam: undefined,
		type: 'ajax',
		url: '?c=Wialon&m=getAllAvlUnitsForMerge',
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