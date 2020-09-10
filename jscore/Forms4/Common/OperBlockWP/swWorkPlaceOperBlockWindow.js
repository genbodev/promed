/**
 * swWorkPlaceOperBlockWindow - АРМ сотрудника оперблока
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2018 Swan Ltd.
 */
Ext6.define('common.OperBlockWP.swWorkPlaceOperBlockWindow', {
	require: [
		'Ext6.data.proxy.Rest',
		'Extensible.calendar.data.MemoryCalendarStore',
		'Extensible.calendar.data.EventStore',
		'Extensible.calendar.CalendarPanel'
	],
	extend: 'base.BaseForm',
	alias: 'widget.swWorkPlaceOperBlockWindow',
    autoShow: false,
	maximized: true,
	width: 1000,
	refId: 'operblockwp',
	findWindow: false,
	closable: true,
	cls: 'arm-window-new',
	title: 'АРМ сотрудника оперблока',
    header: true,
	renderTo: Ext.getCmp('main-center-panel').body.dom,
	callback: Ext6.emptyFn,
	layout: 'border',
	constrain: true,
	onRecordSelect: function() {
		var win = this;

		win.mainPanel.down('#emkbutton').disable();
		win.mainPanel.down('#planbutton').disable();
		win.mainPanel.down('#cancelbutton').disable();
		win.mainPanel.down('#declinebutton').disable();
		win.mainPanel.down('#cancelexecbutton').disable();
		win.mainPanel.down('#resultbutton').disable();
		win.mainPanel.down('#urgentbutton').disable();
		win.mainPanel.down('#form008u').disable();
		win.mainPanel.down('#action_patomorf').disable();

		if (isUserGroup('operblock_head')) {
			win.mainPanel.down('#urgentbutton').enable();
		}

		if (this.mainGrid.getSelectionModel().hasSelection()) {
			var record = this.mainGrid.getSelectionModel().getSelection()[0];

			win.mainPanel.down('#emkbutton').enable();

			if (isUserGroup('operblock_head') && Ext6.isEmpty(record.get('EvnUslugaOper_setDT'))) {
				win.mainPanel.down('#planbutton').enable();
			}

			if (record.get('IsPlanned') == 2) {
				// если распределён, то доступна отмена, выполнение
				if (isUserGroup('operblock_head') && Ext6.isEmpty(record.get('EvnUslugaOper_setDT'))) { // Для распределенных, но не выполненных
					win.mainPanel.down('#cancelbutton').enable();
				}

				if (isUserGroup('operblock_head') && !Ext6.isEmpty(record.get('EvnUslugaOper_setDT'))) { // Для выполненных
					win.mainPanel.down('#cancelexecbutton').enable();
				}

				if (!Ext6.isEmpty(record.get('EvnUslugaOper_setDT'))) { // Для выполненных
					win.mainPanel.down('#form008u').enable();
					win.mainPanel.down('#action_patomorf').enable();
				}

				if (isUserGroup('operblock_head') || isUserGroup('operblock_surg')) {
					win.mainPanel.down('#resultbutton').enable();
				}
			} else {
				if (isUserGroup('operblock_head')) { // Для заявок группы «Очередь»
					win.mainPanel.down('#declinebutton').enable();
				}
			}
		}
	},
	destroyCalendarEvents: function(EvnDirection_id) {
		var win = this;

		win.mask('Отмена операции...');
		Ext6.Ajax.request({
			params: {
				EvnDirection_id: EvnDirection_id
			},
			url: '/?c=OperBlock&m=destroyCalendarEvents',
			callback: function(opt, success, response) {
				win.unmask();
				if (success && response.responseText != '') {
					// обновляем гриды
					win.doFilter();
				}
			}
		});
	},
	cancelPlan: function() {
		var win = this;

		if (this.mainGrid.getSelectionModel().hasSelection()) {
			var record = this.mainGrid.getSelectionModel().getSelection()[0];

			if (typeof record != 'object' || Ext6.isEmpty(record.get('TimetableResource_id'))) {
				return false;
			}

			// задаём вопрос
			Ext6.Msg.show({
				title: 'Отменить',
				msg: 'Вы действительно хотите отменить операцию?',
				buttons: Ext6.Msg.YESNO,
				icon: Ext6.Msg.QUESTION,
				fn: function(btn) {
					if (btn === 'yes') {
						win.destroyCalendarEvents(record.get('EvnDirection_id'));
					}
				}
			});

			return true;
		}
	},
	openEvnDirectionHistologicEditWindow: function () {
		var win = this;

		if (this.mainGrid.getSelectionModel().hasSelection()) {
			var record = this.mainGrid.getSelectionModel().getSelection()[0];

			if (typeof record != 'object' || Ext6.isEmpty(record.get('EvnDirection_id'))) {
				return false;
			}

			var params = {};
			params.action = 'add';
			params.formParams = {};
			params.onHide = function () {
				//
			};
			params.ZNOinfo = 1;
			params.formParams.EvnDirection_pid = record.get('EvnDirection_id');
			params.formParams.DopDispInfoConsent_id = null;
			params.formParams.Diag_id = record.get('Diag_id');
			params.formParams.DirType_id = 7;
			params.formParams.MedService_id = win.userMedStaffFact.MedService_id;
			params.formParams.MedStaffFact_id = record.get('MedStaffFact_id');
			params.formParams.MedPersonal_id = record.get('MedPersonal_id');
			params.formParams.LpuSection_id = record.get('LpuSection_id');
			params.formParams.ARMType_id = win.userMedStaffFact.ARMType_id;
			params.formParams.Lpu_sid = getGlobalOptions().lpu_id;
			params.formParams.withDirection = true;
			params.formParams.EvnDirectionHistologic_pid = record.get('EvnDirection_id');

			params.formParams.Person_id = record.get('Person_id');
			params.formParams.PersonEvn_id = record.get('PersonEvn_id');
			params.formParams.Server_id = record.get('Server_id');

			getWnd('swEvnDirectionHistologicEditWindow').show(params);

			return true;
		}
	},
	decline: function() {
		var win = this;

		if (this.mainGrid.getSelectionModel().hasSelection()) {
			var record = this.mainGrid.getSelectionModel().getSelection()[0];

			if (typeof record != 'object' || Ext6.isEmpty(record.get('EvnDirection_id'))) {
				return false;
			}
			
			return sw.Promed.Direction.cancel({
				cancelType: 'cancel',
				ownerWindow: this,
				userMedStaffFact: this.userMedStaffFact,
				EvnDirection_id: record.get('EvnDirection_id'),
				callback: function (cfg) {
					win.doFilter();
				}
			});
		}
	},
	createUrgentRequest: function() {
		// открываем поиск человека с фильтром по открытым КВС в данном МО
		var win = this;

		if (getWnd('swPersonSearchWindow').isVisible())
		{
			Ext6.Msg.alert('Сообщение', 'Окно поиска человека уже открыто');
			return false;
		}

		getWnd('swPersonSearchWindow').show({
			onClose: function() {
			},
			onSelect: function(person_data) {
				// прячем форму
				getWnd('swPersonSearchWindow').hide();

				// создаём новую заявку
				win.mask('Создание заявки на экстренную операцию...');
				Ext6.Ajax.request({
					params: {
						Person_id: person_data.Person_id,
						PersonEvn_id: person_data.PersonEvn_id,
						Server_id: person_data.Server_id,
						MedService_id: win.MedService_id,
						LpuSection_id: win.userMedStaffFact.LpuSection_id,
						MedPersonal_id: win.userMedStaffFact.MedPersonal_id
					},
					url: '/?c=OperBlock&m=createUrgentRequest',
					callback: function(opt, success, response) {
						win.unmask();
						if (success && response.responseText != '') {
							// обновляем гриды
							win.doFilter();
						}
					}
				});
			},
			searchMode: 'hasopenevnps'
		});
	},
	openPersonEmkWindow: function() {
		var win = this;
		if (this.mainGrid.getSelectionModel().hasSelection()) {
			var record = this.mainGrid.getSelectionModel().getSelection()[0];

			if (typeof record != 'object' || Ext6.isEmpty(record.get('Person_id'))) {
				return false;
			}
			
			var searchNodeObj = {};
			if (!Ext6.isEmpty(record.get('EvnSection_id'))) {
				searchNodeObj = {
					parentNodeId: 'EvnPS_' + record.get('EvnSection_pid'),
					last_child: false,
					disableLoadViewForm: false,
					EvnClass_SysNick: 'EvnSection',
					Evn_id: record.get('EvnSection_id')
				};
			}
			else if (!Ext6.isEmpty(record.get('EvnVizitPL_id'))) {
				searchNodeObj = {
					parentNodeId: 'EvnPL_' + record.get('EvnVizitPL_pid'),
					last_child: false,
					disableLoadViewForm: false,
					EvnClass_SysNick: 'EvnVizitPL',
					Evn_id: record.get('EvnVizitPL_id')
				};
			}

			getWnd('swPersonEmkWindow').show({
				Person_id: record.get('Person_id'),
				Server_id: record.get('Server_id'),
				PersonEvn_id: record.get('PersonEvn_id'),
				userMedStaffFact: win.userMedStaffFact,
				MedStaffFact_id: win.userMedStaffFact.MedStaffFact_id,
				LpuSection_id: win.userMedStaffFact.LpuSection_id,
				readOnly: true,
				ARMType: 'common',
				searchNodeObj: searchNodeObj
			});

			return true;
		}
	},
	openResultWindow: function() {
		var win = this;

		if (this.mainGrid.getSelectionModel().hasSelection()) {
			var record = this.mainGrid.getSelectionModel().getSelection()[0];

			if (typeof record != 'object' || Ext6.isEmpty(record.get('EvnUslugaOper_id')) || record.get('IsPlanned') != 2) {
				return false;
			}

			win.showEvnUslugaOperEditWindow({
				EvnUslugaOper_id: record.get('EvnUslugaOper_id')
			});

			return true;
		}
	},
	openPlanWindow: function() {
		var win = this;

		if (this.mainGrid.getSelectionModel().hasSelection()) {
			var record = this.mainGrid.getSelectionModel().getSelection()[0];

			if (typeof record != 'object' || Ext6.isEmpty(record.get('EvnDirection_id'))) {
				return false;
			}

			getWnd('swEvnPrescrOperBlockPlanWindow').show({
				EvnDirection_id: record.get('EvnDirection_id'),
				callback: function() {
					// обновить гриды
					win.doFilter();
				}
			});

			return true;
		}
	},
	checkConflicts: function() {
		var win = this;

		// todo надо думать над оптимизацией, т.к. на больших объемах заявок может начать вешать браузер.
		// убираем конфликты
		win.eventStore.each(function (rec1) {
			var brig1 = rec1.get('OperBrig');
			for (var k1 in brig1) {
				brig1[k1].conflicted = false;
			}
			rec1.set('conflicted', false);
			rec1.set('OperBrig', brig1);
			rec1.commit();
		});

		// ставим конфликты
		win.eventStore.each(function (rec1) {
			win.eventStore.each(function (rec2) {
				// если пересекаются по времени
				if (rec1.get('EvnDirection_id') != rec2.get('EvnDirection_id') && rec2.get('StartDate') < rec1.get('EndDate') && rec2.get('EndDate') > rec1.get('StartDate')) {
					// находим пересечения врачей, у кого нашли того подсвечиваем.
					var conflicted = false;
					var brig1 = rec1.get('OperBrig');
					var brig2 = rec2.get('OperBrig');

					for (var k1 in brig1) {
						for (var k2 in brig2) {
							if (brig2[k2].MedPersonal_id == brig1[k1].MedPersonal_id) {
								brig1[k1].conflicted = true;
								brig2[k2].conflicted = true;
								conflicted = true;
							}
						}
					}

					rec1.set('conflicted', conflicted);
					rec2.set('conflicted', conflicted);
					rec1.set('OperBrig', brig1);
					rec2.set('OperBrig', brig2);
					rec1.commit();
					rec2.commit();
				}
			});
		});


		// Проверка выше проверяет пересечения только в дместномраг&дропе , без учета операций в других отделениях
		//
		// Здесь проверяем, существуют ли пересечения в расписании медработников с операциями в других отделениях
		win.eventStore.each(function (rec) {
			var brig = rec.get('OperBrig'),
				conflicted = false;

			for (var key in brig)
			{
				if (brig[key].hasConflict == 1)
				{
					brig[key].conflicted = true;
					conflicted = true;
				}
			}

			rec.set('conflicted', conflicted);
			rec.set('OperBrig', brig);
		});

		win.schedulePanel.activeView.refresh();
	},
	setHourIncrement: function(time, menuItem) {
		var win = this;
		var scrollAfter = Math.round(win.schedulePanel.activeView.body.getEl().getScroll().top * win.schedulePanel.activeView.body.hourIncrement / (time * 2)); // чтобы открытый первый час оставался тот же в графике, иначе всё улетает не понятно куда :)
		win.schedulePanel.activeView.body.hourIncrement = time * 2; // обновляем количество минут в разделе (делится пополам при отображении)
		win.schedulePanel.activeView.body.tpl.hourIncrement = time * 2;
		win.schedulePanel.activeView.body.viewEndHour = 24 * 30 / time; // обновляем количество отображаемых разделов
		win.schedulePanel.activeView.body.tpl.viewEndHour = 24 * 30 / time;
		win.schedulePanel.activeView.body.incrementsPerHour = win.schedulePanel.activeView.body.hourIncrement / win.schedulePanel.activeView.body.ddIncrement; // задётся в иниткомпонент, надо обновить
		win.schedulePanel.activeView.body.minEventHeight = win.schedulePanel.activeView.body.minEventDisplayMinutes / (win.schedulePanel.activeView.body.hourIncrement / win.schedulePanel.activeView.body.hourHeight); // задётся в иниткомпонент, надо обновить
		win.schedulePanel.activeView.refresh(); // рефрешим отображение графика
		win.schedulePanel.activeView.body.scrollTo(scrollAfter); // скроллим к тому же часу, что и был открыт

		win.dateMenu.items.each(function(item){
			if (item.checked && item.setChecked != undefined) {
				item.setChecked(false);
			}
		});

		menuItem.setChecked(true);
		win.dateMenu.hide();
	},
	timeDropdown: function(e, el) {
		var win = this;
		// тут меню простое: по 10/20/30/60/120 минут
		if (!this.dateMenu) {
			this.dateMenu = Ext6.create('Ext.menu.Menu', {
				cls: 'operBlockDateMenu',
				scope: this,
				plain: true,
				items: [new Ext6.menu.CheckItem({
					text: 'по 10 минут',
					checked: false,
					handler: function () {
						win.setHourIncrement(10, this);
					}
				}), new Ext6.menu.CheckItem({
					text: 'по 20 минут',
					checked: false,
					handler: function () {
						win.setHourIncrement(20, this);
					}
				}), new Ext6.menu.CheckItem({
					text: 'по 30 минут',
					checked: true,
					handler: function () {
						win.setHourIncrement(30, this);
					}
				}), new Ext6.menu.CheckItem({
					text: 'по 60 минут',
					checked: false,
					handler: function () {
						win.setHourIncrement(60, this);
					}
				}), new Ext6.menu.CheckItem({
					text: 'по 120 минут',
					checked: false,
					handler: function () {
						win.setHourIncrement(120, this);
					}
				})]
			});
		}

		var xy = Ext.get(el).getXY();
		xy[1] = xy[1] + 30; // смещаем вниз
		this.dateMenu.showAt(xy);
	},
	tablesMenu: function(e, el) {
		var win = this;
		// тут сложнее, надо подтянуть столы из сторе и меню должно быть с галочками
		if (!this.tableMenu) {
			var items = [];
			win.calendarStore.each(function(rec) {
				items.push(new Ext6.menu.CheckItem({
					text: "<div class='x-cal-res-left x-cal-res-" + rec.get('ColorId') + "'></div><div class='x-cal-res-right'>" + rec.get('Resource_Name') + "</div>",
					checked: true,
					Resource_id: rec.get('Resource_id'),
					handler: function () {
						var checked = [];
						win.tableMenu.items.each(function(item){
							if (item.checked && item.setChecked != undefined) {
								checked.push(item.Resource_id);
							}
						});

						if (checked.length == 0) {
							// оу, нельзя выключить все столы
							this.setChecked(true);
							return;
						}

						// обновляем количество столов :)
						win.calendarStore.clearFilter();
						win.calendarStore.filterBy(function(rec) { // фильтруем сторе столов
							return rec.get('Resource_id').inlist(checked);
						});
						win.schedulePanel.activeView.refresh(); // рефрешим отображение графика
					}
				}));
			});

			this.tableMenu = Ext6.create('Ext.menu.Menu', {
				cls: 'operTableMenu',
				scope: this,
				plain: true,
				items: items
			});
		}

		var xy = Ext.get(el).getXY();
		xy[1] = xy[1] + 30; // смещаем вниз
		this.tableMenu.showAt(xy);
	},
	show: function() {
		this.callParent(arguments);
		var win = this;

		var plugin = win.mainGrid.getView().getPlugin('gridviewdragdrop');
		plugin.disable();
		if (isUserGroup('operblock_head')) {
			plugin.enable();
		}

		win.schedulePanel.items.items[0].body.dragZone.lock();
		if (isUserGroup('operblock_head')) {
			win.schedulePanel.items.items[0].body.dragZone.unlock();
		}

		if ( arguments[0] ) {
			if ( arguments[0].userMedStaffFact && arguments[0].userMedStaffFact.ARMType ) {
				this.ARMType = arguments[0].userMedStaffFact.ARMType;
				this.userMedStaffFact = arguments[0].userMedStaffFact;
			}
			else {
				if ( arguments[0].MedService_id ) {
					this.MedService_id = arguments[0].MedService_id;
					this.userMedStaffFact = arguments[0];
				}
				else {
					if ( arguments[0].ARMType ) { // Это АРМ без привязки к врачу - АРМ администратора или кадровика
						this.userMedStaffFact = arguments[0];
					} else {
						this.hide();
						sw.swMsg.alert('Ошибка открытия формы', 'Ошибка открытия формы "'+this.title+'".<br/>Не указан тип АРМа.');
						return false;
					}
				}
			}
		}
		else {
			this.hide();
			sw.swMsg.alert('Ошибка открытия формы', 'Ошибка открытия формы "'+this.title+'".<br/>Отсутствуют необходимые параметры.');
			return false;
		}

		sw.Promed.MedStaffFactByUser.setMenuTitle(win, win.userMedStaffFact);

		// грузим текущую дату
		win.mask(LOAD_WAIT);
		Ext6.Ajax.request({
			url: C_LOAD_CURTIME,
			callback: function(opt, success, response) {
				win.unmask();
				if (success && response.responseText != '') {
					var result  = Ext6.JSON.decode(response.responseText);
					win.curDate = result.begDate;
					win.down('#datefilter').setValue(result.begDate);
					win.doFilter(true);
				}
			}
		});

		win.calendarStore.load({
			params: {
				MedService_id: win.MedService_id
			}
		});

		win.onRecordSelect();
	},
	showEvnUslugaOperEditWindow: function(data) {
		var win = this;
		
		var params = {
			action: 'edit',
			parentClass: 'EvnSection',
			useCase: 'OperBlock',
			formParams: {
				EvnUslugaOper_id: data.EvnUslugaOper_id,
				EvnUslugaOper_setDate: data.EvnUslugaOper_setDate,
				LpuSection_id: win.userMedStaffFact.LpuSection_id,
				OperBrig: data.OperBrig,
				LpuSectionProfile_id: win.userMedStaffFact.LpuSectionProfile_id,
			},
			callback: function() {
				// обновить гриды
				win.doFilter();
			}
		};

		getWnd('swEvnUslugaOperEditWindow').show(params);
	},
	loadSchedulePanel: function(params) {
		var win = this;

		win.eventStore.getProxy().extraParams.MedService_id = win.MedService_id;
		win.eventStore.getProxy().extraParams.onDate = win.down('#datefilter').getValue().format('d.m.Y');

		if (!Ext6.isEmpty(win.down('#datefilter').getValue())) {
			if (params.scrollToFirst) {
				win.schedulePanel.scrollToFirst = true;
			}
			win.schedulePanel.setStartDate(win.down('#datefilter').getValue());
		} else {
			win.eventStore.load();
		}
	},
	stepDay: function(day)
	{
		var win = this;
		var date1 = (win.down('#datefilter').getValue() || Date.parseDate(win.curDate, 'd.m.Y')).add(Date.DAY, day).clearTime();
		win.down('#datefilter').setValue(Ext6.util.Format.date(date1, 'd.m.Y'));
	},
	prevDay: function() {
		this.stepDay(-1);
	},
	nextDay: function ()
	{
		this.stepDay(1);
	},
	onTabChange: function() {
		this.doFilter();
	},
	doFilter: function(scrollToFirst) {
		var win = this;
		var params = {
			onDate: win.down('#datefilter').getValue().format('d.m.Y'),
			MedService_id: win.MedService_id
		};

		if (win.mainPanel.tabId == 'operplan') {
			win.mainPanel.down('#planbutton').show();
			win.mainPanel.down('#urgentbutton').show();
			win.mainPanel.down('#cancelbutton').show();
			win.mainPanel.down('#declinebutton').show();
			win.mainPanel.down('#cancelexecbutton').hide();
			win.mainPanel.down('#action_patomorf').hide();
			params.type = 'operplan';
		} else {
			win.mainPanel.down('#planbutton').hide();
			win.mainPanel.down('#urgentbutton').hide();
			win.mainPanel.down('#cancelbutton').hide();
			win.mainPanel.down('#declinebutton').hide();
			win.mainPanel.down('#cancelexecbutton').show();
			win.mainPanel.down('#action_patomorf').show();
			params.type = 'operdone';
		}

		win.mainGrid.getStore().load({params: params});

		if (scrollToFirst) {
			params.scrollToFirst = true;
		}

		win.loadSchedulePanel(params);
	},
	openDocumentUcAddWindow: function(type_code) {
		var params = new Object();
		var edit_window_name = 'swNewDocumentUcEditWindow';

		switch(type_code) {
			case 2: //2 - Документ списания медикаментов
				params.DrugDocumentType_id = 2;
				params.FormParams = {
					Contragent_sid: getGlobalOptions().Contragent_id
				};
				break
			case 3: //3 - Документ ввода остатков
				params.DrugDocumentType_id = 3;
				params.FormParams = {
					Contragent_tid: getGlobalOptions().Contragent_id
				};
				break
			case 6: //6 - Приходная накладная
				params.DrugDocumentType_id = 6;
				params.FormParams = {
					Contragent_tid: getGlobalOptions().Contragent_id
				};
				params.isSmpMainStorage = false;
				break
			case 15: //15 - Накладная на внутреннее перемещение
				params.DrugDocumentType_id = 15;
				params.FormParams = {
					Contragent_sid: getGlobalOptions().Contragent_id
				};
				params.isSmpMainStorage = false;
				break
		}

		if (!Ext.isEmpty(params.DrugDocumentType_id)) {
			params.DrugDocumentType_Code = type_code;
			params.callback = function() { this.hide(); };
			params.action = 'add';
			params.userMedStaffFact = this.userMedStaffFact;

			getWnd(edit_window_name).show(params);
		}
	},
    initComponent: function() {
        var win = this;

		var groupingFeature = new Ext6.grid.feature.Grouping({
			groupHeaderTpl: new Ext6.XTemplate(
				'{name:this.formatName}',
				{
					formatName: function(name) {
						if (win.mainPanel.tabId == 'operdone') {
							return "Выполненные";
						}
						if (name == 2) {
							return "Распределённые";
						} else {
							return "Очередь";
						}
					}
				}
			)
		});

		win.mainGrid = new Ext6.grid.Panel({
			xtype: 'grid',
			region: 'center',
			features: [groupingFeature],
			viewConfig: {
				plugins: {
					ptype: 'gridviewdragdrop',
					pluginId: 'gridviewdragdrop',
					onViewRender: function(view) {
						var me = this,
							scrollEl;
						if (me.containerScroll) {
							scrollEl = view.getEl();
						}

						me.dragZone = new Ext6.view.DragZone({
							view: view,
							getDragText: function() {
								var count = this.dragData.records.length;
								if (count > 0) {
									return this.dragData.records[0].get('Person_Fio');
								}
								return "";
							},
							ddGroup: 'DayViewDD',
							dragText: me.dragText,
							containerScroll: me.containerScroll,
							scrollEl: scrollEl,
							onMouseDown: function (e) {
								win.eventStore.each(function (rec) {
									rec.dz = me.dragZone;
									var el = Ext.query('div[class*='+ rec.id +']');
									var dom = Ext.getDom(el[0]);
									dom.rec_id = rec.id;
									var wrap = Ext.get(dom.id);
									if ( wrap ) {
										wrap.dom.onmouseover = function( event ) {
											var r = Ext.get(this.id);
											win.eventStore.data.map[r.dom.rec_id].dz._dropNotAllowed = true;
										}
										wrap.dom.onmouseout = function( event ) {
											var r = Ext.get(this.id);
											win.eventStore.data.map[r.dom.rec_id].dz._dropNotAllowed = false;
										}
									}
								});
							},
/*
							onDrag: function (e) {
								win.eventStore.each(function (rec) {
									var el = Ext.query('div[class*='+ rec.id +']');
								});
							}
*/						});
					}
				}
			},
			selModel: {
				mode: 'SINGLE',
				listeners: {
					select: function(model, record, index) {
						win.onRecordSelect();
					}
				}
			},
			store: {
				groupField: 'IsPlanned',
				fields: [
					'EvnDirection_id',
					{ name: 'EvnUslugaOper_id', type: 'int' },
					{ name: 'EvnDirection_IsCito', type: 'int' },
					'Person_id',
					'Person_Fio',
					{ name: 'Person_Age', type: 'int' },
					{ name: 'Sex_id', type: 'int' },
					{ name: 'Diag_id', type: 'int' },
					'Diag_Name',
					'IsAllerg',
					{ name: 'EvnDirection_setDate', type: 'date', dateFormat: 'd.m.Y' },
					'EvnXml_id',
					'UslugaEvnXml_id',
					{ name: 'MedStaffFact_id', type: 'int' },
					{ name: 'MedPersonal_id', type: 'int' },
					{ name: 'LpuSection_id', type: 'int' },
					'MedPersonal_Fio',
					'UslugaComplex_id',
					'UslugaComplex_Name',
					{ name: 'EvnDirection_desDT', type: 'date', dateFormat: 'd.m.Y' },
					'LpuSection_Name',
					{ name: 'TimetableResource_begDate', type: 'date', dateFormat: 'd.m.Y' },
					{ name: 'EvnUslugaOper_setDT', type: 'date', dateFormat: 'd.m.Y' },
					'TimetableResource_begTime',
					'EvnRequestOperBrig',
					'isAnest',
					'IsPlanned',
					'TimetableResource_id',
					'Resource_Name',
					'EvnSection_id',
					'EvnSection_pid',
					'EvnVizitPL_id',
					'EvnVizitPL_pid'
				],
				proxy: {
					type: 'ajax',
					actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: '/?c=OperBlock&m=loadMainGrid',
					reader: {
						type: 'json',
						rootProperty: 'data'
					}
				},
				sorters: [
					'Person_Fio'
				],
				listeners: {
					load: function() {
						win.onRecordSelect();
					}
				}
			},
			columns: [
				{text: 'Cito!', width: 50, dataIndex: 'EvnDirection_IsCito', align: "center", renderer: function(val) {
					if (val == 2) {
						return '<img src="/img/icons/warn_red.png" />';
					}

					return '';
				}},
				{text: 'Фамилия И.О.', flex: 1, minWidth: 100, dataIndex: 'Person_Fio'},
				{text: 'Лет', width: 50, dataIndex: 'Person_Age'},
				{text: 'Пол', width: 50, dataIndex: 'Sex_id', align: "center", renderer: function(val, metaData, record) {
						if (val == 1) {
							return '<img src="/img/icons/male16.png" />';
						}else{
							return '<img src="/img/icons/female16.png" />';
						}
					}
				},
				{text: 'Диагноз', width: 150, dataIndex: 'Diag_Name'},
				{text: 'Аллерг.', width: 60, dataIndex: 'IsAllerg', align: "center", renderer: function(val, metaData, record) {
					if (val == 2) {
						return '<a href="#" onClick="getWnd(\'swEmkDocumentViewWindow\').show({ objectCode: \'AllergHistory\',  objectKey: \'Person_id\', objectId: \''+record.get('Person_id')+'\' });"><img src="/img/icons/warn_yellow.png" /></a>';
					}

					return '';
				}},
				{text: 'Поступил', width: 100, dataIndex: 'EvnDirection_setDate', renderer: Ext6.util.Format.dateRenderer('d.m.Y')},
				{text: 'Лечащий врач', width: 100, dataIndex: 'MedPersonal_Fio'},
				{text: 'Отделение', width: 100, dataIndex: 'LpuSection_Name'},
				{text: 'Желаемая дата', width: 100, dataIndex: 'EvnDirection_desDT', renderer: Ext6.util.Format.dateRenderer('d.m.Y')},
				{text: 'Эпикриз', width: 60, dataIndex: 'EvnXml_id', align: "center", renderer: function(val) {
					if (!Ext6.isEmpty(val)) {
						return '<a href="#" onClick="getWnd(\'swEvnXmlViewWindow\').show({ EvnXml_id: '+val+' });"><img src="/img/icons/ok3.png" /></a>';
					}

					return '';
				}},
				{text: 'Операция', width: 150, dataIndex: 'UslugaComplex_Name'},
				{text: 'Протокол', width: 60, dataIndex: 'UslugaEvnXml_id', align: "center", renderer: function(val) {
					if (!Ext6.isEmpty(val)) {
						return '<a href="#" onClick="getWnd(\'swEvnXmlViewWindow\').show({ EvnXml_id: '+val+' });"><img src="/img/icons/ok4.png" /></a>';
					}

					return '';
				}},
				{text: 'Бригада', width: 100, dataIndex: 'EvnRequestOperBrig'},
				{text: 'Необходимость анестезии', width: 100, dataIndex: 'isAnest'},
				{text: 'Дата', width: 80, dataIndex: 'TimetableResource_begDate', renderer: Ext6.util.Format.dateRenderer('d.m.Y')},
				{text: 'Время', width: 50, dataIndex: 'TimetableResource_begTime'},
				{text: 'Стол', width: 100, dataIndex: 'Resource_Name'}
			]
		});

		win.calendarStore = Ext6.create('Extensible.calendar.data.MemoryCalendarStore', {
			autoLoad: false,
			proxy: {
				type: 'ajax',
				actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
				url: '/?c=OperBlock&m=getCalendars',
				noCache: false,

				reader: {
					type: 'json',
					rootProperty: 'calendars'
				}
			}
		});

		win.eventStore = Ext6.create('Extensible.calendar.data.EventStore', {
			autoLoad: false,
			listeners: {
				'load': function() {
					win.checkConflicts();
				},
				'write': function( store, operation, eOpts ) {
					// если были решены конфликты на стороне сервера, то надо обновить всю область АРМ.
					if (operation.response && operation.response.responseText) {
						var response_obj = Ext6.JSON.decode(operation.response.responseText);
						if (response_obj && response_obj.needUpdate) {
							win.doFilter();
						}
					}

					// обновляем стор при каждом дропе, для того, чтобы узнать, существуют ли пересечения мед персонала с операциями в других отделениях
					win.loadSchedulePanel({scrollToFirst: false});
				}
			},
			proxy: {
				type: 'ajax',
				actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
				api: {
					create  : '/?c=OperBlock&m=updateCalendarEvents',
					read    : '/?c=OperBlock&m=getCalendarEvents',
					update  : '/?c=OperBlock&m=updateCalendarEvents',
					destroy : '/?c=OperBlock&m=destroyCalendarEvents'
				},
				noCache: false,

				reader: {
					type: 'json',
					rootProperty: 'data'
				},

				writer: new Ext6.data.writer.Writer({
					type: 'json',
					nameProperty: 'mapping',
					writeRecords: function(request, data) { // это чтобы параметры отправлялись обычным POST-запросом
						if (data.length > 1) {
							data.forEach(function (item, index){ // ищем нужные данные! у которых есть измененные значения start и end
								if (item.start && item.end) {
									request._params = {
										EvnDirection_id: item.EvnDirection_id ? item.EvnDirection_id : request._records[index].data.EvnDirection_id,
										Resource_id: item.Resource_id ? item.Resource_id : request._records[index].data.Resource_id,
										start: item.start ? item.start : request._records[index].data.start,
										end: item.end ? item.end : request._records[index].data.end
									};
								}
							});
						} else {
							request._params = {
								EvnDirection_id: data[0].EvnDirection_id ? data[0].EvnDirection_id : request._records[0].data.EvnDirection_id,
								Resource_id: data[0].Resource_id ? data[0].Resource_id : request._records[0].data.Resource_id,
								start: data[0].start ? data[0].start : request._records[0].data.start,
								end: data[0].end ? data[0].end : request._records[0].data.end
							};
						}
						return request;
					}
				})
			}
		});

		win.eventStore.getProxy().on({
			exception: function() {
				win.eventStore.rejectChanges();
			}
		});

		win.schedulePanel = Ext6.create('Extensible.calendar.CalendarPanel', {
			ownerWin: win,
			region: 'center',
			eventStore: win.eventStore,
			calendarStore: win.calendarStore,
			title: '',
			onStoreAdd: function(ds, rec) {
			},
			onStoreUpdate: function() {
				win.checkConflicts();
			},
			onStoreRemove: function() {
				win.mainGrid.getStore().reload();
			}
		});

		win.leftMenu = new Ext6.menu.Menu({
			xtype: 'menu',
			floating: false,
			dock: 'left',
			cls: 'leftPanelWP',
			hidden: getRegionNick() != 'vologda',
			border: false,
			padding: 0,
			defaults: {
				margin: 0
			},
			mouseLeaveDelay: 100,
			collapsedWidth: 30,
			collapseMenu: function () {
				if (!win.leftMenu.activeChild || win.leftMenu.activeChild.hidden) {
					clearInterval(win.leftMenu.collapseInterval); // сбрасывем
					win.leftMenu.getEl().setWidth(win.leftMenu.collapsedWidth); // сужаем
					win.leftMenu.body.setWidth(win.leftMenu.collapsedWidth - 1); // сужаем
					win.leftMenu.deactivateActiveItem();
				}
			},
			listeners: {
				mouseover: function () {
					clearInterval(win.leftMenu.collapseInterval); // сбрасывем
					win.leftMenu.getEl().setWidth(win.leftMenu.items.items[0].getWidth());
					win.leftMenu.body.setWidth(win.leftMenu.items.items[0].getWidth() - 1);
				},
				afterrender: function (scope) {
					win.leftMenu.setWidth(win.leftMenu.collapsedWidth); // сразу сужаем
					win.leftMenu.setZIndex(10); // fix zIndex чтобы панель не уезжала под грид

					this.el.on('mouseout', function () {
						// сужаем, если нет подменю
						clearInterval(win.leftMenu.collapseInterval); // сбрасывем
						win.leftMenu.collapseInterval = setInterval(win.leftMenu.collapseMenu, 100);
					});
				}
			},
			items: [{
				iconCls: 'drug16-2017',
				handler: function() {
					getWnd('swDrugOstatRegistryListWindow').show({
						mode: 'suppliers',
						userMedStaffFact: win.userMedStaffFact
					});
				},
				text: langs('Остатки')
			}, {
				iconCls: 'protocol16-2017',
				menu: [
					{
						text: langs('Заявка-требование'),
						handler: function() {
							getWnd('swWhsDocumentUcEditWindow').show({
								action: 'add',
								WhsDocumentClass_id: 2,
								WhsDocumentClass_Code: 2,
								userMedStaffFact: win.userMedStaffFact
							});
						}
					}, {
						text: langs('Перемещение'),
						handler: function() {
							win.openDocumentUcAddWindow(15); //15 - Накладная на внутреннее перемещение
						}
					}, {
						text: langs('Списание'),
						handler: function() {
							win.openDocumentUcAddWindow(2); //2 - Документ списания медикаментов
						}
					}, {
						text: langs('Инвентаризация'),
						menuAlign: 'tr?',
						menu: [{
							text: langs('Приказы на проведение инвентаризации'),
							handler: function () {
								getWnd('swWhsDocumentUcInventOrderViewWindow').show({
									ARMType: 'merch'
								});
							}
						}, {
							text: langs('Инвентаризационные ведомости'),
							disabled: false,
							handler: function () {
								var wndParams = {
									ARMType: 'merch',
									MedService_id: win.userMedStaffFact.MedService_id,
									Lpu_id: win.userMedStaffFact.Lpu_id,
									LpuSection_id: win.userMedStaffFact.LpuSection_id,
									LpuBuilding_id: win.userMedStaffFact.LpuBuilding_id
								};
								if (getGlobalOptions().orgtype != 'lpu' && win.userMedStaffFact.MedService_id > 0) {
									Ext.Ajax.request({
										params: {MedService_id: win.userMedStaffFact.MedService_id},
										callback: function (options, success, response) {
											if (success) {
												var response_obj = Ext.util.JSON.decode(response.responseText);
												if (response_obj[0] && response_obj[0].OrgStruct_id) {
													wndParams.OrgStruct_id = response_obj[0].OrgStruct_id;
												}
											}
											getWnd('swWhsDocumentUcInventViewWindow').show(wndParams);
										},
										url: '/?c=MedService&m=loadEditForm'
									});
								} else {
									getWnd('swWhsDocumentUcInventViewWindow').show(wndParams);
								}
							}
						}]
					}, {
						text: langs('Приход'),
						handler: function() {
							win.openDocumentUcAddWindow(6); //6 - Приходная накладная
						}
					}, {
						text: langs('Ввод остатков'),
						handler: function() {
							win.openDocumentUcAddWindow(3); //3 - Документ ввода остатков
						}
					}
				],
				text: langs('Учет медикаментов')
			}, {
				iconCls: 'notice16-2017',
				handler: function() {
					getWnd('swMessagesViewWindow').show();
				},
				text: langs('Журнал уведомлений')
			}, {
				iconCls: 'reports16-2017',
				handler: function() {
					if (sw.codeInfo.loadEngineReports) {
						getWnd('swReportEndUserWindow').show();
					} else {
						getWnd('reports').load({
							callback: function (success) {
								sw.codeInfo.loadEngineReports = success;
								getWnd('swReportEndUserWindow').show();
							}
						});
					}
				},
				text: langs('Отчеты')
			}]
		});

		win.mainPanel = new Ext6.Panel({
			region: 'center',
			layout: 'border',
			border: false,
			tabId: 'operplan',
			dockedItems: [ win.leftMenu ],
			tbar: {
				cls: 'grid-toolbar',
				xtype: 'toolbar',
				items: [{
					xtype: 'button',
					cls: 'bgTrans',
					border: false,
					margin: 3,
					iconCls: 'arrow-previous16-2017',
					handler: function()
					{
						win.prevDay();
						win.doFilter(true);
					}
				}, {
					format : 'd.m.Y',
					itemId: 'datefilter',
					xtype: 'datefield',
					plugins: [ new Ext6.ux.InputTextMask('99.99.9999', false) ]
				}, {
					xtype: 'button',
					cls: 'bgTrans',
					border: false,
					margin: 3,
					iconCls: 'arrow-next16-2017',
					handler: function()
					{
						win.nextDay();
						win.doFilter(true);
					}
				}, {
					itemId: 'emkbutton',
					xtype: 'button',
					cls: 'toolbar-padding',
					iconCls: 'action_openemk',
					tooltip: 'Открыть ЭМК',
					handler: function() {
						win.openPersonEmkWindow();
					}
				}, {
					itemId: 'planbutton',
					xtype: 'button',
					cls: 'toolbar-padding',
					iconCls: 'action_schedule',
					tooltip: 'Планировать',
					handler: function() {
						win.openPlanWindow();
					}
				}, {
					itemId: 'urgentbutton',
					xtype: 'button',
					cls: 'toolbar-padding',
					iconCls: 'action_urgent',
					tooltip: 'Экстренная',
					handler: function() {
						win.createUrgentRequest();
					}
				},{
					itemId: 'resultbutton',
					xtype: 'button',
					cls: 'toolbar-padding',
					iconCls: 'action_result',
					tooltip: 'Результат',
					handler: function() {
						win.openResultWindow();
					}
				}, {
					itemId: 'cancelbutton',
					xtype: 'button',
					cls: 'toolbar-padding',
					iconCls: 'action_cancel',
					tooltip: 'Отменить',
					handler: function() {
						win.cancelPlan();
					}
				}, {
					itemId: 'declinebutton',
					xtype: 'button',
					cls: 'toolbar-padding',
					iconCls: 'action_decline',
					tooltip: 'Отклонить',
					handler: function() {
						win.decline();
					}
				}, {
					itemId: 'cancelexecbutton',
					xtype: 'button',
					cls: 'toolbar-padding',
					iconCls: 'action_cancel',
					tooltip: 'Отменить выполнение',
					handler: function() {
						win.cancelPlan();
					}
				}, {
					xtype: 'button',
					cls: 'toolbar-padding',
					iconCls: 'action_refresh',
					tooltip: 'Обновить',
					handler: function() {
						win.doFilter();
					}
				}, {
					xtype: 'button',
					cls: 'toolbar-padding',
					iconCls: 'action_print',
					tooltip: 'Печать',
					menu: [{
						text: 'Печать списка',
						handler: function() {
							Ext6.ux.GridPrinter.print(win.mainGrid);
						}
					}, {
						text: 'Форма 008у',
						itemId: 'form008u',
						handler: function() {
							var record = win.mainGrid.getSelectionModel().getSelection()[0];

							if (typeof record != 'object' || Ext6.isEmpty(record.get('EvnUslugaOper_id')) || Ext6.isEmpty(record.get('EvnUslugaOper_setDT'))) {
								return false;
							}

							printBirt({
								'Report_FileName': 'f008u.rptdesign',
								'Report_Params': '&paramEvnUslugaOper='+record.get('EvnUslugaOper_id'),
								'Report_Format': 'pdf'
							});
						}
					}, {
						text: 'План операций',
						handler: function() {
							printBirt({
								'Report_FileName': 'plan_Oper.rptdesign',
								'Report_Params': '&paramLpu='+getGlobalOptions().lpu_id+'&paramMedService='+win.MedService_id+'&paramDate='+win.down('#datefilter').getValue().format('d.m.Y'),
								'Report_Format': 'pdf'
							});
						}
					}],
				}, {
					disabled: true,
					xtype: 'button',
					cls: 'toolbar-padding',
					iconCls: 'action_settings',
					tooltip: 'Настройки',
					menu: new Ext6.menu.Menu({
						items: [{
							tooltip: 'Связи',
							text: 'Связи',
							iconCls: '',
							handler: function () {
								getWnd('swMedServiceLinkManageWindow').show({
									MedService_id: win.MedService_id,
									MedServiceType_SysNick: 'oper_block',
									MedServiceLinkType_id: 14
								});
							}
						}]
					})
				}, {
					xtype: 'button',
					cls: 'toolbar-padding',
					iconCls: 'action_messages',
					tooltip: 'Сообщения',
					handler: function() {
						getWnd('swMessagesViewWindow').show();
					}
				}, {
					itemId: 'action_patomorf',
					xtype: 'button',
					cls: 'toolbar-padding',
					iconCls: 'action_patomorf',
					tooltip: 'Направление на патологогистологическое исследование',
					handler: function() {
						win.openEvnDirectionHistologicEditWindow();
					}
				}, '->', {
					xtype: 'segmentedbutton',
					userCls: 'segmentedButtonGroup',
					id: 'tabPanel',
					items: [{
						text: 'Планируемые',
						refId: 'operplan',
						pressed: true,
						handler: function() {
							win.mainPanel.tabId = 'operplan';
							win.onTabChange();
						}
					}, {
						text: 'Выполненные',
						refId: 'operdone',
						handler: function() {
							win.mainPanel.tabId = 'operdone';
							win.onTabChange();
						}
					}]
				}]
			},
			items: [{
				title: 'Список',
				animCollapse: false,
				floatable: false,
				collapsible: true,
				split: true,
				flex: 100,
				region: 'west',
				layout: 'border',
				items: [ win.mainGrid ]
			}, {
				title: 'График',
				flex: 100,
				animCollapse: false,
				floatable: false,
				collapsible: true,
				collapseDirection: 'right',
				region: 'center',
				layout: 'border',
				items: [ win.schedulePanel ]
			}]
		});

        Ext6.apply(win, {
			items: [win.mainPanel, win.FormPanel]
		});

		this.callParent(arguments);
    }
});