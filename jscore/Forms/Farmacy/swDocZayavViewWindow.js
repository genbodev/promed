/**
 * swDocZayavViewWindow - окно просмотра заявок на медикаменты
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Farmacy
 * @access			public
 * @copyright		Copyright (c) 2013 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			26.12.2013
 */

/*NO PARSE JSON*/

sw.Promed.swDocZayavViewWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swDocZayavViewWindow',
	width: 800,
	height: 600,
	callback: Ext.emptyFn,
	layout: 'border',
	maximized: true,
	title: lang['zayavki_na_medikamentyi'],

	Contragent_tid: null,

	openRecordEditWindow: function(action, gridCmp) {
		var wnd = this;
		var grid = gridCmp.getGrid();
		var params = new Object();
		if (action.inlist(['edit','view'])) {
			var record = grid.getSelectionModel().getSelected();
			params.formParams = {DocumentUc_id: record.get('DocumentUc_id')};
		}

		if (wnd.Contagent_tid) {
			params.Contragent_tid = wnd.Contagent_tid;
		}
		params.action = action;
		params.callback = function() {
			gridCmp.ViewActions.action_refresh.execute();
		};
		getWnd(gridCmp.editformclassname).show(params);
	},

	openStatusHistoryWindow: function()
	{
		var record = this.GridPanel.getGrid().getSelectionModel().getSelected();

		getWnd('swDrugDocumentStatusHistoryWindow').show({DocumentUc_id: record.get('DocumentUc_id')});
	},

	statusLoading: false,
	allowedStatusList: {},
	getStatusHistoryMenu: function(record){
		if (this.statusLoading == true || !record) {
			return;
		}

		this.statusLoading = true;

		var wnd = this;

		var params = {
			DocumentUc_id: record.get('DocumentUc_id')
		};

		Ext.Ajax.request({
			url: '/?c=Farmacy&m=getAllowedDocZayavStatusConfig',
			params: params,
			success: function(response) {
				var response_obj = Ext.util.JSON.decode(response.responseText);
				var menuCmp = Ext.getCmp('status_change_menu');

				var data = response_obj.data;
				var menu_item_id = '';

				var nullStatusRec = {
					DrugDocumentStatus_id: '',
					DrugDocumentStatus_Code: '',
					DrugDocumentStatus_Name: lang['net_statusa']
				};

				menuCmp.menu.removeAll();

				if (response_obj.disabled == true) {
					//wnd.GridPanel.ViewActions.actions.items[0].menu.items.items[0].disable();

					menuCmp.menu.add({
						text: lang['net_dostupnyih_statusov'],
						id: 'StatusMenu_empty',
						disabled: true
					});
				} else {
					if (response_obj.allowBlank == true && record.get('DrugDocumentStatus_id') > 0) {
						menuCmp.menu.add({
							text: nullStatusRec.DrugDocumentStatus_Name,
							id: 'StatusMenu_null',
							handler: function() {
								wnd.setDocumentUcStatus(record, nullStatusRec);
							}
						});
					}

					for (var i=0; i<data.length; i++) {
						if (record.get('DrugDocumentStatus_id') == data[i].DrugDocumentStatus_id) {
							continue;
						}

						menu_item_id = 'StatusMenu_'+data[i].DrugDocumentStatus_id;
						wnd.allowedStatusList[menu_item_id] = data[i];

						menuCmp.menu.add({
							text: data[i].DrugDocumentStatus_Name,
							id: menu_item_id,
							handler: function() {
								wnd.setDocumentUcStatus(record, wnd.allowedStatusList[this.id]);
							}
						});
					}

					wnd.GridPanel.ViewActions.actions.items[0].menu.items.items[0].enable();
				}

				wnd.statusLoading = false;
			},
			failure: function(response) {
				wnd.statusLoading = false;
			}
		});
	},

	setDocumentUcStatus: function (record, status_obj) {
		var wnd = this;

		var params = {
			DocumentUc_id: record.get('DocumentUc_id'),
			DrugDocumentStatus_id: status_obj.DrugDocumentStatus_id
		};

		Ext.Ajax.request({
			url: '/?c=Farmacy&m=setDocumentUcStatus',
			params: params,
			callback: function(options, success, response) {
				if (success) {
					record.set('DrugDocumentStatus_id', status_obj.DrugDocumentStatus_id);
					record.set('DrugDocumentStatus_Code', status_obj.DrugDocumentStatus_Code);
					record.set('DrugDocumentStatus_Name', status_obj.DrugDocumentStatus_Name);
					record.commit();

					wnd.getStatusHistoryMenu(record);
				}
			}
		});
	},

	doSearch: function(clear)
	{
		var wnd = this;
		var base_form = wnd.FilterPanel.getForm();
		if (clear) {
			base_form.reset();
			if (wnd.Contagent_tid) {
				base_form.findField('Contragent_tid').setValue(wnd.Contagent_tid);
				base_form.findField('Contragent_tid').disable();
			}
			loadContragent(wnd, 'DZVW_Contragent_tid', null);
			loadContragent(wnd, 'DZVW_Contragent_sid', null);
		}
		var params = base_form.getValues();
		params.Contragent_tid = base_form.findField('Contragent_tid').getValue();

		wnd.GridPanel.loadData({globalFilters: params});
	},

	printForm16AP: function() {
		var record = this.GridPanel.getGrid().getSelectionModel().getSelected();
		var paramWhsDocumentUc = record.get('WhsDocumentUc_id');
		
		printBirt({
			'Report_FileName': 'ARM_DrugRequest_NEW.rptdesign',
			'Report_Params': '&paramWhsDocumentUc=' + paramWhsDocumentUc,
			'Report_Format': 'xls'
		});
	},

	show: function()
	{
		sw.Promed.swDocZayavViewWindow.superclass.show.apply(this, arguments);

		var wnd = this;
		wnd.statusLoading = false;

		var base_form = wnd.FilterPanel.getForm();

		if (arguments[0] && arguments[0].Contragent_tid) {
			this.Contagent_tid = arguments[0].Contragent_tid;
		}

		wnd.GridPanel.addActions({
			name: 'actions', key: 'actions', text:lang['deystviya'], menu: [
				new Ext.Action({
					id: 'status_change_menu',
					name: 'status_change',
					text: lang['statusyi_dokumenta'],
					tooltip: lang['statusyi_dokumenta'],
					menu: new Ext.menu.Menu()
				}),
				new Ext.Action({
					id: 'status_history_action',
					name:'status_history',
					text:lang['istoriya_statusov'],
					tooltip: lang['istoriya_statusov'],
					handler: function() {wnd.openStatusHistoryWindow();}
				}),
				new Ext.Action({
					id: 'print_form16ap_action',
					name:'print_form16ap',
					text:lang['pechat_nakladnoy'],
					tooltip: lang['pechat_nakladnoy_forma_16-ap'],
					handler: function() {wnd.printForm16AP();}
				})
			], tooltip: lang['deystviya'], iconCls : 'x-btn-text', icon: 'img/icons/actions16.png', handler: function() {}
		});

		base_form.findField('DrugDocumentClass_id').getStore().load();
		base_form.findField('DrugDocumentStatus_id').getStore().load({params: {DrugDocumentType_id: 9}});

		wnd.doSearch(true);
	},

	initComponent: function()
	{
		var wnd = this;

		wnd.FilterPanel = new Ext.form.FormPanel({
			autoHeight: true,
			//bodyStyle: 'padding: 5px',
			border: false,
			frame: true,
			labelAlign: 'right',
			labelWidth: 80,
			id: 'DZVW_FilterPanel',
			region: 'north',

			items: [{
				xtype: 'daterangefield',
				name: 'DocumentUc_insDT_Range',
				fieldLabel: lang['period'],
				plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
				width: 220
			}, {
				xtype: 'swdrugdocumentclasscombo',
				hiddenName: 'DrugDocumentClass_id',
				fieldLabel: lang['vid_zayavki'],
				width: 220
			}, {
				width: 500,
				fieldLabel: lang['zakazchik'],
				xtype: 'swcontragentcombo',
				id: 'DZVW_Contragent_tid',
				hiddenName: 'Contragent_tid'
			}, {
				width: 500,
				fieldLabel: lang['ispolnitel'],
				xtype: 'swcontragentcombo',
				id: 'DZVW_Contragent_sid',
				hiddenName: 'Contragent_sid'
			}, {
				xtype: 'swdrugdocumentstatuscombo',
				hiddenName: 'DrugDocumentStatus_id',
				fieldLabel: lang['status'],
				width: 220
			}, {
				layout: 'column',
				style: "margin-top: 5px",
				items: [{
					layout: 'form',
					items: [{
						style: "padding-left: 5px",
						xtype: 'button',
						id: 'DZVW_BtnSearch',
						text: lang['poisk'],
						iconCls: 'search16',
						handler: function() {
							wnd.doSearch();
						}
					}]
				}, {
					layout: 'form',
					items: [{
						style: "padding-left: 10px",
						xtype: 'button',
						id: 'DZVW_BtnClear',
						text: lang['sbros'],
						iconCls: 'resetsearch16',
						handler: function() {
							wnd.doSearch(true);
						}
					}]
				}]
			}]
		});

		wnd.GridPanel = new sw.Promed.ViewFrame({
			id: 'DZVW_DocumentUcGrid',
			region: 'center',
			dataUrl: '/?c=Farmacy&m=load&method=DocZayav',
			editformclassname: 'swDocZayavEditWindow',
			object: 'DocumentUc',
			autoLoadData: false,
			root: 'data',
			stringfields:
				[
					{name: 'DocumentUc_id', header: 'ID', key: true},
					{name: 'WhsDocumentUc_id', type: 'int', hidden: true},
					{name: 'DrugDocumentStatus_id', type: 'int', hidden: true},
					{name: 'DrugDocumentStatus_Code', type: 'int', hidden: true},
					{name: 'Contragent_sid', type: 'int', hidden: true},
					{name: 'DrugDocumentClass_id', type: 'int', hidden: true},
					{name: 'DrugDocumentClass_Code', type: 'int', hidden: true},
					{name: 'DrugDocumentStatus_Name', header: lang['status'], type: 'string', width: 160},
					{name: 'Contragent_sName', header: lang['ispolnitel'], type: 'string', id: 'autoexpand'},
					{name: 'DocumentUc_Num', header: lang['nomer'], type: 'string', width: 160},
					{name: 'DocumentUc_setDate', header: lang['data_zayavki'], type: 'date', width: 160},
					{name: 'DocumentUc_planDate', header: lang['data_plan'], type: 'date', width: 160},
					{name: 'DocumentUc_txtdidDate', header: lang['data_fakt'], type: 'date', width: 160}
				],
			actions:
				[
					{name:'action_add', handler: function (){wnd.openRecordEditWindow('add',wnd.GridPanel);}},
					{name:'action_edit', handler: function (){wnd.openRecordEditWindow('edit',wnd.GridPanel);}},
					{name:'action_view', handler: function (){wnd.openRecordEditWindow('view',wnd.GridPanel);}}
				],
			onRowSelect: function(sm, index, record) {
				if (record.get('DocumentUc_id') > 0) {
					wnd.GridPanel.ViewActions.actions.enable();
					wnd.getStatusHistoryMenu(record);
				} else {
					wnd.GridPanel.ViewActions.actions.disable();
				}
			}
		});

		Ext.apply(this, {
			items: [
				wnd.FilterPanel,
				wnd.GridPanel
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
					id: 'DZVWCancelButton',
					text: '<cite>З</cite>акрыть'
				}]
		});

		sw.Promed.swDocZayavViewWindow.superclass.initComponent.apply(this, arguments);
	}
});
