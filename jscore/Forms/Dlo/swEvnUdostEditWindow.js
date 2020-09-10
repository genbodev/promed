/**
* swEvnUdostEditWindow - окно редактирования/добавления удостоверения льготника.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      DLO
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage1981@gmail.com)
* @version      0.003-15.07.2009
* @comment      Префикс для id компонентов EUEF (EvnUdostEditForm)
*               tabIndex: 201
*
*
* @input data: action - действие (add, edit, view)
*              EvnUdost_id - ID удостоверения для редактирования или просмотра
*              Person_id - ID человека
*              PersonEvn_id - ?
*              PrivilegeType_id - тип льготы (при добавлении)
*              Server_id - ?
*/

sw.Promed.swEvnUdostEditWindow = Ext.extend(sw.Promed.BaseForm, {
	action: null,
	autoHeight: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	doSave: function(print) {
		var current_window = this;
		var form = current_window.findById('EvnUdostEditForm');
		var person_information = current_window.findById('EUEF_PersonInformationFrame');
/*
		if ( !person_information.getFieldValue('Person_Snils') ) {
			sw.swMsg.alert(lang['oshibka'], lang['sohranenie_nevozmojno_[ne_zapolnen_snils]']);
			return false;
		}
*/
		if ( !form.getForm().isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					form.getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var loadMask = new Ext.LoadMask(current_window.getEl(), { msg: "Подождите, идет сохранение..." });
		loadMask.show();

		form.getForm().submit({
			failure: function(result_form, action) {
				loadMask.hide();

				if ( action.result ) {
					if ( action.result.Error_Msg ) {
						sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
					}
					else {
						sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_3]']);
					}
				}
			},
			success: function(result_form, action) {
				loadMask.hide();

				if ( action.result ) {
					if ( action.result.EvnUdost_id > 0 ) {
						current_window.action = 'edit';
						current_window.setTitle(WND_DLO_UDOSTADD);

						var evn_udost_id = action.result.EvnUdost_id;
						var privilege_type_code = null;
						var response = new Object();
						var server_id = form.findById('EUEF_Server_id').getValue();

						form.findById('EUEF_EvnUdost_id').setValue(evn_udost_id);

						var privilege_type_record = form.findById('EUEF_PrivilegeTypeCombo').getStore().getById(form.findById('EUEF_PrivilegeTypeCombo').getValue());
						if ( privilege_type_record ) {
							privilege_type_code = privilege_type_record.get('PrivilegeType_Code');
						}

						response.EvnUdost_disDate = form.findById('EUEF_EvnUdost_disDate').getValue();
						response.EvnUdost_id = evn_udost_id;
						response.EvnUdost_Num = form.findById('EUEF_EvnUdost_Num').getValue();
						response.EvnUdost_Ser = form.findById('EUEF_EvnUdost_Ser').getValue();
						response.EvnUdost_setDate = form.findById('EUEF_EvnUdost_setDate').getValue();
						response.Person_Birthday = person_information.getFieldValue('Person_Birthday');
						response.Person_Firname = person_information.getFieldValue('Person_Firname');
						response.Person_id = form.findById('EUEF_Person_id').getValue();
						response.Person_Secname = person_information.getFieldValue('Person_Secname');
						response.Person_Surname = person_information.getFieldValue('Person_Surname');
						response.PersonEvn_id = form.findById('EUEF_PersonEvn_id').getValue();
						response.Privilege_Refuse = '';
						response.PrivilegeType_Code = privilege_type_code;
						response.Server_id = server_id;

						current_window.callback({ EvnUdostData: response });

						if ( print ) {
							window.open(C_EVNUDOST_PRINT + '&EvnUdost_id=' + evn_udost_id, '_blank');
						}
						else {
							current_window.hide();
						}
					}
					else {
						if ( action.result.Error_Msg ) {
							sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
						}
						else {
							sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_1]']);
						}
					}
				}
				else {
					sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_2]']);
				}
			}
		});
	},
	enableEdit: function(enable) {
		var form = this.findById('EvnUdostEditForm');

		if ( enable ) {
			form.findById('EUEF_EvnUdost_setDate').enable();
			form.findById('EUEF_EvnUdost_disDate').enable();
			form.findById('EUEF_EvnUdost_Ser').enable();
			form.findById('EUEF_EvnUdost_Num').enable();
			form.findById('EUEF_PrivilegeTypeCombo').enable();
			this.buttons[0].enable();
		}
		else {
			form.findById('EUEF_EvnUdost_disDate').disable();
			form.findById('EUEF_EvnUdost_Num').disable();
			form.findById('EUEF_EvnUdost_Ser').disable();
			form.findById('EUEF_EvnUdost_setDate').disable();
			form.findById('EUEF_PrivilegeTypeCombo').disable();
			this.buttons[0].disable();
		}
	},
	id: 'EvnUdostEditWindow',
	initComponent: function() {
		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.ownerCt.doSave(false);
				},
				iconCls: 'save16',
				tabIndex: 204,
				text: BTN_FRMSAVE
			}, {
				handler: function() {
					this.ownerCt.printUdost();
				},
				iconCls: 'print16',
				tabIndex: 205,
				text: '<u>П</u>ечать'
			}, {
				text: '-'
			},
			HelpButton(Ext.getCmp('EvnUdostEditWindow'), 206),
			{
				handler: function() {
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				tabIndex: 207,
				text: BTN_FRMCANCEL
			}],
			items: [ new sw.Promed.PersonInformationPanel({
				button2Callback: function(callback_data) {
					var current_window = Ext.getCmp('EvnUdostEditWindow');

					current_window.findById('EUEF_PersonEvn_id').setValue(callback_data.PersonEvn_id);
					current_window.findById('EUEF_Server_id').setValue(callback_data.Server_id);
					current_window.findById('EUEF_PersonInformationFrame').load({ Person_id: callback_data.Person_id, Server_id: callback_data.Server_id });
				},
				button2OnHide: function() {
					var current_window = Ext.getCmp('EvnUdostEditWindow');

					if ( current_window.action == 'view' ) {
						current_window.buttons[1].focus();
					}
					else {
						current_window.findById('EUEF_EvnUdost_setDate').focus(true);
					}
				},
				button3OnHide: function() {
					var current_window = Ext.getCmp('EvnUdostEditWindow');

					if ( current_window.action == 'view' ) {
						current_window.buttons[1].focus();
					}
					else {
						current_window.findById('EUEF_EvnUdost_setDate').focus(true);
					}
				},
				button4OnHide: function() {
					var current_window = Ext.getCmp('EvnUdostEditWindow');

					current_window.findById('EUEF_PrivilegeTypeCombo').getStore().reload();
					current_window.findById('EUEF_PrivilegeTypeCombo').focus(false);
				},
				id: 'EUEF_PersonInformationFrame'
			}),
			new Ext.form.FormPanel({
				autoHeight: true,
				bodyBorder: false,
				bodyStyle: 'padding: 5px 5px 0',
				border: false,
				buttonAlign: 'left',
				frame: false,
				id: 'EvnUdostEditForm',
				labelAlign: 'right',
				labelWidth: 150,
				items: [{
					id: 'EUEF_EvnUdost_id',
					name: 'EvnUdost_id',
					value: 0,
					xtype: 'hidden'
				}, {
					id: 'EUEF_Person_id',
					name: 'Person_id',
					value: 0,
					xtype: 'hidden'
				}, {
					id: 'EUEF_PersonEvn_id',
					name: 'PersonEvn_id',
					value: 0,
					xtype: 'hidden'
				}, {
					xtype: 'hidden',
					id: 'EUEF_Server_id',
					name: 'Server_id',
					value: 0
				}, {
					allowBlank: false,
					fieldLabel: lang['data_vyidachi'],
					format: 'd.m.Y',
					id: 'EUEF_EvnUdost_setDate',
					listeners: {
						'keydown': function (inp, e) {
							if ( e.shiftKey == false && e.getKey() == Ext.EventObject.TAB ) {
								e.stopEvent();
								inp.ownerCt.findById('EUEF_EvnUdost_disDate').focus(true);
							}
						},
						'change': function(field, newValue, oldValue) {
							blockedDateAfterPersonDeath('personpanelid', 'EUEF_PersonInformationFrame', field, newValue, oldValue);
						}
					},
					name: 'EvnUdost_setDate',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
					tabIndex: 208,
					validateOnBlur: true,
					width: 150,
					xtype: 'swdatefield'
				}, {
					fieldLabel: lang['data_zakryitiya'],
					format: 'd.m.Y',
					id: 'EUEF_EvnUdost_disDate',
					listeners: {
						'keydown': function (inp, e) {
							if ( e.shiftKey == true && e.getKey() == Ext.EventObject.TAB ) {
								e.stopEvent();
								inp.ownerCt.findById('EUEF_EvnUdost_setDate').focus(true);
							}
						}
					},
					name: 'EvnUdost_disDate',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
					tabIndex: 201,
					validateOnBlur: true,
					width: 150,
					xtype: 'swdatefield'
				}, {
					allowBlank: false,
					codeField: 'PrivilegeType_Code',
					displayField: 'PrivilegeType_Name',
					editable: false,
					fieldLabel: lang['kategoriya'],
					hiddenName: 'PrivilegeType_id',
					id: 'EUEF_PrivilegeTypeCombo',
					loadingText: lang['idet_zagruzka'],
					store: new Ext.data.Store({
						autoLoad: false,
						reader: new Ext.data.JsonReader({
							id: 'PrivilegeType_id'
						}, [
							{ name: 'PrivilegeType_Code', mapping: 'PrivilegeType_Code' },
							{ name: 'PrivilegeType_id', mapping: 'PrivilegeType_id' },
							{ name: 'PrivilegeType_Name', mapping: 'PrivilegeType_Name' },
							{ name: 'ReceptDiscount_id', mapping: 'ReceptDiscount_id' },
							{ name: 'ReceptFinance_id', mapping: 'ReceptFinance_id' },
							{ name: 'PersonPrivilege_IsClosed', mapping: 'PersonPrivilege_IsClosed' }
						]),
						url: C_PRIVCAT_LOAD_LIST
					}),
					tabIndex: 202,
					tpl: new Ext.XTemplate(
						'<tpl for="."><div class="x-combo-list-item">',
						'<table style="border: 0;"><tr><td style="width: 25px;"><font color="red">{PrivilegeType_Code}</font></td><td style="font-weight: {[ values.PersonPrivilege_IsClosed == 1 ? "bold" : "normal; color: red;" ]};">{PrivilegeType_Name}{[ values.PersonPrivilege_IsClosed == 1 ? "&nbsp;" : " (закрыта)" ]}</td></tr></table>',
						'</div></tpl>'
					),
					validateOnBlur: true,
					valueField: 'PrivilegeType_id',
					width: 400,
					xtype: 'swbaselocalcombo'
				}, {
					allowBlank: false,
					autoCreate: {
						tag: 'input',
						type: 'text',
						maxLength: '7',
						readonly: 'readonly'
					},
					fieldLabel: lang['seriya'],
					id: 'EUEF_EvnUdost_Ser',
					name: 'EvnUdost_Ser',
					validateOnBlur: true,
					width: 150,
					xtype: 'textfieldpmw'
				}, {
					allowBlank: false,
					autoCreate: {
						maxLength: '6',
						tag: 'input',
						type: 'text'
					},
					fieldLabel: lang['nomer'],
					id: 'EUEF_EvnUdost_Num',
					maskRe: new RegExp("^[0-9]*$"),
					minLength: 6,
					minLengthText: lang['dlina_polya_doljna_sostavlyat_6_simvolov'],
					name: 'EvnUdost_Num',
					tabIndex: 203,
					validateOnBlur: true,
					width: 150,
					xtype: 'textfield'
				}],
				keys: [{
					fn: function(inp, e) {
						e.stopEvent();

						if ( e.browserEvent.stopPropagation ) {
							e.browserEvent.stopPropagation();
						}
						else {
							e.browserEvent.cancelBubble = true;
						}

						if ( e.browserEvent.preventDefault ) {
							e.browserEvent.preventDefault();
						}
						else {
							e.browserEvent.returnValue = false;
						}

						e.browserEvent.returnValue = false;
						e.returnValue = false;

						if ( Ext.isIE ) {
							e.browserEvent.keyCode = 0;
							e.browserEvent.which = 0;
						}

						switch ( e.getKey() ) {
							case Ext.EventObject.F6:
								this.findById('EUEF_PersonInformationFrame').panelButtonClick(1);
							break;

							case Ext.EventObject.F10:
								this.findById('EUEF_PersonInformationFrame').panelButtonClick(2);
							break;

							case Ext.EventObject.F11:
								this.findById('EUEF_PersonInformationFrame').panelButtonClick(3);
							break;

							case Ext.EventObject.F12:
								if ( e.ctrlKey == true ) {
									this.findById('EUEF_PersonInformationFrame').panelButtonClick(5);
								}
								else {
									this.findById('EUEF_PersonInformationFrame').panelButtonClick(4);
								}
							break;
						}
					},
					key: [
						Ext.EventObject.F6,
						Ext.EventObject.F10,
						Ext.EventObject.F11,
						Ext.EventObject.F12
					],
					scope: this,
					stopEvent: true
				}, {
					alt: true,
					fn: function(inp, e) {
						switch ( e.getKey() ) {
							case Ext.EventObject.C:
								if ( this.action != 'view' ) {
									this.doSave(false);
								}
							break;

							case Ext.EventObject.G:
								this.printUdost();
							break;

							case Ext.EventObject.J:
								this.hide();
							break;
						}
					},
					key: [
						Ext.EventObject.C,
						Ext.EventObject.G,
						Ext.EventObject.J
					],
					scope: this,
					stopEvent: true
				}],
				reader: new Ext.data.JsonReader({
					success: function() { }
				}, [
					{ name: 'EvnUdost_setDate' },
					{ name: 'EvnUdost_disDate' },
					{ name: 'EvnUdost_Ser' },
					{ name: 'EvnUdost_Num' },
					{ name: 'Lpu_id' },
					{ name: 'PersonEvn_id' },
					{ name: 'Server_id' },
					{ name: 'PrivilegeType_id' }
				]),
				url: '/?c=EvnUdost&m=saveEvnUdost'
			})]
		});
		sw.Promed.swEvnUdostEditWindow.superclass.initComponent.apply(this, arguments);
	},
	layout: 'form',
	listeners: {
		'hide': function() {
			this.onHide();
		}
	},
	modal: true,
	onHide: Ext.emptyFn,
	params: null,
	plain: true,
	printUdost: function() {
		if ( (this.action == 'add') || (this.action == 'edit') ) {
			this.doSave(true);
		}
		else if ( this.action == 'view' ) {
			var evn_udost_id = this.findById('EvnUdostEditForm').findById('EUEF_EvnUdost_id').getValue();
			var server_id = this.findById('EvnUdostEditForm').findById('EUEF_Server_id').getValue();

			window.open(C_EVNUDOST_PRINT + '&EvnUdost_id=' + evn_udost_id, '_blank');
		}
	},
	resizable: false,
	show: function() {
		sw.Promed.swEvnUdostEditWindow.superclass.show.apply(this, arguments);

		var current_window = this;
		var form = current_window.findById('EvnUdostEditForm');
		form.getForm().reset();

		current_window.callback = Ext.emptyFn;
		current_window.onHide = Ext.emptyFn;

		current_window.findById('EUEF_PrivilegeTypeCombo').getStore().removeAll();
		current_window.findById('EUEF_PrivilegeTypeCombo').clearValue();

		if ( !arguments[0] ) {
			current_window.showMessage(lang['soobschenie'], lang['nevernyie_parametryi']);
			return false;
		}

		form.getForm().setValues(arguments[0]);

		if ( arguments[0].action ) {
			current_window.action = arguments[0].action;
		}

		if ( arguments[0].callback ) {
			current_window.callback = arguments[0].callback;
		}

		if ( arguments[0].onHide ) {
			current_window.onHide = arguments[0].onHide;
		}

		var evn_udost_id = current_window.findById('EUEF_EvnUdost_id').getValue();
		var person_id = current_window.findById('EUEF_Person_id').getValue();
		var person_evn_id = current_window.findById('EUEF_PersonEvn_id').getValue();
		var privilege_type_id = current_window.findById('EUEF_PrivilegeTypeCombo').getValue();
		var server_id = current_window.findById('EUEF_Server_id').getValue();

		var loadMask = new Ext.LoadMask(current_window.getEl(), { msg: LOAD_WAIT });
		loadMask.show();

		current_window.findById('EUEF_PersonInformationFrame').load({
			Person_id: person_id,
			Server_id: server_id
		});
		current_window.findById('EUEF_PersonInformationFrame').setDisabled(false);
		switch ( current_window.action ) {
			case 'add':
				current_window.setTitle(WND_DLO_UDOSTADD);
				current_window.enableEdit(true);

				current_window.findById('EUEF_PrivilegeTypeCombo').getStore().load({
					callback: function(records, options, success) {
						current_window.findById('EUEF_PrivilegeTypeCombo').setValue(privilege_type_id);

						var lpu_id = Ext.globalOptions.globals.lpu_id;

						var lpu_store = new Ext.db.AdapterStore({
							autoLoad: false,
							dbFile: 'Promed.db',
							fields: [
								{ name: 'Lpu_id', type: 'int' },
								{ name: 'Lpu_Ouz', type: 'int' }
							],
							key: 'Lpu_id',
							tableName: 'Lpu'
						});

						lpu_store.load({
							callback: function(records, options, success) {
								var evn_udost_ser = '';

								for ( var i = 0; i < records.length; i++ ) {
									if ( records[i].get('Lpu_id') == lpu_id ) {
										evn_udost_ser = records[i].get('Lpu_Ouz');
									}
								}

								current_window.findById('EUEF_EvnUdost_Ser').setValue(evn_udost_ser);

								loadMask.hide();

								form.getForm().clearInvalid();
							}
						});
					},
					params: {
						Person_id: person_id,
						Server_id: server_id
					}
				});

				current_window.findById('EUEF_EvnUdost_setDate').focus(true, 500);
			break;

			case 'edit':
				current_window.setTitle(WND_DLO_UDOSTEDIT);
				current_window.enableEdit(true);

				form.findById('EUEF_PrivilegeTypeCombo').getStore().load({
					callback: function() {
						form.getForm().load({
							failure: function() {
								loadMask.hide();
								current_window.findById('EUEF_EvnUdost_setDate').focus(true, 250);
							},
							params: {
								EvnUdost_id: evn_udost_id
							},
							success: function() {
								current_window.findById('EUEF_EvnUdost_setDate').focus(true, 250);
								loadMask.hide();
							},
							url: '/?c=EvnUdost&m=loadEvnUdostEditForm'
						});
					},
					params: {
						mode: 'all',
						Person_id: person_id,
						Server_id: server_id
					}
				});
			break;

			case 'view':
				current_window.setTitle(WND_DLO_UDOSTVIEW);
				current_window.enableEdit(false);

				current_window.findById('EUEF_PrivilegeTypeCombo').getStore().load({
					callback: function() {
						form.getForm().load({
							failure: function() {
								loadMask.hide();
							},
							params: {
								EvnUdost_id: evn_udost_id
							},
							success: function() {
								current_window.buttons[1].focus();
								loadMask.hide();
								current_window.findById('EUEF_PersonInformationFrame').setDisabled(true);
							},
							url: '/?c=EvnUdost&m=loadEvnUdostEditForm'
						});
					},
					params: {
						mode: 'all',
						Person_id: person_id,
						Server_id: server_id
					}
				});
			break;
		}
	},
	width: 700
});