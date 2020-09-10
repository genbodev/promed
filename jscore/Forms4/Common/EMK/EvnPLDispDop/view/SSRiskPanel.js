Ext6.define('common.EMK.EvnPLDispDop.view.SSRiskPanel', {
	extend: 'swPanel',
	requires: [
		'common.EMK.EvnPLDispDop.controller.SSRiskController',
	],
	alias: 'widget.EvnPLDispDop_SSRiskPanel',
	userCls: 'panel-with-tree-dots accordion-panel-window',
	bind: {
		title: '{ "Сердечно-сосудистый риск  " + (!!EvnPLDispDop13_SumRick ? EvnPLDispDop13_SumRick + "% ("+getRiskTypeName+")" : "") }'
	},
	controller: 'EvnPLDispDop13SSRiskController',
	parentPanel: {},
	bodyPadding: 10,
	isLoaded: false,
	autoLoad: true,
	listeners: {
		expand: 'onExpand'
	},
	load: function() {
		this.getController().load();
	},
	setParams: function(record) {
		this.getController().setParams(record);
	},
	openModalForm: function () {
		let me = this;
		let vm = me.ownerPanel.getViewModel();
		let EvnPLDispDop13_id = vm.get('EvnPLDispDop13_id');
		getWnd('swEvnUslugaDispDop13EditWindowExt6').show({
			needLoad: true,
			params: {
				panelCode: 'SSRiskPanel',
				EvnPLDispDop13_id: EvnPLDispDop13_id
			},
			callback: function (data) {

			}
		});
	},
	initComponent: function() {
		var me = this;

		me.DataForm = Ext6.create('Ext6.form.Panel', {
			accessType: 'view',
			padding: "0 0 0 27",
			layout: 'anchor',
			border: false,
			defaults: {
				width: 350,
				labelWidth: 210,
				margin: '0 0 -10 0'
			},
			items: [
				{
					xtype: 'label',
					html: '<span style="font-size: 1.1em;">Рассчитан по показателям:</span>'
				}, {
					xtype: 'displayfield',
					fieldLabel: 'Возраст:',
					disabled: true,
					style: 'padding-top: 15px;',
					bind: {
						value : '{Person_Age} г.'
					}
				}, {
					xtype: 'displayfield',
					fieldLabel: 'Пол:',
					disabled: true,
					bind: {
						value: '{Sex_Name}'
					}
				}, {
					xtype: 'displayfield',
					fieldLabel: 'Курение:',
					disabled: true,
					name: 'EvnPLDispDop13_IsSmoking',
					bind: {
						value: '{isSmokingLabel}'
					}
				}, {
					xtype: 'numberfield',
					fieldLabel: 'Уровень холестерина (ммоль/л):',
					hideTrigger: true,
					//triggerWrapCls: 'hiddenTriggerWrap', //отключение рамки у numberfield
					name: 'total_cholesterol',
					//~ allowBlank: false,
					bind: {
						value: '{total_cholesterol}',
						disabled: '{action == "view"}'
					},
					prevValue: '',
					listeners: {
						focusLeave: 'saveCholesterol'
					}
				}, {
					xtype: 'displayfield',
					fieldLabel: 'Артериальное давление:',
					disabled: true,
					margin: '10 0 0 0',
					bind: '{systolic_blood_pressure}/{diastolic_blood_pressure}'
				},
				//скрытые поля(нужны для создания услуги)
				{
					xtype: 'hidden',
					bind: '{EvnPLDispDop13_SumRick}',
					name: 'EvnPLDispDop13_SumRick',
					/*listeners: {
						'loadScore': function (r_type, value) {
							if(value) {
								var risk_status = '';
								if(r_type == 1) risk_status = 'низкий';
								else if(r_type == 2) risk_status = 'умеренный';
								else if(r_type == 3) risk_status = 'высокий';
								else if (r_type == 4) risk_status = 'очень высокий';

								me.setTitle('Сердечно-сосудистый риск <b>' + value + '% (' + risk_status + ')</b>');
							}
						}
					}*/
				},
				{
					xtype: 'hidden',
					bind: '{RiskType_id}',
					name: 'RiskType_id'
				},
				{
					name: 'UslugaComplex_id',
					xtype: 'hidden'
				}, {
					name: 'EvnUslugaDispDop_id',
					value: 0,
					xtype: 'hidden'
				}, {
					name: 'EvnVizitDispDop_id',
					xtype: 'hidden'
				}, {
					name: 'EvnVizitDispDop_pid',
					xtype: 'hidden'
				}, {
					name: 'DopDispInfoConsent_id',
					xtype: 'hidden'
				}, {
					name: 'EvnDirection_id',
					xtype: 'hidden'
				}, {
					name: 'PersonEvn_id',
					bind: '{PersonEvn_id}',
					xtype: 'hidden'
				}, {
					name: 'MedPersonal_id',
					xtype: 'hidden'
				}, {
					name: 'CytoMedPersonal_id',
					xtype: 'hidden'
				}, {
					name: 'Server_id',
					bind: {
						value: '{Server_id}'
					},
					xtype: 'hidden'
				}, {
					name: 'XmlTemplate_id',
					xtype: 'hidden'
				}, {//тип
					name: 'EvnDirection_Type',
					xtype: 'hidden'
				}, {//Дата создания
					name: 'EvnDirection_insDate',
					xtype: 'hidden'
				}, {//Номер направления
					name: 'EvnDirection_Num',
					xtype: 'hidden'
				}, {//Место оказания
					name: 'EvnDirection_RecTo',
					xtype: 'hidden'
				}, {//Дата записи
					name: 'EvnDirection_RecDate',
					xtype: 'hidden'
				}, {
					name: 'EvnUslugaDispDop_setDate',
					xtype: 'datefield',
					format: 'd.m.Y',
					hidden: true
				}, {
					name: 'EvnUslugaDispDop_didDate',
					xtype: 'datefield',
					format: 'd.m.Y',
					hidden: true
				}, {
					name: 'EvnUslugaDispDop_disDate',
					xtype: 'datefield',
					format: 'd.m.Y',
					hidden: true
				}, {
					name: 'EvnUslugaDispDop_setTime',
					xtype: 'hidden'
				}, {
					name: 'EvnUslugaDispDop_didTime',
					xtype: 'hidden'
				}, {
					name: 'EvnUslugaDispDop_disTime',
					xtype: 'hidden'
				}, {
					name: 'Diag_id',
					value: 10944, //Z10.8 Рутинная общая проверка здоровья
					xtype: 'hidden'
				}, {
					name: 'LpuSection_id',
					xtype: 'hidden'
				},  {
					name: 'MedStaffFact_id',
					xtype: 'hidden'
				}, {
					name: 'ExaminationPlace_id',
					xtype: 'hidden'
				}],
			reader: Ext6.create('Ext6.data.reader.Json', {
				type: 'json',
				model: Ext6.create('Ext6.data.Model', {
					fields:[
						{name: 'EvnUslugaDispDop_id'},
						{name: 'EvnVizitDispDop_id'},
						{name: 'EvnVizitDispDop_pid'},
						{name: 'DopDispInfoConsent_id'},
						{name: 'EvnDirection_id'},
						{name: 'PersonEvn_id'},
						{name: 'MedPersonal_id'},
						{name: 'CytoMedPersonal_id'},
						{name: 'Server_id'},
						{name: 'XmlTemplate_id'},
						{name: 'EvnDirection_Type'},
						{name: 'EvnDirection_insDate'},
						{name: 'EvnDirection_Num'},
						{name: 'EvnDirection_RecTo'},
						{name: 'EvnDirection_RecDate'},
					]
				})
			})
		});
		me.tools = [
			{
				xtype: 'displayfield',
				cls:'toolDisplayField',
				itemId: 'status'+me.SurveyType_Code,
				bind: {
					value: '{ MedPersonal_SurveyType_Code'+me.SurveyType_Code+' +" • "+ LpuNick_SurveyType_Code'+me.SurveyType_Code+' +" • "+ Date_SurveyType_Code'+me.SurveyType_Code+' }',
					hidden: '{!MedPersonal_SurveyType_Code'+me.SurveyType_Code+' && !LpuNick_SurveyType_Code'+me.SurveyType_Code+' && !Date_SurveyType_Code'+me.SurveyType_Code+'}'
				},
				fieldLabel: '',
				value: '',
			},
			{	xtype: 'tbspacer', width: 15},
			{
				type: 'gear',
				bind: {
					hidden: '{!Date_SurveyType_Code'+me.SurveyType_Code+'}'
				},
				handler: 'openModalForm'
			}
		];

		Ext6.apply(me, {
			items: [
				me.DataForm
			]
		});

		this.callParent(arguments);
	}
});