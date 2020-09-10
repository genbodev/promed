/**
 * Комбобокс с поводами вызовов СМП
 */
Ext6.define('smp.ux.form.field.ComboCmpReason',{
	extend: 'smp.ux.form.field.ComboDirectory',
	alias: 'widget.comboCmpReason',
	requires: [
		'smp.ux.form.field.ComboDirectory',
		'smp.stores.CmpReason'
	],
	store: Ext6.create('smp.stores.CmpReason'),
	displayField: 'CmpReason_Name',
	valueField: 'CmpReason_id',
	codeField: 'CmpReason_Code',
	queryMode: 'remote'
});