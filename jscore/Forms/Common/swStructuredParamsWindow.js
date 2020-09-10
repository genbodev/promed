/**
* swStructuredParamsWindow - окно выбора структурированных параметров
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      common
* @access       public
* @copyright    Copyright (c) 2013 Swan Ltd.
* @author       Ivan Petukhov <ethereallich@gmail.com>
* @version      13.11.2013
* @comment      
*/

/*NO PARSE JSON*/

/**
 * Кнопка - ссылка
 */
Ext.TextButton = Ext.extend(Ext.Button, {
    template: new Ext.Template(
        '<table border="0" cellpadding="0" cellspacing="0" class="x-btn-wrap"><tbody><tr>',
        '<td class=""><a class="x-btn-text" href="javascript:void(0)" id="{1}">{0}</a></td>',
        "</tr></tbody></table>"),
	
    onRender:   function(ct, position){
        var btn, targs = [this.text || ' ', this.id];
        if(position){
            btn = this.template.insertBefore(position, targs, true);
        }else{
            btn = this.template.append(ct, targs, true);
        }
        var btnEl = btn.child("a:first");
        btnEl.on('focus', this.onFocus, this);
        btnEl.on('blur', this.onBlur, this);

        this.initButtonEl(btn, btnEl);
		
        Ext.ButtonToggleMgr.register(this);
    }
});

// Это окно имеет отдельный оконный менеджер и всегда будет сверху!
var onTop = new Ext.WindowGroup;
onTop.zseed = 10000;

