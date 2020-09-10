/* 
 * Форма создания направления на биохимическое исследование
 */


Ext.define('common.BSME.ForenCorp.SecretaryWP.tools.swCreateBioChemDirection', {
	extend: 'Ext.window.Window',
    autoShow: true,
	modal: true,
	width: '50%',
	height: '70%',
	refId: 'forencorpcreatebiochemdirectionwnd',
	closable: true,
	title: 'Направление на биохимическое исследование',
	id: 'ForenCorpCreateBioChemDirectionWindow',
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
					xtype: 'checkboxgroup',
					fieldLabel: 'Цель',
					columns: 2,
					vertical: true,
					items: [
						{ boxLabel: 'Гликоген', name: 'rb', inputValue: '1' },
						{ boxLabel: 'Сахар', name: 'rb', inputValue: '2' },
						{ boxLabel: 'Сахар на асфиксию', name: 'rb', inputValue: '3' },
						{ boxLabel: 'Мочевина', name: 'rb', inputValue: '4' },
						{ boxLabel: 'Креатинин', name: 'rb', inputValue: '5' },
						{ boxLabel: 'Холинэстераза', name: 'rb', inputValue: '6' }
					]
				},{
					xtype: 'textfield',
					fieldLabel: '№ заключения о вскрытии',
					allowBlank: false,
					name: 'EvnForensic'
				},{
					xtype: 'datefield',
					fieldLabel: 'Дата заключения о вскрытии',
					format: 'd.m.Y',
					invalidText: 'Неправильный формат даты. Дата должна быть указана в формате ДД.ММ.ГГ',
					plugins: [new Ux.InputTextMask('99.99.9999')],
					name: 'EvnForensic'
				},{
					xtype: 'textareafield',
					plugins: [new Ux.Translit(true)],
					minHeight: 5,
					fieldLabel: 'Обстоятельства дела',
					name:'EvnForensic'
				},{
					xtype: 'textareafield',
					minHeight: 5,
					fieldLabel: 'Известные на момент смерти заболевания',
					name:'EvnForensic'
				},{
					xtype: 'textareafield',
					minHeight: 5,
					fieldLabel: 'Принятые незадолго до смерти лекарства',
					name:'EvnForensic'
				},{
					xtype: 'textareafield',
					minHeight: 5,
					fieldLabel: 'Принятые незадолго до смерти алкогольные напитки',
					name:'EvnForensic'
				},{
					xtype: 'textareafield',
					minHeight: 5,
					fieldLabel: 'Принятые незадолго до смерти отравляющие и наркотические в-ва',
					name:'EvnForensic'
				},{
					xtype: 'textfield',
					fieldLabel: 'Предполагаемая причина смерти',
					allowBlank: false,
					name: 'EvnForensic'
				},{
					xtype: 'datefield',
					fieldLabel: 'Дата забора материала',
					format: 'd.m.Y',
					invalidText: 'Неправильный формат даты. Дата должна быть указана в формате ДД.ММ.ГГ',
					plugins: [new Ux.InputTextMask('99.99.9999')],
					name: 'EvnForensic'
				},{
					xtype: 'datefield',
					fieldLabel: 'Дата отправки материала на исследование',
					format: 'd.m.Y',
					invalidText: 'Неправильный формат даты. Дата должна быть указана в формате ДД.ММ.ГГ',
					plugins: [new Ux.InputTextMask('99.99.9999')],
					name: 'EvnForensic'
				}]
			}]
		});
		
		Ext.apply(me,{
			items: this.BaseForm,
			buttons: [{
				xtype: 'button',	
				text: 'Готово',
				handler: function(btn,evnt) {
					var params = {};
					
					params['Person_zid'] = this.BaseForm.getForm().findField('Person_zid').getValue();
					
					if (!this.BaseForm.isValid()) {
						Ext.Msg.alert('Проверка данных формы', 'Не все поля формы заполнены.<br>Незаполненные поля выделены особо.');
						return;
					}
					var loadMask =  new Ext.LoadMask(me, {msg:"Пожалуйста, подождите, идёт сохранение формы..."}); 
//						loadMask.show();
					this.BaseForm.submit({
						params : params,
						url: '/?c=BSME&m=saveForenBioChemDirection',
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