/**
* swUslugaComplexTariffLloSelectWindow - окно выбора тарифа ЛЛО
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Farmacy
* @access       public
* @copyright    Copyright (c) 2015 Swan Ltd.
* @author       Salakhov R.
* @version      04.2015
* @comment      
*/
sw.Promed.swUslugaComplexTariffLloSelectWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: lang['vyibor_tarifa_llo'],
	layout: 'border',
	id: 'UslugaComplexTariffLloSelectWindow',
	modal: true,
	shim: false,
	width: 400,
	resizable: false,
	maximizable: true,
	maximized: true,
	doSelect: function() {
		var wnd = this;
		var record = this.SearchGrid.getGrid().getSelectionModel().getSelected();

		if (record.get('UslugaComplexTariff_id') > 0) {
			if (wnd.WhsDocumentTitleTariff_id > 0) {
				sw.swMsg.show({
					icon: Ext.MessageBox.QUESTION,
					msg: lang['deystvuyuschiy_tarif_budet_izmenen_vyi_hotite_izmenit_tarif_po_dokumentu'],
					title: lang['podtverjdenie'],
					buttons: Ext.Msg.YESNO,
					fn: function(buttonId) {
						if ('yes' == buttonId) {
							wnd.onSelect({
								UslugaComplexTariff_id: record.get('UslugaComplexTariff_id')
							});
							wnd.hide();
						}
					}
				});
			} else {
				wnd.onSelect({
					UslugaComplexTariff_id: record.get('UslugaComplexTariff_id')
				});
				wnd.hide();
			}
		}
	},
	doSearch: function() {
		var wnd = this;
		var current_date = new Date();

		var params = new Object();
		params.UslugaComplexTariff_Date = current_date.format('d.m.Y');
		params.limit = 100;
		params.start =  0;

		this.SearchGrid.removeAll();
		this.SearchGrid.loadData({
			globalFilters: params
		});
	},
	show: function() {
        var wnd = this;
		sw.Promed.swUslugaComplexTariffLloSelectWindow.superclass.show.apply(this, arguments);
		this.onSelect = Ext.emptyFn;
		this.WhsDocumentTitleTariff_id = null;

		if ( arguments[0].onSelect && typeof arguments[0].onSelect == 'function' ) {
			this.onSelect = arguments[0].onSelect;
		}
		if ( arguments[0].WhsDocumentTitleTariff_id ) {
			this.WhsDocumentTitleTariff_id = arguments[0].WhsDocumentTitleTariff_id;
		}

		wnd.doSearch();
	},
	initComponent: function() {
		var wnd = this;

		this.SearchGrid = new sw.Promed.ViewFrame({
			actions: [
				{name: 'action_add'},
				{name: 'action_edit', hidden: true},
				{name: 'action_view', hidden: true},
				{name: 'action_delete', hidden: true},
				{name: 'action_print', hidden: true}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 125,
			autoLoadData: false,
			border: true,
			dataUrl: '/?c=Usluga&m=loadUslugaComplexTariffLloList',
			height: 180,
			object: 'UslugaComplexTariffLlo',
			editformclassname: 'swUslugaComplexTariffLloEditWindow',
			id: 'UslugaComplexTariffLloSelectGrid',
			paging: false,
			style: 'margin-bottom: 10px',
			stringfields: [
				{ name: 'UslugaComplexTariff_id', type: 'int', header: 'ID', key: true },
				{ name: 'UslugaComplex_Name', type: 'string', header: lang['naimenovanie_uslugi'], id: 'autoexpand' },
				{ name: 'UslugaComplexTariff_Tariff', type: 'string', header: lang['stavka_rub'], width: 200 },
				{ name: 'UslugaComplexTariff_Date', type: 'string', header: lang['period_deystviya'], width: 250 }
			],
			title: null,
			toolbar: true,
			onDblClick: function(grid) {
				wnd.doSelect();
			},
			params: {
				callback: function(grid, id) {
					if (id > 0) {
						sw.swMsg.show({
							icon: Ext.MessageBox.QUESTION,
							msg: lang['sohranit_vyibrannyiy_tarif_kak_tarif_po_dokumentu'],
							title: lang['podtverjdenie'],
							buttons: Ext.Msg.YESNO,
							fn: function(buttonId) {
								if ('yes' == buttonId) {
									wnd.onSelect({
										UslugaComplexTariff_id: id
									});
									wnd.hide();
								}
							}
						});
					}
					grid.refreshRecords(null,0);
				}
			}
		});

		Ext.apply(this, {
			layout: 'border',
			buttons:
			[{
				handler: function()
				{
					this.ownerCt.doSelect();
				},
				iconCls: 'ok16',
				text: lang['vyibrat']
			},
			{
				text: '-'
			},
			HelpButton(this, 0),
			{
				handler: function() 
				{
					this.ownerCt.hide();
				},
				iconCls: 'close16',
				text: BTN_FRMCLOSE
			}],
			items:[
				{
					border: false,
					region: 'center',
					layout: 'border',
					items:[{
						border: false,
						region: 'center',
						layout: 'fit',
						items: [this.SearchGrid]
					}]
				}
			]
		});
		sw.Promed.swUslugaComplexTariffLloSelectWindow.superclass.initComponent.apply(this, arguments);
	}	
});