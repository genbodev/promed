/* 
 * Форма создания направления на исследование трупной крови в АРМ Секретаря службы "Cудебно-медицинская экспертиза трупов с судебно-гистологическим отделением"
 */


Ext.define('common.BSME.ForenCorp.SecretaryWP.tools.swCreateCorpBludDirection', {
	extend: 'Ext.window.Window',
    autoShow: true,
	modal: true,
	width: '50%',
	height: '50%',
	refId: 'forencorpcreatecorpbluddirectionwnd',
	closable: true,
	title: 'Направление на исследование трупной крови в судебно-биологическом отделении',
	id: 'ForenCorpCreateCorpBludDirectionWindow',
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
						fieldLabel: 'Исследуемое лицо',
						margin: '0 5 0 0',
						listeners: {
							focus: function(field,focusEvt,evtOpts){
								var Person_id =field.up('container').down('[name=ReasearchedPerson_id]'),
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
						name: 'ReasearchedPerson_id',
						value: 0
					},{
						margin: '0 0 0 5',
						xtype: 'button',
						iconCls: 'search16',
						name: 'searchbutton',
						tooltip: 'Поиск человека',
						handler: function(btn,evnt) {
							var Person_id =btn.up('container').down('[name=ReasearchedPerson_id]'),
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
					xtype: 'datefield',
					padding: '20 0 0 0', // [top, right, bottom, left]
					fieldLabel: 'Дата взятия',
					name: 'EvnForensicGeneticCadBlood_TakeDate',
					format: 'd.m.Y',
					invalidText: 'Неправильный формат даты. Дата должна быть указана в формате ДД.ММ.ГГ',
					plugins: [new Ux.InputTextMask('99.99.9999')]
				}]
			}]
		});
		
		Ext.applyIf(me,{
			items: this.BaseForm,
			buttons: [{
				xtype: 'button',	
				text: 'Готово',
				handler: function(btn,evnt) {
					var params = {};
					
					params['ReasearchedPerson_id'] = this.BaseForm.getForm().findField('ReasearchedPerson_id').getValue();
					params['Evn_pid'] = this.Evn_pid;
					
					if (!this.BaseForm.isValid()) {
						Ext.Msg.alert('Проверка данных формы', 'Не все поля формы заполнены.<br>Незаполненные поля выделены особо.');
						return;
					}
					var loadMask =  new Ext.LoadMask(me, {msg:"Пожалуйста, подождите, идёт сохранение формы..."}); 
//						loadMask.show();
					this.BaseForm.submit({
						params : params,
						url: '/?c=BSME&m=saveForenCorpBloodDirection',
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
		
		me.callParent();
		
		
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