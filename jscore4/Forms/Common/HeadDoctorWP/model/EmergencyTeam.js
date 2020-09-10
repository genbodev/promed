Ext.define('common.HeadDoctorWP.model.EmergencyTeam', {
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
                    name: 'LpuHid_Nick',
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
					name: 'EmergencyTeamDuty_id',
					type: 'int'
				},				
				{
                    name: 'EmergencyTeam_isOnline',
                    type: 'string'
                },
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
                    name: 'EmergencyTeamProposalLogicPriority',
                    type: 'int'
                },
				{
                    name: 'GeoserviceTransport_name',
                    type: 'string'
                },
				{
					name: 'medPersonCount',
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
					name: 'CmpCallCard_id',
					type: 'int'
				},
				{
					name: 'isOverTime',
					type: 'int'
				},
                {
					name: 'EmergencyTeamDuty_DTStart',
					type: 'string'
				},				
				{
					name: 'EmergencyTeamBuildingName',
					type: 'string'
				},
				{
					name: 'EmergencyTeamDuty_DTFinish',
					type: 'string'
				},
				{
					name: 'lastChangedStatusTime',
					type: 'int'
				},
				{
					name: 'EmergencyTeamDuty_isNotFact',
					type: 'int'
				},
				{
					name: 'lastCheckinAddress',
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
				}
				
						
				
            ]
});