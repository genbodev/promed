/*NO PARSE JSON*/

/**
 * Компонент для создания раздела мастера редактирования данных, разбитого на страницы
 */
sw.Promed.WizardCategory = Ext.extend(Ext.form.FormPanel, {
	border: false,

	categoryName: null,
	wizard: null,
	loading: false,
	loaded: false,
	pages: [],
	currentPageNum: null,
	loadParams: {},
	toolbar: true,
	allowSaveButton: true,
	readOnly: false,
	objectName: null,
	idField: null,
	allowCollectData: false,

	loadCategory: function(category, showPage) {
		if (typeof showPage == 'function') showPage();
	},

	saveCategory: null,
	beforeSaveCategory: Ext.emptyFn,
	afterSaveCategory: Ext.emptyFn,

	deleteCategory: null,
	beforeDeleteCategory: Ext.emptyFn,
	afterDeleteCategory: Ext.emptyFn,

	cancelCategory: function(category, onCancel) {onCancel()},

	setCategoryFormData: null,
	getCategoryFormData: null,

	printCategory: null,
	printCategoryMenu: [],

	replaceCategoryDataId: function(category, oldId, newId) {
		if (!category.idField || !category.data.key(oldId)) {
			return false;
		}
		var data = category.data.removeKey(oldId);
		data[category.idField] = newId;
		category.data.add(newId, data);

		if (category[category.idField] == oldId) {
			category[category.idField] = newId;
			if (category.loaded && category.getForm().findField(category.idField)) {
				category.getForm().findField(category.idField).setValue(newId);
			}
		}

		return true;
	},

	setCategoryDataValue: function(category, name, value) {
		var base_form = category.getForm();
		var data = category.getCategoryData(category);
		if (!data || name == category.idField) {
			return false;
		}

		data[name] = value;
		if (base_form.findField(name)) {
			base_form.findField(name).setValue(value);
		}
		return true;
	},

	/**
	 * Сохранение текущих данных раздела в коллекцию данных
	 */
	collectCategoryData: function(category, status, force) {
		if (!category.allowCollectData && !force) {
			return false;
		}
		if (Ext.isEmpty(category.idField) || !category.getCategoryFormData) {
			return false;
		}

		var data = category.getCategoryFormData(category);
		data.loaded = true;

		var id = data[category.idField];
		if (Ext.isEmpty(id)) {
			id = -swGenTempId(category.data);
		}

		var oldData = category.getCategoryData(category, id);

		if (!Ext.isEmpty(status)) {
			data.status = status;
		} else if (oldData) {
			data.status = oldData.status;
		}

		if (Ext.isEmpty(data.status)) {
			data.status = (id<0)?-1:1;
		}

		category[category.idField] = data[category.idField] = id;
		category.getForm().findField(category.idField).setValue(id);

		return category.data.add(id, data);
	},

	removeCategoryData: function(category, id) {
		if (!id && (Ext.isEmpty(category.idField) || Ext.isEmpty(category[category.idField]))) {
			return false;
		}
		return category.data.removeKey(id || category[category.idField]);
	},

	getCategoryData: function(category, id) {
		if (!id && (Ext.isEmpty(category.idField) || Ext.isEmpty(category[category.idField]))) {
			return false;
		}
		return category.data.key(id || category[category.idField]);
	},

	createCategory: function(category) {
		category.setReadOnly(false);
		if (category.idField) {
			category.loadParams[category.idField] = null;
		}
		category.moveToPage(0, category.wizard.afterPageChange);
	},

	validateCategory: function(category, showMsg) {
		if (category.getForm().isValid()) {
			return true;
		}
		if (showMsg) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					var field = category.getFirstInvalidEl();
					var page = category.getPageByField(field);

					if (category.wizard.getCurrentPage() == page) {
						field.focus(true);
					} else {
						category.moveToPage(page, function() {
							category.wizard.afterPageChange();
							field.focus(true);
						});
					}
				},
				icon: Ext.Msg.WARNING,
				msg: (typeof showMsg == 'string')?showMsg:ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
		}
		return false;
	},

	initCategory: function() {
		this.data.clear();
	},

	getPage: function(num) {
		return this.pages[num];
	},

	getCurrentPage: function() {
		return this.getPage(this.currentPageNum);
	},

	getNumByPage: function(page) {
		for(var num = 0; num < this.pages.length; num++) {
			if (this.pages[num] == page) {
				return num;
			}
		}
		return -1;
	},

	selectPage: function(page) {
		var category = this;

		var pageNum = -1;
		if (typeof page == 'object') {
			pageNum = category.getNumByPage(page);
		} else if (typeof page == 'number' && category.pages[page]) {
			pageNum = page;
		}
		if (pageNum < 0) return false;

		category.currentPageNum = pageNum;

		if (category.wizard && category.wizard.getCurrentCategory() != category) {
			category.wizard.selectCategory(category);
		}

		return category.getCurrentPage();
	},

	getPageFields: function(page) {
		var fields = [];
		if (page == 'current') {
			page = this.getCurrentPage();
		}
		function getRecursiveFields(o) {
			if ((typeof o == 'object') && o.items && o.items.items) {
				o = o.items.items;
			}
			if (o && o.length && o.length>0) {
				for (var i = 0, len = o.length; i < len; i++) {
					if (o[i])
						if ((o[i].xtype && (o[i].xtype=='fieldset' || o[i].xtype=='panel' || o[i].xtype=='tabpanel')) || (o[i].layout)) {
							getRecursiveFields(o[i]);
						}
					if (o[i].isFormField) {
						fields.push(o[i]);
					}
				}
			}
		}
		getRecursiveFields(page);
		return fields;
	},

	getPageByField: function(field) {
		var category = this;
		var result = null;
		category.pages.forEach(function(page){
			category.getPageFields(page).forEach(function(pageField){
				if (pageField == field) {
					result = page;
					return false;
				}
			});
			if (result) return false;
		});
		return result;
	},

	reset: function(resetValues) {
		var category = this;
		category.currentPageNum = null;
		if (resetValues) {
			var categoryData = category.getCategoryData(category);
			if (categoryData && categoryData.status.inlist([-1,1])) {
				category.removeCategoryData(category);
			}
			if (category.idField) {
				category[category.idField] = null;
			}
			category.loaded = false;
			category.getForm().reset();
		}
		category.hidePages();
	},

	showPages: function() {
		this.pages.forEach(function(page){page.show()});
	},

	hidePages: function() {
		this.pages.forEach(function(page){page.hide()});
	},

	init: function(wizard) {
		if (wizard instanceof sw.Promed.WizardFrame) {
			this.wizard = wizard;
		}
	},

	moveToPage: function(page, callback) {
		callback = callback || Ext.emptyFn;

		var category = this;
		var wizard = category.wizard;
		if (!wizard) return false;

		var pageNum = -1;
		if (Ext.isEmpty(page) || page == 'current') {
			pageNum = category.currentPageNum;
		} else if (typeof page == 'object') {
			pageNum = category.getNumByPage(page);
		} else if (typeof page == 'number' && category.pages[page]) {
			pageNum = page;
		}
		if (pageNum < 0) return false;

		if (wizard.getCurrentCategory() != category) {
			wizard.selectCategory(category);
		}

		wizard.hideToolbar();
		if (/*category.currentPageNum != pageNum*/true) {
			category.hidePages();
		}

		category.currentPageNum = pageNum;
		var selectedPage = category.pages[pageNum];


		var showPage = function() {
			wizard.refreshToolbar();
			selectedPage.show();
			selectedPage.doLayout();
			category.loading = false;
			callback(category);
		};

		category.loading = true;
		category.loadCategory(category, showPage);

		return selectedPage;
	},

	setReadOnly: function(readOnly) {
		var category = this;

		category.readOnly = readOnly;
		category.getForm().items.each(function(field) {
			if (!field.initialConfig.disabled) {
				field.setDisabled(readOnly);
			}
		});

		category.pages.forEach(function(page) {
			if (page instanceof sw.Promed.ViewFrame) {
				page.setReadOnly(readOnly);
			}
		});

		if (category.saveCategory && category.wizard) {
			category.wizard.SaveButton.setVisible(!readOnly && category.allowSaveButton);
		}
		if (category.deleteCategory && category.wizard) {
			//category.wizard.DeleteButton.setVisible(!readOnly);
		}
	},

	initFields: function() {
		this.form.items.clear();

		sw.Promed.WizardCategory.superclass.initFields.call(this);
	},

	initComponent: function() {
		if (Ext.isArray(this.pages)) {
			this.items = this.pages;
			this.initialConfig.items = this.pages;
		}

		this.form = this.createForm();
		this.form.getValues = function(asString) {
			var values = {};

			this.items.each(function(field) {
				//дизаблим поле 772
				if(field.QuestionType_Code == "772") {
					field.setDisabled(true);
				}
				if (field.getValue() instanceof Date) {
					values[field.getName()] = Ext.util.Format.date(field.getValue(), 'd.m.Y');
				} else {
					values[field.getName()] = field.getValue();
				}
			});

			if(asString === true){
				return Ext.urlEncode(values);
			}
			return values;
		};

		this.data = new Ext.util.MixedCollection(false);

		if (!Ext.isEmpty(this.objectName) && this.idField === null) {
			this.idField = this.objectName+'_id';
		}
		if (!Ext.isEmpty(this.idField)) {
			this[this.idField] = null;
		}

		//!!!
		Ext.FormPanel.superclass.initComponent.call(this);

		this.initItems();

		this.initFields();

		this.addEvents(

			'clientvalidation'
		);

		this.relayEvents(this.form, ['beforeaction', 'actionfailed', 'actioncomplete']);

		this.setReadOnly(this.readOnly);

		this.addListener('render', function(category) {
			setTimeout(function(){category.hidePages()}, 1);
		});
	}
});


