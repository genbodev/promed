/**
* swComponentLibFields - классы различных полей (не комбобоксов)
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      libs
* @access       public
* @copyright    Copyright (c) 2018 Swan Ltd.
* @author       Dmitry Vlasenko
* @version      02.04.2018
*/

// поле ввода периода дат
Ext6.define('swDateRangeField', {
	extend: 'Ext6.date.RangeField',
	alias: 'widget.swDateRangeField',
	initComponent: function() {
		var me = this;
		me.callParent(arguments);
	}
});


// поле ввода даты
Ext6.define('swDateField', {
	extend: 'Ext6.form.field.Date',
	alias: 'widget.swDateField',
	plugins: [],
	initComponent: function() {
		var me = this;
		me.plugins = me.plugins.slice();
		if(Ext6.isEmpty(me.plugins.find(function(plugin){ return !Ext6.isEmpty(plugin.rawMask); }))) //не более одной маски
		me.plugins.push(
			new Ext6.ux.InputTextMask('99.99.9999', false)
		);
		me.callParent(arguments);
	},
	format: 'd.m.Y',
	formatText: '',
	invalidText: 'Неправильная дата',
	listeners: {
		'focus': function(field, event, eOpts) {//курсор на первый символ "_"
			setTimeout(function() {
				var pos=0;
				var s=field.getValue();
				if(s && s.length) {
					pos=s.indexOf('_');
					if(pos<0) pos=s.length;
				}
				document.getElementById(field.getInputId()).selectionStart = pos;
				document.getElementById(field.getInputId()).selectionEnd = pos;
			}, 10);
		}.createDelegate(this)
	}
});

// поле ввода времени
Ext6.define('swTimeField', {
	extend: 'Ext6.form.field.Text',
	alias: 'widget.swTimeField',
	plugins: [],
	triggers: {
		currentTime: {
			cls: 'x6-form-time-trigger',
			handler: function() {
				if (this.disabled) { return; }

				this.setValue(new Date().format('H:i'));
			}
		}
	},
	inputMask: '99:99',
	regex: /([0,1][0-9]|2[0-3]):[0-5][0-9]/,
	regexText: 'Введите время в корректном формате, чч:мм'
});

Ext6.define('SimpleNumField', {
	extend: 'Ext6.form.field.Number',
	alias: 'widget.SimpleNumField',
	minValue: 0,
	hideTrigger: true,
	keyNavEnabled: false,
	mouseWheelEnabled: false,
	listeners: {
		change: function (c, v)
		{
			try {
				if (this.up('form') && this.up('form').getViewModel())
				{
					this.up('form').getViewModel().set(this.name, v);
				}

				//typeof this.up === 'function' ? (this.up('form') ? this.up('form').isValid() : null) : null;
			} catch (e) {
				if (IS_DEBUG)
				{
					console.log(e)
				}
			}


			return;
		}
	}
});


Ext6.define('SimpleTextField', {
	extend: 'Ext6.form.field.Text',
	alias: 'widget.SimpleTextField',
	listeners: {
		change: function (c, v)
		{
			try {
				if (this.up('form') && this.up('form').getViewModel())
				{
					this.up('form').getViewModel().set(this.name, v);
				}

				//typeof this.up === 'function' ? (this.up('form') ? this.up('form').isValid() : null) : null;
			} catch (e) {
				if (IS_DEBUG)
				{
					console.log(e)
				}
			}


			return;
		}
	}
});


// простая кнопка с серым текстом и нужным размером шрифта, по умолчанию ищет функцию обработчик на окне к которому прикреплена. По умолчанию делает отмену и функцию hide()
Ext6.define('SimpleButton', {
	extend: 'Ext6.button.Button',
	alias: 'widget.SimpleButton',

	handlerFnName: 'close',
	userCls: 'buttonPoupup buttonCancel',
	text: langs('Отмена'),
	handler: function() // хендлер можно переопределить
	{
		var win = this.up('window');

		if (win && typeof win[this.handlerFnName] === 'function')
		{
			return win[this.handlerFnName]();
		}

		return false;
	}
});

// кнопка подтверждения синего цвета, по умолчанию вызывает метод doSave с окна к которому прикреплена
Ext6.define('SubmitButton', {
	extend: 'SimpleButton',
	alias: 'widget.SubmitButton',

	handlerFnName: 'doSave',
	cls: 'buttonAccept',
	text: langs('Сохранить') // Применить
});

