/**
* swAmbulanceCardEditWindow - окно редактирования карты вызова.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Ambulance
* @access       public
* @copyright    Copyright (c) 2010 Swan Ltd.
* @author       Pshenicyn Ivan aka IVP (ipshon@rambler.ru)
* @version      05.01.2010
*/

sw.Promed.swAmbulanceCardEditWindow = Ext.extend(sw.Promed.BaseForm, {
	addAmbulanceMedicament: function() {
		/*getWnd('swAmbulanceDrugAddWindow').show({
			params: {
				action: 'add'
			}
		});*/
	},
	deleteAmbulanceMedicament: function() {
		var grid = Ext.getCmp('AmbulanceMedicamentViewGrid').ViewGridPanel;
		var row = grid.getSelectionModel().getSelected();
		sw.swMsg.show({
			title: lang['podtverjdenie_udaleniya'],
			msg: lang['vyi_deystvitelno_jelaete_udalit_etu_zapis'],
			buttons: Ext.Msg.YESNO,
			fn: function ( buttonId ) {
				if ( buttonId == 'yes' )
				{
					Ext.Ajax.request({
						callback: function(options, success, response) {
							if (success)
							{
								grid.getStore().remove(row);					
								if (grid.getStore().getCount() == 0)
								{
									LoadEmptyRow(grid);
									grid.getSelectionModel().selectFirstRow();
									grid.getView().focusRow(0);
								}
							}
							else
							{
								sw.swMsg.alert(lang['oshibka'], lang['pri_udalenii_medikamenta_voznikli_oshibki']);
							}
						},
						params: {
							CmpCallDrug_id: row.data.CmpCallDrug_id
						},
						url: '/?c=AmbulanceCard&m=deleteAmbulanceDrug'
					});					
				}
				if ( grid.getStore().getCount() > 0 )
				{
					grid.getSelectionModel().selectFirstRow();
					grid.getView().focusRow(0);
				}				
			}
		});
	},
	layout: 'fit',
    width: 800,
	height: 350,
    modal: true,
	resizable: false,
	draggable: false,
    autoHeight: true,
    closeAction :'hide',
    plain: true,
	id: 'AmbulanceCardEditWindow',
	onClose: function() {},
	returnFunc: function() {},
	personId: 0,
	maximizable: false,
	action: 'edit',
	title: WND_AMB_EDIT,
	listeners: {
		'hide': function() {this.onClose()}
	},
	disableNotEditableFields: function() {
		var disabledFields = Array(
			'NUMV',
			'NGOD',
			'NUMV',
			'PRTY',
			'SECT',
			'RJON',
			'CITY',
			'ULIC',
			'DOM',
			'KVAR',
			'PODZ',
			'ETAJ',
			'KODP',
			'TELF',
			'MEST',
			'POVD',
			'KTOV',
			'POVT',
			'PROF',
			'SMPT',
			'STAN',
			'DPRM',
			'TPRM',
			'WDAY',
			'LINE',
			'NUMB',
			'SMPB',
			'STBR',
			'STBB',
			'PRFB',
			'NCAR',
			'RCOD',
			'TABN',
			'DOKT',
			'TAB2',
			'TAB3',
			'TAB4',			
			'SMPP',
			'VR51',
			'D201',
			'DSP1',
			'DSP2',
			'DSPP',
			'DSP3',
			'KAKP',
			'TPER',
			'TGSP',
			'TISP',
			'DLIT',
			'PRDL',
			'MNAM',
			'KOD2',
			'PLNK',
			'TIZ1'
		);
		var form_panel = this.findById('ambulance_card_edit_form');
		var form = form_panel.getForm();
		for ( var i = 0; i < disabledFields.length; i++ )
		{
			var field = form.findField(disabledFields[i]);
			if ( field )
			{
				field.disable();
			}			
		}
	},
	disableEdit: function(disable) {
		var form = this.findById('ambulance_card_edit_form');
		if ( disable === false )
		{
			form.enable();
			this.buttons[0].enable();
		}
		else
		{
			var vals = form.getForm().getValues();
			for ( value in vals )
			{
				form.getForm().findField(value).disable();
				this.buttons[0].disable();
			}
		}
	},
	doSubmit: function() {
		if ( this.readOnly )
			return;
		var window = this;
		var form = this.findById('ambulance_card_edit_form');
		form.getForm().submit(
		{
			success: function(form, action) {
				window.hide();
				window.returnFunc();
			},
			failure: function (form, action)
			{
				Ext.Msg.alert("Ошибка", action.result.msg);
			}
		});
	},
	show: function() {
		this.personId = 0;
		this.readOnly = false;

		if ( arguments[0] )
		{
			if ( arguments[0].action )
				this.action = arguments[0].action;

			if ( arguments[0].callback )
				this.returnFunc = arguments[0].callback;

			if ( arguments[0].fields )
				this.findById('person_edit_form').getForm().setValues(arguments[0].fields);

			if ( arguments[0].onClose )
				this.onClose = arguments[0].onClose;

			if ( arguments[0].AmbulanceCard_id )
				this.ambulanceCardId = arguments[0].AmbulanceCard_id;

			if ( arguments[0].readOnly )
				this.readOnly = arguments[0].readOnly;
		}		

		sw.Promed.swAmbulanceCardEditWindow.superclass.show.apply(this, arguments);

		if (!this.readOnly)
			this.disableEdit(false);
		else
			this.disableEdit(true);

		if (this.action == 'add')
			this.setTitle(WND_AMB_ADD);
			
		if (this.action == 'edit')
			if (!this.readOnly)
			{
				this.setTitle(WND_AMB_EDIT);
			}
			else
			{
				this.setTitle(WND_AMB_VIEW);
			}
		
		this.findById('ambulance_card_tab_panel').setActiveTab(7);
		this.findById('ambulance_card_tab_panel').setActiveTab(6);
		this.findById('ambulance_card_tab_panel').setActiveTab(5);
		this.findById('ambulance_card_tab_panel').setActiveTab(4);
		this.findById('ambulance_card_tab_panel').setActiveTab(3);
		this.findById('ambulance_card_tab_panel').setActiveTab(2);		
		this.findById('ambulance_card_tab_panel').setActiveTab(1);
		this.findById('ambulance_card_tab_panel').setActiveTab(0);
		
		// дизаблим нередактируемые поля
		this.disableNotEditableFields();
		
		var form = this.findById('ambulance_card_edit_form');
		
		if ( this.action != 'add' )
		{
			var Mask = new Ext.LoadMask(Ext.get('ambulance_card_edit_form'), {msg:"Пожалуйста, подождите, идет загрузка данных формы..."});
			Mask.show();
		}
		var window = this;

		if ( this.action != 'add' )
		{
			var grid = Ext.getCmp('AmbulanceMedicamentViewGrid').ViewGridPanel;
			grid.getStore().removeAll();
			form.load({			
				params: {
					AmbulanceCard_id: this.ambulanceCardId
				},
				success: function(fm){
					Mask.hide();
					grid.getStore().load({
						params: {
							AmbulanceCard_id: form.getForm().findField('AmbulanceCard_id').getValue()
						}
					});
					form.getForm().findField('RJON').focus(true, 100);
				},
				failure: function() {
					Mask.hide();
					Ext.Msg.alert(lang['oshibka'], lang['ne_udalos_zagruzit_dannyie_s_servera'], function() {window.hide()});
				},
				url: C_AMB_GET
			});				
		} else {			
			form.getForm().findField('AmbulanceCard_id').setValue(0);
			form.getForm().findField('ACE_Person_id').setValue(arguments[0].formParams.Person_id);			
		}
		form.getForm().clearInvalid();
	},
	initComponent: function() {
		Ext.apply(this, {
			buttons: [
				{
					text: BTN_FRMSAVE,
					tabIndex: TABINDEX_AMBCARDEW + 141,
					iconCls: 'save16',
					id: 'ACEW_Save_Button',
					handler: this.doSubmit.createDelegate(this)
				},
				{
					text: '-'
				},
					HelpButton(this, -1),
				{
					text: BTN_FRMCANCEL,
					tabIndex: TABINDEX_AMBCARDEW + 142,
					iconCls: 'cancel16',
					id: 'ACEW_Cancel_Button',
					handler: this.hide.createDelegate(this, [])
				}							
			],
    		items: [
					new Ext.form.FormPanel({
                      	frame: true,
                      	//autoHeight: true,
						height: 350,
						labelAlign: 'right',
			        	id: 'ambulance_card_edit_form',
						labelWidth: 125,
						buttonAlign: 'left',
			        	bodyStyle:'padding:2px',
						url: C_AMB_SAVE,
						reader : new Ext.data.JsonReader({
            				success: function() {alert('All Right!')}
        				}, [
        	            		{name: 'AmbulanceCard_id'},
								{name: 'RJON'},
								{name: 'CITY'},
								{name: 'ULIC'},
								{name: 'DOM'},
								{name: 'TELF'},
								{name: 'MEST'},
								{name: 'PODZ'},
								{name: 'KVAR'},
								{name: 'KODP'},
								{name: 'ETAJ'},
								{name: 'COMM'},
								{name: 'POVD'},
								{name: 'FAM'},
								{name: 'IMYA'},
								{name: 'OTCH'},
								{name: 'KTOV'},
								{name: 'VOZR'},
								{name: 'POL'},
								{name: 'NUMV'},
								{name: 'NGOD'},
								{name: 'POVT'},
								{name: 'PRTY'},
								{name: 'PROF'},
								{name: 'SECT'},
								{name: 'SMPT'},
								{name: 'STAN'},
								{name: 'DPRM'},
								{name: 'TPRM'},
								{name: 'TPER'},
								{name: 'WDAY'},
								{name: 'LINE'},/*,
								{name: 'SECT'}*/
								{name: 'REZL'},
								{name: 'TRAV'},
								{name: 'RGSP'},
								{name: 'KUDA'},
								{name: 'DS1'},
								{name: 'DS2'},
								{name: 'ALK'},
								{name: 'MKB'},
								{name: 'NUMB'},
								{name: 'SMPB'},
								{name: 'STBR'},
								{name: 'STBB'},
								{name: 'PRFB'},
								{name: 'NCAR'},
								{name: 'RCOD'},
								{name: 'TABN'},
								{name: 'TAB2'},
								{name: 'TAB3'},
								{name: 'TAB4'},
								{name: 'SMPP'},
								{name: 'VR51'},
								{name: 'D201'},
								{name: 'DSP1'},
								{name: 'DSP2'},
								{name: 'DSPP'},
								{name: 'DSP3'},
								{name: 'KAKP'},
								{name: 'VYEZ'},
								{name: 'PRZD'},
								{name: 'TGSP'},
								{name: 'TSTA'},
								{name: 'TISP'},
								{name: 'TVZV'},
								{name: 'KILO'},
								{name: 'DLIT'},
								{name: 'PRDL'},
								{name: 'POLI'},
								{name: 'IZV1'},
								{name: 'TIZ1'},
								{name: 'INF1'},
								{name: 'INF2'},
								{name: 'INF3'},
								{name: 'INF4'},
								{name: 'INF5'},
								{name: 'INF6'},
								{name: 'MKOD'},
								{name: 'MNAM'},
								{name: 'MEDI'},
								{name: 'MKOL'},
								{name: 'DSHS'},
								{name: 'FERR'},
								{name: 'EXPO'}
						]),
						items: [{
							xtype: 'hidden',
							name: 'AmbulanceCard_id'
						}, {
							xtype: 'hidden',
							name: 'ACE_Person_id'
						},
						new Ext.TabPanel({
							plain:true,
							id: 'ambulance_card_tab_panel',
							activeTab: 0,
							layoutOnTabChange: true,
							defaults:{bodyStyle:'padding:2px'},
							items: [{
								bodyStyle: 'margin-top: 5px;',
								border: false,
								labelWidth: 50,
								height: 350,
								layout: 'form',
								listeners: {
									'activate': function(panel) {
										var form = this.findById('ambulance_card_edit_form');							
										form.getForm().findField('COMM').focus(true, 100);
									}.createDelegate(this)
								},								
								title: lang['1_o1'],
								items: [{
									border: false,
									layout: 'column',
									items: [{
										border: false,
										layout: 'form',
										items:[{
											allowBlank: true,
											fieldLabel: lang['rayon'],
											hiddenName: 'RJON',
											width: 180,
											tabIndex: TABINDEX_AMBCARDEW + 56,
											xtype: 'swklareastatcombo'										
										}, {
											xtype: 'textfield',
											//maskRe: /\d/,
											fieldLabel: lang['punkt'],
											minLength: 0,
											autoCreate: {tag: "input", type: "text", size: "22", maxLength: "22", autocomplete: "off"},
											width: 180,
											name: 'CITY',
											tabIndex: TABINDEX_AMBCARDEW + 57
										}, {
											xtype: 'textfield',
											fieldLabel: lang['ulitsa'],
											minLength: 0,
											autoCreate: {tag: "input", type: "text", size: "22", maxLength: "22", autocomplete: "off"},
											width: 180,
											name: 'ULIC',
											tabIndex: TABINDEX_AMBCARDEW + 58
										}, {
											xtype: 'textfield',
											//maskRe: /\d/,
											fieldLabel: lang['dom'],
											minLength: 0,
											autoCreate: {tag: "input", type: "text", size: "22", maxLength: "22", autocomplete: "off"},
											width: 180,
											name: 'DOM',
											tabIndex: TABINDEX_AMBCARDEW + 59
										}]
									}, {
										border: false,
										layout: 'form',
										items:[{
											xtype: 'textfield',
											//maskRe: /\d/,
											fieldLabel: lang['kv'],
											minLength: 0,
											autoCreate: {tag: "input", type: "text", size: "4", maxLength: "4", autocomplete: "off"},
											width: 50,
											name: 'KVAR',
											tabIndex: TABINDEX_AMBCARDEW + 60
										}, {
											xtype: 'textfield',
											//maskRe: /\d/,
											fieldLabel: lang['kod'],
											minLength: 0,
											autoCreate: {tag: "input", type: "text", size: "10", maxLength: "10", autocomplete: "off"},
											width: 180,
											name: 'KODP',
											tabIndex: TABINDEX_AMBCARDEW + 61
										}, {
											xtype: 'textfield',
											//maskRe: /\d/,
											fieldLabel: lang['tlf'],
											minLength: 0,
											autoCreate: {tag: "input", type: "text", size: "12", maxLength: "12", autocomplete: "off"},
											width: 180,
											name: 'TELF',
											tabIndex: TABINDEX_AMBCARDEW + 62
										}, {
											allowBlank: true,
											hiddenName: 'MEST',
											width: 180,
											tabIndex: TABINDEX_AMBCARDEW + 63,
											xtype: 'swcmpplacecombo'										
										}]
									}, {
										border: false,
										layout: 'form',
										items:[{
											xtype: 'textfield',
											maskRe: /\d/,
											fieldLabel: lang['pod'],
											minLength: 0,
											autoCreate: {tag: "input", type: "text", size: "2", maxLength: "2", autocomplete: "off"},
											width: 50,
											name: 'PODZ',
											tabIndex: TABINDEX_AMBCARDEW + 64
										}]
									}, {
										border: false,
										layout: 'form',
										items:[{
											xtype: 'textfield',
											maskRe: /\d/,
											fieldLabel: lang['et'],
											minLength: 0,
											autoCreate: {tag: "input", type: "text", size: "2", maxLength: "2", autocomplete: "off"},
											width: 50,
											name: 'ETAJ',
											tabIndex: TABINDEX_AMBCARDEW + 65
										}]
									}]
								}, {
									xtype: 'textfield',
									//maskRe: /\d/,
									fieldLabel: '!',
									minLength: 0,
									autoCreate: {tag: "input", type: "text", size: "51", maxLength: "51", autocomplete: "off"},
									width: 180,
									name: 'COMM',
									tabIndex: TABINDEX_AMBCARDEW + 66
								}, {
									allowBlank: true,
									hiddenName: 'POVD',
									width: 180,
									tabIndex: TABINDEX_AMBCARDEW + 67,
									xtype: 'swcmpreasoncombo'
								}, {
									border: false,
									layout: 'column',
									items: [{
										border: false,
										layout: 'form',
										items:[{
											xtype: 'textfieldpmw',
											fieldLabel: lang['fam'],
											autoCreate: {tag: "input", type: "text", size: "15", maxLength: "15", autocomplete: "off"},
											width: 180,
											name: 'FAM',
											tabIndex: TABINDEX_AMBCARDEW + 68
										}, {
											xtype: 'textfieldpmw',
											fieldLabel: lang['otchestvo'],
											autoCreate: {tag: "input", type: "text", size: "15", maxLength: "15", autocomplete: "off"},
											width: 180,
											name: 'OTCH',
											tabIndex: TABINDEX_AMBCARDEW + 69
										}]
									}, {
										border: false,
										layout: 'form',
										items:[{
											xtype: 'textfieldpmw',
											fieldLabel: lang['imya'],
											autoCreate: {tag: "input", type: "text", size: "14", maxLength: "14", autocomplete: "off"},
											width: 180,
											name: 'IMYA',
											tabIndex: TABINDEX_AMBCARDEW + 70
										}, {
											xtype: 'textfieldpmw',
											fieldLabel: lang['vyizval'],
											autoCreate: {tag: "input", type: "text", size: "20", maxLength: "20", autocomplete: "off"},
											width: 180,
											name: 'KTOV',
											tabIndex: TABINDEX_AMBCARDEW + 71
										}]
									}, {
										border: false,
										layout: 'form',
										items:[{
											xtype: 'textfield',
											maskRe: /\d/,
											fieldLabel: lang['vozr'],
											minLength: 0,
											autoCreate: {tag: "input", type: "text", size: "3", maxLength: "3", autocomplete: "off"},
											width: 50,
											name: 'VOZR',
											tabIndex: TABINDEX_AMBCARDEW + 72
										}]
									}, {
										border: false,
										layout: 'form',
										items:[{
											xtype: 'swpersonsexcombo',
											hiddenName: 'POL',
											enableKeyEvents: true,
											width: 95,
											editable: false,
											codeField: 'Sex_Code',
											tabIndex: TABINDEX_AMBCARDEW + 73
										}]
									}]
								}]
								/*items: [{
									border: false,
									layout: 'column',
									items: [{
										border: false,
										layout: 'form',
										items:[{
											allowBlank: true,
											fieldLabel: lang['rayon'],
											hiddenName: 'RJON',
											width: 180,
											tabIndex: TABINDEX_AMBCARDEW + 56,
											xtype: 'swklareastatcombo'										
										}, {
											xtype: 'textfield',
											//maskRe: /\d/,
											fieldLabel: lang['punkt'],
											minLength: 0,
											autoCreate: {tag: "input", type: "text", size: "22", maxLength: "22", autocomplete: "off"},
											width: 180,
											name: 'CITY',
											tabIndex: TABINDEX_AMBCARDEW + 57
										}, {
											xtype: 'textfield',
											fieldLabel: lang['ulitsa'],
											minLength: 0,
											autoCreate: {tag: "input", type: "text", size: "22", maxLength: "22", autocomplete: "off"},
											width: 180,
											name: 'ULIC',
											tabIndex: TABINDEX_AMBCARDEW + 58
										}, {
											xtype: 'textfield',
											//maskRe: /\d/,
											fieldLabel: lang['dom'],
											minLength: 0,
											autoCreate: {tag: "input", type: "text", size: "22", maxLength: "22", autocomplete: "off"},
											width: 180,
											name: 'DOM',
											tabIndex: TABINDEX_AMBCARDEW + 59
										}]
									}, {
										border: false,
										layout: 'form',
										items:[{
											border: false,
											layout: 'column',
											items: [{
												border: false,
												layout: 'form',
												items:[{
													xtype: 'textfield',
													//maskRe: /\d/,
													fieldLabel: lang['kv'],
													minLength: 0,
													autoCreate: {tag: "input", type: "text", size: "4", maxLength: "4", autocomplete: "off"},
													width: 50,
													name: 'KVAR',
													tabIndex: TABINDEX_AMBCARDEW + 60
												}, {
													xtype: 'textfield',
													//maskRe: /\d/,
													fieldLabel: lang['kod'],
													minLength: 0,
													autoCreate: {tag: "input", type: "text", size: "10", maxLength: "10", autocomplete: "off"},
													width: 180,
													name: 'KODP',
													tabIndex: TABINDEX_AMBCARDEW + 61
												}, {
													xtype: 'textfield',
													//maskRe: /\d/,
													fieldLabel: lang['tlf'],
													minLength: 0,
													autoCreate: {tag: "input", type: "text", size: "12", maxLength: "12", autocomplete: "off"},
													width: 180,
													name: 'TELF',
													tabIndex: TABINDEX_AMBCARDEW + 62
												}, {
													allowBlank: true,
													hiddenName: 'MEST',
													width: 180,
													tabIndex: TABINDEX_AMBCARDEW + 63,
													xtype: 'swcmpplacecombo'										
												}]
											}, {
												border: false,
												layout: 'form',
												items:[{
													xtype: 'textfield',
													maskRe: /\d/,
													fieldLabel: lang['pod'],
													minLength: 0,
													autoCreate: {tag: "input", type: "text", size: "2", maxLength: "2", autocomplete: "off"},
													width: 50,
													name: 'PODZ',
													tabIndex: TABINDEX_AMBCARDEW + 64
												}]
											}, {
												border: false,
												layout: 'form',
												items:[{
													xtype: 'textfield',
													maskRe: /\d/,
													fieldLabel: lang['et'],
													minLength: 0,
													autoCreate: {tag: "input", type: "text", size: "2", maxLength: "2", autocomplete: "off"},
													width: 50,
													name: 'ETAJ',
													tabIndex: TABINDEX_AMBCARDEW + 65
												}]
											}]
										}]
									}]
								}, {
									xtype: 'textfield',
									//maskRe: /\d/,
									fieldLabel: '!',
									minLength: 0,
									autoCreate: {tag: "input", type: "text", size: "51", maxLength: "51", autocomplete: "off"},
									width: 180,
									name: 'COMM',
									tabIndex: TABINDEX_AMBCARDEW + 66
								}, {
									allowBlank: true,
									hiddenName: 'POVD',
									width: 180,
									tabIndex: TABINDEX_AMBCARDEW + 67,
									xtype: 'swcmpreasoncombo'
								}, {
									border: false,
									layout: 'column',
									items: [{
										border: false,
										layout: 'form',
										items:[{
											xtype: 'textfieldpmw',
											fieldLabel: lang['fam'],
											autoCreate: {tag: "input", type: "text", size: "15", maxLength: "15", autocomplete: "off"},
											width: 180,
											name: 'FAM',
											tabIndex: TABINDEX_AMBCARDEW + 68
										}, {
											xtype: 'textfieldpmw',
											fieldLabel: lang['otchestvo'],
											autoCreate: {tag: "input", type: "text", size: "15", maxLength: "15", autocomplete: "off"},
											width: 180,
											name: 'OTCH',
											tabIndex: TABINDEX_AMBCARDEW + 69
										}]
									}, {
										border: false,
										layout: 'form',
										items:[{
											xtype: 'textfieldpmw',
											fieldLabel: lang['imya'],
											autoCreate: {tag: "input", type: "text", size: "14", maxLength: "14", autocomplete: "off"},
											width: 180,
											name: 'IMYA',
											tabIndex: TABINDEX_AMBCARDEW + 70
										}, {
											xtype: 'textfieldpmw',
											fieldLabel: lang['vyizval'],
											autoCreate: {tag: "input", type: "text", size: "20", maxLength: "20", autocomplete: "off"},
											width: 180,
											name: 'KTOV',
											tabIndex: TABINDEX_AMBCARDEW + 71
										}]
									}, {
										border: false,
										layout: 'form',
										items:[{
											xtype: 'textfield',
											maskRe: /\d/,
											fieldLabel: lang['vozr'],
											minLength: 0,
											autoCreate: {tag: "input", type: "text", size: "3", maxLength: "3", autocomplete: "off"},
											width: 50,
											name: 'VOZR',
											tabIndex: TABINDEX_AMBCARDEW + 72
										}]
									}, {
										border: false,
										layout: 'form',
										items:[{
											xtype: 'swpersonsexcombo',
											hiddenName: 'POL',
											enableKeyEvents: true,
											width: 95,
											editable: false,
											codeField: 'Sex_Code',
											tabIndex: TABINDEX_AMBCARDEW + 73
										}]
									}]
								}]*/
							}, {
								bodyStyle: 'margin-top: 5px;',
								border: false,
								height: 280,
								labelWidth: 65,
								layout: 'form',
								listeners: {
									'activate': function(panel) {
										//var form = this.findById('ambulance_card_edit_form');							
										//form.getForm().findField('NUMV').focus(true, 100);
										Ext.getCmp('ACEW_Save_Button').focus();
									}.createDelegate(this)
								},								
								title: lang['2_o2'],
								items: [{
									border: false,
									layout: 'column',
									items: [{
										border: false,
										layout: 'form',
										items:[{
											xtype: 'textfield',
											maskRe: /\d/,
											fieldLabel: lang['nomer'],
											minLength: 0,
											autoCreate: {tag: "input", type: "text", size: "4", maxLength: "4", autocomplete: "off"},
											width: 50,
											name: 'NUMV',
											tabIndex: TABINDEX_AMBCARDEW + 74
										}]
									}, {
										border: false,
										layout: 'form',
										items:[{
											xtype: 'textfield',
											maskRe: /\d/,
											fieldLabel: lang['skv_nom'],
											minLength: 0,
											autoCreate: {tag: "input", type: "text", size: "7", maxLength: "7", autocomplete: "off"},
											width: 50,
											name: 'NGOD',
											tabIndex: TABINDEX_AMBCARDEW + 75
										}]
									}]
								}, {
									/*xtype: 'textfield',
									maskRe: /\d/,
									fieldLabel: lang['tip_vyiz'],
									minLength: 0,
									autoCreate: {tag: "input", type: "text", size: "1", maxLength: "1", autocomplete: "off"},
									width: 50,
									name: 'POVT',
									tabIndex: TABINDEX_AMBCARDEW + 76*/
									allowBlank: true,
									hiddenName: 'POVT',
									width: 180,
									tabIndex: TABINDEX_AMBCARDEW + 76,
									xtype: 'swcmpcalltypecombo'
								}, {
									border: false,
									layout: 'column',
									items: [{
										border: false,
										layout: 'form',
										items:[{
											xtype: 'textfield',
											maskRe: /\d/,
											fieldLabel: lang['sroch'],
											minLength: 0,
											autoCreate: {tag: "input", type: "text", size: "2", maxLength: "2", autocomplete: "off"},
											width: 50,
											name: 'PRTY',
											tabIndex: TABINDEX_AMBCARDEW + 77
										}]
									}, {
										border: false,
										layout: 'form',
										items:[{
											/*xtype: 'textfield',
											maskRe: /\d/,
											fieldLabel: lang['prf'],
											minLength: 0,
											autoCreate: {tag: "input", type: "text", size: "2", maxLength: "2", autocomplete: "off"},
											width: 50,
											name: 'PROF',
											tabIndex: TABINDEX_AMBCARDEW + 78*/
											allowBlank: true,
											hiddenName: 'PROF',
											width: 180,
											tabIndex: TABINDEX_AMBCARDEW + 78,
											xtype: 'swcmpprofilecombo'
										}]
									}]
								}, {
									border: false,
									layout: 'column',
									items: [{
										border: false,
										layout: 'form',
										items:[{
											xtype: 'textfield',
											maskRe: /\d/,
											fieldLabel: lang['sekt'],
											minLength: 0,
											autoCreate: {tag: "input", type: "text", size: "4", maxLength: "4", autocomplete: "off"},
											width: 50,
											name: 'SECT',
											tabIndex: TABINDEX_AMBCARDEW + 79
										}]
									}, {
										border: false,
										layout: 'form',
										items:[{
											xtype: 'textfield',
											maskRe: /\d/,
											fieldLabel: lang['smp'],
											minLength: 0,
											autoCreate: {tag: "input", type: "text", size: "4", maxLength: "4", autocomplete: "off"},
											width: 50,
											name: 'SMPT',
											tabIndex: TABINDEX_AMBCARDEW + 80
										}]
									}, {
										border: false,
										layout: 'form',
										items:[{
											xtype: 'textfield',
											maskRe: /\d/,
											fieldLabel: lang['p_c'],
											minLength: 0,
											autoCreate: {tag: "input", type: "text", size: "2", maxLength: "2", autocomplete: "off"},
											width: 50,
											name: 'STAN',
											tabIndex: TABINDEX_AMBCARDEW + 81
										}]
									}]
								}, {
									border: false,
									layout: 'column',
									items: [{
										border: false,
										layout: 'form',
										items:[{
											xtype: 'swdatefield',
											fieldLabel: lang['data'],
											name: 'DPRM',
											minLength: 0,
											width: 100,
											plugins: [
												new Ext.ux.InputTextMask('99.99.9999', false)
											],
											tabIndex: TABINDEX_AMBCARDEW + 82
										}, {
											xtype: 'textfield',
											//maskRe: /\d:/,
											fieldLabel: 'Принят',
											minLength: 0,
											plugins: [
												new Ext.ux.InputTextMask('99:99', false)
											],
											//autoCreate: {tag: "input", type: "text", size: "5", maxLength: "5", autocomplete: "off"},
											width: 50,
											name: 'TPRM',
											tabIndex: TABINDEX_AMBCARDEW + 83
										}, {
											xtype: 'textfield',
											//maskRe: /\d:/,
											fieldLabel: lang['peredan'],
											minLength: 0,
											plugins: [
												new Ext.ux.InputTextMask('99:99', false)
											],
											//autoCreate: {tag: "input", type: "text", size: "5", maxLength: "5", autocomplete: "off"},
											width: 50,
											name: 'TPER',
											tabIndex: TABINDEX_AMBCARDEW + 84
										}]
									}, {
										border: false,
										layout: 'form',
										items:[{
											xtype: 'textfield',
											maskRe: /\d/,
											fieldLabel: lang['den'],
											minLength: 0,
											autoCreate: {tag: "input", type: "text", size: "1", maxLength: "1", autocomplete: "off"},
											width: 50,
											name: 'WDAY',
											tabIndex: TABINDEX_AMBCARDEW + 85
										}, {
											xtype: 'textfield',
											maskRe: /\d/,
											fieldLabel: lang['pult'],
											minLength: 0,
											autoCreate: {tag: "input", type: "text", size: "2", maxLength: "2", autocomplete: "off"},
											width: 50,
											name: 'LINE',
											tabIndex: TABINDEX_AMBCARDEW + 86
										}/*, {
											xtype: 'textfield',
											maskRe: /\d/,
											fieldLabel: lang['isp'],
											minLength: 0,
											autoCreate: {tag: "input", type: "text", size: "4", maxLength: "4", autocomplete: "off"},
											width: 50,
											name: 'SECT',
											tabIndex: TABINDEX_AMBCARDEW + 87
										}*/]
									}]
								}]
							}, {
								bodyStyle: 'margin-top: 5px;',
								border: false,
								height: 280,								
								labelWidth: 65,
								layout: 'form',
								listeners: {
									'activate': function(panel) {
										var form = this.findById('ambulance_card_edit_form');
										if ( form.getForm().findField('KUDA').getStore().getCount() == 0 )
										{
											form.getForm().findField('KUDA').getStore().load({
												success: function() {
													form.getForm().findField('KUDA').setValue(form.getForm().findField('KUDA').getValue());
												}
											});
										}
										form.getForm().findField('REZL').focus(true, 100);
									}.createDelegate(this)
								},								
								title: lang['3_o3'],
								items: [{
									border: false,
									layout: 'column',
									items: [{
										border: false,
										layout: 'form',
										items:[{
											/*xtype: 'textfield',
											maskRe: /\d/,
											fieldLabel: lang['rez-t'],
											minLength: 0,
											autoCreate: {tag: "input", type: "text", size: "2", maxLength: "2", autocomplete: "off"},
											width: 50,
											name: 'REZL',
											tabIndex: TABINDEX_AMBCARDEW + 88*/
											allowBlank: true,
											hiddenName: 'REZL',
											width: 180,
											tabIndex: TABINDEX_AMBCARDEW + 88,
											xtype: 'swcmpresultcombo'
										}]
									}, {
										border: false,
										layout: 'form',
										items:[{
											/*xtype: 'textfield',
											maskRe: /\d/,
											fieldLabel: lang['vid'],
											minLength: 0,
											autoCreate: {tag: "input", type: "text", size: "2", maxLength: "2", autocomplete: "off"},
											width: 50,
											name: 'TRAV',
											tabIndex: TABINDEX_AMBCARDEW + 89*/
											allowBlank: true,
											hiddenName: 'TRAV',
											width: 180,
											tabIndex: TABINDEX_AMBCARDEW + 89,
											xtype: 'swcmptraumacombo'
										}]
									}]
								}, {
									border: false,
									layout: 'column',
									items: [{
										border: false,
										layout: 'form',
										items:[{
											/*xtype: 'textfield',
											maskRe: /\d/,
											fieldLabel: lang['rayon'],
											minLength: 0,
											autoCreate: {tag: "input", type: "text", size: "3", maxLength: "3", autocomplete: "off"},
											width: 50,
											name: 'RGSP',
											tabIndex: TABINDEX_AMBCARDEW + 90*/
											allowBlank: true,
											fieldLabel: lang['rayon'],
											hiddenName: 'RGSP',
											width: 180,
											tabIndex: TABINDEX_AMBCARDEW + 90,
											xtype: 'swklareastatcombo'
										}]
									}, {
										border: false,
										layout: 'form',
										items:[{
											/*xtype: 'textfield',
											//maskRe: /\d/,
											fieldLabel: lang['kuda'],
											minLength: 0,
											autoCreate: {tag: "input", type: "text", size: "36", maxLength: "36", autocomplete: "off"},
											width: 180,
											name: 'KUDA',
											tabIndex: TABINDEX_AMBCARDEW + 91*/
											allowBlank: true,
											autoLoad: true,
											fieldLabel: lang['kuda'],
											hiddenName: 'KUDA',
											width: 180,
											tabIndex: TABINDEX_AMBCARDEW + 91,
											xtype: 'swlpucombo'
										}]
									}]
								}, {
									border: false,
									layout: 'column',
									items: [{
										border: false,
										layout: 'form',
										items:[/*{
											xtype: 'textfield',
											maskRe: /\d/,
											fieldLabel: 'Ds',
											minLength: 0,
											autoCreate: {tag: "input", type: "text", size: "5", maxLength: "5", autocomplete: "off"},
											width: 50,
											name: 'DS1',
											tabIndex: TABINDEX_AMBCARDEW + 92
										}*/{
											fieldLabel: 'DS',
											hiddenName: 'DS1',
											tabIndex: TABINDEX_AMBCARDEW + 92,
											width: 180,
											xtype: 'swcmpdiagcombo'
										}, /*{
											xtype: 'textfield',
											maskRe: /\d/,
											fieldLabel: 'Ds',
											minLength: 0,
											autoCreate: {tag: "input", type: "text", size: "5", maxLength: "5", autocomplete: "off"},
											width: 50,
											name: 'DS2',
											tabIndex: TABINDEX_AMBCARDEW + 93
										}*/{
											fieldLabel: 'DS',
											hiddenName: 'DS2',
											tabIndex: TABINDEX_AMBCARDEW + 93,
											width: 180,
											xtype: 'swcmpdiagcombo'
										}]
									}, {
										border: false,
										layout: 'form',
										items:[{
											/*xtype: 'textfield',
											maskRe: /\d/,
											fieldLabel: lang['alk'],
											minLength: 0,
											autoCreate: {tag: "input", type: "text", size: "1", maxLength: "1", autocomplete: "off"},
											width: 50,
											name: 'ALK',
											tabIndex: TABINDEX_AMBCARDEW + 94*/
											allowBlank: true,
											fieldLabel: lang['alk'],
											hiddenName: 'ALK',
											width: 180,
											tabIndex: TABINDEX_AMBCARDEW + 94,
											xtype: 'swyesnocombo'
										}, /*{
											xtype: 'textfield',
											//maskRe: /\d/,
											fieldLabel: lang['mkb'],
											minLength: 0,
											autoCreate: {tag: "input", type: "text", size: "5", maxLength: "5", autocomplete: "off"},
											width: 50,
											name: 'MKB',
											tabIndex: TABINDEX_AMBCARDEW + 95
										}*/{
											fieldLabel: lang['mkb'],
											hiddenName: 'MKB',
											tabIndex: TABINDEX_AMBCARDEW + 95,
											width: 180,
											xtype: 'swdiagcombo'
										}]
									}]
								}]
							}, {
								bodyStyle: 'margin-top: 5px;',
								border: false,
								height: 280,								
								labelWidth: 65,
								layout: 'form',
								listeners: {
									'activate': function(panel) {
										//var form = this.findById('ambulance_card_edit_form');							
										//form.getForm().findField('NUMB').focus(true, 100);
										Ext.getCmp('ACEW_Save_Button').focus();
									}.createDelegate(this)
								},								
								title: lang['4_o4'],
								items: [{
									border: false,
									layout: 'column',
									items: [{
										border: false,
										layout: 'form',
										items:[{
											xtype: 'textfield',
											maskRe: /\d/,
											fieldLabel: lang['brigada'],
											minLength: 0,
											autoCreate: {tag: "input", type: "text", size: "4", maxLength: "4", autocomplete: "off"},
											width: 50,
											name: 'NUMB',
											tabIndex: TABINDEX_AMBCARDEW + 96
										}]
									}, {
										border: false,
										layout: 'form',
										items:[{
											xtype: 'textfield',
											maskRe: /\d/,
											fieldLabel: lang['ssmp'],
											minLength: 0,
											autoCreate: {tag: "input", type: "text", size: "4", maxLength: "4", autocomplete: "off"},
											width: 50,
											name: 'SMPB',
											tabIndex: TABINDEX_AMBCARDEW + 97
										}]
									}]
								}, {
									border: false,
									layout: 'column',
									items: [{
										border: false,
										layout: 'form',
										items:[{
											xtype: 'textfield',
											maskRe: /\d/,
											fieldLabel: lang['p_s'],
											minLength: 0,
											autoCreate: {tag: "input", type: "text", size: "2", maxLength: "2", autocomplete: "off"},
											width: 50,
											name: 'STBR',
											tabIndex: TABINDEX_AMBCARDEW + 98
										}]
									}, {
										border: false,
										layout: 'form',
										items:[{
											xtype: 'textfield',
											maskRe: /\d/,
											fieldLabel: '/',
											minLength: 0,
											autoCreate: {tag: "input", type: "text", size: "2", maxLength: "2", autocomplete: "off"},
											width: 50,
											name: 'STBB',
											tabIndex: TABINDEX_AMBCARDEW + 99
										}]
									}, {
										border: false,
										layout: 'form',
										items:[{
											/*xtype: 'textfield',
											//maskRe: /\d/,
											fieldLabel: lang['prf'],
											minLength: 0,
											autoCreate: {tag: "input", type: "text", size: "2", maxLength: "2", autocomplete: "off"},
											width: 50,
											name: 'PRFB',
											tabIndex: TABINDEX_AMBCARDEW + 100*/
											allowBlank: true,
											fieldLabel: lang['prf'],
											hiddenName: 'PRFB',
											width: 180,
											tabIndex: TABINDEX_AMBCARDEW + 100,
											xtype: 'swcmpprofilecombo'
										}]
									}]
								}, {
									border: false,
									layout: 'column',
									items: [{
										border: false,
										layout: 'form',
										items:[{
											xtype: 'textfield',
											maskRe: /\d/,
											fieldLabel: lang['mashina'],
											minLength: 0,
											autoCreate: {tag: "input", type: "text", size: "4", maxLength: "4", autocomplete: "off"},
											width: 50,
											name: 'NCAR',
											tabIndex: TABINDEX_AMBCARDEW + 101
										}]
									}, {
										border: false,
										layout: 'form',
										items:[{
											xtype: 'textfield',
											//maskRe: /\d/,
											fieldLabel: lang['ratsiya'],
											minLength: 0,
											autoCreate: {tag: "input", type: "text", size: "4", maxLength: "4", autocomplete: "off"},
											width: 50,
											name: 'RCOD',
											tabIndex: TABINDEX_AMBCARDEW + 102
										}]
									}]
								}, {
									xtype: 'textfield',
									maskRe: /\d/,
									fieldLabel: lang['sb'],
									minLength: 0,
									autoCreate: {tag: "input", type: "text", size: "4", maxLength: "4", autocomplete: "off"},
									width: 50,
									name: 'TABN',
									tabIndex: TABINDEX_AMBCARDEW + 103
								}, {
									border: false,
									layout: 'column',
									items: [{
										border: false,
										layout: 'form',
										items:[{
											xtype: 'textfield',
											maskRe: /\d/,
											fieldLabel: lang['p1'],
											minLength: 0,
											autoCreate: {tag: "input", type: "text", size: "4", maxLength: "4", autocomplete: "off"},
											width: 50,
											name: 'TAB2',
											tabIndex: TABINDEX_AMBCARDEW + 104
										}]
									}, {
										border: false,
										layout: 'form',
										items:[{
											xtype: 'textfield',
											maskRe: /\d/,
											fieldLabel: lang['p2'],
											minLength: 0,
											autoCreate: {tag: "input", type: "text", size: "4", maxLength: "4", autocomplete: "off"},
											width: 50,
											name: 'TAB3',
											tabIndex: TABINDEX_AMBCARDEW + 105
										}]
									}, {
										border: false,
										layout: 'form',
										items:[{
											xtype: 'textfield',
											maskRe: /\d/,
											fieldLabel: lang['v'],
											minLength: 0,
											autoCreate: {tag: "input", type: "text", size: "4", maxLength: "4", autocomplete: "off"},
											width: 50,
											name: 'TAB4',
											tabIndex: TABINDEX_AMBCARDEW + 106
										}]
									}]
								}]
							}, {
								bodyStyle: 'margin-top: 5px;',
								border: false,
								height: 280,								
								labelWidth: 65,
								layout: 'form',
								listeners: {
									'activate': function(panel) {
										var form = this.findById('ambulance_card_edit_form');							
										form.getForm().findField('VYEZ').focus(true, 100);
									}.createDelegate(this)
								},								
								title: lang['5_o5-1'],
								items: [{
									border: false,
									layout: 'column',
									items: [{
										border: false,
										layout: 'form',
										items:[{
											xtype: 'textfield',
											maskRe: /\d/,
											fieldLabel: lang['ssmp'],
											minLength: 0,
											autoCreate: {tag: "input", type: "text", size: "4", maxLength: "4", autocomplete: "off"},
											width: 50,
											name: 'SMPP',
											tabIndex: TABINDEX_AMBCARDEW + 107
										}]
									}, {
										border: false,
										layout: 'form',
										items:[{
											xtype: 'textfield',
											maskRe: /\d/,
											fieldLabel: lang['st_vrach'],
											minLength: 0,
											autoCreate: {tag: "input", type: "text", size: "4", maxLength: "4", autocomplete: "off"},
											width: 50,
											name: 'VR51',
											tabIndex: TABINDEX_AMBCARDEW + 108
										}]
									}, {
										border: false,
										layout: 'form',
										items:[{
											xtype: 'textfield',
											maskRe: /\d/,
											fieldLabel: lang['st_disp'],
											minLength: 0,
											autoCreate: {tag: "input", type: "text", size: "4", maxLength: "4", autocomplete: "off"},
											width: 50,
											name: 'D201',
											tabIndex: TABINDEX_AMBCARDEW + 109
										}]
									}]
								}, {
									border: false,
									layout: 'column',
									items: [{
										border: false,
										layout: 'form',
										items:[{
											xtype: 'textfield',
											maskRe: /\d/,
											fieldLabel: lang['prinyal'],
											minLength: 0,
											autoCreate: {tag: "input", type: "text", size: "4", maxLength: "4", autocomplete: "off"},
											width: 50,
											name: 'DSP1',
											tabIndex: TABINDEX_AMBCARDEW + 110
										}]
									}, {
										border: false,
										layout: 'form',
										items:[{
											xtype: 'textfield',
											maskRe: /\d/,
											fieldLabel: lang['naznachil'],
											minLength: 0,
											autoCreate: {tag: "input", type: "text", size: "4", maxLength: "4", autocomplete: "off"},
											width: 50,
											name: 'DSP2',
											tabIndex: TABINDEX_AMBCARDEW + 111
										}]
									}, {
										border: false,
										layout: 'form',
										items:[{
											xtype: 'textfield',
											maskRe: /\d/,
											fieldLabel: lang['peredal'],
											minLength: 0,
											autoCreate: {tag: "input", type: "text", size: "4", maxLength: "4", autocomplete: "off"},
											width: 50,
											name: 'DSPP',
											tabIndex: TABINDEX_AMBCARDEW + 112
										}]
									}]
								}, {
									border: false,
									layout: 'column',
									items: [{
										border: false,
										layout: 'form',
										items:[{
											xtype: 'textfield',
											maskRe: /\d/,
											fieldLabel: lang['zakryil'],
											minLength: 0,
											autoCreate: {tag: "input", type: "text", size: "4", maxLength: "4", autocomplete: "off"},
											width: 50,
											name: 'DSP3',
											tabIndex: TABINDEX_AMBCARDEW + 113
										}]
									}, {
										border: false,
										layout: 'form',
										items:[{
											xtype: 'textfield',
											maskRe: /\d/,
											fieldLabel: lang['poluchen'],
											minLength: 0,
											autoCreate: {tag: "input", type: "text", size: "1", maxLength: "1", autocomplete: "off"},
											width: 50,
											name: 'KAKP',
											tabIndex: TABINDEX_AMBCARDEW + 114
										}]
									}/*, {
										border: false,
										layout: 'form',
										items:[{
											xtype: 'textfield',
											//maskRe: /\d/,
											fieldLabel: lang['peredan'],
											minLength: 0,
											plugins: [
												new Ext.ux.InputTextMask('99:99', false)
											],
											//autoCreate: {tag: "input", type: "text", size: "5", maxLength: "5", autocomplete: "off"},
											width: 50,
											name: 'TPER',
											tabIndex: TABINDEX_AMBCARDEW + 115
										}]
									}*/]
								}, {
									border: false,
									layout: 'column',
									items: [{
										border: false,
										layout: 'form',
										items:[{
											xtype: 'textfield',
											//maskRe: /\d/,
											fieldLabel: lang['vyiezd'],
											minLength: 0,
											plugins: [
												new Ext.ux.InputTextMask('99:99', false)
											],
											//autoCreate: {tag: "input", type: "text", size: "5", maxLength: "5", autocomplete: "off"},
											width: 50,
											name: 'VYEZ',
											tabIndex: TABINDEX_AMBCARDEW + 116
										}]
									}, {
										border: false,
										layout: 'form',
										items:[{
											xtype: 'textfield',
											//maskRe: /\d/,
											fieldLabel: lang['pribyil'],
											minLength: 0,
											plugins: [
												new Ext.ux.InputTextMask('99:99', false)
											],
											//autoCreate: {tag: "input", type: "text", size: "5", maxLength: "5", autocomplete: "off"},
											width: 50,
											name: 'PRZD',
											tabIndex: TABINDEX_AMBCARDEW + 117
										}]
									}, {
										border: false,
										layout: 'form',
										items:[{
											xtype: 'textfield',
											//maskRe: /\d/,
											fieldLabel: lang['gospit'],
											plugins: [
												new Ext.ux.InputTextMask('99:99', false)
											],
											minLength: 0,
											//autoCreate: {tag: "input", type: "text", size: "5", maxLength: "5", autocomplete: "off"},
											width: 50,
											name: 'TGSP',
											tabIndex: TABINDEX_AMBCARDEW + 118
										}]
									}]
								}, {
									border: false,
									layout: 'column',
									items: [{
										border: false,
										layout: 'form',
										items:[{
											xtype: 'textfield',
											//maskRe: /\d/,
											fieldLabel: lang['v_stats'],
											plugins: [
												new Ext.ux.InputTextMask('99:99', false)
											],
											minLength: 0,
											//autoCreate: {tag: "input", type: "text", size: "5", maxLength: "5", autocomplete: "off"},
											width: 50,
											name: 'TSTA',
											tabIndex: TABINDEX_AMBCARDEW + 119
										}]
									}, {
										border: false,
										layout: 'form',
										items:[{
											xtype: 'textfield',
											//maskRe: /\d/,
											fieldLabel: lang['ispoln'],
											plugins: [
												new Ext.ux.InputTextMask('99:99', false)
											],
											minLength: 0,
											//autoCreate: {tag: "input", type: "text", size: "5", maxLength: "5", autocomplete: "off"},
											width: 50,
											name: 'TISP',
											tabIndex: TABINDEX_AMBCARDEW + 120
										}]
									}, {
										border: false,
										layout: 'form',
										items:[{
											xtype: 'textfield',
											//maskRe: /\d/,
											fieldLabel: lang['vozvr'],
											plugins: [
												new Ext.ux.InputTextMask('99:99', false)
											],
											minLength: 0,
											//autoCreate: {tag: "input", type: "text", size: "5", maxLength: "5", autocomplete: "off"},
											width: 50,
											name: 'TVZV',
											tabIndex: TABINDEX_AMBCARDEW + 121
										}]
									}]
								}, {
									border: false,
									layout: 'column',
									items: [{
										border: false,
										layout: 'form',
										items:[{
											allowDecimals: true,
											allowNegative: false,
											xtype: 'numberfield',
											fieldLabel: lang['kilometr'],
											maxValue: 9999.99,
											width: 50,
											name: 'KILO',
											tabIndex: TABINDEX_AMBCARDEW + 122
										}]
									}, {
										border: false,
										layout: 'form',
										items:[{
											xtype: 'textfield',
											maskRe: /\d/,
											fieldLabel: lang['dlit_03'],
											minLength: 0,
											autoCreate: {tag: "input", type: "text", size: "3", maxLength: "3", autocomplete: "off"},
											width: 50,
											name: 'DLIT',
											tabIndex: TABINDEX_AMBCARDEW + 123
										}]
									}, {
										border: false,
										layout: 'form',
										items:[{
											xtype: 'textfield',
											//maskRe: /\d/,
											fieldLabel: lang['predloj'],
											minLength: 0,
											autoCreate: {tag: "input", type: "text", size: "3", maxLength: "3", autocomplete: "off"},
											width: 50,
											name: 'PRDL',
											tabIndex: TABINDEX_AMBCARDEW + 124
										}]
									}]
								}]
							}, {
								bodyStyle: 'margin-top: 5px;',
								border: false,
								height: 280,								
								labelWidth: 130,
								layout: 'form',
								listeners: {
									'activate': function(panel) {
										var form = this.findById('ambulance_card_edit_form');							
										form.getForm().findField('POLI').focus(true, 100);
									}.createDelegate(this)
								},								
								title: lang['6_o5-2'],
								items: [{
									border: false,
									layout: 'column',
									items: [{
										border: false,
										layout: 'form',
										items:[{
											/*xtype: 'textfield',
											maskRe: /\d/,
											fieldLabel: lang['aktiv'],
											minLength: 0,
											autoCreate: {tag: "input", type: "text", size: "1", maxLength: "1", autocomplete: "off"},
											width: 50,
											name: 'POLI',
											tabIndex: TABINDEX_AMBCARDEW + 125*/
											allowBlank: true,
											fieldLabel: lang['aktiv'],
											hiddenName: 'POLI',
											width: 180,
											tabIndex: TABINDEX_AMBCARDEW + 125,
											xtype: 'swyesnocombo'
										}]
									}, {
										border: false,
										layout: 'form',
										items:[{
											xtype: 'textfield',
											//maskRe: /\d/,
											fieldLabel: lang['s1'],
											minLength: 0,
											autoCreate: {tag: "input", type: "text", size: "20", maxLength: "20", autocomplete: "off"},
											width: 180,
											name: 'IZV1',
											tabIndex: TABINDEX_AMBCARDEW + 126
										}]
									}, {
										border: false,
										layout: 'form',
										items:[{
											xtype: 'textfield',
											//maskRe: /\d/,
											fieldLabel: lang['vremya1'],
											plugins: [
												new Ext.ux.InputTextMask('99:99', false)
											],
											minLength: 0,
											//autoCreate: {tag: "input", type: "text", size: "4", maxLength: "4", autocomplete: "off"},
											width: 50,
											name: 'TIZ1',
											tabIndex: TABINDEX_AMBCARDEW + 127
										}]
									}]
								}, {
									border: false,
									layout: 'column',
									items: [{
										border: false,
										layout: 'form',
										items:[{
											xtype: 'textfield',
											//maskRe: /\d/,
											fieldLabel: lang['prichina_zaderjki'],
											minLength: 0,
											autoCreate: {tag: "input", type: "text", size: "30", maxLength: "30", autocomplete: "off"},
											width: 180,
											name: 'INF1',
											tabIndex: TABINDEX_AMBCARDEW + 128
										}]
									}, {
										border: false,
										layout: 'form',
										items:[{
											xtype: 'textfield',
											//maskRe: /\d/,
											fieldLabel: lang['diagnostika'],
											minLength: 0,
											autoCreate: {tag: "input", type: "text", size: "30", maxLength: "30", autocomplete: "off"},
											width: 180,
											name: 'INF2',
											tabIndex: TABINDEX_AMBCARDEW + 129
										}]
									}, {
										border: false,
										layout: 'form',
										items:[{
											xtype: 'textfield',
											//maskRe: /\d/,
											fieldLabel: lang['prichina_povtora'],
											minLength: 0,
											autoCreate: {tag: "input", type: "text", size: "30", maxLength: "30", autocomplete: "off"},
											width: 180,
											name: 'INF3',
											tabIndex: TABINDEX_AMBCARDEW + 130
										}]
									}]
								}, {
									border: false,
									layout: 'column',
									items: [{
										border: false,
										layout: 'form',
										items:[{
											xtype: 'textfield',
											//maskRe: /\d/,
											fieldLabel: lang['taktika'],
											minLength: 0,
											autoCreate: {tag: "input", type: "text", size: "20", maxLength: "20", autocomplete: "off"},
											width: 180,
											name: 'INF4',
											tabIndex: TABINDEX_AMBCARDEW + 131
										}]
									}, {
										border: false,
										layout: 'form',
										items:[{
											xtype: 'textfield',
											//maskRe: /\d/,
											fieldLabel: lang['oformlenie_kartyi'],
											minLength: 0,
											autoCreate: {tag: "input", type: "text", size: "20", maxLength: "20", autocomplete: "off"},
											width: 180,
											name: 'INF5',
											tabIndex: TABINDEX_AMBCARDEW + 132
										}]
									}, {
										border: false,
										layout: 'form',
										items:[{
											xtype: 'textfield',
											//maskRe: /\d/,
											fieldLabel: lang['otsenka'],
											minLength: 0,
											autoCreate: {tag: "input", type: "text", size: "20", maxLength: "20", autocomplete: "off"},
											width: 180,
											name: 'INF6',
											tabIndex: TABINDEX_AMBCARDEW + 133
										}]
									}]
								}]
							}, {
								bodyStyle: 'margin-top: 5px;',
								border: false,
								height: 280,								
								labelWidth: 100,
								layout: 'form',
								listeners: {
									'activate': function(panel) {
										var form = this.findById('ambulance_card_edit_form');							
										form.getForm().findField('CmpDrug_id').focus(true, 100);
									}.createDelegate(this)
								},								
								title: lang['7_o6'],
								items: [
								{
									hiddenHame: 'CmpDrug_id',
									xtype: 'swcmpdrugcombo',
									width: 250,
									tabIndex: TABINDEX_AMBCARDEW + 135									
								},
								{
									border: false,
									layout: 'column',
									items: [{
										border: false,
										layout: 'form',
										items: [{
											xtype: 'textfield',
											maskRe: /\d/,
											fieldLabel: lang['kol-vo'],
											minLength: 0,
											allowDecimals: true,
											allowNegative: false,
											autoCreate: {tag: "input", type: "text", size: "10", maxLength: "10", autocomplete: "off"},
											width: 100,
											name: 'MKOL',
											tabIndex: TABINDEX_AMBCARDEW + 136
										}]
									}, {
										border: false,
										layout: 'form',
										items: [{
											xtype: 'button',
											handler: function() {
												var form = Ext.getCmp('ambulance_card_edit_form');
												var cmp_drug_combo = form.getForm().findField('CmpDrug_id');
												var cmp_drug_id = cmp_drug_combo.getValue();
												if ( !(cmp_drug_id > 0) )
													return false;
												var CmpDrug_Name = '';
												var CmpDrug_Code = '';
												var CmpDrug_Ei = '';
												var idx = cmp_drug_combo.getStore().findBy(function(record) {
													if ( record.data.CmpDrug_id == cmp_drug_id )
													{															
														CmpDrug_Name = record.data.CmpDrug_Name;
														CmpDrug_Code = record.data.CmpDrug_Code;
														CmpDrug_Ei = record.data.CmpDrug_Ei;
														return true;
													}
												});												
													
												var cmp_kolvo_field = form.getForm().findField('MKOL');
												var kolvo = cmp_kolvo_field.getValue();
												if ( !(kolvo > 0) )
													return false;
																								
												Ext.Ajax.request({
													callback: function(options, success, response) {
														if (success)
														{
															var resp = Ext.util.JSON.decode(response.responseText);
															var grid = Ext.getCmp('AmbulanceMedicamentViewGrid').ViewGridPanel;
															setGridRecord(grid, {
																CmpCallDrug_id: resp[0].CmpCallDrug_id,
																AmbulanceCard_id: form.getForm().findField('AmbulanceCard_id').getValue(),
																CmpDrug_id: cmp_drug_id,
																CmpDrug_Name: CmpDrug_Name,
																CmpDrug_Code: CmpDrug_Code,
																CmpDrug_Ei: CmpDrug_Ei,
																CmpDrug_Kolvo: kolvo
															}, true);
															cmp_drug_combo.clearValue();
															cmp_kolvo_field.setValue('');
															cmp_drug_combo.focus(true, 100);
														}
														else
														{
															sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_medikamenta_voznikli_oshibki']);
														}
													},
													params: {
														AmbulanceCard_id: form.getForm().findField('AmbulanceCard_id').getValue(),
														CmpDrug_id: cmp_drug_id,
														CmpDrug_Kolvo: kolvo
													},
													url: '/?c=AmbulanceCard&m=saveAmbulanceDrug'
												});
											},
											text: lang['dobavit'],
											width: 150,
											tabIndex: TABINDEX_AMBCARDEW + 137
										}]
									}]
								},
								new sw.Promed.ViewFrame(
								{
									actions:
									[
										{ name: 'action_add', disabled: true },
										{ name: 'action_edit', disabled: true },
										{ name: 'action_view', disabled: true },
										{ name: 'action_delete', handler: function() { Ext.getCmp('AmbulanceCardEditWindow').deleteAmbulanceMedicament() } },
										{ name: 'action_refresh', disabled: true },
										{ name: 'action_print' }
									],
									autoLoadData: false,
									dataUrl: '/?c=AmbulanceCard&m=getAmbulanceMedicamentList',
									id: 'AmbulanceMedicamentViewGrid',
									focusOn: {name:'ACEW_Save_Button', type:'field'},
									height: 300,
									//region: 'center',
									stringfields:
									[
										{name: 'CmpCallDrug_id', type: 'int', header: 'ID', key: true},
										{name: 'CmpDrug_id', type: 'int', hidden: true},
										{name: 'AmbulanceCard_id', type: 'int', hidden: true},
										{name: 'CmpDrug_Name',  type: 'string', header: lang['medikament'], width: 300},
										{name: 'CmpDrug_Code',  type: 'string', header: lang['kod']},
										{name: 'CmpDrug_Ei',  type: 'string', header: lang['ed_izmereniya']},
										{name: 'CmpDrug_Kolvo',  type: 'string', header: lang['kolichestvo']}
									],
									tabIndex: TABINDEX_AMBCARDEW + 137,
									title: lang['medikamentyi'],
									toolbar: true
								})]
							}, {
								bodyStyle: 'margin-top: 5px;',
								border: false,
								height: 280,								
								labelWidth: 65,
								layout: 'form',
								listeners: {
									'activate': function(panel) {
										var form = this.findById('ambulance_card_edit_form');							
										form.getForm().findField('DSHS').focus(true, 100);
									}.createDelegate(this)
								},								
								title: lang['8_o7'],
								items: [/*{
									xtype: 'textfield',
									maskRe: /\d/,
									fieldLabel: 'DS',
									minLength: 0,
									autoCreate: {tag: "input", type: "text", size: "5", maxLength: "5", autocomplete: "off"},
									width: 50,
									name: 'DSHS',
									tabIndex: TABINDEX_AMBCARDEW + 138
								}*/{
									fieldLabel: 'DS',
									hiddenName: 'DSHS',
									id: 'ACEW_DSHS',
									tabIndex: TABINDEX_AMBCARDEW + 138,
									width: 180,
									xtype: 'swdiagcombo'
								}, {
									/*xtype: 'textfield',
									maskRe: /\d/,
									fieldLabel: lang['r'],
									minLength: 0,
									autoCreate: {tag: "input", type: "text", size: "1", maxLength: "1", autocomplete: "off"},
									width: 50,
									name: 'FERR',
									tabIndex: TABINDEX_AMBCARDEW + 139*/
									allowBlank: true,
									hiddenName: 'FERR',
									width: 180,
									tabIndex: TABINDEX_AMBCARDEW + 139,
									xtype: 'swcmptaloncombo'
								}, {
									xtype: 'textfield',
									maskRe: /\d/,
									fieldLabel: lang['e'],
									minLength: 0,
									autoCreate: {tag: "input", type: "text", size: "4", maxLength: "4", autocomplete: "off"},
									width: 50,
									name: 'EXPO',
									tabIndex: TABINDEX_AMBCARDEW + 140
								}]
							}]})
              			]
   					})
   					],
       				keys: [{
						key: "0123456789",
						alt: true,
						fn: function(e) {Ext.getCmp("ambulance_card_tab_panel").setActiveTab(Ext.getCmp("ambulance_card_tab_panel").items.items[ e - 49 ]);},
						stopEvent: true
   					}, {
				    	alt: true,
				        fn: function(inp, e) {
							if ( e.browserEvent.stopPropagation )
								e.browserEvent.stopPropagation();
							else
								e.browserEvent.cancelBubble = true;
							if ( e.browserEvent.preventDefault )
								e.browserEvent.preventDefault();
							else
								e.browserEvent.returnValue = false;
							e.browserEvent.returnValue = false;
							e.returnValue = false;

							if (Ext.isIE)
				            {
				            	e.browserEvent.keyCode = 0;
				            	e.browserEvent.which = 0;
				            }

				            if (e.getKey() == Ext.EventObject.J)
				            {
				            	Ext.getCmp('AmbulanceCardEditWindow').hide();
				            	return false;
				            }
							if (e.getKey() == Ext.EventObject.C)
							{
					        	Ext.getCmp('AmbulanceCardEditWindow').buttons[0].handler();
								return false;
							}
				        },
				        key: [ Ext.EventObject.C, Ext.EventObject.J, Ext.EventObject.D, Ext.EventObject.Y ],
				        scope: this,
				        stopEvent: false
				    }]
		});
    	sw.Promed.swAmbulanceCardEditWindow.superclass.initComponent.apply(this, arguments);
    }
});