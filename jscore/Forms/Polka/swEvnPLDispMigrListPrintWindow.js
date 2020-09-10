/**
* swEvnPLDispMigrListPrintWindow - Печать списка мигрантов, прошедших мед. освидетельствование
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2017 Swan Ltd.
* @version      
* @comment      Префикс для id компонентов EPLDMLPW (EvnPLDispMigrListPrintWindow)
*
*/
sw.Promed.swEvnPLDispMigrListPrintWindow = Ext.extend(sw.Promed.BaseForm, {codeRefresh: true,
	autoHeight: true,
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	collapsible: false,
	doPrint: function() {
		var
			base_form = this.findById('EvnPLDispMigrListPrintForm').getForm(),
			reportParams = '';

		if (
			(this.template.inlist([ 'DispMigrant_JournalUchet.rptdesign' ]) && !base_form.findField('date').getValue())
			|| (this.template.inlist([ 'DispMigrant_PersonList.rptdesign', 'EvnPLDispMigrant_kolvo.rptdesign' ]) && (!base_form.findField('datePeriod').getValue1() || !base_form.findField('datePeriod').getValue2()))
		) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					//
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		switch ( this.template ) {
			case 'DispMigrant_JournalUchet.rptdesign':
				reportParams = '&paramLpu=' + getGlobalOptions().lpu_id + '&paramDate=' + base_form.findField('date').getValue().dateFormat('d.m.Y');
			break;

			case 'DispMigrant_PersonList.rptdesign':
			case 'EvnPLDispMigrant_kolvo.rptdesign':
				reportParams = '&paramLpu=' + getGlobalOptions().lpu_id + '&paramBegDate=' + base_form.findField('datePeriod').getValue1().dateFormat('d.m.Y') + '&paramEndDate=' + base_form.findField('datePeriod').getValue2().dateFormat('d.m.Y');
			break;
		}

		printBirt({
			'Report_FileName': this.template,
			'Report_Params': reportParams,
			'Report_Format': 'pdf'
		});
	},
	initComponent: function() {
		Ext.apply(this, {
			buttons: [{
				handler : function(button, event) {
					this.doPrint();
				}.createDelegate(this),
				iconCls : 'print16',
				text: lang['pechat']
			}, {
				text: '-'
			},
			HelpButton(this, -1),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				onShiftTabAction: function() {
					this.buttons[this.buttons.length - 2].focus();
				}.createDelegate(this),
				onTabAction: function() {
					this.findById('EPLDMLPW_begDT').focus();
				}.createDelegate(this),
				text: BTN_FRMCLOSE
			}],
			items: [ new sw.Promed.FormPanel({
				autoHeight: true,
				border: false,
				frame: true,
				id: 'EvnPLDispMigrListPrintForm',
				labelWidth: 100,
				layout: 'form',
				style: 'padding: 3px',
				items: [{
					fieldLabel: 'Дата',
					id: 'EPLDMLPW_date',
					name: 'date',
					width: 120,
					plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
					xtype: 'swdatefield'
				}, {
					fieldLabel: 'Дата',
					id: 'EPLDMLPW_datePeriod',
					name: 'datePeriod',
					width: 180,
					plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
					xtype: 'daterangefield'
				}]
			})]
		});
		
		sw.Promed.swEvnPLDispMigrListPrintWindow.superclass.initComponent.apply(this, arguments);
	},
	maximizable: false,
	modal: true,
	plain: true,
	resizable: false,
	show: function() {
		sw.Promed.swEvnPLDispMigrListPrintWindow.superclass.show.apply(this, arguments);

		if ( !arguments[0] || !arguments[0].template || !arguments[0].title ) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() { this.hide(); }.createDelegate(this));
			return false;
		}

		this.template = arguments[0].template;
		this.setTitle(arguments[0].title);

		var base_form = this.findById('EvnPLDispMigrListPrintForm').getForm();
		base_form.reset();

		this.restore();
		this.center();

		base_form.findField('date').setAllowBlank(true);
		base_form.findField('date').setContainerVisible(false);
		base_form.findField('datePeriod').setAllowBlank(true);
		base_form.findField('datePeriod').setContainerVisible(false);

		switch ( this.template ) {
			case 'DispMigrant_JournalUchet.rptdesign':
				base_form.findField('date').setAllowBlank(false);
				base_form.findField('date').setContainerVisible(true);
			break;

			case 'DispMigrant_PersonList.rptdesign':
			case 'EvnPLDispMigrant_kolvo.rptdesign':
				base_form.findField('datePeriod').setAllowBlank(false);
				base_form.findField('datePeriod').setContainerVisible(true);
			break;
		}
		
		this.doLayout();
		this.syncSize();
		this.syncShadow();
	},
	title: 'Параметры печати',
	width: 500
});