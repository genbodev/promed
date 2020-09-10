/**
*  Переопределения и добавления новых функций в базовые классы.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      libs
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage@swan.perm.ru)
* @version      09.07.2009
*/

/**
 * Оверрайд меню
 */
Ext6.override(Ext6.menu.Menu, {
	showSeparator: false // чтобы не было разделителей, по дизайну они не нужны
});

/**
 * Оверрайд сплиттера
 */
Ext6.override(Ext6.resizer.Splitter, {
	size: 10 // по дизайну сплиттер больше стандартного ExtJS
});

/**
 * Оверрайд таб панели
 */
Ext6.override(Ext6.tab.Panel, {
	beforeSetActiveTab: function() {
		return true;
	},
	setActiveTab: function(card) {
		if (this.beforeSetActiveTab(card)) {
			return this.callOverridden(arguments);
		}

		return null;
	}
});

/**
 * Оверрайд грида
 */
Ext6.override(Ext6.grid.Panel, {
	// показать/скрыть столбец в гриде
	setColumnHidden: function(dataIndex, hidden) {
		var cm = this.getColumns();
		var index = this.findColumnIndex(cm, dataIndex);
		if (index >= 0) {
			cm[index].setHidden(hidden);
		}
		return null;
	},
	findColumnIndex: function(cm, dataIndex) {
		for (var i = 0, len = cm.length; i < len; i++) {
			if (cm[i].dataIndex == dataIndex) {
				return i;
			}
		}
		return -1;
	}
});

/**
 * Оверрайд пункта меню
 */
Ext6.override(Ext6.menu.Item, {
	showSeparator: false, // чтобы не было разделителей, по дизайну они не нужны
	doHideMenu: function() {
		this.removeCls('childActive');
		this.callOverridden(arguments);
	},
	doExpandMenu: function(clickEvent) {
		this.addCls('childActive');
		this.callOverridden(arguments);
	}
});

/**
 * Оверрайд компонентов, чтобы присвоить всем test_id
 */
Ext6.override(Ext6.Component, {
	onRender: function() {
		this.callOverridden(arguments);
		if (TEST_ID_ENABLED && this.xtype) {
			var xtype = getXTypeForTestId(this, 1);
			if (xtype === false) {
				return;
			}
			var parentTestId = '';
			if (this.el.up) {
				var parentTest = this.el.up('[test_id]');
				if (parentTest) {
					parentTestId = parentTest.getAttribute('test_id') + '_';
				}
			}
			var name = '';
			if (this.fieldLabel && typeof this.fieldLabel == 'string' && this.fieldLabel.length > 0) {
				name = swTranslite(this.fieldLabel);
			} else if (this.text && typeof this.text == 'string' && this.text.length > 0) {
				name = swTranslite(this.text);
			} else if (this.title && typeof this.title == 'string' && this.title.length > 0) {
				name = swTranslite(this.title);
			} else if (this.tooltip && typeof this.tooltip == 'string' && this.tooltip.length > 0) {
				name = swTranslite(this.tooltip);
			} else if (this.name && typeof this.name == 'string' && this.name.length > 0) {
				name = swTranslite(this.name);
			} else if (this.id) {
				name = this.id;
			}

			var test_id = parentTestId + xtype;
			if (!name.match(/\-[0-9]+$/)) { // исключаем динамические идентификаторы
				test_id = test_id + '_' + name;
			}
			test_id = test_id.replace(/<\/?[^>]+>/gi, '');
			test_id = test_id.replace(/\./gi, '');
			this.el.dom.setAttribute('test_id', test_id);
		}
	}
});

