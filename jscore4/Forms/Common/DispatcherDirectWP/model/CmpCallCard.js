/*

 */

Ext.define('common.DispatcherDirectWP.model.CmpCallCard', {
    extend: 'Ext.data.Model',
	idProperty: 'CmpCallCard_id',
	fields: [
	   {
			name: 'CmpCallCard_id',
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
			name: 'Sex_id',
			type: 'int'
		},
		{
			name: 'CmpReason_id',
			type: 'int'
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
			type: 'int'
		},
		{
			name: 'pmUser_insID',
			type: 'int'
		},
		{
			name: 'CmpCallCard_prmDate',
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
			name: 'CmpCallCard_isLocked',
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
			name: 'CmpLpu_Name',
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
			type: 'int'
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
			name: 'CmpCallCard_Comm',
			type: 'string'
		},
		{
			name: 'CmpCallCardStatusType_id',
			type: 'int'
		},{
			name: 'CmpCallPlaceType_id',
			type: 'int'
		}		
	]
});
