/**
 * swRegionStreetListWindow - форма просмотра адресов обслуживаемых участков
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/projects/promed
 *
 * @package      Reg
 * @access       public
 * @copyright    Copyright (c) 2009-2014, Swan.
 * @author       Petukhov Ivan aka Lich (ethereallich@gmail.com)
 * @prefix       RSW
 * @tabindex     TABINDEX_RSW
 * @version      19.02.2014
 */
 
/*NO PARSE JSON*/

sw.Promed.swRegionStreetListWindow = Ext.extend(sw.Promed.BaseForm,
{
	codeRefresh: true,
	objectName: 'swRegionStreetListWindow',
	objectSrc: '/jscore/Forms/Reg/swRegionStreetListWindow.js',
	
	closable: true,
	closeAction: 'hide',
	maximized: true,
	title: WND_RSW,
	iconCls: 'lpu-region16',
	id: 'swRegionStreetListWindow',

	onHide: Ext.emptyFn,
	listeners: 
	{
		hide: function()
		{
			this.onHide();
		}
	},
	
	show: function()
	{
		if (arguments[0]['LpuRegion_id']) {
			this.LpuRegion_id = arguments[0]['LpuRegion_id'];
			
			this.RegionStreetGridPanel.loadData({params:{LpuRegion_id: this.LpuRegion_id}, globalFilters: {LpuRegion_id: this.LpuRegion_id}});
		} else {
			alert(lang['ne_opredelen_uchastok']);
			this.hide();
		}
		
		sw.Promed.swRegionStreetListWindow.superclass.show.apply(this, arguments);
	},

	initComponent: function()
	{
		this.RegionStreetGridPanel = new sw.Promed.ViewFrame(
		{
			id: 'rswRegionStreetGridPanel',
			object: 'LpuRegionStreet',
			dataUrl: C_LPUREGIONSTREET_GET,
			toolbar: true,
			stringfields:
			[
				{name: 'LpuRegionStreet_id', type: 'int', header: 'ID', key: true},
				{name: 'KLTown_Name', type: 'string', header: lang['naselennyiy_punkt'], width: 200},
				{name: 'KLStreet_Name', type: 'string', header: lang['ulitsa'], width: 200},
				{id: 'autoexpand',name: 'LpuRegionStreet_HouseSet', type: 'string', header: lang['nomera_domov']}
			],
			actions:
			[
				{name:'action_add', hidden: true},
				{name:'action_edit', hidden: true},
				{name:'action_view', hidden: true},
				{name:'action_delete', hidden: true},
				{name:'action_refresh'},
				{name:'action_print'}
			]
		});
		
		Ext.apply(this, 
		{
			layout: 'border',
			items: 
			[
				this.RegionStreetGridPanel,
			],
			buttons: 
			[
			{
				text: '-'
			}, 
			{
				text: BTN_FRMHELP,
				iconCls: 'help16',
				handler: function(button, event) {
					ShowHelp(WND_RSW);
				}.createDelegate(this)
			},
			{
				iconCls: 'cancel16',
				text: BTN_FRMCLOSE,
				handler: function() { this.hide(); }.createDelegate(this)
			}]
		});
		
		sw.Promed.swRegionStreetListWindow.superclass.initComponent.apply(this, arguments);
	}
});