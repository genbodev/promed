/**
* swComponentLibTextfields - классы текстовых полей.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      libs
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Pshenicyn Ivan aka IVP (ipshon@rambler.ru)
* @version      24.02.2009
*/

// класс поля ввода c ввозможностью ввода на латинице, независимо от раскладки клавиатуры
sw.Promed.TextFieldPMEN = Ext.extend(Ext.form.TextField, {
	translated: true,
	toUpperCase: false,
	enableKeyEvents: true,
	initComponent: function () {
		sw.Promed.TextFieldPMEN.superclass.initComponent.apply(this, arguments);
		if (this.translated)
		{
			var upperCase = this.toUpperCase;
			this.plugins = this.initPlugin(new Ext.ux.translit2en(true, upperCase));
		}
	},
	onRender: function () {
		sw.Promed.TextFieldPMEN.superclass.onRender.apply(this, arguments);
	}
});

Ext.reg('textfieldpmen', sw.Promed.TextFieldPMEN);

// класс поля ввода c ввозможностью ввода на кириллице, независимо от раскладки клавиатуры
sw.Promed.TextFieldPMW = Ext.extend(Ext.form.TextField, {
	translated: true,
	toUpperCase: false,
	enableKeyEvents: true,
	initComponent: function() {
		sw.Promed.TextFieldPMW.superclass.initComponent.apply(this, arguments);
		if ( this.translated )
		{
			var upperCase = this.toUpperCase;
			this.plugins = this.initPlugin(new Ext.ux.translit(true, upperCase));
		}
  },
	onRender:function() {
		sw.Promed.TextFieldPMW.superclass.onRender.apply(this, arguments);
	}
});

Ext.reg('textfieldpmw', sw.Promed.TextFieldPMW);

// класс поля ввода c ввозможностью ввода на кириллице, независимо от раскладки клавиатуры

sw.Promed.TextAreaPMW = Ext.extend(Ext.form.TextArea, {
	translated: true,
	toUpperCase: false,
	enableKeyEvents: true,
	initComponent: function() {
		sw.Promed.TextAreaPMW.superclass.initComponent.apply(this, arguments);
		if ( this.translated )
		{
			var upperCase = this.toUpperCase;
			this.plugins = this.initPlugin(new Ext.ux.translit(true, upperCase));
		}
  },
	onRender:function() {
		sw.Promed.TextAreaPMW.superclass.onRender.apply(this, arguments);
	}
});
Ext.reg('textareapmw', sw.Promed.TextAreaPMW);


/**
* swTextFieldDescription - класс текстового поля описания.
*
* @access public
* @author Марков Андрей
* @xtype 'descfield'
*/
sw.Promed.swTextFieldDescription = Ext.extend(sw.Promed.TextFieldPMW,
{
	/*
	translated: true,
 	enableKeyEvents: true,
	*/
	anchor: '100%',
	disabled: true,
	//labelStyle: 'font-size: 10pt;',
	initComponent: function()
	{
		sw.Promed.swTextFieldDescription.superclass.initComponent.apply(this, arguments);
	},
	onRender:function()
	{
		sw.Promed.swTextFieldDescription.superclass.onRender.apply(this, arguments);
	}
});

Ext.reg('descfield', sw.Promed.swTextFieldDescription);

// класс поля ввода c ввозможностью ввода на кириллице, независимо от раскладки клавиатуры

sw.Promed.translatedTextFieldParams = {
	translated: true,
	enableKeyEvents: true,
	replaceSymbols: false,
	toUpperCase: true,

	initComponent: function() {
		sw.Promed.swTranslatedTextField.superclass.initComponent.apply(this, arguments);
		if ( this.translated )
		{
			this.plugins = this.initPlugin(new Ext.ux.translit(true, this.toUpperCase, this.replaceSymbols));
		}
	},
	onRender:function() {
		sw.Promed.swTranslatedTextField.superclass.onRender.apply(this, arguments);
	}
};

sw.Promed.translatedTextFieldParamsWithApostrophe = sw.Promed.translatedTextFieldParams;
sw.Promed.translatedTextFieldParamsWithApostrophe.replaceSymbols = { "'" : '\'', '`' : '\`', '\.':'\.' };
sw.Promed.translatedTextFieldParamsWithApostrophe.toUpperCase  = false;

