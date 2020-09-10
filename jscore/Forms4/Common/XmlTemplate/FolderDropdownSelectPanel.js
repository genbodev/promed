Ext6.define('common.XmlTemplate.FolderDropdownSelectPanel', {
	extend: 'base.DropdownPanel',
	width: 400,
	height: 240,
	resizable: false,

	params: {},

	select: function() {
		var me = this;
		var selection = me.tree.selection;
		if (!selection) return;

		me.onSelect({
			XmlTemplateCat_id: selection.get('XmlTemplateCat_id'),
			XmlTemplateCat_pid: selection.get('XmlTemplateCat_pid'),
			XmlTemplateCat_Name: selection.get('XmlTemplateCat_Name')
		});
	},

	createFolder: function() {
		var me = this;
		var selection = me.tree.selection;

		var params = {
			LpuSection_id: me.params.LpuSection_id
		};

		if (selection && selection.get('XmlTemplateCat_id')) {
			params.XmlTemplateCat_pid = selection.get('XmlTemplateCat_id');
		}

		getWnd('swXmlTemplateFolderEditWindow').show({
			params: params,
			callback: function(data) {
				if (data.XmlTemplateCat_id) {
					me.getFolderPath('own', data.XmlTemplateCat_id, function(path) {
						me.tree.expandPath(path, {select: true, focus: true});
					});
					me.onCreateFolder(data);
				}
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

	loadTree: function(folderId, reload) {
		var me = this;

		if (reload) {
			me.tree.getRootNode().removeAll();
			me.tree.getRootNode().set('expanded', false);
			me.tree.getRootNode().set('loaded', false);
			me.force = true;
		}

		if (!folderId) {
			if (me.tree.store.count() == 0 || reload) {
				me.tree.expandPath('/root/own', {select: true, focus: true});
			}
		} else {
			me.getFolderPath('own', folderId, function(path) {
				me.tree.expandPath(path, {select: true, focus: true});
			});
		}
	},

	setParams: function(params) {
		var me = this;
		if (params.XmlTemplateCat_id) {
			me.params.XmlTemplateCat_id = params.XmlTemplateCat_id;
		}
		if (params.LpuSection_id) {
			me.params.LpuSection_id = params.LpuSection_id;
		}
	},

	show: function() {
		var me = this;

		var folderId = null;

		me.force = false;
		me.onSelect = Ext6.emptyFn;
		me.onCreateFolder = Ext6.emptyFn;

		me.callParent(arguments);

		var selectBtn = me.down('button[cls=buttonAccept]');

		if (arguments[0] && arguments[0].onSelect) {
			me.onSelect = arguments[0].onSelect;
		}
		if (arguments[0] && arguments[0].onCreateFolder) {
			me.onCreateFolder = arguments[0].onCreateFolder;
		}
		if (arguments[0] && arguments[0].params) {
			me.setParams(arguments[0].params);
		}
		if (arguments[0] && arguments[0].selectBtnText) {
			selectBtn.setText(arguments[0].selectBtnText);
		} else {
			selectBtn.setText('Выбрать');
		}

		me.loadTree(me.params.XmlTemplateCat_id, true);
	},

	initComponent: function() {
		var me = this;

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

						if (!me.force && Ext6.Object.equals(node.lastParams, params)) {
							return false;
						}

						node.lastParams = params;
						me.force = false;
					}
				}
			}),
			columns: {
				items: [
					{xtype: 'treecolumn', text: 'text', dataIndex: 'text', flex: 1}
				]
			}
		});

		me.panel = Ext6.create('Ext6.Panel', {
			//title: 'Выберите расположение',
			titlePosition: 1,
			layout: 'fit',
			border: false,
			cls: 'sw-panel-white titleless',
			header: {
				items: [{
					xtype: 'button',
					cls: 'create-folder-link button-without-frame',
					text: 'Создать новую папку',
					style: 'left: 0 !important;',
					handler: function() {
						me.createFolder();
					}
				}]
			},
			items: me.tree
		});

		me.buttons = [
			'->',
			{
				text: 'Отмена',
				userCls: 'buttonCancel',
				margin: '0 0 0 0',
				handler: function() {
					me.hide();
				}
			}, {
				cls: 'buttonAccept',
				margin: '0 19 0 0',
				text: 'Выбрать',
				handler: function() {
					me.select();
				}
			}
		];

		me.callParent(arguments);
	}
});