/**
 * swQuestionTypeVisionEditWindow - окно настройки отображения элементов анкеты
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      	Admin
 * @access       	public
 * @copyright		Copyright (c) 2016 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			07.06.2016
 */
/*NO PARSE JSON*/

sw.Promed.swQuestionTypeVisionEditWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swQuestionTypeVisionEditWindow',
	width: 620,
	maximizable: false,
	maximized: true,
	layout: 'fit',

	doSave: function() {
		var wnd = this;

		var base_form = this.FormPanel.getForm();

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function()
				{
					this.FormPanel.getFirstInvalidEl().focus(true);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var QuestionTypeVisionList = [];
		var settings = {};
		for (var key in this.QuestionTypeFactory.settingsByCode) {
			settings = this.QuestionTypeFactory.settingsByCode[key];
			if (Ext.isArray(settings.vision)) {
				settings.vision.forEach(function(vision) {
					if (vision.RecordStatus_Code == 1) return;
					QuestionTypeVisionList.push({
						QuestionTypeVision_id: vision.id,
						DispClass_id: settings.DispClass_id,
						QuestionType_pid: settings.QuestionType_pid,
						QuestionType_id: settings.QuestionType_id,
						QuestionTypeVision_Settings: Ext.util.JSON.encode(vision.settings),
						RecordStatus_Code: vision.RecordStatus_Code
					});
				});
			}
			if (Ext.isArray(settings.childrenVision)) {
				settings.childrenVision.forEach(function(vision) {
					if (vision.RecordStatus_Code == 1) return;
					QuestionTypeVisionList.push({
						QuestionTypeVision_id: vision.id,
						DispClass_id: settings.DispClass_id,
						QuestionType_pid: settings.QuestionType_id,
						QuestionType_id: null,
						QuestionTypeVision_Settings: Ext.util.JSON.encode(vision.settings),
						RecordStatus_Code: vision.RecordStatus_Code
					});
				});
			}
		}

		params = {
			QuestionTypeVisionList: Ext.util.JSON.encode(QuestionTypeVisionList)
		};

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Подождите, идет сохранение..." });
		loadMask.show();
		Ext.Ajax.request({
			params: params,
			url: '/?c=QuestionType&m=saveQuestionTypeVisionList',
			success: function(response) {
				loadMask.hide();
				var response_obj = Ext.util.JSON.decode(response.responseText);

				if (response_obj.success) {
					loadMask = new Ext.LoadMask(this.DemoFormPanel.getEl(), {msg: "Формирование анкеты..."});
					loadMask.show();

					this.QuestionTypeFactory.loadSettings(function() {
						this.DemoFormPanel.reInitFields();
						this.DemoFormPanel.doLayout();
						this.execRefreshQuestionTypeSettings();
						loadMask.hide();
					}.createDelegate(this));
					this.callback();
				}
			}.createDelegate(this),
			failure: function() {
				loadMask.hide();
			}.createDelegate(this)
		});
	},

	loadQuestionTypeList: function(callback) {
		callback = callback || Ext.emptyFn;
		var base_form = this.FormPanel.getForm();
		var combo = base_form.findField('QuestionType_id');
		var disp_class_id = base_form.findField('DispClass_id').getValue();
		var regime = this.getRegime();

		var oldValue = combo.getValue();
		var newValue = null;

		var afterLoad = function() {
			newValue = combo.getValue();
			if (oldValue != newValue) {
				combo.fireEvent('change', combo, newValue, oldValue);
			}
			callback(combo);
		};

		if (Ext.isEmpty(regime) || Ext.isEmpty(disp_class_id)) {
			combo.disable();
			combo.setValue(null);
			combo.getStore().removeAll();
			afterLoad();
		} else {
			if (this.action != 'view') {
				combo.enable();
			}
			params = {
				DispClass_id: disp_class_id,
				Regime_id: regime
			};
			combo.getStore().load({
				params: params,
				callback: function() {
					var record = combo.getStore().getById(oldValue);
					if (record && !Ext.isEmpty(record.get('QuestionType_id'))) {
						combo.setValue(oldValue);
					} else {
						combo.setValue(null);
					}
					afterLoad();
				}
			});
		}
	},

	setSelectType: function(value) {
		var items = this.findBy(function(item) {
			return (item.name=='selectType');
		});
		items.forEach(function(item) {
			item.setValue(item.inputValue == value);
		});
	},

	getSelectType: function() {
		var items = this.findBy(function(item) {
			return (item.name=='selectType');
		});
		var value = null;
		items.forEach(function(item) {
			if (item.checked) {
				value = item.inputValue;
				return false;
			}
		});
		return value;
	},

	selectQuestionTypeList: function(codeList) {
		var gridSelection = this.QuestionTypeSelectGrid.getGrid();
		gridSelection.getStore().each(function(record, index) {
			if (record.get('QuestionType_Code').inlist(codeList)) {
				gridSelection.getSelectionModel().selectRow(index, true);
			}
		});
	},

	getRegime: function() {
		var base_form = this.FormPanel.getForm();
		var Group_id = base_form.findField('Group_id').getValue()
		var QuestionType_id = base_form.findField('QuestionType_id').getValue();
		var answerType = Number(base_form.findField('QuestionType_id').getFieldValue('AnswerType_id'));

		return (!Ext.isEmpty(QuestionType_id) && answerType == 0 && !Ext.isEmpty(Group_id))?2:1;
	},

	refreshGroupList: function(hide) {
		var base_form = this.FormPanel.getForm();
		var code = base_form.findField('QuestionType_id').getFieldValue('QuestionType_Code');
		var answerType = Number(base_form.findField('QuestionType_id').getFieldValue('AnswerType_id'));

		var groupCombo = base_form.findField('Group_id');

		groupCombo.getStore().removeAll();

		if (answerType != 0 || Ext.isEmpty(code) || hide) {
			groupCombo.hideContainer();
			return;
		}
		groupCombo.showContainer();

		var settings = this.QuestionTypeFactory.settingsByCode['QuestionType_'+code];
		var arr = [];
		arr.push(new Ext.data.Record({
			Group_id: '',
			Group_Name: ''
		}));
		if (Ext.isArray(settings.childrenVision)) {
			settings.childrenVision.forEach(function(childVision, index){
				var id = index+1;
				var name = 'Группа '+id+(childVision.id < 0?' (новая)':'');
				var selectType = 'default';
				var codeList = [];
				if (childVision.settings.CodeList) {
					selectType = 'include';
					codeList = childVision.settings.CodeList;
				}
				if (childVision.settings.ExceptCodeList) {
					selectType = 'exclude';
					codeList = childVision.settings.ExceptCodeList;
				}
				arr.push(new Ext.data.Record({
					Group_id: id,
					Group_Name: name,
					QuestionTypeVision_id: childVision.id,
					codeList: codeList,
					selectType: selectType
				}));
			});
		}
		var id = arr.length;
		arr.push(new Ext.data.Record({
			Group_id: id,
			Group_Name: 'Добавить группу',
			QuestionTypeVision_id: -swGenTempId(groupCombo.getStore()),
			codeList: [],
			selectType: 'default'
		}));
		groupCombo.getStore().loadRecords({records: arr}, {add: true}, true);
	},

	refreshQuestionTypeSelection: function() {
		var grid = this.QuestionTypeSelectGrid.getGrid();
		var base_form = this.FormPanel.getForm();

		var regime = this.getRegime();
		var code = base_form.findField('QuestionType_id').getFieldValue('QuestionType_Code');
		var selectType = this.getSelectType();
		var CodeList = [];

		if (selectType == 'default') {
			this.QuestionTypeSelectGrid.disable();
			grid.getSelectionModel().clearSelections(true);
			grid.getView().refresh();
		} else {
			if (this.action != 'view') {
				this.QuestionTypeSelectGrid.enable();
			}
			grid.getSelectionModel().getSelections().forEach(function(record) {
				CodeList.push(record.get('QuestionType_Code'));
			});
		}

		var group_id = base_form.findField('Group_id').getValue();
		if (!Ext.isEmpty(group_id)) {
			var index = base_form.findField('Group_id').getStore().findBy(function(rec) { return rec.get('Group_id') == group_id; });
			var group = base_form.findField('Group_id').getStore().getAt(index);
			if (group) {
				group.set('selectType', selectType);
				group.set('codeList', CodeList);
			}
		}

		this.DemoFormPanel.select(regime, code, selectType, CodeList);
	},

	getQuestionTypeVisionSettings: function() {
		var base_form = this.FormPanel.getForm();
		var selectionGrid = this.QuestionTypeSelectGrid.getGrid();
		var settingsGrid = this.QuestionTypeVisionSettingsGrid.getGrid();

		var region_id = base_form.findField('Region_id').getValue();

		var codeList = [];
		selectionGrid.getSelectionModel().getSelections().forEach(function(record) {
			codeList.push(record.get('QuestionType_Code'));
		});

		var visionSettings = null;
		settingsGrid.getStore().each(function(record) {
			var nick = record.get('nick');
			var value = record.get('value');
			if (Ext.isEmpty(nick) || Ext.isEmpty(value)) return;

			if (!visionSettings) {
				visionSettings = {};
			}
			if (!Ext.isEmpty(region_id)) {
				visionSettings.region_id = region_id;
			}
			switch(nick) {
				case 'columns':
					visionSettings.columns = [];
					var array = record.get('value').split(',');
					array.forEach(function(item){
						visionSettings.columns.push({width: item});
					});
					if (codeList.length > 0) {
						if (this.getSelectType() == 'include') {
							visionSettings.CodeList = codeList;
						} else if (this.getSelectType() == 'exclude') {
							visionSettings.ExceptCodeList = codeList;
						}
					}
					break;
				case 'inRow':
					visionSettings.inRow = {spaceWidth: value};
					if (codeList.length > 0) {
						if (this.getSelectType() == 'include') {
							visionSettings.CodeList = codeList;
						} else if (this.getSelectType() == 'exclude') {
							visionSettings.ExceptCodeList = codeList;
						}
					}
					break;
				default:
					visionSettings[nick] = value;
			}
		}.createDelegate(this));

		return visionSettings;
	},

	refreshQuestionTypeSettings: function(refreshParent) {
		var base_form = this.FormPanel.getForm();

		var regime = this.getRegime();
		var vision_id = base_form.findField('QuestionTypeVision_id').getValue();
		var code = base_form.findField('QuestionType_id').getFieldValue('QuestionType_Code');
		if (!code) return false;

		var items = this.DemoFormPanel.findBy(function(item) {
			return item.QuestionType_Code == code;
		});
		if (items.length == 0) return false;
		var element = items[0];
		if (!element.settings) return false;

		var visionSettings = this.getQuestionTypeVisionSettings();

		if (visionSettings) {
			var vision = {id: vision_id, settings: visionSettings, RecordStatus_Code: 0};
			if (regime == 2) {
				sw.Promed.QuestionType.setChildVision(vision, element.settings);
			} else {
				sw.Promed.QuestionType.setVision(vision, element.settings);
			}
		} else {
			if (regime == 2) {
				sw.Promed.QuestionType.deleteChildVision(vision_id, element.settings);
			} else {
				sw.Promed.QuestionType.deleteVision(vision_id, element.settings);
			}
		}

		if (refreshParent === true && element.settings.parent) {
			element = element.settings.parent;
		}

		element.initSettings(element.settings);
		if (element.doLayout) {
			element.doLayout();
		}

		var group_id = base_form.findField('Group_id').getValue();
		if (!Ext.isEmpty(group_id)) {
			this.refreshGroupList();
			base_form.findField('Group_id').setValue(group_id);
			base_form.findField('Group_id').collapse();
		}

		this.refreshQuestionTypeSelection();

		var elementRoot = this.QuestionTypeFactory.settingsTree.container.items.itemAt(0);
		elementRoot.resizeChildren();

		//В element.initSettings могут пересоздаваться комбобоксы, поэтому их нужно заново прогрузить
		this.QuestionTypeFactory.loadDataLists();

		return true;
	},

	/**
	 * Каждый вызов метода откладывает обновление на 0.3 сек. с момента вызова
	 */
	execRefreshQuestionTypeSettings: function(refreshParent) {
		if (this.refreshSettingsTimeout) {
			clearTimeout(this.refreshSettingsTimeout);
		}
		this.refreshSettingsTimeout = setTimeout(function(){
			this.refreshSettingsTimeout = null;
			this.refreshQuestionTypeSettings(refreshParent);
		}.createDelegate(this), 300);
	},

	prepareSettingsForEdit: function() {
		var base_form = this.FormPanel.getForm();
		var code = base_form.findField('QuestionType_id').getFieldValue('QuestionType_Code');
		var gridSelection = this.QuestionTypeSelectGrid.getGrid();
		var gridSettings = this.QuestionTypeVisionSettingsGrid.getGrid();

		var settings = this.QuestionTypeFactory.settingsByCode['QuestionType_'+code];

		var selectType = null;
		var codeList = [];

		var id = base_form.findField('QuestionTypeVision_id').getValue();

		var visionSettings = {};
		if (settings && Ext.isArray(settings.vision)) {
			var visionSettings = {};
			settings.vision.forEach(function(vision) {
				if (vision.id == id) {
					visionSettings = vision.settings;
					return false;
				}
			});
		} else if (settings && Ext.isArray(settings.childrenVision)) {
			var visionSettings = {};
			settings.childrenVision.forEach(function(childVision) {
				if (childVision.id == id) {
					visionSettings = childVision.settings;
					return false;
				}
			});
		}

		if (Ext.isArray(visionSettings.CodeList)) {
			selectType = 'include';
			codeList = visionSettings.CodeList;
		}
		if (Ext.isArray(visionSettings.ExceptCodeList)) {
			selectType = 'exclude';
			codeList = visionSettings.ExceptCodeList;
		}
		if (selectType && codeList.length > 0) {
			this.setSelectType(selectType);
			this.selectQuestionTypeList(codeList);
		}

		for(nick in visionSettings) {
			if (nick.inlist(['CodeList','ExceptCodeList','region_id'])) {
				continue;
			}
			var value = '';
			switch(nick) {
				case 'columns':
					var arr = [];
					var columns = visionSettings[nick];
					columns.forEach(function(column) {
						arr.push(column.width);
					});
					value = arr.join(',');
					break;
				case 'inRow':
					value = visionSettings[nick].spaceWidth;
					break;
				default:
					value = visionSettings[nick];
			}
			var count = gridSettings.getStore().getCount();

			this.filterPropertySelector();
			var record = this.propertySelector.findRecord('nick', nick);

			if (record && !Ext.isEmpty(value)) {
				var id = count;
				var index = count;
		 		gridSettings.getStore().loadData([{id: id}], true);

				var gridRecord = gridSettings.getStore().getById(id);
				gridRecord.set('nick', record.get('nick'));
				gridRecord.set('name', record.get('name'));
				gridRecord.set('qtip', record.get('qtip'));
				gridRecord.set('value', value);
				gridRecord.commit();

				var editor = gridSettings.getColumnModel().getCellEditor(4, index);
				if (record.get('maskRe')) {
					editor.field.maskRe = record.get('maskRe');
				}
			}
		}
	},

	filterPropertySelector: function(keepPropertyList) {
		var gridSettings = this.QuestionTypeVisionSettingsGrid.getGrid();
		var base_form = this.FormPanel.getForm();
		var regime = this.getRegime();
		var QuestionType_id = base_form.findField('QuestionType_id').getValue();
		var answerType = base_form.findField('QuestionType_id').getFieldValue('AnswerType_Code');
		var answerClass = base_form.findField('QuestionType_id').getFieldValue('AnswerClass_SysNick');

		var combo = this.propertySelector;
		combo.clearFilter();

		var propertyList = [];
		if (!Ext.isEmpty(QuestionType_id)) {
			if (regime == 2) {
				propertyList = ['columns','inRow'];
			} else {
				switch(Number(answerType)) {
					case 0:	//FieldSet
						propertyList = ['title','index','hidden'];
						break;
					case 1:	//Да/Нет (чекбокс)
						propertyList = ['fieldLabel','index','hidden'];
						break;
					case 2:	//Текст
						propertyList = ['fieldLabel','width','labelWidth','maskRe','validator','MaxLength','index','hidden'];
						break;
					case 3:	//Справочник
						propertyList = ['fieldLabel','width','labelWidth','index','hidden'];
						break;
					case 4:	//Смешанные. Сейчас используется для комбо диагноза, перед которым дожен быть чекбокс
						propertyList = ['index','hidden'];	//todo: сделать компонент checkbox+combobox
						break;
					case 13:
					case 5: //Число. Пока только целочисленные
						propertyList = ['fieldLabel','width','labelWidth','maskRe','validator','index','minValue','maxValue','hidden'];
						break;
				}
			}
		}

		gridSettings.getStore().each(function(record) {
			if (!Ext.isEmpty(record.get('nick'))) {
				propertyList.splice(propertyList.indexOf(record.get('nick')), 1);
			}
		});

		if (Ext.isArray(keepPropertyList)) {
			propertyList = propertyList.concat(keepPropertyList);
		}

		combo.getStore().filterBy(function(record){
			return record.get('nick').inlist(propertyList);
		});
	},

	getColumnEditor: function(grid, columnName) {
		var cm = grid.getColumnModel();
		var index = cm.findColumnIndex(columnName);
		return (index >=0 && cm.getCellEditor(index)) ? cm.getCellEditor(index) : null;
	},

	show: function() {
		sw.Promed.swQuestionTypeVisionEditWindow.superclass.show.apply(this, arguments);

		this.action = 'view';
		this.callback = Ext.emptyFn;

		var base_form = this.FormPanel.getForm();
		base_form.reset();

		this.QuestionTypeSelectGrid.removeAll();
		this.QuestionTypeVisionSettingsGrid.removeAll();
		this.refreshGroupList(true);

		if (arguments[0] && arguments[0].action) {
			this.action = arguments[0].action;
		}

		if (arguments[0] && arguments[0].callback) {
			this.callback = arguments[0].callback;
		}

		if (arguments[0] && arguments[0].formParams) {
			base_form.setValues(arguments[0].formParams);
		}

		base_form.items.each(function(f){f.validate()});

		this.QuestionTypeSelectionPanel.hide();
		this.QuestionTypeFactory.clearContainers();

		this.setTitle('Настройка отображения анкет');

		switch(this.action) {
			case 'add':
			case 'edit':
			case 'view':
				if (this.action == 'view') {
					this.enableEdit(false);
					this.QuestionTypeVisionSettingsGrid.setReadOnly(true);
				} else {
					this.enableEdit(true);
					this.QuestionTypeVisionSettingsGrid.setReadOnly(false);
				}

				if (Ext.isEmpty(base_form.findField('QuestionTypeVision_id').getValue())) {
					base_form.findField('QuestionTypeVision_id').setValue(-1);
					base_form.findField('DispClass_id').getStore().load();
					base_form.findField('QuestionType_id').getStore().removeAll();
					base_form.findField('Region_id').setValue(null);
					this.QuestionTypeFactory.setRegionId(null);
				} else {
					var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Подождите, идет загрузка..." });
					loadMask.show();
					Ext.Ajax.request({
						params: {
							QuestionTypeVision_id: base_form.findField('QuestionTypeVision_id').getValue()
						},
						url: '/?c=QuestionType&m=loadQuestionTypeVisionForm',
						success: function (response) {
							var response_obj = Ext.util.JSON.decode(response.responseText);
							var values = response_obj[0];

							var doings = new sw.Promed.Doings();	//Для синхронизации асинхронной загрузки комбобоксов

							var dc_combo = base_form.findField('DispClass_id');
							var qt_combo = base_form.findField('QuestionType_id');
							var group_combo = base_form.findField('Group_id');

							base_form.findField('QuestionTypeVision_id').setValue(values.QuestionTypeVision_id);

							doings.start('loadDispClassList');
							dc_combo.setValue(values.DispClass_id);
							dc_combo.getStore().load({
								callback: function() {
									doings.finish('loadDispClassList');
								}
							});

							doings.start('loadQuestionTypeList');
							this.loadQuestionTypeList(function() {
								doings.finish('loadQuestionTypeList');
							}.createDelegate(this));

							doings.doLater('fn1', function() {
								dc_combo.setValue(values.DispClass_id);

								if (!Ext.isEmpty(values.QuestionType_id)) {
									base_form.findField('QuestionType_id').setValue(values.QuestionType_id);
								} else {
									base_form.findField('QuestionType_id').setValue(values.QuestionType_pid);
								}

								this.QuestionTypeFactory.setDispClassId(dc_combo.getValue());

								var loadMask = new Ext.LoadMask(this.DemoFormPanel.getEl(), {msg: "Формирование анкеты..."});
								loadMask.show();
								this.QuestionTypeFactory.loadSettings(function() {
									this.refreshGroupList();
									var index = group_combo.getStore().findBy(function(rec) { return rec.get('QuestionTypeVision_id') == values.QuestionTypeVision_id; });
									var record = group_combo.getStore().getAt(index);
									group_combo.setValue(record?record.get('Group_id'):null);

									this.DemoFormPanel.reInitFields();
									this.DemoFormPanel.doLayout();
									if (record) {
										group_combo.fireEvent('select', group_combo, record, index);
									} else {
										group_combo.fireEvent('select', group_combo, group_combo.getStore().getAt(0), 0);
									}
									loadMask.hide();
								}.createDelegate(this));
							}.createDelegate(this));

							loadMask.hide();
						}.createDelegate(this),
						falure: function (response) {
							loadMask.hide();
						}.createDelegate(this),
					});
				}

				break;
		}
	},

	initComponent: function() {
		this.QuestionTypeSelectGrid = new sw.Promed.ViewFrame({
			height: 85,
			toolbar: false,
			denyEdit: true,
			hideHeaders: true,
			useEmptyRecord: false,
			autoLoadData: false,
			focusOnFirstLoad: false,
			noSelectFirstRowOnFocus: true,
			multi: true,
			selectionModel: 'multiselect',
			dataUrl: '/?c=QuestionType&m=loadQuestionTypeList',
			stringfields:[
				{name: 'QuestionType_id', type: 'int', header: 'ID', key: true},
				{name: 'QuestionType_Code', type:'int', header: 'Код', width: 60},
				{name: 'QuestionType_Name', type:'string', header: 'Наименование', id: 'autoexpand'}
			],

			onMultiSelectionChangeAdvanced: function() {
				this.refreshQuestionTypeSelection();
				this.execRefreshQuestionTypeSettings();
			}.createDelegate(this),
		});

		this.QuestionTypeSelectionPanel = new Ext.form.FieldSet({
			xtype: 'fieldset',
			title: 'Настройка группировки элементов',
			style: 'padding: 5px;',
			autoHeight: true,
			width: 600,
			items: [
				{
					layout: 'column',
					border: false,
					bodyStyle: 'background:#DFE8F6;',
					items: [{
						layout: 'form',
						border: false,
						bodyStyle: 'background:#DFE8F6;',
						items: [{
							xtype: 'radio',
							hideLabel: true,
							boxLabel: 'По умолчанию',
							inputValue: 'default',
							name: 'selectType',
							listeners: {
								'check': function() {
									this.refreshQuestionTypeSelection();
									this.execRefreshQuestionTypeSettings();
								}.createDelegate(this)
							},
							checked: true
						}]
					}, {
						layout: 'form',
						border: false,
						bodyStyle: 'margin-left: 5px; background:#DFE8F6;',
						items: [{
							xtype: 'radio',
							hideLabel: true,
							boxLabel: 'Включить в группу',
							inputValue: 'include',
							name: 'selectType',
							listeners: {
								'check': function() {
									this.refreshQuestionTypeSelection();
									this.execRefreshQuestionTypeSettings();
								}.createDelegate(this)
							},
							checked: false,
						}]
					}, {
						layout: 'form',
						border: false,
						bodyStyle: 'margin-left: 5px; background:#DFE8F6;',
						items: [{
							xtype: 'radio',
							hideLabel: true,
							boxLabel: 'Исключить из группы',
							inputValue: 'exclude',
							listeners: {
								'check': function() {
									var grid = this.QuestionTypeSelectGrid.getGrid();

									if (this.action != 'view') {
										grid.enable();
									}

									this.refreshQuestionTypeSelection();
									this.execRefreshQuestionTypeSettings();
								}.createDelegate(this)
							},
							name: 'selectType'
						}]
					}]
				},
				this.QuestionTypeSelectGrid
			]
		});

		this.propertySelector = new sw.Promed.SwBaseLocalCombo({
			allowBlank: false,
			editable: false,
			valueField: 'id',
			displayField: 'name',
			store: new Ext.data.SimpleStore({
				autoLoad: false,
				fields: [
					{name: 'id', type: 'int'},
					{name: 'nick', type: 'string'},
					{name: 'name', type: 'string'},
					{name: 'qtip', type: 'string'},
					{name: 'maskRe'}
				],
				key: 'id',
				sortInfo: {
					field: 'id'
				},
				data: [
					[1, 'inRow', 'Вывод в строку', 'Ширина промежутка между элементами, px', /[0-9]/],
					[2, 'columns', 'Вывод в столбцы', 'Ширина столбцов через запятую, px', /[0-9,]/],
					[3, 'title', 'Заголовок', null],
					[4, 'FieldType', 'Тип элемента', null],
					[5, 'fieldLabel', 'Подпись поля', null],
					[6, 'labelWidth', 'Ширина подписи поля', null, /[0-9]/],
					[7, 'width', 'Ширина элемента', null], /[0-9]/,
					[8, 'MaxLength', 'Максимальное количество символов', null, /[0-9]/],
					[9, 'maskRe', 'Допустимые символы', 'Допустимые символы в виде регулярного выражения'],
					[10, 'validator', 'Валидация поля', 'JS-код для валидации значения в поле'],
					[11, 'index', 'Номер позиции в подгруппе', null],
					[12, 'minValue', 'Минимальное значение', null, /[0-9]/],
					[13, 'maxValue', 'Максимальное значение', null, /[0-9]/],
					[14, 'hidden', 'Не отображать элемент', null]
				]
			}),
			listeners: {
				'blur': function(combo) {
					var grid = this.QuestionTypeVisionSettingsGrid.getGrid();
					grid.stopEditing();
					grid.getStore().each(function(record){
						if (Ext.isEmpty(record.get('nick'))) {
							grid.getStore().remove(record);
						}
					});
				}.createDelegate(this)
			}
		});

		this.QuestionTypeVisionSettingsGrid = new sw.Promed.ViewFrame({
			height: 140,
			title: 'Настройки',
			region: 'south',
			editing: true,
			useEmptyRecord: false,
			autoLoadData: false,
			focusOnFirstLoad: false,
			saveAtOnce: false,
			//selectionModel: 'cell',
			stringfields:[
				{name: 'id', type: 'int', header: 'ID', key: true},
				{name: 'nick', type: 'string', hidden: true},
				{name: 'qtip', type: 'string', hidden: true},
				{name: 'name', type:'int', header: 'Свойство', editor: this.propertySelector, width: 340},
				{name: 'value', header: 'Значение', editor: new Ext.form.TextField({allowBlank: false, maskRe: /[\s\S]/}), id: 'autoexpand',
					renderer: function(value, meta, record) {
						var qtip = '';
						if (!Ext.isEmpty(record.get('qtip'))) {
							qtip = 'ext:qtip="'+record.get('qtip')+'"';
						}
						return '<p '+qtip+'>'+value+'&nbsp;</p>';
					}
				}
			],
			onBeforeEdit: function(cell) {
				if (cell.field == 'value' && !Ext.isEmpty(cell.record.get('nick'))) {
					return !cell.record.get('nick').inlist(['hidden']);
				}
			}.createDelegate(this),
			onAfterEditSelf: function(cell) {
				var grid = cell.grid;

				grid.stopEditing(true);

				if (cell.field == 'name') {
					var record = this.propertySelector.findRecord('id', cell.value);
					if (record) {
						cell.record.set('nick', record.get('nick'));
						cell.record.set('name', record.get('name'));
						cell.record.set('qtip', record.get('qtip'));
						cell.record.set('value', '');
					} else {
						cell.record.set('nick', '');
						cell.record.set('name', '');
						cell.record.set('qtip', '');
						cell.record.set('value', '');
					}
					if (cell.record.get('nick') == 'hidden') {
						cell.record.set('value', true);
					}
					cell.record.commit();

					var editor = this.getColumnEditor(grid, 'value');
					if (record.get('maskRe')) {
						editor.field.maskRe = record.get('maskRe');
					} else {
						editor.field.maskRe = /[\s\S]/;
					}
				}
				if (cell.field == 'value') {
					cell.record.set('value', cell.value);
					cell.record.commit();
				}
				if (cell.field == 'value' || cell.record.get('nick') == 'hidden') {
					if (cell.record.get('nick') == 'index') {
						this.execRefreshQuestionTypeSettings(true);
					} else {
						this.execRefreshQuestionTypeSettings(false);
					}
				}
			}.createDelegate(this),
			actions: [
				{name:'action_add', handler: function() {
					var grid = this.QuestionTypeVisionSettingsGrid.getGrid();
					var count = grid.getStore().getCount();
					var record = grid.getStore().getAt(count-1);

					if (!record || !Ext.isEmpty(record.get('value'))) {
						grid.getStore().loadData([{id: swGenTempId(grid.getStore())}], true);
						grid.startEditing(count, 3);
					}
				}.createDelegate(this)},
				{name:'action_edit', hidden: true},
				{name:'action_view', hidden: true},
				{name:'action_delete', handler: function() {
					var grid = this.QuestionTypeVisionSettingsGrid.getGrid();
					var record = grid.getSelectionModel().getSelected();
					if (record) {
						grid.getStore().remove(record);
					}
					if (record && record.get('nick') == 'index') {
						this.execRefreshQuestionTypeSettings(true);
					} else {
						this.execRefreshQuestionTypeSettings(false);
					}
				}.createDelegate(this)},
				{name:'action_refresh', hidden: true},
				{name:'action_print', hidden: true},
				{name:'action_save', hidden: true}
			]
		});

		this.FormPanel = new Ext.FormPanel({
			id: 'QTVEW_FormPanel',
			region: 'center',
			border: false,
			//autoHeight: true,
			height: 155,
			bodyStyle: 'padding: 5px; background:#DFE8F6;',
			labelAlign: 'right',
			labelWidth: 120,
			url: '/?c=QuestionType&m=saveQuestionTypeVision',
			items: [{
				xtype: 'hidden',
				name: 'QuestionTypeVision_id'
			}, {
				layout: 'column',
				border: false,
				bodyStyle: 'padding: 5px; background:#DFE8F6;',
				items: [{
					layout: 'form',
					border: false,
					bodyStyle: 'padding: 5px; background:#DFE8F6;',
					items: [{
						allowBlank: false,
						xtype: 'swqtdispclasscombo',
						hiddenName: 'DispClass_id',
						fieldLabel: 'Анкета',
						listeners: {
							'select': function(combo, record, index) {
								this.QuestionTypeVisionSettingsGrid.removeAll();
								this.refreshGroupList(true);
								this.loadQuestionTypeList(function(){
									this.refreshGroupList();
								}.createDelegate(this));

								var value = record.get('DispClass_id');

								if (Ext.isEmpty(value)) {
									this.QuestionTypeFactory.setDispClassId(null);
									this.QuestionTypeFactory.clearContainers();
									this.doLayout();
								} else {
									this.QuestionTypeFactory.setDispClassId(value);

									var loadMask = new Ext.LoadMask(this.DemoFormPanel.getEl(), {msg: "Формирование анкеты..."});
									loadMask.show();
									this.QuestionTypeFactory.loadSettings(function() {
										this.DemoFormPanel.reInitFields();
										this.DemoFormPanel.doLayout();
										loadMask.hide();
									}.createDelegate(this));
								}
							}.createDelegate(this)
						},
						listWidth: 360,
						width: 360
					}, {
						allowBlank: false,
						xtype: 'swquestiontypecombo',
						hiddenName: 'QuestionType_id',
						fieldLabel: 'Элемент анкеты',
						listeners: {
							'select': function(combo, record, index) {
								var base_form = this.FormPanel.getForm();

								this.refreshGroupList();
								var groupCombo = base_form.findField('Group_id');
								groupCombo.setValue(null);
								groupCombo.fireEvent('select', groupCombo, groupCombo.getStore().getAt(0), 0);
							}.createDelegate(this),
							'keydown': function(combo, e) {
								var base_form = this.FormPanel.getForm();
								if(e.DELETE == e.getKey()) {
									this.refreshGroupList(true);
									var groupCombo = base_form.findField('Group_id');
									groupCombo.setValue(null);
									groupCombo.fireEvent('select', groupCombo, groupCombo.getStore().getAt(0), 0);
								}
							}.createDelegate(this)
						},
						width: 360
					}, {
						editable: false,
						xtype: 'swbaselocalcombo',
						hiddenName: 'Group_id',
						valueField: 'Group_id',
						displayField: 'Group_Name',
						store: new Ext.data.SimpleStore({
							key: 'Group_id',
							autoLoad: false,
							fields: [
								{name: 'Group_id', type: 'int'},
								{name: 'Group_Name', type: 'string'},
								{name: 'QuestionTypeVision_id', type: 'int'},
								{name: 'selectType', type: 'string'},
								{name: 'codeList'}
							]
						}),
						tpl: new Ext.XTemplate(
							'<tpl for="."><div class="x-combo-list-item">',
							'{Group_Name}&nbsp;',
							'</div></tpl>'
						),
						fieldLabel: 'Группа элементов',
						listeners: {
							'select': function(combo, record, index) {
								var base_form = this.FormPanel.getForm();
								var gridSelection = this.QuestionTypeSelectGrid.getGrid();
								var gridSettings = this.QuestionTypeVisionSettingsGrid.getGrid();
								var code = base_form.findField('QuestionType_id').getFieldValue('QuestionType_Code');
								var region_id = base_form.findField('Region_id').getValue();

								gridSelection.getStore().removeAll();
								gridSettings.getStore().removeAll();

								if (!record || Ext.isEmpty(record.get('Group_id'))) {
									if (code) {
										var settings = this.QuestionTypeFactory.settingsByCode['QuestionType_'+code];
										var vision = sw.Promed.QuestionType.getVision(settings, region_id);
										var vision_id = vision ? vision.id : -swGenTempId(combo.getStore());

										if (!Ext.isEmpty(region_id) && !vision) {
											vision = {id: vision_id, settings: {region_id: region_id}};
											sw.Promed.QuestionType.setVision(vision, settings);
										}

										base_form.findField('QuestionTypeVision_id').setValue(vision_id);
										this.prepareSettingsForEdit();
									}
									this.QuestionTypeSelectionPanel.hide();
									this.refreshQuestionTypeSelection();
									this.execRefreshQuestionTypeSettings();
								} else {
									var codeList = record.get('codeList');
									base_form.findField('QuestionTypeVision_id').setValue(record.get('QuestionTypeVision_id'));
									this.prepareSettingsForEdit();

									gridSelection.getStore().load({
										params: {QuestionType_pid: base_form.findField('QuestionType_id').getValue()},
										callback: function() {
											this.setSelectType(record.get('selectType'));
											this.selectQuestionTypeList(codeList);
											this.refreshQuestionTypeSelection();
										}.createDelegate(this)
									});
									this.QuestionTypeSelectionPanel.show();
								}
							}.createDelegate(this),
							'keydown': function(combo, e) {
								if(e.DELETE == e.getKey()) {
									combo.setValue(null);
									this.QuestionTypeSelectionPanel.hide();
									this.refreshQuestionTypeSelection();
								}
							}.createDelegate(this)
						},
						width: 360
					}, {
						xtype: 'swpromedregioncombo',
						hiddenName: 'Region_id',
						fieldLabel: 'Регион',
						width: 360,
						listeners: {
							'select': function(combo, record, index) {
								var base_form = this.FormPanel.getForm();
								var region_id = !Ext.isEmpty(combo.getValue())?combo.getValue():null;

								this.QuestionTypeFactory.setRegionId(region_id);
								this.refreshGroupList();

								var groupCombo = base_form.findField('Group_id');
								groupCombo.setValue(null);
								groupCombo.fireEvent('select', groupCombo, groupCombo.getStore().getAt(0), 0);
							}.createDelegate(this)
						}
					}]
				}, {
					layout: 'form',
					border: false,
					bodyStyle: 'margin-left: 20px; background:#DFE8F6;',
					items: [this.QuestionTypeSelectionPanel]
				}]
			}]
		});

		this.QuestionTypeFactory = new sw.Promed.QuestionType.Factory();

		this.DemoFormPanel = new Ext.FormPanel({
			region: 'center',
			labelAlign: 'right',
			border: true,
			title: 'Анкета',
			bodyStyle: 'padding: 5px;',
			autoScroll: true,
			selected: null,
			childrenSelected: [],
			reInitFields: function() {
				var base_form = this.getForm();
				base_form.items.each(function(item) {
					base_form.items.remove(item);
				});
				this.initFields();
			},
			clearSelection: function() {
				if (this.selected) {
					var clslist = ['qt-selected-element','qt-selected-group'];
					var el = this.selected.el.parent('.x-form-item')?this.selected.el.parent('.x-form-item'):this.selected.el;
					el.removeClass(clslist);

					var domArray = Ext.query('.x-panel-body', Ext.getDom(el));
					domArray.forEach(function(domPanel) {
						if (domPanel.id && Ext.get(domPanel.id)) {
							if (Ext.get(domPanel.id)) {
								Ext.get(domPanel.id).removeClass(clslist);
							}
						}
					});

					this.childrenSelected.forEach(function(child) {
						var el = child.el.parent('.x-form-item')?child.el.parent('.x-form-item'):child.el;
						el.removeClass(clslist);

						var domArray = Ext.query('.x-panel-body', Ext.getDom(el));
						domArray.forEach(function(domPanel) {
							if (Ext.get(domPanel.id)) {
								Ext.get(domPanel.id).removeClass(clslist);
							}
						});
					});
					this.selected = null;
					this.childrenSelected = [];
				}
			},
			select: function(regime, code, selectType, CodeList) {
				var form = this.getForm();
				var itemcls = (regime==2?'qt-selected-group':'qt-selected-element');
				var childcls = 'qt-selected-element';

				this.clearSelection();

				if (!Ext.isEmpty(code)) {
					var items = this.findBy(function(item) {
						return item.QuestionType_Code == code;
					});
					if (items.length > 0) {
						var item = items[0];

						this.selected = item;
						var el = item.el.parent('.x-form-item')?item.el.parent('.x-form-item'):item.el;

						if (el.getTop() < form.el.getTop() || el.getBottom() > form.el.getBottom()) {
							form.el.dom.scrollTop = 10000;
							el.scrollIntoView(form.el);
						}

						el.addClass(itemcls);

						var domArray = Ext.query('.x-panel-body', Ext.getDom(el));
						domArray.forEach(function(domPanel) {
							if (Ext.get(domPanel.id)) {
								Ext.get(domPanel.id).addClass(itemcls);
							}
						});

						if (regime == 2 && item.settings && item.settings.children) {
							var list = [];
							item.settings.children.forEach(function(childSettings) {
								list.push(childSettings.QuestionType_Code);
							});

							this.childrenSelected = item.findBy(function(item) {
								if (!item.QuestionType_Code) return false;

								var code = Number(item.QuestionType_Code);
								var flag = code.inlist(list);

								if (selectType == 'include') {
									flag = (CodeList.length == 0 || code.inlist(CodeList));
								} else if (selectType == 'exclude') {
									flag = !code.inlist(CodeList);
								}

								return flag;
							});
							this.childrenSelected.forEach(function(child) {
								var el = child.el.parent('.x-form-item')?child.el.parent('.x-form-item'):child.el;
								el.addClass(childcls);

								var domArray = Ext.query('.x-panel-body', Ext.getDom(el));
								domArray.forEach(function(domPanel) {
									if (Ext.get(domPanel.id)) {
										Ext.get(domPanel.id).removeClass(itemcls);
										Ext.get(domPanel.id).addClass(childcls);
									}
								});
							});
						}
					}
				}

				return this.selected;
			},
			items: [
				this.QuestionTypeFactory.createContainer({root: true})
			]
		});

		this.MainPanel = new Ext.Panel({
			layout: 'border',
			border: true,
			items: [
				{
					border: 'border',
					border: false,
					bodyStyle: 'background:#DFE8F6;',
					region: 'north',
					autoHeight: true,
					items: [
						this.FormPanel,
						this.QuestionTypeVisionSettingsGrid
					]
				},
				this.DemoFormPanel
			]
		});

		Ext.apply(this, {
			buttons: [
				{
					handler: function () {
						this.doSave();
					}.createDelegate(this),
					iconCls: 'save16',
					id: 'QTVEW_SaveButton',
					text: BTN_FRMSAVE
				},
				{
					text: '-'
				},
				HelpButton(this),
				{
					handler: function()
					{
						this.hide();
					}.createDelegate(this),
					iconCls: 'cancel16',
					text: BTN_FRMCLOSE
				}
			],
			items: [this.MainPanel]
		});

		sw.Promed.swQuestionTypeVisionEditWindow.superclass.initComponent.apply(this, arguments);

		var gridSettings = this.QuestionTypeVisionSettingsGrid.getGrid();
		var editor = this.getColumnEditor(gridSettings, 'name').addListener('beforestartedit', function(editor, el, value) {
			this.filterPropertySelector();
			return true;
		}.createDelegate(this));
	}
});