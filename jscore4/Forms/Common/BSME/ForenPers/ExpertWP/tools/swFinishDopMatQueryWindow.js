/**
 * Форма запроса дополнительных материалов
 */

Ext.define('common.BSME.ForenPers.ExpertWP.tools.swFinishDopMatQueryWindow', {
	extend: 'Ext.window.Window',
	autoShow: true,
	modal: true,
	width: '50%',
	closable: true,
//    header: false,
	title: 'Запрос дополнительных материалов',
	id: 'swFinishDopMatQueryWindow',
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
				labelWidth: 250
			},
			items: [
				{
					xtype: 'hidden',
					name: 'EvnForensicSubDopMatQuery_id'
				}, {
					xtype: 'swdatefield',
					allowBlank: false,
					fieldLabel: 'Дата получения',
					name: 'EvnForensicSubDopMatQuery_ResultDate',
					labelWidth: 250
				}, {
					xtype: 'swtimefield',
					allowBlank: false,
					fieldLabel: 'Время получения',
					name: 'EvnForensicSubDopMatQuery_ResultTime',
					labelWidth: 250
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
					var BaseForm = me.BaseForm.getForm();
					if (!BaseForm.isValid()) {
						Ext.Msg.alert('Проверка данных формы', 'Не все поля формы заполнены.<br>Незаполненные поля выделены особо.');
						return;
					}

					var params = {
						EvnForensicSubDopMatQuery_id: BaseForm.findField('EvnForensicSubDopMatQuery_id').getValue()
					};

					var loadMask =  new Ext.LoadMask(me, {msg:"Пожалуйста, подождите, идёт сохранение формы..."});
					//loadMask.show();
					BaseForm.submit({
						params : params,
						url: '/?c=BSME&m=saveEvnForenSubDopMatQueryResult',
						success: function(form, action) {
							loadMask.hide();

							if (Ext.isEmpty(action.result.EvnForensicSubDopMatQuery_id)) {
								Ext.Msg.alert('Ошибка', 'Не получен идентификатор запроса.');
								return;
							}
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
			}]
		});

		me.callParent(arguments);
	},

	show: function() {
		var me = this;

		me.callParent(arguments);
		if (!arguments[0] || !arguments[0].formParams || !arguments[0].formParams.EvnForensicSubDopMatQuery_id) {
			return false;
		}

		if (arguments[0].callback) {
			me.callback = arguments[0].callback;
		} else {
			me.callback = Ext.emptyFn;
		}

		var BaseForm = me.BaseForm.getForm();

		BaseForm.setValues(arguments[0].formParams);
		
		log(BaseForm.findField('EvnForensicSubDopMatQuery_id').getValue());
	}
});
