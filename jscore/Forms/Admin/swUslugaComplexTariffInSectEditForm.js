/**
* swUslugaComplexTariffInSectEditForm - окно просмотра и редактирования участков
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

sw.Promed.swUslugaComplexTariffInSectEditForm = Ext.extend(sw.Promed.BaseForm, {
	title:lang['tarif_na_uslugu_v_otdelenii'],
	id: 'UslugaComplexTariffEditForm',
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
		sw.Promed.swUslugaComplexTariffInSectEditForm.superclass.show.apply(this, arguments);
		var loadMask = new Ext.LoadMask(Ext.get('UslugaComplexTariffEditForm'), { msg: "Подождите, идет загрузка..." });
		loadMask.show();
		if (arguments[0].callback)
			this.returnFunc = arguments[0].callback;
		if (arguments[0].owner)
			this.owner = arguments[0].owner;
		if (arguments[0].action)
			this.action = arguments[0].action;
		if (arguments[0].UslugaComplexTariff_id)
			this.UslugaComplexTariff_id = arguments[0].UslugaComplexTariff_id;
		else 
			this.UslugaComplexTariff_id = null;
		if (arguments[0].UslugaComplex_id)
			this.UslugaComplex_id = arguments[0].UslugaComplex_id;
		else 
			this.UslugaComplex_id = null;
		if (!arguments[0])
			{
			Ext.Msg.alert(lang['oshibka'], lang['otsutstvuyut_neobhodimyie_parametryi']);
			this.hide();
			return false;
			}
		var form = this;
		form.findById('UslugaComplexTariffEditFormPanel').getForm().reset();
		
		switch (this.action)
		{
			case 'add':
				form.setTitle(lang['tarif_na_uslugu_v_otdelenii_dobavlenie']);
				break;
			case 'edit':
				form.setTitle(lang['tarif_na_uslugu_v_otdelenii_redaktirovanie']);
				break;
			case 'view':
				form.setTitle(lang['tarif_na_uslugu_v_otdelenii_prosmotr']);
				break;
		}
		
		if (this.action=='view')
		{
			form.findById('ustUslugaComplexTariff_Tariff').disable();
			form.findById('ustUslugaComplexTariff_begDate').disable();
			form.findById('ustUslugaComplexTariff_endDate').disable();
			form.findById('ustUslugaComplex_id').disable();
			form.findById('ustUslugaComplexTariff_id').disable();
			form.buttons[0].disable();
		}
		else
		{
			form.findById('ustUslugaComplexTariff_Tariff').enable();
			form.findById('ustUslugaComplexTariff_begDate').enable();
			form.findById('ustUslugaComplexTariff_endDate').enable();
			form.findById('ustUslugaComplex_id').enable();
			form.findById('ustUslugaComplexTariff_id').enable();
			form.buttons[0].enable();
		}
		form.findById('ustUslugaComplex_id').setValue(this.UslugaComplex_id);
		if (this.action!='add')
		{
			
			form.findById('UslugaComplexTariffEditFormPanel').getForm().load(
			{
				url: '/?c=LpuStructure&m=GetUslugaComplexTariff',
				params:
				{
					object: 'UslugaComplexTariff',
					UslugaComplexTariff_id: this.UslugaComplexTariff_id,
					UslugaComplexTariff_Tariff: '',
					UslugaComplexTariff_begDate: '',
					UslugaComplexTariff_endDate: '',
					UslugaComplex_id: ''
				},
				success: function ()
				{
					if (form.action!='view')
						{
							//
						}
					form.findById('ustUslugaComplexTariff_Tariff').focus(true, 100);
					loadMask.hide();
				},
				failure: function ()
				{
					loadMask.hide();
					Ext.Msg.alert(lang['oshibka'], lang['oshibka_zaprosa_k_serveru_poprobuyte_povtorit_operatsiyu']);
				}
			});
			
		}
		else
		{
		//form.findById('ustLpuUnit_Name').setValue(this.LpuUnit_Name);
		form.findById('ustUslugaComplexTariff_Tariff').focus(true, 100);
		loadMask.hide();
		}
	},
	doSave: function() 
	{
		var form = this.findById('UslugaComplexTariffEditFormPanel');
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
		var begDate = form.findById('ustUslugaComplexTariff_begDate').getValue();
		var endDate = form.findById('ustUslugaComplexTariff_endDate').getValue();
		if ((begDate) && (endDate) && (begDate>endDate))
		{
			sw.swMsg.show(
			{
				buttons: Ext.Msg.OK,
				fn: function() 
				{
					form.findById('ustUslugaComplexTariff_begDate').focus(false)
				},
				icon: Ext.Msg.ERROR,
				msg: lang['data_okonchaniya_ne_mojet_byit_menshe_datyi_nachala'],
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		form.ownerCt.submit();
	},
	submit: function()
	{
		var form = this.findById('UslugaComplexTariffEditFormPanel');
		var loadMask = new Ext.LoadMask(Ext.get('UslugaComplexTariffEditForm'), { msg: "Подождите, идет сохранение..." });
		loadMask.show();
		form.getForm().submit(
			{
				failure: function(result_form, action)
				{
					if (action.result)
					{
						if (action.result.Error_Code)
						{
							Ext.Msg.alert(lang['oshibka_#']+action.result.Error_Code, action.result.Error_Message);
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
						if (action.result.UslugaComplexTariff_id)
						{
							form.ownerCt.hide();
							form.ownerCt.returnFunc(form.ownerCt.owner, action.result.UslugaComplexTariff_id);
						}
						else
							Ext.Msg.alert(lang['oshibka_#100004'], lang['pri_sohranenii_proizoshla_oshibka']);
					}
					else
						Ext.Msg.alert(lang['oshibka_#100005'], lang['pri_sohranenii_proizoshla_oshibka']);
				}
			});
	},
	initComponent: function()
	{
		this.MainPanel = new sw.Promed.FormPanel(
		{
			id:'UslugaComplexTariffEditFormPanel',
			height:this.height,
			width: this.width,
			frame: true,
			autoWidth: false,
			autoHeight: false,
			region: 'center',
			items:
			[
			{
				name: 'UslugaComplexTariff_id',
				tabIndex: -1,
				xtype: 'hidden',
				id: 'ustUslugaComplexTariff_id'
			},
			{
				name: 'UslugaComplex_id',
				tabIndex: -1,
				xtype: 'hidden',
				id: 'ustUslugaComplex_id'
			},
			{
				xtype: 'numberfield',
				tabIndex:1201,
				name: 'UslugaComplexTariff_Tariff',
				id:  'ustUslugaComplexTariff_Tariff',
				//maxValue: 999999,
				minValue: 0,
				autoCreate: {tag: "input", size:14, autocomplete: "off"},
				allowBlank: false,
				fieldLabel: lang['tarif']
			},
			{
				xtype: 'fieldset',
				autoHeight: true,
				title: lang['period_deystviya'],
				style: 'padding: 2; padding-left: 5px',
				items: [
				{
					fieldLabel : lang['nachalo'],
					tabIndex: 1202,
					allowBlank: false,
					xtype: 'swdatefield',
					format: 'd.m.Y',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
					name: 'UslugaComplexTariff_begDate',
					id: 'ustUslugaComplexTariff_begDate'
				},
				{
					fieldLabel : lang['okonchanie'],
					tabIndex: 1203,
					xtype: 'swdatefield',
					format: 'd.m.Y',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
					name: 'UslugaComplexTariff_endDate',
					id: 'ustUslugaComplexTariff_endDate'
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
				{ name: 'UslugaComplexTariff_id' },
				{ name: 'UslugaComplex_id' },
				{ name: 'UslugaComplexTariff_Tariff' },
				{ name: 'UslugaComplexTariff_begDate' },
				{ name: 'UslugaComplexTariff_endDate' }
			]
			),
			url: '/?c=LpuStructure&m=SaveUslugaComplexTariff'
		});
		
		Ext.apply(this,
		{
			xtype: 'panel',
			border: true,
			items: [this.MainPanel]
		});
		sw.Promed.swUslugaComplexTariffInSectEditForm.superclass.initComponent.apply(this, arguments);
	}
});