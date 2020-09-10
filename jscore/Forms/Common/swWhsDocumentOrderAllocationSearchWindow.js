/**
* swWhsDocumentOrderAllocationSearchWindow - Разнарядка на выписку рецептов: Поиск
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      WhsDocumentOrderAllocation
* @access       public
* @copyright    Copyright (c) 2013 Swan Ltd.
* @author       Dmitry Vlasenko
* @version      25.04.2013
* @comment      
*/
/*NO PARSE JSON*/
sw.Promed.swWhsDocumentOrderAllocationSearchWindow = Ext.extend(sw.Promed.BaseForm,
{
	maximized: true,
	id: 'WhsDocumentOrderAllocationSearchWindow',
	title: WND_WHSDOCUMENTORDERALLOCATION_SEARCH, 
	layout: 'border',
	resizable: true,
	doFilterWhsDocumentOrderAllocationGrid: function() {
		var base_form = this.WhsDocumentOrderAllocationGridFilters.getForm();
		var filters = base_form.getValues();
        var wnd = this;

        if (wnd.mode == 'limitedRights' && !(getGlobalOptions().groups && (getGlobalOptions().groups.toString().indexOf('SuperAdmin') != -1))) {
            this.WhsDocumentOrderAllocationGridFiltersTab.findById('Lpu_id').disable();
            filters.Lpu_id = getGlobalOptions().lpu_id;
        }

		filters.start = 0;
		filters.limit = 100;
		filters.WhsDocumentType_Code = 9; //только разнарядки МО
		this.WhsDocumentOrderAllocationGrid.loadData({ globalFilters: filters });
	},
	doFilterWhsDocumentOrderAllocationDrugGrid: function()
	{
		var base_form = this.WhsDocumentOrderAllocationDrugGridFilters.getForm();
		var filters = base_form.getValues();
		filters.start = 0;
		filters.limit = 100;
		
		var grid = this.WhsDocumentOrderAllocationGrid.getGrid();

		if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('WhsDocumentOrderAllocation_id') )
		{
			return false;
		}
		
		filters.WhsDocumentOrderAllocation_id = grid.getSelectionModel().getSelected().get('WhsDocumentOrderAllocation_id');
		this.WhsDocumentOrderAllocationDrugGrid.loadData({ globalFilters: filters });	
	},
	doPrintWhsDocumentOrderAllocation: function()
	{
		var grid = this.WhsDocumentOrderAllocationGrid.getGrid();
		if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('WhsDocumentOrderAllocation_id') )
		{
			return false;
		}
		printBirt({
			'Report_FileName': 'WhsDocumentOrderAllocation_Report.rptdesign',
			'Report_Params': '&WhsDocumentOrderAllocation_id=' + grid.getSelectionModel().getSelected().get('WhsDocumentOrderAllocation_id'),
			'Report_Format': 'xls'
		});
	},
	initComponent: function() 
	{
		var win = this;
		
		this.WhsDocumentOrderAllocationGrid = new sw.Promed.ViewFrame(
		{
			id: win.id+'WhsDocumentOrderAllocationGrid',
			title:lang['raznaryadka'],
			object: 'WhsDocumentOrderAllocation',
			dataUrl: '/?c=WhsDocumentOrderAllocation&m=loadWhsDocumentOrderAllocationGrid',
			autoLoadData: false,
			region: 'center',
			toolbar: true,
			paging: true,
			root: 'data',
			onRowSelect: function(sm,index,record) {
				win.doFilterWhsDocumentOrderAllocationDrugGrid();
			},
			totalProperty: 'totalCount',
			stringfields:
			[
				{name: 'WhsDocumentOrderAllocation_id', type: 'int', header: 'ID', key: true, hidden: true},
				{name: 'UAddress_Address', header: lang['territoriya'], width: 120, id: 'autoexpand'},
				{name: 'Lpu_Nick', header: lang['mo'], width: 120},
				{name: 'WhsDocumentOrderAllocation_Period', header: lang['period_raznaryadki'], width: 150},
				{name: 'DrugFinance_Name', header: lang['finansirovanie'], width: 120},
				{name: 'WhsDocumentCostItemType_Name', header: lang['statya_rashodov'], width: 100},
				{name: 'WhsDocumentUc_Sum', header: lang['summa'], width: 100}
			],
			actions:
			[
				{name:'action_add', disabled: true, hidden: true},
				{name:'action_edit', disabled: true, hidden: true},
				{name:'action_print', disabled: true, hidden: true, handler: function() {
					win.doPrintWhsDocumentOrderAllocation();
				}},
				{name:'action_view', disabled: true, hidden: true},
				{name:'action_delete', disabled: true, hidden: true}
			]
		});
		
		this.WhsDocumentOrderAllocationDrugGrid = new sw.Promed.ViewFrame(
		{
			id: win.id+'WhsDocumentOrderAllocationDrugGrid',
			title:'',
			object: 'WhsDocumentOrderAllocationDrug',
			dataUrl: '/?c=WhsDocumentOrderAllocationDrug&m=loadWhsDocumentOrderAllocationDrugGrid',
			autoLoadData: false,
			region: 'center',
			//toolbar: false,
			paging: true,
			root: 'data',
			totalProperty: 'totalCount',
			stringfields:
			[
				{name: 'WhsDocumentOrderAllocationDrug_id', type: 'int', header: 'ID', key: true, hidden: true},
				{name: 'WhsDocumentUc_Num', header: lang['№_gk'], width: 120},
				{name: 'ActMatters_RusName', header: lang['mnn'], width: 80 },
				{name: 'TradeName_Name', header: lang['lp'], width: 80, id: 'autoexpand' },
				{name: 'DrugForm_Name', header: lang['lekarstvennayaforma'], width: 95 },
				{name: 'Drug_Dose', header: lang['dozirovka'], width: 80 },
				{name: 'Drug_Fas', header: lang['fasovka'], width: 80 },
				{name: 'Reg_Num', header: lang['№_ru'], width: 80 },
				{name: 'Firm_Name', header: lang['proizvoditel'], width: 80 },
				{name: 'Drug_id', hidden: true},
				{name: 'WhsDocumentOrderAllocationDrug_Kolvo', header: lang['kol-vo_vraznaryadke'], width: 80},
				{name: 'WhsDocumentOrderAllocationDrug_Price', header: lang['tsena'], width: 80},
				{name: 'WhsDocumentOrderAllocationDrug_Sum', header: lang['summa'], width: 80},
				{name: '', header: 'Кол-во доступно к выписке', width: 120} // TODO что это??
			],
			actions:
			[
				{name:'action_add', disabled: true, hidden: true},
				{name:'action_edit', disabled: true, hidden: true},
				{name:'action_print'},
				{name:'action_view', disabled: true, hidden: true},
				{name:'action_refresh', disabled: true, hidden: true},
				{name:'action_delete', disabled: true, hidden: true}
			]
		});
		
		this.WhsDocumentOrderAllocationGridFiltersTab = new Ext.TabPanel({
			activeTab: 0,
			autoHeight: true,
			border: false,
			enableTabScroll: true,
			listeners: {
				'tabchange': function(panel, tab) {
					win.doLayout();
				}
			},
			layoutOnTabChange: true,
			items: [{
				layout: 'form',
				autoHeight: true,
				bodyStyle: 'padding:5px;',
				id: 'tab_orderallocation',
				labelAlign: 'right',
				title: lang['raznaryadka'],
				items: [{
					layout: 'column',
					border: false,
					items: [{
						layout: 'form',
						labelWidth: 180,
						border: false,
						width: 375,
						items: [{
							hiddenName: 'DrugFinance_id',
							moreFields: [
								{name: 'DrugFinance_begDate', mapping: 'DrugFinance_begDate'},
								{name: 'DrugFinance_endDate', mapping: 'DrugFinance_endDate'}
							],
							onLoadStore: function() {
								// фильтрация по begDate и endDate
								var date = new Date();
								this.lastQuery = '';
								this.getStore().clearFilter();
								this.getStore().filterBy(function(rec) {
									if (
										(Ext.isEmpty(rec.get('DrugFinance_begDate')) || Date.parseDate(rec.get('DrugFinance_begDate'), 'd.m.Y') < date) &&
										(Ext.isEmpty(rec.get('DrugFinance_endDate')) || Date.parseDate(rec.get('DrugFinance_endDate'), 'd.m.Y') > date)
									) {
										return true;
									} else {
										return false;
									}
								});
							},
							xtype: 'swcommonsprcombo',
							anchor: '100%',
							comboSubject: 'DrugFinance',
							fieldLabel: lang['istochnik_finansirovaniya']
						}]
					}, {
						layout: 'form',
						labelWidth: 60,
						border: false,
						width: 375,
						items: [{
							displayField: 'Org_Name',
							editable: false,
							enableKeyEvents: true,
							fieldLabel: lang['mo'],
                            width: 300,
							//anchor: '-10',
							hiddenName: 'Lpu_id',
							id: 'Lpu_id',
							listeners: {
								'keydown': function( inp, e ) {
									if ( inp.disabled )
										return;

									if ( e.F4 == e.getKey() ) {
										if ( e.browserEvent.stopPropagation )
											e.browserEvent.stopPropagation();
										else
											e.browserEvent.cancelBubble = true;

										if ( e.browserEvent.preventDefault )
											e.browserEvent.preventDefault();
										else
											e.browserEvent.returnValue = false;

										e.returnValue = false;

										if ( Ext.isIE ) {
											e.browserEvent.keyCode = 0;
											e.browserEvent.which = 0;
										}

										inp.onTrigger1Click();
										return false;
									}
								},
								'keyup': function(inp, e) {
									if ( e.F4 == e.getKey() ) {
										if ( e.browserEvent.stopPropagation )
											e.browserEvent.stopPropagation();
										else
											e.browserEvent.cancelBubble = true;

										if ( e.browserEvent.preventDefault )
											e.browserEvent.preventDefault();
										else
											e.browserEvent.returnValue = false;

										e.returnValue = false;

										if ( Ext.isIE ) {
											e.browserEvent.keyCode = 0;
											e.browserEvent.which = 0;
										}

										return false;
									}
								}
							},
							mode: 'local',
							onTrigger1Click: function() {
								var combo = this;
								if ( combo.disabled ) {
									return false;
								}

								getWnd('swOrgSearchWindow').show({
									object: 'lpu',
									onClose: function() {
										combo.focus(true, 200)
									},
									onSelect: function(org_data) {
										if ( org_data.Lpu_id > 0 ) {
											combo.getStore().loadData([{
												Lpu_id: org_data.Lpu_id,
												Org_Name: org_data.Org_Name
											}]);
											combo.setValue(org_data.Lpu_id);
											getWnd('swOrgSearchWindow').hide();
											combo.collapse();
										}
									}
								});
							},
							store: new Ext.data.JsonStore({
								autoLoad: false,
								fields: [
									{ name: 'Lpu_id', type: 'int' },
									{ name: 'Org_Name', type: 'string' }
								],
								key: 'Lpu_id',
								sortInfo: {
									field: 'Org_Name'
								},
								url: C_ORG_LIST
							}),
							tpl: new Ext.XTemplate(
								'<tpl for="."><div class="x-combo-list-item">',
								'{Org_Name}',
								'</div></tpl>'
							),
							trigger1Class: 'x-form-search-trigger',
							triggerAction: 'none',
							valueField: 'Lpu_id',
							xtype: 'swbaseremotecombo'
						}]
					}]
				}, {
					layout: 'column',
					border: false,
					items: [{
						layout: 'form',
						labelWidth: 180,
						border: false,
						width: 375,
						items: [{
							hiddenName: 'WhsDocumentCostItemType_id',
							moreFields: [
								{name: 'WhsDocumentCostItemType_begDate', mapping: 'WhsDocumentCostItemType_begDate'},
								{name: 'WhsDocumentCostItemType_endDate', mapping: 'WhsDocumentCostItemType_endDate'}
							],
							onLoadStore: function() {
								// фильтрация по begDate и endDate
								var date = new Date();
								this.lastQuery = '';
								this.getStore().clearFilter();
								this.getStore().filterBy(function(rec) {
									if (
										(Ext.isEmpty(rec.get('WhsDocumentCostItemType_begDate')) || Date.parseDate(rec.get('WhsDocumentCostItemType_begDate'), 'd.m.Y') < date) &&
										(Ext.isEmpty(rec.get('WhsDocumentCostItemType_endDate')) || Date.parseDate(rec.get('WhsDocumentCostItemType_endDate'), 'd.m.Y') > date)
									) {
										return true;
									} else {
										return false;
									}
								});
							},
							xtype: 'swcommonsprcombo',
							anchor: '100%',
							comboSubject: 'WhsDocumentCostItemType',
							fieldLabel: lang['statya_rashodov']
						}]
					}, {
						layout: 'form',
						labelWidth: 60,
						border: false,
						width: 375,
						items: [{
							fieldLabel: lang['period'],
							name: 'WhsDocumentOrderAllocation_Range',
							plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
							width: 170,
							xtype: 'daterangefield'
						}]
					}, {
						layout: 'form',
						border: false,
						items: [{
							style: "padding-left: 0px",
							xtype: 'button',
							text: lang['poisk'],
							iconCls: 'search16',
							handler: function() {
								win.doFilterWhsDocumentOrderAllocationGrid();
							}
						}]
					}, {
						layout: 'form',
						border: false,
						items: [{
							style: "padding-left: 10px",
							xtype: 'button',
							text: lang['sbros'],
							iconCls: 'resetsearch16',
							handler: function() {
								var base_form = win.WhsDocumentOrderAllocationGridFilters.getForm();
								base_form.reset();
								win.doFilterWhsDocumentOrderAllocationGrid();
							}
						}]
					}]
				}]
			}, {
				layout: 'form',
				bodyStyle: 'padding:5px;',
				id: 'tab_address',
				autoHeight: true,
				labelAlign: 'right',
				title: lang['adres'],
				items: [{
					layout: 'column',
					border: false,
					items: [{
						layout: 'form',
						labelWidth: 100,
						border: false,
						width: 375,
						items: [{
							codeField: 'KLAreaStat_Code',
							disabled: false,
							displayField: 'KLArea_Name',
							editable: true,
							enableKeyEvents: true,
							fieldLabel: lang['territoriya'],
							hiddenName: 'KLAreaStat_id',
							listeners: {
								'change': function(combo, newValue, oldValue) {
									var current_record = combo.getStore().getById(newValue);
									var base_form = win.WhsDocumentOrderAllocationGridFilters.getForm();

									var country_combo = base_form.findField('KLCountry_id');
									var region_combo = base_form.findField('KLRgn_id');
									var sub_region_combo = base_form.findField('KLSubRgn_id').enable();
									var city_combo = base_form.findField('KLCity_id').enable();
									var town_combo = base_form.findField('KLTown_id').enable();

									country_combo.enable();
									region_combo.enable();
									sub_region_combo.enable();
									city_combo.enable();
									town_combo.enable();

									if ( !current_record ) {
										return false;
									}

									var country_id = current_record.get('KLCountry_id');
									var region_id = current_record.get('KLRGN_id');
									var subregion_id = current_record.get('KLSubRGN_id');
									var city_id = current_record.get('KLCity_id');
									var town_id = current_record.get('KLTown_id');
									var klarea_pid = 0;
									var level = 0;
									
									clearAddressCombo(
										country_combo.areaLevel, 
										{
											'Country': country_combo,
											'Region': region_combo,
											'SubRegion': sub_region_combo,
											'City': city_combo,
											'Town': town_combo
										}
									);

									if ( country_id != null ) {
										country_combo.setValue(country_id);
										country_combo.disable();
									}
									else {
										return false;
									}

									region_combo.getStore().load({
										callback: function() {
											region_combo.setValue(region_id);
										},
										params: {
											country_id: country_id,
											level: 1,
											value: 0
										}
									});

									if ( region_id.toString().length > 0 ) {
										klarea_pid = region_id;
										level = 1;
									}

									sub_region_combo.getStore().load({
										callback: function() {
											sub_region_combo.setValue(subregion_id);
										},
										params: {
											country_id: 0,
											level: 2,
											value: klarea_pid
										}
									});

									if ( subregion_id.toString().length > 0 ) {
										klarea_pid = subregion_id;
										level = 2;
									}

									city_combo.getStore().load({
										callback: function() {
											city_combo.setValue(city_id);
										},
										params: {
											country_id: 0,
											level: 3,
											value: klarea_pid
										}
									});

									if ( city_id.toString().length > 0 ) {
										klarea_pid = city_id;
										level = 3;
									}

									town_combo.getStore().load({
										callback: function() {
											town_combo.setValue(town_id);
										},
										params: {
											country_id: 0,
											level: 4,
											value: klarea_pid
										}
									});

									if ( town_id.toString().length > 0 ) {
										klarea_pid = town_id;
										level = 4;
									}
									
									switch ( level ) {
										case 1:
											region_combo.disable();
											break;

										case 2:
											region_combo.disable();
											sub_region_combo.disable();
											break;

										case 3:
											region_combo.disable();
											sub_region_combo.disable();
											city_combo.disable();
											break;

										case 4:
											region_combo.disable();
											sub_region_combo.disable();
											city_combo.disable();
											town_combo.disable();
											break;
									}
								}
							},
							store: new Ext.db.AdapterStore({
								autoLoad: true,
								dbFile: 'Promed.db',
								fields: [
									{ name: 'KLAreaStat_id', type: 'int' },
									{ name: 'KLAreaStat_Code', type: 'int' },
									{ name: 'KLArea_Name', type: 'string' },
									{ name: 'KLCountry_id', type: 'int' },
									{ name: 'KLRGN_id', type: 'int' },
									{ name: 'KLSubRGN_id', type: 'int' },
									{ name: 'KLCity_id', type: 'int' },
									{ name: 'KLTown_id', type: 'int' }
								],
								key: 'KLAreaStat_id',
								sortInfo: {
									field: 'KLAreaStat_Code',
									direction: 'ASC'
								},
								tableName: 'KLAreaStat'
							}),
							tpl: new Ext.XTemplate(
								'<tpl for="."><div class="x-combo-list-item">',
								'<font color="red">{KLAreaStat_Code}</font>&nbsp;{KLArea_Name}',
								'</div></tpl>'
							),
							valueField: 'KLAreaStat_id',
							anchor: '-10',
							xtype: 'swbaselocalcombo'
						}]
					}, {
						layout: 'form',
						labelWidth: 120,
						border: false,
						width: 375,
						items: [{
							areaLevel: 2,
							disabled: false,
							displayField: 'KLArea_Name',
							enableKeyEvents: true,
							fieldLabel: lang['rayon'],
							hiddenName: 'KLSubRgn_id',
							listeners: {
								'change': function(combo, newValue, oldValue) {
									var base_form = win.WhsDocumentOrderAllocationGridFilters.getForm();
									
									if ( newValue != null && combo.getRawValue().toString().length > 0 ) {
										loadAddressCombo(
											combo.areaLevel, 
											{
												'Country': base_form.findField('KLCountry_id'),
												'Region': base_form.findField('KLRgn_id'),
												'SubRegion': base_form.findField('KLSubRgn_id'),
												'City': base_form.findField('KLCity_id'),
												'Town': base_form.findField('KLTown_id')
											},
											0,
											combo.getValue(), 
											true
										);
									}
									else {
										clearAddressCombo(
											combo.areaLevel, 
											{
												'Country': base_form.findField('KLCountry_id'),
												'Region': base_form.findField('KLRgn_id'),
												'SubRegion': base_form.findField('KLSubRgn_id'),
												'City': base_form.findField('KLCity_id'),
												'Town': base_form.findField('KLTown_id')
											}
										);
									}
								},
								'keydown': function(combo, e) {
									if ( e.getKey() == e.DELETE && combo.getRawValue().toString().length > 0 && combo.getValue().toString().length > 0 ) {
										combo.fireEvent('change', combo, null, combo.getValue());
									}
								},
								'select': function(combo, record, index) {
									if ( record.get('KLArea_id') == combo.getValue() ) {
										combo.collapse();
										return false;
									}
									combo.fireEvent('change', combo, record.get('KLArea_id'));
								}
							},
							minChars: 0,
							mode: 'local',
							queryDelay: 250,
							store: new Ext.data.JsonStore({
								autoLoad: false,
								fields: [
									{ name: 'KLArea_id', type: 'int' },
									{ name: 'KLArea_Name', type: 'string' }
								],
								key: 'KLArea_id',
								sortInfo: {
									field: 'KLArea_Name'
								},
								url: C_LOAD_ADDRCOMBO
							}),
							tpl: new Ext.XTemplate(
								'<tpl for="."><div class="x-combo-list-item">',
								'{KLArea_Name}',
								'</div></tpl>'
							),
							triggerAction: 'all',
							valueField: 'KLArea_id',
							anchor: '-10',
							xtype: 'combo'
						}]
					}]
				}, {
					layout: 'column',
					border: false,
					items: [{
						layout: 'form',
						labelWidth: 100,
						border: false,
						width: 375,
						items: [{
							areaLevel: 0,
							codeField: 'KLCountry_Code',
							disabled: false,
							displayField: 'KLCountry_Name',
							editable: true,
							fieldLabel: lang['strana'],
							hiddenName: 'KLCountry_id',
							listeners: {
								'change': function(combo, newValue, oldValue) {
									var base_form = win.WhsDocumentOrderAllocationGridFilters.getForm();
									
									if ( newValue != null && combo.getRawValue().toString().length > 0 ) {
										loadAddressCombo(
											combo.areaLevel, 
											{
												'Country': base_form.findField('KLCountry_id'),
												'Region': base_form.findField('KLRgn_id'),
												'SubRegion': base_form.findField('KLSubRgn_id'),
												'City': base_form.findField('KLCity_id'),
												'Town': base_form.findField('KLTown_id')
											},
											combo.getValue(),
											combo.getValue(), 
											true
										);
									}
									else {
										clearAddressCombo(
											combo.areaLevel, 
											{
												'Country': base_form.findField('KLCountry_id'),
												'Region': base_form.findField('KLRgn_id'),
												'SubRegion': base_form.findField('KLSubRgn_id'),
												'City': base_form.findField('KLCity_id'),
												'Town': base_form.findField('KLTown_id')
											}
										);
									}
								},
								'keydown': function(combo, e) {
									if ( e.getKey() == e.DELETE ) {
										if ( combo.getRawValue().toString().length > 0 && combo.getValue().toString().length > 0 ) {
											combo.fireEvent('change', combo, null, combo.getValue());
										}
									}
								},
								'select': function(combo, record, index) {
									if ( record.get('KLCountry_id') == combo.getValue() ) {
										combo.collapse();
										return false;
									}
									combo.fireEvent('change', combo, record.get('KLArea_id'), null);
								}
							},
							store: new Ext.db.AdapterStore({
								autoLoad: true,
								dbFile: 'Promed.db',
								fields: [
									{ name: 'KLCountry_id', type: 'int' },
									{ name: 'KLCountry_Code', type: 'int' },
									{ name: 'KLCountry_Name', type: 'string' }
								],
								key: 'KLCountry_id',
								sortInfo: {
									field: 'KLCountry_Name'
								},
								tableName: 'KLCountry'
							}),
							tpl: new Ext.XTemplate(
								'<tpl for="."><div class="x-combo-list-item">',
								'<font color="red">{KLCountry_Code}</font>&nbsp;{KLCountry_Name}',
								'</div></tpl>'
							),
							valueField: 'KLCountry_id',
							anchor: '-10',
							xtype: 'swbaselocalcombo'
						}]
					}, {
						layout: 'form',
						labelWidth: 120,
						border: false,
						width: 375,
						items: [{
							areaLevel: 3,
							disabled: false,
							displayField: 'KLArea_Name',
							enableKeyEvents: true,
							fieldLabel: lang['gorod'],
							hiddenName: 'KLCity_id',
							listeners: {
								'change': function(combo, newValue, oldValue) {
									var base_form = win.WhsDocumentOrderAllocationGridFilters.getForm();
									
									if ( newValue != null && combo.getRawValue().toString().length > 0 ) {
										loadAddressCombo(
											combo.areaLevel, 
											{
												'Country': base_form.findField('KLCountry_id'),
												'Region': base_form.findField('KLRgn_id'),
												'SubRegion': base_form.findField('KLSubRgn_id'),
												'City': base_form.findField('KLCity_id'),
												'Town': base_form.findField('KLTown_id')
											},
											0,
											combo.getValue(), 
											true
										);
									}
									else {
										clearAddressCombo(
											combo.areaLevel, 
											{
												'Country': base_form.findField('KLCountry_id'),
												'Region': base_form.findField('KLRgn_id'),
												'SubRegion': base_form.findField('KLSubRgn_id'),
												'City': base_form.findField('KLCity_id'),
												'Town': base_form.findField('KLTown_id')
											}
										);
									}
								},
								'keydown': function(combo, e) {
									if ( e.getKey() == e.DELETE && combo.getRawValue().toString().length > 0 && combo.getValue().toString().length > 0 ) {
										combo.fireEvent('change', combo, null, combo.getValue());
									}
								},
								'select': function(combo, record, index) {
									if ( record.get('KLArea_id') == combo.getValue() ) {
										combo.collapse();
										return false;
									}
									combo.fireEvent('change', combo, record.get('KLArea_id'));
								}
							},
							minChars: 0,
							mode: 'local',
							queryDelay: 250,
							store: new Ext.data.JsonStore({
								autoLoad: false,
								fields: [
									{ name: 'KLArea_id', type: 'int' },
									{ name: 'KLArea_Name', type: 'string' }
								],
								key: 'KLArea_id',
								sortInfo: {
									field: 'KLArea_Name'
								},
								url: C_LOAD_ADDRCOMBO
							}),
							tpl: new Ext.XTemplate(
								'<tpl for="."><div class="x-combo-list-item">',
								'{KLArea_Name}',
								'</div></tpl>'
							),
							triggerAction: 'all',
							valueField: 'KLArea_id',
							anchor: '-10',
							xtype: 'combo'
						}]
					}]
				}, {
					layout: 'column',
					border: false,
					items: [{
						layout: 'form',
						labelWidth: 100,
						border: false,
						width: 375,
						items: [{
							areaLevel: 1,
							disabled: false,
							displayField: 'KLArea_Name',
							enableKeyEvents: true,
							fieldLabel: lang['region'],
							hiddenName: 'KLRgn_id',
							listeners: {
								'change': function(combo, newValue, oldValue) {
									var base_form = win.WhsDocumentOrderAllocationGridFilters.getForm();
									
									if ( newValue != null && combo.getRawValue().toString().length > 0 ) {
										loadAddressCombo(
											combo.areaLevel, 
											{
												'Country': base_form.findField('KLCountry_id'),
												'Region': base_form.findField('KLRgn_id'),
												'SubRegion': base_form.findField('KLSubRgn_id'),
												'City': base_form.findField('KLCity_id'),
												'Town': base_form.findField('KLTown_id')
											},
											0,
											combo.getValue(), 
											true
										);
									}
									else {
										clearAddressCombo(
											combo.areaLevel, 
											{
												'Country': base_form.findField('KLCountry_id'),
												'Region': base_form.findField('KLRgn_id'),
												'SubRegion': base_form.findField('KLSubRgn_id'),
												'City': base_form.findField('KLCity_id'),
												'Town': base_form.findField('KLTown_id')
											}
										);
									}
								},
								'keydown': function(combo, e) {
									if ( e.getKey() == e.DELETE && combo.getRawValue().toString().length > 0 && combo.getValue().toString().length > 0 ) {
										combo.fireEvent('change', combo, null, combo.getValue());
									}
								},
								'select': function(combo, record, index) {
									if ( record.get('KLArea_id') == combo.getValue() ) {
										combo.collapse();
										return false;
									}
									combo.fireEvent('change', combo, record.get('KLArea_id'));
								}
							},
							minChars: 0,
							mode: 'local',
							queryDelay: 250,
							store: new Ext.data.JsonStore({
								autoLoad: false,
								fields: [
									{ name: 'KLArea_id', type: 'int' },
									{ name: 'KLArea_Name', type: 'string' }
								],
								key: 'KLArea_id',
								sortInfo: {
									field: 'KLArea_Name'
								},
								url: C_LOAD_ADDRCOMBO
							}),
							tpl: new Ext.XTemplate(
								'<tpl for="."><div class="x-combo-list-item">',
								'{KLArea_Name}',
								'</div></tpl>'
							),
							triggerAction: 'all',
							valueField: 'KLArea_id',
							anchor: '-10',
							xtype: 'combo'
						}]
					}, {
						layout: 'form',
						labelWidth: 120,
						border: false,
						width: 375,
						items: [{
							areaLevel: 4,
							disabled: false,
							displayField: 'KLArea_Name',
							enableKeyEvents: true,
							fieldLabel: lang['naselennyiy_punkt'],
							hiddenName: 'KLTown_id',
							listeners: {
								'change': function(combo, newValue, oldValue) {
									var base_form = win.WhsDocumentOrderAllocationGridFilters.getForm();
									
									if ( newValue != null && combo.getRawValue().toString().length > 0 ) {
										loadAddressCombo(
											combo.areaLevel, 
											{
												'Country': base_form.findField('KLCountry_id'),
												'Region': base_form.findField('KLRgn_id'),
												'SubRegion': base_form.findField('KLSubRgn_id'),
												'City': base_form.findField('KLCity_id'),
												'Town': base_form.findField('KLTown_id')
											},
											0,
											combo.getValue(), 
											true
										);
									}
									else {
										clearAddressCombo(
											combo.areaLevel, 
											{
												'Country': base_form.findField('KLCountry_id'),
												'Region': base_form.findField('KLRgn_id'),
												'SubRegion': base_form.findField('KLSubRgn_id'),
												'City': base_form.findField('KLCity_id'),
												'Town': base_form.findField('KLTown_id')
											}
										);
									}
								},
								'keydown': function(combo, e) {
									if ( e.getKey() == e.DELETE && combo.getRawValue().toString().length > 0 && combo.getValue().toString().length > 0 ) {
										combo.fireEvent('change', combo, null, combo.getValue());
									}
								},
								'select': function(combo, record, index) {
									if ( record.get('KLArea_id') == combo.getValue() ) {
										combo.collapse();
										return false;
									}
									combo.fireEvent('change', combo, record.get('KLArea_id'));
								}
							},
							minChars: 0,
							mode: 'local',
							queryDelay: 250,
							store: new Ext.data.JsonStore({
								autoLoad: false,
								fields: [
									{ name: 'KLArea_id', type: 'int' },
									{ name: 'KLArea_Name', type: 'string' }
								],
								key: 'KLArea_id',
								sortInfo: {
									field: 'KLArea_Name'
								},
								url: C_LOAD_ADDRCOMBO
							}),
							tpl: new Ext.XTemplate(
								'<tpl for="."><div class="x-combo-list-item">',
								'{KLArea_Name}',
								'</div></tpl>'
							),
							triggerAction: 'all',
							valueField: 'KLArea_id',
							anchor: '-10',
							xtype: 'combo'
						}]
					}, {
						layout: 'form',
						border: false,
						items: [{
							style: "padding-left: 0px",
							xtype: 'button',
							text: lang['poisk'],
							iconCls: 'search16',
							handler: function() {
								win.doFilterWhsDocumentOrderAllocationGrid();
							}
						}]
					}, {
						layout: 'form',
						border: false,
						items: [{
							style: "padding-left: 10px",
							xtype: 'button',
							text: lang['sbros'],
							iconCls: 'resetsearch16',
							handler: function() {
								var base_form = win.WhsDocumentOrderAllocationGridFilters.getForm();
								base_form.reset();
								win.doFilterWhsDocumentOrderAllocationGrid();
							}
						}]
					}]
				}]
			}]
		});
		
		this.WhsDocumentOrderAllocationGridFilters = new Ext.FormPanel({
			autoHeight: true,
			region: 'north',
			border: false,
			layout: 'form',
			items: [{
				listeners: {
					collapse: function(p) {
						win.doLayout();
					},
					expand: function(p) {
						win.doLayout();
					}
				},
				title: lang['najmite_na_zagolovok_chtobyi_svernut_razvernut_panel_filtrov'],
				titleCollapse: true,
				collapsible: true,
				animCollapse: false,
				floatable: false,
				autoHeight: true,
				labelWidth: 80,
				layout: 'form',
				border: false,
				defaults:{bodyStyle:'background:#DFE8F6;'}, 
				items:
				[
					this.WhsDocumentOrderAllocationGridFiltersTab
				]
			}]
		});
		
		this.WhsDocumentOrderAllocationFrame = new Ext.Panel({
			region: 'north',
			height: 300,
			layout: 'border',
			border: false,
			items:
			[
				this.WhsDocumentOrderAllocationGridFilters,
				this.WhsDocumentOrderAllocationGrid
			]
		});
		
		this.WhsDocumentOrderAllocationDrugGridFilters = new Ext.FormPanel({
			xtype: 'form',
			region: 'north',
			height: 35,
			bodyStyle: 'padding:5px;',
			labelAlign: 'right',
			items: [{
				layout: 'column',
				labelWidth: 40,
				border: false,
				items: [{
					layout: 'form',
					border: false,
					width: 375,
					items: [{
						xtype: 'swdrugcomplexmnncombo',									
						fieldLabel: lang['mnn'],
						width: 300,
						value: '',
						allowBlank: true
					}]
				}, {
					layout: 'form',
					border: false,
					width: 375,
					items: [{
						xtype: 'swdrugsimplecombo',
						fieldLabel : lang['torg'],
						width: 300,
						name: 'Drug_id',
						value: '',
						allowBlank: true
					}]
				}, {
					layout: 'form',
					border: false,
					items: [{
						style: "padding-left: 0px",
						xtype: 'button',
						text: lang['poisk'],
						iconCls: 'search16',
						handler: function() {
							win.doFilterWhsDocumentOrderAllocationDrugGrid();
						}
					}]
				}, {
					layout: 'form',
					border: false,
					items: [{
						style: "padding-left: 10px",
						xtype: 'button',
						text: lang['sbros'],
						iconCls: 'resetsearch16',
						handler: function() {
							var form = win.WhsDocumentOrderAllocationDrugGridFilters.getForm();
							base_form.reset();
							win.doFilterWhsDocumentOrderAllocationDrugGrid();
						}
					}]
				}]
			}]	
		});
		
		this.WhsDocumentOrderAllocationDrugFrame = new Ext.Panel({
			region: 'center',
			layout: 'border',
			title: lang['lekarstvennyie_sredstva_raznaryadki'],
			border: false,
			items:
			[
				this.WhsDocumentOrderAllocationDrugGridFilters,
				this.WhsDocumentOrderAllocationDrugGrid
			]
		});
				
		this.formPanel = new Ext.Panel(
		{
			region: 'center',
			layout: 'border',
			labelWidth: 50,
			border: false,
			items:
			[
				this.WhsDocumentOrderAllocationFrame,
				this.WhsDocumentOrderAllocationDrugFrame
			]
		});
		
		Ext.apply(this, 
		{
			items: 
			[ 
				win.formPanel
			],
			buttons:
			[{
				text: '-'
			},
			HelpButton(this, TABINDEX_WDOASW + 13),
			{
				iconCls: 'close16',
				tabIndex: TABINDEX_WDOASW + 14,
				onTabAction: function()
				{
					// win.filtersPanel.getForm().findField('RegistryRecept_Snils').focus();
				},
				handler: function() {
					win.hide();
				},
				text: BTN_FRMCLOSE
			}]
		});
		sw.Promed.swWhsDocumentOrderAllocationSearchWindow.superclass.initComponent.apply(this, arguments);
	},
	show: function() {
		sw.Promed.swWhsDocumentOrderAllocationSearchWindow.superclass.show.apply(this, arguments);

        this.mode = null;
        if (arguments[0] && arguments[0].mode) {
            this.mode = arguments[0].mode;
        }

		this.doFilterWhsDocumentOrderAllocationGrid();
	}
});