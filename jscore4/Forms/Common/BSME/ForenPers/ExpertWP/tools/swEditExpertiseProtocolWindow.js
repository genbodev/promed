/**
 * Форма редактирования экспертизы службы "Cудебно-медицинской экспертизы потерпевших, обвиняемых и других лиц"
 */

Ext.define('common.BSME.ForenPers.ExpertWP.tools.swEditExpertiseProtocolWindow', {
	extend: 'Ext.window.Window',
	autoShow: true,
	modal: true,
	width: '50%',
	height: '80%',
	refId: 'forenperseditexpertiseprotocolwnd',
	closable: true,
//    header: false,
	title: 'Заключение эксперта',
	id: 'swEditForenPersExpertiseProtocolWindow',
	border: false,
	layout: {
		align: 'stretch',
		type: 'vbox'
	},
	callback: Ext.emptyFn,

	getTemplateFieldValues: function() {
		var i;
		var field;
		var value = null;
		var result = {};
		var items =  this.templateFieldSet.items;

		for (i = 0; i < items.length; i++) {
			
			field = items.getAt(i);

			switch (field.getXType()) {
				case 'combobox': 
				case 'textareafield': 
					value = field.getValue();
					break;

				case 'checkboxgroup':
					value = (field.getValue()[field.name]) ? field.getValue()[field.name].join(',') : '' ;
					break;

				case 'radiogroup':
					value = field.getValue()[field.name] || '';
					break;

				default:
					break;
			}

			result[field.name] = value;

		};
		
		return result;

	},
	createTemplate: function(fieldItems) {
		
		var i;
		var k;
		var groupItem;
		var fields = [];

		if (!fieldItems || !fieldItems.length) {
			Ext.Msg.alert('Ошибка', 'Ошибка при создании шаблона заключения');
		}
		
		for (i=0; i<fieldItems.length; i++) {
			var item = fieldItems[i],
				fieldConf = {
					name : item['name'],
					value : item['value'],
					xtype : item['type'],
					fieldLabel: item['fieldLabel']
				};
				
			switch (item.type) {
				case 'textarea':
					break;
					
				case 'combobox':
					fieldConf['editable'] = false;
					fieldConf['valueField'] = 'id';
					fieldConf['displayField'] = 'fieldLabel';
					fieldConf['store'] = new Ext.data.Store({
						idProperty: 'id',
						autoLoad: false,
						queryMode: 'local',
						fields: [
							{name: 'id', type: 'id'},
							{name: 'name', type: 'string'},
							{name: 'fieldLabel', type: 'string'}
						],
						data: item.items
					})

					break;
					
				case 'checkboxgroup':
				case 'radiogroup':

					fieldConf['columns'] = 2;
					fieldConf['vertical'] = true;
					fieldConf['items'] = [];
					
					var values = (fieldConf['value']+'').split(',');
					delete fieldConf['value'];
					
					
					for (k = 0; k < item.items.length; k++) {
						groupItem = item.items[k];
						fieldConf['items'].push({
							boxLabel: groupItem['fieldLabel'],
							name: item['name'],
							inputValue: groupItem['id'],
							checked: (values.indexOf(groupItem['id']) !== -1)
						})
					};

					break;	

				default:
//					Ext.Msg.alert('Ошибка','Неверный тип поля');
//					return false;
					fieldConf = false;

					break;
			}
			
			if (fieldConf) {
				fields.push(fieldConf);
			}
			
		}
		
		this.templateFieldSet.removeAll(true);
		this.templateFieldSet.add(fields);
		this.templateFieldSet.setDisabled(false);
		return true;
		
	},
	createEmptyDocument: function(data) {
		
		var me = this;
		var loadMask =  new Ext.LoadMask(me, {msg:"Пожалуйста, подождите, идет создание документа..."});
		var callback = ( data && (typeof data.callback == 'function') ) || Ext.emptyFn;
		Ext.Ajax.request({
			params: ( data && data.params ) || {},
			url: '/?c=EvnXml&m=createEmpty',
			success: function(response) {
				loadMask.hide();
				var response_obj = Ext.JSON.decode(response.responseText);

				if (!response_obj.EvnXml_id) {
					
					if (response_obj.Error_Msg) {
						Ext.Msg.alert('Ошибка', response_obj.Error_Msg);
					} else {
						Ext.Msg.alert('Ошибка', 'Ошибка при создании шаблона заключения');
					}
					
				} else {
					me.BaseForm.getForm().findField('EvnXml_id').setValue( response_obj.EvnXml_id );
					callback(response_obj);
				}
			},
			failure: function() {
				loadMask.hide();
				Ext.Msg.alert('Ошибка', 'Ошибка при создании документа');
			}
		});
	},
	loadEvnXmlForm: function(data) {
		if (!data && (!data.EvnXml_id || !data.XmlTemplate_id)) {
			Ext.Msg.alert('Ошибка', 'Не переданы необходимые параметры для создания формы');
			return;
		}
		
		var me = this,
			callback = (typeof data.callback == 'function') ? data.callback : Ext.emptyFn;
		
		var loadMask =  new Ext.LoadMask(me, {msg:"Пожалуйста, подождите, идет создание полей шаблона..."});
		loadMask.show();

		Ext.Ajax.request({
			params: data,
			url: '/?c=EvnXml&m=loadEvnXmlForm',
			success: function(response) {
				loadMask.hide();
				var response_obj = Ext.JSON.decode(response.responseText);

				if (!response_obj.formData) {

					if (response_obj.Error_Msg) {
						Ext.Msg.alert('Ошибка', response_obj.Error_Msg);
					} else {
						Ext.Msg.alert('Ошибка', 'Ошибка при создании шаблона заключения');
					}
				} else {
					me.createTemplate(response_obj.formData);
				}

				callback();
			},
			failure: function() {
				loadMask.hide();
				Ext.Msg.alert('Ошибка', 'Ошибка при создании шаблона заключения');
			}
		});
		
	},
	initComponent: function(){
		var me = this;

		this.templateFieldSet = Ext.create('Ext.form.FieldSet',{
			fieldsData: {},
			defaultAlign: 'left',
			autoScroll: true,
			layout: {
				align: 'stretch',
				type: 'vbox'
			},
			disabled: true,
			//height:  800,
			flex: 1,
			title: 'Шаблон заключения',
			defaults: {
				labelAlign: 'left',
				labelWidth: 240
			}
		});
		
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
					name: 'EvnXml_id'
				}, {
					xtype: 'hidden',
					name: 'ActVersionForensic_Num'
				}, {
					xtype: 'container',
					defaults: {
						labelAlign: 'left',
						labelWidth: 250
					},
					items: [{
						xtype: 'swdatefield',
						fieldLabel: 'Фактическая дата начала экспертизы',
						name: 'ActVersionForensic_FactBegDT'
					}, {
						xtype: 'swdatefield',
						fieldLabel: 'Фактическая дата окончания экспертизы',
						name: 'ActVersionForensic_FactEndDT'
					}]
				}, {
					xtype: 'button',
					name: 'selectTemplateButton',
					text: 'Выбрать шаблон...',
					cls: 'x-form-file-btn',//Дополнительный класс, чтобы кнопка была нормальной высоты
					handler: function() {
						var Evn_id = me.BaseForm.getForm().findField('EvnForensic_id').getValue();
						var EvnXml_id = me.BaseForm.getForm().findField('EvnXml_id').getValue();
						var XmlType_id = 11;
						var EvnClass_id = 120;
						Ext.create('common.BSME.tools.swSelectTemplateWindow',{
							EvnClass_id:EvnClass_id, //120
							XmlType_id:XmlType_id, //11
							UslugaComplex_id:null,
							Evn_id: Evn_id,
							onSelect: function(data) {
								if (!data || !data['XmlTemplate_id']) {
									Ext.Msg.alert('Ошибка', 'Не передан идентификатор шаблона заключения');
									return false;
								}
								
								me.createEmptyDocument({
									params : {
										Evn_id:Evn_id,
										EvnXml_id: EvnXml_id,
										XmlTemplate_id: data.XmlTemplate_id,
										XmlType_id: XmlType_id,
										EvnClass_id: EvnClass_id, //120
										MedStaffFact_id: getGlobalOptions().CurMedStaffFact_id,
										getSessionServer_id : 1
										//Server_id: null
									},
									callback: function(data){
										me.loadEvnXmlForm({
											EvnXml_id: data['EvnXml_id']
										})
									}
								});
								
								
								
							}
						});
					}
				}, 
				this.templateFieldSet
				/*,{
					xtype: 'container',
					defaults: {
						labelAlign: 'top',
						labelWidth: 250
					},
					flex: 1,
					layout: 'fit',
					items: [
					{
						xtype: 'textareafield',
						name: 'ActVersionForensic_Text',
						fieldLabel: 'Заключение',
						width: '100%',
						padding: '0 0 20 0'
					}
					]
				}*/
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
						//ActVersionForensic_id: BaseForm.findField('ActVersionForensic_id').getValue(),
						ActVersionForensic_Num: BaseForm.findField('ActVersionForensic_Num').getValue(),
						XmlData: Ext.JSON.encode(me.getTemplateFieldValues()),
						EvnXml_id: me.BaseForm.getForm().findField('EvnXml_id').getValue()
					};

					if (!params.EvnXml_id) {
						Ext.Msg.alert('Проверка данных формы', 'Не указан идентификатор документа. Пожалуйста, выберите или перевыберите шаблон');
						return;
					}

					var loadMask =  new Ext.LoadMask(me, {msg:"Пожалуйста, подождите, идёт сохранение формы..."});
					//loadMask.show();

					BaseForm.submit({
						params : params,
						url: '/?c=BSME&m=saveForenPersExpertiseProtocol',
						success: function(form, action) {
							loadMask.hide();
							me.callback();
							Ext.Msg.alert('', "Экспертиза сохранена");
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
		
		var loadMask =  new Ext.LoadMask(me, {msg:"Пожалуйста, подождите, идет создание документа..."});
		Ext.Ajax.request({
			params: {EvnForensic_id: BaseForm.findField('EvnForensic_id').getValue()},
			url: '/?c=BSME&m=getForenPersRequest',
			success: function(response) {
				loadMask.hide();
				var response_obj = Ext.JSON.decode(response.responseText);
				if (!response_obj.EvnForensic_id) {
					Ext.Msg.alert('Ошибка', 'При получении идентификатора заявки произошла ошибка');
				}

				BaseForm.setValues(response_obj);
				
				var setNum = function(num) {
					BaseForm.findField('ActVersionForensic_Num').setValue(num);
					me.setTitle('Заключение эксперта №'+num);
				};
				
				if (response_obj.ActVersionForensic_Num) {
					setNum(response_obj.ActVersionForensic_Num);
				} else {
					me.getNewNum({callback: setNum});
				}
				
				if (response_obj.EvnXml_id) {
					me.loadEvnXmlForm({
						EvnXml_id:response_obj.EvnXml_id
					});
				}
				
			},
			failure: function() {
				loadMask.hide();
				Ext.Msg.alert('Ошибка', 'При получении данных заявки для экспертизы произошла ошибка');
			}
		});
	}
});
