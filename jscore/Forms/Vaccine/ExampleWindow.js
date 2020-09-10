/**
* amm_JournalViewWindow - окно просмотра журналов вакцинации
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Марков Андрей
* @version      июль 2010
* @comment      Префикс для id компонентов regv (amm_JournalViewWindow)
*/

/*
sw.Promed.amm_JournalViewWindow = Ext.extend(sw.Promed.BaseForm, 
{
	border: false,
	buttonAlign: 'left',
	closeAction: 'hide',
	height: 500,
	width: 800,
	id: 'amm_JournalViewWindow',
	title: 'Журналы вакцинации', 
	layout: 'border',
	// objectName: 'ammSprVaccineForm',
	// objectSrc: '/jscore/Forms/Vaccine/amm_JournalViewWindow.js',
	
	//maximizable: true,
	maximized: true,
	modal: false,
	//plain: true,
	resizable: false,
	firstTabIndex: 15800,	
//        region: center,
        items: [
            {
                xtype: 'grouplistview',
                region: 'west',
                width: 200,
                split: true,
                collapsible: true
            },
            {
                xtype: 'operlistview',
                region: 'center'
            }
        ],
	
	
        show: function() 
	{
		sw.Promed.amm_JournalViewWindow.superclass.show.apply(this, arguments);
		
	},
	initComponent: function() 
	{
//		var form = this;
//	
//		
//		this.JournalsView = new sw.Promed.BaseFrame(
//		{
//			id: form.id+'JournalsView',
//			region: 'west',
//			width: 250,
//			title:'Список журналов',
//			object: 'Journals'
//			// editformclassname: 'swRegistryEditWindow',
//			// dataUrl: '/?c=RegistryUfa&m=loadRegistry',
//			
//			// autoLoadData: false,
//			// stringfields:
//			
//			}); 
//                this.JournalGrid = new sw.Promed.BaseFrame(
//		{
//			id: form.id+'JournalGrid',
//			region: 'Orient',
////			width: 250,
//			title:'Содержание журнала',
//			object: 'Journals2'
//			// editformclassname: 'swRegistryEditWindow',
//			// dataUrl: '/?c=RegistryUfa&m=loadRegistry',
//			
//			// autoLoadData: false,
//			// stringfields:
//			
//			});       
//			
		
	sw.Promed.amm_JournalViewWindow.superclass.initComponent.apply(this, arguments);
	}
	});
        */
       
    /*   
 Ext.onReady(function(){

   var win = new Ext.Window({
        title: "title",
        border: false,
        width: 400,
        height: 400,
        layout:'fit',
        items: [
            new Ext.form.FormPanel({
                frame: true,
                items: {xtype: 'textfield'}
            })
        ]
 });

 win.show();
});
*/

sw.Promed.amm_JournalViewWindow = Ext.extend(Ext.Window, {

        title: "Журналы вакцинации!!",
        border: false,
//        width: 400,
//        height: 400,
        maximized: true,
	maximizable: false,        
        layout:'fit',
        codeRefresh: true,
      /*
        items: [ 
            new Ext.form.FormPanel({
                frame: true,
                items: {xtype: 'textfield'}
            })
        ], */
   
        
     initComponent: function() {
         
//         this.FormPanel =
//                    new Ext.form.FormPanel({
//					bodyStyle: 'padding: 5px',
//					border: true,
//					frame: true,
//                                         autoScroll: true,
//					id: 'amm_Panel1',
//                                        autohight: true,
////                                        layout: 'column',
//					items: [
//                                            new Ext.form.FormPanel({
//                                                frame: true,
//                                                items: {xtype: 'textfield'}
//                                            })
//                                           ]
//                    })
         
         sw.Promed.amm_JournalViewWindow.superclass.initComponent.apply(this, arguments);
     },
      show: function() {
		sw.Promed.amm_JournalViewWindow.superclass.show.apply(this, arguments);
      }          

 });

