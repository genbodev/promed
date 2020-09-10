/**
* swSmpFarmacyAddDrugWindow - Форма учета прихода медикаментов для СМП
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @autor		Miyusov Alexandr
* @copyright    Copyright (c) 2013 Swan Ltd.
* @version      21.01.2013
*/

/*NO PARSE JSON*/
sw.Promed.swSmpFarmacyAddDrugWindow = Ext.extend(sw.Promed.BaseForm, {
	title:lang['dobavlenie_medikamenta'],
	autoHeight: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	documentUcStrMode: null,
	codeRefresh: true,
	objectName: 'swSmpFarmacyAddDrugWindow',
	objectSrc: '/jscore/Forms/Common/swSmpFarmacyAddDrugWindow.js',
	doSave: function() {
		var base_form = this.findById('SmpFarmacyAddForm').getForm();
		var form = this;
		if (base_form.findField('CmpFarmacyBalanceAddHistory_RashEdCount').getValue()<=0) {
			sw.swMsg.show(
			{
				buttons: Ext.Msg.OK,
				fn: function() 
				{
					base_form.findField('CmpFarmacyBalanceAddHistory_RashEdCount').focus(true, 0);
				},
				icon: Ext.Msg.WARNING,
				msg: lang['kolichestvo_dozirovok_medikamenta_doljno_byit_bolshe_0'],
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		
		
		if (!base_form.isValid()) 
		{
			sw.swMsg.show(
			{
				buttons: Ext.Msg.OK,
				fn: function() 
				{
					form.getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		
		
		
		
		var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Подождите, идет сохранение..." });
		loadMask.show();
		base_form.submit({
			failure: function(result_form, action) {
				loadMask.hide();

				if ( action.result ) {
					if ( action.result.Error_Msg ) {
						sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
					}
					else {
						sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_3]']);
					}
				}
			}.createDelegate(this),
			params: {
				CmpFarmacyBalanceAddHistory_AddDate: (base_form.findField('CmpFarmacyBalanceAddHistory_AddDate').getValue())?base_form.findField('CmpFarmacyBalanceAddHistory_AddDate').getValue().dateFormat('d.m.Y'):''
			},
			success: function(result_form, action) {
				loadMask.hide();
				form.callback();
				form.hide();
			}.createDelegate(this)
		});
	},
	draggable: true,
	clearValues: function(enable) {
		var base_form = this.findById('SmpFarmacyAddForm').getForm();
		base_form.findField('Drug_id').setValue(null);
		base_form.findField('CmpFarmacyBalanceAddHistory_AddDate').setValue(null);
		base_form.findField('CmpFarmacyBalanceAddHistory_RashCount').setValue(null);
		base_form.findField('DrugPrepFas_id').setValue(null);
		
	},

	id: 'SmpFarmacyAddDrugWindow',
	initComponent: function() {
		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'save16',
				text: BTN_FRMSAVE,
				tooltip: lang['sohranit']
			}, {
				text: '-'
			},
			{	text: BTN_FRMHELP,
				iconCls: 'help16',
				handler: function(button, event) {
					ShowHelp(this.ownerCt.title);
				}
			},
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				onShiftTabAction: function () {
					this.buttons[0].focus();
				}.createDelegate(this),
				onTabAction: function () {
					this.findById('SmpFarmacyAddForm').getForm().findField('Drug_id').focus(true);
				}.createDelegate(this),
				text: BTN_FRMCANCEL,
				tooltip: lang['zakryit']
			}],
			items: [ new Ext.form.FormPanel({
				autoScroll: true,
				bodyStyle: 'padding: 0.5em;',
				border: false,
				frame: true,
				id: 'SmpFarmacyAddForm',
				items: [
				{ // Первый комбобокс (медикамент)
					allowBlank: false,
					hiddenName: 'DrugPrepFas_id',
					anchor: null,
					width: 500,
					xtype: 'swdrugprepcombo'
				},
				{ // второй комбобокс (упаковка)
					allowBlank: false,
					hiddenName: 'Drug_id',
					anchor: null,
					width:500,
					listeners: 
					{
						'change': function(combo, newValue, oldValue) {
							var base_form = this.findById('SmpFarmacyAddForm').getForm();
							var fw = this;
							var record = combo.getStore().getById(newValue);

							base_form.findField('Drug_Fas').setRawValue('');
							base_form.findField('DrugForm_Name').setRawValue('');
							base_form.findField('DrugUnit_Name').setRawValue('');

							if ( !record ) {
								base_form.findField('CmpFarmacyBalanceAddHistory_RashCount').fireEvent('change', base_form.findField('CmpFarmacyBalanceAddHistory_RashCount'), base_form.findField('CmpFarmacyBalanceAddHistory_RashCount').getValue());
								return false;
							}

							base_form.findField('Drug_Fas').setRawValue(record.get('Drug_Fas') ? record.get('Drug_Fas') : 1);
							base_form.findField('DrugForm_Name').setRawValue(record.get('DrugForm_Name'));
							base_form.findField('DrugUnit_Name').setRawValue(record.get('DrugUnit_Name'));

							base_form.findField('CmpFarmacyBalanceAddHistory_RashCount').fireEvent('change', base_form.findField('CmpFarmacyBalanceAddHistory_RashCount'), base_form.findField('CmpFarmacyBalanceAddHistory_RashCount').getValue());

							return true;
						}.createDelegate(this)
					},
					xtype: 'swdrugpackcombo'
				}, {
					border: false,
					layout: 'column',
					items: [{
						border: false,
						layout: 'form',
						items: [{
							disabled: true,
							fieldLabel: lang['ed_ucheta'],
							name: 'DrugUnit_Name',
							width: 110,
							xtype: 'textfield'
						}]
					}, {
						border: false,
						labelWidth: 100,
						layout: 'form',
						items: [{
							disabled: true,
							fieldLabel: lang['lek_forma'],
							name: 'DrugForm_Name',
							width: 120,
							xtype: 'textfield'
						}]
					}, {
						border: false,
						labelWidth: 100,
						layout: 'form',
						items: [{
							disabled: true,
							fieldLabel: lang['kol-vo_v_upak'],
							name: 'Drug_Fas',
							width: 60,
							xtype: 'numberfield'
						}]
					}]
				},{
					border: false,
					layout: 'column',
					items: [{
						border: false,
						layout: 'form',
						items: [{
							allowBlank: false,
							allowDecimals: true,
							allowNegative: false,
							decimalPrecision: 4, 
							fieldLabel: lang['kolichestvo_ed_uch'],
							listeners: {
								'change': function(field, newValue, oldValue) 
								{
									this.setKolvo(field, newValue, false);
								}.createDelegate(this)
							},
							minValue: 0,
							name: 'CmpFarmacyBalanceAddHistory_RashCount',
							width: 100,
							xtype: 'numberfield'
						}]
					}, {
						border: false,
						labelWidth: 130,
						layout: 'form',
						items: 
						[{
							allowBlank: false,
							allowDecimals: true,
							allowNegative: false,
							decimalPrecision: 2,
							disabled: false,
							fieldLabel: lang['kol-vo_ed_doz'],
							name: 'CmpFarmacyBalanceAddHistory_RashEdCount',
							width: 100,
							xtype: 'numberfield',
							listeners: 
							{
								'change': function(field, newValue, oldValue) 
								{
									this.setKolvo(field, newValue, true);
								}.createDelegate(this)
							}
						}]
					}]
				}, {
					fieldLabel: lang['data_postavki'],
					format: 'd.m.Y',
					allowBlank: false,
					name: 'CmpFarmacyBalanceAddHistory_AddDate',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
					width: 100,
					xtype: 'swdatefield'
				}],
				labelAlign: 'right',
				labelWidth: 130,
				reader: new Ext.data.JsonReader({
					success: Ext.emptyFn
				}, [
					{ name: 'Drug_id' },
					{ name: 'DrugPrepFas_id' },
					//{ name: 'CmpFarmacyBalanceAddHistory_RashCount' },
					{ name: 'CmpFarmacyBalanceAddHistory_AddDate' }
					
					
				]),
				region: 'center',
				trackResetOnLoad: true,
				url: '/?c=CmpCallCard&m=saveSmpFarmacyDrug'
			})]
		});
		sw.Promed.swSmpFarmacyAddDrugWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('SmpFarmacyAddForm');

			e.stopEvent();

			if ( e.browserEvent.stopPropagation ) {
				e.browserEvent.stopPropagation();
			}
			else {
				e.browserEvent.cancelBubble = true;
			}

			if ( e.browserEvent.preventDefault ) {
				e.browserEvent.preventDefault();
			}
			else {
				e.browserEvent.returnValue = false;
			}

			e.returnValue = false;

			if ( Ext.isIE ) {
				e.browserEvent.keyCode = 0;
				e.browserEvent.which = 0;
			}

			switch (e.getKey()) {
				case Ext.EventObject.J:
					current_window.hide();
				break;

				case Ext.EventObject.C:
					current_window.doSave();
				break;
			}
		},
		key: [
			Ext.EventObject.C,
			Ext.EventObject.J
		],
		scope: this,
		stopEvent: false
	}],
	layout: 'form',
	modal: true,
	onHide: Ext.emptyFn,
	plain: true,
	resizable: false,
	setKolvo: function(field, newValue, ed) {
		var base_form = this.findById('SmpFarmacyAddForm').getForm();
		
		if (base_form.findField('Drug_Fas').getValue()>0) {
			if (ed==true){
				base_form.findField('CmpFarmacyBalanceAddHistory_RashCount').setValue((newValue/base_form.findField('Drug_Fas').getValue()).toFixed(4));
			}
			else {
				base_form.findField('CmpFarmacyBalanceAddHistory_RashEdCount').setValue((base_form.findField('Drug_Fas').getValue()*newValue).toFixed(2));
			}
		}
	},
	show: function() {
		sw.Promed.swSmpFarmacyAddDrugWindow.superclass.show.apply(this, arguments);
		var base_form = this.findById('SmpFarmacyAddForm').getForm();
		this.findById('SmpFarmacyAddForm').getForm().reset();
		var form = this;
		
		this.action = null;
		this.callback = Ext.emptyFn;
		this.documentUcStrMode = 'income';
		this.onHide = Ext.emptyFn;
		this.center();
		
		if ( !arguments[0] ) {
			sw.swMsg.alert(lang['oshibka'], lang['otsutstvuyut_neobhodimyie_parametryi'], function() { this.hide(); }.createDelegate(this) );
			return false;
		}
		this.clearValues();
		base_form.setValues(arguments[0]);
		base_form.isFirst = 1;
		
		base_form.findField('Drug_id').getStore().removeAll();

		if ( arguments[0].callback ) {
			this.callback = arguments[0].callback;
		}
		
		if ( arguments[0].owner ) {
			this.owner = arguments[0].owner;
		}
				
		if ( arguments[0].onHide ) {
			this.onHide = arguments[0].onHide;
		}
		base_form.findField('Drug_id').getStore().baseParams = {
			mode: this.documentUcStrMode
		}
		
		base_form.findField('DrugPrepFas_id').getStore().baseParams = {
			mode: this.documentUcStrMode
		}
		
//		this.getLoadMask().show();
		
		
		base_form.findField('CmpFarmacyBalanceAddHistory_RashEdCount').focus(true, 0);
		base_form.findField('DrugPrepFas_id').focus(true, 500);
		
	},
	split: true,
	width: 700
});
