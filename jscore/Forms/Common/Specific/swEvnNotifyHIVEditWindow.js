/**
* swEvnNotifyHIVEditWindow - ОПЕРАТИВНОЕ ДОНЕСЕНИЕ о лице, в крови которого при исследовании в реакции иммуноблота выявлены антитела к ВИЧ (форма N 266/У-88)
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009-2010 Swan Ltd.
* @author       Alexander Permyakov 
* @version      2012/12
*/

sw.Promed.swEvnNotifyHIVEditWindow = Ext.extend(sw.Promed.BaseForm, 
{
	action: null,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	formMode: 'remote',
	formStatus: 'edit',
	layout: 'border',
	modal: true,
	maximized : true,
	doSave: function(options)
	{
		if ( this.formStatus == 'save' || this.action != 'add' ) {
			return false;
		}
		if ( !options || typeof options != 'object' ) {
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
		
		params.MedPersonal_id = base_form.findField('MedPersonal_id').getValue();
		params.HIVContingentType_id_list = base_form.findField('HIVContingentType_id_list').getValue();//HIVContingentTypeChGroup
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
						Ext.Msg.alert(lang['oshibka_#']+action.result.Error_Code, action.result.Error_Msg);
					}
				}
			},
			success: function(result_form, action) 
			{
				win.formStatus = 'edit';
				loadMask.hide();
				if (action.result && action.result.EvnNotifyBase_id) {
					if (action.result.PersonRegister_id) {
						showSysMsg(lang['izveschenie_sozdano_i_patsient_vklyuchen_v_registr']);
					} else {
						showSysMsg(lang['izveschenie_sozdano']);
					}
					if (options.print) {
						win.action = 'view';
						win.setFieldsDisabled(true);
						win.EvnNotifyHIV_id = action.result.EvnNotifyBase_id;
						win.printNotification(win.EvnNotifyHIV_id);
					} else {
						win.hide();
					}
					win.callback(action.result);
				} else {
					showSysMsg(lang['nepravilnyiy_format_otveta_servera']);
				}
			}
		});
		
	},
	doPrint: function() {
		if (this.action == 'add') {
			this.doSave({print: true});
		} else {
			this.printNotification(this.EvnNotifyHIV_id);
		}
	},
	printNotification: function(EvnNotifyHIV_id) {
		if ( !EvnNotifyHIV_id ) {
			return false;
		}

		printBirt({
			'Report_FileName': 'EvnNotifyHIV.rptdesign',
			'Report_Params': '&paramEvnNotifyHIV=' + EvnNotifyHIV_id,
			'Report_Format': 'pdf'
		});
	},
	setFieldsDisabled: function(d) 
	{
		var form = this;
		var base_form = this.FormPanel.getForm();
		
		base_form.items.each(function(f) 
		{
			if (f && (f.xtype!='hidden') && (f.xtype!='fieldset')  && (f.changeDisabled!==false))
			{
				f.setDisabled(d);
			}
		});
		form.buttons[0].setDisabled(d);
	},
	show: function() 
	{
		sw.Promed.swEvnNotifyHIVEditWindow.superclass.show.apply(this, arguments);
		
		var current_window = this;
		if (!arguments[0]) 
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
			return false;
		}
		this.focus();
		this.FormPanel.getForm().reset();
		
		this.center();

		var base_form = this.FormPanel.getForm();
		base_form.reset();

		this.formStatus = 'edit';
		this.EvnNotifyHIV_id = arguments[0].EvnNotifyHIV_id || null;
		this.action = (( this.EvnNotifyHIV_id ) && ( this.EvnNotifyHIV_id > 0 ))?'view':'add';
		this.formMode = (arguments[0].formMode && typeof arguments[0].formMode == 'string' && arguments[0].formMode.inlist([ 'local', 'remote' ]))?arguments[0].formMode:'remote';
		this.owner = arguments[0].owner || null;
		this.callback = arguments[0].callback || Ext.emptyFn;
		this.onHide = arguments[0].onHide || Ext.emptyFn;
		
		base_form.setValues(arguments[0].formParams);
		
		var loadMask = new Ext.LoadMask(this.getEl(),{msg: LOAD_WAIT});
		loadMask.show();
		
		var lpu_combo = base_form.findField('Lpuifa_id');

		if (this.action != 'add') {
			switch (this.action) 
			{
				case 'view':
					this.setTitle(lang['operativnoe_donesenie_o_litse_v_krovi_kotorogo_pri_issledovanii_v_reaktsii_immunoblota_vyiyavlenyi_antitela_k_vich_forma_n_266_u-88_prosmotr']);
					this.setFieldsDisabled(true);
					break;
			}
			Ext.Ajax.request({
				failure:function (response, options) {
					loadMask.hide();
					current_window.hide();
					sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_poluchit_dannyie_s_servera']);
				},
				params:{
					EvnNotifyHIV_id: this.EvnNotifyHIV_id
				},
				success:function (response, options) {
					var result = Ext.util.JSON.decode(response.responseText);
					base_form.setValues(result[0]);
					this.InformationPanel.load({
						Person_id: base_form.findField('Person_id').getValue()
					});

					var diag_name = base_form.findField('Diag_Name').getValue();
					var diag_code = diag_name?diag_name.slice(0, 3):null;
					if (diag_code >= 'B20' && diag_code <= 'B24' && getRegionNick() != 'kz') {
						base_form.findField('MorbusHIV_confirmDate').showContainer();
						base_form.findField('MorbusHIV_EpidemCode').showContainer();
					} else {
						base_form.findField('MorbusHIV_confirmDate').hideContainer();
						base_form.findField('MorbusHIV_EpidemCode').hideContainer();
					}

					base_form.findField('MorbusHIVLab_BlotDT').fireEvent('change', base_form.findField('MorbusHIVLab_BlotDT'), base_form.findField('MorbusHIVLab_BlotDT').getValue());

					base_form.findField('MedPersonal_id').getStore().load({
						callback: function()
						{
							base_form.findField('MedPersonal_id').setValue(base_form.findField('MedPersonal_id').getValue());
							base_form.findField('MedPersonal_id').fireEvent('change', base_form.findField('MedPersonal_id'), base_form.findField('MedPersonal_id').getValue());
						}.createDelegate(this)
					});
					lpu_combo.getStore().load({
						callback: function () {
							if ( lpu_combo.getStore().getCount() > 0 ) {
								lpu_combo.setValue(lpu_combo.getValue());
							}
						}
					});
					loadMask.hide();
				}.createDelegate(this),
				url:'/?c=EvnNotifyHIV&m=load'
			});			
		} else {
			this.setTitle(lang['operativnoe_donesenie_o_litse_v_krovi_kotorogo_pri_issledovanii_v_reaktsii_immunoblota_vyiyavlenyi_antitela_k_vich_forma_n_266_u-88_dobavlenie']);
			this.setFieldsDisabled(false);

			var diag_name = base_form.findField('Diag_Name').getValue();
			var diag_code = diag_name?diag_name.slice(0, 3):null;
			if (diag_code >= 'B20' && diag_code <= 'B24' && getRegionNick() != 'kz') {
				base_form.findField('MorbusHIV_confirmDate').showContainer();
				base_form.findField('MorbusHIV_EpidemCode').showContainer();
			} else {
				base_form.findField('MorbusHIV_confirmDate').hideContainer();
				base_form.findField('MorbusHIV_EpidemCode').hideContainer();
			}

			base_form.findField('MorbusHIVLab_BlotDT').fireEvent('change', base_form.findField('MorbusHIVLab_BlotDT'), base_form.findField('MorbusHIVLab_BlotDT').getValue());

			this.InformationPanel.load({
				Person_id: base_form.findField('Person_id').getValue(),
				callback: function () {
					if (current_window.InformationPanel.getFieldValue('DocumentType_id').inlist([3, 13, 14, 18])) {
						base_form.findField('HIVContingentType_pid').setValue(100);
						current_window.findById('HIVContingentTypeChGroup').items.items[6].enable();
					} else {
						base_form.findField('HIVContingentType_pid').setValue(200);
						current_window.findById('HIVContingentTypeChGroup').items.items[6].disable();
					}
				}
			});
			base_form.findField('MedPersonal_id').getStore().load({
				callback: function()
				{
					base_form.findField('MedPersonal_id').setValue(base_form.findField('MedPersonal_id').getValue());
					base_form.findField('MedPersonal_id').fireEvent('change', base_form.findField('MedPersonal_id'), base_form.findField('MedPersonal_id').getValue());
				}
			});
			lpu_combo.getStore().load({
				callback: function () {
					if ( lpu_combo.getStore().getCount() > 0 ) {
						//lpu_combo.setValue(getGlobalOptions().lpu_id);
					}
				}
			});
			base_form.findField('EvnNotifyHIV_setDT').setValue(getGlobalOptions().date);
			loadMask.hide();			
		}		
	},	
	initComponent: function() 
	{
		var win = this;
		this.InformationPanel = new sw.Promed.PersonInformationPanelShort({
			region: 'north'
		});
		this.FormPanel = new Ext.form.FormPanel({
			frame: true,
			layout: 'form',
			region: 'center',
			bodyStyle: 'padding: 5px',
			autoHeight: false,
			labelAlign: 'right',
			labelWidth: 220,
			autoScroll:true,
			url:'/?c=EvnNotifyHIV&m=save',
			items: 
			[{
				region: 'center',
				layout: 'form',
				xtype: 'panel',
				items: [{
					name: 'EvnNotifyHIV_id',
					xtype: 'hidden'
				}, {
					name: 'EvnNotifyHIV_pid',
					xtype: 'hidden'
				}, {
					name: 'Morbus_id',
					xtype: 'hidden'
				}, {
					name: 'Server_id',
					xtype: 'hidden'
				}, {
					name: 'PersonEvn_id',
					xtype: 'hidden'
				}, {
					name: 'Person_id',
					xtype: 'hidden'
				}, {
					name: 'Diag_id',
					xtype: 'hidden'
				}, {
					name: 'Diag_Name',
					xtype: 'hidden'
				}, {
					fieldLabel: lang['grajdanstvo'],
					valueField: 'HIVContingentType_id',
					displayField: 'HIVContingentType_Name',
					hiddenName: 'HIVContingentType_pid',
					comboData: [
						[100,lang['grajdane_rf']],
						[200,lang['inostrannyie_grajdane']]
					],
					comboFields: [
						{name: 'HIVContingentType_id', type:'int'},
						{name: 'HIVContingentType_Name', type:'string'}
					],
					width: 300,
					xtype: 'swstoreinconfigcombo',
					listeners: {
						'change': function (combo, newValue, oldValue) {
							var index = combo.getStore().findBy(function (rec) {
								return (rec.get('HIVContingentType_id') == newValue);
							});
							combo.fireEvent('select', combo, combo.getStore().getAt(index), index);
						},
						'select': function (combo, record, index) {
							if (typeof record == 'object' && record.get('HIVContingentType_id') == 100) {
								win.findById('HIVContingentTypeChGroup').items.items[6].enable();
							} else {
								win.findById('HIVContingentTypeChGroup').items.items[6].disable();
							}
						}
					}
				}, {
					autoHeight: true,
					title: lang['kod_kontingenta'],
					xtype: 'fieldset',
					items: [{
						id:'HIVContingentTypeChGroup',
						xtype: 'swhivcontingenttypecheckboxgroup'
					}]
				}, {
					autoHeight: true,
					title: lang['rezultat_reaktsii_immunoblota'],
					xtype: 'fieldset',
					labelWidth: 220,
					items: [{
						fieldLabel: lang['data_postanovki'],
						name: 'MorbusHIVLab_BlotDT',
						allowBlank:true,
						xtype: 'swdatefield',
						plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
						listeners: {
							'change': function(combo, newValue, oldValue) {
								var base_form = this.FormPanel.getForm();

								base_form.findField('LabAssessmentResult_iid').setAllowBlank(getRegionNick() == 'kz' || Ext.isEmpty(newValue));
								base_form.findField('LabAssessmentResult_iid').setContainerVisible(getRegionNick() != 'kz');
							}.createDelegate(this)
						}
					}, {
						fieldLabel: lang['tip_test-sistemyi'],
						name: 'MorbusHIVLab_TestSystem',
						allowBlank:true,
						width: 300,
						maxLength: 64,
						xtype: 'textfield'
					}, {
						fieldLabel: lang['№_serii'],
						name: 'MorbusHIVLab_BlotNum',
						allowBlank:true,
						width: 300,
						maxLength: 64,
						xtype: 'textfield'
					}, {
						fieldLabel: lang['vyiyavlennyie_belki_i_glikoproteidyi'],
						name: 'MorbusHIVLab_BlotResult',
						allowBlank:true,
						width: 300,
						maxLength: 100,
						xtype: 'textfield'
					}, {
						fieldLabel: 'Результат',
						hiddenName: 'LabAssessmentResult_iid',
						width: 300,
						maxLength: 100,
						xtype: 'swcommonsprcombo',
						comboSubject: 'LabAssessmentResult'
					}]
				}, {
					autoHeight: true,
					title: lang['ifa'],
					xtype: 'fieldset',
					labelWidth: 220,
					items: [{
						fieldLabel: lang['uchrejdenie_pervichno_vyiyavivshee_polojitelnyiy_rezultat_v_ifa'],
						allowBlank: true,
						width: 300,
						autoLoad: false,
						hiddenName: 'Lpuifa_id',
						xtype: 'swlpulocalcombo'
					}, {
						fieldLabel: lang['data_ifa'],
						name: 'MorbusHIVLab_IFADT',
						allowBlank:true,
						xtype: 'swdatefield',
						plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
					}, {
						fieldLabel: lang['rezultat_ifa'],
						name: 'MorbusHIVLab_IFAResult',
						allowBlank:true,
						width: 300,
						maxLength: 30,
						xtype: 'textfield'
					}]
				}, {
					fieldLabel: 'Дата подтверждения диагноза',
					name: 'MorbusHIV_confirmDate',
					xtype: 'swdatefield'
				}, {
					fieldLabel: 'Эпидемиологический код',
					name: 'MorbusHIV_EpidemCode',
					xtype: 'textfield',
					width: 350
				}, {
					fieldLabel: lang['data_zapolneniya_izvescheniya'],
					name: 'EvnNotifyHIV_setDT',
					allowBlank: false,
					/*
					changeDisabled: false,
					disabled: true,
					*/
					xtype: 'swdatefield',
					plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
				}, {
					changeDisabled: false,
					disabled: true,
					fieldLabel: lang['vrach_zapolnivshiy_izveschenie'],
					hiddenName: 'MedPersonal_id',
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
					this.doSave({print: false});
				}.createDelegate(this),
				iconCls: 'save16',
				text: BTN_FRMSAVE
			}, {
				handler: function() {
					this.doPrint();
				}.createDelegate(this),
				iconCls: 'print16',
				text: lang['pechat']
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
				text: BTN_FRMCLOSE
			}],
			items: [this.InformationPanel, this.FormPanel]
		});
		sw.Promed.swEvnNotifyHIVEditWindow.superclass.initComponent.apply(this, arguments);
	}
});