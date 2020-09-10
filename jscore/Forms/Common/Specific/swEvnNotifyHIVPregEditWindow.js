/**
* swEvnNotifyHIVPregEditWindow - Извещение о случае завершения  беременности у ВИЧ-инфицированной женщины (форма N  313/у)
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009-2010 Swan Ltd.
* @author       Alexander Permyakov 
* @version      2012/12
*/

sw.Promed.swEvnNotifyHIVPregEditWindow = Ext.extend(sw.Promed.BaseForm, 
{
	action: null,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	formMode: 'remote',
	formStatus: 'edit',
	layout: 'border',
	modal: true,
	maximized : true,
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

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
		loadMask.show();
		
		params.MedPersonal_id = base_form.findField('MedPersonal_id').getValue();
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
				
				showSysMsg(lang['izveschenie_sozdano']);
				win.formStatus = 'edit';
				loadMask.hide();
				var data = {};
				if (typeof action.result == 'object') {
					data = action.result;
					win.callback(data);
					win.hide();
					if(base_form.findField('HIVPregResultType_id').getValue() == 1)
					{
						//при указании значения «родами» в поле «Беременность закончилась»
						var params = {
							EvnNotifyHIVBorn_id: null
							,formParams: {
								EvnNotifyHIVBorn_id: null
								,EvnNotifyHIVBorn_pid: base_form.findField('EvnNotifyHIVPreg_pid').getValue()
								,Morbus_id: base_form.findField('Morbus_id').getValue()
								,Person_mid: base_form.findField('Person_id').getValue()
								,mother_fio: win.InformationPanel.getFieldValue('Person_Surname') +' '+ win.InformationPanel.getFieldValue('Person_Firname') +' '+ win.InformationPanel.getFieldValue('Person_Secname')
								,EvnNotifyHIVBorn_HIVDT: base_form.findField('EvnNotifyHIVPreg_DiagDT').getValue()
								,HIVPregPathTransType_id: base_form.findField('HIVPregPathTransType_id').getValue()
								,EvnNotifyHIVBorn_setDT: base_form.findField('EvnNotifyHIVPreg_setDT').getValue()
								,MedPersonal_id: base_form.findField('MedPersonal_id').getValue()
							}
							,callback: function (data) {
								//conf.EvnNotifyHIVBorn_id = data.EvnNotifyHIVBorn_id;
							}
						};
						sw.swMsg.show({
							buttons: Ext.Msg.YESNO,
							fn: function( buttonId )
							{
								if ( buttonId == 'yes' ) {
									getWnd('swEvnNotifyHIVBornEditWindow').show(params);
								}
							},
							msg: lang['sozdat_izveschenie_o_novorojdennom_rojdennom_vich-infitsirovannoy_materyu_forma_n_309_u'],
							title: lang['vopros']
						});
					}
				}
			}
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
		sw.Promed.swEvnNotifyHIVPregEditWindow.superclass.show.apply(this, arguments);
		
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
		this.EvnNotifyHIVPreg_id = arguments[0].EvnNotifyHIVPreg_id || null;
		this.action = (( this.EvnNotifyHIVPreg_id ) && ( this.EvnNotifyHIVPreg_id > 0 ))?'view':'add';
		this.formMode = (arguments[0].formMode && typeof arguments[0].formMode == 'string' && arguments[0].formMode.inlist([ 'local', 'remote' ]))?arguments[0].formMode:'remote';
		this.owner = arguments[0].owner || null;
		this.callback = arguments[0].callback || Ext.emptyFn;
		this.onHide = arguments[0].onHide || Ext.emptyFn;
		
		base_form.setValues(arguments[0].formParams);
		
		var loadMask = new Ext.LoadMask(this.getEl(),{msg: LOAD_WAIT});
		loadMask.show();
		
		//var lpu_combo = base_form.findField('Lpuifa_id');

		if (this.action != 'add') {
			switch (this.action) 
			{
				case 'view':
					this.setTitle(lang['izveschenie_o_sluchae_zaversheniya_beremennosti_u_vich-infitsirovannoy_jenschinyi_forma_n_313_u_prosmotr']);
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
					EvnNotifyHIVPreg_id: this.EvnNotifyHIVPreg_id
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
					loadMask.hide();
				}.createDelegate(this),
				url:'/?c=EvnNotifyHIVPreg&m=load'
			});			
		} else {
			this.setTitle(lang['izveschenie_o_sluchae_zaversheniya_beremennosti_u_vich-infitsirovannoy_jenschinyi_forma_n_313_u_dobavlenie']);
			this.setFieldsDisabled(false);
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
			/*lpu_combo.getStore().load({
				callback: function () {
					if ( lpu_combo.getStore().getCount() > 0 ) {
						//lpu_combo.setValue(getGlobalOptions().lpu_id);
					}
				}
			});*/
			base_form.findField('EvnNotifyHIVPreg_setDT').setValue(getGlobalOptions().date);
			loadMask.hide();			
		}		
	},	
	initComponent: function() 
	{
		
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
			labelWidth: 250,
			autoScroll:true,
			url:'/?c=EvnNotifyHIVPreg&m=save',
			items: 
			[{
				region: 'center',
				layout: 'form',
				xtype: 'panel',
				items: [{
					name: 'EvnNotifyHIVPreg_id',
					xtype: 'hidden'
				}, {
					name: 'EvnNotifyHIVPreg_pid',
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
					fieldLabel: lang['predpolagaemyiy_put_infitsirovaniya'],
					comboSubject: 'HIVPregPathTransType',
					width: 300,
					listWidth: 500,
					typeCode: 'int',
					autoLoad: true,
					xtype: 'swcommonsprcombo'
				}, {
					autoHeight: true,
					title: lang['diagnoz_vich-infektsii_ustanovlen'],
					xtype: 'fieldset',
					items: [{
						fieldLabel: lang['data_ustanovleniya'],
						name: 'EvnNotifyHIVPreg_DiagDT',
						xtype: 'swdatefield',
						plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
					}, {
						fieldLabel: lang['period_ustanovleniya'],
						comboSubject: 'HIVPregPeriodType',
						width: 300,
						listWidth: 500,
						typeCode: 'int',
						autoLoad: true,
						xtype: 'swcommonsprcombo'
						,listeners: {
							'beforeselect': function(combo, record){
								var srok_fld = this.FormPanel.getForm().findField('EvnNotifyHIVPreg_Srok');
								if (srok_fld && combo.codeField && record.get(combo.codeField)) {
									var code = record.get(combo.codeField);
									if (code == 2) // «во время беременности»
									{
										srok_fld.setDisabled(false);
										srok_fld.setAllowBlank(false);
										srok_fld.focus(true, 250);
									}
									else
									{
										srok_fld.setDisabled(true);
										srok_fld.setAllowBlank(true);
									}
								}
							}.createDelegate(this)
						}
					}, {
						fieldLabel: lang['srok_beremennosti_nedeli'],
						name: 'EvnNotifyHIVPreg_Srok',
						changeDisabled: false,
						disabled: true,
						xtype: 'numberfield',
						maxValue: 50,
						width: 70,
						allowDecimals: false,
						allowNegative: false
					}]
				}, {
					autoHeight: true,
					title: lang['stadiya_vich-infektsii'],
					xtype: 'fieldset',
					items: [{
						fieldLabel: lang['pri_vzyatii_na_uchet_po_beremennosti'],
						comboSubject: 'HIVPregInfectStudyType',
						hiddenName: 'HIVPregInfectStudyType_id',
						width: 300,
						listWidth: 500,
						typeCode: 'int',
						autoLoad: true,
						xtype: 'swcommonsprcombo'
					}, {
						fieldLabel: lang['pri_zavershenii_beremennosti'],
						comboSubject: 'HIVPregInfectStudyType',
						hiddenName: 'HIVPregInfectStudyType_did',
						allowBlank:true,
						width: 300,
						listWidth: 500,
						typeCode: 'int',
						autoLoad: true,
						xtype: 'swcommonsprcombo'
					}]
				}, {
					fieldLabel: lang['data_zaversheniya_beremennosti'],
					name: 'EvnNotifyHIVPreg_endDT',
					xtype: 'swdatefield',
					plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
				}, {
					fieldLabel: lang['beremennost_zakonchilas'],
					comboSubject: 'HIVPregResultType',
					hiddenName: 'HIVPregResultType_id',
					width: 300,
					listWidth: 500,
					typeCode: 'int',
					autoLoad: true,
					xtype: 'swcommonsprcombo'
				}, {
					autoHeight: true,
					title: lang['rodyi'],
					xtype: 'fieldset',
					items: [{
						fieldLabel: lang['prejdevremennyie_rodyi'],
						xtype: 'swyesnocombo',
						width: 80,
						hiddenName: 'EvnNotifyHIVPreg_IsPreterm'
					}, {
						fieldLabel: lang['sposob_rodorazresheniya'],
						comboSubject: 'HIVPregWayBirthType',
						hiddenName: 'HIVPregWayBirthType_id',
						width: 300,
						listWidth: 500,
						typeCode: 'int',
						autoLoad: true,
						xtype: 'swcommonsprcombo'
					}, {
						fieldLabel: lang['prodoljitelnost_rodov_v_chasah'],
						name: 'EvnNotifyHIVPreg_DuratBirth',
						xtype: 'numberfield',
						width: 70,
						allowDecimals: false,
						allowNegative: false
					}, {
						fieldLabel: lang['prodoljitelnost_bezvodnogo_promejutka_v_chasah'],
						name: 'EvnNotifyHIVPreg_DuratWaterless',
						xtype: 'numberfield',
						width: 70,
						allowDecimals: false,
						allowNegative: false
					}]
				}, {
					autoHeight: true,
					title: lang['himioprofilaktika'],
					xtype: 'fieldset',
					items: [{
						fieldLabel: lang['v_period_beremennosti'],
						comboSubject: 'HIVPregChemProphType',
						hiddenName: 'HIVPregChemProphType_id',
						width: 300,
						listWidth: 500,
						typeCode: 'int',
						autoLoad: true,
						xtype: 'swcommonsprcombo'
						,listeners: {
							'beforeselect': function(combo, record){
								var srok_fld = this.FormPanel.getForm().findField('EvnNotifyHIVPreg_SrokChem');
								if (srok_fld && combo.codeField && record.get(combo.codeField)) {
									var code = record.get(combo.codeField);
									if (code  == 2) // «неполный курс»
									{
										srok_fld.setDisabled(false);
										srok_fld.setAllowBlank(false);
										srok_fld.focus(true, 250);
									}
									else
									{
										srok_fld.setDisabled(true);
										srok_fld.setAllowBlank(true);
									}
								}
							}.createDelegate(this)
						}
					}, {
						fieldLabel: lang['srok_beremennosti_nedeli'],
						name: 'EvnNotifyHIVPreg_SrokChem',
						changeDisabled: false,
						disabled: true,
						xtype: 'numberfield',
						maxValue: 50,
						width: 70,
						allowDecimals: false,
						allowNegative: false
					}, {
						fieldLabel: lang['himioprofilaktika_v_rodah'],
						xtype: 'swyesnocombo',
						width: 80,
						hiddenName: 'EvnNotifyHIVPreg_IsChemProphBirth'
					}]
				}, {
					autoHeight: true,
					title: lang['abort'],
					xtype: 'fieldset',
					items: [{
						fieldLabel: lang['srok'],
						comboSubject: 'HIVPregAbortPeriodType',
						hiddenName: 'HIVPregAbortPeriodType_id',
						width: 300,
						listWidth: 500,
						typeCode: 'int',
						autoLoad: true,
						xtype: 'swcommonsprcombo'
					}, {
						fieldLabel: lang['abort'],
						comboSubject: 'AbortType',
						hiddenName: 'AbortType_id',
						width: 300,
						listWidth: 500,
						typeCode: 'int',
						autoLoad: true,
						xtype: 'swcommonsprcombo'
					}]
				}, {
					fieldLabel: lang['data_zapolneniya_izvescheniya'],
					name: 'EvnNotifyHIVPreg_setDT',
					allowBlank: false,
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
				text: BTN_FRMCLOSE
			}],
			items: [this.InformationPanel, this.FormPanel]
		});
		sw.Promed.swEvnNotifyHIVPregEditWindow.superclass.initComponent.apply(this, arguments);
	}
});