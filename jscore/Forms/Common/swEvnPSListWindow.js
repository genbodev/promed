/**
* swEvnPSListWindow - окно просмотра списка КВС.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage@swan.perm.ru)
* @version      15.10.2010
*/

sw.Promed.swEvnPSListWindow = Ext.extend(sw.Promed.BaseForm, {
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	height: 400,
	id: 'EvnPSListWindow',
	initComponent: function() {
		this.EvnPSGrid = new sw.Promed.ViewFrame({
			actions: [
				{ name: 'action_add', disabled: true },
				{ name: 'action_edit', disabled: true },
				{ name: 'action_view', disabled: true },
				{ name: 'action_delete', disabled: true },
				{ name: 'action_refresh' },
				{ name: 'action_print', disabled: true}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			border: false,
			dataUrl: '/?c=EvnPS&m=loadEvnPSList',
			id: 'EPSLVW_EvnPSGrid',
			onDblClick: function() {
				this.onSelect();
			}.createDelegate(this),
			onEnter: function() {
				this.onSelect();
			}.createDelegate(this),
			onLoadData: function(result) {
				//
			}.createDelegate(this),
			onRowSelect: function(sm, index, record) {
				//
			}.createDelegate(this),
			paging: false,
			region: 'center',
			stringfields: [
				// Поля для отображение в гриде
				{ name: 'EvnPS_id', type: 'int', header: 'ID', key: true },
				{ name: 'EvnPS_deathDate', type: 'date', hidden: true },
				{ name: 'EvnPS_deathTime', type: 'string', hidden: true },
				{ name: 'PrehospType_id', type: 'int', hidden: true },
				{ name: 'EvnPS_NumCard', type: 'string', header: lang['nomer_kartyi'], id: 'autoexpand' },
				{ name: 'EvnPS_setDate', type: 'date', header: lang['data_postupleniya'], width: 100 },
				{ name: 'EvnPS_disDate', type: 'date', header: lang['data_vyipiski'], width: 100 }
			],
			// title: 'КВС: Список',
			toolbar: true
		});

		this.PersonInfo = new sw.Promed.PersonInformationPanelShort({
			id: 'EPSLVW_PersonInformationFrame',
			region: 'north'
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.onSelect();
				}.createDelegate(this),
				iconCls: 'ok16',
				id: 'EPSLVW_SelectButton',
				onShiftTabAction: function() {
					this.buttons[this.buttons.length - 1].focus();
				}.createDelegate(this),
				onTabAction: function() {
					this.buttons[this.buttons.length - 1].focus();
				}.createDelegate(this),
				text: lang['vyibrat']
			}, {
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				id: 'EPSLVW_CloseButton',
				onShiftTabAction: function() {
					this.buttons[0].focus();
				}.createDelegate(this),
				onTabAction: function() {
					this.buttons[0].focus();
				}.createDelegate(this),
				text: BTN_FRMCLOSE
			}],
			items: [
				this.PersonInfo,
				this.EvnPSGrid
			],
			layout: 'border'
		});
		sw.Promed.swEvnPSListWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			Ext.getCmp('EvnPSListWindow').hide();
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
	onSelect: function() {
		if ( !this.EvnPSGrid.getGrid() || !this.EvnPSGrid.getGrid().getSelectionModel() || !this.EvnPSGrid.getGrid().getSelectionModel().getSelected() ) {
			return false;
		}

		var record = this.EvnPSGrid.getGrid().getSelectionModel().getSelected();

		if ( !record || !record.get('EvnPS_id') ) {
			return false;
		}

		this.callback({
			EvnPS_id: record.get('EvnPS_id'),
			EvnPS_deathDate: record.get('EvnPS_deathDate'),
			EvnPS_deathTime: record.get('EvnPS_deathTime'),
			PrehospType_id: record.get('PrehospType_id'),
			EvnPS_NumCard: record.get('EvnPS_NumCard'),
			EvnPS_setDate: record.get('EvnPS_setDate'),
			EvnPS_disDate: record.get('EvnPS_disDate'),
			title: record.get('EvnPS_NumCard') + ', ' + Ext.util.Format.date(record.get('EvnPS_setDate'), 'd.m.Y')
		});

		this.hide();
	},
	personId: null,
	plain: true,
	resizable: true,
	show: function() {
		sw.Promed.swEvnPSListWindow.superclass.show.apply(this, arguments);

		this.restore();
		this.center();

		this.callback = Ext.emptyFn;
		this.onHide = Ext.emptyFn;
		this.personId = null;

		if ( !arguments[0] || !arguments[0].Person_id ) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() { this.hide(); }.createDelegate(this) );
			return false;
		}

		if ( arguments[0].callback ) {
			this.callback = arguments[0].callback;
		}

		if ( arguments[0].onHide ) {
			this.onHide = arguments[0].onHide;
		}

		if ( arguments[0].Person_id ) {
			this.personId = arguments[0].Person_id;
		}

		this.PersonInfo.load({
			Person_id: (arguments[0].Person_Birthday ? arguments[0].Person_id : ''),
			Person_Birthday: (arguments[0].Person_Birthday ? arguments[0].Person_Birthday : ''),
			Person_Firname: (arguments[0].Person_Firname ? arguments[0].Person_Firname : ''),
			Person_Secname: (arguments[0].Person_Secname ? arguments[0].Person_Secname : ''),
			Person_Surname: (arguments[0].Person_Surname ? arguments[0].Person_Surname : '')
		});

		this.EvnPSGrid.removeAll();

		if ( this.personId ) {
			this.EvnPSGrid.loadData({
				globalFilters: {
					Person_id: this.personId
				}
			});
		}
	},
	title: lang['kvs_spisok'],
	width: 700
});
