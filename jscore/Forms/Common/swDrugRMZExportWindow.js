/**
* swDrugRMZExportWindow - окно выгрузки остатков и поставок по ОНЛС и ВЗН
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2015 Swan Ltd.
* @author       Salakhov R.
* @version      03.2015
* @comment      
*/
sw.Promed.swDrugRMZExportWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: lang['vyigruzka_ostatkov_i_postavok_po_onls_i_vzn'],
	layout: 'border',
	id: 'DrugRMZExportWindow',
	modal: true,
	shim: false,
	width: 380,
	height: 180,
	resizable: false,
	maximizable: false,
	maximized: false,
	setDefaultValues: function() {
		var curr_date = new Date();
		var last_month = curr_date.getMonth() > 0 ? curr_date.getMonth() : 12;

		this.form.findField('Year').setValue(curr_date.getFullYear());
		this.form.findField('Month').setValue(last_month);
		this.form.findField('Supply_DateRange').setValue(curr_date.format('d.')+(last_month < 10 ? '0' : '')+last_month+'.'+(last_month == 12 ? curr_date.getFullYear()-1 : curr_date.getFullYear())+' - '+curr_date.format('d.m.Y'));
	},
	doExport:  function() {
		var wnd = this;
		var params = new Object();

		if ( !this.form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					wnd.findById('DrugRMZExportForm').getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		params = wnd.form.getValues();
		params.Month = wnd.form.findField('Month').getValue();

		wnd.getLoadMask(lang['formirovanie_fayla']).show();
		Ext.Ajax.request({
			scope: this,
			params: params,
			url:'/?c=DrugNomen&m=exportDrugRMZToCsv',
			callback: function(o, s, r) {
				wnd.getLoadMask().hide();
				if(s) {
					var obj = Ext.util.JSON.decode(r.responseText);
					if( obj.success ) {
						window.open(obj.url);
					}
				}
			}
		});

		return true;		
	},
	show: function() {
        var wnd = this;
		sw.Promed.swDrugRMZExportWindow.superclass.show.apply(this, arguments);		

		this.form.reset();
		this.setDefaultValues();
	},
	initComponent: function() {
		var wnd = this;

		this.monthCombo = new Ext.form.ComboBox({
			allowBlank: false,
			fieldLabel: lang['otchetnyiy_mesyats'],
			width: 127,
			triggerAction: 'all',
			store: [
				[1, lang['yanvar']],
				[2, lang['fevral']],
				[3, lang['mart']],
				[4, lang['aprel']],
				[5, lang['may']],
				[6, lang['iyun']],
				[7, lang['iyul']],
				[8, lang['avgust']],
				[9, lang['sentyabr']],
				[10, lang['oktyabr']],
				[11, lang['noyabr']],
				[12, lang['dekabr']]
			],
			name: 'Month'
		});
		
		var form = new Ext.Panel({
			autoScroll: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			height: 70,
			border: false,			
			frame: true,
			region: 'center',
			labelAlign: 'right',
			items: [{
				xtype: 'form',
				autoHeight: true,
				id: 'DrugRMZExportForm',
				style: 'margin-bottom: 0.5em;',
				bodyStyle:'background:#DFE8F6;padding:5px;',
				border: true,
				labelWidth: 120,
				collapsible: true,
				items: [{
					xtype: 'numberfield',
					fieldLabel: lang['otchetnyiy_god'],
					name: 'Year',
					allowDecimals: false,
					allowNegative: false,
					allowBlank: false,
					plugins: [new Ext.ux.InputTextMask('9999', false)],
					minLength: 4
				},
				wnd.monthCombo,
				{
					xtype: 'daterangefield',
					fieldLabel: lang['postavki_za_period'],
					name: 'Supply_DateRange',
					plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
					width: 170
				}]
			}]
		});
		Ext.apply(this, {
			layout: 'border',
			buttons:
			[{
				handler: function() 
				{
					this.ownerCt.doExport();
				},
				iconCls: 'view16',
				text: lang['eksport']
			}, 
			{
				text: '-'
			},
			HelpButton(this, 0),//todo проставить табиндексы
			{
				handler: function() 
				{
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],
			items:[form]
		});
		sw.Promed.swDrugRMZExportWindow.superclass.initComponent.apply(this, arguments);
		this.form = this.findById('DrugRMZExportForm').getForm();
	}	
});