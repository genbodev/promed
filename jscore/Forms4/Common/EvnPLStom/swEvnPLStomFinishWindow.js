/**
 * swEvnPLStomFinishWindow - Форма зарвешения случая стомат. лечения
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Polka
 * @access       public
 * @copyright    Copyright (c) 2018 Swan Ltd.
 *
 */
Ext6.define('common.EvnPLStom.swEvnPLStomFinishWindow', {
	/* свойства */
	alias: 'widget.swEvnPLStomFinishWindow',
	autoShow: false,
	closable: true,
	cls: 'arm-window-new',
	constrain: true,
	extend: 'base.BaseForm',
	findWindow: false,
	header: true,
	modal: true,
	layout: 'form',
	refId: 'swEvnPLStomFinishWindow',
	resizable: false,
	title: 'Завершение случая лечения',
	width: 800,
	autoHeight: true,
	show: function (data) {
		var win = this;

		if (!data) {
			this.hide();
			Ext6.Msg.alert('Ошибка открытия формы', 'Ошибка открытия формы "'+this.title+'".<br/>Отсутствуют необходимые параметры.');
			return false;
		}

		if (data.callback) {
			win.callback = data.callback;
		} else {
			win.callback = Ext6.emptyFn;
		}

		win.EvnPLStom_id = data.EvnPLStom_id;

		win.callParent(arguments);

		var base_form = win.formPanel.getForm();
		base_form.findField('ResultDeseaseType_id').setContainerVisible(getRegionNick().inlist(['buryatiya', 'ekb', 'kaluga', 'kareliya', 'krasnoyarsk', 'krym', 'penza', 'pskov','vologda', 'yaroslavl']));
		base_form.findField('ResultDeseaseType_id').setAllowBlank(!getRegionNick().inlist(['buryatiya', 'kaluga', 'kareliya', 'krasnoyarsk', 'krym', 'ekb', 'penza', 'pskov','vologda', 'yaroslavl']));

		if ( getRegionNick() == 'krasnoyarsk' ) {
			base_form.findField('ResultDeseaseType_id').getStore().filterBy(function(rec) {
				return (!Ext.isEmpty(rec.get('ResultDeseaseType_Code')) && rec.get('ResultDeseaseType_Code').toString().substr(0, 1) == '3');
			});
		}
	},
	onSprLoad: function(args) {
		var me = this;
		var base_form = me.formPanel.getForm();
		base_form.reset();

		me.mask('Загрузка данных...');
		base_form.load({
			params: {
				EvnPLStom_id: me.EvnPLStom_id
			},
			success: function (form, action) {
				// good
				me.unmask();

				// значения по умолчанию
				base_form.findField('EvnPLStom_IsFinish').setValue(2); // закончен
				if (Ext6.isEmpty(base_form.findField('ResultClass_id').getValue())) {
					base_form.findField('ResultClass_id').setFieldValue('ResultClass_Code', 1); // выздоровление
					base_form.findField('EvnPLStom_UKL').setValue(1); // уровень качества лечения
					me.calcFedLeaveType();
					me.calcFedResultDeseaseType();
				}

				me.formPanel.getForm().isValid(); // чтобы подсветить зеленым обязательные поля
			},
			failure: function (form, action) {
				// not good
			}
		});
	},
	save: function(options) {
		options = options || {};

		var me = this;
		var base_form = me.formPanel.getForm();

		if ( !base_form.isValid() ) {
			Ext6.Msg.show({
				buttons: Ext6.Msg.OK,
				fn: function() {
					me.formPanel.getFirstInvalidEl().focus(false);
				},
				icon: Ext6.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});

			return false;
		}

		var params = {};
		if (base_form.findField('EvnPLStom_IsFinish').disabled) {
			params.EvnPLStom_IsFinish = base_form.findField('EvnPLStom_IsFinish').getValue();
		}

		if (options.params) {
			for (var param in options.params) {
				params[param] = options.params[param];
			}
		} else {
			options.params = {};
		}

		me.mask('Сохранение...');
		base_form.submit({
			url: '/?c=EvnPLStom&m=saveEvnPLStomFinishForm',
			params: params,
			success: function(result_form, action) {
				me.unmask();
				me.callback();
				me.hide();
			},
			failure: function(result_form, action) {
				sw4.showInfoMsg({
					panel: me,
					type: 'error',
					text: 'Ошибка сохранения данных.'
				});
				if ( action.result && action.result.Alert_Msg && 'YesNo' == action.result.Error_Msg ) {
					sw.swMsg.show({
						buttons: Ext.Msg.YESNO,
						fn: function(buttonId, text, obj) {
							if ( buttonId == 'yes' ) {
								options.params[action.result.ignoreParam] = 1;

								me.save(options);
							} else {
								me.unmask();
							}
						}.createDelegate(this),
						icon: Ext.MessageBox.QUESTION,
						msg: action.result.Alert_Msg,
						title: 'Продолжить сохранение?'
					});
				} else {
					me.unmask();
				}
			}
		});
	},
	calcFedLeaveType: function() {
		var me = this;
		var base_form = me.formPanel.getForm();
		sw.Promed.EvnPL.calcFedLeaveType({
			is2016: true,
			disableToogleContainer: false,
			InterruptLeaveType_id: base_form.findField('InterruptLeaveType_id').getValue(),
			LeaveType_fedid: base_form.findField('ResultClass_id').getFieldValue('LeaveType_fedid'),
			ResultClass_id: base_form.findField('ResultClass_id').getValue(),
			ResultClass_Code: base_form.findField('ResultClass_id').getFieldValue('ResultClass_Code'),
			DirectType_Code: base_form.findField('DirectType_id').getFieldValue('DirectType_Code'),
			DirectClass_Code: base_form.findField('DirectClass_id').getFieldValue('DirectClass_Code'),
			IsFinish: base_form.findField('EvnPLStom_IsFinish').getValue(),
			fieldFedLeaveType: base_form.findField('LeaveType_fedid')
		});
	},
	calcFedResultDeseaseType: function() {
		var me = this;
		var base_form = me.formPanel.getForm();
		sw.Promed.EvnPL.calcFedResultDeseaseType({
			is2016: true,
			disableToogleContainer: false,
			InterruptLeaveType_id: base_form.findField('InterruptLeaveType_id').getValue(),
			DirectType_Code: base_form.findField('DirectType_id').getFieldValue('DirectType_Code'),
			ResultClass_Code: base_form.findField('ResultClass_id').getFieldValue('ResultClass_Code') || null,
			IsFinish: base_form.findField('EvnPLStom_IsFinish').getValue(),
			fieldFedResultDeseaseType: base_form.findField('ResultDeseaseType_fedid')
		});
	},
	initComponent: function() {
		var win = this;

		this.formPanel = Ext6.create('Ext6.form.Panel', {
			autoHeight: true,
			border: false,
			url: '/?c=EvnPLStom&m=loadEvnPLStomFinishForm',
			reader: Ext6.create('Ext6.data.reader.Json', {
				type: 'json',
				model: Ext6.create('Ext6.data.Model', {
					fields: [
						{name: 'EvnPLStom_id'},
						{name: 'EvnPLStom_IsFinish'},
						{name: 'EvnPLStom_IsSurveyRefuse'},
						{name: 'ResultClass_id'},
						{name: 'InterruptLeaveType_id'},
						{name: 'ResultDeseaseType_id'},
						{name: 'EvnPLStom_UKL'},
						{name: 'DirectType_id'},
						{name: 'DirectClass_id'},
						{name: 'Diag_lid'},
						{name: 'Diag_concid'},
						{name: 'PrehospTrauma_id'},
						{name: 'EvnPLStom_IsUnlaw'},
						{name: 'EvnPLStom_IsUnport'},
						{name: 'LeaveType_fedid'},
						{name: 'ResultDeseaseType_fedid'}
					]
				})
			}),
			defaults: {
				anchor: '90%',
				labelWidth: 200
			},
			items: [{
				xtype: 'hidden',
				name: 'EvnPLStom_id'
			}, {
				xtype: 'commonSprCombo',
				allowBlank: false,
				comboSubject: 'YesNo',
				value: 2,
				disabled: true,
				listeners: {
					'select': function() {
						win.calcFedLeaveType();
						win.calcFedResultDeseaseType();
					}
				},
				fieldLabel: 'Случай закончен',
				name: 'EvnPLStom_IsFinish'
			}, {
				xtype: 'commonSprCombo',
				comboSubject: 'YesNo',
				listeners: {
					'change': function(combo,newValue,oldValue) {
						var base_form = win.formPanel.getForm();
						var isSan = (newValue && newValue == 2);
						base_form.findField('SanationStatus_id').setVisible(isSan);
						base_form.findField('SanationStatus_id').setDisabled(!isSan);
					}
				},
				fieldLabel: 'Санирован',
				name: 'EvnPLStom_IsSan'
			}, {
				xtype: 'commonSprCombo',
				comboSubject: 'SanationStatus',
				fieldLabel: 'Санация',
				name: 'SanationStatus_id',
				hidden: getRegionNick() == 'kz'
			}, {
				xtype: 'commonSprCombo',
				comboSubject: 'YesNo',
				fieldLabel: 'Отказ от прохождения медицинских обследований',
				name: 'EvnPLStom_IsSurveyRefuse',
				hidden: getRegionNick() == 'kz'
			}, {
				xtype: 'commonSprCombo',
				typeCode: 'int',
				allowBlank: false,
				comboSubject: 'ResultClass',
				listeners: {
					'select': function() {
						win.calcFedLeaveType();
						win.calcFedResultDeseaseType();
					}
				},
				moreFields: [
					{name: 'LeaveType_fedid', type: 'int'}
				],
				fieldLabel: 'Результат лечения',
				name: 'ResultClass_id'
			}, {
				xtype: 'commonSprCombo',
				comboSubject: 'InterruptLeaveType',
				listeners: {
					'select': function() {
						win.calcFedLeaveType();
						win.calcFedResultDeseaseType();
					}
				},
				fieldLabel: 'Случай прерван',
				name: 'InterruptLeaveType_id'
			}, {
				xtype: 'commonSprCombo',
				comboSubject: 'ResultDeseaseType',
				moreFields: [
					{ name: 'ResultDeseaseType_fedid', type: 'int' }
				],
				fieldLabel: langs('Исход'),
				name: 'ResultDeseaseType_id'
			}, {
				xtype: 'numberfield',
				allowBlank: false,
				allowDecimals: true,
				allowNegative: false,
				minValue: 0,
				maxValue: 1,
				fieldLabel: 'УКЛ',
				name: 'EvnPLStom_UKL'
			}, {
				xtype: 'commonSprCombo',
				typeCode: 'int',
				comboSubject: 'DirectType',
				listeners: {
					'select': function() {
						win.calcFedLeaveType();
						win.calcFedResultDeseaseType();
					}
				},
				fieldLabel: 'Направление',
				name: 'DirectType_id'
			}, {
				xtype: 'commonSprCombo',
				comboSubject: 'DirectClass',
				listeners: {
					'select': function() {
						win.calcFedLeaveType();
						win.calcFedResultDeseaseType();
					}
				},
				fieldLabel: 'Куда направлен',
				name: 'DirectClass_id'
			}, {
				xtype: 'swDiagCombo',
				fieldLabel: 'Закл. диагноз',
				listeners: {
					'change': function() {
						var base_form = win.formPanel.getForm();
						var Diag_lid_Code = base_form.findField('Diag_lid').getFieldValue('Diag_Code');
						if (!Ext6.isEmpty(Diag_lid_Code) && Diag_lid_Code.toString().substr(0, 1).inlist(['S', 'T'])) {
							base_form.findField('Diag_concid').setContainerVisible(true);
							base_form.findField('Diag_concid').setAllowBlank(false);
						}
						else {
							base_form.findField('Diag_concid').clearValue();
							base_form.findField('Diag_concid').setContainerVisible(false);
							base_form.findField('Diag_concid').setAllowBlank(true);
						}
					}
				},
				name: 'Diag_lid'
			}, {
				xtype: 'swDiagCombo',
				fieldLabel: 'Закл. внешняя причина',
				name: 'Diag_concid'
			}, {
				xtype: 'commonSprCombo',
				typeCode: 'int',
				comboSubject: 'PrehospTrauma',
				fieldLabel: 'Вид травмы (внеш. возд)',
				name: 'PrehospTrauma_id'
			}, {
				xtype: 'commonSprCombo',
				comboSubject: 'YesNo',
				fieldLabel: 'Противоправная',
				name: 'EvnPLStom_IsUnlaw'
			}, {
				xtype: 'commonSprCombo',
				comboSubject: 'YesNo',
				fieldLabel: 'Нетранспортабельность',
				name: 'EvnPLStom_IsUnport'
			}, {
				xtype: 'commonSprCombo',
				allowBlank: false,
				suffix: 'Fed',
				comboSubject: 'LeaveType',
				USLOV: 3,
				moreFields: [
					{ name: 'LeaveType_USLOV', mapping: 'LeaveType_USLOV' }
				],
				listeners: {
					'change': function() {
						var base_form = win.formPanel.getForm();
						sw.Promed.EvnPL.filterFedResultDeseaseType({
							fieldFedLeaveType: base_form.findField('LeaveType_fedid'),
							fieldFedResultDeseaseType: base_form.findField('ResultDeseaseType_fedid')
						})
					}
				},
				fieldLabel: 'Фед. результат',
				name: 'LeaveType_fedid'
			}, {
				xtype: 'commonSprCombo',
				allowBlank: false,
				suffix: 'Fed',
				comboSubject: 'ResultDeseaseType',
				listeners: {
					'change': function() {
						var base_form = win.formPanel.getForm();
						sw.Promed.EvnPL.filterFedLeaveType({
							fieldFedLeaveType: base_form.findField('LeaveType_fedid'),
							fieldFedResultDeseaseType: base_form.findField('ResultDeseaseType_fedid')
						});
					}
				},
				fieldLabel: 'Фед. исход',
				name: 'ResultDeseaseType_fedid'
			}]
		});

		Ext6.apply(win, {
			layout: 'form',
			items: [
				win.formPanel
			],
			buttons: ['->', {
				handler: function () {
					win.hide();
				},
				text: 'Отмена'
			}, {
				handler: function () {
					win.save();
				},
				cls: 'flat-button-primary',
				text: 'Применить'
			}]
		});

		this.callParent(arguments);
	}
});