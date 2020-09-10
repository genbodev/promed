/**
 * Окно редактирование дерева решений
 */

Ext6.define('smp.views.decisionTree.FormCopyTree', {
	extend: 'Ext6.window.Window',
	alias: 'widget.decisionTree.formCopyTree',
	title: 'Выбора дерева для копирования',
	width: 500,
	modal: true,
	layout: 'fit',
	initComponent: function(){
		var me = this;

		me.dockedItems = [
			{
				xtype: 'toolbar',
				dock: 'bottom',
				items: [
					{
						xtype: 'button',
						text: 'Выбрать',
						iconCls: 'ok16',
						itemId: 'selectTree',
					}
				]
			}
		];

		var treePanel = Ext6.create('Ext6.form.Panel', {
			layout: {
				type: 'vbox',
				align: 'stretch'
			},
			defaults: {
				margin: '10'
			},
			items: [{
				xtype: 'hidden',
				name: 'XmlTemplateCat_id'
			},{
				allowBlank: false,
				xtype: 'ambulanceTreeLevel',
				name: 'ambulanceTreeLevel',
				fieldLabel: 'Уровень дерева'
			},{
				allowBlank: false,
				xtype: 'getStructuresLevel',
				name: 'XmlTemplateCat_Name12',
				fieldLabel: 'Копировать из'
			}]
		});

		Ext6.applyIf(me, {
			items: [
				treePanel
			]
		});

		this.callParent(arguments);
	}
});