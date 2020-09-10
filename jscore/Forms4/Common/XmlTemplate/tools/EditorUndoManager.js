Ext6.define('common.XmlTemplate.tools.EditorUndoManager', {
	requires: [
		'common.XmlTemplate.EditorInputBlock'
	],

	isLocked: function() {
		return this.locks > 0;
	},

	compareLevels: function(level1, level2) {
		return (
			level1 && level2 &&
			level1.template == level2.template &&
			Ext6.Object.equals(level1.xmlData, level2.xmlData) &&
			Ext6.Object.equals(level1.xmlDataSettings, level2.xmlDataSettings)
		);
	},

	createLevel: function() {
		var me = this;
		return {
			template: me.editor.generateTemplate(),
			xmlData: Ext6.apply({}, me.editor.xmlData),
			xmlDataSettings: Ext6.decode(Ext6.encode(me.editor.xmlDataSettings))
		};
	},

	add: function(e) {
		var me = this;

		if (e && e.type == 'nodechange' /*&& e.selectionChange*/) {
			return null;
		}

		if (me.isLocked() || !me.editor.editing) {
			return null;
		}

		var level = me.createLevel();

		if (me.compareLevels(me.current, level)) {
			return null;
		}

		var index = me.data.indexOf(me.current);
		if (index >= 0) me.data.splice(index + 1);

		me.data.push(level);
		me.current = level;

		me.onChangeLevel();

		return level;
	},

	clear: function() {
		var me = this;
		me.data = [];
		me.current = null;
	},

	hasUndo: function() {
		var me = this;
		var index = me.data.indexOf(me.current);
		return (index > 0);
	},

	hasRedo: function() {
		var me = this;
		var index = me.data.indexOf(me.current);
		return (index < me.data.length - 1);
	},

	undo: function() {
		var me = this;

		var index = me.data.indexOf(me.current);
		if (index <= 0) return;

		me.current = me.data[index - 1];

		me.ignore(function() {
			me.editor.xmlData = Ext6.apply({}, me.current.xmlData);
			me.editor.xmlDataSettings = Ext6.apply({}, me.current.xmlDataSettings);
			me.editor.setContent(me.current.template, {addUndoLevel: false});
			me.blocks = me.editor.renderBlocks(true);
			me.editor.onContentChange();
		});

		me.onChangeLevel();

		return me.current;
	},

	redo: function() {
		var me = this;

		var index = me.data.indexOf(me.current);
		if (index == me.data.length - 1) return;

		me.current = me.data[index + 1];

		me.ignore(function() {
			me.editor.xmlData = Ext6.apply({}, me.current.xmlData);
			me.editor.xmlDataSettings = Ext6.apply({}, me.current.xmlDataSettings);
			me.editor.setContent(me.current.template, {addUndoLevel: false});
			me.blocks = me.editor.renderBlocks(true);
			me.editor.onContentChange();
		});

		me.onChangeLevel();

		return me.current;
	},

	ignore: function(callback) {
		var me = this;
		try {
			me.locks++;
			callback();
		} finally {
			me.locks--;
		}
	},

	transact: function(callback) {
		var me = this;
		me.ignore(callback);
		return me.add();
	},

	onKeyDown: function(e) {
		var me = this;

		var isUndo = (
			(e.ctrlKey && !e.shiftKey && e.getKey() == e.Z)
		);
		var isRedo = (
			(e.ctrlKey && !e.shiftKey && e.getKey() == e.Y) ||
			(e.ctrlKey && e.shiftKey && e.getKey() == e.Z)
		);

		if (isUndo) {
			e.stopEvent();
			me.editor.undo();
		} else if (isRedo) {
			e.stopEvent();
			me.editor.redo();
		}
	},

	onChangeLevel: function() {
		var me = this;
		me.onChangeLevelFns.forEach(function(fn){fn()});
	},

	constructor: function(config) {
		var me = this;

		me.current = null;
		me.data = [];
		me.editor = config.editor;
		me.locks = 0;
		me.onChangeLevelFns = [];

		if (Ext6.isArray(config.onChangeLevel) || Ext6.isFunction(config.onChangeLevel)) {
			me.onChangeLevelFns = me.onChangeLevelFns.concat(config.onChangeLevel);
		}
	}
});