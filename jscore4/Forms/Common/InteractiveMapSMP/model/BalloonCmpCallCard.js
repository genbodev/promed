Ext.define('common.InteractiveMapSMP.model.BalloonCmpCallCard', {
	extend: 'Ext.data.Model',
	idProperty: 'CmpCallCard_id',
	fields: [
		{
			name: 'CmpCallCard_id',
			type: 'int'
		},
		{
			name: 'CmpCallCardStatusType_id',
			type: 'int'
		},
		{
			name: 'CmpCallCard_Urgency',
			convert: function(value, record) {
				return value ? value : '-';
			}
		},
		{
			name: 'CmpCallCard_Numv',
			type: 'string'
		},
		{
			name: 'EmergencyTeam_id',
			type: 'int'
		},
		{
			name: 'point',
			type: 'auto'
		},
		{
			name: 'EventWaitDuration',
			type: 'int'
		},
		{
			name: 'DeliveranceTime',
			type: 'string',
			defaultValue: 'Загрузка...'
		},
		{
			name: 'CmpReason_Name',
			type: 'string'
		}
	]
});
