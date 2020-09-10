/**
* swChangeSmoDemandEditWindow - окно редактирования/добавления заявки на прикрепление к СМО.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Salackhov Rustam
* @version      0.001-18.03.2010
* @comment      Префикс для id компонентов ADE (ChangeSmoDemandEdit)
*/

sw.Promed.swChangeSmoDemandEditWindow = Ext.extend(sw.Promed.BaseForm, {
	action: null,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: false,
	closeAction: 'hide',
	collapsible: true,
	ChangeSmoDemand_id: 0,
	
	doSave: function(options) {
		if ( this.formStatus == 'save' || this.action == 'view' ) {
			return false;
		}
		this.formStatus = 'save';
	
		var base_form = this.findById('demand_edit_form').getForm();
		var person_frame = this.findById('CSDE_PersonInformationFrame');
		var params = new Object();
		
		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.formStatus = 'edit';
					this.findById('demand_edit_form').getFirstInvalidEl().focus(false);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		
		//todo: +проверка на непустой ЛПУ, +проверка на повторное прикрепление к томуже ЛПУ 
		
		var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Подождите, идет сохранение заявки..." });
		loadMask.show();
		
		params.action = this.action;
		params.ChangeSmoDemand_id = base_form.findField('ChangeSmoDemand_id').getValue();
		params.Person_id = person_frame.personId;
		params.Server_id = person_frame.serverId;
		params.PersonEvn_id = person_frame.getFieldValue('PersonEvn_id');
		params.Smo_Id = this.findById('Smo_id').getValue();
		params.SmoUnit_Id = this.findById('SmoUnit_id').getValue();
		params.Polis_Org = base_form.findField('Polis_Org').getValue();
		params.Polis_Ser = base_form.findField('Polis_Ser').getValue();
		params.Polis_Num = base_form.findField('Polis_Num').getValue();	

		if (base_form.findField('DemandState_id')) params.DemandState_id = base_form.findField('DemandState_id').getValue();	
		if (base_form.findField('State_Comment')) params.State_Comment = base_form.findField('State_Comment').getValue();	
		
		
		var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Подождите, идет сохранение заявки..." });
		loadMask.show();

		base_form.submit({
			failure: function(result_form, action) {
				this.formStatus = 'edit';
				loadMask.hide();

				if ( action.result ) {
					if ( action.result.Error_Msg ) {
						sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
					}
					else {
						sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_1]']);
					}
				}
			}.createDelegate(this),
			params: params,
			success: function(result_form, action) {
				this.formStatus = 'edit';
				loadMask.hide();				
				/*if ( action.result ) {
					if ( action.result.EvnPS_id && action.result.EvnPrehosp_id ) {
						var evn_prehosp_id = action.result.EvnPrehosp_id;
						var evn_ps_id = action.result.EvnPS_id;

						base_form.findField('EvnPrehosp_id').setValue(evn_prehosp_id);
						base_form.findField('EvnPS_id').setValue(evn_ps_id);

						if ( options && typeof options.openChildWindow == 'function' && this.action == 'add' ) {
							options.openChildWindow();
						}
						else {
							var date = null;
							var person_information = this.findById('EPSEF_PersonInformationFrame');
							var response = new Object();

							response.EvnPrehosp_id = evn_prehosp_id;
							response.EvnPS_id = evn_ps_id;
							response.EvnPS_disDate = null;
							response.EvnPS_Koika = 0;
							response.EvnPS_NumCard = base_form.findField('EvnPS_NumCard').getValue();
							response.EvnPS_setDate = base_form.findField('EvnPS_setDate').getValue();
							response.Person_Birthday = person_information.getFieldValue('Person_Birthday');
							response.Person_Firname = person_information.getFieldValue('Person_Firname');
							response.Person_id = base_form.findField('Person_id').getValue();
							response.Person_Secname = person_information.getFieldValue('Person_Secname');
							response.Person_Surname = person_information.getFieldValue('Person_Surname');
							response.PersonEvn_id = base_form.findField('PersonEvn_id').getValue();
							response.Server_id = base_form.findField('Server_id').getValue();

							this.callback({ evnPSData: response });

							if ( options && options.print == true ) {
								window.open('/?c=EvnPS&m=printEvnPS&EvnPS_id=' + evn_ps_id, '_blank');

								this.action = 'edit';
								this.setTitle(WND_HOSP_EPSEDIT);
							}
							else {
								this.hide();
							}
						}
					}
					else {
						if ( action.result.Error_Msg ) {
							sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
						}
						else {
							sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_3]']);
						}
					}
				} else {
					sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_2]']);
				}*/
				this.hide;
				
				Ext.getCmp('ADLVW_ChangeSmoDemandListGrid').ViewGridStore.reload();
			}.createDelegate(this)
		});		
	},
	draggable: true,
	height: 560,
	id: 'ChangeSmoDemandEditWindow',
	initComponent: function() {
		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'save16',
				onShiftTabAction: Ext.emptyFn,
				onTabAction: Ext.emptyFn,
				text: BTN_FRMSAVE
			}, {
				text: '-'
			},
			{
				handler: function() {
					this.onCancelAction();
				}.createDelegate(this),
				iconCls: 'cancel16',
				onShiftTabAction: function () {
					this.buttons[1].focus();
				}.createDelegate(this),
				onTabAction: Ext.emptyFn,
				text: BTN_FRMCANCEL
			}],
			items: [ 				
				new Ext.FormPanel({
					frame: true,
					autoHeight: true,
					labelAlign: 'right',
					id: 'demand_edit_form',
					labelWidth: 150,
					bodyStyle:'background:#FFFFFF;padding:5px;',
					url: '/?c=Demand&m=saveChangeSmoDemand',
					items: 
					[
						{
							id: 'CSDE_Person_id',
							name: 'CSDE_Person_id',
							value: 0,
							xtype: 'hidden'
						}, {
							id: 'CSDE_Server_id',
							name: 'CSDE_Server_id',
							value: 0,
							xtype: 'hidden'
						}, {
							id: 'CSDE_Smo_id',
							name: 'CSDE_Smo_id',
							value: 0,
							xtype: 'hidden'
						}, {
							id: 'CSDE_SmoUnit_id',
							name: 'CSDE_SmoUnit_id',
							value: 0,
							xtype: 'hidden'
						}, {
							name: 'ChangeSmoDemand_id',
							value: 0,
							xtype: 'hidden'
						}, 
						{
							xtype: 'fieldset',
							autoHeight: true,
							title: lang['chelovek'],
							style: 'padding-top: 5px; padding: 5px; margin: 0',
							items: [
								new sw.Promed.PersonInformationPanel({
									button1OnHide: function() {
										if ( this.action == 'view' ) {
											this.buttons[this.buttons.length - 1].focus();
										}
										else {
											this.findById('ChangeSmoDemandEditForm').getForm().findField('EvnPS_NumCard').focus(true);
										}
									}.createDelegate(this),
									button2Callback: function(callback_data) {
										var form = this.findById('ChangeSmoDemandEditForm');

										form.getForm().findField('PersonEvn_id').setValue(callback_data.PersonEvn_id);
										form.getForm().findField('Server_id').setValue(callback_data.Server_id);

										this.findById('CSDE_PersonInformationFrame').load({ Person_id: callback_data.Person_id, Server_id: callback_data.Server_id });
									}.createDelegate(this),
									button2OnHide: function() {
										this.findById('CSDE_PersonInformationFrame').button1OnHide();
									}.createDelegate(this),
									button3OnHide: function() {
										this.findById('CSDE_PersonInformationFrame').button1OnHide();
									}.createDelegate(this),
									button4OnHide: function() {
										this.findById('CSDE_PersonInformationFrame').button1OnHide();
									}.createDelegate(this),
									button5OnHide: function() {
										this.findById('CSDE_PersonInformationFrame').button1OnHide();
									}.createDelegate(this),
									id: 'CSDE_PersonInformationFrame',
									region: 'north'
								})
							]
						},
						{	
							xtype: 'fieldset',
							autoHeight: true,
							title: lang['smo'],
							id: 'smo_fs',
							style: 'padding-top: 5px; padding: 5px; margin: 0',							
							items: [{
								allowBlank: false,
								disabled: false,
								id: 'Smo_id',
								//anchor: '100%',
								width:550,
								hiddenName: 'hSmo_id',
								xtype: 'swcomplexorgsmocombo',
								tabIndex:4213,
								listeners: {
									'select': function(combo, record, index) {									
										var filial_combo = Ext.getCmp('SmoUnit_id');
										
										if ( record.get(combo.valueField) ) {											
											filial_combo.getStore().load({
												callback: function() {													
													if(filial_combo.getStore().getCount() > 0 ) {																																									
														filial_combo.setValue(filial_combo.getStore().getAt(0).get(filial_combo.valueField));
														filial_combo.enable();
													} else {
														filial_combo.clearValue();
														filial_combo.disable();
													}
												},
												params: {
													Org_id: record.get('Smo_id')
												}
											});
										}
									}
								}		
							}, {
								allowBlank: false,
								disabled: false,
								id: 'SmoUnit_id',
								//anchor: '100%',
								width:550,
								hiddenName: 'hSmoUnit_id',
								xtype: 'swcomplexorgsmofilialcombo',
								tabIndex:4214/*,
								initComponent: function() {
									Ext.getCmp('SmoUnit_id').disable();
								}*/
							}]
						},
						{
							xtype: 'fieldset',
							autoHeight: true,
							title: lang['pasport'],
							style: 'padding-top: 5px; padding: 5px; margin: 0',
							items: [
								{
									layout: 'column',
									border: false,									
									items: [{
										layout: 'form',
										items: [{
											allowBlank: false,
											fieldLabel: lang['seriya'],
											maxLength: 10,
											name: 'Pasport_Ser',
											plugins: [ new Ext.ux.translit(true, true) ],
											tabIndex: TABINDEX_PEF + 11,
											width: 100,
											xtype: 'textfield'
										}]
									}, {
										layout: 'form',
										labelWidth: 94,
										items:[{
											allowBlank: false,
											xtype: 'textfield',
											maskRe: /\d/,
											maxLength: 20,
											width: 100,
											fieldLabel: lang['nomer'],
											name: 'Pasport_Num',
											tabIndex: TABINDEX_PEF + 12
										}]
									}]
								},
								{
									layout: 'column',
									border: false,	
									items: [{
										layout: 'form',
										items: [{
											allowBlank: false,
											fieldLabel: lang['kogda_vyidan'],
											maxLength: 25,
											name: 'Pasport_TimeInf',											
											tabIndex: TABINDEX_PEF + 14,
											width: 100,
											xtype: 'textfield'
										}]
									}, {
										layout: 'form',
										labelWidth: 94,
										items: [{
											allowBlank: false,
											fieldLabel: lang['kem_vyidan'],
											maxLength: 100,
											name: 'Pasport_Inf',											
											tabIndex: TABINDEX_PEF + 13,
											width: 350,
											xtype: 'textfield'
										}]
									}]
								}								
							]
						},
						{
							xtype: 'fieldset',
							autoHeight: true,
							title: lang['polis'],
							style: 'padding-top: 5px; padding: 5px; margin: 0',
							items: [
								{
									layout: 'column',
									border: false,
									items: [{
										layout: 'form',										
										items: [{
											allowBlank: false,											
											fieldLabel: lang['strahovaya_organizatsiya'],
											maxLength: 50,
											name: 'Polis_Org',
											plugins: [ new Ext.ux.translit(true, true) ],
											tabIndex: TABINDEX_PEF + 15,
											width: 300,
											xtype: 'textfield'
										}]
									}]
								}, {
									layout: 'column',
									border: false,									
									items: [{
										layout: 'form',
										items: [{
											allowBlank: false,
											fieldLabel: lang['seriya'],
											maxLength: 10,
											name: 'Polis_Ser',
											plugins: [ new Ext.ux.translit(true, true) ],
											tabIndex: TABINDEX_PEF + 16,
											width: 100,
											xtype: 'textfield'
										}]
									}, {
										layout: 'form',
										labelWidth: 94,
										items:[{
											allowBlank: false,
											xtype: 'textfield',
											maskRe: /\d/,
											//allowNegative: false,
											//allowDecimals: false,
											maxLength: 20,
											width: 100,
											fieldLabel: lang['nomer'],
											name: 'Polis_Num',
											tabIndex: TABINDEX_PEF + 17
										}]
									}]
								}
							]
						},
						{	
							xtype: 'fieldset',
							id: 'StateFieldset',
							autoHeight: true,
							title: lang['dopolnitelno'],							
							style: 'padding-top: 5px; padding: 5px; margin: 0',							
							items: [
								{
									layout: 'form',										
									items: [
										{
											fieldLabel: lang['status_zayavki'],											
											width:200,
											allowBlank: false,
											disabled: false,
											id: 'DemandState_id',
											name: 'DemandState_id',
											hiddenName: 'hDemandState_id',
											xtype: 'swdemandstatecombo',
											tabIndex:4213										
										},
										{
											allowBlank: true,											
											fieldLabel: lang['kommentariy_k_zayavke'],
											maxLength: 500,											
											name: 'State_Comment',
											plugins: [ new Ext.ux.translit(true, true) ],
											tabIndex: TABINDEX_PEF + 15,
											width: 300,
											xtype: 'textarea'
										}
									]
								}
							]
						}
					],
					reader: new Ext.data.JsonReader({
							id: 'ChangeSmoDemand_id'
						}, [{
							mapping: 'ChangeSmoDemand_id',
							name: 'ChangeSmoDemand_id',
							type: 'int'
						}, {
							mapping: 'CSDE_Person_id',
							name: 'CSDE_Person_id',
							type: 'int'
						}, {
							mapping: 'CSDE_Server_id',
							name: 'CSDE_Server_id',
							type: 'int'
						}, {
							mapping: 'CSDE_Smo_id',
							name: 'CSDE_Smo_id',
							type: 'int'
						}, {
							mapping: 'CSDE_SmoUnit_id',
							name: 'CSDE_SmoUnit_id',
							type: 'int'
						}, {
							mapping: 'Smo_id',
							name: 'Smo_id',
							type: 'int'
						}, {
							mapping: 'SmoUnit_id',
							name: 'SmoUnit_id',
							type: 'int'
						}, {
							mapping: 'Pasport_Inf',
							name: 'Pasport_Inf',
							type: 'string'
						}, {
							mapping: 'Pasport_TimeInf',
							name: 'Pasport_TimeInf',
							type: 'string'							
						}, {
							mapping: 'Pasport_Ser',
							name: 'Pasport_Ser',
							type: 'string'
						}, {
							mapping: 'Pasport_Num',
							name: 'Pasport_Num',
							type: 'string'							
						}, {
							mapping: 'Polis_Org',
							name: 'Polis_Org',
							type: 'string'
						}, {
							mapping: 'Polis_Ser',
							name: 'Polis_Ser',
							type: 'string'
						}, {
							mapping: 'Polis_Num',
							name: 'Polis_Num',
							type: 'string'							
						}, {
							mapping: 'DemandState_id',
							name: 'DemandState_id',
							type: 'int'
						}, {
							mapping: 'State_Comment',
							name: 'State_Comment',
							type: 'string'
						}
						]
					),
					region: 'center'
				})			
			]
		});		
		sw.Promed.swChangeSmoDemandEditWindow.superclass.initComponent.apply(this, arguments);
	},
	layout: 'border',
	listeners: {
		'hide': function(win) {
			win.onHide();
		},
		'maximize': function(win) {
			win.findById('EPSEF_HospitalisationPanel').doLayout();
			win.findById('EPSEF_DirectDiagPanel').doLayout();
			win.findById('EPSEF_AdmitDepartPanel').doLayout();
			win.findById('EPSEF_AdmitDiagPanel').doLayout();

			if ( !win.findById('EPSEF_EvnStickPanel').hidden ) {
				win.findById('EPSEF_EvnStickPanel').doLayout();
			}

			if ( !win.findById('EPSEF_EvnUslugaPanel').hidden ) {
				win.findById('EPSEF_EvnUslugaPanel').doLayout();
			}
		},
		'restore': function(win) {
			win.fireEvent('maximize', win);
		}
	},
	maximizable: true,
	minHeight: 550,
	minWidth: 800,
	modal: true,
	onCancelAction: function() { this.hide(); },
	onHide: Ext.emptyFn,
	openEvnDiagPSEditWindow: function(action, type) {
	},
	plain: true,
	resizable: true,
	show: function() {
		sw.Promed.swChangeSmoDemandEditWindow.superclass.show.apply(this, arguments);
		
		var person_id = 0;
		var server_id = 0;
		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
		var form = this.findById('demand_edit_form');
		var pers_form = this.findById('CSDE_PersonInformationFrame');
		var base_form = form.getForm();
		
		loadMask.show();
		
		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		}
		
		switch ( this.action ) {
			case 'add':				
				if ( arguments[0].formParams ) {
					person_id = arguments[0].formParams.Person_id;
					server_id = arguments[0].formParams.Server_id;
				}				
				pers_form.load({ Person_id: person_id, Server_id: server_id });	
				this.findById('StateFieldset').hide();
				this.findById('DemandState_id').allowBlank = true;
			break;
			case 'edit':
				if ( arguments[0].formParams ) {
					var ChangeSmoDemand_id = arguments[0].formParams.ChangeSmoDemand_id;
				}
				base_form.load({
					failure: function() {
						sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_dannyih_zayavki'], function() { this.hide(); }.createDelegate(this) );
					}.createDelegate(this),
					params: {
						ChangeSmoDemand_id: ChangeSmoDemand_id
					},
					success: function() {						
						person_id = form.findById('CSDE_Person_id').getValue();
						server_id = form.findById('CSDE_Server_id').getValue();
						pers_form.load({ Person_id: person_id, Server_id: server_id });
					}.createDelegate(this),
					url: '/?c=Demand&m=loadChangeSmoDemandEditForm'
				});
				this.findById('StateFieldset').show();
				this.findById('DemandState_id').allowBlank = false;
			break;
		}
		
		var filial_combo = Ext.getCmp('SmoUnit_id');

		filial_combo.getStore().load({
			callback: function() {													
				if(filial_combo.getStore().getCount() > 0 ) {																			
					var combo_val = form.findById('CSDE_SmoUnit_id').getValue()
					if (combo_val > 0)
						filial_combo.setValue(combo_val);
					filial_combo.enable();
				} else {
					filial_combo.clearValue();
					filial_combo.disable();
				}
				Ext.getCmp('smo_fs').doLayout();
				loadMask.hide();
			},
			params: {
				Org_id: form.findById('CSDE_Smo_id').getValue()
			}
		});		
	},
	width: 800
});