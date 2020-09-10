Ext6.define('common.EMK.EvnPLDispDop.view.ArteriaPressPanel',{
	extend: 'swPanel',
	requires: [
		'common.EMK.EvnPLDispDop.controller.ArteriaPressController',
	],
	controller: 'EvnPLDispDop13ArteriaPressController',
	alias: 'widget.EvnPLDispDop_ArteriaPressPanel',
	userCls: 'panel-with-tree-dots accordion-panel-window',
	title: 'Артериальное давление',
	parentPanel: {},
	lastUpdater: '',
	lastUpdateDateTime: '',
	bodyPadding: 10,
	isLoaded: false,
	autoLoad: true,
	listeners: {
		expand: 'onExpand'
	},
	load: function() {
		this.getController().load();
	},
	reset: function() {
		//this.ArteriaPressForm.reset();
	},
	setParams: function(record) {
		//this.setViewModel(this.ownerPanel.getViewModel());
		this.isLoaded = false;
		this.getController().setParams(record);
	},
	openModalForm: function () {
		let me = this;
		let vm = me.ownerPanel.getViewModel();
		let EvnPLDispDop13_id = vm.get('EvnPLDispDop13_id');
		getWnd('swEvnUslugaDispDop13EditWindowExt6').show({
			needLoad: true,
			params: {
				panelCode: 'ArteriaPressPanel',
				EvnPLDispDop13_id: EvnPLDispDop13_id
			},
			callback: function (data) {

			}
		});
	},
	initComponent: function() {
		var me = this;

		me.SystolicWarningOverMax = new Ext6.create('Ext6.form.Label', {
			xtype: 'label',
			hidden: true,
			html: '<div><img src="img/icons/emk/AD-nothing-icon.png" style="margin: 5px 3px -3px 10px;">Высокий показатель. Рекомендуется отправить на второй этап диспансеризации</div>'
		});

		me.SystolicWarningLowerMin = new Ext6.create('Ext6.form.Label', {
			xtype: 'label',
			hidden: true,
			html: '<div><img src="img/icons/emk/AD-nothing-icon.png" style="margin: 5px 3px -3px 10px;">Низкий показатель. Рекомендуется отправить на второй этап диспансеризации</div>'
		});

		me.DiastolicWarningOverMax = new Ext6.create('Ext6.form.Label', {
			xtype: 'label',
			hidden: true,
			html: '<div><img src="img/icons/emk/AD-nothing-icon.png" style="margin: 5px 3px -3px 10px;">Высокий показатель. Рекомендуется отправить на второй этап диспансеризации</div>'
		});

		me.DiastolicWarningLowerMin = new Ext6.create('Ext6.form.Label', {
			xtype: 'label',
			hidden: true,
			html: '<div><img src="img/icons/emk/AD-nothing-icon.png" style="margin: 5px 3px -3px 10px;">Низкий показатель. Рекомендуется отправить на второй этап диспансеризации</div>'
		});

		me.ArteriaPressForm = Ext6.create('Ext6.form.Panel', {
			accessType: 'view',
			padding: "15 0 0 27",
			layout: 'anchor',
			bind: {
				disabled: '{action == add}'
			},
			border: false,
			items: [{
				layout: 'column',
				border: false,
				margin: '0 0 5 0',
				items: [{
					xtype: 'numberfield',
					fieldLabel: 'Систолическое АД (мм рт. ст.)',
					labelWidth: 200,
					width: 270,
					//~ allowBlank: false,
					name: 'systolic_blood_pressure',
					bind: {
						value: '{systolic_blood_pressure}',
						disabled: '{action == "view"}'
					},
					hideTrigger: true,
					prevValue: '',
					listeners: {
						'change': 'onChangeSystolicBP',
						'focusLeave': 'saveArteriaPress'
					}
				}
				, me.SystolicWarningOverMax
				, me.SystolicWarningLowerMin]
			}, {
				layout: 'column',
				border: false,
				items: [{
					xtype: 'numberfield',
					//~ allowBlank: false,
					labelWidth: 200,
					width: 270,
					hideTrigger: true,
					prevValue: '',
					fieldLabel: 'Диастолическое АД (мм рт. ст.)',
					name: 'diastolic_blood_pressure',
					bind: {
						value: '{diastolic_blood_pressure}',
						disabled: '{action == "view"}'
					},
					listeners: {
						'change': 'onChangeDiastolicBP',
						'focusLeave': 'saveArteriaPress'
					}
				}
				, me.DiastolicWarningOverMax
				, me.DiastolicWarningLowerMin]
			},
				//скрытые поля
				{
					name: 'UslugaComplex_id',
					value: 0,
					xtype: 'hidden'
				},
				{
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

		Ext6.Ajax.request({
			url: '/?c=EvnPLDispDop13&m=getArteriaPressGroundValues',
			callback: function (opt, success, response) {
				var response_obj = Ext6.JSON.decode(response.responseText);
				if (success) {
					me.ArteriaPressForm.SystolicBP_maxValue = response_obj['SystolicBP'].LabelRate_Max;
					me.ArteriaPressForm.SystolicBP_minValue = response_obj['SystolicBP'].LabelRate_Min;
					me.ArteriaPressForm.DiastolicBP_maxValue = response_obj['DiastolicBP'].LabelRate_Max;
					me.ArteriaPressForm.DiastolicBP_minValue = response_obj['DiastolicBP'].LabelRate_Min;
				}
			}
		});

		Ext6.apply(me, {
			items: [
				me.ArteriaPressForm
			]
		});

		this.callParent(arguments);
	}
});