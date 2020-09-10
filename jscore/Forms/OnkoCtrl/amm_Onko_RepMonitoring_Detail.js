/**
* amm_Onko_RepMonitoring_Detail - окно просмотра  отчёта "Мониторинг реализации системы "Онкоконтроль": Детализация
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Tagir Nigmatullin, Ufa
* @version      06.02.2015 

*/

var PageSize = 100;

sw.Promed.amm_Onko_RepMonitoring_Detail = Ext.extend(sw.Promed.BaseForm, {
	id: 'amm_Onko_RepMonitoring_Detail',
        title: lang['otchet_monitoring_realizatsii_sistemyi_onkokontrol_detalizatsiya'],
	border: false,
	width: 725,
	height: 500,
	maximized: true,
	maximizable: true,        
	codeRefresh: true,
	closeAction: 'hide',
	objectSrc: '/jscore/Forms/Vaccine/amm_Onko_RepMonitoring_Detail.js',
	//onHide: Ext.emptyFn,

	 
	initComponent: function() {


		this.ViewFrameRepMonitoringDetail = new sw.Promed.ViewFrame(
		{
			id: 'ViewFrameRepMonitoringDetail',
			dataUrl: '/?c=OnkoCtrl&m=getOnkoReportMonitoring_Detail',
			toolbar: true,
			setReadOnly: false,
			autoLoad: false,
                        root: 'data',
			cls: 'txtwrap',
			paging: true,
			totalProperty: 'totalCount',
                        cls: 'txtwrap',  
			layout:'form',
			region: 'center',
			buttonAlign : "right",
			autoLoadData: false,
			height: 500,
			autowith: true,
			tabIndex: TABINDEX_LISTTASKFORMVAC + 2,
			stringfields:
			[
                        {name: 'Person_id', type: 'int', header: 'Person_id', key: true},
                        {name: 'SurName', type: 'string', header: lang['familiya'], width: 120},
                        {name: 'FirName', type: 'string', header: lang['imya'], width: 120},
                        {name: 'SecName', type: 'string', header: lang['otchestvo'], width: 120},
                        {name: 'BirthDay', type: 'date', header: lang['datarojdeniya'], width: 90},
                        {name: 'Prof_DtBeg', type: 'date', header: lang['data_anketirovaniya'], width: 90},
                        {name: 'Prof_ProfileResult', type: 'string', header: lang['rezultat'], width: 150},
                        {name: 'Prof_MedPersonal_fio', type: 'string', header: lang['vrach_anketirovanie'], width: 180},
                        {name: 'Diag_Date', type: 'date', header: lang['data_diagnoza'], width: 90},
                        {name: 'Diag_Code', type: 'string', header: lang['kod_diagnoza'], width: 90},
                        {name: 'Diag_Name', type: 'string', header: lang['naimenovaniediagnoza'],id: 'autoexpand'},
                        {name: 'Diag_MedPersonal_fio', type: 'string', header: lang['vrach_diagnoz'], width: 180}
                        
                        ],
                        actions:
			[
                            { name:'action_add', hidden: true},
                            {name:'action_edit', hidden: true},
                            {name:'action_view', hidden: true},
                            {name:'action_delete', hidden: true}
                        ]

		});
		
                this.ViewFrameRepMonitoringDetail.getGrid().view = new Ext.grid.GridView(
                {
                    getRowClass : function (row, index){
                        //sw.Promed.vac.utils.consoleLog('index');
                        //sw.Promed.vac.utils.consoleLog(index);
                        if (index != undefined && index != '') {
                            //sw.Promed.vac.utils.consoleLog('index2'); 
                            var arrCls = [];
                            if (row.get('Prof_ProfileResult').length > 0){
                                cls = 'x-grid-rowbold';
                                arrCls.push('x-grid-rowbold');
                            }
                            if (row.get('Diag_Code').length > 0){
                                arrCls.push('x-grid-rowred');
                            }
                            return arrCls.join(' ');
                        }
                       
                    }   
                });
                     
		Ext.apply(this, {
			lbodyBorder : true,
			layout : "border",
			cls: 'tg-label',
                        buttons: [
						{
				text: '-'
			},
						HelpButton(this, TABINDEX_LISTTASKFORMVAC + 3),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'close16',
				id: 'ListTaskForm_CancelButton',
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
				
				this.ViewFrameRepMonitoringDetail
			]
		});
		 
		sw.Promed.amm_Onko_RepMonitoring_Detail.superclass.initComponent.apply(this, arguments);
	},
	
	show: function(record) {


                this.formParams = record;
               
                sw.Promed.amm_Onko_RepMonitoring_Detail.superclass.show.apply(this, arguments);
                sw.Promed.vac.utils.consoleLog(lang['forma_amm_repsetzno_detail']);
                
                var params = new Object();
                params.Lpu_id = this.formParams.Lpu_id;
                params.PeriodRange = this.formParams.PeriodRange;
                params.Field = this.formParams.Field;
                //var $title = 'Отчет "Анализ выявленных ЗНО": детализация';
                //console.log('formParams');
                //console.log(record);
                
                params.start = 0
                params.limit = PageSize
             
                
                Ext.getCmp('ViewFrameRepMonitoringDetail').ViewGridPanel.getStore().baseParams = params;    
                
            Ext.getCmp('ViewFrameRepMonitoringDetail').ViewGridPanel.getStore().load();
             
	}
});

