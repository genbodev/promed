/**
* swUslugaPriceListViewWindow - Стоматологические услуги ЛПУ (Справочник УЕТ).
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      DLO
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage1981@gmail.com)
* @version      0.001-19.01.2010
* @comment      Префикс для id компонентов UPLVW (UslugaPriceListViewWindow)
*/

sw.Promed.swUslugaPriceListViewWindow = Ext.extend(sw.Promed.BaseForm, {
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	collapsible: true,
	deleteRecord: Ext.emptyFn,
	draggable: true,
	height: 400,
	id: 'UslugaPriceListViewWindow',
	initComponent: function() {
		this.UslugaPriceListGrid = new sw.Promed.ViewFrame({
			actions: [
				{ name: 'action_add', handler: function() { this.openUslugaPriceListEditWindow('add'); }.createDelegate(this) },
				{ name: 'action_edit', handler: function() { this.openUslugaPriceListEditWindow('edit'); }.createDelegate(this) },
				{ name: 'action_view', handler: function() { this.openUslugaPriceListEditWindow('view'); }.createDelegate(this) },
				{ name: 'action_delete' },
				{ name: 'action_refresh' },
				{ name: 'action_print'}
			],
			autoLoadData: false,
			dataUrl: '/?c=Usluga&m=loadUslugaPriceListGrid',
			focusOn: {
				name: 'UPLVW_CancelButton',
				type: 'button'
			},
			focusPrev: {
				name: 'UPLVW_CancelButton',
				type: 'button'
			},
			id: 'UPLVW_UslugaPriceListGrid',
			object: 'UslugaPriceList',
			onLoadData: function(result) {
				//
			},
			onRowSelect: function(sm, index, record) {
				//
			},
			paging: true,
			region: 'center',
			root: 'data',
			stringfields: [
				// Поля для отображение в гриде
				{ name: 'UslugaPriceList_id', type: 'int', header: 'ID', key: true },
				{ name: 'Usluga_id', type: 'int', hidden: true },
				{ name: 'Usluga_Code', type: 'string', header: lang['kod_uslugi'], width: 130 },
				{ name: 'Usluga_Name', type: 'string', header: lang['usluga'], id: 'autoexpand', autoExpandMin: 250 },
				{ name: 'UslugaPriceList_UET', type: 'money', header: lang['uet'], width: 100 }
			],
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
				id: 'UPLVW_CancelButton',
				text: lang['zakryit']
			}],
			items: [ this.UslugaPriceListGrid ]
		});
		sw.Promed.swUslugaPriceListViewWindow.superclass.initComponent.apply(this, arguments);

		this.UslugaPriceListGrid.addListenersFocusOnFields();
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('UslugaPriceListViewWindow');

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
			win.UslugaPriceListGrid.removeAll();
		}
	},
	maximizable: true,
	minHeight: 400,
	minWidth: 700,
	modal: false,
	openUslugaPriceListEditWindow: function(action) {
		if ( action != 'add' && action != 'edit' && action != 'view' ) {
			return false;
		}

		var view_frame = this.UslugaPriceListGrid;

		if ( this.action == 'view') {
			if ( action == 'add') {
				return false;
			}
			else if ( action == 'edit' ) {
				action = 'view';
			}
		}

		if ( getWnd('swUslugaPriceListEditWindow').isVisible() ) {
			sw.swMsg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_zapisi_spravochnika_uet_uje_otkryito']);
			return false;
		}

		var params = new Object();

		if ( action == 'add' ) {
			params.UslugaPriceList_id = 0;
		}
		else {
			var selected_record = view_frame.getGrid().getSelectionModel().getSelected();

			if ( !selected_record || !selected_record.get('UslugaPriceList_id') ) {
				return false;
			}

			params = selected_record.data;
		}

		getWnd('swUslugaPriceListEditWindow').show({
			action: action,
			callback: function(data) {
				if ( !data || !data.uslugaPriceListData ) {
					return false;
				}

				var record = view_frame.getGrid().getStore().getById(data.uslugaPriceListData.UslugaPriceList_id);

				if ( !record ) {
					if ( view_frame.getGrid().getStore().getCount() == 1 && !view_frame.getGrid().getStore().getAt(0).get('UslugaPriceList_id') ) {
						view_frame.removeAll({ addEmptyRecord: false });
					}

					view_frame.getGrid().getStore().loadData({ 'data': [ data.uslugaPriceListData ]}, true);
				}
				else {
					var usluga_price_list_fields = new Array();
					var i = 0;

					view_frame.getGrid().getStore().fields.eachKey(function(key, item) {
						usluga_price_list_fields.push(key);
					});

					for ( i = 0; i < usluga_price_list_fields.length; i++ ) {
						record.set(usluga_price_list_fields[i], data.uslugaPriceListData[usluga_price_list_fields[i]]);
					}

					record.commit();
				}
			}.createDelegate(this),
			formParams: params,
			onHide: function() {
				if ( selected_record ) {
					view_frame.getGrid().getView().focusRow(view_frame.getGrid().getStore().indexOf(selected_record));
					view_frame.getGrid().getSelectionModel().selectRow(view_frame.getGrid().getStore().indexOf(selected_record));
				}
				else {
					view_frame.focus();
				}
			}.createDelegate(this)
		});
	},
	plain: true,
	resizable: true,
	show: function() {
		sw.Promed.swUslugaPriceListViewWindow.superclass.show.apply(this, arguments);

		this.restore();
		this.maximize();

		this.UslugaPriceListGrid.removeAll();
		this.UslugaPriceListGrid.loadData({
			globalFilters: {
				limit: 100,
				start: 0
			}
		});
	},
	title: lang['stomatologicheskie_uslugi_mo_spravochnik_uet'],
	width: 700
});