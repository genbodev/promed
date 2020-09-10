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
	title: lang['otmetki_ob_oplate_sluchaev'],
	layout: 'border',

	Registry_id: null,

	doSave: function()
	{
		var win = this;

		var params = new Object();
		var grid = this.GridPanel.getGrid();

		var RegistryDataPaid = [];

		if ( win.data.length > 0 ) {
			for(var key in win.data) {
				var record = win.data[key];
				if (record.Evn_id) {
					var is_paid = 1;
					if (record.RegistryData_IsPaid == 2 && record.Err_Count == 0) {
						is_paid = 2;
					}
					RegistryDataPaid.push({
						Evn_id: record.Evn_id,
						RegistryData_IsPaid: is_paid
					});
				}
			}
		} else {
			//log('Нет случаев');
			win.callback();
			win.hide();
		}

		params.RegistryDataPaid = Ext.util.JSON.encode(RegistryDataPaid);
		params.Registry_id = this.Registry_id;

		//log(params);return false;
		win.getLoadMask("Подождите, идет сохранение...").show();

		Ext.Ajax.request({
			url: '/?c=Registry&m=setRegistryDataPaidFromJSON',
			params: params,
			callback: function(options, success, response)
			{
				win.getLoadMask().hide();
				if (success)
				{
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
		params.RegistryType_id = 4;
		params.filterRecords = base_form.findField('filterRecords').getValue();

		this.GridPanel.loadData({globalFilters: params});
	},

	checkAllPaidCheckbox: function()
	{
		var win = this;
		this.GridPanel.getGrid().getStore().each(function(record){
			record.set('RegistryData_IsPaid', 1);
			record.set('RecordPaid', 'true');
			record.commit();

			// также ищем и обновляем запись в хранилище
			win.data.forEach(function(rec) {
				if ((rec.Evn_id) == record.get('Evn_id')) {
					rec.RegistryData_IsPaid = 1;
					rec.RecordPaid = 'true';
				}
			});
		});
	},

	resetPaidCheckbox: function()
	{
		var win = this;
		this.GridPanel.getGrid().getStore().each(function(record){
			if (record.get('Err_Count') == 0) {
				record.set('RegistryData_IsPaid', 2);
				record.set('RecordPaid', false);
				record.commit();

				// также ищем и обновляем запись в хранилище
				win.data.forEach(function(rec) {
					if ((rec.Evn_id) == record.get('Evn_id')) {
						rec.RegistryData_IsPaid = 2;
						rec.RecordPaid = false;
					}

					return false;
				});
			}
		});
	},

	show: function()
	{
		sw.Promed.swRegistryPaidEditWindow.superclass.show.apply(this, arguments);

		var win = this;

		if (!arguments[0] || !arguments[0].Registry_id) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() { this.hide(); }.createDelegate(this) );
			return false;
		}
		this.Registry_id = arguments[0].Registry_id;
		this.RegistryType_id = arguments[0].RegistryType_id;

		if ( arguments[0].callback ) {
			this.callback = arguments[0].callback;
		}

		// 1. грузим всё в массив
		win.data = [];
		win.getLoadMask(lang['zagruzka_dannyih']).show();
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
				resp = resp && String(el.Person_SurName).match(params.Person_SurName);
			}

			if (!Ext.isEmpty(params.Person_FirName)) {
				resp = resp && String(el.Person_FirName).match(params.Person_FirName);
			}

			if (!Ext.isEmpty(params.Person_SecName)) {
				resp = resp && String(el.Person_SecName).match(params.Person_SecName);
			}

			if (!Ext.isEmpty(params.Polis_Num)) {
				resp = resp && String(el.Polis_Num).match(params.Person_SecName);
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
				height: 30,
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
						columnWidth: .20,
						labelWidth: 60,
						items:
							[{
								anchor: '100%',
								fieldLabel: lang['familiya'],
								id: '_Person_SurName',
								name: 'Person_SurName',
								xtype: 'textfieldpmw',
								tabIndex:win.firstTabIndex+10
							}]
					},
						{
							layout: 'form',
							border: false,
							bodyStyle:'padding: 4px;background:#DFE8F6;',
							columnWidth: .15,
							labelWidth: 30,
							items:
								[{
									anchor: '100%',
									fieldLabel: lang['imya'],
									id: 'RPEW_Person_FirName',
									name: 'Person_FirName',
									xtype: 'textfieldpmw',
									tabIndex:win.firstTabIndex+11
								}]
						},
						{
							layout: 'form',
							border: false,
							bodyStyle:'padding:4px;font-size: 12px;background:#DFE8F6;',
							columnWidth: .20,
							labelWidth: 60,
							items:
								[{
									anchor: '100%',
									fieldLabel: lang['otchestvo'],
									id: 'RPEW_Person_SecName',
									name: 'Person_SecName',
									xtype: 'textfieldpmw',
									tabIndex:win.firstTabIndex+12
								}]
						},
						{
							layout: 'form',
							border: false,
							hidden: !isAdmin,
							bodyStyle:'padding:4px;font-size: 12px;background:#DFE8F6;',
							columnWidth: .15,
							labelWidth: 90,
							items:
								[{
									anchor: '100%',
									fieldLabel: lang['nomer_polisa'],
									id: 'RPEW_Polis_Num',
									name: 'Polis_Num',
									xtype: 'numberfield',
									tabIndex:win.firstTabIndex+13
								}]
						},
						{
							layout: 'form',
							border: false,
							bodyStyle:'padding:4px;font-size: 12px;background:#DFE8F6;',
							columnWidth: .15,
							labelWidth: 90,
							items:
								[{
									anchor: '100%',
									xtype: 'combo',
									id: 'RPEW_PfilterRecords',
									listWidth: 200,
									hideLabel: true,
									name: 'filterRecords',
									boxLabel: lang['vse_sluchai'],
									triggerAction: 'all',
									forceSelection: true,
									editable: false,
									store: [
										[1, lang['vse_sluchai']],
										[2, lang['oplachennyie_sluchai']],
										[3, lang['neoplachennyie_sluchai']]
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
				id: 'RPEW_DataGridPanel',
				title:lang['reestr_oms'],
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
						{name: 'RecordPaid', sortable: false, type: 'checkcolumnedit', isparams: true, header: lang['sluchay_ne_oplachen'], width: 180},
						{name: 'Evn_id', type: 'int', header: 'Evn_id', key: true, hidden:!isSuperAdmin()},
						{name: 'Person_id', type: 'int', header: 'Person_id', hidden:!isSuperAdmin()},
						{name: 'Evn_rid', hidden:true},
						{name: 'Registry_id', type: 'int', hidden:true},
						{name: 'EvnClass_id', type: 'int', hidden:true},
						{name: 'RegistryType_id', type: 'int', hidden:true},
						{name: 'Server_id', type: 'int', hidden:true},

						{name: 'PersonEvn_id', type: 'int', hidden:true},
						{name: 'IsRDL', type: 'int', hidden:true},
						{name: 'needReform', type: 'int', hidden:true},
						{name: 'isNoEdit', type: 'int', hidden:true},
						{name: 'EvnPL_NumCard', header: lang['№_talona'], width: 60},
						{name: 'Person_FIO', id: 'autoexpand', header: lang['fio_patsienta']},
						{name: 'Person_BirthDay', type: 'date', header: lang['data_rojdeniya'], width: 80},
						{name: 'Person_IsBDZ',  header: lang['bdz'], type: 'checkbox', width: 30},
						{name: 'LpuSection_name', header: lang['otdelenie'], width: 200},
						{name: 'MedPersonal_Fio', header: lang['vrach'], width: 200},
						{name: 'EvnVizitPL_setDate', type: 'date', header: lang['poseschenie'], width: 80},
						{name: 'Evn_disDate', type: 'date', header: lang['vyipiska'], width: 80},
						{name: 'RegistryData_Uet', header: lang['k_d_fakt'], width: 70},
						{name: 'RegistryData_KdPlan', header: lang['k_d_normativ'], width: 70},
						{name: 'RegistryData_KdPay', header: lang['k_d_k_oplate'], width: 70},
						{name: 'RegistryData_Tariff', type: 'money', header: lang['tarif'], width: 70},
						{name: 'RegistryData_ItogSum', type: 'money', header: lang['summa_k_oplate'], width: 90},
						{name: 'PayMedType_Code', header: lang['kod_sposoba_oplatyi'], width: 90},
						{name: 'checkReform', header: '<img src="/img/grid/hourglass.gif" />', width: 35, renderer: sw.Promed.Format.waitColumn},
						//{name: 'timeReform', type: 'datetimesec', header: 'Изменена', width: 100},
						{name: 'Err_Count', hidden:true},
						{name: 'RegistryData_deleted', hidden:true},
						{name: 'RegistryData_IsPaid', type: 'int', hidden:true}
					],
				onDblClick: function()
				{
					//Ext.getCmp('RegistryViewWindow').openForm(Ext.getCmp('RegistryViewWindow').DataGrid, {});
				},
				onEnter: function()
				{
					//Ext.getCmp('RegistryViewWindow').openForm(Ext.getCmp('RegistryViewWindow').DataGrid, {});

				},
				onLoadData: function() {
					// Проставить правильно галки
					this.getGrid().getStore().each(function(record) {
						if ( record.get('RegistryData_IsPaid') == 1 ) {
							record.set('RecordPaid', 'on');
							record.commit();

							// также ищем и обновляем запись в хранилище
							win.data.forEach(function(rec) {
								if ((rec.Evn_id) == record.get('Evn_id')) {
									rec.RecordPaid = 'on';
								}
							});
						}
					});
				},
				onRowSelect: function(sm,rowIdx,record)
				{
					//
				}.createDelegate(this),
				onAfterEdit: function(o) {
					if (o && o.field && o.field == 'RecordPaid') {
						if (o.record.get('RegistryData_IsPaid') == 1) {
							o.record.set('RegistryData_IsPaid', 2);
						}
						else {
							o.record.set('RegistryData_IsPaid', 1);
						}

						o.record.commit();

						// также ищем и обновляем запись в хранилище
						win.data.forEach(function(rec) {
							if ((rec.Evn_id) == o.record.get('Evn_id')) {
								rec.RegistryData_IsPaid = o.record.get('RegistryData_IsPaid');
							}
						});
					}
				}
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
				id: 'FSEW_SaveButton',
				text: BTN_FRMSAVE
			}, {
				handler: function() {
					this.checkAllPaidCheckbox();
				}.createDelegate(this),
				id: 'FSEW_CheackAllButton',
				text: lang['otmetit_vse']
			}, {
				handler: function() {
					this.resetPaidCheckbox();
				}.createDelegate(this),
				id: 'FSEW_ResetButton',
				text: lang['sbrosit_vse']
			},
			'-',
			HelpButton(this, -1),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				id: 'FSEW_CancelButton',
				tabIndex: 2409,
				text: BTN_FRMCANCEL
			}]
		});

		sw.Promed.swRegistryPaidEditWindow.superclass.initComponent.apply(this, arguments);
	}
});