sw.Promed.swTranslatedTextField = Ext.extend(Ext.form.TextField, sw.Promed.translatedTextFieldParams);
sw.Promed.swTranslatedTextFieldWithApostrophe = Ext.extend(Ext.form.TextField, sw.Promed.translatedTextFieldParamsWithApostrophe);

Ext.reg('swtranslatedtextfield', sw.Promed.swTranslatedTextField);
Ext.reg('swtranslatedtextfieldwithapostrophe', sw.Promed.swTranslatedTextFieldWithApostrophe);

/**
* swTextFieldEmk - класс поля для редактирования данных в панели просмотра ЭМК.
*
* @access public
* @author Пермяков Александр
* @xtype 'emkfield'
* @todo сделать визуальное рeдактированиe с глоссарием
*/
sw.Promed.swTextFieldEmk = Ext.extend(Ext.form.TextArea,
{
	autoHeight: true,
	hideLabel: true,
	enableKeyEvents: true,
	style: 'border: 0; width: 100%; padding:0; margin:0; background: #fff; resize: vertical;',
	EvnXml_id: null,
	grow: true,
	growMin: 16,
	growMax: 3000,
	saveValue:function()
	{
		Ext.Ajax.request({
			url: '/?c=Template&m=saveEvnXmlNode',
			callback: function(opt, success, response) {
				if (success && response.responseText != '') {
					var response_obj = Ext.util.JSON.decode(response.responseText);
					//log(response_obj);
				}
			},
			params: {EvnXml_id: this.EvnXml_id, name: this.name, value: this.getValue()}
		});
	},
    /*autoSize: function(){
        if(!this.grow || !this.textSizeEl){
            return;
        }
        var el = this.el;
        var v = el.dom.value;
        var ts = this.textSizeEl;
        ts.innerHTML = '';
        ts.appendChild(document.createTextNode(v));
        v = ts.innerHTML;
        Ext.fly(ts).setWidth(this.el.getWidth());
        if(v.length < 1){
            v = "&#160;&#160;";
        }else{
            v += this.growAppend;
            if(Ext.isIE){
                v = v.replace(/\n/g, '<br />');
            }
        }
        ts.innerHTML = v;
        var h = Math.min(this.growMax, Math.max(ts.offsetHeight, this.growMin) + this.growPad);
		if(this.myHeight)
			h = this.myHeight
		//log([this.name,ts,ts.offsetHeight]);
        if(h != this.lastHeight){
            this.myHeight = false;
			this.lastHeight = h;
            this.el.setHeight(h);
            this.fireEvent("autosize", this, h);
        }
    },*/
	initComponent: function()
	{
		sw.Promed.swTextFieldEmk.superclass.initComponent.apply(this, arguments);
		this.on('change',function(field,n,o){
			field.saveValue();
		});
		/* 
		this.on('autosize',function(field,n){
			log('autosize');
			log(arguments);
			log(field.textSizeEl);
			log(field.lastHeight);
		});
			log(field.height);
			field.setHeight(field.height);
			field.syncSize();
		this.on('keydown',function(field, evn){
			log('keydown');
			log(arguments);
		});
		this.on('focus',function(field){
			log('focus');
		});
		this.on('blur',function(field){
			log('blur');
		});
		this.on('beforeshow',function(field){
			log('beforeshow');
		});
		this.on('autosize',function(field,n,m){
			log('autosize');
			log(arguments);
			//log(field.getValue());  
			//log(field.textSizeEl);
			//log(field.lastHeight);
		});  
		this.on('resize',function(field){
			log('resize');
		});
		*/
		this.on('render',function(field){
			field.setValue(field.value);
			//field.autoSize();
			//log('render');
			//log(field.getEl());//  getComputedHeight()  getHeight()
			//log(field.getValue());
			//field.focus(true,0);
		});
	},
	onRender:function()
	{
		sw.Promed.swTextFieldEmk.superclass.onRender.apply(this, arguments);
	}
});

Ext.reg('emkfield', sw.Promed.swTextFieldEmk);


