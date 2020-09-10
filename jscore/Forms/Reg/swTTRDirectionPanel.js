/**
 * Панель выписки направления на службу/услугу
 */
sw.Promed.swTTRDirectionPanel = Ext.extend(sw.Promed.swTTRRecordPanel, {
	id: 'TTRDirectionPanel',

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
		if (Ext.isEmpty(this.Resource_id)) {
			return false;
		}

		var url = C_TTR_LISTFORREC;
		this.load(
			{
				url: url,
				params: {
					IsForDirection: 1,
					StartDay: this.date,
					Resource_id: this.Resource_id,
					PanelID: this.id // отправляем идентификатор панели для правильной генерации HTML
				},
				scripts:true,
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
	},


	/**
	 * Запись человека на бирку
	 */
	recordPerson: function(time_id, date, time)
	{
		this.directionData['time'] = (date && time)?(date +' '+ time):null;
		sw.Promed.Direction.recordPerson({
			userMedStaffFact: this.userMedStaffFact,
			Timetable_id: time_id,
			person: this.personData,
			direction: this.directionData,
			order: this.order, // если при записи сделан заказ, то передаем его данные
			callback: this.onDirection || Ext.emptyFn,
			onSaveRecord: this.onSaveRecord || Ext.emptyFn,
			onHide: null,
			needDirection: true,
			fromEmk: false, // что это?
			mode: 'nosave',
			loadMask: true,
			windowId: 'swDirectionMasterWindow'
		});
	},

	/**
	 * Помещение человека в очередь
	 */
	queuePerson: function()
	{
		sw.Promed.Direction.queuePerson({
			userMedStaffFact: this.userMedStaffFact,
			person: this.personData,
			direction: this.directionData,
			order: this.order, // если при записи сделан заказ, то передаем его данные
			callback: this.onQueue || Ext.emptyFn,
			onHide: Ext.emptyFn,
			needDirection: true,
			fromEmk: false, // что это?
			mode: 'nosave',
			loadMask: true,
			windowId: 'swDirectionMasterWindow'
		});
	},

	initComponent: function() {
		sw.Promed.swTTRDirectionPanel.superclass.initComponent.apply(this, arguments);
	}
});