/**
 * swBleedingCardEditWindow - Карта наблюдения для оценки кровотечения
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @input data: action - действие (add, edit, view)
 *              BleedingCard_id - ID Карты для редактирования или просмотра
 *
 * @package      Stac
 * @access       public
 * @copyright    Copyright (c) 2019 Swan Ltd.
 * @author       Ivan Drachyov (i.drachev@swan-it.ru)
 * @version      12.2019
 *
 */

sw.Promed.swBleedingCardEditWindow = Ext.extend(sw.Promed.BaseForm, {
	/* свойства */
	autoScroll: true,
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	height: 600,
	layout: 'form',
	listeners: {
		'hide': function (win) {
			win.onHide();
		},
		'maximize': function (win) {
			win.BleedingCardConditionPanel.doLayout();
			win.BleedingCardDrugPanel.doLayout();
			win.BleedingCardSolutionPanel.doLayout();
		},
		'restore': function (win) {
			win.fireEvent('maximize', win);
		}
	},
	maximizable: false,
	modal: true,
	objects: [
		'BleedingCardCondition',
		'BleedingCardSolution',
		'BleedingCardDrug'
	],
	plain: true,
	resizable: false,
	width: 1000,

	/* методы */
	callback: Ext.emptyFn,
	deleteRecord: function(object) {
		if ( Ext.isEmpty(object) || !object.inlist(this.objects)) {
			return false;
		}

		var win = this;

		if ( win.action == 'view' ) {
			return false;
		}

		var grid = win.findById(win.id + '_' + object + 'Grid').getGrid();

		if ( !grid || !grid.getSelectionModel() || !grid.getSelectionModel().getSelected() || Ext.isEmpty(grid.getSelectionModel().getSelected().get(object + '_id')) ) {
			return false;
		}

		var record = grid.getSelectionModel().getSelected();

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( buttonId == 'yes' ) {
					switch ( Number(record.get('RecordStatus_Code')) ) {
						case 0:
							grid.getStore().remove(record);
							break;

						case 1:
						case 2:
							record.set('RecordStatus_Code', 3);
							record.commit();
							win.filterGridStore(grid.getStore());
							break;
					}

					if ( grid.getStore().getCount() > 0 ) {
						grid.getView().focusRow(0);
						grid.getSelectionModel().selectFirstRow();
					}
				}
			},
			icon: Ext.MessageBox.QUESTION,
			msg: langs('Удалить запись?'),
			title: langs('Вопрос')
		});

		return true;
	},
	doSave: function() {
		var win = this;

		if ( win.formStatus == 'save' ) {
			return false;
		}

		win.formStatus = 'save';

		var
			form = win.FormPanel,
			base_form = form.getForm();

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					win.formStatus = 'edit';
					form.getFirstInvalidEl().focus(false);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var
			grid,
			gridData,
			object,
			params = {};

		// Собираем данные их гридов
		for ( var i in win.objects ) {
			gridData = [];
			object = win.objects[i];

			if ( typeof object != 'string' || typeof win.findById(win.id + '_' + object + 'Grid') != 'object' ) {
				continue;
			}

			grid = win.findById(win.id + '_' + object + 'Grid').getGrid();
			grid.getStore().clearFilter();

			if ( grid.getStore().getCount() > 0 && !Ext.isEmpty(grid.getStore().getAt(0).get(object + '_id')) ) {
				gridData = getStoreRecords(grid.getStore(), {
					convertDateFields: true,
					exceptionFields: [
						'Diuresis_Name', 'CentralNervousSystem_Name', 'PulseOximetry_Name', 'DrugComplexMnn_Name',
						'PrescriptionIntroType_Name', 'SolutionType_Name', object + '_setDT',
						'BleedingCardCondition_Pressure'
					]
				});
			}

			params[object + 'Data'] = Ext.util.JSON.encode(gridData);
			win.filterGridStore(grid.getStore());
		}

		var loadMask = new Ext.LoadMask(win.getEl(), { msg: "Подождите, идет сохранение карты..." });
		loadMask.show();

		base_form.submit({
			failure: function(result_form, action) {
				win.formStatus = 'edit';
				loadMask.hide();

				if ( action.result ) {
					if ( action.result.Error_Msg ) {
						sw.swMsg.alert(langs('Ошибка'), action.result.Error_Msg);
					}
					else {
						sw.swMsg.alert(langs('Ошибка'), langs('При сохранении произошли ошибки [Тип ошибки: 1]'));
					}
				}
			},
			params: params,
			success: function(result_form, action) {
				win.formStatus = 'edit';
				loadMask.hide();

				if ( action.result ) {
					if ( action.result.BleedingCard_id ) {
						var BleedingCard_id = action.result.BleedingCard_id;

						base_form.findField('BleedingCard_id').setValue(BleedingCard_id);

						win.callback({
							bleedingCardData: {
								'accessType': 'edit',
								'BleedingCard_id': BleedingCard_id
							}
						});
						win.hide();
					}
					else {
						if ( action.result.Error_Msg ) {
							sw.swMsg.alert(langs('Ошибка'), action.result.Error_Msg);
						}
						else {
							sw.swMsg.alert(langs('Ошибка'), langs('При сохранении произошли ошибки [Тип ошибки: 3]'));
						}
					}
				}
				else {
					sw.swMsg.alert(langs('Ошибка'), langs('При сохранении произошли ошибки [Тип ошибки: 2]'));
				}
			}
		});
	},
	filterGridStore: function(store) {
		store.clearFilter();
		store.filterBy(function(rec) {
			return (Number(rec.get('RecordStatus_Code')) != 3);
		});

		return true;
	},
	onHide: Ext.emptyFn,
	openRecord: function (object, action) {
		if ( Ext.isEmpty(object) || !object.inlist(this.objects)) {
			return false;
		}

		if ( typeof action != 'string' || !(action.inlist([ 'add', 'edit', 'view' ])) ) {
			return false;
		}

		if ( getWnd('sw' + object + 'EditWindow').isVisible() ) {
			sw.swMsg.alert(langs('Ошибка'), langs('Окно редактирования уже открыто'));
			return false;
		}

		var
			win = this,
			formParams = {},
			grid = win.findById(win.id + '_' + object + 'Grid').getGrid(),
			params = {};

		params.action = action;
		params.callback = function(data) {
			if ( typeof data != 'object' ) {
				sw.swMsg.alert(langs('Ошибка'), langs('Отсутствуют необходимые данные'));
				return false;
			}

			data.RecordStatus_Code = 0;

			var index = grid.getStore().findBy(function(rec) {
				return (!Ext.isEmpty(rec.get(object + '_id')) && rec.get(object + '_id') == data[object + '_id']);
			});

			if ( index == -1 ) {
				data[object + '_id'] = -swGenTempId(grid.getStore());
			}

			if ( index >= 0 ) {
				var record = grid.getStore().getAt(index);

				if ( record.get('RecordStatus_Code') == 1 ) {
					data.RecordStatus_Code = 2;
				}

				var grid_fields = [];

				grid.getStore().fields.eachKey(function(key, item) {
					grid_fields.push(key);
				});

				for ( i = 0; i < grid_fields.length; i++ ) {
					record.set(grid_fields[i], data[grid_fields[i]]);
				}

				record.commit();
			}
			else {
				if ( grid.getStore().getCount() == 1 && Ext.isEmpty(grid.getStore().getAt(0).get(object + '_id')) ) {
					grid.getStore().removeAll();
				}

				grid.getStore().loadData([ data ], true);
			}

			return true;
		};

		if ( action == 'add' ) {
			params.formParams = formParams;
			params.onHide = function() {
				if ( grid.getStore().getCount() > 0 ) {
					grid.getView().focusRow(0);
				}
			};
		}
		else {
			if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get(object + '_id') ) {
				return false;
			}

			var selectedRecord = grid.getSelectionModel().getSelected();

			formParams = selectedRecord.data;
			params.onHide = function() {
				grid.getView().focusRow(grid.getStore().indexOf(selectedRecord));
			};
		}

		params.formParams = formParams;
		getWnd('sw' + object + 'EditWindow').show(params);

		return true;
	},
	show: function () {
		sw.Promed.swBleedingCardEditWindow.superclass.show.apply(this, arguments);

		var win = this;

		win.center();

		var base_form = win.FormPanel.getForm();
		base_form.reset();

		win.action = null;
		win.callback = Ext.emptyFn;
		win.onHide = Ext.emptyFn;

		if ( !arguments[0] || Ext.isEmpty(arguments[0].formParams) || Ext.isEmpty(arguments[0].formParams.EvnSection_id)) {
			sw.swMsg.alert('Сообщение', 'Неверные параметры', function() { win.hide(); });
			return false;
		}

		base_form.setValues(arguments[0].formParams);

		if ( arguments[0].action ) {
			win.action = arguments[0].action;
		}

		if ( typeof arguments[0].callback == 'function' ) {
			win.callback = arguments[0].callback;
		}

		if ( typeof arguments[0].onHide == 'function' ) {
			win.onHide = arguments[0].onHide;
		}

		win.BleedingCardConditionGrid.getGrid().getStore().removeAll();
		win.BleedingCardSolutionGrid.getGrid().getStore().removeAll();
		win.BleedingCardDrugGrid.getGrid().getStore().removeAll();

		win.BleedingCardConditionGrid.setActionDisabled('action_add', win.action == 'view');
		win.BleedingCardConditionGrid.setActionDisabled('action_edit', win.action == 'view');
		win.BleedingCardConditionGrid.setActionDisabled('action_delete', win.action == 'view');
		win.BleedingCardSolutionGrid.setActionDisabled('action_add', win.action == 'view');
		win.BleedingCardSolutionGrid.setActionDisabled('action_edit', win.action == 'view');
		win.BleedingCardSolutionGrid.setActionDisabled('action_delete', win.action == 'view');
		win.BleedingCardDrugGrid.setActionDisabled('action_add', win.action == 'view');
		win.BleedingCardDrugGrid.setActionDisabled('action_edit', win.action == 'view');
		win.BleedingCardDrugGrid.setActionDisabled('action_delete', win.action == 'view');

		var loadMask = new Ext.LoadMask(win.getEl(), {msg: LOAD_WAIT});
		loadMask.show();

		switch ( win.action ) {
			case 'add':
				win.setTitle(WND_HOSP_BCADD);
				win.enableEdit(true);

				loadMask.hide();
				break;

			case 'edit':
			case 'view':
				if ( win.action == 'edit' ) {
					win.setTitle(WND_HOSP_BCEDIT);
					win.enableEdit(true);
				}
				else {
					win.setTitle(WND_HOSP_BCVIEW);
					win.enableEdit(false);
				}

				win.BleedingCardSolutionGrid.loadData({
					globalFilters: {
						BleedingCard_id: base_form.findField('BleedingCard_id').getValue()
					},
					noFocusOnLoad: true
				});

				win.BleedingCardConditionGrid.loadData({
					globalFilters: {
						BleedingCard_id: base_form.findField('BleedingCard_id').getValue()
					}
				});

				win.BleedingCardDrugGrid.loadData({
					globalFilters: {
						BleedingCard_id: base_form.findField('BleedingCard_id').getValue()
					},
					noFocusOnLoad: true
				});

				loadMask.hide();
				break;

			default:
				loadMask.hide();
				break;
		}
	},

	/* конструктор */
	initComponent: function () {
		var win = this;

		win.BleedingCardConditionGrid = new sw.Promed.ViewFrame({
			actions: [
				{ name: 'action_add', handler: function() { win.openRecord('BleedingCardCondition', 'add'); } },
				{ name: 'action_edit', handler: function() { win.openRecord('BleedingCardCondition', 'edit'); } },
				{ name: 'action_view', handler: function() { win.openRecord('BleedingCardCondition', 'view'); } },
				{ name: 'action_delete', handler: function() { win.deleteRecord('BleedingCardCondition'); } },
				{ name: 'action_refresh', disabled: true, hidden: true },
				{ name: 'action_print' }
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 350,
			autoLoadData: false,
			border: false,
			dataUrl: '/?c=BleedingCard&m=loadBleedingCardConditionGrid',
			id: win.id + '_BleedingCardConditionGrid',
			object: 'BleedingCardCondition',
			paging: false,
			region: 'center',
			stringfields: [
				{ name: 'BleedingCardCondition_id', type: 'int', header: 'ID', key: true },
				{ name: 'RecordStatus_Code', type: 'int', hidden: true },
				{ name: 'BleedingCardCondition_setDate', type: 'date', hidden: true },
				{ name: 'BleedingCardCondition_setTime', type: 'string', hidden: true },
				{ name: 'BleedingCardCondition_SistolPress', type: 'int', hidden: true },
				{ name: 'BleedingCardCondition_DiastolPress', type: 'int', hidden: true },
				{ name: 'Diuresis_id', type: 'int', hidden: true },
				{ name: 'CentralNervousSystem_id', type: 'int', hidden: true },
				{ name: 'PulseOximetry_id', type: 'int', hidden: true },
				{ name: 'BleedingCardCondition_setDT', type: 'datetime', dateFormat: 'd.m.Y H:i', header: langs('Дата и время'), width: 110 },
				{ name: 'BleedingCardCondition_Temperature', type: 'float', header: langs('Температрура'), width: 80 },
				{ name: 'BleedingCardCondition_Pressure', type: 'string', header: langs('АД'), width: 80 },
				{ name: 'BleedingCardCondition_Pulse', type: 'int', header: langs('Пульс'), width: 50 },
				{ name: 'BleedingCardCondition_BreathFrequency', type: 'int', header: langs('Частота дыхания'), width: 100 },
				{ name: 'Diuresis_Name', type: 'string', header: langs('Диурез'), width: 100 },
				{ name: 'BleedingCardCondition_CatheterTime', type: 'string', header: langs('Время катетеризации'), width: 100 },
				{ name: 'CentralNervousSystem_Name', type: 'string', header: langs('ЦНС'), width: 120, id: 'autoexpand' },
				{ name: 'PulseOximetry_Name', type: 'string', header: 'SpO<sub>2in</sub>', width: 60 },
				{ name: 'BleedingCardCondition_TotalScore', type: 'int', header: langs('Общее количество баллов'), width: 80 }
			],
			toolbar: true
		});

		win.BleedingCardDrugGrid = new sw.Promed.ViewFrame({
			actions: [
				{ name: 'action_add', handler: function() { win.openRecord('BleedingCardDrug', 'add'); } },
				{ name: 'action_edit', handler: function() { win.openRecord('BleedingCardDrug', 'edit'); } },
				{ name: 'action_view', handler: function() { win.openRecord('BleedingCardDrug', 'view'); } },
				{ name: 'action_delete', handler: function() { win.deleteRecord('BleedingCardDrug'); } },
				{ name: 'action_refresh', disabled: true, hidden: true },
				{ name: 'action_print' }
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 350,
			autoLoadData: false,
			border: false,
			dataUrl: '/?c=BleedingCard&m=loadBleedingCardDrugGrid',
			id: win.id + '_BleedingCardDrugGrid',
			object: 'BleedingCardDrug',
			paging: false,
			region: 'center',
			stringfields: [
				{ name: 'BleedingCardDrug_id', type: 'int', header: 'ID', key: true },
				{ name: 'RecordStatus_Code', type: 'int', hidden: true },
				{ name: 'BleedingCardDrug_setDate', type: 'date', hidden: true },
				{ name: 'BleedingCardDrug_setTime', type: 'string', hidden: true },
				{ name: 'DrugComplexMnn_id', type: 'int', hidden: true },
				{ name: 'PrescriptionIntroType_id', type: 'int', hidden: true },
				{ name: 'GoodsUnit_id', type: 'int', hidden: true },
				{ name: 'BleedingCardDrug_setDT', type: 'datetime', dateFormat: 'd.m.Y H:i', header: langs('Дата и время'), width: 120 },
				{ name: 'DrugComplexMnn_Name', type: 'string', header: langs('Лекарственное средство'), width: 300, id: 'autoexpand' },
				{ name: 'PrescriptionIntroType_Name', type: 'string', header: langs('Способ применения'), width: 200 },
				{ name: 'BleedingCardDrug_Dosage', type: 'float', header: langs('Дозировка'), width: 100 },
				{ name: 'BleedingCardDrug_TotalScore', type: 'int', header: langs('Общее количество'), width: 100 }
			],
			toolbar: true
		});

		win.BleedingCardSolutionGrid = new sw.Promed.ViewFrame({
			actions: [
				{ name: 'action_add', handler: function() { win.openRecord('BleedingCardSolution', 'add'); } },
				{ name: 'action_edit', handler: function() { win.openRecord('BleedingCardSolution', 'edit'); } },
				{ name: 'action_view', handler: function() { win.openRecord('BleedingCardSolution', 'view'); } },
				{ name: 'action_delete', handler: function() { win.deleteRecord('BleedingCardSolution'); } },
				{ name: 'action_refresh', disabled: true, hidden: true },
				{ name: 'action_print' }
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 350,
			autoLoadData: false,
			border: false,
			dataUrl: '/?c=BleedingCard&m=loadBleedingCardSolutionGrid',
			id: win.id + '_BleedingCardSolutionGrid',
			object: 'BleedingCardSolution',
			paging: false,
			stringfields: [
				{ name: 'BleedingCardSolution_id', type: 'int', header: 'ID', key: true },
				{ name: 'RecordStatus_Code', type: 'int', hidden: true },
				{ name: 'BleedingCardSolution_setDate', type: 'date', hidden: true },
				{ name: 'BleedingCardSolution_setTime', type: 'string', hidden: true },
				{ name: 'SolutionType_id', type: 'int', hidden: true },
				{ name: 'BleedingCardSolution_setDT', type: 'datetime', dateFormat: 'd.m.Y H:i', header: langs('Дата и время начала введения'), width: 120 },
				{ name: 'SolutionType_Name', type: 'string', header: langs('Вид раствора'), width: 200, id: 'autoexpand' },
				{ name: 'BleedingCardSolution_Volume', type: 'int', header: langs('Объем'), width: 100 }
			],
			toolbar: true
		});

		win.BleedingCardDrugPanel = new sw.Promed.Panel({
			border: true,
			height: 200,
			id: win.id + '_BleedingCardDrugPanel',
			isLoaded: false,
			layout: 'border',
			style: 'margin-bottom: 0.5em;',
			title: langs('Лекарственные средства'),
			items: [
				win.BleedingCardDrugGrid
			]
		});

		win.BleedingCardConditionPanel = new sw.Promed.Panel({
			border: true,
			height: 200,
			id: win.id + '_BleedingCardConditionPanel',
			isLoaded: false,
			layout: 'border',
			style: 'margin-bottom: 0.5em;',
			title: langs('Оценка состояния'),
			items: [
				win.BleedingCardConditionGrid
			]
		});

		win.BleedingCardSolutionPanel = new sw.Promed.Panel({
			border: true,
			height: 200,
			id: win.id + '_BleedingCardSolutionPanel',
			isLoaded: false,
			layout: 'border',
			style: 'margin-bottom: 0.5em;',
			title: langs('Растворы'),
			items: [
				win.BleedingCardSolutionGrid
			]
		});

		win.FormPanel = new Ext.form.FormPanel({
			bodyBorder: false,
			bodyStyle: 'padding: 0',
			border: false,
			frame: false,
			id: 'BleedingCardEditForm',
			items: [{
				name: 'accessType',
				xtype: 'hidden'
			}, {
				name: 'BleedingCard_id',
				xtype: 'hidden'
			}, {
				name: 'EvnSection_id',
				xtype: 'hidden'
			}],
			labelAlign: 'right',
			labelWidth: 180,
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
				{name: 'accessType'},
				{name: 'BleedingCard_id'},
				{name: 'EvnSection_id'}
			]),
			region: 'north',
			url: '/?c=BleedingCard&m=saveBleedingCard'
		});

		Ext.apply(win, {
			buttons: [{
				handler: function () {
					win.doSave();
				},
				iconCls: 'save16',
				onShiftTabAction: function () {
					//;
				},
				onTabAction: function () {
					win.buttons[win.buttons.length - 1].focus();
				},
				tabIndex: win.tabindex + 1,
				text: BTN_FRMSAVE
			}, {
				text: '-'
			},
			HelpButton(win, -1),
			{
				handler: function () {
					win.hide();
				},
				iconCls: 'cancel16',
				onShiftTabAction: function () {
					win.buttons[0].focus();
				},
				onTabAction: function () {
					win.buttons[0].focus();
				},
				tabIndex: win.tabindex + 2,
				text: BTN_FRMCANCEL
			}],
			items: [
				win.FormPanel,
				win.BleedingCardConditionPanel,
				win.BleedingCardSolutionPanel,
				win.BleedingCardDrugPanel
			]
		});

		sw.Promed.swBleedingCardEditWindow.superclass.initComponent.apply(win, arguments);
	}
});