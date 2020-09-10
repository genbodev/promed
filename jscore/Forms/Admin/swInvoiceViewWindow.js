/**
 * swInvoiceEditWindow - окно редактирования накладной
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      	Admin
 * @access       	public
 * @copyright		Copyright (c) 2014 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			06.10.2014
 */
/*NO PARSE JSON*/

sw.Promed.swInvoiceViewWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swInvoiceViewWindow',
	layout: 'border',
	title: lang['uchet_tmts'],
	maximizable: false,
	maximized: true,

	doSearch: function(reset) {
		var base_form = this.FilterPanel.getForm();

		var tabId = this.TabPanel.getActiveTab().getId();
		var grid_panel = this[tabId+'Grid'];

		if (!grid_panel) {
			return;
		}

		var grid = grid_panel.getGrid();

		if (reset) {
			base_form.reset();
		}

		var params = base_form.getValues();
		params.start = 0;
		params.limit = 100;

		var storage_combo = base_form.findField('StorageStructLevel_id');
		if (!Ext.isEmpty(storage_combo.getValue())) {
			params.Storage_id = storage_combo.getFieldValue('Storage_id');
		} else {
			params.Storage_id = null;
		}

		grid.getStore().load({params: params});
	},

	openInvoiceEditWindow: function(action, invoice_type_id) {
		if (!action.inlist(['add','edit','view']) || !invoice_type_id || !invoice_type_id.inlist([1,2])) {
			return false;
		}

		var grid_panel = null;
		switch(invoice_type_id) {
			case 1:	grid_panel = this.InvoiceInGrid;break;
			case 2:	grid_panel = this.InvoiceOutGrid;break;
		}
		var grid = grid_panel.getGrid();

		var params = {};
		params.action = action;
		params.formParams = {InvoiceType_id: invoice_type_id};

		if (action != 'add') {
			params.formParams.Invoice_id = grid.getSelectionModel().getSelected().get('Invoice_id');
		}

		params.callback = function() {
			grid_panel.getAction('action_refresh').execute();
		};

		getWnd('swInvoiceEditWindow').show(params);
		return true;
	},

	deleteInvoice: function(invoice_type_id) {
		if (!invoice_type_id || !invoice_type_id.inlist([1,2])) {
			return false;
		}

		var grid_panel = null;
		switch(invoice_type_id) {
			case 1:	grid_panel = this.InvoiceInGrid;break;
			case 2:	grid_panel = this.InvoiceOutGrid;break;
		}
		var grid = grid_panel.getGrid();

		var record = grid.getSelectionModel().getSelected();

		if (!record || !record.get('Invoice_id')) {
			return false;
		}

		sw.swMsg.show({
			buttons:Ext.Msg.YESNO,
			fn:function (buttonId, text, obj) {
				if (buttonId == 'yes') {
					var params = {Invoice_id: record.get('Invoice_id')};

					Ext.Ajax.request({
						callback: function(opt, scs, response) {
							var response_obj = Ext.util.JSON.decode(response.responseText);

							if (!response_obj.Error_Msg) {
								grid_panel.getAction('action_refresh').execute();
							}
						}.createDelegate(this),
						params: params,
						url: '/?c=Invoice&m=deleteInvoice'
					});
				}
			}.createDelegate(this),
			icon:Ext.MessageBox.QUESTION,
			msg:lang['vyi_hotite_udalit_zapis'],
			title:lang['podtverjdenie']
		});
	},

	show: function() {
		sw.Promed.swInvoiceViewWindow.superclass.show.apply(this, arguments);

		var base_form = this.FilterPanel.getForm();
		base_form.reset();

		var storage_combo = base_form.findField('StorageStructLevel_id');
		storage_combo.getStore().baseParams.Lpu_aid = getGlobalOptions().lpu_id;

		this.TabPanel.setActiveTab(2);
		this.TabPanel.setActiveTab(1);
		this.TabPanel.setActiveTab(0);

		this.doLayout();
	},

	initComponent: function() {
		this.FilterPanel = new Ext.FormPanel({
			frame: true,
			id: 'IVW_FilterPanel',
			region: 'north',
			autoHeight: true,
			labelAlign: 'right',
			labelWidth: 120,
			items: [{
				xtype: 'daterangefield',
				name: 'DateRange',
				fieldLabel: lang['period_prosmotra'],
				plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
				width: 180
			}, {
				xtype: 'swstoragestructlevelcombo',
				hiddenName: 'StorageStructLevel_id',
				fieldLabel: lang['sklad'],
				width: 300
			}, {
				xtype: 'swinvoicesubjectcombo',
				hiddenName: 'InvoiceSubject_id',
				fieldLabel: lang['postavschik_poluchatel'],
				width: 300
			}, {
				xtype: 'swinventoryitemcombo',
				hiddenName: 'InventoryItem_id',
				fieldLabel: lang['soderjaschie_tmts'],
				width: 300
			}],
			keys: [{
				fn: function(e) {
					this.doSearch();
				}.createDelegate(this),
				key: Ext.EventObject.ENTER,
				stopEvent: true
			}]
		});

		this.InvoiceInGrid = new sw.Promed.ViewFrame({
			id: 'IVW_InvoiveInGrid',
			dataUrl: '/?c=Invoice&m=loadInvoiceInGrid',
			border: false,
			autoLoadData: false,
			paging: true,
			root: 'data',
			stringfields: [
				{name: 'Invoice_id', type: 'int', header: 'ID', key: true},
				{name: 'Invoice_Date', header: lang['data'], type: 'date', width: 120},
				{name: 'Invoice_Num', header: lang['nomer'], type: 'int', width: 120},
				{name: 'InvoiceSubject_Name', header: lang['postavschik'], type: 'string', id: 'autoexpand'},
				{name: 'Storage_Name', header: lang['sklad'], type: 'string', width: 280},
				{name: 'Invoice_Sum', header: lang['summa'], type: 'money', width: 140}
			],
			actions: [
				{name:'action_add', handler: function(){this.openInvoiceEditWindow('add',1);}.createDelegate(this)},
				{name:'action_edit', handler: function(){this.openInvoiceEditWindow('edit',1);}.createDelegate(this)},
				{name:'action_view', handler: function(){this.openInvoiceEditWindow('view',1);}.createDelegate(this)},
				{name:'action_delete', handler: function(){this.deleteInvoice(1);}.createDelegate(this)}
			]
		});

		this.InvoiceOutGrid = new sw.Promed.ViewFrame({
			id: 'IVW_InvoiveOutGrid',
			dataUrl: '/?c=Invoice&m=loadInvoiceOutGrid',
			border: false,
			autoLoadData: false,
			paging: true,
			root: 'data',
			stringfields: [
				{name: 'Invoice_id', type: 'int', header: 'ID', key: true},
				{name: 'Invoice_Date', header: lang['data'], type: 'date', width: 120},
				{name: 'Invoice_Num', header: lang['nomer'], type: 'int', width: 120},
				{name: 'InvoiceSubject_Name', header: lang['poluchatel'], type: 'string', id: 'autoexpand'},
				{name: 'Storage_Name', header: lang['sklad'], type: 'string', width: 280},
				{name: 'Invoice_Sum', header: lang['summa'], type: 'money', width: 140}
			],
			actions: [
				{name:'action_add', handler: function(){this.openInvoiceEditWindow('add',2);}.createDelegate(this)},
				{name:'action_edit', handler: function(){this.openInvoiceEditWindow('edit',2);}.createDelegate(this)},
				{name:'action_view', handler: function(){this.openInvoiceEditWindow('view',2);}.createDelegate(this)},
				{name:'action_delete', handler: function(){this.deleteInvoice(2);}.createDelegate(this)}
			]
		});

		this.ShipmentGrid = new sw.Promed.ViewFrame({
			id: 'IVW_ShipmentGrid',
			dataUrl: '/?c=Invoice&m=loadShipmentGrid',
			border: false,
			autoLoadData: false,
			toolbar: false,
			paging: true,
			root: 'data',
			stringfields: [
				{name: 'Shipment_id', type: 'int', header: 'ID', key: true},
				{name: 'Shipment_setDate', header: lang['data_postupleniya'], type: 'date', width: 120},
				{name: 'InventoryItem_Name', header: lang['tmts'], type: 'string', id: 'autoexpand'},
				{name: 'Shipment_Price', header: lang['tsena'], type: 'money', width: 120},
				{name: 'InvoiceSubject_Name', header: lang['postavschik'], type: 'string', width: 240},
				{name: 'Shipment_Count', header: lang['ostatok'], type: 'string', width: 120},
				{name: 'Okei_NationSymbol', header: lang['edinitsyi_izmereniya'], type: 'string', width: 120},
				{name: 'LastInvoiceOut_Date', header: lang['data_poslednego_spisaniya'], type: 'string', width: 120}
			]
		});

		this.TabPanel = new Ext.TabPanel({
			activeTab: 0,
			id: 'IVW_TabPanel',
			layoutOnTabChange: true,
			region: 'center',
			items: [{
				id: 'InvoiceIn',
				layout: 'fit',
				title: lang['prihodnyie_nakladnyie'],
				items: [this.InvoiceInGrid]
			}, {
				id: 'InvoiceOut',
				layout: 'fit',
				title: lang['rashodnyie_nakladnyie'],
				items: [this.InvoiceOutGrid]
			}, {
				id: 'Shipment',
				layout: 'fit',
				title: lang['partii'],
				items: [this.ShipmentGrid]
			}],
			listeners:
			{
				tabchange: function(tab, panel) {
					var base_form = this.FilterPanel.getForm();
					//base_form.reset();

					switch(panel.id) {
						case 'InvoiceIn':
							base_form.findField('InvoiceSubject_id').setFieldLabel(lang['postavschik']);
							this.doSearch();
							break;

						case 'InvoiceOut':
							base_form.findField('InvoiceSubject_id').setFieldLabel(lang['poluchatel']);
							this.doSearch();
							break;

						case 'Shipment':
							base_form.findField('InvoiceSubject_id').setFieldLabel(lang['postavschik']);
							this.doSearch();
							break;
					}
				}.createDelegate(this)
			}
		});

		Ext.apply(this,
		{
			buttons:
			[{
				handler: function() {
					this.doSearch();
				}.createDelegate(this),
				iconCls: 'search16',
				id: 'IVW_SearchButton',
				text: BTN_FRMSEARCH
			},
			{
				handler: function() {
					this.doSearch(true);
				}.createDelegate(this),
				iconCls: 'resetsearch16',
				id: 'IVW_ResetButton',
				text: BTN_FRMRESET
			},
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
			}],
			items: [this.FilterPanel, this.TabPanel]
		});

		sw.Promed.swInvoiceViewWindow.superclass.initComponent.apply(this, arguments);
	}
});