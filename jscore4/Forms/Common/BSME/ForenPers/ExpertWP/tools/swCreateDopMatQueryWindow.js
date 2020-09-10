/**
 * Форма запроса дополнительных материалов
 */

Ext.define('common.BSME.ForenPers.ExpertWP.tools.swCreateDopMatQueryWindow', {
	extend: 'Ext.window.Window',
	autoShow: true,
	modal: true,
	width: '50%',	
	refId: 'forenperscreatedopmatquerywnd',
	closable: true,	
//    header: false,
	title: 'Запрос дополнительных материалов',
	id: 'swCreateForenPersDopMatQueryWindow',
	border: false,
	layout: {
		align: 'stretch',
		type: 'vbox'
	},
	setValues: function(){		
		var org_combo = this.down('form').getForm().findField('Org_id');							
		var t = org_combo.getValue();
		org_combo.getStore().load({
			params: {Org_id: t},
			callback: function(){
				org_combo.setValue(t);									
			}
		});
	},
	searchPerson: function(data) {
		Ext.create('sw.tools.subtools.swPersonWinSearch',
			{
				callback: data.callback
			}).show()
	},

	initComponent: function(){
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
			defaults: {
				labelAlign: 'left',
				labelWidth: 250
			},
			items: [
				{
					xtype: 'hidden',
					name: 'EvnForensicSubDopMatQuery_id'
				}, {
					xtype: 'hidden',
					name: 'EvnForensicSub_id'
				}, {
					xtype: 'textareafield',
					allowBlank: false,
					name: 'EvnForensicSubDopMatQuery_Name',
					fieldLabel: 'Запрашиваемый материал',
					anchor: '100%'
				}, {
					xtype: 'dOrgCombo',
					allowBlank: false,
					fieldLabel: 'Организация',
					hiddenName: 'Org_id',
					name: 'Org_id',
					store: new Ext.data.Store({
						storeId: 'Org_nid_store',
						fields: [
							{name: 'Org_id', type:'int'},
							{name: 'Org_Nick', type:'string'},
						],
						proxy: {
							limitParam: undefined,
							startParam: undefined,
							paramName: undefined,
							pageParam: undefined,
							type: 'ajax',
							url: '?c=Org&m=getOrgList',
							reader: {
							type: 'json',
							successProperty: 'success',
							root: 'data'
							},
							actionMethods: {
								create : 'POST',
								read   : 'POST',
								update : 'POST',
								destroy: 'POST'
							}
						}
					})
				}, {
					xtype: 'container',
					items:[{
						xtype: 'swdatefield',
						allowBlank: false,
						fieldLabel: 'Дата',
						format: 'd.m.Y',
						invalidText: 'Неправильный формат даты. Дата должна быть указана в формате ДД.ММ.ГГ',
						plugins: [new Ux.InputTextMask('99.99.9999')],
						name: 'EvnForensicSubDopMatQuery_ResearchDate',
						labelWidth: 250
					}]
				}, {
					xtype:'container',
					name:'PersonContainer',
					margin: '0 0 5 0',
					layout: {
						type: 'hbox',
						align: 'stretch'
					},
					items: [{
						flex: 1,
						allowBlank: false,
						labelAlign: 'left',
						labelWidth: 250,
						xtype: 'textfield',
						name: 'Person_FIO',
						readOnly: true,
						fieldLabel: 'На чье имя запрашивается материал',
						margin: '0 5 0 0',
						listeners: {
							focus: function(field,focusEvt,evtOpts){
								var Person_id =field.up('container').down('[name=Person_aid]'),
									Person_FIO = field;
								if (Person_FIO.disabled) return;
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
						name: 'Person_aid',
						value: 0
					},{
						margin: '0 0 0 5',
						xtype: 'button',
						iconCls: 'search16',
						name: 'searchbutton',
						tooltip: 'Поиск человека',
						handler: function(btn,evnt) {
							var Person_id =btn.up('container').down('[name=Person_aid]'),
								Person_FIO = btn.up('container').down('[name=Person_FIO]');
							if (Person_FIO.disabled) return;
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
				}
			]
		});
		this.BaseForm.on('storeloaded',function(){			
			me.setValues();
		});
		
		Ext.apply(me,{
			items: [
				this.BaseForm
			],
			buttons: [{
				xtype: 'button',
				id: this.id+'_SaveButton',
				text: 'Готово',
				handler: function(btn,evnt) {
					var BaseForm = me.BaseForm.getForm();
					if (!BaseForm.isValid()) {
						Ext.Msg.alert('Проверка данных формы', 'Не все поля формы заполнены.<br>Незаполненные поля выделены особо.');
						return;
					}

					var params = {
						EvnForensicSub_id: BaseForm.findField('EvnForensicSub_id').getValue(),
						Person_aid: BaseForm.findField('Person_aid').getValue()
					};

					var loadMask =  new Ext.LoadMask(me, {msg:"Пожалуйста, подождите, идёт сохранение формы..."});
					//loadMask.show();
					BaseForm.submit({
						params : params,
						url: '/?c=BSME&m=saveForenPersDopMatQuery',
						success: function(form, action) {
							loadMask.hide();

							if (Ext.isEmpty(action.result.EvnForensicSubDopMatQuery_id)) {
								Ext.Msg.alert('Ошибка', 'Не получен идентификатор запроса.');
								return;
							}

							Ext.MessageBox.confirm('Сообщение', 'Запрос успешно сохранен. Вывести запрос дополнительных материалов на печать?', function(btn){
								if ( btn !== 'yes' ) {
									return;
								}
								printBirt({
									'Report_FileName': 'CME_EvnForensicSubDopMatQuery.rptdesign',
									'Report_Params': '&paramEvnForensicSubDopMatQuery='+action.result.EvnForensicSubDopMatQuery_id,
									'Report_Format': 'pdf'
								});
							});
							me.callback();
							me.destroy();
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
				}
			}, {
				xtype: 'button',
				id: this.id+'_CloseButton',
				text: 'Закрыть',
				hidden: true,
				handler: function() {
					me.destroy();
				}
			}]
		});

		me.callParent(arguments);
	},

	enableEdit: function(enable){
		var me = this;
		var BaseForm = me.BaseForm.getForm();
		BaseForm.getFields().each(function(field){field.setDisabled(!enable)});
		Ext.getCmp(me.id+'_SaveButton').setVisible(enable);
		Ext.getCmp(me.id+'_CloseButton').setVisible(!enable);
	},

	show: function() {
		var me = this;
		me.action = 'view';
		me.enableEdit(false);

		me.callParent(arguments);
		if (!arguments[0] || !arguments[0].formParams) {
			return false;
		}

		if (arguments[0].action) {
			me.action = arguments[0].action;
		}

		if (arguments[0].callback) {
			me.callback = arguments[0].callback;
		} else {
			me.callback = Ext.emptyFn;
		}

		var BaseForm = me.BaseForm.getForm();
		
		switch (arguments[0].action) {
			case 'view':
				
				break;
			case 'add':
			
			default:
				break;
		}
		
		BaseForm.setValues(arguments[0].formParams);

		switch(me.action) {
			case 'add':
				var EvnForensicSub_id = BaseForm.findField('EvnForensicSub_id').getValue();
				if (Ext.isEmpty(EvnForensicSub_id)) {
					return false;
				}
				me.enableEdit(true);
				break;
			case 'view':				
				var EvnForensicSubDopMatQuery_id = BaseForm.findField('EvnForensicSubDopMatQuery_id').getValue();
				if (Ext.isEmpty(EvnForensicSubDopMatQuery_id)) {
					return false;
				}
				me.enableEdit(false);
				Ext.Ajax.request({
					url: '/?c=BSME&m=getForenPersDopMatQuery',
					params: {EvnForensicSubDopMatQuery_id: EvnForensicSubDopMatQuery_id},
					success: function(response) {
						var response_obj = Ext.JSON.decode(response.responseText);
						if (!response_obj.EvnForensicSubDopMatQuery_id) {
							Ext.Msg.alert('Ошибка', 'При получении данных запроса произошла ошибка');
						}
						BaseForm.setValues(response_obj);
						var org_combo = BaseForm.findField('Org_id');
						if (!Ext.isEmpty(response_obj.Org_id)) {
							org_combo.getStore().load({
								params: {Org_id: response_obj.Org_id},
								callback: function(){
									org_combo.setValue(response_obj.Org_id);									
								}
							});
						}
					},
					failure: function(form, action) {
						if (action.result.Error_Msg) {
							Ext.Msg.alert('Ошибка', action.result.Error_Msg);
						}
					}
				});
				break;
		}
	}
});
