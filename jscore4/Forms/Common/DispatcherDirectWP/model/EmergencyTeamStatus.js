/*

 */

Ext.define('common.DispatcherDirectWP.model.EmergencyTeamStatus', {
    extend: 'Ext.data.Model',
	idProperty: 'EmergencyTeamStatus_id',
	fields: [
		{
			name: 'EmergencyTeamStatus_id',
			type: 'int'
		},
		{
			name: 'EmergencyTeamStatus_Code',
			type: 'int'
		},
		{
			name: 'EmergencyTeamStatus_Name',
			type: 'string'
		},
		// @task https://redmine.swan.perm.ru/issues/112443
		// На случай, если фильтрация по дате понадобится на клиенте
		{
			name: 'EmergencyTeamStatus_begDT',
			type: 'date',
			dateFormat: 'Y-m-d H:i:s'
		},
		{
			name: 'EmergencyTeamStatus_endDT',
			type: 'date',
			dateFormat: 'Y-m-d H:i:s'
		}				
	]
});