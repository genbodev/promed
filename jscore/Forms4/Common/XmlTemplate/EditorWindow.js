Ext6.define('common.XmlTemplate.EditorWindow', {
	extend: 'base.BaseForm',
	alias: 'widget.swXmlTemplateEditorWindow',
	requires: [
		'common.XmlTemplate.EditorPanel',
		'common.XmlTemplate.models.TreeNode',
		'common.XmlTemplate.FolderDropdownSelectPanel'
	],
	maximized: true,
	autoShow: false,
	cls: 'arm-window-new splitter-border save-template-window',
	title: 'Шаблоны документов',
	renderTo: main_center_panel.body.dom,
	constrain: true,
	header: false,

	params: {},

	apply: function() {
		var me = this;
		var grid = me.templateGrid;
		var tree = me.templateTree;
		var selection = me.getSelection({template: true});

		if (!Ext6.isFunction(me.onSelect) || !selection) {
			return;
		}

		me.onSelect({
			XmlTemplate_id: selection.get('XmlTemplate_id')
		});
		me.hide();
	},

	setParams: function(params) {
		var me = this;
		var baseForm = me.filterPanel.getForm();

		me.params = {
			XmlType_id: params.XmlType_id,
			EvnClass_id: params.EvnClass_id,
			LpuSection_id: params.LpuSection_id,
			MedPersonal_id: params.MedPersonal_id,
			MedStaffFact_id: params.MedStaffFact_id,
			MedService_id: params.MedService_id
		};

		me.XmlTemplate_id = params.XmlTemplate_id;
		me.openShared = params.openShared;

		me.editor.reset();
		me.editor.setParams(me.params);

		baseForm.reset();
		baseForm.setValues(me.params);

		if (me.openShared) {
			me.sectionMenuBar.setActiveItem('own', true);
		} else if (me.XmlTemplate_id) {
			me.sectionMenuBar.setActiveItem('all', true);
		} else {
			me.sectionMenuBar.reset();
		}

		me.getDefault(function(data) {
			if (!data) return;
			if (data.XmlTemplateDefault_id) {
				me.XmlTemplateDefault_id = data.XmlTemplateDefault_id;
				me.defaultXmlTemplate_id = data.XmlTemplate_id;
			} else {
				me.XmlTemplateDefault_id = null;
				me.defaultXmlTemplate_id = null;
			}

			var store = me.getStore();
			if (store) store.each(function(record) {
				if (record.get('XmlTemplate_id')) {
					var isDefault = me.isDefault(record.get('XmlTemplate_id'));
					record.set('XmlTemplate_IsDefault', isDefault?2:1);
				}
			});
		});
	},

	refreshToolbar: function() {
		var me = this;

		//var ARMType = me.ARMType;	//todo: передавать ARMType на форму

		var sectionName = me.sectionMenuBar.getActiveItemName();
		var windowParams = me.params;
		var editorParams = me.editor.params;
		var template = me.getSelection({template: true});
		var folder = me.getSelection({folder: true});
		var pmUser_id = getGlobalOptions().pmuser_id;

		var isOwnFolder = (
			!Ext6.isEmpty(folder) &&
			!String(folder.get('id')).inlist(['root','own','base','common']) &&
			String(folder.get('node')).inlist(['own'])
		);

		var isOwnTemplate = (
			!Ext6.isEmpty(template) && (
				(template.isNode && String(template.get('node')).inlist(['own'])) ||
				(!template.isNode && template.get('Author_id') == pmUser_id)
			)
		);

		var isSharedTemplate = (
			!Ext6.isEmpty(template) &&
			!Ext6.isEmpty(template.get('XmlTemplateShared_id')) &&
			template.get('XmlTemplateShared_id') > 0
		);

		Object.keys(me.toolbarButtons).forEach(function(name) {
			var button = me.toolbarButtons[name];
			var enable = null;
			var visible = null;

			switch(name) {
				case 'createFolder':
				case 'createTemplate':
					break;
				case 'editProperties':
					enable = !Ext6.isEmpty(template);
					break;
				case 'renameTemplate':
					enable = isOwnFolder || isOwnTemplate;
					break;
				case 'moveTemplate':
					enable = isOwnTemplate;
					break;
				case 'copyTemplate':
					enable = isOwnTemplate;
					break;
				case 'shareTemplate':
					enable = isOwnTemplate;
					break;
				case 'deleteItem':
					enable = isOwnFolder || isOwnTemplate || isSharedTemplate;
					break;
				default:
					enable = false;
					break;
			}

			if (enable !== null) {
				button.setDisabled(!enable);
			}
			if (visible !== null) {
				button.setVisible(visible);
			}
		});
	},

	toggleFavorite: function(template) {
		var me = this;

		if (!template || !template.get('XmlTemplate_id')) {
			return;
		}

		var infoMsg = sw4.showInfoMsg({
			panel: me,
			type: 'loading',
			text: 'Сохранение ...',
			hideDelay: null
		});

		Ext6.Ajax.request({
			url: '/?c=XmlTemplate6E&m=toggleXmlTemplateSelected',
			params: {
				XmlTemplate_id: template.get('XmlTemplate_id'),
				MedStaffFact_id: me.params.MedStaffFact_id,
				MedPersonal_id: me.params.MedPersonal_id,
				operation: (template.get('XmlTemplate_IsFavorite') == 2)?'unselect':'select'
			},
			callback: function(options, success, response) {
				if (infoMsg) infoMsg.hide();
				var responseObj = Ext6.decode(response.responseText);

				if (responseObj.success) {
					sw4.showInfoMsg({
						panel: me,
						type: 'success',
						text: 'Данные сохранены'
					});

					var isFavorite = (responseObj.operation == 'select');

					if (me.templateGrid.isVisible() && me.sectionMenuBar.getActiveItemName() == 'favorite') {
						me.templateGrid.store.remove(template);
					} else {
						template.set('XmlTemplate_IsFavorite', isFavorite?2:1);
						template.commit();
					}
				}
			}
		});
	},

	onSaveTemplate: function(action, response) {
		var me = this;

		if (action == 'create' && response) {
			me.XmlTemplate_id = response.XmlTemplate_id;
			me.onChangeSection(me.sectionMenuBar.getActiveItemName());
		}
	},

	onSetDefault: function(response) {
		var me = this;
		var store = me.templateTree.isVisible()?
			me.templateTree.store:me.templateGrid.store;

		if (
			me.editor.params.XmlType_id == me.params.XmlType_id &&
			me.editor.params.EvnClass_id == me.params.EvnClass_id
		) {
			me.XmlTemplateDefault_id = response.XmlTemplateDefault_id;
			me.defaultXmlTemplate_id = response.newXmlTemplate_id;
			store.each(function(record) {
				if (record.get('XmlTemplate_id')) {
					var isDefault = me.isDefault(record.get('XmlTemplate_id'));
					record.set('XmlTemplate_IsDefault', isDefault?2:1);
				}
			});
			//me.refreshToolbar();
		}
	},

	onChangeSection: function(sectionName) {
		var me = this;
		var baseForm = me.filterPanel.getForm();

		me.editor.reset();
		me.editor.setParams(me.params || {});

		me.templateGrid.hide();
		me.templateGrid.store.removeAll();

		me.templateTree.hide();
		me.templateTree.getRootNode().removeAll();

		me.refreshToolbar();

		if (!me.params) return;

		var params = {
			mode: sectionName
		};

		me.sectionContainerPanel.mask('Загрузка...');

		if (sectionName.inlist(['last5Days','favorite'])) {
			me.filterPanel.hide();

			me.templateGrid.store.load({
				params: params,
				callback: function() {
					me.sectionContainerPanel.unmask();
					me.templateGrid.show();
					me.refreshToolbar();
				}
			});
		} else {
			me.filterPanel.show();
			me.templateTree.show();

			Ext6.apply(params, baseForm.getValues());

			me.templateTree.store.load({
				params: params,
				callback: function() {
					if (!me.XmlTemplate_id && !me.XmlTemplateCat_id && !me.openShared) {
						me.templateTree.getRootNode().expand();
						me.sectionContainerPanel.unmask();
					} else if (me.openShared) {
						me.openShared = false;
						me.templateTree.expandPath('/root/shared', {select: true, focus: true, callback: function () {
							me.sectionContainerPanel.unmask();
						}});
					} else if (me.XmlTemplate_id) {
						me.getTemplatePath(me.XmlTemplate_id, function(path) {
							me.XmlTemplateCat_id = null;
							me.XmlTemplate_id = null;
							me.templateTree.expandPath(path, {select: true, focus: true, callback: function () {
								me.sectionContainerPanel.unmask();
							}});
						});
					} else if (me.XmlTemplateCat_id) {
						me.getFolderPath(null, me.XmlTemplateCat_id, function(path) {
							me.XmlTemplateCat_id = null;
							me.templateTree.expandPath(path, {select: true, focus: true, callback: function () {
								me.sectionContainerPanel.unmask();
							}});
						});
					}
				}
			});
		}
	},

	search: function(force) {
		var me = this;
		var baseForm = me.filterPanel.getForm();
		var store = me.templateTree.store;

		var params = baseForm.getValues();
		params.mode = me.sectionMenuBar.getActiveItemName();

		if (force || Ext6.encode(store.lastOptions.params) != Ext6.encode(params)) {
			store.load({params: params});
		}
	},

	loadTemplate: function(XmlTemplate_id, callback) {
		var me = this;
		if (!XmlTemplate_id) return;
		callback = callback || Ext6.emptyFn;

		me.editor.setParams({
			XmlTemplate_id: XmlTemplate_id
		});
		me.editor.load({
			resetState: true,
			resetTemplate: true,
			callback: function() {
				me.editor.setParams({
					XmlTemplate_IsDefault: me.isDefault(XmlTemplate_id)?2:1
				});
				me.refreshToolbar();
				callback();
			}
		});
	},

	createFolder: function() {
		var me = this;
		var selection = me.getSelection();
		var params = {
			LpuSection_id: me.params.LpuSection_id
		};

		if (selection && selection.isNode && selection.get('XmlTemplateCat_id')) {
			params.XmlTemplateCat_pid = selection.get('XmlTemplateCat_id');
		}

		getWnd('swXmlTemplateFolderEditWindow').show({
			params: params,
			callback: function(data) {
				if (me.templateTree.isVisible() && data.XmlTemplateCat_id) {
					me.XmlTemplateCat_id = data.XmlTemplateCat_id;
					me.onChangeSection(me.sectionMenuBar.getActiveItemName());
				}
			}
		});
	},

	createTemplate: function() {
		var me = this;

		me.templateGrid.selModel.deselectAll();
		me.templateTree.selModel.deselectAll();

		me.editor.createTemplate(me.params, function() {
			me.refreshToolbar();
		});
	},

	editProperties: function() {
		var me = this;
		var template = me.getSelection({template: true});
		if (!template) return;

		var params = {
			XmlTemplate_id: template.get('XmlTemplate_id')
		};

		getWnd('swXmlTemplateSaveWindow').show({
			needLoad: true,
			params: params,
			callback: function(data) {

			}
		});
	},

	renameTemplate: function() {
		var me = this;
		var selection = me.getSelection();

		if (!selection || !selection.get('XmlTemplate_id') && !selection.get('XmlTemplateCat_id')) {
			return;
		}

		var params = {};

		if (selection.get('XmlTemplate_id')) {
			params.XmlTemplate_id = selection.get('XmlTemplate_id');
			params.name = selection.get('XmlTemplate_Caption');
		} else {
			params.XmlTemplateCat_id = selection.get('XmlTemplateCat_id');
			params.name = selection.get('XmlTemplateCat_Name');
		}

		getWnd('swXmlTemplateRenameWindow').show({
			params: params,
			callback: function(data) {
				if (selection.get('XmlTemplate_id')) {
					selection.set('XmlTemplate_Caption', data.name);
				} else {
					selection.set('XmlTemplateCat_Name', data.name);
				}
				if (selection.isNode) {
					selection.set('text', data.name);
				}
				selection.commit();
			}
		});
	},

	moveTemplate: function() {
		var me = this;
		var template = me.getSelection({template: true});

		if (!me.selectFolderDropdown) me.selectFolderDropdown = Ext6.create(
			'common.XmlTemplate.FolderDropdownSelectPanel'
		);

		var moveTemplate = function(data) {
			me.mask('Перемещение...');
			Ext6.Ajax.request({
				url: '/?c=XmlTemplate6E&m=moveXmlTemplate',
				params: {
					XmlTemplate_id: template.get('XmlTemplate_id'),
					XmlTemplateCat_id: data.XmlTemplateCat_id
				},
				callback: function(options, success, response) {
					me.unmask();
					var responseObj = Ext6.decode(response.responseText);

					if (responseObj.success) {
						me.XmlTemplate_id = template.get('XmlTemplate_id');
						me.onChangeSection(me.sectionMenuBar.getActiveItemName());
					}
				}
			});
		};

		var msgTpl = new Ext6.XTemplate('Перенести шаблон "{template}" в папку "{folder}"?');

		me.selectFolderDropdown.show({
			align: 'tr-br?',
			offset: [0, 5],
			target: me.toolbarButtons.moveTemplate,
			selectBtnText: 'Переместить',
			params: {
				XmlTemplateCat_id: template.get('XmlTemplateCat_id'),
				LpuSection_id: me.params.LpuSection_id
			},
			onSelect: function (data) {
				me.selectFolderDropdown.hide();

				if (data.XmlTemplateCat_id == template.get('XmlTemplateCat_id')) {
					return;
				}

				Ext6.Msg.show({
					title: langs('Перемещение'),
					icon: Ext6.MessageBox.QUESTION,
					msg: msgTpl.apply({
						template: template.get('XmlTemplate_Caption'),
						folder: data.XmlTemplateCat_Name
					}),
					buttons: Ext6.Msg.YESNO,
					fn: function (buttonId) {
						if ('yes' == buttonId) {
							moveTemplate(data)
						}
					}
				});
			},
			onCreateFolder: function (data) {
				if (me.templateTree.isVisible() && data.XmlTemplateCat_id) {
					me.XmlTemplate_id = template.get('XmlTemplate_id');
					me.onChangeSection(me.sectionMenuBar.getActiveItemName());
				}
			}
		});
	},

	copyTemplate: function() {
		var me = this;
		var template = me.getSelection({template: true});

		if (!me.selectFolderDropdown) me.selectFolderDropdown = Ext6.create(
			'common.XmlTemplate.FolderDropdownSelectPanel'
		);

		var moveTemplate = function(data) {
			me.mask('Перемещение...');
			Ext6.Ajax.request({
				url: '/?c=XmlTemplate6E&m=copyXmlTemplate',
				params: {
					XmlTemplate_id: template.get('XmlTemplate_id'),
					XmlTemplateCat_id: data.XmlTemplateCat_id
				},
				callback: function(options, success, response) {
					me.unmask();
					var responseObj = Ext6.decode(response.responseText);

					if (responseObj.success) {
						me.XmlTemplate_id = responseObj.XmlTemplate_id;
						me.onChangeSection(me.sectionMenuBar.getActiveItemName());
					}
				}
			});
		};

		var msgTpl = new Ext6.XTemplate('Копировать шаблон "{template}" в папку "{folder}"?');

		me.selectFolderDropdown.show({
			align: 'tr-br?',
			offset: [0, 5],
			target: me.toolbarButtons.copyTemplate,
			selectBtnText: 'Копировать',
			params: {
				XmlTemplateCat_id: template.get('XmlTemplateCat_id'),
				LpuSection_id: me.params.LpuSection_id
			},
			onSelect: function(data) {
				me.selectFolderDropdown.hide();

				Ext6.Msg.show({
					title: langs('Копирование'),
					icon: Ext6.MessageBox.QUESTION,
					msg: msgTpl.apply({
						template: template.get('XmlTemplate_Caption'),
						folder: data.XmlTemplateCat_Name
					}),
					buttons: Ext6.Msg.YESNO,
					fn: function (buttonId) {
						if ('yes' == buttonId) {
							moveTemplate(data)
						}
					}
				});
			},
			onCreateFolder: function (data) {
				if (me.templateTree.isVisible() && data.XmlTemplateCat_id) {
					me.XmlTemplate_id = template.get('XmlTemplate_id');
					me.onChangeSection(me.sectionMenuBar.getActiveItemName());
				}
			}
		});
	},

	shareTemplate: function() {
		var me = this;
		var template = me.getSelection({template: true});

		getWnd('swXmlTemplateShareWindow').show({
			XmlTemplate_id: template.get('XmlTemplate_id')
		});
	},

	setTemplateSharedIsReaded: function(template) {
		var me = this;
		if (!template || !template.get('XmlTemplate_id')) {
			return;
		}
		if (!template.get('XmlTemplateShared_id') || template.get('XmlTemplateShared_IsReaded') == 2) {
			return;
		}

		Ext6.Ajax.request({
			url: '/?c=XmlTemplate6E&m=setXmlTemplateSharedIsReaded',
			params: {
				XmlTemplate_id: template.get('XmlTemplate_id')
			},
			callback: function(options, success, response) {
				var responseObj = Ext6.decode(response.responseText);
				if (responseObj.success) {
					template.set('XmlTemplateShared_IsReaded', 2);
					template.commit();
					getXmlTemplateSharedUnreadCount();
				}
			}
		});
	},

	deleteItem: function() {
		var me = this;
		var template = me.getSelection({template: true});
		var folder = me.getSelection({folder: true});

		var record = null;
		var message = null;
		var url = null;
		var params = {};

		switch(true) {
			case Boolean(template && template.get('XmlTemplateShared_id')):
				record = template;
				message = '<span class="msg-alert-text">Удалить выбранный шаблон?</span>';
				url = '/?c=XmlTemplate6E&m=deleteXmlTemplateShared';
				params.XmlTemplateShared_id = record.get('XmlTemplateShared_id');
				break;

			case Boolean(template && template.get('XmlTemplate_id')):
				record = template;
				message = '<span class="msg-alert-text">Удалить выбранный шаблон?</span>';
				url = '/?c=XmlTemplate6E&m=deleteXmlTemplate';
				params.XmlTemplate_id = record.get('XmlTemplate_id');
				break;

			case Boolean(folder && folder.get('XmlTemplateCat_id')):
				record = folder;
				message = '<span class="msg-alert-text">Удалить выбранную папку?</span>';
				url = '/?c=XmlTemplate6E&m=deleteXmlTemplateCat';
				params.XmlTemplateCat_id = record.get('XmlTemplateCat_id');
				break;

			default: return;
		}

		var onDeleteFn = function() {
			if (record.get('XmlTemplate_id') == me.editor.params.XmlTemplate_id) {
				me.editor.reset();
			}

			if (record.isNode) {
				var parentNode = record.parentNode;
				parentNode.removeChild(record);
				parentNode.refreshChildrenCount();
			} else {
				me.templateGrid.store.remove(record);
			}
			me.refreshToolbar();
		};

		var deleteFn = function() {
			me.mask('Удаление...');
			Ext6.Ajax.request({
				url: url,
				params: params,
				callback: function(opt, success, response) {
					me.unmask();
					var responseObj = Ext6.decode(response.responseText);

					if (responseObj.success) {
						onDeleteFn();
					}
				}
			})
		};

		sw.swMsg.show({
			buttons: sw.swMsg.OKCANCEL,
			buttonText:{
				ok: 'Удалить',
				cancel: 'Отмена'
			},
			cls: 'alert-window-message',
			width: 338,
			height: 182,
			msg: message,
			icon: 'question-image',
			fn: function(btn) {
				if (btn === 'ok') {
					deleteFn();
				}
			}
		});
	},

	isDefault: function(XmlTemplate_id) {
		var me = this;
		return (XmlTemplate_id == me.defaultXmlTemplate_id);
	},

	getDefault: function(callback) {
		callback = callback || Ext6.emptyFn;
		var me = this;

		var params = {
			XmlType_id: me.params.XmlType_id,
			EvnClass_id: me.params.EvnClass_id,
			LpuSection_id: me.params.LpuSection_id,
			MedPersonal_id: me.params.MedPersonal_id,
			MedStaffFact_id: me.params.MedStaffFact_id,
			MedService_id: me.params.MedService_id
		};

		Ext6.Ajax.request({
			url: '/?c=XmlTemplateDefault&m=getXmlTemplateId',
			params: params,
			callback: function(options, success, response) {
				var responseObj = Ext6.decode(response.responseText);
				if (Ext6.isArray(responseObj) && responseObj.length > 0) {
					callback(responseObj[0]);
				} else {
					callback(null);
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

	getTemplatePath: function(templateId, callback) {
		var me = this;

		Ext6.Ajax.request({
			url: '/?c=XmlTemplate6E&m=getXmlTemplatePath',
			params: {
				mode: me.sectionMenuBar.getActiveItemName(),
				XmlTemplate_id: templateId
			},
			success: function(response) {
				var responseObj = Ext6.decode(response.responseText);
				callback(responseObj.path);
			}
		});
	},

	getStore: function() {
		var me = this;
		if (me.templateGrid.isVisible()) {
			return me.templateGrid.store;
		}
		if (me.templateTree.isVisible()) {
			return me.templateTree.store;
		}
		return null;
	},

	getSelection: function(options) {
		var me = this;
		var grid = me.templateGrid;
		var tree = me.templateTree;
		var selection = null;

		if (!options) {
			options = {template: true, folder: true}
		} else {
			options = Ext6.apply({template: false, folder: false}, options);
		}

		if (options.template && grid.isVisible() && grid.selection && grid.selection.get('XmlTemplate_id')) {
			selection = grid.selection;
		}
		if (options.template && tree.isVisible() && tree.selection && tree.selection.isLeaf() &&  tree.selection.get('XmlTemplate_id')) {
			selection = tree.selection;
		}
		if (options.folder && tree.isVisible() && tree.selection && !tree.selection.isLeaf()) {
			selection = tree.selection;
		}

		return selection;
	},

	onSprLoad: function()
	{
// Хранилища комбобоксов еще не прогружены, ждем:
		Ext6.defer(this._doOnSprLoad, 1, this);
	},

	_doOnSprLoad: function() {
		var me = this;
		var baseForm = me.filterPanel.getForm();
		baseForm.setValues(baseForm.getValues());

		var cmbEvnClass = baseForm.findField('EvnClass_id');
		cmbEvnClass.setVisible(cmbEvnClass.store.getCount() != 1);
	},

	show: function() {
		var me = this;
		var baseForm = me.filterPanel.getForm(),
			cmbEvnClass = baseForm.findField('EvnClass_id');

		me.onSelect = null;
		me.XmlTemplateDefault_id = null;
		me.defaultXmlTemplate_id = null;

		me.allowedEvnClassList = [
			11,13,22,27,29,43,47,160,30,32,120
		];
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

		var params = arguments[0];
		if (params.onSelect) {
			me.onSelect = params.onSelect;
		}

		if (params.allowedEvnClassList)
			this.allowedEvnClassList = params.allowedEvnClassList;

		if (params.allowedXmlTypeEvnClassLink)
			this.allowedXmlTypeEvnClassLink = params.allowedXmlTypeEvnClassLink;

		this.allowedXmlTypeKind = params.allowedXmlTypeKind || {};

		me.editor.allowedEvnClassList = this.allowedEvnClassList;
		me.editor.allowedXmlTypeEvnClassLink = this.allowedXmlTypeEvnClassLink;

		me.footer.setVisible(Ext6.isFunction(me.onSelect));

		me.editor.afterInitEditor(function() {
			me.loaded = false;
			me.setParams(params);

			cmbEvnClass.store.filterBy(function(record) {
				return record.get('EvnClass_id').inlist(me.allowedEvnClassList);
			});

			cmbEvnClass.setVisible(cmbEvnClass.store.getCount() != 1);

				me.onChangeSection(me.sectionMenuBar.getActiveItemName());
		});
	},

	initComponent: function() {
		var me = this;

		me.sectionMenuBar = Ext6.create('sw.toolbar.VerticalMenuBar', {
			width: 150,
			activeItem: 'own',
			borderSides: 'right',
			items: [{
				name: 'last5Days',
				text: 'Последние',
				tooltip: 'Cозданные или измененные шаблоны пользователя за последние 5 дней'
			}, {
				name: 'favorite',
				text: 'Избранные'
			}, '-', {
				name: 'validation',
				text: 'На проверку',
				hidden: true	//todo: доступно для АРМ админа МО с настройкой "Проверять шаблоны перед публикацией в папке "Общие""
			}, {
				name: 'all',
				text: 'Все',
				descr: 'Мои, базовые, общие'
			}, {
				name: 'own',
				text: 'Мои',
				descr: 'Созданные мной'
			}, {
				name: 'base',
				text: 'Базовые',
				descr: 'Образцовые шаблоны'
			}, {
				name: 'common',
				text: 'Общие',
				descr: 'В открытом доступе'
			}],
			listeners: {
				activeitemchange: function(comp, item) {
					if (me.editor.isInitEditor) {
						me.onChangeSection(item.name);
					}
				}
			}
		});

		var templateInfoTpl = new Ext6.XTemplate(
			'<p><span style=\'color: #000\'>Категория: </span><span style=\'color: #666\'>{EvnClass_Name}</span></p>',
			'<p><span style=\'color: #000\'>Тип: </span><span style=\'color: #666\'>{XmlType_Name}</span></p>',
			'<p><span style=\'color: #000\'>Видимость: </span><span style=\'color: #666\'>{XmlTemplateScope_Name}</span></p>',
			'<tpl if="Author_Fin">',
			'<p><span style=\'color: #000\'>Автор: </span><span style=\'color: #666\'>{Author_Fin}</span></p>',
			'</tpl>',
			'<tpl if="XmlTemplate_Descr">',
			'<p><span style=\'color: #000\'>Краткое описание: </span><span style=\'color: #666\'>{XmlTemplate_Descr}</span></p>',
			'</tpl>'
		);
		var tooltipTpl = new Ext6.XTemplate(
			'data-qtip="{tooltip}"'
		);
		var textTpl = new Ext6.XTemplate(
			'<tpl if="XmlTemplate_IsDefault == 2"><b></tpl>',
			'<span style="color: #f44336;">{subtext1}</span>{subtext2}',
			'<tpl if="XmlTemplate_IsDefault == 2"></b></tpl>'
		);
		var textRenderer = function(value, meta, record) {
			if (Ext6.isEmpty(value)) {
				return '';
			}

			if (record.get('XmlTemplate_id')) {
				var tooltip = templateInfoTpl.apply(record.data);
				meta.tdAttr = tooltipTpl.apply({tooltip: tooltip});
			}

			var baseForm = me.filterPanel.getForm();
			var query = baseForm.findField('query').getValue() || '';

			if (!query || !record.isNode || !record.parentNode.isRoot()) {
				return (record.get('XmlTemplate_IsDefault') == 2)?'<b>'+value+'</b>':value;
			}

			var text = value;
			var subtext1 = '';
			var subtext2 = text;

			if (query.length > 0) {
				var l = query.length;
				var s1 = text.slice(0, l);
				var s2 = text.slice(l);

				if (s1.toLowerCase() == query.toLowerCase()) {
					subtext1 = s1;
					subtext2 = s2;
				}
			}

			return textTpl.apply({
				subtext1: subtext1,
				subtext2: subtext2
			});
		};

		me.templateGrid = Ext6.create('Ext6.grid.Panel', {
			border: false,
			emptyText: 'По заданным параметрам шаблоны не найдены. Измените условия поиска.',
			userCls: 'template-search-grid borderless',
			store: {
				fields: [
					{name: 'XmlTemplate_id', type: 'int'},
					{name: 'XmlTemplate_Caption', type: 'string'},
					{name: 'XmlTemplate_Descr', type: 'string'},
					{name: 'XmlTemplate_IsFavorite', type: 'int'},
					{name: 'XmlTemplate_IsDefault', type: 'int'},
					{name: 'Author_id', type: 'int'},
					{name: 'Author_Fin', type: 'string'},
					{name: 'XmlType_id', type: 'int'},
					{name: 'XmlType_Name', type: 'string'},
					{name: 'EvnClass_id', type: 'int'},
					{name: 'EvnClass_SysNick', type: 'string'},
					{name: 'EvnClass_Name', type: 'string'},
					{name: 'XmlTemplateScope_id', type: 'int'},
					{name: 'XmlTemplateScope_Name', type: 'string'},
					{name: 'XmlTemplateShared_id', type: 'int'},
					{name: 'XmlTemplateShared_IsReaded', type: 'int'}
				],
				proxy: {
					type: 'ajax',
					url: '/?c=XmlTemplate6E&m=loadXmlTemplateList',
					reader: {type: 'json'}
				},
				sorters: [
					'XmlTemplate_Caption'
				],
				listeners: {
					beforeload: function(store, operation) {
						var baseForm = me.filterPanel.getForm();
						var node = operation.node;
						var params = operation.getParams();

						Ext6.applyIf(params, baseForm.getValues());
						Ext6.applyIf(params, me.params);

						if (params.query == null || params.query == 'null') {
							delete params.query;
						}
					},
					load: function(store) {
						store.each(function(record) {
							if (record.get('XmlTemplate_id')) {
								var isDefault = me.isDefault(record.get('XmlTemplate_id'));
								record.set('XmlTemplate_IsDefault', isDefault?2:1);
							}
						});
					}
				}
			},
			columns: [
				{dataIndex: 'XmlTemplate_IsFavorite', xtype: 'actioncolumn', width: 30, align: 'end',
					items: [{
						getClass: function(value) {
							return (value == 2)
								?'icon-star-active'
								:'icon-star';
						},
						getTip: function(value) {
							return (value == 2)
								?'Убрать из избранных'
								:'Добавить в избранное';
						},
						handler: function(grid, rowIndex, colIndex, item, e, record) {
							me.toggleFavorite(record);
						}
					}]
				},
				{dataIndex: 'XmlTemplate_Caption', flex: 1, renderer: textRenderer}
			],
			selModel: {
				mode: 'SINGLE',
				listeners: {
					select: function(model, record) {
						me.templateGrid.setSelection(record);
						me.refreshToolbar();
						if (record.get('XmlTemplate_id')) {
							me.loadTemplate(record.get('XmlTemplate_id'));
						}
					}
				}
			},
			listeners: {
				itemdblclick: function() {
					me.apply();
				}
			}
		});

		me.templateTree = Ext6.create('Ext6.tree.Panel', {
			border: false,
			cls: 'template-tree angle-arrows grey-icons',
			useArrows: true,
			rootVisible: false,
			hideHeaders: true,
			displayField: 'text',
			store: Ext6.create('Ext6.data.TreeStore', {
				autoLoad: false,
				parentIdProperty: 'parentId',
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
						var baseForm = me.filterPanel.getForm();
						var node = operation.node;
						var params = operation.getParams();

						Ext6.applyIf(params, baseForm.getValues());
						Ext6.applyIf(params, me.params);

						if (params.query == null || params.query == 'null') {
							delete params.query;
						}
						if (node.id == 'root' && params.mode != 'all') {
							params.node = params.mode;
						}
						if (node.id == 'shared') {
							params.node = 'shared';
						}
						if (node.get('node')) {
							params.node = node.get('node');
						}
						if (node.get('nodeType') == 'FolderNode') {
							params.XmlTemplateCat_id = node.get('XmlTemplateCat_id');
						}
						if (node.get('LpuSection_id')) {
							params.LpuSection_sid = node.get('LpuSection_id');
						}
						if (node.get('MedPersonal_id')) {
							params.MedPersonal_sid = node.get('MedPersonal_id');
						}

						params.XmlTypeKind_id = me.allowedXmlTypeKind[params.EvnClass_id + '.' + params.XmlType_id];
					},
					load: function(store, nodes) {
						var forRemove = [];
						store.each(function(node) {
							var parent = node.parentNode;
							if (!parent.isRoot() && store.getById(parent.id) != parent) {
								forRemove.push(node);
							}
							if (node.get('XmlTemplate_id')) {
								var isDefault = me.isDefault(node.get('XmlTemplate_id'));
								node.set('XmlTemplate_IsDefault', isDefault?2:1);
							}
						});
						forRemove.forEach(function(node) {
							node.removeAll();
							node.remove();
							store.remove(node);
						});
					}
				}
			}),
			columns: {
				items: [
					{xtype: 'treecolumn', text: 'text', dataIndex: 'text', flex: 1, renderer: textRenderer},
					{dataIndex: 'XmlTemplate_IsFavorite', xtype: 'actioncolumn', width: 30, align: 'end',
						items: [{
							getClass: function(value, meta, record) {
								if (record.get('XmlTemplate_id')) {
									return (value == 2)?'icon-star-active':'icon-star';
								}
								return 'x-hide-display';
							},
							getTip: function(value) {
								return (value == 2)
									?'Убрать из избранных'
									:'Добавить в избранное';
							},
							handler: function(grid, rowIndex, colIndex, item, e, record) {
								me.toggleFavorite(record);
							}
						}]
					}
				]
			},
			selModel: {
				mode: 'SINGLE',
				listeners: {
					select: function(model, record) {
						me.templateTree.setSelection(record);
						me.refreshToolbar();
						if (record.get('XmlTemplate_id')) {
							me.loadTemplate(record.get('XmlTemplate_id'), function() {
								me.setTemplateSharedIsReaded(record);
							});
						}
					}
				}
			},
			listeners: {
				itemdblclick: function() {
					me.apply();
				},
				itemcollapse: function(node) {
					var selection = me.templateTree.selection;
					var selectionPath = selection?selection.getPath():'';
					var collapsedPath = node.getPath();

					if (selectionPath.indexOf(collapsedPath) === 0) {
						me.templateTree.selModel.deselectAll();
						me.refreshToolbar();
					}
				}
			}
		});

		me.templateSearchField = Ext6.create('swBaseCombobox', {
			triggerAction: 'all',
			displayField: 'XmlTemplate_Caption',
			valueField: 'XmlTemplate_id',
			queryMode: 'remote',
			name: 'query',
			emptyText: 'Поиск шаблона',
			minChars: 1,
			forceSelection: true,
			enableKeyEvents: true,

			tpl: new Ext6.XTemplate(
				'<tpl for="."><div class="x6-boundlist-item" style="padding: 7px 10px 6px 20px;">',
				'<p style="line-height: 16px;">{[this.renderCaption(values.XmlTemplate_Caption)]}</p>',
				'<p style="font-size: 11px; line-height: 17px; color: #666;">{XmlTemplate_PathText}</p>',
				'</div></tpl>',
				{
					renderCaption: function(caption) {
						var query = this.field.getRawValue();
						var queryStart = caption.toLowerCase().indexOf(query.toLowerCase());

						/*return '<span style="color: red;">'+caption.slice(0, query.length)+'</span>'+
							'<span style="font-weight: 500;">'+caption.slice(query.length)+'</span>';*/
						
						return '<span style="font-weight: 500;">'+caption.slice(0, queryStart)+'</span>' + 
							'<span style="color: red;">'+caption.slice(queryStart, queryStart + query.length)+'</span>'+
							'<span style="font-weight: 500;">'+caption.slice(queryStart + query.length)+'</span>';
					}
				}
			),

			store: Ext6.create('Ext6.data.Store', {
				fields: [
					{name: 'XmlTemplate_id'},
					{name: 'XmlTemplate_Cpation'},
					{name: 'XmlTemplate_Path'},
					{name: 'XmlTemplate_PathText'}
				],
				proxy: {
					type: 'ajax',
					url: '/?c=XmlTemplate6E&m=loadXmlTemplateComboList',
					reader: {type: 'json'}
				},
				listeners: {
					beforeload: function(store, operation) {
						var baseForm = me.filterPanel.getForm();
						var params = operation.getParams();

						if (!params.query && me.templateSearchField.getRawValue()) {
							params.query = me.templateSearchField.getRawValue();
						}

						params.mode = me.sectionMenuBar.getActiveItemName();
						params.EvnClass_id = baseForm.findField('EvnClass_id').getValue();
						params.XmlType_id = baseForm.findField('XmlType_id').getValue();
						params.LpuSection_id = me.params.LpuSection_id;
					}
				}
			}),

			refreshTrigger: function(value) {
				var me = this;
				var isEmpty = Ext6.isEmpty(value || me.getValue());
				me.triggers.clear.setVisible(!isEmpty);
				me.triggers.search.setVisible(isEmpty);
			},

			triggers: {
				picker: {
					hidden: true
				},
				search: {
					cls: 'x6-form-search-trigger',
					handler: function() {
						me.search(true);
					}
				},
				clear: {
					cls: 'sw-clear-trigger',
					hidden: true,
					handler: function() {
						me.templateSearchField.setValue(null);
						me.templateSearchField.refreshTrigger();
					}
				}
			},

			listeners: {
				afterrender: function(combo) {
					combo.refreshTrigger();
				},
				keyup: function(combo, e) {
					if (e.getKey() == e.ENTER && !combo.isExpanded) {
						var path = combo.getFieldValue('XmlTemplate_Path');
						if (!Ext6.isEmpty(path)) {
							me.templateTree.expandPath(path, {select: true, focus: true});
						} else {
							me.search(true);
						}
					}
				},
				change: function(combo, newValue, oldValue) {
					combo.refreshTrigger(newValue);

					var path = combo.getFieldValue('XmlTemplate_Path');
					if (!Ext6.isEmpty(path)) {
						me.templateTree.expandPath(path, {select: true, focus: true});
					}
				}
			}
		});

		me.filterPanel = Ext6.create('Ext6.form.Panel', {
			hidden: true,
			width: '100%',
			minHeight: 80,
			bodyStyle: 'border-width: 0px 0px 2px 0; padding: 10px 12px 0 12px;',
			defaults: {
				anchor: '100%',
				matchFieldWidth: false
			},
			items: [/*{
				xtype: 'swqueryfield',
				name: 'query',
				emptyText: 'Поиск шаблона',
				query: me.search.bind(me)
			},*/me.templateSearchField, {
				xtype: 'fieldset',
				title: 'Фильтры',
				cls: 'sw-fieldset',
				collapsible: true,
				collapsed: true,
				defaults: {
					anchor: '100%',
					matchFieldWidth: false,
					labelWidth: 70,
					cls: 'sw-trigger-field'
				},
				items: [{
					xtype: 'commonSprCombo',
					comboSubject: 'EvnClass',
					name: 'EvnClass_id',
					sortField: 'EvnClass_Code',
					typeCode: 'int',
					displayCode: false,
					fieldLabel: 'Категория',
					listConfig: {
						cls: 'choose-bound-list-menu update-scroller'
					},
					listeners: {
						select: function() {
							me.search();
						},
						change: function(combo, newValue, oldValue) {
							var baseForm = me.filterPanel.getForm();
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
					xtype: 'commonSprCombo',
					comboSubject: 'XmlType',
					name: 'XmlType_id',
					sortField: 'XmlType_Code',
					typeCode: 'int',
					displayCode: false,
					fieldLabel: 'Тип',
					listeners: {
						select: function() {
							me.search();
						}
					}
				}]
			}]
		});

		me.sectionPanel = Ext6.create('Ext6.Panel', {
			layout: 'fit',
			style:{
				'border-top': '1px solid #adadad'
			},
			border: false,
			flex: 1,
			width: '100%',
			items: [
				me.templateGrid,
				me.templateTree
			]
		});

		me.sectionContainerPanel = Ext6.create('Ext6.Panel', {
			region: 'west',
			animCollapse: true,
			collapsible: true,
			split: true,
			header: false,
			width: 450,
			maxWidth: 550,
			title:{
				text: 'ШАБЛОНЫ',
				style:{'fontSize':'14px', 'fontWeight':'500'},
				rotation: 2,
				textAlign: 'right'
			},
			layout: 'hbox',
			defaults: {
				height: '100%',
				border: false
			},
			items: [
				me.sectionMenuBar,
				{
					layout: 'vbox',
					flex: 1,
					width: '100%',
					items: [
						me.filterPanel,
						me.sectionPanel
					]
				}
			]
		});

		me.editor = Ext6.create('common.XmlTemplate.EditorPanel', {
			style: 'border-width: 0;',
			headerHidden: false,
			footerHidden: false,
			onSaveTemplate: function(action, response) {
				me.onSaveTemplate(action, response);
			},
			onSetDefault: function(response) {
				me.onSetDefault(response);
			}
		});

		me.editorContainerPanel = Ext6.create('Ext6.Panel', {
			region: 'center',
			layout: 'fit',
			scroll: true,
			items: [
				me.editor
			]
		});

		var toolbarButtonsCfg = {
			createFolder: {
				iconCls: 'icon-folder',
				text: 'Создать папку',
				margin: '0 3 0 6',
				handler: function() {
					me.createFolder();
				}
			},
			approveTemplate: {
				iconCls: 'icon-approve',
				text: 'Одобрить',
				hidden: true	//todo: Доступно для АРМ админа МО
			},
			rejectTemplate: {
				iconCls: 'icon-reject',
				text: 'Отклонить',
				hidden: true	//todo: Доступно для АРМ админа МО
			},
			createTemplate: {
				iconCls: 'icon-template-new',
				text: 'Создать шаблон',
				handler: function() {
					me.createTemplate();
				}
			},
			editProperties: {
				iconCls: 'icon-properties',
				text: 'Свойства',
				handler: function() {
					me.editProperties();
				}
			},
			renameTemplate: {
				iconCls: 'panicon-edit',
				text: 'Переименовать',
				handler: function() {
					me.renameTemplate();
				}
			},
			moveTemplate: {
				iconCls: 'icon-move-to',
				text: 'Переместить в...',
				menu: [],
				handler: function() {
					me.moveTemplate();
				}
			},
			copyTemplate: {
				iconCls: 'icon-copy-to',
				text: 'Копировать в...',
				menu: [],
				handler: function() {
					me.copyTemplate();
				}
			},
			shareTemplate: {
				iconCls: 'icon-share',
				text: 'Поделиться',
				handler: function() {
					me.shareTemplate();
				}
			},
			deleteItem: {
				iconCls: 'panicon-delete',
				text: 'Удалить',
				handler: function() {
					me.deleteItem();
				}
			},
			print: {
				iconCls: 'panicon-print',
				text: 'Печать',
				menu: []
			}
		};

		me.toolbarButtons = {};
		Object.keys(toolbarButtonsCfg).forEach(function(key) {
			var cfg = toolbarButtonsCfg[key];
			me.toolbarButtons[key] = Ext6.create('Ext6.button.Button', cfg);
		});

		me.toolbar = Ext6.create('Ext6.toolbar.Toolbar', {
			dock: 'top',
			cls: 'grid-toolbar',
			defaults: {
				cls: 'toolbar-padding',
				margin: '0 4 0 0',
				padding: '4 5 4 8',
			},
			items: Object.keys(me.toolbarButtons).map(function(key) {
				return me.toolbarButtons[key];
			})
		});

		me.footer = Ext6.create('Ext6.toolbar.Toolbar', {
			dock: 'bottom',
			ui: 'footer',
			items: ['->', {
				text: 'Отмена',
				cls: 'buttonCancel',
				handler: function() {
					me.hide();
				}
			}, {
				text: 'Применить',
				cls: 'buttonAccept',
				handler: function() {
					me.apply();
				}
			}]
		});

		Ext6.apply(me, {
			layout: 'border',
			border: false,
			style: 'padding: 0 !important;',
			defaults: {
				bodyStyle: {
					borderLeft: 0,
					borderRight: 0
				}
			},
			items: [
				me.sectionContainerPanel,
				me.editorContainerPanel
			],
			dockedItems: [
				me.toolbar,
				me.footer
			]
		});

		me.callParent(arguments);
	}
});