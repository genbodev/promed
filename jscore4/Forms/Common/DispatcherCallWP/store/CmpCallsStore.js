Ext.define('common.DispatcherCallWP.store.CmpCallsStore', {
    extend: 'Ext.data.Store',
	storeId: 'DispatcherCallWP_CmpCallsStore',
	model: 'common.DispatcherCallWP.model.CmpCallCard',
	autoLoad: false,
	stripeRows: true,
	sorters: [{
		sorterFn: function(o1, o2){
			var CmpCallCard_prmDate1 = new Date(o1.get('CmpCallCard_prmDate')),
				CmpCallCard_prmDate2 = new Date(o2.get('CmpCallCard_prmDate'))


			return CmpCallCard_prmDate1 > CmpCallCard_prmDate2 ? -1 : 1;
		}
	}],
	proxy: {
		type: 'ajax',
		url: '/?c=CmpCallCard4E&m=loadDispatcherCallsList',
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