sw.Promed.swSnilsField = Ext.extend(Ext.Panel,{
	allowBlank: true,
	fieldLabel: langs('СНИЛС'),
	name: 'Person_Snils',
	onChange: function(field, newValue) {},

	initComponent: function() {
		var that = this;

		this.updateHiddenValue = function (){
			var v = that.snilsEditField.getValue();
			//var arr = v.split('-');
			//var result = arr.join('');
			var result = (v.split('-').join('')).split(' ').join('');
			if ( !(/^\d{11}$/.test(result)) ) {
				result = '';
			};
			that.hiddenField.setValue(result);
		};

		this.snilsEditField = new Ext.form.TextField({
			allowBlank: that.allowBlank,
			fieldLabel: that.fieldLabel,
			name: that.name,
			width: that.fieldWidth,
			labelWidth: that.labelWidth,
			readOnly: that.readOnly,
			anchor: that.anchor,
			plugins: [new Ext.ux.InputTextMask((getRegionNick()=='astra')?'999-999-999 99':'999-999-999-99', true)],
			tabIndex: that.tabIndex,
			listeners: {
				'change': function (){
					that.updateHiddenValue();
				}
			}
		});

		this.hiddenField = new Ext.form.Hidden({
			tag:'input',
			type:'hidden',
			name: that.name + '_Hidden',
			id: (this.hiddenId||this.hiddenName),
			setValue: function (){
				Ext.form.Hidden.superclass.setValue.apply(this, arguments);
				this.fireEvent('change');
			},
			listeners:{
				'change': function (){
					that.setValue(this.value);
					that.onChange(that, this.value);
				}
			}
		});

		Ext.apply(this, {
			layout: 'form',
			border: false,
			bodyStyle:'background: transparent',
			getValue: function () {
				return that.hiddenField.getValue();
			},
			setValue: function (value) {
				var v = '';
				var inp = '';
				if (value) {
					inp = value.toString().substr(0, 11);
				}

				var regexp = /^(\d{3})(\d{3})(\d{3})(\d{2})$/;

				if ( !regexp.test(inp) ) {
					v = '';
					inp = '';
				} else {
					v = inp.replace(regexp, (getRegionNick()=='astra')?'$1-$2-$3 $4':'$1-$2-$3-$4');
				}
				that.snilsEditField.setValue(v);
				that.hiddenField.value = inp;
			},
			items: [
				this.hiddenField,
				this.snilsEditField
			]
		});

		sw.Promed.swSnilsField.superclass.initComponent.apply(this, arguments);
	}
});
Ext.reg('swsnilsfield', sw.Promed.swSnilsField);


sw.Promed.swPhoneField = Ext.extend(Ext.Panel,{
	allowBlank: true,
	fieldLabel: langs('Телефон')+'  +7',
	name: 'PersonPhone_Phone',
	enableKeyEvents: false,
	onChange: function(field, newValue) {},
	onKeyUp: function(inp, e) {},

	initComponent: function() {
		var that = this;

		this.updateHiddenValue = function (){
			var v = that.phoneEditField.getValue();
			//var arr = v.split('-');
			//var result = arr.join('');
			var result = ((v.split('-').join('')).split('(').join('')).split(')').join('');
			if ( !(/^\d{10}$/.test(result)) ) {
				result = '';
			};
			that.hiddenField.setValue(result);
		};

		this.phoneEditField = new Ext.form.TextField({
			allowBlank: that.allowBlank,
			fieldLabel: that.fieldLabel,
			name: that.name,
			width: that.fieldWidth,
			labelWidth: that.labelWidth,
			labelSeparator: that.labelSeparator,
			readOnly: that.readOnly,
			anchor: that.anchor,
			plugins: [new Ext.ux.InputTextMask('(999)-999-99-99', true)],
			tabIndex: that.tabIndex,
			enableKeyEvents: that.enableKeyEvents,
			useCases: that.useCases,
			listeners: {
				'change': function (){
					that.updateHiddenValue();
				},
				'keyup': function (inp, e){
					that.onKeyUp(inp, e);
				}
			}
		});

		this.hiddenField = new Ext.form.Hidden({
			tag:'input',
			type:'hidden',
			name: that.name + '_Hidden',
			id: (this.hiddenId||this.hiddenName),
			setValue: function (){
				Ext.form.Hidden.superclass.setValue.apply(this, arguments);
				this.fireEvent('change');
			},
			listeners:{
				'change': function (){
					that.setValue(this.value);
					that.onChange(that, this.value);
				}
			}
		});

		Ext.apply(this, {
			layout: 'form',
			border: false,
			bodyStyle:'background: transparent',
			getValue: function () {
				return that.hiddenField.getValue();
			},
			setValue: function (value) {
				var v = '';
				var inp = '';
				if (value) {
					inp = value.toString().substr(0, 10);
				}

				var regexp = /^(\d{3})(\d{3})(\d{2})(\d{2})$/;

				if ( !regexp.test(inp) ) {
					v = '';
					inp = '';
				} else {
					v = inp.replace(regexp,'($1)-$2-$3-$4');
				}
				that.phoneEditField.setValue(v);
				that.hiddenField.value = inp;
			},
			items: [
				this.hiddenField,
				this.phoneEditField
			]
		});

		sw.Promed.swPhoneField.superclass.initComponent.apply(this, arguments);
	}
});
Ext.reg('swphonefield', sw.Promed.swPhoneField);

