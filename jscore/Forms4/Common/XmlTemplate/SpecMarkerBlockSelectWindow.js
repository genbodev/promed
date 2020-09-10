Ext6.define('common.XmlTemplate.SpecMarkerBlockSelectWindow', {
	extend: 'base.BaseForm',
	alias: 'widget.swXmlTemplateSpecMarkerBlockSelectWindow',
	requires: [
		'common.XmlTemplate.SpecMarkerBlockSelectPanel'
	],
	renderTo: main_center_panel.body.dom,
	autoShow: false,
	cls: 'arm-window-new PolkaWP corner-resizable template-spec-marker-block-select-window',
	userCls: 'template-search',
	title: 'Спецмаркер',
	width: 590,
	height: 340,
	modal: true,
	resizable: true,
	resizeHandles: 'se',

	show: function() {
		var me = this;

		me.callParent(arguments);

		me.selectPanel.setParams(arguments[0]);
		me.selectPanel.queryField.focus();
	},

	initComponent: function() {
		var me = this;

		me.selectPanel = Ext6.create('common.XmlTemplate.SpecMarkerBlockSelectPanel');

		Ext6.apply(me, {
			layout: 'fit',
			items: [
				me.selectPanel
			],
			buttons: [
				'->',
				{
					text: 'Отмена',
					cls: 'buttonCancel',
					margin: 0,
					handler: function() {
						me.hide();
					}
				},{
				text: 'Выбрать',
					margin: '0 19 0 0',
					cls: 'buttonAccept',
					handler: function () {
					me.selectPanel.select()
					}
				}
			]
		});

		me.callParent(arguments);
	}
});