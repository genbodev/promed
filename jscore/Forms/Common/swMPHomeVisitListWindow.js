/**
 * swMPHomeVisitListWindow - форма "Журнал вызовов на дом для врача поликлиники"
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/projects/promed
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009-2013, Swan.
 * @author       Sabirov Kirill (ksabirov@swan.perm.ru)
 * @prefix       mphvlw
 * @version      October, 2013
 */
/*NO PARSE JSON*/
sw.Promed.swMPHomeVisitListWindow = Ext.extend(sw.Promed.BaseForm, {
	closable: true,
	closeAction: 'hide',
	maximized: true,
	title: WND_HVL,
	iconCls: 'workplace-mp16',
	id: 'swMPHomeVisitListWindow',
	readOnly: false,
        notifyArmNameList: ['АРМ врача поликлиники', 'АРМ оператора НМП'],
        callList: [],
        playNotification: function()
        {
            Ext.get('swMPHomeVisitListWindowNotification').dom.play();
        },
        
	HomeVisitRow: null,

	userMedStaffFact: null,
	/**
	 * Функция возврашающся ссылку на родительский элемент
	 */
	getOwner: null,

	/**
	 * Дата, на которую отображаются вызовы на дом
	 */
	date: null,

	/**
	 * Маска для загрузки
	 */
	loadMask: null,

	/**
	 * Данные человека
	 */
	personData: null,

	/**
	 * Загрузка вызовов на дом
	 *
	 * @param date Дата, на которую загружать вызовы на дом
	 */
	loadHomeVisits: function (mode) {

		var btn = this.getPeriodToggle(mode);
		if (btn) 
		{
			if (mode != 'range')
			{
				if (this.mode == mode)
				{
					btn.toggle(true);
					if (mode != 'day') // чтобы при повторном открытии тоже происходила загрузка списка на этот день
						return false;
				}
				else 
				{
					this.mode = mode;
				}
			}
			else 
			{
				btn.toggle(true);
				this.mode = mode;
			}
		}

		var params = new Object();
		params.begDate = Ext.util.Format.date(this.dateMenu.getValue1(), 'd.m.Y');
		params.endDate = Ext.util.Format.date(this.dateMenu.getValue2(), 'd.m.Y');
		params.limit = 100;
		params.start = 0;

		params.Person_Firname = this.findById('mphvlw_Search_FirName').getValue();
		params.Person_Secname = this.findById('mphvlw_Search_SecName').getValue();
		params.Person_Surname = this.findById('mphvlw_Search_SurName').getValue();
		params.Person_BirthDay = Ext.util.Format.date(this.findById('mphvlw_Search_BirthDay').getValue(), 'd.m.Y');
		params.HomeVisitStatus_id = this.findById('mphvlw_Search_HomeVisitStatus').getValue();
		params.HomeVisitCallType_id = this.findById('mphvlw_Search_HomeVisitCallType').getValue();
		params.MedPersonal_id = this.userMedStaffFact.MedPersonal_id;
		params.LpuRegion_cid = this.findById('mphvlw_Search_LpuRegion_id').getValue();

		this.getGrid().loadData({
			globalFilters: params
		});
	},
	/**
	 * Возвращает грид
	 */
	getGrid: function () {
		return this.HomeVisitsGrid;
	},
	getPeriodToggle: function (mode)
	{
		switch(mode)
		{
		case 'day':
			return this.DoctorToolbar.items.items[6];
			break;
		case 'week':
			return this.DoctorToolbar.items.items[7];
			break;
		case 'month':
			return this.DoctorToolbar.items.items[8];
			break;
		case 'range':
			return this.DoctorToolbar.items.items[9];
			break;
		default:
			return null;
			break;
		}
	},

	/**
	 * Открытие окна истории статусов
	 */
	openHomeVisitStatusHistWindow: function() {
		var record = this.HomeVisitsGrid.getGrid().getSelectionModel().getSelected();

		getWnd('swHomeVisitStatusHistWindow').show({
			HomeVisit_id: record.get('HomeVisit_id')
		});
	},

	openHomeVisitDenyWindow: function() {
		var wnd = this;
		var grid = wnd.HomeVisitsGrid.getGrid();
		var record = grid.getSelectionModel().getSelected();

		if (!record || Ext.isEmpty(record.get('HomeVisit_id')) || record.get('HomeVisitStatus_id') != 3) {
			return;
		}

		var params = {
			HomeVisit_id: record.get('HomeVisit_id'),
			callback: function(){wnd.loadHomeVisits()}
		};

		getWnd('swHomeVisitDenyWindow').show(params);
	},

	openHomeVisitCancelWindow: function() {
		var wnd = this;
		var grid = wnd.HomeVisitsGrid.getGrid();
		var record = grid.getSelectionModel().getSelected();

		if (!record || Ext.isEmpty(record.get('HomeVisit_id')) || record.get('HomeVisitStatus_id') == 5) {
			return;
		}

		var params = {
			HomeVisit_id: record.get('HomeVisit_id'),
			Person_Surname: record.get('Person_Surname'),
			Person_Firname: record.get('Person_Firname'),
			Person_Secname: record.get('Person_Secname'),
			Address_Address: record.get('Address_Address'),
			callback: function(){wnd.loadHomeVisits()},
			needLpuComment: true
		};

		getWnd('swHomeVisitCancelWindow').show(params);
	},

	updateStatus:function(status){
		var win = this;

		if (status == 2) {
			win.openHomeVisitDenyWindow();
			return;
		}
		if (status == 5) {
			win.openHomeVisitCancelWindow();
			return;
		}

		var url = '/?c=HomeVisit&m=confirmHomeVisit';
		if(status==1){
			var url = '/?c=HomeVisit&m=setStatusNew';
		}
		if(win.HomeVisitRow&&win.HomeVisitRow.get('HomeVisit_id')){
			Ext.Ajax.request({
				failure: function(response, options) {
					showSysMsg(langs('При загрузке сигнальный информации о диспансерном учете возникли ошибки'));
				},
				params: {
					HomeVisit_id:win.HomeVisitRow.get('HomeVisit_id')
				},
				success: function(response, options) {
					win.loadHomeVisits();

				},
				url: url
			});
		}
	},
	openEmk: function (openOnly) {
		if (!this.HomeVisitRow || !this.HomeVisitRow.get('HomeVisit_id')) {
			Ext.Msg.alert(langs('Ошибка'), langs('Не выбрана запись!'));
			return false;
		}

		var record = this.HomeVisitRow;
		var isMyOwnRecord = false;
		var allowHomeVisit = true;

		if (record.get('HomeVisitStatus_id') != 3 && record.get('HomeVisitStatus_id') != 4 && (getRegionNick() != 'kareliya' || !openOnly)) {
			this.updateStatus(3);
		} else if(record.get('HomeVisitStatus_id') == 4){
			allowHomeVisit = false;
		}

		var params = {
			Person_id: record.get('Person_id'),
			Server_id: record.get('Server_id'),
			PersonEvn_id: record.get('PersonEvn_id'),
			userMedStaffFact: this.userMedStaffFact,
			HomeVisit_id: record.get('HomeVisit_id'),
			allowHomeVisit: allowHomeVisit,
			mode: 'workplace',
			isMyOwnRecord: isMyOwnRecord,
			ARMType: this.userMedStaffFact.ARMType,
			callback: function () {
				this.loadHomeVisits();
			}.createDelegate(this)
		};

		if(record.get('HomeVisitStatus_id') != 4 && (getRegionNick() != 'kareliya' || !openOnly) ){
			params.onShow = function(form) {
				form.addNewEvnPLAndEvnVizitPL();
			}
		}

		getWnd('swPersonEmkWindow').show(params);
	},

	/**
	 * Перемещение по календарю
	 */
	stepDay: function (day) {
		var frm = this;
		var date1 = (frm.dateMenu.getValue1() || Date.parseDate(frm.curDate, 'd.m.Y')).add(Date.DAY, day).clearTime();
		var date2 = (frm.dateMenu.getValue2() || Date.parseDate(frm.curDate, 'd.m.Y')).add(Date.DAY, day).clearTime();
		frm.dateMenu.setValue(Ext.util.Format.date(date1, 'd.m.Y')+' - '+Ext.util.Format.date(date2, 'd.m.Y'));
	},

	/**
	 * На день назад
	 */
	prevDay: function () {
		this.stepDay(-1);
	},

	/**
	 * И на день вперед
	 */
	nextDay: function () {
		this.stepDay(1);
	},

	currentDay: function ()
	{
		var frm = this;
		var date1 = Date.parseDate(frm.curDate, 'd.m.Y');
		var date2 = Date.parseDate(frm.curDate, 'd.m.Y');
		frm.dateMenu.setValue(Ext.util.Format.date(date1, 'd.m.Y')+' - '+Ext.util.Format.date(date2, 'd.m.Y'));
	},
	currentWeek: function ()
	{
		var frm = this;
		var date1 = (Date.parseDate(frm.curDate, 'd.m.Y'));
		var dayOfWeek = (date1.getDay() + 6) % 7;
		date1 = date1.add(Date.DAY, -dayOfWeek).clearTime();
		var date2 = date1.add(Date.DAY, 6).clearTime();
    	frm.dateMenu.setValue(Ext.util.Format.date(date1, 'd.m.Y')+' - '+Ext.util.Format.date(date2, 'd.m.Y'));
	},
	currentMonth: function ()
	{
		var frm = this;
		var date1 = (Date.parseDate(frm.curDate, 'd.m.Y')).getFirstDateOfMonth();
		var date2 = date1.getLastDateOfMonth();
    	frm.dateMenu.setValue(Ext.util.Format.date(date1, 'd.m.Y')+' - '+Ext.util.Format.date(date2, 'd.m.Y'));
	},

	/**
	 * Маска при загрузке
	 */
	getLoadMask: function (MSG) {
		if (MSG) {
			delete(this.loadMask);
		}
		if (!this.loadMask) {
			this.loadMask = new Ext.LoadMask(this.getEl(), { msg: MSG });
		}

		return this.loadMask;
	},

	getCurrentDateTime: function() 
	{
		var frm = this;
		frm.getLoadMask(LOAD_WAIT).show();
		Ext.Ajax.request(
		{
			url: C_LOAD_CURTIME,
			callback: function(opt, success, response) 
			{
				if (success && response.responseText != '')
				{
					var result  = Ext.util.JSON.decode(response.responseText);
					frm.curDate = result.begDate;
					frm.curTime = result.begTime;
					frm.userName = result.pmUser_Name;
					frm.userName = result.pmUser_Name;
					// Проставляем время и режим
					this.mode = 'day';
					frm.currentDay();
					frm.loadHomeVisits('day');
					frm.getLoadMask().hide();
				}
			}
		});
	},

	initComponent: function () {

	var win = this;
		
		this.dateMenu = new Ext.form.DateRangeField(
		{
			width: 150,
			testId: 'wnd_workplace_dateMenu',
			fieldLabel: langs('Период'),
			plugins: 
			[
				new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)
			]
		});
		
		this.dateMenu.addListener('keydown',function (inp, e) 
		{
			var form = Ext.getCmp('swMPHomeVisitListWindow');
			if (e.getKey() == Ext.EventObject.ENTER)
			{
				e.stopEvent();
				form.loadHomeVisits('period');
			}
		});
		this.dateMenu.addListener('select',function () 
		{
			// Читаем расписание за период
			var form = Ext.getCmp('swMPHomeVisitListWindow');
			form.loadHomeVisits('period');
		});
		
		this.formActions = new Array();
		this.formActions.selectDate = new Ext.Action(
		{
			text: ''
		});
		this.formActions.prev = new Ext.Action(
		{
			text: langs('Предыдущий'),
			xtype: 'button',
			iconCls: 'arrow-previous16',
			handler: function()
			{
				// на один день назад
				this.prevDay();
				this.loadHomeVisits('range');
			}.createDelegate(this)
		});
		this.formActions.next = new Ext.Action(
		{
			text: langs('Следующий'),
			xtype: 'button',
			iconCls: 'arrow-next16',
			handler: function()
			{
				// на один день вперед
				this.nextDay();
				this.loadHomeVisits('range');
			}.createDelegate(this)
		});
		this.formActions.day = new Ext.Action(
		{
			text: langs('День'),
			xtype: 'button',
			toggleGroup: 'periodToggle',
			iconCls: 'datepicker-day16',
			pressed: true,
			handler: function()
			{
				this.currentDay();
				this.loadHomeVisits('day');
			}.createDelegate(this)
		});
		this.formActions.week = new Ext.Action(
		{
			text: langs('Неделя'),
			xtype: 'button',
			toggleGroup: 'periodToggle',
			iconCls: 'datepicker-week16',
			handler: function()
			{
				this.currentWeek();
				this.loadHomeVisits('week');
			}.createDelegate(this)
		});
		this.formActions.month = new Ext.Action(
		{
			text: langs('Месяц'),
			xtype: 'button',
			toggleGroup: 'periodToggle',
			iconCls: 'datepicker-month16',
			handler: function()
			{
				this.currentMonth();
				this.loadHomeVisits('month');
			}.createDelegate(this)
		});
		this.formActions.range = new Ext.Action(
		{
			text: langs('Период'),
			disabled: true,
			xtype: 'button',
			toggleGroup: 'periodToggle',
			iconCls: 'datepicker-range16',
			handler: function()
			{
				this.loadHomeVisits('range');
			}.createDelegate(this)
		});

		this.DoctorToolbar = new Ext.Toolbar(
		{
			items: 
			[
				this.formActions.prev, 
				{
					xtype : "tbseparator"
				},
				this.dateMenu,
				{
					xtype : "tbseparator"
				},
				this.formActions.next, 
				{
					xtype: 'tbfill'
				},
				this.formActions.day, 
				this.formActions.week, 
				this.formActions.month,
				this.formActions.range
			]
		});

		this.filtersPanel = new Ext.FormPanel({
			xtype: 'form',
			labelAlign: 'right',
			labelWidth: 50,
			items: [
				{
					listeners: {
						collapse: function (p) {
							this.doLayout();
						}.createDelegate(this),
						expand: function (p) {
							this.doLayout();
						}.createDelegate(this)
					},
					xtype: 'fieldset',
					style: 'margin: 5px 0 0 0',
					height: 110,
					title: langs('Поиск'),
					collapsible: true,
					layout: 'column',
					items: [
						{
							layout: 'form',
							labelWidth: 55,
							items: [
								{
									xtype: 'textfieldpmw',
									width: 120,
									id: 'mphvlw_Search_SurName',
									fieldLabel: langs('Фамилия'),
									listeners: {
										'keydown': function (inp, e) {
											if (e.getKey() == Ext.EventObject.ENTER) {
												e.stopEvent();
												this.loadHomeVisits();
											}
										}.createDelegate(this)
									}
								}, 
								{
									xtype: 'textfieldpmw',
									width: 120,
									id: 'mphvlw_Search_FirName',
									fieldLabel: langs('Имя'),
									listeners: {
										'keydown': function (inp, e) {
											if (e.getKey() == Ext.EventObject.ENTER) {
												e.stopEvent();
												this.loadHomeVisits();
											}
										}.createDelegate(this)
									}
								}, 
								{
									xtype: 'textfieldpmw',
									width: 120,
									id: 'mphvlw_Search_SecName',
									fieldLabel: langs('Отчество'),
									listeners: {
										'keydown': function (inp, e) {
											if (e.getKey() == Ext.EventObject.ENTER) {
												e.stopEvent();
												this.loadHomeVisits();
											}
										}.createDelegate(this)
									}
								}
							]
						},
						{
							layout: 'form',
							labelWidth: 110,
							items: [
								{
									xtype: 'swdatefield',
									format: 'd.m.Y',
									plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
									id: 'mphvlw_Search_BirthDay',
									fieldLabel: langs('Дата рождения'),
									listeners: {
										'keydown': function (inp, e) {
											if (e.getKey() == Ext.EventObject.ENTER) {
												e.stopEvent();
												this.loadHomeVisits();
											}
										}.createDelegate(this)
									}
								}, 
								{
									hiddenName: 'HomeVisitStatus_id',
									lastQuery: '',
									xtype: 'swhomevisitstatuscombo',
									id: 'mphvlw_Search_HomeVisitStatus',
									listeners: {
										'keydown': function (inp, e) {
											if (e.getKey() == Ext.EventObject.ENTER) {
												e.stopEvent();
												this.loadHomeVisits();
											}
										}.createDelegate(this)
									}
								}, 
								{
									id: 'mphvlw_Search_HomeVisitCallType',
									comboSubject: 'HomeVisitCallType',
									fieldLabel: langs('Тип вызова'),
									hiddenName: 'HomeVisitCallType_id',
									valueField: 'HomeVisitCallType_id',
									width: 188,
									tpl: '<tpl for="."><div class="x-combo-list-item">{HomeVisitCallType_Name}&nbsp;</div></tpl>',
									xtype: 'swcommonsprcombo'
								}
							]
						},
						{
							layout: 'form',
							labelWidth: 110,
							items: [
								new sw.Promed.SwBaseRemoteCombo({
									displayField: 'LpuRegion_Name',
									editable: false,
									enableKeyEvents: true,
									forceSelection: true,
									fieldLabel: langs('Участок вызова'),
									hiddenName: 'LpuRegion_cid',
									id: 'mphvlw_Search_LpuRegion_id',
									queryDelay: 1,
									lastQuery: '',
									mode: 'remote',
									store: new Ext.data.Store({
										autoLoad: false,
										reader: new Ext.data.JsonReader({
											id: 'LpuRegion_id'
										},
										[
											{name: 'LpuRegion_Name', mapping: 'LpuRegion_Name'},
											{name: 'LpuRegion_id', mapping: 'LpuRegion_id'},
											{name: 'LpuRegion_Descr', mapping: 'LpuRegion_Descr'},
											{name: 'LpuRegionType_id', mapping: 'LpuRegionType_id'},
											{name: 'LpuRegionType_SysNick', mapping: 'LpuRegionType_SysNick'},
											{name: 'LpuRegionType_Name', mapping: 'LpuRegionType_Name'}
										]),
										listeners: {
											'load': function(store) {
												
											}.createDelegate(this)
										},
										url: C_LPUREGION_LIST
									}),
								
									tpl: '<tpl for="."><div class="x-combo-list-item">{LpuRegionType_Name} {LpuRegion_Name}</div></tpl>',
									triggerAction: 'all',
									valueField: 'LpuRegion_id',
									width: 220,
									xtype: 'swbaseremotecombo',
									onTrigger2Click: function() {
										this.clearValue();
									},
									trigger2Class: 'x-form-clear-trigger'
								})
							]
						},
						{
							layout: 'form',
							items: [
								{
									style: "padding-left: 20px",
									xtype: 'button',
									id: 'mpwpBtnSearch',
									text: langs('Найти'),
									iconCls: 'search16',
									handler: function () {
										this.loadHomeVisits();
									}.createDelegate(this)
								}
							]
						},
						{
							layout: 'form',
							items: [
								{
									style: "padding-left: 20px",
									xtype: 'button',
									id: 'mphvlw_BtnClear',
									text: langs('Сброс'),
									iconCls: 'resetsearch16',
									handler: function () {
										this.findById('mphvlw_Search_SurName').setValue(null);
										this.findById('mphvlw_Search_FirName').setValue(null);
										this.findById('mphvlw_Search_SecName').setValue(null);
										this.findById('mphvlw_Search_BirthDay').setValue(null);
										this.loadHomeVisits();
									}.createDelegate(this)
								}
							]
						}
					]
				}
			]
		});

		this.TopPanel = new Ext.Panel(
			{
				region: 'north',
				frame: true,
				border: false,
				autoHeight: true,
				tbar: this.DoctorToolbar,
				items: [
					this.filtersPanel
				]
			});

		this.printObject = new Ext.Action({name: 'printObject', text: langs('Печать'), handler: function(){win.HomeVisitsGrid.printObject()}});
		this.printObjectList = new Ext.Action({name: 'printObjectList', text: langs('Печать текущей страницы'), handler: function(){win.HomeVisitsGrid.printObjectList()}});
		this.printObjectListFull = new Ext.Action({name: 'printObjectListFull', text: langs('Печать всего списка'), handler: function(){win.HomeVisitsGrid.printObjectListFull()}});
		this.printBook = new Ext.Action({name: 'printBook',text:langs('Печать книги записи вызовов на дом'),handler: function(){getWnd('swHomeVisitBookPrintParamsWindow').show({ARMType:'polka'});}});

		this.HomeVisitsGrid = new sw.Promed.ViewFrame({
                        html: '<audio id="swMPHomeVisitListWindowNotification"><source src="/audio/web/WavLibraryNet_Sound5825.mp3" type="audio/mpeg"></audio>',
			actions: [
				{name: 'action_add',
					handler: function() {
						getWnd('swPersonSearchWindow').show({
							onSelect: function(personData) {
								if ( personData.Person_id > 0 ) {
									getWnd('swHomeVisitAddWindow').show({
										Person_id: personData.Person_id,
										Server_id: personData.Server_id,
										action:'add',
										Lpu_id: getGlobalOptions().lpu_id,
										callback : function() {
											this.loadHomeVisits();
										}.createDelegate(this)
									});
								}
								getWnd('swPersonSearchWindow').hide();
							}.createDelegate(this)
						});
						
					}.createDelegate(this)
				},
				{name: 'action_edit', disabled: false, text: langs('Открыть ЭМК'),
					handler: function () {
						this.openEmk(true);
					}.createDelegate(this)
				},
				{name: 'action_delete', disabled: true, hidden: true},
				{name: 'action_view',text:langs('Сменить статус'), hidden:true, disabled: true, menu: new Ext.menu.Menu({
					items: [
						{name: 'action_new',text:langs('Новый'),handler:function(){win.updateStatus(1)}},
						{name: 'action_confirm',text:langs('Одобрен'),handler:function(){win.updateStatus(3)}},
						{name: 'action_cancel',text:'Отменен',handler:function(){win.updateStatus(5)}},
						{name: 'action_deny',text:'Отказ',handler:function(){win.updateStatus(2)}}
					]
				})},
				{name: 'action_print',text:langs('Печать'), menu: new Ext.menu.Menu({
					items: [
						win.printObject,
						win.printObjectList,
						win.printObjectListFull,
						win.printBook
					]
				})}
			],
			grouping: true,
            useEmptyRecord: false,
			groupingView: {showGroupName: false, showGroupsText: true},
			groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length == 1 ? "запись": ( values.rs.length.inlist([2,3,4]) ? "записи" : "записей")]})',
			autoExpandColumn: 'autoexpand',
			autoLoadData: false,
			dataUrl: C_HOMEVISIT_LIST,
			//stateful: true,
			id: 'MPHomeVisitGrid',
			onDblClick: function () {
				this.onEnter();
			},
			onEnter: function () {
				this.openEmk(false);
			}.createDelegate(this),
			onLoadData: function (sm, index, record) {
				var grid = this.getGrid(),
					store = grid.getStore(),
					action = win.HomeVisitsGrid.ViewActions.action_print,
					count = store.getCount(),
					totalCount = store.getTotalCount(),
					overLimit = store.overLimit;
				win.printObject.setDisabled(!(store.totalLength > 0 || overLimit == true));
				win.printObjectList.setDisabled(!(store.totalLength > 0 || overLimit == true));
				win.printObjectListFull.setDisabled(!(store.totalLength > 0 || overLimit == true));
				if (store.totalLength > 0 || overLimit == true) {
					//Если загружена всего одна страница, то запрещаем "Печать текущей страницы"
					if (!this.paging) {
						win.printObjectList.setDisabled(true);
					} else {
						if (count < totalCount || overLimit == true) {
							win.printObjectList.setDisabled(false);
						} else {
							win.printObjectList.setDisabled(true);
						}
					}
					//win.HomeVisitsGrid.restoreActionMenu(action);
				}
				else{
					this.ViewActions.action_print.setDisabled(false);
				}

				store.each(function(rec,idx,count) {
					if (!Ext.isEmpty(rec.get('HomeVisitStatus_id'))) {
						var hvsHref = rec.get('HomeVisitStatus_Name');
						hvsHref = '<a href="javascript://" onClick="Ext.getCmp(\''+win.id+'\').openHomeVisitStatusHistWindow()">'+rec.get('HomeVisitStatus_Name')+'</a>';
						rec.set('HomeVisitStatus_Name', hvsHref);

						var HVisQ = rec.get('HomeVisit_isQuarantine');
						if (1*HVisQ == 2) {
							rec.set('HomeVisit_isQuarantine', 'Да');
						} else {
							rec.set('HomeVisit_isQuarantine', 'Нет');
						}

						rec.commit();
					}
				}.createDelegate(this));
                                
                                // #157110 Звуковое оповещение пользователя о событии в системе
                                if (getRegionNick() == 'ufa' && win.notifyArmNameList.includes(win.userMedStaffFact.ARMName))
                                {
                                    var grid = this.getGrid();
                                    grid.getStore().each(function(rec)
                                    {
                                        var HomeVisit_id = rec.get('HomeVisit_id');
                                        if (HomeVisit_id && !win.callList.includes(HomeVisit_id))
                                        {
                                            win.playNotification();
                                            win.callList.push(HomeVisit_id);
                                        }
                                    });
                                    grid.lastLoadGridDate = new Date();
                                    if(grid.auto_refresh)
                                    {
                                            clearInterval(grid.auto_refresh);
                                    }
                                    grid.auto_refresh = setInterval(
                                        function()
                                        {
                                            var cur_date = new Date();
                                            // если прошло более 2 минут с момента последнего обновления
                                            if(grid.lastLoadGridDate.getTime() < (cur_date.getTime()-120))
                                            {
                                                grid.getStore().reload();
                                            }
                                        }.createDelegate(grid),
                                        120000
                                    );
                                }
			},
			onRowSelect: function (sm, index, record) {
				var home_visit_id = record.get('HomeVisit_id');
				var home_visit_status_id = record.get('HomeVisitStatus_id');
				if(!Ext.isEmpty(home_visit_status_id) && home_visit_status_id.inlist([6,3])){
					this.HomeVisitsGrid.ViewActions.action_status.setDisabled(false);
					this.HomeVisitsGrid.getAction('action_status').items[0].menu.items.items[1].setDisabled(home_visit_status_id==3);
					this.HomeVisitsGrid.getAction('action_status').items[0].menu.items.items[2].setDisabled(home_visit_status_id==3);
					this.HomeVisitsGrid.getAction('action_status').items[0].menu.items.items[3].setDisabled(home_visit_status_id!=3);
				}else{
					this.HomeVisitsGrid.ViewActions.action_status.setDisabled(true);
				}
				if (home_visit_id > 0){
					this.HomeVisitRow = record;
					this.HomeVisitsGrid.ViewActions.action_edit.setDisabled(false);
				} else {
					this.HomeVisitRow = null;
					this.HomeVisitsGrid.ViewActions.action_edit.setDisabled(true);
				}
			}.createDelegate(this),
			region: 'center',
			stringfields: [
				// Поля для отображение в гриде
				{ name: 'HomeVisit_id', type: 'int', header: 'ID', key: true },
				{ name: 'HomeVisit_Num', type: 'string', header: langs('Номер вызова'), width: 100 },
				{ name: 'Person_id', type: 'int', hidden: true },
				{ name: 'Server_id', type: 'int', hidden: true },
				{ name: 'PersonEvn_id', type: 'int', hidden: true },
				{ name: 'Person_Surname', type: 'string', header: langs('Фамилия'), width: 100 },
				{ name: 'Person_Firname', type: 'string', header: langs('Имя'), width: 100 },
				{ name: 'Person_Secname', type: 'string', header: langs('Отчество'), width: 100 },
				{ name: 'Person_Birthday', type: 'date', header: langs('Дата рождения'), renderer: Ext.util.Format.dateRenderer('d.m.Y') },
				{ name: 'Person_Age', type: 'int', header: langs('Возраст'), width: 50 },
				{ name: 'Address_Address', type: 'string', header: langs('Место вызова'), width: 320, id: 'autoexpand' },
				{ name: 'HomeVisit_Phone', type: 'string', header: langs('Телефон'), width: 80},
				{ name: 'HomeVisitWhoCall_Name', type: 'string', header: langs('Кто'), width: 60},
				{ name: 'CallProfType_Name', type: 'string', header: langs('Профиль вызова'), width: 200},
				{ name: 'HomeVisit_Symptoms', type: 'string', header: langs('Повод'), width: 300},
				{ name: 'LpuRegion_id', type: 'int', hidden: true },
				{ name: 'LpuRegionAttach', type: 'string', header: langs('Участок прикрепления'), width: 140 },
				{ name: 'LpuRegion_Name', type: 'string', header: langs('Участок вызова'), width: 120 },
				{ name: 'LpuBuilding_Name', type: 'string', header: langs('Подразделение'), width: 200 },
				{ name: 'MedPersonal_FIO', type: 'string', header: langs('Врач'), width: 200, hidden:true },
				{ name: 'MedStaff_Comp', type: 'string', header: langs('Врач'), width: 400 },
				{ name: 'HomeVisitCallType_Name', type: 'string', header: langs('Тип вызова'), width: 200 },
				{ name: 'HomeVisit_setDate', type: 'date', header: langs('Дата вызова'), width: 100 },
				{ name: 'HomeVisit_setTime', type: 'string', header: langs('Время вызова'), width: 100},
				{ name: 'HomeVisitStatus_Name', type: 'string', header: langs('Статус вызова'), width: 200 },
				{ name: 'HomeVisitStatus_Nameg', type: 'string', header: langs('Статус'), hidden: true, group: true, sort: true, direction: 'ASC' },
				{ name: 'HomeVisitStatus_id', type: 'int', hidden: true },
				{ name: 'HomeVisit_Comment', type: 'string', header: langs('Дополнительно'), width: 200},
				{ name: 'HomeVisit_LpuComment', type: 'string', header: langs('Комментарий ЛПУ'), width: 200},
				{ name: 'CmpCallCard_Ngod', type: 'string', header: langs('Номер карты СМП'), width: 200},
				{ name: 'HomeVisitStatusHist_setDT', type: 'date', header: langs('Дата передачи вызова'), width: 200}, //new
				{ name: 'HomeVisit_isQuarantine', type: 'string', header: langs('Карантин'), width: 100}
			],
			title: null,
			paging: true,
			pageSize: 100,
			root: 'data',
			totalProperty: 'totalCount'
		});
		this.HomeVisitsGrid.getGrid().on('keypress', this.onkeypress);
		this.HomeVisitsGrid.getGrid().keys = {
			key: 188,
			ctrl: true,
			handler: function () {
				curWnd.doReset();
				curWnd.FilterPanel.getForm().findField('Person_Surname').focus(1);
			}
		};

		Ext.apply(this, {
			autoScroll: true,
			buttons: [
				{
					text: '-'
				},
				{
					text: BTN_FRMHELP,
					iconCls: 'help16',
					handler: function (button, event) {
						ShowHelp(WND_HVL);
					}.createDelegate(this),
					tabIndex: TABINDEX_MPSCHED + 98
				},
				{
					iconCls: 'cancel16',
					text: BTN_FRMCLOSE,
					handler: function () {
						this.hide();
					}.createDelegate(this)
				}
			],
			layout: 'border',
			items: [
				this.TopPanel,
				{
					layout: 'border',
					region: 'center',
					id: 'mphvlw_MainPanel',
					items: [
						this.HomeVisitsGrid
					]
				}
			],
			keys: [
				{
					key: [
						Ext.EventObject.F5,
						Ext.EventObject.F9
					],
					fn: function (inp, e) {
						e.stopEvent();
						if (e.browserEvent.stopPropagation)
							e.browserEvent.stopPropagation();
						else
							e.browserEvent.cancelBubble = true;

						if (e.browserEvent.preventDefault)
							e.browserEvent.preventDefault();
						else
							e.browserEvent.returnValue = false;

						e.browserEvent.returnValue = false;
						e.returnValue = false;

						if (Ext.isIE) {
							e.browserEvent.keyCode = 0;
							e.browserEvent.which = 0;
						}

						switch (e.getKey()) {
							case Ext.EventObject.F5:
								this.loadHomeVisits();
								break;
						}
					},
					scope: this,
					stopEvent: false
				}
			]
		});
		sw.Promed.swMPHomeVisitListWindow.superclass.initComponent.apply(this, arguments);

	},

	show: function () {
		sw.Promed.swMPHomeVisitListWindow.superclass.show.apply(this, arguments);

		if (arguments[0] && arguments[0].userMedStaffFact) {
			this.userMedStaffFact = arguments[0].userMedStaffFact
		}
		
		this.filtersPanel.getForm().reset();
		this.filtersPanel.getForm().findField('HomeVisitStatus_id').getStore().loadData([{
			'HomeVisitStatus_id': -1,
			'HomeVisitStatus_Code': -1,
			'HomeVisitStatus_Name': langs('Актив из СМП')
		}], true);
		this.filtersPanel.getForm().findField('HomeVisitStatus_id').getStore().sort('HomeVisitStatus_Code', 'ASC');	
		var win = this;
		this.HomeVisitsGrid.addActions({
			name:'action_status',
			text:langs('Сменить статус'),
			iconCls: 'view16',
			menu: [
				{name: 'action_newS',text:langs('Новый'),handler:function(){win.updateStatus(1)}},
				{name: 'action_confirmS',text:langs('Одобрен'),handler:function(){win.updateStatus(3)}},
				{name: 'action_cancelS',text:'Отменен',handler:function(){win.updateStatus(5)}},
				{name: 'action_denyS',text:'Отказ',handler:function(){win.updateStatus(2)}}
			]
		}, 2);
		var RegionalTypeList = Ext.util.JSON.encode(['ter','ped','vop','op','stom']);
		if(getRegionNick() == 'kz') RegionalTypeList = Ext.util.JSON.encode(['ter','ped','vop','op','stom','pmsp']);
		this.findById('mphvlw_Search_LpuRegion_id').getStore().load({params:{LpuRegionTypeList:RegionalTypeList, showOpenerOnlyLpuRegions:1}});
		
		this.getCurrentDateTime();
		this.loadHomeVisits();
	}
});

