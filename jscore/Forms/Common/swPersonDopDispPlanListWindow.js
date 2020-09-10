/**
* Планы профилактических мероприятий
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      All
* @access       public
* @copyright    Copyright (c) 2017 Swan Ltd.
*/

sw.Promed.swPersonDopDispPlanListWindow = Ext.extend(sw.Promed.BaseForm,
{
	title: 'Планы диспансеризации и профилактических медицинских осмотров',
	maximized: true,
	maximizable: true,
	modal: false,
	shim: false,
	plain: true,
	buttonAlign: "right",
	objectName: 'swPersonDopDispPlanListWindow',
	closeAction: 'hide',
	id: 'swPersonDopDispPlanListWindow',
	objectSrc: '/jscore/Forms/Common/swPersonDopDispPlanListWindow.js',
	deletePersonDopDispPlanExport: function(options) {
		if ( typeof options != 'object' ) {
			options = new Object();
		}

		var win = this;

		var grid = win.PersonDopDispPlanExportGrid.getGrid();
		var selected_record = grid.getSelectionModel().getSelected();

		if (selected_record && selected_record.get('PersonDopDispPlanExport_id')) {
			var params = {
				PersonDopDispPlanExport_id: selected_record.get('PersonDopDispPlanExport_id')
			};

			if (options.ignoreMultiplePlans) {
				params.ignoreMultiplePlans = 1;
			}

			win.getLoadMask('Удаление файла экспорта').show();
			Ext.Ajax.request({
				params: params,
				url: '/?c=PersonDopDispPlan&m=deletePersonDopDispPlanExport',
				callback: function (opt, success, response) {
					win.getLoadMask().hide();
					if (success) {
						var response_obj = Ext.util.JSON.decode(response.responseText);
						if (response_obj.Alert_Msg) {
							sw.swMsg.show({
								buttons: Ext.Msg.YESNO,
								fn: function(buttonId, text, obj) {
									if ( buttonId == 'yes' ) {
										options.ignoreMultiplePlans = true;
										win.deletePersonDopDispPlanExport(options);
									}
								},
								icon: Ext.MessageBox.QUESTION,
								msg: response_obj.Alert_Msg,
								title: lang['vopros']
							});
						} else {
							win.ExportErrorPlanDDGrid.removeAll();
							grid.getStore().reload();
						}
					}
				}
			});
		}
	},
	show: function() {
		sw.Promed.swPersonDopDispPlanListWindow.superclass.show.apply(this, arguments);
		
		var win = this;
		this.GridPanel.getGrid().getStore().removeAll();

		if (getRegionNick() == 'kz') {
			this.GridPanel.setColumnHidden("PersonDopDispPlan_CountReady", true);
			this.GridPanel.setColumnHidden("PersonDopDispPlan_CountError", true);
		} else {
			this.GridPanel.setColumnHidden("PersonDopDispPlan_CountReady", false);
			this.GridPanel.setColumnHidden("PersonDopDispPlan_CountError", false);
		}
		
		if (getRegionNick() == 'vologda') {
			this.setTitle('Планы диспансеризации, диспансерного наблюдения и профилактических медицинских осмотров');
		}

		this.GridPanel.addActions({
			name: 'pddp_export_to_tfoms',
			text: 'Отправить в ТФОМС',
			tooltip: 'Отправить в ТФОМС',
			iconCls: 'database-export16',
			hidden: getRegionNick().inlist(['penza','kz','vologda']),
			disabled: true,
			handler: function() {
				win.exportPersonDopDispPlanToTfoms();
			}
		});
		
		this.GridPanel.addActions({
			name: 'pddp_export', 
			text: 'Экспорт',
			tooltip: 'Экспорт',
			iconCls: 'database-export16',
			disabled: true,
			handler: function() {
				win.exportPersonDopDispPlan();
			}
		});
		
		this.GridPanel.addActions({
			name: 'pddp_import', 
			text: 'Импорт',
			tooltip: 'Импорт',
			iconCls: 'archive16',
			disabled: !getRegionNick().inlist(['perm', 'ekb', 'pskov', 'vologda']),
			handler: function() {
				win.importPersonDopDispPlan();
			}
		});
		
		this.doReset();
		this.doSearch();
	},
	doReset: function() {
		var win = this;
		var form = this.FilterPanel.getForm();
		form.reset();
		form.findField('PersonDopDispPlan_Year').setValue(getGlobalOptions().date.substr(6, 4));
		form.findField('PersonDopDispPlanExport_expDateRange').setMaxValue(getGlobalOptions().date);
		this.doSearch();
	},
	doSearch: function() {
		var form = this.FilterPanel.getForm();
		var grid = this.GridPanel.getGrid();
		var params = form.getValues();
		params.limit = 100;
		params.start = 0;
		this.PersonDopDispPlanExportGrid.removeAll();
		this.ExportErrorPlanDDGrid.removeAll();
		this.GridPanel.removeAll();
		this.GridPanel.loadData({
			globalFilters: params
		});
	},
	importPersonDopDispPlan: function() {
		var win = this;
		getWnd('swPersonDopDispPlanImportWindow').show({
			callback: function() {
				// обновить грид файлов экспорта
				win.GridPanel.getGrid().getStore().reload();
			}
		});
	},
	exportPersonDopDispPlan: function() {
		var win = this;
		var form = this.FilterPanel.getForm();
		var grid = this.GridPanel.getGrid();
		var records = grid.getSelectionModel().getSelections();
		var r_array = [];

		var multiDispClass = false;
		var DispClass_id = null;
		
		if (getRegionNick() == 'vologda') {
			getWnd('swPersonDopDispPlanExportWindow').show({
				PersonDopDispPlan_Year: form.findField('PersonDopDispPlan_Year').getValue(),
				callback: function() {
					// обновить грид файлов экспорта
					win.PersonDopDispPlanExportGrid.getGrid().getStore().reload();
				}
			});
			return false;
		}

		for (var i = 0; i < records.length; i++) {
			if ( Ext.isEmpty(records[i].get('DispClass_id')) ) {
				continue;
			}

			// Для Астрахани доступны все типы диспансеризации
			if ( getRegionNick().inlist(['astra']) ) {
				r_array.push(records[i].get('PersonDopDispPlan_id'));
				multiDispClass = true;
				DispClass_id = records[i].get('DispClass_id');
			}
			// Для Пскова выгрузка идет только по ДВН
			else if ( getRegionNick().inlist(['pskov']) ) {
				DispClass_id = 1;

				if ( records[i].get('DispClass_id') == 1 ) {
					r_array.push(records[i].get('PersonDopDispPlan_id'));
				}
				else {
					multiDispClass = true;
				}
			}
			else {
				if (records[i].get('DispClass_id') == 1) {
					r_array.push(records[i].get('PersonDopDispPlan_id'));
					if (!Ext.isEmpty(DispClass_id) && DispClass_id != 1) {
						if (getRegionNick().inlist(['perm', 'vologda'])) {
							multiDispClass = true;
						} else {
							sw.swMsg.alert(lang['oshibka'], 'Для экспорта должны быть выбраны планы с одним и тем же типом диспансеризации');
							return false;
						}
					}
					DispClass_id = 1;
				}
				// для Перми, Хакасии доступно и для профосмотров взрослого населения
				if (getRegionNick().inlist(['perm', 'khak', 'vologda', 'ufa', 'buryatiya']) && records[i].get('DispClass_id') == 5) {
					r_array.push(records[i].get('PersonDopDispPlan_id'));
					if (!Ext.isEmpty(DispClass_id) && DispClass_id != 5) {
						if (getRegionNick().inlist(['perm', 'vologda'])) {
							multiDispClass = true;
						} else {
							sw.swMsg.alert(lang['oshibka'], 'Для экспорта должны быть выбраны планы с одним и тем же типом диспансеризации');
							return false;
						}
					}
					DispClass_id = 5;
				}
			}
		}
		
		if (!r_array.length) {
			return false;
		}
		
		var params = {
			callback: function() {
				// обновить грид файлов экспорта
				win.PersonDopDispPlanExportGrid.getGrid().getStore().reload();
			}
		};
		if (!multiDispClass) {
			params.DispClass_id = DispClass_id;
		}
		params.PersonDopDispPlan_ids = r_array;
		params.PersonDopDispPlan_Year = form.findField('PersonDopDispPlan_Year').getValue();

		if ( getRegionNick() == 'pskov' && multiDispClass == true ) {
			sw.swMsg.alert(langs('Внимание'), 'Экспорту подлежат планы с типом ДВН, остальные выбранные планы будут проигнорированы', function() {
				getWnd('swPersonDopDispPlanExportWindow').show(params);
			});
		}
		else {
			getWnd('swPersonDopDispPlanExportWindow').show(params);
		}
	},
	exportPersonDopDispPlanToTfoms: function() {
		var win = this;
		var form = this.FilterPanel.getForm();
		var grid = this.GridPanel.getGrid();
		var records = grid.getSelectionModel().getSelections();
		var r_array = [];
		var DispClass_id = records[0]?records[0].get('DispClass_id'):null;

		for (var i = 0; i < records.length; i++) {
			if (records[i].get('DispClass_id') == DispClass_id) {
				r_array.push(records[i].get('PersonDopDispPlan_id'));
			}
		}

		if (!r_array.length) {
			return false;
		}

		var params = {
			callback: function() {
				// обновить грид файлов экспорта
				win.PersonDopDispPlanExportGrid.getGrid().getStore().reload();
			}
		};

		params.DispClass_id = DispClass_id;
		params.PersonDopDispPlan_ids = r_array;
		params.PersonDopDispPlan_Year = form.findField('PersonDopDispPlan_Year').getValue();

		getWnd('swPersonDopDispPlanExportTfomsWindow').show(params);
	},
	initComponent: function()
	{
		var win = this;
		var year_store = [];
		for ( var i = 2017; i <= 2099; i++ ) {
			year_store.push([i, String(i)]);
		}
		
		this.FilterPanel = new Ext.form.FormPanel({
			region: 'north',
			frame: true,
			border: false,
			autoHeight: true,
			labelAlign: 'right',
			labelWidth: 100,
			items: [{
				allowBlank: false,
				xtype: 'swbaselocalcombo',
				fieldLabel: lang['god'],
				triggerAction: 'all',
				hiddenName: 'PersonDopDispPlan_Year',
				width: 350,
				store: year_store,
				listeners: {
					'change': function() {
						
					}.createDelegate(this)
				}
			}, {
				fieldLabel: 'Тип осмотра',
				width: 350,
				listWidth: 450,
				comboSubject: 'DispClass',
				hiddenName: 'DispClass_id',
				lastQuery: '',
				typeCode: 'int',
				xtype: 'swcommonsprcombo',
				listeners: {
					'change': function() {
						
					}.createDelegate(this)
				},
				onLoadStore: function(store) {
					this.lastQuery = '';
					store.clearFilter();
					var allowedDispClassList = [1,3,5,6,7,9,10];
					if (getRegionNick() == 'kareliya') {
						allowedDispClassList.push(2);
						allowedDispClassList.push(12);
					}
					if (getRegionNick() == 'vologda') {
						allowedDispClassList = [1,5,28];
					}
					store.filterBy(function(rec) {
						return (rec.get('DispClass_id').inlist(allowedDispClassList));
					});
				}
			}, {
				fieldLabel: 'Дата экспорта',
				name: 'PersonDopDispPlanExport_expDateRange',
				plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
				xtype: 'daterangefield',
				width: 350
			}, {
				layout: 'column',
				items: 
				[{
					layout: 'form',
					style: "padding-left: 300px",
					labelWidth: 55,
					items:
					[{
						style: "padding-left: 10px",
						xtype: 'button',
						text: lang['nayti'],
						iconCls: 'search16',
						style: 'margin: 0 0 5px 10px',
						handler: function() {
							this.doSearch();
						}.createDelegate(this)
					}]
				}, {
					layout: 'form',
					labelWidth: 100,
					items:[{
						style: "padding-left: 10px",
						xtype: 'button',
						text: lang['sbros'],
						iconCls: 'resetsearch16',
						style: 'margin: 0 0 0 10px',
						handler: function() {
							this.doReset();
						}.createDelegate(this)
					}]
				}]
			}]
		});
		
		this.GridPanel = new sw.Promed.ViewFrame({
			focusOnFirstLoad: false, 
			region: 'center',
			dataUrl: '/?c=PersonDopDispPlan&m=loadList',
			toolbar: true,
			useEmptyRecord: false,
			autoLoadData: false,
			pageSize: 100,
			paging: true,
			root: 'data',
			totalProperty: 'totalCount',
			border: false,
			id: 'PersonDopDispPlanViewFrame',
			editformclassname: 'swPersonDopDispPlanEditWindow',
			selectionModel: 'multiselect',
			autoExpandColumn: 'autoexpand',
			object: 'PersonDopDispPlan',
			onRowSelect: function(sm,rowIdx,record) {
				if (win.ExportGridTm) {
					clearTimeout(win.ExportGridTm);
				}
				
				var form = win.FilterPanel.getForm();
				
				win.ExportErrorPlanDDGrid.removeAll();
				win.PersonDopDispPlanExportGrid.removeAll();
				var records = this.getGrid().getSelectionModel().getSelections();
				var r_array = [];
				
				win.ExportGridTm = setTimeout(function () {
					if (records) {
						for (var i = 0; i < records.length; i++) {
							r_array.push(records[i].get('PersonDopDispPlan_id'));
						}
						if (!r_array.length) return false;
						var params = {};
						params.limit = 100;
						params.start = 0;
						params.PersonDopDispPlan_ids = Ext.util.JSON.encode(r_array);
						params.PersonDopDispPlanExport_expDateRange = form.findField('PersonDopDispPlanExport_expDateRange').getRawValue();
						win.PersonDopDispPlanExportGrid.loadData({
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
				{name: 'PersonDopDispPlan_id', type: 'int', key: true, hidden: true},
				{name: 'DispClass_id', type: 'int', hidden: true},
				{name: 'PersonDopDispPlan_insDT', header: 'Дата создания', width: 150},
				{name: 'DispClass_Name', header: 'Тип осмотра', width: 300, id:'autoexpand'},
				{name: 'DispCheckPeriod_Name', header: 'Период', width: 150},
				{name: 'PersonDopDispPlan_Count', type: 'int', header: 'Количество', width: 150},
				{name: 'PersonDopDispPlan_CountReady', type: 'int', header: 'Готовы к отправке', width: 150},
				{name: 'PersonDopDispPlan_CountError', type: 'int', header: 'Содержат ошибки', width: 150},
				{name: 'PersonDopDispPlan_CountAccepted', type: 'int', header: 'Принято ТФОМС', width: 150},
				{name: 'PersonDopDispPlanExport_impDate', type: 'date', header: 'Дата импорта', width: 150},
				{name: 'deleteAccess', type: 'int', hidden: true},
				{name: 'exportToTfomsAccess', type: 'int', hidden: true}
			],
			actions:
			[
				{name:'action_add', hidden: false, disabled: false, handler: function() {
					var form = win.FilterPanel.getForm();
					var params = {
						action: 'add',
						DispClass_id: form.findField('DispClass_id').getValue(),
						callback: function () {
							win.GridPanel.getGrid().getStore().reload();
						}
					};
					getWnd('swPersonDopDispPlanEditWindow').show(params);
				}},
				{name:'action_edit', hidden: false, disabled: true, handler: function (){
					var grid = win.GridPanel;
					var sel = grid.getGrid().getSelectionModel().getSelected();
					if (sel) {
						getWnd(grid.editformclassname).show({action: 'edit', PersonDopDispPlan_id: sel.get('PersonDopDispPlan_id'), callback:
							function (){
								grid.getGrid().getStore().reload();
							}
						});
					}
				}},
				{name:'action_view', hidden: false, disabled: true},
				{name:'action_delete', hidden: false, disabled: true, handler: function () {
					var grid = win.GridPanel.getGrid();
					var records = grid.getSelectionModel().getSelections();
					if (records) {
						sw.swMsg.show({
							buttons: Ext.Msg.YESNO,
							fn: function(buttonId) {
								if ( buttonId == 'yes' ) {
									for (var i = 0; i < records.length; i++) {
										win.getLoadMask('Удаление плана диспансеризации').show();
										Ext.Ajax.request({
											params: {PersonDopDispPlan_id: records[i].get('PersonDopDispPlan_id')},
											url: '/?c=PersonDopDispPlan&m=delete',
											callback: function(opt, success, resp)  {
												if (success) {
													win.getLoadMask().hide();
													grid.getStore().reload();
												}
											}
										});
									}
								}
							},
							icon: Ext.Msg.QUESTION,
							msg: 'Удалить выбранные записи?',
							title: lang['vopros']
						});
					}
				}},
				{name:'action_refresh', hidden: false, disabled: false},
				{name:'action_print', hidden: true, disabled: false}
			]
		});
		
		this.GridPanel.ViewGridModel.on('selectionchange', function(obj) {
			this.grid.ownerCt.ownerCt.ViewActions.pddp_export.setDisabled(true);
			this.grid.ownerCt.ownerCt.ViewActions.pddp_export_to_tfoms.setDisabled(true);
			this.grid.ownerCt.ownerCt.ViewActions.action_delete.setDisabled(true);
			if (obj.getCount()<1) { 
				this.grid.ownerCt.ownerCt.ViewActions.action_edit.setDisabled(true);
				this.grid.ownerCt.ownerCt.ViewActions.action_view.setDisabled(true);
			}
			else {
				if (obj.getCount()==1) {
					this.grid.ownerCt.ownerCt.ViewActions.action_edit.setDisabled(false);
					this.grid.ownerCt.ownerCt.ViewActions.action_view.setDisabled(false);
				}

				var deleteAccess = true;
				var exportToTfoms = 0;
				var readyForExport = 0;
				
				var records = win.GridPanel.getGrid().getSelectionModel().getSelections();
				var first_DispClass_id = records[0]?records[0].get('DispClass_id'):null;
				for (var i = 0; i < records.length; i++) {
					// для Перми доступно и для профосмотров взрослого населения
					if (
						!Ext.isEmpty(records[i].get('DispClass_id'))
						&& (
							getRegionNick().inlist(['astra', 'vologda'])
							|| (getRegionNick().inlist(['perm', 'khak', 'ufa', 'buryatiya']) && records[i].get('DispClass_id') == 5)
							|| records[i].get('DispClass_id') == 1
						)
					) {
						this.grid.ownerCt.ownerCt.ViewActions.pddp_export.setDisabled(false);
					}

					if (
						records[i].get('exportToTfomsAccess') == 2
						&& !Ext.isEmpty(records[i].get('DispClass_id'))
						&& (
							getRegionNick() == 'astra'
							|| (getRegionNick() == 'pskov' && records[i].get('DispClass_id') == 1)
							|| (getRegionNick().inlist(['khak','perm','ufa','vologda']) && records[i].get('DispClass_id').inlist([ 1, 5 ]))
							|| (getRegionNick().inlist(['kareliya']) && records[i].get('DispClass_id').inlist([ 1, 2, 3, 5, 7, 10, 12 ]))
						)
						&& records[i].get('DispClass_id') == first_DispClass_id
					) {
						exportToTfoms++;
					}

					if (records[i].get('PersonDopDispPlan_CountReady') > 0) {
						readyForExport++;
					}

					if (records[i].get('deleteAccess') == 1) {
						deleteAccess = false;
					}
				}

				this.grid.ownerCt.ownerCt.ViewActions.action_delete.setDisabled(!deleteAccess);
				this.grid.ownerCt.ownerCt.ViewActions.pddp_export_to_tfoms.setDisabled(exportToTfoms != records.length || readyForExport == 0);
			}
		});

		this.PersonDopDispPlanExportGrid = new sw.Promed.ViewFrame({
			title: 'Файлы экспорта',
			focusOnFirstLoad: false,
			region: 'west',
			split: true,
			width: 800,
			dataUrl: '/?c=PersonDopDispPlan&m=loadPersonDopDispPlanExportList',
			toolbar: true,
			useEmptyRecord: false,
			autoLoadData: false,
			pageSize: 100,
			paging: true,
			root: 'data',
			totalProperty: 'totalCount',
			border: false,
			id: 'PersonDopDispPlanExportGrid',
			autoExpandColumn: 'autoexpand',
			object: 'PersonDopDispPlanExport',
			onRowSelect: function(sm,rowIdx,record) {
				win.ExportErrorPlanDDGrid.removeAll();

				if (record.get('PersonDopDispPlanExport_id')) {
					var params = {};
					params.limit = 100;
					params.start = 0;
					params.PersonDopDispPlanExport_id = record.get('PersonDopDispPlanExport_id');
					win.ExportErrorPlanDDGrid.loadData({
						globalFilters: params
					});
				}
			},
			stringfields: [
				{name: 'PersonDopDispPlanExport_id', type: 'int', key: true, hidden: true},
				{name: 'PersonDopDispPlanExport_IsExportPeriod', type: 'int', hidden: true},
				{name: 'PlanPersonListStatusType_id', type: 'int', hidden: true},
				{name: 'PlanPersonListStatusType_Code', type: 'int', hidden: true},
				{name: 'PersonDopDispPlanExport_FileName', header: 'Имя файла', id: 'autoexpand', width: 150, renderer: function(value, cellEl, rec){
					var str = '';

					if (rec.get('PersonDopDispPlanExport_isUsed') == 1) {
						str = 'Файл формируется';
					}
					else if (!Ext.isEmpty(value) && !Ext.isEmpty(rec.get('PersonDopDispPlanExport_DownloadLink'))) {
						str = '<a title="Скачать файл" target="_blank" href="' + rec.get('PersonDopDispPlanExport_DownloadLink') + '">'+value+'</a>';
					}

					return str;
				}},
				{name: 'PersonDopDispPlanExport_expDate', header: 'Дата экспорта', type: 'date', width: 150},
				{name: 'PersonDopDispPlanExport_Period', hidden: !getRegionNick().inlist(['perm', 'vologda']), header: 'Отчетный период', renderer: function(value, cellEl, rec){
					var str = '';

					if (getRegionNick() == 'vologda') {
						str = value;
						if (rec.get('PersonDopDispPlanExport_IsExportPeriod') == 2) {
							str += ' - Декабрь ' + rec.get('PersonDopDispPlanExport_Year');
						}
						return str;
					}

					if (rec.get('PersonDopDispPlanExport_Month')) {
						switch(rec.get('PersonDopDispPlanExport_Month')) {
							case 1:
								str += 'Январь';
								break;
							case 2:
								str += 'Февраль';
								break;
							case 3:
								str += 'Март';
								break;
							case 4:
								str += 'Апрель';
								break;
							case 5:
								str += 'Май';
								break;
							case 6:
								str += 'Июнь';
								break;
							case 7:
								str += 'Июль';
								break;
							case 8:
								str += 'Август';
								break;
							case 9:
								str += 'Сентябрь';
								break;
							case 10:
								str += 'Октябрь';
								break;
							case 11:
								str += 'Ноябрь';
								break;
							case 12:
								str += 'Декабрь';
								break;
						}
					}

					if (rec.get('PersonDopDispPlanExport_Year')) {
						if (str.length > 0) {
							str += " ";
						}
						str += rec.get('PersonDopDispPlanExport_Year');
					}

					return str;
				}, width: 150},
				{name: 'PersonDopDispPlanExport_Year', hidden: true, type: 'int'},
				{name: 'PersonDopDispPlanExport_Month', hidden: true, type: 'int'},
				{name: 'PersonDopDispPlanExport_PackNum', header: 'Номер пакета', type: 'int', hidden: getRegionNick() == 'pskov'},
				{name: 'PersonDopDispPlanExport_DownloadLink', hidden: true},
				{name: 'PersonDopDispPlanExport_isUsed', hidden: true, type: 'int'},
				{name: 'PersonDopDispPlanExport_Count', header: 'Количество записей', width: 150},
				{name: 'PersonDopDispPlanExport_impDate', header: 'Дата импорта', type: 'date', width: 150},
				{name: 'PersonDopDispPlanExportStatus_Name', header: 'Статус', hidden: getRegionNick().inlist(['vologda']), type: 'string', width: 150},
				{name: 'PersonDopDispPlanExport_CountErr', header: 'Ошибки', width: 150}
			],
			actions: [
				{name:'action_add', hidden: true, disabled: false},
				{name:'action_edit', hidden: true, disabled: true},
				{name:'action_view', hidden: true, disabled: true},
				{name:'action_delete', handler: function() { win.deletePersonDopDispPlanExport(); }},
				{name:'action_refresh'},
				{name:'action_print'}
			]
		});

		this.ExportErrorPlanDDGrid = new sw.Promed.ViewFrame({
			title: 'Ошибки',
			focusOnFirstLoad: false,
			region: 'center',
			dataUrl: '/?c=PersonDopDispPlan&m=loadExportErrorPlanDDList',
			toolbar: true,
			useEmptyRecord: false,
			autoLoadData: false,
			pageSize: 100,
			paging: true,
			root: 'data',
			totalProperty: 'totalCount',
			border: false,
			id: 'ExportErrorPlanDDGrid',
			autoExpandColumn: 'autoexpand',
			object: 'ExportErrorPlanDD',
			onRowSelect: function(sm,rowIdx,record) {

			},
			stringfields: [
				{name: 'ExportErrorPlanDD_id', type: 'int', key: true, hidden: true},
				{name: 'PlanPersonList_ExportNum', header: '№ записи', width: 70},
				{name: 'DispClass_Name', header: 'Тип осмотра', hidden: !getRegionNick().inlist(['vologda']), width: 150},
				{name: 'PersonDopDispPlan_Month', header: 'Месяц', hidden: !getRegionNick().inlist(['vologda']), width: 150, renderer: function(value, cellEl, rec){
					if (!value) return false;
					var str = '';
					switch(value) {
						case 1: str += 'Январь'; break;
						case 2: str += 'Февраль'; break;
						case 3: str += 'Март'; break;
						case 4: str += 'Апрель'; break;
						case 5: str += 'Май'; break;
						case 6: str += 'Июнь'; break;
						case 7: str += 'Июль'; break;
						case 8: str += 'Август'; break;
						case 9: str += 'Сентябрь'; break;
						case 10: str += 'Октябрь'; break;
						case 11: str += 'Ноябрь'; break;
						case 12: str += 'Декабрь'; break;
					}
					return str;
				}},
				{name: 'Person_Fio', header: 'ФИО', width: 300, id: 'autoexpand'},
				{name: 'ExportErrorPlanDDType_Code', header: 'Код ошибки', hidden: getRegionNick().inlist(['ekb', 'vologda']), width: 150},
				{name: 'ExportErrorPlanDDType_Name', header: 'Описание ошибки', width: 300}
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
				this.PersonDopDispPlanExportGrid,
				this.ExportErrorPlanDDGrid
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
					tooltip   : lang['zakryit'],
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
		sw.Promed.swPersonDopDispPlanListWindow.superclass.initComponent.apply(this, arguments);
	}
});
