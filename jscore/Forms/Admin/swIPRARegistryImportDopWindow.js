/**
* swIPRARegistryImportDopWindow  - импорт доп.полей ИПРА из XML упакованных в ZIP
*/

sw.Promed.swIPRARegistryImportDopWindow = Ext.extend(sw.Promed.BaseForm, {
    alwaysOnTop: true,
	id    : 'swIPRARegistryImportDopWindow', 
	objectName    : 'swIPRARegistryImportDopWindow',
	objectSrc     : '/jscore/Forms/Admin/swIPRARegistryImportDopWindow.js',
	layout: 'form',
	buttonAlign: 'center',
	title : 'Импорт доп. полей в регистр ИПРА',
	modal : true,
	width : 340,
    fieldWidth:40,
	autoHeight : true,
    closable : true,
    resizable: false,
    bodyStyle:'padding:10px',
	closeAction   : 'hide',
	draggable     : true,
	initComponent: function() 
	{      
		var form = this;		
		Ext.apply(this, 
		{   
			autoHeight: true,                        
            buttonAlign: 'right', 
            buttons: [
                {
                    text: 'Импортировать',
                    handler: function(){
                        if (Ext.getCmp('importZipForm').getForm().isValid()) {
                            
                    		var loadMask = new Ext.LoadMask(form.getEl(), {msg: "Подождите..."});
                    		loadMask.show();
                            Ext.getCmp('importZipForm').getForm().submit({
                                url: '/?c=IPRARegister&m=IPRARegistryDopImport',
                                success: function(form, answer) {          
									loadMask.hide();
									sw.swMsg.alert('Сообщение', 'Импорт успешно произведён');
                                }, 
                                failure: function(form, answer) {
                                    loadMask.hide();
									sw.swMsg.alert('Сообщение', 'Произошла ошибка при импорте');
                                }
                            });
                        }
                        else {
                            Ext.Msg.alert('Ошибка ввода', 'Выберите архив ИПРА для импорта');
                        }                    
                    }                         
                },
                {
                    text: 'Отмена',
                    handler: function(){
                        Ext.getCmp('importZipForm').getForm().reset();
                        Ext.getCmp('swIPRARegistryImportDopWindow').refresh();                                 
                    }
                }
            ],
            items : [
                new Ext.FormPanel({
                    bodyStyle:'padding:5px 5px 0',                                                    
                    layout: 'form',
                    id:'importZipForm',
                    frame: true,
                    fileUpload: true,
                    items: [
                        {
                            //xtype: 'fileuploadfield',
                            xtype: 'textfield',
                            inputType: 'file',
                            autoCreate: { tag: 'input', name: 'IPRARegistry_import', type: 'text', size: '20', autocomplete: 'off' },
                            name: 'IPRARegistry_import',
                            regex: /^.*\.(zip)$/, 
                            hideLabel:true,
                            regexText:'Вводимый файл должен быть архивом zip',
                            //allowBlank: false,
                            width:210,
                        }
                    ]
                })
            ],
		});
		sw.Promed.swIPRARegistryImportDopWindow.superclass.initComponent.apply(this, arguments);
	},
	refresh : function(){
			      var objectClass = this.objectClass;
				  var lastArguments = this.lastArguments;
				  sw.codeInfo.lastObjectName = this.objectName;
				  sw.codeInfo.lastObjectClass = this.objectClass;
				
                  if (sw.Promed.Actions.loadLastObjectCode){
					  sw.Promed.Actions.loadLastObjectCode.setHidden(false);
				  }
				
                  this.hide();
				  this.destroy();
				  window[this.objectName] = null;
				  delete sw.Promed[this.objectName];

				  if (sw.ReopenWindowOnRefresh) {
					  getWnd(objectClass).show(lastArguments);
				  }
	},      
	close : function(){	
 
            this.hide();
            this.destroy();
            window[this.objectName] = null;
            delete sw.Promed[this.objectName];

    },    
	show: function(params) 
	{   
            sw.Promed.swIPRARegistryImportDopWindow.superclass.show.apply(this, arguments);
	}

});