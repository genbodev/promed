/**
 * swTariffVolumesAttributeViewWindow - Справочник атрибутов тарифов и объемов
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2014 Swan Ltd.
 * @author			Dmitriy Vlasenko
 * @version			25.01.2015
 */

/*NO PARSE JSON*/

sw.Promed.swTariffVolumesAttributeViewWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swTariffVolumesAttributeViewWindow',
	width: 800,
	height: 450,
	maximizable: true,
	maximized: true,
	layout: 'border',
	title: lang['spravochnik_atributov_tarifov_i_obyemov'],
	callback: Ext.emptyFn,
	show: function() {
		sw.Promed.swTariffVolumesAttributeViewWindow.superclass.show.apply(this, arguments);
	},
	openAttributeVisionEditWindow: function(action, mode) {
		var attrviewframe, mainviewframe, object;

		switch ( mode ) {
			case 'TariffClass':
				attrviewframe = this.TariffClassAttributeVisionGrid;
				mainviewframe = this.TariffClassGrid;
				object = 'TariffClass';
			break;

			case 'VolumeType':
				attrviewframe = this.VolumeTypeAttributeVisionGrid;
				mainviewframe = this.VolumeTypeGrid;
				object = 'VolumeType';
			break;

			default:
				return false;
			break;
		}

		var
			grid = attrviewframe.getGrid(),
			params = new Object();

		params.action = action;
		params.formParams = new Object();
		params.existsKeyValue = false;

		if (action != 'add') {
			var record = grid.getSelectionModel().getSelected();
			if (!record.get('AttributeVision_id')) { return false; }
			params.formParams.AttributeVision_id = record.get('AttributeVision_id');
		}

		attrviewframe.getGrid().getStore().each(function(rec){
			switch (action){
				case 'edit':
					if (rec.get('AttributeVision_isKeyValue') == 2 && rec.get('AttributeVision_id') != record.get('AttributeVision_id') && record.get('AttributeVision_isKeyValue') != 2){
						params.existsKeyValue = true;
					}
					break;
				default:
					if (rec.get('AttributeVision_isKeyValue') == 2) {
						params.existsKeyValue = true;
					}
					break;
			}
		});

		params.callback = function(){
			attrviewframe.getAction('action_refresh').execute();
		}.createDelegate(this);

		var recordMain = mainviewframe.getGrid().getSelectionModel().getSelected();
		if (!recordMain.get(object + '_id')) {
			return false;
		}

		params.formParams.AttributeVision_TableName = 'dbo.' + object,
		params.formParams.AttributeVision_TablePKey = recordMain.get(object + '_id')
		params.hideDBObject = true;

		getWnd('swAttributeVisionEditWindow').show(params);
	},
	deleteAttributeVision: function(mode) {
		var attrviewframe, win = this;

		switch ( mode ) {
			case 'TariffClass':
				attrviewframe = this.TariffClassAttributeVisionGrid;
			break;

			case 'VolumeType':
				attrviewframe = this.VolumeTypeAttributeVisionGrid;
			break;

			default:
				return false;
			break;
		}

		var grid = attrviewframe.getGrid();
		var record = grid.getSelectionModel().getSelected();

		if (!record.get('AttributeVision_id')) {
			return false;
		}

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( buttonId == 'yes' ) {
					Ext.Ajax.request({
						callback: function(options, success, response) {
							if (success) {
								grid.getStore().reload();
							}
						}.createDelegate(this),
						params: {
							AttributeVision_id: record.get('AttributeVision_id')
						},
						url: "/?c=Attribute&m=deleteAttributeVision"
					});
				}
			}.createDelegate(this),
			icon: Ext.MessageBox.QUESTION,
			msg: lang['vyi_deystvitelno_hotite_udalit_dannyiy_atribut'],
			title: lang['vopros']
		});
	},
	openAttributeEditWindow: function(action) {
		var grid = this.AttributeGrid.getGrid();

		var params = new Object();
		params.action = action;
		params.formParams = new Object();

		if (action != 'add') {
			var record = grid.getSelectionModel().getSelected();
			if (!record.get('Attribute_id')) { return false; }
			params.formParams.Attribute_id = record.get('Attribute_id');
		}

		params.callback = function(){
			this.AttributeGrid.getAction('action_refresh').execute();
		}.createDelegate(this);

		getWnd('swAttributeEditWindow').show(params);
	},
	addCloseFilterMenu: function(gridCmp){
		var form = this;
		var grid = gridCmp;

		if ( !grid.getAction('action_isclosefilter_' + grid.id) ) {
			var menuIsCloseFilter = new Ext.menu.Menu({
				items: [
					new Ext.Action({
						text: lang['vse'],
						handler: function() {
							if (grid.gFilters) {
								grid.gFilters.isClose = null;
							}
							grid.getAction('action_isclosefilter_'+grid.id).setText(lang['pokazyivat_vse']);
							grid.getGrid().getStore().baseParams.isClose = null;
							grid.getGrid().getStore().reload();
						}
					}),
					new Ext.Action({
						text: lang['otkryityie'],
						handler: function() {
							if (grid.gFilters) {
								grid.gFilters.isClose = 1;
							}
							grid.getAction('action_isclosefilter_'+grid.id).setText(lang['pokazyivat_otkryityie']);
							grid.getGrid().getStore().baseParams.isClose = 1;
							grid.getGrid().getStore().reload();
						}
					}),
					new Ext.Action({
						text: lang['zakryityie'],
						handler: function() {
							if (grid.gFilters) {
								grid.gFilters.isClose = 2;
							}
							grid.getAction('action_isclosefilter_'+grid.id).setText(lang['pokazyivat_zakryityie']);
							grid.getGrid().getStore().baseParams.isClose = 2;
							grid.getGrid().getStore().reload();
						}
					})
				]
			});

			grid.addActions({
				isClose: 1,
				name: 'action_isclosefilter_'+grid.id,
				text: lang['pokazyivat_otkryityie'],
				menu: menuIsCloseFilter
			});
			grid.getGrid().getStore().baseParams.isClose = 1;
		}

		return true;
	},
	doResetFiltersAttributeGrid: function () {
		var filtersForm = this.AttributeGridFilters.getForm();
		filtersForm.reset();
	},
	doFilterAttributeGrid: function () {
		var filtersForm = this.AttributeGridFilters.getForm();
		var filters = filtersForm.getValues();
		filters.Attribute_isKeyValue = filtersForm.findField('Attribute_isKeyValue').checked;
		filters.start = 0;
		filters.limit = 100;

		this.AttributeGrid.loadData({globalFilters: filters});
	},
	doResetFiltersTariffClassGrid: function () {
		var filtersForm = this.TariffClassGridFilters.getForm();
		filtersForm.reset();
	},
	doResetFiltersVolumeTypeGrid: function () {
		var filtersForm = this.VolumeTypeGridFilters.getForm();
		filtersForm.reset();
	},
	doFilterTariffClassGrid: function () {
		var filtersForm = this.TariffClassGridFilters.getForm();
		var filters = filtersForm.getValues();
		filters.TariffClass_noKeyValue = filtersForm.findField('TariffClass_noKeyValue').checked;
		filters.start = 0;
		filters.limit = 100;

		this.TariffClassGrid.loadData({globalFilters: filters});
	},
	doFilterVolumeTypeGrid: function () {
		var filtersForm = this.VolumeTypeGridFilters.getForm();
		var filters = filtersForm.getValues();
		filters.VolumeType_noKeyValue = filtersForm.findField('VolumeType_noKeyValue').checked;
		filters.start = 0;
		filters.limit = 100;

		this.VolumeTypeGrid.loadData({globalFilters: filters});
	},
	deleteAttribute: function() {
		var win = this;

		var grid = this.AttributeGrid.getGrid();
		var record = grid.getSelectionModel().getSelected();

		if (!record.get('Attribute_id')) {
			return false;
		}

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( buttonId == 'yes' ) {
					Ext.Ajax.request({
						callback: function(options, success, response) {
							if (success) {
								grid.getStore().reload();
							}
						}.createDelegate(this),
						params: {
							Attribute_id: record.get('Attribute_id')
						},
						url: "/?c=Attribute&m=deleteAttribute"
					});
				}
			}.createDelegate(this),
			icon: Ext.MessageBox.QUESTION,
			msg: lang['vyi_deystvitelno_hotite_udalit_dannyiy_atribut'],
			title: lang['vopros']
		});
	},
	initComponent: function() {
		var win = this;

		this.TariffClassGridFilters = new Ext.form.FormPanel({
			xtype: 'form',
			region: 'north',
			labelAlign: 'right',
			layout: 'form',
			autoHeight: true,
			labelWidth: 50,
			frame: true,
			border: false,
			keys: [{
				key: Ext.EventObject.ENTER,
				fn: function (e) {
					win.doFilterTariffClassGrid();
				},
				stopEvent: true
			}],
			items: [{
				listeners: {
					collapse: function (p) {
						win.doLayout();
					},
					expand: function (p) {
						win.doLayout();
					}
				},
				xtype: 'fieldset',
				style: 'margin: 5px 5px 5px 5px',
				title: lang['filtryi'],
				collapsible: true,
				autoHeight: true,
				labelWidth: 200,
				anchor: '-10',
				layout: 'form',
				items: [{
					border: false,
					layout: 'column',
					anchor: '-10',
					items: [{
						layout: 'form',
						border: false,
						width: 310,
						labelWidth: 200,
						items: [{
							xtype: 'swdatefield',
							plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
							width: 100,
							name: 'TariffClass_begDate_From',
							fieldLabel: lang['data_nachala_ot']
						}]
					}, {
						layout: 'form',
						border: false,
						width: 125,
						labelWidth: 15,
						items: [{
							xtype: 'swdatefield',
							plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
							width: 100,
							name: 'TariffClass_begDate_To',
							fieldLabel: lang['do']
						}]
					}, {
						layout: 'form',
						border: false,
						width: 160,
						labelWidth: 30,
						items: [{
							xtype: 'textfield',
							width: 118,
							name: 'TariffClass_Code',
							fieldLabel: lang['kod']
						}]
					}, {
						layout: 'form',
						border: false,
						width: 155,
						labelWidth: 90,
						items: [{
							xtype: 'swcheckbox',
							width: 20,
							name: 'TariffClass_noKeyValue',
							fieldLabel: lang['bez_znacheniya']
						}]
					}]
				}, {
					border: false,
					layout: 'column',
					anchor: '-10',
					items: [{
						layout: 'form',
						border: false,
						width: 310,
						labelWidth: 200,
						items: [{
							xtype: 'swdatefield',
							plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
							width: 100,
							name: 'TariffClass_endDate_From',
							fieldLabel: lang['data_okonchaniya_ot']
						}]
					}, {
						layout: 'form',
						border: false,
						width: 125,
						labelWidth: 15,
						items: [{
							xtype: 'swdatefield',
							plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
							width: 100,
							name: 'TariffClass_endDate_To',
							fieldLabel: lang['do']
						}]
					}, {
						layout: 'form',
						border: false,
						items: [{
							text: BTN_FILTER,
							xtype: 'button',
							handler: function () {
								win.doFilterTariffClassGrid();
							},
							iconCls: 'search16'
						}]
					}, {
						layout: 'form',
						bodyStyle: 'padding-left: 5px;',
						border: false,
						items: [{
							text: BTN_RESETFILTER,
							xtype: 'button',
							handler: function () {
								win.doResetFiltersTariffClassGrid();
								win.doFilterTariffClassGrid();
							},
							iconCls: 'resetsearch16'
						}]
					}]
				}]
			}]
		});

		this.VolumeTypeGridFilters = new Ext.form.FormPanel({
			xtype: 'form',
			region: 'north',
			labelAlign: 'right',
			layout: 'form',
			autoHeight: true,
			labelWidth: 50,
			frame: true,
			border: false,
			keys: [{
				key: Ext.EventObject.ENTER,
				fn: function (e) {
					win.doFilterVolumeTypeGrid();
				},
				stopEvent: true
			}],
			items: [{
				listeners: {
					collapse: function (p) {
						win.doLayout();
					},
					expand: function (p) {
						win.doLayout();
					}
				},
				xtype: 'fieldset',
				style: 'margin: 5px 5px 5px 5px',
				title: lang['filtryi'],
				collapsible: true,
				autoHeight: true,
				labelWidth: 200,
				anchor: '-10',
				layout: 'form',
				items: [{
					border: false,
					layout: 'column',
					anchor: '-10',
					items: [{
						layout: 'form',
						border: false,
						width: 310,
						labelWidth: 200,
						items: [{
							xtype: 'swdatefield',
							plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
							width: 100,
							name: 'VolumeType_begDate_From',
							fieldLabel: lang['data_nachala_ot']
						}]
					}, {
						layout: 'form',
						border: false,
						width: 125,
						labelWidth: 15,
						items: [{
							xtype: 'swdatefield',
							plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
							width: 100,
							name: 'VolumeType_begDate_To',
							fieldLabel: lang['do']
						}]
					}, {
						layout: 'form',
						border: false,
						width: 160,
						labelWidth: 30,
						items: [{
							xtype: 'textfield',
							width: 118,
							name: 'VolumeType_Code',
							fieldLabel: lang['kod']
						}]
					}, {
						layout: 'form',
						border: false,
						width: 155,
						labelWidth: 90,
						items: [{
							xtype: 'checkbox',
							width: 20,
							name: 'VolumeType_noKeyValue',
							fieldLabel: lang['bez_znacheniya']
						}]
					}]
				}, {
					border: false,
					layout: 'column',
					anchor: '-10',
					items: [{
						layout: 'form',
						border: false,
						width: 310,
						labelWidth: 200,
						items: [{
							xtype: 'swdatefield',
							plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
							width: 100,
							name: 'VolumeType_endDate_From',
							fieldLabel: lang['data_okonchaniya_ot']
						}]
					}, {
						layout: 'form',
						border: false,
						width: 125,
						labelWidth: 15,
						items: [{
							xtype: 'swdatefield',
							plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
							width: 100,
							name: 'VolumeType_endDate_To',
							fieldLabel: lang['do']
						}]
					}, {
						layout: 'form',
						border: false,
						items: [{
							text: BTN_FILTER,
							xtype: 'button',
							handler: function () {
								win.doFilterVolumeTypeGrid();
							},
							iconCls: 'search16'
						}]
					}, {
						layout: 'form',
						bodyStyle: 'padding-left: 5px;',
						border: false,
						items: [{
							text: BTN_RESETFILTER,
							xtype: 'button',
							handler: function () {
								win.doResetFiltersVolumeTypeGrid();
								win.doFilterVolumeTypeGrid();
							},
							iconCls: 'resetsearch16'
						}]
					}]
				}]
			}]
		});

		this.TariffClassGrid = new sw.Promed.ViewFrame({
			dataUrl: '/?c=TariffVolumes&m=loadTariffClassGrid',
			title: lang['vidyi_tarifov'],
			uniqueId: true,
			border: false,
			paging: true,
			totalProperty: 'totalCount',
			root: 'data',
			region: 'center',
			autoLoadData: false,
			stringfields: [
				{name: 'TariffClass_id', type: 'int', header: 'ID', key: true},
				{name: 'TariffClass_Code', header: lang['kod'], type: 'string', width: 120},
				{name: 'TariffClass_Name', header: lang['naimenovanie'], type: 'string', id: 'autoexpand'},
				{name: 'TariffClass_begDT', header: lang['data_nachala'], type: 'date', width: 100},
				{name: 'TariffClass_endDT', header: lang['data_okonchaniya'], type: 'date', width: 100}
			],
			onRowSelect: function(sm, index, record) {
				if (!Ext.isEmpty(record.get('TariffClass_id'))) {
					win.TariffClassAttributeVisionGrid.loadData({
						globalFilters: {
							start: 0,
							limit: 100,
							AttributeVision_TableName: 'dbo.TariffClass',
							AttributeVision_TablePKey: record.get('TariffClass_id')
						}
					});
				} else {
					win.TariffClassAttributeVisionGrid.getGrid().getStore().removeAll();
				}
			},
			actions: [
				{name:'action_add', hidden: true, disabled: true},
				{name:'action_edit', hidden: true, disabled: true},
				{name:'action_view', hidden: true, disabled: true},
				{name:'action_delete', hidden: true, disabled: true}
			]
		});
		win.TariffClassGrid.ViewToolbar.on('render', function(vt){return this.addCloseFilterMenu(win.TariffClassGrid);}.createDelegate(this));

		this.VolumeTypeGrid = new sw.Promed.ViewFrame({
			dataUrl: '/?c=TariffVolumes&m=loadVolumeTypeGrid',
			title: lang['vidyi_obyemov'],
			uniqueId: true,
			border: false,
			paging: true,
			totalProperty: 'totalCount',
			root: 'data',
			region: 'center',
			autoLoadData: false,
			stringfields: [
				{name: 'VolumeType_id', type: 'int', header: 'ID', key: true},
				{name: 'VolumeType_Code', header: lang['kod'], type: 'string', width: 120},
				{name: 'VolumeType_Name', header: lang['naimenovanie'], type: 'string', id: 'autoexpand'},
				{name: 'VolumeType_begDate', header: lang['data_nachala'], type: 'date', width: 100},
				{name: 'VolumeType_endDate', header: lang['data_okonchaniya'], type: 'date', width: 100}
			],
			onRowSelect: function(sm, index, record) {
				if (!Ext.isEmpty(record.get('VolumeType_id'))) {
					win.VolumeTypeAttributeVisionGrid.loadData({
						globalFilters: {
							start: 0,
							limit: 100,
						AttributeVision_TableName: 'dbo.VolumeType',
						AttributeVision_TablePKey: record.get('VolumeType_id')
						}
					});
				} else {
					win.VolumeTypeAttributeVisionGrid.getGrid().getStore().removeAll();
				}
			},
			actions: [
				{name:'action_add', hidden: true, disabled: true},
				{name:'action_edit', hidden: true, disabled: true},
				{name:'action_view', hidden: true, disabled: true},
				{name:'action_delete', hidden: true, disabled: true}
			]
		});
		win.VolumeTypeGrid.ViewToolbar.on('render', function(vt){return this.addCloseFilterMenu(win.VolumeTypeGrid);}.createDelegate(this));

		this.TariffClassAttributeVisionGrid = new sw.Promed.ViewFrame({
			dataUrl: '/?c=Attribute&m=loadAttributeVisionGrid',
			title: lang['atributyi'],
			uniqueId: true,
			border: false,
			paging: true,
			totalProperty: 'totalCount',
			root: 'data',
			region: 'south',
			height: 300,
			autoLoadData: false,
			stringfields: [
				{name: 'AttributeVision_id', type: 'int', header: 'ID', key: true},
				{name: 'Attribute_id', type: 'int', hidden: true},
				{name: 'Region_id', type: 'int', hidden: true},
				{name: 'Org_id', type: 'int', hidden: true},
				{name: 'AttributeVision_isKeyValue', type: 'int', hidden: true},
				{name: 'Attribute_Name', type: 'string', header: lang['atribut'], width: 200},
				{name: 'AttributeVision_TableName', type: 'string', header: lang['tablitsa_v_bd'], width: 200},
				{name: 'AttributeVision_Sort', type: 'int', header: lang['sortirovka'], width: 100},
				{name: 'Region_Name', type: 'string', header: lang['region'], width: 200},
				{name: 'Org_Name', type: 'string', header: lang['organizatsiya'], id: 'autoexpand'}
			],
			actions: [
				{name:'action_add', handler: function() { this.openAttributeVisionEditWindow('add', 'TariffClass'); }.createDelegate(this)},
				{name:'action_edit', handler: function() { this.openAttributeVisionEditWindow('edit', 'TariffClass'); }.createDelegate(this)},
				{name:'action_view', handler: function() { this.openAttributeVisionEditWindow('view', 'TariffClass'); }.createDelegate(this)},
				{name:'action_delete', handler: function() { this.deleteAttributeVision('TariffClass'); }.createDelegate(this)}
			]
		});

		this.VolumeTypeAttributeVisionGrid = new sw.Promed.ViewFrame({
			dataUrl: '/?c=Attribute&m=loadAttributeVisionGrid',
			title: lang['atributyi'],
			uniqueId: true,
			border: false,
			paging: true,
			totalProperty: 'totalCount',
			root: 'data',
			region: 'south',
			height: 300,
			autoLoadData: false,
			stringfields: [
				{name: 'AttributeVision_id', type: 'int', header: 'ID', key: true},
				{name: 'Attribute_id', type: 'int', hidden: true},
				{name: 'Region_id', type: 'int', hidden: true},
				{name: 'Org_id', type: 'int', hidden: true},
				{name: 'AttributeVision_isKeyValue', type: 'int', hidden: true},
				{name: 'Attribute_Name', type: 'string', header: lang['atribut'], width: 200},
				{name: 'AttributeVision_TableName', type: 'string', header: lang['tablitsa_v_bd'], width: 200},
				{name: 'AttributeVision_Sort', type: 'int', header: lang['sortirovka'], width: 100},
				{name: 'Region_Name', type: 'string', header: lang['region'], width: 200},
				{name: 'Org_Name', type: 'string', header: lang['organizatsiya'], id: 'autoexpand'}
			],
			actions: [
				{name:'action_add', handler: function() { this.openAttributeVisionEditWindow('add', 'VolumeType'); }.createDelegate(this)},
				{name:'action_edit', handler: function() { this.openAttributeVisionEditWindow('edit', 'VolumeType'); }.createDelegate(this)},
				{name:'action_view', handler: function() { this.openAttributeVisionEditWindow('view', 'VolumeType'); }.createDelegate(this)},
				{name:'action_delete', handler: function() { this.deleteAttributeVision('VolumeType'); }.createDelegate(this)}
			]
		});

		this.AttributeGridFilters = new Ext.form.FormPanel({
			xtype: 'form',
			region: 'north',
			labelAlign: 'right',
			layout: 'form',
			autoHeight: true,
			labelWidth: 50,
			frame: true,
			border: false,
			keys: [{
				key: Ext.EventObject.ENTER,
				fn: function (e) {
					win.doFilterAttributeGrid();
				},
				stopEvent: true
			}],
			items: [{
				listeners: {
					collapse: function (p) {
						win.doLayout();
					},
					expand: function (p) {
						win.doLayout();
					}
				},
				xtype: 'fieldset',
				style: 'margin: 5px 5px 5px 5px',
				title: lang['filtryi'],
				collapsible: true,
				autoHeight: true,
				labelWidth: 200,
				anchor: '-10',
				layout: 'form',
				items: [{
					border: false,
					layout: 'column',
					anchor: '-10',
					items: [{
						layout: 'form',
						border: false,
						width: 310,
						labelWidth: 200,
						items: [{
							xtype: 'swdatefield',
							plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
							width: 100,
							name: 'Attribute_begDate_From',
							fieldLabel: lang['data_nachala_ot']
						}]
					}, {
						layout: 'form',
						border: false,
						width: 125,
						labelWidth: 15,
						items: [{
							xtype: 'swdatefield',
							plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
							width: 100,
							name: 'Attribute_begDate_To',
							fieldLabel: lang['do']
						}]
					}, {
						layout: 'form',
						border: false,
						width: 160,
						labelWidth: 30,
						items: [{
							xtype: 'textfield',
							width: 118,
							name: 'Attribute_Code',
							fieldLabel: lang['kod']
						}]
					}, {
						layout: 'form',
						border: false,
						width: 165,
						labelWidth: 130,
						items: [{
							xtype: 'checkbox',
							width: 20,
							name: 'Attribute_isKeyValue',
							fieldLabel: lang['yavlyaetsya_znacheniem']
						}]
					}]
				}, {
					border: false,
					layout: 'column',
					anchor: '-10',
					items: [{
						layout: 'form',
						border: false,
						width: 310,
						labelWidth: 200,
						items: [{
							xtype: 'swdatefield',
							plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
							width: 100,
							name: 'Attribute_endDate_From',
							fieldLabel: lang['data_okonchaniya_ot']
						}]
					}, {
						layout: 'form',
						border: false,
						width: 125,
						labelWidth: 15,
						items: [{
							xtype: 'swdatefield',
							plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
							width: 100,
							name: 'Attribute_endDate_To',
							fieldLabel: lang['do']
						}]
					}, {
						layout: 'form',
						border: false,
						items: [{
							text: BTN_FILTER,
							xtype: 'button',
							handler: function () {
								win.doFilterAttributeGrid();
							},
							iconCls: 'search16'
						}]
					}, {
						layout: 'form',
						bodyStyle: 'padding-left: 5px;',
						border: false,
						items: [{
							text: BTN_RESETFILTER,
							xtype: 'button',
							handler: function () {
								win.doResetFiltersAttributeGrid();
								win.doFilterAttributeGrid();
							},
							iconCls: 'resetsearch16'
						}]
					}]
				}]
			}]
		});

		this.AttributeGrid = new sw.Promed.ViewFrame({
			dataUrl: '/?c=Attribute&m=loadAttributeGrid',
			title: lang['atributyi'],
			uniqueId: true,
			border: false,
			paging: true,
			totalProperty: 'totalCount',
			root: 'data',
			region: 'center',
			autoLoadData: false,
			stringfields: [
				{name: 'Attribute_id', type: 'int', header: 'ID', key: true},
				{name: 'Attribute_Code', header: lang['kod'], type: 'int', width: 120},
				{name: 'Attribute_Name', header: lang['naimenovanie'], type: 'string', id: 'autoexpand'},
				{name: 'Attribute_TableName', header: lang['tablitsa_v_bd'], type: 'string'},
				{name: 'Attribute_begDate', header: lang['data_nachala'], type: 'date'},
				{name: 'Attribute_endDate', header: lang['data_okonchaniya'], type: 'date'}
			],
			actions: [
				{name:'action_add', handler: function() { this.openAttributeEditWindow('add'); }.createDelegate(this)},
				{name:'action_edit', handler: function() { this.openAttributeEditWindow('edit'); }.createDelegate(this)},
				{name:'action_view', handler: function() { this.openAttributeEditWindow('view'); }.createDelegate(this)},
				{name:'action_delete', handler: function() { this.deleteAttribute(); }.createDelegate(this)}
			]
		});
		win.AttributeGrid.ViewToolbar.on('render', function(vt){return this.addCloseFilterMenu(win.AttributeGrid);}.createDelegate(this));

		this.TabPanel = new Ext.TabPanel({
			activeTab: 0,
			region: 'center',
			layoutOnTabChange: true,
			region: 'center',
			items: [{
				id: 'tab_attributes',
				layout: 'border',
				title: lang['atributyi'],
				items: [
					win.AttributeGridFilters,
					win.AttributeGrid
				]
			}, {
				id: 'tab_tariffsattribute',
				layout: 'border',
				title: lang['vid_tarifa_atribut'],
				items: [
					{
						border: 'false',
						region: 'center',
						layout: 'border',
						items: [
							win.TariffClassGridFilters,
							win.TariffClassGrid
						]
					},
					win.TariffClassAttributeVisionGrid,
				]
			}, {
				id: 'tab_volumesattribute',
				layout: 'border',
				title: lang['vid_obyema_atribut'],
				items: [
					{
						border: 'false',
						region: 'center',
						layout: 'border',
						items: [
							win.VolumeTypeGridFilters,
							win.VolumeTypeGrid
						]
					},
					win.VolumeTypeAttributeVisionGrid,
				]
			}],
			listeners:
			{
				tabchange: function(tab, panel) {
					switch(panel.id) {
						case 'tab_attributes':
							win.AttributeGrid.loadData();
							break;

						case 'tab_tariffsattribute':
							win.TariffClassGrid.loadData();
							break;

						case 'tab_volumesattribute':
							win.VolumeTypeGrid.loadData();
							break;
					}
					win.doLayout();
				}
			}
		});

		Ext.apply(this, {
			items: [
				win.TabPanel
			],
			buttons: [
				{
					text: '-'
				},
				HelpButton(this, 1),
				{
					handler: function () {
						win.hide();
					}.createDelegate(this),
					iconCls: 'close16',
					text: BTN_FRMCLOSE
				}]
		});

		sw.Promed.swTariffVolumesAttributeViewWindow.superclass.initComponent.apply(this, arguments);
	}
});