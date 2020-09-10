/**
 * swUnionRegistryEditWindow - окно редактирования/добавления реестра (счета) for Ufa.
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
	split: true,
	width: 600,
	layout: 'form',
	firstTabIndex: 15100,
	id: 'UnionRegistryEditWindow',
	codeRefresh: true,
	objectName: 'swUnionRegistryEditWindow',
	objectSrc: '/jscore/Forms/Admin/Ufa/swUnionRegistryEditWindow.js',
	Registry_IsNew: null,
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
		var registry_acc_date = base_form.findField('Registry_accDate').getValue().dateFormat('d.m.Y');

		if (base_form.findField('Registry_IsZNOCheckbox').getValue() == true) {
			base_form.findField('Registry_IsZNO').setValue(2);
		}
		else {
			base_form.findField('Registry_IsZNO').setValue(1);
		}

		if (base_form.findField('Registry_IsNotInsurCheckbox').getValue() == true) {
			base_form.findField('Registry_IsNotInsur').setValue(2);
		}
		else {
			base_form.findField('Registry_IsNotInsur').setValue(1);
		}

		base_form.submit(
			{
				params: {
					RegistryType_id: base_form.findField('RegistryType_id').getValue(),
					Registry_accDate: registry_acc_date,
					Registry_IsNew: win.Registry_IsNew
				},
				failure: function (result_form, action) {
					loadMask.hide();
					/*
					 Тут стандартный акшен на ошибку отрабатывает, если ошибка - поэтому не надо 
					 if (action.result) 
					 {
					 if (action.result.Error_Code)
					 {
					 Ext.Msg.alert('Ошибка #'+action.result.Error_Code, action.result.Error_Message);
					 }
					 }
					 */
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
			base_form.findField('OrgSmo_id').enable();
			base_form.findField('LpuUnitSet_id').enable();
			base_form.findField('DispClass_id').enable();
			base_form.findField('Lpu_cid').enable();
			base_form.findField('PayType_id').enable();
			base_form.findField('Registry_accDate').enable();
			base_form.findField('Registry_Comments').enable();
			base_form.findField('Registry_begDate').enable();
			base_form.findField('Registry_endDate').enable();
			base_form.findField('Registry_Num').enable();
			base_form.findField('Registry_IsNotInsurCheckbox').enable();
			base_form.findField('Registry_IsZNOCheckbox').enable();
			form.buttons[0].show();
		}
		else {
			base_form.findField('OrgSmo_id').disable();
			base_form.findField('LpuUnitSet_id').disable();
			base_form.findField('DispClass_id').disable();
			base_form.findField('Lpu_cid').disable();
			base_form.findField('PayType_id').disable();
			base_form.findField('Registry_accDate').disable();
			base_form.findField('Registry_Comments').disable();
			base_form.findField('Registry_begDate').disable();
			base_form.findField('Registry_endDate').disable();
			base_form.findField('Registry_Num').disable();
			base_form.findField('Registry_IsNotInsurCheckbox').disable();
			base_form.findField('Registry_IsZNOCheckbox').disable();
			form.buttons[0].hide();
		}
	},
	filterOrgSMOCombo: function (date) {
		// log(date);
		var base_form = this.RegistryForm.getForm();
		var OrgSMOCombo = base_form.findField('OrgSmo_id');

		OrgSMOCombo.getStore().clearFilter();
		OrgSMOCombo.baseFilterFn = function (rec) {
			if (/.+/.test(rec.get('OrgSMO_RegNomC')) && (rec.get('OrgSMO_endDate') == '' || Date.parseDate(rec.get('OrgSMO_endDate'), 'd.m.Y') >= date )) {
				return true;
			} else {
				return false;
			}
		};

		OrgSMOCombo.getStore().filterBy(function (rec) {
			if (/.+/.test(rec.get('OrgSMO_RegNomC')) && (rec.get('OrgSMO_endDate') == '' || Date.parseDate(rec.get('OrgSMO_endDate'), 'd.m.Y') >= date )) {
				return true;
			} else {
				return false;
			}
		});
	},
	loadCombo: function () {
		var form = this, params = {};
		var base_form = form.RegistryForm.getForm();

		if (form.RegistryType_id == 6) {
			params.LpuUnitSet_IsCmp = 2;
		}

		base_form.findField('LpuUnitSet_id').getStore().load({
			params: params,
			callback: function () {
				base_form.findField('LpuUnitSet_id').setValue(base_form.findField('LpuUnitSet_id').getValue());
			}
		});

		var OrgSMOCombo = base_form.findField('OrgSmo_id');
		OrgSMOCombo.baseFilterFn = null;
		OrgSMOCombo.getStore().clearFilter();
		OrgSMOCombo.baseFilterFn = function (record) {
			if (/.+/.test(record.get('OrgSMO_RegNomC')))
				return true;
			else
				return false;
		};
		OrgSMOCombo.getStore().filter('OrgSMO_RegNomC', /.+/);
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

		form.Registry_IsNew = (arguments[0].Registry_IsNew) ? arguments[0].Registry_IsNew : null;

		if (form.RegistryType_id == 19 && base_form.findField('Lpu_cid').getStore().getCount() == 0) {
			base_form.findField('Lpu_cid').getStore().load({
				callback: function() {
					base_form.findField('Lpu_cid').setValue(base_form.findField('Lpu_cid').getValue());
				}
			});
		}

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

		base_form.findField('Registry_IsNotInsurCheckbox').setContainerVisible(false);
		base_form.findField('Registry_IsZNOCheckbox').setContainerVisible(form.RegistryType_id.toString().inlist([ '1', '2', '6' ]));
		base_form.findField('LpuUnitSet_id').setContainerVisible(form.RegistryType_id != 19);
		base_form.findField('LpuUnitSet_id').setAllowBlank(form.RegistryType_id == 19);
		base_form.findField('Lpu_cid').setContainerVisible(form.RegistryType_id == 19);
		base_form.findField('Lpu_cid').setAllowBlank(form.RegistryType_id != 19);

		base_form.findField('DispClass_id').setAllowBlank(!form.RegistryType_id.toString().inlist([ '7', '17' ]) || form.Registry_IsNew != 2);
		base_form.findField('DispClass_id').setContainerVisible(form.RegistryType_id.toString().inlist([ '7', '17' ]) && form.Registry_IsNew == 2);

		if ( form.RegistryType_id.toString().inlist([ '7', '17' ]) && form.Registry_IsNew == 2 ) {
			var dispClassList = [];

			switch ( form.RegistryType_id ) {
				case 7: // Дисп-ция взр. населения с 2013 года
					dispClassList = [ '1', '2' ];
				break;

				case 17: // Проф.осмотры взр. населения; Профилактические осмотры несовершеннолетних 1-ый этап
					dispClassList = [ '5', '10' ];
				break;
			}

			base_form.findField('DispClass_id').getStore().clearFilter();
			base_form.findField('DispClass_id').lastQuery = '';
			base_form.findField('DispClass_id').getStore().filterBy(function(rec) {
				return (rec.get('DispClass_Code').toString().inlist(dispClassList));
			});
		}

		form.syncSize();
		form.syncShadow();

		if (form.action == 'edit')
			this.buttons[0].setText('Переформировать');
		else
			this.buttons[0].setText('Сохранить');

		// Если реестр уже помечен как оплачен, то не надо его переформировывать
		if (form.RegistryStatus_id == 4) {
			form.action = "view";
		}

		base_form.findField('PayType_id').getStore().clearFilter();
		base_form.findField('PayType_id').lastQuery = '';

		if (form.RegistryType_id != 19) {
			base_form.findField('PayType_id').getStore().filterBy(function(rec) {
				return (rec.get('PayType_SysNick').inlist(['oms']));
			});
		}

		base_form.setValues(arguments[0]);

		var loadMask = new Ext.LoadMask(form.getEl(), {msg: LOAD_WAIT});
		loadMask.show();
		switch (form.action) {
			//Новый action Task#18011
			case 'add_all' :
				form.hide();
				break;
			case 'add':
				form.setTitle('Реестр по СМО: Добавление');
				form.enableEdit(true);
				form.loadCombo();
				loadMask.hide();
				//form.getForm().clearInvalid();
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
		base_form.findField('Registry_accDate').disable();
		addToolTip(base_form.findField('Registry_Comments'), 'Обязательно для заполнения при подаче случаев, не соответствующих отчетному периоду');

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
					form.loadCombo();
					base_form.findField('PayType_id').fireEvent('change', base_form.findField('PayType_id'), base_form.findField('PayType_id').getValue());

					if (base_form.findField('Registry_IsZNO').getValue() == 2) {
						base_form.findField('Registry_IsZNOCheckbox').setValue(true);
					}

					if (base_form.findField('Registry_IsNotInsur').getValue() == 2) {
						base_form.findField('Registry_IsNotInsurCheckbox').setValue(true);
					}

					base_form.findField('Registry_accDate').fireEvent('change', base_form.findField('Registry_accDate'), base_form.findField('Registry_accDate').getValue(), 0);
					addToolTip(base_form.findField('Registry_Comments'), 'Обязательно для заполнения при подаче случаев, не соответствующих отчетному периоду');
					if (form.action == 'edit')
						base_form.findField('Registry_begDate').focus(true, 50);
					else
						form.focus();
				},
				url: '/?c=RegistryUfa&m=loadUnionRegistry'
			});
		} else {
			base_form.findField('Registry_accDate').setValue(getGlobalOptions().date);
			base_form.findField('Registry_accDate').fireEvent('change', base_form.findField('Registry_accDate'), base_form.findField('Registry_accDate').getValue(), 0);
			base_form.findField('PayType_id').setFieldValue('PayType_SysNick', 'oms');
			base_form.findField('PayType_id').fireEvent('change', base_form.findField('PayType_id'), base_form.findField('PayType_id').getValue());
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
			labelWidth: 150,
			items: [{
				name: 'Registry_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'Lpu_id',
				value: 0,
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
				name: 'Registry_IsNotInsur',
				value: 1 // По умолчанию при добавлении
			}, {
				xtype: 'hidden',
				name: 'Registry_IsZNO',
				value: 1 // По умолчанию при добавлении
			}, {
				anchor: '100%',
				disabled: true,
				name: 'RegistryType_id',
				tabIndex: form.firstTabIndex++,
				xtype: 'swregistrytypecombo'
			}, {
				fieldLabel: 'ЗНО',
				name: 'Registry_IsZNOCheckbox',
				tabIndex: form.firstTabIndex++,
				xtype: 'checkbox'
			}, {
				allowBlank: false,
				fieldLabel: 'Начало периода',
				listeners: {
					'change': function(field, newValue, oldValue) {
						var base_form = form.RegistryForm.getForm();

						if (form.RegistryType_id == 19) {
							var Lpu_cid = base_form.findField('Lpu_cid').getValue();

							base_form.findField('Lpu_cid').getStore().filterBy(function(rec) {
								return (
									Ext.isEmpty(newValue)
									|| (
										(Ext.isEmpty(rec.get('Lpu_BegDate')) || rec.get('Lpu_BegDate') <= newDate)
										&& (Ext.isEmpty(rec.get('Lpu_EndDate')) || rec.get('Lpu_EndDate') >= newDate)
										&& (Ext.isEmpty(rec.get('LpuDispContract_setDate')) || rec.get('LpuDispContract_setDate') <= newDate)
										&& (Ext.isEmpty(rec.get('LpuDispContract_disDate')) || rec.get('LpuDispContract_disDate') >= newDate)
									)
								);
							});

							if (!Ext.isEmpty(Lpu_cid)) {
								var index = base_form.findField('Lpu_cid').getStore().findBy(function(rec) {
									return (rec.get('Lpu_id') == Lpu_cid);
								});

								if (index >= 0 ) {
									base_form.findField('Lpu_cid').setValue(Lpu_cid);
								}
								else {
									base_form.findField('Lpu_cid').clearValue();
								}
							}
						}
					}
				},
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
				xtype: 'swdatefield'
			}, {
				allowBlank: false,
				anchor: '100%',
				hiddenName: 'PayType_id',
				listeners: {
					'change': function(combo, nv, ov) {
						var index = combo.getStore().findBy(function(rec) {
							return (rec.get('PayType_id') == nv);
						});

						combo.fireEvent('select', combo, combo.getStore().getAt(index), index);
					},
					'select': function(combo, record, idx) {
						var base_form  = form.RegistryForm.getForm();

						if (typeof record == 'object' && record.get('PayType_SysNick') == 'oms') {
							base_form.findField('OrgSmo_id').setAllowBlank(false);
							base_form.findField('OrgSmo_id').setContainerVisible(true);
						}
						else {
							base_form.findField('OrgSmo_id').clearValue();
							base_form.findField('OrgSmo_id').setAllowBlank(true);
							base_form.findField('OrgSmo_id').setContainerVisible(false);
						}

						base_form.findField('OrgSmo_id').fireEvent('change', base_form.findField('OrgSmo_id'), base_form.findField('OrgSmo_id').getValue());

						form.syncSize();
						form.syncShadow();
					}.createDelegate(this)
				},
				tabIndex: form.firstTabIndex++,
				xtype: 'swpaytypecombo'
			}, {
				anchor: '100%',
				comboSubject: 'DispClass',
				hiddenName: 'DispClass_id',
				fieldLabel: 'Тип дисп-ции / медосмотра',
				lastQuery: '',
				tabIndex: form.firstTabIndex++,
				typeCode: 'int',
				xtype: 'swcommonsprcombo'
			}, {
				anchor: '100%',
				allowBlank: false,
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
				listeners: {
					'blur': function (combo) {
						if (combo.getRawValue() == '') {
							combo.setValue(null);
						}
					},
					'change': function (combo, newValue, oldValue) {
						var index = combo.getStore().findBy(function (rec) {
							return (rec.get('OrgSMO_id') == newValue);
						});
						combo.fireEvent('select', combo, combo.getStore().getAt(index), index);
					},
					'select': function (combo, record, index) {
						var base_form = form.RegistryForm.getForm();
						if (typeof record == 'object' && record.get('OrgSMO_Nick') != 'Инотерриториальные' && !form.RegistryType_id.inlist([7, 9, 17])) {
							base_form.findField('Registry_IsNotInsurCheckbox').setContainerVisible(true);
						}
						else {
							base_form.findField('Registry_IsNotInsurCheckbox').setValue(false);
							base_form.findField('Registry_IsNotInsurCheckbox').setContainerVisible(false);
						}

						form.syncSize();
						form.syncShadow();
					}
				},
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
				fieldLabel: 'Незастрахованные лица',
				name: 'Registry_IsNotInsurCheckbox',
				tabIndex: form.firstTabIndex++,
				xtype: 'checkbox'
			}, {
				anchor: '100%',
				allowBlank: false,
				fieldLabel: 'Код подр. ТФОМС',
				tabIndex: form.firstTabIndex++,
				xtype: 'swlpuunitsetcombo'
			}, {
				allowBlank: true,
				xtype: 'swlpucombo',
				hiddenName: 'Lpu_cid',
				fieldLabel: 'МО-контрагент',
				tabIndex: form.firstTabIndex++,
				store: new Ext.data.JsonStore({
					autoLoad: false,
					fields: [
						{name: 'Lpu_id', mapping: 'Lpu_id'},
						{name: 'Lpu_Name', mapping: 'Lpu_Name'},
						{name: 'Lpu_Nick', mapping: 'Lpu_Nick'},
						{name: 'Lpu_BegDate', mapping: 'Lpu_BegDate'},
						{name: 'Lpu_EndDate', mapping: 'Lpu_EndDate'},
						{name: 'LpuDispContract_setDate', mapping: 'LpuDispContract_setDate'},
						{name: 'LpuDispContract_disDate', mapping: 'LpuDispContract_disDate'}
					],
					key: 'Lpu_id',
					sortInfo: {field: 'Lpu_Nick'},
					tableName: 'Lpu',
					url: '/?c=RegistryUfa&m=getLpuCidList'
				}),
				anchor: '100%'
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
			}, {
				fieldLabel: 'Комментарий',
				name: 'Registry_Comments',
				anchor: '100%',
				maxLength: 250,
				tabIndex: TABINDEX_SPEF + 29,
				xtype: 'textarea'
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
				{name: 'OrgSmo_id'},
				{name: 'LpuUnitSet_id'},
				{name: 'DispClass_id'},
				{name: 'Lpu_cid'},
				{name: 'PayType_id'},
				{name: 'Registry_accDate'},
				{name: 'Registry_begDate'},
				{name: 'Registry_endDate'},
				{name: 'Registry_Num'},
				{name: 'RegistryType_id'},
				{name: 'RegistryStatus_id'},
				//{ name: 'OrgRSchet_id' },
				{name: 'Registry_IsActive'},
				{name: 'Registry_IsNotInsur'},
				{name: 'Registry_IsZNO'},
				{name: 'Registry_Comments'},
				{name: 'Lpu_id'}
			]),
			timeout: 600,
			url: '/?c=RegistryUfa&m=saveUnionRegistry'
		});

		Ext.apply(this,{
			buttons: [{
				handler: function () {
					this.ownerCt.doSave();
				},
				iconCls: 'save16',
				text: BTN_FRMSAVE,
				tabIndex: 15100 + 8
			},
				{
					text: '-'
				},
				HelpButton(this, 15100 + 9),
				{
					handler: function () {
						this.ownerCt.hide();
					},
					iconCls: 'cancel16',
					// tabIndex: 207,
					text: BTN_FRMCANCEL,
					tabIndex: 15100 + 9
				}],
			items: [form.RegistryForm]
		});

		sw.Promed.swUnionRegistryEditWindow.superclass.initComponent.apply(this, arguments);
	}
});
