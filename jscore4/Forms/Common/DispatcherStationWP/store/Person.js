

Ext.define('common.DispatcherStationWP.store.Person', {
    extend: 'Ext.data.Store',
	storeId: 'DispatcherStationWP_Person',
	model: 'common.DispatcherStationWP.model.PersonMod',
	autoLoad: false,
	stripeRows: true,
	proxy: {
		limitParam: undefined,
		startParam: undefined,
		paramName: undefined,
		pageParam: undefined,
		type: 'ajax',
		url: '/?c=Person4E&m=getPersonSearchGrid',
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
