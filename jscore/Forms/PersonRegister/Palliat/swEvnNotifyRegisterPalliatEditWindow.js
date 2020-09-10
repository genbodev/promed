/**
 * swEvnNotifyRegisterPalliatEditWindow - Извещение по паллиативной помощи
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @package      PersonRegister
 * @access       public
 * @copyright    Copyright (c) 2009-2018 Swan Ltd.
 * @author       Sabirov Kirill
 * @version      12.2018
 */
sw.Promed.swEvnNotifyRegisterPalliatEditWindow = Ext.extend(sw.Promed.BaseForm, {
	title: langs('Извещение о пациенте, нуждающемся в ПМП'),
	PersonRegisterType_SysNick: 'palliat',
	formMode: 'remote',
	width: 600,
	height: 230,
	//autoHeight: true,
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	draggable: true,
	formStatus: 'edit',
	layout: 'border',
	action:'add',
	modal: true,
	callback: Ext.emptyFn,
	onHide: Ext.emptyFn,

	doSave: function() {
		if ( this.formStatus == 'save' ) {
			return false;
		}
		var me = this;
		this.formStatus = 'save';
		var base_form = me.FormPanel.getForm();
		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					me.formStatus = 'edit';
					me.FormPanel.getFirstInvalidEl().focus(false);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		var params = {
			PersonRegisterType_SysNick: me.PersonRegisterType_SysNick
		};
		var diagField = base_form.findField('Diag_id'),
			moField = base_form.findField('Lpu_did'),
			mpField = base_form.findField('MedPersonal_id');
		if (diagField.disabled) {
			params.Diag_id = diagField.getValue();
		}
		if (moField.disabled) {
			params.Lpu_did = moField.getValue();
		}
		if (mpField.disabled) {
			params.MedPersonal_id = mpField.getValue();
		}
		me.getLoadMask().show(langs('Пожалуйста, подождите, идет создание направления...'));
		base_form.submit({
			params: params,
			failure: function(result_form, action)
			{
				me.formStatus = 'edit';
				me.getLoadMask().hide();
				if (action.result) {
					if (action.result.Error_Code) {
						Ext.Msg.alert(langs('Ошибка #')+action.result.Error_Code, action.result.Error_Message);
					}
				}
			},
			success: function(result_form, action)
			{
				showSysMsg(langs('Направление создано'));
				me.formStatus = 'edit';
				me.getLoadMask().hide();
				var data = {};
				if (action.result) {
					data = action.result;
				}
				me.callback(data);
				me.hide();
			}
		});
		return true;
	},

	setFieldsDisabled: function(d) {
		var base_form = this.FormPanel.getForm();
		base_form.items.each(function(f)
		{
			if (f && (f.xtype!='hidden') && (f.xtype!='fieldset')  && (f.changeDisabled!==false))
			{
				f.setDisabled(d);
			}
		});
		this.buttons[0].setDisabled(d);
	},

	show: function() {
		sw.Promed.swEvnNotifyRegisterPalliatEditWindow.superclass.show.apply(this, arguments);
		var me = this;
		if(arguments[0]&&arguments[0].action){
			this.action=arguments[0].action
		}else{
			this.action='add';
		}
		if (!arguments[0] || !arguments[0].formParams || ((this.action=='add'&&!arguments[0].formParams.Person_id)&&(String(this.action).inlinst(['edit','view'])&&!arguments[0].formParams.PalliatNotify_id))) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.ERROR,
				msg: langs('Ошибка открытия формы.<br/>Не указаны нужные входные параметры.'),
				title: langs('Ошибка'),
				fn: function() {
					me.hide();
				}
			});
			return false;
		}
		this.focus();
		this.center();
		this.formStatus = 'edit';
		this.formParams = arguments[0].formParams;
		this.callback = arguments[0].callback || Ext.emptyFn;
		this.onHide = arguments[0].onHide || Ext.emptyFn;
		this._processingInputFormParams();
		this._resetForm();
		this._loadForm();
		this.buttons[0].show();
		return true;
	},

	_processingInputFormParams: function() {
		if (!this.formParams.MedPersonal_id) {
			this.formParams.MedPersonal_id = getGlobalOptions().medpersonal_id;
		}
		if (!this.formParams.Lpu_did) {
			this.formParams.Lpu_did = getGlobalOptions().lpu_id;
		}
	},

	_resetForm: function() {
		var base_form = this.FormPanel.getForm();
		this.formMode = 'remote';
		base_form.reset();
		base_form.setValues(this.formParams);
		base_form.findField('EvnNotifyBase_setDate').setMaxValue(new Date());
	},

	_loadForm: function() {
		var me = this,
			base_form = this.FormPanel.getForm(),
			diagField = base_form.findField('Diag_id'),
			moField = base_form.findField('Lpu_did'),
			mpField = base_form.findField('MedPersonal_id'),
			dateField = base_form.findField('EvnNotifyBase_setDate'),
			isRegisterOperator = sw.Promed.personRegister.isPalliatRegistryOperator();
		me.getLoadMask().show(langs('Пожалуйста, подождите, идет загрузка формы...'));
		switch(me.action){
			case 'add':
				dateField.setValue(new Date());
				me.setFieldsDisabled(false);
				me.InformationPanel.load({
					Person_id: base_form.findField('Person_id').getValue(),
					callback: function(dataList) {
						if (!dataList || !dataList[0] || !dataList[0].data || !dataList[0].data.PersonEvn_id) {
							me.getLoadMask().hide();
							me.hide();
							return false;
						}
						me.getLoadMask().hide();
						base_form.findField('PersonEvn_id').setValue(dataList[0].data.PersonEvn_id);
						base_form.findField('Server_id').setValue(dataList[0].data.Server_id);
						if (diagField.getValue()) {
							diagField.getStore().load({
								params: {
									where: ' where Diag_id = ' + diagField.getValue()
								},
								callback: function()
								{
									diagField.setValue(diagField.getValue());
									diagField.fireEvent('change', diagField, diagField.getValue());
								}
							});
						}
						moField.getStore().load({
							params: {
								Lpu_id: isRegisterOperator ? null : moField.getValue()
							},
							callback: function() {
								moField.setValue(moField.getValue());
								moField.fireEvent('change', moField, moField.getValue());
							}
						});
						return false;
					}
				});
				break;
			case 'edit':
			case 'view':
				if (me.action == 'edit') {
					me.setFieldsDisabled(false);
				} else {
					me.setFieldsDisabled(true);
				}
				base_form.load({
					failure: function() {
						me.getLoadMask().hide();
						sw.swMsg.alert(langs('Ошибка'), langs('Не удалось загрузить данные с сервера'));
					}.createDelegate(this),
					url:'/?c=EvnNotifyPalliat&m=loadEditForm',
					params:me.formParams,
					success: function(fm,rec,d) {
						var response_obj = Ext.util.JSON.decode(rec.response.responseText);
						me.InformationPanel.load({
							Person_id: base_form.findField('Person_id').getValue(),
							callback: function(dataList) {
								if (!dataList || !dataList[0] || !dataList[0].data || !dataList[0].data.PersonEvn_id) {
									me.getLoadMask().hide();
									me.hide();
									return false;
								}
								me.getLoadMask().hide();
								if (diagField.getValue()) {
									diagField.getStore().load({
										params: {
											where: ' where Diag_id = ' + diagField.getValue()
										},
										callback: function()
										{
											diagField.setValue(diagField.getValue());
											diagField.fireEvent('change', diagField, diagField.getValue());
										}
									});
								} else {
									diagField.setDisabled(false);
									diagField.additQueryFilter = "(Diag_Code not like 'E75.5')";
								}

								moField.getStore().load({
									params: {
										Lpu_id:  moField.getValue()
									},
									callback: function()
									{
										moField.setValue(moField.getValue());
										moField.fireEvent('change', moField, moField.getValue());
									}
								});
								return false;
							}
						});
					}
				});
				break;
		}

	},

	_createFormPanel: function() {
		var me = this;
		return new Ext.form.FormPanel({
			frame: true,
			layout: 'form',
			region: 'center',
			id: 'FormPanel',
			bodyStyle: 'padding: 5px',
			autoHeight: false,
			labelAlign: 'right',
			labelWidth: 200,
			autoScroll:true,
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
				{name: 'PalliatNotify_id'},
				{name: 'EvnNotifyBase_id'},
				{name: 'Server_id'},
				{name: 'PersonEvn_id'},
				{name: 'Person_id'},
				{name: 'EvnNotifyBase_setDate'},
				{name: 'Diag_id'},
				{name: 'MedPersonal_id'},
				{name: 'Lpu_did'}
			]),
			url:'/?c=EvnNotifyPalliat&m=save',
			items: [{
				region: 'north',
				layout: 'form',
				xtype: 'panel',
				items: [{
					xtype: 'hidden',
					name: 'PalliatNotify_id'
				}, {
					xtype: 'hidden',
					name: 'EvnNotifyBase_id'
				}, {
					xtype: 'hidden',
					name: 'Server_id'
				}, {
					xtype: 'hidden',
					name: 'PersonEvn_id'
				}, {
					xtype: 'hidden',
					name: 'Person_id'
				}, {
					allowBlank: false,
					xtype: 'swdatefield',
					name: 'EvnNotifyBase_setDate',
					fieldLabel: 'Дата заполнения извещения'
				}, {
					allowBlank: false,
					xtype: 'swdiagcombo',
					hiddenName: 'Diag_id',
					fieldLabel: langs('Диагноз'),
					//PersonRegisterType_SysNick: me.PersonRegisterType_SysNick,
					anchor: '100%'
				}, {
					allowBlank: false,
					xtype: 'swmedpersonalcombo',
					hiddenName: 'MedPersonal_id',
					listWidth: 750,
					fieldLabel: langs('Врач, заполнивший направление'),
					anchor: '100%'
				}, {
					xtype: 'swlpucombo',
					allowBlank: false,
					hiddenName: 'Lpu_did',// Может не совпадать с МО пользователя
					fieldLabel: langs('МО заполнения направления'),
					anchor: '100%',
					listeners: {
						'change': function(combo, newVal) {
							var base_form = me.FormPanel.getForm(),
								mpField = base_form.findField('MedPersonal_id'),
								isRegisterOperator = sw.Promed.personRegister.isPalliatRegistryOperator();
							mpField.getStore().baseParams = {
								Lpu_id: newVal
							};
							mpField.getStore().load({
								params: {
									MedPersonal_id: isRegisterOperator ? null : mpField.getValue()
								},
								callback: function()
								{
									if (mpField.getStore().getById(mpField.getValue())) {
										mpField.setValue(mpField.getValue());
									} else {
										mpField.setValue(null);
									}
								}
							});
						}
					}
				}]
			}]
		});
	},

	_cfgButtons: function() {
		var me = this;
		return [{
			handler: function() {
				me.doSave();
			}.createDelegate(this),
			iconCls: 'save16',
			text: BTN_FRMSAVE
		}, {
			text: '-'
		}, HelpButton(this), {
			handler: function()
			{
				me.hide();
			},
			iconCls: 'cancel16',
			text: BTN_FRMCANCEL
		}];
	},

	initComponent: function() {
		this.InformationPanel = new sw.Promed.PersonInformationPanelShort({
			region: 'north'
		});
		this.FormPanel = this._createFormPanel();
		Ext.apply(this, {
			buttons: this._cfgButtons(),
			items: [this.InformationPanel, this.FormPanel]
		});
		sw.Promed.swEvnNotifyRegisterPalliatEditWindow.superclass.initComponent.apply(this, arguments);
	}
});