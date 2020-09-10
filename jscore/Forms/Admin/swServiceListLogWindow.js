/**
 * swServiceListLogWindow - окно просмотра логов запуска сервисов промеда
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      	Admin
 * @access       	public
 * @copyright		Copyright (c) 2016 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			28.01.2016
 */
/*NO PARSE JSON*/

sw.Promed.swServiceListLogWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swServiceListLogWindow',
	layout: 'border',
	title: langs('Журнал работы сервисов'),
	maximizable: false,
	maximized: true,
	urlServiceFixingErrors: '', //админский портал, для просмотра информации по ошибкам, возникающим в процессе записи
	runServiceEFISImport: function() {
		var ServiceList_id = 9;
		var logGrid = this.ServiceListLogGrid.getGrid();

		var mask = this.getLoadMask('Запуск импорта...');
		mask.show();

		Ext.Ajax.request({
			url: '/?c=ServiceEFIS&m=runImport&objects=all',
			callback: function(options, success, response) {
				mask.hide();
				if (!success) {
					Ext.Msg.alert(langs('Ошибка'), 'Ошибка при запуске импорта из сервиса ЕФИС');
				} else {
					var response_obj = Ext.util.JSON.decode(response.responseText);

					logGrid.getStore().baseParams.ServiceList_id = ServiceList_id;
					logGrid.getStore().baseParams.start = 0;
					logGrid.getStore().load({
						callback: function() {
							if (response_obj.ServiceListLog_id) {
								var index = logGrid.getStore().find('ServiceListLog_id', response_obj.ServiceListLog_id);
								if (index >= 0) logGrid.getSelectionModel().selectRow(index);
							}
						}
					});
				}
			}.createDelegate(this)
		});
	},

	runServiceISZLImport: function() {
		var ServiceList_id = 10;
		var logGrid = this.ServiceListLogGrid.getGrid();

		var mask = this.getLoadMask('Запуск импорта...');
		mask.show();

		Ext.Ajax.request({
			url: '/?c=ServiceISZL&m=runPublisher',
			callback: function(options, success, response) {
				mask.hide();
				if (!success) {
					Ext.Msg.alert(langs('Ошибка'), 'Ошибка при запуске импорта');
				} else {
					var response_obj = Ext.util.JSON.decode(response.responseText);

					logGrid.getStore().baseParams.ServiceList_id = ServiceList_id;
					/*logGrid.getStore().baseParams.start = 0;
					logGrid.getStore().load({
						callback: function() {
							if (response_obj.ServiceListLog_id) {
								var index = logGrid.getStore().find('ServiceListLog_id', response_obj.ServiceListLog_id);
								if (index >= 0) logGrid.getSelectionModel().selectRow(index);
							}
						}
					});*/

					this.doLogFilter(true);
				}
			}.createDelegate(this)
		});
	},

	runServiceFRMRImport: function() {
		var ServiceList_id = 50;
		var logGrid = this.ServiceListLogGrid.getGrid();

		var mask = this.getLoadMask('Запуск импорта...');
		mask.show();

		Ext.Ajax.request({
			url: '/?c=ServiceFRMR&m=runImport',
			callback: function(options, success, response) {
				mask.hide();
				if ( !success ) {
					sw.swMsg.alert(langs('Ошибка'), 'Ошибка при запуске импорта');
				}
				else {
					var response_obj = Ext.util.JSON.decode(response.responseText);

					logGrid.getStore().baseParams.ServiceList_id = ServiceList_id;

					this.doLogFilter(true);
				}
			}.createDelegate(this)
		});
	},

	runServiceFRMOSend: function() {
		getWnd('swExportToFRMOWindow').show();
	},

	runServiceFRMRSend: function() {
		getWnd('swExportToFRMRWindow').show();
	},

	runServiceFRMOUpdate: function() {
		getWnd('swImportFromFRMOWindow').show({
			callback: function() {
				this.ServiceListLogGrid.getGrid().getStore().reload();
			}.createDelegate(this)
		});
	},

	resumeFRMOSession: function() {
		var win = this;

		var grid = this.ServiceListLogGrid.getGrid();
		var record = grid.getSelectionModel().getSelected();

		if (record && record.get('ServiceListLog_id')) {
			win.getLoadMask('Возобновление передачи данных в сервис ФРМО').show();
			Ext.Ajax.request({
				url: '/?c=ServiceFRMO&m=resumeFRMOSession',
				params: {
					FRMOSession_id: record.get('ServiceListLog_id')
				},
				callback: function() {
					win.getLoadMask().hide();
					grid.getStore().reload();
				}
			});
		}
	},

	runImport: function() {
		var grid = this.ServiceListGrid.getGrid();
		var record = grid.getSelectionModel().getSelected();

		if (!record || Ext.isEmpty(record.get('ServiceList_Code'))) return;

		switch(record.get('ServiceList_Code')) {
			case 9: this.runServiceEFISImport();break;
			case 10: this.runServiceISZLImport();break;
			case 11: this.runServiceFRMOSend(); break;
			case 18: this.runServiceFRMOUpdate(); break;
			case 19: this.runServiceFRMRImport();break;
			case 20: this.runServiceFRMRSend();break;
		}
	},

	doLogDetailFilter: function(params)
	{
		var LogPanel = this.ServiceListLogGrid,
			DetailLogPanel = this.ServiceListDetailLogGrid,
			DetailFormPanel = this.ServiceListDetailLogFilter,
			LogGrid = LogPanel.getGrid(),
			DetailLogGrid = DetailLogPanel.getGrid(),
			DetailBaseForm = DetailFormPanel.getForm(),
			record = LogGrid.getSelectionModel().getSelected();
			
		if (this.ServiceList_Code.inlist([15,16])) {
			DetailLogPanel = this.ServiceListPackageGrid;
			DetailLogGrid = DetailLogPanel.getGrid();
		}

		if (typeof params == 'object')
		{
			var reset = params.reset,
				ServiceListLog_id = params.ServiceListLog_id;
		} else
		{
			var reset = false,
				ServiceListLog_id = null;
		}

		DetailLogPanel.removeAll();

		if (reset) {
			DetailBaseForm.reset();
			
			if (this.ARMType != 'superadmin' && this.ServiceList_Code.inlist([15,16])) {
				DetailBaseForm.findField('Lpu_oid').setValue(getGlobalOptions().lpu_id);
			}
		}

		var baseParams = getAllFormFieldValues(DetailFormPanel);

		if ( ServiceListLog_id || (record && ! Ext.isEmpty(record.get('ServiceListLog_id')) ))
		{
			baseParams.ServiceListLog_id = ServiceListLog_id || record.get('ServiceListLog_id');
		} else
		{
			return false;
		}

		baseParams.start = 0;
		baseParams.limit = 100;
			
		DetailLogGrid.getStore().load({params: baseParams});

		return true;
	},
	doLogFilter: function(reset) {
		var log_panel = this.ServiceListLogGrid;
		var log_grid = log_panel.getGrid();
		var service_grid = this.ServiceListGrid.getGrid();
		var ServiceList_Code = service_grid.getSelectionModel().getSelected().get('ServiceList_Code');
		
		if(log_grid.getStore().baseParams.ServiceList_id && log_grid.getStore().baseParams.ServiceList_id>0){
			var form_panel = this.ServiceListLogFilter;
			var base_form = form_panel.getForm();

			var lpu_combo = base_form.findField('Lpu_oid');
			var daterange_field = base_form.findField('ServiceListLog_DateRange');

			log_panel.removeAll();

			if (reset) {
				base_form.reset();
				if (this.ARMType != 'superadmin') {
					lpu_combo.setValue(getGlobalOptions().lpu_id);
				}
				
				if (ServiceList_Code.inlist([15,16])) {
					var date = new Date();
					var begRange = new Date(date);
					begRange.setDate(1);
					var endRange = new Date(date);
					endRange.addMonths(1).setDate(0);
					
					daterange_field.setValue(begRange.format('d.m.Y') + ' - ' + endRange.format('d.m.Y'));
				}
			}

			var params = getAllFormFieldValues(form_panel);

			params.start = 0;
			params.limit = 100;

			log_grid.getStore().load({params: params});
		}
	},
	loadServiceListLogGrid: function(record, ServiceListPackageType_id){
		var base_form = this.ServiceListLogFilter.getForm();
		
		this.ServiceListGrid.getAction('action_add').disable();
		this.ServiceListPackageDetailGrid.hide();
		this.ServiceListLogGrid.removeAll();
		if (!record || Ext.isEmpty(record.get('ServiceList_id'))) {
			return false;
		}
		this.ServiceList_Code = record.get('ServiceList_Code');

		if (record.get('ServiceList_Code').inlist([5])) {
			this.ServiceListGrid.getAction('action_settings').enable();
		} else {
			this.ServiceListGrid.getAction('action_settings').disable();
		}
		
		if (this.ServiceList_Code.inlist([15,16]) && getRegionNick().inlist(['kareliya'])) {
			this.ServiceListPackageGrid.getAction('toggleIsNotSendPackage').show();
		} else {
			this.ServiceListPackageGrid.getAction('toggleIsNotSendPackage').hide();
		}

		this.ServiceListDetailLogGrid.setColumnHidden('ServiceListPackage_GUID', !record.get('ServiceList_Code').inlist([113]));
		this.ServiceListDetailLogGrid.setColumnHidden('ServiceListPackageType_Description', !record.get('ServiceList_Code').inlist([113]));
		this.ServiceListDetailLogGrid.setColumnHidden('ServiceListProcDataType_Result', !record.get('ServiceList_Code').inlist([113]));
		this.ServiceListDetailLogGrid.setColumnHidden('ServiceListLogType_Name', record.get('ServiceList_Code').inlist([113]));
		this.ServiceListDetailLogGrid.setColumnHidden('ServiceListDetailLog_Message', record.get('ServiceList_Code').inlist([113]));
		this.ServiceListDetailLogGrid.setColumnHidden('Evn_id',
			(getRegionNick() == 'vologda' && record.get('ServiceList_Code').inlist([17,18,19])) ||
			record.get('ServiceList_Code').inlist([113])
		);
		this.ServiceListPackageGrid.setColumnHidden('ServiceListPackage_IsNotSend', !getRegionNick().inlist(['kareliya']));
		this.ServiceListPackageGrid.setColumnHidden('ServiceListProcDataType_Result', !record.get('ServiceList_Code').inlist([10,15,85,86]));
		this.ServiceListPackageGrid.setColumnHidden('ServiceListProcDataType_Name', record.get('ServiceList_Code').inlist([85,86]));
		this.ServiceListPackageGrid.setColumnHidden('ServiceListPackageType_Description', record.get('ServiceList_Code').inlist([85,86]));
		this.ServiceListPackageGrid.setColumnHidden('ServiceListDetailLog_Message', !record.get('ServiceList_Code').inlist([85,86]));
		this.ServiceListPackageGrid.setColumnWidth('ServiceListProcDataType_Result', record.get('ServiceList_Code').inlist([85,86]) ? 100 : 250);

		this.ServiceListLogGrid.setColumnHidden('Lpu_Nick', record.get('ServiceList_Code') != 11);
		this.ServiceListLogGrid.setColumnHidden('ServiceListResult_ErrorCount', record.get('ServiceList_Code') != 11);
		this.ServiceListLogGrid.setActionHidden('action_add', record.get('ServiceList_Code') != 11);
		
		this.ServiceListLogGrid.setColumnHidden('ServiceListPackage_AllCount', !record.get('ServiceList_Code').inlist([10,15,16]));
		this.ServiceListLogGrid.setColumnHidden('ServiceListPackage_ErrorCount', !record.get('ServiceList_Code').inlist([10,15,16]));

		this.ServiceListDetailLogGrid.removeAll();
		this.ServiceListPackageGrid.removeAll();
		
		this.ServiceListDetailLogGrid.setVisible(!this.ServiceList_Code.inlist([10,15,85,86]));
		this.ServiceListDetailLogFilter.setVisible(!this.ServiceList_Code.inlist([85,86]));
		
		var f_form = this.ServiceListDetailLogFilter.getForm();
		f_form.findField('PackageStatus_id').setContainerVisible(this.ServiceList_Code.inlist([10,15,16]));
		f_form.findField('Lpu_oid').setContainerVisible(this.ServiceList_Code.inlist([10,15,16]));
		f_form.findField('ServiceListLogType_id').setContainerVisible(!this.ServiceList_Code.inlist([10,15]));
		f_form.findField('ServiceListDetailLog_Message').setContainerVisible(!this.ServiceList_Code.inlist([10,15]));
		
		if (this.ARMType != 'superadmin' && this.ServiceList_Code.inlist([10,15,16])) {
			f_form.findField('Lpu_oid').setValue(getGlobalOptions().lpu_id);
			f_form.findField('Lpu_oid').disable();
		} else {
			f_form.findField('Lpu_oid').enable();
		}

		if (this.ServiceList_Code.inlist([10,15,16,85,86])) {
			this.ServiceListPackageGrid.show();
			this.ServiceListPackageGrid.doLayout();
			this.detailCardPanel.doLayout();
		} else {
			this.ServiceListPackageGrid.hide();
			this.ServiceListDetailLogGrid.getGrid().getStore().baseParams = {};
		}

		if (record.get('ServiceList_Code') == -1 && record.get('ServiceList_Code') == -1) {
			//Вологда. перехода в админский портал, для просмотра информации по ошибкам, возникающим в процессе записи
			if(this.urlServiceFixingErrors) {
				window.open(this.urlServiceFixingErrors);
				this.ServiceListGrid.getGrid().getSelectionModel().clearSelections();
				this.ServiceListLogGrid.getGrid().getStore().baseParams.ServiceList_id = '';
			}
		} else {
			this.ServiceListLogGrid.getGrid().getStore().load({
				params: {
					ServiceList_id: record.get('ServiceList_id'),
					ServiceList_Code: record.get('ServiceList_Code'),
					ServiceListPackageType_id: ServiceListPackageType_id,
					Lpu_oid: base_form.findField('Lpu_oid').getValue(),
					ServiceListLog_DateRange: base_form.findField('ServiceListLog_DateRange').getRawValue(),
					start: 0
				}
			});

			// Разрешаем запуск для вологды ["Обновление ФРМО"]
			if(
				getRegionNick() == 'vologda'
				&& record.get('ServiceList_Code').inlist(['18'])
			) {
				this.ServiceListGrid.getAction('action_add').enable();
			}

			if (record.get('ServiceList_Code').inlist([9,10,11,19,20])) {
				this.ServiceListGrid.getAction('action_add').enable();
			}
		}
		
		this.detailCardPanel.fireEvent('resize', this.detailCardPanel, this.detailCardPanel.el.getWidth(), this.detailCardPanel.el.getHeight());
	},
	addLinkPortalInServiceListGrid: function(){
		if(getRegionNick() != 'vologda') return false;

		var addInStore = Ext.data.Record.create([
			{name: 'ServiceList_id', type: 'int', header: 'ID', key: true},
			{name: 'ServiceList_Code', type: 'int', hidden: true},
			{name: 'ServiceList_Name', header: langs('Наименование'), type: 'string', id: 'autoexpand'}
		]);
		var store = this.ServiceListGrid.getGrid().getStore();
		store.add(new addInStore({ServiceList_id: -1, ServiceList_Code: -1, ServiceList_Name: 'Сервис фиксации ошибок при записи на прием, вызове врача на дом'}))
	},

	openServiceSettingsWindow: function() {
		var grid = this.ServiceListGrid.getGrid();
		var record = grid.getSelectionModel().getSelected();

		if (!record || Ext.isEmpty(record.get('ServiceList_Code'))) {
			return;
		}

		if (record.get('ServiceList_Code') == 5) {
			getWnd('swSURMOSettingsWindow').show();
		}
	},

	show: function() {
		sw.Promed.swServiceListLogWindow.superclass.show.apply(this, arguments);

		this.ARMType = null;
		this.ServiceList_Code = null;

		if (arguments[0].ARMType) {
			this.ARMType = arguments[0].ARMType;
		}
		
		this.ServiceListPackageTypeGrid.hide();
		this.ServiceListGrid.show();
		this.ServiceListGrid.setHeight(this.el.getHeight()-58);

		if (!this.ServiceListGrid.getAction('action_settings')) {
			this.ServiceListGrid.addActions({
				name: 'action_settings',
				text: langs('Настройки'),
				iconCls: 'settings-global16',
				disabled: true,
				handler: function() {
					this.openServiceSettingsWindow();
				}.createDelegate(this)
			});
		}
		
		if (!this.ServiceListPackageDetailGrid.getAction('action_back')) {
			this.ServiceListPackageDetailGrid.addActions({
				name: 'action_back',
				text: langs('Назад'),
				iconCls : 'back16',
				handler: function() {
					this.ServiceListPackageDetailGrid.hide();
					this.ServiceListPackageGrid.show();
					this.detailCardPanel.doLayout();
				}.createDelegate(this)
			}, 0);
		}
		
		if (!this.ServiceListPackageGrid.getAction('toggleIsNotSendPackage')) {
			this.ServiceListPackageGrid.addActions({
				name: 'toggleIsNotSendPackage',
				text: langs('Не отправлять повторно'),
				handler: function() {
					var grid = this.ServiceListPackageGrid.getGrid();
					var record = grid.getSelectionModel().getSelected();
					if (record) {
						this.toggleIsNotSendPackage(record.id);
						grid.getSelectionModel().selectRecords([record]);
					}
				}.createDelegate(this)
			});
		}

		var log_filter_form = this.ServiceListLogFilter.getForm();

		var lpu_combo = log_filter_form.findField('Lpu_oid');
		if (this.ARMType == 'superadmin') {
			lpu_combo.enable();
			lpu_combo.setValue(null);
		} else {
			lpu_combo.disable();
			lpu_combo.setValue(getGlobalOptions().lpu_id);
		}

		this.ServiceListGrid.getGrid().getStore().load({
			callback: function() {
				if (this.ARMType == 'lpuadmin') {
					this.ServiceListGrid.getGrid().getStore().filterBy(function(rec){
						return rec.get('ServiceList_Code').inlist([12,13,15,113]);
					});
					this.ServiceListGrid.focus();
				}
				if (this.ARMType == 'mstat') {
					this.ServiceListGrid.getGrid().getStore().filterBy(function(rec){
						return rec.get('ServiceList_Code').inlist([15,16]);
					});
					this.ServiceListGrid.focus();
				}
				this.addLinkPortalInServiceListGrid();
			}.createDelegate(this)
		});
	},

	initComponent: function() {
		var win = this;

		this.ServiceListGrid = new sw.Promed.ViewFrame({
			id: 'SLLW_ServiceListGrid',
			dataUrl: '/?c=ServiceList&m=loadServiceListGrid',
			border: true,
			autoLoadData: false,
			paging: false,
			region: 'center',
			title: langs('Список сервисов'),
			stringfields: [
				{name: 'ServiceList_id', type: 'int', header: 'ID', key: true},
				{name: 'ServiceList_Code', type: 'int', hidden: true},
				{name: 'ServiceList_Name', header: langs('Наименование'), type: 'string', id: 'autoexpand'}
			],
			actions: [
				{name: 'action_add', text: langs('Запуск'), icon: 'img/icons/actions16.png', handler: this.runImport.createDelegate(this)},
				{name:'action_edit', hidden: true},
				{name:'action_view', hidden: true},
				{name:'action_delete', hidden: true},
				{name:'action_refresh', handler: function() {
					this.ServiceListGrid.loadData({
						callback: function() {
							if (this.ARMType == 'lpuadmin') {
								this.ServiceListGrid.getGrid().getStore().filterBy(function(rec){
									return rec.get('ServiceList_Code').inlist([12,13,15]);
								});
								this.ServiceListGrid.focus();
							}
							if (this.ARMType == 'mstat') {
								this.ServiceListGrid.getGrid().getStore().filterBy(function(rec){
									return rec.get('ServiceList_Code').inlist([15]);
								});
								this.ServiceListGrid.focus();
							}
							this.addLinkPortalInServiceListGrid();
						}.createDelegate(this)
					});
				}.createDelegate(this)}
			],
			onRowSelect: function(sm, index, record) {
				var base_form = this.ServiceListLogFilter.getForm();
				var daterange_field = base_form.findField('ServiceListLog_DateRange');
				
				daterange_field.setValue(null);
				
				if (!record || !record.get('ServiceList_Code')) return false;
				
				if (record.get('ServiceList_Code').inlist([15,16])) {
					var date = new Date();
					var begRange = new Date(date);
					begRange.setDate(1);
					var endRange = new Date(date);
					endRange.addMonths(1).setDate(0);
					
					daterange_field.setValue(begRange.format('d.m.Y') + ' - ' + endRange.format('d.m.Y'));
				}
			
				this.loadServiceListLogGrid(record, null);
			}.createDelegate(this),
			onDblClick: function(grid, number, object) {
				
				var record = grid.getSelectionModel().getSelected();
				if (!record || !record.get('ServiceList_id') || !record.get('ServiceList_Code').inlist([15,113])) return false;
				
				win.ServiceListGrid.hide();
				win.ServiceListPackageTypeGrid.show();
				
				var params = {ServiceList_id: record.get('ServiceList_id')};
				win.ServiceListPackageTypeGrid.loadData({params: params, globalFilters: params})
			}
		});
		
		this.ServiceListPackageTypeGrid = new sw.Promed.ViewFrame({
			id: 'SLLW_ServiceListPackageTypeGrid',
			dataUrl: '/?c=ServiceList&m=loadServiceListPackageTypeGrid',
			border: true,
			autoLoadData: false,
			paging: false,
			region: 'center',
			title: langs('Список сервисов'),
			stringfields: [
				{name: 'ServiceListPackageType_id', type: 'int', header: 'ID', key: true},
				{name: 'ServiceListPackageType_Code', type: 'int', hidden: true},
				{name: 'ServiceListPackageType_Description', header: langs('Наименование'), type: 'string', id: 'autoexpand'}
			],
			actions: [
				{name:'action_add', text: langs('Назад'), icon: 'img/icons/arrow-previous16.png', handler: function() {
					win.ServiceListPackageTypeGrid.hide();
					win.ServiceListGrid.show();
					
					sl_record = win.ServiceListGrid.getGrid().getSelectionModel().getSelected();
					win.loadServiceListLogGrid(sl_record, null);
				}},
				{name:'action_edit', hidden: true},
				{name:'action_view', hidden: true},
				{name:'action_delete', hidden: true},
				{name:'action_refresh', hidden: true},
				{name:'action_print', hidden: true}
			],
			onRowSelect: function(sm, index, record) {
				
				if (!record || !record.get('ServiceListPackageType_id')) return false;
				
				sl_record = this.ServiceListGrid.getGrid().getSelectionModel().getSelected();
				
				this.loadServiceListLogGrid(sl_record, record.get('ServiceListPackageType_id'));
				
			}.createDelegate(this)
		});

		this.ServiceListLogFilter = new Ext.FormPanel({
			border: false,
			region: 'north',
			autoHeight: true,
			labelAlign: 'right',
			bodyStyle: 'padding-top: 5px; background:#DFE8F6;',
			defaults: {bodyStyle: 'background:#DFE8F6;'},
			keys: [{
				fn: function() {
					this.doLogFilter();
				}.createDelegate(this),
				key: Ext.EventObject.ENTER,
				stopEvent: true
			}],
			items: [{
				layout: 'column',
				border: false,
				defaults: {bodyStyle: 'background:#DFE8F6;'},
				items: [{
					layout: 'form',
					border: false,
					labelWidth: 40,
					items: [{
						xtype: 'swlpucombo',
						hiddenName: 'Lpu_oid',
						fieldLabel: 'МО',
						width: 240
					}]
				}, {
					layout: 'form',
					border: false,
					items: [{
						xtype: 'daterangefield',
						name: 'ServiceListLog_DateRange',
						fieldLabel: 'Дата запуска',
						plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
						width: 180
					}]
				}, {
					layout: 'form',
					border: false,
					style: 'margin-left: 20px;',
					items: [{
						xtype: 'button',
						text: langs('Найти'),
						iconCls: 'search16',
						handler: function() {
							this.doLogFilter();
						}.createDelegate(this)
					}]
				}, {
					layout: 'form',
					border: false,
					style: 'margin-left: 10px;',
					items: [{
						xtype: 'button',
						text: langs('Сброс'),
						iconCls: 'reset16',
						handler: function() {
							this.doLogFilter(true);
						}.createDelegate(this)
					}]
				}]
			}]
		});

		this.ServiceListLogGrid = new sw.Promed.ViewFrame({
			id: 'SLLW_ServiceListLogGrid',
			dataUrl: '/?c=ServiceList&m=loadServiceListLogGrid',
			border: false,
			autoLoadData: false,
			paging: true,
			region: 'center',
			root: 'data',
			totalProperty: 'totalCount',
			bodyStyle: 'border-top: 1px solid #99bbe8; border-bottom: 1px solid #99bbe8;',
			stringfields: [
				{name: 'ServiceListLog_id', type: 'int', header: 'ID', key: true},
				{name: 'ServiceList_id', type: 'int', hidden: true},
				{name: 'ServiceListResult_id', type: 'int', hidden: true},
				{name: 'ServiceListResult_Code', type: 'int', hidden: true},
				{name: 'ServiceListLog_begDT', header: langs('Запуск'), type: 'datetimesec', width: 120},
				{name: 'ServiceListLog_endDT', header: langs('Завершение'), type: 'datetimesec', width: 120},
				{name: 'Lpu_Nick', header: langs('МО'), type: 'string', width: 120},
				{name: 'ServiceListResult_Name', header: langs('Результат'), type: 'string', id: 'autoexpand'},
				{name: 'ServiceListPackage_AllCount', header: langs('Всего пакетов'), type: 'int', width: 110},
				{name: 'ServiceListPackage_ErrorCount', header: langs('Пакетов с ошибками'), type: 'int', width: 110},
				{name: 'ServiceListResult_ErrorCount', header: langs('Кол-во ошибок'), renderer: function(v, p, record) {
					if (record && record.get('ServiceListLog_id')) {
						return '<a href="javascript:getWnd(\'swFRMOSessionErrorListWindow\').show({\'FRMOSession_id\':' + record.get('ServiceListLog_id') + '});" style="cursor: pointer; color: #0000EE;">' + v + '</a>';
					} else {
						return v;
					}
				}},
				{name: 'FRMOSessionActionType_Code', header: langs('Статус'), type: 'int', hidden: true}
			],
			actions: [
				{name:'action_add', text: langs('Возобновить передачу'), icon: 'img/icons/actions16.png', handler: this.resumeFRMOSession.createDelegate(this)},
				{name:'action_edit', hidden: true},
				{name:'action_view', hidden: true},
				{name:'action_delete', hidden: true}
			],
			onRowSelect: function(sm, index, record) {
				var form_panel = this.ServiceListLogFilter;
				var base_form = form_panel.getForm();
				
				if (this.ServiceList_Code.inlist([15,85,86]))  {
					win.ServiceListPackageGrid.show();
					win.ServiceListPackageDetailGrid.hide();
					win.detailCardPanel.doLayout();
				}

				this.ServiceListLogGrid.setActionDisabled('action_add', record.get('FRMOSessionActionType_Code') != 60 || record.get('ServiceListResult_id') != 3 || parseInt(record.get('ServiceListResult_ErrorCount')) == 0);

				if (this.ServiceList_Code == 11) {
					this.detailCardPanel.getLayout().setActiveItem(1);
					this.FRMOSessionHistGrid.removeAll();
					if (record && record.get('ServiceListLog_id')) {
						this.FRMOSessionHistGrid.getGrid().getStore().load({
							params: {
								limit: 100,
								start: 0,
								FRMOSession_id: record.get('ServiceListLog_id')
							}
						});
					}
				} else {
					this.detailCardPanel.getLayout().setActiveItem(0);
					var gridPanels = [];
					if (this.ServiceList_Code.inlist([15,85,86])) {
						gridPanels = [
							this.ServiceListPackageGrid
						];
					} else if (this.ServiceList_Code.inlist([10,16])) {
						gridPanels = [
							this.ServiceListPackageGrid,
							this.ServiceListDetailLogGrid
						];
					} else {
						gridPanels = [
							this.ServiceListDetailLogGrid
						];
					}

					gridPanels.forEach(function(gridPanel) {
						if (gridPanel == this.ServiceListDetailLogGrid &&
							record && !Ext.isEmpty(record.get('ServiceListLog_id'))
						) {
							this.doLogDetailFilter({ServiceListLog_id: record.get('ServiceListLog_id'), reset: true});
							return true;
						}

						gridPanel.removeAll();
						if (!record || Ext.isEmpty(record.get('ServiceListLog_id'))) {
							gridPanel.getGrid().getStore().baseParams.ServiceListLog_id = null;
							return false;
						}
						gridPanel.getGrid().getStore().load({
							params: {
								ServiceListLog_id: record.get('ServiceListLog_id'),
								ServiceListPackageType_id: win.ServiceListLogGrid.getGrid().getStore().baseParams.ServiceListPackageType_id || null,
								Lpu_oid: base_form.findField('Lpu_oid').getValue(),
								start: 0
							}
						});
					});
				}
			}.createDelegate(this)
		});

		this.ServiceListDetailLogFilter = new Ext.FormPanel({
			border: false,
			region: 'north',
			autoHeight: true,
			labelAlign: 'right',
			bodyStyle: 'padding-top: 5px; background:#DFE8F6;',
			defaults: {bodyStyle: 'background:#DFE8F6;'},
			items: [{
				layout: 'column',
				border: false,
				defaults: {bodyStyle: 'background:#DFE8F6;'},
				items: [{
					layout: 'form',
					border: false,
					labelWidth: 100,
					items: [{
						xtype: 'swcommonsprcombo',
						comboSubject: 'PackageStatus',
						hiddenName: 'PackageStatus_id',
						fieldLabel: 'Статус пакета',
						width: 220
					}]
				}, {
					layout: 'form',
					border: false,
					labelWidth: 50,
					items: [{
						xtype: 'swlpucombo',
						hiddenName: 'Lpu_oid',
						fieldLabel: 'МО',
						width: 220
					}]
				}, {
					layout: 'form',
					border: false,
					labelWidth: 40,
					items: [{
						xtype: 'combo',
						fieldLabel: 'Тип',
						hiddenName: 'ServiceListLogType_id',
						labelAlign: 'left',
						mode:'local',
						width: 100,
						editable: false,
						triggerAction : 'all',
						store:new Ext.data.SimpleStore(  {
							fields: [{name:'ServiceListLogType_Name', type:'string'},{ name:'ServiceListLogType_id',type:'int'}],
							data: [
								['', null],
								['Сообщение', 1],
								['Ошибка', 2],
								['Ответ', 3]
							]
						}),
						displayField:'ServiceListLogType_Name',
						valueField:'ServiceListLogType_id',
						tpl: '<tpl for="."><div class="x-combo-list-item">{ServiceListLogType_Name}&nbsp;</div></tpl>'
						}
					]
				}, {
					layout: 'form',
					border: false,
					labelWidth: 200,
					items: [{
						xtype: 'textfield',
						name: 'ServiceListDetailLog_Message',
						fieldLabel: 'Искать в сообщении',
						width: 200
					}]
				}, {
					layout: 'form',
					border: false,
					style: 'margin-left: 20px;',
					items: [{
						xtype: 'button',
						text: langs('Найти'),
						iconCls: 'search16',
						handler: function() {
							this.doLogDetailFilter();
						}.createDelegate(this)
					}]
				}, {
					layout: 'form',
					border: false,
					style: 'margin-left: 10px;',
					items: [{
						xtype: 'button',
						text: langs('Сброс'),
						iconCls: 'reset16',
						handler: function() {
							this.doLogDetailFilter({reset: true});
						}.createDelegate(this)
					}]
				}]
			}]
		});

		this.ServiceListDetailLogGrid = new sw.Promed.ViewFrame({
			id: 'SLLW_ServiceListDetailLogGrid',
			dataUrl: '/?c=ServiceList&m=loadServiceListDetailLogGrid',
			border: false,
			height: 360,
			autoLoadData: false,
			paging: true,
			region: 'south',
			bodyStyle: 'border-top: 1px solid #99bbe8;',
			root: 'data',
			totalProperty: 'totalCount',
			stringfields: [
				{name: 'ServiceListDetailLog_id', type: 'int', header: 'ID', key: true},
				{name: 'EvnClass_SysNick', type: 'string', hidden: true},
				{name: 'ServiceListPackage_id', type: 'int', hidden: true},
				{name: 'Person_id', type: 'int', hidden: true},
				{name: 'ServiceListDetailLog_insDT', header: langs('Дата/время'), type: 'datetimesec', width: 120},
				{name: 'ServiceListLogType_Name', header: langs('Тип'), type: 'string', width: 100},
				{name: 'ServiceListPackage_GUID', type: 'string', header: 'Идентификатор пакета', width: 250},
				{name: 'ServiceListPackageType_Description', header: 'Тип пакета', type: 'string', id: 'autoexpand'},
				{name: 'Evn_id', header: 'Событие', width: 100, renderer: function(v, p, record) {
					if(!v) return "";
					switch (record.get('EvnClass_SysNick')) {
						case 'EvnDirection':
							return '<a href="javascript:getWnd(\'swEvnDirectionEditWindow\').show({\'action\':\'view\',\'EvnDirection_id\':'+v+',\'formParams\':{}});" style="cursor: pointer; color: #0000EE;">'+v+'</a>';
							break;
						case 'EvnUslugaPar':
							return '<a href="javascript:getWnd(\'swEvnUslugaParEditWindow\').show({\'action\':\'view\',\'EvnUslugaPar_id\':'+v+',\'Person_id\':'+record.get('Person_id')+',\'viewOnly\':false});" style="cursor: pointer; color: #0000EE;">'+v+'</a>';
							break;
					}
					return v;
				}},
				{name: 'ServiceListDetailLog_Message', header: langs('Сообщение'),  id: 'autoexpand', renderer: function(v, p, record) {
					if (v == null)
					{
						return '';
					}

					var height = '50px';

					if(v.length == null || v.length <300)
					{
						height = 'auto';
					}
						return (
							'<div ' +
									'onclick="this.style.height = (this.style.height == \'auto\' ? \'50px\' : \'auto\')" ' +
									'style="white-space:pre-wrap; height: ' + height + '; overflow: hidden;" ' +
									'title="Нажмите, чтобы раскрыть"' +
									'>' + v +
							'</div>');
					}},
				{name: 'ServiceListProcDataType_Result', header: 'Результат', type: 'string', width: 250},
				{name: 'RequestData', header: 'Данные пакета', type: 'string', hidden: true}
			],
			actions: [
				{name:'action_add', hidden: true},
				{name:'action_edit', hidden: true},
				{name:'action_view', hidden: true},
				{name:'action_delete', hidden: true}
			],
			onDblClick: function(grid, number, object){

				if (!win.ServiceList_Code.inlist([113])) return false;

				var record = grid.getSelectionModel().getSelected();
				if (!record || !record.get('ServiceListPackage_id')) return false;

				if (!!record.get('RequestData')) {
					openNewWindow(record.get('RequestData'));
				}
			}
		});

		this.FRMOSessionHistGrid = new sw.Promed.ViewFrame({
			id: 'SLLW_FRMOSessionHistGrid',
			dataUrl: '/?c=ServiceFRMO&m=loadFRMOSessionHistGrid',
			border: false,
			height: 360,
			autoLoadData: false,
			paging: true,
			region: 'center',
			bodyStyle: 'border-top: 1px solid #99bbe8;',
			root: 'data',
			totalProperty: 'totalCount',
			stringfields: [
				{name: 'FRMOSessionHist_id', type: 'int', header: 'ID', key: true},
				{name: 'FRMOSessionActionType_Name', header: 'Событие', width: 150, type: 'string'},
				{name: 'FRMOSessionHist_insDT', header: 'Начато', width: 120, type: 'datetime'},
				{name: 'FRMOSessionHist_sendDT', header: 'Передача в ФРМО', width: 120, type: 'datetime'},
				{name: 'FRMOSessionHist_getDT', header: 'Ответ от  ФРМО', width: 120, type: 'datetime'},
				{name: 'FRMOSessionHist_doneDT', header: 'Завершено', width: 120, type: 'datetime'}
			],
			actions: [
				{name:'action_add', hidden: true},
				{name:'action_edit', hidden: true},
				{name:'action_view', hidden: true},
				{name:'action_delete', hidden: true}
			]
		});

		this.changeTexttoggleIsNotSendPackage = function(record) {
			var grid = this.ServiceListPackageGrid.getGrid();
			var recordSelect = grid.getSelectionModel().getSelected();
			if (record.get('ServiceListPackage_IsNotSend') == 2 && recordSelect.id == record.id) {
				this.ServiceListPackageGrid.getAction('toggleIsNotSendPackage').setText(langs('Снять запрет отправки'));
			} else {
				this.ServiceListPackageGrid.getAction('toggleIsNotSendPackage').setText(langs('Не отправлять повторно'));
			}
		}.createDelegate(this);
		
		this.toggleIsNotSendPackage = function(id) {
			var that = this;
			var grid = this.ServiceListPackageGrid.getGrid();
			var record = grid.getStore().getById(id);
			var params = {};
			
			if (!record) return;
			
			var oldValue = record.get('ServiceListPackage_IsNotSend');
			var newValue = (oldValue == 2) ? 1 : 2;
			
			params.ServiceListPackage_id = id;
			params.ServiceListPackage_IsNotSend = newValue;
			
			Ext.Ajax.request({
				url: '/?c=ServiceList&m=setServiceListPackageIsNotSend',
				params: params,
				success: function() {
					record.set('ServiceListPackage_IsNotSend', newValue);
					record.commit();
					that.changeTexttoggleIsNotSendPackage(record);
				},
				failue: function() {
					record.set('ServiceListPackage_IsNotSend', oldValue);
					record.commit();
					that.changeTexttoggleIsNotSendPackage(record);
				}
			});
		}.createDelegate(this);
		
		var packageDataShowTpl = new Ext.XTemplate([
			'<span class="fake-link" onclick="Ext.Msg.alert(\'Данные\', \'{data}\');">Показать</span>'
		]);
		
		var packageDataRenderer = function(value) {
			if (Ext.isEmpty(value)) return '';
			return packageDataShowTpl.apply({data: value.replace(/"/g,'&quot;')});
		};

		this.ServiceListPackageGrid = new sw.Promed.ViewFrame({
			id: 'SLLW_ServiceListPackageGrid',
			dataUrl: '/?c=ServiceList&m=loadServiceListPackageGrid',
			border: false,
			autoLoadData: false,
			paging: true,
			region: 'south',
			root: 'data',
			totalProperty: 'totalCount',
			stringfields: [
				{name: 'ServiceListPackage_id', type: 'int', key: true},
				{name: 'ServiceListDetailLog_File', type: 'string', hidden: true},
				{name: 'PackageRouteType_SysNick', type: 'string', hidden: true},
				{name: 'PackageStatus_SysNick', type: 'string', hidden: true},
				{name: 'ServiceListPackage_IsNotSend', type: 'checkbox', header: langs('Не отправлять пакет'), width: 130},
				{name: 'ServiceListPackage_insDT', header: langs('Дата/время'), type: 'datetimesec', width: 130},
				{name: 'ServiceListPackage_GUID', type: 'string', header: 'Идентификатор пакета', width: 250},
				{name: 'ServiceListPackageType_Description', header: 'Тип пакета', type: 'string', id: 'autoexpand'},
				{name: 'ServiceListProcDataType_Name', header: 'Операция', type: 'string', width: 100},
				{name: 'PackageStatus_Name', header: 'Статус пакета', type: 'string', width: 140},
				{name: 'ServiceListProcDataType_Result', header: 'Результат', type: 'string', width: 250, renderer: function(v, p, record) {
					if (v == null) return '';

					var height = '50px';
					if(v.length == null || v.length < 200) {
						height = 'auto';
					}
					return ('<div ' +
						'onclick="this.style.height = (this.style.height == \'auto\' ? \'50px\' : \'auto\')" ' +
						'style="white-space:pre-wrap; height: ' + height + '; overflow: hidden;" ' +
						'title="Нажмите, чтобы раскрыть"' +
						'>' + v +
					'</div>');
				}},
				{name: 'ServiceListDetailLog_Message', header: 'Сообщение', type: 'string', width: 350, renderer: function(v, p, record) {
					if (v == null) return '';

					var height = '50px';
					if(v.length == null || v.length < 200) {
						height = 'auto';
					}
					return ('<div ' +
						'onclick="this.style.height = (this.style.height == \'auto\' ? \'50px\' : \'auto\')" ' +
						'style="white-space:pre-wrap; height: ' + height + '; overflow: hidden;" ' +
						'title="Нажмите, чтобы раскрыть"' +
						'>' + v +
					'</div>');
				}}, 
				{name: 'RequestData', header: 'Данные пакета', width: 100, renderer: packageDataRenderer},
				{name: 'ResponseData', header: 'Ответ', width: 100, renderer: packageDataRenderer}
			],
			actions: [
				{name:'action_add', hidden: true},
				{name:'action_edit', hidden: true},
				{name:'action_view', hidden: true},
				{name:'action_refresh', handler: function() {
					var grid = this.ServiceListPackageGrid;
					if (!grid.getGrid().getStore().baseParams.ServiceListLog_id) {
						return false;
					}
					grid.refreshRecords(null,0);
				}.createDelegate(this)},
				{name:'action_delete', hidden: true}
			],
			listeners: {
				'show': function(panel) {
					var width = panel.el.getWidth();
					this.ServiceListDetailLogGrid.setWidth(width);
				}.createDelegate(this)
			},
			onRowSelect: function(sm, index, record) {
				if (record.get('ServiceListPackage_id') && record.get('PackageRouteType_SysNick') == 'Export') {
					this.ServiceListPackageGrid.getAction('toggleIsNotSendPackage').enable();
				} else {
					this.ServiceListPackageGrid.getAction('toggleIsNotSendPackage').disable();
				}

				this.changeTexttoggleIsNotSendPackage(record);

			}.createDelegate(this),
			onDblClick: function(grid, number, object){
				
				if (!win.ServiceList_Code.inlist([85,86,113])) return false;
				
				var record = grid.getSelectionModel().getSelected();
				if (!record || !record.get('ServiceListPackage_id')) return false;
				
				if (win.ServiceList_Code == 15 ) {
					win.ServiceListPackageGrid.hide();
					win.ServiceListPackageDetailGrid.show();
					win.detailCardPanel.doLayout();
					
					var params = {
						ServiceListLog_id: grid.getStore().baseParams.ServiceListLog_id,
						ServiceListPackage_id: record.get('ServiceListPackage_id'),
						start: 0
					};
					
					win.ServiceListPackageDetailGrid.loadData({params: params, globalFilters: params});
				} else if (!!record.get('ServiceListDetailLog_File')) {
					window.open(record.get('ServiceListDetailLog_File'), '_blank');
				}
			}
		});

		this.ServiceListPackageDetailGrid = new sw.Promed.ViewFrame({
			id: 'SLLW_ServiceListPackageDetailGrid',
			dataUrl: '/?c=ServiceList&m=loadServiceListDetailLogGrid',
			border: false,
			autoLoadData: false,
			paging: true,
			region: 'south',
			root: 'data',
			totalProperty: 'totalCount',
			stringfields: [
				{name: 'ServiceListDetailLog_id', type: 'int', header: 'ID', key: true},
				{name: 'ServiceListPackage_GUID', type: 'string', header: 'Идентификатор пакета', width: 250},
				{name: 'ServiceListDetailLog_Message', header: langs('Результат'),  id: 'autoexpand', renderer: function(v, p, record) {
					if (v == null) return '';

					var height = '50px';
					if(v.length == null || v.length < 300) {
						height = 'auto';
					}
					return ('<div ' +
						'onclick="this.style.height = (this.style.height == \'auto\' ? \'50px\' : \'auto\')" ' +
						'style="white-space:pre-wrap; height: ' + height + '; overflow: hidden;" ' +
						'title="Нажмите, чтобы раскрыть"' +
						'>' + v +
					'</div>');
				}}
			],
			actions: [
				{name:'action_add', hidden: true},
				{name:'action_edit', hidden: true},
				{name:'action_view', hidden: true},
				{name:'action_refresh', hidden: true},
				{name:'action_delete', hidden: true}
			],
			listeners: {
				'show': function(panel) {
					var width = panel.el.getWidth();
					this.ServiceListDetailLogGrid.setWidth(width);
				}.createDelegate(this)
			}
		});

		this.detailCardPanel = new Ext.Panel({
			layout: 'card',
			activeItem: 0,
			region: 'south',
			border: false,
			style: 'border-top: 1px solid #99bbe8; border-bottom: 1px solid #99bbe8;',
			title: langs('Детальный лог работы сервисов'),
			height: 360,
			listeners: {
				'resize': function(panel, width, height) {
					var val = this.ServiceListDetailLogFilter.isVisible() ? 57 : 28;
					this.ServiceListDetailLogGrid.setHeight(height-val);
					this.ServiceListPackageGrid.setHeight(height-val);
					this.ServiceListPackageDetailGrid.setHeight(height-val);
				}.createDelegate(this)
			},
			items: [{
				layout: 'form',
				autoScroll: true,
				border: false,
				items: [
					this.ServiceListDetailLogFilter,
					this.ServiceListDetailLogGrid,
					this.ServiceListPackageGrid,
					this.ServiceListPackageDetailGrid
				]
			}, {
				layout: 'border',
				border: false,
				items: [
					this.FRMOSessionHistGrid
				]
			}]
		});

		Ext.apply(this,{
			layout: 'border',

			buttons: [
				{
					text: '-'
				},
				HelpButton(this),
				{
					handler: function()
					{
						this.hide();
					}.createDelegate(this),
					iconCls: 'cancel16',
					text: BTN_FRMCLOSE
				}
			],
			items: [
				{
					id: 'SLLW_ServiceListPanel',
					layout: 'border',
					region: 'west',
					border: false,
					width: 380,
					items: [
						this.ServiceListGrid,
						this.ServiceListPackageTypeGrid
					]
				},
				{
					id: 'SLLW_ServiceListLogPanel',
					layout: 'border',
					region: 'center',
					border: false,
					defaults: {
						split: true
					},
					items: [
						{
							layout: 'border',
							region: 'center',
							border: false,
							style: 'border-top: 1px solid #99bbe8;',
							title: langs('Лог запусков выбранного сервиса'),
							items: [
								this.ServiceListLogFilter,
								this.ServiceListLogGrid
							]
						}, win.detailCardPanel
					]
				}
			]
		});

		if(getRegionNick() == 'vologda'){
			var location = window.location.hostname.split('.');
			this.urlServiceFixingErrors = (location && location.length >2 && location[1] == 'swn' && location[2] == 'local') ? 'http://test-api2-fer.rt-eu.ru/concentrator_web/' : 'http://fer-concentrator.egisz.rosminzdrav.ru/concentrator_web/audit.htm';
		}
		sw.Promed.swServiceListLogWindow.superclass.initComponent.apply(this, arguments);
	}
});
