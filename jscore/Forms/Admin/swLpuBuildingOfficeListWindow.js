/**
 * swLpuBuildingOfficeListWindow - справочник кабинетов
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package     Admin
 * @access      public
 * @copyright	Copyright (c) 2017 Swan Ltd.
 */
sw.Promed.swLpuBuildingOfficeListWindow = Ext.extend(sw.Promed.BaseForm, {
	/* свойства */
	height: 600,
	id: 'swLpuBuildingOfficeListWindow',
	layout: 'border',
	maximizable: false,
	maximized: true,
	resizable: true,
	title: 'Справочник кабинетов',
	width: 900,

	/* методы */
	addCloseFilterMenu: function(gridCmp) {
		var wnd = this;
		var grid = gridCmp;

		if ( !grid.getAction('action_isclosefilter_' + grid.id) ) {
			var menuIsCloseFilter = new Ext.menu.Menu({
				items: [
					new Ext.Action({
						text: lang['vse'],
						handler: function() {
							if ( grid.gFilters ) {
								grid.gFilters.isClose = null;
							}

							grid.getAction('action_isclosefilter_' + grid.id).setText(lang['pokazyivat_vse']);
							grid.getGrid().getStore().baseParams.isClose = null;
							grid.getGrid().getStore().reload();
						}
					}),
					new Ext.Action({
						text: lang['otkryityie'],
						handler: function() {
							if ( grid.gFilters ) {
								grid.gFilters.isClose = 1;
							}

							grid.getAction('action_isclosefilter_' + grid.id).setText(lang['pokazyivat_otkryityie']);
							grid.getGrid().getStore().baseParams.isClose = 1;
							grid.getGrid().getStore().reload();
						}
					}),
					new Ext.Action({
						text: lang['zakryityie'],
						handler: function() {
							if ( grid.gFilters ) {
								grid.gFilters.isClose = 2;
							}

							grid.getAction('action_isclosefilter_' + grid.id).setText(lang['pokazyivat_zakryityie']);
							grid.getGrid().getStore().baseParams.isClose = 2;
							grid.getGrid().getStore().reload();
						}
					})
				]
			});

			grid.addActions({
				isClose: 1,
				name: 'action_isclosefilter_' + grid.id,
				text: lang['pokazyivat_otkryityie'],
				menu: menuIsCloseFilter
			});

			grid.getGrid().getStore().baseParams.isClose = 1;
		}

		return true;
	},
	deleteLpuBuildingOffice: function() {
		var wnd = this,
			grid = this.LpuBuildingOfficeGrid.getGrid();

		if ( !grid.getSelectionModel().getSelected()
			|| !grid.getSelectionModel().getSelected().get('LpuBuildingOffice_id')
		) {
			return false;
		}

		var
			record = grid.getSelectionModel().getSelected(),
			LpuBuildingOffice_id = record.get('LpuBuildingOffice_id');

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function (buttonId, text, obj) {
				if ( buttonId == 'yes' ) {
					Ext.Ajax.request({
						callback: function(opt, scs, response) {
							var response_obj = Ext.util.JSON.decode(response.responseText);

							if ( response_obj.success == false ) {
								sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg ? response_obj.Error_Msg : 'Ошибка при удалении кабинета');
							}
							else {
								grid.getStore().remove(record);
							}
						},
						params: {
							LpuBuildingOffice_id: LpuBuildingOffice_id
						},
						url: '/?c=LpuBuildingOffice&m=delete'
					});
				}
			},
			icon: Ext.MessageBox.QUESTION,
			msg: lang['udalit_vyibrannuyu_zapis'],
			title: lang['vopros']
		});
	},
	deleteLpuBuildingOfficeMedStaffLink: function() {
		var wnd = this,
			grid = this.LpuBuildingOfficeMedStaffLinkGrid.getGrid();

		if ( !grid.getSelectionModel().getSelected()
			|| !grid.getSelectionModel().getSelected().get('LpuBuildingOfficeMedStaffLink_id')
		) {
			return false;
		}

		var
			record = grid.getSelectionModel().getSelected(),
			LpuBuildingOfficeMedStaffLink_id = record.get('LpuBuildingOfficeMedStaffLink_id');

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function (buttonId, text, obj) {
				if ( buttonId == 'yes' ) {
					Ext.Ajax.request({
						callback: function(opt, scs, response) {
							var response_obj = Ext.util.JSON.decode(response.responseText);

							if ( response_obj.success == false ) {
								sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg ? response_obj.Error_Msg : 'Ошибка при удалении связки кабинета и места работы');
							}
							else {
								grid.getStore().remove(record);
								//grid.getStore().reload();
							}
						},
						params: {
							LpuBuildingOfficeMedStaffLink_id: LpuBuildingOfficeMedStaffLink_id
						},
						url: '/?c=LpuBuildingOfficeMedStaffLink&m=delete'
					});
				}
			},
			icon: Ext.MessageBox.QUESTION,
			msg: lang['udalit_vyibrannuyu_zapis'],
			title: lang['vopros']
		});
	},
	openLpuBuildingOfficeEditWindow: function(action) {
		var
			grid = this.LpuBuildingOfficeGrid.getGrid(),
			msfGrid = this.LpuBuildingOfficeMedStaffLinkGrid.getGrid(),
			params = new Object(),
			wnd = this;

		params.action = action;
		params.msfCount = (msfGrid.getStore().getCount() > 0 && !Ext.isEmpty(msfGrid.getStore().getAt(0).get('LpuBuildingOfficeMedStaffLink_id')) ? msfGrid.getStore().getCount() : 0);

		if ( action != 'add' ) {
			if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('LpuBuildingOffice_id') ) {
				return false;
			}

			params.formParams = grid.getSelectionModel().getSelected().data;
		}
		else {
			params.formParams = {
				LpuBuildingOffice_id: null
			};
		}

		params.callback = function() {
			grid.getStore().reload();
		};

		getWnd('swLpuBuildingOfficeEditWindow').show(params);
	},
	openLpuBuildingOfficeMedStaffLinkEditWindow: function(action) {
		var
			grid = this.LpuBuildingOfficeMedStaffLinkGrid.getGrid(),
			params = new Object(),
			parentGrid = this.LpuBuildingOfficeGrid.getGrid(),
			wnd = this;

		params.action = action;

		if ( action != 'add' ) {
			if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('LpuBuildingOfficeMedStaffLink_id') ) {
				return false;
			}

			params.formParams = grid.getSelectionModel().getSelected().data;
		}
		else {
			if ( !parentGrid.getSelectionModel().getSelected() || !parentGrid.getSelectionModel().getSelected().get('LpuBuildingOffice_id') ) {
				return false;
			}

			params.formParams = {
				LpuBuildingOffice_id: parentGrid.getSelectionModel().getSelected().get('LpuBuildingOffice_id'),
				LpuBuilding_id: parentGrid.getSelectionModel().getSelected().get('LpuBuilding_id'),
				Lpu_id: parentGrid.getSelectionModel().getSelected().get('Lpu_id')
			};
		}

		params.callback = function() {
			grid.getStore().reload();
		};

		getWnd('swLpuBuildingOfficeMedStaffLinkEditWindow').show(params);
	},
	print: function() {
		var grid = this.LpuBuildingOfficeGrid.getGrid();
		var params = {};
		if ( grid.getSelectionModel().getSelected() || grid.getSelectionModel().getSelected().get('LpuBuilding_id') ){
			params.LpuBuilding_id = grid.getSelectionModel().getSelected().get('LpuBuilding_id');
		}
		getWnd('swLpuBuildingOfficePrintWindow').show(params);
	},
	show: function() {
		sw.Promed.swLpuBuildingOfficeListWindow.superclass.show.apply(this, arguments);

		this.LpuBuildingOfficeGrid.loadData({
			globalFilters: {
				Lpu_id: getGlobalOptions().lpu_id,
				limit: 100,
				start: 0
			}
		});
	},
	reloadLpuBuildingOfficeMedStaffLinkGrid: function(LpuBuildingOffice_id){
		if(!LpuBuildingOffice_id)
			return false;
		this.LpuBuildingOfficeMedStaffLinkGrid.loadData({
			globalFilters: {
				LpuBuildingOffice_id: LpuBuildingOffice_id,
				limit: 100,
				start: 0
			}
		});
	},
	/* конструктор */
	initComponent: function() {
		var wnd = this;

		this.LpuBuildingOfficeGrid = new sw.Promed.ViewFrame({
			actions: [
				{name:'action_add', handler: function() { wnd.openLpuBuildingOfficeEditWindow('add'); }},
				{name:'action_edit', handler: function() { wnd.openLpuBuildingOfficeEditWindow('edit'); }},
				{name:'action_view', handler: function() { wnd.openLpuBuildingOfficeEditWindow('view'); }},
				{name:'action_delete', handler: function() { wnd.deleteLpuBuildingOffice(); }},
				{name:'action_refresh'},
				{name:'action_print', disabled: true, hidden: true}
			],
			autoLoadData: false,
			dataUrl: '/?c=LpuBuildingOffice&m=loadList',
			id: wnd.id + 'LpuBuildingOfficeGrid',
			object: 'LpuBuildingOffice',
			onRowSelect: function (sm,index,record) {
				wnd.reloadLpuBuildingOfficeMedStaffLinkGrid(record.get('LpuBuildingOffice_id'));
			},
			paging: true,
			region: 'center',
			root: 'data',
			stringfields: [
				{name: 'LpuBuildingOffice_id', type: 'int', key: true, hidden: true},
				{name: 'Lpu_id', header: 'Идентификатор МО', hidden: true, type: 'int'},
				{name: 'LpuBuilding_id', header: 'Идентификатор подразделения', hidden: true, type: 'int'},
				{name: 'Lpu_Nick', header: 'МО', width: 200},
				{name: 'LpuBuilding_Name', header: 'Подразделение', width: 200},
				{name: 'LpuBuildingOffice_Number', header: 'Номер кабинета', width: 100},
				{name: 'LpuBuildingOffice_Name', header: 'Наименование кабинета', width: 300},
				{name: 'LpuBuildingOffice_Comment', header: 'Комментарий', type: 'string', id: 'autoexpand'},
				{name: 'LpuBuildingOffice_begDate', header: 'Дата начала', width: 100},
				{name: 'LpuBuildingOffice_endDate', header: 'Дата окончания', width: 100}
			],
			title: 'Кабинеты',
			toolbar: true,
			totalProperty: 'totalCount',
			useEmptyRecord: false
		});

		this.LpuBuildingOfficeMedStaffLinkGrid = new sw.Promed.ViewFrame({
			autoLoadData: false,
			dataUrl: '/?c=LpuBuildingOfficeMedStaffLink&m=loadList',
			height: 300,
			id: wnd.id + 'LpuBuildingOfficeMedStaffLinkGrid',
			object: 'LpuBuildingOfficeMedStaffLink',
			paging: true,
			region: 'south',
			root: 'data',
			title: 'Место работы',
			toolbar: true,
			totalProperty: 'totalCount',
			useEmptyRecord: false,

			onRowSelect: function (sm,index,record) {
				
			},
			stringfields: [
				{name: 'LpuBuildingOfficeMedStaffLink_id', type: 'int', header: 'Идентификатор', key: true, hidden: true},
				{name: 'LpuBuildingOffice_id', type: 'int', hidden: true},
				{name: 'MedService_id', type: 'int', hidden: true},
				{name: 'MedStaffFact_id', type: 'int', hidden: true},
				{name: 'MedService_Name', header: 'Служба', type: 'string', width: 400},
				{name: 'MedPersonal_FIO', header: 'ФИО врача', type: 'string', width: 400},
				{name: 'LpuBuildingOfficeMedStaffLink_begDate', header: 'Дата начала', width: 100},
				{name: 'LpuBuildingOfficeMedStaffLink_endDate', header: 'Дата окончания', width: 100},
				{name: 'LpuBuildingOfficeVizitTimeData', header: 'Расписание', hidden: true}
			],
			actions: [
				{name:'action_add', handler: function() { wnd.openLpuBuildingOfficeMedStaffLinkEditWindow('add'); }},
				{name:'action_edit', handler: function() { wnd.openLpuBuildingOfficeMedStaffLinkEditWindow('edit'); }},
				{name:'action_view', handler: function() { wnd.openLpuBuildingOfficeMedStaffLinkEditWindow('view'); }},
				{name:'action_delete', handler: function() { wnd.deleteLpuBuildingOfficeMedStaffLink(); }},
				{name:'action_refresh', disabled: true, hidden: true},
				{name:'action_print', disabled: true, hidden: true}
			]
		});

		wnd.LpuBuildingOfficeMedStaffLinkGrid.ViewToolbar.on('render', function(vt) { return wnd.addCloseFilterMenu(wnd.LpuBuildingOfficeMedStaffLinkGrid);});

		Ext.apply(this, {
			items: [
				wnd.LpuBuildingOfficeGrid,
				wnd.LpuBuildingOfficeMedStaffLinkGrid
			],
			buttons: [{
				handler: function() {
					wnd.print();
				},
				iconCls: 'print16',
				text: BTN_FRMPRINT
			}, {
				text: '-'
			},
			HelpButton(this), {
				handler: function() {
					wnd.hide();
				},
				iconCls: 'close16',
				text: BTN_FRMCLOSE
			}]
		});

		sw.Promed.swLpuBuildingOfficeListWindow.superclass.initComponent.apply(this, arguments);
	}
});