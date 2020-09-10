tinyMCE.helpers = {
	canSplitBlock: function(dom, node) {
		return node &&
			dom.isBlock(node) &&
			!/^(TD|TH|CAPTION|FORM)$/.test(node.nodeName) &&
			!/^(fixed|absolute)/i.test(node.style.position) &&
			dom.getContentEditable(node) !== 'true';
	},

	isTableCell: function (node) {
		return node && /^(TD|TH|CAPTION)$/.test(node.nodeName);
	},

	getEditableRoot: function(dom, node) {
		var root = dom.getRoot();
		var parent, editableRoot;

		// Get all parents until we hit a non editable parent or the root
		parent = node;
		while (parent !== root && dom.getContentEditable(parent) !== 'false') {
			if (dom.getContentEditable(parent) === 'true') {
				editableRoot = parent;
			}

			parent = parent.parentNode;
		}

		return parent !== root ? editableRoot : root;
	},

	wrapSelfAndSiblingsInParagraph: function(editor) {
		var helpers = tinyMCE.helpers;
		var rng = editor.selection.getRng();
		var container = rng.startContainer;
		var offset = rng.startOffset;

		var newBlock, parentBlock, startNode, node, next, rootBlockName;
		var blockName = 'P';
		var dom = editor.dom, editableRoot = helpers.getEditableRoot(dom, container);

		// Not in a block element or in a table cell or caption
		parentBlock = dom.getParent(container, dom.isBlock);
		if (!parentBlock || !helpers.canSplitBlock(dom, parentBlock)) {
			parentBlock = parentBlock || editableRoot;

			if (parentBlock === editor.getBody() || helpers.isTableCell(parentBlock)) {
				rootBlockName = parentBlock.nodeName.toLowerCase();
			} else {
				rootBlockName = parentBlock.parentNode.nodeName.toLowerCase();
			}

			if (!parentBlock.hasChildNodes()) {
				newBlock = dom.create(blockName);
				parentBlock.appendChild(newBlock);
				rng.setStart(newBlock, 0);
				rng.setEnd(newBlock, 0);
				return newBlock;
			}

			// Find parent that is the first child of parentBlock
			node = container;
			while (node.parentNode !== parentBlock) {
				node = node.parentNode;
			}

			// Loop left to find start node start wrapping at
			while (node && !dom.isBlock(node)) {
				startNode = node;
				node = node.previousSibling;
			}

			if (startNode && editor.schema.isValidChild(rootBlockName, blockName.toLowerCase())) {
				newBlock = dom.create(blockName);
				startNode.parentNode.insertBefore(newBlock, startNode);

				// Start wrapping until we hit a block
				node = startNode;
				while (node && !dom.isBlock(node)) {
					next = node.nextSibling;
					newBlock.appendChild(node);
					node = next;
				}

				// Restore range to it's past location
				rng.setStart(container, offset);
				rng.setEnd(container, offset);
			}
		}

		return container;
	},

	wrapElInParagraph: function(editor, el) {
		var helpers = tinyMCE.helpers;
		var container = el;

		var newBlock, parentBlock, startNode, node, next, rootBlockName;
		var blockName = 'P';
		var dom = editor.dom, editableRoot = helpers.getEditableRoot(dom, container);

		// Not in a block element or in a table cell or caption
		parentBlock = dom.getParent(container, dom.isBlock);

		if (parentBlock === editor.getBody() || helpers.isTableCell(parentBlock)) {
			rootBlockName = parentBlock.nodeName.toLowerCase();
		} else {
			rootBlockName = parentBlock.parentNode.nodeName.toLowerCase();
		}

		if (!parentBlock.hasChildNodes()) {
			newBlock = dom.create(blockName);
			parentBlock.appendChild(newBlock);
			return newBlock;
		}

		// Find parent that is the first child of parentBlock
		node = container;
		while (node.parentNode !== parentBlock) {
			node = node.parentNode;
		}

		// Loop left to find start node start wrapping at
		while (node && !dom.isBlock(node)) {
			startNode = node;
			node = node.previousSibling;
		}

		if (startNode && editor.schema.isValidChild(rootBlockName, blockName.toLowerCase())) {
			newBlock = dom.create(blockName);
			startNode.parentNode.insertBefore(newBlock, startNode);

			// Start wrapping until we hit a block
			node = startNode;
			while (node && !dom.isBlock(node)) {
				next = node.nextSibling;
				newBlock.appendChild(node);
				node = next;
			}
		}

		return container;
	}
};

