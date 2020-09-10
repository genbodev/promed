/**
* swMedPersonalSearchWindow - окно поиска медперсонала.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009-2010 Swan Ltd.
* @author       Pshenicyn Ivan aka IVP (ipshon@rambler.ru)
* @version      05.03.2010
* @comment      Префикс для id компонентов MPSW (MedPersonalSearchWindow)
*/

sw.Promed.swMedPersonalSearchWindow = Ext.extend(sw.Promed.BaseForm, {
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	collapsible: true,
	deleteMedPersonal: function() {	
	},
	doSearch: function() {
		var form = this.findById('MPSW_MedPersonalSearchForm');
		var grid = this.findById('MPSW_MedPersonalSearchGrid').ViewGridPanel;
		var params = form.getForm().getValues();
		var arr = form.find('disabled', true);
		for (i = 0; i < arr.length; i++)
		{
			params[arr[i].hiddenName] = arr[i].getValue();
		}
		params.start = 0;
		params.limit = 100;

		//var arr = form.find('disabled', true);

		/*for ( i = 0; i < arr.length; i++ ) {
			params[arr[i].hiddenName] = arr[i].getValue();
		}*/

		grid.getStore().removeAll();
		grid.getStore().baseParams = params;
		grid.getStore().load({
			params: params,
			callback: function(r) {
				if ( r.length > 0 ) {
					grid.getView().focusRow(0);
					grid.getSelectionModel().selectFirstRow();
				}
			}
		});
	},
	draggable: true,
	height: 550,
	id: 'MedPersonalSearchWindow',
	initComponent: function() {
		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.ownerCt.doSearch();
				},
				iconCls: 'search16',
				id: 'MPSW_SearchButton',
				tabIndex: TABINDEX_MPSW + 1,
				text: BTN_FRMSEARCH
			}, {
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'close16',
				id: 'MPSW_CancelButton',
				onTabAction: function () {
					//this.findById('ERPSIF_EvnRecept_Ser').focus(true, 100);
				}.createDelegate(this),
				tabIndex: TABINDEX_MPSW + 2,
				text: lang['zakryit']
			}],
			items: [ new Ext.form.FormPanel({
				bodyStyle: 'padding: 5px',
				border: false,
				frame: false,
				height: (Ext.isIE) ? 90 : 80,
				id: 'MPSW_MedPersonalSearchForm',
				items: [{
					xtype: 'textfieldpmw',
					fieldLabel: lang['familiya'],
					toUpperCase: true,
					width: 180,
					id: 'MPSW_Person_SurName',
					name: 'Person_SurName',
					tabIndex: TABINDEX_MPSW + 28,
					listeners: {
						'keydown': function (inp, e) {
							if (e.shiftKey == false && e.getKey() == Ext.EventObject.TAB)
							{
								e.stopEvent();
								Ext.getCmp('MPSW_Person_FirName').focus();
							}
						}
					}
				},
				{
					xtype: 'textfieldpmw',
					fieldLabel: lang['imya'],
					toUpperCase: true,
					width: 180,
					name: 'Person_FirName',
					id: 'MPSW_Person_FirName',
					tabIndex: TABINDEX_MPSW + 1,
					listeners: {
						'keydown': function (inp, e) {
							if (e.shiftKey == true && e.getKey() == Ext.EventObject.TAB)
							{
								e.stopEvent();
								Ext.getCmp('MPSW_Person_SurName').focus();
							}
						}
					}
				},
				{
					xtype: 'textfieldpmw',
					fieldLabel: lang['otchestvo'],
					toUpperCase: true,
					width: 180,
					name: 'Person_SecName',
					tabIndex: TABINDEX_MPSW + 2
				}],
				labelAlign: 'right',
				labelWidth: 120,
				region: 'north'
			}),
			new sw.Promed.ViewFrame({
				actions: [
					{ name: 'action_add', disabled: true, handler: function() { this.openMedPersonalEditWindow('add'); }.createDelegate(this) },
					{ name: 'action_edit', disabled:true, handler: function() { this.openMedPersonalEditWindow('edit'); }.createDelegate(this) },
					{ name: 'action_view', handler: function() { this.openMedPersonalEditWindow('view'); }.createDelegate(this) },
					{ name: 'action_delete', disabled: true, url: '/?c=MedPersonal&m=deleteMedPersonalCache' }, /*, disabled: true, handler: function() { this.deleteMedPersonal(); }.createDelegate(this)*/
					{ name: 'action_refresh'},
					{ name: 'action_print'/*, disabled: true, handler: function() { this.printMes(); }.createDelegate(this)*/ }
				],
				autoExpandColumn: 'autoexpand',
				autoExpandMin: 150,
				autoLoadData: false,
				//dataUrl: '/?c=MedPersonal&m=loadMedPersonalSearchList',
				dataUrl: '/?c=MedPersonal&m=loadMedPersonalSearchList_Ufa_Old_ERMP',
				focusOn: {
					name: 'MPSW_CancelButton',
					type: 'button'
				},
				focusPrev: {
					name: 'MPSW_CancelButton',
					type: 'button'
				},
				id: 'MPSW_MedPersonalSearchGrid',
				onRowSelect: function( grd, ind ) {					
				},
				object: 'MedPersonalCache',
				pageSize: 100,
				paging: true,
				region: 'center',
				root: 'data',
				stringfields: [
					{ name: 'MedPersonal_id', type: 'int', header: 'ID', key: true },
					{ name: 'MedPersonal_Code',  type: 'string', header: lang['kod_vracha'] },
					{ name: 'MedPersonal_TabCode',  type: 'string', header: lang['tabelnyiy_kod_vracha'] },
					{ name: 'Person_SurName', id: 'autoexpand', type: 'string', header: lang['familiya'] },
					{ name: 'Person_FirName',  type: 'string', header: lang['imya'] },
					{ name: 'Person_SecName',  type: 'string', header: lang['otchestvo'] },
					{ name: 'Person_Snils',  type: 'string', header: lang['snils'] },
					{ name: 'Person_BirthDay',  type: 'date', header: lang['data_rojdeniya'], renderer: Ext.util.Format.dateRenderer('d.m.Y')},
					{ name: 'WorkData_begDate',  type: 'date', header: lang['nachalo_rabotyi'], renderer: Ext.util.Format.dateRenderer('d.m.Y')},
					{ name: 'WorkData_endDate',  type: 'date', header: lang['okonchanie_rabotyi'], renderer: Ext.util.Format.dateRenderer('d.m.Y')},
					{ name: 'WorkData_IsDlo', header: lang['vrach_llo'], width: 150, type: 'checkbox'}					
				],
				toolbar: true,
				totalProperty: 'totalCount'
			})]
		});

		sw.Promed.swMedPersonalSearchWindow.superclass.initComponent.apply(this, arguments);
		this.findById('MPSW_MedPersonalSearchGrid').getGrid().view = new Ext.grid.GridView(
		{
			getRowClass : function (row, index)
			{
				var cls = '';
				//if (row.get('set')>0)
					//cls = cls+'x-grid-rowselect ';
				if (row.get('MesStatus')==4)
					cls = cls+'x-grid-rowblue ';
				if (row.get('MesStatus')==3)
					cls = cls+'x-grid-rowgray ';
			
				if (cls.length == 0)
					cls = 'x-grid-panel'; 
				return cls;
			}
		});
		this.findById('MPSW_MedPersonalSearchGrid').addListenersFocusOnFields();
	},
	keys: [{
		fn: function(inp, e) {
			Ext.getCmp('MedPersonalSearchWindow').openMedPersonalEditWindow('add');
		},
		key: [
			Ext.EventObject.INSERT
		],
		stopEvent: true
	}, {
		alt: true,
		fn: function(inp, e) {
			Ext.getCmp('MedPersonalSearchWindow').doSearch();
		},
		key: [
			Ext.EventObject.ENTER,
			Ext.EventObject.G
		],
		stopEvent: true
	}, {
		fn: function(inp, e) {
			Ext.getCmp('MedPersonalSearchWindow').doSearch();
		},
		key: [
			Ext.EventObject.ENTER
		],
		stopEvent: true
	}, {
		alt: true,
		fn: function(inp, e) {
			Ext.getCmp('MedPersonalSearchWindow').hide();
		},
		key: [
			Ext.EventObject.P
		],
		stopEvent: true
	}, {
		alt: true,
		fn: function(inp, e) {
			if (e.altKey) {
				var grid = Ext.getCmp('MPSW_MedPersonalSearchGrid').getGrid();
				var selected_record = grid.getSelectionModel().getSelected();
				AddRecordToUnion(
					selected_record,
					'MedPersonal',
					lang['vrach'],
					function () {
						grid.getStore().reload();
					}
				)
			}
		},
		key: [
			Ext.EventObject.F6
		],
		stopEvent: true
	}],
	layout: 'border',
	maximizable: true,
	minHeight: 550,
	minWidth: 800,
	modal: true,
	openMedPersonalEditWindow: function(action) {
	
		var current_window = this;
		if ( action != 'add' && action != 'edit' && action != 'view' ) {
			return false;
		}

		if ( action == 'add' && getWnd('swMedPersonalEditWindow').isVisible() ) {
			sw.swMsg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_medpersonala_uje_otkryito']);
			return false;
		}

		var grid = this.findById('MPSW_MedPersonalSearchGrid').getGrid();

		if ( !grid ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_nayti_spisok_medpersonala']);
			return false;
		}
		
		var params = new Object();
		
		if ( action != 'add' )
		{
			var current_row = grid.getSelectionModel().getSelected();

			if ( !current_row ) {
				return;
			}
			
			$med_personal_id = current_row.get('MedPersonal_id');
			if ( $med_personal_id > 0 )
				params.MedPersonal_id = $med_personal_id;
			else
			{
				return false;
			}
		}

		params.action = action;
		params.callback = function(data) {
			if ( !data ) {
				return false;
			}

			var record = grid.getStore().getById(data.MedPersonal_id);

			if ( !record ) {
				if ( grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('MedPersonal_id') ) {
					grid.getStore().removeAll();
				}

				//grid.getStore().loadData({ 'data': [ data ]}, true);
				grid.getStore().reload();
			}
			else {
				var med_personal_fields = new Array();

				grid.getStore().fields.eachKey(function(key, item) {
					med_personal_fields.push(key);
				});

				for ( i = 0; i < med_personal_fields.length; i++ ) {
					record.set(med_personal_fields[i], data[med_personal_fields[i]]);
				}

				record.commit();
				
				var selected_record = grid.getSelectionModel().getSelected();
				if ( selected_record )
				{
					grid.getView().focusRow(grid.getStore().indexOf(selected_record));
				}
				else
				{
					grid.getSelectionModel().selectFirstRow();
					grid.getView().focusRow(0);
				}
				
				/*if ( grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('Mes_id') ) {
					grid.getStore().removeAll();
				}*/

				//grid.getStore().loadData({ 'data': [ data ]}, true);
				//grid.getStore().reload();
			}
		}.createDelegate(this);
		
		params.onHide = function() {
			if ( grid.getStore().getCount() > 0 )
			{
				var selected_record = grid.getSelectionModel().getSelected();
				if ( selected_record )
				{
					grid.getView().focusRow(grid.getStore().indexOf(selected_record));
				}
				else
				{
					grid.getSelectionModel().selectFirstRow();
					grid.getView().focusRow(0);
				}
			}
		}

		getWnd('swMedPersonalEditWindow').show(params);
	},
	plain: true,
	pmUser_Name: null,
	printMes: function() {

	},
	refreshMesSearchGrid: function() {
		var grid = this.findById('MPSW_MedPersonalSearchGrid').getGrid();

		grid.getSelectionModel().clearSelections();
		grid.getStore().reload();

		if ( grid.getStore().getCount() > 0 ) {
			grid.getView().focusRow(0);
			grid.getSelectionModel().selectFirstRow();
		}
	},
	resizable: false,
	show: function() {
		sw.Promed.swMedPersonalSearchWindow.superclass.show.apply(this, arguments);

		this.restore();
		this.center();
		this.maximize();

		var form = this.findById('MPSW_MedPersonalSearchForm');
		form.getForm().reset();
		
		this.findById('MPSW_MedPersonalSearchGrid').getGrid().getStore().removeAll();
		this.findById('MPSW_MedPersonalSearchGrid').addEmptyRecord(this.findById('MPSW_MedPersonalSearchGrid').getGrid().getStore());
		form.getForm().findField('Person_SurName').focus(true, 200);		
		//this.doSearch();
	},
	title: lang['meditsinskiy_personal_prosmotr_staryiy_ermp'],//WND_ADMIN_MPSEARCH,
	width: 800
});