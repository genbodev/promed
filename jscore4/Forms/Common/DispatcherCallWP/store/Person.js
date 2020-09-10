

Ext.define('common.DispatcherCallWP.store.Person', {
    extend: 'Ext.data.Store',
	storeId: 'Person',
	model: 'common.DispatcherCallWP.model.PersonMod',
	autoLoad: false,
	stripeRows: true,
	proxy: {
		type: 'ajax',
		url: '/?c=Person&m=getPersonSearchGrid',
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
