/**
* swOrgWorkPeriodEditWindow - окно редактирования/добавления периода ЛЛО.
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

sw.Promed.swOrgWorkPeriodEditWindow = Ext.extend(sw.Promed.BaseForm, 
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
	id: 'OrgWorkPeriodEditWindow',
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
		var form = this.findById('OrgWorkPeriodEditForm');
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
		var form = this.findById('OrgWorkPeriodEditForm');
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
					if (action.result.OrgWorkPeriod_id)
					{
						current_window.hide();
						Ext.getCmp('LpuPassportEditWindow').findById('LPEW_OrgWorkPeriodGrid').loadData();
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
			var form = this.findById('OrgWorkPeriodEditForm');
			form.getForm().findField('OrgWorkPeriod_begDate').enable();
			form.getForm().findField('OrgWorkPeriod_endDate').enable();
			this.buttons[0].enable();
		}
		else 
		{
			var form = this.findById('OrgWorkPeriodEditForm');
			form.getForm().findField('OrgWorkPeriod_begDate').disable();
			form.getForm().findField('OrgWorkPeriod_endDate').disable();
			this.buttons[0].disable();			
		}
	},
	show: function() 
	{
		sw.Promed.swOrgWorkPeriodEditWindow.superclass.show.apply(this, arguments);
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
		this.findById('OrgWorkPeriodEditForm').getForm().reset();
		this.callback = Ext.emptyFn;
		this.onHide = Ext.emptyFn;
		if (arguments[0].OrgWorkPeriod_id) 
			this.OrgWorkPeriod_id = arguments[0].OrgWorkPeriod_id;
		else 
			this.OrgWorkPeriod_id = null;
		if (arguments[0].Org_id) 
			this.Org_id = arguments[0].Org_id;
		else 
			this.Org_id = null;
			
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
			if ( ( this.OrgWorkPeriod_id ) && ( this.OrgWorkPeriod_id > 0 ) )
				this.action = "edit";
			else 
				this.action = "add";
		}
		
		var form = this.findById('OrgWorkPeriodEditForm');
		form.getForm().setValues(arguments[0]);
		
		var loadMask = new Ext.LoadMask(this.getEl(),{msg: LOAD_WAIT});
		loadMask.show();
		switch (this.action) 
		{
			case 'add':
				this.setTitle(lang['periodyi_rabotyi_v_sisteme_promed_dobavlenie']);
				this.enableEdit(true);
				loadMask.hide();
				form.getForm().clearInvalid();
				break;
			case 'edit':
				this.setTitle(lang['periodyi_rabotyi_v_sisteme_promed_redaktirovanie']);
				this.enableEdit(true);
				break;
			case 'view':
				this.setTitle(lang['periodyi_rabotyi_v_sisteme_promed_prosmotr']);
				this.enableEdit(false);
				break;
		}
		
		if (this.action != 'add')
		{
			form.getForm().load(
			{
				params: 
				{
					OrgWorkPeriod_id: current_window.OrgWorkPeriod_id,
					Org_id: current_window.Org_id
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
				url: '/?c=LpuPassport&m=loadOrgWorkPeriod'
			});
		}
		if ( this.action != 'view' )
			Ext.getCmp('LPEW_OrgWorkPeriod_begDate').focus(true, 100);
		else
			this.buttons[3].focus();
	},	
	initComponent: function() 
	{
		// Форма с полями 
		var current_window = this;
		
		this.OrgWorkPeriodEditForm = new Ext.form.FormPanel(
		{
			autoHeight: true,
			bodyStyle: 'padding: 5px',
			border: false,
			buttonAlign: 'left',
			frame: true,
			id: 'OrgWorkPeriodEditForm',
			labelAlign: 'right',
			labelWidth: 150,
			items: 
			[{
				id: 'LPEW_Org_id',
				name: 'Org_id',
				value: 0,
				xtype: 'hidden'
			}, {
				id: 'LPEW_OrgWorkPeriod_id',
				name: 'OrgWorkPeriod_id',
				value: 0,
				xtype: 'hidden'
			}, 
			{
				id: 'LPEW_OrgWorkPeriod_begDate',
				xtype: 'swdatefield',
				plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
				format: 'd.m.Y',
				fieldLabel: lang['data_nachala'],
				allowBlank: false,
				name: 'OrgWorkPeriod_begDate'
			},
			{
				id: 'LPEW_OrgWorkPeriod_endDate',
				xtype: 'swdatefield',
				plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
				format: 'd.m.Y',
				fieldLabel: lang['data_okonchaniya'],
				name: 'OrgWorkPeriod_endDate'
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
				{ name: 'Org_id' },
				{ name: 'OrgWorkPeriod_id' },
				{ name: 'OrgWorkPeriod_begDate' },
				{ name: 'OrgWorkPeriod_endDate' }
			]),
			url: '/?c=LpuPassport&m=saveOrgWorkPeriod'
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
				text: BTN_FRMCANCEL
			}],
			items: [this.OrgWorkPeriodEditForm]
		});
		sw.Promed.swOrgWorkPeriodEditWindow.superclass.initComponent.apply(this, arguments);
	}
	});