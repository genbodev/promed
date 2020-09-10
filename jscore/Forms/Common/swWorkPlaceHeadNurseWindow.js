/**
* АРМ Главной медсестры МО
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @autor		
* @copyright    Copyright (c) 2015 Swan Ltd.
*/
sw.Promed.swWorkPlaceHeadNurseWindow = Ext.extend(sw.Promed.swWorkPlaceWindow, {
	id: 'swWorkPlaceHeadNurseWindow',
	withOutFP:true,
	showToolbar:false,
	show: function() {
		var wnd = this;

		sw.Promed.swWorkPlaceHeadNurseWindow.superclass.show.apply(this, arguments);

		this.userMedStaffFact = arguments[0];

		
	},

	doSearch: function(mode) {
		var detail_grid = Ext.getCmp('DrugOstatBySkladGridDetail');
		var mnn_filter = Ext.getCmp('DOBSVW_MnnFilter').getValue();
		var torg_filter = Ext.getCmp('DOBSVW_TorgFilter').getValue();
		detail_grid.getStore().removeAll();
		detail_grid.getStore().load({
			params: {
				OrgFarmacy_id: 1,
				mnn: mnn_filter,
				torg: torg_filter
			},
			callback: function()
			{
				Ext.getCmp('DOBSVW_DrugGridCounter').setText('0 / ' + detail_grid.getStore().getCount());
			}
		});
	},
	
	initComponent: function() {
		var form = this;
		this.buttonPanelActions= {
			action_drugrequestview: {
				text: lang['zayavka_na_lekarstvennyie_sredstva_prosmotr'],
				tooltip: lang['zayavka_na_lekarstvennyie_sredstva'],
				iconCls: 'mp-drugrequest32',
				handler: function() {
					getWnd('swMzDrugRequestSelectWindow').show();
					//getWnd('swMzDrugRequestMoViewWindow').show();
				}
			},
			action_goscontracts:{
				// nn: 'action_SwhDocumentSupply',
				tooltip: lang['goskontraktyi'],
				text: lang['goskontraktyi'],
				iconCls : 'card-state32',
				disabled: false,
                menuAlign: 'tr',
                menu: new Ext.menu.Menu({
                    items: [{
                        tooltip: lang['goskontraktyi_na_postavku'],
                        text: lang['goskontraktyi_na_postavku'],
                        iconCls : 'document16',
                        handler: function(){
                            getWnd('swWhsDocumentSupplyViewWindow').show({ARMType: this.userMedStaffFact.ARMType});
                        }.createDelegate(this)
                    }, {
                        tooltip: lang['dopolnitelnyie_soglasheniya'],
                        text: lang['dopolnitelnyie_soglasheniya'],
                        iconCls : 'document16',
                        handler: function(){
                            getWnd('swWhsDocumentSupplyAdditionalViewWindow').show({ARMType: this.userMedStaffFact.ARMType});
                        }.createDelegate(this)
                    }]
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
					items: [/*{
						tooltip: lang['falsifikatyi'],
						text: lang['falsifikatyi'],
						iconCls: 'staff16',
						handler: function() {}
					}, */{
						tooltip: getRLSTitle(),
						text: getRLSTitle(),
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
					},
					sw.Promed.Actions.swDrugDocumentSprAction,
					{
						name: 'action_DrugNomenSpr',
						text: lang['nomenklaturnyiy_spravochnik'],
						iconCls : '',
						handler: function()
						{
							getWnd('swDrugNomenSprWindow').show({readOnly: false});
						}
					}, {
						name: 'action_DrugMnnCodeSpr',
						text: lang['spravochnik_mnn'],
						iconCls : '',
						handler: function()
						{
							getWnd('swDrugMnnCodeViewWindow').show({readOnly: false});
						}
					}, {
						name: 'action_DrugTorgCodeSpr',
						text: lang['spravochnik_torgovyih_naimenovaniy'],
						iconCls : '',
						handler: function()
						{
							getWnd('swDrugTorgCodeViewWindow').show({readOnly: false});
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
					sw.Promed.Actions.swPrepBlockSprAction
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
			}
		};
		this.onKeyDown = function (inp, e) {
			if (e.getKey() == Ext.EventObject.ENTER) {
				e.stopEvent();
				this.doSearch();
			}
		}.createDelegate(this);
		/*this.FilterPanel = new sw.Promed.BaseWorkPlaceFilterPanel({
			owner: form, 
			filter: {
				title: lang['filtr'],
				layout: 'form',				
				items: [{
					layout: 'column',
					labelWidth: 139,
					items: [{
						layout: 'form',
						items: [{
							xtype: 'swcommonsprcombo',
							width: 280,
							name: 'DrugDocumentType_id',
							comboSubject: 'DrugDocumentType',
							typeCode: 'int',
							fieldLabel: lang['tip_dokumenta'],
							value: '',
							listeners: {'keydown': form.onKeyDown}
						}]
					}, {
						layout: 'form',
						items: [{
							xtype: 'textfieldpmw',
							width: 123,
							id: 'wpmwSearch_Num',
							name: 'DocumentUc_Num',
							fieldLabel: lang['nomer_dokumenta'],
							value: '',
							listeners: {'keydown': form.onKeyDown}
						}]
					}, {
						layout: 'form',
						items: [{
							xtype: 'textfield',
							width: 123,
							name: 'WhsDocumentUc_Num',
							fieldLabel: lang['nomer_kontrakta'],
							value: '',
							listeners: {'keydown': form.onKeyDown}
						}]
					}]
				}, {
					layout: 'column',
					labelWidth: 139,
					items: [{
						layout: 'form',
						items: [{
							xtype: 'swdrugfinancecombo',
							width: 280,
							name: 'DrugFinance_id',
							fieldLabel: lang['ist_finansirovaniya'],
							value: '',
							listeners: {'keydown': form.onKeyDown}
						}]
					}, {
						layout: 'form',
						items: [{
							xtype: 'swcontragentcombo',
							disabled: false,
							width: 390,
							id: 'wpmwContragent_sid',
							name: 'Contragent_sid',
							hiddenName:'Contragent_sid',
							fieldLabel: lang['postavschik'],
							listeners: {'keydown': form.onKeyDown}
						}]
					}]
				}, {
					layout: 'column',
					labelWidth: 139,
					items: [{
						layout: 'form',
						items: [{
							xtype: 'swwhsdocumentcostitemtypecombo',
							width: 280,
							name: 'WhsDocumentCostItemType_id',
							fieldLabel: lang['statya_rashoda'],
							value: '',
							listeners: {'keydown': form.onKeyDown}
						}]
					}, {
						layout: 'form',
						items: [{
							xtype: 'swcontragentcombo',
							disabled: false,
							width: 390,
							id: 'wpmwContragent_tid',
							name: 'Contragent_tid',
							hiddenName:'Contragent_tid',
							fieldLabel: lang['poluchatel'],
							listeners: {'keydown': form.onKeyDown}						
						}]
					}]
				}, {
					layout: 'column',
					labelWidth: 139,
					items: [{
						layout: 'form',
						items: [{
							style: "padding-left: 144px",
							xtype: 'button',
							id: form.id+'BtnSearch',
							text: lang['nayti'],
							iconCls: 'search16',
							handler: function()	{form.doSearch();}.createDelegate(form)
						}]
					}, {
						layout: 'form',
						items: [{
							style: "padding-left: 10px",
							xtype: 'button',
							id: form.id+'BtnClear',
							text: lang['sbros'],
							iconCls: 'clear16',
							handler: function() {form.doReset();}.createDelegate(form)
						}]
					}]
				}]
			}
		});*/
		this.GridPanel = new Ext.grid.GridPanel({
					region: 'center',
					id: 'DrugOstatBySkladGridDetail',
					autoExpandColumn: 'autoexpand',
					autoExpandMax: 2000,
					loadMask: true,
					title: lang['medikament'],
					stripeRows: true,
					tbar: new sw.Promed.Toolbar({
						autoHeight: true,
						items: [{
							xtype: 'label',
							text: lang['mnn'],
							style: 'margin-left: 5px; font-weight: bold'
						},{
							xtype: 'textfield',
							id: 'DOBSVW_MnnFilter',
							style: 'margin-left: 5px',
							width: 120,
							enableKeyEvents: true,
							listeners: {
								'keydown': function (inp, e) {
									if (e.getKey() == Ext.EventObject.ENTER)
									{
										e.stopEvent();
										inp.ownerCt.items.item('DOBSVW_FindAction').handler();
									}
									if (e.shiftKey == false && e.getKey() == Ext.EventObject.TAB)
									{
										e.stopEvent();
										inp.ownerCt.items.item('DOBSVW_TorgFilter').focus();
									}
									if (e.shiftKey == true && e.getKey() == Ext.EventObject.TAB)
									{
										e.stopEvent();
										Ext.getCmp('DOBSVW_OrgFarmFilter').focus();
									}
								}
							}
						},{
							xtype: 'label',
							text: lang['torg_naim'],
							style: 'margin-left: 5px; font-weight: bold'
						},{
							xtype: 'textfield',
							id: 'DOBSVW_TorgFilter',
							style: 'margin-left: 5px',
							width: 120,
							enableKeyEvents: true,
							listeners: {
								'keydown': function (inp, e) {
									if (e.getKey() == Ext.EventObject.ENTER)
									{
										e.stopEvent();
										inp.ownerCt.items.item('DOBSVW_FindAction').handler();
									}
									if (e.shiftKey == false && e.getKey() == Ext.EventObject.TAB)
									{
										e.stopEvent();
										inp.ownerCt.items.item('DOBSVW_FindAction').focus();
									}
									if (e.shiftKey == true && e.getKey() == Ext.EventObject.TAB)
									{
										e.stopEvent();
										Ext.getCmp('DOBSVW_MnnFilter').focus();
									}
								}
							}
						}, {
							xtype: 'button',
							style: 'margin-left: 5px',
							text: BTN_FRMFILTER,
							iconCls: 'search16',
							id: 'DOBSVW_FindAction',
							handler: function() {
								Ext.getCmp('DrugOstatBySkladViewWindow').loadDrugOstatGrid();
							},
							onTabElement: 'DOBSVW_ResetFilterButton',
							onShiftTabElement: 'DOBSVW_TorgFilter'
						}, {
							xtype: 'button',
							iconCls: 'resetsearch16',
							id: 'DOBSVW_ResetFilterButton',
							style: 'margin-left: 5px',
							text: BTN_FRMRESET,
							handler: function() {
								this.ownerCt.items.item('DOBSVW_MnnFilter').setValue('');
								this.ownerCt.items.item('DOBSVW_TorgFilter').setValue('');
								this.ownerCt.items.item('DOBSVW_FindAction').handler();
							},
							onTabAction: function(field){
								var grid = Ext.getCmp('DrugOstatBySkladGridDetail');
								if ( grid.getStore().getCount() > 0 )
								{
									grid.getSelectionModel().selectFirstRow();
									grid.getView().focusRow(0);
								}
								else
								{
									Ext.getCmp('DOBSVW_CloseButton').focus();
								}
							},
							onShiftTabElement: 'DOBSVW_FindAction'
						}, {
							xtype: 'tbfill'
						}, {
							id: 'DOBSVW_DrugGridCounter',
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

							var current_window = Ext.getCmp('DrugOstatBySkladViewWindow');
							var drug_grid = current_window.findById('DrugOstatBySkladGridDetail');
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
									Ext.getCmp('DOBSVW_OstatPrintButton').handler();
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
									Ext.getCmp('DOBSVW_CloseButton').focus();
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
							'DrugOstat_Fed',
							'DrugOstat_Reg',
							'DrugOstat_7Noz'
						],
						listeners: {
							'load': function(store) {
								var grid = Ext.getCmp('DrugOstatBySkladGridDetail');
								Ext.getCmp('DOBSVW_DrugGridCounter').setText('0 / ' + store.getCount());
							}
						}
					}),
					columns: [
						{dataIndex: 'DrugOstat_id', hidden: true, hideable: false},
						{dataIndex: 'OrgFarmacy_id', hidden: true, hideable: false},
						{dataIndex: 'Drug_id', hidden: true, hideable: false},
						{header: lang['mnn'], dataIndex: 'DrugMnn_Name', sortable: true, width: 450},
						{header: lang['kod_ges'], dataIndex: 'Drug_CodeG', sortable: true, width: 120},
						{id: 'autoexpand', header: lang['torgovoe_naimenovanie'], dataIndex: 'Drug_Name', sortable: true},
						{header: lang['ostatki_fed'], dataIndex: 'DrugOstat_Fed', /*renderer: sw.Promed.Format.checkColumnForRas,*/ sortable: true, width: 80},
						{header: lang['ostatki_reg'], dataIndex: 'DrugOstat_Reg', /*renderer: sw.Promed.Format.checkColumnForRas,*/ sortable: true, width: 80},
						{header: lang['ostatki_7_noz'], dataIndex: 'DrugOstat_7Noz', /*renderer: sw.Promed.Format.checkColumnForRas,*/ sortable: true, width: 80}
					],
					sm: new Ext.grid.RowSelectionModel({
						singleSelect: true,
						listeners: {
							'rowselect': function(sm, rowIdx, r) {
								Ext.getCmp('DOBSVW_DrugGridCounter').setText((rowIdx + 1) + ' / ' + this.grid.getStore().getCount());
							}
						}
					})
				});
		
		sw.Promed.swWorkPlaceHeadNurseWindow.superclass.initComponent.apply(this, arguments);
	}
});