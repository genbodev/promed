/**
* ext.ux.remotevalidator.js - Плагин для валидации поля через сервер
*
*  url : String (Optional)
*  params : Object/String/Function
*  method : String (Optional)
*  callback : Function (Optional)
*    options : Object
*    success : Boolean
*    response : Object
*  success : Function (Optional)
*    response : Object
*    options : Object
*  failure : Function (Optional)
*    response : Object
*    options : Object
*  scope : Object (Optional)
*  form : Object/String (Optional)
*  isUpload : Boolean (Optional)
*  headers : Object (Optional)
*  xmlData : Object (Optional)
*  jsonData : Object/String (Optional)
*  disableCaching : Boolean (Optional
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Expression package is undefined on line 8, column 19 in Templates/Other/javascript.js.
* @access       public
* @copyright    Copyright (c) 2010 Swan Ltd.
* @author       yunitsky
* @version      15.07.2010
 */

Ext.namespace('Ext.ux', 'Ext.ux.plugins');

/**
 * Remote Validator
 * Makes remote (server) field validation easier
 *
 * To be used by form fields like TextField, NubmerField, TextArea, ...
 */
Ext.ux.plugins.RemoteValidator = {
    init:function(field) {
        // save original functions
        var isValid = field.isValid;
        var validate = field.validate;

        // apply remote validation to field
        Ext.apply(field, {
             remoteValid: true

            // private
            ,isValid:function(preventMark) {
                if(this.disabled) {
                    return true;
                }
                return isValid.call(this, preventMark) && this.remoteValid;
            }

            // private
            ,validate:function() {
                var clientValid = validate.call(this);
                if(!this.disabled && !clientValid) {
                    return false;
                }
                if(this.disabled || (clientValid && this.remoteValid)) {
                    this.clearInvalid();
                    return true;
                }
                if(!this.remoteValid) {
                    this.markInvalid(this.reason);
                    return false;
                }
                return false;
            }

            // private - remote validation request
            ,validateRemote:function() {
                this.rvOptions.params = this.rvOptions.params || {};
                this.rvOptions.params.field = this.name;
                this.rvOptions.params.value = this.getValue();
				if ((this.xtype) && (this.xtype=='textarea')){ //Добавлено в рамках задачи http://redmine.swan.perm.ru/issues/24673.
																// У textarea первоначальное значение хранится в value, а не в originalValue
					this.rvOptions.params.original = this.value;
				}
				else{
					this.rvOptions.params.original = this.originalValue;
				}	
                Ext.Ajax.request(this.rvOptions);
            }

            // private - remote validation request success handler
            ,rvSuccess:function(response, options) {
                var o;
                try {
                    o = Ext.decode(response.responseText);
                }
                catch(e) {
                    throw this.cannotDecodeText;
                }
                if('object' !== typeof o) {
                    throw this.notObjectText;
                }
                if(true !== o.success) {
                    throw this.serverErrorText + ': ' + o.error;
                }
                var names = this.rvOptions.paramNames;
                this.remoteValid = true === o[names.valid];
                this.reason = o[names.reason];
                this.validate();
            }

            // private - remote validation request failure handler
            ,rvFailure:function(response, options) {
                throw this.requestFailText
            }

            // private - runs from keyup event handler
            ,filterRemoteValidation:function(e) {
                if(!e.isNavKeyPress()) {
                    this.remoteValidationTask.delay(this.remoteValidationDelay);
                }
            }
        });

        // remote validation defaults
        Ext.applyIf(field, {
             remoteValidationDelay:500
            ,reason:'Значение еще не проверено сервером'
            ,cannotDecodeText:'Cannot decode json object'
            ,notObjectText:'Server response is not an object'
            ,serverErrorText:'Server error'
            ,requestFailText:'Server request failed'
        });

        // install event handlers on field render
        field.on({
            render:{single:true, scope:field, fn:function() {
                this.remoteValidationTask = new Ext.util.DelayedTask(this.validateRemote, this);
                this.el.on(this.validationEvent, this.filterRemoteValidation, this);
            }}
        });

        // setup remote validation request options
        field.rvOptions = field.rvOptions || {};
        Ext.applyIf(field.rvOptions, {
             method:'post'
            ,scope:field
            ,success:field.rvSuccess
            ,failure:field.rvFailure
            ,paramNames: {
                 valid:'valid'
                ,reason:'reason'
            }
        });
    }
};
