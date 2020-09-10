/* 
 * Форма добавления заявки в АРМ Секретаря службы "Судебно-биологическое отделение с молекулярно-генетической лабораторией"
 */


Ext.define('common.BSME.ForenPers.SecretaryWP.tools.swCreateRequestWindow', {
	extend: 'Ext.window.Window',
    autoShow: true,
	modal: true,
	width: 600,
	resizable : false,
	//height: '80%',
	refId: 'forenperscreaterequestwnd',
	closable: true,
//    header: false,
	title: 'Новая заявка',
	id: 'ForenPersCreateRequestWindow',
	border: false,
	layout: {
        align: 'stretch',
        type: 'vbox'
    },
	callback: Ext.emptyFn,
	EvnForensicSub_id: null,
	//Будем передавать MedPersonal_eid, чтобы при создании в АРМ эксперта подставлялся идентификатор пользователя
	MedPersonal_eid: null,
	// Зададим значения по умолчанию на непредвиденный случай
	XmlType_id: 13, //Тип документа
	ForensicSubType_id: 1, //Тип заявки
	EvnForensicSub_Inherit: 1, // Копирование разделов заключения из связной экспертизы при создании заявки
	
	//Поиск человека по базе
	//Параметры: data - объект
	//	dete.callback - фунция, вызываемая после установки значения в person_id
	searchPerson: function(data) {
		Ext.create('common.BSME.tools.swBSMEPersonWinSearch', 
		{
			callback: data.callback
		}).show()
	},
	/**
	 * Функция добавления строки ввода для произвольного поля с удалением и добавлением
	 * Параметры: data - Объект:
	 *				data.commonName - Наименование типа поля, которое будет являться префиксом для имён контейнеров и поля ввода (напр. Person)
	 *				data.textFieldLabel - Подпись для текстового поля ввода
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
				labelWidth: 200,
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
				}
			}]
		});
	},
	
	
	addAttachmentContainer: function() {
		var containerAllSelectorArray = this.BaseForm.query('[name=AttachmentAllContainer]'),
			containerAll = (containerAllSelectorArray.length)?containerAllSelectorArray[0]:null,
			cnt = (containerAll)?containerAll.items.length:null,
			me = this,
			lastNum = 0;
			
		if (!containerAll) 
			return false;
		
		if (cnt) {
			var prevField = containerAll.items.getAt(cnt-1);
			if (prevField) {
				prevField.query('[name=addbutton]')[0].setVisible(false);
				prevField.query('[name=deletebutton]')[0].setVisible(true);
			}
			lastNum = prevField.query('[name=Attachment_Num]')[0].getValue();
		}
		
		containerAll.add({
			xtype:'container',
			margin: '0 0 5 0',
			name: 'AttachmentContainer',
			layout: {
				type: 'vbox',
				align: 'stretch'
			},
			defaults: {
				labelAlign: 'left',
				labelWidth: 200
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
					labelWidth: 200
				},
				items: 
				[{
					flex: 1,
					allowBlank: true,
					name: 'AttachmentField_'+(lastNum*1+1),
					fieldLabel: 'Прикрепить файл',
					buttonText: 'Выбрать...',
					xtype: 'fileuploadfield',
					listeners: {
						change: function( field, value, eOpts ) {
							if (value) {
								var btn = this.up('container').query('[name=addbutton]')[0];
								btn.setDisabled(false)
							}
						}
					}
				},
				{
					xtype: 'hidden',
					name: 'Attachment_Num',
					value: (lastNum*1+1)
				},
				{
					margin: '0 0 0 5',
					xtype: 'button',
					name: 'addbutton',
					disabled: true,
					iconCls: 'add16',
					tooltip: 'Добавить',
					handler: function(btn,evnt){
						me.addAttachmentContainer()
					}
				},{
					margin: '0 0 0 5',
					xtype: 'button',
					hidden: false,
					name: 'deletebutton',
					iconCls: 'delete16',
					tooltip: 'Удалить',
					handler: function(btn,evnt){
						me.deleteAttachmentContainer(btn);
						var cnt = containerAll ? containerAll.items.length : null;
						if ( cnt === 0 ) {
							me.addAttachmentContainer();
						}
					}
				}]
			},{
				xtype: 'textfield',
				fieldLabel: 'Описание файла',
				name: 'comment['+(lastNum*1+1)+']'
			}]
		});
	},
	deleteAttachmentContainer: function(btn) {
		var containerAllSelectorArray = this.BaseForm.query('[name=AttachmentAllContainer]'),
			me = this,
			containerAll = (containerAllSelectorArray.length)?containerAllSelectorArray[0]:null;
			
		if (!containerAll || !(containerAll.queryById(btn.id)))
			return false;
		
		containerAll.remove(btn.up('container[name=AttachmentContainer]'));
		
		var cnt = containerAll.items.length;
		var prevField = containerAll.items.getAt(cnt - 1);
		if (prevField) {
			prevField.query('[name=addbutton]')[0].setVisible(true);
		}
	},
	addSavedAttachment: function(data) {
		/*
		 * '<p class="journal-header"> Прикрепленные файлы </p><hr/>',
			'<p><a class="savedFile" href="/?c=EvnMediaFiles&m=getFile&EvnMediaData_id={EvnMediaData_id}&fileName={EvnMediaData_FilePath}" target="_blank">{EvnMediaData_FileName}</a></p>',
			'<p>Описание: {EvnMediaData_Comment}</p>',
			'</tpl>',
		 */
		var  me = this;
		if (!data || !data.EvnMediaData_id || !data.EvnMediaData_FilePath || !data.EvnMediaData_FileName) {
			return false;
		}
		
		var containerAllSelectorArray = this.BaseForm.query('[name=SavedAttachmentAllContainer]'),
			containerAll = (containerAllSelectorArray.length)?containerAllSelectorArray[0]:null;
			
		if (!containerAll) 
			return false;
		
		
		
		containerAll.add({
			xtype:'container',
			margin: '0 0 5 0',
			name: 'SavedAttachmentContainer',
			layout: {
				type: 'vbox',
				align: 'stretch'
			},
			defaults: {
				labelAlign: 'left',
				labelWidth: 200
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
					labelWidth: 200
				},
				items: 
				[{
					flex: 1,
					xtype: 'label',
					html:'<p><a class="savedFile" href="/?c=EvnMediaFiles&m=getFile&EvnMediaData_id='+data.EvnMediaData_id+'&fileName='+data.EvnMediaData_FilePath+'" target="_blank">'+data.EvnMediaData_FileName+'</a></p>'
				},{
					margin: '0 0 0 5',
					xtype: 'button',
					name: 'deletebutton',
					iconCls: 'delete16',
					tooltip: 'Удалить',
					handler: function(btn,evnt){
						me.deleteSavedAttachment(data,btn)
					}
				}]
			},{
				xtype: 'label',
				text: 'Описание файла: '+(data.EvnMediaData_Comment||'')
			}]
		});
	},
	deleteSavedAttachment: function(data,btn) {
		var  me = this;
		
		if (!data && !data.EvnMediaData_id || !data.EvnMediaData_FilePath) {
			return false;
		}
		
		var containerAllSelectorArray = this.BaseForm.query('[name=SavedAttachmentAllContainer]'),
			containerAll = (containerAllSelectorArray.length)?containerAllSelectorArray[0]:null;
			
		if (!containerAll) 
			return false;
		
		Ext.MessageBox.confirm('Сообщение', 'Вы уверены, что хотите удалить файл?', function(confirmbtn){
			if ( confirmbtn !== 'yes' ) {
				return;
			}
			var loadMask = new Ext.LoadMask(me, {msg:'Пожалуйста, подождите, идёт удаление файла...'}); 
			loadMask.show();
			
			Ext.Ajax.request({
				params: {
					EvnMediaData_id: data.EvnMediaData_id,
					file_name: data.EvnMediaData_FilePath
				},
				url: '/?c=EvnMediaFiles&m=deleteEvnMediaFile',
				callback: function(params,success,result) {
					loadMask.hide();

					if (result.status !== 200) {
						Ext.Msg.alert('Ошибка', 'При запросе возникла ошибка');
						return false;
					} 
					var resp = Ext.JSON.decode(result.responseText, true);
					if (resp === null) {
						Ext.Msg.alert('Ошибка', 'Ошибка обработки запроса');
						return false;
					}
					
					btn.up('container').destroy();
				}
			});
		});
	},
	loadForm: function() {
		var me = this;
		if (!me.EvnForensicSub_id) {
			return false;
		}
		Ext.Ajax.request({
			params: {EvnForensic_id:me.EvnForensicSub_id},
			url:'/?c=BSME&m=getForenPersRequest',
			success: function(response) {
				var response_obj = Ext.JSON.decode(response.responseText);																															
				if (response_obj) {
					me.setFormValues(response_obj);
				} else {
					Ext.Msg.alert('Ошибка', 'При получении номера заявки произошла ошибка');

				}
			},
			failure: function() {
				Ext.Msg.alert('Ошибка', 'При получении номера заявки произошла ошибка');
			}
		});
	},
	setFormValues: function(data) {
		var me = this;
		var BaseForm = me.BaseForm.getForm();
		
		if (!data) {
			return false;
		}
		BaseForm.setValues(data);
		BaseForm.findField('Org_did').getStore().load();

		if (data.attachment && data.attachment.length) {
			for (var i=0;i< data.attachment.length; i++) {
				me.addSavedAttachment(data.attachment[i]);
			}
		}
		
		
	},
	initComponent: function() {
		var me = this,
			conf = me.initialConfig;
		
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
					width: '100%',
					bodyPadding: 10,
					autoHeight: true,
					defaults: {
						labelAlign: 'left',
						labelWidth: 200
					},
					items: [
					{
						xtype: 'hidden',
						name: 'ForensicSubType_id',
						value: me.ForensicSubType_id
					},{
						xtype: 'hidden',
						name: 'XmlType_id',
						value: me.XmlType_id
					},{
						xtype: 'hidden',
						name: 'EvnForensicSub_Inherit',
						value: me.EvnForensicSub_Inherit
					},{
						xtype: 'textfield',
						name: 'EvnForensicSub_Num',
						padding: '0 0 0 0', // [top, right, bottom, left]
						fieldLabel: 'Номер заявки'
					},{
						xtype: 'container',
						defaults: {
							labelAlign: 'left',
							labelWidth: 200
						},
						layout: {
							padding: '0 0 0 0', // [top, right, bottom, left]
							align: 'stretch',
							type: 'vbox'
						},
						width: '100%',
						items:[
						{
							xtype: 'container',
							layout: {
								padding: '0 0 5 0', // [top, right, bottom, left]
								align: 'stretch',
								type: 'hbox'
							},
							items: [
								{
									labelWidth: 200,
									xtype: 'swdatefield',
									fieldLabel: 'Дата поступления экспертизы',
									name: 'EvnForensicSub_ExpertiseComeDate',
									readOnly: false,
									flex: 5
								},
								{
									padding: '0 0 0 3', // [top, right, bottom, left]
									xtype: 'swtimefield',									
									name: 'EvnForensicSub_ExpertiseComeTime',
									flex: 1
								}
							]
						},
						{
							xtype: 'DoubleValueTriggerField',
							name:'EvnForensicSub_pid',						
							fieldLabel:'Первичная экспертиза',
							disabled: conf.disbleEvnForensicSub_pid == false ? false : true,
							hiddenFieldName: 'EvnForensicSubFirstExp_Num',
							maskRe: /\d/,
							onTriggerClick: function() {
								var trig = this,
									rawVal = this.rawValue,
									args = {},
									primaryRequestSearchWindow = Ext.create('common.BSME.tools.swBSMEPrimaryRequestSearch',{
										EvnForensic_Num:rawVal
									});
									
								var Person_Fio = me.BaseForm.getForm().findField('Person_Fio'),
									Person_Fio_value = Person_Fio.getValue();
								
								if ( Person_Fio_value != '' ) {
									var r = Person_Fio_value.split(' ');
									args = {
										Search_Person_SurName: r[0] || '',
										Search_Person_FirName: r[1] || '',
										Search_Person_SecName: r[2] || ''
									};
								}
									
								primaryRequestSearchWindow.show(args);
								primaryRequestSearchWindow.on('selectEvnForensic', function(rec){
									trig.setValue(rec.get('EvnForensic_Num'), rec.get('EvnForensic_id'));
									
									var Person_id = me.BaseForm.getForm().findField('Person_id'),
										Person_Fio = me.BaseForm.getForm().findField('Person_Fio');
										
									if ( parseInt(Person_id.getValue()) < 1 && parseInt(rec.get('Person_id')) > 0 ) {
										Person_id.setValue(rec.get('Person_id'));
										Person_Fio.setValue(rec.get('Person_Fio'));
									}
									
									Ext.Msg.confirm('Подтверждение','Подставить «исследовательскую часть» и «выводы» при создании нового документа из первичной экспертизы?',function(){
										this.BaseForm.getForm().findField('EvnForensicSub_Inherit').setValue(2);
									},me);
								});
							},
							listeners: {
								'keydown': function(t,e,o){
									if(e.getKey()==13){
										t.onTriggerClick();
									}
								}
							}
						},
						{
							xtype: 'container',
							layout: {
								padding: '0 0 5 0', // [top, right, bottom, left]
								align: 'stretch',
								type: 'hbox'
							},
							items: [
								{
									xtype: 'swdatefield',
									fieldLabel: 'Дата постановления',
									name: 'EvnForensicSub_ResDate',
									labelWidth: 200,
									hidden: (me.ForensicSubType_id == 3),
									disabled: (me.ForensicSubType_id == 3),
									flex: 5
								},
								{
									padding: '0 0 0 3', // [top, right, bottom, left]
									xtype: 'swtimefield',
									name: 'EvnForensicSub_ResTime',
									hidden: (me.ForensicSubType_id == 3),
									disabled: (me.ForensicSubType_id == 3),
									flex: 1
								}
							]
						},{
							xtype: 'BSMEPersonField',
							labelWidth: 200,
							name:'AssignedPersContainer',
							hidden: (me.ForensicSubType_id == 3),
							disabled: (me.ForensicSubType_id == 3),
							searchCallback: function() {
								me.defaultFocus = '[name=ForensicIniciatorPost_id]';
							},
							fieldLabel:'Инициатор экспертизы',
							idName: 'Person_cid',
							FioName: 'Iniciator_Fio'
						},{
							xtype:'swForensicIniciatorPostCombo',
							hidden: (me.ForensicSubType_id == 3),
							disabled: (me.ForensicSubType_id == 3),
							name: 'ForensicIniciatorPost_id'
						},{
							xtype:'container',
							hidden: (me.ForensicSubType_id == 3),
							disabled: (me.ForensicSubType_id == 3),
							name:'OrgContainer',
							margin: '0 0 5 0',
							layout: {
								type: 'hbox',
								align: 'stretch'
							},
							defaults: {
								labelAlign: 'left',
								labelWidth: 200
							},
							items: 
							[{
								flex: 1,
								xtype: 'dOrgCombo',
								fieldLabel: 'Направившая организация',
								name: 'Org_did'
							},{
								margin: '0 0 0 10',
								xtype: 'button',
								iconCls: 'add16',
								tooltip: 'Добавить ораганизацию',
								handler: function(btn,evnt){
									Ext.create('sw.tools.subtools.swOrgEditWindow',{action: 'add'}).show();
								}
							}]
						},{
						
							xtype: 'BSMEPersonField',
							labelWidth: 200,
							name:'PersContainer',
							searchCallback: function() {
								me.defaultFocus = '[name=MedPersonal_eid]';
							},
							fieldLabel:'Подэкспертное лицо',
							idName: 'Person_id',
							FioName: 'Person_Fio'
						}, {
							name: 'MedPersonal_eid',
							fieldLabel: 'Эксперт',
							xtype: 'swMedPersonalExpertsCombo'
						},{
							xtype: 'container',
							layout: {
								padding: '0 0 5 0', // [top, right, bottom, left]
								align: 'stretch',
								type: 'hbox'
							},
							items: [
								{
									labelWidth: 200,
									xtype: 'swdatefield',
									fieldLabel: 'Дата происшествия',
									name: 'EvnForensicSub_AccidentDate',
									flex: 5
								},
								{
									padding: '0 0 0 3', // [top, right, bottom, left]
									xtype: 'swtimefield',
									name: 'EvnForensicSub_AccidentTime',
									flex: 1
								}
							]
						},{
							xtype:'container',
							name: 'SavedAttachmentAllContainer', 
							items: []
						},{
							xtype:'container',
							name: 'AttachmentAllContainer', 
							items: []
						}]
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
					var params = {},
						baseForm = this.BaseForm.getForm();

					params['EvnForensicSub_id'] = me.EvnForensicSub_id;
					params['XmlType_id'] = me.XmlType_id;
//					params['ForensicSubType_id'] = baseForm.findField('ForensicSubType_id').getValue();
//					params['Person_cid'] = baseForm.findField('Person_cid').getValue();
//					params['Person_id'] = baseForm.findField('Person_id').getValue();
					
					var loadMask =  new Ext.LoadMask(me, {msg:"Пожалуйста, подождите, идёт сохранение формы..."}); 
					loadMask.show();
					this.BaseForm.submit({
						params : params,
						url: '/?c=BSME&m=saveForenPersRequest',
						success: function(form, action) {
							loadMask.hide();
							if (!action.result.Evn_id) {
								Ext.Msg.alert('Ошибка', "Не получен идентификатор случая");
							} else {
								Ext.Msg.alert('', "Заявка успешно сохранена");
								me.close();
							}
						},
						failure: function(form, action) {
							loadMask.hide();
							switch (action.failureType) {
								case Ext.form.action.Action.CLIENT_INVALID:{
									Ext.Msg.alert('Проверка данных формы', 'Не все поля формы заполнены.<br>Незаполненные поля выделены особо.');
									break;}
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
					
					//@TODO: Доделать сохранение прикреплений
					
					
				}.bind(this)
			}]
		})
		
		this.addAttachmentContainer();
		
		if (me.EvnForensicSub_id) {
			me.title = 'Редактирование заявки';
			me.loadForm();
		}
		
		me.callParent(arguments);
	},
	listeners: {
		show: function(wnd,eOpts) {

			if (!wnd.EvnForensicSub_id) {
				var BaseForm = wnd.BaseForm.getForm();

				Ext.Ajax.request({
					params: {
						ForensicSubType_id:wnd.ForensicSubType_id
					},
					url: '/?c=BSME&m=getNextForenPersRequestNumber',
					success: function(response) {
						var response_obj = Ext.JSON.decode(response.responseText);																															
						if (response_obj.EvnForensic_Num) {
							BaseForm.findField('EvnForensicSub_Num').setValue(response_obj.EvnForensic_Num);
						} else {
							Ext.Msg.alert('Ошибка', 'При получении номера заявки произошла ошибка');

						}
		},
					failure: function() {
						Ext.Msg.alert('Ошибка', 'При получении номера заявки произошла ошибка');
					}
				});

				BaseForm.findField('EvnForensicSub_ExpertiseComeDate').setValue(new Date())
				BaseForm.findField('EvnForensicSub_ExpertiseComeTime').setValue(new Date())
				BaseForm.findField('MedPersonal_eid').setValue(wnd.MedPersonal_eid);
			}
		},
		close: function(wnd) {
			if (typeof wnd.callback == 'function') {
				wnd.callback();
			}
		}
	}
})