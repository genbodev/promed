/**
 * swBleedingCardConditionEditWindow - Форма добавления/редактирования оценки состояния для карты наблюдения за кровотечениями
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://rtmis.ru/
 *
 * @package		Stac
 * @access		public
 * @copyright	Copyright (c) 2019 Swan Ltd.
 * @author		Stanislav Bykov
 * @version		11.12.2019
 */

sw.Promed.swBleedingCardConditionEditWindow = Ext.extend(sw.Promed.BaseForm, {
	/* свойства */
	action: null,
	autoHeight: true,
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	collapsible: false,
	draggable: true,
	formStatus: 'edit',
	id: 'BleedingCardConditionEditWindow',
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var win = Ext.getCmp('BleedingCardConditionEditWindow');

			switch ( e.getKey() ) {
				case Ext.EventObject.C:
					win.doSave();
					break;

				case Ext.EventObject.J:
					win.hide();
					break;
			}
		},
		key: [
			Ext.EventObject.C,
			Ext.EventObject.J
		],
		stopEvent: true
	}],
	listeners: {
		'hide': function(win) {
			win.onHide();
		}
	},
	layout: 'form',
	maximizable: false,
	maximized: false,
	modal: true,
	plain: true,
	resizable: false,
	tabIndexFirst: 960,
	width: 600,

	/* методы */
	callback: Ext.emptyFn,
	doSave: function() {
		var
			win = this,
			form = win.formPanel,
			base_form = form.getForm();

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

		var
			BleedingCardCondition_Temperature = base_form.findField('BleedingCardCondition_Temperature').getValue(),
			BleedingCardCondition_SistolPress = base_form.findField('BleedingCardCondition_SistolPress').getValue(),
			BleedingCardCondition_Pulse = base_form.findField('BleedingCardCondition_Pulse').getValue(),
			BleedingCardCondition_BreathFrequency = base_form.findField('BleedingCardCondition_BreathFrequency').getValue(),
			BleedingCardCondition_TotalScore = 0;

		if ( BleedingCardCondition_Temperature <= 34.9 || BleedingCardCondition_Temperature > 38.5 ) {
			BleedingCardCondition_TotalScore += 3;
		}
		else if ( BleedingCardCondition_Temperature <= 35.9 || BleedingCardCondition_Temperature >= 38 ) {
			BleedingCardCondition_TotalScore += 1;
		}

		if ( BleedingCardCondition_SistolPress <= 79 || BleedingCardCondition_SistolPress >= 200 ) {
			BleedingCardCondition_TotalScore += 3;
		}
		else if ( BleedingCardCondition_SistolPress >= 150 ) {
			BleedingCardCondition_TotalScore += 2;
		}
		else if ( BleedingCardCondition_SistolPress <= 89 || BleedingCardCondition_SistolPress >= 140 ) {
			BleedingCardCondition_TotalScore += 1;
		}

		if ( BleedingCardCondition_Pulse <= 39 || BleedingCardCondition_Pulse >= 130 ) {
			BleedingCardCondition_TotalScore += 3;
		}
		else if ( BleedingCardCondition_Pulse <= 59 || BleedingCardCondition_Pulse >= 110 ) {
			BleedingCardCondition_TotalScore += 2;
		}
		else if ( BleedingCardCondition_Pulse <= 74 || BleedingCardCondition_Pulse >= 105 ) {
			BleedingCardCondition_TotalScore += 1;
		}

		if ( BleedingCardCondition_BreathFrequency <= 4 || BleedingCardCondition_BreathFrequency >= 30 ) {
			BleedingCardCondition_TotalScore += 3;
		}
		else if ( BleedingCardCondition_BreathFrequency <= 9 || BleedingCardCondition_BreathFrequency >= 25 ) {
			BleedingCardCondition_TotalScore += 2;
		}
		else if ( BleedingCardCondition_BreathFrequency <= 14 || BleedingCardCondition_BreathFrequency >= 20 ) {
			BleedingCardCondition_TotalScore += 1;
		}

		BleedingCardCondition_TotalScore += base_form.findField('Diuresis_id').getFieldValue('Diuresis_Score');
		BleedingCardCondition_TotalScore += base_form.findField('CentralNervousSystem_id').getFieldValue('CentralNervousSystem_Score');
		BleedingCardCondition_TotalScore += base_form.findField('PulseOximetry_id').getFieldValue('PulseOximetry_Score');

		win.callback({
			'BleedingCardCondition_id': base_form.findField('BleedingCardCondition_id').getValue(),
			'BleedingCardCondition_setDate': base_form.findField('BleedingCardCondition_setDate').getValue().format('d.m.Y'),
			'BleedingCardCondition_setTime': base_form.findField('BleedingCardCondition_setTime').getValue(),
			'BleedingCardCondition_SistolPress': BleedingCardCondition_SistolPress,
			'BleedingCardCondition_DiastolPress': base_form.findField('BleedingCardCondition_DiastolPress').getValue(),
			'Diuresis_id': base_form.findField('Diuresis_id').getValue(),
			'CentralNervousSystem_id': base_form.findField('CentralNervousSystem_id').getValue(),
			'PulseOximetry_id': base_form.findField('PulseOximetry_id').getValue(),
			'BleedingCardCondition_setDT': getValidDT(base_form.findField('BleedingCardCondition_setDate').getValue().format('d.m.Y'), base_form.findField('BleedingCardCondition_setTime').getValue()),
			'BleedingCardCondition_Temperature': BleedingCardCondition_Temperature,
			'BleedingCardCondition_Pressure': BleedingCardCondition_SistolPress + ' / ' + base_form.findField('BleedingCardCondition_DiastolPress').getValue(),
			'BleedingCardCondition_Pulse': BleedingCardCondition_Pulse,
			'BleedingCardCondition_BreathFrequency': BleedingCardCondition_BreathFrequency,
			'Diuresis_Name': base_form.findField('Diuresis_id').getFieldValue('Diuresis_Name'),
			'BleedingCardCondition_CatheterTime': base_form.findField('BleedingCardCondition_CatheterTime').getValue(),
			'CentralNervousSystem_Name': base_form.findField('CentralNervousSystem_id').getFieldValue('CentralNervousSystem_Name'),
			'PulseOximetry_Name': base_form.findField('PulseOximetry_id').getFieldValue('PulseOximetry_Name'),
			'BleedingCardCondition_TotalScore': BleedingCardCondition_TotalScore
		});
		win.hide();

		return true;
	},
	onHide: Ext.emptyFn,
	show: function(params) {
		sw.Promed.swBleedingCardConditionEditWindow.superclass.show.apply(this, arguments);

		if ( !arguments[0] || !arguments[0].formParams ) {
			sw.swMsg.alert(langs('Сообщение'), langs('Неверные параметры'), function() { this.hide(); }.createDelegate(this) );
			return false;
		}

		var base_form = this.formPanel.getForm();
		base_form.reset();

		this.action = arguments[0].action || null;
		this.callback = arguments[0].callback || Ext.emptyFn;
		this.formStatus = 'edit';
		this.onHide = arguments[0].onHide || Ext.emptyFn;

		var loadMask = new Ext.LoadMask(this.getEl(),{msg: LOAD_WAIT});
		loadMask.show();

		base_form.setValues(arguments[0].formParams);

		switch ( this.action ) {
			case 'add':
				this.setTitle(langs('Оценка состояния') + ': ' + langs('Добавление'));
				this.enableEdit(true);
				base_form.findField('BleedingCardCondition_setDate').focus(true);
				break;

			case 'edit':
				this.setTitle(langs('Оценка состояния') + ': ' + langs('Редактирование'));
				this.enableEdit(true);
				this.formPanel.getForm().findField('BleedingCardCondition_setDate').focus(true);
				break;

			case 'view':
				this.setTitle(langs('Оценка состояния') + ': ' + langs('Просмотр'));
				this.enableEdit(false);
				this.buttons[this.buttons.length - 1].focus();
				break;
		}

		loadMask.hide();
	},

	/* конструктор */
	initComponent: function() {
		var form = this;

		form.formPanel = new Ext.form.FormPanel({
			autoHeight: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: false,
			id: 'BleedingCardConditionEditForm',
			labelAlign: 'right',
			labelWidth: 170,
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			},  [
				{ name: 'BleedingCardCondition_id' },
				{ name: 'RecordStatus_Code' },
				{ name: 'BleedingCardCondition_setDate' },
				{ name: 'BleedingCardCondition_setTime' },
				{ name: 'BleedingCardCondition_SistolPress' },
				{ name: 'BleedingCardCondition_DiastolPress' },
				{ name: 'Diuresis_id' },
				{ name: 'CentralNervousSystem_id' },
				{ name: 'PulseOximetry_id' },
				{ name: 'BleedingCardCondition_Temperature' },
				{ name: 'BleedingCardCondition_Pulse' },
				{ name: 'BleedingCardCondition_BreathFrequency' },
				{ name: 'BleedingCardCondition_CatheterTime' },
				{ name: 'BleedingCardCondition_TotalScore' }
			]),
			url: '/?c=BleedingCard&m=saveBleedingCardCondition',
			items: [{
				name: 'BleedingCardCondition_id',
				xtype: 'hidden'
			}, {
				name: 'RecordStatus_Code',
				xtype: 'hidden'
			}, {
				border: false,
				layout: 'column',
				items: [{
					border: false,
					layout: 'form',
					items: [{
						allowBlank: false,
						fieldLabel: langs('Дата'),
						format: 'd.m.Y',
						name: 'BleedingCardCondition_setDate',
						plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
						selectOnFocus: true,
						tabIndex: form.tabIndexFirst++,
						width: 100,
						xtype: 'swdatefield'
					}]
				}, {
					border: false,
					labelWidth: 50,
					layout: 'form',
					items: [{
						allowBlank: false,
						fieldLabel: langs('Время'),
						listeners: {
							'keydown': function (inp, e) {
								if ( e.getKey() == Ext.EventObject.F4 ) {
									e.stopEvent();
									inp.onTriggerClick();
								}
							}
						},
						name: 'BleedingCardCondition_setTime',
						onTriggerClick: function() {
							var
								base_form = form.formPanel.getForm(),
								time_field = this;

							if ( time_field.disabled ) {
								return false;
							}

							setCurrentDateTime({
								dateField: base_form.findField('BleedingCardCondition_setDate'),
								loadMask: true,
								setDate: true,
								setDateMaxValue: true,
								setDateMinValue: false,
								setTime: true,
								timeField: time_field,
								windowId: form.id
							});
						},
						plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
						tabIndex: form.tabIndexFirst++,
						validateOnBlur: false,
						width: 60,
						xtype: 'swtimefield'
					}]
				}]
			}, {
				allowBlank: false,
				allowDecimals: true,
				allowNegative: false,
				decimalPrecision: 1,
				enableKeyEvents: true,
				fieldLabel: langs('Температура'),
				name: 'BleedingCardCondition_Temperature',
				tabIndex: form.tabIndexFirst++,
				width: 100,
				xtype: 'numberfield'
			}, {
				allowBlank: false,
				allowDecimals: false,
				allowNegative: false,
				enableKeyEvents: true,
				fieldLabel: langs('Систолическое АД'),
				name: 'BleedingCardCondition_SistolPress',
				tabIndex: form.tabIndexFirst++,
				width: 100,
				xtype: 'numberfield'
			}, {
				allowBlank: false,
				allowDecimals: false,
				allowNegative: false,
				enableKeyEvents: true,
				fieldLabel: langs('Диастолическое АД'),
				name: 'BleedingCardCondition_DiastolPress',
				tabIndex: form.tabIndexFirst++,
				width: 100,
				xtype: 'numberfield'
			}, {
				allowDecimals: false,
				allowNegative: false,
				enableKeyEvents: true,
				fieldLabel: langs('Пульс'),
				name: 'BleedingCardCondition_Pulse',
				tabIndex: form.tabIndexFirst++,
				width: 100,
				xtype: 'numberfield'
			}, {
				allowBlank: false,
				allowDecimals: false,
				allowNegative: false,
				enableKeyEvents: true,
				fieldLabel: langs('Частота дыхания (в мин.)'),
				name: 'BleedingCardCondition_BreathFrequency',
				tabIndex: form.tabIndexFirst++,
				width: 100,
				xtype: 'numberfield'
			}, {
				allowBlank: false,
				anchor: '95%',
				comboSubject: 'Diuresis',
				fieldLabel: langs('Диурез'),
				hiddenName: 'Diuresis_id',
				moreFields: [
					{ name: 'Diuresis_Score', mapping: 'Diuresis_Score', type: 'int' }
				],
				tabIndex: form.tabIndexFirst++,
				xtype: 'swcommonsprcombo'
			}, {
				fieldLabel: langs('Время катетеризации'),
				name: 'BleedingCardCondition_CatheterTime',
				plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
				tabIndex: form.tabIndexFirst++,
				width: 60,
				xtype: 'textfield'
			}, {
				allowBlank: false,
				anchor: '95%',
				comboSubject: 'CentralNervousSystem',
				fieldLabel: langs('ЦНС'),
				hiddenName: 'CentralNervousSystem_id',
				moreFields: [
					{ name: 'CentralNervousSystem_Score', mapping: 'CentralNervousSystem_Score', type: 'int' }
				],
				tabIndex: form.tabIndexFirst++,
				xtype: 'swcommonsprcombo'
			}, {
				allowBlank: false,
				anchor: '95%',
				comboSubject: 'PulseOximetry',
				fieldLabel: 'SpO<sub>2in</sub>',
				hiddenName: 'PulseOximetry_id',
				moreFields: [
					{ name: 'PulseOximetry_Score', mapping: 'PulseOximetry_Score', type: 'int' }
				],
				tabIndex: form.tabIndexFirst++,
				xtype: 'swcommonsprcombo'
			}]
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					form.doSave();
				},
				iconCls: 'save16',
				onShiftTabAction: function () {
					if ( form.action == 'view' ) {
						form.buttons[form.buttons.length - 1].focus(true);
					}
					else {
						form.formPanel.getForm().findField('BleedingCardCondition_setDate').focus(true);
					}
				},
				onTabAction: function () {
					form.buttons[form.buttons.length - 2].focus(true);
				},
				tabIndex: form.tabIndexFirst++,
				text: BTN_FRMSAVE
			}, {
				text: '-'
			},
			HelpButton(form, -1),
			{
				handler: function() {
					form.hide();
				},
				iconCls: 'cancel16',
				onShiftTabAction: function () {
					form.buttons[form.buttons.length - 2].focus(true);
				},
				onTabAction: function () {
					if ( form.action == 'view' ) {
						form.buttons[form.buttons.length - 2].focus(true);
					}
					else {
						form.formPanel.getForm().findField('BleedingCardCondition_Volume').focus(true);
					}
				},
				tabIndex: form.tabIndexFirst++,
				text: BTN_FRMCANCEL
			}],
			items: [
				form.formPanel
			]
		});

		sw.Promed.swBleedingCardConditionEditWindow.superclass.initComponent.apply(this, arguments);
	}
});