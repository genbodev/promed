/**
* swUslugaSectionTariffEditForm - окно просмотра и редактирования участков
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright © 2009 Swan Ltd.
* @author       Быдлокодер ©
* @version      01.07.2009
*/

sw.Promed.swUslugaSectionTariffEditForm = Ext.extend(sw.Promed.BaseForm, {
	title:'Тариф на услугу в отделении',
	id: 'UslugaSectionTariffEditForm',
	layout: 'border',
	maximizable: false,
	shim: false,
	width: 500,
	height: 180,
	modal: true,
	buttons:
	[{
		text: BTN_FRMSAVE,
		id: 'ustOk',
		tabIndex: 1204,
		iconCls: 'save16',
		handler: function()
		{
			this.ownerCt.doSave();
		}
	},
	{
		text:'-'
	}, 
	{
		text: BTN_FRMHELP,
		iconCls: 'help16',
		handler: function(button, event) {
			ShowHelp(this.ownerCt.title);
		}
	},
	{
		text: BTN_FRMCANCEL,
		id: 'ustCancel',
		tabIndex: 1205,
		iconCls: 'cancel16',
		handler: function()
		{
			this.ownerCt.hide();
			this.ownerCt.returnFunc(this.ownerCt.owner, -1);
		}
	}
	],
	listeners:
	{
		hide: function()
		{
			this.returnFunc(this.owner, -1);
		}
	},
	returnFunc: function(owner, kid) {},
	show: function()
	{
		sw.Promed.swUslugaSectionTariffEditForm.superclass.show.apply(this, arguments);
		var loadMask = new Ext.LoadMask(Ext.get('UslugaSectionTariffEditForm'), { msg: "Подождите, идет загрузка..." });
		loadMask.show();
		if (arguments[0].callback)
			this.returnFunc = arguments[0].callback;
		if (arguments[0].owner)
			this.owner = arguments[0].owner;
		if (arguments[0].action)
			this.action = arguments[0].action;
		if (arguments[0].UslugaSectionTariff_id)
			this.UslugaSectionTariff_id = arguments[0].UslugaSectionTariff_id;
		else 
			this.UslugaSectionTariff_id = null;
		if (arguments[0].UslugaSection_id)
			this.UslugaSection_id = arguments[0].UslugaSection_id;
		else 
			this.UslugaSection_id = null;
		if (!arguments[0])
			{
			Ext.Msg.alert('Ошибка', 'Отсутствуют необходимые параметры');
			this.hide();
			return false;
			}
		var form = this;
		form.findById('UslugaSectionTariffEditFormPanel').getForm().reset();
		
		switch (this.action)
		{
			case 'add':
				form.setTitle('Тариф на услугу в отделении: Добавление');
				break;
			case 'edit':
				form.setTitle('Тариф на услугу в отделении: Редактирование');
				break;
			case 'view':
				form.setTitle('Тариф на услугу в отделении: Просмотр');
				break;
		}
		
		if (this.action=='view')
		{
			form.findById('ustUslugaSectionTariff_Tariff').disable();
			form.findById('ustUslugaSectionTariff_begDate').disable();
			form.findById('ustUslugaSectionTariff_endDate').disable();
			form.findById('ustUslugaSection_id').disable();
			form.findById('ustUslugaSectionTariff_id').disable();
			form.buttons[0].disable();
		}
		else
		{
			form.findById('ustUslugaSectionTariff_Tariff').enable();
			form.findById('ustUslugaSectionTariff_begDate').enable();
			form.findById('ustUslugaSectionTariff_endDate').enable();
			form.findById('ustUslugaSection_id').enable();
			form.findById('ustUslugaSectionTariff_id').enable();
			form.buttons[0].enable();
		}
		form.findById('ustUslugaSection_id').setValue(this.UslugaSection_id);
		if (this.action!='add')
		{
			
			form.findById('UslugaSectionTariffEditFormPanel').getForm().load(
			{
				url: C_USLUGASECTIONTARIFF_GET,
				params:
				{
					object: 'UslugaSectionTariff',
					UslugaSectionTariff_id: this.UslugaSectionTariff_id,
					UslugaSectionTariff_Tariff: '',
					UslugaSectionTariff_begDate: '',
					UslugaSectionTariff_endDate: '',
					UslugaSection_id: ''
				},
				success: function ()
				{
					if (form.action!='view')
						{
							//
						}
					form.findById('ustUslugaSectionTariff_Tariff').focus(true, 100);
					loadMask.hide();
				},
				failure: function ()
				{
					loadMask.hide();
					Ext.Msg.alert('Ошибка', 'Ошибка запроса к серверу. Попробуйте повторить операцию.');
				}
			});
			
		}
		else
		{
		//form.findById('ustLpuUnit_Name').setValue(this.LpuUnit_Name);
		form.findById('ustUslugaSectionTariff_Tariff').focus(true, 100);
		loadMask.hide();
		}
	},
	doSave: function() 
	{
		var form = this.findById('UslugaSectionTariffEditFormPanel');
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
		var begDate = form.findById('ustUslugaSectionTariff_begDate').getValue();
		var endDate = form.findById('ustUslugaSectionTariff_endDate').getValue();
		if ((begDate) && (endDate) && (begDate>endDate))
		{
			sw.swMsg.show(
			{
				buttons: Ext.Msg.OK,
				fn: function() 
				{
					form.findById('ustUslugaSectionTariff_begDate').focus(false)
				},
				icon: Ext.Msg.ERROR,
				msg: 'Дата окончания не может быть меньше даты начала.',
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		form.ownerCt.submit();
	},
	submit: function()
	{
		var form = this.findById('UslugaSectionTariffEditFormPanel');
		var loadMask = new Ext.LoadMask(Ext.get('UslugaSectionTariffEditForm'), { msg: "Подождите, идет сохранение..." });
		loadMask.show();
		form.getForm().submit(
			{
				failure: function(result_form, action)
				{
					if (action.result)
					{
						if (action.result.Error_Code)
						{
							Ext.Msg.alert('Ошибка #'+action.result.Error_Code, action.result.Error_Message);
						}
						else
						{
							//Ext.Msg.alert('Ошибка #100003', 'При сохранении произошла ошибка!');
						}
					}
					loadMask.hide();
				},
				success: function(result_form, action)
				{
					loadMask.hide();
					if (action.result)
					{
						if (action.result.UslugaSectionTariff_id)
						{
							form.ownerCt.hide();
							form.ownerCt.returnFunc(form.ownerCt.owner, action.result.UslugaSectionTariff_id);
						}
						else
							Ext.Msg.alert('Ошибка #100004', 'При сохранении произошла ошибка!');
					}
					else
						Ext.Msg.alert('Ошибка #100005', 'При сохранении произошла ошибка!');
				}
			});
	},
	initComponent: function()
	{
		this.MainPanel = new sw.Promed.FormPanel(
		{
			id:'UslugaSectionTariffEditFormPanel',
			height:this.height,
			width: this.width,
			frame: true,
			autoWidth: false,
			autoHeight: false,
			region: 'center',
			items:
			[
			{
				name: 'UslugaSectionTariff_id',
				tabIndex: -1,
				xtype: 'hidden',
				id: 'ustUslugaSectionTariff_id'
			},
			{
				name: 'UslugaSection_id',
				tabIndex: -1,
				xtype: 'hidden',
				id: 'ustUslugaSection_id'
			},
			{
				xtype: 'numberfield',
				tabIndex:1201,
				name: 'UslugaSectionTariff_Tariff',
				id:  'ustUslugaSectionTariff_Tariff',
				//maxValue: 999999,
				minValue: 0,
				autoCreate: {tag: "input", size:14, autocomplete: "off"},
				allowBlank: false,
				fieldLabel: 'Тариф'
			},
			{
				xtype: 'fieldset',
				autoHeight: true,
				title: 'Период действия',
				style: 'padding: 2; padding-left: 5px',
				items: [
				{
					fieldLabel : 'Начало',
					tabIndex: 1202,
					allowBlank: false,
					xtype: 'swdatefield',
					format: 'd.m.Y',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
					name: 'UslugaSectionTariff_begDate',
					id: 'ustUslugaSectionTariff_begDate'
				},
				{
					fieldLabel : 'Окончание',
					tabIndex: 1203,
					xtype: 'swdatefield',
					format: 'd.m.Y',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
					name: 'UslugaSectionTariff_endDate',
					id: 'ustUslugaSectionTariff_endDate'
				}]
			}
			],
			reader: new Ext.data.JsonReader(
			{
				success: function()
				{
				//alert('success');
				}
			},
			[
				{ name: 'UslugaSectionTariff_id' },
				{ name: 'UslugaSection_id' },
				{ name: 'UslugaSectionTariff_Tariff' },
				{ name: 'UslugaSectionTariff_begDate' },
				{ name: 'UslugaSectionTariff_endDate' }
			]
			),
			url: C_USLUGASECTIONTARIFF_SAVE
		});
		
		Ext.apply(this,
		{
			xtype: 'panel',
			border: true,
			items: [this.MainPanel]
		});
		sw.Promed.swUslugaSectionTariffEditForm.superclass.initComponent.apply(this, arguments);
	}
});