/**
* swRegistryEditWindow - окно редактирования/добавления реестра (счета) (Вологда).
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @region       Vologda
* @access       public
* @copyright    Copyright (c) 2018 Swan Ltd.
* @author       Stanislav Bykov
* @version      06.11.2018
* @comment      Префикс для id компонентов rege (RegistryEditForm)
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
	modal: true,
	PasportMOAssignNaselArray: {},
	plain: true,
	resizable: false,
	split: true,
	width: 600,

	/* методы */
	callback: Ext.emptyFn,
	doSave: function() {
		var
			base_form = this.RegistryForm.getForm(),
			win = this;

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

		win.doSubmit();

		return true;
	},
	doSubmit: function() {
		var
			base_form = this.RegistryForm.getForm(),
			loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет формирование реестра..."}),
			win = this;

		loadMask.show();

		var Registry_accDate = base_form.findField('Registry_accDate').getValue().dateFormat('d.m.Y');

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

		if (
			win.RegistryType_id == 2
			&& base_form.findField('PayType_id').getFieldValue('PayType_SysNick') == 'oms'
			&& base_form.findField('Registry_IsZNOCheckbox').getValue() == true
		) {
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

		var params = {
			RegistryType_id: base_form.findField('RegistryType_id').getValue(),
			Registry_accDate: Registry_accDate
		};

		if ( base_form.findField('Registry_IsRepeated').disabled ) {
			params.Registry_IsRepeated = base_form.findField('Registry_IsRepeated').getValue();
		}

		if ( base_form.findField('PayType_id').disabled ) {
			params.PayType_id = base_form.findField('PayType_id').getValue();
		}

		base_form.submit({
			failure: function(result_form, action) {
				loadMask.hide();
			},
			params: params,
			success: function(result_form, action) {
				loadMask.hide();

				if ( action.result ) {
					if ( action.result.RegistryQueue_id ) {
						var records = {
							RegistryQueue_id: action.result.RegistryQueue_id,
							RegistryQueue_Position: action.result.RegistryQueue_Position
						}

						win.callback(win.owner, action.result.RegistryQueue_id, records);
					}
					else {
						sw.swMsg.show({
							buttons: Ext.Msg.OK,
							fn: function() {
								//win.hide();
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
	enableEdit: function(enable) {
		var base_form = this.RegistryForm.getForm();

		if ( enable ) {
			base_form.findField('Registry_accDate').enable();
			base_form.findField('Registry_begDate').enable();
			base_form.findField('Registry_endDate').enable();
			base_form.findField('DispClass_id').enable();
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

			this.buttons[0].show();
		}
		else  {
			base_form.findField('Registry_accDate').disable();
			base_form.findField('Registry_begDate').disable();
			base_form.findField('Registry_endDate').disable();
			base_form.findField('DispClass_id').disable();
			base_form.findField('Registry_Num').disable();
			base_form.findField('Registry_isPersFinCheckbox').disable();
			base_form.findField('Registry_IsRepeated').disable();
			base_form.findField('Registry_IsZNOCheckbox').disable();
			base_form.findField('Registry_IsOnceInTwoYearsCheckbox').disable();

			base_form.findField('PayType_id').disable();

			this.buttons[0].hide();
		}
	},
	onHide: Ext.emptyFn,
	setIsPersFinCheckbox: function() {
		var
			_this = this,
			base_form = _this.RegistryForm.getForm();

		if (
			_this.RegistryType_id == 2
			&& _this.PasportMOAssignNaselArray[getGlobalOptions().lpu_id] === true
			&& base_form.findField('PayType_id').getFieldValue('PayType_SysNick') == 'oms'
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
	show: function() {
		sw.Promed.swRegistryEditWindow.superclass.show.apply(this, arguments);

		var
			base_form = this.RegistryForm.getForm(),
			form = this;

		if ( !arguments[0] || !arguments[0].RegistryType_id || !arguments[0].RegistrySubType_id ) {
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

		form.action = 'add';
		form.callback = Ext.emptyFn;
		form.onHide = Ext.emptyFn;
		form.owner = null;
		form.Registry_id = null;
		form.RegistryStatus_id = null;
		form.RegistrySubType_id = null;
		form.RegistryType_id = null;

		if ( arguments[0].action ) {
			form.action = arguments[0].action;
		}

		if ( typeof arguments[0].callback == 'function' ) {
			form.callback = arguments[0].callback;
		}

		if ( typeof arguments[0].onHide == 'function' ) {
			form.onHide = arguments[0].onHide;
		}

		if ( arguments[0].owner ) {
			form.owner = arguments[0].owner;
		}

		if ( !Ext.isEmpty(arguments[0].Registry_id) ) {
			form.Registry_id = arguments[0].Registry_id;
		}
			
		if ( !Ext.isEmpty(arguments[0].RegistryStatus_id) ) {
			form.RegistryStatus_id = arguments[0].RegistryStatus_id;
		}

		if ( !Ext.isEmpty(arguments[0].RegistrySubType_id) ) {
			form.RegistrySubType_id = arguments[0].RegistrySubType_id;
		}

		if ( !Ext.isEmpty(arguments[0].RegistryType_id) ) {
			form.RegistryType_id = arguments[0].RegistryType_id;
		}

		if ( 'add' == form.action ) {
			base_form.findField('PayType_id').setFieldValue('PayType_SysNick', 'oms');
		}

		base_form.findField('Lpu_cid').lastQuery = '';
		base_form.findField('Lpu_cid').getStore().filterBy(function(rec) {
			if (rec.get('Lpu_id') == getGlobalOptions().lpu_id) {
				return false;
			}

			return true;
		});
		base_form.findField('Lpu_cid').setBaseFilter(function(rec) {
			if (rec.get('Lpu_id') == getGlobalOptions().lpu_id) {
				return false;
			}

			return true;
		});

		base_form.findField('Lpu_cid').setContainerVisible(form.RegistryType_id.toString().inlist(['20']));
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

		base_form.setValues(arguments[0]);
		base_form.findField('DispClass_id').fireEvent('change', base_form.findField('DispClass_id'), base_form.findField('DispClass_id').getValue());

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

		var loadMask = new Ext.LoadMask(form.getEl(),{msg: LOAD_WAIT});
		loadMask.show();

		switch ( form.action ) {
			case 'add':
				form.setTitle(WND_ADMIN_REGISTRYADD);
				form.enableEdit(true);

				loadMask.hide();

				base_form.findField('Registry_IsRepeated').setValue(1);
				base_form.findField('Registry_begDate').focus(true, 50);
				break;

			case 'edit':
				form.setTitle(WND_ADMIN_REGISTRYEDIT);
				form.enableEdit(true);
				base_form.findField('Registry_IsRepeated').disable();
				base_form.findField('Registry_IsZNOCheckbox').disable();
				break;

			case 'view':
				form.setTitle(WND_ADMIN_REGISTRYVIEW);
				form.enableEdit(false);
				break;
		}
		
		// устанавливаем дату счета и запрещаем для редактирования
		base_form.findField('Registry_accDate').disable();

		if ( form.action != 'add' ){
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
					base_form.findField('Registry_accDate').fireEvent('change', base_form.findField('Registry_accDate'), base_form.findField('Registry_accDate').getValue(), 0);

					form.setIsPersFinCheckbox();
					form.setZNOCheckboxVisibility();

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
			form.setIsPersFinCheckbox();
			form.setZNOCheckboxVisibility();

			base_form.findField('Registry_accDate').setValue(getGlobalOptions().date);
			base_form.findField('Registry_accDate').fireEvent('change', base_form.findField('Registry_accDate'), base_form.findField('Registry_accDate').getValue(), 0);
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
			labelWidth: 190,
			items: [{
				name: 'Registry_id',
				xtype: 'hidden'
			}, {
				name: 'Lpu_id',
				xtype: 'hidden'
			}, {
				name: 'OrgSmo_id',
				xtype: 'hidden'
			}, {
				name: 'RegistryStatus_id',
				xtype: 'hidden'
			}, {
				name: 'RegistrySubType_id',
				xtype: 'hidden'
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
				hiddenName: 'RegistryType_id',
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
						form.setZNOCheckboxVisibility();
					}
				},
				loadParams: {params: {where: " where PayType_SysNick in ('oms', 'bud', 'dms', 'speckont', 'contract')"}},
				tabIndex: form.firstTabIndex++,
				typeCode: 'int',
				xtype: 'swcommonsprcombo'
			}, {
				anchor: '100%',
				hiddenName: 'Lpu_cid',
				fieldLabel: 'МО-контрагент',
				xtype: 'swlpucombo',
				tabIndex: form.firstTabIndex++
			}, {
				fieldLabel: 'ЗНО',
				name: 'Registry_IsZNOCheckbox',
				tabIndex: form.firstTabIndex++,
				xtype: 'checkbox'
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
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				tabIndex: form.firstTabIndex++,
				width: 100,
				xtype: 'swdatefield'
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
				{ name: 'Lpu_id' },
				{ name: 'OrgSmo_id' },
				{ name: 'Registry_accDate' },
				{ name: 'Registry_begDate' },
				{ name: 'Registry_endDate' },
				{ name: 'Registry_isPersFin' },
				{ name: 'Registry_IsZNO' },
				{ name: 'Registry_IsOnceInTwoYears' },
				{ name: 'Registry_Num' },
				{ name: 'RegistryStatus_id' },
				{ name: 'RegistrySubType_id' },
				{ name: 'RegistryType_id' },
				{ name: 'Registry_IsRepeated' },
				{ name: 'PayType_id' },
				{ name: 'Lpu_cid' }
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