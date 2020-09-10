/**
 * Панель выписки направлений к врачу поликлиники
 */
sw.Promed.swTTGDirectionPanel = Ext.extend(sw.Promed.swTTGRecordPanel, {
	id: 'TTGDirectionPanel',
	
	/**
	 * Функция вызываемая после успешной записи 
	 */
	onDirection: Ext.emptyFn,
	
	/**
	 * Функция вызываемая после успешной постановки в очередь
	 */
	onQueue: Ext.emptyFn,
	
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

		this.load(
			{
				url: C_TTG_LISTFORREC,
				params: {
					IsForDirection: 1,
					StartDay: this.date,
					MedStaffFact_id: this.MedStaffFact_id,
					PanelID: this.id // отправляем идентификатор панели для правильной генерации HTML
				},
				scripts:true,
				text: lang['podojdite_idet_zagruzka_raspisaniya'],
				success: function () {
					//
				},
				failure: function () {
					Ext.Msg.alert(lang['oshibka'], lang['oshibka_polucheniya_raspisaniya_poprobuyte_esche_raz']);
				}
			}
		);
	},
	
	/**
	 * Запись человека на бирку
	 */
	recordPerson: function(time_id, date, time) {
		this.directionData['time'] = (date && time)?(date +' '+ time):null;
		//sw.Promed.MedStaffFactByUser.current.ARMType_id
		sw.Promed.Direction.recordPerson({
            userMedStaffFact: this.userMedStaffFact,
			Timetable_id: time_id,
			person: this.personData,
			direction: this.directionData,
			callback: this.onDirection || Ext.emptyFn,
			onSaveRecord: this.onSaveRecord || Ext.emptyFn,
			onHide: null,
			needDirection: this.isHimSelf?false:true,
			fromEmk: false, // что это?
			mode: 'nosave',
			loadMask: true,
			windowId: 'swDirectionMasterWindow'
		});
	},
	
	
	/**
	 * Помещение человека в очередь
	 */
	queuePerson: function() {
		sw.Promed.Direction.queuePerson({
            userMedStaffFact: this.userMedStaffFact,
			person: this.personData,
			direction: this.directionData,
			order: {}, // если при записи сделан заказ, то передаем его данные
			callback: this.onQueue || Ext.emptyFn,
			onHide: Ext.emptyFn,
			needDirection: this.isHimSelf?false:true,
			fromEmk: false, // что это?
			mode: 'nosave',
			loadMask: true,
			windowId: 'swDirectionMasterWindow'
		});
	},
	/**
	 * Получение ссылки на объект контекстного меню
	 */
	getContextMenu : function () {
		return this.menuContext;
	},
	/**
	 * Открытие контекстного меню для редактирования бирки
	 */
	openContextMenu : function ( el, TimeTableGraf_id, PersonId, evndirection_id, IsInetUser, date, time ) {
		this.TimeTableGraf_id = TimeTableGraf_id;
		this.Timetable_Element = el;
		this.EvnDirection_id = evndirection_id;
		this.Date = date;
		this.Time = time;
		this.getContextMenu().show(el);
	},
	initComponent: function() {

		sw.Promed.swTTGDirectionPanel.superclass.initComponent.apply(this, arguments);
		/**
		 * Меню редактирования бирки
		 */
		this.menuContext = new Ext.menu.Menu({
			items: [
				{
					id: 'view-group',
					text: langs('Список записанных'),
					icon: 'img/icons/edit16.png'
				}
			],
			listeners: {
				itemclick: function(item) {
					switch (item.id) {
						case 'view-group':
							this.getOwner().openGroupListTTG(this.TimeTableGraf_id, this.Date, this.Time);
							break;
					}
				}.createDelegate(this)
			}
		});

	}
});