Ext6.override(Ext6.data.request.Ajax, {
	start: function(data) {
		var me = this;
		var activeWin = getActiveWin();
		if (activeWin && activeWin.objectClass) {
			me.perfWindow = activeWin.objectClass;
		} else {
			me.perfWindow = null;
		}

		me.perfTime = Date.now().valueOf();

		return me.callOverridden(arguments);
	},
	onComplete: function (xdrResult) {
		var me = this;

		if (me.options.url != '/?c=PerfLog&m=savePerfLog') {
			sw4.addToPerfLog({
				window: me.perfWindow,
				url: me.options.url,
				params: me.options.params,
				time: me.perfTime,
				self: Date.now().valueOf() - me.perfTime,
				type: 'request'
			});
		}

		var response = me.createResponse(me.xhr);
		if ( response.responseText && response.responseText.length > 0 && response.responseText.indexOf('/*NO PARSE JSON*/') == -1 && response.responseText.indexOf('sw.Promed.') == -1) {
			try {
				resultJson = Ext6.JSON.decode(response.responseText);
			} catch (e) {
				var link = langs('ответ сервера');
				if (true || isAdmin) { // открыл для всех, всё равно можно вытащить из консоли ответ, а для понимания ошибок наличие ответа всегда важно.
					sw.responseServer = '<pre>' + response.responseText + '</pre>';
					link = '<a href="_blank" onclick="openNewWindow(sw.responseServer);return false;">ответ сервера</a>';
				}

				Ext6.Msg.alert(langs('Ошибка'), langs('Неверно сформированный ') + link + '!<br />' + langs('Обратитесь к разработчикам программы.'));
			}

			if (resultJson != undefined && resultJson.success != undefined
				&& resultJson.Error_Msg != undefined && !resultJson.success && resultJson.Error_Msg != 'YesNo' && !resultJson.Alert_Msg) {
				Ext6.Msg.alert(langs('Ошибка'), resultJson.Error_Msg);
			}
		}

		return me.callOverridden(arguments);
	}
});

Ext6.override(Ext6.form.Basic, {
	timeout: 120,
	findField: function(id) {
		var field = this.getFields().findBy(function(f) {
			return f.id === id || f.name === id || f.dataIndex === id;
		});

		if (!field) {
			log('findField: поле не найдено', id);
		}

		return field;
	}
});

