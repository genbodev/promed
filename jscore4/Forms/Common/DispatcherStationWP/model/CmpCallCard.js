/*

 */

Ext.define('common.DispatcherStationWP.model.CmpCallCard', {
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
			name: 'CmpCloseCard_rid',
			type: 'int'
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
			name: 'CmpCallCard_IsExtra',
			type: 'int'
		},
		{
			name: 'CmpCallCard_IsExtraText',
			type: 'string'
		},
		{
			name: 'Urgency',
			type: 'string'
		},
		{
			name: 'Person_Age',
			type: 'int'
		},
		{
			name: 'Person_Birthday',
			type: 'date'
		},
		{
			name: 'personAgeText',
			type: 'string'
		},
		{
			name: 'CmpCallCard_prmDate',
			type: 'string'
		},
		{
			name: 'CmpCallCard_prmDateStr',
			type: 'string'
		},
		{
			name: 'CmpCallCard_prmDateFormat',
			type: 'date'
		},
		{
			name: 'CmpCallCardStatusType_id',
			type: 'string'
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
			name: 'Person_FIO',
			type: 'string'
		},
		{
			name: 'CmpReason_Name',
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
			name: 'CmpPPDResult_Name',
			type: 'string'
		},{
			name: 'CmpPPDResult_id',
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
			name: 'CmpCallCard_Telf',
			type: 'string'
		},
		{
			name: 'EmergencyTeamSpec_Code',
			type: 'string'
		},
		{
			name: 'EmergencyTeamSpec_Name',
			type: 'string'
		},
		{
			name: 'Adress_Name',
			type: 'string'
		},
		{
			name: 'AstraAdress_Name',
			type: 'string'
		},
		{
			name: 'CmpCallCardStatusType_Code',
			type: 'string'
		},
		{
			name: 'CmpCallCard_Urgency',
			type: 'int'
		},
		{
			name: 'Person_IsUnknown',
			type: 'int'
		},
		{
			name: 'Person_id',
			type: 'int'
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
			name: 'CmpCallCard_CalculatedUrgency',
			type: 'string'
		},
		{
			name: 'lastCallMessageText',
			type: 'string'
		},
		{
			name: 'CmpGroup_id',
			type: 'int'
		},
		{
			name: 'CmpGroupTable_id',
			type: 'int'
		},
		{
			name: 'CmpCallPlaceType_id',
			type: 'int'
		},
		{
			name: 'CmpCallCard_profile',
			type: 'string'
		},
		{
			name: 'countCardByGroup',
			type: 'int'
		},
		{
			name: 'timeEventBreak',
			type: 'string'
		},	
		{
			name: 'CmpCallCard_Comm',
			type: 'string'
		},
		{
			name: 'CmpCallCard_PlanDT',
			type: 'string'
		},
		{
			name: 'CmpCallCard_TimeTper',
			type: 'string'
		},
		{
			name: 'CmpCallCard_DateTper',
			type: 'string'
		},
		{
			name: 'CmpCallCard_FactDT',
			type: 'string'
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
			name: 'EmergencyTeamDelayType_Name',
			type: 'string'
		},
		{
			name: 'isLate',
			type: 'int'
		},
		{
			name: 'isNewCall',
			type: 'int'
		},
		{
			name: 'TransmittedOrAccepted',
			type: 'int'
		},
		{
			name: 'isTimeDefferedCall',
			type: 'int'
		},
		{
			name: 'is112',
			type: 'int'
		},
		{
			name: 'IsSignalBeg',
			type: 'int'
		},
		{
			name: 'CmpCallCard_isControlCall',
			type: 'int'
		},
		{
			name: 'DuplicateAndActiveCall_Count',
			type: 'string'
		},
		{
			name: 'CmpCallCard_IsQuarantineText',
			type: 'string'
		},
		{
			name: 'Duplicate_Count',
			type: 'int'
		},
		{
			name: 'ActiveCall_Count',
			type: 'int'
		},
		{
			name: 'CmpCallCard_defCom',
			type: 'string'
		},
		{
			name: 'CmpCallRecord_id',
			type: 'int'
		},
		{
			name: 'IsSendCall',
			type: 'int'
		},
		{
			name: 'ridNum',
			type: 'int'
		},
		{
			name: 'EmergencyTeamStatus_Code',
			type: 'int'
		},
		{
			name: 'ridEmergencyTeamStatus_Code',
			type: 'int'
		},
		{
			name: 'ridEmergencyTeam_id',
			type: 'int'
		},
		{
			name: 'CmpCallType_clearName',
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
			name: 'Lpu_ppdid',
			type: 'int'
		},
		{
			name: 'CmpCallCard_IsPassSSMP',
			type: 'int'
		},
		{
			name: 'Lpu_smpid',
			type: 'int'
		},
		{
			name: 'MedService_id',
			type: 'int'
		},
		{
			name: 'KLRegion_id',
			type: 'int'
		},
		{
			name: 'KLCity_id',
			type: 'int'
		},
		{
			name: 'KLTown_id',
			type: 'int'
		},
		{
			name: 'KLStreet_id',
			type: 'int'
		},
		{
			name: 'CmpCallCard_Dom',
			type: 'string'
		},
		{
			name: 'CmpCallCard_Kvar',
			type: 'string'
		},
		{
			name: 'CmpCallCard_Korp',
			type: 'string'
		},
		{
			name: 'countcallsOnTeam',
			type: 'int'
		},
		{
			name: 'CmpCallCardAcceptor_Code',
			type: 'string'
		},
		{
			name: 'LpuBuilding_Code',
			type: 'string'
		},
		{
			name: 'LpuBuilding_Nick',
			type: 'string'
		},
		{
			name: 'StreetAndUnformalizedAddressDirectory_id',
			type: 'string'
		},
		{
			name: 'LpuBuilding_id',
			type: 'int'
		},
		{
			name: 'SmpUnitParam_IsDenyCallAnswerDisp',
			type: 'int'
		},
		{
			name: 'LpuBuilding_IsDenyCallAnswerDoc',
			type: 'int'
		},
		{
			name: 'IsNoTrans',
			type: 'int'
		},
		{
			name: 'hasEventDeny',
			type: 'int'
		},
		{
			name: 'SmpUnitType_Code',
			type: 'int'
		}
	]
});