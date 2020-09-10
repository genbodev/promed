/**
/**
 * swUnionRegistryEditWindow - окно редактирования/добавления объединённого реестра (счета).
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
 * @version      18.05.2020
 * @comment      Префикс для id компонентов UREF (UnionRegistryEditForm)
 *
 *
 * @input data: action - действие (add, edit, view)
 *              Registry_id - ID реестра
 */

sw.Promed.swUnionRegistryEditWindow = Ext.extend(sw.Promed.BaseForm, {
	/* свойства */
	action: null,
	autoHeight: true,
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	draggable: true,
	firstTabIndex: 15180,
	id: 'UnionRegistryEditWindow',
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

		base_form.submit({
			failure: function(result_form, action) {
				loadMask.hide();
			},
			success: function(result_form, action) {
				loadMask.hide();

				if ( action.result ) {
					if ( action.result.Registry_id ) {
						let records = {
							Registry_id: action.result.Registry_id
						};

						win.callback(win.owner, action.result.Registry_id, records);
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
			base_form.findField('KatNasel_id').enable();
			base_form.findField('OrgRSchet_id').enable();
			base_form.findField('OrgSMO_id').enable();
			base_form.findField('PayType_id').enable();
			base_form.findField('Registry_accDate').enable();
			base_form.findField('Registry_begDate').enable();
			base_form.findField('Registry_endDate').enable();
			base_form.findField('Registry_Num').enable();
			base_form.findField('RegistryGroupType_id').enable();

			this.buttons[0].show();
		}
		else  {
			base_form.findField('KatNasel_id').disable();
			base_form.findField('OrgRSchet_id').disable();
			base_form.findField('OrgSMO_id').disable();
			base_form.findField('PayType_id').disable();
			base_form.findField('Registry_accDate').disable();
			base_form.findField('Registry_begDate').disable();
			base_form.findField('Registry_endDate').disable();
			base_form.findField('Registry_Num').disable();
			base_form.findField('RegistryGroupType_id').disable();

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
	filterPayTypeCombo: function() {
		let PayTypeCombo = this.RegistryForm.getForm().findField('PayType_id');
		let RegistryGroupType_Code = this.RegistryForm.getForm().findField('RegistryGroupType_id').getFieldValue('RegistryGroupType_Code');
		let PayType_SysNick = PayTypeCombo.getFieldValue('PayType_SysNick');

		PayTypeCombo.getStore().clearFilter();

		if (!Ext.isEmpty(RegistryGroupType_Code)) {
			PayTypeCombo.getStore().filterBy(function (rec) {
				if (RegistryGroupType_Code == 12) {
					return rec.get('PayType_SysNick') == 'oms';
				} else {
					return rec.get('PayType_SysNick') != 'oms';
				}
			});
		}

		if (
			!Ext.isEmpty(PayType_SysNick)
			&& (
				(PayType_SysNick == 'oms' && RegistryGroupType_Code != 12)
				|| (PayType_SysNick != 'oms' && RegistryGroupType_Code == 12)
			)
		) {
			PayTypeCombo.clearValue();
		}
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
		sw.Promed.swUnionRegistryEditWindow.superclass.show.apply(this, arguments);

		let
			base_form = this.RegistryForm.getForm(),
			form = this;

		form.action = (arguments[0].action || 'add');
		form.callback = (typeof arguments[0].callback == 'function' ? arguments[0].callback : Ext.emptyFn);
		form.onHide = (typeof arguments[0].onHide == 'function' ? arguments[0].onHide : Ext.emptyFn);
		form.owner = arguments[0].owner || null;

		base_form.reset();
		form.center();

		base_form.findField('PayType_id').getStore().clearFilter();

		base_form.setValues(arguments[0]);

		form.syncSize();
		form.syncShadow();

		let loadMask = new Ext.LoadMask(form.getEl(),{msg: LOAD_WAIT});
		loadMask.show();

		form.enableEdit(form.action.inlist(['add','edit']));

		switch ( form.action ) {
			case 'add':
				form.setTitle('Объединённый реестр: Добавление');
				break;

			case 'edit':
				form.setTitle('Объединённый реестр: Редактирование');
				break;

			case 'view':
				form.setTitle('Объединённый реестр: Просмотр');
				break;
		}

		if ( base_form.findField('OrgRSchet_id').getStore().getCount() == 0 ) {
			base_form.findField('OrgRSchet_id').getStore().load({
				callback: function() {
					base_form.findField('OrgRSchet_id').getStore().each(function(rec) {
						if (rec.get('OrgRSchetType_id') != 1) {
							base_form.findField('OrgRSchet_id').getStore().remove(rec);
						}
					});
				},
				params: {
					object: 'OrgRSchet',
					OrgRSchet_id: '',
					OrgRSchet_Name: ''
				}
			});
		}

		if ( form.action.inlist(['edit','view']) ) {
			base_form.load({
				params: {
					Registry_id: base_form.findField('Registry_id').getValue()
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

					form.filterPayTypeCombo();
					form.setKatNaselVisibility();
					form.setOrgSmoVisibility();

					if ( form.action == 'edit' ) {
						base_form.findField('Registry_Num').focus(true, 50);
					}
					else {
						form.buttons[form.buttons.length - 1].focus();
					}
				},
				url: '/?c=Registry&m=loadUnionRegistryEditForm'
			});
		}
		else {
			loadMask.hide();

			base_form.findField('Registry_accDate').fireEvent('change', base_form.findField('Registry_accDate'), base_form.findField('Registry_accDate').getValue(), 0);

			base_form.findField('RegistryGroupType_id').setFieldValue('RegistryGroupType_Code', 12);
			base_form.findField('PayType_id').setFieldValue('PayType_SysNick', 'oms');

			form.filterPayTypeCombo();
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
			id: 'UnionRegistryEditForm',
			labelAlign: 'right',
			labelWidth: 190,
			items: [{
				name: 'Registry_id',
				xtype: 'hidden'
			}, {
				name: 'Lpu_id',
				xtype: 'hidden'
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
				anchor: '100%',
				comboSubject: 'RegistryGroupType',
				disabled: true,
				fieldLabel: 'Тип объединённого реестра',
				hiddenName: 'RegistryGroupType_id',
				listeners: {
					'change': function(combo, newValue, oldValue) {
						let index = combo.getStore().findBy(function(rec) {
							return (rec.get(combo.valueField) == newValue);
						});
						combo.fireEvent('select', combo, combo.getStore().getAt(index), index);
					},
					'select': function(combo, record, index) {
						let base_form = form.RegistryForm.getForm();

						form.filterPayTypeCombo();

						if (typeof record == 'object' && record.get('RegistryGroupType_Code') == 12) {
							base_form.findField('PayType_id').setFieldValue('PayType_SysNick', 'oms');
						}

						base_form.findField('PayType_id').fireEvent('change', base_form.findField('PayType_id'), base_form.findField('PayType_id').getValue());
					}
				},
				listWidth: 350,
				loadParams: {
					params: {
						where: ' where RegistryGroupType_id in (12, 24)'
					}
				},
				tabIndex: form.firstTabIndex++,
				typeCode: 'int',
				xtype: 'swcommonsprcombo'
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
				allowBlank: false,
				anchor: '100%',
				hiddenName: 'OrgRSchet_id',
				tabIndex: form.firstTabIndex++,
				xtype: 'sworgrschetcombo'
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
				{ name: 'KatNasel_id' },
				{ name: 'Lpu_id' },
				{ name: 'OrgRSchet_id' },
				{ name: 'OrgSMO_id' },
				{ name: 'PayType_id' },
				{ name: 'Registry_accDate' },
				{ name: 'Registry_begDate' },
				{ name: 'Registry_endDate' },
				{ name: 'Registry_Num' },
				{ name: 'RegistryGroupType_id' }
			]),
			timeout: 600,
			url: '/?c=Registry&m=saveUnionRegistry'
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

		sw.Promed.swUnionRegistryEditWindow.superclass.initComponent.apply(this, arguments);
	}
});