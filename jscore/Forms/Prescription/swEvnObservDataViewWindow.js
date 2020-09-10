/**
* swEvnObservDataViewWindow - просмотр результатов наблюдений.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Prescription
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage@swan.perm.ru)
* @version      16.12.2011
* @comment      Префикс для id компонентов EOBSDVW (EvnObservDataViewWindow)
*/
/*NO PARSE JSON*/

sw.Promed.swEvnObservDataViewWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swEvnObservDataViewWindow',
	objectSrc: '/jscore/Forms/Prescription/swEvnObservDataViewWindow.js',

	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	collapsible: false,
	draggable: true,
	height: 550,
	id: 'EvnObservDataViewWindow',
	initComponent: function() {
		this.reader = new Ext.data.JsonReader({
			id: 'EvnObservData_id'
		}, [
			{ name: 'EvnObservData_id' },
			{ name: 'ObservTimeType_id' },
			{ name: 'EvnObserv_setDate', type: 'date', dateFormat: 'd.m.Y' },
			{ name: 'ObservParamType_Name' },
			{ name: 'ObservTimeType_Name' },
			{ name: 'EvnObservData_Value' }
		]);

		this.gridStore = new Ext.data.GroupingStore({
			autoLoad: false,
			groupField: 'ObservParamType_Name',
			reader: this.reader,
			sortInfo: {
				field: 'EvnObserv_setDate',
				direction: 'ASC'
			},
			url: '/?c=EvnPrescr&m=loadEvnObservDataViewGrid'
		});

		this.EvnObservDataGrid = new Ext.grid.GridPanel({
			autoExpandColumn: 'autoexpand_observ',
			autoExpandMin: 300,
			clearStore: function() {
				if ( this.getEl() ) {
					this.getStore().removeAll();
				}
			},
			columns: [{
				dataIndex: 'EvnObserv_setDate',
				header: lang['data'],
				renderer: Ext.util.Format.dateRenderer('d.m.Y'),
				resizable: true,
				sortable: true,
				width: 70
			}, {
				dataIndex: 'ObservTimeType_Name',
				header: "Время измерения",
				resizable: true,
				sortable: true,
				width: 100
			}, {
				dataIndex: 'ObservParamType_Name',
				header: lang['parametr'],
				id: 'autoexpand_observ',
				resizable: true,
				sortable: true
			}, {
				dataIndex: 'EvnObservData_Value',
				header: lang['znachenie'],
				resizable: true,
				sortable: true
			}],
			focus: function () {
				if ( this.getStore().getCount() > 0 ) {
					this.getView().focusRow(0);
					this.getSelectionModel().selectFirstRow();
				}
			},
			frame: false,
			layout: 'fit',
			loadMask: true,
			loadStore: function(params) {
				if ( !this.params ) {
					this.params = null;
				}

				if ( params ) {
					this.params = params;
				}

				this.clearStore();
				this.getStore().load({
					params: this.params
				});
			},
			region: 'center',
			sm: new Ext.grid.RowSelectionModel({
				listeners: {
					'rowselect': function(sm, rowIdx, record) {
						//
					}.createDelegate(this),
					'rowdeselect': function(sm, rowIdx, record) {
						//
					}
				},
				singleSelect: true
			}),
			store: this.gridStore,
			stripeRows: true,
			tbar: new sw.Promed.Toolbar({
				buttons: [{
					handler: function() {
						this.refreshGrid();
					}.createDelegate(this),
					iconCls: 'refresh16',
					text: lang['obnovit'],
					tooltip: lang['obnovit']
				}]
			}),
			view: new Ext.grid.GroupingView( {
				forceFit: false,
				groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length.inlist([2, 3, 4]) ? "записи" : (values.rs.length == 1 ? "запись" : "записей")]})'
			})
		});

		this.PersonInfo = new sw.Promed.PersonInformationPanelShort({
			id: 'EOBSDVW_PersonInformationFrame',
			region: 'north'
		});

		Ext.apply(this, {
			buttons: [{
				text: '-'
			},
			HelpButton(this, -1),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				onShiftTabAction: function () {
					// this.buttons[1].focus();
				}.createDelegate(this),
				onTabAction: function () {
					//
				}.createDelegate(this),
				// tabIndex: TABINDEX_ESTEF + 36,
				text: BTN_FRMCLOSE
			}],
			items: [
				this.PersonInfo,
				this.EvnObservDataGrid
			],
			layout: 'border'
		});

		sw.Promed.swEvnObservDataViewWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('EvnObservDataViewWindow');

			switch ( e.getKey() ) {
				case Ext.EventObject.J:
					current_window.hide();
				break;
			}
		},
		key: [
			Ext.EventObject.J
		],
		scope: this,
		stopEvent: false
	}],
	layout: 'border',
	listeners: {
		'beforehide': function(win) {
			//
		},
		'hide': function(win) {
			win.onHide();
		},
		'maximize': function(win) {
			//
		},
		'restore': function(win) {
			//
		}
	},
	maximizable: false,
	maximized: false,
	minHeight: 550,
	minWidth: 750,
	modal: true,
	onHide: Ext.emptyFn,
	plain: true,
	refreshGrid: function() {
		this.EvnObservDataGrid.getStore().reload();
	},
	resizable: false,
	show: function() {
		sw.Promed.swEvnObservDataViewWindow.superclass.show.apply(this, arguments);

		// this.restore();
		this.center();
		// this.maximize();

		this.EvnObservDataGrid.clearStore();

		if ( arguments[0] ) {
			if ( arguments[0].LpuSection_Name ) {
				this.setTitle(lang['rezultatyi_nablyudeniy'] + ' - ' + arguments[0].LpuSection_Name);
			}

			this.PersonInfo.load({
				Person_id: arguments[0].formParams.Person_id
			});

			if ( arguments[0].formParams && arguments[0].formParams.EvnObserv_pid ) {
				this.EvnObservDataGrid.loadStore({
					EvnObserv_pid: arguments[0].formParams.EvnObserv_pid
				});
			}
			else {
				this.hide();
			}
		}
		else {
			this.setTitle(lang['rezultatyi_nablyudeniy']);
		}
	},
	title: lang['rezultatyi_nablyudeniy'],
	width: 750
});