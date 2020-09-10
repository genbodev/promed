/**
* swUserSearchWindow - окно поиска пользователей и добавления в адр. книгу.
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

sw.Promed.swUserSearchWindow = Ext.extend(sw.Promed.BaseForm, {
	buttonAlign: 'right',
	modal: true,
	closable: true,
	shim: false,
	height: 500,
	width: 800,
	onHide: Ext.emptyFn,
	closeAction: 'hide',
	id: 'swUserSearchWindow',
	objectName: 'swUserSearchWindow',
	title: lang['poisk_polzovatelya'],
	plain: true,
	buttons: [
		{
			handler: function()
			{
				this.ownerCt.doSearch();
			},
			iconCls: 'search16',
			//tabIndex: TABINDEX_EVNVK + 34,
			text: BTN_FRMSEARCH
		},
		{
			handler: function()
			{
				this.ownerCt.doReset();
			},
			iconCls: 'resetsearch16',
			//tabIndex: TABINDEX_EVNVK + 34,
			text: BTN_FRMRESET
		},
		{
			handler: function()
			{
				this.ownerCt.onSelect();
			},
			iconCls: 'ok16',
			//tabIndex: TABINDEX_EVNVK + 34,
			text: lang['vyibrat']
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
			this.doReset();
			this.buttons[2].setHandler(function(){this.onSelect();}.createDelegate(this));
			this.onHide();
		}
	},
	
	show: function()
	{
		sw.Promed.swUserSearchWindow.superclass.show.apply(this, arguments);
		
		var frm = this;
		if(arguments[0].onHide)
		{
			frm.onHide = arguments[0].onHide;
		}
		if(arguments[0].selectUser)
		{
			frm.selectUser = arguments[0].selectUser;
		}
		var form = frm.searchPanel.getForm();
		form.reset();
		
		form.findField('pmUser_surName').focus(true, 300);
	},
	/** Функция, которая приходит с клиента 
	 *
	 */
	selectUser: function(userData){},
	doSearch: function()
	{
		var frm = this;
		var base_form = frm.searchPanel.getForm();
		var grid = frm.searchGrid.getGrid();
		grid.getStore().baseParams = base_form.getValues();
		grid.getStore().load();
	},
	
	doReset: function()
	{
		this.searchPanel.getForm().reset();
		this.searchGrid.getGrid().getStore().removeAll();
	},
	
	onSelect: function()
	{
		var grid = this.searchGrid.getGrid();
		var record = grid.getSelectionModel().getSelected();
		if(!record)
		{
			sw.swMsg.alert(lang['oshibka'], lang['ne_vyibran_polzovatel']);
			return false;
		}
		
		this.selectUser(record.data);
		this.hide();
	},
	
	openUserProfile: function()
	{
		var record = this.searchGrid.getGrid().getSelectionModel().getSelected();
		if(!record)
		{	
			sw.swMsg.alert(lang['oshibka'], lang['ne_vyibran_polzovatel']);
			return false;
		}
		args = {}
		args.pmUser_Login = record.get('pmUser_Login');
		args.Lpu_Nick = record.get('Lpu_Nick');
		args.showType = 'strangerprofile';
		getWnd('swUserProfileEditWindow').show(args);
	},
	
	initComponent: function() 
	{
		var win = this;
		this.searchPanel = new Ext.form.FormPanel({
			region: 'north',
			id: 'swUserSearchPanel',
			//bodyStyle: 'padding: 3px;',
			labelAlign: 'top',
			frame: true,
			autoHeight: true,
			items: [{
				xtype: 'fieldset',
				collapsible: true, // :) 
				style:'padding: 0px 3px 0px 6px;',
				autoHeight: true,
				title: lang['f_i_o'],
				labelAlign: 'top',
				listeners: {
					expand: function() {
						win.syncSize();
					},
					collapse: function() {
						win.syncSize();
					}
				},
				items: [{
					layout: 'column',
					defaults: 
					{
						border: false
					},
					border: false,
					items: [{
						layout: 'form',
						columnWidth: .33,
						items: [{
								xtype: 'textfieldpmw',
								anchor: '100%',
								name: 'pmUser_surName',
								fieldLabel: lang['familiya']
							}
						]
					},
					{
						layout: 'form',
						columnWidth: .33,
						style: 'margin-left: 10px;',
						items: [
							{
								xtype: 'textfieldpmw',
								anchor: '100%',
								name: 'pmUser_firName',
								fieldLabel: lang['imya']
							}
						]
					},{
						layout: 'form',
						columnWidth: .33,
						style: 'margin-left: 10px;',
						items: [
							{
								xtype: 'textfieldpmw',
								name: 'pmUser_secName',
								anchor: '100%',
								fieldLabel: lang['otchestvo']
							}
						]
					}]
				}]
			}, {
				xtype: 'fieldset',
				labelAlign: 'top',
				autoHeight: true,
				style:'padding: 0px 3px 0px 6px;',
				collapsible: true, // :) 
				title: lang['mesto_rabotyi'],
				listeners: {
					expand: function() {
						win.syncSize();
					},
					collapse: function() {
						win.syncSize();
					}
				},
				items: [{
					layout: 'column',
					defaults: 
					{
						border: false
					},
					border: false,
					items: [{
						layout: 'form',
						columnWidth: .33,
						items: [
							{
								xtype: 'textfieldpmw',
								anchor: '100%',
								name: 'Lpu_Nick',
								fieldLabel: lang['lpu']
							}
						]
					},
					{
						layout: 'form',
						columnWidth: .33,
						style: 'margin-left: 10px;',
						items: [
							{
								xtype: 'textfieldpmw',
								name: 'LpuSection_Name',
								anchor: '100%',
								fieldLabel: lang['otdelenie']
							}
						]
					},{
						layout: 'form',
						columnWidth: .33,
						style: 'margin-left: 10px;',
						items: [
							{
								xtype: 'textfieldpmw',
								name: 'MedSpec_Name',
								anchor: '100%',
								fieldLabel: lang['doljnost']
							}
						]
					}]
				}]
			}],
			keys: [{
				key: Ext.EventObject.ENTER,
				fn: function(e) {
					win.doSearch();
				},
				stopEvent: true
			}]
		});
		
		this.searchGrid = new sw.Promed.ViewFrame({
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 100,
			anchor: '100%',
			id: 'UserSearchGrid',
			region: 'center',
			pageSize: 20,
			editformclassname: 'swUserProfileEditWindow',
			tbar: false,
			autoLoadData: false,
			actions: [
				{ name: 'action_add', hidden: true, disabled: true }, // TODO: Возможно здесь надо будет прикрутить форму создания пользователя (для суперадмина)
				{ name: 'action_edit', hidden: true, handler: function(){this.onSelect();}.createDelegate(this) },
				{ name: 'action_view', hidden: false }, // , handler: function(){this.openUserProfile();}.createDelegate(this)
				{ name: 'action_delete', hidden: true },
				{ name: 'action_refresh', hidden: true},
				{ name: 'action_print', hidden: true }
			],
			stripeRows: true,
			root: 'data',
			stringfields: [
				{ name: 'pmUser_id', hidden: true, key: true },
				{ name: 'pmUser_Login', hidden: !isAdmin, header: lang['login'], width: 80 },
				{ name: 'pmUser_surName', type: 'string', header: lang['familiya'], id: 'autoexpand' },
				{ name: 'pmUser_firName', type: 'string', header: lang['imya'], width: 100 },
				{ name: 'pmUser_secName', type: 'string', header: lang['otchestvo'], width: 100 },
				{ name: 'Lpu_Nick', type: 'string', header: lang['lpu'], width: 150 },
				{ name: 'LpuSection_Name', type: 'string', header: lang['otdelenie'], width: 150 },
				{ name: 'MedSpec_Name', type: 'string', header: lang['doljnost'], width: 150 },
				{ name: 'medpersonal_id', hidden: true}
			],
			paging: true,
			dataUrl: '/?c=Messages&m=loadUserSearchGrid',
			totalProperty: 'totalCount'
		});
		
		Ext.apply(this, 
		{
			layout: 'border',
			defaults:
			{
				border: false
				//bodyStyle: 'padding: 3px; background: #DFE8F6;'
			},
			items: [this.searchPanel, this.searchGrid]
		});
		sw.Promed.swUserSearchWindow.superclass.initComponent.apply(this, arguments);
	}
});