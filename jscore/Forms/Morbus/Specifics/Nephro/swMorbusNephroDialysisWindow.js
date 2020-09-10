/**
 * swMorbusNephroDialysisWindow - Нуждается в диализе.
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @package      Nephro
 * @access       public
 * @copyright    Copyright (c) 2009-2014 Swan Ltd.
 * @author       Alexander Permyakov
 * @version      11.2014
 */
sw.Promed.swMorbusNephroDialysisWindow = Ext.extend(sw.Promed.BaseForm, 
{
	action: null,
	winTitle: langs('Нуждается в диализе'),
	autoHeight: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	formMode: 'remote',
	formStatus: 'edit',
	modal: true,
	doSave: function() 
	{
        var me = this;
		if ( me.formStatus == 'save' ) {
			return false;
		}

        me.formStatus = 'save';
		
		var form = me.FormPanel;
		var base_form = form.getForm();

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
                    me.formStatus = 'edit';
					form.getFirstInvalidEl().focus(false);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var loadMask = new Ext.LoadMask(me.getEl(), {msg: "Подождите, идет сохранение..."});
		loadMask.show();
		
		var params = {};
		var data = {};

		switch ( this.formMode ) {
			case 'local':
				data.BaseData = {
					'MorbusNephroDialysis_id': base_form.findField('MorbusNephroDialysis_id').getValue(),
					'Lpu_id': base_form.findField('Lpu_id').getValue(),
					'MorbusNephroDialysis_begDT': base_form.findField('MorbusNephroDialysis_begDT').getValue(),
                    'PersonRegisterOutCause_id': base_form.findField('PersonRegisterOutCause_id').getValue(),
					'PersonRegisterOutCause_Name': base_form.findField('PersonRegisterOutCause_id').getRawValue()
				};
                me.callback(data);
                me.formStatus = 'edit';
				loadMask.hide();
                me.hide();
			break;
			case 'remote':
				base_form.submit({
					failure: function(result_form, action) {
                        me.formStatus = 'edit';
						loadMask.hide();
						if ( action.result ) {
							if ( action.result.Error_Msg ) {
								sw.swMsg.alert(langs('Ошибка'), action.result.Error_Msg);
							}
							else {
								sw.swMsg.alert(langs('Ошибка'), langs('При сохранении произошли ошибки [Тип ошибки: 1]'));
							}
						}
					},
					params: params,
					success: function(result_form, action) {
                        me.formStatus = 'edit';
						loadMask.hide();
						if ( action.result ) {
							if ( action.result.MorbusNephroDialysis_id > 0 ) {
								base_form.findField('MorbusNephroDialysis_id').setValue(action.result.MorbusNephroDialysis_id);

								data.BaseData = {
									'MorbusNephroDialysis_id': base_form.findField('MorbusNephroDialysis_id').getValue(),
									'Lpu_id': base_form.findField('Lpu_id').getValue(),
									'MorbusNephroDialysis_begDT': base_form.findField('MorbusNephroDialysis_begDT').getValue(),
									'PersonRegisterOutCause_id': base_form.findField('PersonRegisterOutCause_id').getValue(),
									'PersonRegisterOutCause_Name': base_form.findField('PersonRegisterOutCause_id').getRawValue()
								};

								me.callback(data);
								me.hide();
							} else {
								if ( action.result.Error_Msg ) {
									sw.swMsg.alert(langs('Ошибка'), action.result.Error_Msg);
								}
								else {
									sw.swMsg.alert(langs('Ошибка'), langs('При сохранении произошли ошибки [Тип ошибки: 3]'));
								}
							}
						} else {
							sw.swMsg.alert(langs('Ошибка'), langs('При сохранении произошли ошибки [Тип ошибки: 2]'));
						}
					}
				});
			break;

			default:
				loadMask.hide();
			break;
			
		}
	},
	setFieldsDisabled: function(d) 
	{
		var form = this;
		this.FormPanel.items.each(function(f) 
		{
			if (f && (f.xtype!='hidden') && (f.xtype!='fieldset')  && (f.changeDisabled!==false))
			{
				f.setDisabled(d);
			}
		});
		form.buttons[0].setDisabled(d);
	},
	show: function() 
	{
		sw.Promed.swMorbusNephroDialysisWindow.superclass.show.apply(this, arguments);
		
		var that = this;
		if (!arguments[0] || !arguments[0].formParams) {
			sw.swMsg.show(
			{
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.ERROR,
				msg: langs('Ошибка открытия формы.<br/>Не указаны нужные входные параметры.'),
				title: langs('Ошибка'),
				fn: function() {
                    that.hide();
				}
			});
		}
		this.focus();
		
		this.center();

		var base_form = this.FormPanel.getForm();
		base_form.reset();

		this.formMode = 'remote';
		this.formStatus = 'edit';
        this.action = arguments[0].action || null;
        this.MorbusNephroDialysis_id = arguments[0].MorbusNephroDialysis_id || null;
        this.owner = arguments[0].owner || null;
        this.callback = arguments[0].callback || Ext.emptyFn;
        this.onHide = arguments[0].onHide || Ext.emptyFn;
		if ( arguments[0].formMode
            && typeof arguments[0].formMode == 'string'
            && arguments[0].formMode.inlist([ 'local', 'remote' ])
        ) {
			this.formMode = arguments[0].formMode;
		}
		if (!this.action) {
            if ( ( this.MorbusNephroDialysis_id ) && ( this.MorbusNephroDialysis_id > 0 ) )
                this.action = "edit";
            else
                this.action = "add";
		}
		
		base_form.setValues(arguments[0].formParams);
		base_form.findField('Lpu_id').showContainer();
		base_form.findField('MorbusNephroDialysis_begDT').showContainer();

		this.getLoadMask().show();
		switch (this.action) 
		{
			case 'add':
				this.setTitle(this.winTitle +langs(': Добавление'));
				this.setFieldsDisabled(false);
				break;
			case 'edit':
				this.setTitle(this.winTitle +langs(': Редактирование'));
				this.setFieldsDisabled(false);
				break;
			case 'editout':
				base_form.findField('Lpu_id').hideContainer();
				base_form.findField('MorbusNephroDialysis_begDT').hideContainer();
				this.setTitle('Исключение из списка нуждающихся в диализе');
				this.setFieldsDisabled(false);
				break;
			case 'view':
				this.setTitle(this.winTitle +langs(': Просмотр'));
				this.setFieldsDisabled(true);
				break;
		}
		if (this.action != 'add' && this.formMode == 'remote') {
			Ext.Ajax.request({
				failure:function () {
					sw.swMsg.alert(langs('Ошибка'), langs('Не удалось получить данные с сервера'));
					that.getLoadMask().hide();
				},
				params:{
					MorbusNephroDialysis_id: that.MorbusNephroDialysis_id
				},
				success: function (response) {
					that.getLoadMask().hide();
					var result = Ext.util.JSON.decode(response.responseText);
					if (!result[0]) { return false; }
					base_form.setValues(result[0]);
					base_form.findField('MorbusNephroDialysis_endDT').fireEvent('change', base_form.findField('MorbusNephroDialysis_endDT'), base_form.findField('MorbusNephroDialysis_endDT').getValue());
					base_form.findField('Lpu_id').focus(true,200);
				},
				url:'/?c=MorbusNephro&m=doLoadEditFormMorbusNephroDialysis'
			});				
		} else {
			this.getLoadMask().hide();
			base_form.findField('MorbusNephroDialysis_endDT').fireEvent('change', base_form.findField('MorbusNephroDialysis_endDT'), base_form.findField('MorbusNephroDialysis_endDT').getValue());
			base_form.findField('Lpu_id').focus(true,200);
		}

		this.syncShadow();
	},	
	initComponent: function() 
	{
		var me = this;
		this.FormPanel = new Ext.form.FormPanel(
		{	
			autoScroll: true,
			frame: true,
			region: 'north',
			bodyStyle: 'padding: 5px',
			autoHeight: false,
			labelAlign: 'right',
			labelWidth: 150,
			items: 
			[{
				name: 'MorbusNephroDialysis_id',
				xtype: 'hidden'
			}, {
				name: 'MorbusNephro_id',
				xtype: 'hidden'
			}, {
				fieldLabel: langs('МО'),
				hiddenName: 'Lpu_id',
				width: 350,
				xtype: 'swlpucombo'
			}, {
				fieldLabel: langs('Дата включения'),
				name: 'MorbusNephroDialysis_begDT',
				allowBlank: false,
				xtype: 'swdatefield',
				plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
			}, {
				fieldLabel: langs('Дата исключения'),
				name: 'MorbusNephroDialysis_endDT',
				listeners: {
					'change': function(field, newValue, oldValue) {
						var base_form = me.FormPanel.getForm();
						base_form.findField('PersonRegisterOutCause_id').setAllowBlank(Ext.isEmpty(newValue));
						base_form.findField('PersonRegisterOutCause_id').setDisabled(Ext.isEmpty(newValue));
						if (Ext.isEmpty(newValue)) {
							base_form.findField('PersonRegisterOutCause_id').clearValue();
						}
					}
				},
				allowBlank: true,
				xtype: 'swdatefield',
				plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
			}, {
				fieldLabel: langs('Причина исключения'),
				hiddenName: 'PersonRegisterOutCause_id',
				xtype: 'swcommonsprcombo',
				sortField: 'PersonRegisterOutCause_Code',
				comboSubject: 'PersonRegisterOutCause',
				width: 350
			}],
			reader: new Ext.data.JsonReader(
			{
				success: Ext.emptyFn
			}, 
			[
				{name: 'MorbusNephroDialysis_id'},
                {name: 'MorbusNephro_id'},
				{name: 'Lpu_id'},
				{name: 'MorbusNephroDialysis_begDT'},
				{name: 'MorbusNephroDialysis_endDT'},
				{name: 'PersonRegisterOutCause_id'}
			]),
			url: '/?c=MorbusNephro&m=doSaveMorbusNephroDialysis'
		});
		Ext.apply(this, 
		{
			buttons: 
			[{
				handler: function() {
                    me.doSave();
				},
				iconCls: 'save16',
				text: BTN_FRMSAVE
			}, 
			{
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() 
				{
                    me.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],
			items: [this.FormPanel]
		});
		sw.Promed.swMorbusNephroDialysisWindow.superclass.initComponent.apply(this, arguments);
	}
});