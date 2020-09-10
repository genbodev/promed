/**
 * swNephroCommissionWindow - Комиссия МЗ РБ.
 *
 * @package      Nephro
 * @access       public
 * @copyright    Copyright (c) Emsis.
 * @author       Magafurov Salavat
 * @version      07.2018
 */
sw.Promed.swNephroCommissionWindow = Ext.extend(sw.Promed.BaseForm, 
{
	action: null,
	winTitle: langs('Комиссия МЗ РБ'),
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
		var wnd = this;
		if ( wnd.formStatus == 'save' ) {
			return false;
		}

		wnd.formStatus = 'save';
		
		var form = wnd.FormPanel;
		var base_form = form.getForm();

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					wnd.formStatus = 'edit';
					form.getFirstInvalidEl().focus(false);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var loadMask = new Ext.LoadMask(wnd.getEl(), {msg: "Подождите, идет сохранение..."});
		loadMask.show();
		
		var params = {};
		var data = {};

		switch ( this.formMode ) {
			case 'local':
				data.BaseData = {
					'MorbusNephro_id': base_form.findField('MorbusNephro_id').getValue(),
					'NephroCommission_id': base_form.findField('NephroCommission_id').getValue(),
					'NephroCommission_date': base_form.findField('NephroCommission_date').getValue(),
					'NephroCommission_protocolNumber': base_form.findField('NephroCommission_protocolNumber').getValue()
				};
				wnd.callback(data);
				wnd.formStatus = 'edit';
				loadMask.hide();
				wnd.hide();
			break;
			case 'remote':
				base_form.submit({
					params: params,
					success: function(result_form, action) {
						wnd.formStatus = 'edit';
						loadMask.hide();
						if ( action.result ) {
							if ( action.result.NephroCommission_id > 0 ) {
								base_form.findField('NephroCommission_id').setValue(action.result.NephroCommission_id);

								data.BaseData = {
									'MorbusNephro_id': base_form.findField('MorbusNephro_id').getValue(),
									'NephroCommission_id': base_form.findField('NephroCommission_id').getValue(),
									'NephroCommission_date': base_form.findField('NephroCommission_date').getValue(),
									'NephroCommission_protocolNumber': base_form.findField('NephroCommission_protocolNumber').getValue()
								};
								wnd.callback(data);
								wnd.hide();
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
					},
					failure: function(result_form, action) {
						wnd.formStatus = 'edit';
						loadMask.hide();
						if ( action.result ) {
							if ( action.result.Error_Msg ) {
								sw.swMsg.alert(langs('Ошибка'), action.result.Error_Msg);
							}
							else {
								sw.swMsg.alert(langs('Ошибка'), langs('При сохранении произошли ошибки [Тип ошибки: 1]'));
							}
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
		sw.Promed.swNephroCommissionWindow.superclass.show.apply(this, arguments);
		
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
		this.NephroCommission_id = arguments[0].NephroCommission_id || null;
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
			if ( ( this.NephroCommission_id ) && ( this.NephroCommission_id > 0 ) )
				this.action = "edit";
			else
				this.action = "add";
		}
		
		base_form.setValues(arguments[0].formParams);
		
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
			case 'view':
				this.setTitle(this.winTitle +langs(': Просмотр'));
				this.setFieldsDisabled(true);
				break;
		}
		if (this.action != 'add' && this.formMode == 'remote') {
			Ext.Ajax.request({
				url:'/?c=MorbusNephro&m=doLoadEditFormNephroCommission',
				params:{
					NephroCommission_id: that.NephroCommission_id
				},
				success: function (response) {
					that.getLoadMask().hide();
					var result = Ext.util.JSON.decode(response.responseText);
					if (!result[0]) { return false; }
					base_form.setValues(result[0]);
					base_form.findField('NephroCommission_date').focus(true,200);
				},
				failure:function () {
					sw.swMsg.alert(langs('Ошибка'), langs('Не удалось получить данные с сервера'));
					that.getLoadMask().hide();
				}
			});				
		} else {
			this.getLoadMask().hide();
			base_form.findField('NephroCommission_date').focus(true,200);
		}
	},	
	initComponent: function() 
	{
		var wnd = this;
		this.FormPanel = new Ext.form.FormPanel(
		{	
			autoScroll: true,
			frame: true,
			region: 'north',
			bodyStyle: 'padding: 5px',
			autoHeight: false,
			labelAlign: 'right',
			labelWidth: 200,
			items: 
			[{
				name: 'NephroCommission_id',
				xtype: 'hidden'
			}, {
				name: 'MorbusNephro_id',
				xtype: 'hidden'
			}, {
				fieldLabel: langs('Дата проведения комиссии'),
				name: 'NephroCommission_date',
				allowBlank: false,
				xtype: 'swdatefield',
				plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
			}, {
				fieldLabel: langs('№ протокола'),
				name: 'NephroCommission_protocolNumber',
				allowBlank: false,
				width: 150,
				maxLength: 50,
				xtype: 'textfield'
			}],
			reader: new Ext.data.JsonReader(
			{
				success: Ext.emptyFn
			}, 
			[
				{name: 'MorbusNephro_id'},
				{name: 'NephroCommission_id'},
				{name: 'NephroCommission_date'},
				{name: 'NephroCommission_protocolNumber'}
			]),
			url: '/?c=MorbusNephro&m=nephroCommissionSave'
		});
		Ext.apply(this, 
		{
			buttons: 
			[{
				handler: function() {
					wnd.doSave();
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
					wnd.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],
			items: [this.FormPanel]
		});
		sw.Promed.swNephroCommissionWindow.superclass.initComponent.apply(this, arguments);
	}
});