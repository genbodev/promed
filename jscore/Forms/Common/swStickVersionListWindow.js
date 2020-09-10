/**
* swStickVersionListWindow - окно просмотра списка версий документа.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
*/

sw.Promed.swStickVersionListWindow = Ext.extend(sw.Promed.BaseForm, {
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	height: 400,
	id: 'StickVersionListWindow',
	initComponent: function() {
		var win = this;
		
		this.StickVersionGrid = new sw.Promed.ViewFrame({
			actions: [
				{ name: 'action_add', disabled: true, hidden: true },
				{ name: 'action_edit', disabled: true, hidden: true },
				{ name: 'action_view', disabled: true, hidden: true},
				{ name: 'action_delete', disabled: true, hidden: true },
				{ name: 'action_refresh' },
				{ name: 'action_print' }
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			border: false,
			dataUrl: '/?c=Stick&m=loadStickVersionList',
			id: 'SVLW_StickVersionGrid',
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
				{ name: 'SignaturesHistory_id', type: 'string', header: 'ID', key: true },
				{ name: 'Signatures_Version', type: 'int', header: lang['versiya'], width: 100 },
				{ name: 'SignaturesHistory_insDT', type: 'string', header: lang['data_i_vremya'], width: 200, dateFormat: 'd.m.Y H:i:s' },
				{ name: 'PMUser_Name', type: 'string', header: lang['polzovatel'], id: 'autoexpand' }
			],
			toolbar: true
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
				id: 'SVLW_CloseButton',
				onShiftTabAction: function() {
					this.buttons[0].focus();
				}.createDelegate(this),
				onTabAction: function() {
					this.buttons[0].focus();
				}.createDelegate(this),
				text: BTN_FRMCLOSE
			}],
			items: [
				this.StickVersionGrid
			],
			layout: 'border'
		});

		sw.Promed.swStickVersionListWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			Ext.getCmp('StickVersionListWindow').hide();
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
	maximizable: false,
	modal: true,
	onSelect: function() {
		//
	},
	plain: true,
	resizable: false,
	show: function() {
		sw.Promed.swStickVersionListWindow.superclass.show.apply(this, arguments);
		
		/*this.StickVersionGrid.addActions({
			name:'action_export', 
			text:lang['eksport'], 
			tooltip: lang['eksport_podpisannogo_dokumenta'],
			iconCls: 'save16',
			handler: function() {
				win.exportSignedDoc();
			}.createDelegate(this)
		}, 3);*/
		
		/*this.StickVersionGrid.addActions({
			name:'action_signinfo', 
			text:lang['podrobnee'], 
			tooltip: lang['informatsiya_o_podpisi'],
			iconCls: 'pol-eplsearch16',
			handler: function() {
				win.openSignedDocInfo();
			}.createDelegate(this)
		}, 3);*/
		
		var win = this;

		this.center();

		this.callback = Ext.emptyFn;
		this.onHide = Ext.emptyFn;

		if ( !arguments[0] || !arguments[0].Signatures_id ) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() { this.hide(); }.createDelegate(this) );
			return false;
		}

		if ( arguments[0].callback ) {
			this.callback = arguments[0].callback;
		}

		if ( arguments[0].onHide ) {
			this.onHide = arguments[0].onHide;
		}

		this.StickVersionGrid.removeAll();

		this.StickVersionGrid.loadData({
			globalFilters: {
				Signatures_id: arguments[0].Signatures_id
			}
		});
	},
	title: lang['versii_dokumenta_spisok'],
	width: 700
});
