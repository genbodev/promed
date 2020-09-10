/**
* swMessageBox - компонент заменяющий стандартный Ext6.Msg
* отличие в том, что работает клавиатура для перехода по кнопкам
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      libs
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Petukhov Ivan aka Lich (megatherion@list.ru)
* @version      29.05.2009
*/
// @define Ext.MessageBox, Ext.Msg

/**
 * Utility class for generating different styles of message boxes.  The singleton instance, Ext.MessageBox
 * alias `Ext.Msg` can also be used.
 *
 * Note that a MessageBox is asynchronous.  Unlike a regular JavaScript `alert` (which will halt
 * browser execution), showing a MessageBox will not cause the code to stop.  For this reason, if you have code
 * that should only run *after* some user feedback from the MessageBox, you must use a callback function
 * (see the `function` parameter for {@link #method-show} for more details).
 *
 * Basic alert
 *
 *     @example
 *     Ext.Msg.alert('Status', 'Changes saved successfully.');
 *
 * Prompt for user data and process the result using a callback
 *
 *     @example
 *     Ext.Msg.prompt('Name', 'Please enter your name:', function(btn, text){
 *         if (btn == 'ok'){
 *             // process text value and close...
 *         }
 *     });
 *
 * Show a dialog using config options
 *
 *     @example
 *     Ext.Msg.show({
 *          title:'Save Changes?',
 *          msg: 'You are closing a tab that has unsaved changes. Would you like to save your changes?',
 *          buttons: Ext.Msg.YESNOCANCEL,
 *          icon: Ext.Msg.QUESTION
 *     });
 */
