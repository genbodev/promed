Ext.define('sw.BaseForm', {
	extend: 'Ext.form.Panel',
	requires: ['Ux.InputTextMask'],
	alias: 'widget.BaseForm',
	id: 'mainForm',
	layout: {
		type: 'fit'
	},
	cls: 'mainForm'
})