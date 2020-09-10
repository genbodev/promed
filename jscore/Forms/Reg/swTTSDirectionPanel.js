/**
 * Панель выписки направления на койку в стационар
 */
sw.Promed.swTTSDirectionPanel = Ext.extend(sw.Promed.swTTSRecordPanel, {
	id: 'TTSDirectionPanel',

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

		var params = {
			IsForDirection: 1,
			StartDay: this.date,
			LpuSection_id: this.LpuSection_id,
			PanelID: this.id
		}

		//Синергия двух задач
		//https://jira.is-mis.ru/browse/PROMEDWEB-5809
		//https://jira.is-mis.ru/browse/PROMEDWEB-6651
		if ( getRegionNick() == 'ufa' ) {
			params.Person_id = this.personData ? this.personData.Person_id : null;
		}
		this.load(
			{
				url: C_TTS_LISTFORREC,
				params: params,
				scripts:true,
				timeout: 30,
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
	recordPerson: function(time_id, date) {
		this.directionData['time'] = date +' 00:00';
		sw.Promed.Direction.recordPerson({
            userMedStaffFact: this.userMedStaffFact,
			Timetable_id: time_id,
			person: this.personData,
			direction: this.directionData,
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
	queuePerson: function() {
		sw.Promed.Direction.queuePerson({
            userMedStaffFact: this.userMedStaffFact,
			person: this.personData,
			direction: this.directionData,
			order: {}, // если при записи сделан заказ, то передаем его данные
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
		sw.Promed.swTTSDirectionPanel.superclass.initComponent.apply(this, arguments);
    }
});