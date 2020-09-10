Ext.define('common.DispatcherStationWP.store.EmergencyTeamStore', {
    extend: 'Ext.data.Store',
	storeId: 'DispatcherStationWP_EmergencyTeamStoreDS',
	model: 'common.DispatcherStationWP.model.EmergencyTeam',
	autoLoad: false,
	stripeRows: true,
	setSortType: function(type) {
		if (!Ext.Array.contains(this._sortTypeList, type)) {
			return false;
		}
		this.sortType = type;
	},
	_sortTypeList: ['duration','freetime'], //Список возможных типов сортировки
	sortType: null, //Тип сортировки
	_defaultSortType: 'duration', //Тип сортировки по умолчанию, если тип сортировки (sortType) не установлен
	considerProposalLogic: true, // Флаг учета в сортировке логики предложения бригад на вызов
    proxy: {
        type: 'ajax',
        url: '/?c=EmergencyTeam4E&m=loadEmergencyTeamOperEnvForSmpUnit',
        reader: {
            type: 'json',
            root: 'data',
            successProperty: 'success'
        },
		extraParams:{
			ShowWorkingTeams: 'true'
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
		},
		listeners: {
			exception: function( reader, response, error, eOpts){
				var response_obj = Ext.JSON.decode(response.responseText),
					errorText = (error && error.message) ? error.message : (response_obj && response_obj.Error_Msg) ? response_obj.Error_Msg : null;

				if(errorText) {
					Ext.Msg.alert('Ошибка загрузки данных', errorText, function () {
						Ext.data.StoreManager.lookup('common.DispatcherStationWP.store.EmergencyTeamStore').reload()
					});
				}
			}
		}
    },
	sorters: [
		{
			sorterFn: function(rec1, rec2){
				/*сначала делим на 2 группы выбранные и не выбранные для управления (WorkAccess)
					- для ветки выбранной сортируем по EmergencyTeamDuration, EmergencyTeamStatus_FREE, EmergencyTeamStatus_Code, EmergencyTeam_Num
					- для ветки не выбранной сортируем по LpuBulding_id, EmergencyTeamStatus_FREE, EmergencyTeamStatus_Code, EmergencyTeam_Num
				*/
				if(rec1.get('WorkAccess') != rec2.get('WorkAccess')){
					return rec1.get('WorkAccess') == 'true' ? -1 : 1;
				}else{
					if(rec1.get('WorkAccess') == 'true'){
						if(rec1.get('EmergencyTeamDuration') != rec2.get('EmergencyTeamDuration')){
							return rec1.get('EmergencyTeamDuration') > rec2.get('EmergencyTeamDuration') ? 1 : -1;
						}
					}else{
						if(rec1.get('LpuBuilding_id') != rec2.get('LpuBuilding_id')){
							return rec1.get('LpuBuilding_id') > rec2.get('LpuBuilding_id') ? -1 : 1;
						}
					}

					//общие правила сортировки
					if(rec1.get('EmergencyTeamStatus_FREE') != rec2.get('EmergencyTeamStatus_FREE')){
						return rec1.get('EmergencyTeamStatus_FREE') == 'true' ? -1 : 1;
					}else{
						if(rec1.get('EmergencyTeamStatus_Code') != rec2.get('EmergencyTeamStatus_Code')){
							return rec1.get('EmergencyTeamStatus_Code') > rec2.get('EmergencyTeamStatus_Code') ? -1 : 1;
						}
						else{
							if(rec1.get('EmergencyTeam_Num') != rec2.get('EmergencyTeam_Num')){
								return (rec1.get('EmergencyTeam_Num') > rec2.get('EmergencyTeam_Num')) ? 1 : -1;
							}
							else return 0;
						}
					}
				}
			}
		}
	]
});