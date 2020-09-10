/**
 * Форма запроса дополнительных документов
 */

Ext.define('common.BSME.ForenPers.ExpertWP.tools.swCreateDopDocQueryWindow', {
	extend: 'Ext.window.Window',
	autoShow: true,
	modal: true,
	width: '50%',	
	closable: true,	
//    header: false,
	title: 'Запрос дополнительных документов',
	id: 'swCreateDopDocQueryWindow',
	border: false,
	layout: {
		align: 'stretch',
		type: 'vbox'
	},
	callback: Ext.emptyFn,
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
				labelWidth: 250,
				allowBlank: false
			},
			items: [
				{
					xtype: 'hidden',
					name: 'EvnForensicSubDopDocQuery_id'
				}, {
					xtype: 'hidden',
					name: 'EvnForensicSub_id'
				},{
				// Попросили убрать (@link http://redmine.swan.perm.ru/issues/57082)
//					fieldLabel: '№',
//					xtype: 'textfield',
//					name: 'EvnForensicSubDopDocQuery_Num'
//				},{
//					fieldLabel: 'Дата',
//					xtype: 'swdatefield',
//					name: 'EvnForensicSubDopDocQuery_Date'
//				},{
					fieldLabel: 'Кому',
					xtype: 'textfield',
					name: 'EvnForensicSubDopDocQuery_Iniciator'
				},{	
					fieldLabel: 'Должность, место работы',
					xtype: 'textfield',
					name: 'EvnForensicSubDopDocQuery_IniciatorJob'
				},{
					fieldLabel: 'Подэкспертный',
					xtype: 'textfield',
					name: 'EvnForensicSubDopDocQuery_Person'
				},{
					fieldLabel: 'Что предоставить',
					xtype: 'textfield',
					name: 'EvnForensicSubDopDocQuery_Subject'
				}
			]
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
						EvnForensicSubDopDocQuery_id: BaseForm.findField('EvnForensicSubDopDocQuery_id').getValue()
						
					};

					var loadMask =  new Ext.LoadMask(me, {msg:"Пожалуйста, подождите, идёт сохранение формы..."});
					//loadMask.show();
					BaseForm.submit({
						params : params,
						url: '/?c=BSME&m=saveEvnForensicSubDopDocQuery',
						success: function(form, action) {
							loadMask.hide();

							if (Ext.isEmpty(action.result.EvnForensicSubDopDocQuery_id)) {
								Ext.Msg.alert('Ошибка', 'Не получен идентификатор запроса.');
								return;
							}
							/*
							Ext.MessageBox.confirm('Сообщение', 'Запрос успешно сохранен. Вывести запрос дополнительных материалов на печать?', function(btn){
								if ( btn !== 'yes' ) {
									return;
								}
								var pattern = 'CME_EvnForensicSubDopMatQuery.rptdesign';
								printBirt({
									'Report_FileName': pattern,
									'Report_Params': '&paramEvnForensicSubDopMatQuery='+action.result.EvnForensicSubDopMatQuery_id,
									'Report_Format': 'pdf'
								});
							});
							*/
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
		
		BaseForm.setValues(arguments[0].formParams);
	
		me.loadDopDocQueryData();
		switch(me.action) {
			case 'add':
				if (Ext.isEmpty( BaseForm.findField('EvnForensicSub_id').getValue() )) {
					Ext.msg.alert('Ошибка','Не передан идентификатор заявки');
					me.destroy();
					return false;
				}
				me.enableEdit(true);
				break;
				
			case 'edit':
				if (Ext.isEmpty( BaseForm.findField('EvnForensicSubDopDocQuery_id').getValue() )) {
					Ext.msg.alert('Ошибка','Не передан идентификатор запроса');
					me.destroy();
					return false;
				}
				me.enableEdit(true);
				break;

			case 'view':				
				if (Ext.isEmpty( BaseForm.findField('EvnForensicSubDopDocQuery_id').getValue() )) {
					Ext.msg.alert('Ошибка','Не передан идентификатор запроса');
					me.destroy();
					return false;
				}
				me.enableEdit(false);
				break;
		}
	},

	//Загрузка в форму данных запроса
	loadDopDocQueryData: function() {

		var me = this,
			BaseForm = me.BaseForm.getForm(),
			EvnForensicSubDopDocQuery_id = BaseForm.findField('EvnForensicSubDopDocQuery_id').getValue(),
			EvnForensicSub_id = BaseForm.findField('EvnForensicSub_id').getValue();

		if (!EvnForensicSub_id && !EvnForensicSubDopDocQuery_id) {
			Ext.msg.alert('Ошибка','Не переданы необходимые идентификаторы');
			return false;
		}

		Ext.Ajax.request({
			url: '/?c=BSME&m=getEvnForensicSubDopDocQuery',
			params: {
				EvnForensicSubDopDocQuery_id: EvnForensicSubDopDocQuery_id,
				EvnForensicSub_id:EvnForensicSub_id
			},
			success: function(response) {
				var response_obj = Ext.JSON.decode(response.responseText);
				if (!response_obj) {
					Ext.Msg.alert('Ошибка', 'При получении данных запроса произошла ошибка');
				}
				BaseForm.setValues(response_obj);

			},
			failure: function(form, action) {

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
			}
		});

	}
});
