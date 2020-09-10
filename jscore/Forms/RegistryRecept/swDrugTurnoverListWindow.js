/**
* amm_JournalViewWindow - окно просмотра "Оборотная ведомость"
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Нигматуллин Тагир
* @version      февраль 2016

*/

//var Record;

sw.Promed.swDrugTurnoverListWindow = Ext.extend(sw.Promed.BaseForm, {
        title: "Оборотная ведомость",
        id: 'DrugTurnoverListWindow',
        border: false,
        width: 800,
        height: 500,
        maximized: true,
		maximizable: true,        
        layout:'border',
        resizable: true,
        codeRefresh: true,
		org_id: getGlobalOptions().org_id,
		lpuSection_id: null,
        listeners: {
		hide: function() {
			this.onHide();
		},
                'success': function(source, params) {
                    Cnt = Ext.getCmp('DrugTurnoverGrid').ViewGridPanel.getStore().data.items.length;
                     for (var r = 0; r <= Cnt - 1; r++) {                    
                       record = Ext.getCmp('DrugTurnoverGrid').ViewGridPanel.getStore().data.items[r].data;
                       
                        if (params.DrugOstatRegistry_id == record.DrugOstatRegistry_id) {
                             Ext.getCmp('DrugTurnoverGrid').getGrid().getSelectionModel().selectRow(r);
                             record = Ext.getCmp('DrugTurnoverGrid').getGrid().getSelectionModel().getSelected();
                             record.set('DrugOstatRegistry_Kolvo', params.DrugOstat_Kolvo);
			     			 record.set('EndOst_Contr', params.EndOst_Contr);
                            record.commit();
                            break;
                        }
                    }
                }
		,restore: function ( win, width, height ) {
		    this.doResize();
		},
		maximize: function ( win, width, height ) {
		     this.doResize();
		},
		resize: function ( win, width, height ) {
		     this.doResize();
		 }
	},
	doResize: function() {
	    var $width = Ext.getCmp('DrugTurnoverGrid').getColumnModel().getColumnWidth (5);
	    Ext.getCmp('DrugTurnoverGridItogo').getColumnModel().setColumnWidth(5, $width)
		//console.log('Width1 = ' + $width, 'Width2 = ' + Ext.getCmp('DrugTurnoverGridItogo').getColumnModel().getColumnWidth (5));
	},
        doSearch: function() {
            var form = this;
            var params = new Object();
            console.log('$Org_id = ' + form.org_id);
            params.Org_id = form.org_id;
            params.PeriodRange = Ext.getCmp('DrugTurnover_PeriodDate').value;
            //console.log('doSearch');
            params.Drug_Name =  Ext.getCmp('DrugTurnover_DrugName').getValue();
			params.DrugMNN_Name =  Ext.getCmp('DrugTurnover_MNNName').getValue();
            params.Drug_Code = Ext.getCmp('DrugTurnover_DrugCode').getValue();  
			params.DrugFinance_id =  Ext.getCmp('DrugTurnover_DrugFinance').getValue();
            params.WhsDocumentCostItemType_id =  Ext.getCmp('DrugTurnover_WhsDocumentCostItemType').getValue();
            params.Storage_id =  Ext.getCmp('DrugTurnover_Storage').getValue();
            params.Differences = Ext.getCmp('DrugTurnover_Differences').getValue()?1:0;
	    params.SubAccountType_id =  Ext.getCmp('DrugTurnover_SubAccountTypeCombo').getValue();
		if (getGlobalOptions().orgtype == 'lpu')
			params.LpuSection_id = form.lpuSection_id;
			
            
	    // Скрываем / отображаем поле "Числится на складе"
	    var d1 = new Date();
	    d1 = new Date(d1.getFullYear(), d1.getMonth(), d1.getDate());
	    if (d1 <= Ext.getCmp('DrugTurnover_PeriodDate').getValue2()) {
		form.rec_Contr = 1;
		} else {
		form.rec_Contr = 0;
	    }
	    
	    var idx = Ext.getCmp('DrugTurnoverGrid').getColumnModel().findColumnIndex('DrugOstatRegistry_Kolvo');
	    
	    Ext.getCmp('DrugTurnoverGrid').getColumnModel().setHidden (idx, form.rec_Contr != 1);
	    Ext.getCmp('DrugTurnoverGridItogo').getColumnModel().setHidden (idx, form.rec_Contr != 1);
	    
            form.DrugTurnoverGrid.getGrid().getStore().load({
                params: params,
		callback: function(){  
		    Ext.getCmp('DrugTurnoverGridItogo').getGrid().store.removeAll();
	    
		Cnt = Ext.getCmp('DrugTurnoverGrid').ViewGridPanel.getStore().data.items.length;
		
		var rec  = new Object();
		rec['RowNumber'] = 0;
		rec['Drug_Name'] = 'ИТОГО:';
		rec['BegOst_Sum'] = 0;
		rec['Pr_Sum'] = 0;
		rec['Ras_Sum'] = 0;
		rec['EndOst_Sum'] = 0;
		rec['type_rec'] = 0;
		for (var r = 0; r <= Cnt - 1; r++) {
		    record = Ext.getCmp('DrugTurnoverGrid').ViewGridPanel.getStore().data.items[r].data;
		    rec['BegOst_Sum'] += Number(record['BegOst_Sum']);
		    rec['Pr_Sum'] += Number(record['Pr_Sum']);
		    rec['Ras_Sum'] += Number(record['Ras_Sum']);
		    rec['EndOst_Sum'] += Number(record['EndOst_Sum']);
				}
				;
		    
		    Ext.getCmp('DrugTurnoverGridItogo').getGrid().store.insert(0, [new Ext.data.Record({
							    RowNumber: rec['RowNumber'],
							    Drug_Name: rec['Drug_Name'],
							    BegOst_Sum: rec['BegOst_Sum'].toFixed(2),
							    Pr_Sum: rec['Pr_Sum'].toFixed(2),
							    Ras_Sum: rec['Ras_Sum'].toFixed(2),
							    EndOst_Sum: rec['EndOst_Sum'].toFixed(2),
							    type_rec: rec['type_rec']
						    })]);
		}
            });
},
        doViewDetail: function() {
	    var form = this;
            var rowSelected = Ext.getCmp('DrugTurnoverGrid').getGrid().getSelectionModel().getSelected().data;
            console.log('DrugOstatRegistry_id = ' + rowSelected.DrugOstatRegistry_id);

            var  params = new Object();
            params.PeriodRange = Ext.getCmp('DrugTurnover_PeriodDate').value;
		params.Org_id = form.org_id;
            params.Lpu_id = rowSelected.Lpu_id;
            params.DrugShipment_id = rowSelected.DrugShipment_id;
            params.Drug_Name = rowSelected.Drug_Name;
            params.Drug_Code = rowSelected.Drug_Code;
            params.Kolvo = rowSelected.DrugOstatRegistry_Kolvo.trim();
            params.EndOst_Kol = rowSelected.EndOst_Kol.trim();
            params.WhsDocumentCostItemType_id = rowSelected.WhsDocumentCostItemType_id;
            params.DrugOstatRegistry_id = rowSelected.DrugOstatRegistry_id;
	    params.SubAccountType_id = rowSelected.SubAccountType_id;
		params.DrugOstatRegistry_id = rowSelected.DrugOstatRegistry_id;
		

            console.log('params = ');
            console.log(params);
            getWnd('swDrugTurnoverDetailWindow').show(params);
        },
     initComponent: function() {
        var form = this;
           
         this.SearchParamsPanel = new sw.Promed.BaseWorkPlaceFilterPanel({
            owner: form,
            labelWidth: 110,
            autoHeight: true,
            id: 'DrugTurnover_FilterPanel',
            filter: {
                title: lang['filtryi'],
                collapsed: true,
                id: 'DrugTurnover_rr',
                layout: 'form',
                items: [  
		    {layout: 'form',
				labelWidth: 150,
			items: [  
			{
				xtype: 'sworgcombo',
				fieldLabel : lang['organizatsiya'],
				hiddenName: 'Org_id',
				width: 505,
				disabled: false,
				allowBlank: true,
				editable: false,
				id: 'DrugTurnover_Org', 
				onTrigger1Click: function() {
					if(!this.disabled){
						var combo = this;
						var form = Ext.getCmp('DrugTurnoverListWindow');
						getWnd('swOrgSearchWindow').show({
							object: 'Org_Served',
							onSelect: function(orgData) {
								if ( orgData.Org_id > 0 ) {
									combo.getStore().load({
										params: {
											Object:'Org',
											Org_id: orgData.Org_id,
											Org_Name:''
										},
										callback: function() {
											combo.setValue(orgData.Org_id);
											combo.focus(true, 500);
											combo.fireEvent('change', combo);
											form.org_id = orgData.Org_id;
										}
									});
								}
								getWnd('swOrgSearchWindow').hide();
							},
											onClose: function () {
												combo.focus(true, 200)
											}
						});
					}
				},
				onTrigger2Click: function() {
					if(!this.disabled){
						this.setValue(null);
						this.loadStorageCombo();
					}
				}
			    }
			]},
		    {
			layout: 'column',
			labelWidth: 150,
			items: [
			    {layout: 'form',
			     items: [
			    {
				name : "DrugTurnover_PeriodDate",
				id: 'DrugTurnover_PeriodDate',
				xtype : "daterangefield",
				layout: 'form',
				allowBlank: false,
				width : 200,
				Height : 70,
				fieldLabel : lang['period'],
				plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
				//tabIndex : TABINDEX_LISTTASKFORMVAC + 1
			    }
			     ]
			 },
			 {  
			layout: 'form',
			labelWidth: 150,
			items: [
			    {
				autoLoad: false,
				width: 150,
				xtype:  'amm_SubAccountTypeCombo',
				id: 'DrugTurnover_SubAccountTypeCombo',
				//value: 1,
				emptyText: 'Все субсчета',
				allowEmpty: true ,
			    }]
			    }			
			]
		    },
                    {
                        layout: 'column',
						labelWidth: 150,
                        items: [
                            {layout: 'form',
                                 items: [
                                      {
                                        fieldLabel: lang['kod_lp'],
                                        width: 200,
                                        id: 'DrugTurnover_DrugCode',
                                        xtype: 'textfield'
                                    }
                                 ]
                            },
                            {layout: 'form',
                                labelWidth: 150,
                                items: [
                                     {
                                       fieldLabel: 'МНН',
                                       width: 465,
                                       id: 'DrugTurnover_MNNName',
                                       xtype: 'textfield'
                                   }
                                ]
                            }
                        ]
                    },
					{
                        layout: 'column',
						labelWidth: 150,
                        items: [
							{layout: 'form',
								hidden: getGlobalOptions().orgtype != 'farm',
								items: [
									{
										xtype: 'swdrugfinancecombo',
										fieldLabel: 'Ист. финансирования',
										name: 'DrugFinance_id',
										id: 'DrugTurnover_DrugFinance',
										width: 200
									}
								]
							},
							{layout: 'form',
                                labelWidth: getGlobalOptions().orgtype != 'farm' ? 505 : 150, //150, //265,
                                items: [
									{
                                       fieldLabel: lang['torg_naimenovanie'],
                                       width: 465,
                                       id: 'DrugTurnover_DrugName',
                                       xtype: 'textfield'
                                   }
                                ]
                            }
                        ]
                    },
                    {
                        layout: 'column',
						labelWidth: 150,
                        items: [
                            {layout: 'form',
                                 items: [
                                      {
                                        xtype: 'swwhsdocumentcostitemtypecombo',
                                        fieldLabel: lang['statya_rashoda'],
                                        name: 'WhsDocumentCostItemType_id',
                                        id: 'DrugTurnover_WhsDocumentCostItemType',
                                        width: 200,
										/*
										listeners: {
											expand: function(combo) {
												if (getGlobalOptions().region.nick == 'ufa' && getGlobalOptions().orgtype == 'farm') {
													var Whs_Type_Code = new Array();
													Whs_Type_Code = [1, 2, 3, 34, 114, 115, 116];
													combo.getStore().filterBy(function(rec) {
														if (rec.get('WhsDocumentCostItemType_Code').inlist(Whs_Type_Code) )
															return true;
														else
															return false;
													})
												};
											}
										}
										*/
                                    }
                                 ]
                            },
                            {layout: 'form',
                                labelWidth: 150,
                                 items: [
                                      {
                                        xtype: 'swstoragecombo',
                                        fieldLabel: lang['sklad'],
                                        width: 150,
                                        hiddenName: 'Storage_id',
                                        id: 'DrugTurnover_Storage',
										emptyText: 'Все склады',
										listWidth: 300
                                    }
                                 ]
                            },
                            {layout: 'form',
                                labelWidth: 200,
                                 items: [
                                {
						xtype: 'checkbox',
						//tabindex: TABINDEX_DORLW + 2,
						id: 'DrugTurnover_Differences',
						fieldLabel: lang['rashojdeniya_v_ostatkah']
					}
                                 ]
                            },
                            {
                                layout: 'form',
                                items: [{
                                        style: "padding-left: 25px",
                                        xtype: 'button',
                                        id: 'DrugTurnover_BtnClear',
                                        text: lang['sbros'],
                                        iconCls: 'reset16',
                                        tabIndex: TABINDEX_ONKOCTRLPROFILEJOURNAL + 6,
                                        handler: function() {
                                            //  Очищаем фильтр на панеле фильтров

                                            Ext.getCmp('DrugTurnover_DrugCode').reset();
                                            Ext.getCmp('DrugTurnover_DrugName').reset();
											Ext.getCmp('DrugTurnover_MNNName').reset();
                                            Ext.getCmp('DrugTurnover_WhsDocumentCostItemType').reset();
                                            Ext.getCmp('DrugTurnover_Storage').reset();

                                        }
                                    }]
                            }
                        ]
                    }

              
                ]
            }
         }),
            this.DrugTurnoverGrid = new sw.Promed.ViewFrame({
                    id: 'DrugTurnoverGrid',
                    title:'',
                    dataUrl: '/?c=RegistryRecept&m=loadDrugTurnoverList',
                    autoLoadData: false,
                    // selectionModel: 'multiselect',
                    region: 'center',
                    toolbar: true,
                    cls: 'txtwrap',
                    root: 'data',
                    stringfields:
                    [
                            {name: 'RowNumber', type: 'int', header: 'ID', key: true, hidden: true},
                            {name: 'DrugOstatRegistry_id', type: 'int', header: 'DrugOstatRegistry_id', hidden: true},
                            {name: 'Lpu_id', header: 'Lpu_id', width: 80,  hidden: true },
                            {name: 'Drug_Code', header: lang['kod_lp'], width: 100},
                            {name: 'Drug_id', hidden: true},
                            {name: 'Drug_Name', header: lang['torgovoe_naim'], width: 80, id: 'autoexpand' }, 
							{name: 'DrugMNN_Name', header: 'МНН', width: 80}, 
			    			{name: 'SubAccountType_id', header: 'SubAccountType_id', width: 80,  hidden: true },
							{name: 'SubAccountType_Name', header: langs('Тип субсчета'), width: 80},
							{name: 'GoodsUnit_Name', header: langs('единицы<br>измерения'), width: 80, hidden: getGlobalOptions().orgtype != 'lpu'},
							{name: 'DocumentUcStr_Price', header: langs('Цена (руб.)'), align: 'right', width: 80},
							{name: 'BegOst_Kol', header: langs('Остаток<br>на начало<br>периода'), align: 'right', width: 80},
							{name: 'BegOst_Sum', header: langs('Сумма<br>на начало<br>периода<br>(руб.)'), align: 'right', width: 80},
							{name: 'Pr_Kol', header: langs('Приход<br>за период'), align: 'right', width: 80},
							{name: 'Pr_Sum', header: langs('Сумма<br>прихода<br>за период<br>(руб.)'), align: 'right', width: 80},
							{name: 'Ras_Kol', header: langs('Расход<br>за период'), align: 'right', width: 80},
							{name: 'Ras_Sum', header: langs('Сумма<br>расхода<br>за период<br>(руб.)'), align: 'right', width: 80},
							{name: 'EndOst_Kol', header: langs('Остаток<br>на конец<br>периода'), align: 'right', width: 80},
							{name: 'EndOst_Sum', header: langs('Сумма<br>на конец<br>периода<br>(руб.)'), align: 'right', width: 80},
							{name: 'DrugOstatRegistry_Kolvo', header: langs('Числится<br>на складе'), align: 'right', width: 80},
							{name: 'DrugFinance_id', hidden: true},
                            {name: 'DrugFinance_name', header: 'Источник<br>финансирования', width: 120, hidden: getGlobalOptions().orgtype != 'farm'},
                            {name: 'WhsDocumentCostItemType_id', hidden: true},
                            {name: 'WhsDocumentCostItemType_Name', header: lang['statya_rashoda'], width: 120},
                            {name: 'DrugShipment_id', hidden: true},
                            {name: 'DrugShipment_Name', header: lang['partiya'], width: 120},  //Storage_id, Storage_Name 
			    			{name: 'DocumentUcStr_GodnDate', type: 'date', header: 'Срок<br>годности', width: 80, sortable: true}, 
			    			{name: 'DocumentUcStr_Ser', type: 'string', header: 'Серия', width: 80},
                            {name: 'Storage_id', header: 'Storage_id', hidden: true },
                            {name: 'Storage_Name', header: lang['sklad'], width: 120},
                            {name: 'EndOst_Contr', type: 'int', hidden: true, sortable: false},
			    			{name: 'type_rec',  header: 'type_rec', hidden: true, sortable: false}
                    ],
                    actions:
			[
                            {name:'action_add',  hidden: true},
                            {name:'action_edit',  hidden: true},
                            {name:'action_delete',  hidden: true},
                            {name:'action_save',  hidden: true},
								{name: 'action_refresh', handler: function () {
										Ext.getCmp('DrugTurnoverListWindow').doSearch();
									}},
                            {name:'action_view', 
                                handler: function() {
                                    Ext.getCmp('DrugTurnoverListWindow').doViewDetail();
                            }}
                        ],
                    onDblClick: function(e) {
                        Ext.getCmp('DrugTurnoverListWindow').doViewDetail();
                    },
            });
            
            this.DrugTurnoverGrid.getGrid().view = new Ext.grid.GridView({
			getRowClass : function (row, index) {
                var cls = '';
		
		if (row.get('SubAccountType_id') == 3) {//  ЛС в пути
			cls = cls+' x-grid-rowgray '; 
		    }
		    if (form.rec_Contr == 1) {
			if (row.get('EndOst_Contr') >= 1 && row.get('type_rec') == 2) {
			    cls = cls+'x-grid-rowbackyellow';
			    if (row.get('EndOst_Contr') == 2) 
				cls = cls+' x-grid-rowred';
			}
		    }
           
                return cls;
			}
		});
		
		Ext.getCmp('DrugTurnoverGrid').getGrid().on(
			'columnresize', function( columnIndex, newSize) {
				console.log('columnIndex',columnIndex, 'newSize', newSize);
				Ext.getCmp('DrugTurnoverGridItogo').getColumnModel().setColumnWidth( columnIndex, newSize);
		}
		);
                
             this.DrugTurnoverGridItogo = new sw.Promed.ViewFrame({
                    id: 'DrugTurnoverGridItogo',
                    title:'',
                    dataUrl: '/?c=RegistryRecept&m=loadDrugTurnoverList',
                    autoLoadData: false,
		    height: 40,
                    region: 'south',
                    root: 'data',
		    contextmenu: false,
		    toolbar: false,
		    hideHeaders: true,
                    stringfields:
                    [
							{name: 'RowNumber', type: 'int', header: 'ID', key: true, hidden: true},
                            {name: 'DrugOstatRegistry_id', type: 'int', header: 'DrugOstatRegistry_id', hidden: true},
                            {name: 'Lpu_id', header: 'Lpu_id', width: 80,  hidden: true },
                            {name: 'Drug_Code', header: lang['kod_lp'], width: 100},
                            {name: 'Drug_id', hidden: true},
                            {name: 'Drug_Name', header: lang['torgovoe_naim'], width: 80, id: 'autoexpand' }, 
							{name: 'DrugMNN_Name', header: 'МНН', width: 80}, 
			    			{name: 'SubAccountType_id', header: 'SubAccountType_id', width: 80,  hidden: true },
			    			{name: 'SubAccountType_Name', header: lang['tip_subscheta'], width: 80},
                            {name: 'DocumentUcStr_Price', header: lang['tsena_rub'], align: 'right',width: 80},
                            {name: 'BegOst_Kol', header: lang['ostatokna_nachaloperioda'], align: 'right',width: 80},
                            {name: 'BegOst_Sum', header: lang['summana_nachaloperioda_rub'], align: 'right',width: 80},
                            {name: 'Pr_Kol', header: lang['prihodza_period'], align: 'right',width: 80},
                            {name: 'Pr_Sum', header: lang['summaprihodaza_period_rub'], align: 'right',width: 80},
                            {name: 'Ras_Kol', header: lang['rashodza_period'], align: 'right',width: 80},
                            {name: 'Ras_Sum', header: lang['summarashodaza_period_rub'], align: 'right',width: 80},
                            {name: 'EndOst_Kol', header: lang['ostatokna_konetsperioda'], align: 'right',width: 80},
                            {name: 'EndOst_Sum', header: lang['summana_konetsperioda_rub'], align: 'right',width: 80},
                            {name: 'DrugOstatRegistry_Kolvo', header: lang['chislitsyana_sklade'], align: 'right',width: 80},
							{name: 'DrugFinance_id', hidden: true},
                            {name: 'DrugFinance_name', header: 'Источник<br>финансирования', width: 120, hidden: getGlobalOptions().orgtype != 'farm'},
                            {name: 'WhsDocumentCostItemType_id', hidden: true},
                            {name: 'WhsDocumentCostItemType_Name', header: lang['statya_rashoda'], width: 120},
                            {name: 'DrugShipment_id', hidden: true},
                            {name: 'DrugShipment_Name', header: lang['partiya'], width: 120},  //Storage_id, Storage_Name 
			    			{name: 'DocumentUcStr_GodnDate', type: 'date', header: 'Срок<br>годности', width: 80, sortable: true}, 
			    			{name: 'DocumentUcStr_Ser', type: 'string', header: 'Серия', width: 80},
                            {name: 'Storage_id', header: 'Storage_id', hidden: true },
                            {name: 'Storage_Name', header: lang['sklad'], width: 120},
                            {name: 'EndOst_Contr', type: 'int', hidden: true, sortable: false},
			    			{name: 'type_rec',  header: 'type_rec', hidden: true, sortable: false}
                    ]
            });
	    
	    Ext.getCmp('DrugTurnoverGridItogo').getGrid().on('render', function() {
			Ext.getCmp('DrugTurnoverGrid').getGrid().getView().scroller.dom.style.overflowX = 'hidden';
		});
		
		Ext.getCmp('DrugTurnoverGridItogo').getGrid().on(
			'bodyscroll', function( scrollLeft, scrollTop) {
				Ext.getCmp('DrugTurnoverGrid').getGrid().getView().scroller.dom.scrollLeft = scrollLeft;
		}
		);
            Ext.apply(this, {
                 lbodyBorder: true,
                 layout: "border",
                  cls: 'tg-label',
			items: 
			[ 
                            form.SearchParamsPanel,
			     form.SearchParamsPanel,
			    {
                                layout: 'border',
				region: 'center', 
				items: [
					 form.DrugTurnoverGrid
					, form.DrugTurnoverGridItogo
				]
			    }
			],
                                buttons: [
               {
                        text: lang['sformirovat'],
                        iconCls: 'pill16',
                        id: 'DrugTurnoverForm',
                        disabled: false,
				handler: function() {
				    Ext.getCmp('DrugTurnoverListWindow').doSearch();

                                }
                        },
                {
                    text: '-'
                },
                HelpButton(this, TABINDEX_ONKOCTRLPROFILEJOURNAL + 8),
                {
                    handler: function() {
                        this.hide();
                    }.createDelegate(this),
                    iconCls: 'close16',
                    id: 'DrugTurnover_CancelButton',
                    text: lang['zakryit']
                }
            ]
            });      
            
                
         sw.Promed.swDrugTurnoverListWindow.superclass.initComponent.apply(this, arguments);
     },
      show: function() {
		sw.Promed.swDrugTurnoverListWindow.superclass.show.apply(this, arguments);
                var form = this;
                var d1 = new Date(2016, 0, 1);  
                var d2 = new Date();  
		var date = new Date(getGlobalOptions().date.replace(/(\d+).(\d+).(\d+)/, '$3/$2/$1'));
		if (arguments[0] && arguments[0].LpuSection_id) {
			form.lpuSection_id = arguments[0].LpuSection_id;
		}
		
	       d1 = new Date(d2.getFullYear(), d2.getMonth(), 1);
	       
		Ext.getCmp('DrugTurnover_PeriodDate').setValue(d1.format('d.m.Y') + ' - ' + d2.format('d.m.Y'));
		var params = new Object();
		params.Org_id = form.org_id;
		if (getGlobalOptions().orgtype == 'lpu')
			Ext.getCmp('DrugTurnover_Storage').getStore().baseParams.LpuSection_id = form.lpuSection_id;
		else
			Ext.getCmp('DrugTurnover_Storage').getStore().baseParams.Org_id = form.org_id;
		
		Ext.getCmp('DrugTurnover_Storage').getStore().proxy.conn.url = '/?c=DocumentUc&m=loadStorageList';
		Ext.getCmp('DrugTurnover_Storage').getStore().load();   
		if (getGlobalOptions().groups.toString().indexOf('AdminLLO') == -1) {
		    Ext.getCmp('DrugTurnover_Org').ownerCt.hide();
		}
      }          

 });

