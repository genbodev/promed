/**
* Управление предметами наблюдения регистра БСК
*
*
* @package      All
* @access       admin
* @autor		Васинский Игорь
* @version      20.08.2014
*/

sw.Promed.ufa_AdminBSKViewForm = Ext.extend(sw.Promed.BaseForm,
{
	title: 'Регистр БСК: Администрирование',
	maximized: true,
	maximizable: false,
	shim: false,
	buttonAlign: "right",
	objectName: 'ufa_AdminBSKViewForm',
	closeAction: 'hide',
	id: 'ufa_AdminBSKViewForm',
	objectSrc: '/jscore/Forms/Admin/Ufa/ufa_AdminBSKViewForm.js',
    layout : 'fit',
    checkSymbolTest : 'undefined',    
    
    unselectRow: function(grid){
        grid.getSelectionModel().clearSelections();
    },
    
    initComponent: function()
	{   
	   
        /** Предметы наблюдения */
             
		this.GridObjects = new sw.Promed.ViewFrame({
			id: 'GridObjects', 
			region: 'west',
			contextmenu: false,
            border: false,
            //height: 900,
			height: Ext.getBody().getHeight()*0.7,
            object: 'GridObjects',
			dataUrl: '/?c=ufa_BSK_Register&m=getListObjects',
			autoLoadData: true,
            focusOnFirstLoad: false,
			stringfields: [
				{name: 'BSKObject_id', type: 'int', header: 'ID'},
                {name: 'MorbusType_id', type: 'int',hidden:true},
                {name: 'BSKObject_id', type: 'int', header: 'id', width:20},
				{name: 'MorbusType_name', header: 'Наименование', id: 'autoexpand'}
			],
 			actions: [
				{
                 name:'action_add', 
                 hidden: true,
                 text: 'Создать', 
                 disabled: true,
                 handler: function() {       
                        var wnd = Ext.getCmp('ufa_AdminBSKViewForm');
                        wnd.addObjectPanel.show();
                        wnd.editObjectPanel.hide();
                    }//.createDelegate(this) 
                },{
                 name:'action_edit', 
                 hidden: true,
                 text: 'Изменить', 
                 handler: function() {
                        var wnd = Ext.getCmp('ufa_AdminBSKViewForm');
                        wnd.editObjectPanel.show();
                        wnd.addObjectPanel.hide();
                    }//.createDelegate(this)
                    , 
                 disabled: true 
                },{
                 name:'action_delete', 
                 hidden: true,
                 text: 'Удалить', 
                 disabled: true,
                 handler: function(){
                     var record_id = Ext.getCmp('GridObjects').getGrid().getSelectionModel().getSelected().id;
 
                     Ext.Ajax.request({
        		 		url: '/?c=ufa_BSK_Register&m=deleteObject',
        				params: {
        				    object_id : record_id
        				},
        				callback: function(options, success, response) {
                            if (success === true) {
                              Ext.getCmp('ufa_AdminBSKViewForm').showMsg('Предмет наблюдения удалён');
                              Ext.getCmp('GridObjects').getGrid().getStore().load();
                              //Ext.getCmp('ufa_AdminBSKViewForm').getTreeObjects(); 
        					}
                            
        				}
        		    });
                     
                 },  disabled: true },
				{name:'action_view', hidden: true},
				{name:'action_refresh', hidden: true},
				{name:'action_print', hidden: true}
			], 
            
			onLoadData: function() {

			},
            onDblClick: function() {
                return;
            }
        });  
       
     
       
 	   /**  Форма добавления нового предмета наблюдения */
       
	   	this.addObjectPanel = new Ext.Panel({
	   	    title: 'Добавление',
	   	    id : 'addObjectPanel',
            iconCls : 'add16',
            layout : 'anchor',
            bodyStyle: 'padding:5px; border: 0px;',
            border: false,
            hidden: true,
            frame : true,
            items : [
                {
                    xtype: 'panel',
                    html: 'Наименование',
                    bodyStyle: 'padding:5px; border: 0px;',
                },              
                {
                 xtype: 'textfield',
                 id: 'nameObject',
                 name: 'nameObject',
                 border: false,
                 anchor: '100%',
                 allowBlank: false            
                } 
            ],
            buttons : [
                {
                  text: 'Добавить',
                  id : 'addobj',
                  iconCls: 'save16',
                  handler: function() {
                              var newObject = Ext.getCmp('nameObject');
                              
                              if(newObject.getValue().length< 2){
                     			  Ext.getCmp('ufa_AdminBSKViewForm').showMsg('Необходимо ввести наименование предмета наблюдения (не менее 3х символов)');
                    			  return false;                               
                              }
                              else{
                                  Ext.Ajax.request({
                        		 		url: '/?c=ufa_BSK_Register&m=addObject',
                        				params: {
                        				    BSKObject_name : newObject.getValue()
                        				},
                        				callback: function(options, success, response) {
                                            if (success === true) {
                                              Ext.getCmp('ufa_AdminBSKViewForm').showMsg('Предмет наблюдения успешно добавлен');
                                              
                                              newObject.setValue('');
                                              Ext.getCmp('GridObjects').getGrid().getStore().load();
                                              //Ext.getCmp('ufa_AdminBSKViewForm').getTreeObjects(); 
                                			  return false;
                        					}
                                            
                        				}
                        		  });
                              }
                           } 
                },{
                  text: 'Отмена',
                  id : 'cancelobj',
                  iconCls: 'cancel16',
                  handler: function() {
                               Ext.getCmp('addObjectPanel').hide();    
                           } 
                 },
                             
            ]
	   	}); 
        
        /** Форма редактирования предмета наблюдения */

 	   	this.editObjectPanel = new Ext.Panel({
	   	    title: 'Редактирование',
	   	    id : 'editObjectPanel',
            iconCls : 'edit16',
            layout : 'anchor',
            bodyStyle: 'padding:5px; border: 0px',
            border: false,
            hidden: true,
            frame : true,
            items : [  
                {
                    xtype: 'panel',
                    id : 'editobjpanel',
                    html: 'Наименование',
                    bodyStyle: 'padding:5px; border: 0px',
                },                     
                {
                 xtype: 'textfield',
                 id: 'newNameObject',
                 name: 'newNameObject',
                 border: false,
                 anchor: '100%',
                 allowBlank: false            
                } 
            ],
             buttons : [
                {
                  text: 'Сохранить',
                  id : 'editobjpanel',
                  iconCls: 'save16',
                  handler: function() {
                              var editObject = Ext.getCmp('newNameObject');
                              
                              if(editObject.getValue().length< 2){
                                  Ext.getCmp('ufa_AdminBSKViewForm').showMsg('Необходимо ввести наименование предмета наблюдения (не менее 3х символов)');
                    			  return false;                               
                              }
                              else{
                                  //console.log('>>>', Ext.getCmp('GridObjects').getGrid().getSelectionModel().getSelected());
                                
                                  Ext.Ajax.request({
                        		 		url: '/?c=ufa_BSK_Register&m=editObject',
                        				params: {
                        				    nameObject : editObject.getValue(),
                                            MorbusType_id : Ext.getCmp('GridObjects').getGrid().getSelectionModel().getSelected().data.MorbusType_id,
                                            object_id: Ext.getCmp('GridObjects').getGrid().getSelectionModel().getSelected().data.BSKObject_id
                        				},
                        				callback: function(options, success, response) {
                                            if (success === true) {
                                              Ext.getCmp('ufa_AdminBSKViewForm').showMsg('Предмет наблюдения успешно изменён');
                                              
                                              Ext.getCmp('GridObjects').getGrid().getStore().load();
                                              //Ext.getCmp('ufa_AdminBSKViewForm').getTreeObjects(); 
                                              Ext.getCmp('editObjectPanel').hide();
                                			  return false;
                        					}
                                            
                        				}
                        		  });
                              }
                           } 
                },{
                  text: 'Отмена',
                  id : 'canceleditpanel',
                  iconCls: 'cancel16',
                  handler: function() {
                               Ext.getCmp('editObjectPanel').hide();    
                           } 
                 }            
            ]           
        });     

        this.GridObjects.getGrid().on(
            'rowclick', 
                function(){
                    
                    var editObject = Ext.getCmp('newNameObject');
                    var targetObject = this.getSelectionModel().getSelected(); 
                      
                    this.selected = targetObject;
                    editObject.setValue(this.selected.data.MorbusType_name);
                    
                    Ext.getCmp('GridObjects').getAction('action_edit').setDisabled(false);
                    Ext.getCmp('GridGroupTypes').getAction('action_add').setDisabled(false);
                    Ext.getCmp('GridGroupTypes').getAction('action_edit').setDisabled(true);
                    Ext.getCmp('GridGroupTypes').getAction('action_delete').setDisabled(true);  
                    Ext.getCmp('GridTypes').getAction('action_add').setDisabled(true);
                    Ext.getCmp('GridTypes').getAction('action_edit').setDisabled(true);
                    Ext.getCmp('GridTypes').getAction('action_delete').setDisabled(true);                    

                    Ext.getCmp('GridGroupTypes').getGrid().getStore().load({
                        params: {
                                 BSKObject_id: Ext.getCmp('GridObjects').getGrid().selected.data.BSKObject_id
                                }    
                    });
                    
                    Ext.getCmp('GridValues').getGrid().getStore().removeAll();
                    Ext.getCmp('GridTypes').getGrid().getStore().removeAll();
                    Ext.getCmp('GridObjectsLinks').getGrid().getStore().removeAll();
                }    
        );  
        
        this.GridObjects.getGrid().on(
            'render', 
                function(){
                    Ext.getCmp('addObjectPanel').hide();
                    Ext.getCmp('editObjectPanel').hide();
                }
        );            

        /** Группы сведений предметов наблюдения */
             
		this.GridGroupTypes = new sw.Promed.ViewFrame({
			id: 'GridGroupTypes',
			region: 'center',
			contextmenu: false,
            border: false,
			height: Ext.getBody().getHeight()*0.7,
            object: 'GridGroupTypes',
			dataUrl: '/?c=ufa_BSK_Register&m=getListGroupTypes',
			autoLoadData: false,
            focusOnFirstLoad: false,
			stringfields: [
				{name: 'BSKObservElementGroup_id', type: 'int', header: 'ID', hidden: true},
                {name: 'BSKObservElementGroup_id', type: 'int', header: 'id', width:20},
				{name: 'BSKObservElementGroup_name', header: 'Наименование', id: 'autoexpand'},
			],
 			actions: [
				{name:'action_add', text: 'Создать', 
                 handler: function() { 
                    this.addGroupTypePanel.show();
                    this.editGroupTypePanel.hide()
                 }.createDelegate(this)
                 , disabled: true, },
                {name:'action_edit', text: 'Изменить', 
                 handler: function() { 
                    this.editGroupTypePanel.show();
                    this.addGroupTypePanel.hide()
                    }.createDelegate(this)
                    , disabled: true, },
                {name:'action_delete', text: 'Удалить', disabled: true,
                handler : function()
                {

                                
                    sw.swMsg.show({
            			buttons: Ext.Msg.YESNO,
            			fn: function(buttonId, text, obj) {
            				if ( buttonId == 'yes' ) {
                                /*   */    

                                var BSKObservElementGroup_id = Ext.getCmp('GridGroupTypes').getGrid().selected.data.BSKObservElementGroup_id;

                                 Ext.Ajax.request({
                    		 		url: '/?c=ufa_BSK_Register&m=deleteGroupType',
                    				params: {
                    				    BSKObservElementGroup_id : BSKObservElementGroup_id
                    				},
                    				callback: function(options, success, response) {
                                        if (success === true) {
                                            
            
                                                        
                                                           
                                          Ext.getCmp('ufa_AdminBSKViewForm').showMsg('Группа сведений удалёна');  
            
                                          Ext.getCmp('GridGroupTypes').getGrid().getStore().load();
            
                    					}
                                        
                    				}
                    		    });
                                 /*   */
            				}
            			}.createDelegate(this),
            			icon: Ext.MessageBox.QUESTION,
            			msg: 'Удалить группу типов сведений ?',
            			title: 'Вопрос'
            		});
            
            		return true;  
    
                 },  disabled: true },
				{name:'action_view', hidden: true},
				{name:'action_refresh', hidden: true},
				{name:'action_print', hidden: true}
			],           
			onLoadData: function() {

			},
            onDblClick: function() {
                return;
            }
        }); 
        

        
        this.GridGroupTypes.getGrid().on(
            'rowclick', 
                function(){
                    Ext.getCmp('ufa_AdminBSKViewForm').addGroupTypePanel.hide();
                    Ext.getCmp('ufa_AdminBSKViewForm').editGroupTypePanel.hide(); 
                    
                    //Ext.getCmp('ufa_AdminBSKViewForm').GridTypes.getGrid().getSelectionModel().deselectRange(0,20);
                     
                    var targetGroupType = this.getSelectionModel().getSelected(); 
                    Ext.getCmp('ufa_AdminBSKViewForm').checkSymbolTest = 'undefined';
                    
                    Ext.getCmp('GridValues').getGrid().getStore().removeAll();
                    Ext.getCmp('GridTypes').getGrid().getStore().removeAll();
                    Ext.getCmp('GridObjectsLinks').getGrid().getStore().removeAll();                      
                    
                    //if(targetGroupType.data.BSKObservElementGroup_id != null){
                        this.selected = targetGroupType;
                        Ext.getCmp('newNameGroupType').setValue(this.selected.data.BSKObservElementGroup_name);
                        Ext.getCmp('GridGroupTypes').getAction('action_edit').setDisabled(false);
                        Ext.getCmp('GridGroupTypes').getAction('action_delete').setDisabled(false);   
                        
                        if(typeof(targetGroupType.json) == 'object'){
                            Ext.getCmp('GridTypes').getAction('action_edit').setDisabled(true);  
                            Ext.getCmp('GridTypes').getAction('action_delete').setDisabled(true);              
                            Ext.getCmp('GridTypes').getAction('action_add').setDisabled(false);
        
                            Ext.getCmp('GridValues').getGrid().getStore().removeAll();
                            Ext.getCmp('GridTypes').getGrid().getStore().removeAll();
                            
                            //console.log('>>>', Ext.getCmp('GridObjects'));
                           
                            Ext.getCmp('GridTypes').getGrid().getStore().load({
                                params: {
                                         BSKObservElementGroup_id: Ext.getCmp('GridGroupTypes').getGrid().getSelectionModel().getSelected().data.BSKObservElementGroup_id,
                                         BSKObject_id : Ext.getCmp('GridObjects').getGrid().getSelectionModel().getSelected().data.BSKObject_id
                                        }    
                            });
                            
                            Ext.getCmp('GridObjectsLinks').getGrid().getStore().removeAll();                                                                                    
                          
                            
                        }                                             
                    //}    
                    /* 
                    
                    if(targetGroupType.data.BSKObservElementGroup_id != null){
                        this.selected = targetGroupType;
                        
                        Ext.getCmp('newNameGroupType').setValue(this.selected.data.BSKObservElementGroup_name);
                        
                        Ext.getCmp('GridGroupTypes').getAction('action_edit').setDisabled(false);
                        Ext.getCmp('GridGroupTypes').getAction('action_delete').setDisabled(false);
                        
                        
                        
                        if(typeof(targetGroupType.json) == 'object'){
                            Ext.getCmp('GridTypes').getAction('action_edit').setDisabled(true);  
                            Ext.getCmp('GridTypes').getAction('action_delete').setDisabled(true);              
                            Ext.getCmp('GridTypes').getAction('action_add').setDisabled(false);

                            
                            
                            Ext.getCmp('GridTypes').getGrid().getStore().load({
                                params: {
                                         BSKObservElementGroup_id: Ext.getCmp('GridGroupTypes').getGrid().selected.data.BSKObservElementGroup_id
                                        }    
                            });
                            
                        }
                    }
                    */
                    //Ext.getCmp('GridValues').getGrid().getStore().load({params:{Type_id:0}});
                    
                }    
        );  
              
	   	this.addGroupTypePanel = new Ext.Panel({
	   	    title: 'Добавление',
	   	    id : 'addGroupTypePanel',
            iconCls : 'add16',
            layout : 'anchor',
            bodyStyle: 'padding:5px; border: 0px',
            border: false,
            frame : true,
            items : [
                {
                    xtype: 'panel',
                    id : 'addgrouptypepanel',
                    html: 'Наименование',
                    bodyStyle: 'padding:5px; border: 0px;',
                },
                {
                 xtype: 'textfield',
                 id: 'nameGroupType',
                 layout : 'anchor',
                 name: 'nameGroupType',
                 border: false,
                 anchor: '100%',
                 allowBlank: false       
                }
            ],

            buttons : [
                {
                  text: 'Добавить',
                  id : 'addgrouptype',
                  iconCls: 'save16',
                  handler: function() {
                              var newGroupType = Ext.getCmp('nameGroupType');
                        
                              if(newGroupType.getValue().length< 2){
                                  Ext.getCmp('ufa_AdminBSKViewForm').showMsg('Необходимо ввести наименование группы сведений (не менее 3х символов)');  
                    			  return false;                               
                              }                              
                              else{

                                  Ext.Ajax.request({
                        		 		url: '/?c=ufa_BSK_Register&m=addGroupType',
                        				params: {
                        				    BSKObject_id : Ext.getCmp('GridObjects').getGrid().selected.data.BSKObject_id,
                        				    BSKObservElementGroup_name : newGroupType.getValue()
                        				},
                        				callback: function(options, success, response) {

                                            if (success === true) {
                                              Ext.getCmp('ufa_AdminBSKViewForm').showMsg('Группа сведений успешно добавлена');    

                                              newGroupType.setValue('');
                                              Ext.getCmp('GridGroupTypes').getGrid().getStore().load();
                                              Ext.getCmp('GridObjects').getGrid().getStore().load();
                                              //Ext.getCmp('ufa_AdminBSKViewForm').getTreeObjects(); 
                                			  return false;
                        					}
                        				}
                        		  });
                   
                              }
                           } 
                },{
                  text: 'Отмена',
                  id : 'cancelgrouptype',
                  iconCls: 'cancel16',
                  handler: function() {
                               Ext.getCmp('addGroupTypePanel').hide();    
                           } 
                 }            
            ]
	   	}); 

        /**  Форма редактирования группы сведений */

 	   	this.editGroupTypePanel = new Ext.Panel({
	   	    title: 'Редактирование',
	   	    id : 'editGroupTypePanel',
            iconCls : 'edit16',
            layout : 'anchor',
            bodyStyle: 'padding:5px; border: 0px',
            border: false,
            frame : true,
            items : [  
                {
                    xtype: 'panel',
                    id : 'editgrouptypepanel',
                    html: 'Наименование',
                    bodyStyle: 'padding:5px; border: 0px',
                },                     
                {
                 xtype: 'textfield',
                 id: 'newNameGroupType',
                 name: 'newNameGroupType',
                 border: false,
                 anchor: '100%',
                 allowBlank: false            
                } 
            ],
             buttons : [
                {
                  text: 'Сохранить',
                  id : 'savegrouptype',
                  iconCls: 'save16',
                  handler: function() {
                              var editGroupType = Ext.getCmp('newNameGroupType');
                              
                              if(editGroupType.getValue().length< 2){
                                  Ext.getCmp('ufa_AdminBSKViewForm').showMsg('Необходимо ввести наименование предмета наблюдения (не менее 3х символов)'); 
                    			  return false;                               
                              }
                              else{
                                  Ext.Ajax.request({
                        		 		url: '/?c=ufa_BSK_Register&m=editGroupType',
                        				params: {
                        				    BSKObservElementGroup_name : editGroupType.getValue(),
                                            BSKObservElementGroup_id : Ext.getCmp('GridGroupTypes').getGrid().selected.data.BSKObservElementGroup_id
                        				},
                        				callback: function(options, success, response) {
                                            if (success === true) {
                                                
                                              Ext.getCmp('ufa_AdminBSKViewForm').showMsg('Группа сведений успешно изменёна');   
                                              
                                              Ext.getCmp('GridGroupTypes').getGrid().getStore().load();
                                              //Ext.getCmp('ufa_AdminBSKViewForm').getTreeObjects(); 
                                              Ext.getCmp('editGroupTypePanel').hide();
                                			  return false;
                        					}
                                            
                        				}
                        		  });
                              }
                           } 
                },{
                  text: 'Отмена',
                  id : 'canceleditgrouptype',
                  iconCls: 'cancel16',
                  handler: function() {
                               Ext.getCmp('editGroupTypePanel').hide();    
                           } 
                 }            
            ]           
        });     

        var chbx =  new Ext.grid.CheckboxSelectionModel({
				singleSelect: true
            });
            
		this.GridObjectsLinks = new sw.Promed.ViewFrame({
			id: 'GridObjectsLinks',
            title : 'Общий тип сведений для следующих предметов наблюдения',
            //selectionModel: 'multiselect',
			region: 'center',
            isMan : false,
            disabled : true,
			contextmenu: false,
            border: false,
			height: 300,
            object: 'GridObjects',
			dataUrl: '/?c=ufa_BSK_Register&m=getListObjects',
			autoLoadData: false,
            multi: true,
            focusOnFirstLoad: false,
			stringfields: [
				{name: 'BSKObject_id', type: 'int', header: 'ID'},
                {name: 'MorbusType_id', type: 'int',hidden:true},
                {name: 'BSKObject_id', type: 'int', header: 'id', width:20, hidden: true},
				{name: 'MorbusType_name', header: 'Наименование', id: 'autoexpand'},
			],
  			actions: [
				{name:'action_add', text: 'Создать', hidden: true},
                {name:'action_edit', text: 'Изменить',  hidden: true},
                {name:'action_delete',  text: 'Удалить', hidden: true },
				{name:'action_view', hidden: true},
				{name:'action_refresh', hidden: true},
				{name:'action_print', hidden: true}
			],   
            toolbar : false          
        });  


        /*Ext.getCmp('GridObjectsLinks').getGrid().getSelectionModel().on(
            'rowselect', 
            function(sm, rowIndex, r){
                
                //Добавить id вопроса - и в БД
                //исправить: срабатывает deselect - если отмечена и по новой select
                
                //Так же при добавлении вопроса - писать id предмета наблюдения в БД (видимо в links) - или добавить поле к таблице вопросов (типов)
                
                //console.log('CLICK по chbx', Ext.getCmp('GridObjectsLinks').isMan );
                
                if( Ext.getCmp('GridObjectsLinks').isMan === true){
                    
                        //console.log('>>>', Ext.getCmp('GridTypes').getGrid().getSelectionModel().getSelected().data.BSKObservElement_id);
                        //console.log('SELECT', r, Ext.getCmp('GridObjectsLinks').isMan);
                        //console.log('SET: ', {BSKObject_id : BSKObject_id,BSKObservElement_id : BSKObservElement_id,action : 'set'});
                                             
                        var BSKObject_id = r.data.BSKObject_id;
                        var BSKObservElement_id = Ext.getCmp('GridTypes').getGrid().getSelectionModel().getSelected().data.BSKObservElement_id;    

                         Ext.Ajax.request({
            		 		url: '/?c=ufa_BSK_Register&m=manageLinks',
            				params: {
            				    BSKObject_id : BSKObject_id,
                                BSKObservElement_id : BSKObservElement_id,
                                action : 'set'
            				},
            				callback: function(options, success, response) {
                                
            				}
            		    });       
                }
            }
        );
          
        Ext.getCmp('GridObjectsLinks').getGrid().getSelectionModel().on(
            'rowdeselect', 
            function(sm, rowIndex, r){
                //console.log('CLICK по chbx', Ext.getCmp('GridObjectsLinks').isMan );
                
                if( Ext.getCmp('GridObjectsLinks').isMan === true){
                    
                         //console.log('>>>', Ext.getCmp('GridTypes').getGrid().getSelectionModel().getSelected().data.BSKObservElement_id);
                         //console.log('DESELECT', r, Ext.getCmp('GridObjectsLinks').isMan);
                         //console.log('UNSET: ', {BSKObject_id : BSKObject_id,BSKObservElement_id : BSKObservElement_id,action : 'unset'});                            
                            
                         var BSKObject_id = r.data.BSKObject_id;
                         var BSKObservElement_id = Ext.getCmp('GridTypes').getGrid().getSelectionModel().getSelected().data.BSKObservElement_id;    

                        
                         Ext.Ajax.request({
            		 		url: '/?c=ufa_BSK_Register&m=manageLinks',
            				params: {
            				    BSKObject_id : BSKObject_id,
                                BSKObservElement_id : BSKObservElement_id,
                                action : 'unset'
            				},
            				callback: function(options, success, response) {
                                
            				}
            		    });       
                }
            }
        );      */              
        
        /** Типы сведений предметов наблюдения */
             
		this.GridTypes = new sw.Promed.ViewFrame({
		   
			id: 'GridTypes',
			region: 'center',
			contextmenu: false,
            border: false,
            region: 'center',
            clicksToEdit: false,
            layout: 'fit',            
			height: Ext.getBody().getHeight()*0.7-120,
            object: 'GridTypes',
			dataUrl: '/?c=ufa_BSK_Register&m=getListTypes',
			autoLoadData: false,
            focusOnFirstLoad: false,                      
			stringfields: [
				{name: 'BSKObservElement_id', type: 'int', hidden: true},
                {name: 'BSKObservElementFormat_id',type: 'int', hidden: true},     
                {name: 'BSKObservElement_id', type: 'int',  header: 'id', width:40},           
				{name: 'BSKObservElement_name', header: 'Наименование', id: 'autoexpand'},
                {name: 'BSKObservElementFormat_name', header: 'формат', width:70, align:'center'},
                {name: 'BSKObservElement_symbol', header: 'обозначение', width:60, align:'center'},
                {name: 'BSKObservElement_formula', header: 'формула', width:60},
                {name: 'BSKObservElement_Sex_id', header: 'пол', width:60, align:'center', 
                        renderer: function(v,p,r){
                                    if(r.data.BSKObservElement_id != null){
                                        if(v == 3) 
                                            return ''; 
                                        else {
                                            return (v == 1) ? 'М' : 'Ж'
                                        }
                                    }
                }},
                {name: 'BSKObservElement_stage', header: 'этап', width:60, align:'center'
                /*, renderer: function(v,p,r){return (v == 1) ? 'Анкета' : 'Доп. ислед.'}*/},
                {name: 'BSKObservElement_minAge', header: 'от', width:60, align:'center'},
                {name: 'BSKObservElement_maxAge', header: 'до', width:60, align:'center'},
                /**
                {name: 'BSKObservElement_IsRequire', header: 'обязателен', width:60, align:'center', 
                        renderer: function(v,p,r){
                            if(r.data.BSKObservElement_id != null){
                                return (v==1) ? 'Да' : 'Нет';
                            }
                        }
                }
                */
                {name: 'BSKObservElement_Anketa', header: 'Вопрос', hidden: true}
			],
 			actions: [
				{name:'action_add', text: 'Создать', disabled: true,
                 handler: function() { 
                    this.editTypePanel.hide();
                    
                    var GroupType_parent = this.GridGroupTypes.getGrid().getSelectionModel().getSelected().json;
                    
                    if(typeof(GroupType_parent) == 'undefined'){
                          Ext.getCmp('ufa_AdminBSKViewForm').showMsg('Необходимо создать группы типов сведений');                       
                    }
                    else
                        this.addTypePanel.show()}.createDelegate(this) 
                 
                 },
                {name:'action_edit', text: 'Изменить', disabled: true,
                 handler: function() { 
                       
                        this.addTypePanel.hide(); 
                         //return;
                        
                        this.editTypePanel.show();
                        //return false;
                     }.createDelegate(this) 
                 },
                {
                 name:'action_delete', 
                 id : 'action_delete',
                 text: 'Удалить', 
                 handler: function(){
                    
                    
                    sw.swMsg.show({
            			buttons: Ext.Msg.YESNO,
            			fn: function(buttonId, text, obj) {
            				if ( buttonId == 'yes' ) {
                                /*   */    

                                 Ext.Ajax.request({
                    		 		url: '/?c=ufa_BSK_Register&m=deleteType',
                    				params: {
                    				    BSKObservElement_id : Ext.getCmp('GridTypes').getGrid().getSelectionModel().getSelected().data.BSKObservElement_id
                    				},
                    				callback: function(options, success, response) {
                                        if (success === true) {
                                          Ext.getCmp('ufa_AdminBSKViewForm').showMsg('Тип сведений удалён');   
            
                                          Ext.getCmp('GridTypes').getGrid().getStore().load();
                                          //Ext.getCmp('ufa_AdminBSKViewForm').getTreeObjects(); 
                            			  return false;
                    					}
                                        
                    				}
                    		    });
                                 /*   */
            				}
            			}.createDelegate(this),
            			icon: Ext.MessageBox.QUESTION,
            			msg: 'Удалить тип сведений ?',
            			title: 'Вопрос'
            		});
            
            		return true;                      
   
                 },  disabled: true },
				{name:'action_view', hidden: true},
				{name:'action_refresh', hidden: true},
				{name:'action_print', hidden: true}
			],           
			onLoadData: function() {

			},
            onDblClick: function() {
                return;
            }
        }); 


        
       
 	    /**  Форма добавления нового сведения для предмета наблюдения */
        var StageStore =  new Ext.data.SimpleStore({
					fields:
					[
						{name: 'stage_id', type: 'int'},
						{name: 'stage_name', type: 'string'}
					],
					data: [[1,'Анкета'], [2,'Доп. исследования']]
		});   
      
		var StageCombo = new Ext.form.ComboBox({
			id : 'StageCombo',
            fieldLabel: 'Этап',
			store: StageStore,
			displayField:'stage_name',
            emptyText : 'Укажите этап сведений',
            editable : false,
            width: 150,
            triggerAction : 'all',
			mode: 'local',
            listeners: {
                select: function(combo,record, index){
                    
                    //console.log(index);
                    
                    if(index == 1){
                        Ext.getCmp('labelTextAnket').setVisible(true);
                        Ext.getCmp('textAnket').setVisible(true);
                    }
                    else{
                        Ext.getCmp('labelTextAnket').setVisible(false);
                        Ext.getCmp('textAnket').setVisible(false);                        
                    }
                }
            }
		});   

        StageCombo.setValue('');    

        var SexStore =  new Ext.data.SimpleStore({
					fields:
					[
						{name: 'Sex_id', type: 'int'},
						{name: 'Sex_Name', type: 'string'}
					],
					data: [[1,'Мужской'], [2,'Женский']]
		});   
      
		var SexCombo = new Ext.form.ComboBox({
			id : 'sexCombo',
            fieldLabel: 'Пол',
			store: SexStore,
			displayField:'Sex_Name',
            editable : false,
            width: 150,
            triggerAction : 'all',
			mode: 'local',
		});   
        

        var TypesFormatStore = new Ext.data.JsonStore({
            url: '/?c=ufa_BSK_Register&m=getTypesFormat',
            fields: ['BSKObservElementFormat_id', 'BSKObservElementFormat_name']
        });
        
        TypesFormatStore.load();
        
        
		var TypesFormatCombo = new Ext.form.ComboBox({
			id : 'typesCombo',
			forceSelection : true,
			store: TypesFormatStore,
			displayField:'BSKObservElementFormat_name',
			typeAhead: true,
            editable : false,
            anchor: '100%',
			mode: 'local',
            triggerAction: 'all',
			emptyText:'Укажите формат сведений',
			selectOnFocus:true,
            listeners : {
                'select': function(record, index){
                    if(record.value == 'formula (автоматический расчёт)'){
                        Ext.getCmp('labelformula').show();
                        Ext.getCmp('formula').show();                      
                    }  
                    else{
                        Ext.getCmp('labelformula').hide();
                        Ext.getCmp('formula').hide();                     
                    }                       
                }   
            }
		});        
       
	   	this.addTypePanel = new Ext.Panel({
	   	    title: 'Добавление',
	   	    id : 'addTypePanel',
            iconCls : 'add16',
            layout : 'anchor',
            bodyStyle: 'padding:5px; border: 0px;',
            border: false,
            frame : true,
            items : [
                
                {
                    xtype: 'panel',
                    html: 'Наименование',
                    bodyStyle: 'padding:5px; border: 0px;',
                },
                {
                 xtype: 'textfield',
                 id: 'nameType',
                 layout : 'anchor',
                 name: 'nameType',
                 border: false,
                 anchor: '100%',
                 allowBlank: false       
                },
                {
                    xtype: 'panel',
                    html: 'Формат',
                    bodyStyle: 'padding:5px; border: 0px; margin-top:5px',
                }, 
                TypesFormatCombo,
                {
                    xtype: 'panel',
                    html: 'Формула',
                    id: 'labelformula',
                    layuot: 'anchor',
                    bodyStyle: 'padding:5px; border: 0px',
                    hidden: true                               
                },
                {
                    xtype: 'textfield',
                    id: 'formula',
                    name: 'formula',
                    border: false,
                    anchor: '100%',                    
                    hidden: true
                },                                                         
                {
                    xtype: 'panel',
                    html: 'Этап сведений',
                    id: 'stage',
                    layuot: 'anchor',
                    bodyStyle: 'padding:5px; border: 0px',                            
                },                                  
                StageCombo,
                {
                    xtype: 'panel',
                    html: 'Текст вопроса',
                    id: 'labelTextAnket',
                    layuot: 'anchor',
                    bodyStyle: 'padding:5px; border: 0px',
                    hidden: true                            
                },                 
                {
                    xtype: 'textarea',
                    id: 'textAnket',
                    name: 'textAnket',
                    anchor: '100%',
                    hidden: true 
                }, 
                {
                    xtype: 'panel',
                    html: '', 
                    layuot: 'anchor',
                    bodyStyle: 'margin:5px',                            
                },                                                 
                {
                    xtype: 'panel',
                    html: 'Обозначение (только лат. буквы), если значение будет использоваться в формулах',
                    id: 'labelsymbol',
                    layuot: 'anchor',
                    bodyStyle: 'padding:5px; border: 0px'                        
                },
                {
                    xtype: 'textfield',
                    fieldLabel: 'Обозначение (только лат. буквы)',
                    labelWidth: 50,
                    id: 'symbol',
                    name: 'symbol',
                    style: 'margin:10px!important',
                    width: 50
                },
                {
                    xtype: 'panel',
                    html: 'Возраст от',
                    id: 'minAgeLabel',
                    layuot: 'anchor',
                    bodyStyle: 'padding:5px; border: 0px',                           
                },                               
                {
                    xtype: 'textfield',
                    id: 'minAge',
                    maskRe: /[0-9]/,
                    name: 'minAge',
                    border: false,
                    width: 50                   
                },
                {
                    xtype: 'panel',
                    html: 'Возраст до',
                    id: 'maxAgeLabel',
                    layuot: 'anchor',
                    bodyStyle: 'padding:5px; border: 0px',                            
                },                 
                {
                    xtype: 'textfield',
                    id: 'maxAge',
                    maskRe: /[0-9]/,                    
                    name: 'maxAge',
                    border: false,
                    width: 50                   
                },               
                {
                    xtype: 'panel',
                    html: 'Пол пациента',
                    id: 'sex',
                    layuot: 'anchor',
                    bodyStyle: 'padding:5px; border: 0px',                             
                },                             
                SexCombo, 

                
                                                                                                   
            ],
            buttons : [
                {
                  text: 'Добавить',
                  iconCls: 'save16',
                  handler: function() {
                              var newType      = Ext.getCmp('nameType');
                              var GroupType_id = Ext.getCmp('GridGroupTypes').getGrid().selected.data.BSKObservElementGroup_id;
                              var BSKObject_id = Ext.getCmp('GridObjects').getGrid().getSelectionModel().getSelected().data.BSKObject_id;
                              var format       = Ext.getCmp('typesCombo');
                              var symbol       = Ext.getCmp('symbol');
                              var formula      = Ext.getCmp('formula');
                              var minAge       = Ext.getCmp('minAge');
                              var maxAge       = Ext.getCmp('maxAge');
                              var stage        = Ext.getCmp('StageCombo');
                              var sex          = Ext.getCmp('sexCombo');
                              var textAnket    = Ext.getCmp('textAnket');
                              
                
                              if(minAge.getValue().length != 0 && maxAge.getValue().length != 0){
                                  if(parseInt(minAge.getValue()) > parseInt(maxAge.getValue())){
                                      Ext.getCmp('ufa_AdminBSKViewForm').showMsg('Некорректно ввёден возрастной интервал');  
                    			      return false;  
                                  } 
                              }                 
                
                              if(newType.getValue().length< 2){
                                  Ext.getCmp('ufa_AdminBSKViewForm').showMsg('Необходимо ввести наименование типа сведений');  
                    			  return false;                               
                              }               
                              else if(format.getValue() == ''){
                                  Ext.getCmp('ufa_AdminBSKViewForm').showMsg('Необходимо указать формат типа сведений');  
                    			  return false;                                  
                              }
                              else if(format.getValue() == 'formula (автоматический расчёт)' && formula.getValue().length< 3){
                                  Ext.getCmp('ufa_AdminBSKViewForm').showMsg('Необходимо указать формулу');
                    			  return false;                                  
                              }   
                              //Только по скринингу
                              else if(Ext.getCmp('GridObjects').getGrid().selected.data.BSKObject_id == 2  && stage.getValue() == ''){
                                  Ext.getCmp('ufa_AdminBSKViewForm').showMsg('Необходимо указать этап типа сведений');  
                    			  return false;                               
                              }                           
                              else{
                                  //Проверка, на всяк случай, вдруг данное буквенное обозначение (д ля формул) уже используется каким-либо сведением
                                  if(symbol.getValue().replace(/\s/g,'') != ''){
                                        Ext.getCmp('ufa_AdminBSKViewForm').checkSymbol(symbol.getValue(), null);
                                        
                                        //console.log('CBHF2:', typeof(Ext.getCmp('ufa_AdminBSKViewForm').checkSymbolTest));
                                                        
                                        if(Ext.getCmp('ufa_AdminBSKViewForm').checkSymbolTest != 'undefined'){
                                              Ext.getCmp('ufa_AdminBSKViewForm').showMsg('Данное бкувенное обозначение уже используется для другого типа сведений');
                                              return false;
                                        }
                                  }
                                                                             
                                  Ext.Ajax.request({
                        		 		url: '/?c=ufa_BSK_Register&m=addType',
                        				params: {
                        				    BSKObservElementGroup_id   : GroupType_id,
                                            BSKObject_id               : BSKObject_id,                                            
                        				    BSKObservElement_name      : newType.getValue(),
                                            formatText                 : format.getValue(),
                                            BSKObservElement_symbol    : symbol.getValue(''),
                                            BSKObservElement_formula   : formula.getValue(),
                                            BSKObservElement_stage     : stage.selectedIndex < 0 ? 2 : stage.selectedIndex,
                                            BSKObservElement_Sex_id    : (sex.selectedIndex < 1 || sex.selectedIndex > 2) ? 3 : sex.selectedIndex,
                                            BSKObservElement_minAge    : minAge.getValue(),
                                            BSKObservElement_maxAge    : maxAge.getValue(),
                                            BSKObservElement_IsRequire : 0,
                                            BSKObservElement_Anketa    : textAnket.getValue()
                        				},
                        				callback: function(options, success, response) {
                                            if (success === true) {
                                                Ext.getCmp('ufa_AdminBSKViewForm').showMsg('Тип сведений успешно добавлен');  
                                              
                                                newType.setValue('');
                                                format.setValue('');
                                                symbol.setValue('');
                                                formula.setValue('');
                                                sex.setValue('');
                                                stage.setValue('');
                                                minAge.setValue('');
                                                maxAge.setValue('');
                                                textAnket.setValue(''); 
                                              
                                                Ext.getCmp('GridTypes').getGrid().getStore().load();
                                                //Ext.getCmp('ufa_AdminBSKViewForm').getTreeObjects(); 
                        					}
                        				}
                        		  });
                              }
                           } 
                },{
                  text: 'Отмена',
                  iconCls: 'cancel16',
                  handler: function() {
                               Ext.getCmp('addTypePanel').hide();    
                           } 
                 }            
            ]
	   	});                      
        
        /** Редактирование типов сведений */
        var editSexStore =  new Ext.data.SimpleStore({
					fields:
					[
						{name: 'Sex_id', type: 'int'},
						{name: 'Sex_Name', type: 'string'}
					],
					data: [[1,'Мужской'], [2,'Женский']]
		}); 

		var editSexCombo = new Ext.form.ComboBox({
			id : 'editSexCombo',
            fieldLabel: 'Пол',
			store: editSexStore,
			displayField:'Sex_Name',
            editable : false,
            width: 150,
            triggerAction : 'all',
			mode: 'local',
		}); 
        
        //форматы типов сведений
        var editTypesFormatStore = new Ext.data.JsonStore({
            url: '/?c=ufa_BSK_Register&m=getTypesFormat',
            fields: ['BSKObservElementFormat_name']
        });
        
        editTypesFormatStore.load();
        
		var editTypesFormatCombo = new Ext.form.ComboBox({
			id : 'edittypesCombo',
			forceSelection : true,
			store: TypesFormatStore,
			displayField:'BSKObservElementFormat_name',
			typeAhead: true,
            editable : false,
            anchor: '100%',
			mode: 'local',
            triggerAction: 'all',
			emptyText:'Укажите формат сведений',
			selectOnFocus:true,
            listeners : {
                'select': function(record, index){
                    if(record.value == 'formula (автоматический расчёт)'){
                        Ext.getCmp('labeleditformula').show();
                        Ext.getCmp('editformula').show();                      
                    }  
                    else{
                        Ext.getCmp('labeleditformula').hide();
                        Ext.getCmp('editformula').hide();                     
                    }                       
                }   
            }           
		});  
        
        var editStageStore =  new Ext.data.SimpleStore({
					fields:
					[
						{name: 'stage_id', type: 'int'},
						{name: 'stage_name', type: 'string'}
					],
					data: [[1,'Анкета'], [2,'Доп. исследования']]
		});   
      
		var editStageCombo = new Ext.form.ComboBox({
			id : 'editStageCombo',
            fieldLabel: 'Этап',
			store: StageStore,
			displayField:'stage_name',
            emptyText : 'Укажите этап сведений',
            editable : false,
            width: 150,
            triggerAction : 'all',
			mode: 'local',
            listeners: {
                select: function(combo,record, index){
                    
                    //console.log(index);
                    
                    if(index == 1){
                        Ext.getCmp('editlabelTextAnket').setVisible(true);
                        Ext.getCmp('edittextAnket').setVisible(true);
                    }
                    else{
                        Ext.getCmp('editlabelTextAnket').setVisible(false);
                        Ext.getCmp('edittextAnket').setVisible(false);                        
                    }
                }
            }            
		});          
        
	   	this.editTypePanel = new Ext.Panel({
	   	    title: 'Редактирование',
            iconCls : 'edit16',
	   	    id : 'editTypePanel',
            layout : 'anchor',
            bodyStyle: 'padding:5px; border: 0px',
            border: false,
            frame : true,
            items : [
                {
                    xtype: 'panel',
                    id: 'editTypeLabelName',
                    html: 'Наименование',
                    bodyStyle: 'padding:5px; border: 0px;',
                },
                {
                 xtype: 'textfield',
                 id: 'editType',
                 layout : 'anchor',
                 name: 'esitType',
                 border: false,
                 anchor: '100%',
                 allowBlank: false       
                },
                {
                    xtype: 'panel',
                    id: 'editTypeLabelFormat',
                    html: 'Формат',
                    bodyStyle: 'padding:5px; border: 0px',
                },
                editTypesFormatCombo,
                {
                    xtype: 'panel',
                    html: 'Формула',
                    id: 'labeleditformula',
                    layuot: 'anchor',
                    bodyStyle: 'padding:5px; border: 0px',
                    hidden: true                               
                },
                {
                    xtype: 'textfield',
                    id: 'editformula',
                    name: 'editformula',
                    border: false,
                    anchor: '100%',                    
                    hidden: true
                },                                
                {
                    xtype: 'panel',
                    id: 'editTypeLabelStage',
                    html: 'Этап сведений',
                    id: '',
                    layuot: 'anchor',
                    bodyStyle: 'padding:5px; border: 0px',                            
                },                                  
                editStageCombo, 
                {
                    xtype: 'panel',
                    html: 'Текст вопроса',
                    id: 'editlabelTextAnket',
                    layuot: 'anchor',
                    bodyStyle: 'padding:5px; border: 0px',
                    hidden: true                            
                },                 
                {
                    xtype: 'textarea',
                    id: 'edittextAnket',
                    name: 'textAnket',
                    anchor: '100%',
                    hidden: true 
                },                 
                {
                    xtype: 'panel',
                    id: 'editTypeLabelHtml',
                    html: '', 
                    layuot: 'anchor',
                    bodyStyle: 'margin:5px',                            
                },                                
                {
                    xtype: 'panel',
                    html: 'Обозначение (только лат. буквы)',
                    id: 'labeleditsymbol',
                    layuot: 'anchor',
                    bodyStyle: 'padding:5px; border: 0px'                        
                },
                {
                    xtype: 'textfield',
                    id: 'editsymbol',
                    name: 'symbol',
                    width: 50
                }, 
                {
                    xtype: 'panel',
                    html: 'Возраст от',
                    id: 'editminAgeLabel',
                    layuot: 'anchor',
                    bodyStyle: 'padding:5px; border: 0px',                           
                },                               
                {
                    xtype: 'textfield',
                    id: 'editminAge',
                    name: 'editminAge',
                    border: false,
                    width: 50                   
                },
                {
                    xtype: 'panel',
                    html: 'Возраст до',
                    id: 'editmaxAgeLabel',
                    layuot: 'anchor',
                    bodyStyle: 'padding:5px; border: 0px',                            
                },                 
                {
                    xtype: 'textfield',
                    id: 'editmaxAge',
                    name: 'editmaxAge',
                    border: false,
                    width: 50                   
                },                   
                {
                    xtype: 'panel',
                    html: 'Пол пациента',
                    id: 'editsex',
                    layuot: 'anchor',
                    bodyStyle: 'padding:5px; border: 0px',                             
                },  
                editSexCombo,                   
                                  
            ],

            buttons : [
                {
                  text: 'Сохранить',
                  //id: 'editSaveType',
                  iconCls: 'save16',
                  handler: function() {
                             
                              var editType      = Ext.getCmp('editType');
                              var Type_id = Ext.getCmp('GridTypes').getGrid().getSelectionModel().getSelected().data.BSKObservElement_id;
                              var editformat       = Ext.getCmp('edittypesCombo');
                              var editsymbol       = Ext.getCmp('editsymbol');
                              var editformula      = Ext.getCmp('editformula');
                              var editminAge       = Ext.getCmp('editminAge');
                              var editmaxAge       = Ext.getCmp('editmaxAge');
                              var editstage        = Ext.getCmp('editStageCombo');
                              var editsex          = Ext.getCmp('editSexCombo');
                              var edittextAnket    = Ext.getCmp('edittextAnket');
                
                
                              if(editminAge.getValue().length != 0 && editmaxAge.getValue().length != 0){
                                  if(parseInt(editminAge.getValue()) > parseInt(editmaxAge.getValue())){
                                      Ext.getCmp('ufa_AdminBSKViewForm').showMsg('Некорректно ввёден возрастной интервал');  
                    			      return false;  
                                  } 
                              }                 
                
                              if(editType.getValue().length< 2){
                                  Ext.getCmp('ufa_AdminBSKViewForm').showMsg('Необходимо ввести наименование типа сведений');  
                    			  return false;                               
                              }               
                              else if(editformat.getValue() == ''){
                                  Ext.getCmp('ufa_AdminBSKViewForm').showMsg('Необходимо указать формат типа сведений');  
                    			  return false;                                  
                              }
                              else if(editformat.getValue() == 'formula (автоматический расчёт)' && editformula.getValue().length< 3){
                                  Ext.getCmp('ufa_AdminBSKViewForm').showMsg('Необходимо указать формулу');
                    			  return false;                                  
                              }   
                              //Только по скринингу
                              else if(Ext.getCmp('GridObjects').getGrid().selected.data.BSKObject_id == 2  && editstage.getValue() == ''){
                                  Ext.getCmp('ufa_AdminBSKViewForm').showMsg('Необходимо указать этап типа сведений');  
                    			  return false;                               
                              }                           
                              else{
                                  //Проверка, на всяк случай, вдруг данное буквенное обозначение (д ля формул) уже используется каким-либо сведением
                                  if(editsymbol.getValue().replace(/\s/g,'') != ''){
                                        Ext.getCmp('ufa_AdminBSKViewForm').checkSymbol(editsymbol.getValue(), Type_id);

                                        if(Ext.getCmp('ufa_AdminBSKViewForm').checkSymbolTest != 'undefined'){
                                              Ext.getCmp('ufa_AdminBSKViewForm').showMsg('Данное бкувенное обозначение уже используется для другого типа сведений');
                                              return false;
                                        }
                                  }

                                  Ext.Ajax.request({
                        		 		url: '/?c=ufa_BSK_Register&m=editType',
                        				params: {
                        				    BSKObservElement_id : Type_id,
                        				    BSKObservElement_name : editType.getValue(),
                                            formatText: editformat.getValue(),
                                            BSKObservElement_symbol : editsymbol.getValue(),
                                            BSKObservElement_stage : editstage.selectedIndex, 
                                            BSKObservElement_formula : editformula.getValue(),
                                            BSKObservElement_Sex_id    : (editsex.selectedIndex < 1 || editsex.selectedIndex > 2) ? 3 : editsex.selectedIndex,
                                            BSKObservElement_minAge    : editminAge.getValue(),
                                            BSKObservElement_maxAge    : editmaxAge.getValue(),
                                            BSKObservElement_IsRequire : 0,
                                            BSKObservElement_Anketa    : edittextAnket.getValue()                                            
                        				},
                        				callback: function(options, success, response) {
                                            if (success === true) {
                                                Ext.getCmp('ufa_AdminBSKViewForm').showMsg('Тип сведений успешно изменён');   
                                                Ext.getCmp('GridTypes').getGrid().getStore().load();
                                                //Ext.getCmp('ufa_AdminBSKViewForm').getTreeObjects(); 
                                			    return false;
                        					}
                        				}
                        		  });
                              }
                           } 
                },{
                  text: 'Отмена',
                  iconCls: 'cancel16',
                  handler: function() {
                               Ext.getCmp('editTypePanel').hide();    
                           } 
                 }            
            ]
	   	});         
              
        
        this.GridTypes.getGrid().on(
            'rowclick', 
                function(){
                    
                    Ext.getCmp('ufa_AdminBSKViewForm').addTypePanel.hide();
                    Ext.getCmp('ufa_AdminBSKViewForm').editTypePanel.hide();
                    
                    Ext.getCmp('ufa_AdminBSKViewForm').checkSymbolTest = 'undefined';
                    
                    if(this.getSelectionModel().getSelected().data.BSKObservElement_id == null){
                        Ext.getCmp('GridTypes').getAction('action_edit').setDisabled(true);
                    }
                    else{
                        Ext.getCmp('GridTypes').getAction('action_delete').setDisabled(false);
                        Ext.getCmp('GridTypes').getAction('action_edit').setDisabled(false);
                        Ext.getCmp('GridValues').getAction('action_add').setDisabled(false);
                    }
                
                    Ext.getCmp('GridValues').getGrid().getStore().load({
                        params: {
                                 BSKObservElement_id: Ext.getCmp('GridTypes').getGrid().getSelectionModel().getSelected().data.BSKObservElement_id
                                }    
                    });
       
                    //////////////////////////////////////////////////////////////
                    
    
                    //////////////////////////////////////////////////////// 
                       
                    var record = this.getSelectionModel().getSelected();
                    var BSKObservElement_id         = record.data.BSKObservElement_id;
                    var BSKObservElement_name       = record.data.BSKObservElement_name;
                    var BSKObservElement_stage      = record.data.BSKObservElement_stage;
                    var BSKObservElement_minAge     = record.data.BSKObservElement_minAge;
                    var BSKObservElement_maxAge     = record.data.BSKObservElement_maxAge;
                    var BSKObservElement_Sex_id     = record.data.BSKObservElement_Sex_id;
                    var BSKObservElementFormat_name = record.data.BSKObservElementFormat_name;
                    var BSKObservElementFormat_id   = record.data.BSKObservElementFormat_id;
                    var BSKObservElement_symbol     = record.data.BSKObservElement_symbol;
                    var BSKObservElement_formula    = record.data.BSKObservElement_formula;
                    var BSKObservElement_Sex_id     = record.data.BSKObservElement_Sex_id;  
                    var BSKObservElement_Anketa     = record.data.BSKObservElement_Anketa; 

                    //console.log(record.data);                    

                    Ext.getCmp('editsymbol').setValue(BSKObservElement_symbol); 
                    Ext.getCmp('editformula').setValue(BSKObservElement_formula);
                    
                    switch(parseInt(BSKObservElement_stage)){
                        case 1: var stage = 'Анкета'; break;
                        case 2: var stage = 'Доп. исследования'; break;
                        default : var stage = '';
                    }
                    Ext.getCmp('editStageCombo').setValue(stage);
                    
                    Ext.getCmp('edittextAnket').setValue(BSKObservElement_Anketa); 
                    Ext.getCmp('editminAge').setValue(BSKObservElement_minAge); 
                    Ext.getCmp('editmaxAge').setValue(BSKObservElement_maxAge); 
                    
                    switch(parseInt(BSKObservElement_Sex_id)){
                        case 1 : var sex_name = 'Мужской'; break; 
                        case 2 : var sex_name = 'Женский'; break;
                        default : var sex_name = '';
                    }
                    Ext.getCmp('editSexCombo').setValue(sex_name);
                    
                    //console.log('Формат', BSKObservElementFormat_name);
                    //console.log('ID', BSKObservElementFormat_id)
                    
                    Ext.getCmp('edittypesCombo').setValue(BSKObservElementFormat_name);
                    Ext.getCmp('editformula').setValue(BSKObservElement_formula);
                    
                    
                    if(parseInt(BSKObservElement_stage) == 1){
                        Ext.getCmp('editlabelTextAnket').setVisible(true);
                        Ext.getCmp('edittextAnket').setVisible(true);
                    }
                    else{
                        Ext.getCmp('editlabelTextAnket').setVisible(false);
                        Ext.getCmp('edittextAnket').setVisible(false);                        
                    }
                    
                    Ext.getCmp('editType').setValue(BSKObservElement_name); 
                    Ext.getCmp('edittypesCombo').setValue(BSKObservElementFormat_name);

                    if(BSKObservElementFormat_id == 7){
                        Ext.getCmp('labeleditformula').show();
                        Ext.getCmp('editformula').show(); 
                                             
                    }  
                    else{
                        Ext.getCmp('labeleditformula').hide();
                        Ext.getCmp('editformula').hide();                     
                    }                             

                    var targetType = this.getSelectionModel().getSelected(); 

                    if(typeof(targetType.json) == 'object'){
                        Ext.getCmp('GridValues').getAction('action_add').setDisabled(false);
                        Ext.getCmp('GridTypes').getAction('action_edit').setDisabled(false);
                    }
                    else{
                        Ext.getCmp('GridValues').getAction('action_add').setDisabled(true);
                        Ext.getCmp('GridTypes').getAction('action_edit').setDisabled(true);                        
                    }
                                         
                    //Ext.getCmp('GridValues').getAction('action_delete').setDisabled(true);
                    //Ext.getCmp('GridValues').getAction('action_edit').setDisabled(true);
                    /*
                    //console.log('!!!!>')
                    Ext.getCmp('ufa_AdminBSKViewForm').addValuePanel.hide();
                    Ext.getCmp('ufa_AdminBSKViewForm').editValuePanel.hide();
                    */
                    
                    Ext.getCmp('GridObjectsLinks').isMan = false;
                    //console.log('CLICK по вопросу', Ext.getCmp('GridObjectsLinks').isMan );
                    
                    Ext.getCmp('GridObjectsLinks').getGrid().getStore().load({
                        params: {
                                 BSKObject_id: Ext.getCmp('GridObjects').getGrid().selected.data.BSKObject_id
                        },
                        callback : function(){
                            
                            Ext.Ajax.request({
                		 		url: '/?c=ufa_BSK_Register&m=getLinks',
                				params: { },
                				callback: function(options, success, response) {
                				    
                                    Ext.getCmp('GridObjectsLinks').getGrid().getSelectionModel().deselectRange(0,100);
                                    
                				    //текущий тип сведений
                				    var BSKObservElement_id = Ext.getCmp('GridTypes').getGrid().getSelectionModel().getSelected().data.BSKObservElement_id
                                    //ссылки на общие типы
                				    var links = Ext.util.JSON.decode(response.responseText);
                                    
                                    Ext.getCmp('GridObjectsLinks').links = links;
                                    
                                    
                                    
                                    if (success === true) {
                                        
                                        for(j in links){
                                            
                                            var link = links[j];
                                            
                                            for(k in Ext.getCmp('GridObjectsLinks').getGrid().getStore().data.items){
                                                var record = Ext.getCmp('GridObjectsLinks').getGrid().getStore().data.items[k];
                                                
                                                if(typeof record == 'object'){

                                                    if(record.data.BSKObject_id == link.BSKObject_id && BSKObservElement_id == link.BSKObservElement_id){
                                                        //console.log('YES: ', record.data.BSKObject_id, link.BSKObservElement_id );
                                                        
                                                        var indexRecord = Ext.getCmp('GridObjectsLinks').getGrid().getStore().indexOf(record);
                                                        
                                                        Ext.getCmp('GridObjectsLinks').getGrid().getSelectionModel().selectRow(indexRecord, true)
                                                    }
                                                }
                                            }
                                        }
                                        Ext.getCmp('GridObjectsLinks').isMan = true; 
                                        //console.log('Закончили грузить 2й грид');    
                					}
                				}
                		    }); 
                            

                        }            
                    });                     
                }    
        );      

        this.GridTypes.getGrid().getSelectionModel().on(
            'rowselect', function (){
                

                    
                                     
            }
        );

        
        /** Значения сведений */
        
		this.GridValues = new sw.Promed.ViewFrame({
			id: 'GridValues',
			region: 'east',
			contextmenu: false,
            border: false,
            //height: 900,
			height: Ext.getBody().getHeight()*0.85,
            object: 'GridTypes',
			dataUrl: '/?c=ufa_BSK_Register&m=getListValues',
			autoLoadData: false,
			stringfields: [
				{name: 'BSKObservElementValues_id', type: 'int', header: 'ID'},
                {name: 'BSKObservElementValues_id', type: 'int', header: 'id'},
				{name: 'BSKObservElementValues_data', header: 'Значение', id: 'autoexpand'},
                {name: 'BSKObservElementValues_points', header: 'Баллы',width: 50},
                {name: 'BSKObservElementValues_sign', header: 'Признак',width: 50},
                {name: 'BSKObservElementValues_min', header: 'Min',width: 50},
                {name: 'BSKObservElementValues_max', header: 'Max',width: 50},
			],
 			actions: [
				{name:'action_add', text: 'Создать', 
                 handler: function() { 
                    var Types = Ext.getCmp('GridTypes').getGrid().getSelectionModel().getSelected();
                    var Values = Ext.getCmp('GridValues').getGrid().getSelectionModel().getSelected();

                    if(Types.data.BSK_TypesInfo_Format_id == 3 || Types.data.BSK_TypesInfo_Format_id == 4 || Types.data.BSK_TypesInfo_Format_id == 7 || Types.data.BSK_TypesInfo_Format_id == 8){
                            Ext.getCmp('ufa_AdminBSKViewForm').showMsg('Данный тип сведений не может иметь значение');   
                			return false;                                               
                    }                    

                    this.addValuePanel.show();
                    this.editValuePanel.hide();  
				}.createDelegate(this),disabled: true, },
                
                {name:'action_edit', text: 'Изменить', 
                 handler: function() {
                   /*
                    var Value = Ext.getCmp('GridValues').getGrid().getSelectionModel().getSelected();        
                    
                    var Value_id = Value.data.BSK_TypesInfoValues_id;
                    var Value_data =  Value.data.BSK_TypesInfoValues_data;
                    var editmin = Value.data.BSK_TypesInfoValues_min;
                    var editmax = Value.data.BSK_TypesInfoValues_max;
                    var editpoints = Value.data.BSK_TypesInfoValues_points;
                    var editsign = Value.data.BSK_TypesInfoValues_sign;
                    
                    console.log('Value', Value);
                    
                    Ext.getCmp('editNameValue').setValue(Value_data);
                    Ext.getCmp('editmin').setValue(editmin);
                    Ext.getCmp('editmax').setValue(editmax);
                    Ext.getCmp('editPoints').setValue(editpoints);
                    Ext.getCmp('editSign').setValue(editsign);
*/
                    this.addValuePanel.hide();
                    this.editValuePanel.show();                    
                
                }.createDelegate(this),disabled: true },
                
                {name:'action_delete', text: 'Удалить',
                 handler : function(){

                    sw.swMsg.show({
            			buttons: Ext.Msg.YESNO,
            			fn: function(buttonId, text, obj) {
            				if ( buttonId == 'yes' ) {
                                /*   */    
            
                                 var Value_id = Ext.getCmp('GridValues').getGrid().getSelectionModel().getSelected();
            
                                 Ext.Ajax.request({
                    		 		url: '/?c=ufa_BSK_Register&m=deleteValue',
                    				params: {
                    				    BSKObservElementValues_id: Value_id.id
                    				},
                    				callback: function(options, success, response) {
                                        if (success === true) {
                                          Ext.getCmp('ufa_AdminBSKViewForm').showMsg('Значение удалёно');   
            
                                          Ext.getCmp('GridValues').getGrid().getStore().load({
                                              params: {
                                                       Type_id: Ext.getCmp('GridTypes').getGrid().getSelectionModel().getSelected().id
                                                      }    
                                          });
            
                                          //Ext.getCmp('ufa_AdminBSKViewForm').getTreeObjects(); 
                            			  return false;
                    					}
                                        
                    				}
                    		    });  
                                /*   */
            				}
            			}.createDelegate(this),
            			icon: Ext.MessageBox.QUESTION,
            			msg: 'Удалить значение ?',
            			title: 'Вопрос'
            		});
            
            		return true;    


                  
                 }.createDelegate(this),disabled: true,},
				{name:'action_view', hidden: true},
				{name:'action_refresh', hidden: true},
				{name:'action_print', hidden: true}
			],           
			onLoadData: function() {

			},
            onDblClick: function() {
                return;
            }
        }); 
        
        /** добавление значений сведений */
        
	   	this.addValuePanel = new Ext.Panel({
	   	    title: 'Добавление',
	   	    id : 'addValuePanel',
            iconCls : 'add16',
            layout : 'anchor',
            bodyStyle: 'padding:5px; border: 0px;',
            border: false,
            frame : true,
            items : [
                {
                    xtype: 'panel',
                    html: 'Значение',
                    id : 'labelNameValue',
                    bodyStyle: 'padding:5px; border: 0px;',
                },
                {
                    xtype: 'textfield',
                    id: 'nameValue',
                    layout : 'anchor',
                    name: 'nameValue',
                    border: false,
                    anchor: '100%',
                    allowBlank: false       
                },
                {
                    xtype: 'panel',
                    html: 'Количество баллов',
                    id : 'labelAddPoints',
                    bodyStyle: 'padding:5px; border: 0px;',
                },
                {
                    xtype: 'textfield',
                    id: 'addPoints',
                    name: 'addPoints',
                    width: 50  
                },  
                {
                    xtype: 'panel',
                    html: 'Признак',
                    id : 'labelAddSign',
                    bodyStyle: 'padding:5px; border: 0px;',
                },
                {
                    xtype: 'textfield',
                    id: 'addSign',
                    name: 'addSign',
                    width: 50   
                },                               
                {
                    xtype: 'panel',
                    html: 'Минимальное значение (не обязательно)',
                    id: 'labelmin',
                    layuot: 'anchor',
                    bodyStyle: 'padding:5px; border: 0px'                        
                },
                {
                    xtype: 'textfield',
                    id: 'min',
                    name: 'min',
                    width: 50
                },
                {
                    xtype: 'panel',
                    html: 'Максимальное значение (не обязательно)',
                    id: 'labelmax',
                    layuot: 'anchor',
                    bodyStyle: 'padding:5px; border: 0px'                        
                },
                {
                    xtype: 'textfield',
                    id: 'max',
                    name: 'max',
                    width: 50
                },
                /*
                {
                 xtype: 'button',   
                 text : 'Рекомендации для пациента',
                 layuot: 'anchor',
                 style: 'margin-top:5px; margin-bottom:5px; width:100%',           
                 handler : function(){
                    console.log('VALUE_ID',Ext.getCmp('GridValues').getGrid().getSelectionModel().getSelected());
                    var params = {
                        ChangeTitle: 'Рекомендации для пациента',
                        Type: 2,
                        Value: Ext.getCmp('GridValues').getGrid().getSelectionModel().getSelected().data.BSKObservElementValues_id
                    }
                    
                    getWnd('ufa_AdminBSKRecomendationWindow').show(params);                  
                 }     
                },
                {
                 xtype: 'button',   
                 text : 'Рекомендации для врача',
                 layuot: 'anchor',
                 style: 'margin-top:5px; margin-bottom:5px',
                 handler : function(){
                    //Ext.getCmp('ufa_AdminBSKViewForm').gridRecomendation.getGrid().getStore().load({params:{BSKObservRecomendationType_id:1, BSKObservElementValues:null}})
                    
                    //Ext.getCmp('ufa_AdminBSKViewForm').RecomendationWindow.setTitle('Рекомендации для врача');
                    //Ext.getCmp('ufa_AdminBSKViewForm').RecomendationWindow.Type = 1;
                    //Ext.getCmp('ufa_AdminBSKViewForm').RecomendationWindow.show();
                 }                        
                },
                */                                                                                                    
            ],

            buttons : [
              
                {
                  text: 'Сохранить',
                  iconCls: 'save16',
                  handler: function() {
                              var BSKObservElement_id = Ext.getCmp('GridTypes').getGrid().getSelectionModel().getSelected().data.BSKObservElement_id;
                              
                              var nameValue = Ext.getCmp('nameValue');
                              var min = Ext.getCmp('min');
                              var max = Ext.getCmp('max');
                              var points = Ext.getCmp('addPoints');
                              var sign = Ext.getCmp('addSign');
                              
                              if(Ext.getCmp('nameValue').isVisible() === true){
                                  if(nameValue.getValue().length< 2){
                                      Ext.getCmp('ufa_AdminBSKViewForm').showMsg('Необходимо ввести значение типа сведений (не менее 2х символов)');
                        			  return false;                               
                                  }    
                              }
                              
                              if(min.getValue().replace(/\s/g, '') != '' && min.getValue().replace(/,/,'.') >= max.getValue().replace(/,/,'.')){
                                  Ext.getCmp('ufa_AdminBSKViewForm').showMsg('Минимально допустимое значение не может быть больше или равно максималоно допустимого');
                    			  return false;                                  
                              }                         
                              else{
                                  Ext.Ajax.request({
                        		 		url: '/?c=ufa_BSK_Register&m=addValue',
                        				params: {
                        				    BSKObservElement_id : BSKObservElement_id,
                                            BSKObservElementValues_data : nameValue.getValue(),
                                            BSKObservElementValues_min : min.getValue().replace(/,/,'.'),
                        				    BSKObservElementValues_max : max.getValue().replace(/,/,'.'),
                                            BSKObservElementValues_points: points.getValue(),
                                            BSKObservElementValues_sign: sign.getValue()

                                        },
                        				callback: function(options, success, response) {

                                            if (success === true) {
                                              
                                                Ext.getCmp('GridValues').getGrid().getStore().load({
                                                    params: {
                                                             BSKObservElement_id: Ext.getCmp('GridTypes').getGrid().getSelectionModel().getSelected().data.BSKObservElement_id
                                                            }    
                                                });   
                                                
                                                Ext.getCmp('ufa_AdminBSKViewForm').showMsg('Значение успешно добавлено');                                           
                                                
                                                nameValue.setValue('');
                                                min.setValue('');
                                                max.setValue('');
                                                points.setValue('');
                                                sign.setValue('');
                                			    return false;
                        					}

                        				}
                        		  });
                   
                              }

                           } 
                },{
                  text: 'Отмена',
                  iconCls: 'cancel16',
                  handler: function() {
                               Ext.getCmp('addValuePanel').hide();    
                           } 
                 }                
            ]
        });                 

        /** редактирование значений сведений */
        
	   	this.editValuePanel = new Ext.Panel({
	   	    title: 'Редактирование',
	   	    id : 'editValuePanel',
            iconCls : 'edit16',
            layout : 'anchor',
            bodyStyle: 'padding:5px; border: 0px;',
            border: false,
            frame : true,
            items : [
                {
                    xtype: 'panel',
                    html: 'Значение',
                    id : 'labelEditNameValue',
                    bodyStyle: 'padding:5px; border: 0px;',
                },
                {
                    xtype: 'textfield',
                    id: 'editNameValue',
                    layout : 'anchor',
                    name: 'editNameValue',
                    border: false,
                    anchor: '100%',
                    allowBlank: false       
                },
                {
                    xtype: 'panel',
                    html: 'Количество баллов',
                    id : 'labelEditPoints',
                    bodyStyle: 'padding:5px; border: 0px;',
                },
                {
                    xtype: 'textfield',
                    id: 'editPoints',
                    name: 'editPoints',
                    width: 50  
                },  
                {
                    xtype: 'panel',
                    html: 'Признак',
                    id : 'labelEditSign',
                    bodyStyle: 'padding:5px; border: 0px;',
                },
                {
                    xtype: 'textfield',
                    id: 'editSign',
                    name: 'editSign',
                    width: 50   
                },                 
                {
                    xtype: 'panel',
                    html: 'Минимальное значение (не обязательно)',
                    id: 'labeleditmin',
                    layuot: 'anchor',
                    bodyStyle: 'padding:5px; border: 0px'                        
                },
                {
                    xtype: 'textfield',
                    id: 'editmin',
                    name: 'editmin',
                    width: 50
                },
                {
                    xtype: 'panel',
                    html: 'Максимальное значение (не обязательно)',
                    id: 'labeleditmax',
                    layuot: 'anchor',
                    bodyStyle: 'padding:5px; border: 0px'                        
                },
                {
                    xtype: 'textfield',
                    id: 'editmax',
                    name: 'editmax',
                    width: 50
                },
                {
                 xtype: 'button',   
                 text : 'Рекомендации для пациента',
                 layuot: 'anchor',
                 style: 'margin-top:5px; margin-bottom:5px; width:100%',           
                 handler : function(){
                    var params = {
                        ChangeTitle: 'Рекомендации для пациента',
                        Type: 2,
                        Value: Ext.getCmp('GridValues').getGrid().getSelectionModel().getSelected().data.BSKObservElementValues_id
                    }
                    
                    getWnd('ufa_AdminBSKRecomendationWindow').show(params);      
                 }     
                },
                {
                 xtype: 'button',   
                 text : 'Рекомендации для врача',
                 layuot: 'anchor',
                 style: 'margin-top:5px; margin-bottom:5px',
                 handler : function(){
                    var params = {
                        ChangeTitle: 'Рекомендации для врача',
                        Type: 1,
                        Value: Ext.getCmp('GridValues').getGrid().getSelectionModel().getSelected().data.BSKObservElementValues_id
                    }
                    
                    getWnd('ufa_AdminBSKRecomendationWindow').show(params);   
                 }                        
                },                                                                                                      
            ],

            buttons : [
                {
                  text: 'Изменить',
                  iconCls: 'save16',
                  handler: function() {
                              var BSKObservElementValues_id = Ext.getCmp('GridValues').getGrid().getSelectionModel().getSelected().data.BSKObservElementValues_id;
                              var editNameValue =  Ext.getCmp('editNameValue');
                              var editmin =  Ext.getCmp('editmin');
                              var editmax =  Ext.getCmp('editmax');
                              var editpoints = Ext.getCmp('editPoints');
                              var editsign = Ext.getCmp('editSign');
                              
                              //Если текст для значения доступен
                              if(Ext.getCmp('editNameValue').isVisible() === true){
                                  if(editNameValue.getValue().length< 2){
                                      Ext.getCmp('ufa_AdminBSKViewForm').showMsg('Необходимо ввести значение (не менее 2х символов)');  
                        			  return false;                               
                                  }                                  
                              }
                              
                              if(editmin.getValue().replace(/\s/g, '') != '' && editmin.getValue().replace(/,/,'.') >= editmax.getValue().replace(/,/,'.')){
                                  Ext.getCmp('ufa_AdminBSKViewForm').showMsg('Минимально допустимое значение не может быть больше или равно максималоно допустимого'); 
                    			  return false;                                  
                              }                                
                              
                              Ext.Ajax.request({
                    		 		url: '/?c=ufa_BSK_Register&m=editValue',
                    				params: {
                    				    BSKObservElementValues_id : BSKObservElementValues_id,
                                        BSKObservElementValues_data : editNameValue.getValue(),
                                        BSKObservElementValues_min : editmin.getValue().replace(/,/,'.'),
                    				    BSKObservElementValues_max : editmax.getValue().replace(/,/,'.'),
                                        BSKObservElementValues_points : editpoints.getValue(),
                                        BSKObservElementValues_sign : editsign.getValue()
                                    },
                    				callback: function(options, success, response) {

                                        if (success === true) {
                                          
                                            Ext.getCmp('GridValues').getGrid().getStore().load({
                                                params: {
                                                         BSKObservElement_id: Ext.getCmp('GridTypes').getGrid().getSelectionModel().getSelected().data.BSKObservElement_id
                                                        }    
                                            });   
                                            
                                            Ext.getCmp('ufa_AdminBSKViewForm').showMsg('Значение успешно изменено');                                           
                            			    return false;
                    					}

                    				}
                    		  });

                           }
                          
                },{
                  text: 'Отмена',
                  iconCls: 'cancel16',
                  handler: function() {
                               Ext.getCmp('editValuePanel').hide();    
                           } 
                 }                
            ]
        });          
        
        /** add listener for GridValues and value for edit object name form */
        
        this.GridValues.getGrid().on(
            'rowclick', 
                function(){
                    
                    Ext.getCmp('ufa_AdminBSKViewForm').addValuePanel.hide();
                    Ext.getCmp('ufa_AdminBSKViewForm').editValuePanel.hide();
                    
                    var targetObject = this.getSelectionModel().getSelected(); 
                    
                    if(typeof(targetObject.json) != 'undefined'){
                        Ext.getCmp('GridValues').getAction('action_edit').setDisabled(false);
                        Ext.getCmp('GridValues').getAction('action_delete').setDisabled(false);    
                    
                        var ValueData = Ext.getCmp('GridValues').getGrid().getSelectionModel().getSelected().data;
                                  
                        var BSKObservElementValues_id =ValueData.BSK_TypesInfoValues_id;
                        var BSKObservElementValues_data =  ValueData.BSKObservElementValues_data;
                        var BSKObservElementValues_min =  ValueData.BSKObservElementValues_min;
                        var BSKObservElementValues_max =  ValueData.BSKObservElementValues_max;
                        var BSKObservElementValues_points =  ValueData.BSKObservElementValues_points;
                        var BSKObservElementValues_sign =  ValueData.BSKObservElementValues_sign;

                        
                        Ext.getCmp('editNameValue').setValue(BSKObservElementValues_data);
                        Ext.getCmp('editmin').setValue(BSKObservElementValues_min);
                        Ext.getCmp('editmax').setValue(BSKObservElementValues_max);   
                        Ext.getCmp('editPoints').setValue(BSKObservElementValues_points);
                        Ext.getCmp('editSign').setValue(BSKObservElementValues_sign);                          
                    }
                }  
        );         
      
              
        this.Tree = new Ext.Panel({
            id : 'tree',
            html: 'Список предметов наблюдения не определён',
            border: false,
            bodyStyle: 'padding:5px; border: 0px;', 
            autoScroll: 'auto',
            autoHeight: true,
            height : 'auto', 
            bodyStyle: 'padding: 10px; font-family:tahoma!important;font-size:13px;',          
        });
        
        
        this.listRecomendation = new sw.Promed.ViewFrame({
            			id: 'listRecomendation',
                    	title: '',      
                        //height: 550,
            			height: Ext.getBody().getHeight()*0.7,
                        object: 'listRecomendation',
            			dataUrl: '/?c=ufa_BSK_Register&m=listRecomendation',
            			autoLoadData: false,
                        focusOnFirstLoad: false,
                        toolbar: false,
                        autoScroll: true,
            			stringfields: [
            				{name: 'BSKObservRecomendation_id', type: 'int', header: 'ID'},
                            {name: 'BSKObservRecomendation_id', type: 'int', header: 'id', width: 30},
            				{name: 'BSKObservRecomendation_text', header: 'Текст рекомендации', id: 'autoexpand', editor: new Ext.form.TextField()}                      
                            
            			],
             			actions: [
            				{
                             name:'action_add', 
                             hidden: true,
                             text: 'Создать', 
                             disabled: true,
                             handler: function() { 
                                
                             }
                            },{
                             name:'action_edit', 
                             hidden: true,
                             text: 'Изменить', 
                             handler: function() {
                                
                             }
                                , 
                             disabled: true 
                            },{
                             name:'action_delete', 
                             hidden: true,
                             text: 'Удалить', 
                             disabled: true,
                             handler: function(){
                                },  
                             disabled: true },
            				{name:'action_view', hidden: true},
            				{name:'action_refresh', hidden: true},
            				{name:'action_print', hidden: true},
                            {name: 'action_save', url: '/?c=ufa_BSK_Register&m=saveAfterEditRecomendation', hidden:true}
            			],
                        onLoadData: function(){
                            
                        },
                        onAfterEdit: function(o) {
                				var params = {};
                				params.BSKObservRecomendation_text = o.record.get('BSKObservRecomendation_text');
                        }       
         }) 

         this.listRecomendation.getGrid().addListener(
            'rowcontextmenu',
                              function(grid, index, event){
                                
                                 //console.log('!!!!!!', event);
                                
                                 event.stopEvent();
                                 new Ext.menu.Menu({
                        			items: [{
                        				text: 'Удалить',
                        				tooltip: 'Удалить рекомендацию',
                                        icon : 'img/icons/edit16.png',
                                        width: 200,
                                        height: 20,
                        				handler: function() {
;

                                    		sw.swMsg.show({
                                    			buttons: Ext.Msg.YESNO,
                                    			fn: function(buttonId, text, obj) {
                                    				if ( buttonId == 'yes' ) {
                                    					var rec = Ext.getCmp('listRecomendation').getGrid().getSelectionModel().getSelected().data;
                                                        //console.log('DELETE', rec);

                                                        Ext.Ajax.request({
                                            		 		url: '/?c=ufa_BSK_Register&m=deleteEditRecomendation',
                                            				params: { 
                                                                BSKObservRecomendation_id : rec.BSKObservRecomendation_id
                                            				},
                                            				callback: function(options, success, response) {
                                            				    Ext.getCmp('listRecomendation').getGrid().getStore().load();
                                                            }
                                                        });

   
                                    				}
                                    			}.createDelegate(this),
                                    			icon: Ext.MessageBox.QUESTION,
                                    			msg: 'Удалить рекомендацию ?',
                                    			title: 'Вопрос'
                                    		});
                                    
                                    		return true;                                            
                                            

                                            
                                            //var BSKObservRecomendation_id = 
                        				}
                        			}
                                    /**
                                    , {
                        				text: 'Редактировать',
                        				tooltip: 'Редактировать рекомендацию',
                                        icon : 'img/icons/delete16.png',
                                        width: 200,
                                        height: 20,                                        
                        				handler: function() {
                        					//frms.openAnalyzerEditWindow('add', true);
                        				}
                        			}
                                    */
                                    ]
                        		}).showAt(event.xy);         
         });                 
        
        this.GeneralPanel = new Ext.Panel(
		{   
		    title : '',
			bodyBorder: false,
			border: true,
            BSKObservRecomendationType_id : 1,
			id: 'GeneralPanel',
            tree : 'Список предметов наблюдения пуст',
            //autoHeight: false,
            
            items: [
                 {  
                     xtype: 'tabpanel',
                     id: 'tabpanel',
                     activeTab: 0, 
                     items :[
                        {
                            title: 'Управление предметами наблюдения',
                            autoScroll: true,
                            items:[
                            {
                                xtype:'panel',
                                layout:'column',
                                autoScroll: true,
                                //height: 300,
                                items:[
                                        {                           
                                            title: 'Предметы наблюдения',
                                            columnWidth: .21,
                                            height: Ext.getBody().getWidth(true) - 410,
                                            autoScroll: 'auto',
                                            items : [
                                                      this.addObjectPanel,
                                                      this.editObjectPanel,
                                                      this.GridObjects
                                                    ]
                                        },{
                                            title: 'Группы типов сведений',
                                            columnWidth: .21,
                                            height: Ext.getBody().getWidth(true) - 410,
                                            autoScroll: true,
                                            items : [
                                                     this.addGroupTypePanel,
                                                     this.editGroupTypePanel,
                                                     this.GridGroupTypes
                                                     ]
                                        },{
                                            title: 'Типы сведений',
                                            columnWidth: .40,
                                            height: Ext.getBody().getWidth(true) - 410,
                                            autoScroll: true,
                                            items : [
                                                     this.addTypePanel,
                                                     this.editTypePanel,
                                                     this.GridTypes,
                                                     this.GridObjectsLinks
                                                     ]
                                        },{
                                            title: 'Значения сведений', 
                                            autoScroll: true,
                                            columnWidth: .18,
                                            height: Ext.getBody().getWidth(true) - 410,
                                            items : [
                                                    this.editValuePanel,
                                                    this.addValuePanel,
                                                    this.GridValues
                                                    ]
                                        }                               
                                      ]
                                    }
                                ],
                                listeners: {
                                    'active' : function(p){
                                        //не нужно. tab закрыт маской от модального окна
                                        //Ext.getCmp('ufa_AdminBSKRecomendationWindow').gridRecomendation.getGrid().getStore().load();
                                    }
                                }
                                
                        },
                         {
                            title: 'Управление рекомендациями',
                            autoScroll: true,
                            bodyStyle: '',
                            frame: true,
                            height:900,
                            items:[
                                {
                                        id: 'RecomendationTypeGroup',
                                        xtype: 'radiogroup',
                                        fieldLabel: 'Single Column',
                                        style: 'margin-left:20px;margin:10px',
                                        width: 490,
                                        // Arrange radio buttons into three columns, distributed vertically
                                        columns: 2,
                                        vertical: true,
                                        items: [
                                            {boxLabel: 'База рекомендаций для врачей', name: 'rb', inputValue: '1',  checked: true, id: 'recdoc'},
                                            {boxLabel: 'База рекомендаций для  пациентов', name: 'rb', inputValue: '2'}  
                                        ],
                                              setValue: function(v){
                                                if (this.rendered){
                                                  this.items.each(function(item){
                                                    item.setValue(item.getRawValue() == v);
                                                  });
                                                }
                                                else {
                                                  for (var k in this.items) {
                                                    this.items[k].checked = this.items[k].inputValue == v;
                                                  }
                                                }
                                              },                                        
                						listeners: {
                							change: function(box, value) {
                								Ext.getCmp('searchRecomendationPanel_text').setValue('');
                                                
                                                Ext.getCmp('GeneralPanel').BSKObservRecomendationType_id = value.inputValue;
                							    Ext.getCmp('ufa_AdminBSKViewForm').listRecomendation.getGrid().getStore().load({
                							       params:{ 
                							           BSKObservRecomendationType_id: value.inputValue,
                                                       searchRecomendation_text : '' 
                                                   }
                                                }); 
                                            }
                						}                                        
                                },
                                 {
                                    xtype: 'fieldset',
                                    layout: 'column',
                                    collapsible : true,
                                    collapsed: true,                    
                                    title: 'Поиск в базе рекомендаций',
                                    height: 66,
                                    items: [ 
                                            {
                                              layout : 'form',
                                              labelWidth: 2,
                                    		  border: false,
                                    		  bodyStyle:'padding: 4px;background:#DFE8F6;',
                                              items : [
                                                {
                                                    xtype: 'textfield',
                                                    style: 'border:0px!important;',
                                                    labelWidth: 2,
                                                    labelSeparator: '',
                                                    anchor: '100%',
                                                    id: 'searchRecomendationPanel_text',
                                                    width: 690,
                                                    value: '',
                                                    columnWidth: 0.8,
                                					listeners: {
                                                        specialkey:  function(field, e){
                                                            //console.log('e.getKey: ', e.getKey());
                                                            if (e.getKey() == e.ENTER) {
                                                                Ext.getCmp('searchRecomendationButtonPanel').handler();
                                                            }
                                                        }
                                					}                                                       
                                                }                                
                                              ]  
                                            }, 
                                            {
                                              layout : 'form',
                                    		  border: false,
                                    		  bodyStyle:'padding: 4px;background:#DFE8F6;',
                                              items : [
                                                {
                                                    xtype: 'button',
                                                    text: 'Найти',
                                                    id : 'searchRecomendationButtonPanel',
                                                    handler: function(){
                                                         var searchRecomendation_text = Ext.getCmp('searchRecomendationPanel_text').getValue();
                                                         
                                                         if(searchRecomendation_text.length < 2){
                                             				 sw.swMsg.show(
                                            				 {
                                            					icon: Ext.MessageBox.ERROR,
                                            					title: 'Ошибка',
                                            					msg: 'Для поиска используйте не менее 2х символов!',
                                            					buttons: Ext.Msg.OK
                                            				 });
                                                            
                                                             return false;                                              
                                                            
                                                         }
                                                         Ext.getCmp('ufa_AdminBSKViewForm').listRecomendation.getGrid().getStore().load({
                                                            params:{ 
                                                                BSKObservRecomendationType_id: Ext.getCmp('GeneralPanel').BSKObservRecomendationType_id,
                                                                searchRecomendation_text: searchRecomendation_text 
                                                                }
                                                         });
                                                         
                                                         
                                                         
                                                    },
                                                    columnWidth: 0.1
                                                                                 
                                                },
                                              ]                                
                                            },
                                            {
                                              layout : 'form',
                                    		  border: false,
                                    		  bodyStyle:'padding: 4px;background:#DFE8F6;',
                                              items : [
                                                {
                                                    xtype: 'button',
                                                    text: 'Сбросить',
                                                    handler: function(){
                                                         Ext.getCmp('searchRecomendationPanel_text').setValue('');
                                                         
                                                         if(Ext.getCmp('searchRecomendationPanel_text').getValue().length == 0){
                                                         
                                                             Ext.getCmp('ufa_AdminBSKViewForm').listRecomendation.getGrid().getStore().load({
                                                                params:{ 
                                                                    BSKObservRecomendationType_id: Ext.getCmp('GeneralPanel').BSKObservRecomendationType_id,
                                                                    searchRecomendation_text : ''
                                                                    }
                                                             });
                                                         
                                                         }
                                                    },
                                                    columnWidth: 0.1                                
                                                                                    
                                                }  
                                              ]                                
                                            }                                                 
                                    ],
                                 },                                
                                 {
                                    xtype: 'fieldset',
                                    collapsible : true,
                                    collapsed: true,
                                    title: 'Новая рекомендация',
                                    height: 138,
                                    style: 'margin-top:10px; margin-bottm:10px;',
                                    items: [ 
                                    
                                    
                                             {
                                              layout : 'form',
                                              labelWidth: 2,
                                    		  border: false,
                                    		  bodyStyle:'padding: 4px;background:#DFE8F6;',
                                              items : [                
                                                    {
                                                    xtype: 'textarea',
                                                    height:60,
                                                    //width: 840,
                                                    anchor:'100%',
                                                    labelWidth: 2,
                                                    labelStyle: 'width:1px',
                                                    labelSeparator: '',
                                                    id: 'add_BSKObservRecomendation_text',
                                                    style: 'float:left!important',
                                                    value: '',
                                                   },
                                                   {
                                                    xtype: 'panel',
                                                    html: '',
                                        		    border: false,
                                        		    bodyStyle:'padding: 4px;background:#DFE8F6;',                        
                                                   },
                                                   {
                                                     xtype: 'button',
                                                     text : 'Добавить',
                                                     style: 'float:right',
                                                     handler: function(){
                                                         
                                                         //alert(Ext.getCmp('GeneralPanel').BSKObservRecomendationType_id);
                                                         
                                                         //return false;
                                                         
                                                         var BSKObservRecomendationType_id = Ext.getCmp('GeneralPanel').BSKObservRecomendationType_id;  
                                                         var BSKObservRecomendation_text = Ext.getCmp('add_BSKObservRecomendation_text').getValue();
                
                                                         
                                                         if(BSKObservRecomendation_text.length < 5){
                                            				 sw.swMsg.show(
                                            				 {
                                            					icon: Ext.MessageBox.ERROR,
                                            					title: 'Ошибка',
                                            					msg: 'Текст рекомендации слишком короткий!',
                                            					buttons: Ext.Msg.OK
                                            				 });
                                                            
                                                             return false;     
                                                         }  
                                                            
                                                            
                                                              
                                                         Ext.Ajax.request({
                                            		 		url: '/?c=ufa_BSK_Register&m=addRecomendation',
                                            				params: { 
                                            				    BSKObservRecomendation_text: BSKObservRecomendation_text,
                                                                BSKObservRecomendationType_id : BSKObservRecomendationType_id
                                            				},
                                            				callback: function(options, success, response) {
                                            				     if(success === true){
                                            				        //Ext.getCmp('ufa_AdminBSKRecomendationWindow').hide();
                                                                    
                                                                    Ext.getCmp('add_BSKObservRecomendation_text').setValue('');
                                                                    
                                                    				sw.swMsg.show(
                                                    				{
                                                    					icon: Ext.MessageBox.SUCCESS,
                                                    					title: 'Сообщение',
                                                    					msg: 'Рекомендация успешно добавлена!',
                                                    					buttons: Ext.Msg.OK
                                                    				});      
                                                                    
                                                                    Ext.getCmp('ufa_AdminBSKViewForm').listRecomendation.getGrid().getStore().load({params:{ BSKObservRecomendationType_id: BSKObservRecomendationType_id }});
                                                                                                      
                                                                    return false;
                                            				     }
                                                                 else{
                                                    				sw.swMsg.show(
                                                    				{
                                                    					icon: Ext.MessageBox.ERROR,
                                                    					title: 'Ошибка',
                                                    					msg: 'Произошла ошибка при добавлении рекомендации',
                                                    					buttons: Ext.Msg.OK
                                                    				});
                                                    				return false;
                                                                 }     
                                                            }
                                                         });    
                                                        
                                                     }
                                                   } 
                                             ]
                                            }
                                       
                                   ]    
                               },                                
                                this.listRecomendation
                           ],
                           listeners: {
                              'activate': function(p){
                                 Ext.getCmp('searchRecomendationPanel_text').setValue('');

                                 Ext.getCmp('RecomendationTypeGroup').setValue(1);
                                 
                                 if(Ext.getCmp('searchRecomendationPanel_text').getValue().length == 0){
                                 
                                     Ext.getCmp('ufa_AdminBSKViewForm').listRecomendation.getGrid().getStore().load({
                                        params:{ 
                                            BSKObservRecomendationType_id: 1, 
                                            searchRecomendation_text : '' 
                                        }
                                     });
                                 
                                 }
                              }
                           }
                         },
                         /**
                         {
                            title: 'Предосмотр',
                            height: 800,
                            autoHeight: false,
                            autoScroll: true,
                            items:[
                               {                                
                                border: false,
                                items:[
                                    {     
                                        title: 'Предосмотр предметов наблюдения',
                                        items: [this.Tree]
                                    } 
                                 ]                           
                               }
                            ]
                        }  
                        */                              
                  
                    ],
                    listeners: {
                        'tabchange':function(tab){
                            //Ext.getCmp('ufa_AdminBSKViewForm').getTreeObjects();
                        }
                    }                                       
                 }             
            ]
		});   

		Ext.apply(this,
		{
			defaults: {
				split: true
			},
			buttons: [
			],
			items: [this.GeneralPanel]
		});        

        sw.Promed.ufa_AdminBSKViewForm.superclass.initComponent.apply(this, arguments);
    },
    
    showMsg : function(msg){
    	sw.swMsg.show(
    	{
    		  buttons: Ext.Msg.OK,
    		  icon: Ext.Msg.WARNING,
              width : 300,
    		  msg: msg,
    		  title: ERR_INVFIELDS_TIT
    	});    
    },
    
    show: function(){
        //Получение древа предметов наблюдения
        //Прячем панели hidden:true не растягивает поля даже при anchor
        this.addTypePanel.hide();
        this.editTypePanel.hide();
        this.addGroupTypePanel.hide();
        this.editGroupTypePanel.hide();
        this.addValuePanel.hide();
        this.editValuePanel.hide();
                
        Ext.getCmp('tabpanel').setActiveTab(1);
        Ext.getCmp('tabpanel').setActiveTab(0)        
                
        sw.Promed.ufa_AdminBSKViewForm.superclass.show.apply(this, arguments);
    },  
    getTreeObjects: function(){
          
          Ext.Ajax.request({
		 		url: '/?c=ufa_BSK_Register&m=getTreeObjects',
				callback: function(options, success, response) {
                    if (success === true) {
                        var htmlText= response.responseText;
                    	var treePanel = Ext.getCmp('tree');
                        
                        if(treePanel.body){ 
                    	   treePanel.body.update(Ext.util.JSON.decode(htmlText), true);       
                        }
                    } 
				}
		  });
                  
          Ext.getCmp('ufa_AdminBSKViewForm').doLayout();
    },
    checkSymbol : function(symbol, BSKObservElement_id){
        //console.log('symbol: ', symbol);
        Ext.getCmp('ufa_AdminBSKViewForm').checkSymbolTest = 'undefined';
        Ext.Ajax.request({
     		url: '/?c=ufa_BSK_Register&m=checkSymbol',
    		params: {
    		    symbol : symbol,
                BSKObservElement_id: BSKObservElement_id
    		},
    		callback: function(options, success, response) {
                //Ext.getCmp('ufa_AdminBSKViewForm').checkSymbolTest = '';
                
                var rt = Ext.util.JSON.decode(response.responseText);
                
                Ext.getCmp('ufa_AdminBSKViewForm').checkSymbolTest = typeof(rt[0]); 
                
                //console.log('CHSBL: ', typeof(rt[0]));     
    		}
        });                                  
    }  

});