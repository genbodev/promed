/**
* АРМ специалиста ГКУ
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @autor		Vasinsky Igor, Ufa
* @copyright    Copyright (c) 2011 Swan Ltd.
* @version      май 2015
*/
  
sw.Promed.swWorkPlaceGKUWindow = Ext.extend(sw.Promed.swWorkPlaceWindow,
{
	id: 'swWorkPlaceGKUWindow',
    filters: {},  
    filterParams: {
        type: 'lots'
    },  
    isDirector : function(){
      return isUserGroup('director');  
    },    
	buttonPanelActions: {
		action_Action2: {
			nn: 'action_Action2',
			tooltip: lang['zayavka'],
			text: lang['zayavka'],
			iconCls : 'mp-drugrequest32',
			disabled: false,
			menuAlign: 'tr',
			menu: new Ext.menu.Menu({
				items: [
                {
					text: lang['spiski_medikamentov_dlya_zayavki'],
					tooltip: lang['spiski_medikamentov_dlya_zayavki'],
					iconCls : 'pill16',
					handler: function() {
						getWnd('swDrugRequestPropertyViewWindow').show();
					}
				},{
					text: lang['svodnaya_zayavka'],
					tooltip: lang['svodnaya_zayavka_prosmotr_utverjdenie'],
					iconCls : 'otd-profile16',
					handler: function() {
						getWnd('swConsolidatedDrugRequestViewWindow').show({
                            onlyView: true
                        });
					}
				}, {
					tooltip: lang['lotyi'],
					text: lang['lotyi'],
                    hidden: true,
					iconCls : 'settings16',					
					handler: function(){
						getWnd('swUnitOfTradingViewWindow').show({ARMType: 'zakup'});
					}.createDelegate(this)
				}]
			})
		},
		action_createLots: {
			nn: 'action_createLots',
			tooltip: lang['upravlenie_lotami'],
			text: lang['upravlenie_lotami'],
            id : 'createlots',
		 	iconCls : 'eph-record32',
			handler: function() {
                getWnd('swUnitOfTradingViewWindow').show({ARMType: 'zakup'});
			}
		},          
		action_manageLots: {
			nn: 'action_manageLots',
			tooltip: lang['rabota_s_lotami'],
			text: lang['rabota_s_lotami'],
            id : 'lots',
		    tooltip:  lang['rabota_s_lotami'],
		 	iconCls : 'reports32',
			handler: function() {
                if(Ext.getCmp('swWorkPlaceGKUWindow').isDirector()){
					getWnd('swUnitOfTradingDistributeViewWindow').show(
                        {
                         pmUser_id : getGlobalOptions().pmuser_id, 
                         org_id    : getGlobalOptions().org_id
                        }
                    );
                }
				else{
                    getWnd('swUnitOfTradingViewWindow').show({ARMType: 'zakup'});
                }
			}
		},                  
		action_Action11: {
			nn: 'action_Action11',
			tooltip: lang['dokumentyi'],
			text: lang['dokumentyi'],
			iconCls : 'document32',
			disabled: false, 
			menuAlign: 'tr',
			menu: new Ext.menu.Menu({
				items: [
                {
					text: lang['kommercheskie_predlojeniya'],
					tooltip: lang['kommercheskie_predlojeniya'],
					iconCls : 'document16',
					handler: function() {
						getWnd('swCommercialOfferViewWindow').show();
				    }
				}                          
                ]
			})
		},  
		action_SwhDocumentSupply: {
			nn: 'action_SwhDocumentSupply',
			tooltip: lang['gosudarstvennyie_kontraktyi'],
			text: lang['gosudarstvennyie_kontraktyi'],
			iconCls : 'card-state32',
			disabled: false,
            menuAlign: 'tr',
            menu: new Ext.menu.Menu({
                items: [{
                    tooltip: lang['goskontraktyi_na_postavku'],
                    text: lang['goskontraktyi_na_postavku'],
                    iconCls : 'document16',
                    handler: function(){
                        getWnd('swWhsDocumentSupplyViewWindow').show({ARMType: 'zakup'});
                    }
                }, {
                    tooltip: lang['dopolnitelnyie_soglasheniya'],
                    text: lang['dopolnitelnyie_soglasheniya'],
                    iconCls : 'document16',
                    handler: function(){
                        getWnd('swWhsDocumentSupplyAdditionalViewWindow').show({ARMType: 'zakup'});
                    }
				}]
            })
		},
		 action_MedOstat: {
            nn: 'action_MedOstat',
			id: 'action_MedOstat',
            tooltip: langs('Учет ЛС'),
            text: langs('Учет ЛС'),
            iconCls : 'rls-torg32',
            disabled: false,
            menuAlign: 'tr',
            menu: new Ext.menu.Menu({
                items: [
					{
						text: 'Оборотная ведомость',
						tooltip: 'Оборотная ведомость',
						iconCls: 'pill16',
						id: 'MerchandiserObVed',
						//hidden: (Ext.getCmp('swWorkPlaceGKUWindow').sprType != 'dbo'),
						handler: function() {
							var wnd = Ext.getCmp('swWorkPlaceGKUWindow');
							var params = new Object();
							if(getGlobalOptions().orgtype == 'lpu')
								params.LpuSection_id =  wnd.userMedStaffFact.LpuSection_id;
							getWnd('swDrugTurnoverListWindow').show(params);
						}
					},
					{
						text: langs('Документы учета РАС'),
						tooltip: langs('Документы учета РАС'),
						iconCls: 'pill16',
						handler: function() {
							getWnd('swWorkPlaceMerchandiserWindow').show({
								ARMType: 'merch',
								ARMType_id: 15
							});
						}
					}, {
						text: langs('Просмотр остатков по складам Аптек и РАС'),
						tooltip: langs('Просмотр остатков по складам Аптек и РАС'),
						iconCls: 'pill16',
						id: 'MerchandiserProsmotrRas',
						handler: function() {
							var wnd = Ext.getCmp('swWorkPlaceGKUWindow');

							getWnd('swDrugOstatRegistryListWindow').show({
								mode: 'farmacy_and_store',
								userMedStaffFact: wnd.userMedStaffFact
							});
						}
					}
                ]
            })
        },
		action_References: {
			nn: 'action_References',
			tooltip: lang['spravochniki'],
			text: lang['spravochniki'],
			iconCls : 'book32',
			disabled: false, 
			menuAlign: 'tr?',
			menu: new Ext.menu.Menu({
				items: [
                {
					tooltip: lang['prosmotr_rls'],
					text: lang['prosmotr_rls'],
					iconCls: 'rls16',
					handler: function() {
						if ( !getWnd('swRlsViewForm').isVisible() )
							getWnd('swRlsViewForm').show();
					}
				}, {
					tooltip: lang['mkb-10'],
					text: lang['spravochnik_mkb-10'],
					iconCls: 'spr-mkb16',
					handler: function() {
						if ( !getWnd('swMkb10SearchWindow').isVisible() )
							getWnd('swMkb10SearchWindow').show();
					}
				}, {
					tooltip: lang['prosmotr'] + getMESAlias(),
					text: lang['prosmotr'] + getMESAlias(),
					iconCls: 'spr-mes16',
					handler: function() {
						if ( !getWnd('swMesOldSearchWindow').isVisible() )
							getWnd('swMesOldSearchWindow').show();
					}
				}, {
					name: 'action_DrugNomenSpr',
					text: lang['nomenklaturnyiy_spravochnik'],
					iconCls : '',
					handler: function()
					{
						getWnd('swDrugNomenSprWindow').show({readOnly: false});
					}
				}, {
					name: 'action_PriceJNVLP',
					text: lang['tsenyi_na_jnvlp'],
					iconCls : 'dlo16',
					handler: function() {
						getWnd('swJNVLPPriceViewWindow').show();
					}
				}, {
					name: 'action_DrugMarkup',
					text: lang['predelnyie_nadbavki_na_jnvlp'],
					iconCls : 'lpu-finans16',
					handler: function() {
						getWnd('swDrugMarkupViewWindow').show();
					}
				}, {
					name: 'action_DrugRMZ',
					text: lang['spravochnik_rzn'],
					iconCls : 'view16',
					handler: function() {
						getWnd('swDrugRMZViewWindow').show();
					}
				}, {
					name: 'action_UslugaComplexTariffLlo',
					text: lang['tarifyi_llo'],
					iconCls : 'lpu-finans16',
					handler: function() {
						getWnd('swUslugaComplexTariffLloViewWindow').show();
					}
				},
				sw.Promed.Actions.swPrepBlockSprAction,
				{
					text: 'Единицы измерения товара',
					tooltip: 'Единицы измерения товара',
					handler: function() {
						getWnd('swGoodsUnitViewWindow').show();
					}
				}
				]
			})
		},
		action_JourNotice: {
			handler: function() {
				getWnd('swMessagesViewWindow').show();
			}.createDelegate(this),
			iconCls: 'notice32',
			nn: 'action_JourNotice',
			text: lang['jurnal_uvedomleniy'],
			tooltip: lang['jurnal_uvedomleniy']
		}, 
		action_Reports: {
			handler: function() {
						if (sw.codeInfo.loadEngineReports) {
							getWnd('swReportEndUserWindow').show();
						} else {
							getWnd('reports').load({
								callback: function(success) {
									sw.codeInfo.loadEngineReports = success;
									// здесь можно проверять только успешную загрузку 
									getWnd('swReportEndUserWindow').show();
								}
							});
						}
			}.createDelegate(this),
			iconCls: 'report32',
			nn: 'action_Reports',
			text: lang['prosmotr_otchtetov'],
			tooltip: lang['prosmotr_otchtetov']
		}
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

    initComponent: function() {
		
			var form = this;
            
			this.onKeyDown = function (inp, e) {
    			if (e.getKey() == Ext.EventObject.ENTER) {
    				e.stopEvent();
    				this.doSearch();
    			}
 		    }.createDelegate(this); 

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
									this.GridPanel.loadData({globalFilters: Ext.apply({
										start: 0
									}, this.FilterPanel.getForm().getValues())});
									
									this.GridPanel.setParam(c.valueField, c.getValue(), 0);
									//this.GridPanel2.setParam(c.valueField, c.getValue(), 0);
									//this.GridPanel2.getGrid().getStore().baseParams[c.valueField] = c.getValue();
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
					}]
				}]
			}]
		});



		this.GridPanel =  new sw.Promed.ViewFrame({
			id: 'GridLots',
			region: 'center',
			autoExpandColumn: 'autoexpand',
			paging: true,
	        autoload : false,
	        start:0,
			totalProperty: 'totalCount',
	        hidefooter:false,
	        remoteSort: false,
	        autoLoadData: false,
	        autoLoad: false,
	        closeAction: 'hide',
			actions:
			[
				{name:'action_add', hidden: true},
	            {name:'action_edit', hidden: true},
	            {name:'action_view',hidden: true},
				{name:'action_delete', hidden: true},
				{name: 'action_refresh', hidden: false},
	            {name:'action_print', hidden: false}
			],	
	        /*
			onLoadData: function(sm, index, record) {
	            
	        },
	        */
			pageSize: 50,
	        start:0,                        
	        root: 'data',
	        cls: 'txtwrap',
	        stringfields:
	            [
				{ name: 'WhsDocumentUc_id', type: 'int', hidden: true, key: true },
				{ name: 'WhsDocumentUc_pid', type: 'int', hidden: true },
				{ name: 'WhsDocumentUc_Num', header: lang['№_lota'], type: 'string' },
				{ name: 'WhsDocumentUc_Name',  id: 'autoexpand', header: lang['naimenovanie_lota'], type: 'string' },
	        	{ name: 'WhsDocumentUc_Sum', header: lang['summa_lota'], type: 'string', align: 'right' },
	            { name: 'PMUser_Name', header: lang['sotrudnik'], type: 'string', width: 210 }, 
				{ name: 'WhsDocumentUc_Date', header: lang['data_izmeneniya'], type: 'string', width: 110 },          
				{ name: 'Supply_Data', header: lang['gk'], type: 'string', width: 180 },
				{ name: 'isSigned', header: lang['podpisan'], type: 'checkbox', width: 80 },
	            { name: 'WhsDocumentUcPMUser_id', header: 'WhsDocumentUcPMUser_id', type: 'int', width: 110, hidden: true}, 
	            { name: 'PMUser_did', header: lang['status_lota'], type: 'int', width: 110, hidden: true}, 
	            { name: 'PMUser_did', header: 'PMUser_did', type: 'int', width: 110, hidden: true}, 
	            { name: 'WhsDocumentUcStatusType_id', header: 'WhsDocumentStatusType_id', type: 'int', width: 110, hidden: true}, 
	            { name: 'WhsDocumentUcStatusType_Name', header: lang['status'], type: 'string', width: 210 },
	            { name: 'Org_Nick', header: 'Организация', type: 'string', width: 150 },
	            { name: 'Org_aid', type: 'int', hidden: true },
	            { name: 'WhsDocumentCostItemType_id', type: 'int', hidden: true },
	            { name: 'DrugFinance_id', type: 'int', hidden: true },
	            { name: 'BudgetFormType_id', type: 'int', hidden: true },
	            { name: 'WhsDocumentPurchType_id', type: 'int', hidden: true }
	            ],
	                                              
	        dataUrl: '/?c=Gku&m=loadUnitOfTradingListWithStatus',
	        title: lang['spisok_lotov']
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
			
			//this.setDisabledAction(this.GridPanel, 'changestatus', (selCount > 1));
		}, this);
		this.GridPanel.getGrid().getSelectionModel().on('rowdeselect', function(sm, rIdx, rec) {
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
			
			//this.setDisabledAction(this.GridPanel, 'changestatus', (selCount > 1));
		}, this);
  
        //Ext.getCmp('GridLpu4ReportToolbar').hide();
        
        /**
         +lang['nujno']+ +lang['opredelitsya']+ +lang['s']+ +lang['neobhodimyimi']+ +lang['filtrami']+
        */ 
         
        //Фильтр "ЛОТЫ"
        /*
        this.filters.filterLots = {
                layout: 'column',
                width: 1000,
                items: [
                    {
                        layout : 'form',
                        items : [
                            {
                                xtype : 'numberfield',
                                id : 'pricestart',
                                fieldLabel : lang['tsena_ot']
                            },
                            {
                                xtype : 'numberfield',
                                id : 'countstart',
                                fieldLabel : lang['kolichestvo_ot']
                            },
                            {
                                xtype : 'numberfield',
                                id : 'summstart',
                                fieldLabel : lang['summa_lota_ot']
                            },
                            {
                                xtype : 'numberfield',
                                id : 'grlsstart',
                                fieldLabel : lang['grls_ot']
                            }                                                                                                                          
                        ]
                    },
                    {
                        columnWidth : 0.8 ,
                        layout : 'form',
                        items : [
                            {
                                xtype : 'numberfield',
                                id : 'priceend',
                                fieldLabel : lang['tsena_do']
                            },
                            {
                                xtype : 'numberfield',
                                id : 'countend',
                                fieldLabel : lang['kolichestvo_do']
                            },
                            {
                                xtype : 'numberfield',
                                id : 'summend',
                                fieldLabel : lang['summa_lota_do']
                            },
                            {
                                xtype : 'numberfield',
                                id : 'grlsend',
                                fieldLabel : lang['grls_do']
                            }                                                                                                                          
                        ]
                    }, 
                    {
                        layout : 'form',
                        labelWidth: 30,
                        width: 400,
                        items : [
                            {
                                xtype : 'checkbox',
                                id : 'lotswithcontracts',
                                labelSeparator: '',
                                hideLabel: true,
                                boxLabel: lang['skryit_lotyi_s_kontraktami'],
                                listeners : {
                                    'check' : function(){
                                        this.filterParams.lots
                                    }
                                }
                            },
                            {
                                xtype : 'textfield',
                                width : 250,
                                id : 'mnn',
                                labelAlign : 'left',
                                fieldLabel : lang['mnn'],
                                labelStyle: 'width:30px!important',
                                
                            },                            
                        ]
                    }                   

                ]
        }
        
        //Фильтр справочники
        this.filters.filterReference = {
            
        }
        
        //Фильтр коммерческие предложения
        this.filters.filterCommercialOffer = {
          
        }

        //Фильтр "РАСПРЕДЕЛЕНИЕ ЛОТОВ"
        this.filters.filterManageLots = {

        }
        
        //Сводная заявка
        this.filters.filterListLS = {
             
        }
        
        //Фильтр сводной потредбности в лс
        this.filters.filterSummaryRequirements = {
         
        }
		*/
		sw.Promed.swWorkPlaceGKUWindow.superclass.initComponent.apply(this, arguments);
	},
    show: function(){
		sw.Promed.swWorkPlaceGKUWindow.superclass.show.apply(this, arguments);

		this.getCurrentDateTime();
		this.onChangeDates('day');
        
        //Смена статусов лотов и создание контракта доступны только сотрудникам, не руководителю
        
        //console.log('this.isDirector()', this.isDirector());
        
        if(!this.isDirector()){ 
            //Параметр, необходимый только для сотрудников - чтобы выводить список лотов, закреплённый за конкретным сотрудником
            this.GridPanel.getGrid().getStore().baseParams.isDirector = 1;

            var changestatus = {
                name: 'changestatus',
                iconCls: 'edit16',
                scope: this,
                text: lang['izmenit_status_lota'],
                handler : function(){
                	var grid = this.GridPanel.getGrid().getStore();
                    var record = this.GridPanel.getGrid().getSelectionModel().getSelected();
                    var params = {
                    	WhsDocumentUc_Num : record.get('WhsDocumentUc_Num'),
                    	WhsDocumentUc_id : record.get('WhsDocumentUc_id'),
                    	WhsDocumentUcStatusType_id : record.get('WhsDocumentUcStatusType_id'),
                    	callback:function(){
                    		grid.reload();
                    	}
                    }
                    getWnd('swWhsDocumentUcStatusTypeWindow').show(params);
                }
            }
            var setdocument = {
                name: 'setdocument',
                iconCls: 'add16',
                scope: this,
                text: lang['sozdat_kontrakt'],
                handler : function(){
                    //сразу по дефолту тип контракта "Контрнакт на поставку"
                    var params = {};
                    var grid = this.GridPanel.getGrid().getStore();
                    var sel = Ext.getCmp('GridLots').getGrid().getSelectionModel().getSelected();
                    
                   if(sel.get('WhsDocumentUc_pid') != ''){
            			sw.swMsg.show({
            				buttons: Ext.Msg.OK,
            				fn: function() {
                                   
            				}.createDelegate(this),
            				icon: Ext.Msg.WARNING,
            				msg: lang['dannyiy_lot_uje_imeet_kontrakt'],
            				title: lang['preduprejdenie']
            			});
            			return true;
                    }
                    else if(sel.get('isSigned') != 'true'){
            			sw.swMsg.show({
            				buttons: Ext.Msg.OK,
            				fn: function() {
                                   
            				}.createDelegate(this),
            				icon: Ext.Msg.WARNING,
            				msg: lang['sozdanie_kontrakta_vozmojno_tolko_u_podpisannogo_lota'],
            				title: lang['preduprejdenie']
            			});
            			return true;
                    }

                    getWnd('swSelectWhsDocumentTypeWindow').show({
						onSelect: function (data) {
							params.WhsDocumentType_id = data.WhsDocumentType_id;
		                    params.WhsDocumentUc_id = sel.get('WhsDocumentUc_id');
		                    params.WhsDocumentUc_Num = sel.get('WhsDocumentUc_Num');
		                    params.Org_cid = sel.get('Org_aid');
		                    params.Org_pid = sel.get('Org_aid');
		                    params.WhsDocumentPurchType_id = sel.get('WhsDocumentPurchType_id');
	                    	params.BudgetFormType_id = sel.get('BudgetFormType_id');
	                    	params.DrugFinance_id = sel.get('DrugFinance_id');
	                    	params.WhsDocumentCostItemType_id = sel.get('WhsDocumentCostItemType_id');
	                    	params.DeliveryGraphType = 'percent';
	                    	params.action = 'add';
	                    	params.afterSave = function(dt){
	                    		Ext.Ajax.request({
						            url: '/?c=Gku&m=changeWhsDocumentUcStatusType',
						            params: {
						                WhsDocumentUc_id: dt.WhsDocumentUc_id,
						                WhsDocumentUcStatusType_id: 8 
						            }
						        });
						        grid.reload();
	                    	};
		                    getWnd('swWhsDocumentSupplyEditWindow').show(params);
						}
					});
                }
            }  

            this.GridPanel.addActions(changestatus);  
            this.GridPanel.addActions(setdocument);    
        }
        else{
             //Пользователь из группы - Руководитель
             this.GridPanel.getGrid().getStore().baseParams.isDirector = 2;
        }
        
        /*
        this.FilterPanel.items.get(0).removeAll();
        this.FilterPanel.items.get(0).add(this.filters.filterLots);
        */
        Ext.getCmp('lots').text =  Ext.getCmp('swWorkPlaceGKUWindow').isDirector() ? lang['rabota_s_lotami'] : lang['lotyi'];
        Ext.getCmp('lots').tooltip =  Ext.getCmp('swWorkPlaceGKUWindow').isDirector() ? lang['rabota_s_lotami'] : lang['lotyi'];
        
        var actions = {
            'director' : [
                          'action_Action2',
                          'action_Action11',
                          'action_manageLots',
                          'action_SwhDocumentSupply',
                          'action_References',
                          'action_JourNotice',
                          'action_Reports',
                          'action_createLots',
						  'action_MedOstat'
                         ],
            'employee' :[
                          'action_Action11',
                          'action_SwhDocumentSupply',
                          'action_JourNotice',
                          'action_manageLots',
						  'action_JourNotice'
            ]
        }
        
        var listActions = this.LeftPanel.actions;

        if(listActions){
            for(var k in listActions){
                if(this.isDirector()){
                    if(!k.inlist(actions.director)){
                        listActions[k].hide();
                    }                 
                }
                else{
                    if(!k.inlist(actions.employee)){
                        listActions[k].hide();
                    }                       
                }
            }
        }
       
        var toolbar = this.WindowToolbar.items.items;
        
        //скрыл т.к. нет необходимости в данном фильтре по датам
        //for(var k in toolbar){
        //    if(typeof toolbar[k] == 'object'){
        //        toolbar[k].hide();
        //    }
        //}
        //this.WindowToolbar.layout = 'form';
        /*
        this.WindowToolbar.add(
              new Ext.form.FormPanel({    
                 frame: true,
                 border:false,
                 width : Ext.getBody().getWidth(),
                 items : [
                   
                    //Где нужен этот комбобокс - уточнить!!!
                   
                   {
                      id: 'comboWorksPeriods',
                      fieldLabel: '&nbsp;&nbsp;&nbsp;&nbsp;Рабочий период',
                      width: 400,
                      allowBlank: false,
                      editable: false,
                      mode: 'local',
                      triggerAction: 'all',
                      store: new Ext.data.JsonStore({
                          url: '/?c=DrugRequest&m=loadDrugRequestPeriodListCombo',
                          fields : [
                                    {name: 'DrugRequestPeriod_id', type : 'int'}, 
                                    {name: 'DrugRequestPeriod_Name', type : 'string'}
                          ],
                          autoLoad: true,
                          listeners : {
                            'load': function(){
                                Ext.getCmp('comboWorksPeriods').setValue(this.getAt(0).get('DrugRequestPeriod_Name'));
                                var DrugRequestPeriod_id = Ext.getCmp('comboWorksPeriods').getValue();
                            }
                          }
                      }),
                      hiddenName: 'DrugRequestPeriod_id',
                      name: 'DrugRequestPeriod_id',
                      displayField: 'DrugRequestPeriod_Name',
                      valueField: 'DrugRequestPeriod_id',
                      xtype: 'combo',
                      listeners : {
                        'select' : function(){
                            var DrugRequestPeriod_id = this.getValue();
                            console.log(DrugRequestPeriod_id);
                        }
                      }
                  }    
                ]      
             })     
        );
        */
        
        //console.log('this.CenterPanel',  this.CenterPanel.items.get(1));


        //this.FilterPanel.doLayout();
        
        //Скрыть кнопку "Просмотр отчётов" в боковом меню
        with(this.LeftPanel.actions) {
            action_Report.setHidden(true);
        }

        

        
    }
		 
});