// Поле для множественной загрузки файлов. В Ext 6 classic отсутствует возможность задать свойство multiple, как в modern
Ext6.define('MultipleFileField', {
	extend: 'Ext.form.field.File',
	alias: 'widget.MultipleFileField',
	//name: 'userfile[]' - на забыть указать массив после имени чтобы пришли все файлы
	setMultiple: function ()
	{
		// мульти-загрузку обеспечивает аттрибут multiple у файлового поля ввода, вида <input type="file" multiple name="userfile[]">
		this.fileInputEl.set({multiple: 'multiple'});
		return;
	},
	// каждый раз после загрузки аттрибут multiple слетает и надо ставить его заново
	beforeReset: function ()
	{
		this.setMultiple();
		return;
	},
	listeners: {
		// добавить атрибут после загрузки
		afterrender: function () {
			this.setMultiple();
			return;
		}
	}
});

Ext6.define('sw.form.QueryField', {
	extend: 'Ext6.form.TextField',
	alias: 'widget.swqueryfield',
	cls: 'sw-trigger-field',
	emptyText: 'Поиск',
	enableKeyEvents: true,
	delay: 400,

	query: function(force){},

	delayQuery: function(delay, force) {
		var me = this;
		if (me.delayQueryId) {
			clearTimeout(me.delayQueryId);
			me.delayQueryId = null;
		}
		if (force) {
			me.query(force);
		} else {
			me.delayQueryId = setTimeout(function () {
				me.query();
				me.delaySearchId = null;
			}, delay || me.delay);
		}
	},

	refreshTrigger: function(value) {
		var me = this;
		var isEmpty = Ext6.isEmpty(value || me.getValue());
		me.triggers.clear.setVisible(!isEmpty);
		me.triggers.search.setVisible(isEmpty);
	},

	triggers: {
		search: {
			cls: 'x6-form-search-trigger',
			handler: function() {
				var me = this;
				me.query(true);
			}
		},
		clear: {
			cls: 'sw-clear-trigger',
			hidden: true,
			handler: function() {
				var me = this;
				me.setValue('');
				me.refreshTrigger();
				me.query();
			}
		}
	},

	listeners: {
		afterrender: function(me) {
			me.refreshTrigger();
		},
		keyup: function(me, e) {
			me.refreshTrigger(e.target.value);
			me.delayQuery(me.delay, e.getKey() == e.ENTER);
		}
	}
});

Ext6.define('sw.button.Scale', {
	extend: 'Ext6.button.Button',
	alias: 'widget.swscalebutton',

	menuCls: '',
	value: 100,
	list: [
		25, 50, 70, 100, 125, 150, 200, 400, 800, 1000
	],

	onScale: Ext6.emptyFn,

	setScale: function(value) {
		var me = this;
		me.value = value || me.value;

		var text = me.value+'%';
		me.setText(text);
		me.menu.items.each(function(item) {
			if (item.text == text) {
				item.addCls('active');
			} else {
				item.removeCls('active');
			}
		});
	},

	initComponent: function() {
		var me = this;

		var items = me.list.map(function(value) {
			return {
				text: value+'%',
				handler: function() {
					me.setScale(value);
					me.onScale(value);
				}
			};
		});

		var cls = [
			'menuWithoutIcons',
			'sw-scale-btn-menu',
			me.menuCls
		];

		var menuCfg = {cls: cls, items: items};

		if (me.minMenuWidth) {
			menuCfg.minWidth = me.minMenuWidth;
		}
		if (me.maxMenuWidth) {
			menuCfg.maxWidth = me.maxMenuWidth;
		}

		me.menu = Ext6.create('Ext6.menu.Menu', menuCfg);

		me.callParent(arguments);
	}
});

