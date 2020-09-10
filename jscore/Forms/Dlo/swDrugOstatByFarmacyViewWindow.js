/**
* swDrugOstatByFarmacyViewWindow - окно просмотра и редактирования остатков по аптекам.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      DLO
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Pshenicyn Ivan aka IVP (ipshon@rambler.ru)
* @version      12.03.2009
*/

function FindOrgFarmacy() {
	var field = Ext.getCmp('DOBFVW_OrgFarmFilter');
//	var exp = String(field.getValue()).toLowerCase();
//	field.ownerCt.ownerCt.getStore().filter('OrgFarmacy_Name_HowGo', new RegExp(exp, "i"));
	field.focus();
}

var tm = null;

sw.Promed.swDrugOstatByFarmacyViewWindow = Ext.extend(sw.Promed.BaseForm, {
	layout      : 'border',
	maximized: true,
    modal: true,
	resizable: false,
	draggable: false,
    closeAction :'hide',
	id: 'DrugOstatByFarmacyViewWindow',
	disableButtons: function() {
		var msf_store = sw.Promed.MedStaffFactByUser.store;
		if (getRegionNick() == 'ufa' && msf_store.findBy(function(rec) { return rec.get('ARMType') == 'minzdravdlo'; }) < 0) {
			Ext.getCmp('DOBFVW_OrgFarmOnButton').disable();
			Ext.getCmp('DOBFVW_OrgFarmOffButton').disable();
			Ext.getCmp('DOBFVW_OrgFarmUpButton').disable();
			Ext.getCmp('DOBFVW_OrgFarmDownButton').disable();
		}
		if(getWnd('swWorkPlaceMZSpecWindow').isVisible())
		{
			Ext.getCmp('DOBFVW_OrgFarmOnButton').disable();
			Ext.getCmp('DOBFVW_OrgFarmOffButton').disable();
			Ext.getCmp('DOBFVW_OrgFarmUpButton').disable();
			Ext.getCmp('DOBFVW_OrgFarmDownButton').disable();	
		}
	},
	OrgFarmacyReplace: function(direction) {
       	var grid = Ext.getCmp('DrugOstatByFarmacyViewWindow').findById('FarmacyGrid');
		var row = grid.getSelectionModel().getSelected();
		if ( row.data.OrgFarmacyIndex_id > 0 )
		{
			Ext.Ajax.request({
				url: C_ORGFARMACY_REPLACE,
				params: {OrgFarmacy_id: row.data.OrgFarmacy_id, direction: direction},
				success: function(data) {
					var drug_grid = Ext.getCmp('DrugOstatByFarmacyGridDetail');
                    var mnn_filter = Ext.getCmp('DOBFVW_MnnFilter');
                    var torg_filter = Ext.getCmp('DOBFVW_TorgFilter');
                    var orgfarm_filter = Ext.getCmp('DOBFVW_OrgFarmFilter');
		    		var WhsDocumentCostItemType_filter = this.ownerCt.items.item('DOBFVW_WhsDocumentCostItemType');
					drug_grid.getStore().removeAll();
					grid.getStore().load({
						params: {
							mnn: mnn_filter.getValue(),
							torg: torg_filter.getValue(),
							orgfarm: orgfarm_filter.getValue(),
							WhsDocumentCostItemType_id: WhsDocumentCostItemType_filter.getValue()
						},
						callback: function(store) {
							var i = 0;
							grid.getStore().each(function(record) {

								if ( record.data.OrgFarmacyIndex_Index >= 0 )
								{
									vkl = record.data.OrgFarmacyIndex_Index;
								}
								else
								{
									vkl = 'ZZZZZZ';
								}
								record.data.OrgFarmacy_Name_OrgFarmacy_IsVkl = String(vkl + '_' + record.data.OrgFarmacy_Name).toLowerCase();
								record.data.OrgFarmacy_Name_HowGo = String(record.data.OrgFarmacy_Name + ' ' + record.data.OrgFarmacy_HowGo).toLowerCase();
								record.commit();
								i++;
								if ( i == grid.getStore().getCount() )
								{
									grid.getStore().sort('OrgFarmacy_Name_OrgFarmacy_IsVkl', 'ASC');
								}
							});
							FindOrgFarmacy();
						}
					});
				},
				failure: function() {
					Ext.Msg.alert(lang['oshibka'], lang['ne_udalos_proizvesti_operatsiyu']);
				}
			});

		}
	},
    plain       : true,
    title: WND_DLO_MEDAPT,
	setOrgFarmacyOn: function() {
		var access = (isSuperAdmin() || isLpuAdmin());
		if (getRegionNick() == 'perm') {
			// Включение/выключение аптек могут выполнять только администраторы ЦОД и специалисты ЛЛО ОУЗ.
			var msf_store = sw.Promed.MedStaffFactByUser.store;
			access = (isSuperAdmin() || (msf_store && msf_store.findBy(function(rec) { return rec.get('ARMType') == 'minzdravdlo'; }) >= 0));
		}
		if (!access || this.disableEditFarmacy) {
			return false;
		}
		else if ( Ext.getCmp('DOBFVW_OrgFarmOnButton').disabled ) {
			return false;
		}

		var grid = Ext.getCmp('DrugOstatByFarmacyViewWindow').findById('FarmacyGrid');
		var row = grid.getSelectionModel().getSelected();
		Ext.Ajax.request({
			url: C_ORGFARMACY_VKL,
			params: {OrgFarmacy_id: row.data.OrgFarmacy_id, vkl: 1},
			success: function(data) {
				var org_farmacy_index_id = 0;
				var org_farmacy_index_index = 0;
				if ( data.responseText )
				{
					var response_obj = Ext.util.JSON.decode(data.responseText);
					if (!response_obj.success && !response_obj.OrgFarmacyIndex_id) {
						return false;
					}						
					if ( response_obj.OrgFarmacyIndex_id )
						org_farmacy_index_id = response_obj.OrgFarmacyIndex_id;
					if ( response_obj.OrgFarmacyIndex_Index )
						org_farmacy_index_index = response_obj.OrgFarmacyIndex_Index;
				}
				var i = grid.getStore().findBy(function(rec) { return rec.get('OrgFarmacy_id') == row.data.OrgFarmacy_id; });
				row.data.OrgFarmacy_Vkl = 1;
				row.data.OrgFarmacyIndex_id = org_farmacy_index_id;
				row.data.OrgFarmacyIndex_Index = org_farmacy_index_index;
				row.data.OrgFarmacy_IsVkl = "true";
				// устанавливаем значение поля для сортировки
				row.data.OrgFarmacy_Name_OrgFarmacy_IsVkl = String(row.data.OrgFarmacyIndex_Index + '_' + row.data.OrgFarmacy_Name).toLowerCase();
				grid.getView().getRow(i).style.color = 'black';
				grid.getSelectionModel().suspendEvents();
				grid.getView().refresh();
			    grid.getSelectionModel().selectRow(i);
			    grid.getView().focusRow(i);
			    grid.getStore().sort('OrgFarmacy_Name_OrgFarmacy_IsVkl', 'ASC');
				grid.getSelectionModel().resumeEvents();
				Ext.getCmp('DOBFVW_OrgFarmOffButton').enable();
				Ext.getCmp('DOBFVW_OrgFarmOnButton').disable();
			},
			failure: function() {
				Ext.Msg.alert(lang['oshibka'], lang['ne_udalos_proizvesti_operatsiyu']);
			}
		});
	},
	setOrgFarmacyOff: function() {
		var access = (isSuperAdmin() || isLpuAdmin());
		if (getRegionNick() == 'perm') {
			// Включение/выключение аптек могут выполнять только администраторы ЦОД и специалисты ЛЛО ОУЗ.
			var msf_store = sw.Promed.MedStaffFactByUser.store;
			access = (isSuperAdmin() || (msf_store && msf_store.findBy(function(rec) { return rec.get('ARMType') == 'minzdravdlo'; }) >= 0));
		}
		if (!access || this.disableEditFarmacy) {
			return false;
		}
		else if ( Ext.getCmp('DOBFVW_OrgFarmOffButton').disabled ) {
			return false;
		}

		var wnd = this;
       	var grid = Ext.getCmp('DrugOstatByFarmacyViewWindow').findById('FarmacyGrid');
		var row = grid.getSelectionModel().getSelected();


		Ext.Ajax.request({
			url: C_ORGFARMACY_VKL,
			params: {OrgFarmacy_id: row.data.OrgFarmacy_id, vkl: 0, OrgFarmacyIndex_id: row.data.OrgFarmacyIndex_id},
			success: function() {
				var i = grid.getStore().findBy(function(rec) { return rec.get('OrgFarmacy_id') == row.data.OrgFarmacy_id; });
				row.data.OrgFarmacy_Vkl = 0;
				row.data.OrgFarmacyIndex_Index = '';
				row.data.OrgFarmacy_IsVkl = "false";
				// устанавливаем значение поля для сортировки
				row.data.OrgFarmacy_Name_OrgFarmacy_IsVkl = String('ZZZZZZ_' + row.data.OrgFarmacy_Name).toLowerCase();
				grid.getView().getRow(i).style.color = 'gray';
				grid.getSelectionModel().suspendEvents();
				grid.getView().refresh();
			    grid.getSelectionModel().selectRow(i);
			    grid.getView().focusRow(i);
				grid.getStore().sort('OrgFarmacy_Name_OrgFarmacy_IsVkl', 'ASC');
				grid.getSelectionModel().resumeEvents();
				Ext.getCmp('DOBFVW_OrgFarmOnButton').enable();
				Ext.getCmp('DOBFVW_OrgFarmOffButton').disable();
				wnd.disableButtons();
			},
			failure: function() {
				Ext.Msg.alert(lang['oshibka'], lang['ne_udalos_proizvesti_operatsiyu']);
			}
		});
	},
	refreshGridRowsColors: function()
	{
		var grid = this.findById('FarmacyGrid');
		if ( grid.getStore().getCount() > 0 )
		{
			for (var i=0; i<=(grid.getStore().getCount()-1); i++)
			{
				var row = grid.getStore().getAt(i);
				if (row.data.OrgFarmacy_Vkl == 0)
					grid.getView().getRow(i).style.color = 'gray';
			}
	//		grid.getView().focusRow(0);
	//		grid.getSelectionModel().selectFirstRow();
		}
	},
	refreshDrugOstatUpdateTime: function() {
		/*var current_window = this;
		var loadMask = new Ext.LoadMask(Ext.get('DrugOstatByFarmacyViewWindow'), { msg: "Получение последней даты обновления остатков..." });
		loadMask.show();
		Ext.Ajax.request({
			url: C_DRUG_UPD_TIME,
			callback: function(opt, success, resp) {
				loadMask.hide();
				if (resp.responseText != '')
				{
					var response_data = Ext.util.JSON.decode(resp.responseText);
					if (response_data && response_data[0]['DrugOstatUpdateTime'])
						current_window.setTitle(WND_DLO_MEDAPT + ' (Обновлено: ' + response_data[0]['DrugOstatUpdateTime'] + ')');
				}
			}
		});*/
	},
	show: function() {
		sw.Promed.swDrugOstatByFarmacyViewWindow.superclass.show.apply(this, arguments);
		var grid = this.findById('FarmacyGrid');
		var wnd = this;
		wnd.disableEditFarmacy = getRegionNick() === 'ufa';
		// проверка даты остатков
		this.refreshDrugOstatUpdateTime();
		grid.getView().addListener('refresh', function() {
			wnd.refreshGridRowsColors();
		});
		Ext.getCmp('DOBFVW_ResetFilterButton').handler();
		Ext.getCmp('DOBFVW_WhsDocumentCostItemType').getStore().load();
		wnd.disableButtons();
	},
	initComponent: function() {
		var wnd = this;

		Ext.apply(this, {
			items: [
				new sw.Promed.Toolbar({
				region: 'north',
				autoHeight: true,
				items: [{
	                    xtype: 'tbbutton',
						handler: function() {alert(this.ownerCt.ownerCt.title)},
						text: BTN_GRIDADD
					}]
				}),
				new Ext.grid.EditorGridPanel({
					autoExpandColumn: 'autoexpand',
					enableKeyEvents: true,
					region: 'north',
					height: 200,
					split: true,
					title: lang['apteka'],
					id: 'FarmacyGrid',
					listeners: {
						'rowdblclick': function(grid, rowIndex) {
							var row = grid.getStore().getAt(rowIndex);
							if ( row.data.OrgFarmacy_IsVkl == 'true' )
								grid.ownerCt.setOrgFarmacyOff();
							else
								grid.ownerCt.setOrgFarmacyOn();
						}
					},
					loadMask: true,
					autoExpandMax: 2000,
			        stripeRows: true,
					tbar: new sw.Promed.Toolbar({
						autoHeight: true,
						items: [{
							xtype: 'label',
							text: lang['apteka'],
							style: 'margin-left: 5px; font-weight: bold'
						},{
	                    	xtype: 'textfield',
							id: 'DOBFVW_OrgFarmFilter',
							style: 'margin-left: 5px',
							width: 120,
							enableKeyEvents: true,
							listeners: {
//								'keyup': function(field) {
//									if (tm) clearTimeout(tm);
//									tm = setTimeout("FindOrgFarmacy()", 1000);
//								},
								'keydown': function (inp, e) {
                                    if (e.getKey() == Ext.EventObject.ENTER)
                                    {
   										e.stopEvent();
										inp.ownerCt.items.item('DOBFVW_FindAction').handler();
									}
                                    if (e.shiftKey == false && e.getKey() == Ext.EventObject.TAB)
                                    {
   										e.stopEvent();
										inp.ownerCt.items.item('DOBFVW_MnnFilter').focus();
									}
									if (e.shiftKey == true && e.getKey() == Ext.EventObject.TAB)
                                    {
   										e.stopEvent();
										Ext.getCmp('DOBFVW_CloseButton').focus();
									}
								}
							}
						},'-',{
							xtype: 'label',
							text: lang['mnn'],
							style: 'margin-left: 5px; font-weight: bold'
						},{
	                    	xtype: 'textfield',
							id: 'DOBFVW_MnnFilter',
							style: 'margin-left: 5px',
							width: 120,
							enableKeyEvents: true,
							listeners: {
								'keydown': function (inp, e) {
									if (e.getKey() == Ext.EventObject.ENTER)
                                    {
   										e.stopEvent();
										inp.ownerCt.items.item('DOBFVW_FindAction').handler();
									}
                                    if (e.shiftKey == false && e.getKey() == Ext.EventObject.TAB)
                                    {
   										e.stopEvent();
										inp.ownerCt.items.item('DOBFVW_TorgFilter').focus();
									}
									if (e.shiftKey == true && e.getKey() == Ext.EventObject.TAB)
                                    {
   										e.stopEvent();
										Ext.getCmp('DOBFVW_OrgFarmFilter').focus();
									}
								}
							}
						},{
							xtype: 'label',
							text: lang['torg_naim'],
							style: 'margin-left: 5px; font-weight: bold'
						},{
	                    	xtype: 'textfield',
							id: 'DOBFVW_TorgFilter',
							style: 'margin-left: 5px',
							width: 120,
							enableKeyEvents: true,
							listeners: {
								'keydown': function (inp, e) {
									if (e.getKey() == Ext.EventObject.ENTER)
                                    {
   										e.stopEvent();
										inp.ownerCt.items.item('DOBFVW_FindAction').handler();
									}
                                    if (e.shiftKey == false && e.getKey() == Ext.EventObject.TAB)
                                    {
   										e.stopEvent();
										inp.ownerCt.items.item('DOBFVW_FindAction').focus();
									}
									if (e.shiftKey == true && e.getKey() == Ext.EventObject.TAB)
                                    {
   										e.stopEvent();
										Ext.getCmp('DOBFVW_MnnFilter').focus();
									}
								}
							}
				},  
				{
					xtype: 'label',
					text: lang['statya_rashoda'],
					style: 'margin-left: 5px; font-weight: bold',
					 hidden: getGlobalOptions().region.nick != 'ufa',
				},
				{
					   xtype: 'swwhsdocumentcostitemtypecombo',
					   fieldLabel: lang['statya_rashoda'],
					   name: 'WhsDocumentCostItemType_id',
					   id: 'DOBFVW_WhsDocumentCostItemType',
					   style: 'margin-left: 5px',
					   hidden: getGlobalOptions().region.nick != 'ufa',
					   width: 200
				},				
				{
	                    	xtype: 'button',
							style: 'margin-left: 5px',
							text: (getGlobalOptions().region.nick != 'ufa') ? lang['filtr']: 'Найти',
							iconCls: 'search16',
							id: 'DOBFVW_FindAction',
							handler: function() {
                           		var grid = this.ownerCt.ownerCt;
                                var mnn_filter = this.ownerCt.items.item('DOBFVW_MnnFilter');
                                var torg_filter = this.ownerCt.items.item('DOBFVW_TorgFilter');
                                var orgfarm_filter = this.ownerCt.items.item('DOBFVW_OrgFarmFilter');
				    			var WhsDocumentCostItemType_filter = this.ownerCt.items.item('DOBFVW_WhsDocumentCostItemType'); 
								grid.getStore().removeAll();
								if (getRegionNick() != 'ufa') {
									grid.getStore().baseParams = {mnn: mnn_filter.getValue(), torg: torg_filter.getValue(), orgfarm: orgfarm_filter.getValue(),
										WhsDocumentCostItemType_id: WhsDocumentCostItemType_filter.getValue()};
								}
								else {
									grid.getStore().baseParams = {mnn: mnn_filter.getValue(), torg: torg_filter.getValue(), orgfarm: orgfarm_filter.getValue(),
										WhsDocumentCostItemType_id: WhsDocumentCostItemType_filter.getValue(),
										typeList: 'Остатки'};
								}
								var drug_grid = Ext.getCmp('DrugOstatByFarmacyGridDetail');
								drug_grid.getStore().removeAll();
								grid.getStore().load({params: {mnn: mnn_filter.getValue(), torg: torg_filter.getValue(), orgfarm: orgfarm_filter.getValue(),
									WhsDocumentCostItemType_id: WhsDocumentCostItemType_filter.getValue()}, callback: function() {
									var i = 0;
									grid.getStore().each(function(record) {
										if ( record.data.OrgFarmacyIndex_Index >= 0 )
										{
											vkl = record.data.OrgFarmacyIndex_Index;
										}
										else
										{
											vkl = 'ZZZZZZ';
										}
										record.data.OrgFarmacy_Name_OrgFarmacy_IsVkl = String(vkl + '_' + record.data.OrgFarmacy_Name).toLowerCase();
										record.data.OrgFarmacy_Name_HowGo = String(record.data.OrgFarmacy_Name + ' ' + record.data.OrgFarmacy_HowGo).toLowerCase();
										record.commit();
										i++;
										if ( i == grid.getStore().getCount() )
										{
											grid.getStore().sort('OrgFarmacy_Name_OrgFarmacy_IsVkl', 'ASC');
										}
									});
									FindOrgFarmacy()
								}});
							},
							onTabElement: 'DOBFVW_ResetFilterButton',
							onShiftTabElement: 'DOBFVW_TorgFilter'
						}, {
	                    	xtype: 'button',
							iconCls: 'resetsearch16',
							id: 'DOBFVW_ResetFilterButton',
							style: 'margin-left: 5px',
							text: lang['sbros'],
							handler: function() {
								this.ownerCt.items.item('DOBFVW_OrgFarmFilter').setValue(undefined);
								this.ownerCt.items.item('DOBFVW_MnnFilter').setValue(undefined);
								this.ownerCt.items.item('DOBFVW_TorgFilter').setValue(undefined);
								this.ownerCt.items.item('DOBFVW_FindAction').handler();
								this.ownerCt.items.item('DOBFVW_WhsDocumentCostItemType').setValue(undefined);
							},
							onTabElement: 'DOBFVW_OrgFarmOnButton',
							onShiftTabElement: 'DOBFVW_FindAction'
						}, {
							xtype: 'button',
							id: 'DOBFVW_OrgFarmOnButton',
							text: lang['vkl'],
							disabled: true,
							handler: function() {
                            	Ext.getCmp('DrugOstatByFarmacyViewWindow').setOrgFarmacyOn();
							},
							onTabElement: 'DOBFVW_OrgFarmOffButton',
							onShiftTabElement: 'DOBFVW_ResetFilterButton'
						}, {
							xtype: 'button',
							text: lang['iskl'],
							disabled: true,
							id: 'DOBFVW_OrgFarmOffButton',
							handler: function() {
                            	Ext.getCmp('DrugOstatByFarmacyViewWindow').setOrgFarmacyOff();
							},
							onTabElement: 'DOBFVW_OrgFarmUpButton',
							onShiftTabElement: 'DOBFVW_OrgFarmOnButton'
						}, {
							xtype: 'button',
							id: 'DOBFVW_OrgFarmUpButton',
							text: lang['vverh'],
							disabled: false,
							handler: function() {
                            	Ext.getCmp('DrugOstatByFarmacyViewWindow').OrgFarmacyReplace('up');
							},
							onTabElement: 'DOBFVW_OrgFarmDownButton',
							onShiftTabElement: 'DOBFVW_OrgFarmOffButton'
						}, {
							xtype: 'button',
							text: lang['vniz'],
							disabled: false,
							id: 'DOBFVW_OrgFarmDownButton',
							handler: function() {
                            	Ext.getCmp('DrugOstatByFarmacyViewWindow').OrgFarmacyReplace('down');
							},
							onTabElement: 'DOBFVW_OrgFarmFilter',
							onShiftTabElement: 'DOBFVW_OrgFarmUpButton'
						}, {
							iconCls: 'print16',
							disabled: false,
							hidden: true,
							id: 'DOBFVW_OstatPrintButton',
							handler: function() {
                            	var drug_grid = Ext.getCmp('DrugOstatByFarmacyGridDetail');
								var grid = Ext.getCmp('DrugOstatByFarmacyViewWindow').findById('FarmacyGrid');
								var row = grid.getSelectionModel().getSelected();
								if ( !row )
									return true;
								var org_farmacy_name = row.data.OrgFarmacy_Name;
								var date = Ext.util.Format.date(new Date(), 'd.m.Y');
								Ext.ux.GridPrinter.print(drug_grid, {tableHeaderText: 'Список остатков медикаментов в аптеке: "' + org_farmacy_name + '" на ' + date});
							},
							onTabAction: function(field) {
	                            var grid = Ext.getCmp('FarmacyGrid');
								if ( grid.getStore().getCount() > 0 )
								{
									grid.getSelectionModel().selectFirstRow();
									grid.getView().focusRow(0);
								}
								else
								{
                                	Ext.getCmp('DOBFVW_PrintButton').focus();
								}
							},
							onShiftTabElement: 'DOBFVW_OrgFarmDownButton',
							text: lang['pechat_vsego_spiska'],
							xtype: 'button'
						},{
							iconCls: 'print16',
							disabled: false,
							hidden: true,
							id: 'DOBFVW_OstatPrintAllButton',
							handler: function() {
                            	var drug_grid = Ext.getCmp('DrugOstatByFarmacyGridDetail');
								var grid = Ext.getCmp('DrugOstatByFarmacyViewWindow').findById('FarmacyGrid');
								var row = grid.getSelectionModel().getSelected();
								if ( !row )
									return true;
								/*var org_farmacy_name = row.data.OrgFarmacy_Name;
								var date = Ext.util.Format.date(new Date(), 'd.m.Y');*/
								Ext.ux.GridPrinter.print(row);
							},
							onTabAction: function(field) {
	                            var grid = Ext.getCmp('FarmacyGrid');
								if ( grid.getStore().getCount() > 0 )
								{
									grid.getSelectionModel().selectFirstRow();
									grid.getView().focusRow(0);
								}
								else
								{
                                	Ext.getCmp('DOBFVW_PrintButton').focus();
								}
							},
							onShiftTabElement: 'DOBFVW_OrgFarmDownButton',
							text: lang['pechat_tekuschey_zapisi'],
							xtype: 'button'
						}, {
   	                    	xtype: 'tbfill'
       	                }, {
							id: 'DOBFVW_FarmacyCounter',
           	               	text: '0 / 0',
               	           	xtype: 'label'
                   	    }]
					}),
					enableKeyEvents: true,
					keys: [{
	                    key: [
	                        Ext.EventObject.ENTER,
//	                        Ext.EventObject.DELETE,
	                        Ext.EventObject.F3,
	                        Ext.EventObject.F4,
	                        Ext.EventObject.F5,
							Ext.EventObject.F9,
							Ext.EventObject.F10,
							Ext.EventObject.INSERT,
	                        Ext.EventObject.SPACE,
	                        Ext.EventObject.TAB,
	    					Ext.EventObject.PAGE_UP,
	    					Ext.EventObject.PAGE_DOWN,
	    					Ext.EventObject.HOME,
	    					Ext.EventObject.END
	                    ],
	                    fn: function(inp, e) {

	                    	var current_window = Ext.getCmp('DrugOstatByFarmacyViewWindow');
							var farmacy_grid = current_window.findById('FarmacyGrid');
							var drug_grid = current_window.findById('DrugOstatByFarmacyGridDetail');
							
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

	                        switch (e.getKey())
	                        {
	                            case Ext.EventObject.END:
	                            	GridEnd(farmacy_grid);
                                break;

	                            case Ext.EventObject.ENTER:
	                        	case Ext.EventObject.F4:
								break;
	                        	case Ext.EventObject.F3:
								break;
	                        	case Ext.EventObject.F5:

                        	    break;
								
								case Ext.EventObject.F9:
										Ext.getCmp('DOBFVW_OstatPrintButton').handler();
                                break;
								
								case Ext.EventObject.F10:
									if ( e.ctrlKey )
									{
										var row = farmacy_grid.getSelectionModel().getSelected();
										if ( row )
										{
											getWnd('swOrgEditWindow').show({params: {Org_id: row.data.Org_id, action: 'edit', callback: function() {
												Ext.getCmp('DOBFVW_FindAction').handler();
											}} });
										}
											
									}
                        	    break;
								
								case Ext.EventObject.HOME:
	                            	GridHome(farmacy_grid);
                                break;

	                        	case Ext.EventObject.INSERT:
								break;

	                        	case Ext.EventObject.DELETE:
								break;

	                            case Ext.EventObject.PAGE_DOWN:
	                            	GridPageDown(farmacy_grid, 'OrgFarmacy_id');
                                break;

	                            case Ext.EventObject.PAGE_UP:
	                            	GridPageUp(farmacy_grid, 'OrgFarmacy_id');
                                break;

	                        	case Ext.EventObject.TAB:
									if ( e.shiftKey )
									{
										Ext.getCmp('DOBFVW_OrgFarmDownButton').focus();
									}
									else
									{
	                            	    if (drug_grid.getStore().getCount() > 0)
	                            	    {
	                       				    drug_grid.getView().focusRow(0);
	                       				    drug_grid.getSelectionModel().selectFirstRow();
										}
										else
										{
		                                	Ext.getCmp('DOBFVW_PrintButton').focus();
										}
									}
	                       	    break;
	                        	case Ext.EventObject.SPACE:
									if (e.ctrlKey == true)
									{
										var row = farmacy_grid.getSelectionModel().getSelected();
										if ( row )
										{
											if ( row.data.OrgFarmacy_IsVkl == 'true' )
												farmacy_grid.ownerCt.setOrgFarmacyOff();
											else
												farmacy_grid.ownerCt.setOrgFarmacyOn();
										}
									}
								break;
	                        }
	                    },
	                    stopEvent: false
	                }],
					store: new Ext.data.JsonStore({
						autoLoad: false,
						url: C_ORGFARMACY_LIST,
						fields: [
							'Org_id',
							'OrgFarmacy_id',
							'OrgFarmacy_Code',
							'OrgFarmacy_Name',
							'OrgFarmacy_HowGo',
							'OrgFarmacy_Name_HowGo',
							'OrgFarmacyIndex_id',
							'OrgFarmacyIndex_Name',
							'OrgFarmacyIndex_Index',
							'OrgFarmacy_Vkl',
							'OrgFarmacy_IsVkl',
							'OrgFarmacy_Name_OrgFarmacy_IsVkl'
						],
						listeners: {
							'load': function(store) {
								var grid = Ext.getCmp('FarmacyGrid');
								Ext.getCmp('DOBFVW_FarmacyCounter').setText('0 / ' + store.getCount());
							}
						}
					}),
					columns: [
						{dataIndex: 'OrgFarmacy_id', hidden: true, hideable: false},
						{dataIndex: 'OrgFarmacy_Code', hidden: true, hideable: false},
						{dataIndex: 'OrgFarmacy_Name_HowGo', hidden: true, hideable: false},
						{dataIndex: 'OrgFarmacyIndex_id', hidden: true, hideable: false},
						{dataIndex: 'OrgFarmacyIndex_Name', hidden: true, hideable: false},
						{dataIndex: 'OrgFarmacyIndex_Index', hidden: true , hideable: false},
						{header: lang['apteka'], dataIndex: 'OrgFarmacy_Name', sortable: false, width: 350},
						{id: 'autoexpand', header: lang['adres'], dataIndex: 'OrgFarmacy_HowGo', sortable: false},
						{dataIndex: 'OrgFarmacy_Vkl', hidden: true, hideable: false},
						{dataIndex: 'OrgFarmacy_Name_OrgFarmacy_Vkl', hidden: true, hideable: false},
						new Ext.grid.CheckColumn({
							header: "Включена",
							dataIndex: 'OrgFarmacy_IsVkl',
							width: 65,
							sortable: false
						})
					],
					sm: new Ext.grid.RowSelectionModel({
						singleSelect: true,
						listeners: {
							'rowselect': function(sm, rowIdx, r) { 
							    var detail_grid = this.grid.ownerCt.findById('DrugOstatByFarmacyGridDetail');
							    detail_grid.getStore().removeAll();
							    if (r.data.OrgFarmacy_Vkl == 1) {
									if ( r.data.OrgFarmacyIndex_Index != null && ( isSuperAdmin() || isLpuAdmin() ) )
									{
										Ext.getCmp('DOBFVW_OrgFarmUpButton').enable();
										Ext.getCmp('DOBFVW_OrgFarmDownButton').enable();
									}
									else
									{
										Ext.getCmp('DOBFVW_OrgFarmUpButton').disable();
										Ext.getCmp('DOBFVW_OrgFarmDownButton').disable();
									}
	                            	detail_grid.getStore().load({
	                            		params: {
	                            			OrgFarmacy_id: r.data.OrgFarmacy_id,
											mnn: Ext.getCmp('DOBFVW_MnnFilter').getValue(),
											torg: Ext.getCmp('DOBFVW_TorgFilter').getValue(),
											WhsDocumentCostItemType_id: Ext.getCmp('DOBFVW_WhsDocumentCostItemType').getValue() 
										},
										callback: function()
										{
		                                    detail_grid.getTopToolbar().items.items[1].el.innerHTML = '0 / ' + detail_grid.getStore().getCount();
										}
									});
									Ext.getCmp('DOBFVW_FarmacyCounter').setText((rowIdx + 1) + ' / ' + this.grid.getStore().getCount());
	
									var access = (isSuperAdmin() || isLpuAdmin());
									if (getRegionNick() == 'perm') {
										// Включение/выключение аптек могут выполнять только администраторы ЦОД и специалисты ЛЛО ОУЗ.
										var msf_store = sw.Promed.MedStaffFactByUser.store;
										access = (isSuperAdmin() || (msf_store && msf_store.findBy(function(rec) { return rec.get('ARMType') == 'minzdravdlo'; }) >= 0));
									}
									if (access && !wnd.disableEditFarmacy ) {
										if (r.data.OrgFarmacy_Vkl == 1) {
											Ext.getCmp('DOBFVW_OrgFarmOffButton').enable();
											Ext.getCmp('DOBFVW_OrgFarmOnButton').disable();
										} else {
											Ext.getCmp('DOBFVW_OrgFarmOnButton').enable();
											Ext.getCmp('DOBFVW_OrgFarmOffButton').disable();
										}
									}
									wnd.disableButtons();
								}
						    }
						}
					})
				}),
				new Ext.grid.GridPanel({
					region: 'center',
					id: 'DrugOstatByFarmacyGridDetail',
					autoExpandColumn: 'autoexpand',
					autoExpandMax: 2000,
					loadMask: true,
					title: lang['medikament'],
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
					enableKeyEvents: true,
					keys: [{
	                    key: [
	                        Ext.EventObject.ENTER,
//	                        Ext.EventObject.DELETE,
	                        Ext.EventObject.F3,
	                        Ext.EventObject.F4,
	                        Ext.EventObject.F5,
							Ext.EventObject.F9,
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

	                    	var current_window = Ext.getCmp('DrugOstatByFarmacyViewWindow');
							var farmacy_grid = current_window.findById('FarmacyGrid');
							var drug_grid = current_window.findById('DrugOstatByFarmacyGridDetail');
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
								
								case Ext.EventObject.F9:
									Ext.getCmp('DOBFVW_OstatPrintButton').handler();
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
									if ( e.shiftKey == false )
	                            	    Ext.getCmp('DOBFVW_PrintButton').focus();
									else
									{
										farmacy_grid.getView().focusRow(0);
										farmacy_grid.getSelectionModel().selectFirstRow();
									}
	                       	    break;
	                        }
	                    },
	                    stopEvent: false
	                }],
					store: new Ext.data.JsonStore({
						autoLoad: false,
						url: C_DRUG_OSTAT_FARM_LIST,
						fields: [
							'DrugOstat_id',
							'OrgFarmacy_id',
							'DrugMnn_Name',
							'Drug_id',
							'Drug_Name',
							'Drug_CodeG',
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
							{name: 'DrugOstat_setDT', type: 'string'},
							{name: 'DrugOstat_updDT', type: 'string'},
							//'DocumentUcStr_godnDate',
							{name: 'DocumentUcStr_godnDate', type: 'date', dateFormat:'d.m.Y'},
							'GodnDate_Ctrl'

						],
						listeners: {
							'load': function(store) {
								var grid = Ext.getCmp('DrugOstatByFarmacyGridDetail');
								grid.getTopToolbar().items.items[1].el.innerHTML = '0 / ' + store.getCount();
							}
						}
					}),
					columns: [
						{dataIndex: 'DrugOstat_id', hidden: true, hideable: false},
						{dataIndex: 'OrgFarmacy_id', hidden: true, hideable: false},
						{dataIndex: 'Drug_id', hidden: true, hideable: false},
						{header: lang['mnn'], dataIndex: 'DrugMnn_Name', sortable: true, width: 300},
						{header: lang['kod_ges'], dataIndex: 'Drug_CodeG', sortable: true, width: 120},
						{id: 'autoexpand', header: lang['torgovoe_naimenovanie'], dataIndex: 'Drug_Name', sortable: true},
						{header: 'Срок<br />годности до', dataIndex: 'DocumentUcStr_godnDate', type: 'date', dateFormat:'d.m.Y', sortable: true, width: 100, hidden:  getRegionNick() != 'ufa',
							renderer: Ext.util.Format.dateRenderer('d.m.Y')},
						{header: 'Остатки с <br />учетом <br /> рецептов', dataIndex: 'DrugOstat_all', renderer: sw.Promed.Format.checkColumnForRas, sortable: true, width: 100, hidden:  getRegionNick() != 'ufa'},
						{header: 'Выписано<br />по рецептам', dataIndex: 'Reserve_Kolvo', renderer: sw.Promed.Format.checkColumnForRas, sortable: true, width: 80,  hidden:  getRegionNick() != 'ufa'},
						{header: lang['ostatki_fed'], dataIndex: 'DrugOstat_Fed', renderer: sw.Promed.Format.checkColumnForRas, sortable: true, width: 100},
						{header: lang['ostatki_reg'], dataIndex: 'DrugOstat_Reg', renderer: sw.Promed.Format.checkColumnForRas, sortable: true, width: 100},
						{header: lang['ostatki_7_noz'], dataIndex: 'DrugOstat_7Noz', renderer: sw.Promed.Format.checkColumnForRas, sortable: true, width: 100},
						{header: 'Остатки<br />(диализ)', dataIndex: 'DrugOstat_Dializ', renderer: sw.Promed.Format.checkColumnForRas, sortable: true, width: 100, hidden:  getRegionNick() != 'ufa'},
						{header: 'Остатки<br />(ОНЛС ВИЧ)', dataIndex: 'DrugOstat_Vich', renderer: sw.Promed.Format.checkColumnForRas, sortable: true, width: 100, hidden:  getRegionNick() != 'ufa'},
						{header: 'Остатки<br />(гепатит)', dataIndex: 'DrugOstat_Gepatit', renderer: sw.Promed.Format.checkColumnForRas, sortable: true, width: 100, hidden:  getRegionNick() != 'ufa'},
						{header: 'Остатки<br />(БСК)', dataIndex: 'DrugOstat_BSK', renderer: sw.Promed.Format.checkColumnForRas, sortable: true, width: 100, hidden:  getRegionNick() != 'ufa'},
						{header: lang['data_ostatka'], dataIndex: 'DrugOstat_setDT', sortable: true, hidden:(!(isSuperAdmin() || haveArmType('minzdravdlo'))), width: 100, hidden:  getRegionNick() == 'ufa'},
						{header: lang['data_polucheniya_dannyh'], dataIndex: 'DrugOstat_updDT', sortable: true, hidden:(!(isSuperAdmin() || haveArmType('minzdravdlo'))), width: 150, hidden:  getRegionNick() == 'ufa'}
					],
					sm: new Ext.grid.RowSelectionModel({
						singleSelect: true,
						listeners: {
							'rowselect': function(sm, rowIdx, r) {
								this.grid.getTopToolbar().items.items[1].el.innerHTML = (rowIdx + 1) + ' / ' + this.grid.getStore().getCount();
							}
						}
					})
				})
			],
			enableKeyEvents: true,
			buttonAlign: 'left',
			buttons: [
			{
		        iconCls: 'print16',
				id: 'DOBFVW_PrintButton',
				text: BTN_FRMPRINTALL,
                handler: function() {
					var drug_grid = Ext.getCmp('DrugOstatByFarmacyGridDetail');
					var grid = Ext.getCmp('DrugOstatByFarmacyViewWindow').findById('FarmacyGrid');
					var row = grid.getSelectionModel().getSelected();
					if ( !row )
						return true;
					var org_farmacy_name = row.data.OrgFarmacy_Name;
					var date = Ext.util.Format.date(new Date(), 'd.m.Y');
					Ext.ux.GridPrinter.print(drug_grid, {tableHeaderText: 'Остатки медикаментов в аптеке: "' + org_farmacy_name + '" на ' + date});
				},
				onShiftTabAction: function(field) {
                    var grid = Ext.getCmp('DrugOstatByFarmacyGridDetail');
					if ( grid.getStore().getCount() > 0 )
					{
						grid.getSelectionModel().selectFirstRow();
						grid.getView().focusRow(0);
					}
					else
					{
                       	Ext.getCmp('DOBFVW_ResetFilterButton').focus();
					}
				},
				onTabElement: 'DOBFVW_CloseButton'
			}, {
		        iconCls: 'print16',
				id: 'DOBFVW_PrintCurButton',
				text: BTN_FRMPRINTCUR,
                handler: function() {
					var drug_grid = Ext.getCmp('DrugOstatByFarmacyGridDetail'),
                        rec = drug_grid.getSelectionModel().getSelected(),
					    grid = Ext.getCmp('DrugOstatByFarmacyViewWindow').findById('FarmacyGrid'),
					    row = grid.getSelectionModel().getSelected();

					if ( !row ) return true;

					if ( !rec ) return true;

					var org_farmacy_name = row.data.OrgFarmacy_Name;
					var date = Ext.util.Format.date(new Date(), 'd.m.Y');
					Ext.ux.GridPrinter.print(drug_grid, {tableHeaderText: 'Остатки медикаментов в аптеке: "' + org_farmacy_name + '" на ' + date, rowId: rec.id});
				},
				onShiftTabAction: function(field) {
                    var grid = Ext.getCmp('DrugOstatByFarmacyGridDetail');
					if ( grid.getStore().getCount() > 0 )
					{
						grid.getSelectionModel().selectFirstRow();
						grid.getView().focusRow(0);
					}
					else
					{
                       	Ext.getCmp('DOBFVW_ResetFilterButton').focus();
					}
				},
				onTabElement: 'DOBFVW_CloseButton'
			}, '-',
			{
				text: BTN_FRMHELP,
				iconCls: 'help16',
				handler: function(button, event) {
					ShowHelp(WND_DLO_MEDAPT);
				}.createDelegate(self)
			},
			{
		        iconCls: 'close16',
				id: 'DOBFVW_CloseButton',
				text: BTN_FRMCLOSE,
                handler: function() { this.ownerCt.hide() },
				onShiftTabElement: 'DOBFVW_PrintButton',
				onTabElement: 'DOBFVW_OrgFarmFilter'
			}
			],
			keys: [{
		    	alt: true,
		        fn: function(inp, e) {
					switch ( e.getKey() )
					{
						case Ext.EventObject.P:
							Ext.getCmp('DrugOstatByFarmacyViewWindow').hide();
						break;
						case Ext.EventObject.G:
							Ext.getCmp('DOBFVW_PrintButton').handler();
						break;
					}
		        },
		        key: [ Ext.EventObject.P, Ext.EventObject.G ],
		        stopEvent: true
		    }]            
		});
		Ext.getCmp('DrugOstatByFarmacyGridDetail').view = new Ext.grid.GridView({
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

    	sw.Promed.swDrugOstatByFarmacyViewWindow.superclass.initComponent.apply(this, arguments);
    }
});