/**
 * Пакет библиотек классов для редактирования документов
 *
 * @author		Permyakov Alexander
 * @version		07.2013
 */

/**
 * Библиотека классов для объектов с типом Параметр и список значений (ParameterValue)
 *
 * @author		Permyakov Alexander
 */

/**
* Комбобокс для выбора одного значения параметра
*/
sw.Promed.swParameterValueCombo = Ext.extend(sw.Promed.swStoreInConfigCombo,
{
	onChanged: Ext.emptyFn,
	valueField: 'Value_id',
	displayField: 'Value_Name',
	hiddenName: 'Value_id',
	storeKey: 'Value_id',
	comboData: [],
	comboFields: [
		{name: 'Value_id', type:'int'},
		{name: 'Value_Name', type:'string'}
	],
	fieldLabel: '',
	hideLabel: true,
	mode: 'local',
	width: 200,
    //style: 'backgroundColor: transparent;',
    listWidth: 300,
	editable: true,
	listeners:
	{
		blur: function(f) {
			//
		},
		render: function(f) {
			var id = null;
			if(f.getStore() && f.getStore().getCount() > 0 && f.value > 0) {
				id = f.getStore().getById(f.value) ? f.value : null;
			}
			f.setValue(id);
		},
		change: function(f,n,o) {
			if(!f.getStore() || f.getStore().getCount() == 0) {
				return false;
			}
			//log([n,f.getStore()]);
			var rec = (n)?f.getStore().getById(n):false;
			var value;
			var text;
			if(rec) {
				value = n;
				text = rec.get(f.displayField);
			} else {
				value = 0;
				text = langs('не указано');
			}
			f.onChanged({
				value_id_list: value
				,value_name_list: text
			});
		}
	},
	initComponent: function() {
        var len = this.fieldLabel.length;
        if (len>0) {
            this.labelStyle = 'width: 150px;';
        }
		sw.Promed.swParameterValueCombo.superclass.initComponent.apply(this, arguments);
	}
});
Ext.reg('swparametervaluecombo', sw.Promed.swParameterValueCombo);

/**
* Радиогруппа для выбора одного значения параметра
*/
sw.Promed.swParameterValueRadioGroup = Ext.extend(Ext.form.RadioGroup,
{
	onChanged: Ext.emptyFn,
	itemsData: [],
	value: null,
	name: 'Value_id',
	fieldLabel: '',
	hideLabel: true,
    //style: 'backgroundColor: transparent;',
	//columns: 1,
	listeners:
	{
		render: function(cmp) {
			//
		},
		change: function(cmp,checked_list) {
			//log(['radiogroup change',arguments]);
		}
	},
	getValue: function() {
		return this.value;
	},
	initComponent: function() {
		//this.id = '';
		this.itemsData = Ext.isArray(this.itemsData) ? this.itemsData : [];
		this.items = [];
		for(var i=0; i < this.itemsData.length; i++)
		{
			var value = this.itemsData[i][0];
            var label = this.itemsData[i][1]+((this.itemsData.length-1==i)?'.':';');
			var item_config = {boxLabel: label, value: value, name: this.name, checked: (value == this.value), id: this.id +'_'+ value};
			if(typeof this.toggleValue == 'function') {
				item_config.ownerCmp = this;
				item_config.toggleValue = this.toggleValue;
			}
			this.items.push(new Ext.form.Radio(item_config));
		}
        var len = this.fieldLabel.length;
        if (len>0) {
            this.labelStyle = 'width: 150px;';
        }
		sw.Promed.swParameterValueRadioGroup.superclass.initComponent.apply(this, arguments);
	}
});		
Ext.reg('swparametervalueradiogroup', sw.Promed.swParameterValueRadioGroup);

/**
* Группа чекбоксов для выбора одного или нескольких значений параметра
*/
sw.Promed.swParameterValueCheckboxGroup = Ext.extend(Ext.form.CheckboxGroup,
{
	onChanged: Ext.emptyFn,
	itemsData: [],
	value: null,
	name: 'Value_id',
	fieldLabel: '',
	hideLabel: true,
    //style: 'backgroundColor: transparent;',
	//columns: 1,
	listeners:
	{
		render: function(cmp) {
			//
		},
		change: function(cmp,checked_list) {
			//log(['CheckboxGroup change',arguments,cmp.getValue()]);
			/*
			*/
		}
	},
	getValue: function() {
		var out = [];
		this.items.each(function(item){
			if(item.checked){
				out.push(item.value);
			}
		});
		this.value = out.join(',');
		return this.value;
	},
	initComponent: function() {
		//this.id = '';
		this.itemsData = Ext.isArray(this.itemsData) ? this.itemsData : [];
		this.items = [];
		var value_id_list = this.value.toString().split(',');
		for(var i=0; i < this.itemsData.length; i++)
		{
			var value = this.itemsData[i][0];
            var label = this.itemsData[i][1]+((this.itemsData.length-1==i)?'.':';');
			var item_config = {boxLabel: label, value: value, name: this.name, checked: (value.toString().inlist(value_id_list)), id: this.id +'_'+ value};
			if(typeof this.toggleValue == 'function') {
				item_config.ownerCmp = this;
				item_config.toggleValue = this.toggleValue;
			}
			this.items.push(new Ext.form.Checkbox(item_config));
		}
        var len = this.fieldLabel.length;
        if (len>0) {
            this.labelStyle = 'width: 150px;';
        }
		sw.Promed.swParameterValueCheckboxGroup.superclass.initComponent.apply(this, arguments);
	}
});
Ext.reg('swparametervaluecheckboxgroup', sw.Promed.swParameterValueCheckboxGroup);

/**
* Класс поля объекта Параметр и список значений
*/
sw.Promed.ParameterValue = function (config) {
	/**
	* Идентификатор EvnXml документа
	* @var integer
	*/
	this.EvnXml_id = null;
	
	/**
	* Имя поля, которое уникально в рамках документа, как результат контакенации строки "parameter" с идентификатором параметра
	* @var string
	* @example parameter14
	*/
	this.fieldName = null;
	
	/**
	* Имя поля, которое уникально в рамках случая лечения, как результат контакенации строки "parameter" с идентификатором параметра, со строкой "_" и с идентификатором EvnXml документа
	* @var string
	* @example parameter14_1734
	*/
	this.key = null;
	
	/**
	* Объект с данными параметра и списком его возможных значений
	* @var object
	* @example {"parameter_id":14,"parameter_name":"Сознание","listtype":"radiogroup","value":"41","values":{"42":"отсутствует","41":"спутанное","15":"Ясное"}}
	*/
	this.data = null;

    /**
     * Элемент-контейнер для кнопок.
     * @var Ext.Element
     * @example <div id="buttons_parameter487_4613" style="display: none;"></div>
     */
    this.buttonsEl = null;

    /**
     * Элемент-контейнер для компонента выбора значения. Виден в панели просмотра ЭМК, при печати скрыт.
     * @var Ext.Element
     * @example <div id="input_parameter14_1734" class="radiogroup-parameter"></div>
     */
    this.inputEl = null;

	/**
	* Элемент содержаший список наименований выбранных значений, видим только при печати
	* @var Ext.Element
	* @example <span id="output_parameter14_1734" class="value-parameter" style="display: none;">спутанное</span>
	*/
	this.outputEl = null;
	
	/**
	* Элемент содержаший json-строку с данными параметра и списком его возможных значений
	* @var Ext.Element
	* @example <div id="json_parameter14_1734" class="parametervalue" style="display: none;">{"parameter_id":14,"parameter_name":"Сознание","listtype":"radiogroup","value":"41","values":{"42":"отсутствует","41":"спутанное","15":"Ясное"}}</div>
	*/
	this.jsonEl = null;
	
	/**
	* Проверяет правильность инициализации компонента
	* @return boolean
	*/
	this.isValid = function(){
		return (this.data && this.buttonsEl && this.inputEl && this.outputEl && this.jsonEl);
	};
	
	/**
	* Возвращает объект с данными параметра и списком его возможных значений
	* @return object
	* @example {"parameter_id":14,"parameter_name":"Сознание","listtype":"radiogroup","value":"41","values":{"42":"отсутствует","41":"спутанное","15":"Ясное"}}
	*/
	this.getData = function(){
		if(this.data) {
			return this.data;
		}
		if(this.jsonEl) {
			var json = this.jsonEl.dom.innerHTML;
			this.data = Ext.util.JSON.decode(json.replace(/\\\\/g, '\\'));
			return this.data;
		}
		return null;
	};
	
	var me = this, listeners = {
		beforeSave: [],
		afterSave: []
	};
	this.on = function(event, handler, scope) {
		if (Ext.isArray(listeners[event])) {
			listeners[event].push({
				handler: handler,
				scope: scope || me
			});
		}
	};
	this.fireEvent = function() {
		var event = arguments[0] || '', i = 0;
		if (Ext.isArray(listeners[event])) {
			for (i = 0; listeners[event].length > i; i++) {
				listeners[event][i].handler.call(listeners[event][i].scope, Array.prototype.slice.call(arguments, 1));
			}
		}
	};
	
	/**
	* Сохранение выбранных значений
	* @param string config.value Список значений, разделенных запятой
	* @param function config.callback
	*/
	this.saveValue = function(config){
		if(!config) {
			config = {};
		}
		if(Ext.isEmpty(config.value) || config.value == '0') {
			config.value = null;
		}
		me.data.value = config.value || '0';
		var params = {EvnXml_id: me.EvnXml_id, name: me.fieldName, value: config.value};
		me.fireEvent('beforeSave', params);
		Ext.Ajax.request({
			url: '/?c=EvnXml&m=updateContent',
			callback: function(opt, success, response) {
				if (success && response.responseText != '') {
					var response_obj = Ext.util.JSON.decode(response.responseText);
					if (typeof config.callback == 'function') {
						config.callback(response_obj);
					}
					me.fireEvent('afterSave', params);
				}
			},
			params: params
		});
	};
	
	/**
	* Отрисовка компонента
	*/
	this.cmp = null;
	var th = this;
	var renderCmp = function(){
		//log(['render',th]);
		var data = [];
		for (var id in th.data.values) {
			data.push([id,th.data.values[id]]);
		}
		//log(data);
		
		/**
		* Сохранение выбранных значений
		* @param string data.value_id_list Список значений, разделенных запятой
		* @param string data.value_name_list Список названий значений, разделенных запятой
		*/
		var onChanged = function(data){
			th.saveValue({
				value: data.value_id_list,
				callback: null
			});
		};
		
		var onCheck = function() {
			//log(['onCheck',this]);
			if(!this.checked){
				this.ownerCmp.items.each(function(box){
					if(box.id == this.id){
						this.setValue(true);
					}else{
						box.setValue(false);
					}
				}, this);
			} else {
				//this.setValue(false);
			}
			this.ownerCmp.value = this.value;
			onChanged({
				value_id_list: this.ownerCmp.getValue()
				,value_name_list: this.boxLabel
			});
		};
		
		var toggleValue = function() {
			//log(['toggleValue',this]);
			if(!this.checked){
				this.setValue(true);
			} else {
				this.setValue(false);
			}
			this.ownerCmp.value = this.ownerCmp.getValue();
			onChanged({
				value_id_list: this.ownerCmp.value
				,value_name_list: this.boxLabel
			});
		};
		
		th.outputEl.setVisibilityMode(Ext.Element.DISPLAY);
        th.inputEl.setVisibilityMode(Ext.Element.DISPLAY);
        th.buttonsEl.setVisibilityMode(Ext.Element.DISPLAY);
		th.outputEl.setVisible(false);
        th.inputEl.setVisible(true);
        th.buttonsEl.setVisible(true);

		th.cmp = null;
		switch(th.data.listtype) {
			case 'combobox':
				th.cmp = new sw.Promed.swParameterValueCombo({
                    fieldLabel: th.data.parameter_name,
					comboData: data,
					onChanged: onChanged,
					value: th.data.value,
					renderTo: th.inputEl.id
				});
				break;
			case 'checkboxgroup':
				th.cmp = new sw.Promed.swParameterValueCheckboxGroup({
                    fieldLabel: th.data.parameter_name,
					toggleValue: toggleValue, //переопределяем метод Ext.form.Checkbox
					id: 'checkboxgroup_'+th.key,
					itemsData: data,
					onChanged: onChanged,
					value: th.data.value,
					renderTo: th.inputEl.id
				});
				break;
			case 'radiogroup': 
				th.cmp = new sw.Promed.swParameterValueRadioGroup({
                    fieldLabel: th.data.parameter_name,
					toggleValue: onCheck, //переопределяем метод Ext.form.Radio
					id: 'radiogroup_'+th.key,
					itemsData: data,
					onChanged: onChanged,
					value: th.data.value,
					renderTo: th.inputEl.id
				});
				break;
		}
	};
	
	/**
	* Конструктор
	*/
	if(config.EvnXml_id) {
		this.EvnXml_id = config.EvnXml_id;
	}
	if(config.node) {
		this.jsonEl = new Ext.Element(config.node);
	}
	if(this.jsonEl && this.EvnXml_id == this.jsonEl.id.split('_')[2])
	{
		this.fieldName = this.jsonEl.id.split('_')[1];
		this.key = this.fieldName +'_'+ this.EvnXml_id;
        this.buttonsEl = Ext.get('buttons_'+ this.key);
        this.outputEl = Ext.get('output_'+ this.key);
		this.inputEl = Ext.get('input_'+ this.key);
		this.data = this.getData();
	}
	if(this.jsonEl && this.EvnXml_id == this.jsonEl.id.split('_')[3])
	{
		this.fieldName = this.jsonEl.id.split('_')[1] +'_'+ this.jsonEl.id.split('_')[2];
		this.key = this.fieldName +'_'+ this.EvnXml_id;
        this.buttonsEl = Ext.get('buttons_'+ this.key);
        this.outputEl = Ext.get('output_'+ this.key);
		this.inputEl = Ext.get('input_'+ this.key);
		this.data = this.getData();
	}
	
	/**
	* Отрисовка компонента при создании объекта
	*/
	if(this.isValid())
	{
		renderCmp();
	}
};

/**
 * Функция удаления объекта "параметр-список значений" из документа целиком
 */
sw.Promed.ParameterValueDelete = function (key) {
    var fieldName = null;
    var EvnXml_id = null;
    var wrapEl = null;
    key = key+'';
    var parts = key.split('_');
    if (parts.length == 2) {
        fieldName = parts[0];
        EvnXml_id = parts[1];
        wrapEl = Ext.get('wrap_'+ key);
    }
    if (parts.length == 3) {
        fieldName = parts[0] +'_'+ parts[1];
        EvnXml_id = parts[2];
        wrapEl = Ext.get('wrap_'+ key);
    }
    if (wrapEl) {
        Ext.Ajax.request({
            url: '/?c=EvnXml&m=destroySection',
            callback: function(opt, success, response) {
                if (success && response.responseText != '') {
                    var response_obj = Ext.util.JSON.decode(response.responseText);
                    if(response_obj.success) {
                        wrapEl.update('');
                    }
                }
            },
            params: {name: fieldName, EvnXml_id: EvnXml_id}
        });
    }
};