sw.Promed.swStructuredParamsWindow = Ext.extend(sw.Promed.BaseForm, {
	manager: onTop,
	codeRefresh: true,
	objectName: 'swStructuredParamsWindow',
	objectSrc: '/jscore/Forms/Common/swStructuredParamsWindow.js',

	buttonAlign: 'left',
	closeAction: 'hide',
	layout: 'form',
	listeners: {
		'hide': function() {
			this.onHide();
		}
	},
	title: lang['strukturirovannyie_parametryi'],
	draggable: false,
	id: 'swStructuredParamsWindow',
	width: 500,
	height: 500,
	modal: false,
	plain: true,
	resizable: false,
	maximized: false,
	header: false,
	border: true,
	closable: true,
	autoScroll:true,

	onHide: Ext.emptyFn,
	onFinish: Ext.emptyFn,
	
	// Дерево структурированных параметров
	Tree: null,
	
	// Стэк для обхода элементов дерева
	TreeStack: [],
	
	// Элементы текущего уровня мастера
	CurrentLevel: [],
	
	// Стэк пути к текущей ветке, для возврата назад
	CurrentPath: [],
	
	// Результат работы мастера, набор выбранных листов
	result: [],
	
	// Введеные пользователем значения для каждого элемента
	enteredValues: [],
	
	// Ссылки на вызвавший редактор
	editor: null,
	editor_instance: null,
	
	initComponent: function() {
		var win = this;

		this.MainForm = new sw.Promed.FormPanel({
			id: 'StructuredParamMainForm',
			frame: true,
			border: false,
			region: 'center',
			hidden: false,
			layout: 'form',
			labelAlign: 'left',
			hideLabel: true,
			autoWidth: true,
			autoHeight: true,
			header: false,
			hideTitle: true
		});
		
		Ext.apply(this, {
			tbar: new Ext.Toolbar({
				autoHeight: true,
				buttons: [
					{
						xtype: 'button',
						handler: function() {
							// Чистим текущий уровень
							this.CurrentLevel = [];
							// удаляем его из текущего пути
							this.CurrentPath.pop(); 
							// и из массива результатов тоже удаляем
							this.result.pop();
							// и загружаем предыдыщий
							this.createForm(this.CurrentPath.pop());
						}.createDelegate(this),
						iconCls: 'arrow-previous16',
						tooltip: lang['nazad'],
						id: 'StructuredParamsPreviousButton'
					},
					{
						xtype: 'button',
						handler: function() {
							this.goToStart();
						}.createDelegate(this),
						iconCls: 'home16',
						tooltip: lang['vernutsya_k_nachalu'],
						id: 'StructuredParamsHomeButton'
					},
					{
						xtype: 'label',
						text: lang['filtr'],
						style: 'margin-left: 5px; margin-right: 20px; font-weight: bold'
					}, 
					{
						xtype: 'textfield',
						id: 'StructuredParamsFilter',
						hideLabel: true,
						width: 100,
						enableKeyEvents: true,
						listeners: {
							keydown: function(field, e) {
								if (e.getKey() == Ext.EventObject.ENTER ) {
									e.stopEvent();
									// Ищем по строке
									var filter = Ext.getCmp('StructuredParamsFilter').getValue();
									if (filter != '') {
										this.findBranches(this.Tree[0], filter);
									} else {
										// Если в фильтре пусто, то просто переходим к началу
										this.goToStart();
									}
								}
							}.createDelegate(this)
						}
					},
					{
						text: lang['poisk'],
						xtype: 'button',
						handler: function() {
							// Ищем по строке
							var filter = Ext.getCmp('StructuredParamsFilter').getValue();
							if (filter != '') {
								this.findBranches(this.Tree[0], filter);
							} else {
								// Если в фильтре пусто, то просто переходим к началу
								this.goToStart();
							}
						}.createDelegate(this)
					}
				]

			}),
			buttons: [
				new Ext.TextButton({
					handler: function() {
						// Сохраняем незавершенный путь в результат
						if (this.CurrentPath.length > 0)
							this.result.push(this.CurrentPath[this.CurrentPath.length-1]['id']);

						this.goNext();
					}.createDelegate(this),
					text: lang['propustit'],
					id: 'StructuredParamsSkipButton',
					disabled: true
				}),
				'-',
				{
					handler: function() {
						// Получаем идентификаторы выбранных параметров в списке
						var id = this.MainForm.getForm().getValues()['MainFormList'];
						// насильно превращаем их в массив, если не выбрано или всего один выбран
						var ids = [].concat( id );
						//debugger;
						// Обработка введенных значений
						var StructuredParamsList = Ext.getCmp('StructuredParamsList');
						if ( typeof(StructuredParamsList) == 'undefined' )
							return;
						//console.log(StructuredParamsList.items.itemAt(0));
						// Копируем глобальный массив сохраненных значений в локальную переменную, чтобы потом область действия не ломалась
						var enteredValues = this.enteredValues;
						// Запоминаем введенные элементы
						// Проходим по всем параметрам в списке
						for (var i = 0; i < StructuredParamsList.items.length; i++) {
							if (StructuredParamsList.items.itemAt(i).inputValue.inlist(ids)) {
								// Если параметр выбран, то будем обрабатывать
								this.enteredValues[StructuredParamsList.items.itemAt(i).inputValue] = [];
								// Берем все поля ввода в лейбле и запоминаем их значения в массив
								$("label[for='sp_"+StructuredParamsList.items.itemAt(i).inputValue+"']").find('.sp_ev').each(
									function() {
										enteredValues[StructuredParamsList.items.itemAt(i).inputValue].push($(this).val());
									}
								)
							}
						}
						
						//debugger;
						//Если на текущем уровне еще оставались элементы для обработки, то запоминаем их на будушее
						if (this.CurrentLevel.length != 0) {
							this.TreeStack.push(this.CurrentLevel);
							this.CurrentLevel = [];
						}
						// Переходим на новый уровень.
						this.TreeStack.push(ids);
						this.goNext();
					}.createDelegate(this),
					iconCls: 'arrow-next16',
					text: '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Далее&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',
					id: 'StructuredParamsNextButton',
					disabled: true
				}
			],
			items: [
				this.MainForm
			]
		});
		sw.Promed.swStructuredParamsWindow.superclass.initComponent.apply(this, arguments);
	},
	
	show: function() {
		sw.Promed.swStructuredParamsWindow.superclass.show.apply(this, arguments);
		
		// Попытка закрытия окна структурированных параметров при работе с другим функционалом
		window.onclick = function() { // проверяем вообще все нажатия в окне
			// Функция для скрытия окна выбора параметров в случае если мы ds,bhftv 
			// функция вызывается отложенно, чтобы в document.activeElement успел попасть новый активный элемент, куда мы переходим
			setTimeout(function() {
				if ( !(
					$.contains(Ext.get("swStructuredParamsWindow"), document.activeElement) || // выбранный элемент находится в окне выбора параметров
						$(document.activeElement).hasClass("nicEdit-main") || // выбранный элемент - другое поле ввода
						$(document.activeElement).is('body') // не выбран элемент, в этом случае в качестве выбранного элемента возвращается body. Нужно для того, чтобы окно не закрывалось при клике по пустому месту в том числе в окне выбора параметров
					)
					) {
					Ext.getCmp("swStructuredParamsWindow").hide();
					window.onclick = null;
				}
			}, 100);
		}

		var PersonEmkTree = Ext.get("PersonEmkTree");
		var PersonEmkForm = Ext.getCmp("PersonEmkForm");
		
		this.onFinish = arguments[0].onFinish || Ext.emptyFn;
		this.onHide = arguments[0].onHide ||  Ext.emptyFn;
		this.editor = arguments[0].editor || null;
		this.editor_instance = arguments[0].editor_instance || null;
		var branch = arguments[0].branch ||  '';

		this.alignTo(PersonEmkTree, "tl-tl");
		this.setSize(PersonEmkTree.getWidth(), PersonEmkTree.getHeight());
		this.doLayout();
		
		var loadMask = new Ext.LoadMask(
			this.getEl(),
			{msg: "Подождите, идет загрузка...", removeMask: true}
		);
		loadMask.show();
		Ext.Ajax.request({
            callback: function(options, success, response) {
				// Чистим все массивы
				this.CurrentPath = [];
				this.TreeStack = [];
				this.CurrentLevel = [];
				this.result = [];
				
                loadMask.hide();
				this.Tree = Ext.util.JSON.decode(response.responseText);

				if (this.Tree.length == 0){
					this.hide();
					this.editor.focus();
					return;
				}
				//debugger;
				// Если есть хоть какие-то потомки
				if (this.Tree[0].children) {
					this.MainForm.body.update('', true);
					this.createForm(this.Tree[0]);
				} else {
					this.MainForm.removeAll();
					this.MainForm.body.update(lang['nedostupen_vyibor_parametrov_v_etom_razdele'], true);
				}
				
				//this.editor.focus();
            }.createDelegate(this),
            params: {
            	'EvnXml_id': ((this.editor_instance && this.editor_instance.field.EvnXml_id) ? this.editor_instance.field.EvnXml_id : null),
            	'Evn_id': ((this.editor_instance && this.editor_instance.field.Evn_id) ? this.editor_instance.field.Evn_id : null),
                'branch': branch,
				'Person_id' : PersonEmkForm.Person_id,
				'Person_Birthdate' : Ext.util.Format.date(PersonEmkForm.Person_Birthday, 'd.m.Y'),
				'EvnClass_id': (this.editor_instance && this.editor_instance.field.EvnClass_id) ? this.editor_instance.field.EvnClass_id : null
            },
            url: C_STRUCTPARAMS_TREE
        });
		
		Ext.getCmp('StructuredParamsFilter').setValue('');
	},
	
	/**
	 * Идти на следующий шаг мастера
	 */
	goNext: function() {

		var branch = null;
		// Пока не нашли ветку и в стэке еще есть записи
		while ( !branch && (this.TreeStack.length != 0 || this.CurrentLevel.length != 0)) {
			if (this.CurrentLevel.length != 0) {
				var id = this.CurrentLevel.shift();
			} else {
				// Если на текущем уровне больше обрабатывать нечего, берем из стэка
				this.CurrentLevel = this.TreeStack.pop();
				id = this.CurrentLevel.shift();
			}
			
			var branch = this.getBranchbyId(this.Tree[0], id);
		}

		if (!branch) {
			//если веток больше нет, то это конец мастера
			this.finishMaster();
			return;
		}

		if (!branch['type'].inlist([3, 4])) {
			this.createForm(branch);
		} else {
			this.result.push(branch['id']);
			// если это конечная ветвь, то запоминаем ее значения и идем дальше по мастеру
			this.goNext();
		}
	},
	
	/**
	 * Закончить мастер и вернуть результат
	 */
	finishMaster: function() {
		var result = "";
		var prev_path = [];
		var path = [];

		// Проходим по всем результатам
		for(var i = 0; i < this.result.length; i++) {
			var str = this.getPathStrings(this.Tree[0], this.result[i], 1);
			if (!str) {
				continue;
			}
			path = str.split('@|@');
			var j = 0;
			while (path[j] == prev_path[j] && j <= path.length) {
				j++;
			}
			if (result.length > 0) {
				if (j == 0) {
					if (path.length > 1 ) {
						result += '. '; // пути длиной более 1 уровня всегда отделяются от предыдущего точкой
					} else {
						// Для конечных параметров на первом уровне следующая логика, если предыдущий параметр тоже конечный на первом уровне, то между ними ставится запятая, иначе точка
						if (prev_path.length == 1) {
							result += ', ';
						} else {
							result += '. ';
						}
						
					}
				} else {
					if (j == (path.length - 1) ) { // если это конечный уровень, то разделяем запятыми
						result += ', ';
					} else { // иначе разделяем точками с запятой
						result += '; ';
					}
				}
			}

			while (j < path.length) {
				var part = path[j];
				part = part.replace(/\s\s*$/, ''); // убираем пробелы с конца
				if (j == (path.length - 1)) {
					// если это конечный уровень, то дополнительно убираем точки и запятые в конце
					part = part.replace(/,$/, '').replace(/\.$/, '');
				}
				result += ' ' + part;
				j++;
			}

			prev_path = path;
		}
		if (result != '') {
			result += '. '; // и гордо ставим в конце точку c переносом строки (поправка - пробел вместо переноса строки https://redmine.swan.perm.ru/issues/28764)
		}
		// Возвращаем результат
		if (this.onFinish)
			this.onFinish(result);
		// и переходим на начало
		this.goToStart();
	},
	
	/**
	 * Переход к корню дерева
	 */
	goToStart: function() {
		// Чистим все массивы
		this.CurrentPath = [];
		this.TreeStack = [];
		this.CurrentLevel = [];
		this.result = [];

		if (this.Tree[0].children)
			this.createForm(this.Tree[0]);

		//this.editor.focus();
	},
	
	/**
	 * Поиск параметра в дереве по идентификатору
	 */
	getBranchbyId: function(root, id) {
		if (root['id'] == id) {
			return root;
		}
		if (root.children) {
			for(var i = 0; i < root.children.length; i++) {
				var res = this.getBranchbyId( root.children[i], id );
				if ( res ) return res;
			}
		}
	},
	
	/**
	 * Формирование строк результатов по выбранному листу дерева
	 */
	getPathStrings: function(root, id, level) {
		//debugger;
		if (root['id'] == id) {
			var name = root['name'];
			// Замена полей ввода на введенные параметры
			while (name.indexOf('[--]') != -1) {
				var name = name.replace('[--]', this.enteredValues[root['id']].shift());
			}
			if ( root['print'] == 1) { // тип печати с переносом параметр на новую строку
				name = '<br/>' + name;
			}
			
			return name;
		}
		
		if (root.children) {
			for(var i = 0; i < root.children.length; i++) {
				var res =  this.getPathStrings(root.children[i], id, level + 1);
				if (res) {
					if (level == 1) {
						// Первый уровень не выводим
						return res;
					} else if (root['print'] == 2) {
						// непечатаемые не выводим, но запоминаем что есть уровень
						return '@|@' + res;
					} else {
						var name = root['name'];
						// Замена полей ввода на введенные параметры
						while (name.indexOf('[--]') != -1) {
							var name = name.replace('[--]', this.enteredValues[root['id']].shift());
						}
						if ( root['print'] == 1) { // тип печати с переносом параметр на новую строку
							name = '<br/>' + name;
						}
						return name + '@|@' + res;
					}
					
				}
			}
		}
		
		return false;
	},
	
	/**
	 * Создает форму по элементам верхнего уровня в переданной ветви
	 */
	createForm: function(branch) {
		if ( !branch.children || branch.children.length == 0) {
			// внезапно не оказалось потомков у ветви, такое бывает при ограничениях по специальности, возрасту
			// тогда идем дальше, аналогично нажатию Пропустить
			// Перед этим сохраняем незавершенный путь в результат
			this.result.push(branch['id']);
			this.goNext();
			return;
		}
		
		this.CurrentPath.push( branch );
		
		if (branch['path'] == '') {
			if (!Ext.isEmpty(branch['name'])) {
				this.setTitle(branch['name']);
			}
			else {
				this.setTitle(lang['strukturirovannyie_parametryi']);
			}
		} else {
			this.setTitle(branch['path'] + ' >> ' + branch['name']);
		}

		var fset = new Ext.form.FieldSet({
			title: branch['name'],
			xtype: 'fieldset',
			style: 'padding: 4px',
			labelWidth: Ext.get("PersonEmkTree").getWidth() - 85,
			hidden: false,
			autoHeight: true,
			autoWidth: true,
			autoScroll:true,
			id: 'StructuredParamsList',
			region: 'center'
		}); 
		
		// Для множественного выбора добавляем пункт Выбрать всё в начало
		if (branch['type'] == 1) {
			var config = {
				'id' : 'sp_0',
				'fieldLabel' : "<span class='labelHover' style='font-weight: bold;'>Выбрать всё</span>",
				'name' : 'MainFormList',
				'inputValue' : 0,
				'labelSeparator' : '',
				listeners: {
					check: function (ctl, val) {
						var StructuredParamsList = Ext.getCmp('StructuredParamsList');
						// Проходим по всем пунктам
						for (var i = 0; i < StructuredParamsList.items.length; i++) {
							// если это не сама галочка Выбрать всё
							if (StructuredParamsList.items.itemAt(i) != ctl ) {
								// меняем значение на значение галочки Выбрать всё
								StructuredParamsList.items.itemAt(i).setValue(val);
							}
						}
					}.createDelegate(this)
				},
				'cls': 'labelHover',
				'labelStyle': 'height:auto;'
			};
			var field = new Ext.form.Checkbox(config);
			fset.add(field);
		}
		
		branch.children.forEach(
			function (element){
				//if (element['type'].inlist([3, 4]) && !element.children) return;
				var name = element['name'];
				while (name.indexOf('[--]') != -1) {
					// если есть поля для ввода
					var name = name.replace('[--]','<input class="sp_ev" type="text" style="width:50px; min-width: 50px; max-width: 300px;" onclick="Ext.getCmp(\''+'sp_' + element['id']+'\').toggleValue();"/>');
				}
				var config = {
					'id' : 'sp_' + element['id'],
					'fieldLabel' : "<span class='labelHover'>" + name + "</span>",
					'name' : 'MainFormList',
					'inputValue' : element['id'],
					'labelSeparator' : '',
					listeners: {
						check: function (ctl, val) {
							if (ctl.el.dom.type == 'checkbox') { // для чекбоксов проверяем, если ни один ны выбран, то блокируем кнопку Далее
								Ext.getCmp('StructuredParamsNextButton').disable();
								var StructuredParamsList = Ext.getCmp('StructuredParamsList');
								// Проходим по всем пунктам, кроме первого элемента, так как это будет Выбрать всё
								for (var i = 1; i < StructuredParamsList.items.length; i++) {
									// если это не сама галочка Выбрать всё
									if ( StructuredParamsList.items.itemAt(i). checked ) {
										Ext.getCmp('StructuredParamsNextButton').enable();
										break;
									}
								}
							} else {
								Ext.getCmp('StructuredParamsNextButton').enable();
							}
						}
					},
					'cls': 'labelHover',
					'labelStyle': 'height:auto;'
				};
				
				if (branch['type'] == 1) {
					var field = new Ext.form.Checkbox(config);
				}
				if (branch['type'] == 2) {
					var field = new Ext.form.Radio(config);
				}
				fset.add(field);
			}
		);
		// Удаляем предыдыщий список
		this.MainForm.removeAll();
		// И создаем новый
		this.MainForm.add(fset);
		this.MainForm.doLayout();
			
		$(".sp_ev").autosizeInput({ "space": 10 });
			
		// Отключаем кнопку Далее до выбора параметров
		Ext.getCmp('StructuredParamsNextButton').disable();
		// Если длина текущего пути равна одному, то значит мы в начале и возвращаться некуда, и нельзя пропустить
		if (this.CurrentPath.length == 1) {
			Ext.getCmp('StructuredParamsPreviousButton').disable();
			Ext.getCmp('StructuredParamsSkipButton').disable();
		} else {
			Ext.getCmp('StructuredParamsPreviousButton').enable();
			Ext.getCmp('StructuredParamsSkipButton').enable();
		}
	},
	
	/**
	 * Поиск веток параметров и вывод их в один список
	 */
	findBranches: function(root, branch_name) {
		/**
		 * Рекурсивный поиск по дереву и создание элементов результата
		 */
		function recursiveSearch(root, branch_name, fset) {
			if (root.children) {
				root.children.forEach(
					function (element){
						var name = element['name'];
						if (name.toUpperCase().indexOf(branch_name.toUpperCase()) != -1) {
							while (name.indexOf('[--]') != -1) {
								// если есть поля для ввода
								var name = name.replace('[--]','<input class="sp_ev'+'" type="text" style="width:50px; max-width: 300px;" onclick="Ext.getCmp(\''+'sp_' + element['id']+'\').toggleValue();"/>');
							}
							var config = {
								'id' : 'sp_' + element['id'],
								'fieldLabel' : "<span class='labelHover'>" + name + "<br/><span style = 'font-size: 9px; color: gray;'>" + element['path'] + "</span></span>",
								'name' : 'MainFormList',
								'inputValue' : element['id'],
								listeners: {
									check: function (ctl, val) {
										Ext.getCmp('StructuredParamsNextButton').enable();
									}
								},
								'cls': 'labelHover',
								'labelStyle': 'height:auto;'
							};
							var field = new Ext.form.Radio(config);
							fset.add(field);
						}
						recursiveSearch(element, branch_name, fset);
					}
				);
			}
		}
		
		// Чистим все массивы
		this.CurrentPath = [];
		this.TreeStack = [];
		this.CurrentLevel = [];
		this.result = [];
		
		this.MainForm.setTitle(lang['rezultatyi_poiska']);

		var fset = new Ext.form.FieldSet({
			title: lang['rezultatyi_poiska'],
			xtype: 'fieldset',
			style: 'padding: 4px',
			labelWidth: Ext.get("PersonEmkTree").getWidth() - 85,
			hidden: false,
			autoHeight: true,
			autoWidth: true,
			autoScroll:true,
			id: 'StructuredParamsList',
			region: 'center'
		}); 
		recursiveSearch(root, branch_name, fset);
		
		// Удаляем предыдыщий список
		this.MainForm.removeAll();
		// И создаем новый
		this.MainForm.add(fset);
		this.MainForm.doLayout();
			
		// Отключаем кнопку Далее до выбора параметров
		Ext.getCmp('StructuredParamsNextButton').disable();
		// Отключаем кнопки Назад и Пропустить
		Ext.getCmp('StructuredParamsPreviousButton').disable();
		Ext.getCmp('StructuredParamsSkipButton').disable();
	}
});