Ext6.define('sw.swMessageBox', {
	extend:  Ext6.window.Window ,

	alias: 'widget.swmessagebox',

	/**
	 * @property
	 * Button config that displays a single OK button
	 */
	OK : 1,
	/**
	 * @property
	 * Button config that displays a single Yes button
	 */
	YES : 2,
	OKYES : 3,
	/**
	 * @property
	 * Button config that displays a single No button
	 */
	NO : 4,
	OKNO : 5,
	/**
	 * @property
	 * Button config that displays Yes and No buttons
	 */
	YESNO : 6,
	OKYESNO : 7,
	/**
	 * @property
	 * Button config that displays a single Cancel button
	 */
	CANCEL : 8,
	/**
	 * @property
	 * Button config that displays OK and Cancel buttons
	 */
	OKCANCEL : 9,
	YESCANCEL : 10,
	OKYESCANCEL : 11,
	NOCANCEL : 12,
	OKNOCANCEL : 13,
	/**
	 * @property
	 * Button config that displays Yes, No and Cancel buttons
	 */
	YESNOCANCEL : 14,
	OKYESNOCANCEL : 15,
	//REFRESHCLOSE : {refresh:true, close:true},

	/**
	 * @property
	 * The CSS class that provides the INFO icon image
	 */
	INFO : Ext6.baseCSSPrefix + 'message-box-info',
	/**
	 * @property
	 * The CSS class that provides the WARNING icon image
	 */
	WARNING : 'ext-mb-warning',
	//WARNING : Ext6.baseCSSPrefix + 'message-box-warning',
	/**
	 * @property
	 * The CSS class that provides the QUESTION icon image
	 */
	QUESTION : 'ext-mb-question',
	//QUESTION : Ext6.baseCSSPrefix + 'message-box-question',
	/**
	 * @property
	 * The CSS class that provides the ERROR icon image
	 */
	ERROR : 'ext-mb-error',
	//ERROR : Ext6.baseCSSPrefix + 'message-box-error',

	// hide it by offsets. Windows are hidden on render by default.
	hideMode: 'offsets',
	closeAction: 'hide',
	resizable: false,
	title: '&#160;',

	defaultMinWidth: 460,
	defaultMaxWidth: 600,
	defaultMinHeight: 180,
	defaultMaxHeight: 500,

	// Forcibly set these to null on the prototype to override anything set higher in
	// the hierarchy
	minWidth: null,
	maxWidth: null,
	minHeight: null,
	maxHeight: null,
	constrain: true,

	cls: [Ext6.baseCSSPrefix + 'message-box', Ext6.baseCSSPrefix + 'hide-offsets', 'x-window-dlg'],
	bodyPadding: '20',
	layout: {
		type: 'hbox',
		align: 'middle',
		pack: 'start'
	},

	// We want to shrinkWrap around all docked items
	shrinkWrapDock: true,
	// Если на новом эксте заголовок нужно показать, не смотря на отсутствие его по гайдам, добавляем в конфиг titleHard
	titleHard: false,
	/**
	 * @property
	 * The default height in pixels of the message box's multiline textarea if displayed.
	 */
	defaultTextHeight : 75,
	/**
	 * @property
	 * The minimum width in pixels of the message box if it is a progress-style dialog.  This is useful
	 * for setting a different minimum width than text-only dialogs may need.
	 */
	minProgressWidth : 250,
	/**
	 * @property
	 * The minimum width in pixels of the message box if it is a prompt dialog.  This is useful
	 * for setting a different minimum width than text-only dialogs may need.
	 */
	minPromptWidth: 250,
	//<locale type="object">
	/**
	 * @property
	 * An object containing the default button text strings that can be overriden for localized language support.
	 * Supported properties are: ok, cancel, yes and no.  Generally you should include a locale-specific
	 * resource file for handling language support across the framework.
	 * Customize the default text like so:
	 *
	 *     Ext6.window.MessageBox.buttonText.yes = "oui"; //french
	 */
	buttonText : {
		ok: "OK",
		cancel: "Отмена",
		yes: "Да",
		no: "Нет",
		refresh: "Обновить",
		close: "Закрыть"
	},
	defaultButtonText : {
		ok: "OK",
		cancel: "Отмена",
		yes: "Да",
		no: "Нет",
		refresh: "Обновить",
		close: "Закрыть"
	},
	//</locale>

	buttonIds: [
		'ok', 'yes', 'no', 'cancel', 'refresh', 'close'
	],

	//<locale type="object">
	titleText: {
		confirm: 'Подтверждение',
		prompt: 'Напоминание',
		wait: 'Загрузка...',
		alert: 'Внимание'
	},
	//</locale>

	iconHeight: 40,
	iconWidth: 50,

	ariaRole: 'alertdialog',

	makeButton: function(btnIdx) {
		var btnId = this.buttonIds[btnIdx];

		return new Ext6.button.Button({
			handler: this.btnCallback,
			cls: 'swMsg-btn-'+btnId,
			userCls: 'btn-swMsg-minor-super-flat',
			itemId: btnId,
			scope: this,
			text: this.buttonText[btnId],
			minWidth: 75
		});
		// @todo перенести функции на новые кнопки
		//buttons["refresh"] = dlg.add(Ext6.create('Ext6.Button', {text: bt["refresh"], handler: Ext6.Function.pass(handleButton, ["refresh"], this)}));
		//buttons["close"] = dlg.add(Ext6.create('Ext6.Button', {text: bt["close"], handler: Ext6.Function.pass(handleButton, ["close"], this)}));
	},

	btnCallback: function(btn) {
		var me = this,
			value,
			field;

		if (me.cfg.prompt || me.cfg.multiline) {
			if (me.cfg.multiline) {
				field = me.textArea;
			} else {
				field = me.textField;
			}
			value = field.getValue();
			field.reset();
		}

		// Component.onHide blurs the active element if the Component contains the active element
		me.hide();
		me.userCallback(btn.itemId, value, me.cfg);
	},

	hide: function() {
		var me = this,
			cls = me.cfg.cls;

		me.progressBar.reset();
		if (cls) {
			me.removeCls(cls);
		}
		me.callParent(arguments);
	},

	constructor: function(cfg) {
		var me = this;

		me.callParent(arguments);

		// set the default min/max/Width/Height to the initially configured min/max/Width/Height
		// so that it will be used as the default when reconfiguring.
		me.minWidth = me.defaultMinWidth = (me.minWidth || me.defaultMinWidth);
		me.maxWidth = me.defaultMaxWidth = (me.maxWidth || me.defaultMaxWidth);
		me.minHeight = me.defaultMinHeight = (me.minHeight || me.defaultMinHeight);
		me.maxHeight = me.defaultMaxHeight = (me.maxHeight || me.defaultMaxHeight);
	},

	initComponent: function(cfg) {
		var me = this,
			baseId = me.id,
			i, button;

		me.title = '&#160;';
		var textFieldParams = {
			id: baseId + '-textfield',
			width: '100%',
			enableKeyEvents: true,
			listeners: {
				keydown: me.onPromptKey,
				scope: me
			}
		};
		if(cfg && !Ext6.isEmpty(cfg.labelField)){
			textFieldParams.labelAlign = 'left';
			textFieldParams.fieldLabel = cfg.fieldLabel;
			textFieldParams.labelWidth = 70;
		}
		me.textField = new Ext6.form.field.Text(textFieldParams);
		me.topContainer = new Ext6.container.Container({
			layout: 'hbox',
			style: {
				overflow: 'hidden'
			},
			items: [
				me.iconComponent = new Ext6.Component({
					// иначе не получается http://prntscr.com/nkzu46
					margin: '20 10 0 20',
					width: me.iconWidth,
					// добивать нужно высоту иконки, а не отступ
					height: me.iconHeight
				}),
				me.promptContainer = new Ext6.container.Container({
					flex: 1,
					layout: {
						type: 'vbox',
						align: 'middle'
					},
					items: [
						me.msg = new Ext6.form.field.Display({
							maxWidth: 400,
							centered: true,
							padding: '10 0 0',
							id: baseId + '-displayfield',
							cls: me.baseCls + '-text'
						}),
						me.textField,
						me.textArea = new Ext6.form.field.TextArea({
							id: baseId + '-textarea',
							anchor: '90%',
							height: 75
						})
					]
				})
			]
		});
		me.progressBar = new Ext6.ProgressBar({
			id: baseId + '-progressbar',
			margin: '0 10 10 10'
		});

		me.items = [me.topContainer, me.progressBar];

		// Create the buttons based upon passed bitwise config
		me.msgButtons = [];
		for (i = 0; i < 4; i++) {
			button = me.makeButton(i);
			me.msgButtons[button.itemId] = button;
			me.msgButtons.push(button);
		}
		me.bottomTb = new Ext6.toolbar.Toolbar({
			id: baseId + '-toolbar',
			ui: 'footer',
			dock: 'bottom',
			style:{
				backgroundColor: 'white'
			},
			layout: {
				pack: 'end'
			},
			items: [
				me.msgButtons[3],
				me.msgButtons[2],
				me.msgButtons[1],
				me.msgButtons[0]
			]
		});
		me.dockedItems = [me.bottomTb];
		me.on('close', me.onClose, me);
		me.callParent();
	},

	onClose: function(){
		var btn = this.header.child('[type=close]');
		// Give a temporary itemId so it can act like the cancel button
		btn.itemId = 'cancel';
		this.btnCallback(btn);
		delete btn.itemId;
	},

	onPromptKey: function(textField, e) {
		var me = this;

		if (e.keyCode === e.RETURN || e.keyCode === 10) {
			if (me.msgButtons.ok.isVisible()) {
				me.msgButtons.ok.handler.call(me, me.msgButtons.ok);
			} else if (me.msgButtons.yes.isVisible()) {
				me.msgButtons.yes.handler.call(me, me.msgButtons.yes);
			}
		}
	},

	reconfigure: function(cfg) {
		var me = this,
			buttons = 0,
			hideToolbar = true,
			resizer = me.resizer,
			resizeTracker, width, height, i, textArea, textField,
			msg, progressBar, msgButtons;

		// Restore default buttonText before reconfiguring.
		me.updateButtonText();

		cfg = cfg || {};
		me.cfg = cfg;
		if (cfg.width) {
			width = cfg.width;
		}

		if (cfg.height) {
			height = cfg.height;
		}

		me.minWidth = cfg.minWidth || me.defaultMinWidth;
		me.maxWidth = cfg.maxWidth || me.defaultMaxWidth;
		me.minHeight = cfg.minHeight || me.defaultMinHeight;
		me.maxHeight = cfg.maxHeight || me.defaultMaxHeight;

		if (resizer) {
			resizeTracker = resizer.resizeTracker;
			resizer.minWidth = resizeTracker.minWidth = me.minWidth;
			resizer.maxWidth = resizeTracker.maxWidth = me.maxWidth;
			resizer.minHeight = resizeTracker.minHeight = me.minHeight;
			resizer.maxHeight = resizeTracker.maxHeight = me.maxHeight;
		}

		// Default to allowing the Window to take focus.
		delete me.defaultFocus;
		if (cfg.defaultFocus) {
			me.defaultFocus = cfg.defaultFocus;
		}

		// clear any old animateTarget
		me.animateTarget = cfg.animateTarget || undefined;

		// Defaults to modal
		me.modal = cfg.modal !== false;

		// Show the title/icon
		me.setTitle(cfg.title || '');
		me.setIconCls(cfg.iconCls || '');

		// Extract button configs
		if (Ext6.isObject(cfg.buttons)) {
			// Все ради 2 Экста
			var c = cfg.buttons;
			if(!cfg.buttonText){
				for (var key in cfg.buttons){
					if(key && Ext6.isObject(cfg.buttons[key]) && cfg.buttons[key].text){
						me.buttonText[key] = cfg.buttons[key].text
					}
				}
			}
			// Так как из 2-ки приходит старая структура конфигов, назначаем вручную
			if (c.ok &&c.yes && c.no && c.cancel) {
				buttons = 15;
			} else if (c.yes && c.no && c.cancel) {
				buttons = 14;
			} else if (c.ok && c.no && c.cancel) {
				buttons = 13;
			} else if (c.no && c.cancel) {
				buttons = 12;
			} else if (c.ok && c.yes && c.cancel) {
				buttons = 11;
			} else if (c.yes && c.cancel) {
				buttons = 10;
			} else if (c.ok && c.cancel) {
				buttons = 9;
			} else if (c.cancel) {
				buttons = 8;
			} else if (c.ok && c.yes && c.no) {
				buttons = 7;
			} else if (c.yes && c.no) {
				buttons = 6;
			} else if (c.ok && c.no) {
				buttons = 5;
			} else if (c.no) {
				buttons = 4;
			} else if (c.yes && c.ok) {
				buttons = 3;
			} else if (c.yes) {
				buttons = 2;
			} else if (c.ok) {
				buttons = 1;
			} else {
				buttons = 0;
			}
		} else {
			me.buttonText = cfg.buttonText || me.buttonText;
			buttons = Ext6.isNumber(cfg.buttons) ? cfg.buttons : 0;
		}

		// Apply custom-configured buttonText
		// Infer additional buttons from the specified property names in the buttonText object
		buttons = buttons | me.updateButtonText();

		// Restore buttonText. Next run of reconfigure will restore to prototype's buttonText
		me.buttonText = Ext6.clone(me.defaultButtonText);

		// During the on render, or size resetting layouts, and in subsequent hiding and showing, we need to
		// suspend layouts, and flush at the end when the Window's children are at their final visibility.
		Ext6.suspendLayouts();
		delete me.width;
		delete me.height;
		if (width || height) {
			if (width) {
				me.setWidth(width);
			}

			if (height) {
				me.setHeight(height);
			}
		}
		me.hidden = false;
		if (!me.rendered) {
			me.render(Ext6.getBody());
		}

		// Hide or show the close tool
		me.closable = cfg.closable !== false && !cfg.wait;
		me.header.child('[type=close]').setVisible(me.closable);

		// Hide or show the header
		if ((!cfg.title && !me.closable && !cfg.iconCls) // По экстовски
			// в ExtJs 6 в алертах скрываем header, но если titleHard = true -  все равно header с title выводим
			|| (!cfg.titleHard && Ext.globalOptions && Ext.globalOptions.globals && Ext.globalOptions.globals.client && Ext.globalOptions.globals.client == 'ext6')
		) {
			me.header.hide();
		} else {
			me.header.show();
		}

		// Default to dynamic drag: drag the window, not a ghost
		me.liveDrag = !cfg.proxyDrag;

		// wrap the user callback
		me.userCallback = Ext6.Function.bind(cfg.callback ||cfg.fn || Ext6.emptyFn, cfg.scope || Ext6.global);

		// Hide or show the icon Component
		var hardHideIcon = false;
		if(cfg && cfg.hardHideIcon)
			hardHideIcon = true;
		me.setIcon(cfg.icon, cfg.iconWidth, cfg.iconHeight, hardHideIcon);

		// Hide or show the message area
		msg = me.msg;
		if (cfg.msg) {
			msg.setValue(cfg.msg);
			msg.show();
		} else {
			msg.hide();
		}

		// Hide or show the input field
		textArea = me.textArea;
		textField = me.textField;
		if (cfg.prompt || cfg.multiline) {
			me.multiline = cfg.multiline;
			if (cfg.multiline) {
				textArea.setValue(cfg.value);
				textArea.setHeight(cfg.defaultTextHeight || me.defaultTextHeight);
				textArea.show();
				textField.hide();
				me.defaultFocus = textArea;
			} else {
				textField.setValue(cfg.value);
				textArea.hide();
				textField.show();
				me.defaultFocus = textField;
			}

		} else {
			textArea.hide();
			textField.hide();
		}

		if(cfg && !Ext6.isEmpty(cfg.fieldLabel)){
			textField.setFieldLabel(cfg.fieldLabel);
		}
		if(cfg && !Ext6.isEmpty(cfg.widthField)){
			textField.setWidth(cfg.widthField);
		}

		// Hide or show the progress bar
		progressBar = me.progressBar;
		if (cfg.progress || cfg.wait) {
			progressBar.show();
			me.updateProgress(0, cfg.progressText);
			if(cfg.wait === true){
				progressBar.wait(cfg.waitConfig);
			}
		} else {
			progressBar.hide();
		}

		// Hide or show buttons depending on flag value sent.
		msgButtons = me.msgButtons;
		var btnMain, isFirst = true;
		for (i = 0; i < 4; i++) {
			msgButtons[i].removeCls('btn-swMsg-main-super-flat');
			if (buttons & Math.pow(2, i)) {

				// Default to focus on the first visible button if focus not already set
				if (!me.defaultFocus) {
					me.defaultFocus = msgButtons[i];
				}
				if(isFirst){
					isFirst = false;
					msgButtons[i].addCls('btn-swMsg-main-super-flat');
					msgButtons[i].removeCls('btn-swMsg-minor-super-flat');
				}
				msgButtons[i].show();
				btnMain = msgButtons[i];
				hideToolbar = false;
			} else {
				msgButtons[i].hide();
			}
		}
		// Hide toolbar if no buttons to show
		if (hideToolbar) {
			me.bottomTb.hide();
		} else {
			me.bottomTb.show();
		}
		Ext6.resumeLayouts(true);
	},

	/**
	 * @private
	 * Set button text according to current buttonText property object
	 * @return {Number} The buttons bitwise flag based upon the button IDs specified in the buttonText property.
	 */
	updateButtonText: function() {
		var me = this,
			buttonText = me.buttonText,
			buttons = 0,
			btnId,
			btn;
		for (btnId in buttonText) {
			if (buttonText.hasOwnProperty(btnId)) {
				btn = me.msgButtons[btnId];
				if (btn) {
					if (me.cfg && me.cfg.buttonText) {
						buttons = buttons | Math.pow(2, Ext6.Array.indexOf(me.buttonIds, btnId));
					}
					if (btn.text != buttonText[btnId]) {
						btn.setText(buttonText[btnId]);
					}
				}
			}
		}
		return buttons;
	},

	/**
	 * Displays a new message box, or reinitializes an existing message box, based on the config options passed in. All
	 * display functions (e.g. prompt, alert, etc.) on MessageBox call this function internally, although those calls
	 * are basic shortcuts and do not support all of the config options allowed here.
	 *
	 * Example usage:
	 *
	 *     Ext6.Msg.show({
     *         title: 'Address',
     *         msg: 'Please enter your address:',
     *         width: 300,
     *         buttons: Ext6.Msg.OKCANCEL,
     *         multiline: true,
     *         fn: saveAddress,
     *         animateTarget: 'addAddressBtn',
     *         icon: Ext6.window.MessageBox.INFO
     *     });
	 *
	 * @param {Object} config The following config options are supported:
	 *
	 * @param {String/Ext6.dom.Element} config.animateTarget
	 * An id or Element from which the message box should animate as it opens and closes.
	 *
	 * @param {Number} [config.buttons=false]
	 * A bitwise button specifier consisting of the sum of any of the following constants:
	 *
	 *  - Ext6.MessageBox.OK
	 *  - Ext6.MessageBox.YES
	 *  - Ext6.MessageBox.NO
	 *  - Ext6.MessageBox.CANCEL
	 *
	 * Some common combinations have already been predefined:
	 *
	 *  - Ext6.MessageBox.OKCANCEL
	 *  - Ext6.MessageBox.YESNO
	 *  - Ext6.MessageBox.YESNOCANCEL
	 *
	 * Or false to not show any buttons.
	 *
	 * This may also be specified as an object hash containing custom button text in the same format as the
	 * {@link #buttonText} config. Button IDs present as property names will be made visible.
	 *
	 * @param {Boolean} config.closable
	 * False to hide the top-right close button (defaults to true). Note that progress and wait dialogs will ignore this
	 * property and always hide the close button as they can only be closed programmatically.
	 *
	 * @param {String} config.cls
	 * A custom CSS class to apply to the message box's container element
	 *
	 * @param {Number} [config.defaultTextHeight=75]
	 * The default height in pixels of the message box's multiline textarea if displayed.
	 *
	 * @param {Function} config.fn
	 * A callback function which is called when the dialog is dismissed either by clicking on the configured buttons, or
	 * on the dialog close button, or by pressing the return button to enter input.
	 *
	 * Progress and wait dialogs will ignore this option since they do not respond to user actions and can only be
	 * closed programmatically, so any required function should be called by the same code after it closes the dialog.
	 * Parameters passed:
	 *
	 *  @param {String} config.fn.buttonId The ID of the button pressed, one of:
	 *
	 * - ok
	 * - yes
	 * - no
	 * - cancel
	 *
	 *  @param {String} config.fn.text Value of the input field if either `prompt` or `multiline` is true
	 *  @param {Object} config.fn.opt The config object passed to show.
	 *
	 * @param {Object} config.buttonText
	 * An object containing string properties which override the system-supplied button text values just for this
	 * invocation. The property names are:
	 *
	 * - ok
	 * - yes
	 * - no
	 * - cancel
	 *
	 * @param {Object} config.scope
	 * The scope (`this` reference) in which the function will be executed.
	 *
	 * @param {String} config.icon
	 * A CSS class that provides a background image to be used as the body icon for the dialog.
	 * One can use a predefined icon class:
	 *
	 *  - Ext6.MessageBox.INFO
	 *  - Ext6.MessageBox.WARNING
	 *  - Ext6.MessageBox.QUESTION
	 *  - Ext6.MessageBox.ERROR
	 *
	 * or use just any `'custom-class'`. Defaults to empty string.
	 *
	 * @param {String} config.iconCls
	 * The standard {@link Ext6.window.Window#iconCls} to add an optional header icon (defaults to '')
	 *
	 * @param {String} config.defaultFocus
	 * The button to focus when showing the dialog. If not specified, defaults to
	 * the first visible button.
	 *
	 * @param {Number} config.maxWidth
	 * The maximum width in pixels of the message box (defaults to 600)
	 *
	 * @param {Number} config.minWidth
	 * The minimum width in pixels of the message box (defaults to 100)
	 *
	 * @param {Boolean} config.modal
	 * False to allow user interaction with the page while the message box is displayed (defaults to true)
	 *
	 * @param {String} config.msg
	 * A string that will replace the existing message box body text (defaults to the XHTML-compliant non-breaking space
	 * character '&#160;')
	 *
	 * @param {Boolean} config.multiline
	 * True to prompt the user to enter multi-line text (defaults to false)
	 *
	 * @param {Boolean} config.progress
	 * True to display a progress bar (defaults to false)
	 *
	 * @param {String} config.progressText
	 * The text to display inside the progress bar if progress = true (defaults to '')
	 *
	 * @param {Boolean} config.prompt
	 * True to prompt the user to enter single-line text (defaults to false)
	 *
	 * @param {Boolean} config.proxyDrag
	 * True to display a lightweight proxy while dragging (defaults to false)
	 *
	 * @param {String} config.title
	 * The title text
	 *
	 * @param {String} config.value
	 * The string value to set into the active textbox element if displayed
	 *
	 * @param {Boolean} config.wait
	 * True to display a progress bar (defaults to false)
	 *
	 * @param {Object} config.waitConfig
	 * A {@link Ext6.ProgressBar#wait} config object (applies only if wait = true)
	 *
	 * @param {Number} config.width
	 * The width of the dialog in pixels
	 *
	 * @return {Ext6.window.MessageBox} this
	 */
	show: function(cfg) {
		var me = this,
			visibleFocusables;

		me.buttonText = Ext6.clone(me.defaultButtonText);
		// If called during global layout suspension, make the call after layout resumption
		if (Ext6.AbstractComponent.layoutSuspendCount) {
			Ext6.on({
				resumelayouts: function() {
					me.show(cfg);
				},
				single: true
			});
			return me;
		}

		me.reconfigure(cfg);
		if (cfg && cfg.cls) {
			me.addCls(cfg.cls);
		}

		// Do not steal focus from anything that may be focused if the MessageBox has no visible focusable
		// items. For example, a "wait" message box should not get focus.
		visibleFocusables = me.query('textfield:not([hidden]),textarea:not([hidden]),button:not([hidden])');
		me.preventFocusOnActivate = !visibleFocusables.length;

		// Set the flag, so that the parent show method performs the show procedure that we need.
		// ie: animation from animTarget, onShow processing and focusing.
		me.hidden = true;
		me.callParent();
		return me;
	},

	onShow: function() {
		this.callParent(arguments);
		this.center();
	},

	updateText: function(text) {
		this.msg.setValue(text);
	},

	/**
	 * Adds the specified icon to the dialog.  By default, the class 'x-messagebox-icon' is applied for default
	 * styling, and the class passed in is expected to supply the background image url. Pass in empty string ('')
	 * to clear any existing icon. This method must be called before the MessageBox is shown.
	 * The following built-in icon classes are supported, but you can also pass in a custom class name:
	 *
	 *     Ext6.window.MessageBox.INFO
	 *     Ext6.window.MessageBox.WARNING
	 *     Ext6.window.MessageBox.QUESTION
	 *     Ext6.window.MessageBox.ERROR
	 *
	 * @param {String} icon A CSS classname specifying the icon's background image url, or empty string to clear the icon
	 * @param {Number} [width] The width of the icon. If not specified, the default is used
	 * @param {Number} [height] The height of the icon. If not specified, the default is used
	 * @return {Ext6.window.MessageBox} this
	 */
	setIcon : function(icon, width, height, hardHideIcon) {
		var me = this,
			iconCmp = me.iconComponent,
			cls = me.messageIconCls;

		if (cls) {
			iconCmp.removeCls(cls);
		}
		if(!icon){
			icon = me.WARNING;
		}
		if (icon && !hardHideIcon) {
			iconCmp.show();
			iconCmp.setSize(width || me.iconWidth, height || me.iconHeight);
			iconCmp.addCls(Ext6.baseCSSPrefix + 'dlg-icon');
			iconCmp.addCls(me.messageIconCls = icon);
		} else {
			iconCmp.removeCls(Ext6.baseCSSPrefix + 'dlg-icon');
			iconCmp.hide();
		}
		return me;
	},

	/**
	 * Updates a progress-style message box's text and progress bar. Only relevant on message boxes
	 * initiated via {@link Ext6.window.MessageBox#progress} or {@link Ext6.window.MessageBox#wait},
	 * or by calling {@link Ext6.window.MessageBox#method-show} with progress: true.
	 *
	 * @param {Number} [value=0] Any number between 0 and 1 (e.g., .5)
	 * @param {String} [progressText=''] The progress text to display inside the progress bar.
	 * @param {String} [msg] The message box's body text is replaced with the specified string (defaults to undefined
	 * so that any existing body text will not get overwritten by default unless a new value is passed in)
	 * @return {Ext6.window.MessageBox} this
	 */
	updateProgress : function(value, progressText, msg){
		this.progressBar.updateProgress(value, progressText);
		if (msg){
			this.updateText(msg);
		}
		return this;
	},

	onEsc: function() {
		if (this.closable !== false) {
			this.callParent(arguments);
		}
	},

	/**
	 * Displays a confirmation message box with Yes and No buttons (comparable to JavaScript's confirm).
	 * If a callback function is passed it will be called after the user clicks either button,
	 * and the id of the button that was clicked will be passed as the only parameter to the callback
	 * (could also be the top-right close button, which will always report as "cancel").
	 *
	 * @param {String} title The title bar text
	 * @param {String} msg The message box body text
	 * @param {Function} [fn] The callback function invoked after the message box is closed.
	 * See {@link #method-show} method for details.
	 * @param {Object} [scope=window] The scope (`this` reference) in which the callback is executed.
	 * @return {Ext6.window.MessageBox} this
	 */
	confirm: function(cfg, msg, fn, scope) {
		if (Ext6.isString(cfg)) {
			cfg = {
				title: cfg,
				icon: this.QUESTION,
				msg: msg,
				buttons: this.YESNO,
				callback: fn,
				scope: scope
			};
		}
		return this.show(cfg);
	},

	/**
	 * Displays a message box with OK and Cancel buttons prompting the user to enter some text (comparable to JavaScript's prompt).
	 * The prompt can be a single-line or multi-line textbox.  If a callback function is passed it will be called after the user
	 * clicks either button, and the id of the button that was clicked (could also be the top-right
	 * close button, which will always report as "cancel") and the text that was entered will be passed as the two parameters to the callback.
	 *
	 * @param {String} title The title bar text
	 * @param {String} msg The message box body text
	 * @param {Function} [fn] The callback function invoked after the message box is closed.
	 * See {@link #method-show} method for details.
	 * @param {Object} [scope=window] The scope (`this` reference) in which the callback is executed.
	 * @param {Boolean/Number} [multiline=false] True to create a multiline textbox using the defaultTextHeight
	 * property, or the height in pixels to create the textbox/
	 * @param {String} [value=''] Default value of the text input element
	 * @return {Ext6.window.MessageBox} this
	 */
	prompt : function(cfg, msg, fn, scope, multiline, value){
		if (Ext6.isString(cfg)) {
			cfg = {
				prompt: true,
				title: cfg,
				minWidth: this.minPromptWidth,
				msg: msg,
				buttons: this.OKCANCEL,
				callback: fn,
				scope: scope,
				multiline: multiline,
				value: value
			};
		}
		return this.show(cfg);
	},
	promptText : function(cfg, fieldLabel, fn, widthField, value){
		if (Ext6.isString(cfg)) {
			cfg = {
				width: 450,
				bodyPadding: 30,
				prompt: true,
				title: cfg,
				fieldLabel: fieldLabel,
				widthField: widthField || this.minPromptWidth,
				buttons: this.OKCANCEL,
				callback: fn,
				titleHard: true,
				hardHideIcon: true,
				value: value
			};
		}
		return this.show(cfg);
	},


	/**
	 * Displays a message box with an infinitely auto-updating progress bar.  This can be used to block user
	 * interaction while waiting for a long-running process to complete that does not have defined intervals.
	 * You are responsible for closing the message box when the process is complete.
	 *
	 * @param {String} msg The message box body text
	 * @param {String} [title] The title bar text
	 * @param {Object} [config] A {@link Ext6.ProgressBar#wait} config object
	 * @return {Ext6.window.MessageBox} this
	 */
	wait : function(cfg, title, config){
		if (Ext6.isString(cfg)) {
			cfg = {
				title : title,
				msg : cfg,
				closable: false,
				wait: true,
				modal: true,
				minWidth: this.minProgressWidth,
				waitConfig: config
			};
		}
		return this.show(cfg);
	},

	/**
	 * Displays a standard read-only message box with an OK button (comparable to the basic JavaScript alert prompt).
	 * If a callback function is passed it will be called after the user clicks the button, and the
	 * id of the button that was clicked will be passed as the only parameter to the callback
	 * (could also be the top-right close button, which will always report as "cancel").
	 *
	 * @param {String} title The title bar text
	 * @param {String} msg The message box body text
	 * @param {Function} [fn] The callback function invoked after the message box is closed.
	 * See {@link #method-show} method for details.
	 * @param {Object} [scope=window] The scope (<code>this</code> reference) in which the callback is executed.
	 * @return {Ext6.window.MessageBox} this
	 */
	alert: function(cfg, msg, fn, scope) {
		if (Ext6.isString(cfg)) {
			cfg = {
				title : cfg,
				msg : msg,
				buttons: this.OK,
				fn: fn,
				scope : scope,
				minWidth: this.minWidth
			};
		}
		return this.show(cfg);
	},

	/**
	 * Displays a message box with a progress bar.
	 *
	 * You are responsible for updating the progress bar as needed via {@link Ext6.window.MessageBox#updateProgress}
	 * and closing the message box when the process is complete.
	 *
	 * @param {String} title The title bar text
	 * @param {String} msg The message box body text
	 * @param {String} [progressText=''] The text to display inside the progress bar
	 * @return {Ext6.window.MessageBox} this
	 */
	progress : function(cfg, msg, progressText){
		if (Ext6.isString(cfg)) {
			cfg = {
				title: cfg,
				msg: msg,
				progress: true,
				progressText: progressText
			};
		}
		return this.show(cfg);
	}
}, function() {
	/**
	 * @class sw.swMessageBox
	 * @alternateClassName Ext6.Msg
	 * @extends Ext6.window.MessageBox
	 * @singleton
	 * Окошко/Алерт для ExtJs 6, показывается без header-а по гайдам от дизайнера
	 * С ExtJs 6 кнопки лучше вызывать числом от 1 до 15, либо таким образом buttons: sw.swMsg.YESNO (но это все-равно число)
	 * Число формируется из суммы чисел минимального количества кнопок
	 * числа кнопок: OK = 1
	 * YES = 2
	 * NO = 4
	 * CANCEL = 8
	 * Вывод: если нужны две кнопки, например, CANCEL и NO = 8 + 4 = 12, писать в конфигах нужно buttons: 12,
	 * если нужны две кнопки YES и CANCEL = 2 + 8 = 10, писать в конфигах нужно buttons: 10,
	 * OK и NO = 1 + 4 = 5 buttons: 5, и т.д.
	 * Если на новом эксте заголовок нужно показать, не смотря на отсутствие его по гайдам, добавляем в конфиг titleHard = true
	 * Чтобы изменить текст у кнопок, нужно задать параметр buttonText : {
	 *			ok: "OK",
	 *			cancel: "Отмена",
	 *			yes: "Да",
	 *			no: "Нет",
	 *			refresh: "Обновить",
	 *			close: "Закрыть"
	 *		}, в таком виде
	 *		 либо вызывать как из ExtJs 2
	 *		 buttons: {
	 *						yes: {
	 *							text: langs('Исключить из очереди'),
	 *							iconCls: 'delete16',
	 *							tooltip: langs('Пациент будет исключен из очереди и записан на выбранную бирку')
	 *						},
	 *						cancel: {
	 *							text: langs('Отмена'),
	 *							iconCls: 'close16',
	 *							tooltip: langs('Отмена записи пациента, без исключения его из очереди')
	 *						}
	 *					}
	 *
	 * Singleton instance of {@link Ext6.window.MessageBox}.
	 */
	sw.swMessageBox = sw.swMsg = new this();
});










