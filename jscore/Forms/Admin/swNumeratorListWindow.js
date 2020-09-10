/**
 * swNumeratorListWindow - список нумераторов
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @access       public
 * @copyright    Copyright (c) 2015 Swan Ltd.
 * @comment
 */
/*NO PARSE JSON*/
sw.Promed.swNumeratorListWindow = Ext.extend(sw.Promed.BaseForm,
{
	maximizable: false,
	maximized: true,
	height: 600,
	width: 900,
	id: 'swNumeratorListWindow',
	title: lang['numeratoryi'],
	layout: 'border',
	resizable: true,
	deleteNumerator: function(options) {
		if ( typeof options != 'object' ) {
			options = new Object();
		}

		var win = this;
		var grid = this.NumeratorGrid.getGrid();

		if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('Numerator_id') ) {
			return false;
		}

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( buttonId == 'yes' ) {
					var record = grid.getSelectionModel().getSelected();

					var params = new Object();
					params.Numerator_id = record.get('Numerator_id');

					win.deleteNumeratorReal(options, params);
				}
			}.createDelegate(this),
			icon: Ext.MessageBox.QUESTION,
			msg: lang['udalit_numerator'],
			title: lang['vopros']
		});
	},
	deleteNumeratorReal: function(options, params) {
		var win = this;
		var grid = this.NumeratorGrid.getGrid();
		win.getLoadMask(lang['udalenie_numeratora']).show();

		if (options.ignoreCheckRezerv) {
			params.ignoreCheckRezerv = 1;
		}

		Ext.Ajax.request({
			callback: function(opt, scs, response) {
				win.getLoadMask().hide();
				if (scs) {
					var result = Ext.util.JSON.decode(response.responseText);

					if (result.success) {
						if (result.Alert_Msg) {
							sw.swMsg.show({
								buttons: Ext.Msg.YESNO,
								fn: function (buttonId, text, obj) {
									if (buttonId == 'yes') {
										options.ignoreCheckRezerv = 1;
										win.deleteNumeratorReal(options, params);
									}
								},
								icon: Ext.MessageBox.QUESTION,
								msg: result.Alert_Msg,
								title: lang['vopros']
							});
						} else {
							grid.getStore().reload();
						}
					}
				}
			},
			params: params,
			url: '/?c=Numerator&m=deleteNumerator'
		});
	},
	openNumeratorEditWindow: function(action) {
		var grid = this.NumeratorGrid.getGrid();

		var params = new Object();
		params.action = action;
		params.formParams = new Object();

		if (action != 'add') {
			var record = grid.getSelectionModel().getSelected();
			if (record.get('Numerator_deleted') == 2) {
				params.action = 'view';
			}
			if (!record.get('Numerator_id')) { return false; }
			params.formParams.Numerator_id = record.get('Numerator_id');
		}

		params.callback = function(){
			this.NumeratorGrid.getAction('action_refresh').execute();
		}.createDelegate(this);

		getWnd('swNumeratorEditWindow').show(params);
	},
	openNumeratorRezervEditWindow: function(action) {
		var win = this;
		var ng_grid = this.NumeratorGrid.getGrid();
		var grid = this.NumeratorRezervGrid.getGrid();

		var ng_record = ng_grid.getSelectionModel().getSelected();
		if (!ng_record || !ng_record.get('Numerator_id')) {
			return false;
		}

		var params = new Object();
		params.action = action;
		params.formParams = new Object();
		params.formParams.Numerator_id = ng_record.get('Numerator_id');

		if (win.NumeratorChildTabPanel.getActiveTab().id == 'generationTab') {
			params.formParams.NumeratorRezervType_id = 2;
		} else {
			params.formParams.NumeratorRezervType_id = 1;
		}

		if (action != 'add') {
			var record = grid.getSelectionModel().getSelected();
			if (!record.get('NumeratorRezerv_id')) { return false; }
			params.formParams.NumeratorRezerv_id = record.get('NumeratorRezerv_id');
		}

		params.callback = function(){
			this.NumeratorRezervGrid.getAction('action_refresh').execute();
		}.createDelegate(this);

		getWnd('swNumeratorRezervEditWindow').show(params);
	},
	deleteNumeratorRezerv: function() {
		var win = this;
		var grid = this.NumeratorRezervGrid.getGrid();

		if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('NumeratorRezerv_id') ) {
			return false;
		}

		var text = lang['rezervirovaniya'];
		if (win.NumeratorChildTabPanel.getActiveTab().id == 'generationTab') {
			text = lang['generatsii'];
		}

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( buttonId == 'yes' ) {
					var record = grid.getSelectionModel().getSelected();

					var params = new Object();
					var url = "/?c=Numerator&m=deleteNumeratorRezerv";
					params.NumeratorRezerv_id = record.get('NumeratorRezerv_id');

					win.getLoadMask(lang['udalenie_diapazona']+text+lang['nomerov']).show();
					Ext.Ajax.request({
						callback: function(opt, scs, response) {
							win.getLoadMask().hide();
							if (scs) {
								var result = Ext.util.JSON.decode(response.responseText);

								if (result.success)
								{
									grid.getStore().reload();
								}
							}
						}.createDelegate(this),
						params: params,
						url: url
					});
				}
			}.createDelegate(this),
			icon: Ext.MessageBox.QUESTION,
			msg: lang['udalit_diapazon']+text+lang['nomerov'],
			title: lang['vopros']
		});
	},
	doResetFilters: function() {
		var base_form = this.filtersPanel.getForm();
		base_form.reset();
		base_form.findField('Lpu_id').fireEvent('change', base_form.findField('Lpu_id'), base_form.findField('Lpu_id').getValue());

		if (isSuperAdmin()) {
			base_form.findField('Lpu_id').enable();
		} else {
			base_form.findField('Lpu_id').disable();
			base_form.findField('Lpu_id').setValue(getGlobalOptions().lpu_id);
			base_form.findField('Lpu_id').fireEvent('change', base_form.findField('Lpu_id'), base_form.findField('Lpu_id').getValue());
		}
	},
	reloadRezervGrid: function() {
		var win = this;
		win.NumeratorRezervGrid.removeAll({ clearAll: true });

		var numeratorRecord = win.NumeratorGrid.getGrid().getSelectionModel().getSelected();
		if (numeratorRecord && numeratorRecord.get('Numerator_id')) {
			var params = {
				Numerator_id: numeratorRecord.get('Numerator_id')
			};

			if (win.NumeratorChildTabPanel.getActiveTab().id == 'generationTab') {
				params.NumeratorRezervType_id = 2;
			} else {
				params.NumeratorRezervType_id = 1;
			}

			win.NumeratorRezervGrid.loadData({globalFilters: params});
		}
	},
	doFilter: function() {
		var base_form = this.filtersPanel.getForm();
		var filters = base_form.getValues();
		filters.start = 0;
		filters.limit = 100;

		if (base_form.findField('Lpu_id').disabled) {
			filters.Lpu_id = base_form.findField('Lpu_id').disable();
		}

		var LpuStructure_id = base_form.findField('LpuStructure_id').getValue();
		filters.LpuSection_id = null;
		filters.LpuBuilding_id = null;
		filters.LpuUnit_id = null;
		if (!Ext.isEmpty(LpuStructure_id)) {
			index = base_form.findField('LpuStructure_id').getStore().findBy(function (rec) {
				return (rec.get('LpuStructure_id') == LpuStructure_id);
			});
			if (index >= 0) {
				filters.LpuSection_id = base_form.findField('LpuStructure_id').getStore().getAt(index).get('LpuSection_id');
				filters.LpuBuilding_id = base_form.findField('LpuStructure_id').getStore().getAt(index).get('LpuBuilding_id');
				filters.LpuUnit_id = base_form.findField('LpuStructure_id').getStore().getAt(index).get('LpuUnit_id');
			}
		}

		this.NumeratorGrid.removeAll({ clearAll: true });
		this.NumeratorRezervGrid.removeAll({ clearAll: true });

		this.NumeratorGrid.loadData({ globalFilters: filters });
	},
	initComponent: function()
	{
		var win = this;

		this.filtersPanel = new Ext.FormPanel({
			region: 'north',
			labelAlign: 'right',
			layout: 'form',
			autoHeight: true,
			labelWidth: 100,
			frame: true,
			border: false,
			keys: [{
				key: Ext.EventObject.ENTER,
				fn: function(e)
				{
					win.doFilter();
				},
				stopEvent: true
			}],
			items: [{
				buttons: [{
					text: BTN_FIND,
					tabIndex: TABINDEX_RRLW + 10,
					handler: function() {
						win.doFilter();
					},
					iconCls: 'search16'
				}, {
					text: BTN_RESETFILTER,
					tabIndex: TABINDEX_RRLW + 11,
					handler: function() {
						win.doResetFilters();
						win.doFilter();
					},
					iconCls: 'resetsearch16'
				}, '-'],
				xtype: 'fieldset',
				autoHeight: true,
				collapsible: true,
				listeners: {
					collapse: function(p) {
						win.doLayout();
					},
					expand: function(p) {
						win.doLayout();
					}
				},
				title: lang['filtryi'],
				items: [{
					border: false,
					layout: 'column',
					anchor: '-10',
					items: [{
						layout: 'form',
						columnWidth: .40,
						border: false,
						items: [{
							allowBlank: true,
							hiddenName: 'Lpu_id',
							fieldLabel: lang['mo'],
							xtype: 'swlpucombo',
							listeners: {
								'change': function(combo, newValue) {
									var base_form = win.filtersPanel.getForm();

									base_form.findField('LpuStructure_id').clearValue();
									base_form.findField('LpuStructure_id').getStore().removeAll();
									if (!Ext.isEmpty(newValue)) {
										base_form.findField('LpuStructure_id').getStore().load({
											params: {
												Lpu_id: newValue
											},
											callback: function() {
											}
										});
									}
								}
							},
							anchor: '-10'
						}, {
							allowBlank: true,
							hiddenName: 'LpuStructure_id',
							store: new Ext.data.Store({
								autoLoad: false,
								reader: new Ext.data.JsonReader({
									id: 'LpuStructure_id'
								}, [
									{ name: 'LpuStructure_id', mapping: 'LpuStructure_id' },
									{ name: 'LpuStructure_Name', mapping: 'LpuStructure_Name' },
									{ name: 'LpuBuilding_id', mapping: 'LpuBuilding_id' },
									{ name: 'LpuUnit_id', mapping: 'LpuUnit_id' },
									{ name: 'LpuSection_id', mapping: 'LpuSection_id' },
									{ name: 'sort', mapping: 'sort' }
								]),
								key: 'LpuStructure_id',
								sortInfo: {
									field: 'sort'
								},
								url: '/?c=Numerator&m=loadLpuStructureCombo'
							}),
							tpl: new Ext.XTemplate(
								'<tpl for="."><div class="x-combo-list-item">',
								'{[(values.LpuBuilding_id > 0) ? "<b>" : ""]}',
								'{[(values.LpuSection_id > 0) ? "<i>" : ""]}',
								'{LpuStructure_Name}',
								'{[(values.LpuSection_id > 0) ? "</i>" : ""]}',
								'{[(values.LpuBuilding_id > 0) ? "</b>" : ""]}',
								'</div></tpl>'
							),
							displayField: 'LpuStructure_Name',
							valueField: 'LpuStructure_id',
							fieldLabel: lang['struktura_mo'],
							xtype: 'swbaselocalcombo',
							anchor: '-10'
						}]
					}, {
						layout: 'form',
						columnWidth: .40,
						border: false,
						items: [{
							tpl: new Ext.XTemplate(
								'<tpl for="."><div class="x-combo-list-item">',
								'<font color="red">{NumeratorObject_Code}</font>&nbsp;{NumeratorObject_TableName}',
								'</div></tpl>'
							),
							store: new Ext.db.AdapterStore({
								autoLoad: false,
								dbFile: 'Promed.db',
								fields: [
									{name: 'NumeratorObject_id', mapping: 'NumeratorObject_id'},
									{name: 'NumeratorObject_Code', mapping: 'NumeratorObject_id'},
									{name: 'NumeratorObject_SchemaNam', mapping: 'NumeratorObject_SchemaName'},
									{name: 'NumeratorObject_SysName', mapping: 'NumeratorObject_SysName'},
									{name: 'NumeratorObject_TableName', mapping: 'NumeratorObject_TableName'},
								],
								key: 'NumeratorObject_id',
								sortInfo: {
									field: 'NumeratorObject_Code'
								},
								tableName: 'NumeratorObject'
							}),
							valueField: 'NumeratorObject_id',
							displayField: 'NumeratorObject_TableName',
							codeField: 'NumeratorObject_Code',
							hiddenName: 'NumeratorObject_id',
							fieldLabel: lang['dokument'],
							xtype: 'swbaselocalcombo',
							anchor: '-10'
						}, {
							name: 'Numerator_Name',
							fieldLabel: lang['naimenovanie'],
							xtype: 'textfield',
							anchor: '-10'
						}]
					}]
				}]
			}]
		});

		this.NumeratorGrid = new sw.Promed.ViewFrame({
			id: win.id+'NumeratorGrid',
			title:'',
			object: 'Numerator',
			dataUrl: '/?c=Numerator&m=loadNumeratorList',
			autoLoadData: false,
			paging: true,
			root: 'data',
			totalProperty: 'totalCount',
			region: 'center',
			toolbar: true,
			stringfields:
			[
				{name: 'Numerator_id', type: 'int', header: 'ID', key: true, hidden: true},
				{name: 'Numerator_Name', header: lang['naimenovanie'], width: 150},
				{name: 'Lpu_Nick', header: lang['mo'], width: 100},
				{name: 'NumeratorObject_TableName', header: lang['dokument'], id: 'autoexpand', width: 150},
				{name: 'LpuStructure_Name', header: lang['struktura_mo'], width: 150},
				{name: 'Numerator_begDT', header: lang['data_nachala'], width: 80},
				{name: 'Numerator_endDT', header: lang['data_okonchaniya'], width: 80},
				{name: 'NumeratorGenUpd_Name', header: lang['chastota_obnuleniya'], width: 100},
				{name: 'Numerator_Ser', header: lang['seriya'], width: 100},
				{name: 'Numerator_PreNum', header: lang['prefiks'], width: 100},
				{name: 'Numerator_PostNum', header: lang['postfiks'], width: 100},
				{name: 'Numerator_NumLen', header: lang['dlina_nomera'], width: 100},
				{name: 'Numerator_FirstNum', header: lang['nachalnoe_znachenie'], width: 100},
				{name: 'Numerator_Num', header: lang['tekuschee_znachenie'], width: 100},
				{name: 'Numerator_deleted', type: 'int', hidden: true}
			],
			onDblClick: function() {
				win.openNumeratorEditWindow('edit');
			},
			onLoadData: function() {
			},
			onRowSelect: function(sm,index,record)
			{
				win.NumeratorRezervGrid.removeAll({ clearAll: true });
				if (record && record.get('Numerator_id')) {
					win.reloadRezervGrid();
				}

				this.getAction('action_edit').disable();
				this.getAction('action_view').disable();
				this.getAction('action_delete').disable();
				if (isSuperAdmin() || isLpuAdmin()) {
					if (record.get('Numerator_id')) {
						this.getAction('action_view').enable();
						if (record.get('Numerator_deleted') != 2) {
							this.getAction('action_edit').enable();
							this.getAction('action_delete').enable();
						}
					}
				}
			},
			actions:
			[
				{name:'action_add', disabled: (!isSuperAdmin() && !isLpuAdmin()), handler: function() { win.openNumeratorEditWindow('add'); }},
				{name:'action_edit', disabled: (!isSuperAdmin() && !isLpuAdmin()), handler: function() { win.openNumeratorEditWindow('edit'); }},
				{name:'action_view', handler: function() { win.openNumeratorEditWindow('view'); }},
				{name:'action_print', disabled: false},
				{name:'action_delete', disabled: (!isSuperAdmin() && !isLpuAdmin()), handler: function() { win.deleteNumerator(); }},
			]
		});

		this.NumeratorGrid.getGrid().view = new Ext.grid.GridView({
			getRowClass : function (row, index)
			{
				var cls = '';

				if (row.get('Numerator_deleted') == 2)
					cls = cls+'x-grid-rowgray ';

				return cls;
			},
			listeners:
			{
				rowupdated: function(view, first, record)
				{
					view.getRowClass(record);
				}
			}
		});

		this.NumeratorRezervGrid = new sw.Promed.ViewFrame({
			id: win.id+'NumeratorRezervGrid',
			title: '',
			object: 'NumeratorRezerv',
			dataUrl: '/?c=Numerator&m=loadNumeratorRezervList',
			autoLoadData: false,
			toolbar: true,
			stringfields:
			[
				{name: 'NumeratorRezerv_id', type: 'int', header: 'ID', key: true, hidden: true},
				{name: 'NumeratorRezerv_From', header: lang['nachalo_diapazona'], width: 200},
				{name: 'NumeratorRezerv_To', header: lang['konets_diapazona'], width: 200}
			],
			onDblClick: function() {
				win.openNumeratorRezervEditWindow('edit');
			},
			onRowSelect: function(sm,index,record)
			{
				var numeratorRecord = win.NumeratorGrid.getGrid().getSelectionModel().getSelected();
				this.getAction('action_add').disable();
				if (numeratorRecord.get('Numerator_id') && numeratorRecord.get('Numerator_deleted') != 2) {
					this.getAction('action_add').enable();
				}

				this.getAction('action_edit').disable();
				this.getAction('action_view').disable();
				this.getAction('action_delete').disable();
				if (numeratorRecord.get('Numerator_id') && record.get('NumeratorRezerv_id')) {
					this.getAction('action_view').enable();
					if (numeratorRecord.get('Numerator_deleted') != 2) {
						this.getAction('action_edit').enable();
						this.getAction('action_delete').enable();
					}
				}
			},
			actions:
			[
				{name:'action_add', handler: function() { win.openNumeratorRezervEditWindow('add'); }},
				{name:'action_edit', handler: function() { win.openNumeratorRezervEditWindow('edit'); }},
				{name:'action_view', handler: function() { win.openNumeratorRezervEditWindow('view'); }},
				{name:'action_print', disabled: true, hidden: true},
				{name:'action_refresh', disabled: false},
				{name:'action_delete', handler: function() { win.deleteNumeratorRezerv(); }},
			]
		});

		this.NumeratorChildTabPanel = new Ext.TabPanel({
			items: [
				{
					id: 'rezerveTab',
					tabTip: lang['rezerv_nomerov_dlya_vyipiski_dokumentov_na_blankah'],
					title: lang['diapazonyi_rezervirovaniya']
				},
				{
					id: 'generationTab',
					tabTip: lang['diapazonyi_nomerov_dlya_vyipiski_dokumentov_na_listah'],
					title: lang['diapazonyi_generatsii']
				}
			],
			activeTab: 0,
			border: false,
			listeners: {
				tabchange: function (tab, panel) {
					win.reloadRezervGrid();
				}
			}
		});

		this.NumeratorChildPanel = new Ext.Panel({
			region: 'south',
			height: 200,
			border: false,
			items: [
				this.NumeratorChildTabPanel,
				this.NumeratorRezervGrid
			]
		});

		this.formPanel = new Ext.Panel({
			region: 'center',
			labelAlign: 'right',
			layout: 'border',
			labelWidth: 50,
			border: false,
			items: [
				this.filtersPanel,
				this.NumeratorGrid,
				this.NumeratorChildPanel
			]
		});

		Ext.apply(this, {
		items: [
			win.formPanel
		],
		buttons: [{
				text: '-'
			},
			HelpButton(this, TABINDEX_RRLW + 13),
			{
				iconCls: 'close16',
				tabIndex: TABINDEX_RRLW + 14,
				handler: function() {
					win.hide();
				},
				text: BTN_FRMCLOSE
			}]
		});

		sw.Promed.swNumeratorListWindow.superclass.initComponent.apply(this, arguments);
	},
	show: function() {
		sw.Promed.swNumeratorListWindow.superclass.show.apply(this, arguments);

		this.doResetFilters();
		this.doFilter();
	}
});