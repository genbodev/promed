
Ext.define('stores.smp.GeoserviceTransportGroupStore', {
    extend: 'Ext.data.Store',
	autoLoad: false,
	stripeRows: true,
	fields: [
		{name: 'id', type: 'int'},
		{name: 'name', type: 'string'},
		{name: 'visible', type: 'boolean', defaultValue: true}
		//{name: 'visible', type: 'boolean', defaultValue: !(getGlobalOptions().region.nick == 'ufa')}
	],
	sorters: [{
		property: 'name',
        direction: 'ASC'
    }],
	proxy: {
		
		limitParam: undefined,
		startParam: undefined,
		paramName: undefined,
		pageParam: undefined,
		type: 'ajax',
		url: '/?c=GeoserviceTransport&m=getGroupList',
		reader: {
			type: 'json',
			successProperty: 'success',
			root: 'items'
		},
		actionMethods: {
			create: 'POST',
			read: 'POST',
			update: 'POST',
			destroy: 'POST'
		},
		type: 'ajax',
		url: '/?c=GeoserviceTransport&m=getGroupList'
	},
	//Тип геосервиса. Возможные значения: [wialon , tnc]
	_type: '',
	constructor: function() {
		this.callParent(arguments);
		/*
		Ext.data.StoreManager.lookup('stores.smp.GeoserviceTransportStore').defineGeoserviceType(function(type){
			this.getProxy().setExtraParam('geoservice_type',type);
		}.bind(this));
		*/
	}
})