Ext6.override(Ext6.picker.Date, {
	rangeFrom: null,
	rangeTo: null,
	setRange: function(rangeFrom, rangeTo) {
		var me = this;
		this.rangeFrom = rangeFrom;
		this.rangeTo = rangeTo;
		me.update(me.activeDate, true);
	},
	setValue: function(value) {
		this.callOverridden(arguments);
		this.fireEvent('setvalue');
	},
	fullUpdate: function(date) {
		var me = this,
			cells = me.cells.elements,
			textNodes = me.textNodes,
			disabledCls = me.disabledCellCls,
			eDate = Ext6.Date,
			i = 0,
			extraDays = 0,
			newDate = +eDate.clearTime(date, true),
			today = +eDate.clearTime(new Date()),
			min = me.minDate ? eDate.clearTime(me.minDate, true) : Number.NEGATIVE_INFINITY,
			max = me.maxDate ? eDate.clearTime(me.maxDate, true) : Number.POSITIVE_INFINITY,
			ddMatch = me.disabledDatesRE,
			ddText = me.disabledDatesText,
			ddays = me.disabledDays ? me.disabledDays.join('') : false,
			ddaysText = me.disabledDaysText,
			format = me.format,
			days = eDate.getDaysInMonth(date),
			firstOfMonth = eDate.getFirstDateOfMonth(date),
			startingPos = firstOfMonth.getDay() - me.startDay,
			previousMonth = eDate.add(date, eDate.MONTH, -1),
			ariaTitleDateFormat = me.ariaTitleDateFormat,
			prevStart, current, disableToday, tempDate, setCellClass, html, cls, formatValue, value;

		var rangeCls = me.baseCls + '-range';
		var rangeFrom = me.rangeFrom;
		var rangeTo = me.rangeTo;

		if (startingPos < 0) {
			startingPos += 7;
		}
		days += startingPos;
		prevStart = eDate.getDaysInMonth(previousMonth) - startingPos;
		current = new Date(previousMonth.getFullYear(), previousMonth.getMonth(), prevStart, me.initHour);
		if (me.showToday) {
			tempDate = eDate.clearTime(new Date());
			disableToday = (tempDate < min || tempDate > max || (ddMatch && format && ddMatch.test(eDate.dateFormat(tempDate, format))) || (ddays && ddays.indexOf(tempDate.getDay()) !== -1));
			me.todayDisabled = disableToday;
			if (!me.disabled) {
				me.todayBtn.setDisabled(disableToday);
			}
		}
		setCellClass = function(cellIndex, cls) {
			var cell = cells[cellIndex],
				describedBy = [];
			// Cells are not rendered with ids
			if (!cell.hasAttribute('id')) {
				cell.setAttribute('id', me.id + '-cell-' + cellIndex);
			}
			// store dateValue number as an expando
			value = +eDate.clearTime(current, true);
			cell.firstChild.dateValue = value;
			cell.setAttribute('aria-label', eDate.format(current, ariaTitleDateFormat));
			// Here and below we can't use title attribute instead of data-qtip
			// because JAWS will announce title value before cell content
			// which is not what we need. Also we are using aria-describedby attribute
			// and not placing the text in aria-label because some cells may have
			// compound descriptions (like Today and Disabled day).
			cell.removeAttribute('aria-describedby');
			cell.removeAttribute('data-qtip');
			if (value === today) {
				cls += ' ' + me.todayCls;
				describedBy.push(me.id + '-todayText');
			}
			if (value === newDate) {
				me.activeCell = cell;
				me.eventEl.dom.setAttribute('aria-activedescendant', cell.id);
				cell.setAttribute('aria-selected', true);
				cls += ' ' + me.selectedCls;
				me.fireEvent('highlightitem', me, cell);
			} else {
				cell.setAttribute('aria-selected', false);
			}
			if (value < min) {
				cls += ' ' + disabledCls;
				describedBy.push(me.id + '-ariaMinText');
				cell.setAttribute('data-qtip', me.minText);
			} else if (value > max) {
				cls += ' ' + disabledCls;
				describedBy.push(me.id + '-ariaMaxText');
				cell.setAttribute('data-qtip', me.maxText);
			} else if (ddays && ddays.indexOf(current.getDay()) !== -1) {
				cell.setAttribute('data-qtip', ddaysText);
				describedBy.push(me.id + '-ariaDisabledDaysText');
				cls += ' ' + disabledCls;
			} else if (ddMatch && format) {
				formatValue = eDate.dateFormat(current, format);
				if (ddMatch.test(formatValue)) {
					cell.setAttribute('data-qtip', ddText.replace('%0', formatValue));
					describedBy.push(me.id + '-ariaDisabledDatesText');
					cls += ' ' + disabledCls;
				}
			}

			if (rangeFrom && value >= rangeFrom && rangeTo && value <= rangeTo) {
				cls += ' ' + rangeCls;
			}

			if (describedBy.length) {
				cell.setAttribute('aria-describedby', describedBy.join(' '));
			}
			cell.className = cls + ' ' + me.cellCls;
		};
		me.eventEl.dom.setAttribute('aria-busy', 'true');
		for (; i < me.numDays; ++i) {
			if (i < startingPos) {
				html = (++prevStart);
				cls = me.prevCls;
			} else if (i >= days) {
				html = (++extraDays);
				cls = me.nextCls;
			} else {
				html = i - startingPos + 1;
				cls = me.activeCls;
			}
			textNodes[i].innerHTML = html;
			current.setDate(current.getDate() + 1);
			setCellClass(i, cls);
		}
		me.eventEl.dom.removeAttribute('aria-busy');
		me.monthBtn.setText(Ext6.Date.format(date, me.monthYearFormat));
	}
});

Ext6.override(Ext6.selection.Model, {
	getSelectedRecord: function() {
		if (this.hasSelection()) {
			return this.getSelection()[0];
		}

		return null;
	}
});

Ext6.override(Ext6.form.Panel, {
	getFirstInvalidEl: function() {
		var result = new Array();

		function elCheck(el, arr)
		{
			if ( el.items )
			{
				el.items.each(function(ff) {
					elCheck(ff, arr);
				});
			}
			else if (el.isValid && !el.isValid())
			{
				arr.push(el);
			}

			return arr;
		}

		this.items.each(function(f) {
			result = elCheck(f, result);
		});

		if ( result.length == 0 )
			result[0] = null;

		return result[0];
	}
});

