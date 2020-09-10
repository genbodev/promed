/**
 * Окно редактирование дерева решений
 */

Ext6.define('smp.views.decisionTree.StructuresTree', {
	extend: 'Ext6.window.Window',
	alias: 'widget.decisionTree.structuresTree',
	title: 'Структура дерева принятия решений',
	maximized: true,
	layout: 'fit',
	dockedItems: [
		{
			xtype: 'toolbar',
			dock: 'top',
			itemId: 'toolbarTreeEdit',
			items: [
				{
					xtype: 'button',
					disabled: true,
					text: 'Добавить дерево',
					itemId: 'createTree'
				},
				{
					xtype: 'button',
					disabled: true,
					text: 'Скопировать дерево',
					itemId: 'updateTree'
				}
			]
		}
	],
	initComponent: function(){
		var me = this,
			store = Ext6.create('Ext6.data.TreeStore', {
				root: {
					leaf: false,
					expanded: true
				},
				listeners:{
					nodeappend:function (cmp,node) {
						if(node.get("issetTree") == 'false'){
							node.addCls('empty-node-tree')
						}
					}
				},
				proxy: {
					type: 'ajax',
					url: '/?c=CmpCallCard&m=getStructuresTree',
					extraParams: {
						adminRegion: isUserGroup('smpAdminRegion')
					},
					reader: {
						type: 'json'
					},
					actionMethods: {create: 'POST', read: 'POST', update: 'POST', destroy: 'POST'},
					limitParam: undefined,
					startParam: undefined,
					paramName: undefined,
					pageParam: undefined
				},

			});

		var treePanel = Ext6.create('Ext6.tree.Panel', {
			itemId: 'structuresTreePanel',
			store: store,
			displayField: 'text',
			rootVisible: false
		});

		Ext6.applyIf(me, {
			items: [
				treePanel
			]
		});

		this.callParent(arguments);
	}
});