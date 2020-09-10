/**
* Журнал госпитализаций из СМП
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @autor		Miyusov Alexandr
* @copyright    Copyright (c) 2013 Swan Ltd.
* @version      17.01.2013
*/
sw.Promed.swSmpStacDiffDiagJournal = Ext.extend(sw.Promed.swWorkPlaceWindow, {
	
	id: 'swSmpStacDiffDiagJournal',
	title: lang['jurnal_rashojdeniya_diagnozov_skoroy_i_statsionara'],
	buttons: [
		'-',
		{
			text: BTN_FRMHELP,
			iconCls: 'help16',
			handler: function(button, event) {
				ShowHelp(lang['jurnal_rashojdeniya_diagnozov_skoroy_i_statsionara']);
			}
		}, {
			text      : lang['zakryit'],
			tabIndex  : -1,
			tooltip   : lang['zakryit'],
			iconCls   : 'cancel16',
			handler   : function() {
				this.ownerCt.hide();
			}
		}
	],

	openCmpCallCardEditWindow: function(action) {
		if ( !action || !action.toString().inlist([ 'card', 'talon', 'expert', 'history']) ) {
			return false;
		}

		var wnd = '';
		var formParams = new Object();
		var grid = this.GridPanel;//.getGrid();
		var params = new Object();
		var parentObject = this;

		if ( !grid.getSelectionModel().getSelected() ) { // не выбран вызов смп
			return false;
		}
		var selected_record = grid.getSelectionModel().getSelected();
		if ( !selected_record.get('CmpCallCard_id') ) {
			return false;
		}

		switch(action){
			case 'card':
				wnd = 'swCmpCallCardNewCloseCardWindow';
				params.action = 'edit';
				break;
			case 'talon':
				wnd = 'swCmpCallCardNewShortEditWindow';
				params.action = 'edit';
				break;
			case 'expert':
			case 'history':
				//необходимо переделать формы ниже из 4-ки в 2-ку
				//sw.tools.swExpertResponseWindow
				//sw.tools.swCmpCallCardHistory
				sw.swMsg.alert('в разработке');
				return false;
				break;
		}

		if ( getWnd(wnd).isVisible() ) {
			sw.swMsg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_kartyi_vyizova_uje_otkryito']);
			return false;
		}


		params.callback = function(data) {
			if ( !data || !data.cmpCallCardData ) {
				return false;
			}
			grid.getStore().reload();
			//parentObject.emitAddingEvent(data.cmpCallCardData['CmpCallCard_id']);
			//parentObject.emitEditingEvent(data.cmpCallCardData['CmpCallCard_id']);
			this.autoEvent = false;
		}.createDelegate(this);

		formParams.CmpCallCard_id = selected_record.get('CmpCallCard_id');
		if (selected_record) {
			formParams.CmpCloseCard_id = selected_record.get('CmpCloseCard_id');
		}

		params.onHide = function() {
			grid.getView().focusRow(grid.getStore().indexOf(selected_record));
			//parentObject.emitEndEditingEvent(selected_record.get('CmpCallCard_id'));
		};

		formParams.ARMType = this.ARMType;
		params.formParams = formParams;
		if(wnd != '')
			getWnd(wnd).show(params);
	},
	
	show: function() {
		sw.Promed.swSmpStacDiffDiagJournal.superclass.show.apply(this, arguments);
		this.setTitle(WND_AMB_HOSPJOURNAL);
		if ( !arguments[0] ) {
			sw.swMsg.alert(lang['soobschenie'], lang['otsutstvuyut_parametryi'], function() {this.hide();}.createDelegate(this) );
			return false;
		}

	},
	initComponent: function() {
		
		var form = this,
			gridItem;

		var gridFields = [
			{dataIndex: 'CmpCloseCard_id', header: 'ID', key: true, hidden: true, hideable: false},
			{dataIndex: 'CmpCallCard_id', hidden: true, hideable: false},
			{dataIndex: 'TimetableStac_id', hidden: true, hideable: false},
			{dataIndex: 'CmpCallCard_prmDate', type: 'date', renderer: Ext.util.Format.dateRenderer('d.m.Y H:i'), header: lang['data_vremya_priema_vizova'], width: 170},
			{dataIndex: 'CmpCallCard_Numv',sortType: 'asInt', type: 'int', header: lang['№_vyizova_za_den'], width: 120},
			{dataIndex: 'CmpCallCard_Ngod',sortType: 'asInt', type: 'int', header: lang['№_vyizova_za_god'], width: 120},
			{dataIndex: 'Person_FIO', header: lang['patsient'], width: 250},
			{dataIndex: 'CmpDiag_Name', header: lang['diagnoz_smp'], width: 390},
			{dataIndex: 'CmpCallCard_HospitalizedTime', type: 'date', renderer: Ext.util.Format.dateRenderer('d.m.Y H:i'), header: lang['data_vremya_v_stacionar'], width: 220},
			{dataIndex: 'Stac_Name', header: lang['statsionar'], width: 180},
			{dataIndex: 'StacDiag_Name', header: lang['diagnoz_priemnogo_otdeleniya_statsionara'], width: 390}

		],
		storeFields = [];
		for(var i=0; i<gridFields.length; i++) {
			gridItem = {mapping: gridFields[i].dataIndex, name: gridFields[i].dataIndex};
			if(gridFields[i].dataIndex == 'CmpCallCard_Numv' || gridFields[i].dataIndex == 'CmpCallCard_Ngod')
			{
				gridItem.sortType = 'asInt';
				gridItem.type = 'int';
			}
			storeFields.push(gridItem);
		}
		gridFields.forEach(function(item, i){
			// добавим возможность сортировки по столбцам
			if( !item.hidden){
				item.sortable = true;
			}
		});

		var gridStore = new Ext.data.GroupingStore({
			autoLoad: false,
			sortInfo: {
				field: 'CmpCallCard_prmDate',
				direction: 'ASC'
			},
			reader: new Ext.data.JsonReader({
				totalProperty: 'totalCount',
				root: 'data'
			}, storeFields),
			url: '/?c=CmpCallCard&m=loadSmpStacDiffDiagJournal'
		});
		
		this.GridPanel = new Ext.grid.GridPanel({
			firstShow: true,
			stripeRows: true,
			//autoExpandColumn: 'autoexpand',
			//title: lang['jurnal_rashojdeniya_diagnozov_skoroy_i_statsionara'],
			headerAsText: false,
				id: form.id + '_Grid',
			hideTaskTime: true,
			paging: false,
			keys: [{
				fn: function(inp, e) {
					switch(e.getKey()) {
						case Ext.EventObject.ENTER:
							this.GridPanel.fireEvent('rowdblclick');
							break;
						case Ext.EventObject.DELETE:
							this.GridPanel.ViewActions.action_delete.execute();
							break;
						case Ext.EventObject.F6:
							this.openPCardHistory();
							break;
						case Ext.EventObject.F10:
							this.openPersonEdit();
							break;
						case Ext.EventObject.F11:
							this.openPCureHistory();
							break;
					}
				},
				key: [
					Ext.EventObject.ENTER,
					Ext.EventObject.DELETE,
					Ext.EventObject.F6,
					Ext.EventObject.F10,
					Ext.EventObject.F11
				],
				scope: this,
				stopEvent: true
			}],
			listeners: {
				render: function() {
					this.contextMenu = new Ext.menu.Menu();
					this.ViewActions = {};
					for(var i=0; i<this.actions.length; i++) {
						this.ViewActions[this.actions[i]['name']] = new Ext.Action(this.actions[i]);
						this.getTopToolbar().add(this.ViewActions[this.actions[i]['name']]);
						if(!this.actions[i]['name'].inlist(['action_refresh','action_print']))
							this.contextMenu.add(this.ViewActions[this.actions[i]['name']]);
					}
					form.LeftPanel.hide();
					form.FilterPanel.hide();
					
					this.getTopToolbar().addFill();
					this.getTopToolbar().addButton({
						disabled: true,
						id: this.id + '_tasktime',
						hidden: typeof this.hideTaskTime != 'undefined' ? this.hideTaskTime : true
					});
				},
				rowcontextmenu: function(grd, num, e) {
					e.stopEvent();
					this.getSelectionModel().selectRow(num);
					this.contextMenu.showAt(e.getXY());
				}
			},
			loadData: function(params) {
				with(this.getStore()) {
					removeAll();
					baseParams = params.globalFilters;
					load();
				}
			},
			tbar: new Ext.Toolbar(),
			actions: [
				{name: 'action_talon', disabled: true, text: langs('Талон вызова'), tooltip: langs('Талон вызова'), handler: this.openCmpCallCardEditWindow.createDelegate(this, ['talon'])},
				{name: 'action_card', disabled: true, text: langs('Карта вызова'), tooltip: langs('Карта вызова'), handler: this.openCmpCallCardEditWindow.createDelegate(this, ['card'])},
				{name: 'action_expert', disabled: true, text: langs('Экспертная оценка'), tooltip: langs('Экспертная оценка'), handler: this.openCmpCallCardEditWindow.createDelegate(this, ['expert'])},
				{name: 'action_history', disabled: true, text: langs('История вызова'), tooltip: langs('История вызова'), handler: this.openCmpCallCardEditWindow.createDelegate(this, ['history'])},
				{name: 'action_refresh', iconCls: 'refresh16', text: langs('Обновить '), handler: function(btn) {this.autoEvent = false;this.doSearch();}.createDelegate(this)},
				{name: 'action_print',disabled: true, iconCls: 'print16', text: langs('Печать списка'),
					handler: 
						function() { 
							var params = {};
							params.notPrintEmptyRows = true;
							Ext.ux.GridPrinter.print(this.GridPanel, params);
						}.createDelegate(this) 
				}
			],
			loadMask: {msg: lang['zagruzka']},
			region: 'center',
			colModel: new Ext.grid.ColumnModel({
				columns: gridFields
			}),
			getAction: function(action) {
				return this.ViewActions[action] || null;
			},
			setParam: function(p, v) {
				this.getStore().baseParams[p] = v;
			},
			store: gridStore
		});
		this.GridPanel.getStore().on('load', function(store, rs) {
			if(store.getCount()) {
				this.getSelectionModel().selectFirstRow();
				var selected_record = this.getSelectionModel().getSelected();
				this.getAction('action_card').setDisabled(!selected_record.get('CmpCloseCard_id') > 0);
				this.getAction('action_print').setDisabled(false);
//				form.focusSelectedRow();
			}else{
				this.getAction('action_print').setDisabled(true);
				this.getAction('action_card').setDisabled(true);
				this.getAction('action_talon').setDisabled(true);
			}
		}.createDelegate(this.GridPanel));
		
		this.GridPanel.getSelectionModel().on('rowselect', function(sm, rowIndex, rec) {
			this.getAction('action_card').setDisabled(rec.get('CmpCallCard_id') == null || rec.get('CmpCloseCard_id') == null || rec.get('CmpCloseCard_id') == '');
		}.createDelegate(this.GridPanel));
		
		this.GridPanel.on('rowdblclick', function() {

			if(!this.getAction('action_card').isDisabled()){
				this.getAction('action_card').execute();
			}

		});
		this.GridPanel.on('rowclick', function(grid, rowIndex) {
			var record = grid.getStore().getAt(rowIndex);
			this.getAction('action_card').setDisabled(!record.get('CmpCloseCard_id') > 0);
			this.getAction('action_talon').setDisabled(false);
		});
		
		
		sw.Promed.swSmpStacDiffDiagJournal.superclass.initComponent.apply(this, arguments);
	}
});