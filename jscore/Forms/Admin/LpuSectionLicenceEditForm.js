/**
* swLpuSectionLicenceEditForm - окно просмотра и редактирования участков
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright © 2009 Swan Ltd.
* @author       Быдлокодер ©
* @version      07.07.2009
*/

sw.Promed.swLpuSectionLicenceEditForm = Ext.extend(sw.Promed.BaseForm, {
	title:lang['litsenziya_otdeleniya_lpu'],
	id: 'LpuSectionLicenceEditForm',
	layout: 'border',
	maximizable: false,
	shim: false,
	width: 500,
	height: 220,
	modal: true,
	buttons:
	[{
		text: BTN_FRMSAVE,
		id: 'lslOk',
		tabIndex: 1524,
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
		id: 'lslCancel',
		tabIndex: 1525,
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
		sw.Promed.swLpuSectionLicenceEditForm.superclass.show.apply(this, arguments);
		var loadMask = new Ext.LoadMask(Ext.get('LpuSectionLicenceEditForm'), { msg: "Подождите, идет загрузка..." });
		loadMask.show();
		if (arguments[0].callback)
			this.returnFunc = arguments[0].callback;
		if (arguments[0].owner)
			this.owner = arguments[0].owner;
		if (arguments[0].action)
			this.action = arguments[0].action;
		if (arguments[0].LpuSectionLicence_id)
			this.LpuSectionLicence_id = arguments[0].LpuSectionLicence_id;
		else 
			this.LpuSectionLicence_id = null;
		if (arguments[0].LpuSection_id)
			this.LpuSection_id = arguments[0].LpuSection_id;
		else 
			this.LpuSection_id = null;
		if (arguments[0].LpuSection_Name)
			this.LpuSection_Name = arguments[0].LpuSection_Name;
		else 
			this.LpuSection_Name = null;

		if (!arguments[0])
			{
			Ext.Msg.alert(lang['oshibka'], lang['otsutstvuyut_neobhodimyie_parametryi']);
			this.hide();
			return false;
			}
		var form = this;
		form.findById('LpuSectionLicenceEditFormPanel').getForm().reset();
		
		switch (this.action)
		{
			case 'add':
				form.setTitle(lang['litsenziya_otdeleniya_lpu_dobavlenie']);
				break;
			case 'edit':
				form.setTitle(lang['litsenziya_otdeleniya_lpu_redaktirovanie']);
				break;
			case 'view':
				form.setTitle(lang['litsenziya_otdeleniya_lpu_prosmotr']);
				break;
		}
		
		if (this.action=='view')
		{
			form.findById('lsLpuSectionLicence_Num').disable();
			form.findById('lslLpuSectionLicence_begDate').disable();
			form.findById('lslLpuSectionLicence_endDate').disable();
			form.findById('lslLpuSection_id').disable();
			form.findById('lslLpuSectionLicence_id').disable();
			form.buttons[0].disable();
		}
		else
		{
			form.findById('lsLpuSectionLicence_Num').enable();
			form.findById('lslLpuSectionLicence_begDate').enable();
			form.findById('lslLpuSectionLicence_endDate').enable();
			form.findById('lslLpuSection_id').enable();
			form.findById('lslLpuSectionLicence_id').enable();
			form.buttons[0].enable();
		}
		form.findById('lslLpuSection_id').setValue(this.LpuSection_id);
		form.findById('lslLpuSection_Name').setValue(this.LpuSection_Name);
		if (this.action!='add')
		{
			
			form.findById('LpuSectionLicenceEditFormPanel').getForm().load(
			{
				url: C_LPUSECTIONLICENSE_GET,
				params:
				{
					object: 'LpuSectionLicence',
					LpuSectionLicence_id: this.LpuSectionLicence_id,
					LpuSectionLicence_Num: '',
					LpuSectionLicence_begDate: '',
					LpuSectionLicence_endDate: '',
					LpuSection_id: ''
				},
				success: function ()
				{
					if (form.action!='view')
						{
							//
						}
					form.findById('lsLpuSectionLicence_Num').focus(true, 100);
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
		//form.findById('lslLpuUnit_Name').setValue(this.LpuUnit_Name);
		form.findById('lsLpuSectionLicence_Num').focus(true, 100);
		loadMask.hide();
		}
	},
	checkDataExits: function () 
	{
		// Проверка на ранее введенные данные 
		var form = this.findById('LpuSectionLicenceEditFormPanel');
		var loadMask = new Ext.LoadMask(Ext.get('LpuSectionLicenceEditForm'), { msg: "Подождите, идет проверка..." });
		loadMask.show();
		var params = 
		{
			LpuSectionLicence_id: form.findById('lslLpuSectionLicence_id').getValue(), 
			LpuSection_id: form.findById('lslLpuSection_id').getValue(), 
			LpuSectionLicence_begDate: Ext.util.Format.date(form.findById('lslLpuSectionLicence_begDate').getValue(),'d.m.Y')
		}
		Ext.Ajax.request(
		{
			url: C_LPUSECTIONLICENSE_CHECK,
			params: params,
			callback: function(options, success, response) 
			{
				loadMask.hide();
				if (success)
				{
					if ( response.responseText.length > 0 )
					{
						var result = Ext.util.JSON.decode(response.responseText);
						if (!result.success)
						{
							sw.swMsg.show(
							{
								buttons: Ext.Msg.OK,
								fn: function() 
								{
									form.findById('lslLpuSectionLicence_begDate').focus(false)
								},
								icon: Ext.Msg.ERROR,
								msg: result.ErrorMessage,
								title: ERR_INVFIELDS_TIT
							});
						}
						else 
						{
							form.ownerCt.submit();
						}
					}
				}
			}
		});
	},
	doSave: function() 
	{
		var form = this.findById('LpuSectionLicenceEditFormPanel');
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
		var begDate = form.findById('lslLpuSectionLicence_begDate').getValue();
		var endDate = form.findById('lslLpuSectionLicence_endDate').getValue();
		if ((begDate) && (endDate) && (begDate>endDate))
		{
			sw.swMsg.show(
			{
				buttons: Ext.Msg.OK,
				fn: function() 
				{
					form.findById('lslLpuSectionLicence_begDate').focus(false)
				},
				icon: Ext.Msg.ERROR,
				msg: lang['data_okonchaniya_ne_mojet_byit_menshe_datyi_nachala'],
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		form.ownerCt.checkDataExits();
	},
	submit: function()
	{
		var form = this.findById('LpuSectionLicenceEditFormPanel');
		var loadMask = new Ext.LoadMask(Ext.get('LpuSectionLicenceEditForm'), { msg: "Подождите, идет сохранение..." });
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
						if (action.result.LpuSectionLicence_id)
						{
							form.ownerCt.hide();
							form.ownerCt.returnFunc(form.ownerCt.owner, action.result.LpuSectionLicence_id);
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
			id:'LpuSectionLicenceEditFormPanel',
			height:this.height,
			width: this.width,
			frame: true,
			autoWidth: false,
			autoHeight: false,
			region: 'center',
			items:
			[
			{
				name: 'LpuSectionLicence_id',
				tabIndex: -1,
				xtype: 'hidden',
				id: 'lslLpuSectionLicence_id'
			},
			{
				name: 'LpuSection_id',
				tabIndex: -1,
				xtype: 'hidden',
				id: 'lslLpuSection_id'
			},
			{
				name: 'LpuSection_Name',
				disabled: true,
				fieldLabel: lang['otdelenie'],
				tabIndex: 1,
				xtype: 'descfield',
				id: 'lslLpuSection_Name'
			},
			{
				xtype: 'textfield',
				tabIndex: 1526,
				name: 'LpuSectionLicence_Num',
				id:  'lsLpuSectionLicence_Num',
				autoCreate: {tag: "input", size:20, maxLength: "20", autocomplete: "off"},
				allowBlank: false,
				fieldLabel: lang['nomer'],
				enableKeyEvents: true,
				listeners: 
				{
					'keydown': function (f,e)
					{
						if (!e.shiftKey && (e.getKey() == e.TAB))
						{
							e.stopEvent();
							e.browserEvent.returnValue = false;
							e.returnValue = false;
							if ( e.browserEvent.stopPropagation )
								e.browserEvent.stopPropagation();
							else
								e.browserEvent.cancelBubble = true;
							if ( e.browserEvent.preventDefault )
								e.browserEvent.preventDefault();
							else
								e.browserEvent.returnValue = false;
							if (Ext.isIE)
							{
								e.browserEvent.keyCode = 0;
								e.browserEvent.which = 0;
							}
							this.ownerCt.ownerCt.findById('lslLpuSectionLicence_begDate').focus(true);
							return false;
						}
					}
				}
			},
			{
				xtype: 'fieldset',
				autoHeight: true,
				title: lang['period_deystviya'],
				style: 'padding: 2; padding-left: 5px',
				items: [
				{
					fieldLabel : lang['nachalo'],
					tabIndex: 1521,
					allowBlank: false,
					xtype: 'swdatefield',
					format: 'd.m.Y',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
					name: 'LpuSectionLicence_begDate',
					id: 'lslLpuSectionLicence_begDate'
				},
				{
					fieldLabel : lang['okonchanie'],
					tabIndex: 1522,
					allowBlank: true,
					xtype: 'swdatefield',
					format: 'd.m.Y',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
					name: 'LpuSectionLicence_endDate',
					id: 'lslLpuSectionLicence_endDate'
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
				{ name: 'LpuSectionLicence_id' },
				{ name: 'LpuSection_id' },
				{ name: 'LpuSectionLicence_Num' },
				{ name: 'LpuSectionLicence_begDate' },
				{ name: 'LpuSectionLicence_endDate' }
			]
			),
			url: C_LPUSECTIONLICENSE_SAVE
		});
		
		Ext.apply(this,
		{
			xtype: 'panel',
			border: false,
			items: [this.MainPanel]
		});
		sw.Promed.swLpuSectionLicenceEditForm.superclass.initComponent.apply(this, arguments);
	}
});