/**
* swEvnNotifyTubEditWindow - Извещение об  туберкулезном заболевании
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009-2010 Swan Ltd.
* @author       A.Markoff <markov@swan.perm.ru>
* @version      2012/11
*/

sw.Promed.swEvnNotifyTubEditWindow = Ext.extend(sw.Promed.BaseForm, 
{
	action: null,
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
	width: 900,
	height: 600,
	doSave: function(options)
	{
		if ( this.formStatus == 'save' || !this.action.inlist([ 'add', 'edit' ]) ) {
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

		if (!isNaN(base_form.findField('Diag_Name').getValue())) {
			base_form.findField('Diag_id').setValue(base_form.findField('Diag_Name').getValue());
		}
		params.MedPersonal_id = base_form.findField('MedPersonal_id').getValue();
		params.Lpu_id = base_form.findField('Lpu_id').getValue();
		params.EvnNotifyTub_setDT = Ext.util.Format.date(base_form.findField('EvnNotifyTub_setDT').getValue(), 'd.m.Y');
		//params.Diag_Name = base_form.findField('Diag_Name').getValue();		
		params.PersonCategoryType_id = base_form.findField('PersonCategoryType_id').getValue();
		params.TubFluorSurveyPeriodType_id = base_form.findField('TubFluorSurveyPeriodType_id').getValue();
		params.TubDetectionFactType_id = base_form.findField('TubDetectionFactType_id').getValue();
		params.TubSurveyGroupType_id = base_form.findField('TubSurveyGroupType_id').getValue();
		params.TubDetectionMethodType_id = base_form.findField('TubDetectionMethodType_id').getValue();
		params.EvnNotifyTub_OtherDetectionPlace = base_form.findField('EvnNotifyTub_OtherDetectionPlace').getValue();
		params.EvnNotifyTub_IsFirstDiag = base_form.findField('EvnNotifyTub_IsFirstDiag').getValue();
		params.TubMethodConfirmBactType_id = base_form.findField('TubMethodConfirmBactType_id').getValue();
		params.TubDiagSop = base_form.findField('TubDiagSop').getValue();
		params.TubRiskFactorType = base_form.findField('TubRiskFactorType').getValue();
		params.TubRegCrazyType_id = base_form.findField('TubRegCrazyType_id').getValue();
		params.TubDetectionPlaceType_id = base_form.findField('TubDetectionPlaceType_id').getValue();
		params.EvnNotifyTub_OtherDetectionPlace = base_form.findField('EvnNotifyTub_OtherDetectionPlace').getValue();
		params.EvnNotifyTub_OtherPersonCategory = base_form.findField('EvnNotifyTub_OtherPersonCategory').getValue();
		params.EvnNotifyTub_IsDecreeGroup = base_form.findField('EvnNotifyTub_IsDecreeGroup').getValue();
		params.EvnNotifyTub_IsDestruction = base_form.findField('EvnNotifyTub_IsDestruction').getValue();
		params.EvnNotifyTub_IsConfirmBact = base_form.findField('EvnNotifyTub_IsConfirmBact').getValue();
		params.EvnNotifyTub_IsRegCrazy = base_form.findField('EvnNotifyTub_IsRegCrazy').getValue();
		params.EvnNotifyTub_DiagConfirmDT = Ext.util.Format.date(base_form.findField('EvnNotifyTub_DiagConfirmDT').getValue(), 'd.m.Y');
		params.EvnNotifyTub_FirstDT = Ext.util.Format.date(base_form.findField('EvnNotifyTub_FirstDT').getValue(), 'd.m.Y');
		params.EvnNotifyTub_RegDT = Ext.util.Format.date(base_form.findField('EvnNotifyTub_RegDT').getValue(), 'd.m.Y');
		params.saveFromJournal = this.saveFromJournal;
		
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
						base_form.findField('EvnNotifyTub_id').setValue(action.result.EvnNotifyBase_id);
						win.EvnNotifyTub_id = action.result.EvnNotifyBase_id;
						win.printNotification(win.EvnNotifyTub_id);
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
		if (this.action.inlist([ 'add', 'edit' ])) {
			this.doSave({print: true});
		} else {
			this.printNotification(this.EvnNotifyTub_id);
		}
	},
	printNotification: function(EvnNotifyTub_id) {
		if ( !EvnNotifyTub_id ) {
			return false;
		}

		printBirt({
			'Report_FileName': 'EvnNotifyTub.rptdesign',
			'Report_Params': '&paramEvnNotifyTub=' + EvnNotifyTub_id,
			'Report_Format': 'pdf'
		});
	},
	setFieldsDisabled: function(d) 
	{
		var form = this;
		var base_form = this.findById('FormPanel').getForm();
		
		if (this.action=='edit') {
			base_form.items.each(function(f) {
				if (f && (f.xtype!='hidden')){
					f.setDisabled((f.canEdit!==true));
				}
			});
		} else {
			base_form.items.each(function(f) {
				if (f && (f.xtype!='hidden') && (f.changeDisabled!==false)) {
					f.setDisabled(d);
				}
			});
		}
		
		form.buttons[0].setDisabled(d);
	},
	show: function() 
	{
		sw.Promed.swEvnNotifyTubEditWindow.superclass.show.apply(this, arguments);
		
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
		}
		this.focus();
		this.findById('FormPanel').getForm().reset();
		
		this.center();

		var base_form = this.FormPanel.getForm();
		base_form.reset();

		this.action = null;
		this.callback = Ext.emptyFn;
		this.formMode = 'remote';
		this.formStatus = 'edit';
		this.onHide = Ext.emptyFn;
		this.saveFromJournal = null;
		
		if (arguments[0].EvnNotifyTub_id) 
			this.EvnNotifyTub_id = arguments[0].EvnNotifyTub_id;
		else 
			this.EvnNotifyTub_id = null;
			
		if (arguments[0].callback) 
		{
			this.callback = arguments[0].callback;
		}	
		if ( arguments[0].formMode && typeof arguments[0].formMode == 'string' && arguments[0].formMode.inlist([ 'local', 'remote' ]) ) 
		{
			this.formMode = arguments[0].formMode;
		}
		if (arguments[0].owner) 
		{
			this.owner = arguments[0].owner;
		}
		if (arguments[0].action) 
		{
			this.action = arguments[0].action;
		}
		else 
		{
			if ( ( this.EvnNotifyTub_id ) && ( this.EvnNotifyTub_id > 0 ) )
				this.action = "view";
			else 
				this.action = "add";
		}
		if (arguments[0].saveFromJournal) 
		{
			this.saveFromJournal = arguments[0].saveFromJournal;
			base_form.findField('Diag_Name').enable().setAllowBlank(false);
			base_form.findField('MedPersonal_id').enable().setAllowBlank(false);
			base_form.findField('Lpu_id').enable().setAllowBlank(false);
		} else {
			base_form.findField('Diag_Name').disable().setAllowBlank(true);
			base_form.findField('MedPersonal_id').disable().setAllowBlank(true);
			base_form.findField('Lpu_id').disable().setAllowBlank(true);
		}
		
		base_form.setValues(arguments[0].formParams);
		
		var loadMask = new Ext.LoadMask(this.getEl(),{msg: LOAD_WAIT});
		loadMask.show();
		
		//var lpu_combo = base_form.findField('Lpu_oid');
		var is_confirmed_diag_combo = base_form.findField('EvnNotifyTub_IsConfirmedDiag');

		if (this.action != 'add') {
			Ext.Ajax.request({
				failure:function (response, options) {
					loadMask.hide();
					current_window.hide();
					sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_poluchit_dannyie_s_servera']);
				},
				params:{
					EvnNotifyTub_id: this.EvnNotifyTub_id
				},
				success:function (response, options) {
					var result = Ext.util.JSON.decode(response.responseText);
					base_form.setValues(result[0]);
					this.InformationPanel.load({
						Person_id: base_form.findField('Person_id').getValue()
					});
					base_form.findField('MedPersonal_id').getStore().load({
						callback: function()
						{
							base_form.findField('MedPersonal_id').setValue(base_form.findField('MedPersonal_id').getValue());
							base_form.findField('MedPersonal_id').fireEvent('change', base_form.findField('MedPersonal_id'), base_form.findField('MedPersonal_id').getValue());
						}.createDelegate(this)
					});
					/*lpu_combo.getStore().load({
						callback: function () {
							if ( lpu_combo.getStore().getCount() > 0 ) {
								lpu_combo.setValue(lpu_combo.getValue());
							}
						}
					});*/
					is_confirmed_diag_combo.fireEvent('change', is_confirmed_diag_combo, is_confirmed_diag_combo.getValue());
					loadMask.hide();
				}.createDelegate(this),
				url:'/?c=EvnNotifyTub&m=load'
			});			
		} else {
			this.InformationPanel.load({
				Person_id: base_form.findField('Person_id').getValue()
			});
			base_form.findField('MedPersonal_id').getStore().load({
				callback: function()
				{
					base_form.findField('MedPersonal_id').setValue(base_form.findField('MedPersonal_id').getValue());
					base_form.findField('MedPersonal_id').fireEvent('change', base_form.findField('MedPersonal_id'), base_form.findField('MedPersonal_id').getValue());
				}
			});
			base_form.findField('Lpu_id').setValue(getGlobalOptions().lpu_id);
			/*lpu_combo.getStore().load({
				callback: function () {
					if ( lpu_combo.getStore().getCount() > 0 ) {
						lpu_combo.setValue(getGlobalOptions().lpu_id);
					}
				}
			});*/
			base_form.findField('EvnNotifyTub_setDT').setValue(getGlobalOptions().date);
			is_confirmed_diag_combo.fireEvent('change', is_confirmed_diag_combo, is_confirmed_diag_combo.getValue());
			loadMask.hide();			
		}
				
		switch (this.action) 
		{
			case 'add':
				this.setTitle(lang['izveschenie_o_bolnom_tuberkulezom_dobavlenie']);
				this.setFieldsDisabled(false);
				break;
			case 'edit':
				this.setTitle(lang['izveschenie_o_bolnom_tuberkulezom_redaktirovanie']);
				this.setFieldsDisabled(false);
				break;
			case 'view':
				this.setTitle(lang['izveschenie_o_bolnom_tuberkulezom_prosmotr']);
				this.setFieldsDisabled(true);
				break;
		}
		
	},	
	initComponent: function() 
	{
		var fields_array = [
			{name: '1', description: lang['saharnyiy_diabet'] },
			{name: '2', description: lang['hnzl'] },
			{name: '3', description: lang['gipertonicheskaya_bolezn_ibs'] },
			{name: '4', description: lang['yazvennaya_bolezn_jeludka_i_12_perstnoy_kishki'] },
			{name: '5', description: lang['psihicheskoe_zabolevanie'] },
			{name: '6', description: lang['onkologicheskoe_zabolevanie'] },
			{name: '8', description: lang['vich'] },
			{name: '7', description: lang['prochee_ukazat_kakoe'] }
		];
		var chgroup_array = new Array();		
		for (i = 0; i < fields_array.length; i++) {
			chgroup_array.push({boxLabel: fields_array[i].description, value: fields_array[i].name});
		}
		
		this.ChGroup = new Ext.form.CheckboxGroup({
			name: 'TubDiagSop',
			xtype: 'checkboxgroup',
			hidden: false,
			hideLabel: true,
			itemCls: 'x-check-group-alt',
			columns: 1,
			items: chgroup_array,
			getValue: function() {
				var out = [];
				this.items.each(function(item){
					if(item.checked){
						out.push(item.value);
					}
				});
				return out.join(',');
			},
			setValue: function(val) {
				if (!val) return false;
				val = val.split(',');
				this.items.each(function(item){
					if(item.value.inlist(val)) {
						item.setValue(true)
						log(item);
					}
				});
			}
		});
		
		var risk_fields_array = [
			{name: '1', description: lang['nahozdenie_v_mls'] },
			{name: '2', description: lang['kontakt_s_bolnym_tuberkulozom'] },
			{name: '3', description: lang['narkologicheskaya_zavisimost'] },
			{name: '4', description: lang['alkogolnaya_zavisimost'] },
			{name: '5', description: lang['rodyi'] },
			{name: '6', description: lang['socialnaya_dezadaptatcia'] },
			{name: '7', description: lang['poluchaushie_kortokosteroidnuy_luchevuy_citostaticheskuiy_terapiy'] }
		];
		var risk_chgroup_array = new Array();		
		for (i = 0; i < risk_fields_array.length; i++) {
			risk_chgroup_array.push({boxLabel: risk_fields_array[i].description, value: risk_fields_array[i].name});
		}
		
		this.RiskChGroup = new Ext.form.CheckboxGroup({
			name: 'TubRiskFactorType',
			xtype: 'checkboxgroup',
			hidden: false,
			hideLabel: true,
			itemCls: 'x-check-group-alt',
			columns: 1,
			items: risk_chgroup_array,
			getValue: function() {
				var out = [];
				this.items.each(function(item){
					if(item.checked){
						out.push(item.value);
					}
				});
				return out.join(',');
			},
			setValue: function(val) {
				if (!val) return false;
				val = val.split(',');
				this.items.each(function(item){
					if(item.value.inlist(val)) {
						item.setValue(true)
						log(item);
					}
				});
			}
		});

		this.InformationPanel = new sw.Promed.PersonInformationPanelShort({
			region: 'north'
		});
		this.FormPanel = new Ext.form.FormPanel({
			frame: true,
			layout: 'form',
			region: 'center',
			id: 'FormPanel',
			bodyStyle: 'padding: 5px',
			autoHeight: false,
			labelAlign: 'right',
			labelWidth: 200,
			autoScroll:true,
			url:'/?c=EvnNotifyTub&m=save',
			items: 
			[{
				region: 'north',
				layout: 'form',
				xtype: 'panel',
				items: [{
					name: 'EvnNotifyTub_id',
					xtype: 'hidden'
				}, {
					name: 'EvnNotifyTub_pid',
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
					border: false,
					bodyStyle:'width:100%;margin:5px;',
					layout: 'column',
					items: [{
						border: false,
						layout: 'form',
						columnWidth: .55,
						items: [{
							fieldLabel: lang['kategoriya_naseleniya'],
							anchor:'100%',
							hiddenName: 'PersonCategoryType_id',
							xtype: 'swcommonsprcombo',
							allowBlank: false,
							sortField:'PersonCategoryType_Code',
							comboSubject: 'PersonCategoryType'
						}, {
							fieldLabel: 'Жилищные условия',
							anchor:'100%',
							hiddenName: 'PersonLivingFacilies_id',
							xtype: 'swcommonsprcombo',
							allowBlank: false,
							sortField:'PersonLivingFacilies_Code',
							comboSubject: 'PersonLivingFacilies'
						}, {
							fieldLabel: lang['sroki_predyiduschego_fg_obsledovaniya'],
							anchor:'100%',
							hiddenName: 'TubFluorSurveyPeriodType_id',
							xtype: 'swcommonsprcombo',
							allowBlank: true,
							sortField:'TubFluorSurveyPeriodType_Code',
							comboSubject: 'TubFluorSurveyPeriodType'
						}, {
							fieldLabel: lang['obstoyatelstva_pri_kotoryih_vyiyavleno_zabolevanie_puti_vyiyavleniya'],
							anchor:'100%',
							hiddenName: 'TubDetectionFactType_id',
							xtype: 'swcommonsprcombo',
							allowBlank: false,
							sortField:'TubDetectionFactType_Code',
							comboSubject: 'TubDetectionFactType'
						}, {
							fieldLabel: lang['vyiyavlen_iz_nablyudaemyih_v_tubuchrejdeniyah_grupp'],
							anchor:'100%',
							hiddenName: 'TubSurveyGroupType_id',
							xtype: 'swcommonsprcombo',
							allowBlank: true,
							sortField:'TubSurveyGroupType_Code',
							comboSubject: 'TubSurveyGroupType'
						}, {
							fieldLabel: lang['metod_vyiyavleniya'],
							anchor:'100%',
							hiddenName: 'TubDetectionMethodType_id',
							xtype: 'swcommonsprcombo',
							allowBlank: false,
							sortField:'TubDetectionMethodType_Code',
							comboSubject: 'TubDetectionMethodType'
						}, {
							fieldLabel: lang['drugoy_metod'],
							name: 'EvnNotifyTub_OtherDetectionMethod',
							disabled: true,
							allowBlank:true,
							anchor:'100%',
							xtype: 'textfield'
						}, {
							fieldLabel: 'Тестирование на лекарственную устойчивость',
							anchor:'100%',
							hiddenName: 'DrugResistenceTest_id',
							xtype: 'swcommonsprcombo',
							sortField:'DrugResistenceTest_Code',
							comboSubject: 'DrugResistenceTest'
						}, {
							fieldLabel: lang['kod_po_mkb-10'],
							hiddenName: 'Diag_Name',
							changeDisabled: false,
							canEdit: true,
							disabled: true,
							anchor:'100%',
							MorbusType_SysNick:'tub',
							xtype: 'swdiagcombo'
						}, {
							fieldLabel: lang['diagnoz'],
							anchor:'100%',
							typeCode: 'int',
							hiddenName: 'TubDiagNotify_id',
							xtype: 'swcommonsprcombo',
							allowBlank: false,
							sortField:'TubDiagNotify_Code',
							comboSubject: 'TubDiagNotify',
							canEdit: true
						}, {
							fieldLabel: lang['zabolevanie_po_forme_№8'],
							anchor:'100%',
							typeCode: 'int',
							hiddenName: 'TubDiagForm8_id',
							xtype: 'swcommonsprcombo',
							allowBlank: false,
							sortField:'TubDiagForm8_Code',
							comboSubject: 'TubDiagForm8',
							canEdit: true,
							listWidth: 400
						}, {
							fieldLabel: lang['ustanovlen_vpervyie_v_jizni'],
							width: 70,
							hiddenName: 'EvnNotifyTub_IsFirstDiag',
							xtype: 'swyesnocombo',
							allowBlank: true
						}, {
							fieldLabel: 'Бактериовыделение',
							anchor:'100%',
							hiddenName: 'TubBacterialExcretion_id',
							xtype: 'swcommonsprcombo',
							sortField:'TubBacterialExcretion_Code',
							comboSubject: 'TubBacterialExcretion'
						}, {
							fieldLabel: lang['metod_podtverjdeniya_bakteriovyideleniya'],
							anchor:'100%',
							hiddenName: 'TubMethodConfirmBactType_id',
							xtype: 'swcommonsprcombo',
							sortField:'TubMethodConfirmBactType_Code',
							comboSubject: 'TubMethodConfirmBactType'
						}, {
							fieldLabel: lang['tip_ucheta_v_narkologicheskom_dispansere'],
							anchor:'100%',
							hiddenName: 'TubRegCrazyType_id',
							xtype: 'swcommonsprcombo',
							allowBlank: true,
							sortField:'TubRegCrazyType_Code',
							comboSubject: 'TubRegCrazyType'
						}]
					}, {
						border: false,
						layout: 'form',
						columnWidth: .43,
						items: [{
							fieldLabel: lang['mesto_vyiyavleniya'],
							anchor:'100%',
							hiddenName: 'TubDetectionPlaceType_id',
							xtype: 'swcommonsprcombo',
							sortField:'TubDetectionPlaceType_Code',
							comboSubject: 'TubDetectionPlaceType'
						}, {
							fieldLabel: lang['uchrejdenie'],
							name: 'EvnNotifyTub_OtherDetectionPlace',
							disabled: true,
							allowBlank:true,
							anchor:'100%',
							xtype: 'textfield'
						}, {
							fieldLabel: lang['vedomstvo'],
							name: 'EvnNotifyTub_OtherPersonCategory',
							allowBlank:true,
							disabled: true,
							anchor:'100%',
							xtype: 'textfield'
						}, {
							fieldLabel: 'Декретированная группа',
							anchor:'100%',
							hiddenName: 'PersonDecreedGroup_id',
							xtype: 'swcommonsprcombo',
							allowBlank: false,
							sortField:'PersonDecreedGroup_Code',
							comboSubject: 'PersonDecreedGroup'
						}, {
							fieldLabel: lang['prinadlejnost_k_dekretirovannyim_gruppam'],
							width: 70,
							hiddenName: 'EvnNotifyTub_IsDecreeGroup',
							xtype: 'swyesnocombo',
							allowBlank: true
						}, {
							fieldLabel: lang['nalichie_raspada'],
							width: 70,
							hiddenName: 'EvnNotifyTub_IsDestruction',
							xtype: 'swyesnocombo',
							canEdit: true
						}, {
							fieldLabel: lang['podtverjdenie_bakteriovyideleniya'],
							width: 70,
							hiddenName: 'EvnNotifyTub_IsConfirmBact',
							xtype: 'swyesnocombo',
							allowBlank: true,
							canEdit: true
						},{
							fieldLabel: lang['sostoit_na_uchete_v_narkologicheskom_dispansere'],
							width: 70,
							hiddenName: 'EvnNotifyTub_IsRegCrazy',
							xtype: 'swyesnocombo',
							allowBlank: true
						}, {
							fieldLabel: 'Диагноз подтвержден',
							width: 70,
							hiddenName: 'EvnNotifyTub_IsConfirmedDiag',
							xtype: 'swyesnocombo',
							allowBlank: false,
							listeners: {
								'change': function(combo, newValue, oldValue) {
									var base_form = this.FormPanel.getForm();
									var requiredOnConfirm = [
										'DrugResistenceTest_id', 'TubBacterialExcretion_id', 'TubMethodConfirmBactType_id',
										'TubDetectionPlaceType_id', 'EvnNotifyTub_IsDestruction',
										'EvnNotifyTub_DiagConfirmDT', 'PersonDispGroup_id'
									];

									requiredOnConfirm.forEach(function(fieldName) {
										var field = base_form.findField(fieldName);
										if (field) field.setAllowBlank(newValue != 2);
									});
								}.createDelegate(this)
							}
						}, {
							fieldLabel: lang['data_podtverjdeniya_diagnoza_tuberkuleza_tsvk'],
							name: 'EvnNotifyTub_DiagConfirmDT',
							xtype: 'swdatefield',
							plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
						}, {
							fieldLabel: 'Группа диспансерного наблюдения',
							anchor:'100%',
							hiddenName: 'PersonDispGroup_id',
							xtype: 'swcommonsprcombo',
							sortField:'PersonDispGroup_Code',
							comboSubject: 'PersonDispGroup'
						}, {
							fieldLabel: lang['primechanie'],
							name: 'EvnNotifyTub_Comment',
							allowBlank: true,
							anchor:'100%',
							xtype: 'textfield'
						}, {
							fieldLabel: lang['data_pervogo_obrascheniya_za_med_pomoschyu'],
							name: 'EvnNotifyTub_FirstDT',
							allowBlank:true,
							xtype: 'swdatefield',
							plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
						}, {
							fieldLabel: lang['data_vzyatiya_na_uchet'],
							name: 'EvnNotifyTub_RegDT',
							allowBlank:true,
							xtype: 'swdatefield',
							plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
						}]
					}]
				},{
					layout:'column',
					items:[{
						layout: 'form',
						items:[{
							xtype: 'fieldset',
							autoHeight: true,
							title: lang['soputstvuyuschie_zabolevaniya'],
							items: [
								this.ChGroup, {
								name: 'TubDiagSopLink_Descr',
								disabled: true,
								width: 180,
								xtype: 'textfield',
								autoCreate: {tag: "input", size: 20, maxLength: "20", autocomplete: "off"},
								hideLabel: true
							}]
						}]
					}, {
						layout: 'form',
						items:[{
							xtype: 'fieldset',
							style: 'margin-left:10px',
							autoHeight: true,
							title: 'Факторы риска',
							items: [this.RiskChGroup]
						}]
					}]
				}, {
					fieldLabel: lang['data_zapolneniya_izvescheniya'],
					name: 'EvnNotifyTub_setDT',
					allowBlank: false,
					changeDisabled: false,
					disabled: true,
					xtype: 'swdatefield',
					plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
				}, {
					changeDisabled: false,
					disabled: true,
					fieldLabel: lang['mo_zapolneniya_izvecheniya'],
					hiddenName: 'Lpu_id',
					listWidth: 750,
					width: 350,
					xtype: 'swlpucombo',
					anchor: false
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
				text: BTN_FRMCANCEL
			}],
			items: [this.InformationPanel, this.FormPanel]
		});
		sw.Promed.swEvnNotifyTubEditWindow.superclass.initComponent.apply(this, arguments);
	}
});
