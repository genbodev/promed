/**
* АРМ медсестры процедурного кабинета
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @autor		Tagir Nigmatullin, Ufa
* @copyright    Copyright (c) 2011 Swan Ltd.
* @version      октябрь 2013
*/

var PageSize = 50;
var Calc = 0;

var swPromedActions = {

PresenceVacForm: {
                    text: 'Национальный календарь прививок',
                    tooltip: 'Национальный календарь прививок',
                    iconCls : 'pol-immuno16',
                    handler: function()
                    {
                            getWnd('amm_SprNacCalForm').show();
                    }
            },
SprVaccineForm: {
                    text: 'Справочник вакцин',
                    tooltip: 'Справочник вакцин',
                    iconCls : 'pol-immuno16',
                    handler: function()
                    {
                            getWnd('amm_SprVaccineForm').show();
                    }
            },
        
CountingKartVac: {
                    text: 'Подсчет введенных карт',
                    tooltip: 'Подсчет введенных карт',
					hidden: getRegionNick().inlist([ 'kz' ]),
                    iconCls : 'report32',
                    handler: function()
                    {
                            getWnd('amm_SprVaccineForm').show();
                    }
            }
};			     
        
        
sw.Promed.amm_WorkPlaceEpidemWindow = Ext.extend(sw.Promed.swWorkPlaceWindow,
{
	id: 'amm_WorkPlaceEpidemWindow',
	ARMType: '',		  
	searchParams: '',   
	buttonPanelActions: {
			action_vacSpr: 
			{
				nn: 'action_vacSpr',
				tooltip: 'Справочники',
				text: 'Справочники',
				iconCls : 'immunoprof32',
                                disabled: false, 
				menuAlign: 'tr?' ,
				menu: new Ext.menu.Menu({
					items: [                                               
//						swPromedActions.VacPresence,
						swPromedActions.PresenceVacForm,
						swPromedActions.SprVaccineForm
                                               
												]
				})
							  
			},
                        action_vacARM: 
                        {
					text: 'Кабинет вакцинации',
					tooltip: 'Кабинет вакцинации',
					icon : 'img/icons/vac32.png',
                                        //iconCls : 'vac-plan32',
					handler: function()
					{
					var params = new Object();
                                        params.MedService_id = 1;
                                        params.ARMType = Ext.getCmp('amm_WorkPlaceEpidemWindow').ARMType;
                                            getWnd('amm_WorkPlaceVacCabinetWindow').show(params);
					}
				},
                         action_CountingKartVac: {
                                    text: 'Подсчет введенных карт',
                                    tooltip: 'Подсчет введенных карт',
									hidden: getRegionNick().inlist([ 'kz' ]),
//                                    iconCls : 'analyzer32',
                                     iconCls : 'monitoring32',
//                                    iconCls : 'report32',
//                                    iconCls : 'farm-inv16',
//                                    vac-plan32
                                    handler: function()
                    {
                            var ch = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp';
                            Ext.getCmp('GridLpu4Report').setColumnHidden('kol', false);
                            Ext.getCmp('GridLpu4Report').setColumnHidden('kol0', false);
                            Calc = 1;
                                        Ext.getCmp('GridLpu4Report').ViewGridPanel.getStore().load({
					params: {
						calc:1, 
                                                start:0,
                                                limit:PageSize,
                                                ARMType:Ext.getCmp('amm_WorkPlaceEpidemWindow').ARMType
					 }
                        });
                }
                             
                         }
		},
                
			   
	  initComponent: function() {
		
			var form = this;
			this.onKeyDown = function (inp, e) {
			if (e.getKey() == Ext.EventObject.ENTER) {
				e.stopEvent();
				this.doSearch();
			}
		}.createDelegate(this);

			this.GridPanel =  new sw.Promed.ViewFrame({
			id: 'GridLpu4Report',
			region: 'center',
			autoExpandColumn: 'autoexpand',
			paging: true,
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
				//{name: 'action_refresh', hidden: true},
                               { 
                                name:'action_refresh', 
				//text: VAC_MENU_OPEN,
				handler: function() {
                                    Ext.getCmp('GridLpu4Report').ViewGridPanel.getStore().reload({
                                            params: {
                                                    calc:Calc,
                                                    start:0,
                                                    limit:PageSize,
                                                    ARMType:Ext.getCmp('amm_WorkPlaceEpidemWindow').ARMType 
                                             }
                                     }); 

                                    }
                               }
			],
				   
			
			onLoadData: function(sm, index, record) {
				
                            var Kol = Ext.getCmp('GridLpu4Report').ViewGridStore.reader.jsonData.kol
                            var Kol0 = Ext.getCmp('GridLpu4Report').ViewGridStore.reader.jsonData.kol0                                  
                            
                            if ( Calc == 1) {
                                var ch = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp';
                                      var Str = 'Прикреплено населения ' + Kol0 + ' чел., кол-во карт - ' + Kol +  ' шт.'+ ch+ ch+ ch
                                      Ext.getCmp('GridLpu4Report').ViewGridPanel.getBottomToolbar().displayMsg = Str +'Отображаемые строки {0} - {1} из {2}'                          
                                 }
                                 else {
                                     Ext.getCmp('GridLpu4Report').ViewGridPanel.getBottomToolbar().displayMsg = 'Отображаемые строки {0} - {1} из {2}' 
                                 }
			},
			pageSize: PageSize,
                        start:0,                        
                        root: 'data',
                        cls: 'txtwrap',
                        stringfields:
                            [
                                {name: 'lpu_id', type: 'int', header: 'ID', key: true},   	
                                {name: 'Lpu_Nick', type: 'string', header: 'Краткое наименование', footer: 'Итого:',  width: 300},	
                                {name: 'Lpu_Name', type: 'string', header: 'Полное наименование',  width: 400},
                                {name: 'LpuRegion_Name', type: 'string', header: 'Участок',  width: 400},
                                {name: 'kol0', type: 'string', header: 'Прикреплено',  width: 100, align: 'right', summaryType: 'sum'},
                                {name: 'kol', type: 'string', header: 'Количество карт',  width: 100, align: 'right', summaryType: 'sum'},
                                            // hidetrue},	   
                                {name: 'UAddress_Address', type: 'string', header: 'Адрес / Отделение', id: 'autoexpand'}
//                                {name: 'LpuBuilding_name', type: 'string', header: 'Отделение', width: 400} 
                                
                                , 
                            ],
                                                              
                        dataUrl: '/?c=VaccineCtrl&m=GeCountingKartVac',
                        title: 'Список подотчетных медицинских организаций / Список участков'
		});
				
		
				this.GridPanel.getGrid().view = new Ext.grid.GridView(
		{

			getRowClass : function (row, index){

				
				var arrCls = [];
				switch (row.get('StatusType_id')){
					case 0:// назначено
						arrCls.push('x-grid-rowbold');
					  break;
					case 1:// исполнено
						arrCls.push('x-grid-rowgreen');
						//arrCls.push('x-grid-rowbold');
					  break;
				}
				
				switch (row.get('StatusSrok_id')){
					case -1:// просрочено
						arrCls.length = 0;
						arrCls.push('x-grid-rowred');
					  break;
					case 0:// норм
					  break;
				}
				return arrCls.join(' ');
			}
		});
		  
		sw.Promed.amm_WorkPlaceEpidemWindow.superclass.initComponent.apply(this, arguments);
	},
show: function()
	{
		sw.Promed.amm_WorkPlaceEpidemWindow.superclass.show.apply(this, arguments);
       
		// Свои функции при открытии
    	Ext.getCmp('amm_WorkPlaceEpidemWindow').ARMType = arguments[0].ARMType;
    	Ext.getCmp('amm_WorkPlaceEpidemWindow').searchParams = {ARMType: arguments[0].ARMType};
		//  Скрываем фильтр
             Ext.getCmp('amm_WorkPlaceEpidemWindow').FilterPanel.removeAll();
             //Ext.getCmp('amm_WorkPlaceEpidemWindow').timeMenu.
               
               
            sw.Promed.vac.utils.consoleLog('show');
            Calc = 0;
            if (Calc == 0) {
                Ext.getCmp('GridLpu4Report').setColumnHidden('kol', true);
                Ext.getCmp('GridLpu4Report').setColumnHidden('kol0', true);
                    if (Ext.getCmp('amm_WorkPlaceEpidemWindow').ARMType == 'epidem')                 
                        {                   
                         Ext.getCmp('GridLpu4Report').setColumnHidden('Lpu_Name', false);   
                         Ext.getCmp('GridLpu4Report').setColumnHidden('Lpu_Nick', false);  
                         Ext.getCmp('GridLpu4Report').setColumnHeader('UAddress_Address', 'Адрес / Отделение');
//                         Ext.getCmp('GridLpu4Report').setColumnHidden('UAddress_Address', false); 

                         Ext.getCmp('GridLpu4Report').setColumnHidden('LpuRegion_Name', true);   
                        with(this.LeftPanel.actions) {
                            action_vacARM.setHidden(true);
                        }
//                         Ext.getCmp('GridLpu4Report').setColumnHidden('LpuBuilding_name', true);  
//                         Ext.getCmp('GridLpu4Report').setTitle('Список подотчетных медицинских организаций');
                          
                        }
                    else {
                           Ext.getCmp('GridLpu4Report').setColumnHidden('Lpu_Name', true);   
                           Ext.getCmp('GridLpu4Report').setColumnHidden('Lpu_Nick', true);  
                           Ext.getCmp('GridLpu4Report').setColumnHeader('UAddress_Address', 'Подразделение');
//                           Ext.getCmp('GridLpu4Report').setColumnHidden('UAddress_Address', true); 
                            
                           Ext.getCmp('GridLpu4Report').setColumnHidden('LpuRegion_Name', false);   
                           with(this.LeftPanel.actions) {
                            action_vacARM.show();
                        }
//                           Ext.getCmp('GridLpu4Report').setColumnHidden('LpuBuilding_name', false);
//                           Ext.getCmp('GridLpu4Report').setTitle('Список участков');
                    }    
//                            alert('epidem');
                           Ext.getCmp('GridLpu4Report').ViewGridPanel.getStore().load({
                                params: {
                                        calc:Calc,
                                        start:0,
                                        limit:PageSize,
                                        ARMType:Ext.getCmp('amm_WorkPlaceEpidemWindow').ARMType 
                                 }

                         }); 
//                        }
//                        else {alert('epidem_MO')}
              } 
   
            }
		 
});