/**
 * sw.Promed.BaseForm. Класс базовой формы
 *
 * @author  Марков Андрей
 *
 * @class sw.Promed.BaseForm
 * @extends Ext.Window
 */
//sw.Promed.BaseForm = function(config)
//{
//	Ext.apply(this, config);
//	sw.Promed.BaseForm.superclass.constructor.call(this);
//};

Ext.define('base.swPromedBaseForm',{
	extend: 'Ext.Window',
	requires: ['Ux.InputTextMask'],
	alias: 'widget.swPromedBaseForm',
	title         : '',
	layout        : 'form',
	closable      : true,
	collapsible   : false,
	width         : 700,
	height        : 500,
	border        : false,
	params        : null,
	//collapsed     : true,
	//draggable     : true,
	//plain         : false, 
	//titleCollapse : true, // сворачивание вверх
	modal         : false, // модальное окно
	//maximizable   : true,
	closeAction   :'hide', // просто скрывает форму , а не дестроит ее
	showMode      : 'window', // тип открываемого окна, либо вкладка (tab), либо окно (window)
	codeRefresh   : false,
	// в принципе можно вообще собирать разную статастическую инфу statInfo: {showCount: } бла-бла 
	showCount: 0,
	// наименование панели формы 
	// можно указать formName, если getForm() требуется брать с конкретной FormPanel
	formName: null,
	roles: null,
	checkRole: function(role) {
		if (this.roles && this.roles[role]) {
			return this.roles[role];
		} else {
			return false;
		}
	},
	formPanels: [], // массив доступных панелей, если панель не одна
	// список наименований полей комбобоксов, для которых надо подгружать справочники для текущей формы 
	// если не указать, то определится автоматически при инициализации формы 
	lists: [],
	editFields: [],
	// TO-DO: вот баттоны вообще не так надо сделать
	buttons       :
		[
			{
			text      : BTN_FRMSAVE,
			tabIndex  : -1,
			tooltip   : 'Сохранить данные',
			iconCls   : 'save16',
			type      : 'submit',
			disabled  : false,
			handler  : function()
				{
				// Код сохранения
				this.ownerCt.hide();
				}
			},
			{
			text      : 'Отменить',
			tabIndex  : -1,
			tooltip   : 'Отменить сохранение',
			iconCls   : 'cancel16',
			handler   : function()
				{
				this.ownerCt.hide();
				}
			},
			{
			text	: BTN_FRMHELP,
			tabIndex  : -1,
			tooltip   : BTN_FRMHELP_TIP,
			iconCls   : 'help16',
			handler   : function()
				{
				ShowHelp(this.ownerCt.title);
				}
			}
		],
	buttonAlign : "left",
	listeners: {
		beforehide: function() {
			if (sw.Applets)
			if (this.useUecReader) {
				sw.Applets.uec.stopUecReader();
			}
			
			if (typeof this.onBeforeHide == 'function') {
				this.onBeforeHide();
			}
		},
		hide: function() {
			// TO-DO : код при закрытии формы
		}
	},
	// поиск поля на форме среди списка возможных названий
	findFieldByNames: function(form, fieldlist) {
		// идём по fieldlist и ищем на форме поля, если нашли то возвращаем ссылку на поле, иначе false
		for (var k in fieldlist) {
			var field = form.findField(fieldlist[k]);
			if (!Ext.isEmpty(field)) {
				return field;
			}
		}
		return false;
	},
	// относительно универсальная функция работы с полученными данными из уэк, чтобы не писать везде один и тот же код
	// во вяском случае её всегда можно перекрыть какой либо индивидуальной для формы
	getDataFromUec: function(uecData, person_data) {
		log(uecData);
		log(person_data);
		// для армов: ищем и заполняем фильтры, выполняем поиск
		// обычно это this.FilterPanel, для остальных вводим параметр..
		if (this.FilterPanel) {
			var filterpanel = this.FilterPanel;
		} else {
			
		}
		// если фильтры свернуты - разворачиваем
		if (filterpanel.fieldSet) {
			// для BaseWorkPlaceFilterPanel именно так:
			if (!filterpanel.fieldSet.expanded) {
				filterpanel.fieldSet.expand();
			}
		} else {
			// для остальных ищем свёрнутую панельку с фильтрами
			if (filterpanel.items && filterpanel.items.items && filterpanel.items.items[0]) {
				var fieldSet = filterpanel.items.items[0];
				if (typeof fieldSet.expand == 'function' && !fieldSet.expanded) {
					fieldSet.expand();
				}
			}
		}
		var filterform = filterpanel.getForm();
		// ищем на форме нужные поля для заполнения в фильтрах
		var surnamefield = this.findFieldByNames(filterform, ['Search_SurName', 'Person_Surname', 'Person_SurName']); // фамилия
		var firnamefield = this.findFieldByNames(filterform, ['Search_FirName', 'Person_Firname', 'Person_FirName']); // имя
		var secnamefield = this.findFieldByNames(filterform, ['Search_SecName', 'Person_Secname', 'Person_SecName']); // отчество
		var birthdayfield = this.findFieldByNames(filterform, ['Search_BirthDay', 'Person_Birthday', 'Person_BirthDay']); // дата рождения
		
		var polisfield = null;
		if (getRegionNick().inlist(['ufa'])) {
			polisfield = this.findFieldByNames(filterform, ['Polis_Num']); // единый номер полиса
		} else {
			polisfield = this.findFieldByNames(filterform, ['Person_Code']); // единый номер полиса
		}
		 
		// если нашли хоть одно поле, то заполняем
		var count = 0;
		if (!Ext.isEmpty(surnamefield) && typeof surnamefield.setValue == 'function') {
			surnamefield.setValue(uecData.surName);
			count++;
		}
		if (!Ext.isEmpty(firnamefield) && typeof firnamefield.setValue == 'function') {
			firnamefield.setValue(uecData.firName);
			count++;
		}
		if (!Ext.isEmpty(secnamefield) && typeof secnamefield.setValue == 'function') {
			secnamefield.setValue(uecData.secName);
			count++;
		}
		if (!Ext.isEmpty(birthdayfield) && typeof birthdayfield.setValue == 'function') {
			birthdayfield.setValue(uecData.birthDay);
			count++;
		}
		if (!Ext.isEmpty(polisfield) && typeof polisfield.setValue == 'function') {
			polisfield.setValue(uecData.polisNum);
			count++;
		}
		
		var viewframe = this.MainViewFrame || this.GridPanel;
		if (viewframe && person_data && !Ext.isEmpty(person_data.Person_id)) {
			viewframe.openUecPersonOnLoad = person_data.Person_id;
		}
		// выполняем поиск, обычно это this.doSearch()
		if (count > 0) {
			this.doSearch();
		}
	},
	useUecReader: false, // включение/выключение работы с уэк на форме
	show: function() {
		if (arguments[0] && arguments[0].action && arguments[0].action == 'edit' && !this.checkRole('edit'))  // если запрещено редактирование => открываем в режиме просмотра
		{
			arguments[0].action = 'view';
		}
		/* TODO: Этот функционал надо будет доработать
		if ( this.params == null )
			this.params = new Object();
		this.params = arguments[0];
		*/
		/** Если на сервере включен режим дебага, то стоит выводить параметры которые приходят в форму в лог консоли.
		 * 
		 */
		// При первом открытии загружаем данные справочников
		this.callParent(arguments);
		/*
		this.showCount++;
		if (this.showCount == 1) { // Если первый запуск
			//this.hide(); // и не показываем форму
			this.loadDataLists(arguments[0]); // то загружаем справочники
			return false;
		}*/
		
		var wnd = this;
		var wndparams = arguments[0] || null;
		
		if (isDebug()) {
			console.group('Форма: %s', this.id);
			console.log('метод: %s','show()');
			console.log('Аргументы: %o', arguments);
			console.log('Права: %o', this.roles);
			//console.dir(this);
			console.groupEnd();
		}
		
		if (sw.Applets)
		if (this.useUecReader) {
			sw.Applets.uec.startUecReader({callback: this.getDataFromUec.bind(this)});
		}
		// логирование действий пользователя (открытие) - чисто тестовый вариант для себя
		// эта штука подвиснет если передать объект в форму
		/*if (isDebug()) {
			var args = Ext.apply({}, arguments[0]);
			if (args) {
				// todo: здесь добавляем объекты, которые не надо логировать
				if (args.owner) { args.owner = true; }
				if (args.swPersonSearchWindow) { args.swPersonSearchWindow = true; } // форма jscore\Forms\Common\swAssistantWorkPlaceWindow.js
		 		if (args.swAssistantWorkPlaceWindow) { args.swAssistantWorkPlaceWindow = true; } // форма jscore\Forms\Common\swAssistantWorkPlaceWindow.js
			}
			var log = 'swGetWnd("'+this.objectName+'").show('+ Ext.util.JSON.encode(args)+')';
			saveLog(log);
		}*/
		//for (var i = 0, len = this.editFields.length; i < len; i++) {
		/*
		for (var key in this.editFields) {
			if (typeof this.editFields[key].markInvalid == 'function' ) {
				this.editFields[key].markInvalid();
			}
			warn(this.editFields[key]);
		}
		*/
	},
	/** Загрузка данных справочников, используемых на форме (если ранее не загружены)
	 * arguments - параметры пришедшие на эту форму
	 * form - форма, которая включает в себя компоненты-комбобоксы (getForm())
	 */
	loadStores: function(cmplist, callback) {
		loadStores(cmplist, callback);
	},
	/** Загрузка данных справочников, используемых на форме (если ранее не загружены)
	 * arguments - параметры пришедшие на эту форму
	 * noclose - признак, того что данные взяты с панели и закрывать окно не нужно
	 */
	loadDataLists: function(args, lists, noclose) {
		log({'Метод':'loadDataLists', 'lists':lists, 'this.lists':this.lists});
		var w = this, cmplist = [];
		function show(w, args, noclose) {
			if (!noclose) {
				if (w.isVisible()==false) { // todo: Почему то форма отображается, а isVisible - false
					w.show(args);
				} else {
					w.toFront(); // просто выводим это окно
				}
			}
		}
		function load(t, lists) {
			// функция загрузки справочников для нужных элементов.

			if ( lists.length == 0 ) {
				if (cmplist.length>0) { // Если список компонентов не пустой
					if (!noclose) {
						w.hide();
					}
					sw.Promed.mask.show();
					// Загрузка сторе по списку компонентов
					w.loadStores(cmplist, function() {
						show(w, args, noclose);
						sw.Promed.mask.hide();
						return true;
					}, w);
				} else { // Если нечего загружать, то проверяем не скрыто ли окно
					show(w, args, noclose);
					return true;
				}
			}
			else {
				var params = new Object();
				var field = lists.shift();
				// Если у компонента в принципе есть сторе и оно пустое
				if (field && field.getStore && (field.getStore().getCount()==0) && (field.getStore().mode == 'local')) {
					cmplist.push(field); // Загоняем компоненты в массив
					load(this, lists, noclose);
					// Делаем попытку загрузить данные комбобокса 
					/*form.findField(sprName).getStore().load({
						callback: function() {
							load(this, lists);
						}.bind(t),
						params: (	.findField(sprName).params)?form.findField(sprName).params:null
					});*/
				} else {
					load(t, lists, noclose);
				}
			}
			return true;
		}
		// Если наименования справочников не переданы в функцию, то используем данные текущего объекта
		if (!lists) {
			// todo: здесь по идее надо сделать копирование
			lists = w.lists;
		}
		// И если наименования справочников хоть какие-то есть, то тогда пробуем загрузить
		if (lists && (lists.length > 0)) {
			load(w, lists, noclose);
		} else {
			show(w, args, noclose);
		}
	},
	getLoadMask: function(MSG) {
		if (MSG) {
			delete(this.loadMask);
		}
		if (!this.loadMask) {
			this.loadMask = new Ext.LoadMask(Ext.get(this.id), { msg: MSG });
		}
		return this.loadMask;
	},
	getConstructorName: function()
	{
		for (var p in window)
			if (window[p] === this.constructor)
				return p;
		return null;
	},
	addCodeRefresh: function()
	{
		/**
		 * Для того, чтобы программисту не перезагружать каждый раз форму над которой он работает,
		 * возможно использовать обновление функционала только текущей формы.
		 * Данный функционал доступен только если переменная IS_DEBUG = 1 и форма отнаследована от базовой (BaseForm)
		 *
		 * Поскольку при загрузке и выполнении файла также может произойти ошибка,
		 * то реализована возможность загрузить последний запрошенный JS-файл из меню программы ('Система' - 'Обновить [имя объекта]').
		 *
		 * Примечание: Если форма после обновления отображается не в той кодировке, то поможет
		 * строка (AddCharset windows-1251 .js) добавленная в .htaccess
		 *
		 * Lkz
		 * sw.Promed.swUslugaComplexTreeWindow = Ext.extend(sw.Promed.BaseForm,
		 *   ...
		 *   codeRefresh: true,
		 *   objectName: 'swUslugaComplexTreeWindow',
		 *   objectSrc: '/jscore/Forms/Admin/swUslugaComplexTreeWindow.js',
		 *
		 */

		if (IS_DEBUG) /*!isAdmin && */
		{
			if (!this.tools)
			{
				this.tools = [];
			}
			this.tools.push({
				id:'refresh',
				hidden: (!IS_DEBUG), /*!isAdmin && */
				qtip: 'Обновить функционал формы',
				handler: function(event, toolEl, panel) {
					// поскольку при загрузке и выполнении файла может произойти ошибка, то реализуем возможность вновь загрузить последний JS-файл из меню программы
					sw.codeInfo.lastObjectName = this.objectName;
					sw.codeInfo.lastObjectClass = this.objectClass;
					if (sw.Promed.Actions.loadLastObjectCode)
					{
						sw.Promed.Actions.loadLastObjectCode.setHidden(false);
						sw.Promed.Actions.loadLastObjectCode.setText('Обновить '+this.objectName+' ...');
					}
					// Удаляем полностью объект из DOM, функционал которого хотим обновить
					this.hide();
					this.destroy();
					window[this.objectName] = null;
					delete sw.Promed[this.objectName];

					// Отправляем запрос на получение измененного JS-файла
					// loadJsCode({objectName: this.objectName, objectClass: this.objectClass});

					/*
					Ext.Ajax.request(
					{
						url: this.objectSrc,
						callback: function(options, success, response)
						{
							if (success)
							{
								// Читаем и пересоздаем (добавляем в DOM)
								eval(response.responseText);
								//window[this.objectName] =  new sw.Promed[this.objectName]();
							}
						}.bind(this)
					});

					// Зачистка объекта (исключаем из DOM)
					if (window[this.objectName])
					{
						window[this.objectName] = null;
						delete sw.Promed[this.objectName];
					}
					*/

				}.bind(this)
			});
		}
	},
	/** Ищем и находим первую FormPanel (если this.formPanel еще не определено)
	 *
	 */
	getFormPanel: function() {
		// поля нужно брать рекурсивно (здесь мы должны выбрать только поля для ввода)
		// приходит массив объектов
		var result = [];
		function getRecursivePanel(o) {
			if (o && o.length && o.length>0) {
				for (var i = 0, len = o.length; i < len; i++) {
					//log(o[i].id, i, o[i], o[i].form);
					if (o[i] && o[i].form) {
						// Если объект, является формой, то считаем что все нашли
						if (o[i].getForm) {
							result.push(o[i]);
						} 
					/*} else if (o[i].xtype=='fieldset' || o[i].xtype=='panel' || o[i].xtype=='tabpanel' ) {
						getRecursivePanel(o[i]);
					}*/
					} else if (o[i].items && o[i].items.items ) {
						getRecursivePanel(o[i].items.items);
					}
				};
			}
			//log(result);
			return result;
		}
		
		// Если this.formPanel определен, то ничего не ищем, возвращаем уже определенный FormPanel
		if (this.formPanel) {
			return this.formPanel;
		}
		return getRecursivePanel(this.items.items);
	},
	/** Ищем и находим список всех комбобоксов задействованных на форме, которые надо загрузить 
	 * (возможно для тех комбобоксов, которые загружать не надо следуют предусмотреть какой-то признак)
	 * Данный метод пока используется только в специфике в форме движения, т.к. панели добавляются динамически и при первом открытии формы нет возможности определить все комбобоксы
	 */
	getComboLists: function(o) {
		// поля нужно брать рекурсивно (здесь мы должны выбрать только поля для ввода)
		// приходит массив объектов 
		function getRecursiveCombo(o)
		{
			var fields = [];
			var arr = [];
			// если массив полей, то по самому массиву, если объект - то по items
			if ((typeof o == 'object') && o.items && o.items.items) {
				o = o.items.items;
			}
			if (o && o.length && o.length>0) {
				for (var i = 0, len = o.length; i < len; i++) {
					//log(i,':',o[i],'->', o[i].xtype);
					// только сами филды
					if (o[i])
						if ((o[i].xtype && (o[i].xtype=='fieldset' || o[i].xtype=='panel' || o[i].xtype=='tabpanel')) || (o[i].layout/* && (o[i].layout=='form')*/)) { // TO-DO: Скорее всего здесь надо будет поправить
							// уровень ниже
							fields = fields.concat(getRecursiveCombo(o[i]));
						}
						else if (o[i].hiddenName && o[i].store && o[i].mode  && o[i].mode == 'local') { // по store определяем, что это комбо
							/*
							// собираем массив полей (если с формы то по hiddenname, с панели - по id)
							if (type == 'form') {
								fields.push(o[i].hiddenName);
							} else {
								fields.push(o[i].id);
							}
							*/
							fields.push(o[i]);
						}
				};
			}
			return fields;
		}
		var fields = getRecursiveCombo(o);
		//log('fields', fields);
		return fields;
	},
	/** Ищем и находим список всех комбобоксов задействованных на форме, которые надо загрузить, а также список всех полей формы (используется в this.enableEdit())
	 * Функция в отличие от предыдущей getComboLists ничего не возвращает, после выполнения в this.lists - список комбо, в this.editFields - список полей для редактирования
	 * Вызывается только при первом открытии формы.
	 */
	getFieldsLists: function(o, options) {
		// поля нужно брать рекурсивно (здесь мы должны выбрать только поля для ввода)
		// приходит массив объектов 
		var form = this;
		
		function getRecursiveFields(o, options)
		{
			var comboFields = [];
			var editFields = [];
			
			var arr = [];
			// если массив полей, то по самому массиву, если объект - то по items
			if ((typeof o == 'object') && o.items && o.items.items) {
				o = o.items.items;
			}
			if (o && o.length && o.length>0) {
				for (var i = 0, len = o.length; i < len; i++) {
					//warn(i,':',o[i],'->', o[i].xtype);
					// только сами филды
					if (o[i])
						if ((o[i].xtype && (o[i].xtype=='fieldset' || o[i].xtype=='panel' || o[i].xtype=='tabpanel')) || (o[i].layout/* && (o[i].layout=='form')*/)) { // TO-DO: Скорее всего здесь надо будет поправить
							// уровень ниже
							getRecursiveFields(o[i],options);
						}
						// todo: Условие на (o[i].hiddenName || o[i].valueField) скорее всего уже лишнее, поскольку сейчас передается сам компонент и это имя не нужно
						// вырезал пока: (o[i].hiddenName || o[i].valueField) &&
						else if (o[i].store && o[i].mode  && o[i].mode == 'local') { // по store определяем, что это комбо
							comboFields.push(o[i]);
							editFields.push(o[i]);
						} else if (typeof o[i].getTopToolbar != 'function') { // гриды дизаблить не надо.
							editFields.push(o[i]);
						}
				};
			}
			
			if (options.needConstructComboLists) {
				if (comboFields.length>0) {
					form.lists = form.lists.concat(comboFields);
					if (!form.formPanel) { // в качестве панели выбираем первую "не пустую" (с комбиками) формпанель
						form.formPanel = form.formPanels[i];
					}
				}
			}
			
			if (options.needConstructEditFields) {
				if (editFields.length>0) {
					form.editFields = form.editFields.concat(editFields);
				}
			}
		};		
		
		getRecursiveFields(o, options);
	},
	enableEdit: function(enable) {
		if (!this.checkRole('edit')) { // если для формы не разрешено "редактирование", значит и поля,кнопки должны быть задисаблены
			enable = false;
		}
		
		// все редактируемые поля на форме
		for (var k in this.editFields) {
			if (typeof this.editFields[k] == 'object' && typeof this.editFields[k].enable == 'function') {
				if (enable) {
					if (!this.editFields[k].initialConfig.disabled) {
						this.editFields[k].enable();
					}
				} else {
					this.editFields[k].disable();
				}
			}
		}
		
		// все кнопки, кроме закрыть, помощь, найти, поиск, обновить, фильтр, сброс
		for (var k in this.buttons) {
			if (typeof this.buttons[k] == 'object' && typeof this.buttons[k].show == 'function') {
				if (enable) {
					if (!this.buttons[k].initialConfig.hidden) {
						this.buttons[k].show();
					}
				} else if (
					!(this.buttons[k].iconCls && this.buttons[k].iconCls.inlist(['print16','cancel16','help16','search16','digital-sign16'])) // кнопки которые не надо скрывать для режима "Просмотр"
				) {
					this.buttons[k].hide();
				}
			}
		}
	},
	initComponent: function() {
		this.addCodeRefresh();
		/*
		function CheckRequest(request,callback) {
			if(request.auth) {
				if(request.auth == 1) {
					Ext.Msg.alert('Ошибка', 'У вас нет прав для выполнения данной операции!');
					callback(false);
				}
				if(request.auth == 2) {
					Ext.Msg.alert('Ошибка', 'Время сессии закончилось. Авторизируйтесь заново, пожауйста!');
					window.location.href='/';
					callback(false);
				}
			} else {
				callback(true);
			}
		}*/
		this.formPanels = [];
		this.callParent(arguments);
//		sw.Promed.BaseForm.superclass.initComponent.apply(this, arguments);
		// Определяем панель формы
		if (!this.formPanel) {
			if (this.formName && this.findById(this.formName)) {
				this.formPanel = this.findById(this.formName);
			} else {
				this.formPanels = this.getFormPanel();
			}
		}
		// строим список комбиков
		var options = [];
		options.needConstructComboLists = (this.lists.length == 0);
		options.needConstructEditFields = (this.editFields.length == 0);
		
		if  ( options.needConstructComboLists || options.needConstructEditFields ) { // Если список еще не построен
			// Если выбор не по одной определенной модели
			// И если панелей несколько, то надо будет выбирать по всем
			if (!this.formName && (this.formPanels.length>0)) {
				for (var i = 0, len = this.formPanels.length; i < len; i++) {
					this.getFieldsLists(this.formPanels[i], options);
				}
			} else {
				this.getFieldsLists(this.formPanel, options);
			}
		}

		log({'formPanel':this.formPanel, 'formPanels':this.formPanels});
		//log({'formPanels':this.formPanels});
		if (this.formPanel && this.lists.length>0) {
			log({'lists':this.lists});
		}
	}
})