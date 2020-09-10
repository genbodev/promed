Ext6.define('common.XmlTemplate.SpecMarkerBlockDropdownSelectPanel', {
	extend: 'base.DropdownPanel',
	requires: [
		'common.XmlTemplate.SpecMarkerBlockSelectPanel'
	],
	cls: 'template-spec-marker-block-select-window',

	show: function() {
		var me = this;
		me.callParent(arguments);
		me.panel.setParams(arguments[0], false);
		me.panel.queryField.focus();
	},

	initComponent: function() {
		var me = this;

		me.panel = Ext6.create('common.XmlTemplate.SpecMarkerBlockSelectPanel');

		me.callParent(arguments);
	}
});