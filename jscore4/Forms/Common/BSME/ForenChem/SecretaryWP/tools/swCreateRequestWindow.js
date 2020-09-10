/* 
 * Форма добавления заявки в АРМ Секретаря службы "Судебно-химическое отделение"
 */


Ext.define('common.BSME.ForenChem.SecretaryWP.tools.swCreateRequestWindow', {
	extend: 'Ext.window.Window',
    autoShow: true,
	modal: true,
	width: '50%',
	height: '80%',
	refId: 'forenchemcreaterequestwnd',
	closable: true,
//    header: false,
	title: 'Новая заявка',
	id: 'ForenChemCreateRequestWindow',
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
						data.onChangeFn.apply(this,[field,nV,oV]);
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
	/**
	 * Проверка на полноту заполнения журнала вещественных доказательств
	 * Папраметры:
	 *	setAllowBlankFlag [boolean] - необходимость изменнения флага allowBlank для указанных полей
	 */
	checkBioSamplesJournalIsFilled: function(setAllowBlankFlag) {
		
		var fieldset = this.BaseForm.down('[name=BioSampleJournal_Fieldset]'),
			valueFields = [
				'Person_eFIO',
				'Person_FIO',
				'BioSample_Name',
				'EvnForensicChemBiomat_Facts',
				'EvnForensicChemBiomat_Objective',
			]
		return (fieldset) ? this._checkJournalIsFilled(fieldset,valueFields,setAllowBlankFlag) : false;
	},
	allJournalsAreEmpty: function() {
		return !this.checkBioSamplesJournalIsFilled();
			
	},
	/**
	 * Проверка на полноту заполнения произвольного журнала
	 * Параметры:
	 *		fieldset [Ext.form.Fieldset] - fieldset с необходимыми полями журнала
	 *		valueFields [array] - массив имен полей, значения которых должны быть непусты
	 *		setAllowBlankFlag [boolean] - необходимость изменнения флага allowBlank для указанных полей после проверки на заполненность
	 */
	_checkJournalIsFilled: function(fieldset, valueFields,setAllowBlankFlag) {
		if (!fieldset || !valueFields) {
			return false;
		}
		var field,
			hasEmptyValue = false,
			hasFilledValue = false,
			fieldHasValue = false,
			i;
			
		for (i=0;(i<valueFields.length);i++) {
			field = fieldset.down('[name='+valueFields[i]+']');
			fieldHasValue = (field.xtype == 'datefield') ? ((typeof field.getValue() == 'object') && (field.getValue()!==null)) : (!!field.getValue())
			hasEmptyValue = hasEmptyValue||!fieldHasValue;
			hasFilledValue = hasFilledValue||fieldHasValue;
		}
		if (setAllowBlankFlag) {
			this._setJournalAllowBlankFlag({
				fieldset:fieldset,
				valueFields:valueFields,
				allowBlank: (!hasFilledValue)
			})
		}
				//нет пустых и есть заполненные
		return !hasEmptyValue&&hasFilledValue;
	},
	/**
	 * Установка флагов allowBlank для указанных полей в журнале (fieldset-е)
	 * Параметры: 
	 *	data.fieldset [Ext.form.Fieldset] - fieldset с необходимыми полями журнала
	 *	data.valueFields [array] - массив имен полей, значение флагов allowBlank которых должны быть изменены
	 *	data.allowBlank [boolean] - значение устанавливаемого флаг аallowBlank
	 */
	_setJournalAllowBlankFlag: function(data) {
		if (!data || !data.fieldset || !data.valueFields) {
			return false;
		}
		var val,i,field;
		for (i=0;(i<data.valueFields.length);i++) {
			field = data.fieldset.down('[name='+data.valueFields[i]+']');
			field.allowBlank = !!data.allowBlank;
			field.validate();
		}
		return true;
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
				padding: '0 10 0 0',
				width: '20%',
				bodyPadding: 10,
				autoHeight: true,
				defaults: {
					labelAlign: 'left',
					labelWidth: 250,
				},
				items: [{
					xtype: 'textfield',
					padding: '0 0 0 0', // [top, right, bottom, left]
					fieldLabel: 'Номер заявки',
					readOnly: true,
					name: 'EvnForensic_Num'
				},{
					xtype: 'datefield',
					fieldLabel: 'Дата поступления',
					format: 'd.m.Y',
					invalidText: 'Неправильный формат даты. Дата должна быть указана в формате ДД.ММ.ГГ',
					plugins: [new Ux.InputTextMask('99.99.9999')],
					name: 'EvnForensicChemBiomat_ReceivedDate',
					allowBlank: false
				},]
			},
			{
				xtype: 'fieldset',
				collapsible: true,
				width: '60%',
				margin: '20 50 0 25',
				padding: '20 20 25 25',// [top, right, bottom, left]
				defaults: {
					labelAlign: 'left',
					labelWidth: 250,
				},
				name: 'BioSampleJournal_Fieldset',
				title: 'Журнал регистрации биоматериала в судебно-химическом отделении',
				items: [{
					xtype: 'container',
					layout: {
						type: 'vbox',
						align: 'stretch'
					},
					defaults: {
						labelAlign: 'left',
						labelWidth: 250,
						listeners: {
							change: function(field, newVal, oldVal) {
								this.checkBioSamplesJournalIsFilled(true)
							}.bind(this)
						}
					},
					items: [
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
									}});
								}, 
								change: function(){
									me.checkBioSamplesJournalIsFilled(true)
								}
							}
						},{
							xtype: 'hidden',
							name: 'Person_zid',
							value: 0,
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
								}});
							}
						}]
					},{
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
							name: 'Person_eFIO',
							readOnly: true,
							fieldLabel: 'Направившее лицо',
							margin: '0 5 0 0',
							listeners: {
								focus: function(field,focusEvt,evtOpts){
									var Person_id =field.up('container').down('[name=Person_sid]'),
										Person_eFIO = field;
									me.searchPerson({callback: function(result){
										if (result)	{
											if (result.Person_id) {
												Person_id.setValue(result.Person_id);
											}
											if (result.PersonFirName_FirName && result.PersonSecName_SecName && result.PersonSurName_SurName) {
												Person_eFIO.setValue(result.PersonSurName_SurName+' '+result.PersonFirName_FirName+' '+result.PersonSecName_SecName);
											}
										}
									}});
								}, 
								change: function(){
									me.checkBioSamplesJournalIsFilled(true)
								}
							}
						},{
							xtype: 'hidden',
							name: 'Person_sid',
							value: 0,
						},{
							margin: '0 0 0 5',
							xtype: 'button',
							iconCls: 'search16',
							name: 'searchbutton',
							tooltip: 'Поиск человека',
							handler: function(btn,evnt) {
								var Person_id =btn.up('container').down('[name=Person_sid]'),
									Person_eFIO = btn.up('container').down('[name=Person_eFIO]');
								me.searchPerson({callback: function(result){
									if (result)	{
										if (result.Person_id) {
											Person_id.setValue(result.Person_id);
										}
										if (result.PersonFirName_FirName && result.PersonSecName_SecName && result.PersonSurName_SurName) {
											Person_eFIO.setValue(result.PersonSurName_SurName+' '+result.PersonFirName_FirName+' '+result.PersonSecName_SecName);
										}
									}
								}});
							}
						}]
					},{
						xtype: 'container',
						name: 'BioSampleAllContainer',
						layout: {
							type: 'vbox',
							align: 'stretch'
						},
						items: []
					},{
						xtype: 'textareafield',
						plugins: [new Ux.Translit(true)],
						minHeight: 15,
						fieldLabel: 'Краткие обстоятельства дела',
						name:'EvnForensicChemBiomat_Facts',
					},{
						xtype: 'textfield',
						fieldLabel: 'Цель экспертизы',
						name: 'EvnForensicChemBiomat_Objective'
					}]
				}]
			}]
		});
		
		Ext.apply(me,{
			items: [
				this.BaseForm
			], 
			buttons: [{
				xtype: 'button',	
				text: 'Готово',
				handler: function(btn,evnt) {
					var params = {},
						simpleContainerNames = [
							'BioSample',
						],
						i,k,selectorContainerAll,containerAll,containerArray,obj,value;
					
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
					
					params['Person_sid'] = this.BaseForm.getForm().findField('Person_sid').getValue();
					params['Person_zid'] = this.BaseForm.getForm().findField('Person_zid').getValue();
					
					if (this.allJournalsAreEmpty()) {
						Ext.Msg.alert('Проверка данных формы', 'Ни один из журналов не заполнен.Пожалуйста, заполните хотя бы 1 журнал');
						return;
					}
					if (!this.BaseForm.isValid()) {
						Ext.Msg.alert('Проверка данных формы', 'Не все поля формы заполнены.<br>Незаполненные поля выделены особо.');
						return;
					}
					var loadMask =  new Ext.LoadMask(me, {msg:"Пожалуйста, подождите, идёт сохранение формы..."}); 
//						loadMask.show();
					this.BaseForm.submit({
						params : params,
						url: '/?c=BSME&m=saveForenChemOwnRequest',
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
		})
		
		me.addSimpleField({
			commonName: 'BioSample',
			textFieldLabel: 'Био образец',
			onChangeFn: function() {
				me.checkBioSamplesJournalIsFilled(true)
			}
		});
		
		
		me.callParent(arguments);
	},
	listeners: {
		show: function(wnd,eOpts) {
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