/**
 * sw4.BaseForm. Класс базовой формы
 *
 * @author  dimice
 *
 * @class sw4.BaseForm
 * @extends Ext6.window.Window
 */
Ext6.define('base.BaseForm', {
	extend: 'Ext6.window.Window',
	alias: 'widget.BaseForm',
	border: false,
	closeAction: 'hide', // просто скрывает форму , а не дестроит ее
	closeOnEsc: false,
	resizable: false,
	renderTo: main_center_panel.body.dom,
	onEsc: function() {
		// по Esc не должны закрываться формы.
		var me = this;
		if (this.closeOnEsc) {
			me.callParent(arguments);
		}
	},
	doConstrain: function() {
		var me = this;

		if (me.swMaximized) {
			me.setHeight(main_center_panel.body.getHeight());
		}

		me.callParent(arguments);
	},
	initComponent: function() {
		var me = this;

		if (me.modal) {
			// модальное рендерим на весь экран, т.к. должно перекрывать маской в том числе меню
			me.renderTo = Ext6.getBody();
			// но при этом отступ сверху должен быть, чтобы меню было видно
			me.constrainTo = main_center_panel.body.dom;
		}

		if (me.swMaximized) {
			me.setHeight(main_center_panel.body.getHeight());
		}
		
		me.ghostCreated=false;
		me.addCodeRefresh();
		me.addHelpButton();
		me.callParent(arguments);
	},
	show: function() {
		this.callParent(arguments);

		if (isDebug()) {
			console.group('Форма: %s', this.id);
			console.log('Метод: %s','show()');
			console.log('Аргументы: %o', arguments);
			//console.dir(this);
			console.groupEnd();
		}

		var me = this;
		me.args = arguments;
		if (!me.sprLoaded) {
			// надо прогрузить комбики
			var components = me.query('combobox');
			me.mask('Загрузка локальных справочников');
			me.loadDataLists(components, function () {
				me.unmask();
				me.onSprLoad(me.args);
			});
		} else {
			me.onSprLoad(me.args);
		}
	},
	sprLoaded: false,
	onSprLoad: function(args) {

	},
	setTitle: function (title, iconCls) {
		this.callParent(arguments);

		// обновить надпись на кнопке в таскбаре
		if (this.taskButton) {
			this.taskButton.setButtonText(title);
		}
	},
	addCodeRefresh: function() {
		if (IS_DEBUG)
		{
			if (!this.tools)
			{
				this.tools = [];
			}
			this.tools.push({
				type: 'refresh',
				margin: '0 15 0 5',
				hidden: (!IS_DEBUG), /*!isAdmin && */
				qtip: 'Обновить функционал формы',
				handler: function(event, toolEl, panel) {
					this.refreshCode();
				}.createDelegate(this)
			});
		}
	},
	addHelpButton: function() {
		if (!this.tools)
		{
			this.tools = [];
		}
		this.tools.push({
			type: 'help',
			margin: '0 15 0 5',
			qtip: 'Помощь',
			handler: function(event, toolEl, panel) {
				ShowHelp(this.title);
			}.createDelegate(this)
		});
	},
	listeners: {
		//корректировка стилей перетаскиваемого "призрака" окна (т.к. он создается при первом использовании)
		'drag': function() {
			if(!this.ghostCreated) {//пока нет решения получше
				this.ghostCreated = true;
				this.ghostPanel.addCls(this.cls);
				for(i=0; i<this.ghostPanel.tools.length-1; i++)
					this.ghostPanel.tools[i].setMargin(this.tools[i].margin);
			}
		},
		'activate': function(win) {
			if (!win.perfLogActivate) {
				win.perfLogActivate = true;
				sw4.addToPerfLog({
					window: win.objectClass,
					type: 'activate'
				});
			}
		},
		'afterrender': function(win) {
			sw4.addToPerfLog({
				window: win.objectClass,
				type: 'afterrender'
			});
		},
		'beforerender': function(win) {
			sw4.addToPerfLog({
				window: win.objectClass,
				type: 'beforerender'
			});
		}
	},
	// поиск поля на форме среди списка возможных названий
	findFieldByNames: function(form, fieldlist) {
		// идём по fieldlist и ищем на форме поля, если нашли то возвращаем ссылку на поле, иначе false
		for (var k in fieldlist) {
			var field = form.findField(fieldlist[k]);
			if (!Ext6.isEmpty(field)) {
				return field;
			}
		}
		return false;
	},
	// действие по кнопке "Считать с карты"
	readFromCard: function() {
		var win = this;
		// 1. пробуем считать с эл. полиса
		sw.Applets.AuthApi.getEPoliceData({callback: function(bdzData, person_data) {
			if (bdzData) {
				win.getDataFromBdz(bdzData, person_data);
			} else {
				// 2. пробуем считать с УЭК
				var successRead = false;
				if (sw.Applets.uec.checkPlugin()) {
					successRead = sw.Applets.uec.getUecData({callback: this.getDataFromUec.createDelegate(this), onErrorRead: function() {
						Ext6.Msg.alert('Ошибка', 'Не найден плагин для чтения данных картридера, либо не возможно прочитать данные с карты');
						return false;
					}});
				}
				// 3. если не считалось, то "Не найден плагин для чтения данных картридера либо не возможно прочитать данные с карты"
				if (!successRead) {
					Ext6.Msg.alert('Ошибка', 'Не найден плагин для чтения данных картридера, либо не возможно прочитать данные с карты');
					return false;
				}
			}
		}});
	},
	// относительно универсальная функция работы с полученными данными из уэк, чтобы не писать везде один и тот же код
	// во вяском случае её всегда можно перекрыть какой либо индивидуальной для формы
	getDataFromBdz: function(bdzData, person_data) {
		this.getDataFromUec(bdzData, person_data);
	},
	getDataFromUec: function(uecData, person_data) {
		log('uecData', uecData);
		log('person_data', person_data);
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
		if (!Ext6.isEmpty(surnamefield) && typeof surnamefield.setValue == 'function') {
			if (uecData.surName) {
				surnamefield.setValue(uecData.surName);
			} else if (uecData.Person_Surname) {
				surnamefield.setValue(uecData.Person_Surname);
			} else {
				surnamefield.setValue('');
			}
			count++;
		}
		if (!Ext6.isEmpty(firnamefield) && typeof firnamefield.setValue == 'function') {
			if (uecData.surName) {
				firnamefield.setValue(uecData.firName);
			} else if (uecData.Person_Firname) {
				firnamefield.setValue(uecData.Person_Firname);
			} else {
				firnamefield.setValue('');
			}
			count++;
		}
		if (!Ext6.isEmpty(secnamefield) && typeof secnamefield.setValue == 'function') {
			if (uecData.secName) {
				secnamefield.setValue(uecData.secName);
			} else if (uecData.Person_Secname) {
				secnamefield.setValue(uecData.Person_Secname);
			} else {
				secnamefield.setValue('');
			}
			count++;
		}
		if (!Ext6.isEmpty(birthdayfield) && typeof birthdayfield.setValue == 'function') {
			if (uecData.secName) {
				birthdayfield.setValue(uecData.birthDay);
			} else if (uecData.Person_Birthday) {
				birthdayfield.setValue(uecData.Person_Birthday);
			} else {
				birthdayfield.setValue('');
			}
			count++;
		}
		if (!Ext6.isEmpty(polisfield) && typeof polisfield.setValue == 'function') {
			if (person_data && person_data.resultType == 2) { // найден в БД без полиса
				polisfield.setValue('');
			} else {
				if (uecData.secName) {
					polisfield.setValue(uecData.polisNum);
				} else if (uecData.Polis_Num) {
					polisfield.setValue(uecData.Polis_Num);
				} else {
					polisfield.setValue('');
				}
			}
			count++;
		}

		var viewframe = this.MainViewFrame || this.GridPanel;
		if (viewframe && person_data && !Ext6.isEmpty(person_data.Person_id)) {
			viewframe.openUecPersonOnLoad = person_data.Person_id;
		}
		// выполняем поиск, обычно это this.doSearch()
		if (count > 0) {
			this.doSearch();
		}
	},
	refreshCode: function() {
		var win = this;
		var className = Ext6.getClassName(win);
		var pathWindow = Ext6.Loader.getPath(className);
		var sep = 1+pathWindow.indexOf('?') ? '&' : '?';
		Ext6.undefine(className);
		Ext6.Loader.loadScript({
			url: pathWindow + sep + Ext6.id(),                    // URL of script
			scope: this,                   // scope of callbacks
			onLoad: function(o) {
				win.hide();
				win.destroy();
			}
		});
	},
	errorInParams: function() {
		this.hide();
		Ext6.Msg.alert('Ошибка открытия формы', 'Ошибка открытия формы "' + this.objectClass + '".<br/>Отсутствуют необходимые параметры.');
	},
	/**
	 *  Загрузка данных справочников, используемых на форме (если ранее не загружены)
	 */
	loadDataLists: function(components, callback) {
		var me = this;

		loadDataLists(me, components, callback);
	}
});