/**
 * Коллекция объектов с типом Параметр и список значений в документе EvnXml_id
 */
sw.Promed.ParameterValueCollection = function (config) {
	/**
	* Конструктор
	*/
	this.dom = config.dom;
	this.EvnXml_id = config.EvnXml_id;
	this.items = [];
	
	/**
	* Отрисовка компонентов выбора значения
	*/
	this.render = function(){
		var node_list = Ext.query('div[class*=parametervalue]',this.dom);
		var i;
		for(i=0; i < node_list.length; i++)
		{
			var item = new sw.Promed.ParameterValue({node: node_list[i], EvnXml_id: this.EvnXml_id});
			if(item.isValid()) {
				this.items.push(item);
			}
		}
	};
};

/**
 * Библиотека классов для редактирования документов
 *
 * @author		Permyakov Alexander
 */


/**
 * @access public
 * @param {*} cmp
 * @param {object} config
 * @param {boolean} allowStructuredParams
 * @return {sw.Promed.swTextEditors}
 */
sw.Promed.swTextEditors = function(cmp, allowStructuredParams, instance_id){
    if( !cmp ) {
        cmp = {};
    }
	if (typeof config != 'object') {
		config = new Object();
	}
    this.cmp = cmp;
    if(!this.cmp.input_cmp_list) this.cmp.input_cmp_list = {};

    var thas = this;

    this.createToolsBarIdByInstance = function(instance) {
        return this.createToolsBarId(instance.field.name, instance.field.EvnXml_id);
    };

    var instanceIdStr = instance_id?'_'+instance_id:'';

	this.createBlockId = function(field_name, evnxml_id) {
		return 'block_'+ field_name +'_'+ evnxml_id + instanceIdStr;
	};

    this.createToolsBarId = function(field_name, evnxml_id) {
        return 'toolbar_'+ field_name +'_'+ evnxml_id + instanceIdStr;
    };

    this.createInputCmpId = function(field_name, evnxml_id) {
        return 'field_'+ field_name +'_'+ evnxml_id + instanceIdStr;
    };

    this.createFieldAreaId = function(field_name, evnxml_id) {
        return 'data_'+ field_name +'_'+ evnxml_id + instanceIdStr;
    };

    this.beforeSave = function(){};
    this.afterSave = function(){};

    this.render = function(options) {
        var b,f,capt,el,c,h,s;
        for(o in options.xml_data) {
            if(typeof options.xml_data[o] != 'string')
                continue;

            // Добавил условие, чтобы все это выполнялось только в ЭМК, потому что в старой версии этого файла на рабочей иногда не отображался блок 135844
			if(Ext.WindowMgr.getActive().objectName === 'swPersonEmkWindow')
			{
				var containers = Ext.query('div[id=' + this.createFieldAreaId(o, options.EvnXml_id) + ']'); // костыль для удаления лишнего контейнера, если найден     подробнее тут #114734
				containers = containers.filter(function(container) {
					return Ext.fly(container).up('.template-block-data');	//#136047 Добавляем ещё костылей. Не учитывать контейнер в блоке ввода данных
				});
				// при добавлении документа к направлению после перезагрузки эмк создается снова контейнер с id формата data_anamnesvitae_23516
				// это не дает правильно разместить поле для ввода. неясно в какой момент он создается, поэтому решил проблему удалением
				if (containers.length > 1) {
					containers[0].remove();
				}
			}

			b = Ext.get(this.createFieldAreaId(o, options.EvnXml_id));
            //log(b);
            if(b) {
                f = new sw.Promed.swTextEditor({
                    EvnXml_id: options.EvnXml_id
                    ,EvnClass_id: options.EvnClass_id
                    ,Evn_id: options.Evn_id || null
                    ,Evn_pid: options.Evn_pid || null
                    ,Evn_rid: options.Evn_rid || null
                    ,value: options.xml_data[o]
                    ,name: o
                    ,renderToElement: b
					,allowStructuredParams: allowStructuredParams
                }, this);
                this.cmp.input_cmp_list[this.createInputCmpId(o, options.EvnXml_id)] = f;
            }
        }
    };

    return this;
};

/**
 * @access private
 * @param {object} config
 * @param {sw.Promed.swTextEditors} editors
 * @return {textEditorInstance}
 */
