/**
* swUnionRegistryNewEditWindow - окно просмотра, добавления и редактирования объединённых реестров (новый вариант)
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2019 Swan Ltd.
* @author       Stanislav Bykov
* @version      05.08.2019
*/

sw.Promed.swUnionRegistryNewEditWindow = Ext.extend(sw.Promed.BaseForm, {
	/* свойства */
	autoHeight: true,
	buttonAlign: 'left',
	closeAction: 'hide',
	draggable: true,
	id: 'swUnionRegistryNewEditWindow',
	layout: 'form',
	listeners: {
		'hide': function() {
			this.onHide();
		}
	},
	modal: true,
	PasportMOAssignNaselArray: {},
	plain: true,
	resizable: false,
	firstTabIndex: TABINDEX_UREW + 50,
	title: '',
	width: 600,

	/* методы */
	doSave: function() {
		var win = this,
			base_form = this.formPanel.getForm(),
			RegistryGroupType_Code = base_form.findField('RegistryGroupType_id').getFieldValue('RegistryGroupType_Code'),
			params = {};

		if ( !base_form.isValid() ) {
			sw.swMsg.alert('Ошибка заполнения формы', 'Проверьте правильность заполнения полей формы.');
			return false;
		}
		
		if ( base_form.findField('Registry_IsRepeated').disabled ) {
			params.Registry_IsRepeated = base_form.findField('Registry_IsRepeated').getValue();
		}

		if ( win.findById('urnefRegistry_IsZNOCheckbox').getValue() == true ) {
			base_form.findField('Registry_IsZNO').setValue(2);
		}
		else {
			base_form.findField('Registry_IsZNO').setValue(1);
		}

		if ( base_form.findField('OrgSmo_id').disabled ) {
			params.OrgSmo_id = base_form.findField('OrgSmo_id').getValue();
		}

		if ( base_form.findField('PayType_id').disabled ) {
			params.PayType_id = base_form.findField('PayType_id').getValue();
		}

		if (
			base_form.findField('Registry_isPersFinCheckbox').getValue() == true
			&& RegistryGroupType_Code && RegistryGroupType_Code.inlist([1,21])
			&& win.PasportMOAssignNaselArray[getGlobalOptions().lpu_id] === true
		) {
			base_form.findField('Registry_isPersFin').setValue(2);
		}
		else {
			base_form.findField('Registry_isPersFin').setValue(1);
		}

		if (
			base_form.findField('Registry_isFapCheckbox').getValue() == true
		) {
			base_form.findField('Registry_IsFAP').setValue(2);
		}
		else {
			base_form.findField('Registry_IsFAP').setValue(1);
		}

		params.Registry_IsNew = 2;

		var
			begDate = base_form.findField('Registry_begDate').getValue(),
			endDate = base_form.findField('Registry_endDate').getValue();

		if ( typeof begDate == 'object' && typeof endDate == 'object' && begDate > endDate ) {
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

		// Проверяем, чтобы период был не больше календарного месяца с 1-е по последнее число
		if ( begDate.getFullYear() != endDate.getFullYear() || begDate.getMonth() != endDate.getMonth() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.YESNO,
				fn: function(buttonId, text, obj) {
					if ( buttonId == 'yes' ) {
						// Сохранение реестра
						win.getLoadMask('Подождите, сохраняется запись...').show();

						base_form.submit({
							failure: function (form, action) {
								win.getLoadMask().hide();
							},
							params: params,
							success: function(form, action) {
								win.getLoadMask().hide();
								win.hide();
								win.callback(win.owner, action.result.Registry_id);
							}
						});
					}
				},
				icon: Ext.MessageBox.QUESTION,
				msg: 'Указанный период более одного календарного месяца. Рекомендуется формировать реестры за один календарный месяц. Продолжить формирование?',
				title: ERR_INVFIELDS_TIT
			});
			sw.swMsg.getDialog().buttons[2].focus();

			return false;
		}
		else {
			// Сохранение реестра
			win.getLoadMask('Подождите, сохраняется запись...').show();

			base_form.submit({
				failure: function (form, action) {
					win.getLoadMask().hide();
				},
				params: params,
				success: function(form, action) {
					win.getLoadMask().hide();
					win.hide();
					win.callback(win.owner, action.result.Registry_id);
				}
			});
		}
	},
	filterLpuCid: function() {
		var
			index,
			win = this,
			base_form = win.formPanel.getForm(),
			Lpu_cid = base_form.findField('Lpu_cid').getValue(),
			Registry_begDate = base_form.findField('Registry_begDate').getValue();

		base_form.findField('Lpu_cid').lastQuery = '';
		base_form.findField('Lpu_cid').getStore().filterBy(function(rec) {
			return (
				rec.get('Lpu_id') != getGlobalOptions().lpu_id
				&& (
					Ext.isEmpty(Registry_begDate)
					|| (
						(Ext.isEmpty(rec.get('Lpu_BegDate')) || getValidDT(rec.get('Lpu_BegDate'), '') <= Registry_begDate)
						&& (Ext.isEmpty(rec.get('Lpu_EndDate')) || getValidDT(rec.get('Lpu_EndDate'), '') >= Registry_begDate)
					)
				)
			);
		});
		base_form.findField('Lpu_cid').setBaseFilter(function(rec) {
			return (
				rec.get('Lpu_id') != getGlobalOptions().lpu_id
				&& (
					Ext.isEmpty(Registry_begDate)
					|| (
						(Ext.isEmpty(rec.get('Lpu_BegDate')) || getValidDT(rec.get('Lpu_BegDate'), '') <= Registry_begDate)
						&& (Ext.isEmpty(rec.get('Lpu_EndDate')) || getValidDT(rec.get('Lpu_EndDate'), '') >= Registry_begDate)
					)
				)
			);
		});

		if ( !Ext.isEmpty(Lpu_cid) ) {
			index = base_form.findField('Lpu_cid').getStore().findBy(function(rec) {
				return (rec.get('Lpu_id') == Lpu_cid);
			});

			if ( index >= 0 ) {
				base_form.findField('Lpu_cid').setValue(Lpu_cid);
			}
			else {
				base_form.findField('Lpu_cid').clearValue();
			}
		}
	},
	filterOrgSMOCombo: function (date) {
		var base_form = this.formPanel.getForm();
		var OrgSMOCombo = base_form.findField('OrgSmo_id');

		OrgSMOCombo.getStore().clearFilter();
		OrgSMOCombo.baseFilterFn = function (rec) {
			return ((rec.get('OrgSMO_endDate') == '' || Date.parseDate(rec.get('OrgSMO_endDate'), 'd.m.Y') >= date) && rec.get('KLRgn_id') == 35);
		}

		OrgSMOCombo.getStore().filterBy(function (rec) {
			return ((rec.get('OrgSMO_endDate') == '' || Date.parseDate(rec.get('OrgSMO_endDate'), 'd.m.Y') >= date) && rec.get('KLRgn_id') == 35);
		});
	},
	setIsPersFinVisibility: function() {
		var
			win = this,
			base_form = win.formPanel.getForm(),
			RegistryGroupType_Code = base_form.findField('RegistryGroupType_id').getFieldValue('RegistryGroupType_Code');
		
		if (
			win.PasportMOAssignNaselArray[getGlobalOptions().lpu_id] === true
			&& base_form.findField('PayType_id').getFieldValue('PayType_SysNick') == 'oms'
			&& !Ext.isEmpty(base_form.findField('KatNasel_id').getFieldValue('KatNasel_SysNick'))
			&& base_form.findField('KatNasel_id').getFieldValue('KatNasel_SysNick').inlist(['all','oblast'])
			&& RegistryGroupType_Code && RegistryGroupType_Code.inlist([1,21])
		) {
			base_form.findField('Registry_isPersFinCheckbox').showContainer();
		}
		else {
			base_form.findField('Registry_isPersFinCheckbox').setValue(false);
			base_form.findField('Registry_isPersFinCheckbox').hideContainer();
		}

		win.syncSize();
		win.syncShadow();
	},
	setIsZNOVisibility: function() {
		var
			base_form = this.formPanel.getForm(),
			win = this;

		var
			PayType_SysNick = base_form.findField('PayType_id').getFieldValue('PayType_SysNick'),
			RegistryGroupType_id = base_form.findField('RegistryGroupType_id').getValue();

		if (
			!Ext.isEmpty(RegistryGroupType_id) && RegistryGroupType_id.inlist([ 1, 15 ,34])
			&& PayType_SysNick == 'oms'
		) {
			win.findById('URNEF_ZNOPanel').show();
		}
		else {
			win.findById('urnefRegistry_IsZNOCheckbox').setValue(false);
			win.findById('URNEF_ZNOPanel').hide();
		}

		win.syncSize();
		win.syncShadow();
	},
	setKatNaselVisibility: function() {
		var
			win = this,
			base_form = win.formPanel.getForm();

		if (
			base_form.findField('PayType_id').getFieldValue('PayType_SysNick') == 'oms'
		) {
			base_form.findField('KatNasel_id').setContainerVisible(true);
			base_form.findField('KatNasel_id').setAllowBlank(false);
		}
		else {
			base_form.findField('KatNasel_id').clearValue();
			base_form.findField('KatNasel_id').setAllowBlank(true);
			base_form.findField('KatNasel_id').setContainerVisible(false);
		}

		win.setOrgSmoVisibility();

		win.syncSize();
		win.syncShadow();
	},
	setLpuCidVisibility: function() {
		var
			win = this,
			base_form = win.formPanel.getForm();

		if ( base_form.findField('RegistryGroupType_id').getValue() == 34 ) {
			base_form.findField('Lpu_cid').setContainerVisible(true);
		}
		else {
			base_form.findField('Lpu_cid').clearValue();
			base_form.findField('Lpu_cid').setContainerVisible(false);
		}

		win.syncSize();
		win.syncShadow();
	},
	setOrgDidVisibility: function() {
		var
			win = this,
			base_form = win.formPanel.getForm();

		if (
			base_form.findField('PayType_id').getFieldValue('PayType_SysNick') == 'speckont'
			|| base_form.findField('PayType_id').getFieldValue('PayType_SysNick') == 'contract'
		) {
			base_form.findField('Org_did').setContainerVisible(true);
		}
		else {
			base_form.findField('Org_did').clearValue();
			base_form.findField('Org_did').setContainerVisible(false);
		}

		win.syncSize();
		win.syncShadow();
	},
	setOrgSmoVisibility: function() {
		var
			win = this,
			base_form = win.formPanel.getForm();

		if (
			base_form.findField('PayType_id').getFieldValue('PayType_SysNick') == 'oms'
			&& base_form.findField('KatNasel_id').getFieldValue('KatNasel_SysNick') == 'oblast'
		) {
			base_form.findField('OrgSmo_id').setContainerVisible(true);
			base_form.findField('OrgSmo_id').setAllowBlank(false);

			var index = base_form.findField('OrgSmo_id').getStore().findBy(function(rec) {
				return (rec.get('Orgsmo_f002smocod') == '35003');
			});

			if (index >= 0) {
				var record = base_form.findField('OrgSmo_id').getStore().getAt(index);
				base_form.findField('OrgSmo_id').setValue(record.get('OrgSMO_id'));
			}
		}
		else {
			base_form.findField('OrgSmo_id').clearValue();
			base_form.findField('OrgSmo_id').setAllowBlank(true);
			base_form.findField('OrgSmo_id').setContainerVisible(false);
		}

		win.syncSize();
		win.syncShadow();
	},
	setPayTypeEditAccess: function() {
		var
			win = this,
			base_form = win.formPanel.getForm();

		if (
			!Ext.isEmpty(base_form.findField('RegistryGroupType_id').getValue())
			&& base_form.findField('RegistryGroupType_id').getValue().inlist([1,2])
			&& win.action != 'view'
		) {
			base_form.findField('PayType_id').enable();
		}
		else {
			base_form.findField('PayType_id').disable();

			if (
				!Ext.isEmpty(base_form.findField('RegistryGroupType_id').getValue())
				&& !base_form.findField('RegistryGroupType_id').getValue().inlist([1,2])
			) {
				base_form.findField('PayType_id').setFieldValue('PayType_SysNick', 'oms');
			}
		}
	},
	setFapVisibility: function() {
		var
			win = this,
			base_form = win.formPanel.getForm(),
			PayType_SysNick = base_form.findField('PayType_id').getFieldValue('PayType_SysNick'),
			RegistryGroupType_id = base_form.findField('RegistryGroupType_id').getValue(),
			KatNasel_SysNick = base_form.findField('KatNasel_id').getFieldValue('KatNasel_SysNick'),
			Registry_isPersFinCheckbox = base_form.findField('Registry_isPersFinCheckbox').getValue();

		if (
			!Ext.isEmpty(RegistryGroupType_id) && RegistryGroupType_id==1
			&& PayType_SysNick == 'oms'
			&& !Ext.isEmpty(KatNasel_SysNick) && KatNasel_SysNick.inlist(['all','oblast'])
			&& win.PasportMOAssignNaselArray[getGlobalOptions().lpu_id] === true
			&& Registry_isPersFinCheckbox == true
		) {
			base_form.findField('Registry_isFapCheckbox').showContainer();
		}
		else {
			base_form.findField('Registry_isFapCheckbox').setValue(false);
			base_form.findField('Registry_isFapCheckbox').hideContainer();
		}

		win.syncSize();
		win.syncShadow();
	},
	show: function() {
		sw.Promed.swUnionRegistryNewEditWindow.superclass.show.apply(this, arguments);

		if ( !arguments[0] ) {
			arguments = [{}];
		}

		this.action = arguments[0].action || 'add';
		this.callback = arguments[0].callback || Ext.emptyFn;
		this.onHide = arguments[0].onHide ||  Ext.emptyFn;
		this.owner = arguments[0].owner || null;
		
		this.center();

		var win = this,
			base_form = win.formPanel.getForm();

		base_form.reset();

		win.findById('URNEF_ZNOPanel').hide();

		base_form.setValues(arguments[0]);

		if ( win.PasportMOAssignNaselArray[getGlobalOptions().lpu_id] == undefined ) {
			Ext.Ajax.request({
				failure: function(response, options) {
					sw.swMsg.alert('Ошибка', 'При получении значения признака "МО имеет приписное население" возникли ошибки');
				},
				params: {
					param: 'PasportMO_IsAssignNasel',
					Lpu_id: base_form.findField('Lpu_id').getValue()
				},
				success: function(response, options) {
					var response_obj = Ext.util.JSON.decode(response.responseText);

					if ( typeof response_obj == 'object' && response_obj.length > 0 ) {
						win.PasportMOAssignNaselArray[getGlobalOptions().lpu_id] = (response_obj[0].PasportMO_IsAssignNasel == 1);
					}

					win.setIsPersFinVisibility();
				},
				url: '/?c=LpuPassport&m=getLpuPassport'
			});
		}

		base_form.findField('Lpu_cid').lastQuery = '';
		base_form.findField('Lpu_cid').getStore().filterBy(function(rec) {
			return (rec.get('Lpu_id') != getGlobalOptions().lpu_id);
		});
		base_form.findField('Lpu_cid').setBaseFilter(function(rec) {
			return (rec.get('Lpu_id') != getGlobalOptions().lpu_id);
		});

		win.syncSize();
		win.doLayout();

		switch ( win.action ) {
			case 'view':
				win.setTitle(langs('Объединённый реестр: Просмотр'));
				break;

			case 'edit':
				win.setTitle(langs('Объединённый реестр: Редактирование'));
				break;

			case 'add':
				win.setTitle(langs('Объединённый реестр: Добавление'));
				break;

			default:
				log('swUnionRegistryNewEditWindow - action invalid');
				return false;
				break;
		}
		
		if ( win.action == 'add' ) {
			win.enableEdit(true);

			base_form.findField('Registry_IsRepeated').setValue(1);
			base_form.findField('PayType_id').setFieldValue('PayType_SysNick', 'oms');

			base_form.findField('Registry_Num').focus(true, 250);

			win.filterLpuCid();

			win.setIsPersFinVisibility();
			win.setIsZNOVisibility();
			win.setKatNaselVisibility();
			win.setLpuCidVisibility();
			win.setOrgDidVisibility();
			win.setOrgSmoVisibility();
			win.setPayTypeEditAccess();
			win.setFapVisibility();
		}
		else {
			win.enableEdit(false);
			win.getLoadMask('Пожалуйста, подождите, идет загрузка данных формы...').show();

			win.formPanel.load({
				failure: function() {
					win.getLoadMask().hide();
					sw.swMsg.alert('Ошибка', 'Не удалось загрузить данные с сервера', function() { win.hide(); } );
				},
				params: {
					Registry_id: base_form.findField('Registry_id').getValue(),
					Registry_IsNew: 2
				},
				success: function() {
					win.getLoadMask().hide();

					if ( win.action == 'edit') {
						win.enableEdit(true);
					}

					var Org_did = base_form.findField('Org_did').getValue();

					if ( base_form.findField('Registry_isPersFin').getValue() == 2 ) {
						base_form.findField('Registry_isPersFinCheckbox').setValue(true);
					}

					if ( base_form.findField('Registry_IsFAP').getValue() == 2 ) {
						base_form.findField('Registry_isFapCheckbox').setValue(true);
					}

					win.findById('urnefRegistry_IsZNOCheckbox').setValue(base_form.findField('Registry_IsZNO').getValue() == 2);

					win.filterLpuCid();

					win.setIsPersFinVisibility();
					win.setIsZNOVisibility();
					win.setKatNaselVisibility();
					win.setLpuCidVisibility();
					win.setOrgDidVisibility();
					win.setOrgSmoVisibility();
					win.setPayTypeEditAccess();
					win.setFapVisibility();

					base_form.findField('Registry_accDate').fireEvent('change', base_form.findField('Registry_accDate'), base_form.findField('Registry_accDate').getValue(), 0);

					if (!Ext.isEmpty(Org_did)) {
						base_form.findField('Org_did').getStore().load({
							callback: function() {
								if ( base_form.findField('Org_did').getStore().getCount() > 0 ) {
									base_form.findField('Org_did').setValue(Org_did);
								}
								else {
									base_form.findField('Org_did').clearValue();
								}
							},
							params: {
								Org_id: Org_did
							}
						});
					}

					if ( win.action == 'edit') {
						base_form.findField('Registry_Num').focus(true, 100);
					}
					else {
						win.buttons[win.buttons.length - 1].focus();
					}

					win.syncSize();
					win.doLayout();
				},
				url: '/?c=Registry&m=loadUnionRegistryEditForm'
			});
		}
	},

	/* конструктор */
	initComponent: function() {
		var win = this;
		
		win.formPanel = new Ext.form.FormPanel({
			autoHeight: true,
			buttonAlign: 'left',
			frame: true,
			labelAlign: 'right',
			labelWidth: 180,
			region: 'center',
			items: [{
				allowBlank: false,
				allowDecimals: false,
				allowLeadingZeroes: true,
				allowNegative: false,
				autoCreate: {
					tag: "input",
					type: "text",
					maxLength: "15",
					autocomplete: "off"
				},
				disabled: false,
				enableKeyEvents: true,
				fieldLabel: 'Номер счета',
				listeners: {
					'keydown': function (inp, e) {
						if ( e.shiftKey == true && e.getKey() == Ext.EventObject.TAB ) {
							e.stopEvent();
							win.buttons[win.buttons.length - 1].focus();
						}
					}
				},
				minValue: 1,
				name: 'Registry_Num',
				tabIndex: win.firstTabIndex++,
				width: 120,
				xtype: 'textfield'
			}, {
				allowBlank: false,
				disabled: false,
				fieldLabel: 'Дата счета',
				format: 'd.m.Y',
				listeners: {
					'change': function (field, newValue, oldValue) {
						// наложить фильтр на СМО
						win.filterOrgSMOCombo(newValue);
					}.createDelegate(this)
				},
				name: 'Registry_accDate',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				tabIndex: win.firstTabIndex++,
				width: 100,
				xtype: 'swdatefield'
			}, {
				allowBlank: false,
				fieldLabel: 'Начало периода',
				listeners: {
					'change': function(field) {
						win.filterLpuCid();
					}
				},
				name: 'Registry_begDate',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				tabIndex: win.firstTabIndex++,
				width: 100,
				xtype: 'swdatefield'
			}, {
				allowBlank: false,
				fieldLabel: 'Окончание периода',
				name: 'Registry_endDate',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				tabIndex: win.firstTabIndex++,
				width: 100,
				xtype: 'swdatefield'
			}, {
				fieldLabel: 'Повторная подача',
				hiddenName: 'Registry_IsRepeated',
				tabIndex: win.firstTabIndex++,
				width: 100,
				xtype: 'swyesnocombo'
			}, {
				allowBlank: false,
				anchor: '98%',
				comboSubject: 'RegistryGroupType',
				fieldLabel: 'Тип объединённого реестра',
				hiddenName: 'RegistryGroupType_id',
				listeners: {
					'change': function(combo, newValue, oldValue) {
						win.setIsPersFinVisibility();
						win.setIsZNOVisibility();
						win.setLpuCidVisibility();
						win.setPayTypeEditAccess();
						win.setFapVisibility();
					},
					'select': function(combo, record, index) {
						win.setIsPersFinVisibility();
						win.setIsZNOVisibility();
						win.setLpuCidVisibility();
						win.setPayTypeEditAccess();
						win.setFapVisibility();
					}
				},
				listWidth: 450,
				loadParams: {
					params: {
						where: ' where RegistryGroupType_id in (1, 2, 3, 4, 10, 15, 21, 27, 29, 33, 34)'
					}
				},
				tabIndex: win.firstTabIndex++,
				typeCode: 'int',
				xtype: 'swcommonsprcombo'
			}, {
				allowBlank: false,
				fieldLabel: 'Вид оплаты',
				hiddenName: 'PayType_id',
				listeners: {
					'change': function(combo, newValue, oldValue) {
						win.setIsPersFinVisibility();
						win.setIsZNOVisibility();
						win.setKatNaselVisibility();
						win.setOrgDidVisibility();
						win.setOrgSmoVisibility();
						win.setFapVisibility();
					},
					'select': function(combo, newValue, oldValue) {
						win.setIsPersFinVisibility();
						win.setIsZNOVisibility();
						win.setKatNaselVisibility();
						win.setOrgDidVisibility();
						win.setOrgSmoVisibility();
						win.setFapVisibility();
					}
				},
				loadParams: {
					params: {
						where: " where PayType_SysNick in ('oms', 'bud', 'dms', 'speckont', 'contract')"
					}
				},
				tabIndex: win.firstTabIndex++,
				width: 250,
				xtype: 'swpaytypecombo'
			}, {
				anchor: '100%',
				fieldLabel: 'Направившая организация',
				hiddenName: 'Org_did',
				tabIndex: win.firstTabIndex++,
				xtype: 'sworgcomboex'
			}, {
				allowBlank: false,
				anchor: '100%',
				fieldLabel: 'Категория населения',
				hiddenName: 'KatNasel_id',
				listeners: {
					'change': function(combo, newValue, oldValue) {
						win.setIsPersFinVisibility();
						win.setOrgSmoVisibility();
						win.setFapVisibility();
					},
					'select': function(combo, newValue, oldValue) {
						win.setIsPersFinVisibility();
						win.setOrgSmoVisibility();
						win.setFapVisibility();
					}
				},
				tabIndex: win.firstTabIndex++,
				xtype: 'swkatnaselcombo'
			}, {
				anchor: '100%',
				disabled: true,
				fieldLabel: 'СМО',
				withoutTrigger: true,
				xtype: 'sworgsmocombo',
				hiddenName: 'OrgSmo_id',
				name: 'OrgSmo_id',
				tpl: new Ext.XTemplate('<tpl for="."><div class="x-combo-list-item">' +
					'{OrgSMO_Nick}' + '{[(values.OrgSMO_endDate != "" && values.OrgSMO_endDate!=null && values.OrgSMO_id !=8) ? " (не действует с " + values.OrgSMO_endDate + ")" : "&nbsp;"]}' +
					'</div></tpl>'),
				tabIndex: win.firstTabIndex++,
				minChars: 1,
				onTrigger2Click: function () {
					if (this.disabled)
						return;
					var combo = this;
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
				queryDelay: 1
			}, {
				fieldLabel: 'Подушевое финансирование',
				name: 'Registry_isPersFinCheckbox',
				tabIndex: win.firstTabIndex++,
				xtype: 'checkbox',
				handler: function() {
					win.setFapVisibility();
				}
			}, {
				fieldLabel: 'ФАП',
				name: 'Registry_isFapCheckbox',
				tabIndex: win.firstTabIndex++,
				xtype: 'checkbox'
			}, {
				anchor: '100%',
				hiddenName: 'Lpu_cid',
				fieldLabel: 'МО-контрагент',
				tabIndex: win.firstTabIndex++,
				xtype: 'swlpucombo'
			}, {
				bodyStyle: 'padding: 0px',
				border: false,
				id: 'URNEF_ZNOPanel',
				labelWidth: 180,
				layout: 'form',
				xtype: 'panel',
				items: [{
					fieldLabel: 'ЗНО',
					id: 'urnefRegistry_IsZNOCheckbox',
					tabIndex: win.firstTabIndex++,
					xtype: 'checkbox'
				}]
			}, {
				name: 'Lpu_id',
				xtype: 'hidden'
			}, {
				name: 'Registry_id',
				xtype: 'hidden'
			}, {
				xtype: 'hidden',
				name: 'Registry_isPersFin'
			}, {
				xtype: 'hidden',
				name: 'Registry_IsZNO'
			}, {
				xtype: 'hidden',
				name: 'Registry_IsFAP'
			}],
			keys: [{
				alt: true,
				fn: function(inp, e) {
					switch ( e.getKey() ) {
						case Ext.EventObject.C:
							if ( win.action != 'view' ) {
								win.doSave();
							}
							break;
						case Ext.EventObject.J:
							win.hide();
							break;
					}
				},
				key: [ Ext.EventObject.C, Ext.EventObject.J ],
				scope: win,
				stopEvent: true
			}],
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
				{ name: 'Registry_id' },
				{ name: 'Registry_Num' },
				{ name: 'Registry_accDate' },
				{ name: 'Registry_begDate' },
				{ name: 'Registry_endDate' },
				{ name: 'Registry_isPersFin' },
				{ name: 'Registry_IsRepeated' },
				{ name: 'KatNasel_id' },
				{ name: 'PayType_id' },
				{ name: 'Org_did' },
				{ name: 'OrgSmo_id' },
				{ name: 'RegistryGroupType_id' },
				{ name: 'Registry_IsZNO' },
				{ name: 'Registry_IsFAP' },
				{ name: 'Lpu_cid' },
				{ name: 'Lpu_id' }
			]),
			timeout: 600,
			url: '/?c=Registry&m=saveUnionRegistry'
		});

		Ext.apply(win, {
			buttons: [{
				handler: function() {
					win.doSave();
				},
				iconCls: 'save16',
				tabIndex: win.firstTabIndex++,
				text: BTN_FRMSAVE
			}, {
				text: '-'
			},
			HelpButton(win, win.firstTabIndex++),
			{
				iconCls: 'cancel16',
				handler: function() {
					win.hide();
				},
				onShiftTabAction: function() {
					win.buttons[win.buttons.length - 2].focus();
				},
				onTabAction: function() {
					var base_form = win.formPanel.getForm();

					if ( !base_form.findField('Registry_Num').disabled ) {
						base_form.findField('Registry_Num').focus(true, 100);
					}
					else {
						win.buttons[win.buttons.length - 2].focus();
					}
				},
				tabIndex: win.firstTabIndex++,
				text: BTN_FRMCANCEL
			}],
			items: [ 
				win.formPanel
			]
		});

		sw.Promed.swUnionRegistryNewEditWindow.superclass.initComponent.apply(win, arguments);
	}
});