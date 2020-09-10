/**
 * swUnionRegistryEditWindow - окно редактирования/добавления реестра (счета).
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Admin
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltd.
 * @author       Марков Андрей
 * @version      18.11.2009
 * @comment      Префикс для id компонентов urege (UnionRegistryEditForm)
 *               tabIndex (firstTabIndex): 15100+1 .. 15200
 *
 *
 * @input data: action - действие (add, edit, view)
 *              Registry_id - ID реестра
 */

sw.Promed.swUnionRegistryEditWindow = Ext.extend(sw.Promed.BaseForm, {
	action: null,
	autoHeight: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	PasportMOAssignNaselArray: {},
	split: true,
	width: 600,
	layout: 'form',
	firstTabIndex: 15100,
	id: 'UnionRegistryEditWindow',
	listeners: {
		hide: function () {
			this.callback(this.owner, -1);
		}
	},
	modal: true,
	onHide: Ext.emptyFn,
	plain: true,
	resizable: false,
	doSave: function () {
		var form = this.RegistryForm;
		var base_form = form.getForm();

		if (!base_form.isValid()) {
			sw.swMsg.show(
				{
					buttons: Ext.Msg.OK,
					fn: function () {
						form.getFirstInvalidEl().focus(true);
					},
					icon: Ext.Msg.WARNING,
					msg: ERR_INVFIELDS_MSG,
					title: ERR_INVFIELDS_TIT
				});
			return false;
		}
		var begDate = base_form.findField('Registry_begDate').getValue();
		var endDate = base_form.findField('Registry_endDate').getValue();
		if ((begDate) && (endDate) && (begDate > endDate)) {
			sw.swMsg.show(
				{
					buttons: Ext.Msg.OK,
					fn: function () {
						base_form.findField('Registry_begDate').focus(false)
					},
					icon: Ext.Msg.ERROR,
					msg: 'Дата окончания не может быть меньше даты начала.',
					title: ERR_INVFIELDS_TIT
				});
			return false;
		}
		// а дату-то надо всетаки передать, понадобится при редактировании
		form.ownerCt.submit();
		return true;
	},
	submit: function () {
		var form = this.RegistryForm, win = this;
		var base_form = form.getForm();
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет формирование реестра..."});
		loadMask.show();

		if (base_form.findField('Registry_IsZNOCheckbox').getValue() == true) {
			base_form.findField('Registry_IsZNO').setValue(2);
		}
		else {
			base_form.findField('Registry_IsZNO').setValue(1);
		}

		if (base_form.findField('Registry_IsOnceInTwoYearsCheckbox').getValue() == true) {
			base_form.findField('Registry_IsOnceInTwoYears').setValue(2);
		}
		else {
			base_form.findField('Registry_IsOnceInTwoYears').setValue(1);
		}

		if (
			base_form.findField('Registry_isPersFinCheckbox').getValue() == true
			&& win.RegistryType_id == 2
			&& win.PasportMOAssignNaselArray[getGlobalOptions().lpu_id] === true
		) {
			base_form.findField('Registry_isPersFin').setValue(2);
		}
		else {
			base_form.findField('Registry_isPersFin').setValue(1);
		}

		var params = {
			RegistryType_id: base_form.findField('RegistryType_id').getValue()
		};

		if ( base_form.findField('Registry_accDate').disabled ) {
			params.Registry_accDate = base_form.findField('Registry_accDate').getValue().dateFormat('d.m.Y');
		}

		if ( base_form.findField('PayType_id').disabled ) {
			params.PayType_id = base_form.findField('PayType_id').getValue();
		}

		if ( base_form.findField('OrgSmo_id').disabled ) {
			params.OrgSmo_id = base_form.findField('OrgSmo_id').getValue();
		}

		base_form.submit({
			params: params,
			failure: function (result_form, action) {
				loadMask.hide();
			},
			success: function (result_form, action) {
				loadMask.hide();
				if (action.result) {
					//if (action.result.Registry_id)
					if (action.result.Registry_id) {
						win.hide();
						win.callback(win.owner,action.result.Registry_id);
					}
				}
			}
		});
	},
	enableEdit: function (enable) {
		var form = this;
		var base_form = form.RegistryForm.getForm();
		if (enable) {
			base_form.findField('DispClass_id').enable();
			base_form.findField('Org_did').enable();
			base_form.findField('Registry_accDate').enable();
			base_form.findField('Registry_begDate').enable();
			base_form.findField('Registry_endDate').enable();
			base_form.findField('Registry_Num').enable();
			base_form.findField('Registry_isPersFinCheckbox').enable();
			base_form.findField('Registry_IsRepeated').enable();
			base_form.findField('Registry_IsZNOCheckbox').enable();
			base_form.findField('Registry_IsOnceInTwoYearsCheckbox').enable();

			if (this.RegistryType_id.toString().inlist(['2'])) {
				base_form.findField('PayType_id').enable();
				base_form.findField('PayType_id').setAllowBlank(false);
			} else {
				base_form.findField('PayType_id').disable();
				base_form.findField('PayType_id').setAllowBlank(true);
			}

			form.buttons[0].show();
		}
		else {
			base_form.findField('DispClass_id').disable();
			base_form.findField('Org_did').disable();
			base_form.findField('Registry_accDate').disable();
			base_form.findField('Registry_begDate').disable();
			base_form.findField('Registry_endDate').disable();
			base_form.findField('Registry_Num').disable();
			base_form.findField('Registry_isPersFinCheckbox').disable();
			base_form.findField('Registry_IsRepeated').disable();
			base_form.findField('Registry_IsZNOCheckbox').disable();
			base_form.findField('Registry_IsOnceInTwoYearsCheckbox').disable();

			base_form.findField('PayType_id').disable();

			form.buttons[0].hide();
		}
	},
	filterOrgSMOCombo: function (date) {
		var base_form = this.RegistryForm.getForm();
		var OrgSMOCombo = base_form.findField('OrgSmo_id');

		OrgSMOCombo.getStore().clearFilter();
		OrgSMOCombo.baseFilterFn = function (rec) {
			if ( (rec.get('OrgSMO_endDate') == '' || Date.parseDate(rec.get('OrgSMO_endDate'), 'd.m.Y') >= date) && rec.get('KLRgn_id') == 35 ) {
				return true;
			}
			else {
				return false;
			}
		}

		OrgSMOCombo.getStore().filterBy(function (rec) {
			if ( (rec.get('OrgSMO_endDate') == '' || Date.parseDate(rec.get('OrgSMO_endDate'), 'd.m.Y') >= date) && rec.get('KLRgn_id') == 35 ) {
				return true;
			}
			else {
				return false;
			}
		});
	},
	setIsPersFinCheckbox: function() {
		var
			_this = this,
			base_form = _this.RegistryForm.getForm();

		if (
			_this.RegistryType_id == 2
			&& _this.PasportMOAssignNaselArray[getGlobalOptions().lpu_id] === true
			&& base_form.findField('PayType_id').getFieldValue('PayType_SysNick') == 'oms'
			&& base_form.findField('KatNasel_id').getFieldValue('KatNasel_SysNick') == 'oblast'
		) {
			if (_this.acton == 'add') {
				base_form.findField('Registry_isPersFinCheckbox').setValue(true);
			}
			base_form.findField('Registry_isPersFinCheckbox').showContainer();
		}
		else {
			base_form.findField('Registry_isPersFinCheckbox').setValue(false);
			base_form.findField('Registry_isPersFinCheckbox').hideContainer();
		}

		_this.syncSize();
		_this.syncShadow();
	},
	setKatNaselVisibility: function() {
		var
			_this = this,
			base_form = _this.RegistryForm.getForm();

		if ( base_form.findField('PayType_id').getFieldValue('PayType_SysNick') == 'oms' ) {
			base_form.findField('KatNasel_id').setContainerVisible(true);
			base_form.findField('KatNasel_id').setAllowBlank(false);
		}
		else {
			base_form.findField('KatNasel_id').clearValue();
			base_form.findField('KatNasel_id').setContainerVisible(false);
			base_form.findField('KatNasel_id').setAllowBlank(true);

			_this.setOrgSmoVisibility();
		}

		_this.syncSize();
		_this.syncShadow();
	},
	setOrgDidVisibility: function() {
		var
			_this = this,
			base_form = _this.RegistryForm.getForm();

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

		_this.syncSize();
		_this.syncShadow();
	},
	setOrgSmoVisibility: function() {
		var
			_this = this,
			base_form = _this.RegistryForm.getForm();

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

		_this.syncSize();
		_this.syncShadow();
	},
	setZNOCheckboxVisibility: function() {
		var
			_this = this,
			base_form = _this.RegistryForm.getForm();

		if (
			_this.RegistryType_id == 2
			&& base_form.findField('PayType_id').getFieldValue('PayType_SysNick') == 'oms'
		) {
			base_form.findField('Registry_IsZNOCheckbox').setContainerVisible(true);
		}
		else {
			base_form.findField('Registry_IsZNOCheckbox').setValue(false);
			base_form.findField('Registry_IsZNOCheckbox').setContainerVisible(false);
		}

		_this.syncSize();
		_this.syncShadow();
	},
	show: function () {
		sw.Promed.swUnionRegistryEditWindow.superclass.show.apply(this, arguments);
		var form = this;
		var base_form = form.RegistryForm.getForm();
		if (!arguments[0] || !arguments[0].RegistryType_id) {
			sw.swMsg.show(
				{
					buttons: Ext.Msg.OK,
					icon: Ext.Msg.ERROR,
					msg: 'Ошибка открытия формы ' + form.id + '.<br/>Не указаны нужные входные параметры.',
					title: 'Ошибка'
				});
		}
		form.focus();

		base_form.reset();
		form.callback = Ext.emptyFn;
		form.onHide = Ext.emptyFn;
		if (arguments[0].Registry_id)
			form.Registry_id = arguments[0].Registry_id;
		else
			form.Registry_id = null;

		if (arguments[0].RegistryStatus_id)
			form.RegistryStatus_id = arguments[0].RegistryStatus_id;
		else
			form.RegistryStatus_id = null;
		if (arguments[0].RegistryType_id)
			form.RegistryType_id = arguments[0].RegistryType_id;

		if (arguments[0].callback) {
			form.callback = arguments[0].callback;
		}
		if (arguments[0].owner) {
			form.owner = arguments[0].owner;
		}
		if (arguments[0].onHide) {
			form.onHide = arguments[0].onHide;
		}
		if (arguments[0].action) {
			form.action = arguments[0].action;
		}
		else {
			if ((form.Registry_id) && (form.Registry_id > 0))
				form.action = "edit";
			else
				form.action = "add";
		}

		if ( 'add' == form.action ) {
			base_form.findField('PayType_id').setFieldValue('PayType_SysNick', 'oms');
		}

		base_form.findField('KatNasel_id').setContainerVisible(false);
		base_form.findField('Org_did').setContainerVisible(false);
		base_form.findField('Registry_IsZNOCheckbox').setContainerVisible(false);

		base_form.findField('DispClass_id').setAllowBlank(!form.RegistryType_id.toString().inlist([ '7', '9' ]));
		base_form.findField('DispClass_id').setContainerVisible(form.RegistryType_id.toString().inlist([ '7', '9' ]));

		if ( form.RegistryType_id.toString().inlist([ '7', '9' ]) ) {
			var dispClassList = [];

			switch ( form.RegistryType_id ) {
				case 7: // Дисп-ция взр. населения
					dispClassList = [ '1', '2' ];
					break;

				case 9: // Дисп-ция детей-сирот
					dispClassList = [ '3', '7' ];
					break;
			}

			base_form.findField('DispClass_id').getStore().clearFilter();
			base_form.findField('DispClass_id').lastQuery = '';
			base_form.findField('DispClass_id').getStore().filterBy(function(rec) {
				return (rec.get('DispClass_Code').toString().inlist(dispClassList));
			});
		}

		if (form.action == 'edit')
			this.buttons[0].setText('Переформировать');
		else
			this.buttons[0].setText('Сохранить');

		// Если реестр уже помечен как оплачен, то не надо его переформировывать
		if (form.RegistryStatus_id == 4) {
			form.action = "view";
		}

		base_form.setValues(arguments[0]);

		base_form.findField('KatNasel_id').setFieldValue('KatNasel_SysNick', 'oblast');
		base_form.findField('DispClass_id').fireEvent('change', base_form.findField('DispClass_id'), base_form.findField('DispClass_id').getValue());
		base_form.findField('KatNasel_id').fireEvent('change', base_form.findField('KatNasel_id'), base_form.findField('KatNasel_id').getValue());

		if ( form.PasportMOAssignNaselArray[getGlobalOptions().lpu_id] == undefined && form.RegistryType_id == 2 ) {
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
						form.PasportMOAssignNaselArray[getGlobalOptions().lpu_id] = (response_obj[0].PasportMO_IsAssignNasel == 1);
					}

					form.setIsPersFinCheckbox();
				},
				url: '/?c=LpuPassport&m=getLpuPassport'
			});
		}

		form.syncSize();
		form.syncShadow();

		var loadMask = new Ext.LoadMask(form.getEl(), {msg: LOAD_WAIT});
		loadMask.show();

		switch (form.action) {
			case 'add':
				form.setTitle('Реестр по СМО: Добавление');
				form.enableEdit(true);
				loadMask.hide();
				base_form.findField('Registry_begDate').focus(true, 50);
				break;
			case 'edit':
				form.setTitle('Реестр по СМО: Редактирование');
				form.enableEdit(true);
				base_form.findField('Registry_IsZNOCheckbox').disable();
				break;
			case 'view':
				form.setTitle('Реестр по СМО: просмотр');
				form.enableEdit(false);
				break;
		}

		// устанавливаем дату счета и запрещаем для редактирования
		// base_form.findField('Registry_accDate').disable(); 

		if (form.action != 'add') {
			base_form.load({
				params: {
					Registry_id: form.Registry_id,
					RegistryType_id: form.RegistryType_id
				},
				failure: function () {
					loadMask.hide();
					sw.swMsg.show(
						{
							buttons: Ext.Msg.OK,
							fn: function () {
								form.hide();
							},
							icon: Ext.Msg.ERROR,
							msg: 'Ошибка запроса к серверу. Попробуйте повторить операцию.',
							title: 'Ошибка'
						});
				},
				success: function () {
					loadMask.hide();

					var Org_did = base_form.findField('Org_did').getValue();

					if (base_form.findField('Registry_isPersFin').getValue() == 2) {
						base_form.findField('Registry_isPersFinCheckbox').setValue(true);
					}

					if (base_form.findField('Registry_IsZNO').getValue() == 2) {
						base_form.findField('Registry_IsZNOCheckbox').setValue(true);
					}

					if (base_form.findField('Registry_IsOnceInTwoYears').getValue() == 2) {
						base_form.findField('Registry_IsOnceInTwoYearsCheckbox').setValue(true);
					}

					base_form.findField('DispClass_id').fireEvent('change', base_form.findField('DispClass_id'), base_form.findField('DispClass_id').getValue());
					base_form.findField('KatNasel_id').fireEvent('change', base_form.findField('KatNasel_id'), base_form.findField('KatNasel_id').getValue());
					base_form.findField('Registry_accDate').fireEvent('change', base_form.findField('Registry_accDate'), base_form.findField('Registry_accDate').getValue(), 0);

					form.setIsPersFinCheckbox();
					form.setKatNaselVisibility();
					form.setOrgDidVisibility();
					form.setZNOCheckboxVisibility();

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

					if (form.action == 'edit')
						base_form.findField('Registry_begDate').focus(true, 50);
					else
						form.focus();
				},
				url: '/?c=Registry&m=loadUnionRegistry'
			});
		} else {
			form.setIsPersFinCheckbox();
			form.setKatNaselVisibility();
			form.setZNOCheckboxVisibility();

			base_form.findField('Registry_accDate').setValue(getGlobalOptions().date);
			base_form.findField('Registry_accDate').fireEvent('change', base_form.findField('Registry_accDate'), base_form.findField('Registry_accDate').getValue(), 0);
		}
	},

	initComponent: function () {
		// Форма с полями 
		var form = this;

		this.RegistryForm = new Ext.form.FormPanel({
			autoHeight: true,
			bodyStyle: 'padding: 5px',
			border: false,
			buttonAlign: 'left',
			frame: true,
			labelAlign: 'right',
			labelWidth: 190,
			items: [{
				name: 'Registry_id',
				xtype: 'hidden'
			}, {
				name: 'Lpu_id',
				xtype: 'hidden'
			}, {
				xtype: 'hidden',
				name: 'RegistryStatus_id',
				value: 3 // По умолчанию при добавлении
			}, {
				xtype: 'hidden',
				name: 'Registry_IsActive',
				value: 2 // По умолчанию при добавлении
			}, {
				xtype: 'hidden',
				name: 'Registry_isPersFin',
				value: 1 // По умолчанию при добавлении
			}, {
				xtype: 'hidden',
				name: 'Registry_IsZNO',
				value: 1 // По умолчанию при добавлении
			}, {
				xtype: 'hidden',
				name: 'Registry_IsOnceInTwoYears',
				value: 1 // По умолчанию при добавлении
			}, {
				anchor: '100%',
				disabled: true,
				name: 'RegistryType_id',
				tabIndex: form.firstTabIndex++,
				xtype: 'swregistrytypecombo'
			}, {
				allowBlank: false,
				allowSysNick: true,
				anchor: '100%',
				comboSubject: 'PayType',
				fieldLabel: langs('Вид оплаты'),
				listeners: {
					'change': function(combo, newValue, oldValue) {
						form.setIsPersFinCheckbox();
						form.setKatNaselVisibility();
						form.setOrgSmoVisibility();
						form.setOrgDidVisibility();
						form.setZNOCheckboxVisibility();
					}
				},
				loadParams: {params: {where: " where PayType_SysNick in ('oms', 'bud', 'dms', 'speckont', 'contract')"}},
				tabIndex: form.firstTabIndex++,
				typeCode: 'int',
				xtype: 'swcommonsprcombo'
			}, {
				fieldLabel: 'ЗНО',
				name: 'Registry_IsZNOCheckbox',
				tabIndex: form.firstTabIndex++,
				xtype: 'checkbox'
			}, {
				allowBlank: false,
				fieldLabel: 'Начало периода',
				name: 'Registry_begDate',
				plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
				tabIndex: form.firstTabIndex++,
				width: 100,
				xtype: 'swdatefield'
			}, {
				allowBlank: false,
				fieldLabel: 'Окончание периода',
				name: 'Registry_endDate',
				plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
				tabIndex: form.firstTabIndex++,
				width: 100,
				xtype: 'swdatefield',
				listeners: {
					change: function(cb, newVal){
						var base_form = form.RegistryForm.getForm(),
							dispClassCombo = base_form.findField('DispClass_id'),
							dispClassRec = dispClassCombo.getStore().getById(dispClassCombo.getValue());

						if ( typeof dispClassRec == 'object' && dispClassRec.get('DispClass_Code') == 1 && form.RegistryType_id == 7 && newVal < new Date('2019-06-01 00:00:00')) {
							base_form.findField('Registry_IsOnceInTwoYearsCheckbox').showContainer();
						}
						else {
							base_form.findField('Registry_IsOnceInTwoYearsCheckbox').hideContainer();
							base_form.findField('Registry_IsOnceInTwoYearsCheckbox').setValue(false);
						}
					}
				}
			}, {
				anchor: '100%',
				comboSubject: 'DispClass',
				hiddenName: 'DispClass_id',
				fieldLabel: 'Тип диспансеризации',
				listeners: {
					'change': function(combo, newValue, oldValue) {
						var index = combo.getStore().findBy(function(rec) {
							return rec.get(combo.valueField) == newValue;
						})
						combo.fireEvent('select', combo, combo.getStore().getAt(index), index);
					},
					'select': function(combo, record, index) {
						var base_form = form.RegistryForm.getForm(),
							endDate = base_form.findField('Registry_endDate').getValue();

						if ( typeof record == 'object' && record.get('DispClass_Code') == 1 && form.RegistryType_id == 7 && endDate < new Date('2019-06-01 00:00:00')) {
							base_form.findField('Registry_IsOnceInTwoYearsCheckbox').showContainer();
						}
						else {
							base_form.findField('Registry_IsOnceInTwoYearsCheckbox').hideContainer();
							base_form.findField('Registry_IsOnceInTwoYearsCheckbox').setValue(false);
						}

						form.syncSize();
						form.syncShadow();
					}
				},
				lastQuery: '',
				tabIndex: form.firstTabIndex++,
				typeCode: 'int',
				xtype: 'swcommonsprcombo'
			}, {
				fieldLabel: 'Раз в 2 года',
				name: 'Registry_IsOnceInTwoYearsCheckbox',
				tabIndex: form.firstTabIndex++,
				xtype: 'checkbox'
			}, {
				anchor: '100%',
				fieldLabel: 'Направившая организация',
				hiddenName: 'Org_did',
				tabIndex: form.firstTabIndex++,
				xtype: 'sworgcomboex'
			}, {
				fieldLabel: 'Категория населения',
				hiddenName: 'KatNasel_id',
				listeners: {
					'change': function(combo, newValue) {
						form.setIsPersFinCheckbox();
						form.setOrgSmoVisibility();
					}
				},
				width: 250,
				xtype: 'swkatnaselcombo',
				tabIndex: form.firstTabIndex++
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
				tabIndex: form.firstTabIndex++,
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
				tabIndex: form.firstTabIndex++,
				xtype: 'checkbox'
			}, {
				fieldLabel: 'Повторная подача',
				hiddenName: 'Registry_IsRepeated',
				tabIndex: form.firstTabIndex++,
				width: 100,
				xtype: 'swyesnocombo'
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
				name: 'Registry_accDate',
				listeners: {
					'change': function (field, newValue, oldValue) {
						// наложить фильтр на СМО
						form.filterOrgSMOCombo(newValue);
					}.createDelegate(this)
				},
				plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
				tabIndex: form.firstTabIndex++,
				width: 100,
				xtype: 'swdatefield'
			}],
			keys: [{
				alt: true,
				fn: function (inp, e) {
					switch (e.getKey()) {
						case Ext.EventObject.C:
							if (this.action != 'view') {
								this.doSave(false);
							}
							break;
						case Ext.EventObject.J:
							this.hide();
							break;
					}
				},
				key: [Ext.EventObject.C, Ext.EventObject.J],
				scope: this,
				stopEvent: true
			}],
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
				{name: 'KatNasel_id'},
				{name: 'OrgSmo_id'},
				{name: 'DispClass_id'},
				{name: 'Registry_accDate'},
				{name: 'Registry_begDate'},
				{name: 'Registry_endDate'},
				{name: 'Registry_Num'},
				{name: 'RegistryType_id'},
				{name: 'RegistryStatus_id'},
				{name: 'Registry_IsActive'},
				{name: 'Registry_isPersFin'},
				{name: 'Registry_IsRepeated'},
				{name: 'PayType_id'},
				{name: 'Org_did'},
				{name: 'Registry_IsZNO'},
				{name: 'Registry_IsOnceInTwoYears'},
				{name: 'Lpu_id'}
			]),
			timeout: 600,
			url: '/?c=Registry&m=saveUnionRegistry'
		});

		Ext.apply(this, {
			buttons: [{
				handler: function () {
					this.ownerCt.doSave();
				},
				iconCls: 'save16',
				text: BTN_FRMSAVE,
				tabIndex: form.firstTabIndex++
			}, {
				text: '-'
			},
			HelpButton(this, form.firstTabIndex++),
			{
				handler: function () {
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL,
				tabIndex: form.firstTabIndex++
			}],
			items: [
				form.RegistryForm
			]
		});

		sw.Promed.swUnionRegistryEditWindow.superclass.initComponent.apply(this, arguments);
	}
});
