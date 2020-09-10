/**
* swOrgRSchetKBKEditWindow - окно редактирования/добавления счета.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009-2010 Swan Ltd.
* @author       Chebukin Alexander
* @version      17.01.2011
*/

sw.Promed.swOrgRSchetKBKEditWindow = Ext.extend(sw.Promed.BaseForm, 
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
	id: 'OrgRSchetKBKEditWindow',
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
		var form = this.findById('orgRSchetKBKEditForm');
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
		var form = this.findById('orgRSchetKBKEditForm');
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
					if (action.result.OrgRSchetKBK_id)
					{
						current_window.hide();
						var parent_window = Ext.getCmp('OrgRSchetEditWindow');
						if (parent_window) {
							parent_window.findById(parent_window.id + '_OrgRSchetKBK').loadData();
						}
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
		var form = this.findById('orgRSchetKBKEditForm');
		if (enable) 
		{
			form.getForm().findField('OrgRSchet_KBK').enable();
			this.buttons[0].enable();
		}
		else 
		{
			form.getForm().findField('OrgRSchet_KBK').disable();
			this.buttons[0].disable();			
		}
	},
	show: function() 
	{
		sw.Promed.swOrgRSchetKBKEditWindow.superclass.show.apply(this, arguments);
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
		this.findById('orgRSchetKBKEditForm').getForm().reset();
		this.callback = Ext.emptyFn;
		this.onHide = Ext.emptyFn;
		
		if (arguments[0].OrgRSchetKBK_id) 
			this.OrgRSchetKBK_id = arguments[0].OrgRSchetKBK_id;
		else 
			this.OrgRSchetKBK_id = null;
		
		if (arguments[0].OrgRSchet_id) 
			this.OrgRSchet_id = arguments[0].OrgRSchet_id;
		else 
			this.OrghRSchet_id = null;
			
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
			if ( ( this.OrgRSchet_id ) && ( this.OrgRSchet_id > 0 ) )
				this.action = "edit";
			else 
				this.action = "add";
		}
		
		var form = this.findById('orgRSchetKBKEditForm');
		form.getForm().setValues(arguments[0]);
		
		var loadMask = new Ext.LoadMask(this.getEl(),{msg: LOAD_WAIT});
		loadMask.show();
		switch (this.action) 
		{
			case 'add':
				this.setTitle(lang['kbk_dobavlenie']);
				this.enableEdit(true);
				loadMask.hide();
				form.getForm().clearInvalid();
				break;
			case 'edit':
				this.setTitle(lang['kbk_redaktirovanie']);
				this.enableEdit(true);
				break;
			case 'view':
				this.setTitle(lang['kbk_prosmotr']);
				this.enableEdit(false);
				break;
		}
		
		if (this.action != 'add')
		{
			form.getForm().load(
			{
				params: 
				{
					OrgRSchetKBK_id: current_window.OrgRSchetKBK_id,
					OrgRSchet_id: current_window.OrgRSchet_id
				},
				failure: function() 
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
				},
				url: '/?c=Org&m=loadOrgRSchetKBK'
			});
		}
		if ( this.action != 'view' )
			form.getForm().findField('OrgRSchet_KBK').focus(true, 100);
		else
			this.buttons[3].focus();
	},
	
	initComponent: function() 
	{
		// Форма с полями 
		this.orgRSchetKBKEditForm = new Ext.form.FormPanel(
		{
			autoHeight: true,
			bodyStyle: 'padding: 5px',
			border: false,
			buttonAlign: 'left',
			frame: true,
			id: 'orgRSchetKBKEditForm',
			labelAlign: 'right',
			labelWidth: 50,
			items: 
			[{
				name: 'OrgRSchetKBK_id',
				value: 0,
				xtype: 'hidden'
			}, {				
				name: 'OrgRSchet_id',
				value: 0,
				xtype: 'hidden'
			}, {

				allowBlank: false,
				fieldLabel: lang['kbk'],
				name: 'OrgRSchet_KBK',
				xtype: 'textfield',
				autoCreate: {tag: "input", maxLength: "20", autocomplete: "off"},
				maskRe: /[0-9]/,
				anchor: '100%',
				minLength: 20,
				minLengthText: lang['dlina_polya_doljna_byit_ravna_20_simvolam']
			}],
			keys: 
			[{
				alt: true,
				fn: function(inp, e) 
				{
					switch (e.getKey()) 
					{
						case Ext.EventObject.C:
							if (this.action != 'view') 
							{
								this.doSave(false);
							}
							break;
						case Ext.EventObject.J:
							this.hide();
							break;
					}
				},
				key: [ Ext.EventObject.C, Ext.EventObject.J ],
				scope: this,
				stopEvent: true
			}],
			reader: new Ext.data.JsonReader(
			{
				success: function() 
				{ 
					//
				}
			}, 
			[
				{ name: 'OrgRSchetKBK_id' },
				{ name: 'OrgRSchet_id' },
				{ name: 'OrgRSchet_KBK' }
			]),
			url: '/?c=Org&m=saveOrgRSchetKBK'
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
				tabIndex: TABINDEX_ORSEW + 3,
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
				tabIndex: TABINDEX_ORSEW + 4,
				text: BTN_FRMCANCEL
			}],
			items: [this.orgRSchetKBKEditForm]
		});
		sw.Promed.swOrgRSchetKBKEditWindow.superclass.initComponent.apply(this, arguments);
	}
});