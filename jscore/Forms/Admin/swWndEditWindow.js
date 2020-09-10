/**
* swWndEditWindow - окно просмотра и редактирования данных о загружаемом JS-файле.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Messages
* @access       public
* @copyright    Copyright (c) 2009-2011 Swan Ltd.
* @author       Марков Андрей
* @version      декабрь.2011
*
*/

sw.Promed.swWndEditWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: true,
	buttonAlign: 'right',
	modal: true,
	layout: 'form',
	resizable: false,
	closable: true,
	shim: false,
	width: 500,
	closeAction: 'hide',
	id: 'swWndEditWindow',
	objectName: 'swWndEditWindow',
	title: lang['obyekt_okno'],
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
		sw.Promed.swWndEditWindow.superclass.show.apply(this, arguments);
		if (!isSuperAdmin()) {
			this.hide();
		}
		var win = this;
		var bf = win.CenterPanel.getForm();
		win.onHide = Ext.emptyFn;
		bf.reset();
		
		// значения для редактирования 
		if (arguments[0].action)
			this.action = arguments[0].action;
		if (arguments[0].onHide) 
			this.onHide = arguments[0].onHide;
		if (arguments[0].callback) 
			this.callback = arguments[0].callback;
		if (arguments[0].owner) 
			this.owner = arguments[0].owner;
		if (this.action=='edit') {
			bf.setValues(arguments[0]);
		}
		switch ( this.action ) {
			case 'add':
				this.setTitle(lang['obyekt_dobavlenie']);
				bf.findField('code').setDisabled(false);
				bf.findField('path').setDisabled(false);
				bf.findField('code').focus(true, 100);
				break;
			case 'edit':
				bf.findField('code').setDisabled(true);
				bf.findField('path').setDisabled(true);
				bf.findField('region').focus(true, 100);
				this.setTitle(lang['obyekt_redaktirovanie']);
				break;
		}
		
	},
	
	doSave: function()
	{
		var win = this;
		var form = this.CenterPanel.getForm();
		if(!form.isValid())
		{
			sw.swMsg.alert(lang['oshibka'], lang['ne_zapolnenyi_obyazatelnyie_polya']);
			return false;
		}
		var params = {code: form.findField('code').getValue(), path: form.findField('path').getValue()};
		form.submit({
			params: params,
			success: function()
			{
				/*
				var p = Ext.apply(params, form.getValues());
				p['id'] = p['code']+'_'+((p['region'])?p['region']:'default');
				win.callback(win.owner, 0, new Ext.data.Record(p), win.action);
				*/
				win.callback(win.owner, 0);
				win.hide();
			}
		});
	},
	initComponent: function() 
	{
		
		this.CenterPanel = new Ext.form.FormPanel({
			frame: true,
			border: false,
			labelWidth: 130,
			labelAlign: 'left',
			url: '/?c=promed&m=saveFile',
			items: [
				{
					xtype: 'hidden',
					name: 'id'
				},
				{
					xtype: 'textfield',
					name: 'code',
					allowBlank: false,
					anchor: '100%',
					fieldLabel: lang['kod_obyekta']
				},
				{
					xtype: 'textfield',
					name: 'region',
					allowBlank: true,
					anchor: '100%',
					fieldLabel: lang['region']
				},
				{
					xtype: 'textfield',
					name: 'group',
					allowBlank: true,
					anchor: '100%',
					fieldLabel: lang['gruppa']
				},
				{
					xtype: 'textfield',
					name: 'title',
					allowBlank: false,
					anchor: '100%',
					fieldLabel: lang['nazvanie_obyekta']
				},
				{
					xtype: 'textfield',
					name: 'table',
					allowBlank: true,
					anchor: '100%',
					fieldLabel: lang['nazvanie_tablitsyi']
				},
				{
					xtype: 'textfield',
					name: 'desc',
					allowBlank: true,
					anchor: '100%',
					fieldLabel: lang['opisanie']
				},
				{
					xtype: 'textfield',
					name: 'path',
					allowBlank: false,
					anchor: '100%',
					fieldLabel: lang['put']
				},
				{
					xtype: 'checkbox',
					boxLabel: lang['vsegda_dostupen'],
					hideLabel: true,
					name: 'available',
					allowBlank: true
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
				{ name: 'id' },
				{ name: 'code' },
				{ name: 'group' },
				{ name: 'region' },
				{ name: 'title' },
				{ name: 'table' },
				{ name: 'desc' },
				{ name: 'path' },
				{ name: 'available' }
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
		sw.Promed.swWndEditWindow.superclass.initComponent.apply(this, arguments);
	}
});