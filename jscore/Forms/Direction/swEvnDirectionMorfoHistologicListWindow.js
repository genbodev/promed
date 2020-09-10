/**
* swEvnDirectionMorfoHistologicListWindow - окно просмотра списка направлений на патоморфогистологическое исследование
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Direction
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage@swan.perm.ru)
* @version      10.02.2011
*/

sw.Promed.swEvnDirectionMorfoHistologicListWindow = Ext.extend(sw.Promed.BaseForm, {
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	height: 400,
	id: 'EvnDirectionMorfoHistologicListWindow',
	initComponent: function() {
		this.EvnDirectionMorfoHistologicGrid = new sw.Promed.ViewFrame({
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
			dataUrl: '/?c=EvnDirectionMorfoHistologic&m=loadEvnDirectionMorfoHistologicList',
			id: 'EDMHLVW_EvnDirectionMorfoHistologicGrid',
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
				{ name: 'EvnDirectionMorfoHistologic_id', type: 'int', header: 'ID', key: true },
				{ name: 'EvnDirectionMorfoHistologic_Ser', type: 'string', header: lang['seriya'], id: 'autoexpand' },
				{ name: 'EvnDirectionMorfoHistologic_Num', type: 'string', header: lang['nomer'], width: 100 },
				{ name: 'EvnDirectionMorfoHistologic_setDate', type: 'date', header: lang['data_napravleniya'], width: 100 }
			],
			toolbar: true
		});

		this.PersonInfo = new sw.Promed.PersonInformationPanelShort({
			id: 'EDMHLVW_PersonInformationFrame',
			region: 'north'
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.onSelect();
				}.createDelegate(this),
				iconCls: 'ok16',
				id: 'EDMHLVW_SelectButton',
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
				id: 'EDMHLVW_CloseButton',
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
				this.EvnDirectionMorfoHistologicGrid
			],
			layout: 'border'
		});
		sw.Promed.swEvnDirectionMorfoHistologicListWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			Ext.getCmp('EvnDirectionMorfoHistologicListWindow').hide();
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
		if ( !this.EvnDirectionMorfoHistologicGrid.getGrid() || !this.EvnDirectionMorfoHistologicGrid.getGrid().getSelectionModel() || !this.EvnDirectionMorfoHistologicGrid.getGrid().getSelectionModel().getSelected() ) {
			return false;
		}

		var record = this.EvnDirectionMorfoHistologicGrid.getGrid().getSelectionModel().getSelected();

		if ( !record || !record.get('EvnDirectionMorfoHistologic_id') ) {
			return false;
		}

		this.callback({
			EvnDirectionMorfoHistologic_id: record.get('EvnDirectionMorfoHistologic_id'),
			EvnDirectionMorfoHistologic_Ser: record.get('EvnDirectionMorfoHistologic_Ser'),
			EvnDirectionMorfoHistologic_Num: record.get('EvnDirectionMorfoHistologic_Num'),
			EvnDirectionMorfoHistologic_setDate: record.get('EvnDirectionMorfoHistologic_setDate')
		});

		this.hide();
	},
	personId: null,
	plain: true,
	resizable: true,
	show: function() {
		var win = this;
		sw.Promed.swEvnDirectionMorfoHistologicListWindow.superclass.show.apply(this, arguments);

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

		win.EvnDirectionMorfoHistologicGrid.addActions({
			name: 'action_add_outer', 
			text: langs('Внешнее направление'), 
			handler: function() {
				var callback = function(dir) {
					win.callback({
						EvnDirectionMorfoHistologic_id: dir.evnDirectionMorfoHistologicData.EvnDirectionMorfoHistologic_id,
						EvnDirectionMorfoHistologic_Ser: dir.evnDirectionMorfoHistologicData.EvnDirectionMorfoHistologic_Ser,
						EvnDirectionMorfoHistologic_Num: dir.evnDirectionMorfoHistologicData.EvnDirectionMorfoHistologic_Num,
						EvnDirectionMorfoHistologic_setDate: dir.evnDirectionMorfoHistologicData.EvnDirectionMorfoHistologic_setDate
					});
					win.hide();
				}
				var params = {
					outer: true,
					action: 'add',
					callback: callback,
					formParams: win.formParams
				}
				getWnd('swEvnDirectionMorfoHistologicEditWindow').show(params);
			}
		});

		this.EvnDirectionMorfoHistologicGrid.removeAll();

		if ( this.personId ) {
			this.EvnDirectionMorfoHistologicGrid.loadData({
				globalFilters: {
					Person_id: this.personId
				}
			});
		}
	},
	title: lang['napravleniya_na_patomorfogistologicheskoe_issledovanie_spisok'],
	width: 700
});
