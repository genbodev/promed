/**
 * Окно редактирование дерева решений
 */

Ext4.define('smp.views.cmp110.edit.Window', {
	extend: 'Ext4.window.Window',
	alias: 'widget.cmp110.edit.window',
	title: 'Карта закрытия вызова. Форма 110У',
	width: 640,
	height: 480,
	layout: 'fit',
	maximized: true,
	required: [
		'smp.views.cmp110.edit.Form'
	],
	initComponent: function(){
		Ext4.applyIf(this, {
//			items: [
//				{
//					xtype: 'panel',
//					autoScroll: true,
//					items: [
//						{
//							xtype: 'cmp110.edit.form',
//							flex: 1
//						}
//					]
//				}
//			],
			items: [
				{
					xtype: 'cmp110.edit.form',
					flex: 1
				}
			],
			buttonAlign: 'left',
			buttons: [
				{
					xtype: 'button',
					text: 'Сохранить',
					itemId: 'save',
					iconCls: 'save16'
				},
				{
					xtype: 'button',
					text: 'Отмена',
					itemId: 'cancel',
					iconCls: 'cancel16'
				},
				{
					xtype: 'button',
					text: 'Загрузить',
					itemId: 'load',
				}
			]
		});
		
		this.callParent(arguments);
	}
});