sw.Promed.swTextEditor = function(config, editors){
    if(!config || !editors || !config.EvnXml_id || !config.name || !config.renderToElement ) {
        return null;
    }
    config.value = config.value || '';

    this.config = config;

	var instance = null;
    var block_id = editors.createBlockId(config.name, config.EvnXml_id);
    var field_id = editors.createInputCmpId(config.name, config.EvnXml_id);
    var toolbar_id = editors.createToolsBarId(config.name, config.EvnXml_id);
    this.config.renderToElement.update('<div class="sw-nicEdit-wrap">' +
        '<div id="'+ toolbar_id +
        '" class="sw-nicEdit-tb" style="display: none;"></div>' +
        '<textarea class="sw-nicEdit" id="' +
        field_id +'" name="'+ config.name +
        '">'+ config.value +'</textarea>' +
        '</div>',
    false,
    function(){
		var options = {
			branding: false,
			language: 'ru',
			menubar: false,
			browser_spellcheck: true,
			content_style: '.mce-content-body {font-family: Times New Roman; font-size: 12pt;}',
			plugins: "lists print table",
			toolbar: [
				"undo redo | table | fontselect fontsizeselect | bold italic underline strikethrough | subscript superscript |",
				"alignleft aligncenter alignright | bullist numlist outdent indent | print"
			].join(' '),
			setup: function(theEditor) {
				/**
				 * @author Robert SS
				 * @version 1.0
				 * @email borisworking@gmail.com
				 * @github github.com/Slooo
				 * @description Формирование кнопок
				 */

				setTimeout(function() {
					theEditor.render();
				}, 100);

				theEditor.addButton('swreset', {
					type: 'button',
					image: '/img/icons/2017/refresh16.png',
					style: 'opacity: 0.6;',
					tooltip: 'Восстановить раздел',
					onclick: function () {
						var instance = tinyMCE.get(field_id);
						if(!instance) return;
						Ext.Ajax.request({
							url: '/?c=EvnXml&m=resetSection',
							params: {
								name: instance.field.name,
								EvnXml_id: instance.field.EvnXml_id
							},
							callback: function(opt, success, response) {
								if (success && response.responseText != '') {
									var response_obj = Ext.util.JSON.decode(response.responseText);
									if(response_obj.success) {
										instance.setContent(response_obj.html || '<br />');
									}
								}
							}
						});
					}
				});

				theEditor.addButton('swdelete', {
					type: 'button',
					image: '/img/icons/2017/delete16.png',
					style: 'opacity: 0.6;',
					tooltip: 'Удалить раздел',
					onclick: function() {
						var instance = tinyMCE.get(field_id);
						if(!instance) return;
						Ext.Ajax.request({
							url: '/?c=EvnXml&m=destroySection',
							params: {
								name: instance.field.name,
								EvnXml_id: instance.field.EvnXml_id
							},
							callback: function(opt, success, response) {
								if (success && response.responseText != '') {
									var response_obj = Ext.util.JSON.decode(response.responseText);
									if(response_obj.success) {
										sw.Promed.EvnXml.removeEditors({field_id:instance});
										var block = Ext.fly(block_id);
										if (block) block.remove();
									}
								}
							}
						});
					}
				});
				
				// Если ЭМК то вызываем кнопку ЛИС
				if(Ext.WindowMgr.getActive().objectName === 'swPersonEmkWindow') {
					theEditor.emkWindow = Ext.WindowMgr.getActive();
					theEditor.on('init', function() {
						theEditor.saveValue = function() {
							editors.beforeSave();
							var params = {EvnXml_id: theEditor.field.EvnXml_id, name: theEditor.field.name, value: theEditor.getContent(), isHTML: 1};
							Ext.Ajax.request({
								url: '/?c=EvnXml&m=updateContent',
								callback: function(opt, success, response) {
									if (success && response.responseText != '') {
										var response_obj = Ext.util.JSON.decode(response.responseText);
										//log(response_obj);
										theEditor.lastValue = theEditor.getContent();
									}
									editors.afterSave();
								},
								params: params
							});
						};
						theEditor.field = {
							name: config.name,
							id: field_id,
							EvnClass_id: config.EvnClass_id,
							EvnXml_id: config.EvnXml_id,
							Evn_id: config.Evn_id || null,
							Evn_pid: config.Evn_pid || null,
							Evn_rid: config.Evn_rid || null
						};

						$(this.contentAreaContainer.parentElement).find("div.mce-toolbar-grp").hide();
						$(this.contentAreaContainer.parentElement).find("div.mce-statusbar").hide();

						theEditor.theme.panel.find('listbox').filter(function(cmp) {
							return cmp.settings.text == 'Font Family';
						}).removeClass('fixed-width');

						if (config.allowStructuredParams) {
							theEditor.allowStructuredParams = config.allowStructuredParams;
						}
						if(getRegionNick()=='vologda') {
							if(!theEditor.emkWindow.T9list) {
								theEditor.emkWindow.T9list = Ext6.create('Ext6.menu.Menu', {
									cls: 'T9menu',
									items: []
								});										
							}

							var bg = theEditor.theme.panel.find('toolbar buttongroup')[6];
							theEditor.addButton('t9', {
								type: 'button',
								text: 'T9',
								icon: false,
								tooltip: 'Предиктивный ввод текста',
								onClick: function() {
									theEditor.emkWindow.T9mode = !theEditor.emkWindow.T9mode;
									this.active(theEditor.emkWindow.T9mode);
								},
							});
							bg.append(theEditor.buttons['t9']);
						}
					});
					theEditor.on('focus', function () {
						if(getRegionNick()=='vologda') {
							var buttonT9 = theEditor.theme.panel.find('toolbar buttongroup')[6]._items[1];
							if(getGlobalOptions().enableT9) buttonT9.show(); else buttonT9.hide();
							buttonT9.active(theEditor.emkWindow.T9mode);
						}
						var params = new Object();
						var container = this;
						var Evn_pid = null;
						if (Number(config.EvnClass_id).inlist([30,32])) {
							params.Evn_pid = document.querySelector('[id^=EvnSection_data_]').id.split('_')[2];
						} else if (config.EvnClass_id == 13) {
							params.Evn_pid = document.querySelector('[id^=EvnVizitPLStom_data_]').id.split('_')[2];
						} else {
							params.EvnClass_SysNick = 'EvnVizitPL';
							params.Evn_pid = document.querySelector('[id^=EvnVizitPL_data_]').id.split('_')[2];
						}

						// ф-ия очищение тегов
						var clearHTML = function(html) {
						   var tmp = document.createElement("DIV");
						   tmp.innerHTML = html;
						   return tmp.textContent || tmp.innerText || "";
						};

						Ext.Ajax.request({
							params: params,
							url: '/?c=EvnLabRequest&m=getLabTestsPrintData',
							callback: function(opt, success, response) {
								var result = Ext.util.JSON.decode(response.responseText);
								if(result.UslugaComplex.length > 0) {
									var menu = [];
									result.UslugaComplex.forEach(function(i) {
										var UslugaComplex = clearHTML(i.UslugaComplex_Name);
										var UslugaComplexGroup = [];

										// Меню 2 lvl
										Object.keys(i.EvnLabSample_updDT).map(function(obj) {
											UslugaComplexGroup.push({
												id: obj,
												text: i.EvnLabSample_updDT[obj],
												type: 'button',
												classes: 'tinyMCE__lis',
												name: i.UslugaComplex_Code,
												onclick: function(e) {
													var btn = this;
													var usluga = document.getElementsByClassName('mce-selected')[0].innerText.trim(),
														date = btn.settings.text,
														code = btn.settings.name,
														labsample = 'labsample_'+btn.settings.id,
														id = 'usluga_'+btn.settings.name;
														classes = btn._elmCache[Object.keys(btn._elmCache)[0]].classList;

													// Добавление
													if(!classes.contains('mce-tinyMCE__lis-active')) {
														var el = tinyMCE.activeEditor.dom.get(id);
														if(el === null) {
															var table = '<table id="'+id+'">';
																table += '<tbody>';

															// Собираем тесты
															result.UslugaComplex.forEach(function(v) {
																if(v.UslugaComplex_Code === code) {

																	if(clearHTML(v.UslugaComplex_Name).trim() === usluga) {
																		table += v.UslugaComplex_Name;
																	}
																	
																	// 1. Имена тестов
																	table += '<tr>';
																	table += '<td></td>';
																		v.tests.forEach(function(test) {
																			table += '<td><b>'+test.test_name+'</b></td>';
																		});
																	table += '</tr>';

																	// 2. Результаты тестов
																	v.EvnUslugaPar.forEach(function(va) {
																		if(va.EvnLabSample_updDT === date) {
																			table += '<tr class="'+labsample+'">';
																			table += '<td><b>'+va.EvnUslugaPar_setDate+'</b></td>';
																			va.results.forEach(function(val) {
																				table += '<td><b>'+val.result+'</b></td>';
																			});
																			table += '</tr>';
																		}
																	});

																}
															});

															table += '</tbody>';
															table += '</table>';

															theEditor.insertContent('<div class="marker_lab_tests">' + table + '</div><br><br>');
														} else {
															// Проверяем дату и вставляем тесты
															result.UslugaComplex.forEach(function(u) {
																if(u.UslugaComplex_Code.trim() === code) {

																	// ищем строку с последним тестом
																	var curTr;
																	var el_array = [].slice.call(el.firstChild.childNodes);
																	u.tests.forEach(function(test) {
																		el_array.forEach(function(tr, key){
																			Object.keys(tr.childNodes).map(function(td){
																				if(test.test_name === tr.childNodes[td].innerText.trim()) {
																					curTr = key;
																				}
																			});
																		});
																	});

																	u.EvnUslugaPar.forEach(function(p) {
																		if(p.EvnLabSample_updDT === date) {
																			var table = document.createElement('tr');
																				table.setAttribute('class', labsample);
																			table.innerHTML += '<td><b>'+p.EvnUslugaPar_setDate+'</b></td>';
																			p.results.forEach(function(r) {
																				table.innerHTML += '<td><b>'+r.result+'</b></td>';
																			});

																			el_array.splice(curTr+1, 0, table);

																			Object.keys(el_array).map(function(e){
																				el.firstChild.appendChild(el_array[e]);
																			});
																		}
																	});
																}
															});
															
															tinyMCE.activeEditor.dom.replace(tinyMCE.activeEditor.dom.get(id), el);
														}

														classes.add('mce-tinyMCE__lis-active');
													} else {
														// Удаление
														var table = tinyMCE.activeEditor.dom.get(id);													
														if(table !== null) {
															var removeTR = function(table) {
																Object.keys(table.firstChild.childNodes).map(function(tr){
																	if(table.firstChild.childNodes[tr] !== undefined 
																		&& table.firstChild.childNodes[tr].classList.contains(labsample)) {
																		table.firstChild.childNodes[tr].remove();
																		removeTR(table);
																	} else {
																		return true;
																	}
																});
															};

															removeTR(table);

															// провека остались ли вообще строки с тестами
															var check = false;
															Object.keys(table.firstChild.childNodes).map(function(tr){
																if(table.firstChild.childNodes[tr].hasAttribute('class')) {
																	check = true;
																}
															});

															if(!check) tinyMCE.activeEditor.dom.remove(id);
														}

														classes.remove('mce-tinyMCE__lis-active');
													}
												}
											});
										});
								
										// Меню 1 lvl
										if(UslugaComplex !== '') {
											menu.push({
												text: UslugaComplex,
												menu: UslugaComplexGroup,
												classes: 'tinyMCE__lis-menu',
												onpostrender: function(e) {
													this._elmCache[Object.keys(this._elmCache)[0]].setAttribute('title', this.settings.text);
												},
									        	onshow: function() {
													var submenu = this.menu._items;
													if(tinyMCE.activeEditor.dom.select('table') !== null) {
														Object.keys(submenu).map(function(el){
															el = Number(el);
															if(!isNaN(el)) {
																var btn = submenu[el]._elmCache[Object.keys(submenu[el]._elmCache)[0]],
																	labsample = 'labsample_'+submenu[el]._id,
																	id = 'usluga_'+submenu[el]._name,
																	table = tinyMCE.activeEditor.dom.get(id),
																	classes = submenu[el]._elmCache[Object.keys(submenu[el]._elmCache)[0]].classList;

																if(table !== null) {
																	var tbody = table.firstChild.childNodes;
																	Object.keys(tbody).map(function(tr){
																		if(tbody[tr].classList.contains(labsample)) {
																			classes.add('mce-tinyMCE__lis-active');
																		}
																	});
																}

																if(table === null) {
																	classes.remove('mce-tinyMCE__lis-active');
																}
															}
														});
													}
									        	}
											});
										}

									});

							        theEditor.addButton('lis', {
							        	text: 'ЛИС',
							        	icon: false,
							        	type: 'menubutton',
							        	menu : menu
							        });

									var btn_lis = theEditor.buttons['lis'];
							    }

					            theEditor.addButton('insertbutton', {
					            	type: 'menubutton',
					            	text: 'Вставка',
					            	icon: false,
					            	menu: [
					            		theEditor.menuItems['inserttable'],
					            		theEditor.menuItems['tableprops'],
					            		theEditor.menuItems['deletetable'],
					            		{
					            			text: '-'
					            		}, {
					            			text: 'Вставить документ/фрагмент документа',
					            			onclick: function() {
					            				var instance = tinyMCE.get(field_id);
					            				var c_editor = Ext.get(instance.getElement());
					            				var th = this,
					            					tmp = null;

					            				var params = {
					            					onHide: function() {
					            						//возвращаем фокус в редактор
					            						c_editor.focus();
					            					},
					            					callback: function(data) {
					            						if(typeof data != 'object') {
					            							return false;
					            						}
					            						var rng = instance.selection.getRng();
					            						if(data.range && !data.range.collapsed) {
					            							var frag = data.range.cloneContents();
					            							if(rng.collapsed == false) {
					            								//замещаем выделенный фрагмент or rng.collapse(false);
					            								rng.deleteContents();
					            							}
					            						} else {
					            							//при нажатии на кнопку выбор ничего не было выделено, вставляем весь текст документа
					            							var frag = rng.createContextualFragment(data.wholeDoc);
					            						}
					            						rng.insertNode(frag);
														sw.Promed.EvnXml.refreshEditorSize(instance);
														instance.saveValue();
					            						return true;
					            					},
					            					EvnXml_id: instance.field.EvnXml_id
					            				};

					            				getWnd('swEmkDocumentsListWindow').show(params);
					            			}
					            		}, {
					            			text: 'Вставить из Word',
					            			onclick: function() {
					            				var instance = tinyMCE.get(field_id);
					            				var c_editor = Ext.get(instance.getElement());
					            				var th = this,
					            					onHide = function() {
					            						//возвращаем фокус в редактор
					            						c_editor.focus();
					            					},
					            					onOk = function(content) {
					            						onHide();
					            						Ext.Ajax.request({
					            							url: '/nicedit/word.php',
					            							callback: function(opt, success, response) {
					            								if (success && response.responseText != '') {
					            									var response_obj = Ext.util.JSON.decode(response.responseText);
					            									if(response_obj.success && response_obj.result) {
																		c_editor.focus();
					            										var rng = instance.selection.getRng();
					            										rng.insertNode(rng.createContextualFragment(response_obj.result));
																		sw.Promed.EvnXml.refreshEditorSize(instance);
																		instance.saveValue();
					            									}
					            								}
					            							},
					            							params: {content: content, to: 'text'}
					            						});
					            					};

					            				sw.swMsg.prompt('Вставить из Word',
					            					'Пожалуйста, вставьте текст, используя сочетание клавиш (Ctrl+V), и нажмите кнопку OK',
					            					function(buttonId, text, obj)
					            					{
					            						if ('ok' == buttonId && text) {
					            							onOk(text);
					            						} else  {
					            							onHide();
					            						}
					            					},
					            					th, true, '');
					            			}
					            		}, {
					            			text: 'Загрузить и вставить рисунок',
					            			onclick: function() {
					            				var instance = tinyMCE.get(field_id);
					            				var c_editor = Ext.get(instance.getElement());
					            				var evn_id = instance.field.Evn_rid,
					            					evnxml_id = instance.field.EvnXml_id;

					            				getWnd('swFileUploadWindow').show({
					            					enableFileDescription: false,
					            					saveUrl: '/?c=EvnMediaFiles&m=uploadFile',
					            					saveParams: {
					            						saveOnce: true,
					            						filterType: 'image',
					            						Evn_id: evn_id,
					            						isForDoc: 1
					            					},
					            					onHide: function() {
					            						//возвращаем фокус в редактор
					            						c_editor.focus();
														
					            					},
					            					callback: function(data) {
					            						if (!data) {
					            							return false;
					            						}
					            						var response_obj = Ext.util.JSON.decode(data);
					            						//log(response_obj);
					            						//var src = response_obj[0].upload_path + response_obj[0].file_name;
					            						var src = location.protocol +'//'+ location.host +'/'+ response_obj[0].upload_dir + response_obj[0].file_name;
					            						var id = response_obj[0].EvnMediaData_id;
					            						//вставляем

					            						var img = document.createElement("img");
					            						img.setAttribute('alt', id + '_' + evn_id + '_' + evnxml_id);
					            						img.setAttribute('src', src);
					            						img.setAttribute('border', '1');
					            						if (response_obj[0].width) {
					            							img.style.width = response_obj[0].width;
					            						}

					            						var rng = instance.selection.getRng();
					            						rng.insertNode(rng.createContextualFragment(img.outerHTML));
														instance.saveValue();
					            					}
					            				});
					            			}
					            		}, {
					            			text: 'Генерация текста',
					            			onclick: function() {
					            				var instance = tinyMCE.get(field_id);
					            				var c_editor = Ext.get(instance.getElement());

					            				if (instance.field.name.indexOf('autoname') == -1 ) {
					            					setTimeout(function() {
					            						var params = {
					            							onHide: function() {
					            								//возвращаем фокус в редактор
					            								this.editor.focus();
					            							},
					            							onFinish: function(text) {
					            								//возвращаем фокус в редактор
					            								this.editor.focus();

					            								var rng = this.editor_instance.selection.getRng();
					            								var frag = rng.createContextualFragment(text);
					            								rng.insertNode(frag);
					            								rng.collapse(false);
																this.editor_instance.saveValue();

					            								return true;
					            							},
					            							'branch': instance.field.name,
					            							'editor': c_editor,
					            							'editor_instance' : instance
					            						};


					            						if (Ext.getCmp('PersonEmkTree').collapsed) {
					            							Ext.getCmp('PersonEmkTree').expand();
					            						}

					            						if (Ext.getBody().child('div[id=main-center-panel]')) {
					            							getWnd('swStructuredParamsWindow').show(params);
					            						} else {
					            							sw.swMsg.alert('Ошибка', 'Задан неправильный элемент для отрисовки в нем окна структурированных параметров.');
					            						}
					            					}, 200);
					            				} else {
					            					// Скрываем окно структурированных параметров для нешаблонных разделов
					            					getWnd('swStructuredParamsWindow').hide();
					            				}
					            			}
					            		}]
					            });

					    		var btn_ins = theEditor.buttons['insertbutton'];
					    		var btn_reset = theEditor.buttons['swreset'];
					    		var btn_delete = theEditor.buttons['swdelete'];

					    		//find the buttongroup in the toolbar found in the panel of the theme
					    		var bg = theEditor.theme.panel.find('toolbar buttongroup')[1];
					    		bg._lastRepaintRect = bg._layoutRect;
					    		bg.items().remove();

					    		if(btn_lis!==undefined) {
					    			bg.append(btn_lis); // кнопка ЛИС
					    		} else {
					    			theEditor.addButton('lis_disabled', {
					    				text: 'ЛИС',
					    				icon: false,
					    				type: 'button'
					    			});

					    			bg.append(theEditor.buttons['lis_disabled']);
					    		}
					    		
					    		bg.append(btn_ins); // заменяем table на кастомную менюшку "Вставка"
								bg.append(btn_reset);
								bg.append(btn_delete);

							    theEditor.lastValue = theEditor.getContent();

							    $(container.contentAreaContainer.parentElement).find("div.mce-toolbar-grp").show();
							    $(container.contentAreaContainer.parentElement).find("div.mce-statusbar").show();

							    if (theEditor.allowStructuredParams && !Ext.globalOptions.emk.disable_structured_params_auto_show ) {
							    	instance = tinyMCE.get(field_id);
							    	if (instance.field.name.indexOf('autoname') == -1 ) {
							    		setTimeout(function() {
							    			var c_editor = Ext.get(instance.getElement());
							    			var wnd = getWnd('swStructuredParamsWindow');

							    			if ( !wnd.items || wnd.editor != c_editor) {

							    				if (Ext.getCmp('PersonEmkTree').collapsed) {
							    					Ext.getCmp('PersonEmkTree').expand();
							    				}
							    				var params = {
							    					onHide: function() {
							    						//возвращаем фокус в редактор
							    						this.editor.focus();
							    					},
							    					onFinish: function(text) {
							    						//возвращаем фокус в редактор
							    						this.editor.focus();

							    						var rng = this.editor_instance.selection.getRng();
							    						var frag = rng.createContextualFragment(text);
							    						rng.insertNode(frag);
							    						rng.collapse(false);
														this.editor_instance.saveValue();

							    						return true;
							    					},
							    					'branch': instance.field.name, // тут надо в зависимости от поля подставлять
							    					'editor': c_editor,
							    					'editor_instance' : instance
							    				};

							    				if (Ext.getBody().child('div[id=main-center-panel]')) {
							    					getWnd('swStructuredParamsWindow').show(params);
							    				} else {
							    					sw.swMsg.alert(langs('Ошибка'), langs('Задан неправильный элемент для отрисовки в нем окна структурированных параметров.'));
							    				}
							    			}
							    		}, 200);
							    	} else {
							    		// Скрываем окно структурированных параметров для нешаблонных разделов
							    		if (getWnd('swStructuredParamsWindow').isVisible()) {
							    			getWnd('swStructuredParamsWindow').hide();
							    		}
							    	}
							    }
							}
						});
					});
					
					theEditor.on('keyUp', function(e) {
						var me = this;
						var win = me.emkWindow;
						if(getRegionNick()!='vologda' || !win.T9mode || !getGlobalOptions().enableT9) return;
						if(!me.insertword) {
							me.insertword = function(text, pos, num) {
								var me = this;
								var win = me.emkWindow;
								if(!win) return;								
								var focusNode = win.T9list.focusNode;
								var range = win.T9list.range;
								var cursor_pos = win.T9list.cursor_pos;
								var pos_slice = win.T9list.pos_slice;
								if(num) pos_slice++;
								
								me.focus();
								
								if(pos>=0) { //обычно 1 == один пробел после текущего слова, перед вставляемым
									if(pos_slice>cursor_pos) focusNode.deleteData(cursor_pos, pos_slice-cursor_pos);
									focusNode.insertData(cursor_pos, ' '+text );
									me.selection.getSel().setPosition(focusNode, cursor_pos+text.length+1);
									
								} else if(cursor_pos+pos>=0) {
									if(pos_slice>cursor_pos) focusNode.deleteData(cursor_pos, pos_slice-cursor_pos);
									focusNode.insertData(cursor_pos, text );
									focusNode.deleteData(cursor_pos+pos, -pos);
									me.selection.getSel().setPosition(focusNode, cursor_pos+pos+text.length);
								}
								me.saveValue();
							}
						}
						
						
						var selection = me.selection;
						var menuvisibled = false;
						if(win.T9list.isVisible()) {
							menuvisibled = true;
							win.T9list.hide();
						}
						
						if(e.key.inlist(['ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown', 'Control', 'Shift', ' ', 'Backspace','Delete','Enter'])) {
							return;
						}
						
						if(menuvisibled && e.key>="1" && e.key<="9") {
							var n = Number(e.key);
							var item = win.T9list.items.getAt(n-1);
							if(item) {
								item.enter(item, n);
							}
							return;
						}
									
						var str = selection.getSel().focusNode.data;
						var pos = selection.getSel().focusOffset;
						if(!str || str.length==0) return;
						var s = str.slice(0,pos);
						if(! /[a-zа-я]/i.test(s.slice(-1))) return;
						var words = s.split(' ');
						if(words.length>3) words = words.slice(-3);
						for(i=0;i<words.length;i++) words[i]=words[i].trim();
						if(words[words.length-1].length<3) return;
						var query = words.join('+');
						
						win.T9list.focusNode = selection.getSel().focusNode;
						win.T9list.range = selection.getRng();
						win.T9list.cursor_pos = pos;
						var pos_slice = pos;
						while(str.slice(pos_slice, pos_slice+1)=='_') {
							pos_slice+=1;
						}
						win.T9list.pos_slice = pos_slice;
						win.T9list.elem_id = selection.getNode().id;
						
						Ext6.data.JsonP.request({
							url: 'https://predictor.yandex.net/api/v1/predict.json/complete',
							params: {
								key: 'pdct.1.1.20181125T124441Z.0246b2bc05d6531d.5417543d0833dce6b16b67c30b6920ed42b14423',
								q: query,
								lang: 'ru',
								limit: '9'
							},
							crossDomain: true,
							type: "GET", 
							dataType: "json", 
							scope: this, 
							callback: function (response, value, request) {					
								if(value && value.text && value.text.length>0) {
									if(!win.T9list) {
										return;
									}
									win.T9list.removeAll();
									var i=0;
									value.text.forEach(function(variant) {
										if(win.T9list.items.length<9) {
											var boldword = '';
											var suffix = '';
											if(value.pos<0) {
												boldword = variant.slice(0, -value.pos);
												suffix = variant.slice(-value.pos);
											} else {
												boldword = '';
												suffix = variant;
											}
											i++;
											win.T9list.add(Ext6.create('Ext6.menu.Item', {
												textvalue: variant,
												iconCls: 't9item'+i,
												pos: value.pos,
												text: '<b>'+boldword+'</b>'+suffix,
												handler: function(item) {
													this.enter(item, 0);
												},
												enter: function(item, number) {
													me.insertword(item.textvalue, item.pos, number);
												}
											}));
										}
									});
									var editorPos = me.getContainer().getBoundingClientRect();
									var toolbar = me.theme.panel.find('toolbar')[0];
									var toolrect = toolbar.getEl().getBoundingClientRect();
									var pos = this.selection.getBoundingClientRect();
									win.T9list.showAt([editorPos.x + pos.left, editorPos.y + toolrect.height + pos.bottom]);
									this.focus();
								}
							}
						});
						
					});
				} else {
					theEditor.on('init', function() {
						theEditor.saveValue = function() {
							editors.beforeSave();
							var params = {EvnXml_id: theEditor.field.EvnXml_id, name: theEditor.field.name, value: theEditor.getContent(), isHTML: 1};
							Ext.Ajax.request({
								url: '/?c=EvnXml&m=updateContent',
								callback: function(opt, success, response) {
									if (success && response.responseText != '') {
										var response_obj = Ext.util.JSON.decode(response.responseText);
										//log(response_obj);
										showPopupInfoMsg(langs('Все изменения сохранены.'));
										theEditor.lastValue = theEditor.getContent();
									}
									if (!success) {
										showSysMsg('Изменения не были сохранены!', 'Внимание','warning');
									}
									editors.afterSave();
								},
								params: params
							});
						};
						theEditor.field = {
							name: config.name,
							id: field_id,
							EvnClass_id: config.EvnClass_id,
							EvnXml_id: config.EvnXml_id,
							Evn_id: config.Evn_id || null,
							Evn_pid: config.Evn_pid || null,
							Evn_rid: config.Evn_rid || null
						};

						$(this.contentAreaContainer.parentElement).find("div.mce-toolbar-grp").hide();
						$(this.contentAreaContainer.parentElement).find("div.mce-statusbar").hide();

						theEditor.theme.panel.find('listbox').filter(function(cmp) {
							return cmp.settings.text == 'Font Family';
						}).removeClass('fixed-width');

						if (config.allowStructuredParams) {
							theEditor.allowStructuredParams = config.allowStructuredParams;
						}
					});
					theEditor.on('focus', function () {
						theEditor.addButton('insertbutton', {
							type: 'menubutton',
							text: 'Вставка',
							icon: false,
							menu: [
								theEditor.menuItems['inserttable'],
								theEditor.menuItems['tableprops'],
								theEditor.menuItems['deletetable'],
								{
									text: '-'
								}, {
									text: 'Вставить документ/фрагмент документа',
									onclick: function() {
										var instance = tinyMCE.get(field_id);
										var c_editor = Ext.get(instance.getElement());
										var th = this,
											tmp = null;

										var params = {
											onHide: function() {
												//возвращаем фокус в редактор
												c_editor.focus();
											},
											callback: function(data) {
												if(typeof data != 'object') {
													return false;
												}
												var rng = instance.selection.getRng();
												if(data.range && !data.range.collapsed) {
													var frag = data.range.cloneContents();
													if(rng.collapsed == false) {
														//замещаем выделенный фрагмент or rng.collapse(false);
														rng.deleteContents();
													}
												} else {
													//при нажатии на кнопку выбор ничего не было выделено, вставляем весь текст документа
													var frag = rng.createContextualFragment(data.wholeDoc);
												}
												rng.insertNode(frag);
												return true;
											},
											EvnXml_id: instance.field.EvnXml_id
										};

										getWnd('swEmkDocumentsListWindow').show(params);
									}
								}, {
									text: 'Вставить из Word',
									onclick: function() {
										var instance = tinyMCE.get(field_id);
										var c_editor = Ext.get(instance.getElement());
										var th = this,
											onHide = function() {
												//возвращаем фокус в редактор
												c_editor.focus();
											},
											onOk = function(content) {
												onHide();
												Ext.Ajax.request({
													url: '/nicedit/word.php',
													callback: function(opt, success, response) {
														if (success && response.responseText != '') {
															var response_obj = Ext.util.JSON.decode(response.responseText);
															if(response_obj.success && response_obj.result) {
																c_editor.focus();
																var rng = instance.selection.getRng();
																rng.insertNode(rng.createContextualFragment(response_obj.result));
																sw.Promed.EvnXml.refreshEditorSize(instance);
																instance.saveValue();
															}
														}
													},
													params: {content: content, to: 'text'}
												});
											};

										sw.swMsg.prompt('Вставить из Word',
											'Пожалуйста, вставьте текст, используя сочетание клавиш (Ctrl+V), и нажмите кнопку OK',
											function(buttonId, text, obj)
											{
												if ('ok' == buttonId && text) {
													onOk(text);
												} else  {
													onHide();
												}
											},
											th, true, '');
									}
								}, {
									text: 'Загрузить и вставить рисунок',
									onclick: function() {
										var instance = tinyMCE.get(field_id);
										var c_editor = Ext.get(instance.getElement());
										var evn_id = instance.field.Evn_rid,
											evnxml_id = instance.field.EvnXml_id;

										getWnd('swFileUploadWindow').show({
											enableFileDescription: false,
											saveUrl: '/?c=EvnMediaFiles&m=uploadFile',
											saveParams: {
												saveOnce: true,
												filterType: 'image',
												Evn_id: evn_id,
												isForDoc: 1
											},
											onHide: function() {
												//возвращаем фокус в редактор
												c_editor.focus();
											},
											callback: function(data) {
												if (!data) {
													return false;
												}
												var response_obj = Ext.util.JSON.decode(data);
												//log(response_obj);
												//var src = response_obj[0].upload_path + response_obj[0].file_name;
												var src = location.protocol +'//'+ location.host +'/'+ response_obj[0].upload_dir + response_obj[0].file_name;
												var id = response_obj[0].EvnMediaData_id;
												//вставляем

												var img = document.createElement("img");
												img.setAttribute('alt', id + '_' + evn_id + '_' + evnxml_id);
												img.setAttribute('src', src);
												img.setAttribute('border', '1');
												if (response_obj[0].width) {
													img.style.width = response_obj[0].width;
												}

												var rng = instance.selection.getRng();
												rng.insertNode(rng.createContextualFragment(img.outerHTML));
											}
										});
									}
								}, {
									text: 'Генерация текста',
									onclick: function() {
										var instance = tinyMCE.get(field_id);
										var c_editor = Ext.get(instance.getElement());

										if (instance.field.name.indexOf('autoname') == -1 ) {
											setTimeout(function() {
												var params = {
													onHide: function() {
														//возвращаем фокус в редактор
														this.editor.focus();
													},
													onFinish: function(text) {
														//возвращаем фокус в редактор
														this.editor.focus();

														var rng = this.editor_instance.selection.getRng();
														var frag = rng.createContextualFragment(text);
														rng.insertNode(frag);
														rng.collapse(false);
														this.editor_instance.saveValue();

														return true;
													},
													'branch': instance.field.name,
													'editor': c_editor,
													'editor_instance' : instance
												};


												if (Ext.getCmp('PersonEmkTree').collapsed) {
													Ext.getCmp('PersonEmkTree').expand();
												}

												if (Ext.getBody().child('div[id=main-center-panel]')) {
													getWnd('swStructuredParamsWindow').show(params);
												} else {
													sw.swMsg.alert('Ошибка', 'Задан неправильный элемент для отрисовки в нем окна структурированных параметров.');
												}
											}, 200);
										} else {
											// Скрываем окно структурированных параметров для нешаблонных разделов
											getWnd('swStructuredParamsWindow').hide();
										}
									}
								}]
						});

						// хитрости для отображения кнопке после init'а tinymce.
						var btn_ins = theEditor.buttons['insertbutton'];
						var btn_reset = theEditor.buttons['swreset'];
						var btn_delete = theEditor.buttons['swdelete'];
						//find the buttongroup in the toolbar found in the panel of the theme
						var bg = theEditor.theme.panel.find('toolbar buttongroup')[1];
						bg._lastRepaintRect = bg._layoutRect;
						bg.items().remove();
						bg.append(btn_ins); // заменяем table на кастомную менюшку "Вставка"
						bg.append(btn_reset);
						bg.append(btn_delete);

						theEditor.lastValue = theEditor.getContent();

						$(this.contentAreaContainer.parentElement).find("div.mce-toolbar-grp").show();
						$(this.contentAreaContainer.parentElement).find("div.mce-statusbar").show();

						if (theEditor.allowStructuredParams && !Ext.globalOptions.emk.disable_structured_params_auto_show ) {
							instance = tinyMCE.get(field_id);
							if (instance.field.name.indexOf('autoname') == -1 ) {
								setTimeout(function() {
									var c_editor = Ext.get(instance.getElement());
									var wnd = getWnd('swStructuredParamsWindow');

									if ( !wnd.items || wnd.editor != c_editor) {

										if (Ext.getCmp('PersonEmkTree').collapsed) {
											Ext.getCmp('PersonEmkTree').expand();
										}
										var params = {
											onHide: function() {
												//возвращаем фокус в редактор
												this.editor.focus();
											},
											onFinish: function(text) {
												//возвращаем фокус в редактор
												this.editor.focus();

												var rng = this.editor_instance.selection.getRng();
												var frag = rng.createContextualFragment(text);
												rng.insertNode(frag);
												rng.collapse(false);
												this.editor_instance.saveValue();

												return true;
											},
											'branch': instance.field.name, // тут надо в зависимости от поля подставлять
											'editor': c_editor,
											'editor_instance' : instance
										};

										if (Ext.getBody().child('div[id=main-center-panel]')) {
											getWnd('swStructuredParamsWindow').show(params);
										} else {
											sw.swMsg.alert(langs('Ошибка'), langs('Задан неправильный элемент для отрисовки в нем окна структурированных параметров.'));
										}
									}
								}, 200);
							} else {
								// Скрываем окно структурированных параметров для нешаблонных разделов
								if (getWnd('swStructuredParamsWindow').isVisible()) {
									getWnd('swStructuredParamsWindow').hide();
								}
							}
						}
					});
				}

				theEditor.on('blur', function () {
					if(getRegionNick()=='vologda') {
						var win = Ext.WindowMgr.getActive();
						if(win.T9list && win.T9list.isVisible()) return;
					}
					$(this.contentAreaContainer.parentElement).find("div.mce-toolbar-grp").hide();
					$(this.contentAreaContainer.parentElement).find("div.mce-statusbar").hide();

					if (theEditor.lastValue != theEditor.getContent()) {
						theEditor.saveValue();
					}

					if (theEditor.allowStructuredParams) {
						// Функция для скрытия окна выбора параметров в случае если мы покидаем текстовое поле
						// функция вызывается отложенно, чтобы в document.activeElement успел попасть новый активный элемент, куда мы переходим
						setTimeout(function() {
							if ( Ext.get("swStructuredParamsWindow") ) {
								if ( !(
										$.contains(Ext.get("swStructuredParamsWindow"), document.activeElement) || // выбранный элемент находится в окне выбора параметров
										$(document.activeElement).hasClass("nicEdit-main") || // выбранный элемент - другое поле ввода
										$(document.activeElement).is('body') // не выбран элемент, в этом случае в качестве выбранного элемента возвращается body. Нужно для того, чтобы окно не закрывалось при клике по пустому месту в том числе в окне выбора параметров
									)
								) {
									Ext.getCmp("swStructuredParamsWindow").hide();
								}
							}
						}, 100);
					}
				});

				theEditor.on('NodeChange', function() {
					sw.Promed.EvnXml.refreshEditorSize(theEditor);
				});
			}
		}

		tinyMCE.createEditor(field_id, options);
		instance = tinyMCE.get(field_id);
    });
    return instance;
};

