/**
* swLpuOMSEditWindow - окно редактирования/добавления периода ОМС.
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

sw.Promed.swLpuOMSEditWindow = Ext.extend(sw.Promed.BaseForm, 
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
	id: 'LpuOMSEditWindow',
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
		var form = this.findById('LpuPeriodOMSEditForm');
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
		var form = this.findById('LpuPeriodOMSEditForm');
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
					if (action.result.LpuPeriodOMS_id)
					{
						current_window.hide();
						Ext.getCmp('LpuPassportEditWindow').findById('LPEW_OMSGrid').loadData();
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
			var form = this.findById('LpuPeriodOMSEditForm');
			form.getForm().findField('LpuPeriodOMS_begDate').enable();
			form.getForm().findField('LpuPeriodOMS_DogNum').enable();
			form.getForm().findField('LpuPeriodOMS_RegNumC').enable();
			form.getForm().findField('LpuPeriodOMS_RegNumN').enable();
			form.getForm().findField('Org_id').enable();
			form.getForm().findField('LpuPeriodOMS_Descr').enable();
			this.buttons[0].enable();
		}
		else 
		{
			var form = this.findById('LpuPeriodOMSEditForm');
			form.getForm().findField('LpuPeriodOMS_begDate').disable();
			form.getForm().findField('LpuPeriodOMS_DogNum').disable();
			form.getForm().findField('LpuPeriodOMS_RegNumC').disable();
			form.getForm().findField('LpuPeriodOMS_RegNumN').disable();
			form.getForm().findField('Org_id').enable();
			form.getForm().findField('LpuPeriodOMS_Descr').enable();
			this.buttons[0].disable();			
		}
	},
	show: function() 
	{
		sw.Promed.swLpuOMSEditWindow.superclass.show.apply(this, arguments);
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
		this.findById('LpuPeriodOMSEditForm').getForm().reset();
		this.callback = Ext.emptyFn;
		this.onHide = Ext.emptyFn;
		
		if (arguments[0].LpuPeriodOMS_id) 
			this.LpuPeriodOMS_id = arguments[0].LpuPeriodOMS_id;
		else 
			this.LpuPeriodOMS_id = null;
		if (arguments[0].LpuPeriodOMS_pid) 
			this.LpuPeriodOMS_pid = arguments[0].LpuPeriodOMS_pid;
		else {
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
			if ( ( this.LpuPeriodOMS_id ) && ( this.LpuPeriodOMS_id > 0 ) )
				this.action = "edit";
			else 
				this.action = "add";
		}
		
		var form = this.findById('LpuPeriodOMSEditForm');
		form.getForm().setValues(arguments[0]);
		
		var loadMask = new Ext.LoadMask(this.getEl(),{msg: LOAD_WAIT});
		loadMask.show();
		switch (this.action) 
		{
			case 'add':
				this.setTitle(lang['period_po_oms_dobavlenie']);
				this.enableEdit(true);
				loadMask.hide();
				form.getForm().clearInvalid();
				break;
			case 'edit':
				this.setTitle(lang['period_po_oms_redaktirovanie']);
				this.enableEdit(true);
				break;
			case 'view':
				this.setTitle(lang['period_po_oms_prosmotr']);
				this.enableEdit(false);
				break;
		}
		
		if (this.action != 'add')
		{
			form.getForm().load(
			{
				params: 
				{
					LpuPeriodOMS_id: current_window.LpuPeriodOMS_id,
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
				url: '/?c=LpuPassport&m=loadLpuOMS'
			});
		}
		if ( this.action != 'view' )
			Ext.getCmp('LEW_LpuPeriodOMS_begDate').focus(true, 100);
		else
			this.buttons[3].focus();
	},	
	initComponent: function() 
	{
		// Форма с полями 
		var current_window = this;
		
		this.LpuPeriodOMSEditForm = new Ext.form.FormPanel(
		{
			autoHeight: true,
			bodyStyle: 'padding: 5px',
			border: false,
			buttonAlign: 'left',
			frame: true,
			id: 'LpuPeriodOMSEditForm',
			labelAlign: 'right',
			labelWidth: 180,
			items: 
			[{
				id: 'LEW_Lpu_id',
				name: 'Lpu_id',
				value: 0,
				xtype: 'hidden'
			}, {
				id: 'LEW_LpuPeriodOMS_id',
				name: 'LpuPeriodOMS_id',
				value: 0,
				xtype: 'hidden'
			}, 
			{
				id: 'LEW_LpuPeriodOMS_pid',
				name: 'LpuPeriodOMS_pid',
				value: 0,
				xtype: 'hidden'
			},
			
			{
				id: 'LEW_LpuPeriodOMS_DogNum',
				fieldLabel: lang['nomer_dogovora'],
				xtype: 'textfield',
				disabled: true,
				autoCreate: {tag: "input", maxLength: "20", autocomplete: "off"},
				tabIndex: TABINDEX_LPOMSEW + 2,
				anchor: '100%',
				name: 'LpuPeriodOMS_DogNum'
			},
			{
				id: 'LEW_LpuPeriodOMS_begDate',
				xtype: 'swdatefield',
				plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
				tabIndex: TABINDEX_LPOMSEW + 0,
				format: 'd.m.Y',
				fieldLabel: lang['data_dogovora'],
				allowBlank: false,
				name: 'LpuPeriodOMS_begDate'
			},{
				fieldLabel: lang['organizatsiya'],
				anchor: '100%',
				hiddenName: 'Org_id',
				allowBlank: false,
				xtype: 'sworgcombo',
				onTrigger1Click: function() {
					var combo = this;
					if (this.disabled) {
						return false;
					}

					getWnd('swOrgSearchWindow').show({
						onSelect: function(orgData) {
							if ( orgData.Org_id > 0 ) {
								combo.getStore().load({
									params: {
										Object:'Org',
										Org_id: orgData.Org_id,
										Org_Name:''
									},
									callback: function() {
										combo.setValue(orgData.Org_id);
										combo.focus(true, 500);
										combo.fireEvent('change', combo);
									}
								});
							}

							getWnd('swOrgSearchWindow').hide();
						},
						onClose: function() {combo.focus(true, 200)}
					});
				}
			},
			{
				id: 'LEW_LpuPeriodOMS_RegNumC',
				fieldLabel: lang['kod_territorii_lpu'],
				tabIndex: TABINDEX_LPOMSEW + 3,
				xtype: 'textfield',
				disabled: true,
				autoCreate: {tag: "input", maxLength: "20", autocomplete: "off"},
				maskRe: /[0-9]/,
				anchor: '100%',
				allowBlank: false,
				name: 'LpuPeriodOMS_RegNumC'
			},
			{
				id: 'LEW_LpuPeriodOMS_RegNumN',
				fieldLabel: lang['registratsionnyiy_nomer_lpu'],
				tabIndex: TABINDEX_LPOMSEW + 4,
				xtype: 'textfield',
				disabled: true,
				autoCreate: {tag: "input", maxLength: "20", autocomplete: "off"},
				maskRe: /[0-9]/,
				anchor: '100%',
				allowBlank: true,
				name: 'LpuPeriodOMS_RegNumN'
			},
			{
				id: 'LEW_LpuPeriodOMS_Descr',
				fieldLabel: lang['primechanie_k_dogovoru'],
				tabIndex: TABINDEX_LPOMSEW + 4,
				xtype: 'textfield',
				disabled: true,
				anchor: '100%',
				allowBlank: true,
				name: 'LpuPeriodOMS_Descr'
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
				{ name: 'LpuPeriodOMS_id' },
				{ name: 'LpuPeriodOMS_pid' },
				{ name: 'LpuPeriodOMS_begDate' },
				{ name: 'Org_id' },
				{ name: 'LpuPeriodOMS_DogNum' },
				{ name: 'LpuPeriodOMS_RegNumC' },
				{ name: 'LpuPeriodOMS_RegNumN' },
				{ name: 'LpuPeriodOMS_Descr'}
			]),
			url: '/?c=LpuPassport&m=saveLpuOMS'
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
				tabIndex: TABINDEX_LPOMSEW + 5,
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
				tabIndex: TABINDEX_LPOMSEW + 6,
				text: BTN_FRMCANCEL
			}],
			items: [this.LpuPeriodOMSEditForm]
		});
		sw.Promed.swLpuOMSEditWindow.superclass.initComponent.apply(this, arguments);
	}
	});