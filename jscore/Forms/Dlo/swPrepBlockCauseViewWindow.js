/**
 * swPrepBlockCauseViewWindow - окно справчника причин блокировки оборота серий выпуска ЛС
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      	Dlo
 * @access       	public
 * @copyright		Copyright (c) 2014 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			20.03.2015
 */
/*NO PARSE JSON*/

sw.Promed.swPrepBlockCauseViewWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swPrepBlockCauseViewWindow',
	layout: 'border',
	title: lang['prichinyi_blokirovki_oborota_seriy_vyipuska_ls'],
	maximizable: true,
	maximized: false,

	openPrepBlockCauseEditWindow: function(action) {
		if (!action.inlist(['add','edit','view'])) {
			return false;
		}

		var wnd = this;
		var grid = this.GridPanel.getGrid();

		var params = {};
		params.action = action;
		params.formParams = {};

		if (action != 'add') {
			params.formParams.PrepBlock_id = grid.getSelectionModel().getSelected().get('PrepBlock_id');
		}

		params.callback = function() {
			wnd.GridPanel.getAction('action_refresh').execute();
		};

		getWnd('swPrepBlockCauseEditWindow').show(params);
		return true;
	},

	deletePrepBlockCause: function() {
		var grid = this.GridPanel.getGrid();

		var record = grid.getSelectionModel().getSelected();

		if (!record || !record.get('PrepBlockCause_id')) {
			return false;
		}

		sw.swMsg.show({
			buttons:Ext.Msg.YESNO,
			fn:function (buttonId, text, obj) {
				if (buttonId == 'yes') {
					var params = {PrepBlockCause_id: record.get('PrepBlockCause_id')};

					Ext.Ajax.request({
						callback: function(opt, scs, response) {
							var response_obj = Ext.util.JSON.decode(response.responseText);

							if (!response_obj.Error_Msg) {
								this.GridPanel.getAction('action_refresh').execute();
							}
						}.createDelegate(this),
						params: params,
						url: '/?c=RlsDrug&m=deletePrepBlockCause'
					});
				}
			}.createDelegate(this),
			icon:Ext.MessageBox.QUESTION,
			msg:lang['vyi_hotite_udalit_zapis'],
			title:lang['podtverjdenie']
		});
	},

	show: function() {
		sw.Promed.swPrepBlockCauseViewWindow.superclass.show.apply(this, arguments);

		var grid = this.GridPanel.getGrid();
		this.GridPanel.removeAll();

		grid.getStore().load();
	},

	initComponent: function() {

		this.GridPanel = new sw.Promed.ViewFrame({
			id: 'PBCVW_PrepBlockCauseGrid',
			dataUrl: '/?c=RlsDrug&m=loadPrepBlockCauseGrid',
			autoLoadData: false,
			paging: true,
			pageSize: 100,
			root: 'data',
			stringfields: [
				{name: 'PrepBlockCause_id', type: 'int', header: 'ID', key: true},
				{name: 'PrepBlockCause_Code', header: lang['kod'], type: 'string', width: 140},
				{name: 'PrepBlockCause_Name', header: lang['naimenovanie'], type: 'string', id: 'autoexpand'}
			],
			actions: [
				{name:'action_add', handler: function(){this.openPrepBlockCauseEditWindow('add');}.createDelegate(this)},
				{name:'action_edit', disabled: true, hidden: true},
				{name:'action_view', disabled: true, hidden: true},
				{name:'action_delete', handler: function(){this.deletePrepBlockCause();}.createDelegate(this)},
				{name:'action_refresh', disabled: true, hidden: true},
				{name:'action_print', disabled: true, hidden: true}
			]
		});

		Ext.apply(this,
		{
			buttons:
			[{
				text: '-'
			},
			HelpButton(this),
			{
				handler: function()
				{
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				text: BTN_FRMCLOSE
			}],
			items: [this.GridPanel]
		});

		sw.Promed.swPrepBlockCauseViewWindow.superclass.initComponent.apply(this, arguments);
	}
});