/**
* swWhsDocumentTitleViewWindow - окно поточного ввода договоров о поставках.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Farmacy
* @access       public
* @copyright    Copyright (c) 2009-2012 Swan Ltd.
* @author       Salakhov Rustam
* @version      07.08.2012
*/
sw.Promed.swWhsDocumentTitleViewWindow = Ext.extend(sw.Promed.BaseForm, {
	border: false,
	buttonAlign: 'left',
	closeAction: 'hide',
	height: 500,
	width: 800,
	id: 'WhsDocumentTitleViewWindow',
	title: lang['pravoustanavlivayuschie_dokumentyi_spisok'],
	layout: 'border',
	maximizable: true,
	maximized: true,
	modal: false,
	plain: true,
	resizable: false,
	firstTabIndex: 1000,
	changeYear: function(value) {
		var wnd = this;
	
		var val = Ext.getCmp('wdtvYear').getValue();
		if (!val || value == 0)
			val = (new Date()).getFullYear();
		Ext.getCmp('wdtvYear').setValue(val+value);
		wnd.doSearch();
	},
	doSearch: function(clear) {
		if (clear) {
			Ext.getCmp('wdtvYear').setValue(null);
		}
			
		var params = new Object();
		params.Year = Ext.getCmp('wdtvYear').getValue();
		params.limit = 100;
		params.start =  0;

		this.SearchGrid.removeAll();
		this.SearchGrid.loadData({
			globalFilters: params
		});
	},
	copyDocument: function() {
		var wnd = this;
		var view_frame = wnd.SearchGrid;
		var record = view_frame.getGrid().getSelectionModel().getSelected();
		var id = record.get('WhsDocumentTitle_id');
		if (id > 0) {
			sw.swMsg.show( {
				icon: Ext.MessageBox.QUESTION,
				msg: lang['vyi_hotite_skopirovat_dokument'],
				title: lang['podtverjdenie'],
				buttons: Ext.Msg.YESNO,
				fn: function(buttonId, text, obj) {
					if ('yes' == buttonId) {
						Ext.Ajax.request({
							params:{
								WhsDocumentTitle_id: id
							},
							success: function (response) {
								var result = Ext.util.JSON.decode(response.responseText);
								if (result && result.WhsDocumentTitle_id) {
									getWnd(view_frame.editformclassname).show({
										WhsDocumentTitle_id: result.WhsDocumentTitle_id,
										callback: view_frame.refreshRecords,
										owner: view_frame,
										action: 'edit'
									});
									view_frame.refreshRecords(null,0);
								}
							},
							failure:function () {
								sw.swMsg.alert(lang['oshibka'], lang['pri_kopirovanii_dokumenta_proizoshla_oshibka']);
							},
							url:'/?c=WhsDocumentTitle&m=copy'
						});
					}
				}
			});
		}
	},
	selectTariff: function() {
		var wnd = this;
		var view_frame = wnd.SearchGrid;
		var record = view_frame.getGrid().getSelectionModel().getSelected();
		var id = record.get('WhsDocumentTitle_id');
		if (id > 0) {
			getWnd('swUslugaComplexTariffLloSelectWindow').show({
				WhsDocumentTitleTariff_id: record.get('WhsDocumentTitleTariff_id'),
				onSelect: function(data) {
					if (data.UslugaComplexTariff_id && data.UslugaComplexTariff_id > 0) {
						Ext.Ajax.request({
							params:{
								WhsDocumentTitleTariff_id: record.get('WhsDocumentTitleTariff_id'),
								WhsDocumentTitle_id: id,
								UslugaComplexTariff_id: data.UslugaComplexTariff_id
							},
							success: function (response) {
								view_frame.refreshRecords(null,0);
							},
							failure:function () {
								sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_tarifa_proizoshla_oshibka']);
							},
							url:'/?c=WhsDocumentTitle&m=saveWhsDocumentTitleTariff'
						});
					}
				}
			});
		}
	},
	deleteTariff: function() {
		var wnd = this;
		var view_frame = wnd.SearchGrid;
		var record = view_frame.getGrid().getSelectionModel().getSelected();
		var id = record.get('WhsDocumentTitleTariff_id');
		if (id > 0) {
			sw.swMsg.show({
				icon: Ext.MessageBox.QUESTION,
				msg: lang['deystvuyuschiy_tarif_budet_udalen_prodoljit'],
				title: lang['podtverjdenie'],
				buttons: Ext.Msg.YESNO,
				fn: function(buttonId) {
					if ('yes' == buttonId) {
						Ext.Ajax.request({
							params:{
								id: id
							},
							success: function (response) {
								view_frame.refreshRecords(null,0);
							},
							failure:function () {
								sw.swMsg.alert(lang['oshibka'], lang['pri_udalenii_tarifa_proizoshla_oshibka']);
							},
							url:'/?c=WhsDocumentTitle&m=deleteWhsDocumentTitleTariff'
						});
					}
				}
			});
		}
	},
	deleteDocument: function() {
		var view_frame = this.SearchGrid;
		if (view_frame.getGrid().getSelectionModel().getSelected().data['WhsDocumentStatusType_id'] == 2) {
			Ext.Msg.alert(lang['oshibka'], lang['dokument_ispolnen_ego_udalenie_ne_vozmojno']);
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
	show: function() {
		sw.Promed.swWhsDocumentTitleViewWindow.superclass.show.apply(this, arguments);
		this.getLoadMask().show();

		if(!this.SearchGrid.getAction('wdtv_action_actions')) {
			this.SearchGrid.addActions({
				name:'wdtv_action_actions',
				text:lang['deystviya'],
				menu: [{
					handler: function() {
						this.copyDocument()
					}.createDelegate(this),
					iconCls: 'copy16',
					name: 'action_copy',
					text: lang['kopirovat'],
					tooltip: lang['kopirovat']
				}, {
					handler: function() {
						this.selectTariff()
					}.createDelegate(this),
					iconCls: 'ok16',
					name: 'action_select_tariff',
					text: lang['vyibrat_tarif_na_oplatu'],
					tooltip: lang['vyibrat_tarif_na_oplatu']
				}, {
					handler: function() {
						this.deleteTariff()
					}.createDelegate(this),
					iconCls: 'delete16',
					name: 'action_delete_tariff',
					text: lang['udalit_dannyie_o_tarife'],
					tooltip: lang['udalit_dannyie_o_tarife']
				}],
				iconCls: 'actions16',
				hidden: getWnd('swWorkPlaceSpecMEKLLOWindow').isVisible()
			});
		}
		
		this.center();
		this.maximize();
		this.changeYear(0);
		this.getLoadMask().hide();
        this.onlyView = false;

        if(arguments[0] && arguments[0].onlyView){
            this.onlyView = true;
        }
        this.SearchGrid.setReadOnly(this.onlyView);
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
					id: 'wdtvYear',
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
				{name: 'action_delete', handler: function() {wnd.deleteDocument()}, url: '/?c=WhsDocumentTitle&m=delete'},
				{name: 'action_print'}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			border: true,
			dataUrl: '/?c=WhsDocumentTitle&m=loadList',
			height: 180,
			region: 'center',
			object: 'WhsDocumentTitle',
			editformclassname: 'swWhsDocumentTitleEditWindow',
			id: 'wdtvWhsDocumentTitleGrid',
			paging: false,
			style: 'margin-bottom: 10px',
			stringfields: [
				{name: 'WhsDocumentTitle_id', type: 'int', header: 'ID', key: true},
				{name: 'WhsDocumentTitle_Name', type: 'string', header: lang['naimenovanie_dokumenta'], width: 120, id: 'autoexpand'},
				{name: 'WhsDocumentTitleType_id_Name', type: 'string', header: lang['tip_dokumenta'], width: 250},				
				{name: 'WhsDocumentStatusType_id', type: 'string', header: lang['status'], hidden: true},				
				{name: 'WhsDocumentStatusType_id_Name', type: 'string', header: lang['status'], width: 120},				
				{name: 'WhsDocumentTitle_begDate', type: 'date', header: lang['data_nachala_deystviya'], width: 150},
				{name: 'WhsDocumentTitle_endDate', type: 'date', header: lang['data_okonchaniya_deystviya'], width: 150},
				{name: 'WhsDocumentTitleTariff_id', hidden: true},
				{name: 'UslugaComplexTariff_Name', type: 'string', header: lang['tarif'], width: 250}
			],
			toolbar: true
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
				id: 'wdtvRightPanel',
				items: [wnd.SearchGrid]
			}]
		});
		sw.Promed.swWhsDocumentTitleViewWindow.superclass.initComponent.apply(this, arguments);
	}
});