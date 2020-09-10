Ext.define('sw.lib.Toaster', {
	extend: 'Ext.window.Window',
	alias: 'widget.uxNotification',
	cls: 'ux-notification-window',
	autoClose: true,
	autoHeight: true,
	plain: false,
	draggable: false,
	shadow: false,
	focus: Ext.emptyFn,
	paddingX: 30,
	paddingY: 10,

	fadeInAnimation: 'easeIn',
	fadeBackAnimation: 'easeOut',
	slideInDuration: 1500,
	slideBackDuration: 1000,
	hideDuration: 500,
	autoCloseDelay: 7000,
	
	// Private. Do not override!
	isHiding: false,
	isFading: false,
	destroyAfterHide: false,
	
	paddingFactorX : 0,
	paddingFactorY : 1,
	managerAlignment : "b-t",
	beforeShow: function () {
		var me = this;

		if (me.autoClose) {
			me.task = new Ext.util.DelayedTask(me.close, me);
			me.task.delay(me.autoCloseDelay);
		}

		me.el.setX(-10000);
		me.el.setOpacity(0);
		
	},
	afterShow: function () {
		var me = this;
		me.callParent(arguments);
		me.el.alignTo(Ext.getBody(), me.managerAlignment, [(me.paddingX), (me.paddingY)], false);
		me.el.fadeIn({
		    opacity: 1,
		    easing: this.fadeInAnimation,
		    duration: 500
		});
	},
	
	hide: function () {
		var me = this;

		if (me.isHiding) {
			if (!me.isFading) {
				me.callParent(arguments);
				// Must come after callParent() since it will pass through hide() again triggered by destroy()
				me.isHiding = false;
			}
		} else {
			// Must be set right away in case of double clicks on the close button
			me.isHiding = true;
			me.isFading = true;

			me.cancelAutoClose();

			if (me.el) {
				me.el.fadeOut({
					opacity: 0,
					duration: me.hideDuration,
					remove: me.destroyAfterHide,
					easing: this.fadeOutAnimation,
					listeners: {
						afteranimate: function () {
							me.isFading = false;
							me.hide(me.animateTarget, me.doClose, me);
						}
					}
				});
			}
		}

		return me;
	},
	destroy: function () {
		var me = this;
		if (!me.hidden) {
			me.destroyAfterHide = true;
			me.hide(me.animateTarget, me.doClose, me);
		} else {
			me.callParent(arguments);
		}
	},
	cancelAutoClose: function() {
		var me = this;
		if (me.autoClose) {
			me.task.cancel();
		}
	},
})