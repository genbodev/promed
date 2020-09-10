/**
 * swImportZLWindow - окно импорта Застрахованых лиц
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @package      Admin
 * @access       public
 * @copyright    Copyright (c) 2015 Swan Ltd.
 * @author       promed
 * @version      2015-03
 */
Ext.form.FileUploadField = Ext.extend(Ext.form.TextField,  {
    /**
     * @cfg {String} buttonText The button text to display on the upload button (defaults to
     * 'Browse...').  Note that if you supply a value for {@link #buttonCfg}, the buttonCfg.text
     * value will be used instead if available.
     */
    buttonText: 'Browse...',
    /**
     * @cfg {Boolean} buttonOnly True to display the file upload field as a button with no visible
     * text field (defaults to false).  If true, all inherited TextField members will still be available.
     */
    buttonOnly: false,
    /**
     * @cfg {Number} buttonOffset The number of pixels of space reserved between the button and the text field
     * (defaults to 3).  Note that this only applies if {@link #buttonOnly} = false.
     */
    buttonOffset: 3,
    /**
     * @cfg {Object} buttonCfg A standard {@link Ext.Button} config object.
     */

    // private
    readOnly: true,
    
    /**
     * @hide 
     * @method autoSize
     */
    autoSize: Ext.emptyFn,
    
    // private
    initComponent: function(){
        Ext.form.FileUploadField.superclass.initComponent.call(this);
        
        this.addEvents(
            /**
             * @event fileselected
             * Fires when the underlying file input field's value has changed from the user
             * selecting a new file from the system file selection dialog.
             * @param {Ext.form.FileUploadField} this
             * @param {String} value The file value returned by the underlying file input field
             */
            'fileselected'
        );
    },
    
    // private
    onRender : function(ct, position){
        Ext.form.FileUploadField.superclass.onRender.call(this, ct, position);
        
        this.wrap = this.el.wrap({cls:'x-form-field-wrap x-form-file-wrap'});
        this.el.addClass('x-form-file-text');
        this.el.dom.removeAttribute('name');
        
        this.fileInput = this.wrap.createChild({
            id: this.getFileInputId(),
            name: this.name||this.getId(),
            cls: 'x-form-file',
            tag: 'input', 
            type: 'file',
            size: 1
        });
        
        var btnCfg = Ext.applyIf(this.buttonCfg || {}, {
            text: this.buttonText
        });
        this.button = new Ext.Button(Ext.apply(btnCfg, {
            renderTo: this.wrap,
            cls: 'x-form-file-btn' + (btnCfg.iconCls ? ' x-btn-icon' : '')
        }));
        
        if(this.buttonOnly){
            this.el.hide();
            this.wrap.setWidth(this.button.getEl().getWidth());
        }
        
        this.fileInput.on('change', function(){
            var v = this.fileInput.dom.value;
            this.setValue(v);
            this.fireEvent('fileselected', this, v);
        }, this);
    },
    
    // private
    getFileInputId: function(){
        return this.id+'-file';
    },
    
    // private
    onResize : function(w, h){
        Ext.form.FileUploadField.superclass.onResize.call(this, w, h);
        
        this.wrap.setWidth(w);
        
        if(!this.buttonOnly){
            var w = this.wrap.getWidth() - this.button.getEl().getWidth() - this.buttonOffset;
            this.el.setWidth(w);
        }
    },
    
    // private
    preFocus : Ext.emptyFn,
    
    // private
    getResizeEl : function(){
        return this.wrap;
    },

    // private
    getPositionEl : function(){
        return this.wrap;
    },

    // private
    alignErrorIcon : function(){
        this.errorIcon.alignTo(this.wrap, 'tl-tr', [2, 0]);
    }
    
});
Ext.reg('fileuploadfield', Ext.form.FileUploadField);


