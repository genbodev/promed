/**
 * Панель записи к врачу поликлиники с просмотром одного дня
 * наследуется от развернутого расписания на несколько дней
 * TO-DO: Есть ли смысл в отдельном классе?
 */
sw.Promed.swTTGRecordOneDayPanel = Ext.extend(sw.Promed.swTTGRecordPanel, {
	id: 'TTGRecordOneDayPanel',
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
				url: C_TTG_LISTONEDAYFORREC,
				params: {
					readOnly: this.getUseCase().inlist(['show_list_only'])?'on':null,
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
            var win = window.open(C_TTG_LISTONEDAYFORRECPRINT + '&StartDay=' + (this.date?this.date:'') + '&MedStaffFact_id=' + this.MedStaffFact_id + '&PanelID=TTGRecordOneDayPanel', win_id);
        }
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
						this.loadSchedule(Ext.util.Format.date(inp.getValue(), 'd.m.Y'));
					}
				}.createDelegate(this),
				'select': function () 
				{
					this.loadSchedule(this.calendar.value);
				}.createDelegate(this)
			},
			value: new Date()
		});
		
	    sw.Promed.swTTGRecordOneDayPanel.superclass.initComponent.apply(this, arguments);
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