Ext6.define('sw.table.Selector', {
	extend: 'Ext6.Component',
	alias: 'widget.swtableselector',

	baseCls: 'table-selector',

	size: {x: 10, y: 10},
	data: {rows: []},

	renderTpl: [
		'<div id="{id}-grid" class="{baseCls}-grid">',
		'<tpl for="rows">{.}</tpl>',
		'</div>',
		'<div id="{id}-label" class="{baseCls}-label">',
		'{label}',
		'</div>'
	],
	labelTpl: [
		'{x} x {y}'
	],
	rowTpl: [
		'<div id="{id}-row-{y}" class="{rowCls}"><tpl for="cells">{.}</tpl></div>'
	],
	cellTpl: [
		'<span id="{id}-cell-{y}{x}" class="{cellCls}" row-index="{y}" column-index="{x}">',
		'<span id="{id}-cell-{y}{x}-inner" class="{cellCls}-inner"></span>',
		'</span>'
	],

	select: function(size){},

	getCell: function(coords) {
		var me = this;
		var x = coords.x;
		var y = coords.y;
		return me.el.getById(me.id+'-cell-'+y+x);
	},

	overCells: function(coords) {
		var me = this;

		var x, y;
		for (y = 0; y < me.size.y; y++) {
			for (x = 0; x < me.size.x; x++) {
				if (x <= coords.x && y <= coords.y) {
					me.getCell({x: x, y: y}).addCls('over');
				} else {
					me.getCell({x: x, y: y}).removeCls('over');
				}
			}
		}

		me.el.getById(me.id+'-label').setText(me.labelTpl.apply({
			x: Number(coords.x) + 1,
			y: Number(coords.y) + 1
		}));
	},

	onGridMouseLeave: function(e) {
		var me = this;
		me.overCells({x: -1, y: -1});
	},

	onCellMouseOver: function(e) {
		var me = this;
		var cell = Ext6.get(e.currentTarget);

		me.overCells({
			x: cell.getAttribute('column-index'),
			y: cell.getAttribute('row-index')
		});
	},

	onCellClick: function(e) {
		var me = this;
		var cell = Ext6.get(e.currentTarget);

		me.select({
			x: Number(cell.getAttribute('column-index')) + 1,
			y: Number(cell.getAttribute('row-index')) + 1
		});
	},

	onRender: function() {
		var me = this;
		me.callParent(arguments);

		me.el.on({
			mouseleave: me.onGridMouseLeave,
			delegate: '.'+me.gridCls,
			scope: me
		});

		me.el.on({
			click: me.onCellClick,
			mouseover: me.onCellMouseOver,
			delegate: '.'+me.cellCls,
			scope: me
		});
	},

	initRenderData: function() {
		var me = this;
		var data = me.callParent(arguments);

		data.label = me.labelTpl.apply({x: 0, y: 0});

		data.rows = [];
		for (y = 0; y < me.size.y; y++) {
			var cells = [];
			for (x = 0; x < me.size.x; x++) {
				cells.push(me.cellTpl.apply({id: data.id, cellCls: me.cellCls, x: x, y: y}));
			}
			data.rows.push(me.rowTpl.apply({id: data.id, rowCls: me.rowCls, y: y, cells: cells}));
		}

		return data;
	},

	initComponent: function () {
		var me = this;
		var x, y;

		me.gridCls = me.baseCls+'-grid';
		me.rowCls = me.baseCls+'-row';
		me.cellCls = me.baseCls+'-cell';

		me.labelTpl = new Ext6.XTemplate(me.labelTpl);
		me.rowTpl = new Ext6.XTemplate(me.rowTpl);
		me.cellTpl = new Ext6.XTemplate(me.cellTpl);

		me.callParent(arguments);
	}
});

Ext6.define('sw.table.Menu', {
	extend: 'Ext6.menu.Menu',
	alias: 'widget.swtablemenu',
	layout: 'fit',
	ariaRole: 'dialog',
	border: false,
	plain: true,
	scrollable: false,
	style: 'padding: 0;',

	select: function(size){},

	initComponent: function() {
		var me = this;

		me.items = Ext6.create('sw.table.Selector', {
			select: function(size) {
				me.hide();
				me.select(size);
			}
		});

		me.callParent(arguments);
	}
});

Ext6.define('sw.table.Button', {
	extend: 'Ext6.button.Button',
	alias: 'widget.swtablebutton',

	select: function(size){},

	initComponent: function() {
		var me = this;

		me.menu = Ext6.create('sw.table.Menu', {
			select: me.select.bind(me)
		});

		me.callParent(arguments);
	}
});

