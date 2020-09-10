
Ext.define('common.DispatcherStationWP.store.TNC', {
    extend: 'Ext.data.Store',
	storeId: 'DispatcherStationWP_TNC',
	autoLoad: true,
	stripeRows: true,
	fields: [
		{name:'id', type:'int'},
		{name:'name', type:'string'}
	],
	proxy: {
		limitParam: undefined,
		startParam: undefined,
		paramName: undefined,
		pageParam: undefined,
		type: 'ajax',
		url: '?c=TNC&m=getTransportList',
		reader: {
			type: 'json',
			successProperty: 'success'
		},
		actionMethods: {
			create : 'POST',
			read   : 'POST',
			update : 'POST',
			destroy: 'POST'
		}
	}
});