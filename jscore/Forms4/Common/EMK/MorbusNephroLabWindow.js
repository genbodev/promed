/**
* Окно редактирования Лабораторных исследований
* вызывается из контр.карт дисп.наблюдения (PersonDispEditWindow)
* 
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
* @package      Polka
* @access       public
* @copyright    Copyright (c) 2018 Swan Ltd.
* 
*/
Ext6.define('common.EMK.MorbusNephroLabWindow', {
	alias: 'widget.swMorbusNephroLabWindowExt6',
	height: 195,
	closeToolText: 'Закрыть',
	closable: true,
	closeAction: 'hide',
	width: 550,
	cls: 'arm-window-new emk-forms-window PersonDispPanel',
	extend: 'base.BaseForm',
	renderTo: Ext6.getBody(), //main_center_panel.body.dom,
	layout: 'border',
	constrain: true,
	addCodeRefresh: Ext6.emptyFn,
	modal: true,
	
	title: 'Лабораторное исследование',
	
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
			Ext6.Msg.show({
				buttons: Ext6.Msg.OK,
				fn: function() {
                    me.formStatus = 'edit';
					form.getFirstInvalidEl().focus(false);
				},
				icon: Ext6.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var loadMask = new Ext6.LoadMask(me, {msg: "Подождите, идет сохранение..."});
		loadMask.show();
		
		var params = {};
		var data = {};

		switch ( this.formMode ) {
			case 'local':
				data.BaseData = {
					'MorbusNephroLab_id': base_form.findField('MorbusNephroLab_id').getValue(),
					'MorbusNephroLab_Date': base_form.findField('MorbusNephroLab_Date').getValue(),
                    'Rate_id': base_form.findField('Rate_id').getValue(),
					'Rate_ValueStr': base_form.findField('Rate_ValueStr').getValue(),
                    'RateType_id': base_form.findField('RateType_id').getValue(),
					'RateType_Name': base_form.findField('RateType_id').getRawValue()
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
								Ext6.Msg.alert(langs('Ошибка'), action.result.Error_Msg);
							}
							else {
								Ext6.Msg.alert(langs('Ошибка'), langs('При сохранении произошли ошибки [Тип ошибки: 1]'));
							}
						}
					},
					params: params,
					success: function(result_form, action) {
                        me.formStatus = 'edit';
						loadMask.hide();
						if ( action.result ) {
							if ( action.result.MorbusNephroLab_id > 0 ) {
								base_form.findField('MorbusNephroLab_id').setValue(action.result.MorbusNephroLab_id);

								data.BaseData = {
									'MorbusNephroLab_id': base_form.findField('MorbusNephroLab_id').getValue(),
									'MorbusNephroLab_Date': base_form.findField('MorbusNephroLab_Date').getValue(),
                                    'Rate_id': base_form.findField('Rate_id').getValue(),
									'Rate_ValueStr': base_form.findField('Rate_ValueStr').getValue(),
									'RateType_id': base_form.findField('RateType_id').getValue(),
									'RateType_Name': base_form.findField('RateType_id').getRawValue()
								};
                                me.callback(data);
                                me.hide();
							} else {
								if ( action.result.Error_Msg ) {
									Ext6.Msg.alert(langs('Ошибка'), action.result.Error_Msg);
								}
								else {
									Ext6.Msg.alert(langs('Ошибка'), langs('При сохранении произошли ошибки [Тип ошибки: 3]'));
								}
							}
						} else {
							Ext6.Msg.alert(langs('Ошибка'), langs('При сохранении произошли ошибки [Тип ошибки: 2]'));
						}
					}
				});
			break;

			default:
				loadMask.hide();
			break;
			
		}
	},
	
	setFieldsDisabled: function(d) {
		var form = this;
		this.FormPanel.items.each(function(f) 
		{
			if (f && (f.xtype!='hidden') && (f.xtype!='fieldset')  && (f.changeDisabled!==false))
			{
				f.setDisabled(d);
			}
		});
		form.queryById('button_save').setDisabled(d);
	},
	
	show: function() {
		this.callParent(arguments);
		
		var that = this;
		
		that.taskButton.hide();
		if (!arguments[0] || !arguments[0].formParams) {
			Ext6.Msg.show(
			{
				buttons: Ext6.Msg.OK,
				icon: Ext6.Msg.ERROR,
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
        this.MorbusNephroLab_id = arguments[0].formParams.MorbusNephroLab_id || null;
        this.owner = arguments[0].owner || null;
        this.callback = arguments[0].callback || Ext6.emptyFn;
        
		if ( arguments[0].formMode
            && typeof arguments[0].formMode == 'string'
            && arguments[0].formMode.inlist([ 'local', 'remote' ])
        ) {
			this.formMode = arguments[0].formMode;
		}
		if (!this.action) {
            if ( ( this.MorbusNephroLab_id ) && ( this.MorbusNephroLab_id > 0 ) )
                this.action = "edit";
            else
                this.action = "add";
		}
		var args = arguments;
		base_form.findField('RateType_id').getStore().load({
			callback: function() {
					base_form.setValues(args[0].formParams);
				}
			});
		base_form.setValues(arguments[0].formParams);
		
		this.getLoadMask().show();
		switch (this.action) 
		{
			case 'add':
			case 'edit':
				this.setFieldsDisabled(false);
				break;
			case 'view':
				this.setFieldsDisabled(true);
				break;
		}
		if (this.action != 'add' && this.formMode == 'remote') {
			Ext6.Ajax.request({
				failure:function () {
					Ext6.Msg.alert(langs('Ошибка'), langs('Не удалось получить данные с сервера'));
					that.getLoadMask().hide();
				},
				params:{
					MorbusNephroLab_id: that.MorbusNephroLab_id
				},
				success: function (response) {
					that.getLoadMask().hide();
					var result = Ext6.util.JSON.decode(response.responseText);
					if (!result[0]) { return false; }
					base_form.setValues(result[0]);
					base_form.findField('MorbusNephroLab_Date').focus(true,200);
				},
				url:'/?c=MorbusNephro&m=doLoadEditFormMorbusNephroLab'
			});				
		} else {
			this.getLoadMask().hide();
			base_form.findField('MorbusNephroLab_Date').focus(true,200);
		}
	},
	initComponent: function() {
		var win = this;

		win.FormPanel = new Ext6.form.FormPanel({
			bodyPadding: '25 25 25 30',
			cls: 'dispcard',
			region: 'center',
			border: false,
			msgTarget: 'side',
			items:[{
				name: 'MorbusNephroLab_id',
				xtype: 'hidden'
			}, {
				name: 'MorbusNephro_id',
				xtype: 'hidden'
			}, {
                name: 'Rate_id',
                xtype: 'hidden'
            }, {
				fieldLabel: langs('Дата'),
				name: 'MorbusNephroLab_Date',
				allowBlank: false,
				xtype: 'datefield',
				plugins: [new Ext6.ux.InputTextMask('99.99.9999', false)],
				width: 436,
				labelWidth: 105
			}, {
				layout: 'column',
				border: false,
				items: [
					{
						fieldLabel: langs('Показатель'),
						name: 'RateType_id',
						isDinamic: 1,
						allowBlank: false,
						xtype: 'swNephroRateTypeCombo',
						width: 253,
						labelWidth: 105
					},
					{
						name: 'Rate_ValueStr',
						allowBlank: false,
						fieldLabel: langs('Значение'),
						width: 113+70,
						labelWidth: 113,
						labelAlign: 'right',
						xtype: 'numberfield',
						minValue: 0
					}
				]
			}
			],
			reader: Ext6.create('Ext6.data.reader.Json', {
				type: 'json',
				model: Ext6.create('Ext6.data.Model', {
					fields:[
						{name: 'MorbusNephroLab_id'},
						{name: 'MorbusNephro_id'},
						{name: 'Rate_id'},
						{name: 'MorbusNephroLab_Date'},
						{name: 'Rate_ValueStr'},
						{name: 'RateType_id'}
					]
				})
			}),
			url: '/?c=MorbusNephro&m=doSaveMorbusNephroLab'
		});

		Ext6.apply(win, {
			layout: 'border',
			items: [
				win.FormPanel
			],
			buttons: ['->',
			{
				xtype: 'SimpleButton',
				text: langs('ОТМЕНА'),
				itemId: 'button_cancel'
			},
			{
				xtype: 'SubmitButton',
				text: langs('ПРИМЕНИТЬ'),
				itemId: 'button_save'
			}
			]
		});

		this.callParent(arguments);
	}
});