/**
* swNewslatterAcceptEditForm - Согласие на получение рассылок
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright	Copyright (c) 2015 Swan Ltd.
* @author		Aleksandr Chebukin
* @version      16.12.2015
*/

sw.Promed.swNewslatterAcceptEditForm = Ext.extend(sw.Promed.BaseForm, {
	title: 'Согласие на получение рассылок',
	id: 'swNewslatterAcceptEditForm',
	layout: 'form',
	maximizable: false,
	shim: false,
	width: 550,
	autoHeight: true,
	minWidth: 550,
	minHeight: 420,
	modal: true,
	buttons:
	[{
		text: BTN_FRMSAVE,
		id: 'lbOk',
		iconCls: 'save16',
		handler: function() {
			this.ownerCt.doSave();
		}
	},{
		text:'-'
	},{
		text: BTN_FRMHELP,
		iconCls: 'help16',
		handler: function(button, event) {
			ShowHelp(this.ownerCt.title);
		}
	},{
		text: BTN_FRMCANCEL,
		id: 'lbCancel',
		iconCls: 'cancel16',
		handler: function() {
			this.ownerCt.hide();
		}
	}],
	returnFunc: function(owner) {},
	show: function() 
	{
		sw.Promed.swNewslatterAcceptEditForm.superclass.show.apply(this, arguments);
		var loadMask = new Ext.LoadMask(Ext.get('swNewslatterAcceptEditForm'), { msg: "Загрузка..." });
		loadMask.show();
		
		if (arguments[0].NewslatterAccept_id)
			this.NewslatterAccept_id = arguments[0].NewslatterAccept_id;
		
		if (arguments[0].callback)
			this.returnFunc = arguments[0].callback;
			
		if (arguments[0].owner)
			this.owner = arguments[0].owner;
			
		if (arguments[0].action)
			this.action = arguments[0].action;
			
		if (arguments[0].Person_id)
			this.Person_id = arguments[0].Person_id;
		
		var form = this;
		base_form = form.MainPanel.getForm();
		base_form.reset();
		
		if (this.action == 'add') {
			base_form.findField('Lpu_id').setValue(getGlobalOptions().lpu_id);
			base_form.findField('NewslatterAccept_begDate').setValue(getGlobalOptions().date);
			base_form.findField('Person_id').setValue(this.Person_id);
			loadMask.hide();
			
		} else {			
			base_form.load({
				params:{
					NewslatterAccept_id: form.NewslatterAccept_id
				},
				failure: function(f, o, a){
					loadMask.hide();
					sw.swMsg.show({
						buttons: Ext.Msg.OK,
						fn: function() {
							current_window.hide();
						},
						icon: Ext.Msg.ERROR,
						msg: 'Ошибка запроса к серверу. Попробуйте повторить операцию.',
						title: 'Ошибка'
					});
				},
				success: function(result, request) {
					loadMask.hide();
				},
				url: '/?c=NewslatterAccept&m=load'
			});
		}		
		
	},
	doSave: function() 
	{
		var form = this.findById('swNewslatterAcceptEditFormPanel');
		if (!form.getForm().isValid())
		{
			sw.swMsg.show(
			{
				buttons: Ext.Msg.OK,
				fn: function() 
				{
					form.getFirstInvalidEl().focus(false);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		form.ownerCt.submit();
	},
	submit: function() 
	{
		var form = this.findById('swNewslatterAcceptEditFormPanel');
		var loadMask = new Ext.LoadMask(Ext.get('swNewslatterAcceptEditForm'), { msg: "Подождите, идет сохранение..." });
		loadMask.show();
		var win = this;
		var params = form.getForm().getValues();		

		form.getForm().submit({
				params: {
					Lpu_id: form.getForm().findField('Lpu_id').getValue()
				},
				failure: function(result_form, action) {
					loadMask.hide();
					if (action.result) {
						if (action.result.Error_Code) {
							Ext.Msg.alert('Ошибка #'+action.result.Error_Code, action.result.Error_Message);
						} else {
							//Ext.Msg.alert('Ошибка #100003', 'При сохранении произошла ошибка!');
						}
					}
					loadMask.hide();
				}, 
				success: function(result_form, action) {
					loadMask.hide();
					if (action.result) {
						if (action.result.NewslatterAccept_id) {
							params.NewslatterAccept_id = action.result.NewslatterAccept_id;
							form.ownerCt.hide();
							form.ownerCt.returnFunc(form.ownerCt.owner, true, params);
						} else {
							Ext.Msg.alert('Ошибка #100004', 'При сохранении произошла ошибка!');
						}
					} else {
						Ext.Msg.alert('Ошибка #100005', 'При сохранении произошла ошибка!');
					}
				}
			});
	},
	initComponent: function() 
	{
		this.MainPanel = new sw.Promed.FormPanel({
			id:'swNewslatterAcceptEditFormPanel',
			frame: true,
			region: 'center',
			labelWidth: 150,
			items:
			[{
				name: 'NewslatterAccept_id',
				xtype: 'hidden'
			}, {
				anchor: '100%',
				name: 'Lpu_id',
				xtype: 'swlpucombo',
				disabled: true
			}, {
				name: 'Person_id',
				xtype: 'hidden'
			}, {
				fieldLabel: 'Дата согласия',
				xtype: 'swdatefield',
				format: 'd.m.Y',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				name: 'NewslatterAccept_begDate',
				allowBlank: false
			}, {
				fieldLabel : 'Дата отказа от рассылки',
				xtype: 'swdatefield',
				format: 'd.m.Y',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				name: 'NewslatterAccept_endDate'
            }, {
				boxLabel: 'СМС рассылка',
				name: 'NewslatterAccept_IsSMS',
				xtype: 'checkbox',
				labelSeparator: '',
				inputValue: '2',
				uncheckedValue: '1',
				listeners: {
					'check': function(checkbox, value) {
						var base_form = this.MainPanel.getForm();
						if (base_form.findField('NewslatterAccept_IsSMS').checked) {
							//base_form.findField('NewslatterAccept_Phone').enable();
							base_form.findField('NewslatterAccept_Phone').setAllowBlank(false);
						} else {
							//base_form.findField('NewslatterAccept_Phone').setValue('');
							//base_form.findField('NewslatterAccept_Phone').disable();
							base_form.findField('NewslatterAccept_Phone').setAllowBlank(true);
						}
					}.createDelegate(this)
				}
			}, {
				fieldLabel : 'Номер телефона',
				name: 'NewslatterAccept_Phone',
				xtype: 'textfield',
				plugins: [ new Ext.ux.InputTextMask('+79999999999', true) ],
				//disabled: true
			}, {
				boxLabel: 'E-mail рассылка',
				name: 'NewslatterAccept_IsEmail',
				xtype: 'checkbox',
				labelSeparator: '',
				inputValue: '2',
				uncheckedValue: '1',
				listeners: {
					'check': function(checkbox, value) {
						var base_form = this.MainPanel.getForm();
						if (base_form.findField('NewslatterAccept_IsEmail').checked) {
							//base_form.findField('NewslatterAccept_Email').enable();
							base_form.findField('NewslatterAccept_Email').setAllowBlank(false);
						} else {
							//base_form.findField('NewslatterAccept_Email').setValue('');
							//base_form.findField('NewslatterAccept_Email').disable();
							base_form.findField('NewslatterAccept_Email').setAllowBlank(true);
						}
					}.createDelegate(this)
				}
			}, {
				fieldLabel : 'E-mail',
				name: 'NewslatterAccept_Email',
				xtype: 'textfield',
				//disabled: true
			}],
			reader: new Ext.data.JsonReader(
			{
				success: function() {}
			},
			[
				{ name: 'NewslatterAccept_id' },
				{ name: 'Lpu_id' },
				{ name: 'Person_id' },
				{ name: 'NewslatterAccept_Phone' },
				{ name: 'NewslatterAccept_IsSMS' },
				{ name: 'NewslatterAccept_Email' },
				{ name: 'NewslatterAccept_IsEmail' },
				{ name: 'NewslatterAccept_begDate' },
				{ name: 'NewslatterAccept_endDate' }
			]
			),
			url: '/?c=NewslatterAccept&m=save'
		});
		
		Ext.apply(this, 
		{
			items: [this.MainPanel]
		});
		sw.Promed.swNewslatterAcceptEditForm.superclass.initComponent.apply(this, arguments);
	}
});