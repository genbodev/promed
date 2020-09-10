/**
* swEvnPLBlankSettingsWindow - окно выбора типа услуги.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Polka
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage@swan.perm.ru)
* @version      19.08.2010
* @comment      Префикс для id компонентов EPLBSF (EvnPLBlankSettingsForm)
*/

sw.Promed.swEvnPLBlankSettingsWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: true,
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	draggable: true,
	id: 'EvnPLBlankSettingsWindow',
	initComponent: function() {
		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.printEvnPLBlank();
				}.createDelegate(this),
				iconCls: 'print16',
				onShiftTabAction: function() {
					this.findById('EvnPLBlankSettingsForm').getForm().findField('ServiceType_id').focus(true);
				}.createDelegate(this),
				onTabAction: function() {
					this.buttons[this.buttons.length - 1].focus();
				}.createDelegate(this),
				tabIndex: TABINDEX_EPLBSF + 5,
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
					this.buttons[0].focus();
				}.createDelegate(this),
				onTabAction: function() {
					this.findById('EvnPLBlankSettingsForm').getForm().findField('MedPersonal_id').focus(true);
				}.createDelegate(this),
				tabIndex: TABINDEX_EPLBSF + 6,
				text: BTN_FRMCANCEL
			}],
			items: [ new Ext.form.FormPanel({
				border: false,
				frame: true,
				id: 'EvnPLBlankSettingsForm',
				items: [{
					codeField: 'MedPersonal_TabCode',
					displayField: 'MedPersonal_Fio',
					enableKeyEvents: true,
					fieldLabel: lang['spetsialist'],
					hiddenName: 'MedPersonal_id',
					listeners: {
						'keydown': function(inp, e) {
							if ( e.shiftKey == true && e.getKey() == Ext.EventObject.TAB ) {
								e.stopEvent();
								this.buttons[this.buttons.length - 1].focus();
							}
						}.createDelegate(this)
					},
					listWidth: 500,
					store: new Ext.data.Store({
						autoLoad: false,
						reader: new Ext.data.JsonReader({
							id: 'MedPersonal_id'
						}, [
							{ name: 'MedPersonal_id', mapping: 'MedPersonal_id' },
							{ name: 'MedPersonal_TabCode', mapping: 'MedPersonal_TabCode' },
							{ name: 'MedPersonal_Fio', mapping: 'MedPersonal_Fio' }
						]),
						sortInfo: {
							field: 'MedPersonal_Fio'
						},
						url: C_MP_LOADLIST
					}),
					tabIndex: TABINDEX_EPLBSF + 1,
					tpl: new Ext.XTemplate(
						'<tpl for="."><div class="x-combo-list-item">',
						'<table style="border: 0;"><td style="width: 50px"><font color="red">{MedPersonal_TabCode}</font></td><td><h3>{MedPersonal_Fio}&nbsp;</h3></td></tr></table>',
						'</div></tpl>'
					),
					valueField: 'MedPersonal_id',
					width: 350,
					xtype: 'swbaselocalcombo'
				}, {
					comboSubject: 'LpuSectionProfile',
					fieldLabel: lang['profil'],
					hiddenName: 'LpuSectionProfile_id',
					tabIndex: TABINDEX_EPLBSF + 2,
					width: 350,
					xtype: 'swcommonsprcombo'
				}, {
					useCommonFilter: true,
					tabIndex: TABINDEX_EPLBSF + 3,
					width: 350,
					xtype: 'swpaytypecombo'
				}, {
					fieldLabel: lang['mesto_obslujivaniya'],
					hiddenName: 'ServiceType_id',
					tabIndex: TABINDEX_EPLBSF + 4,
					width: 350,
					xtype: 'swservicetypecombo'
				}],
				labelAlign: 'right',
				labelWidth: 130
			})]
		});
		sw.Promed.swEvnPLBlankSettingsWindow.superclass.initComponent.apply(this, arguments);
	},
	listeners: {
		'hide': function(win) {
			win.onHide();
		}
	},
	maximizable: false,
	modal: true,
	onHide: Ext.emptyFn,
	printEvnPLBlank: function() {
		var base_form = this.findById('EvnPLBlankSettingsForm').getForm();
		var params = new Object();

		params.type = this.printType;

		if ( base_form.findField('LpuSectionProfile_id').getValue() ) {
			params.lpuSectionProfileId = base_form.findField('LpuSectionProfile_id').getValue();
		}

		if ( base_form.findField('MedPersonal_id').getValue() ) {
			params.medPersonalId = base_form.findField('MedPersonal_id').getValue();
		}

		if ( base_form.findField('PayType_id').getValue() ) {
			params.payTypeId = base_form.findField('PayType_id').getValue();
		}

		if ( this.personId ) {
			params.personId = this.personId;
		}

		if ( base_form.findField('ServiceType_id').getValue() ) {
			params.serviceTypeId = base_form.findField('ServiceType_id').getValue();
		}

		if ( this.before2015 ) {
			params.before2015 = true;
		}

		printEvnPLBlank(params);

		return true;
	},
	plain: true,
	resizable: false,
	show: function() {
		sw.Promed.swEvnPLBlankSettingsWindow.superclass.show.apply(this, arguments);

		this.onHide = Ext.emptyFn;
		this.personId = null;
		this.printType = 'EvnPL';
		this.before2015 = false;

		var base_form = this.findById('EvnPLBlankSettingsForm').getForm();
		base_form.reset();

		var PayType_SysNick = 'oms';
		switch ( getRegionNick() ) {
			case 'by': PayType_SysNick = 'besus'; break;
			case 'kz': PayType_SysNick = 'Resp'; break;
		}
		base_form.findField('PayType_id').setFieldValue('PayType_SysNick', PayType_SysNick);
		base_form.findField('ServiceType_id').setFieldValue('ServiceType_SysNick', 'polka');

		base_form.findField('MedPersonal_id').getStore().removeAll();
		base_form.findField('MedPersonal_id').getStore().loadData(getMedPersonalListFromGlobal());
		if(arguments[0].MedPersonal_id) { //В рамках задачи 19752
			var MedPersonal_id = arguments[0].MedPersonal_id; //Получаем id-шник врача, у которого назначена запись
		}
		else {
			var MedPersonal_id = getGlobalOptions().medpersonal_id; //Если что-то пошло не так, то берем врача, который связан с пользователем
		}
		var index = base_form.findField('MedPersonal_id').getStore().findBy(function(rec) {
			if ( Number(rec.get('MedPersonal_id')) == Number(MedPersonal_id) ) {
				return true;
			}
			else {
				return false;
			}
		});
		var med_personal_record = base_form.findField('MedPersonal_id').getStore().getAt(index);

		if ( med_personal_record ) {
			base_form.findField('MedPersonal_id').setValue(med_personal_record.get('MedPersonal_id'));
		}

		if(arguments[0].LpuSectionProfile_id) { //В рамках задачи 19752
			var LpuSectionProfile_id = arguments[0].LpuSectionProfile_id;
			var index = base_form.findField('LpuSectionProfile_id').getStore().findBy(function(rec) {
				if (Number(rec.get('LpuSectionProfile_id')) == Number(LpuSectionProfile_id)) {
					return true;
				}
				else{
					return false;
				}

			});
			var LpuSectionProfile_record = base_form.findField('LpuSectionProfile_id').getStore().getAt(index);
			if (LpuSectionProfile_record){
				base_form.findField('LpuSectionProfile_id').setValue(LpuSectionProfile_record.get('LpuSectionProfile_id'));
			}
		}

		if ( arguments[0] ) {
			if ( arguments[0].onHide ) {
				this.onHide = arguments[0].onHide;
			}

			if ( arguments[0].personId ) {
				this.personId = arguments[0].personId;
			}

			if ( arguments[0].type ) {
				this.printType = arguments[0].type;
			}

			if ( arguments[0].before2015 ) {
				this.before2015 = arguments[0].before2015;
			}
		}

		base_form.findField('MedPersonal_id').focus(true, 250);
	},
	title: lang['parametryi_pechati_blanka_tap'],
	width: 530
});