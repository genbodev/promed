

Ext.define('common.DispatcherCallWP.store.Address', {
    extend: 'Ext.data.Store',
	storeId: 'Address',
	model: 'common.DispatcherCallWP.model.AddressMod',
	autoLoad: false,
	stripeRows: true,
	fields: [
		{
			name: 'KLCity_id',
			type: 'int'
		}
	],
	proxy: {
		type: 'ajax',
		url: '/?c=Address4E&m=getAddressFromLpuID',
		reader: {
			type: 'json',
			successProperty: 'success',
			root: 'address'
		},
		actionMethods: {
			create : 'POST',
			read   : 'POST',
			update : 'POST',
			destroy: 'POST'
		}
	}
});
