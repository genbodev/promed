/**
* swOrgFarmacyByLpuViewWindow - окно просмотра и редактирования списка аптек прикрепленных к ЛПУ (на основе формы "swDrugOstatByFarmacyViewWindow").
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      DLO
* @access       public
* @copyright    Copyright (c) 2013 Swan Ltd.
* @author       Salakhov R.
* @version      12.11.2013
*/

sw.Promed.swOrgFarmacyByLpuViewWindow = Ext.extend(sw.Promed.BaseForm, {
	layout : 'border',
	maximized: true,
    modal: true,
	resizable: false,
	draggable: false,
    closeAction :'hide',
    id: 'OrgFarmacyByLpuViewWindow',
    listeners: {
        'success': function(source, params) {
           
            if (params == 'refresh') {
                //Ext.getCmp('OFBLVW_FindAction').hendler();
                
                    Ext.getCmp('OFBLVW_FindAction').handler();
                //Ext.getCmp(OFBLVW_FindAction).fireEvent('hendler');
        }
            }
                
    },
	
	OrgFarmacyReplace: function(direction) {
       	var grid = Ext.getCmp('OrgFarmacyByLpuViewWindow').findById('OFBLVW_FarmacyGrid');
		var row = grid.getSelectionModel().getSelected();
		var wnd = this;
		if ( row && row.data.OrgFarmacyIndex_id > 0 ) {
			var orgfarmacy_id = row.get('OrgFarmacy_id');
			Ext.Ajax.request({
				url: C_ORGFARMACY_REPLACE,
				params: {
					OrgFarmacy_id: row.data.OrgFarmacy_id,
					direction: direction,
					Lpu_id: wnd.currentLpu
				},
				success: function(data) {
                    var orgfarm_filter = Ext.getCmp('OFBLVW_OrgFarmFilter');
					grid.getStore().load({
						params: {
							orgfarm: orgfarm_filter.getValue(),
							Lpu_id: wnd.currentLpu
						},
						callback: function(store) {
							var i = 0;
							grid.getStore().each(function(record) {
								if ( record.data.OrgFarmacyIndex_Index >= 0 ) {
									vkl = record.data.OrgFarmacyIndex_Index;
								} else {
									vkl = 'ZZZZZZ';
								}
								record.data.OrgFarmacy_Name_OrgFarmacy_IsVkl = String(vkl + '_' + record.data.OrgFarmacy_Name).toLowerCase();
								record.data.OrgFarmacy_Name_HowGo = String(record.data.OrgFarmacy_Name + ' ' + record.data.OrgFarmacy_HowGo).toLowerCase();
								record.commit();
								i++;
								if ( i == grid.getStore().getCount() ) {
									grid.getStore().sort('OrgFarmacy_Name_OrgFarmacy_IsVkl', 'ASC');
								}
							});
							var index = grid.getStore().findBy(function(rec) { return rec.get('OrgFarmacy_id') == orgfarmacy_id; });
							if (index > -1) {
								grid.focus();
								grid.getView().focusRow(index);
								grid.getSelectionModel().selectRow(index);
							}
						}
					});
				},
				failure: function() {
					Ext.Msg.alert('Ошибка', 'Не удалось произвести операцию');
				}
			});

		}
	},
    plain: true,
    title: 'Прикрепление аптек к МО',
        setOrgFarmacyEdit: function() {
			var grid = Ext.getCmp('OrgFarmacyByLpuViewWindow').findById('OFBLVW_FarmacyGrid');
            var row = grid.getSelectionModel().getSelected();
            //var lpu_filter = this.ownerCt.items.item('OFBLVW_LpuFilter');
            /*
            'Org_id',
							'OrgFarmacy_id',
							'OrgFarmacy_Code',
							'OrgFarmacy_Name',
                                                        'LpuBuilding_Name', 
							'OrgFarmacy_HowGo',
							'OrgFarmacy_Name_HowGo',
							'OrgFarmacyIndex_id',
							'OrgFarmacyIndex_Name',
							'OrgFarmacyIndex_Index',
							'OrgFarmacy_Vkl',
							'OrgFarmacy_IsVkl',
							'OrgFarmacy_Name_OrgFarmacy_IsVkl'
                                                */
            params = new Object();
            params.action = 'edit';
            params.OrgFarmacy_Name = row.data.OrgFarmacy_Name
            params.OrgFarmacy_id = row.data.OrgFarmacy_id
            params.Lpu_Name = Ext.getCmp('OFBLVW_LpuFilter').lastSelectionText
            params.Lpu_id = Ext.getCmp('OFBLVW_LpuFilter').value
            params.moAttach = row.data.moAttach
            params.OrgFarmacy_IsNarko = row.data.OrgFarmacy_IsNarko
            params.parent_id = 'OrgFarmacyByLpuViewWindow';
			params.WhsDocumentCostItemType_id = row.data.WhsDocumentCostItemType_id;
//					params.action = 'add';
//					params.VacPresence_Period = new Date();
					getWnd('swOrgFarmacyByLpuEditWindow').show(params);
        },
	setOrgFarmacyOn: function() {
		var wnd = this;
       	var grid = Ext.getCmp('OrgFarmacyByLpuViewWindow').findById('OFBLVW_FarmacyGrid');
		var row = grid.getSelectionModel().getSelected();
		if (row) {
			Ext.Ajax.request({
				url: C_ORGFARMACY_VKL,
				params: {
					OrgFarmacy_id: row.data.OrgFarmacy_id,
					vkl: 1,
					Lpu_id: wnd.currentLpu
				},
				success: function(data) {
					var org_farmacy_index_id = 0;
					var org_farmacy_index_index = 0;
					if ( data.responseText )
					{
						var response_obj = Ext.util.JSON.decode(data.responseText);
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
					Ext.getCmp('OFBLVW_OrgFarmOffButton').enable();
					Ext.getCmp('OFBLVW_OrgFarmOnButton').disable();
					Ext.getCmp('OFBLVW_OrgFarmUpButton').enable();
					Ext.getCmp('OFBLVW_OrgFarmDownButton').enable();
				},
				failure: function() {
					Ext.Msg.alert('Ошибка', 'Не удалось произвести операцию');
				}
			});
		}
	},
	setOrgFarmacyOff: function() {
		var wnd = this;
               
       	var grid = Ext.getCmp('OrgFarmacyByLpuViewWindow').findById('OFBLVW_FarmacyGrid');
		var row = grid.getSelectionModel().getSelected();
		if (row) {
			Ext.Ajax.request({
				url: C_ORGFARMACY_VKL,
				params: {
					OrgFarmacy_id: row.data.OrgFarmacy_id,
					vkl: 0,
					OrgFarmacyIndex_id: row.data.OrgFarmacyIndex_id,
					Lpu_id: wnd.currentLpu
				},
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
					Ext.getCmp('OFBLVW_OrgFarmOnButton').enable();
					Ext.getCmp('OFBLVW_OrgFarmOffButton').disable();
					Ext.getCmp('OFBLVW_OrgFarmUpButton').disable();
					Ext.getCmp('OFBLVW_OrgFarmDownButton').disable();
				},
				failure: function() {
					Ext.Msg.alert('Ошибка', 'Не удалось произвести операцию');
				}
			});
		}
	},
	refreshGridRowsColors: function() {
		var grid = this.findById('OFBLVW_FarmacyGrid');
		if ( grid.getStore().getCount() > 0 ) {
			for (var i=0; i<=(grid.getStore().getCount()-1); i++) {
				var row = grid.getStore().getAt(i);
				if (row.data.OrgFarmacy_Vkl == 0)
					grid.getView().getRow(i).style.color = 'gray';
			}
		}
	},
	show: function() {
		sw.Promed.swOrgFarmacyByLpuViewWindow.superclass.show.apply(this, arguments);
		var grid = this.findById('OFBLVW_FarmacyGrid');
		var wnd = this;
		wnd.currentLpu = null;
		grid.getView().addListener('refresh', function() {
			wnd.refreshGridRowsColors();
		});
		Ext.getCmp('OFBLVW_OrgFarmUpButton').disable();
		Ext.getCmp('OFBLVW_OrgFarmDownButton').disable()

		var lpu_combo = Ext.getCmp('OFBLVW_LpuFilter');
		lpu_combo.go_find = false;
		Ext.getCmp('OFBLVW_LpuFilter').getStore().load({
			callback: function(){
				/*var lpu_id = getGlobalOptions().lpu_id;
				if (lpu_id > 0 && lpu_combo.getStore().findBy(function(rec) { return rec.get('Lpu_id') == lpu_id; })) {
					lpu_combo.setValue(lpu_id);
					wnd.currentLpu = lpu_id;
					Ext.getCmp('OFBLVW_ResetFilterButton').handler();
				}*/
				Ext.getCmp('OFBLVW_ResetFilterButton').handler();
			}
		});
		if (getGlobalOptions().groups.indexOf('LpuUser') == -1) {
			lpu_combo.enable();
		} else {
			lpu_combo.disable();
		}
		
		whsCombo = Ext.getCmp('OFBLVW_WhsDocumentCostItemType');
		whsCombo.getStore().load({
			callback: function(){
				whsCombo.getStore().filterBy(function(record) {
					if (record.get('WhsDocumentCostItemType_Code').inlist('0')) {
						record.set('WhsDocumentCostItemType_Name', 'Не указана'); 
						record.commit();
						return true;
					} 
					else if (record.get('WhsDocumentCostItemType_Code').inlist(whsCombo.Whs_Type_Code)) {
						return true;
					} else {
						return false;
					}
				});
			}
		});
	},
	initComponent: function() {
		var wnd = this;

		Ext.apply(this, {
			items: [
				new Ext.grid.EditorGridPanel({
					autoExpandColumn: 'autoexpand',
					enableKeyEvents: true,
					region: 'center',
					height: 200,
					split: true,
					title: false, //'Список аптек',
					id: 'OFBLVW_FarmacyGrid',
					listeners: {
						'rowdblclick': function(grid, rowIndex) {
							var row = grid.getStore().getAt(rowIndex);
							if (isSuperAdmin())
								grid.ownerCt.setOrgFarmacyEdit();
                                                        /*
							if ( row.data.OrgFarmacy_IsVkl == 'true' ) {
								grid.ownerCt.setOrgFarmacyOff();
							} else {
								grid.ownerCt.setOrgFarmacyOn();
							}
                                                        */
						}
					},
					loadMask: true,
					autoExpandMax: 2000,
			        stripeRows: true,
					tbar: new sw.Promed.Toolbar({
						autoHeight: true,
						items: [{
							xtype: 'label',
							text: lang['mo']+':',
							style: 'margin-left: 5px; margin-right: 5px; font-weight: bold'
						},{
							xtype: 'swlpucombo',
							id: 'OFBLVW_LpuFilter',
							width: 280,
							enableKeyEvents: true,
							listeners: {
								'keydown': function (inp, e) {
                                    if (e.getKey() == Ext.EventObject.ENTER) {
   										e.stopEvent();
										inp.go_find = true;
                                                                                // Ext.getCmp('OFBLVW_FindAction').handler();
									}
                                    if (e.shiftKey == false && e.getKey() == Ext.EventObject.TAB) {
   										e.stopEvent();
										inp.ownerCt.items.item('OFBLVW_OrgFarmFilter').focus();
									}
									if (e.shiftKey == true && e.getKey() == Ext.EventObject.TAB) {
   										e.stopEvent();
										Ext.getCmp('OFBLVW_CloseButton').focus();
									}
								},
								'select': function() {
									if (this.go_find) {
										this.ownerCt.items.item('OFBLVW_FindAction').handler();
										this.go_find = false;
									}
								}
							}
						}, {
							xtype: 'label',
							text: 'Аптека:',
							style: 'margin-left: 10px; font-weight: bold'
						}, {
	                    	xtype: 'textfield',
							id: 'OFBLVW_OrgFarmFilter',
							style: 'margin-left: 5px',
							width: 120,
							enableKeyEvents: true,
							listeners: {
								'keydown': function (inp, e) {
                                    if (e.getKey() == Ext.EventObject.ENTER) {
   										e.stopEvent();
										inp.ownerCt.items.item('OFBLVW_FindAction').handler();
									}
                                    if (e.shiftKey == false && e.getKey() == Ext.EventObject.TAB) {
   										e.stopEvent();
										inp.ownerCt.items.item('OFBLVW_FindAction').focus();
									}
									if (e.shiftKey == true && e.getKey() == Ext.EventObject.TAB) {
   										e.stopEvent();
										Ext.getCmp('OFBLVW_LpuFilter').focus();
									}
								}
							}
						}, {
							xtype: 'label',
							text: 'Программа ЛЛО:',
							style: 'margin-left: 5px; margin-right: 5px; font-weight: bold'
						}, {
								xtype: 'swwhsdocumentcostitemtypecombo',
								name: 'WhsDocumentCostItemType_id',
								id: 'OFBLVW_WhsDocumentCostItemType',
								width: 200,
						},
						'-',
						{
	                    	xtype: 'button',
							style: 'margin-left: 5px',
							text: 'Фильтр',
							iconCls: 'search16',
							id: 'OFBLVW_FindAction',
                                                        handler: function() {
                           		var grid = this.ownerCt.ownerCt;
                                var orgfarm_filter = this.ownerCt.items.item('OFBLVW_OrgFarmFilter');
                                var lpu_filter = this.ownerCt.items.item('OFBLVW_LpuFilter');
								var llo_filter = this.ownerCt.items.item('OFBLVW_WhsDocumentCostItemType');

								if (lpu_filter.getValue() <= 0) {
									Ext.Msg.alert('Ошибка', 'Для просмотра списка аптек необходимо выбрать ЛПУ');
									lpu_filter.focus();
									return false;
								}

								Ext.getCmp('OFBLVW_OrgFarmOffButton').disable();
								Ext.getCmp('OFBLVW_OrgFarmOnButton').disable();
								Ext.getCmp('OFBLVW_OrgFarmUpButton').disable();
								Ext.getCmp('OFBLVW_OrgFarmDownButton').disable();

								grid.getStore().removeAll();
							    grid.getStore().baseParams = {
									orgfarm: orgfarm_filter.getValue(),
									Lpu_id: lpu_filter.getValue(),
									LLO_program: llo_filter.getValue() == '15' ? 0: llo_filter.getValue()
								};
								grid.getStore().load({
									params: {
										orgfarm: orgfarm_filter.getValue(),
										Lpu_id: lpu_filter.getValue(),
										LLO_program: llo_filter.getValue() == '15' ? 0: llo_filter.getValue()
									},
									callback: function() {
									var i = 0;
									grid.getStore().each(function(record) {
										if ( record.data.OrgFarmacyIndex_Index >= 0 ) {
											vkl = record.data.OrgFarmacyIndex_Index;
										} else {
											vkl = 'ZZZZZZ';
										}
										record.data.OrgFarmacy_Name_OrgFarmacy_IsVkl = String(vkl + '_' + record.data.OrgFarmacy_Name).toLowerCase();
										record.data.OrgFarmacy_Name_HowGo = String(record.data.OrgFarmacy_Name + ' ' + record.data.OrgFarmacy_HowGo).toLowerCase();
										record.commit();
										i++;
										if ( i == grid.getStore().getCount() ) {
											grid.getStore().sort('OrgFarmacy_Name_OrgFarmacy_IsVkl', 'ASC');
										}
									});
									wnd.currentLpu = lpu_filter.getValue();
									grid.getSelectionModel().selectFirstRow();
								}});
							},
							onTabElement: 'OFBLVW_ResetFilterButton',
							onShiftTabElement: 'OFBLVW_OrgFarmFilter'
						}, {
	                    	xtype: 'button',
							iconCls: 'resetsearch16',
							id: 'OFBLVW_ResetFilterButton',
							style: 'margin-left: 5px',
							text: 'Сброс',
							handler: function() {
								Ext.getCmp('OFBLVW_WhsDocumentCostItemType').setValue(null);
								var grid = this.ownerCt.ownerCt;
								var lpu_combo = this.ownerCt.items.item('OFBLVW_LpuFilter');
								var lpu_id = getGlobalOptions().lpu_id;
								if (lpu_id > 0 && lpu_combo.getStore().findBy(function(rec) { return rec.get('Lpu_id') == lpu_id; })) {
									lpu_combo.setValue(lpu_id);
									wnd.currentLpu = lpu_id;
								} else {
									lpu_combo.setValue(null);
									wnd.currentLpu = null;
								}
								this.ownerCt.items.item('OFBLVW_OrgFarmFilter').setValue(undefined);
								if (wnd.currentLpu > 0) {
									this.ownerCt.items.item('OFBLVW_FindAction').handler();
								} else {
									Ext.getCmp('OFBLVW_OrgFarmOffButton').disable();
									Ext.getCmp('OFBLVW_OrgFarmOnButton').disable();
									Ext.getCmp('OFBLVW_OrgFarmUpButton').disable();
									Ext.getCmp('OFBLVW_OrgFarmDownButton').disable();
									grid.getStore().removeAll();
								}
							},
							onTabElement: 'OFBLVW_OrgFarmOnButton',
							onShiftTabElement: 'OFBLVW_FindAction'
						}, {
							xtype: 'button',
							id: 'OFBLVW_OrgFarmOnButton',
							text: 'Вкл.',
							disabled: true,
                                                        hidden: true,
                                                        //hidden: getWnd('swWorkPlaceSpecMEKLLOWindow').isVisible(),
							handler: function() {
                                                            Ext.getCmp('OrgFarmacyByLpuViewWindow').setOrgFarmacyOn();
							},
							onTabElement: 'OFBLVW_OrgFarmOffButton',
							onShiftTabElement: 'OFBLVW_ResetFilterButton'
						}, {
							xtype: 'button',
							text: 'Искл.',
							disabled: true,
                                                        hidden: true,
                                                        //hidden: getWnd('swWorkPlaceSpecMEKLLOWindow').isVisible(),
							id: 'OFBLVW_OrgFarmOffButton',
							handler: function() {
                                                            Ext.getCmp('OrgFarmacyByLpuViewWindow').setOrgFarmacyOff();
							},
							onTabElement: 'OFBLVW_OrgFarmUpButton',
							onShiftTabElement: 'OFBLVW_OrgFarmOnButton'
						}, {
							xtype: 'button',
							id: 'OFBLVW_OrgFarmUpButton',
							text: 'Вверх',
							disabled: false,
                            hidden: getRegionNick() == 'ufa' ? true: getWnd('swWorkPlaceSpecMEKLLOWindow').isVisible(),
							handler: function() {
                            	Ext.getCmp('OrgFarmacyByLpuViewWindow').OrgFarmacyReplace('up');
							},
							onTabElement: 'OFBLVW_OrgFarmDownButton',
							onShiftTabElement: 'OFBLVW_OrgFarmOffButton'
						}, {
							xtype: 'button',
							text: 'Вниз',
							disabled: false,
                            hidden: getRegionNick() == 'ufa' ? true: getWnd('swWorkPlaceSpecMEKLLOWindow').isVisible(),
							id: 'OFBLVW_OrgFarmDownButton',
							handler: function() {
                            	Ext.getCmp('OrgFarmacyByLpuViewWindow').OrgFarmacyReplace('down');
							},
							onTabElement: 'OFBLVW_OrgFarmFilter',
							onShiftTabElement: 'OFBLVW_OrgFarmUpButton'
						}, {
   	                    	xtype: 'tbfill'
       	                }, {
							id: 'OFBLVW_FarmacyCounter',
           	               	text: '0 / 0',
               	           	xtype: 'label'
                   	    }]
					}),
					enableKeyEvents: true,
					keys: [{
	                    key: [
	                        Ext.EventObject.ENTER,
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

	                    	var current_window = Ext.getCmp('OrgFarmacyByLpuViewWindow');
							var farmacy_grid = current_window.findById('OFBLVW_FarmacyGrid');

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

	                        if (Ext.isIE) {
	                            e.browserEvent.keyCode = 0;
	                            e.browserEvent.which = 0;
	                        }

	                        switch (e.getKey()) {
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
								
								case Ext.EventObject.F10:
									if ( e.ctrlKey ) {
										var row = farmacy_grid.getSelectionModel().getSelected();
										if ( row ) {
											getWnd('swOrgEditWindow').show({params: {Org_id: row.data.Org_id, action: 'edit', callback: function() {
												Ext.getCmp('OFBLVW_FindAction').handler();
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
									if ( e.shiftKey ) {
										Ext.getCmp('OFBLVW_OrgFarmDownButton').focus();
									} else {
	                            	    if (drug_grid.getStore().getCount() > 0) {
	                       				    drug_grid.getView().focusRow(0);
	                       				    drug_grid.getSelectionModel().selectFirstRow();
										}
									}
	                       	    break;
	                        	case Ext.EventObject.SPACE:
									if (e.ctrlKey == true) {
										var row = farmacy_grid.getSelectionModel().getSelected();
										if ( row ) {
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
                                                        'LpuBuilding_Name', 
							'OrgFarmacy_HowGo',
							'OrgFarmacy_Name_HowGo',
							'OrgFarmacyIndex_id',
							'OrgFarmacyIndex_Name',
							'OrgFarmacyIndex_Index',
							'OrgFarmacy_Vkl',
							'OrgFarmacy_IsVkl',
							'OrgFarmacy_Name_OrgFarmacy_IsVkl',
							'OrgFarmacy_IsNarko',
							'WhsDocumentCostItemType_id',
							'WhsDocumentCostItemType_Name'
						],
						listeners: {
							'load': function(store) {
								var grid = Ext.getCmp('OFBLVW_FarmacyGrid');
								Ext.getCmp('OFBLVW_FarmacyCounter').setText('0 / ' + store.getCount());
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
						{header: 'Аптека', dataIndex: 'OrgFarmacy_Name', sortable: false, width: 350}, 
                                                //{dataIndex: 'OrgFarmacy_IsNarko', type: 'checkbox', header: 'Лицензия на НС и ПВ', width: 150},
                                                new Ext.grid.CheckColumn({
							header: "Лицензия на НС и ПВ",
							dataIndex: 'OrgFarmacy_IsNarko',
							width: 150,
							sortable: false
						}),
						{dataIndex: 'WhsDocumentCostItemType_id', hidden: true, hideable: false},
						{header: 'Программа ЛЛО', dataIndex: 'WhsDocumentCostItemType_Name', sortable: false, width: 250},
						{header: 'Подразделения МО', dataIndex: 'LpuBuilding_Name', sortable: false, width: 250},
						{id: 'autoexpand', header: 'Адрес', dataIndex: 'OrgFarmacy_HowGo', sortable: false},
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
								if ( r.data.OrgFarmacyIndex_Index != null ) {
									Ext.getCmp('OFBLVW_OrgFarmUpButton').enable();
									Ext.getCmp('OFBLVW_OrgFarmDownButton').enable();
								} else {
									Ext.getCmp('OFBLVW_OrgFarmUpButton').disable();
									Ext.getCmp('OFBLVW_OrgFarmDownButton').disable();
								}
								Ext.getCmp('OFBLVW_FarmacyCounter').setText((rowIdx + 1) + ' / ' + this.grid.getStore().getCount());
								if (r.data.OrgFarmacy_Vkl == 1) {
									Ext.getCmp('OFBLVW_OrgFarmOffButton').enable();
									Ext.getCmp('OFBLVW_OrgFarmOnButton').disable();
								} else {
									Ext.getCmp('OFBLVW_OrgFarmOnButton').enable();
									Ext.getCmp('OFBLVW_OrgFarmOffButton').disable();
								}
							}
						}
					})
				})
			],
			enableKeyEvents: true,
			buttonAlign: 'left',
			buttons: [
			'-',
			HelpButton(this),
			{
		        iconCls: 'close16',
				id: 'OFBLVW_CloseButton',
				text: BTN_FRMCLOSE,
                handler: function() { this.ownerCt.hide() },
				onTabElement: 'OFBLVW_OrgFarmFilter'
			}
			],
			keys: [{
		    	alt: true,
		        fn: function(inp, e) {
					switch ( e.getKey() ) {
						case Ext.EventObject.P:
							Ext.getCmp('OrgFarmacyByLpuViewWindow').hide();
						break;
					}
		        },
		        key: [Ext.EventObject.P, Ext.EventObject.G],
		        stopEvent: true
		    }]            
		});
    	sw.Promed.swOrgFarmacyByLpuViewWindow.superclass.initComponent.apply(this, arguments);
    }
});