sw.Promed.EvnXml = function(data) {
	//log(data);
	var me = this,
		XmlType_id = data.XmlType_id,
		EvnXml_id = data.EvnXml_id,
		xml_data = data.xml_data,
		instance_id = data.instance_id,
		EvnClass_id = data.EvnClass_id,
		Evn_id = data.Evn_id || null,
		Evn_pid = data.Evn_pid || null,
		Evn_rid = data.Evn_rid || null,
		dom = data.dom,
		cmp = data.cmp,
		textEditConfig = data.textEditConfig || {},
		allowStructuredParams = data.allowStructuredParams || false,
		onBeforeSectionSave = data.onBeforeSectionSave || Ext.emptyFn,
		onAfterSectionSave = data.onAfterSectionSave || Ext.emptyFn,
		outputMsg = data.outputMsg || Ext.emptyFn,
		onAllSave = Ext.emptyFn,
		isChanged = false,
		cntSaveRequests = 0,
		beforeSectionSave = function() {
			cntSaveRequests++;
			isChanged = true;
			if (typeof(onBeforeSectionSave)  == 'function') {
				onBeforeSectionSave();
			}
		},
		afterSectionSave = function() {
			cntSaveRequests--;
			if (0 == cntSaveRequests) {
				isChanged = false;
				onAllSave();
                onAllSave = Ext.emptyFn;
			}
			if (typeof(onAfterSectionSave)  == 'function') {
				onAfterSectionSave();
			}
		},
		parameterValueCollection = new sw.Promed.ParameterValueCollection({
			dom: dom,
			EvnXml_id: EvnXml_id
		}),
		swTextEditors = new sw.Promed.swTextEditors(cmp, allowStructuredParams, instance_id),
		i;
	// отрисовка компонентов редактирования параметров с выбором значения
	parameterValueCollection.render();
	// отрисовка визуальных редакторов
	swTextEditors.render({
		EvnXml_id: EvnXml_id
		,EvnClass_id: EvnClass_id
		,Evn_id: Evn_id
		,Evn_pid: Evn_pid
		,Evn_rid: Evn_rid
		,xml_data: xml_data
	});
	
	for (i = 0; parameterValueCollection.items.length > i; i++) {
		parameterValueCollection.items[i].on('beforeSave', beforeSectionSave);
		parameterValueCollection.items[i].on('afterSave', afterSectionSave);
	}
	for (var key in swTextEditors.cmp.input_cmp_list) {
		swTextEditors.beforeSave = beforeSectionSave;
		swTextEditors.afterSave = afterSectionSave;
	}
	
	function checkAllSave(onOk) {
		if (cntSaveRequests > 0) {
			onAllSave = onOk;
            // onAllSave будет вызван, когда будет сохранен последний измененный раздел
			return false;
		}
		if (isChanged) {
            // в эту ветку не должно зайти, т.к. срабатывает автосохранение при изменении
			onAllSave = onOk;
            // @todo пока только, чтобы проверить будет ли заходить в эту ветку
            me.doSave();
			return false;
		}
		onOk();
		return true;
    }
    me.doSave = function() {
        outputMsg(langs('Запрос для сохранения изменений в документе не был отправлен!'), 'warning');
        // @todo сделать вызов onAllSave, когда документ будет полностью сохранен
        onAllSave();
    };
    me.doPrint = function() {
		var onOk = function() {
			sw.Promed.EvnXml.doPrintById(EvnXml_id);
			//window.open('/?c=EvnXml&m=doPrint&EvnXml_id=' + EvnXml_id, '_blank');
		};
		if (false == checkAllSave(onOk) &&  27 != EvnClass_id) {
			outputMsg(langs('Изменения в документе еще не сохранены!<br>Документ будет распечатан после сохранения изменений документа'), 'loading');
		}
	};
    me.doPrintHalf = function(half) {
		var onOk = function() {
			sw.Promed.EvnXml.doPrintByIdHalf(EvnXml_id, half);
		};
		if (false == checkAllSave(onOk) &&  27 != EvnClass_id) {
			outputMsg(langs('Изменения в документе еще не сохранены!<br>Документ будет распечатан после сохранения изменений документа'), 'loading');
		}
	};
	me.saveAsXmlTemplate = function() {
		var onOk = function() {
			var loadMask = new Ext.LoadMask(Ext.get(cmp.id), { msg: langs('Пожалуйста, подождите, идет сохранение документа как шаблона...') });
			loadMask.show();
			Ext.Ajax.request({
				url: '/?c=EvnXml&m=saveAsTemplate',
				callback: function(opt, success, response) {
					loadMask.hide();
					if (success && response.responseText != '') {
						var response_obj = Ext.util.JSON.decode(response.responseText);
						if (response_obj.success) {
							getWnd('swXmlTemplateSettingsEditWindow').show({
								XmlTemplate_id: response_obj.XmlTemplate_id,
								disabledChangeEvnClass: true,
								disabledChangeXmlType: true,
								callback: function() {
									//
								}
							});
						}
					}
				},
				params: { EvnXml_id: EvnXml_id }
			});
		};
		if (false == checkAllSave(onOk)) {
			outputMsg(langs('Изменения в документе еще не сохранены!<br>Документ будет сохранен как шаблон после сохранения изменений документа'), 'loading');
		}
	};
	return me;
};
// ниже статические свойства, методы, константы
sw.Promed.EvnXml = Ext.apply(sw.Promed.EvnXml, {
    MULTIPLE_DOCUMENT_TYPE_ID: 2,
    EVN_VIZIT_PROTOCOL_TYPE_ID: 3,
    EVN_USLUGA_PROTOCOL_TYPE_ID: 4,
    LAB_USLUGA_PROTOCOL_TYPE_ID: 7,
    STAC_PROTOCOL_TYPE_ID: 8,
    STAC_RECORD_TYPE_ID: 9,
    STAC_EPIKRIZ_TYPE_ID: 10,
	EVN_FORENSIC_ACT_TYPE_ID: 11,
	EVN_FORENSIC_CRIMINAL_CASE_CONCLUSION: 12,
	EVN_FORENSIC_ADMINISTRATIVE_CASE_CONCLUSION: 13,
	EVN_FORENSIC_MEDICAL_RESEARCH_ACT: 14,
	EVN_FORENSIC_MEDICAL_EXAMINATION_ACT: 15,
	//Заключение эксперта по уголовным делам; Заключение эксперта по административным делам; Акт медицинского исследования; Акт медицинского освидетельствования
	EVN_FORENSIC_CRIMINAL_CASE_CONCLUSION_TYPE_ID: 13, // Заключение эксперта по уголовным делам
	EVN_FORENSIC_ADMINISTRATIVE_CASE_CONCLUSION_TYPE_ID: 14, // Заключение эксперта по административным делам
	EVN_FORENSIC_MEDICAL_RESEARCH_ACT_TYPE_ID: 15, // Акт медицинского исследования
	EVN_FORENSIC_MEDICAL_EXAMINATION_ACT_TYPE_ID: 16, // Акт медицинского освидетельствования
    OPERATION_PROTOCOL_TYPE_ID: 17, // Протокол операции
	EVN_DIRECTION_MRT_TYPE_ID: 18, // Направление на МРТ
	EVN_DIRECTION_CT_TYPE_ID: 19, // Направление на КТ
	EVN_DIRECTION_RMC_TYPE_ID: 20, // Удаленная консультация
	DESCRIPTION_OF_THINGS_AND_VALUES: 21, // Опись вещей и ценностей (Вологда, Москва, Уфа)
	NARCOSIS_PROTOCOL_TYPE_ID: 22, // Протокол анестезии (Москва)
    loadXmlDataSectionStore: function(params, callback) {
        var self = this;
        if (!params) {
            params = {};
        }
        if (!callback) {
            callback = Ext.emptyFn;
        }
        if (!self.XmlDataSectionStore) {
            self.XmlDataSectionStore = new sw.Promed.LocalStorage({
                tableName: 'XmlDataSection'
                ,typeCode: 'int'
                ,allowSysNick: true
                ,loadParams: {params: params}
                ,onLoadStore: function(){
                    callback(self.XmlDataSectionStore);
                }
            });
            return true;
        }
        callback(self.XmlDataSectionStore);
        return true;
    },
    doPrintById: function (id) {
        window.open('/?c=EvnXml&m=doPrint&EvnXml_id=' + id, '_blank');
    },
    doPrintByIdHalf:function(id, half) {
    	window.open('/?c=EvnXml&m=doPrint&doHalf='+half+'&EvnXml_id=' + id, '_blank');
    },
    getNotViewXmlTypeIdList: function (evnclass_id) {
        /*
         какой категории (классу события) могут принадлежать документы того или иного типа
         select distinct
         xt.XmlType_id as view_XmlType_id,
         xt.XmlType_Name as view_XmlType_Name,
         EvnClass.EvnClass_id,
         EvnClass.EvnClass_SysNick
         from v_XmlType xt
         outer apply (
         select distinct
         xe.EvnClass_id
         from v_XmlTemplateEvnClass xe
         join v_XmlTemplate x on xe.XmlTemplate_id = x.XmlTemplate_id and x.XmlType_id=xt.XmlType_id
         ) xe
         join EvnClass on EvnClass.EvnClass_id = xe.EvnClass_id
         order by xt.XmlType_id
         */
        evnclass_id = evnclass_id+'';
        var not_view_id_list = [];
        if (false == evnclass_id.inlist(['30','32'])) {
            not_view_id_list.push(''+this.STAC_PROTOCOL_TYPE_ID);
            not_view_id_list.push(''+this.STAC_RECORD_TYPE_ID);
        }
        if (false == evnclass_id.inlist(['11','32'])) {
            not_view_id_list.push(''+this.STAC_EPIKRIZ_TYPE_ID);
        }
        if (false == evnclass_id.inlist(['11','13','21','22','29','43','47'])) {
            not_view_id_list.push(''+this.EVN_USLUGA_PROTOCOL_TYPE_ID);
        }
        if (false == evnclass_id.inlist(['21','47'])) {
            not_view_id_list.push(''+this.LAB_USLUGA_PROTOCOL_TYPE_ID);
        }
        if (false == evnclass_id.inlist(['3','6','11','13','14'])) {
            not_view_id_list.push(''+this.EVN_VIZIT_PROTOCOL_TYPE_ID);
        }
        if (false == evnclass_id.inlist(['160'])) {
            not_view_id_list.push(''+1);
        }
        if (evnclass_id.inlist(['14','160'])) {
            not_view_id_list.push(''+this.MULTIPLE_DOCUMENT_TYPE_ID);
        }
		if (!evnclass_id.inlist(['120'])) {
			not_view_id_list.push(''+this.EVN_FORENSIC_ACT_TYPE_ID);
			not_view_id_list.push(''+this.EVN_FORENSIC_CRIMINAL_CASE_CONCLUSION_TYPE_ID);
			not_view_id_list.push(''+this.EVN_FORENSIC_ADMINISTRATIVE_CASE_CONCLUSION_TYPE_ID);
			not_view_id_list.push(''+this.EVN_FORENSIC_MEDICAL_RESEARCH_ACT_TYPE_ID);
			not_view_id_list.push(''+this.EVN_FORENSIC_MEDICAL_EXAMINATION_ACT_TYPE_ID);
		}
		if (!evnclass_id.inlist(['43','29'])) {
			not_view_id_list.push(''+this.OPERATION_PROTOCOL_TYPE_ID);
		}
		if (!evnclass_id.inlist(['27'])) {
			not_view_id_list.push(''+this.EVN_DIRECTION_MRT_TYPE_ID);
			not_view_id_list.push(''+this.EVN_DIRECTION_CT_TYPE_ID);
			not_view_id_list.push(''+this.EVN_DIRECTION_RMC_TYPE_ID);
		}
		if(!getRegionNick().inlist(['vologda','msk','ufa']) || !evnclass_id.inlist(['32'])) {
			not_view_id_list.push(''+this.DESCRIPTION_OF_THINGS_AND_VALUES);
		}
        return not_view_id_list;
    },
	removeEditors: function(input_cmp_list) {
		if (input_cmp_list) {
			for (var key in input_cmp_list) {
				var item = input_cmp_list[key];
				if (item instanceof tinyMCE.Editor) {
					item.remove();
				}
			}
		}
	},
	removeNonExistEditors: function(input_cmp_list) {
		if (input_cmp_list) {
			for (var key in input_cmp_list) {
				var item = input_cmp_list[key];
				if (item instanceof tinyMCE.Editor && !Ext.get(key)) {
					delete input_cmp_list[key];
					item.remove();
				}
			}
		}
	},
	refreshEditorSize: function(editor) {
		var iframe = editor.iframeElement;
		var body = editor.getBody();

		if (iframe && body) {
			var height = 0;
			var children = body.children;
			var iframeHeight = iframe.getBoundingClientRect().height;

			for (var i = 0; i < children.length; i++) {
				if (children[i].className == 'mce-resizehandle') {
					continue;
				}
				var _height = Ext.fly(children[i]).getBottom();
				if (height < _height) {
					height = _height;
				}
			}
			height = Math.ceil(height);

			if (height > 0 && iframeHeight != height) {
				iframe.style.height = (height + 30) + 'px';
			}
		}
	}
});

