/**
* АРМ администратора профосмотров
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2017 Swan Ltd.
*/
sw.Promed.swProfServiceWorkPlaceWindow = Ext.extend(sw.Promed.BaseForm,
{
	id: 'swProfServiceWorkPlaceWindow',
	layout: 'border',
	maximized: true,
	show: function()
	{
		sw.Promed.swProfServiceWorkPlaceWindow.superclass.show.apply(this, arguments);

		var win = this;

		if (arguments[0].userMedStaffFact){ this.userMedStaffFact = arguments[0].userMedStaffFact; }
		else { this.userMedStaffFact = arguments[0]; }

		this.ElectronicService_id = null;
		if (this.userMedStaffFact.ElectronicService_id) {
			this.ElectronicService_id = this.userMedStaffFact.ElectronicService_id
		}

		if ( arguments[0].MedService_id && arguments[0].UslugaComplexMedService_id ) {
			this.MedService_id = arguments[0].MedService_id;
			this.UslugaComplexMedService_id = arguments[0].UslugaComplexMedService_id;

			if (arguments[0].MedServiceType_SysNick)
				this.MedServiceType_SysNick = arguments[0].MedServiceType_SysNick;

		} else {
			// Не понятно, что за АРМ открывается
			sw.swMsg.alert(langs('Сообщение'), langs('Неверные параметры'), function () {
				this.hide();
			}.createDelegate(this));
			return false;
		}

		// Создаем свой заголовок, единый для всех армов, на основании данных пришедших с сервера
		sw.Promed.MedStaffFactByUser.setMenuTitle(this, arguments[0]);

		this.WorkPlaceGrid.addActions({
			handler: function() {
				win.openEmk();
			},
			iconCls: 'open16',
			name: 'open_emk',
			text: 'Открыть ЭМК',
			tooltip: 'Открыть ЭМК'
		}, 0);

		win.dateFilter.setValue(getGlobalOptions().date);
		win.ElectronicQueuePanel.initElectronicQueue();

		win.doSearch();
	},
	openEmk: function() {

		var form = this;
		var grid = form.WorkPlaceGrid.getGrid();

		var record = grid.getSelectionModel().getSelected();

		// при смене статуса талона бывает,  что рекорд пустой, тогда берем из сохраненной записи в панели
		if (!record) record = form.ElectronicQueuePanel.lastSelectedRecord;
		if (!form.ElectronicQueuePanel.checkIsUnknown({record: record})) {log('is_unknown'); return false; }

		var electronicQueueData = (form.ElectronicQueuePanel.electronicQueueData
				? form.ElectronicQueuePanel.electronicQueueData
				: form.ElectronicQueuePanel.getElectronicQueueData()
		);

		if (form.ElectronicQueuePanel.electronicQueueData) form.ElectronicQueuePanel.electronicQueueData = null;

		var params = {
			Person_id: record.get('Person_id'),
			Server_id: record.get('Server_id'),
			PersonEvn_id: record.get('PersonEvn_id'),
			userMedStaffFact: this.userMedStaffFact,
			mode: 'workplace',
			electronicQueueData: electronicQueueData,
			ARMType: 'common',
			callback: function(owner, somethingElse, retParams) {

				// выполняем кэллбэк
				if (retParams && retParams.callback && typeof retParams.callback == 'function') retParams.callback();

			},
			searchNodeObj: {
				parentNodeId: 'root',
				last_child: false,
				disableLoadViewForm: false,
				EvnClass_SysNick: 'EvnPLDispTeenInspection',
				Evn_id: record.get('EvnPLDispTeenInspection_id')
			},
		};

		getWnd('swPersonEmkWindow').show(params);
	},
    doSearch: function(options) {

		var win = this;

		if (typeof options != 'object') { options = new Object(); }
		this.WorkPlaceGrid.removeAll({clearAll: true});

		var params = {
			globalFilters: {
				UslugaComplexMedService_id: win.UslugaComplexMedService_id,
				ElectronicService_id: win.ElectronicService_id,
				onDate: win.dateFilter.getValue().format('d.m.Y'),
			}
		};

		this.WorkPlaceGrid.loadData(params);
    },
	stepDay: function(day){
		var win = this;
		var date1 = (win.dateFilter.getValue() || Date.parseDate(getGlobalOptions().date, 'd.m.Y')).add(Date.DAY, day).clearTime();
		win.dateFilter.setValue(Ext.util.Format.date(date1, 'd.m.Y'));
	},
	prevDay: function() {
		this.stepDay(-1);
	},
	nextDay: function (){
		this.stepDay(1);
	},
	initComponent: function() {

		var win = this;

		win.dateFilter = new sw.Promed.SwDateField({
			format : 'd.m.Y',
			plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
			listeners: {
				'select': function() { win.doSearch(); },
				'keydown': function(inp , e) {
					if (e.getKey() == Ext.EventObject.ENTER) {
						e.stopEvent();
						win.doSearch();
					}
				}
			}
		});

		win.toolbarDate = new Ext.Toolbar({
			items: [{
				xtype: 'tbfill'
			}, {
				xtype: 'button',
				iconCls: 'arrow-previous16',
				handler: function()
				{
					win.prevDay();
					win.doSearch();
				}
			}, win.dateFilter, {
				xtype: 'button',
				iconCls: 'arrow-next16',
				handler: function()
				{
					win.nextDay();
					win.doSearch();
				}
			}]
		});

		this.FilterPanel = new Ext.Panel({hidden: true});

		win.WorkPlaceGrid = new sw.Promed.ViewFrame({
			showOnlyActive: true,
			uniqueId: true,
			title: '',
			region: 'center',
			dataUrl: '/?c=ProfService&m=loadWorkPlaceGrid',
			paging: false,
			toolbar: true,
			root: '',
			totalProperty: 'totalCount',
			autoLoadData: false,
			remoteSort: true,
			stringfields:
			[
				{name: 'TimetableMedService_id', type: 'int', header: 'ID', key: true},
				{name: 'TimetableMedService_begTime', type: 'string', header: 'Записан', width: 120},
				{name: 'Person_id', type: 'int', hidden: true},
				{name: 'Person_Firname', hidden: true, type: 'string'},
				{name: 'Person_Secname', hidden: true, type: 'string'},
				{name: 'Person_Surname', hidden: true, type: 'string'},
				{name: 'EvnDirection_id', hidden: true, type: 'int'},
				{name: 'PersonEvn_id', type: 'int', hidden: true},
				{name: 'Server_id', type: 'int', hidden: true},
				{name: 'EvnPLDispTeenInspection_id', type: 'int', hidden: true},
				{name: 'IsCurrentDate', type: 'int', hidden: true},
				{name: 'Person_IsUnknown', type: 'int', hidden: true},
				{name: 'RecMethodType_Name', type: 'string', header: 'Способ записи', width: 120},
				{name: 'Person_Fio', header: 'ФИО', width: 300, id: 'autoexpand'},
				{name: 'Person_BirthDay', type: 'string', header: 'Дата рождения', width: 120},
                {name: 'ElectronicTalon_IdleTime', type: 'string', header: 'Ожидает (мин)', width: 120},
			],
			actions:
			[
				{name:'action_add', hidden: true, disabled: true},
				{name:'action_edit', hidden: true, disabled: true},
				{name:'action_view', hidden: true, disabled: true},
				{name:'action_delete', hidden: true, disabled: true},
				{name:'action_refresh'},
				{name:'action_print'}
			],
			onLoadData: function() {},
			onRowSelect: function() {}
		});

		this.ElectronicQueuePanel = new sw.Promed.ElectronicQueuePanel({
			ownerWindow: win,
			ownerGrid: win.WorkPlaceGrid.getGrid(), // передаем грид для работы с ЭО
			gridPanel: win.WorkPlaceGrid, // свяжем так же грид панель
			applyCallActionFn: function(){ win.openEmk() }, // передаем то что будет отрываться при на жатии на принять
			region: 'south',
			refreshTimer: 15000,
			DispClass_id: 10, // признак, означает то, что передаются доп. параметры
			redirection: false
		});

		Ext.apply(this,	{
			tbar: win.toolbarDate,
			items: [win.WorkPlaceGrid, win.ElectronicQueuePanel],
			buttons: []
		});

		sw.Promed.swProfServiceWorkPlaceWindow.superclass.initComponent.apply(this, arguments);
	}
});