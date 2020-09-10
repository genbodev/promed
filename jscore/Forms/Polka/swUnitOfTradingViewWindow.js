/**
* Форма «Лоты на поставку медикаментов»
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      All
* @access       public
* @autor		Dmitry Storozhev aka nekto_O
* @copyright    Copyright (c) 2011 Swan Ltd.
* @version      23.11.2012
*/

sw.Promed.swUnitOfTradingViewWindow = Ext.extend(sw.Promed.BaseForm,
{
	title: lang['lotyi_na_postavku_medikamentov'],
	maximized: true,
	//shim: false,
	plain: true,
	autoScroll: true,
	id: 'swUnitOfTradingViewWindow',
	
	listeners: {
		resize: function() {
			if( this.layout.layout ) this.doLayout();
		}
	},
    isDirector : function(){
        return isUserGroup('director');
    }, 	
	show: function() {
		sw.Promed.swUnitOfTradingViewWindow.superclass.show.apply(this, arguments);

        this.FilterPanel.getForm().reset();

        this.ARMType = null;
        this.actionsCancel = false;
        this.enableDelete = false;
        this.enableCopy = false;
        this.enableForm = false;
        this.enableReform = false;
        this.enableMerge = false;
        this.enableAddDrug = false;
        this.enableDelDrug = false;
        this.enableDnD = false;

        if (arguments[0]) {
            if (!Ext.isEmpty(arguments[0].ARMType)) {
                this.ARMType = arguments[0].ARMType;
            }
            if (arguments[0].actionsCancel) {
                this.actionsCancel = arguments[0].actionsCancel;
            }
        }

        if(arguments[0] && arguments[0].enableDelete)  {
            this.enableDelete = true;
        }
        if(arguments[0] && arguments[0].enableCopy)  {
            this.enableCopy = true;
        }
        if(arguments[0] && arguments[0].enableForm)  {
            this.enableForm = true;
        }
        if(arguments[0] && arguments[0].enableReform)  {
            this.enableReform = true;
        }
        if(arguments[0] && arguments[0].enableMerge)  {
            this.enableMerge = true;
        }
        if(arguments[0] && arguments[0].enableAddDrug)  {
            this.enableAddDrug = true;
        }
        if(arguments[0] && arguments[0].enableDelDrug)  {
            this.enableDelDrug = true;
        }
        if(arguments[0] && arguments[0].enableDnD)  {
            this.enableDnD = true;
        }
        if(this.ARMType == 'zakup' && this.isDirector()) {
            this.enableForm = true;
            this.enableReform = true;
            this.enableMerge = true;
        }
        if(this.ARMType == 'zakup' && (this.isDirector() || isUserGroup('LpuUser'))) {
            this.enableAddDrug = true;
            this.enableDelDrug = true;
            this.enableDnD = true;
        }

        Ext.getCmp('swUnitOfTradingEditWindow_CenterGrid').getGrid().getStore().proxy.conn.url = '/?c=UnitOfTrading&m=loadUnitOfTradingList' ;

        var selprintAction = {
            name: 'select_print_act',
            text: 'Выбор печатных форм',
            handler: function() {
                var record = this.GridPanel.getGrid().getSelectionModel().getSelected();
                if(record) {
                    getWnd('swUnitOfTradingPrintFormsSelectWindow').show({
                        WhsDocumentUc_id: record.get('WhsDocumentUc_id'),
                        DrugRequest_id: this.FilterPanel.getForm().findField('DrugRequest_id').getValue()
                    });
                } else {
                    sw.swMsg.alert('Ошибка', 'Не выбран лот!');
                }
            }.createDelegate(this)
        };

        this.GridPanel.addActions(selprintAction);

        var docsAction = {
            name: 'docsdata',
            text: 'Данные для документации',
            handler: function() {
                var record = this.GridPanel.getGrid().getSelectionModel().getSelected();
                var owner = this.GridPanel;
                if(record) {
                    getWnd('swUnitOfTradingDocsDataEditWindow').show({
                        WhsDocumentUc_id:record.get('WhsDocumentUc_id'),
                        WhsDocumentProcurementRequestSpecDop_id:record.get('WhsDocumentProcurementRequestSpecDop_id'),
                        Okved_id:record.get('Okved_id'),
                        Okpd_id:record.get('Okpd_id'),
                        WhsDocumentProcurementRequestSpecDop_CodeKOSGU:record.get('WhsDocumentProcurementRequestSpecDop_CodeKOSGU'),
                        WhsDocumentProcurementRequestSpecDop_Count:record.get('WhsDocumentProcurementRequestSpecDop_Count'),
                        SupplyPlaceType_id:record.get('SupplyPlaceType_id'),
                        ProvSizeType_id:record.get('ProvSizeType_id'),
                        action:(Ext.isEmpty(record.get('WhsDocumentProcurementRequestSpecDop_id')) ? 'add' : 'edit'),
                        callback: function(data) {
                            var vals = data.getValues();
                            for (var prop in vals) {
                                record.set(prop,vals[prop]);
                            }
                        }
                    });
                } else {
                    sw.swMsg.alert('Ошибка', 'Не выбран лот!');
                }
            }.createDelegate(this)
        };

        this.GridPanel.addActions(docsAction);

        var copyAction = {
            name: 'copy_uot',
            text: 'Копировать',
            handler: function() {
                var record = this.GridPanel.getGrid().getSelectionModel().getSelected();
                var owner = this.GridPanel;
                if(record) {
                    var params = record.data;
                    params.action = 'add';
                    params.copy = true;
                    params.copyWhsDocumentUc_id = record.data.WhsDocumentUc_id;
                    params.WhsDocumentUc_id = '';
                    params.WhsDocumentProcurementRequestSpecDop_id = '';
                    params.WhsDocumentStatusType_id = 1;
                    params.DrugRequest_id = this.FilterPanel.getForm().findField('DrugRequest_id').getValue(),
                    params.onCopy = function (uc_id) {
                        var WhsDocumentUc_id = uc_id;
                        Ext.Ajax.request({
                            url: '/?c=UnitOfTrading&m=copyUnitOfTradingDocsData',
                            success: function(response){
                                var response_obj = Ext.util.JSON.decode(response.responseText);
                                if ( response_obj ) {
                                } else {
                                }
                            },
                            params: {
                                WhsDocumentUc_id: WhsDocumentUc_id
                            }
                        });
                    };
                    getWnd('swUnitOfTradingEditWindow').show(params);
                } else {
                    sw.swMsg.alert('Ошибка', 'Не выбран лот!');
                }
            }.createDelegate(this)
        };

        this.GridPanel.addActions(copyAction);

		if(!this.GridPanel.getAction('uotv_actions')) {
            
            if (this.actionsCancel) {
                var actions = {
                    name: 'uotv_actions',
                    iconCls: 'actions16',
                    text: lang['deystviya'],
                    menu: [{
                        name: 'export',
                        scope: this,
                        handler: this.exportUot,
                        text: lang['eksport']
                    }/*, {
                        name: 'request_purchase',
                        handler: function() {
                            var record = this.GridPanel.getGrid().getSelectionModel().getSelected();
                            if(record) {
                                if (record.get('isSigned') == 'true') {
                                    var WhsDocumentUc_id = record.get('WhsDocumentUc_id');
									printBirt({
										'Report_FileName': 'dlo_ReguestPurchase.rptdesign',
										'Report_Params': '&paramWhsDocumentProcurementRequest='+WhsDocumentUc_id,
										'Report_Format': 'doc'
									});
                                } else {
                                    sw.swMsg.alert(lang['oshibka'], lang['dostupno_tolko_dlya_podpisannyih_lotov']);
                                }
                            } else {
                                sw.swMsg.alert(lang['oshibka'], lang['ne_vyibran_lot']);
                            }
                        }.createDelegate(this),
                        text: lang['zayavka_na_razmeschenie_zakupki']
                    }, {
                        name: 'marker_research',
                        handler: function() {
                            var record = this.GridPanel.getGrid().getSelectionModel().getSelected();
                            if(record) {
                                if (record.get('isSigned') == 'true') {
                                    var WhsDocumentUc_id = record.get('WhsDocumentUc_id');
									printBirt({
										'Report_FileName': 'dlo_MarketResearch.rptdesign',
										'Report_Params': '&paramWhsDocumentProcurementRequest='+WhsDocumentUc_id,
										'Report_Format': 'xls'
									});
                                } else {
                                    sw.swMsg.alert(lang['oshibka'], lang['dostupno_tolko_dlya_podpisannyih_lotov']);
                                }
                            } else {
                                sw.swMsg.alert(lang['oshibka'], lang['ne_vyibran_lot']);
                            }
                        }.createDelegate(this),
                        text: lang['marketingovoe_issledovanie']
                    }*/]
                };
            } else { 
                var actions = {
                    name: 'uotv_actions',
                    iconCls: 'actions16',
                    text: lang['deystviya'],
                    menu: [{
                        name: 'form',
                        scope: this,
                        handler: this.showFormationUotWindow,
                        hidden: (this.ARMType == 'zakup' && !this.isDirector()),
                        disabled: (!this.enableForm),
                        text: lang['sformirovat']
                    }, {
                        name: 'reform',
                        scope: this,
                        handler: this.showFormationUotWindow,
                        hidden: (this.ARMType == 'zakup' && !this.isDirector()),
                        disabled: (!this.enableReform),
                        text: lang['pereformirovat']
                    }, 
                    /*{
                        name: 'copy',
                        hidden: false,                        
                        scope: this,
                        handler: function(){
                            var record = this.GridPanel.getGrid().getSelectionModel().getSelected();
                            if(record) {
                                var params = record.data;
                                params.action = 'add';
                                params.copy = true;
                                params.copyWhsDocumentUc_id = record.data.WhsDocumentUc_id;
                                params.WhsDocumentUc_id = '';
                                params.WhsDocumentProcurementRequestSpecDop_id = '';
                                params.WhsDocumentStatusType_id = 1;
                                params.DrugRequest_id = this.FilterPanel.getForm().findField('DrugRequest_id').getValue(),
                                params.onCopy = function (uc_id) {
                                    var WhsDocumentUc_id = uc_id;
                                    Ext.Ajax.request({
                                        url: '/?c=UnitOfTrading&m=copyUnitOfTradingDocsData',
                                        success: function(response){
                                            var response_obj = Ext.util.JSON.decode(response.responseText);
                                            if ( response_obj ) {
                                            } else {
                                            }
                                        },
                                        params: {
                                            WhsDocumentUc_id: WhsDocumentUc_id
                                        }
                                    });
                                };
                                getWnd('swUnitOfTradingEditWindow').show(params);
                            } else {
                                sw.swMsg.alert(lang['oshibka'], lang['ne_vyibran_lot']);
                            }*/
                            /*var DrugRequest_id =  Ext.getCmp('swUnitOfTradingViewWindow').FilterPanel.getForm().findField('DrugRequest_id').getValue();
                            var gridLots = Ext.getCmp('swUnitOfTradingViewWindow').GridPanel.getGrid().getSelectionModel().getSelected();
                            var DrugFinance_id = gridLots.get('DrugFinance_id');
                            var WhsDocumentCostItemType_id = gridLots.get('WhsDocumentCostItemType_id');
                            
                            var gridLots = Ext.getCmp('swUnitOfTradingEditWindow_CenterGrid').getGrid().getStore();
                            
                            //Если есть уже сформированные лоты - то отбой
                            if(gridLots.reader.jsonData.totalCount > 0){
                                sw.swMsg.alert(lang['oshibka'], lang['kopirovanie_lotov_nevozmojno_na_tekuschey_period_uje_est_sformirovannyie_lotyi']); 
                                return;   
                            }
                            
                    		Ext.Ajax.request({
                    			scope: this,
                    			url: '/?c=UnitOfTrading&m=CopyLots',
                    			params: {
                    				DrugRequest_id : DrugRequest_id,
                                    DrugFinance_id : DrugFinance_id,
                                    WhsDocumentCostItemType_id : WhsDocumentCostItemType_id
                    			},                                
                    			callback: function(o, s, r) {
                    			    Ext.getCmp('swUnitOfTradingEditWindow_CenterGrid').getGrid().getStore().load({DrugRequest_id: DrugRequest_id});*/                               
                    			  /*
                    				this.getLoadMask().hide();
                    				if( s ) {
                    					var obj = Ext.util.JSON.decode(r.responseText);
                                        
                                        console.log('obj.actions', obj.actions);
                                        
                    					if(obj.actions) {
                    						var actions = this.GridPanel.getAction('uotv_actions').items[0].menu.items;
                    						var menu_actions = this.GridPanel.ViewContextMenu.items.get(11).menu.items;
                    						actions.each(function(a) {
                    							a.setVisible(a.name.inlist(obj.actions));
                    						});
                    						menu_actions.each(function(a) {
                    							a.setVisible(a.name.inlist(obj.actions));
                    						});
                    					}
                    				}
                                   */ 
                    			/*}
                    		});*/                            
                        /*}.createDelegate(this),
                        text: lang['skopirovat']
                    },*/                    
                    {
                        name: 'merge',
                        scope: this,
                        handler: this.mergeUots,
                        hidden: (this.ARMType == 'zakup' && !this.isDirector()),
                        disabled: (!this.enableMerge),
                        text: lang['obyedinit_lotyi']
                    }, {
                        name: 'sign',
                        handler: this.setSignUnitOfTrading.createDelegate(this, ['current', true]),
                        text: lang['podpisat']
                    }, {
                        name: 'sign_all',
                        handler: this.setSignUnitOfTrading.createDelegate(this, ['all', true]),
                        text: lang['podpisat_vse']
                    }, {
                        name: 'unsign',
                        handler: this.setSignUnitOfTrading.createDelegate(this, ['current', false]),
                        text: lang['otmena_podpisaniya']
                    }, {
                        name: 'export',
                        scope: this,
                        handler: this.exportUot,
                        text: lang['eksport']
                    }/*, {
                        name: 'request_purchase',
                        handler: function() {
                            var record = this.GridPanel.getGrid().getSelectionModel().getSelected();
                            if(record) {
                                if (record.get('isSigned') == 'true') {
                                    var WhsDocumentUc_id = record.get('WhsDocumentUc_id');
									printBirt({
										'Report_FileName': 'dlo_ReguestPurchase.rptdesign',
										'Report_Params': '&paramWhsDocumentProcurementRequest='+WhsDocumentUc_id,
										'Report_Format': 'doc'
									});
                                } else {
                                    sw.swMsg.alert(lang['oshibka'], lang['dostupno_tolko_dlya_podpisannyih_lotov']);
                                }
                            } else {
                                sw.swMsg.alert(lang['oshibka'], lang['ne_vyibran_lot']);
                            }
                        }.createDelegate(this),
                        text: lang['zayavka_na_razmeschenie_zakupki']
                    }, {
                        name: 'marker_research',
                        handler: function() {
                            var record = this.GridPanel.getGrid().getSelectionModel().getSelected();
                            if(record) {
                                if (record.get('isSigned') == 'true') {
                                    var WhsDocumentUc_id = record.get('WhsDocumentUc_id');
									printBirt({
										'Report_FileName': 'dlo_MarketResearch.rptdesign',
										'Report_Params': '&paramWhsDocumentProcurementRequest='+WhsDocumentUc_id,
										'Report_Format': 'xls'
									});
                                } else {
                                    sw.swMsg.alert(lang['oshibka'], lang['dostupno_tolko_dlya_podpisannyih_lotov']);
                                }
                            } else {
                                sw.swMsg.alert(lang['oshibka'], lang['ne_vyibran_lot']);
                            }
                        }.createDelegate(this),
                        text: lang['marketingovoe_issledovanie']
                    }*/]
                };
            }
			
			this.GridPanel.addActions(actions);
		}

        var execAction = {
            name: 'pricecount',
            text: 'Выполнить расчет цены',
            handler: function() {
                var record = this.GridPanel2.getGrid().getSelectionModel().getSelected();
                var owner = this.GridPanel2;
                if(record) {
                    getWnd('swWhsDocumentProcurementPriceEditWindow').show({
                        WhsDocumentProcurementRequestSpec_id:record.get('WhsDocumentProcurementRequestSpec_id'),
                        params:{
                            Drug_Name: record.get('Drug_Name') + (!Ext.isEmpty(record.get('Tradenames_Name')) ? ' / '+record.get('Tradenames_Name') : ''),
                            InJnvlp: record.get('InJnvlp')
                        },
                        callback: function(data) {
                            owner.ViewActions.action_refresh.execute();
                        }
                    });
                } else {
                    sw.swMsg.alert('Ошибка', 'Не выбрана строка лота!');
                }
            }.createDelegate(this)
        };

        this.GridPanel2.addActions(execAction);
		
		this.getCurrentDateTime();
		this.onChangeDates('day');
		this.defineActionsVisible();

        if(!arguments[0] || !arguments[0].disableAdd || arguments[0].disableAdd != true)  {
            this.GridPanel.getAction('action_add').setDisabled(false);
        }

        if(!arguments[0] || !arguments[0].disableEdit || arguments[0].disableEdit != true)  {
            this.GridPanel.getAction('action_edit').setDisabled(false);
        }

        //Настройка гридов в зависимости от АРМ-а
        this.GridPanel.setActionHidden('action_add', false);
        this.GridPanel.setActionHidden('action_delete', false);
        this.GridPanel.setActionHidden('uotv_actions', false);
        this.GridPanel.setActionHidden('copy_uot', false);

        if (this.ARMType == 'zakup') { //АРМ специалиста по закупкам
            this.GridPanel.setActionHidden('action_add', !this.isDirector() );
            this.GridPanel.setActionHidden('action_delete', !this.isDirector() );
            this.GridPanel.setActionHidden('copy_uot', !this.isDirector() );
            this.GridPanel.getAction('action_add').setDisabled(!this.isDirector());
            this.enableDelete = this.isDirector();
            this.enableCopy = this.isDirector();
        }
        this.GridPanel.getAction('action_delete').setDisabled(!this.enableDelete);
        this.GridPanel.getAction('copy_uot').setDisabled(!this.enableCopy);
    },
    //Копирование лотов из пред. рабочего периода
	showCopyUotWindow: function(e) {
		var wnd = this;
		e.parentMenu.hide();
        
		//получение информации о том есть ли подписанные лоты
		var isSigned = false;
		this.GridPanel.ViewGridPanel.getStore().each(function(r) {
			if(r.get('isSigned') == 'true') {
				isSigned = true;
				return false;
			}
		});
		//if(isSigned) {
		//	sw.swMsg.alert('Ошибка', 'Переформирование невозможно. Снимите подпись со всех лотов в сводной заявке.');
		//	return false;
		//}
        //else{
            console.log('swbaselocalcombo', Ext.getCmp('swbaselocalcombo').getValue());
        //}
    },
	showFormationUotWindow: function(e) {
		var wnd = this;
		e.parentMenu.hide();

		//получение информации о том есть ли подписанные лоты
		var isSigned = false;
		this.GridPanel.ViewGridPanel.getStore().each(function(r) {
			if(r.get('isSigned') == 'true') {
				isSigned = true;
				return false;
			}
		});
		if(isSigned) {
			sw.swMsg.alert(lang['oshibka'], lang['pereformirovanie_nevozmojno_snimite_podpis_so_vseh_lotov_v_svodnoy_zayavke']);
			return false;
		}
        var frm = this.FilterPanel.getForm();
        if( Ext.isEmpty(frm.findField('DrugRequest_id').getValue()) ) {
            sw.swMsg.alert(lang['oshibka'], lang['ne_vyibrana_svodnaya_zayavka']);
            return;
        }

        if (e.name == 'reform'){
            Ext.Msg.show({
                title: lang['vnimanie'],
                scope: this,
                msg: lang['vse_ranee_sformirovannyie_lotyi_v_t_ch_podpisannyie_budut_udalenyi_i_sformirovanyi_zanovo_prodoljit'],
                buttons: Ext.Msg.YESNO,
                fn: function(btn) {
                    if (btn === 'yes') {
                       getWnd('swUnitOfTradingPropertiesEditWindow').show({
                            DrugFinance_id: this.FilterPanel.getForm().findField('DrugFinance_id').getValue(),
                            doSave: function(data) {
                                if (data && data.PersonRegisterType_id) {
                                    wnd.reformationUot(data);
                                }
                            }
                        }); 
                    } else {
                        return false;
                    }
                },
                icon: Ext.MessageBox.WARNING
            });
        } else {
            getWnd('swUnitOfTradingPropertiesEditWindow').show({
                DrugFinance_id: this.FilterPanel.getForm().findField('DrugFinance_id').getValue(),
                doSave: function(data) {
                    if (data && data.PersonRegisterType_id) {
                        wnd.formationUot(data);
                    }
                }
            });
        }
	},

	defineActionsVisible: function() {
        var wnd = this;
        wnd.visibleActions = [];
		this.getLoadMask(lang['opredelenie_nastroek']).show();
		Ext.Ajax.request({
			scope: this,
			url: '/?c=UnitOfTrading&m=defineActionsVisible',
			callback: function(o, s, r) {
				this.getLoadMask().hide();
				if( s ) {
					var obj = Ext.util.JSON.decode(r.responseText);
                    
                    console.log('obj.actions', obj.actions);
                    
					if(obj.actions) {
						var actions = this.GridPanel.getAction('uotv_actions').items[0].menu.items;
						//var menu_actions = this.GridPanel.ViewContextMenu.items.get(11).menu.items;
						actions.each(function(a) {
							a.setVisible(a.name.inlist(obj.actions));
						});
                        wnd.visibleActions = obj.actions;
						/*menu_actions.each(function(a) {
							a.setVisible(a.name.inlist(obj.actions));
						});*/
					}
				}
			}
		});
	},
	
	moveDrugsInOtherUot: function(drugs, uot_id, cb) {
		var ds = [];
		Ext.each(drugs, function(d) {
			ds.push(d.get('WhsDocumentProcurementRequestSpec_id'));
		});
		if( ds.length == 0 ) return;
		this.getLoadMask(lang['peremeschenie_medikamentov']).show();
		Ext.Ajax.request({
			scope: this,
			params: {
				DrugList: escape(ds.join('|')),
				WhsDocumentUc_id: uot_id
			},
			url: '/?c=UnitOfTrading&m=moveDrugsInOtherUnitOfTrading',
			callback: function(o, s, r) {
				this.getLoadMask().hide();
				if(s) {	if(cb) cb(); }
			}
		});
	},
	
	getSelectedUot: function() {
		return this.GridPanel.getGrid().getSelectionModel().getSelected();
	},
	
	// делает переданный элемент областью дропа
	createDDTarget: function(el, record) {
		var win = this;
		new Ext.dd.DropTarget(el, {
			ddGroup: win.id + '_gridDDGroup',
			isAccess: function(selections) {
				var flag = true;
				Ext.each(selections, function(r) {
					if( record.get('WhsDocumentUc_id') == r.get('WhsDocumentUc_id') || record.get('isSigned') == 'true' ) {
						flag = false;
					}
				});
                if(!win.enableDnD){
                    flag = false;
                }
				return flag;
			},
			notifyOver: function(ddSource, e, data) {
				return this.isAccess(data.selections) ? this.dropAllowed : this.dropNotAllowed;
			},
			notifyDrop: function(ddSource, e, data) {
				var isDrop = this.isAccess(data.selections);
				if( isDrop ) {
					if( data.grid.getStore().getCount() == data.selections.length ) {
						Ext.Msg.show({
							title: lang['vnimanie'],
							msg: 'Вы действительно хотите переместить все медикаменты в другой лот?<br /><b>Лот "'+win.getSelectedUot().get('WhsDocumentUc_Name')+'" будет удален!</b>',
							buttons: Ext.Msg.YESNO,
							fn: function(btn) {
								if (btn === 'yes') {
									win.moveDrugsInOtherUot(data.selections, record.get('WhsDocumentUc_id'), function() {
										Ext.each(data.selections, function(r) { data.grid.getStore().remove(r) });
										win.GridPanel.loadData({globalFilters: {
											start: 0
										}, callback: function() {
                                            win.GridPanel.getGrid().getStore().sort('WhsDocumentUc_Name');
											with( win.GridPanel.getGrid() ) {
												getSelectionModel().selectRow(getStore().findBy(function(rec) { return rec.get('WhsDocumentUc_id') == record.get('WhsDocumentUc_id'); }), false);
											}
										}});
									});
								}
							},
							icon: Ext.MessageBox.WARNING
						});
					} else {
						win.moveDrugsInOtherUot(data.selections, record.get('WhsDocumentUc_id'), function() {
							Ext.each(data.selections, function(r) { data.grid.getStore().remove(r) });
							with( win.GridPanel.getGrid() ) {
								getSelectionModel().selectRow(getStore().findBy(function(rec) { return rec.get('WhsDocumentUc_id') == record.get('WhsDocumentUc_id'); }), false);
							}						
						});
					}
				} else {	
					if( data.selections[0].get('WhsDocumentUc_id') == record.get('WhsDocumentUc_id') ) {
						sw.swMsg.alert(lang['oshibka'], lang['vyibrannyie_medikamentyi_uje_vklyuchenyi_v_etot_lot']);
						return false;
					}
					if( record.get('isSigned') == 'true' ) {
						sw.swMsg.alert(lang['oshibka'], lang['nelzya_peremeschat_medikamentyi_v_podpisannyiy_lot']);
					}
				}
				return isDrop;
			}
		});
	},
	
	mergeUots: function(e) {
		var records = this.GridPanel.getGrid().getSelectionModel().getSelections();
		e.parentMenu.hide();
		if( records.length < 2 ) {
			sw.swMsg.alert(lang['oshibka'], lang['neobhodimo_vyibrat_ne_menee_2-h_lotov_dlya_obyedineniya']);
			return false;
		}
		var issetSigned = false,
			UotList = [];
		Ext.each(records, function(r) {
			if( r.get('isSigned') == 'true' ) issetSigned = true;
			UotList.push(r.get('WhsDocumentUc_id'));
		});
		
		if( issetSigned ) {
			sw.swMsg.alert(lang['oshibka'], lang['podpisannyie_lotyi_ne_mogut_uchastvovat_v_protsedure_obyedineniya']);
			return false;
		}
		this.getLoadMask(lang['obyedinenie_lotov']).show();
		Ext.Ajax.request({
			url: '/?c=UnitOfTrading&m=mergeUnitOfTradings',
			scope: this,
			params: { UotList: escape(UotList.join('|')) },
			callback: function(o, s, r) {
				this.getLoadMask().hide();
				if( s ) {
					with( this.GridPanel.getGrid() ) {
						var newIdx = getStore().findBy(function(rec) { return rec.get('WhsDocumentUc_id') == records[0].get('WhsDocumentUc_id'); });
						getSelectionModel().selectRow(newIdx, false);
						this.GridPanel.ViewActions.action_edit.execute();
					}
				}
			}
		});
	},
	
	formationUot: function(data) {
		var frm = this.FilterPanel.getForm();
		if( Ext.isEmpty(frm.findField('DrugRequest_id').getValue()) ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_vyibrana_svodnaya_zayavka']);
			return;
		}
		if ((typeof data == "object") && data.PersonRegisterType_id > 0) {
            data.DrugRequest_id = frm.findField('DrugRequest_id').getValue();
        }
		this.getLoadMask(lang['formirovanie_lotov']).show();
		Ext.Ajax.request({
			url: '/?c=UnitOfTrading&m=formationUnitOfTrading',
			params: data,
			scope: this,
			callback: function(o, s, r) {
				this.getLoadMask().hide();
				if( s ) {
					var obj = Ext.util.JSON.decode(r.responseText);
					if( obj.success ) {
						this.GridPanel.ViewActions.action_refresh.execute();
                        this.GridPanel.getGrid().getStore().sort('WhsDocumentUc_Name');
					}
				}
			}
		});
	},
	
	reformationUot: function(data) {
		var frm = this.FilterPanel.getForm();
		if( Ext.isEmpty(frm.findField('DrugRequest_id').getValue()) ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_vyibrana_svodnaya_zayavka']);
			return;
		}
		
		var recordsContracts = [];
		this.GridPanel.ViewGridPanel.getStore().each(function(r) {
			if( !Ext.isEmpty(r.get('WhsDocumentUc_pid')) ) {
				recordsContracts.push(r);
			}
		});
		if( recordsContracts.length > 0 ) {
			var msg = lang['suschestvuyut_dogovora_postavki_svyazannyie_so_sleduyuschimi_lotami'];
			Ext.each(recordsContracts, function(r, i) {
				msg += ++i + '. ' + r.get('WhsDocumentUc_Name') + '<br />';
			});
			sw.swMsg.alert(lang['pereformirovanie_nevozmojno'], msg);
			return false;
		}
        if ((typeof data == "object") && data.PersonRegisterType_id > 0) {
            data.DrugRequest_id = frm.findField('DrugRequest_id').getValue();
        }
		
		this.getLoadMask(lang['pereformirovanie_lotov']).show();
		Ext.Ajax.request({
			url: '/?c=UnitOfTrading&m=reformationUnitOfTrading',
			params: data,
			scope: this,
			callback: function(o, s, r) {
				this.getLoadMask().hide();
				if( s ) {
					this.GridPanel.ViewActions.action_refresh.execute();
                    this.GridPanel.getGrid().getStore().sort('WhsDocumentUc_Name');
				}
			}
		});
	},
	
	exportUot: function() {
		var record = this.GridPanel.getGrid().getSelectionModel().getSelected();
		log(record);
		if( !record ) {
			return false;
		}
		this.getLoadMask(lang['vyipolnyaetsya_eksport_lota']).show();
		Ext.Ajax.request({
			scope: this,
			url: '/?c=UnitOfTrading&m=exportUnitOfTrading',
			params: { WhsDocumentProcurementRequest_id: record.get('WhsDocumentUc_id') },
			callback: function(o, s, r) {
				this.getLoadMask().hide();
				if( s ) {
					var obj = Ext.util.JSON.decode(r.responseText);
					if( obj.success ) {
						window.open(obj.url, '_blank');
					}
				}
			}
		});
	},
	
	onChangeDates: function(mode) {
		var params = Ext.apply(this.FilterPanel.getForm().getValues(), this.searchParams || {}),
			btn = this.getPeriodToggle(mode);
		if (btn) {
			if (mode != 'range') {
				if (this.mode == mode) {
					btn.toggle(true);
					if (mode != 'day') // чтобы при повторном открытии тоже происходила загрузка списка записанных на этот день
						return false;
				} else {
					this.mode = mode;
				}
			}
			else {
				btn.toggle(true);
				this.mode = mode;
			}
		}
		
		params.begDate = Ext.util.Format.date(this.dateMenu.getValue1(), 'd.m.Y');
		params.endDate = Ext.util.Format.date(this.dateMenu.getValue2(), 'd.m.Y');

                
		this.FilterPanel.getForm().findField('DrugRequest_id').getStore().load({params: params});
		        
		this.GridPanel.removeAll({clearAll:true});
	},
	
	doSearch: function() {
	
		var form = this.FilterPanel;
		var base_form = this.FilterPanel.getForm();
		
		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.formStatus = 'edit';
					form.getFirstInvalidEl().focus(false);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		
		var params = Ext.apply(base_form.getValues(), {
			start: 0,
			begDate: Ext.util.Format.date(this.dateMenu.getValue1(), 'd.m.Y'),
			endDate: Ext.util.Format.date(this.dateMenu.getValue2(), 'd.m.Y'),
            pmUser_did : getGlobalOptions().pmuser_id
		});
        
		this.GridPanel.loadData({globalFilters: params, callback:function(){this.sort('WhsDocumentUc_Name');}});        
                        
	},
	
	getPeriodToggle: function(mode) {
		switch(mode) {
			case 'day':
				return this.WindowToolbar.items.items[9];
				break;
			case 'week':
				return this.WindowToolbar.items.items[10];
				break;
			case 'month':
				return this.WindowToolbar.items.items[11];
				break;
			case 'range':
				return this.WindowToolbar.items.items[12];
				break;
			default:
				return null;
				break;
		}
	},
	
	getCurrentDateTime: function() {
		if (!getGlobalOptions().date) {
			frm.getLoadMask(LOAD_WAIT).show();
			Ext.Ajax.request({
				url: C_LOAD_CURTIME,
				callback: function(opt, success, response) {
					if (success && response.responseText != '') {
						var result  = Ext.util.JSON.decode(response.responseText);
						this.curDate = result.begDate;
						// Проставляем время и режим
						this.mode = 'day';
						this.currentDay();

						if ( this.gridPanelAutoLoad == true ) {
							this.onChangeDates('day');
						}

						this.getLoadMask().hide();
					}
				}.createDelegate(this)
			});
		} else {
			this.curDate = getGlobalOptions().date;
			// Проставляем время и режим
			this.mode = 'day';
			this.currentDay();

			if ( this.gridPanelAutoLoad == true ) {
				this.onChangeDates('day');
			}
		}
	},
	
	stepDay: function(day) {
		var frm = this;
		var date1 = (frm.dateMenu.getValue1() || Date.parseDate(frm.curDate, 'd.m.Y')).add(Date.DAY, day).clearTime();
		var date2 = (frm.dateMenu.getValue2() || Date.parseDate(frm.curDate, 'd.m.Y')).add(Date.DAY, day).clearTime();
		frm.dateMenu.setValue(Ext.util.Format.date(date1, 'd.m.Y')+' - '+Ext.util.Format.date(date2, 'd.m.Y'));
		// frm.dateMenu.fireEvent("select", frm.dateMenu);
	},
	
	prevDay: function() {
		this.stepDay(-1);
	},
	
	nextDay: function() {
		this.stepDay(1);
	},
	
	currentDay: function() {
		var frm = this;
		var date1 = Date.parseDate(frm.curDate, 'd.m.Y');
		var date2 = Date.parseDate(frm.curDate, 'd.m.Y');
		frm.dateMenu.setValue(Ext.util.Format.date(date1, 'd.m.Y')+' - '+Ext.util.Format.date(date2, 'd.m.Y'));
		// frm.dateMenu.fireEvent("select", frm.dateMenu);
	},
	
	currentWeek: function() {
		var frm = this;
		var date1 = (Date.parseDate(frm.curDate, 'd.m.Y'));
		var dayOfWeek = (date1.getDay() + 6) % 7;
		date1 = date1.add(Date.DAY, -dayOfWeek).clearTime();
		var date2 = date1.add(Date.DAY, 6).clearTime();
		frm.dateMenu.setValue(Ext.util.Format.date(date1, 'd.m.Y')+' - '+Ext.util.Format.date(date2, 'd.m.Y'));
		// frm.dateMenu.fireEvent("select", frm.dateMenu);
	},
	
	currentMonth: function() {
		var frm = this;
		var date1 = (Date.parseDate(frm.curDate, 'd.m.Y')).getFirstDateOfMonth();
		var date2 = date1.getLastDateOfMonth();
		frm.dateMenu.setValue(Ext.util.Format.date(date1, 'd.m.Y')+' - '+Ext.util.Format.date(date2, 'd.m.Y'));
		// frm.dateMenu.fireEvent("select", frm.dateMenu);
	},
	
	deleteUnitOfTrading: function() {
		var record = this.GridPanel.ViewGridPanel.getSelectionModel().getSelected();
		if( !record ) return false;
		Ext.Msg.show({
			title: lang['vnimanie'],
			scope: this,
			msg: lang['vyi_deystvitelno_hotite_udalit_lot'],
			buttons: Ext.Msg.YESNO,
			fn: function(btn) {
				if (btn === 'yes') {
					this.getLoadMask(lang['udalenie_lota']).show();
					Ext.Ajax.request({
						scope: this,
						params: { WhsDocumentProcurementRequest_id: record.get('WhsDocumentUc_id')},
						url: '/?c=UnitOfTrading&m=deleteUnitOfTrading',
						callback: function(options, success, response) {
							this.getLoadMask().hide();
							if(success) {
								this.GridPanel.ViewActions.action_refresh.execute();
                                this.GridPanel.getGrid().getStore().sort('WhsDocumentUc_Name');
							}
						}
					});
				}
			},
			icon: Ext.MessageBox.QUESTION
		});
	},
	
	deleteDrugListInUnitOfTrading: function() {
		var selRecords = this.GridPanel2.getGrid().getSelectionModel().getSelections();
		if( selRecords.length == 0 ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_vyibran_niodin_medikament']);
			return false;
		}
		var msg = lang['vyi_deystvitelno_hotite_udalit'];
		msg += selRecords.length > 1 ? lang['vyibrannyie_medikamentyi'] : lang['vyibrannyiy_medikament'];
		
		var params = { WhsDocumentUc_id: selRecords[0].get('WhsDocumentUc_id') };
		var del_uot = false;
		if(selRecords.length == this.GridPanel2.getGrid().getStore().getCount() ) {
			msg += lang['vnimanie_dlya_udalenie_vyibranyi_vse_medikamentyi_lota_lot_takje_budet_udalen'];
			params.del_uot = 1;
            del_uot = true;
		}
		
		var drugList = [],
			i = 0;
		for(; i<selRecords.length; i++) {
			drugList.push(selRecords[i].get('WhsDocumentProcurementRequestSpec_id'));
		}
		
		params.DrugList = escape(drugList.join('|'));
		
		Ext.Msg.show({
			title: lang['vnimanie'],
			scope: this,
			msg: msg,
			buttons: Ext.Msg.YESNO,
			fn: function(btn) {
				if (btn === 'yes') {
					this.getLoadMask('Удаление медикамент'+(selRecords.length > 1 ? 'ов' : 'а')+'...').show();
					Ext.Ajax.request({
						scope: this,
						params: params,
						url: '/?c=UnitOfTrading&m=deleteDrugListInUnitOfTrading',
						callback: function(options, success, response) {
							this.getLoadMask().hide();
							if(success) {
                                if(del_uot){
                                    this.GridPanel.loadData();
                                } else {
                                    this.GridPanel2.loadData();
                                }
							}
						}
					});
				}
			},
			icon: Ext.MessageBox.QUESTION
		});
		
	},

    addDrugInUnitOfTrading: function() {
        var win = this;
        var params = {
            DrugRequest_id: this.FilterPanel.getForm().findField('DrugRequest_id').getValue(),
            WhsDocumentUc_id: this.GridPanel.getGrid().getSelectionModel().getSelected().get('WhsDocumentUc_id'),
            callback: function(){
                win.GridPanel2.loadData();
            }
        };
        getWnd('swUnitOfTradingRowWindow').show(params);
    },
	
	/*printSpec: function(format) {
		var record = this.GridPanel.getGrid().getSelectionModel().getSelected();
		if( !record ) {
			return false;
		}
		this.getLoadMask(lang['pechat_spetsifikatsii_lota']).show();
		Ext.Ajax.request({
			scope: this,
			params: { format: format, WhsDocumentProcurementRequest_id: record.get('WhsDocumentUc_id') },
			url: '/?c=UnitOfTrading&m=printUnitOfTradingSpec',
			callback: function(o, s, r) {
				this.getLoadMask().hide();
				if( s ) {
					switch(format.toLowerCase()) {
						case 'html':
							openNewWindow(r.responseText);
							break;
						case 'csv':
							var obj = Ext.util.JSON.decode(r.responseText);
							if( obj.success ) {
								window.open(obj.url);
							}
							break;
					}
				}
			}
		});
	},*/

	setSignUnitOfTrading: function(mode, issign) { //проверки и подготовки перед подписанием
		if( !mode || !mode.inlist(['current', 'all']) ) {
			return false;
		}
		var wnd = this;
		var selRecords = [];
		switch(mode) {
			case 'current':
				var recs = this.GridPanel.getGrid().getSelectionModel().getSelections();
				if( recs.length == 0 ) return false;
				selRecords = recs;
				break;
			case 'all':
				this.GridPanel.getGrid().getStore().each(function(r) {
					selRecords.push(r);
				});
				if( selRecords.length == 0 ) return false;
				break;
		}
		var i = 0,
			rs = [];
		for(; i<selRecords.length; i++) {
			rs.push(selRecords[i].get('WhsDocumentUc_id'));
		}


		if (issign) {
			this.doSignUnitOfTrading(rs, issign);
		} else {
			var id_array = new Array();

			this.GridPanel.getGrid().getStore().each(function(r) {
				id_array.push(r.get('WhsDocumentUc_id'));
			});

			//проверяем лот на наличие дочерних контрактов
			Ext.Ajax.request({
				scope: this,
				url: '/?c=UnitOfTrading&m=getWhsDocumentSupplyByUotId',
				params: { UotList: escape(id_array.join('|')) },
				callback: function(o, s, r) {
					if(s) {
						var arr = Ext.util.JSON.decode(r.responseText);
						if (arr.length > 0) {
							var exsist = false;
							for(var i = 0; i < arr.length; i++) {
								for(var j = 0; j < rs.length; j++) {
									log('check arr('+i+'):'+arr[i].WhsDocumentProcurementRequest_id+' rs('+j+'):'+rs[j]);
									if (arr[i].WhsDocumentProcurementRequest_id == rs[j]) {
										exsist = true;
										break;
									}
								}
								if (exsist) break;
							}

							if (exsist) {
								sw.swMsg.alert(lang['oshibka'], lang['s_lota_nevozmojno_snyat_podpisanie_suschestvuet_docherniky_kontrakt']);
							} else {
								Ext.Msg.show({
									title: lang['vnimanie'],
									msg: lang['v_ramkah_dannoy_svodnoy_zayavki_suschestvuet_lot_s_dochernim_kontraktom_prodoljit_snyatie_podpisaniya'],
									buttons: Ext.Msg.YESNO,
									fn: function(btn) {
										if (btn === 'yes') {
											wnd.doSignUnitOfTrading(rs, issign);
										}
									},
									icon: Ext.MessageBox.WARNING
								});
							}
						} else {
							wnd.doSignUnitOfTrading(rs, issign);
						}
					}
				}
			});
		}
	},

	doSignUnitOfTrading: function(id_array, issign) { //непосредственное подписание
		this.getLoadMask(lang['podpisanie_lotov']).show();
		Ext.Ajax.request({
			scope: this,
			url: '/?c=UnitOfTrading&m=' + (issign ? '' : 'un') + 'signUnitOfTrading',
			params: { UotList: escape(id_array.join('|')) },
			callback: function(o, s, r) {
				this.getLoadMask().hide();
				if( s ) {
					this.GridPanel.ViewActions.action_refresh.execute();
				}
			}
		});
	},
	
	issetUnSignedUot: function(store) {
		var f = false;
		store.each(function(r) {
			if(r.get('isSigned') == 'false') {
				f = true;
			}
		});
		return f;
	},
	
	setDisabledAction: function(grid, action, isDisable) {
		var actions = grid.getAction('uotv_actions').items[0].menu.items,
			idx = actions.findIndexBy(function(a) { return a.name == action; });
		if( idx == -1 ) {
			return;
		}
		actions.items[idx].setDisabled(isDisable);
		grid.getAction('uotv_actions').items[1].menu.items.items[idx].setDisabled(isDisable);
	},

	initComponent: function() {
        var wnd = this;

		this.timeMenu = new Ext.form.TimeField ({
			//disabled: true,
			fieldLabel: lang['vremya_do_kontsa_ojidaniya'],
			name: 'PPD_WaitingTime',
			id: 'PPD_WaitingTime',
			format: 'H:i',
			plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
			validateOnBlur: false,
			width: 40,
			xtype: 'swtimefield',
			triggerAction: function () {
				alert('click');
			},
			triggerClass: 'hidden-class',
			hidden:true,
			listeners: {
				focus: function(){
					this.disable();
					var parent_object = this;
					var SetWaitingPPDTimeWindow = new Ext.Window({
						width:400,
						heigth:300,
						title:lang['vvedite_novoe_vremya_ojidaniya'],
						modal: true,
						draggable:false,
						resizable:false,
						closable : false,
						items:[{
							xtype: 'form',
							bodyStyle: {padding: '10px'},
							disabledClass: 'field-disabled',
							items:
							[{																	
							//comboSubject: 'CmpReason',
								disabledClass: 'field-disabled',
								fieldLabel: lang['vremya_ojidaniya_prinyatiya_vyizova_v_ppd_min'],
								allowBlank: false,
								xtype: 'textfield',
								autoCreate: {tag: "input",  maxLength: "3", autocomplete: "off"},
								maskRe: /[0-9]/,
								id:'SetWaitingPPDTimeWindow_time',
								width:250
							},
							{
								disabledClass: 'field-disabled',
								fieldLabel: lang['vash_parol'],
								allowBlank: false,
								id: 'refuse_comment',
								// tabIndex: TABINDEX_PEF + 5,
								width: 250,
								inputType:'password',
								xtype: 'textfield',
								id:'SetWaitingPPDTimeWindow_pass'
							}]
						}],
						buttons:[{
							text:lang['ok'],
							handler:function(){
								var time = Ext.getCmp('SetWaitingPPDTimeWindow_time').getValue();
								var password = Ext.getCmp('SetWaitingPPDTimeWindow_pass').getValue();

								if ((!time)||(!password)) {
									sw.swMsg.show({
										buttons: Ext.Msg.OK,
										icon: Ext.Msg.WARNING,
										msg: lang['vse_polya_doljnyi_byit_zapolnenyi'],
										title: ERR_INVFIELDS_TIT
									});
									return false;
								}
								
								Ext.Ajax.request({
									params: {
										PPD_WaitingTime: time,
										Password: password
									},
									callback: function(options, success, response) {
										var response_obj = Ext.util.JSON.decode(response.responseText);
										if (success) {
											if ((!response_obj.success) ) {
												sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg);
											}
											else {
												SetWaitingPPDTimeWindow.close();
											}
										}
										else {
											sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_ustanovke_vremeni_ojidaniya_prinyatiya_vyizova']);
										}
									},
									url: '/?c=CmpCallCard&m=setPPDWaitingTime'
								});
								SetWaitingPPDTimeWindow.close();
							}
						},
						{
							text: lang['otmena'],
							handler: function(){
								SetWaitingPPDTimeWindow.close();
							}
						}]
					})
					SetWaitingPPDTimeWindow.show();
	
					this.enable();//TODO: Убрать этот комментарий потом
				}
			}
		});
		
		this.timeMenuLabel = new Ext.form.Label({
			disabled: false,
			text: lang['vremya_do_kontsa_ojidaniya'],
			width: 180,
			hidden:true
		});
		
		this.dateMenu = new Ext.form.DateRangeField({
			width: 150,
			fieldLabel: lang['period'],
			plugins: [
				new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)
			]
		});
		
		this.dateMenu.addListener('keydown',function (inp, e) {
			if (e.getKey() == Ext.EventObject.ENTER) {
				e.stopEvent();
				this.onChangeDates('period');
			}
		}.createDelegate(this));
		this.dateMenu.addListener('select',function () {
			// Читаем расписание за период
			this.onChangeDates('period');
		}.createDelegate(this));
		
		this.formActions = [];
		this.formActions.selectDate = new Ext.Action({
			text: ''
		});
		this.formActions.prev = new Ext.Action({
			text: lang['predyiduschiy'],
			xtype: 'button',
			iconCls: 'arrow-previous16',
			handler: function() {
				// на один день назад
				this.prevDay();
				this.onChangeDates('range');
			}.createDelegate(this)
		});
		this.formActions.next = new Ext.Action({
			text: lang['sleduyuschiy'],
			xtype: 'button',
			iconCls: 'arrow-next16',
			handler: function() {
				// на один день вперед
				this.nextDay();
				this.onChangeDates('range');
			}.createDelegate(this)
		});
		this.formActions.day = new Ext.Action({
			text: lang['den'],
			xtype: 'button',
			toggleGroup: 'periodToggle',
			iconCls: 'datepicker-day16',
			pressed: true,
			handler: function() {
				this.currentDay();
				this.onChangeDates('day');
			}.createDelegate(this)
		});
		this.formActions.week = new Ext.Action({
			text: lang['nedelya'],
			xtype: 'button',
			toggleGroup: 'periodToggle',
			iconCls: 'datepicker-week16',
			handler: function() {
				this.currentWeek();
				this.onChangeDates('week');
			}.createDelegate(this)
		});
		this.formActions.month = new Ext.Action({
			text: lang['mesyats'],
			xtype: 'button',
			toggleGroup: 'periodToggle',
			iconCls: 'datepicker-month16',
			handler: function() {
				this.currentMonth();
				this.onChangeDates('month');
			}.createDelegate(this)
		});
		this.formActions.range = new Ext.Action({
			text: lang['period'],
			disabled: true,
			xtype: 'button',
			toggleGroup: 'periodToggle',
			iconCls: 'datepicker-range16',
			handler: function() {
				this.onChangeDates('range');
			}.createDelegate(this)
		});
		
		this.WindowToolbar = new Ext.Toolbar({
			items: [
				this.formActions.prev, 
				{
					xtype : "tbseparator"
				},
				this.dateMenu,
				//this.dateText,
				{
					xtype : "tbseparator"
				},
				this.formActions.next, 
				{
					xtype: 'tbfill'
				},
				this.timeMenuLabel,
				this.timeMenu,
				{
					xtype : "tbseparator"
				},
				this.formActions.day, 
				this.formActions.week, 
				this.formActions.month,
				this.formActions.range
			]
		});
		
		this.FilterPanel = getBaseFiltersFrame({
			ownerWindow: this,
			toolBar: this.WindowToolbar,
			items: [{
				layout: 'column',
				border: false,
				defaults: { border: false },
				autoHeight: true,
				labelWidth: 200,
				items: [{
					layout: 'form',
					defaults: {
						width: 250
					},
					items: [{
						xtype: 'swbaselocalcombo',
                        id : 'swbaselocalcombo',
						triggerAction: 'all',
						hiddenName: 'DrugRequest_id',
						valueField: 'DrugRequest_id',
						displayField: 'DrugRequest_Name',
						store: new Ext.data.JsonStore({
							autoLoad: false,
							fields: [
								{name: 'DrugRequest_id', type: 'int'},
								{name: 'DrugRequest_Name', type: 'string'}
							],
							listeners: {
								load: function(s, rs, os) {
									var combo = this.FilterPanel.getForm().findField('DrugRequest_id');
									combo.reset();
									if( s.getCount() ) {
										combo.setValue(rs[0].get(combo.valueField));
										combo.fireEvent('select', combo, rs[0], s.indexOf(rs[0]));
									}
								}.createDelegate(this)
							},
							key: 'DrugRequest_id',
							sortInfo: { field: 'DrugRequest_id' },
							url: '/?c=UnitOfTrading&m=loadDrugRequest'
						}),
						listeners: {
							select: function(c) {
								if( !Ext.isEmpty(c.getValue()) ) {
									/*this.GridPanel.loadData({globalFilters: {
										start: 0,
										DrugRequest_id: c.getValue()
									}});*/
                                    
                                    //Лоты для конкретного сотрудника
                                    this.GridPanel.setParam('pmUser_did', getGlobalOptions().pmuser_id, 1);        
                                        
									this.GridPanel.loadData({globalFilters: Ext.apply({
										start: 0
									}, this.FilterPanel.getForm().getValues()), callback: function(){this.sort('WhsDocumentUc_Name')}});
									
									this.GridPanel.setParam(c.valueField, c.getValue(), 0);
									this.GridPanel2.setParam(c.valueField, c.getValue(), 0);
									this.GridPanel2.getGrid().getStore().baseParams[c.valueField] = c.getValue();
								} else {
									this.GridPanel.removeAll({clearAll:true});
								}
							}.createDelegate(this)
						},
						tpl: '<tpl for="."><div class="x-combo-list-item"><font color="red"></font>&nbsp;{DrugRequest_Name}</div></tpl>',
						allowBlank: false,
						fieldLabel: lang['svodnaya_zayavka_na_zakup']
					}, {
						xtype: 'swcommonsprcombo',
						listeners: {
							select: function(c) {
								if( Ext.isEmpty(c.getValue()) )
									return false;
								this.GridPanel.setParam(c.valueField, c.getValue(), 0);
							}.createDelegate(this)
						},
						comboSubject: 'DrugFinance',
						fieldLabel: lang['istochnik_finansirovaniya']
					}]
				}, {
					layout: 'form',
					labelWidth: 120,
					items: [{
						xtype: 'swcommonsprcombo',
						listeners: {
							select: function(c) {
								if( Ext.isEmpty(c.getValue()) )
									return false;
								this.GridPanel.setParam(c.valueField, c.getValue(), 0);
							}.createDelegate(this)
						},
						comboSubject: 'WhsDocumentCostItemType',
						fieldLabel: lang['statya_rashoda']
					}, {
                        xtype: 'swcommonsprcombo',
                        listeners: {
                            select: function(c) {
                                if( Ext.isEmpty(c.getValue()) )
                                    return false;
                                this.GridPanel.setParam(c.valueField, c.getValue(), 0);
                            }.createDelegate(this)
                        },
                        comboSubject: 'BudgetFormType',
                        fieldLabel: 'Целевая статья'
                    }]
				}, {
                    layout: 'form',
                    labelWidth: 120,
                    items: [{
                        xtype: 'swcommonsprcombo',
                        listeners: {
                            select: function(c) {
                                if( Ext.isEmpty(c.getValue()) )
                                    return false;
                                this.GridPanel.setParam(c.valueField, c.getValue(), 0);
                            }.createDelegate(this)
                        },
                        comboSubject: 'WhsDocumentPurchType',
                        fieldLabel: 'Тип закупа'
                    }, {
                        xtype: 'swcommonsprcombo',
                        listeners: {
                            select: function(c) {
                                if( Ext.isEmpty(c.getValue()) )
                                    return false;
                                this.GridPanel.setParam(c.valueField, c.getValue(), 0);
                            }.createDelegate(this)
                        },
                        comboSubject: 'FinanceSource',
                        fieldLabel: 'Источник оплаты'
                    }]
                }]
			}]
		});
		
		this.GridPanel = new sw.Promed.ViewFrame({
			title: lang['lotyi'],
			region: 'north',
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 100,
			id: 'swUnitOfTradingEditWindow_CenterGrid',
			pageSize: 50,
			paging: true,
			editformclassname: 'swUnitOfTradingEditWindow',
			autoScroll: true,
			selectionModel: 'multiselect',
			height: 250,
			listeners: {
				resize: function() {
					if( this.layout.layout ) this.doLayout();
				}
			},
			autoLoadData: false,
			root: 'data',
			actions: [
				{ name: 'action_add', disabled: true },
				{ name: 'action_edit', disabled: false },
				{ name: 'action_view' },
				{ name: 'action_delete', disabled: true, handler: this.deleteUnitOfTrading.createDelegate(this) },
				{ name: 'action_refresh' },
				{ name: 'action_print' }
			],
			stringfields: [
				{ name: 'WhsDocumentUc_id', type: 'int', hidden: true, key: true },
				{ name: 'WhsDocumentUc_pid', type: 'int', hidden: true },
				{ name: 'WhsDocumentUc_Num', header: lang['№_lota'], type: 'string', isparams: true, align: 'right' },
                { name: 'WhsDocumentUc_Name', header: lang['naimenovanie_lota'], type: 'string', isparams: true, width: 200 },
                { name: 'WhsDocumentUc_Sum', header: lang['summa_lota'], type: 'string', align: 'right' },
                { name: 'PurchObjType_Name', header: 'Объект закупки', type: 'string', width: 120 },
				{ name: 'DrugFinance_id', header: lang['id_istochnik_finansirovaniya'], type: 'int', isparams: true, hidden: true },
                { name: 'WhsDocumentCostItemType_id', header: lang['id_statya_rashodov'], type: 'int', isparams: true, hidden: true },
                { name: 'FinanceSource_Name', header: 'Источник оплаты', type: 'string', width: 170 },
                { name: 'DrugFinance_Name', header: lang['istochnik_finansirovaniya'], type: 'string', width: 170 },
                { name: 'WhsDocumentCostItemType_Name', header: lang['statya_rashodov'], type: 'string', width: 120 },
                { name: 'BudgetFormType_Name', header: lang['tselevaya_statya'], type: 'string', width: 150 },
                { name: 'WhsDocumentPurchType_Name', header: 'Тип закупа', type: 'string', width: 150 },
                { name: 'Supply_Data', header: lang['gk'], type: 'string', width: 180 },
                { name: 'WhsDocumentUc_Date', header: lang['data_izmeneniya'], type: 'string', width: 110, align: 'right' },
                { name: 'WhsDocumentProcurementRequest_setDate', type: 'string', hidden: true },
                { name: 'PurchObjType_id', type: 'int', hidden: true },
                { name: 'FinanceSource_id', type: 'int', hidden: true },
                { name: 'BudgetFormType_id', type: 'int', hidden: true },
                { name: 'WhsDocumentPurchType_id', type: 'int', hidden: true },
                { name: 'Okved_id', type: 'int', hidden: true },
                { name: 'WhsDocumentProcurementRequestSpecDop_id', type: 'int', hidden: true },
                { name: 'Okved_Code', header: 'Код ОКВЭД', type: 'string', width: 110, align: 'right' },
                { name: 'WhsDocumentProcurementRequestSpecDop_CodeKOSGU', header: 'Код КОСГУ', type: 'string', width: 110, align: 'right' },
				{ name: 'isSigned', header: lang['podpisan'], type: 'checkbox', width: 80, isparams: true },
                { name: 'Okpd_id', type: 'int', hidden: true },
                { name: 'WhsDocumentProcurementRequestSpecDop_Count', type: 'string', hidden: true },
                { name: 'SupplyPlaceType_id', type: 'int', hidden: true },
                { name: 'ProvSizeType_id', type: 'int', hidden: true },
                { name: 'WhsDocumentStatusType_id', type: 'int', hidden: true },
                { name: 'WhsDocumentUcStatusType_id', type: 'int', hidden: true },
                { name: 'PMUser_Name', type: 'string', header: 'Сотрудник', width: 110 },
                { name: 'WhsDocumentUcStatusType_Name', type: 'string', header: 'Статус', width: 110 },
                { name: 'Org_aid', type: 'int', hidden: true }
			],
            totalProperty: 'totalCount'
		});
        this.GridPanel.setParam('pmUser_did', getGlobalOptions().pmuser_id, 1);
        
		Ext.apply(this.GridPanel.ViewGridPanel, {
			collapsible: true,
			titleCollapse: true,
			animCollapse: false
		});
		
		this.GridPanel.getGrid().getSelectionModel().on('rowselect', function(sm, rIdx, rec) {
            var selCount = this.GridPanel.getGrid().getSelectionModel().selections.length;
            var mergeAvail = false;
            if(selCount > 1) {
                mergeAvail = true;
                var items = this.GridPanel.getGrid().getSelectionModel().selections.items;
                for(var i=0;i<items.length;i++){
                    if(items[i].data.isSigned == 'true'){
                        mergeAvail = false;
                    }
                }
            }
			var isMz = (sw.Promed.MedStaffFactByUser.store.findBy(function(rec) { return rec.get('ARMType') == 'minzdravdlo'; }) > -1);

			if( !Ext.isEmpty(rec.get('WhsDocumentUc_id')) ) {
				with( this.GridPanel2.getGrid().getStore() ) {
					baseParams['WhsDocumentUc_id'] = rec.get('WhsDocumentUc_id');
					baseParams['DrugRequest_id'] = this.FilterPanel.getForm().findField('DrugRequest_id').getValue();
					baseParams['stsrt'] = 0;
					load();
				}
				this.GridPanel2.setParam('WhsDocumentUc_id', rec.get('WhsDocumentUc_id'), 0);
				this.GridPanel2.setParam('DrugRequest_id', this.FilterPanel.getForm().findField('DrugRequest_id').getValue(), 0);
                this.GridPanel2.setActionDisabled('action_refresh', false);
			} else {
                this.GridPanel2.setParam('WhsDocumentUc_id', null, 0);
                this.GridPanel2.setActionDisabled('action_refresh', true);
            }

			this.setDisabledAction(this.GridPanel, 'unsign', rec.get('isSigned') != 'true' /*|| !isMz*/);
			this.setDisabledAction(this.GridPanel, 'sign', rec.get('isSigned') == 'true');
			this.setDisabledAction(this.GridPanel, 'sign_all', !this.issetUnSignedUot(this.GridPanel.getGrid().getStore()));
            this.setDisabledAction(this.GridPanel, 'merge', !(mergeAvail && this.enableMerge));
            this.setDisabledAction(this.GridPanel, 'export', selCount > 1);
			this.GridPanel.setActionDisabled('docsdata', (rec.get('isSigned') == 'true' || selCount > 1));
            this.GridPanel.setActionDisabled('select_print_act', (selCount > 1));
            this.GridPanel.setActionDisabled('action_edit', (rec.get('isSigned') == 'true' || selCount > 1));
            this.GridPanel.setActionDisabled('copy_uot', (selCount > 1 || !this.enableCopy));
            this.GridPanel.setActionDisabled('action_delete', (!(this.enableDelete && selCount == 1 && rec.get('isSigned') != 'true' && this.GridPanel2.getGrid().getStore().data.length == 0)));
            this.GridPanel2.setActionDisabled('pricecount', rec.get('isSigned') == 'true');
			this.GridPanel2.setActionDisabled('action_add', (rec.get('isSigned') == 'true' || !this.enableAddDrug));
			this.GridPanel2.setActionDisabled('action_delete', (rec.get('isSigned') == 'true' || !this.enableDelDrug));
            this.GridPanel2.setActionDisabled('action_edit', rec.get('isSigned') == 'true');
		}, this);

        this.GridPanel.getGrid().getSelectionModel().on('rowdeselect', function(sm, rIdx, rec) {
            var selCount = this.GridPanel.getGrid().getSelectionModel().selections.length;
            if(selCount == 1){
                var selected = this.GridPanel.getGrid().getSelectionModel().getSelected();
            }
            var mergeAvail = false;
            if(selCount > 1) {
                mergeAvail = true;
                var items = this.GridPanel.getGrid().getSelectionModel().selections.items;
                for(var i=0;i<items.length;i++){
                    if(items[i].data.isSigned == 'true'){
                        mergeAvail = false;
                    }
                }
            }
            var isMz = (sw.Promed.MedStaffFactByUser.store.findBy(function(rec) { return rec.get('ARMType') == 'minzdravdlo'; }) > -1);

            if( selCount == 1 && !Ext.isEmpty(selected.get('WhsDocumentUc_id')) ) {
                with( this.GridPanel2.getGrid().getStore() ) {
                    baseParams['WhsDocumentUc_id'] = selected.get('WhsDocumentUc_id');
                    baseParams['DrugRequest_id'] = this.FilterPanel.getForm().findField('DrugRequest_id').getValue();
                    baseParams['stsrt'] = 0;
                    load();
                }
                this.GridPanel2.setParam('WhsDocumentUc_id', selected.get('WhsDocumentUc_id'), 0);
                this.GridPanel2.setParam('DrugRequest_id', this.FilterPanel.getForm().findField('DrugRequest_id').getValue(), 0);
            }
            this.setDisabledAction(this.GridPanel, 'unsign', this.issetUnSignedUot(this.GridPanel.getGrid().getStore()));
            this.setDisabledAction(this.GridPanel, 'sign', !(selCount == 1 && selected.get('isSigned') != 'true'));
            this.setDisabledAction(this.GridPanel, 'sign_all', !this.issetUnSignedUot(this.GridPanel.getGrid().getStore()));
            this.setDisabledAction(this.GridPanel, 'merge', !(mergeAvail && this.enableMerge));
            this.setDisabledAction(this.GridPanel, 'export', selCount > 1);
            this.GridPanel.setActionDisabled('docsdata', !(selCount == 1 && selected.get('isSigned') != 'true'));
            this.GridPanel.setActionDisabled('select_print_act', (selCount > 1));
            this.GridPanel.setActionDisabled('action_edit', !(selCount == 1 && selected.get('isSigned') != 'true'));
            this.GridPanel.setActionDisabled('copy_uot', (selCount > 1 || !this.enableCopy));
            this.GridPanel.setActionDisabled('action_delete', (!(this.enableDelete && selCount == 1 && selected.get('isSigned') != 'true' && this.GridPanel2.getGrid().getStore().data.length == 0)));
            /*this.GridPanel2.setActionDisabled('pricecount', rec.get('isSigned') == 'true');
            this.GridPanel2.setActionDisabled('action_add', rec.get('isSigned') == 'true');
            this.GridPanel2.setActionDisabled('action_delete', rec.get('isSigned') == 'true');
            this.GridPanel2.setActionDisabled('action_edit', rec.get('isSigned') == 'true');*/
        }, this);
		
		this.GridPanel.getGrid().getStore().on('clear', function() {
			this.GridPanel2.removeAll({clearAll:true});
		}, this);
		
		this.GridPanel.getGrid().on('collapse', function(p) {
			this.GridPanelWrap.getEl().setHeight(p.header.getHeight());
			this.doLayout();
		}, this);
		this.GridPanel.getGrid().on('expand', function(p) {
			this.GridPanelWrap.getEl().setHeight(p.getEl().getHeight());
			this.doLayout();
		}, this);
		
		this.GridPanel.getGrid().getStore().on('load', function(s) {
			s.each(function(r, i) {
				var row = this.GridPanel.getGrid().getView().getRow(i);
				this.createDDTarget(row, r);
			}, this);
            var actions = this.GridPanel.getAction('uotv_actions').items[0].menu.items;
            if(actions.length > 0){
                var form_action = actions.find(function(rec){
                    return (rec.name == 'form');
                });
                var reform_action = actions.find(function(rec){
                    return (rec.name == 'reform');
                });
                if(this.GridPanel.getGrid().getStore().data.length>0){
                    if(form_action){
                        //form_action.setVisible(false);
                    }
                    if(reform_action && reform_action.name.inlist(this.visibleActions) && !(this.ARMType == 'zakup' && !this.isDirector())){
                        reform_action.setVisible(true);
                    }
                } else {
                    if(reform_action){
                        reform_action.setVisible(false);
                    }
                    if(form_action && form_action.name.inlist(this.visibleActions) && !(this.ARMType == 'zakup' && !this.isDirector())){
                        form_action.setVisible(true);
                    }
                } 
            }
		}, this);
		
		this.GridPanelWrap = new Ext.Panel({
			region: 'north',
			autoHeight: true,
			collapsible: true,
			titleCollapse: true,
			animCollapse: false,
			border: false,
			layout: 'fit',
			items: [this.GridPanel]
		});

		this.GridPanel2 = new sw.Promed.ViewFrame({			
			title: lang['medikamentyi_lota'],
			ddGroup: this.id + '_gridDDGroup',
			enableDragDrop: true,
			region: 'center',
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 100,
			id: this.id + '_SouthGrid',
			pageSize: 50,
			selectionModel: 'multiselect',
			paging: true,
			autoScroll: true,
			listeners: {
				resize: function() {
					if( this.layout.layout ) this.doLayout();
				}
			},
			editformclassname: 'swUnitOfTradingDrugEditWindow',
			autoLoadData: false,
			root: 'data',
			actions: [
				{ name: 'action_add', handler: this.addDrugInUnitOfTrading.createDelegate(this) },
				{ name: 'action_edit' },
				{ name: 'action_view' },
				{ name: 'action_delete', handler: this.deleteDrugListInUnitOfTrading.createDelegate(this) },
				{ name: 'action_refresh' },
				{ name: 'action_print', hidden: true }
			],
			stringfields: [
				{ name: 'DrugRequestPurchaseSpec_id', type: 'int', hidden: true, key: true },
				{ name: 'WhsDocumentUc_id', type: 'int', hidden: true },
                { name: 'DrugComplexMnnCode_Code', type: 'string', header: ((getRegionNick()=='saratov')?'Идентификационный код':'Код'), width: ((getRegionNick()=='saratov')?160:80), align: 'left' },
				{ name: 'Drug_Name', type: 'string', header: lang['naimenovanie'], width: 300 },
                { name: 'Tradenames_Name', type: 'string', header: lang['torgovoe_naimenovanie'], width: 300 },
                { name: 'WhsDocumentProcurementRequestSpec_Kolvo', type: 'string', header: 'Кол-во (уп.)', align: 'right' },
                { name: 'WhsDocumentProcurementRequestSpec_PriceMax', type: 'string', header: 'Цена (за уп.)', align: 'right' },
                { name: 'CalculatPriceType_Name', type: 'string', header: 'Тип расчета цены' },
                { name: 'WhsDocumentProcurementRequestSpec_CalcPriceDate', type: 'string', header: 'Дата расчета цены', align: 'right' },
				{ name: 'GoodsUnit_Name', type: 'string', header: lang['ed_izm_tov'] },
                { name: 'PriceForOkei', type: 'string', header: 'Цена за ед.изм.тов.', align: 'right' },
				{ name: 'DrugRequestPurchaseSpec_Sum', type: 'string', header: lang['summa'], align: 'right' },
                { name: 'WhsDocumentProcurementRequestSpec_id', type: 'int', hidden: true },
                { name: 'CalculatPriceType_id', type: 'int', hidden: true },
                { name: 'WhsDocumentProcurementRequestSpec_Name', type: 'string', hidden: true },
                { name: 'maxKolvo', type: 'int', hidden: true },
                { name: 'GoodsUnit_id', type: 'int', hidden: true },
                { name: 'WhsDocumentProcurementRequestSpec_Count', type: 'int', hidden: true },
                { name: 'DrugComplexMnn_id', type: 'int', hidden: true },
                { name: 'TRADENAMES_ID', type: 'int', hidden: true },
                { name: 'InJnvlp', type: 'int', hidden: true }
			],
			dataUrl: '/?c=UnitOfTrading&m=loadDrugListOnUnitOfTrading',
			totalProperty: 'totalCount'
		});
		
		// костылина блеать...
		this.GridPanel2.getGrid().on('render', function() {
			var w = this;
			this.GridPanel2.getGrid().getView().dragZone.onBeforeDrag = function(data, e) {
				if( w.getSelectedUot().get('isSigned') == 'true' ) {
					return false;
				}
				return true;
			}
		}, this);

        this.GridPanel2.onMultiSelectionChangeAdvanced = function(sm) {
            var selCount2 = sm.grid.getSelectionModel().selections.length;
            this.GridPanel2.setActionDisabled('pricecount', selCount2 > 1);
        }.createDelegate(this);

		this.CenterPanel = new Ext.form.FormPanel({
			region: 'center',
			border: false,
			layout: 'border',
			items: [this.GridPanelWrap, this.GridPanel2]
		});
		
		Ext.apply(this,	{
			layout: 'border',
			items: [this.FilterPanel, this.CenterPanel],
			buttons: [{
					handler: function() {
						this.doSearch();
					}.createDelegate(this),
					iconCls: 'search16',
					tabIndex: TABINDEX_UOTVW + 96,
					text: BTN_FRMSEARCH
				}, {
					handler: function() {
						this.FilterPanel.getForm().reset();
						this.GridPanel.removeAll({clearAll:true});
					}.createDelegate(this),
					iconCls: 'resetsearch16',
					tabIndex: TABINDEX_UOTVW + 97,
					text: BTN_FRMRESET
				},
				'-',
				HelpButton(this, TABINDEX_UOTVW + 98),
				{
					text: lang['zakryit'],
					tabIndex: -1,
					tooltip: lang['zakryit'],
					iconCls: 'cancel16',
					handler: this.hide.createDelegate(this, [])
				}
			]
		});
		sw.Promed.swUnitOfTradingViewWindow.superclass.initComponent.apply(this, arguments);
	}
});