sw.Promed.XmlTemplateScopePanel = Ext.extend(sw.Promed.Panel,{
    object: '',
    tabIndexStart: 0,
    autoHeight: true,
    border: false,
    buttonAlign: 'left',
    labelAlign: 'left',
    labelWidth: 170,
	/**
	 * @param {Ext.form.BasicForm} form
	 * @param {String} action
	 */
	onLoadForm: function(form, action) {
		var me = this,
			access_view_combo = form.findField('XmlTemplateScope_id'),
			access_edit_combo = form.findField('XmlTemplateScope_eid'),
			Lpu_id, LpuSection_id;
		me._baseForm = form;
		if ('add' == action) {
			Lpu_id = getGlobalOptions().lpu_id;
		}
		if (form.findField('Lpu_id').getValue()) {
			Lpu_id = form.findField('Lpu_id').getValue();
		}
		if ('add' == action) {
			LpuSection_id = getGlobalOptions().CurLpuSection_id;
		}
		if (form.findField('LpuSection_id').getValue()) {
			LpuSection_id = form.findField('LpuSection_id').getValue();
		}
		if ('add' == action) {
			access_edit_combo.setValue(sw.Promed.XmlTemplateScopePanel.getDefaultXmlTemplateScopeEid(this.object, Lpu_id, LpuSection_id));
			access_view_combo.setValue(sw.Promed.XmlTemplateScopePanel.getDefaultXmlTemplateScopeId(this.object, Lpu_id, LpuSection_id));
		}
		var onLoadStore = function(combo, is_edit_combo){
			var allowList = ['5'];
			if (Lpu_id) {
				// Нельзя выбрать МО автора, если нет Lpu_id
				allowList.push('3');
			}
			if (Lpu_id && LpuSection_id) {
				// Нельзя выбрать отделение автора, если нет LpuSection_id и нет Lpu_id
				allowList.push('4');
			}
			if (isSuperAdmin() && 'add' == action) {
				allowList = ['1','2'];
			}
			if (isSuperAdmin() && 'add' != action) {
				allowList.push('1');
				allowList.push('2');
			}
			if (is_edit_combo && !access_view_combo.getValue()) {
				combo.setValue(null);
			}
			if (is_edit_combo && access_view_combo.getValue() && combo.getValue()) {
				//свойство редактирования всегда должно быть более жестким, либо таким же
				if (access_view_combo.getValue() == 1) {
					// Видимость Суперадмин - редактировать только Суперадмин
					combo.setValue(access_view_combo.getValue());
				}
				if (access_view_combo.getValue() > 2 && combo.getValue() < access_view_combo.getValue()) {
					/*
					Видимость Автор - редактировать только автор, если редактировать было не только автор
					Видимость отделение автора - редактировать отделения автора, если редактировать было не только автор или отделение автора
					Видимость МО автора - редактировать МО автора, если редактировать было не только автор или отделение автора или МО автора
					*/
					combo.setValue(access_view_combo.getValue());
				}
			}
			combo.getStore().each(function(rec) {
				if (false == rec.get('XmlTemplateScope_id').toString().inlist(allowList)) {
					combo.getStore().remove(rec);
				}
			});
			var index = combo.getStore().findBy(function(rec) {
				return ( rec.get('XmlTemplateScope_id') == combo.getValue() );
			});
			if ( index < 0 ) {
				combo.setValue(null);
				me.onSelectXmlTemplateScope(combo, null);
			} else {
				me.onSelectXmlTemplateScope(combo, combo.getStore().getAt(index));
			}
			if (!is_edit_combo) {
				access_edit_combo.getStore().removeAll();
				access_edit_combo.getStore().load({
					callback: function(r,o,s){
						onLoadStore(access_edit_combo, true);
					}
				});
			}
		};
		access_view_combo.getStore().removeAll();
		access_view_combo.getStore().load({
			callback: function(r,o,s){
				onLoadStore(access_view_combo, false);
			}
		});
	},
	onSelectXmlTemplateScope: function(combo, rec) {
		var bf = this._baseForm;
		if (bf) {
			var comment_field = bf.findField(combo.hiddenName + '_comment'),
				user_name = bf.findField('PMUser_Name').getValue()||'',
				ls_name = bf.findField('LpuSection_Name').getValue()||'',
				lpu_name = bf.findField('Lpu_Name').getValue()||'',
				comment = '';
			if (!rec) {
				comment_field.setValue(comment);
				return true;
			}
			switch (true) {
				case (5 == rec.get('XmlTemplateScope_id')):
					comment = lpu_name + '/' + user_name;
					break;
				case (4 == rec.get('XmlTemplateScope_id')):
					comment = lpu_name + '/' + ls_name;
					break;
				case (3 == rec.get('XmlTemplateScope_id')):
					comment = lpu_name;
					break;
				default:
					comment = '';
					break;
			}
			comment_field.setValue(comment);
			return true;
		}
		return false;
	},
    /**
     * Инициализация
     */
    initComponent: function() {
        var me = this;
		me.items = [{
			xtype: 'fieldset',
			autoHeight: true,
			title: langs('Права доступа'),
			style: 'padding: 0; padding-left: 5px',
			items: [{
				name: 'LpuSection_id',
				xtype: 'hidden'
			}, {
				name: 'Lpu_id',
				xtype: 'hidden'
			}, {
				name: 'LpuSection_Name',
				xtype: 'hidden'
			}, {
				name: 'Lpu_Name',
				xtype: 'hidden'
			}, {
				name: 'PMUser_Name',
				xtype: 'hidden'
			}, {
				layout: 'column',
				border: false,
				width: '100%',
				items: [{
					layout: 'form',
					border: false,
					columnWidth: 0.6,
					items: [{
						fieldLabel: langs('Видимость'),
						allowBlank: false,
						width: 180,
						tabIndex: me.tabIndexStart,
						hiddenName: 'XmlTemplateScope_id',
						comboSubject: 'XmlTemplateScope',
						allowSysNick: false,
						autoLoad: false,
						xtype: 'swcommonsprcombo',
						listeners: {
							select: function(combo, rec) {
								me.onSelectXmlTemplateScope(combo, rec);
							}
						}
					}]
				},{
					layout: 'form',
					border: false,
					columnWidth: 0.4,
					style: 'margin-left: 5px;',
					items: [{
						hideLabel: true,
						readOnly: true,
						width: '95%',
						name: 'XmlTemplateScope_id_comment',
						xtype: 'textfield'
					}]
				}]
			}, {
				layout: 'column',
				border: false,
				width: '100%',
				items: [{
					layout: 'form',
					border: false,
					columnWidth: 0.6,
					items: [{
						fieldLabel: langs('Доступность для изменения'),
						allowBlank: false,
						width: 180,
						tabIndex: me.tabIndexStart+1,
						hiddenName: 'XmlTemplateScope_eid',
						comboSubject: 'XmlTemplateScope',
						allowSysNick: false,
						autoLoad: false,
						xtype: 'swcommonsprcombo',
						listeners: {
							select: function(combo, rec) {
								me.onSelectXmlTemplateScope(combo, rec);
							}
						}
					}]
				},{
					layout: 'form',
					border: false,
					columnWidth: 0.4,
					style: 'margin-left: 5px;',
					items: [{
						hideLabel: true,
						readOnly: true,
						width: '95%',
						name: 'XmlTemplateScope_eid_comment',
						xtype: 'textfield'
					}]
				}]
			}]
		}];
        sw.Promed.XmlTemplateScopePanel.superclass.initComponent.apply(this, arguments);
    }
});
// ниже статические свойства, методы, константы
sw.Promed.XmlTemplateScopePanel = Ext.apply(sw.Promed.XmlTemplateScopePanel, {
    /**
     * Доступность для изменения по умолчанию
     * @param {String} object
     * @param {Number} Lpu_id
     * @param {Number} LpuSection_id
     * @return {Number}
     */
    getDefaultXmlTemplateScopeEid: function(object, Lpu_id, LpuSection_id) {
        var id = 5; // Автор
        if (isSuperAdmin()) {
            id = 1; // Администратор системы
        }
        return id;
    },
    /**
     * Видимость по умолчанию
     * @param {String} object
     * @param {Number} Lpu_id
     * @param {Number} LpuSection_id
     * @return {Number}
     */
    getDefaultXmlTemplateScopeId: function(object, Lpu_id, LpuSection_id) {
        var id = 5; // Автор
        if (LpuSection_id) {
            id = 4; // Отделение автора
        }
        if (isLpuAdmin() && Lpu_id) {
            id = 3; // МО автора
        }
        if (isSuperAdmin()) {
            id = 2; // Все
        }
        return id;
    }
});

