/**
* swTagSelectWindow - окно выбора бирки.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage1981@gmail.com)
* @version      0.001-22.12.2009
* @comment      Префикс для id компонентов TagSelW (TagSelectWindow)
*/

sw.Promed.swTagSelectWindow = Ext.extend(sw.Promed.BaseForm, {
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	collapsible: true,
	draggable: true,
	doSelect: function() {
		// Выбор бирки
	},
	height: 500,
	id: 'TagSelectWindow',
	initComponent: function() {
		var lpuGridStore = new Ext.data.Store({
			autoLoad: false,
			listeners: {
				'load': function(store, records, options) {
					var grid = this.findById('TagSelW_LpuGrid');
					
					if ( store.getCount() > 0 ) {
						grid.getView().focusRow(0);
						grid.getSelectionModel().selectFirstRow();
					}
				}.createDelegate(this)
			},
			reader: new Ext.data.JsonReader({
				id: 'Lpu_id',
				root: 'data',
				totalProperty: 'totalCount'
			}, [{
				mapping: 'Lpu_id',
				name: 'Lpu_id',
				type: 'int'
			}, {
				mapping: 'Lpu_Nick',
				name: 'Lpu_Nick',
				type: 'string'
			}, {
				mapping: 'Lpu_Name',
				name: 'Lpu_Name',
				type: 'string'
			}]),
			url: '/?c=EvnDirection&m=loadLpuGrid'
		});

		var lpuBuildingGridStore = new Ext.data.Store({
			autoLoad: false,
			listeners: {
				'load': function(store, records, options) {
					var grid = this.findById('TagSelW_LpuBuildingGrid');
					
					if ( store.getCount() > 0 ) {
						grid.getView().focusRow(0);
						grid.getSelectionModel().selectFirstRow();
					}
				}.createDelegate(this)
			},
			reader: new Ext.data.JsonReader({
				id: 'LpuBuilding_id',
				root: 'data',
				totalProperty: 'totalCount'
			}, [{
				mapping: 'LpuBuilding_id',
				name: 'LpuBuilding_id',
				type: 'int'
			}, {
				mapping: 'LpuBuilding_Name',
				name: 'LpuBuilding_Name',
				type: 'string'
			}]),
			url: '/?c=EvnDirection&m=loadLpuBuildingGrid'
		});

		var lpuUnitGridStore = new Ext.data.Store({
			autoLoad: false,
			listeners: {
				'load': function(store, records, options) {
					var grid = this.findById('TagSelW_LpuUnitGrid');
					
					if ( store.getCount() > 0 ) {
						grid.getView().focusRow(0);
						grid.getSelectionModel().selectFirstRow();
					}
				}.createDelegate(this)
			},
			reader: new Ext.data.JsonReader({
				id: 'LpuUnit_id',
				root: 'data',
				totalProperty: 'totalCount'
			}, [{
				mapping: 'LpuUnit_id',
				name: 'LpuUnit_id',
				type: 'int'
			}, {
				mapping: 'LpuUnit_Name',
				name: 'LpuUnit_Name',
				type: 'string'
			}]),
			url: '/?c=EvnDirection&m=loadLpuUnitGrid'
		});

		var medStaffFactGridStore = new Ext.data.Store({
			autoLoad: false,
			listeners: {
				'load': function(store, records, options) {
					var grid = this.findById('TagSelW_MedStaffFactGrid');
					
					if ( store.getCount() > 0 ) {
						grid.getView().focusRow(0);
						grid.getSelectionModel().selectFirstRow();
					}
				}.createDelegate(this)
			},
			reader: new Ext.data.JsonReader({
				id: 'MedStaffFact_id',
				root: 'data',
				totalProperty: 'totalCount'
			}, [{
				mapping: 'MedStaffFact_id',
				name: 'MedStaffFact_id',
				type: 'int'
			}, {
				mapping: 'LpuSectionProfile_id',
				name: 'LpuSectionProfile_id',
				type: 'int'
			}, {
				mapping: 'MedPersonal_Fio',
				name: 'MedPersonal_Fio',
				type: 'string'
			}, {
				mapping: 'LpuSection_Name',
				name: 'LpuSection_Name',
				type: 'string'
			}, {
				mapping: 'LpuSectionProfile_Name',
				name: 'LpuSectionProfile_Name',
				type: 'string'
			}, {
				mapping: 'LpuRegion_Name',
				name: 'LpuRegion_Name',
				type: 'string'
			}]),
			url: '/?c=EvnDirection&m=loadMedStaffFactGrid'
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.doSelect();
				}.createDelegate(this),
				iconCls: 'ok16',
				// tabIndex: TABINDEX_EDIREF + 15,
				text: lang['ok']
			}, {
				text: '-'
			},
			HelpButton(this/*, TABINDEX_EDIREF + 16*/),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'close16',
				onShiftTabAction: function () {
					this.buttons[this.buttons.length - 2].focus();
				}.createDelegate(this),
				onTabAction: function () {
					//
				}.createDelegate(this),
				// tabIndex: TABINDEX_EDIREF + 17,
				text: BTN_FRMCLOSE
			}],
			items: [ new Ext.TabPanel({
				activeTab: 0,
				border: false,
				defaults: { bodyStyle: 'padding: 0px' },
				id: 'TagSelW_TagSelectTabPanel',
				layoutOnTabChange: true,
				listeners: {
					'tabchange': function(panel, tab) {
						//
					}
				},
				plain: true,
				region: 'center',
				items: [{
					border: false,
					layout: 'border',
					listeners: {
						'activate': function(panel) {
							//
						}.createDelegate(this)
					},
					title: lang['1_lpu'],

					items: [ new Ext.grid.GridPanel({
						autoExpandColumn: 'autoexpand_lpu',
						autoExpandMin: 300,
						bbar: new Ext.PagingToolbar({
							displayInfo: true,
							displayMsg: lang['otobrajaemyie_stroki_{0}_-_{1}_iz_{2}'],
							emptyMsg: "Нет записей для отображения",
							pageSize: 100,
							store: lpuGridStore
						}),
						border: false,
						columns: [{
							dataIndex: 'Lpu_Nick',
							header: lang['sokraschennoe_naimenovanie'],
							hidden: false,
							sortable: true,
							width: 300
						}, {
							dataIndex: 'Lpu_Name',
							header: lang['polnoe_naimenovanie'],
							hidden: false,
							id: 'autoexpand_lpu',
							sortable: true
						}],
						id: 'TagSelW_LpuGrid',
						keys: [{
							key: [
								Ext.EventObject.END,
								Ext.EventObject.ENTER,
								Ext.EventObject.HOME,
								Ext.EventObject.PAGE_DOWN,
								Ext.EventObject.PAGE_UP,
								Ext.EventObject.TAB
							],
							fn: function(inp, e) {
								e.stopEvent();

								if ( e.browserEvent.stopPropagation ) {
									e.browserEvent.stopPropagation();
								}
								else {
									e.browserEvent.cancelBubble = true;
								}

								if ( e.browserEvent.preventDefault ) {
									e.browserEvent.preventDefault();
								}

								e.browserEvent.returnValue = false;
								e.returnValue = false;

								if ( Ext.isIE ) {
									e.browserEvent.keyCode = 0;
									e.browserEvent.which = 0;
								}

								var grid = this.findById('TagSelW_LpuGrid');

								switch ( e.getKey() ) {
									case Ext.EventObject.END:
										GridEnd(grid);
									break;

									case Ext.EventObject.ENTER:
										if ( !grid.getSelectionModel().getSelected() ) {
											return false;
										}

										grid.fireEvent('rowdblclick', grid);
									break;

									case Ext.EventObject.HOME:
										GridHome(grid);
									break;

									case Ext.EventObject.PAGE_DOWN:
										GridPageDown(grid);
									break;

									case Ext.EventObject.PAGE_UP:
										GridPageUp(grid);
									break;
		 
									case Ext.EventObject.TAB:
										this.buttons[0].focus(false, 100);
									break;
								}
							}.createDelegate(this),
							stopEvent: true
						}],
						listeners: {
							'rowdblclick': function(grid, number, obj) {
								// Выбор значения
								this.selectGridValue({
									id: grid.getSelectionModel().getSelected().get('Lpu_id'),
									tabId: 0
								});
							}.createDelegate(this)
						},
						loadMask: true,
						region: 'center',
						sm: new Ext.grid.RowSelectionModel({
							listeners: {
								'rowselect': function(sm, rowIndex, record) {
									//
								}
							}
						}),
						store: lpuGridStore,
						stripeRows: true
					})]
				}, {
					border: false,
					layout: 'border',
					listeners: {
						'activate': function(panel) {
							//
						}.createDelegate(this)
					},
					title: lang['2_gruppa_podrazdeleniy'],

					items: [ new Ext.grid.GridPanel({
						autoExpandColumn: 'autoexpand_lpubuilding',
						autoExpandMin: 300,
						bbar: new Ext.PagingToolbar({
							displayInfo: true,
							displayMsg: lang['otobrajaemyie_stroki_{0}_-_{1}_iz_{2}'],
							emptyMsg: "Нет записей для отображения",
							pageSize: 100,
							store: lpuBuildingGridStore
						}),
						border: false,
						columns: [{
							dataIndex: 'LpuBuilding_Name',
							header: lang['naimenovanie'],
							hidden: false,
							id: 'autoexpand_lpubuilding',
							sortable: true
						}],
						id: 'TagSelW_LpuBuildingGrid',
						keys: [{
							key: [
								Ext.EventObject.END,
								Ext.EventObject.ENTER,
								Ext.EventObject.HOME,
								Ext.EventObject.PAGE_DOWN,
								Ext.EventObject.PAGE_UP,
								Ext.EventObject.TAB
							],
							fn: function(inp, e) {
								e.stopEvent();

								if ( e.browserEvent.stopPropagation ) {
									e.browserEvent.stopPropagation();
								}
								else {
									e.browserEvent.cancelBubble = true;
								}

								if ( e.browserEvent.preventDefault ) {
									e.browserEvent.preventDefault();
								}

								e.browserEvent.returnValue = false;
								e.returnValue = false;

								if ( Ext.isIE ) {
									e.browserEvent.keyCode = 0;
									e.browserEvent.which = 0;
								}

								var grid = this.findById('TagSelW_LpuBuildingGrid');

								switch ( e.getKey() ) {
									case Ext.EventObject.END:
										GridEnd(grid);
									break;

									case Ext.EventObject.ENTER:
										if ( !grid.getSelectionModel().getSelected() ) {
											return false;
										}

										grid.fireEvent('rowdblclick', grid);
									break;

									case Ext.EventObject.HOME:
										GridHome(grid);
									break;

									case Ext.EventObject.PAGE_DOWN:
										GridPageDown(grid);
									break;

									case Ext.EventObject.PAGE_UP:
										GridPageUp(grid);
									break;
		 
									case Ext.EventObject.TAB:
										this.buttons[0].focus(false, 100);
									break;
								}
							}.createDelegate(this),
							stopEvent: true
						}],
						listeners: {
							'rowdblclick': function(grid, number, obj) {
								// Выбор значения
								this.selectGridValue({
									id: grid.getSelectionModel().getSelected().get('LpuBuilding_id'),
									tabId: 1
								});
							}.createDelegate(this)
						},
						loadMask: true,
						region: 'center',
						sm: new Ext.grid.RowSelectionModel({
							listeners: {
								'rowselect': function(sm, rowIndex, record) {
									//
								}
							}
						}),
						store: lpuBuildingGridStore,
						stripeRows: true
					})]
				}, {
					border: false,
					layout: 'border',
					listeners: {
						'activate': function(panel) {
							//
						}.createDelegate(this)
					},
					title: lang['3_podrazdelenie'],

					items: [ new Ext.grid.GridPanel({
						autoExpandColumn: 'autoexpand_lpuunit',
						autoExpandMin: 300,
						bbar: new Ext.PagingToolbar({
							displayInfo: true,
							displayMsg: lang['otobrajaemyie_stroki_{0}_-_{1}_iz_{2}'],
							emptyMsg: "Нет записей для отображения",
							pageSize: 100,
							store: lpuUnitGridStore
						}),
						border: false,
						columns: [{
							dataIndex: 'LpuUnit_Name',
							header: lang['naimenovanie'],
							hidden: false,
							id: 'autoexpand_lpuunit',
							sortable: true
						}],
						id: 'TagSelW_LpuUnitGrid',
						keys: [{
							key: [
								Ext.EventObject.END,
								Ext.EventObject.ENTER,
								Ext.EventObject.HOME,
								Ext.EventObject.PAGE_DOWN,
								Ext.EventObject.PAGE_UP,
								Ext.EventObject.TAB
							],
							fn: function(inp, e) {
								e.stopEvent();

								if ( e.browserEvent.stopPropagation ) {
									e.browserEvent.stopPropagation();
								}
								else {
									e.browserEvent.cancelBubble = true;
								}

								if ( e.browserEvent.preventDefault ) {
									e.browserEvent.preventDefault();
								}

								e.browserEvent.returnValue = false;
								e.returnValue = false;

								if ( Ext.isIE ) {
									e.browserEvent.keyCode = 0;
									e.browserEvent.which = 0;
								}

								var grid = this.findById('TagSelW_LpuUnitGrid');

								switch ( e.getKey() ) {
									case Ext.EventObject.END:
										GridEnd(grid);
									break;

									case Ext.EventObject.ENTER:
										if ( !grid.getSelectionModel().getSelected() ) {
											return false;
										}

										grid.fireEvent('rowdblclick', grid);
									break;

									case Ext.EventObject.HOME:
										GridHome(grid);
									break;

									case Ext.EventObject.PAGE_DOWN:
										GridPageDown(grid);
									break;

									case Ext.EventObject.PAGE_UP:
										GridPageUp(grid);
									break;
		 
									case Ext.EventObject.TAB:
										this.buttons[0].focus(false, 100);
									break;
								}
							}.createDelegate(this),
							stopEvent: true
						}],
						listeners: {
							'rowdblclick': function(grid, number, obj) {
								// Выбор значения
								this.selectGridValue({
									id: grid.getSelectionModel().getSelected().get('LpuUnit_id'),
									tabId: 2
								});
							}.createDelegate(this)
						},
						loadMask: true,
						region: 'center',
						sm: new Ext.grid.RowSelectionModel({
							listeners: {
								'rowselect': function(sm, rowIndex, record) {
									//
								}
							}
						}),
						store: lpuUnitGridStore,
						stripeRows: true
					})]
				}, {
					border: false,
					layout: 'border',
					listeners: {
						'activate': function(panel) {
							//
						}.createDelegate(this)
					},
					title: lang['4_vrach'],

					items: [ new Ext.grid.GridPanel({
						autoExpandColumn: 'autoexpand_medstafffact',
						autoExpandMin: 150,
						bbar: new Ext.PagingToolbar({
							displayInfo: true,
							displayMsg: lang['otobrajaemyie_stroki_{0}_-_{1}_iz_{2}'],
							emptyMsg: "Нет записей для отображения",
							pageSize: 100,
							store: medStaffFactGridStore
						}),
						border: false,
						columns: [{
							dataIndex: 'MedPersonal_Fio',
							header: lang['fio'],
							hidden: false,
							id: 'autoexpand_medstafffact',
							sortable: true
						}, {
							dataIndex: 'LpuSection_Name',
							header: lang['otdelenie'],
							hidden: false,
							sortable: true,
							width: 250
						}, {
							dataIndex: 'LpuRegion_Name',
							header: lang['uchastok'],
							hidden: false,
							sortable: true,
							width: 150
						}, {
							dataIndex: 'LpuSectionProfile_Name',
							header: lang['profil'],
							hidden: false,
							sortable: true,
							width: 150
						}],
						id: 'TagSelW_MedStaffFactGrid',
						keys: [{
							key: [
								Ext.EventObject.END,
								Ext.EventObject.ENTER,
								Ext.EventObject.HOME,
								Ext.EventObject.PAGE_DOWN,
								Ext.EventObject.PAGE_UP,
								Ext.EventObject.TAB
							],
							fn: function(inp, e) {
								e.stopEvent();

								if ( e.browserEvent.stopPropagation ) {
									e.browserEvent.stopPropagation();
								}
								else {
									e.browserEvent.cancelBubble = true;
								}

								if ( e.browserEvent.preventDefault ) {
									e.browserEvent.preventDefault();
								}

								e.browserEvent.returnValue = false;
								e.returnValue = false;

								if ( Ext.isIE ) {
									e.browserEvent.keyCode = 0;
									e.browserEvent.which = 0;
								}

								var grid = this.findById('TagSelW_MedStaffFactGrid');

								switch ( e.getKey() ) {
									case Ext.EventObject.END:
										GridEnd(grid);
									break;

									case Ext.EventObject.ENTER:
										if ( !grid.getSelectionModel().getSelected() ) {
											return false;
										}

										// rowdblclick
									break;

									case Ext.EventObject.HOME:
										GridHome(grid);
									break;

									case Ext.EventObject.PAGE_DOWN:
										GridPageDown(grid);
									break;

									case Ext.EventObject.PAGE_UP:
										GridPageUp(grid);
									break;
		 
									case Ext.EventObject.TAB:
										this.buttons[0].focus(false, 100);
									break;
								}
							}.createDelegate(this),
							stopEvent: true
						}],
						listeners: {
							'rowdblclick': function(grid, number, obj) {
								// Выбор значения
								this.selectGridValue({
									id: grid.getSelectionModel().getSelected().get('MedStaffFact_id'),
									tabId: 4
								});
							}.createDelegate(this)
						},
						loadMask: true,
						region: 'center',
						sm: new Ext.grid.RowSelectionModel({
							listeners: {
								'rowselect': function(sm, rowIndex, record) {
									// this.buttons[0].enable();
								}.createDelegate(this)
							}
						}),
						store: medStaffFactGridStore,
						stripeRows: true
					})]
				}, {
					border: false,
					layout: 'border',
					listeners: {
						'activate': function(panel) {
							//
						}.createDelegate(this)
					},
					title: lang['5_birki'],

					items: [
/*
							listeners: {
								'rowselect': function(sm, rowIndex, record) {
									// this.buttons[0].enable();
								}
							}.createDelegate(this)
*/
					]
				}]
			})]
		});
		sw.Promed.swTagSelectWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			e.stopEvent();

			if ( e.browserEvent.stopPropagation ) {
				e.browserEvent.stopPropagation();
			}
			else {
				e.browserEvent.cancelBubble = true;
			}

			if ( e.browserEvent.preventDefault ) {
				e.browserEvent.preventDefault();
			}
			else {
				e.browserEvent.returnValue = false;
			}

			e.returnValue = false;

			if ( Ext.isIE ) {
				e.browserEvent.keyCode = 0;
				e.browserEvent.which = 0;
			}

			if ( e.getKey() == Ext.EventObject.P ) {
				this.hide();
			}
		},
		key: [ Ext.EventObject.P ],
		scope: this,
		stopEvent: false
	}],
	layout: 'border',
	listeners: {
		'hide': function() {
			this.findById('TagSelW_LpuGrid').getStore().removeAll();
			this.findById('TagSelW_LpuBuildingGrid').getStore().removeAll();
			this.findById('TagSelW_LpuUnitGrid').getStore().removeAll();
			this.findById('TagSelW_MedStaffFactGrid').getStore().removeAll();

			this.onHide();
		}
	},
	maximizable: true,
	minHeight: 500,
	minWidth: 800,
	modal: true,
	onHide: Ext.emptyFn,
	plain: true,
	resizable: true,
	selectGridValue: function(params) {
		var grid_list = new Array(
			'TagSelW_LpuBuildingGrid',
			'TagSelW_LpuUnitGrid',
			'TagSelW_MedStaffFactGrid'
		);
		var i;

		this.findById('TagSelW_TagSelectTabPanel').unhideTabStripItem(params.tabId + 1);
		this.findById('TagSelW_TagSelectTabPanel').setActiveTab(params.tabId + 1);
		this.findById(grid_list[params.tabId]).getStore().removeAll();
		this.findById(grid_list[params.tabId]).getStore().load({
			params: {
				id: params.id,
				limit: 100,
				start: 0
			}
		});

		for ( i = params.tabId + 1; i < 4; i++ ) {
			this.findById('TagSelW_TagSelectTabPanel').hideTabStripItem(i + 1);
		}
	},
	show: function() {
		sw.Promed.swTagSelectWindow.superclass.show.apply(this, arguments);

		this.restore();
		this.center();

		this.buttons[0].disable();

		this.callback = Ext.emptyFn;
		this.onHide = Ext.emptyFn;

		if ( !arguments[0] ) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi']);
			return false;
		}

		if ( arguments[0].callback ) {
			this.callback = arguments[0].callback;
		}

		if ( arguments[0].onHide ) {
			this.onHide = arguments[0].onHide;
		}

		this.findById('TagSelW_TagSelectTabPanel').setActiveTab(0);

		this.findById('TagSelW_TagSelectTabPanel').hideTabStripItem(4);
		this.findById('TagSelW_TagSelectTabPanel').hideTabStripItem(3);
		this.findById('TagSelW_TagSelectTabPanel').hideTabStripItem(2);
		this.findById('TagSelW_TagSelectTabPanel').hideTabStripItem(1);

		this.findById('TagSelW_LpuGrid').getStore().removeAll();
		this.findById('TagSelW_LpuBuildingGrid').getStore().removeAll();
		this.findById('TagSelW_LpuUnitGrid').getStore().removeAll();
		this.findById('TagSelW_MedStaffFactGrid').getStore().removeAll();

		// Загрузить список ЛПУ
		this.findById('TagSelW_LpuGrid').getStore().load({
			params: {
				limit: 100,
				start: 0
			}
		});
	},
	title: lang['vyibor_birki_[_v_razrabotke_]'],
	width: 800
});