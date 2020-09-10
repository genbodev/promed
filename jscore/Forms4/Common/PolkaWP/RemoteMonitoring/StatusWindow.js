Ext6.define('common.PolkaWP.RemoteMonitoring.StatusWindow', {
	addCodeRefresh: Ext.emptyFn,
	closeToolText: 'Закрыть',

	alias: 'widget.swRemoteMonitoringStatusWindow',
	title: 'Смена статуса приглашения',
	extend: 'base.BaseForm',
	maximized: false,
	width: 400,
	//~ height: 300,
	autoHeight: true,
	layout: 'form',
	
	modal: true,

	findWindow: false,
	closable: true,
	cls: 'arm-window-new arm-window-new-without-padding status-remote-monitoring-window',
	renderTo: Ext6.getBody(),
	

	autoScroll: true,
	autoShow: false,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	
	doSave: function() {
		var me = this;
		Ext6.Ajax.request({
			url: '/?c=PersonDisp&m=ChangeLabelInviteStatus',
			params: {
				LabelInvite_id: me.LabelInvite_id,
				LabelInviteStatus_id: me.queryById('Status_id').getValue(),
				RefuseCause: me.queryById('RefuseCause').getValue(),
				MedStaffFact_id: getGlobalOptions().CurMedStaffFact_id
			},
			callback: function(options, success, response) {
				if (success) {
					rdata = Ext6.JSON.decode(response.responseText);
					if(rdata.Error_Code) Ext6.Msg.alert(langs('Ошибка'), rdata.Error_Message);
					else me.hide();
					me.arg.callback();
				}
			}
		});
	},	
	onSprLoad: function(args) {
		this.FormPanel.reset();
	},	
	show: function() {
		var me = this;
		me.callParent(arguments);
		
		if(!arguments[0] || !arguments[0]['LabelInvite_id'] || !arguments[0]['LabelInviteStatus_id']) {
			me.errorInParams();
			return false;
		}
		me.arg=arguments[0];
		me.LabelInvite_id = arguments[0]['LabelInvite_id'];
	},
	initComponent: function() {
		var me = this;
		
		me.FormPanel = new Ext6.form.FormPanel({
			border: false,
            bodyPadding: '25 25 25 30',
			region: 'center',
			defaults: {
				labelWidth: 90,
				width: 90+200
			},
			items: [{
				xtype: 'swLabelInviteStatusCombo',
				fieldLabel: 'Новый статус',
				itemId: 'Status_id',
				value: 2,
				listeners: {
					change: function(field) {
						me.queryById('RefuseCause').setDisabled( field.getValue()!=3 );
					}
				}
			}, {
				xtype: 'textfield',
				fieldLabel: 'Причина',
				itemId: 'RefuseCause',
				value: '',
				disabled: true
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
				text: langs('Сохранить'),
				itemId: 'saveButton',
				//~ margin: '0 19 0 0',
				handler: function() {
					me.doSave();
				}
			}]
		});

		this.callParent(arguments);
	}
});