/**
 * Панель записи на службу/услугу
 */
sw.Promed.swTTRRecordPanel = Ext.extend(Ext.Panel, {
	id: 'TTRRecordPanel',
	frame: false,

	/**
	 * Функция возврашающся ссылку на родительский элемент
	 */
	getOwner: null,

	/**
	 * Идентификатор выбранной бирки
	 */
	TimetableResource_id: null,

	/**
	 * Идентификатор родителя
	 */
	EvnUsluga_pid: null,

	/**
	 * Элемент выбранной бирки
	 */
	TimetableResource_Element: null,

	/**
	 * Дата, с которой отображается расписание
	 */
	date: null,

	/**
	 * Служба, для которой отображается расписание
	 */
	MedService_id: null,

	/**
	 * Ресурс, для которой отображается расписание
	 */
	Resource_id: null,

	/**
	 * Маска для загрузки
	 */
	loadMask: null,

	/**
	 * Дополнительные данные по ресурсу, которые надо передавать в форму заказа комплексной услуги
	 */
	ResourceData: null,

	/**
	 * Данные человека
	 */
	personData: null,

	/**
	 * Данные для направления
	 */
	directionData: null,

	/**
	 * Функция вызываемая после успешной записи или постановки в очередь
	 */
	onDirection: Ext.emptyFn,
	/**
	 * Функция, вызываемая непосредственно после успешной записи на бирку
	 */
	onSaveRecord:  Ext.emptyFn,
	/**
	 * Получить вариант использования
	 */
	getUseCase: function() {
		return this._useCase || 'undefined';
	},
	/**
	 * Установить вариант использования
	 */
	setUseCase:  function(use_case) {
		this._useCase = use_case || 'undefined';
		switch (this._useCase) {
			case 'rewrite':
			case 'record_from_queue':
			case 'show_list_only':
				this.btnQueuePerson.setVisible(false);
				break;
			default:
				this.btnQueuePerson.setVisible(true);
				break;
		}
		if(getWnd('swWorkPlaceMZSpecWindow').isVisible() || getWnd('swPMWorkPlaceWindow').isVisible() || getWnd('swMiacWorkPlaceWindow').isVisible())
		{
			this.btnQueuePerson.setVisible(false);
		}
	},

	onDateChange: function() {
		this.loadSchedule();
	},

	/**
	 * Загрузка расписания
	 *
	 * @param date Дата, начиная с которой загружать расписание
	 */
	loadSchedule: function(date)
	{
		if (date) {
			this.date = date;
		}

		if (!Ext.isEmpty(this.Resource_id)) {
			var url = C_TTR_LISTFORREC;
			this.load(
				{
					url: url,
					params: {
						StartDay: this.date,
						Resource_id: this.Resource_id,
						PanelID: this.id // отправляем идентификатор панели для правильной генерации HTML
					},
					scripts: true,
					timeout: 300,
					text: lang['podojdite_idet_zagruzka_raspisaniya'],
					callback: function () {
						//
					}.createDelegate(this),
					failure: function () {
						Ext.Msg.alert(lang['oshibka'], lang['oshibka_polucheniya_raspisaniya_poprobuyte_esche_raz']);
					}
				}
			);
		}
	},


	/**
	 * Открытие окна для комментария на службу
	 */
	openMedServiceCommentWindow: function(day)
	{
		getWnd('swEditMedStaffFactCommentWindow').show({
			MedService_id: this.MedService_id,
			callback: function() {
				this.loadSchedule();
			}.createDelegate(this)
		})
	},

	/**
	 * Открытие окна истории изменения бирки
	 */
	openTTHistoryWindow: function()
	{
		getWnd('swTTHistoryWindow').show({
			TimetableResource_id: this.TimetableResource_id,
			callback: function() {

			}.createDelegate(this)
		});
	},

	/**
	 * Печать расписания врача as is
	 */
	printSchedule: function() {
		var id_salt = Math.random();
		var win_id = 'print_ttr_edit' + Math.floor(id_salt * 10000);
		var win = window.open(C_TTR_LISTFOREDITPRINT + '&StartDay=' + this.date + '&Resource_id=' + this.Resource_id, win_id);
	},

	/**
	 * Печать списка записанных пациентов
	 */
	printPacList: function() {
		if (Ext.isEmpty(this.MedService_id))
			return false;

		var d = new Date(),
			MedService_id = this.MedService_id,
			id_salt = Math.random(),
			win_id = 'print_pac_list' + Math.floor(id_salt * 10000);

		var datestring = ("0" + d.getDate()).slice(-2) + "." + ("0"+(d.getMonth()+1)).slice(-2) + "." +	d.getFullYear();

		window.open('/?c=TimetableGraf&m=printPacList&begDate=' + datestring + '&MedService_id=' + MedService_id + '&isPeriod=' + 2, win_id);

	},

	/**
	 * Перемещение по календарю
	 */
	stepDay: function(day)
	{
		var date = (this.calendar.getValue() || Date.parseDate(this.date, 'd.m.Y')).add(Date.DAY, day).clearTime();
		this.calendar.setValue(Ext.util.Format.date(date, 'd.m.Y'));
		this.date = Ext.util.Format.date(date, 'd.m.Y');
	},

	/**
	 * На день назад
	 */
	prevDay: function ()
	{
		this.stepDay(-1);
	},

	/**
	 * И на день вперед
	 */
	nextDay: function ()
	{
		this.stepDay(1);
	},

	/**
	 * Освобождение времени
	 */
	clearTime: function(time_id, evndirection_id)
	{
		if (time_id) {
			this.TimetableResource_id = time_id;
		}
		if (evndirection_id) {
			getWnd('swSelectDirFailTypeWindow').show({
				time_id: this.TimetableResource_id,
				LpuUnitType_SysNick: 'resource',
				onClear: function() {
					this.loadSchedule();
				}.createDelegate(this)
			});
		} else {
			sw.swMsg.show({
				title: lang['podtverjdenie'],
				msg: lang['vyi_deystvitelno_jelaete_osvobodit_vremya_priema'],
				buttons: Ext.Msg.YESNO,
				fn: function ( buttonId ) {
					if ( buttonId == 'yes' )
					{
						var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Подождите, идет сохранение..." });
						loadMask.show();
						submitClearTime(
							{
								id: this.TimetableResource_id,
								type: 'resource',
								DirFailType_id: null,
								EvnComment_Comment: null
							},
							function(options, success, response) {
								loadMask.hide();
								this.loadSchedule();
							}.createDelegate(this),
							function() {
								loadMask.hide();
							}
						);
					}
				}.createDelegate(this)
			});
		}
	},

	recordPerson: function(time_id, date, time)
	{
		this.directionData['time'] = (date && time)?(date +' '+ time):null;
		var params = {
			Timetable_id: time_id
			,userMedStaffFact: this.userMedStaffFact || {}
			,direction: this.directionData
			,person: this.personData || null
			,loadMask: true
			,windowId: 'swDirectionMasterWindow'
			,callback: this.onDirection || Ext.emptyFn
			,onSaveRecord: this.onSaveRecord || Ext.emptyFn
			,onHide: Ext.emptyFn
			,needDirection: null
			,fromEmk: false
			,mode: 'nosave'
			,order: (this.order)?this.order:{} // если при записи сделан заказ, то передаем его данные
			,date: date
		};
		sw.Promed.Direction.recordPerson(params);
		return true;

	},


	/**
	 * Помещение человека в очередь
	 */
	queuePerson: function()
	{
		var params = {
			direction: this.directionData
			,userMedStaffFact: this.userMedStaffFact || {}
			,person: this.personData || null
			,loadMask: true
			,windowId: 'swDirectionMasterWindow'
			,callback: this.onDirection || Ext.emptyFn
			,onSaveRecord: Ext.emptyFn
			,onHide: Ext.emptyFn
			,needDirection: null
			,fromEmk: false
			,order: (this.order)?this.order:{} // если при записи сделан заказ, то передаем его данные
			,mode: 'nosave'
		};
		sw.Promed.Direction.queuePerson(params);
		return true;

	},

	/**
	 * Маска при загрузке
	 */
	getLoadMask: function(MSG)
	{
		if (MSG)
		{
			delete(this.loadMask);
		}
		if (!this.loadMask)
		{
			this.loadMask = new Ext.LoadMask(this.getEl(), { msg: MSG });
		}
		return this.loadMask;
	},

	initComponent: function() {
		var me = this;

		me.btnQueuePerson = new Ext.Toolbar.Button({
			iconCls: 'add16',
			text: lang['postavit_v_ochered'],
			tooltip : "Поставить в очередь <b>(F2)</b>",
			handler: function () {
				me.queuePerson();
			}
		});

		/**
		 * Поле ввода даты для движения по календарю
		 */
		this.calendar = new sw.Promed.SwDateField(
			{
				fieldLabel: lang['data_raspisaniya'],
				plugins:
					[
						new Ext.ux.InputTextMask('99.99.9999', false)
					],
				xtype: 'swdatefield',
				format: 'd.m.Y',
				listeners:
				{
					'keydown': function (inp, e)
					{
						if (e.getKey() == Ext.EventObject.ENTER) {
							e.stopEvent();
							this.calendar.value = Ext.util.Format.date(inp.getValue(), 'd.m.Y');
							this.date = this.calendar.value;
							this.onDateChange();
						}
					}.createDelegate(this),
					'select': function ()
					{
						this.date = this.calendar.value;
						this.onDateChange();
					}.createDelegate(this)
				},
				value: new Date()
			});

		this.tbar = new Ext.Toolbar({
			autoHeight: true,
			buttons: [
				{
					text: lang['predyiduschiy'],
					xtype: 'button',
					iconCls: 'arrow-previous16',
					handler: function()
					{
						// на один день назад
						this.prevDay();
						this.onDateChange();
					}.createDelegate(this)
				},
				this.calendar,
				{
					text: lang['sleduyuschiy'],
					xtype: 'button',
					iconCls: 'arrow-next16',
					style:'margin-left:105px;',
					handler: function()
					{
						// на один день вперед
						this.nextDay();
						this.onDateChange();
					}.createDelegate(this)
				},
				'-',
				{
					iconCls: 'refresh16',
					text: lang['obnovit'],
					tooltip : "Обновить расписание <b>(F5)</b>",
					handler: function () {
						this.loadSchedule(this.calendar.value);
					}.createDelegate(this)
				},
				'-',
				me.btnQueuePerson,
				'-',
				{
					iconCls: 'print16',
					text: lang['pechat'],
					tooltip : "Печать расписания <b>(F9)</b>",
					handler: function () {
						this.printSchedule();
					}.createDelegate(this)
				},
				{
					iconCls: 'print16',
					text: langs('Печать списка пациентов'),
					tooltip: "Печать списка пациентов",
					handler: function() {
						this.printPacList();
					}.createDelegate(this)
				}
			],
			style: "border-bottom: 1px solid #99BBE8;"
		});

		Ext.apply(this, {
			autoScroll: true,
			layout: 'fit',
			keys: [{
				key: [
					Ext.EventObject.F5,
					Ext.EventObject.F9
				],
				fn: function(inp, e) {
					e.stopEvent();
					if ( e.browserEvent.stopPropagation )
						e.browserEvent.stopPropagation();
					else
						e.browserEvent.cancelBubble = true;

					if ( e.browserEvent.preventDefault )
						e.browserEvent.preventDefault();
					else
						e.browserEvent.returnValue = false;

					e.browserEvent.returnValue = false;
					e.returnValue = false;

					if (Ext.isIE)
					{
						e.browserEvent.keyCode = 0;
						e.browserEvent.which = 0;
					}

					switch (e.getKey())
					{

						case Ext.EventObject.F5:
							this.loadSchedule();
							break;

						case Ext.EventObject.F9:
							this.printSchedule();
							break;
					}
				},
				scope: this,
				stopEvent: false
			}]
		});
		sw.Promed.swTTRRecordPanel.superclass.initComponent.apply(this, arguments);

		this.date = Ext.util.Format.date(this.calendar.value, 'd.m.Y');

		this.getTopToolbar().on('render', function(tb){
			me.setUseCase(null);
		});
	}
});