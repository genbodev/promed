/*Аудит записи для Нарядов*/
Ext.define('sw.tools.swEmergencyTeamAuditRecord', {
	alias: 'widget.swEmergencyTeamAuditRecord',
	extend: 'Ext.window.Window',
	closable: true,
	draggable: true,
	closeAction: 'hide',
	maximizable: false,
	modal: true,
	plain: true,
	//border: false,
	resizable: false,
	title: 'Аудит записи',
	width: 700,
	height: 290,
	layout: 'form',
	returnFunc: Ext.emptyFn,
	initComponent: function () {
		var me = this;
		me.on('show', function () {
			if (arguments[0]) {
				if (arguments[0].callback)
				{
					this.returnFunc = arguments[0].callback;
				}
				if (arguments[0].key_id)
				{
					this.key_id = arguments[0].key_id
				}
				if (arguments[0].key_field)
				{
					this.key_field = arguments[0].key_field;
				}
			}
			var params = {
				deleted: 0,
				schema: '',
				key_id: this.key_id,
				key_field: this.key_field,
				registry_id: ''
			};
			var base_form = me.down('form').getForm();
			base_form.reset();

			var loadMask = new Ext.LoadMask(
					this,
					{msg: "Подождите, идет загрузка..."}
			);
			loadMask.show();
			
			Ext.Ajax.request({
				callback: function (opt, success, response) {
					if (success) {
						loadMask.hide();
						var response_obj = Ext.decode(response.responseText)[0];
						base_form.findField('InspmUser').setValue(response_obj.InspmUser);
						base_form.findField('InsDate').setValue(response_obj.InsDate);
						base_form.findField('UpdpmUser').setValue(response_obj.UpdpmUser);
						base_form.findField('UpdDate').setValue(response_obj.UpdDate);
					}
				},
				params: params,
				url: C_GET_AUDIT,
			});

		});

		this.EmergencyTeamAuditRecordFormPanel = Ext.create('sw.BaseForm', {
			xtype: 'BaseForm',
			refId: 'EmergencyTeamAuditRecord',
			height: 230,
			flex: 1,
			layout: 'form',
			//border: false,
			frame: true,
			style: 'padding: 10px',
			labelWidth: 100,
			items: [
				{
					xtype: 'fieldset',
					autoHeight: true,
					title: 'Добавление записи',
					style: 'padding: 5px; margin-bottom: 5px',
					items: [
						{
							fieldLabel: 'Пользователь',
							name: 'InspmUser',
							readOnly: true,
							xtype: 'textfield',
							width: 400
						}, {
							fieldLabel: 'Дата',
							name: 'InsDate',
							readOnly: true,
							xtype: 'textfield',
							width: 250
						}
					]
				}, {
					xtype: 'fieldset',
					autoHeight: true,
					title: 'Изменение записи',
					style: 'padding: 5px; margin-bottom: 5px',
					items: [
						{
							fieldLabel: 'Пользователь',
							name: 'UpdpmUser',
							readOnly: true,
							xtype: 'textfield',
							width: 400
						}, {
							fieldLabel: 'Дата',
							name: 'UpdDate',
							readOnly: true,
							xtype: 'textfield',
							width: 250
						}
					]
				}
			]
		});

		Ext.applyIf(me, {
			items: [
				me.EmergencyTeamAuditRecordFormPanel
			],
			dockedItems: [
				{
					xtype: 'container',
					dock: 'bottom',
					layout: 'fit',
					items: [
						{
							xtype: 'container',
							dock: 'bottom',
							refId: 'bottomButtons',
							margin: '5 4',
							layout: {
								align: 'top',
								pack: 'end',
								type: 'hbox'
							},
							items: [
								{
									xtype: 'container',
									layout: {
										type: 'hbox',
										align: 'middle'
									},
									items: [
										{
											xtype: 'button',
											refId: 'helpBtn',
											text: 'Помощь',
											iconCls: 'help16',
											handler: function ()
											{
												ShowHelp(me.title);
											}
										},
										{
											xtype: 'button',
											refId: 'cancelBtn',
											iconCls: 'cancel16',
											text: 'Закрыть',
											margin: '0 5',
											handler: function () {
												this.up('window').close()
											}
										}
									]
								}

							]
						}
					]
				}
			]
		});

		me.callParent(arguments);
	}
});