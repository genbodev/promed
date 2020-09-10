/**
* swEvnVizitPLDoublesWindow - окно дублей посещений
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Polka
* @access       public
* @copyright    Copyright (c) 2017 Swan Ltd.
*/
/*NO PARSE JSON*/

sw.Promed.swEvnVizitPLDoublesWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swEvnVizitPLDoublesWindow',
	objectSrc: '/jscore/Forms/Polka/swEvnVizitPLDoublesWindow.js',
	action: null,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: false,
	closeAction: 'hide',
	collapsible: true,
	formStatus: 'edit',
	height: 300,
	title: 'Дублирующие посещения',
	id: 'EvnVizitPLDoublesWindow',
	show: function() {
		sw.Promed.swEvnVizitPLDoublesWindow.superclass.show.apply(this, arguments);

		var win = this;

		if ( !arguments[0] || !arguments[0].EvnVizitPLDoublesData) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() {this.hide();}.createDelegate(this) );
			return false;
		}

		this.callback = Ext.emptyFn;
		if (arguments[0].callback) {
			this.callback = arguments[0].callback;
		}

		this.EvnVizitPLDoublesData = arguments[0].EvnVizitPLDoublesData;
		this.EvnVizitPLDoublesGrid.removeAll({ clearAll: true });
		this.EvnVizitPLDoublesGrid.getGrid().getStore().loadData(win.EvnVizitPLDoublesData);
	},
	doSave: function() {
		var win = this;
		var EvnVizitPLDoublesData = getStoreRecords( win.EvnVizitPLDoublesGrid.getGrid().getStore() );

		// При выполнении сохранения проверяется, что как минимум у одного посещения указано «Выгружать в реестр» «Да».
		var check = false;
		for(var k in EvnVizitPLDoublesData) {
			if (EvnVizitPLDoublesData[k].VizitPLDouble_id && EvnVizitPLDoublesData[k].VizitPLDouble_id == 1) {
				check = true;
			}
		}
		if (!check) {
			sw.swMsg.alert('Ошибка', 'Хотя бы одно посещение необходимо выгружать в реестр');
			return false;
		}

		win.callback({
			EvnVizitPLDoublesData: Ext.util.JSON.encode(EvnVizitPLDoublesData)
		});
		win.hide();
	},
	blockStartingEditing: false,
	getVizitPLDoubleEditor: function() {
		var win = this;
		return new sw.Promed.SwCommonSprCombo({
			allowBlank: false,
			comboSubject: 'VizitPLDouble',
			codeField: 'VizitPLDouble_Code',
			editable: true,
			enableKeyEvents: true,
			fireAfterEditOnEmpty: true,
			listeners: {
				'render': function() {
					// как появился нужно и прогрузиться
					this.getStore().load();
				}
			}
		});
	},
	startEditData: function() {
		if (this.blockStartingEditing) {
			return false;
		}
		this.blockStartingEditing = true;

		var win = this;
		var grid = this.EvnVizitPLDoublesGrid.getGrid();

		if (win.action == 'view') {
			this.blockStartingEditing = false;
			return false;
		}

		// если ещё редактируется
		var editor = grid.getColumnModel().getCellEditor(4);
		if (editor && !editor.hidden) {
			this.blockStartingEditing = false;
			return false;
		}

		var cell = grid.getSelectionModel().getSelectedCell();
		var record = grid.getSelectionModel().getSelected();
		if ( !record || !record.get('EvnVizitPL_id') || record.get('accessType') != 'edit' ) {
			this.blockStartingEditing = false;
			return false;
		}

		grid.getSelectionModel().select(cell[0], 4);
		grid.getView().focusCell(cell[0], 4);

		grid.getColumnModel().setEditor(4, new Ext.grid.GridEditor(win.getVizitPLDoubleEditor()));

		grid.getColumnModel().setEditable(4, true);
		grid.startEditing(cell[0], 4);
		this.blockStartingEditing = false;
	},
	initComponent: function() {
		var win = this;

		this.EvnVizitPLDoublesGrid = new sw.Promed.ViewFrame({
			uniqueId: true,
			tbar: false,
			actions: [
				{ name: 'action_add', disabled: true, hidden: true },
				{ name: 'action_edit', disabled: true, hidden: true },
				{ name: 'action_view', disabled: true, hidden: true },
				{ name: 'action_delete', disabled: true, hidden: true },
				{ name: 'action_refresh', disabled: true, hidden: true },
				{ name: 'action_print', disabled: true, hidden: true },
				{ name: 'action_save', disabled: true, hidden: true }
			],
			autoLoadData: false,
			border: false,
			dataUrl: '/?c=EvnVizitPL&m=loadEvnVizitPLDoubles',
			paging: false,
			region: 'center',
			saveAtOnce: false,
			saveAllParams: false,
			selectionModel: 'cell',
			onCellSelect: function(sm,rowIdx,colIdx) {
				win.startEditData();
			},
			stringfields: [
				{ name: 'EvnVizitPL_id', type: 'string', header: 'ID', key: true },
				{ name: 'LpuSection_Name', type: 'string', header: 'Отделение', width: 200 },
				{ name: 'MedPersonal_Fio', type: 'string', header: 'Врач', width: 100, id: 'autoexpand' },
				{ name: 'EvnVizitPL_setDate', type: 'string', header: 'Дата', width: 100 },
				{ name: 'VizitPLDouble_id', renderer: function(v, p, r) {
					if (v) {
						switch (v.toString()) {
							case '1':
								v = 'Да';
								break;
							case '2':
								v = 'Нет';
								break;
							case '3':
								v = 'Не определено';
								break;
						}
					}

					return v;
				}, header: 'Выгружать в реестр', width: 150 },
				{ name: 'accessType', type: 'string', editor: win.getVizitPLDoubleEditor(), hidden: true }
			],
			onSelectionChange: function() {
				win.EvnVizitPLDoublesGrid.getGrid().getColumnModel().setEditable(4, false);
			}.createDelegate(this),
			onAfterEdit: function(o) {
				o.grid.stopEditing(true);
				o.grid.getColumnModel().setEditable(4, false);
				if (o && o.field) {
					if (o.field == 'VizitPLDouble_id') {
						o.record.set('VizitPLDouble_id', o.value);
						o.record.commit();
					}
				}
			},
			toolbar: true
		});
		
		
		Ext.apply(this, {
			layout: 'border',
			buttons: [{
				handler: function() {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'save16',
				text: BTN_FRMSAVE
			}, {
				text: '-'
			},
			HelpButton(this, -1),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],
			items: [
				win.EvnVizitPLDoublesGrid
			]
		});
		
		sw.Promed.swEvnVizitPLDoublesWindow.superclass.initComponent.apply(this, arguments);
	},
	width: 700
});
