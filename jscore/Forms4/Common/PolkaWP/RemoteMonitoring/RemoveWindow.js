Ext6.define('common.PolkaWP.RemoteMonitoring.RemoveWindow', {
	addCodeRefresh: Ext.emptyFn,
	//~ addHelpButton: Ext.emptyFn,
	closeToolText: 'Закрыть',

	alias: 'widget.swRemoteMonitoringRemoveWindow',
	title: 'Исключение из программы "Дистанционный мониторинг"',
	extend: 'base.BaseForm',
	maximized: false,
	width: 600,
	height: 218,
	modal: true,

	findWindow: false,
	closable: true,
	cls: 'arm-window-new arm-window-new-without-padding',
	renderTo: Ext6.getBody(),
	layout: 'border',

	autoScroll: true,
	autoShow: false,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	updateEmk: function() {
		var me = this;
		var emks = Ext6.ComponentQuery.query('[refId=common]');
		emks.forEach(function(emk){
			if(emk.Person_id==me.Person_id) {
				var emkpanel = emk.queryById('ObserveChartPanel');
				if(emk.isVisible()) {
					if(!Ext6.isEmpty(emkpanel)) {
						emkpanel = emkpanel.ownerWin.down('[refId=ObserveChartPanel]');
						if(!Ext6.isEmpty(emkpanel)) emkpanel.reload();
					}
					emk.PersonInfoPanel.load({Person_id:me.Person_id});
				}
			}
		});
	},
	doRem: function() {
		var me = this,
			now = Date.now();
		var form = me.FormPanel,
			base_form = me.FormPanel.getForm()
		if (!base_form.isValid()) {
			Ext6.Msg.show({
				buttons: Ext6.Msg.OK,
				fn: function() {
					form.getFirstInvalidEl().focus(false);
				}.createDelegate(this),
				icon: Ext6.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		//проверка: дата исключения не меньше даты последнего замера
		
		Ext6.Ajax.request({
			params: {
				Label_id: me.Label_id,
				Chart_id: this.Chart_id,
				Person_id: me.Person_id,
				endDate: me.queryById('endDate').getValue(),
				DispOutType_id: me.queryById('DispOutType_id').getValue()
			},
			callback: function(options, success, response) {
				if (success) {
					var res = Ext6.JSON.decode(response.responseText);
					if(!Ext6.isEmpty(res.Error_Msg)) {
						Ext6.Msg.alert(langs('Ошибка'), res.Error_Msg);
					} else {
						me.hide();
						me.updateEmk();
						me.callback({deleted: true, endDate: Date.now(), 
							DispOutType_id: me.queryById('DispOutType_id').getValue(), 
							DispOutType_Name: me.queryById('DispOutType_id').getSelection().get('DispOutType_Name')
						});
					}
				}
			},
			url: '/?c=PersonDisp&m=removePersonFromMonitoring'
		});
	},

	show: function() {
		var me = this;
		me.callParent(arguments);
		me.FormPanel.reset();
		if(!arguments[0] && !arguments.PersonInfo) {
			me.errorInParams();
			return false;
		}
		me.callback = arguments[0].callback;
		me.queryById('personinfo').setValue(arguments[0].PersonInfo);
		me.queryById('endDate').setValue(Date.now());
		me.queryById('endDate').setMaxValue(Date.now());
		me.Person_id = arguments[0].Person_id;
		me.Chart_id = arguments[0].Chart_id;
		me.Label_id = arguments[0].Label_id;
		me.FormPanel.isValid();
	},
	initComponent: function() {
		var me = this;

		me.FormPanel = new Ext6.form.FormPanel({
			border: false,
            bodyPadding: '25 25 25 30',
			region: 'center',
			defaults: {
				labelWidth: 120,
				width: 120+404
			},
			items: [{
				xtype: 'textfield',
				fieldLabel: 'Пациент',
				itemId: 'personinfo'
			}, {
				xtype: 'swDispOutTypeCombo',
				fieldLabel: 'Причина',
				itemId: 'DispOutType_id',
				allowBlank: false
			}, {
				//~ xtype: 'swDateField',
				xtype: 'datefield',
				itemId: 'endDate',
				startDay: 1,
				format: 'd.m.Y',
				fieldLabel: 'Дата исключения',
				allowBlank: false,
				width: 120+140,
				invalidText: 'Неправильная дата',
				plugins: [ new Ext6.ux.InputTextMask('99.99.9999', true) ],
				formatText: null
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
				text: langs('Исключить'),
				margin: '0 19 0 0',
				handler: function() {
					me.doRem();
				}
			}]
		});

		this.callParent(arguments);
	}
});