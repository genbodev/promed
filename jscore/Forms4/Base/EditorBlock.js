Ext6.define('base.EditorBlock', {
	extend: 'Ext6.Component',
	xtype: 'editorblock',
	focusable: true,
	focused: false,
	allowUnfocus: true,
	isReadOnly: false,

	editor: null,

	cls: 'editor-block',
	baseCls: 'abstract-block',
	autoEl: 'div',

	containerTpl: [
		'<{autoEl} id="{id}-container" for="{id}" class="editor-block-container {baseCls}-container" xtype="{xtype}" contenteditable="false"></{autoEl}>',
		//'<br id="{id}-br" for="{id}" class="editor-block-br" data-mce-bogus="1"/>'
	],

	inheritableStatics: {
		placeTpl: (function() {
			return new Ext6.Template('{{xtype}_{nick}}');
		}()),

		getPlace: function(nick) {
			return this.placeTpl.apply({
				xtype: this.xtype, nick: nick
			});
		},

		insertToEditor: function(editor) {
			var blockClass = this;
			var blocks = [];

			return blocks;
		},

		renderBlock: function(block) {
			block.renderToContainer();
		},

		factory: function(editor, template) {
			var blockClass = this;
			var blocks = [];

			return blocks;
		}
	},

	contentTpl: '',

	getContainerEl: function() {
		var me = this;
		return me.el.up('#'+me.getId()+'-container');
	},

	getBrEl: function() {
		var me = this;
		return Ext6.get(me.getId()+'-br');
	},

	getDataEl: function() {
		var me = this;
		return me.el.down('#'+me.getId()+'-data');
	},

	getFocusEl: function() {
		var me = this;
		return me.getDataEl();
	},

	getMce: function() {
		return this.editor.mce;
	},

	getPlace: function() {
		return null;
	},

	getContainerHtml: function() {
		var me = this;
		return me.containerTpl.apply({
			id: me.getId(), baseCls: me.baseCls, xtype: me.xtype, autoEl: me.autoEl
		});
	},

	setReadOnly: function(isReadOnly) {
		var me = this;
		me.isReadOnly = isReadOnly;

		if (me.isReadOnly) {
			me.getEl().addCls('readonly');
		} else {
			me.getEl().removeCls('readonly');
		}
	},

	afterRender: function() {
		var me = this;
		me.callParent(arguments);
		me.setReadOnly(me.editor.isReadOnly || me.isReadOnly);

		me.getEl().on({
			mousedown: me.onMouseDown,
			keydown: me.onKeyDown,
			scope: me
		});
	},

	onMouseDown: function(e) {
		var me = this;
		me.focus();
	},

	indexOfNode: function(el) {
		var parent = el.parent();
		var childNodes = parent.dom.childNodes;
		var index = -1;
		for (var i = 0; i < childNodes.length; i++) {
			if (childNodes[i] == el.dom) {
				index = i;
			}
		}
		return index;
	},

	onKeyDown: function(e) {
		var me = this;

		if (e.getKey() == e.TAB) {
			e.stopEvent();

			var el, target;

			if (e.shiftKey) {
				el = me.getContainerEl();
				target = el.prev();
			} else {
				el = me.getBrEl() || me.getContainerEl();
				target = el.next();
			}

			if (target) {
				var cmp = Ext6.getCmp(target.getAttribute('for'));

				if (cmp instanceof base.EditorBlock) {
					cmp.focus();
				} else {
					var parent = target.parent();
					me.editor.mce.selection.setCursorLocation(parent.dom, parent.indexOf(target));
					me.editor.mce.selection.setContent('&nbsp;');
					me.editor.mce.selection.getNode().remove();

					var caret = me.editor.el.down('.mce-visual-caret');
					if (caret) caret.remove();
				}
			} else {
				var dom = el.parent().dom;
				me.editor.mce.selection.setCursorLocation(dom, dom.children.length);
				me.editor.mce.selection.setContent('<br data-mce-bogus="1">', {format: 'raw'});
				me.editor.refreshSize();
			}
		}
	},

	isFocusable: function() {
		return this.focusable;
	},

	canFocus: function() {
		return !this.focused && !this.isReadOnly;
	},

	onFocus: function() {
		var me = this;
		if(Ext6.isEmpty(me.editor.T9list) || !me.editor.T9list.isVisible()) {
			me.callParent(arguments);
			me.getEl().addCls(me.focusCls);
			me.focused = true;
		}
	},

	onBlur: function() {
		var me = this;
		if(Ext6.isEmpty(me.editor.T9list) || !me.editor.T9list.isVisible()) {
			if (me.allowUnfocus) {
				me.callParent(arguments);
				me.getEl().removeCls(me.focusCls);
				me.focused = false;
			}
		}
	},

	onDestroy: function() {
		var me = this;

		me.callParent(arguments);

		if (me.getContainerEl()) {
			me.getContainerEl().remove();
		}
		if (me.getBrEl()) {
			me.getBrEl().remove();
		}

		var index = me.editor.blocks.indexOf(me);

		if (index >= 0) {
			me.editor.blocks.splice(index, 1);
		}
	},

	remove: function() {
		var me = this;

		if (me.getContainerEl()) {
			me.getContainerEl().remove();
		}
		if (me.getBrEl()) {
			me.getBrEl().remove();
		}

		var index = me.editor.blocks.indexOf(me);

		if (index >= 0) {
			me.editor.blocks.splice(index, 1);
		}

		return me;
	},

	renderToContainer: function() {
		var me = this;
		var containerId = me.getId()+'-container';
		var container = Ext6.get(containerId);
		if (!container) {
			throw new Error('Not found container');
		}
		me.render(container);
		return [me];
	},

	initComponent: function() {
		var me = this;

		if (!(me.editor instanceof base.EditorPanel)) {
			throw new Error('Not defined editor');
		}

		me.focusCls = me.baseCls+'-focus';
		me.placeTpl = me.statics().placeTpl;
		me.containerTpl = new Ext6.Template(me.containerTpl);
		me.contentTpl = new Ext6.Template(me.contentTpl);

		me.callParent(arguments);

		me.editor.blocks.push(me);
	}
});