/**
* Управление предметами наблюдения регистра БСК
*
*
* @package      All
* @access       public
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
			height: Ext.getBody().getHeight()*0.897,
            object: 'GridObjects',
			dataUrl: '/?c=ufa_BSK_Register&m=getListObjects',
			autoLoadData: true,
            focusOnFirstLoad: false,
			stringfields: [
				{name: 'BSKObject_id', type: 'int', header: 'ID'},
                {name: 'MorbusType_id', type: 'int',hidden:true},
				{name: 'MorbusType_name', header: 'Наименование', id: 'autoexpand'}
			],
 			actions: [
				{
                 name:'action_add', 
                 text: 'Создать', 
                 disabled: true,
                 handler: function() { 
                        this.addObjectPanel.show();
                        this.editObjectPanel.hide();
                    }.createDelegate(this) 
                },{
                 name:'action_edit', 
                 text: 'Изменить', 
                 handler: function() {
                        this.editObjectPanel.show();
                        this.addObjectPanel.hide();
                    }.createDelegate(this), 
                 disabled: true 
                },{
                 name:'action_delete', 
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
                              Ext.getCmp('ufa_AdminBSKViewForm').getTreeObjects(); 
        					}
                            
        				}
        		    });
                     
                 },  disabled: true },
				{name:'action_view', hidden: true},
				{name:'action_refresh', hidden: true},
				{name:'action_print', hidden: true}
			], 
            
			onLoadData: function() {

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
            frame : true,
            items : [
                {
                    xtype: 'panel',
                    html: 'Наименование',
                    bodyStyle: 'padding:5px; border: 0px;'
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
                        				    nameObject : newObject.getValue()
                        				},
                        				callback: function(options, success, response) {
                                            if (success === true) {
                                              Ext.getCmp('ufa_AdminBSKViewForm').showMsg('Предмет наблюдения успешно добавлен');
                                              
                                              newObject.setValue('');
                                              Ext.getCmp('GridObjects').getGrid().getStore().load();
                                              Ext.getCmp('ufa_AdminBSKViewForm').getTreeObjects(); 
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
                               Ext.getCmp('addObjectPanel').hide();    
                           } 
                 }
                             
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
            frame : true,
            items : [  
                {
                    xtype: 'panel',
                    html: 'Наименование',
                    bodyStyle: 'padding:5px; border: 0px'
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
                                              Ext.getCmp('ufa_AdminBSKViewForm').getTreeObjects(); 
                                              Ext.getCmp('editObjectPanel').hide();
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
                               Ext.getCmp('editObjectPanel').hide();    
                           } 
                 }            
            ]           
        });     

        //add listener for GridObjects and value for edit object name form
        this.GridObjects.getGrid().on(
            'rowclick', 
                function(){
                    
                    var editObject = Ext.getCmp('newNameObject');
                    var targetObject = this.getSelectionModel().getSelected(); 
                      
                    this.selected = targetObject;
                    editObject.setValue(this.selected.data.MorbusType_name);
                    
                    Ext.getCmp('GridObjects').getAction('action_edit').setDisabled(false);
                    //Ext.getCmp('GridObjects').getAction('action_delete').setDisabled(false);
                    Ext.getCmp('GridGroupTypes').getAction('action_add').setDisabled(false);
                    
                    Ext.getCmp('GridGroupTypes').getAction('action_edit').setDisabled(true);
                    Ext.getCmp('GridGroupTypes').getAction('action_delete').setDisabled(true);

                    
                    Ext.getCmp('GridGroupTypes').getGrid().getStore().load({
                        params: {
                                 BSKObject_id: Ext.getCmp('GridObjects').getGrid().selected.data.BSKObject_id
                                }    
                    });
                    
                    Ext.getCmp('GridTypes').getGrid().getStore().load({params:{GroupType_id:0}});
                    Ext.getCmp('GridValues').getGrid().getStore().load({params:{Type_id:0}});
                }    
        );  
        
        //add listener for gridObject fo hidden add & edit forms & disabled action_edit row
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
            //height: 900,
			height: Ext.getBody().getHeight()*0.897,
            object: 'GridGroupTypes',
			dataUrl: '/?c=ufa_BSK_Register&m=getListGroupTypes',
			autoLoadData: false,
            focusOnFirstLoad: false,
			stringfields: [
				{name: 'BSKObservElementGroup_id', type: 'int', header: 'ID', hidden: true},
				{name: 'BSKObservElementGroup_name', header: 'Наименование', id: 'autoexpand'}
			],
 			actions: [
				{name:'action_add', text: 'Создать', handler: function() { this.addGroupTypePanel.show();this.editGroupTypePanel.hide()}.createDelegate(this), disabled: true },
                {name:'action_edit', text: 'Изменить', handler: function() { this.editGroupTypePanel.show();this.addGroupTypePanel.hide()}.createDelegate(this), disabled: true },
                {name:'action_delete', text: 'Удалить', disabled: true,
                handler : function()
                {
                     var BSKObservElementGroup_id = Ext.getCmp('GridGroupTypes').getGrid().selected.data.BSKObservElementGroup_id;
 
                     Ext.Ajax.request({
        		 		url: '/?c=ufa_BSK_Register&m=deleteGroupType',
        				params: {
        				    BSKObservElementGroup_id : BSKObservElementGroup_id
        				},
        				callback: function(options, success, response) {
                            if (success === true) {
                              Ext.getCmp('ufa_AdminBSKViewForm').showMsg('Группа сведений удалён');  

                              Ext.getCmp('GridGroupTypes').getGrid().getStore().load();
                              Ext.getCmp('ufa_AdminBSKViewForm').getTreeObjects(); 
                			  return false;
        					}
                            
        				}
        		    });
                     
                 },  disabled: true },
				{name:'action_view', hidden: true},
				{name:'action_refresh', hidden: true},
				{name:'action_print', hidden: true}
			],           
			onLoadData: function() {

			}
        }); 

        /** add listener for GridGroupTypes and value for edit object name form */
        
        this.GridGroupTypes.getGrid().on(
            'rowclick', 
                function(){
                    var targetGroupType = this.getSelectionModel().getSelected(); 
                    
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
                    
                    Ext.getCmp('GridValues').getGrid().getStore().load({params:{Type_id:0}});
                    
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
                    html: 'Наименование',
                    bodyStyle: 'padding:5px; border: 0px;'
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
                                              Ext.getCmp('ufa_AdminBSKViewForm').showMsg('Группа сведений успешно добавлен');    

                                              newGroupType.setValue('');
                                              Ext.getCmp('GridGroupTypes').getGrid().getStore().load();
                                              Ext.getCmp('GridObjects').getGrid().getStore().load();
                                              Ext.getCmp('ufa_AdminBSKViewForm').getTreeObjects(); 
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
                    html: 'Наименование',
                    bodyStyle: 'padding:5px; border: 0px'
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
                                              Ext.getCmp('ufa_AdminBSKViewForm').getTreeObjects(); 
                                              Ext.getCmp('editGroupTypePanel').hide();
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
                               Ext.getCmp('editGroupTypePanel').hide();    
                           } 
                 }            
            ]           
        });     
        
        /** Типы сведений предметов наблюдения */
             
		this.GridTypes = new sw.Promed.ViewFrame({
			id: 'GridTypes',
			region: 'center',
			contextmenu: false,
            border: false,
            region: 'center',
            layout: 'fit',            
			height: Ext.getBody().getHeight()*0.897,
            object: 'GridTypes',
			dataUrl: '/?c=ufa_BSK_Register&m=getListTypes',
			autoLoadData: false,
            focusOnFirstLoad: false,
            viewConfig: {
                        forceFit: true
            },                        
			stringfields: [
				{name: 'BSKObservElement_id', type: 'int', hidden: true},
                {name: 'BSKObservElementFormat_id',type: 'int', hidden: true},                
				{name: 'BSKObservElement_name', header: 'Наименование', id: 'autoexpand'},
                {name: 'BSKObservElementFormat_name', header: 'формат', width:70, align:'center'},
                {name: 'BSKObservElement_symbol', header: 'обозначение', width:50, align:'center'},
                {name: 'BSKObservElement_formula', header: 'формула', width:50},
                {name: 'BSKObservElement_Sex_id', header: 'пол', width:50, align:'center', renderer: function(v,p,r){if(v == 3) return ''; else {return (v == 1) ? 'М' : 'Ж'}}},
                {name: 'BSKObservElement_stage', header: 'этап', width:50, align:'center'/*, renderer: function(v,p,r){return (v == 1) ? 'Анкета' : 'Доп. ислед.'}*/},
                {name: 'BSKObservElement_minAge', header: 'от', width:50, align:'center'},
                {name: 'BSKObservElement_maxAge', header: 'до', width:40, align:'center'},
                {name: 'BSKObservElement_IsRequire', header: 'обязателен', width:40, align:'center', renderer: function(v,p,r){return (v==1) ? 'Да' : 'Нет'}},
                {name: 'BSKUnits_name', header: 'ед. изм.',  width:50}
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
                 handler: function() {this.editTypePanel.show();this.addTypePanel.hide()}.createDelegate(this) 
                 },
                {
                 name:'action_delete', 
                 text: 'Удалить', 
                 handler: function(){
                     var record_id = Ext.getCmp('GridTypes').getGrid().getSelectionModel().getSelected().id;
 
                     Ext.Ajax.request({
        		 		url: '/?c=ufa_BSK_Register&m=deleteType',
        				params: {
        				    Type_id : record_id,
                            pmUser_updID : null
        				},
        				callback: function(options, success, response) {
                            if (success === true) {
                              Ext.getCmp('ufa_AdminBSKViewForm').showMsg('Тип сведений удалён');   

                              Ext.getCmp('GridTypes').getGrid().getStore().load();
                              Ext.getCmp('ufa_AdminBSKViewForm').getTreeObjects(); 
                			  return false;
        					}
                            
        				}
        		    });
                     
                 },  disabled: true },
				{name:'action_view', hidden: true},
				{name:'action_refresh', hidden: true},
				{name:'action_print', hidden: true}
			],           
			onLoadData: function() {

			}
        }); 
        this.GridTypes.getGrid().viewConfig.forceFit = true;
        this.GridTypes.getGrid().doLayout(true);           

 	    /**  Форма добавления нового сведения для предмета наблюдения */
       
        // единицы измерения 
        var UnitsStore = new Ext.data.JsonStore({
            url: '/?c=ufa_BSK_Register&m=getUnits',
            fields: ['BSKUnits_name','BSKUnits_name']
        });

		var UnitsCombo = new Ext.form.ComboBox({
			id : 'unitsCombo',
            fieldLabel: 'Единица измерения',
			forceSelection : true,
			store: UnitsStore,
			displayField:'BSKUnits_name',
			typeAhead: true,
            editable : true,
            anchor: '100%',
			mode: 'local',
            triggerAction: 'all',
			emptyText:'Укажите единицу измерения',
			selectOnFocus:true
		});
        
        UnitsStore.load();
 
        //Этап сведений
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
			mode: 'local'
		});   
        
        //Пол пациента
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
			mode: 'local'
		});   
        
        //форматы типов сведений
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
                    bodyStyle: 'padding:5px; border: 0px;'
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
                    bodyStyle: 'padding:5px; border: 0px; margin-top:5px'
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
                    html: 'Единица измерения',
                    bodyStyle: 'padding:5px; border: 0px'
                },                
                UnitsCombo,                            
                {
                    xtype: 'panel',
                    html: 'Этап сведений',
                    id: 'stage',
                    layuot: 'anchor',
                    bodyStyle: 'padding:5px; border: 0px'
                },                                  
                StageCombo, 
                {
                    xtype: 'panel',
                    html: '', 
                    layuot: 'anchor',
                    bodyStyle: 'margin:5px'
                },                 
                {
                    xtype: 'checkbox',
                    boxLabel : 'Ответ на вопрос обязателен',
                    id: 'IsRequire',
                    name: 'IsRequire',
                    checked: true
                },                                  
                {
                    xtype: 'panel',
                    html: 'Обозначение (только лат. буквы)',
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
                    width: 50
                }, 
                
                {
                    xtype: 'panel',
                    html: 'Возраст от',
                    id: 'minAgeLabel',
                    layuot: 'anchor',
                    bodyStyle: 'padding:5px; border: 0px'
                },                               
                {
                    xtype: 'textfield',
                    id: 'minAge',
                    name: 'minAge',
                    border: false,
                    width: 50                   
                },
                {
                    xtype: 'panel',
                    html: 'Возраст до',
                    id: 'maxAgeLabel',
                    layuot: 'anchor',
                    bodyStyle: 'padding:5px; border: 0px'
                },                 
                {
                    xtype: 'textfield',
                    id: 'maxAge',
                    name: 'maxAge',
                    border: false,
                    width: 50                   
                },                   
                {
                    xtype: 'panel',
                    html: 'Пол пациента',
                    id: 'sex',
                    layuot: 'anchor',
                    bodyStyle: 'padding:5px; border: 0px'
                },      
                                            
                SexCombo

                
                                                                                                   
            ],

            buttons : [
                {
                  text: 'Добавить',
                  iconCls: 'save16',
                  handler: function() {
                              var newType = Ext.getCmp('nameType');
                              var GroupType_id = Ext.getCmp('GridGroupTypes').getGrid().selected.data.BSKObservElementGroup_id;
                              var format = Ext.getCmp('typesCombo');
                              var unit = Ext.getCmp('unitsCombo');
                              var symbol = Ext.getCmp('symbol');
                              var formula = Ext.getCmp('formula');
                              var minAge =  Ext.getCmp('minAge');
                              var maxAge =  Ext.getCmp('maxAge');
                              var stage = Ext.getCmp('StageCombo');
                              var sex = Ext.getCmp('sexCombo');
                              var IsRequire = Ext.getCmp('IsRequire');
                              
                                                
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
                              else if(stage.getValue() == ''){
                                  Ext.getCmp('ufa_AdminBSKViewForm').showMsg('Необходимо указать этап типа сведений');  
                    			  return false;                               
                              }                           
                              else{
                                
                                
                                  //Проверка, на всяк случай, вдруг данное буквенное обозначение (д ля формул) уже используется каким-либо сведением
                                  if(symbol.getValue().replace(/\s/g,'') != ''){
                                        Ext.getCmp('ufa_AdminBSKViewForm').checkSymbol(symbol.getValue());

                                        if(typeof(Ext.getCmp('ufa_AdminBSKViewForm').checkSymbolTest) == 'undefined'){
                                              Ext.getCmp('ufa_AdminBSKViewForm').showMsg('Данное бкувенное обозначение уже используется для другого типа сведений');
                                              return false;
                                        }
                                  }
                                                                
                                  Ext.Ajax.request({
                        		 		url: '/?c=ufa_BSK_Register&m=addType',
                        				params: {
                        				    BSKObservElementGroup_id : Ext.getCmp('GridGroupTypes').getGrid().selected.data.BSKObservElementGroup_id,
                        				    nameType : newType.getValue(),
                                            unitText : unit.getValue(),
                                            formatText: format.getValue(),
                                            symbol : symbol.getValue(),
                                            formula : formula.getValue(),
                                            stage : stage.selectedIndex,
                                            Sex_id : (sex.selectedIndex < 1 || sex.selectedIndex > 2) ? 3 : sex.selectedIndex,
                                            minAge : minAge.getValue(),
                                            maxAge : maxAge.getValue(),
                                            IsRequire : IsRequire.checked === true ? 1 : 0
                        				},
                        				callback: function(options, success, response) {

                                            if (success === true) {
                                              Ext.getCmp('ufa_AdminBSKViewForm').showMsg('Тип сведений успешно добавлен');  
                                              
                                              newType.setValue('');
                                              unit.clearValue();
                                              format.clearValue();
                                              symbol.setValue('');
                                              formula.setValue('');
                                              sex.setValue('');
                                              stage.setValue('');
                                              minAge.setValue('');
                                              maxAge.setValue('');
                                              IsRequire.setValue(true);
                                              
                                              Ext.getCmp('GridTypes').getGrid().getStore().load();
                                              Ext.getCmp('ufa_AdminBSKViewForm').getTreeObjects(); 
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
                               Ext.getCmp('addTypePanel').hide();    
                           } 
                 }            
            ]
	   	}); 
        
        
        this.GridTypes.getGrid().on(
            'rowclick', 
                function(){
                    if(this.getSelectionModel().getSelected().data.BSK_TypesInfo_id == null){
                        Ext.getCmp('GridTypes').getAction('action_edit').setDisabled(true);
                    }
                    else{
                        Ext.getCmp('GridTypes').getAction('action_delete').setDisabled(false);
                        Ext.getCmp('GridTypes').getAction('action_edit').setDisabled(false);
                        Ext.getCmp('GridValues').getAction('action_add').setDisabled(false);
                    }
                
                    Ext.getCmp('GridValues').getGrid().getStore().load({
                        params: {
                                 Type_id: Ext.getCmp('GridTypes').getGrid().getSelectionModel().getSelected().id
                                }    
                    });
                }   
        );                        
        
        /** Редактирование типов сведений */
        
        // единицы измерения 
        var editUnitsStore = new Ext.data.JsonStore({
            url: '/?c=ufa_BSK_Register&m=getUnits',
            fields: ['BSKUnits_name']
        });

		var editUnitsCombo = new Ext.form.ComboBox({
			id : 'editunitsCombo',
            fieldLabel: 'Единица измерения',
			forceSelection : true,
			store: UnitsStore,
			displayField:'BSKUnits_name',
			typeAhead: true,
            editable : true,
            anchor: '100%',
			mode: 'local',
            triggerAction: 'all',
			emptyText:'Укажите единицу измерения',
			selectOnFocus:true
		});
        
        editUnitsStore.load();

		var editSexCombo = new Ext.form.ComboBox({
			id : 'editSexCombo',
            fieldLabel: 'Пол',
			store: SexStore,
			displayField:'Sex_Name',
            editable : false,
            width: 150,
            triggerAction : 'all',
			mode: 'local'
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
                    html: 'Наименование',
                    bodyStyle: 'padding:5px; border: 0px;'
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
                    html: 'Формат',
                    bodyStyle: 'padding:5px; border: 0px'
                },
                editTypesFormatCombo  ,
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
                    html: 'Единица измерения',
                    bodyStyle: 'padding:5px; border: 0px'
                },                
                editUnitsCombo,
                {
                    xtype: 'panel',
                    html: 'Этап сведений',
                    id: '',
                    layuot: 'anchor',
                    bodyStyle: 'padding:5px; border: 0px'
                },                                  
                StageCombo, 
                {
                    xtype: 'panel',
                    html: '', 
                    layuot: 'anchor',
                    bodyStyle: 'margin:5px'
                },                 
                {
                    xtype: 'checkbox',
                    boxLabel : 'Ответ на вопрос обязателен',
                    id: 'editIsRequire',
                    name: 'IsRequire',
                    checked: true
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
                    bodyStyle: 'padding:5px; border: 0px'
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
                    bodyStyle: 'padding:5px; border: 0px'
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
                    bodyStyle: 'padding:5px; border: 0px'
                },  
                SexCombo                                 
            ],

            buttons : [
                {
                  text: 'Сохранить',
                  iconCls: 'save16',
                  handler: function() {
                             
                              var editType = Ext.getCmp('editType');
                              var Type_id = Ext.getCmp('GridTypes').getGrid().getSelectionModel().getSelected().id;
                              var format = Ext.getCmp('edittypesCombo');
                              var unit = Ext.getCmp('editunitsCombo');
                              var symbol = Ext.getCmp('editsymbol');
                              var formula = Ext.getCmp('editformula');
                               
                              if(editType.getValue().length< 2){
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
                              else{
                                  Ext.Ajax.request({
                        		 		url: '/?c=ufa_BSK_Register&m=editType',
                        				params: {
                        				    Type_id : Type_id,
                        				    editType : editType.getValue(),
                                            unitText : unit.getValue(),
                                            formatText: format.getValue(),
                                            symbol : symbol.getValue(),
                                            formula : formula.getValue()
                        				},
                        				callback: function(options, success, response) {

                                            if (success === true) {
                                              Ext.getCmp('ufa_AdminBSKViewForm').showMsg('Тип сведений успешно изменён');   
                                              
                                              Ext.getCmp('GridTypes').getGrid().getStore().load();
                                              Ext.getCmp('ufa_AdminBSKViewForm').getTreeObjects(); 
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
       
        /** add listener for GridTypes and value for edit object name form */
        
        this.GridTypes.getGrid().on(
            'rowclick', 
                function(){
                    Ext.getCmp('GridValues').getAction('action_delete').setDisabled(true);
                    Ext.getCmp('GridValues').getAction('action_edit').setDisabled(true);
                    
                    var record = this.getSelectionModel().getSelected();
                    var BSKObservElement_id = record.data.BSKObservElement_id;
                    var BSKObservElement_name = record.data.BSKObservElement_name;
                    var BSKObservElement_stage = record.data.BSKObservElement_stage;
                    var BSKObservElement_IsRequire = record.data.BSKObservElement_IsRequire;
                    var BSKObservElement_minAge = record.data.BSKObservElement_minAge;
                    var BSKObservElement_maxAge = record.data.BSKObservElement_maxAge;

                    var BSKUnits_name = record.data.BSKUnits_name;
                    var BSKObservElementFormat_name = record.data.BSKObservElementFormat_name;
                    var BSKObservElement_symbol = record.data.BSKObservElement_symbol;
                    var BSKObservElement_formula = record.data.BSKObservElement_formula;
                    var BSKObservElementFormat_id = record.data.BSKObservElementFormat_id;
                    var BSKObservElement_Sex_id = record.data.BSKObservElement_Sex_id;                                                            
                    Ext.getCmp('editsymbol').setValue(BSKObservElement_symbol); 
                    Ext.getCmp('editformula').setValue(BSKObservElement_formula);
                    Ext.getCmp('StageCombo').setValue(BSKObservElement_stage);
                    Ext.getCmp('editIsRequire').setValue(BSKObservElement_IsRequire); 
                    Ext.getCmp('editminAge').setValue(BSKObservElement_minAge); 
                    Ext.getCmp('editmaxAge').setValue(BSKObservElement_maxAge); 
                    
                    switch(BSKObservElement_Sex_id){
                        case 1 : var sex_name = 'Мужской'; break; 
                        case 2 : var sex_name = 'Женский'; break;
                        default : var sex_name = '';
                    }
                    Ext.getCmp('editSexCombo').setValue(sex_name);
                    
                    Ext.getCmp('editType').setValue(BSKObservElement_name); 
                    Ext.getCmp('editunitsCombo').setValue(BSKUnits_name);
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
                    
                    Ext.getCmp('ufa_AdminBSKViewForm').addValuePanel.hide();
                    Ext.getCmp('ufa_AdminBSKViewForm').editValuePanel.hide();
                }    
        );      

    

        
        /** Значения сведений */
        
		this.GridValues = new sw.Promed.ViewFrame({
			id: 'GridValues',
			region: 'east',
			contextmenu: false,
            border: false,
            //height: 900,
			height: Ext.getBody().getHeight()*0.897,
            object: 'GridTypes',
			dataUrl: '/?c=ufa_BSK_Register&m=getListValues',
			autoLoadData: false,
			stringfields: [
				{name: 'BSK_TypesInfoValues_id', type: 'int', header: 'ID'},
				{name: 'BSK_TypesInfoValues_data', header: 'Значение', id: 'autoexpand'},
                {name: 'BSK_TypesInfoValues_min', header: 'Min',width: 50},
                {name: 'BSK_TypesInfoValues_max', header: 'Max',width: 50}
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
                        
                    Ext.getCmp('ufa_AdminBSKViewForm').manageCellsAddValuePanel('add');
                    this.addValuePanel.show();
                    this.editValuePanel.hide();  
				}.createDelegate(this),disabled: true },
                
                {name:'action_edit', text: 'Изменить', 
                 handler: function() {
                    var Value = Ext.getCmp('GridValues').getGrid().getSelectionModel().getSelected();        
                    var Value_id = Value.data.BSK_TypesInfoValues_id;
                    var Value_data =  Value.data.BSK_TypesInfoValues_data;
                    var editmin = Value.data.BSK_TypesInfoValues_min;
                    var editmax = Value.data.BSK_TypesInfoValues_max;
                    
                    Ext.getCmp('editNameValue').setValue(Value_data);
                    Ext.getCmp('editmin').setValue(editmin);
                    Ext.getCmp('editmax').setValue(editmax);
                    Ext.getCmp('ufa_AdminBSKViewForm').manageCellsAddValuePanel('edit');

                    this.addValuePanel.hide();
                    this.editValuePanel.show();                    
                
                }.createDelegate(this),disabled: true },
                
                {name:'action_delete', text: 'Удалить',
                 handler : function(){
                     var Value_id = Ext.getCmp('GridValues').getGrid().getSelectionModel().getSelected();

                     Ext.Ajax.request({
        		 		url: '/?c=ufa_BSK_Register&m=deleteValue',
        				params: {
        				    Value_id: Value_id.id
        				},
        				callback: function(options, success, response) {
                            if (success === true) {
                              Ext.getCmp('ufa_AdminBSKViewForm').showMsg('Значение удалёно');   

                              Ext.getCmp('GridValues').getGrid().getStore().load({
                                  params: {
                                           Type_id: Ext.getCmp('GridTypes').getGrid().getSelectionModel().getSelected().id
                                          }    
                              });

                              Ext.getCmp('ufa_AdminBSKViewForm').getTreeObjects(); 
                			  return false;
        					}
                            
        				}
        		    });                    
                 }.createDelegate(this),disabled: true},
				{name:'action_view', hidden: true},
				{name:'action_refresh', hidden: true},
				{name:'action_print', hidden: true}
			],           
			onLoadData: function() {

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
                    bodyStyle: 'padding:5px; border: 0px;'
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
                }                                                                                  
            ],

            buttons : [
                {
                  text: 'Сохранить',
                  iconCls: 'save16',
                  handler: function() {
                              var Type_id = Ext.getCmp('GridTypes').getGrid().getSelectionModel().getSelected().id;
                              
                              var nameValue = Ext.getCmp('nameValue');
                              var min = Ext.getCmp('min');
                              var max = Ext.getCmp('max');
                              
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
                        				    Type_id : Type_id,
                                            nameValue : nameValue.getValue(),
                                            min : min.getValue().replace(/,/,'.'),
                        				    max : max.getValue().replace(/,/,'.')
                                        },
                        				callback: function(options, success, response) {

                                            if (success === true) {
                                              
                                                Ext.getCmp('GridValues').getGrid().getStore().load({
                                                    params: {
                                                             Type_id: Ext.getCmp('GridTypes').getGrid().getSelectionModel().getSelected().id
                                                            }    
                                                });   
                                                
                                                Ext.getCmp('ufa_AdminBSKViewForm').showMsg('Значение успешно добавлено');                                           
                                                
                                                nameValue.setValue('');
                                                min.setValue('');
                                                max.setValue('');
                                                
                                                Ext.getCmp('ufa_AdminBSKViewForm').getTreeObjects(); 
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
                    bodyStyle: 'padding:5px; border: 0px;'
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
                }                                                                                  
            ],

            buttons : [
                {
                  text: 'Изменить',
                  iconCls: 'save16',
                  handler: function() {
                              var Values = Ext.getCmp('GridValues').getGrid().getSelectionModel().getSelected();
                              var Value_id = Values.id;
                              var editNameValue =  Ext.getCmp('editNameValue');
                              var editmin =  Ext.getCmp('editmin');
                              var editmax =  Ext.getCmp('editmax');
                              
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
                    				    Value_id : Value_id,
                                        editNameValue : editNameValue.getValue(),
                                        editmin : editmin.getValue().replace(/,/,'.'),
                    				    editmax : editmax.getValue().replace(/,/,'.')
                                    },
                    				callback: function(options, success, response) {

                                        if (success === true) {
                                          
                                            Ext.getCmp('GridValues').getGrid().getStore().load({
                                                params: {
                                                         Type_id: Ext.getCmp('GridTypes').getGrid().getSelectionModel().getSelected().id
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
                    var targetObject = this.getSelectionModel().getSelected(); 
                    
                    if(typeof(targetObject.json) != 'undefined'){
                        Ext.getCmp('GridValues').getAction('action_edit').setDisabled(false);
                        Ext.getCmp('GridValues').getAction('action_delete').setDisabled(false);    
                    
                        var Value = Ext.getCmp('GridValues').getGrid().getSelectionModel().getSelected();
                                  
                        var Value_id = Value.data.BSK_TypesInfoValues_id;
                        var Value_data =  Value.data.BSK_TypesInfoValues_data;
                        var editmin = Value.data.BSK_TypesInfoValues_min;
                        var editmax = Value.data.BSK_TypesInfoValues_max;
                        
                        Ext.getCmp('editNameValue').setValue(Value_data);
                        Ext.getCmp('editmin').setValue(editmin);
                        Ext.getCmp('editmax').setValue(editmax);                       
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
            bodyStyle: 'padding: 10px; font-family:tahoma!important;font-size:13px;'
        });
        
        this.GeneralPanel = new Ext.Panel(
		{   
		    title : '',
			bodyBorder: false,
			border: true,
			id: 'GeneralPanel',
            tree : 'Список предметов наблюдения пуст',
            //autoHeight: false,
            
            items: [
                 {  
                     xtype: 'tabpanel',
                     activeTab: 0, 
                     items :[
                        {
                            title: 'Управление',
                            items:[
                            {
                                xtype:'panel',
                                layout:'column',
                                items:[
                                        {                           
                                            title: 'Предметы наблюдения',
                                            columnWidth: .21,
                                            //height: 1000,
                                            items : [
                                                      this.addObjectPanel,
                                                      this.editObjectPanel,
                                                      this.GridObjects
                                                    ]
                                        },{
                                            title: 'Группы типов сведений',
                                            columnWidth: .21,
                                            //height: 1000,
                                            items : [
                                                     this.addGroupTypePanel,
                                                     this.editGroupTypePanel,
                                                     this.GridGroupTypes
                                                     ]
                                        },{
                                            title: 'Типы сведений',
                                            columnWidth: .40,
                                            //height: 1000,
                                            items : [
                                                     this.addTypePanel,
                                                     this.editTypePanel,
                                                     this.GridTypes
                                                     ]
                                        },{
                                            title: 'Значения сведений', 
                                            columnWidth: .18,
                                            //height: 1000,
                                            items : [
                                                    this.editValuePanel,
                                                    this.addValuePanel,
                                                    this.GridValues
                                                    ]
                                        }                               
                                      ]
                                    }
                                ]
                        },{
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
                  
                    ],
                    listeners: {
                        'tabchange':function(tab){
                            Ext.getCmp('ufa_AdminBSKViewForm').getTreeObjects();
                        }
                    }                                       
                 }
              
              /* {     
                    columnWidth: .15,
                    //height: 1000,                     
                    collapsible: false, 
                    title: 'Предосмотр предметов наблюдения',
                    items: [this.Tree]                

                },
                {                           
                    title: 'Предметы наблюдения',
                    columnWidth: .20,
                    //height: 1000,
                    items : [
                              this.addObjectPanel,
                              this.editObjectPanel,
                              this.GridObjects
                            ]
                },{
                    title: 'Группы типов сведений',
                    columnWidth: .21,
                    height: 1000,
                    items : [
                             this.addGroupTypePanel,
                             this.editGroupTypePanel,
                             this.GridGroupTypes
                             ]
                },{
                    title: 'Типы сведений',
                    columnWidth: .23,
                    height: 1000,
                    items : [
                             this.addTypePanel,
                             this.editTypePanel,
                             this.GridTypes
                             ]
                },{
                    title: 'Значения сведений', 
                    columnWidth: .25,
                    //height: 1000,
                    items : [
                            this.editValuePanel,
                            this.addValuePanel,
                            this.GridValues
                            ]
                },{
                    title: 'ед. измерения', 
                    columnWidth: .11,
                    //height: 1000,
                    items : [
                            this.editUnitPanel,
                            this.addUnitPanel,
                            this.GridUnits
                            ]
                } 
                */                
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

    manageCellsAddValuePanel : function(action){
        var Type_Format_id = Ext.getCmp('GridTypes').getGrid().getSelectionModel().getSelected().data.BSK_TypesInfo_Format_id;
        
        switch(action){
            case 'add' : 
               var label = 'labelNameValue';
               var name = 'nameValue';
               break;
            case 'edit' :
               var label = 'labelEditNameValue';
               var name = 'editNameValue';
               break;               
        }
        
        if(Type_Format_id == 3 || 
           Type_Format_id == 4 || 
           Type_Format_id == 7){     
             Ext.getCmp(name).hide();
             Ext.getCmp(label).hide();
        }
        else{
             Ext.getCmp(name).show();
             Ext.getCmp(label).show();
        }            
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
    checkSymbol : function(symbol){
        Ext.Ajax.request({
     		url: '/?c=ufa_BSK_Register&m=checkSymbol',
    		params: {
    		    symbol : symbol
    		},
    		callback: function(options, success, response) {
                Ext.getCmp('ufa_AdminBSKViewForm').checkSymbolTest = '';
                
                var rt = Ext.util.JSON.decode(response.responseText);
                
                Ext.getCmp('ufa_AdminBSKViewForm').checkSymbolTest = typeof(rt[0]);      
    		}
        });                                  
    }  

});