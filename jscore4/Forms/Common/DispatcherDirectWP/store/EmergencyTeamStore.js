Ext.define('common.DispatcherDirectWP.store.EmergencyTeamStore', {
    extend: 'Ext.data.Store',
	storeId: 'EmergencyTeamStore',
	model: 'common.DispatcherDirectWP.model.EmergencyTeam',
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
	
	//groupField: 'EmergencyTeamStatus_id',
	
	filters: [
		function(item) {
			var startObj = item.get('EmergencyTeamDuty_DTStart'),
				finishObj = item.get('EmergencyTeamDuty_DTFinish'),
				n = new Date(),
				s = new Date(Ext.Date.parse(startObj, "Y-m-d H:i:s")),
				f = new Date(Ext.Date.parse(finishObj, "Y-m-d H:i:s"));
		
			return ((s<n)&&(n<f));
		}
	],
    proxy: {
        type: 'ajax',
        url: '/?c=EmergencyTeam4E&m=loadEmergencyTeamOperEnv',
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
		}
    },
	initComponent: function() {
		
		
		this.sorters = [
			//сортировка по статусу свободный / занятой
			{
				sorterFn: this.sortByFreeStatus
			}
		];
		
		this.callParent(arguments);
	},
	
	//Метод сортировки по статусу свободен / занят
	
	sortByFreeStatus: function(record1, record2) {
		
		var checkEmergencyTeamIsFree = function(record) {
			return (Ext.Array.contains([0,14], record.get('EmergencyTeamStatus_id'))) ? 1 : 2;
		};
		return (checkEmergencyTeamIsFree(record1) - checkEmergencyTeamIsFree(record2));
		
	},
	
	//Метод сортировки по времени доезда
	
	sortByDuration: function(record1, record2) {
		var duration_diff = (record1.get('EmergencyTeamDuration') || Number.MAX_VALUE) - (record2.get('EmergencyTeamDuration') || Number.MAX_VALUE);
		
		// если время доезда второй бригады больше первой  - возвращаем -1
		// если наоборот - возвращаем 1
		// ( duration_diff || 1) защита от деления на 0
		return ( duration_diff / Math.abs( duration_diff || 1) );
	},
	
	//Метод сортировки по времени простоя
	
	sortByFreetime: function(record1, record2) {
		
		// @todo Если статус не изменялся, значение для EmergencyTeamStatusHistory_insDT
		// возвращается null, подумать над необходимость устанавливать какой-то дефолтный статус
		// при выходе бригад на смену, например, что бригада свободна.
		var rec_time1 = ( record1.get('EmergencyTeamStatusHistory_insDT') ? record1.get('EmergencyTeamStatusHistory_insDT').getTime() : 0 );
		var rec_time2 = ( record2.get('EmergencyTeamStatusHistory_insDT') ? record1.get('EmergencyTeamStatusHistory_insDT').getTime() : 0 );
		var change_status_time_diff = rec_time1 - rec_time2;
		
		// если время простоя второй бригады больше первой  - возвращаем 1
		// если наоборот - возвращаем -1
		// ( change_status_time_diff || 1) защита от деления на 0
		return ( change_status_time_diff / Math.abs( change_status_time_diff || 1) );
	},
	
	//Метод сортировки по приоритету логики предложения бригады на вызов
	sortByProposalLogicPriority: function(record1, record2) {
		
		var proposal_logic_priority_diff = (record1.get('EmergencyTeamProposalLogicPriority') || 99) - (record2.get('EmergencyTeamProposalLogicPriority') || 99);
		
		// если приоритет второй бригады больше первой - возвращаем -1
		// если наоборот - возвращаем 1
		// ( proposal_logic_priority_diff || 1) защита от деления на 0
		return ( proposal_logic_priority_diff / Math.abs( proposal_logic_priority_diff || 1) );	
	},
	
	// Метод возвращает функцию sorterFn для создания сортировщика, учитывая
	// тип сортировки (this.sortType) и флаг учета логики предложения бригад (this.considerProposalLogic)
	
	getCurrentSorterFn: function() {
		
		return function(record1, record2) {
			
			if (!record1 || !record2) {
				return false;
			}
			
				//1. Разделяем на свободные и занятые
			return this.sortByFreeStatus(record1, record2) ||  
				//2. Если установлен флаг considerProposalLogic, разделяем по приоритету правил логики предложения бригад
				(!!this.considerProposalLogic && this.sortByProposalLogicPriority(record1, record2)) || 
				//3.1. Если тип сортировки "по времени доезда", сортируем по времени доезда
				( ( ( this.sortType || this._defaultSortType ) === 'duration' ) && this.sortByDuration(record1, record2) ) ||
				//3.2. Если тип сортировки "по времени простоя", сортируем по временипростоя
				( ( ( this.sortType || this._defaultSortType ) === 'freetime' ) && this.sortByFreetime(record1, record2) );
			
		}.bind(this)
	},
	
	// Метод возвращает конфиг для создания сортировщика, учитывающего
	// тип сортировки (this.sortType) и флаг учета логики предложения бригад (this.considerProposalLogic)
	getCurrentSorterConfig: function() {
		
		return {
			sorterFn: this.getCurrentSorterFn()
		}
		
	}
});