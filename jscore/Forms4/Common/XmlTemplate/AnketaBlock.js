Ext6.define('common.XmlTemplate.AnketaBlock', {
	extend: 'base.EditorBlock',
	xtype: 'xmltemplateanketablock',
	params: null,//?
	anketa_data: null,

	baseCls: 'parameter-block',

	renderTpl: [
		'<div id="{id}-header" class="{baseCls}-header">',
			'<span id="{id}-label" class="{baseCls}-label">{label}</span>',
			'<span id="{id}-delete-btn-wrap" class="{baseCls}-delete-btn-wrap"></span>',
		'</div>',
		'<div id="{id}-field-wrap" class="{baseCls}-field-wrap"></div>'
	],

	inheritableStatics: {
		
		getPlace: function(marker, number) {
			if (number) {
				var matches = /(@#@anketa_)(\d+)/.exec(marker);
				if (matches && matches.length == 3) {
					marker = matches[1]+'_'+matches[2];
				}
			}
			return this.placeTpl.apply({marker: marker});
		},
		
		insertToEditor: function(editor, anketa_info, anketa_data) {
			var blockClass = this;
			var blocks = [];
			var places = [];
			var xmlData = editor.getXmlData();

			editor.xmlData['AnketMarker_'+anketa_info.MedicalFormPerson_id] = Ext6.JSON.encode(anketa_data);
			
			places.push('@#@anketa_'+anketa_info.MedicalFormPerson_id);

			editor.getUndoManager().transact(function() {
				editor.mce.selection.setContent(places.join(''));
				blocks = editor.renderBlocks(true); //Обновляет ВСЕ блоки
			});

			return blocks.filter(function(block) {
				return block.getNick && block.getNick() == 'anketa';
			});
		},

		factory: function(editor, template) {
			var blockClass = this;
			var blocks = [];
			var params = [];
			template = template || editor.getTemplate();

			Object.keys(editor.xmlData).forEach(function(nick_id) {
				
				var arr = nick_id.split('_');
				var nick = arr[0];
				var id = arr[1];
				var data = editor.xmlData[nick_id];

				if (nick != 'AnketMarker') return;

				var params = editor.cache.getData('AnketMarker').find(function(item) {
					return item.MedicalFormPerson_id == id;
				});
				if(Ext6.isEmpty(params)) {
					return;
				}
				
				var block = new blockClass({
					editor: editor,
					params: params,
					anketa_data: new Ext6.util.JSON.decode(data)
				});

				blocks.push(block);
			});

			return blocks;
		}
	},
	
	getPlace: function() {
		var me = this;
		return '@#@anketa_'+me.params.MedicalFormPerson_id;
	},
	
	getNick: function() {
		return 'anketa';
	},
	
	initRenderData: function() {
		var me = this;
		return Ext6.apply(me.callParent(), {
			label: 'Анкета "'+me.params.MedicalForm_Name+'"'
		});
	},

	renderToContainer: function() {
		var me = this;
		var blocks = me.callParent();

		me.field.render(me.getId()+'-field-wrap');
		
		me.getEl().on({
			mouseover: function(event) {
				me.onHeaderOver(true);
			},
			mouseout: function(event) {
				me.onHeaderOver(false);
			},
			click: function(event){
				me.onHeaderOver(true);
			}
		});

		return blocks;
	},
	
	onHeaderOver: function(over) {
		var me = this;

		var formObject = me.getEl();
		if (formObject.parent().el.dom.getAttribute('data-mce-selected') ||
			formObject.component.focused == true ||
			over == true
		) {
			me.ParamsToolsPanel.show({target: formObject, align: 'tr-br', offset: [0, 0]});
		} else {
			me.ParamsToolsPanel.hide();
		}
	},
	
	getXmlDataKey: function() {
		var me = this;
		return 'AnketMarker_'+me.params.MedicalFormPerson_id;
	},
	
	onDestroy: function() {
		var me = this;
		var key = me.getXmlDataKey();

		me.callParent(arguments);

		delete me.editor.xmlData[key];
		me.editor.getUndoManager().add();
		me.editor.onContentChange();
	},
	
	remove: function() {
		var me = this;
		var key = me.getXmlDataKey();

		me.callParent(arguments);

		delete me.editor.xmlData[key];
		me.editor.getUndoManager().add();
		me.editor.onContentChange();
		return me;
	},
	
	afterRender: function() {
		var me = this;
		me.callParent(arguments);
	},
		
	refreshPanel: function(){
		var me = this;
		
		me.questionPanel.removeAll();
		
		var labelTemplate = {
			xtype: 'label',
			text: ''
		};

		if(me.anketa_data){
			me.anketa_data.MedicalForm.forEach(function (el) {
				var items = [];
				items.push({
					xtype: 'label',
					html: el.MedicalFormQuestion_Name+':&nbsp;'
				});
				//---вставка ответа(ов)
				var answer = me.anketa_data.MedicalFormData.find(function(rec) {
					return rec.MedicalFormQuestion_id == el.MedicalFormQuestion_id;
				});
				
				if(el.AnswerType_id == 11){//дата
					items.push({
						xtype: 'label',
						MedicalFormQuestion_id: el.MedicalFormQuestion_id,
						html: answer ? answer.DateValue + ' ' + answer.TimeValue : '' //нестроковое значение в answer.MedicalFormData_ValueDT
					});
				}
				else if(el.AnswerType_id == 2){ //текст
					items.push({
						xtype: 'label',
						MedicalFormQuestion_id: el.MedicalFormQuestion_id,
						html: answer ? answer.MedicalFormData_ValueText : ''
					});
				}
				else{
					if(el && el.children)
						el.children.forEach(function(item, index, arr) {
							items.push({
								xtype: 'label',
								text: item.MedicalFormAnswers_Name,
								MedicalFormAnswers_id: item.MedicalFormAnswers_id,
								style: answer && answer.MedicalFormAnswers_id==item.MedicalFormAnswers_id ? 'text-decoration: underline' : ''
							});
							
							if(index < arr.length-1) {
								items.push({
									xtype: 'label',
									html: ',&nbsp;'
								});
							}
						});
				}
				//---конец ответов
				
				var questionPreview = Ext6.create('Ext6.panel.Panel', {
					cls: 'AnketaMaker-questionPreviewSegment AnketaMaker-one',
					padding: '10 40 10 3',
					margin: 0,
					layout: 'hbox',
					MedicalFormQuestion_id: el.MedicalFormQuestion_id,
					items: items
				});
				
				me.questionPanel.add(questionPreview);
			});
		}
	},

	initComponent: function() {
		var me = this;
		let ParamsToolbar = Ext6.create('Ext6.toolbar.Toolbar', {
			cls: 'editor-table-block-toolbar',
			defaults:{
				width: 16,
				height: 16,
			},
			items:[{
				iconCls: 'icon-table-down',
				tooltip: 'Развернуть',
				text: 'Развернуть',
				margin: '4 2 4 4',
				expanded: false,
				handler: function() {
					if(this.expanded) {//сворачиваем
						me.field.setHeight(me.field.defaultHeight);
						this.setIconCls('icon-table-down');
						this.setTooltip('Развернуть');
					} else {//разворачиваем
						me.field.setHeight(me.questionPanel.getHeight());
						this.setIconCls('icon-table-up');
						this.setTooltip('Свернуть');
					}
					//~ me.editor.setCursorLocation();
					me.ParamsToolsPanel.hide();
					me.editor.onContentChange();
					
					this.expanded = !this.expanded;
				}
			}, {
				iconCls: 'icon-table-up',
				tooltip: 'Переместить выше',
				margin: '4 2 4 4',
				disabled: true,
				handler: function () {
					inDevelopmentAlert();
				}
			}, {
				iconCls: 'icon-table-down',
				tooltip: 'Переместить ниже',
				margin: '4 2 4 2',
				disabled: true,
				handler: function () {
					inDevelopmentAlert();
				}
			}, {
				iconCls: 'icon-table-delete',
				tooltip: 'Удалить анкету',
				margin: '4 4 4 2',
				handler: function() {
					me.ParamsToolsPanel.hide();
					me.remove();
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
				mouseover: function(event) {
					me.onHeaderOver(true);
					me.addCls('menu-over');
				},
				mouseleave: function(event) {
					me.onHeaderOver(false);
					me.removeCls('menu-over');
				}
			}
		});
		
		me.questionPanel = Ext6.create('Ext6.panel.Panel', {
			itemId:'questionPanel',
			width: '100%',
			autoHeight: true,
			border: false,
			scrollable: true,
			preventHeader: true,
			padding: '10 0 20 0',
			cls: 'right-preview'
		});
		
		me.field =  Ext6.create('Ext6.panel.Panel', {
			id: me.getId()+'-anketpanel',
			cls: 'arm-window-new AnketaMaker',
			border: false,
			header: false,
			defaultHeight: 100,
			height: 100,
			items: [
				{
					xtype: 'panel',
					layout: 'vbox',
					border: false,
					scrollable: true,
					cls: 'custom-scroll',
					preventHeader: true,
					items:[
						{
							xtype: 'panel',
							itemId:'questionTitle',
							width: '100%',
							autoHeight: true,
							border: false,
							scrollable: false,
							cls: 'right-header-title'
						},
						me.questionPanel
					]
				}
			]
		});
		
		me.refreshPanel();

		me.callParent(arguments);
	}
});