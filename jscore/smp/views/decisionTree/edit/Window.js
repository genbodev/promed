/**
 * Окно редактирование дерева решений
 */

Ext6.define('smp.views.decisionTree.edit.Window', {
	extend: 'Ext6.window.Window',
	alias: 'widget.decisionTree.edit.window',
	title: 'Дерево принятия решений: Редактирование',
	width: 640,
	height: 480,
	layout: 'fit',
	maximized: true,
	required: [
		'smp.view.decisionTree.Tree',
		'smp.view.decisionTree.edit.Form'
	],
	dockedItems: [
		{
			xtype: 'toolbar',
			dock: 'top',
			items: [
				{
					xtype: 'button',
					text: 'Добавить',
					itemId: 'create'
				},
				{
					xtype: 'button',
					text: 'Изменить',
					itemId: 'update'
				},
				{
					xtype: 'button',
					text: 'Удалить',
					itemId: 'delete'
				},
				{
					xtype: 'button',
					text: 'Свернуть все',
					itemId: 'collapse-all'
				},
				{
					xtype: 'button',
					text: 'Развернуть все',
					itemId: 'expand-all'
				}
			]
		},{
			xtype: 'toolbar',
			dock: 'bottom',
			ui: 'footer',
			defaults: {
				minWidth: 200
			},
			buttonAlign: 'left',
			items: [
				/*{
					xtype: 'button',
					text: 'Сохранить изменения',
					itemId: 'save',
					iconCls: 'save16'
				},
				{
					xtype: 'button',
					text: 'Отменить изменения',
					itemId: 'cancel',
					iconCls: 'cancel16'
				},*/
				{
					xtype: 'tbfill'
				},
				{
					text: BTN_FRMHELP,
					iconCls: 'help16',
					handler: function(button, event) {
						ShowHelp(win.title);
					}
				}
			]
		}
	],
	initComponent: function(data){
		var win = this;

		Ext6.applyIf(this, {
			items: [
				{
					xtype: 'panel',
					layout: {
						type: 'hbox',
						align: 'stretch'
					},
					items: [
						{
							xtype: 'decisionTree.tree',
							flex: 5
						},
						{
							xtype: 'decisionTree.edit.form',
							flex: 5,
							disabled: true
						}
					]
				}
			],
		});
		
		this.callParent(arguments);
	}
});