Ext6.define("Ext.previewDateOrTime", {
	extend: "Ext6.panel.Panel",
	alias: "widget.previewDateOrTime",
	callback: Ext6.emptyFn,
	headerBorders: false,
	border: false,
	cls: 'previewDateOrTime',
	readOnly: false,
	setReadOnly: function(isReadOnly) {
		var me = this;
		me.readOnly = isReadOnly;
		this.previewDateOrTime.items.items.forEach(function(field){
			field.setDisabled(isReadOnly);
		});
	},
	initComponent: function () {
		var me = this;

		me.previewDateOrTime = Ext6.create('Ext6.panel.Panel', {
			margin: 0,
			padding: '10 0 0 0',
			items: [
				{
					xtype: 'datefield',
					itemId: 'fieldDate',
					margin: 0,
					value: new Date(),
					format: 'd.m.Y',
					width: 115,
					cls: 'previewDate',
					disabled: me.readOnly,
					style: {
						display: 'inline-table'
					}
				},
				{
					xtype: 'timefield',
					itemId: 'fieldTime',
					margin: '0 0 0 7',
					format: 'H:i',
					cls: 'previewTime',
					value: new Date(),
					width: 80,
					disabled: me.readOnly,
					style: {
						display: 'inline-table'
					},
					onTriggerClick: function() {
						this.setValue(new Date());
					}
				}
			]
		});


		Ext6.apply(this, {
			border: false,
			items: [
				this.previewDateOrTime
			]
		});
		this.callParent();
	},
	getValue:function () {
		var date = this.down('datefield').getValue(),
			time = this.down('timefield').getValue(),
			datetime;

		if (!date && !time) {
			return null;
		}

		if(date == null) datetime = Ext6.Date.format(new Date(), 'Y-m-d');
		else datetime =  Ext6.Date.format(date, 'Y-m-d');

		if(time == null) datetime += ' '+Ext6.Date.format(new Date(), 'H:i');
		else datetime += ' '+ Ext6.Date.format(time, 'H:i');

		return datetime;
	},
	setValue: function(date) {
		var date_field = this.down('datefield'),
			time_field = this.down('timefield');

		date_field.setValue(date);
		time_field.setValue(date);
	}
});

Ext6.define("Ext.questionPanel", {
	extend: "Ext6.panel.Panel",
	alias: "widget.questionPanel",
	callback: Ext6.emptyFn,
	headerBorders: false,
	cls: 'title-question',
	initComponent: function () {
		var me = this;

		this.menuQuestion = Ext6.create('Ext6.menu.Menu', {
			autoSize: true,
			resizable: false,
			width: 250,
			shadow: false,
			cls: 'menuQuestion',
			style: {
				borderRadius: '2px;',
				border: '1px solid #C5C5C5;',
				boxShadow: '0 3px 6px rgba(0,0,0, .16) !important',
			},
			items: [
				{
					xtype: 'button',
					cls: 'type-quest menuQuestion-btn',
					text: 'Тип вопроса',
					menuAlign: 'b',
					menu: {
						listeners: {
							click: function(m,i) {
								me.fireEvent('typechange', i);
								me.menuQuestion.hide()
							}
						},
						items: [
							{
								iconCls: 'icon-variable radio-variable',
								text: 'Один вариант',
								cls: 'variable',
								value: 9,
								type: 'radioButton'
							},
							{
								iconCls: 'icon-variable check-variable',
								text: 'Несколько вариантов',
								cls: 'variable',
								value: 10,
								type: 'checkbox'
							},
							{
								iconCls: 'icon-variable text-variable',
								text: 'Текст',
								cls: 'variable',
								value: 2,
								type: 'textfield'
							},
							{
								iconCls:'icon-variable date-variable',
								text: 'Дата',
								cls: 'variable',
								value: 11,
								type:'datetime'
							}
						]
					}
				}, {
					xtype:'button',
					cls: 'add-answer menuQuestion-btn',
					iconCls: 'add-answer-icon',
					tooltip: 'Добавить',
					menuAlign: 'b',
					menu: [
						{
							iconCls: 'icon-variable add-answers-icon',
							text: 'Ответ',
							disabled: !!me.disableAddAnswerButton,
							cls: 'variable',
							handler:function(){
								me.fireEvent('addanswer');
								me.menuQuestion.hide()
							}
						},
						{
							iconCls: 'icon-variable other-answer-icon',
							text: '"Другое"',
							disabled: true,
							//disabled: !!me.disableAddAnswerButton,
							cls: 'variable'
						}
					]
				},  {
					xtype:'button',
					cls: 'delete-quest menuQuestion-btn',
					iconCls: 'delete-quest-icon',
					tooltip: 'Удалить',
					handler:function(){
						me.fireEvent('deletequest');
						me.menuQuestion.hide()
					}
				},
				Ext6.create("Ext6.button.Button", {
					cls: 'copy-quest menuQuestion-btn',
					iconCls: 'copy-quest-icon',
					disabled: true,
					tooltip: 'Дублировать'
				}),
				Ext6.create("Ext6.button.Button", {
					cls: 'up-quest menuQuestion-btn',
					iconCls: 'up-quest-icon',
					disabled: true,
					tooltip: 'Переместить вверх'
				}),
				Ext6.create("Ext6.button.Button", {
					cls: 'down-quest menuQuestion-btn',
					iconCls: 'down-quest-icon',
					disabled: true,
					tooltip: 'Переместить вниз'
				})
			]
		});

		this.itemsQuestion = Ext6.create('Ext6.panel.Panel',{
			cls: 'questionPanels',
			paddingTop: 6,
			items:[
				{
					xtype: 'textareafield',
					grow: true,
					growAppend: '\n',
					name: 'message',
					cls: 'textareaCustomConstructor',
					value: this.question,
					anchor: '100%',
					growMin: 20,
					width: this.maxWidthUpdate,
					enableKeyEvents: true,
					listeners: {
						click: {
							element: 'el',
							fn: function(trash, el, item, index, event) {
								me.menuQuestion.show();
								me.menuQuestion.alignTo(el, 'br-bc', [-15, 32]);

							}
						},
						keyup: function (cmp) {
							me.fireEvent('datachange', cmp.getValue());
							if (me.menuQuestion.isVisible()) {
								me.menuQuestion.hide();
							}
						}
					}
				}
			]
		});

		Ext6.apply(me, {
			border: false,
			items: [
				me.itemsQuestion
			]
		});
		this.callParent();
	}
});

