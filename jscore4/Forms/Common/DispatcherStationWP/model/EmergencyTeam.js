/*

 */

Ext.define('common.DispatcherStationWP.model.EmergencyTeam', {
    extend: 'Ext.data.Model',
	idProperty: 'EmergencyTeam_id',
	fields: [
                {
                    name: 'EmergencyTeam_id',
                    type: 'int'
                },
                {
                    name: 'EmergencyTeam_Num',
                    type: 'string'
                },
                {
                    name: 'EmergencyTeamStatus_Color',
                    type: 'string'
                },
                {
                    name: 'EmergencyTeam_CarNum',
                    type: 'int'
                },
                {
                    name: 'EmergencyTeam_CarBrand',
                    type: 'string'
                },
				{
                    name: 'EmergencyTeam_CarModel',
                    type: 'string'
                },
                {
                    name: 'EmergencyTeam_PortRadioNum',
                    type: 'int'
                },
				{
                    name: 'EmergencyTeam_GpsNum',
                    type: 'int'
                },
                {
                    name: 'LpuBuilding_id',
                    type: 'int'
                },
                {
                    name: 'EmergencyTeamSpec_Name',
                    type: 'string'
                },
				{
                    name: 'EmergencyTeamSpec_Code',
                    type: 'string'
                },
				{
					name: 'EmergencyTeamSpec_id',
					type: 'int'
				},
				{
                    name: 'EmergencyTeam_isOnline',
                    type: 'string'
                },
//				{
//                    name: 'EmergencyTeam_isTablet',
//                    type: 'string'
//              },
				{
                    name: 'EmergencyTeamStatus_Name',
                    type: 'string'
                },
                {
                    name: 'EmergencyTeamStatus_Code',
                    type: 'string'
                },
				{
                    name: 'Person_Fin',
                    type: 'string'
                },				
                {
                    name: 'EmergencyTeamStatus_id',
                    type: 'int'
                },
				{
                    name: 'GeoserviceTransport_id',
                    type: 'int'
                },
				{
                    name: 'EmergencyTeamDistance',
                    type: 'int'
                },
				{
                    name: 'EmergencyTeamDuration',
                    type: 'int'
                },
				{
                    name: 'EmergencyTeamDistanceText',
                    type: 'string'
                },
				{
                    name: 'EmergencyTeamDurationText',
                    type: 'string'
                },
				{
					name: 'medPersonCount',
					type: 'int'
				},
				{
					name: 'CmpCallCard_id',
					type: 'int'
				},
				{
					name: 'CmpCallCard_Numv',
					type: 'string'
				},
				{
					name: 'CmpCallCard_Ngod',
					type: 'string'
				},
				{
                    name: 'EmergencyTeamProposalLogicPriority',
                    type: 'int'
                },
				{
                    name: 'GeoserviceTransport_name',
                    type: 'string'
                },
				{
					name: 'groups',
					type: 'auto'
				},
				{
					name: 'EmergencyTeamStatusHistory_insDT',
					type: 'int',
                    sortType: function(val){
                        return parseInt(val)
                    }
				},
				{
                    name: 'EmergencyTeamStatus_FREE',
                    type: 'string'
                },
				{
                    name: 'EmergencyTeamDuty_isNotFact',
                    type: 'int'
                },
                {
                    name: 'LpuBuilding_Name',
                    type: 'string'
                },
				{
                    name: 'alertToStartVigil',
                    type: 'int'
                }, 
				{
                    name: 'alertToEndVigil',
                    type: 'int'
                },
                {
                    name: 'alertAutoStartDutySelection',
                    type: 'string'
                },
                {
                    name: 'alertAutoFinishDutySelection',
                    type: 'string'
                },
				{
					name: 'EmergencyTeamCalcStatus_id',
						convert: function(v, record){
							var val = record.get('EmergencyTeamStatus_id');
							if (val === 0) {
								return 1;
							} else if (val === 14) {
								return 1;
							} else {
								return 2;
							}
					}
				},
                {
                    name: 'HLpu_Nick',
                    type: 'string'
                }
				,
                {
                    name: 'IsSignalEnd',
                    type: 'string'
                },
                {
                    name: 'WorkAccess',
                    type: 'string'
                },
                {
                    name: 'countcallsOnTeam',
                    type: 'int'
                },
                {
                    name: 'Lpu_Nick',
                    type: 'string'
                },
                {
                    name: 'lastCheckinAddress',
                    type: 'string'
                }
            ]
});