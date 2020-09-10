/**
* swDocVersionListWindow - окно просмотра списка версий документа.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage@swan.perm.ru)
* @version      01.11.2013
*/

sw.Promed.swDocVersionListWindow = Ext.extend(sw.Promed.BaseForm, {
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	height: 400,
	id: 'DocVersionListWindow',
	openSignedDocInfo: function() {
		var grid = this.DocVersionGrid.getGrid();
		if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('Doc_id') )
		{
			return false;
		}
		
		var record = grid.getSelectionModel().getSelected();
		
		getWnd('swSignedDocInfoWindow').show({
			Doc_id: record.get('Doc_id'),
			Doc_Version: record.get('Doc_Version'),
			Doc_DateTime: record.get('Doc_DateTime')
		});
	},
	exportSignedDoc: function() {
		var win = this;
		var grid = this.DocVersionGrid.getGrid();
		if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('Doc_id') )
		{
			return false;
		}
		
		var record = grid.getSelectionModel().getSelected();
		
		win.getLoadMask(lang['vyipolnyaetsya_eksport_dokumenta']).show();
		// запускаем экспорт документа
		Ext.Ajax.request({
			url: '/?c=ElectronicDigitalSign&m=exportSignedDoc',
			params: {
				Doc_id: record.get('Doc_id'),
				Doc_Version: record.get('Doc_Version')
			},
			callback: function(options, success, response) {
				win.getLoadMask().hide();
				if (success) {	
					var obj = Ext.util.JSON.decode(response.responseText);
					if( obj.link ) {
						sw.swMsg.alert('Сообщение', 'Документ успешно экспортирован, его можно скачать по ссылке:<br><a href="'+obj.link+'" target="_blank">Подписанный документ</a>');
						return true;
					}
				}
				
				sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_eksporte_dokumenta']);
				return false;
			}
		});
	},
	openSignedDoc: function() {
		var grid = this.DocVersionGrid.getGrid();
		if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('Doc_id') )
		{
			return false;
		}
		
		var record = grid.getSelectionModel().getSelected();
		
		getWnd('swSignedDocViewWindow').show({
			Doc_id: record.get('Doc_id'),
			Doc_Version: record.get('Doc_Version'),
			Doc_DateTime: record.get('Doc_DateTime')
		});
	},
	initComponent: function() {
		var win = this;
		
		this.DocVersionGrid = new sw.Promed.ViewFrame({
			actions: [
				{ name: 'action_add', disabled: true, hidden: true },
				{ name: 'action_edit', disabled: true, hidden: true },
				{ name: 'action_view', handler: function() {
					win.openSignedDoc();
				}},
				{ name: 'action_delete', disabled: true, hidden: true },
				{ name: 'action_refresh' },
				{ name: 'action_print' }
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			border: false,
			dataUrl: '/?c=ElectronicDigitalSign&m=loadDocumentVersionList',
			id: 'DVLW_DocVersionGrid',
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
				{ name: 'Doc_unicId', type: 'string', header: 'ID', key: true },
				{ name: 'Doc_id', type: 'string', header: 'Doc_id', hidden: true },
				{ name: 'Doc_Version', type: 'int', header: lang['versiya'], width: 100 },
				{ name: 'Doc_DateTime', type: 'string', header: lang['data_i_vremya'], width: 200, dateFormat: 'd.m.Y H:i:s' },
				{ name: 'pmUser_Name', type: 'string', header: lang['polzovatel'], id: 'autoexpand' }
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
				id: 'DVLW_CloseButton',
				onShiftTabAction: function() {
					this.buttons[0].focus();
				}.createDelegate(this),
				onTabAction: function() {
					this.buttons[0].focus();
				}.createDelegate(this),
				text: BTN_FRMCLOSE
			}],
			items: [
				this.DocVersionGrid
			],
			layout: 'border'
		});

		sw.Promed.swDocVersionListWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			Ext.getCmp('DocVersionListWindow').hide();
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
		sw.Promed.swDocVersionListWindow.superclass.show.apply(this, arguments);
		
		this.DocVersionGrid.addActions({
			name:'action_export', 
			text:lang['eksport'], 
			tooltip: lang['eksport_podpisannogo_dokumenta'],
			iconCls: 'save16',
			handler: function() {
				win.exportSignedDoc();
			}.createDelegate(this)
		}, 3);
		
		this.DocVersionGrid.addActions({
			name:'action_signinfo', 
			text:lang['podrobnee'], 
			tooltip: lang['informatsiya_o_podpisi'],
			iconCls: 'pol-eplsearch16',
			handler: function() {
				win.openSignedDocInfo();
			}.createDelegate(this)
		}, 3);
		
		var win = this;

		this.center();

		this.callback = Ext.emptyFn;
		this.onHide = Ext.emptyFn;

		if ( !arguments[0] || !arguments[0].Doc_id || !arguments[0].Doc_Type ) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() { this.hide(); }.createDelegate(this) );
			return false;
		}

		if ( arguments[0].callback ) {
			this.callback = arguments[0].callback;
		}

		if ( arguments[0].onHide ) {
			this.onHide = arguments[0].onHide;
		}

		this.DocVersionGrid.removeAll();

		this.DocVersionGrid.loadData({
			globalFilters: {
				Doc_id: arguments[0].Doc_Type + '_' + arguments[0].Doc_id
			}
		});
	},
	title: lang['versii_dokumenta_spisok'],
	width: 700
});
