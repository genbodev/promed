/**
* Окно управления рекомендациями регистра БСК
* административная часть 
*
*
* @package      All
* @access       admin
* @autor		Васинский Игорь
* @version      20.08.2014
*/

sw.Promed.ufa_AdminBSKRecomendationWindow = Ext.extend(sw.Promed.BaseForm,
{
    title: 'Рекомендации',
    bodyStyle: 'padding:5px; border: 0px;',
    width: 900,
    closable: true,
    closeAction: 'hide',
    height: 700,
	modal: true,
	frame: true,
    buttonAlign: "left",
	objectName: 'ufa_AdminBSKRecomendationWindow',
	id: 'ufa_AdminBSKRecomendationWindow',
	objectSrc: '/jscore/Forms/Admin/Ufa/ufa_AdminBSKRecomendationWindow.js',
    
    initComponent: function()
	{   
	   
          this.gridRecomendation = new sw.Promed.ViewFrame({
            			id: 'gridRecomendation',
                    	title: 'Список рекомендаций',   
                        selectionModel: 'multiselect',       
                        multi: true,  
                        ignoreRightMouseSelection: true,     
            			contextmenu: true,
                        //border: false,
                        height: 550,
            			//height: Ext.getBody().getHeight()*0.897,
                        object: 'gridRecomendation',
            			dataUrl: '/?c=ufa_BSK_Register&m=getRecomendations',
            			autoLoadData: false,
                        focusOnFirstLoad: false,
                        toolbar: false,
                        autoScroll: true,
            			stringfields: [
            				{name: 'BSKObservRecomendation_id', type: 'int', header: 'ID'},
                            {name: 'BSKObservRecomendationType_id', type: 'int', hidden: true},
                            {name: 'BSKObservElementValues_id', type: 'int', hidden: true},
                            {name: 'mark', type: 'int', hidden: true},
            				{name: 'BSKObservRecomendation_text', header: 'Текст рекомендации', id: 'autoexpand'}                      
                            
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
            				{name:'action_print', hidden: true}
            			],
                        onLoadData: function(){
                            
                        }       
         }) 
        
             

  
   
/*

*/                                   
       
		Ext.apply(this,
		{
            items : [
                 {
                    xtype: 'fieldset',
                    layout: 'column',
                    collapsible : true,
                    collapsed: true,                    
                    title: 'Поиск по базе рекомендаций',
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
                                    style: 'border:0px!important',
                                    labelWidth: 2,
                                    labelSeparator: '',
                                    id: 'searchRecomendation_text',
                                    width: 690,
                                    value: '',
                                    columnWidth: 0.8,
                					listeners: {
                                        specialkey:  function(field, e){
                                            console.log('e.getKey: ', e.getKey());
                                            if (e.getKey() == e.ENTER) {
                                                Ext.getCmp('searchRecomendationButton').handler();
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
                                    id: 'searchRecomendationButton',
                                    handler: function(){
                                         var searchRecomendation_text = Ext.getCmp('searchRecomendation_text').getValue();
                                         
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
                                         
                                         var params = { 
                                                BSKObservRecomendationType_id: Ext.getCmp('ufa_AdminBSKRecomendationWindow').params.Type,
                                                searchRecomendation_text: searchRecomendation_text,
                                                BSKObservElementValues_id : Ext.getCmp('ufa_AdminBSKRecomendationWindow').params.Value
                                         }                   
                                         
                                         Ext.getCmp('ufa_AdminBSKRecomendationWindow').gridRecomendation.getGrid().getStore().load({
                                            params: params
                                         });
                                         
                                         Ext.getCmp('ufa_AdminBSKRecomendationWindow').checkedRecomendation(params, false);
                                         
                                    },
                                    columnWidth: 0.1                                
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
                                    text: 'Сбросить',
                                    handler: function(){
                                        Ext.getCmp('searchRecomendation_text').setValue('');
                                         
                                         var params = { 
                                                BSKObservRecomendationType_id: Ext.getCmp('ufa_AdminBSKRecomendationWindow').params.Type,
                                                searchRecomendation_text: '',
                                                BSKObservElementValues_id : Ext.getCmp('ufa_AdminBSKRecomendationWindow').params.Value 
                                         }
                                        
                                         Ext.getCmp('ufa_AdminBSKRecomendationWindow').gridRecomendation.getGrid().getStore().load({
                                            params : params
                                         });  
                                         
                                         
                                          Ext.getCmp('ufa_AdminBSKRecomendationWindow').checkedRecomendation(params, false);
                                                                               
                                    },
                                    columnWidth: 0.1                                
                                                                    
                                }  
                              ]                                
                            }                                                 
                    ]
                 },
 
               this.gridRecomendation                                 
            ],
            buttons : 
            [
            
                {
                  text: 'Сохранить', 
                  handler: function(){
                    
                    var sel = Ext.getCmp('ufa_AdminBSKRecomendationWindow').gridRecomendation.getGrid().getSelectionModel().getSelections();
                    
                    //console.log(sel);
                    
                    var setRecomendation = [];
                    
                    for(k in sel){
                        if(typeof sel[k] == 'object'){
                            //console.log('>>>>', sel[k].data);
                            
                            setRecomendation.push(sel[k].data.BSKObservRecomendation_id);
                        }
                    }
                    
                    var BSKObservElementValues_id = Ext.getCmp('ufa_AdminBSKViewForm').GridValues.getGrid().getSelectionModel().getSelected().data.BSKObservElementValues_id;
                    var jsonSetRecomendation = Ext.util.JSON.encode(setRecomendation);
                    var BSKObservRecomendationType_id = Ext.getCmp('ufa_AdminBSKRecomendationWindow').params.Type;
                    
                    //console.log('setRecomendation: ', jsonSetRecomendation);
                    
                            Ext.Ajax.request({
                		 		url: '/?c=ufa_BSK_Register&m=saveRecomendation',
                				params: { 
                				    BSKObservElementValues_id: BSKObservElementValues_id,
                                    jsonSetRecomendation: jsonSetRecomendation,
                                    BSKObservRecomendationType_id : BSKObservRecomendationType_id
                				},
                				callback: function(options, success, response) {
                				     
                                     //console.log('success', success);
                                     
                				     if(success === true){
                				        Ext.getCmp('ufa_AdminBSKRecomendationWindow').hide();
                                        
                        				sw.swMsg.show(
                        				{
                        					icon: Ext.MessageBox.SUCCESS,
                        					title: 'Сообщение',
                        					msg: 'Рекомендации успешно сохранены!',
                        					buttons: Ext.Msg.OK
                        				});                                        
                                        
                				     }
                                     else{
                        				sw.swMsg.show(
                        				{
                        					icon: Ext.MessageBox.ERROR,
                        					title: 'Ошибка',
                        					msg: 'Произошла ошибка при попытки сохранения',
                        					buttons: Ext.Msg.OK
                        				});
                        				//return false;
                                     }
                                }
                            });        
                    
                    return; 
                  }  
                },            
                {
                  text: 'Отмена',
                  style: 'margin-left:720px',
                  handler: function(){
                    Ext.getCmp('ufa_AdminBSKRecomendationWindow').hide(); 
                  }  
                }
            ]            
		});        

        sw.Promed.ufa_AdminBSKViewForm.superclass.initComponent.apply(this, arguments);
    },

    show : function(params){
     
        this.checkedRecomendation(params, true);
        
        sw.Promed.ufa_AdminBSKViewForm.superclass.show.apply(this, arguments);
    },
    checkedRecomendation : function(params, setTitle){
        
        //console.log('PARAMS', params);
        
        if(setTitle === true){
            this.params = params;
            this.setTitle(this.params.ChangeTitle);
        }
        
        Ext.getCmp('ufa_AdminBSKRecomendationWindow').gridRecomendation.getGrid().getStore().load(
            {
                params:
                {
                    BSKObservRecomendationType_id: (setTitle === true) ? params.Type : params.BSKObservRecomendationType_id, 
                    BSKObservElementValues_id:     (setTitle === true) ? params.Value : params.BSKObservElementValues_id
                },
                callback : function(){

                    Ext.getCmp('ufa_AdminBSKRecomendationWindow').gridRecomendation.getGrid().getSelectionModel().deselectRange(0,100);

                    var BSKObservElementValues_id = Ext.getCmp('ufa_AdminBSKViewForm').GridValues.getGrid().getSelectionModel().getSelected().data.BSKObservElementValues_id;

                    var rstore = this;
                    console.log('rstore', rstore);
                    for(k in rstore.data.items){
                        if(typeof rstore.data.items[k] == 'object'){
                            var rec = rstore.data.items[k];
                            
                            console.log(rec.data.mark, rec.get('BSKObservElementValues_id'), rec)
                            if(rec.data.mark == 1 && Ext.getCmp('ufa_AdminBSKRecomendationWindow').params.Type == rec.data.BSKObservRecomendationType_id){
                                //console.log('MARK: ', rec.data.mark, 'TYPE_ID: ', rec.data.BSKObservRecomendationType_id,'/', Ext.getCmp('ufa_AdminBSKRecomendationWindow').params.Type, 'VAL_ID: ', BSKObservElementValues_id);
                                //console.log('YES: ',  rec);
                                
                                var indexRecord = rstore.indexOf(rec);                        
                            
                                Ext.getCmp('gridRecomendation').getGrid().getSelectionModel().selectRow(indexRecord, true);
                                return;
                            }
                            else{
                                //console.log('NO: ',  rec);
                            }
                        }
                    }                   
                }
            }
        )         
    },  
	refresh : function(){
	                var type_id = Ext.getCmp(this.gridID).gFilters.RegistryType_id; 
                    var targetGrid = Ext.getCmp(this.gridID);
   
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
	} ,   
    listeners : {
        'render' : function(){
            //console.log('>>>', this.ChangeTitle);
        },
		'hide': function() {
			if (this.refresh)
				this.onHide();
		}        
    }
});      

/**

Тестирование пройдено успешно.

Просьба добавить объект *[r2].[p_PersonRegister_ins]* на сервер ПРОГРЕССА (забрать с promed_develop или с тестового сервера Уфы)

Так же залить на сервер прогресса последние ревизии файлов

<pre>
\jscore\Forms\Admin\Ufa\ufa_personBskRegistryWindow.js
\jscore\Forms\Common\swBskRegistryWindow.js
\promed\models\Ufa_Bsk_Register_User_model.php
</pre>
*/ 