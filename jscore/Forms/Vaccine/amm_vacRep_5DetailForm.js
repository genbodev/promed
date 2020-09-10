/**
* amm_vacRep_5DetailForm - окно просмотра Списка заданий
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

sw.Promed.amm_vacRep_5DetailForm = Ext.extend(sw.Promed.BaseForm, {
	id: 'amm_vacRep_5DetailForm',
        title: "Журнал вакцинации",
	border: false,
	width: 725,
	height: 500,
	maximized: true,
	maximizable: true,        
	codeRefresh: true,
	closeAction: 'hide',
	objectSrc: '/jscore/Forms/Vaccine/amm_vacRep_5DetailForm.js',
	//onHide: Ext.emptyFn,

	 
	initComponent: function() {

		this.ViewFrameRep5Detail = new sw.Promed.ViewFrame(
		{
			id: 'amm_ViewFrameRep5Detail',
			dataUrl: '/?c=Vaccine_List&m=vacFormReport_5Detail',
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
			{name: 'Inoculation_id', type: 'int', header: 'ID', key: true},
                        {name: 'date_vac', type: 'date', header: 'Дата<br>вакцинации ', width: 90},
                        {name: 'Person_id', type: 'int', header: 'Person_id', hidden: true},
                        {name: 'fio', type: 'string', header: 'ФИО', width: 200},
                        {name: 'BirthDay', type: 'date', header: 'Дата<br>рождения ', width: 90},
                        {name: 'lpu_attach_name', type: 'string', header: 'ЛПУ <br>прикрепления ', width: 200},
                        
                        {name: 'LpuRegion_Name', type: 'string', header: 'Участок', width: 200},
                        {name: 'Vaccine_Name', type: 'string', header: 'Наименование<br>вакцины',  id: 'autoexpand'} //width: 400}
                        ],
                        actions:
			[
                            { name:'action_add', hidden: true},
                            {name:'action_edit', hidden: true},
                            {name:'action_view', hidden: true},
                            {name:'action_delete', hidden: true}
                        ],
			onLoadData: function()
			{

			}
//                        ,
//			
//			initGrid: function(params)
//			{
//                                params.DateStart = Ext.getCmp('Date_View').getValue1().format('d.m.Y');
//				dt = Ext.getCmp('Date_View').getValue2();
//				dt.setDate(dt.getDate() + 1);
//				params.DateEnd = dt.format('d.m.Y');
//				params.Lpu_id =getGlobalOptions().lpu_id;
//				Ext.getCmp('amm_ViewFrameRep5Detail').ViewGridPanel.getStore().baseParams = params;
//				Ext.getCmp('amm_ViewFrameRep5Detail').ViewGridPanel.getStore().reload({
//					callback: function() {
//						Ext.getCmp('ListTaskForm_CancelButton').focus(true, 50);
//					}.createDelegate(this)
//				});
//			}
		});
		
//		this.ViewFrameRep5Detail.getGrid().view = new Ext.grid.GridView(
//		{
//			getRowClass : function (row, index)
//			{
//				var cls = '';
//				if (row.get('RecStatus') == 3)
//					cls = cls+'x-grid-rowred ';
//				else if (row.get('RecStatus') == 1)
//					cls = 'x-grid-rowbold ';
//				else if (row.get('RecStatus') == 2)
//					cls = 'x-grid-panel';    
//				else    
//					cls = 'x-grid-rowblue ';
//
//				return cls;
//			}
//		});

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
				text: '<u>З</u>акрыть'
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
		 
		sw.Promed.amm_vacRep_5DetailForm.superclass.initComponent.apply(this, arguments);
	},
	
	show: function() {
		sw.Promed.amm_vacRep_5DetailForm.superclass.show.apply(this, arguments);
                sw.Promed.vac.utils.consoleLog('форма amm_vacRep_5DetailForm');
                sw.Promed.amm_vacRep_5DetailForm.superclass.show.apply(this, arguments);
		var params = new Object();
		params.Num_Str = arguments[0].Num_Str;
		if (!params.Num_Str) {
			Ext.Msg.alert('Ошибка', 'Некорректно переданы данные!');
			this.hide();
			return false;
		}
                params.DateStart = arguments[0].DateStart
                params.DateEnd = arguments[0].DateEnd
                params.Lpu_id = arguments[0].Lpu_id
                params.LpuBuilding_id = arguments[0].LpuBuilding_id
                params.LpuSection_id = arguments[0].LpuSection_id
                params.LpuRegion_id =  arguments[0].LpuRegion_id;
                params.Organized = arguments[0].Organized
                params.lpuMedService_id = arguments[0].lpuMedService_id
                params.MedService_id = arguments[0].MedService_id
                params.start = 0
                params.limit = PageSize
                 Ext.getCmp('amm_vacRep_5DetailForm').title = arguments[0].title
                
                Ext.getCmp('amm_ViewFrameRep5Detail').ViewGridPanel.getStore().baseParams = params;    
                Ext.getCmp('amm_ViewFrameRep5Detail').ViewGridPanel.getStore().load();
//                        load({
//                            callback: function() {
//                                    Ext.getCmp('ListTaskForm_CancelButton').focus(true, 50);
//                            }.createDelegate(this)
//                });

		
//		Ext.getCmp('amm_ViewFrameVacListTasks').initGrid(params);
//		Ext.getCmp('Date_View').focus(true, 50);
	//         initGrid();
	}
});

