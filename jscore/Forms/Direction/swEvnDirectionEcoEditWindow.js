/**
* swEvnDirectionEcoEditWindow - направление на ЭКО
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Direction
* @access       public
* @copyright    Copyright (c) 2019 Swan Ltd.
* @author       Stanislav Bykov (savage@swan-it.ru)
* @version      06.06.2019
*/

sw.Promed.swEvnDirectionEcoEditWindow = Ext.extend(sw.Promed.BaseForm, {
	/* свойства */
	action: null,
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	collapsible: false,
	draggable: true,
	formStatus: 'edit',
	height: 550,
	id: 'EvnDirectionEcoEditWindow',
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var win = Ext.getCmp('EvnDirectionEcoEditWindow');

			switch ( e.getKey() ) {
				case Ext.EventObject.C:
					win.doSave();
				break;

				case Ext.EventObject.J:
					win.hide();
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
	layout: 'border',
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
	plain: true,
	resizable: false,
	width: 750,

	/* методы */
	callback: Ext.emptyFn,
	doSave: function(options) {
		var win = this;
		// options @Object
		// options.print @Boolean Вызывать печать направления на ЭКО, если true

		if ( this.formStatus == 'save' ) {
			return false;
		}

		this.formStatus = 'save';

		var
			form = this.FormPanel,
			base_form = form.getForm();

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

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Подождите, идет сохранение направления..." });
		loadMask.show();

		base_form.submit({
			failure: function(result_form, action) {
				this.formStatus = 'edit';
				loadMask.hide();

				if ( action.result ) {
					if ( action.result.Error_Msg ) {
						sw.swMsg.alert(langs('Ошибка'), action.result.Error_Msg);
					}
					else {
						sw.swMsg.alert(langs('Ошибка'), langs('При сохранении произошли ошибки [Тип ошибки: 1]'));
					}
				}
			}.createDelegate(this),
			success: function(result_form, action) {
				this.formStatus = 'edit';
				loadMask.hide();

				if ( action.result ) {
					if ( action.result.EvnDirectionEco_id ) {
						base_form.findField('EvnDirectionEco_id').setValue(action.result.EvnDirectionEco_id);

						this.callback();

						if ( options && options.print ) {
							this.printEvnDirectionEco(true);
						}
						else {
							this.hide();
						}
					}
					else {
						if ( action.result.Error_Msg ) {
							sw.swMsg.alert(langs('Ошибка'), action.result.Error_Msg);
						}
						else {
							sw.swMsg.alert(langs('Ошибка'), langs('При сохранении произошли ошибки [Тип ошибки: 3]'));
						}
					}
				}
				else {
					sw.swMsg.alert(langs('Ошибка'), langs('При сохранении произошли ошибки [Тип ошибки: 2]'));
				}
			}.createDelegate(this)
		});
	},
	onHide: Ext.emptyFn,
	printEvnDirectionEco: function(print) {
		if (print !== true && this.action.inlist(['add','edit'])) {
			this.doSave({
				print: true
			});
			return true;
		}

		var base_form = this.FormPanel.getForm();

		window.open('/?c=EvnDirectionEco&m=printEvnDirectionEco&EvnDirectionEco_id=' + base_form.findField('EvnDirectionEco_id').getValue(), '_blank');
	},
	setEvnDirectionEcoNumber: function() {
		var base_form = this.FormPanel.getForm();

		Ext.Ajax.request({
			callback: function(options, success, response) {
				if ( success ) {
					var response_obj = Ext.util.JSON.decode(response.responseText);
					base_form.findField('EvnDirectionEco_Num').setValue(response_obj.EvnDirectionEco_Num);
				}
				else {
					sw.swMsg.alert(langs('Ошибка'), langs('Ошибка при определении номера направления'), function() { base_form.findField('EvnDirectionEco_Num').focus(true); }.createDelegate(this) );
				}
			}.createDelegate(this),
			url: '/?c=EvnDirectionEco&m=getEvnDirectionEcoNumber'
		});
	},
	show: function() {
		sw.Promed.swEvnDirectionEcoEditWindow.superclass.show.apply(this, arguments);

		this.center();

		var win = this;

		var base_form = win.FormPanel.getForm();
		base_form.reset();

		this.action = null;
		this.callback = Ext.emptyFn;
		this.formStatus = 'edit';
		this.onHide = Ext.emptyFn;

		if ( !arguments[0] || !arguments[0].formParams ) {
			sw.swMsg.alert(langs('Сообщение'), langs('Неверные параметры'), function() { /*this.hide();*/ }.createDelegate(this) );
			return false;
		}

		if( arguments[0].formParams.PersonEvn_id ){
			base_form.findField('PersonEvn_id').setValue(arguments[0].formParams.PersonEvn_id);
		}
		if( arguments[0].formParams.Person_id ){
			base_form.findField('Person_id').setValue(arguments[0].formParams.Person_id);			
		}
		if( arguments[0].formParams.Server_id ){
			base_form.findField('Server_id').setValue(arguments[0].formParams.Server_id);			
		}

		base_form.setValues(arguments[0].formParams);

		this.PersonInfo.setTitle('...');
		this.PersonInfo.load({
			callback: function() {
				this.PersonInfo.setPersonTitle();
			}.createDelegate(this),
			Person_id: base_form.findField('Person_id').getValue(),
			Server_id: base_form.findField('Server_id').getValue()
		});

		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		}

		if ( arguments[0].callback && typeof arguments[0].callback == 'function' ) {
			this.callback = arguments[0].callback;
		}
		
		if ( arguments[0].onHide && typeof arguments[0].onHide == 'function' ) {
			this.onHide = arguments[0].onHide;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
		loadMask.show();

		switch ( this.action ) {
			case 'add':
				this.setTitle(langs('Направление на ЭКО: Добавление'));
				this.enableEdit(true);

				this.setEvnDirectionEcoNumber();

				base_form.findField('KLRgnRF_id').setValue(getRegionNumber());
				base_form.findField('KLRgnRF_id').fireEvent('change', base_form.findField('KLRgnRF_id'), base_form.findField('KLRgnRF_id').getValue());

				setCurrentDateTime({
					callback: function() {
						base_form.findField('EvnDirectionEco_setDate').fireEvent('change', base_form.findField('EvnDirectionEco_setDate'), base_form.findField('EvnDirectionEco_setDate').getValue());

						loadMask.hide();
					
						base_form.findField('EvnDirectionEco_Num').focus(true, 250);
					}.createDelegate(this),
					dateField: base_form.findField('EvnDirectionEco_setDate'),
					loadMask: false,
					setDate: true,
					setDateMaxValue: true,
					windowId: this.id
				});
				break;

			case 'edit':
			case 'view':
				var EvnDirectionEco_id = base_form.findField('EvnDirectionEco_id').getValue();

				if ( !EvnDirectionEco_id ) {
					loadMask.hide();
					this.hide();
					return false;
				}

				base_form.load({
					failure: function() {
						loadMask.hide();
						sw.swMsg.alert(langs('Ошибка'), langs('Ошибка при загрузке данных формы'), function() { this.hide(); }.createDelegate(this) );
					}.createDelegate(this),
					params: {
						'EvnDirectionEco_id': EvnDirectionEco_id
					},
					success: function(form, act) {
						var response_obj = Ext.util.JSON.decode(act.response.responseText);

						if (response_obj[0].accessType == 'view') {
							this.action = 'view';
						}

						if ( this.action == 'edit' ) {
							this.setTitle(langs('Направление на ЭКО: Редактирование'));
							this.enableEdit(true);
						}
						else {
							this.setTitle(langs('Направление на ЭКО: Просмотр'));
							this.enableEdit(false);
						}

						if ( this.action == 'edit' ) {
							setCurrentDateTime({
								dateField: base_form.findField('EvnDirectionEco_setDate'),
								loadMask: false,
								setDate: false,
								setDateMaxValue: true,
								windowId: this.id
							});
						}
						
						var Diag_id = response_obj[0].Diag_id;

						if ( !Ext.isEmpty(Diag_id) ) {
							base_form.findField('Diag_id').getStore().load({
								callback: function() {
									base_form.findField('Diag_id').getStore().each(function (rec) {
										if ( rec.get('Diag_id') == Diag_id ) {
											base_form.findField('Diag_id').fireEvent('select', base_form.findField('Diag_id'), rec, 0);
										}
									});
								},
								params: {
									where: "where DiagLevel_id = 4 and Diag_id = " + Diag_id
								}
							});
						}

						if ( !Ext.isEmpty(base_form.findField('Org_id').getValue()) ) {
							base_form.findField('KLRgnRF_id').setValue(parseInt(base_form.findField('Org_id').getFieldValue('OrgECO_f003mcod').substr(0, 2)));
						}

						loadMask.hide();

						if ( this.action == 'edit' ) {
							base_form.findField('EvnDirectionEco_Num').focus(true, 250);
						}
						else {
							this.buttons[this.buttons.length - 1].focus();
						}
					}.createDelegate(this),
					url: '/?c=EvnDirectionEco&m=loadEvnDirectionEcoEditForm'
				});
				break;

			default:
				loadMask.hide();
				this.hide();
				break;
		}
	},

	/* конструктор */
	initComponent: function() {
		var
			formTabIndex = 2321412345,
			win = this;

		win.FormPanel = new Ext.form.FormPanel({
			autoScroll: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: false,
			id: 'EvnDirectionEcoEditForm',
			labelAlign: 'right',
			labelWidth: 200,
			reader: new Ext.data.JsonReader({
				success: Ext.amptyFn
			},  [
				{ name: 'accessType' },
				{ name: 'Diag_id' },
				{ name: 'EvnDirectionEco_Comment' },
				{ name: 'EvnDirectionEco_CommentVKMZ' },
				{ name: 'EvnDirectionEco_GiveDate' },
				{ name: 'EvnDirectionEco_id' },
				{ name: 'EvnDirectionEco_Num' },
				{ name: 'EvnDirectionEco_NumVKMZ' },
				{ name: 'EvnDirectionEco_setDate' },
				{ name: 'EvnDirectionEco_VKMZDate' },
				{ name: 'KLRgnRF_id' },
				{ name: 'Org_id' },
				{ name: 'MedPersonal_id' },
				{ name: 'MedStaffFact_id' },
				{ name: 'Person_id' },
				{ name: 'PersonEvn_id' },
				{ name: 'Server_id' }
			]),
			region: 'center',
			url: '/?c=EvnDirectionEco&m=saveEvnDirectionEco',

			items: [{
				name: 'accessType',
				xtype: 'hidden'
			}, {
				name: 'EvnDirectionEco_id',
				xtype: 'hidden'
			}, {
				name: 'MedPersonal_id',
				xtype: 'hidden'
			}, {
				name: 'MedStaffFact_id',
				xtype: 'hidden'
			}, {
				name: 'Person_id',
				xtype: 'hidden'
			}, {
				name: 'PersonEvn_id',
				xtype: 'hidden'
			}, {
				name: 'Server_id',
				xtype: 'hidden'
			}, {
				allowBlank: false,
				enableKeyEvents: true,
				fieldLabel: '№',
				listeners: {
					'keydown': function(inp, e) {
						if ( e.getKey() == Ext.EventObject.TAB && e.shiftKey == true ) {
							e.stopEvent();
							win.buttons[win.buttons.length - 1].focus();
						}
					}
				},
				name: 'EvnDirectionEco_Num',
				tabIndex: formTabIndex++,
				width: 100,
				xtype: 'textfield'
			}, {
				allowBlank: false,
				fieldLabel: langs('Дата выписки'),
				name: 'EvnDirectionEco_setDate',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				tabIndex: formTabIndex++,
				width: 100,
				xtype: 'swdatefield'
			}, {
				allowBlank: false,
				fieldLabel: langs('Диагноз'),
				hiddenName: 'Diag_id',
				tabIndex: formTabIndex++,
				width: 430,
				xtype: 'swdiagcombo'
			}, {
				allowBlank: false,
				comboSubject: 'KLRgnRF',
				fieldLabel: langs('Регион'),
				hiddenName: 'KLRgnRF_id',
				listeners: {
					'change': function(field, newValue, oldValue) {
						var index = field.getStore().findBy(function(rec) {
							return rec.get('KLRgnRF_id') == newValue;
						});
						field.fireEvent('select', field, field.getStore().getAt(index), index);
					},
					'select': function(field, record, idx) {
						var
							base_form = win.FormPanel.getForm(),
							index,
							Org_id = base_form.findField('Org_id').getValue();

						base_form.findField('Org_id').clearValue();
						base_form.findField('Org_id').getStore().clearFilter();
						base_form.findField('Org_id').lastQuery = '';

						if ( typeof record == 'object' && !Ext.isEmpty(record.get('KLRgnRF_Code')) ) {
							var KLRgnRF_Code = record.get('KLRgnRF_Code');

							if ( KLRgnRF_Code.toString().length == 1 ) {
								KLRgnRF_Code = '0' + KLRgnRF_Code;
							}

							base_form.findField('Org_id').getStore().filterBy(function(rec) {
								return !Ext.isEmpty(rec.get('OrgECO_f003mcod')) && rec.get('OrgECO_f003mcod').substr(0, 2) == KLRgnRF_Code;
							});
						}

						if ( !Ext.isEmpty(Org_id) ) {
							index = base_form.findField('Org_id').getStore().findBy(function(rec) {
								return rec.get('OrgECO_id') == Org_id;
							});

							if ( index >= 0 ) {
								base_form.findField('Org_id').setValue(Org_id);
							}
						}
					}
				},
				tabIndex: formTabIndex++,
				width: 430,
				xtype: 'swcommonsprcombo'
			}, {
				allowBlank: false,
				comboSubject: 'OrgECO',
				fieldLabel: langs('МО направления'),
				hiddenName: 'Org_id',
				lastQuery: '',
				moreFields: [
					{ name: 'OrgECO_f003mcod', mapping: 'OrgECO_f003mcod' }
				],
				showCodefield: false,
				tabIndex: formTabIndex++,
				width: 430,
				xtype: 'swcommonsprcombo'
			}, {
				allowBlank: false,
				fieldLabel: '№ протокола ВК МЗ',
				name: 'EvnDirectionEco_NumVKMZ',
				tabIndex: formTabIndex++,
				width: 100,
				xtype: 'textfield'
			}, {
				allowBlank: false,
				fieldLabel: langs('Дата заседания ВК МЗ'),
				name: 'EvnDirectionEco_VKMZDate',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				tabIndex: formTabIndex++,
				width: 100,
				xtype: 'swdatefield'
			}, {
				fieldLabel: langs('Комментарий ВК МЗ'),
				height: 100,
				name: 'EvnDirectionEco_CommentVKMZ',
				tabIndex: formTabIndex++,
				width: 430,
				xtype: 'textarea'
			}, {
				fieldLabel: langs('Дата выдачи'),
				name: 'EvnDirectionEco_GiveDate',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				tabIndex: formTabIndex++,
				width: 100,
				xtype: 'swdatefield'
			}, {
				fieldLabel: langs('Комментарий к направлению'),
				height: 100,
				name: 'EvnDirectionEco_Comment',
				tabIndex: formTabIndex++,
				width: 430,
				xtype: 'textarea'
			}]
		});

		this.PersonInfo = new sw.Promed.PersonInfoPanel({
			button1OnHide: function() {
				if ( this.action == 'view' ) {
					this.buttons[this.buttons.length - 1].focus();
				}
				else {
					this.FormPanel.getForm().findField('EvnDirectionEco_Num').focus(true);
				}
			}.createDelegate(this),
			button2Callback: function(callback_data) {
				this.FormPanel.getForm().findField('PersonEvn_id').setValue(callback_data.PersonEvn_id);
				this.FormPanel.getForm().findField('Server_id').setValue(callback_data.Server_id);

				this.PersonInfo.load({ Person_id: callback_data.Person_id, Server_id: callback_data.Server_id });
			}.createDelegate(this),
			button2OnHide: function() {
				this.PersonInfo.button1OnHide();
			}.createDelegate(this),
			button3OnHide: function() {
				this.PersonInfo.button1OnHide();
			}.createDelegate(this),
			button4OnHide: function() {
				this.PersonInfo.button1OnHide();
			}.createDelegate(this),
			button5OnHide: function() {
				this.PersonInfo.button1OnHide();
			}.createDelegate(this),
			collapsible: true,
			collapsed: true,
			floatable: false,
			plugins: [ Ext.ux.PanelCollapsedTitle ],
			region: 'north',
			title: langs('Загрузка...'),
			titleCollapse: true
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'save16',
				onShiftTabAction: function() {
					var base_form = this.FormPanel.getForm();

					if ( this.action == 'view' ) {
						this.buttons[this.buttons.length - 1].focus(true);
					}
					else {
						base_form.findField('EvnDirectionEco_Comment').focus();
					}
				}.createDelegate(this),
				onTabAction: function() {
					if ( !this.buttons[1].hidden ) {
						this.buttons[1].focus(true);
					}
					else {
						this.buttons[this.buttons.length - 2].focus(true);
					}
				}.createDelegate(this),
				tabIndex: formTabIndex++,
				text: BTN_FRMSAVE
			}, {
				handler: function() {
					this.printEvnDirectionEco();
				}.createDelegate(this),
				iconCls: 'print16',
				onShiftTabAction: function() {
					if ( this.action != 'view' ) {
						this.buttons[0].focus(true);
					}
					else {
						this.buttons[this.buttons.length - 1].focus(true);
					}
				}.createDelegate(this),
				onTabAction: function() {
					this.buttons[this.buttons.length - 2].focus(true);
				}.createDelegate(this),
				tabIndex: formTabIndex++,
				text: BTN_FRMPRINT
			}, {
				text: '-'
			},
			HelpButton(this, -1),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				onShiftTabAction: function() {
					this.buttons[this.buttons.length - 2].focus(true);
				}.createDelegate(this),
				onTabAction: function() {
					if ( this.action != 'view' ) {
						this.FormPanel.getForm().findField('EvnDirectionEco_Num').focus(true);
					}
					else if ( !this.buttons[1].hidden ) {
						this.buttons[1].focus(true);
					}
					else {
						this.buttons[this.buttons.length - 2].focus(true);
					}
				}.createDelegate(this),
				tabIndex: formTabIndex++,
				text: BTN_FRMCANCEL
			}],
			items: [
				this.PersonInfo,
				this.FormPanel
			],
			layout: 'border'
		});

		sw.Promed.swEvnDirectionEcoEditWindow.superclass.initComponent.apply(this, arguments);
	}
});