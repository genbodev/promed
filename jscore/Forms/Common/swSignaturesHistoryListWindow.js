/**
* swSignaturesHistoryListWindow - окно просмотра списка версий документа.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
*/

sw.Promed.swSignaturesHistoryListWindow = Ext.extend(sw.Promed.BaseForm, {
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	height: 400,
	id: 'SignaturesHistoryListWindow',
	initComponent: function() {
		var win = this;
		
		this.SignaturesHistoryGrid = new sw.Promed.ViewFrame({
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
			dataUrl: '/?c=Signatures&m=loadSignaturesHistoryList',
			uniqueId: true,
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
				{ name: 'Signatures_Version', type: 'int', header: langs('Версия'), width: 100 },
				{ name: 'SignaturesHistory_insDT', type: 'string', header: langs('Дата и время'), width: 200, dateFormat: 'd.m.Y H:i:s' },
				{ name: 'PMUser_Name', type: 'string', header: langs('Пользователь'), id: 'autoexpand' }
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
				onShiftTabAction: function() {
					this.buttons[0].focus();
				}.createDelegate(this),
				onTabAction: function() {
					this.buttons[0].focus();
				}.createDelegate(this),
				text: BTN_FRMCLOSE
			}],
			items: [
				this.SignaturesHistoryGrid
			],
			layout: 'border'
		});

		sw.Promed.swSignaturesHistoryListWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			Ext.getCmp('SignaturesHistoryListWindow').hide();
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
		sw.Promed.swSignaturesHistoryListWindow.superclass.show.apply(this, arguments);
		
		/*this.SignaturesHistoryGrid.addActions({
			name:'action_export', 
			text:langs('Экспорт'), 
			tooltip: langs('Экспорт подписанного документа'),
			iconCls: 'save16',
			handler: function() {
				win.exportSignedDoc();
			}.createDelegate(this)
		}, 3);*/
		
		/*this.SignaturesHistoryGrid.addActions({
			name:'action_signinfo', 
			text:langs('Подробнее'), 
			tooltip: langs('Информация о подписи'),
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
			sw.swMsg.alert(langs('Сообщение'), langs('Неверные параметры'), function() { this.hide(); }.createDelegate(this) );
			return false;
		}

		if ( arguments[0].callback ) {
			this.callback = arguments[0].callback;
		}

		if ( arguments[0].onHide ) {
			this.onHide = arguments[0].onHide;
		}

		this.SignaturesHistoryGrid.removeAll();

		this.SignaturesHistoryGrid.loadData({
			globalFilters: {
				Signatures_id: arguments[0].Signatures_id
			}
		});
	},
	title: langs('Версии документа: Список'),
	width: 700
});
