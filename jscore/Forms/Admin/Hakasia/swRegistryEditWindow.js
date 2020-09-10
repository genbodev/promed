/**
* swRegistryEditWindow - окно редактирования/добавления реестра (счета).
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

		var begDate = base_form.findField('Registry_begDate').getValue();
		var endDate = base_form.findField('Registry_endDate').getValue();

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
		// а дату-то надо всетаки передать, понадобится при редактировании
		win.submit();
		return true;
	},
	enableEdit: function(enable) {
		var base_form = this.RegistryForm.getForm();

		if ( enable ) {
			base_form.findField('KatNasel_id').enable();
			base_form.findField('Registry_accDate').enable();
			base_form.findField('Registry_begDate').enable();
			base_form.findField('Registry_endDate').enable();
			base_form.findField('Registry_Num').enable();
			base_form.findField('DispClass_id').enable();
			base_form.findField('Registry_IsOnceInTwoYearsCheckbox').enable();
			base_form.findField('Registry_IsZNOCheckbox').enable();
			base_form.findField('Registry_IsFinancCheckbox').enable();

			this.buttons[0].enable();
		}
		else {
			base_form.findField('KatNasel_id').disable();
			base_form.findField('Registry_accDate').disable();
			base_form.findField('Registry_begDate').disable();
			base_form.findField('Registry_endDate').disable();
			base_form.findField('Registry_Num').disable();
			base_form.findField('DispClass_id').disable();
			base_form.findField('Registry_IsOnceInTwoYearsCheckbox').disable();
			base_form.findField('Registry_IsZNOCheckbox').disable();
			base_form.findField('Registry_IsFinancCheckbox').disable();

			this.buttons[0].disable();
		}
	},
	filterOrgSMOCombo: function() {
		var OrgSMOCombo = this.RegistryForm.getForm().findField('OrgSMO_id');

		OrgSMOCombo.getStore().clearFilter();
		OrgSMOCombo.getStore().filterBy(function(rec) {
			return (Ext.isEmpty(rec.get('OrgSMO_endDate')) && rec.get('KLRgn_id') == 19);
		});
		OrgSMOCombo.lastQuery = 'Строка, которую никто не додумается вводить в качестве фильтра, ибо это бред искать СМО по такой строке';
		OrgSMOCombo.setBaseFilter(function(rec) {
			return (Ext.isEmpty(rec.get('OrgSMO_endDate')) && rec.get('KLRgn_id') == 19);
		});
	},
	onHide: Ext.emptyFn,
	setIsFinancCheckbox: function() {
		var
			_this = this,
			base_form = _this.RegistryForm.getForm();
		if (
			_this.RegistryType_id == 2
			&& _this.PasportMOAssignNaselArray[getGlobalOptions().lpu_id] === true
			&& base_form.findField('KatNasel_id').getFieldValue('KatNasel_SysNick') == 'oblast'
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
	show: function() {
		sw.Promed.swRegistryEditWindow.superclass.show.apply(this, arguments);

		var form = this;
		var base_form = form.RegistryForm.getForm();

		if ( !arguments[0] || !arguments[0].RegistryType_id ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.ERROR,
				msg: 'Ошибка открытия формы '+form.id+'.<br/>Не указаны нужные входные параметры.',
				title: 'Ошибка'
			});
			form.hide();
			return false;
		}

		form.action = "add";
		form.callback = Ext.emptyFn;
		form.onHide = Ext.emptyFn;
		form.Registry_id = null;
		form.RegistryStatus_id = null;
		form.RegistryType_id = null;

		if ( arguments[0].action )  {
			form.action = arguments[0].action;
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

		if ( arguments[0].RegistryStatus_id ) {
			form.RegistryStatus_id = arguments[0].RegistryStatus_id;
		}

		if ( arguments[0].RegistryType_id ) {
			form.RegistryType_id = arguments[0].RegistryType_id;
		}

		//base_form.findField('RegistryStacType_id').setContainerVisible(form.RegistryType_id == 1);
		base_form.findField('DispClass_id').setAllowBlank(!form.RegistryType_id.toString().inlist(['7', '9', '12']));
		base_form.findField('DispClass_id').setContainerVisible(form.RegistryType_id.toString().inlist(['7', '9', '12']));

		if (form.RegistryType_id.toString().inlist(['7', '9', '12'])) {
			var dispClassList = [];

			switch (form.RegistryType_id) {
				case 7: // Дисп-ция взр. населения
					dispClassList = ['1', '2'];
					break;

				case 9: // Дисп-ция детей-сирот
					dispClassList = ['3', '4', '7', '8'];
					break;

				case 12: // Медосмотры несовершеннолетних
					dispClassList = ['10', '12'];
					break;
			}

			base_form.findField('DispClass_id').getStore().clearFilter();
			base_form.findField('DispClass_id').lastQuery = '';
			base_form.findField('DispClass_id').getStore().filterBy(function(rec) {
				return (rec.get('DispClass_Code').toString().inlist(dispClassList));
			});
		}

		base_form.findField('LpuBuilding_id').getStore().loadData(getStoreRecords(swLpuBuildingGlobalStore));

		if ( 
			form.PasportMOAssignNaselArray[getGlobalOptions().lpu_id] == undefined 
			&& form.RegistryType_id == 2
		) {
			Ext.Ajax.request({
				failure: function(response, options) {
					sw.swMsg.alert('Ошибка', 'При получении значения признака "МО имеет приписное население" возникли ошибки');
				},
				params: {
					param: 'PasportMO_IsAssignNasel',
					Lpu_id: getGlobalOptions().lpu_id
				},
				success: function(response, options) {
					var response_obj = Ext.util.JSON.decode(response.responseText);
					if ( typeof response_obj == 'object' && response_obj.length > 0 ) {
						form.PasportMOAssignNaselArray[getGlobalOptions().lpu_id] = (response_obj[0].PasportMO_IsAssignNasel == 1);
					}
					form.setIsFinancCheckbox();
				},
				url: '/?c=LpuPassport&m=getLpuPassport'
			});
		}
		
		form.syncSize();

		if ( form.action == 'edit' )
			form.buttons[0].setText('Переформировать');
		else
			form.buttons[0].setText('Сохранить');

		// Если реестр уже помечен как оплачен, то не надо его переформировывать
		if ( form.RegistryStatus_id == 4 ) {
			form.action = "view";
		}

		base_form.reset();
		base_form.setValues(arguments[0]);
		base_form.findField('DispClass_id').fireEvent('change', base_form.findField('DispClass_id'), base_form.findField('DispClass_id').getValue());

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

				base_form.findField('KatNasel_id').fireEvent('change', base_form.findField('KatNasel_id'), 0);
				base_form.findField('Registry_begDate').focus(true, 50);
				break;

			case 'edit':
				form.setTitle(WND_ADMIN_REGISTRYEDIT);
				form.enableEdit(true);
				break;

			case 'view':
				form.setTitle(WND_ADMIN_REGISTRYVIEW);
				form.enableEdit(false);
				break;
		}

		form.setIsFinancCheckbox();
		
		if ( form.action != 'add' ){
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

					if (base_form.findField('Registry_IsFinanc').getValue() == 2) {
						base_form.findField('Registry_IsFinancCheckbox').setValue(true);
					}
					
					if (base_form.findField('Registry_IsOnceInTwoYears').getValue() == 2) {
						base_form.findField('Registry_IsOnceInTwoYearsCheckbox').setValue(true);
					}

					if (base_form.findField('Registry_IsZNO').getValue() == 2) {
						base_form.findField('Registry_IsZNOCheckbox').setValue(true);
					}

					base_form.findField('DispClass_id').fireEvent('change', base_form.findField('DispClass_id'), base_form.findField('DispClass_id').getValue());
					base_form.findField('Registry_accDate').fireEvent('change', base_form.findField('Registry_accDate'), base_form.findField('Registry_accDate').getValue(), 0);
					base_form.findField('KatNasel_id').fireEvent('change', base_form.findField('KatNasel_id'), base_form.findField('KatNasel_id').getValue());

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
			base_form.findField('Registry_accDate').fireEvent('change', base_form.findField('Registry_accDate'), base_form.findField('Registry_accDate').getValue(), 0);
		}
		if (form.RegistryType_id.toString().inlist(['1', '2'])){
			base_form.findField('Registry_IsZNOCheckbox').setContainerVisible(true);
		}
		else {
			base_form.findField('Registry_IsZNOCheckbox').setContainerVisible(false);
		}
	},
	submit: function() {
		var
			base_form = this.RegistryForm.getForm(),
			win = this;

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
			base_form.findField('Registry_IsFinancCheckbox').getValue() == true
			&& win.RegistryType_id == 2
			&& win.PasportMOAssignNaselArray[getGlobalOptions().lpu_id] === true
			&& base_form.findField('KatNasel_id').getFieldValue('KatNasel_SysNick') == 'oblast'
		) {
			base_form.findField('Registry_IsFinanc').setValue(2);
		}
		else {
			base_form.findField('Registry_IsFinanc').setValue(1);
		}
		
		base_form.submit({
			params: {
				RegistryType_id: base_form.findField('RegistryType_id').getValue(),
				Registry_accDate: base_form.findField('Registry_accDate').getValue().dateFormat('d.m.Y')
			},
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

						win.callback(win.owner, action.result.RegistryQueue_id, records);
					}
					else {
						sw.swMsg.show({
							buttons: Ext.Msg.OK,
							fn: function() {
								win.hide();
							},
							icon: Ext.Msg.ERROR,
							msg: 'При выполнении операции сохранения произошла ошибка.<br/>Пожалуйста, повторите попытку позже.',
							title: 'Ошибка'
						});
					}
				}
			}
		});
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
			labelWidth: 160,
			items: [{
				name: 'Registry_id',
				xtype: 'hidden'
			}, {
				name: 'Lpu_id',
				xtype: 'hidden'
			},  {
				xtype: 'hidden',
				name: 'RegistryStatus_id',
				value: 3 // По умолчанию при добавлении
			}, {
				xtype: 'hidden',
				name: 'Registry_IsActive',
				value: 2 // По умолчанию при добавлении
			}, {
				xtype: 'hidden',
				name: 'Registry_IsOnceInTwoYears',
				value: 1 // По умолчанию при добавлении
			}, {
				xtype: 'hidden',
				name: 'Registry_IsFinanc',
				value: 1 // По умолчанию при добавлении
			}, {
				anchor: '100%',
				disabled: true,
				hiddenName: 'RegistryType_id',
				tabIndex: form.firstTabIndex++,
				xtype: 'swregistrytypecombo'
			}, {
				fieldLabel: 'ЗНО',
				name: 'Registry_IsZNOCheckbox',
				tabIndex: form.firstTabIndex++,
				xtype: 'checkbox'
			}, {
				xtype: 'hidden',
				name: 'Registry_IsZNO',
				value: 1 // По умолчанию при добавлении
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
				anchor: '100%',
				comboSubject: 'DispClass',
				hiddenName: 'DispClass_id',
				fieldLabel: 'Тип дисп-ции/медосмотра',
				listeners: {
					'change': function(combo, newValue, oldValue) {
						var index = combo.getStore().findBy(function(rec) {
							return rec.get(combo.valueField) == newValue;
						})
						combo.fireEvent('select', combo, combo.getStore().getAt(index), index);
					},
					'select': function(combo, record, index) {
						var base_form = form.RegistryForm.getForm();

						if ( typeof record == 'object' && record.get('DispClass_Code') == 1 && form.RegistryType_id == 7 ) {
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
				allowBlank: false,
				anchor: '100%',
				tabIndex: form.firstTabIndex++,
				xtype: 'swkatnaselcombo',
				listeners: {
					'change': function(combo, nv, ov) {
						var index = combo.getStore().findBy(function(rec) {
							return (rec.get(combo.valueField) == nv);
						});
						combo.fireEvent('select', combo, combo.getStore().getAt(index), index);
						form.setIsFinancCheckbox();
					},
					'select': function(combo, record, idx) {
						var katnasel_code;

						if ( typeof record == 'object' && !Ext.isEmpty(record.get('KatNasel_id')) ) {
							katnasel_code = record.get('KatNasel_Code');
						}

						var bf = form.RegistryForm.getForm();

						/*if ( bf.findField('RegistryType_id').getValue() == 1 ) {
							bf.findField('RegistryStacType_id').setDisabled(katnasel_code != 2);

							if ( katnasel_code != 2 ) {
								bf.findField('RegistryStacType_id').clearValue();
							}
						}*/

						bf.findField('OrgSMO_id').setContainerVisible(katnasel_code == 1);
						bf.findField('OrgSMO_id').setAllowBlank(katnasel_code != 1);

						if ( katnasel_code != 1 ) {
							bf.findField('OrgSMO_id').clearValue();
						}

						form.setIsFinancCheckbox();
						form.syncShadow();
					}
				}
			}, {
				anchor: '100%',
				fieldLabel: 'СМО',
				hiddenName: 'OrgSMO_id',
				lastQuery: '',
				tabIndex: form.firstTabIndex++,
				tpl: new Ext.XTemplate('<tpl for="."><div class="x-combo-list-item">'+
					'{OrgSMO_Nick}' + '{[(values.OrgSMO_endDate != "" && values.OrgSMO_endDate!=null && values.OrgSMO_id !=8) ? " (не действует с " + values.OrgSMO_endDate + ")" : "&nbsp;"]}'+
					'</div></tpl>'),
				xtype: 'sworgsmocombo',
				onTrigger2Click: function() {
					if ( this.disabled ) {
						return;
					}

					var combo = this;

					getWnd('swOrgSearchWindow').show({
						KLRgn_id: 19,
						object: 'smo',
						onClose: function() {
							combo.focus(true, 200);
						},
						onSelect: function(orgData) {
							if ( orgData.Org_id > 0 ) {
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
				name: 'Registry_IsFinancCheckbox',
				tabIndex: form.firstTabIndex++,
				xtype: 'checkbox'
			}, {
				anchor: '100%',
				hiddenName: 'LpuBuilding_id',
				fieldLabel: 'Подразделение',
				linkedElements: [],
				tabIndex: form.firstTabIndex++,
				xtype: 'swlpubuildingglobalcombo'
			}, /*{
				allowBlank: true,
				anchor: '100%',
				comboSubject: 'RegistryStacType',
				fieldLabel: 'Тип реестра стац.',
				hiddenName: 'RegistryStacType_id',
				tabIndex: form.firstTabIndex++,
				xtype: 'swcustomobjectcombo'
			},*/ {
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
				hiddenName: 'OrgRSchet_id',
				tabIndex: form.firstTabIndex++,
				width: 280,
				xtype: 'sworgrschetcombo'
			}, {
				allowBlank: false,
				fieldLabel: 'Дата счета',
				format: 'd.m.Y',
				id: 'regeRegistry_accDate',
				listeners: {
					'change': function(field, newValue, oldValue) {
						// наложить фильтр на СМО
						form.filterOrgSMOCombo();
					}.createDelegate(this)
				},
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
							if ( form.action != 'view' ) {
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
				{ name: 'LpuBuilding_id' },
				{ name: 'OrgRSchet_id' },
				{ name: 'OrgSMO_id' },
				{ name: 'Registry_accDate' },
				{ name: 'Registry_begDate' },
				{ name: 'Registry_endDate' },
				{ name: 'DispClass_id' },
				{ name: 'Registry_IsOnceInTwoYears' },
				{ name: 'Registry_IsActive' },
				{ name: 'Registry_Num' },
				//{ name: 'RegistryStacType_id' },
				{ name: 'RegistryStatus_id' },
				{ name: 'RegistryType_id' },
				{name: 'Registry_IsZNO'},
				{name: 'Registry_IsFinanc'}
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