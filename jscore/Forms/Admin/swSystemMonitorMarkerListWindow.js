/**
 * swSystemMonitorMarkerListWindow - окно просмотра списка запросов для мониторинга системы
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2014 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			15.05.2014
 */

/*NO PARSE JSON*/

sw.Promed.swSystemMonitorMarkerListWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swSystemMonitorMarkerListWindow',
	width: 800,
	height: 450,
	maximizable: true,
	//maximized: true,
	modal: true,
	layout: 'border',
	title: lang['monitoring_sistemyi_markeryi'],
	callback: Ext.emptyFn,

	show: function() {
		sw.Promed.swSystemMonitorMarkerListWindow.superclass.show.apply(this, arguments);

		if (arguments[0] && arguments[0].MarkerListData) {
			this.MarkerListData = arguments[0].MarkerListData;
		}

		var grid = this.GridPanel.getGrid();

		grid.removeAll();
		grid.getStore().loadData(this.MarkerListData);
	},

	initComponent: function() {
		this.GridPanel = new sw.Promed.ViewFrame({
			id: 'SMMLW_QueryGrid',
			region: 'center',
			paging: false,
			autoLoadData: false,
			toolbar: false,
			stringfields:
				[
					{name: 'id', type: 'int', header: 'ID', key: true},
					{name: 'Marker', header: lang['marker'], width: 240, renderer: function(val){return '{'+val+'}';} },
					{name: 'Descr', type: 'string', header: lang['opisanie'], id: 'autoexpand'}
					,{name: 'Value', type: 'string', header: lang['znachenie'], width: 120}
				]
		});

		Ext.apply(this, {
			items: [
				this.GridPanel
			],
			buttons: [
				{
					text: '-'
				},
				HelpButton(this, 1),
				{
					handler: function () {
						this.hide();
					}.createDelegate(this),
					iconCls: 'close16',
					id: 'SMMLW_CancelButton',
					text: lang['zakryit']
				}]
		});

		sw.Promed.swSystemMonitorMarkerListWindow.superclass.initComponent.apply(this, arguments);
	}
});