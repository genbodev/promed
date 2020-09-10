/**
 * swSportRegistryOutCause - окно выбора предмета наблюдения регистра БСК
 *
 */

sw.Promed.swSportRegistryOutCause = Ext.extend(sw.Promed.BaseForm, {
	title: langs('Выбор причины исключения из регистра'),
	id: 'sportRegistryOutCause',
	modal: true,
	height: 130,
	width: 330,
	closable: false,
	closeAction: 'hide',
	labelWidth: 110,
	bodyStyle: 'padding:10px;border:0px;',
	initComponent: function () {
		Ext.apply(this,
			{
				items: [
					{
						fieldLabel: langs('Причина'),
						allowBlank: false,
						mode: 'local',
						anchor: '100%',
						id: 'OutCauseCombo',
						store: new Ext.data.JsonStore({
							url: '/?c=SportRegister&m=getOutCauses',
							autoLoad: true,
							fields: [
								{name: 'PersonRegisterOutCause_id', type: 'int'},
								{name: 'PersonRegisterOutCause_Name', type: 'string'}
							],
							key: 'PersonRegisterOutCause_id',
						}),
						editable: false,
						triggerAction: 'all',
						hiddenName: 'PersonRegisterOutCause_id',
						displayField: 'PersonRegisterOutCause_Name',
						valueField: 'PersonRegisterOutCause_id',
						width: 300,
						xtype: 'combo',
					},
					{
						fieldLabel: langs('Дата исключения'),
						id: 'OutCause_date',
						format: 'd.m.Y',
						anchor: '100%',
						plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
						width: 90,
						xtype: 'datefield'
					}
				], buttons:
					[
						{
							text: langs('Выбрать'),
							id: 'getMorbusType_id',
							handler: function () {
								if (Ext.getCmp('OutCauseCombo').getValue() && Ext.getCmp('OutCause_date').getValue()) {
									if (
											new Date(swSportRegistryWindow.SportRegistrySearchFrame.getGrid().getSelectionModel().getSelected().data.SportRegisterUMO_UMODate).setHours(0,0,0,0) <=
											new Date(Ext.getCmp('OutCause_date').getValue()).setHours(0,0,0,0)
									) {
										swSportRegistryOutCause.deleteSportRegister(swSportRegistryOutCause.SportRegister_id, Ext.getCmp('OutCauseCombo').getValue(), Ext.getCmp('OutCause_date').getValue());
									} else {
										sw.swMsg.show({
											title: langs('Внимание'),
											msg: langs('Укажите дату исключения, не раньше даты последнего УМО спортсмена'),
											buttons: Ext.Msg.OK,
											icon: Ext.MessageBox.INFO
										});
									}
								} else {
									sw.swMsg.show({
										title: langs('Успешно'),
										msg: langs('Укажите причину исключения'),
										buttons: Ext.Msg.OK,
										icon: Ext.MessageBox.INFO
									});
								}
							},
							style: 'margin-right:150px'
						},
						{
							text: langs('Отмена'),
							id: 'cansel',
							handler: function () {
								swSportRegistryOutCause.hide();
							}
						}
					],
			}
		);
		sw.Promed.swSportRegistryOutCause.superclass.initComponent.apply(this, arguments);
	},
	deleteSportRegister: function (SportRegister_id, PersonRegisterOutCause_id, SportRegister_detDT) {
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: langs("Подождите, идет удаление...")});
		loadMask.show();
		Ext.Ajax.request({
			url: '/?c=SportRegister&m=deleteSportRegister',
			params: {
				SportRegister_id: SportRegister_id,
				PersonRegisterOutCause_id: PersonRegisterOutCause_id,
				SportRegister_detDT: SportRegister_detDT,
				pmUser_id: parseInt(getGlobalOptions().pmuser_id),
			},
			callback: function (options, success, response) {
				loadMask.hide();
				if (success) {
					var resp = Ext.util.JSON.decode(response.responseText);
					sw.swMsg.show({
						title: langs('Успешно'),
						msg: langs('Cпортсмен исключен из регистра по причине: ' + Ext.getCmp('OutCauseCombo').getRawValue()),
						buttons: Ext.Msg.OK,
						icon: Ext.MessageBox.INFO
					});
					if (personSportRegistryWindow) {
						personSportRegistryWindow.blockEdit = true;
						Ext.getCmp('restoreSportRegister').setVisible(true);
						Ext.getCmp('deleteSportRegister').setVisible(false);
					}
					swSportRegistryWindow.SportRegistrySearchFrame.getGrid().getStore().load();
					swSportRegistryOutCause.hide();
				} else {
					sw.swMsg.show({
						title: langs('Ошибка'),
						msg: langs('Произошла ошибка при исключении'),
						buttons: Ext.Msg.OK,
						icon: Ext.MessageBox.WARNING
					});
					return false;
				}
			}.createDelegate(this)
		});
	},
	show: function (params) {
		this.SportRegister_id = params.SportRegister_id;
		this.Person_id = params.Person_id;
		sw.Promed.swSportRegistryOutCause.superclass.show.apply(this, arguments);
	},
	listeners: {
		'hide': function () {
			this.onHide();
		}
	}
});