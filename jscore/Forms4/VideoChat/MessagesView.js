Ext6.define('videoChat.MessagesView', {
	extend: 'Ext6.Component',
	alias: 'widget.swVideoChatMessagesView',
	requires: [
		'videoChat.MessageView'
	],
	cls: 'video-chat-messages-view',
	scrollable: true,
	isScrollOnLastCache: false,

	getContacts: null,

	getContact: function(message) {
		var me = this;
		return me.getContacts().find(function(contact) {
			return contact.get('id') == message.get('pmUser_sid');
		});
	},

	onLoadImg: function() {
		var me = this;
		if (me.isScrollOnLastCache) {
			me.scrollToLast();
		}
	},

	addItem: function(message, position) {
		var me = this;

		me.imgCount = 0;
		if (message.get('text').search('src=\'data:image') >= 0) {
			me.imgCount++;
		}

		var item = Ext6.create('videoChat.MessageView', {
			message: message,
			contact: me.getContact(message),
			onLoadImg: me.onLoadImg.bind(me)
		});

		item.render(me.el, position);
		me.items.push(item);

		return item;
	},

	getItem: function(message) {
		var me = this;
		if (!message) return null;
		return me.items.find(function(item) {
			return item.message.get('id') == message.get('id');
		});
	},

	getItemIndex: function(message) {
		var me = this;
		return me.items.findIndex(function(item) {
			return item.message.get('id') == message.get('id');
		});
	},

	getItemsHeight: function(items) {
		return items.reduce(function(height, item) {
			return height + item.getHeight();
		}, 0);
	},

	removeItem: function(message) {
		var me = this;
		var idx = me.items.getItemIndex(message);
		if (idx >= 0) {
			me.items[idx].destroy();
			me.items.splice(idx, 1);
		}
	},

	removeItems: function() {
		var me = this;
		me.items.forEach(function(item) {
			item.destroy();
		});
		me.items = [];
	},

	getFirstItem: function() {
		var me = this;
		return me.items[0] || null;
	},

	getLastItem: function() {
		var me = this;
		return me.items[me.items.length - 1] || null;
	},

	scrollToItem: function(item) {
		var me = this;
		if (item) {
			me.el.scrollChildIntoView(item.el);
		}
	},

	scrollToMessage: function(message) {
		var me = this;
		me.scrollToItem(me.getItem(message));
	},

	scrollToLast: function() {
		var me = this;
		me.scrollToItem(me.getLastItem());
	},

	isScrollOnLast: function() {
		var me = this;
		var lastItem = me.getLastItem();
		if (!lastItem) return true;
		var contRegion = me.getRegion();
		var itemRegion = lastItem.getRegion();
		return (
			itemRegion.top < contRegion.bottom &&
			itemRegion.bottom > contRegion.top
		);
	},

	onScrollMove: function() {
		var me = this;
		me.isScrollOnLastCache = me.isScrollOnLast();
	},

	onScrollEnd: function() {
		var me = this;
		if (me.getScrollY() == 0) {
			me.loadPrev();
		}
	},

	load: function(options) {
		var me = this;
		me.isScrollOnLastCache = true;
		me.engine.messageStore.removeAll();
		me.engine.messageStore.load(options);
	},

	loadPrev: function() {
		var me = this;
		if (!me.getFirstItem()) return;
		var options = Ext.apply({}, me.engine.messageStore.lastOptions);
		options.addRecords = true;
		options.params.beforeDT = me.store.first().get('dt');
		options.callback = Ext6.emptyFn;
		me.engine.messageStore.load(options);
	},

	initComponent: function() {
		var me = this;

		me.engine = sw.Promed.VideoChat;
		me.items = [];

		me.store.on({
			load: function(store, messages) {
				var flag = me.items.length > 0;
				var scrollPosition = me.getScrollY();

				var addedItems = messages.sort(function(a, b) {
					if (a.get('dt') < b.get('dt')) return -1;
					if (a.get('dt') > b.get('dt')) return 1;
					return 0;
				}).map(function(message, idx) {
					return me.addItem(message, flag?idx:null);
				});

				if (me.isScrollOnLastCache) {
					me.scrollToLast();
				} else {
					me.scrollBy(0, me.getItemsHeight(addedItems));
				}
			},
			clear: function(store) {
				me.removeItems();
			},
			add: function(store, messages) {
				messages.forEach(function(message) {
					me.addItem(message);
				});
				me.scrollToLast();
			},
			remove: function(store, messages) {
				messages.forEach(function(message) {
					me.removeItem(message);
				});
			},
			update: function(store, message) {
				var item = me.getItem(message);
				if (item) item.updateMessage(message);
			}
		});

		me.callParent(arguments);
	}
});