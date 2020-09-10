/**
* АРМ заведующего аптекой МО
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @autor		Alaxander Kurakin
* @copyright    Copyright (c) 2016 Swan Ltd.
* @version      09.2016
*/
sw.Promed.swWorkPlaceLpuPharmacyHeadWindow = Ext.extend(sw.Promed.swWorkPlaceWindow,
{
	id: 'swWorkPlaceLpuPharmacyHeadWindow',
	showToolbar: false,
	show: function()
	{
		sw.Promed.swWorkPlaceLpuPharmacyHeadWindow.superclass.show.apply(this, arguments);
		if(arguments[0].userMedStaffFact){
			this.userMedStaffFact = arguments[0].userMedStaffFact;
		} else {
			this.userMedStaffFact = arguments[0];
		}

		var base_form = this.FilterPanel.getForm();

		this.coordinator_combo.getStore().each(function(rec){
			if(rec.get('Coordinator_id') == 1){
				rec.data.Coordinator_Name = getGlobalOptions().lpu_nick;
			}
		});

		var val = (new Date()).getFullYear();
		Ext.getCmp('mdrrvYear').setValue(val);
	},
    doSearch: function() {
        var base_form = this.FilterPanel.getForm();
        var gridPanel = Ext.getCmp('WorkPlaceLpuPharmacyHeadGridPanel');

        var params = base_form.getValues();
        var year = Ext.getCmp('mdrrvYear').getValue();
        if(Ext.isEmpty(year)){
        	year = (new Date()).getFullYear();
        }
        params.Year = year;
        params.fromLpuPharmacyHead = 1;
        params.DrugRequestProperty_Org_id = getGlobalOptions().org_id;
        gridPanel.getGrid().getStore().baseParams = params;
        
        gridPanel.getGrid().getStore().load();
    },
    changeYear: function(value) {
		var val = Ext.getCmp('mdrrvYear').getValue();
		if (!val || value == 0)
			val = (new Date()).getFullYear();
		Ext.getCmp('mdrrvYear').setValue(val+value);
	},
	buttonPanelActions: {
		action_Action2: {
			nn: 'action_Action2',
			tooltip: lang['zayavki_na_zakup_medikamentov'],
			text: lang['zayavki_na_zakup_medikamentov'],
			iconCls : 'mp-drugrequest32',
			disabled: false,
			menuAlign: 'tr',
			menu: new Ext.menu.Menu({
				items: [{
					tooltip: lang['rabochie_periodyi'],
					text: lang['rabochie_periodyi'],
					iconCls : 'datepicker-day16',
					handler: function(){
						getWnd('swDrugRequestPeriodViewWindow').show();
					}.createDelegate(this)
				}, {
					text: lang['normativnyie_perechni_lekarstvennyih_sredstv'],
					tooltip: lang['spravochnik_normativnyih_perechney_lekarstvennyih_sredstv'],
					iconCls : 'drug-name16',
					handler: function() {
						getWnd('swDrugNormativeListViewWindow').show();
					}
				}, {
					text: lang['spiski_medikamentov_dlya_zayavki'],
					tooltip: lang['spiski_medikamentov_dlya_zayavki'],
					iconCls : 'pill16',
					handler: function() {
						getWnd('swDrugRequestPropertyViewWindow').show();
					}
				}, {
					text: lang['zayavochnyie_kampanii'],
					tooltip: lang['zayavochnyie_kampanii'],
					iconCls : 'pers-curehist16',
					handler: function() {
						getWnd('swMzDrugRequestRegionViewWindow').show({ARMType: 'minzdravdlo'});
					}
				}, {
					text: lang['svodnaya_zayavka'],
					tooltip: lang['svodnaya_zayavka_prosmotr_utverjdenie'],
					iconCls : 'otd-profile16',
					handler: function() {
						getWnd('swConsolidatedDrugRequestViewWindow').show();
					}
				}, {
					tooltip: lang['formirovanie_lotov'],
					text: lang['formirovanie_lotov'],
					iconCls : 'settings16',					
					handler: function(){
						getWnd('swUnitOfTradingViewWindow').show({
                            disableAdd: true,
                            disableEdit: true
                        });
					}
				}]
			})
		},
		action_SwhDocumentSupply: {
			nn: 'action_SwhDocumentSupply',
			tooltip: lang['goskontraktyi'],
			text: lang['goskontraktyi'],
			iconCls : 'card-state32',
			menuAlign: 'tr',
			menu: new Ext.menu.Menu({
				items: [{
					tooltip: lang['goskontraktyi_na_postavku'],
					text: lang['goskontraktyi_na_postavku'],
					iconCls : 'document16',
					handler: function(){
						getWnd('swWhsDocumentSupplyViewWindow').show({ARMType: 'lpupharmacyhead'});
					}
				}, {
					tooltip: lang['dopolnitelnyie_soglasheniya'],
					text: lang['dopolnitelnyie_soglasheniya'],
					iconCls : 'document16',
					handler: function(){
						getWnd('swWhsDocumentSupplyAdditionalViewWindow').show({ARMType: 'lpupharmacyhead'});
					}
				}]
			})
		},
		action_CommercialOffer: {
			nn: 'action_CommercialOffer',
			text: lang['kommercheskoe_predlojenie'],
			tooltip: lang['kommercheskoe_predlojenie'],
			iconCls: 'pill16',
			handler: function() {
				getWnd('swCommercialOfferViewWindow').show();
			}
		},
		action_DrugOstatRegistryList: {
			nn: 'action_DrugOstatRegistryList',
			tooltip: lang['prosmotr_registra_ostatkov'],
			text: lang['prosmotr_registra_ostatkov'],
			iconCls : 'pers-cards32',
			menuAlign: 'tr?',
			menu: new Ext.menu.Menu({
				items: [{
					text: lang['prosmotr_ostatkov_po_gk'],
					tooltip: lang['prosmotr_ostatkov_po_gk'],
					iconCls: 'pill16',
					handler: function() {
						getWnd('swDrugOstatRegistryListWindow').show();
					}
				}, {
					text: lang['prosmotr_ostatkov_organizatsii_polzovatelya'],
					tooltip: lang['prosmotr_ostatkov_organizatsii_polzovatelya'],
					iconCls: 'pill16',
					handler: function() {
						getWnd('swDrugOstatRegistryListWindow').show({
                            mode: 'suppliers',
                            userMedStaffFact: this.userMedStaffFact
                        });
                    }.createDelegate(this)
				}]
			})
		},
		action_Contragent: {			
			nn: 'action_Contragent',
			tooltip: lang['spravochnik_kontragentyi'],
			text: lang['spravochnik_kontragentyi'],
			iconCls : 'org32',
			disabled: false, 			
			handler: function() {
				getWnd('swContragentViewWindow').show({
					ARMType: 'dpoint'
				});
			}.createDelegate(this)
		},
		action_Spr: {
			nn: 'action_Spr',
			tooltip: lang['spravochniki'],
			text: lang['spravochniki'],
			iconCls : 'book32',
			disabled: false,
			menuAlign: 'tr',
			menu: new Ext.menu.Menu({
				items: [
				{
					text: getRLSTitle(),
					tooltip: getRLSTitle(),
					iconCls: 'rls16',
					handler: function()
					{
						getWnd('swRlsViewForm').show();
					}
				},
				{
					text: lang['mkb-10'],
					tooltip: lang['spravochnik_mkb-10'],
					iconCls: 'spr-mkb16',
					handler: function()
					{
						if ( !getWnd('swMkb10SearchWindow').isVisible() )
						getWnd('swMkb10SearchWindow').show();
					}
				},
				{
					text: getMESAlias(),
					tooltip: lang['spravochnik'] + getMESAlias(),
					iconCls: 'spr-mes16',
					handler: function()
					{
						getWnd('swMesOldSearchWindow').show();
					}
				},
				{
					text: lang['spravochniki_sistemyi_ucheta_medikamentov'],
					tooltip: lang['spravochniki_sistemyi_ucheta_medikamentov'],
					iconCls: '',
					handler: function()
					{
						getWnd('swDrugDocumentSprWindow').show({ARMType: this.ARMType});
					}.createDelegate(this)
				},
				{
					name: 'action_DrugNomenSpr',
					text: lang['nomenklaturnyiy_spravochnik'],
					tooltip: lang['nomenklaturnyiy_spravochnik'],
					iconCls : '',
					handler: function()
					{
						getWnd('swDrugNomenSprWindow').show({readOnly: false});
					}
				},
				{
					name: 'action_DrugMnnCodeSpr',
					text: lang['spravochnik_mnn'],
					tooltip: lang['spravochnik_mnn'],
					iconCls : '',
					handler: function()
					{
						getWnd('swDrugMnnCodeViewWindow').show({readOnly: false});
					}
				},
				{
					name: 'action_DrugTorgCodeSpr',
					text: lang['spravochnik_torgovyih_naimenovaniy'],
					tooltip: lang['spravochnik_torgovyih_naimenovaniy'],
					iconCls : '',
					handler: function()
					{
						getWnd('swDrugTorgCodeViewWindow').show({readOnly: false});
					}
				},
				{
					name: 'action_PriceJNVLP',
					hidden: getRegionNick().inlist(['by']),
					text: lang['tsenyi_na_jnvlp'],
					iconCls : 'dlo16',
					handler: function() {
						getWnd('swJNVLPPriceViewWindow').show();
					}
				},
				{
					name: 'action_DrugMarkup',
					hidden: getRegionNick().inlist(['by']),
					text: lang['predelnyie_nadbavki_na_jnvlp'],
					iconCls : 'lpu-finans16',
					handler: function() {
						getWnd('swDrugMarkupViewWindow').show();
					}
				},
				{
					text: lang['spravochnik_falsifikatov_i_zabrakovannyih_seriy_ls'],
					tooltip: lang['spravochnik_falsifikatov_i_zabrakovannyih_seriy_ls'],
					handler: function()
					{
						getWnd('swPrepBlockViewWindow').show();
					}
				},
				{
					text: lang['edinitsyi_izmereniya_tovara'],
					tooltip: lang['edinitsyi_izmereniya_tovara'],
					handler: function() {
						getWnd('swGoodsUnitViewWindow').show({allowImportFromRls: true});
					}
				}
				]
			})
		},
		action_WhsDocumentUcInvent: {
			nn: 'action_WhsDocumentUcInvent',
			tooltip: lang['inventarizatsiya'],
			text: lang['inventarizatsiya'],
			iconCls : 'invent32',
			disabled: false,
			menuAlign: 'tr?',
			menu: new Ext.menu.Menu({
				items: [{
					tooltip: lang['prikazyi_na_provedenie_inventarizatsii'],
					text: lang['prikazyi_na_provedenie_inventarizatsii'],
					iconCls : 'document16',
					handler: function() {
						getWnd('swWhsDocumentUcInventOrderViewWindow').show({
							ARMType: 'lpupharmacyhead'
						});
					}
				}, {
					tooltip: lang['inventarizatsionnyie_vedomosti'],
					text: lang['inventarizatsionnyie_vedomosti'],
					iconCls : 'document16',
					disabled: false,
					handler: function() {
                        var wnd = getWnd('swWorkPlaceLpuPharmacyHeadWindow');
						getWnd('swWhsDocumentUcInventViewWindow').show({
							ARMType: 'lpupharmacyhead',
                            MedService_id: wnd.userMedStaffFact.MedService_id,
                            Lpu_id: wnd.userMedStaffFact.Lpu_id,
                            LpuSection_id: wnd.userMedStaffFact.LpuSection_id,
                            LpuBuilding_id: wnd.userMedStaffFact.LpuBuilding_id
						});
                    }
				}]
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
	},
	initComponent: function() {
		var form = this;
		
		var WindowToolbarYear = new Ext.Toolbar({
			items: [{					
					xtype: 'button',
					disabled: true,
					text: lang['god']
				}, {
					text: null,
					xtype: 'button',
					iconCls: 'arrow-previous16',
					handler: function() {						
						form.changeYear(-1);
						form.doSearch();
					}.createDelegate(this)
				}, {
					xtype : "tbseparator"
				}, {
					xtype : 'numberfield',
					id: 'mdrrvYear',
					allowDecimal: false,
					allowNegtiv: false,
					width: 35,
					enableKeyEvents: true,
					listeners: {
						'keydown': function (inp, e) {
							if (e.getKey() == Ext.EventObject.ENTER) {
								e.stopEvent();
								form.doSearch();
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
						form.changeYear(1);
						form.doSearch();
					}.createDelegate(this)
				}, {
					xtype: 'tbfill'
				}
			]
		});

		form.coordinator_combo = new sw.Promed.SwBaseLocalCombo({
            hiddenName: 'Coordinator_id',
            valueField: 'Coordinator_id',
            displayField: 'Coordinator_Name',
            fieldLabel: lang['koordinator'],
            allowBlank: true,
            editable: false,
            width: 300,
            store: new Ext.data.SimpleStore({
				key: 'Coordinator_id',
				autoLoad: false,
				fields: [
					{name:'Coordinator_id',type:'string'},
					{name:'Coordinator_Name',type:'string'}
				],
				data: [
					['1','Текущая МО'],
					['2','Другие организации']
				]
			}),
            tpl: new Ext.XTemplate(
                '<tpl for="."><div class="x-combo-list-item">',
                '{Coordinator_Name}&nbsp;',
                '</div></tpl>'
            )
        });

		this.FilterPanel = new sw.Promed.BaseWorkPlaceFilterPanel({
			owner: form,
			keys: [{
				key: Ext.EventObject.ENTER,
				fn: function(e) {
					form.doSearch();
				}.createDelegate(this),
				stopEvent: true
			}, {
				ctrl: true,
				fn: function(inp, e) {
					form.doReset();
				},
				key: 188,
				scope: this,
				stopEvent: true
			}],
			filter: {
				title: lang['filtr'],
				layout: 'form',
				items: [{
					layout: 'column',
					items: [{
						width: 320,
						layout: 'form',
						labelWidth: 100,
						items:
							[{
								xtype: 'swcommonsprcombo',
								width: 200,
								hiddenName: 'DrugRequestKind_id',
								comboSubject: 'DrugRequestKind',
								fieldLabel: lang['vid_zayavki']
							}]
						}, {
						width: 350,
						labelWidth: 130,
						layout: 'form',
						items:
							[{
								xtype: 'swcommonsprcombo',
								width: 200,
								hiddenName: 'DrugGroup_id',
								comboSubject: 'DrugGroup',
								fieldLabel: lang['gruppa_medikamentov']
							}]
						}]
				}, {
					layout: 'column',
					items: [{
						layout: 'form',
						width: 320,
						labelWidth: 100,
						items: [{
							xtype: 'swcommonsprcombo',
							width: 200,
							hiddenName: 'DrugRequestStatus_id',
							comboSubject: 'DrugRequestStatus',
							fieldLabel: lang['status_zayavki']
						}]
					}, {
						layout: 'form',
						width: 600,
						labelWidth: 130,
						items: [{
							layout:'form',
							items:[{
								layout:'column',
								items:[{
									layout:'form',
									items:[{
										xtype: 'numberfield',
										width: 100,
										fieldLabel: lang['summa_zayavki'],
										name: 'DrugRequest_Summa1'
									}]
								}, {
									layout:'form',
									width: 20,
									items:[{
										xtype: 'label',
										width: 20,
										html: ('<div>-</div>')
									}]
								}, {
									layout:'form',
									labelWidth: 10,
									items:[{
										labelSeparator: '',
										xtype: 'numberfield',
										width: 100,
										name: 'DrugRequest_Summa2'
									}]
								}, {
									layout:'form',
									width: 50,
									style: 'padding-left:10px;',
									items:[{
										xtype: 'label',
										style: 'text-align:left;',
										width: 30,
										html: ((getRegionNick()=='kz')?'<div>тенге</div>':'<div>руб.</div>')
									}]
								}]
							}]
						}]
					}]
				}, {
					layout: 'column',
					items: [{
						layout: 'form',
						width: 420,
						labelWidth: 100,
						items: [form.coordinator_combo]
					}, {
						layout: 'form',
						items: [{
							style: "padding-left: 35px;",
							xtype: 'button',
							id: form.id + 'BtnSearch',
							text: lang['nayti'],
							iconCls: 'search16',
							handler: function() {
								form.doSearch();
							}.createDelegate(form)
						}]
					}, {
						layout: 'form',
						items: [{
							style: "padding-left: 15px;",
							xtype: 'button',
							id: form.id + 'BtnClear',
							text: lang['sbros'],
							iconCls: 'reset16',
							handler: function() {
								form.doReset();
							}.createDelegate(form)
						}]
					}]
				}]
			}
		});

		var OrderPanel = new sw.Promed.ViewFrame(
		{
			title: lang['spisok_zayavok'],
			id: 'WorkPlaceLpuPharmacyHeadGridPanel',
			object: 'DrugRequest',
			editformclassname: 'swMzDrugRequestRegionEditWindow',
			dataUrl: '/?c=MzDrugRequest&m=loadRegionList',
			height:303,
			paging: false,
			toolbar: true,
			root: '',
			totalProperty: 'totalCount',
			autoLoadData: false,
			sortInfo: {field: 'DrugRequest_insDT'},
			stringfields:
			[
				{name: 'DrugRequest_id', type: 'int', header: 'ID', key: true},
				{name: 'DrugRequestPeriod_id', type: 'int', hidden: true},
				{name: 'PersonRegisterType_id', type: 'int', hidden: true},
				{name: 'DrugRequestKind_id', type: 'int', hidden: true},
				{name: 'DrugGroup_id', type: 'int', hidden: true},
				{name: 'DrugRequestKind_Name', type: 'string', header: lang['vid_zayavki'], width: 90},
				{name: 'DrugGroup_Name', type: 'string', header: lang['gruppa_medikamentov'], width: 90},
				{name: 'DrugRequest_Name', type: 'string', header: lang['naimenovanie_zayavki'], width: 120, id: 'autoexpand'},
				{name: 'DrugRequestStatus_Name', type: 'string', header: lang['status_zayavki'], width: 120},
				{name: 'DrugRequest_Summa', header: lang['summa'], width: 120},
				{name: 'SvodDrugRequest_Name', type: 'string', header: lang['svodnaya_zayavka'], width: 120},
				{name: 'DrugRequestProperty_OrgName', type: 'string', header: lang['koordinator'], width: 90},
				{name: 'DrugRequest_insDT', type: 'date', hidden: true},
				{name: 'MoDrugRequest_Count', type: 'int', hidden: true},
				{name: 'MoDrugRequestCur_Count', type: 'int', hidden: true},
				{name: 'DrugRequestStatus_id', type: 'int', hidden: true},
				{name: 'DrugRequestStatus_Code', type: 'int', hidden: true}
			],
			actions:
			[
				{name:'action_add', hidden: true, disabled: true},
				{name:'action_edit', hidden: true, disabled: true},
				{name:'action_view', text: lang['prosmotr_zayavok'], 
					handler: function(){
						if ( form.GridPanel.ViewGridPanel.getSelectionModel().getSelected() )
						{
							var row = form.GridPanel.ViewGridPanel.getSelectionModel().getSelected();
							if(row.data.MoDrugRequest_Count > 1 && row.data.MoDrugRequest_Count != row.data.MoDrugRequestCur_Count){
								getWnd('swMzDrugRequestRegionViewWindow').show({ARMType: 'minzdravdlo', OrgServiceTerr_Org_id: getGlobalOptions().org_id});
							} else if(row.data.MoDrugRequestCur_Count > 0) {
								var params = {
									ARMType: 'minzdravdlo',
									action: 'view',
									DrugRequestPeriod_id: row.data.DrugRequestPeriod_id,
									PersonRegisterType_id: row.data.PersonRegisterType_id,
									DrugRequestKind_id: row.data.DrugRequestKind_id,
									DrugGroup_id: row.data.DrugGroup_id,
									Lpu_id: getGlobalOptions().lpu_id
								};
								getWnd('swMzDrugRequestViewWindow').show(params);
							}
						}
					}
				},
				{name:'action_delete', hidden: true, disabled: true},
				{name:'action_refresh'},
				{name:'action_print'}
			],
			onLoadData: function() {
				this.getGrid().getStore().sort('DrugRequest_insDT','ASC');
            }
		});
        this.GridPanel = OrderPanel;
		Ext.apply(this,	{
			tbar: WindowToolbarYear
		});
		sw.Promed.swWorkPlaceLpuPharmacyHeadWindow.superclass.initComponent.apply(this, arguments);
	}
});