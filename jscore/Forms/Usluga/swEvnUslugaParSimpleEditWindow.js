/**
* swEvnUslugaParSimpleEditWindow - простое окно редактирования/просмотра параклинической услуги
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Usluga
* @access       public
* @copyright    Copyright (c) 2014 Swan Ltd.
* @author       Dmitry Vlasenko
* @version      22.04.2014
* @comment      Префикс для id компонентов EUPSEF (EvnUslugaParSimpleEditForm)
*
*/

sw.Promed.swEvnUslugaParSimpleEditWindow = Ext.extend(sw.Promed.BaseForm, {
	action: null,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: false,
	closeAction: 'hide',
	collapsible: true,
	doSave: function(options) {
		// options @Object

		if ( this.formStatus == 'save' || this.action == 'view' ) {
			return false;
		}
		options = options||{};

		this.formStatus = 'save';

		var base_form = this.findById('EvnUslugaParSimpleEditForm').getForm();

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.formStatus = 'edit';
					this.findById('EvnUslugaParSimpleEditForm').getFirstInvalidEl().focus(false);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		if (!options.ignoreUnlinkWarning && this.EvnUslugaParLinked && Ext.isEmpty(base_form.findField('EvnUslugaPar_pid').getValue())) {
			this.formStatus = 'edit';
			sw.swMsg.show({
				buttons: Ext.Msg.YESNO,
				fn: function (buttonId, text, obj) {
					if (buttonId == 'yes') {
						options.ignoreUnlinkWarning = 1;
						this.doSave(options);
					}
				}.createDelegate(this),
				icon: Ext.MessageBox.QUESTION,
				msg: 'Данная услуга будет отвязана от случая лечения. Продолжить?',
				title: lang['prodoljit_sohranenie']
			});
			return false;
		}

		var evn_usluga_set_time = base_form.findField('EvnUslugaPar_setTime').getValue();
		var evn_usluga_oper_pid = base_form.findField('EvnUslugaPar_pid').getValue();

		// Убрал проверку, т.к. услугу можно отвязать от посещений/движений и в ТАП и в КВС.
		/*if ( base_form.findField('parentClass').getValue().inlist([ 'EvnPL', 'EvnPS', 'EvnVizit' ]) && !evn_usluga_oper_pid ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.formStatus = 'edit';
					base_form.findField('EvnUslugaPar_pid').focus(true, 250);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: lang['ne_vyibrano'] + ' ' + (base_form.findField('parentClass').getValue() == 'EvnPS' ? lang['otdelenie'] : lang['poseschenie']),
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}*/

		var params = new Object();
		var record = null;

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: LOAD_WAIT_SAVE});
		loadMask.show();

		if ( base_form.findField('EvnUslugaPar_pid').disabled ) {
			params.EvnUslugaPar_pid = evn_usluga_oper_pid;
		}
		params.ignoreParentEvnDateCheck = (!Ext.isEmpty(options.ignoreParentEvnDateCheck) && options.ignoreParentEvnDateCheck === 1) ? 1 : 0;
		params.ignoreKSGChangeCheck = (!Ext.isEmpty(options.ignoreKSGChangeCheck) && options.ignoreKSGChangeCheck === 1) ? 1 : 0;

		base_form.submit({
			failure: function(result_form, action) {
				this.formStatus = 'edit';
				loadMask.hide();

				if ( action.result ) {
					if (action.result.Alert_Msg) {
						sw.swMsg.show({
							buttons: Ext.Msg.YESNO,
							fn: function(buttonId, text, obj) {
								if ( buttonId == 'yes' ) {
									if (action.result.Error_Code == 109) {
										options.ignoreParentEvnDateCheck = 1;
									}
									if (action.result.Error_Code == 425) {
										options.ignoreKSGChangeCheck = 1;
									}

									this.doSave(options);
								}
							}.createDelegate(this),
							icon: Ext.MessageBox.QUESTION,
							msg: action.result.Alert_Msg,
							title: lang['prodoljit_sohranenie']
						});
					} else if ( action.result.Error_Msg ) {
						sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
					}
					else {
						sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_3]']);
					}
				}
			}.createDelegate(this),
			params: params,
			success: function(result_form, action) {
				this.formStatus = 'edit';
				loadMask.hide();

				if ( action.result && action.result.EvnUslugaPar_id > 0 ) {
					base_form.findField('EvnUslugaPar_id').setValue(action.result.EvnUslugaPar_id);

					if (action.result.Alert_Msg) {
						sw.swMsg.alert('Внмание', action.result.Alert_Msg);
					}

					this.callback();
					this.hide();
				}
				else {
					sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_2]']);
				}
			}.createDelegate(this)
		});
	},
	draggable: true,
	height: 250,
	id: 'EvnUslugaParSimpleEditWindow',
	initComponent: function() {
		var win=this;
		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'save16',
				onShiftTabAction: function () {
					this.buttons[this.buttons.length - 1].focus();
				}.createDelegate(this),
				onTabAction: function () {
					this.buttons[this.buttons.length - 1].focus();
				}.createDelegate(this),
				tabIndex: TABINDEX_EUPSEF + 25,
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
					if ( this.action != 'view' ) {
						this.buttons[0].focus();
					}
				}.createDelegate(this),
				onTabAction: function () {
					if ( this.action != 'view' ) {
						this.buttons[0].focus();
					}
				}.createDelegate(this),
				tabIndex: TABINDEX_EUPSEF + 26,
				text: BTN_FRMCANCEL
			}],
			items: [ new sw.Promed.PersonInformationPanelShort({
				id: 'EUPSEF_PersonInformationFrame',
				region: 'north'
			}),
			new Ext.form.FormPanel({
				autoScroll: true,
				bodyBorder: false,
				bodyStyle: 'padding: 5px 5px 0',
				border: false,
				frame: false,
				id: 'EvnUslugaParSimpleEditForm',
				labelAlign: 'right',
				labelWidth: 130,
				layout: 'form',
				reader: new Ext.data.JsonReader({
					success: Ext.emptyFn
				}, [
					{name: 'accessType'},
					{name: 'EvnUslugaPar_id'},
					{name: 'parentClass'},
					{name: 'EvnUslugaPar_pid'},
					{name: 'EvnUslugaPar_setDate'},
					{name: 'EvnUslugaPar_setTime'},
					{name: 'Person_id'},
					{name: 'PersonEvn_id'},
					{name: 'Server_id'},
					{name: 'UslugaComplex_id'}
				]),
				region: 'center',
				url: '/?c=EvnUsluga&m=saveEvnUslugaParSimple',
				items: [{
					name: 'accessType',
					value: '',
					xtype: 'hidden'
				}, {
					name: 'parentClass',
					value: '',
					xtype: 'hidden'
				}, {
					name: 'EvnUslugaPar_id',
					value: 0,
					xtype: 'hidden'
				}, {
					name: 'Person_id',
					value: 0,
					xtype: 'hidden'
				}, {
					name: 'PersonEvn_id',
					value: 0,
					xtype: 'hidden'
				}, {
					name: 'Server_id',
					value: 0,
					xtype: 'hidden'
				},
				new sw.Promed.Panel({
					autoHeight: true,
					bodyStyle: 'padding-top: 0.5em;',
					border: true,
					layout: 'form',
					style: 'margin-bottom: 0.5em;',
					title: lang['1_usluga'],
					items: [{
						allowBlank: false,
						fieldLabel: lang['usluga'],
						disabled: true,
						hiddenName: 'UslugaComplex_id',
						to: 'EvnUslugaPar',
						listWidth: 600,
						tabIndex: TABINDEX_EUPSEF + 0,
						width: 500,
						xtype: 'swuslugacomplexnewcombo'
					}, {
						allowBlank: true,
						displayField: 'Evn_Name',
						editable: false,
						enableKeyEvents: true,
						fieldLabel: lang['otdelenie_poseschenie'],
						hiddenName: 'EvnUslugaPar_pid',
						listeners: {
							'change': function(combo, newValue, oldValue) {
								var index = combo.getStore().findBy(function(rec) {
									return (rec.get('Evn_id') == newValue);
								});
								combo.fireEvent('select', combo, combo.getStore().getAt(index), index);
							},
							'select': function(combo, record, index) {
								var base_form = this.findById('EvnUslugaParSimpleEditForm').getForm();

								if ( typeof record == 'object' && !Ext.isEmpty(record.get('Evn_id')) ) {
									base_form.findField('EvnUslugaPar_setDate').setMinValue(typeof record.get('Evn_setDate') == 'object' ? Ext.util.Format.date(record.get('Evn_setDate'), 'd.m.Y') : record.get('Evn_setDate'));
									if (!Ext.isEmpty(record.get('Evn_disDate'))) {
										base_form.findField('EvnUslugaPar_setDate').setMaxValue(typeof record.get('Evn_disDate') == 'object' ? Ext.util.Format.date(record.get('Evn_disDate'), 'd.m.Y') : record.get('Evn_disDate'));
									} else {
										base_form.findField('EvnUslugaPar_setDate').setMaxValue(getGlobalOptions().date);
									}

									if ( Ext.isEmpty(base_form.findField('EvnUslugaPar_setDate').getValue()) ) {
										base_form.findField('EvnUslugaPar_setDate').setValue(record.get('Evn_setDate'));
									}
								}
								else {
									base_form.findField('EvnUslugaPar_setDate').setMinValue(undefined);
									base_form.findField('EvnUslugaPar_setDate').setMaxValue(getGlobalOptions().date);
								}
							}.createDelegate(this)
						},
						listWidth: 600,
						mode: 'local',
						store: new Ext.data.JsonStore({
							autoLoad: false,
							url: '/?c=EvnUslugaPar&m=loadEvnUslugaParPidCombo',
							fields: [
								{ name: 'Evn_id', type: 'int' },
								{ name: 'Evn_Name', type: 'string' },
								{ name: 'Evn_setDate', type: 'date', dateFormat: 'd.m.Y' },
								{ name: 'Evn_disDate', type: 'date', dateFormat: 'd.m.Y' }
							],
							id: 'Evn_id'
						}),
						tabIndex: TABINDEX_EUCOMEF + 1,
						tpl: new Ext.XTemplate('<tpl for="."><div class="x-combo-list-item">', '{Evn_Name}&nbsp;', '</div></tpl>'),
						triggerAction: 'all',
						valueField: 'Evn_id',
						width: 500,
						xtype: 'combo'
					}, {
						border: false,
						layout: 'column',
						items: [{
							border: false,
							layout: 'form',
							items: [{
								allowBlank: false,
								fieldLabel: lang['data_vyipolneniya'],
								format: 'd.m.Y',
								id: 'EUPSEF_EvnUslugaPar_setDate',
								listeners: {
									'change': function(field, newValue, oldValue) {
										if (blockedDateAfterPersonDeath('personpanelid', 'EUPSEF_PersonInformationFrame', field, newValue, oldValue)) return;

										/*
										var base_form = this.findById('EvnUslugaParSimpleEditForm').getForm();
										var evn_combo = base_form.findField('EvnUslugaPar_pid');

										if ( newValue < evn_combo.getStore().getAt(0).get('Evn_setDate') ) {
											sw.swMsg.alert(lang['oshibka'], lang['data_vyipolneniya_uslugi_ne_mojet_byit_menshe_datyi_nachala_lecheniya']);
											base_form.findField('EvnUslugaPar_setDate').setValue(evn_combo.getStore().getAt(0).get('Evn_setDate'));
											base_form.findField('EvnUslugaPar_setDate').focus(true, 100);
											return false;
										}

										// Тут надо бы загружать список услуг на дату, но пока непонятно, как организовать фильтры, кроме даты
										var UslugaComplex_id = base_form.findField('UslugaComplex_id').getValue();

										// Устанавливаем фильтр по дате для услуг
										base_form.findField('UslugaComplex_id').clearValue();
										base_form.findField('UslugaComplex_id').getStore().baseParams.LpuSection_id = null;
										base_form.findField('UslugaComplex_id').getStore().baseParams.UslugaComplex_Date = (typeof newValue == 'object' ? Ext.util.Format.date(newValue, 'd.m.Y') : newValue);
										base_form.findField('UslugaComplex_id').getStore().removeAll();
										*/
									}.createDelegate(this),
									'keydown': function (inp, e) {
										if ( e.shiftKey == true && e.getKey() == Ext.EventObject.TAB && this.findById('EvnUslugaParSimpleEditForm').getForm().findField('EvnUslugaPar_pid').disabled ) {
											e.stopEvent();
											this.buttons[this.buttons.length - 1].focus();
										}
									}.createDelegate(this)
								},
								name: 'EvnUslugaPar_setDate',
								plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
								tabIndex: TABINDEX_EUPSEF + 2,
								width: 100,
								xtype: 'swdatefield'
							}]
						}, {
							border: false,
							layout: 'form',
							items: [{
								fieldLabel: lang['vremya'],
								listeners: {
									'keydown': function (inp, e) {
										if ( e.getKey() == Ext.EventObject.F4 ) {
											e.stopEvent();
											inp.onTriggerClick();
										}
									},
									'change':function(){
										var base_form = win.findById('EvnUslugaParSimpleEditForm').getForm();
										var time_field = base_form.findField('EvnUslugaPar_setTime');

										setCurrentDateTime({
											dateField: base_form.findField('EvnUslugaPar_setDate'),
											loadMask: true,
											setDate: false,
											setTimeMaxValue: false,
											setDateMaxValue: false,
											setDateMinValue: false,
											setTime: false,
											timeField: time_field,
											windowId: 'EvnUslugaParSimpleEditWindow'
										});
									}
								},
								name: 'EvnUslugaPar_setTime',
								onTriggerClick: function() {
									var base_form = this.findById('EvnUslugaParSimpleEditForm').getForm();
									var time_field = base_form.findField('EvnUslugaPar_setTime');

									if ( time_field.disabled ) {
										return false;
									}

									setCurrentDateTime({
										dateField: base_form.findField('EvnUslugaPar_setDate'),
										loadMask: true,
										setDate: true,
										setTimeMaxValue: false,
										setDateMaxValue: false,
										setDateMinValue: false,
										setTime: true,
										timeField: time_field,
										windowId: 'EvnUslugaParSimpleEditWindow'
									});
								}.createDelegate(this),
								plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
								tabIndex: TABINDEX_EUPSEF + 3,
								validateOnBlur: false,
								width: 60,
								xtype: 'swtimefield'
							}]
						}]
					}]
				})]
			})]
		});

		sw.Promed.swEvnUslugaParSimpleEditWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('EvnUslugaParSimpleEditWindow');

			switch ( e.getKey() ) {
				case Ext.EventObject.C:
					current_window.doSave();
				break;

				case Ext.EventObject.J:
					current_window.hide();
				break;
			}
		}.createDelegate(this),
		key: [
			Ext.EventObject.C,
			Ext.EventObject.J
		],
		stopEvent: true
	}],
	layout: 'border',
	listeners: {
		'hide': function(win) {
			win.onHide();
		}
	},
	maximizable: true,
	modal: true,
	onHide: Ext.emptyFn,
	plain: true,
	resizable: true,
	show: function() {
		sw.Promed.swEvnUslugaParSimpleEditWindow.superclass.show.apply(this, arguments);

		var win = this;

		this.restore();
		this.center();

		this.EvnUslugaParLinked = false;

		var base_form = this.findById('EvnUslugaParSimpleEditForm').getForm();
		base_form.reset();

		base_form.findField('UslugaComplex_id').getStore().baseParams.LpuSection_id = null;
		base_form.findField('UslugaComplex_id').getStore().baseParams.UslugaComplex_Date = null;
		base_form.findField('UslugaComplex_id').lastQuery = '';

		this.action = null;
		this.callback = Ext.emptyFn;
		this.onHide = Ext.emptyFn;

		base_form.findField('EvnUslugaPar_pid').getStore().removeAll();

		if ( !arguments[0] || !arguments[0].formParams ) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() {this.hide();}.createDelegate(this) );
			return false;
		}

		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		}

		if ( arguments[0].callback ) {
			this.callback = arguments[0].callback;
		}

		if ( arguments[0].onHide ) {
			this.onHide = arguments[0].onHide;
		}

		base_form.setValues(arguments[0].formParams);

		var index;

		var evn_combo = base_form.findField('EvnUslugaPar_pid');
		var usluga_combo = base_form.findField('UslugaComplex_id');

		base_form.findField('EvnUslugaPar_setDate').setMinValue(undefined);

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: LOAD_WAIT});
		loadMask.show();

		var evn_usluga_oper_pid = null;

		switch ( this.action ) {
			case 'edit':
			case 'view':
				var evn_usluga_oper_id = base_form.findField('EvnUslugaPar_id').getValue();

				if ( !evn_usluga_oper_id ) {
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
						'EvnUslugaPar_id': evn_usluga_oper_id,
						archiveRecord: win.archiveRecord
					},
					success: function(result_form, action) {
						// В зависимости от accessType переопределяем this.action
						if ( base_form.findField('accessType').getValue() == 'view' ) {
							this.action = 'view';
						}

						if (!Ext.isEmpty(base_form.findField('EvnUslugaPar_pid').getValue())) {
							this.EvnUslugaParLinked = true;
						}

						this.findById('EUPSEF_PersonInformationFrame').load({
							Person_id: base_form.findField('Person_id').getValue(),
							callback: function() {
								var field = base_form.findField('EvnUslugaPar_setDate');
								clearDateAfterPersonDeath('personpanelid', 'EUPSEF_PersonInformationFrame', field);
							}
						});

						if ( this.action == 'edit' ) {
							this.setTitle(WND_POL_EUPAREDIT);
							this.enableEdit(true);
						}
						else {
							this.setTitle(WND_POL_EUPARVIEW);
							this.enableEdit(false);
						}

						if ( this.action == 'edit' ) {
							setCurrentDateTime({
								dateField: base_form.findField('EvnUslugaPar_setDate'),
								loadMask: false,
								setDate: false,
								setTimeMaxValue: false,
								setDateMaxValue: false,
								windowId: this.id,
								timeField: base_form.findField('EvnUslugaPar_setTime')
							});
						}

						if ( base_form.findField('parentClass').getValue() == 'EvnVizitPL' || base_form.findField('parentClass').getValue() == 'EvnVizitPLStom' || base_form.findField('parentClass').getValue() == 'EvnPL' ||  base_form.findField('parentClass').getValue() == 'EvnPLStom' ) {
							base_form.findField('EvnUslugaPar_pid').setFieldLabel(lang['poseschenie']);
						}
						else {
							base_form.findField('EvnUslugaPar_pid').setFieldLabel(lang['dvijenie']);
						}

						if (action.response && action.response.responseText) {
							var response = Ext.util.JSON.decode(action.response.responseText);
							if (response[0] && response[0].parentEvnComboData) {
								// загрузим список движений/посещений с сервера
								base_form.findField('EvnUslugaPar_pid').getStore().loadData(response[0].parentEvnComboData);
							}
						}

						var evn_usluga_oper_pid = evn_combo.getValue();
						var record;
						var usluga_complex_id = usluga_combo.getValue();

						var index = evn_combo.getStore().findBy(function (rec) {
							if (rec.get('Evn_id') == evn_usluga_oper_pid) {
								return true;
							}
							else {
								return false;
							}
						});

						record = evn_combo.getStore().getAt(index);

						if (record) {
							evn_combo.setValue(evn_usluga_oper_pid);
							evn_combo.fireEvent('change', evn_combo, evn_usluga_oper_pid);
						} else {
							evn_combo.clearValue();
						}

						if ( !Ext.isEmpty(usluga_complex_id) ) {
							usluga_combo.getStore().load({
								callback: function() {
									if ( usluga_combo.getStore().getCount() > 0 ) {
										usluga_combo.setValue(usluga_complex_id);
									}
									else {
										usluga_combo.clearValue();
									}
								}.createDelegate(this),
								params: {
									UslugaComplex_id: usluga_complex_id
								}
							});
						}

						if ( this.action == 'edit' ) {
							evn_combo.focus(true, 250);
						}
						else {
							this.buttons[this.buttons.length - 1].focus();
						}
						loadMask.hide();
					}.createDelegate(this),
					url: '/?c=EvnUsluga&m=loadEvnUslugaParSimpleEditForm'
				});
			break;

			default:
				loadMask.hide();
				this.hide();
			break;
		}
	},
	width: 700
});