sw.Promed.XmlTemplateCatDefault = {
    /**
     * Получение идентификатора папки по умолчанию c массивом его папок верхнего уровня
     * @param {Object} params
     * @param {Function} callback
     * @param {sw.Promed.BaseForm} scope
     * @return {Boolean}
     */
    loadId: function(params, callback, scope) {
        var hasKey = false;
        if (params.MedStaffFact_id) {
            hasKey = true;
        } else if (params.MedService_id && params.MedPersonal_id) {
            hasKey = true;
        }
		if (false == hasKey) {
			// Невозможно получить папку по умолчанию, например, при работе в АРМ регистратора поликлиники)
			callback.call(scope, true, null, []);
			return true;
		}
        if (!params.EvnClass_id || !params.XmlType_id) {
            callback.call(scope, false, langs('Недостаточно параметров для получения папки по умолчанию'), []);
            return false;
        }
        if (!params.LpuSection_id) {
            params.LpuSection_id = getGlobalOptions().CurLpuSection_id || null;
        }
        // шлем запрос
        var loadMask = scope.getLoadMask(langs('Подождите, идет получение папки по умолчанию...'));
        loadMask.show();
        Ext.Ajax.request({
            url: '/?c=XmlTemplateCatDefault&m=getPath',
            callback: function(o, s, response)
            {
                loadMask.hide();
                if ( s ) {
                    var result = Ext.util.JSON.decode(response.responseText);
                    if (result.length > 0) {
                        callback.call(scope, true, result[0].XmlTemplateCat_id, result);
                    } else {
                        callback.call(scope, true, null, []);
                    }
                } else {
                    callback.call(scope, false, langs('Ошибка при выполнении запроса получения папки по умолчанию.'), []);
                }
            },
            params: params
        });
        return true;
    },
    /**
     * Доступна ли для редактирования корневая папка
     *
     * Если доступна, то в ней можно добавлять/удалять шаблоны и папки.
     * @return {Boolean}
     */
    isAllowRootFolder: function()
    {
        var ms_id = sw.Promed.MedStaffFactByUser.last.MedService_id || getGlobalOptions().CurMedService_id,
            msf_id = sw.Promed.MedStaffFactByUser.last.MedStaffFact_id || getGlobalOptions().CurMedStaffFact_id;
        if (sw.Promed.MedStaffFactByUser.last.ARMType && 'superadmin' == sw.Promed.MedStaffFactByUser.last.ARMType) {
            ms_id = msf_id = null;
        }
        // Редактировать корневую папку можно только из АРМа ЦОД
        return (isSuperAdmin()
            && Ext.isEmpty(ms_id)
            && Ext.isEmpty(msf_id)
        );
    },
    /**
     * Проверка возможности логики папок и шаблонов по умолчанию
     * @param {Object} userMedStaffFact
     * @return {Boolean}
     */
    isDisableDefaults: function(userMedStaffFact)
    {
        if (!userMedStaffFact) {
            userMedStaffFact = {};
        }
        var isDisable = false,
            lpu_id = userMedStaffFact.Lpu_id || getGlobalOptions().lpu_id,
            ms_id = userMedStaffFact.MedService_id || getGlobalOptions().CurMedService_id,
            msf_id = userMedStaffFact.MedStaffFact_id || getGlobalOptions().CurMedStaffFact_id;
        if (userMedStaffFact.ARMType && userMedStaffFact.ARMType.inlist(['superadmin','lpuadmin'])) {
            ms_id = msf_id = null;
        }
        switch (true) {
            case (isSuperAdmin() && Ext.isEmpty(msf_id) && Ext.isEmpty(ms_id)):
                // работа из АРМа ЦОД
                isDisable = true;
                break;
            case (isLpuAdmin(lpu_id) && Ext.isEmpty(msf_id) && Ext.isEmpty(ms_id)):
                // работа из АРМа администратора ЛПУ
                isDisable = true;
                break;
        }
        return isDisable;
    },
	checkParamsSetXmlTemplateCatDefault: function (params)
	{
		var hasKey = false;
		if (params.MedStaffFact_id) {
			hasKey = true;
		} else if (params.MedService_id && params.MedPersonal_id) {
			hasKey = true;
		}
		if (!params.XmlTemplateCat_id
			|| false == hasKey
			|| !params.EvnClass_id
			|| !params.XmlType_id
		) {
			return false;
		}
		return true;
	},
	checkParamsSetXmlTemplateDefault: function (params)
	{
		var hasKey = false;
		if (params.MedStaffFact_id) {
			hasKey = true;
		} else if (params.MedService_id && params.MedPersonal_id) {
			hasKey = true;
		}
		if (!params.XmlTemplate_id
			|| false == hasKey
			|| !params.EvnClass_id
			|| !params.XmlType_id
		) {
			return false;
		}
		return true;
	},
    /**
     * Сохранение папки по умолчанию
     * @param {Object} params
     * @param {Function} callback
     * @param {sw.Promed.BaseForm} scope
     * @return {Boolean}
     */
    save: function(params, callback, scope) {
		if (false == this.checkParamsSetXmlTemplateCatDefault(params) ) {
			callback.call(scope, false, langs('Недостаточно параметров для сохранения папки по умолчанию'));
			return false;
		}
        var loadMask = scope.getLoadMask(langs('Подождите, идет сохранение папки по умолчанию...'));
        loadMask.show();
        Ext.Ajax.request({
            url: '/?c=XmlTemplateCatDefault&m=save',
            callback: function(options, success, response)
            {
                loadMask.hide();
                if ( success ) {
                    var result = Ext.util.JSON.decode(response.responseText);
                    if (result['success']) {
                        callback.call(scope, true, result['XmlTemplateCatDefault_id']);
                    } else {
                        callback.call(scope, false, result['Error_Msg']);
                    }
                } else {
                    callback.call(scope, false, langs('Ошибка при выполнении запроса сохранения папки по умолчанию.'));
                }
            },
            params: params
        });
        return true;
    },
    /**
     * Создание папки по умолчанию
     * @param {Object} params
     * @param {Function} callback
     * @param {sw.Promed.BaseForm} scope
     * @return {Boolean}
     */
    create: function(params, callback, scope) {
        if (this.isDisableDefaults(sw.Promed.MedStaffFactByUser.last)) {
            callback.call(scope, false, {success: false, Error_Msg: langs('Запрещено создание папки по умолчанию')}, []);
            return false;
        }
        if (!params.MedStaffFact_id || !params.EvnClass_id || !params.XmlType_id) {
            callback.call(scope, false, {success: false, Error_Msg: langs('Недостаточно параметров для создания папки по умолчанию')}, []);
            return false;
        }
        var loadMask = scope.getLoadMask(langs('Подождите, идет создание папки по умолчанию...')),
            self = this;
        loadMask.show();
        Ext.Ajax.request({
            url: '/?c=XmlTemplateCat&m=createDefault',
            callback: function(options, success, response)
            {
                loadMask.hide();
                if ( success ) {
                    var result = Ext.util.JSON.decode(response.responseText);
                    if (result['success'] && !result['Error_Msg'] && result['XmlTemplateCat_id']) {
                        callback.call(scope, true, result, [result]);
                    } else {
                        callback.call(scope, false, result, []);
                    }
                } else {
                    callback.call(scope, false, {success: false, Error_Msg: langs('Ошибка при выполнении запроса создания папки по умолчанию.')}, []);
                }
            },
            params: params
        });
        return true;
    }
};

