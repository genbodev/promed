/*
* @package      Person
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       swan
* @version      27.12.2010
* @comment      Префикс
*/

sw.Promed.swPersonAmbulatCardLocatEditWindow = Ext.extend(sw.Promed.BaseForm, {
	action: null,
	autoHeight: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	title:lang['dvijeniya_ambulatornoy_kartyi'],
	closeAction: 'hide',
	collapsible: false,
	doSave: function(options) {
		// options @Object
		// options.ignoreHeightIsIncorrect @Boolean Признак игнорирования проверки правильности ввода длины (роста)

		if ( this.formStatus == 'save' ) {
			return false;
		}

		this.formStatus = 'save';
		
		var form = this.FormPanel;
		var base_form = form.getForm();

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
		var params = new Object();

		var data = new Object();
		
		if(this.type=='nosave'){
		params.MedStaffFact_id= base_form.findField('MedStaffFact_id').getValue();
		params.PersonAmbulatCardLocat_Desc= base_form.findField('PersonAmbulatCardLocat_Desc').getValue();
		params.PersonAmbulatCardLocat_OtherLocat= base_form.findField('PersonAmbulatCardLocat_OtherLocat').getValue();
		params.PersonAmbulatCardLocat_begD= base_form.findField('PersonAmbulatCardLocat_begD').getValue();
		params.PersonAmbulatCardLocat_begT= base_form.findField('PersonAmbulatCardLocat_begT').getValue();
		params.AmbulatCardLocatType_id= base_form.findField('AmbulatCardLocatType_id').getValue();
		params.Server_id= base_form.findField('Server_id').getValue();
		params.PersonAmbulatCardLocat_id = base_form.findField('PersonAmbulatCardLocat_id').getValue();
		params.PersonAmbulatCardLocat_begDate = Ext.util.Format.date(base_form.findField('PersonAmbulatCardLocat_begD').getValue(),'Y-m-d')+" "+base_form.findField('PersonAmbulatCardLocat_begT').getValue();
		params.AmbulatCardLocatType = base_form.findField('AmbulatCardLocatType_id').getRawValue();
			params.FIO = base_form.findField('MedStaffFact_id').getFieldValue('MedPersonal_Fio');
		params.MedStaffFact = base_form.findField('MedStaffFact_id').getRawValue();
			params.LpuBuilding_id = base_form.findField('LpuBuilding_id').getValue();
			params.LpuBuilding_Name = base_form.findField('LpuBuilding_id').getRawValue();
		params.isSave=1;
			loadMask.hide();
			log('sdfsdfsdfsd');
			this.formStatus = 'edit';
			this.callback(params);
			this.hide();
		}else{
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

				if ( action.result ) {
					if ( action.result.PersonAmbulatCardLocat_id > 0 ) {
						base_form.findField('PersonAmbulatCardLocat_id').setValue(action.result.PersonAmbulatCardLocat_id);
						this.callback(params);
						this.hide();
					}
					else {
						if ( action.result.Error_Msg ) {
							sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
						}
						else {
							sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_3]']);
						}
					}
				}
				else {
					sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_2]']);
				}
			}.createDelegate(this)
		});
		}
	},
	draggable: true,
	enableEdit: function(enable) {
		var base_form = this.FormPanel.getForm();
		var form_fields = new Array(
			
		);
		var i = 0;

		for ( i = 0; i < form_fields.length; i++ ) {
			if ( enable ) {
				base_form.findField(form_fields[i]).enable();
			}
			else {
				base_form.findField(form_fields[i]).disable();
			}
		}

		if ( enable ) {
			this.buttons[0].show();
		}
		else {
			this.buttons[0].hide();
		}
	},
	formMode: 'remote',
	formStatus: 'edit',
	id: 'swPersonAmbulatCardLocatEditWindow',
	initComponent: function() {
		var win = this;
		this.FormPanel = new Ext.form.FormPanel({
			autoHeight: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: true,
			id: 'PersonAmbulatCardLocatEditForm',
			labelAlign: 'right',
			labelWidth: 130,
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			},  [
				{name: 'PersonAmbulatCardLocat_id'},
				{name: 'PersonAmbulatCard_id'},
				{name: 'PersonAmbulatCardLocat_begD'},
				{name: 'PersonAmbulatCardLocat_begT'},
				{name: 'AmbulatCardLocatType_id'},
				{name: 'MedStaffFact_id'},
				{name: 'PersonAmbulatCardLocat_Desc'},
				{name: 'PersonAmbulatCardLocat_OtherLocat'},
				{name: 'LpuBuilding_id'}
			]),
			url: '/?c=PersonAmbulatCard&m=savePersonAmbulatCardLocat',

			items: [{
				name: 'PersonAmbulatCardLocat_id',
				hiddenName: 'PersonAmbulatCardLocat_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'PersonAmbulatCard_id',
				hiddenName: 'PersonAmbulatCard_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'Server_id',
				hiddenName: 'Server_id',
				xtype: 'hidden'
			}, {
				allowBlank: false,
				fieldLabel: lang['data'],
				format: 'd.m.Y',
				name: 'PersonAmbulatCardLocat_begD',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				hiddenName: 'PersonAmbulatCardLocat_begD',
				selectOnFocus: true,
				width: 100,
				xtype: 'swdatefield'
			},{
				allowBlank: false,
				fieldLabel: lang['vremya'],
				name: 'PersonAmbulatCardLocat_begT',
				hiddenName: 'PersonAmbulatCardLocat_begT',
				width: 100,
				xtype: 'swtimefield'
				
			} , {
				allowBlank: false,
				fieldLabel: lang['mestonahojdenie'],
				hiddenName: 'AmbulatCardLocatType_id',
				anchor:'100%',
				xtype: 'swambulatcardlocattypecombo',
				listeners: {
					'change': function(combo, newValue, oldValue) {
						var base_form = this.FormPanel.getForm();
						
						base_form.findField('PersonAmbulatCardLocat_Desc').setAllowBlank(!newValue.inlist([3,9]));
							
						if(newValue==9){
							base_form.findField('PersonAmbulatCardLocat_OtherLocat').setContainerVisible(true);
							
						}else{
							base_form.findField('PersonAmbulatCardLocat_OtherLocat').setContainerVisible(false);
							base_form.findField('PersonAmbulatCardLocat_OtherLocat').setValue('');
							this.syncSize();
						}
						base_form.findField('MedStaffFact_id').setDisabled(newValue!=2);
						base_form.findField('MedStaffFact_id').setAllowBlank(newValue!=2);
						
						//картохранилище
						var id=10;
						var personAmbulatCardLocatDesc_AllowBlank = ([9,5,6,8,3].indexOf(newValue) <0) ? true : false;
						base_form.findField('LpuBuilding_id').setAllowBlank(newValue!=id);
						base_form.findField('MedStaffFact_id').setDisabled(newValue==id);
						base_form.findField('PersonAmbulatCardLocat_Desc').setAllowBlank(personAmbulatCardLocatDesc_AllowBlank);
					}.createDelegate(this)
				}
			},{
				hiddenName: 'LpuBuilding_id',
				name: 'LpuBuilding_id',
				fieldLabel: langs('Картохранилище'),
				listeners: {
					'change': function(combo, newValue, oldValue) {
						//...
					}
				},
				anchor:'100%',
				xtype: 'swlpubuildingglobalcombo'
			},{
				fieldLabel: langs('Другое'),
				hiddenName: 'PersonAmbulatCardLocat_OtherLocat',
				name: 'PersonAmbulatCardLocat_OtherLocat',
				anchor:'100%',
				xtype: 'textfield'
			},{
				allowBlank: false,
				fieldLabel: lang['sotrudnik_mo'],
				hiddenName: 'MedStaffFact_id',
				name: 'MedStaffFact_id',
				anchor:'100%',
				listeners: {
					'change': function(combo, newValue) {
						var base_form = win.FormPanel.getForm();
						if (newValue) {
										
							base_form.findField('MedStaffFact_postName').setValue(combo.getFieldValue('PostMed_Name'))
									}
								}
				},
				xtype: 'swmedstafffactglobalcombo'
			}, {
				allowBlank: true,
				disabled: true,
				name: 'MedStaffFact_postName',
				fieldLabel: lang['doljnost'],
				xtype: 'textfield'
			},{
				fieldLabel: lang['poyasnenie'],
				hiddenName: 'PersonAmbulatCardLocat_Desc',
				name: 'PersonAmbulatCardLocat_Desc',
				anchor:'100%',
				xtype: 'textarea'
			}]
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'save16',
				onTabAction: function () {
					this.buttons[this.buttons.length - 1].focus(true);
				}.createDelegate(this),
				tabIndex: TABINDEX_PHEF + 6,
				text: BTN_FRMSAVE
			}, {
				text: '-'
			},
			HelpButton(this, -1),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				onShiftTabAction: function () {
					if ( !this.buttons[0].hidden ) {
						this.buttons[0].focus(true);
					}
				}.createDelegate(this),
				tabIndex: TABINDEX_PHEF + 7,
				text: BTN_FRMCANCEL
			}],
			items: [
				this.FormPanel
			],
			layout: 'form'
		});

		sw.Promed.swPersonAmbulatCardLocatEditWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('swPersonAmbulatCardLocatEditWindow');

			switch ( e.getKey() ) {
				case Ext.EventObject.C:
					current_window.doSave();
				break;

				case Ext.EventObject.J:
					current_window.hide();
				break;
			}
		},
		key: [
			Ext.EventObject.C,
			Ext.EventObject.J
		],
		scope: this,
		stopEvent: true
	}],
	listeners: {
		'beforehide': function(win) {
			// 
		},
		'hide': function(win) {
			win.onHide();
		}
	},
	maximizable: false,
	maximized: false,
	modal: true,
	onHide: Ext.emptyFn,
	plain: true,
	resizable: false,
	show: function() {
		sw.Promed.swPersonAmbulatCardLocatEditWindow.superclass.show.apply(this, arguments);
		
		this.type='';
		this.center();
		var win = this;
		var base_form = this.FormPanel.getForm();
		base_form.reset();
		this.Lpu_id = null;
		if ( !arguments[0]) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() {this.hide();}.createDelegate(this) );
			return false;
			
		}
		
		base_form.setValues(arguments[0].formParams);
		
		base_form.findField('PersonAmbulatCardLocat_Desc').setAllowBlank(true);
		// base_form.findField('MedStaffFact_id').setDisabled(true);
		// base_form.findField('MedStaffFact_id').setAllowBlank(true);
		// base_form.findField('MedStaffFact_id').setDisabled(false);
		// base_form.findField('MedStaffFact_id').setAllowBlank(true);
		
		base_form.findField('PersonAmbulatCardLocat_OtherLocat').setContainerVisible(false);
		this.syncSize();
		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		}
		if ( arguments[0].callback && typeof arguments[0].callback == 'function' ) {
			this.callback = arguments[0].callback;
		}
		if ( arguments[0].Lpu_id ) {
			this.Lpu_id = arguments[0].Lpu_id;
		}
		if ( arguments[0].type ) {
			this.type = arguments[0].type;
		}
		if(arguments[0].onHide){
			this.onHide=arguments[0].onHide;
		}
		if(arguments[0].formParams.isSave ) {
			this.isSave = arguments[0].formParams.isSave;
		} else {
			this.isSave = 0;
		}
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: LOAD_WAIT});
		loadMask.show();
		var MedStaffFact = base_form.findField('MedStaffFact_id');
		var StorageCard = base_form.findField('LpuBuilding_id');
		StorageCard.getStore().baseParams = {Lpu_id: win.Lpu_id};
		MedStaffFact.getStore().removeAll();
		switch ( this.action ) {
			case 'add':
				StorageCard.getStore().load();
				this.enableEdit(true);
				base_form.findField('MedStaffFact_id').getStore().load({
					params:
					{
						Lpu_id: win.Lpu_id
					},
					callback: function() {
						base_form.findField('MedStaffFact_id').setValue(null);
					}
				});
				loadMask.hide();
				//если не передали - устанавливаю текущую
				setCurrentDateTime({
					dateField: base_form.findField('PersonAmbulatCardLocat_begD'),
					loadMask: true,
					setDate: true,
					setTime: true,
					setDateMaxValue: true,
					timeField:base_form.findField('PersonAmbulatCardLocat_begT'),
					windowId: this.id
				});
			break;

			case 'edit':
			case 'view':
				if(this.type=='nosave'&& this.isSave == 1){
					
					base_form.setValues(arguments[0].data);
					if ( this.action == 'edit' ) {
						this.enableEdit(true);
					}
					else {
						this.enableEdit(false);
					}
					base_form.findField('MedStaffFact_id').getStore().load({
						params:
						{
							Lpu_id: win.Lpu_id
						},
						callback: function() {
							var MP = base_form.findField('MedStaffFact_id');
							MP.fireEvent('change',MP,MP.getValue());
							base_form.clearInvalid();
						}
					});
					StorageCard.getStore().load({
						callback: function() {
							var LB = base_form.findField('LpuBuilding_id');
							LB.fireEvent('change',LB,LB.getValue());
							base_form.clearInvalid();
						}
					});
					var AC = base_form.findField('AmbulatCardLocatType_id');
					AC.fireEvent('change',AC,AC.getValue());
					base_form.clearInvalid();
					if ( this.action == 'view' ) {
						this.buttons[this.buttons.length - 1].focus();
					}
					else {
						base_form.findField('PersonAmbulatCardLocat_begD').focus(true, 250);
					}
					loadMask.hide();
				}else{
					var PersonAmbulatCardLocat_id = base_form.findField('PersonAmbulatCardLocat_id').getValue();

					if ( !PersonAmbulatCardLocat_id ) {
						loadMask.hide();
						this.hide();
						return false;
					}

					base_form.load({
						failure: function() {
							loadMask.hide();
							sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_dannyih_formyi'], function() {this.hide();}.createDelegate(this) );
						}.createDelegate(this),
						params: {
							'PersonAmbulatCardLocat_id': PersonAmbulatCardLocat_id
						},
						success: function() {
							if ( this.action == 'edit' ) {
								this.enableEdit(true);
							}
							else {
								this.enableEdit(false);
							}

							loadMask.hide();
							base_form.findField('MedStaffFact_id').getStore().load({
								params:
								{
									Lpu_id: win.Lpu_id
								},
								callback: function() {
									var MP = base_form.findField('MedStaffFact_id');
									MP.setValue(MP.getValue());
									MP.fireEvent('change',MP,MP.getValue());
									base_form.clearInvalid();
								}
							});
							StorageCard.getStore().load({
								callback: function() {
									var LB = base_form.findField('LpuBuilding_id');
									LB.fireEvent('change',LB,LB.getValue());
									base_form.clearInvalid();
								}
							});
							var AC = base_form.findField('AmbulatCardLocatType_id');
							AC.fireEvent('change',AC,AC.getValue());
							base_form.clearInvalid();
							if ( this.action == 'view' ) {
								this.buttons[this.buttons.length - 1].focus();
							}
							else {
								base_form.findField('PersonAmbulatCardLocat_begD').focus(true, 250);
							}
						}.createDelegate(this),
						url: '/?c=PersonAmbulatCard&m=loadPersonAmbulatCardLocat'
					});
				}
			break;

			default:
				loadMask.hide();
				this.hide();
			break;
		}
	},
	width: 600
});