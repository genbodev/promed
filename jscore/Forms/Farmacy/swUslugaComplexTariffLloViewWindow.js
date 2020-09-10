/**
* swUslugaComplexTariffLloViewWindow - окно просмотра списка тарифов
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Farmacy
* @access       public
* @copyright    Copyright (c) 2015 Swan Ltd.
* @author       Salakhov R.
* @version      01.2015
* @comment      
*/
sw.Promed.swUslugaComplexTariffLloViewWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: 'Справочник «Тарифы ЛЛО»',
	layout: 'border',
	id: 'UslugaComplexTariffLloViewWindow',
	modal: true,
	shim: false,
	width: 400,
	resizable: false,
	maximizable: true,
	maximized: true,
	changeYear: function(value) {
		var field = Ext.getCmp('uctlvYear');
		var val = field.getValue();
		if (!val || value == 0) {
			val = (new Date()).getFullYear();
		}
		field.setValue(val+value);
	},
	doSearch: function(clear, default_values) {
		var field = Ext.getCmp('uctlvYear');
		var wnd = this;

		if (clear) {
			field.setValue(null);
		}
		if (default_values) {
			wnd.changeYear(0);
		}

		var params = new Object();
		params.Year = field.getValue();
		params.limit = 100;
		params.start =  0;

		this.SearchGrid.removeAll();
		this.SearchGrid.loadData({
			globalFilters: params
		});
	},
	show: function() {
        var wnd = this;
		sw.Promed.swUslugaComplexTariffLloViewWindow.superclass.show.apply(this, arguments);
		this.viewOnly = false;
		this.allowEdit = false;
		if(arguments[0] && arguments[0].viewOnly)
			this.viewOnly = arguments[0].viewOnly;
		if(arguments[0] && arguments[0].allowEdit)
			this.allowEdit = arguments[0].allowEdit;
		// Установка фильтров при открытии формы просмотра 
		wnd.SearchGrid.setActionHidden('action_add',this.viewOnly);
		wnd.SearchGrid.setActionHidden('action_edit',this.viewOnly);
		wnd.SearchGrid.setActionHidden('action_delete',this.viewOnly);
		if(this.allowEdit)
			wnd.SearchGrid.setActionHidden('action_edit',false);

		wnd.doSearch(true, true);
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
					wnd.doSearch();
				}.createDelegate(this)
			}, {
				xtype : "tbseparator"
			}, {
				xtype : 'numberfield',
				id: 'uctlvYear',
				allowDecimal: false,
				allowNegtiv: false,
				width: 35,
				enableKeyEvents: true,
				listeners: {
					'keydown': function (inp, e) {
						if (e.getKey() == Ext.EventObject.ENTER) {
							e.stopEvent();
							wnd.doSearch();
						}
					}
				}
			}, {
				xtype : "tbseparator"
			}, {
				text: null,
				xtype: 'button',
				iconCls: 'arrow-next16',
				handler: function() {
					wnd.changeYear(1);
					wnd.doSearch();
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
				{name: 'action_delete', url: '/?c=Usluga&m=deleteUslugaComplexTariffLlo'},
				{name: 'action_print'}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 125,
			autoLoadData: false,
			border: true,
			dataUrl: '/?c=Usluga&m=loadUslugaComplexTariffLloList',
			height: 180,
			object: 'UslugaComplexTariffLlo',
			editformclassname: 'swUslugaComplexTariffLloEditWindow',
			id: 'UslugaComplexTariffLloGrid',
			paging: false,
			style: 'margin-bottom: 10px',
			stringfields: [
				{ name: 'UslugaComplexTariff_id', type: 'int', header: 'ID', key: true },
				{ name: 'UslugaComplex_Name', type: 'string', header: lang['naimenovanie_uslugi'], id: 'autoexpand' },
				{ name: 'UslugaComplexTariff_Tariff', type: 'string', header: lang['stavka_rub'], width: 200 },
				{ name: 'UslugaComplexTariff_Date', type: 'string', header: lang['period_deystviya'], width: 250 }
			],
			title: null,
			toolbar: true
		});

		Ext.apply(this, {
			layout: 'border',
			buttons:
			[{
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
			tbar: this.WindowToolbar,
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
		sw.Promed.swUslugaComplexTariffLloViewWindow.superclass.initComponent.apply(this, arguments);
	}	
});