sw.Promed.EvnXmlPanel = Ext.extend(sw.Promed.Panel,{
    // определение параметров конфигурации панели
    ownerWin: {},
    LpuSectionField: null,
    MedStaffFactField: null,
	Person_id: null,
	EvnUslugaPar_id: null,
	consultFunction: Ext.emptyFn,
    options: {
        XmlType_id: null, // Фильтр: Тип документа, который может быть загружен в панель
        EvnClass_id: null // Фильтр: Категория документа, который может быть загружен в панель и категория шаблонов, которые могут быть выбраны для документа
    },
    viewOptions: {
        isWithSelectXmlTemplateBtn: true,
        isWithRestoreXmlTemplateBtn: true,
        isWithEvnXmlClearBtn: true,
        isWithPrintBtn: true
    },
	signEnabled: false,
	biRadsEnabled: false,
	RECISTEnabled: false,
    onClickPrintBtn: function(panel) {
        // По умолчанию используем стандартный обработчик
        panel.doPrint();
    },
    onAfterClearViewForm: function(panel) {
        //
    },
    onAfterLoadData: function(panel) {
        //
    },
    listeners: {
        expand: function(panel) {
            if ( panel.isLoaded === false ) {
                panel.isLoaded = true;
            }
        }.createDelegate(this)
    },
    /**
     * Метод, в котором должна быть выполнена проверка, что учетный документ сохранен
     * Если не сохранен, то должно выполниться сохранение и установка базовых параметров в панели
     * Если сохранен и есть базовые параметры, то должен быть выполнен указанный метод
     *
     * @param {sw.Promed.EvnXmlPanel} panel Экземпляр панели
     * @param {String} method Имя метода панели
     * @param {*} params Параметры для метода (опционально)
     * @return {Boolean}
     */
    onBeforeCreate: function (panel, method, params) {
        if (!panel || !method || typeof panel[method] != 'function') {
            return false;
        }
        panel[method](params);
        return true;
    },
    // конец определения параметров конфигурации панели

    baseParams: {
        Evn_id: null,
        UslugaComplex_id: null,
        Server_id: null,
        userMedStaffFact: {}
    },

    // параметры документа
    _EvnXml_id: null,
    _XmlTemplate_id: null,
    _XmlTemplateType_id: null,
    _xml_data: null,
    _isReadOnly: false,
    /**
     * Признак, что документ загружен
     */
    _isLoaded: false,

    // методы для базовых параметров
    setBaseParams: function(obj) {
        this.baseParams.Evn_id = obj.Evn_id;
        this.baseParams.userMedStaffFact = obj.userMedStaffFact || null;
        this.baseParams.Server_id = obj.Server_id || null;
        this.baseParams.UslugaComplex_id = obj.UslugaComplex_id || null;
    },
    getUserMedStaffFact: function() {
        if (this.baseParams.userMedStaffFact) {
            return this.baseParams.userMedStaffFact;
        } else {
            return sw.Promed.MedStaffFactByUser.last || {};
        }
    },
    getUserMedPersonalId: function() {
        return this.getUserMedStaffFact().MedPersonal_id || null;
    },
    getUserMedServiceId: function() {
        return this.getUserMedStaffFact().MedService_id || null;
    },
    getUserLpuSectionId: function() {
        var umsf = this.getUserMedStaffFact();
        var lpusection_id = umsf.LpuSection_id || null;
        if (!lpusection_id && this.LpuSectionField) {
            lpusection_id = this.LpuSectionField.getValue() || null;
        }
        return lpusection_id;
    },
	getUserMedStaffFactId: function() {
		var umsf = this.getUserMedStaffFact();
		var medstafffact_id = umsf.MedStaffFact_id || null;
		if (!medstafffact_id && this.MedStaffFactField) {
			//в службах в umsf нет medstafffact_id, но в отделении у врача есть место работы
			if ('MedStaffFact_id' == this.MedStaffFactField.valueField) {
				medstafffact_id = this.MedStaffFactField.getValue() || null;
			}
			//оказывается в MedStaffFactField делают так valueField: 'MedPersonal_id'
			if (!medstafffact_id || 'MedPersonal_id' == this.MedStaffFactField.valueField) {
				//ищем по отделению и врачу, берем первое
				var ix, msf,
					medpersonal_id = this.getUserMedPersonalId(),
					lpusection_id = this.getUserLpuSectionId(),
					store = this.MedStaffFactField.getStore();
				ix = store.findBy(function(r) {
					return (r.get('MedPersonal_id') == medpersonal_id && r.get('LpuSection_id') == lpusection_id);
				});
				msf = store.getAt(ix);
				if (msf) {
					medstafffact_id = msf.get('MedStaffFact_id');
				}
			}
		}
		return medstafffact_id;
	},
    getServerId: function() {
        return this.baseParams.Server_id;
    },
    getEvnId: function() {
        return this.baseParams.Evn_id;
    },
    getUslugaComplexId: function() {
        return this.baseParams.UslugaComplex_id;
    },
    // конец определения методов для базовых параметров

    getOption: function(name) {
        return this.options[name] || null;
    },
    getFilterEvnClassId: function() {
        return this.options.EvnClass_id || null;
    },

    // методы получения параметров загруженного документа
    getXmlTemplateId: function() {
        return this._XmlTemplate_id;
    },
    getXmlTypeId: function() {
        return this._XmlType_id;
    },
    getXmlTemplateTypeId: function() {
        return this._XmlTemplateType_id;
    },
    getEvnXmlId: function() {
        return this._EvnXml_id;
    },
    getIsFreeDocument: function()
    {
        return (sw.Promed.EvnXml.MULTIPLE_DOCUMENT_TYPE_ID == this.getXmlTypeId());
    },
    getIsVizitProtocol: function()
    {
        return (sw.Promed.EvnXml.EVN_VIZIT_PROTOCOL_TYPE_ID == this.getXmlTypeId());
    },
    getIsUslugaProtocol: function()
    {
        return (sw.Promed.EvnXml.EVN_USLUGA_PROTOCOL_TYPE_ID == this.getXmlTypeId());
    },
    // конец методов получения параметров загруженного документа

    /**
     * Установлен ли режим "Только просмотр документа"
     * @return {Boolean}
     */
    getIsReadOnly: function()
    {
        return this._isReadOnly;
    },
    /**
     * Устанавливает режим просмотра или редактирования документа
     * Должно соответствовать режиму доступа к учетному документу
     * Должно вызываться после загрузки документа
     * @param {Boolean} is_read_only
     */
    setReadOnly: function(is_read_only)
    {
        this._isReadOnly = is_read_only;
        this.getToolbarItem('btnXmlTemplateSelect').setVisible(!this._isReadOnly);
        this.getToolbarItem('btnXmlTemplateRestore').setVisible(!this._isReadOnly);
        this.getToolbarItem('btnEvnXmlClear').setVisible(!this._isReadOnly);
        this.swEMDPanel.setVisible(this.signEnabled);
        //this.getTopToolbar().setVisible(visible); // добавил, т.к. зачем отображать тулбар, если все кнопки на нём скрыты.

    },
    setBiRads: function(is_br)
    {
        this.biRadsEnabled = is_br;
        this.getToolbarItem('btnEvnXmlBIRADS').setVisible(this.biRadsEnabled && !this._isReadOnly);
    },
	setRECIST: function(is_rec)
	{
		this.RECISTEnabled = is_rec;
		this.getToolbarItem('btnEvnXmlRECIST').setVisible(this.RECISTEnabled && !this._isReadOnly);
	},
    getToolbarItem: function(item)
    {
        var i = 0;
        var result = null;
        while (i<this.getTopToolbar().items.length && result==null)
        {
            if (this.getTopToolbar().items.item(i).name==item)
            {
                result = this.getTopToolbar().items.item(i);
            }
            i++;
        }
        return result;
    },
    onEvnSave: Ext.emptyFn,
    /**
     * Обработчик загрузки документа в панель
     * @param {string} html Документ в виде строки HTML
     * @param {object} data Документ в виде объекта
     * @return {Boolean}
     * @access private
     */
    _onLoadData: function(html, data) {
        this.getToolbarItem('btnXmlTemplateSelect').setDisabled(this.getIsReadOnly());
        this.getToolbarItem('btnXmlTemplateRestore').setDisabled(this.getIsReadOnly());
        this.getToolbarItem('btnEvnXmlClear').setDisabled(this.getIsReadOnly());
		this.swEMDPanel.setParams({
			EMDRegistry_ObjectName: 'EvnXml',
			EMDRegistry_ObjectID: data.EvnXml_id
		});
		this.swEMDPanel.setIsSigned(data.EvnXml_IsSigned);
		this.swEMDPanel.setReadOnly(this.getIsReadOnly());
        this.getToolbarItem('btnEvnXmlPrint').setDisabled(isMseDepers());
        this.getToolbarItem('btnEvnXmlCopy').setVisible(false);
        this.doEvnXmlCopy = Ext.emptyFn;

        this.baseParams.Evn_id = data.Evn_id;
        // data.Evn_pid
        // data.Evn_rid
        // data.EvnClass_id
        // data.EvnXml_Name
        this._EvnXml_id = data.EvnXml_id;
        this._xml_data = data.xml_data;
        this._XmlTemplate_id = data.XmlTemplate_id;
        this._XmlTemplateType_id = data.XmlTemplateType_id;
        this._XmlType_id = data.XmlType_id;

		var tpl = new Ext.XTemplate(Ext.util.Format.htmlDecode(html));
        tpl.overwrite(this.body, {});
        this.removeAll();

        // hidePrintOnly
        var node_list = Ext.query("div[class*=printonly]",this.body.dom);
        //log(node_list);
        var i, el;
        for(i=0; i < node_list.length; i++)
        {
            el = new Ext.Element(node_list[i]);
            //log(el);
            el.setStyle({display: 'none'});
        }
        // end hidePrintOnly

        if (this.getIsReadOnly() || !data.xml_data) {
            this.onAfterLoadData(this);
            return true;
        }

        // подсчитаем число разделов документа
        var cnt = 0, section_name;
        for(section_name in data.xml_data) {
            if(typeof data.xml_data[section_name] == 'string') cnt++;
        }
        this.cKEditor = null;
        if (section_name == 'UserTemplateData' && cnt == 1) {
            // нужно редактировать как документ с одним разделом
            var form_tpl = new Ext.XTemplate('');
            form_tpl.overwrite(this.body, {});
            this.cKEditor = new Ext.form.CKEditor({
                name: section_name,
                hideLabel: true,
                height: "200",
                value: data.xml_data[section_name]
            });
            this.add({
                xtype: "fieldset",
                autoHeight: true,
                labelAlign: "top",
                region: "center",
                style: "border: 0;",
                items:[
                    this.cKEditor
                ]
            });
            this.onAfterLoadData(this);
            this.onEvnSave = function() {
                // нужно сохранить возможные изменения
                // в единственном разделе Xml-документа
                // после сохранения учетного документа
                var field = this.cKEditor;
                if (field && field.getCKEditor()) {
                    Ext.Ajax.request({
                        url: '/?c=EvnXml&m=updateContent',
                        callback: function(opt, success, response) {
                            if (success && response.responseText != '') {
                                var response_obj = Ext.util.JSON.decode(response.responseText);
                                //
                            }
                        },
                        params: {EvnXml_id: this.getEvnXmlId(), name: section_name, value: field.getCKEditor().getData(), isHTML: 1}
                    });
                } else {
                    sw.swMsg.alert(langs('Ошибка'), langs('Xml-документ не сохранен. Обратитесь к разработчикам программы.'));
                }
            }.createDelegate(this);
            return true;
        }
        //this.useCkeditor = false;
        var me = this;
        // Создание и отрисовка объектов для редактирования документа
        me.EvnXml = new sw.Promed.EvnXml({
            XmlType_id: data.XmlType_id,//this._XmlType_id
            EvnClass_id: data.EvnClass_id,
            Evn_id: data.Evn_id,//this.baseParams.Evn_id
            //XmlTemplate_id: data.XmlTemplate_id, //this._XmlTemplate_id
            //XmlTemplateType_id: data.XmlTemplateType_id, //this._XmlTemplateType_id
            Evn_pid: data.Evn_pid,
            Evn_rid: data.Evn_rid,
            EvnXml_id: data.EvnXml_id,//this._EvnXml_id
            xml_data: data.xml_data,//this._xml_data
            dom: me.body.dom,
            cmp: me,
            allowStructuredParams: false,
			onAfterSectionSave: function() {
            	if (me.swEMDPanel && me.swEMDPanel.IsSigned == 2) {
					me.swEMDPanel.setIsSigned(1);
				}
			},
            outputMsg: function(msg, useCase) {
                var title = langs('Ошибка');
                if (useCase.inlist(['loading','info'])) {
                    title = langs('Сообщение');
                }
                sw.swMsg.alert(title, msg);
            }
        });

        this.onAfterLoadData(this);
        return true;
    },
    /**
     * Загрузка документа в панель
     * Предварительно должны быть установлены базовые параметры
     * @param {object} options Параметры для загрузки документа
     */
    doLoadData: function(options) {
        if (!options) {
            options = {};
        }
        if (options.EvnXml_id) {
            this._EvnXml_id = options.EvnXml_id;
        }
        if (options.Evn_id) {
            this.baseParams.Evn_id = options.Evn_id;
        }
        this._loadViewForm();
    },
    /**
     * Открытие печатной формы документа в новом окне
     */
    doPrint: function() {
        if (this.EvnXml) {
            //используются компоненты с автосохранением изменений
            this.EvnXml.doPrint();
        } else {
            //используется компонент без автосохранения изменений
            sw.Promed.EvnXml.doPrintById(this.getEvnXmlId());
        }
    },
    /**
     * Обработчик выбора шаблона
     * или в окне поиска и просмотра шаблонов или из списка недавних шаблонов осмотра
     * @param {integer} XmlTemplate_id
     */
    onSelectXmlTemplate: function(XmlTemplate_id) {
        this._createEmpty(XmlTemplate_id);
    },
    /**
     * Выбор шаблона в окне поиска и просмотра шаблонов
     */
    doSelectXmlTemplate: function() {
        getWnd('swTemplSearchWindow').show({
            onSelect: function(params) {
                this.onSelectXmlTemplate(params.XmlTemplate_id);
            }.createDelegate(this),
            LpuSection_id: this.getUserLpuSectionId(),
            MedService_id: this.getUserMedServiceId(),
            MedPersonal_id: this.getUserMedPersonalId(),
            MedStaffFact_id: this.getUserMedStaffFactId(),
            EvnXml_id: this.getEvnXmlId() || null,
            Evn_id: this.getEvnId(),
            UslugaComplex_id: this.getUslugaComplexId(),
            EvnClass_id: this.getOption('EvnClass_id'),
            XmlType_id: this.getOption('XmlType_id'),
			allowSelectXmlType: (this.getOption('XmlType_id') && 2 == this.getOption('XmlType_id')
				&& this.getOption('EvnClass_id') && 27 == this.getOption('EvnClass_id')
			)
        });
    },
    /**
     * Создание документа
     *  из указанного шаблона
     *  или из шаблона по умолчанию
     *  или из базового шаблона этого типа документов
     * Также производится подсчет использования шаблона
     * @param {integer} xmltemplate_id Идентификатор шаблона
     * @access private
     */
    _createEmpty: function(xmltemplate_id) {
        this.ownerWin.getLoadMask().show();
        Ext.Ajax.request({
            url: '/?c=EvnXml&m=createEmpty',
            callback: function(opt, success, response) {
                this.ownerWin.getLoadMask().hide();
                if ( !success || Ext.isEmpty(response.responseText) ) {
                    return false;
                }
                var response_obj = Ext.util.JSON.decode(response.responseText);
                if ( !response_obj.EvnXml_id ) {
                    return false;
                }
                this._EvnXml_id = response_obj.EvnXml_id;
                this._loadViewForm();
            }.createDelegate(this),
            params: {
                // при перевыборе шаблона документа надо обязательно передавать EvnXml_id
                EvnXml_id: this.getEvnXmlId() || null,
                Evn_id: this.getEvnId(),
                XmlType_id: this.getOption('XmlType_id'),
                EvnClass_id: this.getOption('EvnClass_id'),
                Server_id: this.getServerId(),
                MedStaffFact_id: this.getUserMedStaffFactId(),
                XmlTemplate_id: xmltemplate_id || null
            }
        });
    },
    /**
     * Создание копии документа и загрузка созданной копии в панель
     * Учетный документ должен быть создан
     * @param {integer} EvnXml_id Идентификатор документа
     * @access private
     */
    _copy: function(EvnXml_id) {
        var params = {};
        params.EvnXml_id = EvnXml_id;
        params.Evn_id = this.getEvnId();
        this.ownerWin.getLoadMask().show();
        Ext.Ajax.request({
            url: '/?c=EvnXml&m=doCopy',
            callback: function(opt, success, response) {
                this.ownerWin.getLoadMask().hide();
                if ( !success || Ext.isEmpty(response.responseText) ) {
                    return false;
                }
                var response_obj = Ext.util.JSON.decode(response.responseText);
                if ( !response_obj.EvnXml_id ) {
                    return false;
                }
                this._EvnXml_id = response_obj.EvnXml_id;
                this._loadViewForm();
            }.createDelegate(this),
            params: params
        });
    },
    /**
     * Востановление шаблона документа
     */
    doRestoreXmlTemplate: function() {
        this.ownerWin.getLoadMask().show();
        Ext.Ajax.request({
            url: '/?c=EvnXml&m=restore',
            callback: function(opt, success, response) {
                this.ownerWin.getLoadMask().hide();
                if ( !success || Ext.isEmpty(response.responseText) ) {
                    return false;
                }
                var response_obj = Ext.util.JSON.decode(response.responseText);
                if ( !response_obj.EvnXml_id ) {
                    return false;
                }
                this._EvnXml_id = response_obj.EvnXml_id;
                this._loadViewForm();
            }.createDelegate(this),
            params: {
                EvnXml_id: this.getEvnXmlId()
            }
        });
    },
    /**
     * Очистка разделов документа
     * @return {Boolean}
     */
    doClearEvnXml: function() {
        var f,xmldatanew={},flag=false;
        if(this._xml_data) {
            for(var k in this._xml_data) {
                f = (this.input_cmp_list && this.input_cmp_list['field_'+ k+'_'+ this.getEvnXmlId()]) || null;
                if(f) {
                    f.setContent('&nbsp;-');
                    xmldatanew[k] = '&nbsp;-';
                    flag=true;
                }
            }
            if(flag == false)
                return false;
        } else {
            return false;
        }

        this.ownerWin.getLoadMask().show();
        Ext.Ajax.request({
            url: '/?c=EvnXml&m=updateContent',
            callback: function(opt, success, response) {
                this.ownerWin.getLoadMask().hide();
            }.createDelegate(this),
            params: {
                XmlData: Ext.util.JSON.encode(xmldatanew),
                EvnXml_id: this.getEvnXmlId()
            }
        });
        return true;
    },
    /**
     * Сброс параметров при открытии формы с панелью
     * Должно выполняться вместе с очисткой базовой формы
     */
    doReset: function() {
        this._clearViewForm();
        this.removeAll();
        this.baseParams.Evn_id = null;
        this.baseParams.UslugaComplex_id = null;
        this.baseParams.Server_id = null;
        this.baseParams.userMedStaffFact = {};
    },
    /**
     * Очистка панели от параметров документа
     * @access private
     */
    _clearViewForm: function() {
        var tpl = new Ext.XTemplate('');
        tpl.overwrite(this.body, {});
        this._isLoaded = false;
        this._EvnXml_id = null;
        this._XmlTemplate_id = null;
        this._XmlTemplateType_id = null;
        this._XmlType_id = null;
        this._xml_data = null;
        this.getToolbarItem('btnXmlTemplateSelect').setDisabled(this.getIsReadOnly());
        this.getToolbarItem('btnXmlTemplateRestore').setDisabled(true);
        this.getToolbarItem('btnEvnXmlClear').setDisabled(true);
		this.swEMDPanel.setParams({
			EMDRegistry_ObjectName: 'EvnXml',
			EMDRegistry_ObjectID: null
		});
		this.swEMDPanel.setIsSigned(null);
		this.swEMDPanel.setReadOnly(true);
        this.getToolbarItem('btnEvnXmlPrint').setDisabled(true);
        this.getToolbarItem('btnEvnXmlCopy').setVisible(false);
        this.doEvnXmlCopy = Ext.emptyFn;
        this.onAfterClearViewForm(this);
    },
    /**
     * Загружает форму документа
     * @access private
     */
    _loadViewForm: function () {
        this.ownerWin.getLoadMask().show();
        Ext.Ajax.request({
            url: '/?c=EvnXml&m=doLoadData',
            callback: function(options, success, response) {
                this.onEvnSave = Ext.emptyFn;
                this.ownerWin.getLoadMask().hide();
                if ( !success || Ext.isEmpty(response.responseText) ) {
                    return false;
                }
                var response_obj = Ext.util.JSON.decode(response.responseText);
                if ( response_obj.Error_Msg ) {
                    return false;
                }
                if ( !response_obj.html || !response_obj.data ) {
                    this._onNotFound();
                    return false;
                }
                this._isLoaded = true;
                this._onLoadData(response_obj.html, response_obj.data);
                return true;
            }.createDelegate(this),
            params: {
                XmlType_id: this.getOption('XmlType_id'),
                Evn_id: this.getEvnId(),
                EvnXml_id: this.getEvnXmlId()
            }
        });
    },
    /**
     *
     */
    _onNotFound: function() {
        if (this.getUslugaComplexId()) {
            // пробуем создать документ на основе шаблона по умолчанию
            this.ownerWin.getLoadMask().show();
            Ext.Ajax.request({
                url: '/?c=XmlTemplateDefault&m=getXmlTemplateIdByUsluga',
                callback: function(options, success, response) {
                    this.ownerWin.getLoadMask().hide();
                    if ( !success || Ext.isEmpty(response.responseText) ) {
                        return false;
                    }
                    var response_obj = Ext.util.JSON.decode(response.responseText);
                    if ( !Ext.isArray(response_obj) || response_obj.length == 0 ) {
                        return false;
                    }
					if ( response_obj[0].XmlTemplate_id ) {
						this._createEmpty(response_obj[0].XmlTemplate_id);
						return true;
					}
					return false;
                }.createDelegate(this),
                params: {
                    UslugaComplex_id: this.getUslugaComplexId()
                }
            });
        }
    },
    /**
     * Создание документа, если есть идешник шаблона по умолчанию
     */
    createByXmlTemplateDefault: function() {
        var me = this;
        me.loadXmlTemplateDefault(function(id){
            if (id) {
                //me.onSelectXmlTemplate(id);
                me._createEmpty(id);
            }
        });
    },
    /**
     * Получение идешника шаблона по умолчанию
     */
    loadXmlTemplateDefault: function(callback) {
        if (sw.Promed.XmlTemplateCatDefault.isDisableDefaults(this.getUserMedStaffFact())) {
            callback(null);
            return true;
        }
        var me = this,
            params = {
                XmlType_id: this.getOption('XmlType_id'),
                EvnClass_id: this.getOption('EvnClass_id'),
                MedService_id: null,
                MedPersonal_id: null,
                UslugaComplex_id: null,
                MedStaffFact_id: this.getUserMedStaffFactId()
            },
            has_key = false;
        if (params.MedStaffFact_id > 0) {
            has_key = true
        } else if (params.MedService_id > 0 && params.MedPersonal_id > 0) {
            has_key = true
        } else if (params.UslugaComplex_id > 0) {
            has_key = true
        }
        if ( Ext.isEmpty(params.XmlType_id)
            || Ext.isEmpty(params.EvnClass_id)
            || false == has_key
        ) {
            callback(null);
        }
        me.ownerWin.getLoadMask().show();
        Ext.Ajax.request({
            url: '/?c=XmlTemplateDefault&m=getXmlTemplateId',
            callback: function(options, success, response) {
                me.ownerWin.getLoadMask().hide();
                if ( !success || Ext.isEmpty(response.responseText) ) {
                    callback(null);
                } else {
                    var response_obj = Ext.util.JSON.decode(response.responseText);
                    if ( !Ext.isArray(response_obj) || response_obj.length == 0 ) {
                        callback(null);
                    } else {
                        callback(response_obj[0].XmlTemplate_id || null);
                    }
                }
            },
            params: params
        });
        return true;
    },
    /**
     * Копирование протокола осмотра из предыдущего посещения в этом же талоне
     * @param {integer} Evn_rid Идентификатор талона
     */
    loadLastEvnProtocolData: function(Evn_rid) {
        // пока этот метод только для повторных стомат.посещений
        var me = this;
        if ( Ext.isEmpty(Evn_rid) || !me.getFilterEvnClassId().toString().inlist(['13']) ) {
            return;
        }
        me.ownerWin.getLoadMask().show();
        // Получаем EvnXml_id протокола последнего посещения в рамках указанного талона и создаем копию
        Ext.Ajax.request({
            url: '/?c=EvnXml&m=getLastEvnProtocolId',
            callback: function(options, success, response) {
                me.ownerWin.getLoadMask().hide();
                if ( !success || Ext.isEmpty(response.responseText) ) {
                    return false;
                }
                var response_obj = Ext.util.JSON.decode(response.responseText);
                if ( !Ext.isArray(response_obj) || response_obj.length == 0 ) {
                    return false;
                }
                if (me.getEvnId()) {
                    // если документ сохранен, то сразу копируем данные предыдущего
                    me._copy(response_obj[0].EvnXml_id);
                } else {
                    // отображаем кнопку "Копировать предыдущий осмотр",
                    // чтобы скопировать осмотр после сохранения
                    me.getToolbarItem('btnEvnXmlCopy').setVisible(true);
                    me.doEvnXmlCopy = function() {
                        me._copy(response_obj[0].EvnXml_id);
                    };
                }
                return true;
            },
            params: {
                Evn_rid: Evn_rid,
                EvnClass_id: me.getFilterEvnClassId()
            }
        });
    },
    /**
     * Инициализация
     */
    initComponent: function() {
    	var comp = this;
		this.baseParams = {
			Evn_id: null,
			UslugaComplex_id: null,
			Server_id: null,
			userMedStaffFact: {}
		};
        this.tbar = new Ext.Toolbar({
            items: [{
                text:langs('Выбрать шаблон'),
                tooltip:langs('Выбрать шаблон для документа'),
                name:'btnXmlTemplateSelect',
                hidden: !this.viewOptions.isWithSelectXmlTemplateBtn,
                iconCls: 'search16',
                xtype: 'button',
                handler: function() {
                    this.onBeforeCreate(this, 'doSelectXmlTemplate');
                }.createDelegate(this)
            }, {
                text:langs('Восстановить шаблон'),
                tooltip:langs('Восстановить шаблон документа'),
                name:'btnXmlTemplateRestore',
                hidden: !this.viewOptions.isWithRestoreXmlTemplateBtn,
                iconCls: 'template16',
                xtype: 'button',
                handler: function() {
                    this.doRestoreXmlTemplate();
                }.createDelegate(this)
            }, {
                text:langs('Очистить'),
                tooltip:langs('Очистить разделы документа'),
                name:'btnEvnXmlClear',
                hidden: !this.viewOptions.isWithEvnXmlClearBtn,
                iconCls: 'clear16',
                handler: function() {
                    this.doClearEvnXml();
                }.createDelegate(this)
            }, {
                text:langs('Печать'),
                tooltip:langs('Печать документа'),
                name:'btnEvnXmlPrint',
                hidden: !this.viewOptions.isWithPrintBtn,
                iconCls: 'print16',
                xtype: 'button',
                handler: function() {
                    // Если определен обработчик, то используется он
                    this.onClickPrintBtn(this);
                }.createDelegate(this)
            }, {
                text:langs('Копировать осмотр'),
                tooltip:langs('Копировать осмотр из предыдущего посещения'),
                name:'btnEvnXmlCopy',
                hidden: true,
                iconCls: 'copy16',
                xtype: 'button',
                handler: function() {
                    this.onBeforeCreate(this, 'doEvnXmlCopy');
                }.createDelegate(this)
            }, {
				html: '',
				listeners: {
					'render': function() {
						comp.swEMDPanel = Ext6.create('sw.frames.EMD.swEMDPanel', {
							renderTo: this.getEl(),
							width: 40,
							height: 22,
							padding: 0
						});

						comp.swEMDPanel.setReadOnly(true);
					}
				},
				xtype: 'label'
			}, {
				text: langs('BI-RADS'),
				tooltip: langs('BI-RADS'),
				name: 'btnEvnXmlBIRADS',
				hidden: !this.biRadsEnabled,
				iconCls: 'doc-uch16',
				xtype: 'button',
				handler: function() {
					var Person_id = comp.Person_id;
					var EvnUslugaPar_id = comp.EvnUslugaPar_id;
					sw.Promed.PersonOnkoProfile.openEditWindow('add', {
						MedPersonal_id: comp.ownerWin.MedPersonal_id,
						LpuSection_id: comp.ownerWin.LpuSection_id,
						Person_id: Person_id,
						EvnUslugaPar_id: EvnUslugaPar_id,
						ReportType: 'birads'
					}, function(c, BIRADSQuestion_id, data) {
						if (BIRADSQuestion_id && data.CategoryBIRADS_id && data.CategoryBIRADS_id >= 4) {
							sw.swMsg.show({
								buttons: {
									yes: {
										text: langs('Создать направление'),
										iconCls: 'ok16'
									},
									cancel: {
										text: langs('Отмена'),
										iconCls: 'close16'
									}
								},
								fn: function(buttonId, text, obj) {
									if ( buttonId == 'yes' ) {
										comp.consultFunction();
									}
								}.createDelegate(this),
								icon: Ext.MessageBox.QUESTION,
								msg: langs('В результате оценки получена категория BI-RADS больше 2. Необходимо отправить результаты исследования в ЦУК.'),
								title: langs('Внимание')
							});
						}
					});
				}
			},
				{
					text: langs('RECIST'),
					tooltip: langs('RECIST'),
					name: 'btnEvnXmlRECIST',
					hidden: false, // добавить условное скрытие кнопки
					iconCls: 'doc-uch16',
					xtype: 'button',
					handler: function() {
						var Person_id = comp.Person_id;
						var EvnUslugaPar_id = comp.EvnUslugaPar_id;
						sw.Promed.PersonOnkoProfile.openEditWindow('add', {
							MedPersonal_id: comp.ownerWin.MedPersonal_id,
							LpuSection_id: comp.ownerWin.LpuSection_id,
							Person_id: Person_id,
							EvnUslugaPar_id: EvnUslugaPar_id,
							ReportType: 'recist'
						});
					}
				}]
        });
        sw.Promed.EvnXmlPanel.superclass.initComponent.apply(this, arguments);
    }
});
Ext.reg('swevnxmlpanel', sw.Promed.EvnXmlPanel);