sw.Promed.swHtmlEditor = Ext.extend(Ext.form.HtmlEditor, {
	onDisable: function(){
		if(this.rendered){
			var roMask = this.wrap.mask();
			roMask.dom.innerHTML = this.getValue();
			roMask.dom.style['background'] = "white";
			roMask.dom.style['opacity'] = 1;
			roMask.dom.style['height'] = this.el.getHeight();
			roMask.dom.style['overflow-y'] = "scroll";
			this.el.dom.readOnly = true;
		}
		Ext.form.HtmlEditor.superclass.onDisable.call(this);
	},
	onEnable: function(){
		if(this.rendered){
			this.wrap.unmask();
		}
		Ext.form.HtmlEditor.superclass.onEnable.call(this);
	},
	getPersonId: function() {
		return null;
	},
	createToolbar: function (editor) {
		sw.Promed.swHtmlEditor.superclass.createToolbar.apply(this, arguments);

		editor.tb.add(
			'-',
			{
				itemId: 'insertdoc',
				cls: 'x-btn-icon x-edit-insertdoc',
				enableToggle: false,
				scope: editor,
				handler: function () {
					var params = {
						onHide: function() {
							//возвращаем фокус в редактор
							editor.focus();
						},
						callback: function(data) {
							if(typeof data != 'object') {
								return false;
							}
							if(data.range && !data.range.collapsed) {
								var frag = data.range.cloneContents();
								var div = document.createElement('div');
								div.appendChild( frag.cloneNode(true) );
								var text = div.innerHTML;
							} else {
								//при нажатии на кнопку выбор ничего не было выделено, вставляем весь текст документа
								var text = data.wholeDoc;
							}
							editor.execCmd('InsertHTML', text);
							return true;
						},
						Person_id: editor.getPersonId()
					};

					setTimeout(function () { // костыль, чтобы форма не ушла после открытия под текущую форму.
						getWnd('swEmkDocumentsListWindow').show(params);
					}, 200);
				},
				clickEvent: 'mousedown',
				tooltip: 'Вставить документ/фрагмент документа',
				tabIndex: -1
			}
		);

		editor.tb.add(
			{
				itemId: 'expand',
				cls: 'x-btn-icon x-edit-expand',
				enableToggle: false,
				scope: editor,
				handler: function () {
					if (editor.expandButtonFn) {
						editor.expandButtonFn();
					} else {
						var params = {
							onHide: function() {
								//возвращаем фокус в редактор
								editor.focus();
							},
							callback: function(data) {
								editor.setValue(data);
							},
							Person_id: editor.getPersonId(),
							value: editor.getValue(),
							title: editor.fieldLabel
						};

						setTimeout(function() { // костыль, чтобы форма не ушла после открытия под текущую форму.
							getWnd('swHTMLEditorWindow').show(params);
						}, 200);
					}
				},
				clickEvent: 'mousedown',
				tooltip: editor.expandButtonFn?'Скрыть':'Раскрыть',
				tabIndex: -1
			}
		);
	}
});

Ext.reg('swhtmleditor', sw.Promed.swHtmlEditor);