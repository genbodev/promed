/**
 * Форма дерева решений
 */
Ext4.define('smp.views.cmp110.edit.Form', {
	extend: 'smp.ux.form.MetaFormPanel',
	alias: 'widget.cmp110.edit.form',
	url: '/?c=Cmp110&m=getForm',
	layout: {
		type: 'form'
	},
	initComponent: function () {
		//Ext4.applyIf(this, {});
		this.callParent(arguments);
	}
});