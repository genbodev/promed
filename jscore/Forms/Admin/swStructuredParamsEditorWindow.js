/**
 * swStructuredParamsEditorWindow - форма редактирования структурированных параметров
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright			Copyright (c) 2013 Swan Ltd.
 * @author			Petukhov Ivan (ethereallich@gmail.com)
 * @version			10.02.2014
 */

/*NO PARSE JSON*/

sw.Promed.swStructuredParamsTypeCombo = Ext.extend(sw.Promed.SwBaseLocalCombo, {
	codeField: 'StructuredParamsType_id',
	displayField: 'StructuredParamsType_Name',
	editable: true,
	fieldLabel: langs('Тип параметра'),
	forceSelection: true,
	hiddenName: 'StructuredParamsType_id',
	store: new Ext.db.AdapterStore({
		autoLoad: false,
		dbFile: 'Promed.db',
		fields: [
			{name: 'StructuredParamsType_id', type: 'int'},
			{name: 'StructuredParamsType_Name', type: 'string'},
		],
		key: 'StructuredParamsType_id',
		sortInfo: {
			field: 'StructuredParamsType_id',
			direction: 'ASC'
		},
		tableName: 'StructuredParamsType'
	}),
	tpl: new Ext.XTemplate(
		'<tpl for="."><div class="x-combo-list-item">',
		'<font color="red">{StructuredParamsType_id}</font>&nbsp;{StructuredParamsType_Name}',
		'</div></tpl>'
	),
	valueField: 'StructuredParamsType_id',
	width: 500,
	initComponent: function() {
		sw.Promed.swStructuredParamsTypeCombo.superclass.initComponent.apply(this, arguments);
	}
});
Ext.reg('swstructuredparamstypecombo', sw.Promed.swStructuredParamsTypeCombo);

sw.Promed.swStructuredParamsPrintTypeCombo = Ext.extend(sw.Promed.SwBaseLocalCombo, {
	codeField: 'StructuredParamsPrintType_id',
	displayField: 'StructuredParamsPrintType_Name',
	editable: true,
	fieldLabel: langs('Тип печати параметра'),
	forceSelection: true,
	hiddenName: 'StructuredParamsPrintType_id',
	store: new Ext.db.AdapterStore({
		autoLoad: false,
		dbFile: 'Promed.db',
		fields: [
			{name: 'StructuredParamsPrintType_id', type: 'int'},
			{name: 'StructuredParamsPrintType_Name', type: 'string'},
		],
		key: 'StructuredParamsPrintType_id',
		sortInfo: {
			field: 'StructuredParamsPrintType_id',
			direction: 'ASC'
		},
		tableName: 'StructuredParamsPrintType'
	}),
	tpl: new Ext.XTemplate(
		'<tpl for="."><div class="x-combo-list-item">',
		'<font color="red">{StructuredParamsPrintType_id}</font>&nbsp;{StructuredParamsPrintType_Name}',
		'</div></tpl>'
	),
	valueField: 'StructuredParamsPrintType_id',
	width: 500,
	initComponent: function() {
		sw.Promed.swStructuredParamsPrintTypeCombo.superclass.initComponent.apply(this, arguments);
	}
});
Ext.reg('swstructuredparamsprinttypecombo', sw.Promed.swStructuredParamsPrintTypeCombo);

