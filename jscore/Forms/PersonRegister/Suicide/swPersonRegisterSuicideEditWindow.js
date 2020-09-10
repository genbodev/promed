/**
 * swPersonRegisterSuicideEditWindow - Форма просмотра/редактирования записи регистра лиц, совершивших суицидальные попытки
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      PersonRegister
 * @access       public
 * @copyright    Copyright (c) 2009-2015 Swan Ltd.
 * @author       Alexander Chebukin
 * @version      07.2016
 */

sw.Promed.swPersonRegisterSuicideEditWindow = Ext.extend(sw.Promed.BaseForm, 
{
	title: lang['zapis_registra_dobavlenie'],
	PersonRegisterType_SysNick: 'suicide',
	MorbusType_SysNick: 'suicide',
	autoHeight: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	formMode: 'remote',
	formStatus: 'edit',
	layout: 'form',
	modal: true,
	width: 750,
	doSave: function(options)
	{
		if ( typeof options != 'object' ) {
			options = new Object();
		}

		if ( this.formStatus == 'save' ) {
			return false;
		}
		
		var win = this;
		this.formStatus = 'save';
		
		var form = this.FormPanel;
		var base_form = form.getForm();
		var params = new Object();
		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.formStatus = 'edit';
					form.getFirstInvalidEl().focus(false);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
		loadMask.show();
		
		params.PersonRegister_setDate = Ext.util.Format.date(base_form.findField('PersonRegister_setDate').getValue(), 'd.m.Y');
		params.Diag_id = base_form.findField('Diag_id').getValue();
		params.MedPersonal_iid = base_form.findField('MedPersonal_iid').getValue();
		params.PersonRegister_disDate = Ext.util.Format.date(base_form.findField('PersonRegister_disDate').getValue(), 'd.m.Y');
		params.PersonRegisterOutCause_id = base_form.findField('PersonRegisterOutCause_id').getValue();
		params.MedPersonal_did = base_form.findField('MedPersonal_did').getValue();
		
		if (this.fromSvid) {
			base_form.findField('PersonRegister_disDate').setValue(params.PersonRegister_setDate);
			params.PersonRegister_disDate = win.InformationPanel.getFieldValue('Person_deadDT') || params.PersonRegister_setDate;
			base_form.findField('PersonRegisterOutCause_id').setValue(1);
			params.PersonRegisterOutCause_id = 1;
			params.autoExcept = 1;
		}
		
		base_form.submit({
			params: params,
			failure: function(result_form, action) {
				win.formStatus = 'edit';
				loadMask.hide();
				if (action.result && action.result.Error_Code) {
					sw.swMsg.alert(lang['oshibka_#']+action.result.Error_Code, action.result.Error_Msg);
				}
			},
			success: function(result_form, action) {
				win.formStatus = 'edit';
				loadMask.hide();
				if (!action.result) {
					return false;
				}
				else if (action.result.success) {
					base_form.findField('PersonRegister_id').setValue(action.result.PersonRegister_id);
					var data = base_form.getValues();
					win.callback(data);
					win.hide();
				}
			}
		});
		
	},
	setFieldsDisabled: function(d) 
	{
		var form = this;
		var base_form = this.findById('FormPanel').getForm();
		
		base_form.items.each(function(f) {
			if (f && (f.xtype!='hidden') && (f.xtype!='fieldset')  && (f.changeDisabled!==false)) {
				f.setDisabled(d);
			}
		});
		form.buttons[0].setDisabled(d);
	},
	show: function() 
	{
		sw.Promed.swPersonRegisterSuicideEditWindow.superclass.show.apply(this, arguments);
		var me = this;
		if (!arguments[0] || !arguments[0].Person_id && !arguments[0].PersonRegister_id){
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.ERROR,
				msg: lang['oshibka_otkryitiya_formyi_ne_ukazanyi_nujnyie_vhodnyie_parametryi'],
				title: lang['oshibka'],
				fn: function() {
					this.hide();
				}
			});
		}
		this.focus();
		this.findById('FormPanel').getForm().reset();
		
		this.center();

		var base_form = me.FormPanel.getForm();
        var diag_combo = base_form.findField('Diag_id');

		diag_combo.lastQuery = lang['stroka_kotoraya_nikogda_ne_smojet_okazatsya_v_lastquery'];
        diag_combo.PersonRegisterType_SysNick = this.PersonRegisterType_SysNick;
        diag_combo.MorbusType_SysNick = null;
		diag_combo.additQueryFilter = '';
		diag_combo.additClauseFilter = '';

		base_form.reset();

		this.formMode = 'remote';
		this.formStatus = 'edit';
		
		this.callback = arguments[0].callback || Ext.emptyFn;
		this.onHide = arguments[0].onHide || Ext.emptyFn;
		this.action = arguments[0].action || 'add';
		this.fromSvid = arguments[0].fromSvid || false;
		arguments[0].Lpu_iid = getGlobalOptions().lpu_id;
		arguments[0].MedPersonal_iid = arguments[0].MedPersonal_id || getGlobalOptions().medpersonal_id;
		arguments[0].PersonRegister_setDate = arguments[0].Evn_setDate || getGlobalOptions().date;
		
		base_form.findField('MorbusType_SysNick').setValue(this.MorbusType_SysNick);
		base_form.findField('PersonRegisterType_id').setValue(62);
		base_form.findField('PersonRegisterType_SysNick').setValue(this.PersonRegisterType_SysNick);
		
		base_form.findField('PersonRegister_setDate').hideContainer();
		base_form.findField('PersonRegister_Alcoholemia').hideContainer();
		base_form.findField('MedPersonal_iid').hideContainer();
		base_form.findField('PersonRegister_disDate').hideContainer();
		base_form.findField('PersonRegisterOutCause_id').hideContainer();
		base_form.findField('MedPersonal_did').hideContainer();
		
		base_form.findField('PersonRegister_setDate').setAllowBlank(true);
		base_form.findField('Diag_id').setAllowBlank(true);
		base_form.findField('MedPersonal_iid').setAllowBlank(true);
		base_form.findField('PersonRegister_disDate').setAllowBlank(true);
		base_form.findField('PersonRegisterOutCause_id').setAllowBlank(true);
		base_form.findField('MedPersonal_did').setAllowBlank(true);
		
		var loadMask = new Ext.LoadMask(me.getEl(), { msg: LOAD_WAIT });
		loadMask.show();
		
		if (this.action == 'add') {
		
			me.setTitle('Запись регистра: Добавление');
			
			me.setFieldsDisabled(false);
			
			base_form.findField('PersonRegister_setDate').showContainer();
			base_form.findField('PersonRegister_Alcoholemia').showContainer();
			base_form.findField('MedPersonal_iid').showContainer();
			
			base_form.findField('PersonRegister_setDate').setAllowBlank(false);
			base_form.findField('Diag_id').setAllowBlank(false);
			base_form.findField('MedPersonal_iid').setAllowBlank(false);
			
			base_form.setValues(arguments[0]);
			
			me.InformationPanel.load({
				Person_id: base_form.findField('Person_id').getValue()
			});
			base_form.findField('MedPersonal_iid').getStore().load({
				callback: function() {
					base_form.findField('MedPersonal_iid').setValue(base_form.findField('MedPersonal_iid').getValue());
					base_form.findField('MedPersonal_iid').fireEvent('change', base_form.findField('MedPersonal_iid'), base_form.findField('MedPersonal_iid').getValue());
				}.createDelegate(this)
			});
			log(arguments[0].MedPersonal_id);
			log(!!arguments[0].MedPersonal_id);
			if (!!arguments[0].MedPersonal_id) {
				base_form.findField('MedPersonal_iid').disable();
			}
			var diag_id = base_form.findField('Diag_id').getValue();
			if ( diag_id != null && diag_id.toString().length > 0 ) {
				base_form.findField('Diag_id').getStore().load({
					callback: function() {
						base_form.findField('Diag_id').getStore().each(function(record) {
							if ( record.get('Diag_id') == diag_id ) {
								base_form.findField('Diag_id').fireEvent('select', base_form.findField('Diag_id'), record, 0);
							}
						});
					},
					params: { where: "where DiagLevel_id = 4 and Diag_id = " + diag_id }
				});
			}
			loadMask.hide();
			me.syncShadow();
			
		} 
		else {
		
			base_form.findField('PersonRegister_disDate').showContainer();
			base_form.findField('PersonRegisterOutCause_id').showContainer();
			
			Ext.Ajax.request({
				failure:function () {
					sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_poluchit_dannyie_s_servera']);
					me.getLoadMask().hide();
				},
				params: {PersonRegister_id: arguments[0].PersonRegister_id},
				success: function (response) {
					var result = Ext.util.JSON.decode(response.responseText);
					if (!result[0]) { return false; }
					base_form.setValues(result[0]);
					me.InformationPanel.load({
						Person_id: base_form.findField('Person_id').getValue()
					});
					base_form.findField('MedPersonal_iid').getStore().load({
						callback: function() {
							base_form.findField('MedPersonal_iid').setValue(base_form.findField('MedPersonal_iid').getValue());
							base_form.findField('MedPersonal_iid').fireEvent('change', base_form.findField('MedPersonal_iid'), base_form.findField('MedPersonal_iid').getValue());
						}.createDelegate(this)
					});
					base_form.findField('MedPersonal_did').getStore().load({
						callback: function() {
							base_form.findField('MedPersonal_did').setValue(base_form.findField('MedPersonal_did').getValue());
							base_form.findField('MedPersonal_did').fireEvent('change', base_form.findField('MedPersonal_did'), base_form.findField('MedPersonal_did').getValue());
						}.createDelegate(this)
					});
					var diag_id = base_form.findField('Diag_id').getValue();
					if ( diag_id != null && diag_id.toString().length > 0 ) {
						base_form.findField('Diag_id').getStore().load({
							callback: function() {
								base_form.findField('Diag_id').getStore().each(function(record) {
									if ( record.get('Diag_id') == diag_id ) {
										base_form.findField('Diag_id').fireEvent('select', base_form.findField('Diag_id'), record, 0);
									}
								});
							},
							params: { where: "where DiagLevel_id = 4 and Diag_id = " + diag_id }
						});
					}
					
					switch (me.action) {
						case 'edit':
						
							me.setTitle('Запись регистра: Редактирование');
							me.setFieldsDisabled(false);
							
							base_form.findField('PersonRegister_setDate').showContainer();
							base_form.findField('PersonRegister_Alcoholemia').showContainer();
							base_form.findField('MedPersonal_iid').showContainer();
							
							base_form.findField('PersonRegister_setDate').setAllowBlank(false);
							base_form.findField('Diag_id').setAllowBlank(false);
							base_form.findField('MedPersonal_iid').setAllowBlank(false);
							
							base_form.findField('PersonRegister_disDate').disable();
							base_form.findField('PersonRegisterOutCause_id').disable();
							break;
							
						case 'view':				
							me.setTitle('Запись регистра: Просмотр');			
							me.setFieldsDisabled(true);	
							
							base_form.findField('PersonRegister_setDate').showContainer();
							base_form.findField('PersonRegister_Alcoholemia').showContainer();
							base_form.findField('MedPersonal_iid').showContainer();				
							break;
							
						case 'person_register_dis':				
							me.setTitle('Исключение записи из регистра');
							me.setFieldsDisabled(false);
							
							base_form.findField('PersonRegister_disDate').showContainer();
							base_form.findField('PersonRegisterOutCause_id').showContainer();
							base_form.findField('MedPersonal_did').showContainer();
							
							base_form.findField('PersonRegister_disDate').setAllowBlank(false);
							base_form.findField('PersonRegisterOutCause_id').setAllowBlank(false);
							base_form.findField('MedPersonal_did').setAllowBlank(false);
							
							base_form.findField('PersonRegisterOutCause_id').lastQuery = '';
							base_form.findField('PersonRegisterOutCause_id').getStore().clearFilter();
							base_form.findField('PersonRegisterOutCause_id').getStore().filterBy(function(rec) {
								return (rec.get('PersonRegisterOutCause_id').inlist([1, 2]));
							});
							
							base_form.findField('Diag_id').disable();
							base_form.findField('PersonRegister_disDate').setValue(getGlobalOptions().date);
							base_form.findField('Lpu_did').setValue(getGlobalOptions().lpu_id);
							base_form.findField('MedPersonal_did').setValue(getGlobalOptions().medpersonal_id);	
							break;			
					}
		
					me.getLoadMask().hide();
					me.syncShadow();

				},
				url: '/?c=PersonRegister&m=load'
			});
		}

	},
	initComponent: function() 
	{
		var win = this;
		
		this.InformationPanel = new sw.Promed.PersonInformationPanelShort({
			region: 'north'
		});
		this.FormPanel = new Ext.form.FormPanel(
		{	
			frame: true,
			layout: 'form',
			region: 'center',
			id: 'FormPanel',
			bodyStyle: 'padding: 5px',
			autoHeight: true,
			labelAlign: 'right',
			labelWidth: 260,
			url:'/?c=PersonRegister&m=save',
			items: 
			[{
				region: 'north',
				layout: 'form',
				xtype: 'panel',
				items: [{
					name: 'PersonRegister_id',
					xtype: 'hidden',
					value: 0
				}, {
					name: 'Person_id',
					xtype: 'hidden'
				}, {
					name: 'PersonRegisterType_SysNick',
					xtype: 'hidden'
				}, {
					name: 'PersonRegisterType_id',
					xtype: 'hidden'
				}, {
					name: 'MorbusType_SysNick',
					xtype: 'hidden'
				}, {
					name: 'Lpu_iid',
					xtype: 'hidden'
				}, {
					name: 'Lpu_did',
					xtype: 'hidden'
				}, {
					fieldLabel: 'Дата совершения суицидальной попытки',
					name: 'PersonRegister_setDate',
					xtype: 'swdatefield',
					plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
					maxValue: getGlobalOptions().date
				}, {
					minChars: 0,
					triggerAction: 'all',
					fieldLabel: 'Способ совершения суицидальной попытки',
					hiddenName: 'Diag_id',
					listWidth: 620,
					valueField: 'Diag_id',
					width: 450,
					xtype: 'swdiagcombo'
				}, {
					fieldLabel: 'Наличие алкоголя в крови, моче (‰)',
					name: 'PersonRegister_Alcoholemia',
					xtype: 'numberfield'
				}, {
					fieldLabel: lang['vrach'],
					hiddenName: 'MedPersonal_iid',
					listWidth: 750,
					width: 350,
					xtype: 'swmedpersonalcombo'
				}, {
					fieldLabel: 'Дата исключения из регистра',
					name: 'PersonRegister_disDate',
					xtype: 'swdatefield',
					plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
					maxValue: getGlobalOptions().date
				}, {
					fieldLabel: lang['prichina_isklyucheniya'],
					hiddenName: 'PersonRegisterOutCause_id',
					xtype: 'swcommonsprcombo',
					sortField:'PersonRegisterOutCause_Code',
					comboSubject: 'PersonRegisterOutCause',
					width: 350
				}, {
					fieldLabel: lang['vrach'],
					hiddenName: 'MedPersonal_did',
					listWidth: 750,
					width: 350,
					xtype: 'swmedpersonalcombo'
				}]
			}]
		});
		Ext.apply(this, 
		{	
			buttons: 
			[{
				handler: function() {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'save16',
				text: BTN_FRMSAVE
			}, 
			{
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() 
				{
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],
			items: [this.InformationPanel, this.FormPanel]
		});
		sw.Promed.swPersonRegisterSuicideEditWindow.superclass.initComponent.apply(this, arguments);
	}
});