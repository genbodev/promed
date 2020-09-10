/**
 * swCureStandartListWindow - Клинические рекомендации
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2017 Swan Ltd.
 */

Ext6.define('common.CureStandart.swCureStandartListWindow', {
	/* свойства */
	alias: 'widget.swCureStandartListWindow',
	addCodeRefresh: Ext.emptyFn,
    autoShow: false,
	closable: true,
	closeToolText: 'Закрыть',
	cls: 'arm-window-new PolkaWP',
	constrain: true,
	extend: 'base.BaseForm',
	findWindow: false,
	layout: 'border',
	maximized: true,
	refId: 'swcurestandarts',
	renderTo: main_center_panel.body.dom,
	resizable: false,
	title: 'Стандарты лечения',
	header: getGlobalOptions().client == 'ext2', //если ext6 - заголовок не нужен
	width: 1000,
	constrain: true,
	extFilters: null,
	node_expanded: 0,
	readOnly: false,
	show: function() {
		this.callParent(arguments);

		var win = this;
		if (isUserGroup('PM')) {
			win.ToolBar.items.items[0].disable();
			win.readOnly = true;
		} else {
			win.ToolBar.items.items[0].enable();
			win.readOnly = false;
		}
	},
	doSearch: function (reset) {
		var win = this;
		var fil = win.FilterPanel.getForm();
		if (reset) {
			fil.reset();
		}
		var querystr = fil.findField('SearchString').value;
		var query = '';
		if(querystr.length>2) {
			query = querystr;
		}
		if(!win.filter.collapsed) {//Расширенный поиск
			var age = 0;
			if(fil.findField('AgeAdult').value && fil.findField('AgeChild').value)
				age = 3;
			else
			if(fil.findField('AgeAdult').value)
				age = 1;
			else
			if(fil.findField('AgeChild').value)
				age = 2;
		}
		var phase = fil.findField('Phase_id').value;
		var stage = fil.findField('Stage_id').value;
		var complication = fil.findField('Complication_id').value;
		var conditions = fil.findField('Conditions').getValue().conditions;

		if(conditions) {
			if(conditions.length) {
				conditions = conditions.join(',');
			} else conditions = conditions.toString();
		} else conditions = '';

		win.node_expanded = 0;
		for(i=0; i<win.TreeStore.count(); i++) {
			if(!win.TreeStore.getAt(i).data.leaf) {
				if(win.TreeStore.getAt(i).data.expanded)
					win.node_expanded = win.TreeStore.getAt(i).data.sid;
			}
		}
		win.TreeStore.getProxy().setExtraParam('node', 'root');
			
		win.TreeStore.getProxy().setExtraParam('query', query);
		win.TreeStore.getProxy().setExtraParam('age', age);
		win.TreeStore.getProxy().setExtraParam('phase', phase);
		win.TreeStore.getProxy().setExtraParam('stage', stage);
		win.TreeStore.getProxy().setExtraParam('complication', complication);
		win.TreeStore.getProxy().setExtraParam('conditions', conditions);

		win.TreeStore.load();
	},
	initComponent: function() {
		var win = this;
		
		//~ if(getGlobalOptions().client != 'ext2') this.addHelpButton = Ext.emptyFn;

		win.FilterPanel = new Ext6.form.FormPanel({
			bodyStyle: 'padding: 5px 5px 0px;',
			listeners: {
				render: function(p){
					// Обновление формы по нажатию Enter
					new Ext6.util.KeyMap({
						target: p.body,
						key: Ext.EventObject.ENTER,
						fn: function(){
							win.doSearch();
						}
					});
				}
			},
			autoScroll: true,
			layout: 'anchor',
			border: false,
			region: 'north',


			items: [{
				border: false,
				//style: 'margin: 5px 30px 5px;',
				layout: 'column',
				items: [{
					name: 'SearchString',
					width: 700,
					xtype: 'textfield',
					clearIcon: true,
					emptyText: 'Поиск по наименованию рекомендации',
					triggers: {
						clear: {
							cls: 'x6-form-clear-trigger',
							handler: function() {
								this.setValue('');
							}
						},
						search: {
							cls: 'x6-form-search-trigger',
							handler: function() {
								win.doSearch();
							}
						}						
					}
				}]
			}, win.filter = new Ext6.form.FieldSet( {
				title: 'Фильтр',
				collapsible: true,
				collapsed: true,
				titleCollapse: true,
				cls: 'cs6',
				items: [{
					xtype: 'container',
					items: [
						{
							border: false,
							width: '100%',
							layout: 'anchor',
							hidden: false,
							items: [{
								layout: 'column',
								border: false,
								//width: '50%',
								items: [{
									xtype: 'container',
									border: false,
									defaults: {
										labelWidth: 150
									},
										
									items: [{
										layout: 'column',
										xtype: 'container',
										border: false,
										items: [{
											xtype: 'fieldcontainer',
											fieldLabel: 'Возрастная категория',
											labelWidth: 150,
											defaultType: 'checkboxfield',
											layout: 'column',
											items: [
												{
													boxLabel: 'Взрослые',
													name: 'AgeAdult',
													inputValue: '1',
													width: 100,
													checked: true
												}, {
													boxLabel: 'Дети',
													name: 'AgeChild',
													inputValue: '2',
													width: 100,
													checked: true
												}]
										}]
									}, {
										fieldLabel: 'Фаза',
										name: 'Phase_id',
										width: 400,
										xtype: 'swCureStandartSpr',
										comboSubject: 'Phase',
									}, {
										fieldLabel: 'Стадия',
										name: 'Stage_id',
										width: 400,
										xtype: 'swCureStandartSpr',
										comboSubject: 'Stage',
									}, {
										fieldLabel: 'Осложнения',
										name: 'Complication_id',
										width: 400,
										xtype: 'swCureStandartSpr',
										comboSubject: 'Complication',
									}]
								}, {
									xtype: 'checkboxgroup',
									listConfig: {
										//minWidth: 300,
										resizable: true
									},
									name: 'Conditions',
									style: 'margin-left: 10px;',
									columns: 1,
									vertical: true,
									fieldLabel: 'Условия оказания',
									labelWidth: 120,
									items: [
										{
											boxLabel: 'амбулаторно-поликлиническая помощь',
											inputValue: 1,
											name: 'conditions'
										}, {
											boxLabel: 'стационарная помощь',
											inputValue: 2,
											name: 'conditions'
										}, {
											boxLabel: 'скорая медицинская помощь',
											inputValue: 4,
											name: 'conditions'
										}, {
											boxLabel: 'дневной стационар',
											inputValue: 5,
											name: 'conditions'
										}
									]
								}
								, {
									xtype: 'container',
									border: false,
									height: 150,
									layout: {
										type: 'vbox',
										align: 'bottom'
									},
									items:[{
										xtype: 'container',
										flex:1
									},
									{
										xtype: 'container',
										layout: {
											type: 'hbox',
											align: 'left'
										},
										items: [{
											style: 'margin: 5px;',
											//bodyPadding: 10,
											text: langs('Найти'),
											cls: 'button-primary',
											width: 100,
											xtype: 'button',
											handler: function () {
												win.doSearch();
											}
										}, {
											style: 'margin: 5px;',
											//bodyPadding: 10,
											text: langs('Сбросить'),
											cls: 'button-secondary',
											width: 100,
											xtype: 'button',
											handler: function () {
												win.doSearch(true);
											}
										}]	
									}]
								}
								]
							}]
						}
					]
				}]
			})]
		});

		win.ToolBar = Ext6.create('Ext6.toolbar.Toolbar', {
			cls: 'topPanelCS',
			border: false,
			items:[{
				xtype: 'button',
				text: langs('Добавить'),
				iconCls: 'icon-add',
				handler: function() {
					getWnd('swCureStandartEditWindow').show({ARMType: this.ARMType, action: 'add'});
				}
			}, {
				xtype: 'button',
				text: langs('Изменить'),
				iconCls: 'icon-edit',// 'panicon-edit-pers-info',
				disabled: true,
				handler: function() {
					var index = win.TreePanel.selection.data.sid;
					getWnd('swCureStandartEditWindow').show({
						ARMType: this.ARMType, action: (win.readOnly ? 'view' : 'edit'), id: index,
						callback: function(data) {
							var record = win.TreePanel.getSelectionModel().getSelected().items[0];
							record.set('code', data.diagcodes);
							record.set('name', data.name);

							record.commit();
						}
					});
				}
			}, {
				xtype: 'button',
				text: langs('Копировать'),
				iconCls: 'icon-copy',//'action_copy',
				disabled: true,
				handler: function() {
					var index = win.TreePanel.selection.data.sid;
					getWnd('swCureStandartEditWindow').show({ARMType: this.ARMType, action: 'copy', id: index });
				}
			}, {
				xtype: 'button',
				text: langs('Обновить'),
				cls: 'toolbar-padding',
				iconCls: 'icon-refresh',//'action_refresh',
				handler: function(){
					win.doSearch(false);
				}
			}, {
				xtype: 'button',
				text: langs('Удалить'),
				iconCls: 'icon-delete', //'action_cancel',
				cls: 'toolbar-padding',
				disabled: true,
				handler: function(){
					var index = win.TreePanel.selection.data.sid;
					Ext.Ajax.request({
						method: 'POST',
						url: '/?c=CureStandart&m=delete',
						params: {
							id: index
						},
						success: function(response, opts) {
							var res = JSON.parse(response.responseText);
							if (res.Error_Msg) {
								Ext6.MessageBox.alert('Ошибка', res.Error_Msg);
								win.saveMask.hide();
								return;
							} else {
								var selection = win.TreePanel.getSelectionModel().getSelection();
								var record = selection[0];
								win.TreePanel.getStore().remove(record);
								Ext6.MessageBox.alert('Сохранено', 'Клиническая рекомендация удалена.');
								//win.doSearch(false);
							}
						},
						failure: function() {
							Ext6.MessageBox.alert('Ошибка', 'При сохранении данных произошла ошибка. Обратитесь к администратору');
						}
					});

				}
			}/*, {
				xtype: 'button',
				text: langs('Печать'),
				iconCls: 'action_print',
				cls: 'toolbar-padding',
				disabled: true,
				handler: function(){

				}
			}*/]
		});

		win.TreeStore = Ext6.create('Ext.data.TreeStore', {
			bodyStyle: 'padding: 30px;',
			border: false,
			idProperty: 'sid',
			fields: [
				{name: 'sid', type: 'int'},
				{name: 'name', type: 'string'},
				{name: 'code', type: 'string'},
				{name: 'leaf', type: 'boolean'}
			],
			root:{
				leaf: false,
				expanded: true
			},
			autoLoad:false,
			proxy: {
				limitParam: undefined,
				startParam: undefined,
				paramName: undefined,
				pageParam: undefined,
				type: 'ajax',
				url: '/?c=CureStandart&m=loadTree',
				reader: {
					type: 'json'
				},
				actionMethods: {
					create : 'POST',
					read   : 'POST',
					update : 'POST',
					destroy: 'POST'
				},
				extraParams: {
					node: 'root',
					age: 0,
					query: '',
					phase: 0,
					stage: 0,
					complication: 0,
					conditions: []
				}
			},
			listeners: {
				'load': function ( store, records, successful, operation, node, eOpts ) {
					win.test = {store:store, records:records, successful:successful, operation:operation, node:node, eOpts:eOpts};
					var n = null;
					if(win.node_expanded) {
						n = win.TreeStore.getById(win.node_expanded);
						win.node_expanded = 0;
						if(n) {
							win.TreeStore.getProxy().setExtraParam('node', n.data.sid);
							win.TreePanel.expandNode(n);
						}
					}
				}
			}
		});

		win.TreePanel = Ext6.create('Ext.tree.Panel', {
			region: 'center',
			cls: 'cs6',
			border: false,
			dockedItems: [ win.ToolBar ],
			store: win.TreeStore,
			rootVisible: false,
			reserveScrollbar: true,
			useArrows: true,
			singleExpand: true,
			width: 600,
			height: 370,
			listeners: {
				'beforecellmousedown': function (tree, td, cellIndex, record, tr, rowIndex, e, eOpts) {
					win.TreeStore.getProxy().setExtraParam('node', record.get('id'));
				},
				'select': function (tree, record, index, eOpts) {
					if (record.get('leaf')) {
						win.ToolBar.items.items[1].enable();
						if (win.readOnly) {
							win.ToolBar.items.items[2].disable();
							win.ToolBar.items.items[4].disable();
						} else {
							win.ToolBar.items.items[2].enable();
							win.ToolBar.items.items[4].enable();
						}
					} else {
						win.ToolBar.items.items[1].disable();
						win.ToolBar.items.items[2].disable();
						win.ToolBar.items.items[4].disable();
					}
				},
				'deselect': function () {
					win.ToolBar.items.items[1].disable();
					win.ToolBar.items.items[2].disable();
					win.ToolBar.items.items[4].disable();
				},
				'celldblclick': function (tree, td, cellIndex, record, tr, rowIndex, e, eOpts) {
					if(record.data.parentId!='root') {
						getWnd('swCureStandartEditWindow').show({
							ARMType: this.ARMType, action: (win.readOnly ? 'view' : 'edit'), id: record.get('sid'),
							callback: function(data) {
								record.set('code', data.diagcodes);
								record.set('name', data.name);

								record.commit();
							}
						});	
					}
				},
			},
			columns: [{
				xtype: 'treecolumn',
				//text: langs(' &nbsp; '),
				dataIndex: 'name',
				flex: 1
			}, {
				//text: langs(' &nbsp; '),
				dataIndex: 'code',
				width: 500
			}]
		});

		Ext6.apply(win, {
			items: [
				win.FilterPanel,
				win.TreePanel
			]
		});

		this.callParent(arguments);
	}
});