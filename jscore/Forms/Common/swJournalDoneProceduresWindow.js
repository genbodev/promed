/**
* АРМ медсестры процедурного кабинета
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @autor		Dmitry Vlasenko
* @copyright    Copyright (c) 2011 Swan Ltd.
* @version      15.01.2013
*/

/*NO PARSE JSON*/
sw.Promed.swJournalDoneProceduresWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swJournalDoneProceduresWindow',
	maximized: true,
	border: false,
	buttonAlign: 'right',
	closable: true,
	closeAction: 'hide',
	modal: false,
	plain: false,
	resizable: false,
	title: langs('Журнал проведенных процедур'),
	userMedStaffFact: null,
	show: function()
	{
		var win = this;

		sw.Promed.swJournalDoneProceduresWindow.superclass.show.apply(win, arguments);
		if ((!arguments[0]) || (!arguments[0].userMedStaffFact))
		{
			win.hide();
			Ext.Msg.alert('Ошибка открытия формы', 'Ошибка открытия формы "'+win.title+'".<br/>Не указаны параметры АРМа врача.');
		} else {
			win.userMedStaffFact = arguments[0].userMedStaffFact;
		}

		win.datePeriodToolbar.onShow(win);
	},
	onKeyDown: function (inp, e) {
		if (e.getKey() == Ext.EventObject.ENTER) {
			e.stopEvent();
			this.doSearch();
		}
	},
	initComponent: function()
	{
		var win = this;

		win.datePeriodToolbar = new sw.Promed.datePeriodToolbar({
			curDate: getGlobalOptions().date,
			mode: 'day',
			onSelectPeriod: function(begDate,endDate,allowLoad)
			{
				win.doSearch();
			}
		});


		win.FilterPanel = new sw.Promed.BaseWorkPlaceFilterPanel({
			owner: win,
			tbar: win.datePeriodToolbar,
			filter: {
				title: 'Фильтр',
				layout: 'form',
				items: [{
					layout: 'column',
					items: [{
						layout: 'form',
						labelWidth: 65,
						items:
							[{
								xtype: 'textfieldpmw',
								width: 150,
								name: 'Search_SurName',
								fieldLabel: 'Фамилия',
								listeners: {
									'keydown': win.onKeyDown
								}
							}]
					}, {
						layout: 'form',
						labelWidth: 45,
						items:
							[{
								xtype: 'textfieldpmw',
								width: 150,
								name: 'Search_FirName',
								fieldLabel: 'Имя',
								listeners: {
									'keydown': win.onKeyDown
								}
							}]
					}, {
						layout: 'form',
						labelWidth: 75,
						items:
							[{
								xtype: 'textfieldpmw',
								width: 150,
								name: 'Search_SecName',
								fieldLabel: 'Отчество',
								listeners: {
									'keydown': win.onKeyDown
								}
							}]
					}, {
						layout: 'form',
						labelWidth: 35,
						items:
							[{
								xtype:'swdatefield',
								format:'d.m.Y',
								plugins:[ new Ext.ux.InputTextMask('99.99.9999', false) ],
								name: 'Search_BirthDay',
								fieldLabel: 'ДР',
								listeners: {
									'keydown': win.onKeyDown
								}
							}]
					}, {
						layout: 'form',
						labelWidth: 145,
						items:
							[{
								xtype: 'textfield',
								width: 100,
								name: 'EvnDirection_Num',
								fieldLabel: 'Номер направления',
								listeners: {
									'keydown': win.onKeyDown
								}
							}]
					}]
				}, {
					layout: 'column',
					items: [{
						layout: 'form',
						labelWidth: 65,
						items:
							[{
								xtype: 'swuslugacomplexpidcombo',
								width: 450,
								name: 'Search_Usluga',
								hiddenName: 'UslugaComplex_id',
								fieldLabel: 'Услуга',
								//allowBlank: false,
								listeners: {
									'keydown': win.onKeyDown
								}
							}]
					}, {
						layout: 'form',
						labelWidth: 55,
						items:
							[{
								fieldLabel: 'Cito',
								comboSubject: 'YesNo',
								name: 'EvnDirection_IsCito',
								hiddenName: 'EvnDirection_IsCito',
								xtype: 'swcommonsprcombo'
							}]
					}]
				}, {
					layout: 'column',
					items: [{
						layout: 'form',
						items:
							[{
								xtype: 'button',
								id: win.id+'BtnSearch',
								text: 'Найти',
								iconCls: 'search16',
								handler: function()
								{
									win.doSearch();
								}.createDelegate(win)
							}]
					}, {
						layout: 'form',
						items:
							[{
								style: "padding-left: 10px",
								xtype: 'button',
								id: win.id+'BtnClear',
								text: 'Сброс',
								iconCls: 'reset16',
								handler: function()
								{
									win.doReset();
								}.createDelegate(win)
							}]
					}]
				}]
			}
		});

		win.GridPanel = new sw.Promed.ViewFrame({
			//id: 'WorkPlaceGridPanel',
			region: 'center',
			autoExpandColumn: 'autoexpand',
			grouping: true,
			groupTextTpl: '{[values.text == "(Пусто)" ? "Очередь" : values.text]} ({[values.rs.length]} {[parseInt(values.rs.length.toString().charAt(values.rs.length.toString().length-1)).inlist([1]) ?"процедура" :(parseInt(values.rs.length.toString().charAt(values.rs.length.toString().length-1)).inlist([2,3,4]) ? "процедуры" : "процедур")]})',
			groupingView: {showGroupName: false, showGroupsText: true},
			actions:
				[
					{name:'action_add', hidden: true},
					{name:'action_edit', hidden: true},
					{name:'action_view', hidden: false, handler: function() { win.openEvnFuncRequestEditWindow('view', false);} },
					{name:'action_delete', hidden: true},
					{name: 'action_refresh'},
					{name: 'action_print'}
				],
			autoLoadData: false,
			pageSize: 20,
			useEmptyRecord: false,
			stringfields:
				[
					// Поля для отображение в гриде
					{name: 'EvnDirection_id', type: 'int', header: 'ID', key: true},
					{name: 'EvnFuncRequest_id', type: 'int', hidden:true},
					{name: 'TimetableMedService_id', type: 'int', hidden:true},
					{name: 'TimetableMedService_begDate', type: 'date', hidden: true, group: true, sort: true, direction: 'DESC' },
					{name: 'Person_id', type: 'int', hidden: true},
					{name: 'PersonEvn_id', type: 'int', hidden: true},
					{name: 'Server_id', type: 'int', hidden: true},
					{name: 'Person_FIO', header: 'ФИО пациента', type: 'string', width: 320},
					{name: 'Person_BirthDay', header: 'Дата рождения', type: 'string', width: 320},
					{name: 'EvnFuncRequest_UslugaCache', header: 'Услуга', renderer: function(value, cellEl, rec) {
						var result = '';
						if (!Ext.isEmpty(value)) {
							// разджейсониваем
							var uslugas = Ext.util.JSON.decode(value);
							for(var k in uslugas) {
								if (uslugas[k].UslugaComplex_Name) {
									if (!Ext.isEmpty(result)) {
										result += '<br />';
									}
									result += uslugas[k].UslugaComplex_Name;

									if (!Ext.isEmpty(uslugas[k].EvnUslugaPar_setDate)) {
										result += ' <a title="Отменить выполнение услуги" href="javascript://" onClick="Ext.getCmp(\'swWorkPlaceFuncDiagWindow\').cancelEvnUslugaPar({' +
											'EvnUslugaPar_id: ' + uslugas[k].EvnUslugaPar_id +
											'});"><img width="14" src="/img/icons/cancel_blue16.png" /></a>';
									}
								}
							}
						}
						return result;
					}, width: 420, id: 'autoexpand'},
					{name: 'MedPerson_Fio', header: 'Направивший врач ', type: 'string', width: 320},
					{name: 'Operator', header: 'Врач, наз. Усл. ', type: 'string', width: 320},
					{name: 'EvnDirection_IsCito', header: 'Cito!', type: 'checkbox', width: 40, hidden: true},
					{name: 'FuncRequestState', header: 'Приём', type: 'checkbox', width: 60, hidden: true },
					{name: 'EvnDirection_setDT', dateFormat: 'd.m.Y', type: 'date', header: 'Дата направления', width: 120},
					//{name: 'EvnDirection_setDT', dateFormat: 'd.m.Y H:i:s', type: 'date', header: 'Дата направления', width: 120},
					{name: 'TimetableMedService_begTime', type: 'string', header: 'Запись', width: 120/*, sort: true, direction: 'ASC'*/},
					{name: 'TimetableMedServiceType', type: 'string', header: 'Расписание', width: 120, hidden: true},
					{name: 'EvnDirection_Num', header: 'Номер направления', type: 'string', width: 160, hidden: true},
					{name: 'pmUser_insID', type: 'int', hidden: true}
				],
			//dataUrl: '/?c=EvnFuncRequestProc&m=loadEvnFuncRequestList',
			dataUrl: '/?c=EvnFuncRequestProc&m=loadEvnFuncRequestListDoneStatus',
			totalProperty: 'totalCount',
			title: 'Список заявок',
			onLoadData: function(sm, index, record)
			{
				if (!this.getGrid().getStore().totalLength) {
					this.getGrid().getStore().removeAll();
				}
				if( Ext.get(this.getGrid().getView().getGroupId('(Пусто)')) != null ) {
					this.getGrid().getView().toggleGroup(this.getGrid().getView().getGroupId('(Пусто)'), false);
				}

			},
			onDblClick: function(grid, number, object){
				this.onEnter();
			},
			onEnter: function()
			{
				this.openEvnFuncRequestEditWindow('view', false);

			}.createDelegate(this)
		});

		Ext.apply(this,
			{
				region: 'center',
				layout: 'border',
				items: [
					this.FilterPanel,
					this.GridPanel
				],
				buttons: [{
					id: 'EJHW_BtnSearch',
					text: langs('Найти'),
					tabIndex: TABINDEX_EJHW + 19,
					iconCls: 'search16',
					handler: function()
					{
						this.doSearch();
					}.createDelegate(this)
				},
				{
					id: 'EJHW_BtnClear1',
					text: langs('Сброс'),
					tabIndex: TABINDEX_EJHW + 21,
					iconCls: 'resetsearch16',
					handler: function()
					{
						this.resetForm(true);
					}.createDelegate(this)
				},
				{
					text: '-'
				},
				{
					text: BTN_FRMHELP,
					iconCls: 'help16',
					id: 'EJHW_HelpButton',
					handler: function(button, event)
					{
						ShowHelp(this.title);
					}.createDelegate(this)
				},
				{
					iconCls: 'cancel16',
					text: BTN_FRMCLOSE,
					tabIndex: TABINDEX_EJHW + 50,
					handler: function() {
						this.hide();
					}.createDelegate(this)
				}]
			});
		sw.Promed.swJournalDoneProceduresWindow.superclass.initComponent.apply(this, arguments);
	},
	doSearch: function(mode){

		var win = this;

		if(!mode)mode = win.datePeriodToolbar.mode;
		var w = Ext.WindowMgr.getActive();

		if ( w.modal && !win.disableCheckModal) {
			return;
		}

		var params = {};

		win.FilterPanel.getForm().items.each(function(cmp){
			params[cmp.name] = cmp.getValue();
		})

		params.begDate = Ext.util.Format.date(win.datePeriodToolbar.dateMenu.getValue1(), 'd.m.Y');
		params.endDate = Ext.util.Format.date(win.datePeriodToolbar.dateMenu.getValue2(), 'd.m.Y');
		params.MedService_id = win.userMedStaffFact.MedService_id;
		params.wnd_id = 'swWorkPlaceProcCabinetWindow';

		this.GridPanel.removeAll({clearAll:true});
		this.GridPanel.loadData({globalFilters: params});
		/*
		this.emergencyTeamCombo.store.load({
			params: {
				begDate: params.begDate,
				endDate: params.endDate,
				LpuBuilding_id: params.LpuBuilding_id
			}
		})*/
	},

	doReset: function()
	{
		this.FilterPanel.getForm().reset();
		//this.FilterPanel.getForm().findField('Message_isRead').setValue(0);
		//this.setTitleFieldset();
		//this.GridPanel.getStore().baseParams = {};
	},

	stepDay: function(day)
	{
		var frm = this;
		var date1 = (frm.dateMenu.getValue1() || Date.parseDate(frm.curDate, 'd.m.Y')).add(Date.DAY, day).clearTime();
		var date2 = (frm.dateMenu.getValue2() || Date.parseDate(frm.curDate, 'd.m.Y')).add(Date.DAY, day).clearTime();
		frm.dateMenu.setValue(Ext.util.Format.date(date1, 'd.m.Y')+' - '+Ext.util.Format.date(date2, 'd.m.Y'));
		// frm.dateMenu.fireEvent("select", frm.dateMenu);
	},
	prevDay: function ()
	{
		this.stepDay(-1);
	},
	setActionDisabled: function(action, flag)
	{
		if (this.gridActions[action])
		{
			this.gridActions[action].initialConfig.initialDisabled = flag;
			this.gridActions[action].setDisabled(flag);
		}
	},
	scheduleCollapseDates: function() {
		this.getGrid().getView().collapseAllGroups();
	},
	scheduleExpandDates: function() {
		this.getGrid().getView().expandAllGroups();
	},
	nextDay: function ()
	{
		this.stepDay(1);
	},
	currentDay: function ()
	{
		var frm = this;
		var date1 = Date.parseDate(frm.curDate, 'd.m.Y');
		var date2 = Date.parseDate(frm.curDate, 'd.m.Y');
		frm.dateMenu.setValue(Ext.util.Format.date(date1, 'd.m.Y')+' - '+Ext.util.Format.date(date2, 'd.m.Y'));
		// frm.dateMenu.fireEvent("select", frm.dateMenu);
	},
	currentWeek: function ()
	{
		var frm = this;
		var date1 = (Date.parseDate(frm.curDate, 'd.m.Y'));
		var dayOfWeek = (date1.getDay() + 6) % 7;
		date1 = date1.add(Date.DAY, -dayOfWeek).clearTime();
		var date2 = date1.add(Date.DAY, 6).clearTime();
		frm.dateMenu.setValue(Ext.util.Format.date(date1, 'd.m.Y')+' - '+Ext.util.Format.date(date2, 'd.m.Y'));
		// frm.dateMenu.fireEvent("select", frm.dateMenu);
	},
	currentMonth: function ()
	{
		var frm = this;
		var date1 = (Date.parseDate(frm.curDate, 'd.m.Y')).getFirstDateOfMonth();
		var date2 = date1.getLastDateOfMonth();
		frm.dateMenu.setValue(Ext.util.Format.date(date1, 'd.m.Y')+' - '+Ext.util.Format.date(date2, 'd.m.Y'));
		// frm.dateMenu.fireEvent("select", frm.dateMenu);
	},
	openEvnFuncRequestEditWindow: function(action, is_time) {
		var form = this;
		if ( action != 'add' && action != 'edit' && action != 'view' ) {
			return false;
		}

		sw.Applets.commonReader.stopReaders();

		var swPersonSearchWindow = getWnd('swPersonSearchWindow');
		if ( action == 'add' && swPersonSearchWindow.isVisible() ) {
			sw.swMsg.alert(langs('Окно поиска человека уже открыто'), langs('Для продолжения необходимо закрыть окно поиска человека.'));
			return false;
		}

		var grid = this.GridPanel.getGrid();

		var params = new Object();

		params.MedService_id = this.MedService_id;

		params.action = action;
		params.callback = function(data) {};
		params.swWorkPlaceProcCabinetWindow = form;

		if ( action == 'add' ) {

			if ( grid.getSelectionModel().getSelected() && grid.getSelectionModel().getSelected().get('TimetableMedService_id') && is_time == true ) {
				var record = grid.getSelectionModel().getSelected();
				params.TimetableMedService_id = record.get('TimetableMedService_id');
			}

			swPersonSearchWindow.show({
				onClose: function() {
					sw.Applets.commonReader.startReaders({callback: this.getDataFromUec.createDelegate(this)});
					if ( grid.getSelectionModel().getSelected() ) {
						grid.getView().focusRow(grid.getStore().indexOf(grid.getSelectionModel().getSelected()));
					}
					else {
						//grid.getView().focusRow(0);
						grid.getSelectionModel().selectFirstRow();
					}
				}.createDelegate(this),
				onSelect: function(person_data) {
					params.Person_id = person_data.Person_id;
					params.PersonEvn_id = person_data.PersonEvn_id;
					params.Server_id = person_data.Server_id;
					params.swPersonSearchWindow = swPersonSearchWindow;
					params.callback = function () {
						form.GridPanel.refreshRecords(null, 0);
					};
					params.onHide = function() {
						if (swPersonSearchWindow.isVisible()) {
							//На форме поиска человека нет такого метода
							//sw.Applets.commonReader.startReaders({callback: this.getDataFromUec.createDelegate(this)});
						}
					}
					this.hide(); // закрываем форму поиска человека
					getWnd('swEvnProcRequestEditWindow').show(params);
				},
				searchMode: 'all',
				needUecIdentification: true
			});

		} else {

			if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('EvnDirection_id') ) {
				sw.swMsg.alert(langs('Ошибка'), langs('Не выбрана заявка или направление!'));
				return false;
			}

			var record = grid.getSelectionModel().getSelected();

			params.EvnFuncRequest_id = record.get('EvnFuncRequest_id');
			params.EvnDirection_id = record.get('EvnDirection_id');
			params.Person_id = record.get('Person_id');
			params.PersonEvn_id = record.get('PersonEvn_id');
			params.Server_id = record.get('Server_id');
			params.callback = function () {
				form.GridPanel.refreshRecords(null, 0);
			};
			params.onHide = function() {
				grid.getView().focusRow(grid.getStore().indexOf(record));
				grid.getSelectionModel().selectRow(grid.getStore().indexOf(record));

				sw.Applets.commonReader.startReaders({callback: this.getDataFromUec.createDelegate(this)});
			}

			getWnd('swEvnProcRequestEditWindow').show(params);

		}
	},
});