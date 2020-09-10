/**
 * Панель записи на ресурс на один день
 * наследуется от развернутого расписания на несколько дней
 * TO-DO: Есть ли смысл в отдельном классе?
 */
sw.Promed.swTTRRecordOneDayPanel = Ext.extend(sw.Promed.swTTRRecordPanel, {
	id: 'TTRRecordOneDayPanel',
	frame: false,
	loadMask : true,

	/**
	 * Функция возврашающся ссылку на родительский элемент
	 */
	getOwner: null,

	/**
	 * Идентификатор выбранной бирки
	 */
	TimetableResource_id: null,

	/**
	 * Элемент выбранной бирки
	 */
	TimetableResource_Element: null,

	/**
	 * Дата, на которую отображается расписание
	 */
	date: null,

	/**
	 * Служба, для которой отображается расписание
	 */
	Resource_id: null,


	/**
	 * Маска для загрузки
	 */
	loadMask: null,

	/**
	 * Дополнительные данные по службе/услуге, которые надо передавать в форму заказа комплексной услуги
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
	 * Загрузка расписания
	 *
	 * @param date Дата, начиная с которой загружать расписание
	 */
	loadSchedule: function(date)
	{
		if (date) {
			this.date = date;
		}

		var url = C_TTR_LISTONEDAYFORREC;
		this.load(
			{
				url: url,
				params: {
					readOnly: this.getUseCase().inlist(['show_list_only'])?'on':null,
					StartDay: this.date,
					Resource_id: this.Resource_id,
					PanelID: this.id // отправляем идентификатор панели для правильной генерации HTML
				},
				scripts:true,
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
	 * Печать расписания врача as is
	 */
	printSchedule: function() {
		var id_salt = Math.random();
		var win_id = 'print_ttr_edit' + Math.floor(id_salt * 10000);
		var win = window.open(C_TTR_LISTONEDAYFORRECPRINT + '&StartDay=' + this.date + '&Resource_id=' + this.Resource_id, win_id);
	},

	/**
	 * Перемещение по календарю
	 */
	stepDay: function(day)
	{
		var date = (this.calendar.getValue() || Date.parseDate(this.date, 'd.m.Y')).add(Date.DAY, day).clearTime();
		this.calendar.setValue(Ext.util.Format.date(date, 'd.m.Y'));
		this.date = Ext.util.Format.date(date, 'd.m.Y');

		if ( this.getOwner() && this.getOwner().Wizard && this.getOwner().Wizard.params.date) {
			this.getOwner().Wizard.params.date = this.date;
			this.getOwner().refreshWindowTitle({'changeParams':false});
		}
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

		sw.Promed.swTTRRecordOneDayPanel.superclass.initComponent.apply(this, arguments);
	}
});