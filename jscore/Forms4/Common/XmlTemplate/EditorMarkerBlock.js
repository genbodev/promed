Ext6.define('common.XmlTemplate.EditorMarkerBlock', {
	extend: 'base.EditorBlock',
	xtype: 'xmltemplatemarkerblock',

	markerDataStr: '',
	markerData: {},
	markerKey: '',
	index: 0,
	isMarker: true,

	baseCls: 'marker-block',

	renderTpl: [
		'<span id="{id}-sign-wrapper" class="sign-wrapper {sign}"></span>',
		'<span class="{markerCls}-icon"></span>',
		'<div class="{markerCls} {sign}">{content}</div>'
	],

	inheritableStatics: {
		placeTpl: (function() {
			return new Ext6.Template([
				'{{markerKey}_{index} data="{markerData}" endmarker}'
			]);
		}()),

		configFromPlace: function(place) {
			var markerData = {};
			var fromPlaceRegExp = new RegExp('{(marker_.+)_([0-9]+) data="(.*)" endmarker}');
			var match = fromPlaceRegExp.exec(place);
			if (!match) return null;
			return {
				markerKey: match[1],
				markerData: match[3],
				index: match[2]
			};
		},

		getPlace: function(markerKey, markerData, index) {
			var blockClass = this;
			var _markerData = Ext6.isString(markerData)?
				markerData:Ext6.encode(markerData);
			return blockClass.placeTpl.apply({
				markerKey: markerKey,
				markerData: _markerData,
				index: index
			});
		},

		sameBlock: function(block, markerData) {
			var blockClass = this;
			return (
				block.xtype == blockClass.xtype &&
				block.getCode() == markerData.XmlMarkerType_Code.value
			);
		},

		insertToEditor: function(editor, markerKey, markerData) {
			var blockClass = this;
			var blocks = [];

			var index = editor.blocks.filter(function(block) {
				return blockClass.sameBlock(block, markerData);
			}).length;

			var place = blockClass.getPlace(markerKey, markerData, index);

			editor.getUndoManager().transact(function() {
				editor.mce.selection.setContent(place);
				blocks = editor.renderBlocks(true);		//Обновляет ВСЕ блоки
			});

			return blocks.filter(function(block) {
				return block.index == index && blockClass.sameBlock(block, markerData);
			});
		},

		factory: function(editor, template) {
			var blockClass = this;
			var blocks = [];
			template = template || editor.getTemplate();

			var placeRegExp = new RegExp('{marker_.+?endmarker}', 'g');
			var places = template.match(placeRegExp) || [];

			return places.map(function(place) {
				return new blockClass({editor: editor, place: place});
			});
		}
	},

	getCode: function() {
		var me = this;
		return me.markerData.XmlMarkerType_Code.value;
	},

	getText: function() {
		var me = this;
		var text = Object.keys(me.markerData).filter(function(key) {
			return !key.inlist(['XmlMarkerType_Code']);
		}).map(function(key) {
			return me.markerData[key].text;
		}).join(' / ');
		return Ext6.htmlDecode(text);
	},

	getContent: function() {
		var me = this;
		var markerKey = me.getMarkerKey();
		if (!markerKey) return null;
		return me.editor.xmlData[markerKey];
	},

	getMarkerKey: function() {
		var me = this;
		return me.markerKey;
	},

	getPlace: function() {
		var me = this;
		return me.statics().getPlace(me.markerKey, me.markerDataStr, me.index);
	},

	initRenderData: function() {
		var me = this;
		var data = me.callParent();

		var mode = me.editor.markerMode || 'content';
		var sign = me.editor.signsVisible || false;

		var content = (mode == 'content')?me.getContent():me.getText();
		var markerCls = data.baseCls+'-'+(mode == 'content'?'content':'text');

		return Ext6.apply(data, {
			content: content || '',
			markerCls: markerCls,
			sign: sign?'sign':''
		});
	},

	afterRender: function() {
		var me = this;
		me.callParent(arguments);
		me.getEl().on({
			mouseenter: function(event){
				me.onHeaderOver(true);
			},
			mouseleave: function(event){
				me.onHeaderOver(false);
			}
		});
		/*var signWrapper = Ext6.get(me.id+'-sign-wrapper');
		if (signWrapper) {
			me.signMenuBtn.render(signWrapper);
		}*/
	},

	replace: function() {
		var me = this;
		var el = me.getContainerEl();

		getWnd('swXmlTemplateMarkerBlockSelectWindow').show({
			EvnClass_id: me.editor.params.EvnClass_id,
			callback: function(markerData) {
				me.editor.setCursorLocationByEl(el);
				me.remove();
				me.editor.addMarkerBlock(markerData);
			}
		});
	},

	setReadOnly: function(isReadOnly) {
		var me = this;
		me.callParent(arguments);
		me.onHeaderOver(false);
	},

	onHeaderOver: function(over) {
		var me = this;

		if (me.isReadOnly) {
			me.ParamsToolsPanel.hide();
			return;
		}

		var formObject = me.getEl();
		if(formObject.parent().el.dom.getAttribute('data-mce-selected')){
			me.ParamsToolsPanel.show({target: formObject, align: 'tr-br', offset: [0, 0]});
		}
		if(formObject.component.focused){
			me.ParamsToolsPanel.show({target: formObject, align: 'tr-br', offset: [0, 0]});
		} else if (over){
			me.ParamsToolsPanel.show({target: formObject, align: 'tr-br', offset: [0, 0]});
		} else {
			me.ParamsToolsPanel.hide();
		}
	},

	initComponent: function() {
		var me = this;

		Ext6.apply(me, me.statics().configFromPlace(me.place));

		if (Ext6.isObject(me.markerData)) {
			me.markerDataStr = Ext6.encode(me.markerData);
		} else if (Ext6.isString(me.markerData)) {
			me.markerDataStr = me.markerData;
			me.markerData = Ext6.decode(me.markerData);
		}

		var ParamsToolbar = Ext6.create('Ext6.toolbar.Toolbar', {
			cls: 'editor-table-block-toolbar',
			defaults:{
				width: 16,
				height: 16,
				margin: 4
			},
			items:[{
				iconCls: 'icon-table-delete',
				tooltip: 'Удалить спецмаркер из шаблона',
				handler: function() {
					me.ParamsToolsPanel.hide();
					me.remove();
					me.editor.undoManager.add();
					me.editor.onContentChange();
				}
			}]
		});
		me.ParamsToolsPanel = Ext6.create('base.DropdownPanel', {
			autoSize: true,
			resizable: false,
			minWidth: 16,
			shadow: false,
			panel: ParamsToolbar,
			listeners:{
				mouseenter: function(event) {
					me.onHeaderOver(true);
					me.addCls('menu-over');
				},
				mouseleave: function(event) {
					me.onHeaderOver(false);
					me.removeCls('menu-over');
				}
			}
		});

		/*me.signMenuBtn = Ext6.create('Ext6.Button', {
			cls: 'sign-menu-btn',
			iconCls: 'icon-marker-sign',
			menu: Ext6.create('Ext6.menu.Menu', {
				cls: me.baseCls+'-sign-menu',
				items: [{
					iconCls: 'panicon-edit',
					text: 'Заменить маркер документа',
					handler: function() {
						me.replace();
					}
				}, {
					iconCls: 'icon-canbin',
					text: 'Удалить маркер документа из шаблона',
					handler: function() {
						me.remove();
						me.editor.undoManager.add();
						me.editor.onContentChange();
					}
				}]
			})
		});*/
		me.callParent(arguments);
	}
});