/**
* swRegistryDataReceptListWindow - список рецептов в реестре
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      RegistryRecept
* @access       public
* @copyright    Copyright (c) 2012 Swan Ltd.
* @author       Dmitry Vlasenko
* @version      20.12.2012
* @comment      
*/
/*NO PARSE JSON*/
sw.Promed.swRegistryDataReceptListWindow = Ext.extend(sw.Promed.BaseForm,
{
	maximizable: true,
	maximized: true,
	height: 500,
	width: 800,
	id: 'RegistryDataReceptListWindow',
	title: WND_REGISTRYRECEPT_DATALIST, 
	layout: 'border',
	resizable: true,
	onSelectionChange: function(sm,index,record) {
		var tbar = this.mainToolBar;
		if (sm.getCount() == 1) {
			// просмотр
		} else {
		}
	},
	doFilter: function() {
		var filters = this.filtersPanel.getForm().getValues();
		filters.start = 0;
		filters.limit = 100;
		
		filters.RegistryRecept_id = this.RegistryRecept_id || null;
		
		this.RegistryDataReceptGrid.loadData({ globalFilters: filters });
	},
	doResetFilters: function() {
		this.filtersPanel.getForm().reset();
	},
	initComponent: function() 
	{
		var form = this;
		
		this.RegistryDataReceptGrid = new sw.Promed.ViewFrame(
		{
			id: form.id+'RegistryDataReceptGrid',
			title:'',
			object: 'Registry',
			dataUrl: '/?c=RegistryRecept&m=loadRegistryDataReceptList',
			autoLoadData: false,
			paging: true,
			root: 'data',
			region: 'center',
			toolbar: false,
			totalProperty: 'totalCount',
			stringfields:
			[
				{name: 'RegistryDataRecept_id', type: 'int', header: 'RegistryDataRecept_id', key: true, hidden:!isSuperAdmin()},
				{name: 'RegistryDataRecept_Snils', header: lang['snils'], width: 120, id: 'autoexpand'},
				{name: 'RegistryDataRecept_SurName', header: lang['familiya'], width: 100},
				{name: 'RegistryDataRecept_FirName', header: lang['imya'], width: 80},
				{name: 'RegistryDataRecept_SecName', header: lang['otchestvo'], width: 80}
			],
			onDblClick: function() {
				//
			},
			onRowSelect: function(sm,index,record)
			{
				form.onSelectionChange(sm,index,record);
			},
			onRowDeSelect: function(sm,index,record)
			{
				form.onSelectionChange(sm,index,record);
			},
			onLoadData: function() {
				sm = this.getGrid().getSelectionModel();
				form.onSelectionChange(sm);
			},
			actions:
			[
				{name:'action_add', disabled: true, hidden: true},
				{name:'action_edit', disabled: true, hidden: true},
				{name:'action_print', disabled: true, hidden: true},
				{name:'action_view', disabled: true, hidden: true},
				{name:'action_delete', disabled: true, hidden: true}
			]
		});
		
		this.mainToolBar = new Ext.Toolbar({
			items: [
				{text: lang['obnovit'], iconCls: 'refresh16', handler: function() { form.RegistryDataReceptGrid.getGrid().getStore().reload(); }}
			]
		});
		
		this.filtersPanel = new Ext.FormPanel(
		{
			xtype: 'form',
			region: 'north',
			labelAlign: 'right',
			layout: 'form',
			autoHeight: true,
			labelWidth: 50,
			frame: true,
			border: false,
			keys: 
			[{
				key: Ext.EventObject.ENTER,
				fn: function(e) 
				{
					form.doFilter();
				},
				stopEvent: true
			}],
			items: [{
				listeners: {
					collapse: function(p) {
						form.doLayout();
					},
					expand: function(p) {
						form.doLayout();
					}
				},
				xtype: 'fieldset',
				style: 'margin: 5px 5px 5px 5px',
				title: lang['filtryi'],
				collapsible: true,
				autoHeight: true,
				labelWidth: 100,
				anchor: '-10',
				layout: 'form',
				items:
				[{
					name: 'RegistryDataRecept_Snils',
					fieldLabel: lang['snils'],
					tabindex: TABINDEX_RDRL + 0,
					xtype: 'textfield'
				}],
				buttons: [
					{
						text: BTN_FILTER,
						tabIndex: TABINDEX_RDRL + 8,
						handler: function() {
							form.doFilter();
						},
						iconCls: 'search16'
					}, 
					{
						text: BTN_RESETFILTER,
						tabIndex: TABINDEX_RDRL + 9,
						handler: function() {
							form.doResetFilters();
							form.doFilter();
						},
						iconCls: 'resetsearch16'
					}
				]
			}]
		});
		
		this.formPanel = new Ext.Panel(
		{
			region: 'center',
			labelAlign: 'right',
			layout: 'border',
			labelWidth: 50,
			border: false,
			tbar: this.mainToolBar,
			items:
			[
				this.filtersPanel,
				this.RegistryDataReceptGrid
			]
		});
		
		Ext.apply(this, 
		{
			items: 
			[ 
				form.formPanel
			],
			buttons:
			[{
				text: '-'
			},
			HelpButton(this, TABINDEX_RDRL + 11),
			{
				iconCls: 'close16',
				tabIndex: TABINDEX_RDRL + 12,
				onTabAction: function()
				{
					form.filtersPanel.getForm().findField('RegistryDataRecept_Snils').focus();
				},
				handler: function() {
					form.hide();
				},
				text: BTN_FRMCLOSE
			}]
		});
		sw.Promed.swRegistryDataReceptListWindow.superclass.initComponent.apply(this, arguments);
	},
	show: function() {
		sw.Promed.swRegistryDataReceptListWindow.superclass.show.apply(this, arguments);
		
		if ( !arguments[0] || !arguments[0].RegistryRecept_id ) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() { this.hide(); }.createDelegate(this) );
			return false;
		}
		
		if (arguments[0].title) {
			this.setTitle(WND_REGISTRYRECEPT_DATALIST + '. ' + arguments[0].title);
		} else {
			this.setTitle(WND_REGISTRYRECEPT_DATALIST);
		}
		
		this.RegistryRecept_id = arguments[0].RegistryRecept_id;
		
		this.doResetFilters();
		this.doFilter();
	}
});