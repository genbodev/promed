/**
 * swGoodUnitViewWindow - окно просмотра справочника единиц измерения товара
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      	Admin
 * @access       	public
 * @copyright		Copyright (c) 2014 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			06.10.2014
 */
/*NO PARSE JSON*/

sw.Promed.swGoodsUnitViewWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swGoodsUnitViewWindow',
	layout: 'border',
	title: lang['edinitsyi_izmereniya_tovara'],
	maximizable: true,
	maximized: false,
	width: 780,
	minWidth: 780,
	height: 500,
	minHeight: 300,
	modal: true,

	doSearch: function(reset) {
		var base_form = this.FilterPanel.getForm();

		var grid_panel = this.GridPanel;
		var grid = grid_panel.getGrid();

		if (reset) {
			base_form.reset();
		}

		var params = base_form.getValues();
		params.start = 0;
		params.limit = 100;

		grid.getStore().load({params: params});
	},

	openGoodsUnitEditWindow: function(action, invoice_type_id) {
		if (!action.inlist(['add','edit','view'])) {
			return false;
		}

		var grid_panel = this.GridPanel;
		var grid = grid_panel.getGrid();

		var params = {};
		params.action = action;
		params.formParams = {};

		if (action != 'add') {
			var record = grid.getSelectionModel().getSelected();
			if (!record || Ext.isEmpty(record.get('GoodsUnit_id'))) {
				return false;
			}
			params.formParams.GoodsUnit_id = record.get('GoodsUnit_id');
		}

		params.callback = function() {
			grid_panel.getAction('action_refresh').execute();
		};

		getWnd('swGoodsUnitEditWindow').show(params);
		return true;
	},

	deleteGoodsUnit: function() {
		var grid_panel = this.GridPanel;
		var grid = grid_panel.getGrid();

		var record = grid.getSelectionModel().getSelected();

		if (!record || !record.get('GoodsUnit_id')) {
			return false;
		}

		sw.swMsg.show({
			buttons:Ext.Msg.YESNO,
			fn:function (buttonId, text, obj) {
				if (buttonId == 'yes') {
					var params = {GoodsUnit_id: record.get('GoodsUnit_id')};

					Ext.Ajax.request({
						callback: function(opt, scs, response) {
							var response_obj = Ext.util.JSON.decode(response.responseText);

							if (!response_obj.Error_Msg) {
								grid_panel.getAction('action_refresh').execute();
							}
						}.createDelegate(this),
						params: params,
						url: '/?c=GoodsUnit&m=deleteGoodsUnit'
					});
				}
			}.createDelegate(this),
			icon:Ext.MessageBox.QUESTION,
			msg:lang['vyi_hotite_udalit_zapis'],
			title:lang['podtverjdenie']
		});
	},

	importGoodsUnitFromRls: function() {
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет обновление..."});
		loadMask.show();

		Ext.Ajax.request({
			url: '/?c=GoodsUnit&m=importGoodsUnitFromRls',
			success: function(response) {
				loadMask.hide();
				var response_obj = Ext.util.JSON.decode(response.responseText);

				if (!Ext.isEmpty(response_obj.insCount)) {
					Ext.Msg.alert(lang['soobschenie'], lang['dobavleno_zapisey'] + response_obj.insCount);
					if (response_obj.insCount > 0) {
						this.GridPanel.getAction('action_refresh').execute();
					}
				} else if (response_obj.Error_Msg) {
					Ext.Msg.alert(lang['oshibka'], response_obj.Error_Msg);
				} else {
					Ext.Msg.alert(lang['oshibka'], lang['pri_obnovlenii_edinits_izmereniya_proizoshla_oshibka']);
				}
			}.createDelegate(this),
			failure: function(response) {
				loadMask.hide();

			}.createDelegate(this),
		});
	},

	show: function() {
		sw.Promed.swGoodsUnitViewWindow.superclass.show.apply(this, arguments);

		this.allowImportFromRls = false;
		this.viewOnly = false;
		if (arguments[0].allowImportFromRls) {
			this.allowImportFromRls = arguments[0].allowImportFromRls;
		}
		
		if (arguments[0].viewOnly) {
			this.viewOnly = arguments[0].viewOnly;
		}

		var base_form = this.FilterPanel.getForm();
		base_form.reset();

		this.center();

		if (!this.GridPanel.getAction('action_new')) {
			this.GridPanel.addActions({
				name: 'action_new',
				text: lang['deystviya'],
				iconCls: 'actions16',
				menu: new Ext.menu.Menu({
					items: [{
						id: 'GUVW_ImportFromRls',
						name: 'import_from_rls',
						text: lang['obnovit_ed_izmereniya_dozirovki'],
						tooltip: lang['obnovit_ed_izmereniya_dozirovki'],
						disabled: true,
						handler: function() {
							this.importGoodsUnitFromRls();
						}.createDelegate(this),
					}]
				})
			});
		}

		this.GridPanel.setActionDisabled('action_add',this.viewOnly);
		this.GridPanel.setActionDisabled('action_edit',this.viewOnly);
		this.GridPanel.setActionDisabled('action_delete',this.viewOnly);

		if (this.allowImportFromRls) {
			Ext.getCmp('GUVW_ImportFromRls').enable();
		}

		this.doSearch(true);
	},

	initComponent: function() {
		this.FilterPanel = new Ext.FormPanel({
			frame: true,
			id: 'GUVW_FilterPanel',
			region: 'north',
			autoHeight: true,
			labelAlign: 'right',
			labelWidth: 100,
			items: [{
				layout: 'column',
				items: [{
					layout: 'form',
					items: [{
						xtype: 'textfield',
						name: 'GoodsUnit_Name',
						fieldLabel: lang['naimenovanie'],
						width: '460'
					}]
				}, {
					layout: 'form',
					items: [{
						xtype: 'button',
						handler: function() {
							this.doSearch();
						}.createDelegate(this),
						iconCls: 'search16',
						id: 'GUVW_SearchButton',
						text: BTN_FRMSEARCH,
						style: 'padding-left: 20px;'
					}]
				}, {
					layout: 'form',
					items: [{
						xtype: 'button',
						handler: function() {
							this.doSearch(true);
						}.createDelegate(this),
						iconCls: 'resetsearch16',
						id: 'GUVW_ResetButton',
						text: BTN_FRMRESET,
						style: 'padding-left: 5px;'
					}]
				}]
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
			dataUrl: '/?c=GoodsUnit&m=loadGoodsUnitGrid',
			border: false,
			autoLoadData: false,
			paging: true,
			root: 'data',
			stringfields: [
				{name: 'GoodsUnit_id', type: 'int', header: 'ID', key: true},
				{name: 'Okei_id', type: 'int', hidden: true},
				{name: 'GoodsUnit_Name', header: lang['naimenovanie'], type: 'string', id: 'autoexpand'},
				{name: 'GoodsUnit_Nick', header: lang['kratkoe_naimenovanie'], type: 'string', width: 120},
				{name: 'Okei_Code', header: lang['kod_okei'], type: 'int', width: 120},
				{name: 'Okei_Name', header: lang['naimenovanie_okei'], type: 'string', width: 240},
			],
			actions: [
				{name:'action_add', handler: function(){this.openGoodsUnitEditWindow('add');}.createDelegate(this)},
				{name:'action_edit', handler: function(){this.openGoodsUnitEditWindow('edit');}.createDelegate(this)},
				{name:'action_view', handler: function(){this.openGoodsUnitEditWindow('view');}.createDelegate(this)},
				{name:'action_delete', handler: function(){this.deleteGoodsUnit();}.createDelegate(this)}
			]
		});

		Ext.apply(this,{
			buttons: [
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

		sw.Promed.swGoodsUnitViewWindow.superclass.initComponent.apply(this, arguments);
	}
});