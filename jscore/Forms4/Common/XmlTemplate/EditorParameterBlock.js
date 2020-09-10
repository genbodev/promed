Ext6.define('common.XmlTemplate.EditorParameterBlock', {
	extend: 'base.EditorBlock',
	xtype: 'xmltemplateparameterblock',

	ParameterValue: {},
	number: null,

	baseCls: 'parameter-block',

	renderTpl: [
		'<div id="{id}-header" class="{baseCls}-header">',
			'<span id="{id}-label" class="{baseCls}-label">{label}</span>',
			'<span id="{id}-delete-btn-wrap" class="{baseCls}-delete-btn-wrap"></span>',
		'</div>',
		'<div id="{id}-field-wrap" class="{baseCls}-field-wrap"></div>'
	],

	inheritableStatics: {
		placeTpl: (function() {
			return new Ext6.Template('{marker}');
		}()),

		getPlace: function(marker, number) {
			if (number) {
				var matches = /(@#@_\d+)([А-яЁё][А-яЁё0-9]+)/.exec(marker);
				if (matches && matches.length == 3) {
					marker = matches[1]+'_'+number+matches[2];
				}
			}
			return this.placeTpl.apply({marker: marker});
		},

		insertToEditor: function(editor, ParameterValues) {
			var blockClass = this;
			var blocks = [];
			var places = [];
			var xmlData = editor.getXmlData();

			var xtypeMap = {
				'1': 'swparametervaluecombo',
				'2': 'swparametervaluecheckboxgroup',
				'3': 'swparametervalueradiogroup'
			};

			if (!Ext6.isArray(ParameterValues)) {
				ParameterValues = [ParameterValues];
			}

			var nicks = ParameterValues.map(function(item) {
				return item.ParameterValue_SysNick;
			});
			var markers = ParameterValues.map(function(item) {
				return item.ParameterValue_Marker;
			});
			var xtypes = ParameterValues.map(function(item) {
				return xtypeMap[item.ParameterValueListType_id];
			});

			nicks.forEach(function(nick, index) {
				var marker = markers[index];
				var number = Object.keys(xmlData).reduce(function(number, key) {
					return key.indexOf(nick) < 0 ? number : ++number;
				}, 1);

				editor.xmlData[nick+'_'+number] = '';
				editor.xmlDataSettings[nick+'_'+number] = {xtype: xtypes[index]};

				places.push(blockClass.getPlace(marker, number));
			});

			if (places.length == 0) {
				return [];
			}

			editor.getUndoManager().transact(function() {
				editor.mce.selection.setContent(places.join(''));
				blocks = editor.renderBlocks(true);		//Обновляет ВСЕ блоки
			});

			return blocks.filter(function(block) {
				return block.getNick && block.getNick().inlist(nicks);
			});
		},

		factory: function(editor, template) {
			var blockClass = this;
			var blocks = [];
			template = template || editor.getTemplate();

			var xtypeMap = {
				swparametervaluecombo: 1,
				swparametervaluecheckboxgroup: 2,
				swparametervalueradiogroup: 3
			};

			Object.keys(editor.xmlData).forEach(function(nick_number) {
				var arr = nick_number.split('_');
				var nick = arr[0];
				var number = arr[1];
				var settings = editor.xmlDataSettings[nick_number];
				var ParameterValue = null;

				if (settings && settings.xtype) {
					var type = xtypeMap[settings.xtype];
					ParameterValue = editor.cache.getData('ParameterValue').find(function(item) {
						return item.ParameterValue_SysNick == nick && item.ParameterValueListType_id == type;
					});
				}

				var value = editor.xmlData[nick_number];

				if (!ParameterValue || value === undefined) return;

				var place = blockClass.getPlace(ParameterValue.ParameterValue_Marker, number);

				if (template.indexOf(place) < 0) return;

				var block = new blockClass({
					editor: editor,
					ParameterValue: ParameterValue,
					number: number
				});

				blocks.push(block);
			});

			return blocks;
		}
	},

	getValue: function() {
		var me = this;

		if (me.getValueListType() == 'checkboxgroup') {
			var obj = me.field.getValue();
			var value = obj[me.getXmlDataKey()];
			if (!value) return null;
			return Ext6.isArray(value)?value:[value];
		}

		if (me.getValueListType() == 'radiogroup') {
			var obj = me.field.getValue();
			return obj[me.getXmlDataKey()] || null;
		}

		return me.field.getValue();
	},

	setValue: function(value) {
		var me = this;

		if (me.getValueListType().inlist(['checkboxgroup','radiogroup'])) {
			var obj = {};
			obj[me.getXmlDataKey()] = Ext6.isArray(value)?value:[value];
			me.field.setValue(obj);
		} else {
			me.field.setValue(Ext6.isArray(value)?value[0]||null:value);
		}
	},

	getXmlDataKey: function() {
		var me = this;
		var nick = me.getNick();
		var number = me.getNumber();
		if (!nick || !number) {
			return null;
		}
		return nick+'_'+number;
	},

	getValueListType: function() {
		var me = this;
		if (!me.ParameterValue) {
			return null;
		}
		return me.ParameterValue.ParameterValueListType_SysNick;
	},

	getValueList: function() {
		var me = this;
		if (!me.ParameterValue && me.ParameterValue.ParameterValueList.length == 0) {
			return null;
		}
		return Ext6.JSON.decode(me.ParameterValue.ParameterValueList);
	},

	getNick: function() {
		var me = this;
		if (!me.ParameterValue) {
			return null;
		}
		return me.ParameterValue.ParameterValue_SysNick;
	},

	getName: function() {
		var me = this;
		if (!me.ParameterValue) {
			return null;
		}
		return me.ParameterValue.ParameterValue_Name;
	},

	getMarker: function() {
		var me = this;
		if (!me.ParameterValue) {
			return null;
		}
		return me.ParameterValue.ParameterValue_Marker;
	},

	getNumber: function() {
		var me = this;
		return me.number;
	},

	getPlace: function() {
		var me = this;
		var marker = me.getMarker();
		var number = me.getNumber();
		if (!marker || !number) return null;
		return me.statics().getPlace(marker, number);
	},

	getHeaderEl: function() {
		var me = this;
		return me.el.down('#'+me.getId()+'-header');
	},

	initRenderData: function() {
		var me = this;

		return Ext6.apply(me.callParent(), {
			label: me.getName() || ''
		});
	},

	refreshXmlData: function() {
		var me = this;
		var key = me.getXmlDataKey();
		var value = me.getValue();
		me.editor.xmlData[key] = Ext6.isArray(value)?value.join(','):value;
		me.editor.getUndoManager().add();
		me.editor.onContentChange();
	},

	setReadOnly: function(isReadOnly) {
		var me = this;
		me.callParent(arguments);
		me.onHeaderOver(false);
		me.field.setDisabled(isReadOnly);
	},

	onFocus: function() {
		var me = this;
		me.callParent(arguments);
		me.field.focus(true);
	},

	onHeaderOver: function(over) {
		var me = this;

		if (me.isReadOnly) {
			me.ParamsToolsPanel.hide();
			return;
		}

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
		if (me.getEl().down('.parameter-block-combo')) {
			me.getEl().down('.parameter-block-combo').on({
				focus: function(event) {
					me.onHeaderOver(true);
				}
			});
		}
		var xmlData = me.editor.xmlData;
		var value = xmlData[me.getXmlDataKey()];
		if (value) {
			me.setValue(String(value).split(','));
		}

		return blocks;
	},

	initComponent: function() {
		var me = this;

		me.field = null;

		/*me.deleteBtn = Ext6.create('Ext6.button.Button', {
			id: me.getId()+'-delete-btn',
			cls: ['cs6', 'sw-tool',
				me.baseCls+'-delete-btn'
			].join(' '),
			iconCls: 'icon-delete',
			tooltip: 'Удалить параметр',
			handler: function() {
				me.remove();
			}
		});*/

		let ParamsToolbar = Ext6.create('Ext6.toolbar.Toolbar', {
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
				iconCls: 'icon-table-delete',
				tooltip: 'Удалить параметр',
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

		var list = me.getValueList();

		switch(me.getValueListType()) {
			case 'combobox':
				var data = list.map(function(item) {
					return {
						ParameterValue_id: item.ParameterValue_id,
						ParameterValue_Name: item.ParameterValue_Name
					};
				});

				me.field = Ext6.create('swBaseCombobox', {
					id: me.getId()+'-combo',
					cls: me.baseCls+'-combo',
					width: 360,
					matchFieldWidth: false,
					forceSelection: true,
					triggerAction: 'all',
					valueField: 'ParameterValue_id',
					displayField: 'ParameterValue_Name',
					queryMode: 'local',
					hideLabel: true,
					store: {
						fields: [
							{name: 'ParameterValue_id', type: 'int'},
							{name: 'ParameterValue_Name', type: 'string'}
						],
						data: data
					},
					listeners: {
						select: function() {
							me.refreshXmlData();
						},
						render: function() {
							me.field.inputEl.dom.setAttribute('contenteditable', 'true');
							me.field.onLoad();
						}
					}
				});
				break;
			case 'checkboxgroup':
				var items = list.map(function(item) {
					return {
						inputValue: item.ParameterValue_id,
						boxLabel: item.ParameterValue_Name,
						name: me.getXmlDataKey(),
						onMouseDown: function(e) {
							var checkbox = this;
							if (checkbox.isDisabled()) return;
							checkbox.setValue(!checkbox.checked);
						},
						listeners: {
							render: function(checkbox) {
								checkbox.el.on({
									mousedown: checkbox.onMouseDown,
									scope: checkbox
								});
							}
						}
					};
				});

				me.field = Ext6.create('Ext6.form.CheckboxGroup', {
					id: me.getId()+'-checkboxgroup',
					cls: me.baseCls+'-checkboxgroup',
					hideLabel: true,
					vertiacal: true,
					columns: 1,
					items: items,
					listeners: {
						change: function() {
							me.refreshXmlData();
						}
					}
				});
				break;
			case 'radiogroup':
				var items = list.map(function(item) {
					return {
						inputValue: item.ParameterValue_id,
						boxLabel: item.ParameterValue_Name,
						name: me.getXmlDataKey(),
						onMouseDown: function(e) {
							var radio = this;
							if (radio.isDisabled()) return;
							radio.setValue(!radio.checked);
						},
						listeners: {
							render: function(radio) {
								radio.el.on({
									mousedown: radio.onMouseDown,
									scope: radio
								});
							}
						}
					};
				});

				me.field = Ext6.create('Ext6.form.RadioGroup', {
					id: me.getId()+'-radiogroup',
					cls: me.baseCls+'-radiogroup',
					hideLabel: true,
					vertiacal: true,
					columns: 1,
					items: items,
					listeners: {
						change: function() {
							me.refreshXmlData();
						}
					}
				});
				break;
		}

		me.callParent(arguments);
	}
});