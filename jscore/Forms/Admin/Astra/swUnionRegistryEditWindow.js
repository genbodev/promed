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
	buttonAlign: 'left',
	closeAction: 'hide',
	firstTabIndex: TABINDEX_UREW,
	layout: 'form',
	listeners: {
		'hide': function() {
			this.onHide();
		}
	},
	title: '',
	draggable: true,
	id: 'swUnionRegistryEditWindow',
	width: 500,
	autoHeight: true,
	modal: true,
	plain: true,
	resizable: false,

	doReset: function() {
		var form = this.formPanel.getForm();
		form.reset();
	},
	doSave: function(options) {
		options = options || {};

		var win = this,
			base_form = this.formPanel.getForm(),
			params = {};

		if ( !base_form.isValid() ) {
			sw.swMsg.alert('Ошибка заполнения формы', 'Проверьте правильность заполнения полей формы.');
			return;
		}

		if (options.ignoreExistTFOMSError) {
			params.ignoreExistTFOMSError = 1;
		}

		if ( base_form.findField('Registry_IsOnceInTwoYearsCheckbox').getValue() == true ) {
			base_form.findField('Registry_IsOnceInTwoYears').setValue(2);
		}
		else {
			base_form.findField('Registry_IsOnceInTwoYears').setValue(1);
		}
		
		base_form.findField('Registry_IsZNO').setValue(base_form.findField('Registry_IsZNOCheckbox').getValue() ? 2 : 1);

		/*if (base_form.findField('Registry_IsTestCheckbox').getValue() == true) {
			base_form.findField('Registry_IsTest').setValue(2);
		}
		else {
			base_form.findField('Registry_IsTest').setValue(1);
		}*/

		win.getLoadMask('Подождите, сохраняется запись...').show();
		base_form.submit({
			failure: function (form, action) {
				win.getLoadMask().hide();

				if (action.response && action.response.responseText) {
					var response_obj = Ext.util.JSON.decode(action.response.responseText);
					if (response_obj.Error_Msg && response_obj.Error_Msg == 'YesNo') {
						var msg = response_obj.Alert_Msg;
						sw.swMsg.show({
							buttons: Ext.Msg.YESNO,
							fn: function(buttonId, text, obj) {
								if (buttonId == 'yes') {
									switch (response_obj.Error_Code) {
										case 100:
											options.ignoreExistTFOMSError = 1;
											break;
									}
									win.doSave(options);
									return;
								}
							},
							icon: Ext.MessageBox.QUESTION,
							msg: msg,
							title: langs('Вопрос')
						});
					}
				}
			},
			params: params,
			success: function(form, action) {
				win.getLoadMask().hide();
				win.hide();
				win.callback(win.owner,action.result.Registry_id);
			}
		});
	},
	setIsZNOVisibility: function() {
		var
			base_form = this.formPanel.getForm(),
			win = this;

		var RegistryGroupType_id = base_form.findField('RegistryGroupType_id').getValue();

		if (!!RegistryGroupType_id && RegistryGroupType_id.inlist([22, 23, 34, 35])) {
			base_form.findField('Registry_IsZNOCheckbox').showContainer();
		}
		else {
			base_form.findField('Registry_IsZNOCheckbox').setValue(false);
			base_form.findField('Registry_IsZNOCheckbox').hideContainer();
		}
		
		base_form.findField('Registry_IsZNOCheckbox').setDisabled(win.action != 'add');
		
	},
	initComponent: function() {
		var win = this;

		this.formPanel = new Ext.form.FormPanel({
			autoHeight: true,
			buttonAlign: 'left',
			frame: true,
			labelAlign: 'right',
			labelWidth: 200,
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
				fieldLabel: 'Номер',
				id: 'UREW_Registry_Num',
				minValue: 1,
				name: 'Registry_Num',
				tabIndex: win.firstTabIndex++,
				width: 250,
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
						var base_form = win.formPanel.getForm();

						if ( !Ext.isEmpty(newValue) && typeof newValue == 'object' ) {
							base_form.findField('Registry_endDate').setMinValue(newValue.format('d.m.Y'));
							newValue.setDate(1);
							base_form.findField('Registry_accDate').setMinValue(newValue.format('d.m.Y'));
						}
						else {
							base_form.findField('Registry_accDate').setMinValue(undefined);
							base_form.findField('Registry_endDate').setMinValue(undefined);
						}
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
						var base_form = win.formPanel.getForm();

						if ( !Ext.isEmpty(newValue) && typeof newValue == 'object' ) {
							base_form.findField('Registry_begDate').setMaxValue(newValue.format('d.m.Y'));
							newValue.setMonth(newValue.getMonth() + 2);
							newValue.setDate(0);
							base_form.findField('Registry_accDate').setMaxValue(newValue.format('d.m.Y'));
						}
						else {
							base_form.findField('Registry_begDate').setMaxValue(undefined);
							base_form.findField('Registry_accDate').setMaxValue(undefined);
						}
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
				fieldLabel: 'Тип объединённого реестра',
				hiddenName: 'RegistryGroupType_id',
				width: 250,
				listWidth: 350,
				loadParams: {params: {where: ' where RegistryGroupType_id in (2, 3, 4, 10, 15, 21, 22, 23, 27, 28, 29, 30, 31, 32, 34, 35)'}},
				comboSubject: 'RegistryGroupType',
				typeCode: 'int',
				xtype: 'swcommonsprcombo',
				tabIndex: win.firstTabIndex++,
				listeners: {
					'change': function(combo, newValue, oldValue) {
						var index = combo.getStore().findBy(function(rec) {
							return rec.get(combo.valueField) == newValue;
						});
						combo.fireEvent('select', combo, combo.getStore().getAt(index), index);
					},
					'select': function(combo, record, index) {
						var base_form = win.formPanel.getForm();

						if ( typeof record == 'object' && record.get('RegistryGroupType_Code').inlist([ 2, 22, 23 ]) ) {
							base_form.findField('Registry_IsOnceInTwoYearsCheckbox').setValue(false);
							base_form.findField('Registry_IsOnceInTwoYearsCheckbox').setContainerVisible(false);
						}
						else if ( typeof record == 'object' && record.get('RegistryGroupType_Code') == 3 ) {
							base_form.findField('Registry_IsOnceInTwoYearsCheckbox').setContainerVisible(true);
						}
						else {
							base_form.findField('Registry_IsOnceInTwoYearsCheckbox').setValue(false);
							base_form.findField('Registry_IsOnceInTwoYearsCheckbox').setContainerVisible(false);
						}

						win.setIsZNOVisibility();
						win.syncSize();
						win.syncShadow();
					}
				}
			}, /*{
				fieldLabel: 'Тест',
				name: 'Registry_IsTestCheckbox',
				tabIndex: win.firstTabIndex++,
				xtype: 'checkbox'
			},*/ {
				fieldLabel: 'Раз в 2 года',
				name: 'Registry_IsOnceInTwoYearsCheckbox',
				tabIndex:  win.firstTabIndex++,
				xtype: 'checkbox'
			}, {
				fieldLabel: 'ЗНО',
				name: 'Registry_IsZNOCheckbox',
				tabIndex: win.firstTabIndex++,
				xtype: 'checkbox'
			}, {
				name: 'Lpu_id',
				xtype: 'hidden'
			}, {
				name: 'Registry_id',
				xtype: 'hidden'
			}, {
				name: 'RegistryCheckStatus_id',
				xtype: 'hidden'
			}, {
				name: 'RegistryCheckStatus_Code',
				xtype: 'hidden'
			}, {
				xtype: 'hidden',
				name: 'Registry_IsOnceInTwoYears'
			}, {
				xtype: 'hidden',
				name: 'Registry_IsZNO'
			}/*, {
				xtype: 'hidden',
				name: 'Registry_IsTest',
				value: 1 // По умолчанию при добавлении
			}*/],
			keys:
				[{
					alt: true,
					fn: function(inp, e)
					{
						switch (e.getKey())
						{
							case Ext.EventObject.C:
								if (this.action != 'view')
								{
									this.doSave();
								}
								break;
							case Ext.EventObject.J:
								this.hide();
								break;
						}
					},
					key: [ Ext.EventObject.C, Ext.EventObject.J ],
					scope: this,
					stopEvent: true
				}],
			reader: new Ext.data.JsonReader(
				{
					success: function()
					{
						//
					}
				},
				[
					{ name: 'Registry_id' },
					{ name: 'Registry_Num' },
					{ name: 'Registry_accDate' },
					{ name: 'Registry_begDate' },
					{ name: 'Registry_endDate' },
					{ name: 'KatNasel_id' },
					{ name: 'RegistryGroupType_id' },
					{ name: 'Lpu_id' },
					{ name: 'RegistryCheckStatus_id' },
					{ name: 'RegistryCheckStatus_Code' },
					{ name: 'Registry_IsZNO' },
					{ name: 'Registry_IsOnceInTwoYears' }/*,
					{ name: 'Registry_IsTest' }*/
				]),
			timeout: 600,
			url: '/?c=Registry&m=saveUnionRegistry'
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.ownerCt.doSave();
				},
				iconCls: 'save16',
				tabIndex: win.firstTabIndex++,
				text: BTN_FRMSAVE
			}, {
				text: '-'
			},
				HelpButton(this),
				{
					iconCls: 'cancel16',
					handler: function() {
						this.ownerCt.hide();
					},
					onTabElement: 'UREW_Registry_Num',
					tabIndex: win.firstTabIndex++,
					text: BTN_FRMCANCEL
				}],
			items: [
				this.formPanel
			]
		});
		sw.Promed.swUnionRegistryEditWindow.superclass.initComponent.apply(this, arguments);
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

		this.doReset();
		this.center();

		var win = this,
			base_form = this.formPanel.getForm();

		base_form.findField('Registry_accDate').setMaxValue(undefined);
		base_form.findField('Registry_accDate').setMinValue(undefined);
		base_form.findField('Registry_begDate').setMaxValue(undefined);
		base_form.findField('Registry_endDate').setMinValue(undefined);

		base_form.setValues(arguments[0]);
		base_form.findField('KatNasel_id').fireEvent('change', base_form.findField('KatNasel_id'), base_form.findField('KatNasel_id').getValue());
		base_form.findField('Registry_IsOnceInTwoYearsCheckbox').setContainerVisible(false);
		if ( arguments[0].MedService_pid ) {
			base_form.findField('MedService_pid').setValue(arguments[0].MedService_pid);
		}


		if(win.action == 'add')
		{
			win.setTitle('Объединённый реестр: Добавление');
			win.enableEdit(true);
			win.setIsZNOVisibility();
			win.syncSize();
			win.doLayout();
			base_form.findField('Registry_begDate').setValue(new Date( (new Date).getFullYear(), 0, 1));
			base_form.findField('Registry_begDate').fireEvent('change', base_form.findField('Registry_begDate'), base_form.findField('Registry_begDate').getValue());
			base_form.findField('Registry_endDate').fireEvent('change', base_form.findField('Registry_endDate'), base_form.findField('Registry_endDate').getValue());
			base_form.findField('Registry_accDate').fireEvent('change', base_form.findField('Registry_accDate'), base_form.findField('Registry_accDate').getValue(), 0);
		}
		else
		{
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

					if ( base_form.findField('RegistryCheckStatus_Code').getValue() == 1 ) {
						win.action = 'view';
					}

					switch (win.action) {
						case 'view':
							win.setTitle('Объединённый реестр: Просмотр');
							break;

						case 'edit':
							win.setTitle('Объединённый реестр: Редактирование');
							win.enableEdit(true);
							//base_form.findField('Registry_IsTestCheckbox').disable();
							break;
					}

					//base_form.findField('Registry_IsTestCheckbox').setValue(base_form.findField('Registry_IsTest').getValue() == 2);
					base_form.findField('Registry_IsOnceInTwoYearsCheckbox').setValue(base_form.findField('Registry_IsOnceInTwoYears').getValue() == 2);
					base_form.findField('Registry_IsZNOCheckbox').setValue(base_form.findField('Registry_IsZNO').getValue() == 2);
					base_form.findField('KatNasel_id').fireEvent('change', base_form.findField('KatNasel_id'), base_form.findField('KatNasel_id').getValue());
					base_form.findField('Registry_accDate').fireEvent('change', base_form.findField('Registry_accDate'), base_form.findField('Registry_accDate').getValue(), 0);
					base_form.findField('Registry_begDate').fireEvent('change', base_form.findField('Registry_begDate'), base_form.findField('Registry_begDate').getValue());
					base_form.findField('Registry_endDate').fireEvent('change', base_form.findField('Registry_endDate'), base_form.findField('Registry_endDate').getValue());
					base_form.findField('RegistryGroupType_id').fireEvent('change', base_form.findField('RegistryGroupType_id'), base_form.findField('RegistryGroupType_id').getValue());
					win.setIsZNOVisibility();
					win.syncSize();
					win.doLayout();
				},
				url: '/?c=Registry&m=loadUnionRegistryEditForm'
			});
		}
	}
});