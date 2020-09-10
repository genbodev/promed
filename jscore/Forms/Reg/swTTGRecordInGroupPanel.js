/**
 * Панель записи к врачу поликлиники с просмотром одной группы
 * наследуется от развернутого расписания на несколько дней
 * TO-DO: Есть ли смысл в отдельном классе?
 */
sw.Promed.swTTGRecordInGroupPanel = Ext.extend(sw.Promed.swTTGRecordPanel, {
	id: 'TTGRecordInGroupPanel',
	frame: false,
	loadMask : true,

	/**
	 * Функция возврашающся ссылку на родительский элемент
	 */
	getOwner: null,

	/**
	 * Идентификатор выбранной бирки
	 */
	TimetableGraf_id: null,
	
	/**
	 * Элемент выбранной бирки
	 */
	TimetableGraf_Element: null,
	
	/**
	 * Дата, с которой отображается расписание
	 */
	date: null,
	
	/**
	 * Место работы для которого отображается расписание
	 */
	MedStaffFact_id: null,

	/**
	 * Маска для загрузки
	 */
	loadMask: null,
	
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
	 * Загрузка расписания
	 * @param TimeTableGraf_id Групповая бирка
	 * @param date Дата, начиная с которой загружать расписание
	 */
	loadSchedule: function(TimeTableGraf_id)
	{
		if (TimeTableGraf_id) {
			this.Timetable_id = TimeTableGraf_id;
		}

		this.load(
			{
				url: '/?c=TimetableGraf&m=getTimetableGrafGroup',
				params: {
					readOnly: this.getUseCase().inlist(['show_list_only'])?'on':null,
					TimeTableGraf_id: this.TimeTableGraf_id,
					MedStaffFact_id: this.MedStaffFact_id,
					PanelID: this.id // отправляем идентификатор панели для правильной генерации HTML
				},
				scripts:true,
				text: langs('Подождите, идет загрузка расписания...'),
				success: function () {
					//
				},
				failure: function () {
					Ext.Msg.alert(langs('Ошибка'), langs('Ошибка получения расписания. Попробуйте еще раз.'));
				}
			}
		);
	},
	
	/**
	 * Печать расписания врача as is
	 */
	printSchedule: function() {
        if(getGlobalOptions().region.nick == 'kz'){ //https://redmine.swan.perm.ru/issues/39258
            var paramDate = this.date;
            var paramMedStaffFact = this.MedStaffFact_id;

			printBirt({
				'Report_FileName': 'rec_Kartochka.rptdesign',
				'Report_Params': '&paramDate=' + paramDate + '&paramMedStaffFact=' + paramMedStaffFact,
				'Report_Format': 'pdf'
			});
        }
        else{
            var id_salt = Math.random();
            var win_id = 'print_ttg_edit' + Math.floor(id_salt * 10000);
            var win = window.open(C_TTG_LISTONEDAYFORRECPRINT + '&StartDay=' + (this.date?this.date:'') + '&MedStaffFact_id=' + this.MedStaffFact_id, win_id);
        }
	},
	/**
	 * Заглушка
	 * @returns {boolean}
	 */
	openContextMenu: function(){
		return false;
	},
	/**
	 * Перемещение по календарю
	 * Дополнительно обновляется заголовок окна
	 */
	stepDay: function(day)
	{
		var date = (this.calendar.getValue() || Date.parseDate(this.date, 'd.m.Y')).add(Date.DAY, day).clearTime();
		this.calendar.setValue(Ext.util.Format.date(date, 'd.m.Y'));
		this.date = Ext.util.Format.date(date, 'd.m.Y');
		
		if ( this.getOwner() && this.getOwner().Wizard.params.date) {
			this.getOwner().Wizard.params.date = this.date;
			this.getOwner().refreshWindowTitle({'changeParams':false});
		}
	},
	/**
	 * Запись человека на бирку
	 */
	recordPerson: function(time_id, date, time) {
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
			,date: date
			,TimetableType_id: 14
		};
		sw.Promed.Direction.recordPerson(params);
	},
	initComponent: function() {
		
		/**
		 * Поле ввода даты для движения по календарю
		 */
		this.calendar = new sw.Promed.SwDateField(
			{
				fieldLabel: langs('Дата расписания'),
				plugins:
					[
						new Ext.ux.InputTextMask('99.99.9999', false)
					],
				xtype: 'swdatefield',
				format: 'd.m.Y',
				listeners:
					{
						'keydown': function (inp, e) {
							if (e.getKey() == Ext.EventObject.ENTER) {
								e.stopEvent();
								this.loadSchedule(Ext.util.Format.date(inp.getValue(), 'd.m.Y'));
							}
						}.createDelegate(this),
						'select': function () {
							this.loadSchedule(this.calendar.value);
						}.createDelegate(this)
					},
				value: new Date()
			});
		sw.Promed.swTTGRecordInGroupPanel.superclass.initComponent.apply(this, arguments);
		this.getTopToolbar().hide();
	}
});