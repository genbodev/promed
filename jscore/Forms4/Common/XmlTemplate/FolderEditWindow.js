Ext6.define('common.XmlTemplate.FolderEditWindow', {
	extend: 'base.BaseForm',
	alias: 'widget.swXmlTemplateFolderEditWindow',
	requires: [
		'common.XmlTemplate.models.TreeNode'
	],
	renderTo: main_center_panel.body.dom,
	autoShow: false,
	cls: 'arm-window-new save-template-window arm-window-new-without-padding',
	title: 'Новая папка',
	width: 500,
	height: 360,
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
				msg: 'Не выбрана родительская папка',
				title: ERR_INVFIELDS_TIT
			});
			return;
		}

		me.mask('Сохранение...');

		baseForm.submit({
			url: '/?c=XmlTemplate6E&m=saveXmlTemplateCat',
			success: function(form, action) {
				me.unmask();

				var response = {
					XmlTemplateCat_id: action.result.XmlTemplateCat_id
				};

				me.callback(response);
				me.hide();
			},
			failure: function(form, action) {
				me.unmask();
			}
		});
	},

	getNewFolderName: function(callback) {
		var me = this;
		var baseForm = me.formPanel.getForm();

		Ext6.Ajax.request({
			url: '/?c=XmlTemplate6E&m=getNewXmlTemplateCatName',
			params: {
				XmlTemplateCat_pid: baseForm.findField('XmlTemplateCat_pid').getValue()
			},
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

		me.mask('Загрузка...');

		if (!folderId) {
			me.tree.expandPath('/root/own', {
				select: true,
				focus: true,
				callback: function() {
					me.unmask();
				}
			});
		} else {
			me.getFolderPath('own', folderId, function(path) {
				me.tree.expandPath(path, {
					select: true,
					focus: true,
					callback: function() {
						me.unmask();
					}
				});
			});
		}
	},

	onSprLoad: function(args) {
		var me = this;
		var baseForm = me.formPanel.getForm();

		if (!args[0] || !args[0].params) {
			return;
		}

		var params = args[0].params;

		baseForm.setValues(params);
		var folderId = params.XmlTemplateCat_pid;

		me.loadTree(folderId);
	},

	show: function() {
		var me = this;

		me.formPanel.reset();
		me.tree.getRootNode().removeAll();
		me.callback = Ext6.emptyFn;
		me.lastDefaultName = '';

		me.callParent(arguments);

		if (!arguments[0] || !arguments[0].params) {
			me.hide();
			Ext6.Msg.alert(langs('Ошибка'), langs('Не переданы все необходимые параметры'));
			return;
		}

		if (arguments[0].callback) {
			me.callback = arguments[0].callback;
		}
	},

	initComponent: function() {
		var me = this;
		var labelWidth = 80;

		me.formPanel = Ext6.create('Ext6.form.Panel', {
			border: false,
			bodyPadding: '20 20 0 20',
			trackResetOnLoad: false,
			defaults: {
				anchor: '100%',
				labelWidth: labelWidth,
				matchFieldWidth: false
			},
			reader: Ext6.create('Ext6.data.reader.Json', {
				type: 'json',
				model: Ext6.create('Ext6.data.Model', {
					fields: [
						{name: 'XmlTemplateCat_id'},
						{name: 'XmlTemplateCat_pid'},
						{name: 'XmlTemplateCat_Name'},
						{name: 'LpuSection_id'}
					]
				})
			}),
			items: [{
				xtype: 'hidden',
				name: 'XmlTemplateCat_id'
			}, {
				xtype: 'hidden',
				name: 'XmlTemplateCat_pid'
			},  {
				xtype: 'hidden',
				name: 'LpuSection_id'
			}, {
				allowBlank: false,
				xtype: 'textfield',
				name: 'XmlTemplateCat_Name',
				fieldLabel: 'Название'
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
						baseForm.findField('XmlTemplateCat_pid').setValue(record.get('XmlTemplateCat_id'));

						var lastName = baseForm.findField('XmlTemplateCat_Name').getValue();

						if (!lastName || lastName == me.lastDefaultName) {
							me.getNewFolderName(function(name) {
								me.lastDefaultName = name;
								baseForm.findField('XmlTemplateCat_Name').setValue(name);
							});
						}
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
					margin: '0 19 0 0',
					text: 'Создать',
					handler: function() {
						me.save();
					}
				}
			]
		});

		me.callParent(arguments);
	}
});