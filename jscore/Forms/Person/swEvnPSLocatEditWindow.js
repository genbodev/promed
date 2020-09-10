/*
* @package      Person
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       swan
* @version      27.12.2010
* @comment      Префикс
*/

sw.Promed.swEvnPSLocatEditWindow = Ext.extend(sw.Promed.BaseForm, {
	action: null,
	autoHeight: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	//title:'Движения амбулаторной карты',
	title:'Движения оригинала ИБ',
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
		
		base_form.submit({
			failure: function(result_form, action) {
				this.formStatus = 'edit';
				loadMask.hide();

				if ( action.result ) {
					if ( action.result.Error_Msg ) {
						sw.swMsg.alert('Ошибка', action.result.Error_Msg);
					}
					else {
						sw.swMsg.alert('Ошибка', 'При сохранении произошли ошибки [Тип ошибки: 1]');
					}
				}
			}.createDelegate(this),
			params: params,
			success: function(result_form, action) {
				this.formStatus = 'edit';
				loadMask.hide();

				if ( action.result ) {
					if ( action.result.PersonEvnPSLocat_id > 0 ) {
						base_form.findField('PersonEvnPSLocat_id').setValue(action.result.PersonEvnPSLocat_id);
						this.callback(params);
						this.hide();
					}
					else {
						if ( action.result.Error_Msg ) {
							sw.swMsg.alert('Ошибка', action.result.Error_Msg);
						}
						else {
							sw.swMsg.alert('Ошибка', 'При сохранении произошли ошибки [Тип ошибки: 3]');
						}
					}
				}
				else {
					sw.swMsg.alert('Ошибка', 'При сохранении произошли ошибки [Тип ошибки: 2]');
				}
			}.createDelegate(this)
		});
		
	},
	draggable: true,
	enableEdit: function(enable) {
		var base_form = this.FormPanel.getForm();
		var form_fields = new Array(
			'PersonEvnPSLocat_begD',
			'PersonEvnPSLocat_begT',
			'AmbulatCardLocatType_id',
			'PersonEvnPSLocat_OtherLocat',
			'MedStaffFact_id',
			'PersonEvnPSLocat_Desc'
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
	id: 'swEvnPSLocatEditWindow',
	initComponent: function() {
		this.FormPanel = new Ext.form.FormPanel({
			autoHeight: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: true,
			id: 'PersonEvnPSLocatEditForm',
			labelAlign: 'right',
			labelWidth: 130,
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			},  [
				{name: 'PersonEvnPSLocat_id'},
				{name: 'EvnPS_id'},
				{name: 'PersonEvnPSLocat_begD'},
				{name: 'PersonEvnPSLocat_begT'},
				{name: 'AmbulatCardLocatType_id'},
				{name: 'MedStaffFact_id'},
				//{name: 'MedPersonal_id'},
				{name: 'PersonEvnPSLocat_Desc'},
				{name: 'PersonEvnPSLocat_OtherLocat'}
			]),
			url: '/?c=EvnPSLocat&m=savePersonEvnPSLocat',

			items: [{
				name: 'PersonEvnPSLocat_id',
				hiddenName: 'PersonEvnPSLocat_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'EvnPS_id',
				hiddenName: 'EvnPS_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'Server_id',
				hiddenName: 'Server_id',
				xtype: 'hidden'
			}, {
				allowBlank: false,
				fieldLabel: 'Дата',
				format: 'd.m.Y',
				name: 'PersonEvnPSLocat_begD',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				hiddenName: 'PersonEvnPSLocat_begD',
				selectOnFocus: true,
				width: 100,
				xtype: 'swdatefield'
			},{
				allowBlank: false,
				fieldLabel: 'Время',
				name: 'PersonEvnPSLocat_begT',
				hiddenName: 'PersonEvnPSLocat_begT',
				width: 100,
				xtype: 'swtimefield'
				
			} , {
				allowBlank: false,
				fieldLabel: 'Местонахождение',
				hiddenName: 'AmbulatCardLocatType_id',
				anchor:'100%',
				xtype: 'swambulatcardlocattypecombo',
				listeners: {
					'change': function(combo, newValue, oldValue) {
						var base_form = this.FormPanel.getForm();
						
						base_form.findField('PersonEvnPSLocat_Desc').setAllowBlank(!newValue.inlist([3,9]));
							
						if(newValue==9){
							base_form.findField('PersonEvnPSLocat_OtherLocat').setContainerVisible(true);
							
						}else{
							base_form.findField('PersonEvnPSLocat_OtherLocat').setContainerVisible(false);
							base_form.findField('PersonEvnPSLocat_OtherLocat').setValue('');
							this.syncSize();
						}
						//base_form.findField('MedPersonal_id').setDisabled(newValue!=2);
						//base_form.findField('MedPersonal_id').setAllowBlank(newValue!=2);
						if(this.action != 'view')
							base_form.findField('MedStaffFact_id').setDisabled(newValue!=2 && (this.action == 'edit' || this.action == 'add'));
						base_form.findField('MedStaffFact_id').setAllowBlank(newValue!=2);
						if(newValue!=2){
							//base_form.findField('MedPersonal_id').setValue('');
							base_form.findField('MedStaffFact_id').setValue('');
						}
					}.createDelegate(this)
				}
			},{
				fieldLabel: 'Другое',
				hiddenName: 'PersonEvnPSLocat_OtherLocat',
				name: 'PersonEvnPSLocat_OtherLocat',
				anchor:'100%',
				xtype: 'textfield'
			},/*{
				allowBlank: false,
				editable: true,
				enableKeyEvents: true,
				fieldLabel: 'Сотрудник МО',
				hiddenName: 'MedPersonal_id',
				listeners: {
					'change': function(combo, newValue, oldValue) {
						var base_form = this.FormPanel.getForm();
						var MedStaffFact = base_form.findField('MedStaffFact_id');
						var MedStaffFact_id = MedStaffFact.getValue();
						MedStaffFact.getStore().removeAll();
						//debugger;
						//log(['change LpuSection_id', newValue, MedStaffFact_id]);
						if (newValue>0) {
							MedStaffFact.getStore().load({
								params:{
									MedPersonal_id: newValue,
									Lpu_id: this.Lpu_id
								},
								callback:function () {
									if (MedStaffFact_id>0 && MedStaffFact.getStore().getById(MedStaffFact_id)) {
										MedStaffFact.setValue(MedStaffFact_id);
									} else {
										if(MedStaffFact.getStore().getCount()==1){
											MedStaffFact.setValue(MedStaffFact.getStore().getAt(0).id);
										}else{
											MedStaffFact.clearValue();
										}
										
									}
								}
							});
						} else {
							MedStaffFact.clearValue();
						}
					}.createDelegate(this)
				},
				anchor:'100%',
				xtype: 'swmedpersonalcombo'
			}, {
				allowBlank: false,
				fieldLabel: 'Должность',
				hiddenName: 'MedStaffFact_id',
				name: 'MedStaffFact_id',
				anchor:'100%',
				xtype: 'swmedstafffactpostcombo'
			},*/
			new sw.Promed.SwMedStaffFactGlobalCombo({
				id: 'EPSLEW_MedPersonalCombo',
				name: 'MedStaffFact_id',
				fieldLabel: 'Место работы',
				disabled: true,
				lastQuery: '',
				listWidth: 700,
				tabIndex: TABINDEX_ERSIF + 3,
				width: 400
			}),
			{
				fieldLabel: 'Пояснение',
				hiddenName: 'PersonEvnPSLocat_Desc',
				name: 'PersonEvnPSLocat_Desc',
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

		sw.Promed.swEvnPSLocatEditWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('swEvnPSLocatEditWindow');

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
		sw.Promed.swEvnPSLocatEditWindow.superclass.show.apply(this, arguments);
		this.type='';
		this.center();
		var win = this;
		var base_form = this.FormPanel.getForm();
		base_form.reset();
		this.Lpu_id = null;
		if ( !arguments[0]) {
			sw.swMsg.alert('Сообщение', 'Неверные параметры', function() {this.hide();}.createDelegate(this) );
			return false;
			
		}
		
		base_form.setValues(arguments[0].formParams);
		swMedStaffFactGlobalStore.clearFilter();
		setMedStaffFactGlobalStoreFilter({
			allowLowLevel: 'yes',
			isStac: true
		});
		base_form.findField('MedStaffFact_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
		
		
		base_form.findField('PersonEvnPSLocat_Desc').setAllowBlank(true);
		//base_form.findField('MedPersonal_id').setDisabled(true);
		//base_form.findField('MedPersonal_id').setAllowBlank(true);
		base_form.findField('MedStaffFact_id').setDisabled(true);
		base_form.findField('MedStaffFact_id').setAllowBlank(true);
		
		base_form.findField('PersonEvnPSLocat_OtherLocat').setContainerVisible(false);
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
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: LOAD_WAIT});
		loadMask.show();
		var MedStaffFact = base_form.findField('MedStaffFact_id');
		//MedStaffFact.getStore().removeAll();
		switch ( this.action ) {
			case 'add':
				
				this.enableEdit(true);
				base_form.findField('MedStaffFact_id').setDisabled(true);
				base_form.findField('MedStaffFact_id').setAllowBlank(true);
				/*base_form.findField('MedPersonal_id').getStore().load({
					params:
					{
						Lpu_id: win.Lpu_id
					},
					callback: function() {
						base_form.findField('MedPersonal_id').setValue(null);
					}
				});*/
				loadMask.hide();
				//если не передали - устанавливаю текущую
				setCurrentDateTime({
					dateField: base_form.findField('PersonEvnPSLocat_begD'),
					loadMask: true,
					setDate: true,
					setTime: true,
					setDateMaxValue: true,
					timeField:base_form.findField('PersonEvnPSLocat_begT'),
					windowId: this.id
				});
			break;

			case 'edit':
			case 'view':
				if(this.type=='nosave'&&base_form.findField('PersonEvnPSLocat_id').getValue()<=0){
					
					base_form.setValues(arguments[0].data);
					if ( this.action == 'edit' ) {
						this.enableEdit(true);
					}
					else {
						this.enableEdit(false);
					}
					/*base_form.findField('MedPersonal_id').getStore().load({
						params:
						{
							Lpu_id: win.Lpu_id
						},
						callback: function() {
							var MP = base_form.findField('MedPersonal_id');
							MP.fireEvent('change',MP,MP.getValue());
							base_form.clearInvalid();
						}
					});*/
					var AC = base_form.findField('AmbulatCardLocatType_id');
					AC.fireEvent('change',AC,AC.getValue());
					base_form.clearInvalid();
					if ( this.action == 'view' ) {
						this.buttons[this.buttons.length - 1].focus();
					}
					else {
						base_form.findField('PersonEvnPSLocat_begD').focus(true, 250);
					}
					loadMask.hide();
				}else{
					var PersonEvnPSLocat_id = base_form.findField('PersonEvnPSLocat_id').getValue();

					if ( !PersonEvnPSLocat_id ) {
						loadMask.hide();
						this.hide();
						return false;
					}

					base_form.load({
						failure: function() {
							loadMask.hide();
							sw.swMsg.alert('Ошибка', 'Ошибка при загрузке данных формы', function() {this.hide();}.createDelegate(this) );
						}.createDelegate(this),
						params: {
							'PersonEvnPSLocat_id': PersonEvnPSLocat_id
						},
						success: function() {
							if ( this.action == 'edit' ) {
								this.enableEdit(true);
							}
							else {
								this.enableEdit(false);
							}

							loadMask.hide();
							/*base_form.findField('MedPersonal_id').getStore().load({
								params:
								{
									Lpu_id: win.Lpu_id
								},
								callback: function() {
									var MP = base_form.findField('MedPersonal_id');
									MP.fireEvent('change',MP,MP.getValue());
									base_form.clearInvalid();
								}
							});*/
							var AC = base_form.findField('AmbulatCardLocatType_id');
							AC.fireEvent('change',AC,AC.getValue());
							base_form.clearInvalid();
							if ( this.action == 'view' ) {
								this.buttons[this.buttons.length - 1].focus();
							}
							else {
								base_form.findField('PersonEvnPSLocat_begD').focus(true, 250);
							}
						}.createDelegate(this),
						url: '/?c=EvnPSLocat&m=loadPersonEvnPSLocat'
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