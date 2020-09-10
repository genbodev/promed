/**
 * swExemptVaccineWindow - окно выбора медотвода/отказа от вакинации
 *
 */

Ext6.define('common.EMK.Vaccination.swExemptVaccineWindow', {
	extend: 'base.BaseForm',
	alias: 'widget.swExemptVaccineWindow',
	autoShow: false,
	cls: 'arm-window-new save-template-window arm-window-new-without-padding',
	title: 'Медотвод/Отказ от вакцинации',
	renderTo: main_center_panel.body.dom,
	width: 520,
	modal: true,

	apply: function() {
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

		me.callback(baseForm.getValues());
		me.hide();
	},

	show: function() {
		var me = this;
		var baseForm = me.formPanel.getForm();

		me.callback = Ext6.emptyFn;

		me.callParent(arguments);

		baseForm.reset();

		if (arguments[0] && arguments[0].formParams) {
			baseForm.setValues(arguments[0].formParams);
		}
		if (arguments[0] && arguments[0].callback) {
			me.callback = arguments[0].callback;
		}
	},

	initComponent: function() {
		var me = this;
		var labelWidth = 140;

		me.formPanel = Ext6.create('Ext6.form.Panel', {
			border: false,
			bodyPadding: '20 20 20 20',
			trackResetOnLoad: false,
			defaults: {
				anchor: '100%',
				labelWidth: labelWidth
			},
			items: [{
				allowBlank: false,
				xtype: 'baseCombobox',
				name: 'align',
				fieldLabel: 'Медотвод/Отказ',
				editable: false,
				forceSelection: true,
				triggerAction: 'all',
				valueField: 'id',
				displayField: 'name',
				store: new Ext6.create('Ext6.data.Store', {
					fields: [
						{name: 'id', type: 'string'},
						{name: 'name', type: 'string'}
					],
					autoLoad: false,
					proxy: {
						type: 'ajax',
						actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
						url : '/?c=ExemptVaccine&m=getPersonVaccinationRefuseTypeList',
						reader: {
							type: 'json',
							rootProperty: 'data',
						}
					},
					mode: 'local',
				})
			},
			{
				allowBlank: false,
				xtype: 'baseCombobox',
				name: 'align',
				fieldLabel: 'Блок «Данные отказа»',
				editable: false,
				forceSelection: true,
				triggerAction: 'all',
				valueField: 'id',
				displayField: 'name',
				queryMode: 'local',
				store: {
					fields: [
						{name: 'id', type: 'string'},
						{name: 'name', type: 'string'}
					],
					data: [
						{id: '1', name: 'Причина 1'},
						{id: '2', name: 'Причина 2'},
						{id: '3', name: 'Причина 3'}
					]
				}
			},{
				allowBlank: false,
				xtype: 'baseCombobox',
				name: 'align',
				fieldLabel: 'Вид прививки',
				editable: false,
				forceSelection: true,
				triggerAction: 'all',
				valueField: 'id',
				displayField: 'name',
				queryMode: 'local',
				store: {
					fields: [
						{name: 'id', type: 'string'},
						{name: 'name', type: 'string'}
					],
					data: [
						{id: '1', name: 'Причина 1'},
						{id: '2', name: 'Причина 2'},
						{id: '3', name: 'Причина 3'}
					]
				}
			},{
				allowBlank: false,
				xtype: 'baseCombobox',
				name: 'align',
				fieldLabel: 'Прививка',
				editable: false,
				forceSelection: true,
				triggerAction: 'all',
				valueField: 'id',
				displayField: 'name',
				queryMode: 'local',
				store: {
					fields: [
						{name: 'id', type: 'string'},
						{name: 'name', type: 'string'}
					],
					data: [
						{id: '1', name: 'Причина 1'},
						{id: '2', name: 'Причина 2'},
						{id: '3', name: 'Причина 3'}
					]
				}
			},{
				allowBlank: false,
				xtype: 'datefield',
				name: 'startDate',
				fieldLabel: 'Начало отвода',
				editable: false,
				forceSelection: true,
				triggerAction: 'all',
				queryMode: 'local',
				anchor: '100%',
				format: 'd m Y',
				value: '02 04 2020'
			},{
				allowBlank: false,
				xtype: 'datefield',
				name: 'endDate',
				fieldLabel: 'Окончание отвода',
				editable: false,
				forceSelection: true,
				triggerAction: 'all',
				valueField: 'id',
				displayField: 'name',
				queryMode: 'local',
				format: 'd m Y',
				value: '02 04 2020'
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
					margin: 0,
					handler: function() {
						me.hide();
					}
				}, {
					cls: 'buttonAccept',
					text: 'Сохранить',
					margin: '0 19 0 0',
					handler: function() {
						me.apply();
					}
				}
			]
		});

		me.callParent(arguments);
	}
});