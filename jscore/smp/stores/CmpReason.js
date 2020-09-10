/**
 * Хранилище поводов вызова
 */
Ext6.define('smp.stores.CmpReason', {
	extend: 'Ext6.data.Store',
	alias: 'store.smp.CmpReason',
	storeId: 'CmpReason',
	requires: [
		'smp.models.CmpReason'
	],
	model: 'smp.models.CmpReason',
	//autoLoad: true,	
	proxy: {
		type: 'ajax',
		url: '/?c=MongoDBWork&m=getData',
		extraParams: {
			object: 'CmpReason',
			// Поля перечислены для того чтобы получить их имена в нужном регистре
			CmpReason_id: '',
			CmpReason_Code: '',
			CmpReason_Name: ''
		},
		reader: {
			type: 'json'
		},
		actionMethods: {create: 'POST', read: 'POST', update: 'POST', destroy: 'POST'},
		limitParam: undefined,
		startParam: undefined,
		paramName: undefined,
		pageParam: undefined
	}
});
