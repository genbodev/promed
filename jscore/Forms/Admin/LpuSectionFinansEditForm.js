/**
* swLpuSectionFinansEditForm - окно просмотра и редактирования участков
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
/*NO PARSE JSON*/
sw.Promed.swLpuSectionFinansEditForm = Ext.extend(sw.Promed.BaseForm, {
	title:lang['istochnik_finansirovaniya'],
	id: 'LpuSectionFinansEditForm',
	layout: 'form',
	maximizable: false,
	shim: false,
	width: 500,
	autoHeight: true,
	modal: true,
	codeRefresh: true,
	objectName: 'swLpuSectionFinansEditForm',
	objectSrc: '/jscore/Forms/Admin/LpuSectionFinansEditForm.js',
	buttons:
	[{
		text: BTN_FRMSAVE,
		id: 'lsfedOk',
		tabIndex: 1326,
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
		handler: function(button, event) 
		{
			ShowHelp(this.ownerCt.title);
		}
	},
	{
		text: BTN_FRMCANCEL,
		id: 'lsfedCancel',
		tabIndex: 1327,
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
	/*checkDataExits: function () 
	{
		// Проверка на ранее введенные данные 
		var form = this.findById('LpuSectionFinansEditFormPanel');
		var loadMask = new Ext.LoadMask(Ext.get('LpuSectionFinansEditForm'), { msg: "Подождите, идет проверка..." });
		loadMask.show();
		var params = 
		{
			LpuSectionFinans_id: form.findById('lsfedLpuSectionFinans_id').getValue(), 
			LpuSection_id: form.findById('lsfedLpuSection_id').getValue(), 
			LpuSectionFinans_begDate: Ext.util.Format.date(form.findById('lsfedLpuSectionFinans_begDate').getValue(),'d.m.Y')
		}
		Ext.Ajax.request(
		{
			url: C_LPUSECTIONFINANS_CHECK,
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
									form.findById('lsfedLpuSectionFinans_begDate').focus(false);
								},
								icon: Ext.Msg.ERROR,
								msg: result.Error_Msg,
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
	},*/
	doSave: function() 
	{
		var form = this.findById('LpuSectionFinansEditFormPanel');
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
		var begDate = form.findById('lsfedLpuSectionFinans_begDate').getValue();
		var endDate = form.findById('lsfedLpuSectionFinans_endDate').getValue();
		if ((begDate) && (endDate) && (begDate>endDate))
		{
			sw.swMsg.show(
			{
				buttons: Ext.Msg.OK,
				fn: function() 
				{
					form.findById('lsfedLpuSectionFinans_begDate').focus(false)
				},
				icon: Ext.Msg.ERROR,
				msg: lang['data_okonchaniya_ne_mojet_byit_menshe_datyi_nachala'],
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		this.submit();
	},
	returnFunc: function(owner, kid) {},
	show: function()
	{
		sw.Promed.swLpuSectionFinansEditForm.superclass.show.apply(this, arguments);
		var loadMask = new Ext.LoadMask(Ext.get('LpuSectionFinansEditForm'), { msg: "Подождите, идет загрузка..." });
		loadMask.show();
		if (arguments[0].callback)
			this.returnFunc = arguments[0].callback;
		if (arguments[0].owner)
			this.owner = arguments[0].owner;
		if (arguments[0].action)
			this.action = arguments[0].action;
		if (arguments[0].LpuSectionFinans_id)
			this.LpuSectionFinans_id = arguments[0].LpuSectionFinans_id;
		else 
			this.LpuSectionFinans_id = null;
		if (arguments[0].LpuSection_id)
			this.LpuSection_id = arguments[0].LpuSection_id;
		else 
			this.LpuSection_id = null;
		if (arguments[0].LpuSection_Name)
			this.LpuSection_Name = arguments[0].LpuSection_Name;
		else 
			this.LpuSection_Name = null;
		if (arguments[0].LpuUnitType_id)
			this.LpuUnitType_id = arguments[0].LpuUnitType_id;
		else 
			this.LpuUnitType_id = null;
		
		this.findById('lsfedLpuSectionFinans_Plan').ownerCt.setVisible(this.LpuUnitType_id.inlist([1,6,7,9]));

		this.syncSize();
		this.syncShadow();

		this.syncSize();
		if (!arguments[0])
			{
			Ext.Msg.alert(lang['oshibka'], lang['otsutstvuyut_neobhodimyie_parametryi']);
			this.hide();
			return false;
			}
		var form = this;
		form.findById('LpuSectionFinansEditFormPanel').getForm().reset();

		switch (this.action)
		{
			case 'add':
				form.setTitle(lang['istochnik_finansirovaniya_dobavlenie']);
				break;
			case 'edit':
				form.setTitle(lang['istochnik_finansirovaniya_redaktirovanie']);
				break;
			case 'view':
				form.setTitle(lang['istochnik_finansirovaniya_prosmotr']);
				break;
		}
		
		form.findById('lsfedLpuSectionFinans_endDate').allowBlank = (!form.LpuUnitType_id.inlist([1,6,7,9]) || this.action=='view' || getRegionNick() == 'kareliya');

		
		if (this.action=='view')
		{
			form.findById('lsfedLpuSectionFinans_begDate').disable();
			form.findById('lsfedLpuSectionFinans_endDate').disable();
			form.findById('lsfedLpuSectionFinans_Plan').disable();
			form.findById('lsfedLpuSectionFinans_PlanHosp').disable();
			form.findById('lsfedLpuSectionFinans_IsMRC').disable();
			form.findById('lsfedLpuSectionFinans_IsQuoteOff').disable();
			form.findById('lsfedPayType_id').disable();
			form.findById('lsfedLpuSection_id').disable();
			form.findById('lsfedLpuSectionFinans_id').disable();
			form.buttons[0].disable();
		}
		else
		{
			form.findById('lsfedLpuSectionFinans_begDate').enable();
			form.findById('lsfedLpuSectionFinans_endDate').enable();
			form.findById('lsfedLpuSectionFinans_IsMRC').enable();
			if (form.LpuUnitType_id.inlist([1,6,9]))
			{
				form.findById('lsfedLpuSectionFinans_IsQuoteOff').enable();
			}
			if (form.LpuUnitType_id.inlist([1,6,7,9]))
			{
				form.findById('lsfedLpuSectionFinans_Plan').enable();
				form.findById('lsfedLpuSectionFinans_PlanHosp').enable();
			}
			form.findById('lsfedPayType_id').enable();
			form.findById('lsfedLpuSection_id').enable();
			form.findById('lsfedLpuSectionFinans_id').enable();
			form.buttons[0].enable();
		}
		
		form.findById('lsfedLpuSection_id').setValue(this.LpuSection_id);
		form.findById('lsfedLpuSection_Name').setValue(this.LpuSection_Name);
		if (this.action!='add')
		{
			
			form.findById('LpuSectionFinansEditFormPanel').getForm().load(
			{
				url: C_LPUSECTIONFINANS_GET,
				params:
				{
					object: 'LpuSectionFinans',
					LpuSectionFinans_id: this.LpuSectionFinans_id,
					LpuSectionFinans_Plan: '',
					LpuSectionFinans_PlanHosp: '',
					LpuSectionFinans_IsMRC: '',
					LpuSectionFinans_IsQuoteOff: '',
					LpuSectionFinans_begDate: '',
					LpuSectionFinans_endDate: '',
					PayType_id: '',
					LpuSection_id: ''
				},
				success: function ()
				{
					if (form.action!='view')
						{
							//
						}
					form.findById('lsfedPayType_id').focus(true, 100);
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
		//form.findById('lsfedLpuUnit_Name').setValue(this.LpuUnit_Name);
		form.findById('lsfedLpuSectionFinans_IsMRC').setValue(1);
		form.findById('lsfedPayType_id').focus(true, 100);
		loadMask.hide();
		}
	},
	submit: function()
	{
		var form = this.findById('LpuSectionFinansEditFormPanel');
		var loadMask = new Ext.LoadMask(Ext.get('LpuSectionFinansEditForm'), { msg: "Подождите, идет сохранение..." });
		loadMask.show();
		form.getForm().submit(
		{
			/*
			params: 
			{
				LpuSectionFinans_IsQuoteOff: form.findById('lsfedLpuSectionFinans_IsQuoteOff').getValue()
			},
			*/
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
					if (action.result.LpuSectionFinans_id)
					{
						form.ownerCt.hide();
						form.ownerCt.returnFunc(form.ownerCt.owner, action.result.LpuSectionFinans_id);
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
			autoHeight: true,
			id:'LpuSectionFinansEditFormPanel',
			frame: true,
			region: 'center',
			labelWidth: 136,
			items:
			[
			{
				name: 'LpuSectionFinans_id',
				tabIndex: -1,
				xtype: 'hidden',
				id: 'lsfedLpuSectionFinans_id'
			},
			{
				name: 'LpuSection_id',
				tabIndex: -1,
				xtype: 'hidden',
				id: 'lsfedLpuSection_id'
			},
			{
				name: 'LpuSection_Name',
				disabled: true,
				fieldLabel: lang['otdelenie'],
				xtype: 'descfield',
				id: 'lsfedLpuSection_Name'
			},
			{
				anchor: '100%',
				tabIndex: 1299,
				disabled: false,
				name: 'PayType_id',
				xtype: 'swpaytypecombo',
				id: 'lsfedPayType_id',
				lastQuery: '',
				allowBlank:false,
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
							this.ownerCt.ownerCt.findById('lsfedLpuSectionFinans_IsMRC').focus(true);
							return false;
						}
					}
				}
			},
			{
				xtype: 'swyesnocombo',
				tabIndex:1221,
				name: 'LpuSectionFinans_IsMRC',
				hiddenName: 'LpuSectionFinans_IsMRC',
				id: 'lsfedLpuSectionFinans_IsMRC',
				allowBlank: false,
				fieldLabel: lang['mrts']
			},
			{
				border: false,
				autoHeight: true,
				labelWidth: 136,
				xtype: 'fieldset',
				style: 'padding: 0px;',
				items: 
				[{
					xtype: 'textfield',
					tabIndex:1222,
					name: 'LpuSectionFinans_Plan',
					id: 'lsfedLpuSectionFinans_Plan',
					allowBlank: true,
					width: 164,
					maskRe: /\d/,
					fieldLabel: lang['plan_rabotyi_koyki']
				},
				{
					xtype: 'swyesnocombo',
					tabIndex:1223,
					hiddenName: 'LpuSectionFinans_IsQuoteOff',
					id: 'lsfedLpuSectionFinans_IsQuoteOff',
					fieldLabel: lang['otklyuchit_kvotu'],
					disabled: true
				},
				{
					xtype: 'textfield',
					tabIndex:1224,
					name: 'LpuSectionFinans_PlanHosp',
					id: 'lsfedLpuSectionFinans_PlanHosp',
					allowBlank: true,
					width: 164,
					maskRe: /\d/,
					fieldLabel: lang['plan_gospitalizatsiy']
				}]
			},
			{
				xtype: 'fieldset',
				labelWidth: 130,
				autoHeight: true,
				title: lang['period_deystviya'],
				style: 'padding: 2; padding-left: 5px',
				items: [
				{
					fieldLabel : lang['nachalo'],
					tabIndex: 1225,
					allowBlank: false,
					xtype: 'swdatefield',
					format: 'd.m.Y',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
					name: 'LpuSectionFinans_begDate',
					id: 'lsfedLpuSectionFinans_begDate'
				},
				{
					fieldLabel : lang['okonchanie'],
					tabIndex: 1226,
					allowBlank: true,
					xtype: 'swdatefield',
					format: 'd.m.Y',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
					name: 'LpuSectionFinans_endDate',
					id: 'lsfedLpuSectionFinans_endDate'
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
				{ name: 'LpuSectionFinans_id' },
				{ name: 'LpuSection_id' },
				{ name: 'PayType_id' },
				{ name: 'LpuSectionFinans_Plan' },
				{ name: 'LpuSectionFinans_PlanHosp' },
				{ name: 'LpuSectionFinans_IsMRC' },
				{ name: 'LpuSectionFinans_IsQuoteOff' },
				{ name: 'LpuSectionFinans_begDate' },
				{ name: 'LpuSectionFinans_endDate' }
			]
			),
			url: C_LPUSECTIONFINANS_SAVE
		});
		
		Ext.apply(this,
		{
			xtype: 'panel',
			border: false,
			items: [this.MainPanel]
		});
		sw.Promed.swLpuSectionFinansEditForm.superclass.initComponent.apply(this, arguments);
	}
});