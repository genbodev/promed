/**
 * swAnalyzerModelWindow - окно справочника "Модели анализаторов"
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package	  Common
 * @access	   public
 * @copyright	Copyright (c) 2009 Swan Ltd.
 * @author	   Alexander Chebukin
 * @version	  06.2012
 * @comment
 */
sw.Promed.swAnalyzerModelWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight:false,
	objectName:'swAnalyzerModelWindow',
	objectSrc:'/jscore/Forms/Admin/swAnalyzerModelWindow.js',
	title:langs('Модели анализаторов'),
	layout:'border',
	id:'AnalyzerModelWindow',
	modal:true,
	shim:false,
	resizable:false,
	maximizable:false,
	maximized:true,
	listeners:{
		hide:function () {
			this.onHide();
		}
	},
	onHide:Ext.emptyFn,
	deleteReagent:function () {
		var win = this;
		var grid = this.findById('AMW_ReagentNormRate').getGrid();
		if (!grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('ReagentNormRate_id')) {
			return false;
		}
		var record = grid.getSelectionModel().getSelected();
		sw.swMsg.show({
			buttons:Ext.Msg.YESNO,
			fn:function (buttonId, text, obj) {
				if (buttonId == 'yes') {
					var loadMask = new Ext.LoadMask(win.getEl(), {msg:langs('Удаление...')});
					loadMask.show();
					Ext.Ajax.request({
						callback:function (options, success, response) {
							loadMask.hide();
							if (success) {
								var response_obj = Ext.util.JSON.decode(response.responseText);
								if (response_obj.success == false) {
									sw.swMsg.alert(langs('Ошибка'), response_obj.Error_Msg ? response_obj.Error_Msg : langs('При удалении произошла ошибка!'));
								}
								else {
									grid.getStore().remove(record);
								}
								if (grid.getStore().getCount() > 0) {
									grid.getView().focusRow(0);
									grid.getSelectionModel().selectFirstRow();
								}
							}
							else {
								sw.swMsg.alert(langs('Ошибка'), langs('При удалении реактива модели анализатора возникли ошибки'));
							}
						},
						params:{
							ReagentNormRate_id:record.get('ReagentNormRate_id')
						},
						url:'/?c=ReagentNormRate&m=delete'
					});
				}
			},
			icon:Ext.MessageBox.QUESTION,
			msg:langs('Вы хотите удалить запись?'),
			title:langs('Подтверждение')
		});
	},
		
	deleteAnalyzerModel:function () {
		var win = this;
		var grid = this.findById('AMW_AnalyzerModel').getGrid();
		if (!grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('AnalyzerModel_id')) {
			return false;
		}
		var record = grid.getSelectionModel().getSelected();
		sw.swMsg.show({
			buttons:Ext.Msg.YESNO,
			fn:function (buttonId, text, obj) {
				if (buttonId == 'yes') {
					var loadMask = new Ext.LoadMask(win.getEl(), {msg:langs('Удаление...')});
					loadMask.show();
					Ext.Ajax.request({
						callback:function (options, success, response) {
							loadMask.hide();
							if (success) {
								var response_obj = Ext.util.JSON.decode(response.responseText);
								if (response_obj.success == false) {
									sw.swMsg.alert(langs('Ошибка'), response_obj.Error_Msg ? response_obj.Error_Msg : langs('При удалении произошла ошибка!'));
								}
								else {
									grid.getStore().remove(record);
								}
								if (grid.getStore().getCount() > 0) {
									grid.getView().focusRow(0);
									grid.getSelectionModel().selectFirstRow();
								}
							}
							else {
								sw.swMsg.alert(langs('Ошибка'), langs('При удалении модели анализатора возникли ошибки'));
							}
						},
						params:{
							AnalyzerModel_id:record.get('AnalyzerModel_id')
						},
						url:'/?c=AnalyzerModel&m=delete'
					});
				}
			},
			icon:Ext.MessageBox.QUESTION,
			msg:langs('Вы хотите удалить запись?'),
			title:langs('Подтверждение')
		});
	},
	deleteAnalyzerTest:function ( flWinType ) {
				var grid = null;
				if ( flWinType == 'withNormRate' ) {
					grid = this.AnalyzerTestGridR.getGrid();
				} else {
					grid = this.AnalyzerTestGrid.getGrid();
				}
				
		var win = this;
		if (!grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('AnalyzerTest_id')) {
			return false;
		}
		var record = grid.getSelectionModel().getSelected();
		sw.swMsg.show({
			buttons:Ext.Msg.YESNO,
			fn:function (buttonId, text, obj) {
				if (buttonId == 'yes') {
					var loadMask = new Ext.LoadMask(win.getEl(), {msg:langs('Удаление...')});
					loadMask.show();
					Ext.Ajax.request({
						callback:function (options, success, response) {
							loadMask.hide();
							if (success) {
								var response_obj = Ext.util.JSON.decode(response.responseText);
								if (response_obj.success == false) {
									sw.swMsg.alert(langs('Ошибка'), response_obj.Error_Msg ? response_obj.Error_Msg : langs('При удалении произошла ошибка!'));
								}
								else {
									grid.getStore().remove(record);
								}
								if (grid.getStore().getCount() > 0) {
									grid.getView().focusRow(0);
									grid.getSelectionModel().selectFirstRow();
								}
							}
							else {
								sw.swMsg.alert(langs('Ошибка'), langs('При удалении теста анализатора возникли ошибки'));
							}
						},
						params:{
							AnalyzerTest_id:record.get('AnalyzerTest_id')
						},
						url:'/?c=AnalyzerTest&m=delete'
					});
				}
			},
			icon:Ext.MessageBox.QUESTION,
			msg:langs('Вы хотите удалить запись?'),
			title:langs('Подтверждение')
		});
	},
	show:function () {
		sw.Promed.swAnalyzerModelWindow.superclass.show.apply(this, arguments);
		this.findById('AMW_AnalyzerModel').loadData({
			globalFilters:{}
		});
	},
	showAnalyzerTestContents: function(data) {
		var win = this;
		
		if ( typeof data != 'object' ) {
			return false;
		}

		var NavigationString = this.NavigationString;

		if ( data.level == 0 ) {
			NavigationString.reset();
			// если услуги сервисов		
			var params = {
				 limit: 100
				,start: 0
				,AnalyzerModel_id: win.AnalyzerTestGrid.AnalyzerModel_id
				,AnalyzerTest_pid: null
			}
			win.AnalyzerTestGrid.removeAll();
			win.AnalyzerTestGrid.loadData({
				url: '/?c=AnalyzerTest&m=loadAnalyzerTestGrid',
				params: params, 
				globalFilters: params
			});
		}
		else {
			this.AnalyzerTestGrid.removeAll();

			this.NavigationString.setLevel(0);

			var params = {
				 limit: 100
				,start: 0
				,AnalyzerModel_id: win.AnalyzerTestGrid.AnalyzerModel_id
				,AnalyzerTest_pid: data.AnalyzerTest_id
			}

			this.AnalyzerTestGrid.loadData({
				url: '/?c=AnalyzerTest&m=loadAnalyzerTestGrid',
				params: params,
				globalFilters: params
			});
		}

		if ( data.levelUp ) {
			NavigationString.addRecord(data);
		}
		else {
			NavigationString.update(data);
		}
	},

	openAnalyzerTestEditWindow: function(action, type, flWinType) {
		var win = this;
				var analyzerTestGrid = null;
				var selectedReagentModel = null;
				var ReagentModel_id = null;
				var ReagentNormRate_Name = null;
				analyzerTestGrid = win.AnalyzerTestGrid;
		var selected_record = analyzerTestGrid.getGrid().getSelectionModel().getSelected();

		if (action == 'edit' && (!selected_record || Ext.isEmpty(selected_record.get('AnalyzerTest_id'))))
		{
			return false;
		}
		
		var sel = win.findById('AMW_AnalyzerModel').getGrid().getSelectionModel().getSelected();
		if (sel) {
			var AnalyzerModel_id = sel.get('AnalyzerModel_id');
			var AnalyzerTest_pid = analyzerTestGrid.getGrid().getStore().baseParams.AnalyzerTest_pid;
			if (!Ext.isEmpty(AnalyzerTest_pid) && type == 1)
			{
				sw.swMsg.alert(langs('Внимание'),langs('Нельзя добавить исследование в состав исследования'));
				return false;
			}
						
			var p = {
				AnalyzerTest_pid: AnalyzerTest_pid,
				AnalyzerModel_id: AnalyzerModel_id,
				AnalyzerTest_isTest: type,
								ReagentModel_id: ReagentModel_id,
								ReagentNormRate_Name: ReagentNormRate_Name,
				action: action,
				callback:function () {
					analyzerTestGrid.loadData();
				}
			};
			
			if (action == 'edit') {
				p.AnalyzerTest_id = selected_record.get('AnalyzerTest_id');
				type = selected_record.get('AnalyzerTest_isTest');
			}
			
			switch(type) {
				case 2:
					getWnd('swAnalyzerTestEditWindow').show(p);
				break;
				default:
					getWnd('swAnalyzerTargetEditWindow').show(p);
				break;
			}
		} else {
			sw.swMsg.alert(langs('Не выбран анализатор'),langs('Пожалуйста, выберите анализатор'));
		}
	},
	initComponent:function () {
		var win = this;

		this.leftPanel = new Ext.Panel({
			region:'west',
			border:false,
			layout:'border',
			style:'padding-right: 5px',
			split: true,
			width: 630,
			layoutConfig:{
				titleCollapse:true,
				animate:true,
				activeOnTop:false
			},
			items:[
				new sw.Promed.ViewFrame({
					actions:[
						{name:'action_add'},
						{name:'action_edit'},
						{name:'action_view', hidden:true},
						{name:'action_delete', handler:this.deleteAnalyzerModel.createDelegate(this) },
						{name:'action_print', hidden:true}
					],
					autoExpandColumn:'autoexpand',
					autoExpandMin:150,
					autoLoadData:false,
					border:true,
					dataUrl:'/?c=AnalyzerModel&m=loadList',
					object: 'AnalyzerModel',
					uniqueId: true,
					editformclassname:'swAnalyzerModelEditWindow',
					id:'AMW_AnalyzerModel',
					layout:'fit',
					region:'center',
					paging:false,
					style:'margin-bottom: 10px',
					stringfields:[
						{name:'AnalyzerModel_id', type:'int', header:'ID', key:true},
						{name:'AnalyzerModel_Name', type:'string', header:langs('Модель'), width:120},
						{name:'AnalyzerClass_Name', type:'string', header:langs('Класс анализатора'), width:120},
						{name:'AnalyzerInteractionType_id', type:'int', hidden:true},
						{name:'AnalyzerInteractionType_Name', type:'string', header:langs('Тип взаимодействия'), width:130},
						{name:'AnalyzerModel_IsScaner', type:'checkbox', header:langs('Наличие сканера'), width:120},
						{name:'AnalyzerWorksheetInteractionType_Name', type:'string', header:langs('Тип взаимодействия с рабочими списками'), width:130},
						{name:'FRMOEquipment_Name', type:'string', header:langs('Тип оборудования'), width:140}
					],
					title:langs('Модели анализаторов'),
					onRowSelect:function () {
											var sel = win.findById('AMW_AnalyzerModel').getGrid().getSelectionModel().getSelected();

											//win.reagentPanel.setActiveTab(1);
											//win.reagentPanel.setActiveTab(0);
											if (sel) {
												//var params = {
												//		 limit: 100
												//		,start: 0
												//		,AnalyzerModel_id: sel.get('AnalyzerModel_id')
												//};
												//win.ReagentModelGrid.AnalyzerModel_id = sel.get('AnalyzerModel_id');
												//win.ReagentModelGrid.loadData({
												//		params: params, 
												//		globalFilters: params
												//});
												//win.NavigationStringR.reset();

												var params = {
														 limit: 100
														,start: 0
														,AnalyzerModel_id: sel.get('AnalyzerModel_id')
														,AnalyzerTest_pid: null
												};
												win.AnalyzerTestGrid.AnalyzerModel_id = sel.get('AnalyzerModel_id');
												win.NavigationString.reset();
												win.AnalyzerTestGrid.loadData({
														params: params, 
														globalFilters: params
												});

											}

					},
					toolbar:true
								})
			]
		});
		
		this.ReagentGrid = new sw.Promed.ViewFrame({
			actions:[
				//{name:'action_add', handler: function() { getWnd('swReagentNormRateEditWindow').show({a:1,b:2}); }},
				{name:'action_add'},
				{name:'action_edit'},
				{name:'action_view', hidden:true},
				{name:'action_delete', handler:this.deleteReagent.createDelegate(this) },
				{name:'action_print', hidden:true}
			],
			onRowSelect:function () {
				/*
				var sel = win.findById('AMW_ReagentTest').getGrid().getSelectionModel().getSelected();
				if (sel && sel.get('ReagentModel_id')!=null) {
					var params = {
						limit: 100
					   ,start: 0
					   ,ReagentModel_id: sel.get('ReagentModel_id')
					   ,AnalyzerTest_pid: null
					};
					win.AnalyzerTestGridR.ReagentModel_id = sel.get('ReagentModel_id');
					win.NavigationStringR.reset();
					win.AnalyzerTestGridR.loadData({
						params: params, 
						globalFilters: params
					})
				} else {
					win.AnalyzerTestGridR.removeAll();
				}
				*/
			},
			autoExpandColumn:'autoexpand',
			autoExpandMin:150,
			autoLoadData:false,
			//border:false,
			dataUrl:'/?c=ReagentNormRate&m=loadReagentNormRateGrid',
			height:250,
			id:'AMW_ReagentNormRate',
			region:'south',//'north',
			object: 'ReagentNormRate',
			uniqueId: true,
			paging: true,
			layout:'fit',
			root: 'data',
			totalProperty: 'totalCount',
			editformclassname:'swReagentNormRateEditWindow',
			style:'margin-bottom: 10px',
			stringfields:[
				{name:'ReagentNormRate_id', type:'int', header:'ID', key:true},
				{name:'AnalyzerModel_id', type:'int', hidden:true, isparams:true},
				{name:'DrugNomen_id', type:'int', hidden:true, isparams:true},
				{name:'DrugNomen_Name', type:'string', header:lang['naimenovanie_reaktiva'], width:120, id: 'autoexpand'}
			],
			title:lang['reaktivyi'],
			toolbar:true,
			disabled: true //#PROMEDWEB-14257
		});

		this.AnalyzerTestGrid = new sw.Promed.ViewFrame({
					actions:[
							{name:'action_add', text: langs('Добавить тест'), handler: function() { win.openAnalyzerTestEditWindow('add', 2); }},
							{name:'action_edit', handler: function() { win.openAnalyzerTestEditWindow('edit', 0); }},
							{name:'action_view', hidden:true},
							{name:'action_delete', handler:this.deleteAnalyzerTest.createDelegate(this) },
							{name:'action_print', hidden:true}
					],
					onDblClick: function(grid, number, object){
							this.onEnter();
					},
					onRowSelect:function () {
						var record = win.AnalyzerTestGrid.getGrid().getSelectionModel().getSelected();

						//деактивировано в рамказ задачи #PROMEDWEB-14257
						/*if (record.get('AnalyzerTest_isTest') == 2) {
							console.log( record.get('AnalyzerTest_Code') );
							console.log( record.get('AnalyzerModel_id') );
						
							var params = {
									 limit: 100
									,start: 0
									,AnalyzerModel_id: record.get('AnalyzerModel_id')
									,UslugaComplex_Code: record.get('AnalyzerTest_Code')
							};
							win.ReagentGrid.AnalyzerModel_id = record.get('AnalyzerModel_id');
							win.ReagentGrid.UslugaComplex_Code = record.get('AnalyzerTest_Code');
							win.ReagentGrid.loadData({
									params: params, 
									globalFilters: params
							});
							win.ReagentGrid.getGrid().enable();
							
						} else {
							win.ReagentGrid.getGrid().disable();
							win.ReagentGrid.getGrid().getStore().removeAll();
						}*/
					},
					onEnter: function() {
							// Прогрузить состав выбранной услуги
							if ( !win.AnalyzerTestGrid.getGrid().getSelectionModel().getSelected() ) {
									return false;
							}

							var record = win.AnalyzerTestGrid.getGrid().getSelectionModel().getSelected();

							if ( !record.get('AnalyzerTest_id') ) {
									return false;
							}

							if (record.get('AnalyzerTest_isTest') == 2) {
									// открыть на редактирование
									win.openAnalyzerTestEditWindow('edit', 0);
							} else {
									win.showAnalyzerTestContents({
											AnalyzerTest_id: record.get('AnalyzerTest_id'),
											AnalyzerTest_Name: record.get('AnalyzerTest_Name'),
											level: win.NavigationString.getLevel() + 1,
											levelUp: true
									});
							}
					},
					autoExpandColumn:'autoexpand',
					autoExpandMin:150,
					autoLoadData:false,
					border:true,
					dataUrl:'/?c=AnalyzerTest&m=loadAnalyzerTestGrid',
					//id: 'AnalyzerTestGrid',
					region:'center',
					object: 'AnalyzerTest',
					uniqueId: true,
					paging: true,
					root: 'data',
					totalProperty: 'totalCount',
					editformclassname:'swAnalyzerTestEditWindow',
					style:'margin-bottom: 10px',
					stringfields:[
							{name:'AnalyzerTest_id', type:'int', header:'ID', key:true},
							{name:'AnalyzerTest_pid', type:'int', hidden:true, isparams:true},
							{name:'AnalyzerModel_id', type:'int', hidden:true, isparams:true},
							{name:'AnalyzerTest_Code', type:'string', header:langs('Код услуги'), width:120},
							{name:'AnalyzerTest_Name', type:'string', header:langs('Наименование услуги'), width:120, id: 'autoexpand'},
							{name:'AnalyzerTest_SysNick', type:'string', header:langs('Мнемоника'), width:120},
							{name:'AnalyzerTestType_id_Name', type:'string', header:langs('Тип теста'), width:120},
							{name:'AnalyzerTestType_id', type:'int', hidden:true},
							{name:'AnalyzerTest_isTest', type:'int', hidden:true},
							{name:'Unit_id', type:'int', hidden:true}
					],
					title:langs('Исследования и тесты'),
					toolbar:true
		});
		
		win.AnalyzerTestGrid.ViewToolbar.on('render', function(vt){
			this.ViewActions['action_addisl'] = new Ext.Action({name:'action_addisl', id: 'id_action_addisl', disabled: false, handler: function() {win.openAnalyzerTestEditWindow('add', 1);}, text: langs('Добавить исследование'), tooltip: langs('Добавить исследование'), iconCls: 'x-btn-text', icon: 'img/icons/add16.png'});
			this.ViewActions['action_upperfolder'] = new Ext.Action({name:'action_upperfolder', id: 'id_action_upperfolder', disabled: false, handler: function() {win.NavigationString.goToUpperLevel();}, text: langs('На уровень выше'), tooltip: langs('На уровень выше'), iconCls: 'x-btn-text', icon: 'img/icons/arrow-previous16.png'});

			vt.insertButton(1, this.ViewActions['action_upperfolder']);
			vt.insertButton(1, this.ViewActions['action_addisl']);

			return true;
		}, win.AnalyzerTestGrid);

		win.AnalyzerTestGrid.getGrid().view = new Ext.grid.GridView(
		{
			getRowClass : function (row, index)
			{
				var cls = '';
				if (row.get('AnalyzerTest_isTest') == 1)
					cls = cls+'x-grid-rowselect ';
				return cls;
			}
		});

		win.NavigationString = new Ext.Panel({
			addRecord: function(data) {
				this.setLevel(data.level);

				// BEGIN произвести поиск по сторе, если уже есть, то не добавлять новую а перейти туда.
				var record;
				this.store.each(function(rec) {
					if ( rec.get('AnalyzerTest_id') == data.AnalyzerTest_id ) {
						record = rec;
					}
				});

				if (record && record.get('AnalyzerTest_id')) {
					this.store.each(function(rec) {
						if ( rec.get('level') > record.get('level') ) {
							this.remove('AnalyzerTestCmp_' + rec.get('AnalyzerTest_id'));
							this.store.remove(rec);
						}
					}, this);

					this.buttonIntoText(record);
					this.lastRecord = record;
					this.doLayout();
					this.syncSize();
					return;
				}
				// END произвести поиск по сторе, если уже есть, то не добавлять новую а перейти туда.

				var record = new Ext.data.Record({
					AnalyzerTest_id: data.AnalyzerTest_id,
					AnalyzerTest_Name: data.AnalyzerTest_Name,
					level: data.level
				});

				// Добавляем новую запись
				this.store.add([ record ]);

				if ( typeof this.lastRecord == 'object' ) {
					// Предыдущий текст заменяем на кнопку (удаляем текстовую, добавляем кнопку)
					this.textIntoButton(this.lastRecord);
				}

				// добавляем новую текстовую
				this.lastRecord = record;

				this.add({
					border: false,
					id: 'AnalyzerTestCmp_' + data.AnalyzerTest_id,
					items: [
						new Ext.form.Label({
							record_id: record.id,
							html : "<img src='img/icons/folder16.png'>&nbsp;" + data.AnalyzerTest_Name
						})
					],
					layout: 'form',
					style: 'padding: 2px;'
				});

				this.doLayout();
				this.syncSize();
			},
			autoHeight: true,
			buttonAlign: 'left',
			buttonIntoText: function(record) {
				if ( !record || typeof record != 'object' ) {
					return false;
				}

				this.remove('AnalyzerTestCmp_' + record.get('AnalyzerTest_id'));
			
				this.add({
					border: false,
					id: 'AnalyzerTestCmp_' + record.get('AnalyzerTest_id'),
					items: [
						new Ext.form.Label({
							record_id: record.id,
							html : "<img src='img/icons/folder16.png'>&nbsp;" + record.get('AnalyzerTest_Name')
						})
					],
					layout: 'form',
					style: 'padding: 2px;'
				});

			},
			currentLevel: 0,
			//frame: true,
			items: [
				//
			],
			lastRecord: null,
			layout: 'column',
			region: 'north',
			getLastRecord: function() {
				var record;
				var level = -1;

				this.store.each(function(rec) {
					if ( rec.get('level') > level ) {
						record = rec;
					}
				});

				return record;
			},
			getLevel: function() {
				return this.currentLevel;
			},
			goToUpperLevel: function() {
				var currentLevel = this.getLevel();

				if ( currentLevel == 0 ) {
					return false;
				}

				var prevLevel = 0;
				var prevRecord = new Ext.data.Record({
					AnalyzerTest_id: this.AnalyzerTestRoot_id,
					AnalyzerTest_Name: this.AnalyzerTestRoot_Name,
					level: prevLevel
				});

				this.store.each(function(rec){
					if ( rec.get('level') > prevLevel && rec.get('level') < currentLevel ) {
						prevLevel = rec.get('level');
						prevRecord = rec;
					}
				});

				win.showAnalyzerTestContents(prevRecord.data);
			},
			reset: function() {
				this.removeAll();
				this.store.removeAll();

				this.lastRecord = null;
				this.setLevel(0);

				this.addRecord({
					AnalyzerTest_id: this.AnalyzerTestRoot_id,
					AnalyzerTest_Name: this.AnalyzerTestRoot_Name,
					level: 0
				});
			},
			setLevel: function(level) {
				this.currentLevel = (Number(level) > 0 ? Number(level) : 0);

				if ( this.getLevel() == 0 ) {
					win.AnalyzerTestGrid.setActionDisabled('action_upperfolder', true);
				}
				else {
					win.AnalyzerTestGrid.setActionDisabled('action_upperfolder', false);
				}

				return this;
			},
			store: new Ext.data.SimpleStore({
				data: [
					//
				],
				fields: [
					{name: 'AnalyzerTest_id', type: 'int'},
					{name: 'AnalyzerTest_Name', type: 'string'},
					{name: 'level', type: 'int'}
				],
				key: 'AnalyzerTest_id'
			}),
			style: 'border: 0; padding: 0px; height: 25px; background: #fff;',
			textIntoButton: function(record) {
				if ( !record || typeof record != 'object' ) {
					return false;
				}

				this.remove('AnalyzerTestCmp_' + record.get('AnalyzerTest_id'));

				this.add({
					layout: 'form',
					id: 'AnalyzerTestCmp_' + record.get('AnalyzerTest_id'),
					style: 'padding: 2px;',
					border: false,
					items: [
						new Ext.Button({
							handler: function(btn, e) {
								var rec = this.store.getById(btn.record_id);

								if ( rec ) {
									win.showAnalyzerTestContents(rec.data);
								}
							},
							iconCls: 'folder16',
							record_id: record.id,
							text: record.get('AnalyzerTest_Name'),
							scope: this
						})
					]
				});				
			},
			update: function(data) {
				this.lastRecord = null;
				
				if ( data.AnalyzerTest_id == 0 ) {
					this.reset();
					win.AnalyzerTestGrid.ViewActions.action_upperfolder.setDisabled(true);
				}
				else {
					this.setLevel(data.level);
					win.AnalyzerTestGrid.ViewActions.action_upperfolder.setDisabled(false);

					this.store.each(function(record) {
						if ( record.get('level') > data.level ) {
							this.remove('AnalyzerTestCmp_' + record.get('AnalyzerTest_id'));
							this.store.remove(record);
							this.doLayout();
							this.syncSize();
						}

						if ( record.get('level') == data.level ) {
							this.buttonIntoText(record);
							this.lastRecord = record;
						}

						return true;
					}, this);
				}
			},
			AnalyzerTestRoot_id: 0,
			AnalyzerTestRoot_Name: langs('Корневая папка')
		});

		this.testPanel = new Ext.Panel({
			region:'center',
			border:false,
			layout:'border',
						//id: 'testPanel',
			height:400,
			layoutConfig:{
				titleCollapse:true,
				animate:true,
				activeOnTop:false
			},
			items:[
				win.NavigationString,
				win.AnalyzerTestGrid
			]
		});
		
		this.reagentPanel = new Ext.TabPanel({
			border: false,
			region: 'center',
			id: 'reagentPanel',
			activeTab: 0,
			autoScroll: true,
			enableTabScroll:true,
			layoutOnTabChange: true,
			deferredRender: false,//чтоб рендились все вкладки при создании таба
			plain:true,
			items:[{
					layout:'border',
					//layout: 'fit',
					title: langs('тест - реактив'),
					items:[
						win.testPanel,
						win.ReagentGrid
					]
				}
			]
		});

		this.mainPanel = new Ext.Panel({
			region:'center',
			border:false,
			layout:'border',
						id: 'rightModelPanel',
			layoutConfig:{
				titleCollapse:true,
				animate:true,
				activeOnTop:false
			},
			items:[
								win.reagentPanel
					]
		});

		Ext.apply(this, {
			layout:'border',
			buttons:[
				{
					text:'-'
				},
				//HelpButton(this, -1),
				{
					iconCls:'cancel16',
					text:BTN_FRMCLOSE,
					handler:function () {
						this.hide();
					}.createDelegate(this)
				}
			],
			items:[
				win.leftPanel,
				win.mainPanel
			]
		});

		sw.Promed.swAnalyzerModelWindow.superclass.initComponent.apply(this, arguments);
	}
});