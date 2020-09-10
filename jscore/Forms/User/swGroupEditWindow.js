/**
* swGroupEditWindow - окно добавления/редактирования групп пользователей в адресной книге.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Messages
* @access       public
* @copyright    Copyright (c) 2009-2010 Swan Ltd.
* @author       Марков Андрей <markov@swan.perm.ru>
* @version      дек.2011
*
*/

sw.Promed.swGroupEditWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: true,
	buttonAlign: 'right',
	modal: true,
	layout: 'form',
	resizable: false,
	closable: true,
	shim: false,
	width: 500,
	closeAction: 'hide',
	id: 'swGroupEditWindow',
	objectName: 'swGroupEditWindow',
	title: lang['gruppa'],
	plain: true,
	buttons: [
		{
			handler: function()
			{
				this.ownerCt.doSave();
			},
			iconCls: 'save16',
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
		sw.Promed.swGroupEditWindow.superclass.show.apply(this, arguments);
		
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
		if (arguments[0].owner) 
			this.owner = arguments[0].owner;
		if (this.action=='edit') {
			bf.setValues(arguments[0]);
		}
		
		var combo = bf.findField('GroupType'),
			data = [];
		if (arguments[0].Group_Code == 'SuperAdmin' || isSuperAdmin()) {
			data.push([lang['super-administrator']]);
			data.push([lang['administrator']]);
		} else if (arguments[0].Group_Code == 'LpuAdmin' || isLpuAdmin()) {
			data.push([lang['administrator']]);
		}
		data.push([lang['polzovatel']]);
		combo.getStore().loadData(data, false);
		
		bf.findField('Group_IsBlocked').setDisabled(arguments[0].Group_Code == 'SuperAdmin' || getRegionNick() != 'yaroslavl');
		
		
		switch ( this.action ) {
			case 'add':
				this.setTitle(lang['gruppa_dobavlenie']);
				bf.findField('Group_Code').setDisabled(false);
				bf.findField('Group_Code').focus(true, 100);
				break;
			case 'edit':
				this.setTitle(lang['gruppa_redaktirovanie']);
				bf.findField('Group_Code').setDisabled(true);
				bf.findField('Group_Name').focus(true, 100);
				break;
		}
	},
	
	doSave: function()
	{
		var win = this;
		var form = this.CenterPanel.getForm();
		if(!form.isValid()) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_zapolneno_obyazatelnoe_pole']);
			return false;
		}
		var params = {Group_Code: form.findField('Group_Code').getValue()};
		form.submit({
			params: params,
			success: function() {
				win.hide();
				win.callback(win.owner, 1);
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
		this.UserGrid = new sw.Promed.ViewFrame({
			autoLoadData: false,
			//paging: true,
			title: lang['spisok_akkauntov'],
			dataUrl: '/?c',
			actions: [
				{ name: 'action_add' },
				{ name: 'action_edit' },
				{ name: 'action_view' },
				{ name: 'action_delete' },
				{ name: 'action_refresh' },
				{ name: 'action_print', hidden: true }
			],
			stringfields: [
				{ name: 'd', type: 'int', hidden: true, key: true },
				{ name: 'd', header: lang['s_kakogo_chisla'], type: 'string' },
				{ name: 'd', header: lang['po_kakoe_chislo'], type: 'string' },
				{ name: 'd', header: lang['login'], type: 'string' },
				{ name: 'd', header: lang['fio'], type: 'string' }
			]
		});
		
		this.CenterPanel = new Ext.form.FormPanel({
			frame: true,
			border: false,
			labelAlign: 'top',
			url: '/?c=User&m=saveGroup',
			items: [
				{
					xtype: 'hidden',
					name: 'pmUserCacheGroup_id'
				}, {
					xtype: 'hidden',
					name: 'Group_id'
				}, {
					xtype: 'hidden',
					name: 'dn'
				}, {
					xtype: 'textfield',
					name: 'Group_Code',
					allowBlank: false,
					anchor: '100%',
					fieldLabel: lang['kod']
				}, {
					xtype: 'combo',
					mode: 'local',
					triggerAction: 'all',
					store: new Ext.data.Store({
						reader: new Ext.data.ArrayReader({
							idIndex: 0
						}, [
							{mapping: 0, name: 'GroupType'}
						])
					}),
					hiddenName: 'GroupType',
					valueField: 'GroupType',
					displayField: 'GroupType',
					allowBlank: true,
					anchor: '100%',
					fieldLabel: lang['tip']
				}, {
					xtype: 'textfield',
					name: 'Group_Name',
					allowBlank: false,
					anchor: '100%',
					fieldLabel: lang['nazvanie_gruppyi']
				}, {
					xtype: 'numberfield',
					allowNegative: false,
					allowDecimal: false,
					name: 'Group_ParallelSessions',
					allowBlank: true,
					anchor: '100%',
					fieldLabel: langs('Количество параллельных сеансов одного пользователя'),
					hidden: getRegionNick() == 'kz'
				}, {
					xtype : 'checkbox',
					name : 'Group_IsBlocked',
					hideLabel: true,
					boxLabel : "Группа заблокирована"
				}, {
					xtype : 'checkbox',
					name : 'Group_IsOnly',
					hideLabel: true,
					boxLabel : "Единственность"
				}
				/*
				, {
					xtype: 'fieldset',
					layout: 'column',
					autoHeight: true,
					title: lang['upravlenie_gruppami'],
					items: [
						{
							xtype: 'checkbox',
							columnWidth: .3,
							boxLabel: lang['dobavlenie']
						}, {
							xtype: 'checkbox',
							columnWidth: .3,
							boxLabel: lang['izmenenie']
						}, {
							xtype: 'checkbox',
							boxLabel: lang['udalenie']
						}
					]
				}, {
					xtype: 'fieldset',
					layout: 'column',
					autoHeight: true,
					defaults: {
						style: 'font-color: red'
					},
					title: lang['upravlenie_akkauntami'],
					items: [
						{
							xtype: 'checkbox',
							columnWidth: .3,
							boxLabel: lang['dobavlenie']
						}, {
							xtype: 'checkbox',
							columnWidth: .3,
							boxLabel: lang['izmenenie']
						}, {
							xtype: 'checkbox',
							boxLabel: lang['udalenie']
						}
					]
				},
				this.UserGrid
				*/
				
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
				{ name: 'pmUserCacheGroup_id' },
				{ name: 'Group_id' },
				{ name: 'Group_Code' },
				{ name: 'Group_Name' },
				{ name: 'dn' },
				{ name: 'Group_Type' },
				{ name: 'Group_ParallelSessions' }
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
		sw.Promed.swGroupEditWindow.superclass.initComponent.apply(this, arguments);
	}
});