/**
* swEvnDirectionHistologicListWindow - окно просмотра списка направлений на патологогистологическое исследование
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Direction
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage@swan.perm.ru)
* @version      07.12.2010
*/

sw.Promed.swEvnDirectionHistologicListWindow = Ext.extend(sw.Promed.BaseForm, {
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	height: 400,
	id: 'EvnDirectionHistologicListWindow',
	initComponent: function() {
		this.EvnDirectionHistologicGrid = new sw.Promed.ViewFrame({
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
			dataUrl: '/?c=EvnDirectionHistologic&m=loadEvnDirectionHistologicList',
			id: 'EDHLVW_EvnDirectionHistologicGrid',
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
				{ name: 'EvnDirectionHistologic_id', type: 'int', header: 'ID', key: true },
				{ name: 'EvnDirectionHistologic_Ser', type: 'string', header: lang['seriya'], id: 'autoexpand' },
				{ name: 'EvnDirectionHistologic_Num', type: 'string', header: lang['nomer'], width: 100 },
				{ name: 'EvnDirectionHistologic_setDate', type: 'date', header: lang['data_napravleniya'], width: 100 }
			],
			toolbar: true
		});

		this.PersonInfo = new sw.Promed.PersonInformationPanelShort({
			id: 'EDHLVW_PersonInformationFrame',
			region: 'north'
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.onSelect();
				}.createDelegate(this),
				iconCls: 'ok16',
				id: 'EDHLVW_SelectButton',
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
				id: 'EDHLVW_CloseButton',
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
				this.EvnDirectionHistologicGrid
			],
			layout: 'border'
		});
		sw.Promed.swEvnDirectionHistologicListWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			Ext.getCmp('EvnDirectionHistologicListWindow').hide();
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
		if ( !this.EvnDirectionHistologicGrid.getGrid() || !this.EvnDirectionHistologicGrid.getGrid().getSelectionModel() || !this.EvnDirectionHistologicGrid.getGrid().getSelectionModel().getSelected() ) {
			return false;
		}

		var record = this.EvnDirectionHistologicGrid.getGrid().getSelectionModel().getSelected();

		if ( !record || !record.get('EvnDirectionHistologic_id') ) {
			return false;
		}

		this.callback({
			EvnDirectionHistologic_id: record.get('EvnDirectionHistologic_id'),
			EvnDirectionHistologic_Ser: record.get('EvnDirectionHistologic_Ser'),
			EvnDirectionHistologic_Num: record.get('EvnDirectionHistologic_Num'),
			EvnDirectionHistologic_setDate: record.get('EvnDirectionHistologic_setDate')
		});

		this.hide();
	},
	personId: null,
	plain: true,
	resizable: true,
	show: function() {
		var win = this;

		sw.Promed.swEvnDirectionHistologicListWindow.superclass.show.apply(this, arguments);

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

		if( arguments[0].formParams ) {
			this.formParams = arguments[0].formParams; 
		}

		this.PersonInfo.load({
			Person_id: (arguments[0].Person_Birthday ? arguments[0].Person_id : ''),
			Person_Birthday: (arguments[0].Person_Birthday ? arguments[0].Person_Birthday : ''),
			Person_Firname: (arguments[0].Person_Firname ? arguments[0].Person_Firname : ''),
			Person_Secname: (arguments[0].Person_Secname ? arguments[0].Person_Secname : ''),
			Person_Surname: (arguments[0].Person_Surname ? arguments[0].Person_Surname : '')
		});

		this.EvnDirectionHistologicGrid.removeAll();

		if ( this.personId ) {
			this.EvnDirectionHistologicGrid.loadData({
				globalFilters: {
					Person_id: this.personId
				}
			});
		}
		this.EvnDirectionHistologicGrid.addActions({ 
			name: 'action_add_outer', 
			text: langs('Внешнее направление'), 
			handler: function() {
				var callback = function (dir) {
					win.callback({
						EvnDirectionHistologic_id: dir.evnDirectionHistologicData.EvnDirectionHistologic_id,
						EvnDirectionHistologic_Ser: dir.evnDirectionHistologicData.EvnDirectionHistologic_Ser,
						EvnDirectionHistologic_Num: dir.evnDirectionHistologicData.EvnDirectionHistologic_Num,
						EvnDirectionHistologic_setDate: dir.evnDirectionHistologicData.EvnDirectionHistologic_setDate							
					});
					win.hide();
				}

				var params = {
					outer: true,
					action: 'add',
					callback: callback,
					formParams: win.formParams
				};
				getWnd('swEvnDirectionHistologicEditWindow').show(params);
			}
		});
	},
	title: lang['napravleniya_na_patologogistologicheskoe_issledovanie_spisok'],
	width: 700
});