Ext6.define("Ext.answerPanel", {
	extend: "Ext6.panel.Panel",
	alias: "widget.answerPanel",
	callback: Ext6.emptyFn,
	headerBorders: false,
	cls: 'title-answer',
	initComponent: function () {
		var me = this;

		this.addCls(me.typeQuestion);

		this.menuAnswer = Ext6.create('Ext6.menu.Menu', {
			autoSize: true,
			resizable: false,
			width: 146,
			shadow: false,
			cls: 'menuAnswer',
			style: {
				borderRadius: '2px;',
				border: '1px solid #C5C5C5;',
				boxShadow: '0 3px 6px rgba(0,0,0, .16) !important'
			},
			items: [
				{
					xtype:'button',
					cls: 'add-answer menuQuestion-btn',
					iconCls: 'add-answer-icon',
					tooltip: 'Добавить',
					disabled: true,
					menuAlign: 'b',
					menu: [
						{
							iconCls: 'icon-variable add-subquery-menuAnswer-icon',
							text: 'Подвопрос',
							cls: 'variable'
						},
						{
							iconCls: 'icon-variable add-answers-icon',
							text: 'Ответ',
							cls: 'variable'
						},
						{
							iconCls: 'icon-variable other-answer-icon',
							text: '"Другое"',
							cls: 'variable'
						}
					]
				}, {
					xtype:'button',
					disabled: true,
					cls: 'copy-quest menuQuestion-btn',
					iconCls: 'copy-quest-icon',
					tooltip: 'Дублировать'
				}, {
					xtype:'button',
					cls: 'delete-quest menuQuestion-btn',
					iconCls: 'delete-quest-icon',
					tooltip: 'Удалить',
					handler:function(){
						me.fireEvent('deleteanswer');
						me.menuAnswer.hide()
					}
				},{
					xtype:'button',
					disabled: true,
					cls: 'up-quest menuQuestion-btn',
					iconCls: 'up-quest-icon',
					tooltip: 'Переместить вверх'
				}, {
					xtype:'button',
					disabled: true,
					cls: 'down-quest menuQuestion-btn',
					iconCls: 'down-quest-icon',
					tooltip: 'Переместить вниз'
				}
			]
		});

		switch (this.typeQuestion) {
			case('checkbox'):
				this.itemsAnswer = Ext6.create('Ext6.panel.Panel', {
					margin: 0,
					height: 26,
					cls: 'answerPanels',
					items: [
						{
							xtype: 'checkbox',
							margin: 0,
							cls: 'checkbox-custom',
							style:{
								display: 'inline-block'
							}
						},
						{
							xtype: 'textfield',
							name: 'firstName',
							cls: 'no-border-custom checkbox',
							style: {
								display: 'inline-table'
							},
							margin: '0 0 0 5',
							value: this.answer,
							enableKeyEvents: true,
							grow: true,
							growMin: 50,
							listeners: {
								render: function () {
									this.growMax = me.updateLayoutCustom.getWidth()-170;
								},
								click: {
									element: 'el',
									fn: function(trash, el) {
										me.menuAnswer.show();
										me.menuAnswer.alignTo(el, 'br-br', [35, 35]);
									}
								},
								keyup: function (cmp) {
									me.fireEvent('datachange', cmp.getValue());
									if (me.menuAnswer.isVisible()) {
										me.menuAnswer.hide();
									}
								}
							}
						}
					]
				});
				break;
			case('radioButton'):
				this.itemsAnswer = Ext6.create('Ext6.panel.Panel', {
					margin: 0,
					height: 26,
					width: me.maxWidthUpdate,
					padding: '1 1 1 1',
					cls: 'answerPanels',
					items: [
						{
							xtype: 'fieldcontainer',
							fieldLabel: '',
							defaultType: 'radiofield',
							defaults: {
								flex: 1
							},
							cls: 'custom-radio',
							margin: 0,
							style:{
								display: 'inline-block'
							},
							layout: 'hbox',
							items: [
								{
									name: this.InputValue,
									inputValue: this.InputValue
								}
							]
						},
						{
							xtype: 'textfield',
							name: 'firstName',
							cls: 'no-border-custom checkbox',
							style: {
								display: 'inline-table'
							},
							margin: '-8 0 0 5',
							value: this.answer,
							enableKeyEvents: true,
							grow: true,
							growMin: 50,
							listeners: {
								render: function () {
									this.growMax = me.updateLayoutCustom.getWidth()-138;
								},
								click: {
									element: 'el',
									fn: function(trash, el) {
										me.menuAnswer.show();
										me.menuAnswer.alignTo(el, 'br-br', [35, 35]);
									}
								},
								keyup: function (cmp) {
									me.fireEvent('datachange', cmp.getValue());
									if (me.menuAnswer.isVisible()) {
										me.menuAnswer.hide();
									}
								}
							}
						}
					]
				});
				break;
		}
		Ext6.apply(this, {
			border: false,
			items: [
				this.itemsAnswer
			]
		});
		this.callParent();
	}
});

