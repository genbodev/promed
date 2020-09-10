/**
* swUnionRegistryEditWindow - окно просмотра, добавления и редактирования объединённых реестров
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2013 Swan Ltd.
* @author       Dmitry Vlasenko
* @version      07.10.2013
*/

sw.Promed.swUnionRegistryEditWindow = Ext.extend(sw.Promed.BaseForm, {
	/* свойства */
	autoHeight: true,
	buttonAlign: 'left',
	closeAction: 'hide',
	draggable: true,
	id: 'swUnionRegistryEditWindow',
	layout: 'form',
	listeners: {
		'hide': function() {
			this.onHide();
		}
	},
	modal: true,
	plain: true,
	resizable: false,
	firstTabIndex: TABINDEX_UREW,
	title: '',
	width: 600,

	/* методы */
	checkIsOnceInTwoYearsVisibility: function() {
		var
			base_form = this.formPanel.getForm(),
			DateX20180501 = new Date(2018, 4, 1),
			win = this;

		var
			Registry_begDate = base_form.findField('Registry_begDate').getValue(),
			RegistryGroupType_id = base_form.findField('RegistryGroupType_id').getValue();

		if (
			typeof Registry_begDate == 'object' && Registry_begDate >= DateX20180501
			&& !Ext.isEmpty(RegistryGroupType_id) && RegistryGroupType_id.inlist([ 3, 4 ])
		) {
			win.findById('UREF_OnceInTwoYearsPanel').show();
		}
		else {
			win.findById('uregeRegistry_IsOnceInTwoYearsCheckbox').setValue(false);
			win.findById('UREF_OnceInTwoYearsPanel').hide();
		}

		win.syncSize();
		win.syncShadow();
	},
	doSave: function() {
		var win = this,
			base_form = this.formPanel.getForm(),
			params = {};

		if ( !base_form.isValid() ) {
			sw.swMsg.alert('Ошибка заполнения формы', 'Проверьте правильность заполнения полей формы.');
			return;
		}
		
		if (
			base_form.findField('Registry_accDate').getValue() <= base_form.findField('Registry_endDate').getValue()
			&& base_form.findField('Registry_accDate').getValue() >= base_form.findField('Registry_begDate').getValue()
		) {
			sw.swMsg.alert('Ошибка заполнения формы', 'Дата не должна входить в период реестра.');
			return;
		}

		if ( base_form.findField('Registry_FileNum').disabled ) {
			params.Registry_FileNum = base_form.findField('Registry_FileNum').getValue();
		}

		if ( win.findById('uregeRegistry_IsAddAccCheckbox').getValue() == true ) {
			base_form.findField('Registry_IsAddAcc').setValue(2);
		}
		else {
			base_form.findField('Registry_IsAddAcc').setValue(1);
		}

		if ( win.findById('uregeRegistry_IsOnceInTwoYearsCheckbox').getValue() == true ) {
			base_form.findField('Registry_IsOnceInTwoYears').setValue(2);
		}
		else {
			base_form.findField('Registry_IsOnceInTwoYears').setValue(1);
		}

		var begDate = base_form.findField('Registry_begDate').getValue();
		var endDate = base_form.findField('Registry_endDate').getValue();
		
		// Проверяем чтобы период был не больше календарного месяца с 1-е по последнее число
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
	getRegistryFileNum: function() {
		var
			base_form = this.formPanel.getForm(),
			Registry_endDate = base_form.findField('Registry_endDate').getValue(),
			RegistryGroupType_id = base_form.findField('RegistryGroupType_id').getValue(),
			win = this;

		if ( Ext.isEmpty(Registry_endDate) || Ext.isEmpty(RegistryGroupType_id) ) {
			return false;
		}

		win.getLoadMask('Получение номера пакета...').show();

		Ext.Ajax.request({
			callback: function(options, success, response) {
				win.getLoadMask().hide();

				if ( success && response.responseText ) {
					var result = Ext.util.JSON.decode(response.responseText);

					if ( !Ext.isEmpty(result.Registry_FileNum) ) {
						base_form.findField('Registry_FileNum').setValue(result.Registry_FileNum);
					}
				}
			},
			params: {
				Lpu_id: base_form.findField('Lpu_id').getValue(),
				Registry_endDate: typeof Registry_endDate == 'object' ? Registry_endDate.format('d.m.Y') : Registry_endDate,
				RegistryGroupType_id: RegistryGroupType_id
			},
			url: '/?c=Registry&m=getRegistryFileNum'
		});
	},
	show: function() {
		sw.Promed.swUnionRegistryEditWindow.superclass.show.apply(this, arguments);
		if (!arguments[0])
		{
			arguments = [{}];
		}
		this.action = arguments[0].action || 'add';
		this.callback = arguments[0].callback || Ext.emptyFn;
		this.onHide = arguments[0].onHide ||  Ext.emptyFn;
		this.owner = arguments[0].owner || null;
		
		this.center();

		var win = this,
			base_form = win.formPanel.getForm();

		base_form.reset()

		// Пока убираем, потом наверняка понадобятся
		// @task https://redmine.swan.perm.ru/issues/66261
		base_form.findField('KatNasel_id').setAllowBlank(true);
		base_form.findField('KatNasel_id').setContainerVisible(false);

		win.findById('UREF_OnceInTwoYearsPanel').hide();
		win.syncSize();
		win.doLayout();

		base_form.setValues(arguments[0]);
		//base_form.findField('KatNasel_id').fireEvent('change', base_form.findField('KatNasel_id'), base_form.findField('KatNasel_id').getValue());
		
		switch ( win.action ) {
			case 'view':
				win.setTitle('Объединённый реестр: Просмотр');
			break;

			case 'edit':
				win.setTitle('Объединённый реестр: Редактирование');
			break;

			case 'add':
				win.setTitle('Объединённый реестр: Добавление');
			break;

			default:
				log('swUnionRegistryEditWindow - action invalid');
				return false;
			break;
		}
		
		if ( win.action == 'add' ) {
			win.enableEdit(true);
			base_form.findField('Registry_accDate').setValue(getGlobalOptions().date);
			base_form.findField('Registry_accDate').setMaxValue(getGlobalOptions().date);
			base_form.findField('Registry_accDate').fireEvent('change', base_form.findField('Registry_accDate'), base_form.findField('Registry_accDate').getValue(), 0);
			base_form.findField('Registry_Num').focus(true, 250);
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
					Registry_id: base_form.findField('Registry_id').getValue()
				},
				success: function() {
					win.getLoadMask().hide();

					if ( win.action == 'edit') {
						win.enableEdit(true);
					}

					win.checkIsOnceInTwoYearsVisibility();

					win.findById('uregeRegistry_IsAddAccCheckbox').setValue(base_form.findField('Registry_IsAddAcc').getValue() == 2);
					win.findById('uregeRegistry_IsOnceInTwoYearsCheckbox').setValue(base_form.findField('Registry_IsOnceInTwoYears').getValue() == 2);

					base_form.findField('Registry_accDate').fireEvent('change', base_form.findField('Registry_accDate'), base_form.findField('Registry_accDate').getValue(), 0);

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
				fieldLabel: 'Номер',
				id: 'UREW_Registry_Num',
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
				xtype: 'numberfield'
			}, {
				allowBlank: false,
				disabled: false,
				fieldLabel: 'Дата',
				format: 'd.m.Y',
				name: 'Registry_accDate',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				tabIndex: win.firstTabIndex++,
				width: 100,
				xtype: 'swdatefield'
			}, {
				allowBlank: false,
				fieldLabel: 'Начало периода',
				listeners: {
					'change': function(field, newValue, oldValue) {
						win.checkIsOnceInTwoYearsVisibility();
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
				listeners: {
					'change': function(field, newValue, oldValue) {
						win.getRegistryFileNum();
					}
				},
				name: 'Registry_endDate',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				tabIndex: win.firstTabIndex++,
				width: 100,
				xtype: 'swdatefield'
			}, {
				allowBlank: false,
				fieldLabel: 'Категория населения',
				width: 250,
				xtype: 'swkatnaselcombo',
				hiddenName: 'KatNasel_id',
				tabIndex: win.firstTabIndex++
			}, {
				allowBlank: false,
				anchor: '98%',
				comboSubject: 'RegistryGroupType',
				fieldLabel: 'Тип объединённого реестра',
				hiddenName: 'RegistryGroupType_id',
				value: 1,
				listeners: {
					'change': function(combo, newValue, oldValue) {
						win.getRegistryFileNum();
						win.checkIsOnceInTwoYearsVisibility();
					}
				},
				listWidth: 450,
				loadParams: {params: {where: ' where (RegistryGroupType_id <= 11 or RegistryGroupType_id = 26)'}},
				typeCode: 'int',
				xtype: 'swcommonsprcombo',
				tabIndex: win.firstTabIndex++
			}, {
				allowBlank: false,
				allowDecimals: false,
				allowNegative: false,
				autoCreate: {
					tag: "input",
					type: "text",
					maxLength: "2",
					autocomplete: "off"
				},
				fieldLabel: 'Номер пакета',
				minValue: 1,
				name: 'Registry_FileNum',
				tabIndex: win.firstTabIndex++,
				width: 100,
				xtype: 'numberfield'
			}, {
				fieldLabel: 'Дополнительный счет',
				id: 'uregeRegistry_IsAddAccCheckbox',
				tabIndex: win.firstTabIndex++,
				xtype: 'checkbox'
			}, {
				bodyStyle: 'padding: 0px',
				border: false,
				id: 'UREF_OnceInTwoYearsPanel',
				labelWidth: 180,
				layout: 'form',
				xtype: 'panel',
				items: [{
					fieldLabel: 'Раз в 2 года',
					id: 'uregeRegistry_IsOnceInTwoYearsCheckbox',
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
				name: 'Registry_IsAddAcc',
				xtype: 'hidden'
			}, {
				xtype: 'hidden',
				name: 'Registry_IsOnceInTwoYears'
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
				{ name: 'KatNasel_id' },
				{ name: 'RegistryGroupType_id' },
				{ name: 'Registry_FileNum' },
				{ name: 'Registry_IsAddAcc' },
				{ name: 'Registry_IsOnceInTwoYears' },
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

		sw.Promed.swUnionRegistryEditWindow.superclass.initComponent.apply(win, arguments);
	}
});