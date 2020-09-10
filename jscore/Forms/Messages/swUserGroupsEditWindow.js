/**
* swUserGroupsEditWindow - окно добавления/редактирования групп пользователей в адресной книге.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Messages
* @access       public
* @copyright    Copyright (c) 2009-2010 Swan Ltd.
* @author       Dmitry Storozhev
* @version      24.08.2011
*
*/

sw.Promed.swUserGroupsEditWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: true,
	buttonAlign: 'right',
	modal: true,
	layout: 'form',
	resizable: false,
	closable: true,
	shim: false,
	width: 500,
	closeAction: 'hide',
	id: 'swUserGroupsEditWindow',
	objectName: 'swUserGroupsEditWindow',
	title: lang['gruppa_polzovateley'],
	plain: true,
	buttons: [
		{
			handler: function()
			{
				this.ownerCt.doSave();
			},
			iconCls: 'save16',
			//tabIndex: TABINDEX_EVNVK + 34,
			text: lang['sohranit']
		},
		'-',
		{
			text: BTN_FRMHELP,
			iconCls: 'help16',
			handler: function(button, event)
			{
				ShowHelp(this.ownerCt.title);
			}
		}, {
			text      : lang['otmena'],
			tabIndex  : -1,
			tooltip   : lang['otmena'],
			iconCls   : 'cancel16',
			handler   : function()
			{
				this.ownerCt.hide();
			}
		}
	],
	
	listeners:
	{
		'hide': function()
		{
			this.CenterPanel.getForm().reset();
			this.onHide();
		}
	},
	
	show: function() 
	{
		sw.Promed.swUserGroupsEditWindow.superclass.show.apply(this, arguments);
		
		var win = this;
		var bf = win.CenterPanel.getForm();
		var glOpt = getGlobalOptions();
		win.onHide = Ext.emptyFn;

		bf.reset();
		
		// значения для редактирования 
		if (arguments[0].action)
			this.action = arguments[0].action;
		if (arguments[0].onHide) 
			this.onHide = arguments[0].onHide;
		if (arguments[0].callback) 
			this.callback = arguments[0].callback;
		if (this.action=='edit') {
			bf.setValues(arguments[0]);
		}
		switch ( this.action ) {
			case 'add':
				this.setTitle(lang['gruppa_polzovateley_dobavlenie']);
				this.findById('group_type_fieldset').setDisabled(false);
				break;
			case 'edit':
				this.setTitle(lang['gruppa_polzovateley_redaktirovanie']);
				this.findById('group_type_fieldset').setDisabled(!isSuperAdmin());
				break;
		}
		bf.findField('group_name').focus(true, 100);
	},
	
	doSave: function()
	{
		var win = this;
		var form = this.CenterPanel.getForm();
		if(!form.isValid())
		{
			sw.swMsg.alert(lang['oshibka'], lang['ne_zapolneno_obyazatelnoe_pole']);
			return false;
		}
		form.submit({
			success: function()
			{
				win.hide();
				win.callback();
			}
		});
	},
	/** Доступ к редактированию/удалению в зависимости от типа группы 
	 * 
	 */ 
	isUserAccess: function(type) {
		return ((type>=0 && isSuperAdmin()) || (type>=1 && isLpuAdmin()) || (type==3));
	},
	initComponent: function() 
	{
		
		this.CenterPanel = new Ext.form.FormPanel({
			frame: true,
			border: false,
			labelAlign: 'top',
			url: '/?c=Messages&m=saveGroup',
			items: [
				{
					xtype: 'hidden',
					name: 'group_id'
				},
				{
					xtype: 'hidden',
					name: 'dn'
				},
				{
					xtype: 'fieldset',
					labelAlign: 'left',
					height: 120,
					style:'padding: 0px 3px 0px 6px;',
					hidden: !isSuperAdmin() && !isLpuAdmin(),
					id: 'group_type_fieldset',
					title: lang['tip_gruppyi'],
					items: [
						{
							xtype: 'radio',
							hideLabel: true,
							boxLabel: lang['obschaya_gruppa_dostupna_dlya_vseh_polzovateley'],
							name: 'group_type',
							inputValue: 0,
							hidden: !isSuperAdmin()
						}, {
							xtype: 'radio',
							hideLabel: true,
							boxLabel: lang['lokalnaya_gruppa_dostupna_dlya_polzovateley_vashego_lpu'],
							inputValue: 1,
							hidden: !isSuperAdmin() && !isLpuAdmin(),
							name: 'group_type'
						}, {
							xtype: 'radio',
							hideLabel: true,
							boxLabel: lang['personalnaya_gruppa_dostupna_tolko_dlya_vas'],
							hidden: !isSuperAdmin() && !isLpuAdmin(),
							inputValue: 2,
							name: 'group_type',
							checked: true
						}
					]
				}, {
					xtype: 'textfield',
					name: 'group_name',
					allowBlank: false,
					anchor: '100%',
					fieldLabel: (isSuperAdmin() || isLpuAdmin())?lang['nazvanie_gruppyi']:lang['personalnaya_gruppa']
				}
			],
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
				success: function() { 
				}
			}, 
			[
				{ name: 'group_id' },
				{ name: 'group_name' },
				{ name: 'dn' },
				{ name: 'group_type' }
			])
		});
		
		Ext.apply(this, 
		{
			autoHeight: true,
			defaults:
			{
				border: false,
				bodyStyle: 'padding: 3px; background: #DFE8F6;'
			},
			items: [this.CenterPanel]
		});
		sw.Promed.swUserGroupsEditWindow.superclass.initComponent.apply(this, arguments);
	}
});