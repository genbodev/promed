/**
 * swSystemMonitorQueryListWindow - окно просмотра списка запросов для мониторинга системы
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2014 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			03.04.2014
 */

/*NO PARSE JSON*/

sw.Promed.swSystemMonitorQueryListWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swSystemMonitorQueryListWindow',
	width: 800,
	height: 450,
	maximizable: true,
	maximized: true,
	layout: 'border',
	title: lang['monitoring_sistemyi_zaprosyi'],
	callback: Ext.emptyFn,

	listeners: {
		hide: function () {
			this.callback();
		}
	},

	openQueryEditWindow: function(action) {
		var grid = this.GridPanel.getGrid();
		var params = new Object();

		params.action = action;

		if(action.inlist(['edit','view'])) {
			var record = grid.getSelectionModel().getSelected();
			params.formParams = {
				SystemMonitorQuery_id: record.get('SystemMonitorQuery_id')
			}
		}

		params.callback = function() {
			this.GridPanel.getAction('action_refresh').execute();
		}.createDelegate(this);

		getWnd('swSystemMonitorQueryEditWindow').show(params);
	},

	deleteSystemMonitorQuery: function() {
		var form = this;

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( buttonId == 'yes' ) {
					var grid = form.GridPanel.getGrid();
					var record = grid.getSelectionModel().getSelected();

					if (!record || !record.get('SystemMonitorQuery_id')) {
						return false;
					}

					var params = {
						SystemMonitorQuery_id: record.get('SystemMonitorQuery_id')
					};

					Ext.Ajax.request({
						callback: function(opt, scs, response) {
							form.GridPanel.getAction('action_refresh').execute();
						}.createDelegate(this),
						params: params,
						url: '/?c=SystemMonitor&m=deleteSystemMonitorQuery'
					});
				}
			}.createDelegate(this),
			icon: Ext.MessageBox.QUESTION,
			msg: lang['vyi_deystvitelno_hotite_udalit_zapros'],
			title: lang['vopros']
		});
	},

	show: function() {
		sw.Promed.swSystemMonitorQueryListWindow.superclass.show.apply(this, arguments);

		if (arguments[0].callback) {
			this.callback = arguments[0].callback;
		}

		var grid = this.GridPanel.getGrid();

		grid.removeAll();
		grid.getStore().load();
	},

	initComponent: function() {
		this.GridPanel = new sw.Promed.ViewFrame({
			actions: [
				{name: 'action_add', handler: function(){this.openQueryEditWindow('add');}.createDelegate(this)},
				{name: 'action_edit', handler: function(){this.openQueryEditWindow('edit');}.createDelegate(this)},
				{name: 'action_view', handler: function(){this.openQueryEditWindow('view');}.createDelegate(this)},
				{name: 'action_delete', handler: function(){this.deleteSystemMonitorQuery()}.createDelegate(this)}
			],
			//editformclassname: 'swSystemMonitorQueryEditWindow',
			id: 'SMQLW_QueryGrid',
			region: 'center',
			dataUrl: '/?c=SystemMonitor&m=loadSystemMonitorQueryList',
			object: 'SystemMonitorQuery',
			paging: false,
			autoLoadData: false,
			root: 'data',
			stringfields:
				[
					{name: 'SystemMonitorQuery_id', type: 'int', header: 'ID', key: true},
					{name: 'SystemMonitorQuery_Name', type: 'string', header: lang['nazvanie'], id: 'autoexpand'},
					{name: 'SystemMonitorQuery_RepeatCount', type: 'int', header: lang['kolichestvo_vyipolneniy_podryad'], width: 240},
					{name: 'SystemMonitorQuery_TimeLimit', type: 'float', header: lang['prevyishenie_sek'], width: 240}
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
				id: 'SMQLW_CancelButton',
				text: lang['zakryit']
			}]
		});

		sw.Promed.swSystemMonitorQueryListWindow.superclass.initComponent.apply(this, arguments);
	}
});