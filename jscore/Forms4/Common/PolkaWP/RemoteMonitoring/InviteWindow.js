Ext6.define('common.PolkaWP.RemoteMonitoring.InviteWindow', {
	addCodeRefresh: Ext.emptyFn,
	//~ addHelpButton: Ext.emptyFn,
	closeToolText: 'Закрыть',

	alias: 'widget.swRemoteMonitoringInviteWindow',
	title: 'Приглашение в регистр АД',
	extend: 'base.BaseForm',
	maximized: false,
	width: 600,
	height: 415,
	modal: true,

	findWindow: false,
	closable: true,
	cls: 'arm-window-new arm-window-new-without-padding invite-remote-monitoring-window',
	renderTo: Ext6.getBody(),
	layout: 'border',

	autoScroll: true,
	autoShow: false,
	closable: true,
	closeAction: 'hide',
	draggable: true,

	viewModel: {
        data: {
            single: true //режим отображения окна - для одного пациента или для нескольких
        }
    },
	doSend: function() {
		var me = this,
			vm = me.getViewModel(),
			feedback_id = 0,
			feedback_name = '',
			phone = '',//телефон пациента
			msg = '',//сообщение (может быть с тегами)
			list = [];//список пациентов, отправляемый в ajax
		if(!vm.get('single')) {
			feedback_id = me.queryById('FeedbackComboM').getValue();
			feedback_name = me.queryById('FeedbackComboM').getSelection().get('name');
			msg = me.queryById('TextEditor').getValue();
			msg = msg.replace(/<div>/gi,'\n'); //переносы строк (первый абзац обычно без div)
			msg = msg.replace(/<.+?>/gi,''); //убираем все html-теги (включая </div>)
			msg = msg.replace(/&lt;/gi,'<'); //возвращаем видимые скобки
			msg = msg.replace(/&gt;/gi,'>'); //чтобы в шаблоне были теги вида <ФИО>,<Имя>
		} else {
			feedback_id = me.queryById('FeedbackCombo').getValue();
			feedback_name = me.queryById('FeedbackCombo').getSelection().get('name');
			msg = me.queryById('TextArea').getValue();
			var record = me.list.getStore().getAt(0);
			switch(feedback_id) {
				case 1:
				case 2: if(Ext6.isEmpty(record.get('Person_Phone')) && Ext6.isEmpty(record.get('Chart_Phone'))) {
						Ext6.Msg.alert(langs('Невозможно отправить приглашение'),langs('У пациента не указан телефон'));
						return;
					}
					break;
				case 3: if( Ext6.isEmpty(record.get('Person_Email')) ) {
						Ext6.Msg.alert(langs('Невозможно отправить приглашение'),langs('У пациента не указан email'));
						return;
					};
					break;
				case 4:
				case 5: if( Ext6.isEmpty(record.get('app')) ) {
						Ext6.Msg.alert(langs('Невозможно отправить приглашение'),langs('Нет данных об использовании пациентом мобильного приложения'));
						return;
					}
					break;
			}
		}
		//идем по списку пациентов и добавляем подходящих в рассылку
		//работает в том числе для режима одного пациента
		me.list.getStore().data.items.forEach(
			function(rec) {
				
				phone = rec.get('Person_Phone') ? rec.get('Person_Phone') : (rec.get('Chart_Phone') ? rec.get('Chart_Phone') : '');
				//добавлять только тех пациентов, у которых есть информация
				//о выбранном канале связи
				if(feedback_id==2 && Ext6.isEmpty(phone)) return;
				if(feedback_id==3 && Ext6.isEmpty(rec.get('Person_Email'))) return;
				if(feedback_id==5 && Ext6.isEmpty(rec.get('app'))) return;
				
				list.push({
					Person_id: rec.get('Person_id'),
					PersonLabel_id: rec.get('PersonLabel_id'),
					Sex_id: rec.get('Sex_id'),
					Person_SurName: me.normReg(rec.get('Person_SurName')),
					Person_FirName: me.normReg(rec.get('Person_FirName')),
					Person_SecName: me.normReg(rec.get('Person_SecName')),
					email: rec.get('Person_Email'),
					phone: phone
				});							
			}
		);
		//удалить из отображаемого на форме списка всех, кто попал в рассылку
		//оставшихся потом предложим на другой канал связи
		list.forEach(
			function(person) {
				var index = me.list.store.find('Person_id', person.Person_id);
				if(index>=0) me.list.store.removeAt(index);
			}
		);
		
		var msgTitle = msg.match(/Тема\:(.+)\n/);
		if(msgTitle) {
			msg = msg.replace(msgTitle[0], '');
			msgTitle = msgTitle[1];
		}
		if(Ext6.isEmpty(list)) {
			Ext6.Msg.alert(langs('Сообщение'),langs(
				(vm.get('single') ? 'Пациента' : 'Ни одного пациента из списка' )
				+' не удалось пригласить с помощью "'+feedback_name)+'". Используйте другой канал связи.');
		} else {
			me.InviteMask.show();
			Ext6.Ajax.request({
				url: '/?c=PersonDisp&m=InviteInMonitoring',
				params: {
					isSingle: vm.get('single'),
					Persons: Ext6.util.JSON.encode(list),
					FeedbackMethod: feedback_id,
					MessageText: msg,
					MessageTitle: msgTitle ? msgTitle : '',
					FeedbackMethod_id: feedback_id,
					MedStaffFact_id: getGlobalOptions().CurMedStaffFact_id
				},
				callback: function(options, success, response) {
					me.InviteMask.hide();
					if (success) {
						rdata = Ext6.JSON.decode(response.responseText);
						
						if(!vm.get('single') && me.list.store.getCount()>0) {
							Ext6.Msg.show({
								title: 'Вопрос',
								msg: 'Некоторых пациентов не удалось пригласить с помощью "'+feedback_name+'". Использовать другой канал связи?',
								buttons: Ext6.Msg.YESNO,
								icon: Ext6.Msg.QUESTION,
								fn: function(btn) {
									if ( btn === 'no' ) {
										me.hide();
									}
								}
							});
						} else	me.hide();
						me.arg.callback();
					} else {
						Ext6.Msg.alert('Ошибка','Не удалось отправить приглашение.');
						me.hide();
					}
				}
			});
		}
	},
	deletePerson: function(Person_id) {
		var index = me.list.store.find('Person_id',Person_id);
		if(index>=0) {
			if(me.list.store.getCount()>1)
				me.list.store.removeAt(index);
			else
				sw4.showInfoMsg({
					type: 'warning',
					text: 'В списке должен быть хотя бы один пациент',
					next: showNoticeExt6,
					//~ hideDelay: null //длительность показа уведомления
				});
		}
	},
	normReg: function(s) {
		if(Ext6.isEmpty(s) || s.length==0) return '';
		return s.slice(0,1).toUpperCase()+s.slice(1).toLowerCase();
	},
	getFio: function(rec) {
		return this.normReg(rec.Person_SurName)+' '+this.normReg(rec.Person_FirName)+' '+this.normReg(rec.Person_SecName);
		/*
		if(Ext6.isEmpty(s) || s.length==0) return '';
		var r='';
		s.toLowerCase().split(' ').forEach(function(e) {r+=e.slice(0,1).toUpperCase()+e.slice(1)+' ';});
		return r;*/
	},
	checkEnableSendButton: function() {
		var me = this,
			vm = me.getViewModel();
		me.queryById('sendButton').setDisabled( !me.FormPanel.isValid() );
		/*if(!me.queryById('sendButton').disabled) {
			if(vm.get('single')) {
				//если выбранный метод отправки не подходит пациенту
				switch(me.queryById('FeedbackCombo').getValue()) {
					case 1:
					case 2: me.queryById('sendButton').setDisabled( Ext6.isEmpty(me.list.getStore().getAt(0).data.Person_Phone) );
						break;
					case 3: me.queryById('sendButton').setDisabled( Ext6.isEmpty(me.list.getStore().getAt(0).data.Person_Email) );
						break;
					case 4:
					case 5: me.queryById('sendButton').setDisabled( Ext6.isEmpty(me.list.getStore().getAt(0).data.app) );
						break;
				}
				
			}
		}*/
	},
	changeFeedbackMethod: function(feedback_id) {
		var me = this,
			vm = me.getViewModel();
		itemIndex = me.msgStore.find('FeedbackMethod_id', feedback_id);
		var msg = '';
		if(itemIndex>=0) {
			msg = me.msgStore.getAt(itemIndex).get('LabelMessageText_Text');
			msg = msg.replace(/<номер телефона кабинета здоровья>/gi, me.healthcab.phone);
			msg = msg.replace(/<краткое наименование МО>/gi, me.healthcab.Lpu_Nick);
			msg = msg.replace(/<адрес электронной почты кабинета здоровья>/gi, me.healthcab.email);
			if(vm.get('single')) {
				msg = msg.replace(/<ФИО>/gi, me.getFio(me.arg.persons[0]));
				msg = msg.replace(/<Имя>/gi, me.normReg(me.arg.persons[0].Person_FirName));
				msg = msg.replace(/<Первые буквы фамилии и отчества без пробела>/gi, 
					me.arg.persons[0].Person_SurName.slice(0,1).toUpperCase() + me.arg.persons[0].Person_SecName.slice(0,1).toUpperCase()
				);
				
				me.queryById('TextArea').setValue(msg);
			} else {
				msg = msg.replace(/<(.+?)>/g,'<font color="#2196f3">&lt;$1&gt;</font>');
				msg = '<div>'+msg.split('\r').join('</div><div>')+'<div>';
				
				me.queryById('TextEditor').setValue(msg);
			}
		}
	},
	show: function() {
		var me = this,
			vm = me.getViewModel();
		me.callParent(arguments);
		me.queryById('TextEditor').getToolbar().hide();
		me.healthcab = {};
		
		if(!arguments[0]) {
			me.errorInParams();
			return false;
		}
		me.arg = arguments[0];		
		
		//Очищаем все, что понаписали в комбике
		me.queryById('FeedbackCombo').getStore().data.items.forEach(function(rec) {
			if(rec.get('nameTemp')) rec.set('name', rec.get('nameTemp'));
		});
		
		vm.set('single', me.arg.persons.length<2);//режим формы (одиночный/для нескольких пациентов)
		
		me.list.getStore().loadData(me.arg.persons);
		
		Person_ids = [];
		me.arg.persons.forEach(function(pers){
			Person_ids.push(pers.Person_id);
		});
		if(Ext6.isEmpty(me.LoadMask)) {
			me.LoadMask = new Ext6.LoadMask(me, {msg: LOAD_WAIT});
		}
		me.LoadMask.show();
		Ext6.Ajax.request({//получить данные с портала
			url: '/?c=PersonDisp&m=getPersonDataFromPortal',
			params: {
				Person_ids: Ext6.util.JSON.encode(Person_ids),
				LpuSection_id: getGlobalOptions().CurLpuSection_id
			},
			callback: function(options, success, response) {
				if (success) {
					rdata = Ext6.JSON.decode(response.responseText);
					me.healthcab = rdata.healthcab;//параметры кабинета здоровья
					if(!Ext6.isEmpty(me.healthcab.phone)) {
						//проверка формата номера телефона
						var regexp = /^(\+?7)?[\s\-]?\(?(\d{3})\)?[\s\-]?(\d{3})[\s\-]?(\d{2})[\s\-]?(\d{2})$/;
						me.healthcab.phone = me.healthcab.phone.replace(/[ \(\)_]/g,'');
						if ( !regexp.test(me.healthcab.phone) ) {
							me.healthcab.phone = '';
						} else {
							me.healthcab.phone = me.healthcab.phone.replace(regexp,'+7($2)-$3-$4-$5');
						}
					}
					//запомнить информацию из портала по каждому пациенту
					rdata.persons.forEach(function(pers) {
						index = me.list.store.find('Person_id',pers.Person_id);
						if(index>=0) {
							rec = me.list.store.getAt(index);
							if(!rec.get('app') && pers.isApp) {
								rec.set('app', true);
							}
							if(Ext6.isEmpty(rec.get('Person_Email')) && pers.email) {
								rec.set('Person_Email', pers.email.trim());
							}
							if(Ext6.isEmpty(rec.get('Person_Phone')) && pers.phone) {
								rec.set('Person_Phone', pers.phone.trim());
							}
						}
					});
					//Далее работаем с формой
					me.FormPanel.reset();
					
					me.queryById('Recipient1').setValue(
						me.getFio(me.arg.persons[0])+' '+
						Ext6.Date.format(Date.parse(me.arg.persons[0].Person_BirthDay), 'd.m.Y')
					);
					
					if(vm.get('single') && me.list.store.getCount()==1) {//правим комбик если пациент один
						var pers = me.list.store.getAt(0);
						//добавляем в комбо номер телефона
						var comborec = me.queryById('FeedbackCombo').getStore().getAt(0);
						var phone = pers.get('Person_Phone') ? pers.get('Person_Phone') : (pers.get('Chart_Phone') ? pers.get('Chart_Phone') : '' ); //приоритет сначала у телефона с формы редактирования человека, затем телефон с портала, и в конце из карты наблюдения ( ? )
						var regexp = /^(\+?7)?[\s\-]?\(?(\d{3})\)?[\s\-]?(\d{3})[\s\-]?(\d{2})[\s\-]?(\d{2})$/;
						if ( regexp.test(phone) ) {
							phone = phone.replace(regexp,'+7 $2 $3 $4 $5');
						} else phone = '';
						if(!Ext6.isEmpty(phone))
							comborec.set('name', comborec.get('nameTemp') +'  '+phone);
						//добавляем в комбо email
						var email = pers.get('Person_Email') ? pers.get('Person_Email') : (pers.get('Chart_Email') ? pers.get('Chart_Email') : ''); //предпочитаем email с портала,  email-у из карты наблюдения
						comborec = me.queryById('FeedbackCombo').getStore().getAt(1);
						if(!Ext6.isEmpty(email))
							comborec.set('name', comborec.get('nameTemp') +'  '+email );
					}
					
					me.msgStore.load();
					me.checkEnableSendButton();
					me.LoadMask.hide();
				}
			}
		});
	},
	initComponent: function() {
		var me = this;
		me.InviteMask = new Ext6.LoadMask(me, {msg: langs('Отправляется приглашение')});
		
		me.msgStore = new Ext6.data.JsonStore(
		{
			fields: [
				{name: 'LabelMessageText_id', type:'int'},
				{name: 'LabelMessageText_Text',  type:'string'},
				{name: 'LabelMessageType_id', type:'int'},
				{name: 'FeedbackMethod_id', type:'int'}
			],
			autoLoad: false,
			sorters: {
				property: 'LabelMessageText_id',
				direction: 'ASC'
			},
			proxy: {
				type: 'ajax',
				actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
				url : '/?c=MongoDBWork&m=getData',
				reader: {
					type: 'json'
				},
				extraParams: {object:'LabelMessageText', 
					LabelMessageText_id:'', LabelMessageText_Text:'', LabelMessageType_id:'', FeedbackMethod_id: ''
				}
			},
			tableName: 'LabelMessageText',
			//mode: me.queryMode
			listeners: {
				load: function(data) {
					me.msgStore.addFilter({
						property: 'LabelMessageType_id',
						value: 1 // ==приглашение
					});
					me.changeFeedbackMethod(2);
				}
			}
		});

		me.FormPanel = new Ext6.form.FormPanel({
			border: false,
            bodyPadding: '25 25 25 30',
			region: 'center',
			defaults: {
				labelWidth: 135,
				width: 135+400
			},
			items: [{
				xtype: 'textfield',
				fieldLabel: 'Адресат',
				itemId: 'Recipient1',
				readOnly: true,
				bind: {
					hidden: '{!single}',
				}
			}, {
				layout: 'column',
				bind: {
					hidden: '{single}',
				},
				itemId: 'Recipients',
				border: false,
				padding: '0 0 5 0',
				items: [
					{
						xtype: 'displayfield',
						fieldLabel: 'Адресаты',
						value: '',
						width: 135+5,
					}, {
						xtype: 'panel',
						autoScroll: true,
						width: 400-5,
						height: 100,
						items: [
							me.list = Ext6.create('Ext6.DataView', {
								store: {
									fields: ['Person_SurName', 'Person_FirName', 'Person_SecName'],
									data: []
								},
								itemTpl: new Ext6.XTemplate("<div class='addressee'>{[this.normReg(values.Person_SurName)]} {[this.normReg(values.Person_FirName)]} {[this.normReg(values.Person_SecName)]} {[this.birthDay(values.Person_BirthDay)]} ({[this.getAge(values)]}) <a href='#' onClick='Ext6.getCmp(\""+me.id+"\").deletePerson({Person_id});'><b class='delete-person'></b></a></div>",
								{
									normReg: me.normReg,
									birthDay: function(dt) {
										return Ext6.Date.format(Date.parse(dt), 'd.m.Y');
									},
									getAge: function(rec) {
										return getAgeString(rec);
									}
								})
							}),
						]
					}
				]
			}, {
				itemId: 'TextEditor',
				xtype: 'htmleditor',
				fieldLabel: 'Текст сообщения',
				allowBlank: false,
				bind: {
					hidden: '{single}',
					disabled: '{single}',
				},
				height: 160,
				listeners: {
					change: function() {
						me.checkEnableSendButton();
						//~ this.delayEditor(500);
					}
				},
				/*delaySearchId: null,
				delayEditor: function(delay) {
					var _this = this;
					
					if (this.delaySearchId) {
						clearTimeout(this.delaySearchId);
					}
					this.delaySearchId = setTimeout(function() {
						msg = _this.getValue();
						msg = msg.replace(/<\/?font.*?>/, '');
						//~ msg = msg.replace(/<номер телефона кабинета здоровья>/gi, me.healthcab.phone);
						//~ msg = msg.replace(/<краткое наименование МО>/gi, me.healthcab.Lpu_Nick);
						//~ msg = msg.replace(/<адрес электронной почты кабинета здоровья>/gi, me.healthcab.email);
						
						//~ msg = msg.replace(/<(.+?)>/g,'<font color="#2196f3">&lt;$1&gt;</font>');
						//~ msg = '<div>'+msg.split('\r').join('</div><div>')+'<div>';
												
						msg = msg.replace(/&lt;(.+?)&gt;/g,'<font color="#2196f3">&lt;$1&gt;</font>');
						_this.suspendEvents();
						_this.setValue(msg);
						_this.resumeEvents();
						_this.delaySearchId = null;
					}, delay);
				}*/
			}, {
				xtype: 'textareafield',
				fieldLabel: 'Текст сообщения',
				itemId: 'TextArea',
				bind: {
					hidden: '{!single}',
					disabled: '{!single}',
				},
				height: 160,
				allowBlank: false,
				listeners: {
					change: function() {
						me.checkEnableSendButton();
					}
				}
			}, {
				xtype: 'combo',
				fieldLabel: 'Пригласить через',//для одного пациента
				bind: {
					hidden: '{!single}',
					disabled: '{!single}',
				},
				itemId: 'FeedbackCombo',
				allowBlank: false,
				displayField: 'name',
				valueField: 'id',
				value: 2,
				store: Ext6.create('Ext6.data.Store', {
					fields: ['id', 'name'],
					data: [
						{"id": 2, "name":"СМС", "nameTemp":"СМС"},
						{"id": 3, "name":"Электронная почта", "nameTemp":"Электронная почта"},
						//~ {"id": 4, "name":"Региональный портал"}, //на портале пока нет уведомлений
						{"id": 5, "name":"Мобильное приложение"}
					]
				}),
				listeners: {
					change: function() {
						me.checkEnableSendButton();
						me.changeFeedbackMethod(this.getValue());
					}
				}
			}, {
				xtype: 'combo',
				fieldLabel: 'Канал связи',//для многих пациентов
				bind: {
					hidden: '{single}',
					disabled: '{single}',
				},
				itemId: 'FeedbackComboM',
				allowBlank: false,
				displayField: 'name',
				valueField: 'id',
				value: 2,
				store: Ext6.create('Ext6.data.Store', {
					fields: ['id', 'name'],
					data : [
						{"id": 2, "name":"СМС"},
						{"id": 3, "name":"Электронная почта"},
						//~ {"id": 4, "name":"Региональный портал"}, //на портале пока нет уведомлений
						{"id": 5, "name":"Мобильное приложение"}
					]
				}),
				listeners: {
					change: function() {
						me.checkEnableSendButton();
						me.changeFeedbackMethod(this.getValue());
					}
				}
			}]
		});

		Ext6.apply(me, {
			items: [
				me.FormPanel
			],
			border: false,
			buttons:
			[ '->'
			, {
				userCls:'buttonCancel buttonPoupup',
				text: langs('Отмена'),
				margin: 0,
				handler: function() {
					me.hide();
				}
			}, {
				userCls:'buttonAccept buttonPoupup',
				text: langs('Отправить'),
				itemId: 'sendButton',
				margin: '0 19 0 0',
				handler: function() {
					me.doSend();
				}
			}]
		});

		this.callParent(arguments);
	}
});