Ext6.define('common.XmlTemplate.EditorInputBlock', {
	extend: 'base.EditorBlock',
	xtype: 'xmltemplateinputblock',
	requires: [
		'common.XmlTemplate.EditorSpecMarkerBlock'
	],

	xmlDataKey: '',
	XmlDataSection: {},

	baseCls: 'template-block',

	renderTpl: [
		'<div id="{id}-caption" class="{baseCls}-caption">',
		'<div id="{id}-caption-text" class="{baseCls}-caption-text" contenteditable="true">',
		'{caption}',
		'</div>',
		//'<div id="{id}-menu-btn" class="panicon-theedots"></div>',
		'</div>',
		'<div id="{id}-data" class="{baseCls}-data" contenteditable="true">',
			'{content}',
		'</div>'
	],

	contentTpl: [
		'{content}'
	],

	inheritableStatics: {
		isCaptionEl: function(el) {
			var blockCls = 'template-block';
			var captionTextCls = 'template-block-caption-text';
			return (
				(el.hasCls(captionTextCls) || el.up('.'+captionTextCls))
				&& el.up('.'+blockCls)
			);
		},

		isDataEl: function(el) {
			var blockCls = 'template-block';
			var dataCls = 'template-block-data';
			return (
				(el.hasCls(dataCls) || el.up('.'+dataCls))
				&& el.up('.'+blockCls)
			);
		},

		insertToEditor: function(editor, XmlDataSections, content) {
			var blockClass = this;
			var blocks = [];
			var places = [];

			if (!Ext6.isArray(XmlDataSections)) {
				XmlDataSections = [XmlDataSections];
			}
			if (!Ext6.isArray(content)) {
				content = [content];
			}

			var autonameCount = Object.keys(editor.xmlData).filter(function(key) {
				return /^autoname\d*$/.test(key);
			}).length;

			var nicks = XmlDataSections.map(function(item) {
				return (item.XmlDataSection_SysNick == 'autoname')
					?item.XmlDataSection_SysNick + (++autonameCount)
					:item.XmlDataSection_SysNick;
			});

			nicks.forEach(function(nick, index) {
				editor.xmlData[nick] = content[index] || '';

				places.push(blockClass.placeTpl.apply({
					xtype: blockClass.xtype, nick: nick
				}));
			});

			if (places.length == 0) {
				return [];
			}

			editor.getUndoManager().transact(function() {
				editor.mce.selection.setContent(places.join(''));
				blocks = editor.renderBlocks(true);		//Обновляет ВСЕ блоки
			});

			return blocks.filter(function(block) {
				return block.xmlDataKey && block.xmlDataKey.inlist(nicks);
			});
		},

		factory: function(editor, template) {
			var blockClass = this;
			var blocks = [];
			template = template || editor.getTemplate();

			Object.keys(editor.xmlData).forEach(function(nick) {
				var XmlDataSection = null;

				if (/^autoname\d*$/.test(nick)) {
					XmlDataSection = editor.cache.getData('XmlDataSection', 'XmlDataSection_SysNick', 'autoname');
				} else {
					XmlDataSection = editor.cache.getData('XmlDataSection', 'XmlDataSection_SysNick', nick);
				}
				var content = editor.xmlData[nick];
				var place = blockClass.getPlace(nick);

				if (!XmlDataSection || content === undefined || template.indexOf(place) < 0) {
					return;
				}

				var block = new blockClass({
					editor: editor,
					xmlDataKey: nick,
					XmlDataSection: XmlDataSection,
					content: content
				});

				blocks.push(block);
			});

			return blocks;
		}
	},

	getCaptionEl: function() {
		var me = this;
		return me.el.down('#'+me.getId()+'-caption');
	},

	getCaptionTextEl: function() {
		var me = this;
		return me.el.down('#'+me.getId()+'-caption-text');
	},

	getMenuBtnEl: function() {
		var me = this;
		return me.el.down('#'+me.getId()+'-menu-btn');
	},

	getContent: function() {
		var me = this;
		var re = / id="ext-element-\d+"/g;
		var el = me.getDataEl();

		var content = el.getHtml();

		return me.editor.correctTemplate(content.replace(re, ''), true);
	},

	convertContent: function(content) {
		var el = document.createElement('div');
		el.innerHTML = content;

		if (!el.firstChild || !el.firstChild.nodeName.inlist(['P','DIV'])) {
			content = content+(!content?'<br>':'');
		}

		return content;
	},

	setContent: function(content) {
		var me = this;
		me.content = me.contentTpl.apply({
			content: me.convertContent(content)
		});
		me.getDataEl().setHtml(me.content);

		me.refreshXmlData();
	},

	setCaption: function(caption) {
		var me = this;
		var dom = me.getCaptionTextEl().dom;
		dom.innerHTML = caption;
		me.refreshXmlDataLabel();
	},

	getCaption: function(refresh) {
		var me = this;
		if (refresh) {
			var dom = me.getCaptionTextEl().dom;
			return dom.innerHTML.replace('<br>','').trim();
		}
		var nick = me.xmlDataKey;
		if (!me.editor.xmlDataSettings[nick]) {
			return undefined;
		}
		return me.editor.xmlDataSettings[nick].fieldLabel;
	},

	getName: function() {
		return this.XmlDataSection.XmlDataSection_Name;
	},

	getNick: function() {
		return this.XmlDataSection.XmlDataSection_SysNick;
	},

	getPlace: function() {
		var me = this;
		if (!me.XmlDataSection) {
			return null;
		}
		return me.statics().getPlace(
			me.xmlDataKey
		);
	},

	initRenderData: function() {
		var me = this;

		return Ext6.apply(me.callParent(), {
			caption: me.getCaption() || '<strong>'+me.getName()+'</strong>' || '&nbsp;',
			content: me.contentTpl.apply({content: me.convertContent(me.content)})
		});
	},

	refreshXmlData: function() {
		var me = this;
		var nick = me.xmlDataKey;
		var content = me.getContent();
		me.editor.xmlData[nick] = content;
	},

	refreshXmlDataLabel: function() {
		var me = this;
		var nick = me.xmlDataKey;
		var caption = me.getCaption(true);
		if (!me.editor.xmlDataSettings[nick]) {
			me.editor.xmlDataSettings[nick] = {};
		}
		me.editor.xmlDataSettings[nick].fieldLabel = caption;
	},

	onCaptionTextKeyDown: function(e) {
		var me = this;
		var captionHtml = e.target.innerHTML;
		var captionText = e.target.innerText;
		if (e.getKey().inlist([e.ENTER, e.DOWN])) {
			e.stopEvent();
			me.focusData = true;
			me.onFocus();
		}
		if (!e.isSpecialKey() && captionText.length >= 80) {
			e.stopEvent();
		}
	},

	onKeyDown: function(e) {
		var me = this;

		me.callParent(arguments);

		if (!e.getKey().inlist([e.TAB])) {
			me.editor.delayTyping(function() {
				me.refreshXmlDataLabel();
				me.refreshXmlData();
			});
		}
	},

	onFocus: function(e) {
		var me = this;

		me.callParent(arguments);

		var dataEl = me.getDataEl();
		var cursorNode = (dataEl.down('div>p') || dataEl.down('div') || dataEl).dom;
		var currentNode = me.getMce().selection.getNode();
		var range = me.getMce().selection.getRng();

		if (currentNode != cursorNode && (!me.getCaptionTextEl().contains(currentNode) || me.focusData)) {
			me.focusData = false;
			me.editor.setCursorLocation(cursorNode);
			//me.refreshToolsPanelVisibility(true);
		}
	},

	onBlur: function(e) {
		var me = this;
		if (!me.getEl().contains(e.relatedTarget)) {
			me.callParent(arguments);
			me.refreshXmlData();
			//me.refreshToolsPanelVisibility(false);
		} else {
			me.refreshXmlDataLabel();
		}
	},

	onDestroy: function() {
		var me = this
		var nick = me.xmlDataKey;

		me.callParent(arguments);
		me.removed = true;

		delete me.editor.xmlData[nick];
		delete me.editor.xmlDataSettings[nick];
		me.editor.getUndoManager().add();
		me.editor.onContentChange();
	},

	remove: function() {
		var me = this;
		var nick = me.xmlDataKey;

		me.callParent(arguments);
		me.removed = true;

		delete me.editor.xmlData[nick];
		delete me.editor.xmlDataSettings[nick];
		me.editor.getUndoManager().add();
		me.editor.onContentChange();
		return me;
	},

	restore: function() {
		var me = this;
		var nick = me.xmlDataKey;
		var caption = (me.editor.originalXmlDataSettings[nick]||{}).fieldLabel;
		var content = me.editor.originalXmlData[nick];

		me.editor.getUndoManager().transact(function() {
			if (caption || caption === '') {
				me.setCaption(caption);
			}
			if (content || content === '') {
				me.setContent(content);
			}
			me.renderInnerBlocks();
			me.editor.onContentChange();
		});
	},

	refreshToolsPanelVisibility: function(isHover) {
		var me = this;
		var el = me.getEl();

		if (me.removed) return;

		if (!me.isReadOnly && (me.focused || isHover)) {
			if (me.toolsPanel.isHidden()) {
				me.toolsPanel.show({target: el, align: 'tr-br'});
			}
		} else {
			me.toolsPanel.hide();
		}
	},

	/**
	 * Рендеринг спецмаркеров внутри области ввода
	 */
	renderInnerBlocks: function() {
		var me = this;
		var blockClasses = [
			common.XmlTemplate.EditorSpecMarkerBlock
		];
		var renderBlock = function(blocks, block) {
			try {
				blocks = blocks.concat(block.renderToContainer());
			} catch(e) {
				log('Block not rendered: '+e.message, block);
			}
			return blocks;
		};

		var content = me.getContent();
		var blocks = blockClasses.reduce(function(blocks, blockClass) {
			return blocks.concat(blockClass.factory(me.editor, content));
		}, []);

		me.setContent(blocks.reduce(function(content, block) {
			return content.replace(block.getPlace(), block.getContainerHtml());
		}, content));

		return blocks.reduce(renderBlock, []);
	},

	renderToContainer: function() {
		var me = this;
		var blocks = me.callParent(arguments);
		return blocks.concat(me.renderInnerBlocks());
	},

	afterRender: function() {
		var me = this;
		me.callParent(arguments);

		me.refreshXmlDataLabel();

		me.getCaptionTextEl().on({
			focus: me.onFocus,
			blur: me.onBlur,
			scope: me
		});

		me.getEl().on({
			mouseenter: function() {
				me.isHover = true;
				if(Ext6.isEmpty(me.editor.T9list) || !me.editor.T9list.isVisible()) {
					me.refreshToolsPanelVisibility(true);
				}
			},
			mouseleave: function() {
				me.isHover = false;
				if(Ext6.isEmpty(me.editor.T9list) || !me.editor.T9list.isVisible()) {
					me.refreshToolsPanelVisibility(false);
				}
			}
		});
	},

	setReadOnly: function(isReadOnly) {
		var me = this;
		me.callParent(arguments);
		me.refreshToolsPanelVisibility(me.isHover);
	},

	initComponent: function() {
		var me = this;

		var toolbar = Ext6.create('Ext6.toolbar.Toolbar', {
			cls: 'editor-table-block-toolbar',
			defaults:{
				width: 16,
				height: 16,
			},
			items:[{
				iconCls: 'icon-table-up',
				tooltip: 'Перместить выше',
				margin: '4 2 4 4',
				disabled: true,
				handler: function () {
				}
			}, {
				iconCls: 'icon-table-down',
				tooltip: 'Перместить ниже',
				margin: '4 2 4 2',
				disabled: true,
				handler: function () {
				}
			}, {
				iconCls: 'icon-table-restore',
				tooltip: 'Восстановить область',
				margin: '4 4 4 2',
				handler: function() {
					me.restore();
				}
			}, {
				iconCls: 'icon-table-delete',
				tooltip: 'Удалить область',
				margin: '4 4 4 2',
				handler: function() {
					me.allowUnfocus = true;
					me.toolsPanel.hide();
					me.remove();
				}
			}]
		});

		me.toolsPanel = Ext6.create('base.DropdownPanel', {
			autoSize: true,
			resizable: false,
			shadow: false,
			minWidth: 16,
			panel: toolbar,
			listeners: {
				mouseenter: function() {
					if(Ext6.isEmpty(me.editor.T9list) || !me.editor.T9list.isVisible()) {
						me.allowUnfocus = false;
						me.refreshToolsPanelVisibility(true)
						me.addCls('menu-over');
					}
				},
				mouseleave: function() {
					if(Ext6.isEmpty(me.editor.T9list) || !me.editor.T9list.isVisible()) {
						me.allowUnfocus = true;
						me.refreshToolsPanelVisibility(false)
						me.removeCls('menu-over');
					}
				}
			}
		});

		me.callParent(arguments);
	}
});
