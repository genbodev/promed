Ext6.define('common.PolkaWP.RemoteMonitoring.MessageWindow', {
	addCodeRefresh: Ext.emptyFn,
	//~ addHelpButton: Ext.emptyFn,
	closeToolText: 'Закрыть',

	alias: 'widget.swRemoteMonitoringMessageWindow',
	title: 'Отправить сообщение',
	extend: 'base.BaseForm',
	maximized: false,
	width: 600,
	height: 315,
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

	doSend: function() {// "Отправить"
		var me = this;
		me.SendMask.show();
		Ext6.Ajax.request({
			url: '/?c=PersonDisp&m=sendLabelMessage',
			params: {
				Person_id: me.arg.Person_id,
				Chart_id: me.arg.Chart_id,
				MessageText: me.queryById('msg').getValue(),
				FeedbackMethod_id: me.arg.FeedbackMethod_id,
				email: me.arg.email,
				phone: me.arg.phone
			},
			callback: function(options, success, response) {
				me.SendMask.hide();
				if (success) {
					res = Ext6.JSON.decode(response.responseText);
					if(res.Error_Msg!='') Ext6.Msg.alert(langs('Ошибка'), res.Error_Msg);
					else {
						me.hide();
						Ext6.Msg.alert(langs('Сообщение'), langs('Сообщение отправлено'));
					}
					me.arg.callback();
				} else Ext6.Msg.alert(langs('Ошибка'), langs('Не удалось отправить сообщение'));
			}
		});
	},
	normReg: function(s) {
		if(Ext6.isEmpty(s) || s.length==0) return '';
		return s.slice(0,1).toUpperCase()+s.slice(1).toLowerCase();
	},
	getFio: function(rec) {
		return this.normReg(rec.Person_SurName)+' '+this.normReg(rec.Person_FirName)+' '+this.normReg(rec.Person_SecName);
	},
	checkEnableSendButton: function() {
		var me = this;
		me.queryById('sendButton').setDisabled( !me.FormPanel.isValid() );
	},
	show: function() {
		var me = this;
		me.callParent(arguments);
		
		if(!arguments[0]) {
			me.errorInParams();
			return false;
		}
		me.arg = arguments[0];		
		me.FormPanel.reset();
		me.queryById('Recipient').setValue(me.arg.PersonFio+', '+me.arg.BirthDay+' ('+me.arg.Age+')');
	},
	initComponent: function() {
		var me = this;
		me.SendMask = new Ext6.LoadMask(me, {msg: langs('Отправляется сообщение')});
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
				itemId: 'Recipient',
				readOnly: true
			}, {
				xtype: 'textareafield',
				fieldLabel: 'Текст сообщения',
				itemId: 'msg',
				height: 160,
				allowBlank: false,
				listeners: {
					change: function() {
						me.checkEnableSendButton();
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
				disabled: true,
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