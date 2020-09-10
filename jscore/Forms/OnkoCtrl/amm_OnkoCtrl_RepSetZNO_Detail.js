/**
* amm_OnkoCtrl_RepSetZNO_Detail - окно просмотра Списка заданий
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       
* @version       
* @comment      Префикс для id компонентов regv (amm_JournalViewWindow)
*/

var PageSize = 100;

sw.Promed.amm_OnkoCtrl_RepSetZNO_Detail = Ext.extend(sw.Promed.BaseForm, {
	id: 'amm_OnkoCtrl_RepSetZNO_Detail',
        title: lang['otchet_analiz_vyiyavlennyih_zno_detalizatsiya'],
	border: false,
	width: 725,
	height: 500,
	maximized: true,
	maximizable: true,        
	codeRefresh: true,
	closeAction: 'hide',
	objectSrc: '/jscore/Forms/Vaccine/amm_OnkoCtrl_RepSetZNO_Detail.js',
	//onHide: Ext.emptyFn,

	 
	initComponent: function() {


		this.ViewFrameRep5Detail = new sw.Promed.ViewFrame(
		{
			id: 'amm_ViewFrameRepSetZnoDetail',
			dataUrl: '/?c=OnkoCtrl&m=getOnkoReportSetZNO_Detail',
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
                        //{name: 'fio', type: 'string', header: 'ФИО', width: 200},
                        {name: 'SurName', type: 'string', header: lang['familiya'], width: 120},
                        {name: 'FirName', type: 'string', header: lang['imya'], width: 120},
                        {name: 'SecName', type: 'string', header: lang['otchestvo'], width: 120},
                        {name: 'BirthDay', type: 'date', header: lang['datarojdeniya'], width: 90},
                        {name: 'Ds_date', type: 'date', header: lang['data_diagnoza'], width: 90},
                        {name: 'Diag_Code', type: 'string', header: lang['kod_diagnoza'], width: 90},
                        {name: 'MedPersonal_fin', type: 'string', header: lang['vrach'], width: 180},
                        {name: 'Profile_Date', type: 'date', header: lang['data_anketirovaniya'], width: 90},
                        {name: 'ProfileResult', type: 'string', header: lang['rezultat'], width: 180},
                        {name: 'Diag_Name', type: 'string', header: lang['naimenovaniediagnoza'],id: 'autoexpand'}
                     
                        //{name: 'monitored_Name', type: 'string', header: 'Онкоконтроль', width: 200, id: 'autoexpand'}
                        ],
                        actions:
			[
                            { name:'action_add', hidden: true},
                            {name:'action_edit', hidden: true},
                            {name:'action_view', hidden: true},
                            {name:'action_delete', hidden: true}
                        ]
//			, onLoadData: function()
//			{
//
//			}

		});
		

		Ext.apply(this, {
			lbodyBorder : true,
			layout : "border",
			cls: 'tg-label',
			//      bodyStyle: 'padding: 5px',
			
			buttons: [
//			{
//				text : BTN_FRMSEARCH,
//				iconCls: 'search16',
//								id: 'ListTaskForm_Search',
//				handler: function() {
//					Ext.getCmp('amm_ViewFrameRep5Detail').initGrid();
//				}.createDelegate(this),
//				
//				
//				tabIndex : TABINDEX_LISTTASKFORMVAC + 4
//			},
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
				
				this.ViewFrameRep5Detail
			]
		});
		 
		sw.Promed.amm_OnkoCtrl_RepSetZNO_Detail.superclass.initComponent.apply(this, arguments);
	},
	
	show: function(record) {


                this.formParams = record;
                
                sw.Promed.amm_OnkoCtrl_RepSetZNO_Detail.superclass.show.apply(this, arguments);
                sw.Promed.vac.utils.consoleLog(lang['forma_amm_repsetzno_detail']);
                //sw.Promed.amm_RepSetZNO_Detail.superclass.show.apply(this, arguments);
		
                
                var params = new Object();
                params.Lpu_id = this.formParams.Lpu_id;
                params.PeriodRange = this.formParams.PeriodRange;
                params.Field = this.formParams.Field;
                var $title = lang['otchet_analiz_vyiyavlennyih_zno_detalizatsiya'];
//                if (params.Field == 'Kol')
//                 Ext.getCmp('amm_OnkoCtrl_RepSetZNO_Detail').setTitle($title + ' (Список выявленных ЗНО)')
          
                var $colmodel =  Ext.getCmp('amm_ViewFrameRepSetZnoDetail').getColumnModel()
                var $hidden = true
                    
                if (this.formParams.Field == 'Kol') {
                     Ext.getCmp('amm_OnkoCtrl_RepSetZNO_Detail').setTitle($title + lang['spisok_vyiyavlennyih_zno']);
                     $colmodel.setColumnHeader($colmodel.findColumnIndex('Diag_Name'), lang['naimenovaniediagnoza']);
                     $hidden = true
                }
                else //if (this.formParams.Field == 'KolPassed')
                {
                    $colmodel.setColumnHeader($colmodel.findColumnIndex('Diag_Name'), lang['onkokontrol']);
                    $hidden = false
                }
                console.log('formParams');
                console.log(record);
                
                
                $colmodel.setHidden($colmodel.findColumnIndex('Ds_date'), !$hidden);
                $colmodel.setHidden($colmodel.findColumnIndex('Diag_Code'), !$hidden);
                //$colmodel.setHidden($colmodel.findColumnIndex('Diag_Name'), !$hidden);
                
                $colmodel.setHidden($colmodel.findColumnIndex('MedPersonal_fin'), $hidden);
                $colmodel.setHidden($colmodel.findColumnIndex('Profile_Date'), $hidden);
                $colmodel.setHidden($colmodel.findColumnIndex('ProfileResult'), $hidden);
                //$colmodel.setHidden($colmodel.findColumnIndex('monitored_Name'), $hidden);

                
          
//                else
//                    Ext.getCmp('amm_OnkoCtrl_RepSetZNO_Detail').title += ' (YTСписок выявленных ЗНО)'
                
                params.start = 0
                params.limit = PageSize
                // Ext.getCmp('amm_RepSetZNO_Detail').title = arguments[0].title
                
                Ext.getCmp('amm_ViewFrameRepSetZnoDetail').ViewGridPanel.getStore().baseParams = params;    
                Ext.getCmp('amm_ViewFrameRepSetZnoDetail').ViewGridPanel.getStore().load();
                
	}
});

