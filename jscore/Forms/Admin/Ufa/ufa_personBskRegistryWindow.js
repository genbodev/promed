/**
* Окно управления просмотра специфики регистра БСК
* пользовательсякая часть 
*
*
* @package      BSK
* @access       All
* @autor		Васинский Игорь
* @version      20.08.2014
*/

sw.Promed.ufa_personBskRegistryWindow = Ext.extend(sw.Promed.BaseForm,
{
    title: 'Регистр болезней системы кровообращения',
    bodyStyle: 'padding:5px; border: 0px;',
    //width: 900,
    //maximizable: true,
    maximized: true,
    idInDb : [/*84*/34,35,36,37,43,44,58,59,60,61,67,78,79,
              /*88*/176,177,178,179,180,181,182,183,184,
              /*89*/220,221,222,223,
              /*50*/329,330,331,332
              ],
    robot: true,
	isUserClick : true, //Кликаем по полям с помощью мыши или таба
	listIDSfocus: [],
	isButtonAdd: false,//Кнопка "Добавить" нажата
    clickToPN : 0,
	postfixQuestions: [],
    closable: true,
    editableForm : true,
    closeAction: 'hide',
    BSKRegistry_id : false,
    MorbusType_id : 0,
	clickToRow : false,
    Periods : [],	
    PrefixQuest : 0,
    Reverse : false,
	modal : true,
	Lpu_id: false,
	//collapsedCombo: true,
    newAnkets : false,
	frame: true,
	anthropometry: { 
		//Для каждого предмета наблюдения: рост, вес, талия, имт
		84:[107,108,109,110],
		88:[142,143,0,172],
		89:[208,209,211,210],
		50:[318,319,321,320]
	},
	open1: true, //Первое открытие окна
    buttonAlign: "left",
	objectName: 'ufa_personBskRegistryWindow',
	id: 'ufa_personBskRegistryWindow',
	objectSrc: '/jscore/Forms/Admin/Ufa/ufa_personBskRegistryWindow.js',
	switchFieldOnTab: function(id, isFirst) {
		//console.log(id, isFirst)
		var form = Ext.getCmp('ufa_personBskRegistryWindow');
		/*if (form.collapsedCombo == false) {//Если есть открытый комбобокс с невыбранным ответом, то переход по Tab не срабатывает
			return false;
		}*/
		
		var Periods =form.Periods[form.MorbusType_id];
		
		form.isUserClick = false;
		if(isFirst){
			form.isUserClick = true;			
			setTimeout(function(){
				if(form.isButtonAdd === true  || typeof Periods == 'undefined' 
						|| (typeof Periods == 'object' && Periods.length == 0) 
						|| (typeof Periods == 'array' && Periods.length == 0)
						|| !form.elemDisabled()){							
					form.isButtonAdd = false;
					//При начальной загрузке данных предмета наблюдения создать массив входящих в него id ответов
					if(form.listIDSfocus.length == 0){
						var MorbusType = form.MorbusType_id;
						for(var k in form.listIDS){
							var listIDSsmall = form.listIDS[k];
							
							if(typeof listIDSsmall == 'object'){
								var id =  form.listIDS[k].id;
								//вопрос уже прорисован	
								if(typeof Ext.getCmp('Answer_'+id) != 'undefined'){
									
									//Ext.getCmp('Answer_'+id).setDisabled(form.elemDisabled());
									//console.log('Answer_'+id, form.elemDisabled());
									//и доступен для редактирования
									//console.log('disabled Answer_'+id,Ext.getCmp('Answer_'+id).disabled);
									if(!Ext.getCmp('Answer_'+id).disabled){		
										form.listIDSfocus.push(id);
									}
								}
								else{	
									//Вопрос не прорисован, есть приглашения для вопроса и это не индекс массы тела
									if (!id.inlist(['110','172','210','320'])) {
										form.listIDSfocus.push(id);		
									}
														
								}
							}
						}
					}

					var num_elem = 0;	
					
					if (document.getElementById('i'+form.listIDSfocus[num_elem]) != null) {
						document.getElementById('i'+form.listIDSfocus[num_elem]).click();	
					}
					else {
						Ext.getCmp('Answer_'+form.listIDSfocus[num_elem]).focus();
					}
					form.isActiveElement = num_elem;//Первая запись активного элемента
				}
			},3900);			
		}
		else{
			

			
			//Переключение к следующему вопросу для ответа по табу
			var num_elem = form.listIDSfocus.indexOf(parseInt(id))+1;		
			
			//Если следующего вопроса не существует, то выходим
			if (form.listIDSfocus[num_elem] == undefined) return false;		
			var field = Ext.getCmp('addAnswer_' + form.listIDSfocus[num_elem]);	

			if (/*indexActiveElement == -1*/form.isActiveElement == undefined) {
				//допускаем переход по Tab
			}
			else if (num_elem != form.isActiveElement) {
				form.isActiveElement = num_elem;
				form.switchFieldOnTab(form.listIDSfocus[num_elem-1], false);
				return;  //При несовпадении текущего индекса вопроса с идущим по очереди переход по Tab запрещаем
						//и создаем новый порядок обхода по tab
			}
			
			if  (typeof(field) != 'undefined') {
				document.getElementById('i' + form.listIDSfocus[num_elem]).click();
				if (typeof Ext.getCmp('Answer_'+ form.listIDSfocus[num_elem]) == 'undefined') {
					Ext.getCmp('ufa_personBskRegistryWindow').combExp = '';
					return false;
				}
				setTimeout(function() {
					
					if (Ext.getCmp('Answer_'+ form.listIDSfocus[num_elem]).disabled) {//чтобы перепрыгивать через залоченые поля
						form.isActiveElement = num_elem+1;
						form.switchFieldOnTab(form.listIDSfocus[num_elem], false);
						return;
					}
				},500);
				Ext.getCmp('Answer_'+ form.listIDSfocus[num_elem]).focus(false,100);
				form.isUserClick = true;
				form.isActiveElement = num_elem+1;
				return;
			}
			else {
				//Пропускаем все залоченные ответы
				//console.log('Answer_'+form.listIDSfocus[num_elem],'disabled',Ext.getCmp('Answer_'+form.listIDSfocus[num_elem]).disabled);
				while (Ext.getCmp('Answer_'+form.listIDSfocus[num_elem]).disabled == true) {
					//console.log('цикл','Answer_'+form.listIDSfocus[num_elem])
					++num_elem;
					if (typeof Ext.getCmp('Answer_'+form.listIDSfocus[num_elem]) == 'undefined') { //Если следующий элемент не Answer, то запускаем метод по-новой
						form.isActiveElement = num_elem;
						form.switchFieldOnTab(form.listIDSfocus[num_elem-1], false);
						return;
					}
				}			
				
				Ext.getCmp('Answer_'+form.listIDSfocus[num_elem]).focus(false,100);	
				form.isActiveElement = num_elem+1;
			}			
		}

	},
    resizePanel: function() {
        var widthp = Ext.getBody().getWidth()-10;
        Ext.getCmp('mainPanel').setWidth(widthp);
        var heightp = Ext.getBody().getHeight();
        if (Ext.getCmp('infoPacient').collapsed) {
            //Ext.getCmp('mainPanel').setHeight(Ext.getBody().getHeight()-115);
            heightp = heightp-115;
        }
        else {
            //Ext.getCmp('mainPanel').setHeight(Ext.getBody().getHeight()-230);
            heightp = heightp-230;
        }
        Ext.getCmp('mainPanel').setHeight(heightp);      
    },
    isLastAnket : function(){
        return !(new Date() > new Date(Ext.getCmp('TextFieldDate').getValue()));
    },
    //Скрытие - отображение кнопок управления вкладкой "Сведения"
    hideShowButtons: function(tabid){
        var idsButtons = [
            'addBskDataButton', 
            'saveBskDataButton', 
            'editBskDataButton', 
            'deleteBskDataButton', 
            'printBskDataButton', 
            'prevButton', 
            'nextButton'
        ];

        if(tabid != 'infotab'){
            for(var k in idsButtons){
                if(typeof idsButtons[k] == 'string'){
                    Ext.getCmp(idsButtons[k]).hide();
                }
            }
            this.calendar.hide();
        }
        else{
            for(var k in idsButtons){
                if(typeof idsButtons[k] == 'string'){
                    Ext.getCmp(idsButtons[k]).show();
                }
            } 
            this.calendar.show();           
        }
        
        //Кнопка печати лекарственного лечения
        if(tabid != 'drug'){
            Ext.getCmp('printBskDrugsButton').hide();
        }
        else{
            Ext.getCmp('printBskDrugsButton').show();
        }
        
        //Блокировка добавления и редактирования
        if(Ext.getCmp('GridObjectsUser').getGrid().store.data.length > 0 && Ext.getCmp('GridObjectsUser').getGrid().store.data.items[0].json.Person_deadDT != null)
        {
          Ext.getCmp('addBskDataButton').setDisabled(true);
          Ext.getCmp('saveBskDataButton').setDisabled(true);
          Ext.getCmp('editBskDataButton').setDisabled(true);
          Ext.getCmp('EventsGrid').getAction('action_add').setDisabled(true);
          Ext.getCmp('EventsGrid').getAction('action_edit').setDisabled(true);
        }
        else
        {
            Ext.getCmp('addBskDataButton').setDisabled(false);
            Ext.getCmp('EventsGrid').getAction('action_edit').setDisabled(false);
        }
        
        
    },
    initComponent: function()
	{                
        var wnd = this;
        this.printAnkets = new Ext.Window({
            id: 'printAnkets',
            MorbusType_id : false,
			modal: true,
			title: 'Анкета "Скрининг" регистра БСК',
			height: 720,
			width: 930,
            cloaseAction: 'close',
            bodyStyle:'padding:10px;border:0px',
            html : 'ankets questions',
            autoScroll: true
            
        })    
        
        
 
        this.GridObjects = new sw.Promed.ViewFrame({
        	id: 'GridObjectsUser', 
            hideHeaders:true,
            disabled : true,
            enableColumnHide: true,
            bbar: [],
        	contextmenu: false,
            border: false,
        	height: Ext.getBody().getHeight()*0.897,
            object: 'GridObjectsUser',
        	dataUrl: '/?c=ufa_BSK_Register_User&m=getListObjectsCurrentUser',
        	autoLoadData: false,
            focusOnFirstLoad: false,
        	stringfields: [
        		{name: 'BSKObject_id', type: 'int', header: 'ID'},
                        {name: 'Person_deadDT', type: 'date', format: 'd.m.Y', header: lang['data_smerti'], width: 90, hidden: true},
                {name: 'MorbusType_id', type: 'int',hidden:true},
        		{name: 'MorbusType_Name', header: 'Наименование', /*id: 'autoexpand',*/ width: '250px', renderer: function(value){
        		  
                  if(value){
                      var value = value.toUpperCase();  
                  }
					if(!value) return '';

                  var t_val = value.split(/\(/);

                  var color = 'black';

                  if(value.match(/\(III\)/)){
                      color = 'red';
                  }
                  else if(value.match(/\(II\)/)){
                    color = 'orange';
                  }
                  else if(value.match(/\(I\)/)){
                    color = 'green';
                  }
                  var textGroupRisk = (typeof t_val[1] == 'undefined') ? '':  '<div style="padding:5px;font-size:12px; font-family:tahoma;  color:'+color+'!important;float:right;display:block">('+t_val[1]+'</div>';
                  //Скрининг
                  if(Ext.getCmp('ufa_personBskRegistryWindow').MorbusType_id == 84){
                     return '<div title="'+value+'" style="padding:5px;font-size:12px; float:left;display:block">' + t_val[0] + '</div>' + textGroupRisk;        
                  }
                  //Лёгочная гипертензия
                  else if(Ext.getCmp('ufa_personBskRegistryWindow').MorbusType_id == 88){
                     return '<div title="'+value+'" style="padding:5px;font-size:12px; float:left;display:block">' + t_val[0] + '</div>' + textGroupRisk;   
                  }
                  //Артериальная гипертензия
                  else if(Ext.getCmp('ufa_personBskRegistryWindow').MorbusType_id == 89){
                     return '<div title="'+value+'" style="padding:5px;font-size:12px; float:left;display:block">' + t_val[0] + '</div>' + textGroupRisk;   
                  }  
                  else{
                      //return '<div title="'+value+'" style="padding:5px;font-size:12px; color:'+color+'!important; font-family:tahoma; float:left;display:block">' + value  + '</div>';        
                      return '<div title="'+value+'" style="padding:5px;font-size:12px;  font-family:tahoma; float:left;display:block">' + value  + '</div>';        
        		  }
                }}
        	],
        	actions: [
        		{name:'action_add',hidden: true,text: 'Создать',disabled: true},
                {name:'action_edit', hidden: true,text: 'Изменить', disabled: true },
                {name:'action_delete', hidden: true,text: 'Удалить', disabled: true,},
        		{name:'action_view', hidden: true},
        		{name:'action_refresh', hidden: true},
        		{name:'action_print', hidden: true}
        	], 
        	onLoadData: function() {
                    //Блокировка добавления и редактирования
                    if(Ext.getCmp('GridObjectsUser').getGrid().store.data.length > 0 && Ext.getCmp('GridObjectsUser').getGrid().store.data.items[0].json.Person_deadDT != null)
                    {
                      Ext.getCmp('addBskObjectButton').setDisabled(true);
                      Ext.getCmp('addBskDataButton').setDisabled(true);
                      Ext.getCmp('saveBskDataButton').setDisabled(true);
                      
                    }
                    
        	},
            onDblClick: function() {
                return;
            },
            listeners : {
                'render' : function(){
                    this.getGrid().getTopToolbar().hidden = true;
                }
            },
            
            clickToRow: function(){
                    var form = Ext.getCmp('ufa_personBskRegistryWindow');
                    form.clickToPN +=1;
                
                    form.MorbusType_id = this.getGrid().getSelectionModel().getSelected().data.MorbusType_id;
                    
                    
                    //При клике на предмет набьлюдения - необходимо активировать таб с анкетными даннными
                    Ext.getCmp('tabpanelBSK').setActiveTab(Ext.getCmp('infotab'));  
                    			
                    //У каждого предмета наблюдения есть такой набор дат - по обследованиям (по наличию ранее сохранённых анкет)
                    form.Periods[form.MorbusType_id] = false;
                    
                    var BSKObject_id = this.getGrid().getSelectionModel().getSelected().data.BSKObject_id;

                    if(form.MorbusType_id.inlist([84])){ //88, 89, 19
                        Ext.getCmp('printBskDataButton').setVisible(true);  
                        Ext.getCmp('printBskDataButton').setDisabled(false);
                        //На данный момент работает только скрининг 
                        //Ext.getCmp('addBskDataButton').setDisabled(false);
                        Ext.getCmp('deleteBskDataButton').setDisabled(isAdmin);       
                        
                    }
                    else{
                        Ext.getCmp('printBskDataButton').setVisible(false);
    
                        //На данный момент работает только скрининг 
                        Ext.getCmp('information').removeAll();
                        
                        //Ext.getCmp('addBskDataButton').setDisabled(true);
                        Ext.getCmp('editBskDataButton').setDisabled(true);
                        Ext.getCmp('deleteBskDataButton').setDisabled(isAdmin);                 
                    }
                    
                    //Только для скрининга
                    if(form.MorbusType_id == 84){
                         form.setGroupRisk(form.personInfo.BSKRegistry_riskGroup);
                         //Печать анкеты только на скрининге
                         Ext.getCmp('printBskDataButton').setDisabled(false);
                    }   
                    //Лёгочная гипертензия
                    else if(form.MorbusType_id == 88){
                         form.setFunctionClass88(form.personInfo.BSKRegistry_functionClass);
                    }    
                    //Артериальная гипертензия
                    else if(form.MorbusType_id == 89){
                         form.setDegreeOfRisk89(form.personInfo.BSKRegistry_gegreeRisk);
                    }                        
                    else{
                        Ext.getCmp('printBskDataButton').setDisabled(true);
                    }
                      
                      
                    form.getRegistryDates();
                    form.getLastVizitRegistry(null);   
                    
                    Ext.getCmp('saveBskDataButton').setDisabled(true); 
                    if(Ext.getCmp('GridObjectsUser').getGrid().store.data.length > 0 && Ext.getCmp('GridObjectsUser').getGrid().store.data.items[0].json.Person_deadDT != null)
                       {
                           Ext.getCmp('addBskDataButton').setDisabled(true);
                           Ext.getCmp('saveBskDataButton').setDisabled(true);
                           Ext.getCmp('editBskDataButton').setDisabled(true);
                           Ext.getCmp('EventsGrid').getAction('action_add').setDisabled(true);
                           Ext.getCmp('EventsGrid').getAction('action_edit').setDisabled(true);
                       }
                      
            }
        
        }); 
        
         this.GridObjects.getGrid().on(
            'rowclick',
            function(grid, row){
                   Ext.getCmp('ufa_personBskRegistryWindow').isButtonAdd = false; 
                   Ext.getCmp('ufa_personBskRegistryWindow').listIDSfocus = [];
				   
                   var sm = this.getSelectionModel().getSelected().get('MorbusType_id'); 
                   //console.log('ROW', sm);
         
                   //Ext.getCmp('nextButton').setDisabled(false); 
                   //Ext.getCmp('prevButton').setDisabled(false);
                   Ext.getCmp('recommendations').setDisabled(false); 
                   Ext.getCmp('drug').setDisabled(false); 
                   Ext.getCmp('events').setDisabled(false); 
                   Ext.getCmp('nextButton').setDisabled(true);
           
                  if(sm == 19)
                  {
                    Ext.getCmp('addBskDataButton').setDisabled(sm == 19);  
                  }
                  else
                  {
                     //Блокировка добавления и редактирования
                    if(Ext.getCmp('GridObjectsUser').getGrid().store.data.length > 0 && Ext.getCmp('GridObjectsUser').getGrid().store.data.items[0].json.Person_deadDT != null)
                    {
                         Ext.getCmp('addBskDataButton').setDisabled(true);
                    }
                    else
                    {
                        Ext.getCmp('addBskDataButton').setDisabled(false);
                    }
                   
                  }
                   
                   
                   //Редактирование хроник  
                   Ext.getCmp('editBskDataButton').setDisabled(Ext.getCmp('ufa_personBskRegistryWindow').editableForm); 

                   //В ОКС нельзя добавлять анкеты, только через карту вызова 
                   if(sm == 19){
                      Ext.getCmp('saveBskDataButton').setDisabled(true);
                   } 

                   if( Ext.getCmp('ufa_personBskRegistryWindow').clickToPN == 0){					   
                        Ext.getCmp('GridObjectsUser').clickToRow();
                   }
                   else{
                        sw.swMsg.show({
                            id: 'Question',
                			buttons: Ext.Msg.YESNO,
                			fn: function(buttonId, text, obj) {
                				if ( buttonId != 'yes' ) {
                                    return false;
                				}
                                else{
                                    Ext.getCmp('GridObjectsUser').clickToRow();
                                }
                			}.createDelegate(this),
                			icon: Ext.MessageBox.QUESTION,
                			msg: 'Все не сохранённые данные будут утеряны, продолжить?',
                			title: 'Вопрос'
                		}); 
                        
                        return Ext.getCmp('ufa_personBskRegistryWindow').Question;
                   } 
                  
            }
         );

    

        this.calendar = new sw.Promed.SwDateField(
        {
        	fieldLabel: 'Дата вызова',
        	id: 'calday',
            disabled: true,
            enableKeyEvents: true,
        	plugins: [
        		new Ext.ux.InputTextMask('99.99.9999', false)
        	],
        	xtype: 'swdatefield',
        	format: 'd.m.Y',
        	datetime: null,
        	listeners: {
        		'keydown': function (inp, e) {
        			if (e.getKey() == Ext.EventObject.ENTER) {
        				e.stopEvent();
                         
                    	//this.loadHomeVisits(Ext.util.Format.date(inp.getValue(), 'd.m.Y'));
        			}
        		}.createDelegate(this),
        		'select': function () {
        			
                    //this.loadHomeVisits(this.calendar.value);
        		}.createDelegate(this)
        	},
        	value: new Date(),
            getPeriod : function(direction){
                 var form = Ext.getCmp('ufa_personBskRegistryWindow');
                 Ext.getCmp('addBskDataButton').setDisabled(false);
                 form.robot = true;
                 
                 var Periods = form.Periods[form.MorbusType_id];
                 var activeDate = new Date(this.getValue()).format('Y-m-d H:i');
                 var size = Periods.length;

                    if(direction == '<'){
                        
                        Ext.getCmp('nextButton').setDisabled(false);
                        
                        if(form.Reverse === true){
                           var Periods = Periods.reverse(); 
                           form.Reverse = false;
                        }    

                        for(var k in Periods){
                            
                            var t_period = Periods[k].BSKRegistry_setDate;
                            
                            if(typeof t_period == 'undefined'){
                                Ext.getCmp('prevButton').setDisabled(true);
                                return;
                            }

                            if(t_period < this.datetime){
                                    wnd.getLoadMask('Получение данных по пациенту').show();
                                    form.BSKRegistry_id = Periods[k].BSKRegistry_id;
                                    form.getLastVizitRegistry(Periods[k].BSKRegistry_id);
                                    if( size-parseInt(k) == 1){
                                        Ext.getCmp('prevButton').setDisabled(true);
                                    } 
                                    
                                    Ext.getCmp('nextButton').setDisabled(!parseInt(k)>0);
                                this.datetime = t_period;
                                this.setValue(new Date(t_period));
                                break;
                            }
                            
                        }
                        return;
                    }
                    else if(direction == '>'){
                        Ext.getCmp('prevButton').setDisabled(false);

                        if(form.Reverse === false){
                           var Periods = Periods.reverse(); 
                           form.Reverse = true;
                        }
                        for(var k in Periods){ 

                            if(typeof Periods[k] == 'object'){
                                var t_period = Periods[k].BSKRegistry_setDate;

                                if(t_period > this.datetime){   
                                    wnd.getLoadMask('Получение данных по пациенту').show();
                                    form.BSKRegistry_id = Periods[k].BSKRegistry_id;
                                    form.getLastVizitRegistry(Periods[k].BSKRegistry_id);

                                    if( k == (size-1)){
                                       Ext.getCmp('nextButton').setDisabled(true);
                                    } 

                                    this.datetime = t_period;
                                    this.setValue(new Date(t_period));
                                    break;
                                }
                            }
                               
                        } 
                        return;      
                    }
                //}
                
                return;
           
                
                
            }
        });	
        
        //Отображение событий по пользователю
        this.EventsGrid = new sw.Promed.ViewFrame({
        	id: 'EventsGrid', 
            //hideHeaders:false,
            //enableColumnHide: false,
            //bbar: [],
        	//contextmenu: false,
            border: false,
            //height: 900,
        	height: Ext.getBody().getHeight()*0.897,
            object: 'EventsGrid',
        	dataUrl: '/?c=ufa_BSK_Register_User&m=getListEvents',
        	autoLoadData: false,
            focusOnFirstLoad: false,
        	stringfields: [
        		{name: 'Events_id', type: 'int', header: 'ID'},
                {name: 'Events_setDate', header: 'Дата', type: 'string', width:120, renderer : Ext.util.Format.dateRenderer('d.m.Y')},
        		{name: 'Events_Code', header: 'Код', type: 'string', width:120},
                {name: 'Events_Name', header: 'Наименование', id: 'autoexpand'},
                {name: 'Events_Type', header: 'Тип', type: 'string', width:50},
                {name: 'Events_Description', header: 'Примечание', width:450},
                {name: 'Events_Edit', type: 'int', hidden : true}
        	],
        	actions: [
        		{name:'action_add',hidden: false,text: 'Создать',disabled: false, handler : function(){
        		       getWnd('ufa_BSKEventsWindow').show({params:{Person_id: Ext.getCmp('ufa_personBskRegistryWindow').Person_id }});     
        		}},
                {name:'action_edit', hidden: false, text: 'Изменить', disabled: true , handler : function(){ Ext.getCmp('EventsGrid').editData(); } },
                {name:'action_delete', hidden: false, text: 'Удалить', disabled: true, handler : function(){  Ext.getCmp('EventsGrid').deleteData();}},
        		{name:'action_view', hidden: true},
        		{name:'action_refresh', hidden: false},
        		{name:'action_print', hidden: false}
        	], 
        	onLoadData: function() {
                       if(Ext.getCmp('GridObjectsUser').getGrid().store.data.length > 0 && Ext.getCmp('GridObjectsUser').getGrid().store.data.items[0].json.Person_deadDT != null)
                       {
                           
                           Ext.getCmp('EventsGrid').getAction('action_add').setDisabled(true);
                           Ext.getCmp('EventsGrid').getAction('action_edit').setDisabled(true);
                       }
        	},
            deleteData : function(){
                var EventsGrid = Ext.getCmp('EventsGrid').getGrid();
                var rec =  EventsGrid.getSelectionModel().getSelected(); 
                
                //Если запись НЕ БЫЛА добавлена в ручную - то с ней ничего не сделать. 
                if(rec.get('EventEdit')){
                    return;
                }
                
				sw.swMsg.show({
					buttons: Ext.Msg.YESNO,
					msg: 'Удалить запись?',
					title: 'Удаление записи',
					fn: function( buttonId ) {
						if ( buttonId != 'yes' ) {
							return false;
						}

                        Ext.Ajax.request({
            		 		url: '/?c=ufa_BSK_Register_User&m=deleteEvent',
            				params: {
                                BSKEvents_id : rec.get('Events_id')                                
            				},
            				callback: function(options, success, response) {
        
            				     if(success === true){
            				         Ext.getCmp('ufa_BSKEventsEditWindow').refresh();  
                                     //Ext.getCmp('ufa_BSKEventsEditWindow').showMsg('Событие успешно удалено!');
                                     
                                     Ext.getCmp('EventsGrid').getGrid().getStore().load({
                                        MorbusType_id : Ext.getCmp('ufa_personBskRegistryWindow').MorbusType_id,
                                        Person_id :  Ext.getCmp('ufa_personBskRegistryWindow').Person_id 
                                     });
                                     Ext.getCmp('events').doLayout();                                             
            				     }
                                 else{
                                    Ext.getCmp('ufa_BSKEventsEditWindow').showMsg('Произошла ошибка при попытки удаления события!');
                                 }
                            }
                        });    

					}.createDelegate(this)
				});                
                             
            },
            editData : function(){

                var EventsGrid = Ext.getCmp('EventsGrid').getGrid();
                var rec =  EventsGrid.getSelectionModel().getSelected(); 
                
                //Если запись НЕ БЫЛА добавлена в ручную - то с ней ничего не сделать. 
                if(rec.get('EventEdit')){
                    return;
                }
                
                getWnd('ufa_BSKEventsEditWindow').show({params : {
                    Events_Type : rec.get('Events_Type'),
                    Events_setDate: rec.get('Events_setDate'),
                    Events_Name : rec.get('Events_Name'),
                    Events_Code : rec.get('Events_Code'),
                    EventDescription : rec.get('Events_Description'),
                    Events_id : rec.get('Events_id')                    
                }}); 

            },
            onDblClick: function() {
                this.editData();      
                return;
            },
            listeners : {
                'render' : function(){
                    //this.getGrid().getTopToolbar().hidden = true;
                }
            },
            clickToRow: function(){

            }
        
        }); 
        //Редактирование событий, только для тех - что добавлены руками в таблицу r2.BSKEvents (Определение по полю Events_Edit= 1))
        this.EventsGrid.getGrid().on(
            'rowclick',
            function(grid, rowIndex){
                var rec = this.getStore().getAt(rowIndex);
                var EventsGrid = Ext.getCmp('EventsGrid');
                var disabledActions = !rec.get('Events_Edit');
                
                EventsGrid.getAction('action_edit').setDisabled(disabledActions);
                EventsGrid.getAction('action_delete').setDisabled(disabledActions);       
            }
        );        
        
        var form = this;

    	Ext.apply(this,
		{
            items : [
                {
                    xtype : 'panel',
                    collapsible: false,
                    collapsed: true,
                    id : 'infoPacient',
                    title : 'Ожидание данных...',
                    style : 'cursor:pointer',
                    layout : 'column',
                    frame: true,
                    items : [ 
                    
                      {
                        xtype: 'panel',
                        border: false,
        				defaults: {
        					style: 'background-color:#99BBE8;',
        					xtype: 'button'
        				},                        
                        align : 'right',
                        style: 'float:right!important; margin-top:-100px; width:100%; padding-left:90%',
        				items: [{
        					disabled: false,
                            width:'130px',
        					handler: function() {
        						Ext.getCmp('ufa_personBskRegistryWindow').panelButtonClick(1);
        					},
        					text: BTN_PERSCARD,
        					iconCls: 'pers-card16',
        					tooltip: BTN_PERSCARD_TIP
        				}, {
        					disabled: false,
                            width:'130px',
        					handler: function() {
        						Ext.getCmp('ufa_personBskRegistryWindow').panelButtonClick(2);
        					},
        					text: BTN_PERSEDIT,
        					iconCls: 'edit16',
        					tooltip: BTN_PERSEDIT_TIP
        				}, {
        					disabled: false,
                            width:'130px',
        					handler: function() {
        						Ext.getCmp('ufa_personBskRegistryWindow').panelButtonClick(3);
        					},
        					text: BTN_PERSCUREHIST,
        					iconCls: 'pers-curehist16',
        					tooltip: BTN_PERSCUREHIST_TIP
        				}, {
        					disabled: false,
                            width:'130px',
        					handler: function() {
        						Ext.getCmp('ufa_personBskRegistryWindow').panelButtonClick(4);
        					},
        					text: BTN_PERSPRIV,
        					iconCls: 'pers-priv16',
        					tooltip: BTN_PERSPRIV_TIP
        				}, {
        					disabled: false,
                            width:'130px',
        					handler: function() {
        						Ext.getCmp('ufa_personBskRegistryWindow').panelButtonClick(5);
        					},
        					text: BTN_PERSDISP,
        					iconCls: 'pers-disp16',
        					tooltip: BTN_PERSDISP_TIP
        				}]                        
                      }
                    ],
                     listeners: {
                        
                        'render': function(panel) {
                                panel.header.on('click', function() {
                                  if (panel.collapsed) {
                                    panel.expand();
                                    //Ext.getCmp('tabpanelBSK').setHeight(Ext.getCmp('tabpanelBSK').getEl().dom.clientHeight - 114);
                                  }
                                  else {
                                    panel.collapse();
                                    //Ext.getCmp('tabpanelBSK').setHeight(Ext.getCmp('tabpanelBSK').getEl().dom.clientHeight + 114);
                                  }
                                });
                        },
                        'expand':function(p) {
                                  Ext.getCmp('ufa_personBskRegistryWindow').resizePanel();
                                },
                        'collapse' :function(p) {
                                   Ext.getCmp('ufa_personBskRegistryWindow').resizePanel();
                                }
                       
                     }                      
                },
                {                      
                    xtype: 'panel',
                    //layout: 'column',
                    layout:'border',
                    //width : Ext.getBody().getWidth(),
                    //height : Ext.getBody().getHeight(),
                    id: 'mainPanel',
                    items : [
                        {
                            xtype : 'panel',
                            collapsible: true,
                            title: 'Предметы наблюдения',
                            width : 253,                             
                            region: 'west',
                            bodyBorder: false,
                            id: 'leftPanelmenu',
                            border: false,
                            items : [
                            {
                                xtype : 'panel',
                                //height: Ext.getBody().getViewSize().height-115,
                                
                                tbar: [
                                       {
                                            xtype: 'button',
                                            id: 'addBskObjectButton',
                                            text: 'Добавить',
                                            //disabled : true,
                                            iconCls: 'add16',
                                            handler: function(){
												Ext.getCmp('ufa_personBskRegistryWindow').clickToRow = true;
												var massMorbusType_id = Ext.getCmp('GridObjectsUser').getGrid().getStore().data.items;
												var listMorbusType_id = [];
												for (var k in massMorbusType_id) {
													if (typeof massMorbusType_id[k] == 'object') {
														listMorbusType_id.push(massMorbusType_id[k].data.MorbusType_id);
													}									

												}
												var params = {
													listMorbusType_id:listMorbusType_id
												};
												getWnd('swBSKSelectWindow').show(params);
                                            }
                                       }
                                ],
                                items: [ Ext.getCmp('ufa_personBskRegistryWindow').GridObjects]
                            }],
                            listeners:{
                                /*'render': function() {
                                    Ext.getCmp('ufa_personBskRegistryWindow').resizePanel();
                                    Ext.getCmp('leftPanelmenu').setWidth(253);
                                }*/
                            }
                        },
                        {
                            xtype : 'tabpanel',
                            id: 'tabpanelBSK',
                            plain: false,
                            border: false,
                            bodyBorder : false,
                            autoScroll : false,
                            //height: Ext.getBody().getHeight()-120,
                            activeTab: 0,                         
                            //columnWidth : 1, 
                            region: 'center',
                            tbar: [
                               {
                                    xtype: 'button',
                                    text: 'Добавить',
                                    id: 'addBskDataButton',
                                    iconCls: 'add16',
                                    disabled: true,
                                    handler: function(){
										Ext.getCmp('ufa_personBskRegistryWindow').isButtonAdd = true; 
                                        var form = Ext.getCmp('ufa_personBskRegistryWindow');
                                        form.PrefixQuest = 0;
                                        Ext.getCmp('addBskDataButton').setDisabled(true);
                                        //Ext.getCmp('ufa_personBskRegistryWindow').pmuser_id = getGlobalOptions().pmuser_id;

                                        form.newAnkets = true;
                                        
                                        form.robot = false;
                                        form.editableForm = false;
                                        //24122014
                                        //Ext.getCmp('ufa_personBskRegistryWindow').BSKRegistry_id = false;
                                        Ext.getCmp('calday').setValue(new Date());
                                        Ext.getCmp('calday').datetime = new Date().format('Y-m-d H:i');
                                        //Ext.getCmp('TextFieldDate').setValue(new Date())
                                        form.addRegistryData();
                                        Ext.getCmp('saveBskDataButton').setDisabled(false);
                                        Ext.getCmp('TextFieldDate').setDisabled(false);
                                        
                                        Ext.getCmp('prevButton').setDisabled(true);
                                        Ext.getCmp('nextButton').setDisabled(true);
                                    }
                               }, 
                               {
                                    xtype: 'button',
                                    text: 'Сохранить',
                                    id: 'saveBskDataButton',
                                    iconCls: 'save16',
                                    disabled: true,
                                    handler: function(){
                                        Ext.getCmp('addBskDataButton').setDisabled(false);
                                        Ext.getCmp('saveBskDataButton').setDisabled(true);
                                        var dateRegister = Ext.getCmp('TextFieldDate').getValue();
                                        var now = new Date();
                                        var timeDiff = Math.abs(dateRegister.getTime() - now.getTime());
                                        var diffDays = Math.ceil(timeDiff / (1000 * 3600 * 24))-1; 
                                        
                                        if(diffDays > 30){
                                            form.showMsg('Дата проведения анкетирования не может быть ранее 30 дней от текущей даты. Пожалуйста, проверьте указанную дату анкетирования.');  
                                            return;                                           
                                        }
                                        
                                        var activeTab = Ext.getCmp('tabpanelBSK').getActiveTab();
                                        var activeTabIndex = Ext.getCmp('tabpanelBSK').items.findIndex('id', activeTab.id);
                                        
                                      
                                        
                                        switch(activeTabIndex){
                                            //Сведения
                                            case 0:
                                                    if(Ext.getCmp('ufa_personBskRegistryWindow').Periods[Ext.getCmp('ufa_personBskRegistryWindow').MorbusType_id].length <=1){
                                                         Ext.getCmp('prevButton').setDisabled(true);                    
                                                    }
                                                    else{
                                                         Ext.getCmp('prevButton').setDisabled(false);     
                                                    }  
                                                                            
                                                    Ext.getCmp('ufa_personBskRegistryWindow').findRegisterData();
                                            break;
                                        }
                                    }
                               },                                
                               {
                                    xtype: 'button',
                                    text: 'Изменить',
                                    disabled: true,
                                    hidden:true,
                                    id: 'editBskDataButton',
                                    iconCls: 'edit16',
                                    handler: function(){
                                        
                                    }, 
                               }, 
                               { 
                                    xtype: 'button',
                                    text: 'Удалить',
                                    disabled: true,
                                    id: 'deleteBskDataButton',
                                    iconCls: 'delete16',
                                    handler: function(){
                                        
                                    },                                                                 
                               },
                               {
                                    xtype: 'button',
                                    text: 'Печать анкеты',
                                    disabled: true,
                                    id: 'printBskDataButton',
                                    iconCls: 'print16',
                                    hidden: true,
                                    handler: function(){
                                            
                                            //На рабочем или тестовом
                                            var object_value = null;
                                            //var url = ((getGlobalOptions().birtpath != '/birt-viewer/') ? getGlobalOptions().birtpath : 'http://192.168.200.58:91:91/birt-viewer')  +'/run?__report=Report/';
                                            //var url = 'http://192.168.200.58:91:91/birt-viewer/run?__report=Report/';

                                            switch(Ext.getCmp('ufa_personBskRegistryWindow').MorbusType_id){
                                                case 84 : var report = '/AnketBSKScreening.rptdesign'; break;
                                                default : var report = '/AnketBSKScreening.rptdesign';
                                            }                                            

                                            var paramStr = report +'&__format=pdf';
                                            
                                            var url = ((getGlobalOptions().birtpath)?getGlobalOptions().birtpath:'')+'run?__report=report';	
                                            
                                            console.log(home);
                                            
                                            var home =  window.location.host == '127.0.0.1:81' || window.location.host == '192.168.200.58:81' ? 'http://192.168.200.58:91' : '';          
                                            window.open(home+url+paramStr, '_blank');                                                                                 
                                    }, 
                               },  
                               {
                                    xtype: 'button',
                                    text: 'Печать',
                                    disabled: false,
                                    id: 'printBskDrugsButton',
                                    iconCls: 'print16',
                                    hidden: true,
                                    handler: function(){
                                            var id_salt = Math.random();
                                            var win_id = 'print_registryerror' + Math.floor(id_salt * 10000);
                                            window.open('/?c=ufa_BSK_Register_User&m=getDrugs&Person_id=' + Ext.getCmp('ufa_personBskRegistryWindow').Person_id, win_id);
                                            /*
                                            //На рабочем или тестовом
                                            var object_value = null;
                                            //var url = ((getGlobalOptions().birtpath != '/birt-viewer/') ? getGlobalOptions().birtpath : 'http://192.168.200.58:91:91/birt-viewer')  +'/run?__report=Report/';
                                            //var url = 'http://192.168.200.58:91:91/birt-viewer/run?__report=Report/';

                                            switch(Ext.getCmp('ufa_personBskRegistryWindow').MorbusType_id){
                                                case 84 : var report = '/AnketBSKScreening.rptdesign'; break;
                                                default : var report = '/AnketBSKScreening.rptdesign';
                                            }                                            

                                            var paramStr = report +'&__format=pdf';
                                            
                                            var url = ((getGlobalOptions().birtpath)?getGlobalOptions().birtpath:'')+'/run?__report=report';	
                                            var home =  window.location.host == '127.0.0.1:81' ? 'http://192.168.200.58:91:91' : '';           
                                            window.open(home+url+paramStr, '_blank');         
                                            */                                                                        
                                    }, 
                               },                                                              
                                {
                                  xtype: 'tbfill'  
                                },
                				{
                					text: 'Предыдущий',
                                    disabled: true,
                                    id: 'prevButton',
                					xtype: 'button',
                					iconCls: 'arrow-previous16',
                					handler: function () {
                					    
                						//alert('Проявите терпение');
                                        Ext.getCmp('tabpanelBSK').activate('infotab');
                                        Ext.getCmp('ufa_personBskRegistryWindow').calendar.getPeriod('<');

                					}.createDelegate(this)
                				},
                				Ext.getCmp('ufa_personBskRegistryWindow').calendar,
                				{
                					text: 'Следующий',
                                    id: 'nextButton',
                                    disabled: true,
                					xtype: 'button',
                					iconCls: 'arrow-next16',
                					handler: function () {
                					   
                						Ext.getCmp('tabpanelBSK').activate('infotab');
                                        Ext.getCmp('ufa_personBskRegistryWindow').calendar.getPeriod('>');

                					}.createDelegate(this)
                				}                                  
                            ],
                            items : [
                                {
                                  title: 'Сведения',
                                  xtype: 'panel',
                                  id   : 'infotab',
                                  autoScroll : true,
                                  items : [
                                     {
                                      id   : 'information',
                                      style: 'background-color:#E3E3E3!important' ,                               
                                     }
                                  ],
                                  listeners : {
                                    'activate' : function(p){
                                        Ext.getCmp('ufa_personBskRegistryWindow').hideShowButtons(p.id);
                                    }
                                  }
                                },
                                {
                                  title: 'События',
                                  id   : 'events',
                                  disabled : true,
                                  items : [
                                        {
                                            xtype: 'panel',
                                            title: '',
                                            frame: false,
                                            id: 'eventsSubPanel',
                                            //layout : 'column',
                                            items : [

                                            ]
                                        }
                                  ],
                                  listeners : {
                                      'activate' : function(p){
                                            Ext.getCmp('ufa_personBskRegistryWindow').hideShowButtons(p.id);
                                            //Ext.getCmp('eventsSubPanel').removeAll();
                                            Ext.getCmp('eventsSubPanel').add(Ext.getCmp('EventsGrid'));
                                            //console.log('!!!!!!!', Ext.getCmp('EventsGrid').getGrid().getStore())
                                            
                                            Ext.getCmp('EventsGrid').getGrid().getStore().baseParams = {
                                                MorbusType_id : Ext.getCmp('ufa_personBskRegistryWindow').MorbusType_id,
                                                Person_id :  Ext.getCmp('ufa_personBskRegistryWindow').Person_id                                                 
                                            }
                                            
                                            Ext.getCmp('EventsGrid').getGrid().getStore().load({
                                                MorbusType_id : Ext.getCmp('ufa_personBskRegistryWindow').MorbusType_id,
                                                Person_id :  Ext.getCmp('ufa_personBskRegistryWindow').Person_id 
                                            });
                                            Ext.getCmp('events').doLayout();
                                      }                                      
                                  }  
                                }, 
                                
                                /**
                                {
                                  title: 'Консультации',
                                  id   : 'consultation',
                                  hidden: true,
                                  disabled : true,
                                  html: ''  
                                }, 
                                */
                                {
                                  title: 'Рекомендации',
                                  id   : 'recommendations',
                                  disabled : true,
                                  //html: '',
                                  items : [
                                        {
                                            xtype: 'panel',
                                            title: '',
                                            frame: false,
                                            id: 'recomendationSubPanel',
                                            layout : 'column',
                                            items : [
                                                /*
                                                Ext.getCmp('ufa_personBskRegistryWindow').RecomendationsDatesTreePanel,
                                                {
                                                    xtype: 'panel',
                                                    columnWidth : 90,
                                                    items : []
                                                }
                                                */
                                            ]
                                        }
                                        
                                  ],
                                  listeners : {
                                      'activate' : function(p){
                                           var form = Ext.getCmp('ufa_personBskRegistryWindow');
                                        
                                           Ext.getCmp('ufa_personBskRegistryWindow').hideShowButtons(p.id) 
                                        
                                           Ext.getCmp('recomendationSubPanel').removeAll();
                                           //if(typeof  Ext.getCmp('listRecomendationsPanel') != 'undefined'){
                                           //    Ext.getCmp('listRecomendationsPanel').removeAll();
                                           //}

                                     	   form.RecomendationsDatesTreePanel = new Ext.tree.TreePanel({
                                    			region: 'west',
                                                width: 120,
                                                autoScroll : true,
                                    			id: 'RecomendationsDatesTreePanel',
                                    			loaded: false,
                                    			border: false,
                                                height : Ext.getBody().getHeight() - 80,
                                    			root: {
                                    				nodeType: 'async',
                                    				text: 'Даты проведения',
                                    				id: 'root',
                                    				expanded: false,
                                    
                                    			},
                                    			loader: new Ext.tree.TreeLoader({
                                    				dataUrl: '/?c=ufa_Bsk_Register_User&m=getTreeDatesRecomendations'
                                    			}),
                                    			rootVisible: false,
                                    			lastSelectedId: 0,
                                    			listeners: {
                                    			   'beforeload' : function(){
                                    			         form.getEl().mask('Получение рекомендаций').show();
                                    			   },
                                    			   'load' : function(){
                                    			         form.getEl().mask().hide();
                                    			   }, 
                                                    'click' : function(node, e ) {
                                                           //console.log(form.personInfo);   
                                                           //console.log('new Date(node.text)', node.text);  
                                                           var rsd = node.text.split(".");
                                                           // console.log('>>', rsd[2]+'-'+rsd[1]+'-'+rsd[0]);   
                                                           
                                                           
                                                           var params = {
                                                                BSKRegistry_setDate : rsd[0],
                                                                MorbusType_id : form.MorbusType_id,
                                                                Person_id : form.personInfo.Person_id,
                                                                Sex_id : form.personInfo.Sex_id,
                                                                BSKObservRecomendationType_id : null
                                                           }

                                                           var DoctorRecomendations = new sw.Promed.ViewFrame({
                                                                title: 'Рекомендации для врача',
                                                            	id: 'DoctorRecomendations', 
                                                                toolbar : true,
                                                                hideHeaders:true,
                                                                enableColumnHide: true,
                                                                //bbar: [],
                                                                cls: 'txtwrap',
                                                            	contextmenu: false,
                                                                border: false,
                                                                autoScroll : true,
                                                                autoload: false,
                                                                //height: 900,
                                                            	height: (Ext.getBody().getHeight()*0.897) / 2.2,
                                                                object: 'DoctorRecomendations',
                                                            	dataUrl: '/?c=ufa_BSK_Register_User&m=getRecomendationByDate',
                                                            	autoLoadData: false,
                                                                focusOnFirstLoad: false,
                                                            	stringfields: [
                                                            		{name: 'BSKObservRecomendation_id', type: 'int', header: 'ID', hidden : true},
                                                                    {name: 'BSKObservRecomendation_text', type: 'string', header: 'Текст рекомендации', id: 'autoexpand'},
                                                                ],
                                                            	actions: [
                                                            		{name:'action_add',hidden: true,text: 'Создать',disabled: true},
                                                                    {name:'action_edit', hidden: true,text: 'Изменить', disabled: true },
                                                                    {name:'action_delete', hidden: true,text: 'Удалить', disabled: true,},
                                                            		{name:'action_view', hidden: true},
                                                            		{name:'action_refresh', hidden: true},
                                                            		{name:'action_print', hidden: false, handler : function(){
																		//На рабочем или тестовом
																		var object_value = null;

																		//var url = ((getGlobalOptions().birtpath != '/birt-viewer/') ? getGlobalOptions().birtpath : 'http://192.168.200.58:91:91/birt-viewer')  
																		//+'/run?__report=Report/';
																		//var url = 'http://192.168.200.58:91:91/birt-viewer/run?__report=Report/';     

																		var url = ((getGlobalOptions().birtpath)?getGlobalOptions().birtpath:'')+'run?__report=report';	

																		//var url = 'http://192.168.200.58:91:91/birt-viewer/run?__report=Report/';
																		var paramStr = '/getRecomendationByDate.rptdesign'
																					 +'&Person_id='+form.personInfo.Person_id
																					 +'&Morbus_id='+form.MorbusType_id
																					 +'&Sex_id='+form.personInfo.Sex_id
																					 +'&BSKObservRecomendationType_id=1'
																					 +'&BSKRegistry_setDate='+rsd[0]
																					 +'&__format=pdf';

																		 var home =  (window.location.host == '127.0.0.1:81' || window.location.host == '192.168.200.58:81') ? 'http://192.168.200.58:91' : '';                  
																		 window.open(home+url+paramStr, '_blank');																			
																	}}
                                                            	] ,
                                                                listeners: {
                                                                  
                                                                   'render': function(panel) {
                                                                           /* 
                                                                          console.log(panel);
                                                                          panel.header.on('click', function() {
                                                                             if (panel.collapsed) {
                                                                                panel.expand();
                                                                             }
                                                                             else {
                                                                                panel.collapse();
                                                                             }
                                                                          });
                                                                          */  
                                                                    }
                                                                   
                                                                   
                                                                }                                                                                                                                   
                                                           });                                               
                                                           Ext.getCmp('listRecomendationsPanel').add(DoctorRecomendations);
                                                           Ext.getCmp('listRecomendationsPanel').doLayout();
                                                           
                                                           params.BSKObservRecomendationType_id = 1;
                                                           
                                                           DoctorRecomendations.getGrid().getStore().load({
                                                                params: params
                                                           });
                                                          
                                                           var PacientRecomendations = new sw.Promed.ViewFrame({
                                                                title: 'Рекомендации для пациента',
                                                            	id: 'PacientRecomendations', 
                                                                toolbar : true,
                                                                hideHeaders:true,
                                                                enableColumnHide: true,
                                                                cls: 'txtwrap',                                                               
                                                                layout : 'form',
                                                            	contextmenu: false,
                                                                border: false,
                                                                autoload: false,
                                                                //height: 900,
                                                            	height: (Ext.getBody().getHeight()*0.897)/2.27,
                                                                object: 'DoctorRecomendations',
                                                            	dataUrl: '/?c=ufa_BSK_Register_User&m=getRecomendationByDate',
                                                            	autoLoadData: false,
                                                                focusOnFirstLoad: false,
                                                            	stringfields: [
                                                            		{name: 'BSKObservRecomendation_id', type: 'int', header: 'ID', hidden : true},
                                                                    {name: 'BSKObservRecomendation_text', type: 'string', header: 'Текст рекомендации', id: 'autoexpand'},
                                                                ],
                                                            	actions: [
                                                            		{name:'action_add',hidden: true,text: 'Создать',disabled: true},
                                                                    {name:'action_edit', hidden: true,text: 'Изменить', disabled: true },
                                                                    {name:'action_delete', hidden: true,text: 'Удалить', disabled: true,},
                                                            		{name:'action_view', hidden: true},
                                                            		{name:'action_refresh', hidden: true},
                                                            		{name:'action_print', hidden: false, handler : function(){
																		//console.log(getGlobalOptions().birtpath); // /birt-viewer/
																		//На рабочем или тестовом


																		var object_value = null;
																		//var url = ((getGlobalOptions().birtpath != '/birt-viewer/') ? getGlobalOptions().birtpath : 'http://192.168.200.58:91:91/birt-viewer')  
																		//+'/run?__report=Report/';
																		//var url = 'http://192.168.200.58:91:91/birt-viewer/run?__report=Report/';    
																		var url = ((getGlobalOptions().birtpath)?getGlobalOptions().birtpath:'')+'run?__report=report';
																		var paramStr = '/getRecomendationByDate.rptdesign'
																					 +'&Person_id='+form.personInfo.Person_id
																					 +'&Morbus_id='+form.MorbusType_id
																					 +'&Sex_id='+form.personInfo.Sex_id
																					 +'&BSKObservRecomendationType_id=2'
																					 +'&BSKRegistry_setDate='+rsd[0]
																					 +'&__format=pdf';

																		var home =  window.location.host == '127.0.0.1:81' || window.location.host == '192.168.200.58:81' ? 'http://192.168.200.58:91' : '';    
																		window.open(home+url+paramStr, '_blank');            
																	}}
                                                            	]                                                                
                                                           });   

                                                           Ext.getCmp('listRecomendationsPanel').add(PacientRecomendations);
                                                           Ext.getCmp('listRecomendationsPanel').doLayout();
                                                           
                                                           params.BSKObservRecomendationType_id = 2;
                                                           
                                                           PacientRecomendations.getGrid().getStore().load({
                                                                params: params
                                                           });                                                          
                                                          
                                                           
                                                    }               
                                                }
                                           });                                              
                                           
                                           Ext.getCmp('recomendationSubPanel').add(form.RecomendationsDatesTreePanel);
                                           
                                           var listRecomendationsPanel =                                                 
                                                {
                                                    xtype: 'panel',
                                                    id: 'listRecomendationsPanel',
                                                    items : [],
                                                    columnWidth:1
                                                }
                                           
                                           Ext.getCmp('recomendationSubPanel').add(listRecomendationsPanel);
                                           
                                           var tree =  Ext.getCmp('ufa_personBskRegistryWindow').RecomendationsDatesTreePanel;
                                           var root = tree.getRootNode();
                                           
                                           var MorbusType_id = form.GridObjects.getGrid().getSelectionModel().getSelected().data.MorbusType_id;
                                            
                                           tree.getLoader().baseParams = {
                                                MorbusType_id : MorbusType_id,
                                                Person_id : form.personInfo.Person_id
                                           }

                                           //tree.getLoader().load(root); 
                                           this.doLayout(); 
                                      }
                                  }                                    
                                }, 
                                {
                                  title: 'Лекарственное лечение',
                                  id   : 'drug',
                                  disabled : true,
                                  style: 'padding:10px',
                                  autoScroll : true,
                                  html: '' ,
                                  listeners : {
                                    'activate' : function(p){
                                        Ext.getCmp('ufa_personBskRegistryWindow').hideShowButtons(p.id); 
                                        
                                        Ext.Ajax.request({
                                        	url: '/?c=ufa_Bsk_Register_User&m=getDrugs',
                                        	params: {
                                                Person_id   : Ext.getCmp('ufa_personBskRegistryWindow').personInfo.Person_id
                                        	},
                                        	callback: function(options, success, response) {
                                                 
                                                if (success === true) {   
                                                    
                                                    var responseText = Ext.util.JSON.decode(response.responseText);
                                                    
                                                    Ext.getCmp('drug').body.update(responseText, true);
                                                }
                                            }
                                        });                                           
                                    }
                                  } 
                                }, 
                                {
                                  title: 'Сравнение',
                                  id   : 'compare',
                                  disabled :  true,
                                  style: 'padding:10px',
                                  autoScroll : true,
                                  html: '' ,                          
                                  listeners : {
                                    'activate' : function(p){
                                        Ext.getCmp('ufa_personBskRegistryWindow').hideShowButtons(p.id);
                                        
                                        var DatesGrid = new sw.Promed.ViewFrame({
                                        	id: 'DatesGrid', 
                                            hideHeaders:false,
                                            enableColumnHide: false,
                                            selectionModel: 'multiselect',
                                            multi : true,                                                  
                                            /*
                                            tbar: [
                                                {
                                                    xtype: 'button',
                                                    text: '&raquo; Сформировать динамику по ССЗ',
                                                    handler: function(){
                                                        var form =  Ext.getCmp('ufa_personBskRegistryWindow');
                                                        var sel = Ext.getCmp('DatesGrid').getGrid().getSelectionModel().getSelections();

                                                        if(sel.length == 0){
                                                           form.showMsg('Для формирования сравнения необходимо указать даты проведения обследования !');   
                                                        }
                                                        else{
                                                            var datesForCompare = '';
                                                            for(var k in sel){
                                                                if(typeof sel[k] == 'object'){
                                                                    datesForCompare +=sel[k].get('BSKRegistry_setDate')+",";
                                                                }
                                                            }
                                                            
                                                            var dates = datesForCompare.substr(0,(datesForCompare.length)-1);
                                                            var Person_id = form.personInfo.Person_id;
                                                            var MorbusType_id = form.MorbusType_id;
                                                            var Age = form.personInfo.age
                                                            //console.log(datesForCompare.substr(0,(datesForCompare.length)-1));                                                                    

                                                            //На рабочем или тестовом
                                                            var object_value = null;
                                                            var url = ((getGlobalOptions().birtpath)?getGlobalOptions().birtpath:'')
                                                            +'/run?__report=report/vac_Card063.rptdesign&paramPerson=' + object_value + '&__format=pdf';
                                                            
                                                            switch(Ext.getCmp('ufa_personBskRegistryWindow').MorbusType_id){
                                                                case 54 : var report = '\getBSKCompare.rptdesign'; break;
                                                                default : var report = '\getBSKCompare.rptdesign';
                                                            }                                            
                                                            
                                                            var url = 'http://192.168.200.58:91:91/birt-viewer/run?__report=Report/';
                                                            var paramStr = report
                                                                         +"&Dates='"+dates+"'"
                                                                         +'&Person_id='+Person_id
                                                                         +'&Sex_id='+form.personInfo.Sex_id
                                                                         +'&MorbusType_id='+MorbusType_id
                                                                         +'&Age='+Age
                                                                         +'&__format=html';
                                                            console.log(paramStr);             
                                                            window.open(url+paramStr, '_blank');                                                                               
                               
                                                        }   
                                                    }
                                                }
                                            ],
                                            */
                                            buttons : [
                                                {
                                                    xtype: 'button',
                                                    text: 'Сравнить данные',
                                                    width: 195,
                                                    handler: function(){
                                                        var form =  Ext.getCmp('ufa_personBskRegistryWindow');
                                                        var sel = Ext.getCmp('DatesGrid').getGrid().getSelectionModel().getSelections();

                                                        if(sel.length == 0){
                                                           form.showMsg('Для формирования сравнения необходимо указать даты проведения обследования !');   
                                                        }
                                                        else{
                                                            var datesForCompare = '';
                                                            for(var k in sel){
                                                                if(typeof sel[k] == 'object'){
                                                                    var preDate = sel[k].get('BSKRegistry_setDate').split('.');
                                                                    var dateStr = preDate[2]+'-'+preDate[1]+'-'+preDate[0];
                                                                    
                                                                    datesForCompare +=dateStr+",";
                                                                }
                                                            }
                                                            
                                                            var dates = datesForCompare.substr(0,(datesForCompare.length)-1).replace(/\./g,'-');
                                                            
                                                            //console.log(dates);
                                                            
                                                            var Person_id = form.personInfo.Person_id;
                                                            var MorbusType_id = form.MorbusType_id;
                                                            var Age = form.personInfo.age
                                                            //console.log(datesForCompare.substr(0,(datesForCompare.length)-1));                                                                    

                                                            //На рабочем или тестовом
                                                            var object_value = null;
                                                            //var url = ((getGlobalOptions().birtpath != '/birt-viewer/') ? getGlobalOptions().birtpath : 'http://192.168.200.58:91:91/birt-viewer')  
                                                            //+'/run?__report=Report/';
                                                            //var url = 'http://192.168.200.58:91:91/birt-viewer/run?__report=Report/';    
                                                           
                                                            var url = ((getGlobalOptions().birtpath)?getGlobalOptions().birtpath:'')+'run?__report=report';
                                                            switch(Ext.getCmp('ufa_personBskRegistryWindow').MorbusType_id){
                                                                case 84 : var report = '/getBSKCompare.rptdesign'; break;
                                                                default : var report = '/getBSKCompare.rptdesign';
                                                            }                                            
                                                            

                                                            var paramStr = report
                                                                         +"&Dates='"+dates+"'"
                                                                         +'&Person_id='+Person_id
                                                                         +'&Sex_id='+form.personInfo.Sex_id
                                                                         +'&MorbusType_id='+MorbusType_id
                                                                         +'&Age='+Age
                                                                         +'&__format=html';
                                                            var home =  window.location.host == '127.0.0.1:81' || window.location.host == '192.168.200.58:81' ? 'http://192.168.200.58:91' : '';         
                                                            window.open(home+url+paramStr, '_blank');                                                                               
                               
                                                        }   
                                                    }
                                                }                                            
                                            ],
                                        	contextmenu: false,
                                            border: true,
                                            width: 195,
                                            autoScroll: true,
                                        	height: Ext.getBody().getHeight()*0.58,
                                            object: 'DatesGrid',
                                        	dataUrl: '/?c=ufa_BSK_Register_User&m=getCompare',
                                        	autoLoadData: false,
                                            focusOnFirstLoad: false,
                                        	stringfields: [
                                        		{name: 'BSKRegistry_id', type: 'int', header: 'ID'},
                                                {name: 'BSKRegistry_setDate', width: 70,type: 'string',hidden:false, header: 'Дата'},
                                                {name: 'BSKRegistry_riskGroup',width: 90, type: 'int',hidden:false, header: 'Группа риска', align: 'center'},
                                        	],
                                        	actions: [
                                        		{name:'action_add',hidden: true,text: 'Создать',disabled: true},
                                                {name:'action_edit', hidden: true,text: 'Изменить', disabled: true },
                                                {name:'action_delete', hidden: true,text: 'Удалить', disabled: true,},
                                        		{name:'action_view', hidden: true},
                                        		{name:'action_refresh', hidden: true},
                                        		{name:'action_print', hidden: true}
                                        	], 
                                        	onLoadData: function() {
                                        
                                        	},
                                            onDblClick: function() {
                                                return;
                                            },
                                            listeners : {
                                                'render' : function(){
                                                    this.getGrid().getTopToolbar().hidden = true;
                                                }
                                            },
                                            clickToRow: function(){
                                                    var form = Ext.getCmp('ufa_personBskRegistryWindow');
                                            
                                            }
                                        
                                        }); 
                                        
                                        DatesGrid.getGrid().on(
                                            'rowclick',
                                            function(){
                                                var sel = Ext.getCmp('DatesGrid').getGrid().getSelectionModel().getSelections();
                                                //console.log(sel.length);
                                                
                                                if(sel.length>9){
                                                    Ext.getCmp('ufa_personBskRegistryWindow').showMsg('Указано максимальное количество дат для сравнения !'); 
                                                
                                                }
                                            }
                                        );
                                        
                                        this.removeAll();
                                        this.add(DatesGrid);
                                        this.doLayout();
                                        
                                        DatesGrid.getGrid().getStore().load({
                                            params : {
                                                Person_id : Ext.getCmp('ufa_personBskRegistryWindow').personInfo.Person_id,
                                                MorbusType_id : Ext.getCmp('ufa_personBskRegistryWindow').MorbusType_id
                                            }
                                        });
                                        
                                    }
                                  } 
                                  
                                },                                                                                                                                                               
                            ],
                                       
                        }
					
                    ], buttons: 
							[
								{
									 xtype: 'button',
									 id: 'closef',
									 text: 'Закрыть',
									 iconCls: 'close16',
									 handler: function(){
										Ext.getCmp('ufa_personBskRegistryWindow').refresh();
									 }
								}							
							]
                }

           
            ],
            buttons : [
            ],
            elemDisabled : function(){
                //elem.setDisabled(pmuser_id != getGlobalOptions().pmuser_id)
                
                //console.log(Ext.getCmp('ufa_personBskRegistryWindow').pmuser_id,getGlobalOptions().pmuser_id)
                
                //return true;
				//console.log('Ext.getCmp(ufa_personBskRegistryWindow).Lpu_id', Ext.getCmp('ufa_personBskRegistryWindow').Lpu_id);
                //return (Ext.getCmp('ufa_personBskRegistryWindow').pmuser_id == getGlobalOptions().pmuser_id) || (typeof Ext.getCmp('ufa_personBskRegistryWindow').pmuser_id == 'undefined') ? false : true;
				
		return (Ext.getCmp('ufa_personBskRegistryWindow').Lpu_id == getGlobalOptions().lpu_id) || (typeof Ext.getCmp('ufa_personBskRegistryWindow').Lpu_id == 'undefined') ? false : true;
            },
            //Управление группой вопросов Лекарственного лечения (кроме скрининга)
            manageLLquestions : function(id_question){
                //Легочная гипертензия
                //if(Ext.getCmp('ufa_personBskRegistryWindow').MorbusType_id == 88){
                    //лекарственное средство
                    //dose[препарат,дозировка]
                    var  dose = [];
                    
                    switch(id_question){
                        case 185: 
                            dose = [185,186];
                        break;   
                        case 187: 
                            dose = [187,192];
                        break;   
                        case 193: 
                            dose = [193,194];
                        break;   
                        case 195: 
                            dose = [195,196];
                        break;    
                        case 197: 
                            dose = [197,198];
                        break;   
                        case 199: 
                            dose = [199,200];
                        break;    
                        case 201: 
                            dose = [201,202];
                        break;   
                        case 203: 
                            dose = [203,204];
                        break;    
                        case 251: 
                            dose = [251,260];
                        break;   
                        case 252: 
                            dose = [252,261];
                        break;   
                        case 253: 
                            dose = [253,262];
                        break;   
                        case 254: 
                            dose = [254,263];
                        break;    
                        case 255: 
                            dose = [255,264];
                        break;   
                        case 256: 
                            dose = [256,265];
                        break;    
                        case 257: 
                            dose = [257,266];
                        break;   
                        case 258: 
                            dose = [258,267];
                        break;    
                        case 259: 
                            dose = [259,268];
                        break;                
                        case 365: 
                            dose = [365,366];
                        break;
                        case 367: 
                            dose = [367,368];
                        break;  
                        case 369: 
                            dose = [369,370];
                        break;  
                        case 371: 
                            dose = [371,372];
                        break; 
                        case 373: 
                            dose = [373,374];
                        break;   
                        case 375: 
                            dose = [375,376];
                        break;   
                        case 377: 
                            dose = [377,378];
                        break;   
                        case 379: 
                            dose = [379,380];
                        break; 
                        case 381: 
                            dose = [381,382];
                        break;                       
                        default: dose = false; 
                        break;                                                                                                
                    }

                    if(dose != false){
                        var form = this;  
                        var q = Ext.getCmp('Answer_'+dose[0]);
                        var a = Ext.getCmp('Answer_'+dose[1]);
                        
                        if(q.getValue() == 'Не принимает' || q.getValue() == ''){
                            for(var k in form.listIDS){
                                //Находим дозировка по ID для данного ЛС
                                if(form.listIDS[k].BSKObservElement_id == dose[1]){
                                    Ext.getCmp('QuestionPanel_' + dose[1]).removeAll();
                                    //Нужно создать поле автоматически - если Не принимает данное ЛС
                                    var param = {}
                                        param.data = {}
                                        param.data = form.listIDS[k];
                                        param.data.id = dose[1];
                                       
                                    var field = form.createField(3, param);

                                    field.style = 'border:0px;background-image:url("");border-bottom:1px dashed gray; font-weight:bold';
                                    
                                    Ext.getCmp('QuestionPanel_' + dose[1]).add(field);                                                                        
                                    Ext.getCmp('QuestionPanel_' + dose[1]).doLayout();   
                                    
                                    field.setValue('-');
                                    field.setDisabled(true);

                                    Ext.getCmp('QuestionPanel_' + dose[1]).add(form.getComboUnits(dose[1])); 
                                    Ext.getCmp('QuestionPanel_' + dose[1]).doLayout();   
                                    Ext.getCmp('UnitCombo_'+dose[1]).setDisabled(true); 
                                }
                            }    
                        }
                        else {
                             if(typeof a == 'undefined'){
                                for(var k in form.listIDS){
                                    //Находим дозировка по ID для данного ЛС
                                    if(form.listIDS[k].BSKObservElement_id == dose[1]){
                                        Ext.getCmp('QuestionPanel_' + dose[1]).removeAll();
                                        //Нужно создать поле автоматически - если Не принимает данное ЛС
                                        var param = {}
                                            param.data = {}
                                            param.data = form.listIDS[k];
                                            param.data.id = dose[1];
                                            param.data.maskRe = /[0-9]/; 
                                            
                                        var field = form.createField(3, param);
                                        
                                        field.style = 'border:0px;background-image:url("");border-bottom:1px dashed gray; font-weight:bold';
                                        
                                        Ext.getCmp('QuestionPanel_' + dose[1]).add(field);                                                                        
                                        Ext.getCmp('QuestionPanel_' + dose[1]).doLayout();   
                                        
                                        field.setValue('');
                                        field.setDisabled(false);
    
                                        Ext.getCmp('QuestionPanel_' + dose[1]).add(form.getComboUnits(dose[1])); 
                                        Ext.getCmp('QuestionPanel_' + dose[1]).doLayout();   
                                        Ext.getCmp('UnitCombo_'+dose[1]).setDisabled(false); 
                                    }
                                }                                  
                             }   
                             else{
                                Ext.getCmp('UnitCombo_'+dose[1]).setDisabled(false);
                                Ext.getCmp('Answer_'+dose[1]).setValue('');
                                Ext.getCmp('Answer_'+dose[1]).maskRe = /[0-9]/;
                                Ext.getCmp('Answer_'+dose[1]).focus(true);
                                Ext.getCmp('Answer_'+dose[1]).setDisabled(false);
                            }
                        } 
                        
                        Ext.getCmp('Answer_'+dose[1]).on(
                            'keyup',
                            function(){
                                if(this.getValue().length>5){
                                    this.setValue(this.getValue().substr(0,5));
                                }
                            }
                        );            
                      
                    }
                //}
            },
            getLastVizitRegistry : function(BSKRegistry_id){
                var form = Ext.getCmp('ufa_personBskRegistryWindow');
                //Сравнение скрининга
                //console.log(typeof Ext.getCmp('ufa_personBskRegistryWindow').MorbusType_id)
                //console.log(Ext.getCmp('ufa_personBskRegistryWindow').MorbusType_id)
                
                if(form.MorbusType_id != '19'){
                    Ext.getCmp('compare').setDisabled(false);
                }
                else{
                    Ext.getCmp('compare').setDisabled(true);
                }                


                form.robot = true;

                Ext.Ajax.request({
                	url: '/?c=ufa_Bsk_Register_User&m=getLastVizitRegistry',
                	params: {
                        MorbusType_id:form.MorbusType_id,
                        Person_id : form.personInfo.Person_id,
                        setDate : Ext.getCmp('calday').getValue(),
                        BSKRegistry_id : BSKRegistry_id
                	},
                	callback: function(options, success, response) {

                        
                        if (success === true) {  
                            var result = Ext.util.JSON.decode(response.responseText);
                            
                            
                            //console.log('RESULT', result);
                            
                            
                            Ext.getCmp('information').removeAll();
                            form.addRegistryData();
                            //Масочка на время получения данных о имеющихся регистрах
                            form.getEl().mask('Получение данных по пациенту').show();

                            setTimeout(function(){
                                if(result.length > 0){
                                    form.pmuser_id = false;
									form.Lpu_id = false;
                                    for(var k in result){
                                        //console.log(k, 'result[k]',  result[k])
                                        if(typeof result[k] == 'object'){
                                            
                                            
                                            
                                            if(form.pmuser_id === false){
												form.pmuser_id = result[k].pmUser_insID;
                                            }
											
                                            if(form.Lpu_id === false){
												//console.log('result[k].Lpu_id',result[k].Lpu_id);
                                                form.Lpu_id = result[k].Lpu_id;
												//form.Lpu_id =999;
                                            }											

                                            //console.info('form.pmuser_id', form.pmuser_id);
											
											//console.log(result[k].BSKObservElement_id, Ext.getCmp('Answer_'+result[k].BSKObservElement_id));
											
                                            if(typeof  Ext.getCmp('addAnswer_'+result[k].BSKObservElement_id) != 'undefined' || typeof  Ext.getCmp('Answer_'+result[k].BSKObservElement_id) != 'undefined'){
                                                var answerPanel =  Ext.getCmp('addAnswer_'+result[k].BSKObservElement_id);
                                               
                                                
                                                //console.log(k, result[k].BSKObservElement_id, result[k].BSKRegistryData_data); 
                                                
                                                var params = {
                                                    data:{
                                                        id:result[k].BSKObservElement_id
                                                    }
                                                }
                                                
                                               // console.log('params', params.data.id, result[k].BSKRegistryData_data);
                                               //console.log(result[k].BSKObservElement_id, Ext.getCmp('Answer_'+result[k].BSKObservElement_id));
											   if (typeof Ext.getCmp('addAnswer_'+result[k].BSKObservElement_id) != 'undefined' && typeof answerPanel.params !='undefined' ) {
												  form.renderAnswer(answerPanel.params); 
											   }
                                                
                                                
                                                var BSKRegistryData_data = result[k].BSKRegistryData_data;
                                                /**
                                                 * Корректировки для ОКС MorbusType = 19
                                                 */                                     
                                                
                                                switch(parseInt(result[k].BSKObservElement_id)){
                                                    /*
                                                    case 270 : 
                                                    case 271 : 
                                                    case 272 : 
                                                    case 274 : 
                                                    case 276 : var tmp = BSKRegistryData_data.split(/ /g); if(tmp[1]) BSKRegistryData_data = tmp[1].replace(':00','');
                                                    break;
                                                    */
                                                    case 284 :
                                                    case 285 : 
                                                    case 286 :
                                                    case 277 :
                                                    case 278 :
                                                    case 279 :
                                                    case 280 :
                                                    case 281 :
                                                    case 282 :
                                                    case 283 : if(BSKRegistryData_data.inlist(['Да', 'Да.'])){
                                                                   Ext.getCmp('Answer_'+result[k].BSKObservElement_id).style = 'color:red!important'; 
                                                                   //Ext.getCmp('Answer_'+result[k].BSKObservElement_id).doLayout();
                                                               }
                                                                                                        
                                                    break;
                                                }
													//console.log(k, 'isAnsver? ',Ext.getCmp('Answer_'+result[k].BSKObservElement_id),result[k].BSKObservElement_id, BSKRegistryData_data);
												Ext.getCmp('Answer_'+result[k].BSKObservElement_id).setValue(BSKRegistryData_data); 
												Ext.getCmp('Answer_'+result[k].BSKObservElement_id).BSKRegistryData_id = result[k].BSKRegistryData_id;
												if (Ext.getCmp('Answer_'+result[k].BSKObservElement_id).getValue() == 'Да' || Ext.getCmp('ufa_personBskRegistryWindow').elemDisabled()) {//Если из базы загружен ответ 'да' или это не заполняющая организация, то залочиваем
													Ext.getCmp('Answer_'+result[k].BSKObservElement_id).setDisabled(true);
												}
                                                
                                                if(result[k].BSKUnits_name.length>0){
                                                    if(typeof  Ext.getCmp('UnitCombo_'+result[k].BSKObservElement_id) != 'undefined'){
                                                        Ext.getCmp('UnitCombo_'+result[k].BSKObservElement_id).setValue(result[k].BSKUnits_name);
														if (Ext.getCmp('Answer_'+result[k].BSKObservElement_id).getValue() == 'Да' || Ext.getCmp('ufa_personBskRegistryWindow').elemDisabled()) {//Если из базы загружен ответ 'да' или это не заполняющая организация, то залочиваем размерность
															Ext.getCmp('UnitCombo_'+result[k].BSKObservElement_id).setDisabled(true);
														}														
														//Ext.getCmp('UnitCombo_'+result[k].BSKObservElement_id).setDisabled(true);
                                                    }
                                                }
                                           
                                            }
                                            else{
                                                //console.log(k, '---');
                                            }
                                        }
                                    }   
                                }
                                /**
                                 При переключении между предметами наблюдения - эти 2 даты выставляются в зависимости от наличия заполненных анкет
                                 если есть ранее сохранённая анкета - то дата этой анкеты
                                 если анкета не была ранее сохранена - то текущая дата
                                */
                                Ext.getCmp('TextFieldDate').setValue(new Date());
                                Ext.getCmp('calday').setValue(new Date());
                                Ext.getCmp('calday').datetime = new Date().format('Y-m-d H:i');
                                //Пока тестим на скрининге
                                if(typeof result[3] != 'undefined'){
                                    
                                    if(typeof Ext.getCmp('Answer_'+result[3].BSKObservElement_id) != 'undefined')
                                        Ext.getCmp('Answer_'+result[3].BSKObservElement_id).focus(false);
                                    // Поставить дату обследования                         
                                    //Ext.getCmp('calday').setValue(new Date(result[3].BSKRegistry_setDate.date));
                                    var date = result[3].BSKRegistry_setDate.date.split(" ");
                                    Ext.getCmp('calday').setValue(date[0]);
                                    Ext.getCmp('calday').datetime = new Date(result[3].BSKRegistry_setDate.date).format('Y-m-d H:i');
                                    Ext.getCmp('TextFieldDate').setValue(date[0]);
                                    // Поставим группу риска на текущий регистр
                                    
                                    //Только для скрининга
                                    if(Ext.getCmp('ufa_personBskRegistryWindow').MorbusType_id == 84){
                                        Ext.getCmp('ufa_personBskRegistryWindow').setGroupRisk(result[3].BSKRegistry_riskGroup);
                                    }
                                    
                                    //Проверяем время жизни анкеты
                                    var groupRisk = form.personInfo.BSKRegistry_riskGroup;
                                    var BSKRegistry_setDate = new Date(Ext.getCmp('calday').getValue());
                                    var today = new Date();
                                    var days = Math.floor((today-BSKRegistry_setDate)/(1000*60*60*24));
                                    
                                    var limit = 0;
                                    
                                    switch(groupRisk){
                                        case 3 : limit = 6*30; break;
                                        case 2 : limit = 12*30; break;
                                        case 1 : limit = 18*30; break;
                                        default: limit = 999;
                                    }
                                    
                                    if(limit < days){
                                        sw.swMsg.show({
                                            id: 'Question',
                                            width: 500,
                                			buttons: Ext.Msg.YESNO,
                                			fn: function(buttonId, text, obj) {
                                				if ( buttonId != 'yes' ) {
                                                    return;
                                				}
                                                else{
													Ext.getCmp('ufa_personBskRegistryWindow').isButtonAdd = true;
                                                    form.addRegistryData();													
                                                }
                                			}.createDelegate(this),
                                			icon: Ext.MessageBox.QUESTION,
                                			msg: 'Последняя анкета на данного пациента больше не актуальна, создать новую анкету ?',
                                			title: 'Вопрос'
                                		}); 
                                    }
                                    
                                    //Ловим регистр с которым работаем
                                    form.BSKRegistry_id = result[3].BSKRegistry_id;
                                    Ext.Ajax.request({
                                        url: '/?c=Ufa_BSK_Register_User&m=setIsBrowsed',
                                        params: {
                                            'BSKRegistry_id': result[3].BSKRegistry_id
                                        },
                                        callback: function(options, success, response) {
                                            if (success) {	
                                                var obj = Ext.util.JSON.decode(response.responseText);
                                            } else {
                                                sw.swMsg.alert(langs('Ошибка'), langs('Ошибка при установлении признака просмотра анкеты'));
                                            }
                                        }
                                    });
                                }

                                form.getEl().mask().hide();
                                Ext.getCmp('saveBskDataButton').setDisabled(true); 
                               
                               //Блокировка даты анкеты если анкета не новая 
                               if(Ext.getCmp('ufa_personBskRegistryWindow').BSKRegistry_id){
                                    Ext.getCmp('TextFieldDate').setDisabled(true);   
                               }                                
                               
                               var TabPanel = Ext.getCmp('infotab').items.items[0].items.items[1];
                               
                               if(typeof TabPanel !='object'){
                                   return;
                               }
                               
                               var Stages = TabPanel.items.items;
                               
                               //Ext.getCmp('prevButton').handler();
                               
                               if(Ext.getCmp('ufa_personBskRegistryWindow').Periods[Ext.getCmp('ufa_personBskRegistryWindow').MorbusType_id] != false){
                                   
                                   //console.log('MorbusType_id', Ext.getCmp('ufa_personBskRegistryWindow').MorbusType_id);
                                   //console.log('=>',Ext.getCmp('ufa_personBskRegistryWindow').MorbusType_id, Ext.getCmp('ufa_personBskRegistryWindow').MorbusType_id.inlist([84,88]));
                                   //Блокировка для 84 Скрининг
                                   if(Ext.getCmp('ufa_personBskRegistryWindow').MorbusType_id.inlist([84,88,89,19,50])){
                                   
                                       for(var k in Stages){
                                         if(typeof Stages[k] == 'object'){
                                            //этапы анкетирования ЛД и ФД нельзя блокировать у сохранённой анкеты
                                            //if((/Лабораторная диагностика/).test(Stages[k].title) || (/Функциональная диагностика/).test(Stages[k].title)){
                                                var answers = Stages[k].items.items;
                                                //console.log('--->',answers)
                                                for(var k in answers){
                                                    if(typeof answers[k] == 'object'){
                                                        var answer = answers[k].items.items;
                                                        //данные по ЛИС и ФД были заполнены ранее
														if (typeof answer[0].items != 'undefined' && answer[0].items.keys.length > 0){

														   answer[0].items.items[0].setDisabled(!answer[0].items.items[0].getValue() == '');
														   //используются единиццы измерения
														   if(typeof answer[0].items.items[1] != 'undefined'){
															  answer[0].items.items[1].setDisabled(!answer[0].items.items[0].getValue() == '');
														   }
														}
                                                    }
                                                }
                                            //}
                                            //Все остальные должны быть залочены
                                            /*else{
                                                var answers = Stages[k].items.items;
                                                
                                                for(var k in answers){
                                                    if(typeof answers[k] == 'object'){
                                                        
                                                        if(typeof answers[k].items.items[1] == 'object'){
                                                            //console.log('1.', answers[k].items.items[1]);
                                                            answers[k].items.items[1].setDisabled(true); 
                                                                                                            
                                                        }
                                                        else if(typeof answers[k].items.items[0].items != 'undefined'){
                                                              
                                                              //лочим ранее введённый ответ
                                                              answers[k].items.items[0].items.items[0].setDisabled(true);
                                                              
                                                              //если есть ед. измерения - лочим и их
                                                              if(typeof answers[k].items.items[0].items.items[1] != 'undefined'){
                                                                  answers[k].items.items[0].items.items[1].setDisabled(true);  
                                                              }
                                                        }
                                                        //Ответы из БД нарисованы по другому
                                                        else{
                                                            //console.log(answers[k].items.items[0]);
                                                            answers[k].items.items[0].setDisabled(true);
                                                        }
                                                     
                                                        
                                                    }
                                                }        
                                            }*/
                                         }
                                       }
                                    }
                                    //Блокировка для 88 Лёгочной гипертензии
                                   if(Ext.getCmp('ufa_personBskRegistryWindow').MorbusType_id == 88){
                                   
                                   } 
                                   
                                    //Блокировка для 89 Артериальной гипертензии
                                   if(Ext.getCmp('ufa_personBskRegistryWindow').MorbusType_id == 89){
                                   
                                   }                                    
                                   
                                                                      
                             }  
                                
                                
                                
                            }, 4000)      
                        }
   
                    }
                });  
                
              
                
                Ext.getCmp('saveBskDataButton').setDisabled(true);           
            },         
            getRegistryDates : function(){
               var wnd = this;
                Ext.Ajax.request({
                	url: '/?c=ufa_Bsk_Register_User&m=getRegistryDates',
                	params: {
                        MorbusType_id:Ext.getCmp('ufa_personBskRegistryWindow').MorbusType_id,
                        Person_id : Ext.getCmp('ufa_personBskRegistryWindow').personInfo.Person_id,
                        setDate : Ext.getCmp('calday').getValue()
                	},
                	callback: function(options, success, response) {
                         
                        if (success === true) {    
                            var RegistryDates = Ext.util.JSON.decode(response.responseText);
                            
                            //if(RegistryDates.length > 0){

                                var dates = [];
                                
                                for(var k in RegistryDates){
                                    if(typeof RegistryDates[k].BSKRegistry_id != 'undefined'){
                                        dates.push({
                                            BSKRegistry_id : RegistryDates[k].BSKRegistry_id,
                                            BSKRegistry_setDate : new Date(RegistryDates[k].BSKRegistry_setDate.date).format('Y-m-d H:i')
                                        })
                                        

                                        
                                    }
                                }
                                Ext.getCmp('ufa_personBskRegistryWindow').Periods[Ext.getCmp('ufa_personBskRegistryWindow').MorbusType_id] = {};
                                
                                Ext.getCmp('ufa_personBskRegistryWindow').Periods[Ext.getCmp('ufa_personBskRegistryWindow').MorbusType_id] = dates;
                               
                                if(Ext.getCmp('ufa_personBskRegistryWindow').Periods[Ext.getCmp('ufa_personBskRegistryWindow').MorbusType_id].length <=1){
                                     Ext.getCmp('prevButton').setDisabled(true);                    
                                }
                                else{
                                     Ext.getCmp('prevButton').setDisabled(false);     
                                }        
                                
                                //console.log(Ext.getCmp('ufa_personBskRegistryWindow').MorbusType_id, Ext.getCmp('ufa_personBskRegistryWindow').Periods[Ext.getCmp('ufa_personBskRegistryWindow').MorbusType_id]);                        
                            } 
                        //}
                    }
                });                  
            },
            renderRegistry : function(){
                
            },
            findRegisterData: function(){
              
                var listIDS = Ext.getCmp('ufa_personBskRegistryWindow').listIDS;
                var resultData = []; 
                var questions_ids = [];
                
                for(var k in listIDS){
                    
                    if(typeof listIDS[k] == 'object'){
                        var postfix_id = listIDS[k].BSKObservElement_id;
                        var answer = (typeof Ext.getCmp('Answer_'+postfix_id) == 'undefined') ? null : Ext.getCmp('Answer_'+postfix_id).getValue();
                        var min = listIDS[k].min;
                        var max = listIDS[k].max;
                        var stage = listIDS[k].stage;
                        var html = listIDS[k].html;
                        
                        //console.log(answer, stage, Ext.getCmp('Answer_'+postfix_id));
                        
                        if(answer == null && stage == 1){
                            //this.showMsg('Необходимо заполнить все данные по группе "Факторы риска"!<br/><br/><b>Вопрос : </b> '+ html);
                            //return;
                        }
                        else if(answer != null){
                            if(answer.length = 0 && stage == 1){
                                //this.showMsg('Необходимо заполнить все данные по группе "Факторы риска"!<br/><br/><b>Вопрос : </b> '+ html);
                                
                                //return;                            
                            }
                            else{
                                 var unit = null;
                                 
                                 //Если вопросы не активны - то и проверять наличие ед. измерения - смысла нет
                                 if(answer != null){
                                    //Половина вопросов вообще не привязана к ед. измерения
                                    if(typeof Ext.getCmp('UnitCombo_'+listIDS[k].BSKObservElement_id) != 'undefined'){
                                        //Проверяем наличие ед. измерения у ответа
                                        var unit = Ext.getCmp('UnitCombo_'+listIDS[k].BSKObservElement_id).getValue();
                                        
                                        if(unit.length == 0){
                                            this.showMsg('Необходимо указать единицу измерения для ответа на вопрос:<br/><br/> <i>'+ html + '</i>');
                                            
                                            return;                
                                        }   
                                    }
                                 }
                                // Проверка на допустимые значения не включены 
                                // т.к. на данный момент по ним нет сведений      
                                //console.log('listIDS[k]', listIDS[k]);  
                                var BSKRegistryData_id = (typeof listIDS[k].BSKRegistryData_id != 'undefined') ? listIDS[k].BSKRegistryData_id : null
                                                         
                                questions_ids.push(listIDS[k].BSKObservElement_id)
                                resultData.push([listIDS[k].BSKObservElement_id, answer, unit, listIDS[k].html, listIDS[k].BSKObservElementFormat_id, BSKRegistryData_id]);    
                            }
                        }
                    }
                }  
                //Отаправляем данные на сторону сервера для анализа группы и сохранения
                //console.log("Есть регистр?", Ext.getCmp('ufa_personBskRegistryWindow').BSKRegistry_id);
                
                //return;
                
                if(Ext.getCmp('ufa_personBskRegistryWindow').BSKRegistry_id === false){
                    //console.log('SAVE REGISTRY');
                    this.saveRegistryData(resultData,questions_ids);               
                }
                else{
                    //console.log('UPDATE REGISTRY');
                    this.updateRegistryData(resultData,questions_ids);     
                }
                
                form.getRegistryDates();
                            
            },
            checkQuestionsAnswer : function(){
                    var test = false;
                    var qstns = [];
                    
					var postfixQuestions = Ext.getCmp('ufa_personBskRegistryWindow').postfixQuestions; 
					//console.log('postfixQuestions',postfixQuestions);
                    //Проверка анкетных вопросов на заполнение
                    /*switch(Ext.getCmp('ufa_personBskRegistryWindow').MorbusType_id){
                        //Скрининг 24,25, 26 убрали
                        
                        case 84 : var postfixQuestions = [31,32,33,34,35,36,37,39,40,41,42,43,44,45,46,47,48,49,50,51,54,55,58,59,60,61,62,63,65,67,78,79,111,112,113,114, 107,108,109,110]; 
                        break;
                        //Лёгочная гипертензия
                        case 88 : var postfixQuestions = [142,143,173,174,175,172,144,145,146,147,148,149,151,156,157,158,159,160,161,162/*,185,186,187,192,193,194,195,196,197,198,19,200,201,202,203,204];
                        break;

                        //Артериальная гипертензия
                        case 89 : var postfixQuestions = [205,206,207,208,209,210,211,212,213,214,215,216,217,218,219,220,221,222,223];
                        break;

                        //Артериальная гипертензия
                        case 50 : var postfixQuestions = [318,319,320,321];
                        break;
                    
                        default : var postfixQuestions = [];
                    }*/
                    //console.log('postfixQuestions', postfixQuestions);
                    for(var k in postfixQuestions){
                        //QuestionPanel_
                        var answ = Ext.getCmp('Answer_'+postfixQuestions[k]);
                        
                        //console.log('answ', answ);
                         
                        if(typeof answ == 'undefined' || (typeof answ != 'undefined' &&  answ.getValue() == '')){
                            test = false;
                            
                            var Panel = Ext.getCmp('QuestionPanel_'+postfixQuestions[k]);
                            
                            if(typeof Panel != 'undefined'){
                                
                                var qp = Panel.initialConfig.html;
                                
                                var ptrn = /important;">(.*)<\/td>/;
                                var textQp = qp.match(ptrn);
                                
                                if(textQp){   
                                    //console.log('textQp 1: ', textQp[1]);                                        
                                    qstns.push(textQp[1]);  
                                }
                                
                            }
                            
                        }
                        /*                    
                        else{
                            test = false; 
                            
                            var Panel = Ext.getCmp('QuestionPanel_'+postfixQuestions[k]);
                            
                            if(typeof Panel != 'undefined'){

                                var qp = Panel.initialConfig.html;
                               console.log(qp)
                                var ptrn = /important;">(.*)<\/td>/;
                                var textQp = qp.match(ptrn);
                                 console.log(textQp)
                                if(textQp){    
                                    console.log('textQp 2: ', textQp[1]);                                    
                                    qstns.push(textQp[1]);  
                                }
                            }
                        }
                       */                         
                    }  
                    
                    //console.log('qstns', qstns);
                    //console.log(test, qstns.length);
                    if(test === false && qstns.length > 0){
                        
                        var contentError = '<ol>';
                        for(var k in qstns){
                            if(typeof qstns[k] != 'function'){
                                contentError = contentError + '<li>- ' + qstns[k]  + '</li>';
                            }
                        }

                        Ext.getCmp('addBskDataButton').setDisabled(true);
                        Ext.getCmp('ufa_personBskRegistryWindow').showMsg('<b>Необходимо ответить на обязательные вопросы:</b><p>&nbsp;</p><br/>'+contentError+'</ol>');    
                        return false;
                    }   
                    
                    return true;                  
            },
            //Определение признака по ответу
            saveRegistryData: function(ListAnswers,questions_ids){
                //console.log('SAVE DATA');
                //Проверка ответов на анкетные вопросы - обязательны ответы
                //console.log('-->', this.checkQuestionsAnswer());
                if(this.checkQuestionsAnswer() === false){
                    return;
                }
                
                //return;
                //Поиск регистра на эту дату, предмет наблюдения, пациента 
                Ext.Ajax.request({
                	url: '/?c=ufa_Bsk_Register_User&m=checkRegisterDate',
                	params: {
                        MorbusType_id:Ext.getCmp('ufa_personBskRegistryWindow').MorbusType_id,
                        Person_id : Ext.getCmp('ufa_personBskRegistryWindow').personInfo.Person_id,
                        BSKRegistry_setDate : Ext.getCmp('calday').getValue()
                	},
                	callback: function(options, success, response) {
                         
                        if (success === true) {   
                            var responseText = Ext.util.JSON.decode(response.responseText);
                            
                            //console.log('responseText', responseText, responseText.length);
                            
                            if(responseText.length>0){
                                //Ext.getCmp('ufa_personBskRegistryWindow').checkRegisterDateFlag = false;
                                Ext.getCmp('ufa_personBskRegistryWindow').showMsg('Регистр по данному предмету наблюдения, для данного пациента на указанную дату уже существует!'); 
                                return false;
                            }
                            else{   
                                

                                    
                                    Ext.Ajax.request({
                                    	url: '/?c=ufa_Bsk_Register_User&m=preSaveRegistryData',
                                    	params: {
                                    	    BSKObject_id : form.GridObjects.getGrid().getSelectionModel().getSelected().data.BSKObject_id,
                                            PersonData   : Ext.util.JSON.encode(Ext.getCmp('ufa_personBskRegistryWindow').personInfo),
                                            ListAnswers  : Ext.util.JSON.encode(ListAnswers),
                                            questions_ids : Ext.util.JSON.encode(questions_ids),
                                            MorbusType_id:Ext.getCmp('ufa_personBskRegistryWindow').MorbusType_id,
                                            Person_id : Ext.getCmp('ufa_personBskRegistryWindow').personInfo.Person_id,
                                            setDate : Ext.getCmp('calday').getValue()
                                    	},
                                    	callback: function(options, success, response) {                                            
                                            if (success === true) {   
												//console.log('ListAnswers',ListAnswers); 
                                                var responseText = Ext.util.JSON.decode(response.responseText);
                                                
                                                //console.log(responseText[0]);
                                                
                                                //console.log('RESPONSE', response);
                                                if(responseText.Error_Code == null && Ext.getCmp('ufa_personBskRegistryWindow').MorbusType_id == 84){
                                                    
                                                    Ext.getCmp('ufa_personBskRegistryWindow').setGroupRisk(responseText[0].riskGroupStatus);

                                                }
                                                Ext.getCmp('ufa_personBskRegistryWindow').BSKRegistry_id = responseText[0].BSKRegistry_id;
                                                Ext.getCmp('ufa_personBskRegistryWindow').showMsg('Данные регистра успешно сохранены !'); 
                                                Ext.getCmp('ufa_personBskRegistryWindow').getRegistryDates();
                                                Ext.getCmp('ufa_personBskRegistryWindow').getLastVizitRegistry(Ext.getCmp('ufa_personBskRegistryWindow').BSKRegistry_id);                                                   
                                            }
                                        }
                                    }); 
                            }   
                        }
                    }
               });  
                   
                 
            },
            updateRegistryData: function(ListAnswers,questions_ids){

                //console.log('UPDATE DATA');
                //Проверка ответов на анкетные вопросы - обязательны ответы
                if(this.checkQuestionsAnswer() === false){
                    return;
                }
                
                //Правка для обновления данных
                for(var k in ListAnswers){
                    if(typeof Ext.getCmp('Answer_'+ListAnswers[k][0]) != 'undefined'){
                        if(typeof Ext.getCmp('Answer_'+ListAnswers[k][0]).BSKRegistryData_id != 'undefined'){
                            //console.log(Ext.getCmp('Answer_'+ListAnswers[k][0]));                  
                            
                            var el = Ext.getCmp('Answer_'+ListAnswers[k][0]);
                            
                            ListAnswers[k].push(el.BSKRegistryData_id);
                        }
                    }
                }

                
                Ext.Ajax.request({
                	url: '/?c=ufa_Bsk_Register_User&m=preSaveRegistryData',
                	params: {
                	    BSKRegistry_id : Ext.getCmp('ufa_personBskRegistryWindow').BSKRegistry_id,
                        PersonData   : Ext.util.JSON.encode(Ext.getCmp('ufa_personBskRegistryWindow').personInfo),
                        ListAnswers  : Ext.util.JSON.encode(ListAnswers),
                        questions_ids : Ext.util.JSON.encode(questions_ids),
                	},
                	callback: function(options, success, response) {
                         
                        if (success === true) {   
                            
                            var responseText = Ext.util.JSON.decode(response.responseText);
                            
                            //console.log('RESPONSE', responseText);
                            if(responseText.Error_Code == null){
                                //console.log('1', responseText);
                                //console.log('2', responseText[0]);
                                //Ext.getCmp('ufa_personBskRegistryWindow').BSKRegistry_id = responseText[0].BSKRegistry_id;
                                Ext.getCmp('ufa_personBskRegistryWindow').setGroupRisk(responseText[0].riskGroupStatus);
                                Ext.getCmp('ufa_personBskRegistryWindow').showMsg('Данные регистра успешно сохранены !');    
                            }
                        }
                    }
                });                   
                                                                            

            },      
            //Скрининг 84  Группа риска       
            setGroupRisk : function(numberGroupRisk){
                                switch(parseInt(numberGroupRisk)){
                                    case 1: var textGroupRisk = '(I)'; break;
                                    case 2: var textGroupRisk = '(II)'; break;
                                    case 3: var textGroupRisk = '(III)'; break;
                                    default : var textGroupRisk = '(-)'; break
                                }  
                                //console.log(textGroupRisk, textGroupRisk);
                                
                                var record = Ext.getCmp('ufa_personBskRegistryWindow').GridObjects.getGrid().getSelectionModel().getSelected(); 
                                     //console.log(record.get('MorbusType_Name'));  
                               
                               //  if(!/I+|\-/gi.test(record.data.MorbusType_Name)){
                                    var pattern = /\([I\-]+\)/gi; 
                                    record.set('MorbusType_Name',record.get('MorbusType_Name').replace(pattern, '') + ' '+textGroupRisk + '');
                                    record.commit();  
                                    
                                    //console.log(record.get('MorbusType_Name'));                       
                               //  }                     
            },
            //Легочная гипертензия 88 Функциональный класс легочной гипертензии
            setFunctionClass88: function(numberClass){
                                //console.log('numberClass', numberClass);
                                var numberClassText = (numberClass == '' || numberClass == null) ? '-' : numberClass;
                                
                                //console.log('numberClassText', numberClassText);
                                var record = Ext.getCmp('ufa_personBskRegistryWindow').GridObjects.getGrid().getSelectionModel().getSelected(); 
                               
                               //  if(!/I+|\-/gi.test(record.data.MorbusType_Name)){
                                    var pattern = /\([I\-]+\)/gi; 
                                    record.set('MorbusType_Name',record.get('MorbusType_Name').replace(pattern, '') + ' ('+numberClassText + ')');
                                    record.commit();                  
                               //  }                       
            },
            //Артериальная гипертензия 89 	Степень риска
            setDegreeOfRisk89 : function(numberDegree){
                                var numberDegreeText = (numberDegree == '' || numberDegree == null) ? '-' : numberDegree;
                                
                                var record = Ext.getCmp('ufa_personBskRegistryWindow').GridObjects.getGrid().getSelectionModel().getSelected(); 
                               
                               //  if(!/I+|\-/gi.test(record.data.MorbusType_Name)){
                                    var pattern = /\([I\-]+\)/gi; 
                                    record.set('MorbusType_Name',record.get('MorbusType_Name').replace(pattern, '') + ' ('+numberDegreeText + ')');
                                    record.commit();                  
                               //  }                  
            },
            getIMT : function(field, groupFields){
                var value = field.getValue();//.replace(',', '.');
                
                //field.setValue(value);
                //Скрининг
                if(parseInt(field.id.replace('Answer_','')).inlist([groupFields[0],groupFields[1]])){
                    var height = Ext.getCmp('Answer_' + groupFields[0]);
                    var weight = Ext.getCmp('Answer_' + groupFields[1]);
                    var imt = Ext.getCmp('Answer_' + groupFields[2]);
                    
               
                    
                    
                    if(typeof height != 'undefined' && typeof weight != 'undefined' && typeof imt !='undefined'){
                        var unitHeight = Ext.getCmp('UnitCombo_'+ groupFields[0]).getValue();
                        var unitWeight = Ext.getCmp('UnitCombo_'+ groupFields[1]).getValue();

                        var height = (unitHeight == 'см') ? parseInt(height.getValue())/100 : parseInt(height.getValue()); 
                        var weight = (unitWeight == 'кг') ? parseInt(weight.getValue()) : parseInt(weight.getValue())*1000;
        
                        var indexBody = (weight / (height*height)).toFixed(1);
                        imt.setValue(indexBody);
                        
                        if(imt.getValue() == 'NaN'){
                            imt.setValue(0);
                        }
                    }
                }                 
            },
            addRegistryData: function(){
				this.pmuser_id = getGlobalOptions().pmuser_id;
				this.Lpu_id = getGlobalOptions().lpu_id;
				Ext.getCmp('ufa_personBskRegistryWindow').listIDSfocus = [];
				Ext.getCmp('ufa_personBskRegistryWindow').PrefixQuest = 0;
                Ext.getCmp('ufa_personBskRegistryWindow').BSKRegistry_id = false;
                Ext.getCmp('information').removeAll();
                
                /** По инициативе Влада - поле с датой проведения опроса */
                
                //console.log('textfield', Ext.getCmp('ufa_personBskRegistryWindow').elemDisabled());
                
         		var TextFieldDate = new sw.Promed.SwDateField({
        			id : 'TextFieldDate',
                    labelField: 'Дата проведения',
                    labelSeparator : ':',
                    disabled : Ext.getCmp('ufa_personBskRegistryWindow').elemDisabled(),
                    labelWidth : '50px',
                    //disabled: true,
                    width: '100px',
                	plugins: [
                		new Ext.ux.InputTextMask('99.99.9999', false)
                	],
                	xtype: 'swdatefield',
                	format: 'd.m.Y',                    
                    value : Ext.getCmp('calday').getValue(),
					listeners: {
					   /*
                        'specialkey':  function(field, e){
                            if( (parseInt(params.data.id) == 107) || (parseInt(params.data.id) == 108) ){
                                //console.log('e.getKey: ', e.getKey());
                                if (e.getKey() == e.ENTER) {
                                    //Ext.getCmp('searchRecomendationButton').handler();
                                      Ext.getCmp('ufa_personBskRegistryWindow').getIndex(parseInt(params.data.id));
                                }
                            }
                        },
                        */
                        'change' : function(){
                            if(Ext.getCmp('ufa_personBskRegistryWindow').MorbusType_id == 19){
                                Ext.getCmp('saveBskDataButton').setDisabled(true);
                                return;
                            } 
                           
                            if(Ext.getCmp('GridObjectsUser').getGrid().store.data.length > 0 && Ext.getCmp('GridObjectsUser').getGrid().store.data.items[0].json.Person_deadDT != null)
                            {
                                Ext.getCmp('saveBskDataButton').setDisabled(true);
                            }
                            else
                            {
                               Ext.getCmp('saveBskDataButton').setDisabled(false); 
                            }
                          
                          
                            Ext.getCmp('calday').setValue(this.getValue());
                            Ext.getCmp('calday').datetime = new Date(this.getValue()).format('Y-m-d H:i');
                            var Person_Birthday = Ext.getCmp('ufa_personBskRegistryWindow').personInfo.Person_Birthday;
                            /*var pattern = /(\d{2})\.(\d{2})\.(\d{4})/;
                            var Person_Birthday = new Date(Person_Birthday.replace(pattern,'$3-$2-$1'));  */                          

                            
                            var diff = Math.ceil((new Date().getTime()-this.getValue().getTime())/(1000*60*60*24))-1;

                            var answerAge = 0;
                            
                            switch(Ext.getCmp('ufa_personBskRegistryWindow').MorbusType_id){
                                case 84 :
                                    answerAge = 25;
                                    break;
                                case 88 :
                                    answerAge = 174;
                                    break;                                    
                                case 89 :
                                    answerAge = 206;
                                    break;                            
                            }
                            if(this.getValue() > new Date){
                                form.showMsg('Недопустимо указывать дату позднее текущей!'); 
                                this.setValue(new Date());
                                
                                //Корректировка возраста пациента на дату заполнения анкеты 
                                /*var age = new Date().getFullYear() - Person_Birthday.getFullYear();
                                var m = new Date().getMonth() - Person_Birthday.getMonth();
                                      
                                if (m < 0 || (m === 0 && this.getValue().getDate() < Person_Birthday.getDate())) {
                                    age--;
                                } */                           
                                var age =  Ext.getCmp('ufa_personBskRegistryWindow').getAge(Person_Birthday, new Date());
                                Ext.getCmp('Answer_'+answerAge).setValue(age);  
                                return;                                  
                            }
                            else if(diff>30){
                                form.showMsg('Дата проведения анкетирования не может быть ранее 30 дней от текущей даты. Пожалуйста, проверьте указанную дату анкетирования.'); 
                                this.setValue(new Date());
                                return;
                            }
                            else{
                                //Корректировка возраста пациента на дату заполнения анкеты 
                                /*var age = this.getValue().getFullYear() - Person_Birthday.getFullYear();
    
                                var m = this.getValue().getMonth() - Person_Birthday.getMonth();
                                      
                                if (m < 0 || (m === 0 && this.getValue().getDate() < Person_Birthday.getDate())) {
                                    age--;
                                }  */
                                var age =  Ext.getCmp('ufa_personBskRegistryWindow').getAge(Person_Birthday, this.getValue());
                                
                                Ext.getCmp('Answer_'+answerAge).setValue(age);                                
                            }
                        },
                        'blur' : function(){

                        }
                        /**
                        'blur': function(){
                            this.style = 'border: 2px solid red!important';  
                  
                            console.log('BLUR')                                     
                        }
                        */
					}                                   
        		});                                
                
                Ext.getCmp('information').add(
                            new Ext.Panel({
                                 title: 'Дата проведения',
                                 id : 'QuestionPanel_Date',
                                 border: false,
                                 layout: 'column', 
                                 bodyStyle: 'margin: 10px;',
                                 items : [
                                    {
                                        xtype: 'panel',
                                        border: false,
                                        layout: 'column',
                                        id : 'itemsQuestionPanel_Date',
                                        items: [TextFieldDate]
                                    }
                                 ]
                          })                
                );
                
                var BSKObject_id = form.GridObjects.getGrid().getSelectionModel().getSelected().data.BSKObject_id;
                var Person_id = Ext.getCmp('ufa_personBskRegistryWindow').Person_id;

                
                Ext.Ajax.request({
                	url: '/?c=ufa_Bsk_Register_User&m=addRegistryData',
                	params: {
                	    BSKObject_id: BSKObject_id,
                        Person_id : Person_id
                	},
                	callback: function(options, success, response) {
                         
                        if (success === true) {
                             var allQuestions =  Ext.util.JSON.decode(response.responseText);
                                    
                             var questionsForm = form.createForm();
                             questionsForm.removeAll();
                           
                             for(var k in allQuestions.groups){
                                var field = form.createField(0, {
                                    data: {
                                        html: allQuestions.groups[k].group,
                                        id : allQuestions.groups[k].id
                                    }
                                });
                                
                                questionsForm.add(field)  
                            
                                //console.log('questionsForm', questionsForm);
                                   
                                Ext.getCmp('information').add(questionsForm);       
                             }
                             
                             //собрать все id, QuestionPanel_XX - id вопросов Answer_XX - id полей формы (ответы)
                             var listIDS = [];

                            //https://redmine.swan.perm.ru/issues/90794 
                            var numberAnketQuestion = {
                                24:1,
                                25:2,
                                31:3,
                                33:5,
                                34:6,
                                35:7,
                                36:8,
                                37:9,
                                43:10,
                                44:11,
                                38:12,
                                39:12,
                                40:13,
                                41:14,
                                42:15,
                                45:16,
                                46:17,
                                47:18,
                                48:19,
                                107:20,
                                108:21,
                                109:22,
                                49:23,
                                58:24,
                                67:25,
                                59:26,
                                62:27,
                                63:28,
                                65:29
                            }							 
                             for(var k in allQuestions.questions){
                                    if(typeof allQuestions.questions[k] == 'object'){
                                        
                                        //Есть вопросы только для девочек и только для мальчиков
                                        var PersonSex_id = parseInt(Ext.getCmp('ufa_personBskRegistryWindow').personInfo.Sex_id);
                                        var QuestionSex_id = parseInt(allQuestions.questions[k].BSKObservElement_Sex_id);
                                        //console.log(PersonSex_id,'=>', QuestionSex_id, 3, PersonSex_id.inlist([QuestionSex_id,3]));
                                        //console.log('-')
                                        //console.log('!!!!!!!!!!!', QuestionSex_id);
                                        
                                        if(PersonSex_id == QuestionSex_id || QuestionSex_id == 3){
                                        
                                        //if(QuestionSex_id == 3){
                                        //    console.log('>>>', allQuestions.questions[k].BSKObservElement_name)
                                        //}
                                           
                                            //Есть вопросы для старых, есть для молодых
                                            var maxAge = (allQuestions.questions[k].BSKObservElement_maxAge == null) ? 200 : allQuestions.questions[k].BSKObservElement_maxAge;
                                            var minAge = (allQuestions.questions[k].BSKObservElement_minAge == null) ? 0 : allQuestions.questions[k].BSKObservElement_minAge;
                                            var PersonAge = Ext.getCmp('ufa_personBskRegistryWindow').personInfo.age;
                                            //console.log(minAge, PersonAge, maxAge, PersonAge>=minAge, PersonAge<maxAge);
                                            //console.log('-');
                                            
                                            if(PersonAge>=minAge && PersonAge<maxAge){
                                            
                                                //Для combobox сформировать options
                                                if(allQuestions.questions[k].BSKObservElementFormat_id == 1){
                                                    var arrayAnswer = [];
                                                    
                                                    //console.log('allQuestions.questions[k].answer', allQuestions.questions[k].answer)
                                                    
                                                    for(var j in allQuestions.questions[k].answer){
                                                        var one_answer = [j, allQuestions.questions[k].answer[j]];
                                                        
                                                        arrayAnswer.push(one_answer);
                                                    }
                                                    //многомерный массив вида [[id, value],[id, value],[id, value]]
                                                    answer = arrayAnswer;
                                                    
                                                    //console.log('answer', answer)
                                                }
                                                else{
                                                    answer = allQuestions.questions[k].answer
                                                }

                                                var index = ++Ext.getCmp('ufa_personBskRegistryWindow').PrefixQuest; 

                                                var number = numberAnketQuestion[allQuestions.questions[k].BSKObservElement_id];
                                                var noa = typeof number == 'undefined' ? '' : '<small style="color:gray">(' + number +')</small>'; 
                                                
                                                var objectData = {
                                                        BSKObservElement_id : allQuestions.questions[k].BSKObservElement_id,
                                                        html                : noa+ ' ' + allQuestions.questions[k].BSKObservElement_name,
                                                        id                  : allQuestions.questions[k].BSKObservElement_id,
                                                        group_id            : allQuestions.questions[k].BSKObservElementGroup_id,
                                                        BSKObservElementFormat_id : allQuestions.questions[k].BSKObservElementFormat_id,
                                                        answer              : answer,
                                                        stage               : allQuestions.questions[k].BSKObservElement_stage,
                                                        sex                 : allQuestions.questions[k].BSKObservElement_Sex_id,
                                                        min                 : allQuestions.questions[k].BSKObservElement_minAge,
                                                        max                 : allQuestions.questions[k].BSKObservElement_maxAge,
                                                        index               : parseInt(index)                                              
                                                    }
                                                 
                                                //console.log(allQuestions.questions[k].BSKObservElement_name); 
                                                 
                                                //console.log('objectData', objectData); 
                                                listIDS.push(objectData); 
                                                
                                                var field = form.createField(100, {
                                                    data: objectData
                                                });

                                                var question = Ext.getCmp('groupPanel_'+allQuestions.questions[k].BSKObservElementGroup_id).add(field);
												//console.log('question',question);
												
                                       }
                                    }
                                  }  
                                                    
                             } 
							 
                             Ext.getCmp('ufa_personBskRegistryWindow').listIDS = listIDS;
						
                             Ext.getCmp('information').doLayout();

                             //for(var k in listIDS){
                             //   console.log(listIDS[k].BSKObservElement_id);
                             //}
                		}
                	}
                });   
				//Получение и вставка антропометрических параметров
				var Periods = Ext.getCmp('ufa_personBskRegistryWindow').Periods[Ext.getCmp('ufa_personBskRegistryWindow').MorbusType_id]
				//console.log('Periods',Periods, 'typeof Periods', typeof Periods, 'Periods.length', Periods.length);
				if (Ext.getCmp('ufa_personBskRegistryWindow').isButtonAdd || typeof Periods == 'undefined' 
						|| (typeof Periods == 'object' && Periods.length == 0) 
						|| (typeof Periods == 'array' && Periods.length == 0)) {
					setTimeout(function() {
						Ext.getCmp('ufa_personBskRegistryWindow').getBodyMassIndexParams();	
					},200);
				}
                //Ext.getCmp('addBskDataButton').setDisabled(false);                       
            },
            createForm : function(){
                var formQuestions = new sw.Promed.FormPanel({
                    frame: false,
                    bodyBorder: false,
                    bodyStyle : 'background-color:white!important; padding:20px!important',
        			frame: false,
        			autoWidth: false,
        			autoHeight: false,
        			region: 'center',
        			items: 	[
                        {
                          xtype: 'panel',
                          html: '<p>&nbsp;</p>' ,
                          border: false 
                        }
                    ]
                });
                
                return formQuestions;
            },
            /**
             * Создание объекта combobox с единицами измерения
             */
            getComboUnits: function(id){
                var store= new Ext.data.JsonStore({
                		url:'/?c=ufa_Bsk_Register_User&m=getComboUnits',
                        autoLoad: true,
                		baseParams: {
                		  BSKObservElement_id : id
                		},				
                		fields: [
                			{name:'BSKObservUnits_id', type: 'string'},
                			{name:'BSKObservUnits_name', type: 'string'}
                		],
                        listeners : {
                            'load' : function(){
                                //console.log(this.getAt(0))
                                if(typeof Ext.getCmp('UnitCombo_'+id) != 'undefined'){
                                    if(id != 108)
                                        Ext.getCmp('UnitCombo_'+id).setValue(this.getAt(0).get('BSKObservUnits_name'));
                                    else{
                                        Ext.getCmp('UnitCombo_'+id).setValue('кг');
                                    }    
                                }
                            }
                        }
                });
                           
                var comboUnits=new Ext.form.ComboBox({
                    id: 'UnitCombo_'+id,
                    allowBlank: false,
                    disabled : Ext.getCmp('ufa_personBskRegistryWindow').elemDisabled(),
                    width: 100,
                    mode: 'local',
                	forceSelection: true,
                	store: store,
					tabIndex:-1,
                	triggerAction: 'all',			
                	editable:false,
                	displayField:'BSKObservUnits_name',
                    valueField: 'BSKObservUnits_name',
                    listeners : {
                        'render':function(){     
							
                        }
                    }                    	
                }); 
                
                //comboUnits.setValue('см');
                
                return comboUnits;               
            },
            /**
             * Метод динамического создания нужного типа field
             * @param int
             * @param object
             */
            createField : function(format, params){
                
                //console.log('PARAMS', params);
                //1 combobox (1 вариант из нескольких)
                //2 checkbox (несколько вариантов из нескольких)
                //3 textfield (вводится в ручную)
                //4 datefield (поле для ввода даты)
                //5 formula (автоматический расчёт)
                //6 fulltext (до 500 символов)
                //7 db (автоматически из Promed)

  

                switch(parseInt(format)){
                    case 0: 
                          var GroupPanel = new Ext.Panel({
                                 id : 'groupPanel_'+params.data.id,
                                 title :  '<span style="font-size:13px">' + params.data.html + '</span>' ,
                                 collapsible: true,
								 tabIndex:-1,
                                 //collapsed: false,
                                 border: false,
                                 listeners: {
                                    
                                    'render': function(panel) {
                                            panel.header.on('click', function() {
                                              if (panel.collapsed) {
                                                panel.expand();
                                              }
                                              else {
                                                panel.collapse();
                                              }
                                            });
                                    }
                                   
                                 }                                 
                          })
                          
                          return GroupPanel;
                    break;

                    case 100: 
                          //var color = params.data.id.inlist([31,32,33,34,35,36,37,39,40,41,42,43,44,45,46,47,48,49,58,59,60,61,62,63,65,67,78,79,111,112,113,114]) ? '#B6EC9A' : 'white';
                          //var setPrefQuest = (typeof PrefixQuest.elem != 'undefined') ? PrefixQuest.elem : '<img src="/img/icons/msjobs_unknown.png" style="margin-right:5px">';
                          
                          var bgcolor = Ext.getCmp('ufa_personBskRegistryWindow').getBgColor(params.data.id);   

                          var textColor = parseInt(params.data.id).inlist(Ext.getCmp('ufa_personBskRegistryWindow').idInDb) ? '#1B769C' : 'black';  
                          var style = 'style="cursor: important; font-size:12px; background-color:'+bgcolor+'; color: '+textColor+'!important;"';
                          
                          
                          var QuestionPanel = new Ext.Panel({
                                 baseCls: 'x-plain',
                                 id : 'QuestionPanel_'+params.data.id,
                                 html : '<table id="mark'+params.data.id+'" class="index'+params.data.index+'" style="margin-bottom:10px!important;  background-color:'+bgcolor+'"><tr><td style="background-color:'+bgcolor+'" valign="top" > <b style="color:#B4B4B4">'+params.data.index+'.</b>&nbsp;&nbsp;</td>'
                                       +'<td '+style+'>' + params.data.html + '</td></tr></table>',
                                 border: false,
                                 layout: 'column', 
								 tabIndex:-1,
                                 bodyStyle: 'padding: 10px;background-color:'+bgcolor,
                                 items : [
                                    {
                                        xtype: 'panel',
                                        border: false,
                                        layout: 'column',
                                        id : 'itemsQuestionPanel_'+params.data.id,
                                        items: []
                                    }
                                 ]
                          })
                          
                          this.addAnswer(params);
                          
                          return QuestionPanel;
                    break;   
                    
                                     
                                        
                    case 1: 
                            //Прорисовка дозировки препоратов в подгруппу "Лекарственное лечение"
                            switch(parseInt(params.data.id)){
                                case 113: 
                                case 114: 
                                    var mg = [1,2,4,10,15,20,30,40,80];
                                    for(var i = 0; i < mg.length; i+=1) params.data.answer.push([i+100*999,mg[i] + ' мг.']);
                                break;
                                //case 114: 
                                case 118:
                                case 119: 
                                    for(var i = 5; i < 11; i+=5) params.data.answer.push([i+100*999,i + ' мг.']);           
                                break;   
                                case 123:
                                case 124:
                                    for(var i = 100; i < 2001; i+=100){ 
                                        if(i==100){ 
                                            params.data.answer.push([i+100*999,'145 мг.']); 
                                            params.data.answer.push([i+100*999,'146 мг.']); 
                                        }    
                                        params.data.answer.push([i+100*999,i + ' мг.']); 
                                    }    
                                break; 
                                case 128: 
                                case 129: 
                                    for(var i = 1; i < 31; i+=1) params.data.answer.push([i+100*999,i + ' мг.']);           
                                break;    
                                case 133: 
                                case 136: 
                                    for(var i = 500; i < 3001; i+=50) params.data.answer.push([i+100*999,i + ' мг.']);           
                                break;      
                                                                                                             
                                //return не использовать.
                            }                          
                  
							
                            var Store = new Ext.data.SimpleStore({
            					fields:
            					[
            						{name: 'id', type: 'int'},
            						{name: 'name', type: 'string'}
            					],
            					data: params.data.answer
                            });   
                            
                            //console.log('combo: ', Ext.getCmp('ufa_personBskRegistryWindow').elemDisabled())
                    		var ComboBox = new Ext.form.ComboBox({
                    			id : 'Answer_'+params.data.id,
                                allowBlank: false,
								blankText:'',
								invalidText:'',
								invalidClass:'',
                                hideTrigger: true,
                                labelSeparator : '',
                                labelWidth : '10px',
                    			store: Store,
                                disabled : Ext.getCmp('ufa_personBskRegistryWindow').elemDisabled(),
                                //width: 800,
                                //style : 'border:0px!important;',
								//focusClass: 'background-color:#CCCCCC!important',
                    			displayField:'name',
                                editable : false,
								tabIndex:-1,
                                width: 'auto',
                                //listWidth: 'auto', 
                                triggerAction : 'all',
                    			mode: 'local',
                                listeners : {
									'specialkey': function( field, e ) {
										if (e.getKey() == e.TAB /*&& Ext.getCmp('ufa_personBskRegistryWindow').collapsedCombo*/) {											
											Ext.getCmp('ufa_personBskRegistryWindow').switchFieldOnTab(params.data.id, false);
										}
										else if (e.getKey() == e.ENTER) {										
											setTimeout(function() {
												Ext.getCmp('Answer_'+params.data.id).expand(); //без setTimeout expand() повторно не срабатывал
											},100);
										}
										else if (e.getKey() == e.LEFT || e.getKey() == e.RIGHT) {
											Ext.getCmp('Answer_'+ params.data.id).focus(false,100);
										}
									},
									'collapse' : function() {
										//Ext.getCmp('ufa_personBskRegistryWindow').collapsedCombo = true;
										/*Ext.getCmp('ufa_personBskRegistryWindow').switchFieldOnTab(params.data.id, false);*/
										
									},
									'expand' : function() {
										//Ext.getCmp('ufa_personBskRegistryWindow').collapsedCombo = false;				
										Ext.getCmp('ufa_personBskRegistryWindow').combExp = params.data.id;
									},
									
									'focus' :{scope:this, fn:function(field) {
											//Реализовано для выделения пустых полей (при недопущении пустых значений) при переходе по Tab
											Ext.getCmp('Answer_'+params.data.id).val = Ext.getCmp('Answer_'+params.data.id).getValue();
											Ext.getCmp('Answer_'+params.data.id).allowBlank = true;
											Ext.getCmp('Answer_'+params.data.id).getEl().setStyle('background-color','#CCCCCC');
											Ext.getCmp('Answer_'+params.data.id).reset();
											Ext.getCmp('Answer_'+params.data.id).setValue(Ext.getCmp('Answer_'+params.data.id).val);
											Ext.getCmp('Answer_'+params.data.id).allowBlank = false;
									   /*if(!field.isExpanded()){
										   field.onTriggerClick();
									   }*/
									 },buffer:100},  
									'blur' : function() {
										Ext.getCmp('Answer_'+params.data.id).getEl().setStyle('background-color','');
									},
                                    'change': function(){
                                        var id = parseInt(params.data.id);
                                        if(Ext.getCmp('GridObjectsUser').getGrid().store.data.length > 0 && Ext.getCmp('GridObjectsUser').getGrid().store.data.items[0].json.Person_deadDT != null)
                                        {
                                          Ext.getCmp('saveBskDataButton').setDisabled(true);
                                        }
                                        else
                                        {
                                            Ext.getCmp('saveBskDataButton').setDisabled(false);
                                        }
                                        this.getEl().setStyle('border','0px');
                                        this.getEl().setStyle('background-image','url("")');
                                        this.getEl().setStyle('border-bottom','1px dashed gray');
                                        //this.getEl().setStyle('color','#7A7A7A');
                                        this.getEl().setStyle('color','black');
                                        //this.getEl().setStyle('font-weight','bold');
                                        //Подсветка для данных из БД (отключено)
                                        //this.getEl().setStyle('color', id.inlist(Ext.getCmp('ufa_personBskRegistryWindow').idInDb) ? '#0B7EAE' : '#7A7A7A');
                                        this.getEl().setStyle('padding','2px');
                                        
                                        //Управление группой комбобоксов
                                        //1 - принято накануне
                                        //2 - прописано на текущем
                                        //3 - дозировка накануне
                                        //4 - дозировка на текужем
                                        //5 - причина отмены
                                        /* e.g.
                                        var params = {
                                            ids : {
                                                1: 'Answer_12',
                                                2: 'Answer_13',
                                                3: 'Answer_14',
                                                4: 'Answer_15',
                                                5: 'Answer_16'
                                            }
                                        } 
                                        */ 
                                       
                                        switch(id){
                                            //Статины
                                            case 111:
                                            case 112:
                                            case 113:
                                            case 114:
                                            case 115: 
                                                var paramsData = {
                                                    ids : {
                                                        1:'Answer_111',
                                                        2:'Answer_112',
                                                        3:'Answer_113',
                                                        4:'Answer_114',
                                                        5:'Answer_115'
                                                    }
                                                }   
                                            break;
                                            //Эзетемиб 116,117,118,119,120,
                                            case 116:
                                            case 117:
                                            case 118:
                                            case 119:
                                            case 120: 
                                                var paramsData = {
                                                    ids : {
                                                        1:'Answer_116',
                                                        2:'Answer_117',
                                                        3:'Answer_118',
                                                        4:'Answer_119',
                                                        5:'Answer_120'
                                                    }
                                                }   
                                            break;
                                            //Фибраты  121,122,123,124,125,
                                            case 121:
                                            case 122:
                                            case 123:
                                            case 124:
                                            case 125: 
                                                var paramsData = {
                                                    ids : {
                                                        1:'Answer_121',
                                                        2:'Answer_122',
                                                        3:'Answer_123',
                                                        4:'Answer_124',
                                                        5:'Answer_125'
                                                    }
                                                }   
                                            break;                                            
                                            //секверстанты 126,127,128,129,130,
                                            case 126:
                                            case 127:
                                            case 128:
                                            case 129:
                                            case 130: 
                                                var paramsData = {
                                                    ids : {
                                                        1:'Answer_126',
                                                        2:'Answer_127',
                                                        3:'Answer_128',
                                                        4:'Answer_129',
                                                        5:'Answer_130'
                                                    }
                                                }   
                                            break;                                               
                                            //никотиновая кислота 
                                            case 131:
                                            case 132:
                                            case 133:
                                            case 136:
                                            case 137: 
                                                var paramsData = {
                                                    ids : {
                                                        1:'Answer_131',
                                                        2:'Answer_132',
                                                        3:'Answer_133',
                                                        4:'Answer_136',
                                                        5:'Answer_137'
                                                    }
                                                } 
											break;	
                                            default :  paramsData = {}    
                                            break;                                            
                                        }   
                                               
                                        Ext.getCmp('ufa_personBskRegistryWindow').manageDrugSelect(paramsData, this);
                                        
                                        //управление лекарственным лечением других ПН (не скрининг !=84)
                                        Ext.getCmp('ufa_personBskRegistryWindow').manageLLquestions(id);

                                    },
                                    'render': function(){
										 
                                        var id = parseInt(params.data.id);
                                        if(Ext.getCmp('GridObjectsUser').getGrid().store.data.length > 0 && Ext.getCmp('GridObjectsUser').getGrid().store.data.items[0].json.Person_deadDT != null)
                                        {
                                          Ext.getCmp('saveBskDataButton').setDisabled(true);
                                        }
                                        else
                                        {
                                            Ext.getCmp('saveBskDataButton').setDisabled(Ext.getCmp('ufa_personBskRegistryWindow').elemDisabled());
                                        }
										
                                        this.getEl().setStyle('border','0px');
                                        this.getEl().setStyle('background-image','url("")');
                                        this.getEl().setStyle('border-bottom','1px dashed gray');
                                        //this.getEl().setStyle('color','#7A7A7A');
                                        this.getEl().setStyle('color','black');
                                        this.getEl().setStyle('font-weight','bold');
                                        
                                        //Подсветка для данных из БД (отключено)
                                        //this.getEl().setStyle('color', id.inlist(Ext.getCmp('ufa_personBskRegistryWindow').idInDb) ? '#0B7EAE' : '#7A7A7A');
                                        this.getEl().setStyle('padding','2px');
                                        this.setDisabled(Ext.getCmp('ufa_personBskRegistryWindow').elemDisabled());
                                        
                                        //this.triggerEl.hide()
                    
                    
                                       //Причина смены лекарственных препаратов
                                        var disabled = parseInt(params.data.id).inlist([115,120,125,130,137]);
                                          
                                        
                                        if(disabled === true){
                                            //console.log(params.data.id, disabled) 
                                            this.setDisabled(true);
                                        }                                                                              
                                    }                                    
                                },
                                //Подгон размера комбо под список
                                resizeToFitContent: function() {
                                	if (!this.elMetrics)
                                	{
                                		this.elMetrics = Ext.util.TextMetrics.createInstance(this.getEl());
                                	}
                                	var m = this.elMetrics, width = 0, el = this.el, s = this.getSize();
                                	this.store.each(function (r) {
                                		var text = r.get(this.displayField);
                  		                
                                        //Ширина комбо для ОКС
                                        if(this.el.id.inlist(['Answer_300', 'Answer_275', 'Answer_273', 'Answer_301', 'Answer_302',  'Answer_304', 'Answer_306', 'Answer_307'])){
                                            width = Math.max(width, m.getWidth(text))*18;
                                        }
                                        else{
                                            width = Math.max(width, m.getWidth(text));
                                        }
										if (width < 100 && this.width < 100) {
											this.listWidth = 100;
										}
                                	}, this);
                                	if (el) {
                                		width += el.getBorderWidth('lr');
                                		width += el.getPadding('lr');
                                	}
                                	if (this.trigger) {
                                		width += this.trigger.getWidth();
                                	}
                                	s.width = width;
                                	this.setSize(s);
                                	this.store.on({
                                		'datachange': this.resizeToFitContent,
                                		'add': this.resizeToFitContent,
                                		'remove': this.resizeToFitContent,
                                		'load': this.resizeToFitContent,
                                		'update': this.resizeToFitContent,
                                		buffer: 10,
                                		scope: this
                                	});
                                    
                                } 
                    		}); 
                            
                            ComboBox.on('render', ComboBox.resizeToFitContent, ComboBox);
                            ComboBox.on('blur',
                                function (){
                                    this.style = "border:0px;"
                                    //console.log(this);
                                }
                            );
                            
                            ComboBox.style = "font-size:30px!important";
                            
                            ComboBox.on('render',
                                function(){
                                     var id = parseInt(params.data.id);
                                     //console.log(parseInt(params.data.id)); 
                                     //this.style = "font-size:30px!important";
                                    
                                }
                            );
                            //Калькулятор Grace
                            ComboBox.on('expand',
                                function(){
                                     var id = parseInt(params.data.id);
                                     //console.log(parseInt(params.data.id)); 
                                     var sisLeftHand;
                                     var sisRightHand;
                                     var sisHand;
                                     if (id == 269) {
                                         if (typeof Ext.getCmp('Answer_212') != 'undefined' &&  typeof Ext.getCmp('Answer_213') != 'undefined') {
                                             if (Ext.getCmp('Answer_212').getValue()>0 && Ext.getCmp('Answer_213').getValue()>0) {
                                                sisLeftHand = Number(Ext.getCmp('Answer_212').getValue());
                                                sisRightHand = Number(Ext.getCmp('Answer_213').getValue());
                                                sisHand = Math.ceil((sisLeftHand + sisRightHand)/2);
                                                //console.log(sisHand);
                                            }
                                        }                                  
                                         var paramsWindow = {
                                            age: Ext.getCmp('ufa_personBskRegistryWindow').personInfo.age,
                                            sis: sisHand
                                         }
                                         getWnd('ufa_swGraceCalculator').show(paramsWindow);
                                         
                                     }
                                    
                                }
                            );                    
                    
                            
                            return  ComboBox; 
                    
                    break;
                    
                    case 3: 
                    case 5:    
                                var ids = {
                                    //id текстовых полей, длина которых не должна превышать 3 символа
                                    3: [
                                        //Легочная гипертензия
                                        159, 160, 163, 164, 165, 166, 167, 168, 169, 170,                                 
                                    ], 
                                    //id текстовых полей, длина которых не должна превышать 4 символа
                                    4: [
                                        //Легочная гипертензия
                                        156, 157, 158,
                                        //Артериальная гепиртензия
                                        235, 236, 237, 238, 239, 240, 242, 244, 245,                                 
                                        //Ишемическая болезнь сердца
                                        392, 393, 394, 395, 396, 397                                    
                                    ],
                                    //id текстовых полей, длина которых не должна превышать 5 символов
                                    5: [
                                        //Скрининг 84
                                        50, 51, 54, 55, 88, 89, 90, 91, 92, 93, 94, 95, 96, 97, 98, 99, 100, 101, 102, 104, 106, 107, 108, 109,
                                        //Легочная гипертензия
                                        142, 143, 144, 145, 146, 147, 148, 149, 152, 153, 154, 155, 186, 192, 194, 196, 198, 200, 202, 204,
                                        //Артериальная гепиртензия
                                        208, 209, 210, 211, 212, 213, 214, 215, 216, 224, 225, 226, 227, 228, 229, 230, 231, 232, 233, 234, 
                                        260, 261, 262, 263, 264, 265, 266, 267, 268,
                                        //Ишемическая болезнь сердца
                                        366, 368, 370, 372, 374, 376, 378, 380, 382
                                    ], 
                                    //id текстовых полей, длина которых не должна превышать 6 символов
                                    6: [
                                        //Артериальная гепиртензия
                                        247, 248, 249
                                    ]                                     
                                }                        
                               
                                var plugins;
                                
                                switch(Number(params.data.id)){
                                    //Артериальная гипертензия
                                    //Лаборатороная диагностика
                                    case 224:
                                    case 225:
                                    case 226:
                                    case 227:
                                    case 228:
                                    case 229:
                                    case 230:
                                    case 231: plugins = [new Ext.ux.InputTextMask('99.99', false)]; 
                                    break; 
                                    case 232: plugins = [new Ext.ux.InputTextMask('999.9', false)];
                                    break;
                                    case 233:
                                    case 234: plugins = [new Ext.ux.InputTextMask('9999', false)];                                       
                                    break;    
                                   
                                    //Ишемическая болезнь сердца
                                    //Лаборатороная диагностика
                                    case 333:
                                    case 334:
                                    case 335:
                                    case 336:
                                    case 337:
                                    case 338:
                                    case 339:
                                    case 340:
                                    case 341:
                                    case 385: plugins = [new Ext.ux.InputTextMask('99.99', false)];
                                    break;
                                    //Коронароангиография
                                    case 350:
                                    case 351:
                                    case 352:
                                    case 353:
                                    case 354:
                                    case 355:
                                    case 356:
                                    case 357:
                                    case 358:
                                    case 359:
                                    case 360:
                                    case 361:
                                    case 362:
                                    case 363:
                                    case 364: plugins = [new Ext.ux.InputTextMask('X[1-9]X9', false)];
                                    break;
									//Эхокардиография
                                    case 392:
									case 393:
									case 394:
									case 395:
									case 396:
									case 397: plugins = [new Ext.ux.InputTextMask('9.9', false)];
                                    break;									
                                    default: plugins = ''; 
                                    break;    
                                }
                                
                    		var TextField = new Ext.form.TextField({
                    		id : 'Answer_'+params.data.id,
                                labelSeparator : '',
                                plugins: plugins,
                                maskRe: /[\d.\/]/,
                                type: 'TextField',
								tabIndex:-1,
								focusClass: 'background: #ccc important!',
                                enableKeyEvents : true,
                                disabled : Ext.getCmp('ufa_personBskRegistryWindow').elemDisabled(),
                                labelWidth : '10px',
                                width: (typeof params.data.width == 'undefinted') ? 100 : params.data.width,
                                value : (typeof params.data.value == 'undefined') ? '' : params.data.value,
            					listeners: {
                                    'specialkey':  function(field, e){
                                        //Индекс массы тела
                                        //Скрининг
                                        if( (parseInt(params.data.id) == 107) || (parseInt(params.data.id) == 108) ){
                                            if (e.getKey() == e.ENTER) {
                                                  Ext.getCmp('ufa_personBskRegistryWindow').getIndex(parseInt(params.data.id), 110);
                                            }
                                        }
                                        //Лёгочная гипертензия
                                        else if( (parseInt(params.data.id) == 142) || (parseInt(params.data.id) == 143) ){
                                            if (e.getKey() == e.ENTER) {
                                                  Ext.getCmp('ufa_personBskRegistryWindow').getIndex(parseInt(params.data.id), 172);
                                            }
                                        }     
                                        //Артеириальная гипертензия
                                        else if( (parseInt(params.data.id) == 208) || (parseInt(params.data.id) == 209) ){
                                            if (e.getKey() == e.ENTER) {
                                                  Ext.getCmp('ufa_personBskRegistryWindow').getIndex(parseInt(params.data.id), 210);
                                            }
                                        }      
                                        //Ишемическая болезнь сердца
                                        else if( (parseInt(params.data.id) == 318) || (parseInt(params.data.id) == 319) ){
                                            if (e.getKey() == e.ENTER) {
                                                  Ext.getCmp('ufa_personBskRegistryWindow').getIndex(parseInt(params.data.id), 320);
                                            }
                                        } 
										
										if (e.getKey() == e.TAB) {
											Ext.getCmp('ufa_personBskRegistryWindow').switchFieldOnTab(params.data.id, false);
										}										
										
										
                                    },
                                    'change' : function(){
                                        if(Ext.getCmp('GridObjectsUser').getGrid().store.data.length > 0 && Ext.getCmp('GridObjectsUser').getGrid().store.data.items[0].json.Person_deadDT != null)
                                        {
                                          Ext.getCmp('saveBskDataButton').setDisabled(true);
                                        }
                                        else
                                        {
                                           Ext.getCmp('saveBskDataButton').setDisabled(false);  
                                        }
                                        this.getEl().setStyle('font-weight','bold');
                                    },
                                    'focus': function() {
										//this.style = 'background: #ccc; border:4px solid black !important';
                                        //console.log('this.plugins',this.plugins);
                                        if (this.plugins != '') {
                                            var mask = String(this.plugins[0].viewMask);


                                            var str = this.getValue()
                                            var pointpos = mask.search(/\./);
                                            //console.log('points',pointpos, this.plugins);
                                            if (pointpos != -1) {
                                                var mas = str.split(/\./);
                                                if (mas[1] == undefined) mas[1] = '_';
                                                //console.log('mas',mas);
                                                //console.log('lmas',mas[0].length, 'rmas', mas[1].length);
                                                var masm = mask.split(/\./);
                                                //console.log('lmasm',masm[0].length, 'rmasm', masm[1].length);
                                                while  (masm[0].length > mas[0].length) {
                                                    mas[0] = '_'+ mas[0];
                                                }
                                                while  (masm[1].length > mas[1].length) {
                                                    mas[1] = mas[1] + '_';                                                
                                                }  
                                                //console.log('!!!!!!!!!!!!',mas[0]+'.'+mas[1]);
                                                this.setValue(mas[0]+'.'+mas[1]);
                                            } 
                                            else {
                                                if (str.length < mask.length) {
                                                    while (str.length < mask.length) {
                                                       str = str + '_'; 
                                                    }
                                                    this.setValue(str);
                                                }
                                                else {
                                                    this.setValue(str);
                                                }
                                            }
                                            this.selectText(0,0);
                                            /*if (this.pValue != '' && this.pValue != undefined ) {
                                                this.setValue(this.pValue);    
                                            }*/

                                        }

                                    },
                                    'blur': function(){
                                        this.style = 'border: 2px solid red!important';                                    
                                        
										if (this.plugins != '') {
											this.setValue(this.getValue().replace(/_/g, '').replace(/\.$/,''));
											//console.log('>>>>>>',this.getValue());											
										}                                  

                                        var form = Ext.getCmp('ufa_personBskRegistryWindow');
                                        
                                        //Определение полей роста и веса, которые используются для ррасчёта ИМТ
                                        var fields = [];
                                        var imtField_id = 0;
                                        
                                        switch(Ext.getCmp('ufa_personBskRegistryWindow').MorbusType_id){
                                            //Скрининг
                                            case 84: 
                                                fields = [107,108];
                                                imtField_id = 110;
                                            break;    
                                            //Лёгочная гипертензия
                                            case 88: 
                                                fields = [142,143];
                                                imtField_id = 172;
                                            break;  
                                            //Артериальная гипертензия
                                            case 89: 
                                                fields = [208,209];
                                                imtField_id = 210;
                                            break;
                                            //Ишемическая болезнь сердца
                                            case 50: 
                                                fields = [318,319];
                                                imtField_id = 320;
                                            break;                                           
                                        }

                                        if(parseInt(params.data.id).inlist(fields)){
                                            //console.log('in apple!');
                                            //Если уже введены значения и роста и массы
                                            
                                            //console.log('Рост',Ext.getCmp('Answer_'+fields[0]));
                                            //console.log('Вес',Ext.getCmp('Answer_'+fields[1]));
                                            //console.log('IMT', Ext.getCmp('Answer_'+imtField_id));
                                            
                                            
                                            if((typeof Ext.getCmp('Answer_'+fields[0]) != 'undefined' && typeof Ext.getCmp('Answer_'+fields[1])  != 'undefined')){
                                                //console.log(this)
                                                
                                                //если поле для ИМТ создано - то уже не имеет значение
                                                if(typeof Ext.getCmp('Answer_'+imtField_id)  == 'undefined'){                                                
                                                    for(var k in form.listIDS){
                                                        //Находим дозировка по ID для данного ЛС
                                                        if(form.listIDS[k].BSKObservElement_id == imtField_id){
                                                            Ext.getCmp('QuestionPanel_' + imtField_id).removeAll();
                                                            //Нужно создать поле автоматически - если Не принимает данное ЛС
                                                            var param = {}
                                                                param.data = {}
                                                                param.data = form.listIDS[k];
                                                                param.data.id = imtField_id;
                                                               
                                                            var field = form.createField(3, param);

                                                            field.style = 'border:0px;background-image:url("");border-bottom:1px dashed gray; font-weight:bold';
                                                            
                                                            Ext.getCmp('QuestionPanel_' + imtField_id).add(field);                                                                        
                                                            Ext.getCmp('QuestionPanel_' + imtField_id).doLayout();   
                                                            
                                                            //Подсчёт ИМТ
                                                            fields.push(imtField_id);            
                                                            Ext.getCmp('ufa_personBskRegistryWindow').getIMT(this,fields);                
                                                            field.setDisabled(true);
                                                        }
                                                    }   
                                                }
                                            }
                                        
                                        }                                      
                                    },
                                    'render': function(){
                                        this.getEl().setStyle('color','black');
                                        this.getEl().setStyle('font-weight','bold');
                                        //Ограничение длины полей до 5 символов
                                        if (parseInt(params.data.id).inlist(ids[5])) {
                                            this.getEl().dom.maxLength = 5;
                                        }
                                        else if  (parseInt(params.data.id).inlist(ids[6])) {
                                            this.getEl().dom.maxLength = 6;
                                        }
                                        else if  (parseInt(params.data.id).inlist(ids[4])) {
                                            this.getEl().dom.maxLength = 4;
                                        }
                                        else if  (parseInt(params.data.id).inlist(ids[3])) {
                                            this.getEl().dom.maxLength = 3;
                                        };                                        
                                        
                                        //if(Ext.getCmp('ufa_personBskRegistryWindow').robot === false){
                                            //this.focus(true);
                                        //}
                                        
                                    },
                                    //пересчёт ИМТ на лету
                                    'keyup' : function(){
                                        //Определение полей роста и веса, которые используются для ррасчёта ИМТ
                                        var fields = [];
                                        var imtField_id = 0;
                                        
                                        switch(Ext.getCmp('ufa_personBskRegistryWindow').MorbusType_id){
                                            //Скрининг
                                            case 84: 
                                                fields = [107,108];
                                                imtField_id = 110;
                                            break;    
                                            //Лёгочная гипертензия
                                            case 88: 
                                                fields = [142,143];
                                                imtField_id = 172;
                                            break;    
                                            //Артериальная гипертензия
                                            case 89: 
                                                fields = [208,209];
                                                imtField_id = 210;
                                            break;      
                                            case 50: 
                                                fields = [318,319];
                                                imtField_id = 320;
                                            break;                                           
                                        }          
                                        //Пересчёт ИМТ                              
                                        fields.push(imtField_id);
                                        Ext.getCmp('ufa_personBskRegistryWindow').getIMT(this,fields);                                        
                                    }
            					}                                   
                    		}); 
                            
                            
                            
                            return  TextField; 
                    
                    break;   
                    
                    default : return; break;                    
                    

                }
            },
            /**
             * метод для динамического отображения приглашения установки ответа
             * @param string - id, отображение field с вариантами ответов
             * @param bool - поставить предыдущий вариант ответа, или вывести предложение для ввода ответа
             */
            addAnswer: function(params){
                //console.log('>>', params);
                
                /**
                  Проверка активности кнопок переключения между анкетами
                 */
                var ActivePrevButton = Ext.getCmp('prevButton').disabled;
                var ActiveNextButton = Ext.getCmp('nextButton').disabled;

                /**
                  true = значит анкета новая
                  false = анкета грузиться из бд
                */
                var ActiveRecord = ActivePrevButton && ActiveNextButton;
                //Получить данные из БД по пациенту 
                if(params.data.BSKObservElementFormat_id == 7){

                    switch(parseInt(params.data.id)){
                        // Пол
                        case 24 :  //Скрининг
                        case 173 : //Лёгочная гипертензия
                        case 205 : //Артериальная гипертензия
                        case 315 : //ИБС   
                            var field = this.createField(3, {
                                data: params.data,
                            });  
                            
                            field.setValue(this.personInfo.Sex_Name);
                            field.disabled = true;  
                            
                        break;
                                                
                        // Возраст
                        case 25 :  //Скрининг
                        case 174 : //Лёгочная гипертензия 
                        case 206 : //Артериальная гипертензия  
                        case 316 : //ИБС     
                            var field = this.createField(3, {
                                data: params.data
                            });                          

                            field.setValue(this.personInfo.Person_Age); 
                            field.disabled = true; 
                        break;
                        
                        // Инвалидность
                        case 26:   //Скрининг  
                        case 175 : //Лёгочная гипертензия   
                        case 207 : //Артериальная гипертензия   
                        case 317 : //ИБС        
                            //params.data.width = 800;
							params.data.answer = [[0,'Да'], [1,'Нет']];
                            var field = this.createField(1, {
                                data: params.data
                            });                                      
                            this.getValueOnDb('/?c=ufa_Bsk_Register_User&m=havePrivilege', field);
                            field.disabled = true;
                            //return true;
                        break;    
                    
                        default : ''; break;                                         
                    }
                    
                    //console.log(field);      
                    if(field){
                        //field.style = "border:none;background:url(''); text-decoration:underline";
                        field.style = 'border:0px;background-image:url("");border-bottom:1px dashed gray';
                        //Ext.getCmp('Answer_'+params.data.id).setDisabled(true);
                        Ext.getCmp('QuestionPanel_'+params.data.id).add(field);                                                                        
                        Ext.getCmp('QuestionPanel_'+params.data.id).doLayout();
                        //Ext.getCmp('Answer_34').setDisabled(false);    
                    }
                    return;
                }
                else if(params.data.BSKObservElementFormat_id == 1){
                
                    //params.data.answer = [[0,'Да'], [1,'Нет']];
                    //console.log('params.data.id', typeof parseInt(params.data.id));
                    //console.log('ДИАБЕТ2', params.data);
                    //console.log('params.data.answer ', params.data.id, params.data.answer )
                    if(parseInt(params.data.id).inlist(Ext.getCmp('ufa_personBskRegistryWindow').idInDb)){
                        params.data.answer = [[0,'Да'], [1,'Нет']];
                       //console.log('params.data.id', params.data.id, params) 
                    }                
    
                    switch(parseInt(params.data.id)){
                        // Диабет
                        case 34: 
                        case 329:       
                            //params.data.width = 800;
                            var field = this.createField(1, {
                                data: params.data
                            });                      
                            this.getValueOnDb('/?c=ufa_Bsk_Register_User&m=checkDiabetes', field);
                            
                        break;    

                        // Почки
                        case 35:
                        case 332:     
                            params.data.width = 800;
                            var field = this.createField(1, {
                                data: params.data
                            });                         
                            this.getValueOnDb('/?c=ufa_Bsk_Register_User&m=checkDisease', field);
                            
                            //return true;
                        break;                            

                        // Гипофункция щитовидной железы
                        case 36:
                            params.data.width = 800;
                            var field = this.createField(1, {
                                data: params.data
                            });                         
                            this.getValueOnDb('/?c=ufa_Bsk_Register_User&m=checkGypofunction', field);
                            
                            //return true;
                        break;   
                        
                        // Аутоиммунные заболевания
                        case 37:
                            params.data.width = 800;
                            var field = this.createField(1, {
                                data: params.data
                            });                         
                            this.getValueOnDb('/?c=ufa_Bsk_Register_User&m=checkAutoimmune', field);
                            
                            //return true;
                        break;                           

                        // Неалкогольная жировая болезнь печени
                        case 43:
                            params.data.width = 800;
                            var field = this.createField(1, {
                                data: params.data
                            });                         
                            this.getValueOnDb('/?c=ufa_Bsk_Register_User&m=checkFattyLiver', field);
                            
                            //return true;
                        break;    
                        
                        // Наличие у пациента камней в желчном пузыре
                        case 44:
                            params.data.width = 800;
                            var field = this.createField(1, {
                                data: params.data
                            });                         
                            this.getValueOnDb('/?c=ufa_Bsk_Register_User&m=checkStonesInBubble', field);
                            
                            //return true;
                        break;                            

                        // Синдром обструктивного апное сна (храп)
                        case 58:
                            params.data.width = 800;
                            var field = this.createField(1, {
                                data: params.data
                            });                         
                            this.getValueOnDb('/?c=ufa_Bsk_Register_User&m=checkSnoring', field);
                            
                            //return true;
                        break; 
                        
                        // ухудшение слуха
                        case 59:
                            params.data.width = 800;
                            var field = this.createField(1, {
                                data: params.data
                            });                         
                            this.getValueOnDb('/?c=ufa_Bsk_Register_User&m=checkBadHear', field);
                            
                            //return true;
                        break;   
                        
                        // Эректильная дисфункция
                        case 60:
                            params.data.width = 800;
                            var field = this.createField(1, {
                                data: params.data
                            });                         
                            this.getValueOnDb('/?c=ufa_Bsk_Register_User&m=checkDysfunction', field);
                            
                            //return true;
                        break; 
                        
                        // Поликистоз яичников
                        case 61:
                            params.data.width = 800;
                            var field = this.createField(1, {
                                data: params.data
                            });                         
                            this.getValueOnDb('/?c=ufa_Bsk_Register_User&m=checkPolycystic', field);
                            
                            //return true;
                        break;   

                        // подагра
                        case 67:
                            params.data.width = 800;
                            var field = this.createField(1, {
                                data: params.data
                            });                         
                            this.getValueOnDb('/?c=ufa_Bsk_Register_User&m=checkGout', field);
                            
                            //return true;
                        break;   
                        
                        // Липодистрофия
                        case 78:
                            params.data.width = 800;
                            var field = this.createField(1, {
                                data: params.data
                            });                         
                            this.getValueOnDb('/?c=ufa_Bsk_Register_User&m=checkLipodystrophy', field);
                            
                            //return true;
                        break;    
                        
                        // Болезнь накопления гликогена
                        case 79:
                            params.data.width = 800;
                            var field = this.createField(1, {
                                data: params.data
                            });                         
                            this.getValueOnDb('/?c=ufa_Bsk_Register_User&m=checkGlycogen', field);
                            
                            //return true;
                        break;  
                        //
                        //Лёгочная гипертензия
                        //
                        //В отличии от скрининга, где варианты ответов [Да,Нет],  в данном ПН (и в последующих)) [Нет] - в случае отсутствия, код и наименование диганоза - при выявлении                                                                        
                        // ВИЧ
                        case 176:
                            params.data.width = 400;
                            var field = this.createField(1, {
                                data: params.data
                            });        
                                             
                            this.getValueOnDb('/?c=ufa_Bsk_Register_User&m=checkHIV', field);
                            field.setDisabled(true);
                            
                        break;                                                                                                    
                        // Портальная гипертензия
                        case 177:
                            params.data.width = 400;
                            var field = this.createField(1, {
                                data: params.data
                            });        
                                             
                            this.getValueOnDb('/?c=ufa_Bsk_Register_User&m=checkPortalHypertension', field);
                            field.setDisabled(true);
                            
                        break;    
                        // Патология легких
                        case 178:
                            params.data.width = 400;
                            var field = this.createField(1, {
                                data: params.data
                            });        
                                             
                            this.getValueOnDb('/?c=ufa_Bsk_Register_User&m=checkLungPathology', field);
                            field.setDisabled(true);
                            
                        break;    
                        // Пороки сердца
                        case 179:
                            params.data.width = 400;
                            var field = this.createField(1, {
                                data: params.data
                            });        
                                             
                            this.getValueOnDb('/?c=ufa_Bsk_Register_User&m=checkHeartDefects', field);
                            field.setDisabled(true);
                            
                        break; 
                        // Заболевания соединительной ткани
                        case 180:
                            params.data.width = 400;
                            var field = this.createField(1, {
                                data: params.data
                            });        
                                             
                            this.getValueOnDb('/?c=ufa_Bsk_Register_User&m=checkTissueDiseases', field);
                            field.setDisabled(true);
                            
                        break;
                        //  Синдром абструктивного апноэ сна
                        case 181:
                            params.data.width = 400;
                            var field = this.createField(1, {
                                data: params.data
                            });        
                                             
                            this.getValueOnDb('/?c=ufa_Bsk_Register_User&m=checkSnoringDiag', field);
                            field.setDisabled(true);
                        break; 
                        // Саркоидоз
                        case 182:
                            params.data.width = 400;
                            var field = this.createField(1, {
                                data: params.data
                            });        
                                             
                            this.getValueOnDb('/?c=ufa_Bsk_Register_User&m=checkSarcoidosis', field);
                            field.setDisabled(true);
                        break;            
                        // Гистиоцитоз
                        case 183:
                            params.data.width = 400;
                            var field = this.createField(1, {
                                data: params.data
                            });        
                                             
                            this.getValueOnDb('/?c=ufa_Bsk_Register_User&m=checkHistiocytosis', field);
                            field.setDisabled(true);
                        break;                                                                                                                     
                        // Шистосомоз
                        case 184:
                            params.data.width = 400;
                            var field = this.createField(1, {
                                data: params.data
                            });        
                                             
                            this.getValueOnDb('/?c=ufa_Bsk_Register_User&m=checkSchistosomiasis', field);
                            field.setDisabled(true);
                        break;        
                        
                        //Артериальная гипертензия
                        // Диабет
                        case 220:
                            params.data.width = 400;
                            var field = this.createField(1, {
                                data: params.data
                            });        
                                             
                            this.getValueOnDb('/?c=ufa_Bsk_Register_User&m=checkDiabetesDiag', field);
                            field.setDisabled(true);
                        break; 
                        // ИБС
                        case 330:      
                            params.data.width = 400;
                            var field = this.createField(1, {
                                data: params.data
                            });        
                                             
                            this.getValueOnDb('/?c=ufa_Bsk_Register_User&m=checkIBS', field);
                            field.setDisabled(true);
                        break;     
                        case 221:      
                            params.data.width = 400;
                            var field = this.createField(1, {
                                data: params.data
                            });        
                                             
                            this.getValueOnDb('/?c=ufa_Bsk_Register_User&m=checkIBS', field);
                            field.setDisabled(true);
                        break;  
                        
                        // Цереброваскулярная болезнь
                        case 222:
                        case 331:     
                            params.data.width = 400;
                            var field = this.createField(1, {
                                data: params.data
                            });        
                                             
                            this.getValueOnDb('/?c=ufa_Bsk_Register_User&m=checkCerebrovascular', field);
                            field.setDisabled(true);
                        break;   
                    
                        // Хроническая болезнь почек
                        case 223:
                            params.data.width = 400;
                            var field = this.createField(1, {
                                data: params.data
                            });        
                                             
                            this.getValueOnDb('/?c=ufa_Bsk_Register_User&m=checkDiseaseDiag', field);
                            field.setDisabled(true);
                        break;                                                                                                                                                                                                                          
                        default: ''; break;                                
                    }
                }                
                
                
                if(field){
                    field.style = 'border:0px;background-image:url("");border-bottom:1px dashed gray; font-weight:bold';
                    //Ext.getCmp('Answer_'+params.data.id).setDisabled(true);
                    Ext.getCmp('QuestionPanel_'+params.data.id).removeAll();
                    Ext.getCmp('QuestionPanel_'+params.data.id).add(field);                                                                        
                    Ext.getCmp('QuestionPanel_'+params.data.id).doLayout();
                    
                    //Ext.getCmp('Answer_34').setDisabled(false);   
                }
				var Person_Birthday = Ext.getCmp('ufa_personBskRegistryWindow').personInfo.Person_Birthday;
				var age = Ext.getCmp('ufa_personBskRegistryWindow').getAge(Person_Birthday, new Date());
				var color;
                //var color = (params.data.stage == 1) ? 'red' : 'gray';
				// Формирование массива обязательных для заполнения вопросов и их подсвечивание красным цветом
				if (params.data.stage == 1) {
					/*if (params.data.min != null && age >= params.data.min && params.data.max != null && age < params.data.max ||
						params.data.min != null && age >= params.data.min && params.data.max == null ||
						params.data.min == null && params.data.max != null && age < params.data.max ||
						params.data.min == null && params.data.max == null) {
						color = 'red';
						Ext.getCmp('ufa_personBskRegistryWindow').postfixQuestions.push(params.data.id);
					}
					else {
						color = 'gray';
					}*/
					color = 'red';
					Ext.getCmp('ufa_personBskRegistryWindow').postfixQuestions.push(params.data.id);
				} else {
					color = 'gray';
				}
                //console.log('-------->', postfixQuestions);
                
                if(params.data.BSKObservElementFormat_id == 5 || parseInt(params.data.id) == 110 /*ИМТ Скрининг*/ 
                || parseInt(params.data.id) == 172/*ИМТ Лёгочная гипертензия*/  || parseInt(params.data.id) == 210/*ИМТ Артериальная гипертензия*/
                || parseInt(params.data.id) == 320/*ИМТ Ишемическая болезнь сержца*/
                        ){
                    var text = '<i id="i'+params.data.id+'">Рассчитать</i>';
                    var color = 'orange';
                }
                else{
                    var text = '<i id="i'+params.data.id+'">Не указано</i>';
                }
                
                var bgcolor = Ext.getCmp('ufa_personBskRegistryWindow').getBgColor(params.data.id);  
                
                //пока условие включено
                if(typeof field == 'undefined'){
                    var addAnswer = new Ext.Panel({
                        id: 'addAnswer_'+params.data.id,
                        baseCls: 'x-plain',
                        html: text,
                        border:false,
						tabIndex:-1,
                        style: 'color:' + color + '; margin-top:-10px; font-size:13px; cursor:pointer; width:200px; background-color:'+bgcolor,
                        listeners: {
                           'render': function() {
                               this.body.on('click', function() {
                                   //Защита от ввода дозировки без препарата
                                   var drugDoseTest = {
                                       //Легочная гипертензия
                                       186: 185,
                                       192: 187,
                                       194: 193,
                                       196: 195,
                                       198: 197,
                                       200: 199,
                                       202: 201,
                                       204: 203,
                                       //Артериальная гипертензия
                                       260: 251,
                                       261: 252,
                                       262: 253,
                                       263: 254,
                                       264: 255,
                                       265: 256,
                                       266: 257,
                                       267: 258,
                                       268: 259,
                                       //Ишемическая болезнь сердца
                                       366: 365,
                                       368: 367,
                                       370: 369,
                                       372: 371,
                                       374: 373,
                                       376: 375,
                                       378: 377,
                                       380: 379,
                                       382: 381                                       
                                   }
                                   
                                   var questionID = drugDoseTest[Number(params.data.id)];

                                   if(typeof questionID != 'undefined'){
									   //Если не введен препарат и анкета доступна для редактирования
                                       if((typeof Ext.getCmp('Answer_'+questionID) == 'undefined' || Ext.getCmp('Answer_'+questionID).getValue() == '') 
											   && !Ext.getCmp('ufa_personBskRegistryWindow').elemDisabled()){
                                           Ext.Msg.alert('Ошибка ввода', 'Необходимо ввести препарат');
                                           return;
                                       }
                                   }                                   
                                   //конец защиты
                                   Ext.getCmp('ufa_personBskRegistryWindow').renderAnswer(params); 
								   
                                   if(params.data.BSKObservElementFormat_id == 1){
										if (Ext.getCmp('ufa_personBskRegistryWindow').isUserClick) {
												Ext.getCmp('Answer_'+params.data.id).onTriggerClick(); 
										}		
                                   }    
                                   else if(params.data.BSKObservElementFormat_id == 3)
                                       Ext.getCmp('Answer_'+params.data.id).focus();
                                   
                               });										   
                            }
                        }
                    })  
                    
                    //Пригодиться
                    addAnswer.params = params;                   
                    
                    Ext.getCmp('QuestionPanel_'+params.data.id).add(addAnswer);      
                    Ext.getCmp('itemsQuestionPanel_'+params.data.id).doLayout();
					
					
					//Для удобства - активация первого поля для ввода значения
					
					Ext.getCmp('ufa_personBskRegistryWindow').switchFieldOnTab(params.data.id, true);
					

				}
            },
			//Функция получения id параметра текущего предмета наблюдения, соответствующего BSKObservElement_id антропометрического параметра сохраненного предмета наблюдения
			//MorbusType_id - текущий предмет наблюдения
			//BSKObservElement_id - id параметра предмета наблюдения, сохраненного в бд			
			getBSKObserElement_id: function(MorbusType_id, BSKObservElement_id) {
				for(var k in form.anthropometry){
					if(form.anthropometry[k].indexOf(BSKObservElement_id) >= 0){
						var index = form.anthropometry[k].indexOf(BSKObservElement_id);
						break;
					}				
				}
				var needElement = form.anthropometry[MorbusType_id];     
				var needBSKObserElement_id = (typeof index != 'undefined') ? needElement[index] : null;
				var needKey = needBSKObserElement_id == null || needBSKObserElement_id == 0 ? null : needBSKObserElement_id;

				//console.log(needKey)

				return needKey;				
			},
			//Добавление антропометрических параметров
			getBodyMassIndexParams: function() {	
				
				Ext.Ajax.request({
					url: '/?c=ufa_BSK_Register_User&m=getLastAnketData',
					params: {
						Person_id   : Ext.getCmp('ufa_personBskRegistryWindow').personInfo.Person_id
					},
					callback: function(options, success, response) {						
						if (success === true) {   
							var responseText = Ext.util.JSON.decode(response.responseText);
							var form = Ext.getCmp('ufa_personBskRegistryWindow');
							
							for (var k in responseText) {//Перебираем все сохраненные антропометрические параметры, сохраненные в бд 
								if (responseText[k] != undefined) {
									var idpar = form.getBSKObserElement_id(form.MorbusType_id, responseText[k].BSKObservElement_id);// id параметра текущего предмета наблюдения
									var par = responseText[k].BSKRegistryData_data; // значение этого параметра		
									if (document.getElementById('i' + idpar) != null) { 
										document.getElementById('i' + idpar).click();	//Если вопрос addAnswer, то кликаем по нему										
									}		
									if (typeof Ext.getCmp('Answer_'+ idpar) == 'object') {
											Ext.getCmp('Answer_'+ idpar).setValue(par); //Вставляем значение параметра
									}
								}
							}
						}
					}
				});		
				
			},			
			/*getBodyMassIndexParams: function() {			
				Ext.Ajax.request({
					url: '/?c=ufa_BSK_Register_User&m=getLastAnketData',
					params: {
						Person_id   : Ext.getCmp('ufa_personBskRegistryWindow').personInfo.Person_id
					},
					callback: function(options, success, response) {

						if (success === true) {   
							var responseText = Ext.util.JSON.decode(response.responseText);
							var form = Ext.getCmp('ufa_personBskRegistryWindow');
							var anthrMorbusType_id = form.anthropometry[form.MorbusType_id];
							for (var k in anthrMorbusType_id) {
								if (typeof(anthrMorbusType_id[k]) == 'number') {
									//Если параметр существует, то вставляем его
									if (responseText[k] != undefined) {
										if (document.getElementById('i' + anthrMorbusType_id[k]) != null) {
											document.getElementById('i' + anthrMorbusType_id[k]).click();											
										}		
										if (typeof Ext.getCmp('Answer_'+ anthrMorbusType_id[k]) == 'object') {
											if (form.MorbusType_id == 88 && k == 2) {
												Ext.getCmp('Answer_'+ anthrMorbusType_id[k]).setValue(responseText[Number(k)+1].BSKRegistryData_data);
											} else {												
												Ext.getCmp('Answer_'+ anthrMorbusType_id[k]).setValue(responseText[k].BSKRegistryData_data);
											}
											
										}
										
									}
																	
								}

							}
							
						}
						
					}
				});			
			},*/
            /**
             * Расчёт индекса массы тела
             */
            getIndex : function(id, id_imt){
                //Скрининг
                if(id_imt == 110){
                    var answersIMT = [107,108];
                }
                //Лёгочная гипертензия
                else if(id_imt == 172){
                    var answersIMT = [142,143];
                }  
                //Артериальная гипертензия
                else if(id_imt == 210){
                    var answersIMT = [208,209];
                }  
                 //Ишемическая болезнь сердца
                else if(id_imt == 320){
                    var answersIMT = [318,319];
                }  
                
                //Автоматический расчёт по формулам
                if(!Ext.getCmp('Answer_' + answersIMT[0]) || !Ext.getCmp('Answer_' + answersIMT[1])){
                    this.showMsg('Для расчёта необходимо указать рост и вес пациента');
                    return false;
                }
                else if(Ext.getCmp('Answer_' + answersIMT[0]).getValue() < 0){
                    this.showMsg('Некорректно указан рост пациента');
                    return false;
                }
                else if(Ext.getCmp('Answer_' + answersIMT[1]).getValue() < 0){
                    this.showMsg('Некорректно указан вес пациента');
                    return false;
                }                          

                var unitHeight = Ext.getCmp('UnitCombo_' + answersIMT[0]).getValue();
                var unitWeight = Ext.getCmp('UnitCombo_' + answersIMT[0]).getValue();
                
                if(unitHeight == '' || unitWeight == ''){
                    this.showMsg('Укажите едииницу измерения!');
                    return;                            
                }
                
                var height = (unitHeight == 'см') ? parseInt(Ext.getCmp('Answer_' + answersIMT[0]).getValue())/100 : Ext.getCmp('Answer_' + answersIMT[0]).getValue(); 
                var weight = (unitWeight == 'кг') ? parseInt(Ext.getCmp('Answer_' + answersIMT[1]).getValue()) : Ext.getCmp('Answer_' + answersIMT[1]).getValue()*1000;

                var indexBody = (weight / (height*height)).toFixed(1)
                
                //var indexBody = weight / (height*height);
                
                Ext.getCmp('Answer_'+id_imt).setValue(typeof indexBody == 'NaN' ? 0 : indexBody);
                
                //console.log('Answer_'+id_imt, '!!!!!!!!!', indexBody);
                
                //.toFixed(1)
                //Ext.getCmp('Answer_110').setDisabled(true);
              
            },             
            /**
             * Создание и прорисовка поля после предложения добавить ответ
             * @param object
             */
            renderAnswer: function(params){
                //console.log('>>>', params);
                var field = form.createField(params.data.BSKObservElementFormat_id, {
                    data: params.data
                });
                                 
                
                //Автоматический расчёт по формулам
                switch(parseInt(params.data.id)){ 
                     //индекс массы тела
                    case 110: //Скрининг
                        var heightId = 107; 
                        var weightId = 108;  
                        //.toFixed(1)
                        //field.setDisabled(true);
                    break;
                   
                    case 172: //Лёгочная гипертензия
                        var heightId = 142; 
                        var weightId = 143;                                 
                    break;    
                    
                    case 210: //Артериальная гипертензия
                        var heightId = 208; 
                        var weightId = 209;                                           
                    break;    

                    case 320: //Артериальная гипертензия
                        var heightId = 318; 
                        var weightId = 319;                                           
                    break;                       
                    // не использовать return ни под каким предлогом
                    //default : return; break;   
                }      
                
                if(typeof heightId != 'undefined' && typeof weightId != 'undefined'){
                    if(parseInt(Ext.getCmp('ufa_personBskRegistryWindow').MorbusType_id).inlist([84,88,89,19,50])){
    
                        if(!Ext.getCmp('Answer_'+heightId) || !Ext.getCmp('Answer_'+weightId)){
                            console.log('heightId', Ext.getCmp('Answer_'+heightId), heightId);
                            console.log('weightId', Ext.getCmp('Answer_'+weightId), weightId);
                            
                            this.showMsg('Для расчёта необходимо указать рост и вес пациента');
                            return false;
                        }
                        else if(parseInt(Ext.getCmp('Answer_'+heightId).getValue()) <= 0){
                            this.showMsg('Некорректно указан рост пациента');
                            return false;
                        }
                        else if(parseInt(Ext.getCmp('Answer_'+weightId).getValue()) <= 0){
                            this.showMsg('Некорректно указан масса тела пациента');
                            return false;
                        }                          
    
                        var unitHeight = Ext.getCmp('UnitCombo_'+heightId).getValue();
                        var unitWeight = Ext.getCmp('UnitCombo_'+weightId).getValue();
                        
                        if(unitHeight == '' || unitWeight == ''){
                            this.showMsg('Укажите едииницу измерения!');
                            return;                            
                        }  
    
                        
                        var height = (unitHeight == 'см') ? parseInt(Ext.getCmp('Answer_'+heightId).getValue())/100 : Ext.getCmp('Answer_'+heightId).getValue(); 
                        var weight = (unitWeight == 'кг') ? parseInt(Ext.getCmp('Answer_'+weightId).getValue()) : Ext.getCmp('Answer_'+weightId).getValue()*1000;
                        var indexBody = (weight / (height*height)).toFixed(1)
                        
                        field.setValue(indexBody == 'NaN' ? 0 : indexBody);                      
                    }
                }
                

                Ext.getCmp('QuestionPanel_'+params.data.id).remove('addAnswer_'+params.data.id); 
                Ext.getCmp('itemsQuestionPanel_'+params.data.id).remove('addAnswer_'+params.data.id); 
                Ext.getCmp('itemsQuestionPanel_'+params.data.id).add(field); 
                
                //Единицы измерения не нужны некоторым вопросам
                //Лекарственное лечение полностью без ед. измерения 
                var temp_arr = [];
                for(var i = 111; i<138;i++) temp_arr.push(i);     
                
                //Список ID вопросов без ед. измерения
                if(!params.data.id.inlist([
                                           /*84 - Скрининг*/25,26,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,48,49,62,63,65,102,103,104,105,106,110, 58,59,60,61,67,78,79,
                                           /*88 Лёгочная гипертензия*/150,151,154,155,161,162,171,172,175, 176,177,178,179,180,181,182,183,184, 185,187,193,195,197,199,201,203,
                                           /*89 Аритериальная гипертензия*/207,210,217,218,219,220,221,222,223,241,242,243,245,246,250,251,252,253,254,255,256,257,258,259, 269,
                                           /* ОКС*/270,271,272,273,274,275,276,300,303,284,285,286,277,278,279,280,281,282,283,287,288,289,290,291,292,293,294,295,296,297,298,299,301,302,
                                                   304,305,306,307,308,309,310,311,312,313,314,398,399,400,
                                           /* 50 Ишемическая болезнь сердца*/315,316,317,320,327,328,329,330,331,332,383, 340,341,342,343,344,345,346,347,348,349,
                                           365,367,369,371,373,375,377,379,381, 385
                                           ]) 
                                           && !params.data.id.inlist(temp_arr) ){
                    Ext.getCmp('itemsQuestionPanel_'+params.data.id).add(this.getComboUnits(params.data.id)); 
                }

                Ext.getCmp('itemsQuestionPanel_'+params.data.id).doLayout();
                
            },
            getValueOnDb: function(url, answer_field){
				//return;
                Ext.getCmp('ufa_personBskRegistryWindow').field = answer_field;
                var Evn_insDT = Ext.getCmp('TextFieldDate').getValue().dateFormat("Y-m-d");
                
                Ext.Ajax.request({
                	url: url,
                	params: {
                	    //BSKObject_id: BSKObject_id,
                        Person_id : Ext.getCmp('ufa_personBskRegistryWindow').Person_id,
                	    Evn_insDT : Evn_insDT
                    },
                	callback: function(options, success, response) {
                        if (success === true) {
                            var responseText =  Ext.util.JSON.decode(response.responseText);
                        }
                        /* OLD
                           console.log(answer_field.id); 
                           console.log(url);
                           console.log('responseText', responseText);
                          
                           console.log('callback', Ext.getCmp('ufa_personBskRegistryWindow').field); 
                           
                           if(typeof Ext.getCmp(options.scope.id) != 'undefined'){
                              Ext.getCmp(options.scope.id)
                              answer_field.setValue((typeof responseText[0] == 'undefined') ? 'Нет' : responseText[0].diagstring);
                    	   }
                       */
                       //console.log('!!!!!!!!!', responseText[0].diagstring);
                       if(typeof answer_field != 'undefined'){
						   //console.log(answer_field.id,answer_field.getValue());
                           //Для скрининга было решено так, не факт что перепишу
                           if( Ext.getCmp('ufa_personBskRegistryWindow').MorbusType == 84){ 
							  //if (answer_field.getValue() != 'Да' ) {
								  //console.log('answer_field.getValue() != Да' );
								 answer_field.setValue((typeof responseText[0] == 'undefined') ? 'Нет' : 'Да');//responseText[0].diagstring); 
							 //}
                              
                           }
                           else {
                              //console.log('responseText', responseText, url)
							  //if (answer_field.getValue() == 'Нет') {
								//console.log('answer_field.getValue() != Нет' );
								answer_field.setValue((typeof responseText[0] == 'undefined') ? 'Нет' : responseText[0].diagstring);
							  //}
                              
                              
							  answer_field.setDisabled(true);
                           }

                           //Инвалидность игнор
						   
                           if(!answer_field.id.inlist(['Answer_26','Answer_175','Answer_207','Answer_317'])){   
							    
								if (answer_field.getValue() == 'Да' /*|| Ext.getCmp('ufa_personBskRegistryWindow').elemDisabled()*/){ //если ответ выбран да  или анкету открывает не автор анкеты
									//console.log('залочено answer_field',answer_field, answer_field.getValue());
								   answer_field.setDisabled(true);
								}
								else {
								   answer_field.setDisabled(false);
								   //console.log('разлочено answer_field',answer_field, answer_field.getValue());
								   //answer_field.setDisabled(Ext.getCmp('ufa_personBskRegistryWindow').elemDisabled());
								}
                           }
						   else{
							   answer_field.setDisabled(true);
						   }
						   
                           //Автоматическая длина
                           answer_field.setWidth(answer_field.getValue().length*10);
                       }
                       
                    },
                    scope : Ext.getCmp('ufa_personBskRegistryWindow').field
                });
                
                return;
            },

            getValueOnDb_withCodeDiag: function(url, answer_field){
                Ext.getCmp('ufa_personBskRegistryWindow').field = answer_field;
                var Evn_insDT = Ext.getCmp('TextFieldDate').getValue().dateFormat("Y-m-d");
                
                Ext.Ajax.request({
                	url: url,
                	params: {
                	    //BSKObject_id: BSKObject_id,
                        Person_id : Ext.getCmp('ufa_personBskRegistryWindow').Person_id,
                	    Evn_insDT : Evn_insDT
                    },
                	callback: function(options, success, response) {
                        if (success === true) {
                            var responseText =  Ext.util.JSON.decode(response.responseText);
                        }
                        /* OLD
                           console.log(answer_field.id); 
                           console.log(url);
                           console.log('responseText', responseText);
                          
                           console.log('callback', Ext.getCmp('ufa_personBskRegistryWindow').field); 
                           
                           if(typeof Ext.getCmp(options.scope.id) != 'undefined'){
                              Ext.getCmp(options.scope.id)
                              answer_field.setValue((typeof responseText[0] == 'undefined') ? 'Нет' : responseText[0].diagstring);
                    	   }
                       */
                       if(typeof answer_field != 'undefined'){
                           //console.log('ОТВЕТ ', responseText);                                                
                           /*
                           answer_field.setValue((typeof responseText[0] == 'undefined') ? 'Нет' : 'Да');//responseText[0].diagstring);
                           
                           //Инвалидность игнор
                           if(answer_field.id != 'Answer_26'){             
                               if(answer_field.getValue() == 'Да'){
                                  answer_field.setDisabled(true);
                               }
                               else{
                                  answer_field.setDisabled(false);
                               }
                           }
                           */                                                      
                       }
                       
                    },
                    scope : Ext.getCmp('ufa_personBskRegistryWindow').field
                });
                
                return;
            },

            renderCombo: function(id){
                
                var id = 'Answer_'+id;
                
                //console.log(this.listIDS);
                
                if(typeof Ext.getCmp('QuestionPanel_'+id[1]) == 'undefined'){
                   
                   /*
                    var field = form.createField(1);
                    
                    field.style = 'border:0px;background-image:url("");border-bottom:1px dashed gray; font-weight:bold';
                    Ext.getCmp('QuestionPanel_'+id).removeAll();
                    Ext.getCmp('QuestionPanel_'+id).add(field);                                                                        
                    Ext.getCmp('QuestionPanel_'+id).doLayout();                    
                    */
                }
                
                /*
                if(field){

                    field.style = 'border:0px;background-image:url("");border-bottom:1px dashed gray; font-weight:bold';
                    Ext.getCmp('QuestionPanel_'+params.data.id).removeAll();
                    Ext.getCmp('QuestionPanel_'+params.data.id).add(field);                                                                        
                    Ext.getCmp('QuestionPanel_'+params.data.id).doLayout();
                } 
                */               
            },
                     
            //В лекарственном лечении организованы зависимые списки  
            
            //Управление группами комбобоксов лекарственного лечения - зависимые списки
            //1 - принято накануне
            //2 - прописано на текущем
            //3 - дозировка накануне
            //4 - дозировка на текужем
            //5 - причина отмены
            /* e.g.
            var params = {
                ids : {
                    1: 'Answer_12',
                    2: 'Answer_13',
                    3: 'Answer_14',
                    4: 'Answer_15',
                    5: 'Answer_16'
                }
            } 
            */
            manageDrugSelect: function(params, combo){
                var form = this;
                //Если речь не о группах дозировки
                if(typeof params.ids == 'undefined'){
                    return;
                }
                
                for(var k in form.listIDS){
                    for(var j in params.ids){
                        var id = parseInt(params.ids[j].replace('Answer_', ''));
                         
                        if(form.listIDS[k].BSKObservElement_id == id){
                            //console.log('>', form.listIDS[k]);
                            if(typeof Ext.getCmp(params.ids[j]) == 'undefined'){
                                var param = {}
                                    param.data = {}
                                    param.data = form.listIDS[k];
                                    param.data.id = id;
                                    
                                var field = form.createField(1, param);
                                
                                field.style = 'border:0px;background-image:url("");border-bottom:1px dashed gray; font-weight:bold';
                                Ext.getCmp('QuestionPanel_'+id).removeAll();
                                Ext.getCmp('QuestionPanel_'+id).add(field);                                                                        
                                Ext.getCmp('QuestionPanel_'+id).doLayout();                              
                            }
                        }
                    }
                }

                //при попытке указать дозировку накануне приёма
                if(combo.id == params.ids[3] && Ext.getCmp(params.ids[1]).getValue() == ''){
                    form.showMsg('Укажите группу препаратов, принятую на кануне текущего осмотра!');
                    Ext.getCmp(params.ids[3]).setValue('Нет сведений');
                    
                    Ext.getCmp(params.ids[1]).setValue('Нет сведений');
                    Ext.getCmp(params.ids[2]).setValue('Не назначена');
                    Ext.getCmp(params.ids[4]).setValue('Не принимает');
                    Ext.getCmp(params.ids[5]).setValue('Не назначено');
                }   
                //при попытке указать дозировку на текущем осмотре
                else if(combo.id == params.ids[4]  && Ext.getCmp(params.ids[2]).getValue() == ''){
                    form.showMsg('Укажите группу препаратов, принятую на кануне текущего осмотра!');
                    Ext.getCmp(params.ids[4]).setValue('Нет сведений');
                    
                    Ext.getCmp(params.ids[1]).setValue('Нет сведений');
                    Ext.getCmp(params.ids[3]).setValue('Не назначена');
                    Ext.getCmp(params.ids[3]).setValue('Не принимает');
                    Ext.getCmp(params.ids[5]).setValue('Не назначено');
                }   
                //установка группы накануне текущего осмотра
                else if(combo.id == params.ids[1]){
                    if(Ext.getCmp(params.ids[1]).getValue().inlist(['Нет сведений', 'Не принимает'])){
                         Ext.getCmp(params.ids[3]).setValue(Ext.getCmp(params.ids[1]).getValue());
                         Ext.getCmp(params.ids[3]).setDisabled(true);
                         
                         if(Ext.getCmp(params.ids[2]).getValue() == ''){
                             Ext.getCmp(params.ids[2]).setValue('Не назначена')
                         }
                         if(Ext.getCmp(params.ids[4]).getValue() == ''){
                             Ext.getCmp(params.ids[4]).setValue('Не принимает')
                             Ext.getCmp(params.ids[4]).setDisabled(true);
                         }                         
                         
                    }
                    else{
                         if(Ext.getCmp(params.ids[2]).getValue() == ''){
                             Ext.getCmp(params.ids[2]).setValue('Не назначена')
                         }
                         if(Ext.getCmp(params.ids[4]).getValue() == ''){
                             Ext.getCmp(params.ids[4]).setValue('Не принимает')
                             Ext.getCmp(params.ids[4]).setDisabled(true);
                         }                          
                        
                        Ext.getCmp(params.ids[3]).setValue('Не принимает');
                        Ext.getCmp(params.ids[3]).setDisabled(false);
                    }
                }
                //установка группы на текущем осмотре
                else if(combo.id == params.ids[2]){
                         if(Ext.getCmp(params.ids[1]).getValue() == ''){
                             Ext.getCmp(params.ids[1]).setValue('Не назначена')
                         }
                         if(Ext.getCmp(params.ids[3]).getValue() == ''){
                             Ext.getCmp(params.ids[3]).setValue('Не принимает')
                             Ext.getCmp(params.ids[3]).setDisabled(true);
                         }                        
                    
                    if(Ext.getCmp(params.ids[2]).getValue() == 'Не назначена'){
                         Ext.getCmp(params.ids[4]).setValue('Не принимает');
                         Ext.getCmp(params.ids[4]).setDisabled(true);
                    }
                    else if(Ext.getCmp(params.ids[2]).getValue() == 'Не принимает'){
                         Ext.getCmp(params.ids[4]).setValue('Не принимает');
                         Ext.getCmp(params.ids[4]).setDisabled(true);
                    }                    
                    else{
                         if(Ext.getCmp(params.ids[1]).getValue() == ''){
                             Ext.getCmp(params.ids[1]).setValue('Не назначена')
                         }
                         if(Ext.getCmp(params.ids[3]).getValue() == ''){
                             Ext.getCmp(params.ids[3]).setValue('Не принимает')
                             Ext.getCmp(params.ids[3]).setDisabled(true);
                         }                           
                        
                        Ext.getCmp(params.ids[4]).setValue('Не принимает');
                        Ext.getCmp(params.ids[4]).setDisabled(false);
                    }
                }
                
                if(Ext.getCmp(params.ids[5]).getValue() == ''){               
                    Ext.getCmp(params.ids[5]).setValue('Не назначено');
                }  
                
                Ext.getCmp(params.ids[5]).setDisabled(true);

                //console.log(!Ext.getCmp(params.ids[2]).getValue().inlist(['Не назначена', 'Не принимает']));
                
                if(!Ext.getCmp(params.ids[1]).getValue().inlist(['Не назначена', 'Нет сведений']) && !Ext.getCmp(params.ids[2]).getValue().inlist(['Не назначена', 'Не принимает']))
                {  
                    if(Ext.getCmp(params.ids[1]).getValue() != Ext.getCmp(params.ids[2]).getValue()){
                        //console.log(1);
                        Ext.getCmp(params.ids[5]).setDisabled(false);
                    }
                }
                
                
                if(Ext.getCmp(params.ids[1]).getValue().inlist(['Не назначена', 'Нет сведений']) && !Ext.getCmp(params.ids[2]).getValue().inlist(['Не назначена', 'Не принимает'])){
                    //console.log(1);
                    Ext.getCmp(params.ids[5]).setDisabled(false);
                }

                if(!Ext.getCmp(params.ids[1]).getValue().inlist(['Не назначена', 'Нет сведений']) && Ext.getCmp(params.ids[2]).getValue().inlist(['Не назначена', 'Не принимает'])){
                    //console.log(1);
                    Ext.getCmp(params.ids[5]).setDisabled(false);
                }                
                           
            },
            showMsg : function(msg){
            	sw.swMsg.show(
            	{
            		  buttons: Ext.Msg.OK,
            		  icon: Ext.Msg.WARNING,
                      width : 600,
            		  msg: msg,
            		  title: ERR_INVFIELDS_TIT
            	});    
            },            
                                
		});        

        sw.Promed.ufa_personBskRegistryWindow.superclass.initComponent.apply(this, arguments);
    },
	button1Callback: Ext.emptyFn,
	button2Callback: Ext.emptyFn,
	button3Callback: Ext.emptyFn,
	button4Callback: Ext.emptyFn,
	button5Callback: Ext.emptyFn,
	button1OnHide: Ext.emptyFn,
	button2OnHide: Ext.emptyFn,
	button3OnHide: Ext.emptyFn,
	button4OnHide: Ext.emptyFn,
	button5OnHide: Ext.emptyFn,
	collectAdditionalParams: Ext.emptyFn,    
	panelButtonClick: function(winType) {
		var params = this.collectAdditionalParams(winType);
		var window_name = '';

		if ( typeof params != 'object' ) {
			params = new Object();
		}
        
        var personInfo = Ext.getCmp('ufa_personBskRegistryWindow').personInfo;
        
		switch ( winType ) {
			case 1:
                

				params.callback = this.button1Callback;
				params.onHide = this.button1OnHide;
				params.Person_Birthday = personInfo.Person_Birthday;
				params.Person_Firname = personInfo.Person_Firname;
				params.Person_Secname = personInfo.Person_Secname;
				params.Person_Surname = personInfo.Person_Surname;
				params.Person_deadDT = personInfo.Person_deadDT;
				params.Person_closeDT = personInfo.Person_closeDT;
				window_name = 'swPersonCardHistoryWindow';
			break;

			case 2:
				params.action = 'edit';
				params.callback = this.button2Callback;
				params.onClose = this.button2OnHide;
				window_name = 'swPersonEditWindow';
			break;

			case 3:
				params.callback = this.button3Callback;
				params.onHide = this.button3OnHide;
				params.Person_Birthday = personInfo.Person_Birthday;
				params.Person_Firname = personInfo.Person_Firname;
				params.Person_Secname = personInfo.Person_Secname;
				params.Person_Surname = personInfo.Person_Surname;
				params.Person_deadDT = personInfo.Person_deadDT;
				params.Person_closeDT = personInfo.Person_closeDT;
				window_name = 'swPersonCureHistoryWindow';
			break;

			case 4:
				params.callback = this.button4Callback;
				params.onHide = this.button4OnHide;
				params.Person_Birthday = personInfo.Person_Birthday;
				params.Person_Firname = personInfo.Person_Firname;
				params.Person_Secname = personInfo.Person_Secname;
				params.Person_Surname = personInfo.Person_Surname;
				params.Person_deadDT = personInfo.Person_deadDT;
				params.Person_closeDT = personInfo.Person_closeDT;
				window_name = 'swPersonPrivilegeViewWindow';
			break;

			case 5:
				params.callback = this.button5Callback;
				params.onHide = this.button5OnHide;
				params.Person_Birthday = personInfo.Person_Birthday;
				params.Person_Firname = personInfo.Person_Firname;
				params.Person_Secname = personInfo.Person_Secname;
				params.Person_Surname = personInfo.Person_Surname;
				params.Person_deadDT = personInfo.Person_deadDT;
				params.Person_closeDT = personInfo.Person_closeDT;
				window_name = 'swPersonDispHistoryWindow';
			break;

			default:
				return false;
			break;
		}

		params.Person_id = personInfo.Person_id;
		params.Server_id = personInfo.Server_id;

		if ( getWnd(window_name).isVisible() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: Ext.emptyFn,
				icon: Ext.Msg.WARNING,
				msg: 'Окно уже открыто',
				title: ERR_WND_TIT
			});

			return false;
		}

		getWnd(window_name).show(params);
	}, 
    /*getAge: function (dateString) {
          var day = parseInt(dateString.substr(0,2));
          var month = parseInt(dateString.substr(3,5));
          var year = parseInt(dateString.substr(6,10));
    
          var today = new Date();
          var birthDate = new Date(year, month, day);
          var age = today.getFullYear() - birthDate.getFullYear();
          var m = today.getMonth() - birthDate.getMonth();
          if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
              age--;
          }
          return age;
    },*/
    getAge: function (dateString, TextFieldDate) {
          var day = parseInt(dateString.substr(0,2));
          var month = parseInt(dateString.substr(3,5));
          var year = parseInt(dateString.substr(6,10));
          var birthDate = new Date(year, month, day);
          var age = TextFieldDate.getFullYear() - birthDate.getFullYear();
          var m = TextFieldDate.getMonth() - birthDate.getMonth();
          if (m < 0 || (m === 0 && TextFieldDate.getDate() < birthDate.getDate())) {
              age--;
          }
          return age;
    }, 
    //Установка зебры для лекарственного лечения
    getBgColor : function(id){
            switch(parseInt(id)){
                    //84 Скрининг
                    case 111:
                    case 112:
                    case 113:
                    case 114:
                    case 115: 
                    
                    case 121:
                    case 122:
                    case 123:
                    case 124:
                    case 125:    
                    
                    case 131:
                    case 132:
                    case 133:
                    case 136:
                    case 137:    
                    
                    //88 Лёгочная гипертензия
                    case 185:
                    case 186:
                    
                    case 193:
                    case 194:
                    
                    case 197:
                    case 198:
                    
                    case 201:
                    case 202:       
                    
                    //89 Артериальная гипертензия
                    case 251:
                    case 260:
                    
                    case 253:
                    case 262:          
                    
                    case 255:
                    case 264:  
                    
                    case 257:
                    case 266:        
                    
                    case 259:
                    case 268: 
                        
                    //50 Ишемическая болезнь сержца
                    case 365:
                    case 366:
                    
                    case 369:
                    case 370:
                    
                    case 373:
                    case 374:    
                        
                    case 377:
                    case 378:
                    
                    case 381:
                    case 382:    
                    /*
                    case 242:
                    case 243:
                    case 244:
                    case 245:    
                    case 246:
                    */
                                                                                 
                        var bgcolor = '#E2FFE9';
                    break;
                    default : var bgcolor ='white'; break;
            }     
            return bgcolor; 
                   
    },      
    getInfoPacient : function(Person_id){

              Ext.Ajax.request({
    		 		url: '/?c=ufa_Bsk_Register_User&m=loadPersonData',
    				params: {
    				    Person_id : Person_id
    				},
    				callback: function(options, success, response) {

                        if (success === true) {
                             var personData = Ext.util.JSON.decode(response.responseText);
                             Ext.getCmp('ufa_personBskRegistryWindow').personInfo = personData.personInfo;
                             Ext.getCmp('ufa_personBskRegistryWindow').personInfo.age = Ext.getCmp('ufa_personBskRegistryWindow').getAge(personData.personInfo.Person_Birthday, new Date());
                             //console.log('personData.personInfo.Person_Birthday',personData.personInfo.Person_Birthday);
                             Ext.getCmp('infoPacient').setTitle(personData.title);
                             Ext.getCmp('infoPacient').body.update(personData.text, true);
    					     
                             Ext.getCmp('GridObjectsUser').setDisabled(false);
                        }
    				}
    		  });        
    },
    show : function(params){
		var body = Ext.getBody();
		var form = this;
		//console.log('form',form);
		//Корректировка удержания фокуса на списке вопросов
		document.body.addEventListener(
			'keyup',  function(e){
				if (e.keyCode == 9) {
					setTimeout(function() {						
						if (!(/^(add)?Answer_/.test(document.activeElement.id)) &&
							form.combExp != '' && 
							typeof Ext.getCmp('Answer_'+form.combExp) == 'object' &&
							!Ext.getCmp('Answer_'+form.combExp).disabled) {	
							Ext.getCmp('Answer_'+form.combExp);
							Ext.getCmp('Answer_'+form.combExp).collapse();
							Ext.getCmp('Answer_'+form.combExp).focus();	
							
							form.combExp = '';
						}
					},100);
				
				}
			}
		);
		
	
		/*Ext.getCmp('information').on(
			'onkeydown',
			function (event){
				console.log(event.keyCode);
				if(event.keyCode==9) return false;
			}
		);*/
        this.Person_id = params.Person_id;
        
        this.getInfoPacient(params.Person_id);
       
        this.GridObjects.getGrid().getStore().load({
            params: {
                Person_id: params.Person_id
            }
        });     
        
        Ext.getCmp('information').removeAll();
        
        Ext.getCmp('tabpanelBSK').setActiveTab(Ext.getCmp('infotab'));  
        
        this.Periods[this.MorbusType_id] = {};
          
        sw.Promed.ufa_personBskRegistryWindow.superclass.show.apply(this, arguments);
         
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
					delete sw.Promed[this.objectName];                           
                    
	} ,   
    listeners : {
        'render' : function(){
				

        },
		'hide': function() {
		    Ext.getCmp('ufa_personBskRegistryWindow').clickToPN = 0;
			if (this.refresh)
				this.onHide();
			
		}, 
		
    }
});       