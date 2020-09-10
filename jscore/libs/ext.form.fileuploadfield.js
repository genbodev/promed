/*
 * Ext JS Library 2.2.1
 * Copyright(c) 2006-2009, Ext JS, LLC.
 * licensing@extjs.com
 * 
 * http://extjs.com/license
 */


Ext.form.FileUploadField = Ext.extend(Ext.form.TextField,  {
    /**
     * @cfg {Object} allowedExtensions A list of allowed file extensions
     */
    allowedExtensions: [],
    /**
     * @cfg {String} buttonText The button text to display on the upload button (defaults to
     * 'Browse...').  Note that if you supply a value for {@link #buttonCfg}, the buttonCfg.text
     * value will be used instead if available.
     */
    buttonText: langs('Открыть'),
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
    /**
     * @method setVisible
     */
    setVisible: function(f) {
      this.button.setVisible(f);
      this.fileInput.setVisible(f);
      this.wrap.setVisible(f);
    },
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

		var config = {
			id: this.getFileInputId(),
			name: this.name||this.getId(),
			cls: this.cls || 'x-form-file',
			tag: 'input', 
			type: 'file',
			style: (this.input && this.input.style)?this.input.style:null,
			size: 1
		};

		// Добавляем возможность указывать определенные маски файлов при выборе файла для загрузки
		if ( typeof this.allowedExtensions == 'string' ) {
			this.allowedExtensions = [ this.allowedExtensions ];
		}

		if ( typeof this.allowedExtensions == 'object' ) {
			var i, mimeTypes = [];

			for ( i in this.allowedExtensions ) {
				switch ( this.allowedExtensions[i] ) {
					case 'zip':
						mimeTypes.push('application/zip');
					break;
					case 'xml':
						mimeTypes.push('text/xml');
					break;
					case 'dbf':
						mimeTypes.push('.dbf');
					break;
					case 'gif':
						mimeTypes.push('image/gif');
					break;
					case 'jpeg': case 'jpg':
						mimeTypes.push('image/jpeg');
					break;
					case 'pjpeg':
						mimeTypes.push('image/pjpeg');
					break;
					case 'png':
						mimeTypes.push('image/png');
					break;
					
				}
			}

			if ( mimeTypes.length > 0 ) {
				config.accept = mimeTypes.join(',');
			}
		}

		this.fileInput = this.wrap.createChild(config);

        if (this.link) { // TODO: Все это можно будет доделать в дальнейшем, FF4 and more
          var btnCfg = Ext.applyIf(this.link || {});
          this.button = new Ext.Panel(Ext.apply(btnCfg, {
            renderTo: this.wrap
          }));
          var link = document.getElementById(this.link.linkId);
          //var fileElem = document.getElementById("fileElem");  
          var input = this.fileInput;
          link.addEventListener("click", function (e) {  
            log('click');
            /*
            if (input) {  
              input.el.click();
            }
            /*/
            e.preventDefault(); // prevent navigation to "#"  
          }, false);  
        } else {
          var btnCfg = Ext.applyIf(this.buttonCfg || {}, {
            text: this.buttonText
          });
          this.button = new Ext.Button(Ext.apply(btnCfg, {
            renderTo: this.wrap,
            cls: 'x-form-file-btn' + (btnCfg.iconCls ? ' x-btn-icon' : '')
          }));
        }
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