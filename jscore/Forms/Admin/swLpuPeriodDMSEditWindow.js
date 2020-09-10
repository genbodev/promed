/**
* swLpuPeriodDMSEditWindow - окно редактирования/добавления периода ДМС.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009-2010 Swan Ltd.
* @version      05.10.2011
*/

sw.Promed.swLpuPeriodDMSEditWindow = Ext.extend(sw.Promed.BaseForm, 
{
	action: null,
	autoHeight: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	split: true,
	width: 400,
	layout: 'form',
	id: 'LpuPeriodDMSEditWindow',
	listeners: 
	{
		hide: function() 
		{
			this.onHide();
		}
	},
	modal: true,
	onHide: Ext.emptyFn,
	plain: true,
	resizable: false,
	doSave: function() 
	{
		var form = this.findById('LpuPeriodDMSEditForm');
		if ( !form.getForm().isValid() ) 
		{
			sw.swMsg.show(
			{
				buttons: Ext.Msg.OK,
				fn: function() 
				{
					form.getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		this.submit();
		return true;
	},
	submit: function() 
	{
		var form = this.findById('LpuPeriodDMSEditForm');
		var current_window = this;
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
		loadMask.show();
		form.getForm().submit(
		{
			params: 
			{
				action: current_window.action
			},
			failure: function(result_form, action) 
			{
				loadMask.hide();
				if (action.result) 
				{
					if (action.result.Error_Code)
					{
						Ext.Msg.alert(lang['oshibka_#']+action.result.Error_Code, action.result.Error_Message);
					}
				}
			},
			success: function(result_form, action) 
			{
				loadMask.hide();
				if (action.result)
				{
					if (action.result.LpuPeriodDMS_id)
					{
						current_window.hide();
						Ext.getCmp('LpuPassportEditWindow').findById('LPEW_DMSGrid').loadData();
					}
					else
					{
						sw.swMsg.show(
						{
							buttons: Ext.Msg.OK,
							fn: function() 
							{
								form.hide();
							},
							icon: Ext.Msg.ERROR,
							msg: lang['pri_vyipolnenii_operatsii_sohraneniya_proizoshla_oshibka_pojaluysta_povtorite_popyitku_chut_pozje'],
							title: lang['oshibka']
						});
					}
				}
			}
		});
	},
	enableEdit: function(enable) 
	{
		var form = this;
		if (enable) 
		{
			var form = this.findById('LpuPeriodDMSEditForm');
			form.getForm().findField('LpuPeriodDMS_begDate').enable();
			form.getForm().findField('LpuPeriodDMS_endDate').enable();
			form.getForm().findField('LpuPeriodDMS_DogNum').enable();
			this.buttons[0].enable();
		}
		else 
		{
			var form = this.findById('LpuPeriodDMSEditForm');
			form.getForm().findField('LpuPeriodDMS_begDate').disable();
			form.getForm().findField('LpuPeriodDMS_endDate').disable();
			form.getForm().findField('LpuPeriodDMS_DogNum').disable();
			this.buttons[0].disable();			
		}
	},
	show: function() 
	{
		sw.Promed.swLpuPeriodDMSEditWindow.superclass.show.apply(this, arguments);
		var current_window = this;
		if (!arguments[0]) 
		{
			sw.swMsg.show(
			{
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.ERROR,
				msg: lang['oshibka_otkryitiya_formyi_ne_ukazanyi_nujnyie_vhodnyie_parametryi'],
				title: lang['oshibka'],
				fn: function() {
					this.hide();
				}
			});
		}
		this.focus();
		this.findById('LpuPeriodDMSEditForm').getForm().reset();
		this.callback = Ext.emptyFn;
		this.onHide = Ext.emptyFn;
		if (arguments[0].LpuPeriodDMS_id) 
			this.LpuPeriodDMS_id = arguments[0].LpuPeriodDMS_id;
		else 
			this.LpuPeriodDMS_id = null;
		if (arguments[0].Lpu_id) 
			this.Lpu_id = arguments[0].Lpu_id;
		else 
			this.Lpu_id = null;
			
		if (arguments[0].callback) 
		{
			this.callback = arguments[0].callback;
		}
		if (arguments[0].owner) 
		{
			this.owner = arguments[0].owner;
		}
		if (arguments[0].onHide) 
		{
			this.onHide = arguments[0].onHide;
		}
		if (arguments[0].action) 
		{
			this.action = arguments[0].action;
		}
		else 
		{
			if ( ( this.LpuPeriodDMS_id ) && ( this.LpuPeriodDMS_id > 0 ) )
				this.action = "edit";
			else 
				this.action = "add";
		}
		
		var form = this.findById('LpuPeriodDMSEditForm');
		form.getForm().setValues(arguments[0]);
		
		var loadMask = new Ext.LoadMask(this.getEl(),{msg: LOAD_WAIT});
		loadMask.show();
		switch (this.action) 
		{
			case 'add':
				this.setTitle(lang['period_po_dms_dobavlenie']);
				this.enableEdit(true);
				loadMask.hide();
				form.getForm().clearInvalid();
				break;
			case 'edit':
				this.setTitle(lang['period_po_dms_redaktirovanie']);
				this.enableEdit(true);
				break;
			case 'view':
				this.setTitle(lang['period_po_dms_prosmotr']);
				this.enableEdit(false);
				break;
		}
		
		if (this.action != 'add')
		{
			form.getForm().load(
			{
				params: 
				{
					LpuPeriodDMS_id: current_window.LpuPeriodDMS_id,
					Lpu_id: current_window.Lpu_id
				},
				failure: function(f, o, a)
				{
					log(o);
					log(a);
					loadMask.hide();
					sw.swMsg.show(
					{
						buttons: Ext.Msg.OK,
						fn: function() 
						{
							current_window.hide();
						},
						icon: Ext.Msg.ERROR,
						msg: lang['oshibka_zaprosa_k_serveru_poprobuyte_povtorit_operatsiyu'],
						title: lang['oshibka']
					});
				},
				success: function() 
				{
					loadMask.hide();
				},
				url: '/?c=LpuPassport&m=loadLpuPeriodDMS'
			});
		}
		if ( this.action != 'view' )
			Ext.getCmp('LPEW_LpuPeriodDMS_begDate').focus(true, 100);
		else
			this.buttons[3].focus();
	},	
	initComponent: function() 
	{
		// Форма с полями 
		var current_window = this;
		
		this.LpuPeriodDMSEditForm = new Ext.form.FormPanel(
		{
			autoHeight: true,
			bodyStyle: 'padding: 5px',
			border: false,
			buttonAlign: 'left',
			frame: true,
			id: 'LpuPeriodDMSEditForm',
			labelAlign: 'right',
			labelWidth: 150,
			items: 
			[{
				id: 'LPEW_Lpu_id',
				name: 'Lpu_id',
				value: 0,
				xtype: 'hidden'
			}, {
				id: 'LPEW_LpuPeriodDMS_id',
				name: 'LpuPeriodDMS_id',
				value: 0,
				xtype: 'hidden'
			}, 
			{
				id: 'LPEW_LpuPeriodDMS_begDate',
				xtype: 'swdatefield',
				plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
				tabIndex: TABINDEX_LPDMSEW + 0,
				format: 'd.m.Y',
				fieldLabel: lang['data_vklyucheniya'],
				allowBlank: false,
				name: 'LpuPeriodDMS_begDate'
			},
			{
				id: 'LPEW_LpuPeriodDMS_endDate',
				xtype: 'swdatefield',
				plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
				tabIndex: TABINDEX_LPDMSEW + 1,
				format: 'd.m.Y',
				fieldLabel: lang['data_isklyucheniya'],
				name: 'LpuPeriodDMS_endDate'
			},
			{
				id: 'LPEW_LpuPeriodDMS_DogNum',
				fieldLabel: lang['nomer_dogovora'],
				xtype: 'textfield',
				disabled: true,
				autoCreate: {tag: "input", maxLength: "20", autocomplete: "off"},
				tabIndex: TABINDEX_LPDMSEW + 2,
				anchor: '100%',
				name: 'LpuPeriodDMS_DogNum'
			}
			],
			reader: new Ext.data.JsonReader(
			{
				success: function() 
				{ 
					//
				}
			}, 
			[
				{ name: 'Lpu_id' },
				{ name: 'LpuPeriodDMS_id' },
				{ name: 'LpuPeriodDMS_begDate' },
				{ name: 'LpuPeriodDMS_endDate' },
				{ name: 'LpuPeriodDMS_DogNum' }
			]),
			url: '/?c=LpuPassport&m=saveLpuPeriodDMS'
		});
		Ext.apply(this, 
		{
			buttons: 
			[{
				handler: function() 
				{
					this.ownerCt.doSave();
				},
				iconCls: 'save16',
				tabIndex: TABINDEX_LPDMSEW + 3,
				text: BTN_FRMSAVE
			}, 
			{
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() 
				{
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				tabIndex: TABINDEX_LPDMSEW + 4,
				text: BTN_FRMCANCEL
			}],
			items: [this.LpuPeriodDMSEditForm]
		});
		sw.Promed.swLpuPeriodDMSEditWindow.superclass.initComponent.apply(this, arguments);
	}
	});