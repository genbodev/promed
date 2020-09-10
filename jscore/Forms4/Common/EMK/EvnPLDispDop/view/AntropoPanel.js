Ext6.define('common.EMK.EvnPLDispDop.view.AntropoPanel', {
	extend: 'swPanel',
	requires: [
		'common.EMK.EvnPLDispDop.controller.AntropoController',
	],
	alias: 'widget.EvnPLDispDop_AntropoPanel',
	userCls: 'panel-with-tree-dots accordion-panel-window',
	title: 'Антропология',
	controller: 'EvnPLDispDop13AntropoController',
	ownerPanel: {},
	bodyPadding: 10,
	isLoaded: false,
	setParams: function(record) {//вызов из onLoadConsentGrid
		this.getController().setParams(record);
	},
	listeners: {
		expand: 'onExpand'
	},
	initComponent: function() {
		let me = this;
		me.DataForm = Ext6.create('Ext6.form.Panel', {
			accessType: 'view',
			layout: 'anchor',
			border: false,
			defaults: {
				width: 250,
				labelWidth: 170,
				labelAlign: 'right'
			},
			items: [
				{
					xtype: 'numberfield',
					fieldLabel: 'Вес (кг)',
					name: 'person_weight',
					bind: {
						value: '{person_weight}',
						disabled: '{action == "view" }'
					},
					hideTrigger: true,
					listeners: {
						blur: 'doSave'
					}
				}, {
					xtype: 'numberfield',
					fieldLabel: 'Рост (см)',
					name: 'person_height',
					bind: {
						value: '{person_height}',
						disabled: '{action == "view"}'
					},
					hideTrigger: true,
					listeners: {
						blur: 'doSave'
					}
				}, {
					xtype: 'numberfield',
					fieldLabel: 'Окружность талии (см)',
					name: 'waist_circumference',
					bind: {
						value: '{waist_circumference}',
						disabled: '{action == "view"}'
					},
					hideTrigger: true,
					listeners: {
						blur: 'doSave'
					}
				}, {
					xtype: 'container',
					layout: 'hbox',
					width: '100%',
					items: [{
						xtype: 'numberfield',
						forcePrecision: true,
						decimalPrecision: 1,
						hideTrigger: true,
						fieldLabel: 'Индекс массы тела',
						bind: '{body_mass_index}',
						disabled: true,
						width: 250,
						labelWidth: 170,
						labelAlign: 'right'
					}, {
						xtype: 'button',
						userCls: 'body-mass-index-warning button-without-frame',
						iconCls: 'body-mass-index-warning-icon',
						padding: '6 0 0 10',
						text: 'Превышение ИМТ. Норма 18-25. Рекомендуется отправить на второй этап ДВН',
						bind: {
							hidden: '{!body_mass_index_over_max}'
						}
					}, {
						xtype: 'button',
						userCls: 'body-mass-index-warning button-without-frame',
						iconCls: 'body-mass-index-warning-icon',
						padding: '6 0 0 10',
						text: 'Низкий ИМТ. Норма 18-25. Рекомендуется отправить на второй этап ДВН',
						bind: {
							hidden: '{!body_mass_index_over_min}'
						}
					}]
				},
				//скрытые поля 
				{
					name: 'UslugaComplex_id',
					xtype: 'hidden'
				},
				{
					name: 'EvnUslugaDispDop_id',
					//~ value: 0,
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
					bind: '{PersonEvn_id}',//?
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
						value: '{Server_id}' //?
					},
					xtype: 'hidden'
				}, {
					name: 'XmlTemplate_id',
					xtype: 'hidden'
				}, {
					name: 'EvnDirection_Type',
					xtype: 'hidden'
					//fieldLabel: 'Тип'
				}, {
					name: 'EvnDirection_insDate',
					xtype: 'hidden',
					//fieldLabel: 'Дата создания'
				}, {
					name: 'EvnDirection_Num',
					xtype: 'hidden',
					//fieldLabel: 'Номер направления'
				}, {
					name: 'EvnDirection_RecTo',
					xtype: 'hidden',
					//fieldLabel: 'Место оказания'
				}, {
					name: 'EvnDirection_RecDate',
					xtype: 'hidden',
					//fieldLabel: 'Запись'
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
				}
			],
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
				handler: 'openModalForm',
				bind: {
					hidden: '{!Date_SurveyType_Code'+me.SurveyType_Code+'}'
				}
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