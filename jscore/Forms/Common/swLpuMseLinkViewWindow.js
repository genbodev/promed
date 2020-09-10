/**
 * swLpuMseLinkViewWindow - окно просмотра справочника связи бюро МСЭ с МО
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      	Common
 * @access       	public
 * @copyright		Copyright (c) 2017 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			07.2017
 */
/*NO PARSE JSON*/

sw.Promed.swLpuMseLinkViewWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swLpuMseLinkViewWindow',
	layout: 'border',
	title: 'Справочник связи МО с бюро МСЭ',
	maximizable: false,
	maximized: true,

	doSearch: function(reset) {
		var base_form = this.FilterPanel.getForm();
		var grid = this.GridPanel.getGrid();

		if (reset) {
			base_form.reset();
		}

		var params = base_form.getValues();
		params.start = 0;
		params.limit = 100;

		grid.getStore().load({params: params});
	},

	openLpuMseLinkEditWindow: function(action) {
		if (!action.inlist(['add','edit','view'])) {
			return false;
		}

		var grid = this.GridPanel.getGrid();

		var params = {};
		params.ARMType = this.ARMType;
		params.action = action;
		params.formParams = {};

		if (action != 'add') {
			params.formParams.LpuMseLink_id = grid.getSelectionModel().getSelected().get('LpuMseLink_id');
		}

		params.callback = function() {
			this.GridPanel.getAction('action_refresh').execute();
		}.createDelegate(this);

		getWnd('swLpuMseLinkEditWindow').show(params);
		return true;
	},

	deleteLpuMseLink: function() {
		var grid = this.GridPanel.getGrid();

		var record = grid.getSelectionModel().getSelected();

		if (!record || !record.get('LpuMseLink_id')) {
			return false;
		}

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function (buttonId, text, obj) {
				if (buttonId == 'yes') {
					var params = {LpuMseLink_id: record.get('LpuMseLink_id')};

					Ext.Ajax.request({
						callback: function(opt, scs, response) {
							var response_obj = Ext.util.JSON.decode(response.responseText);

							if (!response_obj.Error_Msg) {
								this.GridPanel.getAction('action_refresh').execute();
							}
						}.createDelegate(this),
						params: params,
						url: '/?c=LpuStructure&m=deleteLpuMseLink'
					});
				}
			}.createDelegate(this),
			icon: Ext.MessageBox.QUESTION,
			msg: lang['vyi_hotite_udalit_zapis'],
			title: lang['podtverjdenie']
		});
	},

	addCloseFilterMenu: function(grid_panel){
		var form = this;

		if ( !grid_panel.getAction('action_isclosefilter_'+grid_panel.id) ) {
			var menuIsCloseFilter = new Ext.menu.Menu({
				items: [
					new Ext.Action({
						text: lang['otkryityie'],
						handler: function() {
							if (grid_panel.gFilters) {
								grid_panel.gFilters.isClose = 1;
							}
							grid_panel.getAction('action_isclosefilter_'+grid_panel.id).setText(lang['pokazyivat_otkryityie']);
							grid_panel.getGrid().getStore().baseParams.isClose = 1;
							grid_panel.getGrid().getStore().reload();
						}
					}),
					new Ext.Action({
						text: lang['zakryityie'],
						handler: function() {
							if (grid_panel.gFilters) {
								grid_panel.gFilters.isClose = 2;
							}
							grid_panel.getAction('action_isclosefilter_'+grid_panel.id).setText(lang['pokazyivat_zakryityie']);
							grid_panel.getGrid().getStore().baseParams.isClose = 2;
							grid_panel.getGrid().getStore().reload();
						}
					}),
					new Ext.Action({
						text: lang['vse'],
						handler: function() {
							if (grid_panel.gFilters) {
								grid_panel.gFilters.isClose = null;
							}
							grid_panel.getAction('action_isclosefilter_'+grid_panel.id).setText(lang['pokazyivat_vse']);
							grid_panel.getGrid().getStore().baseParams.isClose = null;
							grid_panel.getGrid().getStore().reload();
						}
					})
				]
			});

			grid_panel.addActions({
				isClose: 1,
				name: 'action_isclosefilter_'+grid_panel.id,
				text: lang['pokazyivat_otkryityie'],
				menu: menuIsCloseFilter
			});
			grid_panel.getGrid().getStore().baseParams.isClose = 1;
		}

		return true;
	},

	show: function() {
		sw.Promed.swLpuMseLinkViewWindow.superclass.show.apply(this, arguments);

		this.ARMType = null;

		if (arguments[0] && arguments[0].ARMType) {
			this.ARMType = arguments[0].ARMType;
		}

		var base_form = this.FilterPanel.getForm();

		base_form.findField('Lpu_bid').setBaseFilter(function(rec) {
			return rec.get('Lpu_IsMse') == 2;
		});
		base_form.findField('Lpu_oid').setBaseFilter(function(rec) {
			return rec.get('Lpu_IsMse') == 1;
		});
		base_form.findField('MedService_id').getStore().load();
		this.doSearch(true);
	},

	initComponent: function() {
		this.FilterPanel = new Ext.FormPanel({
			frame: true,
			id: 'LMLVW_FilterPanel',
			region: 'north',
			autoHeight: true,
			labelAlign: 'right',
			labelWidth: 100,
			items: [{
				xtype: 'swlpucombo',
				hiddenName: 'Lpu_bid',
				fieldLabel: 'МО МСЭ',
				width: 340
			}, {
				xtype: 'swmedservicecombo',
				hiddenName: 'MedService_id',
				fieldLabel: 'Бюро МСЭ',
				allowBlank: true,
				width: 340,
				params:{
					MedServiceType_id: 2 // с типом «5.Медико-социальная экспертиза»,
				}
			},{
				xtype: 'swlpucombo',
				hiddenName: 'Lpu_oid',
				fieldLabel: 'МО',
				width: 340
			}, {
				xtype: 'swdatefield',
				name: 'LpuMseLink_begDate',
				fieldLabel: 'Дата открытия',
				width: 120
			}, {
				xtype: 'swdatefield',
				name: 'LpuMseLink_endDate',
				fieldLabel: 'Дата закрытия',
				width: 120
			}],
			keys: [{
				fn: function(e) {
					this.doSearch();
				}.createDelegate(this),
				key: Ext.EventObject.ENTER,
				stopEvent: true
			}]
		});

		this.GridPanel = new sw.Promed.ViewFrame({
			id: 'LMLVW_GridPanel',
			dataUrl: '/?c=LpuStructure&m=loadLpuMseLinkGrid',
			autoLoadData: false,
			paging: true,
			root: 'data',
			stringfields: [
				{name: 'LpuMseLink_id', type: 'int', header: 'ID', key: true},
				{name: 'Lpu_oid', type: 'int', hidden: true},
				{name: 'Lpu_bid', type: 'int', hidden: true},
				{name: 'MedService_id', type: 'int', hidden: true},
				{name: 'Lpu_bNick', header: 'МО МСЭ', type: 'string',  width: 400},
				{name: 'MedService_Nick', header: 'Бюро МСЭ', type: 'string', id: 'autoexpand'},
				{name: 'Lpu_Nick', header: 'МО', type: 'string', width: 460},
				{name: 'LpuMseLink_begDate', header: 'Дата открытия', type: 'date', width: 200},
				{name: 'LpuMseLink_endDate', header: 'Дата закрытия', type: 'date', width: 200}
			],
			actions: [
				{name:'action_add', handler: function(){this.openLpuMseLinkEditWindow('add')}.createDelegate(this)},
				{name:'action_edit', handler: function(){this.openLpuMseLinkEditWindow('edit')}.createDelegate(this)},
				{name:'action_view', handler: function(){this.openLpuMseLinkEditWindow('view')}.createDelegate(this)},
				{name:'action_delete', handler: function(){this.deleteLpuMseLink()}.createDelegate(this)}
			]
		});

		this.GridPanel.ViewToolbar.on('render', function(vt){
			return this.addCloseFilterMenu(this.GridPanel);
		}.createDelegate(this));

		Ext.apply(this, {
			buttons: [
				{
					handler: function() {
						this.doSearch();
					}.createDelegate(this),
					iconCls: 'search16',
					id: 'LMLVW_SearchButton',
					text: BTN_FRMSEARCH
				},
				{
					handler: function() {
						this.doSearch(true);
					}.createDelegate(this),
					iconCls: 'resetsearch16',
					id: 'LMLVW_ResetButton',
					text: BTN_FRMRESET
				},
				{
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
				}
			],
			items: [this.FilterPanel, this.GridPanel]
		});

		sw.Promed.swLpuMseLinkViewWindow.superclass.initComponent.apply(this, arguments);
	}
});