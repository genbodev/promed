/**
* swHospDirectionConfirmWindow - ФОРМА ПОДТВЕРЖДЕНИЯ ГОСПИТАЛИЗАЦИИ
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
* @package      Hospital
* @access       public
* @copyright    Copyright (c) 2009 - 2010 Swan Ltd.
* @author       Alexander Permyakov (alexpm)
* @version      11.02.2011
* @comment      Префикс для id компонентов HDCW (HospDirectionConfirmWindow)
**/
/*NO PARSE JSON*/
sw.Promed.swHospDirectionConfirmWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swHospDirectionConfirmWindow',
	objectSrc: '/jscore/Forms/Hospital/swHospDirectionConfirmWindow.js',

	title: lang['vyi_podtverjdaete_gospitalizatsiyu_etogo_patsienta'],
	id: 'HospDirectionConfirmWindow',
	width: 700,
	autoHeight: true,
	border: false,
	buttonAlign: 'right',
	closable: true,
	closeAction: 'hide',
	modal: true,
	plain: false,
	resizable: false,

	onConfirm: Ext.emptyFn,
	record: null,
	
	show: function() {
		sw.Promed.swHospDirectionConfirmWindow.superclass.show.apply(this, arguments);
		this.center();
		
		var base_form = this.findById('HDCW_form').getForm();
		base_form.reset();
		this.onConfirm = Ext.emptyFn;
		this.record = null;
		if ( !arguments[0] || !arguments[0].onConfirm || !arguments[0].record) {
			sw.swMsg.alert(lang['oshibka'], lang['otsutstvuyut_neobhodimyie_parametryi'], function() { this.hide(); }.createDelegate(this) );
			return false;
		}
		this.onConfirm = arguments[0].onConfirm;
		this.record = arguments[0].record;
		this.userLpuSectionProfile_id = arguments[0].userLpuSectionProfile_id || null;
		if (arguments[0].MedPersonal_FIO) {
			var info = arguments[0].MedPersonal_FIO;
			if (arguments[0].PostMed_Name) {
				info = info +' '+ arguments[0].PostMed_Name;
			}
			base_form.findField('MedPerson_Info').setValue(info);
		} else {
			this.getMedPersonInfo(base_form);
		}

		base_form.findField('Person_Fio').setValue(this.record.data.Person_Fio);
		base_form.findField('EvnDirection_Num').setValue(this.record.data.EvnDirection_Num);
		base_form.findField('LpuSectionProfile_Name').setValue(this.record.data.LpuSectionProfile_Name);
		base_form.findField('Hospitalisation_setDate').setValue(getGlobalOptions().date);
		base_form.findField('Hospitalisation_setTime').setValue(Ext.util.Format.date(new Date(), 'H:i'));
		base_form.findField('Hospitalisation_setDate').focus(true, 100);
	},
	getMedPersonInfo: function(base_form) {
		var form = this;
		form.getLoadMask(LOAD_WAIT).show();
		Ext.Ajax.request(
		{
			url: '/?c=MedPersonal&m=getMedPersonInfo',
			callback: function(options, success, response) 
			{
				form.getLoadMask().hide();
				if (success)
				{
					var response_obj = Ext.util.JSON.decode(response.responseText);
					if ( response_obj && response_obj[0] )
					{
						var info = response_obj[0].MedPersonal_FIO;
						if (response_obj[0].PostMed_Name) {
							info = info +' '+ response_obj[0].PostMed_Name;
						}
						base_form.findField('MedPerson_Info').setValue(info);
					}
				}
			},
			params: {MedPersonal_id: getGlobalOptions().medpersonal_id}
		});
	},
	setConfirmed: function() {
		var form = this;
		var base_form = this.findById('HDCW_form').getForm();
		var params = {
			EvnDirection_id: form.record.data.EvnDirection_id,
			Hospitalisation_setDT: Ext.util.Format.date(base_form.findField('Hospitalisation_setDate').getValue(),'d.m.Y') +' '+ (base_form.findField('Hospitalisation_setTime').getValue() || '00:00')
		};
		form.getLoadMask(LOAD_WAIT).show();
		Ext.Ajax.request(
		{
			url: '/?c=EvnDirection&m=setConfirmed',
			callback: function(options, success, response) 
			{
				form.getLoadMask().hide();
				if (success)
				{
					var response_obj = Ext.util.JSON.decode(response.responseText);
					if (response_obj.success)
					{
						form.onConfirm(params);
						form.hide();
					}
					
				}
			},
			params: params
		});
	},
	confirm: function() {
		var form = this;
		if (form.userLpuSectionProfile_id && form.record.data.LpuSectionProfile_id != form.userLpuSectionProfile_id) {
			sw.swMsg.show({
				title: lang['podtverjdenie'],
				msg: lang['napravlenie_ne_sootvetstvuet_profilyu_vashego_otdeleniya_vyi_vse_ravno_hotite_gospitalizirovat_patsienta'],
				buttons: Ext.Msg.YESNO,
				fn: function ( buttonId ) {
					if ( buttonId != 'yes' )
					{
						form.hide();
					}
					else
					{
						form.setConfirmed();
					}
				}
			});
		} else {
			form.setConfirmed();
		}
	},

	initComponent: function() {
		var current_window = this;
		Ext.apply(this, {
			buttons: [{
				handler : function(button, event) {
					this.confirm();
				}.createDelegate(this),
				iconCls : 'ok16',
				tabIndex: TABINDEX_ARMSTAC+3,
				text: lang['ok']
			}, {
				text: '-'
			},
			{
				handler: function(button, event) {
					this.hide();
				}.createDelegate(this),
				onTabAction: function () {
					this.findById('HDCW_form').getForm().findField('Hospitalisation_setDate').focus(true);
				}.createDelegate(this),
				iconCls : 'cancel16',
				tabIndex: TABINDEX_ARMSTAC+4,
				text: BTN_FRMCANCEL
			}],
			items: [ new sw.Promed.FormPanel({
				autoHeight: true,
				border: false,
				frame: true,
				id: 'HDCW_form',
				labelWidth: 150,
				layout: 'form',
				style: 'padding: 3px',
				items: [{
					name: 'Person_Fio',
					width: 450,
					disabled: true,
					fieldLabel: lang['patsient'],
					xtype: 'textfield'
				}, {
					name: 'EvnDirection_Num',
					width: 450,
					disabled: true,
					autoCreate: {tag: "input", type: "text", maxLength: "6", autocomplete: "off"},
					fieldLabel: lang['napravlenie_№'],
					xtype: 'textfield'
				}, {
					name: 'LpuSectionProfile_Name',
					width: 450,
					disabled: true,
					fieldLabel: lang['profil'],
					xtype: 'textfield'
				}, {
					xtype: 'swdatefield',
					allowBlank: false,
					format: 'd.m.Y',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
					name: 'Hospitalisation_setDate',
					tabIndex: TABINDEX_ARMSTAC+1,
					fieldLabel: lang['data_podtverjdeniya']
				}, {
					xtype: 'textfield',
					format: 'H:i',
					width: 40,
					plugins: [ new Ext.ux.InputTextMask('99:99', false) ],
					name: 'Hospitalisation_setTime',
					tabIndex: TABINDEX_ARMSTAC+2,
					fieldLabel: lang['vremya_podtverjdeniya']
				}, {
					name: 'MedPerson_Info',
					width: 450,
					disabled: true,
					fieldLabel: lang['med_rabotnik'],
					xtype: 'textfield'
				}]
			})]
		});
		sw.Promed.swHospDirectionConfirmWindow.superclass.initComponent.apply(this, arguments);
	}
});