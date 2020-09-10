/**
* swRegistryLLOViewWindow - окно просмотра списка реестра рецептов
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2015 Swan Ltd.
* @author       Salakhov R.
* @version      07.2015
* @comment      
*/
sw.Promed.swRegistryLLOViewWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: lang['reestryi_retseptov_obespechennyih_ls'],
	layout: 'border',
	id: 'RegistryLLOViewWindow',
	modal: true,
	shim: false,
	width: 400,
	resizable: false,
	maximizable: true,
	maximized: true,
    setOrgValueByData: function(combo, data) {
        combo.getStore().removeAll();
        combo.getStore().loadData([{
            Org_id: data.Org_id,
            Org_Name: data.Org_Name
        }]);
        combo.setValue(data[combo.valueField]);

        var index = combo.getStore().findBy(function(rec) { return rec.get(combo.valueField) == data[combo.valueField]; });

        if (index == -1) {
            return false;
        }

        var record = combo.getStore().getAt(index);
        combo.fireEvent('select', combo, record, 0)
    },
	getActiveSearchGrid: function() {
		var grid = this.SearchNewGrid;

		switch(this.SearchGridTab.getActiveTab().id) {
			case 'rlv_tab_formed':
				grid = this.SearchFormedGrid;
				break;
			case 'rlv_tab_in_work':
				grid = this.SearchInWorkGrid;
				break;
			case 'rlv_tab_for_pay':
				grid = this.SearchForPayGrid;
				break;
			case 'rlv_tab_paid':
				grid = this.SearchPaidGrid;
				break;
		}

		return grid;
	},
	doSearch: function() {
		var wnd = this;
		var form = wnd.FilterPanel.getForm();
		var grid = wnd.getActiveSearchGrid();

		grid.removeAll();
		params = form.getValues();

		params.Year = Ext.getCmp('rlv_Year').getValue();
		params.start = 0;
		params.limit = 100;
		params.RegistryStatus_Code = grid.RegistryStatus_Code;
		params.Org_id = !Ext.isEmpty(grid.Org_id) ? grid.Org_id : null;

		grid.loadData({globalFilters: params});
	},
	doReset: function() {
		var wnd = this;
		var form = wnd.FilterPanel.getForm();
		form.reset();
		wnd.getActiveSearchGrid().removeAll();
	},
	resetGridDataState: function() {
		this.SearchNewGrid.DataState = 'empty';
		this.SearchFormedGrid.DataState = 'empty';
		this.SearchInWorkGrid.DataState = 'empty';
		this.SearchForPayGrid.DataState = 'empty';
		this.SearchPaidGrid.DataState = 'empty';
	},
	changeYear: function(value) {
		var date_field = Ext.getCmp('rlv_Year');
		var val = date_field.getValue();
		if (!val || value == 0) {
			val = (new Date()).getFullYear();
		}
		date_field.setValue(val+value);
	},
	formingRegistry: function(reforming) {
		var wnd = this;
		var record = this.getActiveSearchGrid().getGrid().getSelectionModel().getSelected();

		sw.swMsg.show({
			icon: Ext.MessageBox.QUESTION,
			msg: reforming ? lang['pereformirovat_reestr'] : lang['peredat_reestr_na_formirovanie'],
			title: lang['podtverjdenie'],
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ('yes' == buttonId) {
					Ext.Ajax.request({
						params: {
							RegistryLLO_id: record.get('RegistryLLO_id'),
							reforming: reforming ? 1 : null
						},
						callback: function (options, success, response) {
							var result = Ext.util.JSON.decode(response.responseText);
							if (result && result.success) {
								wnd.doSearch();

								//помечаем данные связанного грида как устаревшие
								if (reforming) {
									if (wnd.SearchNewGrid.DataState != 'empty') {
										wnd.SearchNewGrid.DataState = 'outdated';
									}
								} else {
									if (wnd.SearchFormedGrid.DataState != 'empty') {
										wnd.SearchFormedGrid.DataState = 'outdated';
									}
								}
							}
						},
						url:'/?c=RegistryLLO&m=forming'
					});
				}
			}
		});
	},
	recountRegistry: function() {
		var wnd = this;
		var record = this.getActiveSearchGrid().getGrid().getSelectionModel().getSelected();

		Ext.Ajax.request({
			params: {
				RegistryLLO_id: record.get('RegistryLLO_id')
			},
			callback: function (options, success, response) {
				var result = Ext.util.JSON.decode(response.responseText);
				if (result && result.success) {
					wnd.doSearch();
				}
			},
			url:'/?c=RegistryLLO&m=recount'
		});
	},
	setRegistryStatus: function(status_code) {
		var wnd = this;
		var record = this.getActiveSearchGrid().getGrid().getSelectionModel().getSelected();
		var msg = lang['smenit_status_reestra'];

		if (status_code == 3) {
			msg = lang['peredat_reestr_na_ekspertizu'];
		}

		sw.swMsg.show({
			icon: Ext.MessageBox.QUESTION,
			msg: msg,
			title: lang['podtverjdenie'],
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ('yes' == buttonId) {
					Ext.Ajax.request({
						params: {
							RegistryLLO_id: record.get('RegistryLLO_id'),
							RegistryStatus_Code: status_code
						},
						callback: function (options, success, response) {
							var result = Ext.util.JSON.decode(response.responseText);
							if (result && result.success) {
								wnd.doSearch();

								switch(status_code) {
									case 3: //3 - В работе
										if (wnd.SearchInWorkGrid.DataState = 'empty') {
											wnd.SearchInWorkGrid.DataState = 'outdated';
										}
										break;
								}
							}
						},
						url:'/?c=RegistryLLO&m=setRegistryStatus'
					});
				}
			}
		});

	},
    editFinDocument: function(grid) {
        var wnd = this;
        var record = grid.getGrid().getSelectionModel().getSelected();
        if (record.get('RegistryLLO_id')) {
            var params = new Object();
            params.RegistryLLO_id = record.get('RegistryLLO_id');
            params.FinDocument_id = record.get('FinDocument_id');
            params.FinDocument_Sum = getGlobalOptions().OrgFarmacy_id > 0 ? record.get('Registry_Sum2') : record.get('RegistryLLO_Sum');
            params.action = record.get('FinDocument_id') > 0 ? 'edit' : 'add';
            params.ARMType = this.ARMType;
            params.callback = function(owner, params) {
                grid.refreshRecords(null,0);
                if (params.RegistryStatus_isChanged) {
                    //помечаем связанные гриды для обновления
                    if (params.RegistryStatus_Code == 2 && wnd.SearchForPayGrid.DataState != 'empty') { //2 - К оплате
                        wnd.SearchForPayGrid.DataState = 'outdated';
                    }
                    if (params.RegistryStatus_Code == 4 && wnd.SearchPaidGrid.DataState != 'empty') { //4 - Оплачен
                        wnd.SearchPaidGrid.DataState = 'outdated';
                    }
                }
            }
            if (grid.RegistryStatus_Code == 3) { //3 - В работе
                params.HideDocGrid = true;
            }
            getWnd('swFinDocumentEditWindow').show(params);
        }
    },
	doExpertise: function(grid_panel, options) {
		options = options || {};

		var grid = grid_panel.getGrid();
		var record = grid.getSelectionModel().getSelected();

		if (!record || Ext.isEmpty(record.get('RegistryLLO_id'))) {
			return false;
		}

		this.getLoadMask('Выполнение экспертизы...').show();

		Ext.Ajax.request({
			url: '/?c=RegistryLLO&m=expertise',
			params: {RegistryLLO_id: record.get('RegistryLLO_id')},
			success: function() {
				this.getLoadMask().hide();
				grid_panel.getAction('action_refresh').execute();
				if (typeof options.callback == 'function') {
					options.callback();
				}
			}.createDelegate(this),
			failure: function() {
				this.getLoadMask().hide();
			}.createDelegate(this)
		});
	},
	setExpertiseResult: function(grid_panel) {
		var grid = grid_panel.getGrid();
		var record = grid.getSelectionModel().getSelected();

		if (!record || Ext.isEmpty(record.get('RegistryLLO_id'))) {
			return false;
		}

		var openForm = function() {
			var params = {};
			params.formParams = {
				RegistryLLO_id: record.get('RegistryLLO_id')
			};
			params.callback = function() {
				grid_panel.getAction('action_refresh').execute();
			}.createDelegate(this);

			getWnd('swRegistryLLOExpertiseWindow').show(params);
		}.createDelegate(this);

		if (record.get('ReceptUploadStatus_Code').inlist([3,4,5])) {
			openForm();
		} else {
			this.doExpertise(grid_panel, {callback: openForm});
		}
	},
	deleteRegistryLLO: function(grid_panel) {
		var grid = grid_panel.getGrid();

		var record = grid.getSelectionModel().getSelected();

		if (!record || !record.get('RegistryLLO_id')) {
			return false;
		}

		if (grid_panel.RegistryStatus_Code == 3 && !record.get('ReceptUploadStatus_Code').inlist([3,5])) {
			sw.swMsg.alert(lang['oshibka'], 'Удаление реестра не возможно');
		}

		sw.swMsg.show({
			buttons:Ext.Msg.YESNO,
			fn:function (buttonId, text, obj) {
				if (buttonId == 'yes') {
					var params = {id: record.get('RegistryLLO_id')};

					Ext.Ajax.request({
						callback: function(opt, scs, response) {
							var response_obj = Ext.util.JSON.decode(response.responseText);

							if (!response_obj.Error_Msg) {
								grid_panel.getAction('action_refresh').execute();
							}
						}.createDelegate(this),
						params: params,
						url: '/?c=RegistryLLO&m=delete'
					});
				}
			}.createDelegate(this),
			icon:Ext.MessageBox.QUESTION,
			msg: 'Реестр будет удален из списка сформированных реестров Аптеки. Подтверждаете удаление реестра?',
			title:lang['podtverjdenie']
		});
	},
    setDisabledAction: function(grid, actions_id, action, disable) {
        var actions = grid.getAction(actions_id).items[0].menu.items;
        var menu_actions = grid.ViewContextMenu.items.get('id_'+actions_id).menu.items;
        actions.each(function(a) {
            if (a.name == action) {
                if (disable) {
                    a.disable();
                } else {
                    a.enable();
                }
            }
        });
        menu_actions.each(function(a) {
            if (a.name == action) {
                if (disable) {
                    a.disable();
                } else {
                    a.enable();
                }
            }
        });
    },
    hideAction: function(grid, actions_id, action, hide) {
        var actions = grid.getAction(actions_id).items[0].menu.items;
        var menu_actions = grid.ViewContextMenu.items.get('id_'+actions_id).menu.items;
        actions.each(function(a) {
            if (a.name == action) {
                if (hide) {
                    a.hide();
                } else {
                    a.show();
                }
            }
        });
        menu_actions.each(function(a) {
            if (a.name == action) {
                if (hide) {
                    a.hide();
                } else {
                    a.show();
                }
            }
        });
    },
	show: function() {
		sw.Promed.swRegistryLLOViewWindow.superclass.show.apply(this, arguments);

		var wnd = this;
        var region = getGlobalOptions().region.nick;
		this.ARMType = null;

		if (arguments[0] && !Ext.isEmpty(arguments[0].ARMType)) {
			this.ARMType = arguments[0].ARMType;
		}

		this.SearchGridTab.enableSearch = false;
		this.SearchGridTab.setActiveTab(4);
		this.SearchGridTab.setActiveTab(3);
		this.SearchGridTab.setActiveTab(2);
		this.SearchGridTab.setActiveTab(1);
		this.SearchGridTab.setActiveTab(0);
		this.SearchGridTab.enableSearch = true;

        //настройка видимости вкладок
        if (this.ARMType == 'merch' || this.ARMType == 'pmllo') {
            wnd.SearchGridTab.unhideTabStripItem('rlv_tab_new');
            wnd.SearchGridTab.unhideTabStripItem('rlv_tab_formed');
        } else {
            wnd.SearchGridTab.hideTabStripItem('rlv_tab_new');
            wnd.SearchGridTab.hideTabStripItem('rlv_tab_formed');
        }

        if ((this.ARMType == 'merch' || this.ARMType == 'pmllo') || this.ARMType == 'spesexpertllo' || (this.ARMType == 'mekllo' && region == 'saratov')) { //merch - АРМ Товароведа; spesexpertllo - АРМ специалиста по экспертизе ЛЛО; mekllo - АРМ МЭК ЛЛО.
            wnd.SearchGridTab.unhideTabStripItem('rlv_tab_in_work');
        } else {
            wnd.SearchGridTab.hideTabStripItem('rlv_tab_in_work');
        }

        if (this.ARMType == 'merch' || this.ARMType == 'pmllo' || this.ARMType == 'minzdravdlo') { //merch - АРМ Товароведа; minzdravdlo - АРМ специалиста ЛЛО.
            wnd.SearchGridTab.unhideTabStripItem('rlv_tab_for_pay');
        } else {
            wnd.SearchGridTab.hideTabStripItem('rlv_tab_for_pay');
        }

        if (this.ARMType == 'merch' || this.ARMType == 'pmllo' || this.ARMType == 'minzdravdlo') { //merch - АРМ Товароведа; minzdravdlo - АРМ специалиста ЛЛО.
            wnd.SearchGridTab.unhideTabStripItem('rlv_tab_paid');
        } else {
            wnd.SearchGridTab.hideTabStripItem('rlv_tab_paid');
        }

        //получение номера первой видимой вкладки
        var active_tab_idx = -1
        for (var i = 0; i < 5; i++) {
            var el = wnd.SearchGridTab.getTabEl(i);
            if(el && el.style.display == ''){
                active_tab_idx = i;
                break;
            }
        }

        //переключение на первую видимую вкладку, если все вкладки закрыты значит нужно закрыть форму
        if (active_tab_idx >= 0) {
            this.SearchGridTab.setActiveTab(active_tab_idx);
        } else {
            sw.swMsg.alert(lang['oshibka'], lang['prosmotr_reestrov_nedostupen'], function() { wnd.hide(); });
            return false;
        }

		this.resetGridDataState();
        this.SearchFormedGrid.setParam('ARMType', this.ARMType, false);
        this.SearchInWorkGrid.setParam('ARMType', this.ARMType, false);
        this.SearchForPayGrid.setParam('ARMType', this.ARMType, false);
        this.SearchPaidGrid.setParam('ARMType', this.ARMType, false);

        this.SearchNewGrid.Org_id = getGlobalOptions().org_id;
        this.SearchFormedGrid.Org_id = getGlobalOptions().org_id;
        this.SearchInWorkGrid.Org_id = this.ARMType == 'merch' || this.ARMType == 'pmllo' ? getGlobalOptions().org_id : null;
        this.SearchForPayGrid.Org_id = this.ARMType == 'merch' || this.ARMType == 'pmllo' ? getGlobalOptions().org_id : null;
        this.SearchPaidGrid.Org_id = this.ARMType == 'merch' || this.ARMType == 'pmllo' ? getGlobalOptions().org_id : null;


		if(!this.SearchNewGrid.getAction('action_rlv_forming')) {
			this.SearchNewGrid.addActions({
				name: 'action_rlv_forming',
				text: lang['sformirovat'],
				iconCls: 'actions16',
				handler: function() {
					wnd.formingRegistry();
				}
			});
		}

		if(!this.SearchFormedGrid.getAction('action_rlv_actions_f')) {
			this.SearchFormedGrid.addActions({
				name:'action_rlv_actions_f',
				text:lang['deystviya'],
				iconCls: 'actions16',
				menu: [{
					name: 'action_rlv_reforming',
					text: lang['pereformirovat'],
					iconCls: 'actions16',
					handler: function() {
						wnd.formingRegistry(true);
					}
				}, {
					name: 'action_rlv_recount',
					text: lang['pereschitat'],
					iconCls: 'actions16',
					handler: function() {
						wnd.recountRegistry();
					}
				}, {
					name: 'action_rlv_set_status_3',
					text: lang['peredat_na_ekspertizu'],
					iconCls: 'actions16',
					handler: function() {
						wnd.setRegistryStatus(3); //3 - В работе
					}
				}]
			});
		}

		if(!this.SearchInWorkGrid.getAction('action_rlv_actions_iw')) {
			this.SearchInWorkGrid.addActions({
				name:'action_rlv_actions_iw',
				text:lang['deystviya'],
				iconCls: 'actions16',
				menu: [{
					name: 'action_rlv_create_fin_document',
					text: lang['sozdat_schet'],
					iconCls: 'actions16',
					handler: function() {
						wnd.editFinDocument(wnd.SearchInWorkGrid);
					}
				}, {
					name: 'action_rlv_set_expertise_result',
					text: lang['ruchnoy_vvod_rezultatov_ekspertizyi'],
					iconCls: 'actions16',
					handler: function() {
						wnd.setExpertiseResult(wnd.SearchInWorkGrid);
					}
				}]
			});
		}

		if (!this.SearchInWorkGrid.getAction('action_rlv_expertise')) {
			this.SearchInWorkGrid.addActions({
				name: 'action_rlv_expertise',
				text: lang['ekspertiza'],
				iconCls: 'actions16',
				handler: function() {
					wnd.doExpertise(wnd.SearchInWorkGrid);
				}
			});
		}

		if(!this.SearchForPayGrid.getAction('action_rlv_edit_fin_document')) {
			this.SearchForPayGrid.addActions({
                name: 'action_rlv_edit_fin_document',
                text: lang['redaktirovat_schet'],
                iconCls: 'actions16',
                handler: function() {
                    wnd.editFinDocument(wnd.SearchForPayGrid);
                }
			});
		}

		if(!this.SearchForPayGrid.getAction('action_rlv_pay_fin_document')) {
			this.SearchForPayGrid.addActions({
                name: 'action_rlv_pay_fin_document',
                text: lang['oplatit'],
                iconCls: 'actions16',
                handler: function() {
                    wnd.editFinDocument(wnd.SearchForPayGrid);
                }
			});
		}

        //установка доступности и видимости экшенов
		if (this.ARMType == 'spesexpertllo' || (this.ARMType == 'mekllo' && region == 'saratov')) {
			this.SearchInWorkGrid.getAction('action_rlv_expertise').show();
		} else {
			this.SearchInWorkGrid.getAction('action_rlv_expertise').hide();
		}

		var show_actions_iw = (this.ARMType == 'merch' || this.ARMType == 'spesexpertllo' || (this.ARMType == 'mekllo' && region == 'saratov')); //merch - АРМ Товароведа; spesexpertllo - АРМ специалиста по экспертизе ЛЛО; mekllo - АРМ МЭК ЛЛО.
		var show_create_fin_document = (this.ARMType == 'merch' || this.ARMType == 'pmllo');
		var show_set_expertise_result = (IS_DEBUG || Ext.globalOptions.others.demo_server);

        if (show_actions_iw && (show_create_fin_document || show_set_expertise_result)) {
            this.SearchInWorkGrid.getAction('action_rlv_actions_iw').show();
        } else {
            this.SearchInWorkGrid.getAction('action_rlv_actions_iw').hide();
        }

        this.SearchInWorkGrid.setActionHidden('action_delete', (this.ARMType == 'pmllo'));

        var hideable_actions_array = [{
            name: 'action_rlv_create_fin_document',
            hidden: !show_create_fin_document
        }, {
            name: 'action_rlv_set_expertise_result',
            hidden: !show_set_expertise_result
        }];

        for(var i = 0; i < hideable_actions_array.length; i++) {
            this.setDisabledAction(this.SearchInWorkGrid, 'action_rlv_actions_iw', hideable_actions_array[i].name, hideable_actions_array[i].hidden);
            this.hideAction(this.SearchInWorkGrid, 'action_rlv_actions_iw', hideable_actions_array[i].name, hideable_actions_array[i].hidden);
        }

        if (this.ARMType == 'merch' || this.ARMType == 'pmllo') { //merch - АРМ Товароведа
            this.SearchForPayGrid.getAction('action_rlv_edit_fin_document').show();
        } else {
            this.SearchForPayGrid.getAction('action_rlv_edit_fin_document').hide();
        }

        if (this.ARMType == 'minzdravdlo') { //minzdravdlo - АРМ специалиста ЛЛО
            this.SearchForPayGrid.getAction('action_rlv_pay_fin_document').show();
        } else {
            this.SearchForPayGrid.getAction('action_rlv_pay_fin_document').hide();
        }

        var form = this.FilterPanel.getForm();

        //настройка видимости фильтров
        if (this.ARMType == 'merch' || this.ARMType == 'pmllo') { //merch - АРМ Товароведа, pmllo - АРМ поставщика
            form.findField('Org_id').hideContainer();
        } else {
            form.findField('Org_id').showContainer();
        }

        //изменение высоты панели фильтров требует перерисовки окна
        this.doLayout();

        form.findField('Org_id').getStore().proxy.conn.url = C_ORG_LIST;

		Ext.getCmp('rlv_Year').setValue(null);
		this.changeYear(0);
		this.doSearch();

        form.findField('WhsDocumentCostItemType_id').setFilter(function(record) {
			return (record.get('WhsDocumentCostItemType_IsDlo') == '2');
		});
	},
	initComponent: function() {
		var wnd = this;

		this.YearToolbar = new Ext.Toolbar({
			items: [{
				xtype: 'button',
				disabled: true,
				text: lang['god']
			}, {
				text: null,
				xtype: 'button',
				iconCls: 'arrow-previous16',
				handler: function() {
					wnd.changeYear(-1);
					wnd.resetGridDataState();
					wnd.doSearch();
				}.createDelegate(this)
			}, {
				xtype : "tbseparator"
			}, {
				xtype : 'numberfield',
				id: 'rlv_Year',
				allowDecimal: false,
				allowNegtiv: false,
				width: 35,
				enableKeyEvents: true,
				listeners: {
					'keydown': function (inp, e) {
						if (e.getKey() == Ext.EventObject.ENTER) {
							e.stopEvent();
							wnd.resetGridDataState();
							wnd.doSearch();
						}
					}
				}
			}, {
				xtype : "tbseparator"
			}, {
				text: null,
				xtype: 'button',
				iconCls: 'arrow-next16',
				handler: function() {
					wnd.changeYear(1);
					wnd.resetGridDataState();
					wnd.doSearch();
				}.createDelegate(this)
			}, {
				xtype: 'tbfill'
			}]
		});

		this.FilterFieldsPanel = new sw.Promed.Panel({
			layout: 'form',
			autoScroll: true,
			bodyBorder: false,
			labelAlign: 'right',
			labelWidth: 180,
			border: false,
			frame: true,
			items: [{
				layout: 'form',
				items: [{
                    xtype: 'sworgcomboex',
                    fieldLabel : lang['organizatsiya'],
                    tabIndex: wnd.firstTabIndex + 10,
                    hiddenName: 'Org_id',
                    id: 'rlv_Org_id',
                    width: 300,
                    editable: true,
                    allowBlank: true,
                    tpl: '<tpl for="."><div class="x-combo-list-item">{Org_Name}</div></tpl>',
                    emptyText: lang['vvedite_chast_nazvaniya'],
                    onTriggerClick: function() {
                        if (this.disabled) {
                            return false;
                        }
                        var combo = this;

                        if (!this.formList) {
                            this.formList = new sw.Promed.swListSearchWindow({
                                title: lang['poisk_organizatsii'],
                                id: 'OrgSearch_' + this.id,
                                object: 'Org',
                                prefix: 'lsswdse1',
                                editformclassname: 'swOrgEditWindow',
                                stringfields: [
                                    {name: 'Org_id',    type:'int'},
                                    {name: 'Org_Name',  type:'string'}
                                ],
                                dataUrl: C_ORG_LIST
                            });
                        }
                        this.formList.show({
                            params: this.getStore().baseParams,
                            onSelect: function(data) {
                                wnd.setOrgValueByData(combo, data);
                            }
                        });
                    }
                }]
			}, {
				xtype: 'daterangefield',
				name: 'RegistryLLO_Date_Range',
				fieldLabel: lang['period_formirovaniya'],
				plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
				width: 200
			}, {
				layout: 'column',
				items: [{
					layout: 'form',
					items: [{
						xtype: 'numberfield',
						fieldLabel: lang['summa_po_retseptam_rub_ot'],
						name: 'MinSum',
						allowNegative: false
					}]
				}, {
					layout: 'form',
					labelWidth: 37,
					items: [{
						xtype: 'numberfield',
						fieldLabel: lang['do'],
						name: 'MaxSum',
						allowNegative: false
					}]
				}]
			}, {
				xtype: 'textfield',
				fieldLabel: lang['kontrakt'],
				name: 'WhsDocumentUc_Num',
				width: 300
			}, {
				xtype: 'swdrugfinancecombo',
				fieldLabel: lang['finansirovanie'],
				name: 'DrugFinance_id',
				width: 300
			}, {
				xtype: 'swwhsdocumentcostitemtypecombo',
				fieldLabel: lang['programma_llo'],
				name: 'WhsDocumentCostItemType_id',
				width: 300
			}, {
				xtype: 'swkatnaselcombo',
				fieldLabel: lang['kategoriya_naseleniya'],
				name: 'KatNasel_id',
				width: 300
			}]
		});

		this.FilterButtonsPanel = new sw.Promed.Panel({
			autoScroll: true,
			bodyBorder: false,
			border: false,
			frame: true,
			items: [{
				layout: 'column',
				items: [{
					layout:'form',
					items: [{
						style: "padding-left: 10px",
						xtype: 'button',
						text: lang['poisk'],
						iconCls: 'search16',
						minWidth: 100,
						handler: function() {
							wnd.doSearch();
						}
					}]
				}, {
					layout:'form',
					items: [{
						style: "padding-left: 10px",
						xtype: 'button',
						text: lang['ochistit'],
						iconCls: 'reset16',
						minWidth: 100,
						handler: function() {
							wnd.doReset();
							wnd.doSearch();
						}
					}]
				}]
			}]
		});

		this.FilterPanel = getBaseFiltersFrame({
			region: 'north',
			defaults: {bodyStyle:'background:#DFE8F6;width:100%;'},
			ownerWindow: wnd,
			toolBar: this.WindowToolbar,
			items: [
				this.FilterFieldsPanel,
				this.FilterButtonsPanel
			]
		});

		this.SearchNewGrid = new sw.Promed.ViewFrame({
			actions: [
				{name: 'action_add'},
				{name: 'action_edit'},
				{name: 'action_view'},
				{name: 'action_delete', handler: function(){wnd.deleteRegistryLLO(wnd.SearchNewGrid)}},
				{name: 'action_print'}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 125,
			autoLoadData: false,
			border: true,
			dataUrl: '/?c=RegistryLLO&m=loadList',
			height: 180,
			object: 'RegistryLLO',
			editformclassname: 'swRegistryLLOEditWindow',
			id: 'rlvRegistryLLONewGrid',
			paging: true,
			root: 'data',
			totalProperty: 'totalCount',
			style: 'margin-bottom: 10px',
			RegistryStatus_Code: 0, //Пустой статус
			stringfields: [
				{ name: 'RegistryLLO_id', type: 'int', header: 'ID', key: true },
				{ name: 'RegistryLLO_Num', type: 'string', header: lang['nomer'], width: 100 },
				{ name: 'RegistryLLO_accDate', type: 'date', header: lang['data'], width: 100 },
				{ name: 'RegistryLLO_Period', type: 'string', header: lang['period'], width: 140 },
				{ name: 'KatNasel_Name', type: 'string', header: lang['kategoriya_naseleniya'], width: 140 },
				{ name: 'DrugFinance_Name', type: 'string', header: lang['finansirovanie'], width: 140 },
				{ name: 'WhsDocumentCostItemType_Name', type: 'string', header: lang['programma_llo'], width: 140 },
				{ name: 'WhsDocumentUc_Num', type: 'string', header: lang['kontrakt'], width: 140 },
				{ name: 'RegistryLLO_updDT', type: 'date', header: lang['data_vneseniya_izmeneniy'], width: 140 }

			],
			title: null,
			toolbar: true,
			onRowSelect: function(sm,rowIdx,record) {
				if (record.get('RegistryLLO_id') > 0) {
					this.ViewActions.action_edit.setDisabled(false);
					this.ViewActions.action_view.setDisabled(false);
					this.ViewActions.action_delete.setDisabled(false);
				} else {
					this.ViewActions.action_edit.setDisabled(true);
					this.ViewActions.action_view.setDisabled(true);
					this.ViewActions.action_delete.setDisabled(true);
				}
			},
			onLoadData: function() {
				this.DataState = 'loaded';
			}
		});

		this.SearchFormedGrid = new sw.Promed.ViewFrame({
			actions: [
				{name: 'action_add', disabled: true, hidden: true},
				{name: 'action_edit'},
				{name: 'action_view'},
				{name: 'action_delete', handler: function(){wnd.deleteRegistryLLO(wnd.SearchFormedGrid)}},
				{name: 'action_print'}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 125,
			autoLoadData: false,
			border: true,
			dataUrl: '/?c=RegistryLLO&m=loadList',
			height: 180,
			object: 'RegistryLLO',
			editformclassname: 'swRegistryDataReceptViewWindow',
			id: 'rlvRegistryLLOFormedGrid',
			paging: true,
			root: 'data',
			totalProperty: 'totalCount',
			style: 'margin-bottom: 10px',
			RegistryStatus_Code: 1, //1 - Сформированные
			stringfields: [
				{ name: 'RegistryLLO_id', type: 'int', header: 'ID', key: true },
				{ name: 'RegistryLLO_Num', type: 'string', header: lang['nomer'], width: 100 },
				{ name: 'RegistryLLO_accDate', type: 'date', header: lang['data'], width: 100 },
				{ name: 'RegistryLLO_Period', type: 'string', header: lang['period'], width: 140, isparams: true },
				{ name: 'KatNasel_Name', type: 'string', header: lang['kategoriya_naseleniya'], width: 140 },
				{ name: 'DrugFinance_Name', type: 'string', header: lang['finansirovanie'], width: 140 },
				{ name: 'WhsDocumentCostItemType_Name', type: 'string', header: lang['programma_llo'], width: 140 },
				{ name: 'WhsDocumentUc_Num', type: 'string', header: lang['kontrakt'], width: 140, isparams: true },
				{ name: 'Registry_Count', type: 'int', header: lang['kolichestvo_retseptov'], width: 140 },
				{ name: 'RegistryLLO_Sum', type: 'money', align: 'right', header: lang['summa_po_lp_rub'], width: 140 },
				{ name: 'Registry_Sum2', type: 'money', align: 'right', header: lang['summa_po_usluge_rub'], width: 140 },
				{ name: 'RegistryLLO_updDT', type: 'date', header: lang['data_vneseniya_izmeneniy'], width: 140 },
                { name: 'DrugFinance_id', hidden: true, isparams: true },
                { name: 'WhsDocumentCostItemType_id', hidden: true, isparams: true },
                { name: 'SupplierContragent_id', hidden: true, isparams: true },
                { name: 'Org_id', hidden: true, isparams: true }
			],
			title: null,
			toolbar: true,
			onRowSelect: function(sm,rowIdx,record) {
				if (record.get('RegistryLLO_id') > 0) {
					this.ViewActions.action_edit.setDisabled(false);
					this.ViewActions.action_view.setDisabled(false);
					this.ViewActions.action_delete.setDisabled(false);
				} else {
					this.ViewActions.action_edit.setDisabled(true);
					this.ViewActions.action_view.setDisabled(true);
					this.ViewActions.action_delete.setDisabled(true);
				}
			},
			onLoadData: function() {
				this.DataState = 'loaded';
			}
		});

		this.SearchInWorkGrid = new sw.Promed.ViewFrame({
			actions: [
				{name: 'action_add', disabled: true, hidden: true},
				{name: 'action_edit'},
				{name: 'action_view'},
				{name: 'action_delete', handler: function(){wnd.deleteRegistryLLO(wnd.SearchInWorkGrid)}},
				{name: 'action_print'}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 125,
			autoLoadData: false,
			border: true,
			dataUrl: '/?c=RegistryLLO&m=loadList',
			height: 180,
			object: 'RegistryLLO',
			editformclassname: 'swRegistryDataReceptViewWindow',
			id: 'rlvRegistryLLOInWorkGrid',
			paging: true,
			root: 'data',
			totalProperty: 'totalCount',
			style: 'margin-bottom: 10px',
			RegistryStatus_Code: 3, //3 - В работе
			stringfields: [
				{ name: 'RegistryLLO_id', type: 'int', header: 'ID', key: true },
				{ name: 'ReceptUploadStatus_Code', type: 'int', hidden: true },
                { name: 'ReceptUploadLog_Data', type: 'string', header: lang['ekspertiza'], width: 140 },
                { name: 'ReceptUploadStatus_Name', type: 'string', header: lang['status_ekspertizyi'], width: 140 },
                { name: 'ReceptUploadLog_Act', header: lang['akt'], width: 140, renderer: function(v, p, r) {
                    return !Ext.isEmpty(v) ? '<a href="'+v+'" target="_blank">скачать</a>' : '';
                }},
                { name: 'Org_Name', type: 'string', header: lang['organizatsiya'], width: 140 },
				{ name: 'RegistryLLO_Num', type: 'string', header: lang['nomer'], width: 100 },
				{ name: 'RegistryLLO_accDate', type: 'date', header: lang['data'], width: 100 },
				{ name: 'RegistryLLO_Period', type: 'string', header: lang['period'], width: 140, isparams: true },
				{ name: 'KatNasel_Name', type: 'string', header: lang['kategoriya_naseleniya'], width: 140 },
				{ name: 'DrugFinance_Name', type: 'string', header: lang['finansirovanie'], width: 140 },
				{ name: 'WhsDocumentCostItemType_Name', type: 'string', header: lang['programma_llo'], width: 140 },
				{ name: 'WhsDocumentUc_Num', type: 'string', header: lang['kontrakt'], width: 140, isparams: true },
                { name: 'Registry_Count', type: 'int', header: lang['kolichestvo_retseptov'], width: 140 },
				{ name: 'Registry_ErrorCount', type: 'int', header: lang['kolichestvo_oshibok'], width: 140 },
                { name: 'RegistryLLO_Sum', type: 'money', align: 'right', header: lang['summa_po_lp_rub'], width: 140 },
                { name: 'Registry_Sum2', type: 'money', align: 'right', header: lang['summa_po_usluge_rub'], width: 140 },
                { name: 'FinDocument_id', hidden: true },
                { name: 'DrugFinance_id', hidden: true, isparams: true },
                { name: 'WhsDocumentCostItemType_id', hidden: true, isparams: true },
                { name: 'SupplierContragent_id', hidden: true, isparams: true },
                { name: 'Org_id', hidden: true, isparams: true }
			],
			title: null,
			toolbar: true,
			onRowSelect: function(sm,rowIdx,record) {
				if (record.get('RegistryLLO_id') > 0) {
					this.ViewActions.action_edit.setDisabled(false);
					this.ViewActions.action_view.setDisabled(false);
					this.ViewActions.action_delete.setDisabled(false);
				} else {
					this.ViewActions.action_edit.setDisabled(true);
					this.ViewActions.action_view.setDisabled(true);
					this.ViewActions.action_delete.setDisabled(true);
				}
			},
			onLoadData: function() {
				this.DataState = 'loaded';
			}
		});

		this.SearchForPayGrid = new sw.Promed.ViewFrame({
			actions: [
				{name: 'action_add', disabled: true, hidden: true},
				{name: 'action_edit', disabled: true, hidden: true},
				{name: 'action_view'},
				{name: 'action_delete', handler: function(){wnd.deleteRegistryLLO(wnd.SearchForPayGrid)}},
				{name: 'action_print'}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 125,
			autoLoadData: false,
			border: true,
			dataUrl: '/?c=RegistryLLO&m=loadList',
			height: 180,
			object: 'RegistryLLO',
			editformclassname: 'swRegistryDataReceptViewWindow',
			id: 'rlvRegistryLLOForPayGrid',
			paging: true,
			root: 'data',
			totalProperty: 'totalCount',
			style: 'margin-bottom: 10px',
			RegistryStatus_Code: 2, //2 - К оплате
			stringfields: [
				{ name: 'RegistryLLO_id', type: 'int', header: 'ID', key: true },
                { name: 'Org_Name', type: 'string', header: lang['organizatsiya'], width: 140 },
				{ name: 'RegistryLLO_Num', type: 'string', header: lang['nomer'], width: 100 },
				{ name: 'RegistryLLO_accDate', type: 'date', header: lang['data'], width: 100 },
				{ name: 'RegistryLLO_Period', type: 'string', header: lang['period'], width: 140, isparams: true },
				{ name: 'KatNasel_Name', type: 'string', header: lang['kategoriya_naseleniya'], width: 140 },
				{ name: 'DrugFinance_Name', type: 'string', header: lang['finansirovanie'], width: 140 },
				{ name: 'WhsDocumentCostItemType_Name', type: 'string', header: lang['programma_llo'], width: 140 },
				{ name: 'WhsDocumentUc_Num', type: 'string', header: lang['kontrakt'], width: 140, isparams: true },
                { name: 'Registry_Count', type: 'int', header: lang['kolichestvo_retseptov'], width: 140 },
                { name: 'RegistryLLO_Sum', type: 'money', align: 'right', header: lang['summa_po_lp_rub'], width: 140 },
                { name: 'Registry_Sum2', type: 'money', align: 'right', header: lang['summa_po_usluge_rub'], width: 140 },
                { name: 'FinDocument_Data', type: 'string', header: lang['schet'], width: 140 },
                { name: 'FinDocument_Sum', type: 'money', align: 'right', header: lang['k_oplate_rub'], width: 140 },
                { name: 'FinDocumentSpec_HtmlData', type: 'string', header: lang['oplata'], width: 140 },
                { name: 'FinDocumentSpec_HtmlSum', type: 'string', align: 'right', header: lang['summa_oplatyi_rub'], width: 140 },
                { name: 'FinDocument_id', hidden: true },
                { name: 'DrugFinance_id', hidden: true, isparams: true },
                { name: 'WhsDocumentCostItemType_id', hidden: true, isparams: true },
                { name: 'SupplierContragent_id', hidden: true, isparams: true },
                { name: 'Org_id', hidden: true, isparams: true }
			],
			title: null,
			toolbar: true,
			onRowSelect: function(sm,rowIdx,record) {
				if (record.get('RegistryLLO_id') > 0) {
					this.ViewActions.action_edit.setDisabled(false);
					this.ViewActions.action_view.setDisabled(false);
					this.ViewActions.action_delete.setDisabled(false);
				} else {
					this.ViewActions.action_edit.setDisabled(true);
					this.ViewActions.action_view.setDisabled(true);
					this.ViewActions.action_delete.setDisabled(true);
				}
			},
			onLoadData: function() {
				this.DataState = 'loaded';
			}
		});

		this.SearchPaidGrid = new sw.Promed.ViewFrame({
			actions: [
				{name: 'action_add', disabled: true, hidden: true},
				{name: 'action_edit'},
				{name: 'action_view'},
				{name: 'action_delete', handler: function(){wnd.deleteRegistryLLO(wnd.SearchPaidGrid)}},
				{name: 'action_print'}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 125,
			autoLoadData: false,
			border: true,
			dataUrl: '/?c=RegistryLLO&m=loadList',
			height: 180,
			object: 'RegistryLLO',
			editformclassname: 'swRegistryDataReceptViewWindow',
			id: 'rlvRegistryLLOPaidGrid',
			paging: true,
			root: 'data',
			totalProperty: 'totalCount',
			style: 'margin-bottom: 10px',
			RegistryStatus_Code: 4, //4 - Оплаченные
			stringfields: [
				{ name: 'RegistryLLO_id', type: 'int', header: 'ID', key: true },
				{ name: 'Org_Name', type: 'string', header: lang['organizatsiya'], width: 140 },
				{ name: 'RegistryLLO_Num', type: 'string', header: lang['nomer'], width: 100 },
				{ name: 'RegistryLLO_accDate', type: 'date', header: lang['data'], width: 100 },
				{ name: 'RegistryLLO_Period', type: 'string', header: lang['period'], width: 140, isparams: true },
				{ name: 'KatNasel_Name', type: 'string', header: lang['kategoriya_naseleniya'], width: 140 },
				{ name: 'DrugFinance_Name', type: 'string', header: lang['finansirovanie'], width: 140 },
				{ name: 'WhsDocumentCostItemType_Name', type: 'string', header: lang['programma_llo'], width: 140 },
				{ name: 'WhsDocumentUc_Num', type: 'string', header: lang['kontrakt'], width: 140, isparams: true },
                { name: 'Registry_Count', type: 'int', header: lang['kolichestvo_retseptov'], width: 140 },
                { name: 'RegistryLLO_Sum', type: 'money', align: 'right', header: lang['summa_po_lp_rub'], width: 140 },
                { name: 'Registry_Sum2', type: 'money', align: 'right', header: lang['summa_po_usluge_rub'], width: 140 },
                { name: 'Schet', type: 'string', header: lang['schet'], width: 140 },
                { name: 'Schet_Sum', type: 'money', align: 'right', header: lang['k_oplate_rub'], width: 140 },
                { name: 'Oplata_Sum', type: 'money', align: 'right', header: lang['summa_oplatyi_rub'], width: 140 },
                { name: 'FinDocument_Data', type: 'string', header: lang['schet'], width: 140 },
                { name: 'FinDocument_Sum', type: 'money', align: 'right', header: lang['k_oplate_rub'], width: 140 },
                { name: 'FinDocumentSpec_HtmlSum', type: 'string', align: 'right', header: lang['summa_oplatyi_rub'], width: 140 },
                { name: 'FinDocument_id', hidden: true, isparams: true },
                { name: 'DrugFinance_id', hidden: true, isparams: true },
                { name: 'WhsDocumentCostItemType_id', hidden: true, isparams: true },
                { name: 'SupplierContragent_id', hidden: true, isparams: true },
                { name: 'Org_id', hidden: true, isparams: true }
			],
			title: null,
			toolbar: true,
			onRowSelect: function(sm,rowIdx,record) {
				if (record.get('RegistryLLO_id') > 0) {
					this.ViewActions.action_edit.setDisabled(false);
					this.ViewActions.action_view.setDisabled(false);
					this.ViewActions.action_delete.setDisabled(false);
				} else {
					this.ViewActions.action_edit.setDisabled(true);
					this.ViewActions.action_view.setDisabled(true);
					this.ViewActions.action_delete.setDisabled(true);
				}
			},
			onLoadData: function() {
				this.DataState = 'loaded';
			}
		});

		this.SearchGridTab = new Ext.TabPanel({
			resizeTabs:true,
			region: 'center',
			id: 'rlvSearchGridTab',
			plain: true,
			activeTab: 0,
			minTabWidth: 140,
			autoScroll: true,
			layoutOnTabChange: true,
			listeners: {
				tabchange:function (tab, panel) {
					var grid = wnd.getActiveSearchGrid();
					if (grid.DataState == 'empty' && wnd.SearchGridTab.enableSearch) {
						wnd.doSearch();
					}
					if (grid.DataState == 'outdated') {
						grid.refreshRecords(null,0);
					}
				}
			},
			items: [{
				id: 'rlv_tab_new',
				title: lang['novyie'],
				layout:'border',
				border: false,
				items: [this.SearchNewGrid]
			}, {
				id: 'rlv_tab_formed',
				title: lang['sformirovannyie'],
				layout:'border',
				border: false,
				items: [this.SearchFormedGrid]
			}, {
				id: 'rlv_tab_in_work',
				title: lang['v_rabote'],
				layout:'border',
				border: false,
				items: [this.SearchInWorkGrid]
			}, {
				id: 'rlv_tab_for_pay',
				title: lang['k_oplate'],
				layout:'border',
				border: false,
				items: [this.SearchForPayGrid]
			}, {
				id: 'rlv_tab_paid',
				title: lang['oplachennyie'],
				layout:'border',
				border: false,
				items: [this.SearchPaidGrid]
			}]
		});

		Ext.apply(this, {
			layout: 'border',
			tbar: this.YearToolbar,
			buttons:
			[{
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
			items:[
				wnd.FilterPanel,
				wnd.SearchGridTab
			]
		});
		sw.Promed.swRegistryLLOViewWindow.superclass.initComponent.apply(this, arguments);
	}	
});