/**
* BSKEventsWindow - окно для добавления событий пациенту в регистре БСК.
*  
*
* PromedWeb - The New Generation of Medical Statistic Software
* 
*
* @access       public
* @version      25.06.2015 
* @author       Васинский Игорь (ООО "ЭМСИС" г.Уфа)
*/

sw.Promed.BSKEventsWindow = Ext.extend(sw.Promed.BaseForm, {
    alwaysOnTop: false,
	id    : 'BSKEventsWindow', 
	objectName    : 'BSKEventsWindow',
	objectSrc     : '/jscore/Forms/Admin/Ufa/BSKEventsWindow.js',    
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
			buttons : [{ 
				text: Ext.getCmp('BSKEventsWindow').Action == 'add' ? 'Добавить' : 'Сохранить', 
				iconCls: Ext.getCmp('BSKEventsWindow').Action == 'add' ? 'add16' : 'save16',
				  handler: function(){
								
                                switch (Ext.getCmp('EventType').getValue()){
									case "Диагноз":										
										var EventType = 1;
										break;
									case "Услуга":
										var EventType = 2;
										break;
								}
                                //var EventType = Ext.getCmp('EventType').selectedIndex == 1 ? 1 : 2;  
                                var EventDate = Ext.getCmp('EventDate').getValue().dateFormat('Y-m-d')+' 00:00:00';
                                var DiagCombo = Ext.getCmp('comboDiag_id');
                                var UslugaComplexCombo = Ext.getCmp('comboUslugaComplex_id');
                                var EventDescription = Ext.getCmp('EventDescription').getValue();    

                                
                                if(EventDate == ''){
                                    this.ownerCt.showMsg('Укажите дату события');
                                    return;
                                }
                                
                                //-1 и 2 Услуга
                                // 1 Диагноз          
                                               
                                if(EventType == 2) {                                     
                                    if(UslugaComplexCombo == ''){
                                        this.ownerCt.showMsg('Укажите услугу события');
                                        return;
                                    }
                                    
                                    var indexStore = UslugaComplexCombo.getStore().findBy(function(rec) { return rec.get('UslugaComplex_id') == UslugaComplexCombo.getValue(); });
                                    var Store = UslugaComplexCombo.getStore();
                                    var rec = Store.getAt(indexStore);
                                    var EventCode = rec.get('UslugaComplex_Code');
                                    var EventName = rec.get('UslugaComplex_Name');                                    
                                }
                                else if(EventType == 1) {
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
                                var controllerParams = {
									BSKEvents_Type : EventType,
									BSKEvents_setDT: EventDate,
									BSKEvents_Code : typeof EventCode == 'undefined' ? null : EventCode,
									BSKEvents_Name : typeof EventName == 'undefined' ? null : EventName,
									BSKEvents_Description : EventDescription  
								}
								if (Ext.getCmp('BSKEventsWindow').Action == 'add') {
									controllerParams.Person_id = Ext.getCmp('personBskRegistryWindow').Person_id;
									var controllerUrl = '/?c=BSK_Register_User&m=addEvent';
								} else {								
									controllerParams.BSKEvents_id = Ext.getCmp('BSKEventsWindow').Events_id;
									var controllerUrl = '/?c=BSK_Register_User&m=saveEvent';
								}			
                                Ext.Ajax.request({
                    		 		url: controllerUrl,
                    				params: controllerParams,
                    				callback: function(options, success, response) {

                    				     if(success === true){
                    				         Ext.getCmp('BSKEventsWindow').refresh();  
                                             //Ext.getCmp('BSKEventsWindow').showMsg('Событие успешно добавлено!');
                                             
                                             Ext.getCmp('EventsGrid').getGrid().getStore().load({
                                                MorbusType_id : Ext.getCmp('personBskRegistryWindow').MorbusType_id,
                                                Person_id :  Ext.getCmp('personBskRegistryWindow').Person_id 
                                             });
                                             Ext.getCmp('events').doLayout();                                             
                    				     }
                                         else{
                                            Ext.getCmp('BSKEventsWindow').showMsg('Произошла ошибка при попытки сохранения события!');
                                         }
                                    }
                                });   
                                                         

				  }
				},
				{ text:'Отмена', 
				  iconCls: 'cancel16', 
				  handler: function(){ 
								Ext.getCmp('BSKEventsWindow').refresh();
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
                        allowBlank: false,
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
                                    Ext.getCmp('comboUslugaComplex_id').ownerCt.hide();
                                }
                                else{
                                    Ext.getCmp('comboDiag_id').ownerCt.hide();
                                    Ext.getCmp('comboUslugaComplex_id').ownerCt.show();                                    
                                }
                                
                                if(this.selectedIndex == 0){
                                    //this.setValue(this.getStore().getAt(2).get('EventType_Name'));
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
                                    Ext.getCmp('BSKEventsWindow').showMsg('Указана не корректная дата события');
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
                                registryType: 'BSKRegistry',
                                maskRe : /[iш\.\d]/i,
                                listeners : {
                                    'keydown' : function(e){
                                        /^[iш]{1}.+/i.test(e.getRawValue()) ? true : this.setRawValue('I');
                                    }       
                                }
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
        						xtype: 'swuslugacomplexnewcombo',
                                registryType: 'BSKRegistry',
                                id: 'comboUslugaComplex_id',
        						hiddenName: 'UslugaComplex_id',
        						allowBlank:false,
                                width: 550,
        						listWidth: 550,
                                maskRe : /[aф\.\d]/i,
                                listeners : {
                                    'keydown' : function(e){
                                        /^[aф]{1}\.16\..+/i.test(e.getRawValue()) ? true : this.setRawValue('A16.');
                                    }       
                                }
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

		sw.Promed.BSKEventsWindow.superclass.initComponent.apply(this, arguments);
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
		this.Action = params.params.Action;
		switch (this.Action) {
			case 'add':					
				this.Person_id = params.params.Person_id;
				break;
			case 'edit':					
				this.setTitle('Регистр БСК. Редактирование события');

				this.findById('EventType').setValue(params.params.Events_Type);
				this.findById('EventType').setDisabled(true);
				this.findById('EventDate').setValue(params.params.Events_setDate);
				this.findById('EventDescription').setValue(params.params.EventDescription);

				if(params.params.Events_Type == 'Услуга'){
					var id_EventName = 'comboUslugaComplex_id';
					this.findById('comboUslugaComplex_id').ownerCt.setVisible(true);
					this.findById('comboDiag_id').setVisible(false);
				}
				else{
					var id_EventName = 'comboDiag_id';  
					this.findById('comboDiag_id').ownerCt.setVisible(true);
					this.findById('comboUslugaComplex_id').ownerCt.setVisible(false);
				}

				this.findById(id_EventName).setValue(params.params.Events_Name);

				//Чтоб не читать по новой из грида - самый главный атрибут формы редактирования
				this.Events_id = params.params.Events_id;
				break;
		}
		sw.Promed.BSKEventsWindow.superclass.show.apply(this, arguments);
		this.findById('comboUslugaComplex_id').setUslugaCategoryList([ 'gost2011' ]);
	}
});