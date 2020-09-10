Ext6.define('common.Timetable.ScheduleEditWindow', {
	extend: 'base.BaseForm',
	alias: 'widget.swTimetableScheduleEditWindow',
	renderTo: main_center_panel.body.dom,
	autoShow: false,
	cls: 'arm-window-new',
	title: 'Расписание',
	width: 420,
	modal: true,
	
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

		me.mask('Сохранение...');

		baseForm.submit({
			url: '/?c=Timetable6E&m=saveTimetableSchedule',
			success: function(form, action) {
				me.unmask();
				me.callback();
				me.hide();
			},
			failure: function(form, action) {
				me.unmask();
			}
		});
	},
	
	onSprLoad: function() {
		var me = this;
		var baseForm = me.formPanel.getForm();
		var TimetableType_id = baseForm.findField('TimetableType_id').getValue();
		
		baseForm.findField('TimetableType_id').setValue(TimetableType_id || 1);
	},
	
	show: function() {
		var me = this;
		var baseForm = me.formPanel.getForm();
		
		me.callback = Ext6.emptyFn;
	
		me.callParent(arguments);
		
		if (Ext6.isFunction(arguments[0].callback)) {
			me.callback = arguments[0].callback;
		}
		
		baseForm.reset();
		baseForm.setValues(arguments[0].formParams);
	},
	
	initComponent: function() {
		var me = this;
		
		me.formPanel = Ext6.create('Ext6.form.Panel', {
			border: false,
			bodyPadding: '20 20 10 20',
			trackResetOnLoad: false,
			defaults: {
				labelWidth: 170,
				anchor: '100%'
			},
			reader: Ext6.create('Ext6.data.reader.Json', {
				type: 'json',
				model: Ext6.create('Ext6.data.Model', {
					fields: [
						{name: 'MedStaffFact_id'},
						{name: 'Range'},
						{name: 'Duration'},
						{name: 'LpuSection_id'},
						{name: 'BegTime'},
						{name: 'EndTime'}
					]
				})
			}),
			items: [{
				xtype: 'hidden',
				name: 'MedStaffFact_id'
			}, {
				allowBlank: false,
				xtype: 'swDateRangeField',
				name: 'Range',
				fieldLabel: 'Интервал работы'
			}, {
				allowBlank: false,
				xtype: 'commonSprCombo',
				comboSubject: 'TimetableType',
				name: 'TimetableType_id',
				fieldLabel: 'Тип бирки',
				value: 1
			}, {
				allowBlank: false,
				allowDecimals: false,
				minValue: 0,
				xtype: 'numberfield',
				name: 'Duration',
				fieldLabel: 'Длительность приема, мин'
			}, {
				allowBlank: false,
				xtype: 'swTimeField',
				name: 'BegTime',
				fieldLabel: 'Начало работ'
			}, {
				allowBlank: false,
				xtype: 'swTimeField',
				name: 'EndTime',
				fieldLabel: 'Окончание работ'
			}]
		});
		
		Ext6.apply(me, {
			items: [
				me.formPanel
			],
			buttons: [
				'->',
				{
					text: 'Отмена',
					userCls: 'buttonCancel',
					margin: 0,
					handler: function() {
						me.hide();
					}
				}, {
					id: me.getId()+'-save-btn',
					cls: 'buttonAccept',
					margin: '0 19 0 0',
					text: 'Создать',
					handler: function() {
						me.save();
					}
				}
			]
		});
		
		me.callParent(arguments);
	}
});