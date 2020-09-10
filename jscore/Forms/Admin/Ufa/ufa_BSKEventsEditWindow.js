/**
* ufa_BSKEventsEditWindow - окно для добавления событий пациенту в регистре БСК.
*  
*
* PromedWeb - The New Generation of Medical Statistic Software
* 
*
* @access       public
* @version      25.06.2015 
* @author       Васинский Игорь (ООО "ЭМСИС" г.Уфа)
*/

sw.Promed.ufa_BSKEventsEditWindow = Ext.extend(sw.Promed.BaseForm, {
    alwaysOnTop: true,
	id    : 'ufa_BSKEventsEditWindow', 
	objectName    : 'ufa_BSKEventsEditWindow',
	objectSrc     : '/jscore/Forms/Admin/Ufa/ufa_BSKEventsEditWindow.js',    
	layout: 'form',
	//plain: true,    
    bodyStyle: 'padding:20px',
	title : 'Регистр БСК. Редактирование события',
	modal : true,
	width : 710,
	height:500,
	closable : false,
	closeAction   : 'close',
	draggable     : true,
    buttonAlign   : 'right',
    showMsg : function(msgText){
                sw.swMsg.show(
            				{
            					icon: Ext.MessageBox.ERROR,
            					title: 'Ошибка',
            					msg: msgText,
            					buttons: Ext.Msg.OK
            				})        
    }, 
	initComponent: function() 
	{   

		Ext.apply(this, 
		{   
			autoHeight: true,
			buttons : [
				{ text:'Сохранить', iconCls: 'save16', 
				  handler: function(){
								
                                
                                var EventType = Ext.getCmp('EventType_edit').getValue() == 'Диагноз' ? 1 : 2;  
                                var EventDate = Ext.getCmp('EventDate_edit').getValue().dateFormat('Y-m-d')+' 00:00:00';
                                var DiagCombo = Ext.getCmp('comboDiag_id_edit');
                                var UslugaCombo = Ext.getCmp('comboUsluga_id_edit');
                                var EventDescription = Ext.getCmp('EventDescription_edit').getValue();

                                
                                if(EventDate == ''){
                                    this.ownerCt.showMsg('Укажите дату события');
                                    return;
                                }
                                
                                //-1 и 2 Услуга
                                // 1 Диагноз                                                               
                                if(EventType == 2){                                     
                                    if(UslugaCombo == ''){
                                        this.ownerCt.showMsg('Укажите услугу события');
                                        return;
                                    }
                                    
                                    var indexStore = UslugaCombo.getStore().findBy(function(rec) { return rec.get('Usluga_id') == UslugaCombo.getValue(); });
                                    var Store = UslugaCombo.getStore();
                                    var rec = Store.getAt(indexStore);
                                    
                                    if(typeof rec == 'object'){
                                        var EventCode = rec.get('Usluga_Code');
                                        var EventName = rec.get('Usluga_Name');                                    
                                    } 
                                }
                                else{
                                    if(DiagCombo == ''){
                                        this.ownerCt.showMsg('Укажите диагноз события');
                                        return;
                                    }
                                    
                                    var indexStore = DiagCombo.getStore().findBy(function(rec) { return rec.get('Diag_id') == DiagCombo.getValue(); });
                                    var Store = DiagCombo.getStore();
                                    var rec = Store.getAt(indexStore);
                                    
                                    if(typeof rec == 'object'){
                                        var EventCode = rec.get('Diag_Code');
                                        var EventName = rec.get('Diag_Name');    
                                    }                                                                         
                                }            
                                
                                Ext.Ajax.request({
                    		 		url: '/?c=ufa_BSK_Register_User&m=saveEvent',
                    				params: {
                    				    BSKEvents_Type : EventType,
                                        BSKEvents_id : Ext.getCmp('ufa_BSKEventsEditWindow').Events_id,
                                        BSKEvents_setDT: EventDate,
                                        BSKEvents_Code : typeof EventCode == 'undefined' ? null : EventCode,
                                        BSKEvents_Name : typeof EventName == 'undefined' ? null : EventName,
                                        BSKEvents_Description : EventDescription                                          
                    				},
                    				callback: function(options, success, response) {

                    				     if(success === true){
                    				         Ext.getCmp('ufa_BSKEventsEditWindow').refresh();  
                                             //Ext.getCmp('ufa_BSKEventsEditWindow').showMsg('Событие успешно сохранено!');
                                             
                                             Ext.getCmp('EventsGrid').getGrid().getStore().load({
                                                MorbusType_id : Ext.getCmp('ufa_personBskRegistryWindow').MorbusType_id,
                                                Person_id :  Ext.getCmp('ufa_personBskRegistryWindow').Person_id 
                                             });
                                             Ext.getCmp('events').doLayout();                                             
                    				     }
                                         else{
                                            Ext.getCmp('ufa_BSKEventsEditWindow').showMsg('Произошла ошибка при попытки сохранения события!');
                                         }
                                    }
                                });   
                                                         

				  }
				},
				{ text:'Отмена', 
				  iconCls: 'cancel16', 
				  handler: function(){ 
								Ext.getCmp('ufa_BSKEventsEditWindow').refresh();
							}
				  },
				{ text:'Справка', 
				  iconCls: 'help16', 
                  hidden: true,
				  handler: function(){
				              ShowHelp(Ext.getCmp('swFilterGridPlugin').title);
				  }
				}                                      
			],
			items : [ 
                     {
            			id : 'EventType_edit',
                        xtype : 'combo',
                        fieldLabel: 'Тип события',
            			store: new Ext.data.SimpleStore({
            					fields:
            					[
            						{name: 'EventType_id', type: 'int'},
            						{name: 'EventType_Name', type: 'string'}
            					],
            					data: [[1,'Диагноз'], [2,'Услуга']]
                        }),
            			displayField:'EventType_Name',
                        hiddenName : 'EventType_id',
                        editable : false,
                        width: 100,
                        triggerAction : 'all',
            			mode: 'local',
                        listeners : {
                            'render' : function(){
                                this.setValue(this.getStore().getAt(1).get('EventType_Name'));
                                Ext.getCmp('comboDiag_id_edit').ownerCt.hide();
                            },
                            'select' : function(){
                                if(this.getValue() == 'Диагноз'){
                                    Ext.getCmp('comboDiag_id_edit').ownerCt.show();
                                    Ext.getCmp('comboUsluga_id_edit').ownerCt.hide();
                                }
                                else{
                                    Ext.getCmp('comboDiag_id_edit').ownerCt.hide();
                                    Ext.getCmp('comboUsluga_id_edit').ownerCt.show();                                    
                                }
                                
                                if(this.selectedIndex == 0){
                                    this.setValue(this.getStore().getAt(2).get('EventType_Name'));
                                }
                                
                            }
                        }
                     }, 
					{
						xtype: 'datefield',
                        id : 'EventDate_edit',
						hiddenName: 'Event_setDate',
						allowBlank:true,
                        fieldLabel : 'Дата события',
                        width: 100,
						format: 'd.m.Y',
						plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
                        listeners : {
                            'blur' : function(){
                                if(new Date(this.getValue()) > new Date()){
                                    Ext.getCmp('ufa_BSKEventsEditWindow').showMsg('Указана не корректная дата события');
                                    this.setValue(new Date());
                                }
                            }
                        }
					},   
                     {
                       xtype : 'panel',
                       frame : false,
                       layout : 'form', 
                       bodyStyle: 'background-color:#dfe8f5;border:0px' ,         
                       items : [
                             {
        						checkAccessRights: true,
                                id: 'comboDiag_id_edit',
        						allowBlank: false,
        						fieldLabel: 'Диагноз',
        						hiddenName: 'Diag_id',
        						listWidth: 550,
        						tabIndex: TABINDEX_EREF + 8,
        						validateOnBlur: true,
                                width: 515,
        						xtype: 'swdiagcombo'
        					},                        
                       ]
                        
                     },   
                     {
                       xtype : 'panel',
                       frame : false,
                       layout : 'form', 
                       bodyStyle: 'background-color:#dfe8f5;border:0px' ,         
                       items : [                                           
        					{
        						xtype: 'swuslugacombo',
                                id: 'comboUsluga_id_edit',
        						hiddenName: 'Usluga_id',
        						allowBlank:false,
                                width: 550,
        						listWidth: 550
        					},                           
                     ], 
                    },
                                        
					{
						xtype  : 'textarea',
						id     : 'EventDescription_edit',
                        fieldLabel : 'Примечание',
                        layout : 'form',
                        labelWidth : 200,
						width: 550,
                        
					}                        
            ]
		});

		sw.Promed.ufa_BSKEventsEditWindow.superclass.initComponent.apply(this, arguments);
	},

	refresh : function(){

					sw.codeInfo.lastObjectName = this.objectName;
					sw.codeInfo.lastObjectClass = this.objectClass;
					if (sw.Promed.Actions.loadLastObjectCode)
					{
						sw.Promed.Actions.loadLastObjectCode.setHidden(false);
						sw.Promed.Actions.loadLastObjectCode.setText('Обновить '+this.objectName+' ...');
					}
					// Удаляем полностью объект из DOM, функционал которого хотим обновить
					this.hide();
					this.close();
					window[this.objectName] = null;
					//delete sw.Promed[this.objectName];        
	},  
	listeners: 
	{
		'hide': function() 
		{

           main_menu_panel.setDisabled(false);

			if (this.refresh)
				this.onHide();
		}

	},

	show: function() 
	{      
            this.findById('EventType_edit').setValue(arguments[0].params.Events_Type);
            this.findById('EventDate_edit').setValue(arguments[0].params.Events_setDate);
            this.findById('EventDescription_edit').setValue(arguments[0].params.EventDescription);
            
            if(arguments[0].params.Events_Type == 'Услуга'){
                var id_EventName_edit = 'comboUsluga_id_edit';
                this.findById('comboUsluga_id_edit').ownerCt.setVisible(true);
                this.findById('comboDiag_id_edit').setVisible(false);
            }
            else{
                var id_EventName_edit = 'comboDiag_id_edit';  
                this.findById('comboDiag_id_edit').ownerCt.setVisible(true);
                this.findById('comboUsluga_id_edit').ownerCt.setVisible(false);
            }
            
            this.findById(id_EventName_edit).setValue(arguments[0].params.Events_Name);
            
            //Чтоб не читать по новой из грида - самый главный атрибут формы редактирования
            this.Events_id = arguments[0].params.Events_id;
            sw.Promed.ufa_BSKEventsEditWindow.superclass.show.apply(this, arguments);
                
	}

});