Ext6.define('base.EditorPanel', {
	extend: 'Ext6.panel.Panel',
	alias: 'widget.doceditor',
	requires: [
		'base.EditorTableBlock'
	],

	cls: 'sw-editor-container',
	border: false,
	autoHeight: false,
	scrollable: true,
	toolbarSticky: false,

	originalContent: '',
	mce: null,

	fontSize: {
		unit: 'pt',
		defaultValue: 10,
		options: [8, 10, 12, 14, 18, 24, 36]
	},

	disableStandartUndoManager: false,

	toolbarCfg: [
		'undo redo | paste | insertobject | fontsize | bold italic underline strikethrough | subscript superscript | list indent align | html print | -> save'
	],

	toolbarMaximizedCfg: null,

	blocks: [],

	paste: function(clearFormat) {
		var me = this;
		//todo: clearFormat
		me.execCommand('paste');
	},

	undo: function() {
		var me = this;

		me.getUndoManager().undo();
		me.setCursorLocation();
	},

	redo: function() {
		var me = this;

		me.getUndoManager().redo();
		me.setCursorLocation();
	},

	toggleHtml: function() {
		var me = this;
		var btn = me.getToolbarButton('html');

		if (me.viewHtml) {
			me.viewHtml = false;
			me.getHtmlViewerEl().hide();
			me.getHtmlViewerEl().setHtml('');
			me.getEditorEl().show();
			me.refreshToolbarButtons();
			btn.setTooltip('Отключить отображение в виде HTML-кода');
		} else {
			me.viewHtml = true;
			var html = htmlentities(me.getContentForHtmlView())
				.split("\n")
				.map(function(str){return '<p>'+str+'</p>'})
				.join('');

			me.getEditorEl().hide();
			me.getHtmlViewerEl().setHtml(html);
			me.getHtmlViewerEl().show();
			me.refreshToolbarButtons();
			btn.setTooltip('Отобразить в виде HTML-кода');
		}

		me.refreshSize();
	},

	insertTable: function(size) {
		var me = this;
		var blocks = base.EditorTableBlock.insertToEditor(me, size);
	},

	printDocument: function() {

	},

	saveDocument: function(saveAs, autoSave) {

	},

	getEditorWrapOuterEl: function() {
		return Ext6.get(this.getId()+'-editor-wrap-outer');
	},

	getEditorWrapEl: function() {
		return Ext6.get(this.getId()+'-editor-wrap');
	},

	getEditorEl: function() {
		return Ext6.get(this.getId()+'-editor');
	},

	getNoticeContainerEl: function() {
		return Ext6.get(this.getId()+'-notice-container');
	},

	getHtmlViewerEl: function() {
		return Ext6.get(this.getId()+'-html-viewer');
	},

	getToolbar: function() {
		return this.toolbar;
	},

	getScrollableParent: function(el) {
		var me = this;
		var parent = el.parent();

		if (!parent) {
			return null;
		}
		if (parent.isScrollable() && parent.getHeight() > 1 && !(parent == me.el && me.autoHeight)) {
			return parent;
		}
		return me.getScrollableParent(parent);
	},

	observeScrollForToolbar: function(e) {
		var me = this;
		var el = me.toolbar.getEl();
		var wrapEl = me.bodyWrap;
		var prevDiffY = el.diffY || 0;
		el.diffY = null;

		if (!me.scrollableForToolbar || me.resetObserveScrollForToolbar || el.isMasked()) {
			me.resetObserveScrollForToolbar = false;
			el.setStyle('transform', null);
			return;
		}

		var elCoords = {
			left: el.getLeft(),
			top: el.getTop() - prevDiffY,
			bottom: el.getBottom() - prevDiffY
		};
		var scrollableCoords = {
			left: me.scrollableForToolbar.getLeft(),
			top: me.scrollableForToolbar.getTop()
		};
		var limitCoords = {
			top: me.el.getTop(),
			bottom: me.el.getBottom()
		};

		var fromPointEl = Ext6.Element.fromPoint(elCoords.left, elCoords.top);

		if (!fromPointEl || (fromPointEl != el && fromPointEl != wrapEl && !fromPointEl.hasCls('x6-mask'))) {
			el.diffY = scrollableCoords.top - elCoords.top;

			if (elCoords.top + el.diffY < limitCoords.top) {
				el.diffY = limitCoords.top - elCoords.top;
			}
			if (elCoords.bottom + el.diffY > limitCoords.bottom) {
				el.diffY = limitCoords.bottom - elCoords.bottom;
			}
		}

		if (el.diff != prevDiffY) {
			var transform = el.diffY?'translateY('+el.diffY+'px)':null;
			el.setStyle('transform', transform);
		}
	},

	refreshObserveScrollForToolbar: function() {
		var me = this;

		if (me.scrollableForToolbar) {
			me.scrollableForToolbar.un('scroll', me.observeScrollForToolbar, me);
			me.scrollableForToolbar = null;
		}

		if (me.toolbarSticky) {
			me.scrollableForToolbar = me.getScrollableParent(me.toolbar.el);

			if (me.scrollableForToolbar) {
				me.resetObserveScrollForToolbar = true;
				me.scrollableForToolbar.on('scroll', me.observeScrollForToolbar, me);
				me.observeScrollForToolbar();
			}
		}
	},

	setReadOnly: function(isReadOnly) {
		var me = this;

		me.isReadOnly = isReadOnly;

		if (me.isReadOnly) {
			me.mce.setMode('readonly');
		} else {
			me.mce.setMode('design');
		}

		me.refreshToolbarButtons();

		me.blocks.forEach(function(block) {
			block.setReadOnly(isReadOnly);
		});

		var maximize = me.getToolbarButton('togglemaximize');
		if(maximize != null) {
			maximize.setDisabled(me.isReadOnly);
		}
	},

	setCursorLocation: function(node, position) {
		var me = this;

		me.mce.selection.setCursorLocation(node, position);
	},

	setCursorLocationByEl: function(el) {
		var me = this;
		if (el instanceof HTMLElement) {
			el = Ext6.fly(el);
		}
		if (el instanceof Ext6.Element) {
			var parent = el.parent();
			me.setCursorLocation(parent.dom, parent.indexOf(el));
		}
	},

	getElConfig: function() {
		var me = this;

		var wrapClasses = ['sw-editor-wrap'];
		if (!me.autoHeight) {
			wrapClasses.push('sw-editor-wrap-full');
		}

		var tpl = new Ext6.Template(
			'<div id="{id}-editor-wrap-outer" class="sw-editor-wrap-outer">',
				'<div id="{id}-editor-wrap" class="{wrapClasses}">',
					'<div id="{id}-editor-wrap-inner" class="sw-editor-wrap-inner">',
						'<div id="{id}-notice-container-wrap" class="sw-editor-notice-container-wrap"></div>',
						'<div id="{id}-editor" class="sw-editor"></div>',
						'<div id="{id}-html-viewer" class="sw-editor"></div>',
					'</div>',
				'</div>',
			'</div>'
		);
		var tplData = {
			id: me.getId(),
			wrapClasses: wrapClasses.join(' ')
		};

		me.html = tpl.apply(tplData);
		return me.callParent();
	},

	getToolbarButton: function(name) {
		var me = this;
		name = String(name).toLowerCase();
		return me.toolbarButtons[name] || null;
	},

	refreshSize: function() {
		var me = this;

		var el = me.getEl();
		var toolbarEl = me.getToolbar().getEl();
		var wrapEl = me.getEditorWrapEl();

		if (el && toolbarEl && wrapEl) {
			var height = wrapEl.getHeight() + toolbarEl.getHeight() + 1;
			if (el.getHeight() != height) me.updateLayout();
		}
	},

	getElFontSize: function(el, resultUnit) {
		var me = this;
		var fontSize = el.getStyle('font-size');
		var match = /^(\d+\.?\d*)(\D+)$/.exec(fontSize);
		var result = null;

		if (match) {
			var value = match[1];
			var unit = match[2];

			if (unit == resultUnit) {
				result = value;
			} else {
				var map = {px: 1, pt: 0.75};
				var coeff1 = map[unit];
				var coeff2 = map[resultUnit];
				result = value*coeff2/coeff1;
			}
		}

		return result?Math.round(result):me.fontSize.defaultValue;
	},

	getSelectionProperties: function() {
		var me = this;
		var tmpEl;

		var rules = {
			fontSize: function(list) {
				var sizeList = list.map(function(el) {
					return me.getElFontSize(el, me.fontSize.unit);
				});
				return Math.max.apply(null, sizeList)+' '+me.fontSize.unit;
			},
			bold: function(list) {
				return list.some(function(el) {
					return el.is('strong') || el.getStyle('font-weight').inlist(['bold','700']);
				});
			},
			italic: function(list) {
				return list.some(function(el) {
					return el.getStyle('font-style') == 'italic';
				});
			},
			underline: function(list) {
				list.forEach(function(el) {
					tmpEl = el.up('span[style*=underline]');
					if (tmpEl) list.push(tmpEl);
				});
				return list.some(function(el) {
					return el.getStyle('text-decoration') == 'underline';
				});
			},
			strikethrough: function(list) {
				list.forEach(function(el) {
					tmpEl = el.up('span[style*=line-through]');
					if (tmpEl) list.push(tmpEl);
				});
				return list.some(function(el) {
					return el.getStyle('text-decoration') == 'line-through';
				});
			},
			subscript: function(list) {
				list.forEach(function(el) {
					tmpEl = el.up('sub');
					if (tmpEl) list.push(tmpEl);
				});
				return list.some(function(el) {
					return el.is('sub');
				});
			},
			superscript: function(list) {
				list.forEach(function(el) {
					tmpEl = el.up('sup');
					if (tmpEl) list.push(tmpEl);
				});
				return list.some(function(el) {
					return el.is('sup');
				});
			}
		};

		var list = me.mce.selection?[
			Ext6.fly(me.mce.selection.getStart()),
			Ext6.fly(me.mce.selection.getEnd())
		]:false;

		var properties = {};

		Object.keys(rules).forEach(function(name) {
			if (list) {
				properties[name] = rules[name]([].concat(list));
			} else if (name == 'fontSize') {
				properties[name] = me.fontSize.defaultValue;
			} else {
				properties[name] = false;
			}
		});

		return properties;
	},

	isContentChanged: function() {
		var me = this;
		return me.originalContent != me.getContent();
	},

	hideFakeCaret: function() {
		var me = this;
		if (!me.mce) return;
		me.mce._selectionOverrides.hideFakeCaret();
	},

	onSelectionChange: function(e) {
		var me = this;
		if (me.toolbar.destroyed) return;

		var properties = me.getSelectionProperties();

		me.getToolbarButton('fontsize').setText(properties.fontSize);
		me.getToolbarButton('bold').setPressed(properties.bold);
		me.getToolbarButton('italic').setPressed(properties.italic);
		me.getToolbarButton('underline').setPressed(properties.underline);
		me.getToolbarButton('strikethrough').setPressed(properties.strikethrough);
		me.getToolbarButton('subscript').setPressed(properties.subscript);
		me.getToolbarButton('superscript').setPressed(properties.superscript);

		var el = Ext6.fly(e.target);
		if (el.up('.editor-table-block')) {
			var tableBlock = Ext6.getCmp(el.up('.editor-table-block').getId());

			if (el.dom.tagName == 'TR') {
				me.setCursorLocationByEl(el.down('td'));
			}
			if (tableBlock && tableBlock.toolsPanel.isHidden()) {
				tableBlock.showToolsPanel();
			}
		}
		if (el.up('.editor-block')) {
			var block = Ext6.getCmp(el.up('.editor-block').getId());

			if (block && block.toolsPanel) {
				block.refreshToolsPanelVisibility(true);
			}
		}
	},

	onContentChange: function(e) {
		var me = this;
		var editorEl = me.getEditorEl();
		if (me.blocksRendering || me.toolbar.destroyed) {
			return;
		}

		if (me.needResetState) me.resetState();

		var isChanged = me.isContentChanged();

		if (isChanged && Ext6.isEmpty(me.getContent())) {
			me.setContent('<br data-mce-bogus="1">', {addUndoLevel: false});
		}

		me.refreshToolbarButtons(['undo','redo']);
		me.getToolbarButton('save').setIconCls(isChanged?'icon-save-active':'icon-save');
		me.getToolbarButton('savemenu').setIconCls(isChanged?'icon-save-active':'icon-save');

		me.blocks = me.blocks.filter(function(block) {
			return editorEl.contains(block.el);
		});

		me.refreshNotice();
		me.refreshSize();
	},

	onNodeChange: function(e) {
		var me = this;

		if (e.type == 'nodechange' && e.target.getAttribute('data-mce-caret') == 'after') {
			e.stopEvent();
			me.hideFakeCaret();
			return;
		}

		me.onSelectionChange(e);
		me.onContentChange(e);
	},

	onEditorBlur: function(e) {

	},

	onSetContent: function(e) {

	},

	setContent: function(content, options) {
		var me = this;

		options = Ext6.apply({}, options, {
			addUndoLevel: true,
			resetState: false
		});

		if (me.rendered && me.mce && me.getUndoManager()) {
			if (options.resetState) {
				me.needResetState = true;
			}

			var addUndoLevel = (options.addUndoLevel && !options.resetState);
			var insertContent = function() {
				me.mce.setContent(content || '', {format: 'raw'});
				//me.setCursorLocation();
			};

			if (me.getUndoManager()) {
				if (addUndoLevel) {
					me.getUndoManager().transact(insertContent);
				} else {
					me.getUndoManager().ignore(insertContent);
				}
			}
		}
	},

	getContent: function(fromHtml) {
		var me = this;
		var content = '';
		var re = /( id="ext-element-\d+")/g;

		if (fromHtml && !Ext6.isEmpty(me.getEditorEl())) {
			content = me.getEditorEl().getHtml();
		} else if (me.mce) {
			content = me.mce.getContent({format: 'raw'});
		}

		return content.replace(re, '');
	},

	getContentForHtmlView: function() {
		return this.getContent();
	},

	getUndoManager: function() {
		return this.undoManager;
	},

	setUndoManager: function(undoManager) {
		var me = this;
		if (me.mce.undoManager) {
			if (undoManager != me.mce.undoManager) {
				me.originalAddUndoLevel = me.mce.undoManager.add;
				me.mce.undoManager.add = Ext6.emptyFn;
			} else if (me.originalAddUndoLevel) {
				me.mce.undoManager.add = me.originalAddUndoLevel;
			}
		}
		me.undoManager = undoManager;
	},

	resetState: function() {
		var me = this;
		me.needResetState = false;
		me.originalContent = me.getContent();
		me.getUndoManager().clear();
		me.getUndoManager().add();
	},

	reset: function() {
		var me = this;
		var undoManager = me.getUndoManager();

		if (me.mce) {
			me.needResetState = false;
			me.setContent('');
			me.originalContent = me.getContent();
			me.blocks = [];
			if (undoManager) {
				undoManager.clear();
				undoManager.add();
			}
		}
	},

	beforeDestroy: function() {
		var me = this;
		if (me.mce) {
			me.mce.remove();
			me.mce = null;
		}
	},

	onRender: function() {
		var me = this;
		me.callParent(arguments);

		me.getEditorEl().setVisibilityMode(Ext6.Element.DISPLAY);
		me.getHtmlViewerEl().setVisibilityMode(Ext6.Element.DISPLAY);
		me.getHtmlViewerEl().hide();

		if (me.mce) me.mce.render();
	},

	afterRender: function() {
		var me = this;
		me.callParent(arguments);
		me.notice.render(me.id+'-notice-container-wrap');
		me.refreshObserveScrollForToolbar();
	},

	onKeyDown: function(e) {
		var me = this;
		var undoManager = me.getUndoManager();

		if (undoManager && undoManager.onKeyDown) {
			undoManager.onKeyDown(e);
			if (e.stopped) return;
		}

		var selection = me.mce.selection;
		var startSelection = Ext6.fly(selection.getStart());
		var endSelection = Ext6.fly(selection.getEnd());
		var node = Ext6.fly(selection.getNode());
		var isCaret = (selection.getSel().type == 'Caret');
		var isRange = (selection.getSel().type == 'Range');
		var blockEl = node.up('.template-block');

		if (blockEl) {
			//Редактирование внутри области ввода данных
			if (e.getKey() == e.DELETE) {
				if (isCaret && !node.next() && node.dom.innerText.length <= 1) {
					e.stopEvent();
				}
			}
			else if (e.getKey() == e.BACKSPACE) {
				if (isCaret && !node.prev() && node.dom.innerText.length <= 1) {
					e.stopEvent();
				}
			}
		} else {
			//Редактирование вне области ввода данных
			if (e.getKey() == e.DELETE && (
				node.getAttribute('data-mce-caret') == 'before' && node.next() ||
				startSelection.hasCls('editor-block-container')
			)) {
				e.stopEvent();
			}
			else if ((e.getKey() == e.BACKSPACE || e.getKey() == e.DELETE) && (
				node.getAttribute('data-mce-caret') == 'after' && node.prev() ||
				endSelection.hasCls('editor-block-container')
			)) {
				e.stopEvent();

				if (selection.getStart().nodeName == 'BR' && !endSelection.hasCls('spec-marker-block-container')) {
					selection.getStart().remove();
				}
			}
			else if (
				(e.getKey() == e.BACKSPACE || e.getKey() == e.DELETE) &&
				startSelection.dom.nodeName == 'P' &&
				Ext6.isEmpty(startSelection.dom.textContent) &&
				startSelection.parent().dom.nodeName == 'DIV' &&
				startSelection.parent().dom.childNodes.length == 1
			) {
				e.stopEvent();
			}
			else if ((e.getKey() == e.BACKSPACE || e.getKey() == e.DELETE) &&
				startSelection.hasCls('sw-editor-page-content') &&
				startSelection.dom.childNodes.length == 1 &&
				selection.getStart().nodeName == 'BR'
			) {
				e.stopEvent();
			}
		}
	},

	onKeyUp: function(e) {
		var me = this;
		var selection = me.mce.selection;
		var node = Ext6.fly(selection.getNode());
		var blockEl = node.up('.template-block');

		if (e.ctrlKey && e.getKey() == e.V) {
			//me.onNodeChange(e);
			e.stopEvent();
		}

		if (blockEl && e.getKey().inlist([e.DELETE, e.BACKSPACE])) {
			me.onNodeChange(e);
			e.stopEvent();
		}
	},
	
	onPaste: function(e) {
		var me = this;

		setTimeout(function() {
			me.getUndoManager().add();
			me.onNodeChange(e);
		}, 1);
	},

	onInitEditor: function() {
		var me = this;
		me.getEditorEl().setStyle('font-size', me.fontSize.defaultValue + me.fontSize.unit);
		me.setUndoManager(me.initUndoManager());
		me.refreshToolbarButtons();
		me.afterInitEditorFn();
		me.isInitEditor = true;
	},

	afterInitEditorFn: Ext6.emptyFn,
	afterInitEditor: function(fn) {
		var me = this;
		me.afterInitEditorFn = fn;
		if (me.isInitEditor) {
			me.afterInitEditorFn();
		}
	},

	onDestroy: function() {
		var me = this;

		me.blocks.forEach(function(block) {
			block.destroy();
		});
	},

	forEachNode: function(nodeList, fn) {
		for(var index = 0; index < nodeList.length; index++) {
			fn(nodeList[index], index, nodeList);
		}
	},

	initMceEditor: function() {
		var me = this;

		var options = {
			language: 'ru',
			branding: false,
			menubar: false,
			resize: false,
			nowrap: false,
			inline: true,
			browser_spellcheck: true,
			autoresize_bottom_margin: 0,
			plugins: "paste lists print autoresize code image",
			/*remove_trailing_brs: false,
			forced_root_block: 'div',
			forced_root_block: false,
			forced_root_block_attrs: {
				'class': 'sw-editor-paragraph'
			},*/
			toolbar: false,
			table_toolbar: false,
			visual_anchor_class: 'ext-anchor'
		};

		if (me.disableStandartUndoManager) {
			options.custom_undo_redo_levels = 1;
		}

		me.mce = tinyMCE.createEditor(me.getId()+'-editor', options);

		me.mce.on('Init', function() {
			me.onInitEditor();
		});
		me.mce.on('SetContent', function(e) {
			me.onSetContent(e);
		});

		me.mce.on('Change', function(e) {
			me.onContentChange(e);
		});
		me.mce.on('NodeChange', function(e) {
			Ext6.apply(e, {target: e.element});
			me.onNodeChange(new Ext6.event.Event(e));
		});
		me.mce.on('Blur', function(e) {
			me.onEditorBlur(e);
		});
		me.mce.on('KeyDown', function(e) {
			me.onKeyDown(new Ext6.event.Event(e));
		});
		me.mce.on('KeyUp', function(e) {
			me.onKeyUp(new Ext6.event.Event(e));
		});
		me.mce.on('Paste', function(e) {
			me.onPaste(new Ext6.event.Event(e));
		});
		me.mce.on('ObjectResized', function(e) {
			var el = Ext6.fly(e.target);
			var tableBlock = me.blocks.find(block => (
				block.xtype == 'editortableblock' &&
				block.getTable() == el
			));

			if (tableBlock) {
				tableBlock.onTableResized(e);
			}
		});
		me.mce.on('DragStart', function(e) {
			e.preventDefault();
		});
	},

	initUndoManager: function() {
		var me = this;
		return me.mce.undoManager;
	},

	createFontSizeMenuItem: function(value) {
		var me = this;
		return {
			text: value,
			handler: function() {
				me.execCommand('fontsize', false, value + me.fontSize.unit);
			}
		};
	},

	createMenu: function(config) {
		config = Ext6.applyIf(config, {cls: 'sw-editor-toolbar-menu'});
		return Ext6.create('Ext6.menu.Menu', config);
	},

	beforeExecCommand: function(command){
		var me = this;
		return !!me.mce.selection;
	},

	afterExecCommand: function(command){

	},

	execCommand: function(command) {
		var me = this;
		var args = arguments;
		var result = false;

		var paragraphCommands = [
			'JustifyLeft','JustifyCenter','JustifyRight','JustifyFull','outdent','indent'
		];

		var rng = me.mce.selection.getRng();

		me.undoManager.transact(function() {
			if (me.beforeExecCommand.apply(me, args) !== false) {
				me.mce.selection.setRng(rng);
				if (command.inlist(paragraphCommands)) {
					tinyMCE.helpers.wrapSelfAndSiblingsInParagraph(me.mce);
				}
				result = me.mce.execCommand.apply(me.mce, args);
				me.afterExecCommand.apply(me, args);
			}
		});
		me.onContentChange();

		return result;
	},

	getToolbarButtonsCfg: function() {
		var me = this;

		var toolbarButtonsCfg = {
			undo: {
				iconCls: 'icon-undo',
				tooltip: 'Отменить',
				disabled: true,
				refreshDisabled: function(btn, prms) {
					return (prms.isReadOnly || prms.viewHtml || !prms.hasUndo);
				},
				handler: function() {
					me.undo();
				}
			},
			redo: {
				iconCls: 'icon-redo',
				tooltip: 'Повторить',
				disabled: true,
				refreshDisabled: function(btn, prms) {
					return (prms.isReadOnly || prms.viewHtml || !prms.hasRedo);
				},
				handler: function() {
					me.redo();
				}
			},
			paste: {
				xtype: 'splitbutton',
				iconCls: 'icon-paste',
				tooltip: 'Вставка из буфера',
				refreshDisabled: function(btn, prms) {
					return (prms.isReadOnly || prms.viewHtml);
				},
				handler: function() {
					me.paste();
				},
				menu: me.createMenu({
					userCls: 'menuWithoutIcons',
					items: [{
						text: 'Вставить с форматированием',
						tooltip: 'Сохранить исходное форматирование',
						handler: function() {
							me.paste();
						}
					}, {
						text: 'Вставить без форматирования',
						tooltip: 'Сохранить только текст',
						disabled: true,
						handler: function() {
							me.paste(true);
						}
					}]
				})
			},
			insertobject: {
				text: 'Вставка',
				cls: 'insert-button',
				refreshDisabled: function(btn, prms) {
					return (prms.isReadOnly || prms.viewHtml);
				},
				menu: me.createMenu({
					cls:'insert-menu',
					items: [{
						iconCls: 'icon-image',
						text: 'Изображение',
						handler: function() {
							me.execCommand('mceImage');
						}
					}, {
						xtype: 'swtablebutton',
						iconCls: 'icon-table',
						text: 'Таблица',
						select: me.insertTable.bind(me)
					}]
				})
			},
			image: {
				iconCls: 'icon-image',
				tooltip: 'Изображение',
				refreshDisabled: function(btn, prms) {
					return (prms.isReadOnly || prms.viewHtml);
				},
				handler: function() {
					me.execCommand('mceImage');
				}
			},
			table: {
				xtype: 'swtablebutton',
				iconCls: 'icon-table',
				tooltip: 'Таблица',
				refreshDisabled: function(btn, prms) {
					return (prms.isReadOnly || prms.viewHtml);
				},
				select: me.insertTable.bind(me)
			},
			fontsize: {
				text: me.fontSize.defaultValue,
				tooltip: 'Размер текста',
				minWidth: 46,
				refreshDisabled: function(btn, prms) {
					return (prms.isReadOnly || prms.viewHtml);
				},
				menu: me.createMenu({
					userCls: 'menuWithoutIcons',
					minWidth: 60,
					items: me.fontSize.options.map(me.createFontSizeMenuItem, me)
				})
			},
			bold: {
				iconCls: 'icon-bold',
				tooltip: 'Жирный',
				enableToggle: true,
				refreshDisabled: function(btn, prms) {
					return (prms.isReadOnly || prms.viewHtml);
				},
				handler: function() {
					me.execCommand('bold');
				}
			},
			italic: {
				iconCls: 'icon-italic',
				tooltip: 'Курсив',
				enableToggle: true,
				refreshDisabled: function(btn, prms) {
					return (prms.isReadOnly || prms.viewHtml);
				},
				handler: function() {
					me.execCommand('italic');
				}
			},
			underline: {
				iconCls: 'icon-underline',
				tooltip: 'Подчеркнутый',
				enableToggle: true,
				refreshDisabled: function(btn, prms) {
					return (prms.isReadOnly || prms.viewHtml);
				},
				handler: function() {
					me.execCommand('underline');
				}
			},
			strikethrough: {
				iconCls: 'icon-strikethrough',
				tooltip: 'Зачеркнутый',
				enableToggle: true,
				refreshDisabled: function(btn, prms) {
					return (prms.isReadOnly || prms.viewHtml);
				},
				handler: function() {
					me.execCommand('strikethrough');
				}
			},
			subscript: {
				iconCls: 'icon-subscript',
				tooltip: 'Нижний индекс',
				enableToggle: true,
				refreshDisabled: function(btn, prms) {
					return (prms.isReadOnly || prms.viewHtml);
				},
				handler: function() {
					me.execCommand('subscript');
				}
			},
			superscript: {
				iconCls: 'icon-superscript',
				tooltip: 'Верхний индекс',
				enableToggle: true,
				refreshDisabled: function(btn, prms) {
					return (prms.isReadOnly || prms.viewHtml);
				},
				handler: function() {
					me.execCommand('superscript');
				}
			},
			list: {
				iconCls: 'icon-list',
				tooltip: 'Список',
				refreshDisabled: function(btn, prms) {
					return (prms.isReadOnly || prms.viewHtml);
				},
				menu: me.createMenu({
					userCls: 'menuWithoutIcons',
					items: [{
						text: 'Маркированный список',
						handler: function() {
							me.execCommand('insertUnorderedList');
						}
					}, {
						text: 'Нумерованный список',
						handler: function() {
							me.execCommand('insertOrderedList');
						}
					}]
				})
			},
			indent: {
				iconCls: 'icon-indent menuWithoutIcons',
				tooltip: 'Отступ',
				refreshDisabled: function(btn, prms) {
					return (prms.isReadOnly || prms.viewHtml);
				},
				menu: me.createMenu({
					userCls: 'menuWithoutIcons',
					items: [{
						text: 'Уменшить отступ',
						handler: function() {
							me.execCommand('outdent');
						}
					}, {
						text: 'Увеличить отступ',
						handler: function() {
							me.execCommand('indent');
						}
					}]
				})
			},
			align: {
				iconCls: 'icon-align',
				tooltip: 'Выравнивание',
				refreshDisabled: function(btn, prms) {
					return (prms.isReadOnly || prms.viewHtml);
				},
				menu: me.createMenu({
					userCls: 'menuWithoutIcons',
					items: [{
						text: 'По левому краю',
						handler: function() {
							me.execCommand('JustifyLeft');
						}
					}, {
						text: 'По центру',
						handler: function() {
							me.execCommand('JustifyCenter');
						}
					}, {
						text: 'По правому краю',
						handler: function() {
							me.execCommand('JustifyRight');
						}
					}, {
						text: 'По всей ширине',
						handler: function() {
							me.execCommand('JustifyFull');
						}
					}]
				})
			},
			html: {
				iconCls: 'icon-html',
				tooltip: 'Отобразить в виде HTML-кода',
				enableToggle: true,
				handler: function() {
					me.toggleHtml();
				}
			},
			print: {
				iconCls: 'icon-print',
				tooltip: 'Печать',
				refreshDisabled: function(btn, prms) {
					return prms.viewHtml;
				},
				handler: function() {
					me.printDocument();
				}
			},
			save: {
				iconCls: 'icon-save',
				tooltip: 'Сохранить изменения',
				refreshDisabled: function(btn, prms) {
					return (prms.isReadOnly || prms.viewHtml);
				},
				handler: function() {
					me.saveDocument();
				}
			},
			savemenu: {
				xtype: 'splitbutton',
				iconCls: 'icon-save',
				tooltip: 'Сохранение',
				maskOnDisable: false,
				refreshDisabled: function(btn, prms) {
					return (prms.isReadOnly || prms.viewHtml);
				},
				handler: function() {
					me.saveDocument();
				},
				menu: me.createMenu({
					userCls: 'menuWithoutIcons',
					items: [{
						text: 'Сохранить',
						tooltip: 'Сохранить',
						handler: function() {
							me.saveDocument();
						}
					}, {
						text: 'Сохранить как...',
						tooltip: 'Сохранить как...',
						handler: function() {
							me.saveDocument(true);
						}
					}]
				})
			}
		};

		return toolbarButtonsCfg;
	},

	initToolbar: function() {
		var me = this;
		var buttonsCfg = me.getToolbarButtonsCfg();
		var toolbarCfg = me.toolbarCfg;

		if (me.maximized && me.toolbarMaximizedCfg) {
			toolbarCfg = me.toolbarMaximizedCfg;
		}

		me.toolbarButtons = {};
		Object.keys(buttonsCfg).forEach(function(key) {
			var cfg = buttonsCfg[key];
			switch(cfg.xtype) {
				case 'panel':
					Ext6.applyIf(cfg, {
						border: false,
						width: 16,
						height: 16
					});
					me.toolbarButtons[key] = Ext6.create('Ext6.Panel', cfg);
					break;
				case 'splitbutton':
					cfg.cls = [cfg.cls||'','sw-split-btn'].join(' ');
					me.toolbarButtons[key] = Ext6.create('Ext6.button.Split', cfg);
					break;
				case 'swscalebutton':
					me.toolbarButtons[key] = Ext6.create('sw.button.Scale', cfg);
					me.toolbarButtons[key].setScale(me.scale);
					break;
				case 'swtablebutton':
					me.toolbarButtons[key] = Ext6.create('sw.table.Button', cfg);
					break;
				default:
					me.toolbarButtons[key] = Ext6.create('Ext6.button.Button', cfg);
					break;
			}
		});

		if (Ext6.isArray(toolbarCfg)) {
			toolbarCfg = toolbarCfg.join(' ');
		}

		var itemsCfg = toolbarCfg.split(' ').map(function(key) {
			if (me.toolbarButtons[key]) return me.toolbarButtons[key];
			if (key == '|') return '-';
			return key;
		});

		if (me.toolbar && me.toolbar.rendered) {
			me.toolbar.removeAll();
			me.toolbar.add(itemsCfg);
		} else {
			me.toolbar = me.tbar = Ext6.create('Ext6.toolbar.Toolbar', {
				cls: 'sw-editor-toolbar',
				height: 33,
				defaults: {
					margin: '1 1'
				},
				items: itemsCfg
			});
		}
	},

	refreshToolbarButtons: function(buttons) {
		var me = this;
		var namesArr = Object.keys(me.toolbarButtons);
		var buttonsArr = Object.values(me.toolbarButtons);
		var undoManager = me.getUndoManager();

		var params = {
			viewHtml: me.viewHtml,
			isReadOnly: me.isReadOnly,
			hasUndo: undoManager?undoManager.hasUndo():false,
			hasRedo: undoManager?undoManager.hasRedo():false
		};

		if (!buttons) {
			buttons = buttonsArr;
		}
		if (!Ext6.isArray(buttons)) {
			buttons = [buttons];
		}

		buttons.forEach(function(button) {
			var name = null;

			if (Ext6.isString(button)) {
				name = button;
				button = me.toolbarButtons[name];
			} else {
				name = namesArr[buttonsArr.indexOf(button)];
			}
			if (!name || !button) {
				return;
			}

			var disabled = null;

			if (button.refreshDisabled) {
				disabled = button.refreshDisabled(button, params);
			}

			if (disabled !== null && button.disabled != disabled) {
				button.setDisabled(disabled);
			}
		});
	},

	initNotice: function() {
		var me = this;

		me.notice = Ext6.create('Ext6.Container', {
			cls: 'sw-editor-notice-container'
		});
	},

	initComponent: function() {
		var me = this;

		me.initNotice();
		me.initToolbar();
		me.callParent(arguments);
		me.initMceEditor();
	}
});