/**
 * swLpuBuildingScheduleWorkDoctorWindow - Форма «Расписание работы врачей»
 * Форма предназначена для просмотра расписания работы врачей, удобного изменения связей Кабинет – Место работы.
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package     Admin
 * @access      public
 * @copyright	Copyright (c) 2017 Swan Ltd.
 */
sw.Promed.swLpuBuildingScheduleWorkDoctorWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swLpuBuildingScheduleWorkDoctorWindow',
	maximizable: false,
	maximized: true,
	resizable: true,
	title: 'Расписание работы врачей',
	width: 900,

	ScheduleWorkDoctorPanel: null,
	FilterPanel: null,

	// Текущая дата d.m.Y
	curDate: null,
	calendarDate: null,
	mondayDate: null,
	sundayDate: null,

	Lpu_id: null,
	LpuBuilding_id: null,
	LpuSection_id: null,
	LpuSectionProfile_id: null,
	Post_id: null,
	MedStaffFact_id: null,
	LpuBuildingOffice_id: null,
	LpuRegion_id: null,

	_filterFieldCombo: function(field, data){

		field.getStore().removeAll();
		field.clearValue();
		field.getStore().load(data);

		return true;
	},



	getLpuId: function(){
		var me = this;

		if(Ext.isEmpty(me.Lpu_id)){
			me.setLpuId(getGlobalOptions().lpu_id);
		}

		return me.Lpu_id;
	},

	setLpuId: function(Lpu_id){
		var me = this;

		me.Lpu_id = Lpu_id;

		return me;
	},
	_clearParams: function(){
		var me = this;

		me.LpuBuilding_id = null;
		me.LpuSection_id = null;
		me.LpuSectionProfile_id = null;
		me.Post_id = null;
		me.MedStaffFact_id = null;
		me.LpuBuildingOffice_id = null;
		me.LpuRegion_id = null;
	},

	initComponent: function() {

		var me = this;

		var curDate = Date.parseDate(getGlobalOptions().date, 'd.m.Y');

		me.setCurDate(curDate);


		var tbar = me.initTbar();
		var topPanel = me.initTopPanel();
		var scheduleWorkDoctorPanel = me.initScheduleWorkDoctorPanel();


		Ext.apply(this, {
			border: false,
			layout: 'border',
			items: [
				tbar,
				topPanel,
				scheduleWorkDoctorPanel
			],

			// Функциональные кнопки
			buttons: [
				{
					handler: function() {
						me.print();
					},
					iconCls: 'print16',
					text: BTN_FRMPRINT
				},
				{
					text: '-'
				},
				{
					iconCls: 'cancel16',
					text: BTN_FRMCLOSE,
					handler: function() {
						me.hide()
					}
				}
			]
		});

		sw.Promed.swLpuBuildingScheduleWorkDoctorWindow.superclass.initComponent.apply(this, arguments);
	},

	// Toolbar
	initTbar: function(){
		var me = this;

		var calendar = me._initCalendar();

		// Верхняя панель фильтров
		me.tbar = new Ext.Toolbar({
			autoHeight: true,
			buttons: [
				{
					text: langs('Предыдущий'),
					xtype: 'button',
					iconCls: 'arrow-previous16',
					handler: function() {
						// на один день назад
						me.prevPeriod();
						me.doSearch();
					}
				},
				calendar,
				{
					text: langs('Следующий'),
					xtype: 'button',
					iconCls: 'arrow-next16',
					handler: function() {
						// на один день назад
						me.nextPeriod();
						me.doSearch();
					}
				}
			],
			style: "border-bottom: 1px solid #99BBE8;"
		});

		return me.tbar;
	},

	// Календарь периода дат (Понедельник - Воскресенье)
	_initCalendar: function(){
		var me = this;
		var curDate = me.getCurDate();

		me.calendar = new Ext.form.DateRangeField(
			{
				disabled: 'disabled',
				width: 150,
				fieldLabel: langs('Период'),
				plugins: [
					new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)
				]

			});

		me._calendarSetValueFromDate(curDate);

		return me.calendar;
	},

	// Search Form
	initTopPanel: function (){
		var me = this;

		// Панель фильтров
		me.FilterPanel = new Ext.FormPanel({
			id: 'SWD_FilterPanel',
			xtype: 'form',
			keys: [{
				key: Ext.EventObject.ENTER,
				fn: function(e) {
					me.doSearch();
				},
				scope: this,
				stopEvent: true
			}],
			labelAlign: 'right',
			labelWidth: 50,
			autoHeight: true,
			items: [
				{
					xtype: 'fieldset',
					style: 'margin: 5px 0 0 0',
					autoHeight: true,
					title: langs('Фильтр'),
					collapsible: true,
					layout: 'column',
					items: [

						{
							layout: 'column',
							items: [
								{
									layout: 'form',
									labelWidth: 110,
									items: [
										// МО
										{
											layout: 'form',
											labelWidth: 120,
											items: [{
												hiddenName: 'Lpu_id',
												allowBlank: false,
												name: 'Search_LpuId',
												width: 200,
												xtype: 'swlpucombo',
												value: me.getLpuId(),
												disabled: true,
												fieldLabel: 'МО'
											}]

										},

										// Подразделение
										{
											layout: 'form',
											labelWidth: 120,
											items: [{
												allowBlank: false,
												xtype: 'swlpubuildingglobalcombo',
												width: 300,
												hiddenName: 'SWD_LpuBuilding_id',
												name: 'Search_Subdivision',
												fieldLabel: 'Подразделение',
												listeners: {
													'change': function(combo, newValue, oldValue){

														switch(newValue){
															case 0:
																me.setLpuBuildingId(null);
																break;
															default:
																me.setLpuBuildingId(newValue);
																break;
														}


														var params = {
															'Lpu_id': me.getLpuId(),
															'LpuBuilding_id': me.getLpuBuildingId()
														};

														// Отделение
														me.filterLpuSectionCombo(params);

														// Профиль
														me.filterLpuSectionProfileCombo(params);

														// Должность
														me.filterPostCombo(params);

														// Врач (Место работы)
														me.filterMedStaffFactCombo({
															'Lpu_id': me.getLpuId(),
															'LpuBuilding_id': me.getLpuBuildingId,
															'onDate': me.getCurDate()
														});

														// Кабинет
														me.filterLpuBuildingOfficeCombo(params);

														// Участок
														me.filterLpuRegionCombo(params);

														return true;
													}
												}
											}]
										},

										// Отделение
										{
											layout: 'form',
											labelWidth: 120,
											items: [{
												hiddenName: 'SWD_LpuSection_id',
												xtype: 'swlpusectioncombo',
												width: 300,
												name: 'Search_Separation',
												fieldLabel: 'Отделение',
												listeners: {
													'change': function(combo, newValue, oldValue){
														me.setLpuSectionId(newValue);

														var params = {
															'Lpu_id': me.getLpuId(),
															'LpuBuilding_id': me.LpuBuilding_id,
															'LpuSection_id': me.LpuSection_id
														};

														// Должность
														me.filterPostCombo(params);



														return true;
													}
												}
											}]
										},

										// Профиль
										{
											layout: 'form',
											labelWidth: 120,
											items: [{
												xtype: 'swlpusectionprofilecombo',
												hiddenName: 'SWD_LpuSectionProfile_id',
												width: 250,
												name: 'Search_Profile',
												fieldLabel: 'Профиль',
												listeners: {
													'change': function(combo, newValue, oldValue){
														me.setLpuSectionProfileId(newValue);



														return true;
													}
												}
											}]
										},


										// Кнопки
										{
											layout: 'column',
											items: [

												// Найти
												{
													layout: 'form',
													items: [{
														style: "padding-left: 20px",
														xtype: 'button',
														id: me.id + 'BtnSearch',
														text: 'Найти',
														iconCls: 'search16',
														handler: function () {
															me.doSearch();
														}
													}]
												},

												// Сброс
												{
													layout: 'form',
													items: [{
														style: "padding-left: 10px",
														xtype: 'button',
														id: me.id + 'BtnClear',
														text: 'С<u>б</u>рос',
														iconCls: 'reset16',
														handler: function () {
															me.doReset();
														}
													}]
												}
											]
										}

									]
								}
							]
						},
						{
							layout: 'column',
							items: [
								{
									layout: 'form',
									labelWidth: 110,
									items: [

										// Должность
										{
											layout: 'form',
											labelWidth: 120,
											items: [{
												hiddenName: 'SWD_Post_id',
												xtype: 'swpostmedlocalcombo',
												width: 250,
												name: 'Search_Position',
												fieldLabel: 'Должность',
												listeners: {
													'change': function(combo, newValue, oldValue){
														me.setPostId(newValue);



														return true;
													}
												}
											}]
										},

										// Врач
										{
											layout: 'form',
											labelWidth: 120,
											items: [{
												hiddenName: 'SWD_MedStaffFact_id',
												xtype: 'swmedstafffactglobalcombo',
												width: 400,
												name: 'Search_Doctor',
												fieldLabel: 'Врач',
												listeners: {
													'change': function(combo, newValue, oldValue){
														me.setMedStaffFactId(newValue);



														return true;
													}
												}
											}]
										},



										// Кабинет
										{
											layout: 'form',
											labelWidth: 120,
											items: [{
												hiddenName: 'SWD_LpuBuildingOffice_id',
												xtype: 'swlpubuildingofficecombo',
												width: 200,
												name: 'Search_Cabinet',
												fieldLabel: 'Кабинет',
												listeners: {
													'change': function(combo, newValue, oldValue){
														me.setLpuBuildingOfficeId(newValue);



														return true;
													}
												}

											}]
										},

										// Участок
										{
											layout: 'form',
											labelWidth: 120,
											items: [{
												hiddenName: 'SWD_LpuRegion_id',
												xtype: 'swlpuregioncombo',
												width: 200,
												name: 'Search_Sector',
												fieldLabel: 'Участок',
												listeners: {
													'change': function(combo, newValue, oldValue){
														me.setLpuRegionId(newValue);

														return true;
													}
												}
											}]
										}


									]
								}
							]
						},

					]
				}
			]
		});

		me.TopPanel = new Ext.Panel({
			region: 'north',
			frame: true,
			border: false,
			autoHeight: true,
			items: [
				me.FilterPanel
			]
		});

		return me.TopPanel;
	},

	// Область данных
	initScheduleWorkDoctorPanel: function(){
		var me = this;

		me.ScheduleWorkDoctorPanel = new Ext.Panel({
			id:'ScheduleWorkDoctor',
			frame: false,
			border: false,
			region: 'center',
			loadMask : true
		});

		return me.ScheduleWorkDoctorPanel;
	},



	getCurDate: function(){return this.curDate;},
	setCurDate: function(v){this.curDate = v; return this;},


	getCalendarDate: function(){return this.calendarDate;},
	setCalendarDate: function(v){this.calendarDate = v; return this;},


	getMondayDate: function(){return this.mondayDate;},
	setMondayDate: function(v){this.mondayDate = v; return this;},

	getSundayDate: function(){return this.sundayDate;},
	setSundayDate: function(v){this.sundayDate = v; return this;},


	getLpuBuildingId: function(){return this.LpuBuilding_id;},
	setLpuBuildingId: function(v){this.LpuBuilding_id = v;return this;},
	// Фильтр выпадающего списка "Подразделение"
	filterLpuBuildingCombo: function(params) {
		var me = this;

		var FilterPanel = me.FilterPanel.getForm();
		me._filterFieldCombo(FilterPanel.findField('SWD_LpuBuilding_id'), {params: params});
		return true;
	},


	getLpuSectionId: function(){return this.LpuSection_id;},
	setLpuSectionId: function(v){this.LpuSection_id = v;return this;},
	// Фильтр выпадающего списка "Отделение"
	filterLpuSectionCombo: function(params) {
		var me = this;

		var FilterPanel = me.FilterPanel.getForm();
		me._filterFieldCombo(FilterPanel.findField('SWD_LpuSection_id'), {params: params});
		return true;
	},

	getLpuSectionProfileId: function(){return this.LpuSectionProfile_id;},
	setLpuSectionProfileId: function(v){this.LpuSectionProfile_id = v;return this;},
	// Фильтр выпадающего списка "Профиль"
	filterLpuSectionProfileCombo: function(params) {
		var me = this;
		var FilterPanel = me.FilterPanel.getForm();
		me._filterFieldCombo(FilterPanel.findField('SWD_LpuSectionProfile_id'), {params: params});
		return true;
	},


	getPostId: function(){return this.Post_id;},
	setPostId: function(v){this.Post_id = v;return this;},
	// Фильтр выпадающего списка "Должность"
	filterPostCombo: function(params) {
		var me = this;
		var FilterPanel = me.FilterPanel.getForm();
		me._filterFieldCombo(FilterPanel.findField('SWD_Post_id'), {params: params});

		return true;
	},


	getMedStaffFactId: function(){return this.MedStaffFact_id;},
	setMedStaffFactId: function(v){this.MedStaffFact_id = v;return this;},
	// Фильтр выпадающего списка "Врач" (место работы)
	filterMedStaffFactCombo: function(params) {
		var me = this;
		var FilterPanel = me.FilterPanel.getForm();
		me._filterFieldCombo(FilterPanel.findField('SWD_MedStaffFact_id'), {params: params});
		return true;
	},


	getLpuBuildingOfficeId: function(){return this.LpuBuildingOffice_id;},
	setLpuBuildingOfficeId: function(v){this.LpuBuildingOffice_id = v;return this;},
	// Фильтр выпадающего списка "Кабинет"
	filterLpuBuildingOfficeCombo: function(params) {
		var me = this;
		var FilterPanel = me.FilterPanel.getForm();
		me._filterFieldCombo(FilterPanel.findField('SWD_LpuBuildingOffice_id'), {params: params});

		return true;
	},


	getLpuRegionId: function(){return this.LpuRegion_id;},
	setLpuRegionId: function(v){this.LpuRegion_id = v;return this;},
	// Фильтр выпадающего списка "Участок"
	filterLpuRegionCombo: function(params) {
		var me = this;
		var FilterPanel = me.FilterPanel.getForm();
		me._filterFieldCombo(FilterPanel.findField('SWD_LpuRegion_id'), {params: params});
		return true;
	},



	/**
	 * Устанавливаем даты календаря на предыдущую неделю
	 *
	 * ВАЖНО!! Функция не запускает поиск
	 *
	 * @returns {boolean}
	 */
	prevPeriod: function(){
		var me = this;

		me._stepDay(-7);

		return true;
	},




	/**
	 * Устанавливаем даты календаря на следующую неделю
	 *
	 * ВАЖНО!! Функция не запускает поиск
	 *
	 * @returns {boolean}
	 */
	nextPeriod: function(){
		var me = this;

		me._stepDay(7);

		return true;
	},



	/**
	 * Устанавливаем значение календаря от переданной даты.
	 * Полученную дату используем для поиска понедельника и воскресенья
	 *
	 * @param date
	 * @returns {boolean}
	 * @private
	 */
	_calendarSetValueFromDate: function(date){
		var me = this;

		var monday = me._findMonday(date).format('d.m.Y');
		me.setMondayDate(monday);

		var sunday = me._findSunday(date).format('d.m.Y');
		me.setSundayDate(sunday);

		me.calendar.setValue(monday + ' - ' + sunday);


		me.setCalendarDate(date);

		return true;
	},


	/**
	 * Сдвигаем дату календаря на количество переданных дней
	 *
	 * @param day
	 * @returns {boolean}
	 * @private
	 */
	_stepDay: function(day){
		var me = this;

		var calendarDate = me.getCalendarDate();

		var date = calendarDate.add(Date.DAY, day).clearTime();

		me._calendarSetValueFromDate(date);

		return true;
	},





	//==================================================================================================================
	// Вспомогательные функции


	/**
	 * Поиск даты понедельника
	 *
	 * @param d - дата ()
	 * @returns {Date}
	 * @private
	 */
	_findMonday: function(d){
		d = new Date(d);

		var day = d.getDay();

		var diff = d.getDate() - day + (day == 0 ? -6:1);

		return new Date(d.setDate(diff));
	},

	/**
	 * Поиск даты воскресенья
	 *
	 * @param d
	 * @returns {Date}
	 * @private
	 */
	_findSunday: function(d){
		d = new Date(d);

		var day = d.getDay();

		var diff = d.getDate() + (7 - (day == 0 ? 7:day));

		return new Date(d.setDate(diff));
	},
	//==================================================================================================================










	//==================================================================================================================
	// Методы


	/**
	 * Открытие формы
	 *
	 * @returns {boolean}
	 */
	show: function() {
		var me = this;

		sw.Promed.swLpuBuildingScheduleWorkDoctorWindow.superclass.show.apply(this, arguments);


		// Фильтруем сразу выпадающий список "Подразделение" т.к. выпадающий список МО по умолчанию выбран и недоступен для редактирования.
		me.filterLpuBuildingCombo({
			'Lpu_id': me.getLpuId()
		});

		return true;
	},



	/**
	 * Поиск
	 *
	 * @returns {boolean}
	 * @private
	 */
	doSearch: function(){
		var me = this;


		if (Ext.isEmpty(me.getLpuId()) || Ext.isEmpty(me.getLpuBuildingId())) {
			sw.swMsg.alert('Ошибка', 'Для поиска требуется указать "Подразделение"');
			return false;
		}


		var params = {
			Lpu_id: me.getLpuId(),
			LpuBuilding_id: me.getLpuBuildingId(),
			LpuSection_id: me.getLpuSectionId(),
			LpuSectionProfile_id: me.getLpuSectionProfileId(),
			Post_id: me.getPostId(),
			MedStaffFact_id: me.getMedStaffFactId(),
			LpuBuildingOffice_id: me.getLpuBuildingOfficeId(),
			LpuRegion_id: me.getLpuRegionId(),
			mondayDate: me.getMondayDate(),
			sundayDate: me.getSundayDate()
		};


		me.getLoadMask().show(LOAD_WAIT);
		me.ScheduleWorkDoctorPanel.load({
			url: C_LBOMSL_LOAD_SWD,
			params: params,
			scripts:true,
			text: langs('Подождите, идет загрузка расписания работы врачей...'),
			callback: function(){},
			failure: function(){
				Ext.Msg.alert(langs('Ошибка'), langs('Ошибка получения расписания работы врачей. Попробуйте еще раз.'));
			}
		});
		me.getLoadMask().hide();

		return true;
	},


	/**
	 * Сброс данных формы поиска
	 *
	 * @returns {boolean}
	 */
	doReset: function() {
		var me = this;

		sw.Promed.swWorkPlacePolkaRegWindow.superclass.doReset.apply(me, arguments); // выполняем базовый метод

		me._clearParams();

		return true;
	},


	/**
	 * Открытие формы "Параметры печати"
	 */
	print: function(){
		var filterPanel = this.FilterPanel.getForm();
		var params = {};
		if ( filterPanel.findField('SWD_LpuBuilding_id').getValue() ){
			params.LpuBuilding_id = filterPanel.findField('SWD_LpuBuilding_id').getValue();
		}
		getWnd('swLpuBuildingOfficeMedStaffLinkPrintWindow').show(params);
	}
	//==================================================================================================================


});