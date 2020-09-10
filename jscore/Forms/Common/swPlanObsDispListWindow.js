/**
* Планы контрольных посещений в рамках диспансерного наблюдения
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      All
* @access       public
* @copyright    Copyright (c) 2017 Swan Ltd.
*/

sw.Promed.swPlanObsDispListWindow = Ext.extend(sw.Promed.BaseForm,
{
	title: 'Планы контрольных посещений в рамках диспансерного наблюдения',
	maximized: true,
	maximizable: true,
	modal: false,
	shim: false,
	plain: true,
	buttonAlign: "right",
	objectName: 'swPlanObsDispListWindow',
	closeAction: 'hide',
	id: 'swPlanObsDispListWindow',
	objectSrc: '/jscore/Forms/Common/swPlanObsDispListWindow.js',
	show: function() {
		sw.Promed.swPlanObsDispListWindow.superclass.show.apply(this, arguments);
		
		var win = this,
			base_form = win.FilterPanel.getForm(),
			grid = win.GridPanel.getGrid(),
			cm = grid.getColumnModel(),
			gridExport = win.PlanObsDispPlanExportGrid.getGrid(),
			cmExport = gridExport.getColumnModel();
		
		win.GridPanel.getGrid().getStore().removeAll();
				
		win.GridPanel.addActions({
			name: 'pod_import', 
			text: 'Импорт',
			tooltip: 'Импорт',
			iconCls: 'archive16',
			disabled: getRegionNick().inlist(['buryatiya','pskov']),
			handler: function() {
				win.importPlanObsDisp();
			}
		});
		
		if(getGlobalOptions().accept_tfoms_answer!=1)
			win.GridPanel.getAction('pod_import').hide();
		else 
			win.GridPanel.getAction('pod_import').show();
		
		win.GridPanel.addActions({
			name: 'pod_export', 
			text: 'Экспорт',
			tooltip: 'Экспорт',
			iconCls: 'database-export16',
			disabled: true,
			handler: function() {
				win.exportPlanObsDisp();
			}
		});
		
		cm.setHidden(cm.findColumnIndex('PlanObsDisp_CountTFOMS'), getGlobalOptions().accept_tfoms_answer!=1);
		cm.setHidden(cm.findColumnIndex('PersonDopDispPlanExport_impDate'), getGlobalOptions().accept_tfoms_answer!=1);
		
		cmExport.setHidden(cmExport.findColumnIndex('PersonDopDispPlanExport_impDate'), getGlobalOptions().accept_tfoms_answer!=1);
		cmExport.setHidden(cmExport.findColumnIndex('PlanObsDispExport_CountErr'), getGlobalOptions().accept_tfoms_answer!=1);
		
		if(getGlobalOptions().accept_tfoms_answer!=1) {//скрыть блок ошибок
			win.ExportErrorPlanGrid.hide();
			win.PlanObsDispPlanExportGrid.setWidth('100%');
		} else {
			win.ExportErrorPlanGrid.show();
			win.PlanObsDispPlanExportGrid.setWidth(800);
		}
		win.BottomPanel.doLayout();
		
		var wdcombo = base_form.findField('TFOMSWorkDirection_id');
		wdcombo.setContainerVisible(getRegionNick()=='ekb');
		win.doLayout();

		if(getRegionNick() == 'ekb') {
			wdcombo.getStore().load({
				callback: function() {
					wdcombo.setValue(wdcombo.getValue());
				}
			});			
		}
		
		if(getRegionNick() == "buryatiya") {
			base_form.findField('OrgSMO_id').getStore().filterBy(
				function(rec){
					return rec.get('KLRgn_id') == getRegionNumber()
						&& (Ext.isEmpty(rec.get('OrgSMO_endDate')) 
							|| Date.parse(rec.get('OrgSMO_endDate')) > Date.now());
				}
			);			
		}
		win.doReset();
		win.doSearch();
	},
	doReset: function() {
		var win = this;
		var form = this.FilterPanel.getForm();
		form.reset();
		form.findField('PlanObsDisp_Year').setValue(getGlobalOptions().date.substr(6, 4));
		form.findField('PlanObsDispExport_expDateRange').setMaxValue(getGlobalOptions().date);
		this.doSearch();
	},
	doSearch: function() {
		var win = this;
		var form = win.FilterPanel.getForm();
		var grid = win.GridPanel.getGrid();
		var params = form.getValues();
		params.limit = win.pageSize;
		params.start = 0;
		win.PlanObsDispPlanExportGrid.removeAll();
		win.ExportErrorPlanGrid.removeAll();
		win.GridPanel.removeAll();
		
		win.GridPanel.loadData({
			globalFilters: params
		});
	},
	importPlanObsDisp: function() {
		var win = this;
		getWnd('swPlanObsDispImportWindow').show({
			callback: function() {
				// обновить грид файлов экспорта
				win.GridPanel.getGrid().getStore().reload();
			}
		});
	},
	exportPlanObsDisp: function() {
		var win = this,
			base_form = this.FilterPanel.getForm(),
			grid = this.GridPanel.getGrid(),
			records = grid.getSelectionModel().getSelections(),
			r_array = [];
		
		var params = {
			PlanObsDisp_id: records[0].get('PlanObsDisp_id'),
			DispCheckPeriod_Year: records[0].get('DispCheckPeriod_Year'),
			DispCheckPeriod_Month: records[0].get('DispCheckPeriod_Month'),
			Lpu_id: getGlobalOptions().lpu_id,
			callback: function() {
				// обновить грид файлов экспорта
				win.GridPanel.getGrid().getStore().reload();
			}
		};
		
		if(getRegionNick().inlist(['ekb','pskov'])) {
			params.PlanObsDisp_Year = base_form.findField('PlanObsDisp_Year').getValue();		
			getWnd('swPlanObsDispExportWindow').show(params);
		} else {
			win.getLoadMask('Выполняется экспорт...').show();
			Ext.Ajax.request({
				params: {PlanObsDisp_id: records[0].get('PlanObsDisp_id'), Lpu_id: getGlobalOptions().lpu_id},
				url: '/?c=PlanObsDisp&m=exportPlanObsDisp',
				callback: function(opt, success, response)  {
					win.getLoadMask().hide();
					if (success) {
						// обновить грид файлов экспорта
						win.GridPanel.getGrid().getStore().reload();
						
						var response_obj = Ext.util.JSON.decode(response.responseText);
						if (response_obj.link) {
							sw.swMsg.alert('Результат', 'Экспорт успешно завершён<br/><a target="_blank" download="" href="' + response_obj.link + '">Скачать и сохранить файл экспорта</a>');
						} else sw.swMsg.alert(langs('Ошибка'), 'При экспорте данных произошла ошибка');
					}
				}
			});
		}
	},
	initComponent: function()
	{
		var win = this;
		win.pageSize = 100;
		var year_store = [];
		for ( var i = 2017; i <= 2099; i++ ) {
			year_store.push([i, String(i)]);
		}
		
		win.FilterPanel = new Ext.form.FormPanel({
			region: 'north',
			frame: true,
			border: false,
			autoHeight: true,
			labelAlign: 'right',
			labelWidth: 150,
			items: [{
				allowBlank: false,
				xtype: 'swbaselocalcombo',
				fieldLabel: langs('Год'),
				triggerAction: 'all',
				hiddenName: 'PlanObsDisp_Year',
				width: 350,
				store: year_store,
				listeners: {
					'change': function(field) {
						win.doSearch();
						
						var base_form = win.FilterPanel.getForm();
						var wdcombo = base_form.findField('TFOMSWorkDirection_id');
						if(getRegionNick()=='ekb') {
							//фильтрация направлений работы: вхождение в период
							wdcombo.getStore().clearFilter();
							
							wdcombo.getStore().filterBy(function(rec){
								return !Ext.isEmpty(field.getValue()) 
									&& (
										(Ext.isEmpty(rec.get('TFOMSWorkDirection_endDT')) || !rec.get('TFOMSWorkDirection_endDT').getFullYear || rec.get('TFOMSWorkDirection_endDT').getFullYear()>=field.getValue() )
										&& 
										(Ext.isEmpty(rec.get('TFOMSWorkDirection_begDT')) || !rec.get('TFOMSWorkDirection_begDT').getFullYear || rec.get('TFOMSWorkDirection_begDT').getFullYear()<=field.getValue() )
									);
								});
							//доступно ли текущее значение направления работы:
							var ind = wdcombo.getStore().findBy(function(rec){return rec.get('TFOMSWorkDirection_id') == wdcombo.getValue()});
							if(ind<0) base_form.findField('TFOMSWorkDirection_id').clearValue();
						}
					}.createDelegate(this)
				}
			}, {
				fieldLabel: 'Дата экспорта',
				name: 'PlanObsDispExport_expDateRange',
				plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
				xtype: 'daterangefield',
				width: 350,
				listeners: {
					blur: function(field) {
						if(!Ext.isEmpty(field.maxValue) && field.getValue2() > field.maxValue)
							field.setValue(field.getValue1().format('d.m.Y')+' - '+field.maxValue.format('d.m.Y'));
					}
				}
			}, {
				hidden: getRegionNick()!='ekb',
				width: 350,
				editable: false,
				hiddenName: 'TFOMSWorkDirection_id',
				fieldLabel: 'Направление работы',
				lastQuery: '',
				typeCode: 'int',
				xtype: 'swbaselocalcombo',
				store: new Ext.data.JsonStore({
					key: 'TFOMSWorkDirection_id',
					autoLoad: false,
					fields: [
						{name:'TFOMSWorkDirection_id',type: 'int'},
						{name:'TFOMSWorkDirection_Name', type: 'string'},
						{name:'TFOMSWorkDirection_Code', type: 'int'},
						{name:'TFOMSWorkDirection_begDT', type: 'date', dateFormat: 'd.m.Y'},
						{name:'TFOMSWorkDirection_endDT', type: 'date', dateFormat: 'd.m.Y'}
					],
					url: '/?c=PlanObsDisp&m=getWorkDirectionSpr'
				}),
				valueField: 'TFOMSWorkDirection_id',
				displayField: 'TFOMSWorkDirection_Name',
				listeners: {
					'change': function() {
						
					}.createDelegate(this)
				},
				tpl: new Ext.XTemplate(
					'<tpl for="."><div class="x-combo-list-item">',
					'<font color="red">{TFOMSWorkDirection_Code}</font>&nbsp;{TFOMSWorkDirection_Name}'+
					'</div></tpl>'
				)
			}, {
				hidden: getRegionNick()!='buryatiya',
				hideLabel: getRegionNick()!='buryatiya',
				width: 350,
				fieldLabel: 'СМО',
				hiddenName: 'OrgSMO_id',
				listWidth: 450,
				lastQuery: '',
				minChars: 1,
				withoutTrigger: true,
				xtype: 'sworgsmocombo'
			}, {
				layout: 'column',
				items: 
				[{
					layout: 'form',
					style: "padding-left: 350px",
					labelWidth: 55,
					items:
					[{
						style: "padding-left: 10px",
						xtype: 'button',
						text: langs('Найти'),
						iconCls: 'search16',
						style: 'margin: 0 0 5px 10px',
						handler: function() {
							win.doSearch();
						}.createDelegate(this)
					}]
				}, {
					layout: 'form',
					labelWidth: 100,
					items:[{
						style: "padding-left: 10px",
						xtype: 'button',
						text: langs('Сброс'),
						iconCls: 'resetsearch16',
						style: 'margin: 0 0 0 10px',
						handler: function() {
							win.doReset();
						}.createDelegate(this)
					}]
				}]
			}]
		});
		
		win.GridPanel = new sw.Promed.ViewFrame({
			focusOnFirstLoad: false, 
			region: 'center',
			dataUrl: '/?c=PlanObsDisp&m=loadPlans',
			toolbar: true,
			useEmptyRecord: false,
			autoLoadData: false,
			pageSize: win.pageSize,
			paging: true,
			root: 'data',
			totalProperty: 'totalCount',
			border: false,
			id: 'PlanObsDispViewFrame',
			editformclassname: 'swPlanObsDispEditWindow',
			selectionModel: 'row', //'multiselect',
			autoExpandColumn: 'autoexpand',
			object: 'PlanObsDisp',
			onRowSelect: function(sm,rowIdx,record) {
				if (win.ExportGridTm) {
					clearTimeout(win.ExportGridTm);
				}
				
				var form = win.FilterPanel.getForm();
				
				win.ExportErrorPlanGrid.removeAll();
				win.PlanObsDispPlanExportGrid.removeAll();
				var records = this.getGrid().getSelectionModel().getSelections();
				var r_array = [];
				
				win.ExportGridTm = setTimeout(function () {
					if (records) {
						for (var i = 0; i < records.length; i++) {
							r_array.push(records[i].get('PlanObsDisp_id'));
						}
						if (!r_array.length) return false;
						var params = {};
						params.limit = win.pageSize;
						params.start = 0;
						params.PlanObsDisp_id = r_array[0];
						//~ params.PlanObsDisp_ids = Ext.util.JSON.encode(r_array);
						params.PlanObsDispExport_expDateRange = form.findField('PlanObsDispExport_expDateRange').getRawValue();
						win.PlanObsDispPlanExportGrid.loadData({
							globalFilters: params
						});
					}
				}, 50);
			},
			onRowDeSelect: function(sm,index,record) {
				this.onRowSelect(sm,index,record);
			},
			onLoadData: function() {

			},
			stringfields:
			[
				{name: 'PlanObsDisp_id', type: 'int', key: true, hidden: true},//группировка по дисп.картам
				{name: 'PlanObsDisp_CreateDate', header: 'Дата создания', width: 150},
				{name: 'DispCheckPeriod_Name', header: 'Отчетный период', width: 150, id:'autoexpand'},
				{name: 'OrgSMO_Nick', header: 'СМО', width: 150, hidden: getRegionNick()!='buryatiya'},
				{name: 'TFOMSWorkDirection_Name', header: 'Направление работы', width: 250, hidden: getRegionNick()!='ekb'},
				{name: 'PlanObsDisp_Count', type: 'int', header: 'Количество', width: 150},
				{name: 'PlanObsDisp_CountTFOMS', type: 'int', header: 'Принято ТФОМС', width: 150},
				{name: 'PersonDopDispPlanExport_impDate', type: 'date', header: 'Дата импорта', width: 150},
				{name: 'accessDelete', type: 'int', hidden: true},
				{name: 'DispCheckPeriod_id', type: 'int', hidden: true},
				{name: 'DispCheckPeriod_Year', type: 'int', hidden: true},
				{name: 'DispCheckPeriod_Month', type: 'int', hidden: true},
				{name: 'TFOMSWorkDirection_id', type: 'int', hidden: true}
			],
			actions:
			[
				{name:'action_add', hidden: false, disabled: false, handler: function() {
					var form = win.FilterPanel.getForm();
					var params = {
						action: 'add',
						year: form.findField('PlanObsDisp_Year').getValue(),
						callback: function () {
							win.GridPanel.getGrid().getStore().reload();
						}
					};
					getWnd('swPlanObsDispEditWindow').show(params);
				}},
				{name:'action_edit', hidden: false, disabled: false, handler: function (){
					var form = win.FilterPanel.getForm();
					var grid = win.GridPanel;
					var sel = grid.getGrid().getSelectionModel().getSelected();
					if(sel) {
						var params = {
							action: 'edit',
							year: form.findField('PlanObsDisp_Year').getValue(),
							DispCheckPeriod_id: sel.get('DispCheckPeriod_id'),
							PlanObsDisp_id: sel.get('PlanObsDisp_id'),
							TFOMSWorkDirection_id: sel.get('TFOMSWorkDirection_id'),
							callback: function () {
								win.GridPanel.getGrid().getStore().reload();
							}
						};
						getWnd('swPlanObsDispEditWindow').show(params);
					}
				}},
				{name:'action_view', hidden: false, disabled: true, handler: function() {
					var form = win.FilterPanel.getForm();
					var grid = win.GridPanel;
					var sel = grid.getGrid().getSelectionModel().getSelected();
					if(sel) {
						var params = {
							action: 'view',
							year: form.findField('PlanObsDisp_Year').getValue(),
							DispCheckPeriod_id: sel.get('DispCheckPeriod_id'),
							PlanObsDisp_id: sel.get('PlanObsDisp_id'),
							TFOMSWorkDirection_id: sel.get('TFOMSWorkDirection_id'),
							callback: function () {
								win.GridPanel.getGrid().getStore().reload();
							}
						};
						getWnd('swPlanObsDispEditWindow').show(params);
					}
				}},
				{name:'action_delete', hidden: false, disabled: true, handler: function () {
					var grid = win.GridPanel.getGrid();
					var records = grid.getSelectionModel().getSelections();
					if (records) {
						sw.swMsg.show({
							buttons: Ext.Msg.YESNO,
							fn: function(buttonId) {
								if ( buttonId == 'yes' ) {
									
									win.getLoadMask('Удаление плана диспансеризации').show();
									Ext.Ajax.request({
										params: {PlanObsDisp_id: records[0].get('PlanObsDisp_id')},
										url: '/?c=PlanObsDisp&m=deletePlan',
										callback: function(opt, success, resp)  {
											if (success) {
												win.getLoadMask().hide();
												grid.getStore().reload();
											}
										}
									});
									
								}
							},
							icon: Ext.Msg.QUESTION,
							msg: 'Удалить выбранные записи?',
							title: langs('Вопрос')
						});
					}
				}},
				{name:'action_refresh', hidden: false, disabled: false},
				{name:'action_print', hidden: true, disabled: false}
			]
		});
		
		this.GridPanel.ViewGridModel.on('selectionchange', function(obj) {
			var actions = this.grid.ownerCt.ownerCt.ViewActions;
			var noselect = obj.getCount()<1;
			actions.action_delete.setDisabled(noselect);
			actions.pod_export.setDisabled(noselect);
			actions.action_edit.setDisabled(noselect);
			actions.action_view.setDisabled(noselect);
			if (!noselect) {
				var deleteAccess = true;
				
				var record = win.GridPanel.getGrid().getSelectionModel().getSelections();
				deleteAccess = record[0].get('accessDelete')==1;

				actions.action_delete.setDisabled(!deleteAccess);
			}
		});

		this.PlanObsDispPlanExportGrid = new sw.Promed.ViewFrame({
			title: 'Файлы экспорта',
			focusOnFirstLoad: false,
			region: 'west',
			split: true,
			dataUrl: '/?c=PlanObsDisp&m=loadPlanObsDispExportList',
			toolbar: true,
			useEmptyRecord: false,
			autoLoadData: false,
			pageSize: win.pageSize,
			paging: true,
			root: 'data',
			totalProperty: 'totalCount',
			border: false,
			autoExpandColumn: 'autoexpand',
			object: 'PlanObsDispExport',
			onRowSelect: function(sm,rowIdx,record) {
				win.ExportErrorPlanGrid.removeAll();
				
				if (win.ExportErrorPlanGrid.isVisible() && record.get('PersonDopDispPlanExport_id')) {
					var params = {};
					params.limit = win.pageSize;
					params.start = 0;
					params.PersonDopDispPlanExport_id = record.get('PersonDopDispPlanExport_id');
					win.ExportErrorPlanGrid.loadData({
						globalFilters: params
					});
				}
			},
			stringfields: [
				{name: 'PersonDopDispPlanExport_id', type: 'int', key: true, hidden: true},
				{name: 'PersonDopDispPlanExport_isUsed', type: 'int', hidden: true},
				{name: 'PersonDopDispPlanExport_DownloadLink', type: 'string',  hidden: true},
				
				{name: 'PersonDopDispPlanExport_FileName', header: 'Имя файла', /*id: 'autoexpand',*/ width: 230, renderer: function(value, cellEl, rec){
					var str = '';
					if (rec.get('PersonDopDispPlanExport_isUsed') == 1) {
						str = 'Файл формируется';
					}
					else if (!Ext.isEmpty(value) && !Ext.isEmpty(rec.get('PersonDopDispPlanExport_DownloadLink'))) {
						str = value+' <a target="_blank" download="" href="/' + rec.get('PersonDopDispPlanExport_DownloadLink') + '">Скачать</a>';
					}

					return str;
				}},
				{name: 'PersonDopDispPlanExport_expDate', header: 'Дата экспорта', type: 'date', width: 100},
				{name: 'DispCheckPeriod_Name', header: 'Отчетный период', width: 150},
				{name: 'OrgSMO_Nick', header: 'СМО', width: 150, hidden: getRegionNick()!='buryatiya'},
				{name: 'PersonDopDispPlanExport_PackNum', header: 'Номер пакета', type: 'int'},
				{name: 'PersonDopDispPlanExport_Count', header: 'Количество записей', width: 120},
				{name: 'PersonDopDispPlanExport_impDate', header: 'Дата импорта', type: 'date', width: 100},
				{name: 'PlanObsDispExport_CountErr', header: 'Ошибки', width: 150}
			],
			actions: [
				{name:'action_add', hidden: true, disabled: true},
				{name:'action_edit', hidden: true, disabled: true},
				{name:'action_view', hidden: true, disabled: true},
				{name:'action_delete', hidden: true, disable: true},
				{name:'action_refresh'},
				{name:'action_print'}
			]
		});

		this.ExportErrorPlanGrid = new sw.Promed.ViewFrame({
			title: 'Ошибки',
			focusOnFirstLoad: false,
			region: 'center',
			dataUrl: '/?c=PlanObsDisp&m=loadExportErrorPlanList',
			toolbar: true,
			useEmptyRecord: false,
			autoLoadData: false,
			pageSize: win.pageSize,
			paging: true,
			root: 'data',
			totalProperty: 'totalCount',
			border: false,
			autoExpandColumn: 'autoexpand',
			object: 'ExportErrorPlanDD',
			onRowSelect: function(sm,rowIdx,record) {

			},
			stringfields: [
				{name: 'record_number', type: 'int', hidden: true },
				{name: 'record_number', header: '№ записи', type: 'int', width: 70},
				{name: 'Person_FIO', header: 'ФИО', type: 'string', width: 300, id: 'autoexpand'},
				{name: 'Error_Code', header: 'Код ошибки', type: 'int', width: 150},
				{name: 'Error_Descr', header: 'Описание ошибки', type: 'string', width: 300}
			],
			actions: [
				{name:'action_add', hidden: true, disabled: false},
				{name:'action_edit', hidden: true, disabled: true},
				{name:'action_view', hidden: true, disabled: true},
				{name:'action_delete', hidden: true, disabled: true},
				{name:'action_refresh'},
				{name:'action_print'}
			]
		});

		this.BottomPanel = new Ext.Panel({
			region: 'south',
			layout: 'border',
			split: true,
			height: 300,
			items: [
				this.PlanObsDispPlanExportGrid,
				this.ExportErrorPlanGrid
			]
		});

		Ext.apply(this,
		{
			layout: 'border',
			items: [
				this.FilterPanel,
				this.GridPanel,
				this.BottomPanel
			],
			buttons: [
				'-',
				{
					text: BTN_FRMHELP,
					iconCls: 'help16',
					handler: function(button, event)
					{
						ShowHelp(this.ownerCt.title);
					}
				}, {
					text      : BTN_FRMCLOSE,
					tabIndex  : -1,
					tooltip   : langs('Закрыть'),
					iconCls   : 'cancel16',
					handler   : function() {
						this.hide();
					}.createDelegate(this)
				}
			],
			defaults:
			{
				bodyStyle: 'background: #DFE8F6;'
			}
		});
		sw.Promed.swPlanObsDispListWindow.superclass.initComponent.apply(this, arguments);
	}
});