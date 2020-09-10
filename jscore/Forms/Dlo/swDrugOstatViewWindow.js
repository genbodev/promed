/**
* swDrugOstatViewWindow - окно просмотра остатков по медикаментам в аптеках.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
* @package      DLO
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Pshenicyn Ivan aka IVP (ipshon@rambler.ru)
* @version      12.03.2009
*/

sw.Promed.swDrugOstatViewWindow = Ext.extend(sw.Promed.BaseForm, {
	layout      : 'border',
	maximized: true,
    modal: true,
	resizable: false,
	draggable: false,
    closeAction :'hide',
    plain       : true,
    title: WND_DLO_MEDNAME,
	id: 'DrugOstatViewWindow',
	refreshDrugOstatUpdateTime: function() {
		/*var current_window = this;
		var loadMask = new Ext.LoadMask(Ext.get('DrugOstatViewWindow'), { msg: "Получение последней даты обновления остатков..." });
		loadMask.show();
		Ext.Ajax.request({
			url: C_DRUG_UPD_TIME,
			callback: function(opt, success, resp) {
				loadMask.hide();
				if (resp.responseText != '')
				{
					var response_data = Ext.util.JSON.decode(resp.responseText);
					if (response_data && response_data[0]['DrugOstatUpdateTime'])
						current_window.setTitle(WND_DLO_MEDNAME + ' (Обновлено: ' + response_data[0]['DrugOstatUpdateTime'] + ')');
				}
			}
		});*/
	},
	show: function() {
		sw.Promed.swDrugOstatViewWindow.superclass.show.apply(this, arguments);
		//this.refreshDrugOstatUpdateTime();
		var grid = this.findById('DrugGrid');
		Ext.getCmp('DOVW_SkladOstatLabel').setText(lang['ostatki_na_aptechnom_sklade_fed_reg_7_noz_medikament_ne_vyibran']);
		Ext.getCmp('DOVW_WhsDocumentCostItemType').getStore().load();
				
		// чистим таблицу остатков
		var detail_grid = Ext.getCmp('DrugOstatGridDetail');
		detail_grid.getStore().removeAll();

		if (arguments[0] && arguments[0].mode) {
			this.mode = arguments[0].mode;
		} else {
			this.mode = null
		}

		switch (this.mode) {
			case 'DrugOstatView':
				if (Ext.isEmpty(arguments[0].Drug_id)) {
					Ext.Msg.alert('Ошибка', 'Не передан идентификатор ЛС', this.hide());
					return false;
				}
				Ext.getCmp('Drug_id').setValue(arguments[0].Drug_id);
				if (arguments[0].ReceptFinance_id) {
					Ext.getCmp('ReceptFinance_id').setValue(arguments[0].ReceptFinance_id);
				}
				if (arguments[0].OrgFarmacy_id) {
					Ext.getCmp('OrgFarmacy_id').setValue(arguments[0].OrgFarmacy_id);
				}
				if (arguments[0].OrgFarmacy_oid) {
					Ext.getCmp('OrgFarmacy_oid').setValue(arguments[0].OrgFarmacy_oid);
				}
				Ext.getCmp('DOVW_SkladOstatLabel').hide();
				this.setTitle(WND_DRGOST);
				if (arguments[0] && arguments[0].DrugMnn_Name) {
					Ext.getCmp('FindField').setValue(arguments[0].DrugMnn_Name);
				}
				Ext.getCmp('FindField').disable();

				if (arguments[0] && arguments[0].Drug_Name) {
					Ext.getCmp('FindTorgField').setValue(arguments[0].Drug_Name);
				}
				Ext.getCmp('FindTorgField').disable();

				detail_grid.getColumnModel().setHidden(detail_grid.getColumnModel().findColumnIndex('DrugOstat_Fed'), true);
				detail_grid.getColumnModel().setHidden(detail_grid.getColumnModel().findColumnIndex('DrugOstat_Reg'), true);
				detail_grid.getColumnModel().setHidden(detail_grid.getColumnModel().findColumnIndex('DrugOstat_7Noz'), true);
				detail_grid.getColumnModel().setHidden(detail_grid.getColumnModel().findColumnIndex('OrgFarmacy_IsVkl'), true);
				detail_grid.getColumnModel().setHidden(detail_grid.getColumnModel().findColumnIndex('DrugOstat_setDT'), (!(isSuperAdmin() || haveArmType('minzdravdlo')) || getRegionNick() == 'ufa'));
				detail_grid.getColumnModel().setHidden(detail_grid.getColumnModel().findColumnIndex('DrugOstat_updDT'), (!(isSuperAdmin() || haveArmType('minzdravdlo')) || getRegionNick() == 'ufa'));
				detail_grid.getColumnModel().setHidden(detail_grid.getColumnModel().findColumnIndex('Spros'), false);
				detail_grid.getColumnModel().setHidden(detail_grid.getColumnModel().findColumnIndex('DrugOstat_Kolvo'), false);
				detail_grid.getColumnModel().setHidden(detail_grid.getColumnModel().findColumnIndex('ReceptFinance_Name'), false);

				grid.getTopToolbar().items.items[11].disable();
				grid.getTopToolbar().items.items[10].handler();
				break;
			default:
				this.setTitle(WND_DLO_MEDNAME);
				Ext.getCmp('DOVW_SkladOstatLabel').show();
				
				detail_grid.getColumnModel().setHidden(detail_grid.getColumnModel().findColumnIndex('DrugOstat_Fed'), false);
				detail_grid.getColumnModel().setHidden(detail_grid.getColumnModel().findColumnIndex('DrugOstat_Reg'), false);
				detail_grid.getColumnModel().setHidden(detail_grid.getColumnModel().findColumnIndex('DrugOstat_7Noz'), false);
				detail_grid.getColumnModel().setHidden(detail_grid.getColumnModel().findColumnIndex('OrgFarmacy_IsVkl'), false);

				detail_grid.getColumnModel().setHidden(detail_grid.getColumnModel().findColumnIndex('DrugOstat_setDT'), (!(isSuperAdmin() || haveArmType('minzdravdlo')) || getRegionNick() == 'ufa'));
				detail_grid.getColumnModel().setHidden(detail_grid.getColumnModel().findColumnIndex('DrugOstat_updDT'), (!(isSuperAdmin() || haveArmType('minzdravdlo')) || getRegionNick() == 'ufa'));
				detail_grid.getColumnModel().setHidden(detail_grid.getColumnModel().findColumnIndex('freeOstat'), true);
				detail_grid.getColumnModel().setHidden(detail_grid.getColumnModel().findColumnIndex('Spros'), true);
				detail_grid.getColumnModel().setHidden(detail_grid.getColumnModel().findColumnIndex('DrugOstat_Kolvo'), true);
				detail_grid.getColumnModel().setHidden(detail_grid.getColumnModel().findColumnIndex('ReceptFinance_Name'), true);

				grid.getTopToolbar().items.items[11].enable();
				grid.getTopToolbar().items.items[11].handler();
				break;
		}
		if(getRegionNick() == 'ufa')
			Ext.getCmp('DOVW_SkladOstatLabel').hide();
	},
	initComponent: function() {
		var _this = this;
		var gridStore = new Ext.data.JsonStore({
			autoLoad: false,
    	    root: 'data',
	        totalProperty: 'totalCount',
			url: C_DRUG_LIST,
			fields: [
				'Drug_id',
				'Drug_Name',
				'Drug_CodeG',
				'DrugMnn_Name'
			],
			listeners: {
				'load': function(store) {
					var grid = Ext.getCmp('DrugGrid');
					grid.getTopToolbar().items.items[11].el.innerHTML = '0 / ' + store.getCount();
					grid.getSelectionModel().selectFirstRow();
				}
			}
		});
		Ext.apply(this, {
			items: [
				new Ext.grid.GridPanel({
					autoExpandColumn: 'autoexpand',
					region: 'center',
					title: lang['medikament'],
					id: 'DrugGrid',
					loadMask: true,
					autoExpandMax: 2000,
					stripeRows: true,
					store: gridStore,
					tbar: new sw.Promed.Toolbar({
						autoHeight: true,
						items: [{
	                    	xtype: 'hidden',
							id: 'Drug_id'
						},{
	                    	xtype: 'hidden',
							id: 'ReceptFinance_id'
						},{
							xtype: 'label',
							text: lang['mnn'],
							style: 'margin-left: 5px; font-weight: bold'
						},{
	                    	xtype: 'textfield',
							id: 'FindField',
							style: 'margin-left: 5px',
							width: 150,
							enableKeyEvents: true,
							listeners: {
								'keyup': function(field, e){
									if ( e.getKey() == Ext.EventObject.ENTER )
										field.ownerCt.items.item('FindAction').handler();
								},
								'keydown': function (inp, e) {
                                    if (e.shiftKey == false && e.getKey() == Ext.EventObject.TAB)
                                    {
   										e.stopEvent();
										inp.ownerCt.items.item('FindTorgField').focus();
									}
									if (e.shiftKey == true && e.getKey() == Ext.EventObject.TAB)
                                    {
   										e.stopEvent();
										Ext.getCmp('DOVW_CloseButton').focus();
									}
								}
							}

						}, {
							xtype: 'label',
							text: lang['torg_naim'],
							style: 'margin-left: 5px; font-weight: bold'
						}, {
	                    	xtype: 'textfield',
							id: 'FindTorgField',
							style: 'margin-left: 5px',
							width: 150,
							enableKeyEvents: true,
							listeners: {
								'keyup': function(field, e){
									if ( e.getKey() == Ext.EventObject.ENTER )
										field.ownerCt.items.item('FindAction').handler();
								},
								'keydown': function (inp, e) {
                                    if (e.shiftKey == false && e.getKey() == Ext.EventObject.TAB)
                                    {
   										e.stopEvent();
										inp.ownerCt.items.item('OrgFarmFilter').focus();
									}
									if (e.shiftKey == true && e.getKey() == Ext.EventObject.TAB)
                                    {
   										e.stopEvent();
										Ext.getCmp('FindField').focus();
									}
								}
							}

						},{
							xtype: 'label',
							text: lang['apteka'],
							style: 'margin-left: 5px; font-weight: bold'
						},{
	                    	xtype: 'textfield',
							id: 'OrgFarmFilter',
							style: 'margin-left: 5px',
							width: 150,
							enableKeyEvents: true,
							listeners: {
								'keydown': function (inp, e) {
									if (e.shiftKey == false && e.getKey() == Ext.EventObject.TAB)
                                    {
   										e.stopEvent();
										inp.ownerCt.items.item('FindAction').focus();
									}
									if (e.shiftKey == true && e.getKey() == Ext.EventObject.TAB)
                                    {
   										e.stopEvent();
										Ext.getCmp('FindTorgField').focus();
									}
								},
								'keypress': function(inp, e) {
									if (e.getKey() == Ext.EventObject.ENTER)
                                    {
   										e.stopEvent();
										inp.ownerCt.items.item('FindAction').handler();
									}
								}
							}

						},
					    {
						xtype: 'label',
						text: lang['statya_rashoda'],
						style: 'margin-left: 5px; font-weight: bold',
						hidden: getGlobalOptions().region.nick != 'ufa'
				    },
    //				
				    {
					    xtype: 'swwhsdocumentcostitemtypecombo',
					    fieldLabel: lang['statya_rashoda'],
					    name: 'WhsDocumentCostItemType_id',
					    id: 'DOVW_WhsDocumentCostItemType',
					    style: 'margin-left: 5px',
					    hidden: getGlobalOptions().region.nick != 'ufa',
					    width: 200
				    },	
						{
	                    	xtype: 'button',
							style: 'margin-left: 5px',
							//text: BTN_FRMFILTER,
							text: (getGlobalOptions().region.nick != 'ufa') ? BTN_FRMFILTER: 'Найти',
							iconCls: 'search16',
							id: 'FindAction',
							handler: function() {
                           		var grid = this.ownerCt.ownerCt;
                                var mnn_filter = this.ownerCt.items.item('FindField');
                                var Drug_id = this.ownerCt.items.item('Drug_id');
                                var torg_filter = this.ownerCt.items.item('FindTorgField');
                                var org_farm_filter = this.ownerCt.items.item('OrgFarmFilter');
								var WhsDocumentCostItemType_filter = this.ownerCt.items.item('DOVW_WhsDocumentCostItemType'); 
								// очищаем поле "Остатки на аптечном складе"
								Ext.getCmp('DOVW_SkladOstatLabel').setText(lang['ostatki_na_aptechnom_sklade_fed_reg_7_noz_medikament_ne_vyibran']);
								// чистим таблицу остатков
								var detail_grid = Ext.getCmp('DrugOstatGridDetail');
								detail_grid.getStore().removeAll();
								detail_grid.getView().refresh();
								grid.getStore().removeAll();
							    grid.getStore().baseParams = {Drug_id: Drug_id.getValue(), mnn: mnn_filter.getValue(), torg: torg_filter.getValue(), org_farm_filter: org_farm_filter.getValue(),  WhsDocumentCostItemType_id: WhsDocumentCostItemType_filter.getValue()};
								grid.getStore().load({params: {Drug_id: Drug_id.getValue(), mnn: mnn_filter.getValue(), torg: torg_filter.getValue(), org_farm_filter: org_farm_filter.getValue(),  WhsDocumentCostItemType_id: WhsDocumentCostItemType_filter.getValue(), start: 0, limit: 100}, callback: function() {grid.getSelectionModel().selectFirstRow();}});
							},
							onTabElement: 'ClearAction',
							onShiftTabElement: 'OrgFarmFilter'
						}, {
	                    	xtype: 'button',
							style: 'margin-left: 5px',
							text: BTN_FRMRESET,
							iconCls: 'resetsearch16',
							id: 'ClearAction',
							handler: function() {
                           		var grid = this.ownerCt.ownerCt;
                                var mnn_filter = this.ownerCt.items.item('FindField');
                                var torg_filter = this.ownerCt.items.item('FindTorgField');
                                var org_farm_filter = this.ownerCt.items.item('OrgFarmFilter');
								var WhsDocumentCostItemType_filter = this.ownerCt.items.item('DOVW_WhsDocumentCostItemType'); 
                                var Drug_id = this.ownerCt.items.item('Drug_id');
                                var ReceptFinance_id = this.ownerCt.items.item('ReceptFinance_id');
                                var OrgFarmacy_id = this.ownerCt.items.item('OrgFarmacy_id');
                                var OrgFarmacy_oid = this.ownerCt.items.item('OrgFarmacy_oid');
								mnn_filter.setValue('');
								torg_filter.setValue('');
								org_farm_filter.setValue('');
								WhsDocumentCostItemType_filter.setValue('')
								Drug_id.setValue('');
								ReceptFinance_id.setValue('');
								OrgFarmacy_id.setValue('');
								OrgFarmacy_oid.setValue('');
								// чистим таблицу остатков
								var detail_grid = Ext.getCmp('DrugOstatGridDetail');
								detail_grid.getStore().removeAll();
								detail_grid.getView().refresh();
								grid.getStore().removeAll();
							    grid.getStore().baseParams = {mnn: mnn_filter.getValue(), torg: torg_filter.getValue(), org_farm_filter: org_farm_filter.getValue(), WhsDocumentCostItemType_id: WhsDocumentCostItemType_filter.getValue()};
								grid.getStore().load({params: {mnn: mnn_filter.getValue(), torg: torg_filter.getValue(), org_farm_filter: org_farm_filter.getValue(), WhsDocumentCostItemType_id: WhsDocumentCostItemType_filter.getValue(), start: 0, limit: 100}, callback: function() {grid.getSelectionModel().selectFirstRow()}});
							},
							onTabAction: function(field) {
	                            var grid = Ext.getCmp('DrugGrid');
								if ( grid.getStore().getCount() > 0 )
								{
									grid.getSelectionModel().selectFirstRow();
									grid.getView().focusRow(0);
								}
								else
								{
		                            var grid = Ext.getCmp('DrugOstatGridDetail');
									if ( grid.getStore().getCount() > 0 )
									{
										grid.getSelectionModel().selectFirstRow();
										grid.getView().focusRow(0);
									}
									else
									{
	                                	Ext.getCmp('DOVW_CloseButton').focus();
									}
								}
							},
							onShiftTabElement: 'FindAction'
						}, {
   	                    	xtype: 'tbfill'
       	                }, {
           	               	text: '0 / 0',
               	           	xtype: 'tbtext'
                   	    },{
	                    	xtype: 'hidden',
							id: 'OrgFarmacy_id'
						},{
	                    	xtype: 'hidden',
							id: 'OrgFarmacy_oid'
						}]
					}),
					bbar: new Ext.PagingToolbar ({
						store: gridStore,
						pageSize: 100,
						displayInfo: true,
        				displayMsg: lang['otobrajaemyie_stroki_{0}_-_{1}_iz_{2}'],
				        emptyMsg: "Нет записей для отображения"
					}),
					columns: [
						{dataIndex: 'Drug_id', hidden: true, hideable: false},
						{header: lang['mnn'], dataIndex: 'DrugMnn_Name', sortable: true, width: 450},
						{header: lang['kod_ges'], dataIndex: 'Drug_CodeG', sortable: true, width: 120, hidden: getRegionNick() == 'ufa'},
						{id: 'autoexpand', header: lang['torgovoe_naimenovanie'], dataIndex: 'Drug_Name', sortable: true}
					],
					sm: new Ext.grid.RowSelectionModel({
						singleSelect: true,
						listeners: {
							'rowselect': function(sm, rowIdx, r) {
								var grid = this.grid,
									org_farm_filter = grid.getTopToolbar().items.item('OrgFarmFilter').getValue(),
									params = {};

								params.Drug_id = r.data.Drug_id;
								params.ReceptFinance_id = Ext.getCmp('ReceptFinance_id').getValue();
								params.OrgFarmacy_id = Ext.getCmp('OrgFarmacy_id').getValue();
								params.OrgFarmacy_oid = Ext.getCmp('OrgFarmacy_oid').getValue();
								params.org_farm_filter = org_farm_filter;
								params.WhsDocumentCostItemType_id  = Ext.getCmp('DOVW_WhsDocumentCostItemType').getValue();

								if (!Ext.isEmpty(_this.mode)) {
									params.mode = _this.mode;
								}

								Ext.getCmp('DOVW_SkladOstatLabel').setText(lang['ostatki_na_aptechnom_sklade_fed_reg_7_noz_net_net_net']);
								grid.ownerCt.findById('DrugOstatGridDetail').getStore().removeAll();
                            	grid.ownerCt.findById('DrugOstatGridDetail').getStore().load({
                            		params: params,
									callback: function() {
										var ostat_grid = Ext.getCmp('DrugOstatGridDetail');
										var store = ostat_grid.getStore();
										if ( store.getCount() > 0 && _this.mode != 'DrugOstatView')
										{
											var index = store.findBy(function(record, id) {
												if ( record.get('OrgFarmacy_id') == '1' )
													return true;
												else
													return false;
											});
											var sklad_record = store.getAt(index);
											
											if ( sklad_record )
											{
												var labelText = lang['ostatki_na_aptechnom_sklade_fed_reg_7_noz'];
												labelText += (sklad_record.get('DrugOstat_Fed') != "" && sklad_record.get('DrugOstat_Fed') != null)?'Да/':'Нет/';
												labelText += (sklad_record.get('DrugOstat_Reg') != "" && sklad_record.get('DrugOstat_Reg') != null)?'Да/':'Нет/';
												labelText += (sklad_record.get('DrugOstat_7Noz') != "" && sklad_record.get('DrugOstat_7Noz') != null)?lang['da']:lang['net'];
												store.remove(sklad_record);
												grid.getTopToolbar().items.items[11].el.innerHTML = (rowIdx + 1) + ' / ' + grid.getStore().getCount();
												Ext.getCmp('DOVW_SkladOstatLabel').setText(labelText);
											}
											else
											{
												Ext.getCmp('DOVW_SkladOstatLabel').setText(lang['ostatki_na_aptechnom_sklade_fed_reg_7_noz_net_net_net']);
											}
										}
									}
								});
								this.grid.getTopToolbar().items.items[11].el.innerHTML = (rowIdx + 1) + ' / ' + this.grid.getStore().getCount();
							}
						}
					}),
					enableKeyEvents: true,
					keys: [{
	                    key: [
	                        //Ext.EventObject.ENTER,
//	                        Ext.EventObject.DELETE,
	                        Ext.EventObject.F3,
	                        Ext.EventObject.F4,
	                        Ext.EventObject.F5,
	                        Ext.EventObject.INSERT,
	                        Ext.EventObject.TAB,
	    					Ext.EventObject.PAGE_UP,
	    					Ext.EventObject.PAGE_DOWN,
	    					Ext.EventObject.HOME,
	    					Ext.EventObject.END
	                    ],
	                    fn: function(inp, e) {
	                    	
	                    	// События для грида работают, только когда мы стоим на гриде
							// Странный код, означает что если мы стоим не на элементе TD или А,
							// что является признаком того, что мы стоим на гриде,
							// то переходим к стандартным обработчикам, 
	                    	if ( e.target.nodeName != 'TD' && e.target.nodeName != 'A' ) {
	                    		return true;
	                    	}
	                    	
	                    	e.stopEvent();

	                        if ( e.browserEvent.stopPropagation )
	                            e.browserEvent.stopPropagation();
	                        else
	                            e.browserEvent.cancelBubble = true;

	                        if ( e.browserEvent.preventDefault )
	                            e.browserEvent.preventDefault();
	                        else
	                            e.browserEvent.returnValue = false;

	                        e.browserEvent.returnValue = false;
	                        e.returnValue = false;

	                        if (Ext.isIE)
	                        {
	                            e.browserEvent.keyCode = 0;
	                            e.browserEvent.which = 0;
	                        }

	                    	var current_window = Ext.getCmp('DrugOstatViewWindow');
							var drug_grid = current_window.findById('DrugGrid');
							var drug_ostat_grid = current_window.findById('DrugOstatGridDetail');
	                        switch (e.getKey())
	                        {
	                            case Ext.EventObject.END:
	                            	GridEnd(drug_grid);
                                break;

	                            case Ext.EventObject.ENTER:
	                        	case Ext.EventObject.F4:
								break;
	                        	case Ext.EventObject.F3:
								break;
	                        	case Ext.EventObject.F5:

                        	    break;

	                            case Ext.EventObject.HOME:
	                            	GridHome(drug_grid);
                                break;

	                        	case Ext.EventObject.INSERT:
								break;

	                        	case Ext.EventObject.DELETE:
								break;

	                            case Ext.EventObject.PAGE_DOWN:
	                            	GridPageDown(drug_grid, 'Drug_id');
                                break;

	                            case Ext.EventObject.PAGE_UP:
	                            	GridPageUp(drug_grid, 'Drug_id');
                                break;

	                        	case Ext.EventObject.TAB:
									if (e.shiftKey == false)
									{
	                            	    if (drug_ostat_grid.getStore().getCount())
	                            	    {
											drug_ostat_grid.getSelectionModel().selectFirstRow();
											drug_ostat_grid.getView().focusRow(0);
										}
										else
										{
											Ext.getCmp('DOVW_CloseButton').focus();
										}
									}
									else
									{
										Ext.getCmp('ClearAction').focus();
									}
								break;
							}
						},
						stopEvent: false
					}]
				}),
				new Ext.grid.GridPanel({
					bbar: new sw.Promed.Toolbar({
						autoHeight: true,
						items: [{
							id: 'DOVW_SkladOstatLabel',
							style: 'font-weight: bold',
							text: lang['ostatki_na_aptechnom_sklade_medikament_ne_vyibran'],
							xtype: 'label'
						}]
					}),
					region: 'south',
					split: true,
					height: 200,
					id: 'DrugOstatGridDetail',
					autoExpandColumn: 'autoexpand',
					autoExpandMax: 2000,
					loadMask: true,
					title: lang['apteka'],
			       	stripeRows: true,
					tbar: new sw.Promed.Toolbar({
						autoHeight: true,
						items: [{
							xtype: 'tbfill'
						}, {
							text: '0 / 0',
							xtype: 'tbtext'
						}]
					}),
					store: new Ext.data.JsonStore({
						autoLoad: false,
						url: C_DRUG_OSTAT_LIST,
						fields: [
							'DrugOstat_id',
							'OrgFarmacy_id',
							'OrgFarmacy_IsVkl',
							'OrgFarmacy_Name',
							'OrgFarmacy_HowGo',
							'Lpu_Nick',
							'ReceptFinance_Name',
							'DrugOstat_Kolvo',
							'DrugOstat_setDT',
							'DrugOstat_updDT',
							'Spros',
							'freeOstat',
							'Drug_id',
							{name: 'setDate', type: 'date', dateFormat:'d.m.Y'},
							{name: 'godnDate', type: 'date', dateFormat:'d.m.Y'},
							'DrugOstat_all',
							'Reserve_Kolvo',
							'DrugOstat_Fed',
							'DrugOstat_Reg',
							'DrugOstat_7Noz',
							'DrugOstat_Dializ',
							'DrugOstat_Vich',
							'DrugOstat_Gepatit',
							'DrugOstat_BSK',
							//'DocumentUcStr_godnDate'
							{name: 'DocumentUcStr_godnDate', type: 'date', dateFormat:'d.m.Y'},
							,'GodnDate_Ctrl'
						],
						listeners: {
							'load': function(store) {
								var grid = Ext.getCmp('DrugOstatGridDetail');
								grid.getTopToolbar().items.items[1].el.innerHTML = '0 / ' + store.getCount();
							}
						}
					}),
					columns: [
						{dataIndex: 'DrugOstat_id', hidden: true, hideable: false},
						{dataIndex: 'OrgFarmacy_id', hidden: true, hideable: false},
						{dataIndex: 'Drug_id', hidden: true, hideable: false},
						{header: lang['apteka'], dataIndex: 'OrgFarmacy_Name', sortable: true, width: 300},
						{id: 'autoexpand', header: lang['adres'], dataIndex: 'OrgFarmacy_HowGo', sortable: true},
						{header: lang['mo'], dataIndex: 'Lpu_Nick', sortable: true, width: 170, hidden: getRegionNick() != 'ufa'},
						{header: 'Срок<br />годности до', dataIndex: 'DocumentUcStr_godnDate', type: 'date', sortable: true, width: 100, hidden: getRegionNick() != 'ufa',
							renderer: Ext.util.Format.dateRenderer('d.m.Y')},
						{header: lang['finansirovanie'], dataIndex: 'ReceptFinance_Name', hidden: false, sortable: true, width: 200},
						{header: lang['kol-vo'], dataIndex: 'DrugOstat_Kolvo', hidden: false, sortable: true, width: 50},
						{header: lang['spros'], dataIndex: 'Spros', hidden: false, sortable: true, width: 50},
						{header: lang['svobodnyiy_ostatok'], dataIndex: 'freeOstat', hidden: false, sortable: true, width: 50},
						{header: 'Остатки с <br />учетом <br /> рецептов', dataIndex: 'DrugOstat_all', renderer: sw.Promed.Format.checkColumnForRas, sortable: true, width: 100, hidden: getRegionNick() != 'ufa'},
						{header: 'Выписано<br />по рецептам', dataIndex: 'Reserve_Kolvo', renderer: sw.Promed.Format.checkColumnForRas, sortable: true, width: 80,  hidden: getRegionNick() != 'ufa'},
						{header: lang['ostatki_fed'], dataIndex: 'DrugOstat_Fed', renderer: sw.Promed.Format.checkColumnForRas, sortable: true, width: 100},
						{header: lang['ostatki_reg'], dataIndex: 'DrugOstat_Reg', renderer: sw.Promed.Format.checkColumnForRas, sortable: true, width: 100},
						{header: lang['ostatki_7_noz'], dataIndex: 'DrugOstat_7Noz', renderer: sw.Promed.Format.checkColumnForRas, sortable: true, width: 100},
						{header: 'Остатки <br />(диализ)', dataIndex: 'DrugOstat_Dializ', renderer: sw.Promed.Format.checkColumnForRas, sortable: true, width: 100, hidden: getRegionNick() != 'ufa'},	
						{header: 'Остатки <br />(ОНЛС ВИЧ)', dataIndex: 'DrugOstat_Vich', renderer: sw.Promed.Format.checkColumnForRas, sortable: true, width: 100, hidden: getRegionNick() != 'ufa'},
						{header: 'Остатки <br />(гепатит)', dataIndex: 'DrugOstat_Gepatit', renderer: sw.Promed.Format.checkColumnForRas, sortable: true, width: 100, hidden: getRegionNick() != 'ufa'},
						{header: 'Остатки <br />(БСК)', dataIndex: 'DrugOstat_BSK', renderer: sw.Promed.Format.checkColumnForRas, sortable: true, width: 100, hidden: getRegionNick() != 'ufa'},
						{
							header: "Включена",
							dataIndex: 'OrgFarmacy_IsVkl',
							width: 65,
							renderer: sw.Promed.Format.checkColumn,
							sortable: false
						},
						{header: lang['data_ostatka'], dataIndex: 'DrugOstat_setDT', sortable: true, width: 100, hidden: getRegionNick() == 'ufa'},
						{header: lang['data_polucheniya_dannyh'], dataIndex: 'DrugOstat_updDT', sortable: true, width: 150, hidden: getRegionNick() == 'ufa'}
					],
					sm: new Ext.grid.RowSelectionModel({
						singleSelect: true,
						listeners: {
							'rowselect': function(sm, rowIdx, r) {
								this.grid.getTopToolbar().items.items[1].el.innerHTML = (rowIdx + 1) + ' / ' + this.grid.getStore().getCount();
							}
						}
					}),
					enableKeyEvents: true,
					keys: [{
	                    key: [
	                        Ext.EventObject.ENTER,
	                        Ext.EventObject.DELETE,
	                        Ext.EventObject.F3,
	                        Ext.EventObject.F4,
	                        Ext.EventObject.F5,
	                        Ext.EventObject.INSERT,
	                        Ext.EventObject.TAB,
	    					Ext.EventObject.PAGE_UP,
	    					Ext.EventObject.PAGE_DOWN,
	    					Ext.EventObject.HOME,
	    					Ext.EventObject.END
	                    ],
	                    fn: function(inp, e) {
	                    	
	                    	// События для грида работают, только когда мы стоим на гриде
							// Странный код, означает что если мы стоим не на элементе TD или А,
							// что является признаком того, что мы стоим на гриде,
							// то переходим к стандартным обработчикам, 
	                    	if ( e.target.nodeName != 'TD' && e.target.nodeName != 'A' ) {
	                    		return true;
	                    	}
	                    	
	                    	e.stopEvent();

	                        if ( e.browserEvent.stopPropagation )
	                            e.browserEvent.stopPropagation();
	                        else
	                            e.browserEvent.cancelBubble = true;

	                        if ( e.browserEvent.preventDefault )
	                            e.browserEvent.preventDefault();
	                        else
	                            e.browserEvent.returnValue = false;

	                        e.browserEvent.returnValue = false;
	                        e.returnValue = false;

	                        if (Ext.isIE)
	                        {
	                            e.browserEvent.keyCode = 0;
	                            e.browserEvent.which = 0;
	                        }

	                    	var current_window = Ext.getCmp('DrugOstatViewWindow');
							var drug_grid = current_window.findById('DrugGrid');
							var drug_ostat_grid = current_window.findById('DrugOstatGridDetail');
	                        switch (e.getKey())
	                        {
	                            case Ext.EventObject.END:
	                            	GridEnd(drug_ostat_grid);
                                break;

	                            case Ext.EventObject.ENTER:
	                        	case Ext.EventObject.F4:
								break;
	                        	case Ext.EventObject.F3:
								break;
	                        	case Ext.EventObject.F5:

                        	    break;

	                            case Ext.EventObject.HOME:
	                            	GridHome(drug_ostat_grid);
                                break;

	                        	case Ext.EventObject.INSERT:
								break;

	                        	case Ext.EventObject.DELETE:
								break;

	                            case Ext.EventObject.PAGE_DOWN:
	                            	GridPageDown(drug_ostat_grid, 'OrgFarmacy_id');
                                break;

	                            case Ext.EventObject.PAGE_UP:
	                            	GridPageUp(drug_ostat_grid, 'OrgFarmacy_id');
                                break;

	                        	case Ext.EventObject.TAB:
									if (e.shiftKey == true)
									{
	                            	    if (drug_grid.getStore().getCount() > 0)
	                            	    {
											drug_grid.getSelectionModel().selectFirstRow();
											drug_grid.getView().focusRow(0);
										}
										else
										{
											Ext.getCmp('ClearAction').focus();
										}
									}
									else
									{
                                    	Ext.getCmp('DOVW_PrintButton').focus();
									}
	                       	    break;
	                        }
	                    },
	                    stopEvent: false
	                }]
				})
			],
			buttonAlign: 'left',
			buttons: [
				{
					iconCls: 'print16',
					id: 'DOVW_PrintButton',
				    text: BTN_FRMPRINTALL,
					handler: function() {
						var drug_grid = Ext.getCmp('DrugOstatGridDetail');
						var grid = Ext.getCmp('DrugGrid');
						var row = grid.getSelectionModel().getSelected();
						if ( !row )
							return true;
						var mnn_name = row.data.DrugMnn_Name;
						var drug_name = row.data.Drug_Name;
						var date = Ext.util.Format.date(new Date(), 'd.m.Y');
						Ext.ux.GridPrinter.print(drug_grid, {tableHeaderText: 'Остатки в аптеках медикамента: "' + mnn_name + '", "' + drug_name + '" на ' + date});
					},
					onShiftTabAction: function(field) {
						var grid = Ext.getCmp('DrugOstatGridDetail');
						if ( grid.getStore().getCount() > 0 )
						{
							grid.getSelectionModel().selectFirstRow();
							grid.getView().focusRow(0);
						}
						else
						{
							Ext.getCmp('DOVW_CloseButton').focus();
						}
					},
					onTabElement: 'DOVW_CloseButton'
				},{
					iconCls: 'print16',
					id: 'DOVW_PrintCurButton',
				    text: BTN_FRMPRINTCUR,
					handler: function() {
						var drug_grid = Ext.getCmp('DrugOstatGridDetail'),
						    grid = Ext.getCmp('DrugGrid'),
						    row = grid.getSelectionModel().getSelected(),
						    rec = drug_grid.getSelectionModel().getSelected();
						if ( !row )
							return true;
						if ( !rec )
							return true;
						var mnn_name = row.data.DrugMnn_Name;
						var drug_name = row.data.Drug_Name;
						var date = Ext.util.Format.date(new Date(), 'd.m.Y');
						Ext.ux.GridPrinter.print(drug_grid, {tableHeaderText: 'Остатки в аптеках медикамента: "' + mnn_name + '", "' + drug_name + '" на ' + date,rowId: rec.id});
					},
					onShiftTabAction: function(field) {
						var grid = Ext.getCmp('DrugOstatGridDetail');
						if ( grid.getStore().getCount() > 0 )
						{
							grid.getSelectionModel().selectFirstRow();
							grid.getView().focusRow(0);
						}
						else
						{
							Ext.getCmp('DOVW_CloseButton').focus();
						}
					},
					onTabElement: 'DOVW_CloseButton'
				},
				'-',
				{
					text: BTN_FRMHELP,
					iconCls: 'help16',
					handler: function(button, event) {
						ShowHelp(WND_DLO_MEDNAME);
					}.createDelegate(self)
				},
				{
			        iconCls: 'close16',
					id: 'DOVW_CloseButton',
					text: BTN_FRMCLOSE,
	                handler: function() { this.ownerCt.hide() },
					onShiftTabAction: function(field) {
	                    var grid = Ext.getCmp('DrugOstatGridDetail');
						if ( grid.getStore().getCount() > 0 )
						{
							grid.getSelectionModel().selectFirstRow();
							grid.getView().focusRow(0);
						}
						else
						{
		                    var grid = Ext.getCmp('DrugGrid');
							if ( grid.getStore().getCount() > 0 )
							{
								grid.getSelectionModel().selectFirstRow();
								grid.getView().focusRow(0);
							}
							else
							{
		                       	Ext.getCmp('ClearAction').focus();
							}
						}
					},
					onTabElement: 'FindField'
				}
			],
			enableKeyEvents: true,
            keys: [{
		    	alt: true,
		        fn: function(inp, e) {
					switch ( e.getKey() )
					{
						case Ext.EventObject.P:
							Ext.getCmp('DrugOstatViewWindow').hide();
						break;
						case Ext.EventObject.G:
							Ext.getCmp('DOVW_PrintButton').handler();
						break;
					}
		        },
		        key: [ Ext.EventObject.P, Ext.EventObject.G ],
		        stopEvent: true
		    }]
		});
		Ext.getCmp('DrugOstatGridDetail').view = new Ext.grid.GridView({
		    getRowClass : function (row, index) {
			var cls = '';
			if (getGlobalOptions().region.nick == 'ufa') {
			    var $dd = row.get('GodnDate_Ctrl');
			    if ($dd == 1) {
				cls = cls+'x-grid-rowred';
			    }
			}
			return cls;
		    }
		});
    	sw.Promed.swDrugOstatViewWindow.superclass.initComponent.apply(this, arguments);
    }
});