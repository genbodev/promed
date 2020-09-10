/**
* swEvnStickListWindow - окно просмотра списка ЛВН.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Stick
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage@swan.perm.ru)
* @version      0.001-06.09.2010
*/

sw.Promed.swEvnStickListWindow = Ext.extend(sw.Promed.BaseForm, {
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	evnStickId: null,
	height: 400,
	id: 'EvnStickListWindow',
	openNewStickEditWindow: function()
	{
		var win = this;
		win.advanceParams.action = 'add';
		win.advanceParams.EvnStick_id = null;
		win.advanceParams.fromList = true;
		win.advanceParams.onHide = function()
		{
			win.EvnStickGrid.loadData({
			globalFilters: {
				EvnStick_id: win.evnStickId,
				Person_id: win.personId,
				StickWorkType_id: win.StickWorkType_id,
				EvnStickOriginal_prid: win.EvnStickOriginal_prid
			}
		})
		}.createDelegate(this);
		getNewWnd('swEvnStickEditWindow').show(win.advanceParams);

	},
	initComponent: function() {
		var win = this;

		this.EvnStickGrid = new sw.Promed.ViewFrame({
			actions: [
				{ name: 'action_add', handler: function() {
					this.openNewStickEditWindow();
				}.createDelegate(this), disabled: true },
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
			dataUrl: '/?c=Stick&m=loadEvnStickList',
			id: win.id + '_EvnStickGrid',
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
				{ name: 'EvnStick_id', type: 'int', header: 'ID', key: true },
				{ name: 'Org_id', type: 'int', hidden: true },
				{ name: 'EvnStick_OrgNick', type: 'string', hidden: true },
				{ name: 'Post_Name', type: 'string', hidden: true },
				{ name: 'disabled', type: 'int', hidden: true },
				{ name: 'MaxDaysLimitAfterStac', type: 'int', hidden: true },
				{ name: 'StickCause_id', type: 'int', hidden: true},
				{ name: 'StickCause_SysNick', type: 'string', hidden: true },
				{ name: 'StickCause_did', type: 'int', hidden: true},
				{ name: 'StickCauseDid_SysNick', type: 'string', hidden: true },
				{ name: 'StickLeaveType_Code', type: 'string', hidden: true },
				{ name: 'EvnStick_stacBegDate', type: 'string', hidden: true },
				{ name: 'EvnStickWorkRelease_begDate', type: 'date', hidden: true },
				{ name: 'EvnStickWorkRelease_endDate', type: 'date', hidden: true },
				{ name: 'StickCause_id', type: 'int', hidden: true },
				{ name: 'StickCause_did', type: 'int', hidden: true },
				{ name: 'Lpu_oid', type: 'int', hidden: true },
				{ name: 'ResumedIn', type: 'string', header: langs('Продолжен в ТАП/КВС'), width: 100 },
				{ name: 'ResumedInNum', type: 'string', header: langs('Продолжен ТАП/КВС номер'), width: 100 },
				{ name: 'EvnStick_setDate', type: 'date', header: langs('Выдан'), width: 80 },
				{ name: 'EvnStick_disDate', type: 'date', header: 'Закрыт', width: 80 },
				{ name: 'EvnStick_Ser', type: 'string', header: lang['seriya'], width: 80 },
				{ name: 'EvnStick_Num', type: 'string', header: lang['nomer'], width: 100 },
				{ name: 'StickOrder_Name', type: 'string', header: lang['poryadok'], id: 'autoexpand' },
				{ name: 'EvnStatus_Name', type: 'string', header: lang['tip_lvn'], width: 100 },
				{ name: 'StickWorkType_Name', type: 'string', header: 'Тип занятости', width: 100 }
			],
			// title: 'ЛВН: Список',
			toolbar: true
		});

		this.EvnStickGrid.getGrid().view = new Ext.grid.GridView({
			getRowClass : function (row, index) {
				var cls = '';
				if (row.get('disabled') == 1)
					cls = cls+'x-grid-rowgray ';
				if (cls.length == 0)
					cls = 'x-grid-panel'; 
				return cls;
			}
		});
		
		this.PersonInfo = new sw.Promed.PersonInformationPanelShort({
			id: win.id + '_PersonInformationFrame',
			region: 'north'
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.onSelect();
				}.createDelegate(this),
				iconCls: 'ok16',
				id: win.id + '_SelectButton',
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
				id: win.id + '_CloseButton',
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
				this.EvnStickGrid
			],
			layout: 'border'
		});
		sw.Promed.swEvnStickListWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			Ext.getCmp('EvnStickListWindow').hide();
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
		if ( !this.EvnStickGrid.getGrid() || !this.EvnStickGrid.getGrid().getSelectionModel() || !this.EvnStickGrid.getGrid().getSelectionModel().getSelected() ) {
			return false;
		}

		var record = this.EvnStickGrid.getGrid().getSelectionModel().getSelected();

		if ( !record || !record.get('EvnStick_id') ) {
			return false;
		}
		
		if (record.get('disabled') == 1) {
			sw.swMsg.alert(lang['oshibka'], lang['vyibrannyiy_lvn_uje_imeet_prodoljenie']); 
			return false;
		}

		this.callback({
			EvnStickWorkRelease_endDate: record.get('EvnStickWorkRelease_endDate'),
			EvnStick_disDate: record.get('EvnStick_disDate'),
			EvnStick_id: record.get('EvnStick_id'),
			StickCause_id: record.get('StickCause_id'),
			StickCause_SysNick: record.get('StickCause_SysNick'),
			StickCause_did: record.get('StickCause_did'),
			StickCauseDid_SysNick: record.get('StickCauseDid_SysNick'),
			Lpu_oid: record.get('Lpu_oid'),
			MaxDaysLimitAfterStac: record.get('MaxDaysLimitAfterStac'),
			Org_id: record.get('Org_id'),
			EvnStick_OrgNick: record.get('EvnStick_OrgNick'),
			PridStickLeaveType_Code: record.get('StickLeaveType_Code'),
			Post_Name: record.get('Post_Name'),
			EvnStick_stacBegDate: record.get('EvnStick_stacBegDate'),
			title: record.get('EvnStick_Ser') + ' ' + record.get('EvnStick_Num') + ', ' + Ext.util.Format.date(record.get('EvnStick_setDate'), 'd.m.Y')
		});

		this.hide();
	},
	personId: null,
	plain: true,
	resizable: true,
	show: function() {
		sw.Promed.swEvnStickListWindow.superclass.show.apply(this, arguments);

		this.restore();
		this.center();

		this.callback = Ext.emptyFn;
		this.evnStickId = null;
		this.onHide = Ext.emptyFn;
		this.personId = null;
		this.serverId = null;
		this.StickWorkType_id = null;
		this.EvnStickOriginal_prid = null;

		if ( !arguments[0] ) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() { this.hide(); }.createDelegate(this) );
			return false;
		}

		if ( arguments[0].advanceParams ) {
			this.advanceParams = arguments[0].advanceParams;
		}

		if ( arguments[0].callback ) {
			this.callback = arguments[0].callback;
		}

		if ( arguments[0].EvnStick_id ) {
			this.evnStickId = arguments[0].EvnStick_id;
		}
		
		if ( arguments[0].StickWorkType_id ) {
			this.StickWorkType_id = arguments[0].StickWorkType_id;
		}

		if ( arguments[0].EvnStickOriginal_prid ) {
			this.EvnStickOriginal_prid = arguments[0].EvnStickOriginal_prid;
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

		this.EvnStickGrid.removeAll();

		if(getGlobalOptions().region.nick == 'astra')
		{
			if(this.advanceParams && this.advanceParams.fromList)
				this.EvnStickGrid.setActionDisabled('action_add',true);
			else
				this.EvnStickGrid.setActionDisabled('action_add',false);
		}

		if ( this.personId ) {
			this.EvnStickGrid.loadData({
				globalFilters: {
					EvnStick_id: this.evnStickId,
					Person_id: this.personId,
					StickWorkType_id: this.StickWorkType_id,
					EvnStickOriginal_prid: this.EvnStickOriginal_prid
				}
			})
		}
	},
	title: lang['lvn_spisok'],
	width: 900
});
