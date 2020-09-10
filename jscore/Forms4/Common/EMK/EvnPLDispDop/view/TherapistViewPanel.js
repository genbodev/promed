Ext6.define('common.EMK.EvnPLDispDop.view.TherapistViewPanel', {
	extend: 'swPanel',
	requires: [
		'common.EMK.EvnPLDispDop.controller.TherapistController',
		'common.XmlTemplate.EditorPanel'
	],
	alias: 'widget.EvnPLDispDop_TherapistViewPanel',
	title: 'Прием(осмотр) врача-терапевта',
	controller: 'EvnPLDispDop13TherapistController',
	userCls: 'accordion-panel-window',
	bodyPadding: 10,
	isLoaded: false,
	autoLoad: true,
	listeners: {
		expand: 'onExpand'
	},
	setParams: function (record) {
		this.getController().setParams(record);
	},
	setReadOnly: function(isReadOnly) {
		this.therapistEditorPanel.setReadOnly(isReadOnly);
	},
	load: function() {
		this.getController().load();
	},
/*
	loadData: function() {
		let me = this;
		let vm = me.ownerPanel.getViewModel();
		let EvnPLDispDop13_id = vm.get('EvnPLDispDop13_id');
		Ext6.Ajax.request({
			url: '/?c=EvnPLDispDop13&m=checkEvnPLDispDop13Access',
			params: {EvnPLDispDop13_id: EvnPLDispDop13_id},
			failure: function (response, options) {

			},
			success: function (response) {
				if (response.responseText) {
					let responseData = Ext6.util.JSON.decode(response.responseText);
					if (responseData === false) {
						//TODO Отключил для отладки
						//me.setDisabled(true);
					}
				}
			}
		});
		Ext6.Ajax.request({
			url: '/?c=EvnPLDispDop13&m=getFormalizedInspectionParamsBySurveyType',
			params: {
				SurveyType_id: 19,
				EvnPLDispDop13_id: EvnPLDispDop13_id
			},
			failure: function (response, options) {

			},
			success: function (response) {
				if (response.responseText) {
					let responseData = Ext6.util.JSON.decode(response.responseText);
					let checkboxgroup = me.up('form').down('checkboxgroup');
					checkboxgroup.removeAll();//yl:повторная загрузка при клике в левой колонке
					responseData.forEach(function (responseDataItem) {
						if (responseDataItem.FormalizedInspectionParams_Directory !== null) {
							checkboxgroup.add({
								name: 'checkedValue_' + responseDataItem.FormalizedInspectionParams_id,
								boxLabel: responseDataItem.FormalizedInspectionParams_Name,
								inputValue: responseDataItem.FormalizedInspectionParams_id,
								listeners: {
									change: function (cmp, newVal, oldVal) {
										if(cmp.containsFocus){//yl:при резет-форм его нет
											me.setCheckValue(responseDataItem.FormalizedInspectionParams_id, newVal);
										}
									}
								}
							});
							let checked = (responseDataItem.value === null) ? false : responseDataItem.value;
							let findNameValue = '[inputValue=' + responseDataItem.FormalizedInspectionParams_id + ']';
							checkboxgroup.down(findNameValue).setValue(checked);
						} else {
							me.therapistEditorPanel.setHtmlText(responseDataItem.value);
						}
					});
				}
			}
		});
	},*/
/*
	openModalForm: function () {
		let me = this;
		let vm = me.ownerPanel.getViewModel();
		let EvnPLDispDop13_id = vm.get('EvnPLDispDop13_id');
		
		getWnd('swEvnUslugaDispDop13EditWindowExt6').show({
			needLoad: true,
			params: {
				panelCode: 'TherapistViewPanel',
				SurveyType_isVizit: me.record ? me.record.get('SurveyType_isVizit') : null,
				EvnPLDispDop13_id: EvnPLDispDop13_id,
				EvnUslugaDispDop13_id: EvnUslugaDispDop13_id,
				ownerWin: me
			},
			callback: function (data) {
				if(data.isOnkoDiag){
					var cntr = me.ownerPanel.getController();
					cntr.checkSpecifics(true);
				}
			}
		});
	},*/

	setCheckValue: function (id, newVal) {
		let me = this;
		let vm = me.ownerPanel.getViewModel();
		Ext6.Ajax.request({
			url: '/?c=EvnPLDispDop13&m=saveFormalizedInspectionParamsCheck',
			params: {
				EvnPLDisp_id: vm.get('EvnPLDispDop13_id'),
				id: id,
				check: newVal
			},
			success: function (response, action) {

			},
			failure: function (response, options) {

			}
		});
	},

	initComponent: function () {
		let me = this;
		me.therapistEditorPanel = Ext6.create("common.EvnXml.EditorPanel", {
			name: 'therapistEditorText',
			toolbarCfg: [//убрать save & maximize, оставить preloader:  //togglemaximize save emdbutton templateedit templaterestore templateclear templatesave |
				'undo redo | fontsize | bold italic underline strikethrough | subscript superscript | list indent align | paste | insertobject | showsigns |',
				' html print T9 | scale | -> preloader'
			],
			doAutoSave: function () {//срабатывает от blur
				me.DataForm.getForm().findField('therapist_text').setValue(me.therapistEditorPanel.getContent());
				me.getController().doSave();
			},
			refreshNotice: function () {//Timeout-костыль, какая-то левая пустая ф-я
				if (me.therapistEditorPanelTimer) clearTimeout(me.therapistEditorPanelTimer);
				if (!me.isLoaded) return;
				me.therapistEditorPanelTimer = setTimeout(
					this.doAutoSave.bind(me),
					5000
				);
			}
		});
		me.TherapisCheckboxControlPanel = {
			xtype: 'fieldset',
			title: 'Проведенные осмотры',
			defaults: {
				labelWidth: 90,
				anchor: '100%',
				layout: 'vbox'
			},
			items:
			[{
				xtype: 'container',
				padding: "0 0 0 10px",
				items: [
					{
						xtype: 'checkbox',
						name: 'skin_inspection',
						boxLabel: 'Осмотр кожных покровов',
						itemId: 'skin_inspection',
						inputValue: '2',
						uncheckedValue: '1',
						bind: {
							disabled: '{action == "view"}'
						},
						listeners: {
							change: 'doSave'
						}
					}, {
						xtype: 'checkbox',
						name: 'oral_inspection',
						boxLabel: 'Осмотр слизистых губ и ротовой полости',
						itemId: 'oral_inspection',
						inputValue: '2',
						uncheckedValue: '1',
						bind: {
							disabled: '{action == "view"}'
						},
						listeners: {
							change: 'doSave'
						}
					}, {
						xtype: 'checkbox',
						name: 'thyroid_palpation',
						boxLabel: 'Пальпация щитовидной железы',
						itemId: 'thyroid_palpation',
						inputValue: '2',
						uncheckedValue: '1',
						bind: {
							disabled: '{action == "view"}'
						},
						listeners: {
							change: 'doSave'
						}
					}, {
						xtype: 'checkbox',
						name: 'lymph_node_palpation',
						boxLabel: 'Пальпация лимфатических узлов',
						itemId: 'lymph_node_palpation',
						inputValue: '2',
						uncheckedValue: '1',
						bind: {
							disabled: '{action == "view"}'
						},
						listeners: {
							change: 'doSave'
						}
					}
				]
			}]
		};
		me.ViewForm = Ext6.create('Ext6.form.Panel', {
			layout: 'anchor',
			border: false,
			items: [
				me.therapistEditorPanel
			]
		});
		
		me.DataForm = Ext6.create('Ext6.form.Panel', {
			items: [
				me.TherapisCheckboxControlPanel,
				{
					name: 'therapist_text',//сюда будем класть текст осмотра
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
				me.ViewForm,
				me.DataForm
			]
		});
		this.callParent(arguments);
	}
});