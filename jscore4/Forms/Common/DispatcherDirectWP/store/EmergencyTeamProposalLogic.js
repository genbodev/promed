
Ext.define('common.DispatcherDirectWP.store.EmergencyTeamProposalLogic', {
    extend: 'Ext.data.Store',
	storeId: 'EmergencyTeamProposalLogic',
	autoLoad: true,
	stripeRows: true,
	fields: [
	   {
			name: 'EmergencyTeamProposalLogic_id',
			type: 'int'
		},
		{
			name: 'CmpReason_id',
			type: 'int'
		},
		{
			name: 'Sex_id',
			type: 'int'
		},
		{
			name: 'CmpReason_Code',
			type: 'string'
		},
		{
			name: 'Sex_Name',
			type: 'string'
		},
		{
			name: 'EmergencyTeamProposalLogic_AgeFrom',
			type: 'int'
		},
		{
			name: 'EmergencyTeamProposalLogic_AgeTo',
			type: 'int'
		},
		{
			name: 'EmergencyTeamProposalLogic_Sequence',
			type: 'string'
		}
	],

	proxy: {
		type: 'ajax',
		url: '/?c=EmergencyTeam4E&m=getEmergencyTeamProposalLogic',
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