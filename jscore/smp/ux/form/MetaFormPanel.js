/**
 * @class Ext4.ux.form.MetaFormPanel
 * @extends Ext4.form.FormPanel
 * 
 * Класс MetaFormPanel является FormPanel, конфигурируемый мета данными получаемыми с сервера
 */

// @todo Перенести MetaFormPanel в другой ns
Ext4.define('smp.ux.form.MetaFormPanel', _nav = {
	extend: 'Ext4.form.FormPanel',
	alias: 'widget.metaform',
	
	/**
	 * @cfg {Boolean/Object} autoInit
	 * Автоматически загружает данные после рендеринга формы.
	 * Если `true`, то Ext4.form.Panel.load получает параметр {meta: true}.
	 * Если указан объект, то Ext4.form.Panel.load принимает его в качестве параметра.
	 * По умолчанию true
	 */
	autoInit: true,
	
	/**
	 * @cfg {Boolean} focusFirstField True если необходимо установить фокус на первый элемент формы
	 * По умолчанию: true
	 */
	focusFirstField: true,

//  /**
//	 * @cfg {String} loadingText Локализация текста загрузки
//	 */
//	loadingText: 'Загружается...',

//	/**
//	 * @cfg {String} savingText Локализация сообщения сохранения
//	 */
//	savingText: 'Сохранение...',

	/**
     * @cfg {Object} data Data object bound to this form. If both {@link #metaData}
	 * and data are set at config time the data is bound and loaded on form render
	 * after metaData processing. Read-only at run-time.
     */

	/**
	 * @property hasMeta True если мета данные были обработаны и поля формы созданы, false в любом другом случае (read-only)
	 * @type Boolean
	 */
	hasMeta: false,

	/**
	 * @cfg {Array} ignoreFields Список имен полей, которые будут проигнорированы,
	 * при обработке metaData
	 * По умолчанию: undefined
	 */

	/**
	 * @cfg {Object} metaData Мета данные используемые для конфигурации формы.
	 * Если параметр задан, тогда он переедет {@link #autoInit} и запрос к серверу
	 * для получения мета данных выполнен не будет.
	 */
	
    /**
	 * @method afterMetaChange Выполняется после того, как мета данные были
	 * обработаны и созданы поля формы. При необходимости переопределите этот
	 * метод для своих нужд.
	 * По умолчанию: Ext4.emptyFn
	 */
	afterMetaChange: Ext4.emptyFn,
	
	/**
	 * @method afterUpdate Выполняется после того, как связанные данные были
	 * обновлены. При необходимости переопределите этот метод для своих нужд.
	 * По умолчанию: Ext4.emptyFn
	 * @param {Ext4.ux.form.MetaFormPanel} form Текущая форма
	 * @param {Object} data Связанные данные которые были обновлены
	 */
	afterUpdate: Ext4.emptyFn,
	
//	applyDefaultValues: function (o) {
//		if ('object' !== typeof o) {
//			return;
//		}
//		for (var name in o) {
//			if (o.hasOwnProperty(name)) {
//				var field = this.form.findField(name);
//				if (field) {
//					field.defaultValue = o[name];
//				}
//			}
//		}
//	},
	
	/**
	 * @private Изменяем порядок выполнения Ext4.form.action.Load::onSuccess чтобы
	 * позволить чтение данных из ответа сервера. Иначе данные будут загружены
	 * в форму до того, как выполнится onMetaChange вызываемый из события actioncomplete
	 */
	beforeAction: function(form, action){
		action.onSuccess = function(response){
			var result = this.processResponse(response),
				form = this.form;
		
			if (result === true || !result.success || !result.data) {
				this.failureType = Ext4.form.action.Action.LOAD_FAILURE;
				form.afterAction(this, false);
				log('Неудачаная загрузка формы. Надо добавить обработчик сюда.');
				return;
			}
			// Оригинал http://docs.sencha.com/extjs/4.2.2/source/Load.html#Ext-form-action-Load onSuccess
			// form.clearInvalid();
			// form.setValues(result.data);
			// form.afterAction(this, true);
			
			form.afterAction(this, true);
			form.clearInvalid();
			form.setValues(result.data);
		};
	},
	
	/**
	 * Backward compatibility function, calls {@link #bindData} function
	 * @param {Object} data A reference to an external data object. The idea is that form can display/change an external object
	 */
	bind: function (data) {
		this.bindData(data);
	},
	
	/**
	 * @param {Object} data A reference to an external data object. The idea is that form can display/change an external object
	 */
	bindData: function(data){
		this.data = data;
		this.form.setValues(this.data);
	},
	
	/**
	 * Возвращает значения полученные через вызов getValue() для каждого поля
	 * @return {Object} Список объектом с парами {имя: значение}
	 */
	getValues: function () {
		var values = {};
		// @todo Сделать рекурсивный запрос
		this.form.items.each(function (f) {
			values[f.name] = f.getValue();
		});
		return values;
	},
	
	initComponent: function(){
//		var config = {
//			items: this.items || {}
//		};
//		if ('function' === typeof this.getButton) {
//			config.buttons = this.getButtons();
//		}
//
//		// apply config
//		Ext4.apply(this, Ext4.apply(this.initialConfig, config));
//
		// Вызов родителя
		this.callParent();

		// Добавление событий
		this.addEvents(
			/**
			 * @event beforemetachange
			 * Вызывается перед тем как будут обработаны мета данные. Для отмены верните false.
			 * @param {Ext4.ux.form.MetaFormPanel} form Текущая форма
			 * @param {Object} metaData Обрабатываемые мета данные
			 */
			'beforemetachange',
			/**
			 * @event metachange
			 * Вызывается после того как были обработаны мета данные и созданы поля формы.
			 * @param {Ext4.ux.form.Metadata} form Текущая форма
			 * @param {Object} metaData Обработанные мета данные
			 */
			'metachange'
//			/**
//			 * @event beforebuttonclick
//			 * Fired before the button click is processed. Return false to cancel the event
//			 * @param {Ext4.ux.form.MetaFormPanel} form This form
//			 * @param {Ext4.Button} btn The button clicked
//			 */
//			'beforebuttonclick',
//			/**
//			 * @event buttonclick
//			 * Fired after the button click has been processed
//			 * @param {Ext4.ux.form.MetaFormPanel} form This form
//			 * @param {Ext4.Button} btn The button clicked
//			 */
//			'buttonclick'
		);

		// Установка обработчиков на базовую форму
		this.form.on({
			beforeaction: {scope: this, fn: this.beforeAction},
			actioncomplete: {scope: this, fn: function (form, action) {
				// Конфигурирование формы при получении metaData
				if ('load' === action.type && action.result.metaData) {
					this.onMetaChange(this, action.result.metaData);
				}
				// Обновить связанные данные при успешном сабмите формы
				else if ('submit' === action.type) {
					this.updateBoundData();
				}
			}}
		});

		// При вызове reset() значения возвращаются к последним загруженным или
		// установленным через setValues(), вместо значений которые были установлены
		// при первичной инициализации формы
		this.form.trackResetOnLoad = true;
	},
	
//	load: function (o) {
//		var options = this.getOptions(o);
//		if (this.loadingText) {
//			options.waitMsg = this.loadingText;
//		}
//		this.form.load(options);
//	},
	
//	/**
//	 * Called in the scope of this form when user clicks a button. Override it if you need a different
//	 * functionality of the button handlers.
//	 * <i>Note: Buttons created by MetaForm has name property that matches {@link #createButtons} names</i>
//	 * @param {Ext4.Button} btn The button clicked. 
//	 * @param {Ext4.EventObject} e Click event
//	 */
//	onButtonClick: function (btn, e) {
//		if (false === this.fireEvent('beforebuttonclick', this, btn)) {
//			return;
//		}
//		switch (btn.name) {
//			case 'meta':
//				this.load({params: {meta: true}});
//				break;
//
//			case 'load':
//				this.load({params: {meta: !this.hasMeta}});
//				break;
//
//			case 'defaults':
//				this.setDefaultValues();
//				break;
//
//			case 'reset':
//				this.form.reset();
//				break;
//
//			case 'save':
//				this.updateBoundData();
//				this.submit();
//				this.closeParentWindow();
//				break;
//
//			case 'ok':
//				this.updateBoundData();
//				this.closeParentWindow();
//				break;
//
//			case 'cancel':
//				this.closeParentWindow();
//				break;
//		}
//		this.fireEvent('buttonclick', this, btn);
//	},
	
	/**
	 * Событие onMetaChange
	 * Переопределите метод если потребуется доп. функционал
	 *
	 * @param {Ext4.FormPanel} this
	 * @param {Object} meta Metadata
	 * @return void
	 */
	onMetaChange: function (form, meta) {
		if (false === this.fireEvent('beforemetachange', this, meta)) {
			return;
		}
		this.removeAll();
		this.hasMeta = false;
		
		// Пропускаем создание колоночного макета
		var panel, tabIndex, ignore = {};;

		// Пропускаем создание колоночного макета
		this.add(new Ext4.Panel({
			border: false,
			defaults: (function(){
				return Ext4.apply({}, meta.formConfig || {}, {
					autoHeight: true,
					border: false,
					layout: 'form'
				});
			}).bind(this)
		}));

		panel = this.items.get(0);
		tabIndex = 1;

		if (Ext4.isArray(this.ignoreFields)) {
			Ext4.each(this.ignoreFields, function (f) {
				ignore[f] = true;
			});
		}
		
		// Обходим рекурсивно поля из метаданных
		eachMetaFields(meta.fields, panel);
		function eachMetaFields(fields, parent){
			Ext4.each(fields, function(item){
				if (true === ignore[item.name]) {
					return;
				}
	
				var items = [];
				if (item.xtype && !item.xtype.inlist(['radiogroup','checkboxgroup'])) {
					if (item.items) {
						items = item.items;
						delete(item.items);
					}
				}
	
				var config = Ext4.apply({}, item, {
					name: item.name || item.dataIndex,
					fieldLabel: item.fieldLabel || item.header
				});

				// @todo Проверить рабочесть
//				if (config.editor && config.editor.plugins) {
//					var plugins = config.editor.plugins;
//					delete(config.editor.plugins);
//					if (plugins instanceof Array) {
//						config.editor.plugins = [];
//						Ext4.each(plugins, function(plugin){
//							config.editor.plugins.push(Ext4.ComponentMgr.create(plugin));
//						});
//					}
//				}

//				// Обработка регулярных выражений
//				if (config.regex) {
//					config.regex = new RegExp(item.editor.regex);
//				}

				// to avoid checkbox misalignment
//				if ('checkbox' === config.xtype) {
//					Ext4.apply(config, {
//						boxLabel: ' ',
//						checked: item.defaultValue
//					});
//				}
//				if (meta.formConfig.msgTarget) {
//					config.msgTarget = meta.formConfig.msgTarget;
//				}

				config.tabIndex = tabIndex++;
				var component = parent.add(config);
				if (items.length) {
					if (component.add) {
						eachMetaFields(items, component);
					} else {
						log('У компонента отсутствует метод `add()` для добавления дочерних элементов.');
						log({component: component});
						log({items: items});
					}
				}
			}, this);
		};
		
		if (this.rendered && 'string' !== typeof this.layout) {
//			this.el.setVisible(false);
			this.doLayout();
//			this.el.setVisible(true);
		}
		
		this.hasMeta = true;
		if (this.data) {
			// Надо дать DOM немного времени на установку
			Ext4.defer(function(){
				this.form.setValues(this.data);
			}, 1, this);
		}
		this.afterMetaChange();
		this.fireEvent('metachange', this, meta);

		// Постараемся навести фокус на первое поле в форме
		// Этот мето нуждается в доработке:
		// - вынести по отдельно
		// - назначить через событие
		// - добавить проверку вкладок (какая активная в данный момент)
		// - добавить проверку видимости поля (visible, hidden)
		// - добавить проверку доступности поля (disabled, readonly)
		if (this.focusFirstField) {
			console.log(this.form);
//			var firstField = this.form.items.getAt(0);
//			if (firstField && firstField.focus) {
//				var delay = this.ownerCt && this.ownerCt.isXType('window') ? 1000 : 100;
//				firstField.focus(firstField.selectOnFocus, delay);
//			}
		}
	},
	
	onRender: function(){
		this.callParent(arguments);
		
		log('onRender called.');
		log({metaData:this.metaData});
		log({autoInit:this.autoInit});

//		this.form.waitMsgTarget = this.el;

		if (this.metaData) {
			this.onMetaChange(this, this.metaData);
			if (this.data) {
				this.bindData(this.data);
			}
		} else if (true === this.autoInit) {
			this.load({params: {meta: true}});
		} else if ('object' === typeof this.autoInit) {
			this.load(this.autoInit);
		}

	},
//	
//	/**
//	 * @private
//	 * Removes all items from both formpanel and basic form
//	 */
//	removeAll: function () {
//		// remove border from header
//		var hd = this.body.up('div.x-panel-bwrap').prev();
//		if (hd) {
//			hd.applyStyles({border: 'none'});
//		}
//		// remove form panel items
//		this.items.each(this.remove, this);
//
//		// remove basic form items
//		this.form.items.clear();
//	},
//	
//	reset: function () {
//		this.form.reset();
//	},
//	
//	
//	setDefaultValues: function () {
//		this.form.items.each(function (item) {
//			item.setValue(item.defaultValue);
//		});
//	},
//	
//	submit: function (o) {
//		var options = this.getOptions(o);
//		if (this.savingText) {
//			options.waitMsg = this.savingText;
//		}
//		this.form.submit(options);
//	},
	
	/**
	 * Обновление связанных данных
	 */
	updateBoundData: function () {
		if (this.data) {
			Ext4.apply(this.data, this.getValues());
			this.afterUpdate(this, this.data);
		}
	},
	
//	beforeDestroy: function () {
//		if (this.data) {
//			this.data = null;
//		}
//		Ext4.ux.form.MetaFormPanel.superclass.beforeDestroy.apply(this, arguments);
//	}

});

// Добавим xtype
//Ext4.reg('metaformpanel', Ext4.ux.form.MetaFormPanel);
