Ext6.define('common.XmlTemplate.SaveWindow', {
	extend: 'base.BaseForm',
	alias: 'widget.swXmlTemplateSaveWindow',
	requires: [
		'common.XmlTemplate.models.TreeNode'
	],
	renderTo: main_center_panel.body.dom,
	autoShow: false,
	cls: 'arm-window-new save-template-window arm-window-new-without-padding',
	title: 'Сохранить шаблон',
	width: 640,
	height: 480,
	modal: true,

	save: function() {
		var me = this;
		var baseForm = me.formPanel.getForm();
		var folderNode = me.tree.selection;

		if (!baseForm.isValid()) {
			Ext6.Msg.show({
				buttons: Ext6.Msg.OK,
				fn: function() {
					me.formPanel.getFirstInvalidEl().focus();
				},
				icon: Ext6.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return;
		}
		if (!folderNode || folderNode.get('XmlTemplateCat_id') === undefined) {
			Ext6.Msg.show({
				buttons: Ext6.Msg.OK,
				icon: Ext6.Msg.WARNING,
				msg: 'Не выбрана папка для сохранения шаблона',
				title: ERR_INVFIELDS_TIT
			});
			return;
		}

		me.mask('Сохранение...');

		baseForm.submit({
			url: '/?c=XmlTemplate6E&m=saveXmlTemplate',
			success: function(form, action) {
				me.unmask();

				var response = Ext6.applyIf({
					XmlTemplate_id: action.result.XmlTemplate_id
				}, baseForm.getValues());

				me.callback(response);
				me.hide();
			},
			failure: function(form, action) {
				me.unmask();
			}
		});
	},

	createFolder: function() {
		var me = this;
		var baseForm = me.formPanel.getForm();
		var selection = me.tree.selection;

		var params = {
			LpuSection_id: baseForm.findField('LpuSection_id').getValue()
		};

		if (selection && selection.get('XmlTemplateCat_id')) {
			params.XmlTemplateCat_pid = selection.get('XmlTemplateCat_id');
		}

		getWnd('swXmlTemplateCatEditWindow').show({
			params: params,
			callback: function(data) {
				if (data.XmlTemplateCat_id) {
					me.loadTree();
					me.getFolderPath('own', data.XmlTemplateCat_id, function(path) {
						me.tree.expandPath(path, {select: true, focus: true});
					});
				}
			}
		});
	},

	getNewCaption: function(callback) {
		Ext6.Ajax.request({
			url: '/?c=XmlTemplate6E&m=getNewXmlTemplateCaption',
			success: function(response) {
				var responseObj = Ext6.decode(response.responseText);
				callback(responseObj.caption);
			},
			failure: function(response) {
				callback(null);
			}
		});
	},

	getFolderPath: function(node, folderId, callback) {
		Ext6.Ajax.request({
			url: '/?c=XmlTemplate6E&m=getXmlTemplateCatPath',
			params: {
				node: node,
				XmlTemplateCat_id: folderId
			},
			success: function(response) {
				var responseObj = Ext6.decode(response.responseText);
				callback(responseObj.path);
			}
		});
	},

	loadTree: function(folderId) {
		var me = this;
		var baseForm = me.formPanel.getForm();

		me.tree.getRootNode().removeAll();
		me.tree.getRootNode().set('expanded', false);
		me.tree.getRootNode().set('loaded', false);

		if (!folderId) {
			me.tree.expandPath('/root/own', {select: true, focus: true});
		} else {
			me.getFolderPath('own', folderId, function(path) {
				me.tree.expandPath(path, {select: true, focus: true});
			});
		}
	},

	onSprLoad: function(args) {
		var me = this;
		var baseForm = me.formPanel.getForm();

		if (!args[0] || !args[0].params) {
			return;
		}

		var needLoad = args[0].needLoad || false;
		var params = args[0].params;

		baseForm.findField('XmlTemplateScope_id').store.filterBy(function(record) {
			return record.get('XmlTemplateScope_id').inlist(me.allowedXmlTemplateScopeList);
		});
		baseForm.findField('EvnClass_id').store.filterBy(function(record) {
			return record.get('EvnClass_id').inlist(me.allowedEvnClassList);
		baseForm.findField('EvnClass_id').setReadOnly(me.allowedEvnClassList.length == 1);

		baseForm.findField('XmlType_id').store
			.filterBy((record) => record.get('XmlType_id').inlist(this.allowedXmlTypeEvnClassLink));
		});

		baseForm.setValues(params);
		var folderId = params.XmlTemplateCat_id;

		if (!baseForm.findField('XmlTemplate_id').getValue()) {
			me.setTitle('Сохранить шаблон');

			me.queryById(me.getId()+'-save-btn').enable();

			baseForm.findField('XmlTemplateScope_id').setValue(5);

			if (!baseForm.findField('XmlTemplate_Caption').getValue()) {
				me.mask('Загрузка...');
				me.getNewCaption(function(caption) {
					me.unmask();
					baseForm.findField('XmlTemplate_Caption').setValue(caption);
				});
			}

			me.loadTree(folderId);
		} else {
			me.setTitle('Свойства шаблона');

			if (!needLoad) {
				me.queryById(me.getId()+'-save-btn').setDisabled(
					baseForm.findField('Author_id').getValue() != getGlobalOptions().pmuser_id
				);

				me.loadTree(folderId);
			} else {
				baseForm.load({
					url: '/?c=XmlTemplate6E&m=loadXmlTemplateProperties',
					params: {
						XmlTemplate_id: baseForm.findField('XmlTemplate_id').getValue()
					},
					success: function(response) {
						folderId = baseForm.findField('XmlTemplateCat_id').getValue();

						me.queryById(me.getId()+'-save-btn').setDisabled(
							baseForm.findField('Author_id').getValue() != getGlobalOptions().pmuser_id
						);

						me.loadTree(folderId);
					}
				});
			}
		}
	},

	show: function() {
		var me = this,
			baseForm = me.formPanel.getForm(),
			v,
			field;

		me.formPanel.reset();
		me.tree.getRootNode().removeAll();
		me.callback = Ext6.emptyFn;

		me.allowedXmlTemplateScopeList = [3,4,5];
		me.allowedEvnClassList = [11,13,22,27,29,43,47,160,30,32,120];
		me.allowedXmlTypeEvnClassLink = {
			1:  [160],
			2:  [10,11,13,22,27,29,30,32,43,47,120],
			3:  [10,11,13],
			4:  [10,11,13,22,29,43,47],
			5:  [47],
			8:  [30,32],
			9:  [30,32],
			10: [32],
			11: [120],
			12: [120],
			13: [120],
			14: [120],
			15: [120],
			16: [29,43],
			18: [27],
			19: [27],
			20: [27]
		};

		me.callParent(arguments);

		if (!arguments[0] || !arguments[0].params) {
			me.hide();
			Ext6.Msg.alert(langs('Ошибка'), langs('Не переданы все необходимые параметры'));
			return;
		}

		if (arguments[0].callback) {
			me.callback = arguments[0].callback;
		}

		if (v = arguments[0].params.allowedEvnClassList)
		{
			this.allowedEvnClassList = v;

			field = baseForm.findField('EvnClass_id');
			field.store.filterBy((record) => record.get('EvnClass_id').inlist(this.allowedEvnClassList));
			field.setReadOnly(this.allowedEvnClassList.length == 1);
		}

		if (v = arguments[0].params.allowedXmlTypeEvnClassLink)
		{
			this.allowedXmlTypeEvnClassLink = v;

			baseForm.findField('XmlType_id').store
				.filterBy((record) => record.get('XmlType_id').inlist(this.allowedXmlTypeEvnClassLink));
		}
	},

	initComponent: function() {
		var me = this;
		var labelWidth = 130;

		me.formPanel = Ext6.create('Ext6.form.Panel', {
			border: false,
			bodyPadding: '20 20 0 20',
			trackResetOnLoad: false,
			defaults: {
				anchor: '100%',
				labelWidth: labelWidth,
				matchFieldWidth: false
			},
			url: '/?c=XmlTemplate6E&m=loadXmlTemplateSettings',
			reader: Ext6.create('Ext6.data.reader.Json', {
				type: 'json',
				model: Ext6.create('Ext6.data.Model', {
					fields: [
						{name: 'XmlTemplate_id'},
						{name: 'XmlTemplateCat_id'},
						{name: 'XmlTemplateSettings_id'},
						{name: 'XmlTemplate_HtmlTemplate'},
						{name: 'EvnXml_Data'},
						{name: 'EvnXml_DataSettings'},
						{name: 'MedStaffFact_id'},
						{name: 'MedPersonal_id'},
						{name: 'LpuSection_id'},
						{name: 'XmlTemplate_Caption'},
						{name: 'XmlTemplate_Descr'},
						{name: 'EvnClass_id'},
						{name: 'XmlType_id'},
						{name: 'XmlTemplateScope_id'}
					]
				})
			}),
			items: [{
				xtype: 'hidden',
				name: 'XmlTemplate_id'
			}, {
				xtype: 'hidden',
				name: 'XmlTemplateCat_id'
			}, {
				xtype: 'hidden',
				name: 'Author_id'
			}, {
				xtype: 'hidden',
				name: 'XmlTemplateSettings_id'
			}, {
				xtype: 'hidden',
				name: 'XmlTemplate_HtmlTemplate'
			}, {
				xtype: 'hidden',
				name: 'mode',
				value: 'template'
			}, {
				xtype: 'hidden',
				name: 'EvnXml_Data'
			}, {
				xtype: 'hidden',
				name: 'EvnXml_DataSettings'
			},  {
				xtype: 'hidden',
				name: 'MedStaffFact_id'
			},  {
				xtype: 'hidden',
				name: 'MedPersonal_id'
			},  {
				xtype: 'hidden',
				name: 'LpuSection_id'
			}, {
				allowBlank: false,
				xtype: 'textfield',
				name: 'XmlTemplate_Caption',
				fieldLabel: 'Название шаблона'
			}, {
				xtype: 'textfield',
				name: 'XmlTemplate_Descr',
				fieldLabel: 'Краткое описание',
				maxLength: 300,
				enforceMaxLength: true
			}, {
				allowBlank: false,
				xtype: 'commonSprCombo',
				comboSubject: 'EvnClass',
				name: 'EvnClass_id',
				typeCode: 'int',
				fieldLabel: 'Категория',
				listeners: {
					change: function(combo, newValue, oldValue) {
						var baseForm = me.formPanel.getForm();
						var xmlTypeCombo = baseForm.findField('XmlType_id');
						var XmlType_id = xmlTypeCombo.getValue();

						var allowedXmlTypeList = Object.keys(me.allowedXmlTypeEvnClassLink)
							.map(function(XmlType_id) {
								return Number(XmlType_id);
							}).filter(function(XmlType_id) {
								var EvnClassList = me.allowedXmlTypeEvnClassLink[XmlType_id];
								return Number(newValue).inlist(EvnClassList);
							});

						xmlTypeCombo.store.clearFilter();
						xmlTypeCombo.store.filterBy(function(record) {
							return record.get('XmlType_id').inlist(allowedXmlTypeList);
						});

						if (xmlTypeCombo.store.count() == 0) {
							xmlTypeCombo.setValue(null);
						} else {
							var index = xmlTypeCombo.store.findBy(function(record) {
								return record.get('XmlType_id') == XmlType_id;
							});
							var record = xmlTypeCombo.store.getAt(index);
							if (!record) {
								record = xmlTypeCombo.store.getAt(0);
							}

							xmlTypeCombo.setValue(record.get('XmlType_id'));
						}
					}
				}
			}, {
				allowBlank: false,
				xtype: 'commonSprCombo',
				comboSubject: 'XmlType',
				name: 'XmlType_id',
				typeCode: 'int',
				fieldLabel: 'Тип документа'
			}, {
				allowBlank: false,
				xtype: 'commonSprCombo',
				comboSubject: 'XmlTemplateScope',
				name: 'XmlTemplateScope_id',
				typeCode: 'int',
				fieldLabel: 'Видимость'
			}]
		});

		me.tree = Ext6.create('Ext6.tree.Panel', {
			border: false,
			cls: 'template-tree angle-arrows grey-icons',
			useArrows: true,
			hideHeaders: true,
			rootVisible: false,
			displayField: 'text',
			store: Ext6.create('Ext6.data.TreeStore', {
				autoLoad: false,
				model: 'common.XmlTemplate.models.TreeNode',
				root: {
					leaf: false,
					expanded: false
				},
				sorters: [
					'sort',
					'leaf',
					'text'
				],
				listeners: {
					beforeload: function(store, operation) {
						var node = operation.node;
						var params = operation.getParams();
						var baseForm = me.formPanel.getForm();

						if (node.id == 'root') {
							params.mode = 'own';
						}
						if (node.get('node')) {
							params.node = node.get('node');
						}
						if (node.get('nodeType') == 'FolderNode') {
							params.XmlTemplateCat_id = node.get('XmlTemplateCat_id');
						}
						params.MedPersonal_id = baseForm.findField('MedPersonal_id').getValue();
						params.onlyFolders = true;

						node.lastParams = params;
					}
				}
			}),
			columns: {
				items: [
					{xtype: 'treecolumn', text: 'text', dataIndex: 'text', flex: 1}
				]
			},
			selModel: {
				mode: 'SINGLE',
				listeners: {
					select: function(model, record) {
						var baseForm = me.formPanel.getForm();
						baseForm.findField('XmlTemplateCat_id').setValue(record.get('XmlTemplateCat_id'));
					}
				}
			}
		});

		me.treePanel = Ext6.create('Ext6.Panel', {
			title: 'Выберите расположение',
			layout: 'fit',
			padding: '10 20 20 20',
			cls: 'sw-panel-gray',
			flex: 1,
			header: {
				items: [{
					xtype: 'button',
					cls: 'create-folder-link button-without-frame',
					text: 'Создать новую папку',
					handler: function() {
						me.createFolder();
					}
				}]
			},
			items: me.tree
		});

		Ext6.apply(me, {
			layout: 'vbox',
			defaults: {
				width: '100%'
			},
			items: [
				me.formPanel,
				me.treePanel
			],
			buttons: [
				'->',
				{
					text: 'Отмена',
					userCls: 'buttonCancel',
					margin: 0,
					handler: function() {
						me.hide();
					}
				}, {
					id: me.getId()+'-save-btn',
					cls: 'buttonAccept',
					text: 'Сохранить',
					margin: '0 19 0 0',
					handler: function() {
						me.save();
					}
				}
			]
		});

		me.callParent(arguments);
	}
});