Ext6.define('common.EMK.EvnPLDispDop.view.IndividualProphConsultPanel', {
	extend: 'swPanel',
	requires: [
		'common.EMK.EvnPLDispDop.controller.IndividualProphConsultController',
	],
	alias: 'widget.EvnPLDispDop_IndividualProphConsultPanel',
	userCls: 'panel-with-tree-dots accordion-panel-window dispdop13form-indiprofconsult-panel',
	title: 'Индивидуальное профилактическое консультирование',
	controller: 'EvnPLDispDop13IndividualProphConsultController',
	ownerPanel: {},
	risk: {},
	bodyPadding: 10,
	lastUpdater: '',
	lastUpdateDateTime: '',
	setParams: function(record) {
		this.getController().setParams(record);
		//~ var view = this;
		//~ view.queryById('ConsultPanel')
	},
	listeners: {
		expand: 'onExpand'
	},
	reset: function() {
		this.getController().reset();
	},
	initComponent: function() {
		let me = this;
		
		me.ViewForm = Ext6.create('Ext6.form.Panel', {
			accessType: 'view',
			bodyPadding: 10,
			border: false,
			layout: 'anchor',
			bind: {
				disabled: '{action == add}'
			},
			items: [
				{
					xtype: 'displayfield',
					width: '100%',
					padding: '0 0 0 5',
					fieldLabel: '',
					value: 'С пациентом проведено консультирование по тематикам:',
				},
				{
					xtype: 'container',
					itemId: 'IndiProfConsultChecksContainer',
					items: []
				},
				{
					xtype: 'container',
					itemId: 'TextConsultContainer',
					border: false,
					maxHeight: 200,
					scrollable: true,
					items: [
						
					]
				}
			]
		});
		
		me.DataForm = Ext6.create('Ext6.form.Panel', {
			hidden: true,
			items: [
				/*{
					xtype: 'displayfield',
					width: '100%',
					padding: '0 0 0 5',
					fieldLabel: '',
					value: 'С пациентом проведено консультирование по тематикам:',
				},
				{
					xtype: 'container',
					itemId: 'IndiProfConsultChecksContainer',
					items: []
				},
				{
					xtype: 'container',
					itemId: 'TextConsultContainer',
					border: false,
					maxHeight: 200,
					scrollable: true,
					items: [
						
					]
				},*/
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
					xtype: 'hidden'
				}, {
					name: 'MedPersonal_id',
					xtype: 'hidden'
				}, {
					name: 'CytoMedPersonal_id',
					xtype: 'hidden'
				}, {
					name: 'Server_id',
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
				me.ViewForm,
				me.DataForm
			]
		});
		this.callParent(arguments);
	}
});