/**
* swFRMOSessionErrorListWindow - окно просмотра списка версий документа.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Dmitry Vlasenko
* @version      12.02.2019
*/

sw.Promed.swFRMOSessionErrorListWindow = Ext.extend(sw.Promed.BaseForm, {
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	height: 400,
	initComponent: function() {
		var win = this;
		
		this.FRMOSessionErrorGrid = new sw.Promed.ViewFrame({
			actions: [
				{ name: 'action_add', disabled: true, hidden: true },
				{ name: 'action_edit', disabled: true, hidden: true },
				{ name: 'action_view', disabled: true, hidden: true },
				{ name: 'action_delete', disabled: true, hidden: true },
				{ name: 'action_refresh' },
				{ name: 'action_print' }
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			border: false,
			dataUrl: '/?c=ServiceFRMO&m=loadFRMOSessionErrorGrid',
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
				{name: 'FRMOSessionError_id', type: 'int', header: 'ID', key: true},
				{name: 'FRMOSessionActionType_Descr', type: 'string', header: 'Стадия', width: 200},
				{name: 'FRMOSessionErrorType_Name', type: 'string', header: 'Ошибка', id: 'autoexpand', width: 200},
				{name: 'FRMOSessionError_Object', header: 'Объект', renderer: function(v, p, record) {
					var s = '';

					if (record) {
						if (record.get('Lpu_Nick')) {
							if (s.length > 0) {
								s = s + ', ';
							}

							s = s + 'МО: ' + record.get('Lpu_Nick');
						}
						if (record.get('LpuUnit_Name')) {
							if (s.length > 0) {
								s = s + ', ';
							}

							s = s + 'Подразделение: ' + record.get('LpuUnit_Name');
						}
						if (record.get('LpuBuildingPass_Name')) {
							if (s.length > 0) {
								s = s + ', ';
							}

							s = s + 'Здание МО: ' + record.get('LpuBuildingPass_Name');
						}
						if (record.get('LpuSection_Name')) {
							if (s.length > 0) {
								s = s + ', ';
							}

							s = s + 'Отделение: ' + record.get('LpuSection_Name');
						}
						if (record.get('LpuStaff_Num')) {
							if (s.length > 0) {
								s = s + ', ';
							}

							s = s + 'Штатное расписание: ' + record.get('LpuStaff_Num');
						}
					}

					return s;
				}, width: 300},
				{name: 'Lpu_Nick', type: 'string', hidden: true},
				{name: 'LpuUnit_Name', type: 'string', hidden: true},
				{name: 'LpuBuildingPass_Name', type: 'string', hidden: true},
				{name: 'LpuSection_Name', type: 'string', hidden: true},
				{name: 'LpuStaff_Num', type: 'string', hidden: true}
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
				this.FRMOSessionErrorGrid
			],
			layout: 'border'
		});

		sw.Promed.swFRMOSessionErrorListWindow.superclass.initComponent.apply(this, arguments);
	},
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
		sw.Promed.swFRMOSessionErrorListWindow.superclass.show.apply(this, arguments);

		var win = this;

		this.center();

		this.callback = Ext.emptyFn;
		this.onHide = Ext.emptyFn;

		if ( !arguments[0] || !arguments[0].FRMOSession_id ) {
			sw.swMsg.alert(langs('Сообщение'), langs('Неверные параметры'), function() { this.hide(); }.createDelegate(this) );
			return false;
		}

		this.FRMOSession_id = arguments[0].FRMOSession_id;

		if ( arguments[0].callback ) {
			this.callback = arguments[0].callback;
		}

		if ( arguments[0].onHide ) {
			this.onHide = arguments[0].onHide;
		}

		this.FRMOSessionErrorGrid.removeAll();

		this.FRMOSessionErrorGrid.loadData({
			globalFilters: {
				FRMOSession_id: win.FRMOSession_id
			}
		});
	},
	title: langs('Список ошибок сессии'),
	width: 1000
});
