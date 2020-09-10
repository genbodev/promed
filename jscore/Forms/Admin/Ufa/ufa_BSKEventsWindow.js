/**
* ufa_BSKEventsWindow - окно для добавления событий пациенту в регистре БСК.
*  
*
* PromedWeb - The New Generation of Medical Statistic Software
* 
*
* @access       public
* @version      25.06.2015 
* @author       Васинский Игорь (ООО "ЭМСИС" г.Уфа)
*/

sw.Promed.ufa_BSKEventsWindow = Ext.extend(sw.Promed.BaseForm, {
    alwaysOnTop: true,
	id    : 'ufa_BSKEventsWindow', 
	objectName    : 'ufa_BSKEventsWindow',
	objectSrc     : '/jscore/Forms/Admin/Ufa/ufa_BSKEventsWindow.js',    
	layout: 'form',
	//plain: true,    
    bodyStyle: 'padding:20px',
	title : 'Регистр БСК. Новое событие',
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
				{ text:'Добавить', iconCls: 'add16', 
				  handler: function(){
								
                                
                                var EventType = Ext.getCmp('EventType').selectedIndex == 1 ? 1 : 2;  
                                var EventDate = Ext.getCmp('EventDate').getValue().dateFormat('Y-m-d')+' 00:00:00';
                                var DiagCombo = Ext.getCmp('comboDiag_id');
                                var UslugaCombo = Ext.getCmp('comboUsluga_id');
                                var EventDescription = Ext.getCmp('EventDescription').getValue();    

                                
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
                                    var EventCode = rec.get('Usluga_Code');
                                    var EventName = rec.get('Usluga_Name');                                    
                                }
                                else{
                                    if(DiagCombo == ''){
                                        this.ownerCt.showMsg('Укажите диагноз события');
                                        return;
                                    }
                                    
                                    var indexStore = DiagCombo.getStore().findBy(function(rec) { return rec.get('Diag_id') == DiagCombo.getValue(); });
                                    var Store = DiagCombo.getStore();
                                    var rec = Store.getAt(indexStore);                                    
                                    var EventCode = rec.get('Diag_Code');
                                    var EventName = rec.get('Diag_Name');                                                                             
                                }
    
                                //return;                               
                                
                                Ext.Ajax.request({
                    		 		url: '/?c=ufa_BSK_Register_User&m=addEvent',
                    				params: {
                    				    BSKEvents_Type : EventType,
                                        Person_id : Ext.getCmp('ufa_personBskRegistryWindow').Person_id,
                                        BSKEvents_setDT: EventDate,
                                        BSKEvents_Code : EventCode,
                                        BSKEvents_Name : EventName,
                                        BSKEvents_Description : EventDescription                                          
                    				},
                    				callback: function(options, success, response) {

                    				     if(success === true){
                    				         Ext.getCmp('ufa_BSKEventsWindow').refresh();  
                                             //Ext.getCmp('ufa_BSKEventsWindow').showMsg('Событие успешно добавлено!');
                                             
                                             Ext.getCmp('EventsGrid').getGrid().getStore().load({
                                                MorbusType_id : Ext.getCmp('ufa_personBskRegistryWindow').MorbusType_id,
                                                Person_id :  Ext.getCmp('ufa_personBskRegistryWindow').Person_id 
                                             });
                                             Ext.getCmp('events').doLayout();                                             
                    				     }
                                         else{
                                            Ext.getCmp('ufa_BSKEventsWindow').showMsg('Произошла ошибка при попытки сохранения события!');
                                         }
                                    }
                                });   
                                                         

				  }
				},
				{ text:'Отмена', 
				  iconCls: 'cancel16', 
				  handler: function(){ 
								Ext.getCmp('ufa_BSKEventsWindow').refresh();
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
            			id : 'EventType',
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
                                Ext.getCmp('comboDiag_id').ownerCt.hide();
                            },
                            'select' : function(){
                                if(this.getValue() == 'Диагноз'){
                                    Ext.getCmp('comboDiag_id').ownerCt.show();
                                    Ext.getCmp('comboUsluga_id').ownerCt.hide();
                                }
                                else{
                                    Ext.getCmp('comboDiag_id').ownerCt.hide();
                                    Ext.getCmp('comboUsluga_id').ownerCt.show();                                    
                                }
                                
                                if(this.selectedIndex == 0){
                                    this.setValue(this.getStore().getAt(2).get('EventType_Name'));
                                }
                                
                            }
                        }
                     }, 
					{
						xtype: 'datefield',
                        id : 'EventDate',
						hiddenName: 'Event_setDate',
						allowBlank:true,
                        fieldLabel : 'Дата события',
                        width: 100,
						format: 'd.m.Y',
						plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
                        listeners : {
                            'blur' : function(){
                                if(new Date(this.getValue()) > new Date()){
                                    Ext.getCmp('ufa_BSKEventsWindow').showMsg('Указана не корректная дата события');
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
                                id: 'comboDiag_id',
        						allowBlank: false,
        						fieldLabel: 'Диагноз',
        						hiddenName: 'Diag_id',
        						listWidth: 550,
        						tabIndex: TABINDEX_EREF + 8,
        						validateOnBlur: true,
                                width: 515,
        						xtype: 'swdiagcombo',
                                //maskRe : /^(i|ш){1}[0-9]{2}/gi
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
                                id: 'comboUsluga_id',
        						hiddenName: 'Usluga_id',
        						allowBlank:false,
                                width: 550,
        						listWidth: 550,
                                //maskRe : /^(a|ф){1}/gi
        					},                           
                     ], 
                    },
                                        
					{
						xtype  : 'textarea',
						id     : 'EventDescription',
                        fieldLabel : 'Примечание',
                        layout : 'form',
                        labelWidth : 200,
						width: 550,
                        
					}                        
            ]
		});

		sw.Promed.ufa_BSKEventsWindow.superclass.initComponent.apply(this, arguments);
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

	show: function(params) 
	{          
            this.Person_id = params.Person_id;
            
            sw.Promed.ufa_BSKEventsWindow.superclass.show.apply(this, arguments);
                
	}

});