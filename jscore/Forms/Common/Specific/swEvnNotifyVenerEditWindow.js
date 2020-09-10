/**
* swEvnNotifyVenerEditWindow - Извещение о больных венерических заболеваний
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

sw.Promed.swEvnNotifyVenerEditWindow = Ext.extend(sw.Promed.BaseForm, 
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
	width: 800,
	height: 570,
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
		
		base_form.submit({
			params: params,
			failure: function(result_form, action) 
			{
				win.formStatus = 'edit';
				loadMask.hide();
			},
			success: function(result_form, action) 
			{
				win.formStatus = 'edit';
				loadMask.hide();
				if (action.result && action.result.EvnNotifyBase_id) {
					if (options.print) {
						win.EvnNotifyVener_id = action.result.EvnNotifyBase_id;
						win.printNotification(win.EvnNotifyVener_id);
						win.action = 'view';
						win.setFieldsDisabled(true);
					} else {
						win.hide();
					}
					win.callback(action.result);
					if (action.result.PersonRegister_id) {
						showSysMsg(lang['izveschenie_sozdano_i_patsient_vklyuchen_v_registr']);
					} else {
						showSysMsg(lang['izveschenie_sozdano']);
					}
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
			this.printNotification(this.EvnNotifyVener_id);
		}
	},
	printNotification: function(EvnNotifyVener_id) {
		if ( !EvnNotifyVener_id ) {
			return false;
		}

		printBirt({
			'Report_FileName': 'NoticeForm_089u_kv.rptdesign',
			'Report_Params': '&paramEvnNotifyVener=' + EvnNotifyVener_id,
			'Report_Format': 'pdf'
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
	show: function() 
	{
		sw.Promed.swEvnNotifyVenerEditWindow.superclass.show.apply(this, arguments);
		
		var current_window = this;
		if (!arguments[0]) {
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
		this.findById('FormPanel').getForm().reset();
		
		this.center();

		var base_form = this.FormPanel.getForm();
		base_form.reset();

		this.action = null;
		this.callback = Ext.emptyFn;
		this.formMode = 'remote';
		this.formStatus = 'edit';
		this.onHide = Ext.emptyFn;
		
		if (arguments[0].EvnNotifyVener_id) 
			this.EvnNotifyVener_id = arguments[0].EvnNotifyVener_id;
		else 
			this.EvnNotifyVener_id = null;
			
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
			if ( ( this.EvnNotifyVener_id ) && ( this.EvnNotifyVener_id > 0 ) )
				this.action = "view";
			else 
				this.action = "add";
		}
		
		base_form.setValues(arguments[0].formParams);
		
		var mpn_combo = base_form.findField('MedPersonal_id');
		var mpp_combo = base_form.findField('MedPersonal_pid');
		var mpf_combo = base_form.findField('MedPersonal_fid');
		
		var loadMask = new Ext.LoadMask(this.getEl(),{msg: LOAD_WAIT});
		loadMask.show();
		

		if (this.action != 'add') {
			Ext.Ajax.request({
				failure:function (response, options) {
					loadMask.hide();
					current_window.hide();
					sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_poluchit_dannyie_s_servera']);
				},
				params:{
					EvnNotifyVener_id: this.EvnNotifyVener_id
				},
				success:function (response, options) {
					loadMask.hide();
					var result = Ext.util.JSON.decode(response.responseText);
					base_form.setValues(result[0]);
					this.InformationPanel.load({
						Person_id: base_form.findField('Person_id').getValue()
					});
					mpn_combo.getStore().load({
						//params: {MedPersonal_id: mpn_combo.getValue()},
						callback: function() {
							mpn_combo.setValue(mpn_combo.getValue());
							mpn_combo.fireEvent('change', mpn_combo, mpn_combo.getValue());							
							if (mpp_combo.getValue()) {
								mpp_combo.getStore().load({
									//params: {MedPersonal_id: mpp_combo.getValue()},
									callback: function() {
										mpp_combo.setValue(mpp_combo.getValue());
									}
								});
							}
							if (mpf_combo.getValue()) {
								mpf_combo.getStore().load({
									//params: {MedPersonal_id: mpf_combo.getValue()},
									callback: function() {
										mpf_combo.setValue(mpf_combo.getValue());
									}
								});
							}
						}
					});
					this.setVisibleFields();
					this.loadDiagCombo();
				}.createDelegate(this),
				url:'/?c=EvnNotifyVener&m=load'
			});			
		} else {
			this.InformationPanel.load({
				Person_id: base_form.findField('Person_id').getValue()
			});
			mpn_combo.getStore().load({
				//params: {MedPersonal_id: mpn_combo.getValue()},
				callback: function() {
					mpn_combo.setValue(mpn_combo.getValue());
					mpn_combo.fireEvent('change', mpn_combo, mpn_combo.getValue());
					mpp_combo.getStore().load({
						callback: function() {
							mpp_combo.setValue(null);
							mpp_combo.fireEvent('change', mpp_combo, mpp_combo.getValue());
							mpf_combo.getStore().load({
								callback: function() {
									mpf_combo.setValue(null);
									mpf_combo.fireEvent('change', mpf_combo, mpf_combo.getValue());
								}
							});
						}
					});
				}
			});

			base_form.findField('EvnNotifyVener_setDT').setValue(getGlobalOptions().date);
			loadMask.hide();	
			this.setVisibleFields();
			this.loadDiagCombo();	
		}
				
		switch (this.action) 
		{
			case 'add':
				this.setTitle(lang['izveschenie_o_bolnom_venericheskim_zabolevaniem_dobavlenie']);
				this.setFieldsDisabled(false);
				break;
			case 'view':
				this.setTitle(lang['izveschenie_o_bolnom_venericheskim_zabolevaniem_prosmotr']);
				this.setFieldsDisabled(true);
				break;
		}
		
	},
	loadDiagCombo: function(){
		if(getRegionNick().inlist(['kz'])) return false;

		var base_form = this.FormPanel.getForm();
		var diag_combo = base_form.findField('Diag_Name');
		var diag_id = base_form.findField('Diag_id').getValue();
		if ( diag_id ) {
			diag_combo.getStore().load({
				callback: function(rec) {
					base_form.findField('VenerLabConfirmType_id').setAllowBlank(true);
					if(rec && rec[0]){
						var diag_code = rec[0].get('Diag_Code');	
						if(diag_code){
							base_form.findField('VenerLabConfirmType_id').setAllowBlank(diag_code.inlist(['A60.0', 'A60.1', 'A60.9', 'A63.0', 'B86', 'B86.']));
						}
						// diag_combo.setValue(diag_id);
					}
				}.createDelegate(this),
				params: { where: "where Diag_id = " + diag_id }
			});
		}
	},
	setVisibleFields: function(){
		var base_form = this.FormPanel.getForm();
		var isRegionKZ = (getRegionNick().inlist(['kz'])) ? true : false;
		var container = base_form.formPanel.findById('dopBlockFormEvnNotifyVenerEditWindow');
		var venerLabConfirmTypeCombo = base_form.findField('VenerLabConfirmType_id'); //Лабораторное подтверждение
		var venerDetectionPlaceTypeCombo = base_form.findField('VenerDetectionPlaceType_id'); //Место выявления заболевания
		var fieldOtherDetectPlace = base_form.findField('EvnNotifyVener_OtherDetectPlace'); //другое место выявления заболевания
		var diagCombo = base_form.findField('Diag_Name');
		var venerLabConfirmType_id = venerLabConfirmTypeCombo.getValue();
		var VenerDetectionPlaceType_id = venerDetectionPlaceTypeCombo.getValue();
		if( !isRegionKZ ){
			base_form.findField('VenerSocGroup_id').showContainer();
			base_form.findField('EvnNotifyVener_Pathogen').showContainer();
			if(venerLabConfirmType_id && venerLabConfirmType_id == 2){
				container.show();
				this.setHeight(this.height + 60);
			}else{
				container.hide();
				this.setHeight(this.height);
			}

			if(venerLabConfirmType_id && venerLabConfirmType_id == 4){
				//Другое
				base_form.findField('EvnNotifyVener_OtherLabConfirm').showContainer();
			}else{
				base_form.findField('EvnNotifyVener_OtherLabConfirm').hideContainer();
			}

			if(VenerDetectionPlaceType_id && VenerDetectionPlaceType_id == 3){
				//В стационаре
				base_form.findField('LpuSectionProfile_id').showContainer();
			}else{
				base_form.findField('LpuSectionProfile_id').hideContainer();
			}

			if(VenerDetectionPlaceType_id && VenerDetectionPlaceType_id.inlist([6,7])){
				fieldOtherDetectPlace.showContainer();
				fieldOtherDetectPlace.setAllowBlank(false);
			}else{
				fieldOtherDetectPlace.hideContainer();
				fieldOtherDetectPlace.setAllowBlank(true);
			}
		}else{
			base_form.findField('VenerSocGroup_id').hideContainer();
			container.hide();
			base_form.findField('EvnNotifyVener_Pathogen').hideContainer();
		}
	},
	initComponent: function() 
	{
		var _this = this;
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
			autoHeight: false,
			labelAlign: 'right',
			labelWidth: 250,
			url:'/?c=EvnNotifyVener&m=save',
			items: 
			[{
				region: 'north',
				layout: 'form',
				xtype: 'panel',
				items: [{
					name: 'EvnNotifyVener_id',
					xtype: 'hidden'
				}, {
					name: 'EvnNotifyVener_pid',
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
				},  {
					name: 'Diag_id',
					xtype: 'hidden'
				}, {
					name: 'EvnNotifyVener_setDT',
					xtype: 'hidden'
				}, {
					fieldLabel: lang['kategoriya_naseleniya'],
					anchor:'100%',
					hiddenName: 'PersonCategoryType_id',
					xtype: 'swcommonsprcombo',
					allowBlank: true,
					sortField:'PersonCategoryType_Code',
					comboSubject: 'PersonCategoryType',
					loadParams: {
						params: {where: " where PersonCategoryType_id in (1,2,4,6,8,10)"}
					},
				}, {
					fieldLabel: langs('Социальная группа'),
					hiddenName: 'VenerSocGroup_id',
					xtype: 'swvenersocgroupcombo',
					anchor:'100%',
					disabled: getRegionNick().inlist(['kz']),
					allowBlank: (getRegionNick().inlist(['kz']))
				},				
				{
					fieldLabel: lang['vedomstvo'],
					name: 'EvnNotifyVener_OtherPersonCategory',
					allowBlank:true,
					disabled: true,
					anchor:'100%',
					xtype: 'textfield'
				}, {
					changeDisabled: false,
					disabled: true,
					fieldLabel: lang['diagnoz'],
					hiddenName: 'Diag_Name',
					anchor:'100%',
					xtype: 'swdiagcombo'
				}, {
					fieldLabel: lang['data_ustanovleniya_diagnoza'],
					name: 'EvnNotifyVener_DiagDT',
					id: 'ENVEW_EvnNotifyVener_DiagDT',
					allowBlank:true,
					listeners:{
						'change':function (field, newValue, oldValue) {
							var base_form = _this.FormPanel.getForm(),
								index,
								LpuSectionProfile_id = base_form.findField('LpuSectionProfile_id').getValue();

							// Фильтруем список профилей отделений
							base_form.findField('LpuSectionProfile_id').clearValue();
							base_form.findField('LpuSectionProfile_id').getStore().clearFilter();
							base_form.findField('LpuSectionProfile_id').lastQuery = '';

							base_form.findField('LpuSectionProfile_id').setBaseFilter(function (rec) {
								var setDate = base_form.findField('EvnNotifyVener_DiagDT').getValue();

								if (!Ext.isEmpty(setDate)) {
									return (Ext.isEmpty(rec.get('LpuSectionProfile_begDT')) || rec.get('LpuSectionProfile_begDT') <= setDate)
									&& (Ext.isEmpty(rec.get('LpuSectionProfile_endDT')) || rec.get('LpuSectionProfile_endDT') >= setDate);
								} else {
									return true;
								}
							}.createDelegate(this));

							index = base_form.findField('LpuSectionProfile_id').getStore().findBy(function(rec) {
								return (rec.get('LpuSectionProfile_id') == LpuSectionProfile_id);
							});

							if ( index >= 0 ) {
								base_form.findField('LpuSectionProfile_id').setValue(LpuSectionProfile_id);
								base_form.findField('LpuSectionProfile_id').fireEvent('select', base_form.findField('LpuSectionProfile_id'), base_form.findField('LpuSectionProfile_id').getStore().getAt(index));
							}
						}
					},
					xtype: 'swdatefield',
					plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
				}, {
					fieldLabel: lang['reinfektsiya'],
					width: 70,
					hiddenName: 'EvnNotifyVener_IsReInfect',
					xtype: 'swyesnocombo',
					allowBlank: true
				}, {
					fieldLabel: lang['put_peredachi'],
					anchor:'100%',
					hiddenName: 'VenerPathTransType_id',
					xtype: 'swcommonsprcombo',
					allowBlank: true,
					sortField:'VenerPathTransType_Code',
					comboSubject: 'VenerPathTransType'
				}, {
					fieldLabel: lang['period_beremennosti'],
					anchor:'100%',
					hiddenName: 'VenerPregPeriodType_id',
					xtype: 'swcommonsprcombo',
					allowBlank: true,
					sortField:'VenerPregPeriodType_Code',
					comboSubject: 'VenerPregPeriodType'
				}, {
					fieldLabel: lang['laboratornoe_podtverjdenie'],
					anchor:'100%',
					hiddenName: 'VenerLabConfirmType_id',
					xtype: 'swcommonsprcombo',
					allowBlank: true,
					sortField:'VenerLabConfirmType_Code',
					comboSubject: 'VenerLabConfirmType',
					loadParams: {
						params: (getRegionNick() == 'kz') ? {where: " where VenerLabConfirmType_id <> 5"} : ''
					},
					listeners: {
						change: function(combo,newValue,oldValue) {
							if(getRegionNick().inlist(['kz'])) return false;
							var base_form = this.FormPanel.getForm();
							var fieldsArr = ['EvnNotifyVener_IsKSP', 'EvnNotifyVener_IsRMP', 'EvnNotifyVener_IsRPR', 'EvnNotifyVener_IsRPGA', 'EvnNotifyVener_IsIFA', 'EvnNotifyVener_IsRIF', 'EvnNotifyVener_IsRIBT', 'EvnNotifyVener_IsTPM', 'EvnNotifyVener_IsImmun'];
							if(newValue != oldValue) {
								fieldsArr.forEach(function(item, i, arr){
									var elem = base_form.findField(item);
									if(elem) elem.setValue(0);
								});
								this.setVisibleFields();
							}
						}.createDelegate(this)
					}
				}, 
				{
					autoHeight: true,
					style: 'padding: 5px; margin: 5px 5px 5px 260px;',
					title: '',
					xtype: 'fieldset',
					id: 'dopBlockFormEvnNotifyVenerEditWindow',
					hidden: (getRegionNick().inlist(['kz'])),
					items: [
						{
							border: true,
							column: 3,
							style: 'padding: 5px;',
							layout: 'column',
							items: [
								{
									border: false,
									labelWidth: 60,
									layout: 'form',
									items: [
										{
											xtype: 'checkbox',
											labelSeparator: '',
											boxLabel: 'КСР',
											name: 'EvnNotifyVener_IsKSR'
										},
										{
											xtype: 'checkbox',
											labelSeparator: '',
											boxLabel: 'РМП',
											name: 'EvnNotifyVener_IsRMP'
										},
										{
											xtype: 'checkbox',
											labelSeparator: '',
											boxLabel: 'РПР',
											name: 'EvnNotifyVener_IsRPR'
										}
									]
								},
								{
									border: false,
									layout: 'form',
									labelWidth: 60,
									items: [
										{
											xtype: 'checkbox',
											labelSeparator: '',
											boxLabel: 'РПГА',
											name: 'EvnNotifyVener_IsRPGA'
										},
										{
											xtype: 'checkbox',
											labelSeparator: '',
											boxLabel: 'ИФА',
											name: 'EvnNotifyVener_IsIFA'
										},
										{
											xtype: 'checkbox',
											labelSeparator: '',
											boxLabel: 'РИФ',
											name: 'EvnNotifyVener_IsRIF'
										}
									]
								},
								{
									border: false,
									layout: 'form',
									labelWidth: 60,
									items: [
										{
											xtype: 'checkbox',
											labelSeparator: '',
											boxLabel: 'РИБТ',
											name: 'EvnNotifyVener_IsRIBT'
										},
										{
											xtype: 'checkbox',
											labelSeparator: '',
											boxLabel: 'ТПМ',
											name: 'EvnNotifyVener_IsTPM'
										},
										{
											xtype: 'checkbox',
											labelSeparator: '',
											boxLabel: 'Иммуноблот',
											name: 'EvnNotifyVener_IsImmun'
										}
									]
								}
							]
						}
					]
				},
				{
					fieldLabel: lang['vid_laboratornogo_podtverjdeniya'],
					name: 'EvnNotifyVener_OtherLabConfirm',
					disabled: true,
					allowBlank:true,
					anchor:'100%',
					xtype: 'textfield'
				}, 
				{
					fieldLabel: 'Выявленный возбудитель',
					name: 'EvnNotifyVener_Pathogen',
					allowBlank:true,
					anchor:'100%',
					xtype: 'textfield'
				},
				{
					fieldLabel: lang['mesto_vyiyavleniya_zabolevaniya'],
					anchor:'100%',
					hiddenName: 'VenerDetectionPlaceType_id',
					xtype: 'swcommonsprcombo',
					allowBlank: true,
					sortField:'VenerDetectionPlaceType_Code',
					comboSubject: 'VenerDetectionPlaceType',
					listeners: {
						change: function(combo,newValue,oldValue) {
							if(newValue != oldValue) this.setVisibleFields();
						}.createDelegate(this)
					}
				}, {
					fieldLabel: lang['profil_koyki'],
					anchor:'100%',
					id: 'ENVEW_LpuSectionProfile_id',
					hiddenName: 'LpuSectionProfile_id',
					xtype: 'swlpusectionprofilecombo',
					allowBlank: true
				}, {
					fieldLabel: lang['spetsialist_vyiyavivshiy_zabolevanie'],
					hiddenName: 'MedPersonal_pid',
					name: 'MedPersonal_pid',
					anchor:'100%',
					editable: true,
					xtype: 'swmedpersonalcombo',
					allowBlank: true
				}, {
					fieldLabel: lang['drugoe_mesto_vyiyavleniya_zabolevaniya'],
					anchor:'100%',
					hiddenName: 'EvnNotifyVener_OtherDetectPlace',
					name: 'EvnNotifyVener_OtherDetectPlace',
					disabled: true,
					allowBlank:true,
					anchor:'100%',
					xtype: 'textfield'
				}, {
					fieldLabel: lang['obstoyatelstva_vyiyavleniya'],
					anchor:'100%',
					hiddenName: 'VenerDetectionFactType_id',
					xtype: 'swcommonsprcombo',
					allowBlank: true,
					sortField:'VenerDetectionFactType_Code',
					comboSubject: 'VenerDetectionFactType',
					loadParams: {
						params: (getRegionNick() == 'kz') ? {where: " where VenerDetectionFactType_id <> 8"} : ''
					}
				}, {
					fieldLabel: lang['spetsialist_k_kotoromu_obratilis'],
					hiddenName: 'MedPersonal_fid',
					name: 'MedPersonal_fid',
					anchor:'100%',
					editable: true,
					xtype: 'swmedpersonalallcombo',
					allowBlank: true
				}, {
					fieldLabel: lang['drugie_obstoyatelstva_vyiyavleniya'],
					anchor:'100%',
					hiddenName: 'EvnNotifyVener_OtherDetectFact',
					name: 'EvnNotifyVener_OtherDetectFact',
					disabled: true,
					allowBlank:true,
					anchor:'100%',
					xtype: 'textfield'
				}, {
					changeDisabled: false,
					disabled: true,
					fieldLabel: lang['vrach_zapolnivshiy_izveschenie'],
					hiddenName: 'MedPersonal_id',
					anchor:'100%',
					xtype: 'swmedpersonalcombo'
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
		sw.Promed.swEvnNotifyVenerEditWindow.superclass.initComponent.apply(this, arguments);

		this.findById('ENVEW_LpuSectionProfile_id').setBaseFilter(function(rec) {
			var setDate = this.findById('ENVEW_EvnNotifyVener_DiagDT').getValue();

			if ( !Ext.isEmpty(setDate)) {
				return (Ext.isEmpty(rec.get('LpuSectionProfile_begDT')) || rec.get('LpuSectionProfile_begDT') <= setDate)
						&& (Ext.isEmpty(rec.get('LpuSectionProfile_endDT')) || rec.get('LpuSectionProfile_endDT') >= setDate);
			} else {
				return true;
			}
		}.createDelegate(this));
	}
});
