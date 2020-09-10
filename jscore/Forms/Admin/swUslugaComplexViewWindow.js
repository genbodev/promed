/**
* swUslugaComplexViewWindow - просмотр справочника комплексных услуг.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage1981@gmail.com)
* @version      0.001-11.02.2010
* @comment      Префикс для id компонентов SPRCUVW (SprComplexUslugaViewWindow)
*/

sw.Promed.swUslugaComplexViewWindow = Ext.extend(sw.Promed.BaseForm, {
	closable: true,
	closeAction: 'hide',
	collapsible: false,
	deleteSprComplexUsluga: Ext.emptyFn,
	deleteSprComplexUslugaList: Ext.emptyFn,
	draggable: true,
	height: 400,
	id: 'SprComplexUslugaViewWindow',
	initComponent: function() {
		this.UslugaComplexGrid = new sw.Promed.ViewFrame({
			actions: [
				{ name: 'action_add', handler: function() { this.openUslugaComplexEditWindow('add'); }.createDelegate(this) },
				{ name: 'action_edit', handler: function() { this.openUslugaComplexEditWindow('edit'); }.createDelegate(this) },
				{ name: 'action_view', handler: function() { this.openUslugaComplexEditWindow('view'); }.createDelegate(this) },
				{ name: 'action_delete' },
				{ name: 'action_refresh' },
				{ name: 'action_print'}
			],
			autoLoadData: false,
			dataUrl: '/?c=EvnUsluga&m=loadUslugaComplexGrid',
			focusOn: {
				name: 'SPRCUVW_UslugaComplexListGrid',
				type: 'grid'
			},
			focusPrev: {
				name: 'SPRCUVW_CancelButton',
				type: 'button'
			},
			id: 'SPRCUVW_UslugaComplexGrid',
			object: 'UslugaComplex',
			onDblClick: function() {
				this.openUslugaComplexEditWindow('edit');
			}.createDelegate(this),
			onLoadData: function(result) {
				//
			},
			onRowSelect: function(sm, index, record) {
				var record = sm.getSelected();

				if ( record.get('UslugaComplex_id') ) {
					this.UslugaComplexListGrid.getAction('action_add').setDisabled(false);
					this.UslugaComplexListGrid.loadData({
						globalFilters: {
							limit: 100,
							start: 0,
							UslugaComplex_id: record.get('UslugaComplex_id')
						},
						noFocusOnLoad: true
					});
				}
				else {
					this.UslugaComplexListGrid.getAction('action_add').setDisabled(true);
					this.UslugaComplexListGrid.removeAll({
						addEmptyRecord: true
					});
				}
			}.createDelegate(this),
			paging: true,
			region: 'center',
			root: 'data',
			stringfields: [
				// Поля для отображение в гриде
				{ name: 'UslugaComplex_id', type: 'int', header: 'ID', key: true },
				{ name: 'UslugaComplex_Code', header: lang['kod'], width: 130 },
				{ name: 'UslugaComplex_Name', header: lang['naimenovanie'], id: 'autoexpand', autoExpandMin: 250 }
			],
			toolbar: true,
			totalProperty: 'totalCount'
		});

		this.UslugaComplexListGrid = new sw.Promed.ViewFrame({
			actions: [
				{ name: 'action_add', handler: function() { this.openUslugaComplexListEditWindow('add'); }.createDelegate(this) },
				{ name: 'action_edit', handler: function() { this.openUslugaComplexListEditWindow('edit'); }.createDelegate(this) },
				{ name: 'action_view', handler: function() { this.openUslugaComplexListEditWindow('view'); }.createDelegate(this) },
				{ name: 'action_delete' },
				{ name: 'action_refresh' },
				{ name: 'action_print'}
			],
			autoLoadData: false,
			dataUrl: '/?c=EvnUsluga&m=loadUslugaComplexListGrid',
			focusOn: {
				name: 'SPRCUVW_CancelButton',
				type: 'button'
			},
			focusPrev: {
				name: 'SPRCUVW_UslugaComplexGrid',
				type: 'grid'
			},
			height: 200,
			id: 'SPRCUVW_UslugaComplexListGrid',
			object: 'UslugaComplexList',
			onDblClick: function() {
				this.openUslugaComplexListEditWindow('edit');
			}.createDelegate(this),
			onLoadData: function(result) {
				//
			},
			onRowSelect: function(sm, index, record) {
				//
			}.createDelegate(this),
			paging: true,
			region: 'south',
			root: 'data',
			stringfields: [
				// Поля для отображение в гриде
				{ name: 'UslugaComplexList_id', type: 'int', header: 'ID', key: true },
				{ name: 'UslugaComplex_id', type: 'int', hidden: true },
				{ name: 'Usluga_id', type: 'int', hidden: true },
				{ name: 'UslugaClass_id', type: 'int', hidden: true },
				{ name: 'Usluga_Code', header: lang['kod'], type: 'string', width: 130 },
				{ name: 'Usluga_Name', header: lang['naimenovanie'], type: 'string', id: 'autoexpand', autoExpandMin: 250 },
				{ name: 'UslugaClass_Name', header: lang['klass_uslugi'], type: 'string', width: 250 }
			],
			// title: 'Набор услуг',
			toolbar: true,
			totalProperty: 'totalCount'
		});

		Ext.apply(this, {
			buttons: [{
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				id: 'SPRCUVW_CancelButton',
				// tabIndex: 4905,
				text: lang['zakryit']
			}],
			items: [
				this.UslugaComplexGrid,
				this.UslugaComplexListGrid
			]
		});
		sw.Promed.swUslugaComplexViewWindow.superclass.initComponent.apply(this, arguments);

		this.UslugaComplexGrid.addListenersFocusOnFields();
		this.UslugaComplexListGrid.addListenersFocusOnFields();
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('SprComplexUslugaViewWindow');

			switch ( e.getKey() ) {
				case Ext.EventObject.P:
					current_window.hide();
				break;
			}
		},
		key: [
			Ext.EventObject.P
		],
		stopEvent: true
	}],
	layout: 'border',
	listeners: {
		'hide': function(win) {
			win.UslugaComplexGrid.removeAll();
			win.UslugaComplexListGrid.removeAll();
		}
	},
	maximizable: true,
	minHeight: 400,
	minWidth: 700,
	modal: false,
	openUslugaComplexEditWindow: function(action) {
		if ( action != 'add' && action != 'edit' && action != 'view' ) {
			return false;
		}

		if ( getWnd('swUslugaComplexEditWindow').isVisible() ) {
			sw.swMsg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_kompleksnoy_uslugi_uje_otkryito']);
			return false;
		}

		var grid = this.UslugaComplexGrid.getGrid();
		var params = new Object();

		if ( action == 'add' ) {
			params.UslugaComplex_id = 0;
		}
		else {
			var selected_record = this.UslugaComplexGrid.getGrid().getSelectionModel().getSelected();

			if ( !selected_record || !selected_record.get('UslugaComplex_id') ) {
				return false;
			}

			params = selected_record.data;
		}

		getWnd('swUslugaComplexEditWindow').show({
			action: action,
			callback: function(data) {
				if ( !data || !data.UslugaComplexData ) {
					return false;
				}

				var record = grid.getStore().getById(data.UslugaComplexData[0].UslugaComplex_id);

				if ( !record ) {
					if ( grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('UslugaComplex_id') ) {
						grid.getStore().removeAll();
					}

					grid.getStore().loadData({ 'data': data.UslugaComplexData }, true);
				}
				else {
					var usluga_complex_fields = new Array();
					var i = 0;

					grid.getStore().fields.eachKey(function(key, item) {
						usluga_complex_fields.push(key);
					});

					for ( i = 0; i < usluga_complex_fields.length; i++ ) {
						record.set(usluga_complex_fields[i], data.UslugaComplexData[0][usluga_complex_fields[i]]);
					}

					record.commit();
				}
			}.createDelegate(this),
			formParams: params,
			onHide: function() {
				if ( selected_record ) {
					this.UslugaComplexGrid.getGrid().getView().focusRow(this.UslugaComplexGrid.getGrid().getStore().indexOf(selected_record));
				}
				else {
					this.UslugaComplexGrid.focus();
				}
			}.createDelegate(this)
		});
	},
	openUslugaComplexListEditWindow: function(action) {
		if ( action != 'add' && action != 'edit' && action != 'view' ) {
			return false;
		}

		if ( getWnd('swUslugaComplexListEditWindow').isVisible() ) {
			sw.swMsg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_uslugi_uje_otkryito']);
			return false;
		}

		if ( !this.UslugaComplexGrid.getGrid().getSelectionModel().getSelected() || !this.UslugaComplexGrid.getGrid().getSelectionModel().getSelected().get('UslugaComplex_id') ) {
			sw.swMsg.alert(lang['soobschenie'], lang['ne_vyibrana_kompleksnaya_usluga']);
			return false;
		}

		var grid = this.UslugaComplexListGrid.getGrid();
		var params = new Object();
		var usluga_complex_id = this.UslugaComplexGrid.getGrid().getSelectionModel().getSelected().get('UslugaComplex_id');

		if ( action == 'add' ) {
			params.UslugaComplexList_id = 0;
			params.UslugaComplex_id = usluga_complex_id;
		}
		else {
			var selected_record = grid.getSelectionModel().getSelected();

			if ( !selected_record || !selected_record.get('UslugaComplexList_id') ) {
				return false;
			}

			params = selected_record.data;
		}

		getWnd('swUslugaComplexListEditWindow').show({
			action: action,
			callback: function(data) {
				if ( !data || !data.UslugaComplexListData ) {
					return false;
				}

				var record = grid.getStore().getById(data.UslugaComplexListData[0].UslugaComplexList_id);

				if ( !record ) {
					if ( grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('UslugaComplexList_id') ) {
						grid.getStore().removeAll();
					}

					grid.getStore().loadData({ 'data': data.UslugaComplexListData }, true);
				}
				else {
					var usluga_complex_list_fields = new Array();
					var i = 0;

					grid.getStore().fields.eachKey(function(key, item) {
						usluga_complex_list_fields.push(key);
					});

					for ( i = 0; i < usluga_complex_list_fields.length; i++ ) {
						record.set(usluga_complex_list_fields[i], data.UslugaComplexListData[0][usluga_complex_list_fields[i]]);
					}

					record.commit();
				}
			}.createDelegate(this),
			formParams: params,
			onHide: function() {
				if ( selected_record ) {
					grid.getView().focusRow(grid.getStore().indexOf(selected_record));
				}
				else {
					this.UslugaComplexListGrid.focus();
				}
			}.createDelegate(this)
		});
	},
	plain: true,
	resizable: true,
	show: function() {
		sw.Promed.swUslugaComplexViewWindow.superclass.show.apply(this, arguments);

		this.restore();
		this.maximize();

		// this.UslugaComplexGrid.getAction('action_refresh').setDisabled(true);
		this.UslugaComplexGrid.removeAll();
		this.UslugaComplexGrid.loadData();

		this.UslugaComplexListGrid.getAction('action_refresh').setDisabled(true);
		this.UslugaComplexListGrid.removeAll();
	},
	title: lang['kompleksnyie_uslugi_spravochnik'],
	width: 700
});