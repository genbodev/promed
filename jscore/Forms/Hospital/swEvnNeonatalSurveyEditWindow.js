/**
 * swEvnNeonatalSurveyEditWindow - окно - форма ввода/редактирования «Наблюдение состояния младенца» 
 *
 *
 * @package			Hospital
 * @access			public
 * @copyright		Copyright (c) 2020 Swan Ltd.
 * @author			Muskat Boris (bob@npk-progress.com)
 * @version			24.01.2020
 * jscore\Forms\Hospital\swEvnNeonatalSurveyEditWindow.js
 */

//initComponent: function() - инициализация объектов формы
//show: function() - запуск формы
//AuscultatoryBuild() - рисование панелей дахания аускультативноG
//EvnNeonatalSurvey_add: function() - добавление записи
//EvnNeonatalSurvey_getData: function(EvnNeonatalSurvey_id) - получение данных наблюдения из БД
//EvnNeonatalSurvey_copy: function(Add_Params, EvnNeonatalSurvey_id) - копирование запипи наблюдения младенца	
//EvnNeonatalSurvey_view: function() - загрузка данных наблюдения в поля
//EvnNeonatalSurvey_ButtonManag: function() - управление видимостью и активностью элементов
//EvnNeonatalSurvey_Save: function(b,e) - сохранение регулярного наблюдения состояния младенца
	
//EvnNS_AntropometrLoud: function(Evn_disDate, Evn_disTime) - загрузка антропометриченских данных 
//EvnNS_AntropometrAdd: function(object, setDate) - добавление антропометриченских данных 
 
//NeonatalTrauma_Build(Trauma) - построение блока травмы (перелома) младенца
//NeonatalTrauma_add() добапвление блока травмы (перелома) младенца
//NeonatalTrauma_del(NeonatalTrauma_id) - удаление блока травмы (перелома) младенца

//EvnNeonatalSurvey_LoadScaleData: function(SysNick, Result) - внесение значений шкал в соответствующие поля, вызывается из окна РП при сохранении новых расчётов по шкалам
//EvnNeonatalSurvey_LoadIVLParams: function(IVL, RP_setDT, RP_disDT) - установка аппарата и параметров ИВЛ по вызову из другого окна (РП) 
//EvnNeonatalSurvey_getIVLParams: function(IVL) - возвращает строку параметров ИВЛ сформерованную из объекта ИВЛ
	 
 
sw.Promed.swEvnNeonatalSurveyEditWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swEvnNeonatalSurveyEditWindow',
	objectName:'swEvnNeonatalSurveyEditWindow',
	objectSrc:'/jscore/Forms/Hospital/swEvnNeonatalSurveyEditWindow.js',

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
	fromObject: null,
	callback: Ext.emptyFn,
	ParentObject: '',

	EvnNeonatalSurvey_id: '',
	EvnNeonatalSurvey_pid: '',
	EvnNeonatalSurvey_rid: '',

	par_data: null,
	SideType: [],
	NeonatalSurvey_data: {},
	//BreathAuscult_records: {}, 


	changedDatas: false,		//признак редактирования данных
	FirstConditionLoad: true, //признак первого открытыя формы


	listeners:{
		'hide':function (win) {
		},
		'maximize':function (win) {
		},
		'restore':function (win) {
			//перестройка панелей при сжатии окна, чтобы сжать гриды
			win.fireEvent('maximize', win);
		},
		success: function(source, params) {
	 	/* source - string - источник события (например форма)
	 	 * params - object - объект со свойствами в завис-ти от источника
	 	 */
			// console.log('BOB__success_source=',source); 
			// console.log('BOB__success_params',params); 
			
			// if (source == 'EvnPrescrUslugaInputWindow')
			// 	this.ReanimatPeriodPrescrLink_Save(params);
			// else if (source == 'UslugaComplexMedServiceListWindow')
			// 	this.ReanimatPeriodDirectLink_Save(params);
		}
	},

	initComponent: function() {
        var win = this; // текущий объект

		//Панель Персональные данные / диагноз  /  профильное отделение	
		this.PersonPanel = new Ext.Panel({
			layout:'form',
			border:false,
			height:75,
			width:1000,
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
									id:'swENSEW_PersonInfo',
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
									id: 'swENSEW_EvnPS_NumCard'
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
									id: 'swENSEW_LpuSection_Name'
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
									id: 'swENSEW_EvnSection_setDate'
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
									id: 'swENSEW_swENSEW_BaseDiag'
								})					
							]
						}
					]							
				}
			]
		});

		//Основная Панель всех данных о ходе реанимационного периода
		this.FormPanel = new Ext.form.FormPanel({
			name: 'swENSEW_Form',
			id: 'swENSEW_Form',

			autoScroll:true,
			autoheight:true,
			bodyBorder:false,
			border:false,
			resizable: true,
			buttonAlign: 'left',
			frame: true,
			labelAlign: 'right',
			region:'center',			
			items: [
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
									id: 'swENSEW_EvnReanimatConditionPanelsManag',
									iconCls: 'view16',  //
									text: '',
									isCollaps: true,
									handler: function(b,e)
									{
										Ext.select('fieldset', true, 'swENSEW_Form').each(function(el){
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
						//Дата Время регулярного наблюдения состояния / Поступил из
						{									
							layout:'column',
							//style:'margin-left: 5px; margin-top: 5px;font-size: 12px',
							region: 'center',
							items:[

								//панель - этап - документ cобытие регулярного наблюдения состояния    
								{	
									layout:'form',
									width: 350,
									labelWidth: 110,
									border:true,
									//region: 'west',
									items:[	
										//combo этап - документ событие регулярного наблюдения состояния    
										{
											id: 'swENSEW_EvnNeonatalSurveyStage',
											hiddenName: 'EvnEvnNeonatalSurveytage',									
											xtype: 'swextemporalcomptypecombo',
											fieldLabel: langs('Этап - документ'),
											allowBlank: false,
											comboSubject: 'ReanimStageType',
											width: 200,
											lastQuery: '',
											listeners: {
												'select': function(combo, record, index, from) {

													//при вызове со специфики новорожденного, не проверяем
													if (win.ARMType!='stas_pol') {
														//если выбрано "Поступление", то проверяю было ли оно уже, если было устанавливаю "дневник"
														if (record.data.ReanimStageType_id == 1) {
															if (win.par_data.EntryExist == 1) {
																Ext.MessageBox.alert('Внимание!', 'Поступление уже имеется!');
																combo.setValue(2); // устанавливаю в комбо этап - "дневник"
																var index = combo.getStore().find('ScaleParameterResult_id', 2);//нахожу индекс в store комбо
																record = combo.getStore().getAt(index);  // нахожу record по index,
															}
														}
													}
														
													//установка видимости реквизитов и предустановки в зависимости от стадии
													win.findById('swENSEW_ArriveFrom_panel').setVisible(false);  //"Поступил из"
													win.findById('swENSEW_EvnNeonatalSurvey_disDate_Pnl').setVisible(false);  //"дата завершения" 
													win.findById('swENSEW_EvnNeonatalSurvey_disTime_Pnl').setVisible(false);  //"время завершения" 

													switch (combo.getValue()){
														case 1:
															win.findById('swENSEW_ArriveFrom_panel').setVisible(true);  //"Поступил из"
															break;
														case 2:																		
															win.findById('swENSEW_EvnNeonatalSurvey_disDate_Pnl').setVisible(true);  //"дата завершения" 
															win.findById('swENSEW_EvnNeonatalSurvey_disTime_Pnl').setVisible(true);  //"время завершения" 
															break;																	
														case 3:
															break;																	
													}
													
													//предустановка даты-времени 
													var curDate = getValidDT(getGlobalOptions().date, ''); // считываю из глобальных параметров текущую дату
													if (combo.getValue() == 2){    
														win.findById('swENSEW_EvnNeonatalSurvey_setDate').setValue(win.par_data.Previous_setDate);
														win.findById('swENSEW_EvnNeonatalSurvey_setTime').setValue(win.par_data.Previous_setTime);
														win.findById('swENSEW_EvnNeonatalSurvey_disDate').setValue(curDate);// в дату окончания события регулярного наблюдения состояния - текущую дату
														win.findById('swENSEW_EvnNeonatalSurvey_disTime').setValue(''); // во время окончания события регулярного наблюдения состояния - пустоту																
													}
													else{
														win.findById('swENSEW_EvnNeonatalSurvey_setDate').setValue(curDate);// в дату события регулярного наблюдения состояния - текущую дату
														win.findById('swENSEW_EvnNeonatalSurvey_setTime').setValue(''); // во время события регулярного наблюдения состояния - пустоту
														win.findById('swENSEW_EvnNeonatalSurvey_disDate').setValue('');// в дату окончания события регулярного наблюдения состояния - текущую дату
														win.findById('swENSEW_EvnNeonatalSurvey_disTime').setValue(''); // во время окончания события регулярного наблюдения состояния - пустоту																
													}

													//заполнение объекта данных
													var EvnNeonatalSurvey = win.NeonatalSurvey_data.EvnNeonatalSurvey;
												
													//если record существует
													if(record){
														EvnNeonatalSurvey['ReanimStageType_id'] = record.data.ReanimStageType_id;
														EvnNeonatalSurvey['EvnNeonatalSurvey_setDate'] = win.findById('swENSEW_EvnNeonatalSurvey_setDate').getValue();
														EvnNeonatalSurvey['EvnNeonatalSurvey_setTime'] = win.findById('swENSEW_EvnNeonatalSurvey_setTime').getValue();
														EvnNeonatalSurvey['EvnNeonatalSurvey_disDate'] = win.findById('swENSEW_EvnNeonatalSurvey_disDate').getValue();
														EvnNeonatalSurvey['EvnNeonatalSurvey_disTime'] = win.findById('swENSEW_EvnNeonatalSurvey_disTime').getValue();
													}
												},
												//защита от... если умудрится сереть строку из поля
												'change':function (field, newValue, oldValue) {
													if(Ext.isEmpty(newValue)){
														this.NeonatalSurvey_data.EvnNeonatalSurvey.ReanimStageType_id = newValue;	
													}
												}.createDelegate(this)

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
											id: 'swENSEW_EvnNeonatalSurvey_setDate',
											listeners:{
												'change':function (field, newValue, oldValue) {	
													this.NeonatalSurvey_data.EvnNeonatalSurvey.EvnNeonatalSurvey_setDate = Ext.util.Format.date(newValue, 'd.m.Y');												
													this.changedDatas = true;
												}.createDelegate(this),
												'keydown':function (inp, e) {
												}.createDelegate(this)
											},
											name:'EvnNeonatalSurvey_setDate',
											plugins:[ new Ext.ux.InputTextMask('99.99.9999', false) ],
											selectOnFocus:true,
											width:100,
											xtype:'swdatefield',
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
													this.NeonatalSurvey_data.EvnNeonatalSurvey.EvnNeonatalSurvey_setTime = newValue;													
													this.changedDatas = true;
												}.createDelegate(this),
												'keydown':function (inp, e) {
												}
											},
											id: 'swENSEW_EvnNeonatalSurvey_setTime',
											name:'EvnNeonatalSurvey_setTime',
											onTriggerClick:function () {
												var time_field = this.findById('swENSEW_EvnNeonatalSurvey_setTime'); 
												if (time_field.disabled) {
													return false;
												}
												setCurrentDateTime({
													callback:function () {
														this.NeonatalSurvey_data.EvnNeonatalSurvey.EvnNeonatalSurvey_setTime = time_field.getValue();													
														this.changedDatas = true;
														this.findById('swENSEW_EvnNeonatalSurvey_setDate').fireEvent('change', this.findById('swENSEW_EvnNeonatalSurvey_setDate'), this.findById('swENSEW_EvnNeonatalSurvey_setDate').getValue());
													}.createDelegate(this),
													dateField:this.findById('swENSEW_EvnNeonatalSurvey_setDate'),
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
								//Дата окончания периода регулярного наблюдения состояния
								{									
									layout:'form',
									id: 'swENSEW_EvnNeonatalSurvey_disDate_Pnl',
									width: 240,
									labelWidth: 130,
									items:[										
										{
											allowBlank: false,
											fieldLabel:'Окончание:  Дата',
											format:'d.m.Y',
											id: 'swENSEW_EvnNeonatalSurvey_disDate',
											name:'EvnNeonatalSurvey_disDate',
											listeners:{
												'change':function (field, newValue, oldValue) {															
													this.NeonatalSurvey_data.EvnNeonatalSurvey.EvnNeonatalSurvey_disDate = Ext.util.Format.date(newValue, 'd.m.Y');
													this.changedDatas = true;
												}.createDelegate(this),
												'keydown':function (inp, e) {
												}.createDelegate(this)
											},
											plugins:[ new Ext.ux.InputTextMask('99.99.9999', false) ],
											selectOnFocus:true,
											width:100,
											xtype:'swdatefield'
										}										
									]
								},	
								//Время окончания периода регулярного наблюдения состояния
								{									
									layout:'form',
									id: 'swENSEW_EvnNeonatalSurvey_disTime_Pnl',
									width: 120,
									labelWidth: 50,
									items:[	

										{
											fieldLabel:'Время',
											allowBlank: false,
											listeners:{
												'change':function (field, newValue, oldValue) {
													this.NeonatalSurvey_data.EvnNeonatalSurvey.EvnNeonatalSurvey_disTime = newValue;													
													this.changedDatas = true;
												}.createDelegate(this),
												'keydown':function (inp, e) {
												}.createDelegate(this)
											},
											id: 'swENSEW_EvnNeonatalSurvey_disTime',
											name:'EvnNeonatalSurvey_disTime',
											onTriggerClick:function () {
												var time_field = this.findById('swENSEW_EvnNeonatalSurvey_disTime'); 
												if (time_field.disabled) {
													return false;
												}
												setCurrentDateTime({
													callback:function () {
														this.NeonatalSurvey_data.EvnNeonatalSurvey.EvnNeonatalSurvey_disTime = time_field.getValue();													
														this.changedDatas = true;
														this.findById('swENSEW_EvnNeonatalSurvey_disDate').fireEvent('change', this.findById('swENSEW_EvnNeonatalSurvey_disDate'), this.findById('swENSEW_EvnNeonatalSurvey_disDate').getValue());
													}.createDelegate(this),
													dateField:this.findById('swENSEW_EvnNeonatalSurvey_disDate'),
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
									id:'swENSEW_ArriveFrom_panel',
									//style:'margin-top: 4px;',
									labelWidth:110,
									border:false,
									items:[	
										{
											id: 'swENSEW_ArriveFrom',
											hiddenName: 'ArriveFrom',									
											xtype: 'swextemporalcomptypecombo',
											fieldLabel: langs('Поступил из'),
											labelSeparator: '',
											allowBlank: false, 
											comboSubject: 'ReanimArriveFromType',
											width: 240,
											lastQuery: '',
											listeners: {
												'change':function (field, newValue, oldValue) {
													this.NeonatalSurvey_data.EvnNeonatalSurvey.ReanimArriveFromType_id = newValue;												
													this.changedDatas = true;
												}.createDelegate(this)
											}
										}																																																																												
									]
								}
							]
						}
					]
				},
				//ПАНЕЛЬ РЕДАКТИРОВАНИЯ События регулярного наблюдения состояния	
				{
					layout:'form',
					id:'swENSEW_Base_Data',
					border:true,
					width:1307,
					style: 'border-top: 1px solid #ffffff; border-left: 1px solid  #ffffff; border-bottom: 1px solid #99bbe8;border-right: 1px solid #99bbe8; padding: 2px; ',
					items:[	
						//панель - параметры печати  
						{
							id: 'swENSEW_PrintParams_Panel',
							labelWidth: 200,
							xtype: 'fieldset',
							autoHeight: true,
							title: langs('Параметры печати'),
							collapsible: true,
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
								//Отображать ФИО пациента
								{
									layout:'form',
									border: false,
									items:[
										{
											fieldLabel: 'Отображать ФИО пациента',
											labelSeparator: '',
											name: 'Print_Patient_FIO',
											id: 'swENSEW_Print_Patient_FIO',
											//tabIndex: form.firstTabIndex + 12,
											xtype: 'checkbox',
											checked: true
										}
									]
								},
								//ФИО врача
								{
									layout:'form',
									border:false,
									items:[

										{
											xtype: 'combo',
											allowBlank: false,
											hiddenName: 'Print_Doctor_FIO', 
											disabled: false,
											id: 'swENSEW_Print_Doctor_FIO',
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
													this.NeonatalSurvey_data.EvnNeonatalSurvey.EvnNeonatalSurvey_Doctor = newValue;													
													this.changedDatas = true;
												}.createDelegate(this)
											}
										}
									]
								}

							]
						},
						//Панель Антропометрические данные
						{
							id: 'swENSEW_Antropometr_Panel',
							xtype: 'fieldset',
							autoHeight: true,
							title: langs('Антропометрические данные'),
							collapsible: true,
							//collapsed:true,   // !!!!!!!!!!!!!!!!!!!!на время разработки, чтобы дальнейшие области быстрее видеть
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
											id: 'swENSEW_Height',
											fieldLabel:'Рост',
											labelSeparator: '',
											width: 80,
											disabled: true
										}
									]
								},
								new Ext.Button({
									id: 'swENSEW_Height_Add_Button',
									iconCls: 'add16',
									text: '',
									handler: function(b,e)
									{
										this.EvnNS_AntropometrAdd('Height', this.findById('swENSEW_EvnNeonatalSurvey_setDate').getValue());
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
											id: 'swENSEW_Weight',
											fieldLabel:'Вес',
											labelSeparator: '',
											width: 80,
											disabled: true
										}
									]
								},
								new Ext.Button({
									id: 'swENSEW_Weight_Add_Button',
									iconCls: 'add16',
									text: '',
									handler: function(b,e)
									{
										this.EvnNS_AntropometrAdd('Weight', this.findById('swENSEW_EvnNeonatalSurvey_setDate').getValue());
									}.createDelegate(this)
								})//,
								// //Индекс массы тела //закомментарено поскольку вроде не нужно, но вдруг понадобится
								// {							
								// 	layout:'form',
								// 	style:'margin-top: 1px;',
								// 	labelWidth:60,
								// 	border:false,									
								// 	items:[	
								// 		{
								// 			xtype: 'textfield',
								// 			id: 'swENSEW_IMT',
								// 			fieldLabel:'ИМТ',
								// 			labelSeparator: '',
								// 			width: 80,
								// 			disabled: true
								// 		}
								// 	]
								// }
							]	
						},
						//Панель Жизненно-важные показатели 
						{
							id: 'swENSEW_VitalParams_Panel',
							labelWidth: 200,
							xtype: 'fieldset',
							autoHeight: true,
							title: langs('Жизненно-важные показатели'),
							collapsible: true,
							//collapsed:true,   // !!!!!!!!!!!!!!!!!!!!на время разработки, чтобы дальнейшие области быстрее видеть
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
								{
									layout:'column',
									border:false,
									items:[	
										//Температура периферическая
										{							
											layout:'form',
											style:'margin-top: 2px;',
											labelWidth:200,
											border:false,
											items:[	
												new Ext.form.NumberField({
													value: 0,
													id: 'swENSEW_TemperPeripher',
													fieldLabel:'Температура периферическая',
													labelSeparator: '',
													enableKeyEvents: true,
													width: 60,
													//plugins:[ new Ext.ux.InputTextMask('99.9', true) ],
													listeners:{
														'keyup':function (obj, e) {
															if (!Ext.isEmpty(obj.getValue()))
																this.NeonatalSurvey_data.NeonatalSurveyParam.TemperPeripher = obj.getValue();													
															else delete this.NeonatalSurvey_data.NeonatalSurveyParam.TemperPeripher;												
															this.changedDatas = true;
														}.createDelegate(this)
													}
												})
											]
										},
										{xtype:'label',text: '°C',style: 'font-size: 10pt;  margin-left: 2pt; margin-top: 2pt'},											
										//Температура центральная
										{							
											layout:'form',
											style:'margin-top: 2px;',
											labelWidth:200,
											border:false,
											items:[	
												new Ext.form.NumberField({
													value: 0,
													id: 'swENSEW_TemperCentr',
													fieldLabel:'Температура центральная',
													labelSeparator: '',
													enableKeyEvents: true,
													width: 60,
													//plugins:[ new Ext.ux.InputTextMask('99.9', true) ],
													listeners:{
														'keyup':function (obj, e) {
															if (!Ext.isEmpty(obj.getValue()))
																this.NeonatalSurvey_data.NeonatalSurveyParam.TemperCentr = obj.getValue();													
															else delete this.NeonatalSurvey_data.NeonatalSurveyParam.TemperCentr;												
															this.changedDatas = true;
														}.createDelegate(this)
													}
												})
											]
										},
										{xtype:'label',text: '°C',style: 'font-size: 10pt;  margin-left: 2pt; margin-top: 2pt'},											
										//Частота дыхания
										{							
											layout:'form',
											style:'margin-top: 2px;',
											labelWidth:200,
											border:false,
											items:[	
												new Ext.form.NumberField({
													value: 0,
													id: 'swENSEW_BreathFrequency',
													fieldLabel:'Частота дыхания',
													labelSeparator: '',
													enableKeyEvents: true,
													width: 60,
													//plugins:[ new Ext.ux.InputTextMask('99.9', true) ],
													listeners:{
														'keyup':function (obj, e) {
															if (!Ext.isEmpty(obj.getValue()))
																this.NeonatalSurvey_data.NeonatalSurveyParam.BreathFrequency = obj.getValue();	
															else delete this.NeonatalSurvey_data.NeonatalSurveyParam.BreathFrequency;												
															this.changedDatas = true;
														}.createDelegate(this)
													}
												})
											]
										},
										{xtype:'label',text: 'вд/мин',style: 'font-size: 10pt;  margin-left: 2pt; margin-top: 2pt'},											
										//Частота сердечных сокращений
										{							
											layout:'form',
											style:'margin-top: 2px;',
											labelWidth:200,
											border:false,
											items:[	
												new Ext.form.NumberField({
													value: 0,
													id: 'swENSEW_HeartFrequency',
													fieldLabel:'Частота сердечных сокращений',
													labelSeparator: '',
													enableKeyEvents: true,
													width: 60,
													//plugins:[ new Ext.ux.InputTextMask('99.9', true) ],
													listeners:{
														'keyup':function (obj, e) {
															if (!Ext.isEmpty(obj.getValue()))
																this.NeonatalSurvey_data.NeonatalSurveyParam.HeartFrequency = obj.getValue();													
															else delete this.NeonatalSurvey_data.NeonatalSurveyParam.HeartFrequency;												
															this.changedDatas = true;
														}.createDelegate(this)
													}
												})
											]
										},
										{xtype:'label',text: 'уд/мин',style: 'font-size: 10pt;  margin-left: 2pt; margin-top: 2pt'},											
									]
								},
								//панель артериальное давление		
								{									
									layout:'column',
									id: 'swENSEW_Heart_Pressure',
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
										{xtype: 'label', text: 'систолическое', style:'margin-top: 9px; margin-left: 10px; margin-right: 5px;'},
										new Ext.form.NumberField({
											value: 0,
											id: 'swENSEW_Heart_Pressure_syst',
											width: 40,
											style:'margin-top: 6px;',
											listeners:{
												'change':function (field, newValue, oldValue) {
													if((newValue == null) || (newValue == ''))
														field.setValue('0');															
													this.findById('swENSEW_Heart_Pressure').calculation(true);			
												}.createDelegate(this)
											}
										}),
										{xtype: 'label', text: 'диастолическое', style:'margin-top: 9px; margin-left: 10px; margin-right: 5px;'},
										new Ext.form.NumberField({
											value: 0,
											id: 'swENSEW_Heart_Pressure_diast',
											width: 40,
											style:'margin-top: 6px;',
											listeners:{
												'change':function (field, newValue, oldValue) {
													if((newValue == null) || (newValue == ''))
														field.setValue('0');															
													this.findById('swENSEW_Heart_Pressure').calculation(true);			
												}.createDelegate(this)
											}													
										}),
										{xtype: 'label', text: 'среднее', style:'margin-top: 9px; margin-left: 10px;  margin-right: 5px;'},
										new Ext.form.NumberField({
											value: 0,
											id: 'swENSEW_Heart_Pressure_sredn',
											width: 40,
											style:'margin-top: 6px;',
											listeners:{
												'change':function (field, newValue, oldValue) {
													if((newValue == null) || (newValue == ''))
														field.setValue('0');															
													this.findById('swENSEW_Heart_Pressure').calculation(true);			
												}.createDelegate(this)
											}													
										}),
										{xtype: 'label', text: 'мм.рт.ст.', style:'margin-top: 9px; margin-left: 3px;'}
									],
									calculation: function(from_interf) { 
										if (from_interf) {
											this.NeonatalSurvey_data.NeonatalSurveyParam.Pressure = win.findById('swENSEW_Heart_Pressure_syst').getValue() + '/' + win.findById('swENSEW_Heart_Pressure_diast').getValue() + '/' + win.findById('swENSEW_Heart_Pressure_sredn').getValue();													
											this.changedDatas = true;
										}
									}.createDelegate(this)
								},
							]
						},
						//Панель Состояние 
						{
							id: 'swENSEW_Condition_Panel',
							xtype: 'fieldset',
							autoHeight: true,
							title: langs('Состояние'),
							collapsible: true,
							//collapsed:true,   // !!!!!!!!!!!!!!!!!!!!на время разработки, чтобы дальнейшие области быстрее видеть
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
								//combo - состояние пациента
								{							
									layout:'form',
									style:'margin-top: 4px;',
									labelWidth:110,
									border:false,
									items:[	
										{
											id: 'swENSEW_Condition',
											hiddenName: 'Condition',									
											xtype: 'swextemporalcomptypecombo',
											fieldLabel: langs('Состояние'),
											allowBlank: false,
											comboSubject: 'ReanimConditionType',
											width: 250,
											lastQuery: '',
											listeners: {
												'change':function (field, newValue, oldValue) {
													this.NeonatalSurvey_data.EvnNeonatalSurvey.ReanimConditionType_id = newValue;												
													this.changedDatas = true;
												}.createDelegate(this),
												'expand': function	(combo)	{
													combo.getStore().clearFilter();
													combo.getStore().filterBy(function (rec) {
														return rec.get('ReanimConditionType_id').inlist([2,3,6,7,8]);
													});
												}.createDelegate(this)												
											}
										}																
									]
								},
								//pSOFA
								{							
									layout:'form',
									style:'margin-top: 4px;',
									labelWidth:60,
									border:false,
									items:[	
										{
											xtype: 'textfield',
											id: 'swENSEW_psofa',
											fieldLabel:'По pSOFA',
											labelSeparator: '',
											width: 60,
											disabled: true
											//style:'margin-top: 6px; '//,

										}
									]
								},
								//PELOD-2
								{							
									layout:'form',
									style:'margin-top: 4px;',
									labelWidth:80,
									border:false,
									items:[	
										{
											xtype: 'textfield',
											id: 'swENSEW_pelod',
											fieldLabel:'По PELOD-2',
											labelSeparator: '',
											width: 60,
											disabled: true
										}
									]
								}
							]
						},
						//Панель Сознание
						{
							id: 'swENSEW_Conscious_Panel',
							xtype: 'fieldset',
							autoHeight: true,
							title: langs('Сознание'),
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
													id: 'swENSEW_Conscious',
													hiddenName: 'Conscious',
													xtype: 'swreanimatconsciouscombo',
													width: 240,
													listeners: {
														'select': function(combo, record, index) {
															this.NeonatalSurvey_data.NeonatalSurveyParam.ConsciousType_id = record.data.ConsciousType_id;											
															this.changedDatas = true;
															//если медикоментозная седация
															if(record.data.ConsciousType_id == 1) {
																Ext.Ajax.request({
																	showErrors: false,
																	url: '/?c=EvnNeonatalSurvey&m=getSedationMedicat',
																	params: { 
																		EvnNeonatalSurvey_pid: this.EvnNeonatalSurvey_pid,
																		EvnNeonatalSurvey_setDate: this.NeonatalSurvey_data['EvnNeonatalSurvey'].EvnNeonatalSurvey_setDate,
																		EvnNeonatalSurvey_setTime: this.NeonatalSurvey_data['EvnNeonatalSurvey'].EvnNeonatalSurvey_setTime,
																		EvnNeonatalSurvey_disDate: this.NeonatalSurvey_data['EvnNeonatalSurvey'].EvnNeonatalSurvey_disDate,
																		EvnNeonatalSurvey_disTime: this.NeonatalSurvey_data['EvnNeonatalSurvey'].EvnNeonatalSurvey_disTime,
																		ReanimatActionType_SysNick: 'sedation'
																	 },
																	failure: function(response, options) {
																		showSysMsg(langs('При получении данных Наблюдение состояния младенца произошла ошибка!'));
																	},
																	callback: function(opt, success, response) {
																		if (success && response.responseText != '')
																		{
																			var SedationMedicat = Ext.util.JSON.decode(response.responseText);
																			var SedationMedicatTxt = win.findById('swENSEW_SedationMedicatTxt').getValue();
																			console.log('BOB_SedationMedicat=',SedationMedicat);
																			for(var i in SedationMedicat) {
																				if (typeof SedationMedicat[i] == 'object') {

																					SedationMedicatTxt +=  ((i==0 && Ext.isEmpty(SedationMedicatTxt)) ? '' : ', ') + SedationMedicat[i].ReanimDrugType_Name + ' ' + SedationMedicat[i].ReanimDrug_Dose + ' ' + SedationMedicat[i].ReanimDrug_Unit;
																				}
																			}
																			win.findById('swENSEW_SedationMedicatTxt').setValue(SedationMedicatTxt);
																			win.NeonatalSurvey_data.NeonatalSurveyParam.SedationMedicatTxt = SedationMedicatTxt;
																		}
																	}
																});
																														
															}
														}.createDelegate(this),
														'expand': function(combo) {
															combo.getStore().clearFilter();
															combo.getStore().filterBy(function (rec) {
																return rec.get('ConsciousType_id').inlist([1,2,10,11,12,13]);
															});
														}
													} 
												} 
											]
										},
										//Glasgow - баллы
										{
											layout:'form',
											style:'margin-top: 2px;',
											labelWidth:80,
											border:false,
											items:[
												{
													xtype: 'textfield',
													id: 'swENSEW_glasgow',
													fieldLabel:'По Glasgow',
													labelSeparator: '',
													width: 60,
													disabled: true
												}
											]
										},
										//Медикамент седации
										{							
											layout:'form',
											style:'margin-top: 2px;',
											labelWidth:130,
											border:false,
											items:[	
												{
													allowBlank: true,
													labelSeparator: '',
													name: 'swENSEW_SedationMedicatTxt',
													id: 'swENSEW_SedationMedicatTxt',
													width: 650,
													fieldLabel:'Медикамент седации',
													value:'',
													xtype: 'textfield',
													listeners:{
														'change':function (field, newValue, oldValue) {
															this.NeonatalSurvey_data.NeonatalSurveyParam.SedationMedicatTxt = newValue;
															this.changedDatas = true;
														}.createDelegate(this)
													}
												}
											]	
										}
									]	
								},
								//панель - сознание по шкалам
								{
									layout:'column',
									border:false,
									items:[
										//COMFORT
										{
											layout:'form',
											style:'margin-top: 2px;',
											labelWidth:110,
											border:false,
											items:[
												{
													xtype: 'textfield',
													id: 'swENSEW_comfort',
													fieldLabel:'По COMFORT',
													labelSeparator: '',
													width: 60,
													disabled: true
												}
											]
										},
										//N-PASS
										{
											layout:'form',
											style:'margin-top: 2px;',
											labelWidth:60,
											border:false,
											items:[
												{
													xtype: 'textfield',
													id: 'swENSEW_npass',
													fieldLabel:'По N-PASS',
													labelSeparator: '',
													width: 60,
													disabled: true
												}
											]
										},
										//NIPS
										{
											layout:'form',
											style:'margin-top: 2px;',
											labelWidth:60,
											border:false,
											items:[
												{
													xtype: 'textfield',
													id: 'swENSEW_nips',
													fieldLabel:'По NIPS',
													labelSeparator: '',
													width: 60,
													disabled: true
												}
											]
										},
										{xtype:'label',text: 'ГИЭ по SARNAT',style: 'font-size: 10pt;  margin-left: 2pt; margin-top: 2pt'},
										//ГИЭ по SARNAT
										{									
											layout:'form',
											labelWidth:1,
											style: 'margin-top: 4px;  margin-left: 4px; margin-bottom: 2px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											items:[
												new Ext.form.RadioGroup({
													id:'swENSEW_sarnat',
													labelSeparator: '',
													vertical: true,
													FromInterface: true,
													columns: 2,
													items: [
														{boxLabel: '---', name: 'sarnat', inputValue: 0, width: 120}, 
														{boxLabel: 'умеренная ГИЭ — Sarnat I степени', name: 'sarnat', inputValue: 1, width: 250}, 
														{boxLabel: 'тяжелая ГИЭ — Sarnat II степени', name: 'sarnat', inputValue: 2, width: 250},
														{boxLabel: 'выраженная ГИЭ — Sarnat III степени', name: 'sarnat', inputValue: 3, width: 250}
													],
													listeners: {
														'change': function(field, checked) {
															if((checked) && field.FromInterface){
																if ((this.NeonatalSurvey_data.NeonatalSurveyParam.sarnat) || (String(checked.inputValue) != '0')) {
																	this.NeonatalSurvey_data.NeonatalSurveyParam.sarnat = checked.inputValue;									
																	this.changedDatas = true;									
																}
															}
															field.FromInterface = true;
														}.createDelegate(this)
													}
												})	
											]
										}
									]
								}
							]
						},										
						//Панель Поза
						{
							id: 'swENSEW_Рose_Panel',
							xtype: 'fieldset',
							autoHeight: true,
							title: langs('Поза'),
							collapsible: true,
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
									labelWidth:1,
									style: 'margin-top: 4px;  margin-left: 4px; margin-bottom: 2px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
									items:[
										new Ext.form.RadioGroup({
											id:'swENSEW_Рose',
											labelSeparator: '',
											vertical: true,
											FromInterface: true,
											columns: 5,
											items: [
												{boxLabel: '---', name: 'Рose', inputValue: 0, width: 50}, 
												{boxLabel: 'физиологическая', name: 'Рose', inputValue: 1, width: 130}, 
												{boxLabel: 'полуфлексорная', name: 'Рose', inputValue: 2, width: 130},
												{boxLabel: 'распластанная', name: 'Рose', inputValue: 3, width: 130},
												{boxLabel: 'иное', name: 'Рose', inputValue: 4, width: 80}
											],
											listeners: {
												'change': function(field, checked) {
													if((checked) && field.FromInterface){
														if ((this.NeonatalSurvey_data.NeonatalSurveyParam.Рose) || (String(checked.inputValue) != '0')) {
															this.NeonatalSurvey_data.NeonatalSurveyParam.Рose = checked.inputValue;									
															this.changedDatas = true;
														}
													}
													field.FromInterface = true;
												}.createDelegate(this)
											}
										})	
									]
								},
								{
									allowBlank: true,
									fieldLabel: '',
									labelSeparator: '',
									name: 'swENSEW_РoseTxt',
									id: 'swENSEW_РoseTxt',
									width: 750,
									value:'',
									style: 'margin-top: 4px;  margin-left: 4px; ',
									xtype: 'textfield',
									listeners:{
										'change':function (field, newValue, oldValue) {
											this.NeonatalSurvey_data.NeonatalSurveyParam.РoseTxt = newValue;
											this.changedDatas = true;
										}.createDelegate(this)
									}
								}
							]
						},
						//Панель Реакция на осмотр
						{
							id: 'swENSEW_CheckReact_Panel',
							xtype: 'fieldset',
							autoHeight: true,
							title: langs('Реакция на осмотр'),
							collapsible: true,
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
									labelWidth:1,
									style: 'margin-top: 4px;  margin-left: 4px; margin-bottom: 2px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
									items:[
										new Ext.form.RadioGroup({
											id:'swENSEW_CheckReact',
											labelSeparator: '',
											vertical: true,
											FromInterface: true,
											columns: 5,
											items: [
												{boxLabel: '---', name: 'CheckReact', inputValue: 0, width: 50}, 
												{boxLabel: 'адекватная', name: 'CheckReact', inputValue: 1, width: 130}, 
												{boxLabel: 'снижена', name: 'CheckReact', inputValue: 2, width: 130},
												{boxLabel: 'беспокойство', name: 'CheckReact', inputValue: 3, width: 130},
												{boxLabel: 'иное', name: 'CheckReact', inputValue: 4, width: 80}
											],
											listeners: {
												'change': function(field, checked) {
													if((checked) && field.FromInterface){
														if ((this.NeonatalSurvey_data.NeonatalSurveyParam.CheckReact) || (String(checked.inputValue) != '0')) {
															this.NeonatalSurvey_data.NeonatalSurveyParam.CheckReact = checked.inputValue;									
															this.changedDatas = true;
														}
													}
													field.FromInterface = true;
												}.createDelegate(this)
											}
										})	
									]
								},
								{
									allowBlank: true,
									fieldLabel: '',
									labelSeparator: '',
									name: 'swENSEW_CheckReactTxt',
									id: 'swENSEW_CheckReactTxt',
									width: 750,
									value:'',
									style: 'margin-top: 4px;  margin-left: 4px; ',
									xtype: 'textfield',
									listeners:{
										'change':function (field, newValue, oldValue) {
											this.NeonatalSurvey_data.NeonatalSurveyParam.CheckReactTxt = newValue;
											this.changedDatas = true;
										}.createDelegate(this)
									}
								}
							]
						},
						//Панель Крик
						{
							id: 'swENSEW_Scream_Panel',
							xtype: 'fieldset',
							autoHeight: true,
							title: langs('Крик'),
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
								{									
									layout:'column',
									items:[
										{									
											layout:'form',
											labelWidth:1,
											style: 'margin-top: 4px;  margin-left: 4px; margin-bottom: 1px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											items:[
												new Ext.form.RadioGroup({
													id:'swENSEW_Scream1',
													labelSeparator: '',
													vertical: true,
													FromInterface: true,
													columns: 3,
													items: [
														{boxLabel: '---', name: 'Scream1', inputValue: 0, width: 50}, 
														{boxLabel: 'вызывается', name: 'Scream1', inputValue: 1, width: 130}, 
														{boxLabel: 'отсутствует', name: 'Scream1', inputValue: 2, width: 130}
													],
													listeners: {
														'change': function(field, checked) {
															win.findById('swENSEW_Scream_Panel').change_handler(field, checked);
														}
													}
												}),
											]
										},
										{									
											layout:'form',
											labelWidth:1,
											style: 'margin-top: 4px;  margin-left: 4px; margin-bottom: 1px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											items:[
												new Ext.form.RadioGroup({
													id:'swENSEW_Scream2',
													labelSeparator: '',
													vertical: true,
													FromInterface: true,
													columns: 7,
													items: [
														{boxLabel: '---', name: 'Scream2', inputValue: 0, width: 50, hidden: true}, 
														{boxLabel: 'громкий', name: 'Scream2', inputValue: 1, width: 130}, 
														{boxLabel: 'слабый', name: 'Scream2', inputValue: 2, width: 130},
														{boxLabel: 'стонущий', name: 'Scream2', inputValue: 3, width: 130},
														{boxLabel: 'пронзительный', name: 'Scream2', inputValue: 4, width: 130},
														{boxLabel: 'монотонный', name: 'Scream2', inputValue: 5, width: 130},
														{boxLabel: 'иное', name: 'Scream2', inputValue: 6, width: 80}
													],
													listeners: {
														'change': function(field, checked) {
															win.findById('swENSEW_Scream_Panel').change_handler(field, checked);
														}
													}
												})	
											]
										}
									]
								},
								{									
									layout:'column',
									items:[
										{
											allowBlank: true,
											fieldLabel: '',
											labelSeparator: '',
											name: 'swENSEW_ScreamTxt',
											id: 'swENSEW_ScreamTxt',
											width: 1150,
											value:'',
											style: 'margin-top: 4px;  margin-left: 4px; margin-bottom: 4px; ',
											xtype: 'textfield',
											listeners:{
												'change':function (field, newValue, oldValue) {
													this.NeonatalSurvey_data.NeonatalSurveyParam.ScreamTxt = newValue;
													this.changedDatas = true;
												}.createDelegate(this)
											}
										}
									]
								}
							],
							change_handler: function(field, checked) {
								if((checked) && field.FromInterface){
									var Scream = (this.NeonatalSurvey_data.NeonatalSurveyParam.Scream) ? this.NeonatalSurvey_data.NeonatalSurveyParam.Scream : '00';
									var position = parseInt(field.id.substr(field.id.length - 1, 1));
									var value = checked.inputValue;
									//если меняется выбранный в первой группе то обнуляются вторая  //BOB - 06.05.2020
									if (position == 1) {
										this.findById('swENSEW_Scream2').items.items[0].setValue(true);
										for (var i = 1; i < this.findById('swENSEW_Scream2').items.items.length; i++  ) this.findById('swENSEW_Scream2').items.items[i].setValue(false);
										this.findById('swENSEW_Scream2').disable();
										if(value == 1) this.findById('swENSEW_Scream2').enable();
									}
									Scream = Scream.split("");
									Scream[position - 1] = value;
									Scream = Scream.join("");
									if (!Ext.isEmpty(this.NeonatalSurvey_data.NeonatalSurveyParam.Scream) || (Scream != '00')) {
										this.NeonatalSurvey_data.NeonatalSurveyParam.Scream = Scream;									
										this.changedDatas = true;
									}
								}
								field.FromInterface = true;
							}.createDelegate(this)
						},
						//Панель Голова
						{
							id: 'swENSEW_head_Panel',
							xtype: 'fieldset',
							autoHeight: true,
							title: langs('Голова'),
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
								{									
									layout:'column',
									items:[
										//Макроцефалия
										{
											layout:'form',
											border: false,
											labelWidth: 110,
											items:[
												{
													fieldLabel: 'Макроцефалия',
													labelSeparator: '',
													name: 'macrocephaly',
													id: 'swENSEW_macrocephaly',
													xtype: 'checkbox',
													checked: false,
													listeners: {
														'check': function(chb, checked ) {
															if ((this.NeonatalSurvey_data.NeonatalSurveyParam.macrocephaly) || (checked)) {
																this.NeonatalSurvey_data.NeonatalSurveyParam.macrocephaly = checked ? 2 : 1;									
																this.changedDatas = true;
															}
														}.createDelegate(this)
													}		
												}
											]
										},
										//Микроцефалия
										{
											layout:'form',
											border: false,
											labelWidth: 110,
											items:[
												{
													fieldLabel: 'Микроцефалия',
													labelSeparator: '',
													name: 'microcephaly',
													id: 'swENSEW_microcephaly',
													xtype: 'checkbox',
													checked: false,
													listeners: {
														'check': function(chb, checked ) {
															if ((this.NeonatalSurvey_data.NeonatalSurveyParam.microcephaly) || (checked)) { 
																this.NeonatalSurvey_data.NeonatalSurveyParam.microcephaly = checked ? 2 : 1;									
																this.changedDatas = true;
															}
														}.createDelegate(this)
													}		
												}
											]
										},
										//Молдинг
										{
											layout:'form',
											border: false,
											labelWidth: 70,
											items:[
												{
													fieldLabel: 'Молдинг',
													labelSeparator: '',
													name: 'molding',
													id: 'swENSEW_molding',
													xtype: 'checkbox',
													checked: false,
													listeners: {
														'check': function(chb, checked ) {
															if ((this.NeonatalSurvey_data.NeonatalSurveyParam.molding) || (checked)) { 
																this.NeonatalSurvey_data.NeonatalSurveyParam.molding = checked ? 2 : 1;									
																this.changedDatas = true;
															}
														}.createDelegate(this)
													}		
												}
											]
										}		
									]
								},
								//Родовая опухоль
								{									
									layout:'column',
									items:[
										{
											layout:'form',
											border: false,
											labelWidth: 200,
											items:[
												{
													fieldLabel: 'Родовая опухоль',
													labelSeparator: '',
													name: 'BirthTumor',
													id: 'swENSEW_BirthTumor',
													xtype: 'checkbox',
													checked: false,
													listeners: {
														'check': function(chb, checked ) {
															if ((this.NeonatalSurvey_data.NeonatalSurveyParam.BirthTumor) || (checked)) { 
																this.NeonatalSurvey_data.NeonatalSurveyParam.BirthTumor = checked ? 2 : 1;									
																this.changedDatas = true;
																this.findById('swENSEW_BirthTumorTxt').setDisabled(!checked);  //BOB - 06.05.2020
																if (!checked) {
																	this.findById('swENSEW_BirthTumorTxt').setValue('');
																	delete this.NeonatalSurvey_data.NeonatalSurveyParam.BirthTumorTxt;
																}
															}
														}.createDelegate(this)
													}		
												}
											]
										},
										{
											allowBlank: true,
											fieldLabel: '',
											labelSeparator: '',
											name: 'swENSEW_BirthTumorTxt',
											id: 'swENSEW_BirthTumorTxt',
											width: 1070,
											value:'',
											style: 'margin-left: 4px;',
											xtype: 'textfield',
											listeners:{
												'change':function (field, newValue, oldValue) {
													this.NeonatalSurvey_data.NeonatalSurveyParam.BirthTumorTxt = newValue;
													this.changedDatas = true;
												}.createDelegate(this)
											}
										}
									]
								},
								//Кефалогематома
								{									
									layout:'column',
									items:[
										{
											layout:'form',
											border: false,
											labelWidth: 200,
											items:[
												{
													fieldLabel: 'Кефалогематома',
													labelSeparator: '',
													name: 'СephaloHematoma',
													id: 'swENSEW_СephaloHematoma',
													xtype: 'checkbox',
													checked: false,
													listeners: {
														'check': function(chb, checked ) {
															if ((this.NeonatalSurvey_data.NeonatalSurveyParam.СephaloHematoma) || (checked)) { 
																this.NeonatalSurvey_data.NeonatalSurveyParam.СephaloHematoma = checked ? 2 : 1;									
																this.changedDatas = true;
																this.findById('swENSEW_СephaloHematomaTxt').setDisabled(!checked);  //BOB - 06.05.2020
																if (!checked) {
																	this.findById('swENSEW_СephaloHematomaTxt').setValue('');
																	delete this.NeonatalSurvey_data.NeonatalSurveyParam.СephaloHematomaTxt;
																}
															}
														}.createDelegate(this)
													}		
												}
											]
										},
										{
											allowBlank: true,
											fieldLabel: '',
											labelSeparator: '',
											name: 'swENSEW_СephaloHematomaTxt',
											id: 'swENSEW_СephaloHematomaTxt',
											width: 1070,
											value:'',
											style: 'margin-left: 4px;',
											xtype: 'textfield',
											listeners:{
												'change':function (field, newValue, oldValue) {
													this.NeonatalSurvey_data.NeonatalSurveyParam.СephaloHematomaTxt = newValue;
													this.changedDatas = true;
												}.createDelegate(this)
											}
										}
									]
								},
								//Подапоневротическая гематома
								{									
									layout:'column',
									items:[
										{
											layout:'form',
											border: false,
											labelWidth: 200,
											items:[
												{
													fieldLabel: 'Подапоневротическая гематома',
													labelSeparator: '',
													name: 'SubaponeuroHematoma',
													id: 'swENSEW_SubaponeuroHematoma',
													xtype: 'checkbox',
													checked: false,
													listeners: {
														'check': function(chb, checked ) {
															if ((this.NeonatalSurvey_data.NeonatalSurveyParam.SubaponeuroHematoma) || (checked)) { 
																this.NeonatalSurvey_data.NeonatalSurveyParam.SubaponeuroHematoma = checked ? 2 : 1;									
																this.changedDatas = true;
																this.findById('swENSEW_SubaponeuroHematomaTxt').setDisabled(!checked);  //BOB - 06.05.2020
																if (!checked) {
																	this.findById('swENSEW_SubaponeuroHematomaTxt').setValue('');
																	delete this.NeonatalSurvey_data.NeonatalSurveyParam.SubaponeuroHematomaTxt;
																}
															}
														}.createDelegate(this)
													}		
												}
											]
										},
										{
											allowBlank: true,
											fieldLabel: '',
											labelSeparator: '',
											name: 'swENSEW_SubaponeuroHematomaTxt',
											id: 'swENSEW_SubaponeuroHematomaTxt',
											width: 1070,
											value:'',
											style: 'margin-left: 4px;',
											xtype: 'textfield',
											listeners:{
												'change':function (field, newValue, oldValue) {
													this.NeonatalSurvey_data.NeonatalSurveyParam.SubaponeuroHematomaTxt = newValue;
													this.changedDatas = true;
												}.createDelegate(this)
											}
										}
									]
								},
								{									
									layout:'column',
									items:[
										//Выступающие вены на черепе
										{
											layout:'form',
											border: false,
											labelWidth: 200,
											items:[
												{
													fieldLabel: 'Выступающие вены на черепе',
													labelSeparator: '',
													name: 'ProtrudingVeins',
													id: 'swENSEW_ProtrudingVeins',
													xtype: 'checkbox',
													checked: false,
													listeners: {
														'check': function(chb, checked ) {
															if ((this.NeonatalSurvey_data.NeonatalSurveyParam.ProtrudingVeins) || (checked)) { 
																this.NeonatalSurvey_data.NeonatalSurveyParam.ProtrudingVeins = checked ? 2 : 1;									
																this.changedDatas = true;
															}
														}.createDelegate(this)
													}		
												}
											]
										},
										//Краниосиностоз
										{
											layout:'form',
											border: false,
											labelWidth: 110,
											items:[
												{
													fieldLabel: 'Краниосиностоз',
													labelSeparator: '',
													name: 'craniosynostosis',
													id: 'swENSEW_craniosynostosis',
													xtype: 'checkbox',
													checked: false,
													listeners: {
														'check': function(chb, checked ) {
															if ((this.NeonatalSurvey_data.NeonatalSurveyParam.craniosynostosis) || (checked)) { 
																this.NeonatalSurvey_data.NeonatalSurveyParam.craniosynostosis = checked ? 2 : 1;									
																this.changedDatas = true;
															}
														}.createDelegate(this)
													}		
												}
											]
										},
										//Краниотабес
										{
											layout:'form',
											border: false,
											labelWidth: 100,
											items:[
												{
													fieldLabel: 'Краниотабес',
													labelSeparator: '',
													name: 'craniotabes',
													id: 'swENSEW_craniotabes',
													xtype: 'checkbox',
													checked: false,
													listeners: {
														'check': function(chb, checked ) {
															if ((this.NeonatalSurvey_data.NeonatalSurveyParam.craniotabes) || (checked)) { 
																this.NeonatalSurvey_data.NeonatalSurveyParam.craniotabes = checked ? 2 : 1;									
																this.changedDatas = true;
															}
														}.createDelegate(this)
													}		
												}
											]
										}		
									]
								},
								{									
									layout:'column',
									items:[
										//Плагиоцефалия
										{
											layout:'form',
											border: false,
											labelWidth: 110,
											items:[
												{
													fieldLabel: 'Плагиоцефалия ',
													labelSeparator: '',
													name: 'plagiocephaly',
													id: 'swENSEW_plagiocephaly',
													xtype: 'checkbox',
													checked: false,
													listeners: {
														'check': function(chb, checked ) {
															if ((this.NeonatalSurvey_data.NeonatalSurveyParam.plagiocephaly) || (checked)) { 
																this.NeonatalSurvey_data.NeonatalSurveyParam.plagiocephaly = checked ? 2 : 1;									
																this.changedDatas = true;
															}
														}.createDelegate(this)
													}		
												}
											]
										},
										//Брахицефалия
										{
											layout:'form',
											border: false,
											labelWidth: 110,
											items:[
												{
													fieldLabel: 'Брахицефалия',
													labelSeparator: '',
													name: 'brachycephaly',
													id: 'swENSEW_brachycephaly',
													xtype: 'checkbox',
													checked: false,
													listeners: {
														'check': function(chb, checked ) {
															if ((this.NeonatalSurvey_data.NeonatalSurveyParam.brachycephaly) || (checked)) { 
																this.NeonatalSurvey_data.NeonatalSurveyParam.brachycephaly = checked ? 2 : 1;									
																this.changedDatas = true;
															}
														}.createDelegate(this)
													}		
												}
											]
										},
										//Долихоцефалия
										{
											layout:'form',
											border: false,
											labelWidth: 110,
											items:[
												{
													fieldLabel: 'Долихоцефалия',
													labelSeparator: '',
													name: 'dolichocephaly',
													id: 'swENSEW_dolichocephaly',
													xtype: 'checkbox',
													checked: false,
													listeners: {
														'check': function(chb, checked ) {
															if ((this.NeonatalSurvey_data.NeonatalSurveyParam.dolichocephaly) || (checked)) { 
																this.NeonatalSurvey_data.NeonatalSurveyParam.dolichocephaly = checked ? 2 : 1;									
																this.changedDatas = true;
															}
														}.createDelegate(this)
													}		
												}
											]
										},
										//Акроцефалия
										{
											layout:'form',
											border: false,
											labelWidth: 100,
											items:[
												{
													fieldLabel: 'Акроцефалия',
													labelSeparator: '',
													name: 'acrocephaly',
													id: 'swENSEW_acrocephaly',
													xtype: 'checkbox',
													checked: false,
													listeners: {
														'check': function(chb, checked ) {
															if ((this.NeonatalSurvey_data.NeonatalSurveyParam.acrocephaly) || (checked)) { 
																this.NeonatalSurvey_data.NeonatalSurveyParam.acrocephaly = checked ? 2 : 1;									
																this.changedDatas = true;
															}
														}.createDelegate(this)
													}		
												}
											]
										}		
									]
								}							
							
							]
						},
						//Панель Большой родничок   
						{
							id: 'swENSEW_Fontanelle_Panel',
							xtype: 'fieldset',
							autoHeight: true,
							title: langs('Большой родничок'),
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
								//Размер
								{									
									layout:'column',
									items:[
										{xtype:'label',text: 'Размер',style: 'font-size: 10pt;  margin-left: 12pt; margin-top: 2pt; margin-bottom: 2px'},											
										new Ext.form.NumberField({
											value: 0,
											id: 'swENSEW_FontanelleSize1',
											fieldLabel:'',
											labelSeparator: '',
											enableKeyEvents: true,
											width: 60,
											style: 'margin-left: 2pt; margin-top: 2pt',
											//plugins:[ new Ext.ux.InputTextMask('99.9', true) ],
											listeners:{
												'keyup':function (obj, e) {
													if (!Ext.isEmpty(obj.getValue()))
														this.NeonatalSurvey_data.NeonatalSurveyParam.FontanelleSize1 = obj.getValue();													
													else delete this.NeonatalSurvey_data.NeonatalSurveyParam.FontanelleSize1;												
													this.changedDatas = true;
												}.createDelegate(this)
											}
										}),
										{xtype:'label',text: 'на',style: 'font-size: 10pt;  margin-left: 2pt; margin-top: 2pt'},											
										new Ext.form.NumberField({
											value: 0,
											id: 'swENSEW_FontanelleSize2',
											fieldLabel:'',
											labelSeparator: '',
											enableKeyEvents: true,
											width: 60,
											style: 'margin-left: 2pt; margin-top: 2pt',
											//plugins:[ new Ext.ux.InputTextMask('99.9', true) ],
											listeners:{
												'keyup':function (obj, e) {
													if (!Ext.isEmpty(obj.getValue()))
													this.NeonatalSurvey_data.NeonatalSurveyParam.FontanelleSize2 = obj.getValue();													
													else delete this.NeonatalSurvey_data.NeonatalSurveyParam.FontanelleSize2;												
													this.changedDatas = true;
												}.createDelegate(this)
											}
										}),
										{xtype:'label',text: 'см',style: 'font-size: 10pt;  margin-left: 2pt; margin-top: 2pt'}

									]
								},
								//Уровень,Напряжение,Пульсация
								{									
									layout:'column',
									items:[
										{xtype:'label',text: 'Уровень',style: 'font-size: 10pt;  margin-left: 2pt; margin-top: 2pt; margin-bottom: 2px'},
										{									
											layout:'form',
											labelWidth:1,
											style: 'margin-top: 4px; margin-bottom: 2px;  margin-left: 4px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											items:[
												new Ext.form.RadioGroup({
													id:'swENSEW_FontanelleProperties1',
													labelSeparator: '',
													vertical: true,
													columns: 2,
													FromInterface: true,
													items: [
														{boxLabel: '---', name: 'FontanelleProperties1', inputValue: 0, width: 120}, 
														{boxLabel: 'на уровне костей свода черепа', name: 'FontanelleProperties1', inputValue: 1, width: 250}, 
														{boxLabel: 'выбухает', name: 'FontanelleProperties1', inputValue: 2, width: 100},
														{boxLabel: 'запавший', name: 'FontanelleProperties1', inputValue: 3, width: 100}
													],
													listeners: {
														'change': function(field, checked) {
															win.findById('swENSEW_Fontanelle_Panel').change_handler(field, checked);
														}
													}
												})	
											]
										},
										{xtype:'label',text: 'Напряжение',style: 'font-size: 10pt;  margin-left: 2pt; margin-top: 2pt'},
										{									
											layout:'form',
											labelWidth:1,
											style: 'margin-top: 4px;  margin-left: 4px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											items:[
												new Ext.form.RadioGroup({
													id:'swENSEW_FontanelleProperties2',
													labelSeparator: '',
													vertical: true,
													columns: 3,
													FromInterface: true,
													items: [
														{boxLabel: '---', name: 'FontanelleProperties2', inputValue: 0, width: 70}, 
														{boxLabel: 'напряжён', name: 'FontanelleProperties2', inputValue: 1, width: 120}, 
														{boxLabel: 'не напряжён', name: 'FontanelleProperties2', inputValue: 2, width: 120}
													],
													listeners: {
														'change': function(field, checked) {
															win.findById('swENSEW_Fontanelle_Panel').change_handler(field, checked);
														}
													}
												})	
											]
										},
										{xtype:'label',text: 'Пульсация',style: 'font-size: 10pt;  margin-left: 2pt; margin-top: 2pt'},
										{									
											layout:'form',
											labelWidth:1,
											style: 'margin-top: 4px;  margin-left: 4px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											items:[
												new Ext.form.RadioGroup({
													id:'swENSEW_FontanelleProperties3',
													labelSeparator: '',
													vertical: true,
													columns: 3,
													FromInterface: true,
													items: [
														{boxLabel: '---', name: 'FontanelleProperties3', inputValue: 0, width: 70}, 
														{boxLabel: 'пульсирует', name: 'FontanelleProperties3', inputValue: 1, width: 120}, 
														{boxLabel: 'не пульсирует', name: 'FontanelleProperties3', inputValue: 2, width: 120}
													],
													listeners: {
														'change': function(field, checked) {
															win.findById('swENSEW_Fontanelle_Panel').change_handler(field, checked);
														}
													}
												})	
											]
										}										
									]
								}

							],
							change_handler: function(field, checked) {
								if((checked) && field.FromInterface){
									var FontanelleProperties = (this.NeonatalSurvey_data.NeonatalSurveyParam.FontanelleProperties) ? this.NeonatalSurvey_data.NeonatalSurveyParam.FontanelleProperties : '000';
									var position = parseInt(field.id.substr(field.id.length - 1, 1));
									var value = checked.inputValue;
									FontanelleProperties = FontanelleProperties.split("");
									FontanelleProperties[position - 1] = value;
									FontanelleProperties = FontanelleProperties.join("");
									if (!Ext.isEmpty(this.NeonatalSurvey_data.NeonatalSurveyParam.FontanelleProperties) || (FontanelleProperties != '000')) 
										this.NeonatalSurvey_data.NeonatalSurveyParam.FontanelleProperties = FontanelleProperties;									
									this.changedDatas = true;
								}
								field.FromInterface = true;
							}.createDelegate(this)
						},
						//Панель Швы черепа
						{
							id: 'swENSEW_SkullSutures_Panel',
							xtype: 'fieldset',
							autoHeight: true,
							title: langs('Швы черепа'),
							collapsible: true,
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
									labelWidth:1,
									style: 'margin-top: 4px;  margin-left: 4px; margin-bottom: 2px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
									items:[
										new Ext.form.RadioGroup({
											id:'swENSEW_SkullSutures',
											labelSeparator: '',
											vertical: true,
											columns: 4,
											FromInterface: true,
											items: [
												{boxLabel: '---', name: 'SkullSutures', inputValue: 0, width: 50}, 
												{boxLabel: 'на стыке', name: 'SkullSutures', inputValue: 1, width: 130}, 
												{boxLabel: 'расхождение', name: 'SkullSutures', inputValue: 2, width: 130},
												{boxLabel: 'захождение', name: 'SkullSutures', inputValue: 3, width: 130}
											],
											listeners: {
												'change': function(field, checked) {
													if((checked) && field.FromInterface){
														if ((this.NeonatalSurvey_data.NeonatalSurveyParam.SkullSutures) || (String(checked.inputValue) != '0')) {
															this.NeonatalSurvey_data.NeonatalSurveyParam.SkullSutures = checked.inputValue;									
															this.changedDatas = true;
														}
													}
													field.FromInterface = true;
												}.createDelegate(this)
											}
										})	
									]
								},
								{xtype:'label',text: 'Размер расхождения',style: 'font-size: 10pt;  margin-left: 12pt; margin-top: 2pt'},											
								new Ext.form.NumberField({
									value: 0,
									id: 'swENSEW_SkullSuturesSize',
									fieldLabel:'',
									labelSeparator: '',
									enableKeyEvents: true,
									width: 60,
									style: 'margin-left: 2pt; margin-top: 2pt',
									//plugins:[ new Ext.ux.InputTextMask('99.9', true) ],
									listeners:{
										'keyup':function (obj, e) {
											if (!Ext.isEmpty(obj.getValue()))
												this.NeonatalSurvey_data.NeonatalSurveyParam.SkullSuturesSize = obj.getValue();													
											else delete this.NeonatalSurvey_data.NeonatalSurveyParam.SkullSuturesSize;												
											this.changedDatas = true;
										}.createDelegate(this)
									}
								}),
								{xtype:'label',text: 'см',style: 'font-size: 10pt;  margin-left: 2pt; margin-top: 2pt'},											
							]
						},
						//Панель Зрачки
						{
							id: 'swENSEW_Pupils_Panel',
							xtype: 'fieldset',
							autoHeight: true,
							title: langs('Зрачки'),
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
								//Размер 
								{									
									layout:'column',
									items:[
										{									
											layout:'form',
											labelWidth:1,
											style: 'margin-top: 4px;  margin-left: 4px; margin-bottom: 2px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											items:[
												new Ext.form.RadioGroup({
													id:'swENSEW_Pupils1',
													labelSeparator: '',
													vertical: true,
													columns: 4,
													FromInterface: true,
													items: [
														{boxLabel: '---', name: 'Pupils1', inputValue: 0, width: 50}, 
														{boxLabel: 'узкие', name: 'Pupils1', inputValue: 1, width: 130}, 
														{boxLabel: 'широкие', name: 'Pupils1', inputValue: 2, width: 130},
														{boxLabel: 'иное', name: 'Pupils1', inputValue: 3, width: 80}
													],
													listeners: {
														'change': function(field, checked) {
															win.findById('swENSEW_Pupils_Panel').change_Pupils(field, checked);
														}
													}
												})	
											]
										},
										{
											allowBlank: true,
											fieldLabel: '',
											labelSeparator: '',
											name: 'swENSEW_PupilsTxt',
											id: 'swENSEW_PupilsTxt',
											width: 850,
											value:'',
											style: 'margin-top: 4px;  margin-left: 4px; ',
											xtype: 'textfield',
											listeners:{
												'change':function (field, newValue, oldValue) {
													this.NeonatalSurvey_data.NeonatalSurveyParam.PupilsTxt = newValue;
													this.changedDatas = true;
												}.createDelegate(this)
											}
										}
									]
								},
								// Симметричность, Фотореакция
								{									
									layout:'column',
									items:[
										{xtype:'label',text: 'Симметричность',style: 'font-size: 10pt;  margin-left: 2pt; margin-top: 2pt; margin-bottom: 2px'},
										{									
											layout:'form',
											labelWidth:1,
											style: 'margin-top: 4px;  margin-left: 4px; margin-bottom: 2px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											items:[
												new Ext.form.RadioGroup({
													id:'swENSEW_Pupils2',
													labelSeparator: '',
													vertical: true,
													columns: 4,
													FromInterface: true,
													items: [
														{boxLabel: '---', name: 'Pupils2', inputValue: 0, width: 50}, 
														{boxLabel: 'равные D = S', name: 'Pupils2', inputValue: 1, width: 130}, 
														{boxLabel: 'анизокория D > S', name: 'Pupils2', inputValue: 2, width: 130},
														{boxLabel: 'анизокория S > D', name: 'Pupils2', inputValue: 3, width: 130}
													],
													listeners: {
														'change': function(field, checked) {
															win.findById('swENSEW_Pupils_Panel').change_Pupils(field, checked);
														}
													}
												})	
											]
										},
										{xtype:'label',text: 'Фотореакция',style: 'font-size: 10pt;  margin-left: 2pt; margin-top: 2pt; margin-bottom: 2px'},
										{									
											layout:'form',
											labelWidth:1,
											style: 'margin-top: 4px;  margin-left: 4px; margin-bottom: 2px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											items:[
												new Ext.form.RadioGroup({
													id:'swENSEW_Pupils3',
													labelSeparator: '',
													vertical: true,
													columns: 3,
													FromInterface: true,
													items: [
														{boxLabel: '---', name: 'Pupils3', inputValue: 0, width: 50}, 
														{boxLabel: 'сохранена', name: 'Pupils3', inputValue: 1, width: 130}, 
														{boxLabel: 'отсутствует', name: 'Pupils3', inputValue: 2, width: 130}
													],
													listeners: {
														'change': function(field, checked) {
															win.findById('swENSEW_Pupils_Panel').change_Pupils(field, checked);
														}
													}
												})	
											]
										}
									]
								},
								// Нистагм
								{									
									layout:'column',
									items:[
										{xtype:'label',text: 'Нистагм',style: 'font-size: 10pt;  margin-left: 2pt; margin-top: 2pt; margin-bottom: 2px'},
										{									
											layout:'form',
											labelWidth:1,
											style: 'margin-top: 4px;  margin-left: 4px; margin-bottom: 2px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											items:[
												new Ext.form.RadioGroup({
													id:'swENSEW_Nystagmus1',
													labelSeparator: '',
													vertical: true,
													columns: 3,
													FromInterface: true,
													items: [
														{boxLabel: 'нет', name: 'Nystagmus1', inputValue: 0, width: 50}, 
														{boxLabel: 'стойкий', name: 'Nystagmus1', inputValue: 1, width: 130}, 
														{boxLabel: 'нестойкий', name: 'Nystagmus1', inputValue: 2, width: 130}
													],
													listeners: {
														'change': function(field, checked) {
															win.findById('swENSEW_Pupils_Panel').change_Nystagmus(field, checked);
														}
													}
												})	
											]
										},
										{									
											layout:'form',
											labelWidth:1,
											style: 'margin-top: 4px;  margin-left: 4px; margin-bottom: 2px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											items:[
												new Ext.form.RadioGroup({
													id:'swENSEW_Nystagmus2',
													labelSeparator: '',
													vertical: true,
													columns: 4,
													FromInterface: true,
													items: [
														{boxLabel: '---', name: 'Nystagmus2', inputValue: 0, width: 50, hidden: true}, 
														{boxLabel: 'горизонтальный', name: 'Nystagmus2', inputValue: 1, width: 130}, 
														{boxLabel: 'вертикальный', name: 'Nystagmus2', inputValue: 2, width: 130},
														{boxLabel: 'смешанный', name: 'Nystagmus2', inputValue: 3, width: 130}
													],
													listeners: {
														'change': function(field, checked) {
															win.findById('swENSEW_Pupils_Panel').change_Nystagmus(field, checked);
														}
													}
												})	
											]
										}
									]
								}
							],
							change_Pupils: function(field, checked) {
								if((checked) && field.FromInterface){
									var Pupils = (this.NeonatalSurvey_data.NeonatalSurveyParam.Pupils) ? this.NeonatalSurvey_data.NeonatalSurveyParam.Pupils : '000';
									var position = parseInt(field.id.substr(field.id.length - 1, 1));
									var value = checked.inputValue;
									Pupils = Pupils.split("");
									Pupils[position - 1] = value;
									Pupils = Pupils.join("");
									if (!Ext.isEmpty(this.NeonatalSurvey_data.NeonatalSurveyParam.Pupils) || (Pupils != '000')) 
										this.NeonatalSurvey_data.NeonatalSurveyParam.Pupils = Pupils;									
									this.changedDatas = true;
								}
								field.FromInterface = true;
							}.createDelegate(this),
							change_Nystagmus: function(field, checked) {
								if((checked) && field.FromInterface){
									var Nystagmus = (this.NeonatalSurvey_data.NeonatalSurveyParam.Nystagmus) ? this.NeonatalSurvey_data.NeonatalSurveyParam.Nystagmus : '00';
									var position = parseInt(field.id.substr(field.id.length - 1, 1));
									var value = checked.inputValue;
									//если меняется выбранный в первой группе то обнуляются вторая
									if (position == 1) {
										this.findById('swENSEW_Nystagmus2').items.items[0].setValue(true);		
										for (var i = 1; i < this.findById('swENSEW_Nystagmus2').items.items.length; i++  ) this.findById('swENSEW_Nystagmus2').items.items[i].setValue(false);						
										this.findById('swENSEW_Nystagmus2').disable();						
										if(value  > 0) this.findById('swENSEW_Nystagmus2').enable();	
									}
									Nystagmus = Nystagmus.split("");
									Nystagmus[position - 1] = value;
									Nystagmus = Nystagmus.join("");
									if (!Ext.isEmpty(this.NeonatalSurvey_data.NeonatalSurveyParam.Nystagmus) || (Nystagmus != '00')) 
										this.NeonatalSurvey_data.NeonatalSurveyParam.Nystagmus = Nystagmus;									
									this.changedDatas = true;
								}
								field.FromInterface = true;
							}.createDelegate(this)
						},
						//Панель Мышечный тонус
						{
							id: 'swENSEW_MuscleTone_Panel',
							xtype: 'fieldset',
							autoHeight: true,
							title: langs('Мышечный тонус'),
							collapsible: true,
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
									labelWidth:1,
									style: 'margin-top: 4px;  margin-left: 4px; margin-bottom: 2px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
									items:[
										new Ext.form.RadioGroup({
											id:'swENSEW_MuscleTone',
											labelSeparator: '',
											vertical: true,
											columns: 6,
											FromInterface: true,
											items: [
												{boxLabel: '---', name: 'MuscleTone', inputValue: 0, width: 50}, 
												{boxLabel: 'атония', name: 'MuscleTone', inputValue: 1, width: 80}, 
												{boxLabel: 'гипотонус', name: 'MuscleTone', inputValue: 2, width: 90},
												{boxLabel: 'гипертонус', name: 'MuscleTone', inputValue: 3, width: 100},
												{boxLabel: 'нормотонус', name: 'MuscleTone', inputValue: 4, width: 100},
												{boxLabel: 'иное', name: 'MuscleTone', inputValue: 5, width: 60}
											],
											listeners: {
												'change': function(field, checked) {
													if((checked) && field.FromInterface){
														if ((this.NeonatalSurvey_data.NeonatalSurveyParam.MuscleTone) || (String(checked.inputValue) != '0')) {
															this.NeonatalSurvey_data.NeonatalSurveyParam.MuscleTone = checked.inputValue;									
															this.changedDatas = true;
														}
													}
													field.FromInterface = true;
												}.createDelegate(this)
											}
										})	
									]
								},
								{
									allowBlank: true,
									fieldLabel: '',
									labelSeparator: '',
									name: 'swENSEW_MuscleToneTxt',
									id: 'swENSEW_MuscleToneTxt',
									width: 750,
									value:'',
									style: 'margin-top: 4px;  margin-left: 4px; ',
									xtype: 'textfield',
									listeners:{
										'change':function (field, newValue, oldValue) {
											this.NeonatalSurvey_data.NeonatalSurveyParam.MuscleToneTxt = newValue;
											this.changedDatas = true;
										}.createDelegate(this)
									}
								}
							]
						},
						//Панель Рефлексы
						{
							id: 'swENSEW_Reflexes_Panel',
							xtype: 'fieldset',
							autoHeight: true,
							title: langs('Рефлексы'),
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
								// Новорожденных, Поисковый, Глабеллярный, Хватательный
								{									
									layout:'column',
									items:[
										//Новорожденных
										{xtype:'label',text: 'Новорожденных',style: 'font-size: 10pt;  margin-left: 2pt; margin-top: 2pt; margin-bottom: 2px'},
										{									
											layout:'form',
											labelWidth:1,
											FromInterface: true,
											style: 'margin-top: 4px;  margin-left: 4px; margin-bottom: 2px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											items:[
												new Ext.form.RadioGroup({
													id:'swENSEW_Reflexes1',
													labelSeparator: '',
													vertical: true,
													columns: 1,
													items: [
														{boxLabel: '---', name: 'Reflexes1', inputValue: 0, width: 50}, 
														{boxLabel: 'вызываются', name: 'Reflexes1', inputValue: 1, width: 130}, 
														{boxLabel: 'усилены', name: 'Reflexes1', inputValue: 2, width: 130},
														{boxLabel: 'угнетены', name: 'Reflexes1', inputValue: 3, width: 130},
														{boxLabel: 'арефлексия', name: 'Reflexes1', inputValue: 4, width: 130}
													],
													listeners: {
														'change': function(field, checked) {
															win.findById('swENSEW_Reflexes_Panel').change_handler(field, checked);
														}
													}
												})	
											]
										},
										//Поисковый
										{xtype:'label',text: 'Поисковый',style: 'font-size: 10pt;  margin-left: 12pt; margin-top: 2pt; margin-bottom: 2px'},
										{									
											layout:'form',
											labelWidth:1,
											FromInterface: true,
											style: 'margin-top: 4px;  margin-left: 4px; margin-bottom: 2px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											items:[
												new Ext.form.RadioGroup({
													id:'swENSEW_Reflexes2',
													labelSeparator: '',
													vertical: true,
													columns: 1,
													items: [
														{boxLabel: '---', name: 'Reflexes2', inputValue: 0, width: 50}, 
														{boxLabel: 'вызывается', name: 'Reflexes2', inputValue: 1, width: 130}, 
														{boxLabel: 'угнетен', name: 'Reflexes2', inputValue: 2, width: 130},
														{boxLabel: 'усилен', name: 'Reflexes2', inputValue: 3, width: 130}
													],
													listeners: {
														'change': function(field, checked) {
															win.findById('swENSEW_Reflexes_Panel').change_handler(field, checked);
														}
													}
												})	
											]
										},
										//Глабеллярный 
										{xtype:'label',text: 'Глабеллярный',style: 'font-size: 10pt;  margin-left: 12pt; margin-top: 2pt; margin-bottom: 2px'},
										{									
											layout:'form',
											labelWidth:1,
											FromInterface: true,
											style: 'margin-top: 4px;  margin-left: 4px; margin-bottom: 2px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											items:[
												new Ext.form.RadioGroup({
													id:'swENSEW_Reflexes3',
													labelSeparator: '',
													vertical: true,
													columns: 1,
													items: [
														{boxLabel: '---', name: 'Reflexes3', inputValue: 0, width: 50}, 
														{boxLabel: 'вызывается', name: 'Reflexes3', inputValue: 1, width: 130}, 
														{boxLabel: 'угнетен', name: 'Reflexes3', inputValue: 2, width: 130},
														{boxLabel: 'усилен', name: 'Reflexes3', inputValue: 3, width: 130}
													],
													listeners: {
														'change': function(field, checked) {
															win.findById('swENSEW_Reflexes_Panel').change_handler(field, checked);
														}
													}
												})	
											]
										},
										//Хватательный 
										{xtype:'label',text: 'Хватательный',style: 'font-size: 10pt;  margin-left: 12pt; margin-top: 2pt; margin-bottom: 2px'},
										{									
											layout:'form',
											labelWidth:1,
											style: 'margin-top: 4px;  margin-left: 4px; margin-bottom: 2px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											items:[
												new Ext.form.RadioGroup({
													id:'swENSEW_Reflexes4',
													labelSeparator: '',
													vertical: true,
													columns: 1,
													FromInterface: true,
													items: [
														{boxLabel: '---', name: 'Reflexes4', inputValue: 0, width: 50}, 
														{boxLabel: 'вызывается', name: 'Reflexes4', inputValue: 1, width: 130}, 
														{boxLabel: 'угнетен', name: 'Reflexes4', inputValue: 2, width: 130},
														{boxLabel: 'усилен', name: 'Reflexes4', inputValue: 3, width: 130}
													],
													listeners: {
														'change': function(field, checked) {
															win.findById('swENSEW_Reflexes_Panel').change_handler(field, checked);
														}
													}
												})	
											]
										}
									]
								},
								//Выпрямления шеи, Моро, Хватание подошвой, Опоры
								{									
									layout:'column',
									items:[
										//Выпрямления шеи 
										{xtype:'label',text: 'Выпрямления шеи',style: 'font-size: 10pt;  margin-left: 2pt; margin-top: 2pt; margin-bottom: 2px'},
										{									
											layout:'form',
											labelWidth:1,
											FromInterface: true,
											style: 'margin-top: 4px;  margin-left: 4px; margin-bottom: 2px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											items:[
												new Ext.form.RadioGroup({
													id:'swENSEW_Reflexes5',
													labelSeparator: '',
													vertical: true,
													columns: 1,
													items: [
														{boxLabel: '---', name: 'Reflexes5', inputValue: 0, width: 50}, 
														{boxLabel: 'вызывается', name: 'Reflexes5', inputValue: 1, width: 130}, 
														{boxLabel: 'угнетен', name: 'Reflexes5', inputValue: 2, width: 130},
														{boxLabel: 'усилен', name: 'Reflexes5', inputValue: 3, width: 130}
													],
													listeners: {
														'change': function(field, checked) {
															win.findById('swENSEW_Reflexes_Panel').change_handler(field, checked);
														}
													}
												})	
											]
										},
										//Моро  
										{xtype:'label',text: 'Моро',style: 'font-size: 10pt;  margin-left: 12pt; margin-top: 2pt; margin-bottom: 2px'},
										{									
											layout:'form',
											labelWidth:1,
											FromInterface: true,
											style: 'margin-top: 4px;  margin-left: 4px; margin-bottom: 2px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											items:[
												new Ext.form.RadioGroup({
													id:'swENSEW_Reflexes6',
													labelSeparator: '',
													vertical: true,
													columns: 1,
													items: [
														{boxLabel: '---', name: 'Reflexes6', inputValue: 0, width: 50}, 
														{boxLabel: 'вызывается', name: 'Reflexes6', inputValue: 1, width: 130}, 
														{boxLabel: 'угнетен', name: 'Reflexes6', inputValue: 2, width: 130},
														{boxLabel: 'усилен', name: 'Reflexes6', inputValue: 3, width: 130}
													],
													listeners: {
														'change': function(field, checked) {
															win.findById('swENSEW_Reflexes_Panel').change_handler(field, checked);
														}
													}
												})	
											]
										},
										//Хватание подошвой 
										{xtype:'label',text: 'Хватание подошвой',style: 'font-size: 10pt;  margin-left: 12pt; margin-top: 2pt; margin-bottom: 2px'},
										{									
											layout:'form',
											labelWidth:1,
											FromInterface: true,
											style: 'margin-top: 4px;  margin-left: 4px; margin-bottom: 2px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											items:[
												new Ext.form.RadioGroup({
													id:'swENSEW_Reflexes7',
													labelSeparator: '',
													vertical: true,
													columns: 1,
													items: [
														{boxLabel: '---', name: 'Reflexes7', inputValue: 0, width: 50}, 
														{boxLabel: 'вызывается', name: 'Reflexes7', inputValue: 1, width: 130}, 
														{boxLabel: 'угнетен', name: 'Reflexes7', inputValue: 2, width: 130},
														{boxLabel: 'усилен', name: 'Reflexes7', inputValue: 3, width: 130}
													],
													listeners: {
														'change': function(field, checked) {
															win.findById('swENSEW_Reflexes_Panel').change_handler(field, checked);
														}
													}
												})	
											]
										},
										//Опоры 
										{xtype:'label',text: 'Опоры',style: 'font-size: 10pt;  margin-left: 12pt; margin-top: 2pt; margin-bottom: 2px'},
										{									
											layout:'form',
											labelWidth:1,
											FromInterface: true,
											style: 'margin-top: 4px;  margin-left: 4px; margin-bottom: 2px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											items:[
												new Ext.form.RadioGroup({
													id:'swENSEW_Reflexes8',
													labelSeparator: '',
													vertical: true,
													columns: 1,
													items: [
														{boxLabel: '---', name: 'Reflexes8', inputValue: 0, width: 50}, 
														{boxLabel: 'вызывается', name: 'Reflexes8', inputValue: 1, width: 130}, 
														{boxLabel: 'угнетен', name: 'Reflexes8', inputValue: 2, width: 130},
														{boxLabel: 'усилен', name: 'Reflexes8', inputValue: 3, width: 130}
													],
													listeners: {
														'change': function(field, checked) {
															win.findById('swENSEW_Reflexes_Panel').change_handler(field, checked);
														}
													}
												})	
											]
										}
									]
								}
							],
							change_handler: function(field, checked) {
								if((checked) && field.FromInterface){
									var Reflexes = (this.NeonatalSurvey_data.NeonatalSurveyParam.Reflexes) ? this.NeonatalSurvey_data.NeonatalSurveyParam.Reflexes : '00000000';
									var position = parseInt(field.id.substr(field.id.length - 1, 1));
									var value = checked.inputValue;
									Reflexes = Reflexes.split("");
									Reflexes[position - 1] = value;
									Reflexes = Reflexes.join("");
									if (!Ext.isEmpty(this.NeonatalSurvey_data.NeonatalSurveyParam.Reflexes) || (Reflexes != '00000000')) 
										this.NeonatalSurvey_data.NeonatalSurveyParam.Reflexes = Reflexes;									
									this.changedDatas = true;
								}
								field.FromInterface = true;
							}.createDelegate(this)
						},
						//Панель Общие симптомы неврологических нарушений
						{
							id: 'swENSEW_Cramp_Panel',
							xtype: 'fieldset',
							autoHeight: true,
							title: langs('Общие симптомы неврологических нарушений'),
							collapsible: true,
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
									labelWidth: 200,
									items:[
										{
											fieldLabel: 'Выбухание переднего родничка',
											labelSeparator: '',
											name: 'FrontFontanelleBulg',
											id: 'swENSEW_FrontFontanelleBulg',
											xtype: 'checkbox',
											checked: false,
											listeners: {
												'check': function(chb, checked ) {
													if ((this.NeonatalSurvey_data.NeonatalSurveyParam.FrontFontanelleBulg) || (checked)) {
														this.NeonatalSurvey_data.NeonatalSurveyParam.FrontFontanelleBulg = checked ? 2 : 1;									
														this.changedDatas = true;
													}
												}.createDelegate(this)
											}		
										},
										{
											fieldLabel: 'Расширение черепных вен',
											labelSeparator: '',
											name: 'CranialVeinsExpans',
											id: 'swENSEW_CranialVeinsExpans',
											xtype: 'checkbox',
											checked: false,
											listeners: {
												'check': function(chb, checked ) {
													if ((this.NeonatalSurvey_data.NeonatalSurveyParam.CranialVeinsExpans) || (checked)) { 
														this.NeonatalSurvey_data.NeonatalSurveyParam.CranialVeinsExpans = checked ? 2 : 1;									
														this.changedDatas = true;
													}
												}.createDelegate(this)
											}		
										},
										{
											fieldLabel: 'Симптом ~заходящего солнца~',
											labelSeparator: '',
											name: 'SettingSunSymptom',
											id: 'swENSEW_SettingSunSymptom',
											xtype: 'checkbox',
											checked: false,
											listeners: {
												'check': function(chb, checked ) {
													if ((this.NeonatalSurvey_data.NeonatalSurveyParam.SettingSunSymptom) || (checked)) { 
														this.NeonatalSurvey_data.NeonatalSurveyParam.SettingSunSymptom = checked ? 2 : 1;									
														this.changedDatas = true;
													}
												}.createDelegate(this)
											}		
										},
										{
											fieldLabel: 'Симптом ~восходящего солнца~',
											labelSeparator: '',
											name: 'RisingSunSymptom',
											id: 'swENSEW_RisingSunSymptom',
											xtype: 'checkbox',
											checked: false,
											listeners: {
												'check': function(chb, checked ) {
													if ((this.NeonatalSurvey_data.NeonatalSurveyParam.RisingSunSymptom) || (checked)) { 
														this.NeonatalSurvey_data.NeonatalSurveyParam.RisingSunSymptom = checked ? 2 : 1;									
														this.changedDatas = true;
													}
												}.createDelegate(this)
											}		
										},
										{
											fieldLabel: 'Апноэ',
											labelSeparator: '',
											name: 'Apnoea',
											id: 'swENSEW_Apnoea',
											xtype: 'checkbox',
											checked: false,
											listeners: {
												'check': function(chb, checked ) {
													if ((this.NeonatalSurvey_data.NeonatalSurveyParam.Apnoea) || (checked)) { 
														this.NeonatalSurvey_data.NeonatalSurveyParam.Apnoea = checked ? 2 : 1;									
														this.changedDatas = true;
													}
												}.createDelegate(this)
											}		
										}
									]
								},
								//Судороги
								{xtype:'label',text: 'Судороги',style: 'font-size: 10pt;  margin-left: 22pt; margin-top: 2pt; margin-bottom: 2px'},
								{									
									layout:'form',
									labelWidth:1,
									style: 'margin-top: 4px;  margin-left: 4px; margin-bottom: 2px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
									items:[
										new Ext.form.RadioGroup({
											id:'swENSEW_Cramp1',
											labelSeparator: '',
											vertical: true,
											columns: 1,
											FromInterface: true,
											items: [
												{boxLabel: 'нет', name: 'Cramp1', inputValue: 0, width: 50}, 
												{boxLabel: 'клонические', name: 'Cramp1', inputValue: 1, width: 130}, 
												{boxLabel: 'тонические', name: 'Cramp1', inputValue: 2, width: 130},
												{boxLabel: 'миоклонические', name: 'Cramp1', inputValue: 3, width: 130}
											],
											listeners: {
												'change': function(field, checked) {
													win.findById('swENSEW_Cramp_Panel').change_handler(field, checked);
												}
											}
										})	
									]
								},
								//Судорожная активность
								{xtype:'label',text: 'Судорожная активность',style: 'font-size: 10pt;  margin-left: 12pt; margin-top: 2pt; margin-bottom: 2px'},
								{									
									layout:'form',
									labelWidth:1,
									style: 'margin-top: 4px;  margin-left: 4px; margin-bottom: 2px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
									items:[
										new Ext.form.RadioGroup({
											id:'swENSEW_Cramp2',
											labelSeparator: '',
											vertical: true,
											columns: 1,
											FromInterface: true,
											items: [
												{boxLabel: 'нет', name: 'Cramp2', inputValue: 0, width: 50}, 
												{boxLabel: 'сосание или жевание языка', name: 'Cramp2', inputValue: 1, width: 200}, 
												{boxLabel: 'мигание век', name: 'Cramp2', inputValue: 2, width: 130},
												{boxLabel: 'закатывание глаз', name: 'Cramp2', inputValue: 3, width: 130},
												{boxLabel: 'икота', name: 'Cramp2', inputValue: 4, width: 130}
											],
											listeners: {
												'change': function(field, checked) {
													win.findById('swENSEW_Cramp_Panel').change_handler(field, checked);
												}
											}
										})	
									]
								}
							],
							change_handler: function(field, checked) {
								if((checked) && field.FromInterface){
									var Cramp = (this.NeonatalSurvey_data.NeonatalSurveyParam.Cramp) ? this.NeonatalSurvey_data.NeonatalSurveyParam.Cramp : '00';
									var position = parseInt(field.id.substr(field.id.length - 1, 1));
									var value = checked.inputValue;
									Cramp = Cramp.split("");
									Cramp[position - 1] = value;
									Cramp = Cramp.join("");
									if (!Ext.isEmpty(this.NeonatalSurvey_data.NeonatalSurveyParam.Cramp) || (Cramp != '00')) 
										this.NeonatalSurvey_data.NeonatalSurveyParam.Cramp = Cramp;									
									this.changedDatas = true;
								}
								field.FromInterface = true;
							}.createDelegate(this)
						},
						//Панель Кожа
						{
							id: 'swENSEW_Skin_Panel',
							xtype: 'fieldset',
							autoHeight: true,
							title: langs('Кожа'),
							collapsible: true,
							layout: 'form',
							labelWidth: 5,
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
								//Цвет кожи
								{									
									layout:'column',
									id: 'swENSEW_SkinColor_Panel',
									items:[
										//Цвет
										{xtype:'label',text: 'Цвет',style: 'font-size: 10pt;  margin-left: 2pt; margin-top: 2pt; margin-bottom: 2px'},
										{									
											layout:'form',
											labelWidth:1,
											style: 'margin-top: 4px;  margin-left: 4px; margin-bottom: 2px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											items:[
												new Ext.form.RadioGroup({
													id:'swENSEW_SkinColor1',
													labelSeparator: '',
													vertical: true,
													columns: 1,
													FromInterface: true,
													items: [
														{boxLabel: '---', name: 'SkinColor1', inputValue: 0, width: 50}, 
														{boxLabel: 'плетора', name: 'SkinColor1', inputValue: 1, width: 130}, 
														{boxLabel: 'желтуха', name: 'SkinColor1', inputValue: 2, width: 130},
														{boxLabel: 'бледность', name: 'SkinColor1', inputValue: 3, width: 130},
														{boxLabel: 'цианоз', name: 'SkinColor1', inputValue: 4, width: 130}
													],
													listeners: {
														'change': function(field, checked) {
															win.findById('swENSEW_SkinColor_Panel').change_handler(field, checked);
														}
													}
												})	
											]
										},
										//желтуха
										{xtype:'label',text: 'желтуха',style: 'font-size: 10pt;  margin-left: 12pt; margin-top: 2pt; margin-bottom: 2px'},
										{									
											layout:'form',
											labelWidth:1,
											style: 'margin-top: 4px;  margin-left: 4px; margin-bottom: 2px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											items:[
												new Ext.form.RadioGroup({
													id:'swENSEW_SkinColor2',
													labelSeparator: '',
													vertical: true,
													columns: 1,
													FromInterface: true,
													items: [
														{boxLabel: '---', name: 'SkinColor2', inputValue: 0, width: 50, hidden: true}, 
														{boxLabel: 'иктеричный', name: 'SkinColor2', inputValue: 1, width: 130}, 
														{boxLabel: 'зеленоватый', name: 'SkinColor2', inputValue: 2, width: 130}
													],
													listeners: {
														'change': function(field, checked) {
															win.findById('swENSEW_SkinColor_Panel').change_handler(field, checked);
														}
													}
												})	
											]
										},
										//цианоз 
										{xtype:'label',text: 'цианоз',style: 'font-size: 10pt;  margin-left: 12pt; margin-top: 2pt; margin-bottom: 2px'},
										{									
											layout:'form',
											labelWidth:1,
											style: 'margin-top: 4px;  margin-left: 4px; margin-bottom: 2px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											items:[
												new Ext.form.RadioGroup({
													id:'swENSEW_SkinColor3',
													labelSeparator: '',
													vertical: true,
													columns: 1,
													FromInterface: true,
													items: [
														{boxLabel: '---', name: 'SkinColor3', inputValue: 0, width: 50, hidden: true}, 
														{boxLabel: 'центральный', name: 'SkinColor3', inputValue: 1, width: 130}, 
														{boxLabel: 'периферический', name: 'SkinColor3', inputValue: 2, width: 130},
														{boxLabel: 'акроцианоз', name: 'SkinColor3', inputValue: 3, width: 130}
													],
													listeners: {
														'change': function(field, checked) {
															win.findById('swENSEW_SkinColor_Panel').change_handler(field, checked);
														}
													}
												})	
											]
										},
										//Кожа на ощупь
										{xtype:'label',text: 'Кожа на ощупь',style: 'font-size: 10pt;  margin-left: 32pt; margin-top: 2pt; margin-bottom: 2px'},
										{									
											layout:'form',
											labelWidth:1,
											style: 'margin-top: 4px;  margin-left: 4px; margin-bottom: 2px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											items:[
												new Ext.form.RadioGroup({
													id:'swENSEW_SkinColor4',
													labelSeparator: '',
													vertical: true,
													columns: 1,
													FromInterface: true,
													items: [
														{boxLabel: '---', name: 'SkinColor4', inputValue: 0, width: 50}, 
														{boxLabel: 'влажная', name: 'SkinColor4', inputValue: 1, width: 130}, 
														{boxLabel: 'сухая', name: 'SkinColor4', inputValue: 2, width: 130}
													],
													listeners: {
														'change': function(field, checked) {
															win.findById('swENSEW_SkinColor_Panel').change_handler(field, checked);
														}
													}
												})	
											]
										}
									],
									change_handler: function(field, checked) {
										if((checked) && field.FromInterface){
												var SkinColor = (this.NeonatalSurvey_data.NeonatalSurveyParam.SkinColor) ? this.NeonatalSurvey_data.NeonatalSurveyParam.SkinColor : '0000';
												var position = parseInt(field.id.substr(field.id.length - 1, 1));
												var value = checked.inputValue;
												//если меняется выбранный в первой группе то обнуляются вторая и третья  //BOB - 06.05.2020
												if (position == 1) {
													this.findById('swENSEW_SkinColor2').items.items[0].setValue(true);
													for (var i = 1; i < this.findById('swENSEW_SkinColor2').items.items.length; i++  ) this.findById('swENSEW_SkinColor2').items.items[i].setValue(false);
													this.findById('swENSEW_SkinColor3').items.items[0].setValue(true);
													for (var i = 1; i < this.findById('swENSEW_SkinColor3').items.items.length; i++  ) this.findById('swENSEW_SkinColor3').items.items[i].setValue(false);
													this.findById('swENSEW_SkinColor2').disable();
													this.findById('swENSEW_SkinColor3').disable();
													if(value == 2)	this.findById('swENSEW_SkinColor2').enable();
													else if (value == 4)	this.findById('swENSEW_SkinColor3').enable();
												}
												SkinColor = SkinColor.split("");
												SkinColor[position - 1] = value;
												SkinColor = SkinColor.join("");
												if (!Ext.isEmpty(this.NeonatalSurvey_data.NeonatalSurveyParam.SkinColor) || (SkinColor != '0000')) 
													this.NeonatalSurvey_data.NeonatalSurveyParam.SkinColor = SkinColor;									
												this.changedDatas = true;
										}
										field.FromInterface = true;
									}.createDelegate(this)

								},
								{
									allowBlank: true,
									fieldLabel: '',
									labelSeparator: '',
									name: 'swENSEW_SkinColorTxt',
									id: 'swENSEW_SkinColorTxt',
									width: 1050,
									value:'',
									style: 'margin-top: 4px;  margin-left: 4px; ',
									xtype: 'textfield',
									listeners:{
										'change':function (field, newValue, oldValue) {
											this.NeonatalSurvey_data.NeonatalSurveyParam.SkinColorTxt = newValue;
											this.changedDatas = true;
										}.createDelegate(this)
									}
								},
								//дефекты кожи
								{									
									layout:'column',
									style: 'margin-top: 4px; ',
									items:[
										{									
											layout:'form',
											style: 'margin-top: 4px; ',
											labelWidth: 150,
											items:[
												{
													fieldLabel: 'Мраморность',
													labelSeparator: '',
													name: 'Marbling',
													id: 'swENSEW_Marbling',
													xtype: 'checkbox',
													checked: false,
													listeners: {
														'check': function(chb, checked ) {
															if ((this.NeonatalSurvey_data.NeonatalSurveyParam.Marbling) || (checked)) { 
																this.NeonatalSurvey_data.NeonatalSurveyParam.Marbling = checked ? 2 : 1;									
																this.changedDatas = true;
															}
														}.createDelegate(this)
													}		
												},
												{
													fieldLabel: 'Синдром Арлекина',
													labelSeparator: '',
													name: 'HarlequinSyndrome',
													id: 'swENSEW_HarlequinSyndrome',
													xtype: 'checkbox',
													checked: false,
													listeners: {
														'check': function(chb, checked ) {
															if ((this.NeonatalSurvey_data.NeonatalSurveyParam.HarlequinSyndrome) || (checked)) { 
																this.NeonatalSurvey_data.NeonatalSurveyParam.HarlequinSyndrome = checked ? 2 : 1;									
																this.changedDatas = true;
															}
														}.createDelegate(this)
													}		
												},
												{
													fieldLabel: 'Ихтиоз',
													labelSeparator: '',
													name: 'Ichthyosis',
													id: 'swENSEW_Ichthyosis',
													xtype: 'checkbox',
													checked: false,
													listeners: {
														'check': function(chb, checked ) {
															if ((this.NeonatalSurvey_data.NeonatalSurveyParam.Ichthyosis) || (checked)) { 
																this.NeonatalSurvey_data.NeonatalSurveyParam.Ichthyosis = checked ? 2 : 1;									
																this.changedDatas = true;
															}
														}.createDelegate(this)
													}		
												},
												{
													fieldLabel: 'Сухость (шелушения)',
													labelSeparator: '',
													name: 'Dryness',
													id: 'swENSEW_Dryness',
													xtype: 'checkbox',
													checked: false,
													listeners: {
														'check': function(chb, checked ) {
															if ((this.NeonatalSurvey_data.NeonatalSurveyParam.Dryness) || (checked)) { 
																this.NeonatalSurvey_data.NeonatalSurveyParam.Dryness = checked ? 2 : 1;									
																this.changedDatas = true;
															}
														}.createDelegate(this)
													}		
												},
												{
													fieldLabel: 'Аплазия кожи',
													labelSeparator: '',
													name: 'SkinAplasia',
													id: 'swENSEW_SkinAplasia',
													xtype: 'checkbox',
													checked: false,
													listeners: {
														'check': function(chb, checked ) {
															if ((this.NeonatalSurvey_data.NeonatalSurveyParam.SkinAplasia) || (checked)) { 
																this.NeonatalSurvey_data.NeonatalSurveyParam.SkinAplasia = checked ? 2 : 1;									
																this.changedDatas = true;
															}
														}.createDelegate(this)
													}		
												},
												{
													fieldLabel: 'Тонкая хрупкая кожа',
													labelSeparator: '',
													name: 'ThinFragile',
													id: 'swENSEW_ThinFragile',
													xtype: 'checkbox',
													checked: false,
													listeners: {
														'check': function(chb, checked ) {
															if((this.NeonatalSurvey_data.NeonatalSurveyParam.ThinFragile) || (checked)) { 
																this.NeonatalSurvey_data.NeonatalSurveyParam.ThinFragile = checked ? 2 : 1;									
																this.changedDatas = true;
															}
														}.createDelegate(this)
													}		
												},
												{
													fieldLabel: 'Белая кожа и волосы',
													labelSeparator: '',
													name: 'WhiteSkinHair',
													id: 'swENSEW_WhiteSkinHair',
													xtype: 'checkbox',
													checked: false,
													listeners: {
														'check': function(chb, checked ) {
															if ((this.NeonatalSurvey_data.NeonatalSurveyParam.WhiteSkinHair) || (checked)) { 
																this.NeonatalSurvey_data.NeonatalSurveyParam.WhiteSkinHair = checked ? 2 : 1;									
																this.changedDatas = true;
															}
														}.createDelegate(this)
													}		
												},
												{
													fieldLabel: 'Дисплазия эктодермы',
													labelSeparator: '',
													name: 'EctodermDysplasia',
													id: 'swENSEW_EctodermDysplasia',
													xtype: 'checkbox',
													checked: false,
													listeners: {
														'check': function(chb, checked ) {
															if ((this.NeonatalSurvey_data.NeonatalSurveyParam.EctodermDysplasia) || (checked)) { 
																this.NeonatalSurvey_data.NeonatalSurveyParam.EctodermDysplasia = checked ? 2 : 1;									
																this.changedDatas = true;
															}
														}.createDelegate(this)
													}		
												},
												{
													fieldLabel: 'Нейрофиброматоз',
													labelSeparator: '',
													name: 'Neurofibromatosis',
													id: 'swENSEW_Neurofibromatosis',
													xtype: 'checkbox',
													checked: false,
													listeners: {
														'check': function(chb, checked ) {
															if ((this.NeonatalSurvey_data.NeonatalSurveyParam.Neurofibromatosis) || (checked)) { 
																this.NeonatalSurvey_data.NeonatalSurveyParam.Neurofibromatosis = checked ? 2 : 1;									
																this.changedDatas = true;
															}
														}.createDelegate(this)
													}		
												}
											]
										},
										{									
											layout:'form',
											style: 'margin-top: 4px; ',
											width: 1100,
											items:[
												{									
													layout:'column',
													items:[
														{
															layout:'form',
															labelWidth: 210,
															items:[
																{
																	fieldLabel: 'Макула',
																	labelSeparator: '',
																	name: 'Macule',
																	id: 'swENSEW_Macule',
																	xtype: 'checkbox',
																	checked: false,
																	listeners: {
																		'check': function(chb, checked ) {
																			if ((this.NeonatalSurvey_data.NeonatalSurveyParam.Macule) || (checked)) { 
																				this.NeonatalSurvey_data.NeonatalSurveyParam.Macule = checked ? 2 : 1;									
																				this.changedDatas = true;
																				this.findById('swENSEW_MaculeTxt').setDisabled(!checked);  //BOB - 06.05.2020
																				if (!checked) {
																					this.findById('swENSEW_MaculeTxt').setValue('');
																					delete this.NeonatalSurvey_data.NeonatalSurveyParam.MaculeTxt;
																				}
																			}
																		}.createDelegate(this)
																	}		
																}
															]
														},		
														{
															allowBlank: true,
															fieldLabel: '',
															labelSeparator: '',
															name: 'swENSEW_MaculeTxt',
															id: 'swENSEW_MaculeTxt',
															width: 850,
															value:'',
															style: 'margin-top: 1px;  margin-left: 4px; ',
															xtype: 'textfield',
															listeners:{
																'change':function (field, newValue, oldValue) {
																	this.NeonatalSurvey_data.NeonatalSurveyParam.MaculeTxt = newValue;
																	this.changedDatas = true;
																}.createDelegate(this)
															}
														}
													]
												},		
												{									
													layout:'column',
													items:[
														{
															layout:'form',
															labelWidth: 210,
															items:[
																{
																	fieldLabel: 'Папулы',
																	labelSeparator: '',
																	name: 'Papules',
																	id: 'swENSEW_Papules',
																	xtype: 'checkbox',
																	checked: false,
																	listeners: {
																		'check': function(chb, checked ) {
																			if ((this.NeonatalSurvey_data.NeonatalSurveyParam.Papules) || (checked)) { 
																				this.NeonatalSurvey_data.NeonatalSurveyParam.Papules = checked ? 2 : 1;									
																				this.changedDatas = true;
																				this.findById('swENSEW_PapulesTxt').setDisabled(!checked);  //BOB - 06.05.2020
																				if (!checked) {
																					this.findById('swENSEW_PapulesTxt').setValue('');
																					delete this.NeonatalSurvey_data.NeonatalSurveyParam.PapulesTxt;
																				}
																			}
																		}.createDelegate(this)
																	}		
																}
															]
														},		
														{
															allowBlank: true,
															fieldLabel: '',
															labelSeparator: '',
															name: 'swENSEW_PapulesTxt',
															id: 'swENSEW_PapulesTxt',
															width: 850,
															value:'',
															style: 'margin-top: 1px;  margin-left: 4px; ',
															xtype: 'textfield',
															listeners:{
																'change':function (field, newValue, oldValue) {
																	this.NeonatalSurvey_data.NeonatalSurveyParam.PapulesTxt = newValue;
																	this.changedDatas = true;
																}.createDelegate(this)
															}
														}
													]
												},		
												{									
													layout:'column',
													items:[
														{
															layout:'form',
															labelWidth: 210,
															items:[
																{
																	fieldLabel: 'Пузырьки',
																	labelSeparator: '',
																	name: 'Bubbles',
																	id: 'swENSEW_Bubbles',
																	xtype: 'checkbox',
																	checked: false,
																	listeners: {
																		'check': function(chb, checked ) {
																			if ((this.NeonatalSurvey_data.NeonatalSurveyParam.Bubbles) || (checked)) { 
																				this.NeonatalSurvey_data.NeonatalSurveyParam.Bubbles = checked ? 2 : 1;									
																				this.changedDatas = true;
																				this.findById('swENSEW_BubblesTxt').setDisabled(!checked);  //BOB - 06.05.2020
																				if (!checked) {
																					this.findById('swENSEW_BubblesTxt').setValue('');
																					delete this.NeonatalSurvey_data.NeonatalSurveyParam.BubblesTxt;
																				}
																			}
																		}.createDelegate(this)
																	}		
																}
															]
														},		
														{
															allowBlank: true,
															fieldLabel: '',
															labelSeparator: '',
															name: 'swENSEW_BubblesTxt',
															id: 'swENSEW_BubblesTxt',
															width: 850,
															value:'',
															style: 'margin-top: 1px;  margin-left: 4px; ',
															xtype: 'textfield',
															listeners:{
																'change':function (field, newValue, oldValue) {
																	this.NeonatalSurvey_data.NeonatalSurveyParam.BubblesTxt = newValue;
																	this.changedDatas = true;
																}.createDelegate(this)
															}
														}
													]
												},		
												{									
													layout:'column',
													items:[
														{
															layout:'form',
															labelWidth: 210,
															items:[
																{
																	fieldLabel: 'Большие пузыри',
																	labelSeparator: '',
																	name: 'BigBubble',
																	id: 'swENSEW_BigBubble',
																	xtype: 'checkbox',
																	checked: false,
																	listeners: {
																		'check': function(chb, checked ) {
																			if ((this.NeonatalSurvey_data.NeonatalSurveyParam.BigBubble) || (checked)) { 
																				this.NeonatalSurvey_data.NeonatalSurveyParam.BigBubble = checked ? 2 : 1;									
																				this.changedDatas = true;
																				this.findById('swENSEW_BigBubbleTxt').setDisabled(!checked);  //BOB - 06.05.2020
																				if (!checked) {
																					this.findById('swENSEW_BigBubbleTxt').setValue('');
																					delete this.NeonatalSurvey_data.NeonatalSurveyParam.BigBubbleTxt;
																				}
																			}
																		}.createDelegate(this)
																	}		
																}
															]
														},		
														{
															allowBlank: true,
															fieldLabel: '',
															labelSeparator: '',
															name: 'swENSEW_BigBubbleTxt',
															id: 'swENSEW_BigBubbleTxt',
															width: 850,
															value:'',
															style: 'margin-top: 1px;  margin-left: 4px; ',
															xtype: 'textfield',
															listeners:{
																'change':function (field, newValue, oldValue) {
																	this.NeonatalSurvey_data.NeonatalSurveyParam.BigBubbleTxt = newValue;
																	this.changedDatas = true;
																}.createDelegate(this)
															}
														}
													]
												},		
												{									
													layout:'column',
													items:[
														{
															layout:'form',
															labelWidth: 210,
															items:[
																{
																	fieldLabel: 'Пустулы',
																	labelSeparator: '',
																	name: 'Pustules',
																	id: 'swENSEW_Pustules',
																	xtype: 'checkbox',
																	checked: false,
																	listeners: {
																		'check': function(chb, checked ) {
																			if ((this.NeonatalSurvey_data.NeonatalSurveyParam.Pustules) || (checked)) { 
																				this.NeonatalSurvey_data.NeonatalSurveyParam.Pustules = checked ? 2 : 1;									
																				this.changedDatas = true;
																				this.findById('swENSEW_PustulesTxt').setDisabled(!checked);  //BOB - 06.05.2020
																				if (!checked) {
																					this.findById('swENSEW_PustulesTxt').setValue('');
																					delete this.NeonatalSurvey_data.NeonatalSurveyParam.PustulesTxt;
																				}
																			}
																		}.createDelegate(this)
																	}		
																}
															]
														},		
														{
															allowBlank: true,
															fieldLabel: '',
															labelSeparator: '',
															name: 'swENSEW_PustulesTxt',
															id: 'swENSEW_PustulesTxt',
															width: 850,
															value:'',
															style: 'margin-top: 1px;  margin-left: 4px; ',
															xtype: 'textfield',
															listeners:{
																'change':function (field, newValue, oldValue) {
																	this.NeonatalSurvey_data.NeonatalSurveyParam.PustulesTxt = newValue;
																	this.changedDatas = true;
																}.createDelegate(this)
															}
														}
													]
												},		
												{									
													layout:'column',
													items:[
														{
															layout:'form',
															labelWidth: 210,
															items:[
																{
																	fieldLabel: 'Токсическая эритема',
																	labelSeparator: '',
																	name: 'ToxicErythema',
																	id: 'swENSEW_ToxicErythema',
																	xtype: 'checkbox',
																	checked: false,
																	listeners: {
																		'check': function(chb, checked ) {
																			if ((this.NeonatalSurvey_data.NeonatalSurveyParam.ToxicErythema) || (checked)) { 
																				this.NeonatalSurvey_data.NeonatalSurveyParam.ToxicErythema = checked ? 2 : 1;									
																				this.changedDatas = true;
																				this.findById('swENSEW_ToxicErythemaTxt').setDisabled(!checked);  //BOB - 06.05.2020
																				if (!checked) {
																					this.findById('swENSEW_ToxicErythemaTxt').setValue('');
																					delete this.NeonatalSurvey_data.NeonatalSurveyParam.ToxicErythemaTxt;
																				}
																			}
																		}.createDelegate(this)
																	}		
																}
															]
														},		
														{
															allowBlank: true,
															fieldLabel: '',
															labelSeparator: '',
															name: 'swENSEW_ToxicErythemaTxt',
															id: 'swENSEW_ToxicErythemaTxt',
															width: 850,
															value:'',
															style: 'margin-top: 1px;  margin-left: 4px; ',
															xtype: 'textfield',
															listeners:{
																'change':function (field, newValue, oldValue) {
																	this.NeonatalSurvey_data.NeonatalSurveyParam.ToxicErythemaTxt = newValue;
																this.changedDatas = true;
																}.createDelegate(this)
															}
														}
													]
												},		
												{									
													layout:'column',
													items:[
														{
															layout:'form',
															labelWidth: 210,
															items:[
																{
																	fieldLabel: 'Кандидозная сыпь',
																	labelSeparator: '',
																	name: 'CandidaRash',
																	id: 'swENSEW_CandidaRash',
																	xtype: 'checkbox',
																	checked: false,
																	listeners: {
																		'check': function(chb, checked ) {
																			if ((this.NeonatalSurvey_data.NeonatalSurveyParam.CandidaRash) || (checked)) { 
																				this.NeonatalSurvey_data.NeonatalSurveyParam.CandidaRash = checked ? 2 : 1;									
																				this.changedDatas = true;
																				this.findById('swENSEW_CandidaRashTxt').setDisabled(!checked);  //BOB - 06.05.2020
																				if (!checked) {
																					this.findById('swENSEW_CandidaRashTxt').setValue('');
																					delete this.NeonatalSurvey_data.NeonatalSurveyParam.CandidaRashTxt;
																				}
																			}
																		}.createDelegate(this)
																	}		
																}
															]
														},		
														{
															allowBlank: true,
															fieldLabel: '',
															labelSeparator: '',
															name: 'swENSEW_CandidaRashTxt',
															id: 'swENSEW_CandidaRashTxt',
															width: 850,
															value:'',
															style: 'margin-top: 1px;  margin-left: 4px; ',
															xtype: 'textfield',
															listeners:{
																'change':function (field, newValue, oldValue) {
																	this.NeonatalSurvey_data.NeonatalSurveyParam.CandidaRashTxt = newValue;
																	this.changedDatas = true;
																}.createDelegate(this)
															}
														}
													]
												},		
												{									
													layout:'column',
													items:[
														{
															layout:'form',
															labelWidth: 210,
															items:[
																{
																	fieldLabel: 'Простой герпес',
																	labelSeparator: '',
																	name: 'HerpesSimplex',
																	id: 'swENSEW_HerpesSimplex',
																	xtype: 'checkbox',
																	checked: false,
																	listeners: {
																		'check': function(chb, checked ) {
																			if ((this.NeonatalSurvey_data.NeonatalSurveyParam.HerpesSimplex) || (checked)) { 
																				this.NeonatalSurvey_data.NeonatalSurveyParam.HerpesSimplex = checked ? 2 : 1;									
																				this.changedDatas = true;
																				this.findById('swENSEW_HerpesSimplexTxt').setDisabled(!checked);  //BOB - 06.05.2020
																				if (!checked) {
																					this.findById('swENSEW_HerpesSimplexTxt').setValue('');
																					delete this.NeonatalSurvey_data.NeonatalSurveyParam.HerpesSimplexTxt;
																				}
																			}
																		}.createDelegate(this)
																	}		
																}
															]
														},		
														{
															allowBlank: true,
															fieldLabel: '',
															labelSeparator: '',
															name: 'swENSEW_HerpesSimplexTxt',
															id: 'swENSEW_HerpesSimplexTxt',
															width: 850,
															value:'',
															style: 'margin-top: 1px;  margin-left: 4px; ',
															xtype: 'textfield',
															listeners:{
																'change':function (field, newValue, oldValue) {
																	this.NeonatalSurvey_data.NeonatalSurveyParam.HerpesSimplexTxt = newValue;
																	this.changedDatas = true;
																}.createDelegate(this)
															}
														}
													]
												},		
												{									
													layout:'column',
													items:[
														{
															layout:'form',
															labelWidth: 210,
															items:[
																{
																	fieldLabel: 'Гемангиома',
																	labelSeparator: '',
																	name: 'Hemangioma',
																	id: 'swENSEW_Hemangioma',
																	xtype: 'checkbox',
																	checked: false,
																	listeners: {
																		'check': function(chb, checked ) {
																			if ((this.NeonatalSurvey_data.NeonatalSurveyParam.Hemangioma) || (checked)) { 
																				this.NeonatalSurvey_data.NeonatalSurveyParam.Hemangioma = checked ? 2 : 1;									
																				this.changedDatas = true;
																				this.findById('swENSEW_HemangiomaTxt').setDisabled(!checked);  //BOB - 06.05.2020
																				if (!checked) {
																					this.findById('swENSEW_HemangiomaTxt').setValue('');
																					delete this.NeonatalSurvey_data.NeonatalSurveyParam.HemangiomaTxt;
																				}
																			}
																		}.createDelegate(this)
																	}		
																}
															]
														},		
														{
															allowBlank: true,
															fieldLabel: '',
															labelSeparator: '',
															name: 'swENSEW_HemangiomaTxt',
															id: 'swENSEW_HemangiomaTxt',
															width: 850,
															value:'',
															style: 'margin-top: 1px;  margin-left: 4px; ',
															xtype: 'textfield',
															listeners:{
																'change':function (field, newValue, oldValue) {
																	this.NeonatalSurvey_data.NeonatalSurveyParam.HemangiomaTxt = newValue;
																	this.changedDatas = true;
																}.createDelegate(this)
															}
														}
													]
												},		
												{									
													layout:'column',
													items:[
														{
															layout:'form',
															labelWidth: 210,
															items:[
																{
																	fieldLabel: 'Геморрагическая сыпь – экхимозы',
																	labelSeparator: '',
																	name: 'HemorRashEcchymos',
																	id: 'swENSEW_HemorRashEcchymos',
																	xtype: 'checkbox',
																	checked: false,
																	listeners: {
																		'check': function(chb, checked ) {
																			if ((this.NeonatalSurvey_data.NeonatalSurveyParam.HemorRashEcchymos) || (checked)) { 
																				this.NeonatalSurvey_data.NeonatalSurveyParam.HemorRashEcchymos = checked ? 2 : 1;									
																				this.changedDatas = true;
																				this.findById('swENSEW_HemorRashEcchymosTxt').setDisabled(!checked);  //BOB - 06.05.2020
																				if (!checked) {
																					this.findById('swENSEW_HemorRashEcchymosTxt').setValue('');
																					delete this.NeonatalSurvey_data.NeonatalSurveyParam.HemorRashEcchymosTxt;
																				}
																			}
																		}.createDelegate(this)
																	}		
																}
															]
														},		
														{
															allowBlank: true,
															fieldLabel: '',
															labelSeparator: '',
															name: 'swENSEW_HemorRashEcchymosTxt',
															id: 'swENSEW_HemorRashEcchymosTxt',
															width: 850,
															value:'',
															style: 'margin-top: 1px;  margin-left: 4px; ',
															xtype: 'textfield',
															listeners:{
																'change':function (field, newValue, oldValue) {
																	this.NeonatalSurvey_data.NeonatalSurveyParam.HemorRashEcchymosTxt = newValue;
																	this.changedDatas = true;
																}.createDelegate(this)
															}
														}
													]
												},		
												{									
													layout:'column',
													items:[
														{
															layout:'form',
															labelWidth: 210,
															items:[
																{
																	fieldLabel: 'Геморрагическая сыпь – петехии',
																	labelSeparator: '',
																	name: 'HemorRashPetechiae',
																	id: 'swENSEW_HemorRashPetechiae',
																	xtype: 'checkbox',
																	checked: false,
																	listeners: {
																		'check': function(chb, checked ) {
																			if ((this.NeonatalSurvey_data.NeonatalSurveyParam.HemorRashPetechiae) || (checked)) {
																				this.NeonatalSurvey_data.NeonatalSurveyParam.HemorRashPetechiae = checked ? 2 : 1;									
																				this.changedDatas = true;
																				this.findById('swENSEW_HemorRashPetechiaeTxt').setDisabled(!checked);  //BOB - 06.05.2020
																				if (!checked) {
																					this.findById('swENSEW_HemorRashPetechiaeTxt').setValue('');
																					delete this.NeonatalSurvey_data.NeonatalSurveyParam.HemorRashPetechiaeTxt;
																				}
																			}
																		}.createDelegate(this)
																	}		
																}
															]
														},		
														{
															allowBlank: true,
															fieldLabel: '',
															labelSeparator: '',
															name: 'swENSEW_HemorRashPetechiaeTxt',
															id: 'swENSEW_HemorRashPetechiaeTxt',
															width: 850,
															value:'',
															style: 'margin-top: 1px;  margin-left: 4px; ',
															xtype: 'textfield',
															listeners:{
																'change':function (field, newValue, oldValue) {
																	this.NeonatalSurvey_data.NeonatalSurveyParam.HemorRashPetechiaeTxt = newValue;
																	this.changedDatas = true;
																}.createDelegate(this)
															}
														}
													]
												}		

											]
										}		
									]
								},
								//Конечности на ощупь, Тургор мягких тканей
								{									
									layout:'column',
									style: 'margin-top: 4px; ',
									items:[
										//Конечности на ощупь 
										{xtype:'label',text: 'Конечности на ощупь',style: 'font-size: 10pt;  margin-left: 2pt; margin-top: 2pt; margin-bottom: 2px'},
										{									
											layout:'form',
											labelWidth:1,
											style: 'margin-top: 4px;  margin-left: 4px; margin-bottom: 2px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											items:[
												new Ext.form.RadioGroup({
													id:'swENSEW_LimbsTouch',
													labelSeparator: '',
													vertical: true,
													columns: 3,
													FromInterface: true,
													items: [
														{boxLabel: '---', name: 'LimbsTouch', inputValue: 0, width: 50}, 
														{boxLabel: 'теплые', name: 'LimbsTouch', inputValue: 1, width: 130}, 
														{boxLabel: 'холодные', name: 'LimbsTouch', inputValue: 2, width: 130}
													],
													listeners: {
														'change': function(field, checked) {
															if((checked) && field.FromInterface){
																if ((this.NeonatalSurvey_data.NeonatalSurveyParam.LimbsTouch) || (String(checked.inputValue) != '0')) {
																	this.NeonatalSurvey_data.NeonatalSurveyParam.LimbsTouch = checked.inputValue;									
																	this.changedDatas = true;
																}
															}
															field.FromInterface = true;
														}.createDelegate(this)
													}
												})	
											]
										},
										//Тургор мягких тканей
										{xtype:'label',text: 'Тургор мягких тканей',style: 'font-size: 10pt;  margin-left: 12pt; margin-top: 2pt; margin-bottom: 2px'},
										{									
											layout:'form',
											labelWidth:1,
											style: 'margin-top: 4px;  margin-left: 4px; margin-bottom: 2px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											items:[
												new Ext.form.RadioGroup({
													id:'swENSEW_Turgor',
													labelSeparator: '',
													vertical: true,
													columns: 4,
													FromInterface: true,
													items: [
														{boxLabel: '---', name: 'Turgor', inputValue: 0, width: 50}, 
														{boxLabel: 'сохранён', name: 'Turgor', inputValue: 1, width: 130}, 
														{boxLabel: 'снижен слабо', name: 'Turgor', inputValue: 2, width: 130},
														{boxLabel: 'снижен умеренно', name: 'Turgor', inputValue: 3, width: 130}
													],
													listeners: {
														'change': function(field, checked) {
															if((checked) && field.FromInterface){
																if ((this.NeonatalSurvey_data.NeonatalSurveyParam.Turgor) || (String(checked.inputValue) != '0')) {
																	this.NeonatalSurvey_data.NeonatalSurveyParam.Turgor = checked.inputValue;									
																	this.changedDatas = true;
																}
															}
															field.FromInterface = true;
														}.createDelegate(this)
													}
												})	
											]
										}
									]
								},
								//Подкожно-жировая клетчатка развита 
								{
									layout: 'column',
									style: 'margin-top: 4px; ',
									items:[	
										{xtype:'label',text: 'Подкожно-жировая клетчатка развита',width: 130,style: 'font-size: 10pt;  margin-left: 12pt; margin-top: 2pt; margin-bottom: 2px'},
										{									
											layout:'form',
											labelWidth:1,
											style: 'margin-top: 4px;  margin-left: 4px; margin-bottom: 2px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											items:[
												new Ext.form.RadioGroup({
													id:'swENSEW_SubcutaneousFat',
													labelSeparator: '',
													vertical: true,
													columns: 3,
													FromInterface: true,
													items: [
														{boxLabel: '---', name: 'SubcutaneousFat', inputValue: 0, width: 50}, 
														{boxLabel: 'достаточно', name: 'SubcutaneousFat', inputValue: 1, width: 110}, 
														{boxLabel: 'избыточно', name: 'SubcutaneousFat', inputValue: 2, width: 110},
														{boxLabel: 'слабо', name: 'SubcutaneousFat', inputValue: 3, width: 110},
														{boxLabel: 'отсутствует', name: 'SubcutaneousFat', inputValue: 4, width: 110},
														{boxLabel: 'иное', name: 'SubcutaneousFat', inputValue: 5, width: 80}
													],
													listeners: {
														'change': function(field, checked) {
															if((checked) && field.FromInterface){
																if ((this.NeonatalSurvey_data.NeonatalSurveyParam.SubcutaneousFat) || (String(checked.inputValue) != '0')) {
																	this.NeonatalSurvey_data.NeonatalSurveyParam.SubcutaneousFat = checked.inputValue;									
																	this.changedDatas = true;
																}
															}
															field.FromInterface = true;
														}.createDelegate(this)
													}
												})	
											]
										},
										{
											allowBlank: true,
											fieldLabel: '',
											labelSeparator: '',
											name: 'swENSEW_SubcutaneousFatTxt',
											id: 'swENSEW_SubcutaneousFatTxt',
											width: 770,
											value:'',
											style: 'margin-top: 4px;  margin-left: 4px; ',
											xtype: 'textfield',
											listeners:{
												'change':function (field, newValue, oldValue) {
													this.NeonatalSurvey_data.NeonatalSurveyParam.SubcutaneousFatTxt = newValue;
													this.changedDatas = true;
												}.createDelegate(this)
											}
										}
									]
								},
								//Панель Отёки
								{
									id: 'swENSEW_Oedemata_Panel',
									style: 'margin-top: 4px; ',
									layout: 'form',
									items:[	
										{									
											layout:'column',
											items:[
												{xtype:'label',text: 'Отёки',style: 'font-size: 10pt;  margin-left: 12pt; margin-top: 2pt; margin-bottom: 2px'},
												{									
													layout:'form',
													labelWidth:1,
													style: 'margin-top: 4px;  margin-left: 4px; margin-bottom: 1px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
													items:[
														new Ext.form.RadioGroup({
															id:'swENSEW_Oedemata1',
															labelSeparator: '',
															vertical: true,
															columns: 5,
															FromInterface: true,
															items: [
																{boxLabel: 'нет', name: 'Oedemata1', inputValue: 0, width: 50}, 
																{boxLabel: 'пастозность', name: 'Oedemata1', inputValue: 1, width: 130}, 
																{boxLabel: 'склерема', name: 'Oedemata1', inputValue: 2, width: 130},
																{boxLabel: 'позиционные', name: 'Oedemata1', inputValue: 3, width: 130},
																{boxLabel: 'иное', name: 'Oedemata1', inputValue: 4, width: 70}
															],
															listeners: {
																'change': function(field, checked) {
																	win.findById('swENSEW_Oedemata_Panel').change_handler(field, checked);
																}
															}
														}),
													]
												},
												{xtype:'label',text: 'пастозность',style: 'font-size: 10pt;  margin-left: 12pt; margin-top: 2pt; margin-bottom: 2px'},
												{									
													layout:'form',
													labelWidth:1,
													style: 'margin-top: 4px;  margin-left: 4px; margin-bottom: 1px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
													items:[
														new Ext.form.RadioGroup({
															id:'swENSEW_Oedemata2',
															labelSeparator: '',
															vertical: true,
															columns: 5,
															FromInterface: true,
															items: [
																{boxLabel: '---', name: 'Oedemata2', inputValue: 0, width: 50, hidden: true}, 
																{boxLabel: 'лица', name: 'Oedemata2', inputValue: 1, width: 130}, 
																{boxLabel: 'конечностей', name: 'Oedemata2', inputValue: 2, width: 130},
																{boxLabel: 'брюшной стенки', name: 'Oedemata2', inputValue: 3, width: 150},
																{boxLabel: 'всего тела', name: 'Oedemata2', inputValue: 4, width: 130}
															],
															listeners: {
																'change': function(field, checked) {
																	win.findById('swENSEW_Oedemata_Panel').change_handler(field, checked);
																}
															}
														})	
													]
												}
											]
										},
										{
											allowBlank: true,
											fieldLabel: '',
											labelSeparator: '',
											name: 'swENSEW_OedemataTxt',
											id: 'swENSEW_OedemataTxt',
											width: 1150,
											value:'',
											style: 'margin-top: 4px;  margin-left: 4px; margin-bottom: 4px; ',
											xtype: 'textfield',
											listeners:{
												'change':function (field, newValue, oldValue) {
													this.NeonatalSurvey_data.NeonatalSurveyParam.OedemataTxt = newValue;
													this.changedDatas = true;
												}.createDelegate(this)
											}
										}
									],
									change_handler: function(field, checked) {
										if((checked) && field.FromInterface){
											var Oedemata = (this.NeonatalSurvey_data.NeonatalSurveyParam.Oedemata) ? this.NeonatalSurvey_data.NeonatalSurveyParam.Oedemata : '00';
											var position = parseInt(field.id.substr(field.id.length - 1, 1));
											var value = checked.inputValue;
											//если меняется выбранный в первой группе то обнуляются вторая  //BOB - 06.05.2020
											if (position == 1) {
												this.findById('swENSEW_Oedemata2').items.items[0].setValue(true);
												for (var i = 1; i < this.findById('swENSEW_Oedemata2').items.items.length; i++  ) this.findById('swENSEW_Oedemata2').items.items[i].setValue(false);
												this.findById('swENSEW_Oedemata2').disable();
												if(value == 1) this.findById('swENSEW_Oedemata2').enable();
											}
											Oedemata = Oedemata.split("");
											Oedemata[position - 1] = value;
											Oedemata = Oedemata.join("");
											if (!Ext.isEmpty(this.NeonatalSurvey_data.NeonatalSurveyParam.Oedemata) || (Oedemata != '00')) 
												this.NeonatalSurvey_data.NeonatalSurveyParam.Oedemata = Oedemata;									
											this.changedDatas = true;
										}
										field.FromInterface = true;
									}.createDelegate(this)
								},
							]
						},
						//Панель Шея
						{
							id: 'swENSEW_Neck_Panel',
							xtype: 'fieldset',
							autoHeight: true,
							title: langs('Шея'),
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
								{									
									layout:'column',
									items:[
										{									
											layout:'form',
											labelWidth:1,
											style: 'margin-top: 4px;  margin-left: 4px; margin-bottom: 1px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											items:[
												new Ext.form.RadioGroup({
													id:'swENSEW_Neck1',
													labelSeparator: '',
													vertical: true,
													columns: 6,
													FromInterface: true,
													items: [
														{boxLabel: '---', name: 'Neck1', inputValue: 0, width: 50}, 
														{boxLabel: 'без особенностей', name: 'Neck1', inputValue: 1, width: 140}, 
														{boxLabel: 'короткая', name: 'Neck1', inputValue: 2, width: 90}, 
														{boxLabel: 'крыловидная', name: 'Neck1', inputValue: 3, width: 110}, 
														{boxLabel: 'кривошея', name: 'Neck1', inputValue: 4, width: 90}, 
														{boxLabel: 'кистозная гигрома', name: 'Neck1', inputValue: 5, width: 140}
													],
													listeners: {
														'change': function(field, checked) {
															win.findById('swENSEW_Neck_Panel').change_handler(field, checked);
														}
													}
												}),
											]
										},
										{xtype:'label',text: 'кривошея',style: 'font-size: 10pt;  margin-left: 2pt; margin-top: 2pt; margin-bottom: 2px'},
										{									
											layout:'form',
											labelWidth:1,
											style: 'margin-top: 4px;  margin-left: 4px; margin-bottom: 1px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											items:[
												new Ext.form.RadioGroup({
													id:'swENSEW_Neck2',
													labelSeparator: '',
													vertical: true,
													columns: 7,
													FromInterface: true,
													items: [
														{boxLabel: '---', name: 'Neck2', inputValue: 0, width: 50, hidden: true}, 
														{boxLabel: 'врождённая мышечная', name: 'Neck2', inputValue: 1, width: 170}, 
														{boxLabel: 'аномалии позвоночника', name: 'Neck2', inputValue: 2, width: 180}
													],
													listeners: {
														'change': function(field, checked) {
															win.findById('swENSEW_Neck_Panel').change_handler(field, checked);
														}
													}
												})	
											]
										}
									]
								},
								{									
									layout:'column',
									items:[
										{
											allowBlank: true,
											fieldLabel: '',
											labelSeparator: '',
											name: 'swENSEW_NeckTxt',
											id: 'swENSEW_NeckTxt',
											width: 1150,
											value:'',
											style: 'margin-top: 4px;  margin-left: 4px; margin-bottom: 4px; ',
											xtype: 'textfield',
											listeners:{
												'change':function (field, newValue, oldValue) {
													this.NeonatalSurvey_data.NeonatalSurveyParam.NeckTxt = newValue;
													this.changedDatas = true;
												}.createDelegate(this)
											}
										}
									]
								}
							],
							change_handler: function(field, checked) {
								if((checked) && field.FromInterface){
									var Neck = (this.NeonatalSurvey_data.NeonatalSurveyParam.Neck) ? this.NeonatalSurvey_data.NeonatalSurveyParam.Neck : '00';
									var position = parseInt(field.id.substr(field.id.length - 1, 1));
									var value = checked.inputValue;
									//если меняется выбранный в первой группе то обнуляются вторая
									if (position == 1) {
										this.findById('swENSEW_Neck2').items.items[0].setValue(true);
										for (var i = 1; i < this.findById('swENSEW_Neck2').items.items.length; i++  ) this.findById('swENSEW_Neck2').items.items[i].setValue(false);
										this.findById('swENSEW_Neck2').disable();
										if(value == 4) this.findById('swENSEW_Neck2').enable();
									}
									Neck = Neck.split("");
									Neck[position - 1] = value;
									Neck = Neck.join("");
									if (!Ext.isEmpty(this.NeonatalSurvey_data.NeonatalSurveyParam.Neck) || (Neck != '00')) 
										this.NeonatalSurvey_data.NeonatalSurveyParam.Neck = Neck;									
									this.changedDatas = true;
								}
								field.FromInterface = true;
							}.createDelegate(this)
						},
						//Панель Лицо
						{
							id: 'swENSEW_Face_Panel',
							xtype: 'fieldset',
							autoHeight: true,
							title: langs('Лицо'),
							collapsible: true,
							layout: 'form',
							labelWidth:120,
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
								//Форма лба, носа, рта, подбородка
								{									
									layout:'column',
									style: 'margin-bottom: 10px;',
									items:[
										//Форма лба, носа, рта
										{									
											layout:'form',
											style: 'margin-top: 2px; ',
											labelWidth: 80,
											items:[
												{
													allowBlank: true,
													fieldLabel: 'Форма лба',
													labelSeparator: '',
													name: 'swENSEW_ForeheadShape',
													id: 'swENSEW_ForeheadShape',
													width: 600,
													value:'',
													style: 'margin-top: 1px;  margin-left: 4px; ',
													xtype: 'textfield',
													listeners:{
														'change':function (field, newValue, oldValue) {
															this.NeonatalSurvey_data.NeonatalSurveyParam.ForeheadShape = newValue;
															this.changedDatas = true;
														}.createDelegate(this)
													}
												},
												{
													allowBlank: true,
													fieldLabel: 'Форма носа',
													labelSeparator: '',
													name: 'swENSEW_NoseShape',
													id: 'swENSEW_NoseShape',
													width: 600,
													value:'',
													style: 'margin-top: 1px;  margin-left: 4px; ',
													xtype: 'textfield',
													listeners:{
														'change':function (field, newValue, oldValue) {
															this.NeonatalSurvey_data.NeonatalSurveyParam.NoseShape = newValue;
															this.changedDatas = true;
														}.createDelegate(this)
													}
												},
												{
													allowBlank: true,
													fieldLabel: 'Форма рта',
													labelSeparator: '',
													name: 'swENSEW_MouthShape',
													id: 'swENSEW_MouthShape',
													width: 600,
													value:'',
													style: 'margin-top: 1px;  margin-left: 4px; ',
													xtype: 'textfield',
													listeners:{
														'change':function (field, newValue, oldValue) {
															this.NeonatalSurvey_data.NeonatalSurveyParam.MouthShape = newValue;
															this.changedDatas = true;
														}.createDelegate(this)
													}
												},
											]
										},
										//Форма подбородка
										{									
											layout:'column',
											style: 'margin-top: 2px; ',
											width: 600,
											labelWidth: 5,
											items:[
												{xtype:'label',text: 'Форма подбородка',style: 'font-size: 10pt;  margin-left: 12pt; margin-top: 2pt; margin-bottom: 2px; margin-right: 12px'},
												{									
													layout:'form',
													labelWidth:1,
													FromInterface: true,
													style: 'margin-top: 4px;  margin-left: 4px; margin-bottom: 1px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
													items:[
														new Ext.form.RadioGroup({
															id:'swENSEW_СhinShape',
															labelSeparator: '',
															vertical: true,
															columns: 4,
															items: [
																{boxLabel: '---', name: 'СhinShape', inputValue: 0, width: 50}, 
																{boxLabel: 'микрогнатия', name: 'СhinShape', inputValue: 1, width: 110}, 
																{boxLabel: 'макрогнатия', name: 'СhinShape', inputValue: 2, width: 110},
																{boxLabel: 'иное', name: 'СhinShape', inputValue: 3, width: 70}
															],
															listeners: {
																'change': function(field, checked) {
																	if((checked) && field.FromInterface){
																		if ((this.NeonatalSurvey_data.NeonatalSurveyParam.СhinShape) || (String(checked.inputValue) != '0')) {
																			this.NeonatalSurvey_data.NeonatalSurveyParam.СhinShape = checked.inputValue;									
																			this.changedDatas = true;
																		}
																	}
																	field.FromInterface = true;
																}.createDelegate(this)
															}
														})
													]
												},
												{
													allowBlank: true,
													fieldLabel: '',
													labelSeparator: '',
													name: 'swENSEW_СhinShapeTxt',
													id: 'swENSEW_СhinShapeTxt',
													width: 550,
													value:'',
													style: 'margin-top: 4px;  margin-left: 12px; ',
													xtype: 'textfield',
													listeners:{
														'change':function (field, newValue, oldValue) {
															this.NeonatalSurvey_data.NeonatalSurveyParam.СhinShapeTxt = newValue;
															this.changedDatas = true;
														}.createDelegate(this)
													}
												}	
											]
										}
									]
								},
								//Асимметрия лица 	
								{
									fieldLabel: 'Асимметрия лица',
									labelSeparator: '',
									name: 'FacialAsymmetry',
									id: 'swENSEW_FacialAsymmetry',
									xtype: 'checkbox',
									checked: false,
									listeners: {
										'check': function(chb, checked ) {
											if ((this.NeonatalSurvey_data.NeonatalSurveyParam.FacialAsymmetry) || (checked)) { 
												this.NeonatalSurvey_data.NeonatalSurveyParam.FacialAsymmetry = checked ? 2 : 1;									
												this.changedDatas = true;
											}
										}.createDelegate(this)
									}		
								},
								//Глаза 
								{									
									layout:'column',
									id: 'swENSEW_EyesOnFace_Panel',
									style: 'margin-top: 10px;  margin-left: 10px; margin-bottom: 10px;',
									items:[
										//Глаза 
										{									
											layout:'column',
											width: 170,
											items:[
												{xtype:'label',text: 'Глаза',style: 'font-size: 10pt;  margin-left: 2pt; margin-top: 2pt; margin-bottom: 2px'},
												{									
													layout:'form',
													labelWidth:1,
													style: 'margin-top: 4px;  margin-left: 4px; margin-bottom: 2px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
													items:[
														new Ext.form.RadioGroup({
															id:'swENSEW_EyesOnFace1',
															labelSeparator: '',
															vertical: true,
															columns: 1,
															FromInterface: true,
															items: [
																{boxLabel: '---', name: 'EyesOnFace1', inputValue: 0, width: 50}, 
																{boxLabel: 'открывает', name: 'EyesOnFace1', inputValue: 1, width: 120}, 
																{boxLabel: 'закрывает', name: 'EyesOnFace1', inputValue: 2, width: 120},
																{boxLabel: 'не закрывает', name: 'EyesOnFace1', inputValue: 3, width: 120},
																{boxLabel: 'не открывает', name: 'EyesOnFace1', inputValue: 4, width: 120}
															],
															listeners: {
																'change': function(field, checked) {
																	if(field.FromInterface) win.findById('swENSEW_EyesOnFace_Panel').change_handler(field, checked);
																	field.FromInterface = true;
																}
															}
														})	
													]
												}
											]
										},
										//стороны
										{									
											layout:'form',
											width: 270,
											items:[
												//не закрывает
												{									
													layout:'form',
													labelWidth:1,
													style: 'margin-top: 80px;  margin-left: 0px; margin-bottom: 0px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
													items:[
														new Ext.form.RadioGroup({
															id:'swENSEW_EyesOnFace2',
															labelSeparator: '',
															vertical: true,
															columns: 4,
															FromInterface: true,
															items: [
																{boxLabel: '---', name: 'EyesOnFace2', inputValue: 0, width: 50, hidden: true}, 
																{boxLabel: 'справа', name: 'EyesOnFace2', inputValue: 1, width: 70}, 
																{boxLabel: 'слева', name: 'EyesOnFace2', inputValue: 2, width: 70},
																{boxLabel: 'с обеих сторон', name: 'EyesOnFace2', inputValue: 3, width: 110}
															],
															listeners: {
																'change': function(field, checked) {
																	if(field.FromInterface) win.findById('swENSEW_EyesOnFace_Panel').change_handler(field, checked);
																	field.FromInterface = true;
																}
															}
														})	
													]
												},
												//не открывает 
												{									
													layout:'form',
													labelWidth:1,
													style: 'margin-top: 0px;  margin-left: 0px; margin-bottom: 0px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
													items:[
														new Ext.form.RadioGroup({
															id:'swENSEW_EyesOnFace3',
															labelSeparator: '',
															vertical: true,
															columns: 4,
															FromInterface: true,
															items: [
																{boxLabel: '---', name: 'EyesOnFace3', inputValue: 0, width: 50, hidden: true}, 
																{boxLabel: 'справа', name: 'EyesOnFace3', inputValue: 1, width: 70}, 
																{boxLabel: 'слева', name: 'EyesOnFace3', inputValue: 2, width: 70},
																{boxLabel: 'с обеих сторон', name: 'EyesOnFace3', inputValue: 3, width: 110}
															],
															listeners: {
																'change': function(field, checked) {
																	if(field.FromInterface) win.findById('swENSEW_EyesOnFace_Panel').change_handler(field, checked);
																	field.FromInterface = true;
																}
															}
														})	
													]
												}
											]
										}
									],
									change_handler: function(field, checked) {
										if(checked){
											var EyesOnFace = (this.NeonatalSurvey_data.NeonatalSurveyParam.EyesOnFace) ? this.NeonatalSurvey_data.NeonatalSurveyParam.EyesOnFace : '000';
											var position = parseInt(field.id.substr(field.id.length - 1, 1));
											var value = checked.inputValue;
											//если меняется выбранный в первой группе то обнуляются вторая и третья
											if (position == 1) {
												this.findById('swENSEW_EyesOnFace2').items.items[0].setValue(true);		
												for (var i = 1; i < this.findById('swENSEW_EyesOnFace2').items.items.length; i++  ) this.findById('swENSEW_EyesOnFace2').items.items[i].setValue(false);						
												this.findById('swENSEW_EyesOnFace3').items.items[0].setValue(true);		
												for (var i = 1; i < this.findById('swENSEW_EyesOnFace3').items.items.length; i++  ) this.findById('swENSEW_EyesOnFace3').items.items[i].setValue(false);
												this.findById('swENSEW_EyesOnFace2').disable();						
												this.findById('swENSEW_EyesOnFace3').disable();	
												if(value == 3)	this.findById('swENSEW_EyesOnFace2').enable();	
												else if (value == 4)	this.findById('swENSEW_EyesOnFace3').enable();			
											}
											EyesOnFace = EyesOnFace.split("");
											EyesOnFace[position - 1] = value;
											EyesOnFace = EyesOnFace.join("");
											if (!Ext.isEmpty(this.NeonatalSurvey_data.NeonatalSurveyParam.EyesOnFace) || (EyesOnFace != '000')) 
												this.NeonatalSurvey_data.NeonatalSurveyParam.EyesOnFace = EyesOnFace;									
											this.changedDatas = true;
										}
									}.createDelegate(this)
								},
								//Гипертелоризм 	
								{
									fieldLabel: 'Гипертелоризм',
									labelSeparator: '',
									name: 'Hypertelorism',
									id: 'swENSEW_Hypertelorism',
									xtype: 'checkbox',
									checked: false,
									listeners: {
										'check': function(chb, checked ) {
											if ((this.NeonatalSurvey_data.NeonatalSurveyParam.Hypertelorism) || (checked)) {
												this.NeonatalSurvey_data.NeonatalSurveyParam.Hypertelorism = checked ? 2 : 1;									
												this.changedDatas = true;
											}
										}.createDelegate(this)
									}		
								},
								//Губы 
								{									
									layout:'column',
									style: 'margin-top: 10px;  margin-bottom: 10px;',
									items:[
										//Движения рта и губ
										{									
											layout:'column',
											width: 310,
											items:[
												{xtype:'label',text: 'Движения рта и губ',style: 'font-size: 10pt;  margin-left: 12pt; margin-top: 2pt; margin-bottom: 2px; margin-right: 12px'},
												{									
													layout:'form',
													labelWidth:1,
													FromInterface: true,
													style: 'margin-top: 4px;  margin-left: 4px; margin-bottom: 1px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
													items:[
														new Ext.form.RadioGroup({
															id:'swENSEW_MouthLipsMovement',
															labelSeparator: '',
															vertical: true,
															columns: 1,
															items: [
																{boxLabel: '---', name: 'MouthLipsMovement', inputValue: 0, width: 50}, 
																{boxLabel: 'симметричные', name: 'MouthLipsMovement', inputValue: 1, width: 110}, 
																{boxLabel: 'несимметричные', name: 'MouthLipsMovement', inputValue: 2, width: 130}
															],
															listeners: {
																'change': function(field, checked) {
																	if((checked) && field.FromInterface){
																		if ((this.NeonatalSurvey_data.NeonatalSurveyParam.MouthLipsMovement) || (String(checked.inputValue) != '0')) {
																			this.NeonatalSurvey_data.NeonatalSurveyParam.MouthLipsMovement = checked.inputValue;									
																			this.changedDatas = true;
																		}
																	}
																	field.FromInterface = true;
																}.createDelegate(this)
															}
														})
													]
												}
											]
										},
										//Опущение угла рта, Отсутствие носогубной складки 
										{									
											layout:'form',
											width: 700,
											items:[
												//Опущение угла рта
												{									
													layout:'column',
													items:[				
														{									
															layout:'form',
															labelWidth:200,
															style: 'margin-top: 3px;',
															items:[
																{
																	fieldLabel: 'Опущение угла рта',
																	labelSeparator: '',
																	name: 'LowMouthСorner',
																	id: 'swENSEW_LowMouthСorner',
																	xtype: 'checkbox',
																	checked: false,
																	listeners: {
																		'check': function(chb, checked ) {
																			if ((this.NeonatalSurvey_data.NeonatalSurveyParam.LowMouthСorner) || (checked)) 
																				this.NeonatalSurvey_data.NeonatalSurveyParam.LowMouthСorner = checked ? 2 : 1;
																			this.findById('swENSEW_LowMouthСornerLoc').setDisabled(!checked);  //BOB - 06.05.2020
																			if (!checked) {
																				this.findById('swENSEW_LowMouthСornerLoc').items.items[0].setValue(true);
																				for (var i = 1; i < this.findById('swENSEW_LowMouthСornerLoc').items.items.length; i++  ) this.findById('swENSEW_LowMouthСornerLoc').items.items[i].setValue(false);						
																			}		
																			this.changedDatas = true;
																		}.createDelegate(this)
																	}		
																}
															]
														},						
														{									
															layout:'form',
															labelWidth:1,
															style: 'margin-top: 0px;  margin-left: 4px; margin-bottom: 0px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
															items:[
																new Ext.form.RadioGroup({
																	id:'swENSEW_LowMouthСornerLoc',
																	labelSeparator: '',
																	vertical: true,
																	columns: 4,
																	FromInterface: true,
																	items: [
																		{boxLabel: '---', name: 'LowMouthСornerLoc', inputValue: 0, width: 50, hidden: true}, 
																		{boxLabel: 'справа', name: 'LowMouthСornerLoc', inputValue: 1, width: 70}, 
																		{boxLabel: 'слева', name: 'LowMouthСornerLoc', inputValue: 2, width: 70},
																		{boxLabel: 'с обеих сторон', name: 'LowMouthСornerLoc', inputValue: 3, width: 110}
																	],
																	listeners: {
																		'change': function(field, checked) {
																			if((checked) && field.FromInterface){
																				if ((this.NeonatalSurvey_data.NeonatalSurveyParam.LowMouthСornerLoc) || (String(checked.inputValue) != '0')) {
																					this.NeonatalSurvey_data.NeonatalSurveyParam.LowMouthСornerLoc = checked.inputValue;									
																					this.changedDatas = true;																					
																				}
																			}
																			field.FromInterface = true;
																		}.createDelegate(this)
																	}
																})	
															]
														}
													]
												},
												//Отсутствие носогубной складки 
												{									
													layout:'column',
													items:[
														{									
															layout:'form',
															labelWidth:200,
															style: 'margin-top: 3px;',
															items:[

																{
																	fieldLabel: 'Отсутствие носогубной складки',
																	labelSeparator: '',
																	name: 'LackNasoLabFold',
																	id: 'swENSEW_LackNasoLabFold',
																	xtype: 'checkbox',
																	checked: false,
																	listeners: {
																		'check': function(chb, checked ) {
																			if ((this.NeonatalSurvey_data.NeonatalSurveyParam.LackNasoLabFold) || (checked)) 
																				this.NeonatalSurvey_data.NeonatalSurveyParam.LackNasoLabFold = checked ? 2 : 1;
																			this.findById('swENSEW_LackNasoLabFoldLoc').setDisabled(!checked);  //BOB - 06.05.2020
																			if (!checked) {
																				this.findById('swENSEW_LackNasoLabFoldLoc').items.items[0].setValue(true);
																				for (var i = 1; i < this.findById('swENSEW_LackNasoLabFoldLoc').items.items.length; i++  ) this.findById('swENSEW_LackNasoLabFoldLoc').items.items[i].setValue(false);						
																			}	
																			this.changedDatas = true;
																		}.createDelegate(this)
																	}		
																}
															]
														},
														{									
															layout:'form',
															labelWidth:1,
															style: 'margin-top: 0px;  margin-left: 4px; margin-bottom: 0px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
															items:[
																new Ext.form.RadioGroup({
																	id:'swENSEW_LackNasoLabFoldLoc',
																	labelSeparator: '',
																	vertical: true,
																	columns: 4,
																	FromInterface: true,
																	items: [
																		{boxLabel: '---', name: 'LackNasoLabFoldLoc', inputValue: 0, width: 50, hidden: true}, 
																		{boxLabel: 'справа', name: 'LackNasoLabFoldLoc', inputValue: 1, width: 70}, 
																		{boxLabel: 'слева', name: 'LackNasoLabFoldLoc', inputValue: 2, width: 70},
																		{boxLabel: 'с обеих сторон', name: 'LackNasoLabFoldLoc', inputValue: 3, width: 110}
																	],
																	listeners: {
																		'change': function(field, checked) {
																			if((checked) && field.FromInterface){
																				if ((this.NeonatalSurvey_data.NeonatalSurveyParam.LackNasoLabFoldLoc) || (String(checked.inputValue) != '0')) {
																					this.NeonatalSurvey_data.NeonatalSurveyParam.LackNasoLabFoldLoc = checked.inputValue;									
																					this.changedDatas = true;
																				}
																			}
																			field.FromInterface = true;
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
								},
								//Слюнотечение 	
								{
									fieldLabel: 'Слюнотечение',
									labelSeparator: '',
									name: 'HyperSalivation',
									id: 'swENSEW_HyperSalivation',
									xtype: 'checkbox',
									checked: false,
									listeners: {
										'check': function(chb, checked ) {
											if ((this.NeonatalSurvey_data.NeonatalSurveyParam.HyperSalivation) || (checked)) {
												this.NeonatalSurvey_data.NeonatalSurveyParam.HyperSalivation = checked ? 2 : 1;									
												this.changedDatas = true;
											}
										}.createDelegate(this)
									}		
								}
							]	
						},
						//Панель Уши    
						{
							id: 'swENSEW_Ears_Panel',
							xtype: 'fieldset',
							autoHeight: true,
							title: langs('Уши'),
							collapsible: true,
							layout: 'form',
							labelWidth:170,
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
								//Особенности
								{									
									layout:'column',
									style: 'margin-bottom: 0px;',
									items:[
										{									
											layout:'form',
											labelWidth:150,
											style: 'margin-top: 3px;',
											items:[
												{
													fieldLabel: 'Уши без особенностей',
													labelSeparator: '',
													name: 'EarsNorm',
													id: 'swENSEW_EarsNorm',
													xtype: 'checkbox',
													checked: false,
													listeners: {
														'check': function(chb, checked ) {
															if ((this.NeonatalSurvey_data.NeonatalSurveyParam.EarsNorm) || (checked)) {
																this.NeonatalSurvey_data.NeonatalSurveyParam.EarsNorm = checked ? 2 : 1;		
																this.changedDatas = true;
															}
														}.createDelegate(this)
													}		
												}
											]
										},						
										{									
											layout:'form',
											labelWidth:170,
											style: 'margin-top: 3px;',
											items:[
												{
													fieldLabel: 'Уши низко посаженные',
													labelSeparator: '',
													name: 'EarsLowSet',
													id: 'swENSEW_EarsLowSet',
													xtype: 'checkbox',
													checked: false,
													listeners: {
														'check': function(chb, checked ) {
															if ((this.NeonatalSurvey_data.NeonatalSurveyParam.EarsLowSet) || (checked)) { 
																this.NeonatalSurvey_data.NeonatalSurveyParam.EarsLowSet = checked ? 2 : 1;		
																this.changedDatas = true;
															}
														}.createDelegate(this)
													}		
												}
											]
										},						
										{									
											layout:'form',
											labelWidth:100,
											style: 'margin-top: 3px;',
											items:[
												{
													fieldLabel: 'Микротия',
													labelSeparator: '',
													name: 'EarsMicrotia',
													id: 'swENSEW_EarsMicrotia',
													xtype: 'checkbox',
													checked: false,
													listeners: {
														'check': function(chb, checked ) {
															if ((this.NeonatalSurvey_data.NeonatalSurveyParam.EarsMicrotia) || (checked)) { 
																this.NeonatalSurvey_data.NeonatalSurveyParam.EarsMicrotia = checked ? 2 : 1;		
																this.changedDatas = true;
															}
														}.createDelegate(this)
													}		
												}
											]
										},						
										{									
											layout:'form',
											labelWidth:120,
											style: 'margin-top: 3px;',
											items:[
												{
													fieldLabel: 'Уши волосатые',
													labelSeparator: '',
													name: 'EarsHairy',
													id: 'swENSEW_EarsHairy',
													xtype: 'checkbox',
													checked: false,
													listeners: {
														'check': function(chb, checked ) {
															if ((this.NeonatalSurvey_data.NeonatalSurveyParam.EarsHairy) || (checked)) { 
																this.NeonatalSurvey_data.NeonatalSurveyParam.EarsHairy = checked ? 2 : 1;		
																this.changedDatas = true;
															}
														}.createDelegate(this)
													}		
												}
											]
										},
										{									
											layout:'form',
											labelWidth:250,
											style: 'margin-top: 3px;',
											items:[
												{
													fieldLabel: 'Преаурикулярные кожные выросты',
													labelSeparator: '',
													name: 'Preauricular',
													id: 'swENSEW_Preauricular',
													xtype: 'checkbox',
													checked: false,
													listeners: {
														'check': function(chb, checked ) {
															if ((this.NeonatalSurvey_data.NeonatalSurveyParam.Preauricular) || (checked)) 
																this.NeonatalSurvey_data.NeonatalSurveyParam.Preauricular = checked ? 2 : 1;
															this.findById('swENSEW_PreauricularLoc').setDisabled(!checked);  //BOB - 06.05.2020
															if (!checked) {
																this.findById('swENSEW_PreauricularLoc').items.items[0].setValue(true);
																for (var i = 1; i < this.findById('swENSEW_PreauricularLoc').items.items.length; i++  ) this.findById('swENSEW_PreauricularLoc').items.items[i].setValue(false);						
															}	
															this.changedDatas = true;
														}.createDelegate(this)
													}		
												}
											]
										},
										{									
											layout:'form',
											labelWidth:1,
											style: 'margin-top: 0px;  margin-left: 4px; margin-bottom: 0px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											items:[
												new Ext.form.RadioGroup({
													id:'swENSEW_PreauricularLoc',
													labelSeparator: '',
													vertical: true,
													columns: 4,
													FromInterface: true,
													items: [
														{boxLabel: '---', name: 'PreauricularLoc', inputValue: 0, width: 50, hidden: true}, 
														{boxLabel: 'справа', name: 'PreauricularLoc', inputValue: 1, width: 70}, 
														{boxLabel: 'слева', name: 'PreauricularLoc', inputValue: 2, width: 70},
														{boxLabel: 'с обеих сторон', name: 'PreauricularLoc', inputValue: 3, width: 110}
													],
													listeners: {
														'change': function(field, checked) {
															if((checked) && field.FromInterface){
																if ((this.NeonatalSurvey_data.NeonatalSurveyParam.PreauricularLoc) || (String(checked.inputValue) != '0')) {
																	this.NeonatalSurvey_data.NeonatalSurveyParam.PreauricularLoc = checked.inputValue;									
																	this.changedDatas = true;
																}
															}
															field.FromInterface = true;
														}.createDelegate(this)
													}
												})	
											]
										}
									]
								},
								//Другие особенности
								{
									allowBlank: true,
									fieldLabel: 'Другие особенности',
									labelSeparator: '',
									name: 'swENSEW_EarsOtherFeat',
									id: 'swENSEW_EarsOtherFeat',
									width: 1050,
									value:'',
									style: 'margin-top: 4px;  margin-left: 12px; ',
									xtype: 'textfield',
									listeners:{
										'change':function (field, newValue, oldValue) {
											this.NeonatalSurvey_data.NeonatalSurveyParam.EarsOtherFeat = newValue;
											this.changedDatas = true;
										}.createDelegate(this)
									}
								},
								//Вздрагивание на звук
								{
									fieldLabel: 'Вздрагивание на звук',
									labelSeparator: '',
									name: 'FlinchAtSound',
									id: 'swENSEW_FlinchAtSound',
									xtype: 'checkbox',
									checked: false,
									listeners: {
										'check': function(chb, checked ) {
											if ((this.NeonatalSurvey_data.NeonatalSurveyParam.FlinchAtSound) || (checked)) { 
												this.NeonatalSurvey_data.NeonatalSurveyParam.FlinchAtSound = checked ? 2 : 1;		
												this.changedDatas = true;
											}
										}.createDelegate(this)
									}		
								}
							]	
						},
						//Панель Глаза    
						{
							id: 'swENSEW_eyes_Panel',
							xtype: 'fieldset',
							autoHeight: true,
							title: langs('Глаза'),
							collapsible: true,
							layout: 'form',
							labelWidth:170,
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
								//Склера
								{									
									layout:'column',
									style: 'margin-bottom: 0px;',
									items:[
										//Цвет склеры
										{xtype:'label',text: 'Цвет склеры',style: 'font-size: 10pt;  margin-left: 12pt; margin-top: 2pt; margin-bottom: 2px; margin-right: 12px'},
										{									
											layout:'form',
											labelWidth:1,
											style: 'margin-top: 4px;  margin-left: 4px; margin-bottom: 1px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											items:[
												new Ext.form.RadioGroup({
													id:'swENSEW_ScleraColor',
													labelSeparator: '',
													vertical: true,
													columns: 3,
													FromInterface: true,
													items: [
														{boxLabel: '---', name: 'ScleraColor', inputValue: 0, width: 50}, 
														{boxLabel: 'белая', name: 'ScleraColor', inputValue: 1, width: 80}, 
														{boxLabel: 'синяя', name: 'ScleraColor', inputValue: 2, width: 80}
													],
													listeners: {
														'change': function(field, checked) {
															if((checked) && field.FromInterface){
																if ((this.NeonatalSurvey_data.NeonatalSurveyParam.ScleraColor) || (String(checked.inputValue) != '0')) {
																	this.NeonatalSurvey_data.NeonatalSurveyParam.ScleraColor = checked.inputValue;									
																	this.changedDatas = true;
																}
															}
															field.FromInterface = true;
														}.createDelegate(this)
													}
												})
											]
										},
										{									
											layout:'form',
											labelWidth:150,
											style: 'margin-top: 3px;',
											items:[
												{
													fieldLabel: 'Пятна Брашвилда',
													labelSeparator: '',
													name: 'BrushfieldSpots',
													id: 'swENSEW_BrushfieldSpots',
													xtype: 'checkbox',
													checked: false,
													listeners: {
														'check': function(chb, checked ) {
															if ((this.NeonatalSurvey_data.NeonatalSurveyParam.BrushfieldSpots) || (checked)) { 
																this.NeonatalSurvey_data.NeonatalSurveyParam.BrushfieldSpots = checked ? 2 : 1;		
																this.changedDatas = true;
															}
														}.createDelegate(this)
													}		
												}
											]
										},						
										{									
											layout:'form',
											labelWidth:250,
											style: 'margin-top: 3px;',
											items:[
												{
													fieldLabel: 'Подконьюнктивальные кровоизлияния',
													labelSeparator: '',
													name: 'SubconjunctHemor',
													id: 'swENSEW_SubconjunctHemor',
													xtype: 'checkbox',
													checked: false,
													listeners: {
														'check': function(chb, checked ) {
															if ((this.NeonatalSurvey_data.NeonatalSurveyParam.SubconjunctHemor) || (checked)) {
																this.NeonatalSurvey_data.NeonatalSurveyParam.SubconjunctHemor = checked ? 2 : 1;		
																this.changedDatas = true;
															}
														}.createDelegate(this)
													}		
												}
											]
										},						
										{									
											layout:'form',
											labelWidth:150,
											style: 'margin-top: 3px;',
											items:[
												{
													fieldLabel: 'Эпикантные складки',
													labelSeparator: '',
													name: 'RacyPleats',
													id: 'swENSEW_RacyPleats',
													xtype: 'checkbox',
													checked: false,
													listeners: {
														'check': function(chb, checked ) {
															if ((this.NeonatalSurvey_data.NeonatalSurveyParam.RacyPleats) || (checked)) { 
																this.NeonatalSurvey_data.NeonatalSurveyParam.RacyPleats = checked ? 2 : 1;		
																this.changedDatas = true;
															}
														}.createDelegate(this)
													}		
												}
											]
										}						
									]
								},
								// Нистагм
								{									
									layout:'column',
									id: 'swENSEW_Nystagm_Panel',
									items:[
										{xtype:'label',text: 'Нистагм',style: 'font-size: 10pt;  margin-left: 12pt; margin-top: 2pt; margin-bottom: 2px'},
										{									
											layout:'form',
											labelWidth:1,
											style: 'margin-top: 4px;  margin-left: 4px; margin-bottom: 2px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											items:[
												new Ext.form.RadioGroup({
													id:'swENSEW_Nystagm1',
													labelSeparator: '',
													vertical: true,
													columns: 3,
													FromInterface: true,
													items: [
														{boxLabel: 'нет', name: 'Nystagm1', inputValue: 0, width: 50}, 
														{boxLabel: 'стойкий', name: 'Nystagm1', inputValue: 1, width: 130}, 
														{boxLabel: 'нестойкий', name: 'Nystagm1', inputValue: 2, width: 130}
													],
													listeners: {
														'change': function(field, checked) {
															win.findById('swENSEW_Nystagm_Panel').change_handler(field, checked);
														}
													}
												})	
											]
										},
										{									
											layout:'form',
											labelWidth:1,
											style: 'margin-top: 4px;  margin-left: 4px; margin-bottom: 2px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											items:[
												new Ext.form.RadioGroup({
													id:'swENSEW_Nystagm2',
													labelSeparator: '',
													vertical: true,
													columns: 4,
													FromInterface: true,
													items: [
														{boxLabel: '---', name: 'Nystagm2', inputValue: 0, width: 50, hidden: true}, 
														{boxLabel: 'горизонтальный', name: 'Nystagm2', inputValue: 1, width: 130}, 
														{boxLabel: 'вертикальный', name: 'Nystagm2', inputValue: 2, width: 130},
														{boxLabel: 'смешанный', name: 'Nystagm2', inputValue: 3, width: 130}
													],
													listeners: {
														'change': function(field, checked) {
															win.findById('swENSEW_Nystagm_Panel').change_handler(field, checked);
														}
													}
												})	
											]
										}
									],
									change_handler: function(field, checked) {
										if((checked) && field.FromInterface){
											var Nystagm = (this.NeonatalSurvey_data.NeonatalSurveyParam.Nystagm) ? this.NeonatalSurvey_data.NeonatalSurveyParam.Nystagm : '00';
											var position = parseInt(field.id.substr(field.id.length - 1, 1));
											var value = checked.inputValue;
											//если меняется выбранный в первой группе то обнуляются вторая
											if (position == 1) {
												this.findById('swENSEW_Nystagm2').items.items[0].setValue(true);		
												for (var i = 1; i < this.findById('swENSEW_Nystagm2').items.items.length; i++  ) this.findById('swENSEW_Nystagm2').items.items[i].setValue(false);						
												this.findById('swENSEW_Nystagm2').disable();						
												if(value > 0) this.findById('swENSEW_Nystagm2').enable();	
											}
											Nystagm = Nystagm.split("");
											Nystagm[position - 1] = value;
											Nystagm = Nystagm.join("");
											if (!Ext.isEmpty(this.NeonatalSurvey_data.NeonatalSurveyParam.Nystagm) || (Nystagm != '00')) 
												this.NeonatalSurvey_data.NeonatalSurveyParam.Nystagm = Nystagm;									
											this.changedDatas = true;
										}
										field.FromInterface = true;
									}.createDelegate(this)
		
								},
								//Птоз , Лейкория
								{									
									layout:'column',
									style: 'margin-bottom: 0px;',
									items:[
										//Птоз 
										{xtype:'label',text: 'Птоз ',style: 'font-size: 10pt;  margin-left: 12pt; margin-top: 2pt; margin-bottom: 2px; margin-right: 12px'},
										{									
											layout:'form',
											labelWidth:1,
											style: 'margin-top: 4px;  margin-left: 4px; margin-bottom: 1px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											items:[
												new Ext.form.RadioGroup({
													id:'swENSEW_Ptosis',
													labelSeparator: '',
													vertical: true,
													columns: 4,
													FromInterface: true,
													items: [
														{boxLabel: 'нет', name: 'Ptosis', inputValue: 0, width: 50}, 
														{boxLabel: 'справа', name: 'Ptosis', inputValue: 1, width: 80}, 
														{boxLabel: 'слева', name: 'Ptosis', inputValue: 2, width: 80},
														{boxLabel: 'двусторонний', name: 'Ptosis', inputValue: 3, width: 130}
													],
													listeners: {
														'change': function(field, checked) {
															if((checked) && field.FromInterface){
																if ((this.NeonatalSurvey_data.NeonatalSurveyParam.Ptosis) || (String(checked.inputValue) != '0')) {
																	this.NeonatalSurvey_data.NeonatalSurveyParam.Ptosis = checked.inputValue;									
																	this.changedDatas = true;
																}
															}
															field.FromInterface = true;
														}.createDelegate(this)
													}
												})
											]
										},
										//Лейкория 
										{xtype:'label',text: 'Лейкория',style: 'font-size: 10pt;  margin-left: 12pt; margin-top: 2pt; margin-bottom: 2px; margin-right: 12px'},
										{									
											layout:'form',
											labelWidth:1,
											style: 'margin-top: 4px;  margin-left: 4px; margin-bottom: 1px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											items:[
												new Ext.form.RadioGroup({
													id:'swENSEW_Leucorrhea',
													labelSeparator: '',
													vertical: true,
													columns: 4,
													FromInterface: true,
													items: [
														{boxLabel: 'нет', name: 'Leucorrhea', inputValue: 0, width: 50}, 
														{boxLabel: 'справа', name: 'Leucorrhea', inputValue: 1, width: 80}, 
														{boxLabel: 'слева', name: 'Leucorrhea', inputValue: 2, width: 80},
														{boxLabel: 'двусторонняя', name: 'Leucorrhea', inputValue: 3, width: 130}
													],
													listeners: {
														'change': function(field, checked) {
															if((checked) && field.FromInterface){
																if ((this.NeonatalSurvey_data.NeonatalSurveyParam.Leucorrhea) || (String(checked.inputValue) != '0')) {
																	this.NeonatalSurvey_data.NeonatalSurveyParam.Leucorrhea = checked.inputValue;									
																	this.changedDatas = true;
																}
															}
															field.FromInterface = true;
														}.createDelegate(this)
													}
												})
											]
										}
									]
								},
								// Коньюктивит
								{									
									layout:'column',
									id: 'swENSEW_Conjunctivitis_Panel',
									items:[
										{xtype:'label',text: 'Коньюктивит: локализация',style: 'font-size: 10pt;  margin-left: 12pt; margin-top: 2pt; margin-bottom: 2px'},
										{									
											layout:'form',
											labelWidth:1,
											style: 'margin-top: 4px;  margin-left: 4px; margin-bottom: 2px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											items:[
												new Ext.form.RadioGroup({
													id:'swENSEW_Conjunctivitis1',
													labelSeparator: '',
													vertical: true,
													columns: 1,
													FromInterface: true,
													items: [
														{boxLabel: '---', name: 'Conjunctivitis1', inputValue: 0, width: 50}, 
														{boxLabel: 'односторонний справа', name: 'Conjunctivitis1', inputValue: 1, width: 200}, 
														{boxLabel: 'односторонний слева', name: 'Conjunctivitis1', inputValue: 2, width: 200},
														{boxLabel: 'двусторонний', name: 'Conjunctivitis1', inputValue: 3, width: 200}
													],
													listeners: {
														'change': function(field, checked) {
															win.findById('swENSEW_Conjunctivitis_Panel').change_handler(field, checked);
														}
													}
												})	
											]
										},
										{xtype:'label',text: 'Характер отделяемого из глаз',style: 'font-size: 10pt;  margin-left: 2pt; margin-top: 2pt; margin-bottom: 2px'},
										{									
											layout:'form',
											labelWidth:1,
											style: 'margin-top: 4px;  margin-left: 4px; margin-bottom: 2px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											items:[
												new Ext.form.RadioGroup({
													id:'swENSEW_Conjunctivitis2',
													labelSeparator: '',
													vertical: true,
													columns: 1,
													FromInterface: true,
													items: [
														{boxLabel: '---', name: 'Conjunctivitis2', inputValue: 0, width: 50}, 
														{boxLabel: 'серозное', name: 'Conjunctivitis2', inputValue: 1, width: 130}, 
														{boxLabel: 'серозно-геморрагическое', name: 'Conjunctivitis2', inputValue: 2, width: 200},
														{boxLabel: 'слизистое', name: 'Conjunctivitis2', inputValue: 3, width: 130},
														{boxLabel: 'слизисто-гнойное', name: 'Conjunctivitis2', inputValue: 4, width: 200},
														{boxLabel: 'гнойное', name: 'Conjunctivitis2', inputValue: 5, width: 130}
													],
													listeners: {
														'change': function(field, checked) {
															win.findById('swENSEW_Conjunctivitis_Panel').change_handler(field, checked);
														}
													}
												})	
											]
										}
									],
									change_handler: function(field, checked) {
										if((checked) && field.FromInterface){
											var Conjunctivitis = (this.NeonatalSurvey_data.NeonatalSurveyParam.Conjunctivitis) ? this.NeonatalSurvey_data.NeonatalSurveyParam.Conjunctivitis : '00';
											var position = parseInt(field.id.substr(field.id.length - 1, 1));
											var value = checked.inputValue;
											Conjunctivitis = Conjunctivitis.split("");
											Conjunctivitis[position - 1] = value;
											Conjunctivitis = Conjunctivitis.join("");
											if (!Ext.isEmpty(this.NeonatalSurvey_data.NeonatalSurveyParam.Conjunctivitis) || (Conjunctivitis != '00')) 
												this.NeonatalSurvey_data.NeonatalSurveyParam.Conjunctivitis = Conjunctivitis;									
											this.changedDatas = true;
										}
										field.FromInterface = true;
									}.createDelegate(this)
		
								},
								//Обтурация слезного протока
								{									
									layout:'column',
									items:[
										{
											layout:'form',
											labelWidth: 180,
											items:[
												{
													fieldLabel: 'Обтурация слезного протока',
													labelSeparator: '',
													name: 'ObturatLacrimDuct',
													id: 'swENSEW_ObturatLacrimDuct',
													xtype: 'checkbox',
													checked: false,
													listeners: {
														'check': function(chb, checked ) {
															if ((this.NeonatalSurvey_data.NeonatalSurveyParam.ObturatLacrimDuct) || (checked)) { 
																this.NeonatalSurvey_data.NeonatalSurveyParam.ObturatLacrimDuct = checked ? 2 : 1;									
																this.changedDatas = true;
																this.findById('swENSEW_ObturatLacrimDuctTxt').setDisabled(!checked);  //BOB - 06.05.2020
																if (!checked) {
																	this.findById('swENSEW_ObturatLacrimDuctTxt').setValue('');
																	delete this.NeonatalSurvey_data.NeonatalSurveyParam.ObturatLacrimDuctTxt;
																}
															}
														}.createDelegate(this)
													}		
												}
											]
										},		
										{
											allowBlank: true,
											fieldLabel: '',
											labelSeparator: '',
											name: 'swENSEW_ObturatLacrimDuctTxt',
											id: 'swENSEW_ObturatLacrimDuctTxt',
											width: 850,
											value:'',
											style: 'margin-top: 1px;  margin-left: 4px; ',
											xtype: 'textfield',
											listeners:{
												'change':function (field, newValue, oldValue) {
													this.NeonatalSurvey_data.NeonatalSurveyParam.ObturatLacrimDuctTxt = newValue;
													this.changedDatas = true;
												}.createDelegate(this)
											}
										}
									]
								}		
							]	
						},
						//Панель Нос    
						{
							id: 'swENSEW_nose_Panel',
							xtype: 'fieldset',
							autoHeight: true,
							title: langs('Нос'),
							collapsible: true,
							layout: 'column',
							labelWidth:170,
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
								//Атрезия носовых ходов
								{xtype:'label',text: 'Атрезия носовых ходов ',style: 'font-size: 10pt;  margin-left: 12pt; margin-top: 2pt; margin-bottom: 2px; margin-right: 12px'},
								{									
									layout:'form',
									labelWidth:1,
									style: 'margin-top: 4px;  margin-left: 4px; margin-bottom: 1px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
									items:[
										new Ext.form.RadioGroup({
											id:'swENSEW_NasalPassAtresia',
											labelSeparator: '',
											vertical: true,
											columns: 4,
											FromInterface: true,
											items: [
												{boxLabel: 'нет', name: 'NasalPassAtresia', inputValue: 0, width: 50}, 
												{boxLabel: 'односторонняя справа', name: 'NasalPassAtresia', inputValue: 1, width: 200}, 
												{boxLabel: 'односторонняя слева', name: 'NasalPassAtresia', inputValue: 2, width: 200},
												{boxLabel: 'двусторонняя', name: 'NasalPassAtresia', inputValue: 3, width: 130}
											],
											listeners: {
												'change': function(field, checked) {
													if((checked) && field.FromInterface){
														if ((this.NeonatalSurvey_data.NeonatalSurveyParam.NasalPassAtresia) || (String(checked.inputValue) != '0')) {
															this.NeonatalSurvey_data.NeonatalSurveyParam.NasalPassAtresia = checked.inputValue;									
															this.changedDatas = true;
														}
													}
													field.FromInterface = true;
												}.createDelegate(this)
											}
										})
									]
								},
								{									
									layout:'form',
									labelWidth:100,
									style: 'margin-top: 3px;',
									items:[
										{
											fieldLabel: 'Сопение',
											labelSeparator: '',
											name: 'Sniff',
											id: 'swENSEW_Sniff',
											xtype: 'checkbox',
											checked: false,
											listeners: {
												'check': function(chb, checked ) {
													if ((this.NeonatalSurvey_data.NeonatalSurveyParam.Sniff) || (checked)) {
														this.NeonatalSurvey_data.NeonatalSurveyParam.Sniff = checked ? 2 : 1;		
														this.changedDatas = true;
													}
												}.createDelegate(this)
											}		
										}
									]
								},						
								{									
									layout:'form',
									labelWidth:150,
									style: 'margin-top: 3px;',
									items:[
										{
											fieldLabel: 'Выделения из носа',
											labelSeparator: '',
											name: 'NasalExcreta',
											id: 'swENSEW_NasalExcreta',
											xtype: 'checkbox',
											checked: false,
											listeners: {
												'check': function(chb, checked ) {
													if ((this.NeonatalSurvey_data.NeonatalSurveyParam.NasalExcreta) || (checked)) {
														this.NeonatalSurvey_data.NeonatalSurveyParam.NasalExcreta = checked ? 2 : 1;		
														this.changedDatas = true;
													}
												}.createDelegate(this)
											}		
										}
									]
								}						
							]	
						},
						//Панель Рот    
						{
							id: 'swENSEW_Mouth_Panel',
							xtype: 'fieldset',
							autoHeight: true,
							title: langs('Рот'),
							collapsible: true,
							layout: 'form',
							labelWidth:80,
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
								//Расщепление
								{									
									layout:'column',
									style: 'margin-bottom: 0px;',
									items:[
										{									
											layout:'form',
											labelWidth:120,
											style: 'margin-top: 3px;',
											items:[
												{
													fieldLabel: 'Расщепление губы',
													labelSeparator: '',
													name: 'LipCleft',
													id: 'swENSEW_LipCleft',
													xtype: 'checkbox',
													checked: false,
													listeners: {
														'check': function(chb, checked ) {
															if ((this.NeonatalSurvey_data.NeonatalSurveyParam.LipCleft) || (checked)) {
																this.NeonatalSurvey_data.NeonatalSurveyParam.LipCleft = checked ? 2 : 1;		
																this.changedDatas = true;
															}
														}.createDelegate(this)
													}		
												}
											]
										},						
										//Расщепление нёба
										{xtype:'label',text: 'Расщепление нёба',style: 'font-size: 10pt;  margin-left: 12pt; margin-top: 4pt; margin-bottom: 2px; margin-right: 12px'},
										{									
											layout:'form',
											labelWidth:1,
											style: 'margin-top: 4px;  margin-left: 4px; margin-bottom: 1px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											items:[
												new Ext.form.RadioGroup({
													id:'swENSEW_PalateCleft',
													labelSeparator: '',
													vertical: true,
													columns: 4,
													FromInterface: true,
													items: [
														{boxLabel: 'нет', name: 'PalateCleft', inputValue: 0, width: 50}, 
														{boxLabel: 'мягкого', name: 'PalateCleft', inputValue: 1, width: 80}, 
														{boxLabel: 'твердого', name: 'PalateCleft', inputValue: 2, width: 80},
														{boxLabel: 'полное', name: 'PalateCleft', inputValue: 3, width: 80}
													],
													listeners: {
														'change': function(field, checked) {
															if((checked) && field.FromInterface){
																if ((this.NeonatalSurvey_data.NeonatalSurveyParam.PalateCleft) || (String(checked.inputValue) != '0')) {
																	this.NeonatalSurvey_data.NeonatalSurveyParam.PalateCleft = checked.inputValue;									
																	this.changedDatas = true;
																}
															}
															field.FromInterface = true;
														}.createDelegate(this)
													}
												})
											]
										}
									]
								},
								//'Короткая уздечка,Ранула,Мукоцеле
								{									
									layout:'column',
									style: 'margin-bottom: 0px;',
									items:[
										{									
											layout:'form',
											labelWidth:115,
											style: 'margin-top: 3px;',
											items:[
												{
													fieldLabel: 'Короткая уздечка',
													labelSeparator: '',
													name: 'ShortBridle',
													id: 'swENSEW_ShortBridle',
													xtype: 'checkbox',
													checked: false,
													listeners: {
														'check': function(chb, checked ) {
															if ((this.NeonatalSurvey_data.NeonatalSurveyParam.ShortBridle) || (checked)) {
																this.NeonatalSurvey_data.NeonatalSurveyParam.ShortBridle = checked ? 2 : 1;		
																this.changedDatas = true;
															}
														}.createDelegate(this)
													}		
												}
											]
										},						
										{									
											layout:'form',
											labelWidth:70,
											style: 'margin-top: 3px;',
											items:[
												{
													fieldLabel: 'Ранула',
													labelSeparator: '',
													name: 'Ranula',
													id: 'swENSEW_Ranula',
													xtype: 'checkbox',
													checked: false,
													listeners: {
														'check': function(chb, checked ) {
															if ((this.NeonatalSurvey_data.NeonatalSurveyParam.Ranula) || (checked)) {
																this.NeonatalSurvey_data.NeonatalSurveyParam.Ranula = checked ? 2 : 1;		
																this.changedDatas = true;
															}
														}.createDelegate(this)
													}		
												}
											]
										},						
										{									
											layout:'form',
											labelWidth:80,
											style: 'margin-top: 3px;',
											items:[
												{
													fieldLabel: 'Мукоцеле',
													labelSeparator: '',
													name: 'Mucocele',
													id: 'swENSEW_Mucocele',
													xtype: 'checkbox',
													checked: false,
													listeners: {
														'check': function(chb, checked ) {
															if ((this.NeonatalSurvey_data.NeonatalSurveyParam.Mucocele) || (checked)) { 
																this.NeonatalSurvey_data.NeonatalSurveyParam.Mucocele = checked ? 2 : 1;		
																this.changedDatas = true;
															}
														}.createDelegate(this)
													}		
												}
											]
										}						
									]
								},
								//Пренатальные зубы
								{									
									layout:'column',
									style: 'margin-bottom: 0px;',
									items:[
										{xtype:'label',text: 'Пренатальные зубы',style: 'font-size: 10pt;  margin-left: 12pt; margin-top: 2pt; margin-bottom: 2px; margin-right: 12px'},
										{									
											layout:'form',
											labelWidth:1,
											style: 'margin-top: 4px;  margin-left: 4px; margin-bottom: 2px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											items:[
												new Ext.form.RadioGroup({
													id:'swENSEW_PrenatalTeeth',
													labelSeparator: '',
													vertical: true,
													columns: 3,
													FromInterface: true,
													items: [
														{boxLabel: 'нет', name: 'PrenatalTeeth', inputValue: 0, width: 50}, 
														{boxLabel: 'предмолочные', name: 'PrenatalTeeth', inputValue: 1, width: 130}, 
														{boxLabel: 'настоящие молочные', name: 'PrenatalTeeth', inputValue: 2, width: 170}
													],
													listeners: {
														'change': function(field, checked) {
															if((checked) && field.FromInterface){
																if ((this.NeonatalSurvey_data.NeonatalSurveyParam.PrenatalTeeth) || (String(checked.inputValue) != '0')) {
																	this.NeonatalSurvey_data.NeonatalSurveyParam.PrenatalTeeth = checked.inputValue;									
																	this.changedDatas = true;
																}
															}
															field.FromInterface = true;
														}.createDelegate(this)
													}
												})	
											]
										},
										{
											allowBlank: true,
											fieldLabel: '',
											labelSeparator: '',
											name: 'swENSEW_PrenatalTeethTxt',
											id: 'swENSEW_PrenatalTeethTxt',
											width: 750,
											value:'',
											style: 'margin-top: 4px;  margin-left: 4px; ',
											xtype: 'textfield',
											listeners:{
												'change':function (field, newValue, oldValue) {
													this.NeonatalSurvey_data.NeonatalSurveyParam.PrenatalTeethTxt = newValue;
													this.changedDatas = true;
												}.createDelegate(this)
											}
										}
		

									]
								},
								//Макроглоссия, Отделяемое изо рта  
								{									
									layout:'column',
									style: 'margin-bottom: 0px;',
									items:[

										//Макроглоссия 
										{xtype:'label',text: 'Макроглоссия',style: 'font-size: 10pt;  margin-left: 12pt; margin-top: 2pt; margin-bottom: 2px; margin-right: 12px'},
										{									
											layout:'form',
											labelWidth:1,
											style: 'margin-top: 4px;  margin-left: 4px; margin-bottom: 1px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											items:[
												new Ext.form.RadioGroup({
													id:'swENSEW_Macroglossia',
													labelSeparator: '',
													vertical: true,
													columns: 3,
													FromInterface: true,
													items: [
														{boxLabel: 'нет', name: 'Macroglossia', inputValue: 0, width: 50}, 
														{boxLabel: 'врожденная', name: 'Macroglossia', inputValue: 1, width: 110}, 
														{boxLabel: 'приобретенная', name: 'Macroglossia', inputValue: 2, width: 130}
													],
													listeners: {
														'change': function(field, checked) {
															if((checked) && field.FromInterface){
																if ((this.NeonatalSurvey_data.NeonatalSurveyParam.Macroglossia) || (String(checked.inputValue) != '0')) {
																	this.NeonatalSurvey_data.NeonatalSurveyParam.Macroglossia = checked.inputValue;									
																	this.changedDatas = true;
																}
															}
															field.FromInterface = true;
														}.createDelegate(this)
													}
												})
											]
										},
										{									
											layout:'form',
											labelWidth:250,
											style: 'margin-top: 3px;',
											items:[
												{
													fieldLabel: 'Отделяемое изо рта',
													labelSeparator: '',
													name: 'MouthSepar',
													id: 'swENSEW_MouthSepar',
													xtype: 'checkbox',
													checked: false,
													listeners: {
														'check': function(chb, checked ) {
															if ((this.NeonatalSurvey_data.NeonatalSurveyParam.MouthSepar) || (checked)) {
																this.NeonatalSurvey_data.NeonatalSurveyParam.MouthSepar = checked ? 2 : 1;		
																this.changedDatas = true;
																//BOB - 23.04.2020
																if (checked) {
																	this.findById('swENSEW_MouthSeparFoamy').setDisabled(false);
																	this.findById('swENSEW_MouthSeparAbund').setDisabled(false);
																} else {
																	this.findById('swENSEW_MouthSeparFoamy').setDisabled(true);
																	this.findById('swENSEW_MouthSeparFoamy').setValue(false);
																	delete this.NeonatalSurvey_data.NeonatalSurveyParam.MouthSeparFoamy;
																	this.findById('swENSEW_MouthSeparAbund').setDisabled(true);
																	this.findById('swENSEW_MouthSeparAbund').setValue(false);
																	delete this.NeonatalSurvey_data.NeonatalSurveyParam.MouthSeparAbund;
																}

															}
														}.createDelegate(this)
													}		
												}
											]
										},						
										{									
											layout:'form',
											labelWidth:70,
											style: 'margin-top: 3px;',
											items:[
												{
													fieldLabel: 'пенистое',
													labelSeparator: '',
													name: 'MouthSeparFoamy',
													id: 'swENSEW_MouthSeparFoamy',
													xtype: 'checkbox',
													checked: false,
													listeners: {
														'check': function(chb, checked ) {
															if ((this.NeonatalSurvey_data.NeonatalSurveyParam.MouthSeparFoamy) || (checked)) { 
																this.NeonatalSurvey_data.NeonatalSurveyParam.MouthSeparFoamy = checked ? 2 : 1;		
																this.changedDatas = true;
															}
														}.createDelegate(this)
													}		
												}
											]
										},						
										{									
											layout:'form',
											labelWidth:70,
											style: 'margin-top: 3px;',
											items:[
												{
													fieldLabel: 'обильное',
													labelSeparator: '',
													name: 'MouthSeparAbund',
													id: 'swENSEW_MouthSeparAbund',
													xtype: 'checkbox',
													checked: false,
													listeners: {
														'check': function(chb, checked ) {
															if ((this.NeonatalSurvey_data.NeonatalSurveyParam.MouthSeparAbund) || (checked)) { 
																this.NeonatalSurvey_data.NeonatalSurveyParam.MouthSeparAbund = checked ? 2 : 1;		
																this.changedDatas = true;
															}
														}.createDelegate(this)
													}		
												}
											]
										}						
									]
								},
								//Молочница
								{
									fieldLabel: 'Молочница',
									labelSeparator: '',
									name: 'Thrush',
									id: 'swENSEW_Thrush',
									xtype: 'checkbox',
									checked: false,
									listeners: {
										'check': function(chb, checked ) {
											if((this.NeonatalSurvey_data.NeonatalSurveyParam.Thrush) || (checked)) {
												this.NeonatalSurvey_data.NeonatalSurveyParam.Thrush = checked ? 2 : 1;		
												this.changedDatas = true;
											}
										}.createDelegate(this)
									}		
								}
							]	
						},
						//Панель Дыхание    
						{
							id: 'swENSEW_Breath_Panel',
							xtype: 'fieldset',
							autoHeight: true,
							title: langs('Дыхание'),
							collapsible: true,
							layout: 'form',
							labelWidth:80,
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
								//Дыхание
								{									
									layout:'column',
									style: 'margin-bottom: 0px;',
									items:[
										{xtype:'label',text: 'Дыхание',style: 'font-size: 10pt;  margin-left: 2pt; margin-top: 2pt; margin-bottom: 2px'},
										{									
											layout:'form',
											labelWidth:1,
											style: 'margin-top: 4px; margin-bottom: 2px;  margin-left: 4px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											items:[
												new Ext.form.RadioGroup({
													id:'swENSEW_Breath1',
													labelSeparator: '',
													vertical: true,
													columns: 1,
													FromInterface: true,
													items: [
														{boxLabel: '---', name: 'Breath1', inputValue: 0, width: 120}, 
														{boxLabel: 'спонтанное', name: 'Breath1', inputValue: 1, width: 120}, 
														{boxLabel: 'аппаратное', name: 'Breath1', inputValue: 2, width: 120}
													],
													listeners: {
														'change': function(field, checked) {
															win.findById('swENSEW_Breath_Panel').change_Breath(field, checked);
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
													id:'swENSEW_Breath2',
													labelSeparator: '',
													vertical: true,
													columns: 1,
													FromInterface: true,
													items: [
														{boxLabel: '---', name: 'Breath2', inputValue: 0, width: 70}, 
														{boxLabel: 'свободное', name: 'Breath2', inputValue: 1, width: 120}, 
														{boxLabel: 'затруднено', name: 'Breath2', inputValue: 2, width: 120}
													],
													listeners: {
														'change': function(field, checked) {
															win.findById('swENSEW_Breath_Panel').change_Breath(field, checked);
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
													id:'swENSEW_Breath3',
													labelSeparator: '',
													vertical: true,
													columns: 1,
													FromInterface: true,
													items: [
														{boxLabel: '---', name: 'Breath3', inputValue: 0, width: 70}, 
														{boxLabel: 'через нос', name: 'Breath3', inputValue: 1, width: 120}, 
														{boxLabel: 'через рот', name: 'Breath3', inputValue: 2, width: 120},
														{boxLabel: 'другое', name: 'Breath3', inputValue: 3, width: 120}
													],
													listeners: {
														'change': function(field, checked) {
															win.findById('swENSEW_Breath_Panel').change_Breath(field, checked);
														}
													}
												})	
											]
										},
										{
											allowBlank: true,
											fieldLabel: '',
											labelSeparator: '',
											name: 'swENSEW_BreathTxt',
											id: 'swENSEW_BreathTxt',
											width: 750,
											value:'',
											style: 'margin-top: 4px;  margin-left: 4px; ',
											xtype: 'textfield',
											listeners:{
												'change':function (field, newValue, oldValue) {
													this.NeonatalSurvey_data.NeonatalSurveyParam.BreathTxt = newValue;
													this.changedDatas = true;
												}.createDelegate(this)
											}
										}										
									]	
								},
								//Респираторная терапия
								{									
									layout:'column',
									style: 'margin-bottom: 0px;',
									items:[
										{xtype:'label',text: 'Респираторная терапия',style: 'font-size: 10pt;  margin-left: 2pt; margin-top: 2pt; margin-bottom: 2px'},
										{									
											layout:'form',
											labelWidth:1,
											style: 'margin-top: 4px; margin-bottom: 2px;  margin-left: 4px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											items:[
												new Ext.form.RadioGroup({
													id:'swENSEW_RespirTherapy1',
													labelSeparator: '',
													vertical: true,
													columns: 1,
													FromInterface: true,
													items: [
														{boxLabel: 'нет', name: 'RespirTherapy1', inputValue: 0, width: 120}, 
														{boxLabel: 'оксигенотерапия', name: 'RespirTherapy1', inputValue: 1, width: 120}, 
														{boxLabel: 'НВИВЛ', name: 'RespirTherapy1', inputValue: 2, width: 120},
														{boxLabel: 'ИВЛ', name: 'RespirTherapy1', inputValue: 3, width: 120}
													],
													listeners: {
														'change': function(field, checked) {
															win.findById('swENSEW_Breath_Panel').change_RespirTherapy(field, checked);
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
													id:'swENSEW_RespirTherapy2',
													labelSeparator: '',
													vertical: true,
													columns: 1,
													FromInterface: true,
													items: [
														{boxLabel: '---', name: 'RespirTherapy2', inputValue: 0, width: 70}, 
														{boxLabel: 'О2 через маску', name: 'RespirTherapy2', inputValue: 1, width: 120}, 
														{boxLabel: 'О2 через носовые канюли', name: 'RespirTherapy2', inputValue: 2, width: 200},
														{boxLabel: 'детская кислородная палатка', name: 'RespirTherapy2', inputValue: 3, width: 200},
														{boxLabel: 'другое', name: 'RespirTherapy2', inputValue: 4, width: 120}
													],
													listeners: {
														'change': function(field, checked) {
															win.findById('swENSEW_Breath_Panel').change_RespirTherapy(field, checked);
														}
													}
												})
											]
										},
										{
											allowBlank: true,
											fieldLabel: '',
											labelSeparator: '',
											name: 'swENSEW_RespirTherapyTxt',
											id: 'swENSEW_RespirTherapyTxt',
											width: 750,
											value:'',
											style: 'margin-top: 4px;  margin-left: 4px; ',
											xtype: 'textfield',
											listeners:{
												'change':function (field, newValue, oldValue) {
													this.NeonatalSurvey_data.NeonatalSurveyParam.RespirTherapyTxt = newValue;
													this.changedDatas = true;
												}.createDelegate(this)
											}
										}
									]
								},
								//Параметры ИВЛ
								{									
									layout:'form',
									labelWidth:100,
									style: 'margin-bottom: 0px;',
									items:[
										{
											allowBlank: true,
											fieldLabel: 'Аппарат ИВЛ',
											labelSeparator: '',
											name: 'swENSEW_Ventilator',
											id: 'swENSEW_Ventilator',
											width: 250,
											value:'',
											style: 'margin-top: 4px;  margin-left: 4px; ',
											xtype: 'textfield',
											listeners:{
												'change':function (field, newValue, oldValue) {
													this.NeonatalSurvey_data.NeonatalSurveyParam.Ventilator = newValue;
													this.changedDatas = true;
												}.createDelegate(this)
											}
										},										
										{
											allowBlank: true,
											fieldLabel: 'Параметры ИВЛ',
											labelSeparator: '',
											name: 'swENSEW_VentilatorParam',
											id: 'swENSEW_VentilatorParam',
											width: 1170,
											value:'',
											style: 'margin-top: 4px;  margin-left: 4px; ',
											xtype: 'textfield',
											listeners:{
												'change':function (field, newValue, oldValue) {
													this.NeonatalSurvey_data.NeonatalSurveyParam.VentilatorParam = newValue;
													this.changedDatas = true;
												}.createDelegate(this)
											}
										}										
									]
								}
							],
							change_Breath: function(field, checked) {
								if((checked) && field.FromInterface){
									var Breath = (this.NeonatalSurvey_data.NeonatalSurveyParam.Breath) ? this.NeonatalSurvey_data.NeonatalSurveyParam.Breath : '000';
									var position = parseInt(field.id.substr(field.id.length - 1, 1));
									var value = checked.inputValue;
									Breath = Breath.split("");
									Breath[position - 1] = value;
									Breath = Breath.join("");
									if (!Ext.isEmpty(this.NeonatalSurvey_data.NeonatalSurveyParam.Breath) || (Breath != '000')) 
										this.NeonatalSurvey_data.NeonatalSurveyParam.Breath = Breath;									
									this.changedDatas = true;
								}
								field.FromInterface = true;
							}.createDelegate(this)	,
							change_RespirTherapy: function(field, checked) {
								if((checked) && field.FromInterface){
									var RespirTherapy = (this.NeonatalSurvey_data.NeonatalSurveyParam.RespirTherapy) ? this.NeonatalSurvey_data.NeonatalSurveyParam.RespirTherapy : '00';
									var position = parseInt(field.id.substr(field.id.length - 1, 1));
									var value = checked.inputValue;
									RespirTherapy = RespirTherapy.split("");
									RespirTherapy[position - 1] = value;
									RespirTherapy = RespirTherapy.join("");
									if (!Ext.isEmpty(this.NeonatalSurvey_data.NeonatalSurveyParam.RespirTherapy) || (RespirTherapy != '00')) 
										this.NeonatalSurvey_data.NeonatalSurveyParam.RespirTherapy = RespirTherapy;									
									this.changedDatas = true;
								}
								field.FromInterface = true;
							}.createDelegate(this)	
						},
						//Панель Грудная клетка    
						{
							id: 'swENSEW_Thorax_Panel',
							xtype: 'fieldset',
							autoHeight: true,
							title: langs('Грудная клетка'),
							collapsible: true,
							layout: 'form',
							labelWidth:80,
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

								//Форма 
								{									
									layout:'column',
									id: 'swENSEW_ThoraxShape_Panel',
									style: 'margin-bottom: 0px;',
									items:[
										{xtype:'label',text: 'Форма',style: 'font-size: 10pt;  margin-left: 12pt; margin-top: 2pt; margin-bottom: 2px'},
										{									
											layout:'form',
											labelWidth:1,
											style: 'margin-top: 4px; margin-bottom: 0px;  margin-left: 4px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											items:[
												new Ext.form.RadioGroup({
													id:'swENSEW_ThoraxShape1',
													labelSeparator: '',
													vertical: true,
													columns: 6,
													FromInterface: true,
													items: [
														{boxLabel: '---', name: 'ThoraxShape1', inputValue: 0, width: 50}, 
														{boxLabel: 'цилиндрическая', name: 'ThoraxShape1', inputValue: 1, width: 130}, 
														{boxLabel: 'бочкообразная', name: 'ThoraxShape1', inputValue: 2, width: 120},
														{boxLabel: 'воронкообразная', name: 'ThoraxShape1', inputValue: 3, width: 130},
														{boxLabel: 'килевидная', name: 'ThoraxShape1', inputValue: 4, width: 100},
														{boxLabel: 'другое', name: 'ThoraxShape1', inputValue: 5, width: 70}
													],
													listeners: {
														'change': function(field, checked) {
															win.findById('swENSEW_ThoraxShape_Panel').change_handler(field, checked);
														}
													}
												})	
											]
										},
										{
											allowBlank: true,
											fieldLabel: '',
											labelSeparator: '',
											name: 'swENSEW_ThoraxShapeTxt',
											id: 'swENSEW_ThoraxShapeTxt',
											width: 620,
											value:'',
											style: 'margin-top: 8px;  margin-left: 4px; ; margin-bottom: 10px',
											xtype: 'textfield',
											listeners:{
												'change':function (field, newValue, oldValue) {
													this.NeonatalSurvey_data.NeonatalSurveyParam.ThoraxShapeTxt = newValue;
													this.changedDatas = true;
												}.createDelegate(this)
											}
										},
										{xtype:'label',text: 'Симметричность',style: 'font-size: 10pt;  margin-left: 12pt; margin-top: 2pt; margin-bottom: 2px'},
										{									
											layout:'form',
											labelWidth:1,
											style: 'margin-top: 4px;  margin-left: 4px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											items:[
												new Ext.form.RadioGroup({
													id:'swENSEW_ThoraxShape2',
													labelSeparator: '',
													vertical: true,
													columns: 6,
													FromInterface: true,
													items: [
														{boxLabel: '---', name: 'ThoraxShape2', inputValue: 0, width: 50}, 
														{boxLabel: 'симметричная', name: 'ThoraxShape2', inputValue: 1, width: 120}, 
														{boxLabel: 'вздутие справа', name: 'ThoraxShape2', inputValue: 2, width: 150},
														{boxLabel: 'вздутие слева', name: 'ThoraxShape2', inputValue: 3, width: 150},
														{boxLabel: 'западение справа', name: 'ThoraxShape2', inputValue: 4, width: 170},
														{boxLabel: 'западение слева', name: 'ThoraxShape2', inputValue: 5, width: 170}
													],
													listeners: {
														'change': function(field, checked) {
															win.findById('swENSEW_ThoraxShape_Panel').change_handler(field, checked);
														}
													}
												})	
											]
										}										
									],
									change_handler: function(field, checked) {
										if((checked) && field.FromInterface){
											var ThoraxShape = (this.NeonatalSurvey_data.NeonatalSurveyParam.ThoraxShape) ? this.NeonatalSurvey_data.NeonatalSurveyParam.ThoraxShape : '00';
											var position = parseInt(field.id.substr(field.id.length - 1, 1));
											var value = checked.inputValue;
											ThoraxShape = ThoraxShape.split("");
											ThoraxShape[position - 1] = value;
											ThoraxShape = ThoraxShape.join("");
											if (!Ext.isEmpty(this.NeonatalSurvey_data.NeonatalSurveyParam.ThoraxShape) || (ThoraxShape != '00')) 
												this.NeonatalSurvey_data.NeonatalSurveyParam.ThoraxShape = ThoraxShape;									
											this.changedDatas = true;
										}
										field.FromInterface = true;
									}.createDelegate(this)
		
								},
								// Особенности грудной клетки
								{									
									layout:'column',
									id: 'swENSEW_ThoraxFeatures_Panel',
									items:[
										//Втяжение грудной клетки
										{xtype:'label',text: 'Втяжение грудной клетки',style: 'font-size: 10pt;  margin-left: 2pt; margin-top: 2pt; margin-bottom: 2px', width: 70},
										{									
											layout:'form',
											labelWidth:1,
											style: 'margin-top: 4px;  margin-left: 4px; margin-bottom: 2px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											items:[
												new Ext.form.RadioGroup({
													id:'swENSEW_ThoraxFeatures1',
													labelSeparator: '',
													vertical: true,
													columns: 1,
													FromInterface: true,
													items: [
														{boxLabel: 'нет', name: 'ThoraxFeatures1', inputValue: 0, width: 50}, 
														{boxLabel: 'умеренное', name: 'ThoraxFeatures1', inputValue: 1, width: 90}, 
														{boxLabel: 'выраженное', name: 'ThoraxFeatures1', inputValue: 2, width: 100}
													],
													listeners: {
														'change': function(field, checked) {
															win.findById('swENSEW_ThoraxFeatures_Panel').change_handler(field, checked);
														}
													}
												})	
											]
										},
										//Участие вспомогательной мускулатуры
										{xtype:'label',text: 'Участие вспомогательной мускулатуры',style: 'font-size: 10pt;  margin-left: 12pt; margin-top: 2pt; margin-bottom: 2px', width: 110},
										{									
											layout:'form',
											labelWidth:1,
											style: 'margin-top: 4px;  margin-left: 4px; margin-bottom: 2px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											items:[
												new Ext.form.RadioGroup({
													id:'swENSEW_ThoraxFeatures2',
													labelSeparator: '',
													vertical: true,
													columns: 1,
													FromInterface: true,
													items: [
														{boxLabel: 'нет', name: 'ThoraxFeatures2', inputValue: 0, width: 50}, 
														{boxLabel: 'умеренное', name: 'ThoraxFeatures2', inputValue: 1, width: 90}, 
														{boxLabel: 'выраженное', name: 'ThoraxFeatures2', inputValue: 2, width: 100}
													],
													listeners: {
														'change': function(field, checked) {
															win.findById('swENSEW_ThoraxFeatures_Panel').change_handler(field, checked);
														}
													}
												})	
											]
										},
										//Западение уступчивых мест грудной клетки 
										{xtype:'label',text: 'Западение уступчивых мест грудной клетки',style: 'font-size: 10pt;  margin-left: 12pt; margin-top: 2pt; margin-bottom: 2px', width: 100},
										{									
											layout:'form',
											labelWidth:1,
											style: 'margin-top: 4px;  margin-left: 4px; margin-bottom: 2px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											items:[
												new Ext.form.RadioGroup({
													id:'swENSEW_ThoraxFeatures3',
													labelSeparator: '',
													vertical: true,
													columns: 1,
													FromInterface: true,
													items: [
														{boxLabel: 'нет', name: 'ThoraxFeatures3', inputValue: 0, width: 50}, 
														{boxLabel: 'умеренное', name: 'ThoraxFeatures3', inputValue: 1, width: 90}, 
														{boxLabel: 'выраженное', name: 'ThoraxFeatures3', inputValue: 2, width: 100}
													],
													listeners: {
														'change': function(field, checked) {
															win.findById('swENSEW_ThoraxFeatures_Panel').change_handler(field, checked);
														}
													}
												})	
											]
										},
										//Одышка 
										{xtype:'label',text: 'Одышка',style: 'font-size: 10pt;  margin-left: 12pt; margin-top: 2pt; margin-bottom: 2px'},
										{									
											layout:'form',
											labelWidth:1,
											style: 'margin-top: 4px;  margin-left: 4px; margin-bottom: 2px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											items:[
												new Ext.form.RadioGroup({
													id:'swENSEW_ThoraxFeatures4',
													labelSeparator: '',
													vertical: true,
													columns: 1,
													FromInterface: true,
													items: [
														{boxLabel: 'нет', name: 'ThoraxFeatures4', inputValue: 0, width: 50}, 
														{boxLabel: 'экспираторная', name: 'ThoraxFeatures4', inputValue: 1, width: 130}, 
														{boxLabel: 'инспираторная', name: 'ThoraxFeatures4', inputValue: 2, width: 130},
														{boxLabel: 'смешанная', name: 'ThoraxFeatures4', inputValue: 3, width: 130}
													],
													listeners: {
														'change': function(field, checked) {
															win.findById('swENSEW_ThoraxFeatures_Panel').change_handler(field, checked);
														}
													}
												})	
											]
										},
										//Стон  
										{xtype:'label',text: 'Стон',style: 'font-size: 10pt;  margin-left: 12pt; margin-top: 2pt; margin-bottom: 2px'},
										{									
											layout:'form',
											labelWidth:1,
											style: 'margin-top: 4px;  margin-left: 4px; margin-bottom: 2px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											items:[
												new Ext.form.RadioGroup({
													id:'swENSEW_ThoraxFeatures5',
													labelSeparator: '',
													vertical: true,
													columns: 1,
													FromInterface: true,
													items: [
														{boxLabel: 'нет', name: 'ThoraxFeatures5', inputValue: 0, width: 50}, 
														{boxLabel: 'слышен при аускультации', name: 'ThoraxFeatures5', inputValue: 1, width: 200}, 
														{boxLabel: 'слышен на расстоянии', name: 'ThoraxFeatures5', inputValue: 2, width: 130}
													],
													listeners: {
														'change': function(field, checked) {
															win.findById('swENSEW_ThoraxFeatures_Panel').change_handler(field, checked);
														}
													}
												})	
											]
										}
									],
									change_handler: function(field, checked) {
										if((checked) && field.FromInterface){
											var ThoraxFeatures = (this.NeonatalSurvey_data.NeonatalSurveyParam.ThoraxFeatures) ? this.NeonatalSurvey_data.NeonatalSurveyParam.ThoraxFeatures : '00000';
											var position = parseInt(field.id.substr(field.id.length - 1, 1));
											var value = checked.inputValue;
											ThoraxFeatures = ThoraxFeatures.split("");
											ThoraxFeatures[position - 1] = value;
											ThoraxFeatures = ThoraxFeatures.join("");
											if (!Ext.isEmpty(this.NeonatalSurvey_data.NeonatalSurveyParam.ThoraxFeatures) || (ThoraxFeatures != '00000')) 
												this.NeonatalSurvey_data.NeonatalSurveyParam.ThoraxFeatures = ThoraxFeatures;									
											this.changedDatas = true;
										}
										field.FromInterface = true;
									}.createDelegate(this)
								},
								//Отделяемое из ВДП 
								{									
									layout:'column',
									id: 'swENSEW_VDPSeparated_Panel',
									style: 'margin-bottom: 0px;',
									items:[
										{xtype:'label',text: 'Отделяемое из ВДП',style: 'font-size: 10pt;  margin-left: 12pt; margin-top: 2pt; margin-bottom: 2px'},
										{									
											layout:'form',
											labelWidth:1,
											style: 'margin-top: 4px; margin-bottom: 0px;  margin-left: 4px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											items:[
												new Ext.form.RadioGroup({
													id:'swENSEW_VDPSeparated1',
													labelSeparator: '',
													vertical: true,
													columns: 6,
													FromInterface: true,
													items: [
														{boxLabel: 'нет', name: 'VDPSeparated1', inputValue: 0, width: 50}, 
														{boxLabel: 'слизистое', name: 'VDPSeparated1', inputValue: 1, width: 80}, 
														{boxLabel: 'гнойное', name: 'VDPSeparated1', inputValue: 2, width: 70},
														{boxLabel: 'геморрагическое', name: 'VDPSeparated1', inputValue: 3, width: 120},
														{boxLabel: 'с примесью мекония', name: 'VDPSeparated1', inputValue: 4, width: 150},
														{boxLabel: 'другое', name: 'VDPSeparated1', inputValue: 5, width: 70}
													],
													listeners: {
														'change': function(field, checked) {
															if(field.FromInterface) win.findById('swENSEW_VDPSeparated_Panel').change_handler(field, checked);
															field.FromInterface = true;
														}
													}
												})	
											]
										},
										{xtype:'label',text: 'количество',style: 'font-size: 10pt;  margin-left: 12pt; margin-top: 2pt; margin-bottom: 2px'},
										{									
											layout:'form',
											labelWidth:1,
											style: 'margin-top: 4px;  margin-left: 4px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											items:[
												new Ext.form.RadioGroup({
													id:'swENSEW_VDPSeparated2',
													labelSeparator: '',
													vertical: true,
													columns: 4,
													FromInterface: true,
													items: [
														{boxLabel: '---', name: 'VDPSeparated2', inputValue: 0, width: 50, hidden: true}, 
														{boxLabel: 'скудное', name: 'VDPSeparated2', inputValue: 1, width: 70}, 
														{boxLabel: 'умеренное', name: 'VDPSeparated2', inputValue: 2, width: 90},
														{boxLabel: 'в большом количестве', name: 'VDPSeparated2', inputValue: 3, width: 170}
													],
													listeners: {
														'change': function(field, checked) {
															if(field.FromInterface) win.findById('swENSEW_VDPSeparated_Panel').change_handler(field, checked);
															field.FromInterface = true;
														}
													}
												})	
											]
										},
										{
											allowBlank: true,
											fieldLabel: '',
											labelSeparator: '',
											name: 'swENSEW_VDPSeparatedTxt',
											id: 'swENSEW_VDPSeparatedTxt',
											width: 700,
											value:'',
											style: 'margin-top: 8px;  margin-left: 12px; margin-bottom: 10px;   ',
											xtype: 'textfield',
											listeners:{
												'change':function (field, newValue, oldValue) {
													this.NeonatalSurvey_data.NeonatalSurveyParam.VDPSeparatedTxt = newValue;
													this.changedDatas = true;
												}.createDelegate(this)
											}
										}										
									],
									change_handler: function(field, checked) {
										if(checked){
											var VDPSeparated = (this.NeonatalSurvey_data.NeonatalSurveyParam.VDPSeparated) ? this.NeonatalSurvey_data.NeonatalSurveyParam.VDPSeparated : '00';
											var position = parseInt(field.id.substr(field.id.length - 1, 1));
											var value = checked.inputValue;
											//если меняется выбранный в первой группе то обнуляются вторая
											if (position == 1) {
												this.findById('swENSEW_VDPSeparated2').items.items[0].setValue(true);		
												for (var i = 1; i < this.findById('swENSEW_VDPSeparated2').items.items.length; i++  ) this.findById('swENSEW_VDPSeparated2').items.items[i].setValue(false);						
												this.findById('swENSEW_VDPSeparated2').disable();						
												if(value > 0) this.findById('swENSEW_VDPSeparated2').enable();	
											}
											VDPSeparated = VDPSeparated.split("");
											VDPSeparated[position - 1] = value;
											VDPSeparated = VDPSeparated.join("");
											if (!Ext.isEmpty(this.NeonatalSurvey_data.NeonatalSurveyParam.VDPSeparated) || (VDPSeparated != '00')) 
												this.NeonatalSurvey_data.NeonatalSurveyParam.VDPSeparated = VDPSeparated;									
											this.changedDatas = true;
										}
									}.createDelegate(this)		
								},
								//Отделяемое из ЭТТ 
								{									
									layout:'column',
									id: 'swENSEW_ETTSeparated_Panel',
									style: 'margin-bottom: 0px;',
									items:[
										{xtype:'label',text: 'Отделяемое из ЭТТ',style: 'font-size: 10pt;  margin-left: 2pt; margin-top: 2pt; margin-bottom: 2px'},
										{									
											layout:'form',
											labelWidth:6,
											style: 'margin-top: 4px; margin-bottom: 2px;  margin-left: 4px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											items:[
												new Ext.form.RadioGroup({
													id:'swENSEW_ETTSeparated1',
													labelSeparator: '',
													vertical: true,
													columns: 6,
													FromInterface: true,
													items: [
														{boxLabel: 'нет', name: 'ETTSeparated1', inputValue: 0, width: 50}, 
														{boxLabel: 'слизистое', name: 'ETTSeparated1', inputValue: 1, width: 80}, 
														{boxLabel: 'гнойное', name: 'ETTSeparated1', inputValue: 2, width: 70},
														{boxLabel: 'геморрагическое', name: 'ETTSeparated1', inputValue: 3, width: 120},
														{boxLabel: 'с примесью мекония', name: 'ETTSeparated1', inputValue: 4, width: 150},
														{boxLabel: 'другое', name: 'ETTSeparated1', inputValue: 5, width: 70}
													],
													listeners: {
														'change': function(field, checked) {
															if(field.FromInterface) win.findById('swENSEW_ETTSeparated_Panel').change_handler(field, checked);
															field.FromInterface = true;
														}
													}
												})	
											]
										},
										{xtype:'label',text: 'количество',style: 'font-size: 10pt;  margin-left: 12pt; margin-top: 2pt; margin-bottom: 2px'},
										{									
											layout:'form',
											labelWidth:1,
											style: 'margin-top: 4px;  margin-left: 4px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											items:[
												new Ext.form.RadioGroup({
													id:'swENSEW_ETTSeparated2',
													labelSeparator: '',
													vertical: true,
													columns: 4,
													FromInterface: true,
													items: [
														{boxLabel: '---', name: 'ETTSeparated2', inputValue: 0, width: 50, hidden: true}, 
														{boxLabel: 'скудное', name: 'ETTSeparated2', inputValue: 1, width: 70}, 
														{boxLabel: 'умеренном', name: 'ETTSeparated2', inputValue: 2, width: 90},
														{boxLabel: 'в большом количестве', name: 'ETTSeparated2', inputValue: 3, width: 170}
													],
													listeners: {
														'change': function(field, checked) {
															if(field.FromInterface) win.findById('swENSEW_ETTSeparated_Panel').change_handler(field, checked);
															field.FromInterface = true;
														}
													}
												})	
											]
										},
										{
											allowBlank: true,
											fieldLabel: '',
											labelSeparator: '',
											name: 'swENSEW_ETTSeparatedTxt',
											id: 'swENSEW_ETTSeparatedTxt',
											width: 700,
											value:'',
											style: 'margin-top: 4px;  margin-left: 12px; margin-bottom: 10px ',
											xtype: 'textfield',
											listeners:{
												'change':function (field, newValue, oldValue) {
													this.NeonatalSurvey_data.NeonatalSurveyParam.ETTSeparatedTxt = newValue;
													this.changedDatas = true;
												}.createDelegate(this)
											}
										}										
									],
									change_handler: function(field, checked) {
										if(checked){
											var ETTSeparated = (this.NeonatalSurvey_data.NeonatalSurveyParam.ETTSeparated) ? this.NeonatalSurvey_data.NeonatalSurveyParam.ETTSeparated : '00';
											var position = parseInt(field.id.substr(field.id.length - 1, 1));
											var value = checked.inputValue;
											//если меняется выбранный в первой группе то обнуляются вторая
											if (position == 1) {
												this.findById('swENSEW_ETTSeparated2').items.items[0].setValue(true);		
												for (var i = 1; i < this.findById('swENSEW_ETTSeparated2').items.items.length; i++  ) this.findById('swENSEW_ETTSeparated2').items.items[i].setValue(false);						
												this.findById('swENSEW_ETTSeparated2').disable();						
												if(value > 0) this.findById('swENSEW_ETTSeparated2').enable();	
											}
											ETTSeparated = ETTSeparated.split("");
											ETTSeparated[position - 1] = value;
											ETTSeparated = ETTSeparated.join("");
											if (!Ext.isEmpty(this.NeonatalSurvey_data.NeonatalSurveyParam.ETTSeparated) || (ETTSeparated != '00')) 
												this.NeonatalSurvey_data.NeonatalSurveyParam.ETTSeparated = ETTSeparated;									
											this.changedDatas = true;
										}
									}.createDelegate(this)		
								},
								// Молочные железы
								{									
									layout:'column',
									id: 'swENSEW_MammaryGlands_Panel',
									items:[
										//Молочные железы
										{xtype:'label',text: 'Молочные железы: Увеличение',style: 'font-size: 10pt;  margin-left: 2pt; margin-top: 2pt; margin-bottom: 2px'},
										{									
											layout:'form',
											labelWidth:1,
											style: 'margin-top: 4px;  margin-left: 4px; margin-bottom: 2px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											items:[
												new Ext.form.RadioGroup({
													id:'swENSEW_MammaryGlands1',
													labelSeparator: '',
													vertical: true,
													columns: 1,
													FromInterface: true,
													items: [
														{boxLabel: '---', name: 'MammaryGlands1', inputValue: 0, width: 50}, 
														{boxLabel: 'одностороннее справа', name: 'MammaryGlands1', inputValue: 1, width: 170}, 
														{boxLabel: 'одностороннее слева', name: 'MammaryGlands1', inputValue: 2, width: 150},
														{boxLabel: 'двустороннее', name: 'MammaryGlands1', inputValue: 3, width: 100}
													],
													listeners: {
														'change': function(field, checked) {
															win.findById('swENSEW_MammaryGlands_Panel').change_handler(field, checked);
														}
													}
												})	
											]
										},
										//Гиперемия
										{xtype:'label',text: 'Гиперемия',style: 'font-size: 10pt;  margin-left: 12pt; margin-top: 2pt; margin-bottom: 2px'},
										{									
											layout:'form',
											labelWidth:1,
											style: 'margin-top: 4px;  margin-left: 4px; margin-bottom: 2px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											items:[
												new Ext.form.RadioGroup({
													id:'swENSEW_MammaryGlands2',
													labelSeparator: '',
													vertical: true,
													columns: 1,
													FromInterface: true,
													items: [
														{boxLabel: '---', name: 'MammaryGlands2', inputValue: 0, width: 50}, 
														{boxLabel: 'односторонняя справа', name: 'MammaryGlands2', inputValue: 1, width: 170}, 
														{boxLabel: 'односторонняя слева', name: 'MammaryGlands2', inputValue: 2, width: 150},
														{boxLabel: 'двусторонняя', name: 'MammaryGlands2', inputValue: 3, width: 110}
													],
													listeners: {
														'change': function(field, checked) {
															win.findById('swENSEW_MammaryGlands_Panel').change_handler(field, checked);
														}
													}
												})	
											]
										},
										//Отделяемое из грудных желез 
										{xtype:'label',text: 'Отделяемое из грудных желез',style: 'font-size: 10pt;  margin-left: 12pt; margin-top: 2pt; margin-bottom: 2px'},
										{									
											layout:'form',
											labelWidth:1,
											style: 'margin-top: 4px;  margin-left: 4px; margin-bottom: 2px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											items:[
												new Ext.form.RadioGroup({
													id:'swENSEW_MammaryGlands3',
													labelSeparator: '',
													vertical: true,
													columns: 1,
													FromInterface: true,
													items: [
														{boxLabel: '---', name: 'MammaryGlands3', inputValue: 0, width: 50}, 
														{boxLabel: 'серозное', name: 'MammaryGlands3', inputValue: 1, width: 90}, 
														{boxLabel: 'серозно-геморрагическое', name: 'MammaryGlands3', inputValue: 2, width: 180}, 
														{boxLabel: 'слизистое', name: 'MammaryGlands3', inputValue: 3, width: 90}, 
														{boxLabel: 'слизисто-гнойное', name: 'MammaryGlands3', inputValue: 4, width: 160}, 
														{boxLabel: 'гнойное', name: 'MammaryGlands3', inputValue: 5, width: 90}
													],
													listeners: {
														'change': function(field, checked) {
															win.findById('swENSEW_MammaryGlands_Panel').change_handler(field, checked);
														}
													}
												})	
											]
										}
									],
									change_handler: function(field, checked) {
										if((checked) && field.FromInterface){
											var MammaryGlands = (this.NeonatalSurvey_data.NeonatalSurveyParam.MammaryGlands) ? this.NeonatalSurvey_data.NeonatalSurveyParam.MammaryGlands : '000';
											var position = parseInt(field.id.substr(field.id.length - 1, 1));
											var value = checked.inputValue;
											MammaryGlands = MammaryGlands.split("");
											MammaryGlands[position - 1] = value;
											MammaryGlands = MammaryGlands.join("");
											if (!Ext.isEmpty(this.NeonatalSurvey_data.NeonatalSurveyParam.MammaryGlands) || (MammaryGlands != '000')) 
												this.NeonatalSurvey_data.NeonatalSurveyParam.MammaryGlands = MammaryGlands;									
											this.changedDatas = true;
										}
										field.FromInterface = true;
									}.createDelegate(this)
								},
								// Аускультативное дыхание
								{									
									layout:'form',
									id: 'swENSEW_Auskult_Panel',
									items:[
										//{xtype:'label',text: 'Аускультативное дыхание',style: 'font-size: 10pt; font-weight: bold; font-style:italic; margin-left: 2pt; margin-top: 2pt; margin-bottom: 2px'},
									]
								}
							]		
						},
						//Панель Гемодинамика
						{
							id: 'swENSEW_Hemodynamics_Panel',
							xtype: 'fieldset',
							autoHeight: true,
							title: langs('Гемодинамика'),
							collapsible: true,
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
									labelWidth:1,
									style: 'margin-top: 4px;  margin-left: 4px; margin-bottom: 2px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
									items:[
										new Ext.form.RadioGroup({
											id:'swENSEW_Hemodynamics',
											labelSeparator: '',
											vertical: true,
											columns: 3,
											FromInterface: true,
											items: [
												{boxLabel: '---', name: 'Hemodynamics', inputValue: 0, width: 50}, 
												{boxLabel: 'стабильная', name: 'Hemodynamics', inputValue: 1, width: 130}, 
												{boxLabel: 'нестабильная', name: 'Hemodynamics', inputValue: 2, width: 130}
											],
											listeners: {
												'change': function(field, checked) {
													if((checked) && field.FromInterface){
														if ((this.NeonatalSurvey_data.NeonatalSurveyParam.Hemodynamics) || (String(checked.inputValue) != '0')) {
															this.NeonatalSurvey_data.NeonatalSurveyParam.Hemodynamics = checked.inputValue;									
															this.changedDatas = true;

															if(String(checked.inputValue) == '2') {
																Ext.Ajax.request({
																	showErrors: false,
																	url: '/?c=EvnNeonatalSurvey&m=getSedationMedicat',
																	params: { 
																		EvnNeonatalSurvey_pid: this.EvnNeonatalSurvey_pid,
																		EvnNeonatalSurvey_setDate: this.NeonatalSurvey_data['EvnNeonatalSurvey'].EvnNeonatalSurvey_setDate,
																		EvnNeonatalSurvey_setTime: this.NeonatalSurvey_data['EvnNeonatalSurvey'].EvnNeonatalSurvey_setTime,
																		EvnNeonatalSurvey_disDate: this.NeonatalSurvey_data['EvnNeonatalSurvey'].EvnNeonatalSurvey_disDate,
																		EvnNeonatalSurvey_disTime: this.NeonatalSurvey_data['EvnNeonatalSurvey'].EvnNeonatalSurvey_disTime,
																		ReanimatActionType_SysNick: 'vazopressors'
																	 },
																	failure: function(response, options) {
																		showSysMsg(langs('При получении данных Наблюдение состояния младенца произошла ошибка!'));
																	},
																	callback: function(opt, success, response) {
																		if (success && response.responseText != '')
																		{
																			var SedationMedicat = Ext.util.JSON.decode(response.responseText);
																			var SedationMedicatTxt = win.findById('swENSEW_HemodynamicsTxt').getValue();
																			console.log('BOB_SedationMedicat=',SedationMedicat);
																			for(var i in SedationMedicat) {
																				if (typeof SedationMedicat[i] == 'object') {

																					SedationMedicatTxt +=  ((i==0 && Ext.isEmpty(SedationMedicatTxt)) ? '' : ', ') + SedationMedicat[i].ReanimDrugType_Name + ' ' + SedationMedicat[i].ReanimDrug_Dose + ' ' + SedationMedicat[i].ReanimDrug_Unit;
																				}
																			}
																			win.findById('swENSEW_HemodynamicsTxt').setValue(SedationMedicatTxt);
																			win.NeonatalSurvey_data.NeonatalSurveyParam.HemodynamicsTxt = SedationMedicatTxt;
																		}
																	}
																});																														
															}
														}
													}
													field.FromInterface = true;
												}.createDelegate(this)
											}
										})	
									]
								},
								{
									allowBlank: true,
									fieldLabel: '',
									labelSeparator: '',
									name: 'swENSEW_HemodynamicsTxt',
									id: 'swENSEW_HemodynamicsTxt',
									width: 970,
									value:'',
									style: 'margin-top: 4px;  margin-left: 4px; ',
									xtype: 'textfield',
									listeners:{
										'change':function (field, newValue, oldValue) {
											this.NeonatalSurvey_data.NeonatalSurveyParam.HemodynamicsTxt = newValue;
											this.changedDatas = true;
										}.createDelegate(this)
									}
								}
							]
						},
						//Панель Сердце    
						{
							id: 'swENSEW_Heart_Panel',
							xtype: 'fieldset',
							autoHeight: true,
							title: langs('Сердце'),
							collapsible: true,
							layout: 'form',
							labelWidth:80,
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
								//Сердечные тоны  
								{									
									layout:'column',
									id: 'swENSEW_HeartTones_Panel',
									style: 'margin-bottom: 0px;',
									items:[
										{xtype:'label',text: 'Сердечные тоны: ритм',style: 'font-size: 10pt;  margin-left: 12pt; margin-top: 2pt; margin-bottom: 2px'},
										{									
											layout:'form',
											labelWidth:1,
											style: 'margin-top: 4px; margin-bottom: 2px;  margin-left: 4px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											items:[
												new Ext.form.RadioGroup({
													id:'swENSEW_HeartTones1',
													labelSeparator: '',
													vertical: true,
													columns: 5,
													FromInterface: true,
													items: [
														{boxLabel: '---', name: 'HeartTones1', inputValue: 0, width: 50}, 
														{boxLabel: 'ритмичный', name: 'HeartTones1', inputValue: 1, width: 110}, 
														{boxLabel: 'тахиаритмия', name: 'HeartTones1', inputValue: 2, width: 120},
														{boxLabel: 'брадиаритмия', name: 'HeartTones1', inputValue: 3, width: 130},
														{boxLabel: 'дополнительный тон', name: 'HeartTones1', inputValue: 4, width: 150}
													],
													listeners: {
														'change': function(field, checked) {
															win.findById('swENSEW_HeartTones_Panel').change_handler(field, checked);
														}
													}
												})	
											]
										},
										{xtype:'label',text: 'характер',style: 'font-size: 10pt;  margin-left: 12pt; margin-top: 2pt; margin-bottom: 2px'},
										{									
											layout:'form',
											labelWidth:1,
											style: 'margin-top: 4px;  margin-left: 4px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											items:[
												new Ext.form.RadioGroup({
													id:'swENSEW_HeartTones2',
													labelSeparator: '',
													vertical: true,
													columns: 4,
													FromInterface: true,
													items: [
														{boxLabel: '---', name: 'HeartTones2', inputValue: 0, width: 50}, 
														{boxLabel: 'ясные', name: 'HeartTones2', inputValue: 1, width: 80}, 
														{boxLabel: 'приглушены', name: 'HeartTones2', inputValue: 2, width: 120},
														{boxLabel: 'глухие', name: 'HeartTones2', inputValue: 3, width: 70}
													],
													listeners: {
														'change': function(field, checked) {
															win.findById('swENSEW_HeartTones_Panel').change_handler(field, checked);
														}
													}
												})	
											]
										}										
									],
									change_handler: function(field, checked) {
										if((checked) && field.FromInterface){
											var HeartTones = (this.NeonatalSurvey_data.NeonatalSurveyParam.HeartTones) ? this.NeonatalSurvey_data.NeonatalSurveyParam.HeartTones : '00';
											var position = parseInt(field.id.substr(field.id.length - 1, 1));
											var value = checked.inputValue;
											HeartTones = HeartTones.split("");
											HeartTones[position - 1] = value;
											HeartTones = HeartTones.join("");
											if (!Ext.isEmpty(this.NeonatalSurvey_data.NeonatalSurveyParam.HeartTones) || (HeartTones != '00')) 
												this.NeonatalSurvey_data.NeonatalSurveyParam.HeartTones = HeartTones;									
											this.changedDatas = true;
										}
										field.FromInterface = true;
									}.createDelegate(this)		
								},
								//Шум
								{									
									layout:'column',
									id: 'swENSEW_HeartNoise_Panel',
									style: 'margin-bottom: 0px;',
									items:[
										{xtype:'label',text: 'Шум: характер',style: 'font-size: 10pt;  margin-left: 12pt; margin-top: 2pt; margin-bottom: 2px'},
										{									
											layout:'form',
											labelWidth:1,
											style: 'margin-top: 4px; margin-bottom: 2px;  margin-left: 4px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											items:[
												new Ext.form.RadioGroup({
													id:'swENSEW_HeartNoise1',
													labelSeparator: '',
													vertical: true,
													columns: 4,
													FromInterface: true,
													items: [
														{boxLabel: 'нет', name: 'HeartNoise1', inputValue: 0, width: 50}, 
														{boxLabel: 'систолический', name: 'HeartNoise1', inputValue: 1, width: 120}, 
														{boxLabel: 'диастолический', name: 'HeartNoise1', inputValue: 2, width: 130},
														{boxLabel: 'систоло-диастолический', name: 'HeartNoise1', inputValue: 3, width: 180}
													],
													listeners: {
														'change': function(field, checked) {
															win.findById('swENSEW_HeartNoise_Panel').change_handler(field, checked);
														}
													}
												})	
											]
										},
										{xtype:'label',text: 'интенсивность',style: 'font-size: 10pt;  margin-left: 12pt; margin-top: 2pt; margin-bottom: 2px'},
										{									
											layout:'form',
											labelWidth:1,
											style: 'margin-top: 4px;  margin-left: 4px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											items:[
												new Ext.form.RadioGroup({
													id:'swENSEW_HeartNoise2',
													labelSeparator: '',
													vertical: true,
													columns: 3,
													FromInterface: true,
													items: [
														{boxLabel: '---', name: 'HeartNoise2', inputValue: 0, width: 50}, 
														{boxLabel: 'интенсивный', name: 'HeartNoise2', inputValue: 1, width: 120}, 
														{boxLabel: 'неинтенсивный', name: 'HeartNoise2', inputValue: 2, width: 120}
													],
													listeners: {
														'change': function(field, checked) {
															win.findById('swENSEW_HeartNoise_Panel').change_handler(field, checked);
														}
													}
												})	
											]
										}										
									],
									change_handler: function(field, checked) {
										if((checked) && field.FromInterface){
											var HeartNoise = (this.NeonatalSurvey_data.NeonatalSurveyParam.HeartNoise) ? this.NeonatalSurvey_data.NeonatalSurveyParam.HeartNoise : '00';
											var position = parseInt(field.id.substr(field.id.length - 1, 1));
											var value = checked.inputValue;
											HeartNoise = HeartNoise.split("");
											HeartNoise[position - 1] = value;
											HeartNoise = HeartNoise.join("");
											if (!Ext.isEmpty(this.NeonatalSurvey_data.NeonatalSurveyParam.HeartNoise) || (HeartNoise != '00')) 
												this.NeonatalSurvey_data.NeonatalSurveyParam.HeartNoise = HeartNoise;									
											this.changedDatas = true;
										}
										field.FromInterface = true;
									}.createDelegate(this)		
								},
								//Пульс на a. radialis
								{									
									layout:'column',
									id: 'swENSEW_PulseRadialis_Panel',
									style: 'margin-bottom: 0px;',
									items:[
										{xtype:'label',text: 'Пульс на a. radialis: характер',style: 'font-size: 10pt;  margin-left: 12pt; margin-top: 2pt; margin-bottom: 2px'},
										{									
											layout:'form',
											labelWidth:1,
											style: 'margin-top: 4px; margin-bottom: 2px;  margin-left: 4px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											items:[
												new Ext.form.RadioGroup({
													id:'swENSEW_PulseRadialis1',
													labelSeparator: '',
													vertical: true,
													columns: 3,
													FromInterface: true,
													items: [
														{boxLabel: '---', name: 'PulseRadialis1', inputValue: 0, width: 50}, 
														{boxLabel: 'ритмичный', name: 'PulseRadialis1', inputValue: 1, width: 100}, 
														{boxLabel: 'неритмичный', name: 'PulseRadialis1', inputValue: 2, width: 100}
													],
													listeners: {
														'change': function(field, checked) {
															win.findById('swENSEW_PulseRadialis_Panel').change_handler(field, checked);
														}
													}
												})	
											]
										},
										{xtype:'label',text: 'наполнение',style: 'font-size: 10pt;  margin-left: 12pt; margin-top: 2pt; margin-bottom: 2px'},
										{									
											layout:'form',
											labelWidth:1,
											style: 'margin-top: 4px;  margin-left: 4px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											items:[
												new Ext.form.RadioGroup({
													id:'swENSEW_PulseRadialis2',
													labelSeparator: '',
													vertical: true,
													columns: 6,
													FromInterface: true,
													items: [
														{boxLabel: '---', name: 'PulseRadialis2', inputValue: 0, width: 50}, 
														{boxLabel: 'удовлетворительное', name: 'PulseRadialis2', inputValue: 1, width: 150}, 
														{boxLabel: 'сниженное', name: 'PulseRadialis2', inputValue: 2, width: 100},
														{boxLabel: 'плохое', name: 'PulseRadialis2', inputValue: 3, width: 80},
														{boxLabel: 'нитевидный', name: 'PulseRadialis2', inputValue: 4, width: 100},
														{boxLabel: 'не определяется', name: 'PulseRadialis2', inputValue: 5, width: 140}
													],
													listeners: {
														'change': function(field, checked) {
															win.findById('swENSEW_PulseRadialis_Panel').change_handler(field, checked);
														}
													}
												})	
											]
										}										
									],
									change_handler: function(field, checked) {
										if((checked) && field.FromInterface){
											var PulseRadialis = (this.NeonatalSurvey_data.NeonatalSurveyParam.PulseRadialis) ? this.NeonatalSurvey_data.NeonatalSurveyParam.PulseRadialis : '00';
											var position = parseInt(field.id.substr(field.id.length - 1, 1));
											var value = checked.inputValue;
											PulseRadialis = PulseRadialis.split("");
											PulseRadialis[position - 1] = value;
											PulseRadialis = PulseRadialis.join("");
											if (!Ext.isEmpty(this.NeonatalSurvey_data.NeonatalSurveyParam.PulseRadialis) || (PulseRadialis != '00')) 
												this.NeonatalSurvey_data.NeonatalSurveyParam.PulseRadialis = PulseRadialis;									
											this.changedDatas = true;
										}
										field.FromInterface = true;
									}.createDelegate(this)		
								},
								//Микроциркуляция
								{									
									layout:'column',
									items:[
										{xtype:'label',text: 'Микроциркуляция ',style: 'font-size: 10pt;  margin-left: 12pt; margin-top: 2pt; margin-bottom: 2px'},											
										{									
											layout:'form',
											labelWidth:1,
											style: 'margin-top: 4px;  margin-left: 4px; margin-bottom: 2px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											items:[
												new Ext.form.RadioGroup({
													id:'swENSEW_Microcirculation',
													labelSeparator: '',
													vertical: true,
													columns: 3,
													FromInterface: true,
													items: [
														{boxLabel: '---', name: 'Microcirculation', inputValue: 0, width: 50}, 
														{boxLabel: 'удовлетворительная', name: 'Microcirculation', inputValue: 1, width: 150}, 
														{boxLabel: 'нарушена', name: 'Microcirculation', inputValue: 2, width: 80}
													],
													listeners: {
														'change': function(field, checked) {
															if((checked) && field.FromInterface){
																if ((this.NeonatalSurvey_data.NeonatalSurveyParam.Microcirculation) || (String(checked.inputValue) != '0')) {
																	this.NeonatalSurvey_data.NeonatalSurveyParam.Microcirculation = checked.inputValue;									
																	this.changedDatas = true;
																}
															}
															field.FromInterface = true;
														}.createDelegate(this)
													}
												})	
											]
										},
										//Время наполнения капилляров
										{xtype:'label',text: 'Время наполнения капилляров: на грудине ',style: 'font-size: 10pt;  margin-left: 12pt; margin-top: 2pt; margin-bottom: 2px'},											
										new Ext.form.NumberField({
											value: 0,
											id: 'swENSEW_TimeFillingCapilСhest',
											fieldLabel:'',
											labelSeparator: '',
											enableKeyEvents: true,
											width: 60,
											style: 'margin-left: 2pt; margin-top: 2pt',
											//plugins:[ new Ext.ux.InputTextMask('99.9', true) ],
											listeners:{
												'keyup':function (obj, e) {
													if (!Ext.isEmpty(obj.getValue()))
														this.NeonatalSurvey_data.NeonatalSurveyParam.TimeFillingCapilСhest = obj.getValue();													
													else delete this.NeonatalSurvey_data.NeonatalSurveyParam.TimeFillingCapilСhest;												
													this.changedDatas = true;
												}.createDelegate(this)
											}
										}),
										{xtype:'label',text: 'сек,         на конечностях ',style: 'font-size: 10pt;  margin-left: 2pt; margin-top: 2pt'},											
										new Ext.form.NumberField({
											value: 0,
											id: 'swENSEW_TimeFillingCapilExtr',
											fieldLabel:'',
											labelSeparator: '',
											enableKeyEvents: true,
											width: 60,
											style: 'margin-left: 2pt; margin-top: 2pt',
											//plugins:[ new Ext.ux.InputTextMask('99.9', true) ],
											listeners:{
												'keyup':function (obj, e) {
													if (!Ext.isEmpty(obj.getValue()))
														this.NeonatalSurvey_data.NeonatalSurveyParam.TimeFillingCapilExtr = obj.getValue();													
													else delete this.NeonatalSurvey_data.NeonatalSurveyParam.TimeFillingCapilExtr;												
													this.changedDatas = true;
												}.createDelegate(this)
											}
										}),
										{xtype:'label',text: 'сек',style: 'font-size: 10pt;  margin-left: 2pt; margin-top: 2pt'}

									]
								},
							]
						},
						//Панель Язык
						{
							id: 'swENSEW_Tongue_Panel',
							xtype: 'fieldset',
							autoHeight: true,
							title: langs('Язык'),
							collapsible: true,
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
									labelWidth:1,
									style: 'margin-top: 4px;  margin-left: 4px; margin-bottom: 2px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
									items:[
										new Ext.form.RadioGroup({
											id:'swENSEW_TongueState',
											labelSeparator: '',
											vertical: true,
											columns: 3,
											FromInterface: true,
											items: [
												{boxLabel: '---', name: 'TongueState', inputValue: 0, width: 50}, 
												{boxLabel: 'сухой', name: 'TongueState', inputValue: 1, width: 60}, 
												{boxLabel: 'влажный', name: 'TongueState', inputValue: 2, width: 80}
											],
											listeners: {
												'change': function(field, checked) {
													if((checked) && field.FromInterface){
														if ((this.NeonatalSurvey_data.NeonatalSurveyParam.TongueState) || (String(checked.inputValue) != '0')) {
															this.NeonatalSurvey_data.NeonatalSurveyParam.TongueState = checked.inputValue;									
															this.changedDatas = true;
														}
													}
													field.FromInterface = true;
												}.createDelegate(this)
											}
										})	
									]
								},
								//Язык - налёт
								{									
									layout:'form',
									labelWidth:130,
									items:[
										{
											fieldLabel: 'Наличие налёта',
											labelSeparator: '',
											name: 'TongueScurf',
											id: 'swENSEW_TongueScurf',
											xtype: 'checkbox',
											checked: false,
											listeners: {
												'check': function(chb, checked ) {
													if ((this.NeonatalSurvey_data.NeonatalSurveyParam.TongueScurf) || (checked)) {
														this.NeonatalSurvey_data.NeonatalSurveyParam.TongueScurf = checked ? 2 : 1;		
														this.changedDatas = true;
														this.findById('swENSEW_TongueScurfSeverity').setDisabled(!checked);  //BOB - 06.05.2020
														this.findById('swENSEW_TongueScurfColor').setDisabled(!checked);  //BOB - 06.05.2020
														if (!checked) {
															this.findById('swENSEW_TongueScurfSeverity').setValue('');
															delete this.NeonatalSurvey_data.NeonatalSurveyParam.TongueScurfSeverity;
															this.findById('swENSEW_TongueScurfColor').setValue('');
															delete this.NeonatalSurvey_data.NeonatalSurveyParam.TongueScurfColor;
														}
													}
												}.createDelegate(this)
											}		
										}
									]
								},
								{									
									layout:'form',
									labelWidth:100,
									items:[
										{
											allowBlank: true,
											fieldLabel: 'Выраженность',
											labelSeparator: '',
											name: 'swENSEW_TongueScurfSeverity',
											id: 'swENSEW_TongueScurfSeverity',
											width: 380,
											value:'',
											style: 'margin-top: 4px;  margin-left: 4px; ',
											xtype: 'textfield',
											listeners:{
												'change':function (field, newValue, oldValue) {
													this.NeonatalSurvey_data.NeonatalSurveyParam.TongueScurfSeverity = newValue;
													this.changedDatas = true;
												}.createDelegate(this)
											}
										}
									]
								},
								{									
									layout:'form',
									labelWidth:50,
									items:[
										{
											allowBlank: true,
											fieldLabel: 'Цвет',
											labelSeparator: '',
											name: 'swENSEW_TongueScurfColor',
											id: 'swENSEW_TongueScurfColor',
											width: 380,
											value:'',
											style: 'margin-top: 4px;  margin-left: 4px; ',
											xtype: 'textfield',
											listeners:{
												'change':function (field, newValue, oldValue) {
													this.NeonatalSurvey_data.NeonatalSurveyParam.TongueScurfColor = newValue;
													this.changedDatas = true;
												}.createDelegate(this)
											}
										}
									]
								}
							]
						},
						//Панель Отделяемое из желудка
						{
							id: 'swENSEW_StomachDischarge_Panel',
							xtype: 'fieldset',
							autoHeight: true,
							title: langs('Отделяемое из желудка'),
							collapsible: true,
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
									labelWidth:1,
									style: 'margin-top: 4px;  margin-left: 54px; margin-bottom: 2px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
									items:[
										new Ext.form.RadioGroup({
											id:'swENSEW_StomachDischarge1',
											labelSeparator: '',
											vertical: true,
											columns: 1,
											FromInterface: true,
											items: [
												{boxLabel: 'нет', name: 'StomachDischarge1', inputValue: 0, width: 50}, 
												{boxLabel: 'слизистое', name: 'StomachDischarge1', inputValue: 1, width: 120}, 
												{boxLabel: 'непереваренная пища', name: 'StomachDischarge1', inputValue: 2, width: 120},
												{boxLabel: 'с примесью крови', name: 'StomachDischarge1', inputValue: 3, width: 150},
												{boxLabel: 'с примесью желчи', name: 'StomachDischarge1', inputValue: 4, width: 150},
												{boxLabel: 'другое', name: 'StomachDischarge1', inputValue: 5, width: 120}
											],
											listeners: {
												'change': function(field, checked) {
													if(field.FromInterface) win.findById('swENSEW_StomachDischarge_Panel').change_handler(field, checked);
													field.FromInterface = true;
												}
											}
										})	
									]
								},
								{									
									layout:'form',
									//width: 850,
									items:[
										//с примесью крови
										{									
											layout:'form',
											width: 250,
											labelWidth:1,
											style: 'margin-top: 80px;  margin-left: 0px; margin-bottom: 0px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											items:[
												new Ext.form.RadioGroup({
													id:'swENSEW_StomachDischarge2',
													labelSeparator: '',
													vertical: true,
													columns: 4,
													FromInterface: true,
													items: [
														{boxLabel: '---', name: 'StomachDischarge2', inputValue: 0, width: 50, hidden: true}, 
														{boxLabel: 'алая', name: 'StomachDischarge2', inputValue: 1, width: 70}, 
														{boxLabel: '~кофейная гуща~', name: 'StomachDischarge2', inputValue: 2, width: 150}
													],
													listeners: {
														'change': function(field, checked) {
															if(field.FromInterface) win.findById('swENSEW_StomachDischarge_Panel').change_handler(field, checked);
															field.FromInterface = true;
														}
													}
												})	
											]
										},
										//с примесью желчи
										{									
											layout:'form',
											width: 250,
											labelWidth:1,
											style: 'margin-top: 0px;  margin-left: 0px; margin-bottom: 0px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											items:[
												new Ext.form.RadioGroup({
													id:'swENSEW_StomachDischarge3',
													labelSeparator: '',
													vertical: true,
													columns: 4,
													FromInterface: true,
													items: [
														{boxLabel: '---', name: 'StomachDischarge3', inputValue: 0, width: 50, hidden: true}, 
														{boxLabel: 'светлая', name: 'StomachDischarge3', inputValue: 1, width: 70}, 
														{boxLabel: 'зелёная', name: 'StomachDischarge3', inputValue: 2, width: 110}
													],
													listeners: {
														'change': function(field, checked) {
															if(field.FromInterface) win.findById('swENSEW_StomachDischarge_Panel').change_handler(field, checked);
															field.FromInterface = true;
														}
													}
												})	
											]
										},
										{									
											layout:'form',
											labelWidth:5,
											items:[
												{
													allowBlank: true,
													fieldLabel: '',
													labelSeparator: '',
													name: 'swENSEW_StomachDischargeTxt',
													id: 'swENSEW_StomachDischargeTxt',
													width: 750,
													value:'',
													style: 'margin-top: 4px;  margin-left: 4px; ',
													xtype: 'textfield',
													listeners:{
														'change':function (field, newValue, oldValue) {
															this.NeonatalSurvey_data.NeonatalSurveyParam.StomachDischargeTxt = newValue;
															this.changedDatas = true;
														}.createDelegate(this)
													}
												}
											]
										}		
									]
								}
							],
							change_handler: function(field, checked) {
								if(checked){
									var StomachDischarge = (this.NeonatalSurvey_data.NeonatalSurveyParam.StomachDischarge) ? this.NeonatalSurvey_data.NeonatalSurveyParam.StomachDischarge : '000';
									var position = parseInt(field.id.substr(field.id.length - 1, 1));
									var value = checked.inputValue;
									//если меняется выбранный в первой группе то обнуляются вторая и третья
									if (position == 1) {
										this.findById('swENSEW_StomachDischarge2').items.items[0].setValue(true);		
										for (var i = 1; i < this.findById('swENSEW_StomachDischarge2').items.items.length; i++  ) this.findById('swENSEW_StomachDischarge2').items.items[i].setValue(false);						
										this.findById('swENSEW_StomachDischarge3').items.items[0].setValue(true);		
										for (var i = 1; i < this.findById('swENSEW_StomachDischarge3').items.items.length; i++  ) this.findById('swENSEW_StomachDischarge3').items.items[i].setValue(false);
										this.findById('swENSEW_StomachDischarge2').disable();						
										this.findById('swENSEW_StomachDischarge3').disable();	
										if(value == 3)	this.findById('swENSEW_StomachDischarge2').enable();	
										else if (value == 4)	this.findById('swENSEW_StomachDischarge3').enable();			
									}
									StomachDischarge = StomachDischarge.split("");
									StomachDischarge[position - 1] = value;
									StomachDischarge = StomachDischarge.join("");
									if (!Ext.isEmpty(this.NeonatalSurvey_data.NeonatalSurveyParam.StomachDischarge) || (StomachDischarge != '000')) 
										this.NeonatalSurvey_data.NeonatalSurveyParam.StomachDischarge = StomachDischarge;									
									this.changedDatas = true;
								}
							}.createDelegate(this)
						},
						//Панель Живот    
						{
							id: 'swENSEW_Stomach_Panel',
							xtype: 'fieldset',
							autoHeight: true,
							title: langs('Живот'),
							collapsible: true,
							layout: 'form',
							labelWidth:80,
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
								//Дефекты передней брюшной стенки
								{									
									layout:'column',
									style: 'margin-bottom: 0px;',
									items:[
										{xtype:'label',text: 'Дефекты передней брюшной стенки',style: 'font-size: 10pt;  margin-left: 12pt; margin-top: 2pt; margin-bottom: 2px'},
										{									
											layout:'form',
											labelWidth:1,
											style: 'margin-top: 4px; margin-bottom: 2px;  margin-left: 4px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											items:[
												new Ext.form.RadioGroup({
													id:'swENSEW_AnterAbdomWallDef',
													labelSeparator: '',
													vertical: true,
													columns: 6,
													FromInterface: true,
													items: [
														{boxLabel: 'нет', name: 'AnterAbdomWallDef', inputValue: 0, width: 50}, 
														{boxLabel: 'гастрошизис', name: 'AnterAbdomWallDef', inputValue: 1, width: 110}, 
														{boxLabel: 'омфалоцеле', name: 'AnterAbdomWallDef', inputValue: 2, width: 120},
														{boxLabel: 'паховые грыжи', name: 'AnterAbdomWallDef', inputValue: 3, width: 130},
														{boxLabel: 'пупочные грыжи', name: 'AnterAbdomWallDef', inputValue: 4, width: 150},
														{boxLabel: 'экстрофия мочевого пузыря', name: 'AnterAbdomWallDef', inputValue: 5, width: 200}
													],
													listeners: {
														'change': function(field, checked) {
															if((checked) && field.FromInterface){
																if ((this.NeonatalSurvey_data.NeonatalSurveyParam.AnterAbdomWallDef) || (String(checked.inputValue) != '0')) {
																	this.NeonatalSurvey_data.NeonatalSurveyParam.AnterAbdomWallDef = checked.inputValue;									
																	this.changedDatas = true;
																}
															}
															field.FromInterface = true;
														}.createDelegate(this)
													}
												})	
											]
										}
									]	
								},
								//Вздутие
								{									
									layout:'column',
									style: 'margin-bottom: 0px;',
									items:[
										{xtype:'label',text: 'Вздутие',style: 'font-size: 10pt;  margin-left: 12pt; margin-top: 2pt; margin-bottom: 2px'},
										{									
											layout:'form',
											labelWidth:1,
											style: 'margin-top: 4px;  margin-left: 4px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											items:[
												new Ext.form.RadioGroup({
													id:'swENSEW_Swelling',
													labelSeparator: '',
													vertical: true,
													columns: 3,
													FromInterface: true,
													items: [
														{boxLabel: 'нет', name: 'Swelling', inputValue: 0, width: 50}, 
														{boxLabel: 'резко вздут', name: 'Swelling', inputValue: 1, width: 110}, 
														{boxLabel: 'умеренно вздут', name: 'Swelling', inputValue: 2, width: 120}
													],
													listeners: {
														'change': function(field, checked) {
															if((checked) && field.FromInterface){
																if ((this.NeonatalSurvey_data.NeonatalSurveyParam.Swelling) || (String(checked.inputValue) != '0')) {
																	this.NeonatalSurvey_data.NeonatalSurveyParam.Swelling = checked.inputValue;									
																	this.changedDatas = true;
																}
															}
															field.FromInterface = true;
														}.createDelegate(this)
													}
												})	
											]
										}
									]	
								},
								//Гепатомегалия 
								{									
									layout:'column',
									items:[
										{									
											layout:'form',
											style: 'margin-top: 1pt;',
											labelWidth:100,
											items:[
												{
													fieldLabel: 'Гепатомегалия',
													labelSeparator: '',
													name: 'Hepatomegaly',
													id: 'swENSEW_Hepatomegaly',
													xtype: 'checkbox',
													checked: false,
													listeners: {
														'check': function(chb, checked ) {
															if ((this.NeonatalSurvey_data.NeonatalSurveyParam.Hepatomegaly) || (checked)) {
																this.NeonatalSurvey_data.NeonatalSurveyParam.Hepatomegaly = checked ? 2 : 1;		
																this.changedDatas = true;
																this.findById('swENSEW_HepatomegalySize').setDisabled(!checked);  //BOB - 06.05.2020
																this.findById('swENSEW_HepatomegalyPalpFeat').setDisabled(!checked);  //BOB - 06.05.2020
																if (!checked) {
																	this.findById('swENSEW_HepatomegalySize').setValue('');
																	delete this.NeonatalSurvey_data.NeonatalSurveyParam.HepatomegalySize;
																	this.findById('swENSEW_HepatomegalyPalpFeat').setValue('');
																	delete this.NeonatalSurvey_data.NeonatalSurveyParam.HepatomegalyPalpFeat;
																}
															}
														}.createDelegate(this)
													}		
												}
											]
										},
										{									
											layout:'column',
											width: 280,
											items:[
												{xtype:'label',text: 'Размер от края реберной дуги',style: 'font-size: 10pt;  margin-left: 12pt; margin-top: 2pt; margin-bottom: 2px'},
												new Ext.form.NumberField({
													value: 0,
													id: 'swENSEW_HepatomegalySize',
													fieldLabel:'',
													labelSeparator: '',
													enableKeyEvents: true,
													width: 60,
													style: 'margin-left: 2pt; margin-top: 2pt',
													//plugins:[ new Ext.ux.InputTextMask('99.9', true) ],
													listeners:{
														'keyup':function (obj, e) {
															if (!Ext.isEmpty(obj.getValue()))
																this.NeonatalSurvey_data.NeonatalSurveyParam.HepatomegalySize = obj.getValue();													
															else delete this.NeonatalSurvey_data.NeonatalSurveyParam.HepatomegalySize;												
															this.changedDatas = true;
														}.createDelegate(this)
													}
												}),
												{xtype:'label',text: 'см',style: 'font-size: 10pt;  margin-left: 2pt; margin-top: 2pt; margin-bottom: 2px'}
											]
										},
										{									
											layout:'form',
											labelWidth:150,
											style: 'margin-top: 1px; ',
											items:[
												{
													allowBlank: true,
													fieldLabel: 'Особенности пальпации',
													labelSeparator: '',
													name: 'swENSEW_HepatomegalyPalpFeat',
													id: 'swENSEW_HepatomegalyPalpFeat',
													width: 700,
													value:'',
													style: 'margin-top: 0px;  margin-left: 4px; ',
													xtype: 'textfield',
													listeners:{
														'change':function (field, newValue, oldValue) {
															this.NeonatalSurvey_data.NeonatalSurveyParam.HepatomegalyPalpFeat = newValue;
															this.changedDatas = true;
														}.createDelegate(this)
													}
												}
											]
										}
									]
								},
								//Спленомегалия 
								{									
									layout:'column',
									items:[
										{									
											layout:'form',
											style: 'margin-top: 1pt;',
											labelWidth:100,
											items:[
												{
													fieldLabel: 'Спленомегалия',
													labelSeparator: '',
													name: 'Splenomegaly',
													id: 'swENSEW_Splenomegaly',
													xtype: 'checkbox',
													checked: false,
													listeners: {
														'check': function(chb, checked ) {
															if ((this.NeonatalSurvey_data.NeonatalSurveyParam.Splenomegaly) || (checked)) {
																this.NeonatalSurvey_data.NeonatalSurveyParam.Splenomegaly = checked ? 2 : 1;		
																this.changedDatas = true;
																this.findById('swENSEW_SplenomegalySize').setDisabled(!checked);  //BOB - 06.05.2020
																this.findById('swENSEW_SplenomegalyPalpFeat').setDisabled(!checked);  //BOB - 06.05.2020
																if (!checked) {
																	this.findById('swENSEW_SplenomegalySize').setValue('');
																	delete this.NeonatalSurvey_data.NeonatalSurveyParam.SplenomegalySize;
																	this.findById('swENSEW_SplenomegalyPalpFeat').setValue('');
																	delete this.NeonatalSurvey_data.NeonatalSurveyParam.SplenomegalyPalpFeat;
																}
															}
														}.createDelegate(this)
													}		
												}
											]
										},
										{									
											layout:'column',
											width: 280,
											items:[
												{xtype:'label',text: 'Размер от края реберной дуги',style: 'font-size: 10pt;  margin-left: 12pt; margin-top: 2pt; margin-bottom: 2px'},
												new Ext.form.NumberField({
													value: 0,
													id: 'swENSEW_SplenomegalySize',
													fieldLabel:'',
													labelSeparator: '',
													enableKeyEvents: true,
													width: 60,
													style: 'margin-left: 2pt; margin-top: 2pt',
													//plugins:[ new Ext.ux.InputTextMask('99.9', true) ],
													listeners:{
														'keyup':function (obj, e) {
															if (!Ext.isEmpty(obj.getValue()))
																this.NeonatalSurvey_data.NeonatalSurveyParam.SplenomegalySize = obj.getValue();													
															else delete this.NeonatalSurvey_data.NeonatalSurveyParam.SplenomegalySize;												
															this.changedDatas = true;
														}.createDelegate(this)
													}
												}),
												{xtype:'label',text: 'см',style: 'font-size: 10pt;  margin-left: 2pt; margin-top: 2pt; margin-bottom: 2px'}
											]
										},
										{									
											layout:'form',
											labelWidth:150,
											style: 'margin-top: 1px; ',
											items:[
												{
													allowBlank: true,
													fieldLabel: 'Особенности пальпации',
													labelSeparator: '',
													name: 'swENSEW_SplenomegalyPalpFeat',
													id: 'swENSEW_SplenomegalyPalpFeat',
													width: 700,
													value:'',
													style: 'margin-top: 0px;  margin-left: 4px; ',
													xtype: 'textfield',
													listeners:{
														'change':function (field, newValue, oldValue) {
															this.NeonatalSurvey_data.NeonatalSurveyParam.SplenomegalyPalpFeat = newValue;
															this.changedDatas = true;
														}.createDelegate(this)
													}
												}
											]
										}
									]
								}
							]
						},
						//Панель Пальпация   
						{
							id: 'swENSEW_Palpation_Panel',
							xtype: 'fieldset',
							autoHeight: true,
							title: langs('Пальпация'),
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
								//Напряжение, Болезненность, Доступность пальпации 
								{									
									layout:'column',
									items:[
										{xtype:'label',text: 'Напряжение',style: 'font-size: 10pt;  margin-left: 2pt; margin-top: 2pt; margin-bottom: 2px'},
										{									
											layout:'form',
											labelWidth:1,
											style: 'margin-top: 4px; margin-bottom: 2px;  margin-left: 4px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											items:[
												new Ext.form.RadioGroup({
													id:'swENSEW_Palpation1',
													labelSeparator: '',
													vertical: true,
													columns: 3,
													FromInterface: true,
													items: [
														{boxLabel: '---', name: 'Palpation1', inputValue: 0, width: 50}, 
														{boxLabel: 'мягкий', name: 'Palpation1', inputValue: 1, width: 70}, 
														{boxLabel: 'напряжен', name: 'Palpation1', inputValue: 2, width: 80}
													],
													listeners: {
														'change': function(field, checked) {
															win.findById('swENSEW_Palpation_Panel').change_handler(field, checked);
														}
													}
												})	
											]
										},
										{xtype:'label',text: 'Болезненность',style: 'font-size: 10pt;  margin-left: 2pt; margin-top: 2pt'},
										{									
											layout:'form',
											labelWidth:1,
											style: 'margin-top: 4px;  margin-left: 4px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											items:[
												new Ext.form.RadioGroup({
													id:'swENSEW_Palpation2',
													labelSeparator: '',
													vertical: true,
													columns: 3,
													FromInterface: true,
													items: [
														{boxLabel: '---', name: 'Palpation2', inputValue: 0, width: 70}, 
														{boxLabel: 'болезненный', name: 'Palpation2', inputValue: 1, width: 120}, 
														{boxLabel: 'безболезненный', name: 'Palpation2', inputValue: 2, width: 120}
													],
													listeners: {
														'change': function(field, checked) {
															win.findById('swENSEW_Palpation_Panel').change_handler(field, checked);
														}
													}
												})	
											]
										},
										{xtype:'label',text: 'Доступность пальпации',style: 'font-size: 10pt;  margin-left: 2pt; margin-top: 2pt'},
										{									
											layout:'form',
											labelWidth:1,
											style: 'margin-top: 4px;  margin-left: 4px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											items:[
												new Ext.form.RadioGroup({
													id:'swENSEW_Palpation3',
													labelSeparator: '',
													vertical: true,
													columns: 3,
													FromInterface: true,
													items: [
														{boxLabel: '---', name: 'Palpation3', inputValue: 0, width: 70}, 
														{boxLabel: 'доступен', name: 'Palpation3', inputValue: 1, width: 120}, 
														{boxLabel: 'недоступен', name: 'Palpation3', inputValue: 2, width: 120}
													],
													listeners: {
														'change': function(field, checked) {
															win.findById('swENSEW_Palpation_Panel').change_handler(field, checked);
														}
													}
												})	
											]
										}										
									]
								},
								//Перистальтика....
								{									
									layout:'column',
									items:[
										{xtype:'label',text: 'Перистальтика',style: 'font-size: 10pt;  margin-left: 2pt; margin-top: 2pt; margin-bottom: 2px'},
										{									
											layout:'form',
											labelWidth:1,
											style: 'margin-top: 4px; margin-bottom: 2px;  margin-left: 4px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											items:[
												new Ext.form.RadioGroup({
													id:'swENSEW_Peristalsis',
													labelSeparator: '',
													vertical: true,
													columns: 1,
													FromInterface: true,
													items: [
														{boxLabel: '---', name: 'Peristalsis', inputValue: 0, width: 50}, 
														{boxLabel: 'обычной звучности', name: 'Peristalsis', inputValue: 1, width: 150}, 
														{boxLabel: 'усилена', name: 'Peristalsis', inputValue: 2, width: 80},
														{boxLabel: 'ослаблена', name: 'Peristalsis', inputValue: 3, width: 100},
														{boxLabel: 'отсутствует', name: 'Peristalsis', inputValue: 4, width: 110}
													],
													listeners: {
														'change': function(field, checked) {
															if((checked) && field.FromInterface){
																if ((this.NeonatalSurvey_data.NeonatalSurveyParam.Peristalsis) || (String(checked.inputValue) != '0')) {
																	this.NeonatalSurvey_data.NeonatalSurveyParam.Peristalsis = checked.inputValue;									
																	this.changedDatas = true;
																}
															}
															field.FromInterface = true;
														}.createDelegate(this)
													}
												})	
											]
										},
										{									
											layout:'form',
											labelWidth: 300,
											items:[
												{
													fieldLabel: 'Петли кишечника контурируют',
													labelSeparator: '',
													name: 'BowLoopContoured',
													id: 'swENSEW_BowLoopContoured',
													xtype: 'checkbox',
													checked: false,
													listeners: {
														'check': function(chb, checked ) {
															if ((this.NeonatalSurvey_data.NeonatalSurveyParam.BowLoopContoured) || (checked)) { 
																this.NeonatalSurvey_data.NeonatalSurveyParam.BowLoopContoured = checked ? 2 : 1;									
																this.changedDatas = true;
															}
														}.createDelegate(this)
													}		
												},
												{
													fieldLabel: 'Отечность передней брюшной стенки',
													labelSeparator: '',
													name: 'AnterAbdomWallSwell',
													id: 'swENSEW_AnterAbdomWallSwell',
													xtype: 'checkbox',
													checked: false,
													listeners: {
														'check': function(chb, checked ) {
															if ((this.NeonatalSurvey_data.NeonatalSurveyParam.AnterAbdomWallSwell) || (checked)) {
																this.NeonatalSurvey_data.NeonatalSurveyParam.AnterAbdomWallSwell = checked ? 2 : 1;									
																this.changedDatas = true;
															}
														}.createDelegate(this)
													}		
												},
												{
													fieldLabel: 'Гиперемия передней брюшной стенки',
													labelSeparator: '',
													name: 'AnterAbdomWallHyper',
													id: 'swENSEW_AnterAbdomWallHyper',
													xtype: 'checkbox',
													checked: false,
													listeners: {
														'check': function(chb, checked ) {
															if ((this.NeonatalSurvey_data.NeonatalSurveyParam.AnterAbdomWallHyper) || (checked)) {
																this.NeonatalSurvey_data.NeonatalSurveyParam.AnterAbdomWallHyper = checked ? 2 : 1;									
																this.changedDatas = true;
															}
														}.createDelegate(this)
													}		
												}
											]
										}
									]
								}
							],
							change_handler: function(field, checked) {
								if((checked) && field.FromInterface){
									var Palpation = (this.NeonatalSurvey_data.NeonatalSurveyParam.Palpation) ? this.NeonatalSurvey_data.NeonatalSurveyParam.Palpation : '000';
									var position = parseInt(field.id.substr(field.id.length - 1, 1));
									var value = checked.inputValue;
									Palpation = Palpation.split("");
									Palpation[position - 1] = value;
									Palpation = Palpation.join("");
									if (!Ext.isEmpty(this.NeonatalSurvey_data.NeonatalSurveyParam.Palpation) || (Palpation != '000')) 
										this.NeonatalSurvey_data.NeonatalSurveyParam.Palpation = Palpation;									
									this.changedDatas = true;
								}
								field.FromInterface = true;
							}.createDelegate(this)
						},
						//Аномалии брюшной полости   
						{
							id: 'swENSEW_Abdominal_Panel',
							xtype: 'fieldset',
							autoHeight: true,
							title: langs('Аномалии брюшной полости'),
							collapsible: true,
							layout: 'form',
							labelWidth: 250,
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
								//Объемные образования брюшной полости
								{
									fieldLabel: 'Объемные образования брюшной полости',
									labelSeparator: '',
									name: 'VolFormAbdom',
									id: 'swENSEW_VolFormAbdom',
									xtype: 'checkbox',
									checked: false,
									listeners: {
										'check': function(chb, checked ) {
											if ((this.NeonatalSurvey_data.NeonatalSurveyParam.VolFormAbdom) || (checked)) {
												this.NeonatalSurvey_data.NeonatalSurveyParam.VolFormAbdom = checked ? 2 : 1;									
												this.changedDatas = true;
												this.findById('swENSEW_VolFormAbdomLocTxt').setDisabled(!checked);  //BOB - 06.05.2020
												this.findById('swENSEW_VolFormAbdomDensTxt').setDisabled(!checked);  //BOB - 06.05.2020
												this.findById('swENSEW_VolFormAbdomPar1').setDisabled(!checked);  //BOB - 06.05.2020
												this.findById('swENSEW_VolFormAbdomPar2').setDisabled(!checked);  //BOB - 06.05.2020
												this.findById('swENSEW_VolFormAbdomPar3').setDisabled(!checked);  //BOB - 06.05.2020
												this.findById('swENSEW_VolFormAbdomPar4').setDisabled(!checked);  //BOB - 06.05.2020
												if (!checked) {
													this.findById('swENSEW_VolFormAbdomLocTxt').setValue('');
													delete this.NeonatalSurvey_data.NeonatalSurveyParam.VolFormAbdomLocTxt;
													this.findById('swENSEW_VolFormAbdomDensTxt').setValue('');
													delete this.NeonatalSurvey_data.NeonatalSurveyParam.VolFormAbdomDensTxt;
													this.findById('swENSEW_VolFormAbdomPar1').items.items[0].setValue(true);
													for (var i = 1; i < this.findById('swENSEW_VolFormAbdomPar1').items.items.length; i++  ) this.findById('swENSEW_VolFormAbdomPar1').items.items[i].setValue(false);
													this.findById('swENSEW_VolFormAbdomPar2').items.items[0].setValue(true);
													for (var i = 1; i < this.findById('swENSEW_VolFormAbdomPar2').items.items.length; i++  ) this.findById('swENSEW_VolFormAbdomPar2').items.items[i].setValue(false);
													this.findById('swENSEW_VolFormAbdomPar3').items.items[0].setValue(true);
													for (var i = 1; i < this.findById('swENSEW_VolFormAbdomPar3').items.items.length; i++  ) this.findById('swENSEW_VolFormAbdomPar3').items.items[i].setValue(false);
													this.findById('swENSEW_VolFormAbdomPar4').items.items[0].setValue(true);
													for (var i = 1; i < this.findById('swENSEW_VolFormAbdomPar4').items.items.length; i++  ) this.findById('swENSEW_VolFormAbdomPar4').items.items[i].setValue(false);
												}
											}
										}.createDelegate(this)
									}		
								},
								//Локализация 
								{									
									layout:'column',
									items:[
										{xtype:'label',text: 'Локализация',style: 'font-size: 10pt;  margin-left: 2pt; margin-top: 2pt; margin-bottom: 2px'},
										{									
											layout:'form',
											labelWidth:1,
											style: 'margin-top: 4px;  margin-left: 4px; margin-bottom: 2px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											items:[
												new Ext.form.RadioGroup({
													id:'swENSEW_VolFormAbdomPar1',
													labelSeparator: '',
													vertical: true,
													columns: 5,
													FromInterface: true,
													items: [
														{boxLabel: '---', name: 'VolFormAbdomPar1', inputValue: 0, width: 50}, 
														{boxLabel: 'односторонние справа', name: 'VolFormAbdomPar1', inputValue: 1, width: 170}, 
														{boxLabel: 'односторонние слева', name: 'VolFormAbdomPar1', inputValue: 2, width: 150},
														{boxLabel: 'двусторонние', name: 'VolFormAbdomPar1', inputValue: 3, width: 110},
														{boxLabel: 'иные', name: 'VolFormAbdomPar1', inputValue: 4, width: 50}
													],
													listeners: {
														'change': function(field, checked) {
															win.findById('swENSEW_Abdominal_Panel').change_VolFormAbdomPar(field, checked);
														}
													}
												})	
											]
										},
										{
											allowBlank: true,
											fieldLabel: '',
											labelSeparator: '',
											name: 'swENSEW_VolFormAbdomLocTxt',
											id: 'swENSEW_VolFormAbdomLocTxt',
											width: 670,
											value:'',
											style: 'margin-top: 4px;  margin-left: 4px; ',
											xtype: 'textfield',
											listeners:{
												'change':function (field, newValue, oldValue) {
													this.NeonatalSurvey_data.NeonatalSurveyParam.VolFormAbdomLocTxt = newValue;
													this.changedDatas = true;
												}.createDelegate(this)
											}
										}
									]
								},
								//Плотность  
								{									
									layout:'column',
									items:[
										{xtype:'label',text: 'Плотность',style: 'font-size: 10pt;  margin-left: 2pt; margin-top: 2pt; margin-bottom: 2px'},
										{									
											layout:'form',
											labelWidth:1,
											style: 'margin-top: 4px;  margin-left: 4px; margin-bottom: 2px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											items:[
												new Ext.form.RadioGroup({
													id:'swENSEW_VolFormAbdomPar2',
													labelSeparator: '',
													vertical: true,
													columns: 4,
													FromInterface: true,
													items: [
														{boxLabel: '---', name: 'VolFormAbdomPar2', inputValue: 0, width: 50}, 
														{boxLabel: 'плотное', name: 'VolFormAbdomPar2', inputValue: 1, width: 90}, 
														{boxLabel: 'мягкое', name: 'VolFormAbdomPar2', inputValue: 2, width: 80},
														{boxLabel: 'иные', name: 'VolFormAbdomPar2', inputValue: 3, width: 50}
													],
													listeners: {
														'change': function(field, checked) {
															win.findById('swENSEW_Abdominal_Panel').change_VolFormAbdomPar(field, checked);
														}
													}
												})	
											]
										},
										{
											allowBlank: true,
											fieldLabel: '',
											labelSeparator: '',
											name: 'swENSEW_VolFormAbdomDensTxt',
											id: 'swENSEW_VolFormAbdomDensTxt',
											width: 940,
											value:'',
											style: 'margin-top: 4px;  margin-left: 4px; ',
											xtype: 'textfield',
											listeners:{
												'change':function (field, newValue, oldValue) {
													this.NeonatalSurvey_data.NeonatalSurveyParam.VolFormAbdomDensTxt = newValue;
													this.changedDatas = true;
												}.createDelegate(this)
											}
										}
									]
								},
								//Болезненность   Подвижность 
								{									
									layout:'column',
									items:[
										{xtype:'label',text: 'Болезненность',style: 'font-size: 10pt;  margin-left: 2pt; margin-top: 2pt; margin-bottom: 2px'},
										{									
											layout:'form',
											labelWidth:1,
											style: 'margin-top: 4px;  margin-left: 4px; margin-bottom: 2px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											items:[
												new Ext.form.RadioGroup({
													id:'swENSEW_VolFormAbdomPar3',
													labelSeparator: '',
													vertical: true,
													columns: 3,
													FromInterface: true,
													items: [
														{boxLabel: '---', name: 'VolFormAbdomPar3', inputValue: 0, width: 50}, 
														{boxLabel: 'болезненное', name: 'VolFormAbdomPar3', inputValue: 1, width: 110}, 
														{boxLabel: 'безболезненное', name: 'VolFormAbdomPar3', inputValue: 2, width: 120},
													],
													listeners: {
														'change': function(field, checked) {
															win.findById('swENSEW_Abdominal_Panel').change_VolFormAbdomPar(field, checked);
														}
													}
												})	
											]
										},
										{xtype:'label',text: 'Подвижность',style: 'font-size: 10pt;  margin-left: 2pt; margin-top: 2pt; margin-bottom: 2px'},
										{									
											layout:'form',
											labelWidth:1,
											style: 'margin-top: 4px;  margin-left: 4px; margin-bottom: 2px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											items:[
												new Ext.form.RadioGroup({
													id:'swENSEW_VolFormAbdomPar4',
													labelSeparator: '',
													vertical: true,
													columns: 3,
													FromInterface: true,
													items: [
														{boxLabel: '---', name: 'VolFormAbdomPar4', inputValue: 0, width: 50}, 
														{boxLabel: 'подвижное', name: 'VolFormAbdomPar4', inputValue: 1, width: 90}, 
														{boxLabel: 'неподвижное', name: 'VolFormAbdomPar4', inputValue: 2, width: 110}
													],
													listeners: {
														'change': function(field, checked) {
															win.findById('swENSEW_Abdominal_Panel').change_VolFormAbdomPar(field, checked);
														}
													}
												})	
											]
										}
									]
								},
								//Изменения почек
								{									
									layout:'column',
									style: 'margin-top: 10pt;',
									items:[
										{xtype:'label',text: 'Изменения почек',style: 'font-size: 10pt;  margin-left: 2pt; margin-top: 2pt; margin-bottom: 2px'},
										{									
											layout:'form',
											labelWidth:1,
											style: 'margin-top: 4px; margin-bottom: 2px;  margin-left: 4px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											items:[
												new Ext.form.RadioGroup({
													id:'swENSEW_KidneyChang',
													labelSeparator: '',
													vertical: true,
													columns: 6,
													FromInterface: true,
													items: [
														{boxLabel: 'нет', name: 'KidneyChang', inputValue: 0, width: 50}, 
														{boxLabel: 'поликистоз почек', name: 'KidneyChang', inputValue: 1, width: 150}, 
														{boxLabel: 'гидронефроз', name: 'KidneyChang', inputValue: 2, width: 100},
														{boxLabel: 'инфантильный поликистоз почек', name: 'KidneyChang', inputValue: 3, width: 250},
														{boxLabel: 'тромбоз почечных вен', name: 'KidneyChang', inputValue: 4, width: 180},
														{boxLabel: 'опухоль Вильмса', name: 'KidneyChang', inputValue: 5, width: 130}
													],
													listeners: {
														'change': function(field, checked) {
															if((checked) && field.FromInterface){
																if ((this.NeonatalSurvey_data.NeonatalSurveyParam.KidneyChang) || (String(checked.inputValue) != '0')) {
																	this.NeonatalSurvey_data.NeonatalSurveyParam.KidneyChang = checked.inputValue;									
																	this.changedDatas = true;
																}
															}
															field.FromInterface = true;
														}.createDelegate(this)
													}
												})	
											]
										},
									]
								},
								//Изменения яичников 
								{									
									layout:'column',
									items:[
										{xtype:'label',text: 'Изменения яичников',style: 'font-size: 10pt;  margin-left: 2pt; margin-top: 2pt; margin-bottom: 2px'},
										{									
											layout:'form',
											labelWidth:1,
											style: 'margin-top: 4px; margin-bottom: 2px;  margin-left: 4px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											items:[
												new Ext.form.RadioGroup({
													id:'swENSEW_OvarianChang',
													labelSeparator: '',
													vertical: true,
													columns: 3,
													FromInterface: true,
													items: [
														{boxLabel: 'нет', name: 'OvarianChang', inputValue: 0, width: 50}, 
														{boxLabel: 'кисты яичника', name: 'OvarianChang', inputValue: 1, width: 150}, 
														{boxLabel: 'иные', name: 'OvarianChang', inputValue: 2, width: 80}
													],
													listeners: {
														'change': function(field, checked) {
															if((checked) && field.FromInterface){
																if ((this.NeonatalSurvey_data.NeonatalSurveyParam.OvarianChang) || (String(checked.inputValue) != '0')) {
																	this.NeonatalSurvey_data.NeonatalSurveyParam.OvarianChang = checked.inputValue;									
																	this.changedDatas = true;
																}
															}
															field.FromInterface = true;
														}.createDelegate(this)
													}
												})	
											]
										},
										{
											allowBlank: true,
											fieldLabel: '',
											labelSeparator: '',
											name: 'swENSEW_OvarianChangTxt',
											id: 'swENSEW_OvarianChangTxt',
											width: 850,
											value:'',
											style: 'margin-top: 4px;  margin-left: 4px; ',
											xtype: 'textfield',
											listeners:{
												'change':function (field, newValue, oldValue) {
													this.NeonatalSurvey_data.NeonatalSurveyParam.OvarianChangTxt = newValue;
													this.changedDatas = true;
												}.createDelegate(this)
											}
										}
									]
								},
								//Изменения печени
								{									
									layout:'column',
									items:[
										{xtype:'label',text: 'Изменения печени',style: 'font-size: 10pt;  margin-left: 2pt; margin-top: 2pt; margin-bottom: 2px'},
										{									
											layout:'form',
											labelWidth:1,
											style: 'margin-top: 4px; margin-bottom: 2px;  margin-left: 4px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											items:[
												new Ext.form.RadioGroup({
													id:'swENSEW_HepaticChang',
													labelSeparator: '',
													vertical: true,
													columns: 6,
													FromInterface: true,
													items: [
														{boxLabel: 'нет', name: 'HepaticChang', inputValue: 0, width: 50}, 
														{boxLabel: 'кисты печени', name: 'HepaticChang', inputValue: 1, width: 130}, 
														{boxLabel: 'гамартомы', name: 'HepaticChang', inputValue: 2, width: 90},
														{boxLabel: 'гемангиомы', name: 'HepaticChang', inputValue: 3, width: 100},
														{boxLabel: 'гемангиоэндотелиома', name: 'HepaticChang', inputValue: 4, width: 170},
														{boxLabel: 'гепатобластома', name: 'HepaticChang', inputValue: 5, width: 120}
													],
													listeners: {
														'change': function(field, checked) {
															if((checked) && field.FromInterface){
																if ((this.NeonatalSurvey_data.NeonatalSurveyParam.HepaticChang) || (String(checked.inputValue) != '0')) {
																	this.NeonatalSurvey_data.NeonatalSurveyParam.HepaticChang = checked.inputValue;									
																	this.changedDatas = true;
																}
															}
															field.FromInterface = true;
														}.createDelegate(this)
													}
												})	
											]
										},
									]
								},
								{									
									layout:'column',
									style: 'margin-top: 10pt;',
									items:[
										//Нижний полюс обеих почек
										{
											layout:'form',
											border: false,
											labelWidth: 180,
											items:[
												{
													fieldLabel: 'Нижний полюс обеих почек',
													labelSeparator: '',
													name: 'KidneysLowerPole',
													id: 'swENSEW_KidneysLowerPole',
													xtype: 'checkbox',
													checked: false,
													listeners: {
														'check': function(chb, checked ) {
															if ((this.NeonatalSurvey_data.NeonatalSurveyParam.KidneysLowerPole) || (checked)) {
																this.NeonatalSurvey_data.NeonatalSurveyParam.KidneysLowerPole = checked ? 2 : 1;									
																this.changedDatas = true;
															}
														}.createDelegate(this)
													}		
												}
											]
										},
										//Диастаз прямой мышцы живота
										{
											layout:'form',
											border: false,
											labelWidth: 210,
											items:[
												{
													fieldLabel: 'Диастаз прямой мышцы живота',
													labelSeparator: '',
													name: 'DiastasRectAbdom',
													id: 'swENSEW_DiastasRectAbdom',
													xtype: 'checkbox',
													checked: false,
													listeners: {
														'check': function(chb, checked ) {
															if ((this.NeonatalSurvey_data.NeonatalSurveyParam.DiastasRectAbdom) || (checked)) { 
																this.NeonatalSurvey_data.NeonatalSurveyParam.DiastasRectAbdom = checked ? 2 : 1;									
																this.changedDatas = true;
															}
														}.createDelegate(this)
													}		
												}
											]
										},
										//Ладьевидный живот
										{
											layout:'form',
											border: false,
											labelWidth: 150,
											items:[
												{
													fieldLabel: 'Ладьевидный живот',
													labelSeparator: '',
													name: 'ScaphoidAbdom',
													id: 'swENSEW_ScaphoidAbdom',
													xtype: 'checkbox',
													checked: false,
													listeners: {
														'check': function(chb, checked ) {
															if((this.NeonatalSurvey_data.NeonatalSurveyParam.ScaphoidAbdom) || (checked)) {
																this.NeonatalSurvey_data.NeonatalSurveyParam.ScaphoidAbdom = checked ? 2 : 1;									
																this.changedDatas = true;
															}
														}.createDelegate(this)
													}		
												}
											]
										},
										//Синдром отвисшего живота
										{
											layout:'form',
											border: false,
											labelWidth: 180,
											items:[
												{
													fieldLabel: 'Синдром отвисшего живота',
													labelSeparator: '',
													name: 'SaggyBellySyndr',
													id: 'swENSEW_SaggyBellySyndr',
													xtype: 'checkbox',
													checked: false,
													listeners: {
														'check': function(chb, checked ) {
															if ((this.NeonatalSurvey_data.NeonatalSurveyParam.SaggyBellySyndr) || (checked)) {
																this.NeonatalSurvey_data.NeonatalSurveyParam.SaggyBellySyndr = checked ? 2 : 1;									
																this.changedDatas = true;
															}
														}.createDelegate(this)
													}		
												}
											]
										},
										//Открытый мочевой проток
										{
											layout:'form',
											border: false,
											labelWidth: 180,
											items:[
												{
													fieldLabel: 'Открытый мочевой проток',
													labelSeparator: '',
													name: 'OpenUrinaryDuct',
													id: 'swENSEW_OpenUrinaryDuct',
													xtype: 'checkbox',
													checked: false,
													listeners: {
														'check': function(chb, checked ) {
															if ((this.NeonatalSurvey_data.NeonatalSurveyParam.OpenUrinaryDuct) || (checked)) {
																this.NeonatalSurvey_data.NeonatalSurveyParam.OpenUrinaryDuct = checked ? 2 : 1;									
																this.changedDatas = true;
															}
														}.createDelegate(this)
													}		
												}
											]
										}		
									]
								},



							],
							change_VolFormAbdomPar: function(field, checked) {
								if((checked) && field.FromInterface){
									var VolFormAbdomPar = (this.NeonatalSurvey_data.NeonatalSurveyParam.VolFormAbdomPar) ? this.NeonatalSurvey_data.NeonatalSurveyParam.VolFormAbdomPar : '0000';
									var position = parseInt(field.id.substr(field.id.length - 1, 1));
									var value = checked.inputValue;
									VolFormAbdomPar = VolFormAbdomPar.split("");
									VolFormAbdomPar[position - 1] = value;
									VolFormAbdomPar = VolFormAbdomPar.join("");
									if (!Ext.isEmpty(this.NeonatalSurvey_data.NeonatalSurveyParam.VolFormAbdomPar) || (VolFormAbdomPar != '0000')) 
										this.NeonatalSurvey_data.NeonatalSurveyParam.VolFormAbdomPar = VolFormAbdomPar;									
									this.changedDatas = true;
								}
								field.FromInterface = true;
							}.createDelegate(this)
						},
						//Панель Анус   
						{
							id: 'swENSEW_Anus_Panel',
							xtype: 'fieldset',
							autoHeight: true,
							title: langs('Анус'),
							collapsible: true,
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
								{xtype:'label',text: 'Анальное отверстие',style: 'font-size: 10pt;  margin-left: 2pt; margin-top: 2pt; margin-bottom: 2px'},
								{									
									layout:'form',
									labelWidth:1,
									style: 'margin-top: 4px; margin-bottom: 2px;  margin-left: 4px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
									items:[
										new Ext.form.RadioGroup({
											id:'swENSEW_Anus',
											labelSeparator: '',
											vertical: true,
											columns: 3,
											FromInterface: true,
											items: [
												{boxLabel: '---', name: 'Anus', inputValue: 0, width: 50}, 
												{boxLabel: 'определяется', name: 'Anus', inputValue: 1, width: 110}, 
												{boxLabel: 'не определяется', name: 'Anus', inputValue: 2, width: 130}
											],
											listeners: {
												'change': function(field, checked) {
													if((checked) && field.FromInterface){
														if ((this.NeonatalSurvey_data.NeonatalSurveyParam.Anus) || (String(checked.inputValue) != '0')) {
															this.NeonatalSurvey_data.NeonatalSurveyParam.Anus = checked.inputValue;									
															this.changedDatas = true;
														}
													}
													field.FromInterface = true;
												}.createDelegate(this)
											}
										})	
									]
								},
								{									
									layout:'form',
									labelWidth: 150,
									items:[
										{
											fieldLabel: 'Топика обычная',
											labelSeparator: '',
											name: 'TopicUsual',
											id: 'swENSEW_TopicUsual',
											xtype: 'checkbox',
											checked: false,
											listeners: {
												'check': function(chb, checked ) {
													if ((this.NeonatalSurvey_data.NeonatalSurveyParam.TopicUsual) || (checked)) {
														this.NeonatalSurvey_data.NeonatalSurveyParam.TopicUsual = checked ? 2 : 1;									
														this.changedDatas = true;
													}
												}.createDelegate(this)
											}		
										}
									]
								},
								{									
									layout:'form',
									labelWidth: 100,
									items:[
										{
											fieldLabel: 'Свищ',
											labelSeparator: '',
											name: 'Fistula',
											id: 'swENSEW_Fistula',
											xtype: 'checkbox',
											checked: false,
											listeners: {
												'check': function(chb, checked ) {
													if ((this.NeonatalSurvey_data.NeonatalSurveyParam.Fistula) || (checked)) {
														this.NeonatalSurvey_data.NeonatalSurveyParam.Fistula = checked ? 2 : 1;									
														this.changedDatas = true;
													}
												}.createDelegate(this)
											}		
										},
									]
								}

							]
						},
						//Стул   StoolHas
						{
							id: 'swENSEW_Stool_Panel',
							xtype: 'fieldset',
							autoHeight: true,
							title: langs('Стул'),
							collapsible: true,
							layout: 'form',
							labelWidth: 250,
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
								//Стул имеется , Частота 
								{									
									layout:'column',
									items:[
										{
											layout:'form',
											border: false,
											labelWidth: 120,
											items:[
												{
													fieldLabel: 'Стул имеется',
													labelSeparator: '',
													name: 'StoolHas',
													id: 'swENSEW_StoolHas',
													xtype: 'checkbox',
													checked: false,
													listeners: {
														'check': function(chb, checked ) {
															if ((this.NeonatalSurvey_data.NeonatalSurveyParam.StoolHas) || (checked)) {
																this.NeonatalSurvey_data.NeonatalSurveyParam.StoolHas = checked ? 2 : 1;									
																this.changedDatas = true;
															}
														}.createDelegate(this)
													}		
												}
											]
										},
										//Частота
										{							
											layout:'form',
											style:'margin-top: 2px;',
											labelWidth:200,
											border:false,
											items:[	
												new Ext.form.NumberField({
													value: 0,
													id: 'swENSEW_StoolFrequency',
													fieldLabel:'Частота cтула',
													labelSeparator: '',
													enableKeyEvents: true,
													width: 60,
													//plugins:[ new Ext.ux.InputTextMask('99.9', true) ],
													listeners:{
														'keyup':function (obj, e) {
															if (!Ext.isEmpty(obj.getValue()))
																this.NeonatalSurvey_data.NeonatalSurveyParam.StoolFrequency = obj.getValue();													
															else delete this.NeonatalSurvey_data.NeonatalSurveyParam.StoolFrequency;												
															this.changedDatas = true;
														}.createDelegate(this)
													}
												})
											]
										},
										{xtype:'label',text: 'раз',style: 'font-size: 10pt;  margin-left: 2pt; margin-top: 2pt'},											
									]
								},
								//Характер 
								{									
									layout:'column',
									items:[
										{xtype:'label',text: 'Характер',style: 'font-size: 10pt;  margin-left: 2pt; margin-top: 2pt; margin-bottom: 2px'},
										{									
											layout:'form',
											labelWidth:1,
											style: 'margin-top: 4px;  margin-left: 4px; margin-bottom: 2px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											items:[
												new Ext.form.RadioGroup({
													id:'swENSEW_StoolNature',
													labelSeparator: '',
													vertical: true,
													columns: 5,
													FromInterface: true,
													items: [
														{boxLabel: '---', name: 'StoolNature', inputValue: 0, width: 50}, 
														{boxLabel: 'жидкий', name: 'StoolNature', inputValue: 1, width: 70}, 
														{boxLabel: 'кашицеобразный', name: 'StoolNature', inputValue: 2, width: 130},
														{boxLabel: 'плотный', name: 'StoolNature', inputValue: 3, width: 90},
														{boxLabel: 'другое', name: 'StoolNature', inputValue: 4, width: 70}
													],
													listeners: {
														'change': function(field, checked) {
															if((checked) && field.FromInterface){
																if ((this.NeonatalSurvey_data.NeonatalSurveyParam.StoolNature) || (String(checked.inputValue) != '0')) {
																	this.NeonatalSurvey_data.NeonatalSurveyParam.StoolNature = checked.inputValue;									
																	this.changedDatas = true;
																}
															}
															field.FromInterface = true;
														}.createDelegate(this)
													}
												})	
											]
										},
										{
											allowBlank: true,
											fieldLabel: '',
											labelSeparator: '',
											name: 'swENSEW_StoolNatureTxt',
											id: 'swENSEW_StoolNatureTxt',
											width: 790,
											value:'',
											style: 'margin-top: 4px;  margin-left: 4px; ',
											xtype: 'textfield',
											listeners:{
												'change':function (field, newValue, oldValue) {
													this.NeonatalSurvey_data.NeonatalSurveyParam.StoolNatureTxt = newValue;
													this.changedDatas = true;
												}.createDelegate(this)
											}
										}
									]
								},
								//Цвет 
								{									
									layout:'column',
									items:[
										{xtype:'label',text: 'Цвет',style: 'font-size: 10pt;  margin-left: 2pt; margin-top: 2pt; margin-bottom: 2px'},
										{									
											layout:'form',
											labelWidth:1,
											style: 'margin-top: 4px;  margin-left: 4px; margin-bottom: 2px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											items:[
												new Ext.form.RadioGroup({
													id:'swENSEW_StoolColor',
													labelSeparator: '',
													vertical: true,
													columns: 5,
													FromInterface: true,
													items: [
														{boxLabel: '---', name: 'StoolColor', inputValue: 0, width: 50}, 
														{boxLabel: 'меконеальный', name: 'StoolColor', inputValue: 1, width: 120}, 
														{boxLabel: 'жёлтый', name: 'StoolColor', inputValue: 2, width: 80},
														{boxLabel: 'зелёный', name: 'StoolColor', inputValue: 3, width: 80},
														{boxLabel: 'коричневый', name: 'StoolColor', inputValue: 4, width: 110},
														{boxLabel: 'чёрный', name: 'StoolColor', inputValue: 5, width: 70},
														{boxLabel: 'кровянистый', name: 'StoolColor', inputValue: 6, width: 110},
														{boxLabel: 'ахоличный', name: 'StoolColor', inputValue: 7, width: 90},
														{boxLabel: 'другое', name: 'StoolColor', inputValue: 8, width: 70}
													],
													listeners: {
														'change': function(field, checked) {
															if((checked) && field.FromInterface){
																if ((this.NeonatalSurvey_data.NeonatalSurveyParam.StoolColor) || (String(checked.inputValue) != '0')) {
																	this.NeonatalSurvey_data.NeonatalSurveyParam.StoolColor = checked.inputValue;									
																	this.changedDatas = true;
																}
															}
															field.FromInterface = true;
														}.createDelegate(this)
													}
												})	
											]
										},
										{
											allowBlank: true,
											fieldLabel: '',
											labelSeparator: '',
											name: 'swENSEW_StoolColorTxt',
											id: 'swENSEW_StoolColorTxt',
											width: 720,
											value:'',
											style: 'margin-top: 4px;  margin-left: 4px; ',
											xtype: 'textfield',
											listeners:{
												'change':function (field, newValue, oldValue) {
													this.NeonatalSurvey_data.NeonatalSurveyParam.StoolColorTxt = newValue;
													this.changedDatas = true;
												}.createDelegate(this)
											}
										}
									]
								},
								//Примеси 
								{									
									layout:'column',
									id: 'swENSEW_StoolImpurit_Panel',
									items:[
										{xtype:'label',text: 'Примеси',style: 'font-size: 10pt;  margin-left: 2pt; margin-top: 2pt; margin-bottom: 2px'},
										{									
											layout:'form',
											labelWidth:1,
											style: 'margin-top: 4px;  margin-left: 4px; margin-bottom: 2px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											items:[
												new Ext.form.RadioGroup({
													id:'swENSEW_StoolImpurit1',
													labelSeparator: '',
													vertical: true,
													columns: 1,
													FromInterface: true,
													items: [
														{boxLabel: 'нет', name: 'StoolImpurit1', inputValue: 0, width: 50}, 
														{boxLabel: 'слизь', name: 'StoolImpurit1', inputValue: 1, width: 70}, 
														{boxLabel: 'кровь', name: 'StoolImpurit1', inputValue: 2, width: 70},
														{boxLabel: 'непереваренные комочки', name: 'StoolImpurit1', inputValue: 3, width: 180}
													],
													listeners: {
														'change': function(field, checked) {
															if(field.FromInterface) win.findById('swENSEW_StoolImpurit_Panel').change_handler(field, checked);
															field.FromInterface = true;
														}
													}
												})	
											]
										},
										//кровь
										{									
											layout:'form',
											//width: 250,
											labelWidth:1,
											style: 'margin-top: 55px;  margin-left: 0px; margin-bottom: 0px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											items:[
												new Ext.form.RadioGroup({
													id:'swENSEW_StoolImpurit2',
													labelSeparator: '',
													vertical: true,
													columns: 4,
													FromInterface: true,
													items: [
														{boxLabel: '---', name: 'StoolImpurit2', inputValue: 0, width: 50, hidden: true}, 
														{boxLabel: 'алая', name: 'StoolImpurit2', inputValue: 1, width: 70}, 
														{boxLabel: 'прожилки', name: 'StoolImpurit2', inputValue: 2, width: 80},
														{boxLabel: 'другое', name: 'StoolImpurit2', inputValue: 3, width: 70}
													],
													listeners: {
														'change': function(field, checked) {
															if(field.FromInterface) win.findById('swENSEW_StoolImpurit_Panel').change_handler(field, checked);
															field.FromInterface = true;
														}
													}
												})	
											]
										},
										{									
											layout:'form',
											labelWidth:5,
											items:[
												{
													allowBlank: true,
													fieldLabel: '',
													labelSeparator: '',
													name: 'swENSEW_StoolImpuritTxt',
													id: 'swENSEW_StoolImpuritTxt',
													width: 780,
													value:'',
													style: 'margin-top: 54px;  margin-left: 4px; ',
													xtype: 'textfield',
													listeners:{
														'change':function (field, newValue, oldValue) {
															this.NeonatalSurvey_data.NeonatalSurveyParam.StoolImpuritTxt = newValue;
															this.changedDatas = true;
														}.createDelegate(this)
													}
												}
											]
										}		
									],
									change_handler: function(field, checked) {
										if(checked){
											var StoolImpurit = (this.NeonatalSurvey_data.NeonatalSurveyParam.StoolImpurit) ? this.NeonatalSurvey_data.NeonatalSurveyParam.StoolImpurit : '00';
											var position = parseInt(field.id.substr(field.id.length - 1, 1));
											var value = checked.inputValue;
											//если меняется выбранный в первой группе то обнуляются вторая и третья
											if (position == 1) {
												this.findById('swENSEW_StoolImpurit2').items.items[0].setValue(true);		
												for (var i = 1; i < this.findById('swENSEW_StoolImpurit2').items.items.length; i++  ) this.findById('swENSEW_StoolImpurit2').items.items[i].setValue(false);						
												this.findById('swENSEW_StoolImpurit2').disable();						
												if(value == 2)	this.findById('swENSEW_StoolImpurit2').enable();	
											}
											StoolImpurit = StoolImpurit.split("");
											StoolImpurit[position - 1] = value;
											StoolImpurit = StoolImpurit.join("");
											if (!Ext.isEmpty(this.NeonatalSurvey_data.NeonatalSurveyParam.StoolImpurit) || (StoolImpurit != '00')) 
												this.NeonatalSurvey_data.NeonatalSurveyParam.StoolImpurit = StoolImpurit;									
											this.changedDatas = true;
										}
									}.createDelegate(this)
								}
							]
						},
						//Пупок   
						{
							id: 'swENSEW_Navel_Panel',
							xtype: 'fieldset',
							autoHeight: true,
							title: langs('Пупок'),
							collapsible: true,
							layout: 'form',
							labelWidth: 250,
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
								//Остаток пуповины
								{									
									layout:'column',
									items:[
										{xtype:'label',text: 'Остаток пуповины',style: 'font-size: 10pt;  margin-left: 2pt; margin-top: 2pt; margin-bottom: 2px', width: 80},
										{									
											layout:'form',
											labelWidth:1,
											style: 'margin-top: 4px;  margin-left: 4px; margin-bottom: 2px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											items:[
												new Ext.form.RadioGroup({
													id:'swENSEW_RemainUmbilCord',
													labelSeparator: '',
													vertical: true,
													FromInterface: true,
													columns: 6,
													items: [
														{boxLabel: 'нет', name: 'RemainUmbilCord', inputValue: 0, width: 50}, 
														{boxLabel: 'в скобе', name: 'RemainUmbilCord', inputValue: 1, width: 80}, 
														{boxLabel: 'сухой', name: 'RemainUmbilCord', inputValue: 2, width: 70},
														{boxLabel: 'отслаивается', name: 'RemainUmbilCord', inputValue: 3, width: 110},
														{boxLabel: 'катетер в вене пуповины', name: 'RemainUmbilCord', inputValue: 4, width: 180},
														{boxLabel: 'другое', name: 'RemainUmbilCord', inputValue: 5, width: 70}
													],
													listeners: {
														'change': function(field, checked) {
															if((checked) && field.FromInterface){
																if ((this.NeonatalSurvey_data.NeonatalSurveyParam.RemainUmbilCord) || (String(checked.inputValue) != '0')) {
																	this.NeonatalSurvey_data.NeonatalSurveyParam.RemainUmbilCord = checked.inputValue;									
																	this.changedDatas = true;
																}
															}
															field.FromInterface = true;
														}.createDelegate(this)
													}
												})	
											]
										},
										{
											allowBlank: true,
											fieldLabel: '',
											labelSeparator: '',
											name: 'swENSEW_RemainUmbilCordTxt',
											id: 'swENSEW_RemainUmbilCordTxt',
											width: 630,
											value:'',
											style: 'margin-top: 4px;  margin-left: 4px; ',
											xtype: 'textfield',
											listeners:{
												'change':function (field, newValue, oldValue) {
													this.NeonatalSurvey_data.NeonatalSurveyParam.RemainUmbilCordTxt = newValue;
													this.changedDatas = true;
												}.createDelegate(this)
											}
										}
									]
								},
								//Пупочная ранка
								{									
									layout:'column',
									items:[
										{xtype:'label',text: 'Пупочная ранка',style: 'font-size: 10pt;  margin-left: 2pt; margin-top: 2pt; margin-bottom: 2px', width: 80},
										{									
											layout:'form',
											labelWidth:1,
											style: 'margin-top: 4px;  margin-left: 4px; margin-bottom: 2px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											items:[
												new Ext.form.RadioGroup({
													id:'swENSEW_UmbilicWound',
													labelSeparator: '',
													vertical: true,
													columns: 6,
													FromInterface: true,
													items: [
														{boxLabel: 'нет', name: 'UmbilicWound', inputValue: 0, width: 50}, 
														{boxLabel: 'сухая', name: 'UmbilicWound', inputValue: 1, width: 70}, 
														{boxLabel: 'эпителизируется', name: 'UmbilicWound', inputValue: 2, width: 120},
														{boxLabel: 'катетер в вене пуповины', name: 'UmbilicWound', inputValue: 3, width: 180},
														{boxLabel: 'другое', name: 'UmbilicWound', inputValue: 4, width: 70}
													],
													listeners: {
														'change': function(field, checked) {
															if((checked) && field.FromInterface){
																if ((this.NeonatalSurvey_data.NeonatalSurveyParam.UmbilicWound) || (String(checked.inputValue) != '0')) {
																	this.NeonatalSurvey_data.NeonatalSurveyParam.UmbilicWound = checked.inputValue;									
																	this.changedDatas = true;
																}
															}
															field.FromInterface = true;
														}.createDelegate(this)
													}
												})	
											]
										},
										{
											allowBlank: true,
											fieldLabel: '',
											labelSeparator: '',
											name: 'swENSEW_UmbilicWoundTxt',
											id: 'swENSEW_UmbilicWoundTxt',
											width: 700,
											value:'',
											style: 'margin-top: 4px;  margin-left: 4px; ',
											xtype: 'textfield',
											listeners:{
												'change':function (field, newValue, oldValue) {
													this.NeonatalSurvey_data.NeonatalSurveyParam.UmbilicWoundTxt = newValue;
													this.changedDatas = true;
												}.createDelegate(this)
											}
										}
									]
								},
								//Аномалии строения пупка, ////Выделения
								{									
									layout:'column',
									items:[
										{									
											layout:'form',
											labelWidth: 300,
											items:[
												{
													fieldLabel: 'Аномалии строения пупка',
													labelSeparator: '',
													name: 'NavelAbnormal',
													id: 'swENSEW_NavelAbnormal',
													xtype: 'checkbox',
													checked: false,
													listeners: {
														'check': function(chb, checked ) {
															if ((this.NeonatalSurvey_data.NeonatalSurveyParam.NavelAbnormal) || (checked)) {
																this.NeonatalSurvey_data.NeonatalSurveyParam.NavelAbnormal = checked ? 2 : 1;									
																this.changedDatas = true;
															}
														}.createDelegate(this)
													}		
												},
												{
													fieldLabel: 'Покраснение пупка',
													labelSeparator: '',
													name: 'NavelRedness',
													id: 'swENSEW_NavelRedness',
													xtype: 'checkbox',
													checked: false,
													listeners: {
														'check': function(chb, checked ) {
															if ((this.NeonatalSurvey_data.NeonatalSurveyParam.NavelRedness) || (checked)) {
																this.NeonatalSurvey_data.NeonatalSurveyParam.NavelRedness = checked ? 2 : 1;									
																this.changedDatas = true;
															}
														}.createDelegate(this)
													}		
												},
												{
													fieldLabel: 'Отек вокруг основания пуповины / пупочной ранки',
													labelSeparator: '',
													name: 'NavelSwell',
													id: 'swENSEW_NavelSwell',
													xtype: 'checkbox',
													checked: false,
													listeners: {
														'check': function(chb, checked ) {
															if ((this.NeonatalSurvey_data.NeonatalSurveyParam.NavelSwell) || (checked)) {
																this.NeonatalSurvey_data.NeonatalSurveyParam.NavelSwell = checked ? 2 : 1;									
																this.changedDatas = true;
															}
														}.createDelegate(this)
													}		
												}
											]
										},
										//Выделения
										{xtype:'label',text: 'Выделения',style: 'font-size: 10pt;  margin-left: 12pt; margin-top: 2pt; margin-bottom: 2px'},
										{									
											layout:'form',
											labelWidth:1,
											style: 'margin-top: 4px; margin-bottom: 2px;  margin-left: 4px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											items:[
												new Ext.form.RadioGroup({
													id:'swENSEW_NavelDischarg',
													labelSeparator: '',
													vertical: true,
													columns: 4,
													FromInterface: true,
													items: [
														{boxLabel: 'нет', name: 'NavelDischarg', inputValue: 0, width: 50}, 
														{boxLabel: 'серозные', name: 'NavelDischarg', inputValue: 1, width: 90}, 
														{boxLabel: 'гнойные', name: 'NavelDischarg', inputValue: 2, width: 80},
														{boxLabel: 'с примесью крови', name: 'NavelDischarg', inputValue: 3, width: 150}
													],
													listeners: {
														'change': function(field, checked) {
															if((checked) && field.FromInterface){
																if ((this.NeonatalSurvey_data.NeonatalSurveyParam.NavelDischarg) || (String(checked.inputValue) != '0')) {
																	this.NeonatalSurvey_data.NeonatalSurveyParam.NavelDischarg = checked.inputValue;									
																	this.changedDatas = true;
																}
															}
															field.FromInterface = true;
														}.createDelegate(this)
													}
												})	
											]
										}
									]
								}
							]
						},
						//Наружные половые органы   
						{
							id: 'swENSEW_gender_Panel',
							xtype: 'fieldset',
							autoHeight: true,
							title: langs('Наружные половые органы'),
							collapsible: true,
							layout: 'column',
							labelWidth: 250,
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
								//Пол
								{									
									layout:'column',
									width: 250,
									items:[
										{xtype:'label',text: 'Пол ',style: 'font-size: 10pt;  margin-left: 2pt; margin-top: 2pt; margin-bottom: 2px'},
										{									
											layout:'form',
											labelWidth:1,
											FromInterface: true,
											style: 'margin-top: 4px;  margin-left: 4px; margin-bottom: 2px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											items:[
												new Ext.form.RadioGroup({
													id:'swENSEW_gender',
													labelSeparator: '',
													vertical: true,
													columns: 1,
													items: [
														{boxLabel: '---', name: 'gender', inputValue: 0, width: 50}, 
														{boxLabel: 'по мужскому типу', name: 'gender', inputValue: 1, width: 130}, 
														{boxLabel: 'по женскому типу', name: 'gender', inputValue: 2, width: 130},
														{boxLabel: 'неясный пол (гермафродитизм)', name: 'gender', inputValue: 3, width: 200}
													],
													listeners: {
														'change': function(field, checked) {
															if((checked) && field.FromInterface){
																if ((this.NeonatalSurvey_data.NeonatalSurveyParam.gender) || (String(checked.inputValue) != '0')) {
																	this.NeonatalSurvey_data.NeonatalSurveyParam.gender = checked.inputValue;									
																	this.changedDatas = true;
																}
															}
															field.FromInterface = true;
														}.createDelegate(this)
													}
												})	
											]
										}
									]
								},
								//У мальчиков
								{									
									layout:'form',
									//width: 600,
									items:[
										{xtype:'label',text: 'У мальчиков',style: 'font-size: 10pt; font-weight: bold; font-style:italic; margin-left: 202pt; margin-top: 2pt; margin-bottom: 2px; color:#15428b'},
										{									
											layout:'column',
											width: 600,
											items:[
												//Нарушения строения половых органов
												{xtype:'label',text: 'Нарушения строения половых органов',style: 'font-size: 10pt;  margin-left: 12pt; margin-top: 2pt; margin-bottom: 2px', width: 130},
												{									
													layout:'form',
													labelWidth:1,
													FromInterface: true,
													style: 'margin-top: 4px; margin-bottom: 2px;  margin-left: 4px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
													items:[
														new Ext.form.RadioGroup({
															id:'swENSEW_GenitalsViolations',
															labelSeparator: '',
															vertical: true,
															columns: 1,
															items: [
																{boxLabel: 'нет', name: 'GenitalsViolations', inputValue: 0, width: 50}, 
																{boxLabel: 'гипоспадия', name: 'GenitalsViolations', inputValue: 1, width: 90}, 
																{boxLabel: 'эписпадии', name: 'GenitalsViolations', inputValue: 2, width: 80},
																{boxLabel: 'микропения', name: 'GenitalsViolations', inputValue: 3, width: 150},
																{boxLabel: 'приапизм', name: 'GenitalsViolations', inputValue: 4, width: 150}
															],
															listeners: {
																'change': function(field, checked) {
																	if((checked) && field.FromInterface){
																		if ((this.NeonatalSurvey_data.NeonatalSurveyParam.GenitalsViolations) || (String(checked.inputValue) != '0')) {
																			this.NeonatalSurvey_data.NeonatalSurveyParam.GenitalsViolations = checked.inputValue;									
																			this.changedDatas = true;
																		}
																	}
																	field.FromInterface = true;
																}.createDelegate(this)
															}
														})	
													]
												},
												{									
													layout:'form',
													labelWidth: 200,
													items:[
														{
															fieldLabel: 'Яички в мошонке',
															labelSeparator: '',
															name: 'TesticlInScrotum',
															id: 'swENSEW_TesticlInScrotum',
															xtype: 'checkbox',
															checked: false,
															listeners: {
																'check': function(chb, checked ) {
																	if ((this.NeonatalSurvey_data.NeonatalSurveyParam.TesticlInScrotum) || (checked)) {
																		this.NeonatalSurvey_data.NeonatalSurveyParam.TesticlInScrotum = checked ? 2 : 1;									
																		this.changedDatas = true;
																	}
																}.createDelegate(this)
															}		
														},
														{
															fieldLabel: 'Гидроцеле',
															labelSeparator: '',
															name: 'Hydrocele',
															id: 'swENSEW_Hydrocele',
															xtype: 'checkbox',
															checked: false,
															listeners: {
																'check': function(chb, checked ) {
																	if ((this.NeonatalSurvey_data.NeonatalSurveyParam.Hydrocele) || (checked)) {
																		this.NeonatalSurvey_data.NeonatalSurveyParam.Hydrocele = checked ? 2 : 1;									
																		this.changedDatas = true;
																	}
																}.createDelegate(this)
															}		
														},
														{
															fieldLabel: 'Гиперемия мошонки',
															labelSeparator: '',
															name: 'ScrotumHyperemia',
															id: 'swENSEW_ScrotumHyperemia',
															xtype: 'checkbox',
															checked: false,
															listeners: {
																'check': function(chb, checked ) {
																	if ((this.NeonatalSurvey_data.NeonatalSurveyParam.ScrotumHyperemia) || (checked)) {
																		this.NeonatalSurvey_data.NeonatalSurveyParam.ScrotumHyperemia = checked ? 2 : 1;									
																		this.changedDatas = true;
																	}
																}.createDelegate(this)
															}		
														},
														{
															fieldLabel: 'Перекрут яичек',
															labelSeparator: '',
															name: 'TesticlesTwisting',
															id: 'swENSEW_TesticlesTwisting',
															xtype: 'checkbox',
															checked: false,
															listeners: {
																'check': function(chb, checked ) {
																	if ((this.NeonatalSurvey_data.NeonatalSurveyParam.TesticlesTwisting) || (checked)) {
																		this.NeonatalSurvey_data.NeonatalSurveyParam.TesticlesTwisting = checked ? 2 : 1;									
																		this.changedDatas = true;
																	}
																}.createDelegate(this)
															}		
														},
														{
															fieldLabel: 'Складки хорошо выражены',
															labelSeparator: '',
															name: 'FoldsWellDefin',
															id: 'swENSEW_FoldsWellDefin',
															xtype: 'checkbox',
															checked: false,
															listeners: {
																'check': function(chb, checked ) {
																	if ((this.NeonatalSurvey_data.NeonatalSurveyParam.FoldsWellDefin) || (checked)) {
																		this.NeonatalSurvey_data.NeonatalSurveyParam.FoldsWellDefin = checked ? 2 : 1;									
																		this.changedDatas = true;
																	}
																}.createDelegate(this)
															}		
														}
													]
												},
											]
										}
									]
								},
								//У девочек
								{									
									layout:'form',
									//width: 600,
									items:[
										{xtype:'label',text: 'У девочек',style: 'font-size: 10pt; font-weight: bold; font-style:italic; margin-left: 102pt; margin-top: 2pt; margin-bottom: 2px; color:#15428b'},
										{									
											layout:'form',
											labelWidth: 200,
											items:[
												{
													fieldLabel: 'Отек половых губ',
													labelSeparator: '',
													name: 'LabiaSwell',
													id: 'swENSEW_LabiaSwell',
													xtype: 'checkbox',
													checked: false,
													listeners: {
														'check': function(chb, checked ) {
															if ((this.NeonatalSurvey_data.NeonatalSurveyParam.LabiaSwell) || (checked)) {
																this.NeonatalSurvey_data.NeonatalSurveyParam.LabiaSwell = checked ? 2 : 1;									
																this.changedDatas = true;
															}
														}.createDelegate(this)
													}		
												},
												{
													fieldLabel: 'Выделения из влагалища',
													labelSeparator: '',
													name: 'VaginalDischarge',
													id: 'swENSEW_VaginalDischarge',
													xtype: 'checkbox',
													checked: false,
													listeners: {
														'check': function(chb, checked ) {
															if ((this.NeonatalSurvey_data.NeonatalSurveyParam.VaginalDischarge) || (checked)) {
																this.NeonatalSurvey_data.NeonatalSurveyParam.VaginalDischarge = checked ? 2 : 1;									
																this.changedDatas = true;
															}
														}.createDelegate(this)
													}		
												},
												{
													fieldLabel: 'Клиторомегалия',
													labelSeparator: '',
													name: 'Clitoromegaly',
													id: 'swENSEW_Clitoromegaly',
													xtype: 'checkbox',
													checked: false,
													listeners: {
														'check': function(chb, checked ) {
															if ((this.NeonatalSurvey_data.NeonatalSurveyParam.Clitoromegaly) || (checked)) {
																this.NeonatalSurvey_data.NeonatalSurveyParam.Clitoromegaly = checked ? 2 : 1;									
																this.changedDatas = true;
															}
														}.createDelegate(this)
													}		
												},
											]
										}
									]
								}
							]
						},
						//Мочеиспускание   
						{
							id: 'swENSEW_Diuresis_Panel',
							xtype: 'fieldset',
							autoHeight: true,
							title: langs('Мочеиспускание'),
							collapsible: true,
							layout: 'form',
							labelWidth: 250,
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
								//Мочеиспускание
								{									
									layout:'column',
									items:[
										{xtype:'label',text: 'Мочеиспускание',style: 'font-size: 10pt;  margin-left: 12pt; margin-top: 2pt; margin-bottom: 2px'},
										{									
											layout:'form',
											labelWidth:1,
											FromInterface: true,
											style: 'margin-top: 4px;  margin-left: 4px; margin-bottom: 2px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											items:[
												new Ext.form.RadioGroup({
													id:'swENSEW_Diuresis1',
													labelSeparator: '',
													vertical: true,
													columns: 3,
													items: [
														{boxLabel: '---', name: 'Diuresis1', inputValue: 0, width: 50}, 
														{boxLabel: 'свободное', name: 'Diuresis1', inputValue: 1, width: 100}, 
														{boxLabel: 'по мочевому катетеру', name: 'Diuresis1', inputValue: 2, width: 150}
													],
													listeners: {
														'change': function(field, checked) {
															win.findById('swENSEW_Diuresis_Panel').change_handler(field, checked);
														}
													}
												})	
											]
										},
										{									
											layout:'column',
											width: 300,
											items:[
												{xtype:'label',text: 'Темп диуреза',style: 'font-size: 10pt;  margin-left: 12pt; margin-top: 2pt; margin-bottom: 2px'},
												new Ext.form.NumberField({
													value: 0,
													id: 'swENSEW_DiuresisVolume',
													fieldLabel:'',
													labelSeparator: '',
													enableKeyEvents: true,
													width: 60,
													style: 'margin-left: 2pt; margin-top: 2pt',
													//plugins:[ new Ext.ux.InputTextMask('99.9', true) ],
													listeners:{
														'keyup':function (obj, e) {
															if (!Ext.isEmpty(obj.getValue()))
																this.NeonatalSurvey_data.NeonatalSurveyParam.DiuresisVolume = obj.getValue();													
															else delete this.NeonatalSurvey_data.NeonatalSurveyParam.DiuresisVolume;												
															this.changedDatas = true;
														}.createDelegate(this)
													}
												}),
												{xtype:'label',text: 'мл/кг/час',style: 'font-size: 10pt;  margin-left: 2pt; margin-top: 2pt; margin-bottom: 2px'}
											]
										}
									]
								},
								//Моча
								{									
									layout:'column',
									items:[
										{xtype:'label',text: 'Моча',style: 'font-size: 10pt;  margin-left: 12pt; margin-top: 2pt; margin-bottom: 2px'},
										{									
											layout:'form',
											labelWidth:1,
											style: 'margin-top: 4px;  margin-left: 4px; margin-bottom: 2px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											items:[
												new Ext.form.RadioGroup({
													id:'swENSEW_Diuresis2',
													labelSeparator: '',
													vertical: true,
													columns: 6,
													FromInterface: true,
													items: [
														{boxLabel: 'нет', name: 'Diuresis2', inputValue: 0, width: 50}, 
														{boxLabel: 'светло–желтая', name: 'Diuresis2', inputValue: 1, width: 130}, 
														{boxLabel: 'интенсивно-желтая', name: 'Diuresis2', inputValue: 2, width: 140},
														{boxLabel: 'тёмно-коричневая', name: 'Diuresis2', inputValue: 3, width: 140},
														{boxLabel: 'окрашена кровью', name: 'Diuresis2', inputValue: 4, width: 130},
														{boxLabel: 'другое', name: 'Diuresis2', inputValue: 5, width: 70}
													],
													listeners: {
														'change': function(field, checked) {
															win.findById('swENSEW_Diuresis_Panel').change_handler(field, checked);
														}
													}
												})	
											]
										},
										{
											allowBlank: true,
											fieldLabel: '',
											labelSeparator: '',
											name: 'swENSEW_UrineTxt',
											id: 'swENSEW_UrineTxt',
											width: 1000,
											value:'',
											style: 'margin-top: 4px;  margin-left: 4px; ',
											xtype: 'textfield',
											listeners:{
												'change':function (field, newValue, oldValue) {
													this.NeonatalSurvey_data.NeonatalSurveyParam.UrineTxt = newValue;
													this.changedDatas = true;
												}.createDelegate(this)
											}
										}
									]
								}
							],
							change_handler: function(field, checked) {
								if((checked) && field.FromInterface){
									var Diuresis = (this.NeonatalSurvey_data.NeonatalSurveyParam.Diuresis) ? this.NeonatalSurvey_data.NeonatalSurveyParam.Diuresis : '00';
									var position = parseInt(field.id.substr(field.id.length - 1, 1));
									var value = checked.inputValue;
									Diuresis = Diuresis.split("");
									Diuresis[position - 1] = value;
									Diuresis = Diuresis.join("");
									if (!Ext.isEmpty(this.NeonatalSurvey_data.NeonatalSurveyParam.Diuresis) || (Diuresis != '00')) 
										this.NeonatalSurvey_data.NeonatalSurveyParam.Diuresis = Diuresis;									
									this.changedDatas = true;
								}
								field.FromInterface = true;
							}.createDelegate(this)
						},
						//Лимфоузлы   
						{
							id: 'swENSEW_LymphNode_Panel',
							xtype: 'fieldset',
							autoHeight: true,
							title: langs('Лимфоузлы'),
							collapsible: true,
							layout: 'column',
							labelWidth: 250,
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
									labelWidth: 80,
									items:[
										{
											fieldLabel: 'Пальпация',
											labelSeparator: '',
											name: 'LymphNodePalp',
											id: 'swENSEW_LymphNodePalp',
											xtype: 'checkbox',
											checked: false,
											listeners: {
												'check': function(chb, checked ) {
													if ((this.NeonatalSurvey_data.NeonatalSurveyParam.LymphNodePalp) || (checked)) {
														this.NeonatalSurvey_data.NeonatalSurveyParam.LymphNodePalp = checked ? 2 : 1;									
														this.changedDatas = true;
														this.findById('swENSEW_LymphNodeLoc').setDisabled(!checked);  //BOB - 06.05.2020
														if (!checked) {
															this.findById('swENSEW_LymphNodeLoc').setValue('');
															delete this.NeonatalSurvey_data.NeonatalSurveyParam.LymphNodeLoc;
														}
													}
												}.createDelegate(this)
											}		
										}
									]
								},		
								{
									allowBlank: true,
									fieldLabel: '',
									labelSeparator: '',
									name: 'swENSEW_LymphNodeLoc',
									id: 'swENSEW_LymphNodeLoc',
									width: 850,
									value:'',
									style: 'margin-top: 1px;  margin-left: 4px; ',
									xtype: 'textfield',
									listeners:{
										'change':function (field, newValue, oldValue) {
											this.NeonatalSurvey_data.NeonatalSurveyParam.LymphNodeLoc = newValue;
											this.changedDatas = true;
										}.createDelegate(this)
									}
								}
							]
						},
						//Конечности   
						{
							id: 'swENSEW_Limbs_Panel',
							xtype: 'fieldset',
							autoHeight: true,
							title: langs('Конечности'),
							collapsible: true,
							layout: 'column',
							labelWidth: 250,
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
									layout:'column',
									items:[				
										//Верхние конечности
										{									
											layout:'form',
											width: 600,
											items:[
												{xtype:'label',text: 'Верхние конечности',style: 'font-size: 10pt; font-weight: bold; font-style:italic; margin-left: 202pt; margin-top: 2pt; margin-bottom: 2px; color:#15428b'},
												//Синдактилия
												{									
													layout:'column',
													items:[				
														{									
															layout:'form',
															labelWidth:200,
															style: 'margin-top: 3px;',
															items:[
																{
																	fieldLabel: 'Синдактилия',
																	labelSeparator: '',
																	name: 'Syndactyly',
																	id: 'swENSEW_Syndactyly',
																	xtype: 'checkbox',
																	checked: false,
																	listeners: {
																		'check': function(chb, checked ) {
																			if ((this.NeonatalSurvey_data.NeonatalSurveyParam.Syndactyly) || (checked)) 
																				this.NeonatalSurvey_data.NeonatalSurveyParam.Syndactyly = checked ? 2 : 1;
																			this.findById('swENSEW_SyndactylyLoc').setDisabled(!checked);  //BOB - 06.05.2020
																			if (!checked) {
																				this.findById('swENSEW_SyndactylyLoc').items.items[0].setValue(true);
																				for (var i = 1; i < this.findById('swENSEW_SyndactylyLoc').items.items.length; i++  ) this.findById('swENSEW_SyndactylyLoc').items.items[i].setValue(false);						
																			}		
																			this.changedDatas = true;
																		}.createDelegate(this)
																	}		
																}
															]
														},						
														{									
															layout:'form',
															labelWidth:1,
															style: 'margin-top: 0px;  margin-left: 4px; margin-bottom: 0px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
															items:[
																new Ext.form.RadioGroup({
																	id:'swENSEW_SyndactylyLoc',
																	labelSeparator: '',
																	vertical: true,
																	columns: 4,
																	FromInterface: true,
																	items: [
																		{boxLabel: '---', name: 'SyndactylyLoc', inputValue: 0, width: 50, hidden: true}, 
																		{boxLabel: 'справа', name: 'SyndactylyLoc', inputValue: 1, width: 70}, 
																		{boxLabel: 'слева', name: 'SyndactylyLoc', inputValue: 2, width: 70},
																		{boxLabel: 'с обеих сторон', name: 'SyndactylyLoc', inputValue: 3, width: 110}
																	],
																	listeners: {
																		'change': function(field, checked) {
																			if((checked) && field.FromInterface){
																				if ((this.NeonatalSurvey_data.NeonatalSurveyParam.SyndactylyLoc) || (String(checked.inputValue) != '0')) {
																					this.NeonatalSurvey_data.NeonatalSurveyParam.SyndactylyLoc = checked.inputValue;									
																					this.changedDatas = true;
																				}
																			}
																			field.FromInterface = true;
																		}.createDelegate(this)
																	}
																})	
															]
														}
													]
												},
												//Полидактилия
												{									
													layout:'column',
													items:[				
														{									
															layout:'form',
															labelWidth:200,
															style: 'margin-top: 3px;',
															items:[
																{
																	fieldLabel: 'Полидактилия',
																	labelSeparator: '',
																	name: 'Polydactyly',
																	id: 'swENSEW_Polydactyly',
																	xtype: 'checkbox',
																	checked: false,
																	listeners: {
																		'check': function(chb, checked ) {
																			if ((this.NeonatalSurvey_data.NeonatalSurveyParam.Polydactyly) || (checked)) 
																				this.NeonatalSurvey_data.NeonatalSurveyParam.Polydactyly = checked ? 2 : 1;
																			this.findById('swENSEW_PolydactylyLoc').setDisabled(!checked);  //BOB - 06.05.2020
																			if (!checked) {
																				this.findById('swENSEW_PolydactylyLoc').items.items[0].setValue(true);
																				for (var i = 1; i < this.findById('swENSEW_PolydactylyLoc').items.items.length; i++  ) this.findById('swENSEW_PolydactylyLoc').items.items[i].setValue(false);						
																			}		
																			this.changedDatas = true;
																		}.createDelegate(this)
																	}		
																}
															]
														},						
														{									
															layout:'form',
															labelWidth:1,
															style: 'margin-top: 0px;  margin-left: 4px; margin-bottom: 0px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
															items:[
																new Ext.form.RadioGroup({
																	id:'swENSEW_PolydactylyLoc',
																	labelSeparator: '',
																	vertical: true,
																	columns: 4,
																	FromInterface: true,
																	items: [
																		{boxLabel: '---', name: 'PolydactylyLoc', inputValue: 0, width: 50, hidden: true}, 
																		{boxLabel: 'справа', name: 'PolydactylyLoc', inputValue: 1, width: 70}, 
																		{boxLabel: 'слева', name: 'PolydactylyLoc', inputValue: 2, width: 70},
																		{boxLabel: 'с обеих сторон', name: 'PolydactylyLoc', inputValue: 3, width: 110}
																	],
																	listeners: {
																		'change': function(field, checked) {
																			if((checked) && field.FromInterface){
																				if ((this.NeonatalSurvey_data.NeonatalSurveyParam.PolydactylyLoc) || (String(checked.inputValue) != '0')) {
																					this.NeonatalSurvey_data.NeonatalSurveyParam.PolydactylyLoc = checked.inputValue;									
																					this.changedDatas = true;
																				}
																			}
																			field.FromInterface = true;
																		}.createDelegate(this)
																	}
																})	
															]
														}
													]
												},
												//Брахидактилия
												{									
													layout:'column',
													items:[				
														{									
															layout:'form',
															labelWidth:200,
															style: 'margin-top: 3px;',
															items:[
																{
																	fieldLabel: 'Брахидактилия',
																	labelSeparator: '',
																	name: 'Brachydactyly',
																	id: 'swENSEW_Brachydactyly',
																	xtype: 'checkbox',
																	checked: false,
																	listeners: {
																		'check': function(chb, checked ) {
																			if ((this.NeonatalSurvey_data.NeonatalSurveyParam.Brachydactyly) || (checked)) 
																				this.NeonatalSurvey_data.NeonatalSurveyParam.Brachydactyly = checked ? 2 : 1;
																			this.findById('swENSEW_BrachydactylyLoc').setDisabled(!checked);  //BOB - 06.05.2020
																			if (!checked) {
																				this.findById('swENSEW_BrachydactylyLoc').items.items[0].setValue(true);
																				for (var i = 1; i < this.findById('swENSEW_BrachydactylyLoc').items.items.length; i++  ) this.findById('swENSEW_BrachydactylyLoc').items.items[i].setValue(false);						
																			}		
																			this.changedDatas = true;
																		}.createDelegate(this)
																	}		
																}
															]
														},						
														{									
															layout:'form',
															labelWidth:1,
															style: 'margin-top: 0px;  margin-left: 4px; margin-bottom: 0px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
															items:[
																new Ext.form.RadioGroup({
																	id:'swENSEW_BrachydactylyLoc',
																	labelSeparator: '',
																	vertical: true,
																	columns: 4,
																	FromInterface: true,
																	items: [
																		{boxLabel: '---', name: 'BrachydactylyLoc', inputValue: 0, width: 50, hidden: true}, 
																		{boxLabel: 'справа', name: 'BrachydactylyLoc', inputValue: 1, width: 70}, 
																		{boxLabel: 'слева', name: 'BrachydactylyLoc', inputValue: 2, width: 70},
																		{boxLabel: 'с обеих сторон', name: 'BrachydactylyLoc', inputValue: 3, width: 110}
																	],
																	listeners: {
																		'change': function(field, checked) {
																			if((checked) && field.FromInterface){
																				if ((this.NeonatalSurvey_data.NeonatalSurveyParam.BrachydactylyLoc) || (String(checked.inputValue) != '0')) {
																					this.NeonatalSurvey_data.NeonatalSurveyParam.BrachydactylyLoc = checked.inputValue;									
																					this.changedDatas = true;
																				}
																			}
																			field.FromInterface = true;
																		}.createDelegate(this)
																	}
																})	
															]
														}
													]
												},
												//Камптодактилия
												{									
													layout:'column',
													items:[				
														{									
															layout:'form',
															labelWidth:200,
															style: 'margin-top: 3px;',
															items:[
																{
																	fieldLabel: 'Камптодактилия',
																	labelSeparator: '',
																	name: 'Camptodactyly',
																	id: 'swENSEW_Camptodactyly',
																	xtype: 'checkbox',
																	checked: false,
																	listeners: {
																		'check': function(chb, checked ) {
																			if ((this.NeonatalSurvey_data.NeonatalSurveyParam.Camptodactyly) || (checked)) 
																				this.NeonatalSurvey_data.NeonatalSurveyParam.Camptodactyly = checked ? 2 : 1;
																			this.findById('swENSEW_CamptodactylyLoc').setDisabled(!checked);  //BOB - 06.05.2020
																			if (!checked) {
																				this.findById('swENSEW_CamptodactylyLoc').items.items[0].setValue(true);
																				for (var i = 1; i < this.findById('swENSEW_CamptodactylyLoc').items.items.length; i++  ) this.findById('swENSEW_CamptodactylyLoc').items.items[i].setValue(false);						
																			}		
																			this.changedDatas = true;
																		}.createDelegate(this)
																	}		
																}
															]
														},						
														{									
															layout:'form',
															labelWidth:1,
															style: 'margin-top: 0px;  margin-left: 4px; margin-bottom: 0px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
															items:[
																new Ext.form.RadioGroup({
																	id:'swENSEW_CamptodactylyLoc',
																	labelSeparator: '',
																	vertical: true,
																	columns: 4,
																	FromInterface: true,
																	items: [
																		{boxLabel: '---', name: 'CamptodactylyLoc', inputValue: 0, width: 50, hidden: true}, 
																		{boxLabel: 'справа', name: 'CamptodactylyLoc', inputValue: 1, width: 70}, 
																		{boxLabel: 'слева', name: 'CamptodactylyLoc', inputValue: 2, width: 70},
																		{boxLabel: 'с обеих сторон', name: 'CamptodactylyLoc', inputValue: 3, width: 110}
																	],
																	listeners: {
																		'change': function(field, checked) {
																			if((checked) && field.FromInterface){
																				if ((this.NeonatalSurvey_data.NeonatalSurveyParam.CamptodactylyLoc) || (String(checked.inputValue) != '0')){
																					this.NeonatalSurvey_data.NeonatalSurveyParam.CamptodactylyLoc = checked.inputValue;									
																					this.changedDatas = true;
																				} 
																			}
																			field.FromInterface = true;
																		}.createDelegate(this)
																	}
																})	
															]
														}
													]
												},
												//Арахнодактилия
												{									
													layout:'column',
													items:[				
														{									
															layout:'form',
															labelWidth:200,
															style: 'margin-top: 3px;',
															items:[
																{
																	fieldLabel: 'Арахнодактилия',
																	labelSeparator: '',
																	name: 'Dolichostenomelia',
																	id: 'swENSEW_Dolichostenomelia',
																	xtype: 'checkbox',
																	checked: false,
																	listeners: {
																		'check': function(chb, checked ) {
																			if ((this.NeonatalSurvey_data.NeonatalSurveyParam.Dolichostenomelia) || (checked)) 
																				this.NeonatalSurvey_data.NeonatalSurveyParam.Dolichostenomelia = checked ? 2 : 1;
																			this.findById('swENSEW_DolichostenomeliaLoc').setDisabled(!checked);  //BOB - 06.05.2020
																			if (!checked) {
																				this.findById('swENSEW_DolichostenomeliaLoc').items.items[0].setValue(true);
																				for (var i = 1; i < this.findById('swENSEW_DolichostenomeliaLoc').items.items.length; i++  ) this.findById('swENSEW_DolichostenomeliaLoc').items.items[i].setValue(false);						
																			}		
																			this.changedDatas = true;
																		}.createDelegate(this)
																	}		
																}
															]
														},						
														{									
															layout:'form',
															labelWidth:1,
															style: 'margin-top: 0px;  margin-left: 4px; margin-bottom: 0px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
															items:[
																new Ext.form.RadioGroup({
																	id:'swENSEW_DolichostenomeliaLoc',
																	labelSeparator: '',
																	vertical: true,
																	columns: 4,
																	FromInterface: true,
																	items: [
																		{boxLabel: '---', name: 'DolichostenomeliaLoc', inputValue: 0, width: 50, hidden: true}, 
																		{boxLabel: 'справа', name: 'DolichostenomeliaLoc', inputValue: 1, width: 70}, 
																		{boxLabel: 'слева', name: 'DolichostenomeliaLoc', inputValue: 2, width: 70},
																		{boxLabel: 'с обеих сторон', name: 'DolichostenomeliaLoc', inputValue: 3, width: 110}
																	],
																	listeners: {
																		'change': function(field, checked) {
																			if((checked) && field.FromInterface){
																				if ((this.NeonatalSurvey_data.NeonatalSurveyParam.DolichostenomeliaLoc) || (String(checked.inputValue) != '0')) {
																					this.NeonatalSurvey_data.NeonatalSurveyParam.DolichostenomeliaLoc = checked.inputValue;									
																					this.changedDatas = true;
																				}
																			}
																			field.FromInterface = true;
																		}.createDelegate(this)
																	}
																})	
															]
														}
													]
												},
												//Обезьянья складка
												{									
													layout:'column',
													items:[				
														{									
															layout:'form',
															labelWidth:200,
															style: 'margin-top: 3px;',
															items:[
																{
																	fieldLabel: 'Обезьянья складка',
																	labelSeparator: '',
																	name: 'MonkeyFold',
																	id: 'swENSEW_MonkeyFold',
																	xtype: 'checkbox',
																	checked: false,
																	listeners: {
																		'check': function(chb, checked ) {
																			if ((this.NeonatalSurvey_data.NeonatalSurveyParam.MonkeyFold) || (checked)) 
																				this.NeonatalSurvey_data.NeonatalSurveyParam.MonkeyFold = checked ? 2 : 1;
																			this.findById('swENSEW_MonkeyFoldLoc').setDisabled(!checked);  //BOB - 06.05.2020
																			if (!checked) {
																				this.findById('swENSEW_MonkeyFoldLoc').items.items[0].setValue(true);
																				for (var i = 1; i < this.findById('swENSEW_MonkeyFoldLoc').items.items.length; i++  ) this.findById('swENSEW_MonkeyFoldLoc').items.items[i].setValue(false);						
																			}		
																			this.changedDatas = true;
																		}.createDelegate(this)
																	}		
																}
															]
														},						
														{									
															layout:'form',
															labelWidth:1,
															style: 'margin-top: 0px;  margin-left: 4px; margin-bottom: 0px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
															items:[
																new Ext.form.RadioGroup({
																	id:'swENSEW_MonkeyFoldLoc',
																	labelSeparator: '',
																	vertical: true,
																	columns: 4,
																	FromInterface: true,
																	items: [
																		{boxLabel: '---', name: 'MonkeyFoldLoc', inputValue: 0, width: 50, hidden: true}, 
																		{boxLabel: 'справа', name: 'MonkeyFoldLoc', inputValue: 1, width: 70}, 
																		{boxLabel: 'слева', name: 'MonkeyFoldLoc', inputValue: 2, width: 70},
																		{boxLabel: 'с обеих сторон', name: 'MonkeyFoldLoc', inputValue: 3, width: 110}
																	],
																	listeners: {
																		'change': function(field, checked) {
																			if((checked) && field.FromInterface){
																				if ((this.NeonatalSurvey_data.NeonatalSurveyParam.MonkeyFoldLoc) || (String(checked.inputValue) != '0')) {
																					this.NeonatalSurvey_data.NeonatalSurveyParam.MonkeyFoldLoc = checked.inputValue;									
																					this.changedDatas = true;
																				}
																			}
																			field.FromInterface = true;
																		}.createDelegate(this)
																	}
																})	
															]
														}
													]
												},
												//Клинодактилия
												{									
													layout:'column',
													items:[				
														{									
															layout:'form',
															labelWidth:200,
															style: 'margin-top: 3px;',
															items:[
																{
																	fieldLabel: 'Клинодактилия',
																	labelSeparator: '',
																	name: 'Clinodactyly',
																	id: 'swENSEW_Clinodactyly',
																	xtype: 'checkbox',
																	checked: false,
																	listeners: {
																		'check': function(chb, checked ) {
																			if ((this.NeonatalSurvey_data.NeonatalSurveyParam.Clinodactyly) || (checked)) 
																				this.NeonatalSurvey_data.NeonatalSurveyParam.Clinodactyly = checked ? 2 : 1;
																			this.findById('swENSEW_ClinodactylyLoc').setDisabled(!checked);  //BOB - 06.05.2020
																			if (!checked) {
																				this.findById('swENSEW_ClinodactylyLoc').items.items[0].setValue(true);
																				for (var i = 1; i < this.findById('swENSEW_ClinodactylyLoc').items.items.length; i++  ) this.findById('swENSEW_ClinodactylyLoc').items.items[i].setValue(false);						
																			}		
																			this.changedDatas = true;
																		}.createDelegate(this)
																	}		
																}
															]
														},						
														{									
															layout:'form',
															labelWidth:1,
															style: 'margin-top: 0px;  margin-left: 4px; margin-bottom: 0px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
															items:[
																new Ext.form.RadioGroup({
																	id:'swENSEW_ClinodactylyLoc',
																	labelSeparator: '',
																	vertical: true,
																	columns: 4,
																	FromInterface: true,
																	items: [
																		{boxLabel: '---', name: 'ClinodactylyLoc', inputValue: 0, width: 50, hidden: true}, 
																		{boxLabel: 'справа', name: 'ClinodactylyLoc', inputValue: 1, width: 70}, 
																		{boxLabel: 'слева', name: 'ClinodactylyLoc', inputValue: 2, width: 70},
																		{boxLabel: 'с обеих сторон', name: 'ClinodactylyLoc', inputValue: 3, width: 110}
																	],
																	listeners: {
																		'change': function(field, checked) {
																			if((checked) && field.FromInterface){
																				if ((this.NeonatalSurvey_data.NeonatalSurveyParam.ClinodactylyLoc) || (String(checked.inputValue) != '0')) {
																					this.NeonatalSurvey_data.NeonatalSurveyParam.ClinodactylyLoc = checked.inputValue;									
																					this.changedDatas = true;
																				}
																			}
																			field.FromInterface = true;
																		}.createDelegate(this)
																	}
																})	
															]
														}
													]
												},
												//Артрогрипоз
												{									
													layout:'column',
													items:[				
														{									
															layout:'form',
															labelWidth:200,
															style: 'margin-top: 3px;',
															items:[
																{
																	fieldLabel: 'Артрогрипоз',
																	labelSeparator: '',
																	name: 'Arthrogryposis',
																	id: 'swENSEW_Arthrogryposis',
																	xtype: 'checkbox',
																	checked: false,
																	listeners: {
																		'check': function(chb, checked ) {
																			if ((this.NeonatalSurvey_data.NeonatalSurveyParam.Arthrogryposis) || (checked)) 
																				this.NeonatalSurvey_data.NeonatalSurveyParam.Arthrogryposis = checked ? 2 : 1;
																			this.findById('swENSEW_ArthrogryposisLoc').setDisabled(!checked);  //BOB - 06.05.2020
																			if (!checked) {
																				this.findById('swENSEW_ArthrogryposisLoc').items.items[0].setValue(true);
																				for (var i = 1; i < this.findById('swENSEW_ArthrogryposisLoc').items.items.length; i++  ) this.findById('swENSEW_ArthrogryposisLoc').items.items[i].setValue(false);						
																			}		
																			this.changedDatas = true;
																		}.createDelegate(this)
																	}		
																}
															]
														},						
														{									
															layout:'form',
															labelWidth:1,
															style: 'margin-top: 0px;  margin-left: 4px; margin-bottom: 0px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
															items:[
																new Ext.form.RadioGroup({
																	id:'swENSEW_ArthrogryposisLoc',
																	labelSeparator: '',
																	vertical: true,
																	columns: 4,
																	FromInterface: true,
																	items: [
																		{boxLabel: '---', name: 'ArthrogryposisLoc', inputValue: 0, width: 50, hidden: true}, 
																		{boxLabel: 'справа', name: 'ArthrogryposisLoc', inputValue: 1, width: 70}, 
																		{boxLabel: 'слева', name: 'ArthrogryposisLoc', inputValue: 2, width: 70},
																		{boxLabel: 'с обеих сторон', name: 'ArthrogryposisLoc', inputValue: 3, width: 110}
																	],
																	listeners: {
																		'change': function(field, checked) {
																			if((checked) && field.FromInterface){
																				if ((this.NeonatalSurvey_data.NeonatalSurveyParam.ArthrogryposisLoc) || (String(checked.inputValue) != '0')) {
																					this.NeonatalSurvey_data.NeonatalSurveyParam.ArthrogryposisLoc = checked.inputValue;									
																					this.changedDatas = true;
																				}
																			}
																			field.FromInterface = true;
																		}.createDelegate(this)
																	}
																})	
															]
														}
													]
												}
											]
										},
										//Нижние конечности
										{									
											layout:'form',
											width: 600,
											items:[
												{xtype:'label',text: 'Нижние конечности',style: 'font-size: 10pt; font-weight: bold; font-style:italic; margin-left: 102pt; margin-top: 2pt; margin-bottom: 2px; color:#15428b'},
												//Эквиноварусная косолапость
												{									
													layout:'column',
													items:[				
														{									
															layout:'form',
															labelWidth:200,
															style: 'margin-top: 3px;',
															items:[
																{
																	fieldLabel: 'Эквиноварусная косолапость',
																	labelSeparator: '',
																	name: 'EquinovarClubfoot',
																	id: 'swENSEW_EquinovarClubfoot',
																	xtype: 'checkbox',
																	checked: false,
																	listeners: {
																		'check': function(chb, checked ) {
																			if ((this.NeonatalSurvey_data.NeonatalSurveyParam.EquinovarClubfoot) || (checked)) 
																				this.NeonatalSurvey_data.NeonatalSurveyParam.EquinovarClubfoot = checked ? 2 : 1;
																			this.findById('swENSEW_EquinovarClubfootLoc').setDisabled(!checked);  //BOB - 06.05.2020
																			if (!checked) {
																				this.findById('swENSEW_EquinovarClubfootLoc').items.items[0].setValue(true);
																				for (var i = 1; i < this.findById('swENSEW_EquinovarClubfootLoc').items.items.length; i++  ) this.findById('swENSEW_EquinovarClubfootLoc').items.items[i].setValue(false);						
																			}		
																			this.changedDatas = true;
																		}.createDelegate(this)
																	}		
																}
															]
														},						
														{									
															layout:'form',
															labelWidth:1,
															style: 'margin-top: 0px;  margin-left: 4px; margin-bottom: 0px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
															items:[
																new Ext.form.RadioGroup({
																	id:'swENSEW_EquinovarClubfootLoc',
																	labelSeparator: '',
																	vertical: true,
																	columns: 4,
																	FromInterface: true,
																	items: [
																		{boxLabel: '---', name: 'EquinovarClubfootLoc', inputValue: 0, width: 50, hidden: true}, 
																		{boxLabel: 'справа', name: 'EquinovarClubfootLoc', inputValue: 1, width: 70}, 
																		{boxLabel: 'слева', name: 'EquinovarClubfootLoc', inputValue: 2, width: 70},
																		{boxLabel: 'с обеих сторон', name: 'EquinovarClubfootLoc', inputValue: 3, width: 110}
																	],
																	listeners: {
																		'change': function(field, checked) {
																			if((checked) && field.FromInterface){
																				if ((this.NeonatalSurvey_data.NeonatalSurveyParam.EquinovarClubfootLoc) || (String(checked.inputValue) != '0')) {
																					this.NeonatalSurvey_data.NeonatalSurveyParam.EquinovarClubfootLoc = checked.inputValue;									
																					this.changedDatas = true;
																				}
																			}
																			field.FromInterface = true;
																		}.createDelegate(this)
																	}
																})	
															]
														}
													]
												},
												//Варусная деформация стопы
												{									
													layout:'column',
													items:[				
														{									
															layout:'form',
															labelWidth:200,
															style: 'margin-top: 3px;',
															items:[
																{
																	fieldLabel: 'Варусная деформация стопы',
																	labelSeparator: '',
																	name: 'VarusDeformitFoot',
																	id: 'swENSEW_VarusDeformitFoot',
																	xtype: 'checkbox',
																	checked: false,
																	listeners: {
																		'check': function(chb, checked ) {
																			if ((this.NeonatalSurvey_data.NeonatalSurveyParam.VarusDeformitFoot) || (checked)) 
																				this.NeonatalSurvey_data.NeonatalSurveyParam.VarusDeformitFoot = checked ? 2 : 1;
																			this.findById('swENSEW_VarusDeformitFootLoc').setDisabled(!checked);  //BOB - 06.05.2020
																			if (!checked) {
																				this.findById('swENSEW_VarusDeformitFootLoc').items.items[0].setValue(true);
																				for (var i = 1; i < this.findById('swENSEW_VarusDeformitFootLoc').items.items.length; i++  ) this.findById('swENSEW_VarusDeformitFootLoc').items.items[i].setValue(false);						
																			}		
																			this.changedDatas = true;
																		}.createDelegate(this)
																	}		
																}
															]
														},						
														{									
															layout:'form',
															labelWidth:1,
															style: 'margin-top: 0px;  margin-left: 4px; margin-bottom: 0px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
															items:[
																new Ext.form.RadioGroup({
																	id:'swENSEW_VarusDeformitFootLoc',
																	labelSeparator: '',
																	vertical: true,
																	columns: 4,
																	FromInterface: true,
																	items: [
																		{boxLabel: '---', name: 'VarusDeformitFootLoc', inputValue: 0, width: 50, hidden: true}, 
																		{boxLabel: 'справа', name: 'VarusDeformitFootLoc', inputValue: 1, width: 70}, 
																		{boxLabel: 'слева', name: 'VarusDeformitFootLoc', inputValue: 2, width: 70},
																		{boxLabel: 'с обеих сторон', name: 'VarusDeformitFootLoc', inputValue: 3, width: 110}
																	],
																	listeners: {
																		'change': function(field, checked) {
																			if((checked) && field.FromInterface){
																				if ((this.NeonatalSurvey_data.NeonatalSurveyParam.VarusDeformitFootLoc) || (String(checked.inputValue) != '0')) {
																					this.NeonatalSurvey_data.NeonatalSurveyParam.VarusDeformitFootLoc = checked.inputValue;									
																					this.changedDatas = true;
																				}
																			}
																			field.FromInterface = true;
																		}.createDelegate(this)
																	}
																})	
															]
														}
													]
												},
												//Стопа–качалка
												{									
													layout:'column',
													items:[				
														{									
															layout:'form',
															labelWidth:200,
															style: 'margin-top: 3px;',
															items:[
																{
																	fieldLabel: 'Стопа–качалка',
																	labelSeparator: '',
																	name: 'RockingFoot',
																	id: 'swENSEW_RockingFoot',
																	xtype: 'checkbox',
																	checked: false,
																	listeners: {
																		'check': function(chb, checked ) {
																			if ((this.NeonatalSurvey_data.NeonatalSurveyParam.RockingFoot) || (checked)) 
																				this.NeonatalSurvey_data.NeonatalSurveyParam.RockingFoot = checked ? 2 : 1;
																			this.findById('swENSEW_RockingFootLoc').setDisabled(!checked);  //BOB - 06.05.2020
																			if (!checked) {
																				this.findById('swENSEW_RockingFootLoc').items.items[0].setValue(true);
																				for (var i = 1; i < this.findById('swENSEW_RockingFootLoc').items.items.length; i++  ) this.findById('swENSEW_RockingFootLoc').items.items[i].setValue(false);						
																			}		
																			this.changedDatas = true;
																		}.createDelegate(this)
																	}		
																}
															]
														},						
														{									
															layout:'form',
															labelWidth:1,
															style: 'margin-top: 0px;  margin-left: 4px; margin-bottom: 0px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
															items:[
																new Ext.form.RadioGroup({
																	id:'swENSEW_RockingFootLoc',
																	labelSeparator: '',
																	vertical: true,
																	columns: 4,
																	FromInterface: true,
																	items: [
																		{boxLabel: '---', name: 'RockingFootLoc', inputValue: 0, width: 50, hidden: true}, 
																		{boxLabel: 'справа', name: 'RockingFootLoc', inputValue: 1, width: 70}, 
																		{boxLabel: 'слева', name: 'RockingFootLoc', inputValue: 2, width: 70},
																		{boxLabel: 'с обеих сторон', name: 'RockingFootLoc', inputValue: 3, width: 110}
																	],
																	listeners: {
																		'change': function(field, checked) {
																			if((checked) && field.FromInterface){
																				if ((this.NeonatalSurvey_data.NeonatalSurveyParam.RockingFootLoc) || (String(checked.inputValue) != '0')){
																					this.NeonatalSurvey_data.NeonatalSurveyParam.RockingFootLoc = checked.inputValue;									
																					this.changedDatas = true;
																				} 
																			}
																			field.FromInterface = true;
																		}.createDelegate(this)
																	}
																})	
															]
														}
													]
												},
												//Торсия большеберцовой кости
												{									
													layout:'column',
													items:[				
														{									
															layout:'form',
															labelWidth:200,
															style: 'margin-top: 3px;',
															items:[
																{
																	fieldLabel: 'Торсия большеберцовой кости',
																	labelSeparator: '',
																	name: 'TorsionTibia',
																	id: 'swENSEW_TorsionTibia',
																	xtype: 'checkbox',
																	checked: false,
																	listeners: {
																		'check': function(chb, checked ) {
																			if ((this.NeonatalSurvey_data.NeonatalSurveyParam.TorsionTibia) || (checked)) 
																				this.NeonatalSurvey_data.NeonatalSurveyParam.TorsionTibia = checked ? 2 : 1;
																			this.findById('swENSEW_TorsionTibiaLoc').setDisabled(!checked);  //BOB - 06.05.2020
																			if (!checked) {
																				this.findById('swENSEW_TorsionTibiaLoc').items.items[0].setValue(true);
																				for (var i = 1; i < this.findById('swENSEW_TorsionTibiaLoc').items.items.length; i++  ) this.findById('swENSEW_TorsionTibiaLoc').items.items[i].setValue(false);						
																			}		
																			this.changedDatas = true;
																		}.createDelegate(this)
																	}		
																}
															]
														},						
														{									
															layout:'form',
															labelWidth:1,
															style: 'margin-top: 0px;  margin-left: 4px; margin-bottom: 0px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
															items:[
																new Ext.form.RadioGroup({
																	id:'swENSEW_TorsionTibiaLoc',
																	labelSeparator: '',
																	vertical: true,
																	columns: 4,
																	FromInterface: true,
																	items: [
																		{boxLabel: '---', name: 'TorsionTibiaLoc', inputValue: 0, width: 50, hidden: true}, 
																		{boxLabel: 'справа', name: 'TorsionTibiaLoc', inputValue: 1, width: 70}, 
																		{boxLabel: 'слева', name: 'TorsionTibiaLoc', inputValue: 2, width: 70},
																		{boxLabel: 'с обеих сторон', name: 'TorsionTibiaLoc', inputValue: 3, width: 110}
																	],
																	listeners: {
																		'change': function(field, checked) {
																			if((checked) && field.FromInterface){
																				if ((this.NeonatalSurvey_data.NeonatalSurveyParam.TorsionTibiaLoc) || (String(checked.inputValue) != '0')) {
																					this.NeonatalSurvey_data.NeonatalSurveyParam.TorsionTibiaLoc = checked.inputValue;									
																					this.changedDatas = true;
																				}
																			}
																			field.FromInterface = true;
																		}.createDelegate(this)
																	}
																})	
															]
														}
													]
												},
												//Рекурвация колена
												{									
													layout:'column',
													items:[				
														{									
															layout:'form',
															labelWidth:200,
															style: 'margin-top: 3px;',
															items:[
																{
																	fieldLabel: 'Рекурвация колена',
																	labelSeparator: '',
																	name: 'KneeRecurvat',
																	id: 'swENSEW_KneeRecurvat',
																	xtype: 'checkbox',
																	checked: false,
																	listeners: {
																		'check': function(chb, checked ) {
																			if ((this.NeonatalSurvey_data.NeonatalSurveyParam.KneeRecurvat) || (checked)) 
																				this.NeonatalSurvey_data.NeonatalSurveyParam.KneeRecurvat = checked ? 2 : 1;
																			this.findById('swENSEW_KneeRecurvatLoc').setDisabled(!checked);  //BOB - 06.05.2020
																			if (!checked) {
																				this.findById('swENSEW_KneeRecurvatLoc').items.items[0].setValue(true);
																				for (var i = 1; i < this.findById('swENSEW_KneeRecurvatLoc').items.items.length; i++  ) this.findById('swENSEW_KneeRecurvatLoc').items.items[i].setValue(false);						
																			}		
																			this.changedDatas = true;
																		}.createDelegate(this)
																	}		
																}
															]
														},						
														{									
															layout:'form',
															labelWidth:1,
															style: 'margin-top: 0px;  margin-left: 4px; margin-bottom: 0px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
															items:[
																new Ext.form.RadioGroup({
																	id:'swENSEW_KneeRecurvatLoc',
																	labelSeparator: '',
																	vertical: true,
																	columns: 4,
																	FromInterface: true,
																	items: [
																		{boxLabel: '---', name: 'KneeRecurvatLoc', inputValue: 0, width: 50, hidden: true}, 
																		{boxLabel: 'справа', name: 'KneeRecurvatLoc', inputValue: 1, width: 70}, 
																		{boxLabel: 'слева', name: 'KneeRecurvatLoc', inputValue: 2, width: 70},
																		{boxLabel: 'с обеих сторон', name: 'KneeRecurvatLoc', inputValue: 3, width: 110}
																	],
																	listeners: {
																		'change': function(field, checked) {
																			if((checked) && field.FromInterface){
																				if ((this.NeonatalSurvey_data.NeonatalSurveyParam.KneeRecurvatLoc) || (String(checked.inputValue) != '0')) {
																					this.NeonatalSurvey_data.NeonatalSurveyParam.KneeRecurvatLoc = checked.inputValue;									
																					this.changedDatas = true;
																				}
																			}
																			field.FromInterface = true;
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
								},
								//Ампутация верхней, нижней конечностей
								{									
									layout:'column',
									items:[	
										{									
											layout:'form',
											labelWidth:200,
											style: 'margin-top: 3px;',
											items:[
												{
													fieldLabel: 'Ампутация верхней, нижней конечностей',
													labelSeparator: '',
													name: 'Amputation',
													id: 'swENSEW_Amputation',
													xtype: 'checkbox',
													checked: false,
													listeners: {
														'check': function(chb, checked ) {
															if ((this.NeonatalSurvey_data.NeonatalSurveyParam.Amputation) || (checked)) { 
																this.NeonatalSurvey_data.NeonatalSurveyParam.Amputation = checked ? 2 : 1;		
																this.changedDatas = true;
																this.findById('swENSEW_AmputationTxt').setDisabled(!checked);  //BOB - 06.05.2020
																this.findById('swENSEW_AmputationLoc').setDisabled(!checked);  //BOB - 06.05.2020
																if (!checked) {
																	this.findById('swENSEW_AmputationTxt').setValue('');
																	delete this.NeonatalSurvey_data.NeonatalSurveyParam.AmputationTxt;
																	this.findById('swENSEW_AmputationLoc').setValue('');
																	delete this.NeonatalSurvey_data.NeonatalSurveyParam.AmputationLoc;
																}
															}
														}.createDelegate(this)
													}		
												}
											]
										},										
										{									
											layout:'form',
											labelWidth:200,
											style: 'margin-top: 3px;',
											items:[										
												{
													allowBlank: true,
													fieldLabel: 'описание',
													labelSeparator: '',
													name: 'swENSEW_AmputationTxt',
													id: 'swENSEW_AmputationTxt',
													width: 850,
													value:'',
													style: 'margin-top: 1px;  margin-left: 4px; ',
													xtype: 'textfield',
													listeners:{
														'change':function (field, newValue, oldValue) {
															this.NeonatalSurvey_data.NeonatalSurveyParam.AmputationTxt = newValue;
															this.changedDatas = true;
														}.createDelegate(this)
													}
												}						
											]
										},
										{									
											layout:'form',
											labelWidth:200,
											style: 'margin-top: 3px;',
											items:[										
												{
													allowBlank: true,
													fieldLabel: 'локализация',
													labelSeparator: '',
													name: 'swENSEW_AmputationLoc',
													id: 'swENSEW_AmputationLoc',
													width: 850,
													value:'',
													style: 'margin-top: 1px;  margin-left: 4px; ',
													xtype: 'textfield',
													listeners:{
														'change':function (field, newValue, oldValue) {
															this.NeonatalSurvey_data.NeonatalSurveyParam.AmputationLoc = newValue;
															this.changedDatas = true;
														}.createDelegate(this)
													}
												}						
											]
										},
									]
								},
								{									
									layout:'form',
									labelWidth:200,
									style: 'margin-top: 3px;',
									items:[
										{
											fieldLabel: 'Макродактилия',
											labelSeparator: '',
											name: 'Macrodactyly',
											id: 'swENSEW_Macrodactyly',
											xtype: 'checkbox',
											checked: false,
											listeners: {
												'check': function(chb, checked ) {
													if ((this.NeonatalSurvey_data.NeonatalSurveyParam.Macrodactyly) || (checked)) {
														this.NeonatalSurvey_data.NeonatalSurveyParam.Macrodactyly = checked ? 2 : 1;		
														this.changedDatas = true;
													}
												}.createDelegate(this)
											}		
										}
									]
								}										
							]
						},
						//Туловище и спина   SpinaBifida
						{
							id: 'swENSEW_Spina_Panel',
							xtype: 'fieldset',
							autoHeight: true,
							title: langs('Туловище и спина'),
							collapsible: true,
							layout: 'form',
							labelWidth: 250,
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
									layout:'column',
									items:[	
										//Туловище - пигментация
										{									
											layout:'form',
											labelWidth:200,
											style: 'margin-top: 3px;',
											items:[
												{
													fieldLabel: 'Туловище - пигментация',
													labelSeparator: '',
													name: 'TorsoPigment',
													id: 'swENSEW_TorsoPigment',
													xtype: 'checkbox',
													checked: false,
													listeners: {
														'check': function(chb, checked ) {
															if ((this.NeonatalSurvey_data.NeonatalSurveyParam.TorsoPigment) || (checked)) {
																this.NeonatalSurvey_data.NeonatalSurveyParam.TorsoPigment = checked ? 2 : 1;		
																this.changedDatas = true;
															}
														}.createDelegate(this)
													}		
												}
											]
										},
										//Оволосение на пояснице										
										{									
											layout:'form',
											labelWidth:200,
											style: 'margin-top: 3px;',
											items:[
												{
													fieldLabel: 'Оволосение на пояснице',
													labelSeparator: '',
													name: 'HairLowerBack',
													id: 'swENSEW_HairLowerBack',
													xtype: 'checkbox',
													checked: false,
													listeners: {
														'check': function(chb, checked ) {
															if ((this.NeonatalSurvey_data.NeonatalSurveyParam.HairLowerBack) || (checked)) {
																this.NeonatalSurvey_data.NeonatalSurveyParam.HairLowerBack = checked ? 2 : 1;		
																this.changedDatas = true;
															}
														}.createDelegate(this)
													}		
												}
											]
										}										
									]
								},
								//Spina bifida
								{									
									layout:'column',
									items:[
										{xtype:'label',text: 'Spina bifida',style: 'font-size: 10pt;  margin-left: 12pt; margin-top: 2pt; margin-bottom: 2px'},
										{									
											layout:'form',
											labelWidth:1,
											style: 'margin-top: 4px;  margin-left: 4px; margin-bottom: 1px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											items:[
												new Ext.form.RadioGroup({
													id:'swENSEW_SpinaBifida1',
													labelSeparator: '',
													vertical: true,
													FromInterface: true,
													columns: 3,
													items: [
														{boxLabel: 'нет', name: 'SpinaBifida1', inputValue: 0, width: 50}, 
														{boxLabel: 'Spina bifida occulta', name: 'SpinaBifida1', inputValue: 1, width: 130}, 
														{boxLabel: 'Spina bifida cystica', name: 'SpinaBifida1', inputValue: 2, width: 130}
													],
													listeners: {
														'change': function(field, checked) {
															if(field.FromInterface) win.findById('swENSEW_Spina_Panel').change_handler(field, checked);
															field.FromInterface = true;
														}
													}
												}),
											]
										},
										{									
											layout:'form',
											labelWidth:1,
											style: 'margin-top: 4px;  margin-left: 4px; margin-bottom: 1px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											items:[
												new Ext.form.RadioGroup({
													id:'swENSEW_SpinaBifida2',
													labelSeparator: '',
													vertical: true,
													FromInterface: true,
													columns: 4,
													items: [
														{boxLabel: '---', name: 'SpinaBifida2', inputValue: 0, width: 50, hidden: true}, 
														{boxLabel: 'менингоцеле', name: 'SpinaBifida2', inputValue: 1, width: 130}, 
														{boxLabel: 'миелоцеле', name: 'SpinaBifida2', inputValue: 2, width: 130},
														{boxLabel: 'миеломенингоцеле', name: 'SpinaBifida2', inputValue: 3, width: 170}
													],
													listeners: {
														'change': function(field, checked) {
															if(field.FromInterface) win.findById('swENSEW_Spina_Panel').change_handler(field, checked);
															field.FromInterface = true;
														}
													}
												})	
											]
										}
									]
								}
							],
							change_handler: function(field, checked) {
								if(checked){
									var SpinaBifida = (this.NeonatalSurvey_data.NeonatalSurveyParam.SpinaBifida) ? this.NeonatalSurvey_data.NeonatalSurveyParam.SpinaBifida : '00';
									var position = parseInt(field.id.substr(field.id.length - 1, 1));
									var value = checked.inputValue;
									//если меняется выбранный в первой группе то обнуляются вторая и третья
									if (position == 1) {
										this.findById('swENSEW_SpinaBifida2').items.items[0].setValue(true);		
										for (var i = 1; i < this.findById('swENSEW_SpinaBifida2').items.items.length; i++  ) this.findById('swENSEW_SpinaBifida2').items.items[i].setValue(false);						
										this.findById('swENSEW_SpinaBifida2').disable();						
										if(value == 2)	this.findById('swENSEW_SpinaBifida2').enable();	
									}
									SpinaBifida = SpinaBifida.split("");
									SpinaBifida[position - 1] = value;
									SpinaBifida = SpinaBifida.join("");
									if (!Ext.isEmpty(this.NeonatalSurvey_data.NeonatalSurveyParam.SpinaBifida) || (SpinaBifida != '00')) 
										this.NeonatalSurvey_data.NeonatalSurveyParam.SpinaBifida = SpinaBifida;									
									this.changedDatas = true;
								}
							}.createDelegate(this)
						},
						//Бёдра   
						{
							id: 'swENSEW_Hips_Panel',
							xtype: 'fieldset',
							autoHeight: true,
							title: langs('Бёдра'),
							collapsible: true,
							layout: 'form',
							labelWidth: 400,
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
								//Дисплазия тазобедренного сустава
								{									
									layout:'column',
									items:[				
										{									
											layout:'form',
											labelWidth:400,
											style: 'margin-top: 3px;',
											items:[
												{
													fieldLabel: 'Дисплазия тазобедренного сустава',
													labelSeparator: '',
													name: 'CaninHipDysplas',
													id: 'swENSEW_CaninHipDysplas',
													xtype: 'checkbox',
													checked: false,
													listeners: {
														'check': function(chb, checked ) {
															if ((this.NeonatalSurvey_data.NeonatalSurveyParam.CaninHipDysplas) || (checked)) 
																this.NeonatalSurvey_data.NeonatalSurveyParam.CaninHipDysplas = checked ? 2 : 1;
															this.findById('swENSEW_CaninHipDysplasLoc').setDisabled(!checked);  //BOB - 06.05.2020
															if (!checked) {
																this.findById('swENSEW_CaninHipDysplasLoc').items.items[0].setValue(true);
																for (var i = 1; i < this.findById('swENSEW_CaninHipDysplasLoc').items.items.length; i++  ) this.findById('swENSEW_CaninHipDysplasLoc').items.items[i].setValue(false);						
															}		
															this.changedDatas = true;
														}.createDelegate(this)
													}		
												}
											]
										},						
										{									
											layout:'form',
											labelWidth:1,
											style: 'margin-top: 0px;  margin-left: 4px; margin-bottom: 0px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											items:[
												new Ext.form.RadioGroup({
													id:'swENSEW_CaninHipDysplasLoc',
													labelSeparator: '',
													vertical: true,
													columns: 4,
													FromInterface: true,
													items: [
														{boxLabel: '---', name: 'CaninHipDysplasLoc', inputValue: 0, width: 50, hidden: true}, 
														{boxLabel: 'справа', name: 'CaninHipDysplasLoc', inputValue: 1, width: 70}, 
														{boxLabel: 'слева', name: 'CaninHipDysplasLoc', inputValue: 2, width: 70},
														{boxLabel: 'с обеих сторон', name: 'CaninHipDysplasLoc', inputValue: 3, width: 110}
													],
													listeners: {
														'change': function(field, checked) {
															if((checked) && field.FromInterface){
																if ((this.NeonatalSurvey_data.NeonatalSurveyParam.CaninHipDysplasLoc) || (String(checked.inputValue) != '0')) {
																	this.NeonatalSurvey_data.NeonatalSurveyParam.CaninHipDysplasLoc = checked.inputValue;									
																	this.changedDatas = true;
																}
															}
															field.FromInterface = true;
														}.createDelegate(this)
													}
												})	
											]
										}
									]
								},
								//Врожденный вывих бедра
								{									
									layout:'column',
									items:[				
										{									
											layout:'form',
											labelWidth:400,
											style: 'margin-top: 3px;',
											items:[
												{
													fieldLabel: 'Врожденный вывих бедра',
													labelSeparator: '',
													name: 'CongenHipDislocat',
													id: 'swENSEW_CongenHipDislocat',
													xtype: 'checkbox',
													checked: false,
													listeners: {
														'check': function(chb, checked ) {
															if ((this.NeonatalSurvey_data.NeonatalSurveyParam.CongenHipDislocat) || (checked)) 
																this.NeonatalSurvey_data.NeonatalSurveyParam.CongenHipDislocat = checked ? 2 : 1;
															this.findById('swENSEW_CongenHipDislocatLoc').setDisabled(!checked);  //BOB - 06.05.2020
															if (!checked) {
																this.findById('swENSEW_CongenHipDislocatLoc').items.items[0].setValue(true);
																for (var i = 1; i < this.findById('swENSEW_CongenHipDislocatLoc').items.items.length; i++  ) this.findById('swENSEW_CongenHipDislocatLoc').items.items[i].setValue(false);						
															}		
															this.changedDatas = true;
														}.createDelegate(this)
													}		
												}
											]
										},						
										{									
											layout:'form',
											labelWidth:1,
											style: 'margin-top: 0px;  margin-left: 4px; margin-bottom: 0px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											items:[
												new Ext.form.RadioGroup({
													id:'swENSEW_CongenHipDislocatLoc',
													labelSeparator: '',
													vertical: true,
													columns: 4,
													FromInterface: true,
													items: [
														{boxLabel: '---', name: 'CongenHipDislocatLoc', inputValue: 0, width: 50, hidden: true}, 
														{boxLabel: 'справа', name: 'CongenHipDislocatLoc', inputValue: 1, width: 70}, 
														{boxLabel: 'слева', name: 'CongenHipDislocatLoc', inputValue: 2, width: 70},
														{boxLabel: 'с обеих сторон', name: 'CongenHipDislocatLoc', inputValue: 3, width: 110}
													],
													listeners: {
														'change': function(field, checked) {
															if((checked) && field.FromInterface){
																if ((this.NeonatalSurvey_data.NeonatalSurveyParam.CongenHipDislocatLoc) || (String(checked.inputValue) != '0')) {
																	this.NeonatalSurvey_data.NeonatalSurveyParam.CongenHipDislocatLoc = checked.inputValue;									
																	this.changedDatas = true;
																}
															}
															field.FromInterface = true;
														}.createDelegate(this)
													}
												})	
											]
										}
									]
								},
								//Гемимелия малоберцовой кости
								{									
									layout:'column',
									items:[				
										{									
											layout:'form',
											labelWidth:400,
											style: 'margin-top: 3px;',
											items:[
												{
													fieldLabel: 'Гемимелия малоберцовой кости',
													labelSeparator: '',
													name: 'HemimelFibula',
													id: 'swENSEW_HemimelFibula',
													xtype: 'checkbox',
													checked: false,
													listeners: {
														'check': function(chb, checked ) {
															if ((this.NeonatalSurvey_data.NeonatalSurveyParam.HemimelFibula) || (checked)) 
																this.NeonatalSurvey_data.NeonatalSurveyParam.HemimelFibula = checked ? 2 : 1;
															this.findById('swENSEW_HemimelFibulaLoc').setDisabled(!checked);  //BOB - 06.05.2020
															if (!checked) {
																this.findById('swENSEW_HemimelFibulaLoc').items.items[0].setValue(true);
																for (var i = 1; i < this.findById('swENSEW_HemimelFibulaLoc').items.items.length; i++  ) this.findById('swENSEW_HemimelFibulaLoc').items.items[i].setValue(false);						
															}		
															this.changedDatas = true;
														}.createDelegate(this)
													}		
												}
											]
										},						
										{									
											layout:'form',
											labelWidth:1,
											style: 'margin-top: 0px;  margin-left: 4px; margin-bottom: 0px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											items:[
												new Ext.form.RadioGroup({
													id:'swENSEW_HemimelFibulaLoc',
													labelSeparator: '',
													vertical: true,
													columns: 4,
													FromInterface: true,
													items: [
														{boxLabel: '---', name: 'HemimelFibulaLoc', inputValue: 0, width: 50, hidden: true}, 
														{boxLabel: 'справа', name: 'HemimelFibulaLoc', inputValue: 1, width: 70}, 
														{boxLabel: 'слева', name: 'HemimelFibulaLoc', inputValue: 2, width: 70},
														{boxLabel: 'с обеих сторон', name: 'HemimelFibulaLoc', inputValue: 3, width: 110}
													],
													listeners: {
														'change': function(field, checked) {
															if((checked) && field.FromInterface){
																if ((this.NeonatalSurvey_data.NeonatalSurveyParam.HemimelFibulaLoc) || (String(checked.inputValue) != '0')) {
																	this.NeonatalSurvey_data.NeonatalSurveyParam.HemimelFibulaLoc = checked.inputValue;									
																	this.changedDatas = true;
																}
															}
															field.FromInterface = true;
														}.createDelegate(this)
													}
												})	
											]
										}
									]
								},
								//Дефицит проксимального отдела бедра
								{									
									layout:'column',
									items:[				
										{									
											layout:'form',
											labelWidth:400,
											style: 'margin-top: 3px;',
											items:[
												{
													fieldLabel: 'Дефицит проксимального отдела бедра',
													labelSeparator: '',
													name: 'DeficProximFemur',
													id: 'swENSEW_DeficProximFemur',
													xtype: 'checkbox',
													checked: false,
													listeners: {
														'check': function(chb, checked ) {
															if ((this.NeonatalSurvey_data.NeonatalSurveyParam.DeficProximFemur) || (checked)) 
																this.NeonatalSurvey_data.NeonatalSurveyParam.DeficProximFemur = checked ? 2 : 1;
															this.findById('swENSEW_DeficProximFemurLoc').setDisabled(!checked);  //BOB - 06.05.2020
															if (!checked) {
																this.findById('swENSEW_DeficProximFemurLoc').items.items[0].setValue(true);
																for (var i = 1; i < this.findById('swENSEW_DeficProximFemurLoc').items.items.length; i++  ) this.findById('swENSEW_DeficProximFemurLoc').items.items[i].setValue(false);						
															}		
															this.changedDatas = true;
														}.createDelegate(this)
													}		
												}
											]
										},						
										{									
											layout:'form',
											labelWidth:1,
											style: 'margin-top: 0px;  margin-left: 4px; margin-bottom: 0px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											items:[
												new Ext.form.RadioGroup({
													id:'swENSEW_DeficProximFemurLoc',
													labelSeparator: '',
													vertical: true,
													columns: 4,
													FromInterface: true,
													items: [
														{boxLabel: '---', name: 'DeficProximFemurLoc', inputValue: 0, width: 50, hidden: true}, 
														{boxLabel: 'справа', name: 'DeficProximFemurLoc', inputValue: 1, width: 70}, 
														{boxLabel: 'слева', name: 'DeficProximFemurLoc', inputValue: 2, width: 70},
														{boxLabel: 'с обеих сторон', name: 'DeficProximFemurLoc', inputValue: 3, width: 110}
													],
													listeners: {
														'change': function(field, checked) {
															if((checked) && field.FromInterface){
																if ((this.NeonatalSurvey_data.NeonatalSurveyParam.DeficProximFemurLoc) || (String(checked.inputValue) != '0')) {
																	this.NeonatalSurvey_data.NeonatalSurveyParam.DeficProximFemurLoc = checked.inputValue;									
																	this.changedDatas = true;
																}
															}
															field.FromInterface = true;
														}.createDelegate(this)
													}
												})	
											]
										}
									]
								},
								//Тибиальная гемимелия
								{									
									layout:'column',
									items:[				
										{									
											layout:'form',
											labelWidth:400,
											style: 'margin-top: 3px;',
											items:[
												{
													fieldLabel: 'Тибиальная гемимелия',
													labelSeparator: '',
													name: 'TibialHemimel',
													id: 'swENSEW_TibialHemimel',
													xtype: 'checkbox',
													checked: false,
													listeners: {
														'check': function(chb, checked ) {
															if ((this.NeonatalSurvey_data.NeonatalSurveyParam.TibialHemimel) || (checked)) 
																this.NeonatalSurvey_data.NeonatalSurveyParam.TibialHemimel = checked ? 2 : 1;
															this.findById('swENSEW_TibialHemimelLoc').setDisabled(!checked);  //BOB - 06.05.2020
															if (!checked) {
																this.findById('swENSEW_TibialHemimelLoc').items.items[0].setValue(true);
																for (var i = 1; i < this.findById('swENSEW_TibialHemimelLoc').items.items.length; i++  ) this.findById('swENSEW_TibialHemimelLoc').items.items[i].setValue(false);						
															}		
															this.changedDatas = true;
														}.createDelegate(this)
													}		
												}
											]
										},						
										{									
											layout:'form',
											labelWidth:1,
											style: 'margin-top: 0px;  margin-left: 4px; margin-bottom: 0px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											items:[
												new Ext.form.RadioGroup({
													id:'swENSEW_TibialHemimelLoc',
													labelSeparator: '',
													vertical: true,
													columns: 4,
													FromInterface: true,
													items: [
														{boxLabel: '---', name: 'TibialHemimelLoc', inputValue: 0, width: 50, hidden: true}, 
														{boxLabel: 'справа', name: 'TibialHemimelLoc', inputValue: 1, width: 70}, 
														{boxLabel: 'слева', name: 'TibialHemimelLoc', inputValue: 2, width: 70},
														{boxLabel: 'с обеих сторон', name: 'TibialHemimelLoc', inputValue: 3, width: 110}
													],
													listeners: {
														'change': function(field, checked) {
															if((checked) && field.FromInterface){
																if ((this.NeonatalSurvey_data.NeonatalSurveyParam.TibialHemimelLoc) || (String(checked.inputValue) != '0')) {
																	this.NeonatalSurvey_data.NeonatalSurveyParam.TibialHemimelLoc = checked.inputValue;									
																	this.changedDatas = true;
																}
															}
															field.FromInterface = true;
														}.createDelegate(this)
													}
												})	
											]
										}
									]
								},
								//Постмедиальное искревление
								{									
									layout:'column',
									items:[				
										{									
											layout:'form',
											labelWidth:400,
											style: 'margin-top: 3px;',
											items:[
												{
													fieldLabel: 'Постмедиальное искривление голени новорожденных',
													labelSeparator: '',
													name: 'PostmedCurvat',
													id: 'swENSEW_PostmedCurvat',
													xtype: 'checkbox',
													checked: false,
													listeners: {
														'check': function(chb, checked ) {
															if ((this.NeonatalSurvey_data.NeonatalSurveyParam.PostmedCurvat) || (checked)) 
																this.NeonatalSurvey_data.NeonatalSurveyParam.PostmedCurvat = checked ? 2 : 1;
															this.findById('swENSEW_PostmedCurvatLoc').setDisabled(!checked);  //BOB - 06.05.2020
															if (!checked) {
																this.findById('swENSEW_PostmedCurvatLoc').items.items[0].setValue(true);
																for (var i = 1; i < this.findById('swENSEW_PostmedCurvatLoc').items.items.length; i++  ) this.findById('swENSEW_PostmedCurvatLoc').items.items[i].setValue(false);						
															}		
															this.changedDatas = true;
														}.createDelegate(this)
													}		
												}
											]
										},						
										{									
											layout:'form',
											labelWidth:1,
											style: 'margin-top: 0px;  margin-left: 4px; margin-bottom: 0px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											items:[
												new Ext.form.RadioGroup({
													id:'swENSEW_PostmedCurvatLoc',
													labelSeparator: '',
													vertical: true,
													columns: 4,
													FromInterface: true,
													items: [
														{boxLabel: '---', name: 'PostmedCurvatLoc', inputValue: 0, width: 50, hidden: true}, 
														{boxLabel: 'справа', name: 'PostmedCurvatLoc', inputValue: 1, width: 70}, 
														{boxLabel: 'слева', name: 'PostmedCurvatLoc', inputValue: 2, width: 70},
														{boxLabel: 'с обеих сторон', name: 'PostmedCurvatLoc', inputValue: 3, width: 110}
													],
													listeners: {
														'change': function(field, checked) {
															if((checked) && field.FromInterface){
																if ((this.NeonatalSurvey_data.NeonatalSurveyParam.PostmedCurvatLoc) || (String(checked.inputValue) != '0')) {
																	this.NeonatalSurvey_data.NeonatalSurveyParam.PostmedCurvatLoc = checked.inputValue;									
																	this.changedDatas = true;																	
																}
															}
															field.FromInterface = true;
														}.createDelegate(this)
													}
												})	
											]
										}
									]
								},
								//Врожденный вывих колена
								{									
									layout:'column',
									items:[				
										{									
											layout:'form',
											labelWidth:400,
											style: 'margin-top: 3px;',
											items:[
												{
													fieldLabel: 'Врожденный вывих колена',
													labelSeparator: '',
													name: 'CongenDislocKnee',
													id: 'swENSEW_CongenDislocKnee',
													xtype: 'checkbox',
													checked: false,
													listeners: {
														'check': function(chb, checked ) {
															if ((this.NeonatalSurvey_data.NeonatalSurveyParam.CongenDislocKnee) || (checked)) 
																this.NeonatalSurvey_data.NeonatalSurveyParam.CongenDislocKnee = checked ? 2 : 1;
															this.findById('swENSEW_CongenDislocKneeLoc').setDisabled(!checked);  //BOB - 06.05.2020
															if (!checked) {
																this.findById('swENSEW_CongenDislocKneeLoc').items.items[0].setValue(true);
																for (var i = 1; i < this.findById('swENSEW_CongenDislocKneeLoc').items.items.length; i++  ) this.findById('swENSEW_CongenDislocKneeLoc').items.items[i].setValue(false);						
															}		
															this.changedDatas = true;
														}.createDelegate(this)
													}		
												}
											]
										},						
										{									
											layout:'form',
											labelWidth:1,
											style: 'margin-top: 0px;  margin-left: 4px; margin-bottom: 0px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											items:[
												new Ext.form.RadioGroup({
													id:'swENSEW_CongenDislocKneeLoc',
													labelSeparator: '',
													vertical: true,
													columns: 4,
													FromInterface: true,
													items: [
														{boxLabel: '---', name: 'CongenDislocKneeLoc', inputValue: 0, width: 50, hidden: true}, 
														{boxLabel: 'справа', name: 'CongenDislocKneeLoc', inputValue: 1, width: 70}, 
														{boxLabel: 'слева', name: 'CongenDislocKneeLoc', inputValue: 2, width: 70},
														{boxLabel: 'с обеих сторон', name: 'CongenDislocKneeLoc', inputValue: 3, width: 110}
													],
													listeners: {
														'change': function(field, checked) {
															if((checked) && field.FromInterface){
																if ((this.NeonatalSurvey_data.NeonatalSurveyParam.CongenDislocKneeLoc) || (String(checked.inputValue) != '0')) {
																	this.NeonatalSurvey_data.NeonatalSurveyParam.CongenDislocKneeLoc = checked.inputValue;									
																	this.changedDatas = true;
																}
															}
															field.FromInterface = true;
														}.createDelegate(this)
													}
												})	
											]
										}
									]
								},
								//Синдром констрикции перетяжками
								{									
									layout:'column',
									items:[				
										{									
											layout:'form',
											labelWidth:400,
											style: 'margin-top: 3px;',
											items:[
												{
													fieldLabel: 'Синдром констрикции перетяжками',
													labelSeparator: '',
													name: 'ConstrictSyndrom',
													id: 'swENSEW_ConstrictSyndrom',
													xtype: 'checkbox',
													checked: false,
													listeners: {
														'check': function(chb, checked ) {
															if ((this.NeonatalSurvey_data.NeonatalSurveyParam.ConstrictSyndrom) || (checked)) 
																this.NeonatalSurvey_data.NeonatalSurveyParam.ConstrictSyndrom = checked ? 2 : 1;
															this.findById('swENSEW_ConstrictSyndromLoc').setDisabled(!checked);  //BOB - 06.05.2020
															if (!checked) {
																this.findById('swENSEW_ConstrictSyndromLoc').items.items[0].setValue(true);
																for (var i = 1; i < this.findById('swENSEW_ConstrictSyndromLoc').items.items.length; i++  ) this.findById('swENSEW_ConstrictSyndromLoc').items.items[i].setValue(false);						
															}		
															this.changedDatas = true;
														}.createDelegate(this)
													}		
												}
											]
										},						
										{									
											layout:'form',
											labelWidth:1,
											style: 'margin-top: 0px;  margin-left: 4px; margin-bottom: 0px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											items:[
												new Ext.form.RadioGroup({
													id:'swENSEW_ConstrictSyndromLoc',
													labelSeparator: '',
													vertical: true,
													columns: 4,
													FromInterface: true,
													items: [
														{boxLabel: '---', name: 'ConstrictSyndromLoc', inputValue: 0, width: 50, hidden: true}, 
														{boxLabel: 'справа', name: 'ConstrictSyndromLoc', inputValue: 1, width: 70}, 
														{boxLabel: 'слева', name: 'ConstrictSyndromLoc', inputValue: 2, width: 70},
														{boxLabel: 'с обеих сторон', name: 'ConstrictSyndromLoc', inputValue: 3, width: 110}
													],
													listeners: {
														'change': function(field, checked) {
															if((checked) && field.FromInterface){
																if ((this.NeonatalSurvey_data.NeonatalSurveyParam.ConstrictSyndromLoc) || (String(checked.inputValue) != '0')) {
																	this.NeonatalSurvey_data.NeonatalSurveyParam.ConstrictSyndromLoc = checked.inputValue;									
																	this.changedDatas = true;
																}
															}
															field.FromInterface = true;
														}.createDelegate(this)
													}
												})	
											]
										}
									]
								}
							]
						},

						//Травмы
						{
							id: 'swENSEW_Injuries_Panel',
							xtype: 'fieldset',
							autoHeight: true,
							title: langs('Травмы'),
							collapsible: true,
							layout: 'form',
							labelWidth: 250,
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
									layout:'column',
									items:[
										{xtype:'label',text: 'Переломы',style: 'font-size: 10pt; font-weight: bold; font-style:italic; margin-left: 12pt; margin-top: 2pt; margin-bottom: 2px; margin-right:12px; color:#15428b'},
										new Ext.Button({
											id: 'swENSEW_Fracture_Add_Button',
											iconCls: 'add16',
											text: '',
											handler: function(b,e)
											{
												this.NeonatalTrauma_add();
											}.createDelegate(this)
										})
									]
								},

								{									
									layout:'form',
									id: 'swENSEW_Fracture_Panel',

									items:[	
									]
								},
								//Травмы плечевого сплетения
								{									
									layout:'column',
									id: 'swENSEW_BrachPlexTrauma_Panel',
									style: 'margin-bottom: 0px;',
									items:[
										{xtype:'label',text: 'Травмы плечевого сплетения',style: 'font-size: 10pt;  margin-left: 12pt; margin-top: 2pt; margin-bottom: 2px'},
										{									
											layout:'form',
											labelWidth:1,
											style: 'margin-top: 4px;  margin-left: 4px; margin-bottom: 1px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											items:[
												new Ext.form.RadioGroup({
													id:'swENSEW_BrachPlexTrauma1',
													labelSeparator: '',
													vertical: true,
													FromInterface: true,
													columns: 3,
													items: [
														{boxLabel: 'нет', name: 'BrachPlexTrauma1', inputValue: 0, width: 50}, 
														{boxLabel: 'паралич Эрба', name: 'BrachPlexTrauma1', inputValue: 1, width: 130}, 
														{boxLabel: 'паралич Клюмпке', name: 'BrachPlexTrauma1', inputValue: 2, width: 130}
													],
													listeners: {
														'change': function(field, checked) {
															if(field.FromInterface) win.findById('swENSEW_BrachPlexTrauma_Panel').change_handler(field, checked);
															field.FromInterface = true;
														}
													}
												}),
											]
										},
										{									
											layout:'form',
											labelWidth:1,
											style: 'margin-top: 4px;  margin-left: 4px; margin-bottom: 1px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
											items:[
												new Ext.form.RadioGroup({
													id:'swENSEW_BrachPlexTrauma2',
													labelSeparator: '',
													vertical: true,
													FromInterface: true,
													columns: 4,
													items: [
														{boxLabel: '---', name: 'BrachPlexTrauma2', inputValue: 0, width: 50, hidden: true}, 
														{boxLabel: 'справа', name: 'BrachPlexTrauma2', inputValue: 1, width: 70}, 
														{boxLabel: 'слева', name: 'BrachPlexTrauma2', inputValue: 2, width: 70},
														{boxLabel: 'с обеих сторон', name: 'BrachPlexTrauma2', inputValue: 3, width: 110}
													],
													listeners: {
														'change': function(field, checked) {
															if(field.FromInterface) win.findById('swENSEW_BrachPlexTrauma_Panel').change_handler(field, checked);
															field.FromInterface = true;
														}
													}
												})	
											]
										}
									],
									change_handler: function(field, checked) {
										if(checked){
											var BrachPlexTrauma = (this.NeonatalSurvey_data.NeonatalSurveyParam.BrachPlexTrauma) ? this.NeonatalSurvey_data.NeonatalSurveyParam.BrachPlexTrauma : '00';
											var position = parseInt(field.id.substr(field.id.length - 1, 1));
											var value = checked.inputValue;
											//если меняется выбранный в первой группе то обнуляются вторая
											if (position == 1) {
												this.findById('swENSEW_BrachPlexTrauma2').items.items[0].setValue(true);		
												for (var i = 1; i < this.findById('swENSEW_BrachPlexTrauma2').items.items.length; i++  ) this.findById('swENSEW_BrachPlexTrauma2').items.items[i].setValue(false);						
												this.findById('swENSEW_BrachPlexTrauma2').disable();						
												if((value == 1) || (value == 2)) this.findById('swENSEW_BrachPlexTrauma2').enable();	
											}
											BrachPlexTrauma = BrachPlexTrauma.split("");
											BrachPlexTrauma[position - 1] = value;
											BrachPlexTrauma = BrachPlexTrauma.join("");
											if (!Ext.isEmpty(this.NeonatalSurvey_data.NeonatalSurveyParam.BrachPlexTrauma) || (BrachPlexTrauma != '00')) 
												this.NeonatalSurvey_data.NeonatalSurveyParam.BrachPlexTrauma = BrachPlexTrauma;									
											this.changedDatas = true;
										}
									}.createDelegate(this)
								},

							]
						},
						//Инфекционный процесс
						{
							id: 'swENSEW_InfectProcess_Panel',
							xtype: 'fieldset',
							autoHeight: true,
							title: langs('Инфекционный процесс'),
							collapsible: true,
							layout: 'column',
							labelWidth: 250,
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
								//Инфекционный процесс
								{
									layout:'form',
									border: false,
									labelWidth: 210,
									items:[
										{
											fieldLabel: 'Инфекционный процесс',
											labelSeparator: '',
											name: 'InfectProcess',
											id: 'swENSEW_InfectProcess',
											xtype: 'checkbox',
											checked: false,
											listeners: {
												'check': function(chb, checked ) {
													if ((this.NeonatalSurvey_data.NeonatalSurveyParam.InfectProcess) || (checked)) {
														this.NeonatalSurvey_data.NeonatalSurveyParam.InfectProcess = checked ? 2 : 1;									
														this.changedDatas = true;
													}
												}.createDelegate(this)
											}		
										}
									]
								},
								//Инфекционный процесс - отёк
								{
									layout:'form',
									border: false,
									labelWidth: 210,
									items:[
										{
											fieldLabel: 'Инфекционный процесс - отёк',
											labelSeparator: '',
											name: 'InfectProcessEdema',
											id: 'swENSEW_InfectProcessEdema',
											xtype: 'checkbox',
											checked: false,
											listeners: {
												'check': function(chb, checked ) {
													if ((this.NeonatalSurvey_data.NeonatalSurveyParam.InfectProcessEdema) || (checked)) { 
														this.NeonatalSurvey_data.NeonatalSurveyParam.InfectProcessEdema = checked ? 2 : 1;									
														this.changedDatas = true;
													}
												}.createDelegate(this)
											}		
										}
									]
								},
								//Боль при пассивных движениях
								{
									layout:'form',
									border: false,
									labelWidth: 210,
									items:[
										{
											fieldLabel: 'Боль при пассивных движениях',
											labelSeparator: '',
											name: 'PainPassivMove',
											id: 'swENSEW_PainPassivMove',
											xtype: 'checkbox',
											checked: false,
											listeners: {
												'check': function(chb, checked ) {
													if ((this.NeonatalSurvey_data.NeonatalSurveyParam.PainPassivMove) || (checked)) {
														this.NeonatalSurvey_data.NeonatalSurveyParam.PainPassivMove = checked ? 2 : 1;									
														this.changedDatas = true;
													}
												}.createDelegate(this)
											}		
										}
									]
								},
								//Ограничение в движении
								{
									layout:'form',
									border: false,
									labelWidth: 210,
									items:[
										{
											fieldLabel: 'Ограничение в движении',
											labelSeparator: '',
											name: 'RestrictMove',
											id: 'swENSEW_RestrictMove',
											xtype: 'checkbox',
											checked: false,
											listeners: {
												'check': function(chb, checked ) {
													if ((this.NeonatalSurvey_data.NeonatalSurveyParam.RestrictMove) || (checked)) {
														this.NeonatalSurvey_data.NeonatalSurveyParam.RestrictMove = checked ? 2 : 1;									
														this.changedDatas = true;
													}
												}.createDelegate(this)
											}		
										}
									]
								},
								//Аномальные позы конечностей
								{
									layout:'form',
									border: false,
									labelWidth: 210,
									items:[
										{
											fieldLabel: 'Аномальные позы конечностей',
											labelSeparator: '',
											name: 'AbnormPosturLimb',
											id: 'swENSEW_AbnormPosturLimb',
											xtype: 'checkbox',
											checked: false,
											listeners: {
												'check': function(chb, checked ) {
													if ((this.NeonatalSurvey_data.NeonatalSurveyParam.AbnormPosturLimb) || (checked)) {
														this.NeonatalSurvey_data.NeonatalSurveyParam.AbnormPosturLimb = checked ? 2 : 1;									
														this.changedDatas = true;
													}
												}.createDelegate(this)
											}		
										}
									]
								}
							]
						},
						//панель - Status localis
						{
							id: 'swENSEW_StatusLocalis_Panel',
							labelWidth: 5,
							xtype: 'fieldset',
							autoHeight: true,
							title: langs('Status localis'),
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
								new Ext.form.TextArea({
									fieldLabel: '',
									labelSeparator: '',
									id: 'swENSEW_StatusLocalis',
									name: 'StatusLocalis',
									height: 154,
									anchor: '99%',
									listeners:{
										'change':function (field, newValue, oldValue) {
											this.NeonatalSurvey_data.NeonatalSurveyParam.StatusLocalis = newValue;													
											this.changedDatas = true;
										}.createDelegate(this)
									}
								})												
							]
						},



						//панель - Заключение
						{
							id: 'swENSEW_Conclusion_Panel',
							labelWidth: 5,
							xtype: 'fieldset',
							autoHeight: true,
							title: langs('Заключение'),
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
								new Ext.form.TextArea({
									fieldLabel: '',
									labelSeparator: '',
									id: 'swENSEW_Conclusion',
									name: 'Conclusion',
									height: 154,
									anchor: '99%',
									listeners:{
										'change':function (field, newValue, oldValue) {
											if (newValue == '') newValue = ' ';
											this.NeonatalSurvey_data.EvnNeonatalSurvey.EvnNeonatalSurvey_Conclusion = newValue;													
											this.changedDatas = true;
										}.createDelegate(this)
									}
								})												
							]
						}
					]
				}
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
				{
					text: langs('Сохранить'),
					id: 'swENSEW_ButtonSave',
					tooltip: langs('Сохранить'),
					iconCls: 'save16',
					handler: function()
					{
						this.EvnNeonatalSurvey_Save();
					}.createDelegate(this)
				},
				//кнопка печати документа поступления/дневника на верхней половине листа
				{
					text:'Печать верх',
					id: 'swENSEW_ButtonPrintUp',
					tooltip: langs('Печать с верхнего края листа'),
					iconCls:'print16',
					handler:function () {
						this.EvnNeonatalSurvey_Print(0);
					}.createDelegate(this)
				},
				//кнопка печати документа поступления/дневника на нижней половине листа
				{
					text:'Печать низ',
					id: 'swENSEW_ButtonPrintDoun',
					tooltip: langs('Печать с середины листа'),
					iconCls:'print16',
					handler:function () {
						this.EvnNeonatalSurvey_Print(1);
					}.createDelegate(this)
				},
				{
					text: '-'
				},
				HelpButton(this, 1),
				{
					handler: function () {	
						//в объектах (радиогруппах), у которых есть свойство FromInterface, устанавливаю его в false
						Ext.select('div[id^="x-form-el-swENSEW_"]', true, 'swENSEW_Base_Data').each(function(el){
							var id = el.id.replace('x-form-el-',''); //выделяю параметр id из Ext.Element
							var object = win.findById(id);	//ищу в окне объект ExtJS
							if((object) && (object.FromInterface)){ // если нахожу, то 
								for (var i = 0; i < object.items.items.length; i++  ) object.items.items[i].setValue(false);						
							}
						});	




						this.changedDatas = false;					
						this.hide();
						//тлько для РЕАНИМАЦИОННОГО ПЕРИОДА
						if (this.fromObject.id == 'swEvnReanimatPeriodEditWindow') {
							this.fromObject.findById('swERPEW_EvnReanimatCondition_Grid').getStore().reload();	//перезагрузка грида наблюдений
							this.fromObject.EvnReanimatCondition_ButtonManag(this.fromObject,true);
						}
						if (win.ARMType == 'stas_pol') {
							win.fromObject.findById('ESEW_EvnNeonatalSurveyGrid').getAction('action_refresh').execute();
						}
					}.createDelegate(this),
					iconCls: 'cancel16',
					id: 'swENSEW_CancelButton',
					text: 'Закрыть'
				}]
		});
		sw.Promed.swEvnNeonatalSurveyEditWindow.superclass.initComponent.apply(this, arguments);
	},

	//запуск формы
	show: function() {
		var win = this;
		var arguments_ = arguments;

		var Args = arguments[0];
		console.log('BOB_swEvnNeonatalSurveyEditWindow_Args=',Args);

		this.action = Args.action;
		this.fromObject = Args.fromObject;	
		this.callback = Args.Callback;
		this.setTitle(Args.ENSEW_title); 
		this.EvnNeonatalSurvey_id = Args.EvnNeonatalSurvey_id;
		this.EvnNeonatalSurvey_pid = Args.EvnNeonatalSurvey_pid;
		this.EvnNeonatalSurvey_rid = Args.EvnNeonatalSurvey_rid;
		this.ParentObject = Args.ParentObject; 
		this.pers_data = Args.pers_data;
		this.userMedStaffFact = Args.userMedStaffFact;
		this.ARMType = Args.ARMType;
		this.changedDatas = false;
		this.FirstConditionLoad	= Args.FirstConditionLoad;  //BOB - 08.04.2020    !!!!!!!это нормально работает только при закрытии по кнопке закрыть, приотладке - мешает поэтому пока закомментил
		if(this.FirstConditionLoad) this.findById( 'swENSEW_EvnReanimatConditionPanelsManag').isCollaps = true;  //BOB - 08.04.2020
		this.LpuSection_id = Args.LpuSection_id ? Args.LpuSection_id : null;
		this.Lpu_id = Args.Lpu_id ? Args.Lpu_id : null;

		if (win.ARMType == 'stas_pol')
			this.findById( 'swENSEW_EvnNeonatalSurvey_setDate').minValue = this.pers_data.Person_Birthday;

		Ext.Ajax.request({
			showErrors: false,
			url: '/?c=EvnNeonatalSurvey&m=getParamsENSWindow',
			params: { 
				EvnNeonatalSurvey_pid: this.EvnNeonatalSurvey_pid,
				EvnNeonatalSurvey_id: this.EvnNeonatalSurvey_id,
				action: this.action,
				LpuSection_id: this.LpuSection_id,// только для специфики новорожденного
				Lpu_id: this.Lpu_id// только для специфики новорожденного
			},
			failure: function(response, options) {
				showSysMsg(langs('При получении данных формы Наблюдение состояния младенца произошла ошибка!'));
			},
			callback: function(opt, success, response) {
				if (success && response.responseText != '')
				{
					var Args2 = Ext.util.JSON.decode(response.responseText);
					console.log('BOB_Args2=',Args2); 
					win.par_data = Args2.par_data[0];	

					//загрузка комбо врачей, подписавших наблюдение
					var Datas =  [];
					for (var i in Args2.MS_doctors) { // цикл по значениям параметра
						Datas[i]= [ Args2.MS_doctors[i].MedPersonal_id,
									Args2.MS_doctors[i].EvnReanimatCondition_Doctor ];
					};
					win.findById('swENSEW_Print_Doctor_FIO').getStore().loadData(Datas);
					console.log('BOB_show_Datas_1=',Datas);

					win.SideType = Args2.SideType;  

					//BOB - 22.04.2020 подмена "Поступление" на "Первичный осмотр"
					Datas =  [];
					var items = win.findById('swENSEW_EvnNeonatalSurveyStage').store.data.items;
					for (var i in items) { // цикл по значениям параметра
						if (typeof items[i] == 'object'){
							Datas[i]= { 
								ReanimStageType_id: items[i].data.ReanimStageType_id ,
								ReanimStageType_Name: items[i].data.ReanimStageType_id == 1 ? 'Первичный осмотр' : items[i].data.ReanimStageType_Name
							};
						}
					};
					console.log('BOB_show_Datas_2=',Datas);
					win.findById('swENSEW_EvnNeonatalSurveyStage').getStore().loadData(Datas);


					win.restore();
					win.center();
					win.maximize();
					//загрузка объекта персональных данных
					var persFrame = win.findById('swENSEW_PersonInfo');
					persFrame.load({
						Person_id: win.pers_data.Person_id
					});
					
					//загрузка отдельных полей
					win.findById('swENSEW_EvnPS_NumCard').setText(win.par_data.EvnPS_NumCard);
					win.findById('swENSEW_LpuSection_Name').setText(win.par_data.LpuSection_Name);
					win.findById('swENSEW_EvnSection_setDate').setText(win.par_data.EvnSection_setDate);
					win.findById('swENSEW_swENSEW_BaseDiag').setText(win.par_data.Diag_Code + ' ' + win.par_data.Diag_Name);

					//рисование панелей дыхание аускультативно
					if (win.findById('swENSEW_Auscultatory_right') == null)
						win.AuscultatoryBuild();


					sw.Promed.swEvnNeonatalSurveyEditWindow.superclass.show.apply(win, arguments_);

					if(win.action == 'add') {
						if(Ext.isEmpty(win.EvnNeonatalSurvey_id)) 
							win.EvnNeonatalSurvey_add(Args2.Add_Params);//добавление сосвсем новой записи
						else
							win.EvnNeonatalSurvey_copy(Args2.Add_Params, win.EvnNeonatalSurvey_id);//копирование
					}
					else {
						win.EvnNeonatalSurvey_getData(win.EvnNeonatalSurvey_id);
					}

					var Condition_Panel = win.FormPanel.getForm().formPanel.findBy(function(item){ return item.id=='swENSEW_Conscious_Panel' });
					if (Condition_Panel) {
						if (win.ARMType == 'stas_pol') {
							Condition_Panel[0].collapse();
							Condition_Panel[0].setDisabled(true);
						} else {
							Condition_Panel[0].expand();
							Condition_Panel[0].setDisabled(false);
						}
					}
				}
			},
		});
	},



	//рисование панелей дахания аускультативноG
	AuscultatoryBuild() {
		var win = this;
		var SideType =  win.SideType;
		//console.log('BOB_thear_is_swENSEW_Auscultatory_right_1=',win.findById('swENSEW_Auscultatory_right_1')); 
		
		for(var i in SideType){
			if (SideType[i]['SideType_SysNick']){
				this.findById('swENSEW_Auskult_Panel').add(new Ext.form.FieldSet({
					id: 'swENSEW_Auscultatory_'+SideType[i]['SideType_SysNick'],
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


				//панель Характер
				this.findById('swENSEW_Auscultatory_'+SideType[i]['SideType_SysNick']).add(
					{
						//id: 'swENSEW_Auscultatory_'+SideType[i]['SideType_SysNick']+'_1_pan',
						layout:'column',
						border:false,						
						items:[	
							{xtype:'label',text: 'Характер',style: 'font-size: 10pt;  margin-left: 12pt; margin-top: 2pt; margin-bottom: 2px'},
							//радиобатоны Характер	
							{									
								layout:'form',
								labelWidth:1,
								style: 'margin-top: 4px;  margin-left: 4px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
								items:[
									new Ext.form.RadioGroup({
										id:'swENSEW_Auscultatory_'+SideType[i]['SideType_SysNick']+'_1',
										labelSeparator: '',
										vertical: true,
										columns: 5,
										FromInterface: true,
										SideType_SysNick: SideType[i]['SideType_SysNick'],
										items: [
											{boxLabel: '---', name: 'Auscultatory_'+SideType[i]['SideType_SysNick']+'_1', inputValue: 0, width: 50}, 
											{boxLabel: 'пуэрильное', name: 'Auscultatory_'+SideType[i]['SideType_SysNick']+'_1', inputValue: 1, width: 100}, 
											{boxLabel: 'везикулярное', name: 'Auscultatory_'+SideType[i]['SideType_SysNick']+'_1', inputValue: 2, width: 120}, 
											{boxLabel: 'жесткое', name: 'Auscultatory_'+SideType[i]['SideType_SysNick']+'_1', inputValue: 3, width: 80},
											{boxLabel: 'ослабленное', name: 'Auscultatory_'+SideType[i]['SideType_SysNick']+'_1', inputValue: 4, width: 100}
										],
										listeners: {
											'change': function(field, checked) {
												if((checked) && field.FromInterface){
													win.NeonatalSurvey_data.BreathAuscultative[field.SideType_SysNick]['BreathAuscultative_Auscult'] = checked.inputValue;
													win.changedDatas = true;
												}
												field.FromInterface = true;
											}
										}
									})	
								]
							},
							//Характер - вариант пользователя: текстовое поле 
							{
								allowBlank: true,
								fieldLabel: '',
								labelSeparator: '',
								id: 'swENSEW_AuscultTxt_'+SideType[i]['SideType_SysNick'],
								width: 758,
								style:'margin-top: 4px; margin-left: 4px',
								value:'',
								xtype: 'textfield',
								SideType_SysNick: SideType[i]['SideType_SysNick'],
								listeners:{  
									'change':function (field, newValue, oldValue) {
										win.NeonatalSurvey_data.BreathAuscultative[field.SideType_SysNick]['BreathAuscultative_AuscultTxt'] = newValue;
										win.changedDatas = true;
									}.createDelegate(this)
								}
							}
						]	
					}
				);
				//панель ХРИПЫ
				this.findById('swENSEW_Auscultatory_'+SideType[i]['SideType_SysNick']).add(
					{
						layout:'column',
						border:false,						
						items:[	
							{xtype:'label',text: 'Хрипы',style: 'font-size: 10pt;  margin-left: 12pt; margin-top: 2pt; margin-bottom: 2px'},
							//радиобатоны Хрипы								
							{									
								layout:'form',
								labelWidth:1,
								style: 'margin-top: 4px;  margin-left: 4px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
								items:[
									new Ext.form.RadioGroup({
										id:'swENSEW_Auscultatory_'+SideType[i]['SideType_SysNick']+'_2',
										labelSeparator: '',
										vertical: true,
										FromInterface: true,
										columns: 5,
										SideType_SysNick: SideType[i]['SideType_SysNick'],
										items: [
											{boxLabel: 'нет', name: 'Auscultatory_'+SideType[i]['SideType_SysNick']+'_2', inputValue: 0, width: 50}, 
											{boxLabel: 'проводные', name: 'Auscultatory_'+SideType[i]['SideType_SysNick']+'_2', inputValue: 1, width: 90}, 
											{boxLabel: 'крепитирующие', name: 'Auscultatory_'+SideType[i]['SideType_SysNick']+'_2', inputValue: 2, width: 120}, 
											{boxLabel: 'влажные', name: 'Auscultatory_'+SideType[i]['SideType_SysNick']+'_2', inputValue: 3, width: 80},
											{boxLabel: 'сухие', name: 'Auscultatory_'+SideType[i]['SideType_SysNick']+'_2', inputValue: 4, width: 70}
										],
										listeners: {
											'change': function(field, checked) {
												if((field.FromInterface) && (checked)){
													win.NeonatalSurvey_data.BreathAuscultative[field.SideType_SysNick]['BreathAuscultative_Rale'] = checked.inputValue;
													win.changedDatas = true;

													win.findById('swENSEW_Auscultatory_'+field.SideType_SysNick+'_3').items.items[0].setValue(true);		
													for (var i = 1; i < win.findById('swENSEW_Auscultatory_'+field.SideType_SysNick+'_3').items.items.length; i++  ) win.findById('swENSEW_Auscultatory_'+field.SideType_SysNick+'_3').items.items[i].setValue(false);						
													win.findById('swENSEW_Auscultatory_'+field.SideType_SysNick+'_3').disable();						
													if(checked.inputValue > 0) win.findById('swENSEW_Auscultatory_'+field.SideType_SysNick+'_3').enable();	
												}
												field.FromInterface = true;
												//console.log('BOB_win.NeonatalSurvey_data.BreathAuscultative=',win.NeonatalSurvey_data.BreathAuscultative[field.SideType_SysNick]); 
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
										id:'swENSEW_Auscultatory_'+SideType[i]['SideType_SysNick']+'_3',
										labelSeparator: '',
										vertical: true,
										FromInterface: true,
										columns: 3,
										SideType_SysNick: SideType[i]['SideType_SysNick'],
										items: [
											{boxLabel: '---', name: 'Auscultatory_'+SideType[i]['SideType_SysNick']+'_3', inputValue: 0, width: 50, hidden: true}, 
											{boxLabel: 'единичные', name: 'Auscultatory_'+SideType[i]['SideType_SysNick']+'_3', inputValue: 1, width: 90}, 
											{boxLabel: 'в большом количестве', name: 'Auscultatory_'+SideType[i]['SideType_SysNick']+'_3', inputValue: 2, width: 160} 
										],
										listeners: {
											'change': function(field, checked) {
												if((field.FromInterface) && (checked)) {
													win.NeonatalSurvey_data.BreathAuscultative[field.SideType_SysNick]['BreathAuscultative_IsPleuDrain'] = checked.inputValue;
													win.changedDatas = true;
												}
												field.FromInterface = true;
											}
										}
									})	
								]
							},
						]
					}
				);


			}
		};
	},


	//добавление записи 
	EvnNeonatalSurvey_add: function(Add_Params) {
		var win = this;

		//врач, подписавший наблюдение
		var index = -1;
		if (this.userMedStaffFact && this.userMedStaffFact.MedPersonal_id)
			index = win.findById('swENSEW_Print_Doctor_FIO').store.find('MedPersonal_id',this.userMedStaffFact.MedPersonal_id);

		var EvnNeonatalSurvey_Doctor = '';
		if (index > -1) {
			win.findById('swENSEW_Print_Doctor_FIO').setValue(this.userMedStaffFact.MedPersonal_id);
			EvnNeonatalSurvey_Doctor = this.userMedStaffFact.MedPersonal_id;
		} else win.findById('swENSEW_Print_Doctor_FIO').setValue('');

		//основной объект EvnNeonatalSurvey
		this.NeonatalSurvey_data['EvnNeonatalSurvey'] = {
			EvnNeonatalSurvey_id: null,
			EvnNeonatalSurvey_setDate: win.par_data.Previous_setDate,
			EvnNeonatalSurvey_setTime: win.par_data.Previous_setTime,
			EvnNeonatalSurvey_disDate: Ext.util.Format.date(getValidDT(getGlobalOptions().date, ''), 'd.m.Y'), //getValidDT(getGlobalOptions().date, ''),  
			EvnNeonatalSurvey_disTime: '',
			ReanimStageType_id: 2,
			ReanimConditionType_id: null,
			ReanimArriveFromType_id: null,
			EvnNeonatalSurvey_Conclusion: null,
			EvnNeonatalSurvey_Doctor: EvnNeonatalSurvey_Doctor
		};

		//установка типа Сознания по шкале Глазго
		var swENSEW_ConsciousValue = -1;
		if(!Ext.isEmpty(Add_Params.glasgow[0])) {
			this.findById('swENSEW_Conscious').fireEvent('expand', this.findById('swENSEW_Conscious'));
			var ConsciousStore = this.findById('swENSEW_Conscious').getStore().data.items;
			for (var i in ConsciousStore){
				if (ConsciousStore[i].data.ConsciousType_ByGlasgow){
					var aGlasgowCodes = ConsciousStore[i].data.ConsciousType_ByGlasgow.split(",");
					if (aGlasgowCodes.indexOf(Add_Params.glasgow[0].EvnScale_Result.toString()) != -1){
						swENSEW_ConsciousValue = ConsciousStore[i].data.ConsciousType_id;
						break;
					}
				}
			}
		}

		//ИВЛ //BOB - 09.04.2020
		var IVLApparat = '';
		var IVLParameter = '';
		if (Add_Params && Add_Params.IVL && Add_Params.IVL.length > 0){
			var IVL = Add_Params.IVL[0];
			IVLApparat = IVL['IVLParameter_Apparat'];
			IVLParameter = this.EvnNeonatalSurvey_getIVLParams(IVL);
		}

		//объект параметров NeonatalSurveyParam
		this.NeonatalSurvey_data['NeonatalSurveyParam'] = {
			EarsNorm: 2,
			StoolHas: 2,
			TesticlInScrotum: 2
		};
		if(!Ext.isEmpty(Add_Params.psofa[0])) this.NeonatalSurvey_data.NeonatalSurveyParam.psofa = Add_Params.psofa[0].EvnScale_Result;
		if(!Ext.isEmpty(Add_Params.pelod[0])) this.NeonatalSurvey_data.NeonatalSurveyParam.pelod = Add_Params.pelod[0].EvnScale_Result;
		if(!Ext.isEmpty(Add_Params.glasgow[0])) this.NeonatalSurvey_data.NeonatalSurveyParam.glasgow = Add_Params.glasgow[0].EvnScale_Result;
		if(!Ext.isEmpty(Add_Params.comfort[0])) this.NeonatalSurvey_data.NeonatalSurveyParam.comfort = Add_Params.comfort[0].EvnScale_Result;
		if(!Ext.isEmpty(Add_Params.npass[0])) this.NeonatalSurvey_data.NeonatalSurveyParam.npass = Add_Params.npass[0].EvnScale_Result;
		if(!Ext.isEmpty(Add_Params.nips[0])) this.NeonatalSurvey_data.NeonatalSurveyParam.nips = Add_Params.nips[0].EvnScale_Result;
		if(swENSEW_ConsciousValue != -1) this.NeonatalSurvey_data.NeonatalSurveyParam.ConsciousType_id = swENSEW_ConsciousValue;
		if(!Ext.isEmpty(IVLApparat)) this.NeonatalSurvey_data.NeonatalSurveyParam.Ventilator = IVLApparat;
		if(!Ext.isEmpty(IVLParameter)) this.NeonatalSurvey_data.NeonatalSurveyParam.VentilatorParam = IVLParameter;

		//объект Аускультативно BreathAuscultative
		this.NeonatalSurvey_data.BreathAuscultative = {};
		var SideType =  this.SideType;
		for(var i in SideType){
			if (SideType[i]['SideType_SysNick']) {
				this.NeonatalSurvey_data.BreathAuscultative[SideType[i]['SideType_SysNick']] = {
					BreathAuscultative_id: null,
					EvnReanimatCondition_id: null,
					SideType_id: SideType[i]['SideType_id'],
					SideType_SysNick: SideType[i]['SideType_SysNick'],
					BreathAuscultative_Auscult: 0,
					BreathAuscultative_AuscultTxt: '',
					BreathAuscultative_Rale: 0,
					BreathAuscultative_IsPleuDrain: 0,
					BA_RecordStatus: 0
				};
			}
		}
		//объект Травма (перелом) NeonatalTrauma
		this.NeonatalSurvey_data.NeonatalTrauma = {};
		//console.log('BOB_add_this.NeonatalSurvey_data.NeonatalTrauma_2=', this.NeonatalSurvey_data.NeonatalTrauma);




		this.EvnNeonatalSurvey_view()
	},

	//получение данных наблюдения из БД
	EvnNeonatalSurvey_getData: function(EvnNeonatalSurvey_id) {
		var win = this;

		Ext.Ajax.request({
			showErrors: false,
			url: '/?c=EvnNeonatalSurvey&m=getEvnNeonatalSurvey',
			params: { EvnNeonatalSurvey_id: EvnNeonatalSurvey_id },
			failure: function(response, options) {
				showSysMsg(langs('При получении данных Наблюдение состояния младенца произошла ошибка!'));
			},
			callback: function(opt, success, response) {
				if (success && response.responseText != '')
				{
					win.NeonatalSurvey_data = Ext.util.JSON.decode(response.responseText);
					if (win.NeonatalSurvey_data.NeonatalSurveyParam.length == 0) win.NeonatalSurvey_data.NeonatalSurveyParam = {EarsNorm: 2, StoolHas: 2, TesticlInScrotum: 2};
					//формирую объект аускультативного дыхания с названиями сторон, приходит просто нумерованное
					var BreathAuscultative = win.NeonatalSurvey_data.BreathAuscultative;
					win.NeonatalSurvey_data.BreathAuscultative = {};
					for(var i in BreathAuscultative){ 
						if (BreathAuscultative[i]['SideType_SysNick']) {
							win.NeonatalSurvey_data.BreathAuscultative[BreathAuscultative[i]['SideType_SysNick']] = BreathAuscultative[i];
						}
					}
					//формирую объект травм с id записей, приходит просто нумерованное
					var NeonatalTrauma = win.NeonatalSurvey_data.NeonatalTrauma;
					win.NeonatalSurvey_data.NeonatalTrauma = {};
					for(var i in NeonatalTrauma){ 
						if (typeof NeonatalTrauma[i] == 'object') win.NeonatalSurvey_data.NeonatalTrauma[NeonatalTrauma[i]['NeonatalTrauma_id']] = NeonatalTrauma[i];
					}

					console.log('BOB_win.NeonatalSurvey_data=',win.NeonatalSurvey_data); 
					win.EvnNeonatalSurvey_view();
				}
			}
		});
	},
	
	//копирование запипи наблюдения младенца
	EvnNeonatalSurvey_copy: function(Add_Params, EvnNeonatalSurvey_id){
		console.log('BOB_copy_Add_Params=',Add_Params);
		console.log('BOB_copy_EvnNeonatalSurvey_id=',EvnNeonatalSurvey_id);


		var win = this;

		Ext.Ajax.request({
			showErrors: false,
			url: '/?c=EvnNeonatalSurvey&m=getEvnNeonatalSurvey',
			params: { EvnNeonatalSurvey_id: EvnNeonatalSurvey_id },
			failure: function(response, options) {
				showSysMsg(langs('При получении данных Наблюдение состояния младенца произошла ошибка!'));
			},
			callback: function(opt, success, response) {
				if (success && response.responseText != '')
				{

					//из копируемой записи
					win.NeonatalSurvey_data = Ext.util.JSON.decode(response.responseText);
					//объект параметров NeonatalSurveyParam
					if (win.NeonatalSurvey_data.NeonatalSurveyParam.length == 0) win.NeonatalSurvey_data.NeonatalSurveyParam = {EarsNorm: 2, StoolHas: 2, TesticlInScrotum: 2};

					//формирую объект аускультативного дыхания с названиями сторон, приходит просто нумерованное
					var BreathAuscultative = win.NeonatalSurvey_data.BreathAuscultative;
					win.NeonatalSurvey_data.BreathAuscultative = {};
					for(var i in BreathAuscultative){ 
						if (BreathAuscultative[i]['SideType_SysNick']) {
							win.NeonatalSurvey_data.BreathAuscultative[BreathAuscultative[i]['SideType_SysNick']] = BreathAuscultative[i];
						}
					}
					//формирую объект травм с id записей, приходит просто нумерованное
					var NeonatalTrauma = win.NeonatalSurvey_data.NeonatalTrauma;
					win.NeonatalSurvey_data.NeonatalTrauma = {};
					for(var i in NeonatalTrauma){ 
						if (typeof NeonatalTrauma[i] == 'object') win.NeonatalSurvey_data.NeonatalTrauma[NeonatalTrauma[i]['NeonatalTrauma_id']] = NeonatalTrauma[i];
					}

					//новое
					//врач, подписавший наблюдение
					var index = win.findById('swENSEW_Print_Doctor_FIO').store.find('MedPersonal_id',win.userMedStaffFact.MedPersonal_id);
					var EvnNeonatalSurvey_Doctor = '';
					if (index > -1) {
						win.findById('swENSEW_Print_Doctor_FIO').setValue(win.userMedStaffFact.MedPersonal_id);
						EvnNeonatalSurvey_Doctor = win.userMedStaffFact.MedPersonal_id;
					} else win.findById('swENSEW_Print_Doctor_FIO').setValue('');

					//основной объект EvnNeonatalSurvey
					var EvnNeonatalSurvey = win.NeonatalSurvey_data['EvnNeonatalSurvey'];
					EvnNeonatalSurvey.EvnNeonatalSurvey_id = null;
					EvnNeonatalSurvey.EvnNeonatalSurvey_setDate = win.par_data.Previous_setDate;
					EvnNeonatalSurvey.EvnNeonatalSurvey_setTime = win.par_data.Previous_setTime;
					EvnNeonatalSurvey.EvnNeonatalSurvey_disDate = Ext.util.Format.date(getValidDT(getGlobalOptions().date, ''), 'd.m.Y');    //getValidDT(getGlobalOptions().date, '');
					EvnNeonatalSurvey.EvnNeonatalSurvey_disTime = '';
					EvnNeonatalSurvey.EvnNeonatalSurvey_Doctor = EvnNeonatalSurvey_Doctor;

					//установка типа Сознания по шкале Глазго
					var swENSEW_ConsciousValue = -1;
					if(!Ext.isEmpty(Add_Params.glasgow[0])) {
						win.findById('swENSEW_Conscious').fireEvent('expand', win.findById('swENSEW_Conscious'));
						var ConsciousStore = win.findById('swENSEW_Conscious').getStore().data.items;
						for (var i in ConsciousStore){
							if (ConsciousStore[i].data.ConsciousType_ByGlasgow){
								var aGlasgowCodes = ConsciousStore[i].data.ConsciousType_ByGlasgow.split(",");
								if (aGlasgowCodes.indexOf(Add_Params.glasgow[0].EvnScale_Result.toString()) != -1){
									swENSEW_ConsciousValue = ConsciousStore[i].data.ConsciousType_id;
									break;
								}
							}
						}
					}

					//ИВЛ //BOB - 09.04.2020
					var IVLApparat = '';
					var IVLParameter = '';
					if (Add_Params.IVL.length > 0){
						var IVL = Add_Params.IVL[0];
						IVLApparat = IVL['IVLParameter_Apparat'];
						IVLParameter = win.EvnNeonatalSurvey_getIVLParams(IVL);
					}

					//объект параметров NeonatalSurveyParam - продолжение
					var NeonatalSurveyParam = win.NeonatalSurvey_data['NeonatalSurveyParam']; 
					if(!Ext.isEmpty(Add_Params.psofa[0])) NeonatalSurveyParam.psofa = Add_Params.psofa[0].EvnScale_Result;
					else delete NeonatalSurveyParam.psofa;
					if(!Ext.isEmpty(Add_Params.pelod[0])) NeonatalSurveyParam.pelod = Add_Params.pelod[0].EvnScale_Result;
					else delete NeonatalSurveyParam.pelod;
					if(!Ext.isEmpty(Add_Params.glasgow[0])) NeonatalSurveyParam.glasgow = Add_Params.glasgow[0].EvnScale_Result;
					else delete NeonatalSurveyParam.glasgow;
					if(!Ext.isEmpty(Add_Params.comfort[0])) NeonatalSurveyParam.comfort = Add_Params.comfort[0].EvnScale_Result;
					else delete NeonatalSurveyParam.comfort;
					if(!Ext.isEmpty(Add_Params.npass[0])) NeonatalSurveyParam.npass = Add_Params.npass[0].EvnScale_Result;
					else delete NeonatalSurveyParam.npass;
					if(!Ext.isEmpty(Add_Params.nips[0])) NeonatalSurveyParam.nips = Add_Params.nips[0].EvnScale_Result;
					else delete NeonatalSurveyParam.nips;
					if(swENSEW_ConsciousValue != -1) NeonatalSurveyParam.ConsciousType_id = swENSEW_ConsciousValue;
					else delete NeonatalSurveyParam.ConsciousType_id;
					if(!Ext.isEmpty(IVLApparat)) NeonatalSurveyParam.Ventilator = IVLApparat;
					else delete NeonatalSurveyParam.Ventilator;
					if(!Ext.isEmpty(IVLParameter)) NeonatalSurveyParam.VentilatorParam = IVLParameter;
					else delete NeonatalSurveyParam.VentilatorParam;
		
					console.log('BOB_copy_win.NeonatalSurvey_data=',win.NeonatalSurvey_data); 
					win.EvnNeonatalSurvey_view();
				}
			}
		});
	},



	//загрузка данных наблюдения в поля
	EvnNeonatalSurvey_view: function() {
		var win = this;
		var EvnNeonatalSurvey = this.NeonatalSurvey_data.EvnNeonatalSurvey;
		var NeonatalSurveyParam = this.NeonatalSurvey_data.NeonatalSurveyParam;
		var BreathAuscultative = this.NeonatalSurvey_data.BreathAuscultative;
		var NeonatalTrauma = this.NeonatalSurvey_data.NeonatalTrauma;
		var RadioString = '';

		//в объектах (радиогруппах), у которых есть свойство FromInterface, устанавливаю его в false
		Ext.select('div[id^="x-form-el-swENSEW_"]', true, 'swENSEW_Base_Data').each(function(el){
			var id = el.id.replace('x-form-el-',''); //выделяю параметр id из Ext.Element
			var object = win.findById(id);	//ищу в окне объект ExtJS
			if((object) && (object.FromInterface)){ // если нахожу, то 
				object.FromInterface = win.ARMType == 'stas_pol' ? true : false;
			}
		});	

		//ШАПКА
		this.findById('swENSEW_EvnNeonatalSurveyStage').setValue(EvnNeonatalSurvey['ReanimStageType_id']); //этап - документ обытие регулярного наблюдения состояния 
		this.findById('swENSEW_EvnNeonatalSurvey_setDate').setValue(EvnNeonatalSurvey['EvnNeonatalSurvey_setDate']); //установка даты события регулярного наблюдения состояния
		this.findById('swENSEW_EvnNeonatalSurvey_setTime').setValue(EvnNeonatalSurvey['EvnNeonatalSurvey_setTime']); //установка времени события регулярного наблюдения состояния
		this.findById('swENSEW_EvnNeonatalSurvey_disDate').setValue(EvnNeonatalSurvey['EvnNeonatalSurvey_disDate']); //установка даты окончания события регулярного наблюдения состояния
		this.findById('swENSEW_EvnNeonatalSurvey_disTime').setValue(EvnNeonatalSurvey['EvnNeonatalSurvey_disTime']); //установка времени окончания события регулярного наблюдения состояния		
		this.findById('swENSEW_ArriveFrom').setValue(EvnNeonatalSurvey['ReanimArriveFromType_id']);	//поступил из 
		//Параметры печати
		this.findById('swENSEW_Print_Doctor_FIO').setValue(EvnNeonatalSurvey['EvnNeonatalSurvey_Doctor']);//  врач, подписавший наблюдение
		if(this.FirstConditionLoad) this.findById('swENSEW_PrintParams_Panel').collapse();// скрываю при первой загрузке наблюдений, !!!!убрать если по умолчанию надо оставить открытыми
		//Антропометрические данные
		if (EvnNeonatalSurvey['ReanimStageType_id'] == 2) {
			this.EvnNS_AntropometrLoud(EvnNeonatalSurvey['EvnNeonatalSurvey_disDate'],EvnNeonatalSurvey['EvnNeonatalSurvey_disTime']);
		}
		else {
			this.EvnNS_AntropometrLoud(EvnNeonatalSurvey['EvnNeonatalSurvey_setDate'],EvnNeonatalSurvey['EvnNeonatalSurvey_setTime']);				
		}
		if(this.FirstConditionLoad) this.findById('swENSEW_Antropometr_Panel').expand();// отрываю при первой загрузке наблюдений, !!!!убрать если по умолчанию надо оставить открытыми
		//Жизненно-важные показатели
		this.findById('swENSEW_TemperPeripher').setValue(!Ext.isEmpty(NeonatalSurveyParam.TemperPeripher) ? NeonatalSurveyParam.TemperPeripher : '');   //Температура периферическая
		this.findById('swENSEW_TemperCentr').setValue(!Ext.isEmpty(NeonatalSurveyParam.TemperCentr) ? NeonatalSurveyParam.TemperCentr : '');   //Температура центральная
		this.findById('swENSEW_BreathFrequency').setValue(!Ext.isEmpty(NeonatalSurveyParam.BreathFrequency) ? NeonatalSurveyParam.BreathFrequency : '');   //Частота дыхания
		this.findById('swENSEW_HeartFrequency').setValue(!Ext.isEmpty(NeonatalSurveyParam.HeartFrequency) ? NeonatalSurveyParam.HeartFrequency : '');   //Частота сердечных сокращений
		//Артериальное давление
		var Pressure = ((NeonatalSurveyParam.Pressure) ? NeonatalSurveyParam.Pressure : '').split("/");
		if ((Pressure[0]) && (!isNaN(Number.parseInt(Pressure[0])) ))		
			this.findById('swENSEW_Heart_Pressure_syst').setValue(Number.parseInt(Pressure[0]));
		else
			this.findById('swENSEW_Heart_Pressure_syst').setValue(0);
		if ((Pressure[1]) && (!isNaN(Number.parseInt(Pressure[1])) ))		
			this.findById('swENSEW_Heart_Pressure_diast').setValue(Number.parseInt(Pressure[1]));
		else
			this.findById('swENSEW_Heart_Pressure_diast').setValue(0);
		if ((Pressure[2]) && (!isNaN(Number.parseInt(Pressure[2])) ))		
			this.findById('swENSEW_Heart_Pressure_sredn').setValue(Number.parseInt(Pressure[2]));
		else
			this.findById('swENSEW_Heart_Pressure_sredn').setValue(0);
		if(this.FirstConditionLoad) this.findById('swENSEW_VitalParams_Panel').expand();// отрываю при первой загрузке наблюдений, !!!!убрать если по умолчанию надо оставить открытыми
			//this.findById('swENSEW_Heart_Pressure').calculation(false);	
		//СОСТОЯНИЕ
		this.findById('swENSEW_Condition').setValue(EvnNeonatalSurvey['ReanimConditionType_id']);	//состояние
		this.findById('swENSEW_psofa').setValue(!Ext.isEmpty(NeonatalSurveyParam.psofa) ? NeonatalSurveyParam.psofa : '');// значение по pSOFA			
		this.findById('swENSEW_pelod').setValue(!Ext.isEmpty(NeonatalSurveyParam.pelod) ? NeonatalSurveyParam.pelod : '');// значение по PELOD-2			
		if(this.FirstConditionLoad) this.findById('swENSEW_Condition_Panel').expand();// отрываю при первой загрузке наблюдений, !!!!убрать если по умолчанию надо оставить открытыми
		//СОЗНАНИЕ
		this.findById('swENSEW_Conscious').setValue(!Ext.isEmpty(NeonatalSurveyParam.ConsciousType_id) ? NeonatalSurveyParam.ConsciousType_id : null);	//Уровень сознания   
		this.findById('swENSEW_glasgow').setValue(!Ext.isEmpty(NeonatalSurveyParam.glasgow) ? NeonatalSurveyParam.glasgow : '');// значение по glasgow			
		this.findById('swENSEW_SedationMedicatTxt').setValue(!Ext.isEmpty(NeonatalSurveyParam.SedationMedicatTxt) ? NeonatalSurveyParam.SedationMedicatTxt : '');// Медикамент седации		
		this.findById('swENSEW_comfort').setValue(!Ext.isEmpty(NeonatalSurveyParam.comfort) ? NeonatalSurveyParam.comfort : '');// значение по comfort			
		this.findById('swENSEW_npass').setValue(!Ext.isEmpty(NeonatalSurveyParam.npass) ? NeonatalSurveyParam.npass : '');// значение по npass			
		this.findById('swENSEW_nips').setValue(!Ext.isEmpty(NeonatalSurveyParam.nips) ? NeonatalSurveyParam.nips : '');// значение по nips
		if (NeonatalSurveyParam.sarnat) {
			RadioString = NeonatalSurveyParam.sarnat;            															//Значение ГИЭ по SARNAT                                                       
			for (var i = 0; i < this.findById('swENSEW_sarnat').items.items.length; i++  )
				this.findById('swENSEW_sarnat').items.items[i].setValue(parseInt(RadioString[0]) == i ? true : false);		
		}
		else {
			this.findById('swENSEW_sarnat').items.items[0].setValue(true);
			for (var i = 1; i < this.findById('swENSEW_sarnat').items.items.length; i++  ) this.findById('swENSEW_sarnat').items.items[i].setValue(false);						
		}
		if(this.FirstConditionLoad) this.findById('swENSEW_Conscious_Panel').collapse();// скрываю при первой загрузке наблюдений, !!!!убрать если по умолчанию надо оставить открытыми
		//ПОЗА
		if (NeonatalSurveyParam.Рose) {
			RadioString = NeonatalSurveyParam.Рose;
			for (var i = 0; i < this.findById('swENSEW_Рose').items.items.length; i++  )
				this.findById('swENSEW_Рose').items.items[i].setValue(parseInt(RadioString[0]) == i ? true : false);		
		}
		else { 
			this.findById('swENSEW_Рose').items.items[0].setValue(true);
			for (var i = 1; i < this.findById('swENSEW_Рose').items.items.length; i++  ) this.findById('swENSEW_Рose').items.items[i].setValue(false);						
		}
		this.findById('swENSEW_РoseTxt').setValue(!Ext.isEmpty(NeonatalSurveyParam.РoseTxt) ? NeonatalSurveyParam.РoseTxt : '');// 	
		if(this.FirstConditionLoad) this.findById('swENSEW_Рose_Panel').collapse();// скрываю при первой загрузке наблюдений, !!!!убрать если по умолчанию надо оставить открытыми
		//РЕАКЦИЯ НА ОСМОТР
		if (NeonatalSurveyParam.CheckReact) {
			RadioString = NeonatalSurveyParam.CheckReact;
			for (var i = 0; i < this.findById('swENSEW_CheckReact').items.items.length; i++  )
				this.findById('swENSEW_CheckReact').items.items[i].setValue(parseInt(RadioString[0]) == i ? true : false);		
		}
		else {
			this.findById('swENSEW_CheckReact').items.items[0].setValue(true);		
			for (var i = 1; i < this.findById('swENSEW_CheckReact').items.items.length; i++  ) this.findById('swENSEW_CheckReact').items.items[i].setValue(false);						
		}
		this.findById('swENSEW_CheckReactTxt').setValue(!Ext.isEmpty(NeonatalSurveyParam.CheckReactTxt) ? NeonatalSurveyParam.CheckReactTxt : '');// 	
		if(this.FirstConditionLoad) this.findById('swENSEW_CheckReact_Panel').collapse();// скрываю при первой загрузке наблюдений, !!!!убрать если по умолчанию надо оставить открытыми
		//КРИК
		value = 0;  //BOB - 06.05.2020
		if (NeonatalSurveyParam.Scream) {
			RadioString = NeonatalSurveyParam.Scream;
			for (var i = 0; i < this.findById('swENSEW_Scream1').items.items.length; i++  )
				this.findById('swENSEW_Scream1').items.items[i].setValue(parseInt(RadioString[0]) == i ? true : false);
			value = parseInt(RadioString[0]);  //BOB - 06.05.2020
			for (var i = 0; i < this.findById('swENSEW_Scream2').items.items.length; i++  )
				this.findById('swENSEW_Scream2').items.items[i].setValue(parseInt(RadioString[1]) == i ? true : false);		
		}
		else {
			this.findById('swENSEW_Scream1').items.items[0].setValue(true);		
			for (var i = 1; i < this.findById('swENSEW_Scream1').items.items.length; i++  ) this.findById('swENSEW_Scream1').items.items[i].setValue(false);						
			this.findById('swENSEW_Scream2').items.items[0].setValue(true);		
			for (var i = 1; i < this.findById('swENSEW_Scream2').items.items.length; i++  ) this.findById('swENSEW_Scream2').items.items[i].setValue(false);						
		}
		this.findById('swENSEW_Scream2').disable();  //BOB - 06.05.2020
		if (value == 1) this.findById('swENSEW_Scream2').enable();  //BOB - 06.05.2020
		this.findById('swENSEW_ScreamTxt').setValue(!Ext.isEmpty(NeonatalSurveyParam.ScreamTxt) ? NeonatalSurveyParam.ScreamTxt : '');//
		if(this.FirstConditionLoad) this.findById('swENSEW_Scream_Panel').collapse();// скрываю при первой загрузке наблюдений, !!!!убрать если по умолчанию надо оставить открытыми
		//ГОЛОВА
		this.findById('swENSEW_macrocephaly').setValue((NeonatalSurveyParam.macrocephaly)&&(NeonatalSurveyParam.macrocephaly == 2) ? true : false);			//Голова - макроцефалия
		this.findById('swENSEW_microcephaly').setValue((NeonatalSurveyParam.microcephaly)&&(NeonatalSurveyParam.microcephaly == 2) ? true : false);			//Голова - микроцефалия
		this.findById('swENSEW_molding').setValue((NeonatalSurveyParam.molding)&&(NeonatalSurveyParam.molding == 2) ? true : false);						//Голова - молдинг
		this.findById('swENSEW_BirthTumor').setValue((NeonatalSurveyParam.BirthTumor)&&(NeonatalSurveyParam.BirthTumor == 2) ? true : false);				//Голова - родовая опухоль
		this.findById('swENSEW_BirthTumorTxt').setValue(!Ext.isEmpty(NeonatalSurveyParam.BirthTumorTxt) ? NeonatalSurveyParam.BirthTumorTxt : '');// 
		this.findById('swENSEW_BirthTumorTxt').setDisabled(!this.findById('swENSEW_BirthTumor').getValue());  //BOB - 06.05.2020
		this.findById('swENSEW_СephaloHematoma').setValue((NeonatalSurveyParam.СephaloHematoma)&&(NeonatalSurveyParam.СephaloHematoma == 2) ? true : false);	//Голова - кефалогематома
		this.findById('swENSEW_СephaloHematomaTxt').setValue(!Ext.isEmpty(NeonatalSurveyParam.СephaloHematomaTxt) ? NeonatalSurveyParam.СephaloHematomaTxt : '');// 
		this.findById('swENSEW_СephaloHematomaTxt').setDisabled(!this.findById('swENSEW_СephaloHematoma').getValue());  //BOB - 06.05.2020
		this.findById('swENSEW_SubaponeuroHematoma').setValue((NeonatalSurveyParam.SubaponeuroHematoma)&&(NeonatalSurveyParam.SubaponeuroHematoma == 2) ? true : false);	//Голова - подапоневротическая гематома
		this.findById('swENSEW_SubaponeuroHematomaTxt').setValue(!Ext.isEmpty(NeonatalSurveyParam.SubaponeuroHematomaTxt) ? NeonatalSurveyParam.SubaponeuroHematomaTxt : '');// 
		this.findById('swENSEW_SubaponeuroHematomaTxt').setDisabled(!this.findById('swENSEW_SubaponeuroHematoma').getValue());  //BOB - 06.05.2020
		this.findById('swENSEW_ProtrudingVeins').setValue((NeonatalSurveyParam.ProtrudingVeins)&&(NeonatalSurveyParam.ProtrudingVeins == 2) ? true : false);				//Голова - выступающие вены на черепе
		this.findById('swENSEW_craniosynostosis').setValue((NeonatalSurveyParam.craniosynostosis)&&(NeonatalSurveyParam.craniosynostosis == 2) ? true : false);		//Голова - краниосиностоз
		this.findById('swENSEW_craniotabes').setValue((NeonatalSurveyParam.craniotabes)&&(NeonatalSurveyParam.craniotabes == 2) ? true : false);					//Голова - краниотабес
		this.findById('swENSEW_plagiocephaly').setValue((NeonatalSurveyParam.plagiocephaly)&&(NeonatalSurveyParam.plagiocephaly == 2) ? true : false);				//Голова - плагиоцефалия
		this.findById('swENSEW_brachycephaly').setValue((NeonatalSurveyParam.brachycephaly)&&(NeonatalSurveyParam.brachycephaly == 2) ? true : false);				//Голова - брахицефалия
		this.findById('swENSEW_dolichocephaly').setValue((NeonatalSurveyParam.dolichocephaly)&&(NeonatalSurveyParam.dolichocephaly == 2) ? true : false);			//Голова - долихоцефалия
		this.findById('swENSEW_acrocephaly').setValue((NeonatalSurveyParam.acrocephaly)&&(NeonatalSurveyParam.acrocephaly == 2) ? true : false);					//Голова - акроцефалия
		if(this.FirstConditionLoad) this.findById('swENSEW_head_Panel').collapse();// скрываю при первой загрузке наблюдений, !!!!убрать если по умолчанию надо оставить открытыми
		//БОЛЬШОЙ РОДНИЧОК
		this.findById('swENSEW_FontanelleSize1').setValue(!Ext.isEmpty(NeonatalSurveyParam.FontanelleSize1) ? NeonatalSurveyParam.FontanelleSize1 : '');   //Большой родничок - размер 1
		this.findById('swENSEW_FontanelleSize2').setValue(!Ext.isEmpty(NeonatalSurveyParam.FontanelleSize2) ? NeonatalSurveyParam.FontanelleSize2 : '');   //Большой родничок - размер 2
		if (NeonatalSurveyParam.FontanelleProperties) {
			RadioString = NeonatalSurveyParam.FontanelleProperties;																							//Большой родничок - свойства
			for (var i = 0; i < this.findById('swENSEW_FontanelleProperties1').items.items.length; i++  )
				this.findById('swENSEW_FontanelleProperties1').items.items[i].setValue(parseInt(RadioString[0]) == i ? true : false);		
			for (var i = 0; i < this.findById('swENSEW_FontanelleProperties2').items.items.length; i++  )
				this.findById('swENSEW_FontanelleProperties2').items.items[i].setValue(parseInt(RadioString[1]) == i ? true : false);		
			for (var i = 0; i < this.findById('swENSEW_FontanelleProperties3').items.items.length; i++  )
				this.findById('swENSEW_FontanelleProperties3').items.items[i].setValue(parseInt(RadioString[2]) == i ? true : false);		
		}
		else {
			this.findById('swENSEW_FontanelleProperties1').items.items[0].setValue(true);		
			for (var i = 1; i < this.findById('swENSEW_FontanelleProperties1').items.items.length; i++  ) this.findById('swENSEW_FontanelleProperties1').items.items[i].setValue(false);						
			this.findById('swENSEW_FontanelleProperties2').items.items[0].setValue(true);		
			for (var i = 1; i < this.findById('swENSEW_FontanelleProperties2').items.items.length; i++  ) this.findById('swENSEW_FontanelleProperties2').items.items[i].setValue(false);						
			this.findById('swENSEW_FontanelleProperties3').items.items[0].setValue(true);		
			for (var i = 1; i < this.findById('swENSEW_FontanelleProperties3').items.items.length; i++  ) this.findById('swENSEW_FontanelleProperties3').items.items[i].setValue(false);						
		}
		if(this.FirstConditionLoad) this.findById('swENSEW_Fontanelle_Panel').collapse();// скрываю при первой загрузке наблюдений, !!!!убрать если по умолчанию надо оставить открытыми
		//ШВЫ ЧЕРЕПА
		if (NeonatalSurveyParam.SkullSutures) {
			RadioString = NeonatalSurveyParam.SkullSutures;
			for (var i = 0; i < this.findById('swENSEW_SkullSutures').items.items.length; i++  )
				this.findById('swENSEW_SkullSutures').items.items[i].setValue(parseInt(RadioString[0]) == i ? true : false);		
		}
		else { 
			this.findById('swENSEW_SkullSutures').items.items[0].setValue(true);
			for (var i = 1; i < this.findById('swENSEW_SkullSutures').items.items.length; i++  ) this.findById('swENSEW_SkullSutures').items.items[i].setValue(false);						
		}
		this.findById('swENSEW_SkullSuturesSize').setValue(!Ext.isEmpty(NeonatalSurveyParam.SkullSuturesSize) ? NeonatalSurveyParam.SkullSuturesSize : '');// 	
		if(this.FirstConditionLoad) this.findById('swENSEW_SkullSutures_Panel').collapse();// скрываю при первой загрузке наблюдений, !!!!убрать если по умолчанию надо оставить открытыми
		//ЗРАЧКИ
		if (NeonatalSurveyParam.Pupils) {
			RadioString = NeonatalSurveyParam.Pupils;																							//Зрачки
			for (var i = 0; i < this.findById('swENSEW_Pupils1').items.items.length; i++  )
				this.findById('swENSEW_Pupils1').items.items[i].setValue(parseInt(RadioString[0]) == i ? true : false);		
			for (var i = 0; i < this.findById('swENSEW_Pupils2').items.items.length; i++  )
				this.findById('swENSEW_Pupils2').items.items[i].setValue(parseInt(RadioString[1]) == i ? true : false);		
			for (var i = 0; i < this.findById('swENSEW_Pupils3').items.items.length; i++  )
				this.findById('swENSEW_Pupils3').items.items[i].setValue(parseInt(RadioString[2]) == i ? true : false);		
		}
		else {
			this.findById('swENSEW_Pupils1').items.items[0].setValue(true);		
			for (var i = 1; i < this.findById('swENSEW_Pupils1').items.items.length; i++  ) this.findById('swENSEW_Pupils1').items.items[i].setValue(false);						
			this.findById('swENSEW_Pupils2').items.items[0].setValue(true);		
			for (var i = 1; i < this.findById('swENSEW_Pupils2').items.items.length; i++  ) this.findById('swENSEW_Pupils2').items.items[i].setValue(false);						
			this.findById('swENSEW_Pupils3').items.items[0].setValue(true);		
			for (var i = 1; i < this.findById('swENSEW_Pupils3').items.items.length; i++  ) this.findById('swENSEW_Pupils3').items.items[i].setValue(false);						
		}
		this.findById('swENSEW_PupilsTxt').setValue(!Ext.isEmpty(NeonatalSurveyParam.PupilsTxt) ? NeonatalSurveyParam.PupilsTxt : '');// Зрачки - вариант пользователя
		value = 0;
		if (NeonatalSurveyParam.Nystagmus) {
			RadioString = NeonatalSurveyParam.Nystagmus;					//Нистагм
			for (var i = 0; i < this.findById('swENSEW_Nystagmus1').items.items.length; i++  )
				this.findById('swENSEW_Nystagmus1').items.items[i].setValue(parseInt(RadioString[0]) == i ? true : false);	
			value = parseInt(RadioString[0]);		
			for (var i = 0; i < this.findById('swENSEW_Nystagmus2').items.items.length; i++  )
				this.findById('swENSEW_Nystagmus2').items.items[i].setValue(parseInt(RadioString[1]) == i ? true : false);		
		}
		else {
			this.findById('swENSEW_Nystagmus1').items.items[0].setValue(true);		
			for (var i = 1; i < this.findById('swENSEW_Nystagmus1').items.items.length; i++  ) this.findById('swENSEW_Nystagmus1').items.items[i].setValue(false);						
			this.findById('swENSEW_Nystagmus2').items.items[0].setValue(true);		
			for (var i = 1; i < this.findById('swENSEW_Nystagmus2').items.items.length; i++  ) this.findById('swENSEW_Nystagmus2').items.items[i].setValue(false);						
		}
		this.findById('swENSEW_Nystagmus2').disable();
		if (value > 0) this.findById('swENSEW_Nystagmus2').enable();
		if(this.FirstConditionLoad) this.findById('swENSEW_Pupils_Panel').collapse();// скрываю при первой загрузке наблюдений, !!!!убрать если по умолчанию надо оставить открытыми
		//МЫШЕЧНЫЙ ТОНУС
		if (NeonatalSurveyParam.MuscleTone) {
			RadioString = NeonatalSurveyParam.MuscleTone;
			for (var i = 0; i < this.findById('swENSEW_MuscleTone').items.items.length; i++  )
				this.findById('swENSEW_MuscleTone').items.items[i].setValue(parseInt(RadioString[0]) == i ? true : false);		
		}
		else { 
			this.findById('swENSEW_MuscleTone').items.items[0].setValue(true);
			for (var i = 1; i < this.findById('swENSEW_MuscleTone').items.items.length; i++  ) this.findById('swENSEW_MuscleTone').items.items[i].setValue(false);						
		}
		this.findById('swENSEW_MuscleToneTxt').setValue(!Ext.isEmpty(NeonatalSurveyParam.MuscleToneTxt) ? NeonatalSurveyParam.MuscleToneTxt : '');// 	
		if(this.FirstConditionLoad) this.findById('swENSEW_MuscleTone_Panel').collapse();// скрываю при первой загрузке наблюдений, !!!!убрать если по умолчанию надо оставить открытыми
		//РЕФЛЕКСЫ
		if (NeonatalSurveyParam.Reflexes) {
			RadioString = NeonatalSurveyParam.Reflexes;																							//Рефлексы
			for (var i = 0; i < this.findById('swENSEW_Reflexes1').items.items.length; i++  )
				this.findById('swENSEW_Reflexes1').items.items[i].setValue(parseInt(RadioString[0]) == i ? true : false);		
			for (var i = 0; i < this.findById('swENSEW_Reflexes2').items.items.length; i++  )
				this.findById('swENSEW_Reflexes2').items.items[i].setValue(parseInt(RadioString[1]) == i ? true : false);		
			for (var i = 0; i < this.findById('swENSEW_Reflexes3').items.items.length; i++  )
				this.findById('swENSEW_Reflexes3').items.items[i].setValue(parseInt(RadioString[2]) == i ? true : false);		
			for (var i = 0; i < this.findById('swENSEW_Reflexes4').items.items.length; i++  )
				this.findById('swENSEW_Reflexes4').items.items[i].setValue(parseInt(RadioString[3]) == i ? true : false);		
			for (var i = 0; i < this.findById('swENSEW_Reflexes5').items.items.length; i++  )
				this.findById('swENSEW_Reflexes5').items.items[i].setValue(parseInt(RadioString[4]) == i ? true : false);		
			for (var i = 0; i < this.findById('swENSEW_Reflexes6').items.items.length; i++  )
				this.findById('swENSEW_Reflexes6').items.items[i].setValue(parseInt(RadioString[5]) == i ? true : false);		
			for (var i = 0; i < this.findById('swENSEW_Reflexes7').items.items.length; i++  )
				this.findById('swENSEW_Reflexes7').items.items[i].setValue(parseInt(RadioString[6]) == i ? true : false);		
			for (var i = 0; i < this.findById('swENSEW_Reflexes8').items.items.length; i++  )
				this.findById('swENSEW_Reflexes8').items.items[i].setValue(parseInt(RadioString[7]) == i ? true : false);		
		}
		else {
			this.findById('swENSEW_Reflexes1').items.items[0].setValue(true);		
			for (var i = 1; i < this.findById('swENSEW_Reflexes1').items.items.length; i++  ) this.findById('swENSEW_Reflexes1').items.items[i].setValue(false);						
			this.findById('swENSEW_Reflexes2').items.items[0].setValue(true);		
			for (var i = 1; i < this.findById('swENSEW_Reflexes2').items.items.length; i++  ) this.findById('swENSEW_Reflexes2').items.items[i].setValue(false);						
			this.findById('swENSEW_Reflexes3').items.items[0].setValue(true);		
			for (var i = 1; i < this.findById('swENSEW_Reflexes3').items.items.length; i++  ) this.findById('swENSEW_Reflexes3').items.items[i].setValue(false);						
			this.findById('swENSEW_Reflexes4').items.items[0].setValue(true);		
			for (var i = 1; i < this.findById('swENSEW_Reflexes4').items.items.length; i++  ) this.findById('swENSEW_Reflexes4').items.items[i].setValue(false);						
			this.findById('swENSEW_Reflexes5').items.items[0].setValue(true);		
			for (var i = 1; i < this.findById('swENSEW_Reflexes5').items.items.length; i++  ) this.findById('swENSEW_Reflexes5').items.items[i].setValue(false);						
			this.findById('swENSEW_Reflexes6').items.items[0].setValue(true);		
			for (var i = 1; i < this.findById('swENSEW_Reflexes6').items.items.length; i++  ) this.findById('swENSEW_Reflexes6').items.items[i].setValue(false);						
			this.findById('swENSEW_Reflexes7').items.items[0].setValue(true);		
			for (var i = 1; i < this.findById('swENSEW_Reflexes7').items.items.length; i++  ) this.findById('swENSEW_Reflexes7').items.items[i].setValue(false);						
			this.findById('swENSEW_Reflexes8').items.items[0].setValue(true);		
			for (var i = 1; i < this.findById('swENSEW_Reflexes8').items.items.length; i++  ) this.findById('swENSEW_Reflexes8').items.items[i].setValue(false);						
		}
		if(this.FirstConditionLoad) this.findById('swENSEW_Reflexes_Panel').collapse();// скрываю при первой загрузке наблюдений, !!!!убрать если по умолчанию надо оставить открытыми
		//ОБЩИЕ СИМПТОМЫ НЕВРОЛОГИЧЕСКИХ НАРУШЕНИЙ
		this.findById('swENSEW_FrontFontanelleBulg').setValue((NeonatalSurveyParam.FrontFontanelleBulg)&&(NeonatalSurveyParam.FrontFontanelleBulg == 2) ? true : false);			//Выбухание переднего родничка
		this.findById('swENSEW_CranialVeinsExpans').setValue((NeonatalSurveyParam.CranialVeinsExpans)&&(NeonatalSurveyParam.CranialVeinsExpans == 2) ? true : false);			//Расширение черепных вен
		this.findById('swENSEW_SettingSunSymptom').setValue((NeonatalSurveyParam.SettingSunSymptom)&&(NeonatalSurveyParam.SettingSunSymptom == 2) ? true : false);			//Симптом ~заходящего солнца~
		this.findById('swENSEW_RisingSunSymptom').setValue((NeonatalSurveyParam.RisingSunSymptom)&&(NeonatalSurveyParam.RisingSunSymptom == 2) ? true : false);			//Симптом ~восходящего солнца~
		this.findById('swENSEW_Apnoea').setValue((NeonatalSurveyParam.Apnoea)&&(NeonatalSurveyParam.Apnoea == 2) ? true : false);			//Апноэ
		if (NeonatalSurveyParam.Cramp) {
			RadioString = NeonatalSurveyParam.Cramp;					//Судороги
			for (var i = 0; i < this.findById('swENSEW_Cramp1').items.items.length; i++  )
				this.findById('swENSEW_Cramp1').items.items[i].setValue(parseInt(RadioString[0]) == i ? true : false);		
			for (var i = 0; i < this.findById('swENSEW_Cramp2').items.items.length; i++  )
				this.findById('swENSEW_Cramp2').items.items[i].setValue(parseInt(RadioString[1]) == i ? true : false);		
		}
		else {
			this.findById('swENSEW_Cramp1').items.items[0].setValue(true);		
			for (var i = 1; i < this.findById('swENSEW_Cramp1').items.items.length; i++  ) this.findById('swENSEW_Cramp1').items.items[i].setValue(false);						
			this.findById('swENSEW_Cramp2').items.items[0].setValue(true);		
			for (var i = 1; i < this.findById('swENSEW_Cramp2').items.items.length; i++  ) this.findById('swENSEW_Cramp2').items.items[i].setValue(false);						
		}
		if(this.FirstConditionLoad) this.findById('swENSEW_Cramp_Panel').collapse();// скрываю при первой загрузке наблюдений, !!!!убрать если по умолчанию надо оставить открытыми
		//КОЖА
		value = 0;  //BOB - 06.05.2020
		if (NeonatalSurveyParam.SkinColor) {
			RadioString = NeonatalSurveyParam.SkinColor;																							//Кожа - цвет
			for (var i = 0; i < this.findById('swENSEW_SkinColor1').items.items.length; i++  )
				this.findById('swENSEW_SkinColor1').items.items[i].setValue(parseInt(RadioString[0]) == i ? true : false);
			value = parseInt(RadioString[0]);  //BOB - 06.05.2020
			for (var i = 0; i < this.findById('swENSEW_SkinColor2').items.items.length; i++  )
				this.findById('swENSEW_SkinColor2').items.items[i].setValue(parseInt(RadioString[1]) == i ? true : false);		
			for (var i = 0; i < this.findById('swENSEW_SkinColor3').items.items.length; i++  )
				this.findById('swENSEW_SkinColor3').items.items[i].setValue(parseInt(RadioString[2]) == i ? true : false);		
			for (var i = 0; i < this.findById('swENSEW_SkinColor4').items.items.length; i++  )
				this.findById('swENSEW_SkinColor4').items.items[i].setValue(parseInt(RadioString[3]) == i ? true : false);		
		}
		else {
			this.findById('swENSEW_SkinColor1').items.items[0].setValue(true);		
			for (var i = 1; i < this.findById('swENSEW_SkinColor1').items.items.length; i++  ) this.findById('swENSEW_SkinColor1').items.items[i].setValue(false);						
			this.findById('swENSEW_SkinColor2').items.items[0].setValue(true);		
			for (var i = 1; i < this.findById('swENSEW_SkinColor2').items.items.length; i++  ) this.findById('swENSEW_SkinColor2').items.items[i].setValue(false);						
			this.findById('swENSEW_SkinColor3').items.items[0].setValue(true);		
			for (var i = 1; i < this.findById('swENSEW_SkinColor3').items.items.length; i++  ) this.findById('swENSEW_SkinColor3').items.items[i].setValue(false);						
			this.findById('swENSEW_SkinColor4').items.items[0].setValue(true);		
			for (var i = 1; i < this.findById('swENSEW_SkinColor4').items.items.length; i++  ) this.findById('swENSEW_SkinColor4').items.items[i].setValue(false);						
		}
		this.findById('swENSEW_SkinColor2').disable();     //BOB - 06.05.2020
		this.findById('swENSEW_SkinColor3').disable();
		if(value == 2)	this.findById('swENSEW_SkinColor2').enable();
		else if (value == 4)	this.findById('swENSEW_SkinColor3').enable();    //BOB - 06.05.2020
		this.findById('swENSEW_SkinColorTxt').setValue(!Ext.isEmpty(NeonatalSurveyParam.SkinColorTxt) ? NeonatalSurveyParam.SkinColorTxt : '');// Кожа - цвет – вариант пользователя
		this.findById('swENSEW_Marbling').setValue((NeonatalSurveyParam.Marbling)&&(NeonatalSurveyParam.Marbling == 2) ? true : false);			//Мраморность
		this.findById('swENSEW_HarlequinSyndrome').setValue((NeonatalSurveyParam.HarlequinSyndrome)&&(NeonatalSurveyParam.HarlequinSyndrome == 2) ? true : false);			//Синдром Арлекина
		this.findById('swENSEW_Ichthyosis').setValue((NeonatalSurveyParam.Ichthyosis)&&(NeonatalSurveyParam.Ichthyosis == 2) ? true : false);			//Ихтиоз
		this.findById('swENSEW_Dryness').setValue((NeonatalSurveyParam.Dryness)&&(NeonatalSurveyParam.Dryness == 2) ? true : false);			//Сухость (шелушения)
		this.findById('swENSEW_SkinAplasia').setValue((NeonatalSurveyParam.SkinAplasia)&&(NeonatalSurveyParam.SkinAplasia == 2) ? true : false);			//Аплазия кожи
		this.findById('swENSEW_ThinFragile').setValue((NeonatalSurveyParam.ThinFragile)&&(NeonatalSurveyParam.ThinFragile == 2) ? true : false);			//Тонкая хрупкая кожа
		this.findById('swENSEW_WhiteSkinHair').setValue((NeonatalSurveyParam.WhiteSkinHair)&&(NeonatalSurveyParam.WhiteSkinHair == 2) ? true : false);			//Белая кожа и волосы
		this.findById('swENSEW_EctodermDysplasia').setValue((NeonatalSurveyParam.EctodermDysplasia)&&(NeonatalSurveyParam.EctodermDysplasia == 2) ? true : false);			//Дисплазия эктодермы
		this.findById('swENSEW_Neurofibromatosis').setValue((NeonatalSurveyParam.Neurofibromatosis)&&(NeonatalSurveyParam.Neurofibromatosis == 2) ? true : false);			//Коричневые пятна (нейрофиброматоз)
		this.findById('swENSEW_Macule').setValue((NeonatalSurveyParam.Macule)&&(NeonatalSurveyParam.Macule == 2) ? true : false);			//Макула
		this.findById('swENSEW_MaculeTxt').setValue(!Ext.isEmpty(NeonatalSurveyParam.MaculeTxt) ? NeonatalSurveyParam.MaculeTxt : '');// Макула - локализация
		this.findById('swENSEW_MaculeTxt').setDisabled(!this.findById('swENSEW_Macule').getValue());  //BOB - 06.05.2020
		this.findById('swENSEW_Papules').setValue((NeonatalSurveyParam.Papules)&&(NeonatalSurveyParam.Papules == 2) ? true : false);			//Папулы
		this.findById('swENSEW_PapulesTxt').setValue(!Ext.isEmpty(NeonatalSurveyParam.PapulesTxt) ? NeonatalSurveyParam.PapulesTxt : '');// Папулы - локализация
		this.findById('swENSEW_PapulesTxt').setDisabled(!this.findById('swENSEW_Papules').getValue());  //BOB - 06.05.2020
		this.findById('swENSEW_Bubbles').setValue((NeonatalSurveyParam.Bubbles)&&(NeonatalSurveyParam.Bubbles == 2) ? true : false);			//Пузырьки
		this.findById('swENSEW_BubblesTxt').setValue(!Ext.isEmpty(NeonatalSurveyParam.BubblesTxt) ? NeonatalSurveyParam.BubblesTxt : '');// Пузырьки - локализация
		this.findById('swENSEW_BubblesTxt').setDisabled(!this.findById('swENSEW_Bubbles').getValue());  //BOB - 06.05.2020
		this.findById('swENSEW_BigBubble').setValue((NeonatalSurveyParam.BigBubble)&&(NeonatalSurveyParam.BigBubble == 2) ? true : false);			//Большие пузыри
		this.findById('swENSEW_BigBubbleTxt').setValue(!Ext.isEmpty(NeonatalSurveyParam.BigBubbleTxt) ? NeonatalSurveyParam.BigBubbleTxt : '');// Большие пузыри - локализация
		this.findById('swENSEW_BigBubbleTxt').setDisabled(!this.findById('swENSEW_BigBubble').getValue());  //BOB - 06.05.2020
		this.findById('swENSEW_Pustules').setValue((NeonatalSurveyParam.Pustules)&&(NeonatalSurveyParam.Pustules == 2) ? true : false);			//Пустулы
		this.findById('swENSEW_PustulesTxt').setValue(!Ext.isEmpty(NeonatalSurveyParam.PustulesTxt) ? NeonatalSurveyParam.PustulesTxt : '');// Пустулы - локализация
		this.findById('swENSEW_PustulesTxt').setDisabled(!this.findById('swENSEW_Pustules').getValue());  //BOB - 06.05.2020
		this.findById('swENSEW_ToxicErythema').setValue((NeonatalSurveyParam.ToxicErythema)&&(NeonatalSurveyParam.ToxicErythema == 2) ? true : false);			//Токсическая эритема
		this.findById('swENSEW_ToxicErythemaTxt').setValue(!Ext.isEmpty(NeonatalSurveyParam.ToxicErythemaTxt) ? NeonatalSurveyParam.ToxicErythemaTxt : '');// Токсическая эритема - локализация
		this.findById('swENSEW_ToxicErythemaTxt').setDisabled(!this.findById('swENSEW_ToxicErythema').getValue());  //BOB - 06.05.2020
		this.findById('swENSEW_CandidaRash').setValue((NeonatalSurveyParam.CandidaRash)&&(NeonatalSurveyParam.CandidaRash == 2) ? true : false);			//Кандидозная сыпь
		this.findById('swENSEW_CandidaRashTxt').setValue(!Ext.isEmpty(NeonatalSurveyParam.CandidaRashTxt) ? NeonatalSurveyParam.CandidaRashTxt : '');// Кандидозная сыпь - локализация
		this.findById('swENSEW_CandidaRashTxt').setDisabled(!this.findById('swENSEW_CandidaRash').getValue());  //BOB - 06.05.2020
		this.findById('swENSEW_HerpesSimplex').setValue((NeonatalSurveyParam.HerpesSimplex)&&(NeonatalSurveyParam.HerpesSimplex == 2) ? true : false);			//Простой герпес
		this.findById('swENSEW_HerpesSimplexTxt').setValue(!Ext.isEmpty(NeonatalSurveyParam.HerpesSimplexTxt) ? NeonatalSurveyParam.HerpesSimplexTxt : '');// Простой герпес - локализация
		this.findById('swENSEW_HerpesSimplexTxt').setDisabled(!this.findById('swENSEW_HerpesSimplex').getValue());  //BOB - 06.05.2020
		this.findById('swENSEW_Hemangioma').setValue((NeonatalSurveyParam.Hemangioma)&&(NeonatalSurveyParam.Hemangioma == 2) ? true : false);			//Гемангиома
		this.findById('swENSEW_HemangiomaTxt').setValue(!Ext.isEmpty(NeonatalSurveyParam.HemangiomaTxt) ? NeonatalSurveyParam.HemangiomaTxt : '');// Гемангиома - локализация
		this.findById('swENSEW_HemangiomaTxt').setDisabled(!this.findById('swENSEW_Hemangioma').getValue());  //BOB - 06.05.2020
		this.findById('swENSEW_HemorRashEcchymos').setValue((NeonatalSurveyParam.HemorRashEcchymos)&&(NeonatalSurveyParam.HemorRashEcchymos == 2) ? true : false);			//Геморрагическая сыпь – экхимозы
		this.findById('swENSEW_HemorRashEcchymosTxt').setValue(!Ext.isEmpty(NeonatalSurveyParam.HemorRashEcchymosTxt) ? NeonatalSurveyParam.HemorRashEcchymosTxt : '');// Геморрагическая сыпь – экхимозы - локализация
		this.findById('swENSEW_HemorRashEcchymosTxt').setDisabled(!this.findById('swENSEW_HemorRashEcchymos').getValue());  //BOB - 06.05.2020
		this.findById('swENSEW_HemorRashPetechiae').setValue((NeonatalSurveyParam.HemorRashPetechiae)&&(NeonatalSurveyParam.HemorRashPetechiae == 2) ? true : false);			//Геморрагическая сыпь – петехии
		this.findById('swENSEW_HemorRashPetechiaeTxt').setValue(!Ext.isEmpty(NeonatalSurveyParam.HemorRashPetechiaeTxt) ? NeonatalSurveyParam.HemorRashPetechiaeTxt : '');// Геморрагическая сыпь – петехии - локализация
		this.findById('swENSEW_HemorRashPetechiaeTxt').setDisabled(!this.findById('swENSEW_HemorRashPetechiae').getValue());  //BOB - 06.05.2020
		if (NeonatalSurveyParam.LimbsTouch) {
			RadioString = NeonatalSurveyParam.LimbsTouch;																												//Конечности на ощупь
			for (var i = 0; i < this.findById('swENSEW_LimbsTouch').items.items.length; i++  )
				this.findById('swENSEW_LimbsTouch').items.items[i].setValue(parseInt(RadioString[0]) == i ? true : false);		
		}
		else { 
			this.findById('swENSEW_LimbsTouch').items.items[0].setValue(true);
			for (var i = 1; i < this.findById('swENSEW_LimbsTouch').items.items.length; i++  ) this.findById('swENSEW_LimbsTouch').items.items[i].setValue(false);						
		}
		if (NeonatalSurveyParam.Turgor) {
			RadioString = NeonatalSurveyParam.Turgor;																												//Тургор мягких тканей
			for (var i = 0; i < this.findById('swENSEW_Turgor').items.items.length; i++  )
				this.findById('swENSEW_Turgor').items.items[i].setValue(parseInt(RadioString[0]) == i ? true : false);		
		}
		else { 
			this.findById('swENSEW_Turgor').items.items[0].setValue(true);
			for (var i = 1; i < this.findById('swENSEW_Turgor').items.items.length; i++  ) this.findById('swENSEW_Turgor').items.items[i].setValue(false);						
		}
		if (NeonatalSurveyParam.SubcutaneousFat) {
			RadioString = NeonatalSurveyParam.SubcutaneousFat;																												//Подкожно-жировая клетчатка развита
			for (var i = 0; i < this.findById('swENSEW_SubcutaneousFat').items.items.length; i++  )
				this.findById('swENSEW_SubcutaneousFat').items.items[i].setValue(parseInt(RadioString[0]) == i ? true : false);		
		}
		else { 
			this.findById('swENSEW_SubcutaneousFat').items.items[0].setValue(true);
			for (var i = 1; i < this.findById('swENSEW_SubcutaneousFat').items.items.length; i++  ) this.findById('swENSEW_SubcutaneousFat').items.items[i].setValue(false);						
		}
		this.findById('swENSEW_SubcutaneousFatTxt').setValue(!Ext.isEmpty(NeonatalSurveyParam.SubcutaneousFatTxt) ? NeonatalSurveyParam.SubcutaneousFatTxt : '');// Подкожно-жировая клетчатка развита – вариант пользовыателя
		value = 0;  //BOB - 06.05.2020
		if (NeonatalSurveyParam.Oedemata) {
			RadioString = NeonatalSurveyParam.Oedemata;					//Отёки
			for (var i = 0; i < this.findById('swENSEW_Oedemata1').items.items.length; i++  )
				this.findById('swENSEW_Oedemata1').items.items[i].setValue(parseInt(RadioString[0]) == i ? true : false);
			value = parseInt(RadioString[0]);  //BOB - 06.05.2020
			for (var i = 0; i < this.findById('swENSEW_Oedemata2').items.items.length; i++  )
				this.findById('swENSEW_Oedemata2').items.items[i].setValue(parseInt(RadioString[1]) == i ? true : false);		
		}
		else {
			this.findById('swENSEW_Oedemata1').items.items[0].setValue(true);		
			for (var i = 1; i < this.findById('swENSEW_Oedemata1').items.items.length; i++  ) this.findById('swENSEW_Oedemata1').items.items[i].setValue(false);						
			this.findById('swENSEW_Oedemata2').items.items[0].setValue(true);		
			for (var i = 1; i < this.findById('swENSEW_Oedemata2').items.items.length; i++  ) this.findById('swENSEW_Oedemata2').items.items[i].setValue(false);						
		}
		this.findById('swENSEW_Oedemata2').disable();     //BOB - 06.05.2020
		if(value == 1)	this.findById('swENSEW_Oedemata2').enable();
		this.findById('swENSEW_OedemataTxt').setValue(!Ext.isEmpty(NeonatalSurveyParam.OedemataTxt) ? NeonatalSurveyParam.OedemataTxt : '');// Отёки – вариант пользовыателя
		if(this.FirstConditionLoad) this.findById('swENSEW_Skin_Panel').collapse();// скрываю при первой загрузке наблюдений, !!!!убрать если по умолчанию надо оставить открытыми
		//ШЕЯ
		value = 0;  //BOB - 06.05.2020
		if (NeonatalSurveyParam.Neck) {
			RadioString = NeonatalSurveyParam.Neck;
			for (var i = 0; i < this.findById('swENSEW_Neck1').items.items.length; i++  )
				this.findById('swENSEW_Neck1').items.items[i].setValue(parseInt(RadioString[0]) == i ? true : false);
			value = parseInt(RadioString[0]);  //BOB - 06.05.2020
			for (var i = 0; i < this.findById('swENSEW_Neck2').items.items.length; i++  )
				this.findById('swENSEW_Neck2').items.items[i].setValue(parseInt(RadioString[1]) == i ? true : false);		
		}
		else {
			this.findById('swENSEW_Neck1').items.items[0].setValue(true);		
			for (var i = 1; i < this.findById('swENSEW_Neck1').items.items.length; i++  ) this.findById('swENSEW_Neck1').items.items[i].setValue(false);						
			this.findById('swENSEW_Neck2').items.items[0].setValue(true);		
			for (var i = 1; i < this.findById('swENSEW_Neck2').items.items.length; i++  ) this.findById('swENSEW_Neck2').items.items[i].setValue(false);						
		}
		this.findById('swENSEW_Neck2').disable();     //BOB - 06.05.2020
		if(value == 4)	this.findById('swENSEW_Neck2').enable();
		this.findById('swENSEW_NeckTxt').setValue(!Ext.isEmpty(NeonatalSurveyParam.NeckTxt) ? NeonatalSurveyParam.NeckTxt : '');//
		if(this.FirstConditionLoad) this.findById('swENSEW_Neck_Panel').collapse();// скрываю при первой загрузке наблюдений, !!!!убрать если по умолчанию надо оставить открытыми
		//ЛИЦО
		this.findById('swENSEW_ForeheadShape').setValue(!Ext.isEmpty(NeonatalSurveyParam.ForeheadShape) ? NeonatalSurveyParam.ForeheadShape : '');//Форма лба
		this.findById('swENSEW_NoseShape').setValue(!Ext.isEmpty(NeonatalSurveyParam.NoseShape) ? NeonatalSurveyParam.NoseShape : '');//Форма носа
		this.findById('swENSEW_MouthShape').setValue(!Ext.isEmpty(NeonatalSurveyParam.MouthShape) ? NeonatalSurveyParam.MouthShape : '');//Форма рта
		if (NeonatalSurveyParam.СhinShape) {
			RadioString = NeonatalSurveyParam.СhinShape;																			//Форма подбородка
			for (var i = 0; i < this.findById('swENSEW_СhinShape').items.items.length; i++  )
				this.findById('swENSEW_СhinShape').items.items[i].setValue(parseInt(RadioString[0]) == i ? true : false);		
		}
		else { 
			this.findById('swENSEW_СhinShape').items.items[0].setValue(true);
			for (var i = 1; i < this.findById('swENSEW_СhinShape').items.items.length; i++  ) this.findById('swENSEW_СhinShape').items.items[i].setValue(false);						
		}
		this.findById('swENSEW_СhinShapeTxt').setValue(!Ext.isEmpty(NeonatalSurveyParam.СhinShapeTxt) ? NeonatalSurveyParam.СhinShapeTxt : '');//Форма подбородка – вариант пользователя
		this.findById('swENSEW_FacialAsymmetry').setValue((NeonatalSurveyParam.FacialAsymmetry)&&(NeonatalSurveyParam.FacialAsymmetry == 2) ? true : false);			//Асимметрия лица
		//Глаза на лице
		var value = 0;
		if (NeonatalSurveyParam.EyesOnFace) {
			RadioString = NeonatalSurveyParam.EyesOnFace;																							//Глаза на лице
			for (var i = 0; i < this.findById('swENSEW_EyesOnFace1').items.items.length; i++  )
				this.findById('swENSEW_EyesOnFace1').items.items[i].setValue(parseInt(RadioString[0]) == i ? true : false);	
			value = parseInt(RadioString[0]);		
			for (var i = 0; i < this.findById('swENSEW_EyesOnFace2').items.items.length; i++  )
				this.findById('swENSEW_EyesOnFace2').items.items[i].setValue(parseInt(RadioString[1]) == i ? true : false);		
			for (var i = 0; i < this.findById('swENSEW_EyesOnFace3').items.items.length; i++  )
				this.findById('swENSEW_EyesOnFace3').items.items[i].setValue(parseInt(RadioString[2]) == i ? true : false);		
		}
		else {
			this.findById('swENSEW_EyesOnFace1').items.items[0].setValue(true);		
			for (var i = 1; i < this.findById('swENSEW_EyesOnFace1').items.items.length; i++  ) this.findById('swENSEW_EyesOnFace1').items.items[i].setValue(false);						
			this.findById('swENSEW_EyesOnFace2').items.items[0].setValue(true);		
			for (var i = 1; i < this.findById('swENSEW_EyesOnFace2').items.items.length; i++  ) this.findById('swENSEW_EyesOnFace2').items.items[i].setValue(false);						
			this.findById('swENSEW_EyesOnFace3').items.items[0].setValue(true);		
			for (var i = 1; i < this.findById('swENSEW_EyesOnFace3').items.items.length; i++  ) this.findById('swENSEW_EyesOnFace3').items.items[i].setValue(false);						
		}
		this.findById('swENSEW_EyesOnFace2').disable();						
		this.findById('swENSEW_EyesOnFace3').disable();	
		if(value == 3)	this.findById('swENSEW_EyesOnFace2').enable();	
		else if (value == 4)	this.findById('swENSEW_EyesOnFace3').enable();			

		this.findById('swENSEW_Hypertelorism').setValue((NeonatalSurveyParam.Hypertelorism)&&(NeonatalSurveyParam.Hypertelorism == 2) ? true : false);			//Гипертелоризм
		if (NeonatalSurveyParam.MouthLipsMovement) {
			RadioString = NeonatalSurveyParam.MouthLipsMovement;																			//Движения рта и губ
			for (var i = 0; i < this.findById('swENSEW_MouthLipsMovement').items.items.length; i++  )
				this.findById('swENSEW_MouthLipsMovement').items.items[i].setValue(parseInt(RadioString[0]) == i ? true : false);		
		}
		else { 
			this.findById('swENSEW_MouthLipsMovement').items.items[0].setValue(true);
			for (var i = 1; i < this.findById('swENSEW_MouthLipsMovement').items.items.length; i++  ) this.findById('swENSEW_MouthLipsMovement').items.items[i].setValue(false);						
		}
		this.findById('swENSEW_LowMouthСorner').setValue((NeonatalSurveyParam.LowMouthСorner)&&(NeonatalSurveyParam.LowMouthСorner == 2) ? true : false);			//Опущение угла рта
		if (NeonatalSurveyParam.LowMouthСornerLoc) {
			RadioString = NeonatalSurveyParam.LowMouthСornerLoc;																			//Опущение угла рта - локализация
			for (var i = 0; i < this.findById('swENSEW_LowMouthСornerLoc').items.items.length; i++  )
				this.findById('swENSEW_LowMouthСornerLoc').items.items[i].setValue(parseInt(RadioString[0]) == i ? true : false);		
		}
		else { 
			this.findById('swENSEW_LowMouthСornerLoc').items.items[0].setValue(true);
			for (var i = 1; i < this.findById('swENSEW_LowMouthСornerLoc').items.items.length; i++  ) this.findById('swENSEW_LowMouthСornerLoc').items.items[i].setValue(false);						
		}
		this.findById('swENSEW_LowMouthСornerLoc').setDisabled(!this.findById('swENSEW_LowMouthСorner').getValue());  //BOB - 06.05.2020
		this.findById('swENSEW_LackNasoLabFold').setValue((NeonatalSurveyParam.LackNasoLabFold)&&(NeonatalSurveyParam.LackNasoLabFold == 2) ? true : false);			//Отсутствие носогубной складки
		if (NeonatalSurveyParam.LackNasoLabFoldLoc) {
			RadioString = NeonatalSurveyParam.LackNasoLabFoldLoc;																			//Отсутствие носогубной складки - локализация
			for (var i = 0; i < this.findById('swENSEW_LackNasoLabFoldLoc').items.items.length; i++  )
				this.findById('swENSEW_LackNasoLabFoldLoc').items.items[i].setValue(parseInt(RadioString[0]) == i ? true : false);		
		}
		else { 
			this.findById('swENSEW_LackNasoLabFoldLoc').items.items[0].setValue(true);
			for (var i = 1; i < this.findById('swENSEW_LackNasoLabFoldLoc').items.items.length; i++  ) this.findById('swENSEW_LackNasoLabFoldLoc').items.items[i].setValue(false);						
		}
		this.findById('swENSEW_LackNasoLabFoldLoc').setDisabled(!this.findById('swENSEW_LackNasoLabFold').getValue());  //BOB - 06.05.2020
		this.findById('swENSEW_HyperSalivation').setValue((NeonatalSurveyParam.HyperSalivation)&&(NeonatalSurveyParam.HyperSalivation == 2) ? true : false);			//Слюнотечение
		if(this.FirstConditionLoad) this.findById('swENSEW_Face_Panel').collapse();// скрываю при первой загрузке наблюдений, !!!!убрать если по умолчанию надо оставить открытыми
		//УШИ
		this.findById('swENSEW_EarsNorm').setValue((NeonatalSurveyParam.EarsNorm)&&(NeonatalSurveyParam.EarsNorm == 2) ? true : false);			//Уши без особенностей
		this.findById('swENSEW_EarsLowSet').setValue((NeonatalSurveyParam.EarsLowSet)&&(NeonatalSurveyParam.EarsLowSet == 2) ? true : false);			//Уши низко посаженные
		this.findById('swENSEW_EarsMicrotia').setValue((NeonatalSurveyParam.EarsMicrotia)&&(NeonatalSurveyParam.EarsMicrotia == 2) ? true : false);			//Микротия
		this.findById('swENSEW_EarsHairy').setValue((NeonatalSurveyParam.EarsHairy)&&(NeonatalSurveyParam.EarsHairy == 2) ? true : false);			//Уши волосатые
		this.findById('swENSEW_Preauricular').setValue((NeonatalSurveyParam.Preauricular)&&(NeonatalSurveyParam.Preauricular == 2) ? true : false);			//Преаурикулярные кожные выросты
		if (NeonatalSurveyParam.PreauricularLoc) {
			RadioString = NeonatalSurveyParam.PreauricularLoc;																			//Преаурикулярные кожные выросты - сторона
			for (var i = 0; i < this.findById('swENSEW_PreauricularLoc').items.items.length; i++  )
				this.findById('swENSEW_PreauricularLoc').items.items[i].setValue(parseInt(RadioString[0]) == i ? true : false);		
		}
		else { 
			this.findById('swENSEW_PreauricularLoc').items.items[0].setValue(true);
			for (var i = 1; i < this.findById('swENSEW_PreauricularLoc').items.items.length; i++  ) this.findById('swENSEW_PreauricularLoc').items.items[i].setValue(false);						
		}
		this.findById('swENSEW_PreauricularLoc').setDisabled(!this.findById('swENSEW_Preauricular').getValue());  //BOB - 06.05.2020
		this.findById('swENSEW_EarsOtherFeat').setValue(!Ext.isEmpty(NeonatalSurveyParam.EarsOtherFeat) ? NeonatalSurveyParam.EarsOtherFeat : '');//Уши другие особенности
		this.findById('swENSEW_FlinchAtSound').setValue((NeonatalSurveyParam.FlinchAtSound)&&(NeonatalSurveyParam.FlinchAtSound == 2) ? true : false);			//Вздрагивание на звук
		if(this.FirstConditionLoad) this.findById('swENSEW_Ears_Panel').collapse();// скрываю при первой загрузке наблюдений, !!!!убрать если по умолчанию надо оставить открытыми
		//ГЛАЗА
		if (NeonatalSurveyParam.ScleraColor) {
			RadioString = NeonatalSurveyParam.ScleraColor;																			//Цвет склеры
			for (var i = 0; i < this.findById('swENSEW_ScleraColor').items.items.length; i++  )
				this.findById('swENSEW_ScleraColor').items.items[i].setValue(parseInt(RadioString[0]) == i ? true : false);		
		}
		else { 
			this.findById('swENSEW_ScleraColor').items.items[0].setValue(true);
			for (var i = 1; i < this.findById('swENSEW_ScleraColor').items.items.length; i++  ) this.findById('swENSEW_ScleraColor').items.items[i].setValue(false);						
		}
		this.findById('swENSEW_BrushfieldSpots').setValue((NeonatalSurveyParam.BrushfieldSpots)&&(NeonatalSurveyParam.BrushfieldSpots == 2) ? true : false);			//Пятна Брашвилда
		this.findById('swENSEW_SubconjunctHemor').setValue((NeonatalSurveyParam.SubconjunctHemor)&&(NeonatalSurveyParam.SubconjunctHemor == 2) ? true : false);			//Подконьюнктивальные кровоизлияния
		this.findById('swENSEW_RacyPleats').setValue((NeonatalSurveyParam.RacyPleats)&&(NeonatalSurveyParam.RacyPleats == 2) ? true : false);			//Эпикантные складки
		value = 0;
		if (NeonatalSurveyParam.Nystagm) {
			RadioString = NeonatalSurveyParam.Nystagm;					//Нистагм
			for (var i = 0; i < this.findById('swENSEW_Nystagm1').items.items.length; i++  )
				this.findById('swENSEW_Nystagm1').items.items[i].setValue(parseInt(RadioString[0]) == i ? true : false);		
				value = parseInt(RadioString[0]);		
			for (var i = 0; i < this.findById('swENSEW_Nystagm2').items.items.length; i++  )
				this.findById('swENSEW_Nystagm2').items.items[i].setValue(parseInt(RadioString[1]) == i ? true : false);		
		}
		else {
			this.findById('swENSEW_Nystagm1').items.items[0].setValue(true);		
			for (var i = 1; i < this.findById('swENSEW_Nystagm1').items.items.length; i++  ) this.findById('swENSEW_Nystagm1').items.items[i].setValue(false);						
			this.findById('swENSEW_Nystagm2').items.items[0].setValue(true);		
			for (var i = 1; i < this.findById('swENSEW_Nystagm2').items.items.length; i++  ) this.findById('swENSEW_Nystagm2').items.items[i].setValue(false);						
		}
		this.findById('swENSEW_Nystagm2').disable();
		if(value > 0) this.findById('swENSEW_Nystagm2').enable();
		if (NeonatalSurveyParam.Ptosis) {
			RadioString = NeonatalSurveyParam.Ptosis;																			//Птоз
			for (var i = 0; i < this.findById('swENSEW_Ptosis').items.items.length; i++  )
				this.findById('swENSEW_Ptosis').items.items[i].setValue(parseInt(RadioString[0]) == i ? true : false);		
		}
		else { 
			this.findById('swENSEW_Ptosis').items.items[0].setValue(true);
			for (var i = 1; i < this.findById('swENSEW_Ptosis').items.items.length; i++  ) this.findById('swENSEW_Ptosis').items.items[i].setValue(false);						
		}
		if (NeonatalSurveyParam.Leucorrhea) {
			RadioString = NeonatalSurveyParam.Leucorrhea;																			//Лейкория
			for (var i = 0; i < this.findById('swENSEW_Leucorrhea').items.items.length; i++  )
				this.findById('swENSEW_Leucorrhea').items.items[i].setValue(parseInt(RadioString[0]) == i ? true : false);		
		}
		else { 
			this.findById('swENSEW_Leucorrhea').items.items[0].setValue(true);
			for (var i = 1; i < this.findById('swENSEW_Leucorrhea').items.items.length; i++  ) this.findById('swENSEW_Leucorrhea').items.items[i].setValue(false);						
		}
		if (NeonatalSurveyParam.Conjunctivitis) {
			RadioString = NeonatalSurveyParam.Conjunctivitis;					//Коньюктивит
			for (var i = 0; i < this.findById('swENSEW_Conjunctivitis1').items.items.length; i++  )
				this.findById('swENSEW_Conjunctivitis1').items.items[i].setValue(parseInt(RadioString[0]) == i ? true : false);		
			for (var i = 0; i < this.findById('swENSEW_Conjunctivitis2').items.items.length; i++  )
				this.findById('swENSEW_Conjunctivitis2').items.items[i].setValue(parseInt(RadioString[1]) == i ? true : false);		
		}
		else {
			this.findById('swENSEW_Conjunctivitis1').items.items[0].setValue(true);		
			for (var i = 1; i < this.findById('swENSEW_Conjunctivitis1').items.items.length; i++  ) this.findById('swENSEW_Conjunctivitis1').items.items[i].setValue(false);						
			this.findById('swENSEW_Conjunctivitis2').items.items[0].setValue(true);		
			for (var i = 1; i < this.findById('swENSEW_Conjunctivitis2').items.items.length; i++  ) this.findById('swENSEW_Conjunctivitis2').items.items[i].setValue(false);						
		}
		this.findById('swENSEW_ObturatLacrimDuct').setValue((NeonatalSurveyParam.ObturatLacrimDuct)&&(NeonatalSurveyParam.ObturatLacrimDuct == 2) ? true : false);			//Обтурация слезного протока
		this.findById('swENSEW_ObturatLacrimDuctTxt').setValue(!Ext.isEmpty(NeonatalSurveyParam.ObturatLacrimDuctTxt) ? NeonatalSurveyParam.ObturatLacrimDuctTxt : '');//Обтурация слезного протока - локализация
		this.findById('swENSEW_ObturatLacrimDuctTxt').setDisabled(!this.findById('swENSEW_ObturatLacrimDuct').getValue());  //BOB - 06.05.2020
		if(this.FirstConditionLoad) this.findById('swENSEW_eyes_Panel').collapse();// скрываю при первой загрузке наблюдений, !!!!убрать если по умолчанию надо оставить открытыми
		//НОС
		if (NeonatalSurveyParam.NasalPassAtresia) {
			RadioString = NeonatalSurveyParam.NasalPassAtresia;																			//Атрезия носовых ходов
			for (var i = 0; i < this.findById('swENSEW_NasalPassAtresia').items.items.length; i++  )
				this.findById('swENSEW_NasalPassAtresia').items.items[i].setValue(parseInt(RadioString[0]) == i ? true : false);		
		}
		else { 
			this.findById('swENSEW_NasalPassAtresia').items.items[0].setValue(true);
			for (var i = 1; i < this.findById('swENSEW_NasalPassAtresia').items.items.length; i++  ) this.findById('swENSEW_NasalPassAtresia').items.items[i].setValue(false);						
		}
		this.findById('swENSEW_Sniff').setValue((NeonatalSurveyParam.Sniff)&&(NeonatalSurveyParam.Sniff == 2) ? true : false);			//Сопение
		this.findById('swENSEW_NasalExcreta').setValue((NeonatalSurveyParam.NasalExcreta)&&(NeonatalSurveyParam.NasalExcreta == 2) ? true : false);			//Выделения из носа
		if(this.FirstConditionLoad) this.findById('swENSEW_nose_Panel').collapse();// скрываю при первой загрузке наблюдений, !!!!убрать если по умолчанию надо оставить открытыми
		//РОТ
		this.findById('swENSEW_LipCleft').setValue((NeonatalSurveyParam.LipCleft)&&(NeonatalSurveyParam.LipCleft == 2) ? true : false);			//Расщепление губы
		if (NeonatalSurveyParam.PalateCleft) {
			RadioString = NeonatalSurveyParam.PalateCleft;																			//Расщепление нёба
			for (var i = 0; i < this.findById('swENSEW_PalateCleft').items.items.length; i++  )
				this.findById('swENSEW_PalateCleft').items.items[i].setValue(parseInt(RadioString[0]) == i ? true : false);		
		}
		else { 
			this.findById('swENSEW_PalateCleft').items.items[0].setValue(true);
			for (var i = 1; i < this.findById('swENSEW_PalateCleft').items.items.length; i++  ) this.findById('swENSEW_PalateCleft').items.items[i].setValue(false);						
		}
		this.findById('swENSEW_ShortBridle').setValue((NeonatalSurveyParam.ShortBridle)&&(NeonatalSurveyParam.ShortBridle == 2) ? true : false);			//Короткая уздечка
		this.findById('swENSEW_Ranula').setValue((NeonatalSurveyParam.Ranula)&&(NeonatalSurveyParam.Ranula == 2) ? true : false);			//Ранула
		this.findById('swENSEW_Mucocele').setValue((NeonatalSurveyParam.Mucocele)&&(NeonatalSurveyParam.Mucocele == 2) ? true : false);			//Мукоцеле
		if (NeonatalSurveyParam.PrenatalTeeth) {
			RadioString = NeonatalSurveyParam.PrenatalTeeth;																			//Пренатальные зубы
			for (var i = 0; i < this.findById('swENSEW_PrenatalTeeth').items.items.length; i++  )
				this.findById('swENSEW_PrenatalTeeth').items.items[i].setValue(parseInt(RadioString[0]) == i ? true : false);		
		}
		else { 
			this.findById('swENSEW_PrenatalTeeth').items.items[0].setValue(true);
			for (var i = 1; i < this.findById('swENSEW_PrenatalTeeth').items.items.length; i++  ) this.findById('swENSEW_PrenatalTeeth').items.items[i].setValue(false);						
		}
		this.findById('swENSEW_PrenatalTeethTxt').setValue(!Ext.isEmpty(NeonatalSurveyParam.PrenatalTeethTxt) ? NeonatalSurveyParam.PrenatalTeethTxt : '');//Пренатальные зубы - локализация
		if (NeonatalSurveyParam.Macroglossia) {
			RadioString = NeonatalSurveyParam.Macroglossia;																			//Макроглоссия
			for (var i = 0; i < this.findById('swENSEW_Macroglossia').items.items.length; i++  )
				this.findById('swENSEW_Macroglossia').items.items[i].setValue(parseInt(RadioString[0]) == i ? true : false);		
		}
		else { 
			this.findById('swENSEW_Macroglossia').items.items[0].setValue(true);
			for (var i = 1; i < this.findById('swENSEW_Macroglossia').items.items.length; i++  ) this.findById('swENSEW_Macroglossia').items.items[i].setValue(false);						
		}
		this.findById('swENSEW_MouthSepar').setValue((NeonatalSurveyParam.MouthSepar)&&(NeonatalSurveyParam.MouthSepar == 2) ? true : false);			//MouthSepar
		//BOB - 23.04.2020
		if (!this.findById('swENSEW_MouthSepar').getValue()){
			this.findById('swENSEW_MouthSeparFoamy').setDisabled(true);
			this.findById('swENSEW_MouthSeparAbund').setDisabled(true);
		}
		this.findById('swENSEW_MouthSeparFoamy').setValue((NeonatalSurveyParam.MouthSeparFoamy)&&(NeonatalSurveyParam.MouthSeparFoamy == 2) ? true : false);			//Отделяемое изо рта пенистое
		this.findById('swENSEW_MouthSeparAbund').setValue((NeonatalSurveyParam.MouthSeparAbund)&&(NeonatalSurveyParam.MouthSeparAbund == 2) ? true : false);			//Отделяемое изо рта обильное
		this.findById('swENSEW_Thrush').setValue((NeonatalSurveyParam.Thrush)&&(NeonatalSurveyParam.Thrush == 2) ? true : false);			//Молочница
		if(this.FirstConditionLoad) this.findById('swENSEW_Mouth_Panel').collapse();// скрываю при первой загрузке наблюдений, !!!!убрать если по умолчанию надо оставить открытыми
		//ДЫХАНИЕ
		if (NeonatalSurveyParam.Breath) {
			RadioString = NeonatalSurveyParam.Breath;																							//Дыхание
			for (var i = 0; i < this.findById('swENSEW_Breath1').items.items.length; i++  )
				this.findById('swENSEW_Breath1').items.items[i].setValue(parseInt(RadioString[0]) == i ? true : false);		
			for (var i = 0; i < this.findById('swENSEW_Breath2').items.items.length; i++  )
				this.findById('swENSEW_Breath2').items.items[i].setValue(parseInt(RadioString[1]) == i ? true : false);		
			for (var i = 0; i < this.findById('swENSEW_Breath3').items.items.length; i++  )
				this.findById('swENSEW_Breath3').items.items[i].setValue(parseInt(RadioString[2]) == i ? true : false);		
		}
		else {
			this.findById('swENSEW_Breath1').items.items[0].setValue(true);		
			for (var i = 1; i < this.findById('swENSEW_Breath1').items.items.length; i++  ) this.findById('swENSEW_Breath1').items.items[i].setValue(false);						
			this.findById('swENSEW_Breath2').items.items[0].setValue(true);		
			for (var i = 1; i < this.findById('swENSEW_Breath2').items.items.length; i++  ) this.findById('swENSEW_Breath2').items.items[i].setValue(false);						
			this.findById('swENSEW_Breath3').items.items[0].setValue(true);		
			for (var i = 1; i < this.findById('swENSEW_Breath3').items.items.length; i++  ) this.findById('swENSEW_Breath3').items.items[i].setValue(false);						
		}
		this.findById('swENSEW_BreathTxt').setValue(!Ext.isEmpty(NeonatalSurveyParam.BreathTxt) ? NeonatalSurveyParam.BreathTxt : '');// Дыхание - ваариант пользователя
		if (NeonatalSurveyParam.RespirTherapy) {
			RadioString = NeonatalSurveyParam.RespirTherapy;																							//Респираторная терапия
			for (var i = 0; i < this.findById('swENSEW_RespirTherapy1').items.items.length; i++  )
				this.findById('swENSEW_RespirTherapy1').items.items[i].setValue(parseInt(RadioString[0]) == i ? true : false);		
			for (var i = 0; i < this.findById('swENSEW_RespirTherapy2').items.items.length; i++  )
				this.findById('swENSEW_RespirTherapy2').items.items[i].setValue(parseInt(RadioString[1]) == i ? true : false);		
		}
		else {
			this.findById('swENSEW_RespirTherapy1').items.items[0].setValue(true);		
			for (var i = 1; i < this.findById('swENSEW_RespirTherapy1').items.items.length; i++  ) this.findById('swENSEW_RespirTherapy1').items.items[i].setValue(false);						
			this.findById('swENSEW_RespirTherapy2').items.items[0].setValue(true);		
			for (var i = 1; i < this.findById('swENSEW_RespirTherapy2').items.items.length; i++  ) this.findById('swENSEW_RespirTherapy2').items.items[i].setValue(false);						
		}
		this.findById('swENSEW_RespirTherapyTxt').setValue(!Ext.isEmpty(NeonatalSurveyParam.RespirTherapyTxt) ? NeonatalSurveyParam.RespirTherapyTxt : '');// Респираторная терапия - вариант пользователя
		this.findById('swENSEW_Ventilator').setValue(!Ext.isEmpty(NeonatalSurveyParam.Ventilator) ? NeonatalSurveyParam.Ventilator : '');// Аппарат ИВЛ
		this.findById('swENSEW_VentilatorParam').setValue(!Ext.isEmpty(NeonatalSurveyParam.VentilatorParam) ? NeonatalSurveyParam.VentilatorParam : '');// Параметры ИВЛ
		if(this.FirstConditionLoad) this.findById('swENSEW_Breath_Panel').collapse();// скрываю при первой загрузке наблюдений, !!!!убрать если по умолчанию надо оставить открытыми
		//ГРУДНАЯ КЛЕТКА
		if (NeonatalSurveyParam.ThoraxShape) {
			RadioString = NeonatalSurveyParam.ThoraxShape;																							//Форма грудной клетки
			for (var i = 0; i < this.findById('swENSEW_ThoraxShape1').items.items.length; i++  )
				this.findById('swENSEW_ThoraxShape1').items.items[i].setValue(parseInt(RadioString[0]) == i ? true : false);		
			for (var i = 0; i < this.findById('swENSEW_ThoraxShape2').items.items.length; i++  )
				this.findById('swENSEW_ThoraxShape2').items.items[i].setValue(parseInt(RadioString[1]) == i ? true : false);		
		}
		else {
			this.findById('swENSEW_ThoraxShape1').items.items[0].setValue(true);		
			for (var i = 1; i < this.findById('swENSEW_ThoraxShape1').items.items.length; i++  ) this.findById('swENSEW_ThoraxShape1').items.items[i].setValue(false);						
			this.findById('swENSEW_ThoraxShape2').items.items[0].setValue(true);		
			for (var i = 1; i < this.findById('swENSEW_ThoraxShape2').items.items.length; i++  ) this.findById('swENSEW_ThoraxShape2').items.items[i].setValue(false);						
		}
		this.findById('swENSEW_ThoraxShapeTxt').setValue(!Ext.isEmpty(NeonatalSurveyParam.ThoraxShapeTxt) ? NeonatalSurveyParam.ThoraxShapeTxt : '');// Форма грудной клетки - вариант пользователя
		if (NeonatalSurveyParam.ThoraxFeatures) {
			RadioString = NeonatalSurveyParam.ThoraxFeatures;																							//Особенности грудной клетки
			for (var i = 0; i < this.findById('swENSEW_ThoraxFeatures1').items.items.length; i++  )
				this.findById('swENSEW_ThoraxFeatures1').items.items[i].setValue(parseInt(RadioString[0]) == i ? true : false);		
			for (var i = 0; i < this.findById('swENSEW_ThoraxFeatures2').items.items.length; i++  )
				this.findById('swENSEW_ThoraxFeatures2').items.items[i].setValue(parseInt(RadioString[1]) == i ? true : false);		
			for (var i = 0; i < this.findById('swENSEW_ThoraxFeatures3').items.items.length; i++  )
				this.findById('swENSEW_ThoraxFeatures3').items.items[i].setValue(parseInt(RadioString[2]) == i ? true : false);		
			for (var i = 0; i < this.findById('swENSEW_ThoraxFeatures4').items.items.length; i++  )
				this.findById('swENSEW_ThoraxFeatures4').items.items[i].setValue(parseInt(RadioString[3]) == i ? true : false);		
			for (var i = 0; i < this.findById('swENSEW_ThoraxFeatures5').items.items.length; i++  )
				this.findById('swENSEW_ThoraxFeatures5').items.items[i].setValue(parseInt(RadioString[4]) == i ? true : false);		
		}
		else {
			this.findById('swENSEW_ThoraxFeatures1').items.items[0].setValue(true);		
			for (var i = 1; i < this.findById('swENSEW_ThoraxFeatures1').items.items.length; i++  ) this.findById('swENSEW_ThoraxFeatures1').items.items[i].setValue(false);						
			this.findById('swENSEW_ThoraxFeatures2').items.items[0].setValue(true);		
			for (var i = 1; i < this.findById('swENSEW_ThoraxFeatures2').items.items.length; i++  ) this.findById('swENSEW_ThoraxFeatures2').items.items[i].setValue(false);						
			this.findById('swENSEW_ThoraxFeatures3').items.items[0].setValue(true);		
			for (var i = 1; i < this.findById('swENSEW_ThoraxFeatures3').items.items.length; i++  ) this.findById('swENSEW_ThoraxFeatures3').items.items[i].setValue(false);						
			this.findById('swENSEW_ThoraxFeatures4').items.items[0].setValue(true);		
			for (var i = 1; i < this.findById('swENSEW_ThoraxFeatures4').items.items.length; i++  ) this.findById('swENSEW_ThoraxFeatures4').items.items[i].setValue(false);						
			this.findById('swENSEW_ThoraxFeatures5').items.items[0].setValue(true);		
			for (var i = 1; i < this.findById('swENSEW_ThoraxFeatures5').items.items.length; i++  ) this.findById('swENSEW_ThoraxFeatures5').items.items[i].setValue(false);						
		}
		//Аускультативно
		for(var i in BreathAuscultative){
			if (BreathAuscultative[i]['SideType_SysNick']) {
				for (var j = 0; j < win.findById('swENSEW_Auscultatory_'+BreathAuscultative[i]['SideType_SysNick']+'_1').items.items.length; j++  )
					win.findById('swENSEW_Auscultatory_'+BreathAuscultative[i]['SideType_SysNick']+'_1').items.items[j].setValue(BreathAuscultative[i]['BreathAuscultative_Auscult'] == j);								
				win.findById('swENSEW_AuscultTxt_'+BreathAuscultative[i]['SideType_SysNick']).setValue(BreathAuscultative[i]['BreathAuscultative_AuscultTxt']);

				for (var j = 0; j < win.findById('swENSEW_Auscultatory_'+BreathAuscultative[i]['SideType_SysNick']+'_2').items.items.length; j++  )
					win.findById('swENSEW_Auscultatory_'+BreathAuscultative[i]['SideType_SysNick']+'_2').items.items[j].setValue(BreathAuscultative[i]['BreathAuscultative_Rale'] == j);
				value = BreathAuscultative[i]['BreathAuscultative_Rale'];
				for (var j = 0; j < win.findById('swENSEW_Auscultatory_'+BreathAuscultative[i]['SideType_SysNick']+'_3').items.items.length; j++  )
					win.findById('swENSEW_Auscultatory_'+BreathAuscultative[i]['SideType_SysNick']+'_3').items.items[j].setValue(((BreathAuscultative[i]['BreathAuscultative_IsPleuDrain']) ? BreathAuscultative[i]['BreathAuscultative_IsPleuDrain'] : 0)  == j);
				win.findById('swENSEW_Auscultatory_'+BreathAuscultative[i]['SideType_SysNick']+'_3').disable();						
				if(value > 0)	win.findById('swENSEW_Auscultatory_'+BreathAuscultative[i]['SideType_SysNick']+'_3').enable();	
				if(this.FirstConditionLoad) this.findById('swENSEW_Auscultatory_'+BreathAuscultative[i]['SideType_SysNick']).collapse();// скрываю при первой загрузке наблюдений, !!!!убрать если по умолчанию надо оставить открытыми
			}
		}
		value = 0;
		if (NeonatalSurveyParam.VDPSeparated) {
			RadioString = NeonatalSurveyParam.VDPSeparated;																							//Отделяемое из ВДП
			for (var i = 0; i < this.findById('swENSEW_VDPSeparated1').items.items.length; i++  )
				this.findById('swENSEW_VDPSeparated1').items.items[i].setValue(parseInt(RadioString[0]) == i ? true : false);		
			value = parseInt(RadioString[0]);		
			for (var i = 0; i < this.findById('swENSEW_VDPSeparated2').items.items.length; i++  )
				this.findById('swENSEW_VDPSeparated2').items.items[i].setValue(parseInt(RadioString[1]) == i ? true : false);		
		}
		else {
			this.findById('swENSEW_VDPSeparated1').items.items[0].setValue(true);		
			for (var i = 1; i < this.findById('swENSEW_VDPSeparated1').items.items.length; i++  ) this.findById('swENSEW_VDPSeparated1').items.items[i].setValue(false);						
			this.findById('swENSEW_VDPSeparated2').items.items[0].setValue(true);		
			for (var i = 1; i < this.findById('swENSEW_VDPSeparated2').items.items.length; i++  ) this.findById('swENSEW_VDPSeparated2').items.items[i].setValue(false);						
		}
		this.findById('swENSEW_VDPSeparated2').disable();						
		if(value > 0)	this.findById('swENSEW_VDPSeparated2').enable();	
		this.findById('swENSEW_VDPSeparatedTxt').setValue(!Ext.isEmpty(NeonatalSurveyParam.VDPSeparatedTxt) ? NeonatalSurveyParam.VDPSeparatedTxt : '');// Отделяемое из ВДП - вариант пользователя
		value = 0;
		if (NeonatalSurveyParam.ETTSeparated) {
			RadioString = NeonatalSurveyParam.ETTSeparated;																							//Отделяемое из ЭТТ
			for (var i = 0; i < this.findById('swENSEW_ETTSeparated1').items.items.length; i++  )
				this.findById('swENSEW_ETTSeparated1').items.items[i].setValue(parseInt(RadioString[0]) == i ? true : false);		
			value = parseInt(RadioString[0]);		
			for (var i = 0; i < this.findById('swENSEW_ETTSeparated2').items.items.length; i++  )
				this.findById('swENSEW_ETTSeparated2').items.items[i].setValue(parseInt(RadioString[1]) == i ? true : false);		
		}
		else {
			this.findById('swENSEW_ETTSeparated1').items.items[0].setValue(true);		
			for (var i = 1; i < this.findById('swENSEW_ETTSeparated1').items.items.length; i++  ) this.findById('swENSEW_ETTSeparated1').items.items[i].setValue(false);						
			this.findById('swENSEW_ETTSeparated2').items.items[0].setValue(true);		
			for (var i = 1; i < this.findById('swENSEW_ETTSeparated2').items.items.length; i++  ) this.findById('swENSEW_ETTSeparated2').items.items[i].setValue(false);						
		}
		this.findById('swENSEW_ETTSeparated2').disable();						
		if(value > 0)	this.findById('swENSEW_ETTSeparated2').enable();	
		this.findById('swENSEW_ETTSeparatedTxt').setValue(!Ext.isEmpty(NeonatalSurveyParam.ETTSeparatedTxt) ? NeonatalSurveyParam.ETTSeparatedTxt : '');// Отделяемое из ЭТТ - вариант пользователя
		if (NeonatalSurveyParam.MammaryGlands) {
			RadioString = NeonatalSurveyParam.MammaryGlands;																							//Молочные железы
			for (var i = 0; i < this.findById('swENSEW_MammaryGlands1').items.items.length; i++  )
				this.findById('swENSEW_MammaryGlands1').items.items[i].setValue(parseInt(RadioString[0]) == i ? true : false);		
			for (var i = 0; i < this.findById('swENSEW_MammaryGlands2').items.items.length; i++  )
				this.findById('swENSEW_MammaryGlands2').items.items[i].setValue(parseInt(RadioString[1]) == i ? true : false);		
			for (var i = 0; i < this.findById('swENSEW_MammaryGlands3').items.items.length; i++  )
				this.findById('swENSEW_MammaryGlands3').items.items[i].setValue(parseInt(RadioString[2]) == i ? true : false);		
		}
		else {
			this.findById('swENSEW_MammaryGlands1').items.items[0].setValue(true);		
			for (var i = 1; i < this.findById('swENSEW_MammaryGlands1').items.items.length; i++  ) this.findById('swENSEW_MammaryGlands1').items.items[i].setValue(false);						
			this.findById('swENSEW_MammaryGlands2').items.items[0].setValue(true);		
			for (var i = 1; i < this.findById('swENSEW_MammaryGlands2').items.items.length; i++  ) this.findById('swENSEW_MammaryGlands2').items.items[i].setValue(false);						
			this.findById('swENSEW_MammaryGlands3').items.items[0].setValue(true);		
			for (var i = 1; i < this.findById('swENSEW_MammaryGlands3').items.items.length; i++  ) this.findById('swENSEW_MammaryGlands3').items.items[i].setValue(false);						
		}
		if(this.FirstConditionLoad) this.findById('swENSEW_Thorax_Panel').collapse();// скрываю при первой загрузке наблюдений, !!!!убрать если по умолчанию надо оставить открытыми
		//ГЕМОДИНАМИКА
		if (NeonatalSurveyParam.Hemodynamics) {
			RadioString = NeonatalSurveyParam.Hemodynamics;
			for (var i = 0; i < this.findById('swENSEW_Hemodynamics').items.items.length; i++  )
				this.findById('swENSEW_Hemodynamics').items.items[i].setValue(parseInt(RadioString[0]) == i ? true : false);		
		}
		else { 
			this.findById('swENSEW_Hemodynamics').items.items[0].setValue(true);
			for (var i = 1; i < this.findById('swENSEW_Hemodynamics').items.items.length; i++  ) this.findById('swENSEW_Hemodynamics').items.items[i].setValue(false);						
		}
		this.findById('swENSEW_HemodynamicsTxt').setValue(!Ext.isEmpty(NeonatalSurveyParam.HemodynamicsTxt) ? NeonatalSurveyParam.HemodynamicsTxt : '');// 	
		if(this.FirstConditionLoad) this.findById('swENSEW_Hemodynamics_Panel').collapse();// скрываю при первой загрузке наблюдений, !!!!убрать если по умолчанию надо оставить открытыми
		//СЕРДЦЕ
		if (NeonatalSurveyParam.HeartTones) {
			RadioString = NeonatalSurveyParam.HeartTones;																							//Сердечные тоны
			for (var i = 0; i < this.findById('swENSEW_HeartTones1').items.items.length; i++  )
				this.findById('swENSEW_HeartTones1').items.items[i].setValue(parseInt(RadioString[0]) == i ? true : false);		
			for (var i = 0; i < this.findById('swENSEW_HeartTones2').items.items.length; i++  )
				this.findById('swENSEW_HeartTones2').items.items[i].setValue(parseInt(RadioString[1]) == i ? true : false);		
		}
		else {
			this.findById('swENSEW_HeartTones1').items.items[0].setValue(true);		
			for (var i = 1; i < this.findById('swENSEW_HeartTones1').items.items.length; i++  ) this.findById('swENSEW_HeartTones1').items.items[i].setValue(false);						
			this.findById('swENSEW_HeartTones2').items.items[0].setValue(true);		
			for (var i = 1; i < this.findById('swENSEW_HeartTones2').items.items.length; i++  ) this.findById('swENSEW_HeartTones2').items.items[i].setValue(false);						
		}
		if (NeonatalSurveyParam.HeartNoise) {
			RadioString = NeonatalSurveyParam.HeartNoise;																							//Сердечный шум
			for (var i = 0; i < this.findById('swENSEW_HeartNoise1').items.items.length; i++  )
				this.findById('swENSEW_HeartNoise1').items.items[i].setValue(parseInt(RadioString[0]) == i ? true : false);		
			for (var i = 0; i < this.findById('swENSEW_HeartNoise2').items.items.length; i++  )
				this.findById('swENSEW_HeartNoise2').items.items[i].setValue(parseInt(RadioString[1]) == i ? true : false);		
		}
		else {
			this.findById('swENSEW_HeartNoise1').items.items[0].setValue(true);		
			for (var i = 1; i < this.findById('swENSEW_HeartNoise1').items.items.length; i++  ) this.findById('swENSEW_HeartNoise1').items.items[i].setValue(false);						
			this.findById('swENSEW_HeartNoise2').items.items[0].setValue(true);		
			for (var i = 1; i < this.findById('swENSEW_HeartNoise2').items.items.length; i++  ) this.findById('swENSEW_HeartNoise2').items.items[i].setValue(false);						
		}
		if (NeonatalSurveyParam.PulseRadialis) {
			RadioString = NeonatalSurveyParam.PulseRadialis;																							//Пульс на a. radialis
			for (var i = 0; i < this.findById('swENSEW_PulseRadialis1').items.items.length; i++  )
				this.findById('swENSEW_PulseRadialis1').items.items[i].setValue(parseInt(RadioString[0]) == i ? true : false);		
			for (var i = 0; i < this.findById('swENSEW_PulseRadialis2').items.items.length; i++  )
				this.findById('swENSEW_PulseRadialis2').items.items[i].setValue(parseInt(RadioString[1]) == i ? true : false);		
		}
		else {
			this.findById('swENSEW_PulseRadialis1').items.items[0].setValue(true);		
			for (var i = 1; i < this.findById('swENSEW_PulseRadialis1').items.items.length; i++  ) this.findById('swENSEW_PulseRadialis1').items.items[i].setValue(false);						
			this.findById('swENSEW_PulseRadialis2').items.items[0].setValue(true);		
			for (var i = 1; i < this.findById('swENSEW_PulseRadialis2').items.items.length; i++  ) this.findById('swENSEW_PulseRadialis2').items.items[i].setValue(false);						
		}
		if (NeonatalSurveyParam.Microcirculation) {
			RadioString = NeonatalSurveyParam.Microcirculation;																						//Микроциркуляция
			for (var i = 0; i < this.findById('swENSEW_Microcirculation').items.items.length; i++  )
				this.findById('swENSEW_Microcirculation').items.items[i].setValue(parseInt(RadioString[0]) == i ? true : false);		
		}
		else { 
			this.findById('swENSEW_Microcirculation').items.items[0].setValue(true);
			for (var i = 1; i < this.findById('swENSEW_Microcirculation').items.items.length; i++  ) this.findById('swENSEW_Microcirculation').items.items[i].setValue(false);						
		}
		this.findById('swENSEW_TimeFillingCapilСhest').setValue(!Ext.isEmpty(NeonatalSurveyParam.TimeFillingCapilСhest) ? NeonatalSurveyParam.TimeFillingCapilСhest : '');   //Время наполнения капилляров на грудине
		this.findById('swENSEW_TimeFillingCapilExtr').setValue(!Ext.isEmpty(NeonatalSurveyParam.TimeFillingCapilExtr) ? NeonatalSurveyParam.TimeFillingCapilExtr : '');   //Время наполнения капилляров на конечностях
		if(this.FirstConditionLoad) this.findById('swENSEW_Heart_Panel').collapse();// скрываю при первой загрузке наблюдений, !!!!убрать если по умолчанию надо оставить открытыми
		//ЯЗЫК
		if (NeonatalSurveyParam.TongueState) {
			RadioString = NeonatalSurveyParam.TongueState;
			for (var i = 0; i < this.findById('swENSEW_TongueState').items.items.length; i++  )
				this.findById('swENSEW_TongueState').items.items[i].setValue(parseInt(RadioString[0]) == i ? true : false);		
		}
		else { 
			this.findById('swENSEW_TongueState').items.items[0].setValue(true);
			for (var i = 1; i < this.findById('swENSEW_TongueState').items.items.length; i++  ) this.findById('swENSEW_TongueState').items.items[i].setValue(false);						
		}
		this.findById('swENSEW_TongueScurf').setValue((NeonatalSurveyParam.TongueScurf)&&(NeonatalSurveyParam.TongueScurf == 2) ? true : false);			//Язык - налёт
		this.findById('swENSEW_TongueScurfSeverity').setValue(!Ext.isEmpty(NeonatalSurveyParam.TongueScurfSeverity) ? NeonatalSurveyParam.TongueScurfSeverity : '');// 	Язык – налёт – выраженность
		this.findById('swENSEW_TongueScurfColor').setValue(!Ext.isEmpty(NeonatalSurveyParam.TongueScurfColor) ? NeonatalSurveyParam.TongueScurfColor : '');// 	Язык – налёт - цвет
		this.findById('swENSEW_TongueScurfSeverity').setDisabled(!this.findById('swENSEW_TongueScurf').getValue());  //BOB - 06.05.2020
		this.findById('swENSEW_TongueScurfColor').setDisabled(!this.findById('swENSEW_TongueScurf').getValue());  //BOB - 06.05.2020
		if(this.FirstConditionLoad) this.findById('swENSEW_Tongue_Panel').collapse();// скрываю при первой загрузке наблюдений, !!!!убрать если по умолчанию надо оставить открытыми
		//ОТДЕЛЯЕМОЕ ИЗ ЖЕЛУДКА
		value = 0;
		if (NeonatalSurveyParam.StomachDischarge) {
			RadioString = NeonatalSurveyParam.StomachDischarge;																							//Отделяемое из желудка
			for (var i = 0; i < this.findById('swENSEW_StomachDischarge1').items.items.length; i++  )
				this.findById('swENSEW_StomachDischarge1').items.items[i].setValue(parseInt(RadioString[0]) == i ? true : false);	
			value = parseInt(RadioString[0]);		
			for (var i = 0; i < this.findById('swENSEW_StomachDischarge2').items.items.length; i++  )
				this.findById('swENSEW_StomachDischarge2').items.items[i].setValue(parseInt(RadioString[1]) == i ? true : false);		
			for (var i = 0; i < this.findById('swENSEW_StomachDischarge3').items.items.length; i++  )
				this.findById('swENSEW_StomachDischarge3').items.items[i].setValue(parseInt(RadioString[2]) == i ? true : false);		
		}
		else {
			this.findById('swENSEW_StomachDischarge1').items.items[0].setValue(true);		
			for (var i = 1; i < this.findById('swENSEW_StomachDischarge1').items.items.length; i++  ) this.findById('swENSEW_StomachDischarge1').items.items[i].setValue(false);						
			this.findById('swENSEW_StomachDischarge2').items.items[0].setValue(true);		
			for (var i = 1; i < this.findById('swENSEW_StomachDischarge2').items.items.length; i++  ) this.findById('swENSEW_StomachDischarge2').items.items[i].setValue(false);						
			this.findById('swENSEW_StomachDischarge3').items.items[0].setValue(true);		
			for (var i = 1; i < this.findById('swENSEW_StomachDischarge3').items.items.length; i++  ) this.findById('swENSEW_StomachDischarge3').items.items[i].setValue(false);						
		}
		this.findById('swENSEW_StomachDischarge2').disable();						
		this.findById('swENSEW_StomachDischarge3').disable();	
		if(value == 3)	this.findById('swENSEW_StomachDischarge2').enable();	
		else if (value == 4)	this.findById('swENSEW_StomachDischarge3').enable();			
		this.findById('swENSEW_StomachDischargeTxt').setValue(!Ext.isEmpty(NeonatalSurveyParam.StomachDischargeTxt) ? NeonatalSurveyParam.StomachDischargeTxt : '');// 	Отделяемое из желудка – вариант пользователя
		if(this.FirstConditionLoad) this.findById('swENSEW_StomachDischarge_Panel').collapse();// скрываю при первой загрузке наблюдений, !!!!убрать если по умолчанию надо оставить открытыми
		//ЖИВОТ
		if (NeonatalSurveyParam.AnterAbdomWallDef) {
			RadioString = NeonatalSurveyParam.AnterAbdomWallDef;																							//Дефекты передней брюшной стенки
			for (var i = 0; i < this.findById('swENSEW_AnterAbdomWallDef').items.items.length; i++  )
				this.findById('swENSEW_AnterAbdomWallDef').items.items[i].setValue(parseInt(RadioString[0]) == i ? true : false);		
		}
		else { 
			this.findById('swENSEW_AnterAbdomWallDef').items.items[0].setValue(true);
			for (var i = 1; i < this.findById('swENSEW_AnterAbdomWallDef').items.items.length; i++  ) this.findById('swENSEW_AnterAbdomWallDef').items.items[i].setValue(false);						
		}
		if (NeonatalSurveyParam.Swelling) {																												//Вздутие
			RadioString = NeonatalSurveyParam.Swelling;
			for (var i = 0; i < this.findById('swENSEW_Swelling').items.items.length; i++  )
				this.findById('swENSEW_Swelling').items.items[i].setValue(parseInt(RadioString[0]) == i ? true : false);		
		}
		else { 
			this.findById('swENSEW_Swelling').items.items[0].setValue(true);
			for (var i = 1; i < this.findById('swENSEW_Swelling').items.items.length; i++  ) this.findById('swENSEW_Swelling').items.items[i].setValue(false);						
		}
		this.findById('swENSEW_Hepatomegaly').setValue((NeonatalSurveyParam.Hepatomegaly)&&(NeonatalSurveyParam.Hepatomegaly == 2) ? true : false);			//Гепатомегалия
		this.findById('swENSEW_HepatomegalySize').setValue(!Ext.isEmpty(NeonatalSurveyParam.HepatomegalySize) ? NeonatalSurveyParam.HepatomegalySize : '');   //Гепатомегалия - размер от края реберной дуги
		this.findById('swENSEW_HepatomegalyPalpFeat').setValue(!Ext.isEmpty(NeonatalSurveyParam.HepatomegalyPalpFeat) ? NeonatalSurveyParam.HepatomegalyPalpFeat : '');// 	Гепатомегалия - особенности пальпации
		this.findById('swENSEW_HepatomegalySize').setDisabled(!this.findById('swENSEW_Hepatomegaly').getValue());  //BOB - 06.05.2020
		this.findById('swENSEW_HepatomegalyPalpFeat').setDisabled(!this.findById('swENSEW_Hepatomegaly').getValue());  //BOB - 06.05.2020
		this.findById('swENSEW_Splenomegaly').setValue((NeonatalSurveyParam.Splenomegaly)&&(NeonatalSurveyParam.Splenomegaly == 2) ? true : false);			//Спленомегалия
		this.findById('swENSEW_SplenomegalySize').setValue(!Ext.isEmpty(NeonatalSurveyParam.SplenomegalySize) ? NeonatalSurveyParam.SplenomegalySize : '');   //Спленомегалия - размер от края реберной дуги
		this.findById('swENSEW_SplenomegalyPalpFeat').setValue(!Ext.isEmpty(NeonatalSurveyParam.SplenomegalyPalpFeat) ? NeonatalSurveyParam.SplenomegalyPalpFeat : '');// 	Спленомегалия - особенности пальпации
		this.findById('swENSEW_SplenomegalySize').setDisabled(!this.findById('swENSEW_Splenomegaly').getValue());  //BOB - 06.05.2020
		this.findById('swENSEW_SplenomegalyPalpFeat').setDisabled(!this.findById('swENSEW_Splenomegaly').getValue());  //BOB - 06.05.2020
		if(this.FirstConditionLoad) this.findById('swENSEW_Stomach_Panel').collapse();// скрываю при первой загрузке наблюдений, !!!!убрать если по умолчанию надо оставить открытыми
		//ПАЛЬПАЦИЯ
		if (NeonatalSurveyParam.Palpation) {
			RadioString = NeonatalSurveyParam.Palpation;																							//Пальпация
			for (var i = 0; i < this.findById('swENSEW_Palpation1').items.items.length; i++  )
				this.findById('swENSEW_Palpation1').items.items[i].setValue(parseInt(RadioString[0]) == i ? true : false);		
			for (var i = 0; i < this.findById('swENSEW_Palpation2').items.items.length; i++  )
				this.findById('swENSEW_Palpation2').items.items[i].setValue(parseInt(RadioString[1]) == i ? true : false);		
			for (var i = 0; i < this.findById('swENSEW_Palpation3').items.items.length; i++  )
				this.findById('swENSEW_Palpation3').items.items[i].setValue(parseInt(RadioString[2]) == i ? true : false);		
		}
		else {
			this.findById('swENSEW_Palpation1').items.items[0].setValue(true);		
			for (var i = 1; i < this.findById('swENSEW_Palpation1').items.items.length; i++  ) this.findById('swENSEW_Palpation1').items.items[i].setValue(false);						
			this.findById('swENSEW_Palpation2').items.items[0].setValue(true);		
			for (var i = 1; i < this.findById('swENSEW_Palpation2').items.items.length; i++  ) this.findById('swENSEW_Palpation2').items.items[i].setValue(false);						
			this.findById('swENSEW_Palpation3').items.items[0].setValue(true);		
			for (var i = 1; i < this.findById('swENSEW_Palpation3').items.items.length; i++  ) this.findById('swENSEW_Palpation3').items.items[i].setValue(false);						
		}
		if (NeonatalSurveyParam.Peristalsis) {																												//Перистальтика
			RadioString = NeonatalSurveyParam.Peristalsis;
			for (var i = 0; i < this.findById('swENSEW_Peristalsis').items.items.length; i++  )
				this.findById('swENSEW_Peristalsis').items.items[i].setValue(parseInt(RadioString[0]) == i ? true : false);		
		}
		else { 
			this.findById('swENSEW_Peristalsis').items.items[0].setValue(true);
			for (var i = 1; i < this.findById('swENSEW_Peristalsis').items.items.length; i++  ) this.findById('swENSEW_Peristalsis').items.items[i].setValue(false);						
		}
		this.findById('swENSEW_BowLoopContoured').setValue((NeonatalSurveyParam.BowLoopContoured)&&(NeonatalSurveyParam.BowLoopContoured == 2) ? true : false);			//Петли кишечника контурируют
		this.findById('swENSEW_AnterAbdomWallSwell').setValue((NeonatalSurveyParam.AnterAbdomWallSwell)&&(NeonatalSurveyParam.AnterAbdomWallSwell == 2) ? true : false);			//Отечность передней брюшной стенки
		this.findById('swENSEW_AnterAbdomWallHyper').setValue((NeonatalSurveyParam.AnterAbdomWallHyper)&&(NeonatalSurveyParam.AnterAbdomWallHyper == 2) ? true : false);			//Гиперемия передней брюшной стенки
		if(this.FirstConditionLoad) this.findById('swENSEW_Palpation_Panel').collapse();// скрываю при первой загрузке наблюдений, !!!!убрать если по умолчанию надо оставить открытыми
		//АНОМАЛИИ БРЮШНОЙ ПОЛОПСТИ
		this.findById('swENSEW_VolFormAbdom').setValue((NeonatalSurveyParam.VolFormAbdom)&&(NeonatalSurveyParam.VolFormAbdom == 2) ? true : false);			//Объемные образования брюшной полости
		if (NeonatalSurveyParam.VolFormAbdomPar) {
			RadioString = NeonatalSurveyParam.VolFormAbdomPar;																							//Объемные образования брюшной полости – параметры
			for (var i = 0; i < this.findById('swENSEW_VolFormAbdomPar1').items.items.length; i++  )
				this.findById('swENSEW_VolFormAbdomPar1').items.items[i].setValue(parseInt(RadioString[0]) == i ? true : false);		
			for (var i = 0; i < this.findById('swENSEW_VolFormAbdomPar2').items.items.length; i++  )
				this.findById('swENSEW_VolFormAbdomPar2').items.items[i].setValue(parseInt(RadioString[1]) == i ? true : false);		
			for (var i = 0; i < this.findById('swENSEW_VolFormAbdomPar3').items.items.length; i++  )
				this.findById('swENSEW_VolFormAbdomPar3').items.items[i].setValue(parseInt(RadioString[2]) == i ? true : false);		
			for (var i = 0; i < this.findById('swENSEW_VolFormAbdomPar4').items.items.length; i++  )
				this.findById('swENSEW_VolFormAbdomPar4').items.items[i].setValue(parseInt(RadioString[3]) == i ? true : false);		
		}
		else {
			this.findById('swENSEW_VolFormAbdomPar1').items.items[0].setValue(true);		
			for (var i = 1; i < this.findById('swENSEW_VolFormAbdomPar1').items.items.length; i++  ) this.findById('swENSEW_VolFormAbdomPar1').items.items[i].setValue(false);						
			this.findById('swENSEW_VolFormAbdomPar2').items.items[0].setValue(true);		
			for (var i = 1; i < this.findById('swENSEW_VolFormAbdomPar2').items.items.length; i++  ) this.findById('swENSEW_VolFormAbdomPar2').items.items[i].setValue(false);						
			this.findById('swENSEW_VolFormAbdomPar3').items.items[0].setValue(true);		
			for (var i = 1; i < this.findById('swENSEW_VolFormAbdomPar3').items.items.length; i++  ) this.findById('swENSEW_VolFormAbdomPar3').items.items[i].setValue(false);						
			this.findById('swENSEW_VolFormAbdomPar4').items.items[0].setValue(true);		
			for (var i = 1; i < this.findById('swENSEW_VolFormAbdomPar4').items.items.length; i++  ) this.findById('swENSEW_VolFormAbdomPar4').items.items[i].setValue(false);						
		}
		this.findById('swENSEW_VolFormAbdomLocTxt').setValue(!Ext.isEmpty(NeonatalSurveyParam.VolFormAbdomLocTxt) ? NeonatalSurveyParam.VolFormAbdomLocTxt : '');// Объемные образования брюшной полости  - локализация – вариант пользователя
		this.findById('swENSEW_VolFormAbdomDensTxt').setValue(!Ext.isEmpty(NeonatalSurveyParam.VolFormAbdomDensTxt) ? NeonatalSurveyParam.VolFormAbdomDensTxt : '');// Объемные образования брюшной полости - плотность - вариант пользователя
		this.findById('swENSEW_VolFormAbdomLocTxt').setDisabled(!this.findById('swENSEW_VolFormAbdom').getValue());  //BOB - 06.05.2020
		this.findById('swENSEW_VolFormAbdomDensTxt').setDisabled(!this.findById('swENSEW_VolFormAbdom').getValue());  //BOB - 06.05.2020
		this.findById('swENSEW_VolFormAbdomPar1').setDisabled(!this.findById('swENSEW_VolFormAbdom').getValue());  //BOB - 06.05.2020
		this.findById('swENSEW_VolFormAbdomPar2').setDisabled(!this.findById('swENSEW_VolFormAbdom').getValue());  //BOB - 06.05.2020
		this.findById('swENSEW_VolFormAbdomPar3').setDisabled(!this.findById('swENSEW_VolFormAbdom').getValue());  //BOB - 06.05.2020
		this.findById('swENSEW_VolFormAbdomPar4').setDisabled(!this.findById('swENSEW_VolFormAbdom').getValue());  //BOB - 06.05.2020
		if (NeonatalSurveyParam.KidneyChang) {																												//Изменения почек
			RadioString = NeonatalSurveyParam.KidneyChang;
			for (var i = 0; i < this.findById('swENSEW_KidneyChang').items.items.length; i++  )
				this.findById('swENSEW_KidneyChang').items.items[i].setValue(parseInt(RadioString[0]) == i ? true : false);		
		}
		else { 
			this.findById('swENSEW_KidneyChang').items.items[0].setValue(true);
			for (var i = 1; i < this.findById('swENSEW_KidneyChang').items.items.length; i++  ) this.findById('swENSEW_KidneyChang').items.items[i].setValue(false);						
		}
		if (NeonatalSurveyParam.OvarianChang) {																												//Изменения яичников
			RadioString = NeonatalSurveyParam.OvarianChang;
			for (var i = 0; i < this.findById('swENSEW_OvarianChang').items.items.length; i++  )
				this.findById('swENSEW_OvarianChang').items.items[i].setValue(parseInt(RadioString[0]) == i ? true : false);		
		}
		else { 
			this.findById('swENSEW_OvarianChang').items.items[0].setValue(true);
			for (var i = 1; i < this.findById('swENSEW_OvarianChang').items.items.length; i++  ) this.findById('swENSEW_OvarianChang').items.items[i].setValue(false);						
		}
		this.findById('swENSEW_OvarianChangTxt').setValue(!Ext.isEmpty(NeonatalSurveyParam.OvarianChangTxt) ? NeonatalSurveyParam.OvarianChangTxt : '');// 	Изменения яичников – вариант пользователя
		if (NeonatalSurveyParam.HepaticChang) {																												//Изменения печени
			RadioString = NeonatalSurveyParam.HepaticChang;
			for (var i = 0; i < this.findById('swENSEW_HepaticChang').items.items.length; i++  )
				this.findById('swENSEW_HepaticChang').items.items[i].setValue(parseInt(RadioString[0]) == i ? true : false);		
		}
		else { 
			this.findById('swENSEW_HepaticChang').items.items[0].setValue(true);
			for (var i = 1; i < this.findById('swENSEW_HepaticChang').items.items.length; i++  ) this.findById('swENSEW_HepaticChang').items.items[i].setValue(false);						
		}
		this.findById('swENSEW_KidneysLowerPole').setValue((NeonatalSurveyParam.KidneysLowerPole)&&(NeonatalSurveyParam.KidneysLowerPole == 2) ? true : false);			//Нижний полюс обеих почек
		this.findById('swENSEW_DiastasRectAbdom').setValue((NeonatalSurveyParam.DiastasRectAbdom)&&(NeonatalSurveyParam.DiastasRectAbdom == 2) ? true : false);			//Диастаз прямой мышцы живота
		this.findById('swENSEW_ScaphoidAbdom').setValue((NeonatalSurveyParam.ScaphoidAbdom)&&(NeonatalSurveyParam.ScaphoidAbdom == 2) ? true : false);			//Ладьевидный живот
		this.findById('swENSEW_SaggyBellySyndr').setValue((NeonatalSurveyParam.SaggyBellySyndr)&&(NeonatalSurveyParam.SaggyBellySyndr == 2) ? true : false);			//Синдром отвисшего живота
		this.findById('swENSEW_OpenUrinaryDuct').setValue((NeonatalSurveyParam.OpenUrinaryDuct)&&(NeonatalSurveyParam.OpenUrinaryDuct == 2) ? true : false);			//Открытый мочевой проток
		if(this.FirstConditionLoad) this.findById('swENSEW_Abdominal_Panel').collapse();// скрываю при первой загрузке наблюдений, !!!!убрать если по умолчанию надо оставить открытыми
		//АНУС	
		if (NeonatalSurveyParam.Anus) {																												//Анальное отверстие
			RadioString = NeonatalSurveyParam.Anus;
			for (var i = 0; i < this.findById('swENSEW_Anus').items.items.length; i++  )
				this.findById('swENSEW_Anus').items.items[i].setValue(parseInt(RadioString[0]) == i ? true : false);		
		}
		else { 
			this.findById('swENSEW_Anus').items.items[0].setValue(true);
			for (var i = 1; i < this.findById('swENSEW_Anus').items.items.length; i++  ) this.findById('swENSEW_Anus').items.items[i].setValue(false);						
		}
		this.findById('swENSEW_TopicUsual').setValue((NeonatalSurveyParam.TopicUsual)&&(NeonatalSurveyParam.TopicUsual == 2) ? true : false);			//Топика обычная
		this.findById('swENSEW_Fistula').setValue((NeonatalSurveyParam.Fistula)&&(NeonatalSurveyParam.Fistula == 2) ? true : false);			//Свищ
		this.findById('swENSEW_Anus').setValue((NeonatalSurveyParam.Anus)&&(NeonatalSurveyParam.Anus == 2) ? true : false);			//Anus
		if(this.FirstConditionLoad) this.findById('swENSEW_Anus_Panel').collapse();// скрываю при первой загрузке наблюдений, !!!!убрать если по умолчанию надо оставить открытыми
		//СТУЛ
		this.findById('swENSEW_StoolHas').setValue((NeonatalSurveyParam.StoolHas)&&(NeonatalSurveyParam.StoolHas == 2) ? true : false);			//Стул имеется
		this.findById('swENSEW_StoolFrequency').setValue(!Ext.isEmpty(NeonatalSurveyParam.StoolFrequency) ? NeonatalSurveyParam.StoolFrequency : '');   //Частота cтула
		if (NeonatalSurveyParam.StoolNature) {																												//Характер стула
			RadioString = NeonatalSurveyParam.StoolNature;
			for (var i = 0; i < this.findById('swENSEW_StoolNature').items.items.length; i++  )
				this.findById('swENSEW_StoolNature').items.items[i].setValue(parseInt(RadioString[0]) == i ? true : false);		
		}
		else { 
			this.findById('swENSEW_StoolNature').items.items[0].setValue(true);
			for (var i = 1; i < this.findById('swENSEW_StoolNature').items.items.length; i++  ) this.findById('swENSEW_StoolNature').items.items[i].setValue(false);						
		}
		this.findById('swENSEW_StoolNatureTxt').setValue(!Ext.isEmpty(NeonatalSurveyParam.StoolNatureTxt) ? NeonatalSurveyParam.StoolNatureTxt : '');// 	Характер стула – вариант пользователя
		if (NeonatalSurveyParam.StoolColor) {																												//Цвет стула
			RadioString = NeonatalSurveyParam.StoolColor;
			for (var i = 0; i < this.findById('swENSEW_StoolColor').items.items.length; i++  )
				this.findById('swENSEW_StoolColor').items.items[i].setValue(parseInt(RadioString[0]) == i ? true : false);		
		}
		else { 
			this.findById('swENSEW_StoolColor').items.items[0].setValue(true);
			for (var i = 1; i < this.findById('swENSEW_StoolColor').items.items.length; i++  ) this.findById('swENSEW_StoolColor').items.items[i].setValue(false);						
		}
		this.findById('swENSEW_StoolColorTxt').setValue(!Ext.isEmpty(NeonatalSurveyParam.StoolColorTxt) ? NeonatalSurveyParam.StoolColorTxt : '');// 	Цвет стула – вариант пользователя
		value = 0;
		if (NeonatalSurveyParam.StoolImpurit) {
			RadioString = NeonatalSurveyParam.StoolImpurit;																							//Примеси стула
			for (var i = 0; i < this.findById('swENSEW_StoolImpurit1').items.items.length; i++  )
				this.findById('swENSEW_StoolImpurit1').items.items[i].setValue(parseInt(RadioString[0]) == i ? true : false);	
			value = parseInt(RadioString[0]);		
			for (var i = 0; i < this.findById('swENSEW_StoolImpurit2').items.items.length; i++  )
				this.findById('swENSEW_StoolImpurit2').items.items[i].setValue(parseInt(RadioString[1]) == i ? true : false);		
		}
		else {
			this.findById('swENSEW_StoolImpurit1').items.items[0].setValue(true);		
			for (var i = 1; i < this.findById('swENSEW_StoolImpurit1').items.items.length; i++  ) this.findById('swENSEW_StoolImpurit1').items.items[i].setValue(false);						
			this.findById('swENSEW_StoolImpurit2').items.items[0].setValue(true);		
			for (var i = 1; i < this.findById('swENSEW_StoolImpurit2').items.items.length; i++  ) this.findById('swENSEW_StoolImpurit2').items.items[i].setValue(false);						
		}
		this.findById('swENSEW_StoolImpurit2').disable();						
		if(value == 2)	this.findById('swENSEW_StoolImpurit2').enable();	
		this.findById('swENSEW_StoolImpuritTxt').setValue(!Ext.isEmpty(NeonatalSurveyParam.StoolImpuritTxt) ? NeonatalSurveyParam.StoolImpuritTxt : '');// 	Примеси стула – вариант пользователя
		if(this.FirstConditionLoad) this.findById('swENSEW_Stool_Panel').collapse();// скрываю при первой загрузке наблюдений, !!!!убрать если по умолчанию надо оставить открытыми
		//ПУПОК
		if (NeonatalSurveyParam.RemainUmbilCord) {																												//Остаток пуповины
			RadioString = NeonatalSurveyParam.RemainUmbilCord;
			for (var i = 0; i < this.findById('swENSEW_RemainUmbilCord').items.items.length; i++  )
				this.findById('swENSEW_RemainUmbilCord').items.items[i].setValue(parseInt(RadioString[0]) == i ? true : false);		
		}
		else { 
			this.findById('swENSEW_RemainUmbilCord').items.items[0].setValue(true);
			for (var i = 1; i < this.findById('swENSEW_RemainUmbilCord').items.items.length; i++  ) this.findById('swENSEW_RemainUmbilCord').items.items[i].setValue(false);						
		}
		this.findById('swENSEW_RemainUmbilCordTxt').setValue(!Ext.isEmpty(NeonatalSurveyParam.RemainUmbilCordTxt) ? NeonatalSurveyParam.RemainUmbilCordTxt : '');		//Остаток пуповины – вариант пользователя
		if (NeonatalSurveyParam.UmbilicWound) {																												//Пупочная ранка
			RadioString = NeonatalSurveyParam.UmbilicWound;
			for (var i = 0; i < this.findById('swENSEW_UmbilicWound').items.items.length; i++  )
				this.findById('swENSEW_UmbilicWound').items.items[i].setValue(parseInt(RadioString[0]) == i ? true : false);		
		}
		else { 
			this.findById('swENSEW_UmbilicWound').items.items[0].setValue(true);
			for (var i = 1; i < this.findById('swENSEW_UmbilicWound').items.items.length; i++  ) this.findById('swENSEW_UmbilicWound').items.items[i].setValue(false);						
		}
		this.findById('swENSEW_UmbilicWoundTxt').setValue(!Ext.isEmpty(NeonatalSurveyParam.UmbilicWoundTxt) ? NeonatalSurveyParam.UmbilicWoundTxt : '');		//Пупочная ранка – вариант пользователя
		this.findById('swENSEW_NavelAbnormal').setValue((NeonatalSurveyParam.NavelAbnormal)&&(NeonatalSurveyParam.NavelAbnormal == 2) ? true : false);			//Аномалии строения пупка
		this.findById('swENSEW_NavelRedness').setValue((NeonatalSurveyParam.NavelRedness)&&(NeonatalSurveyParam.NavelRedness == 2) ? true : false);			//Покраснение пупка
		this.findById('swENSEW_NavelSwell').setValue((NeonatalSurveyParam.NavelSwell)&&(NeonatalSurveyParam.NavelSwell == 2) ? true : false);			//Отек вокруг основания пуповины / пупочной ранки
		if (NeonatalSurveyParam.NavelDischarg) {																												//Выделения из пупка
			RadioString = NeonatalSurveyParam.NavelDischarg;
			for (var i = 0; i < this.findById('swENSEW_NavelDischarg').items.items.length; i++  )
				this.findById('swENSEW_NavelDischarg').items.items[i].setValue(parseInt(RadioString[0]) == i ? true : false);		
		}
		else { 
			this.findById('swENSEW_NavelDischarg').items.items[0].setValue(true);
			for (var i = 1; i < this.findById('swENSEW_NavelDischarg').items.items.length; i++  ) this.findById('swENSEW_NavelDischarg').items.items[i].setValue(false);						
		}
		if(this.FirstConditionLoad) this.findById('swENSEW_Navel_Panel').collapse();// скрываю при первой загрузке наблюдений, !!!!убрать если по умолчанию надо оставить открытыми
		//НАРУЖНЫЕ ПОЛОВЫЕ ОРГАНЫ
		if (NeonatalSurveyParam.gender) {																												//Пол
			RadioString = NeonatalSurveyParam.gender;
			for (var i = 0; i < this.findById('swENSEW_gender').items.items.length; i++  )
				this.findById('swENSEW_gender').items.items[i].setValue(parseInt(RadioString[0]) == i ? true : false);		
		}
		else { 
			this.findById('swENSEW_gender').items.items[0].setValue(true);
			for (var i = 1; i < this.findById('swENSEW_gender').items.items.length; i++  ) this.findById('swENSEW_gender').items.items[i].setValue(false);						
		}
		if (NeonatalSurveyParam.GenitalsViolations) {																												//Нарушения строения половых органов
			RadioString = NeonatalSurveyParam.GenitalsViolations;
			for (var i = 0; i < this.findById('swENSEW_GenitalsViolations').items.items.length; i++  )
				this.findById('swENSEW_GenitalsViolations').items.items[i].setValue(parseInt(RadioString[0]) == i ? true : false);		
		}
		else { 
			this.findById('swENSEW_GenitalsViolations').items.items[0].setValue(true);
			for (var i = 1; i < this.findById('swENSEW_GenitalsViolations').items.items.length; i++  ) this.findById('swENSEW_GenitalsViolations').items.items[i].setValue(false);						
		}
		this.findById('swENSEW_TesticlInScrotum').setValue((NeonatalSurveyParam.TesticlInScrotum)&&(NeonatalSurveyParam.TesticlInScrotum == 2) ? true : false);			//Яички в мошонке
		this.findById('swENSEW_Hydrocele').setValue((NeonatalSurveyParam.Hydrocele)&&(NeonatalSurveyParam.Hydrocele == 2) ? true : false);			//Гидроцеле
		this.findById('swENSEW_ScrotumHyperemia').setValue((NeonatalSurveyParam.ScrotumHyperemia)&&(NeonatalSurveyParam.ScrotumHyperemia == 2) ? true : false);			//Гиперемия мошонки
		this.findById('swENSEW_TesticlesTwisting').setValue((NeonatalSurveyParam.TesticlesTwisting)&&(NeonatalSurveyParam.TesticlesTwisting == 2) ? true : false);			//Перекрут яичек
		this.findById('swENSEW_FoldsWellDefin').setValue((NeonatalSurveyParam.FoldsWellDefin)&&(NeonatalSurveyParam.FoldsWellDefin == 2) ? true : false);			//Складки хорошо выражены
		this.findById('swENSEW_LabiaSwell').setValue((NeonatalSurveyParam.LabiaSwell)&&(NeonatalSurveyParam.LabiaSwell == 2) ? true : false);			//Отек половых губ
		this.findById('swENSEW_VaginalDischarge').setValue((NeonatalSurveyParam.VaginalDischarge)&&(NeonatalSurveyParam.VaginalDischarge == 2) ? true : false);			//Выделения из влагалища
		this.findById('swENSEW_Clitoromegaly').setValue((NeonatalSurveyParam.Clitoromegaly)&&(NeonatalSurveyParam.Clitoromegaly == 2) ? true : false);			//Клиторомегалия
		if(this.FirstConditionLoad) this.findById('swENSEW_gender_Panel').collapse();// скрываю при первой загрузке наблюдений, !!!!убрать если по умолчанию надо оставить открытыми
		//ДИУРКЗ
		if (NeonatalSurveyParam.Diuresis) {
			RadioString = NeonatalSurveyParam.Diuresis;																							//Мочеиспускание - Диурез
			for (var i = 0; i < this.findById('swENSEW_Diuresis1').items.items.length; i++  )
				this.findById('swENSEW_Diuresis1').items.items[i].setValue(parseInt(RadioString[0]) == i ? true : false);		
			for (var i = 0; i < this.findById('swENSEW_Diuresis2').items.items.length; i++  )
				this.findById('swENSEW_Diuresis2').items.items[i].setValue(parseInt(RadioString[1]) == i ? true : false);		
		}
		else {
			this.findById('swENSEW_Diuresis1').items.items[0].setValue(true);		
			for (var i = 1; i < this.findById('swENSEW_Diuresis1').items.items.length; i++  ) this.findById('swENSEW_Diuresis1').items.items[i].setValue(false);						
			this.findById('swENSEW_Diuresis2').items.items[0].setValue(true);		
			for (var i = 1; i < this.findById('swENSEW_Diuresis2').items.items.length; i++  ) this.findById('swENSEW_Diuresis2').items.items[i].setValue(false);						
		}
		this.findById('swENSEW_DiuresisVolume').setValue(!Ext.isEmpty(NeonatalSurveyParam.DiuresisVolume) ? NeonatalSurveyParam.DiuresisVolume : '');   //Темп диуреза
		this.findById('swENSEW_UrineTxt').setValue(!Ext.isEmpty(NeonatalSurveyParam.UrineTxt) ? NeonatalSurveyParam.UrineTxt : '');		//Моча - вариант пользователя
		if(this.FirstConditionLoad) this.findById('swENSEW_Diuresis_Panel').collapse();// скрываю при первой загрузке наблюдений, !!!!убрать если по умолчанию надо оставить открытыми
		//ЛИМФОУЗЛЫ
		this.findById('swENSEW_LymphNodePalp').setValue((NeonatalSurveyParam.LymphNodePalp)&&(NeonatalSurveyParam.LymphNodePalp == 2) ? true : false);			//Лимфоузлы - пальпация
		this.findById('swENSEW_LymphNodeLoc').setValue(!Ext.isEmpty(NeonatalSurveyParam.LymphNodeLoc) ? NeonatalSurveyParam.LymphNodeLoc : '');// Лимфоузлы - локализация
		this.findById('swENSEW_LymphNodeLoc').setDisabled(!this.findById('swENSEW_LymphNodePalp').getValue());  //BOB - 06.05.2020
		if(this.FirstConditionLoad) this.findById('swENSEW_LymphNode_Panel').collapse();// скрываю при первой загрузке наблюдений, !!!!убрать если по умолчанию надо оставить открытыми
		//КОНЕЧНОСТИ
		this.findById('swENSEW_Syndactyly').setValue((NeonatalSurveyParam.Syndactyly)&&(NeonatalSurveyParam.Syndactyly == 2) ? true : false);			//Синдактилия
		if (NeonatalSurveyParam.SyndactylyLoc) {
			RadioString = NeonatalSurveyParam.SyndactylyLoc;																			//Синдактилия - локализация
			for (var i = 0; i < this.findById('swENSEW_SyndactylyLoc').items.items.length; i++  )
				this.findById('swENSEW_SyndactylyLoc').items.items[i].setValue(parseInt(RadioString[0]) == i ? true : false);		
		}
		else { 
			this.findById('swENSEW_SyndactylyLoc').items.items[0].setValue(true);
			for (var i = 1; i < this.findById('swENSEW_SyndactylyLoc').items.items.length; i++  ) this.findById('swENSEW_SyndactylyLoc').items.items[i].setValue(false);						
		}
		this.findById('swENSEW_SyndactylyLoc').setDisabled(!this.findById('swENSEW_Syndactyly').getValue());  //BOB - 06.05.2020
		this.findById('swENSEW_Polydactyly').setValue((NeonatalSurveyParam.Polydactyly)&&(NeonatalSurveyParam.Polydactyly == 2) ? true : false);			//Полидактилия
		if (NeonatalSurveyParam.PolydactylyLoc) {
			RadioString = NeonatalSurveyParam.PolydactylyLoc;																			//Полидактилия - локализация
			for (var i = 0; i < this.findById('swENSEW_PolydactylyLoc').items.items.length; i++  )
				this.findById('swENSEW_PolydactylyLoc').items.items[i].setValue(parseInt(RadioString[0]) == i ? true : false);		
		}
		else { 
			this.findById('swENSEW_PolydactylyLoc').items.items[0].setValue(true);
			for (var i = 1; i < this.findById('swENSEW_PolydactylyLoc').items.items.length; i++  ) this.findById('swENSEW_PolydactylyLoc').items.items[i].setValue(false);						
		}
		this.findById('swENSEW_PolydactylyLoc').setDisabled(!this.findById('swENSEW_Polydactyly').getValue());  //BOB - 06.05.2020
		this.findById('swENSEW_Brachydactyly').setValue((NeonatalSurveyParam.Brachydactyly)&&(NeonatalSurveyParam.Brachydactyly == 2) ? true : false);			//Брахидактилия
		if (NeonatalSurveyParam.BrachydactylyLoc) {
			RadioString = NeonatalSurveyParam.BrachydactylyLoc;																			//Брахидактилия - локализация
			for (var i = 0; i < this.findById('swENSEW_BrachydactylyLoc').items.items.length; i++  )
				this.findById('swENSEW_BrachydactylyLoc').items.items[i].setValue(parseInt(RadioString[0]) == i ? true : false);		
		}
		else { 
			this.findById('swENSEW_BrachydactylyLoc').items.items[0].setValue(true);
			for (var i = 1; i < this.findById('swENSEW_BrachydactylyLoc').items.items.length; i++  ) this.findById('swENSEW_BrachydactylyLoc').items.items[i].setValue(false);						
		}
		this.findById('swENSEW_BrachydactylyLoc').setDisabled(!this.findById('swENSEW_Brachydactyly').getValue());  //BOB - 06.05.2020
		this.findById('swENSEW_Camptodactyly').setValue((NeonatalSurveyParam.Camptodactyly)&&(NeonatalSurveyParam.Camptodactyly == 2) ? true : false);			//Камптодактилия
		if (NeonatalSurveyParam.CamptodactylyLoc) {
			RadioString = NeonatalSurveyParam.CamptodactylyLoc;																			//Камптодактилия - локализация
			for (var i = 0; i < this.findById('swENSEW_CamptodactylyLoc').items.items.length; i++  )
				this.findById('swENSEW_CamptodactylyLoc').items.items[i].setValue(parseInt(RadioString[0]) == i ? true : false);		
		}
		else { 
			this.findById('swENSEW_CamptodactylyLoc').items.items[0].setValue(true);
			for (var i = 1; i < this.findById('swENSEW_CamptodactylyLoc').items.items.length; i++  ) this.findById('swENSEW_CamptodactylyLoc').items.items[i].setValue(false);						
		}
		this.findById('swENSEW_CamptodactylyLoc').setDisabled(!this.findById('swENSEW_Camptodactyly').getValue());  //BOB - 06.05.2020
		this.findById('swENSEW_Dolichostenomelia').setValue((NeonatalSurveyParam.Dolichostenomelia)&&(NeonatalSurveyParam.Dolichostenomelia == 2) ? true : false);			//Арахнодактилия
		if (NeonatalSurveyParam.DolichostenomeliaLoc) {
			RadioString = NeonatalSurveyParam.DolichostenomeliaLoc;																			//Арахнодактилия - локализация
			for (var i = 0; i < this.findById('swENSEW_DolichostenomeliaLoc').items.items.length; i++  )
				this.findById('swENSEW_DolichostenomeliaLoc').items.items[i].setValue(parseInt(RadioString[0]) == i ? true : false);		
		}
		else { 
			this.findById('swENSEW_DolichostenomeliaLoc').items.items[0].setValue(true);
			for (var i = 1; i < this.findById('swENSEW_DolichostenomeliaLoc').items.items.length; i++  ) this.findById('swENSEW_DolichostenomeliaLoc').items.items[i].setValue(false);						
		}
		this.findById('swENSEW_DolichostenomeliaLoc').setDisabled(!this.findById('swENSEW_Dolichostenomelia').getValue());  //BOB - 06.05.2020
		this.findById('swENSEW_MonkeyFold').setValue((NeonatalSurveyParam.MonkeyFold)&&(NeonatalSurveyParam.MonkeyFold == 2) ? true : false);			//Обезьянья складка
		if (NeonatalSurveyParam.MonkeyFoldLoc) {
			RadioString = NeonatalSurveyParam.MonkeyFoldLoc;																			//Обезьянья складка - локализация
			for (var i = 0; i < this.findById('swENSEW_MonkeyFoldLoc').items.items.length; i++  )
				this.findById('swENSEW_MonkeyFoldLoc').items.items[i].setValue(parseInt(RadioString[0]) == i ? true : false);		
		}
		else { 
			this.findById('swENSEW_MonkeyFoldLoc').items.items[0].setValue(true);
			for (var i = 1; i < this.findById('swENSEW_MonkeyFoldLoc').items.items.length; i++  ) this.findById('swENSEW_MonkeyFoldLoc').items.items[i].setValue(false);						
		}
		this.findById('swENSEW_MonkeyFoldLoc').setDisabled(!this.findById('swENSEW_MonkeyFold').getValue());  //BOB - 06.05.2020
		this.findById('swENSEW_Clinodactyly').setValue((NeonatalSurveyParam.Clinodactyly)&&(NeonatalSurveyParam.Clinodactyly == 2) ? true : false);			//Клинодактилия
		if (NeonatalSurveyParam.ClinodactylyLoc) {
			RadioString = NeonatalSurveyParam.ClinodactylyLoc;																			//Клинодактилия - локализация
			for (var i = 0; i < this.findById('swENSEW_ClinodactylyLoc').items.items.length; i++  )
				this.findById('swENSEW_ClinodactylyLoc').items.items[i].setValue(parseInt(RadioString[0]) == i ? true : false);		
		}
		else { 
			this.findById('swENSEW_ClinodactylyLoc').items.items[0].setValue(true);
			for (var i = 1; i < this.findById('swENSEW_ClinodactylyLoc').items.items.length; i++  ) this.findById('swENSEW_ClinodactylyLoc').items.items[i].setValue(false);						
		}
		this.findById('swENSEW_ClinodactylyLoc').setDisabled(!this.findById('swENSEW_Clinodactyly').getValue());  //BOB - 06.05.2020
		this.findById('swENSEW_Arthrogryposis').setValue((NeonatalSurveyParam.Arthrogryposis)&&(NeonatalSurveyParam.Arthrogryposis == 2) ? true : false);			//Артрогрипоз
		if (NeonatalSurveyParam.ArthrogryposisLoc) {
			RadioString = NeonatalSurveyParam.ArthrogryposisLoc;																			//Артрогрипоз - локализация
			for (var i = 0; i < this.findById('swENSEW_ArthrogryposisLoc').items.items.length; i++  )
				this.findById('swENSEW_ArthrogryposisLoc').items.items[i].setValue(parseInt(RadioString[0]) == i ? true : false);		
		}
		else { 
			this.findById('swENSEW_ArthrogryposisLoc').items.items[0].setValue(true);
			for (var i = 1; i < this.findById('swENSEW_ArthrogryposisLoc').items.items.length; i++  ) this.findById('swENSEW_ArthrogryposisLoc').items.items[i].setValue(false);						
		}
		this.findById('swENSEW_ArthrogryposisLoc').setDisabled(!this.findById('swENSEW_Arthrogryposis').getValue());  //BOB - 06.05.2020
		this.findById('swENSEW_EquinovarClubfoot').setValue((NeonatalSurveyParam.EquinovarClubfoot)&&(NeonatalSurveyParam.EquinovarClubfoot == 2) ? true : false);			//Эквиноварусная косолапость
		if (NeonatalSurveyParam.EquinovarClubfootLoc) {
			RadioString = NeonatalSurveyParam.EquinovarClubfootLoc;																			//Эквиноварусная косолапость - локализация
			for (var i = 0; i < this.findById('swENSEW_EquinovarClubfootLoc').items.items.length; i++  )
				this.findById('swENSEW_EquinovarClubfootLoc').items.items[i].setValue(parseInt(RadioString[0]) == i ? true : false);		
		}
		else { 
			this.findById('swENSEW_EquinovarClubfootLoc').items.items[0].setValue(true);
			for (var i = 1; i < this.findById('swENSEW_EquinovarClubfootLoc').items.items.length; i++  ) this.findById('swENSEW_EquinovarClubfootLoc').items.items[i].setValue(false);						
		}
		this.findById('swENSEW_EquinovarClubfootLoc').setDisabled(!this.findById('swENSEW_EquinovarClubfoot').getValue());  //BOB - 06.05.2020
		this.findById('swENSEW_VarusDeformitFoot').setValue((NeonatalSurveyParam.VarusDeformitFoot)&&(NeonatalSurveyParam.VarusDeformitFoot == 2) ? true : false);			//Варусная деформация стопы
		if (NeonatalSurveyParam.VarusDeformitFootLoc) {
			RadioString = NeonatalSurveyParam.VarusDeformitFootLoc;																			//Варусная деформация стопы - локализация
			for (var i = 0; i < this.findById('swENSEW_VarusDeformitFootLoc').items.items.length; i++  )
				this.findById('swENSEW_VarusDeformitFootLoc').items.items[i].setValue(parseInt(RadioString[0]) == i ? true : false);		
		}
		else { 
			this.findById('swENSEW_VarusDeformitFootLoc').items.items[0].setValue(true);
			for (var i = 1; i < this.findById('swENSEW_VarusDeformitFootLoc').items.items.length; i++  ) this.findById('swENSEW_VarusDeformitFootLoc').items.items[i].setValue(false);						
		}
		this.findById('swENSEW_VarusDeformitFootLoc').setDisabled(!this.findById('swENSEW_VarusDeformitFoot').getValue());  //BOB - 06.05.2020
		this.findById('swENSEW_RockingFoot').setValue((NeonatalSurveyParam.RockingFoot)&&(NeonatalSurveyParam.RockingFoot == 2) ? true : false);			//Стопа–качалка
		if (NeonatalSurveyParam.RockingFootLoc) {
			RadioString = NeonatalSurveyParam.RockingFootLoc;																			//Стопа–качалка - локализация
			for (var i = 0; i < this.findById('swENSEW_RockingFootLoc').items.items.length; i++  )
				this.findById('swENSEW_RockingFootLoc').items.items[i].setValue(parseInt(RadioString[0]) == i ? true : false);		
		}
		else { 
			this.findById('swENSEW_RockingFootLoc').items.items[0].setValue(true);
			for (var i = 1; i < this.findById('swENSEW_RockingFootLoc').items.items.length; i++  ) this.findById('swENSEW_RockingFootLoc').items.items[i].setValue(false);						
		}
		this.findById('swENSEW_RockingFootLoc').setDisabled(!this.findById('swENSEW_RockingFoot').getValue());  //BOB - 06.05.2020
		this.findById('swENSEW_TorsionTibia').setValue((NeonatalSurveyParam.TorsionTibia)&&(NeonatalSurveyParam.TorsionTibia == 2) ? true : false);			//Торсия большеберцовой кости
		if (NeonatalSurveyParam.TorsionTibiaLoc) {
			RadioString = NeonatalSurveyParam.TorsionTibiaLoc;																			//Торсия большеберцовой кости - локализация
			for (var i = 0; i < this.findById('swENSEW_TorsionTibiaLoc').items.items.length; i++  )
				this.findById('swENSEW_TorsionTibiaLoc').items.items[i].setValue(parseInt(RadioString[0]) == i ? true : false);		
		}
		else { 
			this.findById('swENSEW_TorsionTibiaLoc').items.items[0].setValue(true);
			for (var i = 1; i < this.findById('swENSEW_TorsionTibiaLoc').items.items.length; i++  ) this.findById('swENSEW_TorsionTibiaLoc').items.items[i].setValue(false);						
		}
		this.findById('swENSEW_TorsionTibiaLoc').setDisabled(!this.findById('swENSEW_TorsionTibia').getValue());  //BOB - 06.05.2020
		this.findById('swENSEW_KneeRecurvat').setValue((NeonatalSurveyParam.KneeRecurvat)&&(NeonatalSurveyParam.KneeRecurvat == 2) ? true : false);			//Рекурвация колена
		if (NeonatalSurveyParam.KneeRecurvatLoc) {
			RadioString = NeonatalSurveyParam.KneeRecurvatLoc;																			//Рекурвация колена - локализация
			for (var i = 0; i < this.findById('swENSEW_KneeRecurvatLoc').items.items.length; i++  )
				this.findById('swENSEW_KneeRecurvatLoc').items.items[i].setValue(parseInt(RadioString[0]) == i ? true : false);		
		}
		else { 
			this.findById('swENSEW_KneeRecurvatLoc').items.items[0].setValue(true);
			for (var i = 1; i < this.findById('swENSEW_KneeRecurvatLoc').items.items.length; i++  ) this.findById('swENSEW_KneeRecurvatLoc').items.items[i].setValue(false);						
		}
		this.findById('swENSEW_KneeRecurvatLoc').setDisabled(!this.findById('swENSEW_KneeRecurvat').getValue());  //BOB - 06.05.2020
		this.findById('swENSEW_Amputation').setValue((NeonatalSurveyParam.Amputation)&&(NeonatalSurveyParam.Amputation == 2) ? true : false);			//Ампутация верхней, нижней конечностей
		this.findById('swENSEW_AmputationTxt').setValue(!Ext.isEmpty(NeonatalSurveyParam.AmputationTxt) ? NeonatalSurveyParam.AmputationTxt : '');// Ампутация верхней, нижней конечностей - описание
		this.findById('swENSEW_AmputationLoc').setValue(!Ext.isEmpty(NeonatalSurveyParam.AmputationLoc) ? NeonatalSurveyParam.AmputationLoc : '');// Ампутация верхней, нижней конечностей - локализация
		this.findById('swENSEW_AmputationTxt').setDisabled(!this.findById('swENSEW_Amputation').getValue());  //BOB - 06.05.2020
		this.findById('swENSEW_AmputationLoc').setDisabled(!this.findById('swENSEW_Amputation').getValue());  //BOB - 06.05.2020
		this.findById('swENSEW_Macrodactyly').setValue((NeonatalSurveyParam.Macrodactyly)&&(NeonatalSurveyParam.Macrodactyly == 2) ? true : false);			//Макродактилия
		if(this.FirstConditionLoad) this.findById('swENSEW_Limbs_Panel').collapse();// скрываю при первой загрузке наблюдений, !!!!убрать если по умолчанию надо оставить открытыми
		//ТУЛОВИЩЕ И СПИНА
		this.findById('swENSEW_TorsoPigment').setValue((NeonatalSurveyParam.TorsoPigment)&&(NeonatalSurveyParam.TorsoPigment == 2) ? true : false);			//Туловище - пигментация
		this.findById('swENSEW_HairLowerBack').setValue((NeonatalSurveyParam.HairLowerBack)&&(NeonatalSurveyParam.HairLowerBack == 2) ? true : false);			//Оволосение на пояснице
		value = 0;
		if (NeonatalSurveyParam.SpinaBifida) {
			RadioString = NeonatalSurveyParam.SpinaBifida;																							//Spina bifida
			for (var i = 0; i < this.findById('swENSEW_SpinaBifida1').items.items.length; i++  )
				this.findById('swENSEW_SpinaBifida1').items.items[i].setValue(parseInt(RadioString[0]) == i ? true : false);	
			value = parseInt(RadioString[0]);		
			for (var i = 0; i < this.findById('swENSEW_SpinaBifida2').items.items.length; i++  )
				this.findById('swENSEW_SpinaBifida2').items.items[i].setValue(parseInt(RadioString[1]) == i ? true : false);		
		}
		else {
			this.findById('swENSEW_SpinaBifida1').items.items[0].setValue(true);		
			for (var i = 1; i < this.findById('swENSEW_SpinaBifida1').items.items.length; i++  ) this.findById('swENSEW_SpinaBifida1').items.items[i].setValue(false);						
			this.findById('swENSEW_SpinaBifida2').items.items[0].setValue(true);		
			for (var i = 1; i < this.findById('swENSEW_SpinaBifida2').items.items.length; i++  ) this.findById('swENSEW_SpinaBifida2').items.items[i].setValue(false);						
		}
		this.findById('swENSEW_SpinaBifida2').disable();						
		if(value == 2)	this.findById('swENSEW_SpinaBifida2').enable();	
		if(this.FirstConditionLoad) this.findById('swENSEW_Spina_Panel').collapse();// скрываю при первой загрузке наблюдений, !!!!убрать если по умолчанию надо оставить открытыми
		//БЁДРА
		this.findById('swENSEW_CaninHipDysplas').setValue((NeonatalSurveyParam.CaninHipDysplas)&&(NeonatalSurveyParam.CaninHipDysplas == 2) ? true : false);			//Дисплазия тазобедренного сустава
		if (NeonatalSurveyParam.CaninHipDysplasLoc) {
			RadioString = NeonatalSurveyParam.CaninHipDysplasLoc;																			//Дисплазия тазобедренного сустава - локализация
			for (var i = 0; i < this.findById('swENSEW_CaninHipDysplasLoc').items.items.length; i++  )
				this.findById('swENSEW_CaninHipDysplasLoc').items.items[i].setValue(parseInt(RadioString[0]) == i ? true : false);		
		}
		else { 
			this.findById('swENSEW_CaninHipDysplasLoc').items.items[0].setValue(true);
			for (var i = 1; i < this.findById('swENSEW_CaninHipDysplasLoc').items.items.length; i++  ) this.findById('swENSEW_CaninHipDysplasLoc').items.items[i].setValue(false);						
		}
		this.findById('swENSEW_CaninHipDysplasLoc').setDisabled(!this.findById('swENSEW_CaninHipDysplas').getValue());  //BOB - 06.05.2020
		this.findById('swENSEW_CongenHipDislocat').setValue((NeonatalSurveyParam.CongenHipDislocat)&&(NeonatalSurveyParam.CongenHipDislocat == 2) ? true : false);			//ТВрожденный вывих бедра
		if (NeonatalSurveyParam.CongenHipDislocatLoc) {
			RadioString = NeonatalSurveyParam.CongenHipDislocatLoc;																			//Врожденный вывих бедра - локализация
			for (var i = 0; i < this.findById('swENSEW_CongenHipDislocatLoc').items.items.length; i++  )
				this.findById('swENSEW_CongenHipDislocatLoc').items.items[i].setValue(parseInt(RadioString[0]) == i ? true : false);		
		}
		else { 
			this.findById('swENSEW_CongenHipDislocatLoc').items.items[0].setValue(true);
			for (var i = 1; i < this.findById('swENSEW_CongenHipDislocatLoc').items.items.length; i++  ) this.findById('swENSEW_CongenHipDislocatLoc').items.items[i].setValue(false);						
		}
		this.findById('swENSEW_CongenHipDislocatLoc').setDisabled(!this.findById('swENSEW_CongenHipDislocat').getValue());  //BOB - 06.05.2020
		this.findById('swENSEW_HemimelFibula').setValue((NeonatalSurveyParam.HemimelFibula)&&(NeonatalSurveyParam.HemimelFibula == 2) ? true : false);			//Гемимелия малоберцовой кости
		if (NeonatalSurveyParam.HemimelFibulaLoc) {
			RadioString = NeonatalSurveyParam.HemimelFibulaLoc;																			//Гемимелия малоберцовой кости - локализация
			for (var i = 0; i < this.findById('swENSEW_HemimelFibulaLoc').items.items.length; i++  )
				this.findById('swENSEW_HemimelFibulaLoc').items.items[i].setValue(parseInt(RadioString[0]) == i ? true : false);		
		}
		else { 
			this.findById('swENSEW_HemimelFibulaLoc').items.items[0].setValue(true);
			for (var i = 1; i < this.findById('swENSEW_HemimelFibulaLoc').items.items.length; i++  ) this.findById('swENSEW_HemimelFibulaLoc').items.items[i].setValue(false);						
		}
		this.findById('swENSEW_HemimelFibulaLoc').setDisabled(!this.findById('swENSEW_HemimelFibula').getValue());  //BOB - 06.05.2020
		this.findById('swENSEW_DeficProximFemur').setValue((NeonatalSurveyParam.DeficProximFemur)&&(NeonatalSurveyParam.DeficProximFemur == 2) ? true : false);			//Дефицит проксимального отдела бедра
		if (NeonatalSurveyParam.DeficProximFemurLoc) {
			RadioString = NeonatalSurveyParam.DeficProximFemurLoc;																			//Дефицит проксимального отдела бедра - локализация
			for (var i = 0; i < this.findById('swENSEW_DeficProximFemurLoc').items.items.length; i++  )
				this.findById('swENSEW_DeficProximFemurLoc').items.items[i].setValue(parseInt(RadioString[0]) == i ? true : false);		
		}
		else { 
			this.findById('swENSEW_DeficProximFemurLoc').items.items[0].setValue(true);
			for (var i = 1; i < this.findById('swENSEW_DeficProximFemurLoc').items.items.length; i++  ) this.findById('swENSEW_DeficProximFemurLoc').items.items[i].setValue(false);						
		}
		this.findById('swENSEW_DeficProximFemurLoc').setDisabled(!this.findById('swENSEW_DeficProximFemur').getValue());  //BOB - 06.05.2020
		this.findById('swENSEW_TibialHemimel').setValue((NeonatalSurveyParam.TibialHemimel)&&(NeonatalSurveyParam.TibialHemimel == 2) ? true : false);			//Тибиальная гемимелия
		if (NeonatalSurveyParam.TibialHemimelLoc) {
			RadioString = NeonatalSurveyParam.TibialHemimelLoc;																			//Тибиальная гемимелия - локализация
			for (var i = 0; i < this.findById('swENSEW_TibialHemimelLoc').items.items.length; i++  )
				this.findById('swENSEW_TibialHemimelLoc').items.items[i].setValue(parseInt(RadioString[0]) == i ? true : false);		
		}
		else { 
			this.findById('swENSEW_TibialHemimelLoc').items.items[0].setValue(true);
			for (var i = 1; i < this.findById('swENSEW_TibialHemimelLoc').items.items.length; i++  ) this.findById('swENSEW_TibialHemimelLoc').items.items[i].setValue(false);						
		}
		this.findById('swENSEW_TibialHemimelLoc').setDisabled(!this.findById('swENSEW_TibialHemimel').getValue());  //BOB - 06.05.2020
		this.findById('swENSEW_PostmedCurvat').setValue((NeonatalSurveyParam.PostmedCurvat)&&(NeonatalSurveyParam.PostmedCurvat == 2) ? true : false);			//Постмедиальное искревление
		if (NeonatalSurveyParam.PostmedCurvatLoc) {
			RadioString = NeonatalSurveyParam.PostmedCurvatLoc;																			//Постмедиальное искревление - локализация
			for (var i = 0; i < this.findById('swENSEW_PostmedCurvatLoc').items.items.length; i++  )
				this.findById('swENSEW_PostmedCurvatLoc').items.items[i].setValue(parseInt(RadioString[0]) == i ? true : false);		
		}
		else { 
			this.findById('swENSEW_PostmedCurvatLoc').items.items[0].setValue(true);
			for (var i = 1; i < this.findById('swENSEW_PostmedCurvatLoc').items.items.length; i++  ) this.findById('swENSEW_PostmedCurvatLoc').items.items[i].setValue(false);						
		}
		this.findById('swENSEW_PostmedCurvatLoc').setDisabled(!this.findById('swENSEW_PostmedCurvat').getValue());  //BOB - 06.05.2020
		this.findById('swENSEW_CongenDislocKnee').setValue((NeonatalSurveyParam.CongenDislocKnee)&&(NeonatalSurveyParam.CongenDislocKnee == 2) ? true : false);			//Врожденный вывих колена
		if (NeonatalSurveyParam.CongenDislocKneeLoc) {
			RadioString = NeonatalSurveyParam.CongenDislocKneeLoc;																			//Врожденный вывих колена - локализация
			for (var i = 0; i < this.findById('swENSEW_CongenDislocKneeLoc').items.items.length; i++  )
				this.findById('swENSEW_CongenDislocKneeLoc').items.items[i].setValue(parseInt(RadioString[0]) == i ? true : false);		
		}
		else { 
			this.findById('swENSEW_CongenDislocKneeLoc').items.items[0].setValue(true);
			for (var i = 1; i < this.findById('swENSEW_CongenDislocKneeLoc').items.items.length; i++  ) this.findById('swENSEW_CongenDislocKneeLoc').items.items[i].setValue(false);						
		}
		this.findById('swENSEW_CongenDislocKneeLoc').setDisabled(!this.findById('swENSEW_CongenDislocKnee').getValue());  //BOB - 06.05.2020
		this.findById('swENSEW_ConstrictSyndrom').setValue((NeonatalSurveyParam.ConstrictSyndrom)&&(NeonatalSurveyParam.ConstrictSyndrom == 2) ? true : false);			//Синдром констрикции перетяжками
		if (NeonatalSurveyParam.ConstrictSyndromLoc) {
			RadioString = NeonatalSurveyParam.ConstrictSyndromLoc;																			//Синдром констрикции перетяжками - локализация
			for (var i = 0; i < this.findById('swENSEW_ConstrictSyndromLoc').items.items.length; i++  )
				this.findById('swENSEW_ConstrictSyndromLoc').items.items[i].setValue(parseInt(RadioString[0]) == i ? true : false);		
		}
		else { 
			this.findById('swENSEW_ConstrictSyndromLoc').items.items[0].setValue(true);
			for (var i = 1; i < this.findById('swENSEW_ConstrictSyndromLoc').items.items.length; i++  ) this.findById('swENSEW_ConstrictSyndromLoc').items.items[i].setValue(false);						
		}
		this.findById('swENSEW_ConstrictSyndromLoc').setDisabled(!this.findById('swENSEW_ConstrictSyndrom').getValue());  //BOB - 06.05.2020
		if(this.FirstConditionLoad) this.findById('swENSEW_Hips_Panel').collapse();// скрываю при первой загрузке наблюдений, !!!!убрать если по умолчанию надо оставить открытыми
		//ПЕРЕЛОМЫ, ТРАВМЫ
		win.findById('swENSEW_Fracture_Panel').removeAll();
		for(var i in NeonatalTrauma){ 
			this.NeonatalTrauma_Build(NeonatalTrauma[i]);
		}
		win.findById('swENSEW_Fracture_Panel').doLayout();
		//Spina bifida
		value = 0;
		if (NeonatalSurveyParam.BrachPlexTrauma) {
			RadioString = NeonatalSurveyParam.BrachPlexTrauma;																							//Spina bifida
			for (var i = 0; i < this.findById('swENSEW_BrachPlexTrauma1').items.items.length; i++  )
				this.findById('swENSEW_BrachPlexTrauma1').items.items[i].setValue(parseInt(RadioString[0]) == i ? true : false);	
			value = parseInt(RadioString[0]);		
			for (var i = 0; i < this.findById('swENSEW_BrachPlexTrauma2').items.items.length; i++  )
				this.findById('swENSEW_BrachPlexTrauma2').items.items[i].setValue(parseInt(RadioString[1]) == i ? true : false);		
		}
		else {
			this.findById('swENSEW_BrachPlexTrauma1').items.items[0].setValue(true);		
			for (var i = 1; i < this.findById('swENSEW_BrachPlexTrauma1').items.items.length; i++  ) this.findById('swENSEW_BrachPlexTrauma1').items.items[i].setValue(false);						
			this.findById('swENSEW_BrachPlexTrauma2').items.items[0].setValue(true);		
			for (var i = 1; i < this.findById('swENSEW_BrachPlexTrauma2').items.items.length; i++  ) this.findById('swENSEW_BrachPlexTrauma2').items.items[i].setValue(false);						
		}
		this.findById('swENSEW_BrachPlexTrauma2').disable();						
		if((value == 1) || (value == 2))	this.findById('swENSEW_BrachPlexTrauma2').enable();	
		if(this.FirstConditionLoad) this.findById('swENSEW_Injuries_Panel').collapse();// скрываю при первой загрузке наблюдений, !!!!убрать если по умолчанию надо оставить открытыми
		//ИНФЕКЦИОННЫЙ ПРОЦЕСС
		this.findById('swENSEW_InfectProcess').setValue((NeonatalSurveyParam.InfectProcess)&&(NeonatalSurveyParam.InfectProcess == 2) ? true : false);			//Инфекционный процесс
		this.findById('swENSEW_InfectProcessEdema').setValue((NeonatalSurveyParam.InfectProcessEdema)&&(NeonatalSurveyParam.InfectProcessEdema == 2) ? true : false);			//Инфекционный процесс - отёк
		this.findById('swENSEW_PainPassivMove').setValue((NeonatalSurveyParam.PainPassivMove)&&(NeonatalSurveyParam.PainPassivMove == 2) ? true : false);			//Боль при пассивных движениях
		this.findById('swENSEW_RestrictMove').setValue((NeonatalSurveyParam.RestrictMove)&&(NeonatalSurveyParam.RestrictMove == 2) ? true : false);			//Ограничение в движении
		this.findById('swENSEW_AbnormPosturLimb').setValue((NeonatalSurveyParam.AbnormPosturLimb)&&(NeonatalSurveyParam.AbnormPosturLimb == 2) ? true : false);			//Аномальные позы конечностей
		if(this.FirstConditionLoad) this.findById('swENSEW_InfectProcess_Panel').collapse();// скрываю при первой загрузке наблюдений, !!!!убрать если по умолчанию надо оставить открытыми
		//STATUS LOCALIS
		this.findById('swENSEW_StatusLocalis').setValue(!Ext.isEmpty(NeonatalSurveyParam.StatusLocalis) ? NeonatalSurveyParam.StatusLocalis : '');// Status localis 
		if(this.FirstConditionLoad) this.findById('swENSEW_StatusLocalis_Panel').collapse();// скрываю при первой загрузке наблюдений, !!!!убрать если по умолчанию надо оставить открытыми
		//ЗАКЛЮЧЕНИЕ
		this.findById('swENSEW_Conclusion').setValue(EvnNeonatalSurvey['EvnNeonatalSurvey_Conclusion']);		//Заключение   
		if(this.FirstConditionLoad) this.findById('swENSEW_Conclusion_Panel').collapse();// скрываю при первой загрузке наблюдений, !!!!убрать если по умолчанию надо оставить открытыми


		this.EvnNeonatalSurvey_ButtonManag();

		this.FirstConditionLoad = false;
		this.fromObject.FirstConditionLoad = false;  //BOB - 08.04.2020
		this.changedDatas = false;
		//console.log('BOB_this.changedDatas-2=',this.changedDatas);

		if (win.ARMType == 'stas_pol')
			this.changedDatas = true;
	},	

	//управление видимостью и активностью элементов
	EvnNeonatalSurvey_ButtonManag: function() {
		var win = this;
		var EvnNeonatalSurvey = this.NeonatalSurvey_data.EvnNeonatalSurvey;

		//установка видимости реквизитов и предустановки в зависимости от стадии
		win.findById('swENSEW_ArriveFrom_panel').setVisible(false);  //"Поступил из"
		win.findById('swENSEW_EvnNeonatalSurvey_disDate_Pnl').setVisible(false);  //"дата завершения" 
		win.findById('swENSEW_EvnNeonatalSurvey_disTime_Pnl').setVisible(false);  //"время завершения" 

		switch (EvnNeonatalSurvey['ReanimStageType_id']){
			case 1:
				win.findById('swENSEW_ArriveFrom_panel').setVisible(true);  //"Поступил из"
				break;
			case 2:																		
				win.findById('swENSEW_EvnNeonatalSurvey_disDate_Pnl').setVisible(true);  //"дата завершения" 
				win.findById('swENSEW_EvnNeonatalSurvey_disTime_Pnl').setVisible(true);  //"время завершения" 
				break;																	
			case 3:
				break;																	
		}

		if (this.action == 'view'){
			//делаю неактивными поля, чекбоксы, кнопки и т.п.
			Ext.select('input, textarea, table', true, 'swENSEW_Form').each(function(el){
				var id = el.id; //выделяю параметр id из Ext.Element
				var object = win.findById(id);	//ищу в окне объект ExtJS
				if(object){ // если нахожу, то 
					object.setDisabled(true); // делаю Disabled /Enabled
				}
			});

			//радиогруппы делаю неактивными
			Ext.select('div[id^="x-form-el-swENSEW_"]', true, 'swENSEW_Base_Data').each(function(el){
				var id = el.id.replace('x-form-el-',''); //выделяю параметр id из Ext.Element
				var object = win.findById(id);	//ищу в окне объект ExtJS
				if(object){ // если нахожу, то 
					object.setDisabled(true);
				}
			});	
		}
		if (this.action != 'view' && this.ARMType == 'stas_pol'){
			//делаю неактивными поля, чекбоксы, кнопки и т.п.
			Ext.select('input, textarea, table', true, 'swENSEW_Form').each(function(el){
				var id = el.id; //выделяю параметр id из Ext.Element
				var object = win.findById(id);	//ищу в окне объект ExtJS
				if(object){ // если нахожу, то
					object.setDisabled(false); // делаю Disabled /Enabled
				}
			});

			//радиогруппы делаю неактивными
			Ext.select('div[id^="x-form-el-swENSEW_"]', true, 'swENSEW_Base_Data').each(function(el){
				var id = el.id.replace('x-form-el-',''); //выделяю параметр id из Ext.Element
				var object = win.findById(id);	//ищу в окне объект ExtJS
				if(object){ // если нахожу, то
					object.setDisabled(false);
				}
			});
		}
	},

	//сохранение регулярного наблюдения состояния младенца
	//!!!!!места, где используется исходное окно this.fromObject реализованы только для РЕАНИМАЦИОННОГО ПЕРИОДА!!!!!
	EvnNeonatalSurvey_Save: function(b,e){

		var win = this;
		var EvnNeonatalSurvey = this.NeonatalSurvey_data.EvnNeonatalSurvey;
	
		if (win.ARMType == 'stas_pol'){
			this.fromObject.ConditionGridLoadRawNum = this.fromObject.findById('ESEW_EvnNeonatalSurveyGrid').getGrid().getStore().find('EvnNeonatalSurvey_id',this.EvnNeonatalSurvey_id);
		}else {
			//индекс (номер) обрабатываемой записи в таблице наблюдений в окне РЕАНИМАЦИОННОГО ПЕРИОДА - это нужно для перестройки грда и установки выделения на нужной записи
			this.fromObject.ConditionGridLoadRawNum = this.fromObject.findById('swERPEW_EvnReanimatCondition_Grid').getStore().find('EvnReanimatCondition_id', EvnNeonatalSurvey.EvnNeonatalSurvey_id);
		}
		if (this.fromObject.ConditionGridLoadRawNum == -1)  this.fromObject.ConditionGridLoadRawNum = 0;


		if (win.ARMType != 'stas_pol') {
			//проверяю для РЕАНИМАЦИОННОГО ПЕРИОДА закрыт ли он
			if ((this.fromObject.id == 'swEvnReanimatPeriodEditWindow') && (!this.fromObject.ReanimatPeriod_isClosed) && (EvnNeonatalSurvey['ReanimStageType_id'] == 3)) {
				Ext.MessageBox.alert('Внимание!', 'Реанимационный период ещё не закрыт - <br> Переводной эпикриз сохранять нельзя! ');
				return false;
			}
		}
		
		var ErrMessag = '';
		if (Ext.isEmpty(EvnNeonatalSurvey['EvnNeonatalSurvey_setDate']))
			ErrMessag += 'дата начала наблюдения<br>';
		if (Ext.isEmpty(EvnNeonatalSurvey['EvnNeonatalSurvey_setTime']))
			ErrMessag += 'время начала наблюдения<br>';

		if (win.ARMType != 'stas_pol') {
			if (EvnNeonatalSurvey['ReanimStageType_id'] == 2) {
				if (Ext.isEmpty(EvnNeonatalSurvey['EvnNeonatalSurvey_disDate']))
					ErrMessag += 'дата окончания наблюдения<br>';
				if (Ext.isEmpty(EvnNeonatalSurvey['EvnNeonatalSurvey_disTime']))
					ErrMessag += 'время окончания наблюдения<br>';
			}
			if (Ext.isEmpty(EvnNeonatalSurvey['ReanimStageType_id']))
				ErrMessag += 'Этап - документ<br>';
			if (Ext.isEmpty(EvnNeonatalSurvey['ReanimConditionType_id']))
				ErrMessag += 'состояние<br>';

			if (EvnNeonatalSurvey['ReanimStageType_id'] == 1)
				if (Ext.isEmpty(EvnNeonatalSurvey['ReanimArriveFromType_id']))
					ErrMessag += 'поступил из...<br>';

			//Контроль даты по отношению к началу и концу РП
			//Начало периода
			var RP_setDT = this.fromObject.findById('swERPEW_EvnReanimatPeriod_setDate').getValue(); //  + ' ' +
			var Time = this.fromObject.findById('swERPEW_EvnReanimatPeriod_setTime').getValue();
			RP_setDT.setHours(parseInt(Time.substr(0, 2)), parseInt(Time.substr(3, 2)));
			//Конец периода
			var RP_disDT = '';
			if (this.fromObject.findById('swERPEW_EvnReanimatPeriod_disDate').getValue() != '') {
				RP_disDT = this.fromObject.findById('swERPEW_EvnReanimatPeriod_disDate').getValue(); //  + ' ' +
				Time = this.fromObject.findById('swERPEW_EvnReanimatPeriod_disTime').getValue();
				RP_disDT.setHours(parseInt(Time.substr(0, 2)), parseInt(Time.substr(3, 2)));
			}
		}
		var ERC_setDT = '';
		var ERC_disDT = '';
		//Дата начала наблюдения
		if (this.findById('swENSEW_EvnNeonatalSurvey_setDate').getValue() != ''){
			ERC_setDT = this.findById('swENSEW_EvnNeonatalSurvey_setDate').getValue();   //console.log('BOB_ERC_setDT_1=',ERC_setDT);
			Time = this.findById('swENSEW_EvnNeonatalSurvey_setTime').getValue();        //console.log('BOB_Time=',Time);
			ERC_setDT.setHours(parseInt(Time.substr(0, 2)), parseInt(Time.substr(3, 2)));	  //console.log('BOB_ERC_setDT_2=',ERC_setDT);
			if (win.ARMType != 'stas_pol') {
				//Начало периода
				if (ERC_setDT < RP_setDT)
					ErrMessag += 'дата начала наблюдения меньше даты начала Реанимационного периода<br>';
				//Конец периода
				if ((this.fromObject.findById('swERPEW_EvnReanimatPeriod_disDate').getValue() != '') && (EvnNeonatalSurvey['ReanimStageType_id'] != 3)) {
					if (ERC_setDT > RP_disDT)
						ErrMessag += 'дата начала наблюдения больше даты окончания Реанимационного периода<br>';
				}
				//сравнение начала периода наблюдения с окончанием предыдущего периода
				if (EvnNeonatalSurvey['ReanimStageType_id'] != 1) {
					if (!Ext.isEmpty(this.par_data.Previous_setDate) && !Ext.isEmpty(this.par_data.Previous_setTime)) {
						var date = this.par_data.Previous_setDate;
						var time = this.par_data.Previous_setTime;
						var PrevERC_disDT = new Date(parseInt(date.substr(6, 4)), parseInt(date.substr(3, 2)) - 1, parseInt(date.substr(0, 2)), parseInt(time.substr(0, 2)), parseInt(time.substr(3, 2)));
						if (ERC_setDT < PrevERC_disDT)
							ErrMessag += 'дата начала наблюдения меньше даты окончания предыдущего наблюдения<br>';
					}
				}
			}
		}
		//Дата окончания наблюдения
		if (this.findById('swENSEW_EvnNeonatalSurvey_disDate').getValue() != ''){
			ERC_disDT = this.findById('swENSEW_EvnNeonatalSurvey_disDate').getValue();  
			Time = this.findById('swENSEW_EvnNeonatalSurvey_disTime').getValue();	
			ERC_disDT.setHours(parseInt(Time.substr(0, 2)), parseInt(Time.substr(3, 2)));
			if (win.ARMType != 'stas_pol') {
				if (ERC_disDT < RP_setDT)
					ErrMessag += 'дата окончания наблюдения меньше даты начала Реанимационного периода<br>';
				//Конец периода
				if (this.fromObject.findById('swERPEW_EvnReanimatPeriod_disDate').getValue() != '') {
					if (ERC_disDT > RP_disDT)
						ErrMessag += 'дата окончания наблюдения больше даты окончания Реанимационного периода<br>';
				}
			}
		}
		if ((this.findById('swENSEW_EvnNeonatalSurvey_setDate').getValue() != '') && (this.findById('swENSEW_EvnNeonatalSurvey_disDate').getValue() != '')){
			if (ERC_setDT > ERC_disDT)
				ErrMessag += 'дата начала наблюдения больше даты окончания наблюдения<br>';				
		}

		if (ErrMessag == '') { //если сообщение о незаполненных реквизитах пустое
			var loadMask = new Ext.LoadMask(Ext.get('swENSEW_Form'), {msg: "Идёт сохранение..."});
			loadMask.show();
			// console.log('BOB_Save_EvnNeonatalSurvey.EvnNeonatalSurvey_setDate=',EvnNeonatalSurvey.EvnNeonatalSurvey_setDate); 
			// console.log('BOB_Save_EvnNeonatalSurvey.EvnNeonatalSurvey_disDate=',EvnNeonatalSurvey.EvnNeonatalSurvey_disDate); 

			//параметры регулярного наблюдения и общие параметры
			var data = 	{ 
				EvnNeonatalSurvey_id: EvnNeonatalSurvey.EvnNeonatalSurvey_id,    
				EvnNeonatalSurvey_pid: this.EvnNeonatalSurvey_pid,
				EvnNeonatalSurvey_rid: this.EvnNeonatalSurvey_rid,
				Lpu_id: this.par_data.Lpu_id,
				PersonEvn_id: this.pers_data.PersonEvn_id,
				Server_id: this.pers_data.Server_id,
				EvnNeonatalSurvey_setDate: EvnNeonatalSurvey.EvnNeonatalSurvey_setDate,
				EvnNeonatalSurvey_setTime: EvnNeonatalSurvey.EvnNeonatalSurvey_setTime,
				EvnNeonatalSurvey_disDate: EvnNeonatalSurvey.EvnNeonatalSurvey_disDate,
				EvnNeonatalSurvey_disTime: EvnNeonatalSurvey.EvnNeonatalSurvey_disTime,
				ReanimStageType_id: EvnNeonatalSurvey.ReanimStageType_id,
				ReanimConditionType_id: EvnNeonatalSurvey.ReanimConditionType_id,
				ReanimArriveFromType_id:	EvnNeonatalSurvey.ReanimArriveFromType_id,
				EvnNeonatalSurvey_Conclusion: EvnNeonatalSurvey.EvnNeonatalSurvey_Conclusion,
				EvnNeonatalSurvey_Doctor: EvnNeonatalSurvey.EvnNeonatalSurvey_Doctor								
			};
			// console.log('BOB_Save_data.EvnNeonatalSurvey_setDate=',data.EvnNeonatalSurvey_setDate); 
			// console.log('BOB_Save_data.EvnNeonatalSurvey_disDate=',data.EvnNeonatalSurvey_disDate); 


			//!!!!!стоит вырезать параметры - радиогруппы, если там одни нули.
			data['NeonatalSurveyParam'] = Ext.util.JSON.encode(this.NeonatalSurvey_data.NeonatalSurveyParam);	
			data['BreathAuscultative'] = Ext.util.JSON.encode(this.NeonatalSurvey_data.BreathAuscultative);	
			data['NeonatalTrauma'] = Ext.util.JSON.encode(this.NeonatalSurvey_data.NeonatalTrauma);	

			Ext.Ajax.request({
				showErrors: false,
				url: '/?c=EvnNeonatalSurvey&m=EvnNeonatalSurvey_Save',
				params: data,
				failure: function(response, options) {
					loadMask.hide();
					showSysMsg(langs('При сохранении данных Наблюдение состояния младенца произошла ошибка!'));
				},
				callback: function(opt, success, response) {
					if (success && response.responseText != '')
					{
						var SaveResponse = Ext.util.JSON.decode(response.responseText);
						console.log('BOB_SaveResponse=',SaveResponse); 

						if (SaveResponse['success'] == 'true'){
								win.changedDatas = false;
								EvnNeonatalSurvey.EvnNeonatalSurvey_id = SaveResponse['EvnNeonatalSurvey_id'];

								//АУСКУЛЬТАТИВНО
								//переформирую объект аускультативного дыхания, на случай сохранения новых, у которых ранее не было собственных id
								var BreathAuscultative = SaveResponse['BreathAuscultative'];
								win.NeonatalSurvey_data.BreathAuscultative = {};
								for(var i in BreathAuscultative){ 
									if (BreathAuscultative[i]['SideType_SysNick']) {
										win.NeonatalSurvey_data.BreathAuscultative[BreathAuscultative[i]['SideType_SysNick']] = BreathAuscultative[i];
									}
								}

								//ТРАВМА
								var NeonatalTrauma = SaveResponse['NeonatalTrauma'];
								win.NeonatalSurvey_data.NeonatalTrauma = {};
								win.findById('swENSEW_Fracture_Panel').removeAll();
								for(var i in NeonatalTrauma){ 
									if (typeof NeonatalTrauma[i] == 'object') {
										win.NeonatalSurvey_data.NeonatalTrauma[NeonatalTrauma[i]['NeonatalTrauma_id']] = NeonatalTrauma[i];
										win.NeonatalTrauma_Build(NeonatalTrauma[i]);
									} 
								}
								win.findById('swENSEW_Fracture_Panel').doLayout();

								//если из Реанимационного Периода - перезагрузка грида наблюдений в окне РЕАНИМАЦИОННОГО ПЕРИОДА
								if (win.fromObject.id == 'swEvnReanimatPeriodEditWindow'){
									win.fromObject.findById('swERPEW_EvnReanimatCondition_Grid').getStore().reload();
								}
								loadMask.hide();
							}
						else{
							loadMask.hide();
							Ext.MessageBox.alert('Ошибка сохранения!', SaveResponse['Error_Message']);
						}
	
					}
				},
			});
	
		}			
		else {
			//this.EvnReanimatCondition_ButtonManag(win,false);  //BOB - 11.02.2019
			ErrMessag = 'Отсутствуют или неверны следующие реквизиты регулярного наблюдения: <br><br>' + ErrMessag;
			Ext.MessageBox.alert('Внимание!', ErrMessag);
		}			
	},

	//загрузка антропометриченских данных 
	EvnNS_AntropometrLoud: function(Evn_disDate, Evn_disTime) {
		var win = this; 
		$.ajax({
			mode: "abort",
			type: "post",
			async: false,
			url: '/?c=EvnReanimatPeriod&m=getAntropometrData',
			data: { Person_id: this.pers_data.Person_id,
				    Evn_disDate: Ext.util.Format.date(Evn_disDate, 'd.m.Y'), 
					Evn_disTime: Evn_disTime},
			success: function(response) {
				var Antropometr = Ext.util.JSON.decode(response);
				//console.log('BOB_Antropometr=',Antropometr); 
				if (Antropometr['PersonHeight'].length == 1){
					win.findById('swENSEW_Height').setValue(Antropometr['PersonHeight'][0]['PersonHeight_Height'] + ' см');
					win.findById('swENSEW_Height').setFieldLabel('Рост на ' + Antropometr['PersonHeight'][0]['PersonHeight_setDate']);
				} else {
					win.findById('swENSEW_Height').setValue('');
					win.findById('swENSEW_Height').setFieldLabel('');
				}
				if (Antropometr['PersonWeight'].length == 1){
					win.findById('swENSEW_Weight').setValue( (parseFloat(Antropometr['PersonWeight'][0]['PersonWeight_Weight']) * 1000) + ' г');
					win.findById('swENSEW_Weight').setFieldLabel('Вес на ' + Antropometr['PersonWeight'][0]['PersonWeight_setDate']);
					//win.findById('swENSEW_IMT').setValue(Antropometr['PersonWeight'][0]['Weight_Index']);		 //закомментарено поскольку вроде не нужно, но вдруг понадобится			
				} else {
					win.findById('swENSEW_Weight').setValue('');
					win.findById('swENSEW_Weight').setFieldLabel('');
					//win.findById('swENSEW_IMT').setValue('');				 //закомментарено поскольку вроде не нужно, но вдруг понадобится						
				}
			}, 
			error: function() {
				alert("При обработке запроса на сервере произошла ошибка!");
			} 
		}); 
	},

	//добавление антропометриченских данных 
	EvnNS_AntropometrAdd: function(object, setDate) {
		var win = this; 
		var EvnNeonatalSurvey = this.NeonatalSurvey_data.EvnNeonatalSurvey;
		var params = new Object();
		params.action = "add";
		params.formParams = {
			PersonHeight_id: 0,
			Person_id: this.pers_data.Person_id,
			Server_id: this.pers_data.Server_id			
		};
		if(object == 'Height')
			params.formParams.PersonHeight_setDate = setDate;
		else{
			params.formParams.PersonWeight_setDate = setDate;
			params.Okei_InterNationSymbol = 'g'; //'kg';
		}		
		params.measureTypeExceptions = [ 1, 2 ];
		params.onHide = Ext.emptyFn;
		params.callback =  function(data) {
			//console.log('BOB_callback_data=',data ); 
			if (EvnNeonatalSurvey['ReanimStageType_id'] == 2) {
				win.EvnNS_AntropometrLoud(EvnNeonatalSurvey['EvnNeonatalSurvey_disDate'],EvnNeonatalSurvey['EvnNeonatalSurvey_disTime']);
			}
			else {
				win.EvnNS_AntropometrLoud(EvnNeonatalSurvey['EvnNeonatalSurvey_setDate'],EvnNeonatalSurvey['EvnNeonatalSurvey_setTime']);				
			}
		}.createDelegate(this);
		getWnd('swPerson'+object+'EditWindow').show(params);
	},

	//построение блока травмы (перелома) младенца
	NeonatalTrauma_Build(Trauma) {
	//	console.log('BOB_Trauma=',Trauma);

		var win = this;
		var NeonatalTrauma_id =  Trauma['NeonatalTrauma_id'];
		var RadioString = Trauma.NeonatalTrauma_Fracture;
		var Val1 = parseInt(RadioString[0]);
		var Val2 = parseInt(RadioString[1]);

		this.findById('swENSEW_Fracture_Panel').add(
			{
				id: 'swENSEW_Fracture_' + NeonatalTrauma_id + '_Panel',
				layout:'form',
				style: 'margin: 3px 0 0 0; padding: 0 0 3px 0; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
				items: [
					{									
						layout:'column',
						style: 'margin-bottom: 0px;',
						items:[
							{									
								layout:'form',
								labelWidth:1,
								style: 'margin-top: 4px;  margin-left: 4px; margin-bottom: 1px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
								items:[
									new Ext.form.RadioGroup({
										id:'swENSEW_Fracture_' + NeonatalTrauma_id + '_1',
										labelSeparator: '',
										vertical: true,
										FromInterface: true,
										NeonatalTrauma_id: NeonatalTrauma_id,
										columns: 4,
										items: [
											{boxLabel: '---', name: 'Fracture_' + NeonatalTrauma_id + '_1', inputValue: 0, width: 50, checked: (Val1 == 0)}, 
											{boxLabel: 'ключицы', name: 'Fracture_' + NeonatalTrauma_id + '_1', inputValue: 1, width: 130, checked: (Val1 == 1)}, 
											{boxLabel: 'плечевой кости', name: 'Fracture_' + NeonatalTrauma_id + '_1', inputValue: 2, width: 130, checked: (Val1 == 2)},
											{boxLabel: 'бедренной кости', name: 'Fracture_' + NeonatalTrauma_id + '_1', inputValue: 3, width: 130, checked: (Val1 == 3)}
										],
										listeners: {
											'change': function(field, checked) {
												if(field.FromInterface){
													win.findById('swENSEW_Fracture_' + field.NeonatalTrauma_id + '_Panel').change_handler(field, checked);
													win.changedDatas = true;
												} 
												field.FromInterface = true;
											}
										}
									}),
								]
							},
							{									
								layout:'form',
								labelWidth:1,
								style: 'margin-top: 4px;  margin-left: 4px; margin-bottom: 1px; border-top: 1px solid #99bbe8; border-left: 1px solid  #99bbe8; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; ',
								items:[
									new Ext.form.RadioGroup({
										id:'swENSEW_Fracture_' + NeonatalTrauma_id + '_2',
										labelSeparator: '',
										vertical: true,
										FromInterface: true,
										NeonatalTrauma_id: NeonatalTrauma_id,
										columns: 4,
										disabled: (Val1 == 0),
										items: [
											{boxLabel: '---', name: 'Fracture_' + NeonatalTrauma_id + '_2', inputValue: 0, width: 50, hidden: true, checked: (Val2 == 0)}, 
											{boxLabel: 'справа', name: 'Fracture_' + NeonatalTrauma_id + '_2', inputValue: 1, width: 70, checked: (Val2 == 1)}, 
											{boxLabel: 'слева', name: 'Fracture_' + NeonatalTrauma_id + '_2', inputValue: 2, width: 70, checked: (Val2 == 2)},
											{boxLabel: 'с обеих сторон', name: 'Fracture_' + NeonatalTrauma_id + '_2', inputValue: 3, width: 110, checked: (Val2 == 3)}
										],
										listeners: {
											'change': function(field, checked) {
												if(field.FromInterface){
													win.findById('swENSEW_Fracture_' + field.NeonatalTrauma_id + '_Panel').change_handler(field, checked);
													win.changedDatas = true;
												} 
												field.FromInterface = true;
											}
										}
									})	
								]
							},
							new Ext.Button({
								id: 'swENSEW_Fracture_Del_Button_' + NeonatalTrauma_id,
								iconCls: 'delete16',
								text: '',
								NeonatalTrauma_id: NeonatalTrauma_id,
								style: 'margin-top: 4px;  margin-left: 500px; margin-bottom: 0px;',
								handler: function(b,e)
								{
									this.NeonatalTrauma_del(b.NeonatalTrauma_id);
								}.createDelegate(this)
							})
						]
					},
					{									
						layout:'column',
						style: 'margin-bottom: 0px;',
						items:[
							//Ригидность мышц над ключицей
							{
								layout:'form',
								border: false,
								labelWidth: 210,
								items:[
									{
										fieldLabel: 'Ригидность мышц над ключицей',
										labelSeparator: '',
										name: 'NeonatalTrauma_IsRigidMusclClavicle_' + NeonatalTrauma_id,
										id: 'swENSEW_NeonatalTrauma_IsRigidMusclClavicle_' + NeonatalTrauma_id,
										xtype: 'checkbox',
										NeonatalTrauma_id: NeonatalTrauma_id,
										checked: (Trauma.NeonatalTrauma_IsRigidMusclClavicle == 2),
										listeners: {
											'check': function(field, checked ) {
												if ((win.NeonatalSurvey_data.NeonatalTrauma[field.NeonatalTrauma_id].NeonatalTrauma_IsRigidMusclClavicle) || (checked)) {
													win.NeonatalSurvey_data.NeonatalTrauma[field.NeonatalTrauma_id].NeonatalTrauma_IsRigidMusclClavicle = checked ? 2 : 1;									
													win.changedDatas = true;
												}
											}
										}		
									}
								]
							},
							//Потеря движения конечности
							{
								layout:'form',
								border: false,
								labelWidth: 190,
								items:[
									{
										fieldLabel: 'Потеря движения конечности',
										labelSeparator: '',
										name: 'NeonatalTrauma_IsLossLimbMov_' + NeonatalTrauma_id,
										id: 'swENSEW_NeonatalTrauma_IsLossLimbMov_' + NeonatalTrauma_id,
										xtype: 'checkbox',
										NeonatalTrauma_id: NeonatalTrauma_id,
										checked: (Trauma.NeonatalTrauma_IsLossLimbMov == 2),
										listeners: {
											'check': function(field, checked ) {
												if ((win.NeonatalSurvey_data.NeonatalTrauma[field.NeonatalTrauma_id].NeonatalTrauma_IsLossLimbMov) || (checked)) {
													win.NeonatalSurvey_data.NeonatalTrauma[field.NeonatalTrauma_id].NeonatalTrauma_IsLossLimbMov = checked ? 2 : 1;									
													win.changedDatas = true;
												}
											}
										}		
									}
								]
							},
							//Ограничение движений
							{
								layout:'form',
								border: false,
								labelWidth: 170,
								items:[
									{
										fieldLabel: 'Ограничение движений',
										labelSeparator: '',
										name: 'NeonatalTrauma_IsLimitMov_' + NeonatalTrauma_id,
										id: 'swENSEW_NeonatalTrauma_IsLimitMov_' + NeonatalTrauma_id,
										xtype: 'checkbox',
										NeonatalTrauma_id: NeonatalTrauma_id,
										checked: (Trauma.NeonatalTrauma_IsLimitMov == 2),
										listeners: {
											'check': function(field, checked ) {
												if ((win.NeonatalSurvey_data.NeonatalTrauma[field.NeonatalTrauma_id].NeonatalTrauma_IsLimitMov) || (checked)) {
													win.NeonatalSurvey_data.NeonatalTrauma[field.NeonatalTrauma_id].NeonatalTrauma_IsLimitMov = checked ? 2 : 1;									
													win.changedDatas = true;
												}
											}
										}		
									}
								]
							},
							//Крепитация в области перелома
							{
								layout:'form',
								border: false,
								labelWidth: 210,
								items:[
									{
										fieldLabel: 'Крепитация в области перелома',
										labelSeparator: '',
										name: 'NeonatalTrauma_IsCrepitation_' + NeonatalTrauma_id,
										id: 'swENSEW_NeonatalTrauma_IsCrepitation_' + NeonatalTrauma_id,
										xtype: 'checkbox',
										NeonatalTrauma_id: NeonatalTrauma_id,
										checked: (Trauma.NeonatalTrauma_IsCrepitation == 2),
										listeners: {
											'check': function(field, checked ) {
												if ((win.NeonatalSurvey_data.NeonatalTrauma[field.NeonatalTrauma_id].NeonatalTrauma_IsCrepitation) || (checked)) { 
													win.NeonatalSurvey_data.NeonatalTrauma[field.NeonatalTrauma_id].NeonatalTrauma_IsCrepitation = checked ? 2 : 1;									
													win.changedDatas = true;
												}
											}
										}		
									}
								]
							},
							//Боль
							{
								layout:'form',
								border: false,
								labelWidth: 80,
								items:[
									{
										fieldLabel: 'Боль',
										labelSeparator: '',
										name: 'NeonatalTrauma_IsPain_' + NeonatalTrauma_id,
										id: 'swENSEW_NeonatalTrauma_IsPain_' + NeonatalTrauma_id,
										xtype: 'checkbox',
										NeonatalTrauma_id: NeonatalTrauma_id,
										checked: (Trauma.NeonatalTrauma_IsPain == 2),
										listeners: {
											'check': function(field, checked ) {
												if ((win.NeonatalSurvey_data.NeonatalTrauma[field.NeonatalTrauma_id].NeonatalTrauma_IsPain) || (checked)) {
													win.NeonatalSurvey_data.NeonatalTrauma[field.NeonatalTrauma_id].NeonatalTrauma_IsPain = checked ? 2 : 1;									
													win.changedDatas = true;
												}
											}
										}		
									}
								]
							},
							//Псевдопаралич
							{
								layout:'form',
								border: false,
								labelWidth: 120,
								items:[
									{
										fieldLabel: 'Псевдопаралич',
										labelSeparator: '',
										name: 'NeonatalTrauma_IsPseudoParalys_' + NeonatalTrauma_id,
										id: 'swENSEW_NeonatalTrauma_IsPseudoParalys_' + NeonatalTrauma_id,
										xtype: 'checkbox',
										NeonatalTrauma_id: NeonatalTrauma_id,
										checked: (Trauma.NeonatalTrauma_IsPseudoParalys == 2),
										listeners: {
											'check': function(field, checked ) {
												if ((win.NeonatalSurvey_data.NeonatalTrauma[field.NeonatalTrauma_id].NeonatalTrauma_IsPseudoParalys) || (checked)) {
													win.NeonatalSurvey_data.NeonatalTrauma[field.NeonatalTrauma_id].NeonatalTrauma_IsPseudoParalys = checked ? 2 : 1;									
													win.changedDatas = true;
												}
											}
										}		
									}
								]
							}

						]
					}
				],
				change_handler: function(field, checked) {
					if(checked){
						var NeonatalTrauma_Fracture = (win.NeonatalSurvey_data.NeonatalTrauma[field.NeonatalTrauma_id].NeonatalTrauma_Fracture) ? win.NeonatalSurvey_data.NeonatalTrauma[field.NeonatalTrauma_id].NeonatalTrauma_Fracture : '00';
						var position = parseInt(field.id.substr(field.id.length - 1, 1));
						var value = checked.inputValue;
						//если меняется выбранный в первой группе то обнуляются вторая
						if (position == 1) {
							win.findById('swENSEW_Fracture_' + field.NeonatalTrauma_id + '_2').items.items[0].setValue(true);		
							for (var i = 1; i < win.findById('swENSEW_Fracture_' + field.NeonatalTrauma_id + '_2').items.items.length; i++  ) win.findById('swENSEW_Fracture_' + field.NeonatalTrauma_id + '_2').items.items[i].setValue(false);						
							win.findById('swENSEW_Fracture_' + field.NeonatalTrauma_id + '_2').disable();						
							if(value > 0)  win.findById('swENSEW_Fracture_' + field.NeonatalTrauma_id + '_2').enable();	
						}
						NeonatalTrauma_Fracture = NeonatalTrauma_Fracture.split("");
						NeonatalTrauma_Fracture[position - 1] = value;
						NeonatalTrauma_Fracture = NeonatalTrauma_Fracture.join("");
						if (!Ext.isEmpty(win.NeonatalSurvey_data.NeonatalTrauma[field.NeonatalTrauma_id].NeonatalTrauma_Fracture) || (NeonatalTrauma_Fracture != '00')) 
						win.NeonatalSurvey_data.NeonatalTrauma[field.NeonatalTrauma_id].NeonatalTrauma_Fracture = NeonatalTrauma_Fracture;									
						win.changedDatas = true;
					}
				}
			}
		);
	},

	//добапвление блока травмы (перелома) младенца
	NeonatalTrauma_add() {

		//объект Травма (перелом) NeonatalTrauma
		//формирую id для нового объекта
		var vNeonatalTrauma_id = 0;
		for (var i in this.NeonatalSurvey_data.NeonatalTrauma){
			if (parseInt(i) < parseInt(vNeonatalTrauma_id)) vNeonatalTrauma_id = i;
		}
		vNeonatalTrauma_id -= 1;
		//сам объект
		this.NeonatalSurvey_data.NeonatalTrauma[vNeonatalTrauma_id] = {
			NeonatalTrauma_id: vNeonatalTrauma_id,
			EvnNeonatalSurvey_id: this.EvnNeonatalSurvey_id,
			NeonatalTrauma_Fracture: "00",
			NeonatalTrauma_IsRigidMusclClavicle: 1,
			NeonatalTrauma_IsLossLimbMov: 1,
			NeonatalTrauma_IsLimitMov: 1,
			NeonatalTrauma_IsCrepitation: 1,
			NeonatalTrauma_IsPain: 1,
			NeonatalTrauma_IsPseudoParalys: 1,
			NT_RecordStatus: 0};

		this.NeonatalTrauma_Build(this.NeonatalSurvey_data.NeonatalTrauma[vNeonatalTrauma_id]);
		this.findById('swENSEW_Fracture_Panel').doLayout();
	},

	//удаление блока травмы (перелома) младенца
	NeonatalTrauma_del(NeonatalTrauma_id) {
		this.NeonatalSurvey_data.NeonatalTrauma[NeonatalTrauma_id]['NT_RecordStatus'] = 3;
		this.findById('swENSEW_Fracture_' + NeonatalTrauma_id + '_Panel').hide();
	},

	//внесение значений шкал в соответствующие поля, вызывается из окна РП при сохранении новых расчётов по шкалам
	EvnNeonatalSurvey_LoadScaleData: function(SysNick, Result) {
		this.NeonatalSurvey_data['NeonatalSurveyParam'][SysNick] = Result;		
		this.findById('swENSEW_' + SysNick).setValue(Result);

		if (SysNick == 'glasgow'){
			this.findById('swENSEW_Conscious').fireEvent('expand', this.findById('swENSEW_Conscious'));
			var ConsciousStore = this.findById('swENSEW_Conscious').getStore().data.items;
			var swENSEW_ConsciousValue = -1;
			for (var i in ConsciousStore){
				if (ConsciousStore[i].data.ConsciousType_ByGlasgow){
					var aGlasgowCodes = ConsciousStore[i].data.ConsciousType_ByGlasgow.split(",");
					if (aGlasgowCodes.indexOf(Result.toString()) != -1){
						swENSEW_ConsciousValue = ConsciousStore[i].data.ConsciousType_id;
						break;
					}
				}
			}
			if (swENSEW_ConsciousValue != -1){
				this.NeonatalSurvey_data['NeonatalSurveyParam']['ConsciousType_id'] = swENSEW_ConsciousValue;		
				this.findById('swENSEW_Conscious').setValue(swENSEW_ConsciousValue);
			}
		}
	},

	//установка аппарата и параметров ИВЛ по вызову из другого окна (РП) 
	EvnNeonatalSurvey_LoadIVLParams: function(IVL, RP_setDT, RP_disDT) {
		//Начало наблюдения
		var NS_setDT = this.findById('swENSEW_EvnNeonatalSurvey_setDate').getValue(); //  + ' ' + 
		var Time = this.findById('swENSEW_EvnNeonatalSurvey_setTime').getValue();		
		NS_setDT.setHours(parseInt(Time.substr(0, 2)), parseInt(Time.substr(3, 2)));
		//Конец наблюдения
		var NS_disDT = '';
		if ((this.findById('swENSEW_EvnNeonatalSurvey_disDate').getValue() != '') && (this.findById('swENSEW_EvnNeonatalSurvey_disTime').getValue() != '')){
			NS_disDT = this.findById('swENSEW_EvnNeonatalSurvey_disDate').getValue(); //  + ' ' + 
			Time = this.findById('swENSEW_EvnNeonatalSurvey_disTime').getValue();		
			NS_disDT.setHours(parseInt(Time.substr(0, 2)), parseInt(Time.substr(3, 2)));		
		}
		//периоды мероприятия ИВЛ и наблюдения должны пересекаться т.е.:
		//окончание ИВЛ д.б. позже начала наблюдения ИЛИ вообще отсутствовать (не закончилось) 
		//И окнчание наблюдения д.б. позже начала ИВЛ ИЛИ вообще отсутствовать (не закончилось)
		if (((RP_disDT > NS_setDT) || (RP_disDT == '')) && ((NS_disDT > RP_setDT) || (NS_disDT == ''))) {
			var IVLApparat = IVL['IVLParameter_Apparat'];
			var IVLParameter = this.EvnNeonatalSurvey_getIVLParams(IVL);

			this.NeonatalSurvey_data.NeonatalSurveyParam.Ventilator = IVLApparat;
			this.NeonatalSurvey_data.NeonatalSurveyParam.VentilatorParam = IVLParameter;
			this.findById('swENSEW_Ventilator').setValue(!Ext.isEmpty(this.NeonatalSurvey_data.NeonatalSurveyParam.Ventilator) ? this.NeonatalSurvey_data.NeonatalSurveyParam.Ventilator : '');// Аппарат ИВЛ
			this.findById('swENSEW_VentilatorParam').setValue(!Ext.isEmpty(this.NeonatalSurvey_data.NeonatalSurveyParam.VentilatorParam) ? this.NeonatalSurvey_data.NeonatalSurveyParam.VentilatorParam : '');// Параметры ИВЛ
		}
	},

	//возвращает строку параметров ИВЛ сформерованную из объекта ИВЛ
	EvnNeonatalSurvey_getIVLParams: function(IVL) {
		var IVLApparat = IVL['IVLParameter_Apparat'];
		var IVLParameter = IVL['IVLRegim_SysNick'].toUpperCase().replace('_', ' ');
		if (IVL['IVLParameter_FiO2'] > 0 ) IVLParameter += ', FiO2=' + IVL['IVLParameter_FiO2'] + '%';
		if (IVL['IVLParameter_PressInsp'] > 0 ) IVLParameter +=  (IVLApparat.indexOf('Avea') != -1 || IVLApparat.indexOf('Servo I') != -1) ? ', Pinsp=' + IVL['IVLParameter_PressInsp'] + 'см вд ст' : '';
		if (IVL['IVLParameter_PressSupp'] > 0 ) IVLParameter +=  (IVLApparat.indexOf('Avea') != -1 || IVLApparat.indexOf('Servo I') != -1) ? ', PSV=' + IVL['IVLParameter_PressSupp'] + 'см вд ст' : '';
		if (IVL['IVLParameter_FrequTotal'] > 0 ) IVLParameter +=  ((IVLApparat.indexOf('Infant Flow') != -1 || IVLApparat.indexOf('Servo I') != -1) ? ', Fr=' : IVLApparat.indexOf('Sensor Medics 3100A') != -1 ? ', Hz=' :'') +  
																	((IVLApparat.indexOf('Infant Flow') != -1 || IVLApparat.indexOf('Servo I') != -1 || IVLApparat.indexOf('Sensor Medics 3100A') != -1) ? IVL['IVLParameter_FrequTotal'] : '') + 
																	((IVLApparat.indexOf('Infant Flow') != -1 || IVLApparat.indexOf('Servo I') != -1) ? 'раз/мин' : IVLApparat.indexOf('Sensor Medics 3100A') != -1 ? 'Гц' :'');
		if (IVL['IVLParameter_VolTi'] > 0 ) IVLParameter +=   (IVLApparat.indexOf('Avea') != -1 || IVLApparat.indexOf('Servo I') != -1) ? ', Vti=' + IVL['IVLParameter_VolTi'] + 'мл' : '';
		if (IVL['IVLParameter_VolTe'] > 0 ) IVLParameter +=   (IVLApparat.indexOf('Avea') != -1 || IVLApparat.indexOf('Servo I') != -1) ? ', Vte=' + IVL['IVLParameter_VolTe'] + 'мл' : '';
		if (IVL['IVLParameter_Peak'] > 0 ) IVLParameter += (IVLApparat.indexOf('Avea') != -1 || IVLApparat.indexOf('Servo I') != -1) ? ', Peak=' + IVL['IVLParameter_Peak'] + 'см вд ст' : '';
		if (IVL['IVLParameter_PEEP'] > 0 ) IVLParameter += (IVLApparat.indexOf('Avea') != -1 || IVLApparat.indexOf('Servo I') != -1) ? ', PEEP=' + IVL['IVLParameter_PEEP'] + 'см вд ст' : '';
		if (IVL['IVLParameter_MAP'] > 0 ) IVLParameter +=  ((IVLApparat.indexOf('Avea') != -1 || IVLApparat.indexOf('Servo I') != -1) ? ', MAP=' : IVLApparat.indexOf('Sensor Medics 3100A') != -1 ? ', Paw=' :'') +  
																	((IVLApparat.indexOf('Avea') != -1 || IVLApparat.indexOf('Servo I') != -1 || IVLApparat.indexOf('Sensor Medics 3100A') != -1) ? IVL['IVLParameter_MAP'] + 'см вд ст' : '');
		if (IVL['IVLParameter_Tins'] != ".00") IVLParameter += (IVLApparat.indexOf('Avea') != -1 || IVLApparat.indexOf('Servo I') != -1) ? ', Tins=' + IVL['IVLParameter_Tins'] + 'сек' : '';
		if (IVL['IVLParameter_FlowMax'] != ".00") IVLParameter +=  ((IVLApparat.indexOf('Avea') != -1 || IVLApparat.indexOf('Servo I') != -1) ? ', Flow=' : IVLApparat.indexOf('Infant Flow') != -1 ? ', Flow max=' :'') +  
																	((IVLApparat.indexOf('Avea') != -1 || IVLApparat.indexOf('Servo I') != -1 || IVLApparat.indexOf('Infant Flow') != -1) ? IVL['IVLParameter_FlowMax'] + 'л/мин' : '');
		if (IVL['IVLParameter_FlowMin'] != ".00") IVLParameter += IVLApparat.indexOf('Infant Flow') != -1 ? ', Flow min=' +IVL['IVLParameter_FlowMin'] + 'л/мин'  :'';
		if (IVL['IVLParameter_deltaP']  > 0) IVLParameter += IVLApparat.indexOf('Sensor Medics 3100A') != -1 ? ', delta P=' +IVL['IVLParameter_deltaP'] + 'см вд ст'  :'';
		if (!Ext.isEmpty(IVL['IVLParameter_Other'])) IVLParameter += (IVLApparat.indexOf('Avea') != -1 || IVLApparat.indexOf('Servo I') != -1) ? ', иное-' + IVL['IVLParameter_Other'].replace(/:/g,'-').replace(/\'/g, '~') : '';
		return IVLParameter;

	},

	//печать документа поступление/дневник
	EvnNeonatalSurvey_Print: function(Doun) {
		
		//var EvnRCGridRowData = this.findById('swERPEW_EvnReanimatCondition_Grid').getSelectionModel().getSelected().data;  //выбранная строка в гриде событий регулярного наблюдения состояния

		if (Ext.isEmpty(this.NeonatalSurvey_data.EvnNeonatalSurvey.EvnNeonatalSurvey_id)) {
			Ext.MessageBox.alert('Внимание!', 'Наблюдение требуется сохранить перед печатью! ');
			return false;
		}
		var EvnNeonatalSurvey_id = this.NeonatalSurvey_data.EvnNeonatalSurvey.EvnNeonatalSurvey_id;

		//даты начала и конца
		var NoteDatetTime = this.NeonatalSurvey_data.EvnNeonatalSurvey.EvnNeonatalSurvey_setDate + ' ' + this.NeonatalSurvey_data.EvnNeonatalSurvey.EvnNeonatalSurvey_setTime; // датавремя
		var NoteDatetTimeEnd = this.NeonatalSurvey_data.EvnNeonatalSurvey.EvnNeonatalSurvey_disDate + ' ' + this.NeonatalSurvey_data.EvnNeonatalSurvey.EvnNeonatalSurvey_disTime;
		var NoteMedService_Name = Ext.isEmpty(this.par_data.MedService_Name) ? '' : this.par_data.MedService_Name; // наименование медслужбы
		
		var NoteDocName = this.findById('swENSEW_Print_Doctor_FIO').lastSelectionText;//this.NeonatalSurvey_data.EvnNeonatalSurvey.EvnNeonatalSurvey_Doctor;//доктор
		var ReanimStageType = this.NeonatalSurvey_data.EvnNeonatalSurvey.ReanimStageType_id; 
		
		var BeginDate = this.par_data.EvnParent_setDate;
		var EndDate = Ext.isEmpty(this.par_data.EvnParent_disDate) ? '' : this.par_data.EvnParent_disDate;
		var Section = this.par_data.swERPEW_LpuSection_Name;  
		//BOB - 27.09.2019
		var patient = '';
		if (this.findById('swENSEW_Print_Patient_FIO').getValue())
			patient = this.pers_data.Person_Surname + ' ' + (Ext.isEmpty(this.pers_data.Person_Firname) ? '' : this.pers_data.Person_Firname.substr(0, 1) + '.') + (Ext.isEmpty(this.pers_data.Person_Secname) ? '': this.pers_data.Person_Secname.substr(0, 1) + '.') + ' ' + Date.parse(this.pers_data.Person_Birthday.date.substr(0,10)).dateFormat('d.m.Y');
				
		//var fileName = EvnRCGridRowData['ReanimStageType_id'] === 1 ? 'ReanimatArriveMulty' : EvnRCGridRowData['ReanimStageType_id'] === 2 ? 'ReanimatNoteUnion' : 'ReanimatOutEpikrizMulty';		
		var fileName = 'NeonatalNote';	
		var url = ((getGlobalOptions().birtpath)?getGlobalOptions().birtpath:'') + '/run?__report=report/' + fileName + '.rptdesign';			
		
		//		var url = 'http://192.168.200.16/birt-viewer//run?__report=report/cathetPrrint.rptdesign';

		url += '&NoteDatetTime=' + NoteDatetTime;
		url += '&NoteDatetTimeEnd=' + NoteDatetTimeEnd;
		url += '&NoteMedService_Name=' + NoteMedService_Name;
		url += '&NoteDocName=' + NoteDocName;
		url += '&EvnReanimatCondition_id=' + EvnNeonatalSurvey_id;
		url += '&ReanimStageType=' + ReanimStageType;
		url += '&Doun=' + Doun;
		url += '&patient=' + patient;  //BOB - 27.09.2019
		switch (this.NeonatalSurvey_data.EvnNeonatalSurvey.ReanimStageType_id){
			case 1:
				url += '&NameDocument=ПЕРВИЧНЫЙ ОСМОТР'; //BOB - 22.04.2020
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



	// задержка
	sleep(ms) {
		//console.log('BOB_Date().getTime()=', new Date().getTime());
		ms += new Date().getTime();
		//console.log('BOB_ms=', ms);
		while (new Date() < ms){}
		//console.log('BOB_Date().getTime()_2=', new Date().getTime());	
	},


});
