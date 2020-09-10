/**
 * Форма редактирования заявки в АРМах службы "Отделение комиссионных и комплексных экспертиз"
 */

Ext.define('common.BSME.ForenComplex.SecretaryWP.tools.swCreateRequestWindow', {
	extend: 'Ext.window.Window',
	autoShow: true,
	modal: true,
	width: '50%',
	height: '80%',
	refId: 'forencomplexcreaterequestwnd',
	closable: true,
//    header: false,
	title: 'Новая заявка',
	id: 'ForenComplexCreateRequestWindow',
	border: false,
	callback: Ext.emptyFn(),
	listeners: {
		close: function(wnd) {
			if (typeof wnd.callback == 'function') {
				wnd.callback();
			}
		}
	},
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
	 * Функция добавления строки ввода для человека с поиском удалением и добавлением
	 * Параметры:
	 *	data.commonName [string] - Наименование типа поля, являющееся префиксом для имён контейнеров (напр. Person)
	 *	data.hasTypeFlag [boolean] - флаг установки типа лица (Обвиняемый/свидетель)
	 *	data.onChangeFn [function] - функция-обработчик события change
	 */
	addPerson: function(data) {
		if (!data.commonName) {
			return false;
		}

		var PersonsContainerSelectorArray = this.BaseForm.query('[name='+data.commonName+'AllContainer]'),
			PersonsContainer = (PersonsContainerSelectorArray.length)?PersonsContainerSelectorArray[0]:null,
			cnt = (PersonsContainer)?PersonsContainer.items.length:null,
			me = this;

		if (!PersonsContainer)
			return false;

		if (cnt) {
			var prevField = PersonsContainer.items.getAt(cnt-1);
			if (prevField) {
				prevField.query('[name=addbutton]')[0].setVisible(false);
				prevField.query('[name=deletebutton]')[0].setVisible(true);
			}
		}

		var label = (data.hasTypeFlag)?'Потерпевший/обвиняемый':'Исследуемое лицо';

		PersonsContainer.add({
			xtype:'container',
			name: data.commonName+'Container',
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
				fieldLabel: label+' #'+(1*cnt+1),
				margin: '0 5 0 0',
				listeners: {
					focus: function(field,focusEvt,evtOpts){
						var Person_id =field.up('container').down('[name=Person_id]'),
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
					},
					change: data.onChangeFn
				},
				close: function(wnd, eOpts){
					wnd.callback();
				}
			},{
				xtype: 'hidden',
				name: 'Person_id',
				value: 0,
				listeners: {
					change: function(field,nV,oV) {
						if (nV) {
							this.up('container').query('[name=addbutton]')[0].setDisabled(false);
						} else {
							this.up('container').query('[name=addbutton]')[0].setDisabled(true);
						}
					}
				}
			},{
				xtype: 'splitbutton',
				name: 'EvnForensicGeneticEvidLink_IsVic',
				value: 1,
				hidden: !data.hasTypeFlag,
				disabled: !data.hasTypeFlag,
				setValue: function(val) {
					this.value = val;
				},
				getValue: function() {
					return this.value
				},
				text: 'Обвиняемый',
				menu: new Ext.menu.Menu({
					items: [
						{value: 1, text: 'Обвиняемый', handler: function(){
							var splitbutton = this.up('splitbutton');
							splitbutton.setText(this.text);
							splitbutton.setValue(this.value);
						}},
						{value: 2, text: 'Потерпевший', handler: function(){
							var splitbutton = this.up('splitbutton');
							splitbutton.setText(this.text);
							splitbutton.setValue(this.value);
						}}
					]
				})
			},{
				margin: '0 0 0 5',
				xtype: 'button',
				iconCls: 'search16',
				name: 'searchbutton',
				tooltip: 'Поиск человека',
				handler: function(btn,evnt) {
					var Person_id =btn.up('container').down('[name=Person_id]'),
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
						me.defaultFocus = '[id='+btn.next().id+']';
					}});
				}
			},{
				margin: '0 0 0 5',
				xtype: 'button',
				name: 'addbutton',
				iconCls: 'add16',
				disabled: true,
				tooltip: 'Добавить человека',
				handler: function(btn,evnt){
					me.addPerson(data)
				}
			},{
				margin: '0 0 0 5',
				xtype: 'button',
				hidden: true,
				name: 'deletebutton',
				iconCls: 'delete16',
				tooltip: 'Удалить человека',
				handler: function(btn,evnt){
					me.deleteSimpleField(btn,data)
				}
			}]
		})
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

	_initCreateRequest: function() {
		var me = this;

		me.addSimpleField({
			commonName: 'Evidence',
			textFieldLabel: 'Документ',
			onChangeFn: Ext.emptyFn
		});

		var BaseForm = me.BaseForm.getForm();
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

		BaseForm.findField('EvnForensic_Date').setValue(new Date());
	},

	_initViewRequest: function(params) {
		if (!params.EvnForensic_id) {return false;}
		var me = this;

		var BaseForm = me.BaseForm.getForm();
		BaseForm.load({
			params: params,
			url: '/?c=BSME&m=loadForenBioOwnRequestForm',
			success: function(form, action) {
				log([form, action]);
			},
			failure: function() {
				Ext.Msg.alert('Ошибка', 'При получении данных заявки');
			}
		});
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
			items: [
				{
					xtype: 'container',
					padding: '0 10 0 0',
					width: '20%',
					bodyPadding: 10,
					autoHeight: true,
					defaults: {
						labelAlign: 'left',
						labelWidth: 250
					},
					items: [
						{
							xtype: 'hidden',
							name: 'EvnForensic_id'
						}, {
							xtype: 'hidden',
							name: 'EvnForensicComplexResearch_id'
						}, {
							xtype: 'textfield',
							padding: '0 0 0 0', // [top, right, bottom, left]
							fieldLabel: 'Номер заявки',
							readOnly: true,
							name: 'EvnForensic_Num'
						},{
							xtype: 'datefield',
							fieldLabel: 'Дата заявки',
							readOnly: true,
							format: 'd.m.Y',
							invalidText: 'Неправильный формат даты. Дата должна быть указана в формате ДД.ММ.ГГ',
							plugins: [new Ux.InputTextMask('99.99.9999')],
							name: 'EvnForensic_Date'
						}, {
							allowBlank: false,
							xtype: 'textfield',
							fieldLabel: 'Основание для проведения экспертизы',
							name: 'EvnForensicComplexResearch_Base'
						}, {
							allowBlank: false,
							xtype: 'PersonField',
							searchCallback: function() {
								me.defaultFocus = '[name=Evidence_Name]';
							},
							onChange: Ext.emptyFn,
							fieldLabel:'ФИО назначившего',
							idName: 'Person_cid',
							FioName: 'Person_FIO'
						}, {
							xtype: 'container',
							name:'EvidenceAllContainer',
							layout: {
								type: 'vbox',
								align: 'stretch'
							},
							items: []
						}
					]
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
					var params = {},
						//PersonContainerNames = ['Person'],
						simpleContainerNames = ['Evidence'],
						i,k,selectorContainerAll,containerAll,containerArray,obj,value,personTypeField;

					/*for (i=0;i<PersonContainerNames.length;i++) {
						selectorContainerAll = this.BaseForm.query('[name='+PersonContainerNames[i]+'AllContainer]');
						containerAll = (selectorContainerAll.length)?selectorContainerAll[0]:null;
						if (containerAll) {
							containerArray = containerAll.query('[name='+PersonContainerNames[i]+'Container]');
							params[PersonContainerNames[i]]= [];
							for (k=0;k<containerArray.length;k++) {
								value = containerArray[k].query('[name=Person_id]')[0].getValue();
								if (parseInt(value)) {
									obj = {'Person_id': value};
									personTypeField = containerArray[k].down('[name=EvnForensicGeneticEvidLink_IsVic]');
									if (personTypeField) {
										obj['EvnForensicGeneticEvidLink_IsVic']=personTypeField.getValue();
									}
									params[PersonContainerNames[i]].push(obj)
								}
							}
						}
					}*/

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

					params['Person_cid'] = this.BaseForm.getForm().findField('Person_cid').getValue();

					if (!this.BaseForm.isValid()) {
						Ext.Msg.alert('Проверка данных формы', 'Не все поля формы заполнены.<br>Незаполненные поля выделены особо.');
						return;
					}
					var loadMask =  new Ext.LoadMask(me, {msg:"Пожалуйста, подождите, идёт сохранение формы..."});
					//loadMask.show();
					this.BaseForm.submit({
						params : params,
						url: '/?c=BSME&m=saveForenComplexOwnRequest',
						success: function(form, action) {
							loadMask.hide();
							Ext.Msg.alert('', "Заявка успешно сохранена");
							me.close();
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

		me.callParent(arguments);
	},

	show: function() {
		var me = this;

		if (arguments && arguments[0]) {

			if (typeof arguments[0].callback == 'function') {
				me.callback = arguments[0].callback;
			}

			switch (arguments[0].action) {
				case 'add':

					me._initCreateRequest();

					break;
				case 'edit':

					me._initViewRequest(arguments[0]);

					break;
				default:
					break;
			}
		}

		me.callParent(arguments);
	}

});