Ext.define('common.BSME.ForenBio.ExpertWP.tools.swEditExpertiseProtocolWindow', {
	extend: 'Ext.window.Window',
	autoShow: true,
	modal: true,
	width: '50%',
	height: '80%',
	refId: 'forenbioeditexpertiseprotocolwnd',
	closable: true,
//    header: false,
	title: 'Заключение эксперта',
	id: 'swEditForenBioExpertiseProtocolWindow',
	border: false,
	layout: {
		align: 'stretch',
		type: 'vbox'
	},
	callback: Ext.emptyFn,

	addEvidenceFieldSet: function(data) {

		if (!data.commonName || !data.title) {
			return false;
		}

		var containerAllSelectorArray = this.BaseForm.query('[name='+data.commonName+'AllContainer]'),
			containerAll = (containerAllSelectorArray.length)?containerAllSelectorArray[0]:null,
			cnt = (containerAll)?containerAll.items.length:null,
			me = this;

		if (!containerAll)
			return false;

		/*if (cnt) {
			var prevField = containerAll.items.getAt(cnt-1);
			if (prevField) {
				prevField.query('[name='+data.commonName+'_Name]')[0].setReadOnly(true);
				prevField.query('[name=addbutton]')[0].setVisible(false);
				prevField.query('[name=deletebutton]')[0].setVisible(true);
			}
		}*/

		containerAll.add({
			xtype: 'fieldset',
			title: data.title+' #'+(1*cnt+1),
			name: data.commonName+'Container',
			defaults: {
				labelAlign: 'left',
				labelWidth: 250
			},
			items: [{
				xtype: 'textfield',
				fieldLabel: 'Наименование',
				name: 'Evidence_Name',
				readOnly: true
			}, {
				xtype: 'textfield',
				fieldLabel: 'Cостояние',
				name: 'Evidence_CorpStateName'
			}, {
				xtype: 'textfield',
				fieldLabel: 'Упаковка',
				name: 'Evidence_CorpStatePack'
			}, {
				xtype: 'textfield',
				fieldLabel: 'Количество',
				name: 'Evidence_CorpStateKol'
			}, {
				xtype: 'datefield',
				fieldLabel: 'Дата исследования',
				format: 'd.m.Y',
				invalidText: 'Неправильный формат даты. Дата должна быть указана в формате ДД.ММ.ГГ',
				plugins: [new Ux.InputTextMask('99.99.9999')],
				name: 'Evidence_ResearchDate'
			}]
		});
	},

	setContainerValues: function(commonName, index, data) {
		var containerArray = this.BaseForm.query('[name='+commonName+'Container]');
		if (containerArray && containerArray[index]) {
			containerArray[index].items.each(function(field){
				var value = data[field.getName()];
				if (value) { field.setValue(value) }
			});
		}
	},

	getContainerValues: function(commonName, index) {
		var values = {};
		var containerArray = this.BaseForm.query('[name='+commonName+'Container]');
		if (containerArray && containerArray[index]) {
			containerArray[index].items.each(function(field){
				var name = field.getName();
				if (field.xtype == 'datefield') {
					values[name] = field.getRawValue();
				} else {
					values[name] = field.getValue();
				}
			});
		}
		return values;
	},

	getAllContainerValues: function(commonName) {
		var values = [];
		var AllContainer = this.BaseForm.down('[name='+commonName+'AllContainer]');
		if (AllContainer && AllContainer.items) {
			for (var i=0; i<AllContainer.items.getCount(); i++) {
				values.push(this.getContainerValues(commonName, i));
			}
		}
		return values;
	},

	initComponent: function(){
		var me = this;

		this.BaseForm = Ext.create('sw.BaseForm',{
			xtype:'BaseForm',
			cls: 'mainFormNeptune',
			autoScroll: true,
			id: this.id+'_BaseForm',
			flex: 1,
			width: '100%',
			height: '100%',
			layout: {
				padding: '0 0 0 0', // [top, right, bottom, left]
				align: 'stretch',
				type: 'vbox'
			},
			items: [
				{
					xtype: 'hidden',
					name: 'ActVersionForensic_id'
				}, {
					xtype: 'hidden',
					name: 'EvnForensic_id'
				}, {
					xtype: 'hidden',
					name: 'ActVersionForensic_Num'
				}, {
					name: 'CadBloodJournal_Fieldset',
					xtype: 'fieldset',
					title: 'Журнал регистрации трупной крови в лаборатории',
					hidden: true,
					defaults: {
						labelAlign: 'left',
						labelWidth: 250
					},
					items: [{
						xtype: 'hidden',
						name: 'EvnForensicGeneticCadBlood_id'
					}, {
						xtype: 'datefield',
						fieldLabel: 'Дата исследования образца',
						format: 'd.m.Y',
						invalidText: 'Неправильный формат даты. Дата должна быть указана в формате ДД.ММ.ГГ',
						plugins: [new Ux.InputTextMask('99.99.9999')],
						name: 'EvnForensicGeneticCadBlood_StudyDate'
					}, {
						xtype: 'checkbox',
						name: 'EvnForensicGeneticCadBlood_IsIsosTestEA',
						fieldLabel: 'Тест-эритроцит А'
					}, {
						xtype: 'checkbox',
						name: 'EvnForensicGeneticCadBlood_IsIsosTestEB',
						fieldLabel: 'Тест-эритроцит B'
					}, {
						xtype: 'checkbox',
						name: 'EvnForensicGeneticCadBlood_IsIsosTestIsoB',
						fieldLabel: 'Изосыворотка бетта'
					}, {
						xtype: 'checkbox',
						name: 'EvnForensicGeneticCadBlood_IsIsosTestIsoA',
						fieldLabel: 'Изосыворотка альфа'
					}, {
						xtype: 'checkbox',
						name: 'EvnForensicGeneticCadBlood_IsIsosAntiA',
						fieldLabel: 'Имунная сыворотка Анти-A'
					}, {
						xtype: 'checkbox',
						name: 'EvnForensicGeneticCadBlood_IsIsosAntiB',
						fieldLabel: 'Имунная сыворотка Анти-B'
					}, {
						xtype: 'checkbox',
						name: 'EvnForensicGeneticCadBlood_IsIsosAntiH',
						fieldLabel: 'Имунная сыворотка Анти-H'
					}, {
						xtype: 'textareafield',
						fieldLabel:'Упаковка, состояние, количество материала',
						name: 'EvnForensicGeneticCadBlood_MatCondition',
						anchor: '100%'
					}, {
						xtype: 'textareafield',
						fieldLabel:'Другие системы (изосерология)',
						name: 'EvnForensicGeneticCadBlood_IsosOtherSystems',
						anchor: '100%'
					}, {
						xtype: 'textareafield',
						fieldLabel:'Результаты определения групп',
						name: 'EvnForensicGeneticCadBlood_Result',
						anchor: '100%'
					}]
				}, {
					name: 'BioSamplesJournal_Fieldset',
					xtype: 'fieldset',
					title: 'Журнал регистрации биологических образцов, изъятых у живых лиц в лаборатории',
					hidden: true,
					defaults: {
						labelAlign: 'left',
						labelWidth: 250
					},
					items: [{
						xtype: 'hidden',
						name: 'EvnForensicGeneticSampleLive_id'
					}, /*{
						xtype: 'datefield',
						fieldLabel: 'Дата исследования образца',
						format: 'd.m.Y',
						invalidText: 'Неправильный формат даты. Дата должна быть указана в формате ДД.ММ.ГГ',
						plugins: [new Ux.InputTextMask('99.99.9999')],
						name: 'EvnForensicGeneticSampleLive_StudyDate'
					},*/ {
						xtype: 'checkbox',
						name: 'EvnForensicGeneticSampleLive_IsIsosTestEA',
						fieldLabel: 'Тест-эритроцит А'
					}, {
						xtype: 'checkbox',
						name: 'EvnForensicGeneticSampleLive_IsIsosTestEB',
						fieldLabel: 'Тест-эритроцит B'
					}, {
						xtype: 'checkbox',
						name: 'EvnForensicGeneticSampleLive_IsIsosCyclAntiA',
						fieldLabel: 'Цоликлон Анти-А'
					}, {
						xtype: 'checkbox',
						name: 'EvnForensicGeneticSampleLive_IsIsosCyclAntiB',
						fieldLabel: 'Цоликлон Анти-В'
					}, {
						xtype: 'textareafield',
						name: 'EvnForensicGeneticSampleLive_Result',
						fieldLabel: 'Результаты определения групп по исследованым системам',
						anchor: '100%'
					}, {
						xtype: 'textareafield',
						name: 'EvnForensicGeneticSampleLive_IsosOtherSystems',
						fieldLabel: 'Изосерология: другие системы',
						anchor: '100%'
					}, {
						xtype: 'container',
						name: 'BioSampleAllContainer',
						layout: {
							type: 'vbox',
							align: 'stretch'
						},
						items: []
					}]
				}, {
					xtype: 'container',
					defaults: {
						labelAlign: 'left',
						labelWidth: 260
					},
					items: [{
						xtype: 'datefield',
						fieldLabel: 'Фактическая дата начала экспертизы',
						format: 'd.m.Y',
						invalidText: 'Неправильный формат даты. Дата должна быть указана в формате ДД.ММ.ГГ',
						plugins: [new Ux.InputTextMask('99.99.9999')],
						name: 'ActVersionForensic_FactBegDT'
					}, {
						xtype: 'datefield',
						fieldLabel: 'Фактическая дата окончания экспертизы',
						format: 'd.m.Y',
						invalidText: 'Неправильный формат даты. Дата должна быть указана в формате ДД.ММ.ГГ',
						plugins: [new Ux.InputTextMask('99.99.9999')],
						name: 'ActVersionForensic_FactEndDT'
					}]
				}, {
					xtype: 'container',
					defaults: {
						labelAlign: 'top',
						labelWidth: 250
					},
					items: [{
						//xtype: 'tinymce_textarea',
						xtype: 'textarea',
						name: 'ActVersionForensic_Text',
						noWysiwyg: false,
						fieldStyle: 'font-family: Arial; font-size: 16px;',
						fieldLabel: 'Заключение',
						tinyMCEConfig: {
							language: 'ru',
							toolbar: 'subscript superscript | bold italic underline | alignleft aligncenter alignright | bullist numlist | mybutton',
							toolbar_items_size: 'small',
							menubar: false
						},
						height: 220,
						width: '100%'
					}]
				}
			]
		});

		Ext.apply(me,{
			items: [
				this.BaseForm
			],
			buttons: [{
				xtype: 'button',
				text: 'Готово',
				handler: function(btn,evnt) {
					var BaseForm = me.BaseForm.getForm();
					if (!BaseForm.isValid()) {
						Ext.Msg.alert('Проверка данных формы', 'Не все поля формы заполнены.<br>Незаполненные поля выделены особо.');
						return;
					}
					var params = {
						EvnForensic_id: BaseForm.findField('EvnForensic_id').getValue(),
						ActVersionForensic_id: BaseForm.findField('ActVersionForensic_id').getValue(),
						ActVersionForensic_Num: BaseForm.findField('ActVersionForensic_Num').getValue(),
						EvnForensicGeneticCadBlood_id: BaseForm.findField('EvnForensicGeneticCadBlood_id').getValue(),
						EvnForensicGeneticSampleLive_id: BaseForm.findField('EvnForensicGeneticSampleLive_id').getValue()
					};

					if (params.EvnForensicGeneticSampleLive_id > 0) {
						params.BioSample = Ext.JSON.encode(me.getAllContainerValues('BioSample'));
					}

					var loadMask =  new Ext.LoadMask(me, {msg:"Пожалуйста, подождите, идёт сохранение формы..."});
					//loadMask.show();
					BaseForm.submit({
						params : params,
						url: '/?c=BSME&m=saveEvnForensicGeneticExpertiseProtocol',
						success: function(form, action) {
							loadMask.hide();
							Ext.Msg.alert('', "Экспертиза сохранена");
							me.callback();
							me.destroy();
						},
						failure: function(form, action) {
							loadMask.hide();
							switch (action.failureType) {
								case Ext.form.action.Action.CLIENT_INVALID:
									Ext.Msg.alert('Проверка данных формы', 'Не все поля формы заполнены.<br>Незаполненные поля выделены особо.');
									break;
								case Ext.form.action.Action.CONNECT_FAILURE:
									Ext.Msg.alert('Ошибка', 'Ошибка соединения с сервером');
									break;
								case Ext.form.action.Action.SERVER_INVALID:
									Ext.Msg.alert('Ошибка', action.result.Error_Msg);
							}
						},
						callback: function() {
							loadMask.hide();
						}
					});
				}
			}]
		});

		me.callParent(arguments);
	},

	getNewNum: function(options) {
		options = Ext.applyIf(options || {}, {callback: Ext.emptyFn});
		var me = this;
		var BaseForm = me.BaseForm.getForm();
		Ext.Ajax.request({
			params: {EvnForensic_id: BaseForm.findField('EvnForensic_id').getValue()},
			url: '/?c=BSME&m=getActVersionForensicNum',
			success: function(response) {
				var response_obj = Ext.JSON.decode(response.responseText);
				if (!response_obj.ActVersionForensic_Num) {
					Ext.Msg.alert('Ошибка', 'Ошибка при получении номера акта заключения');
				} else {
					options.callback(response_obj.ActVersionForensic_Num);
				}
			},
			failure: function() {
				Ext.Msg.alert('Ошибка', 'Ошибка при получении номера акта заключения');
			}
		});
	},

	show: function() {
		var me = this;

		me.callParent(arguments);
		if (!arguments[0] || !arguments[0].formParams || !arguments[0].formParams.EvnForensic_id) {
			return false;
		}

		if (arguments[0].callback) {
			me.callback = arguments[0].callback;
		}

		var BaseForm = me.BaseForm.getForm();

		BaseForm.setValues(arguments[0].formParams);

		Ext.Ajax.request({
			params: {EvnForensic_id: BaseForm.findField('EvnForensic_id').getValue()},
			url: '/?c=BSME&m=getEvnForensicGeneticRequest',
			success: function(response) {
				var response_obj = Ext.JSON.decode(response.responseText);
				if (!response_obj.EvnForensic_id) {
					Ext.Msg.alert('Ошибка', 'При получении идентификатора заявки произошла ошибка');
				}

				log(response_obj);

				BaseForm.setValues(response_obj);
				if (response_obj.EvnForensicGeneticCadBlood_id > 0) {
					me.BaseForm.down('[name=CadBloodJournal_Fieldset]').show();
					BaseForm.findField('EvnForensicGeneticCadBlood_StudyDate').allowBlank = false;
				}
				if (response_obj.EvnForensicGeneticSampleLive_id > 0) {
					if (Ext.isArray(response_obj.EvnForensicGeneticSampleLive_BioSample)) {
						for (var i=0; i<response_obj.EvnForensicGeneticSampleLive_BioSample.length; i++) {
							me.addEvidenceFieldSet({commonName: 'BioSample', title: 'Био образец'});
							me.setContainerValues('BioSample', i, response_obj.EvnForensicGeneticSampleLive_BioSample[i]);
						}
					}
					me.BaseForm.down('[name=BioSamplesJournal_Fieldset]').show();
				}
				me.getNewNum({callback: function(num) {
					BaseForm.findField('ActVersionForensic_Num').setValue(num);
					me.setTitle('Заключение эксперта №'+num);
				}});
			},
			failure: function() {
				Ext.Msg.alert('Ошибка', 'При получении данных заявки для экспертизы произошла ошибка');
			}
		});
	}
});
