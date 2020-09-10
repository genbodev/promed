/**
 * Согласие на получение рассылок
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      
 * @access       public
 * @copyright    Copyright (c) 2018 Swan Ltd.
 *
 */
Ext6.define('common.EMK.NewslatterAcceptEditForm', {
	addCodeRefresh: Ext.emptyFn,
	//~ addHelpButton: Ext.emptyFn,
	closeToolText: 'Закрыть',
	
	alias: 'widget.swNewslatterAcceptEditForm',
	title: 'Согласие на получение рассылок',
	extend: 'base.BaseForm',
	maximized: false,
	width: 488,
	height: 359,
	modal: true,
	
	findWindow: false,
	closable: true,
	cls: 'arm-window-new emk-forms-window dispatchWindow arm-window-new-without-padding',
	renderTo: Ext6.getBody(), // main_center_panel.body.dom,
	layout: 'border',
	
	autoScroll: true,
	autoShow: false,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	
	doSave: function() {
		var form = this.MainPanel;
		if(form.getForm().findField('PersonAgree').getValue()) {
			if (!form.getForm().isValid())
			{
				
				Ext6.MessageBox.show({
					title: ERR_INVFIELDS_TIT,
					msg: ERR_INVFIELDS_MSG,
					buttons: Ext6.Msg.OK,
					icon: Ext6.Msg.WARNING,
					fn: function() 
					{
						form.getFirstInvalidEl().focus(false);
					}
				});
				return false;
			}
			this.doSubmit();
		} else {
			this.hide();
		}
	},
	
	doSubmit: function() {
		var win = this;
		var form = this.MainPanel;
		var loadMask = new Ext6.LoadMask(this.MainPanel, { msg: "Подождите, идет сохранение..." });
		loadMask.show();
		var win = this;
		var params = form.getForm().getValues();		
		
		form.getForm().submit({
				params: {
					Lpu_id: form.getForm().findField('Lpu_id').getValue()
				},
				failure: function(result_form, action) {
					loadMask.hide();
					if (action.result) {
						if (action.result.Error_Code) {
							Ext6.Msg.alert(langs('Ошибка #')+action.result.Error_Code, action.result.Error_Message);
						} else {
							//Ext6.Msg.alert('Ошибка #100003', 'При сохранении произошла ошибка!');
						}
					}
					loadMask.hide();
				}, 
							
				success: function(result_form, action) {
					loadMask.hide();
					if (action.result) {
						if (action.result.NewslatterAccept_id) {
							params.NewslatterAccept_id = action.result.NewslatterAccept_id;
							win.hide();
							win.returnFunc(form.ownerCt.owner, true, params);
						} else {
							Ext6.Msg.alert(langs('Ошибка #100004'), langs('При сохранении произошла ошибка'));
						}
					} else {
						Ext6.Msg.alert(langs('Ошибка #100005'), langs('При сохранении произошла ошибка'));
					}
				}
			});
	},

	show: function() {
		this.callParent(arguments);
		var win = this;
		
		win.taskButton.hide();
		var loadMask = new Ext6.LoadMask(win.MainPanel, { msg: langs('Загрузка...') });
		loadMask.show();
		
		if (arguments[0].NewslatterAccept_id)
			this.NewslatterAccept_id = arguments[0].NewslatterAccept_id;
		
		if (arguments[0].callback)
			this.returnFunc = arguments[0].callback;
			
		if (arguments[0].owner)
			this.owner = arguments[0].owner;
			
		if (arguments[0].action)
			this.action = arguments[0].action;
			
		if (arguments[0].Person_id)
			this.Person_id = arguments[0].Person_id;
		
		var form = this;
		base_form = form.MainPanel.getForm();
		base_form.reset();
		
		if (this.action == 'add') {
			base_form.findField('Lpu_id').setValue(getGlobalOptions().lpu_id);
			base_form.findField('NewslatterAccept_begDate').setValue(getGlobalOptions().date);
			base_form.findField('Person_id').setValue(this.Person_id);
			loadMask.hide();
			
		} else {
			base_form.load({
				params:{
					NewslatterAccept_id: form.NewslatterAccept_id
				},
				failure: function(f, o, a){
					loadMask.hide();
					
					Ext6.MessageBox.show({
							title: langs('Ошибка'),
							msg: langs('Ошибка запроса к серверу. Попробуйте повторить операцию.'),
							buttons: Ext6.Msg.OK,
							icon: Ext6.Msg.WARNING,
							fn: function() 
							{
								form.getFirstInvalidEl().focus(false);
							}
						});
				},
				success: function(result, request) {
					loadMask.hide();
				},
				url: '/?c=NewslatterAccept&m=load'
			});
		}
	},
	initComponent: function() {
		var win = this;
		
		this.MainPanel = new Ext6.form.Panel({
			region: 'center',
			border: false,
			bodyStyle: 'padding: 15px 15px 0px 15px;',
			userCls:'newslatter-accept-form DispatchForm',
			defaults: {
				labelWidth: 180
			},
			items:
			[{
				name: 'NewslatterAccept_id',
				xtype: 'hidden'
			}, {
				name: 'Lpu_id',
				xtype: 'hidden'
			}, {
				name: 'Person_id',
				xtype: 'hidden'
			}, {
				xtype: 'checkbox',
				padding: '0px 0px 0px 16px',
				name: 'PersonAgree',
				boxLabel: langs('Пациент согласен на рассылку'),
				checked: true
			}, {
				fieldLabel: langs('Дата согласия'),
				userCls: 'date-accept',
				labelWidth: 106,
				width: 106+110,
				padding: '0px 0px 23px 16px',
			/*	style:{
					paddingLeft: '16px'
				},*/
				xtype: 'datefield',
				format: 'd.m.Y',
				userCls: 'AcceptBegDate',
				plugins: [ new Ext6.ux.InputTextMask('99.99.9999', false) ],
				name: 'NewslatterAccept_begDate',
				allowBlank: false,
				formatText: null,
				invalidText: 'Неправильная дата',
				maxValue: new Date()
			}, {
				fieldLabel : langs('Дата отказа от рассылки'),
				style:{
					paddingLeft: '16px'
				},
				userCls: 'CancelBegDate',
				hidden: true,
				xtype: 'datefield',
				format: 'd.m.Y',
				plugins: [ new Ext6.ux.InputTextMask('99.99.9999', true) ],
				name: 'NewslatterAccept_endDate',
				formatText: null,
				invalidText: 'Неправильная дата'
			}, new Ext6.create('swPanel',{
				cls: 'emk_forms DataPatient',
				title: 'ДАННЫЕ ПАЦИЕНТА',
				bodyStyle: 'padding: 23px 14px 23px', // 'padding: 14px 13px 14px;',
				border: true,
				defaults: {
					padding: '0px 0px 5px 0px',
					style: 'height: 32px !important;'
				},
				items: [
					{
						layout: 'column',
						border: false,
						items: [
							{
								width: 100,
								boxLabel: langs('СМС'),
								name: 'NewslatterAccept_IsSMS',
								xtype: 'checkbox',
								inputValue: '1',
								uncheckedValue: '0',
								listeners: {
									'change': function(checkbox, newValue, oldValue, eOpts) {
										var base_form = win.MainPanel.getForm();
										if (base_form.findField('NewslatterAccept_IsSMS').checked) {
											//base_form.findField('NewslatterAccept_Phone').enable();
											base_form.findField('NewslatterAccept_Phone').setAllowBlank(false);
										} else {
											//base_form.findField('NewslatterAccept_Phone').setValue('');
											//base_form.findField('NewslatterAccept_Phone').disable();
											base_form.findField('NewslatterAccept_Phone').setAllowBlank(true);
										}
									}.createDelegate(win)
								}
							}, {
								fieldLabel : langs('Номер телефона'),
								name: 'NewslatterAccept_Phone',
								xtype: 'textfield',
								plugins: [ new Ext6.ux.InputTextMask('+7 (999) 999 99 99', true) ],
								labelWidth: 115,
								width: 160+115,
								//disabled: true
								listeners: {
									'focus': function(field, event, eOpts) {		
										setTimeout(function() {
											var pos=0;
											var s=field.getValue();
											if(s && s.length) {
												pos=s.indexOf('_');
												if(pos<0) pos=s.length;
											}
											document.getElementById(field.getInputId()).selectionStart = pos;
											document.getElementById(field.getInputId()).selectionEnd = pos;
										}, 10);
									},
									'blur': function(field, event, eOpts ) {
										if(!field.allowBlank) setTimeout(function() { field.setAllowBlank(false);}, 10);
									}
								}
							}
						]
					}, {
						layout: 'column',
						border: false,
						padding: '0px 0px 5px 0px',
						items: [
							{
								width: 100,
								boxLabel: langs('E-mail'),
								name: 'NewslatterAccept_IsEmail',
								xtype: 'checkbox',
								inputValue: '1',
								uncheckedValue: '0',
								listeners: {
									'change': function(checkbox, newValue, oldValue, eOpts) {
										var base_form = win.MainPanel.getForm();
										if (base_form.findField('NewslatterAccept_IsEmail').checked) {
											//base_form.findField('NewslatterAccept_Email').enable();
											base_form.findField('NewslatterAccept_Email').setAllowBlank(false);
										} else {
											//base_form.findField('NewslatterAccept_Email').setValue('');
											//base_form.findField('NewslatterAccept_Email').disable();
											base_form.findField('NewslatterAccept_Email').setAllowBlank(true);
										}
									}.createDelegate(win)
								}
							}, {
								fieldLabel : 'E-mail адрес',
								name: 'NewslatterAccept_Email',
								xtype: 'textfield',
								labelWidth: 115,
								width: 160+115,
								//disabled: true
							}
						]
					}
				]
			})],
			
			reader: Ext6.create('Ext6.data.reader.Json', {
				type: 'json',
				model: Ext6.create('Ext6.data.Model', {
					fields: [
						{ name: 'NewslatterAccept_id' },
						{ name: 'Lpu_id' },
						{ name: 'Person_id' },
						{ name: 'NewslatterAccept_Phone' },
						{ name: 'NewslatterAccept_IsSMS' },
						{ name: 'NewslatterAccept_Email' },
						{ name: 'NewslatterAccept_IsEmail' },
						{ name: 'NewslatterAccept_begDate' },
						{ name: 'NewslatterAccept_endDate' }
					]
				})
			}),
			url: '/?c=NewslatterAccept&m=save'
		});
		
		Ext6.apply(win, {
			items: [
				win.MainPanel
			],
			border: false,
			buttons:
			[ '->'
			, {
				userCls:'buttonCancel buttonPoupup',
				text: langs('Отмена'),
				margin: 0,
				handler: function() {
					win.hide();
				}
			}, {
				userCls:'buttonAccept buttonPoupup',
				text: langs('Применить'),
				margin: '0 19 0 0',
				handler: function() {
					win.doSave();
				}
			}]
		});

		this.callParent(arguments);
	}
});