/**
 * Модель поводов вызова
 */
Ext6.define('smp.models.CmpReason', {
	extend: 'Ext6.data.Model',
	idProperty: 'CmpReason_id',
	fields: [
		{
			name: 'CmpReason_id',
			type: 'int'
		},
		{
			name: 'CmpReason_Code',
			type: 'string'
		},
		{
			name: 'CmpReason_Name',
			type: 'string'
		}
	]
});
