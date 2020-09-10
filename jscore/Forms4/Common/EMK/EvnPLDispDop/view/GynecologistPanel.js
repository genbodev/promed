//yl:Осмотр фельдшером (акушеркой) или врачом акушером-гинекологом 31
Ext6.define("common.EMK.EvnPLDispDop.view.GynecologistPanel", {
	extend: "swPanel",
	requires: [
		"common.EMK.EvnPLDispDop.controller.GynecologistController",
		'common.EvnXml.EditorPanel'
	],
	controller: "EvnPLDispDop13GynecologistController",
	alias: "widget.EvnPLDispDop13GynecologistPanel",
	title: "Осмотр фельдшером (акушеркой) или врачом акушером-гинекологом",
	userCls: 'accordion-panel-window',
	listeners: {
		expand: 'onExpand'
	},
	setParams: function(record) {
		this.getController().setParams(record);
	},
	setReadOnly: function(isReadOnly) {
		this.GynecologistEditor.setReadOnly(isReadOnly);
	},
	initComponent: function () {
		let me = this;

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

		me.GynecologistEditor = Ext6.create("common.EvnXml.EditorPanel", {//редактор
			name: "GynecologistEditor",
			toolbarCfg: [//убрать save & maximize, оставить preloader:  //togglemaximize save emdbutton templateedit templaterestore templateclear templatesave |
				'undo redo | fontsize | bold italic underline strikethrough | subscript superscript | list indent align | paste | insertobject | showsigns |',
				' html print T9 | scale | -> preloader'
			],
			doAutoSave: function () {//срабатывает от blur
				me.getController().doSave();
			},
			refreshNotice: function () {//Timeout-костыль, какая-то левая пустая ф-я
				if (me.GynecologistEditorTimer) clearTimeout(me.GynecologistEditorTimer);
				if (!me.isLoaded) return;
				me.GynecologistEditorTimer = setTimeout(
					this.doAutoSave.bind(me),
					5000
				);
			}
		});
		
		me.DataForm = Ext6.create('Ext6.form.Panel', {
			hidden: true,
			items: [
				{
					name: 'gynecologist_inspection_text',//сюда будем класть текст осмотра
					xtype: 'hidden'
				},
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

		Ext6.apply(me, {
			items: [
				me.GynecologistEditor,
				//me.PanelForm,
				me.DataForm
			]
		});
		this.callParent(arguments);
	}
});

