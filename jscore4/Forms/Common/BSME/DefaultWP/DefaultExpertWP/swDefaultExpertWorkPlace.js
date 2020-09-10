/* 
 *  Шаблон рабочего места эксперта БСМЭ
 */


Ext.define('common.BSME.DefaultWP.DefaultExpertWP.swDefaultExpertWorkPlace', {
	extend: 'common.BSME.DefaultWP.BSMEDefaultWP.swBSMEDefaultWorkPlace',
	alias: 'widget.swDefaultExpertWorkPlace',
    autoShow: true,
	maximized: true,
	width: 1000,
	refId: 'DefaultExpertWorkPlace',
	closable: true,
	baseCls: 'arm-window',
    header: false,
	renderTo: Ext.getCmp('inPanel').body,
	callback:Ext.emptyFn,
	id: 'DefaultExpertWorkPlace',
	layout: {
        type: 'fit'
    },
	constrain: true,
	updateRequestCountInTabsParams: {ARMType: 'expert'},
	editExpertiseProtocolHandler: Ext.emptyFn,
	EvnClass_id: 120,
	XmlType_id: 11,
	TabPanelItems: [
		{
			title: 'Все заявки <em>0</em>',
			itemId: 'All',
			iconCls: 'tab_all_icon16'
		}, {
			title: 'Готовые',	//<em>0</em>',
			itemId: 'Archived',
			iconCls: 'tab_check_icon16'
		}
	],
	additionalRequestListDataviewStoreFields: [
		{name: 'ActVersionForensic_id', type: 'int'}
	],
	additionalRequestViewPanelButtons: [],
	//Массив идентификаторов типов документов, по которому будет фильтроваться комбобокс
	//в окне выбора шаблона. Чтобы отображались только необходимые типы документов
	//Если пустой, значит не фильтровать
	XmlTypeFilterValues: [],
	
	// Вынесено для переиспользования в арме заведующего отделением
	getExpertSelectTemplateBtn: function(){
		var me = this;
		return {
			id: this.id+'_SelectTemplateButton',
			refId: 'SelectTemplateButton',
			text: 'Выбор шаблона',
			//iconCls: 'edit16',
			disabled: true,
			xtype: 'button',
			handler: function() {
				var Evn_id = me.RequestViewPanel._Evn_id || null,
					EvnXml_id = me.ExpertisePanel._EvnXml_id || null,
					XmlType_id = me.XmlType_id,
					EvnClass_id = me.EvnClass_id,
					win = Ext.create('common.BSME.tools.swSelectTemplateWindow',{
						EvnClass_id: EvnClass_id, //120
						XmlType_id: XmlType_id, //11
						UslugaComplex_id: null,
						Evn_id: Evn_id,
						//Массив идентификаторов типов документов, по которому будет фильтроваться комбобокс
						//Чтобы отображались только необходимые типы документов
						XmlTypeFilterValues: me.XmlTypeFilterValues,
						onSelect: function(data) {
							if (!data || !data['XmlTemplate_id']) {
								Ext.Msg.alert('Ошибка', 'Не передан идентификатор шаблона заключения');
								return false;
							}

							me.createEmptyDocument({
								params : {
									EvnForensic_id:Evn_id,
									EvnXml_id: EvnXml_id,
									XmlTemplate_id: data.XmlTemplate_id,
									XmlType_id: XmlType_id,
									EvnClass_id: EvnClass_id, //120
									MedStaffFact_id: getGlobalOptions().CurMedStaffFact_id
								},
								callback: function(data){
									me.loadExpertisePanel(data['EvnXml_id']||null);
								}
							});
						}
				});
			}
		};
	},
	
	afterExpertiseProtocolSave: function(Request_id) {
		var me = this;

		me.loadRequestViewStore({aftercallback: function() {
			var idProperty = me.RequestListDataview.getStore().idProperty;
			var rec = me.RequestListDataview.getStore().findRecord(idProperty,Request_id);
			if (rec) {
				me.RequestListDataview.getSelectionModel().select([rec]);
				me.RequestListDataview.fireEvent('itemclick', me.RequestListDataview, rec, null, null, {});
			}
		}});
	},
	
	// Вынесено для переиспользования в функционале заведующего отделением
	changeTemplateButtonOnItemClick: function(rec){
		var selectTemplateButton = this.RequestViewPanel.down('[refId=SelectTemplateButton]');
		selectTemplateButton.setDisabled(rec.get('EvnStatus_SysNick') == 'Check');
	},
	
	requestListViewItemClick: function(rec) {
//		var sendToCheckButton = this.RequestViewPanel.down('[refId=CheckExpertiseProtocolButton]');
//		sendToCheckButton.setDisabled(!rec.get('ActVersionForensic_id'));
		
		this.changeTemplateButtonOnItemClick(rec);
		
		this.RequestViewPanel._Evn_id = rec.get('EvnForensic_id');
		
		//Пока вкладки всего две: "все заявки" и "в работе", любую заявку из этих вкладок можно редактировать
		this.RequestViewPanel.down('button#edit_request_button').enable();
		this.RequestViewPanel.down('button#xml_versions_button').enable();
	},

	//Функция загрузки панели заключения
	//входные параметры
	//data['html'] - шаблон документа
	//data['formData'] - данные для вставки в шаблон и создания формы
	
	_updateExpertisePanel: function(data) {
		if (!data || !data['html'] || !data['formData']) {
			return false;
		}
		
		var me = this;
		var panel = me.ExpertisePanel.down('[itemId=expertise]'); //Панель документа
		var html = data['html'];
		var fieldParams = {};
		var key, 
			value, 
			i, 
			value_indexes,
			item,
			field,
			Evn_id = this.RequestViewPanel._Evn_id;
		
		var formData = data['formData'];
		
		// Будем добавлять случайное число к идентификаторам, чтобы при откртии 
		// одной и той же заявки одновременно в разных армах не возникало конфликтов
		// идентификаторов
		var rand = Math.random().toString().substr(2);
		
		//Заменяем плейсхолдеры в шаблоне на div'ы с конкретными идентификаторами
		for (key in formData) {
			fieldParams = formData[key];
			fieldParams['div_id'] = fieldParams.name + '_' + Evn_id + '_' + rand;
			html = html.replace('{'+ fieldParams.name + '}' , '<div id = "'+ fieldParams['div_id'] +'" style="height: auto; width: '+(me.ExpertisePanel.getWidth()-40)+'px" > </div>');
			
		}
		
		panel.update(html,true);
		
		panel.fields={};
		
		//Функция обновления раздела на сервере
		var updateSection = function(name,value,isHTML) {
			var params = {
				EvnXml_id: me.ExpertisePanel._EvnXml_id, 
				name: name, 
				value: value,
				isHTML: (isHTML)?1:0
			};

			Ext.Ajax.request({
				url: '/?c=EvnXml&m=updateContent',
				callback: function(opt, success, response) {
					if (success && response.responseText != '') {
						var response_obj = Ext.JSON.decode(response.responseText);
						if (!response_obj.success) {
							Ext.Msg.alert('Ошибка',response_obj.Error_Msg||'При сохранении раздела произошла ошибка. Обратитесь к администратору');
						}
					}
				},
				params: params
			});
		};
		
		//Обработчики событий для комбобоксов/радиогрупп/чекбоксгрупп
		var combolisteners = {
			change: function (el, newValue, oldValue, eOpts ) {
				updateSection.apply(el,[el.name , (typeof el.getProcessedValue == 'function')?el.getProcessedValue():el.getValue() , 0]);
			}
		}
		
		//Создаем объект эдитора. Эдитор является генератором аля tinyMCE полей
		me.nicEditor = new nicEditor({buttonList: ['bold','italic','ol','ul',"center","justify","left","right","subscript","superscript",'swpastefromword']});
		
		for (key in formData) {
			fieldParams = formData[key];
			switch (fieldParams.type) {
				
				case 'combobox':

					field = Ext.create('Ext.form.ComboBox', {
						name: fieldParams.name,
						value: fieldParams.value,
						fieldLabel: fieldParams.fieldLabel,
						editable: false,
						valueField: 'id',
						displayField: 'fieldLabel',
						store: new Ext.data.Store({
							idProperty: 'id',
							autoLoad: false,
							queryMode: 'local',
							fields: [
								{name: 'id', type: 'id'},
								{name: 'name', type: 'string'},
								{name: 'fieldLabel', type: 'string'}
							],
							data: fieldParams.items
						}),
						listeners: combolisteners
					});

					break;
					
				case 'checkboxgroup':
				
					field = Ext.create('Ext.form.CheckboxGroup',{
						name: fieldParams.name,
						fieldLabel: fieldParams.fieldLabel,
						columns: 2,
						vertical: true,
						items: function(){
							var result = [];
							
							for (var i=0; i<fieldParams.items.length; i++) {
								var item = fieldParams.items[i];
								var values = (fieldParams.value+'').split(',');
								result.push({
									boxLabel: item['fieldLabel'],
									name: item['name'],
									inputValue: item['id'],
									checked: (values.indexOf(item['id']) !== -1)
								});
							}
							
							return result;
						}(),
						getProcessedValue: function() {
							var values = this.getValue();
							var values_arr = [];
							for (var key in values) {
								if (values.hasOwnProperty(key)) {
									values_arr.push(values[key]);
								}
							}
							return values_arr.join(',')
						},
						listeners: combolisteners
					});

					break;
				case 'radiogroup':
				
					field = Ext.create('Ext.form.RadioGroup',{
						name: fieldParams.name,
						//value: fieldParams.value,
						fieldLabel: fieldParams.fieldLabel,
						columns: 2,
						vertical: true,
						items: function(){
							var result = [];
							for (var i=0; i<fieldParams.items.length; i++) {
								var item = fieldParams.items[i];
								var values = (fieldParams.value+'').split(',');
								result.push({
									boxLabel: item['fieldLabel'],
									name: item['name'],
									inputValue: item['id'],
									checked: (values.indexOf(item['id']) !== -1)
								});
							}
							return result;
						}(),
						getProcessedValue: function() {
							var values = this.getValue();
							var values_arr = [];
							for (var key in values) {
								if (values.hasOwnProperty(key)) {
									values_arr.push(values[key]);
								}
							}
							return values_arr.join(',')
						},
						listeners: combolisteners
					});

					break;
					
				case 'textarea':
				default:
					
					field = {};
					
					field.render = function(div_id) {
						
						var el = Ext.get(div_id);
						
						var toolbar_id = 'toolbar_'+div_id;
						var field_id = 'field_'+div_id;
						
						el.update(
							'<div class="sw-nicEdit-wrap">' +
								'<div id="'+toolbar_id+'" class="sw-nicEdit-tb" style="display: none;">    </div>' +
								'<textarea class="sw-nicEdit" width id="' + field_id +'" name="'+ fieldParams.name +'">'+ 
									fieldParams.value +
								'</textarea>' +
							'</div>',
							false,
							function() {
								me.nicEditor.addInstance(field_id);
								me.nicEditor.setPanel(toolbar_id);
								var instance = me.nicEditor.instanceById(field_id);
								this.nicEditor = instance;
								if(instance) {
									instance['name'] = fieldParams.name;
									instance['id'] = field_id;
									instance['toolbar_id'] = toolbar_id;
								}
								
								el.on('keydown', function(e, t, eOpts){
									if ( e.button == 8) {
										e.stopEvent();
										var editor = instance;
										document.execCommand("InsertHTML",false,"&nbsp;&nbsp;&nbsp;&nbsp;");
									}
								})
								
								el.on('keyup', function(e, t, eOpts){
									
									if ( (el.prevHeight||null) != el.getHeight()) {
										el.prevHeight = el.getHeight()
										me.ExpertisePanel.doLayout();
									}
								})
							}
						)
					}
					
					break;
			}
			field.render(fieldParams['div_id']);
			panel.fields[fieldParams.name] = field;
			
		}
		
		
		me.nicEditor.addEvent('focus', function() {
			var instance = me.nicEditor.selectedInstance;
			if(instance) {
				instance.lastValue = instance.getContent();
				var nicTbar = Ext.get(instance.toolbar_id);
				if (nicTbar) {
					nicTbar.setVisibilityMode(Ext.Element.DISPLAY);
					nicTbar.setVisible(true);
				}
			}
			me.ExpertisePanel.doLayout();
		});
		me.nicEditor.addEvent('blur', function() {
			var instance = me.nicEditor.selectedInstance;
			if (instance ) {
				
				if (instance.lastValue != instance.getContent()) {
					updateSection(instance.name, instance.getContent(), 1)
					//instance.saveValue();
					instance.lastValue = instance.getContent();
				}
				
				var nicTbar = Ext.get(instance.toolbar_id);
				if (nicTbar) {
					nicTbar.setVisible(false);
					nicTbar.setVisibilityMode(Ext.Element.DISPLAY);
				}
			}
			me.ExpertisePanel.doLayout();
		});
		
		me.ExpertisePanel.doLayout();
		// hidePrintOnly
        var node_list = Ext.query("div[class*=printonly]",this.body.dom);
        //log(node_list);
        for(i=0; i < node_list.length; i++)
        {
            el = new Ext.Element(node_list[i]);
            //log(el);
            el.setStyle({display: 'none'});
        }
        // end hidePrintOnly
		
		
	},
	createEmptyDocument: function(data) {
		
		var me = this;
		var loadMask =  new Ext.LoadMask(me, {msg:"Пожалуйста, подождите, идет создание документа..."});
		var callback = ( !!data && (typeof data.callback == 'function') ) ? data.callback : Ext.emptyFn;
		Ext.Ajax.request({
			params: ( data && data.params ) || {},
			url: '/?c=BSME&m=createEmpty',
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
					callback(response_obj);
				}
			},
			failure: function() {
				loadMask.hide();
				Ext.Msg.alert('Ошибка', 'Ошибка при создании документа');
			}
		});
	},
	
	ReportPanelSaveHandler: function(){
		var me = this,
			form = this.ReportPanel.getForm();
	
		if (!form.isValid()) {
			Ext.Msg.alert('Проверка данных формы', 'Не все поля формы заполнены.<br>Незаполненные поля выделены особо.');
			return;
		}

		var params = form.getValues();

		form.submit({
			params : params,
			url: '/?c=BSME&m=saveForensicSubReportWorking',
			success: function(form, action){
				if ( typeof action.result.ForensicSubReportWorking_id === 'undefined' ) {
					Ext.Msg.alert('Ошибка', 'Результат сохранения данных вернул неверный ответ. Обновите страницу, если данные не сохранились обратитесь в службу технической поддержки.');
					return;
				}
				form.findField('ForensicSubReportWorking_id').setValue( action.result.ForensicSubReportWorking_id );
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
			}
		});
	},
	
	initReportPanel: function( editable ){
		editable = typeof editable === 'undefined' ? true : editable;
		
		var me = this;
		this.ReportPanel = Ext.create('sw.BaseForm',{
			xtype: 'BaseForm',
			id: this.id + '_ReportPanel',
			cls: 'mainFormNeptune',
			hidden: true,
			autoScroll: true,
			width: '100%',
			height: 'auto',
			title: 'Отчет «Деятельность бюро»',
			header: {
				xtype: 'header',
				padding: '12px 12px 12px 35px',
				layout: {
					type: 'vbox',
					align: 'left'
				}
			},
			layout: {
				padding: '12px 12px 12px 35px',
				align: 'stretch',
				type: 'vbox'
			},
			items: [
				{ xtype: 'hidden', name: 'ForensicSubReportWorking_id' },
				{ xtype: 'hidden', name: 'EvnForensicSub_id' },
				{ xtype: sw.forensicValuationInjuryCombo },
				{ xtype: sw.forensicDefinitionSexualOffensesCombo },
				{ xtype: sw.forensicSubDefinitionCombo }
			]
		});
		
		// Т.к. xtype нельзя назначить события через listeners если они уже есть
		// в описании, назначаем их ниже. Если кто в курсе как это обойти, сделайте.
		if ( editable ) {
			this.ReportPanel.getForm().findField('ForensicValuationInjury_id').on('select',function(combo){ me.ReportPanelComboEitherOne(combo); me.ReportPanelSaveHandler(); });
			this.ReportPanel.getForm().findField('ForensicDefinitionSexualOffenses_id').on('select',function(combo){ me.ReportPanelComboEitherOne(combo); me.ReportPanelSaveHandler(); });
			this.ReportPanel.getForm().findField('ForensicSubDefinition_id').on('select',function(combo){ me.ReportPanelComboEitherOne(combo); me.ReportPanelSaveHandler(); });
		}
	},
	
    initComponent: function() {
		var me = this;

		//Кнопки тулбара для панели просмотре заявок
		me.requestViewPanelButtons = [
//			{
//			id: this.id+'_EditExpertiseProtocolButton',
//			refId: 'EditExpertiseProtocolButton',
//			text: 'Заключение экспертизы',
//			iconCls: 'edit16',
//			disabled: true,
//			xtype: 'button',
//			handler: function() {
//				me.editExpertiseProtocolHandler({
//					action: 'view',
//					formParams: {EvnForensic_id: me.RequestViewPanel._Evn_id},
//					callback: function(){me.afterExpertiseProtocolSave(me.RequestViewPanel._Evn_id)}
//				});
//			}
//		}, {
//			text: 'На проверку',
//			id: this.id+'_CheckExpertiseProtocolButton',
//			refId: 'CheckExpertiseProtocolButton',
//			//iconCls: '',
//			disabled: true,
//			xtype: 'button',
//			handler: function(){
//				var EvnForensic_id = me.RequestViewPanel._Evn_id;
//				if ( !EvnForensic_id ) {
//					Ext.Msg.alert('Ошибка', 'Выберите заявку, которую хотите отправить на проверку.');
//					return false;
//				}
//
//				Ext.Ajax.request({
//					url: '/?c=BSME&m=checkEvnForensic',
//					params: {
//						EvnForensic_id: EvnForensic_id
//					},
//					callback: function(opt, success, response) {
//						if ( !success ) {
//							Ext.Msg.alert('Ошибка','Во время выполнения запроса произошла ошибка.');
//							return;
//						}
//						var result = Ext.JSON.decode(response.responseText);
//						if (!Ext.isEmpty(result.Error_Msg)) {
//							Ext.Msg.alert('Ошибка', result.Error_Msg);
//							return;
//						}
//						Ext.Msg.alert('Сообщение','Заключение экспертизы отправлено на проверку.');
//						me.loadRequestViewStore();
//					}
//				});
//			}
//		},
		this.getExpertSelectTemplateBtn(),{
			text: 'Редактировать',
			itemId: 'edit_request_button',
			xtype: 'button',
			iconCls: 'edit16',
			disabled: true,
			handler: function () {
				var loadMask =  new Ext.LoadMask(me, {msg:"Пожалуйста, подождите, идёт открытие формы..."}); 
				loadMask.show();
				
				setTimeout(function() {
					Ext.create('common.BSME.ForenPers.SecretaryWP.tools.swCreateRequestWindow',{
						EvnForensicSub_id:me.RequestViewPanel._Evn_id,
						disbleEvnForensicSub_pid: false,
						callback: function(){
							var idProperty = me.RequestListDataview.getStore().idProperty,
								selection = me.RequestListDataview.getSelectionModel().getSelection(),
								id = selection && selection[0] ? selection[0].get(idProperty) : null;
						
							me.loadRequestViewStore({
								aftercallback: function() {
									if (id && idProperty) {
										var rec = me.RequestListDataview.getStore().findRecord(idProperty,id);
										if (rec) {
											me.RequestListDataview.getSelectionModel().select([rec]);
//											me.updateRequestView(rec.get('EvnForensic_id'))
										}
									}
								}
							});
							loadMask.hide();
						}
					});
				},1)
			}
		},{
			text: 'Версии документа',
			itemId: 'xml_versions_button',
			xtype: 'button',
			disabled: true,
			handler: function() {
				Ext.create('common.BSME.tools.swBSMEXmlVersionListWindow',{
					EvnForensic_id: me.RequestViewPanel._Evn_id
				});
			}
		}];

		me.requestViewPanelButtons = me.requestViewPanelButtons.concat(me.additionalRequestViewPanelButtons);
		
		this.initReportPanel();
		
		me.callParent(arguments);
	},
	
	updateSearchFormMedPersonalEid: function(){
		this.SearchForm.getForm().findField('MedPersonal_eid').setValue(parseInt(getGlobalOptions().CurMedPersonal_id));
	},
	
	show: function(){
		this.updateSearchFormMedPersonalEid();
		this.callParent(arguments);
	}
});
		
