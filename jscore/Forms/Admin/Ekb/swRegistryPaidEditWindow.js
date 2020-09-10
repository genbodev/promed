/**
 * swRegistryPaidEditWindow - окно редактирования отметки об оплате случаев в реестре
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2013 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			05.12.2013
 */

/*NO PARSE JSON*/

sw.Promed.swRegistryPaidEditWindow = Ext.extend(sw.Promed.BaseForm, {
	//autoHeight: true,
	id: 'swRegistryPaidEditWindow',
	width: 650,
	//height: 450,
	callback: Ext.emptyFn,
	maximizable: false,
	maximized: true,
	modal: true,
	objectSrc: '/jscore/Forms/Admin/swRegistryPaidEditWindow.js',
	title: 'Отметки об оплате случаев',
	layout: 'border',

	Registry_id: null,

	openRegistryDataEditWindow: function() {
		var win = this;

		if (win.RegistryStatus_id == 2) {
			return false;
		}

		var grid = this.GridPanel.getGrid();

		var record = grid.getSelectionModel().getSelected();
		if (!record || Ext.isEmpty(record.get('Evn_id'))) {
			return false;
		}

		var params = new Object();
		params.callback = function(data) {
			if ( typeof data != 'object' || typeof data.RegistryData != 'object' ) {
				return false;
			}
			data.RegistryData.RecordStatus_Code = 0;

			var record = grid.getStore().getById(data.RegistryData.Evn_id);

			if ( record.get('RecordStatus_Code') == 1 ) {
				data.RegistryData.RecordStatus_Code = 2;
			}

			var grid_fields = new Array();

			grid.getStore().fields.eachKey(function(key, item) {
				if (data.RegistryData[key]!=undefined) {
					grid_fields.push(key);
				}
			});

			for ( i = 0; i < grid_fields.length; i++ ) {
				record.set(grid_fields[i], data.RegistryData[grid_fields[i]]);
			}
			record.set('RegistryData_IsPaid', 1);
			record.commit();

			// также ищем и обновляем запись в хранилище
			win.data.forEach(function(rec) {
				if ((rec.Evn_id) == record.get('Evn_id')) {
					rec.RegistryData_IsPaid = 1;

					for ( i = 0; i < grid_fields.length; i++ ) {
						rec[grid_fields[i]] = data.RegistryData[grid_fields[i]];
					}
				}
			});
		};
		params.formParams = record.data;

		getWnd('swRegistryDataEditWindow').show(params);
	},

	doSave: function()
	{
		var win = this;

		var params = new Object();
		var RegistryDataPaid = [];

		if ( win.data.length > 0 ) {
			var error = '';
			for(var key in win.data) {
				var record = win.data[key];
				if (record.Evn_id) {
					var is_paid = 1;
					if (record.RegistryData_IsPaid == 2 && record.RegistryErrorClass_id != 1) {
						is_paid = 2;
					}
					if ( Ext.isEmpty(record.RegistryErrorType_Code) && is_paid == 1 ) {
						if (win.RegistryStatus_id == 2) {
							// При сохранении на все случаи с отметкой об не оплате, у которых ещё нет ошибки, добавляются ошибки с кодом «5.2.2.».
						} else {
							error = 'Обнаружен случай, отмеченный как неоплаченный, у которого не указан код ошибки ТФОМС';
							break;
						}
					}
					RegistryDataPaid.push({
						Evn_id: record.Evn_id,
						RecordStatus_Code: record.RecordStatus_Code,
						RegistryErrorTFOMS_id: record.RegistryErrorTFOMS_id,
						Person_FIO: record.Person_FIO,
						RegistryData_EvnNum: record.RegistryData_EvnNum,
						Registry_xmlExportFile: record.Registry_xmlExportFile,
						RegistryErrorType_Code: record.RegistryErrorType_Code,
						RegistryData_IsPaid: is_paid
					});
				}
			}
			if ( !Ext.isEmpty(error) ) {
				sw.swMsg.alert('Ошибка', error);
				return false;
			}
		} else {
			//log('Нет случаев');
			win.callback();
			win.hide();
		}
		params.RegistryDataPaid = Ext.util.JSON.encode(RegistryDataPaid);
		params.Registry_id = this.Registry_id;
		params.RegistryStatus_id = this.RegistryStatus_id;

		//log(params);return false;
		win.getLoadMask("Подождите, идет сохранение...").show();

		Ext.Ajax.request({
			url: '/?c=Registry&m=setRegistryDataPaidFromJSON',
			params: params,
			failure: function(response, options) {
				win.getLoadMask().hide();
				sw.swMsg.alert('Ошибка', 'Ошибка при выполнении запроса к серверу');
			},
			success: function(response, options)
			{
				win.getLoadMask().hide();

				var response_obj = Ext.util.JSON.decode(response.responseText);

				if ( response_obj.success == false || !Ext.isEmpty(response_obj.Error_Msg) ) {
					sw.swMsg.alert('Ошибка', response_obj.Error_Msg ? response_obj.Error_Msg : 'Ошибка при выполнении запроса к серверу');
				}
				else {
					win.callback();
					win.hide();
				}
			}
		});
	},

	doSearch: function(clear)
	{
		var base_form = this.FiltersPanel.getForm();
		var params = new Object();
		if (clear == true) {
			base_form.reset();
		} else {
			params = base_form.getValues();
		}

		params.start = 0;
		params.limit = 100;
		params.Registry_id = this.Registry_id;
		params.RegistryType_id = this.RegistryType_id;
		params.filterRecords = base_form.findField('filterRecords').getValue();
		this.GridPanel.loadData({globalFilters: params});
	},

	checkRenderer: function(v, p, record) {
		var id = record.get('Evn_id');
		var value = 'value="'+id+'"';
		var checked = record.get('RegistryData_IsPaid')!=2 ? ' checked="checked"' : '';
		var onclick = 'onClick="getWnd(\'swRegistryPaidEditWindow\').checkOne(this.value);"';
		var disabled = (record.get('RegistryErrorClass_id') != 1?'':'disabled="disabled"');

		return '<input type="checkbox" '+value+' '+checked+' '+onclick+' '+disabled+'>';
	},

	checkOne: function(id) {
		var win = this;
		var grid = this.GridPanel.getGrid();

		var record = grid.getStore().getAt(grid.getStore().findBy(function(rec) { return rec.get('Evn_id') == id; }));
		if (record) {
			var newVal = (record.get('RegistryData_IsPaid')==2)?1:2;
			record.set('RegistryData_IsPaid', newVal);
			record.commit();

			// также ищем и обновляем запись в хранилище
			win.data.forEach(function(rec) {
				if ((rec.Evn_id) == record.get('Evn_id')) {
					rec.RegistryData_IsPaid = newVal;
				}
			});
		}
	},

	checkAllPaidCheckbox: function()
	{
		var win = this;
		this.GridPanel.getGrid().getStore().each(function(record){
			record.set('RegistryData_IsPaid', 1);
			record.commit();
		});

		// ищем и обновляем записи в хранилище
		win.data.forEach(function(rec) {
			rec.RegistryData_IsPaid = 1;
		});
	},

	resetPaidCheckbox: function()
	{
		var win = this;
		this.GridPanel.getGrid().getStore().each(function(record){
			if (Ext.isEmpty(record.get('RegistryErrorType_Code'))) {
				record.set('RegistryData_IsPaid', 2);
				record.commit();
			}
		});

		// ищем и обновляем записи в хранилище
		win.data.forEach(function(rec) {
			rec.RegistryData_IsPaid = 2;
		});
	},

	show: function()
	{
		sw.Promed.swRegistryPaidEditWindow.superclass.show.apply(this, arguments);

		var win = this;

		if (!arguments[0] || !arguments[0].Registry_id || !arguments[0].RegistryType_id) {
			sw.swMsg.alert('Сообщение', 'Неверные параметры', function() { this.hide(); }.createDelegate(this) );
			return false;
		}
		this.Registry_id = arguments[0].Registry_id;
		this.RegistryType_id = arguments[0].RegistryType_id;
		this.RegistryStatus_id = arguments[0].RegistryStatus_id;

		if ( arguments[0].callback ) {
			this.callback = arguments[0].callback;
		}

		// 1. грузим всё в массив
		win.data = [];
		win.getLoadMask('Загрузка данных').show();
		Ext.Ajax.request({
			url: '/?c=Registry&m=loadRegistryDataPaid',
			params: {
				Registry_id: win.Registry_id,
				RegistryType_id: win.RegistryType_id
			},
			callback: function(opt, success, response) {
				win.getLoadMask().hide();
				if (success && response.responseText != '')
				{
					win.data  = Ext.util.JSON.decode(response.responseText);
					// 2. грид используем с временным хранилищем (грузим первые сто записей)
					var response_obj = new Object();
					response_obj.totalCount = win.data.length;
					response_obj.data = win.data.slice(0, 100);
					win.GridPanel.getGrid().getStore().loadData(response_obj);
				}
			}
		});
	},
	loadRecords: function(params) {
		var win = this;

		win.GridPanel.getGrid().getStore().baseParams.start = params.start;
		win.GridPanel.getGrid().getStore().baseParams.limit = params.limit;

		var response_obj = new Object();
		var result = new Object();

		result.data = win.data.filter(function(el){
			resp = true;

			if (!Ext.isEmpty(params.Person_SurName)) {
				var expr = new RegExp(params.Person_SurName, 'ig');
				resp = resp && String(el.Person_SurName).match(expr);
			}

			if (!Ext.isEmpty(params.Person_FirName)) {
				var expr = new RegExp(params.Person_FirName, 'ig');
				resp = resp && String(el.Person_FirName).match(expr);
			}

			if (!Ext.isEmpty(params.Person_SecName)) {
				var expr = new RegExp(params.Person_SecName, 'ig');
				resp = resp && String(el.Person_SecName).match(expr);
			}

			if (!Ext.isEmpty(params.Polis_Num)) {
				var expr = new RegExp(params.Polis_Num, 'ig');
				resp = resp && String(el.Polis_Num).match(expr);
			}

			if (!Ext.isEmpty(params.N_ZAP)) {
				resp = resp && String(el.RegistryData_EvnNum).match(params.N_ZAP);
			}

			if (!Ext.isEmpty(params.filterRecords)) {
				switch(params.filterRecords) {
					case 1:

					break;
					case 2:
						resp = resp && (el.RegistryData_IsPaid == 2);
					break;
					case 3:
						resp = resp && (el.RegistryData_IsPaid == 1);
					break;
				}
			}

			return resp;
		});

		response_obj.totalCount = result.data.length;
		response_obj.data = result.data.slice(params.start, params.start+params.limit);
		win.GridPanel.getGrid().getStore().loadData(response_obj);
	},
	initComponent: function()
	{
		var win = this;

		this.FiltersPanel = new Ext.form.FormPanel(
			{
				bodyStyle:'width:100%;background:#DFE8F6;padding:0px;',
				border: true,
				collapsible: false,
				region: 'north',
				height: 60,
				minSize: 30,
				maxSize: 30,
				layout: 'column',
				//title: 'Ввод',
				id: 'RPEW_FiltersPanel',
				keys:
					[{
						key: Ext.EventObject.ENTER,
						fn: function(e)
						{
							win.doSearch();
						},
						stopEvent: true
					}],
				items:
					[{
						layout: 'form',
						border: false,
						bodyStyle:'padding: 4px;background:#DFE8F6;',
						columnWidth: .30,
						labelWidth: 100,
						items:
							[{
								anchor: '100%',
								fieldLabel: 'Фамилия',
								id: '_Person_SurName',
								name: 'Person_SurName',
								xtype: 'textfieldpmw',
								tabIndex:win.firstTabIndex+10
							}, {
								anchor: '100%',
								fieldLabel: 'Номер полиса',
								id: 'RPEW_Polis_Num',
								name: 'Polis_Num',
								xtype: 'numberfield',
								tabIndex:win.firstTabIndex+13
							}]
						},
						{
							layout: 'form',
							border: false,
							bodyStyle:'padding: 4px;background:#DFE8F6;',
							columnWidth: .30,
							labelWidth: 140,
							items:
								[{
									anchor: '100%',
									fieldLabel: 'Имя',
									id: 'RPEW_Person_FirName',
									name: 'Person_FirName',
									xtype: 'textfieldpmw',
									tabIndex:win.firstTabIndex+11
								}, {
									anchor: '100%',
									fieldLabel: 'Номер записи (N_ZAP)',
									id: 'RPEW_N_ZAP',
									name: 'N_ZAP',
									xtype: 'numberfield',
									tabIndex:win.firstTabIndex+14
								}]
						},
						{
							layout: 'form',
							border: false,
							bodyStyle:'padding:4px;font-size: 12px;background:#DFE8F6;',
							columnWidth: .30,
							labelWidth: 60,
							items:
								[{
									anchor: '100%',
									fieldLabel: 'Отчество',
									id: 'RPEW_Person_SecName',
									name: 'Person_SecName',
									xtype: 'textfieldpmw',
									tabIndex:win.firstTabIndex+12
								}, {
									anchor: '100%',
									xtype: 'combo',
									id: 'RPEW_PfilterRecords',
									listWidth: 200,
									hideLabel: true,
									name: 'filterRecords',
									boxLabel: 'Все случаи',
									triggerAction: 'all',
									forceSelection: true,
									editable: false,
									store: [
										[1, 'Все случаи'],
										[2, 'Оплаченные случаи'],
										[3, 'Неоплаченные случаи']
									],
									allowBlank: false,
									value: 1,
									tabIndex:win.firstTabIndex+13
								}]
						},
						{
							layout: 'form',
							border: false,
							bodyStyle:'padding: 4px;background:#DFE8F6;',
							columnWidth: .1,
							items: [
								new Ext.Button({
									tooltip: BTN_FRMSEARCH_TIP,
									id: 'RPEW_BtnSearch',
									text: BTN_FRMSEARCH,
									icon: 'img/icons/search16.png',
									iconCls : 'x-btn-text',
									disabled: false,
									handler: function()
									{
										win.doSearch();
									}
								})
							]
						}]
			});

		this.GridPanel = new sw.Promed.ViewFrame(
			{
				id: 'RPEW_RegistryDataPaidGridPanel',
				title:'Реестр ОМС',
				object: 'RegistryData',
				region: 'center',
				dataUrl: '/?c=Registry&m=loadRegistryDataPaid',
				paging: true,
				root: 'data',
				totalProperty: 'totalCount',
				toolbar: false,
				autoLoadData: false,
				passPersonEvn: true,
				focusOnFirstLoad: false,
				useEmptyRecord: false,
				saveAtOnce: false,
				saveAllParams: false,
				stringfields:
				[
					{name: 'check', sortable: false, header: 'Случай не оплачен', width: 100, renderer: this.checkRenderer},
					{name: 'Evn_id', type: 'int', header: 'Evn_id', key: true, hidden:!isSuperAdmin()},
					{name: 'Person_id', type: 'int', header: 'Person_id', hidden:!isSuperAdmin()},
					{name: 'Evn_rid', hidden:true},
					{name: 'Registry_id', type: 'int', hidden:true},
					{name: 'EvnClass_id', type: 'int', hidden:true},
					{name: 'DispClass_id', type: 'int', hidden:true},
					{name: 'RegistryType_id', type: 'int', hidden:true},
					{name: 'Server_id', type: 'int', hidden:true},
					{name: 'RecordStatus_Code', type: 'int', hidden:true},

					{name: 'PersonEvn_id', type: 'int', hidden:true},
					{name: 'IsRDL', type: 'int', hidden:true},
					{name: 'needReform', type: 'int', hidden:true},
					{name: 'isNoEdit', type: 'int', hidden:true},
					{name: 'RegistryErrorType_id', type: 'int', hidden:true},
					{name: 'RegistryErrorClass_id', type: 'int', hidden:true},
					{name: 'RegistryErrorTFOMS_id', type: 'int', hidden:true},
					{name: 'EvnPL_NumCard', header: '№ талона', width: 60},
					{name: 'Registry_xmlExportFile', header: 'Имя файла', width: 80},
					{name: 'RegistryErrorType_Code', header: 'Код ошибки', width: 80},
					{name: 'RegistryData_EvnNum', header: '№ п/п (N_ZAP)', width: 80},
					{name: 'Person_SurName', hidden: true},
					{name: 'Person_FirName', hidden: true},
					{name: 'Person_SecName', hidden: true},
					{name: 'Polis_Num', hidden: true},
					{name: 'Person_FIO', id: 'autoexpand', header: 'ФИО пациента'},
					{name: 'Person_BirthDay', type: 'date', header: 'Дата рождения', width: 80},
					{name: 'Person_IsBDZ',  header: 'БДЗ', type: 'checkbox', width: 30},
					{name: 'LpuSection_Name', header: 'Отделение', width: 200},
					{name: 'MedPersonal_Fio', header: 'Врач', width: 200},
					{name: 'EvnVizitPL_setDate', type: 'date', header: 'Посещение', width: 80},
					{name: 'Evn_disDate', type: 'date', header: 'Выписка', width: 80},
					{name: 'RegistryData_Uet', header: 'К/д факт', width: 70},
					{name: 'RegistryData_KdPlan', header: 'К/д норматив', width: 70},
					{name: 'RegistryData_KdPay', header: 'К/д к оплате', width: 70},
					{name: 'RegistryData_Tariff', type: 'money', header: 'Тариф', width: 70},
					{name: 'RegistryData_ItogSum', type: 'money', header: 'Сумма к оплате', width: 90},
					{name: 'PayMedType_Code', header: 'Код способа оплаты', width: 90},
					{name: 'checkReform', header: '<img src="/img/grid/hourglass.gif" />', width: 35, renderer: sw.Promed.Format.waitColumn},
					{name: 'RegistryData_deleted', hidden:true},
					{name: 'RegistryData_IsPaid', type: 'int', hidden:true}
				],
				actions: [
					{ name: 'action_add', hidden: true, disabled: true },
					{ name: 'action_edit', hidden: true, disabled: true },
					{ name: 'action_view', hidden: true, disabled: true },
					{ name: 'action_delete', hidden: true, disabled: true },
					{ name: 'action_refresh', hidden: true, disabled: true }
				],
				onDblClick: function()
				{
					Ext.getCmp('swRegistryPaidEditWindow').openRegistryDataEditWindow();
				},
				onEnter: function()
				{
					//Ext.getCmp('RegistryViewWindow').openForm(Ext.getCmp('RegistryViewWindow').DataGrid, {});

				},
				onLoadData: function() {
					// Учитываются только ошибки
					// https://redmine.swan.perm.ru/issues/42306
					this.getGrid().getStore().each(function(record) {
						if ( record.get('RegistryErrorClass_id') == 1 ) {
							record.set('RegistryData_IsPaid', 1);
							record.commit();

							// также ищем и обновляем запись в хранилище
							win.data.forEach(function(rec) {
								if ((rec.Evn_id) == record.get('Evn_id')) {
									rec.RegistryData_IsPaid = 1;
								}
							});
						}
					});
				},
				onRowSelect: function(sm,rowIdx,record)
				{
					//
				}.createDelegate(this)
			});

		this.GridPanel.getGrid().getStore().addListener('beforeload', function(store, options) {
			win.loadRecords(options.params);
			return false;
		});

		Ext.apply(this, {
			items: [
				this.FiltersPanel,
				this.GridPanel
			],
			buttons: [{
				handler: function() {
					this.doSave(false);
				}.createDelegate(this),
				iconCls: 'save16',
				id: 'RPEW_SaveButton',
				text: BTN_FRMSAVE
			}, {
				handler: function() {
					this.checkAllPaidCheckbox();
				}.createDelegate(this),
				id: 'RPEW_CheackAllButton',
				text: 'Отметить все'
			}, {
				handler: function() {
					this.resetPaidCheckbox();
				}.createDelegate(this),
				id: 'RPEW_ResetButton',
				text: 'Сбросить все (без ошибок)'
			},
				'-',
				HelpButton(this, -1),
				{
					handler: function() {
						this.hide();
					}.createDelegate(this),
					iconCls: 'cancel16',
					id: 'RPEW_CancelButton',
					tabIndex: 2409,
					text: BTN_FRMCANCEL
				}]
		});

		sw.Promed.swRegistryPaidEditWindow.superclass.initComponent.apply(this, arguments);
	}
});