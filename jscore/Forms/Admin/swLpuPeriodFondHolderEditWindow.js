/**
* swLpuPeriodFondHolderEditWindow - окно редактирования/добавления периода фондодержания.
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

sw.Promed.swLpuPeriodFondHolderEditWindow = Ext.extend(sw.Promed.BaseForm, 
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
	id: 'LpuPeriodFondHolderEditWindow',
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
		var form = this.findById('LpuPeriodFondHolderEditForm');
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
		var form = this.findById('LpuPeriodFondHolderEditForm');
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
					if (action.result.LpuPeriodFondHolder_id)
					{
						current_window.hide();
						Ext.getCmp('LpuPassportEditWindow').findById('LPEW_FondHolderGrid').loadData();
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
			var form = this.findById('LpuPeriodFondHolderEditForm');
			form.getForm().findField('LpuPeriodFondHolder_begDate').enable();
			form.getForm().findField('LpuRegionType_id').enable();
			form.getForm().findField('LpuPeriodFondHolder_endDate').enable();
			this.buttons[0].enable();
		}
		else 
		{
			var form = this.findById('LpuPeriodFondHolderEditForm');
			form.getForm().findField('LpuPeriodFondHolder_begDate').disable();
			form.getForm().findField('LpuRegionType_id').disable();
			form.getForm().findField('LpuPeriodFondHolder_endDate').disable();
			this.buttons[0].disable();			
		}
	},
	show: function() 
	{
		sw.Promed.swLpuPeriodFondHolderEditWindow.superclass.show.apply(this, arguments);
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
		this.findById('LpuPeriodFondHolderEditForm').getForm().reset();
		this.callback = Ext.emptyFn;
		this.onHide = Ext.emptyFn;
		if (arguments[0].LpuPeriodFondHolder_id) 
			this.LpuPeriodFondHolder_id = arguments[0].LpuPeriodFondHolder_id;
		else 
			this.LpuPeriodFondHolder_id = null;
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
			if ( ( this.LpuPeriodFondHolder_id ) && ( this.LpuPeriodFondHolder_id > 0 ) )
				this.action = "edit";
			else 
				this.action = "add";
		}
		
		var form = this.findById('LpuPeriodFondHolderEditForm');
		form.getForm().setValues(arguments[0]);
		
		var loadMask = new Ext.LoadMask(this.getEl(),{msg: LOAD_WAIT});
		loadMask.show();

        var title_name = lang['period_po_fondoderjaniyu'];
        if (getGlobalOptions().region && getGlobalOptions().region.nick != 'perm')
        {
            title_name = lang['period_po_uchastkovoy_slujbe'];
        }

		switch (this.action) 
		{
			case 'add':
				this.setTitle(title_name+lang['_dobavlenie']);
				this.enableEdit(true);
				loadMask.hide();
				form.getForm().clearInvalid();
				break;
			case 'edit':
				this.setTitle(title_name+lang['_redaktirovanie']);
				this.enableEdit(true);
				break;
			case 'view':
				this.setTitle(title_name+lang['_prosmotr']);
				this.enableEdit(false);
				break;
		}
		
		if (this.action != 'add')
		{
			form.getForm().load(
			{
				params: 
				{
					LpuPeriodFondHolder_id: current_window.LpuPeriodFondHolder_id,
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
				url: '/?c=LpuPassport&m=loadLpuPeriodFondHolder'
			});
		}
		if ( this.action != 'view' )
			Ext.getCmp('LPEW_LpuPeriodFondHolder_begDate').focus(true, 100);
		else
			this.buttons[3].focus();
	},	
	initComponent: function() 
	{
		// Форма с полями 
		var current_window = this;
		
		this.LpuPeriodFondHolderEditForm = new Ext.form.FormPanel(
		{
			autoHeight: true,
			bodyStyle: 'padding: 5px',
			border: false,
			buttonAlign: 'left',
			frame: true,
			id: 'LpuPeriodFondHolderEditForm',
			labelAlign: 'right',
			labelWidth: 150,
			items: 
			[{
				id: 'LPEW_Lpu_id',
				name: 'Lpu_id',
				value: 0,
				xtype: 'hidden'
			}, {
				id: 'LPEW_LpuPeriodFondHolder_id',
				name: 'LpuPeriodFondHolder_id',
				value: 0,
				xtype: 'hidden'
			}, 
			{
				id: 'LPEW_LpuPeriodFondHolder_begDate',
				xtype: 'swdatefield',
				plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
				tabIndex: TABINDEX_LPFHEW + 1,
				format: 'd.m.Y',
				fieldLabel: lang['data_vklyucheniya'],
				allowBlank: false,
				name: 'LpuPeriodFondHolder_begDate'
			},
			{
				id: 'LPEW_LpuPeriodFondHolder_endDate',
				xtype: 'swdatefield',
				plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
				tabIndex: TABINDEX_LPFHEW + 2,
				format: 'd.m.Y',
				fieldLabel: lang['data_isklyucheniya'],
				name: 'LpuPeriodFondHolder_endDate'
			},
			{
				id: 'LPEW_LpuRegionType_id',
				anchor: '100%',
				xtype: 'swlpuregiontypecombo',
				tabIndex: TABINDEX_LPFHEW + 3,
				format: 'string',
				fieldLabel: lang['tip_uchastka'],
				name: 'LpuRegionType_id'
			}],
			reader: new Ext.data.JsonReader(
			{
				success: function() 
				{ 
					//
				}
			}, 
			[
				{ name: 'Lpu_id' },
				{ name: 'LpuRegionType_id' },
				{ name: 'LpuPeriodFondHolder_id' },
				{ name: 'LpuPeriodFondHolder_begDate' },
				{ name: 'LpuPeriodFondHolder_endDate' }
			]),
			url: '/?c=LpuPassport&m=saveLpuPeriodFondHolder'
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
				tabIndex: TABINDEX_LPFHEW + 4,
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
				tabIndex: TABINDEX_LPFHEW + 5,
				text: BTN_FRMCANCEL
			}],
			items: [this.LpuPeriodFondHolderEditForm]
		});
		sw.Promed.swLpuPeriodFondHolderEditWindow.superclass.initComponent.apply(this, arguments);
	}
	});