/*

sw.swMessageBox = function(){
	var dlg, opt, mask, waitTimer;
	var bodyEl, msgEl, textboxEl, textareaEl, progressBar, pp, iconEl, spacerEl;
	var buttons, activeTextEl, bwidth, iconCls = '';

	// private
	var handleButton = function(button){
		if(dlg.isVisible()){
			dlg.hide();
			Ext6.callback(opt.fn, opt.scope||window, [button, activeTextEl.dom.value, opt], 1);
		}
	};

	// private
	var handleHide = function(){
		if(opt && opt.cls){
			dlg.el.removeCls(opt.cls);
		}
		//progressBar.reset();
	};

	// private
	var handleEsc = function(d, k, e){
		if(opt && opt.closable !== false){
			dlg.hide();
		}
		if(e){
			e.stopEvent();
		}
	};

	// private
	var updateButtons = function(b){
		var width = 0;
		if(!b){
			buttons["ok"].hide();
			buttons["cancel"].hide();
			buttons["yes"].hide();
			buttons["no"].hide();
			buttons["refresh"].hide();
			buttons["close"].hide();
			return width;
		}
//		console.log({'dlg.footer':dlg.footer});
//		dlg.footer.dom.style.display = '';
		for(var k in buttons){
			if(typeof buttons[k] != "function"){
				if(b[k]){
					buttons[k].show();
					buttons[k].setText(typeof b[k] == "string" ? b[k] : sw.swMessageBox.buttonText[k]);
					width += buttons[k].el.getWidth()+15;
				}else{
					buttons[k].hide();
				}
			}
		}
		return width;
	};

	return {

		getDialog : function(titleText){
			if(!dlg){
				dlg = Ext6.create('Ext6.window.Window',{
					autoCreate : true,
					title:titleText,
					resizable:false,
					constrain:true,
					constrainHeader:true,
					minimizable : false,
					maximizable : false,
					stateful: false,
					modal: true,
					shim:true,
					buttonAlign:"center",
					width:400,
					height:100,
					minHeight: 80,
					plain:true,
					footer:true,
					closable:true,
					close : function(){
						if(opt && opt.buttons && opt.buttons.no && !opt.buttons.cancel){
							handleButton("no");
						}else{
							handleButton("cancel");
						}
					},
					keys: [{
						key:[
							Ext6.EventObjectImpl.TAB,
							Ext6.EventObjectImpl.LEFT,
							Ext6.EventObjectImpl.RIGHT
							],
						fn: function() {
							if (dlg.focusEl==dlg.buttons[2]) {
								dlg.buttons[1].focus();
								dlg.focusEl=dlg.buttons[1];
							}
							else
								if (dlg.focusEl==dlg.buttons[1]) {
									dlg.buttons[2].focus();
									dlg.focusEl=dlg.buttons[2];
								}
						},
						stopEvent: true
					}]
				});
				buttons = {};
				var bt = this.buttonText;
				//TODO: refactor this block into a buttons config to pass into the Window constructor
				
				buttons["ok"] = dlg.add(Ext6.create('Ext6.Button', {text: bt["ok"], handler: Ext6.Function.pass(handleButton, ["ok"], this)}));
				buttons["yes"] = dlg.add(Ext6.create('Ext6.Button', {text: bt["yes"], handler: Ext6.Function.pass(handleButton, ["yes"], this)}));
				buttons["no"] = dlg.add(Ext6.create('Ext6.Button', {text: bt["no"], handler: Ext6.Function.pass(handleButton, ["no"], this)}));
				buttons["cancel"] = dlg.add(Ext6.create('Ext6.Button', {text: bt["cancel"], handler: Ext6.Function.pass(handleButton, ["cancel"], this)}));
				buttons["refresh"] = dlg.add(Ext6.create('Ext6.Button', {text: bt["refresh"], handler: Ext6.Function.pass(handleButton, ["refresh"], this)}));
				buttons["close"] = dlg.add(Ext6.create('Ext6.Button', {text: bt["close"], handler: Ext6.Function.pass(handleButton, ["close"], this)}));
				
//				buttons["ok"] = dlg.addButton(bt["ok"], Ext6.Function.pass(handleButton, ["ok"], this));
//				buttons["yes"] = dlg.addButton(bt["yes"], Ext6.Function.pass(handleButton, ["yes"], this));
//				buttons["no"] = dlg.addButton(bt["no"], Ext6.Function.pass(handleButton, ["no"], this));
//				buttons["cancel"] = dlg.addButton(bt["cancel"], Ext6.Function.pass(handleButton, ["cancel"], this));
//				buttons["refresh"] = dlg.addButton(bt["refresh"], Ext6.Function.pass(handleButton, ["refresh"], this));
//				buttons["close"] = dlg.addButton(bt["close"], Ext6.Function.pass(handleButton, ["close"], this))  ;
				buttons["ok"].hideMode = buttons["yes"].hideMode = buttons["no"].hideMode = buttons["cancel"].hideMode = 'offsets';
				dlg.render(document.body);
				dlg.getEl().addCls('x-window-dlg');
				mask = dlg.mask;
				bodyEl = dlg.body.createChild({
					html:'<div class="ext-mb-icon"></div><div class="ext-mb-content"><span class="ext-mb-text"></span><br /><div class="ext-mb-fix-cursor"><input type="text" class="ext-mb-input" /><textarea class="ext-mb-textarea"></textarea></div></div>'
				});
				iconEl = Ext6.get(bodyEl.dom.firstChild);
				var contentEl = bodyEl.dom.childNodes[1];
				msgEl = Ext6.get(contentEl.firstChild);
				textboxEl = Ext6.get(contentEl.childNodes[2].firstChild);
				textboxEl.enableDisplayMode();
				textboxEl.addKeyListener([10,13], function(){
					if(dlg.isVisible() && opt && opt.buttons){
						if(opt.buttons.ok){
							handleButton("ok");
						}else if(opt.buttons.yes){
							handleButton("yes");
						}
					}
				});
				textareaEl = Ext6.get(contentEl.childNodes[2].childNodes[1]);
				textareaEl.enableDisplayMode();
				progressBar = Ext6.create('Ext6.ProgressBar',{
					renderTo:bodyEl
				});
				bodyEl.createChild({cls:'x-clear'});
			}
			return dlg;
		},


		updateText : function(text){
			if(!dlg.isVisible() && !opt.width){
				dlg.setSize(this.maxWidth, 100); // resize first so content is never clipped from previous shows
			}
			msgEl.update(text || '&#160;');

			var iw = iconCls != '' ? (iconEl.getWidth() + iconEl.getMargin('lr')) : 0;
			var mw = msgEl.getWidth() + msgEl.getMargin('lr');
			var fw = dlg.getWidth();
			var bw = dlg.body.getWidth('lr');
			if (Ext6.isEdge && iw > 0){
				//3 pixels get subtracted in the icon CSS for an IE margin issue,
				//so we have to add it back here for the overall width to be consistent
				iw += 3;
			}
			var w = Math.max(Math.min(opt.width || iw+mw+fw+bw, this.maxWidth),
						Math.max(opt.minWidth || this.minWidth, bwidth || 0));

			if(opt.prompt === true){
				activeTextEl.setWidth(w-iw-fw-bw);
			}
			if(opt.progress === true || opt.wait === true){
				progressBar.setSize(w-iw-fw-bw);
			}
			if(Ext6.isIE && w == bwidth){
				w += 4; //Add offset when the content width is smaller than the buttons.
			}
			//dlg.setSize(w, 'auto').center();
			return this;
		},


		updateProgress : function(value, progressText, msg){
			progressBar.updateProgress(value, progressText);
			if(msg){
				this.updateText(msg);
			}
			return this;
		},


		isVisible : function(){
			return dlg && dlg.isVisible();
		},


		hide : function(){
			var proxy = dlg.activeGhost;
			if(this.isVisible() || proxy) {
				dlg.hide();
				handleHide();
				if (proxy) {
					proxy.hide();
				}
			}
			return this;
		},


		show : function(options){
			if(this.isVisible()){
				this.hide();
			}
			opt = options;
			var d = this.getDialog(opt.title || "&#160;");

			d.setTitle(opt.title || "&#160;");
			var allowClose = (opt.closable !== false && opt.progress !== true && opt.wait !== true);
			d.tools.close.setVisible(allowClose);
			activeTextEl = textboxEl;
			opt.prompt = opt.prompt || (opt.multiline ? true : false);
			if(opt.prompt){
				if(opt.multiline){
					textboxEl.hide();
					textareaEl.show();
					textareaEl.setHeight(typeof opt.multiline == "number" ?
						opt.multiline : this.defaultTextHeight);
					activeTextEl = textareaEl;
				}else{
					textboxEl.show();
					textareaEl.hide();
				}
			}else{
				textboxEl.hide();
				textareaEl.hide();
			}
			activeTextEl.dom.value = opt.value || "";
			if(opt.prompt){
				d.focusEl = activeTextEl;
			}else{
				var bs = opt.buttons;
				var db = null;
				if(bs && bs.ok){
					db = buttons["ok"];
				}else if(bs && bs.yes){
					db = buttons["yes"];
				}
				if (db){
					d.focusEl = db;
				}
			}
			if(opt.iconCls){
				d.setIconClass(opt.iconCls);
			}
			this.setIcon(opt.icon);
			bwidth = updateButtons(opt.buttons);
			//progressBar.setVisible(opt.progress === true || opt.wait === true);
			//this.updateProgress(0, opt.progressText);
			this.updateText(opt.msg);
			if(opt.cls){
				d.el.addCls(opt.cls);
			}
			d.proxyDrag = opt.proxyDrag === true;
			d.modal = opt.modal !== false;
			d.mask = opt.modal !== false ? mask : false;
			if(!d.isVisible()){
				// force it to the end of the z-index stack so it gets a cursor in FF
				document.body.appendChild(dlg.el.dom);
//				d.setAnimateTarget(opt.animEl);
				d.show(opt.animEl);
			}

			//workaround for window internally enabling keymap in afterShow
			d.on('show', function(){
				if(allowClose === true){
					d.keyMap.enable();
				}else{
					d.keyMap.disable();
				}
			}, this, {single:true});

			//workaround for window internally enabling keymap in afterShow
			if(opt.wait === true){
				progressBar.wait(opt.waitConfig);
			}
			return this;
		},


		setIcon : function(icon){
			if(icon && icon != ''){
				iconEl.removeCls('x-hidden');
				iconEl.replaceCls(iconCls, icon);
				iconCls = icon;
			}else{
				iconEl.replaceCls(iconCls, 'x-hidden');
				iconCls = '';
			}
			return this;
		},


		progress : function(title, msg, progressText){
			this.show({
				title : title,
				msg : msg,
				buttons: false,
				progress:true,
				closable:false,
				minWidth: this.minProgressWidth,
				progressText: progressText
			});
			return this;
		},


		wait : function(msg, title, config){
			this.show({
				title : title,
				msg : msg,
				buttons: false,
				closable:false,
				wait:true,
				modal:true,
				minWidth: this.minProgressWidth,
				waitConfig: config
			});
			return this;
		},


		alert : function(title, msg, fn, scope){
			this.show({
				title : title,
				msg : msg,
				buttons: this.OK,
				fn: fn,
				scope : scope
			});
			return this;
		},


		confirm : function(title, msg, fn, scope){
			this.show({
				title : title,
				msg : msg,
				buttons: this.YESNO,
				fn: fn,
				scope : scope,
				icon: this.QUESTION
			});
			return this;
		},


		prompt : function(title, msg, fn, scope, multiline, value){
			this.show({
				title : title,
				msg : msg,
				buttons: this.OKCANCEL,
				fn: fn,
				minWidth:250,
				scope : scope,
				prompt:true,
				multiline: multiline,
				value: value
			});
			return this;
		},


		OK : {ok:true},

		CANCEL : {cancel:true},

		OKCANCEL : {ok:true, cancel:true},

		YESNO : {yes:true, no:true},

		YESNOCANCEL : {yes:true, no:true, cancel:true},
		
		REFRESHCLOSE : {refresh:true, close:true},

		INFO : 'ext-mb-info',

		WARNING : 'ext-mb-warning',

		QUESTION : 'ext-mb-question',

		ERROR : 'ext-mb-error',


		defaultTextHeight : 75,

		maxWidth : 600,

		minWidth : 100,

		minProgressWidth : 250,

		buttonText : {
			ok : "OK",
			cancel : "Отмена",
			yes : "Да",
			no : "Нет",
			refresh: "Обновить",
			close: "Закрыть"
		}
	};
}();


sw.swMsg = sw.swMessageBox */