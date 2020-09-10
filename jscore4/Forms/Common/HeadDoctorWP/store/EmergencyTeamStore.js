Ext.define('common.HeadDoctorWP.store.EmergencyTeamStore', {
    extend: 'Ext.data.Store',
	storeId: 'HeadDoctorWP_EmergencyTeamStore',
	model: 'common.HeadDoctorWP.model.EmergencyTeam',
	autoLoad: false,
	stripeRows: true,
	groupField: 'LpuBuilding_id',
	sorters: [
		//сортировка по статусу свободный / занятой
		/*{
			sorterFn: function(o1, o2){
				var getRank = function(o){
					var EmergencyTeamStatusFree = o.get('EmergencyTeamStatus_id');
					if (EmergencyTeamStatusFree === 0) {
						return 1;
					} else if (EmergencyTeamStatusFree === 14) {
						return 1;
					} else {
						return 2;
					}
				},
				getRankPriority = function(o){
					var GroupRankPriority = o.get('EmergencyTeamProposalLogicPriority');
					if (GroupRankPriority === 0)					
					{return 99;}
					else {return GroupRankPriority;}
				},
				rank1 = getRank(o1),
				rank2 = getRank(o2);

				if (rank1 === rank2) {
					
					//сортировка по статусу важности(по правилу)
					var prior1 = getRankPriority(o1),
						prior2 = getRankPriority(o2);
				
					if (prior1 === prior2) {
						
						//сортировка по времени прибытия
						var dist1 = o1.get('EmergencyTeamDuration'), 
							dist2 = o2.get('EmergencyTeamDuration');

						if (dist1 === dist2) {
							return 0;
						}
						return dist1 < dist2 ? -1 : 1;
					}					
					return prior1 < prior2 ? -1 : 1;
					
				}
				return rank1 < rank2 ? -1 : 1;
			}
		}*/
	],
    proxy: {
        type: 'ajax',
        url: '/?c=EmergencyTeam4E&m=loadEmergencyTeamOperEnvForSmpUnitsNested',
        reader: {
            type: 'json',
            root: 'data',
            successProperty: 'success'
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