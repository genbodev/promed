
/**
 * amm_OnkoCtrl_ReportMonitoring - отчет "Мониторинг реализации системы "Онкоконтроль".
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Onko
 * @access       public
 * @copyright    Copyright (c) 2011 Swan Ltd.
 * @author       Nigmatullin Tagir (Ufa)
 * @version      04.02.2015
 */

var Record;
var LpuAcces;

sw.Promed.amm_OnkoCtrl_ReportMonitoring = Ext.extend(sw.Promed.BaseForm, {
    title: lang['otchet_monitoring_realizatsii_sistemyi_onkokontrol_chel'],
    maximized: true, 
    objectName: 'amm_OnkoCtrl_ReportMonitoring',
    id: 'amm_OnkoCtrl_ReportMonitoring',
    objectSrc: '/jscore/Forms/OnkoCtrl/amm_OnkoCtrl_ReportMonitoring.js',
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
					name : "OnkoCtrlReptMonitor_DateForm",
					id: 'OnkoCtrlReptMonitor_DateForm',
					xtype : "daterangefield",
					layout: 'form',
					allowBlank: false,
					width : 170,
					Height : 70,
					fieldLabel : lang['data_otchetnosti'],
					plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
					tabIndex : TABINDEX_LISTTASKFORMVAC + 1
                                        }
                                    ],
                                     keys: [{
                                            key: 13,
                                            fn: function() {
                                                Ext.getCmp('OnkoCtrlReptMonitor_GridPanel').initGrid();
                                            }
                                     }]    
                                }
                            ]
                        }),
                        
        this.OnkoCtrlReptMonitor_GridPanel = new sw.Promed.ViewFrame(
                {
                    id: 'OnkoCtrlReptMonitor_GridPanel',
                    dataUrl: '/?c=OnkoCtrl&m=getOnkoReportMonitoring',
                    toolbar: true,
                    setReadOnly: false,
                    autoLoadData: false,
                    cls: 'txtwrap',
                    //root: 'data',
                    paging: true,
                    totalProperty: 'totalCount',
                    layout: 'form',
                    selectionModel: 'cell',
                    region: 'center',
                    buttonAlign: "right",
                    autoLoadData: false,
                    height: 500,
                    autowith: true,
                    tabIndex: TABINDEX_LISTTASKFORMVAC + 2,
                    
                    stringfields:
                            [
                                {name: 'Lpu_id', type: 'int', header: 'ID', key: true},
                                {name: '_type', type: 'int', header: '_type', hidden:true},
                                {name: 'Lpu_Nick', type: 'string', header: lang['naimenovanie_mo'], id: 'autoexpand'},
                                //{name: 'Lpu_Name', type: 'string', header: 'Полное наименование ЛПУ', width: 300, hidden: true},
                                {name: 'KolPassed', type: 'int', header: lang['proshli_anketirovanie'], align: 'right', width: 200},
                                {name: 'NeedOnko', type: 'int', header: lang['neobhodim_onkokontrol'], align: 'right', width: 200},
                                {name: 'NotNeedOnko', type: 'int', header: lang['ne_nujen_onkokontrol'], align: 'right', width: 200},
                                {name: 'KolZnoAll', type: 'int', header: lang['vpervyie_vyiyavleno_zno_vsego'], align: 'right', width: 200},
                                {name: 'KolZnoUnderAnket', type: 'int', header: lang['v_t_ch_anketirovanie'], align: 'right', width: 200},
                                {name: 'kolZnoDespiteAnket', type: 'int', header: lang['v_t_ch_anketa_-_ok_ne_nujen'], align: 'right', width: 200}
                                

                            ],
                            
                            actions:
			[
                            {name:'action_add',  hidden: true},
                            {name:'action_view',  hidden: true},
                            {name:'action_delete',  hidden: true},
                            {name:'action_save',  hidden: true},
                            {name:'action_edit',  hidden: true}
			
                        ],
                        /*
                        onLoadData: function(sm, index, record) {

                        var KolPassed = 0;
                        var NeedOnko = 0;
                        Ext.getCmp('OnkoCtrlReptMonitor_GridPanel').ViewGridStore.reader.jsonData.forEach (function (rec) {
                            KolPassed += rec.KolPassed;
                            NeedOnko += rec.NeedOnko;

                        });
                       
 

                                var ch = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp';
                                      var Str = lang['proshli_anketirovanie'] + KolPassed + lang['chel_v_t_ch_neobhodim_onkokontrol_-'] + NeedOnko +  lang['sht']+ ch+ ch+ ch
                                      Ext.getCmp('OnkoCtrlReptMonitor_GridPanel').ViewGridPanel.getBottomToolbar().displayMsg = Str +lang['otobrajaemyie_stroki_{0}_-_{1}_iz_{2}']                          


			},    
                       */
                    initGrid: function()
                    {
                        //if (Ext.getCmp('SearchParamsPanel').form.isValid()) {
                        if ((Ext.getCmp('OnkoCtrlReptMonitor_DateForm').getValue1() != '') && (Ext.getCmp('OnkoCtrlReptMonitor_DateForm').getValue2() != '')) {

                        var params = new Object();
                        
                        params.PeriodRange = Ext.getCmp('OnkoCtrlReptMonitor_DateForm').value;
                        if (LpuAcces != undefined)
                            params.Lpu_id = LpuAcces;
                     

                        Ext.getCmp('OnkoCtrlReptMonitor_GridPanel').ViewGridPanel.getStore().baseParams = params;
                        Ext.getCmp('OnkoCtrlReptMonitor_GridPanel').ViewGridPanel.getStore().reload();
                        }
                    },
                          
                    onCellSelect: function (sm, rowIdx, colIdx) {
                        RowIdx = colIdx;
                        Record = sm.selection.record.data;
                        Record.colIdx = colIdx;
                        Record.field = this.getColumnModel().getDataIndex(colIdx);
                        //sw.Promed.vac.utils.consoleLog(Record);
                         
                        },
                     onDblClick: function() {
                         
//                         if ((Record.field == 'Kol')||(Record.field == 'KolPassed')
//                                 ||(Record.field == 'NeedOnko') ||(Record.field == 'NotNeedOnko'))  {
                             //sw.Promed.vac.utils.consoleLog('onDblClick');
                         if (Record._type == 0) {
                             var  params = new Object();
                             params.Lpu_id = Record.Lpu_id;
                             params.PeriodRange =Ext.getCmp('OnkoCtrlReptMonitor_DateForm').value;
                             params.Field = Record.field;
                             getWnd('amm_Onko_RepMonitoring_Detail').show(params);
                       }                 
                     }
                        

                            
                });

        this.OnkoCtrlReptMonitor_GridPanel.getGrid().view = new Ext.grid.GridView(
        {
            getRowClass: function(row, index)
            {
                var cls = '';
                if (row.get('_type') == 1) {
                    cls = 'x-grid-rowbold ';
                }  
                /*
                if (row.get('StatusOnkoProfile_id') == 1) {
                    //cls = 'x-grid-rowbold ';
                    if (row.get('monitored') == 1)
                        cls = cls + 'x-grid-rowred ';
                    else
                        cls = cls + 'x-grid-rowgreen ';
                };
                */
                return cls;
            }
        });



        Ext.apply(this, {
            lbodyBorder: true,
            layout: "border",
            cls: 'tg-label',
            buttons: [
               {
                        text: lang['sformirovat_otchet'],
                        iconCls: 'inj-stream16',
                        id: 'Vac_FormPlan',
                        disabled: false,
                        tabIndex: TABINDEX_STARTVACFORMPLAN + 6,

				handler: function() {
                                     
                                     Ext.getCmp('OnkoCtrlReptMonitor_GridPanel').initGrid();
                                     
                                     Ext.getCmp('OnkoCtrlReptMonitor_DateForm').focus(true, 50);
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
                this.OnkoCtrlReptMonitor_GridPanel
            ]
        });

        sw.Promed.amm_OnkoCtrl_ReportMonitoring.superclass.initComponent.apply(this, arguments);
    },
    show: function(record) {
        sw.Promed.amm_OnkoCtrl_ReportMonitoring.superclass.show.apply(this, arguments);
        this.formParams = record;
        sw.Promed.vac.utils.consoleLog('record!!!');
        Ext.getCmp('OnkoCtrlReptMonitor_DateForm').isValid();
        if (!Ext.getCmp('amm_OnkoCtrl_ReportMonitoring').MOAccess())
            LpuAcces = getGlobalOptions().lpu_id;
    }
});

