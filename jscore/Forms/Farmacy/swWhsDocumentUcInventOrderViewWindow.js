/**
 * swWhsDocumentUcInventOrderViewWindow - окно просмотра спика приказов на инвентаризацию.
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Farmacy
 * @access       public
 * @copyright    Copyright (c) 2014 Swan Ltd.
 * @author       Salakhov Rustam
 * @version      10.2014
 */
sw.Promed.swWhsDocumentUcInventOrderViewWindow = Ext.extend(sw.Promed.BaseForm, {
	border: false,
	buttonAlign: 'left',
	closeAction: 'hide',
	height: 500,
	width: 800,
	id: 'WhsDocumentUcInventOrderViewWindow',
	title: lang['prikazyi_na_provedenie_inventarizatsii_spisok'],
	layout: 'border',
	maximizable: true,
	maximized: true,
	modal: false,
	plain: true,
	resizable: false,
	firstTabIndex: 1000,
	changeYear: function(value) {
		var wnd = this;

		var val = Ext.getCmp('wduiotvYear').getValue();
		if (!val || value == 0)
			val = (new Date()).getFullYear();
		Ext.getCmp('wduiotvYear').setValue(val+value);
		wnd.doSearch();
	},
	doSearch: function(clear) {
		if (clear) {
			Ext.getCmp('wduiotvYear').setValue(null);
		}

		var params = new Object();
		params.limit = 100;
		params.start =  0;
		params.Year = Ext.getCmp('wduiotvYear').getValue();
		params.Org_aid = this.ARMType == 'merch' || this.ARMType == 'lpupharmacyhead' ? getGlobalOptions().org_id : null; //merch - АРМ товароведа; lpupharmacyhead - АРМ заведующего аптекой МО

		this.SearchGrid.removeAll();
		this.SearchGrid.loadData({
			globalFilters: params
		});
	},
	deleteDocument: function() {
		var view_frame = this.SearchGrid;
		if (view_frame.getGrid().getSelectionModel().getSelected().get('WhsDocumentStatusType_Code') == '2') {
			Ext.Msg.alert(lang['oshibka'], lang['dokument_podpisan_ego_udalenie_ne_vozmojno']);
		} else {
			view_frame.deleteRecord();
		}
	},
	getLoadMask: function() {
		if (!this.loadMask) {
			this.loadMask = new Ext.LoadMask(Ext.get(this.id), {msg: lang['podojdite']});
		}
		return this.loadMask;
	},
	sendNotice: function() {
		var record = this.SearchGrid.getGrid().getSelectionModel().getSelected();

		if (record && record.get('WhsDocumentUc_id') > 0 && record.get('WhsDocumentStatusType_Code') == 2) { //2 - Действующий;
			Ext.Ajax.request({
				params:{
					WhsDocumentUc_id: record.get('WhsDocumentUc_id')
				},
				success: function (response) {
					var result = Ext.util.JSON.decode(response.responseText);
					if (result[0] && result[0].Message_id > 0) {
						Ext.Msg.alert(lang['soobschenie'], lang['uvedomlenie_uspeshno_otpravleno']);
					}
				},
				url:'/?c=WhsDocumentUcInvent&m=getNotice'
			});
		}
	},
	show: function() {
		sw.Promed.swWhsDocumentUcInventOrderViewWindow.superclass.show.apply(this, arguments);

		this.ARMType = null;
        this.onlyView = false;

        if(arguments[0]) {
			if(arguments[0].onlyView){
                this.onlyView = true;
			}
			if (arguments[0].ARMType) {
				this.ARMType = arguments[0].ARMType;
			}
		}

		if(!this.SearchGrid.getAction('wduiov_action_actions')) {
			this.SearchGrid.addActions({
				name:'wduiov_action_actions',
				text:lang['deystviya'],
				menu: [{
					name: 'create_by_contract',
					iconCls: 'ok16',
					text: lang['rassyilka_uvedomleniy'],
					handler: this.sendNotice.createDelegate(this)
				}],
				iconCls: 'actions16'
			});
		}

		this.SearchGrid.params.ARMType = this.ARMType;
        this.SearchGrid.setReadOnly(this.onlyView);

		this.getLoadMask().show();
		this.center();
		this.maximize();
		this.changeYear(0);
		this.getLoadMask().hide();
	},
	initComponent: function() {
		var wnd = this;

		this.WindowToolbar = new Ext.Toolbar({
			items: [{
				xtype: 'button',
				disabled: true,
				text: lang['god']
			}, {
				text: null,
				xtype: 'button',
				iconCls: 'arrow-previous16',
				handler: function() {
					wnd.changeYear(-1);
				}.createDelegate(this)
			}, {
				xtype : "tbseparator"
			}, {
				xtype : 'numberfield',
				id: 'wduiotvYear',
				name: 'Year',
				allowDecimal: false,
				allowNegtiv: false,
				width: 35
			}, {
				xtype : "tbseparator"
			}, {
				text: null,
				xtype: 'button',
				iconCls: 'arrow-next16',
				handler: function() {
					wnd.changeYear(1);
				}.createDelegate(this)
			}, {
				xtype: 'tbfill'
			}
			]
		});

		this.SearchGrid = new sw.Promed.ViewFrame({
			actions: [
				{name: 'action_add'},
				{name: 'action_edit'},
				{name: 'action_view'},
				{name: 'action_delete', handler: function() {wnd.deleteDocument()}, url: '/?c=WhsDocumentUcInvent&m=deleteWhsDocumentUcInventOrder'},
				{name: 'action_print'}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			border: true,
			dataUrl: '/?c=WhsDocumentUcInvent&m=loadWhsDocumentUcInventOrderList',
			height: 180,
			region: 'center',
			object: 'WhsDocumentUcInventOrder',
			editformclassname: 'swWhsDocumentUcInventOrderEditWindow',
			id: 'wduiotvWhsDocumentUcInventOrderGrid',
			paging: false,
			style: 'margin-bottom: 10px',
			params: {
				WhsDocumentType_Code: 20
			},
			stringfields: [
				{name: 'WhsDocumentUc_id', type: 'int', header: 'ID', key: true},
				{name: 'WhsDocumentUc_Num', type: 'string', header: lang['№_dokumenta'], width: 150},
				{name: 'WhsDocumentUc_Date', type: 'date', header: lang['data_dokumenta'], width: 150},
				{name: 'WhsDocumentStatusType_Code', hidden: true, isparams: true},
				{name: 'WhsDocumentStatusType_Name', type: 'string', header: lang['status'], width: 120},
				{name: 'WhsDocumentUc_Result', type: 'string', header: lang['rezultatyi'], width: 150},
				{name: 'Org_Name', type: 'string', header: lang['organizatsiya'], width: 150},
				{name: 'WhsDocumentUc_Name', type: 'string', header: lang['naimenovanie_dokumenta'], width: 250, id: 'autoexpand'},
				{ name: 'DrugFinance_Name', type: 'string', header: lang['istochnik_finans'], width: 150 },
				{name: 'WhsDocumentCostItemType_Name', type: 'string', header: lang['statya_rashoda'], width: 150}
			],
			toolbar: true,
			onRowSelect: function(sm,rowIdx,record) {
				if (record.get('WhsDocumentStatusType_Code') == 2) {
					this.ViewActions.wduiov_action_actions.setDisabled(false);
				} else {
					this.ViewActions.wduiov_action_actions.setDisabled(true);
				}
			}
		});

		Ext.apply(this, {
			layout:'border',
			defaults: {split: true},
			buttons:
				[{
					text: '-'
				},
					HelpButton(this),
					{
						handler: function()
						{
							this.ownerCt.hide()
						},
						iconCls: 'close16',
						text: BTN_FRMCLOSE
					}],
			tbar: this.WindowToolbar,
			items:
				[{
					border: false,
					xtype: 'panel',
					region: 'center',
					layout:'border',
					id: 'wduiotvRightPanel',
					items: [wnd.SearchGrid]
				}]
		});
		sw.Promed.swWhsDocumentUcInventOrderViewWindow.superclass.initComponent.apply(this, arguments);
	}
});