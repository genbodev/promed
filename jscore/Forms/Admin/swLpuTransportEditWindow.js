/**
* swLpuTransportEditWindow - окно редактирования/добавления транспорта.
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

sw.Promed.swLpuTransportEditWindow = Ext.extend(sw.Promed.BaseForm, 
{
	action: null,
	autoHeight: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	split: true,
	width: 420,
	layout: 'form',
	id: 'LpuTransportEditWindow',
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
		var form = this.findById('LpuTransportEditForm');
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
		var form = this.findById('LpuTransportEditForm');
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
					if (action.result.LpuTransport_id)
					{
						current_window.hide();
						Ext.getCmp('LpuPassportEditWindow').findById('LPEW_TransportGrid').loadData();
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
			var form = this.findById('LpuTransportEditForm');
			form.getForm().findField('LpuTransport_Name').enable();
			form.getForm().findField('LpuTransport_Producer').enable();
			form.getForm().findField('LpuTransport_Model').enable();
			form.getForm().findField('LpuTransport_ReleaseDT').enable();
			form.getForm().findField('LpuTransport_PurchaseDT').enable();
			form.getForm().findField('LpuTransport_Supplier').enable();
			form.getForm().findField('LpuTransport_RegNum').enable();
			form.getForm().findField('LpuTransport_EngineNum').enable();
			form.getForm().findField('LpuTransport_BodyNum').enable();
			form.getForm().findField('LpuTransport_ChassiNum').enable();
			form.getForm().findField('LpuTransport_StartUpDT').enable();
			form.getForm().findField('LpuTransport_WearPersent').enable();
			form.getForm().findField('LpuTransport_PurchaseCost').enable();
			form.getForm().findField('LpuTransport_ResidualCost').enable();
			form.getForm().findField('LpuTransport_ValuationDT').enable();
			form.getForm().findField('LpuTransport_IsNationProj').enable();
			this.buttons[0].enable();
		}
		else 
		{
			var form = this.findById('LpuTransportEditForm');
			form.getForm().findField('LpuTransport_Name').disable();
			form.getForm().findField('LpuTransport_Producer').disable();
			form.getForm().findField('LpuTransport_Model').disable();
			form.getForm().findField('LpuTransport_ReleaseDT').disable();
			form.getForm().findField('LpuTransport_PurchaseDT').disable();
			form.getForm().findField('LpuTransport_Supplier').disable();
			form.getForm().findField('LpuTransport_RegNum').disable();
			form.getForm().findField('LpuTransport_EngineNum').disable();
			form.getForm().findField('LpuTransport_BodyNum').disable();
			form.getForm().findField('LpuTransport_ChassiNum').disable();
			form.getForm().findField('LpuTransport_StartUpDT').disable();
			form.getForm().findField('LpuTransport_WearPersent').disable();
			form.getForm().findField('LpuTransport_PurchaseCost').disable();
			form.getForm().findField('LpuTransport_ResidualCost').disable();
			form.getForm().findField('LpuTransport_ValuationDT').disable();
			form.getForm().findField('LpuTransport_IsNationProj').disable();
			this.buttons[0].disable();			
		}
	},
	show: function() 
	{
		sw.Promed.swLpuTransportEditWindow.superclass.show.apply(this, arguments);
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
		this.findById('LpuTransportEditForm').getForm().reset();
		this.callback = Ext.emptyFn;
		this.onHide = Ext.emptyFn;
		if (arguments[0].LpuTransport_id) 
			this.LpuTransport_id = arguments[0].LpuTransport_id;
		else 
			this.LpuTransport_id = null;
			
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
			if ( ( this.LpuTransport_id ) && ( this.LpuTransport_id > 0 ) )
				this.action = "edit";
			else 
				this.action = "add";
		}
		
		var form = this.findById('LpuTransportEditForm');
		form.getForm().setValues(arguments[0]);
		
		var loadMask = new Ext.LoadMask(this.getEl(),{msg: LOAD_WAIT});
		loadMask.show();
		switch (this.action) 
		{
			case 'add':
				this.setTitle(lang['transport_dobavlenie']);
				this.enableEdit(true);
				loadMask.hide();
				form.getForm().clearInvalid();
				break;
			case 'edit':
				this.setTitle(lang['transport_redaktirovanie']);
				this.enableEdit(true);
				break;
			case 'view':
				this.setTitle(lang['transport_prosmotr']);
				this.enableEdit(false);
				break;
		}
		
		if (this.action != 'add')
		{
			form.getForm().load(
			{
				params: 
				{
					LpuTransport_id: current_window.LpuTransport_id,
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
				url: '/?c=LpuPassport&m=loadLpuTransport'
			});
		}
		if ( this.action != 'view' )
			Ext.getCmp('LPEW_LpuTransport_Name').focus(true, 100);
		else
			this.buttons[3].focus();
	},	
	initComponent: function() 
	{
		// Форма с полями 
		var current_window = this;
		
		this.LpuTransportEditForm = new Ext.form.FormPanel(
		{
			autoHeight: true,
			bodyStyle: 'padding: 5px',
			border: false,
			buttonAlign: 'left',
			frame: true,
			id: 'LpuTransportEditForm',
			labelAlign: 'right',
			labelWidth: 180,
			items: 
			[{
				id: 'LPEW_Lpu_id',
				name: 'Lpu_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'LpuTransport_id',
				value: 0,
				xtype: 'hidden'
			}, 
			{
				fieldLabel: lang['naimenovanie'],
				xtype: 'textfield',
				disabled: true,
				autoCreate: {tag: "input", maxLength: "24", autocomplete: "off"},
				anchor: '100%',
				id: 'LPEW_LpuTransport_Name',
				name: 'LpuTransport_Name',
				tabIndex: TABINDEX_LPTEW + 3
			},
			{
				fieldLabel: lang['proizvoditel'],
				xtype: 'textfield',
				disabled: true,
				autoCreate: {tag: "input", maxLength: "24", autocomplete: "off"},
				anchor: '100%',
				name: 'LpuTransport_Producer',
				tabIndex: TABINDEX_LPTEW + 3
			},
			{
				fieldLabel: lang['model'],
				xtype: 'textfield',
				disabled: true,
				autoCreate: {tag: "input", maxLength: "24", autocomplete: "off"},
				anchor: '100%',
				name: 'LpuTransport_Model',
				tabIndex: TABINDEX_LPTEW + 3
			},
			{
				fieldLabel: lang['data_vyipuska'],
				xtype: 'swdatefield',
				plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
				format: 'd.m.Y',
				disabled: true,
				name: 'LpuTransport_ReleaseDT',
				tabIndex: TABINDEX_LPTEW + 3
			},
			{
				fieldLabel: lang['data_priobreteniya'],
				xtype: 'swdatefield',
				plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
				format: 'd.m.Y',
				disabled: true,
				name: 'LpuTransport_PurchaseDT',
				tabIndex: TABINDEX_LPTEW + 3
			},
			{
				fieldLabel: lang['postavschik'],
				xtype: 'textfield',
				disabled: true,
				autoCreate: {tag: "input", maxLength: "24", autocomplete: "off"},
				anchor: '100%',
				name: 'LpuTransport_Supplier',
				tabIndex: TABINDEX_LPTEW + 3
			},
			{
				fieldLabel: lang['registratsionnyiy_nomer'],
				xtype: 'textfield',
				disabled: true,
				autoCreate: {tag: "input", maxLength: "24", autocomplete: "off"},
				anchor: '100%',
				name: 'LpuTransport_RegNum',
				tabIndex: TABINDEX_LPTEW + 3
			},
			{
				fieldLabel: lang['nomer_dvigatelya'],
				xtype: 'textfield',
				disabled: true,
				autoCreate: {tag: "input", maxLength: "24", autocomplete: "off"},
				anchor: '100%',
				name: 'LpuTransport_EngineNum',
				tabIndex: TABINDEX_LPTEW + 3
			},
			{
				fieldLabel: lang['nomer_kuzova'],
				xtype: 'textfield',
				disabled: true,
				autoCreate: {tag: "input", maxLength: "24", autocomplete: "off"},
				anchor: '100%',
				name: 'LpuTransport_BodyNum',
				tabIndex: TABINDEX_LPTEW + 3
			},
			{
				fieldLabel: lang['nomer_shassi'],
				xtype: 'textfield',
				disabled: true,
				autoCreate: {tag: "input", maxLength: "24", autocomplete: "off"},
				anchor: '100%',
				name: 'LpuTransport_ChassiNum',
				tabIndex: TABINDEX_LPTEW + 3
			},
			{
				fieldLabel: lang['data_vvoda_v_ekspluatatsiyu'],
				xtype: 'swdatefield',
				plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
				format: 'd.m.Y',
				disabled: true,
				name: 'LpuTransport_StartUpDT',
				tabIndex: TABINDEX_LPTEW + 3
			},
			{
				fieldLabel: lang['%_iznosa'],
				xtype: 'textfield',
				disabled: true,
				autoCreate: {tag: "input", maxLength: "3", autocomplete: "off"},
				maskRe: /[0-9]/,
				anchor: '100%',
				name: 'LpuTransport_WearPersent',
				tabIndex: TABINDEX_LPTEW + 3
			},
			{
				fieldLabel: lang['stoimost_priobreteniya'],
				xtype: 'textfield',
				disabled: true,
				autoCreate: {tag: "input", maxLength: "8", autocomplete: "off"},
				maskRe: /[0-9]/,
				anchor: '100%',
				name: 'LpuTransport_PurchaseCost',
				tabIndex: TABINDEX_LPTEW + 3
			},
			{
				fieldLabel: lang['ostatochnaya_stoimost'],
				xtype: 'textfield',
				disabled: true,
				autoCreate: {tag: "input", maxLength: "8", autocomplete: "off"},
				maskRe: /[0-9]/,
				anchor: '100%',
				name: 'LpuTransport_ResidualCost',
				tabIndex: TABINDEX_LPTEW + 3
			},
			{
				fieldLabel: lang['data_otsenki_stoimosti'],
				xtype: 'swdatefield',
				plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
				format: 'd.m.Y',
				disabled: true,
				name: 'LpuTransport_ValuationDT',
				tabIndex: TABINDEX_LPTEW + 3
			},
			{
				xtype: 'swyesnocombo',
				tabIndex: TABINDEX_LPTEW + 3,
				fieldLabel: lang['postavlen_po_nats_proektu'],
				hiddenName: 'LpuTransport_IsNationProj'
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
				{ name: 'LpuTransport_id' },
				{ name: 'LpuTransport_Name' },
				{ name: 'LpuTransport_Producer' },
				{ name: 'LpuTransport_Model' },
				{ name: 'LpuTransport_ReleaseDT' },
				{ name: 'LpuTransport_PurchaseDT' },
				{ name: 'LpuTransport_Supplier' },
				{ name: 'LpuTransport_RegNum' },
				{ name: 'LpuTransport_EngineNum' },
				{ name: 'LpuTransport_BodyNum' },
				{ name: 'LpuTransport_ChassiNum' },
				{ name: 'LpuTransport_StartUpDT' },
				{ name: 'LpuTransport_WearPersent' },
				{ name: 'LpuTransport_PurchaseCost' },
				{ name: 'LpuTransport_ResidualCost' },
				{ name: 'LpuTransport_ValuationDT' },
				{ name: 'LpuTransport_IsNationProj' }
			]),
			url: '/?c=LpuPassport&m=saveLpuTransport'
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
				tabIndex: TABINDEX_LPTEW + 4,
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
				tabIndex: TABINDEX_LPTEW + 4,
				text: BTN_FRMCANCEL
			}],
			items: [this.LpuTransportEditForm]
		});
		sw.Promed.swLpuTransportEditWindow.superclass.initComponent.apply(this, arguments);
	}
	});