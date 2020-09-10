/**
* ЛИС: форма Методики ИФА
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package    All
* @access     public
* @autor      Salavat Magafurov
* @copyright  Copyright (c) 2019 EMSIS.
* @version    13.11.2019
*/

sw.Promed.swMethodsIFAWindow = Ext.extend(sw.Promed.BaseForm, {
	objectName: 'swMethodsIFAWindow',
	title: langs('Методики ИФА'),
	modal: false,
	maximized: true,
	border: false,
	show: function () {
		var params = arguments[0],
			win = this;

		if(!params) return;

		win.btnSave.hide();
		win.action = params.action ? params.action : 'view';
		win.callback = params.callback ? params.callback : Ext.emptyFn;
		win.owner = params.owner ? params.owner : false;
		win.MedService_id = params.MedService_id;


		var action_upperfolder = {
			name: 'action_upperfolder',
			text: langs('На уровень выше'),
			iconCls: 'x-btn-text',
			icon: 'img/icons/arrow-previous16.png',
			handler: function() {
				win.NavigationPanel.goToUpperLevel();
			}
		};
		win.AnalyzerTestGrid.addActions(action_upperfolder,0);
		var medServiceParams = { 
			MedService_id: win.MedService_id,
			MedServiceType_SysNick: 'lab',
			disabled: true
		};
		win.filterAnalyzer.getStore().load({ params: medServiceParams});
		win.AnalyzerTestGrid.removeAll();
		win.MethodsIfaGrid.removeAll();
		win.NavigationPanel.reset();
		sw.Promed.swMethodsIFAWindow.superclass.show.apply(win, arguments);

		//win.enableEdit(win.action.inlist(["edit", "add"]));
		//if(win.action.inlist(['edit','view']))
		//	win.loadForm({ QcControlMaterial_id: params.QcControlMaterial_id })
		//baseForm.reset();
		//baseForm.setValues(params);
	},

	initComponent: function () {
		var win = this;

		win.NavigationPanel = new Ext.Panel({
			autoHeight: true,
			buttonAlign: 'left',
			currentLevel: 0,
			items: [],
			lastRecord: null,
			layout: 'column',
			bodyStyle: 'width:100%;background:#DFE8F6;padding:2px;',
			store: new Ext.data.JsonStore({
				fields: [
					{ name: 'AnalyzerTest_id', type: 'int' },
					{ name: 'AnalyzerTest_Name', type: 'string' },
					{ name: 'level', type: 'int' }
				],
				data: [],
				key: 'AnalyzerTest_id',
				getByFieldValue: function (field,value) {
					var idx = this.findBy(
						function (rec) {
							return rec.get(field) == value;
						}
					);
					return idx != -1 ? this.getAt(idx) : false;
				}
			}),
			AnalyzerTestRoot_id: 0,
			AnalyzerTestRoot_Name: langs('Корневая папка'),
			getActionUpFolder(id) {
				return Ext.getCmp(this.getActionId(id));
			},
			removeAction: function(id) {
				var actionUpFolder = this.getActionUpFolder(id);
				if(actionUpFolder) {
					this.remove(actionUpFolder);
				}
			},
			addAction: function(id, text) {
				var navPan = this;
				this.add(new Ext.Button({
					id: navPan.getActionId(id),
					iconCls: 'x-btn-text',
					icon: '/img/icons/folder16.png',
					style: 'padding: 1px;',
					text: text,
					handler: function (btn, e) {
						var record = navPan.store.getByFieldValue('AnalyzerTest_id',id);
						if (record) {
							win.showAnalyzerTestContents(record.data);
						}
					}
				}));
			},
			getActionId: function (AnalyzerTest_id) {
				return win.id + '_ActionUpFolder_' + AnalyzerTest_id;
			},
			addRecord: function (data) {
				this.setLevel(data.level);

				// BEGIN произвести поиск по сторе, если уже есть, то не добавлять новую а перейти туда.
				var record;
				this.store.each(function (rec) {
					if (rec.get('AnalyzerTest_id') == data.AnalyzerTest_id) {
						record = rec;
					}
				});

				if (record && record.get('AnalyzerTest_id')) {

					this.store.each(function (rec) {
						if (rec.get('level') > record.get('level')) {
							this.removeAction(rec.get('AnalyzerTest_id'));
							this.store.remove(rec);
						}
					}, this);

					this.buttonIntoText(record);
					this.lastRecord = record;
					this.doLayout();
					this.syncSize();
					return;
				}
				// END произвести поиск по сторе, если уже есть, то не добавлять новую а перейти туда.

				record = new Ext.data.Record({
					AnalyzerTest_id: data.AnalyzerTest_id,
					AnalyzerTest_Name: data.AnalyzerTest_Name,
					level: data.level
				});

				// Добавляем новую запись
				this.store.add([record]);

				// добавляем новую текстовую
				this.lastRecord = record;

				this.addAction(data.AnalyzerTest_id, data.AnalyzerTest_Name);
				this.doLayout();
				this.syncSize();
			},
			getLastRecord: function () {
				var record;
				var level = -1;

				this.store.each(function (rec) {
					if (rec.get('level') > level) {
						record = rec;
					}
				});

				return record;
			},
			getLevel: function () {
				return this.currentLevel;
			},
			goToUpperLevel: function () {
				var currentLevel = this.getLevel();

				if (currentLevel == 0) {
					return false;
				}

				var prevLevel = 0;
				var prevRecord = new Ext.data.Record({
					AnalyzerTest_id: this.AnalyzerTestRoot_id,
					AnalyzerTest_Name: this.AnalyzerTestRoot_Name,
					level: prevLevel
				});

				this.store.each(function (rec) {
					if (rec.get('level') > prevLevel && rec.get('level') < currentLevel) {
						prevLevel = rec.get('level');
						prevRecord = rec;
					}
				});

				win.showAnalyzerTestContents(prevRecord.data);
			},
			reset: function () {
				var navPanel = this;
				navPanel.removeAll();
				navPanel.store.removeAll();
				navPanel.lastRecord = null;
				navPanel.setLevel(0);
				navPanel.addRecord({
					AnalyzerTest_id: navPanel.AnalyzerTestRoot_id,
					AnalyzerTest_Name: navPanel.AnalyzerTestRoot_Name,
					level: 0
				});
			},
			setLevel: function (level) {
				this.currentLevel = (Number(level) > 0 ? Number(level) : 0);
				win.AnalyzerTestGrid.setActionDisabled('action_upperfolder', this.getLevel() == 0);
				return this;
			},
			update: function (data) {
				this.lastRecord = null;

				if (data.AnalyzerTest_id == 0) {
					this.reset();
					win.AnalyzerTestGrid.ViewActions.action_upperfolder.setDisabled(true);
				}
				else {
					this.setLevel(data.level);

					this.store.each(function (record) {
						if (record.get('level') > data.level) {
							this.removeAction(record.get('AnalyzerTest_id'));
							this.store.remove(record);
							this.doLayout();
							this.syncSize();
						}

						if (record.get('level') == data.level) {
							this.lastRecord = record;
						}

						return true;
					}, this);
				}
			}
		});

		win.filterAnalyzer = new sw.Promed.SwAnalyzerCombo({
			fieldLabel: langs('Анализатор'),
			allowBlank: true,
			editable: true,
			listWidth: 300,
			anchor: '100%',
			store: new Ext.data.JsonStore({
				fields: [
					{ type: 'int', name: 'Analyzer_id' },
					{ type: 'int', name: 'Analyzer_Code' },
					{ type: 'int', name: 'AnalyzerModel_id' },
					{ type: 'int', name: 'Analyzer_IsUseAutoReg' },
					{ type: 'date', name: 'Analyzer_begDT' },
					{ type: 'string', name: 'Analyzer_Name' }
				],
				url: '?c=Analyzer&m=loadList',
				key: 'Analyzer_id',
				listeners: {
					load: function(store) {
						let combo = win.filterAnalyzer,
							value = combo.getValue(),
							selectedRec = store.getById(value);
						if(selectedRec) {
							combo.fireEvent('select', combo, selectedRec )
						} else {
							combo.clearValue();
						}
					}
				}
			}),
			listeners: {
				select: function(combo, rec, idx) {
					win.AnalyzerTestGrid.setParam('Analyzer_id', rec.get('Analyzer_id'));
					win.AnalyzerTestGrid.setParam('Analyzer_IsUseAutoReg', rec.get('Analyzer_IsUseAutoReg'));
					win.showAnalyzerTestContents({ level: 0 });
				}
			}
		});

		win.AnalyzerTestGrid = new sw.Promed.ViewFrame({
			object: 'AnalyzerTest',
			autoLoadData: false,
			border: true,
			dataUrl: '/?c=AnalyzerTest&m=loadAnalyzerTestGrid',
			region: 'west',
			width: 500,
			split: true,
			root: 'data',
			totalProperty: 'totalCount',
			useEmptyRecord: false,
			tbar: new Ext.Panel({
				title: langs('Услуги'),
				width: 500,
				border: true,
				layout: 'form',
				bodyStyle: 'width:100%;background:#DFE8F6;padding:1px;padding-top:4px;',
				labelWidth: 70,
				items: [
					win.filterAnalyzer,
					win.NavigationPanel
				]
			}),
			actions: [
				{ name: 'action_add', hidden: true },
				{ name: 'action_edit', hidden: true },
				{ name: 'action_view', hidden: true },
				{ name: 'action_delete', hidden: true },
				{ name: 'action_print', hidden: true },
			],
			stringfields: [
				{ type: 'int', name: 'AnalyzerTest_id', key: true },
				{ type: 'int', name: 'AnalyzerTest_pid', hidden: true },
				{ type: 'int', name: 'AnalyzerTest_isTest', hidden: true },
				{ type: 'string', header: langs('Код теста'), name: 'AnalyzerTest_Code' },
				{ type: 'string', header: langs('Наименование теста'), name: 'AnalyzerTest_Name', id: 'autoexpand' }
			],
			onDblClick: function(grid, number, object){
				this.onEnter();
			},
			onEnter: function() {
				var rec = win.AnalyzerTestGrid.getGrid().getSelectionModel().getSelected();

				if ( !rec || !rec.get('AnalyzerTest_id')) {
					return false;
				}

				if (rec.get('AnalyzerTest_isTest') == 1) {
					win.showAnalyzerTestContents({
						AnalyzerTest_id: rec.get('AnalyzerTest_id'),
						AnalyzerTest_Name: rec.get('AnalyzerTest_Name'),
						level: win.NavigationPanel.getLevel() + 1,
						levelUp: true
					});
				}
			},
			onRowSelect: function (sm, idx, rec) {
				win.MethodsIfaGrid.removeAll();
				win.MethodsIfaGrid.setParam('AnalyzerTest_id', rec.get('AnalyzerTest_id'));
				if(!rec || rec.get('AnalyzerTest_isTest') == 1) {

				} else {
					win.MethodsIfaGrid.loadData();
				}
			},
			getSelected: function () {
				return this.getGrid().getSelectionModel().getSelected();
			},
			checkBeforeLoadData: function() {
				return Boolean(this.getParam('Analyzer_id'));
			}
		});

		win.AnalyzerTestGrid.getGrid().view = new Ext.grid.GridView({
			getRowClass: function (row, index) {
				var cls = '';
				if (row.get('AnalyzerTest_isTest') == 1)
					cls = cls + 'x-grid-rowselect ';
				return cls;
			}
		});

		win.MethodsIfaGrid = new sw.Promed.ViewFrame({
			title: langs('Методики'),
			object: 'MethodsIfa',
			dataUrl: '/?c=MethodsIFAAnalyzerTest&m=loadGrid',
			region: 'center',
			autoLoadData: false,
			useEmptyRecord: false,
			actions: [
				{ name: 'action_add' },
				{ name: 'action_edit', hidden: true },
				{ name: 'action_view', text: langs('Посмотреть референсные значения') },
				{ name: 'action_delete', url: '/?c=MethodsIFAAnalyzerTest&m=doDelete' },
				{ name: 'action_print', hidden: true },
			],
			stringfields: [
				{ type: 'int', name: 'MethodsIFAAnalyzerTest_id', key: true },
				{ type: 'int', name: 'MethodsIFA_id', hidden: true },
				{ type: 'string', header: langs('Наименование методики'), name: 'MethodsIFA_Name', id: 'autoexpand'},
				{ type: 'string', header: langs('Производитель'), name: 'FULLNAME' },
				{ type: 'string', header: langs('Единицы изменения'), name: 'Unit_Name' },
				{ type: 'string', header: langs('Чувствительность'), name: 'MethodsIFA_Sens' },
				{ type: 'string', header: langs('Диапазон измерений'), name: 'diap' },
				{ type: 'string', header: langs('Время инкубации(мин)'), name: 'MethodsIFA_IncTime' },
				{ type: 'string', header: langs('Температура инкубации(C)'), name: 'MethodsIFA_IncTemp' },
				{ type: 'string', header: langs('Объем пробы'), name: 'MethodsIFA_TestVolume' },
				{ type: 'string', header: langs('Длина волн'), name: 'MethodsIFA_Wavelength' }
			],
			getSelected: function() {
				return this.getGrid().getSelectionModel().getSelected();
			},
			function_action_add: function() {
				this.openWindow('add');
			},
			function_action_view: function() {
				this.openWindow('view');
			},
			deleteRecord: function () {
				var viewframe = this,
					rec = viewframe.getSelected();
				if(!rec) return;

				sw.swMsg.show({
					icon: Ext.MessageBox.QUESTION,
					msg: langs('Вы хотите удалить запись?'),
					title: langs('Подтверждение'),
					buttons: Ext.Msg.YESNO,
					fn: function (buttonId, text, obj) {
						if ('yes' == buttonId) {
							ajaxRequest({
								url: '/?c=MethodsIFAAnalyzerTest&m=doDelete',
								params: {
									MethodsIFAAnalyzerTest_id: rec.get('MethodsIFAAnalyzerTest_id')
								},
								maskEl: viewframe.getEl(),
								maskText: langs('Удаление'),
								onSuccess: function() {
									viewframe.loadData();
									win.callback();
								}
							});
						}
					}
				})
			},
			openWindow(action) {
				var viewframe = this,
					winName = action == 'add' ? 'swMethodsIFAAnalyzerTestWindow':'swAnalyzerTestEditWindow',
					rec = win.AnalyzerTestGrid.getSelected();

				if (!rec || rec.get('AnalyzerTest_isTest') == 1) {
					sw.swMsg.alert(langs('Сообщение'), langs('Выберите тест'));
					return;
				}

				var params = {
					action: action,
					AnalyzerTest_id: rec.get('AnalyzerTest_id'),
					AnalyzerTest_pid: rec.get('AnalyzerTest_pid'),
					AnalyzerTest_isTest: rec.get('AnalyzerTest_isTest'),
					Analyzer_id: win.AnalyzerTestGrid.getParam('Analyzer_id'),
					Analyzer_IsUseAutoReg: win.AnalyzerTestGrid.getParam('Analyzer_IsUseAutoReg'),
					callback: function() {
						win.callback();
						viewframe.loadData();
					}
				};

				getWnd(winName).show(params);
			}
		});
		var mainPanel = {
			layout: 'border',
			items: [
				win.AnalyzerTestGrid,
				win.MethodsIfaGrid
			]
		};
		Ext.apply(win, mainPanel);
		sw.Promed.swMethodsIFAWindow.superclass.initComponent.apply(this, arguments);
	},
	showAnalyzerTestContents: function (data) {
		var win = this;
		if (typeof data != 'object') {
			return false;
		}

		if (data.level == 0) {
			win.NavigationPanel.reset();
			win.AnalyzerTestGrid.setParam('AnalyzerTest_pid', null);
			win.AnalyzerTestGrid.removeAll();
			win.AnalyzerTestGrid.loadData();
		}
		else {
			win.AnalyzerTestGrid.removeAll();
			win.NavigationPanel.setLevel(0);
			win.AnalyzerTestGrid.setParam('AnalyzerTest_pid', data.AnalyzerTest_id);
			win.AnalyzerTestGrid.loadData();
		}

		if (data.levelUp) {
			win.NavigationPanel.addRecord(data);
		}
		else {
			win.NavigationPanel.update(data);
		}
	}
});