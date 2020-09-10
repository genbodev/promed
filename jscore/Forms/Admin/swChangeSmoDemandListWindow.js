/**
* swChangeSmoDemandListWindow - окно просмотра списка заявок на смену прикрепления к СМО.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Salackhov Rustam
* @version      0.001-18.03.2010
*/

sw.Promed.swChangeSmoDemandListWindow = Ext.extend(sw.Promed.BaseForm, {
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	collapsible: true,
	deleteRecord: Ext.emptyFn,
	draggable: true,
	height: 400,
	id: 'ChangeSmoDemandListWindow',
	initComponent: function() {
		this.ChangeSmoDemandListGrid = new sw.Promed.ViewFrame({
			actions: [
				{ name: 'action_add', handler: function() { this.openChangeSmoDemandListEditWindow('add'); }.createDelegate(this) },
				{ name: 'action_edit', handler: function() { this.openChangeSmoDemandListEditWindow('edit'); }.createDelegate(this) },
				{ name: 'action_view', handler: function() {  }.createDelegate(this) },
				{ name: 'action_delete', handler: function() { this.deleteAttacmentDemand(); }.createDelegate(this) },
				{ name: 'action_refresh' },
				{ name: 'action_print'}
			],
			autoLoadData: false,
			dataUrl: '/?c=Demand&m=loadChangeSmoDemandListGrid',
			focusOn: {
				name: 'ADLVW_CancelButton',
				type: 'button'
			},
			focusPrev: {
				name: 'ADLVW_CancelButton',
				type: 'button'
			},
			id: 'ADLVW_ChangeSmoDemandListGrid',
			object: 'ChangeSmoDemandList',
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
				/*{ name: 'ChangeSmoDemandList_id', type: 'int', header: 'ID', key: true },
				{ name: 'Usluga_id', type: 'int', hidden: true },
				{ name: 'Usluga_Code', type: 'string', header: lang['kod_uslugi'], width: 130 },
				{ name: 'Usluga_Name', type: 'string', header: lang['usluga'], id: 'autoexpand', autoExpandMin: 250 },
				{ name: 'ChangeSmoDemandList_UET', type: 'money', header: lang['uet'], width: 100 },*/
				{ name: 'ChangeSmoDemand_id', type: 'int', header: 'ID', key: true },
				{ name: 'Person_Surname', type: 'string', header: lang['familiya'], width: 130 },
				{ name: 'Person_Firname', type: 'string', header: lang['imya'], width: 130 },
				{ name: 'Person_Secname', type: 'string', header: lang['otchestvo'], width: 130 },				
				{ name: 'DemandState_Name', type: 'string', header: lang['sostoyanie_zayavki'], width: 190 },
				{ name: 'Insert_Date', type: 'string', header: lang['data'], width: 130 },
				{ name: 'Org_Name', type: 'string', header: lang['organizatsiya'], id: 'autoexpand', autoExpandMin: 250 }
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
				id: 'ADLVW_CancelButton',
				text: '<u>З</u>акрыть'
			}],
			items: [ this.ChangeSmoDemandListGrid ]
		});
		sw.Promed.swChangeSmoDemandListWindow.superclass.initComponent.apply(this, arguments);

		this.ChangeSmoDemandListGrid.addListenersFocusOnFields();
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('ChangeSmoDemandListViewWindow');

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
			win.ChangeSmoDemandListGrid.removeAll();
		}
	},
	maximizable: true,
	minHeight: 400,
	minWidth: 700,
	modal: false,
	openChangeSmoDemandListEditWindow: function(action) {
		if ( action != 'add' && action != 'edit' && action != 'view' ) {
			return false;
		}

		var grid = this.findById('ADLVW_ChangeSmoDemandListGrid').getGrid();

		if ( this.action == 'view') {
			if ( action == 'add') {
				return false;
			} else if ( action == 'edit' ) {
				action = 'view';
			}
		}

		/*if ( getWnd('swChangeSmoDemandListEditWindow').isVisible() ) {
			sw.swMsg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_zapisi_spravochnika_uet_uje_otkryito']);
			return false;
		}*/

		var params = new Object();
		if ( action == 'add' ) {
			params.onHide = function() {
				// TODO: getWnd
				getWnd('swPersonSearchWindow').findById('person_search_form').getForm().findField('PersonSurName_SurName').focus(true, 500);
			};

			getWnd('swPersonSearchWindow').show({
				onClose: function() {
					if ( grid.getSelectionModel().getSelected() ) {
						grid.getView().focusRow(grid.getStore().indexOf(grid.getSelectionModel().getSelected()));
					}
					else {
						grid.getView().focusRow(0);
						grid.getSelectionModel().selectFirstRow();
					}
				}.createDelegate(this),
				onSelect: function(person_data) {					
					params.Person_id 	= person_data.Person_id;
					params.PersonEvn_id = person_data.PersonEvn_id;
					params.Server_id 	= person_data.Server_id;

					getWnd('swChangeSmoDemandEditWindow').show({
						action: action,
						formParams: params
					});
				},
				searchMode: 'all'
			});
		} else {		
			if ( !grid || !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('ChangeSmoDemand_id') ) {
				return false;
			}
			var record = grid.getSelectionModel().getSelected();
			
			params = record.data;
			
			getWnd('swChangeSmoDemandEditWindow').show({
				action: action,
				formParams: params
			});
		}

		/*getWnd('swChangeSmoDemandListEditWindow').show({
			action: action,
			callback: function(data) {
				if ( !data || !data.ChangeSmoDemandListData ) {
					return false
				}

				var record = view_frame.getGrid().getStore().getById(data.ChangeSmoDemandListData.ChangeSmoDemandList_id);

				if ( !record ) {
					if ( view_frame.getGrid().getStore().getCount() == 1 && !view_frame.getGrid().getStore().getAt(0).get('ChangeSmoDemandList_id') ) {
						view_frame.removeAll({ addEmptyRecord: false });
					}

					view_frame.getGrid().getStore().loadData({ 'data': [ data.ChangeSmoDemandListData ]}, true);
				}
				else {
					var usluga_price_list_fields = new Array();
					var i = 0;

					view_frame.getGrid().getStore().fields.eachKey(function(key, item) {
						usluga_price_list_fields.push(key);
					});

					for ( i = 0; i < usluga_price_list_fields.length; i++ ) {
						record.set(usluga_price_list_fields[i], data.ChangeSmoDemandListData[usluga_price_list_fields[i]]);
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
		});*/
	},
	deleteAttacmentDemand: function() {
		var grid = this.findById('ADLVW_ChangeSmoDemandListGrid').getGrid();

		if ( !grid || !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('ChangeSmoDemand_id') ) {
			return false;
		}

		var record = grid.getSelectionModel().getSelected();
		var demand_id = record.get('ChangeSmoDemand_id');

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( buttonId == 'yes' ) {
					Ext.Ajax.request({
						callback: function(options, success, response) {
							if ( success ) {
								grid.getStore().remove(record);
								if ( grid.getStore().getCount() == 0 ) {
									LoadEmptyRow(grid);
								}
								grid.getView().focusRow(0);
								grid.getSelectionModel().selectFirstRow();
							} else {
								sw.swMsg.alert(lang['oshibka'], lang['pri_udalenii_zayavki_voznikli_oshibki']);
							}
						},
						params: {
							ChangeSmoDemand_id: demand_id
						},
						url: '/?c=Demand&m=deleteChangeSmoDemand'
					});
				}
			},
			icon: Ext.MessageBox.QUESTION,
			msg: lang['udalit_zayavku'],
			title: lang['vopros']
		});
	},
	plain: true,
	resizable: true,
	show: function() {
		sw.Promed.swChangeSmoDemandListWindow.superclass.show.apply(this, arguments);

		this.restore();
		this.maximize();

		this.ChangeSmoDemandListGrid.removeAll();
		this.ChangeSmoDemandListGrid.loadData({
			globalFilters: {
				limit: 100,
				start: 0
			}
		});
	},
	title: lang['zayavki_na_prikreplenie_smo'],
	width: 700
});