Ext6.override(Ext6.panel.Panel, {
	// для совместимости с аналогичными методами в ExtJS 2
	getLoadMask: function(message) {
		var win = this;
		return {
			show: function() {
				win.mask(message);
			},
			hide: function() {
				win.unmask();
			}
		};
	},
	enableEdit: function(enable) {
		this.query('.field, .button').forEach(function(c) {
			c.setDisabled(!enable);
		});

		if (this.onEnableEdit && typeof this.onEnableEdit == 'function') {
			this.onEnableEdit(enable);
		}
	},
	/**
	 *  Проверка заполненности полей формы
	 *  Необходимо для поисковых форм
	 */
	isEmpty: function() {
		var flag = true;
		var vals = this.getForm().getValues();
		var base_form = this.getForm();
		var value;

		for (value in vals) {
			if (
				!base_form.findField(value)['hidden']
				&& base_form.findField(value)['xtype'] != 'hidden'
				&& base_form.findField(value)['xtype'] != 'button'
				&& vals[value].toString() != ','
				&& vals[value].toString() != '__.__.____'
				&& !base_form.findField(value)['ignoreIsEmpty']
				&& vals[value] != null
				&& vals[value].toString().replace(/[%_]/g, '').length > 0
			) {
				flag = false;
			}
		}
		return flag;
	}
});

Ext6.override(Ext6.form.field.Base, {
	// для совместимости с аналогичными методами в оверрайдах ExtJS 2
	setContainerVisible: function(visible) {
        if (this.rendered) {
            if (visible) {
                this.showContainer();
            } else {
                this.hideContainer();
            }
        }

        return this;
    },
	// для совместимости с аналогичными методами в оверрайдах ExtJS 2
	showContainer: function() {
        this.show();
    },
	// для совместимости с аналогичными методами в оверрайдах ExtJS 2
    hideContainer: function() {
        this.hide();
    },
	// для совместимости с аналогичными методами в оверрайдах ExtJS 2
	setAllowBlank: function(allowBlank) {
		this.allowBlank = allowBlank;

		if (this.rendered && typeof this.validate == 'function') {
			this.validate();
		}
	},
	enable: function() {
		this.callOverridden(arguments);

		if (this.rendered && typeof this.validate == 'function') {
			this.validate();
		}

		return this;
	},
	disable: function() {
		this.callOverridden(arguments);

		if (this.rendered && typeof this.validate == 'function') {
			this.validate();
		}

		return this;
	}
});

Ext6.override(Ext6.layout.container.Accordion, {
	allowCollapseAll: true, // разрешать сворачивать все разделы аккордеона
	onBeforeComponentCollapse: function(comp) {
		var me = this,
			owner = me.owner,
			toExpand, expanded, previousValue;
		if (me.owner.items.getCount() === 1) {
			// do not allow collapse if there is only one item
			return false;
		}
		if (!me.processing) {
			me.processing = true;
			previousValue = owner.deferLayouts;
			owner.deferLayouts = true;
			toExpand = comp.next() || comp.prev();
			// If we are allowing multi, and the "toCollapse" component is NOT the only expanded Component,
			// then ask the box layout to collapse it to its header.
			if (me.multi) {
				expanded = me.getExpanded();
				// If the collapsing Panel is the only expanded one, expand the following Component.
				// All this is handling fill: true, so there must be at least one expanded,
				if (!me.allowCollapseAll && expanded.length === 1) {
					toExpand.expand();
				}
			} else if (!me.allowCollapseAll && toExpand) {
				toExpand.expand();
			}
			owner.deferLayouts = previousValue;
			me.processing = false;
		}
	}
});

