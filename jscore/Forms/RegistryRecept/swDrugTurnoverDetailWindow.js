/**
* amm_Onko_RepMonitoring_Detail - Оборотная ведомость: Детализация
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Tagir Nigmatullin, Ufa
* @version      15.02.2016 

*/


sw.Promed.swDrugTurnoverDetailWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'DrugTurnoverDetailWindow',
        title: lang['oborotnaya_vedomost_detalizatsiya'],
	border: false,
	width: 1030,
	height: 600,
	maximizable: true,        
	codeRefresh: true,
	closeAction: 'hide',
	initComponent: function() {

                this.infoPanel = new Ext.Panel({
                        height: 120,
			border: false,
                        bodyStyle: 'padding: 5px 5px 0',
			frame: true,
			region: 'north',
			labelAlign: 'right',
                        items: [{
                            layout: 'form',
                            items: [{
                                    xtype: 'textfield', 
                                    fieldLabel: lang['kod_lp'],
                                    readOnly: true,
                                    id: 'ViewFrameTurnover_DrugCode', 
                                    },
                                    {
                                    xtype: 'textarea', 
                                    fieldLabel: lang['naimenovanie'],
                                    readOnly: true,
                                    height: 40,
							width: 700,
                                    id: 'ViewFrameTurnover_DrugName', 
                                    },
                                    {
                                    xtype: 'textfield', 
                                    fieldLabel: lang['chislitsya_na_sklade'],
                                    readOnly: true,
                                    id: 'ViewFrameTurnover_Kolvo', 
                                    }
                                ]
                        }]
                 });
                 
		this.ViewFrameTurnoverDetail = new sw.Promed.ViewFrame(
		{
			id: 'ViewFrameTurnoverDetail',
			dataUrl: '/?c=RegistryRecept&m=loadDrugTurnoverDetail',
			toolbar: true,
			setReadOnly: false,
			autoLoad: false,
                        root: 'data',
			cls: 'txtwrap',
			//paging: true,
			totalProperty: 'totalCount',
			layout:'form',
			region: 'center',
			buttonAlign : "right",
			autoLoadData: false,
			height: 500,
			autowith: true,
			tabIndex: TABINDEX_LISTTASKFORMVAC + 2,
			stringfields:
			[
			{name: 'ID', type: 'int', header: 'ID', key: true},
                        {name: 'DocumentUcStr_id', type: 'int', header: 'DocumentUcStr_id', hidden: true},
                        {name: 'recType', type: 'int', header: 'recType', hidden: true},
                        {name: 'DocumentUc_Num', type: 'string', header: lang['nomer_dokumenta'], width: 120},
                        {name: 'DocumentUc_didDate', type: 'date', header: lang['data_dokumenta'], width: 120},
                        {name: 'DrugDocumentType_id', type: 'int', header: lang['iddokumenta'],  hidden: true},
                        {name: 'DrugDocumentType_Name', type: 'string', header: lang['tipdokumenta'], width: 250},
                        {name: 'BegOst', type: 'string', header: lang['ostatokna_nachaloperioda'], align: 'right', width: 100},
                        {name: 'Pr_Kol', type: 'string', header: lang['prihodza_period'], align: 'right', width: 100},
                        {name: 'Ras_Kol', type: 'string', header: lang['rashodza_period'], align: 'right', width: 100},
                        {name: 'endOst', type: 'string', header: lang['ostatokna_konetsperioda'], align: 'right', width: 100},
                        {name: 'recdeleted_name', type: 'string', header: lang['kommentariy'], align: 'right', width: 100}, 
                        {name: 'recdeleted', header: 'recdeleted', hidden: true}
                        
                        ],
                        actions:
			[
                            { name:'action_add', hidden: true},
                            {name:'action_edit', hidden: true},
                            {name:'action_view', hidden: true},
                            {name:'action_delete', hidden: true}
                        ]
		});
                
                this.ViewFrameTurnoverDetail.getGrid().view = new Ext.grid.GridView({
			getRowClass : function (row, index) {
                        var cls = '';
                        if (row.get('recdeleted') == 3) {
                            cls = cls+'x-grid-rowbackyellow';
						} else if (row.get('recType') == 3) {
                            cls = cls+'x-grid-rowbackgreen';
                        } else if (row.get('recdeleted') == 2) {
                            cls = cls+'x-grid-rowdeleted';
                        } else if (row.get('recType') == -1) {
                            cls = cls+'x-grid-rowbold ';
                             if ( row.get('endOst')!= Ext.getCmp('ViewFrameTurnover_Kolvo').getValue()) {
                                 cls = cls + 'x-grid-rowred'
                        }
					;
				}
                        return cls;
                                }
		});

		Ext.getCmp('ViewFrameTurnoverDetail').getGrid().on(
				'columnresize', function (columnIndex, newSize) {
					//console.log('columnIndex',columnIndex, 'newSize', newSize);
					Ext.getCmp('ViewFrameTurnoverDetailItogo').getColumnModel().setColumnWidth(columnIndex, newSize);
				}
		);

		this.ViewFrameTurnoverDetailItogo = new sw.Promed.ViewFrame(
                {
					id: 'ViewFrameTurnoverDetailItogo',
					title: '',
					dataUrl: '/?c=RegistryRecept&m=loadDrugTurnoverDetail',
					autoLoadData: false,
					height: getGlobalOptions().orgtype == 'lpu' ? 50 : 25,
					region: 'south',
					root: 'data',
					contextmenu: false,
					toolbar: false,
					hideHeaders: true,
					stringfields:
							[
								{name: 'ID', type: 'int', header: 'ID', key: true},
								{name: 'DocumentUcStr_id', type: 'int', header: 'DocumentUcStr_id', hidden: true},
								{name: 'recType', type: 'int', header: 'recType', hidden: true},
								{name: 'DocumentUc_Num', type: 'string', header: lang['nomer_dokumenta'], width: 120},
								{name: 'DocumentUc_didDate', type: 'date', header: lang['data_dokumenta'], width: 120},
								{name: 'DrugDocumentType_id', type: 'int', header: lang['iddokumenta'], hidden: true},
								{name: 'DrugDocumentType_Name', type: 'string', header: lang['tipdokumenta'], width: 250},
								{name: 'BegOst', type: 'string', header: lang['ostatokna_nachaloperioda'], align: 'right', width: 100},
								{name: 'Pr_Kol', type: 'string', header: lang['prihodza_period'], align: 'right', width: 100},
								{name: 'Ras_Kol', type: 'string', header: lang['rashodza_period'], align: 'right', width: 100},
								{name: 'endOst', type: 'string', header: lang['ostatokna_konetsperioda'], align: 'right', width: 100},
								{name: 'recdeleted_name', type: 'string', header: lang['kommentariy'], align: 'right', width: 100},
								{name: 'recdeleted', header: 'recdeleted', hidden: true}
							]
				});

		this.ViewFrameTurnoverDetailItogo.getGrid().view = new Ext.grid.GridView({
                    getRowClass : function (row, index){
				var cls = '';
				cls = cls + 'x-grid-rowbold ';
				return cls;
                            }
		})

		Ext.getCmp('ViewFrameTurnoverDetailItogo').getGrid().on('render', function () {
			Ext.getCmp('ViewFrameTurnoverDetail').getGrid().getView().scroller.dom.style.overflowX = 'hidden';
		});

		Ext.getCmp('ViewFrameTurnoverDetailItogo').getGrid().on(
				'bodyscroll', function (scrollLeft, scrollTop) {
					Ext.getCmp('ViewFrameTurnoverDetail').getGrid().getView().scroller.dom.scrollLeft = scrollLeft;
                            }
		);
                       
		Ext.apply(this, {
			lbodyBorder : true,
			layout : "border",
			cls: 'tg-label',
                        buttons: [
                            {
                                id: 'ViewFrameTurnover_KorrectButton',
                                text: lang['izmenit_ostatki'], 
                                iconCls: 'edit16',
                                handler: function() {
                                    var params = new Object();
                                    params.DrugOstatRegistry_id = this.formParams.DrugOstatRegistry_id;
                                    params.DrugOstat_Kolvo = this.formParams.EndOst_Kol;
								    params.EndOst_Contr = 0;
								    if ( params.DrugOstat_Kolvo < 0) {
										params.DrugOstat_Kolvo = '0'
										params.EndOst_Contr = 1
						}
						;
                                    //console.log('DrugOstat_Kolvo = ' + params.DrugOstat_Kolvo);
                                    var form = this;
                                    Ext.Ajax.request({
							url: '/?c=RegistryRecept&m=UpdateDrugOstatRegistry_balances',
							method: 'POST',
							params: params,
							success: function(){
                                                                Ext.getCmp('DrugTurnoverListWindow').fireEvent('success', 'DrugTurnoverDetailWindow', params);
								form.hide();
							}
						});
                                    
                                }.createDelegate(this),
                            },
                            {
				text: '-'
                            },
						HelpButton(this, TABINDEX_LISTTASKFORMVAC + 3),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'close16',
				id: 'ViewFrameTurnover_CancelButton',
				onTabAction: function () {
					Ext.getCmp('Date_View').focus(true, 50);
				}.createDelegate(this),
				tabIndex: TABINDEX_LISTTASKFORMVAC + 5,
				text: lang['zakryit']
			}
			],
			items : [{
				region: 'north',
				layout : "form",
				autoHeight: true,
				labelWidth : 180,
				labelAlign : "right"
				},
                                    this.infoPanel,
				this.ViewFrameTurnoverDetail,
				this.ViewFrameTurnoverDetailItogo
			]
		});
		 
		sw.Promed.swDrugTurnoverDetailWindow.superclass.initComponent.apply(this, arguments);
	},
	show: function(record) {


                this.formParams = record;
               
                sw.Promed.swDrugTurnoverDetailWindow.superclass.show.apply(this, arguments);
              
                var params = new Object();
                params.PeriodRange = this.formParams.PeriodRange;
                params.DrugShipment_id = this.formParams.DrugShipment_id;
                params.Drug_Code = this.formParams.Drug_Code;
                params.Org_id = this.formParams.Org_id;
                params.WhsDocumentCostItemType_id = this.formParams.WhsDocumentCostItemType_id;
                params.Lpu_id = this.formParams.Lpu_id;
		params.SubAccountType_id = this.formParams.SubAccountType_id;
		params.DrugOstatRegistry_id = this.formParams.DrugOstatRegistry_id;
             
                Ext.getCmp('ViewFrameTurnover_DrugCode').setValue(this.formParams.Drug_Code);
                Ext.getCmp('ViewFrameTurnover_DrugName').setValue(this.formParams.Drug_Name);
                Ext.getCmp('ViewFrameTurnover_Kolvo').setValue(this.formParams.Kolvo);
                //console.log('this.formParams = ' ); console.log(this.formParams);
                if (this.formParams.EndOst_Kol < this.formParams.Kolvo && this.formParams.EndOst_Kol >= 0 
                        && getGlobalOptions().groups.toString().indexOf('OrgAdmin') != -1)
                    Ext.getCmp('ViewFrameTurnover_KorrectButton').show();
                else
                     Ext.getCmp('ViewFrameTurnover_KorrectButton').hide();
		//Ext.getCmp('ViewFrameTurnover_KorrectButton').show();
                Ext.getCmp('ViewFrameTurnoverDetail').ViewGridPanel.getStore().baseParams = params;   
		Ext.getCmp('ViewFrameTurnoverDetail').ViewGridPanel.getStore().load({
			callback: function () {
				console.log('ViewFrameTurnoverDetail = ', Ext.getCmp('ViewFrameTurnoverDetail').ViewGridPanel.getStore().data);
				Ext.getCmp('ViewFrameTurnoverDetailItogo').getGrid().store.removeAll();
				Cnt = Ext.getCmp('ViewFrameTurnoverDetail').ViewGridPanel.getStore().data.items.length;
				for (var r = 0; r <= Cnt - 1; r++) {
					rec = Ext.getCmp('ViewFrameTurnoverDetail').ViewGridPanel.getStore().data.items[r].data;
					//console.log('rec =', rec);
					if (rec['recType'] == -1 || rec['recType'] == -2) {
						Ext.getCmp('ViewFrameTurnoverDetailItogo').getGrid().store.insert(0, [new Ext.data.Record({
								DocumentUc_Num: rec['DocumentUc_Num'],
								BegOst: rec['BegOst'],
								Pr_Kol: rec['Pr_Kol'],
								Ras_Kol: rec['Ras_Kol'],
								endOst: rec['endOst'],
								recType: rec['recType']
							})]);
					}
				};
				Ext.getCmp('ViewFrameTurnoverDetailItogo').ViewGridPanel.getStore().sort('recType', 'asc');
                
				var grid = Ext.getCmp('ViewFrameTurnoverDetail').ViewGridPanel;
				grid.getStore().clearFilter();
				grid.getStore().filterBy(function (rec) {
					return (rec.get('recType').inlist([0, 1, 2]))
				});
	}
		})
	}
});
       