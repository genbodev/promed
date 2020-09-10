
/**
 * amm_OnkoCtrl_ReportSetZNO - отчет по онкологии.
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      VAC
 * @access       public
 * @copyright    Copyright (c) 2011 Swan Ltd.
 * @author       Nigmatullin Tagir (Ufa)
 * @version      17.09.2014
 */

var Record;

sw.Promed.amm_OnkoCtrl_ReportSetZNO = Ext.extend(sw.Promed.BaseForm, {
    title: lang['otchet_analiz_vyiyavlennyih_zno'],
    border: false,
    width: 725,
    height: 500,
    maximized: true,
    maximizable: true,
    codeRefresh: true,
    closeAction: 'hide',
    objectName: 'amm_OnkoCtrl_ReportSetZNO',
    id: 'amm_OnkoCtrl_ReportSetZNO',
    objectSrc: '/jscore/Forms/OnkoCtrl/amm_OnkoCtrl_ReportSetZNO.js',
    onHide: Ext.emptyFn,
    
    MOAccess: function()
    {   
        var result = false;
        if ((getGlobalOptions().onkoctrlAccessAllLpu == 1) || isAdmin)
            result = true;
        return result;
    },
            
    initComponent: function() {

        curWnd = this;
      

                this.SearchParamsPanel = new Ext.form.FormPanel({
			id : "SearchParamsPanel",
			labelWidth : 150,
			frame : false,
			border: false,
                        bodyStyle:'border-bottom-width: 1px;',
			region: 'north',
			layout: 'form',
                        autoHeight : true,
                        items : [{
				region: 'north',
				layout : "form",
				autoHeight: true,
                                //id: 'SearchTaskForm',
				labelWidth : 180,
				labelAlign : "right",
                                items : [{
					height : 10,
					border : false,
					cls: 'tg-label'
				},
				{
					name : "RptZNO_Date_Form",
					id: 'RptZNO_Date_Form',
					xtype : "daterangefield",
					layout: 'form',
					allowBlank: false,
					width : 170,
					Height : 70,
					fieldLabel : lang['data_otchetnosti'],
					plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
					tabIndex : TABINDEX_LISTTASKFORMVAC + 1
//                                        listeners: {
//                                                    'keydown':  alert('keydown')
//                                                            //Ext.getCmp('Vac_FormPlan').handler()
//                                                },
                                        }
                                    ],
                                     keys: [{
                                            key: 13,
                                            fn: function() {
                                                Ext.getCmp('RptZNO_ViewFrameOnkoProfileJurnal').initGrid();
                                            }
                                     }]    
                                }
                            ]
//                            ,
//                            
//                            keys: [{
//                                            key: 13,
//                                            fn: function() {
//                                              sw.Promed.vac.utils.consoleLog('key 13');
//                                             
//                                            },
//                                            stopEvent: true
//                                          }],
                        }),
                        
        this.RptZNO_ViewFrameOnkoProfileJurnal = new sw.Promed.ViewFrame(
                {//ViewFrameVacListTasks    amm_ViewFrameVacListTasks
                    id: 'RptZNO_ViewFrameOnkoProfileJurnal',
                    dataUrl: '/?c=OnkoCtrl&m=GetOnkoReportSetZNO',
                    toolbar: true,
                    setReadOnly: false,
                    autoLoadData: false,
                    cls: 'txtwrap',
                    //root: 'data',
                    paging: false,
                    totalProperty: 'totalCount',
                    layout: 'form',
                    selectionModel: 'cell',
                    region: 'center',
                    buttonAlign: "right",
                    autoLoadData: false,
                            height: 500,
                    //                          autoheight: true,
                    autowith: true,
                    tabIndex: TABINDEX_LISTTASKFORMVAC + 2,
                    stringfields:
                            [
                                {name: 'Lpu_id', type: 'int', header: 'ID', key: true},
                                {name: '_type', type: 'int', header: '_type', hidden:true},
                                {name: 'Lpu_Nick', type: 'string', header: lang['naimenovanie_mo'], id: 'autoexpand'},
                                {name: 'Lpu_Name', type: 'string', header: lang['polnoe_naimenovanie_lpu'], width: 300, hidden: true},
                                {name: 'Kol_Zno', type: 'int', header: lang['vsego_vyiyavleno_zno'], align: 'right', width: 200, summaryType: 'sum'},
                                {name: 'Kol', type: 'int', header: lang['v_t_ch_vyiyavleno_vpervyie'], align: 'right', width: 200, summaryType: 'sum'},
                                {name: 'KolPassed', type: 'int', header: lang['v_t_ch_proshli_anketirovanie'], align: 'right', width: 200, summaryType: 'sum'},
                                {name: 'NeedOnko', type: 'int', header: lang['neobhodim_onkokontrol'], align: 'right', width: 200, summaryType: 'sum'},
                                {name: 'NotNeedOnko', type: 'int', header: lang['ne_nujen_onkokontrol'], align: 'right', width: 200, summaryType: 'sum'}

                            ],
                            
                            actions:
			[
                            {name:'action_add',  hidden: true},
                            {name:'action_view',  hidden: true},
                            {name:'action_delete',  hidden: true},
                            {name:'action_save',  hidden: true},
                            {name:'action_edit',  hidden: true}
			
                        ],
                       
                    initGrid: function()
                    {
                        //if (Ext.getCmp('SearchParamsPanel').form.isValid()) {
                        if ((Ext.getCmp('RptZNO_Date_Form').getValue1() != '') && (Ext.getCmp('RptZNO_Date_Form').getValue2() != '')) {
                                        //sw.Promed.vac.utils.msgBoxNoValidForm();
                                        /*
                                        Ext.Msg.alert(lang['oshibka'], lang['vvedite_period_otchetnosti']);
                                        Ext.getCmp('RptZNO_Date_Form').focus(true, 50);
                                        return false;
                                    };  
                                    */
                        var params = new Object();
                        
                        params.PeriodRange = Ext.getCmp('RptZNO_Date_Form').value;
                        //alert(params.PeriodRange);

                        Ext.getCmp('RptZNO_ViewFrameOnkoProfileJurnal').ViewGridPanel.getStore().baseParams = params;
                        //this.GridPanel.loadData({globalFilters: params});
                        Ext.getCmp('RptZNO_ViewFrameOnkoProfileJurnal').ViewGridPanel.getStore().reload();
                        }
                    },
                    /*  
                    onLoadData: function(sm, index, record) {

                    var Kol_Zno = Ext.getCmp('RptZNO_ViewFrameOnkoProfileJurnal').ViewGridStore.reader.jsonData.Kol_Zno
                    var Kol = Ext.getCmp('RptZNO_ViewFrameOnkoProfileJurnal').ViewGridStore.reader.jsonData.kol                                

                        var ch = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp';
                              var Str = lang['vsego_vyiyavleno_zno'] + Kol_Zno + lang['chel_v_t_ch_vyiyavleno_vpervyie_-'] + Kol +  lang['sht']+ ch+ ch+ ch
                              Ext.getCmp('GridLpu4Report').ViewGridPanel.getBottomToolbar().displayMsg = Str +lang['otobrajaemyie_stroki_{0}_-_{1}_iz_{2}']                          

                },
                */
                    onCellSelect: function (sm, rowIdx, colIdx) {
                        RowIdx = colIdx;
                        Record = sm.selection.record.data;
                        Record.colIdx = colIdx;
                        Record.field = this.getColumnModel().getDataIndex(colIdx);
                        //sw.Promed.vac.utils.consoleLog(Record);
                            //alert('cellselect');
                        },
                     onDblClick: function() {
                         
                         if ((Record._type == 0) && ((Record.field == 'Kol')||(Record.field == 'Kol_Zno')||(Record.field == 'KolPassed')
                                 ||(Record.field == 'NeedOnko') ||(Record.field == 'NotNeedOnko')))  {
                             //sw.Promed.vac.utils.consoleLog('onDblClick');
                            
                            var  params = new Object();
                         params.Lpu_id = Record.Lpu_id;
                         params.PeriodRange =Ext.getCmp('RptZNO_Date_Form').value;
                         params.Field = Record.field;
                           
                           getWnd('amm_OnkoCtrl_RepSetZNO_Detail').show(params);
                       }                 
                     }
                        

                            
                });

        this.RptZNO_ViewFrameOnkoProfileJurnal.getGrid().view = new Ext.grid.GridView(
                {
                    getRowClass: function(row, index)
                    {
                        var cls = '';
                        if (row.get('_type') == 1) {
                            cls = 'x-grid-rowbold ';
                        }   
                        /*
                            if (row.get('monitored') == 1)
                                cls = cls + 'x-grid-rowred ';
                            else
                                cls = cls + 'x-grid-rowgreen ';
                          */
                            


                        return cls;
                    }
                });



        Ext.apply(this, {
            lbodyBorder: true,
            layout: "border",
            cls: 'tg-label',
            //      bodyStyle: 'padding: 5px',

            buttons: [
               {
                        text: lang['sformirovat_otchet'],
                        iconCls: 'inj-stream16',
                        id: 'Vac_FormPlan',
                        disabled: false,
                        tabIndex: TABINDEX_STARTVACFORMPLAN + 6,

				handler: function() {
                                      
                                     Ext.getCmp('RptZNO_ViewFrameOnkoProfileJurnal').initGrid();
                                     Ext.getCmp('RptZNO_Date_Form').focus(true, 50);
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
                    id: 'OnkoCtrlJ_CancelButton',
                    onTabAction: function() {
                        Ext.getCmp('OnkoCtrlJ_ResetSearch').focus(true, 50);
                    }.createDelegate(this),
                     tabIndex : TABINDEX_ONKOCTRLPROFILEJOURNAL + 9,
                    text: lang['zakryit']
                }
            ],
            items: [
                this.SearchParamsPanel,
                this.RptZNO_ViewFrameOnkoProfileJurnal
            ]
        });

        sw.Promed.amm_OnkoCtrl_ReportSetZNO.superclass.initComponent.apply(this, arguments);
    },
    show: function(record) {
        sw.Promed.amm_OnkoCtrl_ReportSetZNO.superclass.show.apply(this, arguments);
        this.formParams = record;
        Ext.getCmp('RptZNO_Date_Form').isValid();
        
//        Ext.getCmp('Search_LpuListCombo').setValue(getGlobalOptions().lpu_id);
//        Ext.getCmp('ViewFrameOnkoProfileJurnal').initGrid();
//        Ext.getCmp('OnkoCtrlJ_ResetSearch').focus(true, 50);
        //load(this.formParams.Lpu_id)




    }
});

