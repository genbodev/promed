/* 
 * Форма создания направления на медико-криминалистическое исследование"
 */


Ext.define('common.BSME.ForenCorp.SecretaryWP.tools.swCreateMedCrimDirection', {
	extend: 'Ext.window.Window',
    autoShow: true,
	modal: true,
	width: '50%',
	height: '50%',
	refId: 'forencorpcreateeviddirectionwnd',
	closable: true,
	title: 'Направление на медико-криминалистическое исследование',
	id: 'ForenCorpCreateMedCrimDirectionWindow',
	border: false,
	layout: {
        align: 'stretch',
        type: 'vbox'
    },
	//Поиск человека по базе
	//Параметры:
	//	dete.callback [function] - функция, вызываемая после успешного поиска человека
	searchPerson: function(data) {
		Ext.create('sw.tools.subtools.swPersonWinSearch', 
		{
			callback: data.callback
		}).show()
	},
	/**
	 * Функция добавления строки ввода для произвольного поля с удалением и добавлением
	 * Параметры: 
	 *	data.commonName [string] - Наименование типа поля, которое будет являться префиксом для имён контейнеров и поля ввода (напр. Person)
	 *	data.textFieldLabel [string] - Подпись для текстового поля ввода
	 *	data.onChangeFn [function] - функция-обработчик события change
	 */
	addSimpleField: function(data) {
		
		if (!data.commonName || !data.textFieldLabel) {
			return false;
		}
		
		var containerAllSelectorArray = this.BaseForm.query('[name='+data.commonName+'AllContainer]'),
			containerAll = (containerAllSelectorArray.length)?containerAllSelectorArray[0]:null,
			cnt = (containerAll)?containerAll.items.length:null,
			me = this;
				
		if (!containerAll) 
			return false;
		
		if (cnt) {
			var prevField = containerAll.items.getAt(cnt-1);
			if (prevField) {
				prevField.query('[name='+data.commonName+'_Name]')[0].setReadOnly(true);
				prevField.query('[name=addbutton]')[0].setVisible(false);
				prevField.query('[name=deletebutton]')[0].setVisible(true);
			}
		}
		
		containerAll.add({
			xtype:'container',
			margin: '0 0 5 0',
			name: data.commonName+'Container',
			layout: {
				type: 'hbox',
				align: 'stretch'
			},
			items: [{
				flex: 1,
				labelAlign: 'left',
				labelWidth: 250,
				xtype: 'textfield',
				name: data.commonName+'_Name',
				fieldLabel: data.textFieldLabel+' #'+(1*cnt+1),
				listeners: {
					change: function(field,nV,oV) {
						field.up('container').down('[name=addbutton]').setDisabled(!nV)
					}
				}
			},{
				margin: '0 0 0 5',
				xtype: 'button',
				iconCls: 'add16',
				name: 'addbutton',
				disabled: true,
				tooltip: 'Добавить',
				handler: function(btn,evnt){
					me.addSimpleField(data)
				}
			},{
				margin: '0 0 0 5',
				xtype: 'button',
				hidden: true,
				name: 'deletebutton',
				iconCls: 'delete16',
				tooltip: 'Удалить',
				handler: function(btn,evnt){
					me.deleteSimpleField(btn,data)
					//Используем onChangeFn как реакцию на удаление поля => удаление значения
					data.onChangeFn();
				}
			}]
		});
	},
	/**
	 * Функция удаления строки ввода для произвольного поля с удалением и добавлением
	 * Параметры: 
	 *	btn [Ext.button.Button] - кнопка внутри строки, которую нужно удалить
	 *	data.commonName [string] - Наименование типа поля, которое будет являться префиксом для имён контейнеров и поля ввода (напр. Person)
	 */
	deleteSimpleField: function(btn,data) {
		var containerAllSelectorArray = this.BaseForm.query('[name='+data.commonName+'AllContainer]'),
			containerAll = (containerAllSelectorArray.length)?containerAllSelectorArray[0]:null,
			cnt = (containerAll)?containerAll.items.length:null,
			me = this;
			
		if (!containerAll || !(containerAll.queryById(btn.id)))
			return false;
		
		containerAll.remove(btn.up('container'));
		
		var containerItemsArray = containerAll.query('[name='+data.commonName+'Container]');
		var textField,label;
		for (var i=0;i<containerItemsArray.length;i++) {
			textField = containerItemsArray[i].query('[name='+data.commonName+'_Name]')[0];
			label = textField.getFieldLabel();
			textField.setFieldLabel(label.replace(/#\d+/,'#'+(1*i+1)));
		}
	},
	initComponent: function() {
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
			items: [{
				xtype: 'container',
				width: '60%',
				margin: '0 50 0 25',
				padding: '0 20 25 25',
				defaults: {
					labelAlign: 'left',
					labelWidth: 250
				},
				layout: {
					type: 'vbox',
					align: 'stretch'
				},
				items: [
				{
					xtype: 'textfield',
					padding: '20 0 0 0', // [top, right, bottom, left]
					fieldLabel: 'Номер заявки',
					readOnly: true,
					name: 'EvnForensic_Num'
				},
				{
					xtype:'container',
					name:'PersContainer',
					margin: '0 0 5 0',
					layout: {
						type: 'hbox',
						align: 'stretch'
					},
					items: [{
						flex: 1,
						labelAlign: 'left',
						labelWidth: 250,
						xtype: 'textfield',
						allowBlank: false,
						name: 'Person_FIO',
						readOnly: true,
						fieldLabel: 'Исследуемое лицо',
						margin: '0 5 0 0',
						listeners: {
							focus: function(field,focusEvt,evtOpts){
								var Person_id =field.up('container').down('[name=Person_zid]'),
									Person_FIO = field;
								me.searchPerson({callback: function(result){
									if (result)	{
										if (result.Person_id) {
											Person_id.setValue(result.Person_id);
										}
										if (result.PersonFirName_FirName && result.PersonSecName_SecName && result.PersonSurName_SurName) {
											Person_FIO.setValue(result.PersonSurName_SurName+' '+result.PersonFirName_FirName+' '+result.PersonSecName_SecName);
										}
									}
									me.defaultFocus = '[id='+field.next().next().id+']';
								}});
							}
						}
					},{
						xtype: 'hidden',
						name: 'Person_zid',
						value: 0
					},{
						margin: '0 0 0 5',
						xtype: 'button',
						iconCls: 'search16',
						name: 'searchbutton',
						tooltip: 'Поиск человека',
						handler: function(btn,evnt) {
							var Person_id =btn.up('container').down('[name=Person_zid]'),
								Person_FIO = btn.up('container').down('[name=Person_FIO]');
							me.searchPerson({callback: function(result){
								if (result)	{
									if (result.Person_id) {
										Person_id.setValue(result.Person_id);
									}
									if (result.PersonFirName_FirName && result.PersonSecName_SecName && result.PersonSurName_SurName) {
										Person_FIO.setValue(result.PersonSurName_SurName+' '+result.PersonFirName_FirName+' '+result.PersonSecName_SecName);
									}
								}
								btn.next().focus(true,500);
							}});
						}
					}]
				},
				{
					xtype: 'swCrymeStudyType',
					fieldLabel: 'Тип исследования',
					allowBlank: false
				},
				{
					xtype: 'container',
					name:'EvidenceAllContainer',
					layout: {
						type: 'vbox',
						align: 'stretch'
					},
					items: []
				},
				{
					xtype: 'textfield',
					minHeight: 15,
					fieldLabel: 'Фиксирующая жидкость',
					name:'EvnForensicCrimeExCorp_Liquid'
				},
				{
					xtype: 'textfield',
					fieldLabel: 'Заключение №',
					allowBlank: false,
					name: 'EvnForensicCrimeExCorp_OpinNum'
				},
				{
					xtype: 'datefield',
					fieldLabel: 'Дата взятия',
					format: 'd.m.Y',
					invalidText: 'Неправильный формат даты. Дата должна быть указана в формате ДД.ММ.ГГ',
					plugins: [new Ux.InputTextMask('99.99.9999')],
					name: 'EvnForensicCrimeExCorp_TakeDT',
					allowBlank: false
				},
				{
					xtype: 'datefield',
					fieldLabel: 'Время взятия',
					format: 'H:i',
					hideTrigger: true,
					invalidText: 'Неправильный формат времени. Время должно быть указано в формате ЧЧ:ММ',
					plugins: [new Ux.InputTextMask('99:99')],
					name: 'EvnForensicCrimeExCorp_TakeTime'
				},
				{
					xtype: 'textfield',
					padding: '20 0 0 0', // [top, right, bottom, left]
					fieldLabel: 'Опечатано печатью и оттиском',
					allowBlank: false,
					name: 'EvnForensicCrimeExCorp_Seal'
				},
				{
					xtype: 'textareafield',
					minHeight: 15,
					fieldLabel: 'Краткие обстоятельства дела',
					allowBlank: false,
					name:'EvnForensicCrimeExCorp_Facts'
				},
				{
					xtype: 'textareafield',
					minHeight: 15,
					fieldLabel: 'Вопросы подлежащие разрешению',
					allowBlank: false,
					name:'EvnForensicCrimeExCorp_Ques'
				}]
			}]
		});
		
		Ext.apply(me,{
			items: this.BaseForm,
			buttons: [{
				xtype: 'button',	
				text: 'Готово',
				handler: function(btn,evnt) {
					var params = {},
						simpleContainerNames = [
							'Evidence'
						],
						i,k,selectorContainerAll,containerAll,containerArray,obj,value,personTypeField;
					
					for (i=0;i<simpleContainerNames.length;i++) {
						selectorContainerAll = this.BaseForm.query('[name='+simpleContainerNames[i]+'AllContainer]');
						containerAll = (selectorContainerAll.length)?selectorContainerAll[0]:null;
						if (containerAll) {
							containerArray = containerAll.query('[name='+simpleContainerNames[i]+'Container]');
							params[simpleContainerNames[i]]= [];
							for (k=0;k<containerArray.length;k++) {
								obj = {};
								value = containerArray[k].query('[name='+simpleContainerNames[i]+'_Name]')[0].getValue();
								if (value) {
									obj[simpleContainerNames[i]+'_Name'] =  containerArray[k].query('[name='+simpleContainerNames[i]+'_Name]')[0].getValue();
									params[simpleContainerNames[i]].push(obj)
								}
							}
						}
					}
					
					for (var key in params) {
						if (params.hasOwnProperty(key)) {
							params[key] = Ext.JSON.encode(params[key]);
						}
					}
					
					params['Person_zid'] = this.BaseForm.getForm().findField('Person_zid').getValue();
					params['Evn_pid'] = this.Evn_pid;
					
					if (!this.BaseForm.isValid()) {
						Ext.Msg.alert('Проверка данных формы', 'Не все поля формы заполнены.<br>Незаполненные поля выделены особо.');
						return;
					}
					var loadMask =  new Ext.LoadMask(me, {msg:"Пожалуйста, подождите, идёт сохранение формы..."}); 
//						loadMask.show();
					this.BaseForm.submit({
						params : params,
						url: '/?c=BSME&m=saveForenMedCrimDirection',
						success: function(form, action) {
							loadMask.hide();
							Ext.Msg.alert('', "Заявка успешно сохранена");
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
					
					
					
				}.bind(this)
			}]
		});
		
		me.addSimpleField({
			commonName: 'Evidence',
			textFieldLabel: 'Вещественное доказательство'
		});
			
		me.callParent(arguments);
	},
	listeners: {
		show: function(wnd,eOpts) {
			if (!wnd.Evn_pid) {
				Ext.Msg.alert('Ошибка', 'Не указан идентификатор родительской заявки');
				wnd.close();
				return false;
			}
			
			var BaseForm = wnd.BaseForm.getForm();
			Ext.Ajax.request({
				params: {},
				url: '/?c=BSME&m=getNextRequestNumber',
				success: function(response) {
					var response_obj = Ext.JSON.decode(response.responseText);																															
					if (response_obj.EvnForensic_Num) {
						BaseForm.findField('EvnForensic_Num').setValue(response_obj.EvnForensic_Num);
					} else {
						Ext.Msg.alert('Ошибка', 'При получении номера заявки произошла ошибка');
						
					}
				},
				failure: function() {
					Ext.Msg.alert('Ошибка', 'При получении номера заявки произошла ошибка');
				}
			});
			
//			BaseForm.findField('EvnForensic_Date').setValue(new Date())
			
			
			
		}
	}
    
})