Ext6.define('sw.button.ExtendedButton', {
	extend: 'Ext6.button.Button',
	alias: 'widget.extendedbutton',

	descr: null,

	renderTpl: '<span id="{id}-btnWrap" data-ref="btnWrap" role="presentation" unselectable="on" style="{btnWrapStyle}" ' +
	'class="{btnWrapCls} {btnWrapCls}-{ui} {splitCls}{childElCls}">' + '<span id="{id}-btnEl" data-ref="btnEl" role="presentation" unselectable="on" style="{btnElStyle}" ' +
	'class="{btnCls} {btnCls}-{ui} {textCls} {noTextCls} {hasIconCls} ' + '{iconAlignCls} {textAlignCls} {btnElAutoHeightCls}{childElCls}">' +
	'<tpl if="iconBeforeText">{[values.$comp.renderIcon(values)]}</tpl>' +
	'<span id="{id}-btnInnerEl" data-ref="btnInnerEl" unselectable="on" class="{innerCls} {innerCls}-{ui}{childElCls}">' +
	'<span id="{id}-btnText" class="{baseCls}-text">{text}</span> ' +
	'<tpl if="descr"><span id="{id}-btnDescr" class="{baseCls}-descr">{descr}</span></tpl>' +
	'</span><br/>' +
	'<tpl if="!iconBeforeText">{[values.$comp.renderIcon(values)]}</tpl>' + '</span>' +
	'</span>' + '{[values.$comp.getAfterMarkup ? values.$comp.getAfterMarkup(values) : ""]}' + // if "closable" (tab) add a close element icon
	'<tpl if="closable">' + '<span id="{id}-closeEl" data-ref="closeEl" class="{baseCls}-close-btn">' + '<tpl if="closeText">' + ' {closeText}' + '</tpl>' + '</span>' + '</tpl>' + // Split buttons have additional tab stop for the arrow element
	'<tpl if="split">' + '<span id="{id}-arrowEl" class="{arrowElCls}" data-ref="arrowEl" ' + 'role="button" hidefocus="on" unselectable="on"' + '<tpl if="tabIndex != null"> tabindex="{tabIndex}"</tpl>' + '<tpl foreach="arrowElAttributes"> {$}="{.}"</tpl>' + ' style="{arrowElStyle}"' + '>{arrowElText}</span>' + '</tpl>',

	getTemplateArgs: function() {
		return Ext6.apply(this.callParent(arguments), {descr: this.descr});
	}
});