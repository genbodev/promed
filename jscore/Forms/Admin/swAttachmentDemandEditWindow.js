/**
* swAttachmentDemandEditWindow - окно редактирования/добавления заявления на прикрепление к ЛПУ.
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
* @comment      Префикс для id компонентов ADE (AttachmentDemandEdit)
*/

sw.Promed.swAttachmentDemandEditWindow = Ext.extend(sw.Promed.BaseForm, {
	action: null,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: false,
	closeAction: 'hide',
	collapsible: true,
	AttachmentDemand_id: 0,
	
	doSave: function(options) {
		if ( this.formStatus == 'save' || this.action == 'view' ) {
			return false;
		}
		this.formStatus = 'save';
	
		var base_form = this.findById('demand_edit_form').getForm();
		var person_frame = this.findById('ADE_PersonInformationFrame');
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
		
		var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Подождите, идет сохранение заявления..." });
		loadMask.show();
		
		params.action = this.action;
		params.AttachmentDemand_id = base_form.findField('AttachmentDemand_id').getValue();
		params.Person_id = person_frame.personId;
		params.Server_id = person_frame.serverId;
		params.PersonEvn_id = person_frame.getFieldValue('PersonEvn_id');
		//params.Lpu_Id = this.findById('Lpu_id').getValue();
		params.Polis_Org = base_form.findField('Polis_Org').getValue();
		params.Polis_Ser = base_form.findField('Polis_Ser').getValue();
		params.Polis_Num = base_form.findField('Polis_Num').getValue();	

		if (base_form.findField('DemandState_id')) params.DemandState_id = base_form.findField('DemandState_id').getValue();	
		if (base_form.findField('State_Comment')) params.State_Comment = base_form.findField('State_Comment').getValue();	
		
		
		var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Подождите, идет сохранение заявления..." });
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
				
				Ext.getCmp('ADLVW_AttachmentDemandListGrid').ViewGridStore.reload();
				Ext.getCmp('AttachmentDemandEditWindow').hide();
			}.createDelegate(this)
		});		
	},
	draggable: true,
	height: 520,
	id: 'AttachmentDemandEditWindow',
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
					url: '/?c=Demand&m=saveAttachmentDemand',
					items: 
					[
						{
							id: 'ADE_Person_id',
							name: 'ADE_Person_id',
							value: 0,
							xtype: 'hidden'
						}, {
							id: 'ADE_Server_id',
							name: 'ADE_Server_id',
							value: 0,
							xtype: 'hidden'
						},  {
							name: 'AttachmentDemand_id',
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
											this.findById('AttachmentDemandEditForm').getForm().findField('EvnPS_NumCard').focus(true);
										}
									}.createDelegate(this),
									button2Callback: function(callback_data) {
										var form = this.findById('AttachmentDemandEditForm');

										form.getForm().findField('PersonEvn_id').setValue(callback_data.PersonEvn_id);
										form.getForm().findField('Server_id').setValue(callback_data.Server_id);

										this.findById('ADE_PersonInformationFrame').load({ Person_id: callback_data.Person_id, Server_id: callback_data.Server_id });
									}.createDelegate(this),
									button2OnHide: function() {
										this.findById('ADE_PersonInformationFrame').button1OnHide();
									}.createDelegate(this),
									button3OnHide: function() {
										this.findById('ADE_PersonInformationFrame').button1OnHide();
									}.createDelegate(this),
									button4OnHide: function() {
										this.findById('ADE_PersonInformationFrame').button1OnHide();
									}.createDelegate(this),
									button5OnHide: function() {
										this.findById('ADE_PersonInformationFrame').button1OnHide();
									}.createDelegate(this),
									id: 'ADE_PersonInformationFrame',
									region: 'north'
								})
							]
						},
						{	
							xtype: 'fieldset',
							autoHeight: true,
							title: lang['tekuschee_prikreplenie'],
							style: 'padding-top: 5px; padding: 5px; margin: 0',							
							items: [{
								allowBlank: false,
								disabled: true,
								fieldLabel: lang['lpu'],
								id: 'currentLpu_Name',								
								width:550,
								hiddenName: 'currentLpu_Name',
								xtype: 'textfield',
								tabIndex:4213
							}]
						},
						{	
							xtype: 'fieldset',
							autoHeight: true,
							title: lang['zayavlenie'],
							style: 'padding-top: 5px; padding: 5px; margin: 0',							
							items: [{
								allowBlank: false,
								disabled: true,
								fieldLabel: lang['lpu'],
								id: 'Lpu_Name',								
								width:550,
								hiddenName: 'Lpu_Name',
								xtype: 'textfield',
								tabIndex:4214
							}/*, {
								allowBlank: false,
								disabled: true,
								id: 'Lpu_id',
								//anchor: '100%',
								width:550,
								hiddenName: 'hLpu_id',
								xtype: 'swlpulocalcombo',
								tabIndex:4213
							}*/]
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
											disabled: true,
											fieldLabel: lang['strahovaya_organizatsiya'],
											maxLength: 50,
											name: 'Polis_Org',
											plugins: [ new Ext.ux.translit(true, true) ],
											tabIndex: TABINDEX_PEF + 10,
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
											disabled: true,
											fieldLabel: lang['seriya'],
											maxLength: 10,
											name: 'Polis_Ser',
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
											disabled: true,
											xtype: 'textfield',
											maskRe: /\d/,
											//allowNegative: false,
											//allowDecimals: false,
											maxLength: 20,
											width: 100,
											fieldLabel: lang['nomer'],
											name: 'Polis_Num',
											tabIndex: TABINDEX_PEF + 12
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
											fieldLabel: lang['status_zayavleniya'],											
											width:200,
											allowBlank: false,
											disabled: false,
											id: 'DemandState_id',
											name: 'DemandState_id',
											hiddenName: 'hDemandState_id',
											xtype: 'swdemandstatecombo',
											tabIndex:4213,
											listeners: {
												'select': function(combo, record, index) {													
													Ext.getCmp('State_Comment').allowBlank = (this.getValue() != 5 && this.getValue() != 6);
												}
											}											
										},
										{
											allowBlank: true,											
											fieldLabel: lang['kommentariy_k_zayavleniyu'],
											maxLength: 500,											
											id: 'State_Comment',
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
							id: 'AttachmentDemand_id'
						}, [{
							mapping: 'AttachmentDemand_id',
							name: 'AttachmentDemand_id',
							type: 'int'
						}, {
							mapping: 'ADE_Person_id',
							name: 'ADE_Person_id',
							type: 'int'
						}, {
							mapping: 'ADE_Server_id',
							name: 'ADE_Server_id',
							type: 'int'
						}, {
							mapping: 'Lpu_id',
							name: 'Lpu_id',
							type: 'int'
						}, {
							mapping: 'Lpu_Name',
							name: 'Lpu_Name',
							type: 'string'
						}, {
							mapping: 'currentLpu_Name',
							name: 'currentLpu_Name',
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
		sw.Promed.swAttachmentDemandEditWindow.superclass.initComponent.apply(this, arguments);
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
	minHeight: 250,
	minWidth: 800,
	modal: true,
	onCancelAction: function() { this.hide(); },
	onHide: Ext.emptyFn,
	openEvnDiagPSEditWindow: function(action, type) {
	},
	plain: true,
	resizable: true,
	show: function() {
		sw.Promed.swAttachmentDemandEditWindow.superclass.show.apply(this, arguments);
		
		var person_id = 0;
		var server_id = 0;
		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
		var form = this.findById('demand_edit_form');
		var pers_form = this.findById('ADE_PersonInformationFrame');
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
					var attachmentdemand_id = arguments[0].formParams.AttachmentDemand_id;
				}
				base_form.load({
					failure: function() {
						sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_dannyih_zayavleniya'], function() { this.hide(); }.createDelegate(this) );
					}.createDelegate(this),
					params: {
						AttachmentDemand_id: attachmentdemand_id
					},
					success: function() {						
						person_id = form.findById('ADE_Person_id').getValue();
						server_id = form.findById('ADE_Server_id').getValue();
						pers_form.load({ Person_id: person_id, Server_id: server_id });
					}.createDelegate(this),
					url: '/?c=Demand&m=loadAttachmentDemandEditForm'
				});
				this.findById('StateFieldset').show();
				this.findById('DemandState_id').allowBlank = false;				
				this.findById('State_Comment').allowBlank = (arguments[0].formParams.DemandState_id != 5 && arguments[0].formParams.DemandState_id != 6);
			break;
		}
		
		loadMask.hide();
	},
	width: 800
});