sw.Promed.swImportZLWindow = Ext.extend(sw.Promed.BaseForm, {
	title: lang['import_dannyih_zl'],
	id: 'swImportZLWindow',
	height: 230,
	width: 450,
	maximized: false,
	maximizable: false,
	resizable: false,
	modal: true,
	show: function () {
		sw.Promed.swImportZLWindow.superclass.show.apply(this, arguments);
		this.callback = (arguments[0]&&arguments[0].callback)?arguments[0].callback:null;
		var base_form = this.findById('ImportZLForm').getForm();
		base_form.reset();
		if(arguments[0]&&arguments[0].RegisterList_id){
			base_form.findField('RegisterList_id').setValue(arguments[0].RegisterList_id)
		}else{
			base_form.findField('RegisterList_id').setValue(26)
		}
	},
	initComponent: function () {
		var that = this;
		Ext.apply(this, {
			buttons: [
				{
					text: lang['import'],
					tabIndex: -1,
					tooltip: lang['import'],
					iconCls: 'ok16',
					handler: function () {
						that.getLoadMask(lang['pojaluysta_podojdite_proizvoditsya_import']).show();
						that.findById('ImportZLForm').getForm().submit({
							success: function (form, action)
							{
								//log(form, action)
								sw.swMsg.alert('Информация',"Импорт данных ЗЛ будет проводиться в фоновом режиме. Результаты проведения импорта можно посмотреть в форме «Обновление регистров»");
								that.getLoadMask().hide();
								that.hide();
                               // window.open('/export/ImportIns'+getGlobalOptions().lpu_id+'.zip','_blank');
							},
							failure: function (form, action)
							{
								that.getLoadMask().hide();
                                //log(form,action);
                                if(action.result && action.result.Error_Msg){
									// sw.swMsg.alert('Ошибка', 'Ошибка при выполнении запроса на идентификацию человека', function() {this.buttons[2].focus();}.createDelegate(this) );
									var msg = action.result.Error_Msg.replace("<h1>","").replace("</h1>","").trim();//костыль
									sw.swMsg.alert(lang['oshibka'], msg);
                                }
                                //
								//that.hide();
							}
						});
					}
				},
				{
					text: '-'
				},
				HelpButton(this),
				{
					text: lang['otmena'],
					tabIndex: -1,
					tooltip: lang['otmena'],
					iconCls: 'cancel16',
					handler: function () {
						this.ownerCt.hide();
					}
				}
			],
			layout: 'border',
			items: [
				
				new Ext.form.FormPanel({
					region: 'center',
					height: 230,
					bodyStyle: 'padding: 5px;background-color:white;',
					border: false,
					buttonAlign: 'left',
					frame: true,
					id: 'ImportZLForm',
					labelAlign: 'right',
					labelWidth: 100,
					fileUpload: true,
					items: [
						{
							xtype: 'hidden',
							name:"RegisterList_id"
						},
						{
							xtype: 'fileuploadfield',
							fieldLabel:"Файл",
							buttonText:lang['otkryit'],
							name:"importFileZL",
							width:300

						}
					],
					keys: [
						{
							alt: true,
							fn: function (inp, e) {
								switch (e.getKey()) {
									case Ext.EventObject.C:
										if (this.action != 'view') {
											this.doSave(false);
										}
										break;
									case Ext.EventObject.J:
										this.hide();
										break;
								}
							},
							key: [ Ext.EventObject.C, Ext.EventObject.J ],
							scope: this,
							stopEvent: true
						}
					],
					params: {
						RegisterList_id: this.RegisterList_id,
						RegisterList_Name: this.RegisterList_Name
					},
					reader: new Ext.data.JsonReader({
						success: function () {
							//
						}
					}, [
						
					]),
					timeout: 999999999,
					url: '/?c=ImportInsured&m=Import'
				})
			]});
		sw.Promed.swImportZLWindow.superclass.initComponent.apply(this, arguments);
		/*this.findById('ImportZLForm').getForm().errorReader = {
			read: function (resp) {
				var result = false;
				that.getLoadMask().hide();
				try {
					result = Ext.decode(resp.responseText);
                   
				} catch (e) {
					 resp.responseText = resp.responseText.replace("<h1>","").replace("</h1>","").trim();//костыль
					 sw.swMsg.alert( lang['oshibka'],resp.responseText , function() {}.createDelegate(this) );
				}
				return result;
			}
		}*/
	}
});
