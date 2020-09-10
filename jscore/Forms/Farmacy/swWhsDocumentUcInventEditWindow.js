/**
* swWhsDocumentUcInventEditWindow - окно редактирования инв. ведомости
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Farmacy
* @access       public
* @copyright    Copyright (c) 2014 Swan Ltd.
* @author       Salakhov R.
* @version      10.2014
* @comment      
*/
var Consolidated = 1;

sw.Promed.swWhsDocumentUcInventEditWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: lang['inventarizatsionnaya_vedomost_redaktirovanie'],
	layout: 'border',
	id: 'WhsDocumentUcInventEditWindow',
	modal: true,
	shim: false,
	width: 400,
	resizable: false,
	maximizable: true,
	maximized: true,
	listeners: {
		hide: function() {
			this.onHide();
		}
	},
	onHide: Ext.emptyFn,
	setDisabled: function(disable) {
		var wnd = this;

		if (disable) {
			wnd.buttons[0].disable();
			wnd.buttons[1].disable();
			wnd.buttons[2].disable();
		} else {
			if (wnd.WhsDocumentStatusType_Code == 1) { //1 - Новый;
				wnd.buttons[0].enable();
			} else {
				wnd.buttons[0].disable();
			}

			if (wnd.ARMType == 'merch' && wnd.WhsDocumentStatusType_Code == 2) { //2 - Действующий; 
				wnd.buttons[1].enable();
				wnd.buttons[2].enable();
			} else {
				wnd.buttons[1].disable();
				wnd.buttons[2].disable();
			}
		}

		wnd.DrugGrid.setReadOnly(wnd.WhsDocumentStatusType_Code != 1); //1 - Новый;
	},
	doPrint: function(){
		var doc_id = this.WhsDocumentUcInvent_id;
		if (!(getGlobalOptions().region.nick == 'ufa' && getGlobalOptions().orgtype == 'farm')) {
		    var print_report_data = [{
				report_label: langs('Инвентаризационная ведомость'),
				report_file: 'dlo_StockSheet.rptdesign',
				fun: function(rb,checked,panel){
					if(checked) panel.clearMarks(rb);
				}
			},{
				report_label: langs('Сличительная ведомость'),
				report_file: 'dlo_CollationStatementInventory.rptdesign',
				report_format: 'xls',
				fun: function(rb,checked,panel){
					if(checked) panel.clearMarks(rb);
				}
			},{
				report_label: langs('Инвентаризационная опись'),
				report_file: 'InventoryList.rptdesign',
				paramWhsDocumentUcInventDrugInventory: -1,
				paramIsKolvoUchet: 1,
				fun: function(rb,checked,panel){
					var el = rb.ownerCt.ownerCt;
					if(checked) panel.clearMarks(rb);
					if(el.number && panel.items){
						panel.items.items[el.number+1].setVisible(checked);
					}
				}
			},{
				report_label: langs('без учетного остатка '),
				elHidden: true,
				fun: function(rb,checked,panel){
					var el = rb.ownerCt.ownerCt;
					var setEl = panel.find('name', 'Field'+(el.number-1));
					if(checked) {
						setEl[0].paramIsKolvoUchet = 0;
					}else{
						setEl[0].paramIsKolvoUchet = 1;
					}
				}
			},{
				report_label: langs('Ведомость расхождений'),
				report_file: 'ListDiscrepancy.rptdesign',
				paramWhsDocumentUcInventDrugInventory: -1,
				paramIsKolvoUchet: 1,
				fun: function(rb,checked,panel){
					var el = rb.ownerCt.ownerCt;
					if(checked) panel.clearMarks(rb);
					if(el.number && panel.items){
						panel.items.items[el.number+1].setVisible(checked)
					}
				}
			},{
				report_label: langs('без учетного остатка '),
				elHidden: true,
				fun: function(rb,checked,panel){
					var el = rb.ownerCt.ownerCt;
					var setEl = panel.find('name', 'Field'+(el.number-1));
					if(checked) {
						setEl[0].paramIsKolvoUchet = 0;
					}else{
						setEl[0].paramIsKolvoUchet = 1;
					}
				}
			},];

		    if (doc_id > 0) {
			    //подготовка массива ссылок
			    for(var i = 0; i < print_report_data.length; i++) {
				    if (!print_report_data[i].report_format || print_report_data[i].report_format.length < 1) {
					    print_report_data[i].report_format = 'pdf';
				    }
				    print_report_data[i].report_params = '&paramWhsDocumentUcInvent='+doc_id
			    }
			    getWnd('swReportSelectWindow').show({
				    ReportData: print_report_data
			    });
		    }
		} else {
			//  Если это Уфа, аптеки ЛЛО
			//var Storage = 2; // С учетом складов
			//var DrugClass = 1; // 1 - Все припараты, 2 - ПКУ
			if (Consolidated == 1) {
			    var print_report_data = [{
				report_label: 'Инвентаризационная опись ИНВ-3', //lang['inventarizatsionnaya_opis'],
				report_file: 'dlo_StockSheet_Ufa.rptdesign',
				report_svod: '1', //  1 - по складам, 2 - свод
				report_drugClass: '1' // 1 - Все припараты, 2 - ПКУ
			    }, {
				report_label: 'Сводная инвентаризационная опись ИНВ-3', //lang['inventarizatsionnaya_opis'],
				report_file: 'dlo_StockSheet_Ufa.rptdesign',
				report_svod: '2', //  1 - по складам, 2 - свод
				report_drugClass: '1' // 1 - Все припараты, 2 - ПКУ
			    }, {
				report_label: 'Сводная инвентаризационная опись ИНВ-3 по ПКУ', //lang['inventarizatsionnaya_opis'],
				report_file: 'dlo_StockSheet_Ufa.rptdesign',
				report_svod: '2', //  1 - по складам, 2 - свод
				report_drugClass: '2' // 1 - Все припараты, 2 - ПКУ
			    }, {
				report_label: 'Инвентаризационная ведомость',
				report_file: 'dlo_DocumentUcStr_PhysicalInventory_Ufa.rptdesign'
			    }, 
			    {
				report_label: lang['slichitelnaya_vedomost'],
				report_file: 'dlo_CollationStatementInventory_Ufa.rptdesign'
				//,report_format: 'xls'
			    }];
			Storage = 1;
			Storage 
		    } else {
			var print_report_data = [{
				report_label: 'Инвентаризационная опись ИНВ-3', //lang['inventarizatsionnaya_opis'],
				report_file: 'dlo_StockSheet_Ufa.rptdesign',
				report_svod: '1', //  1 - по складам, 2 - свод
				report_drugClass: '1' // 1 - Все припараты, 2 - ПКУ
			    }, {
				report_label: 'Инвентаризационная ведомость',
				report_file: 'dlo_DocumentUcStr_PhysicalInventory_Ufa.rptdesign'
			    }, 
			    {
				report_label: lang['slichitelnaya_vedomost'],
				report_file: 'dlo_CollationStatementInventory_Ufa.rptdesign'
				//,report_format: 'xls'
			    }];
		    }

		    if (doc_id > 0) {
			    //подготовка массива ссылок
			    for(var i = 0; i < print_report_data.length; i++) {
				    if (!print_report_data[i].report_format || print_report_data[i].report_format.length < 1) {
					    print_report_data[i].report_format = 'pdf';
				    }
				    print_report_data[i].report_params = '&paramWhsDocumentUcInvent='+doc_id 
				    if (getGlobalOptions().region.nick == 'ufa' && getGlobalOptions().orgtype == 'farm') {
					print_report_data[i].report_params += '&paramConsolidated=' + Consolidated;
					if (print_report_data[i].report_file ==  'dlo_StockSheet_Ufa.rptdesign' ) {
					    print_report_data[i].report_params += '&paramSvod=' + print_report_data[i].report_svod;
					    print_report_data[i].report_params += '&paramDrugClass=' + print_report_data[i].report_drugClass;
					}
				    }
			    }
			    getWnd('swReportSelectWindow').show({
				    ReportData: print_report_data
			    });
		    }
		}

	},
	setApproved: function(approved) {
		var wnd = this;
		var id = this.WhsDocumentUcInvent_id;

		if (id > 0) {
			Ext.Ajax.request({
                params: {
                    WhsDocumentUcInvent_id: id,
                    sign: true
                },
                url: '/?c=WhsDocumentUcInvent&m=signWhsDocumentUcInvent',
                callback: function(options, success, response) {
                    if (response.responseText) {
                        var response_obj = Ext.util.JSON.decode(response.responseText);
                        if (response_obj && response_obj.success && response_obj.WhsDocumentStatusType_Code) {
                            wnd.WhsDocumentStatusType_Code =  response_obj.WhsDocumentStatusType_Code*1;
                            wnd.setDisabled(wnd.action == 'view');
                            if (wnd.WhsDocumentStatusType_Code == 2) {
                                Ext.Msg.alert(lang['soobschenie'], lang['vedomost_uspeshno_utverjdena']);
                                if (wnd.owner) {
                                    wnd.owner.refreshRecords(null, 0);
                                }
                            }
                        }
                    }
                }
            });
		}
	},
	createDocumentUc: function(type) {
		var wnd = this;
		var create_enable = false;

		//проверка возможности создания документа
		this.DrugGrid.getGrid().getStore().each(function(record) {
			if (record.get('WhsDocumentUcInventDrug_FactKolvo') > 0) {
				var r = (record.get('WhsDocumentUcInventDrug_FactKolvo')*1)-(record.get('WhsDocumentUcInventDrug_Kolvo'));
				if ((type == 'DokSpis' && r < 0) || (type == 'DocOprih' && r > 0)) {
					create_enable = true;
					return false;
				}
			}
		});

		var openDocEditWindow = function(windowName, documents) {
			if (documents.length == 0) return;
			var document = documents.shift();
			getWnd(windowName).show({
				action: 'edit',
				DocumentUc_id: document.DocumentUc_id,
				DrugDocumentType_Code: document.DrugDocumentType_Code,
				DrugDocumentStatus_Code: 1,
				callback: function() {
					getWnd('swWorkPlaceMerchandiserWindow').doSearch();
					getWnd(windowName).hide();
					openDocEditWindow(windowName, documents);
				}
			});
		};

		if (create_enable && wnd.WhsDocumentUcInvent_id) {
			Ext.Ajax.request({
				params: {
					WhsDocumentUcInvent_id: wnd.WhsDocumentUcInvent_id,
					DrugDocumentType_SysNick: type
				},
				url: '/?c=WhsDocumentUcInvent&m=createDocumentUc',
				callback: function(options, success, response) {
					if (response.responseText) {
						var response_obj = Ext.util.JSON.decode(response.responseText);
						var $winDoc = 'swNewDocumentUcEditWindow';
						if (response_obj && response_obj.success && Ext.isArray(response_obj.DocumentUcList)) {
							if (getGlobalOptions().region.nick == 'ufa' && getGlobalOptions().orgtype == 'farm') {
								$winDoc = 'swFarmDocumentUcEditWindow';
							}

							openDocEditWindow($winDoc, Ext.apply([], response_obj.DocumentUcList));
						}
					}
				}
			});
		} else {
			if (type == 'DocOprih') {
				sw.swMsg.alert(lang['oshibka'], lang['vyipolnenie_operatsii_ne_dostupno_t_k_v_rezultatah_inventarizatsii_izlishki_ne_zafiksirovanyi']);
			}
			if (type == 'DokSpis') {
				sw.swMsg.alert(lang['oshibka'], lang['vyipolnenie_operatsii_ne_dostupno_t_k_v_rezultatah_inventarizatsii_nedostacha_ne_zafiksirovana']);
			}
		}
	},

	resetDrugFilter: function() {
		var df_form = this.DrugFilterPanel.getForm();
		df_form.reset();
	},

	searchDrug: function(reset) {
		var df_form = this.DrugFilterPanel.getForm();

		if (reset) {
			this.resetDrugFilter();
		}

		var params = df_form.getValues();
		params.WhsDocumentUcInvent_id = this.WhsDocumentUcInvent_id;

		this.DrugGrid.setColumnHidden('MultiSelectValue', true);

		this.DrugGrid.loadData({
			globalFilters: params,
			callback: function(){
				var WhsDocumentStatusType_Code = this.form.findField().getValue(WhsDocumentStatusType_Code);
				this.DrugGrid.setColumnHidden('MultiSelectValue', WhsDocumentStatusType_Code != 1);
				this.onRowSelectChange();
			}.createDelegate(this)
		});
	},

	onRowSelectChange: function() {
		var selections = this.DrugGrid.getMultiSelections();
		var allCount = selections.length;
		var countFactKolvo = 0;		//Для подсчета выбранных строк с заполненным фактическим количеством
		var countStorageWork = 0;	//Для подсчета выбранных строк с нарядом на выполние работ
		var WhsDocumentStatusType_Code = this.form.findField().getValue(WhsDocumentStatusType_Code);
		var action = this.action;

		selections.forEach(function(record) {
			if (!Ext.isEmpty(record.get('WhsDocumentUcInventDrug_FactKolvo'))) {
				countFactKolvo++;
			}
			if (!Ext.isEmpty(record.get('StorageWork_id'))) {
				countStorageWork++;
			}
		});

		this.DrugGrid.getAction('action_clear_fact_kolvo').setDisabled(action == 'view' || countFactKolvo == 0);
		this.DrugGrid.additActions.addStorageWork.setDisabled(action == 'view' || allCount == 0);	//можно создавать наряд, если предыдущий наряд был выполнен
		this.DrugGrid.additActions.editStorageWork.setDisabled(action == 'view' || countStorageWork == 0);
		this.DrugGrid.additActions.deleteStorageWork.setDisabled(action == 'view' || countStorageWork == 0);
		this.DrugGrid.additActions.includeSelectedDrugsToInventory.setDisabled(action == 'view' || allCount == 0);
		this.DrugGrid.getAction('action_add').setDisabled(action == 'view' || WhsDocumentStatusType_Code == 2);
		this.DrugGrid.getAction('action_edit').setDisabled(action == 'view' || WhsDocumentStatusType_Code == 2 || allCount != 1 || selections[0].get('Server_id') == 0);
		this.DrugGrid.getAction('action_view').setDisabled(allCount != 1 || selections[0].get('Server_id') == 0);
		this.DrugGrid.getAction('action_delete').setDisabled(action == 'view' || allCount == 0 || WhsDocumentStatusType_Code == 2);
	},

	includeDrugsToWhsDocumentUcInvent: function() {
		var wnd = this;
		var selected_data = [];

		wnd.DrugGrid.getMultiSelections().forEach(function(rec) {
			selected_data.push(rec.get('WhsDocumentUcInventDrug_id'));
		});

		if (selected_data.length > 0) {
			getWnd('swWhsDocumentUcInventNumberAssignEditWindow').show({
				WhsDocumentUcInventDrug_List: selected_data,
				WhsDocumentUcInvent_id: wnd.WhsDocumentUcInvent_id,
				callback: function() {
					wnd.searchDrug();
				}
			});
		}
	},

	createDocumentUcStorageWork: function() {
		var wnd = this;
		var selected_data = [];

		wnd.DrugGrid.getMultiSelections().forEach(function(rec) {
			selected_data.push(rec.get('WhsDocumentUcInventDrug_id'));
		});

		if (selected_data.length > 0) {
			getWnd('swDocumentUcStorageWorkCreateWindow').show({
				WhsDocumentUcInventDrug_List: selected_data,
				callback: function() {
					wnd.searchDrug();
				}
			});
		}
	},

	editDocumentUcStorageWork: function() {
		var wnd = this;
		var DocumentUcStorageWork_List = [];
		var selected_data = [];

		wnd.DrugGrid.getMultiSelections().forEach(function(rec) {
			if (!Ext.isEmpty('StorageWork_id')) {
				selected_data.push(rec.get('StorageWork_id'));
			}
		});

		if (selected_data.length > 0) {
			getWnd('swDocumentUcStorageWorkEditWindow').show({
				DocumentUcStorageWork_List: selected_data,
				callback: function() {
					wnd.searchDrug();
				}
			});
		}
	},

	deleteDocumentUcStorageWork: function() {
		var wnd = this;
		var selected_data = new Array();

		wnd.DrugGrid.getMultiSelections().forEach(function(rec) {
			selected_data.push(rec.get('WhsDocumentUcInventDrug_id'));
		});

		if (selected_data.length > 0) {
			sw.swMsg.show({
				buttons:Ext.Msg.YESNO,
				fn:function (buttonId, text, obj) {
					if (buttonId == 'yes') {
						Ext.Ajax.request({
							params: {
								WhsDocumentUcInventDrug_List: selected_data.join(',')
							},
							url: '/?c=WhsDocumentUcInvent&m=deleteDocumentUcStorageWork',
							callback: function(options, success, response) {
								if (success) {
									wnd.searchDrug();
								}
							}
						});
					}
				}.createDelegate(this),
				icon:Ext.MessageBox.QUESTION,
				msg: 'Вы действительно хотите удалить наряд(-ы) на выполнение работ?',
				title:lang['podtverjdenie']
			});
		}
	},

	clearDrugFactKolvo: function() {
		var wnd = this;
		var selected_data = new Array();

		wnd.DrugGrid.getMultiSelections().forEach(function(rec) {
			selected_data.push(rec.get('WhsDocumentUcInventDrug_id'));
		});

		if (selected_data.length > 0) {
			Ext.Ajax.request({
				params: {
					WhsDocumentUcInventDrug_List: selected_data.join(',')
				},
				url: '/?c=WhsDocumentUcInvent&m=clearDrugFactKolvo',
				callback: function(options, success, response) {
					if (success) {
						wnd.searchDrug();
					}
				}
			});
		}
	},

	saveDrugFactKolvo: function(record) {

		if (!record) return;

		var params = {
			WhsDocumentUcInventDrug_id: record.get('WhsDocumentUcInventDrug_id'),
			WhsDocumentUcInventDrug_FactKolvo: record.get('WhsDocumentUcInventDrug_FactKolvo')
		};

		Ext.Ajax.request({
			url: '/?c=WhsDocumentUcInvent&m=saveDrugFactKolvo',
			params: params,
			success: function(response) {
				var result = Ext.util.JSON.decode(response.responseText);
				if (result.success) {
					record.set('StorageWork_endDate', result.DocumentUcStorageWork_endDate || null);
					record.set('WhsDocumentStatusType_Name', result.WhsDocumentStatusType_Name || 'Ошибочный');
					record.commit();
				} else {
					record.reject();
				}
			},
			failure: function() {
				record.reject();
			}
		});
	},

	saveStorageWorkComment: function(record) {
		if (!record) return;

		if (Ext.isEmpty(record.get('StorageWork_id'))) {
			record.reject();
			return;
		}

		var params = {
			DocumentUcStorageWork_id: record.get('StorageWork_id'),
			DocumentUcStorageWork_Comment: record.get('StorageWork_Comment')
		};


		Ext.Ajax.request({
			url: '/?c=WhsDocumentUcInvent&m=saveStorageWorkComment',
			params: params,
			success: function(response) {
				var result = Ext.util.JSON.decode(response.responseText);
				if (result.success) {
					record.commit();
				} else {
					record.reject();
				}
			},
			failure: function() {
				record.reject();
			}
		});
	},

	openWhsDocumentUcInventDrugInventoryEditWindow: function(action) {

		if (!action.inlist(['add','edit','view'])) return;

		var wnd = this,
			params = {};
		var grid = this.InventoryNumGrid.getGrid();
		var record = grid.getSelectionModel().getSelected();
		var WhsDocumentUcInventDrugInventory_id = null;
		if(action != 'add')
			WhsDocumentUcInventDrugInventory_id = record.get('WhsDocumentUcInventDrugInventory_id');
		params = {
			action: action,
			callback: function() {
				this.InventoryNumGrid.getAction('action_refresh').execute();
			}.createDelegate(this),
			WhsDocumentUcInvent_id: wnd.WhsDocumentUcInvent_id,
			WhsDocumentUcInventDrugInventory_id: (action == 'add')?null:WhsDocumentUcInventDrugInventory_id
		};

		getWnd('swWhsDocumentUcInventDrugInventoryEditWindow').show(params);
	},

	openWhsDocumentUcInventDrugEditWindow: function(action) {
		if (!action.inlist(['add','edit','view'])) return;

		var grid = this.DrugGrid.getGrid();

		var params = {
			action: action,
			DrugFinance_id: this.form.findField('DrugFinance_id').getValue(),
			WhsDocumentCostItemType_id: this.form.findField('WhsDocumentCostItemType_id').getValue(),
			StorageZone_id: this.form.findField('StorageZone_id').getValue()
		};

		params.callback = function() {
			this.DrugGrid.getAction('action_refresh').execute();
		}.createDelegate(this);

		if (action == 'add') {
			params.formParams = {
				WhsDocumentUcInvent_id: this.WhsDocumentUcInvent_id,
				Org_id: this.form.findField('Org_id').getValue(),
				Storage_id: this.form.findField('Storage_id').getValue(),
				StorageZone_id: this.form.findField('StorageZone_id').getValue()
			};
		} else {
			var record = grid.getSelectionModel().getSelected();
			if (!record || Ext.isEmpty(record.get('WhsDocumentUcInventDrug_id'))) {
				return;
			}

			params.formParams = {
				WhsDocumentUcInventDrug_id: record.get('WhsDocumentUcInventDrug_id')
			};
		}

		getWnd('swWhsDocumentUcInventDrugEditWindow').show(params);
	},

	deleteWhsDocumentUcInventDrug: function() {
		var wnd = this;
		var selected_data = [];
		var has_auto_record = false;

		wnd.DrugGrid.getMultiSelections().forEach(function(rec) {
			if (rec.get('Server_id') == 0) has_auto_record = true;
			selected_data.push(rec.get('WhsDocumentUcInventDrug_id'));
		});

		if (has_auto_record) {
			sw.swMsg.alert(lang['oshibka'], 'Удаление записи не доступно, т.к. она была сформирована автоматически');
			return false;
		}

		if (selected_data.length > 0) {
			sw.swMsg.show({
				buttons:Ext.Msg.YESNO,
				fn:function (buttonId, text, obj) {
					if (buttonId == 'yes') {
						Ext.Ajax.request({
							params: {
								WhsDocumentUcInventDrug_List: selected_data.join(',')
							},
							url: '/?c=WhsDocumentUcInventDrug&m=deleteList',
							callback: function(options, success, response) {
								if (success) {
									wnd.searchDrug();
								}
							}
						});
					}
				}.createDelegate(this),
				icon:Ext.MessageBox.QUESTION,
				msg: 'Вы действительно хотите удалить медикамент(-ы) из инветаризационной ведомости?',
				title:lang['podtverjdenie']
			});
		}
	},

	initDrugGridActions: function() {
		var wnd = this;
		var grid_panel = this.DrugGrid;

		if (!grid_panel.getAction('action_menu')) {
			var addit_actions = {
				addStorageWork: new Ext.Action({
					text: 'Добавить наряд на выполнение работ',
					iconCls: 'add16',
					handler: function(){wnd.createDocumentUcStorageWork()}
				}),
				editStorageWork: new Ext.Action({
					text: 'Редактировать наряд на выполнение работ',
					iconCls: 'edit16',
					handler: function(){wnd.editDocumentUcStorageWork()}
				}),
				deleteStorageWork: new Ext.Action({
					text: 'Удалить наряд на выполние работ',
					iconCls: 'delete16',
					handler: function(){wnd.deleteDocumentUcStorageWork()}
				}),
				includeSelectedDrugsToInventory: new Ext.Action({
					text: 'Включить выбранные медикаменты в опись',
					iconCls: 'add16',
					handler: function(){wnd.includeDrugsToWhsDocumentUcInvent()}
				})
			};

			grid_panel.additActions = addit_actions;
			grid_panel.addActions({
				name: 'action_menu',
				text: 'Действия',
				iconCls: 'actions16',
				menu: [
					addit_actions.addStorageWork,
					addit_actions.editStorageWork,
					addit_actions.deleteStorageWork,
					addit_actions.includeSelectedDrugsToInventory
				]
			});
		}
		if (!grid_panel.getAction('action_clear_fact_kolvo')) {
			grid_panel.addActions({
				name: 'action_clear_fact_kolvo',
				text: 'Удалить Факт. кол-во',
				iconCls: 'delete16',
				handler: function(){wnd.clearDrugFactKolvo()}
			});
		}

		if (getDrugControlOptions().doc_uc_operation_control) {
			grid_panel.additActions.addStorageWork.show();
			grid_panel.additActions.editStorageWork.show();
			grid_panel.additActions.deleteStorageWork.show();
		} else {
			grid_panel.additActions.addStorageWork.hide();
			grid_panel.additActions.editStorageWork.hide();
			grid_panel.additActions.deleteStorageWork.hide();
		}

		var visibleAdditActionCount = 0;
		for (actionName in grid_panel.additActions) {
			var action = grid_panel.additActions[actionName];
			if (!action.isHidden()) visibleAdditActionCount++;
		}

		grid_panel.getAction('action_menu').setDisabled(visibleAdditActionCount == 0);
	},

	initDocumentStatusTypeComboFilter: function(){

		var combo = this.DrugFilterPanel.findById('WhsDocumentStatusTypeCombo'),
		// разрешенные типы документов
			whsDocStatusTypeCode = [1, 10, 11];

		combo.getStore().clearFilter();
		combo.lastQuery = '';

		combo.getStore().filterBy(function(record) {

			if (record.get('WhsDocumentStatusType_Code').inlist(whsDocStatusTypeCode))
				return true;
		});
	},

	showMsgDelay: function(msg, delay, callback){
        var div = document.createElement('div');

        div.style.background='#dfe8f6';
        div.style.border='solid 2px #efefb3';
        div.style.position='absolute';
        div.style.padding='10px';
        div.style.zIndex='99999';
        div.innerHTML = msg;
        div.style.top = '50%';
        div.style.left = '50%';
        div.style.marginRight = '-50%';
        div.style.transform = 'translate(-50%, -50%)';
        document.body.appendChild(div);

        setTimeout(function(){
            div.parentNode.removeChild(div);
            if(callback)callback();
        }, delay);
    },

	refreshWhsDocumentStatusType: function(){
		var wnd = this;
		if(wnd.WhsDocumentUcInvent_id){
			var doc_id = wnd.WhsDocumentUcInvent_id;
		}else{
			console.warn('Отсутствует идентификатор статус ведомости !!!');
			return false;
		}
		var loadMask = new Ext.LoadMask(this.form.getEl(), {msg:langs('Идет обновление статуса ведомости...')});
        loadMask.show();
		Ext.Ajax.request({
			failure:function () {
				sw.swMsg.alert(langs('Ошибка'), langs('Не удалось обновить статус ведомости'));
				loadMask.hide();
			},
			params:{
				WhsDocumentUcInvent_id: doc_id
			},
			success: function (response) {
				loadMask.hide();
				var result = Ext.util.JSON.decode(response.responseText);
				if(result['success']){
					wnd.showMsgDelay('Статус ведомости обновлен', 1000);
				}
			},
			url:'/?c=WhsDocumentUcInvent&m=updateStatusDocumentUcInvent'
		});
	},
	show: function() {
        var wnd = this;
		sw.Promed.swWhsDocumentUcInventEditWindow.superclass.show.apply(this, arguments);

		var df_form = this.DrugFilterPanel.getForm();

		this.action = '';
		this.callback = Ext.emptyFn;
		this.onHide = Ext.emptyFn;
		this.WhsDocumentUcInvent_id = null;
		this.WhsDocumentStatusType_Code = null;
		this.ARMType = null;

        if ( !arguments[0] ) {
            sw.swMsg.alert(lang['oshibka'], lang['ne_ukazanyi_vhodnyie_dannyie'], function() { wnd.hide(); });
            return false;
        }
		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		}
		if ( arguments[0].callback && typeof arguments[0].callback == 'function' ) {
			this.callback = arguments[0].callback;
		}
		if ( arguments[0].onHide && typeof arguments[0].onHide == 'function' ) {
			this.onHide = arguments[0].onHide;
		}
		if ( arguments[0].owner ) {
			this.owner = arguments[0].owner;
		}
		if ( arguments[0].WhsDocumentUcInvent_id ) {
			this.WhsDocumentUcInvent_id = arguments[0].WhsDocumentUcInvent_id;
		}
		if ( arguments[0].WhsDocumentStatusType_Code ) {
			this.WhsDocumentStatusType_Code = arguments[0].WhsDocumentStatusType_Code*1;
		}
		if ( arguments[0].ARMType ) {
			this.ARMType = arguments[0].ARMType;
		}

		wnd.DrugFilterPanel.findById('WhsDocumentStatusTypeCombo').store.addListener('load',wnd.initDocumentStatusTypeComboFilter());

		df_form.findField('PersonWork_eid').hideContainer();
		this.DrugGrid.setColumnHidden('StorageWork_Person', true);
		this.DrugGrid.setColumnHidden('StorageWork_Comment', true);
		this.DrugGrid.setColumnHidden('StorageWork_endDate', true);

		if (getDrugControlOptions().doc_uc_operation_control) {
			df_form.findField('PersonWork_eid').defaultBaseParams = {Org_id: getGlobalOptions().org_id};
			df_form.findField('PersonWork_eid').fullReset();
			df_form.findField('PersonWork_eid').showContainer();

			this.DrugGrid.setColumnHidden('StorageWork_Person', false);
			this.DrugGrid.setColumnHidden('StorageWork_Comment', false);
			this.DrugGrid.setColumnHidden('StorageWork_endDate', false);
		}

		this.DrugGrid.setColumnHidden('MultiSelectValue', true);
		this.resetDrugFilter();
		this.form.reset();
		this.setTitle(lang['inventarizatsionnaya_vedomost']);

		this.initDrugGridActions();
		df_form.findField('StorageZone_id').getStore().load();
        var loadMask = new Ext.LoadMask(this.form.getEl(), {msg:lang['zagruzka']});
        loadMask.show();
		switch (arguments[0].action) {
			case 'edit':
			case 'view':
				this.setTitle(this.title + (this.action == "edit" ? ": Редактирование" : ": Просмотр"));
				this.setDisabled(this.action == 'view');
				Ext.Ajax.request({
					failure:function () {
						sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_poluchit_dannyie_s_servera']);
						loadMask.hide();
						wnd.hide();
					},
					params:{
						WhsDocumentUcInvent_id: wnd.WhsDocumentUcInvent_id
					},
					success: function (response) {

						loadMask.hide();
						var result = Ext.util.JSON.decode(response.responseText);

						if (result[0]) {
							wnd.form.setValues(result[0]);
						}

						// подгружаем список описей ведомости
						wnd.InventoryNumGrid.loadData({
							globalFilters: {
								WhsDocumentUcInvent_id: wnd.WhsDocumentUcInvent_id
							}
						});

						// ищем медикаменты для ведомости
						wnd.searchDrug();
					},
					url:'/?c=WhsDocumentUcInvent&m=load'
				});
			break;
		}
	},
	initComponent: function() {
		var wnd = this;		
		
		var form = new Ext.Panel({
			region: 'north',
			autoScroll: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			autoHeight: true,
			border: false,
			frame: true,
			labelAlign: 'right',
			items: [{
				xtype: 'form',
				autoHeight: true,
				id: 'WhsDocumentUcInventEditForm',
				style: 'margin-bottom: 0.5em;',
				bodyStyle:'background:#DFE8F6;padding:5px;',
				border: true,
				labelWidth: 200,
				collapsible: true,
				url:'/?c=WhsDocumentUcInvent&m=save',
				items: [{
					xtype: 'hidden',
					name: 'WhsDocumentStatusType_Code'
				}, {
					xtype: 'hidden',
					name: 'Org_id'
				}, {
					xtype: 'hidden',
					name: 'Storage_id'
				}, {
					xtype: 'hidden',
					name: 'StorageZone_id'
				}, {
					xtype: 'hidden',
					name: 'DrugFinance_id'
				}, {
					xtype: 'hidden',
					name: 'WhsDocumentCostItemType_id'
				}, {
					xtype: 'textfield',
					fieldLabel: lang['organizatsiya'],
					name: 'Org_Name',
					anchor: '60%',
					disabled: true
				}, {
					xtype: 'textfield',
					fieldLabel: lang['sklad'],
					name: 'Storage_Name',
					anchor: '60%',
					disabled: true
				}, {
					layout:'form',
					hidden:(!getGlobalOptions().orgtype.inlist(['farm','reg_dlo'])),
					items:[{
						xtype: 'textfield',
						fieldLabel: 'Место хранения',
						name: 'StorageZone_Name',
						anchor: '60%',
						disabled: true
					}]
				}, {
					xtype: 'textfield',
					fieldLabel: lang['№_vedomosti'],
					name: 'WhsDocumentUc_Num',
					anchor: '60%',
					disabled: true
				}, {
					xtype: 'textfield',
					fieldLabel: lang['data'],
					name: 'WhsDocumentUc_Date',
					anchor: '60%',
					disabled: true
				}, {
					xtype: 'textfield',
					fieldLabel: lang['istochnik_finansirovaniya'],
					name: 'DrugFinance_Name',
					anchor: '60%',
					disabled: true
				}, {
					xtype: 'textfield',
					fieldLabel: lang['statya_rashodov'],
					name: 'WhsDocumentCostItemType_Name',
					anchor: '60%',
					disabled: true
				}]
			}]
		});

		this.DrugFilterPanel = new Ext.FormPanel({
			frame: false,
			border: false,
			bodyStyle: 'background: #DFE8F6; padding-top: 5px; border-bottom: 1px solid #99bbe8;',
			region: 'north',
			autoHeight: true,
			labelAlign: 'right',
			keys: [{
				fn: function(e) {
					this.searchDrug();
				}.createDelegate(this),
				key: Ext.EventObject.ENTER,
				stopEvent: true
			}],
			items: [{
				layout: 'column',
				bodyStyle: 'background: #DFE8F6;',
				border: false,
                labelWidth: 85,
				defaults: {
					bodyStyle: 'background: #DFE8F6;',
					border: false,
				},
				items: [{
					layout: 'form',
                    labelWidth: 60,
					items: [{
						xtype: 'SwWhsDocumentStatusTypeCombo',
						fieldLabel: lang['status'],
						hiddenName: 'WhsDocumentStatusType_id',
						id: 'WhsDocumentStatusTypeCombo',
						width: 120
					}]
				},{
					layout: 'form',
                    labelWidth: 105,
					items: [{
						xtype: 'swstoragezonecombo',
						fieldLabel: 'Место хранения',
						hiddenName: 'StorageZone_id',
						width: 150
					}]
				}, {
					layout: 'form',
					items: [{
						xtype: 'textfield',
						name: 'Drug_Name',
						fieldLabel: 'Медикамент',
						width: 150
					}]
				}, {
					layout: 'form',
                    labelWidth: 65,
					items: [{
						xtype: 'swgoodsunitcombo',
                        hiddenName: 'GoodsUnit_id',
						fieldLabel: 'Ед.учета',
						width: 100
					}]
				}, {
					layout: 'form',
					items: [{
						xtype: 'swpersonworkcombo',
						hiddenName: 'PersonWork_eid',
						fieldLabel: 'Исполнитель',
						width: 150,
						listWidth: 500
					}]
				}, {
					layout: 'form',
					style: 'margin-left: 15px;',
					items: [{
						xtype: 'button',
						handler: function() {
							this.searchDrug();
						}.createDelegate(this),
						iconCls: 'search16',
						text: BTN_FRMSEARCH
					}]
				}, {
					layout: 'form',
					style: 'margin-left: 10px;',
					items: [{
						xtype: 'button',
						handler: function() {
							this.searchDrug(true);
						}.createDelegate(this),
						iconCls: 'resetsearch16',
						text: BTN_FRMRESET
					}]
				}]
			}]
		});

		this.InventoryNumGrid = new sw.Promed.ViewFrame({
			actions: [
				{name: 'action_add', handler: function(){this.openWhsDocumentUcInventDrugInventoryEditWindow('add')}.createDelegate(this)},
				{name: 'action_edit', handler: function(){this.openWhsDocumentUcInventDrugInventoryEditWindow('edit')}.createDelegate(this)},
				{name: 'action_delete'},
				{name: 'action_print'},
				{name: 'action_refresh'},
				{name: 'action_view', hidden:true}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			border: false,
			dataUrl: '/?c=WhsDocumentUcInvent&m=loadWhsDocumentUcInventDrugInventoryNumList',
			height: 220,
			object: 'WhsDocumentUcInventDrugInventory',
			id: 'wduieWhsDocumentUcInventDrugInventoryGrid',
			editformclassname: 'swWhsDocumentUcInventDrugInventoryEditWindow',
			paging: false,
			style: 'margin: 0px',
			toolbar: true,
			contextmenu: false,
			useEmptyRecord: false,
			stringfields: [
				{name: 'WhsDocumentUcInventDrugInventory_id', type: 'int', key: true, hidden: true},
				{name: 'WhsDocumentUcInventDrugInventory_InvNum', type: 'string', header: lang['№_opisi'], width: 100},
				{name: 'WhsDocumentStatusType_Name', type: 'string', header: lang['status'], width: 300},
				{name: 'StorageWork_Person', type: 'string', header: 'Исполнитель', id: 'autoexpand'}
			],
		});

		this.DrugGrid = new sw.Promed.ViewFrame({
			actions: [
				{name: 'action_add', handler: function(){this.openWhsDocumentUcInventDrugEditWindow('add')}.createDelegate(this)},
				{name: 'action_edit', handler: function(){this.openWhsDocumentUcInventDrugEditWindow('edit')}.createDelegate(this)},
				{name: 'action_view', handler: function(){this.openWhsDocumentUcInventDrugEditWindow('view')}.createDelegate(this)},
				{name: 'action_delete', handler: function(){this.deleteWhsDocumentUcInventDrug()}.createDelegate(this)},
				{name: 'action_refresh', hidden: true},
				{name: 'action_print', hidden: true},
				{name: 'action_save', hidden: true, handler: function(o) {
					switch(o.field) {
						case 'WhsDocumentUcInventDrug_FactKolvo':
							this.saveDrugFactKolvo(o.record);
							break;
						case 'StorageWork_Comment':
							this.saveStorageWorkComment(o.record);
							break;
					}
				}.createDelegate(this)}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			border: false,
			dataUrl: '/?c=WhsDocumentUcInvent&m=loadWhsDocumentUcInventDrugList',
			height: 220,
			object: 'WhsDocumentUcInventDrug',
			id: 'wduieWhsDocumentUcInventDrugGrid',
			paging: false,
			region: 'center',
			style: 'margin: 0px',
			toolbar: true,
			contextmenu: false,
			editing: true,
			useEmptyRecord: false,
			selectionModel: 'multiselect',
			groupTextTpl: '{text} ({[values.rs.length]} {["заявки"]})',
			grouping: true,
			groupingView: {showGroupName: false, showGroupsText: true},
			stringfields: [
				{name: 'WhsDocumentUcInventDrug_id', type: 'int', header: 'ID', key: true},
				{name: 'Server_id', type: 'int', hidden: true},
				{name: 'WhsDocumentStatusType_Name', type: 'string', header: lang['status'], width: 100},
				{name: 'StorageZone_Name', header: 'Место хранения', width: 120},
				{name: 'Drug_Code', header: lang['kod_lp'], width: 100, hidden: !(getGlobalOptions().region.nick == 'ufa' && getGlobalOptions().orgtype == 'farm')},
				{name: 'Drug_Name', type: 'string', header: lang['lp'], width: 150, id: 'autoexpand'},
				{name: 'PrepSeries_isDefect', hidden: true},
				{name: 'PrepSeries_Ser', header: langs('Серия'), width: 100, renderer: function(v, p, record) { return record.get('PrepSeries_isDefect') == '1' ? '<div style="color: #ff0000">'+v+'</div>' : v; }},
				{name: 'DrugShipment_Name', type: 'string', header: langs('Партия'), width: 100},
				{name: 'WhsDocumentSupply_Name', type: 'string', header: langs('ГК'), width: 100},
				{name: 'SubAccountType_Name', type: 'string', header: langs('Субсчет'), width: 100},
				{name: 'WhsDocumentUcInventDrug_Cost', type: 'string', header: langs('Цена'), width: 100},
                {name: 'GoodsUnit_Name', type: 'string', header: langs('Ед.учета'), width: 100},
				{name: 'WhsDocumentUcInventDrug_Kolvo', type: 'string', header: langs('Кол-во'), width: 100},
				{name: 'WhsDocumentUcInventDrug_FactKolvo', type: 'float', header: langs('Факт. кол-во.'), width: 100, editor: new Ext.form.NumberField() },
				{name: 'StorageWork_id', type: 'int', hidden: true},
				{name: 'StorageWork_Person', type: 'string', header: 'Исполнитель', width: 180},
				{name: 'StorageWork_Comment', type: 'string', header: 'Примечание', width: 180, editor: new Ext.form.TextField()},
				{name: 'StorageWork_endDate', type: 'string', header: 'Дата и время исполнения', width: 180},
				{name: 'WhsDocumentUcInventDrugInventory_InvNum',hidden: true, group: true, sort: true, direction: 'ASC', header: '№ Описи'},
			],
			onRowSelect: function(sm, index, record) {
				this.onRowSelectChange();
			}.createDelegate(this),
			onRowDeSelect: function(sm, index, record) {
				this.onRowSelectChange();
			}.createDelegate(this)
		});
		
		this.DrugGrid.getGrid().view = new Ext.grid.GridView({
			getRowClass : function (row, index) {
			    var cls = '';
			    if (row.get('WhsDocumentUcInventDrug_Kolvo') != row.get('WhsDocumentUcInventDrug_FactKolvo')) {//  ЛС в пути
				    cls = cls+'x-grid-rowbackyellow';
			    }
			    return cls;
			}
		});

		Ext.apply(this, {
			buttons:
			[{
				handler: function() {
					this.ownerCt.setApproved();
				},
				iconCls: 'ok16',
				text: lang['utverdit']
			},{
				handler: function() {
					this.ownerCt.createDocumentUc('DocOprih');
				},
				iconCls: 'add16',
				text: lang['oprihodovat']
			},{
				handler: function() {
					this.ownerCt.createDocumentUc('DokSpis');
				},
				iconCls: 'delete16',
				text: lang['spisat']
			},{
				handler: function() {
					wnd.refreshWhsDocumentStatusType();
				},
				iconCls: 'refresh16',
				text: lang['obnovit_status_vedomosti']
			},{
				handler: function() {
					Consolidated = 1;
					this.ownerCt.doPrint();
				},
				iconCls: 'print16',
				text: 'Печать по всем складам',
				hidden: !(getGlobalOptions().region.nick == 'ufa' && getGlobalOptions().orgtype == 'farm')
				
			},
			{
				handler: function() {
					Consolidated = 2;
					this.ownerCt.doPrint();
				},
				iconCls: 'print16',
				//text: lang['pechat']
				text: !(getGlobalOptions().region.nick == 'ufa' && getGlobalOptions().orgtype == 'farm') ? lang['pechat'] : 'Печать по текущему складу'
			},
			{
				text: '-'
			},
			HelpButton(this, 0),
			{
				handler: function() 
				{
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],
			layout: 'border',
			items:[
				form,
				{
					region: 'center',
					title: 'Список «Описи»',
					items: [
						this.InventoryNumGrid
					]
				},
				{  
					region: 'south',
					title: lang['spisok_medikamentov'],
					height: 400,
					layout: 'border',
					items: [
						this.DrugFilterPanel,
						this.DrugGrid
					],
				},
			]
		});
		sw.Promed.swWhsDocumentUcInventEditWindow.superclass.initComponent.apply(this, arguments);
		this.form = this.findById('WhsDocumentUcInventEditForm').getForm();
	}	
});