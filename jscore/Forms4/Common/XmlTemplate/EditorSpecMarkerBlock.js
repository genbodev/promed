Ext6.define('common.XmlTemplate.EditorSpecMarkerBlock', {
	extend: 'base.EditorBlock',
	xtype: 'xmltemplatespecmarkerblock',

	SpecMarker: {},
	isMarker: true,
	isLocked: false,

	baseCls: 'spec-marker-block',
	autoEl: 'span',

	renderTpl: [
		'<span id="{id}-sign-wrapper" class="sign-wrapper {sign}"></span>',
		'<span class="{markerCls}-icon"></span>',
		'<{autoEl} class="{markerCls} {sign}">{marker}</{autoEl}>',
		'<span id="{id}-locked-icon" class="locked-icon" data-qtip="{lockedTip}"></span>'
	],

	inheritableStatics: {
		placeTpl: (function() {
			return new Ext6.Template('@#@{name}');
		}()),

		getPlace: function(name) {
			return this.placeTpl.apply({name: name});
		},

		insertToEditor: function(editor, SpecMarkers) {
			var blockClass = this;
			var blocks = [];
			var selection = editor.mce.selection;
			var parentBlock = null;

			var parentBlockEl = Ext6.fly(selection.getNode()).up('.editor-table-block, .template-block');
			if (parentBlockEl) parentBlock = editor.blocks.find(function(block) {
				return block.getId() == parentBlockEl.id;
			});

			if (!Ext6.isArray(SpecMarkers)) {
				SpecMarkers = [SpecMarkers];
			}
			if (SpecMarkers.length == 0) {
				return [];
			}

			var names = SpecMarkers.map(function(item) {
				return item.name;
			});

			var places = names.map(function(name) {
				return blockClass.getPlace(name);
			});

			editor.getUndoManager().transact(function() {
				editor.mce.selection.setContent(places.join(' '));
				if (parentBlock) {
					blocks = parentBlock.renderInnerBlocks();
					editor.refreshNotice();
				} else {
					blocks = editor.renderBlocks(true);		//Обновляет ВСЕ блоки
				}
			});

			return blocks.filter(function(block) {
				return (
					block.xtype == blockClass.xtype &&
					block.getName().inlist(names)
				);
			});
		},

		factory: function(editor, template) {
			var blockClass = this;
			var blocks = [];
			template = template || editor.getTemplate();

			//Удаление существующих блоков, чтобы в них не рендерились маркеры
			var dom = document.createElement('div');
			dom.insertAdjacentHTML('afterbegin', template);
			editor.forEachNode(dom.querySelectorAll('.spec-marker-block-container'), function(el){el.remove()});
			template = dom.innerHTML;

			editor.cache.getData('SpecMarker').forEach(function(SpecMarker) {
				var place = blockClass.getPlace(SpecMarker.name);

				var regexp = new RegExp(place+'(?:[^А-яЁё0-9]|$)', 'g');
				var count = (template.match(regexp) || []).length;

				for (var i = 0; i < count; i++) {
					blocks.push(new blockClass({
						editor: editor,
						SpecMarker: SpecMarker
					}));
				}
			});

			return blocks;
		}
	},

	getName: function() {
		var me = this;
		if (!me.SpecMarker) return null;
		return me.SpecMarker.name;
	},

	getContent: function() {
		var me = this;
		if (!me.SpecMarker) return null;
		return me.editor.xmlData['specMarker_'+me.SpecMarker.id];
	},

	getPlace: function() {
		var me = this;
		var name = me.getName();
		if (!name) return null;
		return me.statics().getPlace(name);
	},

	isPrescr: function() {
		var me = this;
		return me.SpecMarker.field == 'print_evnprescr';
	},

	//Специфичная функция для спецмаркера "Назначения"
	getPrescrCount: function() {
		var me = this;
		var prescrCountEl = me.el.down('[prescr-count]');

		if (prescrCountEl) {
			return prescrCountEl.getAttribute('prescr-count');
		}

		return null;
	},

	initRenderData: function() {
		var me = this;
		var data = me.callParent();

		var mode = me.editor.markerMode || 'content';
		var sign = me.editor.signsVisible || false;

		var marker = (mode == 'content')?me.getContent():me.getPlace();
		var markerCls = data.baseCls+'-'+(mode == 'content'?'content':'text');
		var lockedTip = 'Информация добавляется автоматически и недоступна для редактирования';

		return Ext6.apply(data, {
			marker: marker || '',
			autoEl: me.autoEl,
			markerCls: markerCls,
			sign: sign?'sign':'',
			lockedTip: lockedTip
		});
	},

	renderToContainer: function() {
		var me = this;
		var blocks = me.callParent(arguments);
		me.el.query('.spec-marker-block-content *[style]').forEach(function(dom) {
			Ext6.fly(dom).setStyle('font-size', null);
		});
		return blocks;
	},

	afterRender: function() {
		var me = this;
		me.callParent(arguments);

		if (me.SpecMarker.field == 'print_evnprescr') {
			me.addCls('print_evnprescr');
		}
		if (me.SpecMarker.field == 'print_evnxml_survey_header') {
			me.addCls('header');
		}
		
		var checkEl = me.getContainerEl().up('[contenteditable="false"]');
		if (checkEl && (checkEl.hasCls('sw-editor-page-header') || checkEl.hasCls('sw-editor-page-footer'))) {
			me.isLocked = true;
			me.addCls('locked');
			
			if (me.SpecMarker.field == 'print_evnprescr') {
				me.createRecommendBtn();
			}
		}
		
		if (me.recommendBtn) {
			me.recommendBtn.render(me.getContainerEl());
			me.refreshRecommendBtnVisibility();
		}

		var signWrapper = Ext6.get(me.id+'-sign-wrapper');
		if (signWrapper) {
			//me.signMenuBtn.render(signWrapper);
		}

		if (!me.isLocked) {
			me.getEl().on({
				mouseenter: function(e) {
					me.onHeaderOver(true);
				},
				mouseleave: function(e) {
					me.onHeaderOver(false);
				}
			});
		}
	},
	
	createRecommendBtn: function() {
		var me = this;
		
		me.recommendBtn = Ext6.create('Ext6.Button', {
			cls: 'button-without-frame recommendations-btn',
			text: 'Добавить рекомендации',
			style: {textTransform: 'none'},
			handler: function() {
				//todo: подгрузка с данных с сервера
				var XmlDataSectionList = [{
					XmlDataSection_id: 8,
					XmlDataSection_id: 7,
					XmlDataSection_Name: 'Рекомендации, назначения',
					XmlDataSection_SysNick: 'recommendations'
				}];

				var container = me.getContainerEl();
				var parent = container.parent();

				me.editor.xmlDataSettings['recommendations'] = Ext.apply(
					me.editor.xmlDataSettings['recommendations'] || {},
					{fieldLabel: '<strong>Рекомендации</strong>'}
				);

				me.editor.setCursorLocation(parent.dom, parent.indexOf(container)+1);
				me.editor.addInputBlocks(XmlDataSectionList);
			}
		});
	},

	onDestroy: function() {
		var me = this;
		me.callParent(arguments);
		me.editor.refreshNotice();
	},

	remove: function() {
		var me = this;
		me.callParent(arguments);
		me.editor.refreshNotice();
		return me;
	},

	replace: function() {
		var me = this;
		var el = me.getContainerEl();
		var parent = el.parent();

		getWnd('swXmlTemplateSpecMarkerBlockSelectWindow').show({
			EvnClass_id: me.editor.params.EvnClass_id,
			onSelect: function(SpecMarker) {
				me.editor.mce.selection.setCursorLocation(parent.dom, parent.indexOf(el));

				me.editor.undoManager.transact(function() {
					me.remove();
					me.editor.addSpecMarkerBlocks([SpecMarker]);
				});

				getWnd('swXmlTemplateSpecMarkerBlockSelectWindow').hide();
			}
		});
	},

	refreshRecommendBtnVisibility: function() {
		var me = this;
		if (me.recommendBtn) {
			try { // вероятно стоило обработать ошибку возникающую в me.recommendBtn.setVisible как то иначе, но ошибку не удаётся воспроизвести локально, да и на рабочем она плавающая, поэтому обернул в try..catch
				me.recommendBtn.setVisible(
					!me.isReadOnly &&
					!me.editor.xmlData.recommendations
				);
				me.editor.refreshSize();
			} catch(e) {
				log('refreshRecommendBtnVisibility error: ' + e.message, me.recommendBtn);
			}
		}
	},

	setReadOnly: function(isReadOnly) {
		var me = this;
		me.callParent(arguments);
		me.onHeaderOver(false);
		me.refreshRecommendBtnVisibility();
	},

	onHeaderOver: function(over) {
		var me = this;

		if (me.isReadOnly) {
			me.ParamsToolsPanel.hide();
			return;
		}

		var formObject = me.getEl();
		if (
			formObject.parent().el.dom.getAttribute('data-mce-selected') ||
			formObject.component.focused || over
		) {
			me.ParamsToolsPanel.show({target: formObject, align: 'tr-br'});
		} else {
			me.ParamsToolsPanel.hide();
		}
	},

	initComponent: function() {
		var me = this;

		if (me.SpecMarker.isTable == 2) {
			me.autoEl = 'div';
			me.style = 'display: flex;';
		}

		var menu = Ext6.create('Ext6.menu.Menu', {
			cls: me.baseCls+'-sign-menu',
			items: [{
				iconCls: 'panicon-edit',
				text: 'Заменить спецмаркер',
				handler: function() {
					me.replace();
				}
			}, {
				iconCls: 'icon-canbin',
				text: 'Удалить спецмаркер из шаблона',
				handler: function() {
					me.remove();
					me.editor.undoManager.add();
					me.editor.onContentChange();
				}
			}]
		});

		/*me.signMenuBtn = Ext6.create('Ext6.Button', {
			cls: 'sign-menu-btn',
			iconCls: 'icon-specmarker-sign',
			menu: !me.isLocked?menu:null
		});*/

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
			listeners: {
				mouseenter: function(e) {
					me.onHeaderOver(true);
					me.addCls('menu-over');
				},
				mouseleave: function(e) {
					me.onHeaderOver(false);
					me.removeCls('menu-over');
				}
			}
		});

		me.callParent(arguments);
	}
});