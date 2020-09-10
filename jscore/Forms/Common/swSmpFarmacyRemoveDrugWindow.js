/**
* swSmpFarmacyRemoveDrugWindow - Форма учета прихода медикаментов для СМП
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
sw.Promed.swSmpFarmacyRemoveDrugWindow = Ext.extend(sw.Promed.BaseForm, {
	title:lang['spisanie_medikamenta'],
	autoHeight: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	codeRefresh: true,
	objectName: 'swSmpFarmacyRemoveDrugWindow',
	objectSrc: '/jscore/Forms/Common/swSmpFarmacyRemoveDrugWindow.js',
	doSave: function() {
		var base_form = this.findById('SmpFarmacyRemoveForm').getForm();
		var form = this;
		
		log(base_form.findField('CmpFarmacyBalanceRemoveHistory_DoseCount').getValue()<=0);
//		return false;
		if (base_form.findField('CmpFarmacyBalanceRemoveHistory_DoseCount').getValue()<=0) {
			sw.swMsg.show(
			{
				buttons: Ext.Msg.OK,
				fn: function() 
				{
					base_form.findField('CmpFarmacyBalanceRemoveHistory_DoseCount').focus(true, 0);
				},
				icon: Ext.Msg.WARNING,
				msg: lang['kolichestvo_dozirovok_medikamenta_doljno_byit_bolshe_0'],
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		
		if (base_form.findField('CmpFarmacyBalanceRemoveHistory_DoseCount').getValue()>base_form.findField('maxDoseCount').getValue()) {
			sw.swMsg.show(
			{
				buttons: Ext.Msg.OK,
				fn: function() 
				{
					base_form.findField('CmpFarmacyBalanceRemoveHistory_DoseCount').focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: lang['ukazannoe_kolichestvo_doz_medikamenta_prevyishaet_ukazannoe_v_registre_maksimalnoe_kolichestvo_doz']+base_form.findField('maxDoseCount').getValue(),
				title: lang['prevyishen_predel']
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
		
		
		
		
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
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
				CmpFarmacyBalance_PackRest: base_form.findField('maxPackCount').getValue() - base_form.findField('CmpFarmacyBalanceRemoveHistory_PackCount').getValue(),
				CmpFarmacyBalance_DoseRest: base_form.findField('maxDoseCount').getValue() - base_form.findField('CmpFarmacyBalanceRemoveHistory_DoseCount').getValue()
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
		var base_form = this.findById('SmpFarmacyRemoveForm').getForm();
		base_form.findField('CmpFarmacyBalance_id').setValue(null);
		base_form.findField('DrugTorg_Name').setValue(null);
		base_form.findField('maxDoseCount').setValue(null);
		base_form.findField('maxPackCount').setValue(null);
		base_form.findField('EmergencyTeam_id').setValue(null);
		base_form.findField('DrugUnit_Name').setValue(null);
		base_form.findField('Drug_Fas').setValue(null);		
	},

	id: 'SmpFarmacyRemoveDrugWindow',
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
				text: BTN_FRMCANCEL,
				tooltip: lang['zakryit']
			}],
			items: [ new Ext.form.FormPanel({
				autoScroll: true,
				bodyStyle: 'padding: 0.5em;',
				border: false,
				frame: true,
				id: 'SmpFarmacyRemoveForm',
				items: [
				{
					name: 'CmpFarmacyBalance_id',
					xtype: 'hidden'
				},
				{
					name: 'Drug_id',
					xtype: 'hidden'
				},
				{ 
					name: 'DrugTorg_Name',
					fieldLabel: lang['nazvanie'],
					anchor: null,
					disabled: true,
					width: 500,
					xtype: 'textfield'
				},
				{ 
					name: 'EmergencyTeam_id',
					id: 'emergencyteam',
//					disabledClass: 'field-disabled',
					allowBlank: false,
					anchor: null,
					width:500,
					xtype: 'swemergencyteamcombo'
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
						labelWidth: 120,
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
							disabled: true,
							fieldLabel: lang['ostatok_ed_doz'],
							name: 'maxDoseCount',
							width: 110,
							xtype: 'textfield'
						}]
					}, {
						border: false,
						labelWidth: 120,
						layout: 'form',
						items: [{
							disabled: true,
							fieldLabel: lang['ostatok_ed_uch'],
							name: 'maxPackCount',
							width: 60,
							xtype: 'numberfield'
						}]
					}]
				},{
					border: false,
					layout: 'column',
					items: [
						{
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
							name: 'CmpFarmacyBalanceRemoveHistory_DoseCount',
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
					},{
						border: false,
						layout: 'form',
						items: [{
//							allowBlank: false,
							allowDecimals: true,
							allowNegative: false,
							decimalPrecision: 4, 
							readOnly: true,
							fieldLabel: lang['kolichestvo_ed_uch'],
							listeners: {
								'change': function(field, newValue, oldValue) 
								{
									this.setKolvo(field, newValue, false);
								}.createDelegate(this)
							},
							minValue: 0,
							name: 'CmpFarmacyBalanceRemoveHistory_PackCount',
							width: 100,
							xtype: 'numberfield'
						}]
					}]
				}],
				labelAlign: 'right',
				labelWidth: 130,
				reader: new Ext.data.JsonReader({
					success: Ext.emptyFn
				}, [
					{name: 'EmergencyTeam_id'},
					{name: 'CmpFarmacyBalance_id'},
					//{ name: 'CmpFarmacyBalanceAddHistory_RashCount' },
					{name: 'CmpFarmacyBalanceRemoveHistory_DoseCount'},
					{name: 'CmpFarmacyBalanceRemoveHistory_PackCount'}				
				]),
				region: 'center',
				trackResetOnLoad: true,
				url: '/?c=CmpCallCard&m=removeSmpFarmacyDrug'
			})]
		});
		sw.Promed.swSmpFarmacyRemoveDrugWindow.superclass.initComponent.apply(this, arguments);
	},
	layout: 'form',
	modal: true,
	onHide: Ext.emptyFn,
	plain: true,
	resizable: false,
	setKolvo: function(field, newValue, ed) {
		var base_form = this.findById('SmpFarmacyRemoveForm').getForm();
		
		if (base_form.findField('Drug_Fas').getValue()>0) {
			if (ed==true){
				base_form.findField('CmpFarmacyBalanceRemoveHistory_PackCount').setValue((newValue/base_form.findField('Drug_Fas').getValue()).toFixed(4));
			} else {
				base_form.findField('CmpFarmacyBalanceRemoveHistory_DoseCount').setValue((base_form.findField('Drug_Fas').getValue()*newValue).toFixed(2));
			}
		}
	},
	show: function() {
		sw.Promed.swSmpFarmacyRemoveDrugWindow.superclass.show.apply(this, arguments);
		var base_form = this.findById('SmpFarmacyRemoveForm').getForm();
		this.findById('SmpFarmacyRemoveForm').getForm().reset();
		var form = this;
		
		this.action = null;
		this.callback = Ext.emptyFn;
		this.documentUcStrMode = 'income';
		this.onHide = Ext.emptyFn;
		this.center();
		
		if ( !arguments[0] || !arguments[0].CmpFarmacyBalance_id || !arguments[0].DrugTorg_Name || !arguments[0].maxDoseCount ) {
			sw.swMsg.alert(lang['oshibka'], lang['otsutstvuyut_neobhodimyie_parametryi'], function() {this.hide();}.createDelegate(this) );
			return false;
		}
		this.clearValues();
		base_form.setValues(arguments[0]);
		base_form.isFirst = 1;
		
		if ( arguments[0].callback ) {
			this.callback = arguments[0].callback;
		}
		
		if ( arguments[0].owner ) {
			this.owner = arguments[0].owner;
		}
				
		if ( arguments[0].onHide ) {
			this.onHide = arguments[0].onHide;
		}
		this.findById('emergencyteam').getStore().load();
		
		base_form.findField('CmpFarmacyBalanceRemoveHistory_DoseCount').focus(true, 0);
		base_form.findField('DrugTorg_Name').focus(true, 0);
		
	},
	split: true,
	width: 700
});