sw.Promed.swStructuredParamsEditorWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swStructuredParamsEditorWindow',
	width: 800,
	height: 600,
	callback: Ext.emptyFn,
	maximized: true,
	layout: 'border',
	title: WND_SP_EDITOR,
	codeRefresh: true,
	objectName: 'swStructuredParamsEditorWindow',
	objectSrc: '/jscore/Forms/Common/swStructuredParamsEditorWindow.js',

	searchInProgress: false,
	
	/**
	 * Идентификаторы текущего, родительского и начального уровней, нужны при добавлении нового параметра
	 */
	StructuredParams_id: null,
	StructuredParams_pid: null,
	StructuredParams_rid: null,

	doSearch: function(StructuredParams_pid)
	{
		var form = this;
		if (form.searchInProgress == true) {
			return;
		} else {
			form.searchInProgress = true;
		}

		var grid = form.StructuredParamsGrid.getGrid();
		var node = form.TreePanel.getSelectionModel().selNode;

		var params = [];
		
		if (StructuredParams_pid) {
			params.StructuredParams_pid = StructuredParams_pid;
		} else if (node && node.attributes.id) {
			params.StructuredParams_pid = node.attributes.id;
		} else {
			form.searchInProgress = false;
			return;
		}
		if (params.StructuredParams_pid == 'root') {
			params.StructuredParams_pid = null;
		}
		grid.getStore().load({
			params: params,
			callback: function (data) {
				form.searchInProgress = false;
				if (data.length > 0) {
					grid.getView().focusRow(0);
					grid.getSelectionModel().selectFirstRow();
				}
			}
		});
	},

	moveParam: function(pos) {
		var params = {
			StructuredParams_id: this.StructuredParamsGrid.getGrid().getSelectionModel().getSelected().id,
			position: pos,
			position_old: this.StructuredParamsGrid.getGrid().getSelectionModel().getSelected().data.StructuredParams_Order
		}
		
		var grid = this.StructuredParamsGrid.getGrid();
		
		Ext.Ajax.request({
			url: C_STRUCTPARAMEDIT_MOVE,
			params: params,
			failure: function(response, options)
			{
				Ext.Msg.alert(langs('Ошибка'), langs('При перемещении произошла ошибка!'));
			},
			success: function(response, action)
			{
				//grid.getStore().removeAll();
				if (response.responseText)
				{
					var answer = Ext.util.JSON.decode(response.responseText);
					if (!answer.success)
					{
						if (answer.Error_Code && !answer.Error_Msg) //todo: Убрать в ближайшем будущем это условие
						{
							Ext.Msg.alert(langs('Ошибка #')+answer.Error_Code, answer.Error_Message);
						}
						else
							if (!answer.Error_Msg) // если не автоматически выводится
							{
								Ext.Msg.alert(langs('Ошибка'), langs('Перемещение невозможно!'));
							}
					}
					else
					{
						grid.getStore().reload();
						this.reloadCurrentTreeBranch();
					}
				}
				else
				{
					Ext.Msg.alert(langs('Ошибка'), langs('Ошибка при перемещении! Отсутствует ответ сервера.'));
				}
			}.createDelegate(this)
		});
	},
	
	moveParamTree: function(pos, old_pos, id, old_parent, new_parent) {
		var params = {
			StructuredParams_id: id,
			position: pos,
			position_old: old_pos
		}

		if (old_parent != new_parent ) {
			params['pid'] = new_parent.id;
			
		}
		var new_parent_id = new_parent.id;
		var old_parent_id = old_parent.id;
		Ext.Ajax.request({
			url: C_STRUCTPARAMEDIT_MOVE,
			params: params,
			failure: function(response, options)
			{
				Ext.Msg.alert(langs('Ошибка'), langs('При перемещении произошла ошибка!'));
			},
			success: function(response, action)
			{
				//grid.getStore().removeAll();
				if (response.responseText)
				{
					var answer = Ext.util.JSON.decode(response.responseText);
					if (!answer.success)
					{
						if (answer.Error_Code && !answer.Error_Msg) //todo: Убрать в ближайшем будущем это условие
						{
							Ext.Msg.alert(langs('Ошибка #')+answer.Error_Code, answer.Error_Message);
						}
						else
							if (!answer.Error_Msg) // если не автоматически выводится
							{
								Ext.Msg.alert(langs('Ошибка'), langs('Перемещение невозможно!'));
							}
					}
					else
					{
						if (this.StructuredParams_id == new_parent_id || this.StructuredParams_id == old_parent_id) { 
							// если в гриде показывается тот же раздел куда мы перемещаем или откуда мы перемещаем, обновляем его
							this.StructuredParamsGrid.getGrid().getStore().reload();
						}
					}
				}
				else
				{
					Ext.Msg.alert(langs('Ошибка'), langs('Ошибка при перемещении! Отсутствует ответ сервера.'));
				}
			}.createDelegate(this)
		});
	},
	
	show: function()
	{
		if ( !this.StructuredParamsGrid.getAction('action_order_up') ) {
			this.StructuredParamsGrid.addActions({
				name: 'action_order_up',
				iconCls: 'uparrow',
				tooltip: langs('Переместить вверх'),
				text: langs('На позицию выше'),
				handler: function() {
					this.moveParam('-1');
				}.createDelegate(this)
			}, 5);
		}
		
		if ( !this.StructuredParamsGrid.getAction('action_order_down') ) {
			this.StructuredParamsGrid.addActions({
				name: 'action_order_down',
				iconCls: 'downarrow',
				tooltip: langs('Переместить вниз'),
				text: langs('На позицию ниже'),
				handler: function() {
					this.moveParam('+1');
				}.createDelegate(this)
			}, 6);
		}
		
		if ( !this.StructuredParamsGrid.getAction('action_order_begin') ) {
			this.StructuredParamsGrid.addActions({
				name: 'action_order_begin',
				iconCls: 'upuparrow',
				tooltip: langs('Переместить в начало'),
				text: langs('В начало'),
				handler: function() {
					this.moveParam('1');
				}.createDelegate(this)
			}, 7);
		}
		
		if ( !this.StructuredParamsGrid.getAction('action_order_end') ) {
			this.StructuredParamsGrid.addActions({
				name: 'action_order_end',
				iconCls: 'downdownarrow',
				tooltip: langs('Переместить в конец'),
				text: langs('В конец'),
				handler: function() {
					this.moveParam(this.StructuredParamsGrid.getGrid().getStore().getCount());
				}.createDelegate(this)
			}, 8);
		}
		
		sw.Promed.swStructuredParamsEditorWindow.superclass.show.apply(this, arguments);
	},
	
	reloadCurrentTreeBranch: function() {
		var node = this.TreePanel.getSelectionModel().getSelectedNode();
		if (node) {
			if (node.isExpanded())
			{
				node.getOwnerTree().loader.load(node);
			}
			node.expand();
		}
	},

	initComponent: function()
	{
		var form = this;

		form.TreePanel = new Ext.tree.TreePanel({
			contextMenu: new Ext.menu.Menu({
				items: 
				[{
					id: 'structuredparams-refresh',
					text: langs('Перезагрузить'),
					icon: 'img/icons/refresh16.png',
					iconCls : 'x-btn-text'
				}],
				listeners: 
				{
					itemclick: function(item) 
					{
						switch (item.id) 
						{
							case 'structuredparams-refresh':
								var n = item.parentMenu.contextNode;
								if (n.isExpanded())
								{
									n.getOwnerTree().loader.load(n);
								}
								n.expand();
							break;
						}
					}
				}
			}),
			region: 'center',
			id: 'SPEW_StructuredParamsTreePanel',
			autoScroll: true,
			loaded: false,
			border: false,
			rootVisible: true,
			lastSelectedId: 0,
			enableDD:true,
			root: {
				nodeType: 'async',
				text: langs('Разделы'),
				id: 'root',
				expanded: true
			},
			loader: new Ext.tree.TreeLoader({
				listeners:
				{
					load: function(loader, node, response)
					{
						if (node.id == 'root')
						{
							if ((node.getOwnerTree().rootVisible == false) && (node.hasChildNodes() == true))
							{
								var child = node.findChild('object', 'Lpu');
								if (child)
								{
									node.getOwnerTree().fireEvent('click', child);
									child.select();
									child.expand();
								}
							}
							else 
							{
								node.getOwnerTree().fireEvent('click', node);
								node.select();
							}
						}
					},
					beforeload: function (tl, node)
					{

					}
				},
				dataUrl: C_STRUCTPARAMSEDIT_TREE
			}),
			selModel: new Ext.tree.KeyHandleTreeSelectionModel(),
			listeners: {
				'click': function(node)
				{
					this.doSearch(node.attributes.id);
					this.StructuredParams_id = node.attributes.id;
					this.StructuredParams_pid = node.attributes.pid;
					this.StructuredParams_rid = node.attributes.rid;
				}.createDelegate(this),
				'contextmenu': function(node, e) 
				{
					if (!node.isLeaf())
					{
						var c = node.getOwnerTree().contextMenu;
						c.contextNode = node;
						c.showAt(e.getXY());
					}
				},
				'movenode': function(tree, node, old_parent, new_parent, index) {
					if (old_parent == new_parent) { // перемещение внутри одной ветки
						this.moveParamTree(index+1, node.attributes.pos, node.id, old_parent, new_parent);
					} else {
						this.moveParamTree(index+1, node.attributes.pos, node.id, old_parent, new_parent);
					}
					node.attributes.pos = index + 1;
					node.attributes.pid = new_parent.id;
				}.createDelegate(this)
			}
		});

		this.StructuredParamsGrid = new sw.Promed.ViewFrame({
			actions:
			[
				{name:'action_add', 
					handler: function() {
						getWnd('swStructuredParamsEditWindow').show({
							records: null,
							StructuredParams_pid: this.StructuredParams_id,
							StructuredParams_rid: this.StructuredParams_rid,
							callback: function() {
								this.reloadCurrentTreeBranch();
								this.doSearch();
							}.createDelegate(this)
						});
					}.createDelegate(this)
				},
				{name:'action_edit', handler: function() {
						var grid = this.StructuredParamsGrid.getGrid();
						var records = grid.getSelectionModel().getSelections();
						getWnd('swStructuredParamsEditWindow').show({
							StructuredParams_id:records[0].id,
							StructuredParams_pid: this.StructuredParams_id,
							StructuredParams_rid: this.StructuredParams_rid,
							callback: function() {
								this.reloadCurrentTreeBranch();
								this.doSearch();
							}.createDelegate(this)
						});
					}.createDelegate(this)
				},
				{name:'action_view', hidden: true},
				{name:'action_delete', handler: function() {
					var viewframe = this.StructuredParamsGrid;
					var grid = viewframe.getGrid();
					if (!grid)
					{
						Ext.Msg.alert(langs('Ошибка'), langs('Системная ошибка: Ошибка выбора грида!'));
						return false;
					}
					else if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get(viewframe.jsonData['key_id']) ) {
						Ext.Msg.alert(langs('Ошибка'), langs('Не выбрана запись!'));
						return false;
					}
					
					var records = grid.getSelectionModel().getSelections();
					var params = {
						'records[]': []
					};
					for (var i = 0; i < records.length; i++) {
						params['records[]'].push(records[i].id);
					}
					
					sw.swMsg.show(
					{
						icon: Ext.MessageBox.QUESTION,
						msg: langs('Вы действительно желаете удалить параметр? Будут удалены все лежащие ниже параметры.'),
						title: langs('Подтверждение'),
						buttons: Ext.Msg.YESNO,
						fn: function(buttonId, text, obj)
						{
							if ('yes' == buttonId)
							{
								Ext.Ajax.request(
								{
									url: C_STRUCTPARAMEDIT_DEL,
									params: params,
									failure: function(response, options)
									{
										Ext.Msg.alert(langs('Ошибка'), langs('При удалении произошла ошибка!'));
									},
									success: function(response, action)
									{
										//grid.getStore().removeAll();
										if (response.responseText)
										{
											var answer = Ext.util.JSON.decode(response.responseText);
											if (!answer.success)
											{
												if (answer.Error_Code && !answer.Error_Msg) //todo: Убрать в ближайшем будущем это условие
												{
													Ext.Msg.alert(langs('Ошибка #')+answer.Error_Code, answer.Error_Message);
												}
												else
													if (!answer.Error_Msg) // если не автоматически выводится
													{
														Ext.Msg.alert(langs('Ошибка'), langs('Удаление невозможно!'));
													}
											}
											else
											{
												grid.getStore().reload();
												if (viewframe.afterDeleteRecord)
												{
													viewframe.afterDeleteRecord({object:viewframe.object, id:id, answer:answer});
												}
												this.reloadCurrentTreeBranch();
											}
										}
										else
										{
											Ext.Msg.alert(langs('Ошибка'), langs('Ошибка при удалении! Отсутствует ответ сервера.'));
										}
									}.createDelegate(this)
								});
							}
							else
							{
								if (grid.getStore().getCount()>0)
								{
									grid.getView().focusRow(0);
								}
							}
						}.createDelegate(this)
					});
					}.createDelegate(this)
				},
				{name:'action_save', url: C_STRUCTPARAMSINEDIT_SAVE, handler: 
					function(o) {
						this.StructuredParamsGrid.saveRecord(o);
						this.reloadCurrentTreeBranch();
					}.createDelegate(this)
				}
			], 
			id: 'SPEW_StructuredParamsGrid',
			region: 'center',
			dataUrl: C_STRUCTPARAMSEDIT_GRID,
			paging: false,
			autoLoadData: false,
			root: 'data',
			editable: true,
			useEmptyRecord: false,
			saveAtOnce: false,
			stringfields:
			[
				{name: 'StructuredParams_id', type: 'int', header: 'ID', key: true},
				{name: 'StructuredParamsType_id', type: 'int', header: langs('Тип'), hidden: true},
				{name: 'StructuredParamsPrintType_id', type: 'int', header: langs('Тип печати'), hidden: true},
				{name: 'StructuredParams_Name', header: langs('Наименование'), id: 'autoexpand', /*editor: new Ext.form.TextField(),*/ sortable: false},
				{name: 'StructuredParamsType_Name', header: langs('Тип'), type: 'string', width: 200, sortable: false
					/*editor: new sw.Promed.swStructuredParamsTypeCombo({
						allowBlank: false,
						editable: true,
						enableKeyEvents: true,
						fireAfterEditOnEmpty: true,
						lazyRender:true,
						listeners: {
							'keypress': function(field, e) {
								if ( e.getKey() == e.TAB )
									field.fireEvent('blur', field);
							},
							'render': function() {
								// как появился нужно и прогрузиться
								this.getStore().load();
							}
						}
					})*/
					
				},
				{name: 'StructuredParamsPrintType_Name', header: langs('Тип печати'), type: 'string', width: 200, sortable: false
					/*editor: new sw.Promed.swStructuredParamsPrintTypeCombo({
						allowBlank: true,
						editable: true,
						enableKeyEvents: true,
						fireAfterEditOnEmpty: true,
						lazyRender:true,
						listeners: {
							'keypress': function(field, e) {
								if ( e.getKey() == e.TAB )
									field.fireEvent('blur', field);
							},
							'render': function() {
								// как появился нужно и прогрузиться
								this.getStore().load();
							}
						}
					})*/
				},
				{name: 'MedSpecOms_DocumentTypeText', header: langs('Тип документа'), type: 'string', width: 240, /*editor: new Ext.form.TextField(),*/sortable: false},
				//{name: 'StructuredParams_SysNick', header: 'Метка', type: 'string', width: 120, /*editor: new Ext.form.TextField(),*/ sortable: false},
				{name: 'MedSpecOms_Text', header: langs('Специальности'), type: 'string', width: 240, /*editor: new Ext.form.TextField(),*/sortable: false},
				{name: 'MedSpecOms_DiagText', header: langs('Диагноз'), type: 'string', width: 240, /*editor: new Ext.form.TextField(),*/sortable: false},
				{name: 'StructuredParams_Order', header: langs('Порядок'), type: 'int', hidden: true}
			],
			onAfterEdit: function(o) {
				o.grid.stopEditing(true);
				//o.grid.getColumnModel().setEditable(4, false);
				if (o && o.field) {
					if (o.field == 'StructuredParamsType_Name') {
						o.record.set('StructuredParamsType_id', o.value);
						o.record.set('StructuredParamsType_Name', o.rawvalue);
					}
					if (o.field == 'StructuredParamsPrintType_Name') {
						o.record.set('StructuredParamsPrintType_id', o.value);
						o.record.set('StructuredParamsPrintType_Name', o.rawvalue);
					}
				}
				//this.checkAllFieldsNotEmpty();
			}.createDelegate(this),
			sm: new Ext.grid.CheckboxSelectionModel({
				singleSelect: true,
				listeners:
				{
					'rowselect': function(sm, rowIdx, record)
					{
						
						var count = this.StructuredParamsGrid.getGrid().getStore().getCount();
						var rowNum = rowIdx + 1;
						// Alert: Хотя отсутствие второго условия можно заюзать при добавлении записей в гриде, в дальнейшем
						if ((record.data[this.StructuredParamsGrid.jsonData['key_id']]==null) || (record.data[this.StructuredParamsGrid.jsonData['key_id']]==''))
						{
							count = 0;
							rowNum = 0;
						}
						if (!this.StructuredParamsGrid.getGrid().getTopToolbar().hidden)
						{
							this.StructuredParamsGrid.getGrid().getTopToolbar().items.last().el.innerHTML = rowNum+' / '+count;
						}
						// Сохраняем индекс выбранной записи
						this.StructuredParamsGrid.saveSelectedIndex();
						// Дополнительно свои действия на выбор грида
						if (this.StructuredParamsGrid.onRowSelect) //&& (count>0))
						{
							this.StructuredParamsGrid.onRowSelect(sm,rowIdx,record);
						}
						
						this.StructuredParamsGrid.getAction('action_order_up').setDisabled( rowNum == 1 );
						this.StructuredParamsGrid.getAction('action_order_begin').setDisabled( rowNum == 1 );
						this.StructuredParamsGrid.getAction('action_order_down').setDisabled( rowNum == count );
						this.StructuredParamsGrid.getAction('action_order_end').setDisabled( rowNum == count );
					}.createDelegate(this),
					'rowdeselect': function(sm, rowIdx, record)
					{
						if (this.StructuredParamsGrid.onRowDeSelect)
						{
							this.StructuredParamsGrid.onRowDeSelect(sm,rowIdx,record);
						}
					}.createDelegate(this),
					'selectionchange': function(sm)
					{
						if (sm.getCount() != 1) {
							this.StructuredParamsGrid.getAction('action_order_up').disable();
							this.StructuredParamsGrid.getAction('action_order_down').disable();
							this.StructuredParamsGrid.getAction('action_order_begin').disable();
							this.StructuredParamsGrid.getAction('action_order_end').disable();
						}
					}.createDelegate(this)
				},
				handleMouseDown : function(g, rowIndex, e){
					if(e.button !== 0 || this.isLocked()){
						return;
					};
					var view = this.grid.getView();
					if(e.shiftKey && !this.singleSelect && this.last !== false){
						var last = this.last;
						this.selectRange(last, rowIndex, (this.multi||e.ctrlKey));
						this.last = last; // reset the last
						view.focusRow(rowIndex);
					}else{
						var isSelected = this.isSelected(rowIndex);
						if((this.multi||e.ctrlKey) && isSelected){
							this.deselectRow(rowIndex);
						}else if(!isSelected || this.getCount() > 1){
							this.selectRow(rowIndex, (this.multi||e.ctrlKey) || e.shiftKey);
							view.focusRow(rowIndex);
						}
					}
				}
			})
		});

		form.LeftPanel = new Ext.form.FormPanel({
			region: 'west',
			split: true,
			layout: 'border',
			width: 350,
			items: [
				form.TreePanel
			]
		});

		form.CenterPanel = new Ext.form.FormPanel({
			region: 'center',
			layout: 'border',
			items: [
				form.StructuredParamsGrid
			]
		});

		Ext.apply(this, {
			items: [
				form.LeftPanel,
				form.CenterPanel
			],
			buttons: [
			{
				text: '-'
			},
			HelpButton(this, 1),
			{
				handler: function () {
					this.hide();
				}.createDelegate(this),
				iconCls: 'close16',
				id: 'SPEW_CancelButton',
				onTabAction: function () {
				},
				tabIndex: 1,
				text: langs('Закрыть')
			}]
		});

		sw.Promed.swStructuredParamsEditorWindow.superclass.initComponent.apply(this, arguments);
	}
});