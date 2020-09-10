Ext6.define('common.EMK.EvnPLDispDop.view.EyePressPanel',{
	extend: 'swPanel',
	requires: [
		'common.EMK.EvnPLDispDop.controller.EyePressController',
	],
	controller: 'EvnPLDispDop13EyePressController',
	alias: 'widget.EvnPLDispDop_EyePressPanel',
	userCls: 'panel-with-tree-dots accordion-panel-window',
	title: 'Измерение внутриглазного давления',
	parentPanel: {},
	bodyPadding: 10,
	isLoaded: false,
	listeners: {
		expand: 'onExpand'
	},
	setParams: function(record) {
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
				panelCode: 'EyePressPanel',
				EvnPLDispDop13_id: EvnPLDispDop13_id
			},
			callback: function (data) {

			}
		});
	},
	initComponent: function() {
		var me = this;

		me.ODWarningOverMax = new Ext6.create('Ext6.form.Label', {
			xtype: 'label',
			hidden: true,
			html: '<div><img src="img/icons/emk/AD-nothing-icon.png" style="margin: 5px 3px -3px 10px;">Высокий показатель. Рекомендуется отправить на второй этап диспансеризации</div>'
		});

		me.ODWarningLowerMin = new Ext6.create('Ext6.form.Label', {
			xtype: 'label',
			hidden: true,
			html: '<div><img src="img/icons/emk/AD-nothing-icon.png" style="margin: 5px 3px -3px 10px;">Низкий показатель. Рекомендуется отправить на второй этап диспансеризации</div>'
		});

		me.OSWarningOverMax = new Ext6.create('Ext6.form.Label', {
			xtype: 'label',
			hidden: true,
			html: '<div><img src="img/icons/emk/AD-nothing-icon.png" style="margin: 5px 3px -3px 10px;">Высокий показатель. Рекомендуется отправить на второй этап диспансеризации</div>'
		});

		me.OSWarningLowerMin = new Ext6.create('Ext6.form.Label', {
			xtype: 'label',
			hidden: true,
			html: '<div><img src="img/icons/emk/AD-nothing-icon.png" style="margin: 5px 3px -3px 10px;">Низкий показатель. Рекомендуется отправить на второй этап диспансеризации</div>'
		});

		me.EyePressForm = Ext6.create('Ext6.form.Panel', {
			accessType: 'view',
			padding: "15 0 0 27",
			layout: 'anchor',
			bind: {
				disabled: '{action == add}'
			},
			border: false,
			items: [{
				layout: 'column',
				margin: '0 0 5 0',
				border: false,
				items: [{
					xtype: 'numberfield',
					fieldLabel: 'Давление OD (мм рт. ст.)',
					labelWidth: 160,
					width: 230,
					//~ allowBlank: false,
					name: 'eye_pressure_right',
					bind: {
						value: '{eye_pressure_right}',
						disabled: '{action == "view"}'
					},
					hideTrigger: true,
					prevValue: '',
					listeners: {
						change: 'onChangeODBP',
						focusLeave: 'saveEyePress'
					}
				}
				, me.ODWarningOverMax
				, me.ODWarningLowerMin]
			}, {
				layout: 'column',
				border: false,
				items: [{
					xtype: 'numberfield',
					//~ allowBlank: false,
					hideTrigger: true,
					fieldLabel: 'Давление OS (мм рт. ст.)',
					labelWidth: 160,
					width: 230,
					name: 'eye_pressure_left',
					bind: {
						value: '{eye_pressure_left}',
						disabled: '{action == "view"}'
					},
					prevValue: '',
					listeners: {
						change: 'onChangeOSBP',
						focusLeave: 'saveEyePress'
					}
				}
				, me.OSWarningOverMax
				, me.OSWarningLowerMin]
			},
				//скрытые поля
				{
					name: 'UslugaComplex_id',
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

		Ext6.Ajax.request({
			url: '/?c=EvnPLDispDop13&m=getEyePressGroundValues',
			callback: function (opt, success, response) {
				var response_obj = Ext6.JSON.decode(response.responseText);
				if (success) {
					me.EyePressForm.OSBP_maxValue = response_obj['OSBP'].LabelRate_Max;
					me.EyePressForm.OSBP_minValue = response_obj['OSBP'].LabelRate_Min;
					me.EyePressForm.ODBP_maxValue = response_obj['ODBP'].LabelRate_Max;
					me.EyePressForm.ODBP_minValue = response_obj['ODBP'].LabelRate_Min;
				}
			}
		});

		Ext6.apply(me, {
			items: [
				me.EyePressForm
			]
		});

		this.callParent(arguments);
	}
});