Ext6.override(Ext6.window.MessageBox, {
	initComponent: function(cfg) {
		var me = this,
			baseId = me.id,
			i, button;
		// A title or iconCls could have been passed in the config to the constructor.
		me.title = me.title || '&#160;';
		me.iconCls = me.iconCls || '';
		me.topContainer = new Ext6.container.Container({
			layout: 'hbox',
			padding: 30,
			style: {
				overflow: 'hidden'
			},
			items: [
				me.iconComponent = new Ext6.Component({
					cls: me.baseIconCls
				}),
				me.promptContainer = new Ext6.container.Container({
					flex: 1,
					layout: {
						type: 'vbox',
						align: 'stretch'
					},
					items: [
						me.msg = new Ext6.Component({
							id: baseId + '-msg',
							cls: me.baseCls + '-text'
						}),
						me.textField = new Ext6.form.field.Text({
							id: baseId + '-textfield',
							enableKeyEvents: true,
							ariaAttributes: {
								'aria-labelledby': me.msg.id
							},
							listeners: {
								keydown: me.onPromptKey,
								scope: me
							}
						}),
						me.textArea = new Ext6.form.field.TextArea({
							id: baseId + '-textarea',
							height: 75,
							ariaAttributes: {
								'aria-labelledby': me.msg.id
							}
						})
					]
				})
			]
		});
		me.progressBar = new Ext6.ProgressBar({
			id: baseId + '-progressbar',
			margin: '0 10 10 10'
		});
		me.items = [
			me.topContainer,
			me.progressBar
		];
		// Create the buttons based upon passed bitwise config
		me.msgButtons = [];
		for (i = 0; i < 4; i++) {
			button = me.makeButton(i);
			me.msgButtons[button.itemId] = button;
			me.msgButtons.push(button);
		}

		me.msgButtons[0].cls = 'flat-button-primary';
		me.msgButtons[1].cls = 'flat-button-primary';

		me.bottomTb = new Ext6.toolbar.Toolbar({
			id: baseId + '-toolbar',
			ui: 'footer',
			dock: 'bottom',
			focusableContainer: false,
			ariaRole: null,
			items: [
				'->',
				me.msgButtons[2],
				me.msgButtons[3],
				me.msgButtons[0],
				me.msgButtons[1]
			]
		});
		me.dockedItems = [
			me.bottomTb
		];
		me.on('close', me.onClose, me);

		Ext6.window.MessageBox.superclass.initComponent.call(this);
	}
});

/**
 * Кастомное оверфлоу меню, которое появляется, если тулбар не влазит в ширину экрана
 */
Ext6.override(Ext6.layout.container.boxOverflow.Menu, {
	getSuffixConfig: function() {
		var me = this,
			layout = me.layout,
			owner = layout.owner,
			oid = owner.id;
		/**
		 * @private
		 * @property {Ext.menu.Menu} menu
		 * The expand menu - holds items for every item that cannot be shown
		 * because the container is currently not large enough.
		 */
		me.menu = new Ext6.menu.Menu({
			cls: 'more-menu',
			listeners: {
				scope: me,
				beforeshow: me.beforeMenuShow
			}
		});
		/**
		 * @private
		 * @property {Ext.button.Button} menuTrigger
		 * The expand button which triggers the overflow menu to be shown
		 */
		me.menuTrigger = new Ext6.button.Button({
			id: oid + '-menu-trigger',
			cls: me.menuCls + '-after ' + Ext6.baseCSSPrefix + 'toolbar-item',
			plain: owner.usePlainButtons,
			ownerCt: owner,
			// To enable the Menu to ascertain a valid zIndexManager owner in the same tree
			ownerLayout: layout,
			iconCls: 'more-icon16',
			ui: owner.defaultButtonUI || 'default',
			menu: me.menu,
			text: 'Ещё...',
			// Menu will be empty when we're showing it because we populate items after
			showEmptyMenu: true
		});
		return me.menuTrigger.getRenderTree();
	}
});

/**
 * Закомментировал пока saveTabbableState и restoreTabbableState, т.к. из-за них очень сильно тормозит отрисовка модальных окон.
 */
