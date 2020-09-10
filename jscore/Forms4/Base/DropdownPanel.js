Ext6.define('base.DropdownPanel', {
	extend: 'Ext6.menu.Menu',
	alias: 'widget.dropdown',
	border: false,
	plain: true,
	scrollable: false,
	autoSize: false,
	width: 600,
	height: 240,
	minHeight: 150,
	ignoreParentClicks: true,
	ariaRole: 'dialog',
	userCls: 'template-search',
	shadow: 'frame',
	layout: 'fit',
	resizable: true,
	enableRefreshScroll: true,
	panel: null,

	refreshGridScroller: function(grid) {
		var me = this;
		if (grid.rendered && grid.view && me.enableRefreshScroll) {
			grid.view.scrollable.updateSize({
				x: grid.view.body.getWidth(),
				y: grid.view.body.getHeight()
			});
		}
	},

	show: function() {
		var me = this;

		if (arguments[0]) {
			me._lastAlignTarget = arguments[0].target;
			me._lastAlignToPos = arguments[0].align || me.defaultAlign;
			me._lastAlignToOffsets = arguments[0].offset || me.alignOffset;
		}

		me.callParent(arguments);

		me.alignTo(me._lastAlignTarget, me._lastAlignToPos, me._lastAlignToOffsets);

		if (window.showDropdownLog) {
			log('dropdown', me);
		}

		me.query('grid').forEach(function(grid) {
			me.refreshGridScroller(grid);
		});
	},

	initComponent: function() {
		var me = this;
		var showResizerPanel = false;

		if (me.resizable === true) {
			showResizerPanel = true;
			me.resizeHandles = 'se';
			me.resizable = {dynamic: false, transparent: true};
		}

		if (me.autoSize) {
			me.width = null;
			me.height = null;
			me.minHeight = null;
		}

		me.resizerPanel = Ext6.create('Ext6.Component', {
			dock: 'bottom',
			cls: 'resizer-panel',
			hidden: !showResizerPanel,
			height: 13,
			html: '<div class="icon-resizer"></div>'
		});

		Ext6.apply(me, {
			items: me.panel,
			dockedItems: [
				me.resizerPanel
			]
		});

		me.callParent(arguments);

		me.query('grid').forEach(function(grid) {
			grid.store.on('endupdate', function() {
				me.refreshGridScroller(grid);
			}, {order: 'after'});
		});
	}
});