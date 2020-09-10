/**
* swRegistryEditWindow - окно редактирования/добавления реестра (счета) для Астрахани.
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
* @comment      Префикс для id компонентов rege (RegistryEditForm)
*               tabIndex (firstTabIndex): 15100+1 .. 15200
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
	firstTabIndex: 15100,
	id: 'RegistryEditWindow',
	layout: 'form',
	listeners: {
		hide: function() {
			swLpuBuildingGlobalStore.clearFilter();
			this.callback(this.owner, -1);
		}
	},
	modal: true,
	PasportMOAssignNaselArray: {},
	plain: true,
	resizable: false,
	width: 600,

	/* методы */
	callback: Ext.emptyFn,
	checkKatNasel: function() {
		var
			_this = this,
			base_form = _this.RegistryForm.getForm();

		if (
			(
				(
					base_form.findField('KatNasel_id').getFieldValue('KatNasel_Code') == 1
					&& _this.RegistryType_id == 2
				)
				|| _this.RegistryType_id == 1
			)
			&& _this.PasportMOAssignNaselArray[getGlobalOptions().lpu_id] === true
			&& _this.PayType_SysNick == 'oms'
		) {
			if (_this.acton == 'add') {
				base_form.findField('Registry_IsFinancCheckbox').setValue(true);
			}
			base_form.findField('Registry_IsFinancCheckbox').showContainer();
		}
		else {
			base_form.findField('Registry_IsFinancCheckbox').setValue(false);
			base_form.findField('Registry_IsFinancCheckbox').hideContainer();
		}

		_this.syncSize();
		_this.syncShadow();
	},
	setIsZNOVisibility: function() {
		var
			base_form = this.RegistryForm.getForm(),
			win = this;

		var 
			PayType_SysNick = base_form.findField('PayType_id').getFieldValue('PayType_SysNick'),
			RegistryType_id = base_form.findField('RegistryType_id').getValue();

		if (!!RegistryType_id && RegistryType_id.inlist([1, 2, 20]) && PayType_SysNick == 'oms') {
			base_form.findField('Registry_IsZNOCheckbox').showContainer();
		}
		else {
			base_form.findField('Registry_IsZNOCheckbox').setValue(false);
			base_form.findField('Registry_IsZNOCheckbox').hideContainer();
		}
		
		base_form.findField('Registry_IsZNOCheckbox').setDisabled(win.action != 'add');
		
	},
	doSave: function() {
		var
			base_form = this.RegistryForm.getForm(),
			form = this.RegistryForm;

		if ( !base_form.isValid() ) {
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

		if ( base_form.findField('Registry_IsOnceInTwoYearsCheckbox').getValue() == true ) {
			base_form.findField('Registry_IsOnceInTwoYears').setValue(2);
		}
		else {
			base_form.findField('Registry_IsOnceInTwoYears').setValue(1);
		}
		
		base_form.findField('Registry_IsZNO').setValue(base_form.findField('Registry_IsZNOCheckbox').getValue() ? 2 : 1);

		var
			begDate = base_form.findField('Registry_begDate').getValue(),
			endDate = base_form.findField('Registry_endDate').getValue();

		if ( typeof begDate == 'object' && typeof endDate == 'object' ) {
			var err = '';

			if ( begDate > endDate ) {
				err = 'Дата окончания не может быть меньше даты начала.';
			}
			else if ( (endDate - begDate) / (24 * 3600 * 1000) > 31 ) {
				err = 'Период формирования реестра не должен превышать 31 день.';
			}

			if ( err.length > 0 ) {
				sw.swMsg.show({
					buttons: Ext.Msg.OK,
					fn: function() {
						base_form.findField('Registry_begDate').focus(false);
					},
					icon: Ext.Msg.ERROR,
					msg: err,
					title: ERR_INVFIELDS_TIT
				});
				return false;
			}
		}

		// а дату-то надо всетаки передать, понадобится при редактировании
		this.submit();

		return true;
	},
	onHide: Ext.emptyFn,
	show: function() {
		sw.Promed.swRegistryEditWindow.superclass.show.apply(this, arguments);

		var form = this;

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

		form.action = "add";
		form.callback = Ext.emptyFn;
		form.onHide = Ext.emptyFn;
		form.owner = null;
		form.Registry_id = null;
		form.PayType_SysNick = 'oms';
		form.RegistryStatus_id = null;

		if ( arguments[0].action ) {
			form.action = arguments[0].action;
		}
		else if ( !Ext.isEmpty(form.Registry_id) ) {
			form.action = "edit";
		}

		if ( typeof arguments[0].callback == 'function' ) {
			form.callback = arguments[0].callback;
		}

		if ( typeof arguments[0].onHide == 'function' ) {
			form.onHide = arguments[0].onHide;
		}

		if ( typeof arguments[0].owner == 'object' ) {
			form.owner = arguments[0].owner;
		}

		if ( arguments[0].Registry_id ) {
			form.Registry_id = arguments[0].Registry_id;
		}

		if ( arguments[0].PayType_SysNick ) {
			form.PayType_SysNick = arguments[0].PayType_SysNick;
		}
			
		if ( arguments[0].RegistryStatus_id ) {
			form.RegistryStatus_id = arguments[0].RegistryStatus_id;
		}

		if ( arguments[0].RegistryType_id ) {
			form.RegistryType_id = arguments[0].RegistryType_id;
		}
			
		var base_form = form.findById('RegistryEditForm').getForm();
		
		base_form.findField('Registry_IsFinancCheckbox').setContainerVisible(false);
		base_form.findField('Registry_IsOnceInTwoYearsCheckbox').setContainerVisible(false)
		base_form.findField('KatNasel_id').setAllowBlank(form.PayType_SysNick != 'oms' || form.RegistryType_id.inlist([1]));
		base_form.findField('KatNasel_id').setContainerVisible(form.PayType_SysNick == 'oms' && !form.RegistryType_id.inlist([1]));
		//base_form.findField('OMSSprTerr_id').setContainerVisible(false);
		base_form.findField('RegistryStacType_id').setContainerVisible(form.PayType_SysNick == 'oms' && (form.RegistryType_id == 1 || form.RegistryType_id == 14));
		// Показываем подразделение только для полки и стаца / смп
		form.findById('RegistryEditForm').getForm().findField('LpuBuilding_id').setContainerVisible(form.RegistryType_id.inlist([1,2,6,14]));

		base_form.findField('DispClass_id').setAllowBlank(!form.RegistryType_id.toString().inlist([ '7', '9', '12' ]));
		base_form.findField('DispClass_id').setContainerVisible(form.RegistryType_id.toString().inlist([ '7', '9', '12' ]));

		if ( form.RegistryType_id.toString().inlist([ '7', '9', '12' ]) ) {
			var dispClassList = [];

			switch ( form.RegistryType_id ) {
				case 7: // Дисп-ция взр. населения с 2013 года
					dispClassList = [ '1', '2' ];
					break;

				case 9: // Дисп-ция детей-сирот с 2013 года
					dispClassList = [ '3', '4', '7', '8' ];
					break;

				case 12: // Медосмотры несовершеннолетних
					dispClassList = [ '6', '9', '10', '11', '12' ];
					break;
			}

			base_form.findField('DispClass_id').getStore().clearFilter();
			base_form.findField('DispClass_id').lastQuery = '';
			base_form.findField('DispClass_id').getStore().filterBy(function(rec) {
				return (rec.get('DispClass_Code').toString().inlist(dispClassList));
			});
		}

		base_form.findField('Registry_IsFinancCheckbox').setContainerVisible(false);

		form.syncSize();
		form.syncShadow();

		if ((form.RegistryType_id == 2) || (form.RegistryType_id == 1) || (form.RegistryType_id == 14)) {
			// Читаем список подразделений
			form.findById('RegistryEditForm').getForm().findField('LpuBuilding_id').getStore().loadData(getStoreRecords(swLpuBuildingGlobalStore));
		}

		if ( form.action == 'edit' ) {
			this.buttons[0].setText('Переформировать');
		}
		else {
			this.buttons[0].setText('Сохранить');
		}

		// Если реестр уже помечен как оплачен, то не надо его переформировывать
		if ( form.RegistryStatus_id == 4 ) {
			form.action = "view";
		}

		var base_form = form.findById('RegistryEditForm').getForm();
		base_form.reset();

		base_form.setValues(arguments[0]);

		base_form.findField('PayType_id').getStore().clearFilter();
		base_form.findField('PayType_id').lastQuery = '';
		if (form.PayType_SysNick == 'bud') {
			base_form.findField('PayType_id').getStore().filterBy(function(rec) {
				return (rec.get('PayType_SysNick').inlist(['bud', 'fbud']));
			});
		} else {
			base_form.findField('PayType_id').getStore().filterBy(function(rec) {
				return (rec.get('PayType_SysNick').inlist(['oms']));
			});
		}

		if ( 'add' == form.action ) {
			if (form.PayType_SysNick == 'bud') {
				base_form.findField('PayType_id').setFieldValue('PayType_SysNick', 'bud');
			} else {
				base_form.findField('PayType_id').setFieldValue('PayType_SysNick', 'oms');
			}
			base_form.findField('PayType_id').fireEvent('change', base_form.findField('PayType_id'), base_form.findField('PayType_id').getValue());
		}

		if ( form.PasportMOAssignNaselArray[getGlobalOptions().lpu_id] == undefined && (form.RegistryType_id == 2 || form.RegistryType_id == 1) ) {
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

					form.checkKatNasel();
				},
				url: '/?c=LpuPassport&m=getLpuPassport'
			});
		}

		if ( base_form.findField('OrgRSchet_id').getStore().getCount() == 0 ) {
			base_form.findField('OrgRSchet_id').getStore().load({
				params: {
					object: 'OrgRSchet',
					OrgRSchet_id: '',
					OrgRSchet_Name: ''
				}
			});
		}

		var loadMask = new Ext.LoadMask(form.getEl(),{msg: LOAD_WAIT});
		loadMask.show();

		switch ( form.action ) {
			case 'add':
				form.setTitle(WND_ADMIN_REGISTRYADD);
				form.enableEdit(true);
				loadMask.hide();
				if ( form.RegistryType_id == 2 || form.RegistryType_id == 1 ) {
					base_form.findField('Registry_IsFinancCheckbox').setValue(true);
				}
				form.filterKatNasel();
				form.setIsZNOVisibility();
				base_form.findField('Registry_begDate').focus(true, 50);
				break;

			case 'edit':
				form.setTitle(WND_ADMIN_REGISTRYEDIT);
				form.enableEdit(true);
				//base_form.findField('Registry_IsTestCheckbox').disable();
				break;

			case 'view':
				form.setTitle(WND_ADMIN_REGISTRYVIEW);
				form.enableEdit(false);
				break;
		}

		// устанавливаем дату счета и запрещаем для редактирования
		// upd: разрешили редактировать дату счета
		// @task https://redmine.swan.perm.ru/issues/104406
		if ( form.action != 'add' ) {
			base_form.load({
				params: {
					Registry_id: form.Registry_id
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

					form.filterKatNasel();
					form.setIsZNOVisibility();
					base_form.findField('PayType_id').fireEvent('change', base_form.findField('PayType_id'), base_form.findField('PayType_id').getValue());

					base_form.findField('Registry_IsFinancCheckbox').setValue(base_form.findField('Registry_IsFinanc').getValue() == 2);
					//base_form.findField('Registry_IsTestCheckbox').setValue(base_form.findField('Registry_IsTest').getValue() == 2);
					base_form.findField('Registry_IsOnceInTwoYearsCheckbox').setContainerVisible(base_form.findField('DispClass_id').getValue() == 1);
					base_form.findField('Registry_IsOnceInTwoYearsCheckbox').setValue(base_form.findField('Registry_IsOnceInTwoYears').getValue() == 2);
					base_form.findField('Registry_IsZNOCheckbox').setValue(base_form.findField('Registry_IsZNO').getValue() == 2);
					base_form.findField('Registry_accDate').fireEvent('change', base_form.findField('Registry_accDate'), base_form.findField('Registry_accDate').getValue(), 0);
					base_form.findField('KatNasel_id').fireEvent('change', base_form.findField('KatNasel_id'), base_form.findField('KatNasel_id').getValue(), 0);

					if ( form.action == 'edit' ) {
						base_form.findField('Registry_begDate').focus(true, 50);
					}
					else {
						form.buttons[form.buttons.length - 1].focus();
					}
				},
				url: '/?c=Registry&m=loadRegistry'
			});
		}
		else {
			base_form.findField('KatNasel_id').fireEvent('change', base_form.findField('KatNasel_id'), base_form.findField('KatNasel_id').getValue(), 0);
			base_form.findField('Registry_accDate').setValue(getGlobalOptions().date);
			base_form.findField('Registry_accDate').fireEvent('change', base_form.findField('Registry_accDate'), base_form.findField('Registry_accDate').getValue(), 0);
		}
	},
	submit: function() {
		var
			win = this,
			base_form = this.RegistryForm.getForm(),
			loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение реестра..."}),
			params = new Object();

		params.RegistryType_id = base_form.findField('RegistryType_id').getValue();

		if (
			base_form.findField('Registry_IsFinancCheckbox').getValue() == true
			&& (
				(
					win.RegistryType_id == 2
					&& base_form.findField('KatNasel_id').getFieldValue('KatNasel_Code') == 1
				)
				|| win.RegistryType_id == 1
			)
			&& win.PasportMOAssignNaselArray[getGlobalOptions().lpu_id] === true
		) {
			base_form.findField('Registry_IsFinanc').setValue(2);
		}
		else {
			base_form.findField('Registry_IsFinanc').setValue(1);
		}

		/*if ( base_form.findField('Registry_IsTestCheckbox').getValue() == true ) {
			base_form.findField('Registry_IsTest').setValue(2);
		}
		else {
			base_form.findField('Registry_IsTest').setValue(1);
		}*/

		loadMask.show();

		base_form.submit({
			params: params,
			failure: function(result_form, action) {
				loadMask.hide();
			},
			success: function(result_form, action) {
				loadMask.hide();

				if ( action.result ) {
					if ( action.result.RegistryQueue_id ) {
						var records = {
							RegistryQueue_id: action.result.RegistryQueue_id,
							RegistryQueue_Position: action.result.RegistryQueue_Position
						}

						if ( typeof win.callback == 'function' ) {
							win.callback(win.owner, action.result.RegistryQueue_id, records);
						}
					}
					else {
						sw.swMsg.show({
							buttons: Ext.Msg.OK,
							fn: function() {
								//
							},
							icon: Ext.Msg.ERROR,
							msg: 'При выполнении операции сохранения произошла ошибка.<br/>Пожалуйста, повторите попытку чуть позже.',
							title: 'Ошибка'
						});
					}
				}
			}
		});
	},
	filterKatNasel: function() {
		var base_form = this.RegistryForm.getForm();
		var KatNasel_id = base_form.findField('KatNasel_id').getValue();

		var Registry_begDate = base_form.findField('Registry_begDate').getValue();

		var dateX = new Date(2018, 11, 1); // 01.12.2018

		base_form.findField('KatNasel_id').getStore().clearFilter();
		base_form.findField('KatNasel_id').lastQuery = "";
		base_form.findField('KatNasel_id').getStore().filterBy(function(rec) {
			return (rec.get('KatNasel_SysNick') != 'allinog' || !Registry_begDate || Registry_begDate >= dateX);
		});

		if (!Ext.isEmpty(KatNasel_id)) {
			index = base_form.findField('KatNasel_id').getStore().findBy(function(rec) {
				return (rec.get('KatNasel_id') == KatNasel_id);
			});

			if (index >= 0) {
				base_form.findField('KatNasel_id').setValue(KatNasel_id);
			} else {
				base_form.findField('KatNasel_id').clearValue();
			}
			base_form.findField('KatNasel_id').fireEvent('change', base_form.findField('KatNasel_id'), base_form.findField('KatNasel_id').getValue());
		}
	},
	/* конструктор */
	initComponent: function() {
		// Форма с полями 
		var form = this;
		
		this.RegistryForm = new Ext.form.FormPanel({
			autoHeight: true,
			bodyStyle: 'padding: 5px',
			border: false,
			buttonAlign: 'left',
			frame: true,
			id: 'RegistryEditForm',
			labelAlign: 'right',
			labelWidth: 180,
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
				name: 'Registry_IsFinanc',
				value: 1 // По умолчанию при добавлении 
			}, {
				xtype: 'hidden',
				name: 'Registry_IsOnceInTwoYears'
			}, {
				xtype: 'hidden',
				name: 'Registry_IsZNO'
			}, /*{
				xtype: 'hidden',
				name: 'Registry_IsTest',
				value: 1 // По умолчанию при добавлении
			},*/ {
				disabled: true,
				name: 'RegistryType_id',
				xtype: 'swregistrytypecombo',
				tabIndex: form.firstTabIndex++,
				width: 370
			}, /*{
				fieldLabel: 'Тест',
				name: 'Registry_IsTestCheckbox',
				tabIndex: form.firstTabIndex++,
				xtype: 'checkbox'
			},*/ {
				allowBlank: false,
				fieldLabel: 'Начало периода',
				name: 'Registry_begDate',
				listeners: {
					'change': function(field, newValue) {
						form.filterKatNasel();
					}
				},
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
				listeners: {
					'change': function(combo, nv, ov) {
						var index = combo.getStore().findBy(function(rec) {
							return (rec.get('PayType_id') == nv);
						});

						combo.fireEvent('select', combo, combo.getStore().getAt(index), index);
					}.createDelegate(this),
					'select': function(combo, record, idx) {
						var base_form  = this.RegistryForm.getForm();

						// какие то поля должны прятаться todo

						this.syncSize();
						this.syncShadow();
					}.createDelegate(this)
				},
				name: 'PayType_id',
				xtype: 'swpaytypecombo',
				tabIndex: form.firstTabIndex++
			}, {
				xtype: 'swkatnaselcombo',
				lastQuery: '',
				listeners: {
					'change': function(combo, newValue, oldValue) {
						var index = combo.getStore().findBy(function(rec) {
							return (rec.get(combo.valueField) == newValue);
						});
						combo.fireEvent('select', combo, combo.getStore().getAt(index), index);
					},
					'select': function(combo, record, index) {
						var base_form = form.RegistryForm.getForm();

						if ( typeof record == 'object' && record.get('KatNasel_Code') == 2 ) {
							if ( form.action == 'view' ) {
								base_form.findField('OMSSprTerr_id').disable();
							}
							else {
								base_form.findField('OMSSprTerr_id').enable();
							}
						}
						else {
							base_form.findField('OMSSprTerr_id').disable();
							base_form.findField('OMSSprTerr_id').clearValue();
						}

						form.checkKatNasel();
						form.syncSize();
						form.syncShadow();
					}
				},
				tabIndex: form.firstTabIndex++,
				width: 370
			},
			new Ext.ux.Andrie.Select({
				allowBlank: true,
				displayField: 'OMSSprTerr_Name',
				fieldLabel: 'Территория страхования',
				hiddenName: 'OMSSprTerr_id',
				mode: 'local',
				multiSelect: true,
				store: new Ext.db.AdapterStore({
					autoLoad: true,
					dbFile: 'Promed.db',
					fields: [
						{ name: 'OMSSprTerr_id', type: 'int' },
						{ name: 'OMSSprTerr_Code', type: 'string' },
						{ name: 'OMSSprTerr_Name', type: 'string' }
					],
					key: 'OMSSprTerr_id',
					sortInfo: {
						field: 'OMSSprTerr_id'
					},
					tableName: 'r30_OMSSprTerr'
				}),
				tabIndex: form.firstTabIndex++,
				tpl: '<tpl for="."><div class="x-combo-list-item">' +
					'<font color="red">{OMSSprTerr_Code}</font>&nbsp;{OMSSprTerr_Name}' +
					'</div></tpl>',
				valueField: 'OMSSprTerr_id',
				width: 370
			}), {
				fieldLabel: 'ЗНО',
				name: 'Registry_IsZNOCheckbox',
				tabIndex: form.firstTabIndex++,
				xtype: 'checkbox'
			}, {
				comboSubject: 'DispClass',
				hiddenName: 'DispClass_id',
				fieldLabel: 'Тип дисп-ции/медосмотра',
				lastQuery: '',
				tabIndex: form.firstTabIndex++,
				typeCode: 'int',
				width: 370,
				xtype: 'swcommonsprcombo',
				listeners: {
					select: function(cb, rec){
						form.RegistryForm.getForm().findField('Registry_IsOnceInTwoYearsCheckbox').setContainerVisible(rec.get('DispClass_Code').toString().inlist([1]))
					}
				}
			}, {
				hiddenName: 'LpuBuilding_id',
				fieldLabel: 'Подразделение',
				linkedElements: [],
				tabIndex: form.firstTabIndex++,
				width: 370,
				xtype: 'swlpubuildingglobalcombo'
			}, {
				allowBlank: true,
				comboSubject: 'RegistryStacType',
				fieldLabel: 'Тип реестра стац.',
				hiddenName: 'RegistryStacType_id',
				loadParams: {
					params: {
						where: "where RegistryStacType_Code in (1, 2)"
					}
				},
				tabIndex: form.firstTabIndex++,
				width: 370,
				xtype: 'swcustomobjectcombo'
			}, {
				fieldLabel: 'Раз в 2 года',
				name: 'Registry_IsOnceInTwoYearsCheckbox',
				tabIndex: form.firstTabIndex++,
				xtype: 'checkbox'
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
				width: 370,
				hiddenName: 'OrgRSchet_id',
				tabIndex: form.firstTabIndex++,
				xtype: 'sworgrschetcombo'
			}, {
				allowBlank: false,
				fieldLabel: 'Дата счета',
				format: 'd.m.Y',
				name: 'Registry_accDate',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				tabIndex: form.firstTabIndex++,
				width: 100,
				xtype: 'swdatefield'
			}, {
				fieldLabel: 'Подушевое финансирование',
				name: 'Registry_IsFinancCheckbox',
				tabIndex: form.firstTabIndex++,
				xtype: 'checkbox'
			}],
			keys: [{
				alt: true,
				fn: function(inp, e) {
					switch ( e.getKey() ) {
						case Ext.EventObject.C:
							if ( form.action != 'view')  {
								form.doSave(false);
							}
							break;

						case Ext.EventObject.J:
							form.hide();
							break;
					}
				},
				key: [ Ext.EventObject.C, Ext.EventObject.J ],
				scope: form,
				stopEvent: true
			}],
			reader: new Ext.data.JsonReader({
				success: function()  { 
					//
				}
			}, [
				{ name: 'KatNasel_id' },
				{ name: 'DispClass_id' },
				{ name: 'PayType_id' },
				{ name: 'Registry_accDate' },
				{ name: 'Registry_begDate' },
				{ name: 'Registry_endDate' },
				{ name: 'Registry_Num' },
				{ name: 'RegistryType_id' },
				{ name: 'LpuBuilding_id' },
				{ name: 'RegistryStacType_id' },
				{ name: 'RegistryStatus_id' },
				{ name: 'OMSSprTerr_id' },
				{ name: 'OrgRSchet_id' },
				{ name: 'Registry_IsActive' },
				{ name: 'Registry_IsFinanc' },
				{ name: 'Registry_IsOnceInTwoYears' },
				{ name: 'Registry_IsZNO' },
				//{ name: 'Registry_IsTest' },
				{ name: 'Lpu_id' }
			]),
			timeout: 600,
			url: '/?c=Registry&m=saveRegistry'
		});

		Ext.apply(form, {
			buttons: [{
				handler: function() {
					form.doSave();
				},
				iconCls: 'save16',
				text: BTN_FRMSAVE,
				tabIndex: form.firstTabIndex++
			}, {
				text: '-'
			},
			HelpButton(form, form.firstTabIndex++),
			{
				handler: function() {
					form.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL,
				tabIndex: form.firstTabIndex++
			}],
			items: [
				form.RegistryForm
			]
		});

		sw.Promed.swRegistryEditWindow.superclass.initComponent.apply(this, arguments);
	}
});