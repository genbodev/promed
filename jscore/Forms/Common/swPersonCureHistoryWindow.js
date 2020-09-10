/**
* swPersonCureHistoryWindow - окно просмотра истории лечения.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      DLO
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage1981@gmail.com)
* @version      0.002-18.02.2010
*/

sw.Promed.swPersonCureHistoryWindow = Ext.extend(sw.Promed.BaseForm, {
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	height: 500,
	id: 'PersonCureHistoryWindow',
	initComponent: function() {
		var win = this;

		this.PersonCureHistoryGrid = new sw.Promed.ViewFrame({
			useArchive: 1,
			actions: [
				{ name: 'action_add', disabled: true },
				{ name: 'action_edit', handler: function() { this.openEvnEditWindow('edit'); }.createDelegate(this) },
				{ name: 'action_view', handler: function() { this.openEvnEditWindow('view'); }.createDelegate(this) },
				{ name: 'action_delete', disabled: true },
				{ name: 'action_refresh' },
				{ name: 'action_print'}
			],
			auditOptions: {
				maskRe: new RegExp('^([a-z]+)_(\\d+)$', 'i'),
				maskParams: ['key_field', 'key_id'],
				needIdSuffix: true
			},
			autoLoadData: false,
			filterByFieldEnabled: true,
			border: false,
			dataUrl: '/?c=Common&m=loadPersonCureHistoryList',
			focusOn: {
				name: 'PCureHF_PrintButton',
				type: 'button'
			},
			focusPrev: {
				name: 'PCureHF_CloseButton',
				type: 'button'
			},
			id: 'PCureHF_PersonCureHistoryGrid',
			onLoadData: function(result) {
				//
			},
			onRowSelect: function(sm, index, record) {
				// Запретить редактирование/удаление архивных записей
				if (getGlobalOptions().archive_database_enable) {
					this.getAction('action_edit').setDisabled(win.PersonCureHistoryGrid.readOnly || record.get('archiveRecord') == 1);
					this.getAction('action_delete').setDisabled(win.PersonCureHistoryGrid.readOnly || record.get('archiveRecord') == 1);
				}
			},
			paging: false,
			region: 'center',
			stringfields: [
				// Поля для отображение в гриде
				{ name: 'Evn_id', type: 'string', header: 'ID', key: true },
				{ name: 'Evn_pid', type: 'int', hidden: true },
				{ name: 'Person_id', type: 'int', hidden: true },
				{ name: 'PersonEvn_id', type: 'int', hidden: true },
				{ name: 'Server_id', type: 'int', hidden: true },
				{ name: 'EvnClass_SysNick', type: 'string', hidden: true },
				{ name: 'EvnClass_Name', type: 'string', header: lang['sobyitie'], id: 'autoexpand', autoExpandMin: 150 },
				{ name: 'LpuSection_Name', type: 'string', header: lang['otdelenie'], width: 150 },
				{ name: 'MedPersonal_Fio', type: 'string', header: lang['vrach'], width: 150 },
				{ name: 'Diag_Name', type: 'string', header: lang['diagnoz'], width: 150 },
				{ name: 'Evn_setDate', type: 'date', header: lang['nachalo'], width: 100 },
				{ name: 'Evn_disDate', type: 'date', header: lang['okonchanie'], width: 100 }
			],
			toolbar: true
		});
		
		
		this.PersonDispDopGrid = new sw.Promed.ViewFrame({
			useArchive: 1,
			actions: [
				{ name: 'action_add', disabled: true },
				{ name: 'action_edit', handler: function() { this.openEvnPLDDEditWindow('edit'); }.createDelegate(this) },
				{ name: 'action_view', handler: function() { this.openEvnPLDDEditWindow('view'); }.createDelegate(this) },
				{ name: 'action_delete', disabled: true },
				{ name: 'action_refresh' },
				{ name: 'action_print'}
			],
			height:150,
			autoLoadData: false,
			border: false,
			dataUrl: '/?c=EvnPLDispDop&m=loadEvnPLDispDopForPerson',
			focusOn: {
				name: 'PCureHF_PrintButton',
				type: 'button'
			},
			focusPrev: {
				name: 'PCureHF_CloseButton',
				type: 'button'
			},
			id: 'PCureHF_PersonDispDopGrid',
			onLoadData: function(result) {
				//
			},
			onRowSelect: function(sm, index, record) {
				// Запретить редактирование/удаление архивных записей
				if (getGlobalOptions().archive_database_enable) {
					this.getAction('action_edit').setDisabled(win.PersonDispDopGrid.readOnly || record.get('archiveRecord') == 1);
					this.getAction('action_delete').setDisabled(win.PersonDispDopGrid.readOnly || record.get('archiveRecord') == 1);
				}
			},
			paging: false,
			region: 'south',
			stringfields: [
				// Поля для отображение в гриде
				{ name: 'EvnPLDispDop_id', type: 'int', header: 'ID', key: true },
				{ name: 'Person_id', type: 'int', hidden: true },
				{ name: 'Server_id', type: 'int', hidden: true },
				{ name: 'EvnPLDispDop_setDate', type: 'date', format: 'd.m.Y', header: lang['data_nachala'], id: 'autoexpand', autoExpandMin: 150 },
				{ name: 'EvnPLDispDop_disDate', type: 'date', format: 'd.m.Y', header: lang['data_okonchaniya'], width:150 },
				{ name: 'EvnPLDispDop_VizitCount', type: 'int', header: lang['posescheniy'], width:150 },
				{ name: 'EvnPLDispDop_IsFinish', type: 'string', header: lang['zakonch'], width:100 }
			],
			toolbar: true,
			title: lang['talonyi_po_dopolnitelnoy_dispanserizatsii']
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.printEvent();
				}.createDelegate(this),
				iconCls: 'print16',
				id: 'PCureHF_PrintButton',
				onTabAction: function() {
					this.buttons[this.buttons.length - 1].focus();
				}.createDelegate(this),
				text: BTN_GRIDPRINT
			}, {
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() {
					this.callback();
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				id: 'PCureHF_CloseButton',
				onShiftTabAction: function() {
					this.buttons[0].focus();
				}.createDelegate(this),
				text: BTN_FRMCLOSE
			}],
			items: [ new sw.Promed.PersonInformationPanelShort({
				id: 'PCureHF_PersonInformationFrame',
				region: 'north'
			}),
			this.PersonCureHistoryGrid,
			this.PersonDispDopGrid
			]
		});
		sw.Promed.swPersonCureHistoryWindow.superclass.initComponent.apply(this, arguments);

		this.PersonCureHistoryGrid.addListenersFocusOnFields();
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			Ext.getCmp('PersonCureHistoryWindow').hide();
		},
		key: [ Ext.EventObject.P ],
		stopEvent: true
	}],
	layout: 'border',
	listeners: {
		'hide': function() {
			this.onHide();
		}
	},
	maximizable: true,
	minHeight: 400,
	minWidth: 700,
	modal: true,
	openEvnEditWindow: function(action) {
		if ( action != 'edit' && action != 'view' ) {
			return false;
		}

		var grid = this.findById('PCureHF_PersonCureHistoryGrid').getGrid();
		var view_frame = this.findById('PCureHF_PersonCureHistoryGrid');

		if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('Evn_id') ) {
			return false;
		}

		var params = new Object();
		var record = grid.getSelectionModel().getSelected();
		var window_name = '';

		var index = grid.getStore().indexOf(record);

		if ( index == -1 ) {
			return false;
		}

		params.action = action;
		params.onHide = function() {
			view_frame.getGrid().getView().focusRow(index);
		};
		params.Person_id = record.get('Person_id');
		params.PersonEvn_id = record.get('PersonEvn_id');
		params.Server_id = record.get('Server_id');

		if (getGlobalOptions().archive_database_enable) {
			params.archiveRecord = record.get('archiveRecord');
		}
		
		if (!Ext.isEmpty(record.get('Evn_id'))) {
			var id = (record.get('Evn_id').split('_').reverse())[0];
		} else {
			sw.swMsg.alert(lang['soobschenie'], lang['u_vyibranoy_zapisi_otsutstvuet_identifikator']);
			return false;
		}

		switch ( record.get('EvnClass_SysNick') ) {
			case 'EvnPL':
				window_name = 'swEvnPLEditWindow';
				params.EvnPL_id = id;
			break;

			case 'EvnPLStom':
				window_name = 'swEvnPLStomEditWindow';
				params.EvnPLStom_id = id;
			break;

			case 'EvnPS':
				window_name = 'swEvnPSEditWindow';
				params.EvnPS_id = id;
			break;

			case 'EvnRecept':
				window_name = 'swEvnReceptEditWindow';
				params.action = 'view';
				params.EvnRecept_id = id;
				params.EvnRecept_pid = record.get('Evn_pid');
			break;
			case 'EvnCmp':
				if (getRegionNick().inlist([ 'ekb', 'perm' ])) {
					window_name = 'swCmpCallCardEditWindow';
				} else {
					window_name = 'swCmpCallCardNewCloseCardWindow';
				}
				params.formParams = {};
				params.formParams.CmpCallCard_id = id;
			break;
		}

		if ( window_name.length > 0 ) {
			if ( getWnd(window_name).isVisible() ) {
				sw.swMsg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_ukazannogo_tipa_sobyitiya_uje_otkryito']);
			}
			else {
				getWnd(window_name).show(params);
			}
		}
	},
	openEvnPLDDEditWindow: function(action) {
		if ( action != 'edit' && action != 'view' ) {
			return false;
		}

		var grid = this.findById('PCureHF_PersonDispDopGrid').getGrid();
		var view_frame = this.findById('PCureHF_PersonDispDopGrid');

		if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('EvnPLDispDop_id') ) {
			return false;
		}

		var params = new Object();
		var record = grid.getSelectionModel().getSelected();
		var window_name = 'swEvnPLDispDopEditWindow';

		var index = grid.getStore().indexOf(record);

		if ( index == -1 ) {
			return false;
		}

		params.action = action;
		params.onHide = function() {
			view_frame.getGrid().getView().focusRow(index);
		};
		params.EvnPLDispDop_id = record.get('EvnPLDispDop_id');
		params.Person_id = record.get('Person_id');
		params.Server_id = record.get('Server_id');

		if (getGlobalOptions().archive_database_enable) {
			params.archiveRecord = record.get('archiveRecord');
		}

		if ( window_name.length > 0 ) {
			if ( getWnd(window_name).isVisible() ) {
				sw.swMsg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_talona_po_dop_dispanserizatsii_uje_otkryito']);
			}
			else {
				getWnd(window_name).show(params);
			}
		}
	},
	personId: null,
	plain: true,
	printEvent: function() {
		var grid = this.PersonCureHistoryGrid.getGrid();

		if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('Evn_id') ) {
			return false;
		}

		var record = grid.getSelectionModel().getSelected();
		var url = '';

		if (!Ext.isEmpty(record.get('Evn_id'))) {
			var id = (record.get('Evn_id').split('_').reverse())[0];
		} else {
			sw.swMsg.alert(lang['soobschenie'], lang['u_vyibranoy_zapisi_otsutstvuet_identifikator']);
			return false;
		}

		switch ( record.get('EvnClass_SysNick') ) {
			case 'EvnPL':
				url = C_EVNPL_PRINT + '&EvnPL_id=' + id;
			break;

			case 'EvnPLStom':
				url = C_EVNPLSTOM_PRINT + '&EvnPLStom_id=' + id;
			break;

			case 'EvnPS':
				url = C_EVNPS_PRINT + '&EvnPS_id=' + id;
			break;

			case 'EvnRecept':
				url = C_EVNREC_PRINT + '&EvnRecept_id=' + id;
			break;
		}

		if ((getGlobalOptions().region.nick == 'penza') && (record.get('EvnClass_SysNick') == 'EvnPL')) { //https://redmine.swan.perm.ru/issues/63097
			printBirt({
				'Report_FileName': 'EvnPLPrint.rptdesign',
				'Report_Params': '&paramEvnPL=' + id,
				'Report_Format': 'pdf'
			});
		}
		else {
			if ( url.length > 0 ) {
				window.open(url, '_blank');
			}
		}
	},
	resizable: true,
	serverId: null,
	show: function() {
		sw.Promed.swPersonCureHistoryWindow.superclass.show.apply(this, arguments);

		this.restore();
		this.center();

		this.callback = Ext.emptyFn;
		this.onHide = Ext.emptyFn;
		this.personId = null;
		this.serverId = null;

		this.action = 'edit';

		if ( arguments[0] ) {
			if ( arguments[0].callback ) {
				this.callback = arguments[0].callback;
			}

			if ( arguments[0].action ) {
				this.action = arguments[0].action;
			}

			if ( arguments[0].onHide ) {
				this.onHide = arguments[0].onHide;
			}

			if ( arguments[0].Person_id ) {
				this.personId = arguments[0].Person_id;
			}

			if ( arguments[0].Server_id ) {
				this.serverId = arguments[0].Server_id;
			}
		}

		this.findById('PCureHF_PersonCureHistoryGrid').setReadOnly(this.action == 'view');
		this.findById('PCureHF_PersonDispDopGrid').setReadOnly(this.action == 'view');

		this.findById('PCureHF_PersonInformationFrame').load({
			Person_id: this.personId,
			Person_Birthday: (arguments[0].Person_Birthday ? arguments[0].Person_Birthday : ''),
			Person_Firname: (arguments[0].Person_Firname ? arguments[0].Person_Firname : ''),
			Person_Secname: (arguments[0].Person_Secname ? arguments[0].Person_Secname : ''),
			Person_Surname: (arguments[0].Person_Surname ? arguments[0].Person_Surname : '')
		});

		this.findById('PCureHF_PersonCureHistoryGrid').removeAll();
		this.findById('PCureHF_PersonDispDopGrid').removeAll();

		if ( this.personId ) {
			this.findById('PCureHF_PersonCureHistoryGrid').loadData({
				globalFilters: {
					Person_id: this.personId,
					Server_id: this.serverId
				}
			});
			
			this.findById('PCureHF_PersonDispDopGrid').loadData({
				globalFilters: {
					Person_id: this.personId,
					Server_id: this.serverId
				}
			});
		}
	},
	title: lang['istoriya_lecheniya_patsienta'],
	width: 700
});
