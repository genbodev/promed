/**
 * swEvnReanimatPeriodEditWindow - окно - форма ввода/редактирования «Реанимационного периода» 
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Hospital
 * @access			public
 * @copyright		Copyright (c) 2017 Swan Ltd.
 * @author			Muskat Boris (bob@npk-progress.com)
 * @version			05.05.2017
 * C:\Zend\Promed\jscore\Forms\Hospital\swEvnReanimatPeriodEditWindow.js
 */

//initComponent -- инициализация компонент
//show -- запуск формы
//AuscultatoryBuild() -- исование панелей дахания аускультативноG

//ScalePanel_load(SpravName, EvnScaleSprav ) -- загрузка шкал	
//EvnScale_view: function()-- загрузка панели события расчёта по шкале
//EvnScale_Add: function() -- создание нового события расчёта по шкале
//EvnScale_Del: function() -- удаление шкалы
//EvnScale_Save: function(b,e) -- сохранение события расчёта по шкале
//
//EvnReanimatAction_view: function() --загрузка просмотра/редактирования реанимационного мероприятия
//SetCardPulmData: function(CardPulmData, RadioEventExec) -- отображение сведений сердечно-лёгочной реанимации
//EvnReanimatAction_Add: function() -- добавление реанимационного мероприятия
//EvnReanimatAction_Edit: function() -- редактирование реанимационного мероприятия  //BOB - 04.07.2019
//EvnReanimatAction_RateAdd: function() -- добавление строки в таблице измерений
//EvnReanimatAction_RateDel: function()	-- удаление строки в таблице измерений
//EvnReanimatAction_RateCopy: function(src) -- возвращает объекта типа записи измерений на основе другого объекта
//EvnReanimatAction_Del: function() -- удаление реанимационного мероприятия	
//EvnReanimatAction_Save: function(b,e)-- сохранение события реанимационного мероприятия
//EvnReanimatAction_ButtonManag: function(old_rec, EvnRAGridRowData, from)процедура управления кнопками раздела наблюдений   //BOB - 04.07.2019
//EvnReanimatAction_PrintUpDoun: function()	-- функция печати документа по катетеризации верх/низ
//CardPulm_Print: function() -- печать формы сердечно-лёгочной реанимации
//
//EvnReanimatCondition_view: function()  --загрузка просмотра/редактирования регулярного наблюдения состояния
//EvnReanimatCondition_GetNutritious: function(EvnRCGridRowData) - извлечение сведений о мероприятии - питании и отображение в наблюдениях
//EvnReanimatCondition_Add: function() -- добавление регулярного наблюдения состояния
//EvnReanimatCondition_Copy: function() -- добавление регулярного наблюдения состояния на основе существующего
//EvnReanimatCondition_Edit: function() -- активизация элементов панели наблюдения для редактирования
//уEvnReanimatCondition_Del: function() -- даление регулярного наблюдения состояния
//EvnReanimatCondition_Save: function(b,e) -- сохранение регулярного наблюдения состояния
//EvnReanimatCondition_ButtonManag: function(win,old_rec) - управление кнопками раздела наблюдений
//EvnReanimatCondition_Print: function() -- печать документа поступление/дневник/эпикриз  верх/низ
//
//EvnReanimatPeriod_Save: function() -- cсохранение изменений реанимационного периода
//
//EvnRC_AntropometrAdd: function(object) -- добавление антропометриченских данных //BOB - 24.01.2019
//EvnRC_AntropometrLoud: function(Evn_disDate, Evn_disTime) -- агрузка антропометриченских данных //BOB - 24.01.2019
	
//ReanimatPrescr_Add: function(PrescriptionType_id) -- открытие окна добавление НАЗНАЧЕНИЯ //BOB - 22.04.2019
//ReanimatPrescr_Edit: function(PrescriptionType_id, EvnPrescr_id) -- открытие окна редактирования НАЗНАЧЕНИЯ //BOB - 22.04.2019
//ReanimatPrescr_Cancel: function(PrescriptionType_id, EvnPrescr_id) -- отмена НАЗНАЧЕНИЯ //BOB - 22.04.2019
//ReanimatPrescrDirection_View: function(EvnRPGridRowData) -- просмотр направления
//ReanimatDirectionResult_View: function(EvnRPGridRowData) -- просмотр результатов
//ReanimatPeriodPrescrLink_Save: function(params) -- создание прикрепления назначения к РП //BOB - 22.04.2019
//
//ReanimatDirection_View: function(EvnRPGridRowData) -- просмотр направления в назначениях
//ReanimatDirect_Add: function() -- открытие окна добавление НАПРАВЛЕНИЯ //BOB - 22.04.2019
//ReanimatPeriodDirectLink_Save: function(params)	-- создание прикрепления направлений к РП //BOB - 22.04.2019
//ReanimatDirect_Cancel: function(EvnRPGridRowData)	-- Отменить направление
//ReanimatDirectDoc_Add: function(EvnRPGridRowData)	-- Добавить документ - прикрепление документа к направлению
//ReanimatDirectDoc_Del: function(EvnRPGridRowData)	-- открепление документа от направления
//ReanimatDirectBlank: function(EvnRPGridRowData, SrcHandl) -- заполнение просмотр / редактирование бланка к направлению
//
//ReanimatDrugCourse_Add_Edit: function() -- открытие окна добавление и редактирования курса лечения //BOB - 07.11.2019
//ReanimatDrugCourse_Save: function(data) -- создание прикрепления курса лечения к РП //BOB - 07.11.2019
//ReanimatDrugCourse_Cancel: function(PrescriptionType_id, EvnCourse_id) -- отмена КУРСА //BOB - 07.11.2019
//ReanimatPrescrTreatDrug_Edit: function(action) -- открытие окна редактирования назначения в рамках курса лечения //BOB - 07.11.2019
//ReanimatPrescrTreatDrug_Cancel: function() - отмена назначения в рамках курса лечения //BOB - 07.11.2019
//ReanimatPrescrTreatDrug_Exec: function(evn) -	выполнение назначения в рамках курса лечения //BOB - 07.11.2019
//ReanimatPrescrTreatDrug_UnExec: function(evn) - отмена выполнения назначения в рамках курса лечения //BOB - 07.11.2019
//ReanimatPrescrTreatDrug_ButtonManag: function(record) - управление кнопками грида назначения в рамках курса лечения //BOB - 07.11.2019

//getAge: function (dateString, form ) -- функция расчёта полных лет
//getAge_month: function (dateString, form ) -- функция расчёта полных месяцев	
//checkTime(hours, minutes)  --  контроль времени //BOB - 12.07.2019
//checkDate(days, months, years, min_y, max_y) -- контроль даты //BOB - 12.07.2019
//sleep(ms) -- задержка
//isNeonatal(ReanimatAgeGroup) -- отображение по Неонатальному варианту?


//КЛАСС, РЕАЛИЗУЮЩИЙ ПАНЕЛЬ КАЖДОГО ПАРАМЕТРА ШКАЛ
//sw.Promed.SwScaleParameter = function(config) - конструктор класса
//Ext.extend(sw.Promed.SwScaleParameter, Ext.Panel,... - описание класса




//ВИДЫ РЕАНИМАЦИОННЫХ МЕРОПРИЯТИЙ
//1		lung_ventilation	Искусственная вентиляция лёгких
//2		vazopressors	Применение вазопрессоров
//3		nutrition	Питание
//4		hemodialysis	Гемодиализ
//5		endocranial_sensor	Использование датчика внутричерепного давления
//6		invasive_hemodynamics	Инвазивная гемодинамика
//7		epidural_analgesia	Эпидуральная анальгезия
//8		antifungal_therapy	Противогрибковая терапия эхинокандинами
//9		observation_saturation	Наблюдение сатурации гемоглобина
//10	catheterization_veins	Катетеризация центральных вен
//11	card_pulm	Сердечно-лёгочная реанимация
//12	sedation	Седативная терапия


sw.Promed.swEvnReanimatPeriodEditWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swEvnReanimatPeriodEditWindow',
	objectName:'swEvnReanimatPeriodEditWindow',
	objectSrc:'/jscore/Forms/Hospital/swEvnReanimatPeriodEditWindow.js',
	
	
	
	width:1350,//1500,
	height:650,//850,
	maximizable:true,
	minWidth:1350,//1500,
	minHeight:650,//850,
	//maximized: true,	// делается максимальным при открытии 
    resizable: true,  // возможность делать максимальным
	layout:'border',


	modal: true,
	action:null,
	from: '',
	callback: Ext.emptyFn,
	changedDates:false,

	EvnReanimatPeriod_id: '',
	EvnReanimatPeriod_pid: '',
	EvnReanimatPeriod_rid: '',
	Lpu_id:'',
	LpuSection_id:'',
	Person_id:'',
	PersonEvn_id:'',
	Server_id:'',
	Diag_id:'',
	MedPersonal_id:'',
	MedStaffFact_id:'',
	MedPersonal_FIO:'',
	//нужна ещё и должность
	MedService_Name:'',
	EvnXmlRecordList: '',
	ReanimatPeriod_isClosed: false,
	erp_data: null,
	pers_data: null,
	ConditionGridLoadRawNum: 0,
	PrescrGridLoadRawNum: 0,
	DirectGridLoadRawNum: 0,
	DrugCourseGridLoadRawNum: 0, //BOB - 07.11.2019
	PrescrTreatDrugGridLoadRawNum: 0, //BOB - 07.11.2019
	ScaleGridLoadRawNum: 0,
	ActionGridLoadRawNum: 0,
	FirstConditionLoad: true,
	SideType: [],
	ERPEW_NSI: null,

	listeners:{
		'hide':function (win) {
			win.findById('swERPEW_EvnReanimatCondition_Grid').store.data.clear();   //23.10.2019
			win.findById('swERPEW_Scales_Panel').collapse();
			win.findById('swERPEW_Scales_Panel').isLoaded = false;
			win.findById('swERPEW_EvnScales_Grid').store.data.clear();   //23.10.2019
			win.findById('swERPEW_ReanimatAction_Panel').collapse();
			win.findById('swERPEW_ReanimatAction_Panel').isLoaded = false;
			win.findById('swERPEW_ReanimatAction_Grid').store.data.clear();			//23.10.2019
			win.findById('swERPEW_ReanimatPrescr_Panel').collapse();
			win.findById('swERPEW_ReanimatPrescr_Panel').isLoaded = false;
			win.findById('swERPEW_ReanimatPrescr_Grid').store.data.clear();   //23.10.2019
			win.findById('swERPEW_ReanimatDirect_Panel').collapse();
			win.findById('swERPEW_ReanimatDirect_Panel').isLoaded = false;
			win.findById('swERPEW_ReanimatDirect_Grid').store.data.clear();   //23.10.2019
			win.findById('swERPEW_ReanimatDrugCourse_Panel').collapse();   //07.12.2019
			win.findById('swERPEW_ReanimatDrugCourse_Panel').isLoaded = false;   //07.12.2019
			win.findById('swERPEW_ReanimatDrugCourse_Grid').store.data.clear();   //07.12.2019
			win.findById('swERPEW_RepositoryObserv_Panel').collapse();
			win.findById('swERPEW_RepositoryObserv_Panel').isLoaded = false;
			win.findById('swERPEW_RepositoryObserv_Grid').getGrid().getStore().removeAll();
			// win.findById('swERPEW_ReanimatPrescrTreatDrug_Grid').store.data.clear();   //07.12.2019
		},
		'maximize':function (win) {
			//перестройка панелей при максимизации окна, чтобы растянуть гриды
			win.findById('swERPEW_Scales_Panel').doLayout();
			win.findById('swERPEW_ReanimatAction_Panel').doLayout();
			win.findById('swERPEW_Condition_Panel').doLayout();
			win.findById('swERPEW_ReanimatPrescr_Panel').doLayout();
			win.findById('swERPEW_ReanimatDirect_Panel').doLayout();
			win.findById('swERPEW_ReanimatDrugCourse_Panel').doLayout();   //07.12.2019
			win.findById('swERPEW_RepositoryObserv_Panel').doLayout();
		},
		'restore':function (win) {
			//перестройка панелей при сжатии окна, чтобы сжать гриды
			win.fireEvent('maximize', win);
		},
		//BOB - 22.04.2019
		success: function(source, params) {
	 	/* source - string - источник события (например форма)
	 	 * params - object - объект со свойствами в завис-ти от источника
	 	 */
			console.log('BOB__success_source=',source); //BOB - 22.04.2019
			console.log('BOB__success_params',params); //BOB - 22.04.2019
			
			if (source == 'EvnPrescrUslugaInputWindow')
				this.ReanimatPeriodPrescrLink_Save(params);
			else if (source == 'UslugaComplexMedServiceListWindow')
				this.ReanimatPeriodDirectLink_Save(params);
		}
		//BOB - 22.04.2019
	},


	initComponent: function() {
		
        var win = this; // текущий объект

		//Панель Персональные данные / диагноз  /  профильное отделение	
		this.PersonPanel = new Ext.Panel({
					layout:'form',
					border:false,
					height:75,
					autoheight:true,
					autoScroll:true,

					//	frame: true,
					style: 'border-top: 1px solid #99bbe8; border-left: 1px solid #99bbe8; border-bottom: 1px solid #ffffff;border-right: 1px solid #ffffff; padding-bottom: 5px',
					bodyStyle:'background-color: transparent',
					region:'north',
					items:[						
						{							
							layout:'column',
							bodyStyle:'background-color: transparent',
							border:false,
							width:1000,
							items:[
								//панель - Персональные данные
								{									
									layout:'form',
									width: 570, //470,
									border:false,
									items:[
										new sw.Promed.PersonInformationPanelShort({
											id:'swERPEW_PersonInfo',
											region: 'north'
										})
									]
								},
								//Лейбл номер КВС
								{									
									layout:'form',
									style:'margin-top: 12px;',
									width: 45,
									border:false,
									bodyStyle:'background-color: transparent',
									items:[
										new Ext.form.Label({
											text: 'КВС №:'
										})					
									]
								},
								//номер КВС
								{									
									layout:'form',
									style:'margin-top: 12px; color: blue; font-weight: bold;',
									width: 100,
									border:false,
									bodyStyle:'background-color: transparent',
									items:[
										new Ext.form.Label({
											id: 'swERPEW_EvnPS_NumCard'
										})					
									]
								}
							]
						},  
						
						
						
						//панель - сведения из движения
						{							
							layout:'column',
							border:false,
							width:1000,
							bodyStyle:'background-color: transparent',
							items:[
								//Лейбл Профильное отделение
								{									
									layout:'form',
									style:'margin-left: 12px; ',
									width: 130,
									border:false,
									bodyStyle:'background-color: transparent',
									items:[
										new Ext.form.Label({
											text: 'Профильное отделение:'
										})					
									]
								},
								//Профильное отделение
								{									
									layout:'form',
									style:'color: blue;',
									width: 300,
									border:false,
									bodyStyle:'background-color: transparent',
									items:[
										new Ext.form.Label({
											id: 'swERPEW_LpuSection_Name'
										})					
									]
								},
								//Лейбл дата поступления в отделение
								{									
									layout:'form',
									style:'margin-left: 5px; ',
									width: 10,
									border:false,
									bodyStyle:'background-color: transparent',
									items:[
										new Ext.form.Label({
											text: 'с:'
										})					
									]
								},
								// дата поступления в отделение
								{									
									layout:'form',
									style:'color: blue;',
									width: 60,
									border:false,
									bodyStyle:'background-color: transparent',
									items:[
										new Ext.form.Label({
											id: 'swERPEW_EvnSection_setDate'
										})					
									]
								},
								//Лейбл Основной диагноз
								{									
									layout:'form',
									style:'margin-left: 5px; ',
									width: 130,
									border:false,
									bodyStyle:'background-color: transparent',
									items:[
										new Ext.form.Label({
											text: 'Основной диагноз:'
										})					
									]
								},
								//Основной диагноз
								{									
									layout:'form',
									style:'color: blue;',
									width: 300,
									border:false,
									bodyStyle:'background-color: transparent',
									items:[
										new Ext.form.Label({
											id: 'swERPEW_swERPEW_BaseDiag'
										})					
									]
								}
							]							
						}
					]
				});



//Основная Панель всех данных о ходе реанимационного периода
		this.FormPanel = new Ext.form.FormPanel({
			name: 'swERPEW_Form',
			id: 'swERPEW_Form',
			//url: '/?c=EvnSection&m=saveEvnSection',

			autoScroll:true,
			autoheight:true,
			bodyBorder:false,
			//bodyStyle:'padding: 5px 5px 0',
			border:false,
			resizable: true,
			buttonAlign: 'left',
			frame: true,
			labelAlign: 'right',
			region:'center',
			
			items: [
				
//Панель служба / начало-конец периода  /  показания к переводу в реанимацуию - исход									
				{
				//	xtype: 'panel', 
					id: 'swERPEW_GenralData',
					layout:'form',
					border:true,
					autoScroll:true,
					style: 'border-top: 1px solid #99bbe8; border-left: 1px solid #99bbe8; border-bottom: 1px solid #ffffff;border-right: 1px solid #ffffff; padding: 2px; ',
					//width:1475,
					items:[
						
						
						
						//панель реанимационная служба
						{								
							layout:'border',
							width: 1300,//1440,  
							height: 23,
							xtype: 'panel',
							border:true,
							//style:'padding-bottom: 5px; ',
							//width:770,
							items:[							
								//Лейбл реанимационная служба
								{									
									layout:'form',
									//style:'margin-left: 12px; ',
									width: 200, //135,
									border:true,
									region: 'west',
									items:[
										new Ext.form.Label({
											text: 'Реанимационная служба:'
										})					
									]
								},
								//реанимационная служба
								{									
									layout:'form',
									style:'color: blue;',
									region: 'center',
									//width: 300,
									items:[
										new Ext.form.Label({
											id: 'swERPEW_MedService_Name'
										})					
									]
								},
								// кнопка сохранить
								{									
									layout:'form',
									region: 'east',
									width: 100,
									//margins: '5 0 0 0',
									items:[

										new Ext.Button({
											id: 'swERPEW_ButtonSave',
											iconCls: 'save16',
											text: langs('Сохранить'),
											handler: function()
											{
												this.EvnReanimatPeriod_Save();
											}.createDelegate(this)
										})

									]
								}
							]
						},
						//даты - время реанимационного периода
						{							
							layout:'column',
							width:770,
							border:true,
							items:[							
								//Дата начала периода
								{									
									layout:'form',
									width: 260,
									labelWidth: 150,
									items:[										
										{
											allowBlank:false,
											fieldLabel:'Начало периода: дата',
											format:'d.m.Y',
											id: 'swERPEW_EvnReanimatPeriod_setDate',
											maxValue: getGlobalOptions().date,
											listeners:{
												'change':function (field, newValue, oldValue) {
													if (newValue > field.maxValue){
														field.setValue(oldValue);
														Ext.MessageBox.alert('Ошибка!', 'Неверная дата - превышает текущую!', function(){field.focus(true,100);});
													}
													else this.changedDates = true; //а зачем не знаю
												}.createDelegate(this),
												'keydown':function (inp, e) {
													//сделал по образцу из формы движения, а зачем не знаю
													if (e.getKey() == Ext.EventObject.TAB && e.shiftKey == true) {
														e.stopEvent();
														this.buttons[this.buttons.length - 1].focus();
													}
												}.createDelegate(this)
											},
											name:'EvnReanimatPeriod_setDate',
											plugins:[ new Ext.ux.InputTextMask('99.99.9999', false) ],
											selectOnFocus:true,
											//tabIndex:this.tabIndex + 1,
											width:100,
											xtype:'swdatefield'
										}										
										
										
									]
								},
								//Время начала периода
								{									
									layout:'form',
									width: 120,
									labelWidth: 50,
									items:[										
										{
											allowBlank:false,
											fieldLabel:'время',
											listeners:{
												'change':function (field, newValue, oldValue) {
													this.changedDates = true;
													var base_form = this.findById('swERPEW_Form').getForm();
													base_form.findField('EvnReanimatPeriod_setDate').fireEvent('change', base_form.findField('EvnReanimatPeriod_setDate'), base_form.findField('EvnReanimatPeriod_setDate').getValue());
												}.createDelegate(this),
												'keydown':function (inp, e) {
													if (e.getKey() == Ext.EventObject.F4) {
														e.stopEvent();
														inp.onTriggerClick();
													}
												}
											},
											id: 'swERPEW_EvnReanimatPeriod_setTime',
											name:'EvnReanimatPeriod_setTime',
											onTriggerClick:function () {
												var base_form = this.findById('swERPEW_Form').getForm();
												var time_field = base_form.findField('EvnReanimatPeriod_setTime');

												if (time_field.disabled) {
													return false;
												}

												setCurrentDateTime({
													callback:function () {
														base_form.findField('EvnReanimatPeriod_disDate').setMinValue(base_form.findField('EvnReanimatPeriod_setDate').getValue());
														base_form.findField('EvnReanimatPeriod_setDate').fireEvent('change', base_form.findField('EvnReanimatPeriod_setDate'), base_form.findField('EvnReanimatPeriod_setDate').getValue());
													}.createDelegate(this),
													dateField:base_form.findField('EvnReanimatPeriod_setDate'),
													loadMask:true,
													setDate:true,
													setDateMaxValue:true,
													setDateMinValue:false,
													setTime:true,
													timeField:time_field,
													windowId:this.id
												});
											}.createDelegate(this),
											plugins:[ new Ext.ux.InputTextMask('99:99', true) ],
											//tabIndex:this.tabIndex + 2,
											validateOnBlur:false,
											width:60,
											xtype:'swtimefield'
										}
									]
								},
								//Дата конца периода
								{									
									layout:'form',
									width: 260,
									labelWidth: 150,
									items:[										
										{
											allowBlank:true,
											fieldLabel:'Конец периода: дата',
											format:'d.m.Y',
											id: 'swERPEW_EvnReanimatPeriod_disDate',
											maxValue: getGlobalOptions().date,
											listeners:{
												'change':function (field, newValue, oldValue) {
													if (newValue > field.maxValue){
														field.setValue(oldValue);
														Ext.MessageBox.alert('Ошибка!', 'Неверная дата - превышает текущую!', function(){field.focus(true,100);});
													}
													else this.changedDates = true; //а зачем не знаю
												}.createDelegate(this),
												'keydown':function (inp, e) {
													//сделал по образцу из формы движения, а зачем не знаю
													if (e.getKey() == Ext.EventObject.TAB && e.shiftKey == true) {
														e.stopEvent();
														this.buttons[this.buttons.length - 1].focus();
													}
												}.createDelegate(this)
											},
											name:'EvnReanimatPeriod_disDate',
											plugins:[ new Ext.ux.InputTextMask('99.99.9999', false) ],
											selectOnFocus:true,
													//tabIndex:this.tabIndex + 1,
											width:100,
											xtype:'swdatefield'
										}										
									]
								},	
								//Время конца периода
								{									
									layout:'form',
									width: 120,
									labelWidth: 50,
									items:[	
										
										{
											fieldLabel:'время',
											allowBlank: true,
											listeners:{
												'change':function (field, newValue, oldValue) {
													this.changedDates = true;
													var base_form = this.findById('swERPEW_Form').getForm();
												}.createDelegate(this),
												'keydown':function (inp, e) {
													if (e.getKey() == Ext.EventObject.F4) {
														e.stopEvent();
														inp.onTriggerClick();
													}
												}
											},
											id: 'swERPEW_EvnReanimatPeriod_disTime',
											name:'EvnReanimatPeriod_disTime',
											onTriggerClick:function () {
												var base_form = this.findById('swERPEW_Form').getForm();
												var time_field = base_form.findField('EvnReanimatPeriod_disTime');

												if (time_field.disabled) {
													return false;
												}

												setCurrentDateTime({
													callback:function () {
														base_form.findField('EvnReanimatPeriod_disDate').fireEvent('change', base_form.findField('EvnReanimatPeriod_disDate'), base_form.findField('EvnReanimatPeriod_disDate').getValue());
													}.createDelegate(this),
													dateField:base_form.findField('EvnReanimatPeriod_disDate'),
													loadMask:true,
													setDate:true,
													setDateMaxValue:true,
													addMaxDateDays: this.addMaxDateDays,
													setDateMinValue:false,
													setTime:true,
													timeField:time_field,
													windowId:this.id
												});
											}.createDelegate(this),
											plugins:[ new Ext.ux.InputTextMask('99:99', true) ],
											//tabIndex:this.tabIndex + 4,
											validateOnBlur:false,
											width:60,
											xtype:'swtimefield'
										}										
										
									]
								}										
							]
						},
						//BOB - 23.01.2020
						{							
							layout:'column',
							//width:770,
							border:true,
							items:[	
								//Панель - Показание для перевода в реанимацию
								{								
									layout:'form',
									labelWidth: 240,
									border:true,
									//width:770,
									items:[	
										//combo  - Показание для перевода в реанимацию   
										//BOB - 21.03.2019
										{
											allowBlank: false,
											comboSubject: 'ReanimReasonType',
											fieldLabel: langs('Показание для перевода в реанимацию'),
											labelSeparator: '',
											id: 'swERPEW_ReanimReasonType',
											hiddenName: 'ReanimReasonType',									
											lastQuery: '',
											width: 200,
											xtype: 'swcommonsprcombo'
										}
									]
								},
								//Панель - Исход пребывания в реанимации
								{								
									layout:'form',
									labelWidth: 240,
									border:true,
									//width:770,
									items:[	
										//combo - Исход пребывания в реанимации   
										{
											allowBlank: true,
											comboSubject: 'ReanimResultType',
											fieldLabel: langs('Исход пребывания в реанимации'),
											labelSeparator: '',
											id: 'swERPEW_ReanimResultType',
											hiddenName: 'ReanimResultType',									
											lastQuery: '',
											width: 200,
											xtype: 'swcommonsprcombo',
		//									oldValue: null,
											listeners: {
												'beforeselect': function(combo, record, index) {
													if (record.data.ReanimResultType_id == 4) {
														Ext.MessageBox.alert('Внимание!', 'Данное значение устанавливается только из функции Перевод в другую реанимацию!');
		//												console.log('BOB_beforeselect_combo.value_1=',Ext.getCmp('swERPEW_ReanimResultType').value);
														return false;
													}
												}
											}
										}
									]
								}
							]
						},

						//BOB - 23.01.2020
						{							
							layout:'column',
							//width:770,
							border:true,
							items:[	
								//Панель - Возрастная категория  //BOB - 21.03.2019
								{								
									layout:'form',
									labelWidth: 240,
									border:true,
									//width:770,
									items:[	
										//combo  - Возрастная категория   
										{											//BOB - 03.04.2020 - это чёрт знает что, справочник ReanimatAgeGroup не фиксится - пришлось вернуться к xtype: 'combo'
											allowBlank: false,
											comboSubject: 'ReanimatAgeGroup',
											fieldLabel: langs('Возрастная группа'),
											labelSeparator: '',
											id: 'swERPEW_ReanimatAgeGroup',
											hiddenName: 'ReanimatAgeGroup',									
											lastQuery: '',
											width: 200,
											xtype: 'swcommonsprcombo'
										}																																																																													
									]
								},
								//Панель - Профиль коек
								{								
									layout:'form',
									labelWidth: 240,
									//labelAlign: 'left',
									border:true,
									//width:770,
									items:[	
										//combo  - Профиль коек
										{
											allowBlank: false,
											comboSubject: 'LpuSectionBedProfile',
											suffix: 'Reanim',
											fieldLabel: langs('Профиль коек'),
											labelSeparator: '',
											id: 'swERPEW_BedProfile',
											hiddenName: 'BedProfile',									
											lastQuery: '',
											listeners: {
												'change': function(combo, newValue, oldValue) {
													if (Ext.isEmpty(oldValue) && !Ext.isEmpty(newValue)){
														Ext.getCmp('swERPEW_CancelButton').disable();
													}
												}
											},
											width: 300,
											xtype: 'swcommonsprcombo'
										}								
									]
								}
							]
						}

					]
				},

//Панель регулярного наблюдения состояния 				
				new sw.Promed.Panel({
					title:'1. Регулярное наблюдение состояния',
					id:'swERPEW_Condition_Panel',
					autoHeight:true,
				//	width:1450,   //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
					border:true,
					collapsible:true,
					//collapsed:true,   // !!!!!!!!!!!!!!!!!!!!на время разработки, чтобы дальнейшие области быстрее видеть
					layout:'form',
					style:'margin-bottom: 0.5em; ',
					bodyStyle:'padding-top: 0.5em; border-top: 1px none #99bbe8; border-left: 1px solid #99bbe8; border-bottom: 1px solid #ffffff;border-right: 1px solid #ffffff;',
					autoScroll:true,
					BreathAuscult_records: {}, //BOB  24/01/2019
					listeners:{
						'expand':function (panel) {
						}.createDelegate(this)
					},					
					items:[
						
						//Панель - Таблица регулярного наблюдения состояния 	
						{
							border:true,
							style: 'border-top: 1px solid #99bbe8; border-left: 1px solid #99bbe8; border-bottom: 1px solid #ffffff;border-right: 1px solid #ffffff; padding: 0px; ',
							height:211,
							layout:'border',
							items:[
								//Таблица регулярного наблюдения состояния
								new Ext.grid.GridPanel({
									id: 'swERPEW_EvnReanimatCondition_Grid',
									frame: false,
									border: false,
									loadMask: true,
									region: 'center',
									stripeRows: true,
									height:200,

									columns: [
										{dataIndex: 'EvnReanimatCondition_setDate', header: 'Дата', hidden: false, renderer: Ext.util.Format.dateRenderer('d.m.Y'), resizable: false, sortable: false, width: 100 }, 
										{dataIndex: 'EvnReanimatCondition_setTime', header: 'Время', hidden: false, resizable: false, sortable: false, width: 100 },
										{dataIndex: 'EvnReanimatCondition_disDate', header: 'Дата оконч', hidden: false, renderer: Ext.util.Format.dateRenderer('d.m.Y'), resizable: false, sortable: false, width: 100 }, 
										{dataIndex: 'EvnReanimatCondition_disTime', header: 'Время оконч', hidden: false, resizable: false, sortable: false, width: 100 },
										{dataIndex: 'Stage_Name', header: 'Этапный - документ', hidden: false, resizable: false, sortable: false, width: 200},
										{dataIndex: 'Condition_Name', header: 'Состояние', hidden: false, id: 'Condition_Name', resizable: true, sortable: false, width: 200 }
									],
									autoExpandColumn: 'Condition_Name',
									autoExpandMin: 200,
									listeners:{
										'rowdblclick': function(grid, rowIndex, e){
											if (this.isNeonatal(this.findById('swERPEW_ReanimatAgeGroup').getValue())) {
												this.EvnReanimatCondition_Edit();
											}
										}.createDelegate(this)
									},
									sm:new Ext.grid.RowSelectionModel({
											listeners:{
												'rowselect':function (sm, rowIndex, record) {
													this.EvnReanimatCondition_view();//загрузка панели шкал
												}.createDelegate(this)
											}
										}),
									store:new Ext.data.Store({
										autoLoad:false,
										listeners:{
											'load':function (store, records, index) {
												if (store.getCount() == 0) {
													LoadEmptyRow(this.findById('swERPEW_EvnReanimatCondition_Grid'));
													this.findById('swERPEW_EvnReanimatCondition_Grid').getSelectionModel().selectRow(0);													
												} else {		
													this.findById('swERPEW_EvnReanimatCondition_Grid').getSelectionModel().selectRow(win.ConditionGridLoadRawNum); 	//установка выбранности на первой строке грда 												
													this.ConditionGridLoadRawNum = 0;
												}
											}.createDelegate(this)
										},
										reader:new Ext.data.JsonReader({
											id:'EvnReanimatCondition_id'
										}, [
											{mapping:'EvnReanimatCondition_id', name:'EvnReanimatCondition_id', type:'int'},
											{mapping:'EvnReanimatCondition_pid', name:'EvnReanimatCondition_pid', type:'int'},
											{mapping:'Person_id', name:'Person_id', type:'int'},
											{mapping:'PersonEvn_id', name:'PersonEvn_id', type:'int'},
											{mapping:'Server_id', name:'Server_id', type:'int' },
											{mapping:'EvnReanimatCondition_setDate', name:'EvnReanimatCondition_setDate', type:'date', dateFormat:'d.m.Y' },
											{mapping:'EvnReanimatCondition_setTime', name:'EvnReanimatCondition_setTime', type:'string' },
											{mapping:'EvnReanimatCondition_disDate', name:'EvnReanimatCondition_disDate', type:'date', dateFormat:'d.m.Y' },   //BOB - 24.07.2018
											{mapping:'EvnReanimatCondition_disTime', name:'EvnReanimatCondition_disTime', type:'string' },						//BOB - 24.07.2018
											{mapping:'ReanimStageType_id', name:'ReanimStageType_id', type:'int' },
											{mapping:'Stage_Name', name:'Stage_Name', type:'string' },
											{mapping:'ReanimConditionType_id', name:'ReanimConditionType_id', type:'int' },
											{mapping:'Condition_Name', name:'Condition_Name', type:'string' },
											{mapping:'EvnReanimatCondition_Complaint', name:'EvnReanimatCondition_Complaint', type:'string' },
											{mapping:'SkinType_id', name:'SkinType_id', type:'int'},
											{mapping:'EvnReanimatCondition_SkinTxt', name:'EvnReanimatCondition_SkinTxt', type:'string'},
											{mapping:'ConsciousType_id', name:'ConsciousType_id', type:'int'},
											{mapping:'BreathingType_id', name:'BreathingType_id', type:'int'},
											{mapping:'EvnReanimatCondition_IVLapparatus', name:'EvnReanimatCondition_IVLapparatus', type:'string'},
											{mapping:'EvnReanimatCondition_IVLparameter', name:'EvnReanimatCondition_IVLparameter', type:'string'},
											{mapping:'EvnReanimatCondition_Auscultatory', name:'EvnReanimatCondition_Auscultatory', type:'string'},
											{mapping:'HeartTonesType_id', name:'HeartTonesType_id', type:'int'},
											{mapping:'HemodynamicsType_id', name:'HemodynamicsType_id', type:'int'},
											{mapping:'EvnReanimatCondition_Pressure', name:'EvnReanimatCondition_Pressure', type:'string'},
											{mapping:'EvnReanimatCondition_HeartFrequency', name:'EvnReanimatCondition_HeartFrequency', type:'int'},
											{mapping:'EvnReanimatCondition_StatusLocalis', name:'EvnReanimatCondition_StatusLocalis', type:'string'},
											{mapping:'AnalgesiaType_id', name:'AnalgesiaType_id', type:'int'},
											{mapping:'EvnReanimatCondition_AnalgesiaTxt', name:'EvnReanimatCondition_AnalgesiaTxt', type:'string'},
											{mapping:'EvnReanimatCondition_Diuresis', name:'EvnReanimatCondition_Diuresis', type:'string'},
											{mapping:'UrineType_id', name:'UrineType_id', type:'int'},
											{mapping:'EvnReanimatCondition_UrineTxt', name:'EvnReanimatCondition_UrineTxt', type:'string'},
											{mapping:'EvnReanimatCondition_Conclusion', name:'EvnReanimatCondition_Conclusion', type:'string'},
											{mapping:'ReanimArriveFromType_id', name:'ReanimArriveFromType_id', type:'int'},
											{mapping:'EvnReanimatCondition_HemodynamicsTxt', name:'EvnReanimatCondition_HemodynamicsTxt', type:'string'},
											{mapping:'EvnReanimatCondition_NeurologicStatus', name:'EvnReanimatCondition_NeurologicStatus', type:'string'},										
											{mapping:'EvnReanimatCondition_sofa', name:'EvnReanimatCondition_sofa', type:'int'},										//BOB - 23.04.2018
											{mapping:'EvnReanimatCondition_apache', name:'EvnReanimatCondition_apache', type:'int'},									//BOB - 23.04.2018
											{mapping:'EvnReanimatCondition_Saturation', name:'EvnReanimatCondition_Saturation', type:'int'},							//BOB - 23.04.2018
											{mapping:'EvnReanimatCondition_OxygenFraction', name:'EvnReanimatCondition_OxygenFraction', type:'int'},
											{mapping:'EvnReanimatCondition_OxygenPressure', name:'EvnReanimatCondition_OxygenPressure', type:'int'},
											{mapping:'EvnReanimatCondition_PaOFiO', name:'EvnReanimatCondition_PaOFiO', type:'float'},
											//{mapping:'NutritiousType_id', name:'NutritiousType_id', type:'int'},														//BOB - 23.04.2018   - //23.09.2019 - закомментарено
											//{mapping:'EvnReanimatCondition_NutritiousTxt', name:'EvnReanimatCondition_NutritiousTxt', type:'string'},					//BOB - 28.08.2018  - //23.09.2019 - закомментарено
											{mapping:'EvnReanimatCondition_Temperature', name:'EvnReanimatCondition_Temperature', type:'float'},						//BOB - 28.08.2018
											{mapping:'EvnReanimatCondition_InfusionVolume', name:'EvnReanimatCondition_InfusionVolume', type:'float'},					//BOB - 28.08.2018
											{mapping:'EvnReanimatCondition_DiuresisVolume', name:'EvnReanimatCondition_DiuresisVolume', type:'float'},					//BOB - 28.08.2018
											{mapping:'EvnReanimatCondition_CollectiveSurvey', name:'EvnReanimatCondition_CollectiveSurvey', type:'string'},				//BOB - 28.08.2018
											
											{mapping:'EvnReanimatCondition_SyndromeType', name:'EvnReanimatCondition_SyndromeType', type:'string'},						//BOB - 21.12.2018
											{mapping:'EvnReanimatCondition_ConsTxt', name:'EvnReanimatCondition_ConsTxt', type:'string'},								//BOB - 21.12.2018
											{mapping:'SpeechDisorderType_id', name:'SpeechDisorderType_id', type:'int'},												//BOB - 21.12.2018
											{mapping:'EvnReanimatCondition_rass', name:'EvnReanimatCondition_rass', type:'int'},										//BOB - 21.12.2018
											{mapping:'EvnReanimatCondition_Eyes', name:'EvnReanimatCondition_Eyes', type:'string'},										//BOB - 21.12.2018
											{mapping:'EvnReanimatCondition_WetTurgor', name:'EvnReanimatCondition_WetTurgor', type:'string'},							//BOB - 21.12.2018
											{mapping:'EvnReanimatCondition_waterlow', name:'EvnReanimatCondition_waterlow', type:'int'},								//BOB - 21.12.2018
											{mapping:'SkinType_mid', name:'SkinType_mid', type:'int'},																	//BOB - 21.12.2018
											{mapping:'EvnReanimatCondition_MucusTxt', name:'EvnReanimatCondition_MucusTxt', type:'string'},								//BOB - 21.12.2018
											{mapping:'EvnReanimatCondition_IsMicrocDist', name:'EvnReanimatCondition_IsMicrocDist', type:'int'},						//BOB - 21.12.2018
											{mapping:'EvnReanimatCondition_IsPeriphEdem', name:'EvnReanimatCondition_IsPeriphEdem', type:'int'},						//BOB - 21.12.2018
											{mapping:'EvnReanimatCondition_Reflexes', name:'EvnReanimatCondition_Reflexes', type:'string'},								//BOB - 21.12.2018
											{mapping:'EvnReanimatCondition_BreathFrequency', name:'EvnReanimatCondition_BreathFrequency', type:'int'},					//BOB - 21.12.2018
											{mapping:'EvnReanimatCondition_HeartTones', name:'EvnReanimatCondition_HeartTones', type:'string'},							//BOB - 21.12.2018
											{mapping:'EvnReanimatCondition_IsHemodStab', name:'EvnReanimatCondition_IsHemodStab', type:'int'},							//BOB - 21.12.2018
											{mapping:'EvnReanimatCondition_Tongue', name:'EvnReanimatCondition_Tongue', type:'string'},									//BOB - 21.12.2018
											{mapping:'EvnReanimatCondition_Paunch', name:'EvnReanimatCondition_Paunch', type:'string'},									//BOB - 21.12.2018
											{mapping:'EvnReanimatCondition_PaunchTxt', name:'EvnReanimatCondition_PaunchTxt', type:'string'},							//BOB - 21.12.2018
											{mapping:'PeristalsisType_id', name:'PeristalsisType_id', type:'int'},														//BOB - 21.12.2018
											{mapping:'EvnReanimatCondition_VBD', name:'EvnReanimatCondition_VBD', type:'int'},											//BOB - 21.12.2018
											{mapping:'EvnReanimatCondition_Defecation', name:'EvnReanimatCondition_Defecation', type:'int'},							//BOB - 21.12.2018
											{mapping:'EvnReanimatCondition_DefecationTxt', name:'EvnReanimatCondition_DefecationTxt', type:'string'},					//BOB - 21.12.2018
											{mapping:'LimbImmobilityType_id', name:'LimbImmobilityType_id', type:'int'},												//BOB - 21.12.2018
											{mapping:'EvnReanimatCondition_MonopLoc', name:'EvnReanimatCondition_MonopLoc', type:'string'},								//BOB - 21.12.2018
											{mapping:'EvnReanimatCondition_mrc', name:'EvnReanimatCondition_mrc', type:'int'},											//BOB - 21.12.2018
											{mapping:'EvnReanimatCondition_MeningSign', name:'EvnReanimatCondition_MeningSign', type:'int'},							//BOB - 21.12.2018
											{mapping:'EvnReanimatCondition_MeningSignTxt', name:'EvnReanimatCondition_MeningSignTxt', type:'string'},					//BOB - 21.12.2018
											{mapping:'EvnReanimatCondition_glasgow', name:'EvnReanimatCondition_glasgow', type:'int'},										//BOB - 16.09.2019
											{mapping:'EvnReanimatCondition_four', name:'EvnReanimatCondition_four', type:'int'},											//BOB - 16.09.2019
											{mapping:'EvnReanimatCondition_SyndromeTxt', name:'EvnReanimatCondition_SyndromeTxt', type:'string'},						//BOB - 16.09.2019
											{mapping:'EvnReanimatCondition_Doctor', name:'EvnReanimatCondition_Doctor', type:'string'}							//BOB - 16.09.2019

										]),
										url:'/?c=EvnReanimatPeriod&m=loudEvnReanimatConditionGrid'
									}),
									tbar:new sw.Promed.Toolbar({
										buttons:[
											//кнопка добавления
											{
												id: 'swERPEW_EvnReanimatConditionButtonAdd',
												handler:function () {
													this.EvnReanimatCondition_Add();
												}.createDelegate(this),
												iconCls:'add16',
												text:'Добавить'
											},
											//кнопка копирования
											{
												id: 'swERPEW_EvnReanimatConditionButtonCopy',
												handler:function () {
													this.EvnReanimatCondition_Copy();
												}.createDelegate(this),
												iconCls:'copy16',
												text:'Копировать'
											},
											//кнопка редактирования
											{
												id: 'swERPEW_EvnReanimatConditionButtonEdit',
												handler:function () {
													this.EvnReanimatCondition_Edit();
												}.createDelegate(this),
												iconCls:'edit16',
												text:'Редактировать'
											},
											//кнопка удаления
											{
												id: 'swERPEW_EvnReanimatConditionButtonDel',
												handler:function () {
													this.EvnReanimatCondition_Del();
												}.createDelegate(this),
												iconCls:'delete16',
												text:'Удалить'
											},
											// кнопка обновления списка
											{
												id: 'swERPEW_EvnReanimatConditionButtonRefresh',
												handler:function () {
													//BOB - 17.03.2020  //если младенцы
													if (this.isNeonatal(this.findById('swERPEW_ReanimatAgeGroup').getValue()))  {
														NeonatalRefresh = function() {
															getWnd('swEvnNeonatalSurveyEditWindow').hide();
															win.findById('swERPEW_EvnReanimatCondition_Grid').getStore().reload();	//перезагрузка грида 
															win.EvnReanimatCondition_ButtonManag(win,true);  //BOB - 11.02.2019
														}

														if (getWnd('swEvnNeonatalSurveyEditWindow').isVisible() && getWnd('swEvnNeonatalSurveyEditWindow').changedDatas) {
															sw.swMsg.show({
																buttons: Ext.Msg.YESNO,
																fn: function(buttonId) {
																	if ( buttonId == 'yes' ) NeonatalRefresh();
																},
																icon: Ext.Msg.WARNING,
																msg: 'Окно Наблюдение состояния младенца открыто<br> и в нём имеются несохранённые изменния!<br> Вы действительно желаете закрыть его без сохранения?',
																title: 'Внимание!'
															});
														} else NeonatalRefresh();

													} else {  //BOB - 17.03.2020  //если взрослые
														this.findById('swERPEW_EvnReanimatCondition_Grid').getStore().reload();	//перезагрузка грида 
														this.EvnReanimatCondition_ButtonManag(this,true);  //BOB - 11.02.2019
														this.findById('swERPEW_Condition_Panel').BreathAuscult_records = {};//BOB - 24.01.2018
													}

												}.createDelegate(this),
												iconCls:'refresh16',
												text:'Обновить'
											},											
											//кнопка печати документа поступления/дневника на верхней половине листа
											{
												id: 'swERPEW_EvnReanimatConditionButtonPrintUp',
												handler:function () {
													this.EvnReanimatCondition_Print(0);
												}.createDelegate(this),
												iconCls:'print16',
												text:'Печать верх'
											},
											//кнопка печати документа поступления/дневника на нижней половине листа
											{
												id: 'swERPEW_EvnReanimatConditionButtonPrintDoun',
												handler:function () {
													this.EvnReanimatCondition_Print(1);
												}.createDelegate(this),
												iconCls:'print16',
												text:'Печать низ'
											}
										]
									}),
									//BOB - 25.12.2019
									keys: [{
										key: [
											Ext.EventObject.F3,
										],
										fn: function(inp, e) {
											e.stopEvent();
											e.returnValue = false;
											var grid = this.findById('swERPEW_EvnReanimatCondition_Grid');

											switch ( e.getKey() ) {
												case Ext.EventObject.F3:
													if ( e.altKey ) {
														var params = new Object();
														params['key_id'] = grid.getSelectionModel().getSelected().data.EvnReanimatCondition_id;
														params['key_field'] = 'EvnReanimatCondition_id';
														getWnd('swAuditWindow').show(params);
													}
													break;
											}
										},
										scope: this,
										stopEvent: true
									}]
								})
							]
						},

						//Панель - Событие регулярного наблюдения состояния		
						{
							//	xtype: 'panel', 
							id: 'swERPEW_EvnReanimatConditionPanel',
							layout:'form',
							border:true,
							width:1307,  // 1457
							style: 'border-top: 1px solid #99bbe8; border-left: 1px solid #99bbe8; border-bottom: 1px solid #ffffff;border-right: 1px solid #ffffff; padding: 2px 0px 0px 0px; margin-top: 10px;',
							//autoScroll:true,
							//height:300,

							items:[
								//ШАПКА Событие регулярного наблюдения состояния
								{							
									layout:'border',
									width: 1300,  //  1440
									height: 23,
									xtype: 'panel',
									border:true,
									items:[	

										//BOB - 08.04.2020
										// кнопка открытия/закрытия блоков
										{									
											layout:'form',
											region: 'west',
											width: 25,
											items:[
												new Ext.Button({
													id: 'swERPEW_EvnReanimatConditionPanelsManag',
													iconCls: 'view16',  //
													text: '',
													isCollaps: true,
													handler: function(b,e)
													{
														Ext.select('fieldset', true, 'swERPEW_EvnReanimatConditionPanel').each(function(el){
															var id = el.id; //выделяю параметр id из Ext.Element
															var object = win.findById(id);	//ищу в окне объект ExtJS
															if(object){ // если нахожу, то
																if (b.isCollaps) 
																	object.expand(); // делаю Disabled /Enabled
																else
																	object.collapse(); // делаю Disabled /Enabled
															}
														});
														b.isCollaps = !b.isCollaps;											
													}.createDelegate(this)
												})
											]
										},
										//этап, Дата Время регулярного наблюдения состояния / Поступил из
										{
											layout:'column',
											//width: 750,
											border:false,
											region: 'center',
											items:[	
												//панель - этап - документ cобытие регулярного наблюдения состояния    350
												{	
													layout:'form',
													width: 350, //550,
													labelWidth: 100, //110,
													border:true,
													//region: 'west',
													items:[	
														//combo этап - документ событие регулярного наблюдения состояния    
														{
															id: 'swERPEW_EvnReanimatConditionStage',
															hiddenName: 'EvnReanimatConditionStage',									
															xtype: 'swextemporalcomptypecombo',
															fieldLabel: langs('Этап - документ'),
															allowBlank: false,
															comboSubject: 'ReanimStageType',
															width: 200, //400,
															lastQuery: '',
															listeners: {
																'select': function(combo, record, index, from) {
																	//если выбрано "Поступление", то проверяю было ли оно уже, если было устанавливаю "дневник"
																	if (record.data.ReanimStageType_id == 1){
																		var index = win.findById('swERPEW_EvnReanimatCondition_Grid').getStore().find('ReanimStageType_id', 1);
																		if (index != -1){
																			Ext.MessageBox.alert('Внимание!', 'Поступление уже имеется!');
																			combo.setValue(2); // устанавливаю в комбо этап - "дневник"
																			var index = combo.getStore().find('ScaleParameterResult_id',2);//нахожу индекс в store комбо 
																			record = combo.getStore().getAt(index);  // нахожу record по index,
																		}
																	}
																		
																	//заполнение строки грида
																	var EvnRAGridRowData = win.findById('swERPEW_EvnReanimatCondition_Grid').getSelectionModel().getSelected();  //выбранная строка в гриде регулярного наблюдения состояния    
																	//если запуск события по выбору в комбо (а не из процедуры _view)
																	if (!(from) || (from == '')) {	
																		//установка выидимости реквизитов и предустановки в зависимости от стадии
																		win.findById('swERPEW_RC_ArriveFrom_panel').setVisible(false);  //"Поступил из"
																		if (win.findById('swERPEW_RC_Analgesia').value == 3) win.findById('swERPEW_RC_AnalgesiaTxt').setVisible(true);  //"анальгезия вариант пользователя"
																		if (win.findById('swERPEW_RC_Nutritious').value == 4) win.findById('swERPEW_RC_NutritiousTxt').setVisible(true);  //"питание вариант пользователя" //BOB - 28.08.2018 
																		if (win.findById('swERPEW_RC_Urine').value == 4) win.findById('swERPEW_RC_UrineTxt').setVisible(true);  //"моча вариант пользователя"
																		win.findById('swERPEW_RC_DiuresisVolume').showContainer();  //"объём диуреза" //BOB - 28.08.2018 
																		win.findById('swERPEW_RC_DiuresisVolume_Unit').setVisible(true);  //"объём диуреза мл" //BOB - 28.08.2018 
																		
																		win.findById('swERPEW_RC_IVLapparatus').showContainer();  //"аппарат ИВЛ"
																		win.findById('swERPEW_RC_IVLparameter').showContainer();  //"параметры ИВЛ"
																		
																		win.findById('swERPEW_EvnReanimatCondition_disDate_Pnl').setVisible(false);  //"дата завершения" //BOB - 13.08.2018
																		win.findById('swERPEW_EvnReanimatCondition_disTime_Pnl').setVisible(false);  //"время завершения" //BOB - 13.08.2018

																		//win.findById('swERPEW_RC_Neurologic_Status_Panel').setTitle('Неврологический статус');
																		win.findById('swERPEW_RC_Neurologic_Status_Panel').setVisible(false); //BOB - 27.06.2019   //доп инфа
																		win.findById('swERPEW_RC_Neurologic_Status_Bis_Panel').setVisible(true); //BOB - 27.06.2019  //невролог стат
																		win.findById('swERPEW_RC_Conclusion_Panel').setTitle('Заключение');
																		
																		if (EvnRAGridRowData.data['EvnReanimatCondition_id'] == 'New_GUID_Id'){
																			win.findById('swERPEW_RC_Conscious').setValue(null); //ясное
																			win.findById('swERPEW_RC_Breathing').setValue(null); //самостоятельное адекватное
																			win.findById('swERPEW_RC_Hemodynamics').setValue(null); //стабильная																
																		}

																		switch (combo.getValue()){
																			case 1:
																				win.findById('swERPEW_RC_ArriveFrom_panel').setVisible(true);
																				break;
																			case 2:																		//win.findById('swERPEW_RC_HemodynamicsTxtPanel').setVisible(true);
																				win.findById('swERPEW_EvnReanimatCondition_disDate_Pnl').setVisible(true);  //"дата завершения" //BOB - 13.08.2018
																				win.findById('swERPEW_EvnReanimatCondition_disTime_Pnl').setVisible(true);  //"время завершения" //BOB - 13.08.2018
																				break;																	
																			case 3:
																				win.findById('swERPEW_RC_DiuresisVolume').hideContainer();  //"объём диуреза" //BOB - 28.08.2018 
																				win.findById('swERPEW_RC_DiuresisVolume_Unit').setVisible(false); //"объём диуреза мл" //BOB - 28.08.2018 
																				win.findById('swERPEW_RC_IVLapparatus').hideContainer();  //"аппарат ИВЛ"
																				win.findById('swERPEW_RC_IVLparameter').hideContainer();  //"параметры ИВЛ"

																				//win.findById('swERPEW_RC_Neurologic_Status_Panel').setTitle('Дополнительная информация');
																				win.findById('swERPEW_RC_Neurologic_Status_Panel').setVisible(true); //BOB - 27.06.2019   //доп инфа
																				win.findById('swERPEW_RC_Neurologic_Status_Bis_Panel').setVisible(false); //BOB - 27.06.2019   //невролог стат
																				win.findById('swERPEW_RC_Conclusion_Panel').setTitle('Проведено');

																				if (EvnRAGridRowData.data['EvnReanimatCondition_id'] == 'New_GUID_Id'){
																					win.findById('swERPEW_RC_Conscious').setValue(2); //ясное
																					win.findById('swERPEW_RC_Breathing').setValue(1); //самостоятельное адекватное
																					//win.findById('swERPEW_RC_Hemodynamics').setValue(1); //стабильная
																				}
																				break;																	
																		}
																		
																		//предустановка даты-времени  //BOB - 13.08.2018
																		var curDate = getValidDT(getGlobalOptions().date, ''); // считываю из глобальных параметров текущую дату
																		if (combo.getValue() == 2){    
																			if(win.findById('swERPEW_EvnReanimatCondition_Grid').getStore().data.items.length > 1){
																				win.findById('swERPEW_EvnReanimatCondition_setDate').setValue(win.findById('swERPEW_EvnReanimatCondition_Grid').getStore().data.items[1].data['EvnReanimatCondition_disDate']);
																				win.findById('swERPEW_EvnReanimatCondition_setTime').setValue(win.findById('swERPEW_EvnReanimatCondition_Grid').getStore().data.items[1].data['EvnReanimatCondition_disTime']);
																			}
																			else {  //похоже эта ветвь никогда не выполняется - на всякий случай - чтобы не ругалось
																				win.findById('swERPEW_EvnReanimatCondition_setDate').setValue(curDate);
																				win.findById('swERPEW_EvnReanimatCondition_setTime').setValue('');
																			}
																			win.findById('swERPEW_EvnReanimatCondition_disDate').setValue(curDate);// в дату окончания события регулярного наблюдения состояния - текущую дату
																			win.findById('swERPEW_EvnReanimatCondition_disTime').setValue(''); // во время окончания события регулярного наблюдения состояния - пустоту																
																		}
																		else{
																			win.findById('swERPEW_EvnReanimatCondition_setDate').setValue(curDate);// в дату события регулярного наблюдения состояния - текущую дату
																			win.findById('swERPEW_EvnReanimatCondition_setTime').setValue(''); // во время события регулярного наблюдения состояния - пустоту
																			win.findById('swERPEW_EvnReanimatCondition_disDate').setValue('');// в дату окончания события регулярного наблюдения состояния - текущую дату
																			win.findById('swERPEW_EvnReanimatCondition_disTime').setValue(''); // во время окончания события регулярного наблюдения состояния - пустоту																
																		}
																		//BOB - 13.08.2018	
																	
																		//если record существует
																		if(record){
																		//	console.log('BOB_record=',record); 
																			EvnRAGridRowData.data['ReanimStageType_id'] = record.data.ReanimStageType_id;
																			EvnRAGridRowData.data['Stage_Name'] = record.data.ReanimStageType_Name;
																			EvnRAGridRowData.data['ConsciousType_id'] = win.findById('swERPEW_RC_Conscious').getValue();
																			EvnRAGridRowData.data['BreathingType_id'] = win.findById('swERPEW_RC_Breathing').getValue();
																			EvnRAGridRowData.data['HemodynamicsType_id'] = win.findById('swERPEW_RC_Hemodynamics').getValue();
																			EvnRAGridRowData.data['EvnReanimatCondition_setDate'] = win.findById('swERPEW_EvnReanimatCondition_setDate').getValue();//BOB - 13.08.2018
																			EvnRAGridRowData.data['EvnReanimatCondition_setTime'] = win.findById('swERPEW_EvnReanimatCondition_setTime').getValue();//BOB - 13.08.2018
																			EvnRAGridRowData.data['EvnReanimatCondition_disDate'] = win.findById('swERPEW_EvnReanimatCondition_disDate').getValue();//BOB - 13.08.2018
																			EvnRAGridRowData.data['EvnReanimatCondition_disTime'] = win.findById('swERPEW_EvnReanimatCondition_disTime').getValue();//BOB - 13.08.2018
																			EvnRAGridRowData.commit();
																		}
																	}
																}
															}
														}												
													]
												},
												//Дата начала регулярного наблюдения состояния
												{									
													layout:'form',
													width: 160,
													labelWidth: 50,
													items:[										
														{
															allowBlank: false,
															fieldLabel:'Дата',
															labelSeparator: '',
															format:'d.m.Y',
															id: 'swERPEW_EvnReanimatCondition_setDate',
															listeners:{
																'change':function (field, newValue, oldValue) {															
																	//заполнение строки грида
																	var EvnScalesGridRow = win.findById('swERPEW_EvnReanimatCondition_Grid').getSelectionModel().getSelected();  //выбранная строка в гриде Реанимационных мероприятий
																	//если запись в гриде новая
																	//if (EvnScalesGridRow.data['EvnReanimatCondition_id'] == 'New_GUID_Id'){
																		EvnScalesGridRow.data['EvnReanimatCondition_setDate'] = newValue;
																		EvnScalesGridRow.commit();			
																	//}															
																	this.changedDates = true; //а зачем не знаю		
																}.createDelegate(this),
																'keydown':function (inp, e) {
																	//сделал по образцу из формы движения
																	if (e.getKey() == Ext.EventObject.TAB && e.shiftKey == true) {
																		e.stopEvent();
																		this.buttons[this.buttons.length - 1].focus();
																	}
																}.createDelegate(this)
															},
															name:'EvnReanimatCondition_setDate',
															plugins:[ new Ext.ux.InputTextMask('99.99.9999', false) ],
															selectOnFocus:true,
																	//tabIndex:this.tabIndex + 1,
															width:100,
															xtype:'swdatefield'
														}										
													]
												},	
												//Время начала регулярного наблюдения состояния
												{									
													layout:'form',
													width: 120,
													labelWidth: 50,
													items:[	

														{
															fieldLabel:'Время',
															labelSeparator: '',
															allowBlank: false,
															listeners:{
																'change':function (field, newValue, oldValue) {

																	//заполнение строки грида
																	var EvnScalesGridRow = win.findById('swERPEW_EvnReanimatCondition_Grid').getSelectionModel().getSelected();  //выбранная строка в гриде Реанимационных мероприятий
																	EvnScalesGridRow.data['EvnReanimatCondition_setTime'] = newValue;
																	EvnScalesGridRow.commit();
																	this.changedDates = true;

																}.createDelegate(this),
																'keydown':function (inp, e) {
																	if (e.getKey() == Ext.EventObject.F4) {
																		e.stopEvent();
																		inp.onTriggerClick();
																	}
																}
															},
															id: 'swERPEW_EvnReanimatCondition_setTime',
															name:'EvnReanimatCondition_setTime',
															onTriggerClick:function () {
																var base_form = this.findById('swERPEW_Form').getForm();
																var time_field = base_form.findField('EvnReanimatCondition_setTime');

																if (time_field.disabled) {
																	return false;
																}

																setCurrentDateTime({
																	callback:function () {
																		//заполнение строки грида
																		var EvnScalesGridRow = win.findById('swERPEW_EvnReanimatCondition_Grid').getSelectionModel().getSelected();  //выбранная строка в гриде Реанимационных мероприятий
																		EvnScalesGridRow.data['EvnReanimatCondition_setTime'] = time_field.getValue();
																		EvnScalesGridRow.commit();
																		base_form.findField('swERPEW_EvnReanimatCondition_setDate').fireEvent('change', base_form.findField('swERPEW_EvnReanimatCondition_setDate'), base_form.findField('swERPEW_EvnReanimatCondition_setDate').getValue());
																	}.createDelegate(this),
																	dateField:base_form.findField('swERPEW_EvnReanimatCondition_setDate'),
																	loadMask:true,
																	setDate:true,
																	setDateMaxValue:true,
																	addMaxDateDays: this.addMaxDateDays,
																	setDateMinValue:false,
																	setTime:true,
																	timeField:time_field,
																	windowId:this.id
																});
															}.createDelegate(this),
															plugins:[ new Ext.ux.InputTextMask('99:99', true) ],
															//tabIndex:this.tabIndex + 4,
															validateOnBlur:false,
															width:60,
															xtype:'swtimefield'
														}										

													]
												},												
												//BOB - 24.07.2018
												//Дата окончания периода регулярного наблюдения состояния
												{									
													layout:'form',
													id: 'swERPEW_EvnReanimatCondition_disDate_Pnl',
													width: 240,
													labelWidth: 130,
													items:[										
														{
															allowBlank: false,
															fieldLabel:'Окончание:  Дата',
															format:'d.m.Y',
															id: 'swERPEW_EvnReanimatCondition_disDate',
															name:'EvnReanimatCondition_disDate',
															listeners:{
																'change':function (field, newValue, oldValue) {															
																	//заполнение строки грида
																	var EvnScalesGridRow = win.findById('swERPEW_EvnReanimatCondition_Grid').getSelectionModel().getSelected();  //выбранная строка в гриде Реанимационных мероприятий
																	//если запись в гриде новая
																	//if (EvnScalesGridRow.data['EvnReanimatCondition_id'] == 'New_GUID_Id'){
																	EvnScalesGridRow.data['EvnReanimatCondition_disDate'] = newValue;
																	EvnScalesGridRow.commit();			
																	//}															
																	this.changedDates = true; //а зачем не знаю		
																}.createDelegate(this),
																'keydown':function (inp, e) {
																	//сделал по образцу из формы движения
																	if (e.getKey() == Ext.EventObject.TAB && e.shiftKey == true) {
																		e.stopEvent();
																		this.buttons[this.buttons.length - 1].focus();
																	}
																}.createDelegate(this)
															},
															plugins:[ new Ext.ux.InputTextMask('99.99.9999', false) ],
															selectOnFocus:true,
																	//tabIndex:this.tabIndex + 1,
															width:100,
															xtype:'swdatefield'
														}										
													]
												},	
												//Время окончания периода регулярного наблюдения состояния
												{									
													layout:'form',
													id: 'swERPEW_EvnReanimatCondition_disTime_Pnl',
													width: 120,
													labelWidth: 50,
													items:[	
														{
															fieldLabel:'Время',
															allowBlank: false,
															listeners:{
																'change':function (field, newValue, oldValue) {

																	//заполнение строки грида
																	var EvnScalesGridRow = win.findById('swERPEW_EvnReanimatCondition_Grid').getSelectionModel().getSelected();  //выбранная строка в гриде Реанимационных мероприятий
																	//если запись в гриде новая
																	//if (EvnScalesGridRow.data['EvnReanimatCondition_id'] == 'New_GUID_Id'){
																	EvnScalesGridRow.data['EvnReanimatCondition_disTime'] = newValue;
																	EvnScalesGridRow.commit();			
																	//}
																	this.changedDates = true;

																}.createDelegate(this),
																'keydown':function (inp, e) {
																	if (e.getKey() == Ext.EventObject.F4) {
																		e.stopEvent();
																		inp.onTriggerClick();
																	}
																}
															},
															id: 'swERPEW_EvnReanimatCondition_disTime',
															name:'EvnReanimatCondition_disTime',
															onTriggerClick:function () {
																var base_form = this.findById('swERPEW_Form').getForm();
																var time_field = base_form.findField('EvnReanimatCondition_disTime');

																if (time_field.disabled) {
																	return false;
																}

																setCurrentDateTime({
																	callback:function () {
																		//заполнение строки грида
																		var EvnScalesGridRow = win.findById('swERPEW_EvnReanimatCondition_Grid').getSelectionModel().getSelected();  //выбранная строка в гриде Реанимационных мероприятий
																		//если запись в гриде новая
																		//if (EvnScalesGridRow.data['EvnReanimatCondition_id'] == 'New_GUID_Id'){
																		EvnScalesGridRow.data['EvnReanimatCondition_disTime'] = time_field.getValue();
																		EvnScalesGridRow.commit();			
																		//}

																		base_form.findField('swERPEW_EvnReanimatCondition_disDate').fireEvent('change', base_form.findField('swERPEW_EvnReanimatCondition_disDate'), base_form.findField('swERPEW_EvnReanimatCondition_disDate').getValue());
																	}.createDelegate(this),
																	dateField:base_form.findField('swERPEW_EvnReanimatCondition_disDate'),
																	loadMask:true,
																	setDate:true,
																	setDateMaxValue:true,
																	addMaxDateDays: this.addMaxDateDays,
																	setDateMinValue:false,
																	setTime:true,
																	timeField:time_field,
																	windowId:this.id
																});
															}.createDelegate(this),
															plugins:[ new Ext.ux.InputTextMask('99:99', true) ],
															//tabIndex:this.tabIndex + 4,
															validateOnBlur:false,
															width:60,
															xtype:'swtimefield'
														}										
													]
												},
												//combo - Поступил из 
												{							
													layout:'form',
													id:'swERPEW_RC_ArriveFrom_panel',
													//style:'margin-top: 4px;',
													labelWidth:110,
													border:false,
													items:[	
														//BOB - 21.03.2019
														{
															id: 'swERPEW_RC_ArriveFrom',
															hiddenName: 'RC_ArriveFrom',									
															xtype: 'swextemporalcomptypecombo',
															fieldLabel: langs('Поступил из'),
															labelSeparator: '',
															allowBlank: false, //BOB - 23.10.2019
															comboSubject: 'ReanimArriveFromType',
															width: 240,
															lastQuery: '',
															listeners: {
																'select': function(combo, record, index) {
																	var EvnScalesGridRow = win.findById('swERPEW_EvnReanimatCondition_Grid').getSelectionModel().getSelected();  //выбранная строка в гриде регулярного наблюдения состояния    
																	EvnScalesGridRow.data['ReanimArriveFromType_id'] = record.data.ReanimArriveFromType_id;
																	EvnScalesGridRow.commit();
																}
															}
														}																																																																												
													]
												}
												//BOB - 24.07.2018										
											]
										},

										// кнопка сохранить
										{									
											layout:'form',
											region: 'east',
											width: 100,
											//margins: '5 0 0 0',
											items:[

												new Ext.Button({
													id: 'swERPEW_EvnReanimatConditionButtonSave',
													iconCls: 'save16',
													text: 'Сохранить',
													handler: function(b,e)
													{
														this.EvnReanimatCondition_Save(b,e);
													}.createDelegate(this)
												})

											]
										}
										
									]
								},

								//ПАНЕЛЬ РЕДАКТИРОВАНИЯ События регулярного наблюдения состояния	
								{
									layout:'form',
									id:'swERPEW_RC_Base_Data',
									border:true,
									style: 'border-top: 1px solid #ffffff; border-left: 1px solid  #ffffff; border-bottom: 1px solid #99bbe8;border-right: 1px solid #99bbe8; padding: 2px; ',
									items:[	

										//панель - параметры печати  //BOB - 27.09.2019
										{
											id: 'swERPEW_RC_PrintParams_Panel',
											labelWidth: 200,
											xtype: 'fieldset',
											autoHeight: true,
											title: langs('Параметры печати'),
											collapsible: true,
											//collapsed: true,
											layout: 'column',
											style: 'margin: 3px 0 0 0; padding:0; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											listeners: {
												collapse: function(p) {
													win.doLayout();
												},
												expand: function(p) {
													win.doLayout();
												}
											},
											items:[
												{
													layout:'form',
													border: false,
													items:[
														{
															fieldLabel: 'Отображать ФИО пациента',
															name: 'Print_Patient_FIO',
															id: 'swERPEW_RC_Print_Patient_FIO',
															//tabIndex: form.firstTabIndex + 12,
															xtype: 'checkbox',
															checked: true
//															listeners: {
//																'check': function(chb, checked ) {
//																	if (checked){
//																		win.findById('RRW_ReanimatPeriodNow').setValue(true);
//																	}
//																}.createDelegate(this)
//															}

														}
													]
												},
												{
													layout:'form',
													border:false,
													items:[

														{
															xtype: 'combo',
															allowBlank: false,
															hiddenName: 'Print_Doctor_FIO', //'glasgow_eye_response',
															disabled: false,
															id: 'swERPEW_RC_Print_Doctor_FIO', // 'swERPEW_glasgow_eye_response',
															mode:'local',
															listWidth: 400,
															width: 400,
															triggerAction : 'all',
															editable: true,
															displayField:'EvnReanimatCondition_Doctor',
															valueField:'MedPersonal_id',
															tpl: '<tpl for="."><div class="x-combo-list-item">'+
																'{EvnReanimatCondition_Doctor} '+ '&nbsp;' +
																'</div></tpl>' ,
															fieldLabel: 'ФИО врача',
															store:new Ext.data.SimpleStore(  {
																fields: [{name:'MedPersonal_id',type:'string'},
																		 {name:'EvnReanimatCondition_Doctor',type:'string'}]
															}),
															listeners: {
																'change':function (field, newValue, oldValue) {
																	var EvnScalesGridRow = win.findById('swERPEW_EvnReanimatCondition_Grid').getSelectionModel().getSelected();  //выбранная строка в гриде Реанимационных мероприятий
																	EvnScalesGridRow.data['EvnReanimatCondition_Doctor'] = newValue;
																	EvnScalesGridRow.commit();
																	win.findById('swERPEW_RC_Print_Doctor_FIO').store.clearFilter();
																}
															}
														}
													]
												}

											]
										},    //BOB - 27.09.2019



										//Панель Антропометрические данные //BOB - 24.01.2019
										{
											id: 'swERPEW_RC_Antropometr_Panel',
											xtype: 'fieldset',
											autoHeight: true,
											title: langs('Антропометрические данные'),
											collapsible: true,
											//collapsed: true,  
											layout: 'column',
											style: 'margin: 3px 0 0 0; padding:0; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											listeners: {
												collapse: function(p) {
													win.doLayout();
												},
												expand: function(p) {
													win.doLayout();
												}
											},
											items:[	
												//Рост
												{							
													layout:'form',
													style:'margin-top: 1px; margin-right: 2px; ',
													labelWidth:120,
													border:false,
													items:[	
														{
															xtype: 'textfield',
															id: 'swERPEW_RC_Height',
															fieldLabel:'Рост',
															labelSeparator: '',
															width: 80,
															disabled: true
														}
													]
												},
												new Ext.Button({
													id: 'swERPEW_RC_Height_Add_Button',
													iconCls: 'add16',
													text: '',
													handler: function(b,e)
													{
														this.EvnRC_AntropometrAdd('Height', this.findById('swERPEW_EvnReanimatCondition_setDate').getValue());
													}.createDelegate(this)
												}),
												//Вес
												{							
													layout:'form',
													style:'margin-top: 1px; margin-right: 2px; ',
													labelWidth:120,
													border:false,
													items:[	
														{
															xtype: 'textfield',
															id: 'swERPEW_RC_Weight',
															fieldLabel:'Вес',
															labelSeparator: '',
															width: 80,
															disabled: true
														}
													]
												},
												new Ext.Button({
													id: 'swERPEW_RC_Weight_Add_Button',
													iconCls: 'add16',
													text: '',
													handler: function(b,e)
													{
														this.EvnRC_AntropometrAdd('Weight', this.findById('swERPEW_EvnReanimatCondition_setDate').getValue());
													}.createDelegate(this)
												}),
												//Индекс массы тела
												{							
													layout:'form',
													style:'margin-top: 1px;',
													labelWidth:60,
													border:false,
													items:[	
														{
															xtype: 'textfield',
															id: 'swERPEW_RC_IMT',
															fieldLabel:'ИМТ',
															labelSeparator: '',
															width: 80,
															disabled: true
														}
													]
												}
											]	
										},  //BOB - 24.01.2019
										//Панель Состояние //BOB - 24.01.2019
										{
											id: 'swERPEW_RC_Condition_Panel',
											xtype: 'fieldset',
											autoHeight: true,
											title: langs('Состояние'),
											collapsible: true,
											//collapsed: true,  //на время разработки
											layout: 'form',
											style: 'margin: 3px 0 0 0; padding:0; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											listeners: {
												collapse: function(p) {
													win.doLayout();
												},
												expand: function(p) {
													win.doLayout();
												}
											},
											items:[	
												//панель СОСТОЯНИЕ пациента / Sofa /Apache /температура тела
												{
													layout:'column',
													border:false,
													items:[	
														//combo - состояние пациента
														{							
															layout:'form',
															style:'margin-top: 4px;',
															labelWidth:110,
															border:false,
															items:[	
																//BOB - 21.03.2019
																{
																	id: 'swERPEW_RC_Condition',
																	hiddenName: 'RC_Condition',									
																	xtype: 'swextemporalcomptypecombo',
																	fieldLabel: langs('Состояние'),
																	allowBlank: false,
																	comboSubject: 'ReanimConditionType',
																	width: 250,
																	lastQuery: '',
																	listeners: {
																		'select': function(combo, record, index) {
																			var EvnScalesGridRow = win.findById('swERPEW_EvnReanimatCondition_Grid').getSelectionModel().getSelected();  //выбранная строка в гриде регулярного наблюдения состояния    
																			EvnScalesGridRow.data['ReanimConditionType_id'] = record.data.ReanimConditionType_id;
																			EvnScalesGridRow.data['Condition_Name'] = record.data.ReanimConditionType_Name;
																			EvnScalesGridRow.commit();
																		},
																		'expand': function	(combo)	{
																			combo.getStore().clearFilter();
																			combo.getStore().filterBy(function (rec) {
																				return rec.get('ReanimConditionType_id').inlist([1,2,3,4,5,6,7]);
																			});
																		}.createDelegate(this)																	}
																}																
															]
														},
														//температура тела  //BOB - 28.08.2018
														{							
															layout:'form',
															style:'margin-top: 4px;',
															labelWidth:80,
															border:false,
															items:[	
																new Ext.form.NumberField({
																	value: 0,
																	id: 'swERPEW_RC_Temperature',
																	fieldLabel:'Температура',
																	labelSeparator: '',
																	enableKeyEvents: true,
																	width: 60,
																	plugins:[ new Ext.ux.InputTextMask('99.9', true) ],
																	listeners:{
																		'keyup':function (obj, e) {
																			var EvnScalesGridRow = win.findById('swERPEW_EvnReanimatCondition_Grid').getSelectionModel().getSelected();  //выбранная строка в гриде регулярного наблюдения состояния 
																			EvnScalesGridRow.data['EvnReanimatCondition_Temperature'] = obj.getValue();
																			EvnScalesGridRow.commit();
																		}.createDelegate(this)
																	}
																})					
															]
														},
														//Лейбл °C
														{									
															layout:'form',
															style:'margin-top: 7px;',
															width: 15,
															border:false,
															//bodyStyle:'background-color: transparent',
															items:[
																new Ext.form.Label({
																	text: '°C'
																})					
															]
														},    //BOB - 28.08.2018
														//Sofa
														{							
															layout:'form',
															style:'margin-top: 4px;',
															labelWidth:60,
															border:false,
															items:[	
																{
																	xtype: 'textfield',
																	id: 'swERPEW_RC_sofa',
																	fieldLabel:'По SOFA',
																	labelSeparator: '',
																	width: 60,
																	disabled: true
																	//style:'margin-top: 6px; '//,

																}
															]
														},
														//Apache
														{							
															layout:'form',
															style:'margin-top: 4px;',
															labelWidth:80,
															border:false,
															items:[	
																{
																	xtype: 'textfield',
																	id: 'swERPEW_RC_apache',
																	fieldLabel:'По APACHE',
																	labelSeparator: '',
																	width: 60,
																	disabled: true
																}
															]
														}
													]
												},
												//combo - Реанимационный синдром - тоже относится к блоку СОСТОЯНИЕ //BOB - 24.01.2019
												{							
													layout:'form',
													//style:'margin-top: 4px;',
													labelWidth:110,
													border:false,
													items:[	
														new Ext.ux.Andrie.Select({
															allowBlank: true,
															multiSelect: true,
															mode: 'local',
															anchor: '99%',
															fieldLabel: 'Синдром',
															labelSeparator: '',
															xtype: 'swmedpersonalcombo',
															displayField: 'ReanimatSyndromeType_Name',
															valueField: 'ReanimatSyndromeType_id',
															listWidth: 320,
															//name: 'MedPersonal_id',
															id: 'swERPEW_RC_ReanimatSyndrome',
															tpl: '<tpl for="."><div class="x-combo-list-item">'+
																'{ReanimatSyndromeType_Name} '+ '&nbsp;' +
																'</div></tpl>' ,
															store: new Ext.data.Store({
																autoLoad: false,
																reader: new Ext.data.JsonReader(
																		{
																			id: 'ReanimatSyndromeType_id'
																		},
																		[
																			{name: 'ReanimatSyndromeType_id', mapping: 'ReanimatSyndromeType_id'},
																			{name: 'ReanimatSyndromeType_Name', mapping: 'ReanimatSyndromeType_Name'}
																		]),

																url: '/?c=EvnReanimatPeriod&m=loadReanimatSyndromeType'
															}),
															listeners: {
																'change': function(combo, newValue) {
																	var EvnScalesGridRow = win.findById('swERPEW_EvnReanimatCondition_Grid').getSelectionModel().getSelected();  //выбранная строка в гриде регулярного наблюдения состояния    
																	EvnScalesGridRow.data['EvnReanimatCondition_SyndromeType'] = combo.getValue();
																	EvnScalesGridRow.commit();
																}
															}
														})												
													]
												},//BOB - 24.01.2019
												//Реанимационный синдром: текстовое поле  //BOB - 16.09.2019
												{
													layout:'form',
													//style:'margin-top: 4px;',
													labelWidth:5,
													border:false,
													items:[
														{
															allowBlank: true,
															fieldLabel: '',
															labelSeparator: '',
															name: 'swERPEW_RC_ReanimatSyndromeTxt',
															id: 'swERPEW_RC_ReanimatSyndromeTxt',
															width: 1270,
															style:'margin-top: 2px; margin-left: 4px;',
														//	tabIndex: TABINDEX_MS,
															value:'',
															xtype: 'textfield',
															listeners:{
																'change':function (field, newValue, oldValue) {
																	var EvnScalesGridRow = win.findById('swERPEW_EvnReanimatCondition_Grid').getSelectionModel().getSelected();  //выбранная строка в гриде Реанимационных мероприятий
																	EvnScalesGridRow.data['EvnReanimatCondition_SyndromeTxt'] = newValue;
																	EvnScalesGridRow.commit();
																}.createDelegate(this)
															}
														}
													]
												} //BOB - 16.09.2019
											]
										},
										//панель - Совместный осмотр  //BOB - 28.08.2018
										{
											id: 'swERPEW_RC_CollectiveSurvey_Panel',
											labelWidth: 5,
											xtype: 'fieldset',
											autoHeight: true,
											title: langs('Совместный осмотр'),
											collapsible: true,
											//collapsed: true,
											layout: 'form',
											style: 'margin: 3px 0 0 0; padding:0; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											listeners: {
												collapse: function(p) {
													win.doLayout();
												},
												expand: function(p) {
													win.doLayout();
												}
											},
											items:[	
												new Ext.form.TextArea({
													labelSeparator: '',
													id: 'swERPEW_RC_CollectiveSurvey',
													name: 'RC_CollectiveSurvey',
													//enableKeyEvents: true,														
													height: 154,
													anchor: '99%',
													//width:800,
													listeners:{
														'change':function (field, newValue, oldValue) {
															var EvnScalesGridRow = win.findById('swERPEW_EvnReanimatCondition_Grid').getSelectionModel().getSelected();  //выбранная строка в гриде Реанимационных мероприятий
															EvnScalesGridRow.data['EvnReanimatCondition_CollectiveSurvey'] = newValue;
															EvnScalesGridRow.commit();
														}.createDelegate(this)
													}
												})
											]
										},    //BOB - 28.08.2018										
										//панель - Жалобы пациента 
										{
											id: 'swERPEW_RC_Complaint_Panel',
											labelWidth: 5,
											xtype: 'fieldset',
											autoHeight: true,
											title: langs('Жалобы пациента'),
											collapsible: true,
											//collapsed: true,
											layout: 'form',
											style: 'margin: 3px 0 0 0; padding:0; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											listeners: {
												collapse: function(p) {
													win.doLayout();
												},
												expand: function(p) {
													win.doLayout();
												}
											},
											items:[	
												new Ext.form.TextArea({
													labelSeparator: '',
													id: 'swERPEW_RC_Complaint',
													name: 'RC_Complaint',
													height: 154,
													anchor: '99%',
													listeners:{
														'change':function (field, newValue, oldValue) {
															var EvnScalesGridRow = win.findById('swERPEW_EvnReanimatCondition_Grid').getSelectionModel().getSelected();  //выбранная строка в гриде Реанимационных мероприятий
															EvnScalesGridRow.data['EvnReanimatCondition_Complaint'] = newValue;
															EvnScalesGridRow.commit();
														}.createDelegate(this)
													}
												})												
											]
										},
										//Панель Сознание //BOB - 24.01.2019
										{
											id: 'swERPEW_RC_Conscious_Panel',
											xtype: 'fieldset',
											autoHeight: true,
											title: langs('Сознание'),
											collapsible: true,
											//collapsed: true,  //на времф разработки
											layout: 'form',
											style: 'margin: 3px 0 0 0; padding:0; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											listeners: {
												collapse: function(p) {
													win.doLayout();
												},
												expand: function(p) {
													win.doLayout();
												}
											},
											items:[	
												//панель - Уровень сознания
												{
													layout:'column',
													border:false,
													items:[	
														//combo - Уровень сознания 
														{							
															layout:'form',
															style:'margin-top: 2px;',
															labelWidth:110,
															border:false,
															items:[	
																{
																	id: 'swERPEW_RC_Conscious',
																	hiddenName: 'RC_Conscious',
																	xtype: 'swreanimatconsciouscombo',
																	width: 240,
																	listeners: {
																		'select': function(combo, record, index) {
																			var EvnScalesGridRow = win.findById('swERPEW_EvnReanimatCondition_Grid').getSelectionModel().getSelected();  //выбранная строка в гриде регулярного наблюдения состояния    
																			EvnScalesGridRow.data['ConsciousType_id'] = record.data.ConsciousType_id;
																			EvnScalesGridRow.commit();
																		},
																		'expand': function(combo) {
																			combo.getStore().clearFilter();
																			combo.getStore().filterBy(function (rec) {
																				return rec.get('ConsciousType_id').inlist([1,2,3,4,5,6,7,8,9]);
																			});
																		}
																	} 
																} 
															]
														},
														//кнопка По Глазго из списка шкал
														{									
															layout:'form',
															style:'margin-top: 2px; margin-left: 10px ',
															items:[
																new Ext.Button({
																	id: 'swERPEW_RC_Conscious_from_glasgow_Button',
																	text: 'По Глазго из списка шкал',
																	handler: function(b,e)
																	{	
																		//если панель шкал уже открывалась
																		if(win.findById('swERPEW_Scales_Panel').isLoaded){
																			b.handler_2();
																		}
																		else {
																			//загрузка грида исследований по шкалам																	
																			win.findById('swERPEW_EvnScales_Grid').getStore().load({
																				params:{
																					EvnScale_pid: win.EvnReanimatPeriod_id
																				},
																				callback: function(records, options, success) {
																					b.handler_2();
																				}
																			});																	
																		}
																	}.createDelegate(this),
																	handler_2: function() {
																		var Ocsigen_Val =  0;
																		var ScalesStore = win.findById('swERPEW_EvnScales_Grid').getStore().data.items;
																		//console.log('BOB_ScalesStore',ScalesStore);
																		for(var i in ScalesStore){
																			if ((ScalesStore[i].data) && (ScalesStore[i].data.EvnScale_id != "New_GUID_Id")){
																				if((ScalesStore[i].data.ScaleType_SysNick == "glasgow") || (ScalesStore[i].data.ScaleType_SysNick == "glasgow_ch") || (ScalesStore[i].data.ScaleType_SysNick == "glasgow_neonat")){
																					Ocsigen_Val =  parseInt(ScalesStore[i].data.EvnScale_Result);
																					break;
																				}
																			}
																		}
																		if (Ocsigen_Val == 0){
																			Ext.MessageBox.alert('Невозможно!', 'Отсутствуют сохранённые результаты по шкале Глазго!');
																			return;
																		}
																		var ConsciousStore = win.findById('swERPEW_RC_Conscious').getStore().data.items;
																		var swERPEW_RC_ConsciousValue = -1;
																		for (var i in ConsciousStore){
																			if (ConsciousStore[i].data.ConsciousType_ByGlasgow){
																				var aGlasgowCodes = ConsciousStore[i].data.ConsciousType_ByGlasgow.split(",");
																				if (aGlasgowCodes.indexOf(Ocsigen_Val.toString()) != -1){
																					swERPEW_RC_ConsciousValue = ConsciousStore[i].data.ConsciousType_id;
																					break;
																				}
																			}
																		}
																		if (swERPEW_RC_ConsciousValue != -1){
																			win.findById('swERPEW_RC_Conscious').setValue(swERPEW_RC_ConsciousValue);
																			var EvnScalesGridRow = win.findById('swERPEW_EvnReanimatCondition_Grid').getSelectionModel().getSelected();  //выбранная строка в гриде регулярного наблюдения состояния    
																			EvnScalesGridRow.data['ConsciousType_id'] = swERPEW_RC_ConsciousValue;
																			win.findById('swERPEW_RC_glasgow').setValue(Ocsigen_Val);						//BOB - 16.09.2019
																			EvnScalesGridRow.data['EvnReanimatCondition_glasgow'] = Ocsigen_Val;			//BOB - 16.09.2019
																			EvnScalesGridRow.commit();
																		}
																		else
																			Ext.MessageBox.alert('Невозможно!', 'Значение не найдено!');
																	}
																})
															]
														},
														//Уровень сознания - вариант пользователя: текстовое поле 
														{
															allowBlank: true,
															fieldLabel: '',
															labelSeparator: '',
															name: 'swERPEW_RC_ConsciousTxt',
															id: 'swERPEW_RC_ConsciousTxt',
															width: 775,
															style:'margin-top: 2px; margin-left: 4px;',
														//	tabIndex: TABINDEX_MS,
															value:'',
															xtype: 'textfield',
															//hidden: true,
															listeners:{
																'change':function (field, newValue, oldValue) {
																	var EvnScalesGridRow = win.findById('swERPEW_EvnReanimatCondition_Grid').getSelectionModel().getSelected();  //выбранная строка в гриде Реанимационных мероприятий
																	EvnScalesGridRow.data['EvnReanimatCondition_ConsTxt'] = newValue;
																	EvnScalesGridRow.commit();
																}.createDelegate(this)
															}
														}
													]	
												},
												//панель - сознание по шкалам
												{
													layout:'column',
													border:false,
													items:[

														//Glasgow - баллы  //BOB - 16.09.2019
														{
															layout:'form',
															style:'margin-top: 2px;',
															labelWidth:110,
															border:false,
															items:[
																{
																	xtype: 'textfield',
																	id: 'swERPEW_RC_glasgow',
																	fieldLabel:'По Glasgow',
																	labelSeparator: '',
																	width: 60,
																	disabled: true
																	//style:'margin-top: 6px; '//,

																}
															]
														},  //BOB - 16.09.2019


														//FOUR  //BOB - 16.09.2019
														{
															layout:'form',
															style:'margin-top: 2px;',
															labelWidth:60,
															border:false,
															items:[
																{
																	xtype: 'textfield',
																	id: 'swERPEW_RC_four',
																	fieldLabel:'По FOUR',
																	labelSeparator: '',
																	width: 60,
																	disabled: true
																	//style:'margin-top: 6px; '//,

																}
															]
														}  //BOB - 16.09.2019
													]
												},
												//панель - Речь / RASS
												{
													layout:'column',
													border:false,
													items:[	
														//combo - Речь 
														{							
															layout:'form',
															style:'margin-top: 2px;',
															labelWidth:110,
															border:false,
															items:[	
																//BOB - 21.03.2019
																{
																	id: 'swERPEW_RC_SpeechDisorder',
																	hiddenName: 'RC_SpeechDisorder',									
																	xtype: 'swextemporalcomptypecombo',
																	fieldLabel: langs('Речь'),
																	labelSeparator: '',
																	allowBlank: true,
																	comboSubject: 'SpeechDisorderType',
																	width: 240,
																	lastQuery: '',
																	listeners: {
																		'select': function(combo, record, index) {
																			var EvnScalesGridRow = win.findById('swERPEW_EvnReanimatCondition_Grid').getSelectionModel().getSelected();  //выбранная строка в гриде регулярного наблюдения состояния    
																			EvnScalesGridRow.data['SpeechDisorderType_id'] = record.data.SpeechDisorderType_id;
																			EvnScalesGridRow.commit();
																		}
																	}
																}																
															]
														},
														//RASS
														{							
															layout:'form',
															style:'margin-top: 4px;',
															labelWidth:60,
															border:false,
															items:[	
																{
																	xtype: 'textfield',
																	id: 'swERPEW_RC_rass',
																	fieldLabel:'По RASS',
																	labelSeparator: '',
																	width: 60,
																	disabled: true
																	//style:'margin-top: 6px; '//,

																}
															]
														}
													]
												}
											]
										},										
										//Неврологический статус
										{
											id: 'swERPEW_RC_Neurologic_Status_Bis_Panel',
											labelWidth: 5,
											xtype: 'fieldset',
											autoHeight: true,
											title: langs('Неврологический статус'),
											collapsible: true,
											//collapsed: true,
											layout: 'form',
											style: 'margin: 3px 0 0 0; padding:0; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											listeners: {
												collapse: function(p) {
													win.doLayout();
												},
												expand: function(p) {
													win.doLayout();
												}
											},
											items:[
												new Ext.form.TextArea({
													fieldLabel: '',
													labelSeparator: '',
													id: 'swERPEW_RC_Neurologic_Status_Bis',
													name: 'RC_Neurologic_Status_Bis',
													//enableKeyEvents: true,
													height: 154,
													//width:800,
													anchor: '99%',
													listeners:{
														'change':function (field, newValue, oldValue) {
															var EvnScalesGridRow = win.findById('swERPEW_EvnReanimatCondition_Grid').getSelectionModel().getSelected();  //выбранная строка в гриде Реанимационных мероприятий
															EvnScalesGridRow.data['EvnReanimatCondition_NeurologicStatus'] = newValue;
															EvnScalesGridRow.commit();
														}.createDelegate(this)
													}
												})
											]
										},
										//Панель Зрачки - вместо Глаза //BOB - 24.01.2019
										{
											id: 'swERPEW_RC_Eyes',
											layout:'column',
											xtype: 'fieldset',
											autoHeight: true,
											title: langs('Зрачки'),
											collapsible: true,
											//collapsed: true,  //на времф разработки
											style: 'margin: 3px 0 0 0; padding: 0 0 3px 0; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											listeners: {
												collapse: function(p) {
													win.doLayout();
												},
												expand: function(p) {
													win.doLayout();
												}
											},
											items:[	
												//Лейбл Зрачки 
												{									
													layout:'form',
													style:' margin-left: 22px; margin-top: 4px; font-size: 12px ',
													items:[
														new Ext.form.Label({
															text: 'Размеры'
														})					
													]
												},
												{									
													layout:'form',
													labelWidth:1,
													style: 'margin-top: 4px;  margin-left: 4px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
													items:[
														new Ext.form.RadioGroup({
															id:'swERPEW_RC_Eyes1',
															labelSeparator: '',
															vertical: true,
															columns: 2,
															items: [
																{boxLabel: '---', name: 'Eyes1', inputValue: 0, width: 120}, 
																{boxLabel: 'равные', name: 'Eyes1', inputValue: 1, width: 120}, 
																{boxLabel: 'анизокория D > S', name: 'Eyes1', inputValue: 2, width: 130},
																{boxLabel: 'анизокория S > D', name: 'Eyes1', inputValue: 3, width: 130}
															],
															listeners: {
																'change': function(field, checked) {
																	win.findById('swERPEW_RC_Eyes').change_handler(field, checked);
																}
															}
														})	
													]
												},
												{									
													layout:'form',
													labelWidth:1,
													style: 'margin-top: 4px;  margin-left: 4px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
													items:[
														new Ext.form.RadioGroup({
															id:'swERPEW_RC_Eyes2',
															labelSeparator: '',
															vertical: true,
															columns: 3,
															items: [
																{boxLabel: '---',  name: 'Eyes2',  inputValue: 0, width: 60 },
																{boxLabel: 'миоз',  name: 'Eyes2',  inputValue: 1, width: 120 },
																{boxLabel: 'мидриаз', name: 'Eyes2', inputValue: 2, width: 120}
															],
															listeners: {
																'change': function(field, checked) {
																	win.findById('swERPEW_RC_Eyes').change_handler(field, checked);
																}
															}
														})	
													]
												},
												//Лейбл Фотореакция 
												{									
													layout:'form',
													style:' margin-left: 22px; margin-top: 4px; font-size: 12px ',
													items:[
														new Ext.form.Label({
															text: 'Фотореакция'
														})					
													]
												},
												{									
													layout:'form',
													labelWidth:1,
													style: 'margin-top: 4px;  margin-left: 4px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
													items:[
														new Ext.form.RadioGroup({
															id:'swERPEW_RC_Eyes3',
															labelSeparator: '',
															vertical: true,
															columns: 3,
															items: [
																{boxLabel: '---', name: 'Eyes3', inputValue: 0, width: 60}, 
																{boxLabel: 'сохранена', name: 'Eyes3', inputValue: 1, width: 120}, 
																{boxLabel: 'отсутствует', name: 'Eyes3', inputValue: 2, width: 120}
															],
															listeners: {
																'change': function(field, checked) {
																	win.findById('swERPEW_RC_Eyes').change_handler(field, checked);
																}
															}
														})	
													]
												}
											],
											change_handler: function(field, checked) {
												if(checked){
													var EvnScalesGridRow = win.findById('swERPEW_EvnReanimatCondition_Grid').getSelectionModel().getSelected();  //выбранная строка в гриде Реанимационных мероприятий
													if (!(EvnScalesGridRow)) return false;
													var Eyes = EvnScalesGridRow.data['EvnReanimatCondition_Eyes'];
													var position = parseInt(field.id.substr(field.id.length - 1, 1));
													var value = checked.inputValue;
													Eyes = Eyes.split("");
													Eyes[position - 1] = value;
													Eyes = Eyes.join("");

													EvnScalesGridRow.data['EvnReanimatCondition_Eyes'] = Eyes;
													EvnScalesGridRow.commit();
												}
											}
										},
										//панель - кожные покровы 
										{
											xtype: 'fieldset',
											id: 'swERPEW_RC_Skin_Panel',
											autoHeight: true,
											//collapsed: true,
											title: langs('Кожный покров'),
											collapsible: true,
											layout: 'form',
											style: 'margin: 3px 0 0 0; padding:0; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											listeners: {
												collapse: function(p) {
													win.doLayout();
												},
												expand: function(p) {
													win.doLayout();
												}
											},
											items:[	
												//панель - окраска 
												{
													layout:'column',
													border:false,
													items:[	
														//combo - окраска 
														{							
															layout:'form',
															style:'margin-top: 2px;',
															labelWidth:110,
															border:false,
															items:[	
																//BOB - 21.03.2019
																{
																	id: 'swERPEW_RC_Skin',
																	hiddenName: 'RC_Skin',									
																	xtype: 'swextemporalcomptypecombo',
																	fieldLabel: langs('Окрас'),
																	labelSeparator: '',
																	allowBlank: true,
																	comboSubject: 'SkinType',
																	width: 250,
																	lastQuery: '',
																	listeners: {
																		'select': function(combo, record, index) {
																			var EvnScalesGridRow = win.findById('swERPEW_EvnReanimatCondition_Grid').getSelectionModel().getSelected();  //выбранная строка в гриде регулярного наблюдения состояния    
																			EvnScalesGridRow.data['SkinType_id'] = record.data.SkinType_id;
																			EvnScalesGridRow.commit();
//																			//если пользовательский вариант
//																			if (record.data.SkinType_id == 5)
//																				win.findById('swERPEW_RC_SkinTxt').setVisible( true );
//																			else {
//																				win.findById('swERPEW_RC_SkinTxt').setVisible( false );
//																				win.findById('swERPEW_RC_SkinTxt').setValue('');
//																				EvnScalesGridRow.data['EvnReanimatCondition_SkinTxt'] = '';
//																				EvnScalesGridRow.commit();
//																			}
																		}
																	}
																}
															]
														},
														//кожные покровы: текстовое поле  - варианта пользователя
														{
															allowBlank: true,
															name: 'swERPEW_RC_SkinTxt',
															id: 'swERPEW_RC_SkinTxt',
															width: 925,
															style:'margin-top: 2px; margin-left: 4px;',
														//	tabIndex: TABINDEX_MS,
															value:'',
															xtype: 'textfield',
//															hidden: true,
															listeners:{
																'change':function (field, newValue, oldValue) {
																	var EvnScalesGridRow = win.findById('swERPEW_EvnReanimatCondition_Grid').getSelectionModel().getSelected();  //выбранная строка в гриде Реанимационных мероприятий
																	EvnScalesGridRow.data['EvnReanimatCondition_SkinTxt'] = newValue;
																	EvnScalesGridRow.commit();																	//}
																}.createDelegate(this)
															}
														}									
													]
												},
												//панель - влажность, тургор
												{
													id: 'swERPEW_RC_WetTurgor',
													layout:'column',
													border:false,
													items:[	
														//Лейбл влажность 
														{									
															layout:'form',
															style:' margin-left: 22px; margin-top: 4px; font-size: 12px ',
															items:[
																new Ext.form.Label({
																	text: 'Влажность'
																})					
															]
														},
														{									
															layout:'form',
															labelWidth:1,
															style: 'margin: 4px 0 4px 0;  margin-left: 4px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
															items:[
																new Ext.form.RadioGroup({
																	id:'swERPEW_RC_WetTurgor1',
																	labelSeparator: '',
																	vertical: true,
																	columns: 2,
																	items: [
																		{boxLabel: '---', name: 'WetTurgor1', inputValue: 0, width: 120},
																		{boxLabel: 'влажный', name: 'WetTurgor1', inputValue: 1, width: 120},
																		{boxLabel: 'сухой', name: 'WetTurgor1', inputValue: 2, width: 120},
																		{boxLabel: 'умеренной влажности', name: 'WetTurgor1', inputValue: 3, width: 160}
																	],
																	listeners: {
																		'change': function(field, checked) {
																			win.findById('swERPEW_RC_WetTurgor').change_handler(field, checked);
																		}
																	}
																})	
															]
														},
														//Лейбл тургор 
														{									
															layout:'form',
															style:' margin-left: 22px; margin-top: 4px; font-size: 12px ',
															items:[
																new Ext.form.Label({
																	text: 'Тургор'
																})					
															]
														},
														{									
															layout:'form',
															labelWidth:1,
															style: 'margin-top: 4px;  margin-left: 4px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
															items:[
																new Ext.form.RadioGroup({
																	id:'swERPEW_RC_WetTurgor2',
																	labelSeparator: '',
																	vertical: true,
																	columns: 3,
																	items: [
																		{boxLabel: '---',  name: 'WetTurgor2',  inputValue: 0, width: 60 },
																		{boxLabel: 'удовлетворительный',  name: 'WetTurgor2',  inputValue: 1, width: 160 },
																		{boxLabel: 'сниженный', name: 'WetTurgor2', inputValue: 2, width: 120}
																	],
																	listeners: {
																		'change': function(field, checked) {
																			win.findById('swERPEW_RC_WetTurgor').change_handler(field, checked);
																		}
																	}
																})	
															]
														},
														//Ватерлоу
														{							
															layout:'form',
															style:'margin-top: 4px;',
															labelWidth:80,
															border:false,
															items:[	
																{
																	xtype: 'textfield',
																	id: 'swERPEW_RC_waterlow',
																	fieldLabel:'По Waterlow',
																	labelSeparator: '',
																	width: 60,
																	disabled: true
																}
															]
														}
													],
													change_handler: function(field, checked) {
														if(checked){
															var EvnScalesGridRow = win.findById('swERPEW_EvnReanimatCondition_Grid').getSelectionModel().getSelected();  //выбранная строка в гриде Реанимационных мероприятий
															if (!(EvnScalesGridRow)) return false;
															var WetTurgor = EvnScalesGridRow.data['EvnReanimatCondition_WetTurgor'];
															var position = parseInt(field.id.substr(field.id.length - 1, 1));
															var value = checked.inputValue;
															WetTurgor = WetTurgor.split("");
															WetTurgor[position - 1] = value;//.toString(); 
															WetTurgor = WetTurgor.join("");

															EvnScalesGridRow.data['EvnReanimatCondition_WetTurgor'] = WetTurgor;
															EvnScalesGridRow.commit();
														}
													}
												},
												//панель Видимые слизистые 
												{
													layout:'column',
													border:false,
													items:[	
														//combo - Видимые слизистые 
														{							
															layout:'form',
															style:'margin-top: 2px;',
															labelWidth:160,
															border:false,
															items:[	
																//BOB - 21.03.2019
																{
																	id: 'swERPEW_RC_SkinM',
																	hiddenName: 'RC_SkinM',									
																	xtype: 'swextemporalcomptypecombo',
																	fieldLabel: langs('Видимые слизистые:окрас'),
																	labelSeparator: '',
																	allowBlank: true,
																	comboSubject: 'SkinType',
																	suffix: 'M',
																	width: 250,
																	lastQuery: '',
																	listeners: {
																		'select': function(combo, record, index) {
																			var EvnScalesGridRow = win.findById('swERPEW_EvnReanimatCondition_Grid').getSelectionModel().getSelected();  //выбранная строка в гриде регулярного наблюдения состояния    
																			EvnScalesGridRow.data['SkinType_mid'] = record.data.SkinType_id;
																			EvnScalesGridRow.commit();
//																			//если пользовательский вариант
//																			if (record.data.SkinType_id == 5)
//																				win.findById('swERPEW_RC_MucusTxt').setVisible( true );
//																			else {
//																				win.findById('swERPEW_RC_MucusTxt').setVisible( false );
//																				win.findById('swERPEW_RC_MucusTxt').setValue('');
//																				EvnScalesGridRow.data['EvnReanimatCondition_MucusTxt'] = '';
//																				EvnScalesGridRow.commit();
//																			}
																		}
																	}
																}
															]
														},
														//Видимые слизистые: текстовое поле  - варианта пользователя
														{
															allowBlank: true,
															name: 'swERPEW_RC_MucusTxt',
															id: 'swERPEW_RC_MucusTxt',
															width: 875,
															style:'margin-top: 2px; margin-left: 4px;',
														//	tabIndex: TABINDEX_MS,
															value:'',
															xtype: 'textfield',
//															hidden: true,
															listeners:{
																'change':function (field, newValue, oldValue) {
																	var EvnScalesGridRow = win.findById('swERPEW_EvnReanimatCondition_Grid').getSelectionModel().getSelected();  //выбранная строка в гриде Реанимационных мероприятий
																	EvnScalesGridRow.data['EvnReanimatCondition_MucusTxt'] = newValue;
																	EvnScalesGridRow.commit();
																}.createDelegate(this)
															}
														}									
													]
												},
												//панель - Нарушения микроциркуляции, Периферические отёки
												{
													id: 'swERPEW_RC_MicrocEdem',
													layout:'column',
													border:false,
													items:[	
														//Нарушения микроциркуляции 
														{									
															layout:'form',
															style:' margin-left: 22px; margin-top: 4px; font-size: 12px ',
															items:[
																new Ext.form.Label({
																	text: 'Нарушения микроциркуляции'
																})					
															]
														},
														{									
															layout:'form',
															labelWidth:1,
															style: 'margin: 4px 0 4px 0;  margin-left: 4px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
															items:[
																new Ext.form.RadioGroup({
																	id:'swERPEW_RC_IsMicrocDist',
																	labelSeparator: '',
																	vertical: true,
																	columns: 3,
																	items: [
																		{boxLabel: '---', name: 'IsMicrocDist', inputValue: 0, width: 60},
																		{boxLabel: 'не выражены', name: 'IsMicrocDist', inputValue: 1, width: 120},
																		{boxLabel: 'выражены', name: 'IsMicrocDist', inputValue: 2, width: 120}
																	],
																	listeners: {
																		'change': function(field, checked) {
																			var EvnScalesGridRow = win.findById('swERPEW_EvnReanimatCondition_Grid').getSelectionModel().getSelected();  //выбранная строка в гриде Реанимационных мероприятий
																			EvnScalesGridRow.data['EvnReanimatCondition_IsMicrocDist'] = checked.inputValue > 0 ? checked.inputValue : null ;
																			EvnScalesGridRow.commit();
																		}
																	}
																})	
															]
														},
														//Лейбл Периферические отёки 
														{									
															layout:'form',
															style:' margin-left: 22px; margin-top: 4px; font-size: 12px ',
															items:[
																new Ext.form.Label({
																	text: 'Периферические отёки'
																})					
															]
														},
														{									
															layout:'form',
															labelWidth:1,
															style: 'margin-top: 4px;  margin-left: 4px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
															items:[
																new Ext.form.RadioGroup({
																	id:'swERPEW_RC_IsPeriphEdem',
																	labelSeparator: '',
																	vertical: true,
																	columns: 3,
																	items: [
																		{boxLabel: '---',  name: 'IsPeriphEdem',  inputValue: 0, width: 60 },
																		{boxLabel: 'не выражены',  name: 'IsPeriphEdem',  inputValue: 1, width: 120 },
																		{boxLabel: 'выражены', name: 'IsPeriphEdem', inputValue: 2, width: 120}
																	],
																	listeners: {
																		'change': function(field, checked) {
																			var EvnScalesGridRow = win.findById('swERPEW_EvnReanimatCondition_Grid').getSelectionModel().getSelected();  //выбранная строка в гриде Реанимационных мероприятий
																			EvnScalesGridRow.data['EvnReanimatCondition_IsPeriphEdem'] = checked.inputValue > 0 ? checked.inputValue : null ;
																			EvnScalesGridRow.commit();
																		}
																	}
																})	
															]
														}
													]
												}
											]
										},
										//Панель Рефлексы //BOB - 24.01.2019
										{
											id: 'swERPEW_RC_Reflexes',
											layout:'column',
											xtype: 'fieldset',
											autoHeight: true,
											title: langs('Рефлексы'),
											collapsible: true,
											style: 'margin: 3px 0 0 0; padding: 0 0 3px 0; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											listeners: {
												collapse: function(p) {
													win.doLayout();
												},
												expand: function(p) {
													win.doLayout();
												}
											},
											items:[	
												//Лейбл Глотание 
												{									
													layout:'form',
													style:' margin-left: 22px; margin-top: 4px; font-size: 12px ',
													items:[
														new Ext.form.Label({
															text: 'Глотание'
														})					
													]
												},
												{									
													layout:'form',
													labelWidth:1,
													style: 'margin-top: 4px;  margin-left: 4px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
													items:[
														new Ext.form.RadioGroup({
															id:'swERPEW_RC_Reflexes1',
															labelSeparator: '',
															vertical: true,
															columns: 3,
															items: [
																{boxLabel: '---', name: 'Reflexes1', inputValue: 0, width: 120},
																{boxLabel: 'нарушено', name: 'Reflexes1', inputValue: 1, width: 120},
																{boxLabel: 'не нарушено', name: 'Reflexes1', inputValue: 2, width: 130}
															],
															listeners: {
																'change': function(field, checked) {
																	win.findById('swERPEW_RC_Reflexes').change_handler(field, checked);
																}
															}
														})	
													]
												},
												//Лейбл Кашлевой рефлекс 
												{									
													layout:'form',
													style:' margin-left: 22px; margin-top: 4px; font-size: 12px ',
													items:[
														new Ext.form.Label({
															text: 'Кашлевой'
														})					
													]
												},
												{									
													layout:'form',
													labelWidth:1,
													style: 'margin-top: 4px;  margin-left: 4px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
													items:[
														new Ext.form.RadioGroup({
															id:'swERPEW_RC_Reflexes2',
															labelSeparator: '',
															vertical: true,
															columns: 2,
															items: [
																{boxLabel: '---',  name: 'Reflexes2',  inputValue: 0, width: 60 },
																{boxLabel: 'сохранён',  name: 'Reflexes2',  inputValue: 1, width: 120 },
																{boxLabel: 'снижен', name: 'Reflexes2', inputValue: 2, width: 120},
																{boxLabel: 'отсутствует', name: 'Reflexes2', inputValue: 3, width: 120}
															],
															listeners: {
																'change': function(field, checked) {
																	win.findById('swERPEW_RC_Reflexes').change_handler(field, checked);
																}
															}
														})	
													]
												},
												//Лейбл Корнеальный
												{									
													layout:'form',
													style:' margin-left: 22px; margin-top: 4px; font-size: 12px ',
													items:[
														new Ext.form.Label({
															text: 'Корнеальный'
														})					
													]
												},
												{									
													layout:'form',
													labelWidth:1,
													style: 'margin-top: 4px;  margin-left: 4px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
													items:[
														new Ext.form.RadioGroup({
															id:'swERPEW_RC_Reflexes3',
															labelSeparator: '',
															vertical: true,
															columns: 3,
															items: [
																{boxLabel: '---', name: 'Reflexes3', inputValue: 0, width: 60},
																{boxLabel: 'сохранён', name: 'Reflexes3', inputValue: 1, width: 120},
																{boxLabel: 'отсутствует', name: 'Reflexes3', inputValue: 2, width: 120}
															],
															listeners: {
																'change': function(field, checked) {
																	win.findById('swERPEW_RC_Reflexes').change_handler(field, checked);
																}
															}
														})	
													]
												}
											],
											change_handler: function(field, checked) {
												if(checked){
													var EvnScalesGridRow = win.findById('swERPEW_EvnReanimatCondition_Grid').getSelectionModel().getSelected();  //выбранная строка в гриде Реа��имационных мероприятий
													if (!(EvnScalesGridRow)) return false;
													var Reflexes = EvnScalesGridRow.data['EvnReanimatCondition_Reflexes'];
													var position = parseInt(field.id.substr(field.id.length - 1, 1));
													var value = checked.inputValue;
													Reflexes = Reflexes.split("");
													Reflexes[position - 1] = value;
													Reflexes = Reflexes.join("");
													EvnScalesGridRow.data['EvnReanimatCondition_Reflexes'] = Reflexes;
													EvnScalesGridRow.commit();
												}
											}
										},
										//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
										//панель - Менингеальные знаки
										{
											id: 'swERPEW_RC_MeningSign_Panel',
											layout:'column',
											xtype: 'fieldset',
											autoHeight: true,
											title: langs('Менингеальные знаки'),
											collapsible: true,
											style: 'margin: 3px 0 0 0; padding: 0 0 3px 0; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											listeners: {
												collapse: function(p) {
													win.doLayout();
												},
												expand: function(p) {
													win.doLayout();
												}
											},
											items:[
												{
													layout:'form',
													labelWidth:1,
													style: 'margin-top: 4px;  margin-left: 4px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
													items:[
														new Ext.form.RadioGroup({
															id:'swERPEW_RC_MeningSign',
															labelSeparator: '',
															vertical: true,
															columns: 2,
															items: [
																{boxLabel: '---',  name: 'MeningSign',  inputValue: 0, width: 60 },
																{boxLabel: 'есть',  name: 'MeningSign',  inputValue: 1, width: 160 },
																{boxLabel: 'нет', name: 'MeningSign', inputValue: 2, width: 120},
																{boxLabel: 'сомнительные', name: 'MeningSign', inputValue: 3, width: 160}
															],
															listeners: {
																'change': function(field, checked) {
																	var EvnScalesGridRow = win.findById('swERPEW_EvnReanimatCondition_Grid').getSelectionModel().getSelected();  //выбранная строка в гриде Реанимационных мероприятий
																	EvnScalesGridRow.data['EvnReanimatCondition_MeningSign'] = checked.inputValue;
																	EvnScalesGridRow.commit();
																}
															}
														})
													]
												},
												//Менингеальные знаки - вариант пользователя
												{
													layout:'form',
													style:'margin-top: 4px;',
													labelWidth:10,
													border:false,
													items:[
														{
															allowBlank: true,
															fieldLabel: '',
															labelSeparator: '',
															name: 'swERPEW_RC_MeningSignTxt',
															id: 'swERPEW_RC_MeningSignTxt',
															width: 930,
															style:'margin-top: 2px; margin-left: 4px;',
														//	tabIndex: TABINDEX_MS,
															value:'',
															xtype: 'textfield',
															listeners:{
																'change':function (field, newValue, oldValue) {
																	var EvnScalesGridRow = win.findById('swERPEW_EvnReanimatCondition_Grid').getSelectionModel().getSelected();  //выбранная строка в гриде Реанимационных мероприятий
																	EvnScalesGridRow.data['EvnReanimatCondition_MeningSignTxt'] = newValue;
																	EvnScalesGridRow.commit();
																}.createDelegate(this)
															}
														}
													]
												}
											]
										},

										//Панель Дыхание //BOB - 24.01.2019
										{
											id: 'swERPEW_RC_Breathing_Panel',
											layout:'form',
											xtype: 'fieldset',
											autoHeight: true,
											title: langs('Дыхание'),
											collapsible: true,
											style: 'margin: 3px 0 0 0; padding: 0 0 3px 0; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											listeners: {
												collapse: function(p) {
													win.doLayout();
												},
												expand: function(p) {
													win.doLayout();
												}
											},
											items:[	
												//панель - Дыхание
												{
													layout:'column',
													border:false,
													items:[	
														//combo - тип Дыхание 
														{							
															layout:'form',
															style:'margin-top: 4px;',
															labelWidth:110,
															border:false,
															items:[	
																//BOB - 21.03.2019
																{
																	id: 'swERPEW_RC_Breathing',
																	hiddenName: 'RC_Breathing',									
																	xtype: 'swcommonsprcombo',
																	fieldLabel: langs('Тип дыхания'),
																	labelSeparator: '',
																	allowBlank: true,
																	comboSubject: 'BreathingType',
																	width: 335,
																	lastQuery: '',
																	listeners: {
																		'select': function(combo, record, index) {
																			var EvnScalesGridRow = win.findById('swERPEW_EvnReanimatCondition_Grid').getSelectionModel().getSelected();  //выбранная строка в гриде регулярного наблюдения состояния    
																			EvnScalesGridRow.data['BreathingType_id'] = record.data.BreathingType_id;
																			EvnScalesGridRow.commit();
																		}
																	}
																}
																
															]
														},
														//Частота дыхания
														{							
															layout:'form',
															style:'margin-top: 4px;',
															labelWidth:100,
															border:false,
															items:[	
																new Ext.form.NumberField({
																	value: 0,
																	id: 'swERPEW_RC_BreathFrequency',
																	fieldLabel:'Частота дыхания',
																	labelSeparator: '',
																	width: 60,
		//															style:'margin-top: 6px; '//,
																	listeners:{
																		'change':function (field, newValue, oldValue) {
																			var EvnScalesGridRow = win.findById('swERPEW_EvnReanimatCondition_Grid').getSelectionModel().getSelected();  //выбранная строка в гриде событий шкал
																			EvnScalesGridRow.data['EvnReanimatCondition_BreathFrequency'] = newValue;
																			EvnScalesGridRow.commit();
																		}.createDelegate(this)
																	}
																})
															]
														},
														//Сатурация гемоглобина   //BOB - 28.08.2018
														{							
															layout:'form',
															style:'margin-top: 4px;',
															labelWidth:145,
															border:false,
															items:[	
																new Ext.form.NumberField({
																	value: 0,
																	id: 'swERPEW_RC_Saturation',
																	fieldLabel:'Сатурация гемоглобина',
																	labelSeparator: '',
																	width: 60,
		//															style:'margin-top: 6px; '//,
																	listeners:{
																		'change':function (field, newValue, oldValue) {
																			var EvnScalesGridRow = win.findById('swERPEW_EvnReanimatCondition_Grid').getSelectionModel().getSelected();  //выбранная строка в гриде событий шкал
																			EvnScalesGridRow.data['EvnReanimatCondition_Saturation'] = newValue;
																			EvnScalesGridRow.commit();
																		}.createDelegate(this)
																	}

																})   //BOB - 28.08.2018
															]
														}
														
													]
												}, {
													layout: 'column',
													border: false,
													defaults: {
														border: false,
														layout: 'form',
														style: 'margin-top: 4px;',
													},
													items: [{
														labelWidth: 220,
														items: [{
															allowBlank: true,
															allowDecimals: false,
															allowNegative: false,
															fieldLabel: langs('Фракция кислорода на вдохе (FiO<sub>2</sub>)'),
															listeners: {
																'change':function (field, newValue, oldValue) {
																	win.calcPaO2FiO2();
																	var EvnScalesGridRow = win.findById('swERPEW_EvnReanimatCondition_Grid').getSelectionModel().getSelected();  //выбранная строка в гриде событий шкал
																	if (EvnScalesGridRow) {
																		EvnScalesGridRow.data['EvnReanimatCondition_OxygenFraction'] = newValue;
																		EvnScalesGridRow.commit();
																	}
																}
															},
															name: 'EvnReanimatCondition_OxygenFraction',
															width: 100,
															xtype: 'numberfield'
														}]
													}, {
														labelWidth: 50,
														items: [{
															allowBlank: true,
															allowDecimals: false,
															allowNegative: false,
															fieldLabel: langs('РаО<sub>2</sub>'),
															listeners: {
																'change':function (field, newValue, oldValue) {
																	win.calcPaO2FiO2();
																	var EvnScalesGridRow = win.findById('swERPEW_EvnReanimatCondition_Grid').getSelectionModel().getSelected();  //выбранная строка в гриде событий шкал
																	if (EvnScalesGridRow) {
																		EvnScalesGridRow.data['EvnReanimatCondition_OxygenPressure'] = newValue;
																		EvnScalesGridRow.commit();
																	}
																}
															},
															name: 'EvnReanimatCondition_OxygenPressure',
															width: 100,
															xtype: 'numberfield'
														}]
													}, {
														labelWidth: 145,
														items: [{
															allowBlank: true,
															allowDecimals: true,
															allowNegative: false,
															decimalPrecision: 2,
															disabled: true,
															fieldLabel: langs('Респираторный индекс'),
															listeners: {
																'change':function (field, newValue, oldValue) {
																	var EvnScalesGridRow = win.findById('swERPEW_EvnReanimatCondition_Grid').getSelectionModel().getSelected();  //выбранная строка в гриде событий шкал
																	if (EvnScalesGridRow) {
																		EvnScalesGridRow.data['EvnReanimatCondition_PaOFiO'] = newValue;
																		EvnScalesGridRow.commit();
																	}
																}
															},
															name: 'EvnReanimatCondition_PaOFiO',
															width: 100,
															xtype: 'numberfield'
														}]
													}]
												},
												//ИВЛ
												{
													layout:'column',
													border:false,
													items:[	
														//Дыхание / Аппарат ИВЛ: текстовое поле 
														{							
															layout:'form',
															style:'margin-top: 4px;',
															labelWidth:80,
															border:false,
															items:[													
																{
																	allowBlank: true,
																	fieldLabel: 'ИВЛ: аппарат ',
																	labelSeparator: '',
																	name: 'swERPEW_RC_IVLapparatus',
																	id: 'swERPEW_RC_IVLapparatus',
																	width: 120,
																	style:'margin-top: 2px; margin-left: 4px;',
																//	tabIndex: TABINDEX_MS,
																	value:'',
																	xtype: 'textfield',
																	listeners:{
																		'change':function (field, newValue, oldValue) {
																			var EvnScalesGridRow = win.findById('swERPEW_EvnReanimatCondition_Grid').getSelectionModel().getSelected();  //выбранная строка в гриде Реанимационных мероприятий
																			EvnScalesGridRow.data['EvnReanimatCondition_IVLapparatus'] = newValue;
																			EvnScalesGridRow.commit();
																		}.createDelegate(this)
																	}
																}
															]
														},
														//Дыхание / Параметры ИВЛ: текстовое поле												
														{							
															layout:'form',
															style:'margin-top: 4px;',
															labelWidth:70,
															border:false,
															items:[													
																{
																	allowBlank: true,
																	fieldLabel: 'параметры',
																	labelSeparator: '',
																	name: 'swERPEW_RC_IVLparameter',
																	id: 'swERPEW_RC_IVLparameter',
																	width: 1000,
																	style:'margin-top: 2px; margin-left: 4px;',
																//	tabIndex: TABINDEX_MS,
																	value:'',
																	xtype: 'textfield',
																	listeners:{
																		'change':function (field, newValue, oldValue) {
																			var EvnScalesGridRow = win.findById('swERPEW_EvnReanimatCondition_Grid').getSelectionModel().getSelected();  //выбранная строка в гриде Реанимационных мероприятий
																			EvnScalesGridRow.data['EvnReanimatCondition_IVLparameter'] = newValue;
																			EvnScalesGridRow.commit();
																		}.createDelegate(this)
																	}
																}
															]
														}
													]
												}
											]
										},
										//панель - Сердцу
										{
											xtype: 'fieldset',
											id: 'swERPEW_RC_Heart_Panel',
											autoHeight: true,
											title: langs('Сердце / гемодинамика'),
											collapsible: true,
											layout: 'form',
											style: 'margin: 3px 0 0 0; padding:0; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											listeners: {
												collapse: function(p) {
													win.doLayout();
												},
												expand: function(p) {
													win.doLayout();
												}
											},
											items:[											
												//панель - Тоны сердца, Частота сердечных сокращений 
												{
													id: 'swERPEW_RC_HeartTones',
													layout:'column',
													border:false,
													items:[	
														//Лейбл Тоны сердца
														{									
															layout:'form',
															style:' margin-left: 22px; margin-top: 4px; font-size: 12px ',
															items:[
																new Ext.form.Label({
																	text: 'Тоны сердца'
																})					
															]
														},
														{									
															layout:'form',
															labelWidth:1,
															style: 'margin: 4px 0 4px 0;  margin-left: 4px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
															items:[
																new Ext.form.RadioGroup({
																	id:'swERPEW_RC_HeartTones1',
																	labelSeparator: '',
																	vertical: true,
																	columns: 3,
																	items: [
																		{boxLabel: '---', name: 'HeartTones1', inputValue: 0, width: 60}, 
																		{boxLabel: 'ритмичные', name: 'HeartTones1', inputValue: 1, width: 120}, 
																		{boxLabel: 'аритмичные', name: 'HeartTones1', inputValue: 2, width: 120}
																	],
																	listeners: {
																		'change': function(field, checked) {
																			win.findById('swERPEW_RC_HeartTones').change_handler(field, checked);
																		}
																	}
																})	
															]
														},
														{									
															layout:'form',
															labelWidth:1,
															style: 'margin-top: 4px;  margin-left: 4px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
															items:[
																new Ext.form.RadioGroup({
																	id:'swERPEW_RC_HeartTones2',
																	labelSeparator: '',
																	vertical: true,
																	columns: 3,
																	items: [
																		{boxLabel: '---',  name: 'HeartTones2',  inputValue: 0, width: 60 },
																		{boxLabel: 'ясные',  name: 'HeartTones2',  inputValue: 1, width: 160 },
																		{boxLabel: 'приглушенные', name: 'HeartTones2', inputValue: 2, width: 120},
																		{boxLabel: 'глухие', name: 'HeartTones2', inputValue: 3, width: 160}
																	],
																	listeners: {
																		'change': function(field, checked) {
																			win.findById('swERPEW_RC_HeartTones').change_handler(field, checked);
																		}
																	}
																})	
															]
														},
														//Частота сердечных сокращений
														{							
															layout:'form',
															style:'margin-top: 4px;',
															labelWidth:190,
															border:false,
															items:[	
																new Ext.form.NumberField({
																	value: 0,
																	id: 'swERPEW_RC_Heart_frequency',
																	fieldLabel:'Частота сердечных сокращений',
																	labelSeparator: '',
																	width: 60,
		//															style:'margin-top: 6px; '//,
																	listeners:{
																		'change':function (field, newValue, oldValue) {
																			var EvnScalesGridRow = win.findById('swERPEW_EvnReanimatCondition_Grid').getSelectionModel().getSelected();  //выбранная строка в гриде событий шкал
																			EvnScalesGridRow.data['EvnReanimatCondition_HeartFrequency'] = newValue;
																			EvnScalesGridRow.commit();
																		}.createDelegate(this)
																	}

																})
															]
														}
													],
													change_handler: function(field, checked) {
														if(checked){
															var EvnScalesGridRow = win.findById('swERPEW_EvnReanimatCondition_Grid').getSelectionModel().getSelected();  //выбранная строка в гриде Реанимационных мероприятий
															if (!(EvnScalesGridRow)) return false;
															var HeartTones = EvnScalesGridRow.data['EvnReanimatCondition_HeartTones'];
															var position = parseInt(field.id.substr(field.id.length - 1, 1));
															var value = checked.inputValue;
															HeartTones = HeartTones.split("");
															HeartTones[position - 1] = value;//.toString(); 
															HeartTones = HeartTones.join("");
															EvnScalesGridRow.data['EvnReanimatCondition_HeartTones'] = HeartTones;
															EvnScalesGridRow.commit();
														}
													}
												},
												//панель артериальное давление		
												{									
													layout:'column',
													id: 'swERPEW_RC_Heart_Pressure',
													border:false,
													items:[
														{									
															layout:'form',
															style:' margin-left: 22px; margin-top: 8px; font-size: 12px ',
															items:[
																new Ext.form.Label({
																	text: 'Артериальное давление'
																})					
															]
														},
														{
															xtype: 'label',													
															text: '( ',
															style:'margin-top: 5px;  margin-left: 10px; font-size: 17px; color: DarkBlue;'
														},
														{
															xtype: 'label',													
															text: 'АД сист',
															style:'margin-top: 9px;  10px; margin-right: 5px;'
														},
														new Ext.form.NumberField({
															value: 0,
															id: 'swERPEW_RC_Heart_Pressure_syst',
															width: 40,
															style:'margin-top: 6px;',
															listeners:{
																'change':function (field, newValue, oldValue) {
																	if((newValue == null) || (newValue == ''))
																		field.setValue('0');															
																	this.findById('swERPEW_RC_Heart_Pressure').calculation(true);			
																}.createDelegate(this)
															}
														}),
														{
															xtype: 'label',													
															text: ' + 2 * ',
															style:'margin-top: 5px;  margin-left: 10px; margin-right: 5px; font-size: 17px; color: DarkBlue;'
														},
														{
															xtype: 'label',													
															text: 'АД диаст',
															style:'margin-top: 9px;  margin-right: 5px;'
														},
														new Ext.form.NumberField({
															value: 0,
															id: 'swERPEW_RC_Heart_Pressure_diast',
															width: 40,
															style:'margin-top: 6px;',
															listeners:{
																'change':function (field, newValue, oldValue) {
																	if((newValue == null) || (newValue == ''))
																		field.setValue('0');															
																	this.findById('swERPEW_RC_Heart_Pressure').calculation(true);			
																}.createDelegate(this)
															}													
														}),
														{
															xtype: 'label',													
															text: ') / 3 =',
															style:'margin-top: 5px;  margin-left: 2px; margin-right: 5px; font-size: 17px; color: DarkBlue;'
														},
														{									
															layout:'form',
															style:'margin-top: 8px; color: blue; font-weight: bold; font-size: 12px; border-top: 1px solid #99bbe8; border-left: 1px solid #99bbe8 ; border-bottom: 1px solid #ffffff;border-right: 1px solid #ffffff;',
															width: 50,
															height: 19,
															items:[
																new Ext.form.Label({
																	id: 'swERPEW_RC_Heart_Pressure_Val',
																	xtype: 'label',
																	text: '0'
																})					
															]
														},
														{
															xtype: 'label',													
															text: 'мм.рт.ст.',
															style:'margin-top: 9px; margin-left: 3px;'
														}
													],
													calculation: function(from_interf) { 
														//console.log('BOB_win.findById(swERPEW_RC_Heart_Pressure)=',win.findById('swERPEW_RC_Heart_Pressure'));
														if ((parseInt(win.findById('swERPEW_RC_Heart_Pressure_syst').getValue()) > 0) && (parseInt(win.findById('swERPEW_RC_Heart_Pressure_diast').getValue()) > 0)) {
															var sum = (parseInt(win.findById('swERPEW_RC_Heart_Pressure_syst').getValue()) + 2 * parseInt(win.findById('swERPEW_RC_Heart_Pressure_diast').getValue())) / 3;
															sum = Math.round(sum * 100) / 100;
															win.findById('swERPEW_RC_Heart_Pressure_Val').setText(sum);
														}
														else
															win.findById('swERPEW_RC_Heart_Pressure_Val').setText(0);
														
														if (from_interf) {
															var EvnScalesGridRow = win.findById('swERPEW_EvnReanimatCondition_Grid').getSelectionModel().getSelected();  //выбранная строка в гриде Реанимационных мероприятий
															EvnScalesGridRow.data['EvnReanimatCondition_Pressure'] = win.findById('swERPEW_RC_Heart_Pressure_syst').getValue() + '/' + win.findById('swERPEW_RC_Heart_Pressure_diast').getValue();
															EvnScalesGridRow.commit();
															
															win.findById('swERPEW_RC_VBD_Panel').calculation(win.findById('swERPEW_RC_VBD'), win.findById('swERPEW_RC_VBD').getValue());
														}
													}
												},
												//Гемодинамика
												{
													layout:'column',
													id: 'swERPEW_RC_Hemodynamics_Panel',
													border:false,
													items:[	
														//Гемодинамика
														{									
															layout:'form',
															style:' margin-left: 22px; margin-top: 4px; font-size: 12px ',
															items:[
																new Ext.form.Label({
																	text: 'Гемодинамика'
																})					
															]
														},
														{									
															layout:'form',
															labelWidth:1,
															style: 'margin: 4px 0 4px 0;  margin-left: 4px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
															items:[
																new Ext.form.RadioGroup({
																	id:'swERPEW_RC_IsHemodStab',
																	labelSeparator: '',
																	vertical: true,
																	columns: 3,
																	items: [
																		{boxLabel: '---', name: 'IsHemodStab', inputValue: 0, width: 60},
																		{boxLabel: 'стабильная', name: 'IsHemodStab', inputValue: 1, width: 120},
																		{boxLabel: 'нестабильная', name: 'IsHemodStab', inputValue: 2, width: 120}
																	],
																	listeners: {
																		'change': function(field, checked) {
																			var EvnScalesGridRow = win.findById('swERPEW_EvnReanimatCondition_Grid').getSelectionModel().getSelected();  //выбранная строка в гриде Реанимационных мероприятий
																			EvnScalesGridRow.data['EvnReanimatCondition_IsHemodStab'] = checked.inputValue > 0 ? checked.inputValue : null ;
																			EvnScalesGridRow.commit();
																		}
																	}
																})	
															]
														},
														//combo - Гемодинамика
														{							
															layout:'form',
															style:'margin-top: 4px;',
															labelWidth:5,
															border:false,
															items:[	
																//BOB - 21.03.2019
																{
																	id: 'swERPEW_RC_Hemodynamics',
																	hiddenName: 'RC_Hemodynamics',									
																	xtype: 'swextemporalcomptypecombo',
																	fieldLabel: '',
																	labelSeparator: '',
																	allowBlank: true,
																	comboSubject: 'HemodynamicsType',
																	width: 270,
																	lastQuery: '',
																	listeners: {
																		'select': function(combo, record, index) {
																			var EvnScalesGridRow = win.findById('swERPEW_EvnReanimatCondition_Grid').getSelectionModel().getSelected();  //выбранная строка в гриде регулярного наблюдения состояния    
																			EvnScalesGridRow.data['HemodynamicsType_id'] = record.data.HemodynamicsType_id;
																			//BOB - 09.09.2019 если «поддерживается вазопрессорами»
																			if(record.data.HemodynamicsType_id == 3){
																				//если панель шкал ещё не открывалась И в store грида мероприятий пусто (туда могут попасть записи без открытия панели  -  вот прямо здесь это и делается)
																				if(!win.findById('swERPEW_ReanimatAction_Panel').isLoaded && win.findById('swERPEW_ReanimatAction_Grid').store.data.items.length == 0){
																					//загрузка грида мероприятий
																					win.findById('swERPEW_ReanimatAction_Grid').getStore().load({
																						params:{
																							EvnReanimatAction_pid: win.EvnReanimatPeriod_id
																						},
																						callback: function(records, options, success) {
																							combo.vazopressors_params(EvnScalesGridRow);
																						}
																					});
																				}
																				else {
																					combo.vazopressors_params(EvnScalesGridRow);
																				}
																			}
																			//BOB - 09.09.2019
																			EvnScalesGridRow.commit(EvnScalesGridRow);
																		}
																	},
																	//BOB - 09.09.2019
																	vazopressors_params: function(EvnScalesGridRow) {
																		var ReanimatActionStore = win.findById('swERPEW_ReanimatAction_Grid').getStore().data.items;
																		var swERPEW_RC_HemodynamicsTxt_value = win.findById('swERPEW_RC_HemodynamicsTxt').getValue();
																		for(var i in ReanimatActionStore){
																			if ((ReanimatActionStore[i].data) && (ReanimatActionStore[i].data.EvnReanimatAction_id != "New_GUID_Id")){
																				if(ReanimatActionStore[i].data.ReanimatActionType_SysNick == "vazopressors"){
																					swERPEW_RC_HemodynamicsTxt_value += (!Ext.isEmpty(swERPEW_RC_HemodynamicsTxt_value) ? ', ' : '') +  ReanimatActionStore[i].data['EvnReanimatAction_Medicoment'];
																					if((ReanimatActionStore[i].data['EvnReanimatAction_DrugDose']) && (!Ext.isEmpty(ReanimatActionStore[i].data['EvnReanimatAction_DrugDose'])) && (ReanimatActionStore[i].data['EvnReanimatAction_DrugDose'] != 0)){
																						swERPEW_RC_HemodynamicsTxt_value += ' ' + ReanimatActionStore[i].data['EvnReanimatAction_DrugDose'] + ' ' +  ReanimatActionStore[i].data['EvnReanimatAction_DrugUnit'];
																					}
																					break;
																				}
																			}
																		}
																		win.findById('swERPEW_RC_HemodynamicsTxt').setValue(swERPEW_RC_HemodynamicsTxt_value);
																		EvnScalesGridRow.data['EvnReanimatCondition_HemodynamicsTxt'] = win.findById('swERPEW_RC_HemodynamicsTxt').getValue();
																	}
																}																																
															]
														},																												
														//Гемодинамика - параметры 
														{							
															layout:'form',
															id: 'swERPEW_RC_HemodynamicsTxtPanel',
															style:'margin-top: 4px;',
															labelWidth:90,
															border:false,
															items:[													
																{
																	allowBlank: true,
																	fieldLabel: 'Параметры',
																	labelSeparator: '',
																	name: 'swERPEW_RC_HemodynamicsTxt',
																	id: 'swERPEW_RC_HemodynamicsTxt',
																	width: 496,
																	style:'margin-top: 2px; margin-left: 4px;',
																//	tabIndex: TABINDEX_MS,
																	value:'',
																	xtype: 'textfield',
																	listeners:{
																		'change':function (field, newValue, oldValue) {
																			var EvnScalesGridRow = win.findById('swERPEW_EvnReanimatCondition_Grid').getSelectionModel().getSelected();  //выбранная строка в гриде Реанимационных мероприятий
																			EvnScalesGridRow.data['EvnReanimatCondition_HemodynamicsTxt'] = newValue;
																			EvnScalesGridRow.commit();
																		}.createDelegate(this)
																	}
																}
															]
														}												
													]	
												}
											]
										},										
										//Панель ЯЗЫК //BOB - 24.01.2019
										{
											id: 'swERPEW_RC_Tongue_Panel',
											layout:'column',
											xtype: 'fieldset',
											autoHeight: true,
											title: langs('Язык'),
											collapsible: true,
											style: 'margin: 3px 0 0 0; padding: 0 0 3px 0; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											listeners: {
												collapse: function(p) {
													win.doLayout();
												},
												expand: function(p) {
													win.doLayout();
												}
											},
											items:[	
												{									
													layout:'form',
													labelWidth:1,
													style: 'margin-top: 4px;  margin-left: 4px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
													items:[
														new Ext.form.RadioGroup({
															id:'swERPEW_RC_Tongue1',
															labelSeparator: '',
															vertical: true,
															columns: 3,
															items: [
																{boxLabel: '---', name: 'Tongue1', inputValue: 0, width: 60},
																{boxLabel: 'влажный', name: 'Tongue1', inputValue: 1, width: 100},
																{boxLabel: 'сухой', name: 'Tongue1', inputValue: 2, width: 100}
															],
															listeners: {
																'change': function(field, checked) {
																	win.findById('swERPEW_RC_Tongue_Panel').change_handler(field, checked);
																}
															}
														})	
													]
												},
												{									
													layout:'form',
													labelWidth:1,
													style: 'margin-top: 4px;  margin-left: 4px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
													items:[
														new Ext.form.RadioGroup({
															id:'swERPEW_RC_Tongue2',
															labelSeparator: '',
															vertical: true,
															columns: 4,
															items: [
																{boxLabel: '---',  name: 'Tongue2',  inputValue: 0, width: 60 },
																{boxLabel: 'не обложен',  name: 'Tongue2',  inputValue: 1, width: 120 },
																{boxLabel: 'обложен белым налётом', name: 'Tongue2', inputValue: 2, width: 200},
																{boxLabel: 'обложен жёлтым налётом', name: 'Tongue2', inputValue: 3, width: 200}
															],
															listeners: {
																'change': function(field, checked) {
																	win.findById('swERPEW_RC_Tongue_Panel').change_handler(field, checked);
																}
															}
														})	
													]
												}
											],
											change_handler: function(field, checked) {
												if(checked){
													var EvnScalesGridRow = win.findById('swERPEW_EvnReanimatCondition_Grid').getSelectionModel().getSelected();  //выбранная строка в гриде Реанимационных мероприятий
													if (!(EvnScalesGridRow)) return false;
													var Tongue = EvnScalesGridRow.data['EvnReanimatCondition_Tongue'];
													var position = parseInt(field.id.substr(field.id.length - 1, 1));
													var value = checked.inputValue;
													Tongue = Tongue.split("");
													Tongue[position - 1] = value;
													Tongue = Tongue.join("");
													EvnScalesGridRow.data['EvnReanimatCondition_Tongue'] = Tongue;
													EvnScalesGridRow.commit();
												}
											}
										},
										//Панель Состояние живота //BOB - 24.01.2019
										{
											id: 'swERPEW_RC_Paunch_Panel',
											layout:'form',
											xtype: 'fieldset',
											autoHeight: true,
											title: langs('Живот'),
											collapsible: true,
											style: 'margin: 3px 0 0 0; padding: 0 0 3px 0; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											listeners: {
												collapse: function(p) {
													win.doLayout();
												},
												expand: function(p) {
													win.doLayout();
												}
											},
											items:[	
												//радиокнопки Состояние живота
												{
													id: 'swERPEW_RC_Paunch',
													layout:'column',
													border:false,
													items:[	
														{									
															layout:'form',
															labelWidth:1,
															style: 'margin-top: 4px;  margin-left: 4px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
															items:[
																new Ext.form.RadioGroup({
																	id:'swERPEW_RC_Paunch1',
																	labelSeparator: '',
																	vertical: true,
																	columns: 2,
																	items: [
																		{boxLabel: '---', name: 'Paunch1', inputValue: 0, width: 120},
																		{boxLabel: 'мягкий', name: 'Paunch1', inputValue: 1, width: 120},
																		{boxLabel: 'умеренно напряжён', name: 'Paunch1', inputValue: 2, width: 160},
																		{boxLabel: 'напряжён', name: 'Paunch1', inputValue: 3, width: 130}
																	],
																	listeners: {
																		'change': function(field, checked) {
																			win.findById('swERPEW_RC_Paunch').change_handler(field, checked);
																		}
																	}
																})	
															]
														},
														{									
															layout:'form',
															labelWidth:1,
															style: 'margin-top: 4px;  margin-left: 4px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
															items:[
																new Ext.form.RadioGroup({
																	id:'swERPEW_RC_Paunch2',
																	labelSeparator: '',
																	vertical: true,
																	columns: 2,
																	items: [
																		{boxLabel: '---',  name: 'Paunch2',  inputValue: 0, width: 60 },
																		{boxLabel: 'не вздут',  name: 'Paunch2',  inputValue: 1, width: 120 },
																		{boxLabel: 'умеренно вздут', name: 'Paunch2', inputValue: 2, width: 160},
																		{boxLabel: 'вздут', name: 'Paunch2', inputValue: 3, width: 120}
																	],
																	listeners: {
																		'change': function(field, checked) {
																			win.findById('swERPEW_RC_Paunch').change_handler(field, checked);
																		}
																	}
																})	
															]
														},
														//Лейбл При пальпации
														{									
															layout:'form',
															style:' margin-left: 22px; margin-top: 4px; font-size: 12px ',
															items:[
																new Ext.form.Label({
																	text: 'При пальпации'
																})					
															]
														},
														{									
															layout:'form',
															labelWidth:1,
															style: 'margin-top: 4px;  margin-left: 4px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
															items:[
																new Ext.form.RadioGroup({
																	id:'swERPEW_RC_Paunch3',
																	labelSeparator: '',
																	vertical: true,
																	columns: 2,
																	items: [
																		{boxLabel: '---', name: 'Paunch3', inputValue: 0, width: 60},
																		{boxLabel: 'безболезненный', name: 'Paunch3', inputValue: 1, width: 120},
																		{boxLabel: 'умеренно болезненный', name: 'Paunch3', inputValue: 2, width: 180},
																		{boxLabel: 'болезненный', name: 'Paunch3', inputValue: 3, width: 120}
																	],
																	listeners: {
																		'change': function(field, checked) {
																			win.findById('swERPEW_RC_Paunch').change_handler(field, checked);
																		}
																	}
																})	
															]
														}												
													],
													change_handler: function(field, checked) {
														if(checked){
															var EvnScalesGridRow = win.findById('swERPEW_EvnReanimatCondition_Grid').getSelectionModel().getSelected();  //выбранная строка в гриде Реанимационных мероприятий
															if (!(EvnScalesGridRow)) return false;
															var Paunch = EvnScalesGridRow.data['EvnReanimatCondition_Paunch'];
															var position = parseInt(field.id.substr(field.id.length - 1, 1));
															var value = checked.inputValue;
															Paunch = Paunch.split("");
															Paunch[position - 1] = value;
															Paunch = Paunch.join("");
															EvnScalesGridRow.data['EvnReanimatCondition_Paunch'] = Paunch;
															EvnScalesGridRow.commit();
														}
													}													
												},
												//текстовое поле												
												{							
													layout:'form',
													style:'margin-top: 4px;',
													labelWidth:5,
													border:false,
													items:[													
														{
															allowBlank: true,
															fieldLabel: '',
															labelSeparator: '',
															name: 'swERPEW_RC_PaunchTxt',
															id: 'swERPEW_RC_PaunchTxt',
															width: 1270,
															style:'margin-top: 2px; margin-left: 4px;',
														//	tabIndex: TABINDEX_MS,
															value:'',
															xtype: 'textfield',
															listeners:{
																'change':function (field, newValue, oldValue) {
																	var EvnScalesGridRow = win.findById('swERPEW_EvnReanimatCondition_Grid').getSelectionModel().getSelected();  //выбранная строка в гриде Реанимационных мероприятий
																	EvnScalesGridRow.data['EvnReanimatCondition_PaunchTxt'] = newValue;
																	EvnScalesGridRow.commit();
																}.createDelegate(this)
															}
														}
													]
												},
												//combo - перистальтика 
												{							
													layout:'form',
													style:'margin-top: 2px;',
													labelWidth:110,
													border:false,
													items:[	
														//BOB - 21.03.2019
														{
															id: 'swERPEW_RC_Peristalsis',
															hiddenName: 'RC_Peristalsis',									
															xtype: 'swextemporalcomptypecombo',
															fieldLabel: langs('Перистальтика'),
															labelSeparator: '',
															allowBlank: true,
															comboSubject: 'PeristalsisType',
															width: 250,
															lastQuery: '',
															listeners: {
																'select': function(combo, record, index) {
																	var EvnScalesGridRow = win.findById('swERPEW_EvnReanimatCondition_Grid').getSelectionModel().getSelected();  //выбранная строка в гриде регулярного наблюдения состояния    
																	EvnScalesGridRow.data['PeristalsisType_id'] = record.data.PeristalsisType_id;
																	EvnScalesGridRow.commit();
																}
															}
														}																														
													]
												},
												//панель Внутрибрюшное давление 		
												{									
													layout:'column',
													id: 'swERPEW_RC_VBD_Panel',
													border:false,
													items:[
														{									
															layout:'form',
															style:' margin-left: 22px; margin-top: 3px; font-size: 12px ',
															items:[
																new Ext.form.Label({
																	text: 'ВБД'
																})					
															]
														},
														new Ext.form.NumberField({
															value: 0,
															id: 'swERPEW_RC_VBD',
															width: 60,
															style:'margin-left: 4px; margin-top: 0px;',
															listeners:{
																'change':function (field, newValue, oldValue) {
																	win.findById('swERPEW_RC_VBD_Panel').calculation(field, newValue);
																	var EvnScalesGridRow = win.findById('swERPEW_EvnReanimatCondition_Grid').getSelectionModel().getSelected();  //выбранная строка в гриде регулярного наблюдения состояния    
																	EvnScalesGridRow.data['EvnReanimatCondition_VBD'] = field.getValue();
																	EvnScalesGridRow.commit();
																}.createDelegate(this)
															}
														}),
														{
															xtype: 'label',													
															text: ' мм рт. ст.',
															style:'margin-top: 3px;  margin-left: 10px; margin-right: 5px;  font-size: 12px '
														},
														{									
															layout:'form',
															style:'margin-top: 2px; font-size: 12px; border-top: 1px solid #99bbe8; border-left: 1px solid #99bbe8 ; border-bottom: 1px solid #ffffff;border-right: 1px solid #ffffff;',
															width: 500,
															height: 19,
															items:[
																new Ext.form.Label({
																	id: 'swERPEW_RC_IAG',
																	xtype: 'label',
																	text: ''
																})					
															]
														},
														{
															xtype: 'label',													
															text: ' АПД',
															style:'margin-top: 3px;  margin-left: 10px; margin-right: 5px; font-size: 12px; '
														},
														{									
															layout:'form',
															style:'margin-top: 2px; font-size: 12px; border-top: 1px solid #99bbe8; border-left: 1px solid #99bbe8 ; border-bottom: 1px solid #ffffff;border-right: 1px solid #ffffff;',
															width: 50,
															height: 19,
															items:[
																new Ext.form.Label({
																	id: 'swERPEW_RC_APD',
																	xtype: 'label',
																	text: '0'
																})					
															]
														},
														{
															xtype: 'label',													
															text: 'мм.рт.ст.',
															style:'margin-top: 3px; margin-left: 3px; font-size: 12px; '
														}
													],
													calculation: function(field, newValue) { 
														if((newValue == null) || (newValue == '') || (newValue == '')){
															field.setValue('0');
															win.findById('swERPEW_RC_IAG').setText('');
															win.findById('swERPEW_RC_APD').setText('0');
														}
														else {
															var VBD = parseInt(newValue);
															if (VBD < 12)win.findById('swERPEW_RC_IAG').setText('');
															else if (VBD < 16) win.findById('swERPEW_RC_IAG').setText('I степень ИАГ');
															else if (VBD < 21) win.findById('swERPEW_RC_IAG').setText('II степень ИАГ');
															else if (VBD < 26) win.findById('swERPEW_RC_IAG').setText('III степень ИАГ');
															else win.findById('swERPEW_RC_IAG').setText('IV степень ИАГ (СИАГ)');

															var SAD = parseFloat(win.findById('swERPEW_RC_Heart_Pressure_Val').text);
															if ((!isNaN(SAD)) && SAD > 0) win.findById('swERPEW_RC_APD').setText(Math.round((SAD - VBD)*100)/100);
															else win.findById('swERPEW_RC_APD').setText(0);
														}
													}
												}
											]
										},
										//Панель дефекация / диурез //BOB - 24.01.2019
										{
											id: 'swERPEW_RC_DefecationDiuresis_Panel',
											layout:'form',
											xtype: 'fieldset',
											autoHeight: true,
											title: langs('Дефекация / диурез'),
											collapsible: true,
											style: 'margin: 3px 0 0 0; padding: 0 0 3px 0; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											listeners: {
												collapse: function(p) {
													win.doLayout();
												},
												expand: function(p) {
													win.doLayout();
												}
											},
											items:[	
												//панель - Дефекация
												{
													//id: 'swERPEW_RC_Defecation_Panel',
													layout:'column',
													border:false,
													items:[	
														//Лейбл Стул
														{									
															layout:'form',
															style:' margin-left: 22px; margin-top: 4px; font-size: 12px ',
															items:[
																new Ext.form.Label({
																	text: 'Стул'
																})					
															]
														},
														{									
															layout:'form',
															labelWidth:1,
															style: 'margin-top: 4px;  margin-left: 4px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
															items:[
																new Ext.form.RadioGroup({
																	id:'swERPEW_RC_Defecation',
																	labelSeparator: '',
																	vertical: true,
																	columns: 2,
																	items: [
																		{boxLabel: '---',  name: 'Defecation2',  inputValue: 0, width: 60 },
																		{boxLabel: 'самостоятельный',  name: 'Defecation2',  inputValue: 1, width: 160 },
																		{boxLabel: 'на фоне стимуляции', name: 'Defecation2', inputValue: 2, width: 120},
																		{boxLabel: 'жидкий', name: 'Defecation2', inputValue: 3, width: 160}
																	],
																	listeners: {
																		'change': function(field, checked) {
																			var EvnScalesGridRow = win.findById('swERPEW_EvnReanimatCondition_Grid').getSelectionModel().getSelected();  //выбранная строка в гриде Реанимационных мероприятий
																			EvnScalesGridRow.data['EvnReanimatCondition_Defecation'] = checked.inputValue;
																			EvnScalesGridRow.commit();
																		}
																	}
																})	
															]
														},
														//Дефекация - вариант пользователя
														{							
															layout:'form',
															style:'margin-top: 4px;',
															labelWidth:10,
															border:false,
															items:[	
																{
																	allowBlank: true,
																	fieldLabel: '',
																	labelSeparator: '',
																	name: 'swERPEW_RC_DefecationTxt',
																	id: 'swERPEW_RC_DefecationTxt',
																	width: 890,
																	style:'margin-top: 2px; margin-left: 4px;',
																//	tabIndex: TABINDEX_MS,
																	value:'',
																	xtype: 'textfield',
																	listeners:{
																		'change':function (field, newValue, oldValue) {
																			var EvnScalesGridRow = win.findById('swERPEW_EvnReanimatCondition_Grid').getSelectionModel().getSelected();  //выбранная строка в гриде Реанимационных мероприятий
																			EvnScalesGridRow.data['EvnReanimatCondition_DefecationTxt'] = newValue;
																			EvnScalesGridRow.commit();
																		}.createDelegate(this)
																	}
																}
															]
														}
													]
												},
												//панель - Диурез
												{
													id: 'swERPEW_RC_Diuresis',
													layout:'column',
													border:false,
													items:[	
														//Лейбл Диурез 
														{									
															layout:'form',
															style:' margin-left: 8px; margin-top: 4px; font-size: 12px ',
															items:[
																new Ext.form.Label({
																	text: 'Диурез'
																})					
															]
														},
														{									
															layout:'form',
															labelWidth:1,
															style: 'margin-top: 4px;  margin-left: 4px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
															items:[
																new Ext.form.RadioGroup({
																	id:'swERPEW_RC_Diuresis1',
																	labelSeparator: '',
																	vertical: true,
																	columns: 3,
																	items: [
																		{boxLabel: '---', name: 'Diuresis1', inputValue: 0, width: 120},
																		{boxLabel: 'адекватный', name: 'Diuresis1', inputValue: 1, width: 120},
																		{boxLabel: 'снижен', name: 'Diuresis1', inputValue: 2, width: 120},
																		{boxLabel: 'олигурия', name: 'Diuresis1', inputValue: 3, width: 120},
																		{boxLabel: 'анурия', name: 'Diuresis1', inputValue: 4, width: 120},
																		{boxLabel: 'полиурия', name: 'Diuresis1', inputValue: 5, width: 120}
																	],
																	listeners: {
																		'change': function(field, checked) {
																			win.findById('swERPEW_RC_Diuresis').change_handler(field, checked);
																		}
																	}
																})	
															]
														},
														{									
															layout:'form',
															labelWidth:1,
															style: 'margin-top: 4px;  margin-left: 4px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
															items:[
																new Ext.form.RadioGroup({
																	id:'swERPEW_RC_Diuresis2',
																	labelSeparator: '',
																	vertical: true,
																	columns: 2,
																	items: [
																		{boxLabel: '---',  name: 'Diuresis2',  inputValue: 0, width: 120 },
																		{boxLabel: 'самостоятельно',  name: 'Diuresis2',  inputValue: 1, width: 120 },
																		{boxLabel: 'по уретральному катетеру', name: 'Diuresis2', inputValue: 2, width: 200}
																	],
																	listeners: {
																		'change': function(field, checked) {
																			win.findById('swERPEW_RC_Diuresis').change_handler(field, checked);
																		}
																	}
																})	
															]
														},
														{									
															layout:'form',
															labelWidth:1,
															style: 'margin-top: 4px;  margin-left: 4px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
															items:[
																new Ext.form.RadioGroup({
																	id:'swERPEW_RC_Diuresis3',
																	labelSeparator: '',
																	vertical: true,
																	columns: 2,
																	items: [
																		{boxLabel: '---',  name: 'Diuresis3',  inputValue: 0, width: 120 },
																		{boxLabel: 'на фоне стимуляции',  name: 'Diuresis3',  inputValue: 1, width: 160 },
																		{boxLabel: 'без стимуляции', name: 'Diuresis3', inputValue: 2, width: 120} 
																	],
																	listeners: {
																		'change': function(field, checked) {
																			win.findById('swERPEW_RC_Diuresis').change_handler(field, checked);
																		}
																	}
																})	
															]
														}
													],
													change_handler: function(field, checked) {
														if(checked){
															var EvnScalesGridRow = win.findById('swERPEW_EvnReanimatCondition_Grid').getSelectionModel().getSelected();  //выбранная строка в гриде Реанимационных мероприятий
															if (!(EvnScalesGridRow)) return false;
															var Diuresis = EvnScalesGridRow.data['EvnReanimatCondition_Diuresis'];
															//console.log('BOB_Diuresis0=',Diuresis);  //BOB - 22.09.2017
															var position = parseInt(field.id.substr(field.id.length - 1, 1));
															var value = checked.inputValue;
															Diuresis = Diuresis.split("");
															Diuresis[position - 1] = value;//.toString(); 
															Diuresis = Diuresis.join("");

															EvnScalesGridRow.data['EvnReanimatCondition_Diuresis'] = Diuresis;
															EvnScalesGridRow.commit();
														}
													}
												},
												//панель - Моча 
												{
													layout:'column',
													border:false,
													items:[	
														//Объём диуреза   //BOB - 28.08.2018
														{							
															layout:'form',
															style:'margin-top: 2px;',
															labelWidth:100,
															border:false,
															items:[	
																new Ext.form.NumberField({
																	value: 0,
																	id: 'swERPEW_RC_DiuresisVolume',
																	fieldLabel:'Объём диуреза',
																	labelSeparator: '',
																	enableKeyEvents: true,
																	width: 60,
																	listeners:{
																		'keyup':function (obj, e) {
																			var EvnScalesGridRow = win.findById('swERPEW_EvnReanimatCondition_Grid').getSelectionModel().getSelected();  //выбранная строка в гриде регулярного наблюдения состояния 
																			EvnScalesGridRow.data['EvnReanimatCondition_DiuresisVolume'] = obj.getValue();
																			EvnScalesGridRow.commit();
																		}.createDelegate(this)
																	}
																})					
															]
														},
														//Лейбл мл
														{									
															layout:'form',
															style:'margin-left: 2px; margin-top: 4px; ',
															width: 15,
															border:false,
															//bodyStyle:'background-color: transparent',
															items:[
																new Ext.form.Label({
																	id: 'swERPEW_RC_DiuresisVolume_Unit',
																	text: 'мл'
																})					
															]
														} ,   //BOB - 28.08.2018
														//combo - Моча 
														{							
															layout:'form',
															style:'margin-top: 2px;',
															labelWidth:50,
															border:false,
															items:[	
																//BOB - 21.03.2019
																{
																	id: 'swERPEW_RC_Urine',
																	hiddenName: 'RC_Urine',									
																	xtype: 'swextemporalcomptypecombo',
																	fieldLabel: langs('Моча'),
																	labelSeparator: '',
																	allowBlank: true,
																	comboSubject: 'UrineType',
																	width: 240,
																	lastQuery: '',
																	listeners: {
																		'select': function(combo, record, index) {
																			var EvnScalesGridRow = win.findById('swERPEW_EvnReanimatCondition_Grid').getSelectionModel().getSelected();  //выбранная строка в гриде регулярного наблюдения состояния    
																			EvnScalesGridRow.data['UrineType_id'] = record.data.UrineType_id;
																			EvnScalesGridRow.commit();
																			//если пользовательский вариант
																			if (record.data.UrineType_id == 4)
																				win.findById('swERPEW_RC_UrineTxt').setVisible( true );
																			else {
																				win.findById('swERPEW_RC_UrineTxt').setVisible( false );
																				win.findById('swERPEW_RC_UrineTxt').setValue('');
																				EvnScalesGridRow.data['EvnReanimatCondition_UrineTxt'] = '';
																				EvnScalesGridRow.commit();
																			}
																		}
																	}
																}																																														
															]
														},
														//Моча  - варианта пользователя
														{
															allowBlank: true,
														//	fieldLabel: 'зззззз',
															name: 'swERPEW_RC_UrineTxt',
															id: 'swERPEW_RC_UrineTxt',
															width: 815,
															style:'margin-top: 2px; margin-left: 4px;',
														//	tabIndex: TABINDEX_MS,
															value:'',
															xtype: 'textfield',
															hidden: true,
															listeners:{
																'change':function (field, newValue, oldValue) {
																	var EvnScalesGridRow = win.findById('swERPEW_EvnReanimatCondition_Grid').getSelectionModel().getSelected();  //выбранная строка в гриде Реанимационных мероприятий
																	EvnScalesGridRow.data['EvnReanimatCondition_UrineTxt'] = newValue;
																	EvnScalesGridRow.commit();
																}.createDelegate(this)
															}
														}
													]
												}
											]
										},
										//панель - Status localis 
										{
											id: 'swERPEW_RC_Status_localis_Panel',
											labelWidth: 5,
											xtype: 'fieldset',
											autoHeight: true,
											title: langs('Status localis'),
											collapsible: true,
											//collapsed: true,
											layout: 'form',
											style: 'margin: 3px 0 0 0; padding:0; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											listeners: {
												collapse: function(p) {
													win.doLayout();
												},
												expand: function(p) {
													win.doLayout();
												}
											},
											items:[	
												new Ext.form.TextArea({
													fieldLabel: '',
													labelSeparator: '',
													id: 'swERPEW_RC_Status_localis',
													name: 'RC_Status_localis',
													height: 154,
													anchor: '99%',
													listeners:{
														'change':function (field, newValue, oldValue) {
															var EvnScalesGridRow = win.findById('swERPEW_EvnReanimatCondition_Grid').getSelectionModel().getSelected();  //выбранная строка в гриде Реанимационных мероприятий
															EvnScalesGridRow.data['EvnReanimatCondition_StatusLocalis'] = newValue;
															EvnScalesGridRow.commit();
														}.createDelegate(this)
													}
												})												
											]
										},
										//Панель ПОДВИЖНОСТЬ //BOB - 24.01.2019
										{
											id: 'swERPEW_RC_LimbImmobility_Panel',
											layout:'form',
											xtype: 'fieldset',
											autoHeight: true,
											title: langs('Подвижность'),
											collapsible: true,
											style: 'margin: 3px 0 0 0; padding: 0 0 3px 0; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											listeners: {
												collapse: function(p) {
													win.doLayout();
												},
												expand: function(p) {
													win.doLayout();
												}
											},
											items:[	
												//панель - Движения в конечностях 
												{
													id:'swERPEW_RC_MonopLoc_Panel',
													layout:'column',
													border:false,
													items:[	
														//combo - Движения в конечностях 
														{							
															layout:'form',
															style:'margin-top: 2px;',
															labelWidth:150,
															border:false,
															items:[	
																//BOB - 21.03.2019
																{
																	id: 'swERPEW_RC_LimbImmobility',
																	hiddenName: 'RC_LimbImmobility',									
																	xtype: 'swextemporalcomptypecombo',
																	fieldLabel: langs('Движения в конечностях'),
																	labelSeparator: '',
																	allowBlank: true,
																	comboSubject: 'LimbImmobilityType',
																	width: 240,
																	lastQuery: '',
																	listeners: {
																		'select': function(combo, record, index) {
																			var EvnScalesGridRow = win.findById('swERPEW_EvnReanimatCondition_Grid').getSelectionModel().getSelected();  //выбранная строка в гриде регулярного наблюдения состояния    
																			EvnScalesGridRow.data['LimbImmobilityType_id'] = record.data.LimbImmobilityType_id;
																			EvnScalesGridRow.commit();
																		}
																	}
																}																																																														
															]
														},
														{									
															layout:'form',
															labelWidth:1,
															style: 'margin-top: 4px;  margin-left: 4px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
															items:[
																new Ext.form.RadioGroup({
																	id:'swERPEW_RC_MonopLoc1',
																	labelSeparator: '',
																	vertical: true,
																	columns: 3,
																	items: [
																		{boxLabel: '---', name: 'MonopLoc1', inputValue: 0, width: 60},
																		{boxLabel: 'правая', name: 'MonopLoc1', inputValue: 1, width: 100},
																		{boxLabel: 'левая', name: 'MonopLoc1', inputValue: 2, width: 100}
																	],
																	listeners: {
																		'change': function(field, checked) {
																			win.findById('swERPEW_RC_MonopLoc_Panel').change_handler(field, checked);
																		}
																	}
																})	
															]
														},
														{									
															layout:'form',
															labelWidth:1,
															style: 'margin-top: 4px;  margin-left: 4px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
															items:[
																new Ext.form.RadioGroup({
																	id:'swERPEW_RC_MonopLoc2',
																	labelSeparator: '',
																	vertical: true,
																	columns: 4,
																	items: [
																		{boxLabel: '---',  name: 'MonopLoc2',  inputValue: 0, width: 60 },
																		{boxLabel: 'рука',  name: 'MonopLoc2',  inputValue: 1, width: 100 },
																		{boxLabel: 'нога', name: 'MonopLoc2', inputValue: 2, width: 100}
																	],
																	listeners: {
																		'change': function(field, checked) {
																			win.findById('swERPEW_RC_MonopLoc_Panel').change_handler(field, checked);
																		}
																	}
																})	
															]
														}
													],
													change_handler: function(field, checked) {
														if(checked){
															var EvnScalesGridRow = win.findById('swERPEW_EvnReanimatCondition_Grid').getSelectionModel().getSelected();  //выбранная строка в гриде Реанимационных мероприятий
															if (!(EvnScalesGridRow)) return false;
															var MonopLoc = EvnScalesGridRow.data['EvnReanimatCondition_MonopLoc'];
															var position = parseInt(field.id.substr(field.id.length - 1, 1));
															var value = checked.inputValue;
															MonopLoc = MonopLoc.split("");
															MonopLoc[position - 1] = value;
															MonopLoc = MonopLoc.join("");
															EvnScalesGridRow.data['EvnReanimatCondition_MonopLoc'] = MonopLoc;
															EvnScalesGridRow.commit();
														}
													}
												},
												//панель - Сила мышц  
												{							
													layout:'form',
													style:'margin-top: 4px;',
													labelWidth:150,
													border:false,
													items:[	
														{
															xtype: 'textfield',
															id: 'swERPEW_RC_mrc',
															fieldLabel:'Сила мышц по шкале MRC',
															labelSeparator: '',
															width: 1130,
															disabled: true
															//style:'margin-top: 6px; '//,

														}
													]
												}
											]
										},

										//панель - Анальгезия
										{
											id: 'swERPEW_RC_Analgesia_Panel',
											layout:'column',
											xtype: 'fieldset',
											autoHeight: true,
											title: langs('Анальгезия'),
											collapsible: true,
											style: 'margin: 3px 0 0 0; padding: 0 0 3px 0; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											listeners: {
												collapse: function(p) {
													win.doLayout();
												},
												expand: function(p) {
													win.doLayout();
												}
											},
											items:[	
												//combo - Анальгезия
												{							
													layout:'form',
													style:'margin-top: 2px;',
													labelWidth:10,
													border:false,
													items:[	
														//BOB - 21.03.2019
														{
															id: 'swERPEW_RC_Analgesia',
															hiddenName: 'RC_Analgesia',									
															xtype: 'swextemporalcomptypecombo',
															fieldLabel: '',
															labelSeparator: '',
															allowBlank: true,
															comboSubject: 'AnalgesiaType',
															width: 240,
															lastQuery: '',
															listeners: {
																'select': function(combo, record, index) {
																	var EvnScalesGridRow = win.findById('swERPEW_EvnReanimatCondition_Grid').getSelectionModel().getSelected();  //выбранная строка в гриде регулярного наблюдения состояния    
																		EvnScalesGridRow.data['AnalgesiaType_id'] = record.data.AnalgesiaType_id;
																		EvnScalesGridRow.commit();
																	//если пользовательский вариант
																	if (record.data.AnalgesiaType_id == 3) 
																		win.findById('swERPEW_RC_AnalgesiaTxt').setVisible( true );
																	else {
																		win.findById('swERPEW_RC_AnalgesiaTxt').setVisible( false );
																		win.findById('swERPEW_RC_AnalgesiaTxt').setValue('');
																		EvnScalesGridRow.data['EvnReanimatCondition_AnalgesiaTxt'] = '';
																		EvnScalesGridRow.commit();
																	}
																}
															}
														}																																																														
													]
												},
												//Анальгнзия - вариант пользователя
												{
													allowBlank: true,
													fieldLabel: '',
													labelSeparator: '',
													name: 'swERPEW_RC_AnalgesiaTxt',
													id: 'swERPEW_RC_AnalgesiaTxt',
													width: 1030,
													style:'margin-top: 2px; margin-left: 4px;',
												//	tabIndex: TABINDEX_MS,
													value:'',
													xtype: 'textfield',
													hidden: true,
													listeners:{
														'change':function (field, newValue, oldValue) {
															var EvnScalesGridRow = win.findById('swERPEW_EvnReanimatCondition_Grid').getSelectionModel().getSelected();  //выбранная строка в гриде Реанимационных мероприятий
															EvnScalesGridRow.data['EvnReanimatCondition_AnalgesiaTxt'] = newValue;
															EvnScalesGridRow.commit();
														}.createDelegate(this)
													}
												}
											]
										},
										//панель - Нутритивная поддержка / Объём инфузии 
										{
											id: 'swERPEW_RC_Nutritious_Panel',
											layout:'form',
											xtype: 'fieldset',
											autoHeight: true,
											title: langs('Нутритивная под-а / инфузия'),
											collapsible: true,
											style: 'margin: 3px 0 0 0; padding: 0 0 3px 0; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											listeners: {
												collapse: function(p) {
													win.doLayout();
												},
												expand: function(p) {
													win.doLayout();
												}
											},
											items:[	

												{
													layout:'column',
													border:false,
													items:[
														//combo - Нутритивная поддержка  //BOB - 28.08.2018
														{
															layout:'form',
															//style:'margin-top: 2px;',
															labelWidth:10,
															border:false,
															items:[
																//BOB - 21.03.2019
																{
																	id: 'swERPEW_RC_Nutritious',
																	hiddenName: 'RC_Nutritious',
																	xtype: 'swcommonsprcombo',
																	//xtype: 'swextemporalcomptypecombo',
																	fieldLabel: '',
																	labelSeparator: '',
																	allowBlank: true,
																	comboSubject: 'NutritiousType',
																	width: 240,
																	lastQuery: ''//,
//																	listeners: {  //BOB - 23.09.2019 - закомментарено
//																		'select': function(combo, record, index) {
//																			var EvnScalesGridRow = win.findById('swERPEW_EvnReanimatCondition_Grid').getSelectionModel().getSelected();  //выбранная строка в гриде регулярного наблюдения состояния
//																			EvnScalesGridRow.data['NutritiousType_id'] = record.data.NutritiousType_id;
//																			EvnScalesGridRow.commit();
//																			//если пользовательский вариант
//																			if (record.data.NutritiousType_id == 4)
//																				win.findById('swERPEW_RC_NutritiousTxt').setVisible( true );
//																			else {
//																				win.findById('swERPEW_RC_NutritiousTxt').setVisible( false );
//																				win.findById('swERPEW_RC_NutritiousTxt').setValue('');
//																				EvnScalesGridRow.data['EvnReanimatCondition_NutritiousTxt'] = '';
//																				EvnScalesGridRow.commit();
//																			}
//																		}
//																	}
																}

															]
														},   //BOB - 28.08.2018
														//Нутритивная поддержка  - варианта пользователя
														{
															allowBlank: true,
															name: 'swERPEW_RC_NutritiousTxt',
															id: 'swERPEW_RC_NutritiousTxt',
															width: 850,
															style:'margin-left: 4px;',  //margin-top: 2px;
															value:'',
															xtype: 'textfield'//,
//															listeners:{  //BOB - 23.09.2019 - закомментарено
//																'change':function (field, newValue, oldValue) {
//																	var EvnScalesGridRow = win.findById('swERPEW_EvnReanimatCondition_Grid').getSelectionModel().getSelected();  //выбранная строка в гриде Реанимационных мероприятий
//																	EvnScalesGridRow.data['EvnReanimatCondition_NutritiousTxt'] = newValue;
//																	EvnScalesGridRow.commit();
//																}.createDelegate(this)
//															}
														}

													]
												},

												//BOB - 23.09.2019
												//панель - объём питания и энергетическая ценность питания
												{
													layout:'column',
													border:false,
													items:[
														//Объём питания
														{
															layout:'form',
															style:'margin-top: 4px;',
															labelWidth:100,
															border:false,
															items:[
																new Ext.form.NumberField({
																	value: 0,
																	id: 'swERPEW_RC_NutritVol',
																	fieldLabel:'Объём питания',
																	labelSeparator: '',
																	enableKeyEvents: true,
																	width: 60
																})
															]
														},
														//Лейбл мл
														{
															layout:'form',
															style:'margin-left: 2px; margin-top: 5px; font-size: 10pt;',
															width: 15,
															border:false,
															items:[
																{
																	xtype: 'label',
																	text: 'мл'
																}
															]
														},
														//Энергетическая ценность питания
														{
															layout:'form',
															style:'margin-top: 4px;',
															labelWidth:160,
															border:false,
															items:[
																new Ext.form.NumberField({
																	value: 0,
																	id: 'swERPEW_RC_NutritEnerg',
																	fieldLabel:'Энергетическая ценность',
																	labelSeparator: '',
																	enableKeyEvents: true,
																	width: 60
																})
															]
														},
														//Лейбл ккал
														{
															layout:'form',
															style:'margin-left: 2px; margin-top: 5px; font-size: 10pt;',
															width: 30,
															border:false,
															items:[
																{
																	xtype: 'label',
																	text: 'ккал'
																}
															]
														}
													]
												},
												//BOB - 23.09.2019

												//Объём инфузии - панель //BOB - 23.09.2019
												{
													layout:'column',
													border:false,
													items:[
														//Объём инфузии   //BOB - 28.08.2018
														{
															layout:'form',
															style:'margin-top: 4px;',
															labelWidth:100,
															border:false,
															items:[
																new Ext.form.NumberField({
																	value: 0,
																	id: 'swERPEW_RC_InfusionVolume',
																	fieldLabel:'Объём инфузии',
																	labelSeparator: '',
																	enableKeyEvents: true,
																	width: 60,
																	listeners:{
																		'keyup':function (obj, e) {
																			var EvnScalesGridRow = win.findById('swERPEW_EvnReanimatCondition_Grid').getSelectionModel().getSelected();  //выбранная строка в гриде регулярного наблюдения состояния
																			EvnScalesGridRow.data['EvnReanimatCondition_InfusionVolume'] = obj.getValue();
																			EvnScalesGridRow.commit();
																		}.createDelegate(this)
																	}
																})
															]
														},
														//Лейбл мл
														{
															layout:'form',
															style:'margin-left: 2px; margin-top: 5px;  font-size: 10pt;',
															width: 15,
															border:false,
															items:[
																new Ext.form.Label({
																	text: 'мл',
																	id: 'swERPEW_RC_InfusionVolume_Unit'
																})
															]
														}    //BOB - 28.08.2018
													]
												}
											]
										},


										//Дополнительно
										{
											id: 'swERPEW_RC_Neurologic_Status_Panel',
											labelWidth: 5,
											xtype: 'fieldset',
											autoHeight: true,
											title: langs('Дополнительная информация'),
											collapsible: true,
											//collapsed: true,
											layout: 'form',
											style: 'margin: 3px 0 0 0; padding:0; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											listeners: {
												collapse: function(p) {
													win.doLayout();
												},
												expand: function(p) {
													win.doLayout();
												}
											},
											items:[	
												new Ext.form.TextArea({
													fieldLabel: '',
													labelSeparator: '',
													id: 'swERPEW_RC_Neurologic_Status',
													name: 'RC_Neurologic_Status',
													//enableKeyEvents: true,														
													height: 154,
													//width:800,
													anchor: '99%',
													listeners:{
														'change':function (field, newValue, oldValue) {
															var EvnScalesGridRow = win.findById('swERPEW_EvnReanimatCondition_Grid').getSelectionModel().getSelected();  //выбранная строка в гриде Реанимационных мероприятий
															EvnScalesGridRow.data['EvnReanimatCondition_NeurologicStatus'] = newValue;
															EvnScalesGridRow.commit();
														}.createDelegate(this)
													}
												})												
											]
										},


										//панель - Заключение  / проведено
										{
											id: 'swERPEW_RC_Conclusion_Panel',
											labelWidth: 5,
											xtype: 'fieldset',
											autoHeight: true,
											title: langs('Заключение'),
											collapsible: true,
											//collapsed: true,
											layout: 'form',
											style: 'margin: 3px 0 0 0; padding:0; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											listeners: {
												collapse: function(p) {
													win.doLayout();
												},
												expand: function(p) {
													win.doLayout();
												}
											},
											items:[	
												new Ext.form.TextArea({
													fieldLabel: '',
													labelSeparator: '',
													id: 'swERPEW_RC_Conclusion',
													name: 'RC_Conclusion',
													//enableKeyEvents: true,														
													height: 154,
													//width:800,
													anchor: '99%',
													listeners:{
														'change':function (field, newValue, oldValue) {
															var EvnScalesGridRow = win.findById('swERPEW_EvnReanimatCondition_Grid').getSelectionModel().getSelected();  //выбранная строка в гриде Реанимационных мероприятий
															EvnScalesGridRow.data['EvnReanimatCondition_Conclusion'] = newValue;
															EvnScalesGridRow.commit();
														}.createDelegate(this)
													}
												})												
											]
										}
									]
								}
							]
						}
					]
				}),
				
				
				
				
				
//Панель шкал исследования состояния
				new sw.Promed.Panel({
					title:'2. Шкалы исследования состояния',
					id:'swERPEW_Scales_Panel',
					autoHeight:true,
					border:true,
					collapsible:true,
					collapsed:true,   // !!!!!!!!!!!!!!!!!!!!на время разработки, чтобы дальнейшие области быстрее видеть
					isLoaded:false,					
					layout:'form',
					style:'margin-bottom: 0.5em; ',
					bodyStyle:'padding-top: 0.5em; border-top: 1px none #99bbe8; border-left: 1px solid #99bbe8; border-bottom: 1px solid #ffffff;border-right: 1px solid #ffffff;',
					autoScroll:true,
					listeners:{
						'expand':function (panel) {
							//загрузка таблицы при первом открытии панели
							if (panel.isLoaded === false) {
								panel.isLoaded = true;
							
								//загрузка грида исследований по шкалам
								panel.findById('swERPEW_EvnScales_Grid').getStore().load({
									params:{
										EvnScale_pid: this.EvnReanimatPeriod_id
									}
								});							
							}
							panel.doLayout();
							if (this.action == 'view'){
								Ext.getCmp('swERPEW_EvnScaleButtonAdd').disable(); // кнопку добавления делаю неактивной
								Ext.getCmp('swERPEW_EvnScaleButtonDel').disable(); // кнопку удаления делаю неаактивной
							}
							else {
								Ext.getCmp('swERPEW_EvnScaleButtonAdd').enable();	
								Ext.getCmp('swERPEW_EvnScaleButtonDel').enable(); // кнопку удаления делаю неаактивной
							}
						}.createDelegate(this)
					},					
					items:[
						//Панель - Таблица резултатов исследования по шкалам
						{
							border:true,
							style: 'border-top: 1px solid #99bbe8; border-left: 1px solid #99bbe8; border-bottom: 1px solid #ffffff;border-right: 1px solid #ffffff; padding: 0px; ',
							height:211,
							layout:'border',
							items:[
								//Таблица резултатов исследования по шкалам
								new Ext.grid.GridPanel({
									id: 'swERPEW_EvnScales_Grid',
									frame: false,
									border: false,
									loadMask: true,
									region: 'center',
									stripeRows: true,
									height:200,

									columns: [
										{dataIndex: 'EvnScale_setDate', header: 'Дата', hidden: false, renderer: Ext.util.Format.dateRenderer('d.m.Y'), resizable: false, sortable: false, width: 100},
										{dataIndex: 'EvnScale_setTime', header: 'Время', hidden: false, resizable: false, sortable: false, width: 100 },
										{dataIndex: 'ScaleType_Name', header: 'Наименование шкалы', hidden: false, resizable: false, sortable: false, width: 400	 },
										{dataIndex: 'EvnScale_Result', header: 'Результат', hidden: false, resizable: true, sortable: false, width: 100},
										{dataIndex: 'EvnScale_ResultTradic', header: 'Традиционная классификация', hidden: false, id: 'EvnScale_ResultTradic', resizable: true, sortable: false}
									],
									autoExpandColumn: 'EvnScale_ResultTradic',
									autoExpandMin: 100,
									sm:new Ext.grid.RowSelectionModel({
											listeners:{
												'rowselect':function (sm, rowIndex, record) {													
													this.EvnScale_view();//загрузка панели шкал													
												}.createDelegate(this)
											}
										}),
									store:new Ext.data.Store({
										autoLoad:false,
										listeners:{
											'load':function (store, records, index) {
												if(win.findById('swERPEW_Scales_Panel').isLoaded === true){
													if (store.getCount() == 0) {
														LoadEmptyRow(this.findById('swERPEW_EvnScales_Grid'));
													} else {
														this.findById('swERPEW_EvnScales_Grid').getSelectionModel().selectRow(this.ScaleGridLoadRawNum); 	//установка выбранности на первой строке грда 	
														this.ScaleGridLoadRawNum = 0;
													} 													
												}
											}.createDelegate(this)
										},
										reader:new Ext.data.JsonReader({
											id:'EvnScale_id'
										}, 
										[
											{mapping:'EvnScale_id', name:'EvnScale_id', type:'int'},
											{mapping:'EvnScale_pid', name:'EvnScale_pid', type:'int'},
											{mapping:'Person_id', name:'Person_id', type:'int'},
											{mapping:'PersonEvn_id', name:'PersonEvn_id', type:'int'},
											{mapping:'Server_id', name:'Server_id', type:'int'},
											{mapping:'EvnScale_setDate', name:'EvnScale_setDate', type:'date', dateFormat:'d.m.Y'},
											{mapping:'EvnScale_setTime', name:'EvnScale_setTime', type:'string'},
											{mapping:'ScaleType_id', name:'ScaleType_id', type:'int'},
											{mapping:'ScaleType_Name', name:'ScaleType_Name', type:'string'},
											{mapping:'ScaleType_SysNick', name:'ScaleType_SysNick', type:'string'},
											{mapping:'EvnScale_Result', name:'EvnScale_Result', type:'int'},
											{mapping:'EvnScale_ResultTradic', name:'EvnScale_ResultTradic', type:'string'},
											{mapping:'EvnScale_AgeMonth', name:'EvnScale_AgeMonth', type:'int'}
										]),
										url:'/?c=EvnReanimatPeriod&m=loudEvnScaleGrid'
									}),
									tbar:new sw.Promed.Toolbar({
										buttons:[
											{
												id: 'swERPEW_EvnScaleButtonAdd',
												handler:function () {
													this.EvnScale_Add();
												}.createDelegate(this),
												iconCls:'add16',
												text:'Добавить'
											},
											//кнопка удаления
											{
												id: 'swERPEW_EvnScaleButtonDel',
												handler:function () {
													this.EvnScale_Del();
												}.createDelegate(this),
												iconCls:'delete16',
												text:'Удалить'
											},
											// кнопка обновления списка
											{
												id: 'swERPEW_EvnScaleButtonRefresh',
												handler:function () {
													this.findById('swERPEW_EvnScales_Grid').getStore().reload();	//перезагрузка грида 
													this.findById('swERPEW_EvnScaleButtonSave').disable(); // кнопку сохранения делаю неактивной
													Ext.getCmp('swERPEW_EvnScaleButtonAdd').enable(); // кнопку добавления делаю активной
													Ext.getCmp('swERPEW_EvnScaleButtonDel').enable(); // кнопку добавления делаю активной
												}.createDelegate(this),
												iconCls:'refresh16',
												text:'Обновить'
											}											
										]
									}),
									//BOB - 25.12.2019
									keys: [{
										key: [
											Ext.EventObject.F3,
										],
										fn: function(inp, e) {
											e.stopEvent();
											e.returnValue = false;
											var grid = this.findById('swERPEW_EvnScales_Grid');

											switch ( e.getKey() ) {
												case Ext.EventObject.F3:
													if ( e.altKey ) {
														var params = new Object();
														params['key_id'] = grid.getSelectionModel().getSelected().data.EvnScale_id;
														params['key_field'] = 'EvnScale_id';
														getWnd('swAuditWindow').show(params);
													}
													break;
											}
										},
										scope: this,
										stopEvent: true
									}]
								})
							]
						},

						//Панель - Событие расчёта Шкалы	id: 'swERPEW_GeneralScalesPanel'	
						{
							//	xtype: 'panel', 
							id: 'swERPEW_GeneralScalesPanel',
							layout:'form',
							border:true,
							width: 1307, //  1457,
							style: 'border-top: 1px solid #99bbe8; border-left: 1px solid #99bbe8; border-bottom: 1px solid #ffffff;border-right: 1px solid #ffffff; padding: 2px 0px 0px 0px; margin-top: 10px;',
							items:[

								//ШАПКА События расчёта Шкалы
								{							
									layout:'column',
									border:true,
									items:[	
										//панель - combo Тип Шкалы исследования состояния    
										{	
											layout:'form',
											labelWidth: 80,
											border:true,
											items:[	
												//combo Тип Шкалы исследования состояния
												{
													id: 'swERPEW_EvnScaleType',
													hiddenName: 'EvnScaleType',									
													xtype: 'swreanimatactsctypecombo',
													comboSubject: 'ScaleType',
													suffix: 'Reanimat',
													fieldLabel: langs('Тип шкалы'),
													labelSeparator: '',
													width: 500,
													allowBlank: false,
													lastQuery: '',
													from_select: true,
													listeners: {
														'render': function(combo) {
															combo.getStore().load();
														},
														'select': function(combo, record, index) {
															//если выбрана не пустая строка															
															if (record){  //if (index > 0){
																var SysNick = record.data.ScaleType_SysNick;
																																
																Ext.select('[id$="ScalePanel"]').setStyle('display', 'none');
																Ext.select('#swERPEW_'+SysNick+'_ScalePanel').setStyle('display', 'block');
																win.findById('swERPEW_'+SysNick+'_ScalePanel').overall_results('swERPEW_EvnScaleType');
																//BOB - 20.02.2020  !!!!!!! надо бы уточнить возрастные интервалы для этих предупреждений !!!!!!!!!!!!   
																if(combo.from_select){ // BOB - 29.02.2020 - признак того, что обработчик по выбору из списка, а не из функции view
																	var age = win.getAge(win.pers_data.Person_Birthday.date, 'amer');
																	if (((SysNick == 'glasgow') && (age < 4)) || ((SysNick == 'glasgow_ch') && ((age >= 4) || (age < 1)))|| ((SysNick == 'glasgow_neonat') && (age >= 1)))
																		Ext.MessageBox.alert('Предупреждение!', 'Выбранный тип шкалы не соответствует возрасту пациента!');
																}
															}
															else { //выбрана пустая строка
																Ext.select('[id$="ScalePanel"]').setStyle('display', 'none');
																win.findById('swERPEW_EvnScaleResult').setText('0');
																win.findById('swERPEW_EvnScaleResultText').setText('');
															}
															
															//заполнение строки грида
															var EvnScalesGridRow = win.findById('swERPEW_EvnScales_Grid').getSelectionModel().getSelected();  //выбранная строка в гриде событий шкал
															//если запись в гриде новая
															if (EvnScalesGridRow.data['EvnScale_id'] == 'New_GUID_Id'){
																//если record существует
																if(record){
																//	console.log('BOB_record=',record); 
																	EvnScalesGridRow.data['ScaleType_SysNick'] = record.data.ScaleType_SysNick;
																	EvnScalesGridRow.data['ScaleType_Name'] = record.data.ScaleType_Name;
																	if (record.data.ScaleType_id){
																		EvnScalesGridRow.data['ScaleType_id'] = record.data.ScaleType_id;
																	}
																	else {
																		EvnScalesGridRow.data['ScaleType_id'] = 0;
																	}
																	EvnScalesGridRow.data['EvnScale_Result'] = win.findById('swERPEW_EvnScaleResult').text;
																	EvnScalesGridRow.data['EvnScale_ResultTradic'] = win.findById('swERPEW_EvnScaleResultText').text;
																	
																	EvnScalesGridRow.commit();			
																//	console.log('BOB_EvnScalesGridRow.data=',EvnScalesGridRow.data);
																}
															}
															combo.from_select = true;
														}
													}

												} 								
											]
										},
										//Дата События расчёта Шкалы
										{									
											layout:'form',
											width: 160,
											labelWidth: 50,
											items:[										
												{
													allowBlank:true,
													fieldLabel:'Дата',
													format:'d.m.Y',
													id: 'swERPEW_EvnScale_setDate',
													listeners:{
														'change':function (field, newValue, oldValue) {
															
															//заполнение строки грида
															var EvnScalesGridRow = win.findById('swERPEW_EvnScales_Grid').getSelectionModel().getSelected();  //выбранная строка в гриде событий шкал
															//если запись в гриде новая
															if (EvnScalesGridRow.data['EvnScale_id'] == 'New_GUID_Id'){
																//	console.log('BOB_record=',record); 
																EvnScalesGridRow.data['EvnScale_setDate'] = newValue;
																EvnScalesGridRow.commit();			
															}
															
															
															
															this.changedDates = true; //а зачем не знаю		
														}.createDelegate(this),
														'keydown':function (inp, e) {
															//сделал по образцу из формы движения, а зачем не знаю
															if (e.getKey() == Ext.EventObject.TAB && e.shiftKey == true) {
																e.stopEvent();
																this.buttons[this.buttons.length - 1].focus();
															}
														}.createDelegate(this)
													},
													name:'EvnScale_setDate',
													plugins:[ new Ext.ux.InputTextMask('99.99.9999', false) ],
													selectOnFocus:true,
															//tabIndex:this.tabIndex + 1,
													width:100,
													xtype:'swdatefield'
												}										
											]
										},	
										//Время События расчёта Шкалы
										{									
											layout:'form',
											width: 120,
											labelWidth: 50,
											items:[	
												
												{
													fieldLabel:'Время',
													allowBlank: false,
													listeners:{
														'change':function (field, newValue, oldValue) {
															
															//заполнение строки грида
															var EvnScalesGridRow = win.findById('swERPEW_EvnScales_Grid').getSelectionModel().getSelected();  //выбранная строка в гриде событий шкал
															//если запись в гриде новая
															if (EvnScalesGridRow.data['EvnScale_id'] == 'New_GUID_Id'){
																//	console.log('BOB_record=',record); 
																EvnScalesGridRow.data['EvnScale_setTime'] = newValue;
																EvnScalesGridRow.commit();			
															}
															this.changedDates = true;
															
														}.createDelegate(this),
														'keydown':function (inp, e) {
															if (e.getKey() == Ext.EventObject.F4) {
																e.stopEvent();
																inp.onTriggerClick();
															}
														}
													},
													id: 'swERPEW_EvnScale_setTime',
													name:'EvnScale_setTime',
													onTriggerClick:function () {
														var base_form = this.findById('swERPEW_Form').getForm();
														var time_field = base_form.findField('EvnScale_setTime');

														if (time_field.disabled) {
															return false;
														}

														setCurrentDateTime({
															callback:function () {
																//заполнение строки грида
																var EvnScalesGridRow = win.findById('swERPEW_EvnScales_Grid').getSelectionModel().getSelected();  //выбранная строка в гриде событий шкал
																//если запись в гриде новая
																if (EvnScalesGridRow.data['EvnScale_id'] == 'New_GUID_Id'){
																//		console.log('BOB_time_field=',time_field.getValue()); 
																	EvnScalesGridRow.data['EvnScale_setTime'] = time_field.getValue();
																	EvnScalesGridRow.commit();			
																}
																
																base_form.findField('swERPEW_EvnScale_setDate').fireEvent('change', base_form.findField('swERPEW_EvnScale_setDate'), base_form.findField('swERPEW_EvnScale_setDate').getValue());
															}.createDelegate(this),
															dateField:base_form.findField('swERPEW_EvnScale_setDate'),
															loadMask:true,
															setDate:true,
															setDateMaxValue:true,
															addMaxDateDays: this.addMaxDateDays,
															setDateMinValue:false,
															setTime:true,
															timeField:time_field,
															windowId:this.id
														});
													}.createDelegate(this),
													plugins:[ new Ext.ux.InputTextMask('99:99', true) ],
													//tabIndex:this.tabIndex + 4,
													validateOnBlur:false,
													width:60,
													xtype:'swtimefield'
												}										
												
											]
										}										
									]
								},																
								//Шкала Glasgow 
								{							
									layout:'column',
									id:'swERPEW_glasgow_ScalePanel',
									border:true,
									style: 'border-top: 1px solid #ffffff; border-left: 1px solid  #ffffff; border-bottom: 1px solid #99bbe8;border-right: 1px solid #99bbe8; padding: 2px; ',
									items:[	
										
										//панель - Открывание глаз    
										//BOB - 27.08.2019	
										{
											xtype: 'swscaleparameter',
											nam_begin: 'swERPEW',
											scale_name: 'glasgow',
											parameter_name: 'eye_response',
											text_anchor: '95%',
											lbl_text: 'Открывание глаз',
											text_width: 140,
											combo_width: 160,
											value_width: 10,
											win: win
										},
										//панель - Речевая реакция  //BOB - 27.08.2019	  
										new sw.Promed.SwScaleParameter({
											nam_begin: 'swERPEW',
											scale_name: 'glasgow',
											parameter_name: 'verbal_response',
											text_anchor: '98%',
											lbl_text: 'Речевая реакция',
											text_width: 460,
											combo_width: 480,
											value_width: 10,
											win: this
										}),
										//панель - Двигательная реакция   //BOB - 27.08.2019	  
										new sw.Promed.SwScaleParameter({
											nam_begin: 'swERPEW',
											scale_name: 'glasgow',
											parameter_name: 'motor_response',
											text_anchor: '98%',
											lbl_text: 'Двигательная реакция',
											text_width: 460,
											combo_width: 480,
											value_width: 10,
											win: this
										})
									],
									//функция расчёта общего итогов
									overall_results: function(object_id){
										var eye_response_Val = parseInt(this.findById('swERPEW_glasgow_eye_response_Val').text);
										//console.log('BOB_eye_response_Val=',eye_response_Val);  
										var verbal_response_Val = parseInt(this.findById('swERPEW_glasgow_verbal_response_Val').text);
										var motor_response_Val = parseInt(this.findById('swERPEW_glasgow_motor_response_Val').text);
										var sum = eye_response_Val + verbal_response_Val + motor_response_Val;
										win.findById('swERPEW_EvnScaleResult').setText(sum);
										
										if ((sum === 0) || (eye_response_Val === 0) || (verbal_response_Val === 0) || (motor_response_Val === 0))
											win.findById('swERPEW_EvnScaleResultText').setText('');
										else if (sum <= 3)
											win.findById('swERPEW_EvnScaleResultText').setText('терминальная кома, смерть мозга');
										else if (sum <= 5)
											win.findById('swERPEW_EvnScaleResultText').setText('глубокая кома, кома-2');
										else if (sum <= 7)
											win.findById('swERPEW_EvnScaleResultText').setText('умеренная кома, кома-1');
										else if (sum <= 10)
											win.findById('swERPEW_EvnScaleResultText').setText('сопор');
										else if (sum <= 12)
											win.findById('swERPEW_EvnScaleResultText').setText('глубокое оглушение');
										else if (sum <= 14)
											win.findById('swERPEW_EvnScaleResultText').setText('умеренное оглушение');
										else 
											win.findById('swERPEW_EvnScaleResultText').setText('сознание ясное');
										
										//заполнение строки грида
										var EvnScalesGridRow = win.findById('swERPEW_EvnScales_Grid').getSelectionModel().getSelected();  //выбранная строка в гриде событий шкал
										//если запись в гриде новая
										if (EvnScalesGridRow.data['EvnScale_id'] == 'New_GUID_Id'){
											//	console.log('BOB_record=',record); 
											EvnScalesGridRow.data['EvnScale_Result'] = win.findById('swERPEW_EvnScaleResult').text;
											EvnScalesGridRow.data['EvnScale_ResultTradic'] = win.findById('swERPEW_EvnScaleResultText').text;																	
											EvnScalesGridRow.commit();			
										}
									}
								},								
								//Шкала Glasgow_ch 
								{							
									layout:'column',
									id:'swERPEW_glasgow_ch_ScalePanel',
									border:true,
									style: 'border-top: 1px solid #ffffff; border-left: 1px solid  #ffffff; border-bottom: 1px solid #99bbe8;border-right: 1px solid #99bbe8; padding: 2px;  ',
									items:[	
										
										//панель - Открывание глаз    
										//BOB - 27.08.2019	
										{
											xtype: 'swscaleparameter',
											nam_begin: 'swERPEW',
											scale_name: 'glasgow_ch',
											parameter_name: 'eye_response',
											text_anchor: '95%',
											lbl_text: 'Открывание глаз',
											text_width: 140,
											combo_width: 160,
											value_width: 10,
											win: this
										},
										//панель - Речевая реакция  //BOB - 27.08.2019	  
										new sw.Promed.SwScaleParameter({
											nam_begin: 'swERPEW',
											scale_name: 'glasgow_ch',
											parameter_name: 'verbal_response',
											text_anchor: '98%',
											lbl_text: 'Речевая реакция',
											text_width: 460,
											combo_width: 480,
											value_width: 10,
											win: this
										}),  
										//панель - Двигательная реакция    //BOB - 27.08.2019	  
										new sw.Promed.SwScaleParameter({
											nam_begin: 'swERPEW',
											scale_name: 'glasgow_ch',
											parameter_name: 'motor_response',
											text_anchor: '98%',
											lbl_text: 'Двигательная реакция',
											text_width: 460,
											combo_width: 480,
											value_width: 10,
											win: this
										})
									],
									//функция расчёта общего итогов
									overall_results: function(object_id){
										var eye_response_Val = parseInt(this.findById('swERPEW_glasgow_ch_eye_response_Val').text);
									//	console.log('BOB_eye_response_Val=',eye_response_Val);  
										var verbal_response_Val = parseInt(this.findById('swERPEW_glasgow_ch_verbal_response_Val').text);
										var motor_response_Val = parseInt(this.findById('swERPEW_glasgow_ch_motor_response_Val').text);
										var sum = eye_response_Val + verbal_response_Val + motor_response_Val;
										win.findById('swERPEW_EvnScaleResult').setText(sum);
										
										if ((sum === 0) || (eye_response_Val === 0) || (verbal_response_Val === 0) || (motor_response_Val === 0))
											win.findById('swERPEW_EvnScaleResultText').setText('');
										else if (sum <= 3)
											win.findById('swERPEW_EvnScaleResultText').setText('терминальная кома, смерть мозга');
										else if (sum <= 5)
											win.findById('swERPEW_EvnScaleResultText').setText('глубокая кома, кома-2');
										else if (sum <= 7)
											win.findById('swERPEW_EvnScaleResultText').setText('умеренная кома, кома-1');
										else if (sum <= 10)
											win.findById('swERPEW_EvnScaleResultText').setText('сопор');
										else if (sum <= 12)
											win.findById('swERPEW_EvnScaleResultText').setText('глубокое оглушение');
										else if (sum <= 14)
											win.findById('swERPEW_EvnScaleResultText').setText('умеренное оглушение');
										else 
											win.findById('swERPEW_EvnScaleResultText').setText('сознание ясное');
										
										//заполнение строки грида
										var EvnScalesGridRow = win.findById('swERPEW_EvnScales_Grid').getSelectionModel().getSelected();  //выбранная строка в гриде событий шкал
										//если запись в гриде новая
										if (EvnScalesGridRow.data['EvnScale_id'] == 'New_GUID_Id'){
											//	console.log('BOB_record=',record); 
											EvnScalesGridRow.data['EvnScale_Result'] = win.findById('swERPEW_EvnScaleResult').text;
											EvnScalesGridRow.data['EvnScale_ResultTradic'] = win.findById('swERPEW_EvnScaleResultText').text;																	
											EvnScalesGridRow.commit();			
										}										
									}
								},
								//Шкала SOFA 
								{							
									layout:'form',
									id:'swERPEW_sofa_ScalePanel',
									border:true,
									style: 'border-top: 1px solid #ffffff; border-left: 1px solid  #ffffff; border-bottom: 1px solid #99bbe8;border-right: 1px solid #99bbe8; padding: 2px;  ',
									items:[	
										
										{							
											layout:'column',
											items:[	
												//панель - Дыхательная система [PaO2/FiO2]     //BOB - 27.08.2019	  
												new sw.Promed.SwScaleParameter({
													nam_begin: 'swERPEW',
													scale_name: 'sofa',
													parameter_name: 'respiratory',
													text_anchor: '98%',
													lbl_text: 'Дыхательная система [PaO2/FiO2]',
													text_width: 290,
													combo_width: 310,
													value_width: 10,
													win: this
												}),												
												//панель - Коагуляция [тромбоцитов на мл]    //BOB - 27.08.2019	  
												new sw.Promed.SwScaleParameter({
													nam_begin: 'swERPEW',
													scale_name: 'sofa',
													parameter_name: 'coagulation',
													text_anchor: '97%',
													lbl_text: 'Коагуляция [тромбоцитов на мл]',
													text_width: 180,
													combo_width: 200,
													value_width: 10,
													win: this
												}),		
												//панель - Печень [билирубин сыворотки]    //BOB - 27.08.2019	  
												new sw.Promed.SwScaleParameter({
													nam_begin: 'swERPEW',
													scale_name: 'sofa',
													parameter_name: 'bilirubin',
													text_anchor: '98%',
													lbl_text: 'Печень [билирубин сыворотки]',
													text_width: 320,
													combo_width: 340,
													value_width: 10,
													win: this
												})
											]
										},
										{							
											layout:'column',
											items:[	
												//панель - Сердечно-сосудистая система     //BOB - 27.08.2019	  
												new sw.Promed.SwScaleParameter({
													nam_begin: 'swERPEW',
													scale_name: 'sofa',
													parameter_name: 'cardiovascular',
													text_anchor: '98%',
													lbl_text: 'Сердечно-сосудистая система',
													text_width: 290,
													combo_width: 310,
													value_width: 10,
													win: this
												}),
												//панель - Нервная система [Глазго]   //BOB - 27.08.2019	  
												new sw.Promed.SwScaleParameter({
													nam_begin: 'swERPEW',
													scale_name: 'sofa',
													parameter_name: 'glasgow',
													text_anchor: '97%',
													lbl_text: 'Нервная система [Глазго]',
													text_width: 180,
													combo_width: 200,
													value_width: 10,
													win: this
												}), 
												//панель - Почечная [креатинин сыворотки или диурез]    //BOB - 27.08.2019	  
												new sw.Promed.SwScaleParameter({
													nam_begin: 'swERPEW',
													scale_name: 'sofa',
													parameter_name: 'nephritic',
													text_anchor: '98%',
													lbl_text: 'Почечная [креатинин сыворотки или диурез]',
													text_width: 320,
													combo_width: 340,
													value_width: 10,
													win: this
												})
											]
										}										
									],
									//функция расчёта общего итогов
									overall_results: function(object_id){
										var respiratory_Val = parseInt(this.findById('swERPEW_sofa_respiratory_Val').text);
										var coagulation_Val = parseInt(this.findById('swERPEW_sofa_coagulation_Val').text);
										var bilirubin_Val = parseInt(this.findById('swERPEW_sofa_bilirubin_Val').text);
										var cardiovascular_Val = parseInt(this.findById('swERPEW_sofa_cardiovascular_Val').text);
										var glasgow_Val = parseInt(this.findById('swERPEW_sofa_glasgow_Val').text);
										var nephritic_Val = parseInt(this.findById('swERPEW_sofa_nephritic_Val').text);
										var sum = respiratory_Val + coagulation_Val + bilirubin_Val + cardiovascular_Val + glasgow_Val + nephritic_Val;
										win.findById('swERPEW_EvnScaleResult').setText(sum);
										
										//формирование строки	Традиционной классификации , по данной шкале пока нету
										win.findById('swERPEW_EvnScaleResultText').setText('');
									/*	if ((sum === 0) || (respiratory_Val === 0) || (coagulation_Val === 0) || (bilirubin_Val === 0))
											win.findById('swERPEW_EvnScaleResultText').setText('');
										else if (sum <= 3)
											win.findById('swERPEW_EvnScaleResultText').setText('терминальная кома, смерть мозга');
										else if (sum <= 5)
											win.findById('swERPEW_EvnScaleResultText').setText('глубокая кома, кома-2');
										else if (sum <= 7)
											win.findById('swERPEW_EvnScaleResultText').setText('умеренная кома, кома-1');
										else if (sum <= 10)
											win.findById('swERPEW_EvnScaleResultText').setText('сопор');
										else if (sum <= 12)
											win.findById('swERPEW_EvnScaleResultText').setText('глубокое оглушение');
										else if (sum <= 14)
											win.findById('swERPEW_EvnScaleResultText').setText('умеренное оглушение');
										else 
											win.findById('swERPEW_EvnScaleResultText').setText('сознание ясное');*/
										
										//заполнение строки грида
										var EvnScalesGridRow = win.findById('swERPEW_EvnScales_Grid').getSelectionModel().getSelected();  //выбранная строка в гриде событий шкал
										//если запись в гриде новая
										if (EvnScalesGridRow.data['EvnScale_id'] == 'New_GUID_Id'){
											//	console.log('BOB_record=',record); 
											EvnScalesGridRow.data['EvnScale_Result'] = win.findById('swERPEW_EvnScaleResult').text;
											EvnScalesGridRow.data['EvnScale_ResultTradic'] = win.findById('swERPEW_EvnScaleResultText').text;																	
											EvnScalesGridRow.commit();			
										}
									}
								},								
								//Шкала APACHE II 
								{							
									layout:'column',
									id:'swERPEW_apache_ScalePanel',
									border:true,
									correction: 0,
									items:[	
										//Основные параметры 
										{							
											layout:'form',
											border:true,
											style: 'border-top: 1px solid #ffffff; border-left: 1px solid  #ffffff; border-bottom: 1px solid #99bbe8;border-right: 1px solid #99bbe8; padding: 2px;  ',
											width: 869,
											items:[	
												{							
													layout:'column',
													items:[	

														//панель - Ректальная температура   //BOB - 27.08.2019	  
														new sw.Promed.SwScaleParameter({
															nam_begin: 'swERPEW',
															scale_name: 'apache',
															parameter_name: 'temperature',
															text_anchor: '98%',
															lbl_text: 'Ректальная температура',
															text_width: 160,
															combo_width: 180,
															value_width: 10,
															win: this
														}),
														//панель - Среднее артериальное давление  //BOB - 27.08.2019	  
														new sw.Promed.SwScaleParameter({
															nam_begin: 'swERPEW',
															scale_name: 'apache',
															parameter_name: 'pressure',
															text_anchor: '98%',
															lbl_text: 'Среднее артериальное давление',
															text_width: 210,
															combo_width: 230,
															value_width: 10,
															win: this
														}), 
														//панель - Частота сердечных сокращений   //BOB - 27.08.2019	  
														new sw.Promed.SwScaleParameter({
															nam_begin: 'swERPEW',
															scale_name: 'apache',
															parameter_name: 'heart_frequency',
															text_anchor: '98%',
															lbl_text: 'Частота сердечных сокращений',
															text_width: 210,
															combo_width: 230,
															value_width: 10,
															win: this
														}), 
														//панель - Частота дыхания  //BOB - 27.08.2019	  
														new sw.Promed.SwScaleParameter({
															nam_begin: 'swERPEW',
															scale_name: 'apache',
															parameter_name: 'breath_frequency',
															text_anchor: '98%',
															lbl_text: 'Частота дыхания',
															text_width: 179,
															combo_width: 199,
															value_width: 10,
															win: this
														}) 
													]
												},
												{							
													layout:'column',
													items:[	
														//панель - Дыхательная система - Оксигенация  //BOB - 27.08.2019	  
														new sw.Promed.SwScaleParameter({
															nam_begin: 'swERPEW',
															scale_name: 'apache',
															parameter_name: 'respiration',
															text_anchor: '98%',
															lbl_text: 'Дыхательная система - Оксигенация',
															text_width: 250,
															combo_width: 270,
															value_width: 10,
															win: this
														}), 
														//панель - pH артериальной крови или HCO3    //BOB - 27.08.2019	  
														new sw.Promed.SwScaleParameter({
															nam_begin: 'swERPEW',
															scale_name: 'apache',
															parameter_name: 'acidity',
															text_anchor: '98%',
															lbl_text: 'pH артериальной крови или HCO3',
															text_width: 320,
															combo_width: 340,
															value_width: 10,
															win: this
														}), 
														//панель - Натрий сыворотки    //BOB - 27.08.2019	  
														new sw.Promed.SwScaleParameter({
															nam_begin: 'swERPEW',
															scale_name: 'apache',
															parameter_name: 'natrium',
															text_anchor: '98%',
															lbl_text: 'Натрий сыворотки',
															text_width: 215,
															combo_width: 235,
															value_width: 10,
															win: this
														}) 
													]
												},
												{							
													layout:'column',
													items:[	
														
														//панель - Калий сыворотки   //BOB - 27.08.2019	  
														new sw.Promed.SwScaleParameter({
															nam_begin: 'swERPEW',
															scale_name: 'apache',
															parameter_name: 'potassium',
															text_anchor: '98%',
															lbl_text: 'Калий сыворотки',
															text_width: 250,
															combo_width: 270,
															value_width: 10,
															win: this
														}), 
														//панель - Креатинин    //BOB - 27.08.2019	  
														new sw.Promed.SwScaleParameter({
															nam_begin: 'swERPEW',
															scale_name: 'apache',
															parameter_name: 'creatinine',
															text_anchor: '98%',
															lbl_text: 'Креатинин',
															text_width: 320,
															combo_width: 340,
															value_width: 10,
															win: this
														}), 
														//панель - Гематокрит  //BOB - 27.08.2019	  
														new sw.Promed.SwScaleParameter({
															nam_begin: 'swERPEW',
															scale_name: 'apache',
															parameter_name: 'hematocrit',
															text_anchor: '98%',
															lbl_text: 'Гематокрит',
															text_width: 215,
															combo_width: 235,
															value_width: 10,
															win: this
														})  
													]
												},
												{							
													layout:'column',
													items:[	
														//панель - Лейкоциты  //BOB - 27.08.2019	  
														new sw.Promed.SwScaleParameter({
															nam_begin: 'swERPEW',
															scale_name: 'apache',
															parameter_name: 'leucocytes',
															text_anchor: '98%',
															lbl_text: 'Лейкоциты',
															text_width: 140,
															combo_width: 160,
															value_width: 10,
															win: this
														}),   
														//панель - Оценка по шкале Глазго  //BOB - 27.08.2019	  
														new sw.Promed.SwScaleParameter({
															nam_begin: 'swERPEW',
															scale_name: 'apache',
															parameter_name: 'glasgow',
															text_anchor: '98%',
															lbl_text: 'Оценка по Глазго',
															text_width: 130,
															combo_width: 160,
															value_width: 20,
															win: this
														}),   
														//панель - Оценка возраста  //BOB - 27.08.2019	  
														new sw.Promed.SwScaleParameter({
															nam_begin: 'swERPEW',
															scale_name: 'apache',
															parameter_name: 'age',
															text_anchor: '98%',
															lbl_text: 'Оценка возраста',
															text_width: 140,
															combo_width: 160,
															value_width: 10,
															win: this
														}),   
														//панель - Органная недостаточность или иммунодефицитное состояние  //BOB - 27.08.2019	  
														new sw.Promed.SwScaleParameter({
															nam_begin: 'swERPEW',
															scale_name: 'apache',
															parameter_name: 'organ_insufficiency',
															text_anchor: '98%',
															lbl_text: 'Органная недостаточность или иммунодефицитное состояние',
															text_width: 339,
															combo_width: 359,
															value_width: 10,
															win: this
														})   
													]
												}
											]
										},
										//Корректировка 
										{							
											layout:'form',
											border:true,
											style: 'border-top: 1px solid #ffffff; border-left: 1px solid  #ffffff; border-bottom: 1px solid #99bbe8;border-right: 1px solid #99bbe8; padding: 2px; margin-left: 2px ',
											width: 431,//581,
											height: 198,
											items:[	
												new Ext.tree.TreePanel({
													id: 'swERPEW_apache_Tree',
													animate:false,
													width: 420,
													height: 190,
													style: 'border-top: 1px solid #99bbe8; border-left: 1px solid #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; margin-left: 2px; background-color: #ffffff',
													enableDD: false,
													autoScroll: true,
													autoLoad:false,
													//clearOnLoad:false,
													border: true,
													rootVisible: false,
													root: 
													{
														nodeType: 'async',
														id:'root',
														expanded: true
													},
													loader: new Ext.tree.TreeLoader({
														listeners:
														{
															load: function(loader, node, response)
															{
																//console.log('BOB_Object=','load');
															},
															beforeload: function (tl, node)
															{
																//console.log('BOB_Object=','beforeload');
															}
														},
														clearOnLoad: true,
														dataUrl:'/?c=EvnReanimatPeriod&m=getapache_TreeData'
													}),													
													selModel: new Ext.tree.KeyHandleTreeSelectionModel(),
													listeners: {
														'click': function(node)
														{		
															//console.log('BOB_this=',this);
															if (node.attributes.leaf == true) {
																win.findById('swERPEW_apache_ScalePanel').correction = parseFloat(node.attributes.ScaleParameterResult_Value);
																this.ScaleRequ.ScaleParameterResult_id = node.attributes.ScaleParameterResult_id;
																this.ScaleRequ.ScaleParameterType_SysNick = node.attributes.ScaleParameterType_SysNick;
																this.ScaleRequ.ScaleParameterType_id = node.attributes.ScaleParameterType_id;
															}
															else
																win.findById('swERPEW_apache_ScalePanel').correction = 0;
															
															win.findById('swERPEW_apache_ScalePanel').overall_results();
															
														}
													},
													ScaleRequ :{  // я тут храню реквизиты узла на случай отображения нового расчёта после отображения сохранённого
														ScaleType_SysNick: 'apache',
														ScaleParameterResult_id: '',
														ScaleParameterType_SysNick: '',
														ScaleParameterType_id: ''
													}
												})
											]
										}
									],
									//функция расчёта общего итогов
									overall_results: function(object_id){
										var sum = 0;
										Ext.select('label[id$="Val"]', true, 'swERPEW_apache_ScalePanel').each(function(el){
											var id = el.id; //выделяю параметр id из Ext.Element
											var object = win.findById(id);	//ищу в окне объект ExtJS
											if(object){ // если нахожу, то 
												sum += 	parseInt(object.text);
											}
										});	
										
										win.findById('swERPEW_EvnScaleResult').setText(sum);
										// контролирую все ли параметры выбраны
										var toDo = true;
										var ParamCombos = win.findById('swERPEW_apache_ScalePanel').find('xtype', 'combo');
										for (var i in ParamCombos){
											if(ParamCombos[i].id) {
												//console.log('BOB_ParamCombos[i].getValue()=',ParamCombos[i].getValue());
												if ((ParamCombos[i].getValue() == null) || (ParamCombos[i].getValue() == '')) {
													toDo = false;
													break;
												}	
											}
										}
										//расчитываю риск смерти
										if (toDo) {
											var correction = win.findById('swERPEW_apache_ScalePanel').correction;;  //величина коррекции
											var logit = -3.517 + sum*0.146;
											var death_risk = Math.round((Math.exp(logit)/(1 + Math.exp(logit))) * 10000) / 100;  //Риск смерти нескорректированный
											logit += correction;
											var death_risk_corr = Math.round((Math.exp(logit)/(1 + Math.exp(logit))) * 10000) / 100;  //Риск смерти нескорректированный
											//формирование строки Риск смерти
											win.findById('swERPEW_EvnScaleResultText').setText('Риск смерти - ' + death_risk + '%, скорректированный - ' + death_risk_corr + '%');
										}
										else
											win.findById('swERPEW_EvnScaleResultText').setText('');
										
										//заполнение строки грида
										var EvnScalesGridRow = win.findById('swERPEW_EvnScales_Grid').getSelectionModel().getSelected();  //выбранная строка в гриде событий шкал
										//если запись в гриде новая
										if (EvnScalesGridRow.data['EvnScale_id'] == 'New_GUID_Id'){
											//	console.log('BOB_record=',record); 
											EvnScalesGridRow.data['EvnScale_Result'] = win.findById('swERPEW_EvnScaleResult').text;
											EvnScalesGridRow.data['EvnScale_ResultTradic'] = win.findById('swERPEW_EvnScaleResultText').text;																	
											EvnScalesGridRow.commit();			
										}

										//проверка возраста  //BOB - 23.10.2019
										//console.log('BOB_object_id=',object_id);
										if ((object_id == 'swERPEW_apache_age') && (EvnScalesGridRow.data['EvnScale_id'] == 'New_GUID_Id')) {
											var index = win.findById('swERPEW_apache_age').getStore().find('ScaleParameterResult_id',win.findById('swERPEW_apache_age').value);//нахожу индекс в store комбо возраста
											if (index > -1){
												var ageInterval = win.findById('swERPEW_apache_age').getStore().getAt(index).data.ScaleParameterResult_Name;  // интервал возрастов
												var age = win.getAge(   win.pers_data.Person_Birthday.date, 'amer');
												if ((ageInterval == '<= 44') && (age > 44))
													Ext.MessageBox.alert('Предупреждение!', 'Выбранный интервал не соответствует возрасту пациента!');
												if ((ageInterval == '45-54') && ((age < 45) || (age > 54)))
													Ext.MessageBox.alert('Предупреждение!', 'Выбранный интервал не соответствует возрасту пациента!');
												if ((ageInterval == '55-64') && ((age < 55)  || (age > 64)))
													Ext.MessageBox.alert('Предупреждение!', 'Выбранный интервал не соответствует возрасту пациента!');
												if ((ageInterval == '65-74') && ((age < 65)  || (age > 74)))
													Ext.MessageBox.alert('Предупреждение!', 'Выбранный интервал не соответствует возрасту пациента!');
												if ((ageInterval == '>= 75') && (age < 75))
													Ext.MessageBox.alert('Предупреждение!', 'Выбранный интервал не соответствует возрасту пациента!');
											}
										}
									},
									scale_load: function(ScaleRequ) {
										var intScaleParameterType_id = parseInt(ScaleRequ.ScaleParameterType_id);
										var varTree = win.findById('swERPEW_apache_Tree');
										//console.log('BOB_ScaleRequ=',ScaleRequ);
										varTree.getLoader().load(
											varTree.getNodeById('root'),
											function() {

												if ((intScaleParameterType_id >= 29) && (intScaleParameterType_id <= 34)){
													varTree.getLoader().load(
														varTree.getNodeById('no_oper'),
														function() {
															//console.log('BOB_node=',node);
															varTree.getNodeById('no_oper').loaded = true;
															varTree.getNodeById('no_oper').expand();
															varTree.getLoader().load(
																varTree.getNodeById(ScaleRequ.ScaleParameterType_SysNick),
																function() {
																	varTree.getNodeById(ScaleRequ.ScaleParameterType_SysNick).loaded = true;
																	varTree.getNodeById(ScaleRequ.ScaleParameterType_SysNick).expand();
																	varTree.getNodeById(ScaleRequ.ScaleParameterType_SysNick+'_'+ScaleRequ.ScaleParameterResult_id).select();
																	win.findById('swERPEW_apache_ScalePanel').correction = parseFloat(varTree.getNodeById(ScaleRequ.ScaleParameterType_SysNick+'_'+ScaleRequ.ScaleParameterResult_id).attributes.ScaleParameterResult_Value);
																	win.findById('swERPEW_apache_ScalePanel').overall_results();
																}
															);								
														}		
													);
												}
												else if ((intScaleParameterType_id == 35) || (intScaleParameterType_id  == 37)){
													varTree.getLoader().load(
														varTree.getNodeById('oper'),
														function() {
															//console.log('BOB_node=',node);
															varTree.getNodeById('oper').loaded = true;
															varTree.getNodeById('oper').expand();
															varTree.getLoader().load(
																varTree.getNodeById(ScaleRequ.ScaleParameterType_SysNick),
																function() {
																	varTree.getNodeById(ScaleRequ.ScaleParameterType_SysNick).loaded = true;
																	varTree.getNodeById(ScaleRequ.ScaleParameterType_SysNick).expand();
																	varTree.getNodeById(ScaleRequ.ScaleParameterType_SysNick+'_'+ScaleRequ.ScaleParameterResult_id).select();
																	win.findById('swERPEW_apache_ScalePanel').correction = parseFloat(varTree.getNodeById(ScaleRequ.ScaleParameterType_SysNick+'_'+ScaleRequ.ScaleParameterResult_id).attributes.ScaleParameterResult_Value);
																	win.findById('swERPEW_apache_ScalePanel').overall_results();
																}
															);								
														}		
													);
												}
												else if ((intScaleParameterType_id == 36) || (intScaleParameterType_id  == 38)){
													varTree.getLoader().load(
														varTree.getNodeById('oper'),
														function() {
															//console.log('BOB_node=',node);
															varTree.getNodeById('oper').loaded = true;
															varTree.getNodeById('oper').expand();
															//console.log('BOB_node=',ScaleRequ.ScaleParameterType_SysNick.replace('_organ_system', ''));
															varTree.getLoader().load(
																varTree.getNodeById(ScaleRequ.ScaleParameterType_SysNick.replace('_organ_system', '')),
																function() {
																	varTree.getNodeById(ScaleRequ.ScaleParameterType_SysNick.replace('_organ_system', '')).loaded = true;
																	varTree.getNodeById(ScaleRequ.ScaleParameterType_SysNick.replace('_organ_system', '')).expand();
																	varTree.getLoader().load(
																		varTree.getNodeById(ScaleRequ.ScaleParameterType_SysNick),
																		function() {
																			varTree.getNodeById(ScaleRequ.ScaleParameterType_SysNick).loaded = true;
																			varTree.getNodeById(ScaleRequ.ScaleParameterType_SysNick).expand();
																			varTree.getNodeById(ScaleRequ.ScaleParameterType_SysNick+'_'+ScaleRequ.ScaleParameterResult_id).select();
																			win.findById('swERPEW_apache_ScalePanel').correction = parseFloat(varTree.getNodeById(ScaleRequ.ScaleParameterType_SysNick+'_'+ScaleRequ.ScaleParameterResult_id).attributes.ScaleParameterResult_Value);
																			win.findById('swERPEW_apache_ScalePanel').overall_results();
																		}
																	);
																}
															);								
														}		
													);
												}
											}
										);
										
									}
									
								},
								//Шкала WATERLOW 
								{							
									layout:'form',
									id:'swERPEW_waterlow_ScalePanel',
									border:true,
									style: 'border-top: 1px solid #ffffff; border-left: 1px solid  #ffffff; border-bottom: 1px solid #99bbe8;border-right: 1px solid #99bbe8; padding: 2px;  ',
									items:[	
										
										
										{							
											layout:'column',
											items:[	
												//панель - ПОЛ   //BOB - 27.08.2019	  
												new sw.Promed.SwScaleParameter({
													nam_begin: 'swERPEW',
													scale_name: 'waterlow',
													parameter_name: 'sex',
													text_anchor: '98%',
													lbl_text: 'Пол',
													text_width: 290,
													combo_width: 310,
													value_width: 10,
													win: this
												}),  
												//панель - ВОЗРАСТ    //BOB - 27.08.2019	  
												new sw.Promed.SwScaleParameter({
													nam_begin: 'swERPEW',
													scale_name: 'waterlow',
													parameter_name: 'age',
													text_anchor: '98%',
													lbl_text: 'Возраст',
													text_width: 290,
													combo_width: 310,
													value_width: 10,
													win: this
												}),   
												//панель - Индекс массы тела   //BOB - 27.08.2019	  
												new sw.Promed.SwScaleParameter({
													nam_begin: 'swERPEW',
													scale_name: 'waterlow',
													parameter_name: 'mass_index',
													text_anchor: '98%',
													lbl_text: 'Индекс массы тела',
													text_width: 290,
													combo_width: 310,
													value_width: 10,
													win: this
												})     
											]
										},
										{							
											layout:'column',
											items:[	
												//панель - Состояние кожи   //BOB - 27.08.2019	  
												new sw.Promed.SwScaleParameter({
													nam_begin: 'swERPEW',
													scale_name: 'waterlow',
													parameter_name: 'skin_stat',
													text_anchor: '98%',
													lbl_text: 'Состояние кожи',
													text_width: 310,
													combo_width: 330,
													value_width: 10,
													win: this
												}),       
												//панель - Особые факторы риска     //BOB - 27.08.2019	  
												new sw.Promed.SwScaleParameter({
													nam_begin: 'swERPEW',
													scale_name: 'waterlow',
													parameter_name: 'special_risk',
													text_anchor: '98%',
													lbl_text: 'Особые факторы риска',
													text_width: 340,
													combo_width: 360,
													value_width: 10,
													win: this
												}),       
												//панель - Недержание     //BOB - 27.08.2019	  
												new sw.Promed.SwScaleParameter({
													nam_begin: 'swERPEW',
													scale_name: 'waterlow',
													parameter_name: 'incontinence',
													text_anchor: '98%',
													lbl_text: 'Недержание',
													text_width: 290,
													combo_width: 310,
													value_width: 10,
													win: this
												})       
											]
										},
										{							
											layout:'column',
											items:[	
												//панель - Подвижность  //BOB - 27.08.2019	  
												new sw.Promed.SwScaleParameter({
													nam_begin: 'swERPEW',
													scale_name: 'waterlow',
													parameter_name: 'mobility',
													text_anchor: '98%',
													lbl_text: 'Подвижность',
													text_width: 290,
													combo_width: 310,
													value_width: 10,
													win: this
												}),      
												//панель - Аппетит и способ принятия пищи  //BOB - 27.08.2019	  
												new sw.Promed.SwScaleParameter({
													nam_begin: 'swERPEW',
													scale_name: 'waterlow',
													parameter_name: 'appetite',
													text_anchor: '98%',
													lbl_text: 'Аппетит и способ принятия пищи',
													text_width: 290,
													combo_width: 310,
													value_width: 10,
													win: this
												}),       
												//панель - Расстройства неврологического характера  //BOB - 27.08.2019	  
												new sw.Promed.SwScaleParameter({
													nam_begin: 'swERPEW',
													scale_name: 'waterlow',
													parameter_name: 'neurologic',
													text_anchor: '98%',
													lbl_text: 'Расстройства неврологического характера',
													text_width: 290,
													combo_width: 310,
													value_width: 10,
													win: this
												})    
											]
										},
										{							
											layout:'column',
											items:[	
												//панель - Оперативное вмешательство  //BOB - 27.08.2019	  
												new sw.Promed.SwScaleParameter({
													nam_begin: 'swERPEW',
													scale_name: 'waterlow',
													parameter_name: 'surgery',
													text_anchor: '98%',
													lbl_text: 'Оперативное вмешательство',
													text_width: 330,
													combo_width: 350,
													value_width: 10,
													win: this
												}) ,   
												//панель - Лекарственная терапия     //BOB - 27.08.2019	  
												new sw.Promed.SwScaleParameter({
													nam_begin: 'swERPEW',
													scale_name: 'waterlow',
													parameter_name: 'drug',
													text_anchor: '98%',
													lbl_text: 'Лекарственная терапия',
													text_width: 290,
													combo_width: 310,
													value_width: 10,
													win: this
												})  
											]
										}
									],

									//функция расчёта общего итогов
									overall_results: function(object_id){

										var sex_Val = parseInt(this.findById('swERPEW_waterlow_sex_Val').text);
										var age_Val = parseInt(this.findById('swERPEW_waterlow_age_Val').text);
										var mass_index_Val = parseInt(this.findById('swERPEW_waterlow_mass_index_Val').text);
										var skin_stat_Val = parseInt(this.findById('swERPEW_waterlow_skin_stat_Val').text);
										var special_risk_Val = parseInt(this.findById('swERPEW_waterlow_special_risk_Val').text);
										var incontinence_Val = parseInt(this.findById('swERPEW_waterlow_incontinence_Val').text);
										var mobility_Val = parseInt(this.findById('swERPEW_waterlow_mobility_Val').text);
										var appetite_Val = parseInt(this.findById('swERPEW_waterlow_appetite_Val').text);
										var neurologic_Val = parseInt(this.findById('swERPEW_waterlow_neurologic_Val').text);
										var surgery_Val = parseInt(this.findById('swERPEW_waterlow_surgery_Val').text);
										var drug_Val = parseInt(this.findById('swERPEW_waterlow_drug_Val').text);
										
										var sum = sex_Val + age_Val + mass_index_Val + skin_stat_Val + special_risk_Val + incontinence_Val + mobility_Val + appetite_Val + neurologic_Val + surgery_Val + drug_Val;
										win.findById('swERPEW_EvnScaleResult').setText(sum);
										
										//формирование строки	Традиционной классификации , по данной шкале пока нету
										win.findById('swERPEW_EvnScaleResultText').setText('');
										
										if ((this.findById('swERPEW_waterlow_sex').value) &&
										(this.findById('swERPEW_waterlow_age').value) &&
										(this.findById('swERPEW_waterlow_mass_index').value) &&
										(this.findById('swERPEW_waterlow_skin_stat').value) &&
										(this.findById('swERPEW_waterlow_special_risk').value) &&
										(this.findById('swERPEW_waterlow_incontinence').value) &&
										(this.findById('swERPEW_waterlow_mobility').value) &&
										(this.findById('swERPEW_waterlow_appetite').value) &&
										(this.findById('swERPEW_waterlow_neurologic').value) &&
										(this.findById('swERPEW_waterlow_surgery').value) &&
										(this.findById('swERPEW_waterlow_drug').value)) {
											if (sum === 0)
												win.findById('swERPEW_EvnScaleResultText').setText('');
											else if (sum <= 9)
												win.findById('swERPEW_EvnScaleResultText').setText('отсутствие риска пролежней');
											else if (sum <= 14)
												win.findById('swERPEW_EvnScaleResultText').setText('существует риск пролежней');
											else if (sum <= 19)
												win.findById('swERPEW_EvnScaleResultText').setText('риск развития пролежней - высокий');
											else 
												win.findById('swERPEW_EvnScaleResultText').setText('наивысшая степень риска развития пролежней');										
										}
										
										//заполнение строки грида
										var EvnScalesGridRow = win.findById('swERPEW_EvnScales_Grid').getSelectionModel().getSelected();  //выбранная строка в гриде событий шкал
										//если запись в гриде новая
										if (EvnScalesGridRow.data['EvnScale_id'] == 'New_GUID_Id'){
											//	console.log('BOB_record=',record); 
											EvnScalesGridRow.data['EvnScale_Result'] = win.findById('swERPEW_EvnScaleResult').text;
											EvnScalesGridRow.data['EvnScale_ResultTradic'] = win.findById('swERPEW_EvnScaleResultText').text;																	
											EvnScalesGridRow.commit();			
										}

										//проверка возраста  //BOB - 23.10.2019
										//console.log('BOB_object_id=',object_id);
										if((object_id == 'swERPEW_waterlow_age') && (EvnScalesGridRow.data['EvnScale_id'] == 'New_GUID_Id')){
											var index = win.findById('swERPEW_waterlow_age').getStore().find('ScaleParameterResult_id',win.findById('swERPEW_waterlow_age').value);//нахожу индекс в store комбо возраста
											if (index > -1){
												var ageInterval = win.findById('swERPEW_waterlow_age').getStore().getAt(index).data.ScaleParameterResult_Name;  // интервал возрастов
												var age = win.getAge(   win.pers_data.Person_Birthday.date, 'amer');
												if ((ageInterval == '< 14') && (age >= 14))
													Ext.MessageBox.alert('Предупреждение!', 'Выбранный интервал не соответствует возрасту пациента!');
												if ((ageInterval == '14 - 49') && ((age < 14) || (age > 49)))
													Ext.MessageBox.alert('Предупреждение!', 'Выбранный интервал не соответствует возрасту пациента!');
												if ((ageInterval == '50 - 64') && ((age < 50)  || (age > 64)))
													Ext.MessageBox.alert('Предупреждение!', 'Выбранный интервал не соответствует возрасту пациента!');
												if ((ageInterval == '65 - 74') && ((age < 65)  || (age > 74)))
													Ext.MessageBox.alert('Предупреждение!', 'Выбранный интервал не соответствует возрасту пациента!');
												if ((ageInterval == '75 - 80') && ((age < 75)  || (age > 80)))
													Ext.MessageBox.alert('Предупреждение!', 'Выбранный интервал не соответствует возрасту пациента!');
												if ((ageInterval == '81 +') && (age < 81))
													Ext.MessageBox.alert('Предупреждение!', 'Выбранный интервал не соответствует возрасту пациента!');
											}
										}
										//проверка пола //BOB - 23.10.2019
										if((object_id == 'swERPEW_waterlow_sex') && (EvnScalesGridRow.data['EvnScale_id'] == 'New_GUID_Id')) {
											var index = win.findById('swERPEW_waterlow_sex').getStore().find('ScaleParameterResult_id',win.findById('swERPEW_waterlow_sex').value);//нахожу индекс в store комбо возраста
											if (index > -1){
												var ageInterval = win.findById('swERPEW_waterlow_sex').getStore().getAt(index).data.ScaleParameterResult_Value;  // код пола, случайно совпадающий с баллами, чем я и воспользовался
												if (ageInterval != win.pers_data.Sex_id) {
													Ext.MessageBox.alert('Предупреждение!', 'Выбранный вариант не соответствует полу пациента!');
												}
											}
										}
										//проверка индекса массы тела //BOB - 23.10.2019
										if((object_id == 'swERPEW_waterlow_mass_index') && (EvnScalesGridRow.data['EvnScale_id'] == 'New_GUID_Id')) {
											var index = win.findById('swERPEW_waterlow_mass_index').getStore().find('ScaleParameterResult_id',win.findById('swERPEW_waterlow_mass_index').value);//нахожу индекс в store комбо возраста
											var age = parseFloat(win.findById('swERPEW_RC_IMT').getValue());
											if ((index > -1)&&(!Number.isNaN(age)))   {
												var ageInterval = win.findById('swERPEW_waterlow_mass_index').getStore().getAt(index).data.ScaleParameterResult_Name;  // интервал возрастов
												if ((ageInterval == 'Средний – 20-24.9') && ((age < 20)||(age >= 25)) )
													Ext.MessageBox.alert('Предупреждение!', 'Выбранный интервал не соответствует индексу массы тела пациента!');
												if ((ageInterval == 'Выше среднего –25-29.9') && ((age < 25) || (age >= 30)))
													Ext.MessageBox.alert('Предупреждение!', 'Выбранный интервал не соответствует индексу массы тела пациента!');
												if ((ageInterval == 'Ожирение – > 30') && (age < 30))
													Ext.MessageBox.alert('Предупреждение!', 'Выбранный интервал не соответствует индексу массы тела пациента!');
												if ((ageInterval == 'Ниже среднего – < 20') && (age >= 20))
													Ext.MessageBox.alert('Предупреждение!', 'Выбранный интервал не соответствует индексу массы тела пациента!');
											}
										}


									}
									
								},								
								//Шкала RASS 
								{							
									layout:'form',
									id:'swERPEW_rass_ScalePanel',
									border:true,
									style: 'border-top: 1px solid #ffffff; border-left: 1px solid  #ffffff; border-bottom: 1px solid #99bbe8;border-right: 1px solid #99bbe8; padding: 2px;  ',
									items:[	
										{							
											layout:'column',
											items:[	
												//панель - Возбуждение-седация  //BOB - 27.08.2019	  
												new sw.Promed.SwScaleParameter({
													nam_begin: 'swERPEW',
													scale_name: 'rass',
													parameter_name: 'rass',
													text_anchor: '98%',
													lbl_text: 'Возбуждение-седация',
													text_width: 320,
													combo_width: 350,
													value_width: 20,
													win: this
												})    
											]
										}
									],
									//функция расчёта общего итогов
									overall_results: function(object_id){

										var sum = parseInt(this.findById('swERPEW_rass_rass_Val').text);
										win.findById('swERPEW_EvnScaleResult').setText(sum);
										
										//формирование строки	Традиционной классификации , по данной шкале пока нету
										win.findById('swERPEW_EvnScaleResultText').setText('');
										
										if (this.findById('swERPEW_rass_rass').value) {
											var index = this.findById('swERPEW_rass_rass').getStore().find('ScaleParameterResult_id', this.findById('swERPEW_rass_rass').value);//нахожу индекс в store комбо 
											win.findById('swERPEW_EvnScaleResultText').setText(this.findById('swERPEW_rass_rass').getStore().getAt(index).data.ScaleParameterResult_Name);
										}
										
										//заполнение строки грида
										var EvnScalesGridRow = win.findById('swERPEW_EvnScales_Grid').getSelectionModel().getSelected();  //выбранная строка в гриде событий шкал
										//если запись в гриде новая
										if (EvnScalesGridRow.data['EvnScale_id'] == 'New_GUID_Id'){
											//	console.log('BOB_record=',record); 
											EvnScalesGridRow.data['EvnScale_Result'] = win.findById('swERPEW_EvnScaleResult').text;
											EvnScalesGridRow.data['EvnScale_ResultTradic'] = win.findById('swERPEW_EvnScaleResultText').text;																	
											EvnScalesGridRow.commit();			
										}
									}
									
								},								
								//Шкала hunt_hess 
								{							
									layout:'form',
									id:'swERPEW_hunt_hess_ScalePanel',
									border:true,
									style: 'border-top: 1px solid #ffffff; border-left: 1px solid  #ffffff; border-bottom: 1px solid #99bbe8;border-right: 1px solid #99bbe8; padding: 2px;  ',
									items:[	
										{							
											layout:'column',
											items:[	
												//панель - Тяжесть больных //BOB - 27.08.2019	  
												new sw.Promed.SwScaleParameter({
													nam_begin: 'swERPEW',
													scale_name: 'hunt_hess',
													parameter_name: 'hunt_hess',
													text_anchor: '98%',
													lbl_text: 'Тяжесть больных',
													text_width: 1100,
													combo_width: 1150,
													value_width: 20,
													win: this
												})      
											]
										},
										{							
											layout:'column',
											labelWidth: 5,
											items:[											
												new Ext.form.CheckboxGroup({
													id:'wERPEW_hunt_hess_Dopoln',
													labelSeparator: '',
													vertical: true,
													columns: 1,
													items: [	
														{xtype: 'checkbox', boxLabel: 'Дополнительно – сопутствующая патология', name: 'hunt_hess_Dopoln', inputValue: 'hunt_hess_Dopoln', width: 300}
													],											
													listeners: {
														'change': function(field, checked) {
															var NewRow = (win.findById('swERPEW_EvnScales_Grid').getSelectionModel().getSelected().data['EvnScale_id'] == 'New_GUID_Id')
															if (NewRow) win.findById('swERPEW_hunt_hess_Dopoln_Hid').setValue(field.items.items[0].getValue());
															
															if (win.findById('swERPEW_hunt_hess_hunt_hess').value)
																win.findById('swERPEW_hunt_hess_ScalePanel').overall_results();
															else
																field.items.items[0].setValue(false);
														}
													}
												}),
												{
													id: 'swERPEW_hunt_hess_Dopoln_Hid',
													value:false,
													xtype:'hidden'
												}

											]
										}
										
									],
									//функция расчёта общего итогов
									overall_results: function(object_id){
										var sum = parseInt(this.findById('swERPEW_hunt_hess_hunt_hess_Val').text) + (win.findById('wERPEW_hunt_hess_Dopoln').items.items[0].checked ? 1 : 0) ;
										win.findById('swERPEW_EvnScaleResult').setText(sum);
										
										//формирование строки	Традиционной классификации , по данной шкале пока нету
										win.findById('swERPEW_EvnScaleResultText').setText('');
										
										if (this.findById('swERPEW_hunt_hess_hunt_hess').value) {
											var TextResult = 'Выживаемость - ';
											switch (sum)  {
												case 1 : TextResult += '70%'; break;
												case 2 : TextResult += '60%'; break;
												case 3 : TextResult += '50%'; break;
												case 4 : TextResult +=  win.findById('wERPEW_hunt_hess_Dopoln').items.items[0].checked ? '40%': '20%'; break;
												default : TextResult += '10%'; break;
											}											
											win.findById('swERPEW_EvnScaleResultText').setText(TextResult);
										}
										
										//заполнение строки грида
										var EvnScalesGridRow = win.findById('swERPEW_EvnScales_Grid').getSelectionModel().getSelected();  //выбранная строка в гриде событий шкал
										//если запись в гриде новая
										if (EvnScalesGridRow.data['EvnScale_id'] == 'New_GUID_Id'){
											//	console.log('BOB_record=',record); 
											EvnScalesGridRow.data['EvnScale_Result'] = win.findById('swERPEW_EvnScaleResult').text;
											EvnScalesGridRow.data['EvnScale_ResultTradic'] = win.findById('swERPEW_EvnScaleResultText').text;																	
											EvnScalesGridRow.commit();			
										}
									}
									
								},								
								//Шкала FOUR 
								{							
									layout:'form',
									id:'swERPEW_four_ScalePanel',
									border:true,
									style: 'border-top: 1px solid #ffffff; border-left: 1px solid  #ffffff; border-bottom: 1px solid #99bbe8;border-right: 1px solid #99bbe8; padding: 2px;  ',
									items:[	
										{							
											layout:'column',
											items:[	
												//панель - Глазные реакции (E) //BOB - 27.08.2019	  
												new sw.Promed.SwScaleParameter({
													nam_begin: 'swERPEW',
													scale_name: 'four',
													parameter_name: 'eyes',
													text_anchor: '98%',
													lbl_text: 'Глазные реакции (E)',
													text_width: 420,
													combo_width: 440,
													value_width: 10,
													win: this
												}),     
												//панель - Двигательные реакции (M)  //BOB - 27.08.2019	  
												new sw.Promed.SwScaleParameter({
													nam_begin: 'swERPEW',
													scale_name: 'four',
													parameter_name: 'motor',
													text_anchor: '98%',
													lbl_text: 'Двигательные реакции (M)',
													text_width: 420,
													combo_width: 440,
													value_width: 10,
													win: this
												})   
											]
										},
										{							
											layout:'column',
											items:[	
												//панель - Стволовые рефлексы (B)  //BOB - 27.08.2019	  
												new sw.Promed.SwScaleParameter({
													nam_begin: 'swERPEW',
													scale_name: 'four',
													parameter_name: 'stem_reflex',
													text_anchor: '98%',
													lbl_text: 'Стволовые рефлексы (B)',
													text_width: 420,
													combo_width: 440,
													value_width: 10,
													win: this
												}),    
												//панель - Дыхательные паттерны (R)   //BOB - 27.08.2019	  
												new sw.Promed.SwScaleParameter({
													nam_begin: 'swERPEW',
													scale_name: 'four',
													parameter_name: 'breath_patt',
													text_anchor: '98%',
													lbl_text: 'Дыхательные паттерны (R)',
													text_width: 420,
													combo_width: 440,
													value_width: 10,
													win: this
												})      
											]
										}																
									],
									
									//функция расчёта общего итогов
									overall_results: function(object_id){

										var eyes_Val = parseInt(this.findById('swERPEW_four_eyes_Val').text);
										var motor_Val = parseInt(this.findById('swERPEW_four_motor_Val').text);
										var stem_reflex_Val = parseInt(this.findById('swERPEW_four_stem_reflex_Val').text);
										var breath_patt_Val = parseInt(this.findById('swERPEW_four_breath_patt_Val').text);
										
										var sum = eyes_Val + motor_Val + stem_reflex_Val + breath_patt_Val;
										win.findById('swERPEW_EvnScaleResult').setText(sum);
										
										//формирование строки	Традиционной классификации , по данной шкале пока нету
										win.findById('swERPEW_EvnScaleResultText').setText('');
										
										if ((this.findById('swERPEW_four_eyes').value) &&
										(this.findById('swERPEW_four_motor').value) &&
										(this.findById('swERPEW_four_stem_reflex').value) &&
										(this.findById('swERPEW_four_breath_patt').value)) {
											if (sum === 0)
												win.findById('swERPEW_EvnScaleResultText').setText('Кома III, гибель коры');
											else if (sum <= 6)
												win.findById('swERPEW_EvnScaleResultText').setText('Кома II');
											else if (sum <= 8)
												win.findById('swERPEW_EvnScaleResultText').setText('Кома I');
											else if (sum <= 12)
												win.findById('swERPEW_EvnScaleResultText').setText('Сопор');
											else if (sum <= 14)
												win.findById('swERPEW_EvnScaleResultText').setText('Глубокое оглушение');
											else if (sum === 15)
												win.findById('swERPEW_EvnScaleResultText').setText('Умеренное оглушение');
											else 
												win.findById('swERPEW_EvnScaleResultText').setText('Ясное сознание');										
										}
										
										//заполнение строки грида
										var EvnScalesGridRow = win.findById('swERPEW_EvnScales_Grid').getSelectionModel().getSelected();  //выбранная строка в гриде событий шкал
										//если запись в гриде новая
										if (EvnScalesGridRow.data['EvnScale_id'] == 'New_GUID_Id'){
											//	console.log('BOB_record=',record); 
											EvnScalesGridRow.data['EvnScale_Result'] = win.findById('swERPEW_EvnScaleResult').text;
											EvnScalesGridRow.data['EvnScale_ResultTradic'] = win.findById('swERPEW_EvnScaleResultText').text;																	
											EvnScalesGridRow.commit();			
										}
									}									
								},								
								//Шкала MRC 
								{							
									layout:'form',
									id:'swERPEW_mrc_ScalePanel',
									border:true,
									style: 'border-top: 1px solid #ffffff; border-left: 1px solid  #ffffff; border-bottom: 1px solid #99bbe8;border-right: 1px solid #99bbe8; padding: 2px;  ',
									items:[	
										{							
											layout:'column',
											items:[	
												//панель - Мышечная активность  //BOB - 27.08.2019	  
												new sw.Promed.SwScaleParameter({
													nam_begin: 'swERPEW',
													scale_name: 'mrc',
													parameter_name: 'mrc',
													text_anchor: '98%',
													lbl_text: 'Мышечная активность',
													text_width: 800,
													combo_width: 850,
													value_width: 20,
													win: this
												})     
											]
										}
									],
									//функция расчёта общего итогов
									overall_results: function(object_id){

										var sum = parseInt(this.findById('swERPEW_mrc_mrc_Val').text);
										win.findById('swERPEW_EvnScaleResult').setText(sum);
										
										//формирование строки	Традиционной классификации , по данной шкале пока нету
										win.findById('swERPEW_EvnScaleResultText').setText('');
										
										if (this.findById('swERPEW_mrc_mrc').value) {
											var index = this.findById('swERPEW_mrc_mrc').getStore().find('ScaleParameterResult_id', this.findById('swERPEW_mrc_mrc').value);//нахожу индекс в store комбо 
											win.findById('swERPEW_EvnScaleResultText').setText(this.findById('swERPEW_mrc_mrc').getStore().getAt(index).data.ScaleParameterResult_Name);
										}
										
										//заполнение строки грида
										var EvnScalesGridRow = win.findById('swERPEW_EvnScales_Grid').getSelectionModel().getSelected();  //выбранная строка в гриде событий шкал
										//если запись в гриде новая
										if (EvnScalesGridRow.data['EvnScale_id'] == 'New_GUID_Id'){
											//	console.log('BOB_record=',record); 
											EvnScalesGridRow.data['EvnScale_Result'] = win.findById('swERPEW_EvnScaleResult').text;
											EvnScalesGridRow.data['EvnScale_ResultTradic'] = win.findById('swERPEW_EvnScaleResultText').text;																	
											EvnScalesGridRow.commit();			
										}
									}
									
								},
								//Шкала ВАШ 
								{							
									layout:'form',
									id:'swERPEW_VAScale_ScalePanel',
									border:true,
									style: 'border-top: 1px solid #ffffff; border-left: 1px solid  #ffffff; border-bottom: 1px solid #99bbe8;border-right: 1px solid #99bbe8; padding: 2px;  ',
									items:[	
										{
											id: 'swERPEW_VAScale_VAScale_Hid',
											value:0,
											xtype:'hidden'
										},
										new Ext.form.RadioGroup({
											id:'swERPEW_VAScale_VAScale',
											labelSeparator: '',
											vertical: true,
											columns: 11,
											items: [												
												{									
													layout:'form',
													width: 20,
													items:[														
														{name: 'VAScale', inputValue: 0},
														{xtype:'label',text: '0б'}													
													]
												},
												{									
													layout:'form',
													width: 20,
													style:'margin-left: 10px;',
													items:[														
														{name: 'VAScale', inputValue: 1},
														{xtype:'label',text: '1б'}													
													]
												},
												{									
													layout:'form',
													width: 20,
													style:'margin-left: 10px;',
													items:[														
														{name: 'VAScale', inputValue: 2},
														{xtype:'label',text: '2б'}					
													]
												},
												{									
													layout:'form',
													width: 20,
													style:'margin-left: 10px;',
													items:[														
														{name: 'VAScale', inputValue: 3, width: 20},
														{xtype:'label',text: '3б'}					
													]
												},
												{									
													layout:'form',
													width: 20,
													style:'margin-left: 10px;',
													items:[														
														{name: 'VAScale', inputValue: 4, width: 20},
														{xtype:'label',text: '4б'}					
													]
												},
												{									
													layout:'form',
													width: 20,
													style:'margin-left: 10px;',
													items:[														
														{name: 'VAScale', inputValue: 5, width: 20},
														{xtype:'label',text: '5б'}					
													]
												},
												{									
													layout:'form',
													width: 20,
													style:'margin-left: 10px;',
													items:[														
														{name: 'VAScale', inputValue: 6, width: 20},
														{xtype:'label',text: '6б'}					
													]
												},
												{									
													layout:'form',
													width: 20,
													style:'margin-left: 10px;',
													items:[														
														{name: 'VAScale', inputValue: 7, width: 20},
														{xtype:'label',text: '7б'}					
													]
												},
												{									
													layout:'form',
													width: 20,
													style:'margin-left: 10px;',
													items:[														
														{name: 'VAScale', inputValue: 8, width: 20},
														{xtype:'label',text: '8б'}					
													]
												},
												{									
													layout:'form',
													width: 20,
													style:'margin-left: 10px;',
													items:[														
														{name: 'VAScale', inputValue: 9, width: 20},
														{xtype:'label',text: '9б'}					
													]
												},
												{									
													layout:'form',
													width: 20,
													style:'margin-left: 10px;',
													items:[														
														{name: 'VAScale', inputValue: 10, width: 20},
														{xtype:'label',text: '10б'}					
													]
												}
											],
											listeners: {
												'change': function(field, checked) {
													
													if(checked) {
														var NewRow = (win.findById('swERPEW_EvnScales_Grid').getSelectionModel().getSelected().data['EvnScale_id'] == 'New_GUID_Id')
														if (NewRow) win.findById('swERPEW_VAScale_VAScale_Hid').setValue(checked.inputValue);
													}
													win.findById('swERPEW_VAScale_ScalePanel').overall_results();
												}
											}
										})	
									],
									overall_results: function(object_id){
										
										var checked = null;
										
										for (var j in win.findById('swERPEW_VAScale_VAScale').items.items  ){
											if (win.findById('swERPEW_VAScale_VAScale').items.items[j].checked)
												checked = win.findById('swERPEW_VAScale_VAScale').items.items[j];
										}
										console.log('BOB_checked=',checked);
										
										
										if (checked) {
											
											win.findById('swERPEW_EvnScaleResult').setText(checked.inputValue);

											//формирование строки	Традиционной классификации , по данной шкале пока нету
											win.findById('swERPEW_EvnScaleResultText').setText('');


											if (checked.inputValue === 0)
												win.findById('swERPEW_EvnScaleResultText').setText(checked.inputValue + ' баллов');
											else if (checked.inputValue === 1)
												win.findById('swERPEW_EvnScaleResultText').setText(checked.inputValue + ' балл');
											else if (checked.inputValue <= 4)
												win.findById('swERPEW_EvnScaleResultText').setText(checked.inputValue + ' балла');
											else 
												win.findById('swERPEW_EvnScaleResultText').setText(checked.inputValue + ' баллов');										




											//заполнение строки грида
											var EvnScalesGridRow = win.findById('swERPEW_EvnScales_Grid').getSelectionModel().getSelected();  //выбранная строка в гриде событий шкал
											//если запись в гриде новая
											if (EvnScalesGridRow.data['EvnScale_id'] == 'New_GUID_Id'){
												//	console.log('BOB_record=',record); 
												EvnScalesGridRow.data['EvnScale_Result'] = win.findById('swERPEW_EvnScaleResult').text;
												EvnScalesGridRow.data['EvnScale_ResultTradic'] = win.findById('swERPEW_EvnScaleResultText').text;																	
												EvnScalesGridRow.commit();			
											}
										}
									}
									
									
								},
								//Шкала NIHSS
								{							
									layout:'form',
									id:'swERPEW_nihss_ScalePanel',
									border:true,
									style: 'border-top: 1px solid #ffffff; border-left: 1px solid  #ffffff; border-bottom: 1px solid #99bbe8;border-right: 1px solid #99bbe8; padding: 2px;  ',
									items:[	
										
										{							
											layout:'column',
											items:[	
												//панель - Уровень сознания 1А //BOB - 27.08.2019	  
												new sw.Promed.SwScaleParameter({
													nam_begin: 'swERPEW',
													scale_name: 'nihss',
													parameter_name: 'level_consciousness_1A',
													text_anchor: '98%',
													lbl_text: 'Уровень сознания 1А',
													text_width: 290,
													combo_width: 310,
													value_width: 10,
													win: this
												}),     
												//панель - Уровень сознания 1Б //BOB - 27.08.2019	  
												new sw.Promed.SwScaleParameter({
													nam_begin: 'swERPEW',
													scale_name: 'nihss',
													parameter_name: 'level_consciousness_1B',
													text_anchor: '98%',
													lbl_text: 'Уровень сознания 1Б',
													text_width: 290,
													combo_width: 310,
													value_width: 10,
													win: this
												}),     
												//панель - Уровень сознания 1С //BOB - 27.08.2019	  
												new sw.Promed.SwScaleParameter({
													nam_begin: 'swERPEW',
													scale_name: 'nihss',
													parameter_name: 'level_consciousness_1C',
													text_anchor: '98%',
													lbl_text: 'Уровень сознания 1С',
													text_width: 290,
													combo_width: 310,
													value_width: 10,
													win: this
												})     
											]
										},
										{							
											layout:'column',
											items:[	
												//панель - Движения глазных яблок   //BOB - 27.08.2019	  
												new sw.Promed.SwScaleParameter({
													nam_begin: 'swERPEW',
													scale_name: 'nihss',
													parameter_name: 'move_eyeballs',
													text_anchor: '98%',
													lbl_text: 'Движения глазных яблок',
													text_width: 290,
													combo_width: 310,
													value_width: 10,
													win: this
												}), 
												//панель -  Поля зрения  //BOB - 27.08.2019	  
												new sw.Promed.SwScaleParameter({
													nam_begin: 'swERPEW',
													scale_name: 'nihss',
													parameter_name: 'fields_view',
													text_anchor: '98%',
													lbl_text: 'Поля зрения',
													text_width: 290,
													combo_width: 310,
													value_width: 10,
													win: this
												}),   
												//панель -  Функция лицевого нерва    //BOB - 27.08.2019	  
												new sw.Promed.SwScaleParameter({
													nam_begin: 'swERPEW',
													scale_name: 'nihss',
													parameter_name: 'func_facial_nerve',
													text_anchor: '98%',
													lbl_text: 'Функция лицевого нерва',
													text_width: 290,
													combo_width: 310,
													value_width: 10,
													win: this
												})   
											]
										},
										{							
											layout:'column',
											items:[	
												//панель - Сила мышц - левая рука    //BOB - 27.08.2019	  
												new sw.Promed.SwScaleParameter({
													nam_begin: 'swERPEW',
													scale_name: 'nihss',
													parameter_name: 'strength_muscles_left_arm',
													text_anchor: '98%',
													lbl_text: 'Сила мышц - левая рука',
													text_width: 290,
													combo_width: 320,
													value_width: 10,
													win: this
												}) ,
												//панель -  Сила мышц - правая рука   //BOB - 27.08.2019	  
												new sw.Promed.SwScaleParameter({
													nam_begin: 'swERPEW',
													scale_name: 'nihss',
													parameter_name: 'strength_muscles_right_arm',
													text_anchor: '98%',
													lbl_text: 'Сила мышц - правая рука',
													text_width: 290,
													combo_width: 318,
													value_width: 10,
													win: this
												}) ,  
												//панель -  Сила мышц - левая нога   //BOB - 27.08.2019	  
												new sw.Promed.SwScaleParameter({
													nam_begin: 'swERPEW',
													scale_name: 'nihss',
													parameter_name: 'strength_muscles_left_leg',
													text_anchor: '98%',
													lbl_text: 'Сила мышц - левая нога',
													text_width: 290,
													combo_width: 318,
													value_width: 10,
													win: this
												}) ,   
												//панель -  Сила мышц - правая нога    //BOB - 27.08.2019	  
												new sw.Promed.SwScaleParameter({
													nam_begin: 'swERPEW',
													scale_name: 'nihss',
													parameter_name: 'strength_muscles_right_leg',
													text_anchor: '98%',
													lbl_text: 'Сила мышц - правая нога',
													text_width: 290,
													combo_width: 318,
													value_width: 10,
													win: this
												})     
											]
										},
										{							
											layout:'column',
											items:[	
												//панель - Атаксия конечности   //BOB - 27.08.2019	  
												new sw.Promed.SwScaleParameter({
													nam_begin: 'swERPEW',
													scale_name: 'nihss',
													parameter_name: 'ataxia_limb',
													text_anchor: '98%',
													lbl_text: 'Атаксия конечности',
													text_width: 290,
													combo_width: 310,
													value_width: 10,
													win: this
												})  ,  
												//панель -  Чувствительность  //BOB - 27.08.2019	  
												new sw.Promed.SwScaleParameter({
													nam_begin: 'swERPEW',
													scale_name: 'nihss',
													parameter_name: 'sensitivity',
													text_anchor: '98%',
													lbl_text: 'Чувствительность',
													text_width: 290,
													combo_width: 310,
													value_width: 10,
													win: this
												})       
											]
										},
										{							
											layout:'column',
											items:[	
												//панель - Речь  //BOB - 27.08.2019	  
												new sw.Promed.SwScaleParameter({
													nam_begin: 'swERPEW',
													scale_name: 'nihss',
													parameter_name: 'speech',
													text_anchor: '98%',
													lbl_text: 'Речь',
													text_width: 290,
													combo_width: 310,
													value_width: 10,
													win: this
												}) ,    
												//панель -  Дизартрия   //BOB - 27.08.2019	  
												new sw.Promed.SwScaleParameter({
													nam_begin: 'swERPEW',
													scale_name: 'nihss',
													parameter_name: 'dysatria',
													text_anchor: '98%',
													lbl_text: 'Дизартрия',
													text_width: 290,
													combo_width: 310,
													value_width: 10,
													win: this
												}) ,    
												//панель -  Игнорирование //BOB - 27.08.2019	  
												new sw.Promed.SwScaleParameter({
													nam_begin: 'swERPEW',
													scale_name: 'nihss',
													parameter_name: 'ignoring',
													text_anchor: '98%',
													lbl_text: 'Игнорирование',
													text_width: 290,
													combo_width: 310,
													value_width: 10,
													win: this
												})     
											]
										}										
									],
									//функция расчёта общего итогов
									overall_results: function(object_id){
										
										
										var sum = 0;
										Ext.select('label[id$="Val"]', true, 'swERPEW_nihss_ScalePanel').each(function(el){
											var id = el.id; //выделяю параметр id из Ext.Element
											var object = win.findById(id);	//ищу в окне объект ExtJS
											if(object){ // если нахожу, то 
												sum += 	parseInt(object.text);
											}
										});	
										
										win.findById('swERPEW_EvnScaleResult').setText(sum);
										// контролирую все ли параметры выбраны
										var toDo = true;
										var ParamCombos = win.findById('swERPEW_nihss_ScalePanel').find('xtype', 'combo');
										for (var i in ParamCombos){
											if(ParamCombos[i].id) {
												//console.log('BOB_ParamCombos[i].getValue()=',ParamCombos[i].getValue());
												if ((ParamCombos[i].getValue() == null) || (ParamCombos[i].getValue() == '')) {
													toDo = false;
													break;
												}	
											}

										}
										//формирование строки	Традиционной классификации , по данной шкале пока нету
										if (toDo) {
											
											win.findById('swERPEW_EvnScaleResultText').setText('');
											if (sum === 0)
												win.findById('swERPEW_EvnScaleResultText').setText('Нет симптомов инсульта');
											else if (sum < 5)
												win.findById('swERPEW_EvnScaleResultText').setText('Легкой степени тяжести');
											else if (sum < 16)
												win.findById('swERPEW_EvnScaleResultText').setText('Средней степени тяжести');
											else if (sum < 21)
												win.findById('swERPEW_EvnScaleResultText').setText('Тяжелый инсульт');
											else 
												win.findById('swERPEW_EvnScaleResultText').setText('Крайне тяжелый инсульт');
										}
										else
											win.findById('swERPEW_EvnScaleResultText').setText('');
										
										//заполнение строки грида
										var EvnScalesGridRow = win.findById('swERPEW_EvnScales_Grid').getSelectionModel().getSelected();  //выбранная строка в гриде событий шкал
										//если запись в гриде новая
										if (EvnScalesGridRow.data['EvnScale_id'] == 'New_GUID_Id'){
											//	console.log('BOB_record=',record); 
											EvnScalesGridRow.data['EvnScale_Result'] = win.findById('swERPEW_EvnScaleResult').text;
											EvnScalesGridRow.data['EvnScale_ResultTradic'] = win.findById('swERPEW_EvnScaleResultText').text;																	
											EvnScalesGridRow.commit();			
										}
									}
								},
								//Шкала glasgow_neonat  //BOB - 20.02.2020
								{							
									layout:'column',
									id:'swERPEW_glasgow_neonat_ScalePanel',
									border:true,
									style: 'border-top: 1px solid #ffffff; border-left: 1px solid  #ffffff; border-bottom: 1px solid #99bbe8;border-right: 1px solid #99bbe8; padding: 2px;  ',
									items:[	
										
										//панель - Открывание глаз    
										//BOB - 20.02.2020	
										{
											xtype: 'swscaleparameter',
											nam_begin: 'swERPEW',
											scale_name: 'glasgow_neonat',
											parameter_name: 'eye_response',
											text_anchor: '95%',
											lbl_text: 'Открывание глаз',
											text_width: 140,
											combo_width: 160,
											value_width: 10,
											win: this
										},
										//панель - Вербальные реакции  //BOB - 20.02.2020 
										new sw.Promed.SwScaleParameter({
											nam_begin: 'swERPEW',
											scale_name: 'glasgow_neonat',
											parameter_name: 'verbal_response',
											text_anchor: '98%',
											lbl_text: 'Вербальные реакции',
											text_width: 460,
											combo_width: 480,
											value_width: 10,
											win: this
										}),  
										//панель - Моторные реакции    //BOB - 20.02.2020 
										new sw.Promed.SwScaleParameter({
											nam_begin: 'swERPEW',
											scale_name: 'glasgow_neonat',
											parameter_name: 'motor_response',
											text_anchor: '98%',
											lbl_text: 'Моторные реакции',
											text_width: 460,
											combo_width: 480,
											value_width: 10,
											win: this
										})
									],
									//функция расчёта общего итогов
									overall_results: function(object_id){
										var eye_response_Val = parseInt(this.findById('swERPEW_glasgow_neonat_eye_response_Val').text);
									//	console.log('BOB_eye_response_Val=',eye_response_Val);  
										var verbal_response_Val = parseInt(this.findById('swERPEW_glasgow_neonat_verbal_response_Val').text);
										var motor_response_Val = parseInt(this.findById('swERPEW_glasgow_neonat_motor_response_Val').text);
										var sum = eye_response_Val + verbal_response_Val + motor_response_Val;
										win.findById('swERPEW_EvnScaleResult').setText(sum);
										
										if ((sum === 0) || (eye_response_Val === 0) || (verbal_response_Val === 0) || (motor_response_Val === 0))
											win.findById('swERPEW_EvnScaleResultText').setText('');
										else if (sum <= 3)
											win.findById('swERPEW_EvnScaleResultText').setText('смерть мозга');
										else if (sum <= 8)
											win.findById('swERPEW_EvnScaleResultText').setText('кома');
										else if (sum <= 12)
											win.findById('swERPEW_EvnScaleResultText').setText('сопор');
										else if (sum <= 14)
											win.findById('swERPEW_EvnScaleResultText').setText('оглушение');
										else 
											win.findById('swERPEW_EvnScaleResultText').setText('сознание ясное');
										
										//заполнение строки грида
										var EvnScalesGridRow = win.findById('swERPEW_EvnScales_Grid').getSelectionModel().getSelected();  //выбранная строка в гриде событий шкал
										//если запись в гриде новая
										if (EvnScalesGridRow.data['EvnScale_id'] == 'New_GUID_Id'){
											//	console.log('BOB_record=',record); 
											EvnScalesGridRow.data['EvnScale_Result'] = win.findById('swERPEW_EvnScaleResult').text;
											EvnScalesGridRow.data['EvnScale_ResultTradic'] = win.findById('swERPEW_EvnScaleResultText').text;																	
											EvnScalesGridRow.commit();			
										}										
									}
								},
								//Шкала psas  //BOB - 20.02.2020
								{							
									layout:'form',
									id:'swERPEW_psas_ScalePanel',
									border:true,
									style: 'border-top: 1px solid #ffffff; border-left: 1px solid  #ffffff; border-bottom: 1px solid #99bbe8;border-right: 1px solid #99bbe8; padding: 2px;  ',
									items:[	
										{							
											layout:'column',
											items:[	
												//панель - Мышечная активность  //BOB - 27.08.2019	  
												new sw.Promed.SwScaleParameter({
													nam_begin: 'swERPEW',
													scale_name: 'psas',
													parameter_name: 'psas',
													text_anchor: '98%',
													lbl_text: 'Седация и возбуждение',
													text_width: 800,
													combo_width: 850,
													value_width: 20,
													win: this
												})     
											]
										}
									],
									//функция расчёта общего итогов
									overall_results: function(object_id){

										var sum = parseInt(this.findById('swERPEW_psas_psas_Val').text);
										win.findById('swERPEW_EvnScaleResult').setText(sum);
										
										//формирование строки	Традиционной классификации , по данной шкале пока нету
										win.findById('swERPEW_EvnScaleResultText').setText('');
										
										if (this.findById('swERPEW_psas_psas').value) {
											var index = this.findById('swERPEW_psas_psas').getStore().find('ScaleParameterResult_id', this.findById('swERPEW_psas_psas').value);//нахожу индекс в store комбо 
											win.findById('swERPEW_EvnScaleResultText').setText(this.findById('swERPEW_psas_psas').getStore().getAt(index).data.ScaleParameterResult_Name);
										}
										
										//заполнение строки грида
										var EvnScalesGridRow = win.findById('swERPEW_EvnScales_Grid').getSelectionModel().getSelected();  //выбранная строка в гриде событий шкал
										//если запись в гриде новая
										if (EvnScalesGridRow.data['EvnScale_id'] == 'New_GUID_Id'){
											//	console.log('BOB_record=',record); 
											EvnScalesGridRow.data['EvnScale_Result'] = win.findById('swERPEW_EvnScaleResult').text;
											EvnScalesGridRow.data['EvnScale_ResultTradic'] = win.findById('swERPEW_EvnScaleResultText').text;																	
											EvnScalesGridRow.commit();			
										}
									}
									
								},								
								//Шкала pSOFA 
								{							
									layout:'column',
									id:'swERPEW_psofa_ScalePanel',
									border:true,
									style: 'border-top: 1px solid #ffffff; border-left: 1px solid  #ffffff; border-bottom: 1px solid #99bbe8;border-right: 1px solid #99bbe8; padding: 2px;  ',
									items:[	
										//возраст в месяцах	
										{
											layout:'form',
											labelWidth:110,
												style:'margin-top: 2px; margin-right: 5px;',
												items:[	
												new Ext.form.NumberField({
													value: 0,
													id: 'swERPEW_psofa_age',
													width: 40,
													fieldLabel: langs('Возраст в месяцах'),
													labelSeparator: '',
													listeners:{
														'change':function (field, newValue, oldValue) {
															console.log('BOB_swERPEW_psofa_age_change_newValue=',newValue);
															if((newValue == null) || (newValue == '')){
																field.setValue('0');
																newValue = 0;
															}
															win.findById('swERPEW_psofa_cardiovascular').setValue(null);
															win.findById('swERPEW_psofa_cardiovascular_Val').setText(0);
															//this.findById('swERPEW_psofa_cardiovascular').fireEvent('expand', this.findById('swERPEW_psofa_cardiovascular')); // запуск события 
															//BOB - 20.04.2020
															var SysNick = 'cardiovascular';
															if (parseInt(newValue) == 0){
																SysNick += '~0';
															} else if ((parseInt(newValue) >= 1) && (parseInt(newValue) <= 11)){
																SysNick += '~1_11';
															} else if ((parseInt(newValue) >= 12) && (parseInt(newValue) <= 23)){
																SysNick += '~12_23';
															} else if ((parseInt(newValue) >= 24) && (parseInt(newValue) <= 59)){
																SysNick += '~24_59';
															}
															var NSI = win.ERPEW_NSI.EvnScalepsofa[SysNick];					
															var Datas =  [];// данные одного параметра, заготовляем для первого параметра
															if (SysNick != 'cardiovascular'){
																for (var i in NSI) { // цикл по значениям параметра
																	Datas[i]= [ NSI[i].ScaleParameterType_SysNick, 
																				NSI[i].ScaleParameterResult_Name,   
																				NSI[i].ScaleParameterResult_id,   
																				NSI[i].ScaleParameterResult_Value,   
																				NSI[i].ScaleParameterType_id ];
																};
															}
															win.findById('swERPEW_psofa_cardiovascular').getStore().loadData(Datas);			
															if(win.findById('swERPEW_psofa_cardiovascular').view) win.findById('swERPEW_psofa_cardiovascular').view.getStore().loadData(Datas);										

															win.findById('swERPEW_psofa_renal').setValue(null);
															win.findById('swERPEW_psofa_renal_Val').setText(0);
															//this.findById('swERPEW_psofa_renal').fireEvent('expand', this.findById('swERPEW_psofa_renal')); // запуск события 
															var SysNick = 'renal';
															if (parseInt(newValue) == 0){
																SysNick += '~0';
															} else if ((parseInt(newValue) >= 1) && (parseInt(newValue) <= 11)){
																SysNick += '~1_11';
															} else if ((parseInt(newValue) >= 12) && (parseInt(newValue) <= 23)){
																SysNick += '~12_23';
															} else if ((parseInt(newValue) >= 24) && (parseInt(newValue) <= 59)){
																SysNick += '~24_59';
															}
															var NSI = win.ERPEW_NSI.EvnScalepsofa[SysNick];					
															var Datas =  [];// данные одного параметра, заготовляем для первого параметра
															if (SysNick != 'renal'){
																for (var i in NSI) { // цикл по значениям параметра
																	Datas[i]= [ NSI[i].ScaleParameterType_SysNick, 
																				NSI[i].ScaleParameterResult_Name,   
																				NSI[i].ScaleParameterResult_id,   
																				NSI[i].ScaleParameterResult_Value,   
																				NSI[i].ScaleParameterType_id ];
																};
															}
															win.findById('swERPEW_psofa_renal').getStore().loadData(Datas);			
															if(win.findById('swERPEW_psofa_renal').view) win.findById('swERPEW_psofa_renal').view.getStore().loadData(Datas);										
															//BOB - 20.04.2020
															win.findById('swERPEW_psofa_ScalePanel').overall_results('swERPEW_psofa_age');	
														}.createDelegate(this)
													}													
												}),
											]
										},
										{
											layout:'form',
											style: 'border-top: 1px solid #ffffff; border-left: 1px solid  #ffffff; border-bottom: 1px solid #99bbe8;border-right: 1px solid #99bbe8; padding: 2px;  ',
											width: 1100,
											items:[	

												{							
													layout:'column',
													items:[	
														//панель - Респираторная дисфункция     //BOB - 27.08.2019	  
														new sw.Promed.SwScaleParameter({
															nam_begin: 'swERPEW',
															scale_name: 'psofa',
															parameter_name: 'respiratory',
															text_anchor: '98%',
															lbl_text: 'Респираторная дисфункция PaO2(мм.рт.ст.)/FiO2 или SpO2/FiO2',
															text_width: 340,
															combo_width: 360,
															value_width: 10,
															win: this
														}),												
														//панель - Сердечно-сосудистая дисфункция   //BOB - 27.08.2019	  
														new sw.Promed.SwScaleParameter({
															nam_begin: 'swERPEW',
															scale_name: 'psofa',
															parameter_name: 'cardiovascular',
															text_anchor: '98%',
															lbl_text: 'Сердечно-сосудистая дисфункция (мм.рт.ст. или мкг/кг/мин)',
															text_width: 340,
															combo_width: 360,
															value_width: 10,
															win: this
														}),		
														//панель - Почечная дисфункция    //BOB - 27.08.2019	  
														new sw.Promed.SwScaleParameter({
															nam_begin: 'swERPEW',
															scale_name: 'psofa',
															parameter_name: 'renal',
															text_anchor: '98%',
															lbl_text: 'Почечная дисфункция (мкмоль/л)',
															text_width: 290,
															combo_width: 310,
															value_width: 10,
															win: this
														})
													]
												},
												{							
													layout:'column',
													items:[	
														//панель - Гематологическая дисфункция     //BOB - 27.08.2019	  
														new sw.Promed.SwScaleParameter({
															nam_begin: 'swERPEW',
															scale_name: 'psofa',
															parameter_name: 'hematological',
															text_anchor: '98%',
															lbl_text: 'Гематологическая дисфункция (тромбоцитов *10^9/л)',
															text_width: 340,
															combo_width: 360,
															value_width: 10,
															win: this
														}),
														//панель - Печёночная дисфункция   //BOB - 27.08.2019	  
														new sw.Promed.SwScaleParameter({
															nam_begin: 'swERPEW',
															scale_name: 'psofa',
															parameter_name: 'hepatic',
															text_anchor: '98%',
															lbl_text: 'Печёночная дисфункция (мкмоль/л)',
															text_width: 340,
															combo_width: 360,
															value_width: 10,
															win: this
														}), 
														//панель - Неврологическая дисфункция    //BOB - 27.08.2019	  
														new sw.Promed.SwScaleParameter({
															nam_begin: 'swERPEW',
															scale_name: 'psofa',
															parameter_name: 'neurological',
															text_anchor: '98%',
															lbl_text: 'Неврологическая дисфункция',
															text_width: 290,
															combo_width: 310,
															value_width: 10,
															win: this
														})
													]
												}										
											]
										}
									],
									//функция расчёта общего итогов
									overall_results: function(object_id){
										//console.log('BOB_overall_results_object_id=',object_id); 
										var respiratory_Val = parseInt(this.findById('swERPEW_psofa_respiratory_Val').text);
										var cardiovascular_Val = parseInt(this.findById('swERPEW_psofa_cardiovascular_Val').text);
										var renal_Val = parseInt(this.findById('swERPEW_psofa_renal_Val').text);
										var hematological_Val = parseInt(this.findById('swERPEW_psofa_hematological_Val').text);
										var hepatic_Val = parseInt(this.findById('swERPEW_psofa_hepatic_Val').text);
										var neurological_Val = parseInt(this.findById('swERPEW_psofa_neurological_Val').text);
										var sum = respiratory_Val + cardiovascular_Val + renal_Val + hematological_Val + hepatic_Val + neurological_Val;
										win.findById('swERPEW_EvnScaleResult').setText(sum);
										
										//формирование строки	Традиционной классификации , по данной шкале пока нету
										win.findById('swERPEW_EvnScaleResultText').setText('');

										// контролирую все ли параметры выбраны
										var toDo = true;
										var ParamCombos = win.findById('swERPEW_psofa_ScalePanel').find('xtype', 'combo');
										for (var i in ParamCombos){
											if(ParamCombos[i].id) {
												if ((ParamCombos[i].getValue() == null) || (ParamCombos[i].getValue() == '')) {
													toDo = false;
													break;
												}	
											}
										}
										if (toDo) {
											if (sum <= 4)
												win.findById('swERPEW_EvnScaleResultText').setText('риск летального исхода 0%');
											else if (sum <= 8)
												win.findById('swERPEW_EvnScaleResultText').setText('риск летального исхода 2%');
											else if (sum <= 12)
												win.findById('swERPEW_EvnScaleResultText').setText('риск летального исхода 8%');
											else if (sum <= 16)
												win.findById('swERPEW_EvnScaleResultText').setText('риск летального исхода 30%');
											else if (sum <= 20)
												win.findById('swERPEW_EvnScaleResultText').setText('риск летального исхода 50%');
											else 
												win.findById('swERPEW_EvnScaleResultText').setText('риск летального исхода 70%');
										}
										
										//заполнение строки грида
										var EvnScalesGridRow = win.findById('swERPEW_EvnScales_Grid').getSelectionModel().getSelected();  //выбранная строка в гриде событий шкал
										//если запись в гриде новая
										if ((EvnScalesGridRow) && (EvnScalesGridRow.data['EvnScale_id'] == 'New_GUID_Id')){
											EvnScalesGridRow.data['EvnScale_Result'] = win.findById('swERPEW_EvnScaleResult').text;
											EvnScalesGridRow.data['EvnScale_ResultTradic'] = win.findById('swERPEW_EvnScaleResultText').text;																	
											EvnScalesGridRow.data['EvnScale_AgeMonth'] = win.findById('swERPEW_psofa_age').getValue();																	
											EvnScalesGridRow.commit();			
										}

									}
								},
								//Шкала PELOD-2 
								{							
									layout:'column',
									id:'swERPEW_pelod_ScalePanel',
									border:true,
									style: 'border-top: 1px solid #ffffff; border-left: 1px solid  #ffffff; border-bottom: 1px solid #99bbe8;border-right: 1px solid #99bbe8; padding: 2px;  ',
									items:[
										//возраст в месяцах	
										{
											layout:'form',
											labelWidth:110,
												style:'margin-top: 2px; margin-right: 5px;',
												items:[	
												new Ext.form.NumberField({
													value: 0,
													id: 'swERPEW_pelod_age',
													width: 40,
													fieldLabel: langs('Возраст в месяцах'),
													labelSeparator: '',
													listeners:{
														'change':function (field, newValue, oldValue) {
															//console.log('BOB_swERPEW_pelod_age_change_newValue=',newValue);
															if((newValue == null) || (newValue == '')){
																field.setValue('0');
																newValue = 0;
															}
															win.findById('swERPEW_pelod_pressure').setValue(null);
															win.findById('swERPEW_pelod_pressure_Val').setText(0);
															//this.findById('swERPEW_pelod_pressure').fireEvent('expand', this.findById('swERPEW_pelod_pressure')); // запуск события 
															//BOB - 20.04.2020
															var SysNick = 'pressure';
															if (parseInt(newValue) == 0){
																SysNick += '~0';
															} else if ((parseInt(newValue) >= 1) && (parseInt(newValue) <= 11)){
																SysNick += '~1_11';
															} else if ((parseInt(newValue) >= 12) && (parseInt(newValue) <= 23)){
																SysNick += '~12_23';
															} else if ((parseInt(newValue) >= 24) && (parseInt(newValue) <= 59)){
																SysNick += '~24_59';
															}
															var NSI = win.ERPEW_NSI.EvnScalepelod[SysNick];					
															var Datas =  [];// данные одного параметра, заготовляем для первого параметра
															if (SysNick != 'pressure'){
																for (var i in NSI) { // цикл по значениям параметра
																	Datas[i]= [ NSI[i].ScaleParameterType_SysNick, 
																				NSI[i].ScaleParameterResult_Name,   
																				NSI[i].ScaleParameterResult_id,   
																				NSI[i].ScaleParameterResult_Value,   
																				NSI[i].ScaleParameterType_id ];
																};
															}
															win.findById('swERPEW_pelod_pressure').getStore().loadData(Datas);			
															if(win.findById('swERPEW_pelod_pressure').view) win.findById('swERPEW_pelod_pressure').view.getStore().loadData(Datas);										
															
															win.findById('swERPEW_pelod_renal').setValue(null);
															win.findById('swERPEW_pelod_renal_Val').setText(0);
															//this.findById('swERPEW_pelod_renal').fireEvent('expand', this.findById('swERPEW_pelod_renal')); // запуск события 
															SysNick = 'renal';
															if (parseInt(newValue) == 0){
																SysNick += '~0';
															} else if ((parseInt(newValue) >= 1) && (parseInt(newValue) <= 11)){
																SysNick += '~1_11';
															} else if ((parseInt(newValue) >= 12) && (parseInt(newValue) <= 23)){
																SysNick += '~12_23';
															} else if ((parseInt(newValue) >= 24) && (parseInt(newValue) <= 59)){
																SysNick += '~24_59';
															}
															var NSI = win.ERPEW_NSI.EvnScalepelod[SysNick];					
															var Datas =  [];// данные одного параметра, заготовляем для первого параметра
															if (SysNick != 'renal'){
																for (var i in NSI) { // цикл по значениям параметра
																	Datas[i]= [ NSI[i].ScaleParameterType_SysNick, 
																				NSI[i].ScaleParameterResult_Name,   
																				NSI[i].ScaleParameterResult_id,   
																				NSI[i].ScaleParameterResult_Value,   
																				NSI[i].ScaleParameterType_id ];
																};
															}
															win.findById('swERPEW_pelod_renal').getStore().loadData(Datas);			
															if(win.findById('swERPEW_pelod_renal').view) win.findById('swERPEW_pelod_renal').view.getStore().loadData(Datas);										
															//BOB - 20.04.2020
															
															win.findById('swERPEW_pelod_ScalePanel').overall_results('swERPEW_pelod_age');	
														}.createDelegate(this)
													}													
												}),
											]
										},
										{
											layout:'form',
											style: 'border-top: 1px solid #ffffff; border-left: 1px solid  #ffffff; border-bottom: 1px solid #99bbe8;border-right: 1px solid #99bbe8; padding: 2px;  ',
											width: 1100,
											items:[	

												{							
													layout:'column',
													items:[	
														//панель - Неврологическая дисфункция по Глазго    //BOB - 27.08.2019	  
														new sw.Promed.SwScaleParameter({
															nam_begin: 'swERPEW',
															scale_name: 'pelod',
															parameter_name: 'neurologic_glasgow',
															text_anchor: '98%',
															lbl_text: 'Неврологическая дисфункция по Глазго',
															text_width: 290,
															combo_width: 310,
															value_width: 10,
															win: this
														}),
														//панель - Неврологическая дисфункция – реакция зрачков    //BOB - 27.08.2019	  
														new sw.Promed.SwScaleParameter({
															nam_begin: 'swERPEW',
															scale_name: 'pelod',
															parameter_name: 'neurologic_pupil',
															text_anchor: '98%',
															lbl_text: 'Неврологическая дисфункция – реакция зрачков',
															text_width: 290,
															combo_width: 310,
															value_width: 10,
															win: this
														})

													]
												},


												{							
													layout:'column',
													items:[	
														//панель - Сердечно-сосудистая дисфункция   //BOB - 27.08.2019	  
														new sw.Promed.SwScaleParameter({
															nam_begin: 'swERPEW',
															scale_name: 'pelod',
															parameter_name: 'cardiovascular',
															text_anchor: '98%',
															lbl_text: 'Сердечно-сосудистая дисфункция (ммоль/л)',
															text_width: 290,
															combo_width: 310,
															value_width: 10,
															win: this
														}),		
														//панель - Среднее артериальное давление   //BOB - 27.08.2019	  
														new sw.Promed.SwScaleParameter({
															nam_begin: 'swERPEW',
															scale_name: 'pelod',
															parameter_name: 'pressure',
															text_anchor: '98%',
															lbl_text: 'Среднее артериальное давление (мм.рт.ст.)',
															text_width: 290,
															combo_width: 310,
															value_width: 10,
															win: this
														}), 
														//панель - Почечная дисфункция    //BOB - 27.08.2019	  
														new sw.Promed.SwScaleParameter({
															nam_begin: 'swERPEW',
															scale_name: 'pelod',
															parameter_name: 'renal',
															text_anchor: '98%',
															lbl_text: 'Почечная дисфункция (мкмоль/л)',
															text_width: 290,
															combo_width: 310,
															value_width: 10,
															win: this
														})
													]
												},
												{							
													layout:'column',
													items:[	
														//панель - Респираторная дисфункция     //BOB - 27.08.2019	  
														new sw.Promed.SwScaleParameter({
															nam_begin: 'swERPEW',
															scale_name: 'pelod',
															parameter_name: 'respiratory',
															text_anchor: '98%',
															lbl_text: 'Респираторная дисфункция PaO2(мм.рт.ст.)/FiO2 или PaCO2(мм.рт.ст.)',
															text_width: 400,
															combo_width: 420,
															value_width: 10,
															win: this
														}),												
														//панель - Гематологическая дисфункция     //BOB - 27.08.2019	  
														new sw.Promed.SwScaleParameter({
															nam_begin: 'swERPEW',
															scale_name: 'pelod',
															parameter_name: 'hematologic',
															text_anchor: '98%',
															lbl_text: 'Гематологическая дисфункция (лейк-ты ×10⁹/л) или тромб-ты ×10⁹/л)',
															text_width: 400,
															combo_width: 420,
															value_width: 10,
															win: this
														}),
													]
												}										
											]
										}
									],
									//функция расчёта общего итогов
									overall_results: function(object_id){
										//console.log('BOB_overall_results_object_id=',object_id); 
										var neurologic_glasgow_Val = parseInt(this.findById('swERPEW_pelod_neurologic_glasgow_Val').text);
										var neurologic_pupil_Val = parseInt(this.findById('swERPEW_pelod_neurologic_pupil_Val').text);
										var cardiovascular_Val = parseInt(this.findById('swERPEW_pelod_cardiovascular_Val').text);
										var pressure_Val = parseInt(this.findById('swERPEW_pelod_pressure_Val').text);
										var renal_Val = parseInt(this.findById('swERPEW_pelod_renal_Val').text);
										var respiratory_Val = parseInt(this.findById('swERPEW_pelod_respiratory_Val').text);
										var hematologic_Val = parseInt(this.findById('swERPEW_pelod_hematologic_Val').text);
										var sum = neurologic_glasgow_Val + neurologic_pupil_Val + cardiovascular_Val + pressure_Val + renal_Val + respiratory_Val + hematologic_Val;
										win.findById('swERPEW_EvnScaleResult').setText(sum);
										
										//формирование строки	Традиционной классификации , по данной шкале пока нету
										win.findById('swERPEW_EvnScaleResultText').setText('');

										// контролирую все ли параметры выбраны
										var toDo = true;
										var ParamCombos = win.findById('swERPEW_pelod_ScalePanel').find('xtype', 'combo');
										for (var i in ParamCombos){
											if(ParamCombos[i].id) {
												if ((ParamCombos[i].getValue() == null) || (ParamCombos[i].getValue() == '')) {
													toDo = false;
													break;
												}	
											}
										}
										if (toDo) {
											var logit = -6.61 + sum * 0.47;
											var death_risk = Math.round((1 / (1 + Math.exp(-logit))) * 10000) / 100;
											win.findById('swERPEW_EvnScaleResultText').setText('Риск смерти -    ' + death_risk + '%');
										}
										
										//заполнение строки грида
										var EvnScalesGridRow = win.findById('swERPEW_EvnScales_Grid').getSelectionModel().getSelected();  //выбранная строка в гриде событий шкал
										//если запись в гриде новая
										if ((EvnScalesGridRow) && (EvnScalesGridRow.data['EvnScale_id'] == 'New_GUID_Id')){
											EvnScalesGridRow.data['EvnScale_Result'] = win.findById('swERPEW_EvnScaleResult').text;
											EvnScalesGridRow.data['EvnScale_ResultTradic'] = win.findById('swERPEW_EvnScaleResultText').text;																	
											EvnScalesGridRow.data['EvnScale_AgeMonth'] = win.findById('swERPEW_pelod_age').getValue();																	
											EvnScalesGridRow.commit();			
										}

									}
								},
								//Шкала N-PASS 
								{							
									layout:'form',
									id:'swERPEW_npass_ScalePanel',
									border:true,
									style: 'border-top: 1px solid #ffffff; border-left: 1px solid  #ffffff; border-bottom: 1px solid #99bbe8;border-right: 1px solid #99bbe8; padding: 2px;  ',
									items:[	
										//панель - Плач раздражительность	  
										{							
											layout:'column',
											items:[	
												new sw.Promed.SwScaleParameter({
													nam_begin: 'swERPEW',
													scale_name: 'npass',
													parameter_name: 'crying',
													text_anchor: '100%',
													lbl_text: 'Плач раздражительность',
													text_width: 1010,
													combo_width: 1050,
													value_width: 20,
													win: this
												}),
											]
										},
										//панель - Поведение
										{							
											layout:'column',
											items:[	
												new sw.Promed.SwScaleParameter({
													nam_begin: 'swERPEW',
													scale_name: 'npass',
													parameter_name: 'behavior',
													text_anchor: '100%',
													lbl_text: 'Поведение',
													text_width: 1010,
													combo_width: 1050,
													value_width: 20,
													win: this
												})
											]
										},
										//панель - Выражение лица
										{							
											layout:'column',
											items:[	
												new sw.Promed.SwScaleParameter({
													nam_begin: 'swERPEW',
													scale_name: 'npass',
													parameter_name: 'face',
													text_anchor: '100%',
													lbl_text: 'Выражение лица',
													text_width: 1010,
													combo_width: 1050,
													value_width: 20,
													win: this
												})
											]
										},
										//панель - Тонус рук и ног
										{							
											layout:'column',
											items:[	
												new sw.Promed.SwScaleParameter({
													nam_begin: 'swERPEW',
													scale_name: 'npass',
													parameter_name: 'tone',
													text_anchor: '100%',
													lbl_text: 'Тонус рук и ног',
													text_width: 1010,
													combo_width: 1050,
													value_width: 20,
													win: this
												})
											]
										},
										//панель - Жизненно важные показатели
										{							
											layout:'column',
											items:[	
												new sw.Promed.SwScaleParameter({
													nam_begin: 'swERPEW',
													scale_name: 'npass',
													parameter_name: 'vital_signs',
													text_anchor: '100%',
													lbl_text: 'Жизненно важные показатели',
													text_width: 1010,
													combo_width: 1050,
													value_width: 20,
													win: this
												}) 
											]
										}
									],
									//функция расчёта общего итогов
									overall_results: function(object_id){

										var crying_Val = parseInt(this.findById('swERPEW_npass_crying_Val').text);
										var behavior_Val = parseInt(this.findById('swERPEW_npass_behavior_Val').text);
										var face_Val = parseInt(this.findById('swERPEW_npass_face_Val').text);
										var tone_Val = parseInt(this.findById('swERPEW_npass_tone_Val').text);
										var vital_signs_Val = parseInt(this.findById('swERPEW_npass_vital_signs_Val').text);
										var sum = crying_Val + behavior_Val + face_Val + tone_Val + vital_signs_Val;
										win.findById('swERPEW_EvnScaleResult').setText(sum);
										
										//формирование строки	Традиционной классификации , по данной шкале пока нету
										win.findById('swERPEW_EvnScaleResultText').setText('');
										
										
										//заполнение строки грида
										var EvnScalesGridRow = win.findById('swERPEW_EvnScales_Grid').getSelectionModel().getSelected();  //выбранная строка в гриде событий шкал
										//если запись в гриде новая
										if (EvnScalesGridRow.data['EvnScale_id'] == 'New_GUID_Id'){
											//	console.log('BOB_record=',record); 
											EvnScalesGridRow.data['EvnScale_Result'] = win.findById('swERPEW_EvnScaleResult').text;
											EvnScalesGridRow.data['EvnScale_ResultTradic'] = win.findById('swERPEW_EvnScaleResultText').text;																	
											EvnScalesGridRow.commit();			
										}
									}
									
								},
								//Шкала NIPS 
								{							
									layout:'form',
									id:'swERPEW_nips_ScalePanel',
									border:true,
									style: 'border-top: 1px solid #ffffff; border-left: 1px solid  #ffffff; border-bottom: 1px solid #99bbe8;border-right: 1px solid #99bbe8; padding: 2px;  ',
									items:[	
										
										{							
											layout:'column',
											items:[	
												//панель - Выражение лица
												new sw.Promed.SwScaleParameter({
													nam_begin: 'swERPEW',
													scale_name: 'nips',
													parameter_name: 'face',
													text_anchor: '98%',
													lbl_text: 'Выражение лица',
													text_width: 290,
													combo_width: 310,
													value_width: 10,
													win: this
												}),												
												//панель - Плач
												new sw.Promed.SwScaleParameter({
													nam_begin: 'swERPEW',
													scale_name: 'nips',
													parameter_name: 'crying',
													text_anchor: '97%',
													lbl_text: 'Плач',
													text_width: 290,
													combo_width: 310,
													value_width: 10,
													win: this
												}),		
												//панель - Дыхание
												new sw.Promed.SwScaleParameter({
													nam_begin: 'swERPEW',
													scale_name: 'nips',
													parameter_name: 'breath',
													text_anchor: '98%',
													lbl_text: 'Дыхание',
													text_width: 290,
													combo_width: 310,
													value_width: 10,
													win: this
												})
											]
										},
										{							
											layout:'column',
											items:[	
												//панель - Верхние конечности
												new sw.Promed.SwScaleParameter({
													nam_begin: 'swERPEW',
													scale_name: 'nips',
													parameter_name: 'upper_extremity',
													text_anchor: '98%',
													lbl_text: 'Верхние конечности',
													text_width: 290,
													combo_width: 310,
													value_width: 10,
													win: this
												}),
												//панель - Нижние конечности
												new sw.Promed.SwScaleParameter({
													nam_begin: 'swERPEW',
													scale_name: 'nips',
													parameter_name: 'lower_extremity',
													text_anchor: '97%',
													lbl_text: 'Нижние конечности',
													text_width: 290,
													combo_width: 310,
													value_width: 10,
													win: this
												}), 
												//панель - Сон
												new sw.Promed.SwScaleParameter({
													nam_begin: 'swERPEW',
													scale_name: 'nips',
													parameter_name: 'sleep',
													text_anchor: '98%',
													lbl_text: 'Сон',
													text_width: 290,
													combo_width: 310,
													value_width: 10,
													win: this
												})
											]
										}										
									],
									//функция расчёта общего итогов
									overall_results: function(object_id){
										var face_Val = parseInt(this.findById('swERPEW_nips_face_Val').text);
										var crying_Val = parseInt(this.findById('swERPEW_nips_crying_Val').text);
										var breath_Val = parseInt(this.findById('swERPEW_nips_breath_Val').text);
										var upper_extremity_Val = parseInt(this.findById('swERPEW_nips_upper_extremity_Val').text);
										var lower_extremity_Val = parseInt(this.findById('swERPEW_nips_lower_extremity_Val').text);
										var sleep_Val = parseInt(this.findById('swERPEW_nips_sleep_Val').text);
										var sum = face_Val + crying_Val + breath_Val + upper_extremity_Val + lower_extremity_Val + sleep_Val;
										win.findById('swERPEW_EvnScaleResult').setText(sum);
										
										//формирование строки	Традиционной классификации , по данной шкале пока нету
										win.findById('swERPEW_EvnScaleResultText').setText('');
										// контролирую все ли параметры выбраны
										var toDo = true;
										var ParamCombos = win.findById('swERPEW_nips_ScalePanel').find('xtype', 'combo');
										for (var i in ParamCombos){
											if(ParamCombos[i].id) {
												if ((ParamCombos[i].getValue() == null) || (ParamCombos[i].getValue() == '')) {
													toDo = false;
													break;
												}	
											}
										}
										
										if (toDo) {

											if (sum <= 2)
												win.findById('swERPEW_EvnScaleResultText').setText('нет боли');
											else if (sum <= 5)
												win.findById('swERPEW_EvnScaleResultText').setText('умеренно выраженная боль');
											else 
												win.findById('swERPEW_EvnScaleResultText').setText('сильная боль');
										}
										
										//заполнение строки грида
										var EvnScalesGridRow = win.findById('swERPEW_EvnScales_Grid').getSelectionModel().getSelected();  //выбранная строка в гриде событий шкал
										//если запись в гриде новая
										if (EvnScalesGridRow.data['EvnScale_id'] == 'New_GUID_Id'){
											//	console.log('BOB_record=',record); 
											EvnScalesGridRow.data['EvnScale_Result'] = win.findById('swERPEW_EvnScaleResult').text;
											EvnScalesGridRow.data['EvnScale_ResultTradic'] = win.findById('swERPEW_EvnScaleResultText').text;																	
											EvnScalesGridRow.commit();			
										}
									}
								},
								//Шкала COMFORT 
								{							
									layout:'form',
									id:'swERPEW_comfort_ScalePanel',
									border:true,
									style: 'border-top: 1px solid #ffffff; border-left: 1px solid  #ffffff; border-bottom: 1px solid #99bbe8;border-right: 1px solid #99bbe8; padding: 2px;  ',
									items:[	
										
										{							
											layout:'column',
											items:[	
												//панель - Беспокойство
												new sw.Promed.SwScaleParameter({
													nam_begin: 'swERPEW',
													scale_name: 'comfort',
													parameter_name: 'anxiety',
													text_anchor: '98%',
													lbl_text: 'Беспокойство',
													text_width: 304,
													combo_width: 324,
													value_width: 10,
													win: this
												}),												
												//панель - Тревожность
												new sw.Promed.SwScaleParameter({
													nam_begin: 'swERPEW',
													scale_name: 'comfort',
													parameter_name: 'worry',
													text_anchor: '97%',
													lbl_text: 'Тревожность',
													text_width: 310,
													combo_width: 330,
													value_width: 10,
													win: this
												}),		
												//панель - Дыхательные нарушения
												new sw.Promed.SwScaleParameter({
													nam_begin: 'swERPEW',
													scale_name: 'comfort',
													parameter_name: 'respiratory',
													text_anchor: '98%',
													lbl_text: 'Дыхательные нарушения',
													text_width: 420,
													combo_width: 440,
													value_width: 10,
													win: this
												})
											]
										},
										{							
											layout:'column',
											items:[	
												//панель - Плач
												new sw.Promed.SwScaleParameter({
													nam_begin: 'swERPEW',
													scale_name: 'comfort',
													parameter_name: 'crying',
													text_anchor: '99%',
													lbl_text: 'Плач',
													text_width: 530,
													combo_width: 550,
													value_width: 10,
													win: this
												}),
												//панель - Физическая подвижность
												new sw.Promed.SwScaleParameter({
													nam_begin: 'swERPEW',
													scale_name: 'comfort',
													parameter_name: 'mobility',
													text_anchor: '99%',
													lbl_text: 'Физическая подвижность',
													text_width: 530,
													combo_width: 550,
													value_width: 10,
													win: this
												}) 
											]
										},
										{							
											layout:'column',
											items:[	
												//панель - Мышечный тонус
												new sw.Promed.SwScaleParameter({
													nam_begin: 'swERPEW',
													scale_name: 'comfort',
													parameter_name: 'muscle',
													text_anchor: '99%',
													lbl_text: 'Мышечный тонус',
													text_width: 530,
													combo_width: 550,
													value_width: 10,
													win: this
												}),
												//панель - Мимический тонус
												new sw.Promed.SwScaleParameter({
													nam_begin: 'swERPEW',
													scale_name: 'comfort',
													parameter_name: 'face',
													text_anchor: '99%',
													lbl_text: 'Мимический тонус',
													text_width: 530,
													combo_width: 550,
													value_width: 10,
													win: this
												})
											]
										},
										{							
											layout:'column',
											items:[	
												//панель - Среднее артериальное давление
												new sw.Promed.SwScaleParameter({
													nam_begin: 'swERPEW',
													scale_name: 'comfort',
													parameter_name: 'pressure',
													text_anchor: '99%',
													lbl_text: 'Среднее артериальное давление',
													text_width: 530,
													combo_width: 550,
													value_width: 10,
													win: this
												}), 
												//панель - Частота сердечных сокращений
												new sw.Promed.SwScaleParameter({
													nam_begin: 'swERPEW',
													scale_name: 'comfort',
													parameter_name: 'heart_rate',
													text_anchor: '99%',
													lbl_text: 'Частота сердечных сокращений',
													text_width: 530,
													combo_width: 550,
													value_width: 10,
													win: this
												})
											]
										}
									],
									//функция расчёта общего итогов
									overall_results: function(object_id){
										var anxiety_Val = parseInt(this.findById('swERPEW_comfort_anxiety_Val').text);
										var worry_Val = parseInt(this.findById('swERPEW_comfort_worry_Val').text);
										var respiratory_Val = parseInt(this.findById('swERPEW_comfort_respiratory_Val').text);
										var crying_Val = parseInt(this.findById('swERPEW_comfort_crying_Val').text);
										var mobility_Val = parseInt(this.findById('swERPEW_comfort_mobility_Val').text);
										var muscle_Val = parseInt(this.findById('swERPEW_comfort_muscle_Val').text);
										var face_Val = parseInt(this.findById('swERPEW_comfort_face_Val').text);
										var pressure_Val = parseInt(this.findById('swERPEW_comfort_pressure_Val').text);
										var heart_rate_Val = parseInt(this.findById('swERPEW_comfort_heart_rate_Val').text);
										var sum = anxiety_Val + worry_Val + respiratory_Val + crying_Val + mobility_Val + muscle_Val + face_Val + pressure_Val + heart_rate_Val;
										win.findById('swERPEW_EvnScaleResult').setText(sum);
										
										//формирование строки	Традиционной классификации , по данной шкале пока нету
										win.findById('swERPEW_EvnScaleResultText').setText('');
										// контролирую все ли параметры выбраны
										var toDo = true;
										var ParamCombos = win.findById('swERPEW_comfort_ScalePanel').find('xtype', 'combo');
										for (var i in ParamCombos){
											if(ParamCombos[i].id) {
												if ((ParamCombos[i].getValue() == null) || (ParamCombos[i].getValue() == '')) {
													toDo = false;
													break;
												}	
											}
										}
										
										if (toDo) {

											if ((sum >= 17) && (sum <= 26))
												win.findById('swERPEW_EvnScaleResultText').setText('адекватные седация и обезболивание');
											else 
												win.findById('swERPEW_EvnScaleResultText').setText('НЕадекватные седация и обезболивание');
										}
										
										//заполнение строки грида
										var EvnScalesGridRow = win.findById('swERPEW_EvnScales_Grid').getSelectionModel().getSelected();  //выбранная строка в гриде событий шкал
										//если запись в гриде новая
										if (EvnScalesGridRow.data['EvnScale_id'] == 'New_GUID_Id'){
											//	console.log('BOB_record=',record); 
											EvnScalesGridRow.data['EvnScale_Result'] = win.findById('swERPEW_EvnScaleResult').text;
											EvnScalesGridRow.data['EvnScale_ResultTradic'] = win.findById('swERPEW_EvnScaleResultText').text;																	
											EvnScalesGridRow.commit();			
										}
									}
								},
								//Шкала PIPP 
								{							
									layout:'form',
									id:'swERPEW_pipp_ScalePanel',
									border:true,
									style: 'border-top: 1px solid #ffffff; border-left: 1px solid  #ffffff; border-bottom: 1px solid #99bbe8;border-right: 1px solid #99bbe8; padding: 2px;  ',
									items:[	
										
										{							
											layout:'column',
											items:[	
												//панель - Срок гестации
												new sw.Promed.SwScaleParameter({
													nam_begin: 'swERPEW',
													scale_name: 'pipp',
													parameter_name: 'gestational',
													text_anchor: '98%',
													lbl_text: 'Срок гестации',
													text_width: 304,
													combo_width: 324,
													value_width: 10,
													win: this
												}),												
												//панель - Поведение
												new sw.Promed.SwScaleParameter({
													nam_begin: 'swERPEW',
													scale_name: 'pipp',
													parameter_name: 'behavior',
													text_anchor: '98%',
													lbl_text: 'Поведение',
													text_width: 350,
													combo_width: 370,
													value_width: 10,
													win: this
												}),		
												//панель - Максимальная ЧСС
												new sw.Promed.SwScaleParameter({
													nam_begin: 'swERPEW',
													scale_name: 'pipp',
													parameter_name: 'heart_rate',
													text_anchor: '98%',
													lbl_text: 'Максимальная ЧСС',
													text_width: 412,
													combo_width: 432,
													value_width: 10,
													win: this
												})
											]
										},
										{							
											layout:'column',
											items:[	
												//панель - Максимальная сатурация
												new sw.Promed.SwScaleParameter({
													nam_begin: 'swERPEW',
													scale_name: 'pipp',
													parameter_name: 'saturation',
													text_anchor: '98%',
													lbl_text: 'Максимальная сатурация',
													text_width: 260,
													combo_width: 280,
													value_width: 10,
													win: this
												}),
												//панель - Нахмуривание бровей
												new sw.Promed.SwScaleParameter({
													nam_begin: 'swERPEW',
													scale_name: 'pipp',
													parameter_name: 'frowning',
													text_anchor: '98%',
													lbl_text: 'Нахмуривание бровей',
													text_width: 260,
													combo_width: 280,
													value_width: 10,
													win: this
												}), 
												//панель - Зажмуривание глаз
												new sw.Promed.SwScaleParameter({
													nam_begin: 'swERPEW',
													scale_name: 'pipp',
													parameter_name: 'eyes_closing',
													text_anchor: '98%',
													lbl_text: 'Зажмуривание глаз',
													text_width: 260,
													combo_width: 280,
													value_width: 10,
													win: this
												}),
												//панель - Носогубная складка
												new sw.Promed.SwScaleParameter({
													nam_begin: 'swERPEW',
													scale_name: 'pipp',
													parameter_name: 'nasolabial',
													text_anchor: '98%',
													lbl_text: 'Носогубная складка',
													text_width: 260,
													combo_width: 280,
													value_width: 10,
													win: this
												})


											]
										},
									],
									//функция расчёта общего итогов
									overall_results: function(object_id){
										var gestational_Val = parseInt(this.findById('swERPEW_pipp_gestational_Val').text);
										var behavior_Val = parseInt(this.findById('swERPEW_pipp_behavior_Val').text);
										var heart_rate_Val = parseInt(this.findById('swERPEW_pipp_heart_rate_Val').text);
										var saturation_Val = parseInt(this.findById('swERPEW_pipp_saturation_Val').text);
										var frowning_Val = parseInt(this.findById('swERPEW_pipp_frowning_Val').text);
										var eyes_closing_Val = parseInt(this.findById('swERPEW_pipp_eyes_closing_Val').text);
										var nasolabial_Val = parseInt(this.findById('swERPEW_pipp_nasolabial_Val').text);
										var sum = gestational_Val + behavior_Val + heart_rate_Val + saturation_Val + frowning_Val + eyes_closing_Val + nasolabial_Val;
										win.findById('swERPEW_EvnScaleResult').setText(sum);
										
										//формирование строки	Традиционной классификации , по данной шкале пока нету
										win.findById('swERPEW_EvnScaleResultText').setText('');
										// контролирую все ли параметры выбраны
										var toDo = true;
										var ParamCombos = win.findById('swERPEW_pipp_ScalePanel').find('xtype', 'combo');
										for (var i in ParamCombos){
											if(ParamCombos[i].id) {
												if ((ParamCombos[i].getValue() == null) || (ParamCombos[i].getValue() == '')) {
													toDo = false;
													break;
												}	
											}
										}
										
										if (toDo) {

											if (sum <= 5)
												win.findById('swERPEW_EvnScaleResultText').setText('слабая боль');
											else if (sum <= 12)
												win.findById('swERPEW_EvnScaleResultText').setText('умеренная боль');
											else 
												win.findById('swERPEW_EvnScaleResultText').setText('сильная боль');
										}
										
										//заполнение строки грида
										var EvnScalesGridRow = win.findById('swERPEW_EvnScales_Grid').getSelectionModel().getSelected();  //выбранная строка в гриде событий шкал
										//если запись в гриде новая
										if (EvnScalesGridRow.data['EvnScale_id'] == 'New_GUID_Id'){
											//	console.log('BOB_record=',record); 
											EvnScalesGridRow.data['EvnScale_Result'] = win.findById('swERPEW_EvnScaleResult').text;
											EvnScalesGridRow.data['EvnScale_ResultTradic'] = win.findById('swERPEW_EvnScaleResultText').text;																	
											EvnScalesGridRow.commit();			
										}
									}
								},
								//Шкала BIND  //BOB - 20.02.2020
								{							
									layout:'column',
									id:'swERPEW_bind_ScalePanel',
									border:true,
									style: 'border-top: 1px solid #ffffff; border-left: 1px solid  #ffffff; border-bottom: 1px solid #99bbe8;border-right: 1px solid #99bbe8; padding: 2px;  ',
									items:[	
										
										//панель - Психологическое состояние
										//BOB - 20.02.2020	
										{
											xtype: 'swscaleparameter',
											nam_begin: 'swERPEW',
											scale_name: 'bind',
											parameter_name: 'psychological',
											text_anchor: '98%',
											lbl_text: 'Психологическое состояние',
											text_width: 340,
											combo_width: 360,
											value_width: 10,
											win: this
										},
										//панель - Мышечный тонус
										new sw.Promed.SwScaleParameter({
											nam_begin: 'swERPEW',
											scale_name: 'bind',
											parameter_name: 'muscle',
											text_anchor: '98%',
											lbl_text: 'Мышечный тонус',
											text_width: 340,
											combo_width: 360,
											value_width: 10,
											win: this
										}),  
										//панель - Плач
										new sw.Promed.SwScaleParameter({
											nam_begin: 'swERPEW',
											scale_name: 'bind',
											parameter_name: 'crying',
											text_anchor: '98%',
											lbl_text: 'Плач',
											text_width: 340,
											combo_width: 360,
											value_width: 10,
											win: this
										})
									],
									//функция расчёта общего итогов
									overall_results: function(object_id){
										var psychological_Val = parseInt(this.findById('swERPEW_bind_psychological_Val').text);
										var muscle_Val = parseInt(this.findById('swERPEW_bind_muscle_Val').text);
										var crying_Val = parseInt(this.findById('swERPEW_bind_crying_Val').text);
										var sum = psychological_Val + muscle_Val + crying_Val;
										win.findById('swERPEW_EvnScaleResult').setText(sum);

										var toDo = true;
										var ParamCombos = win.findById('swERPEW_bind_ScalePanel').find('xtype', 'combo');
										for (var i in ParamCombos){
											if(ParamCombos[i].id) {
												if ((ParamCombos[i].getValue() == null) || (ParamCombos[i].getValue() == '')) {
													toDo = false;
													break;
												}	
											}
										}
										win.findById('swERPEW_EvnScaleResultText').setText('');
										if (toDo) {
											if (sum == 0)
												win.findById('swERPEW_EvnScaleResultText').setText('признаки отсутствуют');
											else if (sum <= 3)
												win.findById('swERPEW_EvnScaleResultText').setText('слабые признаки, без видимых последствий');
											else if (sum <= 6)
												win.findById('swERPEW_EvnScaleResultText').setText('умеренные признаки, в значительной степени обратимые с агрессивным течением');
											else 
												win.findById('swERPEW_EvnScaleResultText').setText('тяжёлые признаки, в значительной степени необратимые, но признаки могут уменьшиться');
										}
										//заполнение строки грида
										var EvnScalesGridRow = win.findById('swERPEW_EvnScales_Grid').getSelectionModel().getSelected();  //выбранная строка в гриде событий шкал
										//если запись в гриде новая
										if (EvnScalesGridRow.data['EvnScale_id'] == 'New_GUID_Id'){
											//	console.log('BOB_record=',record); 
											EvnScalesGridRow.data['EvnScale_Result'] = win.findById('swERPEW_EvnScaleResult').text;
											EvnScalesGridRow.data['EvnScale_ResultTradic'] = win.findById('swERPEW_EvnScaleResultText').text;																	
											EvnScalesGridRow.commit();			
										}										
									}
								},
								
								//ИТОГ События расчёта Шкалы
								{							
									layout:'border',
									width: 1300, //  1440,
									height: 30,
									xtype: 'panel',
									border:true,
									items:[	

										//Итоговая сумма
										{									
											layout:'form',
											style:'color: blue; font-weight: bold;border-top: 1px solid #99bbe8; border-left: 1px solid #99bbe8 ; border-bottom: 1px solid #ffffff;border-right: 1px solid #ffffff;',
											width: 20,
											margins: '5 5 5 5',

											region: 'west',
											items:[
												new Ext.form.Label({
													id: 'swERPEW_EvnScaleResult',
													xtype: 'label',
													text: '0'
												})					
											]
										},
										// текстовый результат - традиционной классификации
										{									
											layout:'form',
											style:'margin-left: 5px; margin-top: 5px;font-size: 12px',
											region: 'center',
											items:[
												new Ext.form.Label({
													id: 'swERPEW_EvnScaleResultText',
													text: 'jhlkjh;lkhoiuyljhlkjhlkjhlkjhlkjhlkjhlkjhlk'
												})					
											]
										},
										// кнопка сохранить
										{									
											layout:'form',
											region: 'east',
											width: 100,
											margins: '5 0 0 0',
											items:[
												new Ext.Button({
													id: 'swERPEW_EvnScaleButtonSave',
													iconCls: 'save16',
													text: 'Сохранить',
													handler: function(b,e)
													{
														this.EvnScale_Save(b,e);
													}.createDelegate(this)
													
												})
											]
										}
									]
								}
							]
						},
						
						
						//МИКРОКАЛЬКУЛЯТОРЫ
						{
							layout:'column',
							id:'swERPEW_microcalc',
							border:true,
							correction: 0,
							width: 1307, //1457,
							items:[	
								//среднее АРТЕРИАЛЬНОЕ ДАВЛЕНИЕ
								{							
									layout:'form',
									id:'swERPEW_microcalc_Pressure',
									border:true,
									style: 'border-top: 1px solid #ffffff; border-left: 1px solid  #ffffff; border-bottom: 1px solid #99bbe8;border-right: 1px solid #99bbe8; padding: 2px; ',
									width: 412,
									height: 60,
									items:[	
										{									
											layout:'column',											
											width: 410,
											items:[
												{									
													layout:'form',
													width: 330,
													items:[
														{
															xtype: 'label',
															text: 'Среднее артериальное давление',
															style:'margin-top: 5px;  margin-left: 20px; color: DarkBlue; text-align : left '
														}
													]
												},
												{									
													layout:'form',
													width: 63,
													items:[
														new Ext.Button({
															id: 'swERPEW_microcalc_Pressure_Button',
															text: 'В шкалу',
															handler: function(b,e)
															{

																//alert('uuu');
																var EvnScalesGridRowData = win.findById('swERPEW_EvnScales_Grid').getSelectionModel().getSelected().data;  //выбранная строка в гриде событий шкал

																//если новый расчёт по шкале - по коду записи из грида
																if (EvnScalesGridRowData['EvnScale_id'] == 'New_GUID_Id'){ 
																	  
																	var Ocsigen_Val =  parseFloat(win.findById('swERPEW_microcalc_Pressure_Val').text);  // значение из калькулятора
																	var ScaleParameterResult_id = 0;     // Id реквизита из шкалы
																	var Requ_Name = '';  // название экранных объектоа реквизита
																	switch (EvnScalesGridRowData['ScaleType_SysNick']) {  //какая именно шкала 
																		case 'apache':
																			Requ_Name = 'swERPEW_apache_pressure';	
																			//нахожу нужное значение по интервалам
																			if(Ocsigen_Val >= 160 ) ScaleParameterResult_id = 99;
																			else if (Ocsigen_Val >= 130 ) ScaleParameterResult_id = 100;
																			else if (Ocsigen_Val >= 110 ) ScaleParameterResult_id = 101;
																			else if (Ocsigen_Val >= 70 ) ScaleParameterResult_id = 102;
																			else if (Ocsigen_Val >= 50 ) ScaleParameterResult_id = 103;
																			else ScaleParameterResult_id = 104;
																			break;
																		default : {																		
																			Ext.MessageBox.alert('Невозможно!', 'Не выбрана соответствующая шкала!');
																			return false;
																		}
																	}
																	
																	var combo = win.findById(Requ_Name);  // комбо реквизита
																	 
																	win.findById(Requ_Name + '_Hid').setValue(ScaleParameterResult_id); // устанавливаю в скрытом поле значения нового расчёта
																	combo.setValue(ScaleParameterResult_id); // устанавливаю в combo значения нового расчёта
																	var index = combo.getStore().find('ScaleParameterResult_id',ScaleParameterResult_id);//нахожу индекс в store комбо по ScaleParameterResult_id из БД
																	var rec = combo.getStore().getAt(index);  // нахожу record по index, 
																	win.findById(Requ_Name+'_Val').setText(rec.data['ScaleParameterResult_Value']);  // устанавливаю результат в баллах
																	
																	win.findById('swERPEW_'+EvnScalesGridRowData['ScaleType_SysNick']+'_ScalePanel').overall_results(); //расчёт суммы по шкале
																	
																	
																}
																else {
																	Ext.MessageBox.alert('Невозможно!', 'Отсутствует новое событие расчёта по шкале!');
																	return false;
																}
																
																
																//нахожу нужное значение по интервалам и устанавливаю комбо
																															
															
															}.createDelegate(this)

														})
													]
												}
											]
										},
										{									
											layout:'column',
											width: 410,
											height: 40,
											items:[
												{
													xtype: 'label',													
													text: '( ',
													style:'margin-top: 5px;  margin-left: 10px; font-size: 17px; color: DarkBlue;'
												},
												{
													xtype: 'label',													
													text: 'АД сист',
													style:'margin-top: 9px;  10px; margin-right: 5px;'
												},
												new Ext.form.NumberField({
													value: 0,
													id: 'swERPEW_microcalc_Pressure_syst',
													width: 40,
													style:'margin-top: 6px;',
													listeners:{
														'change':function (field, newValue, oldValue) {
															if((newValue == null) || (newValue == ''))
																field.setValue('0');															
															this.findById('swERPEW_microcalc_Pressure').calculation();			
														}.createDelegate(this)
													}
													
												}),
												{
													xtype: 'label',													
													text: ' + 2 * ',
													style:'margin-top: 5px;  margin-left: 10px; margin-right: 5px; font-size: 17px; color: DarkBlue;'
												},
												{
													xtype: 'label',													
													text: 'АД диаст',
													style:'margin-top: 9px;  margin-right: 5px;'
												},
												new Ext.form.NumberField({
													value: 0,
													id: 'swERPEW_microcalc_Pressure_diast',
													width: 40,
													style:'margin-top: 6px;',
													listeners:{
														'change':function (field, newValue, oldValue) {
															if((newValue == null) || (newValue == ''))
																field.setValue('0');															
															this.findById('swERPEW_microcalc_Pressure').calculation();			
														}.createDelegate(this)
													}													
												}),
												{
													xtype: 'label',													
													text: ') / 3 =',
													style:'margin-top: 5px;  margin-left: 2px; margin-right: 5px; font-size: 17px; color: DarkBlue;'
												},
												{									
													layout:'form',
													style:'margin-top: 8px; color: blue; font-weight: bold; font-size: 12px; border-top: 1px solid #99bbe8; border-left: 1px solid #99bbe8 ; border-bottom: 1px solid #ffffff;border-right: 1px solid #ffffff;',
													width: 50,
													height: 19,
													items:[
														new Ext.form.Label({
															id: 'swERPEW_microcalc_Pressure_Val',
															xtype: 'label',
															text: '0'
														})					
													]
												},
												{
													xtype: 'label',													
													text: 'мм.рт.ст.',
													style:'margin-top: 9px; margin-left: 3px;'
												}
											]
										}
									],
									calculation: function() { 
										//console.log('BOB_this.findById(swERPEW_microcalc_Pressure_syst)=',this.findById('swERPEW_microcalc_Pressure_syst'));
										if ((parseInt(this.findById('swERPEW_microcalc_Pressure_syst').getValue()) > 0) && (parseInt(this.findById('swERPEW_microcalc_Pressure_diast').getValue()) > 0)) {
											var sum = (parseInt(this.findById('swERPEW_microcalc_Pressure_syst').getValue()) + 2 * parseInt(this.findById('swERPEW_microcalc_Pressure_diast').getValue())) / 3;
											sum = Math.round(sum * 100) / 100;
											this.findById('swERPEW_microcalc_Pressure_Val').setText(sum);
										}
										else
											this.findById('swERPEW_microcalc_Pressure_Val').setText(0);
									}

								},
								
								//Индекс оксигенации
								{							
									layout:'form',
									id:'swERPEW_microcalc_Ocsigen',
									border:true,
									style: 'border-top: 1px solid #ffffff; border-left: 1px solid  #ffffff; border-bottom: 1px solid #99bbe8;border-right: 1px solid #99bbe8; padding: 2px;  ',
									width: 315,
									height: 60,
									items:[	
										{									
											layout:'column',											
											width: 313,
											items:[
												{									
													layout:'form',
													width: 235,
													items:[
														{
															xtype: 'label',
															text: 'Индекс оксигенации',
															style:'margin-top: 5px;  margin-left: 20px; color: DarkBlue;'
														}
													]
												},
												{									
													layout:'form',
													width: 63,
													items:[
														new Ext.Button({
															id: 'swERPEW_microcalc_Ocsigen_Button',
															text: 'В шкалу',
															handler: function(b,e)
															{
																//alert('uuu');
																var EvnScalesGridRowData = win.findById('swERPEW_EvnScales_Grid').getSelectionModel().getSelected().data;  //выбранная строка в гриде событий шкал

																//если новый расчёт по шкале - по коду записи из грида
																if (EvnScalesGridRowData['EvnScale_id'] == 'New_GUID_Id'){ 
																	  
																	var Ocsigen_Val =  parseFloat(win.findById('swERPEW_microcalc_Ocsigen_Val').text);  // значение из калькулятора
																	var ScaleParameterResult_id = 0;     // Id реквизита из шкалы
																	var Requ_Name = '';  // название экранных объектоа реквизита
																	switch (EvnScalesGridRowData['ScaleType_SysNick']) {  //какая именно шкала 
																		case 'sofa':
																			Requ_Name = 'swERPEW_sofa_respiratory';		
																			//нахожу нужное значение по интервалам
																			if(Ocsigen_Val >= 400 ) ScaleParameterResult_id = 54;
																			else if (Ocsigen_Val >= 300 ) ScaleParameterResult_id = 55;
																			else if (Ocsigen_Val >= 200 ) ScaleParameterResult_id = 56;
																			else if (Ocsigen_Val >= 100 ) ScaleParameterResult_id = 57;
																			else ScaleParameterResult_id = 58;
																			break;
																		case 'apache':
																			Requ_Name = 'swERPEW_apache_respiration';	
																			//нахожу нужное значение по интервалам
																			if(Ocsigen_Val >= 500 ) ScaleParameterResult_id = 119;
																			else if (Ocsigen_Val >= 350 ) ScaleParameterResult_id = 120;
																			else if (Ocsigen_Val >= 200 ) ScaleParameterResult_id = 121;
																			else ScaleParameterResult_id = 122;
																			break;
																		default : {																		
																			Ext.MessageBox.alert('Невозможно!', 'Не выбрана соответствующая шкала!');
																			return false;
																		}
																	}
																	
																	var combo = win.findById(Requ_Name);  // комбо реквизита
																	 
																	win.findById(Requ_Name + '_Hid').setValue(ScaleParameterResult_id); // устанавливаю в скрытом поле значения нового расчёта
																	combo.setValue(ScaleParameterResult_id); // устанавливаю в combo значения нового расчёта
																	var index = combo.getStore().find('ScaleParameterResult_id',ScaleParameterResult_id);//нахожу индекс в store комбо по ScaleParameterResult_id из БД
																	var rec = combo.getStore().getAt(index);  // нахожу record по index, 
																	win.findById(Requ_Name+'_Val').setText(rec.data['ScaleParameterResult_Value']);  // устанавливаю результат в баллах
																	
																	win.findById('swERPEW_'+EvnScalesGridRowData['ScaleType_SysNick']+'_ScalePanel').overall_results(); //расчёт суммы по шкале
																	
																	
																}
																else {
																	Ext.MessageBox.alert('Невозможно!', 'Отсутствует новое событие расчёта по шкале!');
																	return false;
																}
																
																
																//нахожу нужное значение по интервалам и устанавливаю комбо
																
																
															}.createDelegate(this)

														})
													]
												}
											]
										},
										
										{									
											layout:'column',
											width: 313,
											height: 40,

											items:[
												{
													xtype: 'label',													
													text: 'PaO2',
													style:'   margin-left: 10px; margin-top: 9px;  10px; margin-right: 5px;'
												},
												new Ext.form.NumberField({
													value: 0,
													id: 'swERPEW_microcalc_Ocsigen_PaO2',
													width: 40,
													style:'margin-top: 6px; ',
													listeners:{
														'change':function (field, newValue, oldValue) {
															if((newValue == null) || (newValue == ''))
																field.setValue('0');															
															this.findById('swERPEW_microcalc_Ocsigen').calculation();			
														}.createDelegate(this)
													}
													
												}),
												{
													xtype: 'label',													
													text: '%',
													style:'margin-top: 9px; margin-left: 3px; '
												},
												{
													xtype: 'label',													
													text: ' / ',
													style:'margin-top: 5px;  margin-left: 10px; margin-right: 5px; font-size: 17px; color: DarkBlue;'
												},
												{
													xtype: 'label',													
													text: 'FiO2',
													style:'margin-top: 9px;  margin-right: 5px;'
												},
												new Ext.form.NumberField({
													value: 21,
													id: 'swERPEW_microcalc_Ocsigen_FiO2',
													width: 40,
													style:'margin-top: 6px;',
													listeners:{
														'change':function (field, newValue, oldValue) {
															if((newValue == null) || (newValue == ''))
																field.setValue('0');															
															this.findById('swERPEW_microcalc_Ocsigen').calculation();			
														}.createDelegate(this)
													}													
												}),
												{
													xtype: 'label',													
													text: ' =',
													style:'margin-top: 5px;  margin-left: 2px; margin-right: 5px; font-size: 17px; color: DarkBlue;'
												},
												{									
													layout:'form',
													style:'margin-top: 8px; color: blue; font-weight: bold; font-size: 12px; border-top: 1px solid #99bbe8; border-left: 1px solid #99bbe8 ; border-bottom: 1px solid #ffffff;border-right: 1px solid #ffffff;',
													width: 60,
													height: 19,
													items:[
														new Ext.form.Label({
															id: 'swERPEW_microcalc_Ocsigen_Val',
															xtype: 'label',
															text: '0'
														})					
													]
												},
												{
													xtype: 'label',													
													text: 'мм.рт.ст.',
													style:'margin-top: 9px; margin-left: 3px;'
												}
											]
										}
									],
									calculation: function() { 
										//console.log('BOB_this.findById(swERPEW_microcalc_Pressure_syst)=',this.findById('swERPEW_microcalc_Pressure_syst'));
										if ((parseInt(this.findById('swERPEW_microcalc_Ocsigen_PaO2').getValue()) > 0) && (parseInt(this.findById('swERPEW_microcalc_Ocsigen_FiO2').getValue()) > 0)) {
											var sum = parseInt(this.findById('swERPEW_microcalc_Ocsigen_PaO2').getValue()) / parseInt(this.findById('swERPEW_microcalc_Ocsigen_FiO2').getValue());
											sum = Math.round(sum * 10000) / 100;
											this.findById('swERPEW_microcalc_Ocsigen_Val').setText(sum);
										}
										else
											this.findById('swERPEW_microcalc_Ocsigen_Val').setText(0);
									}
								},
								
								//Глазго из списка
								{							
									layout:'form',
									id:'swERPEW_microcalc_glasgow',
									border:true,
									style: 'border-top: 1px solid #ffffff; border-left: 1px solid  #ffffff; border-bottom: 1px solid #99bbe8;border-right: 1px solid #99bbe8; padding: 2px;  ',
									width: 185,
									height: 60,
									items:[	
										{									
											layout:'column',											
											width: 177,
											items:[
												{									
													layout:'form',
													width: 100,
													items:[
														{
															xtype: 'label',
															text: 'Глазго из списка',
															style:'margin-top: 5px;  margin-left: 5px; color: DarkBlue;'
														}
													]
												},
												{									
													layout:'form',
													width: 73,
													items:[
																
														new Ext.Button({
															id: 'swERPEW_microcalc_glasgow_Button',
															text: 'Из списка',
															handler: function(b,e)
															{

																//alert('uuu');
																
																var EvnScalesGridRowData = win.findById('swERPEW_EvnScales_Grid').getSelectionModel().getSelected().data;  //выбранная строка в гриде событий шкал

																//если новый расчёт по шкале - по коду записи из грида
																if (EvnScalesGridRowData['EvnScale_id'] == 'New_GUID_Id'){
																	  
																	var Ocsigen_Val =  0;  // значение из калькулятора
																	var ScalesStore = win.findById('swERPEW_EvnScales_Grid').getStore().data.items;
																	for(var i in ScalesStore){
																		  
																		if ((ScalesStore[i].data) && (ScalesStore[i].data.EvnScale_id != "New_GUID_Id")){
																			if((ScalesStore[i].data.ScaleType_SysNick == "glasgow") || (ScalesStore[i].data.ScaleType_SysNick == "glasgow_ch") || (ScalesStore[i].data.ScaleType_SysNick == "glasgow_neonat")){
																				Ocsigen_Val =  parseInt(ScalesStore[i].data.EvnScale_Result);
																				break;
																			}
																		}
																	}
																	if (Ocsigen_Val == 0)
																		Ext.MessageBox.alert('Невозможно!', 'Отсутствуют сохранённые результаты по шкале Глазго!');
																	//console.log('BOB_Ocsigen_Val',Ocsigen_Val);
																	var ScaleParameterResult_id = 0;     // Id реквизита из шкалы
																	var Requ_Name = '';  // название экранных объектоа реквизита
																	switch (EvnScalesGridRowData['ScaleType_SysNick']) {  //какая именно шкала 
																		case 'sofa':
																			Requ_Name = 'swERPEW_sofa_glasgow';		
																			//нахожу нужное значение по интервалам
																			if(Ocsigen_Val == 15 ) ScaleParameterResult_id = 79;
																			else if (Ocsigen_Val >= 13 ) ScaleParameterResult_id = 80;
																			else if (Ocsigen_Val >= 10 ) ScaleParameterResult_id = 81;
																			else if (Ocsigen_Val >= 6 ) ScaleParameterResult_id = 82;
																			else ScaleParameterResult_id = 83;
																			break;
																		case 'apache':
																			Requ_Name = 'swERPEW_apache_glasgow';	
																			//нахожу нужное значение по интервалам
																			ScaleParameterResult_id = 186 - Ocsigen_Val;
																			break;
																		default : {																		
																			Ext.MessageBox.alert('Невозможно!', 'Не выбрана соответствующая шкала!');
																			return false;
																		}
																	}
																	
																	var combo = win.findById(Requ_Name);  // комбо реквизита
																	 
																	win.findById(Requ_Name + '_Hid').setValue(ScaleParameterResult_id); // устанавливаю в скрытом поле значения нового расчёта
																	combo.setValue(ScaleParameterResult_id); // устанавливаю в combo значения нового расчёта
																	var index = combo.getStore().find('ScaleParameterResult_id',ScaleParameterResult_id);//нахожу индекс в store комбо по ScaleParameterResult_id из БД
																	var rec = combo.getStore().getAt(index);  // нахожу record по index, 
																	win.findById(Requ_Name+'_Val').setText(rec.data['ScaleParameterResult_Value']);  // устанавливаю результат в баллах
																	
																	win.findById('swERPEW_'+EvnScalesGridRowData['ScaleType_SysNick']+'_ScalePanel').overall_results(); //расчёт суммы по шкале
																	
																	
																}
																else {
																	Ext.MessageBox.alert('Невозможно!', 'Отсутствует новое событие расчёта по шкале!');
																	return false;
																}


															}.createDelegate(this)

														})
													]
												}
											]
										}
									]
								}
							]
						}
					]
				}),


//Панель реанимационных мероприятий
				new sw.Promed.Panel({
					title:'3. Реанимационные мероприятия',
					id:'swERPEW_ReanimatAction_Panel',
					autoHeight:true,
					border:true,
					collapsible:true,
					collapsed:true,
					isLoaded:false,					
					layout:'form',
					style:'margin-bottom: 0.5em; ',
					autoScroll:true,
					bodyStyle:'padding-top: 0.5em; border-top: 1px none #99bbe8; border-left: 1px solid #99bbe8; border-bottom: 1px solid #ffffff;border-right: 1px solid #ffffff;',
					RA_Drug: {
						vazopressors: [1,2,3,4,11,15],
						epidural_analgesia: [5],
						antifungal_therapy: [6,7,8],
						catheterization_veins: [9,10,12], //BOB - 03.11.2018
						invasive_hemodynamics: [9,10,12], //BOB - 03.11.2018
						card_pulm: [1,13,14],   //BOB - 22.02.2019
						sedation: [16,17,18,19,20]  //BOB - 05.03.2020
					},
					RA_Veins: {
						catheterization_veins: [1,2,3,4,5,6], //BOB - 03.11.2018
						invasive_hemodynamics: [7,8,9,10] //BOB - 03.11.2018
					},
					RA_Method: {
						nutrition: [1,2,3,4,5],  //BOB - 11.09.2019
						lung_ventilation: 'A16.09.011',
						hemodialysis: 'A18.05',
						endocranial_sensor: 'A16.23.037',
						catheterization_veins: 'A11.12.001',
						epidural_analgesia: 'B01.003.004.006'						
					},
					//observation_saturation: [], //массив данных по наблюдению сатурации, чтобы строить диаграмму
					rate_records: {}, //BOB - 03.11.2018 - массив массивов результатов измерений
					ReanimDrug: {}, //BOB - 05.03.2020 - массив массивов Лекарственных средств
					
					listeners:{
						'expand':function (panel) {
							//загрузка таблицы при первом открытии панели
							if (panel.isLoaded === false) {
								panel.isLoaded = true;

								var Datas =  [];
								//установка справочника способов оплаты 
								win.findById('swERPEW_RA_PayType').setValue(9);

								//фильтр комбов медикаментов в сердечно-лёгочной
								var filterReanimatDrug = win.findById('swERPEW_ReanimatAction_Panel').RA_Drug['card_pulm'];
								win.findById('swERPEW_RA_CardPulm_Drug_1').getStore().clearFilter();
								win.findById('swERPEW_RA_CardPulm_Drug_2').getStore().clearFilter();
								if (filterReanimatDrug){
									win.findById('swERPEW_RA_CardPulm_Drug_1').getStore().filterBy(function (rec) {
										return rec.get('ReanimDrugType_id').inlist(filterReanimatDrug);
									});
									win.findById('swERPEW_RA_CardPulm_Drug_2').getStore().filterBy(function (rec) {
										return rec.get('ReanimDrugType_id').inlist(filterReanimatDrug);
									});
								}
								
								//сортировка справочника методов, уж не знаю почемУ, но для того чтобы сработала сортировка приходится переключать атрибуты сортировки сначала на ненужные, а потом на нужные
								win.findById('swERPEW_RA_Method').store.sort("EvnReanimatActionMethod_id", "DESC"); // BOB - 21.03.2019 
								win.findById('swERPEW_RA_Method').store.sort("EvnReanimatActionMethod_Code", "ASC"); // BOB - 21.03.2019 

								//BOB - 29.02.2020 //загрузка комбо аппаратов ИВЛ
								var IVLAppar = ['Aeros','Avea','Bear Cub 750','Chirolog','Datex Ohmeda','Dixion','Draeger Evita','Draeger Savina','Dräger Carina','Drager Babylog 8000','Engstrom','eVENT Medical','Evita XL','Hamilton',
												'Infant Flow','Kontron','Maquet Servo','Maquet','Mindray','Neumovent','Newport','Puritan Bennet','Sensor Medics','Servo','Sophia',
												'Vela','Zisline','Авента','РО-6','ФАЗА-21'];
								if (this.isNeonatal(this.findById('swERPEW_ReanimatAgeGroup').getValue())) 
									IVLAppar = ['Avea','Infant Flow','Sensor Medics 3100A','Servo I'];
								var Datas =  [];
								for (var i in IVLAppar) { // цикл по значениям параметра
									Datas[i]= [ IVLAppar[i], IVLAppar[i] ];
								};
								win.findById('swERPEW_RA_IVLParameter_Apparat').getStore().loadData(Datas);
								//BOB - 29.02.2020
								//BOB - 03.11.2018
								//установка свойства Renderer на поле таблицы swERPEW_RA_Rate_Grid для графического отображения значения измерения
								win.findById('swERPEW_RA_Rate_Grid').getGrid().getColumnModel( ).setRenderer( 3, function (value,p,row) {
									//console.log('BOB_row=',row);
									var Rate_PerCent = row.get('Rate_PerCent');
									var ret = '<div style="background-color:lime; width:' + Rate_PerCent + '%">' + value + '</div>' ;	
									return 	ret;
								});
								//BOB - 03.11.2018
								//загрузка грида реанимационных событий
								panel.findById('swERPEW_ReanimatAction_Grid').getStore().load({
									params:{
										EvnReanimatAction_pid:this.EvnReanimatPeriod_id
									}
								});	
							}
							panel.doLayout();
							//BOB - 04.07.2019 - убрал управление кнопками
						}.createDelegate(this)
					},
					
					refresh: function() {
						this.ReanimDrug = {}; //BOB - 05.03.2020
						this.findById('swERPEW_ReanimatAction_Grid').getStore().reload();	//перезагрузка грида 
						Ext.select('[id$="RA_Panel"]').setStyle('display', 'none'); // панель делаю неактивной
						//BOB - 04.07.2019 - убрал управление кнопками
						this.rate_records = {};
					},

					items:[

						//Панель - Таблица Реанимационные мероприятия
						{
							height:211,
							layout:'border',
							border:true,
							style: 'border-top: 1px solid #99bbe8; border-left: 1px solid #99bbe8; border-bottom: 1px solid #ffffff;border-right: 1px solid #ffffff; padding: 0px; ',
							items:[
								//Таблица Реанимационные мероприятия
								//new Ext.grid.GridPanel({
								new Ext.grid.EditorGridPanel({
									id: 'swERPEW_ReanimatAction_Grid',
									frame: false,
									border: false,
									loadMask: true,
									region: 'center',
									stripeRows: true,
									height:200,
									columns: [
										{dataIndex: 'EvnReanimatAction_setDate', header: 'Дата', hidden: false, renderer: Ext.util.Format.dateRenderer('d.m.Y'), resizable: false, sortable: false, width: 80}, 
										{dataIndex: 'EvnReanimatAction_setTime', header: 'Время', hidden: false, resizable: false, sortable: false, width: 60 },
										{dataIndex: 'EvnReanimatAction_disDate', header: 'Дата оконч', hidden: false, renderer: Ext.util.Format.dateRenderer('d.m.Y'), resizable: false, sortable: false, width: 80},
										{dataIndex: 'EvnReanimatAction_disTime', header: 'Время оконч', hidden: false, resizable: false, sortable: false, width: 60 },
										{dataIndex: 'ReanimatActionType_Name', header: 'Наименование мероприятия', hidden: false, resizable: true, sortable: false, width: 300, editor: new Ext.form.TextField()},//new Ext.form.TextArea()
										{dataIndex: 'EvnReanimatAction_MethodName', header: 'Метод', hidden: false, resizable: true, sortable: false, id: 'EvnReanimatAction_MethodName', width: 400 },
										{dataIndex: 'EvnReanimatAction_Medicoment', header: 'Медикамент', hidden: false, resizable: true, sortable: false, width: 100 }
										
									],
									autoExpandColumn: 'EvnReanimatAction_MethodName',
									autoExpandMin: 400,
									sm:new Ext.grid.RowSelectionModel({
											listeners:{
												'rowselect':function (sm, rowIndex, record) {
													this.EvnReanimatAction_view();  //загрузка просмотра/редактирования мероприятия													
												}.createDelegate(this)
											}
										}),
									store:new Ext.data.Store({
										autoLoad:false,
										listeners:{
											'load':function (store, records, index) {
												
//												// формрирвание массива данных по наблюдению сатурации, чтобы строить диаграмму
//												var observation_saturation = [];
//												var Store = this.findById('swERPEW_ReanimatAction_Grid').getStore().data.items;
//												var j = 0;												
//												for(var i in Store) {
//													if((Store[i].data)&& (Store[i].data['ReanimatActionType_SysNick']) == "observation_saturation") {													
//														observation_saturation[j++] =  {EvnReanimatAction_setDate: Store[i].data['EvnReanimatAction_setDate'].format("d.m.y"),
//																						EvnReanimatAction_setTime: Store[i].data['EvnReanimatAction_setTime'],
//																						EvnReanimatAction_ObservValue: Store[i].data['EvnReanimatAction_ObservValue']};														
//													}
//												}
//												observation_saturation.reverse();
//												this.findById('swERPEW_ReanimatAction_Panel').observation_saturation = observation_saturation;
												//установка выбранной записи грида

												if(win.findById('swERPEW_ReanimatAction_Panel').isLoaded === true){ //BOB - 09.09.2019
													if (store.getCount() == 0) {
														LoadEmptyRow(this.findById('swERPEW_ReanimatAction_Grid'));
													} else {
														this.findById('swERPEW_ReanimatAction_Grid').getSelectionModel().selectRow(this.ActionGridLoadRawNum); 	//установка выбранности на первой строке грда
														this.ActionGridLoadRawNum = 0;
													}
												}

											}.createDelegate(this)
										},
										reader:new Ext.data.JsonReader({
											id:'EvnReanimatAction_id'
										},
										[
											{mapping:'EvnReanimatAction_id', name:'EvnReanimatAction_id', type:'int'},
											{mapping:'EvnReanimatAction_pid', name:'EvnReanimatAction_pid', type:'int'},
											{mapping:'Person_id', name:'Person_id', type:'int'},
											{mapping:'PersonEvn_id', name:'PersonEvn_id', type:'int'},
											{mapping:'Server_id', name:'Server_id', type:'int'},
											{mapping:'EvnReanimatAction_setDate', name:'EvnReanimatAction_setDate', type:'date',dateFormat:'d.m.Y' },
											{mapping:'EvnReanimatAction_setTime', name:'EvnReanimatAction_setTime', type:'string'},
											{mapping:'EvnReanimatAction_disDate', name:'EvnReanimatAction_disDate', type:'date', dateFormat:'d.m.Y'},
											{mapping:'EvnReanimatAction_disTime', name:'EvnReanimatAction_disTime', type:'string'},
											{mapping:'ReanimatActionType_id', name:'ReanimatActionType_id',type:'int'},
											{mapping:'ReanimatActionType_SysNick', name:'ReanimatActionType_SysNick', type:'string'},
											{mapping:'ReanimatActionType_Name', name:'ReanimatActionType_Name', type:'string'},
											{mapping:'UslugaComplex_id', name:'UslugaComplex_id', type:'int'},
											{mapping:'EvnUsluga_id', name:'EvnUsluga_id', type:'int'},
											{mapping:'ReanimDrugType_id', name:'ReanimDrugType_id', type:'int'},
											{mapping:'EvnReanimatAction_DrugDose', name:'EvnReanimatAction_DrugDose', type:'float'},
											{mapping:'EvnDrug_id', name:'EvnDrug_id', type:'int' },
											{mapping:'EvnReanimatAction_Medicoment', name:'EvnReanimatAction_Medicoment', type:'string'},
											{mapping:'EvnReanimatAction_MethodCode', name:'EvnReanimatAction_MethodCode', type:'string'},
											{mapping:'EvnReanimatAction_MethodName', name:'EvnReanimatAction_MethodName', type:'string'},
											{mapping:'PayType_id', name:'PayType_id', type:'int'},
											//{mapping:'EvnReanimatAction_ObservValue', name:'EvnReanimatAction_ObservValue', type:'float'},											
											{mapping:'ReanimatCathetVeins_id', name:'ReanimatCathetVeins_id', type:'int'},
											{mapping:'CathetFixType_id', name:'CathetFixType_id', type:'int'},
											{mapping:'EvnReanimatAction_CathetNaborName', name:'EvnReanimatAction_CathetNaborName', type:'string'},
											{mapping:'EvnReanimatAction_DrugUnit', name:'EvnReanimatAction_DrugUnit', type:'string'},					 //BOB - 23.04.2018
											{mapping:'EvnReanimatAction_MethodTxt', name:'EvnReanimatAction_MethodTxt', type:'string'},					 //BOB - 03.11.2018
											{mapping:'EvnReanimatAction_NutritVol', name:'EvnReanimatAction_NutritVol', type:'int'},					 //BOB - 03.11.2018
											{mapping:'EvnReanimatAction_NutritEnerg', name:'EvnReanimatAction_NutritEnerg', type:'int'},				 //BOB - 03.11.2018
											{mapping:'MilkMix_id', name:'MilkMix_id', type:'int'}														//BOB - 15.04.2020	
											
										]),
										url:'/?c=EvnReanimatPeriod&m=loudEvnReanimatActionGrid'
									}),
									tbar:new sw.Promed.Toolbar({
										buttons:[
											{
												id: 'swERPEW_EvnReanimatActionAdd',
												handler:function () {
													this.EvnReanimatAction_Add();
												}.createDelegate(this),
												iconCls:'add16',
												text:'Добавить'
											},
											//кнопка редактирования //BOB - 04.07.2019
											{
												id: 'swERPEW_EvnReanimatActionEdit',
												handler:function () {
													this.EvnReanimatAction_Edit();
												}.createDelegate(this),
												iconCls:'edit16',
												text:'Редактировать'
											},
											//кнопка удаления
											{
												id: 'swERPEW_EvnReanimatActionDel',
												handler:function () {
													this.EvnReanimatAction_Del();
												}.createDelegate(this),
												iconCls:'delete16',
												text:'Удалить'
											},
											{
												id: 'swERPEW_EvnReanimatActionRefresh',
												name: 'swERPEW_EvnReanimatActionRefresh',
												handler:function () {
													this.findById('swERPEW_ReanimatAction_Panel').refresh();
												}.createDelegate(this),
												iconCls:'refresh16',
												text:'Обновить'
											},
//											//кнопка печати документа катетеризации
//											{
//												id: 'swERPEW_EvnReanimatActionButtonPrint',
//												name: 'swERPEW_EvnReanimatActionButtonPrint',
//												handler: function(){
//													this.EvnReanimatAction_Print();
//												}.createDelegate(this),
//												iconCls: 'print16',
//												text: 'Печать'
//
//											},
											//кнопка печати документа катетеризации
											{
												id: 'swERPEW_EvnReanimatActionButtonPrintUp',
												name: 'swERPEW_EvnReanimatActionButtonPrintUp',
												handler: function(){
													var EvnRAGridRowData = this.findById('swERPEW_ReanimatAction_Grid').getSelectionModel().getSelected().data;  //выбранная строка в гриде событий реанимационных мероприятий
													if (EvnRAGridRowData['ReanimatActionType_SysNick'] == 'card_pulm')
														this.CardPulm_Print();
													else
														this.EvnReanimatAction_PrintUpDoun(0);
												}.createDelegate(this),
												iconCls: 'print16',
												text: 'Печать верх'

											},
											//кнопка печати документа катетеризации
											{
												id: 'swERPEW_EvnReanimatActionButtonPrintDoun',
												name: 'swERPEW_EvnReanimatActionButtonPrintDoun',
												handler: function(){
													var EvnRAGridRowData = this.findById('swERPEW_ReanimatAction_Grid').getSelectionModel().getSelected().data;  //выбранная строка в гриде событий реанимационных мероприятий
													if (EvnRAGridRowData['ReanimatActionType_SysNick'] == 'card_pulm')
														this.CardPulm_Print();
													else
														this.EvnReanimatAction_PrintUpDoun(1);
												}.createDelegate(this),
												iconCls: 'print16',
												text: 'Печать низ'

											}

										]
									}),
									//BOB - 25.12.2019
									keys: [{
										key: [
											Ext.EventObject.F3,
										],
										fn: function(inp, e) {
											e.stopEvent();
											e.returnValue = false;
											var grid = this.findById('swERPEW_ReanimatAction_Grid');

											switch ( e.getKey() ) {
												case Ext.EventObject.F3:
													if ( e.altKey ) {
														var params = new Object();
														params['key_id'] = grid.getSelectionModel().getSelected().data.EvnReanimatAction_id;
														params['key_field'] = 'EvnReanimatAction_id';
														getWnd('swAuditWindow').show(params);
													}
													break;
											}
										},
										scope: this,
										stopEvent: true
									}]
								})						
							]
						},
						
						//Панель - Событие Реанимационного мероприятия		
						{
							//	xtype: 'panel', 
							id: 'swERPEW_GeneralReanimatActionPanel',
							layout:'form',
							border:true,
							width: 1307,  //1457,
							style: 'border-top: 1px solid #99bbe8; border-left: 1px solid #99bbe8; border-bottom: 1px solid #ffffff;border-right: 1px solid #ffffff; padding: 2px 0px 0px 0px; margin-top: 10px;',
							items:[

								//ШАПКА События Реанимационного мероприятия
								{							
									layout:'border',
									width: 1300, // 1456,
									height: 23,
									xtype: 'panel',
									border:true,
									items:[	
										//панель - combo Тип Реанимационного мероприятия    
										{	
											layout:'form',
											width: 550,
											labelWidth: 110,
											border:true,
											region: 'west',
											items:[	
												//combo Тип Реанимационного мероприятия
												{
													id: 'swERPEW_ReanimatActionType',
													hiddenName: 'ReanimatActionType',									
													xtype: 'swreanimatactsctypecombo',
													comboSubject: 'ReanimatActionType',
													suffix: '',
													fieldLabel: langs('Вид мероприятия'),
													labelSeparator: '',
													width: 400,
													allowBlank: false,
													lastQuery: '',

													listeners: {														
														'render': function(combo) {
															combo.getStore().load();
														},
														'select': function(combo, record, index, from) {
															//console.log('BOB_select_from=',from);
															var SysNick = '';
															var EvnRAGridRowData = win.findById('swERPEW_ReanimatAction_Grid').getSelectionModel().getSelected();  //выбранная строка в гриде событий шкал
														 
															//если выбрана не пустая строка
															if (record) { //  (index > 0){
																Ext.select('#swERPEW_RA_Base_Data').setStyle('display', 'block');  // делаю видимой
																
																SysNick = record.data.ReanimatActionType_SysNick;
																																	
																//фильтрация справочников методов реанимационных мероприятий 
																var filterReanimatActionMethod = win.findById('swERPEW_ReanimatAction_Panel').RA_Method[SysNick];
																win.findById('swERPEW_RA_Method').getStore().clearFilter();
																if (filterReanimatActionMethod) {
																	win.findById('swERPEW_RA_Method').getStore().filterBy(function (rec) {
																		if (SysNick == 'nutrition') {
																			return (rec.get('EvnReanimatActionMethod_Code') && rec.get('EvnReanimatActionMethod_Code').inlist(filterReanimatActionMethod));
																		} else {
																			return (String(rec.get('EvnReanimatActionMethod_Code')).indexOf(filterReanimatActionMethod) > -1);
																		}
																	});
																}
																
																//фильтр комбо вен / артерий
																var filterReanimatCathetVeins = win.findById('swERPEW_ReanimatAction_Panel').RA_Veins[SysNick];
																win.findById('swERPEW_RA_CathetVeins').getStore().clearFilter();
																if (filterReanimatCathetVeins){
																	win.findById('swERPEW_RA_CathetVeins').getStore().filterBy(function (rec) {
																		return rec.get('ReanimatCathetVeins_id').inlist(filterReanimatCathetVeins);
																	});
																}
																
																
																Ext.select('#swERPEW_RA_Method_Panel').setStyle('display', 'none');// делаю невидимыми комбо метод услуги	
																Ext.select('#swERPEW_RA_PayType_Panel').setStyle('display', 'none');// делаю невидимыми комбо способ оплаты	
																win.findById('swERPEW_EvnReanimatAction_disDate_Pnl').setVisible(false);// делаю невидимыми время окончания
																win.findById('swERPEW_EvnReanimatAction_disTime_Pnl').setVisible(false);// делаю невидимыми дату окончания	
																Ext.select('#swERPEW_RA_Drug_Panel').setStyle('display', 'none');// делаю невидимыми панель медикаментов с содержимым
																Ext.select('#swERPEW_RA_Print_Cathet_Panel').setStyle('display', 'none');// делаю невидимыми панель доп рнквизитов для печати катетеризации вен
																//Ext.select('#swERPEW_RA_Observ_Panel').setStyle('display', 'none');// делаю невидимыми панель наблюдений с содержимым
																Ext.select('#swERPEW_RA_ParamIVL_Panel').setStyle('display', 'none');// делаю невидимой панель режимов ИВЛ //BOB - 03/11/2018	
																Ext.select('#swERPEW_RA_Rate_Panel').setStyle('display', 'none');// делаю невидимой панель отображения измерений //BOB - 03/11/2018	
																win.findById('swERPEW_RA_MethodTxt').setVisible(false);// делаю невидимым метод - вариант пользователя  //BOB - 03/11/2018
																Ext.select('#swERPEW_RA_Nutrit_Panel').setStyle('display', 'none');// делаю невидимой панель объёма и энеогии питания //BOB - 03/11/2018
																Ext.select('#swERPEW_RA_CardPulm_Panel').setStyle('display', 'none');// делаю невидимой панель Сердечно-лёгочнjq реанимациb //BOB - 22/02/2019

																switch (SysNick) {
																	case 'nutrition':
																		Ext.select('#swERPEW_RA_Method_Panel').setStyle('display', 'block');// делаю видимыми комбо метод услуги	
																		win.findById('swERPEW_EvnReanimatAction_disDate_Pnl').setVisible(true);// делаю видимым время окончания
																		win.findById('swERPEW_EvnReanimatAction_disTime_Pnl').setVisible(true);// делаю видимым дату окончания	
																		Ext.select('#swERPEW_RA_Nutrit_Panel').setStyle('display', 'block');// делаю видимой панель объёма и энеогии питания //BOB - 03/11/2018	
																		if(EvnRAGridRowData.data['UslugaComplex_id'] == 4)
																			win.findById('swERPEW_RA_MethodTxt').setVisible(true);// делаю видимым метод - вариант пользователя  //BOB - 03/11/2018
																		//BOB - 15.04.2020
																		if(win.isNeonatal(win.findById('swERPEW_ReanimatAgeGroup').getValue())) 
																			Ext.select('#swERPEW_RA_MilkMix_panel').setStyle('display', 'block');// делаю видимой панель молочных смесей
																		else
																			Ext.select('#swERPEW_RA_MilkMix_panel').setStyle('display', 'none');// делаю невидимой панель молочных смесей
																		break;
										
																	case 'lung_ventilation':
																		Ext.select('#swERPEW_RA_Method_Panel').setStyle('display', 'block');// делаю видимыми комбо метод услуги	
																		Ext.select('#swERPEW_RA_PayType_Panel').setStyle('display', 'block');// делаю видимыми комбо способ оплаты	
																		Ext.select('#swERPEW_RA_ParamIVL_Panel').setStyle('display', 'block');// делаю видимой панель режимов ИВЛ //BOB - 03/11/2018	
																		win.findById('swERPEW_EvnReanimatAction_disDate_Pnl').setVisible(true);// делаю видимым время окончания
																		win.findById('swERPEW_EvnReanimatAction_disTime_Pnl').setVisible(true);// делаю видимым дату окончания	
																		break;
																	case 'hemodialysis':
																		Ext.select('#swERPEW_RA_Method_Panel').setStyle('display', 'block');// делаю видимыми комбо метод услуги	
																		Ext.select('#swERPEW_RA_PayType_Panel').setStyle('display', 'block');// делаю видимыми комбо способ оплаты		
																		win.findById('swERPEW_EvnReanimatAction_disDate_Pnl').setVisible(true);// делаю видимым время окончания
																		win.findById('swERPEW_EvnReanimatAction_disTime_Pnl').setVisible(true);// делаю видимым дату окончания	
																		break;
																	case 'endocranial_sensor':
																		Ext.select('#swERPEW_RA_Method_Panel').setStyle('display', 'block');// делаю видимыми комбо метод услуги	
																		Ext.select('#swERPEW_RA_PayType_Panel').setStyle('display', 'block');// делаю видимыми комбо способ оплаты	
																		Ext.select('#swERPEW_RA_Rate_Panel').setStyle('display', 'block');// делаю видимой панель отображения измерений //BOB - 03/11/2018	
																		win.findById('swERPEW_EvnReanimatAction_disDate_Pnl').setVisible(true);// делаю видимым время окончания
																		win.findById('swERPEW_EvnReanimatAction_disTime_Pnl').setVisible(true);// делаю видимым дату окончания	
																		win.findById('swERPEW_RA_Rate_Grid').getGrid().getColumnModel( ).setHidden( 4, false );
																		win.findById('swERPEW_RA_Rate_Grid').getGrid().setWidth(1299); 				
																		break;
																	case 'invasive_hemodynamics':
																		win.findById('swERPEW_EvnReanimatAction_disDate_Pnl').setVisible(true);// делаю видимым время окончания
																		win.findById('swERPEW_EvnReanimatAction_disTime_Pnl').setVisible(true);// делаю видимым дату окончания	
																		Ext.select('#swERPEW_RA_Rate_Panel').setStyle('display', 'block');// делаю видимой панель отображения измерений //BOB - 03/11/2018	
																		win.findById('swERPEW_RA_Rate_Grid').getGrid().getColumnModel( ).setHidden( 4, true );
																		win.findById('swERPEW_RA_Rate_Grid').getGrid().setWidth(359); 
																		
																		Ext.select('#swERPEW_RA_Drug_Panel').setStyle('display', 'block');// делаю видимыми панель медикаментов с содержимым
																		Ext.select('#swERPEW_RA_Print_Cathet_Panel').setStyle('display', 'block');// делаю видимыми панель доп реквизитов 
																		win.findById('swERPEW_RA_CathetVeins').setFieldLabel('Артерия');
																		break;
																	case 'vazopressors':
																	case 'antifungal_therapy':
																	case 'sedation': //BOB - 05.03.2020
																		win.findById('swERPEW_EvnReanimatAction_disDate_Pnl').setVisible(true);// делаю видимым время окончания  //BOB - 07.05.2020
																		win.findById('swERPEW_EvnReanimatAction_disTime_Pnl').setVisible(true);// делаю видимым дату окончания	 //BOB - 07.05.2020
																		Ext.select('#swERPEW_RA_Drug_Panel').setStyle('display', 'block');// делаю видимыми панель медикаментов с содержимым																		
																		break;
																	case 'epidural_analgesia':
																		Ext.select('#swERPEW_RA_Method_Panel').setStyle('display', 'block');// делаю видимыми комбо метод услуги	
																		Ext.select('#swERPEW_RA_PayType_Panel').setStyle('display', 'block');// делаю видимыми комбо способ оплаты		
																		Ext.select('#swERPEW_RA_Drug_Panel').setStyle('display', 'block');// делаю видимыми панель медикаментов с содержимым
																		break;
																	case 'catheterization_veins':
																		Ext.select('#swERPEW_RA_Method_Panel').setStyle('display', 'block');// делаю видимыми комбо метод услуги	
																		Ext.select('#swERPEW_RA_PayType_Panel').setStyle('display', 'block');// делаю видимыми комбо способ оплаты		
																		Ext.select('#swERPEW_RA_Drug_Panel').setStyle('display', 'block');// делаю видимыми панель медикаментов с содержимым
																		Ext.select('#swERPEW_RA_Print_Cathet_Panel').setStyle('display', 'block');// делаю видимыми панель доп реквизитов 
																		win.findById('swERPEW_RA_CathetVeins').setFieldLabel('Вена');
																		break;
																	case 'observation_saturation':
																		//win.Observ_Diagram();
																		//Ext.select('#swERPEW_RA_Observ_Panel').setStyle('display', 'block');// делаю видимыми панель наблюдений с содержимым
																		win.findById('swERPEW_EvnReanimatAction_disDate_Pnl').setVisible(true);// делаю видимым время окончания
																		win.findById('swERPEW_EvnReanimatAction_disTime_Pnl').setVisible(true);// делаю видимым дату окончания	
																		Ext.select('#swERPEW_RA_Rate_Panel').setStyle('display', 'block');// делаю видимой панель отображения измерений //BOB - 03/11/2018	
																		win.findById('swERPEW_RA_Rate_Grid').getGrid().getColumnModel( ).setHidden( 4, true );
																		win.findById('swERPEW_RA_Rate_Grid').getGrid().setWidth(359); 				
																		break;
																	case 'card_pulm':
																		Ext.select('#swERPEW_RA_CardPulm_Panel').setStyle('display', 'block');// делаю видимой панель Сердечно-лёгочнjq реанимациb //BOB - 22/02/2019
																	case 'pronpos':
																	case 'mioplegia':
																	case 'eksoksigen':
																		win.findById('swERPEW_EvnReanimatAction_disDate_Pnl').setVisible(true);
																		win.findById('swERPEW_EvnReanimatAction_disTime_Pnl').setVisible(true);	
																		break;
																}
																
																
															}
															else { //выбрана пустая строка																	
																Ext.select('#swERPEW_RA_Base_Data').setStyle('display', 'none');// делаю невидимыми все панели мероприятий, которые рисовались	
															}
															
															//заполнение строки грида
															//если запуск события по выбору в комбо (а не из процедуры EvnReanimatAction_view)	
															if (!(from) || (from == '')) {	
																win.findById('swERPEW_RA_Method').setValue(null);
																var ReanimDrug = win.findById('swERPEW_ReanimatAction_Panel').ReanimDrug['New_GUID_Id'];//BOB - 05.03.2020
																ReanimDrug['-1'].ReanimDrug_Unit = 'мг';//BOB - 05.03.2020
																ReanimDrug['-1'].ReanimDrugType_id = null;//BOB - 05.03.2020
																ReanimDrug['-1'].ReanimDrug_Dose = 0;//BOB - 05.03.2020
																for (var i in ReanimDrug) { //BOB - 05.03.2020 удаляю записи в объекте кроме первого
																	if (i != -1) delete ReanimDrug[i];
																}

																win.findById('swERPEW_RA_CathetVeins').setValue(null);
																//win.findById('swERPEW_RA_Observ_Value').setValue(null); //результат наблюдения


																switch (SysNick) {
																	case 'vazopressors':
																	case 'sedation': //BOB - 05.03.2020
																		win.findById('swERPEW_RA_PayType').setValue(0); //устанавливаю пустое значение комбо способ оплаты	
																		ReanimDrug['-1'].ReanimDrug_Unit = 'мкг/кг/мин';//BOB - 05.03.2020
																		win.ReanimDrug_Build(ReanimDrug, SysNick, combo.id);//BOB - 05.03.2020
																		break;
																	case 'nutrition':
																		win.findById('swERPEW_RA_MethodTxt').setValue(''); //метод - вариант пользователя  //BOB - 03.11.2018	
																		break;
																	case 'antifungal_therapy':
																	case 'invasive_hemodynamics':
																		win.findById('swERPEW_RA_PayType').setValue(0); //устанавливаю пустое значение комбо способ оплаты	
																		win.ReanimDrug_Build(ReanimDrug, SysNick, combo.id);//BOB - 05.03.2020
																		break;
																	case 'observation_saturation':
																		win.findById('swERPEW_RA_PayType').setValue(0); //устанавливаю пустое значение комбо способ оплаты	
																		//win.findById('swERPEW_RA_Observ_Value').setValue(0); //результат наблюдения
																		break;
																	case 'lung_ventilation':
																	case 'hemodialysis':      //BOB - 04.07.2019
																		win.findById('swERPEW_RA_PayType').setValue(9); //устанавливаю значение по умолчанию комбо способ оплаты
																		break;
																	case 'catheterization_veins':
																		win.findById('swERPEW_RA_PayType').setValue(9); //устанавливаю значение по умолчанию комбо способ оплаты	
																		win.ReanimDrug_Build(ReanimDrug, SysNick, combo.id);//BOB - 05.03.2020
																		break;
																	case 'endocranial_sensor':
																		win.findById('swERPEW_RA_PayType').setValue(9); //устанавливаю значение по умолчанию комбо способ оплаты	
																		
																		win.findById('swERPEW_RA_Method').setValue(win.findById('swERPEW_RA_Method').getStore().data.items[0].data['EvnReanimatActionMethod_id']); //BOB - 21.03.2018 устанавливаю значение по умолчанию комбо метода (единственное в данном случае)	
																		break;
																	case 'epidural_analgesia':
																		win.findById('swERPEW_RA_PayType').setValue(9); //устанавливаю значение по умолчанию комбо способ оплаты	
																		win.findById('swERPEW_RA_Method').setValue(win.findById('swERPEW_RA_Method').getStore().data.items[0].data['EvnReanimatActionMethod_id']); //BOB - 21.03.2018 устанавливаю значение по умолчанию комбо метода (единственное в данном случае)	
																		ReanimDrug['-1'].ReanimDrugType_id = 5;//BOB - 05.03.2020
																		win.ReanimDrug_Build(ReanimDrug, SysNick, combo.id);//BOB - 05.03.2020
																		break;
																}
																//если record существует
																if(record){
																	//реанимационное мероприятие: sysnick, название
																	EvnRAGridRowData.data['ReanimatActionType_SysNick'] = record.data.ReanimatActionType_SysNick;
																	EvnRAGridRowData.data['ReanimatActionType_Name'] = record.data.ReanimatActionType_Name;
																	if (record.data.ReanimatActionType_id)
																		EvnRAGridRowData.data['ReanimatActionType_id'] = record.data.ReanimatActionType_id;
																	else 
																		EvnRAGridRowData.data['ReanimatActionType_id'] = 0;
																	
																	//метод  //BOB - 19.02.2018
																	var MethodCombo = win.findById('swERPEW_RA_Method');
																	EvnRAGridRowData.data['UslugaComplex_id'] = MethodCombo.value;
																	index = MethodCombo.getStore().find('EvnReanimatActionMethod_id',MethodCombo.value); //нахожу индекс в store комбо
																	var rec = MethodCombo.getStore().getAt(index);  // нахожу record по индексу
																	if (rec) {
																		EvnRAGridRowData.data['EvnReanimatAction_MethodCode'] = rec.data['EvnReanimatActionMethod_Code'];
																		EvnRAGridRowData.data['EvnReanimatAction_MethodName'] = rec.data['EvnReanimatActionMethod_Name'];
																	}
																	else {
																		EvnRAGridRowData.data['EvnReanimatAction_MethodCode'] = '';
																		EvnRAGridRowData.data['EvnReanimatAction_MethodName'] = '';
																	}
																	
																	//лекарственное средства //BOB - 05.03.2020
																	EvnRAGridRowData.data['ReanimDrugType_id'] = ReanimDrug['-1'].ReanimDrugType_id;
																	if(ReanimDrug['-1'].ReanimDrugType_id){
																		EvnRAGridRowData.data['EvnReanimatAction_Medicoment'] = win.ERPEW_NSI.ReanimDrugType[ReanimDrug['-1'].ReanimDrugType_id - 1].ReanimDrugType_Name;
																	} else {
																		EvnRAGridRowData.data['EvnReanimatAction_Medicoment'] = '';
																	}
																	
																	//событие назначения лекарственного средства	
																	EvnRAGridRowData.data['EvnDrug_id'] = null; // 0;  
															
																	//дозировка и единицы лекарственного средства
																	EvnRAGridRowData.data['EvnReanimatAction_DrugDose'] = 0; 
																	EvnRAGridRowData.data['EvnReanimatAction_DrugUnit'] = '';  //BOB - 23.04.2018
																	if (SysNick.inlist(['vazopressors','epidural_analgesia','antifungal_therapy','catheterization_veins','invasive_hemodynamics','sedation']))//BOB - 05.03.2020 закомментарено
																		EvnRAGridRowData.data['EvnReanimatAction_DrugUnit'] = ReanimDrug['-1'].ReanimDrug_Unit;  //win.findById('swERPEW_RA_Drug_Unit_-1').getValue();  //BOB - 04.07.2019  //'мг';  //BOB - 23.04.2018
																	 																	
																	//способ оплаты
																	EvnRAGridRowData.data['PayType_id'] =  win.findById('swERPEW_RA_PayType').value;

																	//результат наблюдения
																	//EvnRAGridRowData.data['EvnReanimatAction_ObservValue'] =  win.findById('swERPEW_RA_Observ_Value').value;
																	
																	EvnRAGridRowData.commit();
																	//console.log('BOB_EvnRAGridRowData_3=',EvnRAGridRowData.data['PayType_id']);
																}
															}
														}
													}
												}
											]
										},
										//Дата Время Реанимационного мероприятия
										{									
											layout:'column',
											//style:'margin-left: 5px; margin-top: 5px;font-size: 12px',
											region: 'center',
											items:[
												//Дата Реанимационного мероприятия
												{									
													layout:'form',
													width: 160,
													labelWidth: 50,
													items:[										
														{
															allowBlank: false,
															fieldLabel:'Дата',
															format:'d.m.Y',
															id: 'swERPEW_EvnReanimatAction_setDate',
															listeners:{
																'change':function (field, newValue, oldValue) {															
																	//заполнение строки грида
																	var EvnScalesGridRow = win.findById('swERPEW_ReanimatAction_Grid').getSelectionModel().getSelected();  //выбранная строка в гриде Реанимационных мероприятий
																	//если запись в гриде новая
																	//if (EvnScalesGridRow.data['EvnReanimatAction_id'] == 'New_GUID_Id'){
																	//	console.log('BOB_newValue=',newValue);
																		EvnScalesGridRow.data['EvnReanimatAction_setDate'] = newValue;
																		EvnScalesGridRow.commit();			
																	//}
																	this.changedDates = true; //а зачем не знаю		
																}.createDelegate(this),
																'keydown':function (inp, e) {
																	//сделал по образцу из формы движения
																	if (e.getKey() == Ext.EventObject.TAB && e.shiftKey == true) {
																		e.stopEvent();
																		this.buttons[this.buttons.length - 1].focus();
																	}
																}.createDelegate(this)
															},
															name:'EvnReanimatAction_setDate',
															plugins:[ new Ext.ux.InputTextMask('99.99.9999', false) ],
															selectOnFocus:true,
																	//tabIndex:this.tabIndex + 1,
															width:100,
															xtype:'swdatefield'
														}										
													]
												},	
												//Время Реанимационного мероприятия
												{									
													layout:'form',
													width: 120,
													labelWidth: 50,
													items:[	

														{
															fieldLabel:'Время',
															allowBlank: false,
															listeners:{
																'change':function (field, newValue, oldValue) {

																	//заполнение строки грида
																	var EvnScalesGridRow = win.findById('swERPEW_ReanimatAction_Grid').getSelectionModel().getSelected();  //выбранная строка в гриде Реанимационных мероприятий
																	//если запись в гриде новая
																	//if (EvnScalesGridRow.data['EvnReanimatAction_id'] == 'New_GUID_Id'){
																		EvnScalesGridRow.data['EvnReanimatAction_setTime'] = newValue;
																		EvnScalesGridRow.commit();			
																	//}
																	this.changedDates = true;

																}.createDelegate(this),
																'keydown':function (inp, e) {
																	if (e.getKey() == Ext.EventObject.F4) {
																		e.stopEvent();
																		inp.onTriggerClick();
																	}
																}
															},
															id: 'swERPEW_EvnReanimatAction_setTime',
															name:'EvnReanimatAction_setTime',
															onTriggerClick:function () {
																var base_form = this.findById('swERPEW_Form').getForm();
																var time_field = base_form.findField('EvnReanimatAction_setTime');

																if (time_field.disabled) {
																	return false;
																}

																setCurrentDateTime({
																	callback:function () {
																		//заполнение строки грида
																		var EvnScalesGridRow = win.findById('swERPEW_ReanimatAction_Grid').getSelectionModel().getSelected();  //выбранная строка в гриде Реанимационных мероприятий
																		//если запись в гриде новая
																		//if (EvnScalesGridRow.data['EvnReanimatAction_id'] == 'New_GUID_Id'){
																			EvnScalesGridRow.data['EvnReanimatAction_setTime'] = time_field.getValue();
																			EvnScalesGridRow.commit();			
																		//}

																		base_form.findField('swERPEW_EvnReanimatAction_setDate').fireEvent('change', base_form.findField('swERPEW_EvnReanimatAction_setDate'), base_form.findField('swERPEW_EvnReanimatAction_setDate').getValue());
																	}.createDelegate(this),
																	dateField:base_form.findField('swERPEW_EvnReanimatAction_setDate'),
																	loadMask:true,
																	setDate:true,
																	setDateMaxValue:true,
																	addMaxDateDays: this.addMaxDateDays,
																	setDateMinValue:false,
																	setTime:true,
																	timeField:time_field,
																	windowId:this.id
																});
															}.createDelegate(this),
															plugins:[ new Ext.ux.InputTextMask('99:99', true) ],
															//tabIndex:this.tabIndex + 4,
															validateOnBlur:false,
															width:60,
															xtype:'swtimefield'
														}										

													]
												},
												
												
												
												//Дата окончания Реанимационного мероприятия
												{									
													layout:'form',
													id: 'swERPEW_EvnReanimatAction_disDate_Pnl',
													width: 240,
													labelWidth: 130,
													items:[										
														{
															allowBlank: true,
															fieldLabel:'Окончание:  Дата',
															format:'d.m.Y',
															id: 'swERPEW_EvnReanimatAction_disDate',
															name:'EvnReanimatAction_disDate',
															listeners:{
																'change':function (field, newValue, oldValue) {															
																	//заполнение строки грида
																	var EvnScalesGridRow = win.findById('swERPEW_ReanimatAction_Grid').getSelectionModel().getSelected();  //выбранная строка в гриде Реанимационных мероприятий
																	//если запись в гриде новая
																	//if (EvnScalesGridRow.data['EvnReanimatAction_id'] == 'New_GUID_Id'){
																	EvnScalesGridRow.data['EvnReanimatAction_disDate'] = newValue;
																	EvnScalesGridRow.commit();			
																	//}															
																	this.changedDates = true; //а зачем не знаю		
																}.createDelegate(this),
																'keydown':function (inp, e) {
																	//сделал по образцу из формы движения
																	if (e.getKey() == Ext.EventObject.TAB && e.shiftKey == true) {
																		e.stopEvent();
																		this.buttons[this.buttons.length - 1].focus();
																	}
																}.createDelegate(this)
															},
															plugins:[ new Ext.ux.InputTextMask('99.99.9999', false) ],
															selectOnFocus:true,
																	//tabIndex:this.tabIndex + 1,
															width:100,
															xtype:'swdatefield'
														}										
													]
												},	
												//Время окончания Реанимационного мероприятия
												{									
													layout:'form',
													id: 'swERPEW_EvnReanimatAction_disTime_Pnl',
													width: 120,
													labelWidth: 50,
													items:[	

														{
															fieldLabel:'Время',
															allowBlank: true,
															listeners:{
																'change':function (field, newValue, oldValue) {

																	//заполнение строки грида
																	var EvnScalesGridRow = win.findById('swERPEW_ReanimatAction_Grid').getSelectionModel().getSelected();  //выбранная строка в гриде Реанимационных мероприятий
																	//если запись в гриде новая
																	//if (EvnScalesGridRow.data['EvnReanimatAction_id'] == 'New_GUID_Id'){
																	EvnScalesGridRow.data['EvnReanimatAction_disTime'] = newValue;
																	EvnScalesGridRow.commit();			
																	//}
																	this.changedDates = true;

																}.createDelegate(this),
																'keydown':function (inp, e) {
																	if (e.getKey() == Ext.EventObject.F4) {
																		e.stopEvent();
																		inp.onTriggerClick();
																	}
																}
															},
															id: 'swERPEW_EvnReanimatAction_disTime',
															name:'EvnReanimatAction_disTime',
															onTriggerClick:function () {
																var base_form = this.findById('swERPEW_Form').getForm();
																var time_field = base_form.findField('EvnReanimatAction_disTime');

																if (time_field.disabled) {
																	return false;
																}

																setCurrentDateTime({
																	callback:function () {
																		//заполнение строки грида
																		var EvnScalesGridRow = win.findById('swERPEW_ReanimatAction_Grid').getSelectionModel().getSelected();  //выбранная строка в гриде Реанимационных мероприятий
																		//если запись в гриде новая
																		//if (EvnScalesGridRow.data['EvnReanimatAction_id'] == 'New_GUID_Id'){
																		EvnScalesGridRow.data['EvnReanimatAction_disTime'] = time_field.getValue();
																		EvnScalesGridRow.commit();			
																		//}

																		base_form.findField('swERPEW_EvnReanimatAction_disDate').fireEvent('change', base_form.findField('swERPEW_EvnReanimatAction_disDate'), base_form.findField('swERPEW_EvnReanimatAction_disDate').getValue());
																	}.createDelegate(this),
																	dateField:base_form.findField('swERPEW_EvnReanimatAction_disDate'),
																	loadMask:true,
																	setDate:true,
																	setDateMaxValue:true,
																	addMaxDateDays: this.addMaxDateDays,
																	setDateMinValue:false,
																	setTime:true,
																	timeField:time_field,
																	windowId:this.id
																});
															}.createDelegate(this),
															plugins:[ new Ext.ux.InputTextMask('99:99', true) ],
															//tabIndex:this.tabIndex + 4,
															validateOnBlur:false,
															width:60,
															xtype:'swtimefield'
														}										

													]
												}
												

											]
										},
												
										// кнопка сохранить
										{									
											layout:'form',
											region: 'east',
											width: 100,
											//margins: '5 0 0 0',
											items:[

												new Ext.Button({
													id: 'swERPEW_EvnReanimatActionButtonSave',
													iconCls: 'save16',
													text: 'Сохранить',
													handler: function(b,e)
													{
														this.EvnReanimatAction_Save(b,e);
													}.createDelegate(this)
												})

											]
										}

										
										
									]
								},
								
																
								//ПАНЕЛЬ РЕДАКТИРОВАНИЯ События Реанимационного мероприятия
								{
									layout:'form',
									id:'swERPEW_RA_Base_Data',
									border:true,
									style: 'border-top: 1px solid #ffffff; border-left: 1px solid  #ffffff; border-bottom: 1px solid #99bbe8;border-right: 1px solid #99bbe8; padding: 2px; ',
									MyClass: 'RA_Panels',
									items:[	
										//панель метод Реанимационного мероприятия
										{											
											layout:'form',
											id: 'swERPEW_RA_Method_Panel',
											border:false,
											items:[	
												{
													layout:'column',
													border:false,
													items:[

														//Лейбл метод Реанимационного мероприятия
														{
															layout:'form',
															style:'margin-top: 2px; font-size: 10pt;',
															width: 50,
															items:[
																new Ext.form.Label({
																	id: 'swERPEW_RA_Method_Lbl',
																	text: 'Метод'
																})
															]
														},
														//combo - метод Реанимационного мероприятия
														{
															layout:'column',
															border:false,
															items:[
																{
																	id: 'swERPEW_RA_Method',
																	hiddenName: 'RA_Method',
																	xtype: 'swcommonsprcombo',
																	allowBlank: false,
																	comboSubject: 'EvnReanimatActionMethod',
																	suffix: '',
																	fieldLabel: '',
																	labelSeparator: '',
																	width: 900,
																	lastQuery: '',
																	listeners: {
																		'change': function(combo, newValue, oldValue) { //BOB - 12.07.2019
																			console.log('BOB_EvnReanimatActionMethod_change_oldValue=', oldValue); //BOB - 12.07.2019
																			console.log('BOB_EvnReanimatActionMethod_change_change_newValue=', newValue); //BOB - 12.07.2019
																			if (newValue == '') {
																				var EvnScalesGridRow = win.findById('swERPEW_ReanimatAction_Grid').getSelectionModel().getSelected();  //выбранная строка в гриде событий шкал
																				EvnScalesGridRow.data['EvnReanimatAction_MethodCode'] = null;
																				EvnScalesGridRow.data['EvnReanimatAction_MethodName'] = null;
																				EvnScalesGridRow.data['UslugaComplex_id'] = null;
																				win.findById('swERPEW_RA_MethodTxt').setVisible( false );
																				win.findById('swERPEW_RA_MethodTxt').setValue('');
																				EvnScalesGridRow.data['EvnReanimatAction_MethodTxt'] = '';
																				EvnScalesGridRow.commit();
																			}
																		}, //BOB - 12.07.2019
																		'select': function(combo, record, index) {
																			var EvnScalesGridRow = win.findById('swERPEW_ReanimatAction_Grid').getSelectionModel().getSelected();  //выбранная строка в гриде событий шкал
																			EvnScalesGridRow.data['EvnReanimatAction_MethodCode'] = record.data.EvnReanimatActionMethod_Code;
																			EvnScalesGridRow.data['EvnReanimatAction_MethodName'] = record.data.EvnReanimatActionMethod_Name;
																			EvnScalesGridRow.data['UslugaComplex_id'] = record.data.EvnReanimatActionMethod_id;
																			if (record.data.EvnReanimatActionMethod_id == 4) //BOB - 03.11.2018
																				win.findById('swERPEW_RA_MethodTxt').setVisible( true );
																			else {
																				win.findById('swERPEW_RA_MethodTxt').setVisible( false );
																				win.findById('swERPEW_RA_MethodTxt').setValue('');
																				EvnScalesGridRow.data['EvnReanimatAction_MethodTxt'] = '';
																			} //BOB - 03.11.2018
																			EvnScalesGridRow.commit();
																		}
																	}
																}
															]
														}
													]
												},

												{
													layout:'form',
													//style:'margin-top: 4px;',
													labelWidth:5,
													border:false,
													items:[
														//Метод  - варианта пользователя //BOB - 03.11.2018
														{
															allowBlank: true,
															name: 'swERPEW_RA_MethodTxt',
															id: 'swERPEW_RA_MethodTxt',
															width: 940,
															//style:'margin-left: 4px;',  //margin-top: 2px;
															fieldLabel: '',
															labelSeparator: '',
															value:'',
															xtype: 'textfield',
															//hidden: true,
															listeners:{
																'change':function (field, newValue, oldValue) {
																	var EvnScalesGridRow = win.findById('swERPEW_ReanimatAction_Grid').getSelectionModel().getSelected();  //выбранная строка в гриде Реанимационных мероприятий
																	EvnScalesGridRow.data['EvnReanimatAction_MethodTxt'] = newValue;
																	EvnScalesGridRow.commit();
																}.createDelegate(this)
															}
														} //BOB - 03.11.2018
													]
												}
											]
										},
										//BOB - 03.11.2018
										//панель - объём питания и энергетическая ценность питания 
										{
											layout:'column',
											id: 'swERPEW_RA_Nutrit_Panel',
											border:false,
											items:[	
												//BOB - 15.04.2020
												//combo - молочная смесь 
												{							
													id: 'swERPEW_RA_MilkMix_panel',
													layout:'form',
													style:'margin-top: 4px;',
													labelWidth:110,
													border:false,
													items:[	
														{
															id: 'swERPEW_RA_MilkMix',
															hiddenName: 'RA_MilkMix',									
															xtype: 'swcommonsprcombo',
															fieldLabel: langs('Молочная смесь'),
															labelSeparator: '',
															allowBlank: true,
															comboSubject: 'MilkMix',
															width: 335,
															lastQuery: '',
															listeners: {
																'select': function(combo, record, index) {
																	var EvnScalesGridRow = win.findById('swERPEW_ReanimatAction_Grid').getSelectionModel().getSelected();  //выбранная строка в гриде регулярного наблюдения состояния    
																	EvnScalesGridRow.data['MilkMix_id'] = record.data.MilkMix_id;   //
																	EvnScalesGridRow.commit();
																}
															}
														}
														
													]
												},
												//Объём питания
												{							
													layout:'form',
													style:'margin-top: 4px;',
													labelWidth:90,
													border:false,
													items:[	
														new Ext.form.NumberField({
															value: 0,
															id: 'swERPEW_RA_NutritVol',
															fieldLabel:'Объём питания',
															labelSeparator: '',
															enableKeyEvents: true,
															width: 60,
//															style:'margin-top: 6px; '//,
															listeners:{
																'keyup':function (obj, e) {
																	var EvnScalesGridRow = win.findById('swERPEW_ReanimatAction_Grid').getSelectionModel().getSelected();  //выбранная строка в гриде событий шкал  
																	EvnScalesGridRow.data['EvnReanimatAction_NutritVol'] = obj.getValue();
																	EvnScalesGridRow.commit();
																}.createDelegate(this)
															}

														})
													]
												},
												//Лейбл мл
												{									
													layout:'form',
													style:'margin-left: 2px; margin-top: 5px; font-size: 10pt;',
													width: 15,
													border:false,
													items:[
														{
															xtype: 'label',
															text: 'мл'
														}					
													]
												},    
												//Энергетическая ценность питания
												{							
													layout:'form',
													style:'margin-top: 4px;',
													labelWidth:160,
													border:false,
													items:[	
														new Ext.form.NumberField({
															value: 0,
															id: 'swERPEW_RA_NutritEnerg',
															fieldLabel:'Энергетическая ценность',
															labelSeparator: '',
															enableKeyEvents: true,
															width: 60,
//															style:'margin-top: 6px; '//,
															listeners:{
																'keyup':function (obj, e) {
																	var EvnScalesGridRow = win.findById('swERPEW_ReanimatAction_Grid').getSelectionModel().getSelected();  //выбранная строка в гриде событий шкал  
																	EvnScalesGridRow.data['EvnReanimatAction_NutritEnerg'] = obj.getValue();
																	EvnScalesGridRow.commit();
																}.createDelegate(this)
															}

														})
													]
												},
												//Лейбл ккал
												{									
													layout:'form',
													style:'margin-left: 2px; margin-top: 5px; font-size: 10pt;',
													width: 30,
													border:false,
													items:[
														{
															xtype: 'label',
															text: 'ккал'
														}					
													]
												}
											]
										},
										//BOB - 03.11.2018
										//панель - Тип оплаты Реанимационного мероприятия - услуги
										{
											layout:'column',
											id: 'swERPEW_RA_PayType_Panel',
											border:false,
											items:[	

												//combo - Тип оплаты Реанимационного мероприятия - услуги
												{							
													layout:'form',
													style:'margin-top: 4px;',
													labelWidth:80,
													border:false,
													items:[	
														{
															id: 'swERPEW_RA_PayType',
															allowBlank: false,
															xtype: 'swextemporalcomptypecombo',
															fieldLabel: 'Тип оплаты',
															labelSeparator: '',
															comboSubject: 'PayType',
															width: 240,
															listeners: {
																'change': function(combo, newValue, oldValue) { //BOB - 12.07.2019	
																	if (newValue == '') {
																		var EvnScalesGridRow = win.findById('swERPEW_ReanimatAction_Grid').getSelectionModel().getSelected();  //выбранная строка в гриде событий шкал
																		EvnScalesGridRow.data['PayType_id'] = null;
																		EvnScalesGridRow.commit();
																	}
																}, //BOB - 12.07.2019	
																'select': function(combo, record, index) {
																	var EvnScalesGridRow = win.findById('swERPEW_ReanimatAction_Grid').getSelectionModel().getSelected();  //выбранная строка в гриде событий шкал
																	EvnScalesGridRow.data['PayType_id'] = record.data.PayType_id;
																	EvnScalesGridRow.commit();
																}
															}
														}										
													]
												}
											]
										},
										//панель - использование медикамента
										{
											layout:'form',
											id: 'swERPEW_RA_Drug_Panel',
											border:false,
											items:[	
												
											]
										},
										
										//панель - дополнительные реквизиты для печати документа пока только катетеризации 
										{
											layout:'column',
											id: 'swERPEW_RA_Print_Cathet_Panel',
											border:false,
											items:[	
										
												//combo - вены при катетеризации
												{							
													layout:'form',
													style:'margin-top: 4px;',
													labelWidth:80,
													border:false,
													items:[	
														{
															id: 'swERPEW_RA_CathetVeins',
															hiddenName: 'RA_CathetVeins',
															xtype: 'swreanimatcathetveinscombo',
															allowBlank: false,
															width: 240,
															listeners: {
																'change': function(combo, newValue, oldValue) { //BOB - 12.07.2019	
																	if (newValue == '') {
																		var EvnScalesGridRow = win.findById('swERPEW_ReanimatAction_Grid').getSelectionModel().getSelected();  //выбранная строка в гриде событий шкал
																		EvnScalesGridRow.data['ReanimatCathetVeins_id'] = null;
																		EvnScalesGridRow.commit();
																	}
																}, //BOB - 12.07.2019	
																'select': function(combo, record, index) {
																	var EvnScalesGridRow = win.findById('swERPEW_ReanimatAction_Grid').getSelectionModel().getSelected();  //выбранная строка в гриде событий шкал
																	EvnScalesGridRow.data['ReanimatCathetVeins_id'] = record.data.ReanimatCathetVeins_id;
																	EvnScalesGridRow.commit();
																	//}
																},
																'expand': function	(combo)	{
																	var SysNick = win.findById('swERPEW_ReanimatAction_Grid').getSelectionModel().getSelected().data['ReanimatActionType_SysNick'];				
																	var filterReanimatCathetVeins = win.findById('swERPEW_ReanimatAction_Panel').RA_Veins[SysNick];
																	combo.getStore().clearFilter();
																	if (filterReanimatCathetVeins){
																		combo.getStore().filterBy(function (rec) {
																			return rec.get('ReanimatCathetVeins_id').inlist(filterReanimatCathetVeins);
																		});
																	}
																}
															} 
														} 
														
													]
												},
												
												//combo - способ фиксации катетера
												{							
													layout:'form',
													style:'margin-top: 4px;',
													labelWidth:80,
													border:false,
													items:[	
														{
															id: 'swERPEW_RA_CathetFix',
															allowBlank: true,
															xtype: 'swextemporalcomptypecombo',
															fieldLabel: 'Фиксация',
															labelSeparator: '',
															comboSubject: 'CathetFixType',
															width: 240,
															'change': function(combo, newValue, oldValue) { //BOB - 12.07.2019	
																console.log('BOB_swERPEW_RA_CathetFix_change_oldValue=', oldValue); //BOB - 12.07.2019		
																console.log('BOB_swERPEW_RA_CathetFix_change_change_newValue=', newValue); //BOB - 12.07.2019		
																if (newValue == '') {
																	var EvnScalesGridRow = win.findById('swERPEW_ReanimatAction_Grid').getSelectionModel().getSelected();  //выбранная строка в гриде событий шкал
																	EvnScalesGridRow.data['CathetFixType_id'] = null;
																	EvnScalesGridRow.commit();
																}
															}, //BOB - 12.07.2019	
															listeners: {
																'select': function(combo, record, index) {
																	var EvnScalesGridRow = win.findById('swERPEW_ReanimatAction_Grid').getSelectionModel().getSelected();  //выбранная строка в гриде событий шкал
																	EvnScalesGridRow.data['CathetFixType_id'] = record.data.CathetFixType_id;
																	EvnScalesGridRow.commit();
																}
															}
														}										
														
													]
												},
												//Набор - текстовое поле
												{
													layout:'form',
													labelWidth: 60,
													style:'margin-top: 4px;',
													items:[
														{
														//	anchor: '95%', // растягивает на 95% от возможного размера
															allowBlank: true,
															fieldLabel: 'Набор',
															labelSeparator: '',
															name: 'cathetNabor',
															id: 'swERPEW_RA_cathetNabor',
															width: 475,
															enableKeyEvents: true,
														//	tabIndex: TABINDEX_MS,
															xtype: 'textfield',
															listeners:{
																'keyup':function (obj, e) {
																	var EvnScalesGridRow = win.findById('swERPEW_ReanimatAction_Grid').getSelectionModel().getSelected();  //выбранная строка в гриде событий шкал
																	//if (EvnScalesGridRow.data['EvnReanimatAction_id'] == 'New_GUID_Id') {
																		EvnScalesGridRow.data['EvnReanimatAction_CathetNaborName'] = obj.getValue();
																		EvnScalesGridRow.commit();
																	//}
																}.createDelegate(this)
															}

														}											
													]
												}
										
										
										
											]
										},
										
										//BOB - 03.11.2018
										//панель - Показание наблюдения 03.11.2018
										{
											layout:'form',
											id: 'swERPEW_RA_Rate_Panel',
											border:false,
											items:[	
												
												this.DrugGrid = new sw.Promed.ViewFrame({
													actions: [
														{name: 'action_add', handler: function(){win.EvnReanimatAction_RateAdd();}},
														{name: 'action_edit', hidden: true},
														{name: 'action_view', hidden: true},
														{name: 'action_delete', handler: function(){win.EvnReanimatAction_RateDel();}},
														{name: 'action_refresh', hidden: true},
														{name: 'action_print', hidden: true},
														{name: 'action_save', hidden: true, handler: function(o) {
															var rate_records = win.findById('swERPEW_ReanimatAction_Panel').rate_records;
															var RateGridData = o.record.data;
															//console.log('BOB_action_save_RateGridData=',RateGridData);															
															//BOB - 12.07.2019
															if (!win.checkTime(RateGridData['Rate_setTime'].substr(0, 2) , RateGridData['Rate_setTime'].substr(3, 2))){
																Ext.MessageBox.alert('Внимание!', 'Время неверное(');
																RateGridData['Rate_setTime'] = rate_records[RateGridData['EvnReanimatAction_id']][RateGridData['Rate_id']]['Rate_setTime'];
															}
															if (!win.checkDate(RateGridData['Rate_setDate'].substr(0, 2) , RateGridData['Rate_setDate'].substr(3, 2) , RateGridData['Rate_setDate'].substr(6, 4), 1950, 2100)){
																Ext.MessageBox.alert('Внимание!', 'Дата неверная(');
																RateGridData['Rate_setDate'] = rate_records[RateGridData['EvnReanimatAction_id']][RateGridData['Rate_id']]['Rate_setDate'];
															}
															//BOB - 12.07.2019															
															RateGridData['Rate_RecordStatus'] = RateGridData['Rate_RecordStatus'] == 1 ? 2 : RateGridData['Rate_RecordStatus'];
															//расчёт величины Rate_PerCent для рисования графика, это процеты поэтому *100, деление на 200 потому что принял 200 за максимум
															RateGridData['Rate_PerCent'] = (win.findById('swERPEW_ReanimatAction_Grid').getSelectionModel().getSelected().get('ReanimatActionType_SysNick')) == 'observation_saturation' ? RateGridData['Rate_Value'] : RateGridData['Rate_Value']/200*100;
															win.findById('swERPEW_RA_Rate_Grid').getGrid().getSelectionModel().getSelected().commit();															
															rate_records[RateGridData['EvnReanimatAction_id']][RateGridData['Rate_id']] = win.EvnReanimatAction_RateCopy(RateGridData);
														}}
													],
													autoExpandColumn: 'autoexpand',
													autoExpandMin: 150,
													autoLoadData: false,
													border: false,
													dataUrl: '/?c=EvnReanimatPeriod&m=GetReanimatActionRate',
													height: 220,
													object: 'swERPEW_RA_Rate_Grid',
													id: 'swERPEW_RA_Rate_Grid',
													paging: false,
													style: 'margin: 0px',
													toolbar: true,
													contextmenu: false,
													editing: true,
													useEmptyRecord: false,
													stringfields: [
														{name:'Rate_id', type: 'int', hidden: true, key: true},
														{name:'Rate_setDate', type:'string', header: 'Дата', resizable: false, sortable: false, width: 80, editor: new Ext.form.TextField({plugins:[ new Ext.ux.InputTextMask('99.99.9999', true) ]}) },  //, format:'d.m.Y'
														{name:'Rate_setTime', type:'string', header: 'Время', resizable: false, sortable: false, width: 60, editor: new Ext.form.TextField({plugins:[ new Ext.ux.InputTextMask('99:99', true) ]}) },
														{name:'Rate_Value', type: 'string', header: langs('Значение'), width: 200, editor: new Ext.form.NumberField()},														
														{name:'Rate_StepsToChange', type: 'string', header: 'Действия по изменению', width: 180, editor: new Ext.form.TextField(), id: 'autoexpand'},
														{name:'EvnReanimatAction_id', type: 'string', hidden: true},
														{name:'Rate_RecordStatus', type: 'int', hidden: true},
														{name:'Rate_PerCent', type: 'int', hidden: true}
													]//,
//													onRowSelect: function(sm, index, record) {
//														//this.onRowSelectChange();
//														return false;
//													}.createDelegate(this),
//													onRowDeSelect: function(sm, index, record) {
//														//this.onRowSelectChange();
//														return false;
//													}.createDelegate(this)
												})
											]	
										},
										//BOB - 03.11.2018
										//BOB - 03.12.2018										
										//панель - ПАРАМЕТРЫ ИВЛ
										{
											layout:'form',
											id: 'swERPEW_RA_ParamIVL_Panel',
											//border:false,
											border:true,
											style: 'border-top: 1px solid #99bbe8; border-left: 1px solid #99bbe8; border-bottom: 1px solid #ffffff;border-right: 1px solid #ffffff; padding: 2px;  ',
											items:[	
												//BOB - 04.07.2019
												{
													id: 'swERPEW_RA_IVLParameter_id',
													value:'',
													xtype:'hidden'
												},

												//панель Аппарат ИВЛ
												{
													layout:'form',
													id: 'swERPEW_RA_IVLParameter_Apparat_Panel',
													labelWidth: 80,
													style:'margin-top: 4px;',
													items:[
														//BOB - 29.02.2020
														{
															xtype: 'combo',
															allowBlank: false,
															hiddenName: 'IVLParameter_Apparat', //'glasgow_eye_response',
															disabled: false,
															id: 'swERPEW_RA_IVLParameter_Apparat', // 'swERPEW_glasgow_eye_response',
															mode:'local',
															listWidth: 400,
															width: 400,
															triggerAction : 'all',
															editable: true,
															displayField:'IVLParameter_Apparat_Name',
															valueField:'IVLParameter_Apparat',
															tpl: '<tpl for="."><div class="x-combo-list-item">'+
																'{IVLParameter_Apparat_Name} '+ '&nbsp;' +
																'</div></tpl>' ,
															fieldLabel: 'Аппарат',
															store:new Ext.data.SimpleStore(  {
																fields: [{name:'IVLParameter_Apparat',type:'string'},
																		 {name:'IVLParameter_Apparat_Name',type:'string'}]
															}),
															listeners: {
																'select': function(combo, record, index) {
																	var newValue = record.data.IVLParameter_Apparat;
																	win.findById('swERPEW_RA_IVLParameter_Apparat_Hid').setValue(newValue);

																	if (win.isNeonatal(win.findById('swERPEW_ReanimatAgeGroup').getValue())) {
																		win.findById('swERPEW_RA_IVLRegim').setValue(null);
																		win.findById('swERPEW_RA_IVLRegim_Hid').setValue(null);
																		win.findById('swERPEW_RA_IVLRegim').fireEvent('expand', win.findById('swERPEW_RA_IVLRegim'));
																		win.findById('swERPEW_RA_ParamIVL_Panel').ParamVisualisation(true, null, newValue);
																	}
																},
																'change':function (field, newValue, oldValue) {
																	if (win.findById('swERPEW_ReanimatAction_Grid').getSelectionModel().getSelected().data['EvnReanimatAction_id'] == 'New_GUID_Id') {
																		win.findById('swERPEW_RA_IVLParameter_Apparat_Hid').setValue(newValue);
																		
																		if (win.isNeonatal(win.findById('swERPEW_ReanimatAgeGroup').getValue())) {
																			win.findById('swERPEW_RA_IVLRegim').setValue(null);
																			win.findById('swERPEW_RA_IVLRegim_Hid').setValue(null);
																			win.findById('swERPEW_RA_IVLRegim').fireEvent('expand', win.findById('swERPEW_RA_IVLRegim'));
																			win.findById('swERPEW_RA_ParamIVL_Panel').ParamVisualisation(true, null, newValue);
																		}
																	}
																}
															}
														},
														//BOB - 29.02.2020
														{
															id: 'swERPEW_RA_IVLParameter_Apparat_Hid',
															value:'',
															xtype:'hidden'
														}
													]
												},										
												//панель Режим ИВЛ
												{							
													layout:'form',
													id: 'swERPEW_RA_IVLRegim_Panel',
													labelWidth: 80,
													border:false,
													items:[	
														//combo - Режим ИВЛ //BOB - 21.03.2019
														{
															id: 'swERPEW_RA_IVLRegim',
															hiddenName: 'RA_IVLRegim',
															xtype: 'swreanimativlregimcombo',
															allowBlank: false,
															width: 800,
															listeners: {
																'select': function(combo, record, index) {
																	console.log('BOB_swERPEW_RA_IVLRegim_select_record=',record);
																	var SysNick = '';
																	//если выбрана не пустая строка
																	if (record) { //  (index > 0){
																		SysNick = record.data.IVLRegim_SysNick;	
																		if (!win.isNeonatal(win.findById('swERPEW_ReanimatAgeGroup').getValue())) 																			
																			win.findById('swERPEW_RA_ParamIVL_Panel').ParamVisualisation(true, SysNick, null);
																		win.findById('swERPEW_RA_IVLRegim_Hid').setValue(record.data.IVLRegim_id);
																	}				
																	else { //выбрана пустая строка																	
																		if (!win.isNeonatal(win.findById('swERPEW_ReanimatAgeGroup').getValue()))
																			win.findById('swERPEW_RA_ParamIVL_Panel').ParamVisualisation(false, SysNick, null);
																		win.findById('swERPEW_RA_IVLRegim_Hid').setValue(null);
																	}
																},
																//BOB - 29.02.2020
																'expand': function(combo){
																	combo.getStore().clearFilter();
																	var toDo = false;
																	var aIVLRegim_SysNick = ['cmv_vc','cmv_pc','simv_vc','simv_pc','psv','asv'];

																	if (win.isNeonatal(win.findById('swERPEW_ReanimatAgeGroup').getValue())) {
																		var IVLAppar = win.findById('swERPEW_RA_IVLParameter_Apparat').getValue();
																		if (win.findById('swERPEW_RA_IVLParameter_Apparat').getValue().indexOf('Avea') > -1) IVLAppar = 'Avea';
																		else if (win.findById('swERPEW_RA_IVLParameter_Apparat').getValue().indexOf('Servo I') > -1) IVLAppar = 'Servo I';
																		else if (win.findById('swERPEW_RA_IVLParameter_Apparat').getValue().indexOf('Sensor Medics 3100A') > -1) IVLAppar = 'Sensor Medics 3100A';
																		else if (win.findById('swERPEW_RA_IVLParameter_Apparat').getValue().indexOf('Infant Flow') > -1) IVLAppar = 'Infant Flow';
				
																		switch (IVLAppar) {
																			case 'Avea' :
																					aIVLRegim_SysNick = ['ac_vc','ac_pc','tcpl','simv_vc','simv_pc','simv_tcpl','vg','psv','ncpap','cpap'];
																					toDo = true;
																					break;
																			case 'Servo I' :
																					aIVLRegim_SysNick = ['vc','pc','prvc','simv_vc','simv_pc','simv_prvc','psv','bi_vent','cpap'];
																					toDo = true;
																					break;
																			case 'Sensor Medics 3100A' :
																					aIVLRegim_SysNick = ['hfov'];
																					toDo = true;
																					break;
																			case 'Infant Flow' :
																					aIVLRegim_SysNick = ['bi_phasic','ncpap'];
																					toDo = true;
																					break;
																		}
																	} else
																		toDo = true;


																	if(toDo) {
																		combo.getStore().filterBy(function (rec) {
																			return rec.get('IVLRegim_SysNick').inlist(aIVLRegim_SysNick);
																		});

																	}
																}
															} 
														}, 
														{
															id: 'swERPEW_RA_IVLRegim_Hid',
															value: null,
															xtype:'hidden'
														}
													]
												},
												//Диаметр трубки
												{							
													layout:'column',
													id: 'swERPEW_RA_IVLParameter_TubeDiam_Panel',
													labelWidth:300,
													border:false,
													items:[	
														{
															layout:'form',
															items:[
																new Ext.form.NumberField({
																	value: 0,
																	id: 'swERPEW_RA_IVLParameter_TubeDiam',
																	fieldLabel:'Диаметр трубки - D',
																	labelSeparator: '',
																	enableKeyEvents: true,
																	width: 60,
																	listeners:{
																		'change':function (field, newValue, oldValue) {
																			if (Ext.isEmpty(newValue)) {
																				field.setValue(0);
																				newValue = 0;
																			}
																			if (newValue <= 99)
																				win.findById('swERPEW_RA_IVLParameter_TubeDiam_Hid').setValue(newValue);
																			else {
																				field.setValue(oldValue);
																				Ext.MessageBox.alert('Ошибка!', 'Слишком большой диаметр трубки!', function(){field.focus(true,100);});
																			}
																		}.createDelegate(this)
																	}
																})
															]														
														},
														{xtype:'label',text: 'мм',style: 'font-size: 10pt;  margin-left: 2pt; margin-top: 1pt'},											
														{
															id: 'swERPEW_RA_IVLParameter_TubeDiam_Hid',
															value:0,
															xtype:'hidden'
														}														
													]
												},
												//FiO2
												{							
													layout:'column',
													id: 'swERPEW_RA_IVLParameter_FiO2_Panel',
													labelWidth:300,
													border:false,
													items:[	
														{
															layout:'form',
															items:[
																new Ext.form.NumberField({
																	value: 0,
																	id: 'swERPEW_RA_IVLParameter_FiO2',
																	fieldLabel:'Концентрация кислорода - FiO2',
																	labelSeparator: '',
																	enableKeyEvents: true,
																	width: 60,
																	listeners:{
																		'keyup':function (obj, e) {
																			if (Ext.isEmpty(obj.getValue())) obj.setValue(0);
																			win.findById('swERPEW_RA_IVLParameter_FiO2_Hid').setValue(obj.getValue());
																		}
																	}
																})
															]														
														},
														{xtype:'label',text: '%',style: 'font-size: 10pt;  margin-left: 2pt; margin-top: 1pt'},											
														{
															id: 'swERPEW_RA_IVLParameter_FiO2_Hid',
															value:0,
															xtype:'hidden'
														}
													]
												},
												//Процент минимального объёма
												{							
													layout:'column',
													id: 'swERPEW_RA_IVLParameter_PcentMinVol_Panel',
													labelWidth:300,
													border:false,
													items:[	
														{
															layout:'form',
															items:[
																new Ext.form.NumberField({
																	value: 0,
																	id: 'swERPEW_RA_IVLParameter_PcentMinVol',
																	fieldLabel:'Процент минимального объёма',
																	labelSeparator: '',
																	enableKeyEvents: true,
																	width: 60,
																	listeners:{
																		'keyup':function (obj, e) {
																			if (Ext.isEmpty(obj.getValue())) obj.setValue(0);
																			win.findById('swERPEW_RA_IVLParameter_PcentMinVol_Hid').setValue(obj.getValue());
																		}
																	}
																})
															]														
														},
														{xtype:'label',text: '%',style: 'font-size: 10pt;  margin-left: 2pt; margin-top: 1pt'},											
														{
															id: 'swERPEW_RA_IVLParameter_PcentMinVol_Hid',
															value:0,
															xtype:'hidden'
														}
													]
												},
												//Два ASV максимум
												{							
													layout:'column',
													id: 'swERPEW_RA_IVLParameter_TwoASVMax_Panel',
													labelWidth:300,
													border:false,
													items:[	
														{
															layout:'form',
															items:[
																new Ext.form.NumberField({
																	value: 0,
																	id: 'swERPEW_RA_IVLParameter_TwoASVMax',
																	fieldLabel:'Два ASV максимум',
																	labelSeparator: '',
																	enableKeyEvents: true,
																	width: 60,
																	listeners:{
																		'keyup':function (obj, e) {
																			if (Ext.isEmpty(obj.getValue())) obj.setValue(0);
																			win.findById('swERPEW_RA_IVLParameter_TwoASVMax_Hid').setValue(obj.getValue());
																		}
																	}
																})
															]														
														},
														{xtype:'label',text: 'см вд ст',style: 'font-size: 10pt;  margin-left: 2pt; margin-top: 1pt'},											
														{
															id: 'swERPEW_RA_IVLParameter_TwoASVMax_Hid',
															value:0,
															xtype:'hidden'
														}
													]
												},
												//частота дыхания заданная (ЧД)
												{							
													layout:'column',
													id: 'swERPEW_RA_IVLParameter_FrequSet_Panel',
													labelWidth:300,
													border:false,
													items:[	
														{
															layout:'form',
															items:[
																new Ext.form.NumberField({
																	value: 0,
																	id: 'swERPEW_RA_IVLParameter_FrequSet',
																	fieldLabel:'Частота дыхания заданная - F',
																	labelSeparator: '',
																	enableKeyEvents: true,
																	width: 60,
																	listeners:{
																		'keyup':function (obj, e) {
																			if (Ext.isEmpty(obj.getValue())) obj.setValue(0);
																			win.findById('swERPEW_RA_IVLParameter_FrequSet_Hid').setValue(obj.getValue());
																		}
																	}
																})
															]														
														},
														{xtype:'label',text: 'раз / мин',style: 'font-size: 10pt;  margin-left: 2pt; margin-top: 1pt'},											
														{
															id: 'swERPEW_RA_IVLParameter_FrequSet_Hid',
															value:0,
															xtype:'hidden'
														}
													]
												},
												//Объём дыхания заданный
												{							
													layout:'column',
													id: 'swERPEW_RA_IVLParameter_VolInsp_Panel',
													labelWidth:300,
													border:false,
													items:[	
														{
															layout:'form',
															items:[
																new Ext.form.NumberField({
																	value: 0,
																	id: 'swERPEW_RA_IVLParameter_VolInsp',
																	fieldLabel:'Объём дыхания заданный - Vinsp',
																	labelSeparator: '',
																	enableKeyEvents: true,
																	width: 60,
																	listeners:{
																		'keyup':function (obj, e) {
																			if (Ext.isEmpty(obj.getValue())) obj.setValue(0);
																			win.findById('swERPEW_RA_IVLParameter_VolInsp_Hid').setValue(obj.getValue());
																		}
																	}
																})
															]														
														},
														{xtype:'label',text: 'мл',style: 'font-size: 10pt;  margin-left: 2pt; margin-top: 1pt'},											
														{
															id: 'swERPEW_RA_IVLParameter_VolInsp_Hid',
															value:0,
															xtype:'hidden'
														}
													]
												},
												//Давление вдоха заданное
												{							
													layout:'column',
													id: 'swERPEW_RA_IVLParameter_PressInsp_Panel',
													labelWidth:300,
													border:false,
													items:[	
														{
															layout:'form',
															items:[
																new Ext.form.NumberField({
																	value: 0,
																	id: 'swERPEW_RA_IVLParameter_PressInsp',
																	fieldLabel:'Давление вдоха заданное - Pinsp',
																	labelSeparator: '',
																	enableKeyEvents: true,
																	width: 60,
																	listeners:{
																		'keyup':function (obj, e) {
																			if (Ext.isEmpty(obj.getValue())) obj.setValue(0);
																			win.findById('swERPEW_RA_IVLParameter_PressInsp_Hid').setValue(obj.getValue());
																		}
																	}
																})
															]														
														},
														{xtype:'label',text: 'см вд ст',style: 'font-size: 10pt;  margin-left: 2pt; margin-top: 1pt'},											
														{
															id: 'swERPEW_RA_IVLParameter_PressInsp_Hid',
															value:0,
															xtype:'hidden'
														}
													]
												},
												//Поддержка давлением
												{							
													layout:'column',
													id: 'swERPEW_RA_IVLParameter_PressSupp_Panel',
													labelWidth:300,
													border:false,
													items:[	
														{
															layout:'form',
															items:[
																new Ext.form.NumberField({
																	value: 0,
																	id: 'swERPEW_RA_IVLParameter_PressSupp',
																	fieldLabel:'Поддержка давлением - PS',
																	labelSeparator: '',
																	enableKeyEvents: true,
																	width: 60,
																	listeners:{
																		'keyup':function (obj, e) {
																			if (Ext.isEmpty(obj.getValue())) obj.setValue(0);
																			win.findById('swERPEW_RA_IVLParameter_PressSupp_Hid').setValue(obj.getValue());
																		}
																	}
																})
															]														
														},
														{xtype:'label',text: 'см вд ст',style: 'font-size: 10pt;  margin-left: 2pt; margin-top: 1pt'},											
														{
															id: 'swERPEW_RA_IVLParameter_PressSupp_Hid',
															value:0,
															xtype:'hidden'
														}
													]
												},
												//Общее количество вдохов
												{							
													layout:'column',
													id: 'swERPEW_RA_IVLParameter_FrequTotal_Panel',
													labelWidth:300,
													border:false,
													items:[	
														{
															layout:'form',
															items:[
																new Ext.form.NumberField({
																	value: 0,
																	id: 'swERPEW_RA_IVLParameter_FrequTotal',
																	fieldLabel:'Общее количество вдохов - Ftotal',
																	labelSeparator: '',
																	enableKeyEvents: true,
																	width: 60,
																	listeners:{
																		'keyup':function (obj, e) {
																			if (Ext.isEmpty(obj.getValue())) obj.setValue(0);
																			win.findById('swERPEW_RA_IVLParameter_FrequTotal_Hid').setValue(obj.getValue());
																		}
																	}
																})
															]														
														},
														{xtype:'label',id: 'swERPEW_RA_IVLParameter_FrequTotal_Unit',text: 'раз / мин',style: 'font-size: 10pt;  margin-left: 2pt; margin-top: 1pt'},											
														{
															id: 'swERPEW_RA_IVLParameter_FrequTotal_Hid',
															value:0,
															xtype:'hidden'
														}
													]
												},
												//Объём реально вдыхаемый //BOB - 29.02.2020
												{							
													layout:'column',
													id: 'swERPEW_RA_IVLParameter_VolTi_Panel',
													labelWidth:300,
													border:false,
													items:[	
														{
															layout:'form',
															items:[
																new Ext.form.NumberField({
																	value: 0,
																	id: 'swERPEW_RA_IVLParameter_VolTi',
																	fieldLabel:'Объём реально вдыхаемый - Vti',
																	labelSeparator: '',
																	enableKeyEvents: true,
																	width: 60,
																	listeners:{
																		'keyup':function (obj, e) {
																			if (Ext.isEmpty(obj.getValue())) obj.setValue(0);
																			win.findById('swERPEW_RA_IVLParameter_VolTi_Hid').setValue(obj.getValue());
																		}
																	}
																})
															]														
														},
														{xtype:'label',text: 'мл',style: 'font-size: 10pt;  margin-left: 2pt; margin-top: 1pt'},											
														{
															id: 'swERPEW_RA_IVLParameter_VolTi_Hid',
															value:0,
															xtype:'hidden'
														}
													]
												},
												//Объём реально выдыхаемый
												{							
													layout:'column',
													id: 'swERPEW_RA_IVLParameter_VolTe_Panel',
													labelWidth:300,
													border:false,
													items:[	
														{
															layout:'form',
															items:[
																new Ext.form.NumberField({
																	value: 0,
																	id: 'swERPEW_RA_IVLParameter_VolTe',
																	fieldLabel:'Объём реально выдыхаемый - Vte',
																	labelSeparator: '',
																	enableKeyEvents: true,
																	width: 60,
																	listeners:{
																		'keyup':function (obj, e) {
																			if (Ext.isEmpty(obj.getValue())) obj.setValue(0);
																			win.findById('swERPEW_RA_IVLParameter_VolTe_Hid').setValue(obj.getValue());
																		}
																	}
																})
															]														
														},
														{xtype:'label',text: 'мл',style: 'font-size: 10pt;  margin-left: 2pt; margin-top: 1pt'},											
														{
															id: 'swERPEW_RA_IVLParameter_VolTe_Hid',
															value:0,
															xtype:'hidden'
														}
													]
												},
												//Минутный объём дыхания
												{							
													layout:'column',
													id: 'swERPEW_RA_IVLParameter_VolE_Panel',
													labelWidth:300,
													border:false,
													items:[	
														{
															layout:'form',
															items:[
																new Ext.form.NumberField({
																	value: 0,
																	id: 'swERPEW_RA_IVLParameter_VolE',
																	fieldLabel:'Минутный объём дыхания - Ve',
																	labelSeparator: '',
																	enableKeyEvents: true,
																	width: 60,
																	listeners:{
																		'keyup':function (obj, e) {
																			if (Ext.isEmpty(obj.getValue())) obj.setValue(0);
																			win.findById('swERPEW_RA_IVLParameter_VolE_Hid').setValue(obj.getValue());
																		}
																	}
																})
															]														
														},
														{xtype:'label',text: 'мл',style: 'font-size: 10pt;  margin-left: 2pt; margin-top: 1pt'},											
														{
															id: 'swERPEW_RA_IVLParameter_VolE_Hid',
															value:0,
															xtype:'hidden'
														}
													]
												},
												//Соотношение времён вдоха и выдоха
												{							
													layout:'column',
													id: 'swERPEW_RA_IVLParameter_TinTet_Panel',
													labelWidth:300,
													border:false,
													items:[	
														{
															layout:'form',
															items:[
																new Ext.form.NumberField({
																	value: 0,
																	id: 'swERPEW_RA_IVLParameter_Tin',
																	fieldLabel:'Соотношение времён вдоха и выдоха - Tin : Texp',
																	labelSeparator: '',
																	enableKeyEvents: true,
																	width: 60,
																	listeners:{
																		'keyup':function (obj, e) {
																			if (Ext.isEmpty(obj.getValue())) obj.setValue(0);
																			win.findById('swERPEW_RA_IVLParameter_Tin_Hid').setValue(obj.getValue());
																		}
																	}
																})
															]														
														},
														{xtype:'label',text: ':',style: 'font-size: 12pt;  margin-left: 4pt; margin-top: 0pt'},											
														{
															id: 'swERPEW_RA_IVLParameter_Tin_Hid',
															value:0,
															xtype:'hidden'
														},
														{
															layout:'form',
															labelWidth:1,
															items:[
																new Ext.form.NumberField({
																	value: 0,
																	id: 'swERPEW_RA_IVLParameter_Tet',
																	fieldLabel:'',
																	labelSeparator: '',
																	enableKeyEvents: true,
																	width: 60,
																	listeners:{
																		'keyup':function (obj, e) {
																			if (Ext.isEmpty(obj.getValue())) obj.setValue(0);
																			win.findById('swERPEW_RA_IVLParameter_Tet_Hid').setValue(obj.getValue());
																		}
																	}
																})
															]														
														},											
														{
															id: 'swERPEW_RA_IVLParameter_Tet_Hid',
															value:0,
															xtype:'hidden'
														}
													]
												},
												//Триггер по объёму / давлению
												{							
													layout:'column',
													id: 'swERPEW_RA_IVLParameter_Trig_Panel',
													labelWidth:300,
													border:false,
													items:[	
														{
															layout:'form',
															items:[
																new Ext.form.NumberField({
																	value: 0,
																	id: 'swERPEW_RA_IVLParameter_VolTrig',
																	fieldLabel:'Триггер по объёму - Vtrig',
																	labelSeparator: '',
																	enableKeyEvents: true,
																	width: 60,
																	listeners:{
																		'keyup':function (obj, e) {
																			if (Ext.isEmpty(obj.getValue())) obj.setValue(0);
																			win.findById('swERPEW_RA_IVLParameter_VolTrig_Hid').setValue(obj.getValue());
																		}
																	}
																})
															]														
														},
														{xtype:'label',text: 'мл / мин',style: 'font-size: 10pt;  margin-left: 2pt; margin-top: 2pt'},											
														{
															id: 'swERPEW_RA_IVLParameter_VolTrig_Hid',
															value:0,
															xtype:'hidden'
														},
														{
															layout:'form',
															items:[
																new Ext.form.NumberField({
																	value: 0,
																	id: 'swERPEW_RA_IVLParameter_PressTrig',
																	fieldLabel:'Триггер по давлению - Ptrig',
																	labelSeparator: '',
																	enableKeyEvents: true,
																	width: 60,
																	listeners:{
																		'keyup':function (obj, e) {
																			if (Ext.isEmpty(obj.getValue())) obj.setValue(0);
																			win.findById('swERPEW_RA_IVLParameter_PressTrig_Hid').setValue(obj.getValue());
																		}
																	}
																})
															]														
														},
														{xtype:'label',text: 'см вд ст',style: 'font-size: 10pt;  margin-left: 2pt; margin-top: 2pt'},											
														{
															id: 'swERPEW_RA_IVLParameter_PressTrig_Hid',
															value:0,
															xtype:'hidden'
														}
													]
												},
												//Пиковое давление на вдохе  //BOB - 29.02.2020
												{							
													layout:'column',
													id: 'swERPEW_RA_IVLParameter_Peak_Panel',
													labelWidth:300,
													border:false,
													items:[	
														{
															layout:'form',
															items:[
																new Ext.form.NumberField({
																	value: 0,
																	id: 'swERPEW_RA_IVLParameter_Peak',
																	fieldLabel:'Пиковое давление на вдохе - Peak',
																	labelSeparator: '',
																	enableKeyEvents: true,
																	width: 60,
																	listeners:{
																		'keyup':function (obj, e) {
																			if (Ext.isEmpty(obj.getValue())) obj.setValue(0);
																			win.findById('swERPEW_RA_IVLParameter_Peak_Hid').setValue(obj.getValue());
																		}
																	}
																})
															]														
														},
														{xtype:'label',text: 'см вд ст',style: 'font-size: 10pt;  margin-left: 2pt; margin-top: 1pt'},											
														{
															id: 'swERPEW_RA_IVLParameter_Peak_Hid',
															value:0,
															xtype:'hidden'
														}
													]
												},
												//Положительное давление в конце выдоха
												{							
													layout:'column',
													id: 'swERPEW_RA_IVLParameter_PEEP_Panel',
													labelWidth:300,
													border:false,
													items:[	
														{
															layout:'form',
															items:[
																new Ext.form.NumberField({
																	value: 0,
																	id: 'swERPEW_RA_IVLParameter_PEEP',
																	fieldLabel:'Давление в конце выдоха - PEEP',
																	labelSeparator: '',
																	enableKeyEvents: true,
																	width: 60,
																	listeners:{
																		'keyup':function (obj, e) {
																			if (Ext.isEmpty(obj.getValue())) obj.setValue(0);
																			win.findById('swERPEW_RA_IVLParameter_PEEP_Hid').setValue(obj.getValue());
																		}
																	}
																})
															]														
														},
														{xtype:'label',text: 'см вд ст',style: 'font-size: 10pt;  margin-left: 2pt; margin-top: 1pt'},											
														{
															id: 'swERPEW_RA_IVLParameter_PEEP_Hid',
															value:0,
															xtype:'hidden'
														}
													]
												},
												//Среднее давление в дыхательных путях //BOB - 29.02.2020
												{							
													layout:'column',
													id: 'swERPEW_RA_IVLParameter_MAP_Panel',
													labelWidth:300,
													border:false,
													items:[	
														{
															layout:'form',
															items:[
																new Ext.form.NumberField({
																	value: 0,
																	id: 'swERPEW_RA_IVLParameter_MAP',
																	fieldLabel:'Среднее давление в дыхательных путях - MAP',
																	labelSeparator: '',
																	enableKeyEvents: true,
																	width: 60,
																	listeners:{
																		'keyup':function (obj, e) {
																			if (Ext.isEmpty(obj.getValue())) obj.setValue(0);
																			win.findById('swERPEW_RA_IVLParameter_MAP_Hid').setValue(obj.getValue());
																		}
																	}
																})
															]														
														},
														{xtype:'label',text: 'см вд ст',style: 'font-size: 10pt;  margin-left: 2pt; margin-top: 1pt'},											
														{
															id: 'swERPEW_RA_IVLParameter_MAP_Hid',
															value:0,
															xtype:'hidden'
														}
													]
												},
												//Время вдоха //BOB - 29.02.2020
												{							
													layout:'column',
													id: 'swERPEW_RA_IVLParameter_Tins_Panel',
													labelWidth:300,
													border:false,
													items:[	
														{
															layout:'form',
															items:[
																new Ext.form.NumberField({
																	value: 0,
																	id: 'swERPEW_RA_IVLParameter_Tins',
																	fieldLabel:'Время вдоха - Tins',
																	labelSeparator: '',
																	enableKeyEvents: true,
																	width: 60,
																	listeners:{
																		'keyup':function (obj, e) {
																			if (Ext.isEmpty(obj.getValue())) obj.setValue(0);
																			win.findById('swERPEW_RA_IVLParameter_Tins_Hid').setValue(obj.getValue());
																		}
																	}
																})
															]														
														},
														{xtype:'label',text: 'сек',style: 'font-size: 10pt;  margin-left: 2pt; margin-top: 1pt'},											
														{
															id: 'swERPEW_RA_IVLParameter_Tins_Hid',
															value:0,
															xtype:'hidden'
														}
													]
												},
												//Поток (максимальный) //BOB - 29.02.2020
												{							
													layout:'column',
													id: 'swERPEW_RA_IVLParameter_FlowMax_Panel',
													labelWidth:300,
													border:false,
													items:[	
														{
															layout:'form',
															items:[
																new Ext.form.NumberField({
																	value: 0,
																	id: 'swERPEW_RA_IVLParameter_FlowMax',
																	fieldLabel:'Поток - Flow',
																	labelSeparator: '',
																	enableKeyEvents: true,
																	width: 60,
																	listeners:{
																		'keyup':function (obj, e) {
																			if (Ext.isEmpty(obj.getValue())) obj.setValue(0);
																			win.findById('swERPEW_RA_IVLParameter_FlowMax_Hid').setValue(obj.getValue());
																		}
																	}
																})
															]														
														},
														{xtype:'label',text: 'л/мин',style: 'font-size: 10pt;  margin-left: 2pt; margin-top: 1pt'},											
														{
															id: 'swERPEW_RA_IVLParameter_FlowMax_Hid',
															value:0,
															xtype:'hidden'
														}
													]
												},
												//Минимальный поток //BOB - 29.02.2020
												{							
													layout:'column',
													id: 'swERPEW_RA_IVLParameter_FlowMin_Panel',
													labelWidth:300,
													border:false,
													items:[	
														{
															layout:'form',
															items:[
																new Ext.form.NumberField({
																	value: 0,
																	id: 'swERPEW_RA_IVLParameter_FlowMin',
																	fieldLabel:'Минимальный поток - Flowmin',
																	labelSeparator: '',
																	enableKeyEvents: true,
																	width: 60,
																	listeners:{
																		'keyup':function (obj, e) {
																			if (Ext.isEmpty(obj.getValue())) obj.setValue(0);
																			win.findById('swERPEW_RA_IVLParameter_FlowMin_Hid').setValue(obj.getValue());
																		}
																	}
																})
															]														
														},
														{xtype:'label',text: 'л/мин',style: 'font-size: 10pt;  margin-left: 2pt; margin-top: 1pt'},											
														{
															id: 'swERPEW_RA_IVLParameter_FlowMin_Hid',
															value:0,
															xtype:'hidden'
														}
													]
												},
												//Амплитуда давления //BOB - 29.02.2020
												{							
													layout:'column',
													id: 'swERPEW_RA_IVLParameter_deltaP_Panel',
													labelWidth:300,
													border:false,
													items:[	
														{
															layout:'form',
															items:[
																new Ext.form.NumberField({
																	value: 0,
																	id: 'swERPEW_RA_IVLParameter_deltaP',
																	fieldLabel:'Амплитуда давления - delta P',
																	labelSeparator: '',
																	enableKeyEvents: true,
																	width: 60,
																	listeners:{
																		'keyup':function (obj, e) {
																			if (Ext.isEmpty(obj.getValue())) obj.setValue(0);
																			win.findById('swERPEW_RA_IVLParameter_deltaP_Hid').setValue(obj.getValue());
																		}
																	}
																})
															]														
														},
														{xtype:'label',text: 'см вд ст',style: 'font-size: 10pt;  margin-left: 2pt; margin-top: 1pt'},											
														{
															id: 'swERPEW_RA_IVLParameter_deltaP_Hid',
															value:0,
															xtype:'hidden'
														}
													]
												},
												//Другое //BOB - 29.02.2020
												{							
													layout:'column',
													id: 'swERPEW_RA_IVLParameter_Other_Panel',
													labelWidth:300,
													border:false,
													items:[															
														{
															layout:'form',
															items:[
																{
																	xtype: 'textfield',
																	id: 'swERPEW_RA_IVLParameter_Other',
																	fieldLabel:'Другое',
																	labelSeparator: '',
																	width: 270,
																	enableKeyEvents: true,
																	listeners:{
																		'keyup':function (obj, e) {
																			win.findById('swERPEW_RA_IVLParameter_Other_Hid').setValue(obj.getValue());
																		}
																	}
																}
															]														
														},
														//{xtype:'label',text: ':',style: 'font-size: 12pt;  margin-left: 4pt; margin-top: 0pt'},											
														{
															id: 'swERPEW_RA_IVLParameter_Other_Hid',
															value:0,
															xtype:'hidden'
														}
													]
												}
											],
											//BOB - 03.11.2018 визуализация панели параметров ИВЛ
											ParamVisualisation: function(Show, Rejim, Apparat) {
												var SysNick = '';
												// делаю невидимыми 
												Ext.select('#swERPEW_RA_IVLParameter_FrequSet_Panel').setStyle('display', 'none');
												Ext.select('#swERPEW_RA_IVLParameter_VolInsp_Panel').setStyle('display', 'none');
												Ext.select('#swERPEW_RA_IVLParameter_PressInsp_Panel').setStyle('display', 'none');
												Ext.select('#swERPEW_RA_IVLParameter_PressSupp_Panel').setStyle('display', 'none');
												win.findById('swERPEW_RA_IVLParameter_PressSupp').setFieldLabel('Поддержка давлением - PS');
												Ext.select('#swERPEW_RA_IVLParameter_PcentMinVol_Panel').setStyle('display', 'none');
												Ext.select('#swERPEW_RA_IVLParameter_TwoASVMax_Panel').setStyle('display', 'none');
												Ext.select('#swERPEW_RA_IVLParameter_TubeDiam_Panel').setStyle('display', 'none');
												Ext.select('#swERPEW_RA_IVLParameter_FiO2_Panel').setStyle('display', 'none');
												Ext.select('#swERPEW_RA_IVLParameter_FrequTotal_Panel').setStyle('display', 'none');
												win.findById('swERPEW_RA_IVLParameter_FrequTotal').setFieldLabel('Общее количество вдохов - Ftotal');
												win.findById('swERPEW_RA_IVLParameter_FrequTotal_Unit').setText('раз / мин')
												Ext.select('#swERPEW_RA_IVLParameter_VolTe_Panel').setStyle('display', 'none');
												Ext.select('#swERPEW_RA_IVLParameter_VolE_Panel').setStyle('display', 'none');
												Ext.select('#swERPEW_RA_IVLParameter_TinTet_Panel').setStyle('display', 'none');
												Ext.select('#swERPEW_RA_IVLParameter_Trig_Panel').setStyle('display', 'none');
												Ext.select('#swERPEW_RA_IVLParameter_PEEP_Panel').setStyle('display', 'none');
												//BOB - 29.02.2020 - младенчество
												Ext.select('#swERPEW_RA_IVLParameter_VolTi_Panel').setStyle('display', 'none');
												Ext.select('#swERPEW_RA_IVLParameter_Peak_Panel').setStyle('display', 'none');
												Ext.select('#swERPEW_RA_IVLParameter_MAP_Panel').setStyle('display', 'none');
												win.findById('swERPEW_RA_IVLParameter_MAP').setFieldLabel('Среднее давление в дыхательных путях - MAP');
												Ext.select('#swERPEW_RA_IVLParameter_Tins_Panel').setStyle('display', 'none');
												Ext.select('#swERPEW_RA_IVLParameter_FlowMax_Panel').setStyle('display', 'none');
												win.findById('swERPEW_RA_IVLParameter_FlowMax').setFieldLabel('Поток - Flow');
												Ext.select('#swERPEW_RA_IVLParameter_FlowMin_Panel').setStyle('display', 'none');
												Ext.select('#swERPEW_RA_IVLParameter_deltaP_Panel').setStyle('display', 'none');
												Ext.select('#swERPEW_RA_IVLParameter_Other_Panel').setStyle('display', 'none');
												//BOB - 29.02.2020
												if (Show) {
													if (win.isNeonatal(win.findById('swERPEW_ReanimatAgeGroup').getValue())) { // младенцы
														SysNick = Apparat;
														if (Apparat.indexOf('Avea') > -1) SysNick = 'Avea';
														else if (Apparat.indexOf('Servo I') > -1) SysNick = 'Servo I';
														else if (Apparat.indexOf('Sensor Medics 3100A') > -1) SysNick = 'Sensor Medics 3100A';
														else if (Apparat.indexOf('Infant Flow') > -1) SysNick = 'Infant Flow';

														Ext.select('#swERPEW_RA_IVLParameter_FiO2_Panel').setStyle('display', 'block');
														switch (SysNick) {
															case 'Avea':
															case 'Servo I':
																Ext.select('#swERPEW_RA_IVLParameter_PressInsp_Panel').setStyle('display', 'block');
																Ext.select('#swERPEW_RA_IVLParameter_VolTi_Panel').setStyle('display', 'block');
																Ext.select('#swERPEW_RA_IVLParameter_VolTe_Panel').setStyle('display', 'block');
																Ext.select('#swERPEW_RA_IVLParameter_Peak_Panel').setStyle('display', 'block');
																Ext.select('#swERPEW_RA_IVLParameter_PEEP_Panel').setStyle('display', 'block');
																Ext.select('#swERPEW_RA_IVLParameter_MAP_Panel').setStyle('display', 'block');
																Ext.select('#swERPEW_RA_IVLParameter_Tins_Panel').setStyle('display', 'block');
																Ext.select('#swERPEW_RA_IVLParameter_PressSupp_Panel').setStyle('display', 'block');  //!!!!!!!переименовать
																win.findById('swERPEW_RA_IVLParameter_PressSupp').setFieldLabel('Предварительно установленный уровень вентиляции с поддержкой давления - PSV');
																Ext.select('#swERPEW_RA_IVLParameter_FlowMax_Panel').setStyle('display', 'block');
																if (SysNick == 'Servo I') {
																	Ext.select('#swERPEW_RA_IVLParameter_FrequTotal_Panel').setStyle('display', 'block');  //!!!!!!!переименовать
																	win.findById('swERPEW_RA_IVLParameter_FrequTotal').setFieldLabel('Частота дыхания - Fr');
																}
																Ext.select('#swERPEW_RA_IVLParameter_Other_Panel').setStyle('display', 'block');
																break;
															case 'Sensor Medics 3100A':
																Ext.select('#swERPEW_RA_IVLParameter_MAP_Panel').setStyle('display', 'block');  //!!!!!!!переименовать
																win.findById('swERPEW_RA_IVLParameter_MAP').setFieldLabel('Среднее установленное давление в дыхательных путях - Paw');
																Ext.select('#swERPEW_RA_IVLParameter_FrequTotal_Panel').setStyle('display', 'block');  //!!!!!!!переименовать
																win.findById('swERPEW_RA_IVLParameter_FrequTotal').setFieldLabel('Частота дыхания в герцах - Hz');
																win.findById('swERPEW_RA_IVLParameter_FrequTotal_Unit').setText('Гц')
																Ext.select('#swERPEW_RA_IVLParameter_deltaP_Panel').setStyle('display', 'block');
																break;
															case 'Infant Flow':
																Ext.select('#swERPEW_RA_IVLParameter_FlowMax_Panel').setStyle('display', 'block');   //!!!!!!!переименовать
																win.findById('swERPEW_RA_IVLParameter_FlowMax').setFieldLabel('Максимальный поток - Flowmax');
																Ext.select('#swERPEW_RA_IVLParameter_FlowMin_Panel').setStyle('display', 'block');
																Ext.select('#swERPEW_RA_IVLParameter_FrequTotal_Panel').setStyle('display', 'block');  //!!!!!!!переименовать
																win.findById('swERPEW_RA_IVLParameter_FrequTotal').setFieldLabel('Частота дыхания - Fr');
																break;
						
														}
													} else {   //взрослые
														// делаю видимыми	
														SysNick = Rejim;

														switch (SysNick) {
															case 'cmv_vc':
																Ext.select('#swERPEW_RA_IVLParameter_FrequSet_Panel').setStyle('display', 'block');
																Ext.select('#swERPEW_RA_IVLParameter_VolInsp_Panel').setStyle('display', 'block');
																break;
															case 'cmv_pc':
																Ext.select('#swERPEW_RA_IVLParameter_FrequSet_Panel').setStyle('display', 'block');
																Ext.select('#swERPEW_RA_IVLParameter_PressInsp_Panel').setStyle('display', 'block');
																break;
															case 'simv_vc':
																Ext.select('#swERPEW_RA_IVLParameter_FrequSet_Panel').setStyle('display', 'block');
																Ext.select('#swERPEW_RA_IVLParameter_VolInsp_Panel').setStyle('display', 'block');
																Ext.select('#swERPEW_RA_IVLParameter_PressSupp_Panel').setStyle('display', 'block');
																break;
															case 'simv_pc':
																Ext.select('#swERPEW_RA_IVLParameter_FrequSet_Panel').setStyle('display', 'block');
																Ext.select('#swERPEW_RA_IVLParameter_PressInsp_Panel').setStyle('display', 'block');
																Ext.select('#swERPEW_RA_IVLParameter_PressSupp_Panel').setStyle('display', 'block');
																break;
															case 'psv':
																Ext.select('#swERPEW_RA_IVLParameter_PressSupp_Panel').setStyle('display', 'block');
																break;
															case 'asv':
																Ext.select('#swERPEW_RA_IVLParameter_PcentMinVol_Panel').setStyle('display', 'block');
																Ext.select('#swERPEW_RA_IVLParameter_TwoASVMax_Panel').setStyle('display', 'block');
																break;																					
														}

														Ext.select('#swERPEW_RA_IVLParameter_TubeDiam_Panel').setStyle('display', 'block');
														Ext.select('#swERPEW_RA_IVLParameter_FiO2_Panel').setStyle('display', 'block');
														Ext.select('#swERPEW_RA_IVLParameter_FrequTotal_Panel').setStyle('display', 'block');
														Ext.select('#swERPEW_RA_IVLParameter_VolTe_Panel').setStyle('display', 'block');
														Ext.select('#swERPEW_RA_IVLParameter_VolE_Panel').setStyle('display', 'block');
														Ext.select('#swERPEW_RA_IVLParameter_TinTet_Panel').setStyle('display', 'block');
														Ext.select('#swERPEW_RA_IVLParameter_Trig_Panel').setStyle('display', 'block');
														Ext.select('#swERPEW_RA_IVLParameter_PEEP_Panel').setStyle('display', 'block');
													}
												}				
											}
										},
										//BOB - 03.12.2018
										//BOB - 22.02.2019
										//панель - сердечно-лёгочная реанимация
										{
											layout:'form',
											id: 'swERPEW_RA_CardPulm_Panel',
											//border:false,
											border:true,
											style: 'border-top: 1px solid #99bbe8; border-left: 1px solid #99bbe8; border-bottom: 1px solid #ffffff;border-right: 1px solid #ffffff; padding: 2px;  ',
											CardPulm_Data: {},
											RadioEventExec: true,
											items:[	
												//BOB - 04.07.2019
												{
													id: 'swERPEW_RA_CardPulm_id',
													value:'',
													xtype:'hidden'
												},
												//Дата Время фиксации клинической смерти
												{									
													layout:'column',
													region: 'center',
													items:[

														//Дата фиксации клинической смерти
														{									
															layout:'form',
															width: 400,
															labelWidth: 230,
															items:[										
																{
																	allowBlank: false,
																	fieldLabel:'Фиксация клинической смерти: дата',
																	format:'d.m.Y',
																	id: 'swERPEW_RA_ClinicalDeath_Date',
																	listeners:{
																		'change':function (field, newValue, oldValue) {	
																			console.log('BOB_newValue=',newValue);
																			win.findById('swERPEW_RA_CardPulm_Panel').CardPulm_Data.ReanimatCardPulm_ClinicalDeathDate = newValue;
																		}.createDelegate(this)
																	},
																	name:'RA_ClinicalDeath_Date',
																	plugins:[ new Ext.ux.InputTextMask('99.99.9999', false) ],
																	selectOnFocus:true,
																			//tabIndex:this.tabIndex + 1,
																	width:100,
																	xtype:'swdatefield'
																}										
															]
														},	
														//Время фиксации клинической смерти
														{									
															layout:'form',
															width: 120,
															labelWidth: 50,
															items:[	

																{
																	fieldLabel:'время',
																	allowBlank: false,
																	listeners:{
																		'change':function (field, newValue, oldValue) {
																			win.findById('swERPEW_RA_CardPulm_Panel').CardPulm_Data.ReanimatCardPulm_ClinicalDeathTime = newValue;
																		}.createDelegate(this)
																	},
																	id: 'swERPEW_RA_ClinicalDeath_Time',
																	name:'RA_ClinicalDeath_Time',
																	onTriggerClick:function () {
																		setCurrentDateTime({
																			callback:function () {
																				win.findById('swERPEW_RA_CardPulm_Panel').CardPulm_Data.ReanimatCardPulm_ClinicalDeathTime = win.findById('swERPEW_RA_ClinicalDeath_Time').getValue();
																				this.findById('swERPEW_RA_ClinicalDeath_Date').fireEvent('change', this.findById('swERPEW_RA_ClinicalDeath_Date'), this.findById('swERPEW_RA_ClinicalDeath_Date').getValue());
																			}.createDelegate(this),
																			dateField:this.findById('swERPEW_RA_ClinicalDeath_Date'),
																			loadMask:true,
																			setDate:true,
																			setDateMaxValue:true,
																			addMaxDateDays: this.addMaxDateDays,
																			setDateMinValue:false,
																			setTime:true,
																			timeField:this.findById('swERPEW_RA_ClinicalDeath_Time'),
																			windowId:this.id
																		});
																	}.createDelegate(this),
																	plugins:[ new Ext.ux.InputTextMask('99:99', true) ],
																	//tabIndex:this.tabIndex + 4,
																	validateOnBlur:false,
																	width:60,
																	xtype:'swtimefield'
																}										

															]
														}
													]
												},
												//Зрачки 
												{									
													layout:'column',
													region: 'center',
													items:[
														//Лейбл Зрачки  
														{									
															layout:'form',
															style:' margin-left: 14px; margin-top: 8px; font-size: 12px ',
															items:[
																new Ext.form.Label({
																	text: 'Зрачки'
																})					
															]
														},
														{									
															layout:'form',
															labelWidth:1,
															style: 'margin-top: 4px;  margin-left: 4px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
															items:[
																new Ext.form.RadioGroup({
																	id:'swERPEW_RA_IsPupilDilat',
																	labelSeparator: '',
																	vertical: true,
																	columns: 2,
																	items: [
																		{boxLabel: 'не расширены', name: 'IsPupilDilat', inputValue: 1, width: 120},
																		{boxLabel: 'расширены', name: 'IsPupilDilat', inputValue: 2, width: 120}
																	],
																	listeners: {
																		'change': function(field, checked) {
																			if((checked) && (win.findById('swERPEW_RA_CardPulm_Panel').RadioEventExec))
																				win.findById('swERPEW_RA_CardPulm_Panel').CardPulm_Data.ReanimatCardPulm_IsPupilDilat = checked.inputValue;
																		}
																	}
																})	
															]
														}
													]
												},
												//Кардиомониторирование в момент остановки кровообращения 
												{									
													layout:'column',
													region: 'center',
													items:[
														//Лейбл Кардиомониторирование в момент остановки кровообращения 
														{									
															layout:'form',
															style:' margin-left: 14px; margin-top: 8px; font-size: 12px ',
															items:[
																new Ext.form.Label({
																	text: 'Кардиомониторирование в момент остановки кровообращения'
																})					
															]
														},
														{									
															layout:'form',
															labelWidth:1,
															style: 'margin-top: 4px;  margin-left: 4px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
															items:[
																new Ext.form.RadioGroup({
																	id:'swERPEW_RA_IsCardMonitor',
																	labelSeparator: '',
																	vertical: true,
																	columns: 2,
																	items: [
																		{boxLabel: 'не проводится', name: 'IsCardMonitor', inputValue: 1, width: 120},
																		{boxLabel: 'проводится', name: 'IsCardMonitor', inputValue: 2, width: 120}
																	],
																	listeners: {
																		'change': function(field, checked) {
																			if((checked) && (win.findById('swERPEW_RA_CardPulm_Panel').RadioEventExec))
																				win.findById('swERPEW_RA_CardPulm_Panel').CardPulm_Data.ReanimatCardPulm_IsCardMonitor = checked.inputValue;
																		}
																	}
																})	
															]
														}
													]
												},
												//Вид прекращения сердечной деятельности 
												{									
													layout:'column',
													region: 'center',
													items:[
														//Лейбл Вид прекращения сердечной деятельности 
														{									
															layout:'form',
															style:' margin-left: 14px; margin-top: 8px; font-size: 12px ',
															items:[
																new Ext.form.Label({
																	text: 'Вид прекращения сердечной деятельности'
																})					
															]
														},
														{									
															layout:'form',
															labelWidth:1,
															style: 'margin-top: 4px;  margin-left: 4px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
															items:[
																new Ext.form.RadioGroup({
																	id:'swERPEW_RA_StopCardActType',
																	labelSeparator: '',
																	vertical: true,
																	columns: 3,
																	items: [
																		{boxLabel: 'асистолия', name: 'StopCardActType', inputValue: 1, width: 120},
																		{boxLabel: 'неэффективное сердце', name: 'StopCardActType', inputValue: 2, width: 160},
																		{boxLabel: 'фибрилляция', name: 'StopCardActType', inputValue: 3, width: 120}
																	],
																	listeners: {
																		'change': function(field, checked) {
																			if((checked) && (win.findById('swERPEW_RA_CardPulm_Panel').RadioEventExec))
																				win.findById('swERPEW_RA_CardPulm_Panel').CardPulm_Data.ReanimatCardPulm_StopCardActType = checked.inputValue;
																		}
																	}
																})	
															]
														}
													]
												},
												//ИВЛ в момент остановки кровообращения 
												{									
													layout:'column',
													region: 'center',
													items:[
														//панель Режим ИВЛ
														{							
															layout:'form',
															id: 'swERPEW_RA_IVLRegim_Panel2',
															labelWidth: 80,
															border:false,
															items:[	
																//combo - Режим ИВЛ //BOB - 21.03.2019
																{
																	id: 'swERPEW_RA_IVLRegim2',
																	hiddenName: 'RA_IVLRegim2',
																	xtype: 'swreanimativlregimcombo',
																	allowBlank: true,
																	width: 800,
																	listeners: {
																		'select': function(combo, record, index) {
																			//если выбрана не пустая строка
																			if (record) 
																				win.findById('swERPEW_RA_CardPulm_Panel').CardPulm_Data.IVLRegim_id = record.data.IVLRegim_id;																				
																			else  //выбрана пустая строка																	
																				win.findById('swERPEW_RA_CardPulm_Panel').CardPulm_Data.IVLRegim_id = null;
																		}
																	} 
																}
															]
														},
														{
															layout:'form',
															labelWidth: 200,
															items:[
																new Ext.form.NumberField({
																	value: 0,
																	id: 'swERPEW_RA_CardPulm_FiO2',
																	fieldLabel:'Концентрация кислорода - FiO2',
																	labelSeparator: '',
																	enableKeyEvents: true,
																	width: 60,
																	listeners:{
																		'keyup':function (obj, e) {
																			win.findById('swERPEW_RA_CardPulm_Panel').CardPulm_Data.ReanimatCardPulm_FiO2 = obj.getValue();
																		}
																	}
																})
															]														
														},
														{xtype:'label',text: '%',style: 'font-size: 10pt;  margin-left: 2pt; margin-top: 1pt'},											
												
													]
												},
												//Введение кардиотоников в момент остановки кровообращения
												{									
													layout:'column',
													region: 'center',
													items:[
														//Лейбл Введение кардиотоников в момент остановки кровообращения
														{									
															layout:'form',
															style:' margin-left: 14px; margin-top: 4px; font-size: 12px ',
															items:[
																new Ext.form.Label({
																	text: 'Введение кардиотоников в момент остановки кровообращения'
																})					
															]
														},
														{									
															layout:'form',
															labelWidth:1,
															style: 'margin-top: 0px;  margin-left: 4px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
															items:[
																new Ext.form.RadioGroup({
																	id:'swERPEW_RA_IsCardTonics',
																	labelSeparator: '',
																	vertical: true,
																	columns: 2,
																	items: [
																		{boxLabel: 'не проводится', name: 'IsCardTonics', inputValue: 1, width: 120}, 
																		{boxLabel: 'проводится введение дофамина в дозе', name: 'IsCardTonics', inputValue: 2, width: 300}
																	],
																	listeners: {
																		'change': function(field, checked) {
																			if((checked) && (win.findById('swERPEW_RA_CardPulm_Panel').RadioEventExec))
																				win.findById('swERPEW_RA_CardPulm_Panel').CardPulm_Data.ReanimatCardPulm_IsCardTonics = checked.inputValue;
																		}
																	}
																})	
															]
														},
														{
															layout:'form',
															style:'margin-top: 1px;',
															labelWidth: 5,
															items:[
																new Ext.form.NumberField({
																	value: 0,
																	id: 'swERPEW_RA_CardTonicDose',
																	fieldLabel:'',
																	labelSeparator: '',
																	enableKeyEvents: true,
																	width: 60,
																	listeners:{
																		'keyup':function (obj, e) {
																			win.findById('swERPEW_RA_CardPulm_Panel').CardPulm_Data.ReanimatCardPulm_CardTonicDose = obj.getValue();
																		}
																	}
																})
															]														
														},
														{xtype:'label',text: 'мкг/кг/мин',style: 'font-size: 10pt;  margin-left: 2pt; margin-top: 2pt'}											
													]
												},
												//Катетеризация магистральной вены 
												{									
													layout:'column',
													region: 'center',
													items:[
														//Лейбл Катетеризация магистральной вены 
														{									
															layout:'form',
															style:' margin-left: 14px; margin-top: 8px; font-size: 12px ',
															items:[
																new Ext.form.Label({
																	text: 'Катетеризация магистральной вены'
																})					
															]
														},
														{									
															layout:'form',
															labelWidth:1,
															style: 'margin-top: 4px;  margin-left: 4px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
															items:[
																new Ext.form.RadioGroup({
																	id:'swERPEW_RA_CathetVein',
																	labelSeparator: '',
																	vertical: true,
																	columns: 3,
																	items: [
																		{boxLabel: 'проведена', name: 'CathetVein', inputValue: 1, width: 120},
																		{boxLabel: 'проведена во время реанимационных мероприятий', name: 'CathetVein', inputValue: 2, width: 350},
																		{boxLabel: 'не проводится', name: 'CathetVein', inputValue: 3, width: 120}
																	],
																	listeners: {
																		'change': function(field, checked) {
																			if((checked) && (win.findById('swERPEW_RA_CardPulm_Panel').RadioEventExec))
																				win.findById('swERPEW_RA_CardPulm_Panel').CardPulm_Data.ReanimatCardPulm_CathetVein = checked.inputValue;
																		}
																	}
																})	
															]
														}
													]
												},
												//Интубация трахеи трубкой №
												{
													layout:'form',
													style:'margin-top: 1px;',
													labelWidth: 185,
													items:[
														new Ext.form.NumberField({
															value: 0,
															id: 'swERPEW_RA_TrachIntub',
															fieldLabel:'Интубация трахеи трубкой №',
															labelSeparator: '',
															enableKeyEvents: true,
															width: 60,
															listeners:{
																'change':function (field, newValue, oldValue) {
																	if (newValue <= 99)
																		win.findById('swERPEW_RA_CardPulm_Panel').CardPulm_Data.ReanimatCardPulm_TrachIntub = (newValue == '' ? null : newValue); //BOB - 12.07.2019
																	else {
																		field.setValue(oldValue);
																		Ext.MessageBox.alert('Ошибка!', 'Слишком большой диаметр трубки!', function(){field.focus(true,100);});
																	}
																}.createDelegate(this)
															}
														})
													]														
												},
												//Аускультативная картина
												{
													//id: 'swERPEW_RC_Defecation_Panel',
													layout:'column',
													border:false,
													items:[	
														//Лейбл Аускультативная картина
														{									
															layout:'form',
															style:' margin-left: 14px; margin-top: 8px; font-size: 12px ',
															items:[
																new Ext.form.Label({
																	text: 'Аускультативная картина'
																})					
															]
														},
														{									
															layout:'form',
															labelWidth:1,
															style: 'margin-top: 4px;  margin-left: 4px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
															items:[
																new Ext.form.RadioGroup({
																	id:'swERPEW_RA_Auscultatory',
																	labelSeparator: '',
																	vertical: true,
																	columns: 2,
																	items: [
																		{boxLabel: 'дыхание проводится по всем полям', name: 'Auscultatory', inputValue: 1, width: 250},
																		{boxLabel: 'гиповентиляция',  name: 'Auscultatory',  inputValue: 2, width: 120 }
																	],
																	listeners: {
																		'change': function(field, checked) {
																			if((checked) && (win.findById('swERPEW_RA_CardPulm_Panel').RadioEventExec))
																				win.findById('swERPEW_RA_CardPulm_Panel').CardPulm_Data.ReanimatCardPulm_Auscultatory = checked.inputValue;
																		}
																	}
																})	
															]
														},
														//Аускультативная картина - вариант пользователя
														{							
															layout:'form',
															style:'margin-top: 4px;',
															labelWidth:55,
															border:false,
															items:[	
																{
																	allowBlank: true,
																	fieldLabel: 'Другое',
																	labelSeparator: '',
																	name: 'swERPEW_RA_AuscultatoryTxt',
																	id: 'swERPEW_RA_AuscultatoryTxt',
																	width: 1215,
																	style:'margin-top: 2px; margin-left: 4px;',
																//	tabIndex: TABINDEX_MS,
																	value:'',
																	xtype: 'textfield',
																	listeners:{
																		'change':function (field, newValue, oldValue) {
																			win.findById('swERPEW_RA_CardPulm_Panel').CardPulm_Data.ReanimatCardPulm_AuscultatoryTxt = newValue;
																		}
																	}
																}
															]
														}
													]
												},
												//Непрямой массаж сердца
												{									
													layout:'column',
													region: 'center',
													items:[
														{
															layout:'form',
															style:'margin-top: 1px;',
															labelWidth: 161,
															items:[
																new Ext.form.NumberField({
																	value: 0,
																	id: 'swERPEW_RA_CardMassage',
																	fieldLabel:'Непрямой массаж сердца',
																	labelSeparator: '',
																	enableKeyEvents: true,
																	width: 60,
																	listeners:{
																		'keyup':function (obj, e) {
																			win.findById('swERPEW_RA_CardPulm_Panel').CardPulm_Data.ReanimatCardPulm_CardMassage = obj.getValue();
																		}
																	}
																})
															]														
														},
														{xtype:'label',text: '/мин',style: 'font-size: 10pt;  margin-left: 2pt; margin-top: 2pt'}	
													]
												},
												//Дефибрилляция
												{									
													layout:'column',
													region: 'center',
													items:[
														{
															layout:'form',
															style:'margin-top: 1px;',
															labelWidth: 101,
															items:[
																new Ext.form.NumberField({
																	value: 0,
																	id: 'swERPEW_RA_DefibrilCount',
																	fieldLabel:'Дефибрилляция',
																	labelSeparator: '',
																	enableKeyEvents: true,
																	width: 60,
																	listeners:{
																		'keyup':function (obj, e) { 
																			win.findById('swERPEW_RA_CardPulm_Panel').CardPulm_Data.ReanimatCardPulm_DefibrilCount = obj.getValue();
																			
																		}
																	}
																})
															]														
														},
														{xtype:'label',text: 'раз с нарастанием мощности ',style: 'font-size: 10pt;  margin-left: 2pt; margin-top: 2pt'},	
														{
															layout:'form',
															style:'margin-top: 1px;',
															labelWidth: 20,
															items:[
																new Ext.form.NumberField({
																	value: 0,
																	id: 'swERPEW_RA_DefibrilMin',
																	fieldLabel:'от',
																	labelSeparator: '',
																	enableKeyEvents: true,
																	width: 60,
																	listeners:{
																		'keyup':function (obj, e) { 
																			win.findById('swERPEW_RA_CardPulm_Panel').CardPulm_Data.ReanimatCardPulm_DefibrilMin = obj.getValue();
																		}
																	}
																})
															]														
														},
														{
															layout:'form',
															style:'margin-top: 1px;',
															labelWidth: 40,
															items:[
																new Ext.form.NumberField({
																	value: 0,
																	id: 'swERPEW_RA_DefibrilMax',
																	fieldLabel:'Дж,   до',
																	labelSeparator: '',
																	enableKeyEvents: true,
																	width: 60,
																	listeners:{
																		'keyup':function (obj, e) { 
																			win.findById('swERPEW_RA_CardPulm_Panel').CardPulm_Data.ReanimatCardPulm_DefibrilMax = obj.getValue();
																		}
																	}
																})
															]														
														},
														{xtype:'label',text: 'Дж',style: 'font-size: 10pt;  margin-left: 2pt; margin-top: 2pt'}	
													]
												},
												//Внутривенно введено 1
												{									
													layout:'column',
													region: 'center',
													items:[
														//combo - Медикамент
														{							
															layout:'form',
															style:'margin-top: 4px;',
															labelWidth:149,
															border:false,
															items:[	
																{
																	id: 'swERPEW_RA_CardPulm_Drug_1',
																	hiddenName: 'RA_CardPulm_Drug_1',									
																	xtype: 'swextemporalcomptypecombo',
																	fieldLabel: 'Внутривенно введено 1',
																	labelSeparator: '',
																	allowBlank: true,
																	comboSubject: 'ReanimDrugType',
																	width: 240,
																	lastQuery: '',
																	listeners: {
																		'change': function(combo, newValue, oldValue) { //BOB - 12.07.2019	
																			//если выбрана не пустая строка
																			//console.log('BOBswERPEW_RA_CardPulm_Drug_1_change_oldValue=', oldValue); //BOB - 12.07.2019		
																			//console.log('BOBswERPEW_RA_CardPulm_Drug_1_change_newValue=', newValue); //BOB - 12.07.2019		
																			if (newValue == '') {
																				win.findById('swERPEW_RA_CardPulm_Panel').CardPulm_Data.ReanimDrugType_id = null;
																				win.findById('swERPEW_RA_CardPulm_Drug_Dose_1').setValue(null);
																				win.findById('swERPEW_RA_CardPulm_Panel').CardPulm_Data.ReanimatCardPulm_DrugDose = null; //BOB - 12.07.2019
																				for (var i = 0; i < win.findById('swERPEW_RA_DrugSposob_1').items.items.length; i++  )
																					win.findById('swERPEW_RA_DrugSposob_1').items.items[i].setValue(false);
																				win.findById('swERPEW_RA_CardPulm_Panel').CardPulm_Data.ReanimatCardPulm_DrugSposob = null;
																			}
																		}, //BOB - 12.07.2019	
																		
																		'select': function(combo, record, index) {
																			//если выбрана не пустая строка
																			if (record) {
																				win.findById('swERPEW_RA_CardPulm_Panel').CardPulm_Data.ReanimDrugType_id = record.data.ReanimDrugType_id;		
																			}
																			else  //выбрана пустая строка																	
																				win.findById('swERPEW_RA_CardPulm_Panel').CardPulm_Data.ReanimDrugType_id = null;
																		},
																		'expand': function	(combo)	{
																			var filterReanimatDrug = win.findById('swERPEW_ReanimatAction_Panel').RA_Drug['card_pulm'];
																			combo.getStore().clearFilter();
																			if (filterReanimatDrug){
																				combo.getStore().filterBy(function (rec) {
																					return rec.get('ReanimDrugType_id').inlist(filterReanimatDrug);
																				});
																			}
																		}
																	}
																}																																																																												
															]
														},
														//дозировка
														{							
															layout:'form',
															style:'margin-top: 4px;',
															labelWidth:80,
															border:false,
															items:[	
																new Ext.form.NumberField({
																	value: 0,
																	id: 'swERPEW_RA_CardPulm_Drug_Dose_1',
																	fieldLabel:'дозировка',
																	labelSeparator: '',
																	enableKeyEvents: true,
																	width: 60,
		//															style:'margin-top: 6px; '//,
																	listeners:{
																		'keyup':function (obj, e) { 
																			win.findById('swERPEW_RA_CardPulm_Panel').CardPulm_Data.ReanimatCardPulm_DrugDose = (obj.getValue() == '' ? null : obj.getValue()); //BOB - 12.07.2019
																		}
																	}
																})
															]
														},
														//Лейбл Способ 
														{									
															layout:'form',
															style:' margin-left: 5px; margin-top: 6px; font-size: 12px ',
															items:[
																new Ext.form.Label({
																	text: 'мл способ'
																})					
															]
														},
														{									
															layout:'form',
															labelWidth:1,
															style: 'margin-top: 4px;  margin-left: 4px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
															items:[
																new Ext.form.RadioGroup({
																	id:'swERPEW_RA_DrugSposob_1',
																	labelSeparator: '',
																	vertical: true,
																	columns: 2,
																	items: [
																		{boxLabel: 'струйно',  name: 'DrugSposob_1',  inputValue: 1, width: 100 },
																		{boxLabel: 'дробно', name: 'DrugSposob_1', inputValue: 2, width: 100}
																	],
																	listeners: {
																		'change': function(field, checked) {
																			if((checked) && (win.findById('swERPEW_RA_CardPulm_Panel').RadioEventExec))
																				win.findById('swERPEW_RA_CardPulm_Panel').CardPulm_Data.ReanimatCardPulm_DrugSposob = checked.inputValue;
																		}
																	}
																})	
															]
														}
													]
												},
												//Внутривенно введено 2
												{									
													layout:'column',
													region: 'center',
													items:[
														//combo - Медикамент
														{							
															layout:'form',
															style:'margin-top: 4px;',
															labelWidth:149,
															border:false,
															items:[	
																{
																	id: 'swERPEW_RA_CardPulm_Drug_2',
																	hiddenName: 'RA_CardPulm_Drug_2',									
																	xtype: 'swextemporalcomptypecombo',
																	fieldLabel: 'Внутривенно введено 2',
																	labelSeparator: '',
																	allowBlank: true,
																	comboSubject: 'ReanimDrugType',
																	width: 240,
																	lastQuery: '',
																	listeners: {
																		'change': function(combo, newValue, oldValue) { //BOB - 12.07.2019	
																			//если выбрана не пустая строка
																			if (newValue == '') {
																				win.findById('swERPEW_RA_CardPulm_Panel').CardPulm_Data.ReanimDrugType_did = null;
																				win.findById('swERPEW_RA_CardPulm_Drug_Dose_2').setValue(null);
																				win.findById('swERPEW_RA_CardPulm_Panel').CardPulm_Data.ReanimatCardPulm_dDrugDose = null; //BOB - 12.07.2019
																				for (var i = 0; i < win.findById('swERPEW_RA_DrugSposob_2').items.items.length; i++  )
																					win.findById('swERPEW_RA_DrugSposob_2').items.items[i].setValue(false);
																				win.findById('swERPEW_RA_CardPulm_Panel').CardPulm_Data.ReanimatCardPulm_dDrugSposob = null;
																			}
																		}, //BOB - 12.07.2019	
																		'select': function(combo, record, index) {
																			//если выбрана не пустая строка
																			if (record) 
																				win.findById('swERPEW_RA_CardPulm_Panel').CardPulm_Data.ReanimDrugType_did = record.data.ReanimDrugType_id;																				
																			else  //выбрана пустая строка																	
																				win.findById('swERPEW_RA_CardPulm_Panel').CardPulm_Data.ReanimDrugType_did = null;
																		},
																		'expand': function	(combo)	{
																			var filterReanimatDrug = win.findById('swERPEW_ReanimatAction_Panel').RA_Drug['card_pulm'];
																			combo.getStore().clearFilter();
																			if (filterReanimatDrug){
																				combo.getStore().filterBy(function (rec) {
																					return rec.get('ReanimDrugType_id').inlist(filterReanimatDrug);
																				});
																			}
																		}
																	}
																}																																																																												
															]
														},
														//дозировка
														{							
															layout:'form',
															style:'margin-top: 4px;',
															labelWidth:80,
															border:false,
															items:[	
																new Ext.form.NumberField({
																	value: 0,
																	id: 'swERPEW_RA_CardPulm_Drug_Dose_2',
																	fieldLabel:'дозировка',
																	labelSeparator: '',
																	enableKeyEvents: true,
																	width: 60,
		//															style:'margin-top: 6px; '//,
																	listeners:{
																		'keyup':function (obj, e) {
																			win.findById('swERPEW_RA_CardPulm_Panel').CardPulm_Data.ReanimatCardPulm_dDrugDose = (obj.getValue() == '' ? null : obj.getValue()); //BOB - 12.07.2019
																		}
																	}
																})
															]
														},
														//Лейбл Способ 
														{									
															layout:'form',
															style:' margin-left: 5px; margin-top: 6px; font-size: 12px ',
															items:[
																new Ext.form.Label({
																	text: 'мл способ'
																})					
															]
														},
														{									
															layout:'form',
															labelWidth:1,
															style: 'margin-top: 4px;  margin-left: 4px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
															items:[
																new Ext.form.RadioGroup({
																	id:'swERPEW_RA_DrugSposob_2',
																	labelSeparator: '',
																	vertical: true,
																	columns: 2,
																	items: [
																		{boxLabel: 'струйно',  name: 'DrugSposob_2',  inputValue: 1, width: 100 },
																		{boxLabel: 'дробно', name: 'DrugSposob_2', inputValue: 2, width: 100}
																	],
																	listeners: {
																		'change': function(field, checked) {
																			if((checked) && (win.findById('swERPEW_RA_CardPulm_Panel').RadioEventExec))
																				win.findById('swERPEW_RA_CardPulm_Panel').CardPulm_Data.ReanimatCardPulm_dDrugSposob = checked.inputValue;
																		}
																	}
																})	
															]
														}
													]
												},
												//Другое 
												{									
													layout:'column',
													region: 'center',
													items:[
														{xtype:'label',text: 'Другое',style: 'font-size: 10pt;  margin-left: 10pt; margin-top: 2pt;  margin-right: 5pt'},
														new Ext.form.TextArea({
															fieldLabel: '',
															labelSeparator: '',
															id: 'swERPEW_RA_DrugTxt',
															name: 'RA_DrugTxt',
															height: 54,
															width:1200,
															//anchor: '99%',
															listeners:{
																'change':function (field, newValue, oldValue) {
																	win.findById('swERPEW_RA_CardPulm_Panel').CardPulm_Data.ReanimatCardPulm_DrugTxt = newValue;
																}
															}
														})												
													]
												},
												//Реанимационные мероприятия - эффективность
												{
													layout:'column',
													border:false,
													items:[	
														//Лейбл Аускультативная картина
														{									
															layout:'form',
															style:' margin-left: 14px; margin-top: 8px; font-size: 12px ',
															items:[
																new Ext.form.Label({
																	text: 'Реанимационные мероприятия'
																})					
															]
														},
														{									
															layout:'form',
															labelWidth:1,
															style: 'margin-top: 4px;  margin-left: 4px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
															items:[
																new Ext.form.RadioGroup({
																	id:'swERPEW_RA_IsEffective',
																	labelSeparator: '',
																	vertical: true,
																	columns: 2,
																	items: [
																		{boxLabel: 'не эффективны', name: 'IsEffective', inputValue: 1, width: 120},
																		{boxLabel: 'эффективны',  name: 'IsEffective',  inputValue: 2, width: 100 }
																	],
																	listeners: {
																		'change': function(field, checked) {
																			if((checked) && (win.findById('swERPEW_RA_CardPulm_Panel').RadioEventExec))
																				win.findById('swERPEW_RA_CardPulm_Panel').CardPulm_Data.ReanimatCardPulm_IsEffective = checked.inputValue;
																		}
																	}
																})	
															]
														}
													]
												},
												//Время проведения реанимационных мероприятий
												{
													layout:'column',
													border:false,
													items:[	
														//Лейбл Аускультативная картина
														{									
															layout:'form',
															style:' margin-left: 14px; margin-top: 8px; font-size: 12px ',
															items:[
																new Ext.form.Label({
																	text: 'Время проведения реанимационных мероприятий'
																})					
															]
														},
														{							
															layout:'form',
															style:'margin-top: 4px;',
															labelWidth:10,
															border:false,
															items:[	
																{
																	allowBlank: false,
																	fieldLabel: '',
																	labelSeparator: '',
																	name: 'swERPEW_RA_CardPulm_Time',
																	id: 'swERPEW_RA_CardPulm_Time',
																	style:'margin-top: 2px; margin-left: 4px;',
																	xtype: 'textfield',
																	plugins:[ new Ext.ux.InputTextMask('99:99', true) ],
																	width:45,
																	value: '',
																	listeners:{
																		'change':function (field, newValue, oldValue) {
																			newValue = newValue.replace(/_/g,'0');
																			var a_newValue = newValue.split(':');
																			var i_newValue = parseInt(a_newValue[0], 10) * 60 + parseInt(a_newValue[1], 10);
																			win.findById('swERPEW_RA_CardPulm_Panel').CardPulm_Data.ReanimatCardPulm_Time = i_newValue;
																			field.setValue(newValue);
																		}.createDelegate(this)
																	}
																}
															]
														}
													]
												},
												//Дата Время констатации биологической смерти
												{									
													layout:'column',
													region: 'center',
													items:[

														//Дата констатации биологической смерти
														{									
															layout:'form',
															width: 400,
															labelWidth: 259,
															items:[										
																{
																	allowBlank: true,
																	fieldLabel:'Констатация биологической смерти: дата',
																	format:'d.m.Y',
																	id: 'swERPEW_RA_BiologDeath_Date',
																	listeners:{
																		'change':function (field, newValue, oldValue) {															
																			win.findById('swERPEW_RA_CardPulm_Panel').CardPulm_Data.ReanimatCardPulm_BiologDeathDate = newValue;
																		}.createDelegate(this)
																	},
																	name:'RA_BiologDeath_Date',
																	plugins:[ new Ext.ux.InputTextMask('99.99.9999', false) ],
																	selectOnFocus:true,
																			//tabIndex:this.tabIndex + 1,
																	width:100,
																	xtype:'swdatefield'
																}										
															]
														},	
														//Время фиксации клинической смерти
														{									
															layout:'form',
															width: 120,
															labelWidth: 50,
															items:[	

																{
																	fieldLabel:'время',
																	allowBlank: true,
																	listeners:{
																		'change':function (field, newValue, oldValue) {
																			win.findById('swERPEW_RA_CardPulm_Panel').CardPulm_Data.ReanimatCardPulm_BiologDeathTime = newValue;
																		}.createDelegate(this)
																	},
																	id: 'swERPEW_RA_BiologDeath_Time',
																	name:'RA_BiologDeath_Time',
																	onTriggerClick:function () {
																		setCurrentDateTime({
																			callback:function () {
																				win.findById('swERPEW_RA_CardPulm_Panel').CardPulm_Data.ReanimatCardPulm_BiologDeathTime = win.findById('swERPEW_RA_BiologDeath_Time').getValue();
																				this.findById('swERPEW_RA_BiologDeath_Date').fireEvent('change', this.findById('swERPEW_RA_BiologDeath_Date'), this.findById('swERPEW_RA_BiologDeath_Date').getValue());
																			}.createDelegate(this),
																			dateField:this.findById('swERPEW_RA_BiologDeath_Date'),
																			loadMask:true,
																			setDate:true,
																			setDateMaxValue:true,
																			addMaxDateDays: this.addMaxDateDays,
																			setDateMinValue:false,
																			setTime:true,
																			timeField:this.findById('swERPEW_RA_BiologDeath_Time'),
																			windowId:this.id
																		});
																	}.createDelegate(this),
																	plugins:[ new Ext.ux.InputTextMask('99:99', true) ],
																	//tabIndex:this.tabIndex + 4,
																	validateOnBlur:false,
																	width:60,
																	xtype:'swtimefield'
																}										

															]
														}
													]
												},
												//Врач - вариант пользователя
												{							
													layout:'form',
													style:'margin-top: 4px;',
													labelWidth:41,
													border:false,
													items:[	
														{
															allowBlank: true,
															fieldLabel: 'Врач',
															labelSeparator: '',
															name: 'swERPEW_RA_CardPulm_DoctorTxt',
															id: 'swERPEW_RA_CardPulm_DoctorTxt',
															width: 1200,
															style:'margin-top: 2px; margin-left: 4px;',
														//	tabIndex: TABINDEX_MS,
															value:'',
															xtype: 'textfield',
															listeners:{
																'change':function (field, newValue, oldValue) {
																	win.findById('swERPEW_RA_CardPulm_Panel').CardPulm_Data.ReanimatCardPulm_DoctorTxt = newValue;
																}.createDelegate(this)
															}
														}
													]
												}
											]
										}
										//BOB - 22.02.2019
									]
								}								
							]
						}
					]
				}),

//BOB - 22.04.2019
//Панель НАЗНАЧЕНИЯ 
				new sw.Promed.Panel({
					title:'4. Назначения',
					id:'swERPEW_ReanimatPrescr_Panel',
					autoHeight:true,
					border:true,
					collapsible:true,
					collapsed:true,
					isLoaded:false,					
					layout:'form',
					style:'margin-bottom: 0.5em; ',
					autoScroll:true,
					bodyStyle:'padding-top: 0.5em; border-top: 1px none #99bbe8; border-left: 1px solid #99bbe8; border-bottom: 1px solid #ffffff;border-right: 1px solid #ffffff;',


					listeners:{
						'expand':function (panel) {
							//загрузка таблицы при первом открытии панели
							if (panel.isLoaded === false) {
								panel.isLoaded = true;

								//var Datas =  [];

								//загрузка грида назначений
								panel.findById('swERPEW_ReanimatPrescr_Grid').getStore().load({
									params:{
										EvnSection_id: this.EvnReanimatPeriod_pid,
										EvnReanimatPeriod_id: this.EvnReanimatPeriod_id
									}
								});	

							}
							panel.doLayout();

						}.createDelegate(this)
					},

					items:[
						//Панель - Таблица Назначений
						{
							height:211,
							layout:'border',
							border:true,
							style: 'border-top: 1px solid #99bbe8; border-left: 1px solid #99bbe8; border-bottom: 1px solid #ffffff;border-right: 1px solid #ffffff; padding: 0px; ',
							items:[
								//Таблица Назначений
								new Ext.grid.EditorGridPanel({
									id: 'swERPEW_ReanimatPrescr_Grid',
									frame: false,
									border: false,
									loadMask: true,
									region: 'center',
									stripeRows: true,
									height:200,
									columns: [
										{dataIndex: 'EvnPrescr_setDate', header: 'Дата', hidden: false, renderer: Ext.util.Format.dateRenderer('d.m.Y'), resizable: false, sortable: false, width: 80},
										{dataIndex: 'PrescriptionType_Name', header: 'Тип назначения', hidden: false, resizable: false, sortable: false, width: 180},
										{dataIndex: 'UslugaComplex_Code', header: 'Код услуги', hidden: false, resizable: true, sortable: false, width: 100},
										{dataIndex: 'UslugaComplex_Name', header: 'Наименование услуги', hidden: false, resizable: true, sortable: false, width: 400 },// id: 'UslugaComplex_Name',
										{dataIndex: 'RecTo', header: 'Служба', hidden: false, resizable: true, sortable: false, width: 400, id: 'RecTo'},
										{dataIndex: 'RecDate', header: 'Постановка', hidden: false, resizable: true, sortable: false, width: 150 },
										{dataIndex: 'EvnPrescr_execDT', header: 'Выполнение', hidden: false, resizable: true, sortable: false, width: 130 },
										{dataIndex: 'EvnDirection_Num', header: 'Направление', hidden: false, resizable: true, sortable: false, width: 100 },
										{dataIndex: 'EvnPrescr_StatusTxt', header: 'Состояние', hidden: false, resizable: true, sortable: false, width: 180,
											renderer: function (v, p, r) {
												var ret = r.get('EvnPrescr_StatusTxt');
												if (r.get('EvnPrescr_StatusTxt') == 'Cito!')
													ret = "<span style='color:red'>" + r.get('EvnPrescr_StatusTxt') + "</span>";
												else if (r.get('EvnPrescr_StatusTxt') == 'Выполнено')
													ret = "<span style='color:green'>" + r.get('EvnPrescr_StatusTxt') + "</span>";
												else if (r.get('EvnPrescr_StatusTxt').indexOf('Отменено') > -1)
													ret = "<span style='color:blue'>" + r.get('EvnPrescr_StatusTxt') + "</span>";
												return ret;
											}
										}
										
										
									],
									autoExpandColumn: 'RecTo',
									autoExpandMin: 400,
									listeners:{
										'rowdblclick': function(grid, rowIndex, e){
											console.log('BOB_swERPEW_ReanimatPrescr_Grid_dblclick_grid=', grid); //BOB - 22.04.2019
											console.log('BOB_swERPEW_ReanimatPrescr_Grid_dblclick_rowIndex=', rowIndex); //BOB - 22.04.2019
											var EvnRPGridRowData = grid.store.getAt(rowIndex).data;  //выбранная строка в гриде событий реанимационных мероприятий
											this.ReanimatPrescr_Edit(EvnRPGridRowData['PrescriptionType_id'],EvnRPGridRowData['EvnPrescr_id']);

										}.createDelegate(this)
									},
									sm:new Ext.grid.RowSelectionModel({
											listeners:{
												'rowselect':function (sm, rowIndex, record) {
													sm.grid.topToolbar.items.items[0].setDisabled(win.action == 'view');
													sm.grid.topToolbar.items.items[1].setDisabled(win.action == 'view');
													Ext.getCmp('swERPEW_Prescr_del').setDisabled(record.data.EvnPrescr_IsExec == 2);

												}.createDelegate(this)
											}
										}),
									store:new Ext.data.Store({
										autoLoad:false,
										listeners:{
											'load':function (store, records, index) {
												
												//установка выбранной записи грида
												if (store.getCount() == 0) {
													LoadEmptyRow(this.findById('swERPEW_ReanimatPrescr_Grid'));
												} else {
													this.findById('swERPEW_ReanimatPrescr_Grid').getSelectionModel().selectRow(this.PrescrGridLoadRawNum); 	//установка выбранности на первой строке грда 	
													this.PrescrGridLoadRawNum = 0;
												}
											}.createDelegate(this)
										},
										reader:new Ext.data.JsonReader({
											id:'EvnPrescr_id'
										}, 
										[
											{mapping:'EvnPrescr_id', name:'EvnPrescr_id', type:'int'},
											{mapping:'EvnPrescr_setDate', name:'EvnPrescr_setDate', type:'date',dateFormat:'d.m.Y' },
											{mapping:'PrescriptionType_id', name:'PrescriptionType_id',type:'int'},
											{mapping:'PrescriptionType_Name', name:'PrescriptionType_Name', type:'string'}, 
											{mapping:'EvnPrescr_IsExec', name:'EvnPrescr_IsExec', type:'int'},
											{mapping:'EvnPrescr_IsCito', name:'EvnPrescr_IsCito', type:'int'},
											{mapping:'EvnPrescr_StatusTxt', name:'EvnPrescr_StatusTxt', type:'string'},
											{mapping:'UslugaComplex_id', name:'UslugaComplex_id', type:'int' },											
											{mapping:'UslugaComplex_Code', name:'UslugaComplex_Code', type:'string'},
											{mapping:'UslugaComplex_Name', name:'UslugaComplex_Name', type:'string'},
											{mapping:'EvnDirection_id', name:'EvnDirection_id', type:'int'},
											{mapping:'EvnDirection_Num', name:'EvnDirection_Num', type:'string'},
											{mapping:'RecTo', name:'RecTo', type:'string'},
											{mapping:'RecDate', name:'RecDate', type:'string'},
											{mapping:'timetable', name:'timetable', type:'string'},
											{mapping:'EvnPrescr_execDT', name:'EvnPrescr_execDT', type:'string'},
											{mapping:'EvnQueue_id', name:'EvnQueue_id', type:'int'},
											{mapping:'MedService_id', name:'MedService_id', type:'int'},
											{mapping:'LpuSection_id', name:'LpuSection_id', type:'int'},
											{mapping:'LpuUnit_id', name:'LpuUnit_id', type:'int'},
											{mapping:'Lpu_id', name:'Lpu_id', type:'int'},
											{mapping:'TableUsluga_id', name:'TableUsluga_id', type:'int'}
										]),
										url:'/?c=EvnReanimatPeriod&m=loudEvnPrescrGrid'
									}),
									tbar:new sw.Promed.Toolbar({
										buttons:[
											{
												iconCls:'add16',
												text:'Добавить назначение',
												menu: new Ext.menu.Menu({
													items: [
														{														
															id: 'swERPEW_PrescrLab_Add',
															text: 'Лабораторная диагностика',
															iconCls: 'parka16',
															handler: function () { this.ReanimatPrescr_Add(11);  }.createDelegate(this)
														},
														{														
															id: 'swERPEW_PrescrFunc_Add',
															text: 'Инструментальная диагностика',
															iconCls: 'dicom',
															handler: function () { this.ReanimatPrescr_Add(12);  }.createDelegate(this)
														},
														{														
															id: 'swERPEW_PrescrCons_Add',
															text: 'Консультационная услуга',
															iconCls: 'workplace-mp16',
															handler: function () { this.ReanimatPrescr_Add(13);  }.createDelegate(this)
														}
														
													]
												})
											},
											{
												//id: 'swERPEW_ReanimatPrescrBut',
												iconCls:'parka16',
												text:'Назначения',
												menu: new Ext.menu.Menu({
													items: [
														{														
															id: 'swERPEW_Prescr_Edit',
															text: 'Редактировать назначение',
															iconCls: 'edit16',
															handler: function () {
																var EvnRPGridRowData = this.findById('swERPEW_ReanimatPrescr_Grid').getSelectionModel().getSelected().data;  //выбранная строка в гриде событий реанимационных мероприятий																
																this.ReanimatPrescr_Edit(EvnRPGridRowData['PrescriptionType_id'],EvnRPGridRowData['EvnPrescr_id']);
															}.createDelegate(this)
														},
														{														
															id: 'swERPEW_Prescr_del',
															text: 'Отменить назначение',
															iconCls: 'delete16',
															handler: function () {
																var EvnRPGridRowData = this.findById('swERPEW_ReanimatPrescr_Grid').getSelectionModel().getSelected().data;  //выбранная строка в гриде событий реанимационных мероприятий																
																this.ReanimatPrescr_Cancel(EvnRPGridRowData['PrescriptionType_id'],EvnRPGridRowData['EvnPrescr_id']);
															}.createDelegate(this)
														}

													]
												})
											},
											{
												iconCls:'direction-new16',
												text:'Направления',
												menu: new Ext.menu.Menu({
													items: [
														{														
															id: 'swERPEW_Prescr_Direct_View',
															text: 'Просмотр направления',
															iconCls: 'log16',
															handler: function () {
																
																var EvnRPGridRowData = this.findById('swERPEW_ReanimatPrescr_Grid').getSelectionModel().getSelected().data;  //выбранная строка в гриде событий реанимационных мероприятий																
																this.ReanimatPrescrDirection_View(EvnRPGridRowData);
															}.createDelegate(this)
														},
														{														
															id: 'swERPEW_Prescr_Direct_Print',
															text: 'Печать направления',
															iconCls: 'print16',
															handler: function () {
																var EvnRPGridRowData = this.findById('swERPEW_ReanimatPrescr_Grid').getSelectionModel().getSelected().data;  //выбранная строка в гриде событий реанимационных мероприятий																
																sw.Promed.Direction.print({
																	EvnDirection_id: EvnRPGridRowData['EvnDirection_id']
																});
															}.createDelegate(this)
														}//,
//														{
//															id: 'swERPEW_Prescr_Direct_Del',
//															text: 'Отменить направление',
//															iconCls: 'delete16',
//															handler: function () {
//																var EvnRPGridRowData = this.findById('swERPEW_ReanimatPrescr_Grid').getSelectionModel().getSelected().data;  //выбранная строка в гриде событий реанимационных мероприятий
//																this.ReanimatPrescr_Cancel(EvnRPGridRowData['PrescriptionType_id'],EvnRPGridRowData['EvnPrescr_id']);
//															}.createDelegate(this)
//														}
													]
												})
											},
											//кнопка Просмотр результатов
											{
												id: 'swERPEW_PrescrResult_View',
												handler:function () {
													var EvnRPGridRowData = this.findById('swERPEW_ReanimatPrescr_Grid').getSelectionModel().getSelected().data;  //выбранная строка в гриде событий реанимационных мероприятий																
													this.ReanimatDirectionResult_View(EvnRPGridRowData);
												}.createDelegate(this),
												iconCls:'log16',
												text:'Просмотр результатов'
											},
											{
												id: 'swERPEW_Prescr_Refresh',
												name: 'swERPEW_Prescr_Refresh',
												handler:function () {
													win.findById('swERPEW_ReanimatPrescr_Grid').getStore().load({
														params:{
															EvnSection_id: win.EvnReanimatPeriod_pid,
															EvnReanimatPeriod_id: win.EvnReanimatPeriod_id
														}
													});	
												}.createDelegate(this),
												iconCls:'refresh16',
												text:'Обновить'
											}
										]
									}),
									//BOB - 25.12.2019
									keys: [{
										key: [
											Ext.EventObject.F3,
										],
										fn: function(inp, e) {
											e.stopEvent();
											e.returnValue = false;
											var grid = this.findById('swERPEW_ReanimatPrescr_Grid');

											switch ( e.getKey() ) {
												case Ext.EventObject.F3:
													if ( e.altKey ) {
														var params = new Object();
														params['key_id'] = grid.getSelectionModel().getSelected().data.EvnPrescr_id;
														params['key_field'] = 'EvnPrescr_id';
														getWnd('swAuditWindow').show(params);
													}
													break;
											}
										},
										scope: this,
										stopEvent: true
									}]
								})						
							]
						}

					]
				}),

//Панель НАПРАЛЕНИЯ 
				new sw.Promed.Panel({
					title:'5. Направления на удалённую консультацию',
					id:'swERPEW_ReanimatDirect_Panel',
					autoHeight:true,
					border:true,
					collapsible:true,
					collapsed:true,
					isLoaded:false,					
					layout:'form',
					style:'margin-bottom: 0.5em; ',
					autoScroll:true,
					bodyStyle:'padding-top: 0.5em; border-top: 1px none #99bbe8; border-left: 1px solid #99bbe8; border-bottom: 1px solid #ffffff;border-right: 1px solid #ffffff;',


					listeners:{
						'expand':function (panel) {
							//загрузка таблицы при первом открытии панели
							if (panel.isLoaded === false) {
								panel.isLoaded = true;

								//var Datas =  [];

								//загрузка грида направлений
								panel.findById('swERPEW_ReanimatDirect_Grid').getStore().load({
									params:{
										EvnSection_id: this.EvnReanimatPeriod_pid,
										EvnReanimatPeriod_id: this.EvnReanimatPeriod_id,
										Lpu_id: this.Lpu_id
									}
								});	

							}
							panel.doLayout();

						}.createDelegate(this)
					},

					items:[
						//Панель - Таблица Направлений
						{
							height:211,
							layout:'border',
							border:true,
							style: 'border-top: 1px solid #99bbe8; border-left: 1px solid #99bbe8; border-bottom: 1px solid #ffffff;border-right: 1px solid #ffffff; padding: 0px; ',
							items:[
								//Таблица Направлений
								new Ext.grid.EditorGridPanel({
									id: 'swERPEW_ReanimatDirect_Grid',
									frame: false,
									border: false,
									loadMask: true,
									region: 'center',
									stripeRows: true,
									height:200,
									columns: [
										{dataIndex: 'EvnDirection_setDate', header: 'Дата направления', hidden: false, resizable: false, sortable: false, width: 130, renderer: Ext.util.Format.dateRenderer('d.m.Y')},
										{dataIndex: 'EvnDirection_Num', header: 'Направление', hidden: false, resizable: true, sortable: false, width: 100 },
										{dataIndex: 'RecWhat', header: 'Тип направления', hidden: false, resizable: true, sortable: false, width: 400, renderer: function (v, p, r) {
												var ret = r.get('RecWhat');
												if (r.get('EvnXmlDir_id'))
													ret = ret + " &nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp <span style='color:blue'>бланк заполнен</span>";

												return ret;
											}
										},
										{dataIndex: 'RecTo', header: 'Служба', hidden: false, resizable: true, sortable: false, width: 400, id: 'RecTo'},
										{dataIndex: 'RecDate', header: 'Постановка', hidden: false, resizable: true, sortable: false, width: 150 },
										{dataIndex: 'EvnStatus_Name', header: 'Статус', hidden: false, resizable: false, sortable: false, width: 180,renderer: function (v, p, r) {
												var ret = r.get('EvnStatus_Name');
												if (r.get('EvnStatus_Name') == 'Отменено')
													ret = "<span style='color:red'>" + r.get('EvnStatus_Name') + "</span>";
												else if (r.get('EvnStatus_Name') == 'Обслужено')
													ret = "<span style='color:green'>" + r.get('EvnStatus_Name') + "</span>";

												return ret;
											}
										},
										{dataIndex: 'EvnDirection_statusDate', header: 'Дата изменения статуса', hidden: false, resizable: true, sortable: false, width: 130, renderer: Ext.util.Format.dateRenderer('d.m.Y') }
									],
									autoExpandColumn: 'RecTo',
									autoExpandMin: 400,
									listeners:{
										'rowdblclick': function(grid, rowIndex, e){
											var EvnRPGridRowData = grid.store.getAt(rowIndex).data;  //выбранная строка в гриде событий реанимационных мероприятий
											this.ReanimatDirection_View(EvnRPGridRowData);
										}.createDelegate(this)
									},
									sm:new Ext.grid.RowSelectionModel({
											listeners:{
												'rowselect':function (sm, rowIndex, record) {													
													sm.grid.topToolbar.items.items[0].setDisabled(win.action == 'view');
													sm.grid.topToolbar.items.items[3].setDisabled(!record.data.allowCancel || win.action == 'view');
													sm.grid.topToolbar.items.items[4].setDisabled(win.action == 'view');
													Ext.getCmp('swERPEW_DirectBlank').setText( (!record.data.EvnXmlDir_id) ? 'Заполнить бланк' : 'Просмотр/редактирование бланка');
													Ext.getCmp('swERPEW_DirectBlank').setIconClass( (!record.data.EvnXmlDir_id) ? 'add16' : 'edit16');

													//console.log('BOB_record=',record);
													//загрузка грида дополнительных привязанных документов
													if((record.data['EvnDirection_id'] != null) && (record.data['EvnDirection_id'] != "")){
														win.findById('swERPEW_ReanimatDirectLinkedDocs_Grid').getStore().load({
															params:{
																EvnDirection_id: record.data['EvnDirection_id']
															}
														});
													} else
														win.findById('swERPEW_ReanimatDirectLinkedDocs_Grid').store.removeAll()
												}.createDelegate(this)
											}
										}),
									store:new Ext.data.Store({
										autoLoad:false,
										listeners:{
											'load':function (store, records, index) {
												
												//установка выбранной записи грида
												if (store.getCount() == 0) {
													LoadEmptyRow(this.findById('swERPEW_ReanimatDirect_Grid'));
												} else {
													this.findById('swERPEW_ReanimatDirect_Grid').getSelectionModel().selectRow(this.DirectGridLoadRawNum); 	//установка выбранности на первой строке грда 	
													this.DirectGridLoadRawNum = 0;
												}
											}.createDelegate(this)
										},
										reader:new Ext.data.JsonReader({
											id:'EvnDirection_id'
										}, 
										[
											{mapping:'EvnDirection_id', name:'EvnDirection_id', type:'int'},
											{mapping:'EvnDirection_setDate', name:'EvnDirection_setDate', type:'date',dateFormat:'d.m.Y' },
											{mapping:'EvnDirection_Num', name:'EvnDirection_Num', type:'string'},
											{mapping:'RecTo', name:'RecTo', type:'string'},
											{mapping:'RecDate', name:'RecDate', type:'string'},
											{mapping:'EvnStatus_id', name:'EvnStatus_id', type:'int'},
											{mapping:'EvnStatus_Name', name:'EvnStatus_Name', type:'string'},
											{mapping:'EvnDirection_statusDate', name:'EvnDirection_statusDate', type:'date',dateFormat:'d.m.Y' },
											{mapping:'allowCancel', name:'allowCancel', type:'int' },											
											{mapping:'DirType_Code', name:'DirType_Code', type:'int'},
											{mapping:'RecWhat', name:'RecWhat', type:'string'},
											{mapping:'timetable', name:'timetable', type:'string'},
											{mapping:'timetable_id', name:'timetable_id', type:'int'},
											{mapping:'EvnXmlDir_id', name:'EvnXmlDir_id', type:'int'},
											{mapping:'EvnXmlDirType_id', name:'EvnXmlDirType_id', type:'int'}
										]),
										url:'/?c=EvnReanimatPeriod&m=loudEvnDirectionGrid'
									}),
									tbar:new sw.Promed.Toolbar({
										buttons:[
											//кнопка Добавление Направления
											{
												id: 'swERPEW_Direct_Add',
												text: 'Добавление направления',
												iconCls: 'add16',
												handler: function () {
													this.ReanimatDirect_Add();
												}.createDelegate(this)
											},
											//кнопка Просмотр Направления
											{
												id: 'swERPEW_Direct_View',
												text: 'Просмотр направления',
												iconCls: 'log16',
												handler: function () {

													var EvnRPGridRowData = this.findById('swERPEW_ReanimatDirect_Grid').getSelectionModel().getSelected().data;  //выбранная строка в гриде событий реанимационных мероприятий																
													this.ReanimatDirection_View(EvnRPGridRowData);
												}.createDelegate(this)
											},
											{														
												id: 'swERPEW_Direct_Print',
												text: 'Печать направления',
												iconCls: 'print16',
												handler: function () {
													var EvnRPGridRowData = this.findById('swERPEW_ReanimatDirect_Grid').getSelectionModel().getSelected().data;  //выбранная строка в гриде событий реанимационных мероприятий																
													sw.Promed.Direction.print({
														EvnDirection_id: EvnRPGridRowData['EvnDirection_id']
													});
												}.createDelegate(this)
											},
											{
												id: 'swERPEW_Direct_Del',
												text: 'Отменить направление',
												iconCls: 'delete16',
												handler: function () {
													var EvnRPGridRowData = this.findById('swERPEW_ReanimatDirect_Grid').getSelectionModel().getSelected().data;  //выбранная строка в гриде событий реанимационных мероприятий
													this.ReanimatDirect_Cancel(EvnRPGridRowData);
												}.createDelegate(this)
											},
											{
												iconCls:'document16',
												text:'Документы',
												menu: new Ext.menu.Menu({
													items: [
														{
															id: 'swERPEW_DirectBlank',
															text: 'Заполнить бланк',
															iconCls: 'add16',
															handler: function () {
																var EvnRPGridRowData = this.findById('swERPEW_ReanimatDirect_Grid').getSelectionModel().getSelected().data;  //выбранная строка в гриде событий реанимационных мероприятий
																this.ReanimatDirectBlank(EvnRPGridRowData, 'DirectBlank');
															}.createDelegate(this)
														},
														{
															id: 'swERPEW_DirectDoc_Add',
															text: 'Добавить документ',
															iconCls: 'add16',
															handler: function () {
																var EvnRPGridRowData = this.findById('swERPEW_ReanimatDirect_Grid').getSelectionModel().getSelected().data;  //выбранная строка в гриде событий реанимационных мероприятий
																this.ReanimatDirectDoc_Add(EvnRPGridRowData);  }.createDelegate(this)
														}
													]
												})
											},
											{
												id: 'swERPEW_Direct_Refresh',
												name: 'swERPEW_Direct_Refresh',
												handler:function () {
													win.findById('swERPEW_ReanimatDirect_Grid').getStore().load({
														params:{
															EvnSection_id: win.EvnReanimatPeriod_pid,
															EvnReanimatPeriod_id: win.EvnReanimatPeriod_id,
															Lpu_id: win.Lpu_id
														}
													});	
												}.createDelegate(this),
												iconCls:'refresh16',
												text:'Обновить'
											}
										]
									}),
									//BOB - 25.12.2019
									keys: [{
										key: [
											Ext.EventObject.F3,
										],
										fn: function(inp, e) {
											e.stopEvent();
											e.returnValue = false;
											var grid = this.findById('swERPEW_ReanimatDirect_Grid');

											switch ( e.getKey() ) {
												case Ext.EventObject.F3:
													if ( e.altKey ) {
														var params = new Object();
														params['key_id'] = grid.getSelectionModel().getSelected().data.EvnDirection_id;
														params['key_field'] = 'EvnDirection_id';
														getWnd('swAuditWindow').show(params);
													}
													break;
											}
										},
										scope: this,
										stopEvent: true
									}]
								})						
							]
						},
						//Объекты прикреплённые к направлению
						{
							id: 'swERPEW_ReanimatDirectDocs_Panel',
							layout:'form',
							border:true,
							width: 1307,
							style: 'border-top: 1px solid #99bbe8; border-left: 1px solid #99bbe8; border-bottom: 1px solid #ffffff;border-right: 1px solid #ffffff; padding: 2px 0px 0px 0px; margin-top: 10px;',
							items:[

								//Панель - Таблица дополнительных прикреплённых к направлению документов
								{
									width: 655,
									height:161,
									layout:'border',
									border:true,
									style: 'border-top: 1px solid #99bbe8; border-left: 1px solid #99bbe8; border-bottom: 1px solid #ffffff;border-right: 1px solid #ffffff; padding: 0px; ',
									items:[
										//Таблица дополнительных прикреплённых к направлению документов
										new Ext.grid.EditorGridPanel({
											id: 'swERPEW_ReanimatDirectLinkedDocs_Grid',
											frame: false,
											border: false,
											loadMask: true,
											region: 'center',
											stripeRows: true,
											height:150,
											columns: [
												{dataIndex: 'EvnXml_Date', header: 'Дата документа', hidden: false, resizable: false, sortable: false, width: 130, renderer: Ext.util.Format.dateRenderer('d.m.Y')},
												{dataIndex: 'EvnXml_Name', header: 'Наименование', hidden: false, resizable: true, sortable: false, width: 200 },
												{dataIndex: 'pmUser_Name', header: 'Автор', hidden: false, resizable: true, sortable: false, width: 300}
											],
											listeners:{
												'rowdblclick': function(grid, rowIndex, e){ }.createDelegate(this)
											},
											sm:new Ext.grid.RowSelectionModel({
													listeners:{
														'rowselect':function (sm, rowIndex, record) {
															sm.grid.topToolbar.items.items[1].setDisabled(win.action == 'view');
														}.createDelegate(this)
													}
												}),
											store:new Ext.data.Store({
												autoLoad:false,
												listeners:{
													'load':function (store, records, index) {

														//установка выбранной записи грида
														if (store.getCount() == 0) {
															LoadEmptyRow(this.findById('swERPEW_ReanimatDirectLinkedDocs_Grid'));
														} else {
															this.findById('swERPEW_ReanimatDirectLinkedDocs_Grid').getSelectionModel().selectRow(0); 	//установка выбранности на первой строке грда
														}
													}.createDelegate(this)
												},
												reader:new Ext.data.JsonReader({
													id:'EvnXmlDirectionLink_id'
												},
												[
													{mapping:'EvnXmlDirectionLink_id', name:'EvnXmlDirectionLink_id', type:'int'},
													{mapping:'EvnDirection_id', name:'EvnDirection_id', type:'int'},
													{mapping:'EvnXml_id', name:'EvnXml_id', type:'int'},
													{mapping:'Evn_id', name:'Evn_id', type:'int'},   // Evn_id - основного события к которому привязан документ
													{mapping:'EvnXml_Name', name:'EvnXml_Name', type:'string'},
													{mapping:'EvnXml_Date', name:'EvnXml_Date', type:'date',dateFormat:'d.m.Y' },
													{mapping:'pmUser_Name', name:'pmUser_Name', type:'string'},
													{mapping:'EvnClass_id', name:'EvnClass_id', type:'int'},
													{mapping:'XmlType_id', name:'XmlType_id', type:'int'},

												]),
												url:'/?c=EvnReanimatPeriod&m=getDirectionLinkedDocs'
											}),
											tbar:new sw.Promed.Toolbar({
												buttons:[
													{
														id: 'swERPEW_DirectLinkedDocs_view',
														text: 'Просмотр документа',
														iconCls: 'document16',
														handler: function () {
															var EvnRPGridRowData = this.findById('swERPEW_ReanimatDirectLinkedDocs_Grid').getSelectionModel().getSelected().data;  //выбранная строка в гриде событий реанимационных мероприятий
															this.ReanimatDirectBlank(EvnRPGridRowData, 'LinkedDocs');
														}.createDelegate(this)
													},
													{
														id: 'swERPEW_DirectLinkedDocs_Del',
														text: 'Удалить документ',
														iconCls: 'delete16',
														handler: function () {
															var EvnRPGridRowData = this.findById('swERPEW_ReanimatDirectLinkedDocs_Grid').getSelectionModel().getSelected().data;  //выбранная строка в гриде событий реанимационных мероприятий
															this.ReanimatDirectDoc_Del(EvnRPGridRowData);
														}.createDelegate(this)
													},
													{
														id: 'swERPEW_DirectLinkedDocs_Refresh',
														name: 'swERPEW_DirectLinkedDocs_Refresh',
														handler:function () {
															var EvnRPGridRowData = win.findById('swERPEW_ReanimatDirect_Grid').getSelectionModel().getSelected().data;  //выбранная строка в гриде событий шкал
															win.findById('swERPEW_ReanimatDirectLinkedDocs_Grid').getStore().load({
																params:{
																	EvnDirection_id: EvnRPGridRowData['EvnDirection_id']
																}
															});
														}.createDelegate(this),
														iconCls:'refresh16',
														text:'Обновить'
													}
												]
											})
										})
									]
								}
							]
						}
					]
				}),

//Панель КУРСЫ ЛЕКАРСТВЕННОГО ЛЕЧЕНИЯ //BOB - 07.11.2019
				new sw.Promed.Panel({
					title:'6. Лекарственное лечение',
					id:'swERPEW_ReanimatDrugCourse_Panel',
					autoHeight:true,
					border:true,
					collapsible:true,
					collapsed:true,
					isLoaded:false,
					layout:'form',
					style:'margin-bottom: 0.5em; ',
					autoScroll:true,
					bodyStyle:'padding-top: 0.5em; border-top: 1px none #99bbe8; border-left: 1px solid #99bbe8; border-bottom: 1px solid #ffffff;border-right: 1px solid #ffffff;',
					DrugCourseGridStyle: 'x-grid-rowbackgainsboro',

					listeners:{
						'expand':function (panel) {
							//загрузка таблицы при первом открытии панели
							if (panel.isLoaded === false) {
								panel.isLoaded = true;


								if (panel.findById('swERPEW_ReanimatDrugCourse_Grid').view == null) {
									panel.findById('swERPEW_ReanimatDrugCourse_Grid').view = new Ext.grid.GridView({
										getRowClass : function (r, index) {
											if (!Ext.isEmpty(r.get('EvnCourse_Title'))){
												if (win.findById('swERPEW_ReanimatDrugCourse_Panel').DrugCourseGridStyle == '') {
													win.findById('swERPEW_ReanimatDrugCourse_Panel').DrugCourseGridStyle = 'x-grid-rowbackgainsboro';
												} else {
													win.findById('swERPEW_ReanimatDrugCourse_Panel').DrugCourseGridStyle = '';
												}
											}
											return cls = win.findById('swERPEW_ReanimatDrugCourse_Panel').DrugCourseGridStyle;
										}
									});
								}

								//загрузка грида направлений
								panel.findById('swERPEW_ReanimatDrugCourse_Grid').getStore().load({
									params:{
										EvnSection_id: this.EvnReanimatPeriod_pid,
										EvnReanimatPeriod_id: this.EvnReanimatPeriod_id,
										Lpu_id: this.Lpu_id
									}
								});
							}
							panel.doLayout();

						}.createDelegate(this)
					},

					items:[
						//Панель - Таблица КУРСЫ ЛЕКАРСТВЕННОГО ЛЕЧЕНИЯ
						{
							height:220,
							layout:'border',
							border:true,
							style: 'border-top: 1px solid #99bbe8; border-left: 1px solid #99bbe8; border-bottom: 1px solid #ffffff;border-right: 1px solid #ffffff; padding: 0px; ',
							items:[
								//Таблица КУРСЫ ЛЕКАРСТВЕННОГО ЛЕЧЕНИЯ
								new Ext.grid.EditorGridPanel({
									id: 'swERPEW_ReanimatDrugCourse_Grid',
									frame: false,
									border: false,
									loadMask: true,
									region: 'center',
									stripeRows: false,
									height:209,
									columns: [
										{dataIndex: 'EvnCourse_Title', header: 'Курс', hidden: false, resizable: true, sortable: false, width: 50 },
										{dataIndex: 'EvnPrescrTreat_IsCito', header: 'Срочность', hidden: false, resizable: true, sortable: false, width: 70, renderer: function (v, p, r) {
											var ret = r.get('EvnPrescrTreat_IsCito');
											if (ret == 'Cito!')
												ret = "<span style='color:red'>" + ret + "</span>";
											return ret;
										}},
										{dataIndex: 'DrugTorg_Name', header: 'Торговое наименование', hidden: false, resizable: true, sortable: false, width: 400, id: 'DrugTorg_Name'},
										{dataIndex: 'DoseOne', header: 'Доза разовая', hidden: false, resizable: true, sortable: false, width: 80},
										{dataIndex: 'DoseDay', header: 'Доза дневная', hidden: false, resizable: true, sortable: false, width: 90},
										{dataIndex: 'DoseCourse', header: 'Доза курсовая', hidden: false, resizable: true, sortable: false, width: 90},
										{dataIndex: 'EvnPrescrTreat_setDate', header: 'Дата начала курса', hidden: false, resizable: true, sortable: false, width: 110, renderer: Ext.util.Format.dateRenderer('d.m.Y')},
										{dataIndex: 'Duration', header: 'Продолжительность', hidden: false, resizable: true, sortable: false, width: 120},
										{dataIndex: 'PrescriptionIntroType_Name', header: 'Метод введения', hidden: false, resizable: true, sortable: false, width: 140},
										{dataIndex: 'PerformanceType_Name', header: 'Исполнение', hidden: false, resizable: true, sortable: false, width: 170}
									],
									autoExpandColumn: 'DrugTorg_Name',
									autoExpandMin: 400,
									listeners:{
										'rowdblclick': function(grid, rowIndex, e){
											this.ReanimatDrugCourse_Add_Edit('edit');
										}.createDelegate(this)
									},
									sm:new Ext.grid.RowSelectionModel({
										listeners:{
											'rowselect':function (sm, rowIndex, record) {
												var view_act = this.action == 'view' ? true : false;  //режим просмотра
												sm.grid.topToolbar.items.items[0].setDisabled(view_act);
												sm.grid.topToolbar.items.items[1].setDisabled(view_act);
												sm.grid.topToolbar.items.items[2].setDisabled(view_act);

												//загрузка грида назначений / лекарственных средств
												if((record.data['EvnCourse_id'] != null) && (record.data['EvnCourse_id'] != "")){
													win.findById('swERPEW_ReanimatPrescrTreatDrug_Grid').getStore().load({
														params:{
															EvnCourse_id: record.data['EvnCourse_id']
														}
													});
												} else
													win.findById('swERPEW_ReanimatPrescrTreatDrug_Grid').store.removeAll();
											}.createDelegate(this)
										}
									}),
									store:new Ext.data.Store({
										autoLoad:false,
										listeners:{
											'load':function (store, records, index) {
												//установка выбранной записи грида
												if (store.getCount() == 0) {
													LoadEmptyRow(this.findById('swERPEW_ReanimatDrugCourse_Grid'));
												} else {
													this.findById('swERPEW_ReanimatDrugCourse_Grid').getSelectionModel().selectRow(this.DrugCourseGridLoadRawNum); 	//установка выбранности на первой строке грда
													this.DrugCourseGridLoadRawNum = 0;
												}
											}.createDelegate(this)
										},
										reader:new Ext.data.JsonReader({
											id:'EvnCourseTreat_id'
										},
										[
											{mapping:'EvnCourseTreat_id', name:'EvnCourseTreat_id', type:'int'},
											{mapping:'EvnCourse_id', name:'EvnCourse_id', type:'int'},
											{mapping:'EvnCourse_Title', name:'EvnCourse_Title', type:'string'},
											{mapping:'EvnCourseTreat_setDate', name:'EvnCourseTreat_setDate', type:'date',dateFormat:'d.m.Y' },
											{mapping:'DrugTorg_Name', name:'DrugTorg_Name', type:'string'},
											{mapping:'EvnPrescrTreat_setDate', name:'EvnPrescrTreat_setDate', type:'date',dateFormat:'d.m.Y' },
											{mapping:'EvnPrescrTreat_IsCito', name:'EvnPrescrTreat_IsCito', type:'string'},
											{mapping:'DoseOne', name:'DoseOne', type:'string'},
											{mapping:'DoseDay', name:'DoseDay', type:'string'},
											{mapping:'DoseCourse', name:'DoseCourse', type:'string'},
											{mapping:'Duration', name:'Duration', type:'string'},
											{mapping:'PrescriptionIntroType_Name', name:'PrescriptionIntroType_Name', type:'string'},
											{mapping:'PerformanceType_Name', name:'PerformanceType_Name', type:'string'}
										]),
										url:'/?c=EvnReanimatPeriod&m=loudEvnDrugCourseGrid'
									}),
									tbar:new sw.Promed.Toolbar({
										buttons:[
											{
												id: 'swERPEW_DrugCourse_Add',
												text: 'Добавление курса',
												iconCls: 'add16',
												handler: function () {
													this.ReanimatDrugCourse_Add_Edit('add');
												}.createDelegate(this)
											},
											{
												id: 'swERPEW_DrugCourse_Edit',
												text: 'Редактировать курс',
												iconCls: 'edit16',
												handler: function () {
													this.ReanimatDrugCourse_Add_Edit('edit');
												}.createDelegate(this)
											},
											{
												id: 'swERPEW_DrugCourse_Del',
												text: 'Отмена курса',
												iconCls: 'delete16',
												handler: function () {
													var EvnRPGridRowData = this.findById('swERPEW_ReanimatDrugCourse_Grid').getSelectionModel().getSelected().data;  //выбранная строка в гриде событий реанимационных мероприятий
													this.ReanimatDrugCourse_Cancel(5,EvnRPGridRowData['EvnCourse_id']);
												}.createDelegate(this)
											},
											{
												id: 'swERPEW_DrugCourse_Refresh',
												name: 'swERPEW_DrugCourse_Refresh',
												handler:function () {
													win.findById('swERPEW_ReanimatDrugCourse_Grid').getStore().load({
														params:{
															EvnSection_id: win.EvnReanimatPeriod_pid,
															EvnReanimatPeriod_id: win.EvnReanimatPeriod_id,
															Lpu_id: win.Lpu_id
														}
													});
												}.createDelegate(this),
												iconCls:'refresh16',
												text:'Обновить'
											}
										]
									}),
									//BOB - 25.12.2019
									keys: [{
										key: [
											Ext.EventObject.F3,
										],
										fn: function(inp, e) {
											e.stopEvent();
											e.returnValue = false;
											var grid = this.findById('swERPEW_ReanimatDrugCourse_Grid');

											switch ( e.getKey() ) {
												case Ext.EventObject.F3:
													if ( e.altKey ) {
														var params = new Object();
														params['key_id'] = grid.getSelectionModel().getSelected().data.EvnCourse_id;
														params['key_field'] = 'EvnCourse_id';
														getWnd('swAuditWindow').show(params);
													}
													break;
											}
										},
										scope: this,
										stopEvent: true
									}]
								})
							]
						},
						//Объекты прикреплённые к курсу лечения
						{
							id: 'swERPEW_ReanimatPrescrTreatDrug_Panel',
							layout:'form',
							border:true,
							height:222,
							width: 1185,
							style: 'border-top: 1px solid #99bbe8; border-left: 1px solid #99bbe8; border-bottom: 1px solid #ffffff;border-right: 1px solid #ffffff; padding: 2px 0px 0px 0px; margin-top: 10px;',
							items:[

								//Панель - Таблица назначений / лекарственных средств
								{
									width: 1182,
									height:220,
									layout:'border',
									border:true,
									style: 'border-top: 1px solid #99bbe8; border-left: 1px solid #99bbe8; border-bottom: 1px solid #ffffff;border-right: 1px solid #ffffff; padding: 0px; ',
									items:[
										//Таблица назначений / лекарственных средств
										new Ext.grid.EditorGridPanel({
											id: 'swERPEW_ReanimatPrescrTreatDrug_Grid',
											frame: false,
											border: false,
											loadMask: true,
											region: 'center',
											stripeRows: true,
											height:209,
											columns: [
												{dataIndex: 'dayNum', header: 'День', hidden: false, resizable: true, sortable: false, width: 50},
												{dataIndex: 'EvnPrescrTreat_setDate', header: 'Дата', hidden: false, resizable: true, sortable: false, width: 110, renderer: Ext.util.Format.dateRenderer('d.m.Y')},
												{dataIndex: 'DrugTorg_Name', header: 'Медикамент', hidden: false, resizable: true, sortable: false, width: 600 },
												{dataIndex: 'DoseDay', header: 'Суточная доза', hidden: false, resizable: true, sortable: false, width: 100},
												{dataIndex: 'DoseOne', header: 'Разовая доза', hidden: false, resizable: true, sortable: false, width: 100},
												{dataIndex: 'CntDay', header: 'Приёмов в день', hidden: false, resizable: true, sortable: false, width: 100},
												{dataIndex: 'ExecDay', header: 'Выполнение', hidden: false, resizable: true, sortable: false, width: 100}
											],
											listeners:{
												'rowdblclick': function(grid, rowIndex, e){ }.createDelegate(this)
											},
											sm:new Ext.grid.RowSelectionModel({
													listeners:{
														'rowselect':function (sm, rowIndex, record) {
															this.ReanimatPrescrTreatDrug_ButtonManag(record);
														 }.createDelegate(this)
													}
												}),
											store:new Ext.data.Store({
												autoLoad:false,
												listeners:{
													'load': function (store, records, index) {
														//установка выбранной записи грида
														if (store.getCount() == 0) {
															LoadEmptyRow(this.findById('swERPEW_ReanimatPrescrTreatDrug_Grid'));
														} else {
															this.findById('swERPEW_ReanimatPrescrTreatDrug_Grid').getSelectionModel().selectRow(this.PrescrTreatDrugGridLoadRawNum);
															this.PrescrTreatDrugGridLoadRawNum = 0;
														}
													}.createDelegate(this)
												},
												reader:new Ext.data.JsonReader({
													id:'EvnPrescrTreatDrug_id'
												},
												[
													{mapping:'EvnPrescrTreatDrug_id', name:'EvnPrescrTreatDrug_id', type:'int'},
													{mapping:'EvnPrescrTreat_id', name:'EvnPrescrTreat_id', type:'int'},
													{mapping:'dayNum', name:'dayNum', type:'int'},
													{mapping:'EvnPrescrTreat_setDate', name:'EvnPrescrTreat_setDate', type:'date',dateFormat:'d.m.Y' },
													{mapping:'DrugTorg_Name', name:'DrugTorg_Name', type:'string'},
													{mapping:'DoseDay', name:'DoseDay', type:'string'},
													{mapping:'DoseOne', name:'DoseOne', type:'string'},
													{mapping:'CntDay', name:'CntDay', type:'string'},
													{mapping:'ExecDay', name:'ExecDay', type:'string'},
													{mapping:'EvnPrescr_IsExec', name:'EvnPrescr_IsExec', type:'int'},
													{mapping:'EvnCourse_id', name:'EvnCourse_id', type:'int'},
													{mapping:'EvnPrescr_IsHasEvn', name:'EvnPrescr_IsHasEvn', type:'int'},
													{mapping:'PrescriptionStatusType_id', name:'PrescriptionStatusType_id', type:'int'}

												]),
												url:'/?c=EvnReanimatPeriod&m=loudEvnPrescrTreatDrugGrid'
											}),
											tbar:new sw.Promed.Toolbar({
												buttons:[
													{
														id: 'swERPEW_PrescrTreatDrug_edit',
														text: 'Редактировать',
														iconCls: 'edit16',
														handler: function () {
															this.ReanimatPrescrTreatDrug_Edit('edit');
														}.createDelegate(this)
													},
													{
														id: 'swERPEW_PrescrTreatDrug_Del',
														text: 'Отменить назначение',
														iconCls: 'delete16',
														handler: function () {
															this.ReanimatPrescrTreatDrug_Cancel();
														}.createDelegate(this)
													},
													{
														id: 'swERPEW_PrescrTreatDrug_Exec',
														text: 'Выполнить',
														iconCls: 'ok16',
														handler: function (item, evn) {
															this.ReanimatPrescrTreatDrug_Exec(evn);
														}.createDelegate(this)
													},
													{
														id: 'swERPEW_PrescrTreatDrug_UnExec',
														text: 'Отменить выполнение',
														iconCls: 'undo16',
														handler: function () {
															this.ReanimatPrescrTreatDrug_UnExec();
														}.createDelegate(this)
													},




													{
														id: 'swERPEW_PrescrTreatDrug_Refresh',
														name: 'swERPEW_PrescrTreatDrug_Refresh',
														handler:function () {
															var EvnRPGridRowData = win.findById('swERPEW_ReanimatDrugCourse_Grid').getSelectionModel().getSelected().data;  //выбранная строка в гриде событий шкал
															win.findById('swERPEW_ReanimatPrescrTreatDrug_Grid').getStore().load({
																params:{
																	EvnCourse_id: EvnRPGridRowData['EvnCourse_id']
																}
															});
														}.createDelegate(this),
														iconCls:'refresh16',
														text:'Обновить'
													}
												]
											})
										})
									]
								}
							]
						}
					]
				}),

				// ПАНЕЛЬ "НАБЛЮДЕНИЯ ЗА ПАЦИЕНТАМИ С COVID-19"
				new sw.Promed.Panel({
					title: '7. Наблюдения за пациентом с пневмонией, подозрением на COVID-19 и COVID-19',
					id: 'swERPEW_RepositoryObserv_Panel',
					autoHeight:true,
					border:true,
					collapsible:true,
					collapsed:true,
					isLoaded:false,
					layout:'form',
					style:'margin-bottom: 0.5em; ',
					autoScroll:true,
					bodyStyle:'padding-top: 0.5em; border-top: 1px none #99bbe8; border-left: 1px solid #99bbe8; border-bottom: 1px solid #ffffff;border-right: 1px solid #ffffff;',

					listeners: {
						'expand': function (panel) {
							//загрузка таблицы при первом открытии панели
							if (panel.isLoaded === false) {
								panel.isLoaded = true;

								//загрузка грида назначений
								panel.findById('swERPEW_RepositoryObserv_Grid').getGrid().getStore().load({
									params: {
										Evn_id: this.EvnReanimatPeriod_id
									}
								});
							}

							panel.doLayout();
						}.createDelegate(this)
					},

					items: [
						new sw.Promed.ViewFrame({
							id: 'swERPEW_RepositoryObserv_Grid',
							style: 'margin-bottom: 0.5em;',
							actions: [
								{name: 'action_add', handler: function() { win.openRepositoryObservEditWindow('add'); }},
								{name: 'action_edit', handler: function() { win.openRepositoryObservEditWindow('edit'); }},
								{name: 'action_view', handler: function() { win.openRepositoryObservEditWindow('view'); }},
								{name: 'action_delete', handler: function() { win.deleteRepositoryObserv(); }},
								{name: 'action_refresh', hidden: true},
								{name: 'action_print', hidden: true}
							],
							autoExpandColumn: 'autoexpand',
							autoExpandMin: 150,
							autoLoadData: false,
							border: true,
							dataUrl: '/?c=RepositoryObserv&m=loadList',
							height: 200,
							paging: false,
							stringfields: [
								{name: 'RepositoryObserv_id', type: 'int', header: 'ID', key: true},
								{name: 'RepositoryObserv_setDT', type: 'datetime', header: 'Дата и время наблюдения', width: 200},
								{name: 'MedPersonal_FIO', type: 'string', header: 'Врач', id: 'autoexpand'}
							],
							toolbar: true
						})
					]
				})
			]
		});

		Ext.apply(this, {
			items: [
				//Панель Персональные данные / диагноз  /  профильное отделение
				this.PersonPanel,
				//Панель всех данных о ходе реанимационного периода
				this.FormPanel
			],
			buttons: [
//				{
//					text: langs('Сохранить'),
//					id: 'swERPEW_ButtonSave',
//					tooltip: langs('Сохранить'),
//					iconCls: 'save16',
//					handler: function()
//					{
//						this.EvnReanimatPeriod_Save();
//					}.createDelegate(this)
//				},
				{
					text: '-'
				},
				HelpButton(this, 1),
				{
					handler: function () {

						if ( Ext.isEmpty(this.findById('swERPEW_BedProfile').getValue())) {
							var ErrMessag = 'Отсутствует профиль коек<br>';
							Ext.MessageBox.alert('Внимание!', ErrMessag);
							return false;
						};
						getWnd('swEvnNeonatalSurveyEditWindow').hide();
						this.callback(this.ReanimatPeriod_isClosed);
					//	this.hide();
					}.createDelegate(this),
					iconCls: 'cancel16',
					id: 'swERPEW_CancelButton',
					text: 'Закрыть'
				}]
		});

		sw.Promed.swEvnReanimatPeriodEditWindow.superclass.initComponent.apply(this, arguments);

	},
	calcPaO2FiO2: function() {
		var base_form = this.findById('swERPEW_Form').getForm();

		var
			FiO2 = base_form.findField('EvnReanimatCondition_OxygenFraction').getValue(),
			PaO2 = base_form.findField('EvnReanimatCondition_OxygenPressure').getValue(),
			PaO2FiO2;

		if (!Ext.isEmpty(FiO2) && !Ext.isEmpty(PaO2)) {
			PaO2FiO2 = (PaO2 / FiO2).toFixed(2);
		}

		base_form.findField('EvnReanimatCondition_PaOFiO').setValue(PaO2FiO2);
		base_form.findField('EvnReanimatCondition_PaOFiO').fireEvent('change', base_form.findField('EvnReanimatCondition_PaOFiO'), PaO2FiO2);
	},
	//отображение окна, заготовка данных и т.д.
	show: function() {
		var win = this;
		var Args = arguments[0];
		//console.log('BOB_Args=',Args);
		this.action = Args.action;
		this.from = Args.from;	
		this.callback = Args.Callback;
		this.setTitle(Args.ERPEW_title); //  //Заголовок
		this.EvnReanimatPeriod_id = Args.EvnReanimatPeriod_id;
		this.FirstConditionLoad = true;

		this.findById('swERPEW_RepositoryObserv_Panel').hide();

		this.findById( 'swERPEW_EvnReanimatConditionPanelsManag').isCollaps = true;
		
		// Получение параметров пациента и РП
		$.ajax({
			mode: "abort",
			type: "post",
			async: false,
			url: '/?c=EvnReanimatPeriod&m=getParamsERPWindow',
			data: { EvnReanimatPeriod_id: this.EvnReanimatPeriod_id },
			success: function(response) {
				var Args2 = Ext.util.JSON.decode(response);
				console.log('BOB_Args2=',Args2); 	
				win.erp_data = Args2.erp_data[0];
				win.pers_data = Args2.pers_data[0];
				//загрузка комбо врачей, подписавших наблюдение (дневник) //BOB - 27.09.2019
				var Datas =  [];
				for (var i in Args2.MS_doctors) { // цикл по значениям параметра
					Datas[i]= [ Args2.MS_doctors[i].MedPersonal_id,
								Args2.MS_doctors[i].EvnReanimatCondition_Doctor ];
				};
				win.findById('swERPEW_RC_Print_Doctor_FIO').getStore().loadData(Datas);
				if (
					(
						!Ext.isEmpty(win.erp_data.Diag_Code)
						&& (
							win.erp_data.Diag_Code.inlist(['U07.1','U07.2'])
							|| (
								win.erp_data.Diag_Code.substr(0, 3) >= 'J12'
								&& win.erp_data.Diag_Code.substr(0, 3) <= 'J19'
							)
						)
					)
					|| (
						getRegionNick() == 'msk'
						&& (win.erp_data.CovidType_id == 2 || win.erp_data.CovidType_id == 3)
					)
				) {
					win.findById('swERPEW_RepositoryObserv_Panel').show();
				}
			}, 
			error: function() {
				alert("При обработке запроса на сервере произошла ошибка!");
			} 
		}); 
		
		
		this.EvnReanimatPeriod_pid = this.erp_data.EvnReanimatPeriod_pid;
		this.EvnReanimatPeriod_rid = this.erp_data.EvnReanimatPeriod_rid;

		this.Lpu_id = this.erp_data.Lpu_id;
		this.LpuSection_id = this.erp_data.LpuSection_id;

		this.Person_id = this.pers_data.Person_id;
		this.PersonEvn_id = this.pers_data.PersonEvn_id;
		this.Server_id = this.pers_data.Server_id;
		this.Diag_id = (this.erp_data.Diag_id) ? this.erp_data.Diag_id : this.erp_data.Diag_id_PS;
		this.MedService_Name = this.erp_data.MedService_Name;
		
		this.MedPersonal_id = null;
		this.MedStaffFact_id = null;
		this.MedPersonal_FIO = null;

		if ( Args.userMedStaffFact ) {
			this.MedPersonal_id = Args.userMedStaffFact.MedPersonal_id;
			this.MedStaffFact_id = Args.userMedStaffFact.MedStaffFact_id;
			this.MedPersonal_FIO = Args.userMedStaffFact.MedPersonal_FIO;
		this.userMedStaffFact = Args.userMedStaffFact;
			//нужна ещё и должность
		}

		sw.Promed.swEvnReanimatPeriodEditWindow.superclass.show.apply(this, arguments);

	
		// Получение справочных данных и загрузка в выпадающие меню
		var ERPEW_NSI = {};
		ERPEW_NSI = win.ERPEW_NSI;
		if (Ext.isEmpty(ERPEW_NSI)){
			$.ajax({
				mode: "abort",
				type: "post",
				async: false,
				url: '/?c=EvnReanimatPeriod&m=ERPEW_NSI',
				success: function(response) {
					//var ERPEW_NSI = Ext.util.JSON.decode(response);
					ERPEW_NSI = Ext.util.JSON.decode(response);
					console.log('BOB_Object_ERPEW_NSI=',ERPEW_NSI);

					win.ERPEW_NSI = ERPEW_NSI;
					//стороны
					win.SideType = ERPEW_NSI.SideType;  

				},
				error: function() {
					alert("При обработке запроса на сервере произошла ошибка!");
				} 
			});
		}
								
//***********загрузка справочников для ШКАЛ***********************************************************************************************************************************
		var Datas =  [];
		var fields = [{name:'ScaleParameterResult_Name', type:'string'},
					 {name:'ScaleParameterResult_id',type:'string'},
					 {name:'ScaleParameterResult_Value',type:'int'},
					 {name:'ScaleParameterType_id',type:'int'}];						 	

		//загрузка справочника glasgow	 
		this.ScalePanel_load('glasgow', ERPEW_NSI.EvnScaleglasgow);
		//загрузка справочника glasgow_ch	 
		this.ScalePanel_load('glasgow_ch', ERPEW_NSI.EvnScaleglasgow_ch);
		//загрузка справочника SOFA	 
		this.ScalePanel_load('sofa', ERPEW_NSI.EvnScalesofa);
		//загрузка справочника Apache
		this.ScalePanel_load('apache', ERPEW_NSI.EvnScaleapache);
		//загрузка справочника WATERLOW	 
		this.ScalePanel_load('waterlow', ERPEW_NSI.EvnScalewaterlow);
		//загрузка справочника RASS	 
		this.ScalePanel_load('rass', ERPEW_NSI.EvnScalerass);
		//загрузка справочника hunt_hess	 
		this.ScalePanel_load('hunt_hess', ERPEW_NSI.EvnScalehunt_hess);
		//загрузка справочника four	 
		this.ScalePanel_load('four', ERPEW_NSI.EvnScalefour);
		//загрузка справочника mrc	 
		this.ScalePanel_load('mrc', ERPEW_NSI.EvnScalemrc);
		//загрузка справочника nihss	 
		this.ScalePanel_load('nihss', ERPEW_NSI.EvnScalenihss);
		
		//загрузка справочника glasgow_neonat	 //BOB - 20.02.2020
		this.ScalePanel_load('glasgow_neonat', ERPEW_NSI.EvnScaleglasgow_neonat);
		//загрузка справочника psofa	 //BOB - 20.02.2020
		this.ScalePanel_load('psofa', ERPEW_NSI.EvnScalepsofa);
		this.findById('swERPEW_psofa_age').setValue(win.getAge_month(win.pers_data.Person_Birthday.date, 'amer'));
		this.findById('swERPEW_psofa_age').fireEvent('change', this.findById('swERPEW_psofa_age'), this.findById('swERPEW_psofa_age').getValue(),this.findById('swERPEW_psofa_age').getValue()); // запуск события в поле возраста в месяцах
		//загрузка справочника psas	 //BOB - 20.02.2021
		this.ScalePanel_load('psas', ERPEW_NSI.EvnScalepsas);
		//загрузка справочника pelod	 //BOB - 20.02.2020
		this.ScalePanel_load('pelod', ERPEW_NSI.EvnScalepelod);
		this.findById('swERPEW_pelod_age').setValue(win.getAge_month(win.pers_data.Person_Birthday.date, 'amer'));
		this.findById('swERPEW_pelod_age').fireEvent('change', this.findById('swERPEW_pelod_age'), this.findById('swERPEW_pelod_age').getValue(),this.findById('swERPEW_pelod_age').getValue()); // запуск события в поле возраста в месяцах
		//загрузка справочника npass	 //BOB - 20.02.2020
		this.ScalePanel_load('npass', ERPEW_NSI.EvnScalenpass);
		//загрузка справочника nips	 //BOB - 20.02.2020
		this.ScalePanel_load('nips', ERPEW_NSI.EvnScalenips);
		//загрузка справочника comfort	 //BOB - 20.02.2020
		this.ScalePanel_load('comfort', ERPEW_NSI.EvnScalecomfort);
		//загрузка справочника pipp	 //BOB - 20.02.2020
		this.ScalePanel_load('pipp', ERPEW_NSI.EvnScalepipp);
		//загрузка справочника bind	 //BOB - 20.02.2020
		this.ScalePanel_load('bind', ERPEW_NSI.EvnScalebind);
		
		//состояния, сортировка по id в убывающем порядке
		win.findById('swERPEW_RC_Condition').store.sort("ReanimConditionType_id", "DESC"); // BOB - 21.03.2019 

		//загрузка справочника СИНДРОМОВ - отличная от других справочников BOB - 24.01.2019
		win.findById('swERPEW_RC_ReanimatSyndrome').getStore().load();

		//рисование панелей дыхание аускультативно
		if (win.findById('swERPEW_RC_Auscultatory_right_1') == null)
			this.AuscultatoryBuild();

		//загрузка грида регулярного наблюдения состояния
		this.findById('swERPEW_EvnReanimatCondition_Grid').getStore().load({
			params:{
				EvnReanimatCondition_pid: this.erp_data.EvnReanimatPeriod_id
			}
		});
		this.findById('swERPEW_Condition_Panel').expand();
		
		this.restore();
		this.center();
		this.maximize();
		//загрузка объекта персональных данных !!!!!!! НАДО  !!!!!!!!!!!!!!!!!!!!!!!!!!
		var persFrame = this.findById('swERPEW_PersonInfo');
		persFrame.load({
            Person_id: this.pers_data.Person_id
        });
		
		//загрузка отдельных полей
		this.findById('swERPEW_EvnPS_NumCard').setText(this.erp_data.EvnPS_NumCard);
		this.findById('swERPEW_LpuSection_Name').setText(this.erp_data.LpuSection_Name);
		this.findById('swERPEW_EvnSection_setDate').setText(this.erp_data.EvnSection_setDate);
		this.findById('swERPEW_swERPEW_BaseDiag').setText(this.erp_data.Diag_Code + ' ' + this.erp_data.Diag_Name);
		
		this.findById('swERPEW_MedService_Name').setText(this.erp_data.MedService_Name);
		this.findById('swERPEW_EvnReanimatPeriod_setDate').setValue(this.erp_data.EvnReanimatPeriod_setDate);
		this.findById('swERPEW_EvnReanimatPeriod_setTime').setValue(this.erp_data.EvnReanimatPeriod_setTime);
		this.findById('swERPEW_EvnReanimatPeriod_disDate').setValue(this.erp_data.EvnReanimatPeriod_disDate);
		this.findById('swERPEW_EvnReanimatPeriod_disTime').setValue(this.erp_data.EvnReanimatPeriod_disTime);
		this.ReanimatPeriod_isClosed = (this.erp_data.EvnReanimatPeriod_disDate == null) ? false : true;

		//значение исхода реанимационного периода			
		this.findById('swERPEW_ReanimResultType').setValue(this.erp_data.ReanimResultType_id);
		//значение показаний к реанимации
		this.findById('swERPEW_ReanimReasonType').setValue(this.erp_data.ReanimReasonType_id);	
		//профиль коек //BOB - 25.10.2018
		this.findById('swERPEW_BedProfile').setValue(this.erp_data.LpuSectionBedProfile_id);	
		//возрастная категория   //BOB - 23.01.2020
		this.findById('swERPEW_ReanimatAgeGroup').setValue(this.erp_data.ReanimatAgeGroup_id);	

		//Установка неактивности на Панель служба / начало-конец периода  /  показания к переводу в реанимацуию - исход
		//и кнопки добавления прочих объектов
		//в зависимости от режима edit/view
		if (this.action == 'view'){
			Ext.select('input', true, 'swERPEW_GenralData').each(function(el){
				var id = el.id; //выделяю параметр id из Ext.Element
				var object = win.findById(id);	//ищу в окне объект ExtJS
				if(object){ // если нахожу, то 
					object.setDisabled(true); // делаю Disabled /Enabled
				}
			});
			Ext.getCmp('swERPEW_ButtonSave').disable();
			if (Ext.getCmp('swERPEW_EvnScaleButtonAdd'))
				Ext.getCmp('swERPEW_EvnScaleButtonAdd').disable(); // кнопку добавления делаю неактивной
			if (Ext.getCmp('swERPEW_EvnScaleButtonDel'))
				Ext.getCmp('swERPEW_EvnScaleButtonDel').disable(); // кнопку удаления делаю неаактивной
			//BOB - 04.07.2019 - убрал управление кнопками мероприятий
		}
		else {
			//Управление активностями и доступностью в зависимости от точек вызова и функций начало/конца РП и т.д.
			this.findById('swERPEW_EvnReanimatPeriod_setDate').enable();
			this.findById('swERPEW_EvnReanimatPeriod_setTime').enable();
			this.findById('swERPEW_ReanimReasonType').enable();
			this.findById('swERPEW_EvnReanimatPeriod_disDate').enable();
			this.findById('swERPEW_EvnReanimatPeriod_disTime').enable();
			this.findById('swERPEW_ReanimResultType').enable();
			this.findById('swERPEW_BedProfile').enable();	 //BOB - 23.01.2020
			this.findById('swERPEW_ReanimatAgeGroup').enable();	  //BOB - 23.01.2020
				Ext.getCmp('swERPEW_ButtonSave').enable();
			if (Ext.getCmp('swERPEW_EvnScaleButtonAdd'))
				Ext.getCmp('swERPEW_EvnScaleButtonAdd').enable(); // кнопку добавления делаю неактивной
			if (Ext.getCmp('swERPEW_EvnScaleButtonDel'))
				Ext.getCmp('swERPEW_EvnScaleButtonDel').enable(); // кнопку удаления делаю аактивной
			//BOB - 04.07.2019 - убрал управление кнопками мероприятий

			if (this.from == 'moveToReanimation') {  //перевод в реанимацию
				this.findById('swERPEW_EvnReanimatPeriod_disDate').disable();
				this.findById('swERPEW_EvnReanimatPeriod_disTime').disable();
				this.findById('swERPEW_ReanimResultType').disable();
			}
			else if (this.from == 'endReanimatReriod') {  // окончание Реанимационного периода
				this.findById('swERPEW_EvnReanimatPeriod_disDate').setValue(getGlobalOptions().date);
				var Date_ = new Date();
				this.findById('swERPEW_EvnReanimatPeriod_disTime').setValue(('00'+Date_.getHours().toString()).slice(-2)+':'+('00'+Date_.getMinutes().toString()).slice(-2));
				this.findById('swERPEW_ReanimResultType').setValue(1);
			}
		}
		this.EvnReanimatCondition_ButtonManag(this,true);  //BOB - 11.02.2019

		//загрузка XML шаблона  //BOB - 08.11.2017 - закомментарил - пока не надо	
		/*
		var NotePanel = this.findById('swERPEW_Notes_Panel');
		NotePanel.collapse();
		NotePanel.setBaseParams({
//			userMedStaffFact: sw.Promed.MedStaffFactByUser.last,
//			UslugaComplex_id: base_form.findField('UslugaComplex_id').getValue(),
//			Server_id: base_form.findField('Server_id').getValue(),
			Evn_id: this.EvnReanimatPeriod_id
		});
		NotePanel.doLoadData();
		*/
		
	},

	//рисование панелей дахания аускультативноG
	AuscultatoryBuild() {
		var win = this;
		var SideType =  win.SideType;
		//console.log('BOB_thear_is_swERPEW_RC_Auscultatory_right_1=',win.findById('swERPEW_RC_Auscultatory_right_1')); 
		
		for(var i in SideType){
			if (SideType[i]['SideType_SysNick']){
				this.findById('swERPEW_RC_Breathing_Panel').add(new Ext.form.FieldSet({
					id: 'swERPEW_RC_Auscultatory_'+SideType[i]['SideType_SysNick'],
					title: 'аускультативно ' +  SideType[i]['SideType_Name'],
					layout:'form',
					autoHeight: true,
					collapsible: true,
					style: 'margin: 3px 0 0 0; padding: 0 0 3px 0; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
					listeners: {
						collapse: function(p) {
							win.doLayout();
						},
						expand: function(p) {
							win.doLayout();
						}
					}			
				}));
				//панель АКСКУЛЬТАТИВНО
				this.findById('swERPEW_RC_Auscultatory_'+SideType[i]['SideType_SysNick']).add(
					{
						//id: 'swERPEW_RC_Auscultatory_'+SideType[i]['SideType_SysNick']+'_1_pan',
						layout:'column',
						border:false,						
						items:[	
							//Лейбл Аускультативно 
							{									
								layout:'form',
								style:' margin-left: 22px; margin-top: 4px; font-size: 12px; width:90px ',
								items:[
									new Ext.form.Label({
										text: 'Аускультативно'
									})					
								]
							},
							//радиобатоны Аускультативно	
							{									
								layout:'form',
								labelWidth:1,
								style: 'margin-top: 4px;  margin-left: 4px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
								items:[
									new Ext.form.RadioGroup({
										id:'swERPEW_RC_Auscultatory_'+SideType[i]['SideType_SysNick']+'_1',
										labelSeparator: '',
										vertical: true,
										columns: 2,
										SideType_SysNick: SideType[i]['SideType_SysNick'],
										items: [
											{boxLabel: '---', name: 'Auscultatory_'+SideType[i]['SideType_SysNick']+'_1', inputValue: 0, width: 120}, 
											{boxLabel: 'везикулярное', name: 'Auscultatory_'+SideType[i]['SideType_SysNick']+'_1', inputValue: 1, width: 120}, 
											{boxLabel: 'жесткое', name: 'Auscultatory_'+SideType[i]['SideType_SysNick']+'_1', inputValue: 2, width: 120},
											{boxLabel: 'ослабленное', name: 'Auscultatory_'+SideType[i]['SideType_SysNick']+'_1', inputValue: 3, width: 120}, 
											{boxLabel: 'не проводится', name: 'Auscultatory_'+SideType[i]['SideType_SysNick']+'_1', inputValue: 4, width: 120}
										],
										listeners: {
											'change': function(field, checked) {
												var EvnScalesGridRow = win.findById('swERPEW_EvnReanimatCondition_Grid').getSelectionModel().getSelected().data;  //выбранная строка в гриде Реанимационных мероприятий
												if (win.findById('swERPEW_Condition_Panel').BreathAuscult_records[EvnScalesGridRow['EvnReanimatCondition_id']]){ 
													win.findById('swERPEW_Condition_Panel').BreathAuscult_records[EvnScalesGridRow['EvnReanimatCondition_id']][field.SideType_SysNick]['BreathAuscultative_Auscult'] = checked.inputValue;
												}
											}
										}
									})	
								]
							},
							//Аускультативно - вариант пользователя: текстовое поле 
							{
								allowBlank: true,
								fieldLabel: '',
								labelSeparator: '',
								id: 'swERPEW_RC_AuscultTxt_'+SideType[i]['SideType_SysNick'],
								width: 928,
								style:'margin-top: 2px; margin-left: 4px;',
								value:'',
								xtype: 'textfield',
								SideType_SysNick: SideType[i]['SideType_SysNick'],
								listeners:{  
									'change':function (field, newValue, oldValue) {
										var EvnScalesGridRow = win.findById('swERPEW_EvnReanimatCondition_Grid').getSelectionModel().getSelected().data;  //выбранная строка в гриде Реанимационных мероприятий
										win.findById('swERPEW_Condition_Panel').BreathAuscult_records[EvnScalesGridRow['EvnReanimatCondition_id']][field.SideType_SysNick]['BreathAuscultative_AuscultTxt'] = newValue;
									}.createDelegate(this)
								}
							}
						]	
					}
				);
				//панель ХРИПЫ
				this.findById('swERPEW_RC_Auscultatory_'+SideType[i]['SideType_SysNick']).add(
					{
						layout:'column',
						border:false,						
						items:[	
							//Лейбл Хрипы 
							{									
								layout:'form',
								style:' margin-left: 22px; margin-top: 4px; font-size: 12px; width:90px ',
								items:[
									new Ext.form.Label({
										text: 'Хрипы'
									})					
								]
							},
							//радиобатоны Хрипы								
							{									
								layout:'form',
								labelWidth:1,
								style: 'margin-top: 4px;  margin-left: 4px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
								items:[
									new Ext.form.RadioGroup({
										id:'swERPEW_RC_Auscultatory_'+SideType[i]['SideType_SysNick']+'_3',
										labelSeparator: '',
										vertical: true,
										columns: 2,
										SideType_SysNick: SideType[i]['SideType_SysNick'],
										items: [
											{boxLabel: '---', name: 'Auscultatory_'+SideType[i]['SideType_SysNick']+'_3', inputValue: 0, width: 120}, 
											{boxLabel: 'хрипов нет', name: 'Auscultatory_'+SideType[i]['SideType_SysNick']+'_3', inputValue: 1, width: 120}, 
											{boxLabel: 'хрипы влажные', name: 'Auscultatory_'+SideType[i]['SideType_SysNick']+'_3', inputValue: 2, width: 120}, 
											{boxLabel: 'хрипы сухие', name: 'Auscultatory_'+SideType[i]['SideType_SysNick']+'_3', inputValue: 3, width: 120}
										],
										listeners: {
											'change': function(field, checked) {
												var EvnScalesGridRow = win.findById('swERPEW_EvnReanimatCondition_Grid').getSelectionModel().getSelected().data;  //выбранная строка в гриде Реанимационных мероприятий
												if (win.findById('swERPEW_Condition_Panel').BreathAuscult_records[EvnScalesGridRow['EvnReanimatCondition_id']]){ 
													win.findById('swERPEW_Condition_Panel').BreathAuscult_records[EvnScalesGridRow['EvnReanimatCondition_id']][field.SideType_SysNick]['BreathAuscultative_Rale'] = checked.inputValue;
												}
											}
										}
									})	
								]
							},
							//Хрипы - вариант пользователя: текстовое поле 
							{
								allowBlank: true,
								fieldLabel: '',
								labelSeparator: '',
								id: 'swERPEW_RC_RaleTxt_'+SideType[i]['SideType_SysNick'],
								width: 928,
								style:'margin-top: 2px; margin-left: 4px;',
								value:'',
								xtype: 'textfield',
								SideType_SysNick: SideType[i]['SideType_SysNick'],
								listeners:{
									'change':function (field, newValue, oldValue) {
										var EvnScalesGridRow = win.findById('swERPEW_EvnReanimatCondition_Grid').getSelectionModel().getSelected().data;  //выбранная строка в гриде Реанимационных мероприятий
										win.findById('swERPEW_Condition_Panel').BreathAuscult_records[EvnScalesGridRow['EvnReanimatCondition_id']][field.SideType_SysNick]['BreathAuscultative_RaleTxt'] = newValue;
									}.createDelegate(this)
								}
							}
						]
					}
				);
				//панель Плевральный дренаж
				this.findById('swERPEW_RC_Auscultatory_'+SideType[i]['SideType_SysNick']).add(
					{
						layout:'column',
						border:false,						
						items:[	
							//Лейбл Плевральный дренаж 
							{									
								layout:'form',
								style:' margin-left: 22px; margin-top: 4px; font-size: 12px; width:90px ',
								items:[
									new Ext.form.Label({
										text: 'Плевральный дренаж'
									})					
								]
							},
							//радиобатоны Плевральный дренаж								
							{									
								layout:'form',
								labelWidth:1,
								style: 'margin-top: 4px;  margin-left: 4px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
								items:[
									new Ext.form.RadioGroup({
										id:'swERPEW_RC_Auscultatory_'+SideType[i]['SideType_SysNick']+'_2',
										labelSeparator: '',
										vertical: true,
										columns: 3,
										SideType_SysNick: SideType[i]['SideType_SysNick'],
										items: [
											{boxLabel: '---', name: 'Auscultatory_'+SideType[i]['SideType_SysNick']+'_2', inputValue: 0, width: 80}, 
											{boxLabel: 'нет', name: 'Auscultatory_'+SideType[i]['SideType_SysNick']+'_2', inputValue: 1, width: 80}, 
											{boxLabel: 'есть', name: 'Auscultatory_'+SideType[i]['SideType_SysNick']+'_2', inputValue: 2, width: 80} 
										],
										listeners: {
											'change': function(field, checked) {
												var EvnScalesGridRow = win.findById('swERPEW_EvnReanimatCondition_Grid').getSelectionModel().getSelected().data;  //выбранная строка в гриде Реанимационных мероприятий
												if (win.findById('swERPEW_Condition_Panel').BreathAuscult_records[EvnScalesGridRow['EvnReanimatCondition_id']]){ 
													win.findById('swERPEW_Condition_Panel').BreathAuscult_records[EvnScalesGridRow['EvnReanimatCondition_id']][field.SideType_SysNick]['BreathAuscultative_IsPleuDrain'] = checked.inputValue;
												}
											}
										}
									})	
								]
							},
							//Плевральный дренаж - вариант пользователя: текстовое поле 
							{
								allowBlank: true,
								fieldLabel: '',
								labelSeparator: '',
								id: 'swERPEW_RC_PleuDrainTxt_'+SideType[i]['SideType_SysNick'],
								width: 928,
								style:'margin-top: 2px; margin-left: 4px;',
								value:'',
								xtype: 'textfield',
								SideType_SysNick: SideType[i]['SideType_SysNick'],
								listeners:{
									'change':function (field, newValue, oldValue) {
										var EvnScalesGridRow = win.findById('swERPEW_EvnReanimatCondition_Grid').getSelectionModel().getSelected().data;  //выбранная строка в гриде Реанимационных мероприятий
										win.findById('swERPEW_Condition_Panel').BreathAuscult_records[EvnScalesGridRow['EvnReanimatCondition_id']][field.SideType_SysNick]['BreathAuscultative_PleuDrainTxt'] = newValue;
										//win.findById('swERPEW_Condition_Panel').BreathAuscult_records[EvnScalesGridRow['EvnReanimatCondition_id']][field.SideType_SysNick]['BA_RecordStatus'] = 2;										
									}.createDelegate(this)
								}
							}
						]
					}
				);
			}
		};
	},

	//загрузка шкал
	ScalePanel_load(SpravName, EvnScaleSprav ) {
		var Datas =  [];// данные одного параметра, заготовляем для первого параметра
		var fields = [{name:'ScaleParameterType_SysNick', type:'string'},
					 {name:'ScaleParameterResult_Name', type:'string'},
					 {name:'ScaleParameterResult_id',type:'string'},
					 {name:'ScaleParameterResult_Value',type:'int'},
					 {name:'ScaleParameterType_id',type:'int'}];						 	


		//console.log('ScalePanel_load: SpravName = ', SpravName, ', EvnScaleSprav = ', EvnScaleSprav); 
		var varSysNick = ''; // переменная для хранения SysNick и контроля не пора ли менять параметр шкалы
		for(var SysNick in EvnScaleSprav) { //цикл по параметрам шкалы
			if (varSysNick != SysNick){   // если параметр не совпадает с тем, что был на прошлой итерации
				if (varSysNick != ''){		// если не первый раз контролируем реквизит 
					//загрузка данных параметра в комбо
					if (this.findById('swERPEW_'+ SpravName +'_' + varSysNick)) {
						this.findById('swERPEW_'+ SpravName +'_' + varSysNick).store = new Ext.data.SimpleStore(  {           
							fields: fields,
							data: Datas
						});
					}
					Datas =  [];// данные одного параметра, заготовляем для следующего параметра
				}
				varSysNick = SysNick; //меняю параметр в переменной
			}
			//формирование Datas
			for (var i in EvnScaleSprav[varSysNick]) { // цикл по значениям параметра
				Datas[i]= [ EvnScaleSprav[varSysNick][i].ScaleParameterType_SysNick, 
							EvnScaleSprav[varSysNick][i].ScaleParameterResult_Name,   
							EvnScaleSprav[varSysNick][i].ScaleParameterResult_id,   
							EvnScaleSprav[varSysNick][i].ScaleParameterResult_Value,   
							EvnScaleSprav[varSysNick][i].ScaleParameterType_id ];
			};
		}
		//console.log('BOB_ScalePanel_load_ID=', 'swERPEW_'+ SpravName +'_' + varSysNick); 
		//загрузка данных последнего параметра в комбо
		if (varSysNick != '' && this.findById('swERPEW_'+ SpravName +'_' + varSysNick)) {
			this.findById('swERPEW_'+ SpravName +'_' + varSysNick).store = new Ext.data.SimpleStore(  {           
				fields: fields,
				data: Datas
			});
		}
	},

	//загрузка панели события расчёта по шкале
	EvnScale_view: function() {
		//	console.log('BOB_EvnScale_view='); 
		var win = this;
		
		var EvnScalesGridRowData = this.findById('swERPEW_EvnScales_Grid').getSelectionModel().getSelected().data;  //выбранная строка в гриде событий шкал
		var swERPEW_EvnScaleType =  this.findById('swERPEW_EvnScaleType');// комбо тип шкалы
		
		swERPEW_EvnScaleType.setValue(EvnScalesGridRowData['ScaleType_SysNick']); //установка значения комбо тип шкалы
		this.findById('swERPEW_EvnScale_setDate').setValue(EvnScalesGridRowData['EvnScale_setDate']); //установка даты события шкалы
		this.findById('swERPEW_EvnScale_setTime').setValue(EvnScalesGridRowData['EvnScale_setTime']); //установка времени события шкалы
		
		//Загрузка значений реквизитов шкал
		var EvnScale_id = EvnScalesGridRowData['EvnScale_id'];  //Id события в строке в гриде событий шкал
		var ScaleType_SysNick = EvnScalesGridRowData['ScaleType_SysNick'];  //Id события в строке в гриде событий шкал  //BOB - 26.11.2018

		//console.log('BOB_EvnScale_view_EvnScalesGridRowData=', EvnScalesGridRowData); 
		//BOB - 25.02.2020  //установка возраста в шкале psofa или pelod или если запись добавленв
		if ((ScaleType_SysNick == 'psofa') || (ScaleType_SysNick == 'pelod')){
			this.findById('swERPEW_' + ScaleType_SysNick + '_age').setValue(EvnScalesGridRowData['EvnScale_AgeMonth']);
			this.findById('swERPEW_' + ScaleType_SysNick + '_age').fireEvent('change', this.findById('swERPEW_' + ScaleType_SysNick + '_age'), this.findById('swERPEW_' + ScaleType_SysNick + '_age').getValue(),this.findById('swERPEW_' + ScaleType_SysNick + '_age').getValue()); // запуск события в поле возраста в месяцах
		} else if  (EvnScale_id == 'New_GUID_Id') {
			this.findById('swERPEW_psofa_age').setValue(EvnScalesGridRowData['EvnScale_AgeMonth']);
			this.findById('swERPEW_psofa_age').fireEvent('change', this.findById('swERPEW_psofa_age'), this.findById('swERPEW_psofa_age').getValue(),this.findById('swERPEW_psofa_age').getValue()); // запуск события в поле возраста в месяцах
			this.findById('swERPEW_pelod_age').setValue(EvnScalesGridRowData['EvnScale_AgeMonth']);
			this.findById('swERPEW_pelod_age').fireEvent('change', this.findById('swERPEW_pelod_age'), this.findById('swERPEW_pelod_age').getValue(),this.findById('swERPEW_pelod_age').getValue()); // запуск события в поле возраста в месяцах
		}

		//запуск события выбора в комбо, чтобы загрузилась панель нужной шкалы
		var index = swERPEW_EvnScaleType.getStore().find('ScaleType_SysNick',EvnScalesGridRowData['ScaleType_SysNick']);//нахожу индекс в store комбо по SysNick из грида
		var rec = swERPEW_EvnScaleType.getStore().getAt(index);  // нахожу record по индексу
		swERPEW_EvnScaleType.from_select = false;
		swERPEW_EvnScaleType.fireEvent('select', swERPEW_EvnScaleType, rec,index + 1); // запуск события в комбо, туда направляю index + 1 потому что так передаётся индекс при выборе с интерфейса, возможно это когда в комбо разрешено пустое состояние

		var ScaleRequ = []; //для apache: массив реквизитов узла шкалы для загрузки узлов и отображения выбранного  
		

		if (EvnScale_id == 'New_GUID_Id'){ //новый расчёт
			this.findById('swERPEW_EvnScaleButtonSave').enable(); // кнопку сохранения делаю активной
			
			if (EvnScalesGridRowData['ScaleType_SysNick'] != '') {
				//Ext.select('input, label', true, 'swERPEW_GeneralScalesPanel').each(function(el){  //BOB - 26.11.2018
				Ext.select('input', true, 'swERPEW_' + ScaleType_SysNick + '_ScalePanel').each(function(el){
					var id = el.id; //выделяю параметр id из Ext.Element
					//console.log('BOB_id_En=',id); 
					var object = win.findById(id);	//ищу в окне объект ExtJS
					//console.log('BOB_Object_En=',object);  
					if(object){ // если нахожу, то 
						//object.setDisabled(false); // делаю Enabled   //BOB - 26.11.2018 - закомментарено
						if ((object.xtype == 'combo') && (id != 'swERPEW_EvnScaleType' )) {
							if ((win.findById(id+'_Hid').getValue() != null)&&(win.findById(id+'_Hid').getValue() > 0)){ //восстанавливаю в полюшках значения баллов нового расчёта
								object.setValue(win.findById(id+'_Hid').getValue()); // восстанавливаю в combo значения нового расчёта
								var index = object.getStore().find('ScaleParameterResult_id',win.findById(id+'_Hid').getValue());//нахожу индекс в store комбо по ScaleParameterResult_id из БД
								var rec = object.getStore().getAt(index);  // нахожу record по index, 
								win.findById(id+'_Val').setText(rec.data['ScaleParameterResult_Value']);
							}
							else {
								object.setValue(null); 
								win.findById(id+'_Val').setText('0');
							}
						} 
					}
				});
				//BOB - 26.11.2018
				if (ScaleType_SysNick == "VAScale") {
					for (var j = 0; j < win.findById('swERPEW_VAScale_VAScale').items.items.length; j++  )
						win.findById('swERPEW_VAScale_VAScale').items.items[j].setValue(win.findById('swERPEW_VAScale_VAScale_Hid').getValue() == j ? true : false);							
				}

				if (ScaleType_SysNick == "hunt_hess") {
					win.findById('wERPEW_hunt_hess_Dopoln').items.items[0].setValue(win.findById('swERPEW_hunt_hess_Dopoln_Hid').getValue());
				}
				win.findById('swERPEW_'+EvnScalesGridRowData['ScaleType_SysNick']+'_ScalePanel').overall_results('EvnScale_view_New_GUID_Id');				
			}
			
			if (EvnScalesGridRowData['ScaleType_SysNick'] == 'apache')
				ScaleRequ = this.findById('swERPEW_apache_Tree').ScaleRequ;
		}
		else {
			
			// Сохранённый расчёт по шкале - Получение данных из БД
			this.findById('swERPEW_EvnScaleButtonSave').disable(); // кнопку сохранения делаю неактивной
			//var ScaleRequ = [];
			
			$.ajax({
				mode: "abort",
				type: "post",
				async: false,
				url: '/?c=EvnReanimatPeriod&m=getEvnScaleContent',
				data: { EvnScale_id: EvnScale_id },
				success: function(response) {
					var EvnScaleContent = Ext.util.JSON.decode(response);
					//console.log('BOB_EvnScaleContent=',EvnScaleContent); 
																		
					for (var i in EvnScaleContent) {
						//BOB - 26.11.2018
						if (EvnScaleContent[i].ScaleType_SysNick == "VAScale"){   							
							for (var j = 0; j < win.findById('swERPEW_VAScale_VAScale').items.items.length; j++  )
								win.findById('swERPEW_VAScale_VAScale').items.items[j].setValue(EvnScalesGridRowData['EvnScale_Result'] == j ? true : false);
						} else {
							
							//установка значения combo
							var name = 'swERPEW_'+EvnScaleContent[i].ScaleType_SysNick+'_'+EvnScaleContent[i].ScaleParameterType_SysNick;
							var combo = win.findById(name);
							if (combo) {
								var ScaleParameterResult_id = EvnScaleContent[i].ScaleParameterResult_id;
								combo.setValue(ScaleParameterResult_id);   	

								//запуск события выбора в комбо, чтобы установить баллы в поле и посчитать сумму баллов
								var index = combo.getStore().find('ScaleParameterResult_id',ScaleParameterResult_id);//нахожу индекс в store комбо по ScaleParameterResult_id из БД
								var rec = combo.getStore().getAt(index);  // нахожу record по index, 
								combo.fireEvent('select', combo, rec,index); // запуск события в комбо 
							}

							if ((EvnScaleContent[i].ScaleType_SysNick == "apache") && (parseInt(EvnScaleContent[i].ScaleParameterType_id)) >= 29) //BOB - 10.02.2018
								ScaleRequ = EvnScaleContent[i];
							
							if (EvnScaleContent[i].ScaleType_SysNick == "hunt_hess") {
								if (parseInt(win.findById('swERPEW_hunt_hess_hunt_hess_Val').text) == EvnScalesGridRowData['EvnScale_Result'])
									win.findById('wERPEW_hunt_hess_Dopoln').items.items[0].setValue(false);
								else
									win.findById('wERPEW_hunt_hess_Dopoln').items.items[0].setValue(true);
								win.findById('swERPEW_hunt_hess_ScalePanel').overall_results();
							}
						}
					};
				}, 
				error: function() {
					alert("При обработке запроса на сервере произошла ошибка!");
				} 
			});	
		}
		
		//если APACHE - загрузка и раскрытие дерева
		//console.log('BOB_ScaleRequ=',ScaleRequ); 
		if (ScaleRequ.ScaleType_SysNick == "apache"){
			this.findById('swERPEW_apache_Tree').setDisabled(EvnScalesGridRowData['EvnScale_id'] == 'New_GUID_Id' ? false : true); // делаю Disabled /Enabled
			this.findById('swERPEW_apache_ScalePanel').scale_load(ScaleRequ);
		}
		
							
		
		
		//установка активности/неактивности на область шкал
		// создание выборки элементов 'input', внутри панели с id 'swERPEW_GeneralScalesPanel', возвращает с типом  Ext.Element
		//по массиву выбранных элементов
		Ext.select('input', true, 'swERPEW_GeneralScalesPanel').each(function(el){
			var id = el.id; //выделяю параметр id из Ext.Element
			//console.log('BOB_id_dis=',id); 
			var object = win.findById(id);	//ищу в окне объект ExtJS
			//console.log('BOB_Object_dis=',object); 
			if(object){ // если нахожу, то 
				object.setDisabled(EvnScalesGridRowData['EvnScale_id'] == 'New_GUID_Id' ? false : true); // делаю Disabled /Enabled
			}
		});
		
		this.findById('swERPEW_VAScale_VAScale').setDisabled(EvnScalesGridRowData['EvnScale_id'] == 'New_GUID_Id' ? false : true); 

		
	},

	//создание нового события расчёта по шкале
	EvnScale_Add: function() {
		var win = this;
		this.findById('swERPEW_EvnScaleType').setValue(null); // устанавливаю в комбо тип шкалы пустоту
		var curDate = getValidDT(getGlobalOptions().date, ''); // считываю из глобальных параметров текущую дату
		
		this.findById('swERPEW_EvnScale_setDate').setValue(curDate);// в дату события расчёта - текущую дату
		this.findById('swERPEW_EvnScale_setTime').setValue(''); // вовремя события расчёта - пустоту

		
		//Очистка значений реквизитов и результатов во всех шкалах
		Ext.select('input, label', true, 'swERPEW_GeneralScalesPanel').each(function(el){
			var id = el.id; //выделяю параметр id из Ext.Element
			var object = win.findById(id);	//ищу в окне объект ExtJS
			if(object){ // если нахожу, то 
				//	console.log('BOB_Object=',object);  
				object.setDisabled(false); // делаю Enabled
				if (object.xtype === 'combo')
					//object.setValue(null);
					object.value = null;
				if (object.xtype ==='hidden')
					//object.setValue(0);
					object.value = 0;
				if (object.xtype === 'label')
					//object.setText('0');
					object.value = '0';
				if (object.id === 'swERPEW_EvnScaleResultText')
					//object.setText('');
					object.text = '';
			}
		});
		//BOB - 26.11.2018
		for (var j = 0; j < win.findById('swERPEW_VAScale_VAScale').items.items.length; j++  )
			this.findById('swERPEW_VAScale_VAScale').items.items[j].setValue(false);

		win.findById('wERPEW_hunt_hess_Dopoln').items.items[0].setValue(false);
		
		//добавление строки в грид		
		var Grid = this.findById('swERPEW_EvnScales_Grid');
		Grid.store.insert(0, new Ext.data.Record({
			EvnScale_id: 'New_GUID_Id',
			EvnScale_pid: this.EvnReanimatPeriod_id,
			Person_id: this.Person_id,
			PersonEvn_id: this.PersonEvn_id,
			Server_id: this.Server_id,
			EvnScale_setDate: curDate,
			EvnScale_setTime: '',
			ScaleType_id: 0,
			ScaleType_Name: '',
			ScaleType_SysNick: '',
			EvnScale_Result: 0,
			EvnScale_ResultTradic: '',
			EvnScale_AgeMonth: win.getAge_month(win.pers_data.Person_Birthday.date, 'amer') //BOB - 25.02.2020, надо только для некоторых, но вроде не помешает
		}));

		//для APACHE - активизация и раскрытие дерева
		var ScaleRequ  = {
			ScaleType_SysNick: 'apache',
			ScaleParameterResult_id: '',
			ScaleParameterType_SysNick: '',
			ScaleParameterType_id: ''
		};
		//console.log('BOB_ScaleRequ=',ScaleRequ); 
		this.findById('swERPEW_apache_Tree').setDisabled(false); // делаю Enabled
		this.findById('swERPEW_apache_ScalePanel').scale_load(ScaleRequ);

		Grid.getSelectionModel().selectRow(0); 	//установка выбранности на первой строке грида 
		this.findById('swERPEW_EvnScaleButtonSave').enable(); // кнопку сохранения делаю активной
		Ext.getCmp('swERPEW_EvnScaleButtonAdd').disable(); // кнопку добавления делаю неактивной
		Ext.getCmp('swERPEW_EvnScaleButtonDel').disable(); // кнопку удаления делаю неактивной

	},

	//удаление шкалы
	EvnScale_Del: function() {
		var win = this;
		
		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId) {
				if ( buttonId == 'yes' ) {
					
					var Grid = this.findById('swERPEW_EvnScales_Grid');
					var EvnRCGridRowData = Grid.getSelectionModel().getSelected().data;  //выбранная строка в гриде событий регулярного наблюдения состояния
					console.log('BOB_EvnRCGridRowData=',EvnRCGridRowData);  //BOB - 21.05.2018;
					
					if (Ext.isEmpty(EvnRCGridRowData['EvnScale_id'])) {
						Ext.MessageBox.alert('Внимание!', 'Не выбрана строка в списке наблюдений! ');
						return false;
					}
					
					//var RawId = Grid.getSelectionModel().getSelected().id;
					this.ScaleGridLoadRawNum = Grid.getStore().find('EvnScale_id',Grid.getSelectionModel().getSelected().id);
					
					var data = 	{ 
						EvnScale_id: EvnRCGridRowData['EvnScale_id']
					};				
									
				
					$.ajax({
						mode: "abort",
						type: "post",
						async: false,
						url: '/?c=EvnReanimatPeriod&m=EvnScales_Del',
						data: data,
						success: function(response) {
							var DelResponse = Ext.util.JSON.decode(response);
							console.log('BOB_DelResponse=',DelResponse); 
							if (DelResponse['success'] == 'true'){
								if (win.ScaleGridLoadRawNum == Grid.getStore().data.length - 1)//перейти на запись с тем же положением, а если последняя то на первую	
									win.ScaleGridLoadRawNum--;
								Grid.getStore().reload();	//перезагрузка грида Реанимационных мероприятий	
								//console.log('BOB_RawNum0=',win.ScaleGridLoadRawNum);  //BOB - 21.05.2018;
							}
							else	
								Ext.MessageBox.alert('Ошибка сохранения!', DelResponse['Error_Msg']);
								//alert('Ошибка сохранения.'+SaveResponse['message']);
						}, 
						error: function() {
							Ext.MessageBox.alert('Ошибка сохранения!', "При обработке запроса на сервере произошла ошибка!");
						} 
					});		
				}

			}.createDelegate(this),
			icon: Ext.Msg.WARNING,
			msg: 'Вы действительно хотите удалить шкалу?',
			title: 'Внимание!'
		});
	},

	//сохранение события расчёта по шкале
	EvnScale_Save: function(b,e) {
	
		var win = this;
		var EvnScalesGridRowData = this.findById('swERPEW_EvnScales_Grid').getSelectionModel().getSelected().data;  //выбранная строка в гриде событий шкал
		
		
		if (EvnScalesGridRowData['EvnScale_id'] == 'New_GUID_Id'){ //если новый расчёт
	
			//контроль перед сохранением
			var ErrMessag = '';
			if (EvnScalesGridRowData['EvnScale_setDate'] == '')
				ErrMessag += 'дата расчёта<br>';
			if (EvnScalesGridRowData['EvnScale_setTime'] == '')
				ErrMessag += 'время расчёта<br>';
			if (EvnScalesGridRowData['ScaleType_id'] == 0)
				ErrMessag += 'тип шкалы<br>';
			else {
				var ScalePanel = this.findById('swERPEW_'+ EvnScalesGridRowData['ScaleType_SysNick'] + '_ScalePanel');
				var ParamCombos =  ScalePanel.find('xtype', 'combo');
				for (var i in ParamCombos) {
					if(ParamCombos[i].id) { 
						//console.log('BOB_ParamCombos[i].getValue()=',ParamCombos[i].getValue());
						if ((ParamCombos[i].getValue() == null) || (ParamCombos[i].getValue() == ''))
							ErrMessag += 'параметр "' + this.findById(ParamCombos[i].id + '_Lbl').text + '"<br>';
					}
				}			
			}
			
			
			//Контроль даты по отношению к началу и концу РП 
			//Дата наблюдения
			if (this.findById('swERPEW_EvnScale_setDate').getValue() != ''){
				var ES_setDT = this.findById('swERPEW_EvnScale_setDate').getValue();
				var Time = this.findById('swERPEW_EvnScale_setTime').getValue();
				ES_setDT.setHours(parseInt(Time.substr(0, 2)), parseInt(Time.substr(3, 2)));			
				//Начало периода
				var RP_setDT = this.findById('swERPEW_EvnReanimatPeriod_setDate').getValue(); //  + ' ' + 
				Time = this.findById('swERPEW_EvnReanimatPeriod_setTime').getValue();		
				RP_setDT.setHours(parseInt(Time.substr(0, 2)), parseInt(Time.substr(3, 2)));

				if (ES_setDT < RP_setDT)
					ErrMessag += 'дата шкалы меньше даты начала Реанимационного периода<br>';

				//Конец периода
				if (this.findById('swERPEW_EvnReanimatPeriod_disDate').getValue() != ''){
					var RP_disDT = this.findById('swERPEW_EvnReanimatPeriod_disDate').getValue(); //  + ' ' + 
					Time = this.findById('swERPEW_EvnReanimatPeriod_disTime').getValue();		
					RP_disDT.setHours(parseInt(Time.substr(0, 2)), parseInt(Time.substr(3, 2)));		

					if (ES_setDT > RP_disDT)
						ErrMessag += 'дата шкалы больше даты окончания Реанимационного периода<br>';
				}
			}
		
			if (ErrMessag == '') { //если сообщение о незаполненных реквизитах пустое

				//параметры события рсчёта и общие параметры
				var data = 	{ 
					EvnScale_pid: EvnScalesGridRowData['EvnScale_pid'],
					EvnScale_rid: this.EvnReanimatPeriod_rid,
					Lpu_id: this.Lpu_id,
					Person_id: EvnScalesGridRowData['Person_id'],
					PersonEvn_id: EvnScalesGridRowData['PersonEvn_id'],
					Server_id: EvnScalesGridRowData['Server_id'],
					EvnScale_setDate: Ext.util.Format.date(EvnScalesGridRowData['EvnScale_setDate'], 'd.m.Y'),
					EvnScale_setTime: EvnScalesGridRowData['EvnScale_setTime'],
					ScaleType_id: EvnScalesGridRowData['ScaleType_id'],
					EvnScale_Result: EvnScalesGridRowData['EvnScale_Result'],
					EvnScale_ResultTradic: EvnScalesGridRowData['EvnScale_ResultTradic'],
					EvnScale_AgeMonth: EvnScalesGridRowData['EvnScale_AgeMonth']
				};

				//параметры реквизитов шкалы
				var ScalePanel = this.findById('swERPEW_'+ EvnScalesGridRowData['ScaleType_SysNick'] + '_ScalePanel');
				var ParamCombos =  ScalePanel.find('xtype', 'combo');
				var ScaleParameter = [];

				for (i in ParamCombos) {
					if(ParamCombos[i].id) {
						var ScaleParameterType_SysNick = ParamCombos[i].id.replace('swERPEW_'+ EvnScalesGridRowData['ScaleType_SysNick'] + '_', '');
						var ScaleParameterResult_id = ParamCombos[i].getValue();

						var index = ParamCombos[i].getStore().find('ScaleParameterResult_id',ScaleParameterResult_id);//нахожу индекс в store комбо по ScaleParameterResult_id из БД
						var rec = ParamCombos[i].getStore().getAt(index);  // нахожу record по index,

						if (rec) {
							ScaleParameter[ScaleParameter.length] = {
								ScaleParameterType_id: rec.data.ScaleParameterType_id,
								ScaleParameterResult_id: ScaleParameterResult_id
							};
						}

					}
				}
				
				//если шкла APACHE - сохраняю аттрибут коррекции
				if(EvnScalesGridRowData['ScaleType_SysNick'] == 'apache'){
					var node = this.findById('swERPEW_apache_Tree').getSelectionModel().selNode;
					if ((node) && (node.attributes.leaf == true)) {
						ScaleParameter[ScaleParameter.length] = {
							ScaleParameterType_id: node.attributes.ScaleParameterType_id,
							ScaleParameterResult_id: node.attributes.ScaleParameterResult_id
						};
					}
				}

				data['ScaleParameter'] = Ext.util.JSON.encode(ScaleParameter);
				var loadMask = new Ext.LoadMask(Ext.get('swERPEW_Scales_Panel'), {msg: "Идёт сохранение..."});
				loadMask.show();

				$.ajax({
					mode: "abort",
					type: "post",
					async: true,
					url: '/?c=EvnReanimatPeriod&m=EvnScale_Save',
					data: data,
					success: function(response) {
						loadMask.hide();
						var Data = Ext.util.JSON.decode(response);
						var SaveResponse = Ext.util.JSON.decode(response);
						if (SaveResponse['success'] == 'true'){
							win.findById('swERPEW_EvnScales_Grid').getStore().reload();	//перезагрузка грида исследований по шкалам					
							Ext.select('[id$="ScalePanel"]').setStyle('display', 'none'); // панель делаю неактивной
							win.findById('swERPEW_EvnScaleButtonSave').disable(); // кнопку сохранения делаю неактивной
							Ext.getCmp('swERPEW_EvnScaleButtonAdd').enable(); // кнопку добавления делаю активной
							Ext.getCmp('swERPEW_EvnScaleButtonDel').enable(); // кнопку удаления делаю активной
							
							//BOB - 16.09.2019  закидываю в наблюдения состояний результат по шкале
							var vScaleType_SysNick = EvnScalesGridRowData['ScaleType_SysNick'].inlist(['glasgow_ch','glasgow_neonat']) ? 'glasgow' : EvnScalesGridRowData['ScaleType_SysNick'];
							//BOB - 12.03.2020 закидываю в наблюдения за младенцами
							if (win.isNeonatal(win.findById('swERPEW_ReanimatAgeGroup').getValue()))  {
								if(vScaleType_SysNick.inlist(['glasgow','psofa','pelod','comfort','npass','nips'])){
									if (getWnd('swEvnNeonatalSurveyEditWindow').isVisible()) {
										getWnd('swEvnNeonatalSurveyEditWindow').EvnNeonatalSurvey_LoadScaleData(vScaleType_SysNick, EvnScalesGridRowData['EvnScale_Result']);
									}
								}
							} else {
								//BOB - 16.09.2019  закидываю в наблюдения состояний результат по шкале
								if(vScaleType_SysNick.inlist(['apache','sofa','rass','waterlow','mrc','glasgow','glasgow_ch','four'])){
									var EvnReanimatCondition_GridId = win.findById('swERPEW_EvnReanimatCondition_Grid').getStore().find('EvnReanimatCondition_id', 'New_GUID_Id');  //ищу Id новой записи в гриде наблюдений состояния    
									if (EvnReanimatCondition_GridId > -1) {
										var EvnReanimatCondition_GridRow = win.findById('swERPEW_EvnReanimatCondition_Grid').getStore().getAt(EvnReanimatCondition_GridId);
										EvnReanimatCondition_GridRow.data['EvnReanimatCondition_' + vScaleType_SysNick] = EvnScalesGridRowData['EvnScale_Result'];   // в грид наюлюдений состояния
										EvnReanimatCondition_GridRow.commit();
										win.findById('swERPEW_RC_' + vScaleType_SysNick).setValue(EvnReanimatCondition_GridRow.data['EvnReanimatCondition_' + vScaleType_SysNick]);	// в тектовое поле			//BOB - 23.04.2018
										//для MRC особо: ещё и текст вставляю на интерфейсе
										if(vScaleType_SysNick == 'mrc'){
											var idx = win.findById('swERPEW_mrc_mrc').store.find('ScaleParameterResult_Value',EvnReanimatCondition_GridRow.data['EvnReanimatCondition_' + vScaleType_SysNick]);
											var mrc_text = win.findById('swERPEW_mrc_mrc').store.getAt(idx).data.ScaleParameterResult_Name;
											win.findById('swERPEW_RC_mrc').setValue(EvnReanimatCondition_GridRow.data['EvnReanimatCondition_' + vScaleType_SysNick] + ' - ' + mrc_text);
										}
									}
								}	
							}					
						}
						else{
							loadMask.hide();
							Ext.MessageBox.alert('Ошибка сохранения!', SaveResponse['Error_Msg']);
						}
					}, 
					error: function() {
						loadMask.hide();
						Ext.MessageBox.alert('Ошибка сохранения!', "При обработке запроса на сервере произошла ошибка!");
					} 
				});		
			
			}
			else {
				ErrMessag = 'Отсутствуют или неверны следующие реквизиты расчёта шкалы: <br><br>' + ErrMessag;
				Ext.MessageBox.alert('Внимание!', ErrMessag);
			}
		}
	},

	//загрузка просмотра/редактирования реанимационного мероприятия
	EvnReanimatAction_view: function() {
		var win = this;
		
		var EvnRAGridRowData = this.findById('swERPEW_ReanimatAction_Grid').getSelectionModel().getSelected().data;  //выбранная строка в гриде событий реанимационных мероприятий
		var swERPEW_RAType =  this.findById('swERPEW_ReanimatActionType');// комбо тип реанимационных мероприятий
		
		swERPEW_RAType.setValue(EvnRAGridRowData['ReanimatActionType_SysNick']); //установка значения комбо тип реанимационных мероприятий
		this.findById('swERPEW_EvnReanimatAction_setDate').setValue(EvnRAGridRowData['EvnReanimatAction_setDate']); //установка даты события реанимационных мероприятий
		this.findById('swERPEW_EvnReanimatAction_setTime').setValue(EvnRAGridRowData['EvnReanimatAction_setTime']); //установка времени события реанимационных мероприятий
		this.findById('swERPEW_EvnReanimatAction_disDate').setValue(EvnRAGridRowData['EvnReanimatAction_disDate']); //установка даты окончания события реанимационных мероприятий
		this.findById('swERPEW_EvnReanimatAction_disTime').setValue(EvnRAGridRowData['EvnReanimatAction_disTime']); //установка времени окончания события реанимационных мероприятий
		
		//запуск события выбора в комбо, чтобы загрузилась панель нужного мероприятия
		var index = swERPEW_RAType.getStore().find('ReanimatActionType_SysNick',EvnRAGridRowData['ReanimatActionType_SysNick']);//нахожу индекс в store комбо по SysNick из грида
		var rec = swERPEW_RAType.getStore().getAt(index);  // нахожу record по индексу
		swERPEW_RAType.fireEvent('select', swERPEW_RAType, rec,index + 1, 'EvnReanimatAction_view'); // запуск события в комбо, туда направляю index + 1 потому что так передаётся индекс при выборе с интерфейса, возможно это когда в комбо разрешено пустое состояние
		var MethodCombo = win.findById('swERPEW_RA_Method');   //combo метода мероприятия
		if (MethodCombo) {
			MethodCombo.setValue(EvnRAGridRowData['UslugaComplex_id']); //MethodCombo.setValue(EvnRAGridRowData['EvnReanimatAction_MethodCode']); //BOB - 19.02.2018		
		}
		
		//BOB - 03.11.2018
		this.findById('swERPEW_RA_MethodTxt').setValue(EvnRAGridRowData['EvnReanimatAction_MethodTxt']);  //BOB - 03.11.2018  метод - вариант пользователя
		this.findById('swERPEW_RA_NutritVol').setValue(EvnRAGridRowData['EvnReanimatAction_NutritVol']);  //BOB - 03.11.2018  объём питания
		this.findById('swERPEW_RA_NutritEnerg').setValue(EvnRAGridRowData['EvnReanimatAction_NutritEnerg']);  //BOB - 03.11.2018  энеогия питания
		win.findById('swERPEW_RA_MilkMix').setValue(EvnRAGridRowData['MilkMix_id']); //BOB - 15.04.2020  молочная смесь swERPEW_RA_MilkMix
		
		//способ платежа
		var combo = win.findById('swERPEW_RA_PayType');   //combo способ платежа
		if (combo) {
			combo.setValue(EvnRAGridRowData['PayType_id']); 		
		}

		//Показание наблюдения	
		//win.findById('swERPEW_RA_Observ_Value').setValue(EvnRAGridRowData['EvnReanimatAction_ObservValue']); 
		
		//Ктетеризация вен:
		win.findById('swERPEW_RA_CathetVeins').setValue(EvnRAGridRowData['ReanimatCathetVeins_id']); 
		win.findById('swERPEW_RA_CathetFix').setValue(EvnRAGridRowData['CathetFixType_id']); 
		win.findById('swERPEW_RA_cathetNabor').setValue(EvnRAGridRowData['EvnReanimatAction_CathetNaborName']); 
		
		//BOB - 03.11.2018
		//Если ИВЛ
		//Загрузка параметров ИВЛ
		if(EvnRAGridRowData['ReanimatActionType_SysNick'] == 'lung_ventilation'){	

			if (EvnRAGridRowData['EvnReanimatAction_id'] == 'New_GUID_Id'){ //новое мероприятие
				win.findById('swERPEW_RA_IVLParameter_id').setValue('');	//BOB - 04.07.2019

				Ext.select('input[id$="_Hid"]', true, 'swERPEW_RA_ParamIVL_Panel').each(function(el){
					var id = el.id; //выделяю параметр id из Ext.Element
					win.findById(id.replace("_Hid", "")).setValue(win.findById(id).getValue());
				});
				win.findById('swERPEW_RA_IVLRegim').fireEvent('expand', win.findById('swERPEW_RA_IVLRegim'));  //BOB - 29.02.2020		
				win.findById('swERPEW_RA_IVLRegim').setValue(win.findById('swERPEW_RA_IVLRegim_Hid').getValue());	//BOB - 29.02.2020	
				
				var index = win.findById('swERPEW_RA_IVLRegim').getStore().find('IVLRegim_id', win.findById('swERPEW_RA_IVLRegim').getValue());
				if (index > -1) {
					var SysNick = win.findById('swERPEW_RA_IVLRegim').getStore().getAt(index).data['IVLRegim_SysNick'];
					win.findById('swERPEW_RA_ParamIVL_Panel').ParamVisualisation(true, SysNick, win.findById('swERPEW_RA_IVLParameter_Apparat').getValue() ); //BOB - 29.02.2020	
				}
			} else {
				$.ajax({
					mode: "abort",
					type: "post",
					async: false,
					url: '/?c=EvnReanimatPeriod&m=GetParamIVL',
					data: {EvnReanimatAction_id: EvnRAGridRowData['EvnReanimatAction_id']},
					success: function(response) {
						var Data = Ext.util.JSON.decode(response);
						var Exists =  (Data.length == 0) ? false : true;

						//показ или скрытие полей параметров ИВЛ, использование Exists на всякий слукчай когда отсутствует зпись в БД этих параметров, например созданных до доработок
						win.findById('swERPEW_RA_ParamIVL_Panel').ParamVisualisation(Exists, Exists ? Data[0].IVLRegim_SysNick : '', Exists ? Data[0].IVLParameter_Apparat : '');	//BOB - 29.02.2020

						win.findById('swERPEW_RA_IVLParameter_id').setValue(Exists ? Data[0].IVLParameter_id : '');	//BOB - 04.07.2019
						win.findById('swERPEW_RA_IVLParameter_Apparat').setValue(Exists ? Data[0].IVLParameter_Apparat : '');	
						win.findById('swERPEW_RA_IVLRegim').fireEvent('expand', win.findById('swERPEW_RA_IVLRegim'));  //BOB - 29.02.2020				
						win.findById('swERPEW_RA_IVLRegim').setValue(Exists ? Data[0].IVLRegim_id : null);
						win.findById('swERPEW_RA_IVLParameter_TubeDiam').setValue(Data[0].IVLParameter_TubeDiam);
						win.findById('swERPEW_RA_IVLParameter_FiO2').setValue(Data[0].IVLParameter_FiO2);
						win.findById('swERPEW_RA_IVLParameter_PcentMinVol').setValue(Data[0].IVLParameter_PcentMinVol);
						win.findById('swERPEW_RA_IVLParameter_TwoASVMax').setValue(Data[0].IVLParameter_TwoASVMax);
						win.findById('swERPEW_RA_IVLParameter_FrequSet').setValue(Data[0].IVLParameter_FrequSet);
						win.findById('swERPEW_RA_IVLParameter_VolInsp').setValue(Data[0].IVLParameter_VolInsp);
						win.findById('swERPEW_RA_IVLParameter_PressInsp').setValue(Data[0].IVLParameter_PressInsp);
						win.findById('swERPEW_RA_IVLParameter_PressSupp').setValue(Data[0].IVLParameter_PressSupp);
						win.findById('swERPEW_RA_IVLParameter_FrequTotal').setValue(Data[0].IVLParameter_FrequTotal);
						win.findById('swERPEW_RA_IVLParameter_VolTe').setValue(Data[0].IVLParameter_VolTe);
						win.findById('swERPEW_RA_IVLParameter_VolE').setValue(Data[0].IVLParameter_VolE);
						var TinTet = Data[0].IVLParameter_TinTet.split(":");
						if (TinTet.length == 2){
							win.findById('swERPEW_RA_IVLParameter_Tin').setValue(TinTet[0]);
							win.findById('swERPEW_RA_IVLParameter_Tet').setValue(TinTet[1]);						
						} else {
							win.findById('swERPEW_RA_IVLParameter_Tin').setValue(0);
							win.findById('swERPEW_RA_IVLParameter_Tet').setValue(0);						
						}
						win.findById('swERPEW_RA_IVLParameter_VolTrig').setValue(Data[0].IVLParameter_VolTrig);
						win.findById('swERPEW_RA_IVLParameter_PressTrig').setValue(Data[0].IVLParameter_PressTrig);
						win.findById('swERPEW_RA_IVLParameter_PEEP').setValue(Data[0].IVLParameter_PEEP);	
						
						win.findById('swERPEW_RA_IVLParameter_VolTi').setValue(Data[0].IVLParameter_VolTi); //BOB - 29.02.2020	
						win.findById('swERPEW_RA_IVLParameter_Peak').setValue(Data[0].IVLParameter_Peak); //BOB - 29.02.2020	
						win.findById('swERPEW_RA_IVLParameter_MAP').setValue(Data[0].IVLParameter_MAP); //BOB - 29.02.2020	
						win.findById('swERPEW_RA_IVLParameter_Tins').setValue(Data[0].IVLParameter_Tins); //BOB - 29.02.2020	
						win.findById('swERPEW_RA_IVLParameter_FlowMax').setValue(Data[0].IVLParameter_FlowMax); //BOB - 29.02.2020	
						win.findById('swERPEW_RA_IVLParameter_FlowMin').setValue(Data[0].IVLParameter_FlowMin); //BOB - 29.02.2020	
						win.findById('swERPEW_RA_IVLParameter_deltaP').setValue(Data[0].IVLParameter_deltaP); //BOB - 29.02.2020	
						win.findById('swERPEW_RA_IVLParameter_Other').setValue(Data[0].IVLParameter_Other); //BOB - 29.02.2020	
					}, 
					error: function() {
						Ext.MessageBox.alert('Ошибка сохранения!', "При обработке запроса на сервере произошла ошибка!");
					} 
				});		
			}
		}
		
		// если использование датчика ВЧД или Инвазивная гемодинамика или Наблюдение сатурации гемоглобина
		// ЗАГРУЗКА СПИСКОВ ИЗМЕРЕНИЙ		
		else if (EvnRAGridRowData['ReanimatActionType_SysNick'].inlist(['endocranial_sensor','invasive_hemodynamics','observation_saturation'])){ 
			var rate_records = this.findById('swERPEW_ReanimatAction_Panel').rate_records;
			var rate_grid = this.findById('swERPEW_RA_Rate_Grid').getGrid(); 
			
			//установки размера таблицы измерений и видимости поля Rate_StepsToChange
			if (EvnRAGridRowData['ReanimatActionType_SysNick'].inlist(['endocranial_sensor'])){
				rate_grid.getColumnModel( ).setHidden( 4, false );
				rate_grid.setWidth(1299); 				
			} else {
				rate_grid.getColumnModel( ).setHidden( 4, true );
				rate_grid.setWidth(359); 				
			}
				
			if (rate_records[EvnRAGridRowData['EvnReanimatAction_id']]) { //если для текущего мероприятия уже существует пакет записей измерений
				var rate_pack = rate_records[EvnRAGridRowData['EvnReanimatAction_id']];
				rate_grid.store.removeAll();
				//загружаю новую служебную строку и тут же её удаляю иначе редактирование полей в гриде не работает
				var params = {
					EvnReanimatAction_id: EvnRAGridRowData['EvnReanimatAction_id'] == 'New_GUID_Id' ? null : EvnRAGridRowData['EvnReanimatAction_id'],
					ReanimatActionType_SysNick: 'new_rate'
				};
				this.findById('swERPEW_RA_Rate_Grid').loadData({
					globalFilters: params,
					callback: function(){
						rate_grid.store.removeAt(0);
						for(var i in rate_pack) {
							rate_grid.store.insert(rate_grid.store.getCount(), new Ext.data.Record(
								win.EvnReanimatAction_RateCopy(rate_pack[i])	
							));						
						}
						rate_grid.getSelectionModel().selectRow(rate_grid.store.getCount() - 1);
						rate_grid.getView().focusRow(rate_grid.store.getCount() - 1);
					}
				});
			} else { // если ещё нет пакета записей измерений
				if (EvnRAGridRowData['EvnReanimatAction_id'] == 'New_GUID_Id'){
					rate_grid.store.removeAll();						
				} else {
					//загружаю из БД
					var params = {
						EvnReanimatAction_id: EvnRAGridRowData['EvnReanimatAction_id'],
						ReanimatActionType_SysNick: EvnRAGridRowData['ReanimatActionType_SysNick']
					};
					this.findById('swERPEW_RA_Rate_Grid').loadData({
						globalFilters: params,
						callback: function(){
							//создаём в rate_records элемент относящийся к текущему мероприятию
							if (rate_grid.store.getCount() > 0){
								rate_records[EvnRAGridRowData['EvnReanimatAction_id']]  = {};
								for (var i in rate_grid.store.data.items) {
									if (rate_grid.store.data.items[i].data){
										rate_records[EvnRAGridRowData['EvnReanimatAction_id']][rate_grid.store.data.items[i].data['Rate_id']] = win.EvnReanimatAction_RateCopy(rate_grid.store.data.items[i].data);
									}
								}
							}
						}
					});
				}
			}
		}//BOB - 03.11.2018
		//BOB - 22.02.2019 - сердечно-сосудистая реанимация
		else if (EvnRAGridRowData['ReanimatActionType_SysNick'] == 'card_pulm'){ 

			if (EvnRAGridRowData['EvnReanimatAction_id'] == 'New_GUID_Id'){ //новое мероприятие		
				win.SetCardPulmData(win.findById('swERPEW_RA_CardPulm_Panel').CardPulm_Data,true);
			} else {
				$.ajax({
					mode: "abort",
					type: "post",
					async: false,
					url: '/?c=EvnReanimatPeriod&m=GetCardPulm',
					data: {EvnReanimatAction_id: EvnRAGridRowData['EvnReanimatAction_id']},
					success: function(response) {
						var Data = Ext.util.JSON.decode(response);
						win.SetCardPulmData(Data[0],false);
					}, 
					error: function() {
						Ext.MessageBox.alert('Ошибка сохранения!', "При обработке запроса на сервере произошла ошибка!");
					} 
				});		
				
			}	
		}


		//BOB - 05.03.2020
		//ЗАГРУЗКА Лекарственных Средств
		if (EvnRAGridRowData['ReanimatActionType_SysNick'].inlist(['vazopressors','epidural_analgesia','antifungal_therapy','catheterization_veins','invasive_hemodynamics','sedation'])){
			var ReanimDrug_records = this.findById('swERPEW_ReanimatAction_Panel').ReanimDrug;
			var ReanimDrug = {};
			if (ReanimDrug_records[EvnRAGridRowData['EvnReanimatAction_id']]) { //если для текущего мероприятия уже существует пакет записей ЛС
				ReanimDrug = ReanimDrug_records[EvnRAGridRowData['EvnReanimatAction_id']];
				win.ReanimDrug_Build(ReanimDrug, EvnRAGridRowData['ReanimatActionType_SysNick'], 'EvnReanimatAction_view');
			} else {
				//использую именно такой ajax - он даёт возможность апсинхронно работать - это упрощает код по управлению активностью элементов
				$.ajax({
					mode: "abort",
					type: "post",
					async: false,
					url: '/?c=EvnReanimatPeriod&m=GetReanimDrug',
					data: {EvnReanimatAction_id: EvnRAGridRowData['EvnReanimatAction_id']},
					error: function() {
						Ext.MessageBox.alert('Ошибка сохранения!', "При обработке запроса на сервере произошла ошибка!");
					}, 
					success: function(response)
					{
						var response_obj = Ext.util.JSON.decode(response); 
						ReanimDrug_records[EvnRAGridRowData['EvnReanimatAction_id']] = response_obj;
						win.ReanimDrug_Build(response_obj, EvnRAGridRowData['ReanimatActionType_SysNick'], 'EvnReanimatAction_view_Ajax');
					}
				});
			}
		}
		//BOB - 05.03.2020

		//установка активности/неактивности на область ввода реанимационных мероприятий
		// создание выборки элементов 'input', внутри панели с id 'swERPEW_GeneralReanimatActionPanel', возвращает с типом  Ext.Element
		//по массиву выбранных элементов
		Ext.select('input', true, 'swERPEW_GeneralReanimatActionPanel').each(function(el){
			var id = el.id; //выделяю параметр id из Ext.Element
			var object = win.findById(id);	//ищу в окне объект ExtJS
			if(object){ // если нахожу, то 
				object.setDisabled(EvnRAGridRowData['EvnReanimatAction_id'] == 'New_GUID_Id' ? false : true); // делаю Disabled /Enabled
			}
		});

		//BOB - 04.07.2019 - убрал управление кнопками мероприятий
		this.EvnReanimatAction_ButtonManag(EvnRAGridRowData['EvnReanimatAction_id'] != 'New_GUID_Id', EvnRAGridRowData, 'EvnReanimatAction_view'); //BOB - 04.07.2019
		
		//BOB - 07.11.2017
		//для типов мероприятий, где есть дата завершения, и при условии, что дата завершения пустая, кнопку сохранения и дата/время завершения надо оставить активными, чтобы можно было ввести их позже и если нет новой записи
		if((EvnRAGridRowData['ReanimatActionType_SysNick'].inlist(['lung_ventilation','nutrition','hemodialysis','endocranial_sensor','invasive_hemodynamics','observation_saturation']))&&
				((EvnRAGridRowData['EvnReanimatAction_disDate'] == '') || (EvnRAGridRowData['EvnReanimatAction_disTime'] == '')) && (this.findById('swERPEW_ReanimatAction_Grid').store.find('EvnReanimatAction_id', 'New_GUID_Id')==-1)) {
			//win.findById('swERPEW_EvnReanimatActionButtonSave').enable(); // кнопку сохранения делаю активной  //BOB - 04.07.2019 - закомментарил
			win.findById('swERPEW_EvnReanimatAction_disDate').enable(); // дату окончания делаю активной
			win.findById('swERPEW_EvnReanimatAction_disTime').enable(); // время окончания делаю активной								
		}
	},

	//рисование блока Лекарственных средств в мероприятиях
	ReanimDrug_Build: function(ReanimDrug, SysNick, from){
		win = this;
		var EvnScalesGridRow = win.findById('swERPEW_ReanimatAction_Grid').getSelectionModel().getSelected();  //выбранная строка в гриде событий шкал

		var Datas =  [];
		for (var i in win.ERPEW_NSI.ReanimDrugType) { // цикл по значениям параметра
			Datas[i]= [ win.ERPEW_NSI.ReanimDrugType[i].ReanimDrugType_id,
						win.ERPEW_NSI.ReanimDrugType[i].ReanimDrugType_Name ];
		};
		var top_panel = true;

		win.findById('swERPEW_RA_Drug_Panel').removeAll();
		for(var i in ReanimDrug){ 
			if (ReanimDrug[i].ReanimDrug_Status != 3){
				this.findById('swERPEW_RA_Drug_Panel').add(
					{
						layout:'column',
						id: 'swERPEW_RA_Drug_' + i + '_Panel',
						style: 'border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff;border-right: 1px solid  #ffffff; padding: 2px; ',
						border:true,
						top_panel: top_panel,
						items:[	

							//combo - Медикамент
							{							
								layout:'form',
								style:'margin-top: 4px;',
								labelWidth:80,
								border:false,
								items:[	
									{
										id: 'swERPEW_RA_DrugNames_'+ i,
										hiddenName: 'RA_DrugNames_'+ i,									
										xtype: 'combo',
										fieldLabel: 'Медикамент',
										labelSeparator: '',
										allowBlank: false,
										disabled: false,
										mode:'local',
										width: 240,
										triggerAction : 'all',
										displayField:'ReanimDrugType_Name',
										valueField:'ReanimDrugType_id',
										editable: false,
										value: ReanimDrug[i].ReanimDrugType_id, 
										tpl: '<tpl for="."><div class="x-combo-list-item">'+
											'{ReanimDrugType_Name} '+ '&nbsp;' +
											'</div></tpl>' ,
										store:new Ext.data.SimpleStore(  {
											fields: [{name:'ReanimDrugType_id',type:'string'},
													{name:'ReanimDrugType_Name',type:'string'}],
											data: 	Datas	 
										}),
										index: i,
										top_panel: top_panel,
										listeners: {
											'change': function(combo, newValue, oldValue) { //BOB - 12.07.2019	
												if (newValue == '') {
													ReanimDrug[combo.index].ReanimDrugType_id = null;
													if (combo.top_panel) {
														EvnScalesGridRow.data['ReanimDrugType_id'] = null;
														EvnScalesGridRow.data['EvnReanimatAction_Medicoment'] = null;																		
														EvnScalesGridRow.commit();
													}
												}
											}, //BOB - 12.07.2019	
											'select': function(combo, record, index) {
												ReanimDrug[combo.index].ReanimDrugType_id = record.data.ReanimDrugType_id;
												if (combo.top_panel) {
													EvnScalesGridRow.data['ReanimDrugType_id'] = record.data.ReanimDrugType_id;
													EvnScalesGridRow.data['EvnReanimatAction_Medicoment'] = record.data.ReanimDrugType_Name;																		
													EvnScalesGridRow.commit();
												}
											},
											'expand': function	(combo)	{
												var filterReanimatDrug = win.findById('swERPEW_ReanimatAction_Panel').RA_Drug[SysNick];
												combo.getStore().clearFilter();
												if (filterReanimatDrug){
													combo.getStore().filterBy(function (rec) {
														return rec.get('ReanimDrugType_id').inlist(filterReanimatDrug);
													});
												}
											}
										}
									}																																																																												
								]
							},
							//дозировка
							{							
								layout:'form',
								style:'margin-top: 4px;',
								labelWidth:80,
								border:false,
								items:[	
									new Ext.form.NumberField({
										value: ReanimDrug[i].ReanimDrug_Dose,
										id: 'swERPEW_RA_Drug_Dose_'+ i,
										fieldLabel:'Дозировка',
										labelSeparator: '',
										enableKeyEvents: true,
										width: 60,
										index: i,
										top_panel: top_panel,
										listeners:{
											'keyup':function (obj, e) {
												ReanimDrug[obj.index].ReanimDrug_Dose = obj.getValue();
												if (obj.top_panel) {
													EvnScalesGridRow.data['EvnReanimatAction_DrugDose'] = obj.getValue();
													EvnScalesGridRow.commit();
												}
											}
										}
									})
								]
							},
							//Единицы измерения    //BOB - 23.04.2018
							{							
								layout:'form',
								style:'margin-top: 4px;',
								labelWidth:5,
								border:false,
								items:[	
									{
										xtype: 'textfield',
										id: 'swERPEW_RA_Drug_Unit_'+ i,
										value: ReanimDrug[i].ReanimDrug_Unit,
										labelSeparator: '',
										width: 80,
										enableKeyEvents: true,
										index: i,
										top_panel: top_panel,
										listeners:{
											'keyup':function (obj, e) {
												ReanimDrug[obj.index].ReanimDrug_Unit = obj.getValue();
												if (obj.top_panel) {
													EvnScalesGridRow.data['EvnReanimatAction_DrugUnit'] = obj.getValue();
													EvnScalesGridRow.commit();
												}
											}
										}
									}
								]
							},
							new Ext.Button({
								id: 'swERPEW_RA_Drug_Del_Button_'+ i,
								iconCls: 'delete16',
								text: 'Удалить',
								index: i,
								hidden: top_panel,
								top_panel: top_panel,
								style: 'margin-top: 4px;  margin-left: 500px; margin-bottom: 0px;',
								handler: function(b,e)
								{
									if(ReanimDrug[b.index].ReanimDrug_Status == 0)
										delete ReanimDrug[b.index];
									else
										ReanimDrug[b.index].ReanimDrug_Status = 3;
									win.ReanimDrug_Build(ReanimDrug, SysNick, 'swERPEW_RA_Drug_Del_Button_'+ i);
								}.createDelegate(this)
							})
						]
					}
				);
			}
			top_panel = false;
		}

		this.findById('swERPEW_RA_Drug_Panel').add(
			new Ext.Button({
				id: 'swERPEW_RA_Drug_Add_Button',
				iconCls: 'add16',
				text: 'Добавить',
				handler: function(b,e)
				{
					var EvnReanimatAction_id = EvnScalesGridRow.data.EvnReanimatAction_id;

					var ReanimDrug_id = 0;
					var ReanimDrug_Unit ='';
					for (var i in ReanimDrug){
						if (parseInt(i) < parseInt(ReanimDrug_id)) ReanimDrug_id = i;
						ReanimDrug_Unit = ReanimDrug[i].ReanimDrug_Unit;
					}
					ReanimDrug_id -= 1;
			
					ReanimDrug[ReanimDrug_id] = {
						ReanimDrug_id: ReanimDrug_id,
						EvnReanimatAction_id: EvnReanimatAction_id,
						ReanimDrugType_id: null,
						EvnDrug_id: null,
						ReanimDrug_Dose: 0,
						ReanimDrug_Unit: ReanimDrug_Unit,
						ReanimDrug_Status: 0
					}
					win.ReanimDrug_Build(ReanimDrug, SysNick, 'swERPEW_RA_Drug_Add_Button');
				}
			})
		);
		win.findById('swERPEW_RA_Drug_Panel').doLayout();
	},




	//отображение сведений сердечно-лёгочной реанимации
	SetCardPulmData: function(CardPulmData, RadioEventExec) {
		var win = this;
		win.findById('swERPEW_RA_CardPulm_Panel').RadioEventExec = RadioEventExec;
		//console.log('BOB_RadioEventExec0=',win.findById('swERPEW_RA_CardPulm_Panel').RadioEventExec);

		this.findById('swERPEW_RA_CardPulm_id').setValue((CardPulmData.ReanimatCardPulm_id == null) ? '' : CardPulmData.ReanimatCardPulm_id); //BOB - 04.07.2019

		win.findById('swERPEW_RA_ClinicalDeath_Date').setValue(CardPulmData.ReanimatCardPulm_ClinicalDeathDate);		//дата клинической смерти			
		win.findById('swERPEW_RA_ClinicalDeath_Time').setValue(CardPulmData.ReanimatCardPulm_ClinicalDeathTime);		//время клинической смерти				
		var RadioI = CardPulmData.ReanimatCardPulm_IsPupilDilat ? CardPulmData.ReanimatCardPulm_IsPupilDilat : 0;       // Зрачки расширены / нерасширены
		for (var i = 0; i < this.findById('swERPEW_RA_IsPupilDilat').items.items.length; i++  )
			this.findById('swERPEW_RA_IsPupilDilat').items.items[i].setValue(parseInt(RadioI) == i + 1 ? true : false);		
		RadioI = CardPulmData.ReanimatCardPulm_IsCardMonitor ? CardPulmData.ReanimatCardPulm_IsCardMonitor : 0;       // Кардиомониторирование проводится / непроводится
		for (var i = 0; i < this.findById('swERPEW_RA_IsCardMonitor').items.items.length; i++  )
			this.findById('swERPEW_RA_IsCardMonitor').items.items[i].setValue(parseInt(RadioI) == i + 1 ? true : false);		
		RadioI = CardPulmData.ReanimatCardPulm_StopCardActType ? CardPulmData.ReanimatCardPulm_StopCardActType : 0;       // Вид прекращения сердечной деятельности
		for (var i = 0; i < this.findById('swERPEW_RA_StopCardActType').items.items.length; i++  )
			this.findById('swERPEW_RA_StopCardActType').items.items[i].setValue(parseInt(RadioI) == i + 1 ? true : false);					
		this.findById('swERPEW_RA_IVLRegim2').setValue(CardPulmData.IVLRegim_id);											//режим ИВЛ
		this.findById('swERPEW_RA_CardPulm_FiO2').setValue(CardPulmData.ReanimatCardPulm_FiO2);								//концентрация кислорода
		RadioI = CardPulmData.ReanimatCardPulm_IsCardTonics ? CardPulmData.ReanimatCardPulm_IsCardTonics : 0;       // Введение кардиотоников проводится / непроводится
		for (var i = 0; i < this.findById('swERPEW_RA_IsCardTonics').items.items.length; i++  )
			this.findById('swERPEW_RA_IsCardTonics').items.items[i].setValue(parseInt(RadioI) == i + 1 ? true : false);					
		this.findById('swERPEW_RA_CardTonicDose').setValue(CardPulmData.ReanimatCardPulm_CardTonicDose);		//Доза кардиотоника дофамина		
		RadioI = CardPulmData.ReanimatCardPulm_CathetVein ? CardPulmData.ReanimatCardPulm_CathetVein : 0;       // Катетеризация магистральной вены
		for (var i = 0; i < this.findById('swERPEW_RA_CathetVein').items.items.length; i++  )
			this.findById('swERPEW_RA_CathetVein').items.items[i].setValue(parseInt(RadioI) == i + 1 ? true : false);					
		this.findById('swERPEW_RA_TrachIntub').setValue(CardPulmData.ReanimatCardPulm_TrachIntub);	         	//Интубация трахеи трубкой №
		RadioI = CardPulmData.ReanimatCardPulm_Auscultatory ? CardPulmData.ReanimatCardPulm_Auscultatory : 0;       // Аускультативная картина
		for (var i = 0; i < this.findById('swERPEW_RA_Auscultatory').items.items.length; i++  )
			this.findById('swERPEW_RA_Auscultatory').items.items[i].setValue(parseInt(RadioI) == i + 1 ? true : false);					
		this.findById('swERPEW_RA_AuscultatoryTxt').setValue(CardPulmData.ReanimatCardPulm_AuscultatoryTxt);	         	//Аускультативная картина, вариант пользователя
		this.findById('swERPEW_RA_CardMassage').setValue(CardPulmData.ReanimatCardPulm_CardMassage);						//Непрямой массаж сердца, частота
		this.findById('swERPEW_RA_DefibrilCount').setValue(CardPulmData.ReanimatCardPulm_DefibrilCount);					//Дефибрилляция, количество
		this.findById('swERPEW_RA_DefibrilMin').setValue(CardPulmData.ReanimatCardPulm_DefibrilMin);						//Дефибрилляция, минимальная мощность
		this.findById('swERPEW_RA_DefibrilMax').setValue(CardPulmData.ReanimatCardPulm_DefibrilMax);						//Дефибрилляция, максимальная мощность
		this.findById('swERPEW_RA_CardPulm_Drug_1').setValue(CardPulmData.ReanimDrugType_id);								//Внутривенно введено 1, медикамент
		this.findById('swERPEW_RA_CardPulm_Drug_Dose_1').setValue(CardPulmData.ReanimatCardPulm_DrugDose);					//Внутривенно введено 1, доза
		RadioI = CardPulmData.ReanimatCardPulm_DrugSposob ? CardPulmData.ReanimatCardPulm_DrugSposob : 0;					// Внутривенно введено 1, способ
		for (var i = 0; i < this.findById('swERPEW_RA_DrugSposob_1').items.items.length; i++  )
			this.findById('swERPEW_RA_DrugSposob_1').items.items[i].setValue(parseInt(RadioI) == i + 1 ? true : false);					
		this.findById('swERPEW_RA_CardPulm_Drug_2').setValue(CardPulmData.ReanimDrugType_did);								//Внутривенно введено 2, медикамент
		this.findById('swERPEW_RA_CardPulm_Drug_Dose_2').setValue(CardPulmData.ReanimatCardPulm_dDrugDose);					//Внутривенно введено 2, доза
		RadioI = CardPulmData.ReanimatCardPulm_dDrugSposob ? CardPulmData.ReanimatCardPulm_dDrugSposob : 0;					// Внутривенно введено 2, способ
		for (var i = 0; i < this.findById('swERPEW_RA_DrugSposob_2').items.items.length; i++  )
			this.findById('swERPEW_RA_DrugSposob_2').items.items[i].setValue(parseInt(RadioI) == i + 1 ? true : false);					
		this.findById('swERPEW_RA_DrugTxt').setValue(CardPulmData.ReanimatCardPulm_DrugTxt);								//Внутривенно введено, вариант пользователя
		RadioI = CardPulmData.ReanimatCardPulm_IsEffective ? CardPulmData.ReanimatCardPulm_IsEffective : 0;					// Реанимационные мероприятия эффективны / неэффективны
		for (var i = 0; i < this.findById('swERPEW_RA_IsEffective').items.items.length; i++  )
			this.findById('swERPEW_RA_IsEffective').items.items[i].setValue(parseInt(RadioI) == i + 1 ? true : false);					
		if (CardPulmData.ReanimatCardPulm_Time) {																			//Время проведения реанимационных мероприятий			
			var minuts = CardPulmData.ReanimatCardPulm_Time % 60;
			var hour = (CardPulmData.ReanimatCardPulm_Time - minuts) / 60;
			win.findById('swERPEW_RA_CardPulm_Time').setValue((100+hour).toString( ).substr(1, 2)+ ':' +  (100+minuts).toString( ).substr(1, 2));
		} else win.findById('swERPEW_RA_CardPulm_Time').setValue('');//.setValue('00:00');
		win.findById('swERPEW_RA_BiologDeath_Date').setValue(CardPulmData.ReanimatCardPulm_BiologDeathDate);		//дата биологической смерти			
		win.findById('swERPEW_RA_BiologDeath_Time').setValue(CardPulmData.ReanimatCardPulm_BiologDeathTime);		//время биологической смерти				
		win.findById('swERPEW_RA_CardPulm_DoctorTxt').setValue(CardPulmData.ReanimatCardPulm_DoctorTxt);		//Врач				

	},

	//добавление реанимационного мероприятия
	EvnReanimatAction_Add: function() {
		var win = this;
		this.findById('swERPEW_ReanimatActionType').setValue(null); // устанавливаю в комбо тип реанимационных мероприятий
		var curDate = getValidDT(getGlobalOptions().date, ''); // считываю из глобальных параметров текущую дату
		
		this.findById('swERPEW_EvnReanimatAction_setDate').setValue(curDate);// в дату события реанимационных мероприятий - текущую дату
		this.findById('swERPEW_EvnReanimatAction_setTime').setValue(''); // во время события реанимационных мероприятий - пустоту
		this.findById('swERPEW_EvnReanimatAction_disDate').setValue('');// в дату окончания реанимационных мероприятий - пустоту
		this.findById('swERPEW_EvnReanimatAction_disTime').setValue(''); // во время окончания реанимационных мероприятий - пустоту
		

		//параметры ИВЛ
		win.findById('swERPEW_RA_IVLParameter_id').setValue('');	//BOB - 04.07.2019
		win.findById('swERPEW_RA_IVLParameter_Apparat').setValue('');					
		win.findById('swERPEW_RA_IVLRegim').setValue(null);
		win.findById('swERPEW_RA_IVLParameter_TubeDiam').setValue(0);
		win.findById('swERPEW_RA_IVLParameter_FiO2').setValue(0);
		win.findById('swERPEW_RA_IVLParameter_PcentMinVol').setValue(0);
		win.findById('swERPEW_RA_IVLParameter_TwoASVMax').setValue(0);
		win.findById('swERPEW_RA_IVLParameter_FrequSet').setValue(0);
		win.findById('swERPEW_RA_IVLParameter_VolInsp').setValue(0);
		win.findById('swERPEW_RA_IVLParameter_PressInsp').setValue(0);
		win.findById('swERPEW_RA_IVLParameter_PressSupp').setValue(0);
		win.findById('swERPEW_RA_IVLParameter_FrequTotal').setValue(0);
		win.findById('swERPEW_RA_IVLParameter_VolTe').setValue(0);
		win.findById('swERPEW_RA_IVLParameter_VolE').setValue(0);
		win.findById('swERPEW_RA_IVLParameter_Tin').setValue(0);
		win.findById('swERPEW_RA_IVLParameter_Tet').setValue(0);						
		win.findById('swERPEW_RA_IVLParameter_VolTrig').setValue(0);
		win.findById('swERPEW_RA_IVLParameter_PressTrig').setValue(0);
		win.findById('swERPEW_RA_IVLParameter_PEEP').setValue(0);
		win.findById('swERPEW_RA_IVLParameter_VolTi').setValue(0); //BOB - 29.02.2020	
		win.findById('swERPEW_RA_IVLParameter_Peak').setValue(0); //BOB - 29.02.2020	
		win.findById('swERPEW_RA_IVLParameter_MAP').setValue(0); //BOB - 29.02.2020	
		win.findById('swERPEW_RA_IVLParameter_Tins').setValue(0); //BOB - 29.02.2020	
		win.findById('swERPEW_RA_IVLParameter_FlowMax').setValue(0); //BOB - 29.02.2020	
		win.findById('swERPEW_RA_IVLParameter_FlowMin').setValue(0); //BOB - 29.02.2020	
		win.findById('swERPEW_RA_IVLParameter_deltaP').setValue(0); //BOB - 29.02.2020	
		win.findById('swERPEW_RA_IVLParameter_Other').setValue(''); //BOB - 29.02.2020	

		//BOB - 03.11.2018 - параметры ИВЛ
		//BOB - 22.02.2019 - параметры сердечно-лёгочной реанимации
		win.findById('swERPEW_RA_CardPulm_Panel').CardPulm_Data = {
			ReanimatCardPulm_id: null, //BOB - 04.07.2019
			ReanimatCardPulm_DoctorTxt: this.MedPersonal_FIO,
			ReanimatCardPulm_IsPupilDilat: 1,
			ReanimatCardPulm_IsCardMonitor: 1,
			ReanimatCardPulm_StopCardActType: 1,
			ReanimatCardPulm_IsCardTonics: 1,
			ReanimatCardPulm_CathetVein: 1,
			ReanimatCardPulm_Auscultatory: 1,
			ReanimatCardPulm_IsEffective: 1,
			ReanimatCardPulm_Time: 0
		};
		this.SetCardPulmData(win.findById('swERPEW_RA_CardPulm_Panel').CardPulm_Data, true);

		//BOB - 05.03.2020  создание объекта ЛС
		this.findById('swERPEW_ReanimatAction_Panel').ReanimDrug['New_GUID_Id'] = {
			"-1" : {
				ReanimDrug_id: -1,
				EvnReanimatAction_id: null,
				ReanimDrugType_id: null,
				EvnDrug_id: null,
				ReanimDrug_Dose: 0,
				ReanimDrug_Unit: "",
				ReanimDrug_Status: 0
			}
		};


		//установка активности на область мероприятий и Очистка значений реквизитов
		Ext.select('input, label', true, 'swERPEW_GeneralReanimatActionPanel').each(function(el){
			var id = el.id; //выделяю параметр id из Ext.Element
			var object = win.findById(id);	//ищу в окне объект ExtJS
			if(object){ // если нахожу, то 
			//	console.log('BOB_Object=',object);  
				object.setDisabled(false); // делаю Enabled
				if (object.xtype == 'combo') {
					if(object.id.indexOf('PayType') > -1)
						object.setValue(9);
					else
						object.setValue(null);
				}					
			}		
		});

		//добавление строки в грид		
		var Grid = this.findById('swERPEW_ReanimatAction_Grid');
		Grid.store.insert(0, new Ext.data.Record({
			EvnReanimatAction_id:  'New_GUID_Id',
			EvnReanimatAction_pid:  this.EvnReanimatPeriod_id,
			Person_id:  this.Person_id,
			PersonEvn_id:  this.PersonEvn_id,
			Server_id: this.Server_id,
			EvnReanimatAction_setDate:  curDate,
			EvnReanimatAction_setTime: '',
			EvnReanimatAction_disDate: '',
			EvnReanimatAction_disTime: '',
			ReanimatActionType_id:  null, // 0,  //BOB - 21.03.2018
			ReanimatActionType_SysNick:  '',
			ReanimatActionType_Name:  '',
			UslugaComplex_id: null, // 0,  //BOB - 21.03.2018
			EvnUsluga_id:  null, // 0,  //BOB - 21.03.2018
			ReanimDrugType_id:  null, // 0,  //BOB - 21.03.2018
			EvnReanimatAction_DrugDose:  0,
			EvnDrug_id:  null, // 0,  //BOB - 21.03.2018
			EvnReanimatAction_MethodCode:  '',
			EvnReanimatAction_MethodName:  '',
			EvnReanimatAction_Medicoment:	'',
			PayType_id: null,
			//EvnReanimatAction_ObservValue: null,
			ReanimatCathetVeins_id: null, //'', //BOB - 03.11.2018
			CathetFixType_id: '',
			EvnReanimatAction_CathetNaborName: '',
			EvnReanimatAction_DrugUnit: 'мг',           //BOB - 23.04.2018
			EvnReanimatAction_MethodTxt: '',  //BOB - 03.11.2018  метод - вариант пользователя
			EvnReanimatAction_NutritVol:  0,  //BOB - 03.11.2018  объём питания
			EvnReanimatAction_NutritEnerg:  0,  //BOB - 03.11.2018  энеогия питания
			MilkMix_id: null					//BOB - 15.04.2020 молочная смесь
		}));




		this.findById('swERPEW_EvnReanimatAction_disDate_Pnl').setVisible(false);// делаю невидимыми время окончания
		this.findById('swERPEW_EvnReanimatAction_disTime_Pnl').setVisible(false);// делаю невидимыми дату окончания
		this.findById('swERPEW_RA_Rate_Grid').getGrid().store.removeAll();  //BOB - 03.11.2018 очистка грида измерений
		
		//BOB - 04.07.2019 - убрал управление кнопками мероприятий
		Grid.getSelectionModel().selectRow(0); 	//установка выбранности на первой строке грида 
		this.EvnReanimatAction_ButtonManag(false, null,'EvnReanimatAction_Add');  //BOB - 04.07.2019
	},

	//редактирование реанимационного мероприятия  //BOB - 04.07.2019
	EvnReanimatAction_Edit: function() {

		var win = this;

		var Grid = this.findById('swERPEW_ReanimatAction_Grid');
		var EvnRAGridRowData = Grid.getSelectionModel().getSelected().data;  //выбранная строка в гриде событий регулярного наблюдения состояния

		if (Ext.isEmpty(EvnRAGridRowData['EvnReanimatAction_id'])) {
			Ext.MessageBox.alert('Внимание!', 'Не выбрана строка в списке мероприятий! ');
			return false;
		}

		//BOB - 04.07.2019
		if (EvnRAGridRowData['ReanimatActionType_SysNick'] == 'card_pulm') {
			this.findById('swERPEW_RA_CardPulm_Panel').CardPulm_Data = {
				ReanimatCardPulm_id: (this.findById('swERPEW_RA_CardPulm_id').getValue() == '') ? null : this.findById('swERPEW_RA_CardPulm_id').getValue(),
				IVLRegim_id: this.findById('swERPEW_RA_IVLRegim2').getValue(),
				ReanimDrugType_did: this.findById('swERPEW_RA_CardPulm_Drug_2').getValue(),
				ReanimDrugType_id: this.findById('swERPEW_RA_CardPulm_Drug_1').getValue(),
				ReanimatCardPulm_AuscultatoryTxt: this.findById('swERPEW_RA_AuscultatoryTxt').getValue(),
				ReanimatCardPulm_BiologDeathDate: ((this.findById('swERPEW_RA_BiologDeath_Date').getValue() == '') || (this.findById('swERPEW_RA_BiologDeath_Date').getValue() == null)) ? null : this.findById('swERPEW_RA_BiologDeath_Date').getValue(),
				ReanimatCardPulm_BiologDeathTime: this.findById('swERPEW_RA_BiologDeath_Time').getValue(),
				ReanimatCardPulm_CardMassage: ((this.findById('swERPEW_RA_CardMassage').getValue() == '') || (this.findById('swERPEW_RA_CardMassage').getValue() == null)) ? null : this.findById('swERPEW_RA_CardMassage').getValue(),
				ReanimatCardPulm_CardTonicDose: ((this.findById('swERPEW_RA_CardTonicDose').getValue() == '') || (this.findById('swERPEW_RA_CardTonicDose').getValue() == null)) ? null : this.findById('swERPEW_RA_CardTonicDose').getValue(),
				ReanimatCardPulm_ClinicalDeathDate: this.findById('swERPEW_RA_ClinicalDeath_Date').getValue(),
				ReanimatCardPulm_ClinicalDeathTime: this.findById('swERPEW_RA_ClinicalDeath_Time').getValue(),
				ReanimatCardPulm_DefibrilCount: ((this.findById('swERPEW_RA_DefibrilCount').getValue() == '') || (this.findById('swERPEW_RA_DefibrilCount').getValue() == null)) ? null : this.findById('swERPEW_RA_DefibrilCount').getValue(),
				ReanimatCardPulm_DefibrilMax: ((this.findById('swERPEW_RA_DefibrilMax').getValue() == '') || (this.findById('swERPEW_RA_DefibrilMax').getValue() == null)) ? null : this.findById('swERPEW_RA_DefibrilMax').getValue(),
				ReanimatCardPulm_DefibrilMin: ((this.findById('swERPEW_RA_DefibrilMin').getValue() == '') || (this.findById('swERPEW_RA_DefibrilMin').getValue() == null)) ? null : this.findById('swERPEW_RA_DefibrilMin').getValue(),
				ReanimatCardPulm_DoctorTxt: this.findById('swERPEW_RA_CardPulm_DoctorTxt').getValue(),
				ReanimatCardPulm_DrugDose: ((this.findById('swERPEW_RA_CardPulm_Drug_Dose_1').getValue() == '') || (this.findById('swERPEW_RA_CardPulm_Drug_Dose_1').getValue() == null)) ? null : this.findById('swERPEW_RA_CardPulm_Drug_Dose_1').getValue(),
				ReanimatCardPulm_DrugTxt: this.findById('swERPEW_RA_DrugTxt').getValue(),
				ReanimatCardPulm_FiO2: ((this.findById('swERPEW_RA_CardPulm_FiO2').getValue()== '') || (this.findById('swERPEW_RA_CardPulm_FiO2').getValue() == null)) ? null : this.findById('swERPEW_RA_CardPulm_FiO2').getValue(),
				ReanimatCardPulm_TrachIntub: ((this.findById('swERPEW_RA_TrachIntub').getValue() == '') || (this.findById('swERPEW_RA_TrachIntub').getValue() == null)) ? null : this.findById('swERPEW_RA_TrachIntub').getValue(),
				ReanimatCardPulm_dDrugDose: ((this.findById('swERPEW_RA_CardPulm_Drug_Dose_2').getValue() == '')  || (this.findById('swERPEW_RA_CardPulm_Drug_Dose_2').getValue() == null)) ? null : this.findById('swERPEW_RA_CardPulm_Drug_Dose_2').getValue()
			};

			var CardPulm_Data = win.findById('swERPEW_RA_CardPulm_Panel').CardPulm_Data;
			CardPulm_Data.ReanimatCardPulm_Auscultatory = this.findById('swERPEW_RA_Auscultatory').items.items.find(function(c) {return(c.checked == true)}).inputValue;
			CardPulm_Data.ReanimatCardPulm_CathetVein = this.findById('swERPEW_RA_CathetVein').items.items.find(function(c) {return(c.checked == true)}).inputValue;
			if (this.findById('swERPEW_RA_DrugSposob_1').items.items.find(function(c) {return(c.checked == true)}))
				CardPulm_Data.ReanimatCardPulm_DrugSposob = this.findById('swERPEW_RA_DrugSposob_1').items.items.find(function(c) {return(c.checked == true)}).inputValue;
			else
				CardPulm_Data.ReanimatCardPulm_DrugSposob = null;
			if (this.findById('swERPEW_RA_DrugSposob_2').items.items.find(function(c) {return(c.checked == true)}))
				CardPulm_Data.ReanimatCardPulm_dDrugSposob = this.findById('swERPEW_RA_DrugSposob_2').items.items.find(function(c) {return(c.checked == true)}).inputValue;
			else
				CardPulm_Data.ReanimatCardPulm_dDrugSposob = null;
			CardPulm_Data.ReanimatCardPulm_IsCardMonitor = this.findById('swERPEW_RA_IsCardMonitor').items.items.find(function(c) {return(c.checked == true)}).inputValue;
			CardPulm_Data.ReanimatCardPulm_IsCardTonics = this.findById('swERPEW_RA_IsCardTonics').items.items.find(function(c) {return(c.checked == true)}).inputValue;
			CardPulm_Data.ReanimatCardPulm_IsEffective = this.findById('swERPEW_RA_IsEffective').items.items.find(function(c) {return(c.checked == true)}).inputValue;
			CardPulm_Data.ReanimatCardPulm_IsPupilDilat = this.findById('swERPEW_RA_IsPupilDilat').items.items.find(function(c) {return(c.checked == true)}).inputValue;
			CardPulm_Data.ReanimatCardPulm_StopCardActType = this.findById('swERPEW_RA_StopCardActType').items.items.find(function(c) {return(c.checked == true)}).inputValue;

			var newValue = this.findById('swERPEW_RA_CardPulm_Time').getValue().replace(/_/g,'0');
			var a_newValue = newValue.split(':');
			var i_newValue = parseInt(a_newValue[0], 10) * 60 + parseInt(a_newValue[1], 10);
			CardPulm_Data.ReanimatCardPulm_Time = i_newValue;

			this.findById('swERPEW_RA_CardPulm_Panel').RadioEventExec = true;

			console.log('BOB_CardPulm_Data=',CardPulm_Data);
		};
		//BOB - 04.07.2019





		//установка активности на область мероприятий
		Ext.select('input, label', true, 'swERPEW_GeneralReanimatActionPanel').each(function(el){
			var id = el.id; //выделяю параметр id из Ext.Element
			var object = win.findById(id);	//ищу в окне объект ExtJS
			if(object){ // если нахожу, то
			//	console.log('BOB_Object=',object);
				object.setDisabled(false); // делаю Enabled
			}
		});
		//тип мероприятия остаётся недоступным - менять нельзя
		this.findById('swERPEW_ReanimatActionType').setDisabled(true);

		//BOB - 04.07.2019 - убрал управление кнопками мероприятий
		this.EvnReanimatAction_ButtonManag(false, null,'EvnReanimatAction_Edit');  //BOB - 04.07.2019
								
	},

	//добавление строки в таблице измерений
	EvnReanimatAction_RateAdd: function() {
		var win = this;		
		var rate_grid = this.findById('swERPEW_RA_Rate_Grid').getGrid();
		var EvnRAGridRowData = this.findById('swERPEW_ReanimatAction_Grid').getSelectionModel().getSelected().data;  //выбранная строка в гриде событий реанимационных мероприятий
		var rate_records = this.findById('swERPEW_ReanimatAction_Panel').rate_records;
		var vRate_id = 0;

		//ДЕЙСТВИЯ с ОБЪЕКТОМ ПАКЕТОВ ИЗМЕРЕНИЙ
		if (rate_records[EvnRAGridRowData['EvnReanimatAction_id']]) { //если для текущего мероприятия уже существует пакет записей измерений
			//формирование кода новой записи измерения
			var rate_pack = rate_records[EvnRAGridRowData['EvnReanimatAction_id']];
			for (var i in rate_pack)
				if (rate_pack[i]['Rate_id'] < vRate_id) vRate_id = rate_pack[i]['Rate_id'];
		} else { // если ещё нет пакета записей измерений //СОЗДАНИЕ ПАКЕТА ДЛЯ ТЕКУЩЕГО МЕРОПРИЯТИЯ			
			rate_records[EvnRAGridRowData['EvnReanimatAction_id']] = {};	
		}		
		vRate_id -= 1;			
		
		//ДОБАВЛЕНИЕ СТРОКИ В ГРИД ИЗМЕРЕНИЙ
		if(rate_grid.store.getCount() == 0){ // если в гриде измерений пусто
			var params = {
				EvnReanimatAction_id: EvnRAGridRowData['EvnReanimatAction_id'] == 'New_GUID_Id' ? null : EvnRAGridRowData['EvnReanimatAction_id'],
				ReanimatActionType_SysNick: 'new_rate'
			};
			this.findById('swERPEW_RA_Rate_Grid').loadData({
				globalFilters: params,
				callback: function(){
					rate_grid.getSelectionModel().selectRow(0);
					//ДОБАВЛЕНИЕ ЗАПИСИ В ОБЪЕКТ ПАКЕТОВ ИЗМЕРЕНИЙ		
					var RateRowData = rate_grid.getSelectionModel().getSelected().data;
					rate_records[EvnRAGridRowData['EvnReanimatAction_id']][vRate_id] = win.EvnReanimatAction_RateCopy(RateRowData);
				}
			});			
		} else {  // гриде измерений чё-то есть
			getCurrentDateTime({  // функция извлекает текущие время и дату и возвращает их в параметре функции callback
				callback: function(result) {
					if (result.success) {
						var rowN = 	rate_grid.store.getCount();
						rate_grid.store.insert(rowN, new Ext.data.Record({
							Rate_id: vRate_id,
							Rate_setDate: result.date,
							Rate_setTime: result.time,
							Rate_Value: 0,	
							Rate_PerCent: 0,
							Rate_StepsToChange: '',
							EvnReanimatAction_id: EvnRAGridRowData['EvnReanimatAction_id'],
							Rate_RecordStatus: 0
						}));
						rate_grid.getSelectionModel().selectRow(rate_grid.store.getCount() - 1);
						rate_grid.getView().focusRow(rate_grid.store.getCount() - 1);
						//ДОБАВЛЕНИЕ ЗАПИСИ В ОБЪЕКТ ПАКЕТОВ ИЗМЕРЕНИЙ		
						var RateRowData = rate_grid.getSelectionModel().getSelected().data;
						rate_records[EvnRAGridRowData['EvnReanimatAction_id']][vRate_id] = win.EvnReanimatAction_RateCopy(RateRowData);						
					}
				}
			});			
		}
	},

	//удаление строки в таблице измерений
	EvnReanimatAction_RateDel: function() {
		var win = this;	
		var rate_records = this.findById('swERPEW_ReanimatAction_Panel').rate_records;
		var rate_grid = this.findById('swERPEW_RA_Rate_Grid').getGrid();
		var RateGridRow = rate_grid.getSelectionModel().getSelected();
		var EvnRAGridRowData = this.findById('swERPEW_ReanimatAction_Grid').getSelectionModel().getSelected().data;  //выбранная строка в гриде событий реанимационных мероприятий
		if (!RateGridRow) 
			return false;
		
		sw.swMsg.show({
			buttons:Ext.Msg.YESNO,
			fn:function (buttonId, text, obj) {
				if (buttonId == 'yes') {
					switch ( Number(RateGridRow.get('Rate_RecordStatus')) ) {
						case 0:  // запись новая - не сохранённая
							delete rate_records[EvnRAGridRowData['EvnReanimatAction_id']][RateGridRow.get('Rate_id')];  //удаляю и объекта пакетов записей							
							rate_grid.getStore().remove(RateGridRow);  //удаляю из грида
							break;
						case 1:
						case 2:  //запись сохранённапя
							
							RateGridRow.set('Rate_RecordStatus', 3);
							RateGridRow.commit();
							rate_records[EvnRAGridRowData['EvnReanimatAction_id']][RateGridRow.get('Rate_id')] = win.EvnReanimatAction_RateCopy(RateGridRow.data);
							rate_grid.getStore().filterBy(function(rec) {
								return (Number(rec.get('Rate_RecordStatus')) != 3);
							});
							break;
					}

					if ( rate_grid.getStore().getCount() > 0 ) {
						rate_grid.getView().focusRow(0);
						rate_grid.getSelectionModel().selectFirstRow();
					}
				}
			},
			icon:Ext.MessageBox.QUESTION,
			msg:langs('Вы хотите удалить запись?'),
			title:langs('Подтверждение')
		});
	},

	//возвращает объекта типа записи измерений на основе другого объекта
	EvnReanimatAction_RateCopy: function(src) {
		return {
			Rate_id: src['Rate_id'],
			Rate_setDate: src['Rate_setDate'],
			Rate_setTime: src['Rate_setTime'],
			Rate_Value: src['Rate_Value'],
			Rate_PerCent: src['Rate_PerCent'],
			Rate_StepsToChange: src['Rate_StepsToChange'],
			EvnReanimatAction_id: src['EvnReanimatAction_id'],
			Rate_RecordStatus: src['Rate_RecordStatus']
		};
	},

	//удаление реанимационного мероприятия
	EvnReanimatAction_Del: function() {
		var win = this;
		
		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId) {
				if ( buttonId == 'yes' ) {
					
					var Grid = this.findById('swERPEW_ReanimatAction_Grid');
					var EvnRCGridRowData = Grid.getSelectionModel().getSelected().data;  //выбранная строка в гриде событий регулярного наблюдения состояния
					console.log('BOB_EvnRCGridRowData=',EvnRCGridRowData);  //BOB - 21.05.2018;
					
					if (Ext.isEmpty(EvnRCGridRowData['EvnReanimatAction_id'])) {
						Ext.MessageBox.alert('Внимание!', 'Не выбрана строка в списке наблюдений! ');
						return false;
					}
					
					//var RawId = Grid.getSelectionModel().getSelected().id;
					this.ActionGridLoadRawNum = Grid.getStore().find('EvnReanimatAction_id',Grid.getSelectionModel().getSelected().id);
					
					var data = 	{ 
						EvnReanimatAction_id: EvnRCGridRowData['EvnReanimatAction_id']
					};				
									
				
					$.ajax({
						mode: "abort",
						type: "post",
						async: false,
						url: '/?c=EvnReanimatPeriod&m=EvnReanimatAction_Del',
						data: data,
						success: function(response) {
							var DelResponse = Ext.util.JSON.decode(response);
							console.log('BOB_DelResponse=',DelResponse); 
							if (DelResponse['success'] == 'true'){
								if (win.ActionGridLoadRawNum == Grid.getStore().data.length - 1)//перейти на запись с тем же положением, а если последняя то на первую	
									win.ActionGridLoadRawNum--;
								delete win.findById('swERPEW_ReanimatAction_Panel').rate_records[EvnRCGridRowData['EvnReanimatAction_id']];
								Grid.getStore().reload();	//перезагрузка грида Реанимационных мероприятий	
								//console.log('BOB_RawNum0=',win.ActionGridLoadRawNum);  //BOB - 21.05.2018;
							}
							else	
								Ext.MessageBox.alert('Ошибка при удалении!', DelResponse['Error_Msg']);
								//alert('Ошибка сохранения.'+SaveResponse['message']);
						}, 
						error: function() {
							Ext.MessageBox.alert('Ошибка при удалении!', "При обработке запроса на сервере произошла ошибка!");
						} 
					});		
				}

			}.createDelegate(this),
			icon: Ext.Msg.WARNING,
			msg: 'Вы действительно хотите удалить реанимационное мероприятие?',
			title: 'Внимание!'
		});
	},

	//сохранение события реанимационного мероприятия
	EvnReanimatAction_Save: function(b,e) {
		var win = this;
		



		var EvnRAGridRowData = this.findById('swERPEW_ReanimatAction_Grid').getSelectionModel().getSelected().data;  //выбранная строка в гриде событий реанимационных мероприятий
		var EvnReanimatActionMethod_id = 0;
		
		//контроль перед сохранением
		var ErrMessag = '';
		if (EvnRAGridRowData['EvnReanimatAction_setDate'] == '')
			ErrMessag += 'дата мероприятия<br>';
		if (EvnRAGridRowData['EvnReanimatAction_setTime'] == '')
			ErrMessag += 'время мероприятия<br>';
		if (EvnRAGridRowData['ReanimatActionType_id'] == 0)
			ErrMessag += 'вид мероприятия<br>';
		else {
			//BOB - 07.11.2017 - делаю окончание периода необязательными полями				
				if(EvnRAGridRowData['ReanimatActionType_SysNick'].inlist(['lung_ventilation','nutrition','hemodialysis','endocranial_sensor','invasive_hemodynamics','observation_saturation'])) { 
				//	console.log('BOB_SaveResponse1=',EvnRAGridRowData['EvnReanimatAction_disDate'] + '~' + EvnRAGridRowData['EvnReanimatAction_disTime']); 
					if ((EvnRAGridRowData['EvnReanimatAction_disDate'] == '') && (EvnRAGridRowData['EvnReanimatAction_disTime'] != ''))
						ErrMessag += 'дата окончания мероприятия<br>';
					if ((EvnRAGridRowData['EvnReanimatAction_disTime'] == '') && (EvnRAGridRowData['EvnReanimatAction_disDate'] != ''))
						ErrMessag += 'время окончания мероприятия<br>';					
				}

			// если типы мероприятий, содержащие метод
			if (EvnRAGridRowData['ReanimatActionType_SysNick'].inlist(['lung_ventilation','nutrition','hemodialysis','endocranial_sensor','epidural_analgesia','catheterization_veins'])) {
				var combo = win.findById('swERPEW_RA_Method');   //combo метода мероприятия 
				if (combo) {
					if ((combo.getValue() == null) || (combo.getValue() == ''))
						ErrMessag += 'метод мероприятия<br>';
				}
			}

			// если типы мероприятий, содержащие медикамент
			if (EvnRAGridRowData['ReanimatActionType_SysNick'].inlist(['vazopressors','epidural_analgesia','antifungal_therapy','catheterization_veins','invasive_hemodynamics','sedation'])) {
				if ((EvnRAGridRowData['ReanimDrugType_id'] == null))
					ErrMessag += 'медикамент<br>';
				for(var i in win.findById('swERPEW_ReanimatAction_Panel').ReanimDrug[EvnRAGridRowData['EvnReanimatAction_id']]){
					if (win.findById('swERPEW_ReanimatAction_Panel').ReanimDrug[EvnRAGridRowData['EvnReanimatAction_id']][i].ReanimDrugType_id == null)
						ErrMessag += 'медикамент<br>';
				}
			}

			// если типы мероприятий, содержащие ОПЛАТУ
			if (EvnRAGridRowData['ReanimatActionType_SysNick'].inlist(['lung_ventilation','hemodialysis','endocranial_sensor','epidural_analgesia','catheterization_veins'])) {
				if ((EvnRAGridRowData['PayType_id'] == null))
					ErrMessag += 'тип оплаты<br>';
			}

//			// если наблюдение
//			if (EvnRAGridRowData['ReanimatActionType_SysNick'].inlist(['observation_saturation'])) {
//				if ((EvnRAGridRowData['EvnReanimatAction_ObservValue'] == 0))
//					ErrMessag += 'значение наблюдения<br>';
//			}

			// если катетеризация вен, чтобы проверить дополнительные парматры
			if (EvnRAGridRowData['ReanimatActionType_SysNick'].inlist(['catheterization_veins','invasive_hemodynamics'])) {
				if ((EvnRAGridRowData['ReanimatCathetVeins_id'] == '')||(EvnRAGridRowData['ReanimatCathetVeins_id'] == null))
					ErrMessag += EvnRAGridRowData['ReanimatActionType_SysNick'] == 'catheterization_veins' ? 'вена, используемая при катетеризации<br>' : 'артерия<br>';
			}
			
			// если ИВЛ, чтобы проверить дополнительные парматры  //BOB - 03.11.2018
			if (EvnRAGridRowData['ReanimatActionType_SysNick'].inlist(['lung_ventilation'])) {
				if (win.findById('swERPEW_RA_IVLParameter_Apparat').getValue() == '')
					ErrMessag += 'аппарат ИВЛ<br>';
				if (win.findById('swERPEW_RA_IVLRegim').getValue() == null)
					ErrMessag += 'режим ИВЛ<br>';
			}
			//если сердечно-лёгочная реанимация
			if (EvnRAGridRowData['ReanimatActionType_SysNick'].inlist(['card_pulm'])) {
				if (!win.findById('swERPEW_RA_CardPulm_Panel').CardPulm_Data.ReanimatCardPulm_ClinicalDeathDate)  
					ErrMessag += 'дата фиксация клинической смерти <br>';
				if (!win.findById('swERPEW_RA_CardPulm_Panel').CardPulm_Data.ReanimatCardPulm_ClinicalDeathTime) 
					ErrMessag += 'время фиксация клинической смерти <br>';
				if (!win.findById('swERPEW_RA_CardPulm_Panel').CardPulm_Data.ReanimatCardPulm_Time) 
					ErrMessag += 'время проведения реанимационных мероприятий <br>';
			}
			
			
		}
		
		//Контроль даты по отношению к началу и концу РП 
		//Дата мероприятия
		if (this.findById('swERPEW_EvnReanimatAction_setDate').getValue() != ''){
			var ERA_setDT = this.findById('swERPEW_EvnReanimatAction_setDate').getValue();
			var Time = this.findById('swERPEW_EvnReanimatAction_setTime').getValue();
			ERA_setDT.setHours(parseInt(Time.substr(0, 2)), parseInt(Time.substr(3, 2)));			
			//Начало периода
			var RP_setDT = this.findById('swERPEW_EvnReanimatPeriod_setDate').getValue(); //  + ' ' + 
			Time = this.findById('swERPEW_EvnReanimatPeriod_setTime').getValue();		
			RP_setDT.setHours(parseInt(Time.substr(0, 2)), parseInt(Time.substr(3, 2)));

			if (ERA_setDT < RP_setDT)
				ErrMessag += 'дата начала мероприятия меньше даты начала Реанимационного периода<br>';
			
			//дата окончания реаниац мероприятия
			var ERA_disDT = '';
			if (this.findById('swERPEW_EvnReanimatAction_disDate').getValue() != ''){
				ERA_disDT = this.findById('swERPEW_EvnReanimatAction_disDate').getValue();
				Time = this.findById('swERPEW_EvnReanimatAction_disTime').getValue();
				ERA_disDT.setHours(parseInt(Time.substr(0, 2)), parseInt(Time.substr(3, 2)));
			}	

			//Конец периода
			if (this.findById('swERPEW_EvnReanimatPeriod_disDate').getValue() != ''){
				var RP_disDT = this.findById('swERPEW_EvnReanimatPeriod_disDate').getValue(); //  + ' ' + 
				Time = this.findById('swERPEW_EvnReanimatPeriod_disTime').getValue();		
				RP_disDT.setHours(parseInt(Time.substr(0, 2)), parseInt(Time.substr(3, 2)));		

				if (ERA_setDT > RP_disDT)
					ErrMessag += 'дата начала мероприятия больше даты окончания Реанимационного периода<br>';
				
				if ((RP_disDT != '') && (ERA_disDT > RP_disDT)){
					ErrMessag += 'дата окончания мероприятия больше даты окончания Реанимационного периода<br>';					
				}
			}
			
			//console.log('BOB_ERA_setDT / ERA_disDT=',ERA_setDT + ' / ' + ERA_disDT);
			if ((ERA_disDT != '') &&(ERA_setDT > ERA_disDT))
				ErrMessag += 'дата окончания мероприятия меньше даты начала мероприятия <br>';				
			
			
		}

		if (ErrMessag == '') { //если сообщение о незаполненных реквизитах пустое
//			//если наблюдение за сатурацией - дополняем массив формирующий диаграмму 
//			if(EvnRAGridRowData['ReanimatActionType_SysNick'] == 'observation_saturation'){
//				var observation_saturation = this.findById('swERPEW_ReanimatAction_Panel').observation_saturation;
//				observation_saturation[observation_saturation.lenght] =  {EvnReanimatAction_setDate: EvnRAGridRowData['EvnReanimatAction_setDate'].format("d.m.y"),
//																					EvnReanimatAction_setTime: EvnRAGridRowData['EvnReanimatAction_setTime'],
//																					EvnReanimatAction_ObservValue: EvnRAGridRowData['EvnReanimatAction_ObservValue']};														
//				this.findById('swERPEW_ReanimatAction_Panel').observation_saturation = observation_saturation;																	
//			}

			//параметры Реанимационного мероприятия и общие параметры
			var data = 	{ 
				EvnReanimatAction_id: EvnRAGridRowData['EvnReanimatAction_id'],
				EvnReanimatAction_pid: EvnRAGridRowData['EvnReanimatAction_pid'],
				EvnReanimatAction_rid: this.EvnReanimatPeriod_rid,
				EvnSection_id: this.EvnReanimatPeriod_pid,   //BOB - 02.09.2018
				Lpu_id: this.Lpu_id,
				Person_id: EvnRAGridRowData['Person_id'],
				PersonEvn_id: EvnRAGridRowData['PersonEvn_id'],
				Server_id: EvnRAGridRowData['Server_id'],

				EvnReanimatAction_setDate: Ext.util.Format.date(EvnRAGridRowData['EvnReanimatAction_setDate'], 'd.m.Y'),
				EvnReanimatAction_setTime: EvnRAGridRowData['EvnReanimatAction_setTime'],

				EvnReanimatAction_disDate: Ext.util.Format.date(EvnRAGridRowData['EvnReanimatAction_disDate'], 'd.m.Y'),
				EvnReanimatAction_disTime: EvnRAGridRowData['EvnReanimatAction_disTime'],

				ReanimatActionType_id: EvnRAGridRowData['ReanimatActionType_id'],
				ReanimatActionType_SysNick: EvnRAGridRowData['ReanimatActionType_SysNick'],
				EvnReanimatAction_MethodCode: EvnRAGridRowData['EvnReanimatAction_MethodCode'], 
				UslugaComplex_id: EvnRAGridRowData['UslugaComplex_id'], //BOB - 19.02.2018

				ReanimDrugType_id: EvnRAGridRowData['ReanimDrugType_id'],
				EvnReanimatAction_DrugDose: EvnRAGridRowData['EvnReanimatAction_DrugDose'],
				ReanimDrug: Ext.util.JSON.encode(win.findById('swERPEW_ReanimatAction_Panel').ReanimDrug[EvnRAGridRowData['EvnReanimatAction_id']]), //BOB - 05.03.2020

				//EvnReanimatAction_ObservValue: EvnRAGridRowData['EvnReanimatAction_ObservValue'],

				ReanimatCathetVeins_id: EvnRAGridRowData['ReanimatCathetVeins_id'],
				CathetFixType_id: EvnRAGridRowData['CathetFixType_id'],
				EvnReanimatAction_CathetNaborName: EvnRAGridRowData['EvnReanimatAction_CathetNaborName'],
				EvnReanimatAction_DrugUnit: EvnRAGridRowData['EvnReanimatAction_DrugUnit'],                 //BOB - 23.04.2018
				EvnReanimatAction_MethodTxt: EvnRAGridRowData['EvnReanimatAction_MethodTxt'],  //BOB - 03.11.2018  метод - вариант пользователя
				EvnReanimatAction_NutritVol:  EvnRAGridRowData['EvnReanimatAction_NutritVol'],  //BOB - 03.11.2018  объём питания
				EvnReanimatAction_NutritEnerg:  EvnRAGridRowData['EvnReanimatAction_NutritEnerg'],  //BOB - 03.11.2018  энеогия питания
				MilkMix_id: EvnRAGridRowData['MilkMix_id'],  //BOB - 15.04.2020  молочная смесь
				
				//EvnReanimatActionMethod_id: EvnReanimatActionMethod_id, //BOB - 19.02.2018
				LpuSection_id: this.LpuSection_id,
				Diag_id: this.Diag_id,
				MedPersonal_id: this.MedPersonal_id,
				MedStaffFact_id: this.MedStaffFact_id,

				PayType_id: EvnRAGridRowData['PayType_id'],
				EvnUsluga_id: EvnRAGridRowData['EvnUsluga_id'],  //BOB - 04.07.2019
				EvnDrug_id: EvnRAGridRowData['EvnDrug_id']   //BOB - 04.07.2019
			};
			var IVLParameter = {};
			// если ИВЛ, формирую набор параметров парматры  //BOB - 03.11.2018
			if (EvnRAGridRowData['ReanimatActionType_SysNick'].inlist(['lung_ventilation'])) {
				IVLParameter = {
					IVLParameter_id: (win.findById('swERPEW_RA_IVLParameter_id').getValue() == '') ? null : win.findById('swERPEW_RA_IVLParameter_id').getValue(),	//BOB - 04.07.2019
					IVLParameter_Apparat: win.findById('swERPEW_RA_IVLParameter_Apparat').getValue(),
					IVLRegim_id: win.findById('swERPEW_RA_IVLRegim').getValue(),
					IVLParameter_TubeDiam: win.findById('swERPEW_RA_IVLParameter_TubeDiam').getValue(),
					IVLParameter_FiO2: win.findById('swERPEW_RA_IVLParameter_FiO2').getValue(),
					IVLParameter_PcentMinVol: win.findById('swERPEW_RA_IVLParameter_PcentMinVol').getValue(),
					IVLParameter_TwoASVMax: win.findById('swERPEW_RA_IVLParameter_TwoASVMax').getValue(),
					IVLParameter_FrequSet: win.findById('swERPEW_RA_IVLParameter_FrequSet').getValue(),
					IVLParameter_VolInsp: win.findById('swERPEW_RA_IVLParameter_VolInsp').getValue(),
					IVLParameter_PressInsp: win.findById('swERPEW_RA_IVLParameter_PressInsp').getValue(),
					IVLParameter_PressSupp: win.findById('swERPEW_RA_IVLParameter_PressSupp').getValue(),
					IVLParameter_FrequTotal: win.findById('swERPEW_RA_IVLParameter_FrequTotal').getValue(),
					IVLParameter_VolTe: win.findById('swERPEW_RA_IVLParameter_VolTe').getValue(),
					IVLParameter_VolE: win.findById('swERPEW_RA_IVLParameter_VolE').getValue(),
					IVLParameter_TinTet: win.findById('swERPEW_RA_IVLParameter_Tin').getValue() + ':' + win.findById('swERPEW_RA_IVLParameter_Tet').getValue(),						
					IVLParameter_VolTrig: win.findById('swERPEW_RA_IVLParameter_VolTrig').getValue(),
					IVLParameter_PressTrig: win.findById('swERPEW_RA_IVLParameter_PressTrig').getValue(),
					IVLParameter_PEEP: win.findById('swERPEW_RA_IVLParameter_PEEP').getValue(),
					IVLParameter_VolTi: win.findById('swERPEW_RA_IVLParameter_VolTi').getValue(), //BOB - 29.02.2020	
					IVLParameter_Peak: win.findById('swERPEW_RA_IVLParameter_Peak').getValue(), //BOB - 29.02.2020	
					IVLParameter_MAP: win.findById('swERPEW_RA_IVLParameter_MAP').getValue(), //BOB - 29.02.2020	
					IVLParameter_Tins: win.findById('swERPEW_RA_IVLParameter_Tins').getValue(), //BOB - 29.02.2020	
					IVLParameter_FlowMax: win.findById('swERPEW_RA_IVLParameter_FlowMax').getValue(), //BOB - 29.02.2020	
					IVLParameter_FlowMin: win.findById('swERPEW_RA_IVLParameter_FlowMin').getValue(), //BOB - 29.02.2020	
					IVLParameter_deltaP: win.findById('swERPEW_RA_IVLParameter_deltaP').getValue(), //BOB - 29.02.2020	
					IVLParameter_Other: win.findById('swERPEW_RA_IVLParameter_Other').getValue() //BOB - 29.02.2020	
				
				};
				data['IVLParameter'] = Ext.util.JSON.encode(IVLParameter);
			}
			//если сердечно-лёгочная реанимация
			if (EvnRAGridRowData['ReanimatActionType_SysNick'].inlist(['card_pulm'])){
				data['CardPulm'] = Ext.util.JSON.encode(win.findById('swERPEW_RA_CardPulm_Panel').CardPulm_Data);
			}
			
			// если использование датчика ВЧД или Инвазивная гемодинамика или Наблюдение сатурации гемоглобина
			if (EvnRAGridRowData['ReanimatActionType_SysNick'].inlist(['endocranial_sensor','invasive_hemodynamics','observation_saturation'])){ 
				data['Rate_List'] = Ext.util.JSON.encode(this.findById('swERPEW_ReanimatAction_Panel').rate_records[EvnRAGridRowData['EvnReanimatAction_id']]);	
				delete this.findById('swERPEW_ReanimatAction_Panel').rate_records[EvnRAGridRowData['EvnReanimatAction_id']];
			}
			
			
			if (EvnRAGridRowData['EvnReanimatAction_id'] == 'New_GUID_Id')
				this.ActionGridLoadRawNum = 0;
			else
				this.ActionGridLoadRawNum = this.findById('swERPEW_ReanimatAction_Grid').getStore().find('EvnReanimatAction_id',this.findById('swERPEW_ReanimatAction_Grid').getSelectionModel().getSelected().id);

			//BOB - 04.07.2019 - приходится так делать - иначе не извлекаются данные из таблици измерения сатурации
			var Rate_Items = (EvnRAGridRowData['ReanimatActionType_SysNick'] == 'observation_saturation') ? win.findById('swERPEW_RA_Rate_Grid').getGrid().getStore().data.items : [];

			var loadMask = new Ext.LoadMask(Ext.get('swERPEW_ReanimatAction_Panel'), {msg: "Идёт сохранение..."});
			loadMask.show();

			$.ajax({
				mode: "abort",
				type: "post",
				async: true,
				url: '/?c=EvnReanimatPeriod&m=EvnReanimatAction_Save',
				data: data,
				success: function(response) {
					loadMask.hide();
					var SaveResponse = Ext.util.JSON.decode(response);
					console.log('BOB_SaveResponse=',SaveResponse); 
					if (SaveResponse['success'] == 'true'){
						delete win.findById('swERPEW_ReanimatAction_Panel').ReanimDrug[EvnRAGridRowData['EvnReanimatAction_id']]; //BOB - 05.03.2020


						if (EvnRAGridRowData['EvnReanimatAction_id'] == 'New_GUID_Id')
							win.findById('swERPEW_ReanimatAction_Grid').getStore().reload();	//перезагрузка грида Реанимационных мероприятий
						else  // панель делаю неактивной
							win.findById('swERPEW_ReanimatAction_Grid').getSelectionModel().selectRow(win.ActionGridLoadRawNum);   //BOB - 04.07.2019

						//BOB - 23.04.2018  закидываю в наблюдения состояний результат по мероприятиям
						var EvnReanimatCondition_GridId = win.findById('swERPEW_EvnReanimatCondition_Grid').getStore().find('EvnReanimatCondition_id', 'New_GUID_Id');  //ищу Id новой записи в гриде наблюдений состояния    
						if (EvnReanimatCondition_GridId > -1) {
							
							var EvnReanimatCondition_GridRow = win.findById('swERPEW_EvnReanimatCondition_Grid').getStore().getAt(EvnReanimatCondition_GridId);
							if(EvnRAGridRowData['ReanimatActionType_SysNick'] == 'nutrition'){
								//BOB - 23.09.2019
								win.findById('swERPEW_RC_Nutritious').setValue(EvnRAGridRowData['UslugaComplex_id']);
								win.findById('swERPEW_RC_NutritiousTxt').setVisible(EvnRAGridRowData['UslugaComplex_id'] == 4);
								win.findById('swERPEW_RC_NutritiousTxt').setValue(EvnRAGridRowData['EvnReanimatAction_MethodTxt']);
								win.findById('swERPEW_RC_NutritVol').setValue(EvnRAGridRowData['EvnReanimatAction_NutritVol']);
								win.findById('swERPEW_RC_NutritEnerg').setValue(EvnRAGridRowData['EvnReanimatAction_NutritEnerg']);
							}
							
							//BOB - 24.01.2019 перекладывание измерений сатурации гемоглобина в наблюдения
							if(EvnRAGridRowData['ReanimatActionType_SysNick'] == 'observation_saturation'){
								var Rate_Value = 0;
								//ищу в таблице наблюдений, если таблица не пустая беру значение из неё
								if (Rate_Items.length > 0){
									Rate_Value = Rate_Items[Rate_Items.length - 1].data.Rate_Value;
								}
								EvnReanimatCondition_GridRow.data['EvnReanimatCondition_Saturation'] = Rate_Value;   // в грид наблюдений состояния
								EvnReanimatCondition_GridRow.commit();
								win.findById('swERPEW_RC_Saturation').setValue(Rate_Value);	// в тектовое поле			
							}
							//BOB - 24.01.2019 параметров ИВЛ в наблюдения
							if(EvnRAGridRowData['ReanimatActionType_SysNick'] == 'lung_ventilation'){
								var IVLApparat = '';
								var IVLParameter_ = '';
								IVLApparat = IVLParameter['IVLParameter_Apparat'];
								var IVLRegim_SysNick = win.findById('swERPEW_RA_IVLRegim').store.getAt(win.findById('swERPEW_RA_IVLRegim').store.find('IVLRegim_id',IVLParameter['IVLRegim_id'])).data['IVLRegim_SysNick'];					
								IVLParameter_ = IVLRegim_SysNick.toUpperCase().replace('_', ' ');
								if (IVLParameter['IVLParameter_TubeDiam'] > 0 ) IVLParameter_ += ', D=' + Math.round(IVLParameter['IVLParameter_TubeDiam']*10)/10 + 'мм';
								if (IVLParameter['IVLParameter_FiO2'] > 0 ) IVLParameter_ += ', FiO2=' + IVLParameter['IVLParameter_FiO2'] + '%';
								if (IVLParameter['IVLParameter_PcentMinVol'] > 0 ) IVLParameter_ +=  IVLRegim_SysNick.inlist(['asv']) ? ', % Мин. Объ=' + IVLParameter['IVLParameter_PcentMinVol'] + '%' : '';
								if (IVLParameter['IVLParameter_TwoASVMax'] > 0 ) IVLParameter_ +=  IVLRegim_SysNick.inlist(['asv']) ? ', Два ASV макс=' + IVLParameter['IVLParameter_TwoASVMax'] + 'см вд ст' : '';
								if (IVLParameter['IVLParameter_FrequSet'] > 0 ) IVLParameter_ +=  IVLRegim_SysNick.inlist(['cmv_vc','cmv_pc','simv_vc','simv_pc']) ? ', f=' + IVLParameter['IVLParameter_FrequSet'] + 'раз/мин' : '';
								if (IVLParameter['IVLParameter_VolInsp'] > 0 ) IVLParameter_ +=  IVLRegim_SysNick.inlist(['cmv_vc','simv_vc']) ? ', Vinsp=' + IVLParameter['IVLParameter_VolInsp'] + 'мл' : '';
								if (IVLParameter['IVLParameter_PressInsp'] > 0 ) IVLParameter_ +=  IVLRegim_SysNick.inlist(['cmv_pc','simv_pc']) ? ', Pinsp=' + IVLParameter['IVLParameter_PressInsp'] + 'см вд ст' : '';
								if (IVLParameter['IVLParameter_PressSupp'] > 0 ) IVLParameter_ +=  IVLRegim_SysNick.inlist(['simv_vc','simv_pc','psv']) ? ', PS=' + IVLParameter['IVLParameter_PressSupp'] + 'см вд ст' : '';
								if (IVLParameter['IVLParameter_FrequTotal'] > 0 ) IVLParameter_ += ', f_total=' + IVLParameter['IVLParameter_FrequTotal'] + 'раз/мин';
								if (IVLParameter['IVLParameter_VolTe'] > 0 ) IVLParameter_ += ', Vte=' + IVLParameter['IVLParameter_VolTe'] + 'мл';
								if (IVLParameter['IVLParameter_VolE'] > 0 ) IVLParameter_ += ', Ve=' + IVLParameter['IVLParameter_VolE'] + 'мл/мин';
								if (IVLParameter['IVLParameter_TinTet'] != "0:0" ) IVLParameter_ += ', t_in-t_exp=' + IVLParameter['IVLParameter_TinTet'];
								if (IVLParameter['IVLParameter_VolTrig'] > 0 ) IVLParameter_ += ', Vtrig=' + IVLParameter['IVLParameter_VolTrig'] + 'мл/мин';
								else if (IVLParameter['IVLParameter_PressTrig'] > 0 )  IVLParameter_ += ', Ptrig=' + IVLParameter['IVLParameter_PressTrig'] + 'см вд ст';
								if (IVLParameter['IVLParameter_PEEP'] > 0 ) IVLParameter_ += ', PEEP=' + IVLParameter['IVLParameter_PEEP'] + 'см вд ст';

								EvnReanimatCondition_GridRow.data['EvnReanimatCondition_IVLapparatus'] = IVLApparat;   // в грид наблюдений состояния
								EvnReanimatCondition_GridRow.data['EvnReanimatCondition_IVLparameter'] = IVLParameter_;   // в грид наблюдений состояния
								EvnReanimatCondition_GridRow.commit();
								win.findById('swERPEW_RC_IVLapparatus').setValue(IVLApparat);	// в текстовое поле			
								win.findById('swERPEW_RC_IVLparameter').setValue(IVLParameter_);	// в текстовое поле										
							}	
						}
						//если младенцы BOB - 13.04.2020 
						if (win.isNeonatal(win.findById('swERPEW_ReanimatAgeGroup').getValue()))  {
							if(EvnRAGridRowData['ReanimatActionType_SysNick'] == 'lung_ventilation'){
								if (getWnd('swEvnNeonatalSurveyEditWindow').isVisible()) {
									//Начало мероприятия
									var RP_setDT = win.findById('swERPEW_EvnReanimatAction_setDate').getValue(); //  + ' ' + 
									var Time = win.findById('swERPEW_EvnReanimatAction_setTime').getValue();		
									RP_setDT.setHours(parseInt(Time.substr(0, 2)), parseInt(Time.substr(3, 2)));
									//Конец мероприятия
									var RP_disDT = '';
									if (win.findById('swERPEW_EvnReanimatAction_disDate').getValue() != ''){
										RP_disDT = win.findById('swERPEW_EvnReanimatAction_disDate').getValue(); //  + ' ' + 
										Time = win.findById('swERPEW_EvnReanimatAction_disTime').getValue();		
										RP_disDT.setHours(parseInt(Time.substr(0, 2)), parseInt(Time.substr(3, 2)));		
									}



									IVLParameter['IVLRegim_SysNick'] = win.findById('swERPEW_RA_IVLRegim').store.getAt(win.findById('swERPEW_RA_IVLRegim').store.find('IVLRegim_id',IVLParameter['IVLRegim_id'])).data['IVLRegim_SysNick'];					

									getWnd('swEvnNeonatalSurveyEditWindow').EvnNeonatalSurvey_LoadIVLParams(IVLParameter, RP_setDT, RP_disDT);
								}
							}
						}

						//BOB - 23.04.2018
					}
					else{
						loadMask.hide();
						Ext.MessageBox.alert('Ошибка сохранения!', SaveResponse['Error_Msg']);
					}
				}, 
				error: function() {
					loadMask.hide();
					Ext.MessageBox.alert('Ошибка сохранения!', "При обработке запроса на сервере произошла ошибка!");
				} 
			});		
		}
		else {
			this.EvnReanimatAction_ButtonManag(false, null, 'EvnReanimatAction_Save_2');  //BOB - 04.07.2019
			ErrMessag = 'Отсутствуют или неверны следующие реквизиты Реанимационного мероприятия: <br><br>' + ErrMessag;
			Ext.MessageBox.alert('Внимание!', ErrMessag);
		}
	},
	
	//процедура управления кнопками раздела наблюдений   //BOB - 04.07.2019
	EvnReanimatAction_ButtonManag: function(old_rec, EvnRAGridRowData, from){
		//console.log('BOB_EvnReanimatAction_ButtonManag_from=',from);
		//old_rec - старая ли текущая запись
		var win  = this;
		var view_act = win.action == 'view' ? true : false;  //режим просмотра
		var exists_new = win.findById('swERPEW_ReanimatAction_Grid').store.find('EvnReanimatAction_id','New_GUID_Id') == -1 ? false :true;  // имеется ли новая запись вообще

//		console.log('BOB_view_act old_rec exists_new=',view_act + ' ' +  old_rec + ' ' + exists_new);
//		console.log('BOB_(view_act || !old_rec || exists_new)=',(view_act || !old_rec || exists_new));

		if (Ext.getCmp('swERPEW_EvnReanimatActionAdd'))
			Ext.getCmp('swERPEW_EvnReanimatActionAdd').setDisabled(view_act || !old_rec || exists_new);  // кнопку добавления
		if(Ext.getCmp('swERPEW_EvnReanimatActionEdit'))
			Ext.getCmp('swERPEW_EvnReanimatActionEdit').setDisabled(view_act || !old_rec || exists_new); // кнопку редактирования
		if (Ext.getCmp('swERPEW_EvnReanimatActionDel'))
			Ext.getCmp('swERPEW_EvnReanimatActionDel').setDisabled(view_act || !old_rec || exists_new); // кнопку удаления
		if (Ext.getCmp('swERPEW_EvnReanimatActionRefresh'))
			Ext.getCmp('swERPEW_EvnReanimatActionRefresh').setDisabled(false); // кнопку обновления


		var is_SysNick = (EvnRAGridRowData != null) && (EvnRAGridRowData['ReanimatActionType_SysNick'] != null) && (EvnRAGridRowData['ReanimatActionType_SysNick'].inlist(['catheterization_veins','card_pulm'])) ? true : false;
//		console.log('BOB_is_SysNick=',is_SysNick);

		if(Ext.getCmp('swERPEW_EvnReanimatActionButtonPrintUp'))
			Ext.getCmp('swERPEW_EvnReanimatActionButtonPrintUp').setDisabled(!old_rec || !is_SysNick); // кнопку печати верх документакатетеризации    //(!view_act && !old_rec);
		if(Ext.getCmp('swERPEW_EvnReanimatActionButtonPrintDoun'))
			Ext.getCmp('swERPEW_EvnReanimatActionButtonPrintDoun').setDisabled(!old_rec || !is_SysNick); // кнопку печати низ документакатетеризации    //(!view_act && !old_rec);


		var EndDate = (EvnRAGridRowData != null) && (EvnRAGridRowData['ReanimatActionType_SysNick'] != null) &&
				(EvnRAGridRowData['ReanimatActionType_SysNick'].inlist(['lung_ventilation','nutrition','hemodialysis','endocranial_sensor','invasive_hemodynamics','observation_saturation']))&&
				((EvnRAGridRowData['EvnReanimatAction_disDate'] == '') || (EvnRAGridRowData['EvnReanimatAction_disTime'] == ''))
				&& !exists_new

//		console.log('BOB_(EvnRAGridRowData != null) && (EvnRAGridRowData[ReanimatActionType_SysNick] != null)=',(EvnRAGridRowData != null) && (EvnRAGridRowData['ReanimatActionType_SysNick'] != null));
//		//console.log('BOB_(EvnRAGridRowData[ReanimatActionType_SysNick].inlist([lung_ventilation,nutrition,hemodialysis,endocranial_sensor,invasive_hemodynamics,observation_saturation]))=',(EvnRAGridRowData['ReanimatActionType_SysNick'].inlist(['lung_ventilation','nutrition','hemodialysis','endocranial_sensor','invasive_hemodynamics','observation_saturation'])));
//		console.log('BOB_exists_new=',exists_new);
//		console.log('BOB_EndDate=',EndDate);
//		console.log('BOB_(view_act || (old_rec && !EndDate))=',(view_act || (old_rec && !EndDate)));


		if(win.findById('swERPEW_EvnReanimatActionButtonSave'))
			win.findById('swERPEW_EvnReanimatActionButtonSave').setDisabled(view_act || (old_rec && !EndDate)); // кнопку сохранения

		//кнопки в панели нескольких ЛС  //BOB - 05.03.2020
		//установка неактивности на область ввода ЛС
		Ext.select('table', true, 'swERPEW_RA_Drug_Panel').each(function(el){
			var id = el.id; //выделяю параметр id из Ext.Element
			var object = win.findById(id);  //ищу в окне объект ExtJS
			if(object){ // если нахожу, то 
				object.setDisabled(view_act || (old_rec && !EndDate)); // делаю Disabled /Enabled
			}
		});



	},
	
	//функция печати документа по катетеризации верх/низ
	EvnReanimatAction_PrintUpDoun: function(Doun) {
	
		var EvnRAGridRowData = this.findById('swERPEW_ReanimatAction_Grid').getSelectionModel().getSelected().data;  //выбранная строка в гриде событий реанимационных мероприятий

		var cathetDate = EvnRAGridRowData['EvnReanimatAction_setDate'].format("d.m.Y");
		var cathetTime = EvnRAGridRowData['EvnReanimatAction_setTime'];
		var cathetAnestetic = EvnRAGridRowData['EvnReanimatAction_Medicoment'] + (EvnRAGridRowData['EvnReanimatAction_Medicoment'] == 'Новокаин' ? ' 0.5прц ' : ' 2прц ')   + ' - ' + EvnRAGridRowData['EvnReanimatAction_DrugDose'] + ' '  + EvnRAGridRowData['EvnReanimatAction_DrugUnit'];

		var cathetVein = '';		
		var Combo = this.findById('swERPEW_RA_CathetVeins');
		var index = Combo.getStore().find('ReanimatCathetVeins_id',EvnRAGridRowData['ReanimatCathetVeins_id']); //нахожу индекс в store комбо
		var rec = Combo.getStore().getAt(index);  // нахожу record по индексу
		if (rec) cathetVein = rec.data['ReanimatCathetVeins_NameR'];
																				
		var cathetNabor = EvnRAGridRowData['EvnReanimatAction_CathetNaborName'] != "" ? ", набором: " + EvnRAGridRowData['EvnReanimatAction_CathetNaborName'] : "" ;
		
		var cathetFixer = '';
		if (EvnRAGridRowData['CathetFixType_id'] != ""){
			Combo = this.findById('swERPEW_RA_CathetFix');
			index = Combo.getStore().find('CathetFixType_id',EvnRAGridRowData['CathetFixType_id']); //нахожу индекс в store комбо
			rec = Combo.getStore().getAt(index);  // нахожу record по индексу
			if (rec) cathetFixer = ": " + rec.data['CathetFixType_Name'];
		}
		var aFIO = this.MedPersonal_FIO.split(' ');
		var cathetDoctor = aFIO[0] + (aFIO.length > 1 ? ' ' + aFIO[1].substr(0, 1) + '.':'') + (aFIO.length > 2 ? ' ' + aFIO[2].substr(0, 1) + '.':'');
		
		var url = ((getGlobalOptions().birtpath)?getGlobalOptions().birtpath:'') + '/run?__report=report/cathetPrrintMulty.rptdesign';
//		var url = 'http://192.168.200.16/birt-viewer//run?__report=report/cathetPrrint.rptdesign';

		url += '&cathetDate=' + cathetDate;
		url += '&cathetTime=' + cathetTime;
		url += '&cathetAnestetic=' + cathetAnestetic;
		url += '&cathetVein=' + cathetVein;
		url += '&cathetNabor=' + cathetNabor;
		url += '&cathetFixer=' + cathetFixer;
		url += '&cathetDoctor=' + cathetDoctor;
		url += '&Doun=' + Doun;
		url += '&__format=pdf';		
		console.log('BOB_url=',url);  //BOB - 06.08.2017
		window.open(url, '_blank'); 

		
	}, 

	//печать формы сердечно-лёгочной реанимации
	CardPulm_Print: function() {
		var EvnRAGridRowData = this.findById('swERPEW_ReanimatAction_Grid').getSelectionModel().getSelected().data;  //выбранная строка в гриде событий реанимационных мероприятий
		var RA_Date = EvnRAGridRowData['EvnReanimatAction_setDate'].format("d.m.Y");		// дата документап
		var RA_Time = EvnRAGridRowData['EvnReanimatAction_setTime'];						// время документа		
		var CardPulm_Doctor = this.findById('swERPEW_RA_CardPulm_DoctorTxt').getValue();   // врач
		var ClinicalDeath_Time = this.findById('swERPEW_RA_ClinicalDeath_Time').getValue();  //время клинической смерти
		var IsPupilDilat = '';  // зрачки
		var RadioSet = this.findById('swERPEW_RA_IsPupilDilat').items.items;
		for (var i in RadioSet)
			if (RadioSet[i].checked) IsPupilDilat = RadioSet[i].boxLabel;
		var IsCardMonitor = '';  //Кардиомониторирование в момент остановки кровообращения 
		RadioSet = this.findById('swERPEW_RA_IsCardMonitor').items.items;
		for (var i in RadioSet)
			if (RadioSet[i].checked) IsCardMonitor = RadioSet[i].boxLabel;
		var StopCardActType = ''; //Вид прекращения сердечной деятельности 
		RadioSet = this.findById('swERPEW_RA_StopCardActType').items.items;
		for (var i in RadioSet)
			if (RadioSet[i].checked) StopCardActType = RadioSet[i].boxLabel;
		var IVLRegim = (this.findById('swERPEW_RA_IVLRegim2').lastSelectionText) ? 'проводится в режиме ' + this.findById('swERPEW_RA_IVLRegim2').lastSelectionText : 'не проводится';  //режим ИВЛ
		var FiO2 = this.findById('swERPEW_RA_CardPulm_FiO2').getValue();  // Концентрация кислорода - FiO2
		var IsCardTonics = '';     //введение кардиотоников
		RadioSet = this.findById('swERPEW_RA_IsCardTonics').items.items;
		for (var i in RadioSet){
			if (RadioSet[i].checked){
				IsCardTonics = RadioSet[i].boxLabel;
				if (RadioSet[i].inputValue == 2)  IsCardTonics += ' ' +  this.findById('swERPEW_RA_CardTonicDose').getValue() + ' мкг/кг/мин'; 
			}
		}		
		var CathetVein = '';  // катетеризация магистральной вены
		RadioSet = this.findById('swERPEW_RA_CathetVein').items.items;
		for (var i in RadioSet)
			if (RadioSet[i].checked) CathetVein = RadioSet[i].boxLabel;
		var TrachIntu = this.findById('swERPEW_RA_TrachIntub').getValue(); // № трубки
		var Auscultatory = ''; //аускультативная картина
		RadioSet = this.findById('swERPEW_RA_Auscultatory').items.items;
		for (var i in RadioSet)
			if (RadioSet[i].checked) Auscultatory = RadioSet[i].boxLabel;
		Auscultatory += (Auscultatory ? ', ' : '') + this.findById('swERPEW_RA_AuscultatoryTxt').getValue(); //аускультативная другое
		var CardMassage = this.findById('swERPEW_RA_CardMassage').getValue(); // частота массажа сердца
		var DefibrilCount = this.findById('swERPEW_RA_DefibrilCount').getValue();  // дефибрилляция - количество
		var DefibrilMin = this.findById('swERPEW_RA_DefibrilMin').getValue(); // дефибрилляция - мин
		var DefibrilMax = this.findById('swERPEW_RA_DefibrilMax').getValue();  // дефибрилляция - макс
		//внутривенно введено
		var drug = {1: 'Sol. Adrenalini 0,1прц', 13: 'Sol. Atropini 0,1прц',14: 'Sol. Cordarone 0,1прц'}
		var CardPulm_Drug1 = '';
		if (this.findById('swERPEW_RA_CardPulm_Drug_1').value) {
			CardPulm_Drug1 = drug[this.findById('swERPEW_RA_CardPulm_Drug_1').value] + ' ' + this.findById('swERPEW_RA_CardPulm_Drug_Dose_1').getValue() + 'ml ';
			RadioSet = this.findById('swERPEW_RA_DrugSposob_1').items.items;
			for (var i in RadioSet)
				if (RadioSet[i].checked) CardPulm_Drug1 += RadioSet[i].boxLabel;			
		}
		var CardPulm_Drug2 = '';
		if (this.findById('swERPEW_RA_CardPulm_Drug_2').value) {
			CardPulm_Drug2 = drug[this.findById('swERPEW_RA_CardPulm_Drug_2').value] + ' ' + this.findById('swERPEW_RA_CardPulm_Drug_Dose_2').getValue() + 'ml ';
			RadioSet = this.findById('swERPEW_RA_DrugSposob_2').items.items;
			for (var i in RadioSet)
				if (RadioSet[i].checked) CardPulm_Drug2 += RadioSet[i].boxLabel;			
		}
		var DrugTxt = this.findById('swERPEW_RA_DrugTxt').getValue(); //внутривенно друго
		var IsEffective = ''; //эффективность мероприятий
		RadioSet = this.findById('swERPEW_RA_IsEffective').items.items;
		for (var i in RadioSet)
			if (RadioSet[i].checked) IsEffective = RadioSet[i].boxLabel;
		var CardPulm_Time = this.findById('swERPEW_RA_CardPulm_Time').getValue(); // время мероприятий
		var BiologDeath_Time = this.findById('swERPEW_RA_BiologDeath_Time').getValue();  //время биологической смерти
		
		
		//alert(IVLRegim);
		var url = ((getGlobalOptions().birtpath)?getGlobalOptions().birtpath:'') + '/run?__report=report/ReanimatCardPulm.rptdesign';
//		var url = 'http://192.168.200.16/birt-viewer//run?__report=report/cathetPrrint.rptdesign';

		url += '&RA_Date=' + RA_Date;
		url += '&RA_Time=' + RA_Time;
		url += '&CardPulm_Doctor=' + CardPulm_Doctor;
		url += '&ClinicalDeath_Time=' + ClinicalDeath_Time;
		url += '&IsPupilDilat=' + IsPupilDilat;
		url += '&IsCardMonitor=' + IsCardMonitor;
		url += '&StopCardActType=' + StopCardActType;
		url += '&IVLRegim=' + IVLRegim;
		url += '&FiO2=' + FiO2;
		url += '&IsCardTonics=' + IsCardTonics;
		url += '&CathetVein=' + CathetVein;
		url += '&TrachIntu=' + TrachIntu;
		url += '&Auscultatory=' + Auscultatory;
		url += '&CardMassage=' + CardMassage;
		url += '&DefibrilCount=' + DefibrilCount;
		url += '&DefibrilMin=' + DefibrilMin;
		url += '&DefibrilMax=' + DefibrilMax;
		url += '&CardPulm_Drug1=' + CardPulm_Drug1;
		url += '&CardPulm_Drug2=' + CardPulm_Drug2;
		url += '&DrugTxt=' + DrugTxt;
		url += '&IsEffective=' + IsEffective;
		url += '&CardPulm_Time=' + CardPulm_Time;
		url += '&BiologDeath_Time=' + BiologDeath_Time;
		url += '&__format=pdf';		
		console.log('BOB_url=',url);  //BOB - 06.08.2017
		window.open(url, '_blank'); 
		
		
		

	},

	//загрузка просмотра/редактирования регулярного наблюдения состояния
	EvnReanimatCondition_view: function() {
		var win = this;
		var base_form = this.findById('swERPEW_Form').getForm();

		//BOB - 31.01.2020 - если детская реанимация, то сразу выходим из процедуры
		if (this.isNeonatal(this.findById('swERPEW_ReanimatAgeGroup').getValue())) {
			this.findById('swERPEW_EvnReanimatConditionPanel').setVisible(false);  
			return;
		} 

		this.findById('swERPEW_EvnReanimatConditionPanel').setVisible(true);  //BOB - 31.01.2020
		var EvnRCGridRowData = this.findById('swERPEW_EvnReanimatCondition_Grid').getSelectionModel().getSelected().data;  //выбранная строка в гриде событий регулярного наблюдения состояния
		var SideType =  this.SideType;

		//console.log('BOB_EvnRCGridRowData=',EvnRCGridRowData);  //BOB - 21.05.2018;

		this.findById('swERPEW_EvnReanimatConditionStage').setValue(EvnRCGridRowData['ReanimStageType_id']); //этап - документ обытие регулярного наблюдения состояния 
		this.findById('swERPEW_EvnReanimatCondition_setDate').setValue(EvnRCGridRowData['EvnReanimatCondition_setDate']); //установка даты события регулярного наблюдения состояния
		this.findById('swERPEW_EvnReanimatCondition_setTime').setValue(EvnRCGridRowData['EvnReanimatCondition_setTime']); //установка времени события регулярного наблюдения состояния
		this.findById('swERPEW_EvnReanimatCondition_disDate').setValue(EvnRCGridRowData['EvnReanimatCondition_disDate']); //установка даты окончания события регулярного наблюдения состояния
		this.findById('swERPEW_EvnReanimatCondition_disTime').setValue(EvnRCGridRowData['EvnReanimatCondition_disTime']); //установка времени окончания события регулярного наблюдения состояния		
		this.findById('swERPEW_RC_ArriveFrom').setValue(EvnRCGridRowData['ReanimArriveFromType_id']);	//поступил из пациента
		//Антропометрические данные//BOB - 24.01.2019
		if (EvnRCGridRowData['ReanimStageType_id'] == 2) {
			this.EvnRC_AntropometrLoud(EvnRCGridRowData['EvnReanimatCondition_disDate'],EvnRCGridRowData['EvnReanimatCondition_disTime']);
		}
		else if (EvnRCGridRowData['ReanimStageType_id'] == 1){
			var GridStoreLength = this.findById('swERPEW_EvnReanimatCondition_Grid').store.data.items.length;
			if (GridStoreLength == 1)
				this.EvnRC_AntropometrLoud(null,null);
			else{
				var NextRowData = this.findById('swERPEW_EvnReanimatCondition_Grid').store.data.items[GridStoreLength - 2].data;
				this.EvnRC_AntropometrLoud(NextRowData['EvnReanimatCondition_setDate'],NextRowData['EvnReanimatCondition_setTime']);				
			}
		}
		else
			this.EvnRC_AntropometrLoud(null,null);
		if(this.FirstConditionLoad) this.findById('swERPEW_RC_Antropometr_Panel').expand();// отрываю при первой загрузке наблюдений, !!!!убрать если по умолчанию надо оставить открытыми
			//СОСТОЯНИЕ
		this.findById('swERPEW_RC_Condition').setValue(EvnRCGridRowData['ReanimConditionType_id']);	//состояние пациента
		this.findById('swERPEW_RC_ReanimatSyndrome').setValue(EvnRCGridRowData['EvnReanimatCondition_SyndromeType']);	//реанимационный синдром //BOB - 24.01.2019
		this.findById('swERPEW_RC_ReanimatSyndromeTxt').setValue(EvnRCGridRowData['EvnReanimatCondition_SyndromeTxt']);	//синдром текстовый //BOB - 16.09.2019
		this.findById('swERPEW_RC_sofa').setValue(EvnRCGridRowData['EvnReanimatCondition_sofa']);						// значение по Sofa			//BOB - 23.04.2018
		this.findById('swERPEW_RC_apache').setValue(EvnRCGridRowData['EvnReanimatCondition_apache']);					// значение по Apache			//BOB - 23.04.2018
		this.findById('swERPEW_RC_Temperature').setValue(EvnRCGridRowData['EvnReanimatCondition_Temperature']);   //температура тела					//BOB - 28.08.2018
		if(this.FirstConditionLoad) this.findById('swERPEW_RC_Condition_Panel').expand();// отрываю при первой загрузке наблюдений, !!!!убрать если по умолчанию надо оставить открытыми
		
		this.findById('swERPEW_RC_CollectiveSurvey').setValue(EvnRCGridRowData['EvnReanimatCondition_CollectiveSurvey']);  //коллективный осмотр			//BOB - 28.08.2018
		if(this.FirstConditionLoad) this.findById('swERPEW_RC_CollectiveSurvey_Panel').collapse();// скрываю при первой загрузке наблюдений, !!!!убрать если по умолчанию надо оставить открытыми		
		this.findById('swERPEW_RC_Complaint').setValue(EvnRCGridRowData['EvnReanimatCondition_Complaint']);	//Жалобы пациента 
		if(this.FirstConditionLoad) this.findById('swERPEW_RC_Complaint_Panel').collapse();// скрываю при первой загрузке наблюдений, !!!!убрать если по умолчанию надо оставить открытыми
		//СОЗНАНИЕ
		this.findById('swERPEW_RC_Conscious').setValue(EvnRCGridRowData['ConsciousType_id']);	//Уровень сознания
		this.findById('swERPEW_RC_ConsciousTxt').setValue(EvnRCGridRowData['EvnReanimatCondition_ConsTxt']);		//Уровень сознания  - вариант пользователя  //BOB - 24.01.2019		
		this.findById('swERPEW_RC_glasgow').setValue(EvnRCGridRowData['EvnReanimatCondition_glasgow']);						// значение по Glasgow			//BOB - 16.09.2019
		this.findById('swERPEW_RC_four').setValue(EvnRCGridRowData['EvnReanimatCondition_four']);					// значение по FOUR			//BOB - 16.09.2019
		this.findById('swERPEW_RC_SpeechDisorder').setValue(EvnRCGridRowData['SpeechDisorderType_id']);	//Речь   //BOB - 24.01.2019
		this.findById('swERPEW_RC_rass').setValue(EvnRCGridRowData['EvnReanimatCondition_rass']);	// значение по RASS			//BOB - 24.01.2019
		if(this.FirstConditionLoad) this.findById('swERPEW_RC_Conscious_Panel').collapse();// скрываю при первой загрузке наблюдений, !!!!убрать если по умолчанию надо оставить открытыми
		//Зрачки вместо ГЛАЗА //BOB - 24.01.2019
		var RadioString = EvnRCGridRowData['EvnReanimatCondition_Eyes'];
		for (var i = 0; i < this.findById('swERPEW_RC_Eyes1').items.items.length; i++  )
			this.findById('swERPEW_RC_Eyes1').items.items[i].setValue(parseInt(RadioString[0]) == i ? true : false);		
		for (var i = 0; i < this.findById('swERPEW_RC_Eyes2').items.items.length; i++  )
			this.findById('swERPEW_RC_Eyes2').items.items[i].setValue(parseInt(RadioString[1]) == i ? true : false);		
		for (var i = 0; i < this.findById('swERPEW_RC_Eyes3').items.items.length; i++  )
			this.findById('swERPEW_RC_Eyes3').items.items[i].setValue(parseInt(RadioString[2]) == i ? true : false);
		if(this.FirstConditionLoad) this.findById('swERPEW_RC_Eyes').collapse();// скрываю при первой загрузке наблюдений, !!!!убрать если по умолчанию надо оставить открытыми
		//КОЖНЫЕ ПОКРОВЫ 
		this.findById('swERPEW_RC_Skin').setValue(EvnRCGridRowData['SkinType_id']);			//окраска 
		//this.findById('swERPEW_RC_SkinTxt').setVisible(EvnRCGridRowData['SkinType_id'] == 5 ? true : false);
		this.findById('swERPEW_RC_SkinTxt').setValue(EvnRCGridRowData['EvnReanimatCondition_SkinTxt']);		//окраска  - вариант пользователя
		var RadioString = EvnRCGridRowData['EvnReanimatCondition_WetTurgor']; // влажность, тургор //BOB - 24.01.2019
		for (var i = 0; i < this.findById('swERPEW_RC_WetTurgor1').items.items.length; i++  )
			this.findById('swERPEW_RC_WetTurgor1').items.items[i].setValue(parseInt(RadioString[0]) == i ? true : false);		
		for (var i = 0; i < this.findById('swERPEW_RC_WetTurgor2').items.items.length; i++  )
			this.findById('swERPEW_RC_WetTurgor2').items.items[i].setValue(parseInt(RadioString[1]) == i ? true : false);	
		this.findById('swERPEW_RC_waterlow').setValue(EvnRCGridRowData['EvnReanimatCondition_waterlow']);	// значение по ватерлоу //BOB - 24.01.2019
		this.findById('swERPEW_RC_SkinM').setValue(EvnRCGridRowData['SkinType_mid']);			//Видимые слизистые  //BOB - 24.01.2019
		//this.findById('swERPEW_RC_MucusTxt').setVisible(EvnRCGridRowData['SkinType_mid'] == 5 ? true : false);
		this.findById('swERPEW_RC_MucusTxt').setValue(EvnRCGridRowData['EvnReanimatCondition_MucusTxt']);		//Видимые слизистые  - вариант пользователя //BOB - 24.01.2019
		var RadioStringI = EvnRCGridRowData['EvnReanimatCondition_IsMicrocDist'] ? EvnRCGridRowData['EvnReanimatCondition_IsMicrocDist'] : 0; // Нарушения микроциркуляции //BOB - 24.01.2019
		for (var i = 0; i < this.findById('swERPEW_RC_IsMicrocDist').items.items.length; i++  )
			this.findById('swERPEW_RC_IsMicrocDist').items.items[i].setValue(parseInt(RadioStringI) == i ? true : false);		
		var RadioStringI = EvnRCGridRowData['EvnReanimatCondition_IsPeriphEdem'] ? EvnRCGridRowData['EvnReanimatCondition_IsPeriphEdem'] : 0; // Периферические отёки //BOB - 24.01.2019
		for (var i = 0; i < this.findById('swERPEW_RC_IsPeriphEdem').items.items.length; i++  )
			this.findById('swERPEW_RC_IsPeriphEdem').items.items[i].setValue(parseInt(RadioStringI) == i ? true : false);		
		if(this.FirstConditionLoad) this.findById('swERPEW_RC_Skin_Panel').collapse();// скрываю при первой загрузке наблюдений, !!!!убрать если по умолчанию надо оставить открытыми
		//РЕФЛЕКСЫ //BOB - 24.01.2019
		var RadioString = EvnRCGridRowData['EvnReanimatCondition_Reflexes'];
		for (var i = 0; i < this.findById('swERPEW_RC_Reflexes1').items.items.length; i++  )
			this.findById('swERPEW_RC_Reflexes1').items.items[i].setValue(parseInt(RadioString[0]) == i ? true : false);		
		for (var i = 0; i < this.findById('swERPEW_RC_Reflexes2').items.items.length; i++  )
			this.findById('swERPEW_RC_Reflexes2').items.items[i].setValue(parseInt(RadioString[1]) == i ? true : false);		
		for (var i = 0; i < this.findById('swERPEW_RC_Reflexes3').items.items.length; i++  )
			this.findById('swERPEW_RC_Reflexes3').items.items[i].setValue(parseInt(RadioString[2]) == i ? true : false);
		if(this.FirstConditionLoad) this.findById('swERPEW_RC_Reflexes').collapse();// скрываю при первой загрузке наблюдений, !!!!убрать если по умолчанию надо оставить открытыми
		//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
		//Менингеальные ЗНАКИ
		var RadioStringI = EvnRCGridRowData['EvnReanimatCondition_MeningSign'] ? EvnRCGridRowData['EvnReanimatCondition_MeningSign'] : 0; // дефекация //BOB - 24.01.2019
		for (var i = 0; i < this.findById('swERPEW_RC_MeningSign').items.items.length; i++  )
			this.findById('swERPEW_RC_MeningSign').items.items[i].setValue(parseInt(RadioStringI) == i ? true : false);
		this.findById('swERPEW_RC_MeningSignTxt').setValue(EvnRCGridRowData['EvnReanimatCondition_MeningSignTxt']);	//дефекация вариант пользователя
		if(this.FirstConditionLoad) this.findById('swERPEW_RC_MeningSign_Panel').collapse();// скрываю при первой загрузке наблюдений, !!!!убрать если по умолчанию надо оставить открытыми
		//ДЫХАНИЕ
		this.findById('swERPEW_RC_Breathing').setValue(EvnRCGridRowData['BreathingType_id']);
		this.findById('swERPEW_RC_BreathFrequency').setValue(EvnRCGridRowData['EvnReanimatCondition_BreathFrequency']);	//Частота дыхания    //BOB - 24.01.2019
		this.findById('swERPEW_RC_Saturation').setValue(EvnRCGridRowData['EvnReanimatCondition_Saturation']);	// сатурация гемоглобина	//BOB - 23.04.2018		
		base_form.findField('EvnReanimatCondition_OxygenFraction').setValue(EvnRCGridRowData['EvnReanimatCondition_OxygenFraction']);		
		base_form.findField('EvnReanimatCondition_OxygenPressure').setValue(EvnRCGridRowData['EvnReanimatCondition_OxygenPressure']);		
		base_form.findField('EvnReanimatCondition_PaOFiO').setValue(EvnRCGridRowData['EvnReanimatCondition_PaOFiO']);
		this.calcPaO2FiO2();
		this.findById('swERPEW_RC_IVLapparatus').setValue(EvnRCGridRowData['EvnReanimatCondition_IVLapparatus']);	//Дыхание / Аппарат ИВЛ
		this.findById('swERPEW_RC_IVLparameter').setValue(EvnRCGridRowData['EvnReanimatCondition_IVLparameter']);	//Дыхание / Параметры ИВЛ
		//BOB - 24.01.2019
		//Дыхание аускультативно
		var BreathAuscult_records = this.findById('swERPEW_Condition_Panel').BreathAuscult_records;
		//если для текущего наблюдения уже существует пакет записей аускультативно - гружу поля из пакета
		if (BreathAuscult_records[EvnRCGridRowData['EvnReanimatCondition_id']]) { 
			var BreathAuscultative = BreathAuscult_records[EvnRCGridRowData['EvnReanimatCondition_id']]; // то заполняю поля на форме из объекта
			for(var i in BreathAuscultative){
				if (BreathAuscultative[i]['SideType_SysNick']) {
					for (var j = 0; j < win.findById('swERPEW_RC_Auscultatory_'+BreathAuscultative[i]['SideType_SysNick']+'_1').items.items.length; j++  )
						win.findById('swERPEW_RC_Auscultatory_'+BreathAuscultative[i]['SideType_SysNick']+'_1').items.items[j].setValue(BreathAuscultative[i]['BreathAuscultative_Auscult'] == j);								
					win.findById('swERPEW_RC_AuscultTxt_'+BreathAuscultative[i]['SideType_SysNick']).setValue(BreathAuscultative[i]['BreathAuscultative_AuscultTxt']);
					for (var j = 0; j < win.findById('swERPEW_RC_Auscultatory_'+BreathAuscultative[i]['SideType_SysNick']+'_3').items.items.length; j++  )
						win.findById('swERPEW_RC_Auscultatory_'+BreathAuscultative[i]['SideType_SysNick']+'_3').items.items[j].setValue(BreathAuscultative[i]['BreathAuscultative_Rale'] == j);
					win.findById('swERPEW_RC_RaleTxt_'+BreathAuscultative[i]['SideType_SysNick']).setValue(BreathAuscultative[i]['BreathAuscultative_RaleTxt']);
					for (var j = 0; j < win.findById('swERPEW_RC_Auscultatory_'+BreathAuscultative[i]['SideType_SysNick']+'_2').items.items.length; j++  )
						win.findById('swERPEW_RC_Auscultatory_'+BreathAuscultative[i]['SideType_SysNick']+'_2').items.items[j].setValue(((BreathAuscultative[i]['BreathAuscultative_IsPleuDrain']) ? BreathAuscultative[i]['BreathAuscultative_IsPleuDrain'] : 0)  == j);
					win.findById('swERPEW_RC_PleuDrainTxt_'+BreathAuscultative[i]['SideType_SysNick']).setValue(BreathAuscultative[i]['BreathAuscultative_PleuDrainTxt']);
				}
			}
		} else { // если ещё нет пакета записей аускультативно, после установки версии это будет со старыми записями наблюдений		
			$.ajax({
				mode: "abort",
				type: "post",
				async: false,
				url: '/?c=EvnReanimatPeriod&m=GetBreathAuscultative',
				data: { EvnReanimatCondition_id: EvnRCGridRowData['EvnReanimatCondition_id'] },
				success: function(response) {
					var BreathAuscultative = Ext.util.JSON.decode(response);
					//console.log('BOB_BreathAuscultative=',BreathAuscultative); 
					if(BreathAuscultative.length > 0){ //если имеются привязанные сведения о дыхании аускультативно,  
						BreathAuscult_records[EvnRCGridRowData['EvnReanimatCondition_id']] = {}; //то создаю для текущего наблюдения пакет записей аускультативно
						for(var i in BreathAuscultative){ // и заполняю поля на форме
							if (BreathAuscultative[i]['SideType_SysNick']) {
								BreathAuscult_records[EvnRCGridRowData['EvnReanimatCondition_id']][BreathAuscultative[i]['SideType_SysNick']] = BreathAuscultative[i];
								for (var j = 0; j < win.findById('swERPEW_RC_Auscultatory_'+BreathAuscultative[i]['SideType_SysNick']+'_1').items.items.length; j++  )
									win.findById('swERPEW_RC_Auscultatory_'+BreathAuscultative[i]['SideType_SysNick']+'_1').items.items[j].setValue(BreathAuscultative[i]['BreathAuscultative_Auscult'] == j);								
								win.findById('swERPEW_RC_AuscultTxt_'+BreathAuscultative[i]['SideType_SysNick']).setValue(BreathAuscultative[i]['BreathAuscultative_AuscultTxt']);
								for (var j = 0; j < win.findById('swERPEW_RC_Auscultatory_'+BreathAuscultative[i]['SideType_SysNick']+'_3').items.items.length; j++  )
									win.findById('swERPEW_RC_Auscultatory_'+BreathAuscultative[i]['SideType_SysNick']+'_3').items.items[j].setValue(BreathAuscultative[i]['BreathAuscultative_Rale'] == j);
								win.findById('swERPEW_RC_RaleTxt_'+BreathAuscultative[i]['SideType_SysNick']).setValue(BreathAuscultative[i]['BreathAuscultative_RaleTxt']);
								for (var j = 0; j < win.findById('swERPEW_RC_Auscultatory_'+BreathAuscultative[i]['SideType_SysNick']+'_2').items.items.length; j++  )
									win.findById('swERPEW_RC_Auscultatory_'+BreathAuscultative[i]['SideType_SysNick']+'_2').items.items[j].setValue(((BreathAuscultative[i]['BreathAuscultative_IsPleuDrain']) ? BreathAuscultative[i]['BreathAuscultative_IsPleuDrain'] : 0)  == j);
								win.findById('swERPEW_RC_PleuDrainTxt_'+BreathAuscultative[i]['SideType_SysNick']).setValue(BreathAuscultative[i]['BreathAuscultative_PleuDrainTxt']);
							}
						}
					} else {	//если привязанных сведений о дыхании аускультативно нет, то очищаю поля на форме
						for(var i in SideType){
							if (SideType[i]['SideType_SysNick']) {
								for (var j = 0; j < win.findById('swERPEW_RC_Auscultatory_'+SideType[i]['SideType_SysNick']+'_1').items.items.length; j++  )
									win.findById('swERPEW_RC_Auscultatory_'+SideType[i]['SideType_SysNick']+'_1').items.items[j].setValue(false);								
								win.findById('swERPEW_RC_AuscultTxt_'+SideType[i]['SideType_SysNick']).setValue('');
								for (var j = 0; j < win.findById('swERPEW_RC_Auscultatory_'+SideType[i]['SideType_SysNick']+'_3').items.items.length; j++  )
									win.findById('swERPEW_RC_Auscultatory_'+SideType[i]['SideType_SysNick']+'_3').items.items[j].setValue(false);
								win.findById('swERPEW_RC_RaleTxt_'+SideType[i]['SideType_SysNick']).setValue('');
								for (var j = 0; j < win.findById('swERPEW_RC_Auscultatory_'+SideType[i]['SideType_SysNick']+'_2').items.items.length; j++  )
									win.findById('swERPEW_RC_Auscultatory_'+SideType[i]['SideType_SysNick']+'_2').items.items[j].setValue(false);
								win.findById('swERPEW_RC_PleuDrainTxt_'+SideType[i]['SideType_SysNick']).setValue('');
							}
						}						
					}					
				}, 
				error: function() {
					alert("При обработке запроса на сервере произошла ошибка!");
				} 
			}); 
		}
		if(this.FirstConditionLoad){ 
			this.findById('swERPEW_RC_Auscultatory_right').collapse();// скрываю при первой загрузке наблюдений, !!!!убрать если по умолчанию надо оставить открытыми
			this.findById('swERPEW_RC_Auscultatory_left').collapse();// скрываю при первой загрузке наблюдений, !!!!убрать если по умолчанию надо оставить открытыми
			this.findById('swERPEW_RC_Auscultatory_both').collapse();// скрываю при первой загрузке наблюдений, !!!!убрать если по умолчанию надо оставить открытыми
			this.findById('swERPEW_RC_Breathing_Panel').collapse();// скрываю при первой загрузке наблюдений, !!!!убрать если по умолчанию надо оставить открытыми
		}
		//СЕРДЦЕ
		var RadioString = EvnRCGridRowData['EvnReanimatCondition_HeartTones']; // тоны сердца //BOB - 24.01.2019
		for (var i = 0; i < this.findById('swERPEW_RC_HeartTones1').items.items.length; i++  )
			this.findById('swERPEW_RC_HeartTones1').items.items[i].setValue(parseInt(RadioString[0]) == i ? true : false);		
		for (var i = 0; i < this.findById('swERPEW_RC_HeartTones2').items.items.length; i++  )
			this.findById('swERPEW_RC_HeartTones2').items.items[i].setValue(parseInt(RadioString[1]) == i ? true : false);	
		this.findById('swERPEW_RC_Heart_frequency').setValue(EvnRCGridRowData['EvnReanimatCondition_HeartFrequency']);	//Частота сердечных сокращений 
		//Артериальное давление
		var Pressure = EvnRCGridRowData['EvnReanimatCondition_Pressure'].split("/");
		if ((Pressure[0]) && (!isNaN(Number.parseInt(Pressure[0])) ))		
			this.findById('swERPEW_RC_Heart_Pressure_syst').setValue(Number.parseInt(Pressure[0]));
		else
			this.findById('swERPEW_RC_Heart_Pressure_syst').setValue(0);
		if ((Pressure[1]) && (!isNaN(Number.parseInt(Pressure[1])) ))		
			this.findById('swERPEW_RC_Heart_Pressure_diast').setValue(Number.parseInt(Pressure[1]));
		else
			this.findById('swERPEW_RC_Heart_Pressure_diast').setValue(0);
		this.findById('swERPEW_RC_Heart_Pressure').calculation(false);	
		var RadioStringI = EvnRCGridRowData['EvnReanimatCondition_IsHemodStab'] ? EvnRCGridRowData['EvnReanimatCondition_IsHemodStab'] : 0; // Стабильность гемодинамики //BOB - 24.01.2019
		for (var i = 0; i < this.findById('swERPEW_RC_IsHemodStab').items.items.length; i++  )
			this.findById('swERPEW_RC_IsHemodStab').items.items[i].setValue(parseInt(RadioStringI) == i ? true : false);		
		this.findById('swERPEW_RC_Hemodynamics').setValue(EvnRCGridRowData['HemodynamicsType_id']);		//Гемодинамика
		this.findById('swERPEW_RC_HemodynamicsTxt').setValue(EvnRCGridRowData['EvnReanimatCondition_HemodynamicsTxt']);		//Гемодинамика - параметры
		if(this.FirstConditionLoad) this.findById('swERPEW_RC_Heart_Panel').collapse();// скрываю при первой загрузке наблюдений, !!!!убрать если по умолчанию надо оставить открытыми
		//ЯЗЫК
		var RadioString = EvnRCGridRowData['EvnReanimatCondition_Tongue']; // тоны сердца //BOB - 24.01.2019
		for (var i = 0; i < this.findById('swERPEW_RC_Tongue1').items.items.length; i++  )
			this.findById('swERPEW_RC_Tongue1').items.items[i].setValue(parseInt(RadioString[0]) == i ? true : false);		
		for (var i = 0; i < this.findById('swERPEW_RC_Tongue2').items.items.length; i++  )
			this.findById('swERPEW_RC_Tongue2').items.items[i].setValue(parseInt(RadioString[1]) == i ? true : false);	
		if(this.FirstConditionLoad) this.findById('swERPEW_RC_Tongue_Panel').collapse();// скрываю при первой загрузке наблюдений, !!!!убрать если по умолчанию надо оставить открытыми
		//ЖИВОТ //BOB - 24.01.2019
		var RadioString = EvnRCGridRowData['EvnReanimatCondition_Paunch'];
		for (var i = 0; i < this.findById('swERPEW_RC_Paunch1').items.items.length; i++  )
			this.findById('swERPEW_RC_Paunch1').items.items[i].setValue(parseInt(RadioString[0]) == i ? true : false);		
		for (var i = 0; i < this.findById('swERPEW_RC_Paunch2').items.items.length; i++  )
			this.findById('swERPEW_RC_Paunch2').items.items[i].setValue(parseInt(RadioString[1]) == i ? true : false);		
		for (var i = 0; i < this.findById('swERPEW_RC_Paunch3').items.items.length; i++  )
			this.findById('swERPEW_RC_Paunch3').items.items[i].setValue(parseInt(RadioString[2]) == i ? true : false);
		this.findById('swERPEW_RC_PaunchTxt').setValue(EvnRCGridRowData['EvnReanimatCondition_PaunchTxt']);	//живот текстовый
		this.findById('swERPEW_RC_Peristalsis').setValue(EvnRCGridRowData['PeristalsisType_id']);		//Перистальтика
		this.findById('swERPEW_RC_VBD').setValue(EvnRCGridRowData['EvnReanimatCondition_VBD']);		//Внутрибрюшное давление 
		win.findById('swERPEW_RC_VBD_Panel').calculation(win.findById('swERPEW_RC_VBD'), EvnRCGridRowData['EvnReanimatCondition_VBD']);
		if(this.FirstConditionLoad) this.findById('swERPEW_RC_Paunch_Panel').collapse();// скрываю при первой загрузке наблюдений, !!!!убрать если по умолчанию надо оставить открытыми
		//ДЕФЕКАЦИЯ / ДИУРЕЗ    
		var RadioStringI = EvnRCGridRowData['EvnReanimatCondition_Defecation'] ? EvnRCGridRowData['EvnReanimatCondition_Defecation'] : 0; // дефекация //BOB - 24.01.2019
		for (var i = 0; i < this.findById('swERPEW_RC_Defecation').items.items.length; i++  )
			this.findById('swERPEW_RC_Defecation').items.items[i].setValue(parseInt(RadioStringI) == i ? true : false);		
		this.findById('swERPEW_RC_DefecationTxt').setValue(EvnRCGridRowData['EvnReanimatCondition_DefecationTxt']);	//дефекация вариант пользователя
		//Диурез
		if (EvnRCGridRowData['EvnReanimatCondition_Diuresis'].length == 2)
			EvnRCGridRowData['EvnReanimatCondition_Diuresis'] += '0';	
		var RadioString = EvnRCGridRowData['EvnReanimatCondition_Diuresis'];
		for (var i = 0; i < this.findById('swERPEW_RC_Diuresis1').items.items.length; i++)
			this.findById('swERPEW_RC_Diuresis1').items.items[i].setValue(parseInt(RadioString[0]) == i ? true : false);		
		for (var i = 0; i < this.findById('swERPEW_RC_Diuresis2').items.items.length; i++)
			this.findById('swERPEW_RC_Diuresis2').items.items[i].setValue(parseInt(RadioString[1]) == i ? true : false);
		for (var i = 0; i < this.findById('swERPEW_RC_Diuresis3').items.items.length; i++)
			this.findById('swERPEW_RC_Diuresis3').items.items[i].setValue(parseInt(RadioString[2]) == i ? true : false);
		this.findById('swERPEW_RC_DiuresisVolume').setValue(EvnRCGridRowData['EvnReanimatCondition_DiuresisVolume']);  //объём диуреза				//BOB - 28.08.2018
		//Моча
		this.findById('swERPEW_RC_Urine').setValue(EvnRCGridRowData['UrineType_id']); 		
		if (EvnRCGridRowData['UrineType_id'] == 4)
			this.findById('swERPEW_RC_UrineTxt').setVisible( true );
		else 
			this.findById('swERPEW_RC_UrineTxt').setVisible( false );
		this.findById('swERPEW_RC_UrineTxt').setValue(EvnRCGridRowData['EvnReanimatCondition_UrineTxt']);			//Моча - пользовательский вариант
		if(this.FirstConditionLoad) this.findById('swERPEW_RC_DefecationDiuresis_Panel').collapse();// скрываю при первой загрузке наблюдений, !!!!убрать если по умолчанию надо оставить открытыми
		//Status_localis
		this.findById('swERPEW_RC_Status_localis').setValue(EvnRCGridRowData['EvnReanimatCondition_StatusLocalis']);	
		if(this.FirstConditionLoad) this.findById('swERPEW_RC_Status_localis_Panel').collapse();// скрываю при первой загрузке наблюдений, !!!!убрать если по умолчанию надо оставить открытыми
		//ПОДВИЖНОСТЬ
		this.findById('swERPEW_RC_LimbImmobility').setValue(EvnRCGridRowData['LimbImmobilityType_id']); 		
		var RadioString = EvnRCGridRowData['EvnReanimatCondition_MonopLoc']; // Движения в конечностях //BOB - 24.01.2019
		for (var i = 0; i < this.findById('swERPEW_RC_MonopLoc1').items.items.length; i++  )
			this.findById('swERPEW_RC_MonopLoc1').items.items[i].setValue(parseInt(RadioString[0]) == i ? true : false);		
		for (var i = 0; i < this.findById('swERPEW_RC_MonopLoc2').items.items.length; i++  )
			this.findById('swERPEW_RC_MonopLoc2').items.items[i].setValue(parseInt(RadioString[1]) == i ? true : false);
		//сила мышц по шкале MRC
		var index = win.findById('swERPEW_mrc_mrc').store.find('ScaleParameterResult_Value',EvnRCGridRowData['EvnReanimatCondition_mrc']);
		if (index > -1) {
			var mrc_text = win.findById('swERPEW_mrc_mrc').store.getAt(index).data.ScaleParameterResult_Name;
			win.findById('swERPEW_RC_mrc').setValue(EvnRCGridRowData['EvnReanimatCondition_mrc'] + ' - ' + mrc_text);			
		} else win.findById('swERPEW_RC_mrc').setValue('');
		if(this.FirstConditionLoad) this.findById('swERPEW_RC_LimbImmobility_Panel').collapse();// скрываю при первой загрузке наблюдений, !!!!убрать если по умолчанию надо оставить открытыми
		//АНАЛЬГЕЗИЯ
		this.findById('swERPEW_RC_Analgesia').setValue(EvnRCGridRowData['AnalgesiaType_id']);
		if (EvnRCGridRowData['AnalgesiaType_id'] == 3)
			this.findById('swERPEW_RC_AnalgesiaTxt').setVisible( true );
		else 
			this.findById('swERPEW_RC_AnalgesiaTxt').setVisible( false );
		this.findById('swERPEW_RC_AnalgesiaTxt').setValue(EvnRCGridRowData['EvnReanimatCondition_AnalgesiaTxt']);		//Анальгезия - пользовательский вариант
		if(this.FirstConditionLoad) this.findById('swERPEW_RC_Analgesia_Panel').collapse();// скрываю при первой загрузке наблюдений, !!!!убрать если по умолчанию надо оставить открытыми
		// НУТРИТИВНАЯ ПОДДЕРЖКА/ИНФУЗИЯ	//BOB - 28.08.2018
		this.EvnReanimatCondition_GetNutritious(EvnRCGridRowData);  //BOB - 23.09.2019
//		this.findById('swERPEW_RC_Nutritious').setValue(EvnRCGridRowData['NutritiousType_id']); 		//23.09.2019 - закомментарено
//		if (EvnRCGridRowData['NutritiousType_id'] == 4)
//			this.findById('swERPEW_RC_NutritiousTxt').setVisible( true );
//		else
//			this.findById('swERPEW_RC_NutritiousTxt').setVisible( false );
//		this.findById('swERPEW_RC_NutritiousTxt').setValue(EvnRCGridRowData['EvnReanimatCondition_NutritiousTxt']);			//Питание - пользовательский вариант
		this.findById('swERPEW_RC_InfusionVolume').setValue(EvnRCGridRowData['EvnReanimatCondition_InfusionVolume']);  //объём инфузии				//BOB - 28.08.2018
		if(this.FirstConditionLoad) this.findById('swERPEW_RC_Nutritious_Panel').collapse();// скрываю при первой загрузке наблюдений, !!!!убрать если по умолчанию надо оставить открытыми

		//неврологический статус / дополнительная информация  //BOB - 27.06.2019
		if (EvnRCGridRowData['ReanimStageType_id'] == 3) {
			this.findById('swERPEW_RC_Neurologic_Status').setValue(EvnRCGridRowData['EvnReanimatCondition_NeurologicStatus']);		//дополнительная информация
		} else {
			this.findById('swERPEW_RC_Neurologic_Status_Bis').setValue(EvnRCGridRowData['EvnReanimatCondition_NeurologicStatus']);		//неврологический статус
		}
		if(this.FirstConditionLoad) this.findById('swERPEW_RC_Neurologic_Status_Panel').collapse();// скрываю при первой загрузке наблюдений, !!!!убрать если по умолчанию надо оставить открытыми
		if(this.FirstConditionLoad) this.findById('swERPEW_RC_Neurologic_Status_Bis_Panel').collapse();// скрываю при первой загрузке наблюдений, !!!!убрать если по умолчанию надо оставить открытыми

		this.findById('swERPEW_RC_Conclusion').setValue(EvnRCGridRowData['EvnReanimatCondition_Conclusion']);		//Заключение    'swERPEW_RC_Conclusion_Panel'
		if(this.FirstConditionLoad) this.findById('swERPEW_RC_Conclusion_Panel').collapse();// скрываю при первой загрузке наблюдений, !!!!убрать если по умолчанию надо оставить открытыми

		win.findById('swERPEW_RC_Print_Doctor_FIO').setValue(EvnRCGridRowData['EvnReanimatCondition_Doctor']);//BOB - 27.09.2019  врач, подписавший наблюдение
		if(this.FirstConditionLoad) this.findById('swERPEW_RC_PrintParams_Panel').collapse();// скрываю при первой загрузке наблюдений, !!!!убрать если по умолчанию надо оставить открытыми



		//установка выидимости реквизитов и предустановки в зависимости от стадии
		win.findById('swERPEW_RC_ArriveFrom_panel').setVisible(false);  //"Поступил из"
		//win.findById('swERPEW_RC_HemodynamicsTxtPanel').setVisible(false);   //"гемодинамика-параметры"
		//win.findById('swERPEW_RC_Complaint_Panel').show(); //"жалобы"
		//win.findById('swERPEW_RC_Status_localis_Panel').show();  //"Status_localis"
		//win.findById('swERPEW_RC_Analgesia_Panel').show();  //"анальгезия"s
		//win.findById('swERPEW_RC_Nutritious_Panel').show();  //"питание"   //BOB - 28.08.2018
		//win.findById('swERPEW_RC_Diuresis').setVisible(true);  //"диурез"
		//win.findById('swERPEW_RC_Urine').showContainer();  //"моча"
		//win.findById('swERPEW_RC_UrineTxt').setVisible(true);  //"моча вариант пользователя"
		win.findById('swERPEW_RC_DiuresisVolume').showContainer();  //"объём диуреза" //BOB - 28.08.2018 
		win.findById('swERPEW_RC_DiuresisVolume_Unit').setVisible(true);  //"объём диуреза мл" //BOB - 28.08.2018 
		win.findById('swERPEW_RC_IVLapparatus').showContainer();  //"аппарат ИВЛ"
		win.findById('swERPEW_RC_IVLparameter').showContainer();  //"параметры ИВЛ"
		win.findById('swERPEW_EvnReanimatCondition_disDate_Pnl').setVisible(false);  //"дата завершения" //BOB - 13.08.2018
		win.findById('swERPEW_EvnReanimatCondition_disTime_Pnl').setVisible(false);  //"время завершения" //BOB - 13.08.2018

		//win.findById('swERPEW_RC_Neurologic_Status_Panel').setTitle('Неврологический статус');
		win.findById('swERPEW_RC_Neurologic_Status_Panel').setVisible(false); //BOB - 27.06.2019   //доп инфа
		win.findById('swERPEW_RC_Neurologic_Status_Bis_Panel').setVisible(true); //BOB - 27.06.2019  //невролог стат
		win.findById('swERPEW_RC_Conclusion_Panel').setTitle('Заключение');
		//win.findById('swERPEW_RC_CollectiveSurvey_Panel').show();  //"коллективный осмотр" //BOB - 28.08.2018 

		switch (EvnRCGridRowData['ReanimStageType_id']){
			case 1:
				win.findById('swERPEW_RC_ArriveFrom_panel').setVisible(true);
				//win.findById('swERPEW_RC_Analgesia_Panel').hide();  //"анальгезия"
				//win.findById('swERPEW_RC_Nutritious_Panel').hide();  //"питание"   //BOB - 28.08.2018
				//win.findById('swERPEW_RC_DiuresisVolume').hideContainer();  //"объём диуреза" //BOB - 28.08.2018 
				//win.findById('swERPEW_RC_DiuresisVolume_Unit').setVisible(false); //"объём диуреза мл" //BOB - 28.08.2018 
				break;
			case 2:
				//win.findById('swERPEW_RC_HemodynamicsTxtPanel').setVisible(true);
				win.findById('swERPEW_EvnReanimatCondition_disDate_Pnl').setVisible(true);  //"дата завершения" //BOB - 13.08.2018
				win.findById('swERPEW_EvnReanimatCondition_disTime_Pnl').setVisible(true);  //"время завершения" //BOB - 13.08.2018
				break;																	
			case 3:
				//win.findById('swERPEW_RC_Complaint_Panel').hide(); //"жалобы"
				//win.findById('swERPEW_RC_Status_localis_Panel').hide();  //"Status_localis"
				//win.findById('swERPEW_RC_Analgesia_Panel').hide();  //"анальгезия"
				//win.findById('swERPEW_RC_Nutritious_Panel').hide();  //"питание"   //BOB - 28.08.2018
				//win.findById('swERPEW_RC_Diuresis').setVisible(false);  //"диурез"
				//win.findById('swERPEW_RC_Urine').hideContainer();  //"моча"
				//win.findById('swERPEW_RC_UrineTxt').setVisible(false);  //"моча вариант пользователя"
				win.findById('swERPEW_RC_DiuresisVolume').hideContainer();  //"объём диуреза" //BOB - 28.08.2018 
				win.findById('swERPEW_RC_DiuresisVolume_Unit').setVisible(false); //"объём диуреза мл" //BOB - 28.08.2018 
				win.findById('swERPEW_RC_IVLapparatus').hideContainer();  //"аппарат ИВЛ"
				win.findById('swERPEW_RC_IVLparameter').hideContainer();  //"параметры ИВЛ"

				//win.findById('swERPEW_RC_Neurologic_Status_Panel').setTitle('Дополнительная информация');
				win.findById('swERPEW_RC_Neurologic_Status_Panel').setVisible(true); //BOB - 27.06.2019   //доп инфа
				win.findById('swERPEW_RC_Neurologic_Status_Bis_Panel').setVisible(false); //BOB - 27.06.2019  //невролог стат
				win.findById('swERPEW_RC_Conclusion_Panel').setTitle('Проведено');
				//win.findById('swERPEW_RC_CollectiveSurvey_Panel').hide();  //"коллективный осмотр" //BOB - 28.08.2018 
				break;																	
		}


		//установка активности/неактивности на область ввода наблюдения состояния
		// создание выборки элементов 'input', внутри панели с id 'swERPEW_Condition_Panel', возвращает с типом  Ext.Element
		//по массиву выбранных элементов
		Ext.select('input, textarea', true, 'swERPEW_Condition_Panel').each(function(el){
			var id = el.id; //выделяю параметр id из Ext.Element
			var object = win.findById(id);	//ищу в окне объект ExtJS
			if(object){ // если нахожу, то 
				object.setDisabled(EvnRCGridRowData['EvnReanimatCondition_id'] == 'New_GUID_Id' ? false : true); // делаю Disabled /Enabled
			}
		});
		//установка активности/неактивности на радиокнопки в области ввода наблюдения состояния
		for(var i in SideType){
			if(SideType[i]['SideType_SysNick']) {
				this.findById('swERPEW_RC_Auscultatory_'+SideType[i]['SideType_SysNick']+'_1').setDisabled(EvnRCGridRowData['EvnReanimatCondition_id'] == 'New_GUID_Id' ? false : true); 
				this.findById('swERPEW_RC_Auscultatory_'+SideType[i]['SideType_SysNick']+'_2').setDisabled(EvnRCGridRowData['EvnReanimatCondition_id'] == 'New_GUID_Id' ? false : true); 
				this.findById('swERPEW_RC_Auscultatory_'+SideType[i]['SideType_SysNick']+'_3').setDisabled(EvnRCGridRowData['EvnReanimatCondition_id'] == 'New_GUID_Id' ? false : true); 		
			}
		};
		this.findById('swERPEW_RC_Diuresis1').setDisabled(EvnRCGridRowData['EvnReanimatCondition_id'] == 'New_GUID_Id' ? false : true); 
		this.findById('swERPEW_RC_Diuresis2').setDisabled(EvnRCGridRowData['EvnReanimatCondition_id'] == 'New_GUID_Id' ? false : true); 
		this.findById('swERPEW_RC_Diuresis3').setDisabled(EvnRCGridRowData['EvnReanimatCondition_id'] == 'New_GUID_Id' ? false : true); 
		this.findById('swERPEW_RC_Eyes1').setDisabled(EvnRCGridRowData['EvnReanimatCondition_id'] == 'New_GUID_Id' ? false : true); 
		this.findById('swERPEW_RC_Eyes2').setDisabled(EvnRCGridRowData['EvnReanimatCondition_id'] == 'New_GUID_Id' ? false : true); 
		this.findById('swERPEW_RC_Eyes3').setDisabled(EvnRCGridRowData['EvnReanimatCondition_id'] == 'New_GUID_Id' ? false : true); 
		this.findById('swERPEW_RC_WetTurgor1').setDisabled(EvnRCGridRowData['EvnReanimatCondition_id'] == 'New_GUID_Id' ? false : true); 
		this.findById('swERPEW_RC_WetTurgor2').setDisabled(EvnRCGridRowData['EvnReanimatCondition_id'] == 'New_GUID_Id' ? false : true); 
		this.findById('swERPEW_RC_IsMicrocDist').setDisabled(EvnRCGridRowData['EvnReanimatCondition_id'] == 'New_GUID_Id' ? false : true); 
		this.findById('swERPEW_RC_IsPeriphEdem').setDisabled(EvnRCGridRowData['EvnReanimatCondition_id'] == 'New_GUID_Id' ? false : true); 
		this.findById('swERPEW_RC_Reflexes1').setDisabled(EvnRCGridRowData['EvnReanimatCondition_id'] == 'New_GUID_Id' ? false : true); 
		this.findById('swERPEW_RC_Reflexes2').setDisabled(EvnRCGridRowData['EvnReanimatCondition_id'] == 'New_GUID_Id' ? false : true); 
		this.findById('swERPEW_RC_Reflexes3').setDisabled(EvnRCGridRowData['EvnReanimatCondition_id'] == 'New_GUID_Id' ? false : true); 
		this.findById('swERPEW_RC_HeartTones1').setDisabled(EvnRCGridRowData['EvnReanimatCondition_id'] == 'New_GUID_Id' ? false : true); 
		this.findById('swERPEW_RC_HeartTones2').setDisabled(EvnRCGridRowData['EvnReanimatCondition_id'] == 'New_GUID_Id' ? false : true); 
		this.findById('swERPEW_RC_IsHemodStab').setDisabled(EvnRCGridRowData['EvnReanimatCondition_id'] == 'New_GUID_Id' ? false : true); 
		this.findById('swERPEW_RC_Tongue1').setDisabled(EvnRCGridRowData['EvnReanimatCondition_id'] == 'New_GUID_Id' ? false : true); 
		this.findById('swERPEW_RC_Tongue2').setDisabled(EvnRCGridRowData['EvnReanimatCondition_id'] == 'New_GUID_Id' ? false : true); 
		this.findById('swERPEW_RC_Paunch1').setDisabled(EvnRCGridRowData['EvnReanimatCondition_id'] == 'New_GUID_Id' ? false : true); 
		this.findById('swERPEW_RC_Paunch2').setDisabled(EvnRCGridRowData['EvnReanimatCondition_id'] == 'New_GUID_Id' ? false : true); 
		this.findById('swERPEW_RC_Paunch3').setDisabled(EvnRCGridRowData['EvnReanimatCondition_id'] == 'New_GUID_Id' ? false : true); 
		this.findById('swERPEW_RC_Defecation').setDisabled(EvnRCGridRowData['EvnReanimatCondition_id'] == 'New_GUID_Id' ? false : true); 
		this.findById('swERPEW_RC_MonopLoc1').setDisabled(EvnRCGridRowData['EvnReanimatCondition_id'] == 'New_GUID_Id' ? false : true); 
		this.findById('swERPEW_RC_MonopLoc2').setDisabled(EvnRCGridRowData['EvnReanimatCondition_id'] == 'New_GUID_Id' ? false : true); 
		this.findById('swERPEW_RC_MeningSign').setDisabled(EvnRCGridRowData['EvnReanimatCondition_id'] == 'New_GUID_Id' ? false : true); 

		this.EvnReanimatCondition_ButtonManag(this,(EvnRCGridRowData['EvnReanimatCondition_id'] != 'New_GUID_Id'));  //BOB - 11.02.2019
		
		//Поля с реквизитами из шкал
		this.findById('swERPEW_RC_sofa').setDisabled(true);	// значение по Sofa			//BOB - 23.04.2018
		this.findById('swERPEW_RC_apache').setDisabled(true);	// значение по Apache			//BOB - 23.04.2018
		this.findById('swERPEW_RC_rass').setDisabled(true);	// значение по RASS			//BOB - 24.01.2018
		this.findById('swERPEW_RC_waterlow').setDisabled(true);	// значение по waterlow			//BOB - 24.01.2018
		this.findById('swERPEW_RC_mrc').setDisabled(true);	// значение по waterlow			//BOB - 24.01.2018
		this.findById('swERPEW_RC_glasgow').setDisabled(true);	// значение по Glasgow			//BOB - 16.09.2019
		this.findById('swERPEW_RC_four').setDisabled(true);	// значение по FOUR			//BOB - 16.09.2019
		//Поля антропометрических данных
		this.findById('swERPEW_RC_Height').setDisabled(true);	// рост			//BOB - 11.04.2019
		this.findById('swERPEW_RC_Weight').setDisabled(true);	// вес			//BOB - 11.04.2019
		this.findById('swERPEW_RC_IMT').setDisabled(true);	// индекс веса тела			//BOB - 11.04.2019
		//Поля питания из мероприятия
		this.findById('swERPEW_RC_Nutritious').setDisabled(true);	// тип питания		//BOB - 23.09.2019
		this.findById('swERPEW_RC_NutritiousTxt').setDisabled(true);	// питание - вариант пользователя		//BOB - 23.09.2019
		this.findById('swERPEW_RC_NutritVol').setDisabled(true);	// объём питания		//BOB - 23.09.2019
		this.findById('swERPEW_RC_NutritEnerg').setDisabled(true);	// энергетическая ценность питания		//BOB - 23.09.2019
		//Параметры печати
		this.findById('swERPEW_RC_Print_Patient_FIO').setDisabled(false);	// чекбокс печатать ФИО пациента в дневнике		//BOB - 27.09.2019

		this.FirstConditionLoad = false;
	},

	//BOB - 23.09.2019
	//извлечение сведений о мероприятии - питании и отображение в наблюдениях
	EvnReanimatCondition_GetNutritious: function(EvnRCGridRowData) {
		//console.log('BOB_EvnReanimatCondition_GetNutritious_EvnRCGridRowData=',EvnRCGridRowData);  //BOB - 20.11.2019;

		var win = this;

		win.findById('swERPEW_RC_Nutritious').setValue(null);
		win.findById('swERPEW_RC_NutritiousTxt').setVisible(false);
		win.findById('swERPEW_RC_NutritiousTxt').setValue(null);
		win.findById('swERPEW_RC_NutritVol').setValue(null);
		win.findById('swERPEW_RC_NutritEnerg').setValue(null);

		if (EvnRCGridRowData['EvnReanimatCondition_setDate'] != '' && EvnRCGridRowData['EvnReanimatCondition_setTime'] != ''  && EvnRCGridRowData['ReanimStageType_id'] == 2){


			//дата-время начала наблюдения
			var RC_setDT = EvnRCGridRowData['EvnReanimatCondition_setDate'];
			var Time = EvnRCGridRowData['EvnReanimatCondition_setTime'];
			RC_setDT.setHours(parseInt(Time.substr(0, 2)), parseInt(Time.substr(3, 2)));

			//дата-время конца наблюдения
			var RC_disDT = EvnRCGridRowData['EvnReanimatCondition_disDate'];
			if (RC_disDT == '' || EvnRCGridRowData['EvnReanimatCondition_disTime'] == '')
				RC_disDT = new Date(2999, 0, 1);
			else {
				Time = EvnRCGridRowData['EvnReanimatCondition_disTime'];
				RC_disDT.setHours(parseInt(Time.substr(0, 2)), parseInt(Time.substr(3, 2)));
			}

			var RA_setDT;  //дата-время начала мероприятия-питания
			var RA_disDT;  //дата-время концап мероприятия-питания

			GetNutritious_inside = function(from) {

				//цикл по записям в store грида мероприятий, ищем позднее питание пересекающееся периодами с текущим наблюдением
				var ReanimatActionStore = win.findById('swERPEW_ReanimatAction_Grid').getStore().data.items;
				for(var i in ReanimatActionStore){
					if ((ReanimatActionStore[i].data) && (ReanimatActionStore[i].data.EvnReanimatAction_id != "New_GUID_Id")){
						if(ReanimatActionStore[i].data.ReanimatActionType_SysNick == "nutrition"){
							//console.log('BOB_ReanimatActionStore[i].data=',ReanimatActionStore[i].data);

							RA_setDT = ReanimatActionStore[i].data['EvnReanimatAction_setDate'];
							Time = ReanimatActionStore[i].data['EvnReanimatAction_setTime'];
							RA_setDT.setHours(parseInt(Time.substr(0, 2)), parseInt(Time.substr(3, 2)));

							if((ReanimatActionStore[i].data['EvnReanimatAction_disDate'] == '') || (ReanimatActionStore[i].data['EvnReanimatAction_disTime'] == ''))
								RA_disDT = new Date(2999, 0, 1);
							else {
								RA_disDT = ReanimatActionStore[i].data['EvnReanimatAction_disDate'];
								Time = ReanimatActionStore[i].data['EvnReanimatAction_disTime'];
								RA_disDT.setHours(parseInt(Time.substr(0, 2)), parseInt(Time.substr(3, 2)));
							}

							//console.log('BOB_RA_setDT=',RA_setDT);
							//console.log('BOB_RA_disDT=',RA_disDT);
							// ЕСЛИ дата начала питания меньше даты конца наблюдения И дата конца питания больше даты начала наблюдения т.е. пересечение периодов
							if (RA_setDT < RC_disDT && RA_disDT > RC_setDT) {
								//Записываю реквизиты питания из текущей записи мероприятий в текущую запись наблюдений
								win.findById('swERPEW_RC_Nutritious').setValue(ReanimatActionStore[i].data['UslugaComplex_id']);
								win.findById('swERPEW_RC_NutritiousTxt').setVisible(ReanimatActionStore[i].data['UslugaComplex_id'] == 4);
								win.findById('swERPEW_RC_NutritiousTxt').setValue(ReanimatActionStore[i].data['EvnReanimatAction_MethodTxt']);
								win.findById('swERPEW_RC_NutritVol').setValue(ReanimatActionStore[i].data['EvnReanimatAction_NutritVol']);
								win.findById('swERPEW_RC_NutritEnerg').setValue(ReanimatActionStore[i].data['EvnReanimatAction_NutritEnerg']);
								break;
							}
						}
					}
				}
			};

			//если панель мероприятий ещё не открывалась И в store грида мероприятий пусто (туда могут попасть записи без открытия панели  -  вот прямо здесь это и делается)
			if(!win.findById('swERPEW_ReanimatAction_Panel').isLoaded && win.findById('swERPEW_ReanimatAction_Grid').store.data.items.length == 0){
				//загрузка грида мероприятий
				win.findById('swERPEW_ReanimatAction_Grid').getStore().load({
					params:{
						EvnReanimatAction_pid: win.EvnReanimatPeriod_id
					},
					callback: function(records, options, success) {
						GetNutritious_inside('callback');
					}
				});
			}
			else {
				GetNutritious_inside('store');
			}

		}

	},

	//добавление регулярного наблюдения состояния
	EvnReanimatCondition_Add: function() {
		var win = this;

		//BOB - 24.01.2020  //если младенцы
		if (this.isNeonatal(this.findById('swERPEW_ReanimatAgeGroup').getValue()))  {
			if (getWnd('swEvnNeonatalSurveyEditWindow').isVisible() && getWnd('swEvnNeonatalSurveyEditWindow').changedDatas) {
				Ext.Msg.alert(langs('Сообщение'), langs('Окно Наблюдение состояния младенца уже открыто<br> и в нём имеются несохранённые изменния!'));
				return false;
			}
			var params = {
				ENSEW_title: langs('Наблюдение состояния младенца'),
				action: 'add',
				fromObject: this,
				EvnNeonatalSurvey_id: null,
				pers_data: this.pers_data,
				EvnNeonatalSurvey_pid: this.EvnReanimatPeriod_id,
				EvnNeonatalSurvey_rid: this.EvnReanimatPeriod_rid,
				ParentObject: 'EvnReanimatPeriod',
				userMedStaffFact: this.userMedStaffFact,
				ARMType: 'reanimation',
				FirstConditionLoad: this.FirstConditionLoad
			};

			params.Callback = function() {
				console.log('BOB_RP_saved=', 'kjhlkjhkjhlk');
			};
			getWnd('swEvnNeonatalSurveyEditWindow').show(params);
			this.EvnReanimatCondition_ButtonManag(this,false);  //BOB - 17.03.2020
			
		}
		else {


			this.findById('swERPEW_EvnReanimatConditionStage').setValue(2); // устанавливаю в комбо этап - "дневник"
			var curDate = getValidDT(getGlobalOptions().date, ''); // считываю из глобальных параметров текущую дату
			
	//		this.findById('swERPEW_EvnReanimatCondition_setDate').setValue(curDate);// в дату события регулярного наблюдения состояния - текущую дату
	//		this.findById('swERPEW_EvnReanimatCondition_setTime').setValue(''); // во время события регулярного наблюдения состояния - пустоту
			this.findById('swERPEW_EvnReanimatCondition_disDate').setValue(curDate);// в дату окончания события регулярного наблюдения состояния - текущую дату
			this.findById('swERPEW_EvnReanimatCondition_disTime').setValue(''); // во время окончания события регулярного наблюдения состояния - пустоту
			
			var SpO2 = ''; 
			//var Nutritious = '';//  0;
			var Sofa = '';
			var Apache = '';
			var rass = '';
			var waterlow = '';
			var IVLApparat = '';
			var IVLParameter_ = '';
			var mrc = '';
			var Glasgow = ''; //BOB - 16.09.2019
			var FOUR = ''; //BOB - 16.09.2019
			
			$.ajax({
				mode: "abort",
				type: "post",
				async: false,
				url: '/?c=EvnReanimatPeriod&m=getDataToNewCondition',
				data: { EvnReanimatCondition_pid: win.EvnReanimatPeriod_id},
				success: function(response) {
					var DataToNewCondition = Ext.util.JSON.decode(response);
					console.log('BOB_DataToNewCondition=',DataToNewCondition); 
					if (DataToNewCondition.IVLParameter.length > 0){
						var IVLParameter = DataToNewCondition.IVLParameter[0];
						IVLApparat = IVLParameter['IVLParameter_Apparat'];
						var IVLRegim_SysNick = IVLParameter['IVLRegim_SysNick'];					
						IVLParameter_ = IVLRegim_SysNick.toUpperCase().replace('_', ' ');
						if (IVLParameter['IVLParameter_TubeDiam'] != ".00" ) IVLParameter_ += ', D=' + Math.round(IVLParameter['IVLParameter_TubeDiam']*10)/10 + 'мм';
						if (IVLParameter['IVLParameter_FiO2'] > 0 ) IVLParameter_ += ', FiO2=' + IVLParameter['IVLParameter_FiO2'] + '%';
						if (IVLParameter['IVLParameter_PcentMinVol'] > 0 ) IVLParameter_ +=  IVLRegim_SysNick.inlist(['asv']) ? ', % Мин. Объ=' + IVLParameter['IVLParameter_PcentMinVol'] + '%' : '';
						if (IVLParameter['IVLParameter_TwoASVMax'] > 0 ) IVLParameter_ +=  IVLRegim_SysNick.inlist(['asv']) ? ', Два ASV макс=' + IVLParameter['IVLParameter_TwoASVMax'] + 'см вд ст' : '';
						if (IVLParameter['IVLParameter_FrequSet'] > 0 ) IVLParameter_ +=  IVLRegim_SysNick.inlist(['cmv_vc','cmv_pc','simv_vc','simv_pc']) ? ', f=' + IVLParameter['IVLParameter_FrequSet'] + 'раз/мин' : '';
						if (IVLParameter['IVLParameter_VolInsp'] > 0 ) IVLParameter_ +=  IVLRegim_SysNick.inlist(['cmv_vc','simv_vc']) ? ', Vinsp=' + IVLParameter['IVLParameter_VolInsp'] + 'мл' : '';
						if (IVLParameter['IVLParameter_PressInsp'] > 0 ) IVLParameter_ +=  IVLRegim_SysNick.inlist(['cmv_pc','simv_pc']) ? ', Pinsp=' + IVLParameter['IVLParameter_PressInsp'] + 'см вд ст' : '';
						if (IVLParameter['IVLParameter_PressSupp'] > 0 ) IVLParameter_ +=  IVLRegim_SysNick.inlist(['simv_vc','simv_pc','psv']) ? ', PS=' + IVLParameter['IVLParameter_PressSupp'] + 'см вд ст' : '';
						if (IVLParameter['IVLParameter_FrequTotal'] > 0 ) IVLParameter_ += ', f_total=' + IVLParameter['IVLParameter_FrequTotal'] + 'раз/мин';
						if (IVLParameter['IVLParameter_VolTe'] > 0 ) IVLParameter_ += ', Vte=' + IVLParameter['IVLParameter_VolTe'] + 'мл';
						if (IVLParameter['IVLParameter_VolE'] > 0 ) IVLParameter_ += ', Ve=' + IVLParameter['IVLParameter_VolE'] + 'мл/мин';
						if (IVLParameter['IVLParameter_TinTet'] != "0:0" ) IVLParameter_ += ', t_in-t_exp=' + IVLParameter['IVLParameter_TinTet'];
						if (IVLParameter['IVLParameter_VolTrig'] > 0 ) IVLParameter_ += ', Vtrig=' + IVLParameter['IVLParameter_VolTrig'] + 'мл/мин';
						else if (IVLParameter['IVLParameter_PressTrig'] > 0 )  IVLParameter_ += ', Ptrig=' + IVLParameter['IVLParameter_PressTrig'] + 'см вд ст';
						if (IVLParameter['IVLParameter_PEEP'] > 0 ) IVLParameter_ += ', PEEP=' + IVLParameter['IVLParameter_PEEP'] + 'см вд ст';
					}
					if (DataToNewCondition.SpO2.length > 0)
						SpO2 = DataToNewCondition.SpO2[0]['EvnReanimatAction_ObservValue'];
	//				if (DataToNewCondition.Nutritious.length > 0)						//23.09.2019 - закомментарено
	//					Nutritious = DataToNewCondition.Nutritious[0]['NutritiousType_id'];
					if (DataToNewCondition.Sofa.length > 0)
						Sofa = DataToNewCondition.Sofa[0]['EvnScale_Result'];
					if (DataToNewCondition.Apache.length > 0)
						Apache = DataToNewCondition.Apache[0]['EvnScale_Result'];
					if (DataToNewCondition.rass.length > 0)
						rass = DataToNewCondition.rass[0]['EvnScale_Result'];     //BOB - 24.01.2019
					if (DataToNewCondition.waterlow.length > 0)
						waterlow = DataToNewCondition.waterlow[0]['EvnScale_Result'];     //BOB - 24.01.2019
					if (DataToNewCondition.mrc.length > 0)
						mrc = DataToNewCondition.mrc[0]['EvnScale_Result'];     //BOB - 24.01.2019
					if (DataToNewCondition.Glasgow.length > 0)
						Glasgow = DataToNewCondition.Glasgow[0]['EvnScale_Result'];     //BOB - 16.09.2019
					if (DataToNewCondition.FOUR.length > 0)
						FOUR = DataToNewCondition.FOUR[0]['EvnScale_Result'];     //BOB - 16.09.2019
					if (DataToNewCondition.LastCondit.length > 0) {
						win.findById('swERPEW_EvnReanimatCondition_setDate').setValue(DataToNewCondition.LastCondit[0]['EvnReanimatCondition_disDate']);// в дату события регулярного наблюдения состояния - текущую дату
						win.findById('swERPEW_EvnReanimatCondition_setTime').setValue(DataToNewCondition.LastCondit[0]['EvnReanimatCondition_disTime']); // во время события регулярного наблюдения состояния - пустоту
					}
					
				}, 
				error: function() {
					alert("При обработке запроса на сервере произошла ошибка!");
				} 
			});	

			//console.log('BOB_DataToNewCondition_2=',SpO2 + ' ' + Nutritious + ' ' + Sofa + ' ' + Apache);
			//BOB - 24.01.2019 формирование пустого объекта дыхания аускультативно
			var SideType =  this.SideType;
			var BreathAuscult_records = this.findById('swERPEW_Condition_Panel').BreathAuscult_records;
			BreathAuscult_records['New_GUID_Id'] = {}; 
			for(var i in SideType){
				if (SideType[i]['SideType_SysNick']) {
					BreathAuscult_records['New_GUID_Id'][SideType[i]['SideType_SysNick']] = {
						BreathAuscultative_id: null,
						EvnReanimatCondition_id: 'New_GUID_Id',
						BreathAuscultative_Auscult: 0,
						BreathAuscultative_AuscultTxt: '',
						BreathAuscultative_Rale: 0,
						BreathAuscultative_RaleTxt: '',
						BreathAuscultative_IsPleuDrain: null,
						BreathAuscultative_PleuDrainTxt: '',
						SideType_id: SideType[i]['SideType_id'],
						SideType_SysNick: SideType[i]['SideType_SysNick'],
						BA_RecordStatus: 0
					};
				}
			}


			//BOB - 27.09.2019  врач, подписавший наблюдение
			var index = win.findById('swERPEW_RC_Print_Doctor_FIO').store.find('MedPersonal_id',this.MedPersonal_id);
			var EvnReanimatCondition_Doctor = '';
			if (index > -1) {
				win.findById('swERPEW_RC_Print_Doctor_FIO').setValue(this.MedPersonal_id);
				EvnReanimatCondition_Doctor = this.MedPersonal_id;
			} else win.findById('swERPEW_RC_Print_Doctor_FIO').setValue('');


			//добавление строки в грид
			var Grid = this.findById('swERPEW_EvnReanimatCondition_Grid');
			Grid.store.insert(0, new Ext.data.Record({
				EvnReanimatCondition_id: 'New_GUID_Id',
				EvnReanimatCondition_pid:  this.EvnReanimatPeriod_id,
				Person_id:  this.Person_id,
				PersonEvn_id:  this.PersonEvn_id,
				Server_id: this.Server_id,
				EvnReanimatCondition_setDate: win.findById('swERPEW_EvnReanimatCondition_setDate').getValue(),
				EvnReanimatCondition_setTime: win.findById('swERPEW_EvnReanimatCondition_setTime').getValue(),
				EvnReanimatCondition_disDate: curDate,
				EvnReanimatCondition_disTime: '',
				ReanimStageType_id: 2,
				Stage_Name: 'Регулярный дневник',
				//ReanimConditionType_id: '',
				Condition_Name: '',
				
				EvnReanimatCondition_Auscultatory: '000',
				//EvnReanimatCondition_Pressure: '',
				EvnReanimatCondition_HeartFrequency: 0,
	//			AnalgesiaType_id: 0,
				EvnReanimatCondition_Diuresis: '000',
				EvnReanimatCondition_sofa: Sofa,
				EvnReanimatCondition_apache: Apache,
				EvnReanimatCondition_Saturation: SpO2,
				//NutritiousType_id: Nutritious,			//23.09.2019 - закомментарено
				EvnReanimatCondition_IVLapparatus: IVLApparat, //BOB - 24.01.2019
				EvnReanimatCondition_IVLparameter: IVLParameter_, //BOB - 24.01.2019
				EvnReanimatCondition_rass: rass,					//BOB - 24.01.2019
				EvnReanimatCondition_Eyes: '000',					//BOB - 24.01.2019
				EvnReanimatCondition_WetTurgor: '00',				//BOB - 24.01.2019
				EvnReanimatCondition_waterlow: waterlow,				//BOB - 24.01.2019
				EvnReanimatCondition_Reflexes: '000',					//BOB - 24.01.2019
				EvnReanimatCondition_BreathFrequency: 0,				//BOB - 24.01.2019
				EvnReanimatCondition_HeartTones: '00',				//BOB - 24.01.2019
				EvnReanimatCondition_Pressure: '',				//BOB - 24.01.2019
				EvnReanimatCondition_Tongue: '00',				//BOB - 24.01.2019
				EvnReanimatCondition_Paunch: '000',					//BOB - 24.01.2019
				EvnReanimatCondition_MonopLoc: '00',				//BOB - 24.01.2019
				EvnReanimatCondition_mrc: mrc,				        //BOB - 24.01.2019
				EvnReanimatCondition_glasgow: Glasgow,				//BOB - 16.09.2019
				EvnReanimatCondition_four: FOUR,				        //BOB - 16.09.2019
				EvnReanimatCondition_Doctor: EvnReanimatCondition_Doctor //BOB - 27.09.2019
			}));
			Grid.getSelectionModel().selectRow(0); 	//установка выбранности на первой строке грида 
			this.EvnReanimatCondition_ButtonManag(this,false);  //BOB - 11.02.2019
		}
	},

	//добавление регулярного наблюдения состояния на основе существующего
	EvnReanimatCondition_Copy: function() {
		var win = this;
		var Grid = this.findById('swERPEW_EvnReanimatCondition_Grid');
		

		
		//выбранная строка в гриде событий регулярного наблюдения состояния
		var EvnRCGridRowDataDst = Grid.getSelectionModel().getSelected().data; 
		if (Ext.isEmpty(EvnRCGridRowDataDst['EvnReanimatCondition_id'])) {
			Ext.MessageBox.alert('Внимание!', 'Не выбрана строка в списке наблюдений! ');
			return false;
		}
		if (EvnRCGridRowDataDst['ReanimStageType_id'] != 2){
			Ext.MessageBox.alert('Внимание!', 'Копировать можно только этап Регулярный дневник! ');
			return false;
		}

		//BOB - 16.03.2020      //если младенцы
		if (this.isNeonatal(this.findById('swERPEW_ReanimatAgeGroup').getValue()))  {
			if (getWnd('swEvnNeonatalSurveyEditWindow').isVisible() && getWnd('swEvnNeonatalSurveyEditWindow').changedDatas) {
				Ext.Msg.alert(langs('Сообщение'), langs('Окно Наблюдение состояния младенца уже открыто<br> и в нём имеются несохранённые изменния!'));
				return false;
			}
			var params = {
				ENSEW_title: langs('Наблюдение состояния младенца'),
				action: 'add',
				fromObject: this,
				EvnNeonatalSurvey_id: EvnRCGridRowDataDst['EvnReanimatCondition_id'],
				pers_data: this.pers_data,
				EvnNeonatalSurvey_pid: this.EvnReanimatPeriod_id,
				EvnNeonatalSurvey_rid: this.EvnReanimatPeriod_rid,
				ParentObject: 'EvnReanimatPeriod',
				userMedStaffFact: this.userMedStaffFact,
				ARMType: 'reanimation',
				FirstConditionLoad: this.FirstConditionLoad
			};
			params.Callback = function() {
				console.log('BOB_RP_saved=', 'kjhlkjhkjhlk');
			};
			getWnd('swEvnNeonatalSurveyEditWindow').show(params);
			this.EvnReanimatCondition_ButtonManag(this,false);  //BOB - 17.03.2019
			
		}
		else {  //взрослые
			//формируем начало и конец нового периода
			var curDate = getValidDT(getGlobalOptions().date, ''); // считываю из глобальных параметров текущую дату
					
			var SpO2 = ''; 
			var Nutritious = 0;
			var Sofa = '';
			var Apache = '';
			var rass = '';
			var waterlow = '';
			var IVLApparat = '';
			var IVLParameter_ = '';
			var mrc = '';
			var Glasgow = ''; //BOB - 16.09.2019
			var FOUR = ''; //BOB - 16.09.2019
			$.ajax({
				mode: "abort",
				type: "post",
				async: false,
				url: '/?c=EvnReanimatPeriod&m=getDataToNewCondition',
				data: { EvnReanimatCondition_pid: win.EvnReanimatPeriod_id},
				success: function(response) {
					var DataToNewCondition = Ext.util.JSON.decode(response);
					console.log('BOB_DataToNewCondition=',DataToNewCondition); 
					if (DataToNewCondition.IVLParameter.length > 0){
						var IVLParameter = DataToNewCondition.IVLParameter[0];
						IVLApparat = IVLParameter['IVLParameter_Apparat'];
						var IVLRegim_SysNick = IVLParameter['IVLRegim_SysNick'];					
						IVLParameter_ = IVLRegim_SysNick.toUpperCase().replace('_', ' ');
						if (IVLParameter['IVLParameter_TubeDiam'] != ".00" ) IVLParameter_ += ', D=' + Math.round(IVLParameter['IVLParameter_TubeDiam']*10)/10 + 'мм';
						if (IVLParameter['IVLParameter_FiO2'] > 0 ) IVLParameter_ += ', FiO2=' + IVLParameter['IVLParameter_FiO2'] + '%';
						if (IVLParameter['IVLParameter_PcentMinVol'] > 0 ) IVLParameter_ +=  IVLRegim_SysNick.inlist(['asv']) ? ', % Мин. Объ=' + IVLParameter['IVLParameter_PcentMinVol'] + '%' : '';
						if (IVLParameter['IVLParameter_TwoASVMax'] > 0 ) IVLParameter_ +=  IVLRegim_SysNick.inlist(['asv']) ? ', Два ASV макс=' + IVLParameter['IVLParameter_TwoASVMax'] + 'см вд ст' : '';
						if (IVLParameter['IVLParameter_FrequSet'] > 0 ) IVLParameter_ +=  IVLRegim_SysNick.inlist(['cmv_vc','cmv_pc','simv_vc','simv_pc']) ? ', f=' + IVLParameter['IVLParameter_FrequSet'] + 'раз/мин' : '';
						if (IVLParameter['IVLParameter_VolInsp'] > 0 ) IVLParameter_ +=  IVLRegim_SysNick.inlist(['cmv_vc','simv_vc']) ? ', Vinsp=' + IVLParameter['IVLParameter_VolInsp'] + 'мл' : '';
						if (IVLParameter['IVLParameter_PressInsp'] > 0 ) IVLParameter_ +=  IVLRegim_SysNick.inlist(['cmv_pc','simv_pc']) ? ', Pinsp=' + IVLParameter['IVLParameter_PressInsp'] + 'см вд ст' : '';
						if (IVLParameter['IVLParameter_PressSupp'] > 0 ) IVLParameter_ +=  IVLRegim_SysNick.inlist(['simv_vc','simv_pc','psv']) ? ', PS=' + IVLParameter['IVLParameter_PressSupp'] + 'см вд ст' : '';
						if (IVLParameter['IVLParameter_FrequTotal'] > 0 ) IVLParameter_ += ', f_total=' + IVLParameter['IVLParameter_FrequTotal'] + 'раз/мин';
						if (IVLParameter['IVLParameter_VolTe'] > 0 ) IVLParameter_ += ', Vte=' + IVLParameter['IVLParameter_VolTe'] + 'мл';
						if (IVLParameter['IVLParameter_VolE'] > 0 ) IVLParameter_ += ', Ve=' + IVLParameter['IVLParameter_VolE'] + 'мл/мин';
						if (IVLParameter['IVLParameter_TinTet'] != "0:0" ) IVLParameter_ += ', t_in-t_exp=' + IVLParameter['IVLParameter_TinTet'];
						if (IVLParameter['IVLParameter_VolTrig'] > 0 ) IVLParameter_ += ', Vtrig=' + IVLParameter['IVLParameter_VolTrig'] + 'мл/мин';
						else if (IVLParameter['IVLParameter_PressTrig'] > 0 )  IVLParameter_ += ', Ptrig=' + IVLParameter['IVLParameter_PressTrig'] + 'см вд ст';
						if (IVLParameter['IVLParameter_PEEP'] > 0 ) IVLParameter_ += ', PEEP=' + IVLParameter['IVLParameter_PEEP'] + 'см вд ст';
					}
					if (DataToNewCondition.SpO2.length > 0)
						SpO2 = DataToNewCondition.SpO2[0]['EvnReanimatAction_ObservValue'];
	//				if (DataToNewCondition.Nutritious.length > 0)							//23.09.2019 - закомментарено
	//					Nutritious = DataToNewCondition.Nutritious[0]['NutritiousType_id'];
					if (DataToNewCondition.Sofa.length > 0)
						Sofa = DataToNewCondition.Sofa[0]['EvnScale_Result'];
					if (DataToNewCondition.Apache.length > 0)
						Apache = DataToNewCondition.Apache[0]['EvnScale_Result'];
					if (DataToNewCondition.rass.length > 0)
						rass = DataToNewCondition.rass[0]['EvnScale_Result'];     //BOB - 24.01.2019
					if (DataToNewCondition.waterlow.length > 0)
						waterlow = DataToNewCondition.waterlow[0]['EvnScale_Result'];     //BOB - 24.01.2019
					if (DataToNewCondition.mrc.length > 0)
						mrc = DataToNewCondition.mrc[0]['EvnScale_Result'];     //BOB - 24.01.2019
					if (DataToNewCondition.Glasgow.length > 0)
						Glasgow = DataToNewCondition.Glasgow[0]['EvnScale_Result'];     //BOB - 16.09.2019
					if (DataToNewCondition.FOUR.length > 0)
						FOUR = DataToNewCondition.FOUR[0]['EvnScale_Result'];     //BOB - 16.09.2019
					if (DataToNewCondition.LastCondit.length > 0) {
						win.findById('swERPEW_EvnReanimatCondition_setDate').setValue(DataToNewCondition.LastCondit[0]['EvnReanimatCondition_disDate']);// в дату события регулярного наблюдения состояния - текущую дату
						win.findById('swERPEW_EvnReanimatCondition_setTime').setValue(DataToNewCondition.LastCondit[0]['EvnReanimatCondition_disTime']); // во время события регулярного наблюдения состояния - пустоту
					}				
				}, 
				error: function() {
					alert("При обработке запроса на сервере произошла ошибка!");
				} 
			});	
			//BOB - 24.01.2019 формирование пустого объекта дыхания аускультативно
			var SideType =  this.SideType;
			var BreathAuscult_records = this.findById('swERPEW_Condition_Panel').BreathAuscult_records;
			BreathAuscult_records['New_GUID_Id'] =  Ext.util.JSON.decode(Ext.util.JSON.encode(BreathAuscult_records[EvnRCGridRowDataDst['EvnReanimatCondition_id']])); 
			for(var i in SideType){
				if (SideType[i]['SideType_SysNick']) {
					BreathAuscult_records['New_GUID_Id'][SideType[i]['SideType_SysNick']]['BreathAuscultative_id'] = null;
					BreathAuscult_records['New_GUID_Id'][SideType[i]['SideType_SysNick']]['EvnReanimatCondition_id'] = 'New_GUID_Id';
					BreathAuscult_records['New_GUID_Id'][SideType[i]['SideType_SysNick']]['BA_RecordStatus'] = 0;
				}
			}
			//BOB - 27.09.2019  врач, подписавший наблюдение
			var index = win.findById('swERPEW_RC_Print_Doctor_FIO').store.find('MedPersonal_id',this.MedPersonal_id);
			var EvnReanimatCondition_Doctor = '';
			if (index > -1) {
				win.findById('swERPEW_RC_Print_Doctor_FIO').setValue(this.MedPersonal_id);
				EvnReanimatCondition_Doctor = this.MedPersonal_id;
			} else win.findById('swERPEW_RC_Print_Doctor_FIO').setValue('');

			//добавление строки в грид
			Grid.store.insert(0, new Ext.data.Record({
				EvnReanimatCondition_id: 'New_GUID_Id',
				EvnReanimatCondition_pid:  this.EvnReanimatPeriod_id,
				Person_id:  this.Person_id,
				PersonEvn_id:  this.PersonEvn_id,
				Server_id: this.Server_id,
				EvnReanimatCondition_setDate: win.findById('swERPEW_EvnReanimatCondition_setDate').getValue(),
				EvnReanimatCondition_setTime: win.findById('swERPEW_EvnReanimatCondition_setTime').getValue(),
				EvnReanimatCondition_disDate: curDate,
				EvnReanimatCondition_disTime: '',
				
				ReanimStageType_id: EvnRCGridRowDataDst['ReanimStageType_id'],
				Stage_Name: EvnRCGridRowDataDst['Stage_Name'],
				ReanimConditionType_id: EvnRCGridRowDataDst['ReanimConditionType_id'],
				Condition_Name: EvnRCGridRowDataDst['Condition_Name'],
				EvnReanimatCondition_Complaint: EvnRCGridRowDataDst['EvnReanimatCondition_Complaint'],
				SkinType_id: EvnRCGridRowDataDst['SkinType_id'],
				EvnReanimatCondition_SkinTxt: EvnRCGridRowDataDst['EvnReanimatCondition_SkinTxt'],
				ConsciousType_id: EvnRCGridRowDataDst['ConsciousType_id'],
				BreathingType_id: EvnRCGridRowDataDst['BreathingType_id'],
				EvnReanimatCondition_IVLapparatus: IVLApparat, //EvnRCGridRowDataDst['EvnReanimatCondition_IVLapparatus'],
				EvnReanimatCondition_IVLparameter: IVLParameter_, //EvnRCGridRowDataDst['EvnReanimatCondition_IVLparameter'],
				EvnReanimatCondition_Auscultatory: EvnRCGridRowDataDst['EvnReanimatCondition_Auscultatory'],
				HeartTonesType_id: EvnRCGridRowDataDst['HeartTonesType_id'],
				HemodynamicsType_id: EvnRCGridRowDataDst['HemodynamicsType_id'],
				EvnReanimatCondition_Pressure: EvnRCGridRowDataDst['EvnReanimatCondition_Pressure'],
				EvnReanimatCondition_HeartFrequency: EvnRCGridRowDataDst['EvnReanimatCondition_HeartFrequency'],
				EvnReanimatCondition_StatusLocalis: EvnRCGridRowDataDst['EvnReanimatCondition_StatusLocalis'],
				AnalgesiaType_id: EvnRCGridRowDataDst['AnalgesiaType_id'],
				EvnReanimatCondition_AnalgesiaTxt: EvnRCGridRowDataDst['EvnReanimatCondition_AnalgesiaTxt'],
				EvnReanimatCondition_Diuresis: EvnRCGridRowDataDst['EvnReanimatCondition_Diuresis'],
				UrineType_id: EvnRCGridRowDataDst['UrineType_id'],
				EvnReanimatCondition_UrineTxt: EvnRCGridRowDataDst['EvnReanimatCondition_UrineTxt'],
				EvnReanimatCondition_Conclusion: EvnRCGridRowDataDst['EvnReanimatCondition_Conclusion'],
				ReanimArriveFromType_id: EvnRCGridRowDataDst['ReanimArriveFromType_id'],
				EvnReanimatCondition_HemodynamicsTxt: EvnRCGridRowDataDst['EvnReanimatCondition_HemodynamicsTxt'],
				EvnReanimatCondition_NeurologicStatus: EvnRCGridRowDataDst['EvnReanimatCondition_NeurologicStatus'],										
				EvnReanimatCondition_sofa: Sofa,
				EvnReanimatCondition_apache: Apache,
				EvnReanimatCondition_Saturation: SpO2,
				//NutritiousType_id: Nutritious,    //23.09.2019 - закомментарено
				//EvnReanimatCondition_NutritiousTxt: '',					//BOB - 28.08.2018  //23.09.2019 - закомментарено
				EvnReanimatCondition_Temperature: EvnRCGridRowDataDst['EvnReanimatCondition_Temperature'],						//BOB - 28.08.2018
				EvnReanimatCondition_InfusionVolume: EvnRCGridRowDataDst['EvnReanimatCondition_InfusionVolume'],				//BOB - 28.08.2018
				EvnReanimatCondition_DiuresisVolume: EvnRCGridRowDataDst['EvnReanimatCondition_DiuresisVolume'],				//BOB - 28.08.2018
				EvnReanimatCondition_CollectiveSurvey: EvnRCGridRowDataDst['EvnReanimatCondition_CollectiveSurvey'],			//BOB - 28.08.2018
				EvnReanimatCondition_SyndromeType: EvnRCGridRowDataDst['EvnReanimatCondition_SyndromeType'],					//BOB - 24.01.2019
				EvnReanimatCondition_SyndromeTxt: EvnRCGridRowDataDst['EvnReanimatCondition_SyndromeTxt'],							//BOB - 23.10.2019
				EvnReanimatCondition_ConsTxt: EvnRCGridRowDataDst['EvnReanimatCondition_ConsTxt'],								//BOB - 24.01.2019
				SpeechDisorderType_id: EvnRCGridRowDataDst['SpeechDisorderType_id'],											//BOB - 24.01.2019
				EvnReanimatCondition_rass: rass,																				//BOB - 24.01.2019
				EvnReanimatCondition_Eyes: EvnRCGridRowDataDst['EvnReanimatCondition_Eyes'],									//BOB - 24.01.2019
				EvnReanimatCondition_WetTurgor: EvnRCGridRowDataDst['EvnReanimatCondition_WetTurgor'],							//BOB - 24.01.2019
				EvnReanimatCondition_waterlow: waterlow,																		//BOB - 24.01.2019
				SkinType_mid: EvnRCGridRowDataDst['SkinType_mid'],																//BOB - 24.01.2019
				EvnReanimatCondition_MucusTxt: EvnRCGridRowDataDst['EvnReanimatCondition_MucusTxt'],							//BOB - 24.01.2019
				EvnReanimatCondition_IsMicrocDist: EvnRCGridRowDataDst['EvnReanimatCondition_IsMicrocDist'],					//BOB - 24.01.2019
				EvnReanimatCondition_IsPeriphEdem: EvnRCGridRowDataDst['EvnReanimatCondition_IsPeriphEdem'],					//BOB - 24.01.2019
				EvnReanimatCondition_Reflexes: EvnRCGridRowDataDst['EvnReanimatCondition_Reflexes'],							//BOB - 24.01.2019
				EvnReanimatCondition_BreathFrequency: EvnRCGridRowDataDst['EvnReanimatCondition_BreathFrequency'],				//BOB - 24.01.2019
				EvnReanimatCondition_HeartTones: EvnRCGridRowDataDst['EvnReanimatCondition_HeartTones'],						//BOB - 24.01.2019
				EvnReanimatCondition_IsHemodStab: EvnRCGridRowDataDst['EvnReanimatCondition_IsHemodStab'],						//BOB - 24.01.2019
				EvnReanimatCondition_Tongue: EvnRCGridRowDataDst['EvnReanimatCondition_Tongue'],								//BOB - 24.01.2019
				EvnReanimatCondition_Paunch: EvnRCGridRowDataDst['EvnReanimatCondition_Paunch'],								//BOB - 24.01.2019
				EvnReanimatCondition_PaunchTxt: EvnRCGridRowDataDst['EvnReanimatCondition_PaunchTxt'],							//BOB - 24.01.2019
				PeristalsisType_id: EvnRCGridRowDataDst['PeristalsisType_id'],													//BOB - 24.01.2019
				EvnReanimatCondition_VBD: EvnRCGridRowDataDst['EvnReanimatCondition_VBD'],										//BOB - 24.01.2019
				EvnReanimatCondition_Defecation: EvnRCGridRowDataDst['EvnReanimatCondition_Defecation'],						//BOB - 24.01.2019
				EvnReanimatCondition_DefecationTxt: EvnRCGridRowDataDst['EvnReanimatCondition_DefecationTxt'],					//BOB - 24.01.2019
				EvnReanimatCondition_MonopLoc: EvnRCGridRowDataDst['EvnReanimatCondition_MonopLoc'],							//BOB - 24.01.2019
				LimbImmobilityType_id: EvnRCGridRowDataDst['LimbImmobilityType_id'],											//BOB - 24.01.2019
				EvnReanimatCondition_mrc: mrc,																			        //BOB - 24.01.2019
				EvnReanimatCondition_MeningSign: EvnRCGridRowDataDst['EvnReanimatCondition_MeningSign'],									//BOB - 24.01.2019
				EvnReanimatCondition_MeningSignTxt: EvnRCGridRowDataDst['EvnReanimatCondition_MeningSignTxt'],								//BOB - 24.01.2019
				EvnReanimatCondition_glasgow: Glasgow,				//BOB - 16.09.2019
				EvnReanimatCondition_four: FOUR,				        //BOB - 16.09.2019
				EvnReanimatCondition_Doctor: EvnReanimatCondition_Doctor //BOB - 27.09.2019

				
			}));

			Grid.getSelectionModel().selectRow(0); 	//установка выбранности на первой строке грида 
			this.EvnReanimatCondition_ButtonManag(this,false);  //BOB - 11.02.2019
			this.findById('swERPEW_EvnReanimatConditionStage').disable();  // выпадающее меню выбор этапа / документа  BOB - 08.08.2018

		}

	},

	//активизация элементов панели наблюдения для редактирования
	EvnReanimatCondition_Edit: function() {
		
		var win = this;
			
		var Grid = this.findById('swERPEW_EvnReanimatCondition_Grid');
		var EvnRCGridRowData = Grid.getSelectionModel().getSelected().data;  //выбранная строка в гриде событий регулярного наблюдения состояния

		if (Ext.isEmpty(EvnRCGridRowData['EvnReanimatCondition_id'])) {
			Ext.MessageBox.alert('Внимание!', 'Не выбрана строка в списке наблюдений! ');
			return false;
		}
		
		//BOB - 24.01.2020 //если младенцы
		if (this.isNeonatal(this.findById('swERPEW_ReanimatAgeGroup').getValue()))  {
			if (getWnd('swEvnNeonatalSurveyEditWindow').isVisible() && getWnd('swEvnNeonatalSurveyEditWindow').changedDatas) {
				Ext.Msg.alert(langs('Сообщение'), langs('Окно Наблюдение состояния младенца уже открыто<br> и в нём имеются несохранённые изменния!'));

				var index = this.findById('swERPEW_EvnReanimatCondition_Grid').store.find('EvnReanimatCondition_id',getWnd('swEvnNeonatalSurveyEditWindow').EvnNeonatalSurvey_id);
				this.findById('swERPEW_EvnReanimatCondition_Grid').selModel.selectRow(index);

				return false;
			}
			var params = {
				ENSEW_title: langs('Наблюдение состояния младенца'),
				action: (this.action == 'view') ? this.action : 'edit',
				fromObject: this,
				EvnNeonatalSurvey_id: EvnRCGridRowData['EvnReanimatCondition_id'],
				pers_data: this.pers_data,
				EvnNeonatalSurvey_pid: this.EvnReanimatPeriod_id,
				EvnNeonatalSurvey_rid: this.EvnReanimatPeriod_rid,
				ParentObject: 'EvnReanimatPeriod',
				userMedStaffFact: this.userMedStaffFact,
				ARMType: 'reanimation',
				FirstConditionLoad: this.FirstConditionLoad
			};
			params.Callback = function() {
				console.log('BOB_RP_saved=', 'kjhlkjhkjhlk');
			};
			getWnd('swEvnNeonatalSurveyEditWindow').show(params);
			this.EvnReanimatCondition_ButtonManag(this,false);  //BOB - 17.03.2020		
			
		}
		else {
			//BOB - 24.01.2019 формирование пустого объекта дыхания аускультативно, это для старых записей, которые захотят редактировать
			var SideType =  this.SideType;
			var BreathAuscult_records = this.findById('swERPEW_Condition_Panel').BreathAuscult_records;
			if(!BreathAuscult_records[EvnRCGridRowData['EvnReanimatCondition_id']]) {
				BreathAuscult_records[EvnRCGridRowData['EvnReanimatCondition_id']] = {}; 
				for(var i in SideType){
					if (SideType[i]['SideType_SysNick']) {
						BreathAuscult_records[EvnRCGridRowData['EvnReanimatCondition_id']][SideType[i]['SideType_SysNick']] = {
							BreathAuscultative_id: null,
							EvnReanimatCondition_id: EvnRCGridRowData['EvnReanimatCondition_id'],
							BreathAuscultative_Auscult: 0,
							BreathAuscultative_AuscultTxt: '',
							BreathAuscultative_Rale: 0,
							BreathAuscultative_RaleTxt: '',
							BreathAuscultative_IsPleuDrain: null,
							BreathAuscultative_PleuDrainTxt: '',
							SideType_id: SideType[i]['SideType_id'],
							SideType_SysNick: SideType[i]['SideType_SysNick'],
							BA_RecordStatus: 0
						};
					}
				}
			}
			//установка активности/неактивности на область ввода наблюдения состояния
			// создание выборки элементов 'input', внутри панели с id 'swERPEW_Condition_Panel', возвращает с типом  Ext.Element
			//по массиву выбранных элементов
			Ext.select('input, textarea', true, 'swERPEW_Condition_Panel').each(function(el){
				var id = el.id; //выделяю параметр id из Ext.Element
				var object = win.findById(id);	//ищу в окне объект ExtJS
				if(object){ // если нахожу, то 
					object.setDisabled(false); // делаю Disabled /Enabled
				}
			});
			//установка активности/неактивности на радиокнопки в области ввода наблюдения состояния
			for(var i in SideType){
				if(SideType[i]['SideType_SysNick']) {
					this.findById('swERPEW_RC_Auscultatory_'+SideType[i]['SideType_SysNick']+'_1').setDisabled(false); 
					this.findById('swERPEW_RC_Auscultatory_'+SideType[i]['SideType_SysNick']+'_2').setDisabled(false); 
					this.findById('swERPEW_RC_Auscultatory_'+SideType[i]['SideType_SysNick']+'_3').setDisabled(false); 	
				}
			}
			this.findById('swERPEW_RC_Diuresis1').setDisabled(false); 
			this.findById('swERPEW_RC_Diuresis2').setDisabled(false); 
			this.findById('swERPEW_RC_Diuresis3').setDisabled(false); 
			this.findById('swERPEW_RC_Eyes1').setDisabled(false); 
			this.findById('swERPEW_RC_Eyes2').setDisabled(false); 
			this.findById('swERPEW_RC_Eyes3').setDisabled(false); 		
			this.findById('swERPEW_RC_WetTurgor1').setDisabled(false); 
			this.findById('swERPEW_RC_WetTurgor2').setDisabled(false); 
			this.findById('swERPEW_RC_IsMicrocDist').setDisabled(false); 
			this.findById('swERPEW_RC_IsPeriphEdem').setDisabled(false); 
			this.findById('swERPEW_RC_Reflexes1').setDisabled(false); 
			this.findById('swERPEW_RC_Reflexes2').setDisabled(false); 
			this.findById('swERPEW_RC_Reflexes3').setDisabled(false); 		
			this.findById('swERPEW_RC_HeartTones1').setDisabled(false); 
			this.findById('swERPEW_RC_HeartTones2').setDisabled(false); 
			this.findById('swERPEW_RC_IsHemodStab').setDisabled(false); 
			this.findById('swERPEW_RC_Tongue1').setDisabled(false); 
			this.findById('swERPEW_RC_Tongue2').setDisabled(false); 
			this.findById('swERPEW_RC_Paunch1').setDisabled(false); 
			this.findById('swERPEW_RC_Paunch2').setDisabled(false); 
			this.findById('swERPEW_RC_Paunch3').setDisabled(false); 		
			this.findById('swERPEW_RC_Defecation').setDisabled(false); 
			this.findById('swERPEW_RC_MonopLoc1').setDisabled(false); 
			this.findById('swERPEW_RC_MonopLoc2').setDisabled(false); 
			this.findById('swERPEW_RC_MeningSign').setDisabled(false); 

			this.EvnReanimatCondition_ButtonManag(this,false);  //BOB - 11.02.2019		
			this.findById('swERPEW_EvnReanimatConditionStage').disable();  // выпадающее меню выбор этапа / документа  BOB - 08.08.2018

			//Поля с реквизитами из шкал и мероприятий
			this.findById('swERPEW_RC_sofa').setDisabled(true);	// значение по Sofa			//BOB - 23.04.2018
			this.findById('swERPEW_RC_apache').setDisabled(true);	// значение по Apache			//BOB - 23.04.2018
			this.findById('swERPEW_RC_rass').setDisabled(true);	// значение по RASS			//BOB - 24.01.2018
			this.findById('swERPEW_RC_waterlow').setDisabled(true);	// значение по waterlow			//BOB - 24.01.2018
			this.findById('swERPEW_RC_mrc').setDisabled(true);	// значение по waterlow			//BOB - 24.01.2018
			this.findById('swERPEW_RC_glasgow').setDisabled(true);	// значение по Glasgow			//BOB - 16.09.2019
			this.findById('swERPEW_RC_four').setDisabled(true);	// значение по FOUR			//BOB - 16.09.2019
			//Поля антропометрических данных
			this.findById('swERPEW_RC_Height').setDisabled(true);	// рост			//BOB - 11.04.2019
			this.findById('swERPEW_RC_Weight').setDisabled(true);	// вес			//BOB - 11.04.2019
			this.findById('swERPEW_RC_IMT').setDisabled(true);	// коэффициент веса тела			//BOB - 11.04.2019
			//Поля питания из мероприятия
			this.findById('swERPEW_RC_Nutritious').setDisabled(true);	// тип питания		//BOB - 23.09.2019
			this.findById('swERPEW_RC_NutritiousTxt').setDisabled(true);	// питание - вариант пользователя		//BOB - 23.09.2019
			this.findById('swERPEW_RC_NutritVol').setDisabled(true);	// объём питания		//BOB - 23.09.2019
			this.findById('swERPEW_RC_NutritEnerg').setDisabled(true);	// энергетическая ценность питания		//BOB - 23.09.2019
		}
	},

	//удаление регулярного наблюдения состояния
	EvnReanimatCondition_Del: function() {
		var win = this;
		
		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId) {
				if ( buttonId == 'yes' ) {
					
					var Grid = this.findById('swERPEW_EvnReanimatCondition_Grid');
					var EvnRCGridRowData = Grid.getSelectionModel().getSelected().data;  //выбранная строка в гриде событий регулярного наблюдения состояния
					console.log('BOB_EvnRCGridRowData=',EvnRCGridRowData);  //BOB - 21.05.2018;
					
					if (Ext.isEmpty(EvnRCGridRowData['EvnReanimatCondition_id'])) {
						Ext.MessageBox.alert('Внимание!', 'Не выбрана строка в списке наблюдений! ');
						return false;
					}
					
					var RawId = Grid.getSelectionModel().getSelected().id;
					this.ConditionGridLoadRawNum = Grid.getStore().find('EvnReanimatCondition_id',Grid.getSelectionModel().getSelected().id);

					//BOB - 20.02.2020
					var data = {};
					var vURL = '';
					//младенец
					if (this.isNeonatal(this.findById('swERPEW_ReanimatAgeGroup').getValue()))  {
						data['EvnNeonatalSurvey_id'] = EvnRCGridRowData['EvnReanimatCondition_id'];
						vURL = '/?c=EvnNeonatalSurvey&m=EvnNeonatalSurvey_Delete';

						if (getWnd('swEvnNeonatalSurveyEditWindow').isVisible() && (getWnd('swEvnNeonatalSurveyEditWindow').EvnNeonatalSurvey_id ==  EvnRCGridRowData['EvnReanimatCondition_id']))
							getWnd('swEvnNeonatalSurveyEditWindow').hide();
					}
					else { //взрослый
						data['EvnReanimatCondition_id'] = EvnRCGridRowData['EvnReanimatCondition_id'];
						vURL = '/?c=EvnReanimatPeriod&m=EvnReanimatCondition_Del';
					}
					
					// var data = 	{ 
					// 	EvnReanimatCondition_id: EvnRCGridRowData['EvnReanimatCondition_id']
					// };				
									
				
					$.ajax({
						mode: "abort",
						type: "post",
						async: false,
						//url: '/?c=EvnReanimatPeriod&m=EvnReanimatCondition_Del',
						url: vURL,
						data: data,
						success: function(response) {
							var DelResponse = Ext.util.JSON.decode(response);
							console.log('BOB_DelResponse=',DelResponse); 
							if (DelResponse['success'] == 'true'){
								if (win.ConditionGridLoadRawNum == Grid.getStore().data.length - 1)//перейти на запись с тем же положением, а если последняя то на первую	
									win.ConditionGridLoadRawNum--;
								Grid.getStore().reload();	//перезагрузка грида Реанимационных мероприятий	
								//console.log('BOB_RawNum0=',win.ConditionGridLoadRawNum);  //BOB - 21.05.2018;
							}
							else	
								Ext.MessageBox.alert('Ошибка сохранения!', DelResponse['Error_Msg']);
								//alert('Ошибка сохранения.'+SaveResponse['message']);
						}, 
						error: function() {
							Ext.MessageBox.alert('Ошибка сохранения!', "При обработке запроса на сервере произошла ошибка!");
						} 
					});		
				}

			}.createDelegate(this),
			icon: Ext.Msg.WARNING,
			msg: 'Вы действительно хотите удалить запись регулярного наблюдения?',
			title: 'Внимание!'
		});
	},

	//сохранение регулярного наблюдения состояния
	EvnReanimatCondition_Save: function(b,e){
	
		var win = this;
	
		var EvnRCGridRowData = this.findById('swERPEW_EvnReanimatCondition_Grid').getSelectionModel().getSelected().data;  //выбранная строка в гриде событий регулярного наблюдения состояния
		if ((!this.ReanimatPeriod_isClosed) && (EvnRCGridRowData['ReanimStageType_id'] == 3)) {
			Ext.MessageBox.alert('Внимание!', 'Реанимационный период ещё не закрыт - <br> Переводной эпикриз сохранять нельзя! ');
			return false;
		}
		
		//индекс (номер) обрабатываемой записи
		this.ConditionGridLoadRawNum = this.findById('swERPEW_EvnReanimatCondition_Grid').getStore().find('EvnReanimatCondition_id',this.findById('swERPEW_EvnReanimatCondition_Grid').getSelectionModel().getSelected().id);
		if (this.ConditionGridLoadRawNum == -1)  this.ConditionGridLoadRawNum = 0;

		//if (EvnRCGridRowData['EvnReanimatCondition_id'] == 'New_GUID_Id'){ //если новая запись //BOB - 26.07.2018 Комментарю т.к. заказчик хочет иметь возможность вносить ИЗМЕНЕНИЯ 
		var ErrMessag = '';
		if (EvnRCGridRowData['EvnReanimatCondition_setDate'] == '')
			ErrMessag += 'дата начала наблюдения<br>';
		if (EvnRCGridRowData['EvnReanimatCondition_setTime'] == '')
			ErrMessag += 'время начала наблюдения<br>';
		if(EvnRCGridRowData['ReanimStageType_id'] == 2){ //BOB - 13.08.2018;
			if (EvnRCGridRowData['EvnReanimatCondition_disDate'] == '')
				ErrMessag += 'дата окончания наблюдения<br>';
			if (EvnRCGridRowData['EvnReanimatCondition_disTime'] == '')
				ErrMessag += 'время окончания наблюдения<br>';
		}
		if (EvnRCGridRowData['ReanimStageType_id'] == 0)
			ErrMessag += 'Этап - документ<br>';
		if (EvnRCGridRowData['Condition_Name'] == '')
			ErrMessag += 'состояние<br>';

		if (EvnRCGridRowData['SkinType_id'] == 5)
			if (!(EvnRCGridRowData['EvnReanimatCondition_SkinTxt'])||(EvnRCGridRowData['EvnReanimatCondition_SkinTxt'] === ''))
				ErrMessag += 'окраска кожного покрова - вариант пользователя<br>';

		if (EvnRCGridRowData['SkinType_mid'] == 5)
			if (!(EvnRCGridRowData['EvnReanimatCondition_MucusTxt'])||(EvnRCGridRowData['EvnReanimatCondition_MucusTxt'] === ''))
				ErrMessag += 'слизистые - вариант пользователя<br>';

		if (EvnRCGridRowData['AnalgesiaType_id'] == 3)
			if (!(EvnRCGridRowData['EvnReanimatCondition_AnalgesiaTxt'])||(EvnRCGridRowData['EvnReanimatCondition_AnalgesiaTxt'] === ''))
				ErrMessag += 'анальгезия - вариант пользователя<br>';

		if (EvnRCGridRowData['UrineType_id'] == 4)
			if (!(EvnRCGridRowData['EvnReanimatCondition_UrineTxt'])||(EvnRCGridRowData['EvnReanimatCondition_UrineTxt'] === ''))
				ErrMessag += 'моча - вариант пользователя<br>';
		
//		if (EvnRCGridRowData['NutritiousType_id'] == 4)  //23.09.2019 - закомментарено
//			if (!(EvnRCGridRowData['EvnReanimatCondition_NutritiousTxt'])||(EvnRCGridRowData['EvnReanimatCondition_NutritiousTxt'] === ''))
//				ErrMessag += 'нутритивная поддержка - вариант пользователя<br>';
		
		//BOB - 27.09.2019
		if(Ext.isEmpty(EvnRCGridRowData['EvnReanimatCondition_Doctor'])){
			ErrMessag += 'ФИО врача, подписывающего документ<br>';
		}
		
		

		if (EvnRCGridRowData['ReanimStageType_id'] == 1)
			if (!(EvnRCGridRowData['ReanimArriveFromType_id'])||(EvnRCGridRowData['ReanimArriveFromType_id'] === ''))
				ErrMessag += 'поступил из...<br>';

		//Контроль даты по отношению к началу и концу РП 			
		//Начало периода
		var RP_setDT = this.findById('swERPEW_EvnReanimatPeriod_setDate').getValue(); //  + ' ' + 
		var Time = this.findById('swERPEW_EvnReanimatPeriod_setTime').getValue();		
		RP_setDT.setHours(parseInt(Time.substr(0, 2)), parseInt(Time.substr(3, 2)));
		//Конец периода
		var RP_disDT = '';
		if (this.findById('swERPEW_EvnReanimatPeriod_disDate').getValue() != ''){
			RP_disDT = this.findById('swERPEW_EvnReanimatPeriod_disDate').getValue(); //  + ' ' + 
			Time = this.findById('swERPEW_EvnReanimatPeriod_disTime').getValue();		
			RP_disDT.setHours(parseInt(Time.substr(0, 2)), parseInt(Time.substr(3, 2)));		
		}


		var ERC_setDT = '';
		var ERC_disDT = '';

		//Дата начала наблюдения
		if (this.findById('swERPEW_EvnReanimatCondition_setDate').getValue() != ''){
			ERC_setDT = this.findById('swERPEW_EvnReanimatCondition_setDate').getValue();   //console.log('BOB_ERC_setDT_1=',ERC_setDT);
			Time = this.findById('swERPEW_EvnReanimatCondition_setTime').getValue();        //console.log('BOB_Time=',Time);
			ERC_setDT.setHours(parseInt(Time.substr(0, 2)), parseInt(Time.substr(3, 2)));	  //console.log('BOB_ERC_setDT_2=',ERC_setDT);		
			//Начало периода
			if (ERC_setDT < RP_setDT)
				ErrMessag += 'дата начала наблюдения меньше даты начала Реанимационного периода<br>';

			//Конец периода
			if ((this.findById('swERPEW_EvnReanimatPeriod_disDate').getValue() != '') && (EvnRCGridRowData['ReanimStageType_id'] != 3)){
				if (ERC_setDT > RP_disDT)
					ErrMessag += 'дата начала наблюдения больше даты окончания Реанимационного периода<br>';
			}

			//сравнение начала периода наблюдения с окончанием предыдущего периода
			//console.log('BOB_RawNum1=',this.ConditionGridLoadRawNum );  //BOB - 21.05.2018;
			if (EvnRCGridRowData['ReanimStageType_id'] != 1) {  //BOB - 23.10.2019
				if (this.findById('swERPEW_EvnReanimatCondition_Grid').getStore().data.items[win.ConditionGridLoadRawNum + 1]) {
					var EvnRCGridRowDataPrev = this.findById('swERPEW_EvnReanimatCondition_Grid').getStore().data.items[win.ConditionGridLoadRawNum + 1].data;  //выбранная строка в гриде событий регулярного наблюдения состояния
					var PrevERC_disDT = EvnRCGridRowDataPrev['EvnReanimatCondition_disDate'];
					if(!Ext.isEmpty(PrevERC_disDT)){
						Time = EvnRCGridRowDataPrev['EvnReanimatCondition_disTime'];
						PrevERC_disDT.setHours(parseInt(Time.substr(0, 2)), parseInt(Time.substr(3, 2)));
						if (ERC_setDT < PrevERC_disDT)
							ErrMessag += 'дата начала наблюдения меньше даты окончания предыдущего наблюдения<br>';
					}
				}
			}
		}

		//Дата окончания наблюдения
		if (this.findById('swERPEW_EvnReanimatCondition_disDate').getValue() != ''){
			ERC_disDT = this.findById('swERPEW_EvnReanimatCondition_disDate').getValue();  
			Time = this.findById('swERPEW_EvnReanimatCondition_disTime').getValue();	
			ERC_disDT.setHours(parseInt(Time.substr(0, 2)), parseInt(Time.substr(3, 2)));	

			if (ERC_disDT < RP_setDT)
				ErrMessag += 'дата окончания наблюдения меньше даты начала Реанимационного периода<br>';

			//Конец периода
			if (this.findById('swERPEW_EvnReanimatPeriod_disDate').getValue() != ''){
				if (ERC_disDT > RP_disDT)
					ErrMessag += 'дата окончания наблюдения больше даты окончания Реанимационного периода<br>';
			}
		}

		if ((this.findById('swERPEW_EvnReanimatCondition_setDate').getValue() != '') && (this.findById('swERPEW_EvnReanimatCondition_disDate').getValue() != '')){
			if (ERC_setDT > ERC_disDT)
				ErrMessag += 'дата начала наблюдения больше даты окончания наблюдения<br>';				
		}

		if (ErrMessag == '') { //если сообщение о незаполненных реквизитах пустое
			var loadMask = new Ext.LoadMask(Ext.get('swERPEW_Condition_Panel'), {msg: "Идёт сохранение..."});
			loadMask.show();

			//параметры регулярного наблюдения и общие параметры
			var data = 	{ 
				EvnReanimatCondition_id: (EvnRCGridRowData['EvnReanimatCondition_id'] == 'New_GUID_Id') ? null : EvnRCGridRowData['EvnReanimatCondition_id'],     //BOB - 26.07.2018 
				EvnReanimatCondition_pid: EvnRCGridRowData['EvnReanimatCondition_pid'],
				EvnReanimatCondition_rid: this.EvnReanimatPeriod_rid,
				Lpu_id: this.Lpu_id,
				Person_id: EvnRCGridRowData['Person_id'],
				PersonEvn_id: EvnRCGridRowData['PersonEvn_id'],
				Server_id: EvnRCGridRowData['Server_id'],

				EvnReanimatCondition_setDate: Ext.util.Format.date(EvnRCGridRowData['EvnReanimatCondition_setDate'], 'd.m.Y'),
				EvnReanimatCondition_setTime: EvnRCGridRowData['EvnReanimatCondition_setTime'],
				EvnReanimatCondition_disDate: Ext.util.Format.date(EvnRCGridRowData['EvnReanimatCondition_disDate'], 'd.m.Y'),
				EvnReanimatCondition_disTime: EvnRCGridRowData['EvnReanimatCondition_disTime'],

				ReanimStageType_id: EvnRCGridRowData['ReanimStageType_id'],
				ReanimConditionType_id: EvnRCGridRowData['ReanimConditionType_id'],
				EvnReanimatCondition_Complaint: EvnRCGridRowData['EvnReanimatCondition_Complaint'],
				SkinType_id: EvnRCGridRowData['SkinType_id'],
				EvnReanimatCondition_SkinTxt: EvnRCGridRowData['EvnReanimatCondition_SkinTxt'],
				ConsciousType_id: EvnRCGridRowData['ConsciousType_id'],
				BreathingType_id: EvnRCGridRowData['BreathingType_id'],
				EvnReanimatCondition_IVLapparatus: EvnRCGridRowData['EvnReanimatCondition_IVLapparatus'],
				EvnReanimatCondition_IVLparameter: EvnRCGridRowData['EvnReanimatCondition_IVLparameter'],
				EvnReanimatCondition_Auscultatory: EvnRCGridRowData['EvnReanimatCondition_Auscultatory'],
				HeartTonesType_id: EvnRCGridRowData['HeartTonesType_id'],
				HemodynamicsType_id: EvnRCGridRowData['HemodynamicsType_id'],
				EvnReanimatCondition_Pressure: EvnRCGridRowData['EvnReanimatCondition_Pressure'],
				EvnReanimatCondition_HeartFrequency: EvnRCGridRowData['EvnReanimatCondition_HeartFrequency'],
				EvnReanimatCondition_StatusLocalis: EvnRCGridRowData['EvnReanimatCondition_StatusLocalis'],
				AnalgesiaType_id: EvnRCGridRowData['AnalgesiaType_id'],
				EvnReanimatCondition_AnalgesiaTxt: EvnRCGridRowData['EvnReanimatCondition_AnalgesiaTxt'],
				EvnReanimatCondition_Diuresis: EvnRCGridRowData['EvnReanimatCondition_Diuresis'],
				UrineType_id: EvnRCGridRowData['UrineType_id'],
				EvnReanimatCondition_UrineTxt: EvnRCGridRowData['EvnReanimatCondition_UrineTxt'],
				EvnReanimatCondition_Conclusion: EvnRCGridRowData['EvnReanimatCondition_Conclusion'],
				ReanimArriveFromType_id:	EvnRCGridRowData['ReanimArriveFromType_id'],
				EvnReanimatCondition_HemodynamicsTxt: EvnRCGridRowData['EvnReanimatCondition_HemodynamicsTxt'],
				EvnReanimatCondition_NeurologicStatus: EvnRCGridRowData['EvnReanimatCondition_NeurologicStatus'],
				EvnReanimatCondition_sofa: EvnRCGridRowData['EvnReanimatCondition_sofa'],									//BOB - 23.04.2018
				EvnReanimatCondition_apache: EvnRCGridRowData['EvnReanimatCondition_apache'],								//BOB - 23.04.2018
				EvnReanimatCondition_Saturation: EvnRCGridRowData['EvnReanimatCondition_Saturation'],						//BOB - 23.04.2018
				EvnReanimatCondition_OxygenFraction: EvnRCGridRowData['EvnReanimatCondition_OxygenFraction'],
				EvnReanimatCondition_OxygenPressure: EvnRCGridRowData['EvnReanimatCondition_OxygenPressure'],
				EvnReanimatCondition_PaOFiO: EvnRCGridRowData['EvnReanimatCondition_PaOFiO'],
				//NutritiousType_id: EvnRCGridRowData['NutritiousType_id'],													//BOB - 23.04.2018   //23.09.2019 - закомментарено
				//EvnReanimatCondition_NutritiousTxt: EvnRCGridRowData['EvnReanimatCondition_NutritiousTxt'],					//BOB - 28.08.2018
				EvnReanimatCondition_Temperature: EvnRCGridRowData['EvnReanimatCondition_Temperature'],						//BOB - 28.08.2018
				EvnReanimatCondition_InfusionVolume: EvnRCGridRowData['EvnReanimatCondition_InfusionVolume'],				//BOB - 28.08.2018
				EvnReanimatCondition_DiuresisVolume: EvnRCGridRowData['EvnReanimatCondition_DiuresisVolume'],				//BOB - 28.08.2018
				EvnReanimatCondition_CollectiveSurvey: EvnRCGridRowData['EvnReanimatCondition_CollectiveSurvey'],			//BOB - 28.08.2018
				EvnReanimatCondition_SyndromeType: EvnRCGridRowData['EvnReanimatCondition_SyndromeType'],					//BOB - 24.01.2019
				EvnReanimatCondition_ConsTxt: EvnRCGridRowData['EvnReanimatCondition_ConsTxt'],								//BOB - 24.01.2019
				SpeechDisorderType_id: EvnRCGridRowData['SpeechDisorderType_id'],											//BOB - 24.01.2019
				EvnReanimatCondition_rass: EvnRCGridRowData['EvnReanimatCondition_rass'],									//BOB - 24.01.2019
				EvnReanimatCondition_Eyes: EvnRCGridRowData['EvnReanimatCondition_Eyes'],									//BOB - 24.01.2019
				EvnReanimatCondition_WetTurgor: EvnRCGridRowData['EvnReanimatCondition_WetTurgor'],							//BOB - 24.01.2019
				EvnReanimatCondition_waterlow: EvnRCGridRowData['EvnReanimatCondition_waterlow'],							//BOB - 24.01.2019
				SkinType_mid: EvnRCGridRowData['SkinType_mid'],																//BOB - 24.01.2019
				EvnReanimatCondition_MucusTxt: EvnRCGridRowData['EvnReanimatCondition_MucusTxt'],							//BOB - 24.01.2019
				EvnReanimatCondition_IsMicrocDist: EvnRCGridRowData['EvnReanimatCondition_IsMicrocDist'],					//BOB - 24.01.2019
				EvnReanimatCondition_IsPeriphEdem: EvnRCGridRowData['EvnReanimatCondition_IsPeriphEdem'],					//BOB - 24.01.2019
				EvnReanimatCondition_Reflexes: EvnRCGridRowData['EvnReanimatCondition_Reflexes'],							//BOB - 24.01.2019
				EvnReanimatCondition_BreathFrequency: EvnRCGridRowData['EvnReanimatCondition_BreathFrequency'],				//BOB - 24.01.2019
				EvnReanimatCondition_HeartTones: EvnRCGridRowData['EvnReanimatCondition_HeartTones'],						//BOB - 24.01.2019
				EvnReanimatCondition_IsHemodStab: EvnRCGridRowData['EvnReanimatCondition_IsHemodStab'],						//BOB - 24.01.2019
				EvnReanimatCondition_Tongue: EvnRCGridRowData['EvnReanimatCondition_Tongue'],								//BOB - 24.01.2019
				EvnReanimatCondition_Paunch: EvnRCGridRowData['EvnReanimatCondition_Paunch'],								//BOB - 24.01.2019
				EvnReanimatCondition_PaunchTxt: EvnRCGridRowData['EvnReanimatCondition_PaunchTxt'],							//BOB - 24.01.2019
				PeristalsisType_id: EvnRCGridRowData['PeristalsisType_id'],													//BOB - 24.01.2019
				EvnReanimatCondition_VBD: EvnRCGridRowData['EvnReanimatCondition_VBD'],										//BOB - 24.01.2019
				EvnReanimatCondition_Defecation: EvnRCGridRowData['EvnReanimatCondition_Defecation'],						//BOB - 24.01.2019
				EvnReanimatCondition_DefecationTxt: EvnRCGridRowData['EvnReanimatCondition_DefecationTxt'],					//BOB - 24.01.2019
				EvnReanimatCondition_MonopLoc: EvnRCGridRowData['EvnReanimatCondition_MonopLoc'],							//BOB - 24.01.2019
				LimbImmobilityType_id: EvnRCGridRowData['LimbImmobilityType_id'],											//BOB - 24.01.2019
				EvnReanimatCondition_mrc: EvnRCGridRowData['EvnReanimatCondition_mrc'],										//BOB - 24.01.2019
				EvnReanimatCondition_MeningSign: EvnRCGridRowData['EvnReanimatCondition_MeningSign'],						//BOB - 24.01.2019
				EvnReanimatCondition_MeningSignTxt: EvnRCGridRowData['EvnReanimatCondition_MeningSignTxt'],					//BOB - 24.01.2019
				EvnReanimatCondition_glasgow: EvnRCGridRowData['EvnReanimatCondition_glasgow'],										//BOB - 16.09.2019
				EvnReanimatCondition_four: EvnRCGridRowData['EvnReanimatCondition_four'],											//BOB - 16.09.2019
				EvnReanimatCondition_SyndromeTxt: EvnRCGridRowData['EvnReanimatCondition_SyndromeTxt'],						//BOB - 16.09.2019
				EvnReanimatCondition_Doctor: EvnRCGridRowData['EvnReanimatCondition_Doctor']								//BOB - 16.09.2019

			};				

			data['BreathAuscult_List'] = Ext.util.JSON.encode(this.findById('swERPEW_Condition_Panel').BreathAuscult_records[EvnRCGridRowData['EvnReanimatCondition_id']]);	
			delete this.findById('swERPEW_Condition_Panel').BreathAuscult_records[EvnRCGridRowData['EvnReanimatCondition_id']];

			$.ajax({
				mode: "abort",
				type: "post",
				async: true,
				url: '/?c=EvnReanimatPeriod&m=EvnReanimatCondition_Save',
				data: data,
				success: function(response) {
					var SaveResponse = Ext.util.JSON.decode(response);
					console.log('BOB_SaveResponse=',SaveResponse); 
					if (SaveResponse['success'] == 'true'){
						if (EvnRCGridRowData['EvnReanimatCondition_id'] == 'New_GUID_Id')
							win.findById('swERPEW_EvnReanimatCondition_Grid').getStore().reload();	//перезагрузка грида Реанимационных мероприятий		
						else  // панель делаю неактивной
							win.findById('swERPEW_EvnReanimatCondition_Grid').getSelectionModel().selectRow(win.ConditionGridLoadRawNum);
						
						win.EvnReanimatCondition_ButtonManag(win,true);  //BOB - 11.02.2019
						loadMask.hide();
						}
					else{
						loadMask.hide();
						Ext.MessageBox.alert('Ошибка сохранения!', SaveResponse['Error_Msg']);
					}
				}, 
				error: function() {
					loadMask.hide();
					Ext.MessageBox.alert('Ошибка сохранения!', "При обработке запроса на сервере произошла ошибка!");
				} 
			});		
		}			
		else {
			this.EvnReanimatCondition_ButtonManag(win,false);  //BOB - 11.02.2019
			ErrMessag = 'Отсутствуют или неверны следующие реквизиты регулярного наблюдения: <br><br>' + ErrMessag;
			Ext.MessageBox.alert('Внимание!', ErrMessag);
		}			
	},

	//процедура управления кнопками раздела наблюдений
	EvnReanimatCondition_ButtonManag: function(win,old_rec){
		//old_rec - старая ли текущая запись
		var view_act = win.action == 'view' ? true : false;  //режим просмотра
		var exists_new = win.findById('swERPEW_EvnReanimatCondition_Grid').store.find('EvnReanimatCondition_id','New_GUID_Id') == -1 ? false :true;  // имеется ли новая запись вообще

		//console.log('BOB_view_act old_rec exists_new=',view_act + ' ' +  old_rec + ' ' + exists_new);
		if (Ext.getCmp('swERPEW_EvnReanimatConditionButtonAdd'))
			Ext.getCmp('swERPEW_EvnReanimatConditionButtonAdd').setDisabled(view_act || !old_rec || exists_new);  // кнопку добавления 
		if (Ext.getCmp('swERPEW_EvnReanimatConditionButtonCopy'))
			Ext.getCmp('swERPEW_EvnReanimatConditionButtonCopy').setDisabled(view_act || !old_rec || exists_new); // кнопку копирования
		if(Ext.getCmp('swERPEW_EvnReanimatConditionButtonEdit'))
			Ext.getCmp('swERPEW_EvnReanimatConditionButtonEdit').setDisabled(view_act || !old_rec || exists_new); // кнопку редактирования 
		if (Ext.getCmp('swERPEW_EvnReanimatConditionButtonDel'))
			Ext.getCmp('swERPEW_EvnReanimatConditionButtonDel').setDisabled(view_act || !old_rec || exists_new); // кнопку удаления
		if (Ext.getCmp('swERPEW_EvnReanimatConditionButtonRefresh'))
			Ext.getCmp('swERPEW_EvnReanimatConditionButtonRefresh').setDisabled(false); // кнопку обновления
		
//		if(Ext.getCmp('swERPEW_EvnReanimatConditionButtonPrint'))
//			Ext.getCmp('swERPEW_EvnReanimatConditionButtonPrint').setDisabled(!view_act && !old_rec); // кнопку печати документакатетеризации 
		if(Ext.getCmp('swERPEW_EvnReanimatConditionButtonPrintUp'))
			Ext.getCmp('swERPEW_EvnReanimatConditionButtonPrintUp').setDisabled(!view_act && !old_rec); // кнопку печати верх документакатетеризации 
		if(Ext.getCmp('swERPEW_EvnReanimatConditionButtonPrintDoun'))	
			Ext.getCmp('swERPEW_EvnReanimatConditionButtonPrintDoun').setDisabled(!view_act && !old_rec); // кнопку печати низ документакатетеризации 
		
		if(win.findById('swERPEW_EvnReanimatConditionButtonSave'))
			win.findById('swERPEW_EvnReanimatConditionButtonSave').setDisabled(view_act || old_rec); // кнопку сохранения 
		if(win.findById('swERPEW_RC_Conscious_from_glasgow_Button'))
			win.findById('swERPEW_RC_Conscious_from_glasgow_Button').setDisabled(view_act || old_rec); // кнопку сознание по глазго 
		if(win.findById('swERPEW_RC_Height_Add_Button'))
			win.findById('swERPEW_RC_Height_Add_Button').setDisabled(view_act || old_rec); // кнопку добавления роста 
		if(win.findById('swERPEW_RC_Weight_Add_Button'))	
			win.findById('swERPEW_RC_Weight_Add_Button').setDisabled(view_act || old_rec); // кнопку добавления веса 
	},	

	//печать документа поступление/дневник
	EvnReanimatCondition_Print: function(Doun) {
		
		var EvnRCGridRowData = this.findById('swERPEW_EvnReanimatCondition_Grid').getSelectionModel().getSelected().data;  //выбранная строка в гриде событий регулярного наблюдения состояния

		if (Ext.isEmpty(EvnRCGridRowData['EvnReanimatCondition_id'])) {
			Ext.MessageBox.alert('Внимание!', 'Не выбрана строка в списке наблюдений! ');
			return false;
		}
		var EvnReanimatCondition_id = EvnRCGridRowData['EvnReanimatCondition_id'];

		//даты начала и конца
		var NoteDatetTime = EvnRCGridRowData['EvnReanimatCondition_setDate'].format("d.m.Y") + ' ' + EvnRCGridRowData['EvnReanimatCondition_setTime']; // датавремя
		var NoteDatetTimeEnd = ''; //BOB - 28.08.2018
		if (!Ext.isEmpty(EvnRCGridRowData['EvnReanimatCondition_disDate']))
			NoteDatetTimeEnd = EvnRCGridRowData['EvnReanimatCondition_disDate'].format("d.m.Y") + ' ' + EvnRCGridRowData['EvnReanimatCondition_disTime'];
		var NoteMedService_Name = this.MedService_Name; // наименование медслужбы
		//доктор
		var NoteDocName = "";
		var index = this.findById('swERPEW_RC_Print_Doctor_FIO').store.find('MedPersonal_id',EvnRCGridRowData['EvnReanimatCondition_Doctor']);
		if (index > -1)
			NoteDocName = this.findById('swERPEW_RC_Print_Doctor_FIO').store.getAt(index).data.EvnReanimatCondition_Doctor;
		else
			NoteDocName = EvnRCGridRowData['EvnReanimatCondition_Doctor'];



		var ReanimStageType = EvnRCGridRowData['ReanimStageType_id'];
		

		var BeginDate = this.findById('swERPEW_EvnReanimatPeriod_setDate').getValue().dateFormat('d.m.Y');
		var EndDate = (!this.findById('swERPEW_EvnReanimatPeriod_disDate').getValue())||(this.findById('swERPEW_EvnReanimatPeriod_disDate').getValue() == "") ? "" : this.findById('swERPEW_EvnReanimatPeriod_disDate').getValue().dateFormat('d.m.Y');
		var Section = this.findById('swERPEW_LpuSection_Name').text; 
		//BOB - 27.09.2019
		var patient = '';
		if (this.findById('swERPEW_RC_Print_Patient_FIO').getValue())
			patient = this.pers_data.Person_Surname + ' ' + (Ext.isEmpty(this.pers_data.Person_Firname) ? '' : this.pers_data.Person_Firname.substr(0, 1) + '.') + (Ext.isEmpty(this.pers_data.Person_Secname) ? '': this.pers_data.Person_Secname.substr(0, 1) + '.') + ' ' + Date.parse(this.pers_data.Person_Birthday.date.substr(0,10)).dateFormat('d.m.Y');
				
		//var fileName = EvnRCGridRowData['ReanimStageType_id'] === 1 ? 'ReanimatArriveMulty' : EvnRCGridRowData['ReanimStageType_id'] === 2 ? 'ReanimatNoteUnion' : 'ReanimatOutEpikrizMulty';		
		var fileName = 'ReanimatNote';	
		if (this.isNeonatal(this.findById('swERPEW_ReanimatAgeGroup').getValue())) //BOB - 23.03.2020
			fileName = 'NeonatalNote';		
		var url = ((getGlobalOptions().birtpath)?getGlobalOptions().birtpath:'') + '/run?__report=report/' + fileName + '.rptdesign';			
		
		//		var url = 'http://192.168.200.16/birt-viewer//run?__report=report/cathetPrrint.rptdesign';

		url += '&NoteDatetTime=' + NoteDatetTime;
		url += '&NoteDatetTimeEnd=' + NoteDatetTimeEnd;
		url += '&NoteMedService_Name=' + NoteMedService_Name;
		url += '&NoteDocName=' + NoteDocName;
		url += '&EvnReanimatCondition_id=' + EvnReanimatCondition_id;
		url += '&ReanimStageType=' + ReanimStageType;
		url += '&Doun=' + Doun;
		url += '&patient=' + patient;  //BOB - 27.09.2019
		switch (EvnRCGridRowData['ReanimStageType_id']){
			case 1:
				url += '&NameDocument=ПОСТУПЛЕНИЕ';
				break;
			case 2:
				url += '&NameDocument=ДНЕВНИК';
				break;
			case 3:
				url += '&NameDocument=ПЕРЕВОДНОЙ ЭПИКРИЗ';
				url += '&BeginDate=' + BeginDate;
				url += '&EndDate=' + EndDate;
				url += '&Section=' + Section;
				break;				
		}
		//url += '&Doun=' + Doun;
		url += '&__format=pdf';		
		
		console.log('BOB_url=',url);  //BOB - 06.08.2017
		window.open(url, '_blank'); 
		
	},

	//cсохранение изменений реанимационного периода
	EvnReanimatPeriod_Save: function() {
		 
		var win = this;

		var ErrMessag = '';
		if ((this.findById('swERPEW_EvnReanimatPeriod_disDate').getValue() != '') && (this.findById('swERPEW_EvnReanimatPeriod_disTime').getValue() == ''))
			ErrMessag += 'время завершения периода<br>';
		if ((this.findById('swERPEW_EvnReanimatPeriod_disDate').getValue() == '') && (this.findById('swERPEW_EvnReanimatPeriod_disTime').getValue() != ''))
			ErrMessag += 'дата завершения периода<br>';
		if (((this.findById('swERPEW_EvnReanimatPeriod_disDate').getValue() != '') || (this.findById('swERPEW_EvnReanimatPeriod_disTime').getValue() != '')) && (Ext.isEmpty(this.findById('swERPEW_ReanimResultType').getValue())))
			ErrMessag += 'исход пребывания в реанимации<br>';
			
		//BOB - 17.05.2018 - для дат начала, как для дат окончания
		if (this.findById('swERPEW_EvnReanimatPeriod_setTime').getValue() == '')
			ErrMessag += 'время начала периода<br>';
		if (this.findById('swERPEW_EvnReanimatPeriod_setDate').getValue() == '')
			ErrMessag += 'дата начала периода<br>';
		if (((this.findById('swERPEW_EvnReanimatPeriod_setDate').getValue() != '') || (this.findById('swERPEW_EvnReanimatPeriod_setTime').getValue() != '')) && (Ext.isEmpty(this.findById('swERPEW_ReanimReasonType').getValue())))
			ErrMessag += 'показания для перевода в реанимацию<br>';
		if (((this.findById('swERPEW_EvnReanimatPeriod_setDate').getValue() != '') || (this.findById('swERPEW_EvnReanimatPeriod_setTime').getValue() != '')) && (Ext.isEmpty(this.findById('swERPEW_ReanimatAgeGroup').getValue()))) //BOB - 23.01.2020
			ErrMessag += 'возрастная категория<br>';

		if ( Ext.isEmpty(this.findById('swERPEW_BedProfile').getValue())) 
			ErrMessag += 'профиль коек<br>';

		//Ext.getCmp('swERPEW_CancelButton').enable();
		if (ErrMessag == '') { //если сообщение о незаполненных реквизитах пустое


			//параметры РП и общие параметры
			var data = 	{ 
				EvnReanimatPeriod_id: this.EvnReanimatPeriod_id,
				EvnReanimatPeriod_pid: this.EvnReanimatPeriod_pid,
				EvnReanimatPeriod_setDate: this.findById('swERPEW_EvnReanimatPeriod_setDate').getValue() != '' ? this.findById('swERPEW_EvnReanimatPeriod_setDate').getValue().dateFormat('Y-m-d') : '',
				EvnReanimatPeriod_setTime: this.findById('swERPEW_EvnReanimatPeriod_setTime').getValue(),
				EvnReanimatPeriod_disDate: this.findById('swERPEW_EvnReanimatPeriod_disDate').getValue() != '' ? this.findById('swERPEW_EvnReanimatPeriod_disDate').getValue().dateFormat('Y-m-d') : '',
				EvnReanimatPeriod_disTime: this.findById('swERPEW_EvnReanimatPeriod_disTime').getValue(),
				ReanimReasonType_id: this.findById('swERPEW_ReanimReasonType').getValue(),
				ReanimResultType_id: this.findById('swERPEW_ReanimResultType').getValue(),
				LpuSectionBedProfile_id: this.findById('swERPEW_BedProfile').getValue(),  //BOB - 25.10.2018
				ReanimatAgeGroup_id: this.findById('swERPEW_ReanimatAgeGroup').getValue(),  //BOB - 23.01.2020
				Lpu_id: this.Lpu_id,
				Person_id: this.Person_id,
				PersonEvn_id: this.PersonEvn_id,
				Server_id: this.Server_id
			};

			var loadMask = new Ext.LoadMask(Ext.get('swERPEW_GenralData'), {msg: "Идёт сохранение..."});
			loadMask.show();

			$.ajax({
				mode: "abort",
				type: "post",
				async: true,
				url: '/?c=EvnReanimatPeriod&m=EvnReanimatPeriod_Save',
				data: data,
				success: function(response) {
					var SaveResponse = Ext.util.JSON.decode(response);
					console.log('BOB_SaveResponse=',SaveResponse); 
					if (SaveResponse['success'] == 'false'){
						Ext.MessageBox.alert('Ошибка!', SaveResponse['Error_Msg'].substr(1).replace(/~/g,'<br>'));
					}
					else {//BOB - 17.05.2018	
						Ext.getCmp('swERPEW_CancelButton').enable(); //BOB - 02.10.2019
						if (win.findById('swERPEW_EvnReanimatPeriod_disDate').getValue() != ''){
							win.ReanimatPeriod_isClosed = true;
						}	
						if ( win.findById('swERPEW_ReanimatAction_Panel').isLoaded){
							win.findById('swERPEW_ReanimatAction_Panel').refresh();
						}
					} //BOB - 17.05.2018	
					
					loadMask.hide();
					
				}, 
				error: function() { 
					loadMask.hide();
					Ext.MessageBox.alert('Ошибка сохранения!', "При обработке запроса на сервере произошла ошибка!");
				} 
			});		
		}
		else {
			ErrMessag = 'Отсутствуют следующие реквизиты Реанимационного периода: <br><br>' + ErrMessag;
			Ext.MessageBox.alert('Внимание!', ErrMessag);
		}			
		
	//	this.callback(true);
		
	},

	//добавление антропометриченских данных //BOB - 24.01.2019
	EvnRC_AntropometrAdd: function(object, setDate) {
		var win = this; 
		//alert(object);
		
		var params = new Object();
		params.action = "add";
		params.formParams = {
			PersonHeight_id: 0,
			Person_id: this.Person_id,
			Server_id: this.Server_id
			
		};
		if(object == 'Height')
			params.formParams.PersonHeight_setDate = setDate;
		else{
			params.formParams.PersonWeight_setDate = setDate;
			params.Okei_InterNationSymbol = 'kg';
		}
		
		params.measureTypeExceptions = [ 1, 2 ];
		params.onHide = Ext.emptyFn;
		params.callback =  function(data) {
			console.log('BOB_callback_data=',data ); 
			var EvnRCGridRowData = this.findById('swERPEW_EvnReanimatCondition_Grid').getSelectionModel().getSelected().data;
			win.EvnRC_AntropometrLoud(EvnRCGridRowData['EvnReanimatCondition_disDate'], EvnRCGridRowData['EvnReanimatCondition_disTime']);
		}.createDelegate(this);

		getWnd('swPerson'+object+'EditWindow').show(params);
		//getWnd('swPersonHeightEditWindow').show(params);
	},

	//загрузка антропометриченских данных //BOB - 24.01.2019
	EvnRC_AntropometrLoud: function(Evn_disDate, Evn_disTime) {
		var win = this; 
		//console.log('BOB_Evn_disDate=',Evn_disDate );  //BOB - 24.01.2019;
		//console.log('BOB_Evn_disTime=',Evn_disTime );  //BOB - 24.01.2019;
		$.ajax({
			mode: "abort",
			type: "post",
			async: false,
			url: '/?c=EvnReanimatPeriod&m=getAntropometrData',
			data: { Person_id: this.Person_id,
				    Evn_disDate: Ext.util.Format.date(Evn_disDate, 'd.m.Y'), 
					Evn_disTime: Evn_disTime},
			success: function(response) {
				var Antropometr = Ext.util.JSON.decode(response);
				//console.log('BOB_Antropometr=',Antropometr); 
				if (Antropometr['PersonHeight'].length == 1){
					win.findById('swERPEW_RC_Height').setValue(Antropometr['PersonHeight'][0]['PersonHeight_Height'] + ' см');
					win.findById('swERPEW_RC_Height').setFieldLabel('Рост на ' + Antropometr['PersonHeight'][0]['PersonHeight_setDate']);
				} else {
					win.findById('swERPEW_RC_Height').setValue('');
					win.findById('swERPEW_RC_Height').setFieldLabel('');
				}
				if (Antropometr['PersonWeight'].length == 1){
					win.findById('swERPEW_RC_Weight').setValue(Antropometr['PersonWeight'][0]['PersonWeight_Weight'] + ' кг');
					win.findById('swERPEW_RC_Weight').setFieldLabel('Вес на ' + Antropometr['PersonWeight'][0]['PersonWeight_setDate']);
					win.findById('swERPEW_RC_IMT').setValue(Antropometr['PersonWeight'][0]['Weight_Index']);					
				} else {
					win.findById('swERPEW_RC_Weight').setValue('');
					win.findById('swERPEW_RC_Weight').setFieldLabel('');
					win.findById('swERPEW_RC_IMT').setValue('');										
				}
			}, 
			error: function() {
				alert("При обработке запроса на сервере произошла ошибка!");
			} 
		}); 
	},

	//открытие окна добавление НАЗНАЧЕНИЯ //BOB - 22.04.2019
	ReanimatPrescr_Add: function(PrescriptionType_id) {
        var option = {
                parentEvnClass_SysNick: "EvnSection",       
                userMedStaffFact: this.userMedStaffFact
            };
        if (this.userMedStaffFact) {
            option.UserLpuSection_id = !Ext.isEmpty(this.userMedStaffFact.LpuSection_id) ? this.userMedStaffFact.LpuSection_id : null;
            option.UserLpuUnitType_id = !Ext.isEmpty(this.userMedStaffFact.LpuUnitType_id) ? this.userMedStaffFact.LpuUnitType_id : null;
        }
        option.PrescriptionType_id = PrescriptionType_id;
        option.PrescriptionType_Code = PrescriptionType_id;
        option.action = 'add';
		option.callbackEditWindow = function(changedType){};

		option.data = {
            Person_id: this.Person_id,
            PersonEvn_id: this.PersonEvn_id,
            Server_id: this.Server_id,
            Person_Firname: this.pers_data.Person_Firname,
            Person_Surname: this.pers_data.Person_Surname,
            Person_Secname: this.pers_data.Person_Secname,
            Person_Age: this.getAge(   this.pers_data.Person_Birthday.date, 'amer'),
            Diag_Code: this.erp_data.Diag_Code,
            Diag_Name: this.erp_data.Diag_Name,
            Diag_id: this.erp_data.Diag_id,
            Evn_pid: this.EvnReanimatPeriod_pid ,
			MedPersonal_id: this.MedPersonal_id,
			LpuSection_id: this.erp_data.LpuSection_id,
            begDate: Date.parseDate(this.erp_data.EvnReanimatPeriod_setDate, 'd.m.Y'),  //в оригинале EvnSection_setDate, я ставлю EvnReanimatPeriod_setDate
            parentEvnClass_SysNick: "EvnSection",   
            userMedStaffFact: this.userMedStaffFact,
			electronicQueueData: false   
        };
		option.parentWindow_id = this.id;

		console.log('BOB_ReanimatPrescr_Add_option=', option); //BOB - 22.04.2019		
		sw.Promed.EvnPrescr.openEditWindow(option);		
	},

	//открытие окна редактирования НАЗНАЧЕНИЯ //BOB - 22.04.2019
	ReanimatPrescr_Edit: function(PrescriptionType_id, EvnPrescr_id) {

		var win = this;

	    var option = {		
			parentEvnClass_SysNick: "EvnSection",
			userMedStaffFact: this.userMedStaffFact,
			PrescriptionType_id: PrescriptionType_id,
			PrescriptionType_Code: PrescriptionType_id,
			action: 'edit',
			callbackEditWindow: function(){		
				win.PrescrGridLoadRawNum = win.findById('swERPEW_ReanimatPrescr_Grid').getStore().find('EvnPrescr_id',win.findById('swERPEW_ReanimatPrescr_Grid').getSelectionModel().getSelected().id); 	//установка выбранности на первой строке грда 												
				win.findById('swERPEW_ReanimatPrescr_Grid').getStore().load({
					params:{
						EvnSection_id: win.EvnReanimatPeriod_pid,
						EvnReanimatPeriod_id: win.EvnReanimatPeriod_id
					}
				});					
			},						
			data: {
				Person_id: this.Person_id,
				PersonEvn_id: this.PersonEvn_id,
				Server_id: this.Server_id,
				Person_Firname: this.pers_data.Person_Firname,
				Person_Surname: this.pers_data.Person_Surname,
				Person_Secname: this.pers_data.Person_Secname,
				Person_Birthday: Date.parse(this.pers_data.Person_Birthday.date.substr(0,10)),  //!!!!!!сравнить с ихим!!!
				Diag_id: this.erp_data.Diag_id,
				Evn_pid: this.EvnReanimatPeriod_pid,
				EvnPrescr_id: EvnPrescr_id
			}
		};
		option.parentWindow_id = this.id;// пока не знаю пригодится ли...

		//console.log('BOB_ReanimatPrescr_Edit_option=', option); //BOB - 22.04.2019		
		sw.Promed.EvnPrescr.openEditWindow(option);
	},

	//отмена НАЗНАЧЕНИЯ //BOB - 22.04.2019
	ReanimatPrescr_Cancel: function(PrescriptionType_id, EvnPrescr_id) {

		var win = this;

		sw.Promed.EvnPrescr.cancel({
			ownerWindow: this,
			getParams: function () {
				return {
					parentEvnClass_SysNick: "EvnSection",
					PrescriptionType_id: PrescriptionType_id,
					EvnPrescr_id: EvnPrescr_id
				};
			},
			callback: function(){		
				win.PrescrGridLoadRawNum = win.findById('swERPEW_ReanimatPrescr_Grid').getStore().find('EvnPrescr_id',win.findById('swERPEW_ReanimatPrescr_Grid').getSelectionModel().getSelected().id); 	//установка выбранности на первой строке грда 												
				win.findById('swERPEW_ReanimatPrescr_Grid').getStore().load({
					params:{
						EvnSection_id: win.EvnReanimatPeriod_pid,
						EvnReanimatPeriod_id: win.EvnReanimatPeriod_id
					}
				});					
			}
		});		
	},
	
	//просмотр направления в назначениях
	ReanimatPrescrDirection_View: function(EvnRPGridRowData) {
		var win = this;
		var params = {
			archiveRecord:	0,
			from_MSE:	1,
			from_MZ:	1,
			is_reload_one_section:	1,
			param_name:	"section",
			param_value:	"EvnPrescrPlan",
			parent_object_id:	"EvnPrescr_pid",
			parent_object_value:	this.EvnReanimatPeriod_pid,
			scroll_value:	null,
			user_MedStaffFact_id:	this.userMedStaffFact.MedStaffFact_id
		};

		switch (EvnRPGridRowData['PrescriptionType_id']){
			case 11:
				params.object =	"EvnPrescrLabDiag";
				params.object_id = "EvnPrescrLabDiag_id";
				params.object_value = "11";
				break;
			case 12:
				params.object =	"EvnPrescrFuncDiag";
				params.object_id = "EvnPrescrFuncDiag_id";
				params.object_value = "12";
				break;
			case 13:
				params.object =	"EvnPrescrConsUsluga";
				params.object_id = "EvnPrescrConsUsluga_id";
				params.object_value = "13";
				break;
		};		
		
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет поиск..."});
		loadMask.show();
		
		Ext.Ajax.request({
			url: '/?c=Template&m=getEvnForm',
			callback: function(opt, success, response) {
				loadMask.hide();
				if (success && response.responseText != '')
				{
					var response_obj = Ext.util.JSON.decode(response.responseText);
					if ( response_obj.success ) {
						if (response_obj['map'])
						{
							var PrescrList = {};
							switch (EvnRPGridRowData['PrescriptionType_id']){
								case 11:
									PrescrList = response_obj.map.EvnPrescrLabDiag.item;
									break;
								case 12:
									PrescrList = response_obj.map.EvnPrescrFuncDiag.item;
									break;
								case 13:
									PrescrList = response_obj.map.EvnPrescrConsUsluga.item;
									break;
							};		
							
							for	(var i in PrescrList){
								if ((PrescrList[i].data) && (PrescrList[i].data['EvnDirection_id'] == EvnRPGridRowData['EvnDirection_id'])) {
		
									var option = {
										
										ARMType:"common",
										EvnDirection_id:EvnRPGridRowData['EvnDirection_id'],
										PersonEvn_id: win.PersonEvn_id,
										Person_Birthday: Date.parse(win.pers_data.Person_Birthday.date.substr(0,10)),  //!!!!!!сравнить с ихим!!!
										Person_Firname: win.pers_data.Person_Firname,
										Person_Surname: win.pers_data.Person_Surname,
										Person_Secname: win.pers_data.Person_Secname,
										Person_id: win.Person_id,
										Server_id: win.Server_id,
										TimetableGraf_id: null,
										UserLpuSection_id: win.userMedStaffFact.LpuSection_id,
										UserMedStaffFact_id: win.userMedStaffFact.MedStaffFact_id,
										XXX_id: null,
										action: "editpaytype",
										formParams: PrescrList[i].data,
										from: "",
										personData: win.pers_data,
										userMedStaffFact: win.userMedStaffFact										
									};
									getWnd('swEvnDirectionEditWindow').show(option);
								}
							}
						}
						
					}
				}
			},
			params: params
		});		
		
	},
	
	//просмотр результатов
	ReanimatDirectionResult_View: function(EvnRPGridRowData) {
		var win = this;
		var params = {
			archiveRecord:	0,
			from_MSE:	1,
			from_MZ:	1,
			is_reload_one_section:	1,
			param_name:	"section",
			param_value:	"EvnPrescrPlan",
			parent_object_id:	"EvnPrescr_pid",
			parent_object_value:	this.EvnReanimatPeriod_pid,
			scroll_value:	null,
			user_MedStaffFact_id:	this.userMedStaffFact.MedStaffFact_id
		};

		switch (EvnRPGridRowData['PrescriptionType_id']){
			case 11:
				params.object =	"EvnPrescrLabDiag";
				params.object_id = "EvnPrescrLabDiag_id";
				params.object_value = "11";
				break;
			case 12:
				params.object =	"EvnPrescrFuncDiag";
				params.object_id = "EvnPrescrFuncDiag_id";
				params.object_value = "12";
				break;
			case 13:
				params.object =	"EvnPrescrConsUsluga";
				params.object_id = "EvnPrescrConsUsluga_id";
				params.object_value = "13";
				break;
		};		
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет поиск..."});
		loadMask.show();

		
		Ext.Ajax.request({
			url: '/?c=Template&m=getEvnForm',
			callback: function(opt, success, response) {
				loadMask.hide();
				if (success && response.responseText != '')
				{
					var response_obj = Ext.util.JSON.decode(response.responseText);
					if ( response_obj.success ) {
						if (response_obj['map'])
						{
							var PrescrList = {};
							switch (EvnRPGridRowData['PrescriptionType_id']){
								case 11:
									PrescrList = response_obj.map.EvnPrescrLabDiag.item;
									break;
								case 12:
									PrescrList = response_obj.map.EvnPrescrFuncDiag.item;
									break;
								case 13:
									PrescrList = response_obj.map.EvnPrescrConsUsluga.item;
									break;
							};		
							
							for	(var i in PrescrList){
								if ((PrescrList[i].data) && (PrescrList[i].data['EvnDirection_id'] == EvnRPGridRowData['EvnDirection_id'])) {
		console.log('BOB_PrescrList[i].data=',PrescrList[i].data);
		
									if (PrescrList[i].data['EvnXml_id']) {
										
										var win = getWnd('swEvnXmlViewWindow');
										if (win.isVisible()) {
											win.hide();
										}
										var params = {
											EvnXml_id: PrescrList[i].data['EvnXml_id'],
											onBlur: function() {
												win.hide();
											},
											onHide: Ext.emptyFn
										};
											console.log('BOB__params=',params); //BOB - 22.04.2019
										win.show(params);
									}
									else{
										Ext.MessageBox.alert('Внимание!', 'Результаты отсутствуют.');										
									}										
								}
							}
						}						
					}
				}
			},
			params: params
		});		
		
	},

	//создание прикрепления назначения к РП //BOB - 22.04.2019
	ReanimatPeriodPrescrLink_Save: function(params) {
		var win = this;
		
		params.EvnReanimatPeriod_id = this.EvnReanimatPeriod_id;
		console.log('BOB_params=',params);
		
		$.ajax({
			mode: "abort",
			type: "post",
			async: false,
			url: '/?c=EvnReanimatPeriod&m=ReanimatPeriodPrescrLink_Save',
			data: params,
			success: function(response) {
				var SaveResponse = Ext.util.JSON.decode(response);
				console.log('BOB_SaveResponse=',SaveResponse); 
				if (SaveResponse['success'] == 'true'){
					setTimeout(function(){
						win.findById('swERPEW_ReanimatPrescr_Grid').getStore().load({
							params:{
								EvnSection_id: win.EvnReanimatPeriod_pid,
								EvnReanimatPeriod_id: win.EvnReanimatPeriod_id
							}
						});	
					}, 2000);					
				}
				else	
					Ext.MessageBox.alert('Ошибка сохранения!', SaveResponse['Error_Msg']);
					//alert('Ошибка сохранения.'+SaveResponse['message']);
			}, 
			error: function() {
				Ext.MessageBox.alert('Ошибка сохранения!', "При обработке запроса на сервере произошла ошибка!");
			} 
		});		
	},
	
	//просмотр направления в назначениях
	ReanimatDirection_View: function(EvnRPGridRowData) {
		var win = this;
		var params = {
			EvnDirection_pid:	this.EvnReanimatPeriod_pid
		};
		
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет поиск..."});
		loadMask.show();
		
		Ext.Ajax.request({
			url: '/?c=EvnReanimatPeriod&m=getEvnDirectionViewData',
			callback: function(opt, success, response) {
				loadMask.hide();
				if (success && response.responseText != '')
				{
					var response_obj = Ext.util.JSON.decode(response.responseText);
					console.log('BOB_response_obj=',response_obj);
					
							
					for	(var i in response_obj){
						if ((response_obj[i]) && (response_obj[i]['EvnDirection_id'] == EvnRPGridRowData['EvnDirection_id'])) {

							var option = {

								ARMType:"common",
								EvnDirection_id:EvnRPGridRowData['EvnDirection_id'],
								PersonEvn_id: win.PersonEvn_id,
								Person_Birthday: Date.parse(win.pers_data.Person_Birthday.date.substr(0,10)),  //!!!!!!сравнить с ихим!!!
								Person_Firname: win.pers_data.Person_Firname,
								Person_Surname: win.pers_data.Person_Surname,
								Person_Secname: win.pers_data.Person_Secname,
								Person_id: win.Person_id,
								Server_id: win.Server_id,
								TimetableGraf_id: null,
								UserLpuSection_id: win.userMedStaffFact.LpuSection_id,
								UserMedStaffFact_id: win.userMedStaffFact.MedStaffFact_id,
								XXX_id: null,
								action: "view",
								formParams: response_obj[i],
								from: "",
								personData: win.pers_data,
								userMedStaffFact: win.userMedStaffFact										
							};
							getWnd('swEvnDirectionEditWindow').show(option);
						}
					}
				}
			},
			params: params
		});		
	},	

	//открытие окна добавление НАПРАВЛЕНИЯ //BOB - 22.04.2019
	ReanimatDirect_Add: function() {

		var dirTypeData  = {
			DirType_Code: 13,	
			DirType_Name: "На удаленную консультацию",	
			DirType_id: 17
		};
		var personData = {
			PersonEvn_id: this.PersonEvn_id,
			Person_Birthday: Date.parse(this.pers_data.Person_Birthday.date.substr(0,10)),  //!!!!!!сравнить с ихим!!!
			Person_Firname: this.pers_data.Person_Firname,
			Person_Surname: this.pers_data.Person_Surname,
			Person_Secname: this.pers_data.Person_Secname,
			Person_id: this.Person_id,
			Server_id: this.Server_id,
			Person_IsDead: null
		};
		var directionData = {
			ARMType_id: this.userMedStaffFact.ARMType_id,
			Diag_id: this.Diag_id,
			DirType_id: 17,
			EvnDirection_pid: this.EvnReanimatPeriod_pid,
			LpuSection_id: this.LpuSection_id,
			Lpu_sid: this.Lpu_id,
			MedPersonal_id: this.MedPersonal_id,
			MedService_id: this.erp_data.MedService_id,
			MedStaffFact_id: this.MedStaffFact_id,
			PersonEvn_id: this.PersonEvn_id,
			Person_id: this.Person_id,
			Server_id: this.Server_id,
			withDirection: true,
			parentWindow_id: this.id
		};
        var onDirection = Ext.emptyFn;

		console.log('BOB_ReanimatDirect_Add_directionData=', directionData); //BOB - 22.04.2019		
		
		getWnd('swUslugaComplexMedServiceListWindow').show({
			userMedStaffFact: this.userMedStaffFact,
			personData: personData,
			dirTypeData: dirTypeData,
			directionData: directionData,
			onDirection: onDirection
		});
	},

	//создание прикрепления направлений к РП //BOB - 22.04.2019
	ReanimatPeriodDirectLink_Save: function(params) {
		var win = this;
		
		params.EvnReanimatPeriod_id = this.EvnReanimatPeriod_id;
		console.log('BOB_params=',params);
		
		$.ajax({
			mode: "abort",
			type: "post",
			async: false,
			url: '/?c=EvnReanimatPeriod&m=ReanimatPeriodDirectLink_Save',
			data: params,
			success: function(response) {
				var SaveResponse = Ext.util.JSON.decode(response);
				console.log('BOB_SaveResponse=',SaveResponse); 
				if (SaveResponse['success'] == 'true'){
					setTimeout(function(){
						win.findById('swERPEW_ReanimatDirect_Grid').getStore().load({
							params:{
								EvnSection_id: win.EvnReanimatPeriod_pid,
								EvnReanimatPeriod_id: win.EvnReanimatPeriod_id,
								Lpu_id: win.Lpu_id
							}
						});	
					}, 2000);					
				}
				else	
					Ext.MessageBox.alert('Ошибка сохранения!', SaveResponse['Error_Msg']);
					//alert('Ошибка сохранения.'+SaveResponse['message']);
			}, 
			error: function() {
				Ext.MessageBox.alert('Ошибка сохранения!', "При обработке запроса на сервере произошла ошибка!");
			} 
		});		
	},

	//Отменить направление
	ReanimatDirect_Cancel: function(EvnRPGridRowData){

		var win = this;

		onSuccess = function(){
			win.findById('swERPEW_ReanimatDirect_Grid').getStore().load({
				params:{
					EvnSection_id: win.EvnReanimatPeriod_pid,
					EvnReanimatPeriod_id: win.EvnReanimatPeriod_id,
					Lpu_id: win.Lpu_id
				}
			});

		};


		var param = {
					cancelType: 'cancel',
					ownerWindow: this,
					EvnDirection_id: EvnRPGridRowData['EvnDirection_id'],
					DirType_Code: 13,
					TimetableGraf_id:  null,
					TimetableMedService_id: ('TimetableMedService' == EvnRPGridRowData['timetable']) ? EvnRPGridRowData['timetable_id'] : null,
					TimetableStac_id:  null,
					EvnQueue_id: ('EvnQueue' == EvnRPGridRowData['timetable']) ?  EvnRPGridRowData['timetable_id'] : null,
					allowRedirect: true,
					userMedStaffFact: this.userMedStaffFact,
					personData: {
						PersonEvn_id: this.PersonEvn_id,
						Person_Birthday: Date.parse(this.pers_data.Person_Birthday.date.substr(0,10)),  //!!!!!!сравнить с ихим!!!
						Person_Firname: this.pers_data.Person_Firname,
						Person_Surname: this.pers_data.Person_Surname,
						Person_Secname: this.pers_data.Person_Secname,
						Person_id: this.Person_id,
						Server_id: this.Server_id,
						Person_IsDead: null
					},
					callback: onSuccess
				};


		sw.Promed.Direction.cancel(param);





	},

	//Добавить документ - прикрепление документа к направлению
	ReanimatDirectDoc_Add: function(EvnRPGridRowData){

		console.log('BOB_ReanimatDirectDoc_Add_EvnRPGridRowData=',EvnRPGridRowData); //BOB - 22.04.2019

		var win = this;

		var params = {
			onHide: function() {
			},
			callback: function(data) {
				if(typeof data != 'object' || ! data.evnXmlData || ! data.evnXmlData.EvnXml_id) {
					return false;
				}

				var EvnXml_id = data.evnXmlData.EvnXml_id;

				var params = {
					EvnDirection_id: EvnRPGridRowData['EvnDirection_id'],
					EvnXml_id: EvnXml_id
				};

				Ext.Ajax.request({
					showErrors: false,
					url: '/?c=EvnXml&m=createEvnXmlDirectionLink',
					params: params,
					callback: function(options, success, response) {
						var response_obj = Ext.util.JSON.decode(response.responseText); //никак не используется

						win.DirectGridLoadRawNum = win.findById('swERPEW_ReanimatDirect_Grid').getStore().find('EvnDirection_id',win.findById('swERPEW_ReanimatDirect_Grid').getSelectionModel().getSelected().id); 	//установка выбранности на первой строке грда
						win.findById('swERPEW_ReanimatDirect_Grid').getStore().load({
							params:{
								EvnSection_id: win.EvnReanimatPeriod_pid,
								EvnReanimatPeriod_id: win.EvnReanimatPeriod_id,
								Lpu_id: win.Lpu_id
							}
						});
					}
				});



//				this.createEvnXmlDirectionLink(params);

			}.createDelegate(this),

			Person_id: win.Person_id
		};

		console.log('BOB_ReanimatDirectDoc_Add_params=',params); //BOB - 22.04.2019

		getWnd('swEmkDocumentsListWindow').show(params);


	},

	//открепление документа от направления
	ReanimatDirectDoc_Del: function(EvnRPGridRowData){
		var win = this;

		Ext.Ajax.request({
			showErrors: false,
			url: '/?c=EvnXml&m=deleteEvnXmlDirectionLink',
			params: {EvnXmlDirectionLink_id : EvnRPGridRowData['EvnXmlDirectionLink_id']},
			callback: function(options, success, response) {
				var response_obj = Ext.util.JSON.decode(response.responseText); //никак не используется

				win.findById('swERPEW_ReanimatDirectLinkedDocs_Grid').getStore().load({
					params:{
						EvnDirection_id: EvnRPGridRowData['EvnDirection_id']
					}
				});
			}
		});
	},

	//заполнение просмотр / редактирование бланка к направлению
	ReanimatDirectBlank: function(EvnRPGridRowData, SrcHandl){
		console.log('BOB_ReanimatDirectBlank_EvnRPGridRowData=',EvnRPGridRowData); //BOB - 22.04.2019

		var win = this;
		var params = {};

		if (SrcHandl == 'DirectBlank') {
			params.EvnClass_id = 27;
			params.EvnXml_id = EvnRPGridRowData['EvnXmlDir_id'];
			params.Evn_id = EvnRPGridRowData['EvnDirection_id'];
			params.UslugaComplex_id = null;
			params.XmlType_id = "20";
			params.action = (!EvnRPGridRowData['EvnXmlDir_id']) ? 'add' : 'edit';
			params.title = "Бланк направления";
			params.userMedStaffFact = this.userMedStaffFact;

			params.onHide = function() {
				win.DirectGridLoadRawNum = win.findById('swERPEW_ReanimatDirect_Grid').getStore().find('EvnDirection_id',win.findById('swERPEW_ReanimatDirect_Grid').getSelectionModel().getSelected().id); 	//установка выбранности на первой строке грда
				win.findById('swERPEW_ReanimatDirect_Grid').getStore().load({
					params:{
						EvnSection_id: win.EvnReanimatPeriod_pid,
						EvnReanimatPeriod_id: win.EvnReanimatPeriod_id,
						Lpu_id: win.Lpu_id
					}
				});
			};
		}
		else {
			params.EvnClass_id = EvnRPGridRowData['EvnClass_id'];
			params.EvnXml_id = EvnRPGridRowData['EvnXml_id'];
			params.Evn_id = EvnRPGridRowData['Evn_id'];
			params.UslugaComplex_id = null;
			params.XmlType_id = EvnRPGridRowData['XmlType_id'];
			params.action = 'view';
			params.title = "Прикреплённый документ";
			params.userMedStaffFact = this.userMedStaffFact;

		}

		var XmlEditWindow = getWnd('swEvnXmlEditWindow');
		if (XmlEditWindow.isVisible()) {
			XmlEditWindow.hide();
		}
		console.log('BOB_ReanimatDirectBlank_params=',params); //BOB - 22.04.2019
		XmlEditWindow.show(params);


	},

	//открытие окна добавление/редактирования курса лечения //BOB - 07.11.2019
	ReanimatDrugCourse_Add_Edit: function(action) {

		var win = this;
		var EvnRPGridRowData = this.findById('swERPEW_ReanimatDrugCourse_Grid').getSelectionModel().getSelected().data;  //выбранная строка в гриде событий реанимационных мероприятий

		var formParams = {
			EvnCourseTreat_id: action == 'edit' ? EvnRPGridRowData.EvnCourse_id : null,
			EvnCourseTreat_pid: this.EvnReanimatPeriod_pid,
			EvnCourseTreat_setDate: null,
			LpuSection_id: this.LpuSection_id,
			MedPersonal_id: this.MedPersonal_id,
			Morbus_id: null,
			PersonEvn_id: this.PersonEvn_id,
			Server_id: this.Server_id,
			action:action
		};

		callbackFunc = function(data) {
			console.log('BOB_callbackFunc_data=',data);  //BOB - 07.11.2019
			win.ReanimatDrugCourse_Save(data, action);
		};

		var params = {
			UserLpuSection_id: this.LpuSection_id,
			UserLpuUnitType_id: this.erp_data.LpuUnitType_id,
			action: action,
			parentEvnClass_SysNick: "EvnSection",
			userMedStaffFact: this.userMedStaffFact,
			formParams: formParams,
			callback: callbackFunc
		};
		console.log('BOB_ReanimatDrugCourse_Addparams=',params);  //BOB - 07.11.2019
		getWnd('swEvnCourseTreatEditWindow').show(params);
	},

	//создание прикрепления курса лечения к РП //BOB - 07.11.2019
	ReanimatDrugCourse_Save: function(data){
		win = this;

		var params = {
			EvnReanimatPeriod_id: this.EvnReanimatPeriod_id,
			EvnCourseTreat_id: data.EvnCourseTreat_id
		};

		Ext.Ajax.request({
			showErrors: false,
			url: '/?c=EvnReanimatPeriod&m=ReanimatPeriodDrugCourse_Save',
			params: params,
			failure: function(response, options) {
				showSysMsg(langs('При получении данных для проверок произошла ошибка!'));
			},
			success: function(response, action)
			{
				if (response.responseText) {
					var response_obj = Ext.util.JSON.decode(response.responseText); //никак не используется
					if (action == 'edit')
						win.DrugCourseGridLoadRawNum = win.findById('swERPEW_ReanimatDrugCourse_Grid').getStore().find('EvnCourseTreat_id',response_obj.EvnCourseTreat_id);
					else
						win.DrugCourseGridLoadRawNum = win.findById('swERPEW_ReanimatDrugCourse_Grid').getStore().getCount(); //установка выбранности на новой строке грида
					win.findById('swERPEW_ReanimatDrugCourse_Grid').getStore().load({
						params:{
							EvnSection_id: win.EvnReanimatPeriod_pid,
							EvnReanimatPeriod_id: win.EvnReanimatPeriod_id,
							Lpu_id: win.Lpu_id
						}
					});
				}
			}
		});
	},

	//отмена КУРСА //BOB - 07.11.2019
	ReanimatDrugCourse_Cancel: function(PrescriptionType_id, EvnCourse_id) {

		var win = this;

		sw.Promed.EvnPrescr.cancelEvnCourse({
			ownerWindow: this,
			getParams: function () {
				return {
					parentEvnClass_SysNick: "EvnSection",
					PrescriptionType_id: PrescriptionType_id,
					EvnCourse_id: EvnCourse_id
				};
			},
			callback: function(){
				win.DrugCourseGridLoadRawNum = 0; 	//установка выбранности на первой строке грда
				win.findById('swERPEW_ReanimatDrugCourse_Grid').getStore().load({
					params:{
						EvnSection_id: win.EvnReanimatPeriod_pid,
						EvnReanimatPeriod_id: win.EvnReanimatPeriod_id,
						Lpu_id: win.Lpu_id
					}
				});
			}
		});
	},

	//открытие окна редактирования назначения в рамках курса лечения //BOB - 07.11.2019
	ReanimatPrescrTreatDrug_Edit: function(action) {

		var win = this;
		var DrugCourse_GridRowData = this.findById('swERPEW_ReanimatDrugCourse_Grid').getSelectionModel().getSelected().data;  //выбранная строка в гриде событий реанимационных мероприятий
		var PrescrTreatDrug_GridRowData = this.findById('swERPEW_ReanimatPrescrTreatDrug_Grid').getSelectionModel().getSelected().data;  //выбранная строка в гриде

		var formParams = {
			EvnCourse_id: DrugCourse_GridRowData.EvnCourse_id,
			EvnPrescrTreat_id: PrescrTreatDrug_GridRowData.EvnPrescrTreat_id,
			EvnPrescrTreat_pid: this.EvnReanimatPeriod_pid,
			EvnPrescrTreat_setDate: PrescrTreatDrug_GridRowData.EvnPrescrTreat_setDate,
			PersonEvn_id: this.PersonEvn_id,
			Server_id: this.Server_id
		};

		callbackFunc = function(data) {
			console.log('BOB_callbackFunc_data=',data);  //BOB - 07.11.2019
			win.PrescrTreatDrugGridLoadRawNum = win.findById('swERPEW_ReanimatPrescrTreatDrug_Grid').getStore().find('EvnPrescrTreat_id',data.EvnPrescrTreat_id);
			win.DrugCourseGridLoadRawNum = win.findById('swERPEW_ReanimatDrugCourse_Grid').getStore().find('EvnCourseTreat_id',data.EvnCourse_id);
			win.findById('swERPEW_ReanimatDrugCourse_Grid').getStore().load({
				params:{
					EvnSection_id: win.EvnReanimatPeriod_pid,
					EvnReanimatPeriod_id: win.EvnReanimatPeriod_id,
					Lpu_id: win.Lpu_id
				}
			});

			//win.ReanimatDrugCourse_Save(data, action);
		};

		var params = {
			LpuSection_id: this.LpuSection_id,
			action: action,
			changedType: null,
			parentEvnClass_SysNick: "EvnSection",
			formParams: formParams,
			callback: callbackFunc
		};
		console.log('BOB_ReanimatDrugCourse_Addparams=',params);  //BOB - 07.11.2019
		getWnd('swEvnPrescrTreatEditWindow').show(params);
	},

	//отмена назначения в рамках курса лечения //BOB - 07.11.2019
	ReanimatPrescrTreatDrug_Cancel: function() {

		var win = this;
		var PrescrTreatDrug_GridRowData = this.findById('swERPEW_ReanimatPrescrTreatDrug_Grid').getSelectionModel().getSelected().data;  //выбранная строка в гриде
		var PrescrCount = this.findById('swERPEW_ReanimatPrescrTreatDrug_Grid').store.data.items.length;

		sw.Promed.EvnPrescr.cancel({
			ownerWindow: this,
			getParams: function () {
				return {
					parentEvnClass_SysNick: "EvnSection",
					PrescriptionType_id: 5,
					EvnPrescr_id: PrescrTreatDrug_GridRowData.EvnPrescrTreat_id,
				};
			},
			callback: function(){
				win.DrugCourseGridLoadRawNum = PrescrCount == 1 ? 0 : win.findById('swERPEW_ReanimatDrugCourse_Grid').getStore().find('EvnCourseTreat_id',PrescrTreatDrug_GridRowData.EvnCourse_id);
				win.findById('swERPEW_ReanimatDrugCourse_Grid').getStore().load({
					params:{
						EvnSection_id: win.EvnReanimatPeriod_pid,
						EvnReanimatPeriod_id: win.EvnReanimatPeriod_id,
						Lpu_id: win.Lpu_id
					}
				});
			}
		});
	},

	//выполнение назначения в рамках курса лечения //BOB - 07.11.2019
	ReanimatPrescrTreatDrug_Exec: function(evn) {

		var win = this;
		var PrescrTreatDrug_GridRowData = this.findById('swERPEW_ReanimatPrescrTreatDrug_Grid').getSelectionModel().getSelected().data;  //выбранная строка в гриде

		var conf = {

			Diag_id: this.Diag_id,
			EvnDirection_id: null,
			EvnPrescr_IsExec: PrescrTreatDrug_GridRowData.EvnPrescr_IsExec,
			EvnPrescr_id: PrescrTreatDrug_GridRowData.EvnPrescrTreat_id,
			EvnPrescr_pid: this.EvnReanimatPeriod_pid,
			EvnPrescr_rid: this.EvnReanimatPeriod_rid,
			EvnPrescr_setDate: PrescrTreatDrug_GridRowData.EvnPrescrTreat_setDate,
			ObservTimeType_id: null,
			PersonEvn_id: this.PersonEvn_id,
			Person_Birthday: this.pers_data.Person_Birthday,
			Person_Firname: this.pers_data.Person_Firname,
			Person_Secname: this.pers_data.Person_Secname,
			Person_Surname: this.pers_data.Person_Surname,
			Person_id: this.pers_data.Person_id,
			PrescriptionType_Code: 5,
			PrescriptionType_id: 5,
			Server_id: this.pers_data.Server_id,
			TableUsluga_id: null,
			UslugaId_List: null,
			allowChangeTime: false,
			btnId: "swERPEW_PrescrTreatDrug_Exec",  //без разници что - лишь бы не null
			coords: evn.getXY(), //[1587, 553]
			ownerWindow: this,
			userMedStaffFact: this.userMedStaffFact,
			onExecCancel: Ext.emptyFn,
			onExecSuccess: function(data){
				console.log('BOB_ReanimatPrescrTreatDrug_Exec_onExecSuccess_conf=',data);  //BOB - 07.11.2019
				win.PrescrTreatDrugGridLoadRawNum = win.findById('swERPEW_ReanimatPrescrTreatDrug_Grid').getStore().find('EvnPrescrTreat_id',PrescrTreatDrug_GridRowData.EvnPrescrTreat_id);
				win.findById('swERPEW_ReanimatPrescrTreatDrug_Grid').getStore().load({
					params:{
						EvnCourse_id: PrescrTreatDrug_GridRowData['EvnCourse_id']
					}
				});
			}
		};
		console.log('BOB_ReanimatPrescrTreatDrug_Exec__conf=',conf);  //BOB - 07.11.2019
		sw.Promed.EvnPrescr.exec(conf);
	},

	//отмена выполнения назначения в рамках курса лечения //BOB - 07.11.2019
	ReanimatPrescrTreatDrug_UnExec: function() {

		var win = this;
		var PrescrTreatDrug_GridRowData = this.findById('swERPEW_ReanimatPrescrTreatDrug_Grid').getSelectionModel().getSelected().data;  //выбранная строка в гриде

		var conf = {
			EvnDirection_id: null,
			EvnPrescrDay_id: PrescrTreatDrug_GridRowData.EvnPrescrTreat_id,
			EvnPrescr_IsExec: PrescrTreatDrug_GridRowData.EvnPrescr_IsExec,
			EvnPrescr_IsHasEvn: PrescrTreatDrug_GridRowData.EvnPrescr_IsHasEvn, //!!!!!!!!!!разбирусь вместе с управлением доступом кнопками, пока только если нет списания лекарства
			PrescriptionType_id: 5,
			ownerWindow: this,
			onCancel: Ext.emptyFn,
			onSuccess: function(){
				win.PrescrTreatDrugGridLoadRawNum = win.findById('swERPEW_ReanimatPrescrTreatDrug_Grid').getStore().find('EvnPrescrTreat_id',PrescrTreatDrug_GridRowData.EvnPrescrTreat_id);
				win.findById('swERPEW_ReanimatPrescrTreatDrug_Grid').getStore().load({
					params:{
						EvnCourse_id: PrescrTreatDrug_GridRowData['EvnCourse_id']
					}
				});
			}
		};
		sw.Promed.EvnPrescr.unExec(conf);
	},

	//управление кнопками грида назначения в рамках курса лечения //BOB - 07.11.2019
	ReanimatPrescrTreatDrug_ButtonManag: function(record){
		var view_act = this.action == 'view' ? true : false;  //режим просмотра
		var	Grid = this.findById('swERPEW_ReanimatPrescrTreatDrug_Grid');  //грид

		if(record == null)
			record = Grid.getSelectionModel().getSelected();  //выбранная строка в гриде

		var	GridRowData = record.data;

		//Редактировать - swERPEW_PrescrTreatDrug_edit - дизейблю если: режим просмотра или назначение выполнено или статус не равен "рабочее"
		Grid.topToolbar.items.items[0].setDisabled(view_act || GridRowData.EvnPrescr_IsExec == 2 || GridRowData.PrescriptionStatusType_id != 1);
		//Отменить назначение - swERPEW_PrescrTreatDrug_Del - дизейблю если: режим просмотра или назначение выполнено
		Grid.topToolbar.items.items[1].setDisabled(view_act || GridRowData.EvnPrescr_IsExec == 2);
		//Выполненить - swERPEW_PrescrTreatDrug_Exec - дизейблю если: режим просмотра или назначение выполнено
		Grid.topToolbar.items.items[2].setDisabled(view_act || GridRowData.EvnPrescr_IsExec == 2);
		//Отменить выполнение - swERPEW_PrescrTreatDrug_UnExec - дизейблю если: режим просмотра или назначение выполнено или имеется списание лекарства
		Grid.topToolbar.items.items[3].setDisabled(view_act || GridRowData.EvnPrescr_IsExec != 2 || GridRowData.EvnPrescr_IsHasEvn == 2);
	},

	//функция расчёта полных лет
	//dateString - дата рождения в виде строки
	//form - формат dateString: 'rus' - 'dd.mm.yyyy', 'amer' - 'yyyy.mm.dd'
	getAge: function (dateString, form ) {
		var TextFieldDate = new Date();
		var day = 0;
		var month = 0;
		var year = 0;
		if (form == 'rus'){
			day = parseInt(dateString.substr(0, 2));
			month = parseInt(dateString.substr(3, 2));
			year = parseInt(dateString.substr(6, 4));			
		}
		else if (form == 'amer'){
			day = parseInt(dateString.substr(8, 2));
			month = parseInt(dateString.substr(5, 2));
			year = parseInt(dateString.substr(0, 4));			
		}
		var birthDate = new Date(year, month - 1, day);
		var age = TextFieldDate.getFullYear() - birthDate.getFullYear();
		var m = TextFieldDate.getMonth() - birthDate.getMonth();
		if (m < 0 || (m === 0 && TextFieldDate.getDate() < birthDate.getDate())) {
			age--;
		}
		return age;
    },

	//функция расчёта полных месяцев
	//dateString - дата рождения в виде строки
	//form - формат dateString: 'rus' - 'dd.mm.yyyy', 'amer' - 'yyyy.mm.dd'
	getAge_month: function (dateString, form ) {
		var TextFieldDate = new Date();
		var day = 0;
		var month = 0;
		var year = 0;
		if (form == 'rus'){
			day = parseInt(dateString.substr(0, 2));
			month = parseInt(dateString.substr(3, 2));
			year = parseInt(dateString.substr(6, 4));			
		}
		else if (form == 'amer'){
			day = parseInt(dateString.substr(8, 2));
			month = parseInt(dateString.substr(5, 2));
			year = parseInt(dateString.substr(0, 4));			
		}
		var birthDate = new Date(year, month - 1, day);
		var age = TextFieldDate.getFullYear() - birthDate.getFullYear();
		var m = TextFieldDate.getMonth() - birthDate.getMonth();
		month = age * 12 + m;
		if (TextFieldDate.getDate() < birthDate.getDate()) {
			month--;
		}
		return month;
    },

	//контроль времени //BOB - 12.07.2019
	checkTime(hours, minutes) {	
		//console.log('BOB_checkTime_hours minutes=', hours+' '+minutes);
		if ((hours.lastIndexOf('_') > -1)  || (minutes.lastIndexOf('_') > -1))  return false;
		
		var int_Hours = parseInt(hours, 10);
		if (isNaN(int_Hours) || int_Hours < 0 || int_Hours > 23 ) return false;
		
		var int_Minutes = parseInt(minutes, 10);
		if (isNaN(int_Minutes) || int_Minutes < 0 || int_Minutes > 59 ) return false;
		
		return true;
	},

	//контроль даты //BOB - 12.07.2019
	checkDate(days, months, years, min_y, max_y) {	
		//console.log('BOB_checkDate_days months years=', days+' '+months+' '+years+' '+min_y+' '+max_y);
		
		if ((days.lastIndexOf('_')> -1) || (months.lastIndexOf('_')> -1) || (years.lastIndexOf('_')> -1))  return false;
		
		var int_days = parseInt(days, 10);
		var int_months = parseInt(months, 10);
		var int_years = parseInt(years, 10);
		
		if (isNaN(int_years) || int_years < min_y || int_years > max_y ) return false;	
		var leap_year = int_years % 4 == 0;
		
		if (isNaN(int_months) || int_months < 1 || int_months > 12 ) return false;
		
		var days_in_month = [31, leap_year ? 29 : 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
		if (isNaN(int_days) || int_days < 1 || int_days > days_in_month[int_months - 1] ) return false;
		
		return true;
	},

	// задержка
	sleep(ms) {
		//console.log('BOB_Date().getTime()=', new Date().getTime());
		ms += new Date().getTime();
		//console.log('BOB_ms=', ms);
		while (new Date() < ms){}
		//console.log('BOB_Date().getTime()_2=', new Date().getTime());	
	},

	// отображение по Неонатальному варианту?
	isNeonatal(ReanimatAgeGroup) {
		return ReanimatAgeGroup && ReanimatAgeGroup.inlist([1,2])
	},

	

	openRepositoryObservEditWindow: function (action) {
		if (this.findById('swERPEW_RepositoryObserv_Panel').hidden) {
			return false;
		}

		if (Ext.isEmpty(action) || !action.inlist([ 'add', 'edit', 'view'])) {
			return false;
		}

		var
			grid = this.findById('swERPEW_RepositoryObserv_Grid'),
			win = this;

		if (win.action == 'view') {
			if (action == 'add') {
				return false;
			}
			else if (action == 'edit') {
				action = 'view';
			}
		}

		var params = {};

		params.action = action;
		params.useCase = 'reanim';
		params.callback = function() {
			grid.loadData({
				globalFilters: {
					Evn_id: win.EvnReanimatPeriod_id
				}
			});
		};
		params.Evn_id = win.EvnReanimatPeriod_id;
		params.MedStaffFact_id = win.MedStaffFact_id;
		params.Person_id = win.Person_id;

		if (action.inlist(['edit','view'])) {
			var selected_record = grid.getGrid().getSelectionModel().getSelected();

			if (!selected_record || !selected_record.get('RepositoryObserv_id')) {
				return false;
			}

			params.RepositoryObserv_id = selected_record.get('RepositoryObserv_id');
		} else {
			if (win.erp_data && win.erp_data.CovidType_id) {
				params.CovidType_id = win.erp_data.CovidType_id;
			}
		}

		getWnd('swRepositoryObservEditWindow').show(params);
	},
	deleteRepositoryObserv: function(){
		var grid = this.findById('swERPEW_RepositoryObserv_Grid');
		var rec = grid.getGrid().getSelectionModel().getSelected();
		var win = this;

		if (!rec || !rec.get('RepositoryObserv_id')) {
			return false;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Удаление..."});
		loadMask.show();

		Ext.Ajax.request({
			callback: function (options, success, response) {
				loadMask.hide();

				if (success) {
					grid.loadData({
						globalFilters: {
							Evn_id: win.EvnReanimatPeriod_id
						}
					});
				}
				else {
					sw.swMsg.alert('Ошибка', 'При удалении возникли ошибки');
					return false;
				}
			},
			params: {
				RepositoryObserv_id: rec.get('RepositoryObserv_id')
			},
			url: '/?c=RepositoryObserv&m=delete'
		});
	}
});


/*
 * BOB - 27.08.2019 
 * конструктор класса
 * панель для реализации каждого параметра шкал 
 */
sw.Promed.SwScaleParameter = function(config)
{
	Ext.apply(this, config);
	sw.Promed.SwScaleParameter.superclass.constructor.call(this);
};
/*
 * BOB - 27.08.2019 
 * класса, реализующий панель каждого параметра шкал 
 */
Ext.extend(sw.Promed.SwScaleParameter, Ext.Panel,
{
	nam_begin: '',
	scale_name: '',
	parameter_name: '',
	text_anchor: '',
	lbl_text: '',
	text_width: 0,
	combo_width: 0,
	value_width: 0,
	win: null,
	layout:'form',
	labelWidth: 0,
	border:true,
	style: 'border-top: 1px solid #ffffff; border-left: 1px solid  #ffffff; border-bottom: 1px solid #99bbe8;border-right: 1px solid #99bbe8; padding: 2px; ',
	
	initComponent: function() {

		var Name = this.nam_begin + '_' + this.scale_name + '_' + this.parameter_name;
		//console.log('BOB_this.win=',this.win);
		var win = this.win;
		var Panel_name = this.nam_begin + '_' + this.scale_name + '_ScalePanel'
		
		Ext.apply(this, 
		{
			items: [
				{									
					layout:'column',
					anchor: this.text_anchor, // '95%'
					items:[
						//Лейбл
						{									
							layout:'form',
							style:'margin-bottom: 5px;',
							width: this.text_width, // 140,
							items:[
								new Ext.form.Label({
									id: Name + '_Lbl', //'swERPEW_glasgow_eye_response_Lbl',
									text: this.lbl_text //  'Открывание глаз'
								})					
							]
						},
						//значение
						{									
							layout:'form',
							style:'margin-bottom: 5px; color: blue; font-weight: bold;border-top: 1px solid #99bbe8; border-left: 1px solid #99bbe8 ; border-bottom: 1px solid #ffffff;border-right: 1px solid #ffffff;',
							width: this.value_width,
							items:[
								new Ext.form.Label({
									id: Name + '_Val', //     'swERPEW_glasgow_eye_response_Val',
									xtype: 'label',
									text: '0'
								})					
							]
						}
					]
				},
				{
					id: Name + '_Hid', //'swERPEW_glasgow_eye_response_Hid',
					value:0,
					xtype:'hidden'
				},
				//combo 
				{							
					layout:'column',
					border:false,
					items:[	

						{
							xtype: 'combo',
							allowBlank: false,
							hiddenName: this.scale_name + '_' + this.parameter_name, //'glasgow_eye_response',									
							disabled: false,
							id: Name, // 'swERPEW_glasgow_eye_response',
							mode:'local',
							listWidth: this.combo_width, // 160,
							width: this.combo_width, // 160,
							triggerAction : 'all',
							editable: false,
							displayField:'ScaleParameterResult_Name',
							valueField:'ScaleParameterResult_id',
							tpl: '<tpl for="."><div class="x-combo-list-item">'+
								'{ScaleParameterResult_Name} '+ '&nbsp;' +
								'</div></tpl>' ,
							listeners: {
								'select': function(combo, record, index) {
									var NewRow = (win.findById('swERPEW_EvnScales_Grid').getSelectionModel().getSelected().data['EvnScale_id'] == 'New_GUID_Id')
									if (record.data.ScaleParameterResult_id != ''){
										win.findById(Name + '_Val').setText(record.data.ScaleParameterResult_Value);
										if (NewRow) win.findById(Name + '_Hid').setValue(record.data.ScaleParameterResult_id);
									}
									else{
										win.findById(Name + '_Val').setText(0);
										if (NewRow)  win.findById(Name + '_Hid').setValue(null);
									}
									win.findById(Panel_name).overall_results(combo.id);	//'swERPEW_glasgow_ScalePanel'
								}
							}
						} 
					]
				}
			]
		});
		sw.Promed.SwScaleParameter.superclass.initComponent.apply(this, arguments);
	}
});
Ext.reg('swscaleparameter', sw.Promed.SwScaleParameter);

