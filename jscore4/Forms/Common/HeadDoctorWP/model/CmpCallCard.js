Ext.define('common.HeadDoctorWP.model.CmpCallCard', {
    extend: 'Ext.data.Model',
	idProperty: 'CmpCallCard_id',
	fields: [
		{
			name: 'CmpCallCard_id',
			type: 'int'
		},
		{
			name: 'CmpCloseCard_id',
			type: 'int'
		},
		{
			name: 'CmpCallCard_rid',
			type: 'int'
		},
		{
			name: 'Person_id',
			type: 'int'
		},
		{
			name: 'PersonEvn_id',
			type: 'int'
		},
		{
			name: 'Person_Birthday',
			type: 'date'
		},
		{
			name: 'Sex_id',
			type: 'int'
		},
		{
			name: 'CmpReason_id',
			type: 'int'
		},	
		{
			name: 'CmpReason_Name',
			type: 'string'
		},		
		{
			name: 'Urgency',
			type: 'string'
		},	
		{
			name: 'Server_id',
			type: 'string'
		},
		{
			name: 'Person_Surname',
			type: 'string'
		},
		{
			name: 'Person_Firname',
			type: 'string'
		},
		{
			name: 'Person_Secname',
			type: 'string'
		},
		{
			name: 'Person_Age',
			type: 'string'
		},	
		{
			name: 'personAgeText',
			type: 'string'
		},
		{
			name: 'pmUser_insID',
			type: 'int'
		},
		{
			name: 'CmpCallCard_prmDateStr',
			type: 'string'
		},
		{
			name: 'CmpCallCard_prmDate',
			type: 'date',
			//dateFormat: 'U'
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
			name: 'CmpCallCard_isLocked',
			type: 'string'
		},
		{
			name: 'Person_FIO',
			type: 'string'
		},
		{
			name: 'Person_IsUnknown',
			type: 'int'
		},
		{
			name: 'CmpReason_Code',
			type: 'string'
		},
		{
			name: 'CmpCallType_Name',
			type: 'string'
		},
		{
			name: 'CmpCallType_Code',
			type: 'string'
		},
		{
			name: 'Lpu_hNick',
			type: 'string'
		},
		{
			name: 'CmpDiag_Name',
			type: 'string'
		},
		{
			name: 'StacDiag_Name',
			type: 'string'
		},
		{
			name: 'EmergencyTeam_id',
			type: 'int'
		},		
		{
			name: 'EmergencyTeam_Num',
			type: 'string'
		},
		{
			name: 'EmergencyTeamSpec_id',
			type: 'int'
		},
		{
			name: 'EmergencyTeamSpec_Code',
			type: 'int'
		},
		{
			name: 'CmpCallType_id',
			type: 'int'
		},
		{
			name: 'CmpCallCard_prmDT',
			type: 'string'
		},
		{
			name: 'timezone_type',
			type: 'string'
		},
		{
			name: 'PPD_WaitingTime',
			type: 'string'
		},
		{
			name: 'SendLpu_Nick',
			type: 'string'
		},
		{
			name: 'Adress_Name',
			type: 'string'
		},
		{
			name: 'PPDResult',
			type: 'string'
		},
		{
			name: 'ServeDT',
			type: 'string'
		},
		{
			name: 'PPDUser_Name',
			type: 'string'
		},
		{
			name: 'CmpGroup_id',
			type: 'int'
		},
		{
			name: 'CmpCallCard_Urgency',
			type: 'string'
		},
		{
			name: 'CmpCallCard_BoostTime',
			type: 'date'
		},
		{
			name: 'UnAdress_Name',
			type: 'string'
		},
		{
			name: 'UnAdress_lat',
			type: 'string'
		},
		{
			name: 'UnAdress_lng',
			type: 'string'
		},
		{
			name: 'CmpCallCard_isControlCall',
			type: 'int'
		},
		{
			name: 'LpuBuilding_Name',
			type: 'string'
		},
		{
			name: 'LpuBuilding_id',
			type: 'int'
		},
		{
			name: 'CmpCallCard_CalculatedUrgency',
			type: 'string'
		},
		{
			name: 'CmpSecondReason_id',
			type: 'int'
		},
		{
			name: 'CmpSecondReason_Name',
			type: 'string'
		},
		{
			name: 'CmpCallCardStatusType_id',
			type: 'int'
		},
		{
			name: 'CmpCallCardStatusType_Code',
			type: 'int'
		},
		{
			name: 'CmpCallCardStatusType_Name',
			type: 'string'
		},
		{
			name: 'CmpCallRecord_id',
			type: 'int'
		},
        {
			name: 'timeSMPExpiredReasonCode',
			type: 'int'
		},
        {
			name: 'HeadDoctorObservReason',
			type: 'int'
		},
		{
			name: 'CmpCallPlaceType_id',
			type: 'int'
		},{
			name: 'timeToAlertByMinTimeSMP',
			type: 'int'
		},{
			name: 'timeToAlertByMaxTimeSMP',
			type: 'int'
		},{
			name: 'CmpCallCardAcceptor_Code',
			type: 'string'
		},{
			name: 'DuplicateAndActiveCall_Count',
			type: 'string'
		},
		{
			name: 'CmpCallCard_IsExtraText',
			type: 'string'
		},
		{
			name: 'CmpCallCard_IsExtra',
			type: 'int'
		},
		{
			name: 'CmpCallCardEventType_Name',
			type: 'string'
		},
		{
			name: 'EventWaitDuration',
			type: 'string'
		},
		{
			name: 'timeEventBreak',
			type: 'string'
		},
		{
			name: 'IsCallControll',
			type: 'string'
		},
		{
			name: 'CmpIllegalAct_prmDT',
			type: 'string'
		},
		{
			name: 'CmpIllegalAct_Comment',
			type: 'string'
		},
		{
			name: 'CmpIllegalAct_byPerson',
			type: 'int'
		},
		{
			name: 'hasEventDeny',
			type: 'int'
		}
	]
});
