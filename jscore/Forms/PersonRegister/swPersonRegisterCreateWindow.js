/**
* swPersonRegisterCreateWindow - Запись регистра: Добавление
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009-2010 Swan Ltd.
* @version      09.2012
*/

sw.Promed.swPersonRegisterCreateWindow = Ext.extend(sw.Promed.BaseForm, 
{
	title: lang['zapis_registra_dobavlenie'],
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
	width: 600,
	height: 200,
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
		var PersonRegisterType_SysNick = base_form.findField('PersonRegisterType_SysNick').getValue();
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
		if(
			!Ext.isEmpty(base_form.findField('Direction_setDate').getValue()) 
			&& base_form.findField('Direction_setDate').getValue() > base_form.findField('PersonRegister_setDate').getValue()
		){
			sw.swMsg.show(
			{
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.ERROR,
				msg: 'Дата направления не может быть позже даты включения в регистр',
				title: lang['oshibka'],
				fn: function() {
					this.formStatus = 'edit';
				}.createDelegate(this)
			});
			return false;
		}
		
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
		loadMask.show();

		if (options.ignoreCheckAnotherDiag) {
			params.ignoreCheckAnotherDiag = 1;
		}
		
		params.MedPersonal_iid = base_form.findField('MedPersonal_iid').getValue();
		params.Lpu_iid = base_form.findField('Lpu_iid').getValue();
		
		base_form.submit({
			params: params,
			failure: function(result_form, action) 
			{
				win.formStatus = 'edit';
				loadMask.hide();
				if (action.result) 
				{
					if (action.result.Error_Code)
					{
						sw.swMsg.alert(lang['oshibka_#']+action.result.Error_Code, action.result.Error_Msg);
					}
				}
			},
			success: function(result_form, action) 
			{
				win.formStatus = 'edit';
				loadMask.hide();
				if (!action.result) 
				{
					return false;
				}
				else if (action.result.Alert_Msg && action.result.Alert_Code && action.result.Alert_Code == 'ProfDiag')
				{
					var buttons = {
						yes: lang['vklyuchit_v_registr'],
						cancel: lang['otmena']
					};
					sw.swMsg.show({
						buttons: buttons,
						fn: function( buttonId )
						{
							var mode;
							if ( buttonId == 'yes' )
							{
								options.ignoreCheckAnotherDiag = 1;
								win.doSave(options);
							}
							else
							{
								win.hide();
							}
						},
						msg: action.result.Alert_Msg,
						title: lang['vopros']
					});
				}
				else if (action.result.Alert_Msg) 
				{
					var buttons = {
						yes:  (parseInt(action.result.PersonRegisterOutCause_id) == 3)? lang['novoe'] : lang['da'],
						no: (parseInt(action.result.PersonRegisterOutCause_id) == 3) ? lang['predyiduschee'] : lang['net']
					};
					if (parseInt(action.result.PersonRegisterOutCause_id) == 3) {
						buttons.cancel = lang['otmena'];
					}
					sw.swMsg.show(
					{
						buttons: buttons,
						fn: function( buttonId ) 
						{
							var mode;
							if ( buttonId == 'yes' && action.result.Yes_Mode) 
							{
								mode = action.result.Yes_Mode
							} 
							else if ( buttonId == 'no' && action.result.No_Mode) 
							{
								mode = action.result.No_Mode
							}
							if(mode)
							{
								if ( mode.inlist(['homecoming','relapse']) ) 
								{
									// Вернуть пациента в регистр, удалить дату закрытия заболевания
									sw.Promed.personRegister.back({
										PersonRegister_id: action.result.PersonRegister_id
										,PersonRegister_setDate:base_form.findField('PersonRegister_setDate').getValue().dateFormat('d.m.Y')
										,Diag_id: base_form.findField('Diag_id').getValue()
										,ownerWindow: win
										,callback: function(data) {
											base_form.findField('PersonRegister_id').setValue(action.result.PersonRegister_id);
											var data = base_form.getValues();
											win.callback(data);
											win.hide();
										}
									});
								}
								else
								{
									base_form.findField('Mode').setValue(mode);
									win.doSave();
								}
							}
							else
							{
								win.hide();
							}
						},
						msg: action.result.Alert_Msg,
						title: lang['vopros']
					});
				}
				else if (action.result.success) 
				{
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
		
		base_form.items.each(function(f) 
		{
			if (f && (f.xtype!='hidden') && (f.xtype!='fieldset')  && (f.changeDisabled!==false))
			{
				f.setDisabled(d);
			}
		});
		form.buttons[0].setDisabled(d);
	},
	loadMedPersonalCombo: function()
	{
		var base_form = this.FormPanel.getForm();
		var medpersonal_combo = base_form.findField('MedPersonal_iid');
		var medpersonal_id = medpersonal_combo.getValue();
		var mpParams = {};
		if(this.PersonRegisterType_SysNick == 'orphan' && this.isMzSpecialist()){
			mpParams.Lpu_id = base_form.findField('Lpu_iid').getValue();
			var dirDate = base_form.findField('Direction_setDate').getValue();
			if(Ext.isEmpty(dirDate)){
				dirDate = base_form.findField('PersonRegister_setDate').getValue();
			}
			mpParams.onDate = Ext.util.Format.date(dirDate, 'Y-m-d');
		}
		
		medpersonal_combo.getStore().load({
			params: mpParams,
			callback: function()
			{
				if(medpersonal_id > 0 && medpersonal_combo.getStore().getById(medpersonal_id)){
					medpersonal_combo.setValue(medpersonal_id);
				} else {
					medpersonal_combo.clearValue();
				}
				medpersonal_combo.fireEvent('change', medpersonal_combo, medpersonal_id);
			}.createDelegate(this)
		});
	},
	show: function() 
	{
		sw.Promed.swPersonRegisterCreateWindow.superclass.show.apply(this, arguments);
		var me = this;
		if (!arguments[0] || !arguments[0].PersonRegisterType_SysNick || !arguments[0].Person_id)
		{
			sw.swMsg.show(
			{
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
        diag_combo.PersonRegisterType_SysNick = null;
        diag_combo.MorbusType_SysNick = null;

		base_form.reset();

		this.formMode = 'remote';
		this.formStatus = 'edit';
		
		this.callback = arguments[0].callback || Ext.emptyFn;
		this.onHide = arguments[0].onHide || Ext.emptyFn;
		arguments[0].Lpu_iid = getGlobalOptions().lpu_id;
		arguments[0].MedPersonal_iid = getGlobalOptions().medpersonal_id;
		arguments[0].PersonRegister_setDate = getGlobalOptions().date;
		
		base_form.setValues(arguments[0]);

		this.onChangeMorbusProfDiag();
		this.PersonRegisterType_SysNick = base_form.findField('PersonRegisterType_SysNick').getValue();
		var loadMask = new Ext.LoadMask(this.getEl(),{msg: LOAD_WAIT});
		loadMask.show();
		
		base_form.findField('MedPersonal_iid').getStore().load({
			callback: function()
			{
				base_form.findField('MedPersonal_iid').setValue(base_form.findField('MedPersonal_iid').getValue());
				base_form.findField('MedPersonal_iid').fireEvent('change', base_form.findField('MedPersonal_iid'), base_form.findField('MedPersonal_iid').getValue());
			}.createDelegate(this)
		});
        me.InformationPanel.load({
			Person_id: base_form.findField('Person_id').getValue()
		});

        switch (base_form.findField('PersonRegisterType_SysNick').getValue()) {
            default:
                diag_combo.PersonRegisterType_SysNick = base_form.findField('PersonRegisterType_SysNick').getValue();
                diag_combo.MorbusType_SysNick = base_form.findField('MorbusType_SysNick').getValue() || null;
                diag_combo.additQueryFilter = '';
                diag_combo.additClauseFilter = '';
                break;
        }
		
		var diag_id = arguments[0].Diag_id;
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
		
		if ( base_form.findField('PersonRegisterType_SysNick').getValue() == 'large family' ) {
			base_form.findField('Diag_id').hideContainer();
		} else {
			base_form.findField('Diag_id').showContainer();
		}

		base_form.findField('Diag_id').MorbusProfDiag_id = null;

		if ( base_form.findField('PersonRegisterType_SysNick').getValue() == 'prof' ) {
			base_form.findField('MorbusProfDiag_id').showContainer();
			base_form.findField('Lpu_iid').showContainer();
			base_form.findField('MorbusProfDiag_id').setAllowBlank(false);
			base_form.findField('Lpu_iid').setAllowBlank(false);
		} else {
			base_form.findField('MorbusProfDiag_id').hideContainer();
			base_form.findField('Lpu_iid').hideContainer();
			base_form.findField('MorbusProfDiag_id').setAllowBlank(true);
			base_form.findField('Lpu_iid').setAllowBlank(true);
		}

		if ( base_form.findField('PersonRegisterType_SysNick').getValue() == 'orphan' && this.isMzSpecialist() ) {
			base_form.findField('Direction_setDate').showContainer();
			base_form.findField('Lpu_iid').showContainer();
			base_form.findField('Lpu_iid').enable();
			base_form.findField('Lpu_iid').setAllowBlank(false);
			base_form.findField('MedPersonal_iid').enable();
		} else {
			base_form.findField('Direction_setDate').hideContainer();
			base_form.findField('Lpu_iid').setAllowBlank(true);
			base_form.findField('Lpu_iid').hideContainer();
			base_form.findField('Lpu_iid').disable();
			base_form.findField('MedPersonal_iid').disable();
		}

		this.syncShadow();
		
		loadMask.hide();

	},
	onChangeMorbusProfDiag: function()
	{
		var base_form = this.FormPanel.getForm();
		if ( base_form.findField('PersonRegisterType_SysNick').getValue() == 'prof' ) {
			base_form.findField('Diag_id').clearValue();
			base_form.findField('Diag_id').MorbusProfDiag_id = base_form.findField('MorbusProfDiag_id').getValue();

			var diag_ids = [];
			if (!Ext.isEmpty(base_form.findField('MorbusProfDiag_id').getValue()) && !Ext.isEmpty(base_form.findField('MorbusProfDiag_id').getFieldValue('Diag_ids'))) {
				diag_ids = base_form.findField('MorbusProfDiag_id').getFieldValue('Diag_ids').split(', ');
			}

			if (diag_ids && diag_ids.length > 0) {
				base_form.findField('Diag_id').setBaseFilter(function(rec) {
					return rec.get('Diag_id').inlist(diag_ids);
				});
				base_form.findField('Diag_id').getStore().load({
					params: {where: "where Diag_id = " + diag_ids[0]},
					callback: function() {
						base_form.findField('Diag_id').setValue(diag_ids[0]);
						base_form.findField('Diag_id').getStore().each(function (rec) {
							if (rec.get('Diag_id') == diag_ids[0]) {
								base_form.findField('Diag_id').fireEvent('select', base_form.findField('Diag_id'), rec, 0);
							}
						});
					}
				});
			} else {
				base_form.findField('Diag_id').clearBaseFilter();
				base_form.findField('Diag_id').clearValue();
			}
		}
	},
	isMzSpecialist: function()
	{
		return (haveArmType('minzdravdlo') || haveArmType('spec_mz') || haveArmType('mzchieffreelancer'));
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
			labelWidth: 200,
			url:'/?c=PersonRegister&m=create',
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
					name: 'Mode',
					xtype: 'hidden',
					value: null
				}, {
					name: 'Person_id',
					xtype: 'hidden'
				}, {
					name: 'PersonRegisterType_SysNick',
					xtype: 'hidden'
				}, {
					name: 'MorbusType_SysNick',
					xtype: 'hidden'
				}, {
					name: 'Morbus_id',
					xtype: 'hidden'
				}, {
					fieldLabel: lang['data_napravleniya'],
					name: 'Direction_setDate',
					xtype: 'swdatefield',
					plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
					maxValue: getGlobalOptions().date,
					listeners: {
						'change': function(combo,newVal){
							if(!Ext.isEmpty(newVal) && this.PersonRegisterType_SysNick == 'orphan' && this.isMzSpecialist()){
								this.loadMedPersonalCombo();
							}
						}.createDelegate(this)
					}
				}, {
					allowBlank: false,
					fieldLabel: lang['data_vklyucheniya_v_registr'],
					name: 'PersonRegister_setDate',
					xtype: 'swdatefield',
					plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
					maxValue: getGlobalOptions().date,
					listeners: {
						'change': function(combo,newVal){
							if(!Ext.isEmpty(newVal) && this.PersonRegisterType_SysNick == 'orphan' && this.isMzSpecialist()){
								this.loadMedPersonalCombo();
							}
						}.createDelegate(this)
					}
				}, {
					fieldLabel: lang['zabolevanie'],
					hiddenName: 'MorbusProfDiag_id',
					moreFields: [
						{ name: 'Diag_ids', mapping: 'Diag_ids' }
					],
					listeners: {
						'change': function() {
							win.onChangeMorbusProfDiag();
						}
					},
					editable: true,
					width: 350,
					comboSubject: 'MorbusProfDiag',
					xtype: 'swcommonsprcombo'
				}, {
					allowBlank: false,
					minChars: 0,
					triggerAction: 'all',
					fieldLabel: lang['diagnoz'],
					hiddenName: 'Diag_id',
					listWidth: 620,
					valueField: 'Diag_id',
					width: 350,
					xtype: 'swdiagcombo'
				}, {
					fieldLabel: lang['mo'],
					hiddenName: 'Lpu_iid',
					width: 350,
					xtype: 'swlpucombo',
					listeners: {
						'change': function(combo,newVal){
							if(!Ext.isEmpty(newVal) && this.PersonRegisterType_SysNick == 'orphan' && this.isMzSpecialist()){
								this.loadMedPersonalCombo();
							}
						}.createDelegate(this)
					}
				}, {
					changeDisabled: false,
					disabled: true,
					fieldLabel: lang['vrach'],
					hiddenName: 'MedPersonal_iid',
					listWidth: 750,
					width: 350,
					xtype: 'swmedpersonalcombo',
					anchor: false
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
		sw.Promed.swPersonRegisterCreateWindow.superclass.initComponent.apply(this, arguments);
	}
});