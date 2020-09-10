/**
* swLpuQuoteEditWindow - окно редактирования/добавления оборудования.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009-2011 Swan Ltd.
* @version      05.10.2011
*/

sw.Promed.swLpuQuoteEditWindow = Ext.extend(sw.Promed.BaseForm, 
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
	id: 'LpuQuoteEditWindow',
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
		var form = this.findById('LpuQuoteEditForm');
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
		var form = this.findById('LpuQuoteEditForm');
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
					if (action.result.LpuQuote_id)
					{
						current_window.hide();
						Ext.getCmp('LpuPassportEditWindow').findById('LPEW_LpuQuoteGrid').loadData();
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
			var form = this.findById('LpuQuoteEditForm');
			form.getForm().findField('PayType_id').enable();
			form.getForm().findField('LpuQuote_HospCount').enable();
			form.getForm().findField('LpuQuote_BedDaysCount').enable();
			form.getForm().findField('LpuQuote_VizitCount').enable();
			form.getForm().findField('LpuQuote_begDate').enable();
			form.getForm().findField('LpuQuote_endDate').enable();
			this.buttons[0].enable();
		}
		else 
		{
			var form = this.findById('LpuQuoteEditForm');
			form.getForm().findField('PayType_id').disable();
			form.getForm().findField('LpuQuote_HospCount').disable();
			form.getForm().findField('LpuQuote_BedDaysCount').disable();
			form.getForm().findField('LpuQuote_VizitCount').disable();
			form.getForm().findField('LpuQuote_begDate').disable();
			form.getForm().findField('LpuQuote_endDate').disable();
			this.buttons[0].disable();			
		}
	},
	show: function() 
	{
		sw.Promed.swLpuQuoteEditWindow.superclass.show.apply(this, arguments);
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
		this.findById('LpuQuoteEditForm').getForm().reset();
		this.callback = Ext.emptyFn;
		this.onHide = Ext.emptyFn;
		if (arguments[0].LpuQuote_id) 
			this.LpuQuote_id = arguments[0].LpuQuote_id;
		else 
			this.LpuQuote_id = null;
			
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
			if ( ( this.LpuQuote_id ) && ( this.LpuQuote_id > 0 ) )
				this.action = "edit";
			else 
				this.action = "add";
		}
		
		var form = this.findById('LpuQuoteEditForm');
		form.getForm().setValues(arguments[0]);
		
		var loadMask = new Ext.LoadMask(this.getEl(),{msg: LOAD_WAIT});
		loadMask.show();
		switch (this.action) 
		{
			case 'add':
				this.setTitle(lang['raschetnyie_kvotyi_dobavlenie']);
				this.enableEdit(true);
				loadMask.hide();
				form.getForm().clearInvalid();
				break;
			case 'edit':
				this.setTitle(lang['raschetnyie_kvotyi_redaktirovanie']);
				this.enableEdit(true);
				break;
			case 'view':
				this.setTitle(lang['raschetnyie_kvotyi_prosmotr']);
				this.enableEdit(false);
				break;
		}
		
		if (this.action != 'add')
		{
			form.getForm().load(
			{
				params: 
				{
					LpuQuote_id: current_window.LpuQuote_id,
					Lpu_id: current_window.Lpu_id
				},
				failure: function(f, o, a)
				{
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
					current_window.findById('LPEW_Lpu_id').setValue(current_window.Lpu_id);
				},
				url: '/?c=LpuPassport&m=loadLpuQuote'
			});
		}
		if ( this.action != 'view' )
			Ext.getCmp('LPEW_PayType_id').focus(true, 100);
		else
			this.buttons[3].focus();
	},	
	initComponent: function() 
	{
		// Форма с полями 
		var current_window = this;
		
		this.LpuQuoteEditForm = new Ext.form.FormPanel(
		{
			autoHeight: true,
			bodyStyle: 'padding: 5px',
			border: false,
			buttonAlign: 'left',
			frame: true,
			id: 'LpuQuoteEditForm',
			labelAlign: 'right',
			labelWidth: 150,
			items: 
			[{
				id: 'LPEW_Lpu_id',
				name: 'Lpu_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'LpuQuote_id',
				value: 0,
				xtype: 'hidden'
			},
			{
				xtype: 'swpaytypecombo',
				tabIndex: TABINDEX_LPQEW + 3,
				allowBlank: false,
				anchor: '100%',
				id: 'LPEW_PayType_id',
				name: 'PayType_id'
			},
			{
				fieldLabel: lang['kol-vo_gospitalizatsiy'],
				disabled: true,
				tabIndex: TABINDEX_LPQEW + 3,
				maskRe: /[0-9]/,
				xtype: 'textfield',
				width: 300,
				anchor: '100%',
				name: 'LpuQuote_HospCount'
			},
			{
				fieldLabel: lang['kol-vo_koyko-dney'],
				disabled: true,
				tabIndex: TABINDEX_LPQEW + 3,
				maskRe: /[0-9]/,
				xtype: 'textfield',
				width: 300,
				anchor: '100%',
				name: 'LpuQuote_BedDaysCount'
			},
			{
				fieldLabel: lang['kol-vo_posescheniy'],
				disabled: true,
				tabIndex: TABINDEX_LPQEW + 3,
				maskRe: /[0-9]/,
				xtype: 'textfield',
				width: 300,
				anchor: '100%',
				name: 'LpuQuote_VizitCount'
			},
			{				
				fieldLabel: lang['nachalo'],
				xtype: 'swdatefield',
				allowBlank: false,
				tabIndex: TABINDEX_LPQEW + 3,
				plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
				format: 'd.m.Y',
				disabled: true,
				name: 'LpuQuote_begDate'
			},
			{				
				fieldLabel: lang['okonchanie'],
				xtype: 'swdatefield',
				tabIndex: TABINDEX_LPQEW + 3,
				plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
				format: 'd.m.Y',
				disabled: true,
				name: 'LpuQuote_endDate'
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
				{ name: 'LpuQuote_id' },
				{ name: 'PayType_id' },
				{ name: 'LpuQuote_HospCount' },
				{ name: 'LpuQuote_BedDaysCount' },
				{ name: 'LpuQuote_VizitCount' },
				{ name: 'LpuQuote_begDate' },
				{ name: 'LpuQuote_endDate' }
			]),
			url: '/?c=LpuPassport&m=saveLpuQuote'
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
				tabIndex: TABINDEX_LPQEW + 3,
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
				tabIndex: TABINDEX_LPQEW + 4,
				text: BTN_FRMCANCEL
			}],
			items: [this.LpuQuoteEditForm]
		});
		sw.Promed.swLpuQuoteEditWindow.superclass.initComponent.apply(this, arguments);
	}
	});