Ext6.define('common.EMK.EvnCourseProcEditWindow', {
	extend: 'base.BaseForm',
	alias: 'widget.EvnCourseProcEditWindow',
	renderTo: main_center_panel.body.dom,
	autoShow: false,
	cls: 'arm-window-new',
	title: 'Назначения: Манипуляции и процедуры',
	width: 640,
	height: 500,
	modal: true,

	listeners: {
		beforehide: function() {
			var me = this;
			me.callback(me.response,'EvnCourseProc');
		}
	},

	save: function() {
		var me = this;
		var baseForm = me.formPanel.getForm();

		if (!baseForm.isValid()) {
			Ext6.Msg.show({
				buttons: Ext6.Msg.OK,
				fn: function() {
					me.formPanel.getFirstInvalidEl().focus();
				},
				icon: Ext6.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return;
		}

		me.mask('Сохранение');

		baseForm.submit({
			success: function(form, action) {
				me.unmask();
				me.response = action.result;
				me.hide();
			},
			failure: function(form, action) {
				me.unmask();
			}
		});
	},

	onSprLoad: function(args) {
		var me = this;
		var baseForm = me.formPanel.getForm();
		var uslugaCombo = baseForm.findField('UslugaComplex_id');

		var data = args[0].formParams;

		data.DurationType_id = 1;
		data.DurationType_recid = 1;
		data.DurationType_intid = 1;
		baseForm.setValues(data);

		if (!data.UslugaComplex_id) {
			uslugaCombo.enable();
		} else {
			uslugaCombo.disable();
			uslugaCombo.store.load({
				params: {UslugaComplex_id: data.UslugaComplex_id},
				callback: function() {
					uslugaCombo.setValue(data.UslugaComplex_id);
				}
			});
		}
	},

	show: function() {
		var me = this;
		var baseForm = me.formPanel.getForm();

		me.callback = Ext6.emptyFn;
		me.response = null;

		baseForm.reset();

		me.callParent(arguments);

		if (arguments[0].callback) {
			me.callback = arguments[0].callback;
		}

		baseForm.isValid();
	},

	initComponent: function() {
		var me = this;
		me.callback = this.callback || Ext.emptyFn;
		var labelWidth = 140;

		me.formPanel = Ext6.create('Ext6.form.Panel', {
			border: false,
			bodyPadding: 20,
			defaults: {
				labelWidth: labelWidth,
				matchFieldWidth: false
			},
			url: '/?c=EvnPrescr&m=saveEvnCourseProc',
			/*reader: Ext6.create('Ext6.data.reader.Json', {
				type: 'json',
				model: Ext6.create('Ext6.data.Model', {
					fields: [
						{name: 'EvnCourseProc_id'},
						{name: 'EvnCourseProc_pid'},
						{name: 'UslugaComplex_id'},
						{name: 'StudyTarget_id'},
						{name: 'EvnCourseProc_setDate'},
						{name: 'EvnCourseProc_setTime'},
						{name: 'EvnCourseProc_Duration'},
						{name: 'DurationType_id'},
						{name: 'PayType_id'},
						{name: 'EvnPrescrProc_Descr'},
						{name: 'EvnPrescrProc_IsCito'}
					]
				})
			}),*/
			items: [{
				xtype: 'hidden',
				name: 'EvnCourseProc_id'
			}, {
				xtype: 'hidden',
				name: 'EvnCourseProc_pid'
			}, {
				xtype: 'hidden',
				name: 'PersonEvn_id'
			}, {
				xtype: 'hidden',
				name: 'Server_id'
			}, {
				xtype: 'hidden',
				name: 'parentEvnClass_SysNick'
			}, {
				xtype: 'hidden',
				name: 'MedPersonal_id'
			}, {
				xtype: 'hidden',
				name: 'MedService_id'
			}, {
				xtype: 'hidden',
				name: 'LpuSection_id'
			}, {
				allowBlank: false,
				xtype: 'swUslugaComplexCombo',
				name: 'UslugaComplex_id',
				fieldLabel: 'Услуга',
				width: 550
			}, {
				allowBlank: false,
				xtype: 'commonSprCombo',
				comboSubject: 'StudyTarget',
				name: 'StudyTarget_id',
				displayCode: false,
				fieldLabel: 'Цель исследования',
				width: 450
			}, {
				layout: 'hbox',
				border: false,
				style: 'margin-bottom: 5px;',
				items: [{
					allowBlank: false,
					xtype: 'datefield',
					format: 'd.m.Y',
					plugins: [new Ext6.ux.InputTextMask('99.99.9999', false)],
					name: 'EvnCourseProc_setDate',
					fieldLabel: 'Начать',
					labelWidth: labelWidth,
					width: 270
				}, {
					allowBlank: false,
					xtype: 'swTimeField',
					name: 'EvnCourseProc_setTime',
					style: 'margin-left: 10px;',
					width: 100
				}]
			}, {
				allowDecimals: false,
				allowNegative: false,
				fieldLabel: lang['povtorov_v_sutki'],
				value: 1,
				minValue: 1,
				name: 'EvnCourseProc_MaxCountDay',
				width: 220,
				labelWidth: labelWidth,
				xtype: 'numberfield'
			}, {
				layout: 'hbox',
				border: false,
				style: 'margin-bottom: 5px;',
				items: [{
					allowBlank: false,
					allowNegative: false,
					allowDecimals: false,
					xtype: 'numberfield',
					name: 'EvnCourseProc_Duration',
					minValue: 1,
					value: 1,
					fieldLabel: 'Продолжать',
					hideTrigger: true,
					labelWidth: labelWidth,
					width: 220
				}, {
					allowBlank: false,
					xtype: 'commonSprCombo',
					comboSubject: 'DurationType',
					name: 'DurationType_id',
					displayCode: false,
					style: 'margin-left: 10px;',
					width: 110
				}, {
					xtype: 'checkboxfield',
					name: 'PayType_id',
					boxLabel: 'До выписки',
					style: 'margin-left: 10px;',
					listeners: {
						change: function (combo, newValue) {
							var baseForm = me.formPanel.getForm();
							baseForm.findField('EvnCourseProc_Duration').setDisabled(!!newValue);
							baseForm.findField('DurationType_id').setDisabled(!!newValue);
						}
					}
				}]
			},{
				layout: 'hbox',
				border: false,
				style: 'margin-bottom: 5px;',
				items: [{
					allowBlank: false,
					allowNegative: false,
					allowDecimals: false,
					xtype: 'numberfield',
					name: 'EvnCourseProc_ContReception',
					minValue: 1,
					value: 1,
					fieldLabel: lang['povtoryat_nepreryivno'],
					hideTrigger: true,
					labelWidth: labelWidth,
					width: 220
				}, {
					allowBlank: false,
					xtype: 'commonSprCombo',
					comboSubject: 'DurationType',
					name: 'DurationType_recid',
					displayCode: false,
					style: 'margin-left: 10px;',
					width: 110
				}]
			},{
				layout: 'hbox',
				border: false,
				style: 'margin-bottom: 5px;',
				items: [{
					allowBlank: false,
					allowNegative: false,
					allowDecimals: false,
					xtype: 'numberfield',
					name: 'EvnCourseProc_Interval',
					minValue: 0,
					value: 0,
					fieldLabel: lang['pereryiv'],
					hideTrigger: true,
					labelWidth: labelWidth,
					width: 220
				}, {
					allowBlank: false,
					xtype: 'commonSprCombo',
					comboSubject: 'DurationType',
					name: 'DurationType_intid',
					displayCode: false,
					style: 'margin-left: 10px;',
					width: 110
				}]
			}, {
				xtype: 'textarea',
				name: 'EvnPrescrProc_Descr',
				fieldLabel: 'Комментарий',
				width: 450
			}, {
				xtype: 'checkboxfield',
				name: 'EvnPrescrProc_IsCito',
				boxLabel: 'Cito!',
				style: 'margin-left: '+(labelWidth+4)+'px;'
			}]
		});

		Ext6.apply(me, {
			items: [
				me.formPanel
			],
			buttons: [
				'->',
				{
					cls: 'buttonCancel',
					text: 'Отмена',
					handler: function() {
						me.hide();
					}
				}, {
					cls: 'buttonAccept',
					text: 'Применить',
					handler: function() {
						me.save();
					}
				}
			]
		});

		me.callParent(arguments);
	}
});