Ext6.override(Ext6.ZIndexManager, {
	privates: {
		showModalMask: function(comp) {
			var me = this,
				compEl = comp.el,
				maskTarget = comp.floatParent ? comp.floatParent.getEl() : comp.container,
				mask = me.mask;
			if (!mask) {
				// Create the mask at zero size so that it does not affect upcoming target measurements.
				me.mask = mask = Ext6.getBody().createChild({
					// tell the spec runner to ignore this element when checking if the dom is clean
					'data-sticky': true,
					role: 'presentation',
					cls: Ext6.baseCSSPrefix + 'mask ' + Ext6.baseCSSPrefix + 'border-box',
					style: 'height:0;width:0'
				});
				mask.setVisibilityMode(Ext6.Element.DISPLAY);
				mask.on({
					mousedown: me.onMaskMousedown,
					click: me.onMaskClick,
					scope: me
				});
			} else // If the mask is already shown, hide it before showing again
			// to ensure underlying elements' tabbability is restored
			{
				me.hideModalMask();
			}
			mask.maskTarget = maskTarget;
			// Since there is no fast and reliable way to find elements above or below
			// a given z-index, we just cheat and prevent tabbable elements within the
			// topmost component from being made untabbable.
			/* maskTarget.saveTabbableState({
				excludeRoot: compEl
			});*/
			// Size and zIndex stack the mask (and its shim)
			me.syncModalMask(comp);
		},
		hideModalMask: function() {
			var mask = this.mask,
				maskShim = this.maskShim;
			if (mask && mask.isVisible()) {
				// mask.maskTarget.restoreTabbableState();
				mask.maskTarget = undefined;
				mask.hide();
				if (maskShim) {
					maskShim.hide();
				}
			}
		}
	}
});

/**
 * Writer для использования в model -> proxy -> writer
 * Используется для корректной работы с php-методами промеда, добавляет параметр вида idProperty=12345 в параметры запроса (EvnMediaData_id=100053)
 * По умолчанию record.erase() и прочие из CRUD добавляют этот параметр в тело запроса в виде зашифрованного json-массива
 * Возможно ему здесь не место и надо перенести
 */
Ext6.define('QueryStringWriter', {
	extend: 'Ext6.data.JsonWriter',
	alias: 'writer.QueryStringWriter',
	writeRecords: function (request, data)
	{
		this.callParent(arguments);

		if (request._action == 'destroy')
		{
			var idProperty = request.getRecords()[0].getIdProperty();

			request.setParam(idProperty, data[0][idProperty]); // ставим параметр в url
			request.setJsonData(); // зануляем
		}
		return request;
	}
});
/**
 * Кнопки редактирования в строке таблицы
 * Используется в мониторинге
 */
Ext6.grid.RowEditorButtons.override({
    constructor: function (config) {
        var me = this,
            rowEditor = config.rowEditor,
            cssPrefix = Ext6.baseCSSPrefix,
            plugin = rowEditor.editingPlugin;

        config = Ext6.apply({
            baseCls: 'grid-row-editor-buttons',
            defaults: {
                xtype: 'button'
            },
            items: [{
				userCls: 'button-without-frame dm-row-tool-btn',
				iconCls: 'dm-row-tool-complete',
				padding: '12 6 0 6',
				itemId: 'update',
                text: '',
                handler: function(editor, context, eOpts) {
					plugin.completeEdit();
				}
            }, {
                userCls: 'button-without-frame dm-row-tool-btn',
                iconCls: 'dm-row-tool-copy',
                text: '',
                padding: '12 6 0 6',
                handler: function(editor, context, eOpts) {
					if(!Ext6.isEmpty(plugin.grid.doCopy))
						plugin.grid.doCopy();
                }
            }, {
                userCls: 'button-without-frame dm-row-tool-btn',
                iconCls: 'dm-row-tool-del',
                text: '',
                padding: '12 6 0 6',
                handler: function(editor, context, eOpts) {
					if(!Ext6.isEmpty(plugin.grid.doDelete))
						plugin.grid.doDelete();
                }
            }]
        }, config);
 
        Ext6.grid.RowEditorButtons.superclass.constructor.call(this, config);
 
        me.addClsWithUI(me.position);
    }
});