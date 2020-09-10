/* 
 * Форма создания направления на исследование вещественных доказательств в АРМ Секретаря службы "Медико-криминалистическая экспертиза"
 */


Ext.define('common.BSME.ForenCorp.SecretaryWP.tools.swCreateForenChemDirection', {
	extend: 'Ext.window.Window',
    autoShow: true,
	modal: true,
	width: '50%',
	height: '80%',
	refId: 'forencorpcreatebiochemdirectionwnd',
	closable: true,
	title: 'Направление на исследование  вещественных доказательств в медико-криминалистическом отделении',
	id: 'ForenCorpCreateForenChemDirectionWindow',
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
	
	addJarContainer: function(params) {

		if (!params) {
			params = {};
		}
		
		var filedPrefix = (params.filedPrefix)?params.filedPrefix:'Jar'
		
		var containerAllSelectorArray = this.BaseForm.query('[name='+filedPrefix+'AllContainer]'),
			containerAll = (containerAllSelectorArray.length)?containerAllSelectorArray[0]:null,
			cnt = (containerAll)?containerAll.items.length:null,
			me = this;
				
		if (!containerAll) 
			return false;
		
		
		if (cnt) {
			var prevField = containerAll.items.getAt(cnt-1);
			if (prevField) {
				prevField.query('[name=Description]')[0].setReadOnly(true);
				prevField.query('[name=Organ]')[0].setReadOnly(true);
				prevField.query('[name=Weight]')[0].setReadOnly(true);
				prevField.query('[name=C2H5OHPerc]')[0].setReadOnly(true);
				prevField.query('[name=C2H5OHMl]')[0].setReadOnly(true);
				
				prevField.query('[name=addbutton]')[0].setVisible(false);
				prevField.query('[name=deletebutton]')[0].setVisible(true);
			}
		}
		
		containerAll.add({
			xtype:'container',
			margin: '0 0 5 0',
			name: filedPrefix+'Container',
			layout: {
				type: 'vbox',
				align: 'stretch'
			},
			defaults: {
				labelAlign: 'left',
				labelWidth: 250
			},
			items: [
			{
				xtype:'container',
				margin: '0 0 5 0',
				layout: {
					type: 'hbox',
					align: 'stretch'
				},
				defaults: {
					labelAlign: 'left',
					labelWidth: 250
				},
				items: 
				[{
					xtype: 'textfield',
					flex: 1,
					fieldLabel: (params.descr_fieldlabel)?(params.descr_fieldlabel+' #'+(1*cnt+1)):('Описание #'+(1*cnt+1)),
					name: 'Description',
					listeners: {
						change: function(field,nV,oV) {
							field.up('container').down('[name=addbutton]').setDisabled(!nV);
						}
					}
				},{
					margin: '0 0 0 5',
					xtype: 'button',
					name: 'addbutton',
					disabled: true,
					iconCls: 'add16',
					tooltip: 'Добавить',
					handler: function(btn,evnt){
						me.addJarContainer()
					}
				},{
					margin: '0 0 0 5',
					xtype: 'button',
					hidden: true,
					name: 'deletebutton',
					iconCls: 'delete16',
					tooltip: 'Удалить',
					handler: function(btn,evnt){
						me.deleteJarContainer(btn,filedPrefix)
					}
				}]
			},{
				xtype: 'container',
				margin: '0 0 5 0',
				layout: {
					type: 'hbox',
					align: 'stretch'
				},
				defaults: {
					labelAlign: 'left',
					labelWidth: 250
				},
				items: [
					{
						xtype: 'textfield',
						flex:1,
						fieldLabel: (params.organ_fieldlabel)?(params.organ_fieldlabel+' #'+(1*cnt+1)):('Орган #'+(1*cnt+1)),
						name: 'Organ',
						padding: '0 20 0 0' // [top, right, bottom, left]
					},{
						xtype: 'textfield',
						labelWidth: 200,
						flex:1,
						fieldLabel: (params.weight_fieldlabel)?(params.weight_fieldlabel+' #'+(1*cnt+1)):('Вес банки #'+(1*cnt+1)),
						name: 'Weight'
					}
				]
			},{
				xtype: 'container',
				margin: '0 0 5 0',
				layout: {
					type: 'hbox',
					align: 'stretch'
				},
				defaults: {
					labelAlign: 'left',
					labelWidth: 250
				},
				items: [
					{
						xtype: 'textfield',
						fieldLabel: 'Образец Спирта (%%) #'+(1*cnt+1),
						flex:1,
						name: 'C2H5OHPerc',
						padding: '0 20 0 0' // [top, right, bottom, left]
					},{
						xtype: 'textfield',
						labelWidth: 200,
						flex:1,
						fieldLabel: 'Образец Спирта (мл.) #'+(1*cnt+1),
						name: 'C2H5OHMl'
					},
				]
			},
			]
		});
	},
	deleteJarContainer: function(btn,prefix) {
		var containerAllSelectorArray = this.BaseForm.query('[name='+prefix+'AllContainer]'),
			containerAll = (containerAllSelectorArray.length)?containerAllSelectorArray[0]:null,
			cnt = (containerAll)?containerAll.items.length:null;
			
		if (!containerAll || !(containerAll.queryById(btn.id)))
			return false;
		
		
		containerAll.remove(btn.up('container[name='+prefix+'Container]'));
		
		var containerItemsArray = containerAll.query('[name='+prefix+'Container]');
		var description,organ,weight,label;
		for (var i=0;i<containerItemsArray.length;i++) {
			description = containerItemsArray[i].query('[name=Description]')[0];
			label = description.getFieldLabel();
			description.setFieldLabel(label.replace(/#\d+/,'#'+(1*i+1)));
			
			organ = containerItemsArray[i].query('[name=Organ]')[0];
			label = organ.getFieldLabel();
			organ.setFieldLabel(label.replace(/#\d+/,'#'+(1*i+1)));
			
			weight = containerItemsArray[i].query('[name=Weight]')[0];
			label = weight.getFieldLabel();
			weight.setFieldLabel(label.replace(/#\d+/,'#'+(1*i+1)));
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
						name: 'Person_FIO',
						readOnly: true,
						allowBlank: false,
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
							}});
						}
					}]
				},{
					xtype:'container',
					name: 'JarAllContainer', 
					items: []
				},{
					xtype:'container',
					name: 'FlakAllContainer', 
					items: []
				},{
					xtype: 'datefield',
					fieldLabel: 'Дата смерти',
					format: 'd.m.Y',
					invalidText: 'Неправильный формат даты. Дата должна быть указана в формате ДД.ММ.ГГ',
					plugins: [new Ux.InputTextMask('99.99.9999')],
					name: 'EvnForensicChemDirection_DeathDate'
				},{
					xtype: 'datefield',
					fieldLabel: 'Дата вскрытия',
					format: 'd.m.Y',
					invalidText: 'Неправильный формат даты. Дата должна быть указана в формате ДД.ММ.ГГ',
					plugins: [new Ux.InputTextMask('99.99.9999')],
					name: 'EvnForensicChemDirection_DissectionDate'
				},{
					xtype: 'textareafield',
					plugins: [new Ux.Translit(true)],
					minHeight: 5,
					fieldLabel: 'Обстоятельства дела',
					name:'EvnForensicChemDirection_Facts'
				},{
					xtype: 'textfield',
					fieldLabel: 'Предполагаемая причина смерти',
					allowBlank: false,
					name: 'EvnForensicChemDirection_CauseOfDeath'
				},{
					xtype: 'textareafield',
					minHeight: 5,
					fieldLabel: 'Вопросы для разрешения',
					name:'EvnForensicChemDirection_Resolve'
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
						JarContainerNames = [
							'Jar',
							'Flak'
						],
						i,k,selectorContainerAll,containerAll,containerArray,obj,value,personTypeField;
					
					for (i=0;i<JarContainerNames.length;i++) {
						selectorContainerAll = this.BaseForm.query('[name='+JarContainerNames[i]+'AllContainer]');
						containerAll = (selectorContainerAll.length)?selectorContainerAll[0]:null;
						if (containerAll) {
							containerArray = containerAll.query('[name='+JarContainerNames[i]+'Container]');
							params[JarContainerNames[i]]= [];
							for (k=0;k<containerArray.length;k++) {
								obj = {}
								obj.description = containerArray[k].query('[name=Description]')[0].getValue();
								obj.organ = containerArray[k].query('[name=Organ]')[0].getValue();
								obj.weight = containerArray[k].query('[name=Weight]')[0].getValue();
								obj.c2h5ohPerc = containerArray[k].query('[name=C2H5OHPerc]')[0].getValue();
								obj.c2h5ohMl = containerArray[k].query('[name=C2H5OHMl]')[0].getValue();
								params[JarContainerNames[i]].push(obj)
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
						url: '/?c=BSME&m=saveForenChemDirection',
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
		
		me.addJarContainer({
			filedPrefix: 'Jar',
			weight_fieldlabel: 'Вес банки',
			organ_fieldlabel: 'Орган в банке',
			descr_fieldlabel: 'Описание банки'
		});
		
		me.addJarContainer({
			filedPrefix: 'Flak',
			weight_fieldlabel: 'Вес флакона',
			organ_fieldlabel: 'Орган во флаконе',
			descr_fieldlabel: 'Описание флакона'
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