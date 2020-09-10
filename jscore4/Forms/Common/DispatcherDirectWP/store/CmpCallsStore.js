
Ext.define('common.DispatcherDirectWP.store.CmpCallsStore', {
    extend: 'Ext.data.Store',
	storeId: 'CmpCallsStore',
	model: 'common.DispatcherDirectWP.model.CmpCallCard',
	autoLoad: false,
	stripeRows: true,
//	groupField: 'CmpGroupName_id',
	sorters: [{
        sorterFn: function(o1, o2){
			var datePrm1 = new Date(o1.get('CmpCallCard_prmDate')),
				datePrm2 = new Date(o2.get('CmpCallCard_prmDate')),
				CmpCallCard_Urgency1 = o1.get('CmpCallCard_Urgency'),
				CmpCallCard_Urgency2 = o2.get('CmpCallCard_Urgency'),
				priority1, priority2,
				calcPriority = function(datePrm, CmpCallCard_Urgency){
					var delta = new Date() - datePrm,
						mins = Math.floor(delta/60000),
						updateTimeMinutes = 15,
						result = Math.floor(mins/updateTimeMinutes),
						urgencyVal = CmpCallCard_Urgency - result;
						
					if (urgencyVal>0){
						return urgencyVal
					}
					else return 1
				};				
				
			priority1 = (calcPriority(datePrm1, CmpCallCard_Urgency1));	
			priority2 = (calcPriority(datePrm2, CmpCallCard_Urgency2));			
			
			if (priority1 === priority2) {
                return 0;
            }

            return priority1 < priority2 ? -1 : 1;
        }
    }],

	listeners:{
		load: function(stor, records, successful, eOpts){	
//тест на большое кол-во записей			
//			var i = 0, rec = stor.last();
//			for (i; i<100; i++){
//				stor.add(rec.data);
//			}
		}
	}, 

	proxy: {
		type: 'ajax',
		url: '/?c=CmpCallCard4E&m=loadSMPDispatchDirectWorkPlace',
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