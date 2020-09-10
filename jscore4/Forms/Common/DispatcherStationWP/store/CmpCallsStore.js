
Ext.define('common.DispatcherStationWP.store.CmpCallsStore', {
    extend: 'Ext.data.Store',
	storeId: 'DispatcherStationWP_CmpCallsStoreDS',
	model: 'common.DispatcherStationWP.model.CmpCallCard',
	autoLoad: false,
	stripeRows: true,
	stringsort: 'urg',
//	groupField: 'CmpGroupName_id',
	//sorters: [
		/*
		{
			direction: 'ASC',
			property: 'CmpGroup_id'
		},
		{
			direction: 'ASC',
			property: 'CmpCallCard_IsExtra'
		},
		{
			direction: 'ASC',
			property: 'CmpCallType_Code'
		},
		{
			direction: 'ASC',
			property: 'CmpCallCard_prmDate',
		},
		{
			sorterFn: function(v1,v2){
				//здоровенный костыль на сортировку,
				//сделан из за ошибки скрытия вызовов при использовании метода store.sort() (refs #113860)
				//переделать когда будет время
				var storeCalls =  Ext.data.StoreManager.lookup('common.DispatcherStationWP.store.CmpCallsStore'),
					curArm = sw.Promed.MedStaffFactByUser.current.ARMType || sw.Promed.MedStaffFactByUser.last.ARMType,
					isNmpArm = curArm.inlist(['dispnmp','dispcallnmp', 'dispdirnmp']);

				if(isNmpArm){
					debugger;

				}else{
					if(storeCalls.stringsort == 'urg'){
						var urg1 = v1.get('CmpCallCard_Urgency'),
							urg2 = v2.get('CmpCallCard_Urgency'),
							group1 = v1.get('CmpGroup_id'),
							group2 = v2.get('CmpGroup_id');

						if ( (group1 < group2)) {
							return -1;
						}
						else if((group1 == group2) && (urg1 > urg2)){
							return 1;
						}else if((group1 == group2) && (urg1 < urg2)){
							return -1;
						}else if((group1 == group2) && (urg1 == urg2)){
							return 0;
						}
						else {
							return 1;
						}
					}else{
						var date1 = new Date(Date.parse(v1.get('CmpCallCard_prmDate'))),
							date2 = new Date(Date.parse(v2.get('CmpCallCard_prmDate'))),
							group1 = v1.get('CmpGroup_id'),
							group2 = v2.get('CmpGroup_id');

						if ( (group1 < group2)) {
							return -1;
						}
						else if((group1 == group2) && (date1 > date2)){
							return 1;
						}else if((group1 == group2) && (date1 < date2)){
							return -1;
						}
						else {
							return 1;
						}
					}
				}
			}
		}
*/
	//],

	proxy: {
		type: 'ajax',
		url: '/?c=CmpCallCard4E&m=loadSMPDispatchStationWorkPlace',
		reader: {
			type: 'json',
			successProperty: 'success',
			root: 'data',
			listeners: {
				exception: function( reader, response, error, eOpts){
					var response_obj = Ext.JSON.decode(response.responseText),
						errorText = (error && error.message) ? error.message : response_obj.Error_Msg;

					if(errorText) {
						Ext.Msg.alert('Ошибка загрузки данных', errorText, function () {
							Ext.data.StoreManager.lookup('common.DispatcherStationWP.store.EmergencyTeamStore').reload()
						});
					}
				}
			}
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