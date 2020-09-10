/* 
 * Форма добавления заявки в АРМ Секретаря службы "Судебно-биологическое отделение с молекулярно-генетической лабораторией"
 */


Ext.define('common.BSME.ForenAreaDprt.SecretaryWP.tools.swCreateRequestWindow', {
	extend: 'Ext.window.Window',
    autoShow: true,
	modal: true,
	width: '50%',
	height: '80%',
	refId: 'forenareadprtcreaterequestwnd',
	closable: true,
//    header: false,
	title: 'Новая заявка',
	id: 'ForenAreaDprtCreateRequestWindow',
	border: false,
	layout: {
        align: 'stretch',
        type: 'vbox'
    },
	//Поиск человека по базе
	//Параметры: data - объект
	//	dete.callback - фунция, вызываемая после установки значения в person_id
	searchPerson: function(data) {
		Ext.create('sw.tools.subtools.swPersonWinSearch', 
		{
			callback: data.callback
		}).show()
	},

	/**
	 * Функция добавления строки ввода для человека с поиском удалением и добавлением
	 * Параметры: data - Объект:
	 *				data.commonName - Наименование типа поля, являющееся префиксом для имён контейнеров (напр. Person)
	 *				data.hasTypeFlag - флаг установки типа лица (Обвиняемый/свидетель)
	 *				
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
//			margin: '0 0 5 0',
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
				padding: '0 0 0 0',
				readOnly: true,
				fieldLabel: label+' #'+(1*cnt+1),
//				margin: '0 5 0 0',
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
						}});
					}
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
				value: '1',
				hidden: !data.hasTypeFlag,
				disabled: !data.hasTypeFlag,
				setValue: function(val) {
					this.value = val;
				},
				text: 'Обвиняемый',
				menu: new Ext.menu.Menu({
					items: [
						{value: 1, text: 'Обвиняемый', handler: function(){
							var splitbutton = this.up('splitbutton');
							splitbutton.setText(this.text);
							splitbutton.setValue(this.text);
						}},
						{value: 2, text: 'Свидетель', handler: function(){
							var splitbutton = this.up('splitbutton');
							splitbutton.setText(this.text);
							splitbutton.setValue(this.text);
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
				}
			}]
		});
	},
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
	
	
	addAttachmentContainer: function() {

		var containerAllSelectorArray = this.BaseForm.query('[name=AttachmentAllContainer]'),
			containerAll = (containerAllSelectorArray.length)?containerAllSelectorArray[0]:null,
			cnt = (containerAll)?containerAll.items.length:null,
			me = this;
				
		if (!containerAll) 
			return false;
		
		if (cnt) {
			var prevField = containerAll.items.getAt(cnt-1);
			if (prevField) {
				prevField.query('[name=comment]')[0].setReadOnly(true);
				prevField.query('[name=addbutton]')[0].setVisible(false);
				prevField.query('[name=deletebutton]')[0].setVisible(true);
			}
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
				labelWidth: 250,
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
					labelWidth: 250,
				},
				items: 
				[{
					allowBlank: true,
					name: 'AttachmentField',
					fieldLabel: 'Прикрепление #'+(1*cnt+1),
					buttonText: 'Выбрать...',
					xtype: 'fileuploadfield',
					listeners: {
						change: function( field, value, eOpts ) {
							if (value) {
								var btn = this.up('container').query('[name=addbutton]')[0];
								btn.setDisabled(false)
							}
						}
					},
				},{
					margin: '0 0 0 85',
					xtype: 'button',
					name: 'addbutton',
					disabled: true,
					iconCls: 'add16',
					tooltip: 'Добавить',
					handler: function(btn,evnt){
						me.addAttachmentContainer()
					}
				},{
					margin: '0 0 0 85',
					xtype: 'button',
					hidden: true,
					name: 'deletebutton',
					iconCls: 'delete16',
					tooltip: 'Удалить',
					handler: function(btn,evnt){
						me.deleteAttachmentContainer(btn)
					}
				}]
			},{
				xtype: 'textfield',
				fieldLabel: 'Комментарий',
				name: 'comment'
			}]
		});
	},
	deleteAttachmentContainer: function(btn) {
		var containerAllSelectorArray = this.BaseForm.query('[name=AttachmentAllContainer]'),
			containerAll = (containerAllSelectorArray.length)?containerAllSelectorArray[0]:null,
			cnt = (containerAll)?containerAll.items.length:null;
			
		if (!containerAll || !(containerAll.queryById(btn.id)))
			return false;
		
		
		containerAll.remove(btn.up('container[name=AttachmentContainer]'));
		
		var containerItemsArray = containerAll.query('[name=AttachmentContainer]');
		var textField,label;
		for (var i=0;i<containerItemsArray.length;i++) {
			textField = containerItemsArray[i].query('[name=AttachmentField]')[0];
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
			items: [
				{
					xtype: 'container',
					padding: '0 10 0 0',
					width: '100%',
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
						readOnly: true
					},{
						xtype: 'datefield',
						fieldLabel: 'Дата заявки',
						format: 'd.m.Y',
						invalidText: 'Неправильный формат даты. Дата должна быть указана в формате ДД.ММ.ГГ',
						plugins: [new Ux.InputTextMask('99.99.9999')],
					}]
				},{
					xtype: 'fieldset',
					collapsible: true,
					width: '60%',
					margin: '20 50 20 25',
					padding: '20 20 25 25',
					defaults: {
						labelAlign: 'left',
						labelWidth: 250,
					},
					layout: {
							padding: '0 0 0 0', // [top, right, bottom, left]
							align: 'stretch',
							type: 'vbox'
						},
					title: 'Исследование живых лиц и медицинских документов',
					items: [{
						xtype: 'datefield',
						fieldLabel: 'Дата постановления',
						format: 'd.m.Y',
						invalidText: 'Неправильный формат даты. Дата должна быть указана в формате ДД.ММ.ГГ',
						plugins: [new Ux.InputTextMask('99.99.9999')],
					},{
						xtype: 'combobox',
						fieldLabel: 'Тип экспертизы',
						name: 'ExpertiseType',
						valueField: 'ExpertiseType_id',
						displayField: 'ExpertiseType_Name',
						codeField: 'ExpertiseType_Code',
						value: 1,
						store: new Ext.data.Store({
							autoLoad: false,
							queryMode: 'local',
							fields: [
//								'ExpertiseType_id','ExpertiseType_Name','ExpertiseType_Code'
								{name: 'ExpertiseType_id', type:'int'},
								{name: 'ExpertiseType_Name', type:'string'},
								{name: 'ExpertiseType_Code', type:'int'}
							],
							data: [
								{'ExpertiseType_id':1,'ExpertiseType_Name':'Исследование лица по запросу','ExpertiseType_Code':1},
								{'ExpertiseType_id':2,'ExpertiseType_Name':'Исследование медицинских документов с осмотром лица по запросу','ExpertiseType_Code':2},
								{'ExpertiseType_id':3,'ExpertiseType_Name':'Исследование медицинских документов по запросу','ExpertiseType_Code':3},
								{'ExpertiseType_id':4,'ExpertiseType_Name':'Исследование лица по личному заявлению','ExpertiseType_Code':4},
							]
						}),
						listeners: {
							change: function(field,value,eOpts) {
								switch (value) {
									case 1:
										me.BaseForm.down('[name=AssignedPersContainer]').show();
										me.BaseForm.down('[name=Org_did]').show();
										me.BaseForm.down('[name=Materials]').hide();
										me.BaseForm.down('[name=Price]').hide();
										break;
									case 2: 
									case 3:	
										me.BaseForm.down('[name=AssignedPersContainer]').show();
										me.BaseForm.down('[name=Org_did]').show();
										me.BaseForm.down('[name=Materials]').show();
										me.BaseForm.down('[name=Price]').hide();
										break;
									case 4: 
										me.BaseForm.down('[name=AssignedPersContainer]').hide();
										me.BaseForm.down('[name=Org_did]').hide();
										me.BaseForm.down('[name=Materials]').show();
										me.BaseForm.down('[name=Price]').show();
										break;
									default: 
										break;

								}
							}
						}
					},{
						xtype:'container',
						name:'AssignedPersContainer',
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
							name: 'AssignedPerson_FIO',
							readOnly: true,
							fieldLabel: 'Назначившее лицо',
							margin: '0 5 0 0',
							listeners: {
								focus: function(field,focusEvt,evtOpts){
									var Person_id =field.up('container').down('[name=AssignedPerson_id]'),
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
								}
							}
						},{
							xtype: 'hidden',
							name: 'AssignedPerson_id',
							value: 0,
						},{
							margin: '0 0 0 5',
							xtype: 'button',
							iconCls: 'search16',
							name: 'searchbutton',
							tooltip: 'Поиск человека',
							handler: function(btn,evnt) {
								var Person_id =btn.up('container').down('[name=AssignedPerson_id]'),
									Person_FIO = btn.up('container').down('[name=AssignedPerson_FIO]');
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
						xtype: 'dOrgCombo',
						fieldLabel: 'Направившая организация',
						name: 'Org_did'
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
							fieldLabel: 'Подэкспертное лицо',
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
									}});
								}
							}
						},{
							xtype: 'hidden',
							name: 'Person_id',
							value: 0,
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
								}});
							}
						}]
					},{
						name: 'EvnForensicSub_Facts',
						xtype: 'textareafield',
						plugins: [new Ux.Translit(true)],
						minHeight: 10,
						fieldLabel: 'Краткие обстоятельства дела',
					},{
						xtype: 'textareafield',
						name: 'Materials',
						plugins: [new Ux.Translit(true)],
						minHeight: 10,
						fieldLabel: 'Переданные материалы',
					},{
						xtype: 'textfield',
						plugins: [new Ux.Translit(true)],
						fieldLabel: 'Цена',
						hidden: true,
						name: 'Price'
					},{
						xtype:'container',
						name: 'AttachmentAllContainer', 
						items: []
					}]
				},{
					xtype: 'fieldset',
					collapsible: true,
					width: '60%',
					margin: '20 50 20 25',
					padding: '20 20 25 25',
					defaults: {
						labelAlign: 'left',
						labelWidth: 250,
					},
					layout: {
							padding: '0 0 0 0', // [top, right, bottom, left]
							align: 'stretch',
							type: 'vbox'
						},
					title: 'Исследование трупов',
					items:[{
						xtype: 'datefield',
						fieldLabel: 'Дата поступления',
						format: 'd.m.Y',
						invalidText: 'Неправильный формат даты. Дата должна быть указана в формате ДД.ММ.ГГ',
						plugins: [new Ux.InputTextMask('99.99.9999')],
					},{
						xtype:'container',
						name:'PersCorpContainer',
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
							name: 'PersonCorp_FIO',
							readOnly: true,
							fieldLabel: 'Умершее лицо',
							margin: '0 5 0 0',
							listeners: {
								focus: function(field,focusEvt,evtOpts){
									var Person_id =field.up('container').down('[name=PersonCorp_id]'),
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
								}
							}
						},{
							xtype: 'hidden',
							name: 'PersonCorp_id',
							value: 0,
						},{
							margin: '0 0 0 5',
							xtype: 'button',
							iconCls: 'search16',
							name: 'searchbutton',
							tooltip: 'Поиск человека',
							handler: function(btn,evnt) {
								var Person_id =btn.up('container').down('[name=PersonCorp_id]'),
									Person_FIO = btn.up('container').down('[name=PersonCorp_FIO]');
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
						name:'PersTranspContainer',
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
							name: 'PersonTransp_FIO',
							readOnly: true,
							fieldLabel: 'Доставившее лицо',
							margin: '0 5 0 0',
							listeners: {
								focus: function(field,focusEvt,evtOpts){
									var Person_id =field.up('container').down('[name=PersonTransp_id]'),
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
								}
							}
						},{
							xtype: 'hidden',
							name: 'PersonTransp_id',
							value: 0,
						},{
							margin: '0 0 0 5',
							xtype: 'button',
							iconCls: 'search16',
							name: 'searchbutton',
							tooltip: 'Поиск человека',
							handler: function(btn,evnt) {
								var Person_id =btn.up('container').down('[name=PersonTransp_id]'),
									Person_FIO = btn.up('container').down('[name=PersonTransp_FIO]');
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
						name: 'EvidenceAllContainer', 
						items: []
					},{
						xtype:'container',
						name: 'ValueStuffAllContainer', 
						items: []
					}]
				},
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
//					var params = {},
//						PersonContainerNames = [
//							'Person',
//							'PersonBioSample'
//						],
//						simpleContainerNames = [
//							'Evidence',
//							'BioSample',
//							'BioSampleForMolGenRes',
//							'Sample'
//						],
//						i,k,selectorContainerAll,containerAll,containerArray,obj,value;
//					
//					for (i=0;i<PersonContainerNames.length;i++) {
//						selectorContainerAll = this.BaseForm.query('[name='+PersonContainerNames[i]+'AllContainer]');
//						containerAll = (selectorContainerAll.length)?selectorContainerAll[0]:null;
//						if (containerAll) {
//							containerArray = containerAll.query('[name='+PersonContainerNames[i]+'Container]');
//							params[PersonContainerNames[i]]= [];
//							for (k=0;k<containerArray.length;k++) {
//								value = containerArray[k].query('[name=Person_id]')[0].getValue();
//								if (value) {
//									params[PersonContainerNames[i]].push({
//										'Person_id': value
//									})
//								}
//							}
//						}
//					}
//					
//					for (i=0;i<simpleContainerNames.length;i++) {
//						selectorContainerAll = this.BaseForm.query('[name='+simpleContainerNames[i]+'AllContainer]');
//						containerAll = (selectorContainerAll.length)?selectorContainerAll[0]:null;
//						if (containerAll) {
//							containerArray = containerAll.query('[name='+simpleContainerNames[i]+'Container]');
//							params[simpleContainerNames[i]]= [];
//							for (k=0;k<containerArray.length;k++) {
//								obj = {};
//								value = containerArray[k].query('[name='+simpleContainerNames[i]+'_Name]')[0].getValue();
//								if (value) {
//									obj[simpleContainerNames[i]+'_Name'] =  containerArray[k].query('[name='+simpleContainerNames[i]+'_Name]')[0].getValue();
//									params[simpleContainerNames[i]].push(obj)
//								}
//							}
//						}
//					}
					
					console.log({params : params});
					
					
					
				}.bind(this)
			}]
		})
		
		me.addAttachmentContainer();
		
		me.addSimpleField({
			commonName: 'Evidence',
			textFieldLabel: 'Вещественное доказательство.'
		});
		me.addSimpleField({
			commonName: 'ValueStuff',
			textFieldLabel: 'Ценность/документ'
		});
		me.callParent(arguments);
	},
})