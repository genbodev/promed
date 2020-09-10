/**
/**
 * swRegistryEditWindow - окно редактирования/добавления реестра (счета).
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * https://rtmis.ru
 *
 *
 * @package      Admin
 * @region       Yaroslavl
 * @access       public
 * @copyright    Copyright (c) 2020 RT MIS Ltd.
 * @author       Stanislav Bykov
 * @version      29.04.2020
 * @comment      Префикс для id компонентов REF (RegistryEditForm)
 *
 *
 * @input data: action - действие (add, edit, view)
 *              Registry_id - ID реестра
 */

sw.Promed.swRegistryEditWindow = Ext.extend(sw.Promed.BaseForm, {
	/* свойства */
	action: null,
	autoHeight: true,
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	draggable: true,
	firstTabIndex: 15150,
	id: 'RegistryEditWindow',
	layout: 'form',
	modal: true,
	plain: true,
	resizable: false,
	split: true,
	width: 600,

	/* методы */
	callback: Ext.emptyFn,
	doSave: function(options) {
		let
			base_form = this.RegistryForm.getForm(),
			win = this;

		if ( typeof options != 'object' ) {
			options = {};
		}

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					win.RegistryForm.getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		let
			//accDate = base_form.findField('Registry_accDate').getValue(),
			begDate = base_form.findField('Registry_begDate').getValue(),
			endDate = base_form.findField('Registry_endDate').getValue();

		/*if ( accDate < endDate ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					base_form.findField('Registry_accDate').focus(false);
				},
				icon: Ext.Msg.ERROR,
				msg: 'Дата счета не может быть меньше даты окончания периода.',
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}*/

		if ( typeof begDate == 'object' && typeof endDate == 'object' ) {
			if ( begDate > endDate ) {
				sw.swMsg.show({
					buttons: Ext.Msg.OK,
					fn: function() {
						base_form.findField('Registry_begDate').focus(false);
					},
					icon: Ext.Msg.ERROR,
					msg: 'Дата окончания не может быть меньше даты начала.',
					title: ERR_INVFIELDS_TIT
				});
				return false;
			}

			if (!options.ignoreNotEqualPeriods && begDate.format('Ym') != endDate.format('Ym')) {
				sw.swMsg.show({
					buttons: Ext.Msg.YESNO,
					fn: function(buttonId, text, obj) {
						if ( buttonId == 'yes' ) {
							options.ignoreNotEqualPeriods = true;
							win.doSave(options);
						}
					},
					icon: Ext.MessageBox.QUESTION,
					msg: langs('Рекомендуется формировать реестры за один календарный месяц. Продолжить формирование?'),
					title: langs('Вопрос')
				});
				return false;
			}
		}

		win.doSubmit();

		return true;
	},
	doSubmit: function() {
		let
			base_form = this.RegistryForm.getForm(),
			loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет формирование реестра..."}),
			win = this;

		loadMask.show();

		let params = {
			RegistryType_id: base_form.findField('RegistryType_id').getValue()
		};

		base_form.submit({
			failure: function(result_form, action) {
				loadMask.hide();
			},
			params: params,
			success: function(result_form, action) {
				loadMask.hide();

				if ( action.result ) {
					if ( action.result.RegistryQueue_id ) {
						let records = {
							RegistryQueue_id: action.result.RegistryQueue_id,
							RegistryQueue_Position: action.result.RegistryQueue_Position
						};

						win.callback(win.owner, action.result.RegistryQueue_id, records);
					}
					else {
						sw.swMsg.show({
							buttons: Ext.Msg.OK,
							fn: function() {
								//win.hide();
							},
							icon: Ext.Msg.ERROR,
							msg: langs('При выполнении операции сохранения произошла ошибка') + '.<br/>' + langs('Пожалуйста, повторите попытку позже') + '.',
							title: 'Ошибка'
						});
					}
				}
			}
		});
	},
	enableEdit: function(enable) {
		let base_form = this.RegistryForm.getForm();

		if ( enable ) {
			base_form.findField('DispClass_id').enable();
			base_form.findField('KatNasel_id').enable();
			base_form.findField('OrgSMO_id').enable();
			base_form.findField('PayType_id').enable();
			base_form.findField('Registry_accDate').enable();
			base_form.findField('Registry_begDate').enable();
			base_form.findField('Registry_endDate').enable();
			base_form.findField('Registry_Num').enable();

			this.buttons[0].show();
		}
		else  {
			base_form.findField('DispClass_id').disable();
			base_form.findField('KatNasel_id').disable();
			base_form.findField('OrgSMO_id').disable();
			base_form.findField('PayType_id').disable();
			base_form.findField('Registry_accDate').disable();
			base_form.findField('Registry_begDate').disable();
			base_form.findField('Registry_endDate').disable();
			base_form.findField('Registry_Num').disable();

			this.buttons[0].hide();
		}
	},
	filterOrgSMOCombo: function() {
		let OrgSMOCombo = this.RegistryForm.getForm().findField('OrgSMO_id');

		OrgSMOCombo.getStore().clearFilter();
		OrgSMOCombo.getStore().filterBy(function(rec) {
			return (Ext.isEmpty(rec.get('OrgSMO_endDate')) && rec.get('KLRgn_id') == 76 && !Ext.isEmpty(rec.get('Orgsmo_f002smocod')) && rec.get('Orgsmo_f002smocod') != '76');
		});
		OrgSMOCombo.lastQuery = 'Строка, которую никто не додумается вводить в качестве фильтра, ибо это бред искать СМО по такой строке';
		OrgSMOCombo.setBaseFilter(function(rec) {
			return (Ext.isEmpty(rec.get('OrgSMO_endDate')) && rec.get('KLRgn_id') == 76 && !Ext.isEmpty(rec.get('Orgsmo_f002smocod')) && rec.get('Orgsmo_f002smocod') != '76');
		});
	},
	onHide: Ext.emptyFn,
	setKatNaselVisibility: function() {
		let
			win = this,
			base_form = win.RegistryForm.getForm();

		if ( base_form.findField('PayType_id').getFieldValue('PayType_SysNick') == 'oms' ) {
			base_form.findField('KatNasel_id').setContainerVisible(true);
			base_form.findField('KatNasel_id').setAllowBlank(false);
		}
		else {
			base_form.findField('KatNasel_id').clearValue();
			base_form.findField('KatNasel_id').setAllowBlank(true);
			base_form.findField('KatNasel_id').setContainerVisible(false);
		}

		win.syncSize();
		win.syncShadow();
	},
	setOrgSmoVisibility: function() {
		let
			win = this,
			base_form = win.RegistryForm.getForm();

		if (
			base_form.findField('KatNasel_id').getFieldValue('KatNasel_SysNick') == 'oblast'
			&& base_form.findField('PayType_id').getFieldValue('PayType_SysNick') == 'oms'
		) {
			base_form.findField('OrgSMO_id').setContainerVisible(true);
			base_form.findField('OrgSMO_id').setAllowBlank(false);
		}
		else {
			base_form.findField('OrgSMO_id').clearValue();
			base_form.findField('OrgSMO_id').setAllowBlank(true);
			base_form.findField('OrgSMO_id').setContainerVisible(false);
		}

		win.syncSize();
		win.syncShadow();
	},
	show: function() {
		sw.Promed.swRegistryEditWindow.superclass.show.apply(this, arguments);

		let
			base_form = this.RegistryForm.getForm(),
			form = this;

		if ( !arguments[0] || !arguments[0].RegistryType_id ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.ERROR,
				msg: 'Ошибка открытия формы ' + form.id + '.<br/>Не указаны нужные входные параметры.',
				title: 'Ошибка'
			});
			form.hide();
			return false;
		}

		base_form.reset();

		form.action = (arguments[0].action || 'add');
		form.callback = (typeof arguments[0].callback == 'function' ? arguments[0].callback : Ext.emptyFn);
		form.onHide = (typeof arguments[0].onHide == 'function' ? arguments[0].onHide : Ext.emptyFn);
		form.owner = arguments[0].owner || null;
		form.Registry_id = (!Ext.isEmpty(arguments[0].Registry_id) ? arguments[0].Registry_id : null);
		form.RegistryStatus_id = (!Ext.isEmpty(arguments[0].RegistryStatus_id) ? arguments[0].RegistryStatus_id : null);
		form.RegistryType_id = arguments[0].RegistryType_id;

		if ( form.action == 'edit' ) {
			form.buttons[0].setText('Переформировать');
		}
		else {
			form.buttons[0].setText('Сохранить');
		}

		// Если реестр уже помечен как оплачен, то не надо его переформировывать
		if ( form.RegistryStatus_id == 4 ) {
			form.action = "view";
		}

		base_form.findField('DispClass_id').setAllowBlank(!form.RegistryType_id.toString().inlist([ '7', '9', '12' ]));
		base_form.findField('DispClass_id').setContainerVisible(form.RegistryType_id.toString().inlist([ '7', '9', '12' ]));

		if ( form.RegistryType_id.toString().inlist([ '7', '9', '12' ]) ) {
			let dispClassList = [];

			switch ( form.RegistryType_id ) {
				case 7: // Дисп-ция взр. населения с 2013 года
					dispClassList = [ '1', '2' ];
					break;

				case 9: // Дисп-ция детей-сирот с 2013 года
					dispClassList = [ '3', '4', '7', '8' ];
					break;

				case 12: // Мед. осмотры несовершеннолетних
					dispClassList = [ '10', '12' ];
					break;
			}

			base_form.findField('DispClass_id').getStore().clearFilter();
			base_form.findField('DispClass_id').lastQuery = '';
			base_form.findField('DispClass_id').getStore().filterBy(function(rec) {
				return (rec.get('DispClass_Code').toString().inlist(dispClassList));
			});
		}

		base_form.setValues(arguments[0]);

		form.syncSize();
		form.syncShadow();

		let loadMask = new Ext.LoadMask(form.getEl(),{msg: LOAD_WAIT});
		loadMask.show();

		form.enableEdit(form.action.inlist(['add','edit']));

		switch ( form.action ) {
			case 'add':
				form.setTitle(WND_ADMIN_REGISTRYADD);
				break;

			case 'edit':
				form.setTitle(WND_ADMIN_REGISTRYEDIT);
				break;

			case 'view':
				form.setTitle(WND_ADMIN_REGISTRYVIEW);
				break;
		}

		if ( form.action.inlist(['edit','view']) ) {
			base_form.load({
				params: {
					Registry_id: form.Registry_id,
					RegistryType_id: form.RegistryType_id
				},
				failure: function() {
					loadMask.hide();

					sw.swMsg.show({
						buttons: Ext.Msg.OK,
						fn: function() {
							form.hide();
						},
						icon: Ext.Msg.ERROR,
						msg: 'Ошибка запроса к серверу. Попробуйте повторить операцию.',
						title: 'Ошибка'
					});
				},
				success: function() {
					loadMask.hide();

					base_form.findField('Registry_accDate').fireEvent('change', base_form.findField('Registry_accDate'), base_form.findField('Registry_accDate').getValue(), 0);

					form.setKatNaselVisibility();
					form.setOrgSmoVisibility();

					if ( form.action == 'edit' ) {
						base_form.findField('Registry_Num').focus(true, 50);
					}
					else {
						form.buttons[form.buttons.length - 1].focus();
					}
				},
				url: '/?c=Registry&m=loadRegistry'
			});
		}
		else {
			loadMask.hide();

			//base_form.findField('Registry_accDate').setValue(getGlobalOptions().date);
			base_form.findField('Registry_accDate').fireEvent('change', base_form.findField('Registry_accDate'), base_form.findField('Registry_accDate').getValue(), 0);

			base_form.findField('PayType_id').setFieldValue('PayType_SysNick', 'oms');

			form.setKatNaselVisibility();
			form.setOrgSmoVisibility();

			base_form.findField('Registry_Num').focus(true, 50);
		}
	},

	/* конструктор */
	initComponent: function() {
		let form = this;

		// Форма с полями
		this.RegistryForm = new Ext.form.FormPanel({
			autoHeight: true,
			bodyStyle: 'padding: 5px',
			border: false,
			buttonAlign: 'left',
			frame: true,
			id: 'RegistryEditForm',
			labelAlign: 'right',
			labelWidth: 190,
			items: [{
				name: 'Registry_id',
				xtype: 'hidden'
			}, {
				name: 'Lpu_id',
				xtype: 'hidden'
			}, {
				name: 'RegistryStatus_id',
				xtype: 'hidden'
			}, {
				anchor: '100%',
				disabled: true,
				hiddenName: 'RegistryType_id',
				tabIndex: form.firstTabIndex++,
				xtype: 'swregistrytypecombo'
			}, {
				allowBlank: false,
				autoCreate: {
					tag: "input",
					type: "text",
					maxLength: "10",
					autocomplete: "off"
				},
				fieldLabel: 'Номер счета',
				name: 'Registry_Num',
				tabIndex: form.firstTabIndex++,
				width: 100,
				xtype: 'textfield'
			}, {
				allowBlank: false,
				fieldLabel: 'Дата счета',
				format: 'd.m.Y',
				listeners: {
					'change': function(field, newValue, oldValue) {
						form.filterOrgSMOCombo();
					}
				},
				name: 'Registry_accDate',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				tabIndex: form.firstTabIndex++,
				width: 100,
				xtype: 'swdatefield'
			}, {
				allowBlank: false,
				fieldLabel: 'Начало периода',
				name: 'Registry_begDate',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				tabIndex: form.firstTabIndex++,
				width: 100,
				xtype: 'swdatefield'
			}, {
				allowBlank: false,
				fieldLabel: 'Окончание периода',
				name: 'Registry_endDate',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				tabIndex: form.firstTabIndex++,
				width: 100,
				xtype: 'swdatefield'
			}, {
				allowBlank: false,
				allowSysNick: true,
				anchor: '100%',
				comboSubject: 'PayType',
				disabled: true,
				fieldLabel: 'Вид оплаты',
				hiddenName: 'PayType_id',
				lastQuery: '',
				listeners: {
					'change': function(combo, newValue, oldValue) {
						let index = combo.getStore().findBy(function(rec) {
							return (rec.get(combo.valueField) == newValue);
						});
						combo.fireEvent('select', combo, combo.getStore().getAt(index), index);
					},
					'select': function(combo, record, index) {
						form.setKatNaselVisibility();
						form.setOrgSmoVisibility();
					}
				},
				loadParams: {
					params: {
						where: " where PayType_SysNick in ('oms', 'bud', 'money', 'ovd', 'other', 'dms')"
					}
				},
				tabIndex: form.firstTabIndex++,
				typeCode: 'int',
				xtype: 'swcommonsprcombo'
			}, {
				allowBlank: false,
				anchor: '100%',
				fieldLabel: 'Категория населения',
				hiddenName: 'KatNasel_id',
				listeners: {
					'change': function(combo, newValue, oldValue) {
						let index = combo.getStore().findBy(function(rec) {
							return (rec.get(combo.valueField) == newValue);
						});
						combo.fireEvent('select', combo, combo.getStore().getAt(index), index);
					},
					'select': function(combo, record, index) {
						form.setOrgSmoVisibility();
					}
				},
				tabIndex: form.firstTabIndex++,
				xtype: 'swkatnaselcombo'
			}, {
				anchor: '100%',
				fieldLabel: 'СМО',
				hiddenName: 'OrgSMO_id',
				minChars: 1,
				onTrigger2Click: function () {
					if (this.disabled) {
						return false;
					}

					let combo = this;

					getWnd('swOrgSearchWindow').show({
						object: 'smo',
						onClose: function () {
							combo.focus(true, 200);
						},
						onSelect: function (orgData) {
							if (orgData.Org_id > 0) {
								combo.setValue(orgData.Org_id);
								combo.focus(true, 250);
								combo.fireEvent('change', combo);
							}
							getWnd('swOrgSearchWindow').hide();
						}
					});
				},
				queryDelay: 1,
				tabIndex: form.firstTabIndex++,
				tpl: new Ext.XTemplate('<tpl for="."><div class="x-combo-list-item">' +
					'{OrgSMO_Nick}' + '{[(values.OrgSMO_endDate != "" && values.OrgSMO_endDate != null) ? " (не действует с " + values.OrgSMO_endDate + ")" : "&nbsp;"]}' +
					'</div></tpl>'),
				xtype: 'sworgsmocombo'
			}, {
				anchor: '100%',
				comboSubject: 'DispClass',
				hiddenName: 'DispClass_id',
				fieldLabel: 'Тип диспансеризации',
				lastQuery: '',
				tabIndex: form.firstTabIndex++,
				typeCode: 'int',
				xtype: 'swcommonsprcombo'
			}],
			keys: [{
				alt: true,
				fn: function(inp, e) {
					switch ( e.getKey() ) {
						case Ext.EventObject.C:
							if (form.action != 'view') {
								form.doSave(false);
							}
							break;

						case Ext.EventObject.J:
							form.hide();
							break;
					}
				},
				key: [ Ext.EventObject.C, Ext.EventObject.J ],
				scope: this,
				stopEvent: true
			}],
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
				{ name: 'DispClass_id' },
				{ name: 'KatNasel_id' },
				{ name: 'Lpu_id' },
				{ name: 'OrgSMO_id' },
				{ name: 'PayType_id' },
				{ name: 'Registry_accDate' },
				{ name: 'Registry_begDate' },
				{ name: 'Registry_endDate' },
				{ name: 'Registry_Num' },
				{ name: 'RegistryStatus_id' },
				{ name: 'RegistryType_id' }
			]),
			timeout: 600,
			url: '/?c=Registry&m=saveRegistry'
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					form.doSave();
				},
				iconCls: 'save16',
				tabIndex: form.firstTabIndex++,
				text: BTN_FRMSAVE
			}, {
				text: '-'
			},
				HelpButton(this, form.firstTabIndex++),
				{
					handler: function() {
						form.hide();
					},
					iconCls: 'cancel16',
					tabIndex: form.firstTabIndex++,
					text: BTN_FRMCANCEL
				}],
			items: [
				form.RegistryForm
			]
		});

		sw.Promed.swRegistryEditWindow.superclass.initComponent.apply(this, arguments);
	}
});