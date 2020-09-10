/**
* swPersonRegisterExceptWindow - Исключение записи из регистра
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

sw.Promed.swPersonRegisterExceptWindow = Ext.extend(sw.Promed.BaseForm,
{
	title: lang['isklyuchenie_zapisi_iz_registra'],
	//autoHeight: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	formMode: 'remote',
	formStatus: 'edit',
	layout: 'border',
	modal: true,
	width: 650,
	height: 330,
	listeners: {
		hide: function () {
			var	commentContainer =  this.findById('EvnNotifyRegister_OutComment_Container');

			commentContainer.hide();
		}
	},

	doSave: function()
	{
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

		if (
			!Ext.isEmpty(win.InformationPanel.getFieldValue('Person_deadDT'))
			&& base_form.findField('PersonRegister_disDate').getValue().dateFormat('Y-m-d') > win.InformationPanel.getFieldValue('Person_deadDT').dateFormat('Y-m-d')
			&& base_form.findField('PersonRegisterOutCause_id').getValue()!=1
		) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.formStatus = 'edit';
					base_form.findField('PersonRegisterOutCause_id').focus(false);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: lang['data_isklyucheniya_iz_registra_ne_mojet_byit_bolshe_date_smerti_patsienta_esli_prichina_isklyucheniya_ne_smert'],
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		
		if (base_form.findField('PersonRegister_disDate').getValue().dateFormat('Y-m-d') <= new Date(base_form.findField('PersonRegister_setDate').getValue()).dateFormat('Y-m-d')) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.formStatus = 'edit';
					base_form.findField('PersonRegister_disDate').focus(false);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: lang['data_isklyucheniya_iz_registra_ne_mojet_byit_menshe_ili_ravno_date_vklyucheniya_v_registr'],
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		if(
			win.PersonRegisterType_SysNick == 'nolos'
			&& !Ext.isEmpty(base_form.findField('Notify_setDate').getValue()) 
			&& base_form.findField('Notify_setDate').getValue() > base_form.findField('PersonRegister_disDate').getValue()
		){
			sw.swMsg.show(
			{
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.ERROR,
				msg: 'Дата извещения не может быть позже даты исключения из регистра',
				title: lang['oshibka'],
				fn: function() {
					this.formStatus = 'edit';
				}.createDelegate(this)
			});
			return false;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});

		params.MedPersonal_did = base_form.findField('MedPersonal_did').getValue();
		if (String(base_form.findField('MorbusType_SysNick').getValue()).inlist(['crazy','narc'])){
			params.PersonRegisterOutCause_id = base_form.findField('CrazyCauseEndSurveyType_id').getValue();
		}
		if(
			win.PersonRegisterType_SysNick == 'nolos'
			&& !Ext.isEmpty(base_form.findField('Notify_setDate').getValue()) 
		) {
			params.Notify_setDate = Ext.util.Format.date(base_form.findField('Notify_setDate').getValue(),'Y-m-d');
		}
		loadMask.show();
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
				if (action.result && action.result.success)
				{
					win.callback(action.result);
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
	isMzSpecialist: function()
	{
		return (haveArmType('minzdravdlo') || haveArmType('spec_mz') || haveArmType('mzchieffreelancer'));
	},
	loadMedPersonalCombo: function()
	{
		var base_form = this.FormPanel.getForm();
		var medpersonal_combo = base_form.findField('MedPersonal_did');
		var medpersonal_id = medpersonal_combo.getValue();
        medpersonal_combo.getStore().baseParams = {
            Lpu_id: base_form.findField('Lpu_did').getValue()
        };


        if((this.PersonRegisterType_SysNick == 'nolos' || this.PersonRegisterType_SysNick == 'orphan') && this.isMzSpecialist()){
			var dirDate = base_form.findField('Notify_setDate').getValue();
			if(Ext.isEmpty(dirDate)){
				dirDate = base_form.findField('PersonRegister_disDate').getValue();
			}
			medpersonal_combo.getStore().baseParams.onDate = Ext.util.Format.date(dirDate, 'Y-m-d');
		}


        medpersonal_combo.getStore().load({
            callback: function()
            {
                if (medpersonal_combo.getStore().getById(medpersonal_id)) {
                    medpersonal_combo.setValue(medpersonal_id);
                } else {
                    medpersonal_combo.setValue(null);
                }
            }
        });
	},
	show: function()
	{
		sw.Promed.swPersonRegisterExceptWindow.superclass.show.apply(this, arguments);

		var me = this;
		if (!arguments[0] || !arguments[0].PersonRegister_id || !arguments[0].Person_id || !arguments[0].Diag_Name || !arguments[0].PersonRegister_setDate || !arguments[0].PersonRegisterType_SysNick)
		{
			sw.swMsg.show(
			{
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.ERROR,
				msg: lang['oshibka_otkryitiya_formyi_ne_ukazanyi_nujnyie_vhodnyie_parametryi'],
				title: lang['oshibka'],
				fn: function() {
					me.hide();
				}
			});
			return false;
		}		
		var minDate = arguments[0].PersonRegister_setDate;		
		if (!minDate || !minDate.add) {
			sw.swMsg.alert(lang['soobschenie'], lang['nepravilnyiy_format_datyi_vklyucheniya_v_registr']);
			me.hide();
			return false;
		}
		minDate = minDate.add(Date.DAY, 1);
		
		if (false == sw.Promed.personRegister.isAllow(arguments[0].PersonRegisterType_SysNick)) {
			me.hide();
			return false;
		}
		if (arguments[0].PersonRegisterType_SysNick == 'nolos' && false == sw.Promed.personRegister.isVznRegistryOperator()) {
			sw.swMsg.alert('Сообщение', 'Форма "'+ me.title +'" доступна только для пользователей, с указанной группой «Регистр по ВЗН');
			me.hide();
			return false;
		}
		this.focus();

		this.center();

		var base_form = this.FormPanel.getForm();
		base_form.reset();

		this.callback = Ext.emptyFn;
		this.formMode = 'remote';
		this.formStatus = 'edit';
		this.PersonRegisterType_SysNick = null;
		this.callback = arguments[0].callback || Ext.emptyFn;
		this.onHide = arguments[0].onHide || Ext.emptyFn;
		// arguments[0].Lpu_did = (arguments[0].Lpu_did) ? arguments[0].Lpu_did : getGlobalOptions().lpu_id;
		arguments[0].MedPersonal_did = (arguments[0].MedPersonal_did) ? arguments[0].MedPersonal_did : getGlobalOptions().medpersonal_id;
		arguments[0].PersonRegister_disDate = (arguments[0].PersonRegister_disDate) ? arguments[0].PersonRegister_disDate : getGlobalOptions().date;
		base_form.findField('PersonRegister_disDate').minValue = minDate;
		if(arguments[0].MorbusType_SysNick&&arguments[0].MorbusType_SysNick.inlist(['crazy'])){
			base_form.findField('CrazyCauseEndSurveyType_id').setDisabled(false);
			base_form.findField('PersonRegisterOutCause_id').setDisabled(true);
			base_form.findField('CrazyCauseEndSurveyType_id').showContainer();
			base_form.findField('PersonRegisterOutCause_id').hideContainer();
		}else{
			base_form.findField('PersonRegisterOutCause_id').setDisabled(false);
			base_form.findField('CrazyCauseEndSurveyType_id').setDisabled(true);
			base_form.findField('PersonRegisterOutCause_id').showContainer();
			base_form.findField('CrazyCauseEndSurveyType_id').hideContainer();
		}

		arguments[0].Lpu_did = null;

		base_form.setValues(arguments[0]);

		var loadMask = new Ext.LoadMask(this.getEl(),{msg: LOAD_WAIT});
		loadMask.show();

		this.PersonRegisterType_SysNick = base_form.findField('PersonRegisterType_SysNick').getValue();

		// Выбор из справочника МО, действующих на дату исключения из регистра (PersonRegister_disDate).


		if ( base_form.findField('PersonRegisterType_SysNick').getValue() == 'nolos') {
			base_form.findField('EvnVK_id').showContainer();
			base_form.findField('EvnVK_id').setAllowBlank(true);
			base_form.findField('EvnVK_id').setValue(null);
			base_form.findField('EvnVK_id').getStore().removeAll();
			base_form.findField('EvnVK_id').getStore().baseParams = {
				Person_id: base_form.findField('Person_id').getValue()
			};
			base_form.findField('EvnVK_id').getStore().load({});
			base_form.findField('Lpu_did').showContainer();
			base_form.findField('Lpu_did').setAllowBlank(false);
			base_form.findField('MedPersonal_did').setDisabled(false);

			base_form.findField('Lpu_did').lastQuery = '';
			base_form.findField('Lpu_did').getStore().clearFilter();
			// base_form.findField('Lpu_did').getStore().load();


			base_form.findField('Lpu_did').setBaseFilter(function(rec, id) {
				if ( ! Ext.isEmpty(rec.get('Lpu_EndDate'))) {
					var lpuEndDate = Date.parseDate(rec.get('Lpu_EndDate'), 'd.m.Y');

					// Выбор из справочника МО, действующих на дату исключения из регистра.
					if (lpuEndDate < arguments[0].PersonRegister_disDate) {
						return false;
					}
				}
				return true;
			});


			base_form.findField('Notify_Num').showContainer();
			base_form.findField('Notify_setDate').showContainer();
			this.buttons[0].show();
		} else if(base_form.findField('PersonRegisterType_SysNick').getValue() == 'orphan') {
			base_form.findField('EvnVK_id').hideContainer();
			base_form.findField('EvnVK_id').setAllowBlank(true);
			base_form.findField('Lpu_did').showContainer();

			if(this.isMzSpecialist()){
				base_form.findField('MedPersonal_did').setDisabled(false);	
				base_form.findField('Lpu_did').setDisabled(false);	
			} else {
				base_form.findField('MedPersonal_did').setDisabled(true);
				base_form.findField('Lpu_did').setDisabled(true);	
			}


			base_form.findField('Lpu_did').lastQuery = '';
			base_form.findField('Lpu_did').getStore().clearFilter();
			// base_form.findField('Lpu_did').getStore().load();



			base_form.findField('Lpu_did').setBaseFilter(function(rec, id) {
				if ( ! Ext.isEmpty(rec.get('Lpu_EndDate'))) {
					var lpuEndDate = Date.parseDate(rec.get('Lpu_EndDate'), 'd.m.Y');

					// Выбор из справочника МО, действующих на дату исключения из регистра.
					if (lpuEndDate < arguments[0].PersonRegister_disDate) {
						return false;
					}
				}
				return true;
			});

			base_form.findField('Notify_Num').hideContainer();
			base_form.findField('Notify_setDate').showContainer();
			this.buttons[0].show();
		} else {
			base_form.findField('EvnVK_id').hideContainer();
			base_form.findField('EvnVK_id').setAllowBlank(true);
			base_form.findField('Lpu_did').hideContainer();
			base_form.findField('Lpu_did').setAllowBlank(true);
			base_form.findField('MedPersonal_did').setDisabled(true);			
			base_form.findField('MedPersonal_did').getStore().load({
				callback: function()
				{
					base_form.findField('MedPersonal_did').setValue(base_form.findField('MedPersonal_did').getValue());
					base_form.findField('MedPersonal_did').fireEvent('change', base_form.findField('MedPersonal_did'), base_form.findField('MedPersonal_did').getValue());
				}.createDelegate(this)
			});
			base_form.findField('Notify_Num').hideContainer();
			base_form.findField('Notify_setDate').hideContainer();
		}
		
		this.InformationPanel.load({
			Person_id: base_form.findField('Person_id').getValue()
		});
		var allow_outcause_list = ['1','2','5','6'];
		switch(base_form.findField('PersonRegisterType_SysNick').getValue()) {
			case 'crazy':
			case 'narko':
			case 'narc':
				allow_outcause_list.push('3');

				if ( getRegionNick() == 'khak' ) {
					allow_outcause_list.push('7');
				}
			    break;
			case 'orphan':
				allow_outcause_list.push('8');
				allow_outcause_list.push('9');
			    break;
			case 'large family':
				allow_outcause_list.push('4');
			    break;
			case 'nolos':
				allow_outcause_list = ['1','2', '9'];
			    break;
			default:
				allow_outcause_list.push('3');
			    break;
		}
		base_form.findField('PersonRegisterOutCause_id').lastQuery = '';
		base_form.findField('PersonRegisterOutCause_id').getStore().removeAll();
		base_form.findField('PersonRegisterOutCause_id').getStore().load({
			callback: function(records) {
				for (var i = 0; records.length > i; i++) {
					if (false == records[i].data['PersonRegisterOutCause_Code'].toString().inlist(allow_outcause_list)) {
						base_form.findField('PersonRegisterOutCause_id').getStore().remove(records[i]);
					}
				}
			}
		});
		/* Не всегда работает
		base_form.findField('PersonRegisterOutCause_id').getStore().filterBy(function (rec) {
			return rec.get('PersonRegisterOutCause_Code').inlist(allow_outcause_list);
		});
		*/
		if ( base_form.findField('PersonRegisterType_SysNick').getValue() == 'large family' ) {
			this.findById('prDiagGroupBox').hide();
		} else {
			this.findById('prDiagGroupBox').show();
		}
		loadMask.hide();
		return true;
	},
	initComponent: function()
	{
		var me = this;
		this.InformationPanel = new sw.Promed.PersonInformationPanelShort({
			region: 'north'
		});
		this.FormPanel = new Ext.form.FormPanel(
		{
			frame: true,
			layout: 'form',
			region: 'center',
			id: 'FormPanel',
			autoScroll: true,
			bodyStyle: 'padding: 5px',
			autoHeight: false,
			labelAlign: 'right',
			labelWidth: 200,
			url:'/?c=PersonRegister&m=doExcept',
			items:
			[{
				region: 'north',
				layout: 'form',
				xtype: 'panel',
				items: [{
					name: 'PersonRegister_id',
					xtype: 'hidden'
				}, {
					name: 'Person_id',
					xtype: 'hidden'
				}, {
					name: 'PersonRegister_setDate',
					xtype: 'hidden'
				}, {
					name: 'MorbusType_SysNick',
					xtype: 'hidden'
				}, {
					name: 'PersonRegisterType_SysNick',
					xtype: 'hidden'
				}, {
					name: 'Diag_id',
					xtype: 'hidden'
				},{
					fieldLabel: 'Номер извещения',
					name: 'Notify_Num',
					width: 350,
					xtype: 'textfield'
				}, {
					fieldLabel: 'Дата извещения',
					name: 'Notify_setDate',
					xtype: 'swdatefield',
					plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
					maxValue: getGlobalOptions().date,
					listeners: {
						'change': function(combo,newVal){
							if(
								!Ext.isEmpty(newVal) 
								&& (this.PersonRegisterType_SysNick == 'nolos' || this.PersonRegisterType_SysNick == 'orphan') 
								&& this.isMzSpecialist()
							){
								this.loadMedPersonalCombo();
							}
						}.createDelegate(this)
					}
				}, {
					xtype: 'fieldset',
					autoHeight: true,
					id: 'prDiagGroupBox',
					style: 'padding: 0; margin: 0',
					border: false,
					items: [{
						changeDisabled: false,
						disabled: true,
						fieldLabel: lang['diagnoz'],
						hiddenName: 'Diag_Name',
						listWidth: 620,
						width: 350,
						xtype: 'swdiagcombo'
					}]
				}, {
					allowBlank: false,
					fieldLabel: lang['data_isklyucheniya_iz_registra'],
					name: 'PersonRegister_disDate',
					xtype: 'swdatefield',
					plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
					minText: lang['data_isklyucheniya_iz_registra_doljna_byit_pozje_datyi_vklyucheniya_v_registr'],
					maxText: lang['data_isklyucheniya_iz_registra_doljna_byit_ranshe_ili_ravna_tekuschey_date'],
					maxValue: getGlobalOptions().date,
					listeners: {
						'change': function(combo,newVal){
							if(
								!Ext.isEmpty(newVal) 
								&& (this.PersonRegisterType_SysNick == 'nolos' || this.PersonRegisterType_SysNick == 'orphan') 
								&& this.isMzSpecialist()
							){
								this.loadMedPersonalCombo();
							}
						}.createDelegate(this)
					}
				}, {
					fieldLabel: lang['prichina_isklyucheniya'],
					hiddenName: 'CrazyCauseEndSurveyType_id',
					xtype: 'swcommonsprcombo',
					allowBlank:false,
					sortField:'CrazyCauseEndSurveyType_Code',
					comboSubject: 'CrazyCauseEndSurveyType',
					width: 350
				}, {
					fieldLabel: lang['prichina_isklyucheniya'],
					hiddenName: 'PersonRegisterOutCause_id',
					xtype: 'swcommonsprcombo',
					allowBlank:false,
					sortField:'PersonRegisterOutCause_Code',
					comboSubject: 'PersonRegisterOutCause',
					width: 350,
					listeners: {
						beforeselect: function (causeField, record, index) {

							var	comment =  this.findById('EvnNotifyRegister_OutComment_Container');

							if (this.PersonRegisterType_SysNick !== 'nolos')
							{
								return true;
							}


							if (record.get('PersonRegisterOutCause_id') == 9)
							{
								comment.show();
								return true;
							}


							comment.hide();
							return true;

						}.createDelegate(this)
					}
				}, {
					id: 'EvnNotifyRegister_OutComment_Container',
					layout: 'form',
					hidden: true,
					items: [{
						fieldLabel: langs('Комментарий'),
						id: 'EvnNotifyRegister_OutComment_Field',
						allowBlank: true,
						xtype: 'textarea',
						name: 'EvnNotifyRegister_OutComment',
					width: 350
					}],
					listeners: {
						hide: function (obj) {
							var commentField = obj.findById('EvnNotifyRegister_OutComment_Field');
							commentField.allowBlank = true;
							commentField.reset();
						},
						show: function (obj) {
							var commentField = obj.findById('EvnNotifyRegister_OutComment_Field');
							commentField.allowBlank = false;
						}
					}
				}, {
                    fieldLabel: lang['protokol_vk'],
                    hiddenName: 'EvnVK_id',
                    anchor:'100%',
					xtype: 'swevnvknoloscombo'
				}, {
                    fieldLabel: lang['mo_zapolneniya_napravleniya'],
					hiddenName: 'Lpu_did',// Может не совпадать с МО пользователя
					width: 340,
					xtype: 'swlpucombo',
                    allowBlank: false,
                    anchor: false,
                    listeners: {
                        'change': function(combo, newVal) {
                            this.loadMedPersonalCombo();
                        }.createDelegate(this)
                    }
				}, {
					disabled: true,
					fieldLabel: lang['vrach'],
					hiddenName: 'MedPersonal_did',
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
		sw.Promed.swPersonRegisterExceptWindow.superclass.initComponent.apply(this, arguments);
	}
});