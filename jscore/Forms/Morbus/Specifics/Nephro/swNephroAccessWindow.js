/**
 * swNephroAccessWindow - Комиссия МЗ РБ.
 *
 * @package      Nephro
 * @access       public
 * @copyright    Copyright (c) Emsis.
 * @author       Magafurov Salavat
 * @version      07.2018
 */
sw.Promed.swNephroAccessWindow = Ext.extend(sw.Promed.BaseForm, 
{
	action: null,
	winTitle: langs('Тип доступа'),
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
					'NephroAccess_id': base_form.findField('NephroAccess_id').getValue(),
					'NephroAccessType_id': base_form.findField('NephroAccessType_id').getValue(),
					'NephroAccess_setDate': base_form.findField('NephroAccess_setDate').getValue()
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
							if ( action.result.NephroAccess_id > 0 ) {
								base_form.findField('NephroAccess_id').setValue(action.result.NephroAccess_id);

								data.BaseData = {
									'MorbusNephro_id': base_form.findField('MorbusNephro_id').getValue(),
									'NephroAccess_id': base_form.findField('NephroAccess_id').getValue(),
									'NephroAccessType_id': base_form.findField('NephroAccessType_id').getValue(),
									'NephroAccess_setDate': base_form.findField('NephroAccess_setDate').getValue()
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
		sw.Promed.swNephroAccessWindow.superclass.show.apply(this, arguments);
		
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
		this.NephroAccess_id = arguments[0].NephroAccess_id || null;
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
			if ( ( this.NephroAccess_id ) && ( this.NephroAccess_id > 0 ) )
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
				url:'/?c=MorbusNephro&m=doLoadEditFormNephroAccess',
				params:{
					NephroAccess_id: that.NephroAccess_id
				},
				success: function (response) {
					that.getLoadMask().hide();
					var result = Ext.util.JSON.decode(response.responseText);
					if (!result[0]) { return false; }
					base_form.setValues(result[0]);
					base_form.findField('NephroAccessType_id').focus(true,200);
				},
				failure:function () {
					sw.swMsg.alert(langs('Ошибка'), langs('Не удалось получить данные с сервера'));
					that.getLoadMask().hide();
				}
			});				
		} else {
			this.getLoadMask().hide();
			base_form.findField('NephroAccessType_id').focus(true,200);
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
			[ {
				name: 'NephroAccess_id',
				xtype: 'hidden'
			}, {
				name: 'MorbusNephro_id',
				xtype: 'hidden'
			}, {
				fieldLabel: langs('Тип доступа'),
				name: 'NephroAccessType_id',
				comboSubject: 'NephroAccessType',
				allowBlank: false,
				xtype: 'swcommonsprcombo'
			}, {
				fieldLabel: langs('Дата '),
				name: 'NephroAccess_setDate',
				allowBlank: false,
				xtype: 'swdatefield'
			}],
			reader: new Ext.data.JsonReader(
			{
				success: Ext.emptyFn
			}, 
			[
				{name: 'MorbusNephro_id'},
				{name: 'NephroAccess_id'},
				{name: 'NephroAccessType_id'},
				{name: 'NephroAccess_setDate'}
			]),
			url: '/?c=MorbusNephro&m=NephroAccessSave'
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
		sw.Promed.swNephroAccessWindow.superclass.initComponent.apply(this, arguments);
	}
});