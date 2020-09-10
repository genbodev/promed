/**
* sw.Promed.FormPanelWithChangeEvents - класс формы с обработкой события изменения поля.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      libs
* @access       public
* @copyright    Copyright (c) 2009-2010 Swan Ltd.
* @author       Pshenicyn Ivan aka IVP (ipshon@rambler.ru)
* @version      22.06.2010
*/

/****************************************************
 
* CKEditor Extension
 
*****************************************************/
 
Ext.form.CKEditor = function(config){
 
    this.config = config;
 
    Ext.form.CKEditor.superclass.constructor.call(this, config);
    this.on('destroy', function (ct) {
    	ct.destroyInstance();
    });
 
};
 
 
Ext.extend(Ext.form.CKEditor, Ext.form.TextArea,  {
 
	labelStyle: 'font-weight:bold;',
    onRender : function(ct, position){
 
        if(!this.el){
 
            this.defaultAutoCreate = {
 
                tag: 'textarea',
 
                autocomplete: 'off'
 
            };
 
        }
 
        Ext.form.TextArea.superclass.onRender.call(this, ct, position);
        if (!this.config.CKConfig) this.config.CKConfig = {};
        var defConfig = {
			 resize_enabled : false,
			 on : {
	         // maximize the editor on startup
	         'instanceReady' : function( evt ) {
	            evt.editor.resize((evt.editor.element.$.style.width ? evt.editor.element.$.style : '100%'), parseInt(evt.editor.element.$.style.height));
	            evt.editor.is_instance_ready = true;
	         }
	      }
        };
        Ext.apply(this.config.CKConfig, defConfig);
        CKEDITOR.replace(this.id, this.config.CKConfig);
 
    },
 
    onResize: function(width, height) {
    	Ext.form.TextArea.superclass.onResize.call(this, width, height);    	
    	if (CKEDITOR.instances[this.id].is_instance_ready) {
    		CKEDITOR.instances[this.id].resize(width, height);
    	}		
    },
 
    setValue : function(value){
    	if (!value) value = '&nbsp;'; 
 
        Ext.form.TextArea.superclass.setValue.apply(this,arguments);
 
        if (CKEDITOR.instances[this.id]) CKEDITOR.instances[this.id].setData( value );
 
    },
 
	getCKEditor: function() {
		if ( CKEDITOR.instances[this.id] )
			return CKEDITOR.instances[this.id];
		else
			return false;			
	},
 
    getValue : function(){
 
        if (CKEDITOR.instances[this.id]) CKEDITOR.instances[this.id].updateElement();
        return Ext.form.TextArea.superclass.getValue.call(this);
 
    },
 
	getRawValue : function(){
 
        if (CKEDITOR.instances[this.id]) CKEDITOR.instances[this.id].updateElement();    
 
        return Ext.form.TextArea.superclass.getRawValue.call(this);
 
    },
 
	destroyInstance: function(){
        if (CKEDITOR.instances[this.id]) {
            delete CKEDITOR.instances[this.id];
        }
    } 
 
 
});
 
Ext.reg('ckeditor', Ext.form.CKEditor);