sw.Promed.WizardFrame = Ext.extend(Ext.Panel, {
	readOnly: false,
	autoScroll: true,
	buttonAlign : 'left',
	toolbar: true,
	allowCollectData: null,

	autoSyncWidth: true,
	maskEl: null,
	loadMask: null,

	categories: [],
	prevCategoryName: null,
	currentCategoryName: null,

	printConfig: {},

	addCategory: function(category) {
		var wizard = this;

		if (category instanceof sw.Promed.WizardCategory && !Ext.isEmpty(category.name)) {
			if (wizard.inputData instanceof sw.Promed.PersonPregnancy.InputData) {
				category.inputData = wizard.inputData;
			}

			var printConfig = wizard.printConfig[category.name];
			if (typeof printConfig == 'function') {
				category.printCategory = printConfig;
			} else if (Ext.isArray(printConfig) && printConfig.length > 0) {
				category.printCategoryMenu = printConfig;
			}

			var methodListForCategory = [
				'createCategory','saveCategory','beforeSaveCategory','afterSaveCategory',
				'deleteCategory','beforeDeleteCategory','afterDeleteCategory','cancelCategory','onCancelCategory'
			];
			methodListForCategory.forEach(function(methodName){
				if (methodName == 'deleteCategory' && Ext.isEmpty(category.objectName) && Ext.isEmpty(category.idField)) {
					return;
				}
				if (typeof wizard[methodName] == 'function' && category.initialConfig[methodName] == undefined) {
					category[methodName] = wizard[methodName];
				}
			});

			if (category.initialConfig.toolbar == undefined) {
				category.toolbar = wizard.toolbar;
			}
			if (!Ext.isEmpty(wizard.allowCollectData) && category.initialConfig.allowCollectData == undefined) {
				category.allowCollectData = wizard.allowCollectData;
			}

			category.init(wizard);
			wizard.categories.add(category.name, category);
			wizard.add(category);
			return category;
		}
		return false;
	},

	getCategory: function(name) {
		return this.categories.get(name);
	},

	getPrevCategory: function() {
		return this.getCategory(this.prevCategoryName);
	},

	getCurrentCategory: function() {
		return this.getCategory(this.currentCategoryName);
	},

	getCurrentPage: function() {
		var category = this.getCurrentCategory();
		if (category) {
			return category.getCurrentPage();
		}
		return null;
	},

	resetCurrentCategory: function(resetValues) {
		var wizard = this;
		var category = wizard.getCurrentCategory();
		if (category) {
			wizard.prevCategoryName = category.name;
			category.reset(resetValues);
		}
		wizard.currentCategoryName = null;
		wizard.hideToolbar();
	},

	resetCategories: function(resetValues) {
		var wizard = this;
		wizard.categories.each(function(category){
			category.reset(resetValues);
		});
	},

	selectCategory: function(category) {
		var  wizard = this;

		if (typeof category == 'string') {
			category = wizard.getCategory(category);
		}
		if (!(category instanceof sw.Promed.WizardCategory)) return false;
		wizard.resetCurrentCategory();
		wizard.currentCategoryName = category.name;
	},

	setReadOnly: function(readOnly) {
		var wizard = this;

		wizard.readOnly = readOnly;
		wizard.categories.each(function(category){
			if (!category.initialConfig.readOnly) {
				category.setReadOnly(readOnly);
			}
		});
	},

	afterPageChange: Ext.emptyFn,

	onCancel: function() {
		this.resetCurrentCategory(true);
		this.afterPageChange();
	},

	refreshToolbar: function() {
		var wizard = this;
		var category = wizard.getCurrentCategory();

		this.hideToolbar();
		if (category && !Ext.isEmpty(category.currentPageNum)) {
			var prevHandler, nextHandler, saveHandler, deleteHandler, cancelHandler, printHandler, printMenu;
			var pageNum = category.currentPageNum;

			if (typeof category.saveCategory == 'function') {
				saveHandler = function() {category.saveCategory(category)};
			}
			if (typeof category.deleteCategory == 'function') {
				//deleteHandler = function() {category.deleteCategory(category)};
			}
			if (category.printCategoryMenu.length > 0) {
				printMenu = new Ext.menu.Menu({items: category.printCategoryMenu});
			} else if (typeof category.printCategory == 'function') {
				printHandler = function() {category.printCategory(category)};
			}
			if (category.pages.length > 1) {
				prevHandler = function() {category.moveToPage(pageNum-1, wizard.afterPageChange)};
				nextHandler = function() {category.moveToPage(pageNum+1, wizard.afterPageChange)};
			}
			cancelHandler = function() {
				category.cancelCategory(category, wizard.onCancel.createDelegate(wizard));
			};

			wizard.PrevButton.handler = prevHandler || Ext.emptyFn;
			wizard.PrevButton.setVisible(prevHandler);
			wizard.PrevButton.setDisabled(pageNum == 0);

			wizard.NextButton.handler = nextHandler || Ext.emptyFn;
			wizard.NextButton.setVisible(nextHandler);
			wizard.NextButton.setDisabled(pageNum == category.pages.length-1);

			wizard.SaveButton.handler = saveHandler || Ext.emptyFn;
			wizard.SaveButton.setVisible(saveHandler && !category.readOnly && category.allowSaveButton);

			wizard.DeleteButton.handler = deleteHandler || Ext.emptyFn;
			wizard.DeleteButton.setVisible(deleteHandler && !category.readOnly);

			wizard.PrintButton.setMenuOrHandler(printMenu || printHandler);
			wizard.PrintButton.setVisible(printMenu || printHandler);

			wizard.CancelButton.handler = cancelHandler || Ext.emptyFn;
			wizard.CancelButton.setVisible(cancelHandler);

			if (category.toolbar) {
				wizard.showToolbar();
			}
		}
	},

	showToolbar: function() {
		if (this.DataToolbar && this.DataToolbar.hidden == true) {
			this.DataToolbar.show();

			if (this.rendered) {
				this.setHeight(this.getSize().height-this.DataToolbar.height);
			}
		}
	},

	hideToolbar: function() {
		if (this.DataToolbar && this.DataToolbar.hidden == false) {
			if (this.rendered) {
				this.setHeight(this.getSize().height+this.DataToolbar.height);
			}

			this.DataToolbar.hide();
		}
	},

	loadDataLists: function(force) {
		var comboboxes = [];
		this.categories.each(function(category) {
			category.getForm().items.each(function(field) {
				if (field.mode == 'local' && field.store
					&& (force === true || field.store.getCount() == 0)
					&& !String(field.store.baseParams.object).inlist(['Diag'])
				) {
					comboboxes.push(field);
				}
			});
		});
		loadStores(comboboxes, function() {
			comboboxes.forEach(function(combo) {
				if (!combo.rendered) return;

				var index = combo.getStore().findBy(function(rec) { return rec.get(combo.valueField) == combo.getValue(); });
				var record = combo.getStore().getAt(index);

				combo.fireEvent('select', combo, record, index);
				combo.fireEvent('change', combo, combo.getValue());
			});
		});
	},

	isLoading: function() {
		var flag = false;
		this.categories.each(function(category){
			if (category.loading) {
				flag = true;
				return false;
			}
		});
		return flag;
	},

	setMaskEl: function(maskEl) {
		this.maskEl = maskEl || this.getEl();
	},

	getMaskEl: function() {
		return this.maskEl;
	},

	getLoadMask: function(config) {
		if (config || !this.loadMask) {
			var el = this.getMaskEl();
			if (el) {
				this.loadMask = new Ext.LoadMask(el, config);
			} else {
				this.loadMask = {show: Ext.emptyFn, hide: Ext.emptyFn};
			}
		}
		return this.loadMask;
	},

	init: function() {
		this.categories.each(function(category){
			category.initCategory(category);
		});
	},

	initComponent: function() {
		var wizard = this;

		if (!wizard.maskEl) {
			wizard.setMaskEl(wizard.getEl());
		}

		wizard.PrevButton = new Ext.Button({iconCls: 'arrow-previous16', text: 'Назад'});
		wizard.NextButton = new Ext.Button({iconCls: 'arrow-next16', text: 'Далее'});
		wizard.SaveButton = new Ext.Button({iconCls: 'save16', text: BTN_FRMSAVE});
		wizard.DeleteButton = new Ext.Button({iconCls: 'delete16', text: BTN_GRIDDEL});
		wizard.PrintButton = new Ext.Button({iconCls: 'print16', text: BTN_GRIDPRINT});
		wizard.CancelButton = new Ext.Button({iconCls: 'cancel16', text: 'Отмена'});

		wizard.PrintButton.setMenuOrHandler = function(obj) {
			if (obj instanceof Ext.menu.Menu) {
				this.menu = obj;
				this.handler = undefined;
				this.el.child(this.menuClassTarget).addClass('x-btn-with-menu');
			} else if (typeof obj == 'function') {
				this.menu = undefined;
				this.handler = obj;
				this.el.child(this.menuClassTarget).removeClass('x-btn-with-menu');
			} else {
				this.menu = undefined;
				this.handler = undefined;
				this.el.child(this.menuClassTarget).removeClass('x-btn-with-menu');
			}
		};

		wizard.PrintButton.getMenuItem = function(name) {
			if (!this.menu) return null;
			return this.menu.items.find(function(item){
				return item.name == name;
			});
		};

		wizard.bbar = wizard.DataToolbar = new sw.Promed.Toolbar({
			height: 26,
			items: [wizard.PrevButton, wizard.NextButton, wizard.SaveButton, wizard.DeleteButton, wizard.PrintButton, '->', wizard.CancelButton]
		});

		sw.Promed.WizardFrame.superclass.initComponent.apply(wizard, arguments);

		var categoriesArray = [];
		if (Ext.isArray(wizard.categories)) {
			wizard.categories.forEach(function(category) {
				categoriesArray.push(category)
			});
		}

		wizard.categories = new Ext.util.MixedCollection(false);
		categoriesArray.forEach(function(category) {
			wizard.addCategory(category);
		});

		wizard.setReadOnly(wizard.readOnly);

		wizard.loadDataLists();

		wizard.addListener('render', function(wizard) {
			wizard.hideToolbar();
		});
	}
});