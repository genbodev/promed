/**
 * Окно управления просмотра специфики регистра Реабилитации
 * пользовательсякая часть
 *
 *
 * @package      Reab
 * @access       All
 * @autor
 * @version      17.02.2017
 */
sw.Promed.ufa_personReabRegistryWindow = Ext.extend(sw.Promed.BaseForm,
		{
			title: lang['registr_reability'],
			bodyStyle: 'padding:5px; border: 0px;',
			maximized: true,
			robot: true,
			isUserClick: true, //Кликаем по полям с помощью мыши или таба
			listIDSfocus: [],
			isButtonAdd: false, //Кнопка "Добавить" нажата
			isButtonEdit: false, //Кнопка "Изменить" нажата
			clickToPN: 0,
			Templ: '', //Имя шаблона
			TemplObject: '', //Шаблон
			HeadAnketa: [], //шапка анкет
			ObjScale: '', //Структура шкалы
			DirectType_id: 0,
			StageType_id: 0,
			postfixQuestions: [],
			closable: true,
			editableForm: true,
			closeAction: 'hide',
			DirectSysNick: '',
			clickToRow: false,
			Periods: [],
			PrefixQuest: 0,
			Reverse: false,
			modal: true,
			Lpu_id: false,
			newAnkets: false,
			newICF: false,
			// frame: true,
			open1: true, //Первое открытие окна
			buttonAlign: "left",
			objectName: 'ufa_personReabRegistryWindow',
			id: 'ufa_personReabRegistryWindow',
			objectSrc: '/jscore/Forms/Admin/Ufa/ufa_personReabRegistryWindow.js',
			button1Callback: Ext.emptyFn,
			button2Callback: Ext.emptyFn,
			button3Callback: Ext.emptyFn,
			button4Callback: Ext.emptyFn,
			button5Callback: Ext.emptyFn,
			button1OnHide: Ext.emptyFn,
			button2OnHide: Ext.emptyFn,
			button3OnHide: Ext.emptyFn,
			button4OnHide: Ext.emptyFn,
			button5OnHide: Ext.emptyFn,
			collectAdditionalParams: Ext.emptyFn,
			anthropometry: {
				//Для каждого предмета наблюдения: рост, вес, талия, имт
				84: [107, 108, 109, 110],
				88: [142, 143, 0, 172],
				89: [208, 209, 211, 210],
				50: [318, 319, 321, 320]
			},

			//Скрытие - отображение кнопок управления вкладкой "Сведения"
			hideShowButtons: function (tabid)
			{
				//console.log('tabid=', tabid);
				// Для Сведений
				var idsButtons = [
					'addReabDataButton',
					'saveReabDataButton',
					'editReabDataButton',
					'deleteReabDataButton',
					'printReabDataButton',
					'ViewReabDataButton',
					'prevButton',
					'nextButton'
				];

				var ScaleButtons = [
					'addReabScaleDataButton',
					'saveReabScaleDataButton',
					'editReabScaleDataButton',
					'deleteReabScaleDataButton',
					'printReabScaleButton'
				];
				var MKBButtons = [
					'addReabMkbButton'
				];

				if (tabid == 'infotabReab')
				{
					for (var k in idsButtons) {
						if (typeof idsButtons[k] == 'string') {
							Ext.getCmp(idsButtons[k]).show();
						}
					}
					this.calendar.show();
				} else
				{
					for (var k in idsButtons) {
						if (typeof idsButtons[k] == 'string') {
							Ext.getCmp(idsButtons[k]).hide();
						}
					}
					this.calendar.hide();
					Ext.getCmp('saveReabDataButton').setDisabled(true);
					this.AnketaDisabled(true);
				}

				if (tabid == 'scalesReab')
				{
					for (var j in ScaleButtons) {
						//console.log('tabid=',tabid);
						if (typeof ScaleButtons[j] == 'string' && typeof Ext.getCmp(ScaleButtons[j]) === 'object')
						{
							//console.log('ScaleButtons=', Ext.getCmp(ScaleButtons[j]));
							Ext.getCmp(ScaleButtons[j]).show();
						}
					}
				} else
				{
					for (var j in ScaleButtons)
					{
						if (typeof ScaleButtons[j] == 'string' && typeof Ext.getCmp(ScaleButtons[j]) === 'object')
						{
							//console.log('ScaleButtons=', Ext.getCmp(ScaleButtons[j]));
							Ext.getCmp(ScaleButtons[j]).hide();
						}
					}
				}

//				if (tabid == 'MKFReab')
//				{
//					for (var j in MKBButtons) {
//						if (typeof MKBButtons[j] == 'string' && typeof Ext.getCmp(MKBButtons[j]) === 'object')
//						{
//							Ext.getCmp(MKBButtons[j]).show();
//						}
//					}
//				} else
//				{
//					for (var j in MKBButtons) {
//						if (typeof MKBButtons[j] == 'string' && typeof Ext.getCmp(MKBButtons[j]) === 'object')
//						{
//							Ext.getCmp(MKBButtons[j]).hide();
//						}
//					}
//				}


				//Кнопка печати лекарственного лечения
//        if(tabid != 'drug'){
//            Ext.getCmp('printBskDrugsButton').hide();
//        }
//        else{
//            Ext.getCmp('printBskDrugsButton').show();
//        }

			},

			initComponent: function ()
			{
				this.printAnkets = new Ext.Window({
					id: 'printAnkets',
					DirectType_id: false,
					modal: true,
					//title: 'Анкета "Скрининг" регистра БСК',
					title: 'Что-то с анкетой',
					height: 720,
					width: 930,
					cloaseAction: 'close',
					bodyStyle: 'padding:10px;border:0px',
					html: 'ankets questions',
					autoScroll: true
				});

				this.GridReabObjects = new sw.Promed.ViewFrame({
					id: 'GridReabUser',
					hideHeaders: true,
					disabled: true,
					enableColumnHide: true,
					bbar: [],
					contextmenu: true,
					border: false,
					height: Ext.getBody().getHeight() * 0.897,
					object: 'GridReabUser',
					dataUrl: '/?c=Ufa_Reab_Register_User&m=getListObjectsCurrentUser',
					autoLoadData: false,
					focusOnFirstLoad: false,
					stringfields: [
						{name: 'ReabEvent_id', type: 'int', header: 'ID'},
						{name: 'StageType_id', type: 'int', hidden: true},
						{name: 'StageName', type: 'string', hidden: true},
						{name: 'OutCause_id', type: 'int', hidden: true},
						{name: 'OutCause_Name', type: 'string', hidden: true},
						{name: 'OutCause_Code', type: 'int', hidden: true},
						{name: 'MedPersonal_did', type: 'int', hidden: true},
						{name: 'Lpu_did', type: 'int', hidden: true},
						{name: 'Event_disDate', type: 'date', format: 'd.m.Y', hidden: true},
						{name: 'Event_updDT', type: 'date', format: 'd.m.Y', hidden: true},
						{name: 'Event_setDate', type: 'date', format: 'd.m.Y', hidden: true},
						{name: 'DirectType_SysNick', type: 'string', hidden: true},
						{name: 'DirectType_id', type: 'int', hidden: true},
						{name: 'DirectType_Name', header: 'Наименование', width: '250px', renderer: function (value) {
							if (value)
							{
								var value = value.toUpperCase();
							}
							var t_val = value.split(/\(/);
							var color = 'black';

							if (t_val.length > 2)
							{
								if (t_val[2] == 0)
								{
									color = 'green';
								}
//							if (t_val[2] == 2) {
//								color = 'blue';
//							}
//							if (t_val[2] == 3) {
//								color = 'red';
//							}
//							if (t_val[2] == 4) {
//								color = 'gray';
//							}

							}
							//console.log('color=', color);
							var textMenu = (typeof t_val[1] == 'undefined') ? '' : '<div style="padding:5px;font-size:12px; font-family:tahoma;  color:' + color + '!important;float:right;display:block">(' + t_val[1] + '</div>';
							//console.log('textMenu=', textMenu);
							return '<div title="' + value + '" style="padding:5px;font-size:12px; float:left;display:block">' + t_val[0] + '</div>' + textMenu;
						}}
					],

					actions: [
						{name: 'action_add', hidden: true, text: 'Создать', disabled: true},
						{name: 'action_edit', hidden: false, text: 'Отмена закрытия этапа', disabled: true, handler: function () {
							Ext.getCmp('GridReabUser').CancelCloseStage();
						}.createDelegate(this)},
						{name: 'action_delete', disabled: true, text: 'Закрытие этапа', iconCls: 'resetsearch16', handler: function () {
							Ext.getCmp('GridReabUser').CloseStage();
						}.createDelegate(this)},
						{name: 'action_view', hidden: true},
						{name: 'action_refresh', hidden: true},
						{name: 'action_print', hidden: true}
					],
					onLoadData: function () {

					},
					onDblClick: function () {
						return;
					},
					listeners: {
						'render': function () {
							this.getGrid().getTopToolbar().hidden = true;
						}
					},
					clickToRow: function () {
						// alert('tttt');
						var form = Ext.getCmp('ufa_personReabRegistryWindow');
						form.clickToPN += 1; // разобраться

						var ReabObject_id = this.getGrid().getSelectionModel().getSelected().data.ReabEvent_id;
					},

					// Закрытие этапа
					CloseStage: function ()
					{
						var Inparams = new Object();
						Inparams.Person_id = Ext.getCmp('ufa_personReabRegistryWindow').Person_id;
						Inparams.inp = 2;
						Inparams.DirectType_id = Ext.getCmp('GridReabUser').getGrid().getSelectionModel().getSelected().data.DirectType_id;
						Inparams.StageType_id = Ext.getCmp('GridReabUser').getGrid().getSelectionModel().getSelected().data.StageType_id;
						getWnd('swReabSelectWindow').show(Inparams);
					},

					CancelCloseStage: function ()
					{
						var Inparams = new Object();
						Inparams.Person_id = Ext.getCmp('ufa_personReabRegistryWindow').Person_id;
						Inparams.inp = 3;
						Inparams.DirectType_id = Ext.getCmp('GridReabUser').getGrid().getSelectionModel().getSelected().data.DirectType_id;
						Inparams.StageType_id = Ext.getCmp('GridReabUser').getGrid().getSelectionModel().getSelected().data.StageType_id;
						Inparams.OutCause_id = Ext.getCmp('GridReabUser').getGrid().getSelectionModel().getSelected().data.OutCause_id;
						Inparams.Event_disDate = Ext.getCmp('GridReabUser').getGrid().getSelectionModel().getSelected().data.Event_disDate;
						Inparams.ReabEvent_id = Ext.getCmp('GridReabUser').getGrid().getSelectionModel().getSelected().data.ReabEvent_id;
						getWnd('swReabSelectWindow').show(Inparams);
					}
				});

				// Событие - клик по меню профиля
				this.GridReabObjects.getGrid().on(
						'rowclick',
						function (grid, row)
						{

							var form = Ext.getCmp('ufa_personReabRegistryWindow');
							form.isButtonAdd = false;
							form.listIDSfocus = [];

							var directTypeSysNick = this.getSelectionModel().getSelected().get('DirectType_SysNick');
							var stageTypeId = this.getSelectionModel().getSelected().get('StageType_id');

							//console.log('form.clickToPN=', form.clickToPN);

							if (form.Templ == directTypeSysNick + stageTypeId && form.clickToPN > 0)
							{
								sw.swMsg.alert(lang['soobschenie'], 'Форма уже загружена!');
								return;
							} else
							{
								//Смена шаблона
								//При клике на предмет набьлюдения - необходимо активировать таб с анкетными даннными
								Ext.getCmp('tabpanelReab').setActiveTab(Ext.getCmp('infotabReab'));
								Ext.getCmp('informReab').removeAll();
								Ext.getCmp('DateScale_id').removeAll();
								Ext.getCmp('ViewScale_id').removeAll();

								Ext.getCmp('scaleReabCenterPanel').hide();
								Ext.getCmp('ReabScaleValue_id').setValue('');
								Ext.getCmp('ReabScaleValue').hide();
								Ext.getCmp('FillindScaleDateReab').setValue(new Date());
								Ext.getCmp('FillindScaleTimeReab').setValue('');

								form.Templ = directTypeSysNick + stageTypeId;
								form.DirectSysNick = this.getSelectionModel().getSelected().get('DirectType_SysNick');
								form.DirectType_id = this.getSelectionModel().getSelected().get('DirectType_id');
								form.StageType_id = this.getSelectionModel().getSelected().get('StageType_id');
								Ext.getCmp('ViewReabDataButton').setDisabled(false);

								Ext.getCmp('ufa_personReabRegistryWindow').newICF = false;

								//Работа с панелью "Оценка по МКФ"
								//Только для 2 и 3 этапа
//								if (form.StageType_id == 1)
//								{
//									Ext.getCmp('tabpanelReab').hideTabStripItem('ReabICFVal');
//								} else
//								{
//									Ext.getCmp('tabpanelReab').unhideTabStripItem('ReabICFVal');
////									Ext.getCmp('tabpanelReab').hideTabStripItem('ReabICFVal');
//								}
							}

							Ext.getCmp('tabpanelReab').hideTabStripItem('MeasurementsReab'); // закрываем панель измерений !!!!!!!!!!!!!!!!!!!!

							switch (directTypeSysNick)
							{
								case 'cnsReab':
									//1 этап Неврология
									var cNameScales = "'renkin','rivermid','glasgow','Ashworth','Hauser','МоСА','Depression_HADS','alarm_HADS','Berg','Frenchay','VAScale','MedResCouncil'," +
											"'Bartel','Vasserman','FIM','ARAT','dysarthria','rivermid_DAA','nihss'";
									form.getScalesName(cNameScales);
									form.workingPanels();
									Ext.getCmp('tabpanelReab').unhideTabStripItem('MeasurementsReab'); // открываем панель измерений !!!!!!!!!!!!!!!!!!!!
									/*                    if(sm1 == '1' )
									 //                    {
									 //                        form.workingPanels();
									 //                    };
									 //                    if(sm1 == '2')
									 //                    {
									 //                        form.workingPanels();
									 //                        Ext.getCmp('tabpanelReab').unhideTabStripItem('MeasurementsReab'); // открываем панель измерений !!!!!!!!!!!!!!!!!!!!
									 //
									 //                    };
									 //                    if(sm1 != '1' && sm1 != '2')
									 //                    {
									 //                        sw.swMsg.alert(lang['soobschenie'], 'Для данного профиля и этапа отсутствует шаблон параметров');
									 //                        Ext.getCmp('ufa_personReabRegistryWindow').noWorkingPanels();
									 };
									 */
									break;
								case   'travmReab':
									Ext.getCmp('ufa_personReabRegistryWindow').getScalesName("'Harris','Lequesne'");
									if (Ext.getCmp('ufa_personReabRegistryWindow').PersonInfoPanelReab.DataView.store.data.items[0].json.Person_Age < 18)
									{
										sw.swMsg.alert(lang['soobschenie'], 'Для данного возраста анкета не создается!');
										Ext.getCmp('ufa_personReabRegistryWindow').noWorkingPanels();
									} else
									{
										Ext.getCmp('ufa_personReabRegistryWindow').workingPanels();
									}

									/*                     if(sm1 == '1' )  //1 этап Травматология
									 //                     {
									 //
									 //                        if(Ext.getCmp('ufa_personReabRegistryWindow').personInfo.age < 18)
									 //                        {
									 //                            sw.swMsg.alert(lang['soobschenie'], 'Для данного возраста анкета не создается!');
									 //                            Ext.getCmp('ufa_personReabRegistryWindow').noWorkingPanels();
									 //                        }
									 //                        else
									 //                        {
									 //                            Ext.getCmp('ufa_personReabRegistryWindow').workingPanels();
									 //                        }
									 //                     }
									 //                    else //Не 1 этап Травматология
									 //                    {
									 //                        sw.swMsg.alert(lang['soobschenie'], 'Для данного профиля и этапа отсутствует шаблон параметров');
									 //                        Ext.getCmp('ufa_personReabRegistryWindow').noWorkingPanels();
									 //                    }
									 */
									break;
								case 'cardiologyReab' :
									form.getScalesName("'Depression_HADS','alarm_HADS','МоСА','GRACE','renkin','Killip'");
									form.workingPanels();
									/*                    if(sm1 == '1' )  //1 этап Травматология
									 //                     {
									 //                     form.workingPanels();
									 //                     }
									 //                     else
									 //                     {
									 //                         sw.swMsg.alert(lang['soobschenie'], 'Для данного профиля и этапа отсутствует шаблон параметров');
									 //                        Ext.getCmp('ufa_personReabRegistryWindow').noWorkingPanels();
									 //                     }
									 */
									//sw.swMsg.alert(lang['soobschenie'], 'Будем творить!!!!');
									break;
								default :
									sw.swMsg.alert(lang['soobschenie'], 'Для данного профиля и этапа отсутствует шаблон параметров');
									Ext.getCmp('ufa_personReabRegistryWindow').noWorkingPanels();
									break;
							}
							;

							//console.log('isSuperAdmin=', isSuperAdmin());

							if (Ext.getCmp('GridReabUser').ViewGridPanel.getStore().data.items[row].data.OutCause_Code == 0)
							{
								Ext.getCmp('GridReabUser').ViewActions.action_delete.setDisabled(false);
								Ext.getCmp('GridReabUser').ViewActions.action_edit.setDisabled(true); //Отмена закрытия этапа
							} else
							{
								Ext.getCmp('GridReabUser').ViewActions.action_delete.setDisabled(true);

								Ext.getCmp('GridReabUser').getGrid().getSelectionModel().getSelected().data.Event_disDate;
								var diff = Math.ceil((new Date().getTime() - Ext.getCmp('GridReabUser').getGrid().getSelectionModel().getSelected().data.Event_updDT.getTime()) / (1000 * 60 * 60 * 24)) - 1;
								if ((diff < 3 && Ext.getCmp('GridReabUser').getGrid().getSelectionModel().getSelected().data.MedPersonal_did == getGlobalOptions().medpersonal_id &&
										Ext.getCmp('GridReabUser').getGrid().getSelectionModel().getSelected().data.Lpu_did == getGlobalOptions().lpu_id) || isSuperAdmin())
								{
									Ext.getCmp('GridReabUser').ViewActions.action_edit.setDisabled(false); //Отмена закрытия этапа
								} else
								{
									Ext.getCmp('GridReabUser').ViewActions.action_edit.setDisabled(true); //Отмена закрытия этапа
								}
							}
							Ext.getCmp('GridReabUser').clickToRow();

						}
				);

				this.calendar = new sw.Promed.SwDateField(
						{
							fieldLabel: 'Дата вызова',
							id: 'calday',
							disabled: true,
							enableKeyEvents: true,
							plugins: [
								new Ext.ux.InputTextMask('99.99.9999', false)
							],
							xtype: 'swdatefield',
							format: 'd.m.Y',
							value: new Date(),
							listeners: {
								'keydown': function (inp, e) {
									if (e.getKey() == Ext.EventObject.ENTER) {
										e.stopEvent();
									}
								}.createDelegate(this),

								'select': function () {

								}.createDelegate(this)
							},
							/*
							 * Работа со стрелками на просмотр
							 */
							getPeriod: function () {
								var form = Ext.getCmp('ufa_personReabRegistryWindow');
								//Берем, что есть и определяем что включаем
								var yy1 = this.value;

								//console.log('form.HeadAnketa[0].ReabAnketa_Data=',form.HeadAnketa[0].ReabAnketa_Data);
								var nPeriod = 0;
								for (var k in form.HeadAnketa)
								{
									if (form.HeadAnketa[k].ReabAnketa_Data.date.substr(0, 10) == (yy1.substr(6, 4) + '-' + yy1.substr(3, 2) + '-' + yy1.substr(0, 2)))
									{
										nPeriod = k;
										break;
									}
								};

								if (nPeriod < form.HeadAnketa.length - 1)
								{
									//стрелка влево
									Ext.getCmp('prevButton').setDisabled(false);
								}
								else
								{
									//гасим стрелку
									Ext.getCmp('prevButton').setDisabled(true);
								};

								if (nPeriod > 0)
								{
									//стрелка вправо
									Ext.getCmp('nextButton').setDisabled(false);

								}
								else
								{
									//гасим стрелку
									Ext.getCmp('nextButton').setDisabled(true);
								}
								return;

							}
						});

				////////////////////////////

				//Меню шкал
				this.GridReabScales = new sw.Promed.ViewFrame({
					id: 'GridReabScales',
					hideHeaders: true,
					disabled: false,
					enableColumnHide: true,
					bbar: [],
					contextmenu: false,
					border: false,
					width: 250,
					height: Ext.getBody().getHeight() - 130,
					root: 'data', //Обертка ответа(формируется в контроллере)
					// dataUrl: '/?c=Ufa_Reab_Register_User&m=getListScales',
					autoLoadData: false,
					focusOnFirstLoad: false,
					stringfields: [
						{name: 'id', type: 'int', header: 'ID', key: true},
						{name: 'ScaleType_id', type: 'int', hidden: true},
						{name: 'ScaleType_SysNick', type: 'string', hidden: true},
						{name: 'ScaleType_Name', type: 'string', header: 'Наименование', width: '200px', hidden: false}
					],
					actions: [
					],
					onLoadData: function () {

					},
					onDblClick: function () {
						alert('pppppp');
						return;
					},
					listeners: {
						'render': function () {
							this.getGrid().getTopToolbar().hidden = true;
						}
					},

					clickToRow: function () {
						alert('ttttt');
						return;
					}
				});

				// Панель наименования Шкал
				this.scaleReabLeftPanel = new Ext.form.FormPanel({
					region: 'west',
					split: true,
					layout: 'border',
					// collapsible: true,
					border: true,
					id: 'scaleReabLeftPanel',
					style: 'margin: 6px 0px 0px 6px; background: #DFE8F6',
					width: 250,
					//height: 130,
					//autoHeight: true,
					height: Ext.getBody().getHeight() - 130,
					autoScroll: false,
					items: [
						{
							xtype: 'label',
							text: lang['List_Scales'],
							region: 'north',
							style: 'padding: 5px'
						},
						{
							xtype: 'panel',
							region: 'center',
							width: 250,
							items: [
								Ext.getCmp('ufa_personReabRegistryWindow').GridReabScales
							]
						}
					]
				});

				// Панель дерева дат заполнения шкал
				this.scaleReabCenterPanel = new Ext.form.FormPanel({
					region: 'center',
					layout: 'column',
					border: false,
					frame: false,
					//width: 180,
					//  width: Ext.getBody().getWidth()-650,
					height: Ext.getBody().getHeight() - 140,
					//height: 130,
					//autoHeight: true,
					//style: 'margin: 6px 0px 0px 6px; background: #DFE8F6',
					style: 'margin: 6px 0px 0px 6px;',
					items: [
						{
							xtype: 'panel',
							title: '',
							frame: false,
							border: false,
							id: 'DateScale_id',
							//bodyStyle: 'background: #DFE8F6;',
							layout: 'column',
							items: [

							]
						}
					]
				});

				// Дата заполнения шкалы
				// Формируем дату  и время анкетирования
				var FillindScaleDate = new sw.Promed.SwDateField({
					id: 'FillindScaleDateReab',
					labelField: 'Дата проведения',
					labelSeparator: ':',
					disabled: true,
					labelWidth: '50px',
					width: '100px',
					height: 30,
					plugins: [
						new Ext.ux.InputTextMask('99.99.9999', false)
					],
					xtype: 'swdatefield',
					format: 'd.m.Y',
					value: new Date(),
					maxValue: getGlobalOptions().date,
					listeners: {
						'change': function () {
							//    alert('Время');
							var form = Ext.getCmp('ufa_personReabRegistryWindow');
							// Удаленная дата
							if (this.getValue() == '') {
								form.showMsg('Введите дату заполнения шкалы!');
								this.setValue(new Date());
								return;
							}


							var diff = Math.ceil((new Date().getTime() - this.getValue().getTime()) / (1000 * 60 * 60 * 24)) - 1;

							if (this.getValue() > new Date)
							{
								form.showMsg('Недопустимо указывать дату позднее текущей!');
								this.setValue(new Date());
								return;
							} else if (diff > 30) {
								form.showMsg('Дата заполнения шкалы не может быть ранее 30 дней от текущей даты. Пожалуйста, проверьте указанную дату анкетирования.');
								this.setValue(new Date());
								return;
							}
						},
						'blur': function () {}
					}
				});
				//Итоговая оценка
				var ReabScaleValue = new Ext.form.FieldSet(
						{
							border: false,
							autoHeight: true,
							hidden: true,
							style: 'padding:0px;margin:0px;',
							labelWidth: 220,
							labelAlign: 'right',
							id: 'ReabScaleValue',
							items: [
								new Ext.form.TextField({
									allowBlank: true,
									disabled: true,
									style: 'font-style:normal;font-size:1.2em;color:blue;text-align: center;font-width: bold',
									fieldLabel: 'Итоговая оценка',
									id: 'ReabScaleValue_id',
									width: 40
								})
							]
						});

				//панель шкал
				this.scaleReabRightPanel = new Ext.form.FormPanel({
					//region: 'east',
					//layout: 'border',
					layout: 'column',
					border: false,
					frame: false,
					//  autoScroll: true,
					// height: Ext.getBody().getHeight()-140,
					// autoWidth: true,
					//  autoHeight: true,
					id: 'scaleReabRightPan',
					style: 'margin: 6px 0px 0px 6px;',
					items: [
						{
							title: 'Дата заполнения шкалы',
							region: 'north',
							id: 'ReabScale_Date',
							width: Ext.getBody().getWidth() - 650,
							height: 50,
							border: false,
							layout: 'column',
							bodyStyle: 'margin: 10px;',
							items: [
								FillindScaleDate,
								{
									xtype: 'panel',
									frame: false,
									border: false,
									style: 'margin: 0px 0px 0px 10px;',
									items: [
										{
											allowBlank: false,
											fieldLabel: 'время',
											disabled: true,
											id: 'FillindScaleTimeReab',
											plugins: [new Ext.ux.InputTextMask('99:99', true)],
											validateOnBlur: false,
											width: 60,
											xtype: 'swtimefield'
										}]
								},

								ReabScaleValue

							]
						},

						{
							xtype: 'panel',
							frame: false,
							border: false,
							autoScroll: true,
							//autoWidth: true,
							//autoHeight: true,
							width: Ext.getBody().getWidth() - 680,
							height: Ext.getBody().getHeight() - 340,
							id: 'ViewScale_id',
							//bodyStyle: 'background: #DFE8F6;',
							layout: 'column',
							items: [

							]
						}
					]
				});
				/////////////////////////////

				// Событие - клик по меню шкал
				this.GridReabScales.getGrid().on(
						'rowclick',
						function (grid, row) {
							// alert('Шкала');
							var form = Ext.getCmp('ufa_personReabRegistryWindow');
							var cSysNick = this.getSelectionModel().getSelected().get('ScaleType_SysNick');
							var cScaleName = this.getSelectionModel().getSelected().get('ScaleType_Name');
							//console.log('record=',this.getSelectionModel().getSelected());
							//console.log('sm=', cSysNick);
							Ext.getCmp('scaleReabRightPan').show();
							form.loadScale(cSysNick, cScaleName);
							Ext.getCmp('FillindScaleDateReab').setDisabled(true);
							Ext.getCmp('FillindScaleTimeReab').setDisabled(true);
							Ext.getCmp('FillindScaleDateReab').setValue(new Date());
							Ext.getCmp('FillindScaleTimeReab').setValue('');
							Ext.getCmp('saveReabScaleDataButton').setDisabled(true);
							Ext.getCmp('deleteReabScaleDataButton').setDisabled(true);
						}
				);

				//Панель с перс данными
				this.PersonInfoPanelReab = new sw.Promed.PersonInfoPanel({
					floatable: false,
					collapsed: true,
					region: 'north',
					title: lang['zagruzka'],
					plugins: [Ext.ux.PanelCollapsedTitle],
					titleCollapse: true,
					collapsible: true,
					id: 'ReabPersonInfoFrame'
				});

				// GRID's для оценки по МКФ

				Ext.apply(this, {
					layout: 'border',
					items: [

						this.PersonInfoPanelReab,
						{
							xtype: 'panel',
							collapsible: true,
							title: lang['Profil'],
							width: 253,
							region: 'west',
							bodyBorder: false,
							id: 'leftPanelmenuReab',
							border: false,
							items: [
								{
									xtype: 'panel',
									tbar: [
										{
											xtype: 'button',
											id: 'addReabObjectButton',
											text: 'Добавить',
											iconCls: 'add16',
											hidden: false,
											handler: function () {
												//alert('Добавляем');
												// проверить на закрытие этапа.Если нормально, то

												var Inparams = new Object();
												Inparams.Person_id = Ext.getCmp('ufa_personReabRegistryWindow').Person_id;
												Inparams.inp = 1;
												getWnd('swReabSelectWindow').show(Inparams);
											}
										},
										{
											xtype: 'button',
											id: 'CloseReabStageButton',
											style: 'position:relative;  left:40px ',
											text: 'Закрыть этап',
											iconCls: 'resetsearch16',
											hidden: true,
											handler: function () {
												var form = Ext.getCmp('ufa_personReabRegistryWindow');
												form.clickToPN = 0;
												if (Ext.getCmp('GridReabUser').getGrid().getSelectionModel().getSelected())
												{
													//alert('Будем передергивать');
													Ext.getCmp('GridReabUser').getGrid().fireEvent('rowclick', Ext.getCmp('GridReabUser').getGrid(), 0)
												}
											}
										}
									],
									items: [Ext.getCmp('ufa_personReabRegistryWindow').GridReabObjects]
								}],
							listeners: {
								/*'render': function() {
								 Ext.getCmp('ufa_personBskRegistryWindow').resizePanel();
								 Ext.getCmp('leftPanelmenu').setWidth(253);
								 }*/
							}
						},
						{
							xtype: 'tabpanel',
							id: 'tabpanelReab',
							plain: false,
							//layout: 'border',
							border: false,
							bodyBorder: false,
							autoScroll: true,
							// height: Ext.getBody().getHeight()-120,
							//height: Ext.getBody().getHeight()-500,
							activeTab: 0,
							//columnWidth : 1,
							region: 'center',
							tbar: [
								{
									xtype: 'button',
									text: 'Добавить',
									id: 'addReabDataButton',
									iconCls: 'add16',
									disabled: true,
									handler: function () {
										// alert('Будем добавлять');
										Ext.getCmp('ufa_personReabRegistryWindow').isButtonAdd = true;
										var form = Ext.getCmp('ufa_personReabRegistryWindow');
										form.PrefixQuest = 0;

										form.newAnkets = true;

										form.robot = false;
										form.editableForm = false;

										form.addRegistryData();

									}
								},
								{
									xtype: 'button',
									text: 'Сохранить',
									id: 'saveReabDataButton',
									iconCls: 'save16',
									disabled: true,
									handler: function () {
										var form = Ext.getCmp('ufa_personReabRegistryWindow');
										//Сформировать шаблон сохранения
										form.saveRegistryData();
									}
								},
								{
									xtype: 'button',
									text: 'Изменить',
									disabled: true,
									hidden: true,
									id: 'editReabDataButton',
									iconCls: 'edit16',
									handler: function () {
										//alert('Отработка');
										Ext.getCmp('ufa_personReabRegistryWindow').AnketUpdate();
									}
								},
								{
									xtype: 'button',
									text: 'Просмотр',
									disabled: true,
									hidden: true,
									id: 'ViewReabDataButton',
									iconCls: 'view16',
									handler: function () {
										//  alert('Просмотр дат анкет');
										var Inparams = new Object();
										Inparams.Title = 'Даты проведения анкетирования';
										Inparams.Person_id = Ext.getCmp('ufa_personReabRegistryWindow').Person_id;
										Inparams.DirectType_id = Ext.getCmp('ufa_personReabRegistryWindow').DirectType_id;
										getWnd('ufaViewReabDateWindow').show({
											callback1: function (data) {
												//Запуск обновления окна анкеты
												// console.log('data=',data);
												Ext.getCmp('ufa_personReabRegistryWindow').loadAnketaInDate(data.ReabAnketa_Data.substr(0, 10));
												this.hide();
											},
											Inparams: Inparams
										});
									}
								},
								{
									xtype: 'button',
									text: 'Удалить',
									disabled: true,
									id: 'deleteReabDataButton',
									iconCls: 'delete16',
									handler: function () {
										//   alert('все по новой');
									}
								},
								{
									xtype: 'button',
									text: 'Печать анкеты',
									disabled: true,
									id: 'printReabDataButton',
									iconCls: 'print16',
									hidden: true,
									handler: function () {

//                                            //На рабочем или тестовом
//                                            var object_value = null;
//                                            //var url = ((getGlobalOptions().birtpath != '/birt-viewer/') ? getGlobalOptions().birtpath : 'http://192.168.200.58:91:91/birt-viewer')  +'/run?__report=Report/';
//                                            //var url = 'http://192.168.200.58:91:91/birt-viewer/run?__report=Report/';
//
//                                            switch(Ext.getCmp('ufa_personBskRegistryWindow').MorbusType_id){
//                                                case 84 : var report = '/AnketBSKScreening.rptdesign'; break;
//                                                default : var report = '/AnketBSKScreening.rptdesign';
//                                            }
//
//                                            var paramStr = report +'&__format=pdf';
//
//                                            var url = ((getGlobalOptions().birtpath)?getGlobalOptions().birtpath:'')+'run?__report=report';
//
//                                            console.log(home);
//
//                                            var home =  window.location.host == '127.0.0.1:81' || window.location.host == '192.168.200.58:81' ? 'http://192.168.200.58:91' : '';
//                                            window.open(home+url+paramStr, '_blank');
									}
								},
								{
									xtype: 'button',
									text: 'Печать',
									disabled: false,
									id: 'printReabDrugsButton',
									iconCls: 'print16',
									hidden: true,
									handler: function () {
//                                            var id_salt = Math.random();
//                                            var win_id = 'print_registryerror' + Math.floor(id_salt * 10000);
//                                            window.open('/?c=ufa_BSK_Register_User&m=getDrugs&Person_id=' + Ext.getCmp('ufa_personBskRegistryWindow').Person_id, win_id);
//                                            //Что-то есть в оригинале
									}
								},
								{
									xtype: 'tbfill'
								},
								{
									text: 'Предыдущий',
									disabled: true,
									id: 'prevButton',
									xtype: 'button',
									iconCls: 'arrow-previous16',
									handler: function () {
										//  alert('Загрузка предыдущей анкеты');
										var form = Ext.getCmp('ufa_personReabRegistryWindow');
										form.isButtonEdit = false;
										form.isButtonAdd = false;
										Ext.getCmp('ufa_personReabRegistryWindow').loadAnketaInDate('<');
										// Ext.getCmp('tabpanelReab').activate('infotabReab');

									}.createDelegate(this)
								},
								Ext.getCmp('ufa_personReabRegistryWindow').calendar,
								{
									text: 'Следующий',
									id: 'nextButton',
									disabled: true,
									xtype: 'button',
									iconCls: 'arrow-next16',
									handler: function () {
										//alert('Вправо');
										var form = Ext.getCmp('ufa_personReabRegistryWindow');
										form.isButtonEdit = false;
										form.isButtonAdd = false;
										Ext.getCmp('ufa_personReabRegistryWindow').loadAnketaInDate('>');
									}.createDelegate(this)
								}
							],
							items: [
								{
									title: 'Сведения',
									xtype: 'panel',
									id: 'infotabReab',
									autoScroll: true,
									items: [
										{
											border: true,
											bodyBorder: true,
											id: 'informReab',
											style: 'background-color:#E3E3E3!important',
										}
									],
									listeners: {
										'activate': function (p) {
											//console.log('Сведения=', p);
											Ext.getCmp('ufa_personReabRegistryWindow').hideShowButtons(p.id);
											if (Ext.getCmp('ufa_personReabRegistryWindow').ARMType == 'spec_mz')
											{
												Ext.getCmp('addReabDataButton').setDisabled(true);
											} else
											{
												Ext.getCmp('addReabDataButton').setDisabled(false);
											}
											if (typeof Ext.getCmp('saveReabScaleDataButton') === 'object') {
												Ext.getCmp('saveReabScaleDataButton').setDisabled(true);
											}

										}
									}
								},
								{
									title: 'Оценка по МКФ',
									id: 'ReabICFVal',
									disabled: true,
									autoScroll: true,
									tbar: [
//										{
//											xtype: 'button',
//											text: 'Что-то такое',
//											//id: 'addReabMkbButton',
//											iconCls: 'print16',
//											disabled: false,
//										}
									],
									items: [
										{
											xtype: 'tabpanel',
											//id: 'tabpanelReab',
											plain: false,
											border: false,
											bodyBorder: false,
											//autoScroll: true,
											height: 'auto',
											activeTab: 0,
											items: [
												{
													title: 'Активность и участие',
													id: 'ReabICF_d_pan',
													xtype: 'panel',
													height: 'auto',
													autoScroll: true,
													items: [
														new sw.Promed.ViewFrame(
																{
																	actions: [
																		{name: 'action_add', handler: function () {
																			Ext.getCmp('ufa_personReabRegistryWindow').ICFVerdict('d', 'add');
																		}.createDelegate(this)},
																		{name: 'action_view', hidden: true},
																		{name: 'action_edit', disabled: true, handler: function () {
																			Ext.getCmp('ufa_personReabRegistryWindow').ICFVerdict('d', 'edit');
																		}.createDelegate(this)}, // Выход на списочную форму
																		{name: 'action_delete', disabled: true, handler: function () {
																			Ext.getCmp('ufa_personReabRegistryWindow').ICFVerdictDelete('d');
																		}.createDelegate(this)},
																		{name: 'action_refresh', hidden: false, handler: function () {
																			Ext.getCmp('ufa_personReabRegistryWindow').ICFRefresh('d',2);
																		}.createDelegate(this)},
																		{name: 'action_print', hidden: false, handler: function () {
																			Ext.getCmp('ufa_personReabRegistryWindow').ICFPrint('d');
																		}.createDelegate(this)}
																	],
																	autoExpandColumn: 'autoexpand',
																	//autoExpandMin: 100,
																	autoLoadData: false,
																	id: 'ReabICF_d',
																	width: Ext.getBody().getWidth() - 263,
																	height: Ext.getBody().getHeight() - 220,
																	paging: false, // навигатор
																	region: 'center',
																	dataUrl: '/?c=Ufa_Reab_Register_User&m=getListICF_Verdict',
																	stringfields: [
																		{name: 'ReabICFRating_id', type: 'int', header: 'ID'},
																		{name: 'ICFSetDate', type: 'date', dateFormat: 'd.m.Y', header: lang['data'], width: 80, align: 'center', vertical: 'middle', sortable: false, },
																		{name: 'ICF_Code', type: 'string', header: 'Код', align: 'center', vertical: 'middle', width: 80},
																		{name: 'ICF_Description', type: 'string', hidden: true},
																		{name: 'ICF_EvalRealiz_id', type: 'int', hidden: true},
																		{name: 'ICF_TargetRealiz_id', type: 'int', hidden: true},
																		{name: 'ICF_CapasitEval_id', type: 'int', hidden: true},
																		{name: 'ICF_TargetCapasit_id', type: 'int', hidden: true},
																		{name: 'ICF_Name', type: 'string', width: 300, vertical: 'middlee', header: '<div style="width:300px;text-align:center;">' + "Наименование" + '</div>'},
																		{name: 'ICF_EvalRealiz', type: 'int', width: 90, align: 'center', vertical: 'middlee', header: 'Оценка по <br>реализации'},
																		{name: 'ICF_TargetRealiz', type: 'int', width: 80, align: 'center', vertical: 'middlee', header: 'Цель по <br>реализации'},
																		{name: 'ICF_EvalCapasit', type: 'int', width: 80, align: 'center', vertical: 'middlee', header: 'Оценка по <br>капаситету'},
																		{name: 'ICF_TargetCapasit', type: 'int', width: 80, align: 'center', vertical: 'middlee', header: 'Цель по <br>капаситету'},
																		{name: 'MedPersonalPost', type: 'string', header: 'Профиль врача', align: 'center', width: 200},
																		{name: 'MedStaffFact_id', type: 'int', hidden: true},
																		{name: 'MedPersonalFIO', type: 'string', header: '<div style="width:200px;text-align:center;">' + "Ф.И.О" + '</div>', width: 200, id: 'autoexpand'}
																	],
																	totalProperty: 'totalCount',
																	focusOnFirstLoad: false,
																	toolbar: true,
																	onBeforeLoadData: function () {
																		//this.getButtonSearch().disable();
																	}.createDelegate(this),
																	onLoadData: function () {
																		// alert('Хрень');
																		//this.getButtonSearch().enable();
																	}.createDelegate(this),
																	onRowSelect: function (sm, index, record) {
																		//console.log('onRowSelect11=', record.data.ICF_Code);
																		if (record.data.ICF_Code != null)
																		{
																			Ext.getCmp('ReabICF_d').ViewActions.action_edit.setDisabled(false);
																			Ext.getCmp('ReabICF_d').ViewActions.action_delete.setDisabled(false);
																		}
																	}
																})
													],
													listeners: {
														'activate': function (p) {
															// Ext.getCmp('ufa_personReabRegistryWindow').hideShowButtons(p.id);
															Ext.getCmp('ReabICF_d_pan').doLayout();
															Ext.getCmp('ReabICF_d').getGrid().getSelectionModel().clearSelections();
														}
													}
												},
												{
													title: 'Функции организма',
													id: 'ReabICF_b_pan',
													xtype: 'panel',
													height: 'auto',
													autoScroll: true,
													items: [
														new sw.Promed.ViewFrame(
																{
																	actions: [
																		{name: 'action_add', handler: function () {
																			Ext.getCmp('ufa_personReabRegistryWindow').ICFVerdict('b', 'add');
																		}.createDelegate(this)},
																		{name: 'action_view', hidden: true},
																		{name: 'action_edit', disabled: true, handler: function () {
																			Ext.getCmp('ufa_personReabRegistryWindow').ICFVerdict('b', 'edit');
																		}.createDelegate(this)}, // Выход на списочную форму
																		{name: 'action_delete', disabled: true, handler: function () {
																			Ext.getCmp('ufa_personReabRegistryWindow').ICFVerdictDelete('b');
																		}.createDelegate(this)},
																		{name: 'action_refresh', hidden: false, handler: function () {
																			Ext.getCmp('ufa_personReabRegistryWindow').ICFRefresh('b',3);
																		}.createDelegate(this)},
																		{name: 'action_print', hidden: false, handler: function () {
																			Ext.getCmp('ufa_personReabRegistryWindow').ICFPrint('b');
																		}.createDelegate(this)}
																	],
																	autoExpandColumn: 'autoexpand',
																	//autoExpandMin: 100,
																	autoLoadData: false,
																	id: 'ReabICF_b',
																	//pageSize: 50,
																	width: Ext.getBody().getWidth() - 263,
																	height: Ext.getBody().getHeight() - 220,
																	paging: false, // навигатор
																	region: 'center',
																	dataUrl: '/?c=Ufa_Reab_Register_User&m=getListICF_Verdict',
																	stringfields: [
																		{name: 'ReabICFRating_id', type: 'int', header: 'ID'},
																		{name: 'ICFSetDate', type: 'date', dateFormat: 'd.m.Y', header: lang['data'], width: 80, align: 'center', vertical: 'middle', sortable: false, },
																		{name: 'ICF_Code', type: 'string', header: 'Код', align: 'center', vertical: 'middle', width: 80},
																		{name: 'ICF_Description', type: 'string', hidden: true},
																		{name: 'ICF_EvalRealiz_id', type: 'int', hidden: true},
																		{name: 'ICF_TargetRealiz_id', type: 'int', hidden: true},
																		{name: 'ICF_Name', type: 'string', width: 300, vertical: 'middlee', header: '<div style="width:300px;text-align:center;">' + "Наименование" + '</div>'},
																		{name: 'ICF_EvalRealiz', type: 'int', width: 90, align: 'center', vertical: 'middlee', header: 'Выраженность <br>нарушения'},
																		{name: 'ICF_TargetRealiz', type: 'int', width: 80, align: 'center', vertical: 'middlee', header: 'Цель'},
																		{name: 'MedPersonalPost', type: 'string', header: 'Профиль врача', align: 'center', width: 200},
																		{name: 'MedStaffFact_id', type: 'int', hidden: true},
																		{name: 'MedPersonalFIO', type: 'string', header: '<div style="width:200px;text-align:center;">' + "Ф.И.О" + '</div>', width: 200, id: 'autoexpand'}
																		//header: '<div style="width:100px;text-align:center; font-family:serif;font-weight: bold;font-size:medium;">' + lang['klass'] + '</div>',

																	],
																	totalProperty: 'totalCount',
																	focusOnFirstLoad: false,
																	toolbar: true,
																	onBeforeLoadData: function () {
																		//this.getButtonSearch().disable();
																	}.createDelegate(this),
																	onLoadData: function () {
																		// alert('Хрень');
																		//this.getButtonSearch().enable();
																	}.createDelegate(this),
																	onRowSelect: function (sm, index, record) {
																		if (record.data.ICF_Code != null)
																		{
																			Ext.getCmp('ReabICF_b').ViewActions.action_edit.setDisabled(false);
																			Ext.getCmp('ReabICF_b').ViewActions.action_delete.setDisabled(false);
																		}
																	}
																})
													],
													listeners: {
														'activate': function (p) {
															// Ext.getCmp('ufa_personReabRegistryWindow').hideShowButtons(p.id);
															Ext.getCmp('ReabICF_b_pan').doLayout();
															Ext.getCmp('ReabICF_b').getGrid().getSelectionModel().clearSelections();
														}
													}
												},
												{
													title: 'Структура организма',
													id: 'ReabICF_s_pan',
													xtype: 'panel',
													autoScroll: true,
													height: 'auto',
													items: [
														new sw.Promed.ViewFrame(
																{
																	actions: [
																		{name: 'action_add', handler: function () {
																			Ext.getCmp('ufa_personReabRegistryWindow').ICFVerdict('s', 'add');
																		}.createDelegate(this)},
																		{name: 'action_view', hidden: true},
																		{name: 'action_edit', disabled: true, handler: function () {
																			Ext.getCmp('ufa_personReabRegistryWindow').ICFVerdict('s', 'edit');
																		}.createDelegate(this)}, // Выход на списочную форму
																		{name: 'action_delete', disabled: true, handler: function () {
																			Ext.getCmp('ufa_personReabRegistryWindow').ICFVerdictDelete('s');
																		}.createDelegate(this)},
																		{name: 'action_refresh', hidden: false, handler: function () {
																			Ext.getCmp('ufa_personReabRegistryWindow').ICFRefresh('s',3);
																		}.createDelegate(this)},
																		{name: 'action_print', hidden: false, handler: function () {
																			Ext.getCmp('ufa_personReabRegistryWindow').ICFPrint('s');
																		}.createDelegate(this)}
																	],
																	autoExpandColumn: 'autoexpand',
																	//autoExpandMin: 100,
																	autoLoadData: false,
																	id: 'ReabICF_s',
																	//pageSize: 50,
																	width: Ext.getBody().getWidth() - 263,
																	height: Ext.getBody().getHeight() - 220,
																	paging: false, // навигатор
																	region: 'center',
																	dataUrl: '/?c=Ufa_Reab_Register_User&m=getListICF_Verdict',
																	stringfields: [
																		{name: 'ReabICFRating_id', type: 'int', header: 'ID'},
																		{name: 'ICFSetDate', type: 'date', dateFormat: 'd.m.Y', header: lang['data'], width: 80, align: 'center', vertical: 'middle', sortable: false, },
																		{name: 'ICF_Code', type: 'string', header: 'Код', align: 'center', vertical: 'middle', width: 80},
																		{name: 'ICF_Description', type: 'string', hidden: true},
																		{name: 'ICF_EvalRealiz_id', type: 'int', hidden: true},
																		{name: 'ICF_TargetRealiz_id', type: 'int', hidden: true},
																		{name: 'ICFNature_id', type: 'int', hidden: true},
																		{name: 'ICFLocalization_id', type: 'int', hidden: true},
																		{name: 'ICF_Name', type: 'string', width: 300, vertical: 'middlee', header: '<div style="width:300px;text-align:center;">' + "Наименование" + '</div>'},
																		{name: 'ICF_EvalRealiz', type: 'int', width: 90, align: 'center', vertical: 'middlee', header: 'Выраженность <br>нарушения'},
																		{name: 'ICFNature_Name', type: 'string', width: 180, vertical: 'middlee', header: '<div style="width:180px;text-align:center;">' + "Характер <br>нарушения" + '</div>'},
																		{name: 'ICFLocalization_Name', type: 'string', width: 120, align: 'center', vertical: 'middlee', header: 'Локализация <br>нарушения'},
																		{name: 'ICF_TargetRealiz', type: 'int', width: 80, align: 'center', vertical: 'middlee', header: 'Цель'},
																		{name: 'MedPersonalPost', type: 'string', header: 'Профиль врача', align: 'center', width: 200},
																		{name: 'MedStaffFact_id', type: 'int', hidden: true},
																		{name: 'MedPersonalFIO', type: 'string', header: '<div style="width:200px;text-align:center;">' + "Ф.И.О" + '</div>', width: 200, id: 'autoexpand'}
																		//header: '<div style="width:100px;text-align:center; font-family:serif;font-weight: bold;font-size:medium;">' + lang['klass'] + '</div>',

																	],
																	totalProperty: 'totalCount',
																	focusOnFirstLoad: false,
																	toolbar: true,
																	onBeforeLoadData: function () {
																		//this.getButtonSearch().disable();
																	}.createDelegate(this),
																	onLoadData: function () {
																		// alert('Хрень');
																		//this.getButtonSearch().enable();
																	}.createDelegate(this),
																	onRowSelect: function (sm, index, record) {
																		if (record.data.ICF_Code != null)
																		{
																			Ext.getCmp('ReabICF_s').ViewActions.action_edit.setDisabled(false);
																			Ext.getCmp('ReabICF_s').ViewActions.action_delete.setDisabled(false);
																		}
																	}
																})
													],
													listeners: {
														'activate': function (p) {
															// Ext.getCmp('ufa_personReabRegistryWindow').hideShowButtons(p.id);
															Ext.getCmp('ReabICF_s_pan').doLayout();
														}
													}
												},
												{
													title: 'Факторы окружающей среды',
													id: 'ReabICF_e_pan',
													xtype: 'panel',
													autoScroll: true,
													height: 'auto',
													items: [
														new sw.Promed.ViewFrame(
																{
																	actions: [
																		{name: 'action_add', handler: function () {
																			Ext.getCmp('ufa_personReabRegistryWindow').ICFVerdict('e', 'add');
																		}.createDelegate(this)},
																		{name: 'action_view', hidden: true},
																		{name: 'action_edit', disabled: true, handler: function () {
																			Ext.getCmp('ufa_personReabRegistryWindow').ICFVerdict('e', 'edit');
																		}.createDelegate(this)},
																		{name: 'action_delete', disabled: true, handler: function () {
																			Ext.getCmp('ufa_personReabRegistryWindow').ICFVerdictDelete('e');
																		}.createDelegate(this)},
																		{name: 'action_refresh', hidden: false, handler: function () {
																			Ext.getCmp('ufa_personReabRegistryWindow').ICFRefresh('e',2);
																		}.createDelegate(this)},
																		{name: 'action_print', hidden: false, handler: function () {
																			Ext.getCmp('ufa_personReabRegistryWindow').ICFPrint('e');
																		}.createDelegate(this)}
																	],
																	autoExpandColumn: 'autoexpand',
																	//autoExpandMin: 100,
																	autoLoadData: false,
																	id: 'ReabICF_e',
																	//pageSize: 50,
																	width: Ext.getBody().getWidth() - 263,
																	height: Ext.getBody().getHeight() - 220,
																	paging: false, // навигатор
																	region: 'center',
																	dataUrl: '/?c=Ufa_Reab_Register_User&m=getListICF_Verdict',
																	stringfields: [
																		{name: 'ReabICFRating_id', type: 'int', header: 'ID'},
																		{name: 'ICFSetDate', type: 'date', dateFormat: 'd.m.Y', header: lang['data'], width: 80, align: 'center', vertical: 'middle', sortable: false, },
																		{name: 'ICF_Code', type: 'string', header: 'Код', align: 'center', vertical: 'middle', width: 80},
																		{name: 'ICF_Description', type: 'string', hidden: true},
																		{name: 'ICF_EnvFactors_id', type: 'int', hidden: true},
																		{name: 'ICF_FactorsTarget_id', type: 'int', hidden: true},
																		{name: 'ICF_Name', type: 'string', width: 300, vertical: 'middlee', header: '<div style="width:300px;text-align:center;">' + "Наименование" + '</div>'},
																		{name: 'ICFEnvFactors', type: 'int', width: 90, align: 'center', vertical: 'middlee', header: 'Степень <br>выраженности'},
																		{name: 'ICFEnvFactorsTarget', type: 'int', width: 80, align: 'center', vertical: 'middlee', header: 'Цель'},
																		{name: 'MedPersonalPost', type: 'string', header: 'Профиль врача', align: 'center', width: 200},
																		{name: 'MedStaffFact_id', type: 'int', hidden: true},
																		{name: 'MedPersonalFIO', type: 'string', header: '<div style="width:200px;text-align:center;">' + "Ф.И.О" + '</div>', width: 200, id: 'autoexpand'}
																	],
																	totalProperty: 'totalCount',
																	focusOnFirstLoad: false,
																	toolbar: true,
																	onBeforeLoadData: function () {
																		//this.getButtonSearch().disable();
																	}.createDelegate(this),
																	onLoadData: function () {
																		// alert('Хрень');
																		//this.getButtonSearch().enable();
																	}.createDelegate(this),
																	onRowSelect: function (sm, index, record) {
																		if (record.data.ICF_Code != null)
																		{
																			Ext.getCmp('ReabICF_e').ViewActions.action_edit.setDisabled(false);
																			Ext.getCmp('ReabICF_e').ViewActions.action_delete.setDisabled(false);
																		}
																	}
																})
													],
													listeners: {
														'activate': function (p) {
															Ext.getCmp('ReabICF_e_pan').doLayout();
														}
													}
												}
											]
										}

									],
									listeners: {
										'activate': function (p) {
											Ext.getCmp('ufa_personReabRegistryWindow').hideShowButtons(p.id);
											Ext.getCmp('ReabICFVal').doLayout();

											if (Ext.getCmp('ufa_personReabRegistryWindow').newICF == false)
											{
												//Заполнение GRIDов по ICF
												Ext.getCmp('ufa_personReabRegistryWindow').ICFRefresh('b', 1);
												Ext.getCmp('ufa_personReabRegistryWindow').ICFRefresh('d', 1);
												Ext.getCmp('ufa_personReabRegistryWindow').ICFRefresh('e', 1);
												Ext.getCmp('ufa_personReabRegistryWindow').ICFRefresh('s', 1);
											}
											Ext.getCmp('ufa_personReabRegistryWindow').newICF = true;
											if (Ext.getCmp('ufa_personReabRegistryWindow').ARMType == 'spec_mz')
											{
												Ext.getCmp('ReabICF_d').ViewActions.action_add.setHidden(true);
												Ext.getCmp('ReabICF_d').ViewActions.action_edit.setHidden(true);
												Ext.getCmp('ReabICF_d').ViewActions.action_delete.setHidden(true);

												Ext.getCmp('ReabICF_b').ViewActions.action_add.setHidden(true);
												Ext.getCmp('ReabICF_b').ViewActions.action_edit.setHidden(true);
												Ext.getCmp('ReabICF_b').ViewActions.action_delete.setHidden(true);


												Ext.getCmp('ReabICF_s').ViewActions.action_add.setHidden(true);
												Ext.getCmp('ReabICF_s').ViewActions.action_edit.setHidden(true);
												Ext.getCmp('ReabICF_s').ViewActions.action_delete.setHidden(true);

												Ext.getCmp('ReabICF_e').ViewActions.action_add.setHidden(true);
												Ext.getCmp('ReabICF_e').ViewActions.action_edit.setHidden(true);
												Ext.getCmp('ReabICF_e').ViewActions.action_delete.setHidden(true);
											} else
											{
												Ext.getCmp('ReabICF_d').ViewActions.action_add.setHidden(false);
												Ext.getCmp('ReabICF_d').ViewActions.action_add.setHidden(false);
												Ext.getCmp('ReabICF_d').ViewActions.action_edit.setHidden(false);
												Ext.getCmp('ReabICF_d').ViewActions.action_delete.setHidden(false);

												Ext.getCmp('ReabICF_b').ViewActions.action_add.setHidden(false);
												Ext.getCmp('ReabICF_b').ViewActions.action_edit.setHidden(false);
												Ext.getCmp('ReabICF_b').ViewActions.action_delete.setHidden(false);


												Ext.getCmp('ReabICF_s').ViewActions.action_add.setHidden(false);
												Ext.getCmp('ReabICF_s').ViewActions.action_edit.setHidden(false);
												Ext.getCmp('ReabICF_s').ViewActions.action_delete.setHidden(false);

												Ext.getCmp('ReabICF_e').ViewActions.action_add.setHidden(false);
												Ext.getCmp('ReabICF_e').ViewActions.action_edit.setHidden(false);
												Ext.getCmp('ReabICF_e').ViewActions.action_delete.setHidden(false);
											}
										}
									}
								},
								{
									title: 'Шкалы',
									id: 'scalesReab',
									xtype: 'panel',
									disabled: true,
									autoScroll: true,
									items: [
										{
											xtype: 'panel',
											title: '',
											frame: false,
											id: 'scalesReabSubPanel',
											//   autoScroll : true,
											//    layout : 'border',
											layout: 'column',
											// margins: '5 0 0 5',
											// style: 'margin: 6px 0px 0px 6px; background: #DFE8F6',
											// style: 'pаdding: 3px 6px 6px 6px; background: #DFE8F6',
											width: Ext.getBody().getWidth() - 263,
											height: Ext.getBody().getHeight() - 140,
											tbar: [
												{
													xtype: 'button',
													text: 'Добавить',
													id: 'addReabScaleDataButton',
													iconCls: 'add16',
													disabled: true,
													handler: function () {
														var form = Ext.getCmp('ufa_personReabRegistryWindow');
														form.addScaleData();
													}
												},
												{
													xtype: 'button',
													text: 'Сохранить',
													id: 'saveReabScaleDataButton',
													iconCls: 'save16',
													disabled: true,
													handler: function () {
														var form = Ext.getCmp('ufa_personReabRegistryWindow');
														form.SaveScaleData();
													}
												},
												{
													xtype: 'button',
													text: 'Изменить',
													disabled: true,
													hidden: false,
													id: 'editReabScaleDataButton',
													iconCls: 'edit16',
													handler: function () {
														alert('Отработка');
													}
												},
												{
													xtype: 'button',
													text: 'Удалить',
													disabled: true,
													hidden: false,
													id: 'deleteReabScaleDataButton',
													iconCls: 'delete16',
													handler: function () {
														Ext.getCmp('ufa_personReabRegistryWindow').DeleteScaleData();
													}
												},
												{
													xtype: 'button',
													text: 'Печать шкалы',
													disabled: true,
													id: 'printReabScaleButton',
													iconCls: 'print16',
													handler: function () {
														Ext.getCmp('scalesReab').printScales();
													}
												},
												/*
												 //													new Ext.Action({
												 //														text: 'Печать',
												 //														iconCls: 'print16',
												 //													    menu:
												 //															new Ext.menu.Menu([
												 //																{text: langs('Список'), handler: function () {alert("ddddd")}.createDelegate(this)},
												 //																{text: langs('Листок прибытия'),  handler: function () {alert("ddddd")}.createDelegate(this)},
												 //																{text: langs('Листок убытия'), handler: function () {alert("ddddd")}.createDelegate(this)},
												 //																{text: '114/у - Сопроводительный лист и талон к нему', handler: function () {alert("ddddd")}.createDelegate(this)}
												 //															])
												 //														}),
												 //
												 */
												{
													xtype: 'tbfill'
												}
											],
											items: [
												{
													xtype: 'panel',
													layout: 'column',
													region: 'west',
													width: 250,
													items: [
														{
															xtype: 'panel',
															layout: 'form',
															//width: 250,
															//height: Ext.getBody().getHeight()-130,
															items: [
																this.scaleReabLeftPanel
															]

														}
													]
												},
												{
													xtype: 'panel',
													layout: 'column',
													hidden: true,
													region: 'center',
													width: 120,
													//height: Ext.getBody().getHeight()-150,
													id: 'scaleReabCenterPanel',
													items: [
														{
															xtype: 'panel',
															layout: 'form',
															//  width: 120,
															// width: Ext.getBody().getWidth()-650,
															//height: Ext.getBody().getHeight()-150,
															border: false,
															items: [
																this.scaleReabCenterPanel
															]
														}
													]
												},
												{
													xtype: 'panel',
													layout: 'form',
													region: 'east',
													autoScroll: true,
													// autoWidth: true,
													// autoHeight: true,
													width: Ext.getBody().getWidth() - 660,
													//width: 1800,
													height: Ext.getBody().getHeight() - 140,

													// width: 400,
													items: [
														this.scaleReabRightPanel
													]
												}
											]
										}
									],
									printScales: function ()
									{
//										sw.swMsg.alert(lang['soobschenie'], 'Печать в разработке!');
//											return;
										if (Ext.getCmp('saveReabScaleDataButton').disabled == false)
										{
											sw.swMsg.alert(lang['soobschenie'], 'Печать шкалы невозможна!. Шкала не сохранена!');
											return;
										}
										var form = Ext.getCmp('ufa_personReabRegistryWindow');
										//console.log("Печать");
										var url = ((getGlobalOptions().birtpath) ? getGlobalOptions().birtpath : '') + '/run?__report=report/formScales.rptdesign';

										var sysNickScale = Ext.getCmp('GridReabScales').getGrid().getSelectionModel().getSelected().get('ScaleType_SysNick')
										var titleScale = "";
										switch (sysNickScale)
										{
											case 'renkin':
											//Шкала Рэнкина
											case 'Hauser':
											//Индекс ходьбы Хаузера
											case 'rivermid':
											case 'glasgow':
											case 'Harris':
											case 'МоСА':
											case 'Frenchay':
											case 'dysarthria':
											case 'Lequesne':
											case 'Bartel':
											case 'Alarm_HADS':
											case 'Depression_HADS':
											case 'Berg':
												titleScale = Ext.getCmp('GridReabScales').getGrid().getSelectionModel().getSelected().get('ScaleType_Name');
												break;
											case 'GRACE':
												titleScale = 'Оценка риска смерти больных ОКС в стационаре и через 6 месяцев';
												break;
											case 'Ashworth':
												//Шкала Ашфорта
												titleScale = 'Модифицированная шкала Ашфорт';
												break;
											case 'Killip':
												titleScale = 'Классификация острой сердечной недостаточности по Киллип';
												break;
											case 'VAScale':
												titleScale = 'Визуально-аналоговая шкала (ВАШ) боли';
												break;
											case 'MedResCouncil':
												titleScale = "Шкала Комитета медицинских исследований";
												break;
											case 'FIM':
												titleScale = "МЕРА ФУНКЦИОНАЛЬНОЙ НЕЗАВИСИМОСТИ (FIM)";
												break;
											case 'ARAT':
												titleScale = "Тест двигательной активности руки (АРАТ)";
												break;
											case 'Vasserman':
												titleScale = "Шкала Вассермана Л.И. для оценки степени выраженности речевых нарушений у больных с локальными поражениями мозга";
												break;
											case 'nihss':
												titleScale = 'Шкала тяжести инсульта национальных институтов США (NIHSS)';
												break;
											case 'rivermid_DAA':
												titleScale = "Шкала активностей повседневной жизни Ривермид";
												break;
											default :
												form.showMsg('Косяк');
												break;
										}
										url += '&titleScale=' + titleScale;
										//Для Grace
										if (sysNickScale == 'GRACE')
										{
//											var uuu = Ext.getCmp('Creating_OKS_id').getStore().data.items[Ext.getCmp('Creating_OKS_id').getStore().find('ScaleParameterResult_id', Ext.getCmp('Creating_OKS_id').getValue())].get('ScaleParameterResult_Name');
//											console.log("uuu=",uuu);
											url += '&scaleSysnickGrace=' + Ext.getCmp('Creating_OKS_id').getStore().data.items[Ext.getCmp('Creating_OKS_id').getStore().find('ScaleParameterResult_id', Ext.getCmp('Creating_OKS_id').getValue())].get('ScaleParameterResult_Name');
											url += '&GraceHospDegree=' + Ext.getCmp('ReabRiskHospitalDegree').getValue();
											url += '&GraceHospDeath=' + Ext.getCmp('ReabProbability_of_hosp_death').getValue();
											url += '&Grace6MountDegree=' + Ext.getCmp('ReabRisk6MonthsDegree').getValue();
											url += '&Grace6MountDeath=' + Ext.getCmp('ReabProbability6Months_death').getValue();
											//console.log("GraceHospDegree=",Ext.getCmp('ReabRiskHospitalDegree').getValue());
										}
										url += '&scaleSysnick=' + sysNickScale;

										//профиль,этап,дата и время
										var fff = form.GridReabObjects.getGrid().getSelectionModel().getSelected().get('DirectType_Name');
										url += '&DirectType=' + fff.substr(0, fff.indexOf(" "));

										url += '&StageName=' + form.GridReabObjects.getGrid().getSelectionModel().getSelected().get('StageName');
										url += '&DateScale=' + Ext.getCmp('FillindScaleDateReab').value;
										url += '&TimeScale=' + Ext.getCmp('FillindScaleTimeReab').getValue();

										//Данные по врачу + ID заполненной шкалы
										var evaluation_date = Ext.getCmp('FillindScaleDateReab').value.substr(6, 4) + '-' +
												Ext.getCmp('FillindScaleDateReab').value.substr(3, 2) + '-' + Ext.getCmp('FillindScaleDateReab').value.substr(0, 2) + " " +
												Ext.getCmp('FillindScaleTimeReab').getValue() + ":00";
										//console.log('evaluation_date=',evaluation_date);
										for (var kk = 0; kk < Ext.getCmp('ufa_personReabRegistryWindow').ScalesDatesTreePanel.objDateScale.length; kk++)
										{
											//console.log('evaluation_date2=',form.ScalesDatesTreePanel.objDateScale[kk].setDate.date.substr(0,19));
											if (form.ScalesDatesTreePanel.objDateScale[kk].setDate.date.substr(0,19) == evaluation_date)
											{
												url += '&MedPersonal=' + form.ScalesDatesTreePanel.objDateScale[kk].MedPersonal;
												url += '&LpuId=' + form.ScalesDatesTreePanel.objDateScale[kk].LpuId;
												url += '&ScaleId=' + form.ScalesDatesTreePanel.objDateScale[kk].ScaleId;
												break;
											}
										}

										//Данные по пациенту
										url += '&Pacient=' + form.PersonInfoPanelReab.DataView.store.data.items[0].data.Person_Surname + " " +
												form.PersonInfoPanelReab.DataView.store.data.items[0].data.Person_Firname + " " +
												form.PersonInfoPanelReab.DataView.store.data.items[0].data.Person_Secname;
//										;
										url += '&SexPacient=' + form.PersonInfoPanelReab.DataView.store.data.items[0].data.Sex_Name;
										url += '&Pacient_Birthday=' + form.PersonInfoPanelReab.DataView.store.data.items[0].json.Person_Birthday;


										//Для Лекена
										if (sysNickScale == "Lequesne")
										{
											url += '&Type_of_joint=' + Ext.getCmp('LequesneType_of_joint_id').getStore().data.items[Ext.getCmp('LequesneType_of_joint_id').getStore().find('ReabSpr_Elem_id', Ext.getCmp('LequesneType_of_joint_id').getValue())].data.SprName;
											url += '&Side_Lequesne=' + Ext.getCmp('LequesneSide_id').getStore().data.items[Ext.getCmp('LequesneSide_id').getStore().find('ReabSpr_Elem_id', Ext.getCmp('LequesneSide_id').getValue())].data.SprName;
											url += '&ReabTotal=' + 'Ограничение жизнедеятельности:   ' + Ext.getCmp('Reab' + sysNickScale + 'Total').getValue();
										}
										//Передача интерпретации
										if (sysNickScale == "glasgow" || sysNickScale == "Alarm_HADS" || sysNickScale == "Depression_HADS" || sysNickScale == "Berg" ||
												sysNickScale == "Harris" || sysNickScale == "dysarthria")
										{
											url += '&ReabTotal=' + Ext.getCmp('Reab' + sysNickScale + 'Total').getValue();
										}
										if (sysNickScale == "rivermid_DAA")
										{
											url += '&ReabTotal=' + 'Интегральный показатель - ADL:   ' + Ext.getCmp('Reab' + sysNickScale + 'Total').getValue().replace('%', '');
										}
										if (sysNickScale == "nihss")
										{
											url += '&ReabTotal=' + 'Тяжесть инсульта:   ' + Ext.getCmp('Reab' + sysNickScale + 'Total').getValue();
										}
										if (sysNickScale == "Vasserman")
										{
											url += '&ReabTotal=' + 'Степень нарушения:   ' + Ext.getCmp('Reab' + sysNickScale + 'Total').getValue();
										}

										//url += '&formType= 1' ;
										url += '&__format=pdf';
										//console.log('BOB_url=', url);  //BOB - 06.08.2017
										window.open(url, '_blank');
									},
									listeners: {
										'activate': function (p) {
											Ext.getCmp('ufa_personReabRegistryWindow').hideShowButtons(p.id);
											Ext.getCmp('scalesReab').doLayout();

											if (Ext.getCmp('scaleReabCenterPanel').hidden == true)
											{
												Ext.getCmp('printReabScaleButton').setDisabled(true);  //Кнопка печати шкалы
											}

											if (Ext.getCmp('ufa_personReabRegistryWindow').ARMType == 'spec_mz')
											{

												Ext.getCmp('addReabScaleDataButton').setVisible(false);
												Ext.getCmp('saveReabScaleDataButton').setVisible(false);
												Ext.getCmp('editReabScaleDataButton').setVisible(false);
												Ext.getCmp('deleteReabScaleDataButton').setVisible(false);
											}
											else
											{
												Ext.getCmp('addReabScaleDataButton').setVisible(true);
												Ext.getCmp('saveReabScaleDataButton').setVisible(true);
												Ext.getCmp('editReabScaleDataButton').setVisible(true);
												Ext.getCmp('deleteReabScaleDataButton').setVisible(true);
											}


//											Ext.getCmp('ufa_personReabRegistryWindow').hideShowButtons(p.id);
											// Ext.getCmp('scalesReabSubPanel').removeAll();
											//  console.log('Панель шкал2');

											// Ext.getCmp('scalesReabSubPanel').add(Ext.getCmp('scaleReabLeftPanel'));
											//console.log('Панель шкал3');
//                                            //console.log('!!!!!!!', Ext.getCmp('EventsGrid').getGrid().getStore())
//
//                                            Ext.getCmp('EventsGrid').getGrid().getStore().baseParams = {
//                                                MorbusType_id : Ext.getCmp('ufa_personBskRegistryWindow').MorbusType_id,
//                                                Person_id :  Ext.getCmp('ufa_personBskRegistryWindow').Person_id
//                                            }
//
//                                            Ext.getCmp('EventsGrid').getGrid().getStore().load({
//                                                MorbusType_id : Ext.getCmp('ufa_personBskRegistryWindow').MorbusType_id,
//                                                Person_id :  Ext.getCmp('ufa_personBskRegistryWindow').Person_id
//                                            });


										}
									}
								},
								{
									title: 'Измерения',
									id: 'MeasurementsReab',
									xtype: 'panel',
									disabled: true,
									autoScroll: true,
									items: [
										{
											xtype: 'panel',
											border: false,
											layout: 'form',
											title: '<span style="font-size:13px">' + 'Функциональные пробы' + '</span>',
											collapsible: true,
											frame: true,
											tabIndex: -1,
											// width: Ext.getBody().getWidth()- 263,
											// height: Ext.getBody().getHeight()-140,
											items: [
												new Ext.Panel({
													height: 211,
													width: 1200,
													layout: 'border',
													border: true,
													style: 'border-top: 1px solid #99bbe8; border-left: 1px solid #99bbe8; border-bottom: 1px solid #ffffff;border-right: 1px solid #ffffff; padding: 0px; ',
													items: [
														new sw.Promed.ViewFrame(
																{
																	actions: [
																		{name: 'action_add', handler: function () {
																			Ext.getCmp('FuncTestsCns2_id').AddFuncTests();
																		}.createDelegate(this)},
																		{name: 'action_view', hidden: true},
																		{name: 'action_edit', disabled: true, handler: function () {
																			Ext.getCmp('FuncTestsCns2_id').EditFuncTests();
																		}.createDelegate(this)}, // Выход на списочную форму
																		{name: 'action_delete', disabled: true, handler: function () {
																			Ext.getCmp('FuncTestsCns2_id').DeleteFuncTests();
																		}.createDelegate(this)},
																		{name: 'action_refresh', hidden: false, handler: function () {
																			Ext.getCmp('FuncTestsCns2_id').RefreshTest();
																		}.createDelegate(this)},
																		{name: 'action_print', hidden: true}
																	],
																	autoExpandColumn: 'autoexpand',
																	autoExpandMin: 100,
																	autoLoadData: false,
																	id: 'FuncTestsCns2_id',
																	pageSize: 50,
																	height: 110,
																	//width: 1200,
																	paging: false, // навигатор
																	region: 'center',
																	dataUrl: '/?c=Ufa_Reab_Register_User&m=getListTestUserReab',
																	stringfields: [
																		{name: 'ReabTestId', type: 'int', header: 'ID'},
																		{name: 'TestSetDate', type: 'datetime', dateFormat: 'd.m.Y H:i', header: lang['data_i_vremya_provedeniya'], width: 150},
																		//  {name: 'ReabTest_setDate', type: 'date', dateFormat: 'd.m.Y H:i:s', header: lang['data'], width: 110},
																		//    {name: 'SocStatus_begDT', type: 'date', dateFormat: 'd.m.Y H:i:s'},
																		{name: 'ReabTestNameId', type: 'string', width: 100, hidden: true}, //1
																		{name: 'ReabTestName', type: 'string', header: 'Наименование теста', width: 200},
																		{name: 'ReabTestValueId', type: 'string', width: 100, hidden: true}, //2
																		{name: 'ReabTestValue', type: 'string', header: 'Значение теста', width: 200},
																		{name: 'ReabTestWeight', type: 'string', header: 'Потенциал', hidden: true},
																		{name: 'MedPersonal_iid', type: 'int', header: 'Сотрудник', hidden: true},
																		{name: 'Lpu_iid', type: 'int', header: 'ЛПУ', hidden: true},
																	],
																	totalProperty: 'totalCount',
																	focusOnFirstLoad: false,
																	toolbar: true,
																	onBeforeLoadData: function () {
																		//this.getButtonSearch().disable();
																	}.createDelegate(this),
																	onLoadData: function () {
																		// alert('Хрень');
																		//this.getButtonSearch().enable();
																	}.createDelegate(this),
																	onRowSelect: function (sm, index, record) {
																		//  console.log('onRowSelect=');
																		Ext.getCmp('FuncTestsCns2_id').ViewActions.action_delete.setDisabled(false);
																		Ext.getCmp('FuncTestsCns2_id').ViewActions.action_edit.setDisabled(false);
																		Ext.getCmp('FuncTestsCns2_id_Panel').hide();
																	},
																	//Обновление GRIDa
																	RefreshTest: function ()
																	{
																		var form = Ext.getCmp('ufa_personReabRegistryWindow');

																		Ext.getCmp('FuncTestsCns2_id').getGrid().getStore().load({
																			params: {
																				Person_id: form.Person_id,
																				DirectType_id: form.DirectType_id,
																				StageType_id: form.StageType_id
																			},
																			callback: function (success) {
																				// console.log('success11=', success);

																				var nRec = Ext.getCmp('FuncTestsCns2_id').getGrid().getStore().data.items.length;
																				if (nRec == 0)
																				{
																					Ext.getCmp('FuncTestsCns2_id').getGrid().tbar.dom.firstChild.firstChild.firstChild.firstChild.lastChild.firstChild.innerText = '0 / 0';
																				} else
																				{
																					Ext.getCmp('FuncTestsCns2_id').getGrid().getSelectionModel().selectRow(0);
																					Ext.getCmp('FuncTestsCns2_id').getGrid().getSelectionModel().deselectRow(0);
																				}
																				// console.log('RefreshTest=');
																				Ext.getCmp('FuncTestsCns2_id').ViewActions.action_delete.setDisabled(true);
																				Ext.getCmp('FuncTestsCns2_id').ViewActions.action_edit.setDisabled(true);

																				if (Ext.getCmp('ufa_personReabRegistryWindow').ARMType == 'spec_mz')
																				{
																					Ext.getCmp('FuncTestsCns2_id').ViewActions.action_add.setHidden(true);
																					Ext.getCmp('FuncTestsCns2_id').ViewActions.action_delete.setHidden(true);
																					Ext.getCmp('FuncTestsCns2_id').ViewActions.action_edit.setHidden(true);
																				}
																			}
																		});
																	},
																	//Удаление пробы
																	DeleteFuncTests: function ()
																	{
																		var form = Ext.getCmp('ufa_personReabRegistryWindow');
																		//Контроль даты и исполнителя
																		var rr = Ext.getCmp('FuncTestsCns2_id').getGrid().getSelectionModel().getSelected();
																		if (rr.data.MedPersonal_iid == getGlobalOptions().medpersonal_id && rr.data.Lpu_iid == getGlobalOptions().lpu_id)
																		{
																			Ext.getCmp('FuncTestsCns2_id').ViewActions.action_add.setDisabled(true);
																			var diff = Math.ceil((new Date().getTime() - rr.data.TestSetDate.getTime()) / (1000 * 60 * 60 * 24)) - 1;
																			// console.log('diff=',diff);
																			if (diff > 30)
																			{
																				form.showMsg('Дата удаляемого теста не может быть ранее 30 дней от текущей даты. Пожалуйста, проверьте указанную дату теста.');
																				return;
																			}
																			Ext.getCmp('FuncTestsCns2_id_Panel').hide();
																			//Само едаление
																			var loadMask = new Ext.LoadMask(this.getEl(), {msg: lang['podojdite_idet_sohranenie']});
																			loadMask.show();

																			Ext.Ajax.request({
																				url: '?c=Ufa_Reab_Register_User&m=saveRegistrTest',
																				params: {
																					Person_id: Ext.getCmp('ufa_personReabRegistryWindow').Person_id,
																					DirectType_id: Ext.getCmp('ufa_personReabRegistryWindow').DirectType_id,
																					StageType_id: Ext.getCmp('ufa_personReabRegistryWindow').StageType_id,
																					ReabTest_setDate: Ext.getCmp('MeasurTestDateReab').getValue(),
																					ReabTestParam_id: 0,
																					ReabTestValue_id: 0,
																					MedPersonal_iid: getGlobalOptions().medpersonal_id,
																					Lpu_iid: getGlobalOptions().lpu_id,
																					isButton: 'Delete',
																					ReabResultTest_id: Ext.getCmp('FuncTestsCns2_id').getGrid().getSelectionModel().getSelected().data.ReabTestId
																				},
																				callback: function (options, success, response)
																				{
																					// console.log('success=',success);
																					// console.log('response=',response);
																					loadMask.hide(); // Обязательно сделать !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
																					if (success == true)
																					{
																						var response_obj = Ext.util.JSON.decode(response.responseText);
																						if (response_obj.success == true)
																						{
																							//Перерисовка GRIDa
																							Ext.getCmp('FuncTestsCns2_id').RefreshTest();
																							Ext.getCmp('FuncTestsCns2_id').ViewActions.action_add.setDisabled(false);
																							Ext.getCmp('FuncTestsCns2_id_Panel').isButton = '';
																							//console.log('Все хорошо');
																						}
																					} else {
																						sw.swMsg.alert(lang['soobschenie'], lang['oshibka_pri_vyipolnenii_zaprosa_k_serveru']);
																					}
																				}
																			})

																			Ext.getCmp('FuncTestsCns2_id').ViewActions.action_delete.setDisabled(true);
																			Ext.getCmp('FuncTestsCns2_id').ViewActions.action_add.setDisabled(false);
																		} else
																		{
																			sw.swMsg.alert(lang['soobschenie'], lang['u_vas_net_prav_na_udalenie']);
																		}
																	},
																	//Редактирование пробы
																	EditFuncTests: function ()
																	{
																		var form = Ext.getCmp('ufa_personReabRegistryWindow');
																		if (Ext.getCmp('ufa_personReabRegistryWindow').ARMType == 'spec_mz')
																		{
																			return;
																		}
																		;
																		//Контроль даты и персонала
																		var rr = Ext.getCmp('FuncTestsCns2_id').getGrid().getSelectionModel().getSelected();
																		if (rr.data.MedPersonal_iid == getGlobalOptions().medpersonal_id && rr.data.Lpu_iid == getGlobalOptions().lpu_id)
																		{
																			var diff = Math.ceil((new Date().getTime() - rr.data.TestSetDate.getTime()) / (1000 * 60 * 60 * 24)) - 1;
																			// console.log('diff=',diff);
																			if (diff > 30)
																			{
																				form.showMsg('Дата редактируемого теста не может быть ранее 30 дней от текущей даты. Пожалуйста, проверьте указанную дату теста.');
																				return;
																			}

																			//Продолжаем
																			Ext.getCmp('FuncTestsCns2_id').ViewActions.action_add.setDisabled(true);
																			Ext.getCmp('FuncTestsCns2_id_Panel').isButton = 'Edit';
																			//Заполняем форму
																			Ext.getCmp('MeasurTestDateReab').setValue(rr.data.TestSetDate);
																			Ext.getCmp('FillindTestTimeReab').setValue(rr.data.TestSetDate.toTimeString().substr(0, 5));
																			Ext.getCmp('ValueTestCns2Reab').setValue(rr.data.ReabTestValueId);
																			Ext.getCmp('NameTestCns2Reab').setValue(rr.data.ReabTestNameId);

																			Ext.getCmp('FuncTestsCns2_id_Panel').show();

																			Ext.getCmp('FuncTestsCns2_id').ViewActions.action_edit.setDisabled(true);
																			Ext.getCmp('FuncTestsCns2_id').ViewActions.action_add.setDisabled(false);
																		} else
																		{
																			sw.swMsg.alert(lang['soobschenie'], lang['u_vas_net_prav_na_redaktirovanie']);
																		}

																	},
																	//Добавление теста
																	AddFuncTests: function ()
																	{
																		var form = Ext.getCmp('ufa_personReabRegistryWindow');
																		// Отсекаем закрытые этапы
																		if (form.GridReabObjects.getGrid().getSelectionModel().getSelected().get('OutCause_id') != 0)
																		{
																			sw.swMsg.alert(lang['soobschenie'], 'Добавление функциональных проб невозможно. Этап закрыт!');
																			return
																		}
																		// alert('Добавляем!');
																		Ext.getCmp('MeasurTestDateReab').setValue(new Date());
																		Ext.getCmp('FillindTestTimeReab').setValue('');
																		Ext.getCmp('ValueTestCns2Reab').setValue('Введите параметр');
																		Ext.getCmp('ValueTestCns2Reab').selectedIndex = -1;
																		Ext.getCmp('NameTestCns2Reab').setValue('Введите параметр');
																		Ext.getCmp('NameTestCns2Reab').selectedIndex = -1;

																		Ext.getCmp('FuncTestsCns2_id_Panel').isButton = 'Add';
																		Ext.getCmp('FuncTestsCns2_id_Panel').show();
																		Ext.getCmp('FuncTestsCns2_id').ViewActions.action_add.setDisabled(true);
																	}
																})
													]
												}),
												new Ext.Panel({
													id: 'FuncTestsCns2_id_Panel',
													layout: 'form',
													border: true,
													hidden: false,
													// width:1240,
													//  heigth : 150,
													bodyStyle: 'padding: 10px;background-color: #E2FFE9', //+bgcolor
													style: 'border-top: 1px solid #99bbe8; border-left: 1px solid #99bbe8; border-bottom: 1px solid #ffffff;border-right: 1px solid #ffffff; padding: 2px 0px 0px 0px; margin-top: 5px;background-color: transparent ;',
													items: [
//				//Панель для заполнения RGRID
														{
															layout: 'form',
															//width: 1200,
															//labelWidth: 90,
															border: true,
															bodyStyle: 'background-color: transparent',
															style: 'background-color: transparent',
															items: [
																{
																	layout: 'column',
																	// width: 1200,
																	items: [
//						//Дата проведения
																		{
																			layout: 'form',
																			border: false,
																			// width: 320,
																			labelWidth: 100,
																			labelAlign: 'left',
																			items: [
																				new sw.Promed.SwDateField({
																					id: 'MeasurTestDateReab',
																					fieldLabel: 'Дата проведения',
																					disabled: false,
																					labelWidth: '100px',
																					width: '100px',
																					height: 30,
																					plugins: [
																						new Ext.ux.InputTextMask('99.99.9999', false)
																					],
																					xtype: 'swdatefield',
																					format: 'd.m.Y',
																					value: new Date(),
																					maxValue: getGlobalOptions().date,
																					listeners: {
																						'change': function () {
																							//    alert('Время');
																							// Удаленная дата
																							if (this.getValue() == '') {
																								Ext.getCmp('ufa_personReabRegistryWindow').showMsg('Введите дату проведения теста!');
																								this.setValue(new Date());
																								return;
																							}
																							var diff = Math.ceil((new Date().getTime() - this.getValue().getTime()) / (1000 * 60 * 60 * 24)) - 1;

																							if (this.getValue() > new Date) {
																								Ext.getCmp('ufa_personReabRegistryWindow').showMsg('Недопустимо указывать дату позднее текущей!');
																								this.setValue(new Date());
																								return;
																							} else if (diff > 30) {
																								form.showMsg('Дата проведения теста не может быть ранее 30 дней от текущей даты. Пожалуйста, проверьте указанную дату тестирования.');
																								this.setValue(new Date());
																								return;
																							}
																						},
																						'blur': function () {}
																					}
																				})
																			]
																		},
																		// Время проведения
																		{
																			layout: 'form',
																			border: false,
																			labelWidth: 50,
																			style: 'position:relative;  left:20px ',
																			items: [
																				{
																					allowBlank: false,
																					fieldLabel: 'Время',
																					disabled: false,
																					labelAlign: 'rigth',
																					id: 'FillindTestTimeReab',
																					plugins: [new Ext.ux.InputTextMask('99:99', true)],
																					validateOnBlur: false,
																					width: 60,
																					xtype: 'swtimefield'
																				}
																			]
																		},
																		//Вид теста
																		{
																			layout: 'form',
																			border: false,
																			labelWidth: 60,
																			labelAlign: 'rigth',
																			style: 'position:relative;  left:50px ',
																			items: [
																				//Вид теста (неврология 2 этап)
																				{
																					allowBlank: false,
																					// style : 'text-align : center; font-size:1.1em; ',
																					id: 'NameTestCns2Reab',
																					xtype: 'combo',
																					fieldLabel: 'Вид теста',
																					hideTrigger: false, // Chek
																					mode: 'local',
																					editable: false,
																					triggerAction: 'all',
																					displayField: 'paramName',
																					valueField: 'paramId',
																					width: 200,
																					emptyText: 'Введите параметр',
																					listWidth: 'auto',
																					hiddenName: 'paramId',
																					autoscroll: false,
																					store: new Ext.data.JsonStore({
																						url: '?c=Ufa_Reab_Register_User&m=ReabSpr',
																						autoLoad: false,
																						fields: [
																							{name: 'paramId', type: 'int'},
																							{name: 'paramName', type: 'string'},
																							{name: 'paramWeight', type: 'string'}
																						],
																						key: 'paramId',
																					}),
																					tpl: '<tpl for="."><div class="x-combo-list-item">' +
																					'{paramName} ' + '&nbsp;' +
																					'</div></tpl>'
																				}
																			]
																		},
																		//Значение теста
																		{
																			layout: 'form',
																			border: false,
																			labelWidth: 100,
																			labelAlign: 'rigth',
																			style: 'position:relative;  left:80px ',
																			items: [
																				//Вид теста (неврология 2 этап)
																				{
																					allowBlank: false,
																					// style : 'text-align : center; font-size:1.1em; ',
																					id: 'ValueTestCns2Reab',
																					xtype: 'combo',
																					fieldLabel: 'Значение теста',
																					hideTrigger: false, // Chek
																					mode: 'local',
																					editable: false,
																					triggerAction: 'all',
																					displayField: 'paramName',
																					valueField: 'paramId',
																					width: 210,
																					emptyText: 'Введите параметр',
																					listWidth: 'auto',
																					hiddenName: 'paramId',
																					autoscroll: false,
																					store: new Ext.data.JsonStore({
																						url: '?c=Ufa_Reab_Register_User&m=ReabSpr',
																						autoLoad: false,
																						fields: [
																							{name: 'paramId', type: 'int'},
																							{name: 'paramName', type: 'string'},
																							{name: 'paramWeight', type: 'string'}
																						],
																						key: 'paramId',
																					}),
																					tpl: '<tpl for="."><div class="x-combo-list-item">' +
																					'{paramName} ' + '&nbsp;' +
																					'</div></tpl>'
																				}
																			]
																		},
																	]
																},
//                             // кнопка сохранить
																{
																	layout: 'form',
																	width: 100,
																	items: [
																		new Ext.Button({
																			id: 'FuncTestsCns2_ButtonSave',
																			iconCls: 'save16',
																			text: 'Сохранить',
																			handler: function (b, e)
																			{
																				Ext.getCmp('FuncTestsCns2_id_Panel').FuncTestsCns2Save();
																			}.createDelegate(this)
																		})
																	]
																}
															]
														}
													],
													//Сохранение тестов
													FuncTestsCns2Save: function ()
													{
														var form = Ext.getCmp('ufa_personReabRegistryWindow');
														//Валидация
														if (Ext.getCmp('FillindTestTimeReab').isValid() == false)
														{
															if (Ext.getCmp('FillindTestTimeReab').getValue() == '')
															{
																form.showMsg(lang['zadat_vremya']);
																return;
															}
															form.showMsg('Не верно указано время измерения');
															return;
														}
														if (Ext.getCmp('NameTestCns2Reab').isValid() == false)
														{
															form.showMsg('Не указан вид теста');
															return;
														}
														if (Ext.getCmp('ValueTestCns2Reab').isValid() == false)
														{
															form.showMsg('Не указано значение теста');
															return;
														}

														//работа с датой и временем
														var rr = Ext.getCmp('MeasurTestDateReab').getValue();
														var uu = Ext.getCmp('FillindTestTimeReab').getValue();
														rr.setHours(parseInt(uu.substr(0, 2)));
														rr.setMinutes(parseInt(uu.substr(3, 2)));

														//Подготовка ReabTest_id
														if (Ext.getCmp('FuncTestsCns2_id_Panel').isButton == 'Add')
														{
															var resultTestId = null;
														} else
														{
															var resultTestId = Ext.getCmp('FuncTestsCns2_id').getGrid().getSelectionModel().getSelected().data.ReabTestId;
														}


														//console.log('nReabTest_id=',nReabTest_id);
														//Само сохранение
														var loadMask = new Ext.LoadMask(this.getEl(), {msg: lang['podojdite_idet_sohranenie']});
														loadMask.show();

														Ext.Ajax.request({
															url: '?c=Ufa_Reab_Register_User&m=saveRegistrTest',
															params: {
																Person_id: Ext.getCmp('ufa_personReabRegistryWindow').Person_id,
																DirectType_id: Ext.getCmp('ufa_personReabRegistryWindow').DirectType_id,
																StageType_id: Ext.getCmp('ufa_personReabRegistryWindow').StageType_id,

																ReabTest_setDate: rr,

																ReabTestParam_id: Ext.getCmp('NameTestCns2Reab').getValue(),
																ReabTestValue_id: Ext.getCmp('ValueTestCns2Reab').getValue(),

																MedPersonal_iid: getGlobalOptions().medpersonal_id,
																Lpu_iid: getGlobalOptions().lpu_id,
																isButton: Ext.getCmp('FuncTestsCns2_id_Panel').isButton,
																ReabResultTest_id: resultTestId

															},
															callback: function (options, success, response)
															{
																// console.log('success=',success);
																// console.log('response=',response);
																loadMask.hide(); // Обязательно сделать !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
																if (success == true)
																{
																	var response_obj = Ext.util.JSON.decode(response.responseText);
																	if (response_obj.success == true)
																	{
																		//Перерисовка GRIDa
																		Ext.getCmp('FuncTestsCns2_id').RefreshTest();
																		Ext.getCmp('FuncTestsCns2_id').ViewActions.action_add.setDisabled(false);
																		Ext.getCmp('FuncTestsCns2_id_Panel').isButton = '';
																		// console.log('Все хорошо');
																	}
																} else {
																	sw.swMsg.alert(lang['soobschenie'], lang['oshibka_pri_vyipolnenii_zaprosa_k_serveru']);
																}
															}
														})
														Ext.getCmp('FuncTestsCns2_id_Panel').hide();
													}
												})
											],
											listeners:
											{
												'render': function (panel) {
													panel.header.on('click', function () {
														if (panel.collapsed) {
															panel.expand();
														} else {
															panel.collapse();
														}
													});
												}
											}
										},
										{
											xtype: 'panel',
											border: false,
											layout: 'form',
											title: '<span style="font-size:13px">' + 'Частота сердечных сокращений' + '</span>',
											collapsible: true,
											frame: true,
											tabIndex: -1,
											items: [
												new Ext.Panel({
													height: 211,
													width: 1200,
													layout: 'border',
													border: true,
													style: 'border-top: 1px solid #99bbe8; border-left: 1px solid #99bbe8; border-bottom: 1px solid #ffffff;border-right: 1px solid #ffffff; padding: 0px; ',
													items: [
														new sw.Promed.ViewFrame(
																{
																	actions: [
																		{name: 'action_add', handler: function () {
																			Ext.getCmp('FuncHeartRateCns2_id').AddHeartRate();
																		}.createDelegate(this)},
																		{name: 'action_view', hidden: true},
																		{name: 'action_edit', hidden: true},
																		{name: 'action_delete', disabled: true, handler: function () {
																			Ext.getCmp('FuncHeartRateCns2_id').DeleteHeartRate();
																		}.createDelegate(this)},
																		{name: 'action_refresh', hidden: false, handler: function () {
																			Ext.getCmp('FuncHeartRateCns2_id').RefreshHeartRate();
																		}.createDelegate(this)},
																		{name: 'action_print', hidden: true}
																	],
																	autoExpandColumn: 'autoexpand',
																	autoExpandMin: 100,
																	autoLoadData: false,
																	id: 'FuncHeartRateCns2_id',
																	pageSize: 50,
																	height: 110,
																	//  width: 1240,
																	paging: false, // навигатор
																	region: 'center',
																	//root: 'data',
																	dataUrl: '/?c=Ufa_Reab_Register_User&m=getListHeartRateUserReab',
																	autoLoadData: false,
																	stringfields: [
																		{name: 'ReabHeartRate_id', type: 'int', header: 'ID'},
																		{name: 'HeartRate_setDate', type: 'datetime', dateFormat: 'd.m.Y H:i', header: lang['data_i_vremya_provedeniya'], width: 150},
																		{name: 'ReabHeartRate_peace', type: 'int', header: 'Частота сердечных сокращений', width: 200},
																		{name: 'ReabHeartRate_max', type: 'string', header: 'ЧСС мах сутки', width: 200},
																		{name: 'MedPersonal_iid', type: 'int', header: 'Сотрудник', hidden: true},
																		{name: 'Lpu_iid', type: 'int', header: 'ЛПУ', hidden: true},
																	],
																	totalProperty: 'totalCount',
																	focusOnFirstLoad: false,
																	toolbar: true,
																	onBeforeLoadData: function () {
																		//this.getButtonSearch().disable();
																	}.createDelegate(this),
																	onLoadData: function () {
																		// alert('Хрень');
																		//this.getButtonSearch().enable();
																	}.createDelegate(this),
																	onRowSelect: function (sm, index, record) {
																		//  console.log('onRowSelect=');
																		Ext.getCmp('FuncHeartRateCns2_id').ViewActions.action_delete.setDisabled(false);
																		Ext.getCmp('FuncHeartRateCns2_id_Panel').hide();
																	},
																	//  Расчет мах ЧСС
																	HeartRateMax: function ()
																	{
																		var form = Ext.getCmp('ufa_personReabRegistryWindow');
																		if (Ext.getCmp('HeartRateKolReab_id').isValid() == false)
																		{
																			form.showMsg('Неверно указана ЧСС покоя');
																			return;
																		}
//                              ЧСС мах сут=(145-ЧСС покоя)*0,6 + ЧСС покоя
																		var D = Ext.getCmp('HeartRateKolReab_id').getValue();
																		var ff = (145 - D) * 0.6 + D;
																		Ext.getCmp('HeartRateMaxReab_id').setValue(Math.round(ff));
																	},
																	//Обновление GRIDa
																	RefreshHeartRate: function ()
																	{
																		var form = Ext.getCmp('ufa_personReabRegistryWindow');

																		Ext.getCmp('FuncHeartRateCns2_id').getGrid().getStore().load({
																			params: {
																				Person_id: form.Person_id,
																				DirectType_id: form.DirectType_id,
																				StageType_id: form.StageType_id
																			},
																			callback: function (success) {
																				//console.log('success11=', success);

																				var nRec = Ext.getCmp('FuncHeartRateCns2_id').getGrid().getStore().data.items.length;
																				if (nRec == 0)
																				{
																					Ext.getCmp('FuncHeartRateCns2_id').getGrid().tbar.dom.firstChild.firstChild.firstChild.firstChild.lastChild.firstChild.innerText = '0 / 0';
																				} else
																				{
																					Ext.getCmp('FuncHeartRateCns2_id').getGrid().getSelectionModel().selectRow(0);
																					Ext.getCmp('FuncHeartRateCns2_id').getGrid().getSelectionModel().deselectRow(0);
																				}
																				// console.log('RefreshTest=');
																				Ext.getCmp('FuncHeartRateCns2_id').ViewActions.action_delete.setDisabled(true);
																				Ext.getCmp('FuncHeartRateCns2_id').ViewActions.action_add.setDisabled(false);

																				if (Ext.getCmp('ufa_personReabRegistryWindow').ARMType == 'spec_mz')
																				{
																					Ext.getCmp('FuncHeartRateCns2_id').ViewActions.action_add.setHidden(true);
																					Ext.getCmp('FuncHeartRateCns2_id').ViewActions.action_delete.setHidden(true);
																				}

																			}
																		});
																	},
																	//Удаление пробы
																	DeleteHeartRate: function ()
																	{
																		var form = Ext.getCmp('ufa_personReabRegistryWindow');
																		//console.log('diff=');
																		//Контроль даты и исполнителя
																		var rr = Ext.getCmp('FuncHeartRateCns2_id').getGrid().getSelectionModel().getSelected();
																		if (rr.data.MedPersonal_iid == getGlobalOptions().medpersonal_id && rr.data.Lpu_iid == getGlobalOptions().lpu_id)
																		{
																			Ext.getCmp('FuncHeartRateCns2_id').ViewActions.action_add.setDisabled(true);
																			var diff = Math.ceil((new Date().getTime() - rr.data.HeartRate_setDate.getTime()) / (1000 * 60 * 60 * 24)) - 1;
																			// console.log('diff=',diff);
																			if (diff > 30)
																			{
																				form.showMsg('Дата удаляемого измерения не может быть ранее 30 дней от текущей даты. Пожалуйста, проверьте указанную дату измерения.');
																				return;
																			}
																			Ext.getCmp('FuncHeartRateCns2_id_Panel').hide();
																			//Само едаление
																			var loadMask = new Ext.LoadMask(this.getEl(), {msg: lang['podojdite_idet_sohranenie']});
																			loadMask.show();

																			Ext.Ajax.request({
																				url: '?c=Ufa_Reab_Register_User&m=saveHeartRate',
																				params: {
																					Person_id: Ext.getCmp('ufa_personReabRegistryWindow').Person_id,
																					DirectType_id: Ext.getCmp('ufa_personReabRegistryWindow').DirectType_id,
																					StageType_id: Ext.getCmp('ufa_personReabRegistryWindow').StageType_id,
																					ReabHeartRate_setDate: Ext.getCmp('MeasurTestDateReab').getValue(),
																					ReabHeartRate_peace: 0,
																					ReabHeartRate_max: 0,
																					MedPersonal_iid: getGlobalOptions().medpersonal_id,
																					Lpu_iid: getGlobalOptions().lpu_id,
																					isButton: 'Delete',
																					ReabHeartRate_id: Ext.getCmp('FuncHeartRateCns2_id').getGrid().getSelectionModel().getSelected().data.ReabHeartRate_id
																				},
																				callback: function (options, success, response)
																				{
																					// console.log('success=',success);
																					// console.log('response=',response);
																					loadMask.hide(); // Обязательно сделать !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
																					if (success == true)
																					{
																						var response_obj = Ext.util.JSON.decode(response.responseText);
																						if (response_obj.success == true)
																						{
																							//Перерисовка GRIDa
																							Ext.getCmp('FuncHeartRateCns2_id').RefreshHeartRate();
																							Ext.getCmp('FuncHeartRateCns2_id').ViewActions.action_add.setDisabled(false);
																							//console.log('Все хорошо');
																						}
																					} else {
																						sw.swMsg.alert(lang['soobschenie'], lang['oshibka_pri_vyipolnenii_zaprosa_k_serveru']);
																					}
																				}
																			})

																			Ext.getCmp('FuncHeartRateCns2_id').ViewActions.action_delete.setDisabled(true);
																			Ext.getCmp('FuncTestsCns2_id').ViewActions.action_add.setDisabled(false);
																		} else
																		{
																			sw.swMsg.alert(lang['soobschenie'], lang['u_vas_net_prav_na_udalenie']);
																		}
																	},
																	//Добавление теста
																	AddHeartRate: function ()
																	{
																		var form = Ext.getCmp('ufa_personReabRegistryWindow');
																		// Отсекаем закрытые этапы
																		if (form.GridReabObjects.getGrid().getSelectionModel().getSelected().get('OutCause_id') != 0)
																		{
																			sw.swMsg.alert(lang['soobschenie'], 'Добавление измерений ЧСС невозможно. Этап закрыт!');
																			return
																		}
																		Ext.getCmp('HeartRateDateReab').setValue(new Date());
																		Ext.getCmp('HeartRateTimeReab').setValue('');
																		Ext.getCmp('HeartRateKolReab_id').setValue('');
																		Ext.getCmp('HeartRateMaxReab_id').setValue('');


																		Ext.getCmp('FuncHeartRateCns2_id_Panel').show();
																		Ext.getCmp('FuncHeartRateCns2_ButtonSave').focus();

																		Ext.getCmp('FuncHeartRateCns2_id').ViewActions.action_add.setDisabled(true);
																		Ext.getCmp('FuncHeartRateCns2_id').ViewActions.action_delete.setDisabled(true);
																	}
																})
													]
												}),
												new Ext.Panel({
													id: 'FuncHeartRateCns2_id_Panel',
													layout: 'form',
													border: true,
													hidden: false,
													bodyStyle: 'padding: 10px;background-color: #E2FFE9', //+bgcolor
													style: 'border-top: 1px solid #99bbe8; border-left: 1px solid #99bbe8; border-bottom: 1px solid #ffffff;border-right: 1px solid #ffffff; padding: 2px 0px 0px 0px; margin-top: 5px;background-color: transparent ;',
													items: [
//				//Панель для заполнения RGRID
														{
															layout: 'form',
															border: true,
															bodyStyle: 'background-color: transparent',
															style: 'background-color: transparent',
															items: [
																{layout: 'column',
																	// width: 1200,
																	items: [
//						//Дата проведения
																		{
																			layout: 'form',
																			border: false,
																			labelWidth: 100,
																			labelAlign: 'left',
																			items: [
																				new sw.Promed.SwDateField({
																					id: 'HeartRateDateReab',
																					fieldLabel: 'Дата проведения',
																					disabled: false,
																					labelWidth: '100px',
																					width: '100px',
																					height: 30,
																					plugins: [
																						new Ext.ux.InputTextMask('99.99.9999', false)
																					],
																					xtype: 'swdatefield',
																					format: 'd.m.Y',
																					value: new Date(),
																					maxValue: getGlobalOptions().date,
																					listeners: {
																						'change': function () {
																							var form = Ext.getCmp('ufa_personReabRegistryWindow');
																							// Удаленная дата
																							if (this.getValue() == '') {
																								Ext.getCmp('ufa_personReabRegistryWindow').showMsg('Введите дату проведения измерения!');
																								this.setValue(new Date());
																								return;
																							}
																							var diff = Math.ceil((new Date().getTime() - this.getValue().getTime()) / (1000 * 60 * 60 * 24)) - 1;

																							if (this.getValue() > new Date) {
																								Ext.getCmp('ufa_personReabRegistryWindow').showMsg('Недопустимо указывать дату позднее текущей!');
																								this.setValue(new Date());
																								return;
																							} else if (diff > 30) {
																								form.showMsg('Дата проведения измерения не может быть ранее 30 дней от текущей даты. Пожалуйста, проверьте указанную дату измерения.');
																								this.setValue(new Date());
																								return;
																							}
																						},
																						'blur': function () {}
																					}
																				})
																			]
																		},
																		// Время проведения
																		{
																			layout: 'form',
																			border: false,
																			labelWidth: 50,
																			style: 'position:relative;  left:20px ',
																			items: [
																				{
																					allowBlank: false,
																					fieldLabel: 'Время',
																					disabled: false,
																					labelAlign: 'rigth',
																					id: 'HeartRateTimeReab',
																					plugins: [new Ext.ux.InputTextMask('99:99', true)],
																					validateOnBlur: false,
																					width: 60,
																					xtype: 'swtimefield'
																				}
																			]
																		},
																		//ЧСС
																		{
																			layout: 'form',
																			border: false,
																			labelWidth: 200,
																			labelAlign: 'rigth',
																			style: 'position:relative;  left:50px ',
																			items: [
																				//Частота сердечных сокращений
																				new Ext.form.NumberField({
																					allowBlank: false,
																					// hideLabel : true,
																					fieldLabel: 'Частота сердечных сокращений',
																					disabled: false,
																					minLength: 1,
																					maxLength: 3,
																					maxValue: 500,
																					minValue: 1,
																					id: 'HeartRateKolReab_id',
																					style: 'text-align:center;font-weight:bold;color: black;',
																					// plugins:[ new Ext.ux.InputTextMask('999', true) ],
																					width: 60,
																					listeners: {
																						'change': function () {
																							Ext.getCmp('FuncHeartRateCns2_id').HeartRateMax();
																						}
																					}
																				})

																			]
																		},
																		//Максимальная ЧСС
																		{
																			layout: 'form',
																			border: false,
																			labelWidth: 130,
																			labelAlign: 'rigth',
																			style: 'position:relative;  left:80px ',
																			items: [
																				//Вид теста (неврология 2 этап)
																				new Ext.form.TextField({
																					allowBlank: false,
																					//hideLabel : true,
																					fieldLabel: 'Максимальная ЧСС',
																					disabled: true,
																					id: 'HeartRateMaxReab_id',
																					style: 'text-align:center;font-weight:bold;',
																					width: 80
																				})
																			]
																		}
																	]
																},
//                             // кнопка сохранить
																{
																	layout: 'form',
																	width: 100,
																	items: [
																		new Ext.Button({
																			id: 'FuncHeartRateCns2_ButtonSave',
																			iconCls: 'save16',
																			text: 'Сохранить',
																			handler: function (b, e)
																			{
																				Ext.getCmp('FuncHeartRateCns2_id_Panel').FuncHeartRateCns2Save();
																			}.createDelegate(this)
																		})
																	]
																}
															]
														}
													],
													//Сохранение тестов
													FuncHeartRateCns2Save: function ()
													{
														var form = Ext.getCmp('ufa_personReabRegistryWindow');
														//Валидация
														if (Ext.getCmp('HeartRateTimeReab').isValid() == false)
														{
															if (Ext.getCmp('HeartRateTimeReab').getValue() == '')
															{
																form.showMsg(lang['zadat_vremya']);
																return;
															}
															form.showMsg('Не верно указано время измерения');
															return;
														}
														if (Ext.getCmp('HeartRateKolReab_id').isValid() == false)
														{
															form.showMsg('Не верно указана ЧСС покоя');
															return;
														}

														//работа с датой и временем
														var rr = Ext.getCmp('HeartRateDateReab').getValue();
														var uu = Ext.getCmp('HeartRateTimeReab').getValue();
														rr.setHours(parseInt(uu.substr(0, 2)));
														rr.setMinutes(parseInt(uu.substr(3, 2)));
														//console.log('rr=',rr);
														//Само сохранение
														var loadMask = new Ext.LoadMask(this.getEl(), {msg: lang['podojdite_idet_sohranenie']});
														loadMask.show();

														Ext.Ajax.request({
															url: '?c=Ufa_Reab_Register_User&m=saveHeartRate',
															params: {
																Person_id: Ext.getCmp('ufa_personReabRegistryWindow').Person_id,
																DirectType_id: Ext.getCmp('ufa_personReabRegistryWindow').DirectType_id,
																StageType_id: Ext.getCmp('ufa_personReabRegistryWindow').StageType_id,
																ReabHeartRate_setDate: rr,
																ReabHeartRate_peace: Ext.getCmp('HeartRateKolReab_id').getValue(),
																ReabHeartRate_max: Ext.getCmp('HeartRateMaxReab_id').getValue(),
																MedPersonal_iid: getGlobalOptions().medpersonal_id,
																Lpu_iid: getGlobalOptions().lpu_id,
																isButton: 'Add',
																ReabHeartRate_id: null
															},
															callback: function (options, success, response)
															{
																// console.log('success=',success);
																// console.log('response=',response);
																loadMask.hide(); // Обязательно сделать !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
																if (success == true)
																{
																	var response_obj = Ext.util.JSON.decode(response.responseText);
																	if (response_obj.success == true)
																	{
																		//Перерисовка GRIDa
																		Ext.getCmp('FuncHeartRateCns2_id').RefreshHeartRate();
																		Ext.getCmp('FuncHeartRateCns2_id').ViewActions.action_add.setDisabled(false);
																		console.log('Все хорошо');
																	}
																} else {
																	sw.swMsg.alert(lang['soobschenie'], lang['oshibka_pri_vyipolnenii_zaprosa_k_serveru']);
																}
															}
														})
														Ext.getCmp('FuncHeartRateCns2_id_Panel').hide();
													}
												})
											],
											listeners:
											{
												'render': function (panel) {
													panel.header.on('click', function () {
														if (panel.collapsed) {
															panel.expand();
														} else {
															panel.collapse();
														}
													});
												}
											}
										}
									],
									listeners: {
										'activate': function (p) {
											Ext.getCmp('ufa_personReabRegistryWindow').hideShowButtons(p.id);
											// Загрузка справочников
											Ext.getCmp('NameTestCns2Reab').getStore().load(
													{params: {
														SprNumber: 200,
														SprNumberGroup: 1
													}});
											Ext.getCmp('ValueTestCns2Reab').getStore().load(
													{params: {
														SprNumber: 201,
														SprNumberGroup: 1
													}});
											Ext.getCmp(p.id).doLayout();

											Ext.getCmp('FuncTestsCns2_id').RefreshTest();
											Ext.getCmp('FuncHeartRateCns2_id').RefreshHeartRate();

											Ext.getCmp('FuncTestsCns2_id_Panel').hide();
											Ext.getCmp('FuncTestsCns2_id').ViewActions.action_add.setDisabled(false);
											//Закрываем в GRIDe удаление и редактирование
											Ext.getCmp('FuncTestsCns2_id').ViewActions.action_delete.setDisabled(true);
											Ext.getCmp('FuncTestsCns2_id').ViewActions.action_edit.setDisabled(true);

											Ext.getCmp('FuncHeartRateCns2_id_Panel').hide();
											Ext.getCmp('FuncHeartRateCns2_id').ViewActions.action_add.setDisabled(false);
											//Закрываем в GRIDe удаление и редактирование
											Ext.getCmp('FuncHeartRateCns2_id').ViewActions.action_delete.setDisabled(true);
											Ext.getCmp('FuncHeartRateCns2_id').ViewActions.action_edit.setDisabled(true);

										}
									}
								},
								{
									title: 'События',
									id: 'eventsReab',
									hidden: true,
									disabled: true,
									items: [
										{
											xtype: 'panel',
											title: '',
											frame: false,
											id: 'eventsReabSubPanel',
											//layout : 'column',
											items: [

											]
										}
									],
									listeners: {
										'activate': function (p) {
//                                            Ext.getCmp('ufa_personBskRegistryWindow').hideShowButtons(p.id);
//
//                                            //Ext.getCmp('eventsSubPanel').removeAll();
//                                            Ext.getCmp('eventsSubPanel').add(Ext.getCmp('EventsGrid'));
//                                            //console.log('!!!!!!!', Ext.getCmp('EventsGrid').getGrid().getStore())
//
//                                            Ext.getCmp('EventsGrid').getGrid().getStore().baseParams = {
//                                                MorbusType_id : Ext.getCmp('ufa_personBskRegistryWindow').MorbusType_id,
//                                                Person_id :  Ext.getCmp('ufa_personBskRegistryWindow').Person_id
//                                            }
//
//                                            Ext.getCmp('EventsGrid').getGrid().getStore().load({
//                                                MorbusType_id : Ext.getCmp('ufa_personBskRegistryWindow').MorbusType_id,
//                                                Person_id :  Ext.getCmp('ufa_personBskRegistryWindow').Person_id
//                                            });
//                                            Ext.getCmp('events').doLayout();
										}
									}
								},
								{
									title: 'Рекомендации',
									id: 'recommendReab',
									disabled: true,
									//html: '',
									items: [
										{
											xtype: 'panel',
											title: '',
											frame: false,
											id: 'recomendReabSubPanel',
											layout: 'column',
											items: [
												/*
												 Ext.getCmp('ufa_personBskRegistryWindow').RecomendationsDatesTreePanel,
												 {
												 xtype: 'panel',
												 columnWidth : 90,
												 items : []
												 }
												 */
											]
										}

									],
									listeners: {
										'activate': function (p) {
											alert('Это тьма');
										}
									}
								}
								/*
								 {
								 title: 'МКФ',
								 id: 'MKFReab',
								 disabled: true,
								 autoScroll: true,
								 //html: '',
								 items: [
								 {
								 xtype: 'panel',
								 title: '',
								 frame: false,
								 id: 'MKFSubPanel',
								 layout: 'column',
								 width: Ext.getBody().getWidth() - 263,
								 height: Ext.getBody().getHeight() - 140,
								 tbar: [
								 {
								 xtype: 'button',
								 text: 'Добавить',
								 id: 'addReabMkbButton',
								 iconCls: 'add16',
								 disabled: false,
								 handler: function () {
								 //alert('sssssss');
								 var Inparams = new Object();
								 Inparams.Title = 'Даты проведения анкетирования';
								 Inparams.Person_id = Ext.getCmp('ufa_personReabRegistryWindow').Person_id;
								 Inparams.DirectType_id = Ext.getCmp('ufa_personReabRegistryWindow').DirectType_id;

								 getWnd('ufaMkfDiagSearchTreeWindow').show({
								 callback1: function (data) {
								 //Запуск обновления окна анкеты
								 console.log('data=', data);
								 //Ext.getCmp('ufa_personReabRegistryWindow').loadAnketaInDate(data.ReabAnketa_Data.substr(0, 10));
								 this.hide();
								 },
								 Inparams: Inparams
								 });
								 }
								 },
								 {
								 xtype: 'button',
								 text: 'Отработка',
								 id: 'tmpReabMkbButton',
								 iconCls: 'add16',
								 disabled: false,
								 handler: function () {
								 //alert('sssssss');
								 var Inparams = new Object();
								 Inparams.ICF_id = 29;

								 getWnd('ufaMkfDiagSearchTreeWindow').show({
								 callback1: function (data) {
								 //Запуск обновления окна анкеты
								 console.log('data=', data);
								 //Ext.getCmp('ufa_personReabRegistryWindow').loadAnketaInDate(data.ReabAnketa_Data.substr(0, 10));
								 this.hide();
								 },
								 Inparams: Inparams
								 });
								 }
								 }
								 ],
								 items: [

								 ]
								 }

								 ],
								 listeners: {
								 'activate': function (p) {
								 Ext.getCmp('ufa_personReabRegistryWindow').hideShowButtons(p.id);
								 Ext.getCmp('MKFReab').doLayout();
								 //alert('Это тьма');
								 }
								 }
								 },
								 */
							],
						}
					],
					buttons: [
						{
							text: '-'
						},
						{
							xtype: 'button',
							id: 'closef',
							//text: 'Закрыть',
							text: lang['zakryit'],
							iconCls: 'close16',
							handler: function () {
								Ext.getCmp('ufa_personReabRegistryWindow').refresh();
							}
						}
					],
					//Перерисовывание Меню (наблюдение -этапы)
					setReabStageNew: function (directTypeId) {
						var form = Ext.getCmp('ufa_personReabRegistryWindow');
						var nRecord = form.GridReabObjects.ViewGridPanel.getStore().data.items.length;
						//   console.log('nRecord=', nRecord);
						//Формируем этап реабилитации
						for (var r = 0; r <= nRecord - 1; r++) {
							form.GridReabObjects.getGrid().getSelectionModel().selectRow(r);
							var record = form.GridReabObjects.getGrid().getSelectionModel().getSelected();
							//console.log('record=', record);
							var textStage = '(' + Ext.getCmp('ufa_personReabRegistryWindow').GridReabObjects.getGrid().getSelectionModel().getSelected().data.StageName + ')';

							if (Ext.getCmp('ufa_personReabRegistryWindow').GridReabObjects.getGrid().getSelectionModel().getSelected().data.OutCause_id == 0)
							{
								var outCause = 0;
							} else
							{
								var outCause = Ext.getCmp('ufa_personReabRegistryWindow').GridReabObjects.getGrid().getSelectionModel().getSelected().data.OutCause_Code;
							}
//                                 textStage = textStage + " " + "(" + parseInt(Ext.getCmp('ufa_personReabRegistryWindow').GridReabObjects.getGrid().getSelectionModel().getSelected().data.OutCause_Code) ;
							textStage = textStage + " " + "(" + outCause;
							//console.log('textStage=', textStage);
							var pattern = /\([I\-]+\)/gi;
							record.set('DirectType_Name', record.get('DirectType_Name').replace(pattern, '') + ' ' + textStage + '');
							record.commit();
						}
						form.GridReabObjects.getGrid().getSelectionModel().selectRow(Ext.getCmp('GridReabUser').getGrid().getStore().find('DirectType_id', directTypeId))
					},
					//Добавление профиля или этапа с контролем
					addReabProfStage: function (paramIn) {
						//Поиск профиля и этапа в регистре - если норма, то сохраняем
						var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
						loadMask.show();
						Ext.Ajax.request({
							url: '?c=Ufa_Reab_Register_User&m=AddRegistrProfStage',
							params: {
								Person_id: Ext.getCmp('ufa_personReabRegistryWindow').Person_id,
								DirectType_id: paramIn.DirectType_id,
								StageType_id: paramIn.StageId,
								ReabEvent_setDate: paramIn.DateIn,
								StageName: paramIn.StageName,
								MedPersonal_iid: getGlobalOptions().medpersonal_id,
								Lpu_iid: getGlobalOptions().lpu_id

							},
							callback: function (options, success, response)
							{
								loadMask.hide();
								if (success == true)
								{
									var response_obj = Ext.util.JSON.decode(response.responseText);
									//console.log('response_obj=',response_obj);
									if (response_obj.success == true)
									{
										//console.log('сохранение');
										//Передергивание Grida -- Работает
										Ext.getCmp('ufa_personReabRegistryWindow').GridReabObjects.getGrid().getStore().reload({
											callback: function (success) {
												//  console.log('success=', success);
												if (success.length > 0)
												{
													//Попробуем сразу сформировать этапы
													Ext.getCmp('ufa_personReabRegistryWindow').setReabStageNew();
												}
											}
										});

									}//Если false - система скажет сама
								} else {
									sw.swMsg.alert(lang['soobschenie'], lang['oshibka_pri_vyipolnenii_zaprosa_k_serveru']);
								}
							}
						})
					},
					// Закрытие этапа
					CloseReabProfStage: function (paramIn) {

						var form = Ext.getCmp('ufa_personReabRegistryWindow');
						//Запомним профиль
						var directTypeId = Ext.getCmp('GridReabUser').getGrid().getSelectionModel().getSelected().data.DirectType_id;

						var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
						loadMask.show();
						Ext.Ajax.request({
							url: '?c=Ufa_Reab_Register_User&m=CloseRegistrStage',
							params: {
								Person_id: Ext.getCmp('ufa_personReabRegistryWindow').Person_id,
								ReabEvent_id: Ext.getCmp('GridReabUser').getGrid().getSelectionModel().getSelected().data.ReabEvent_id,
								DirectType_id: paramIn.DirectType_id,
								StageType_id: paramIn.StageId,
								StageName: paramIn.StageName,
								ReabEvent_disDate: paramIn.DateOff,
								MedPersonal_did: getGlobalOptions().medpersonal_id,
								Lpu_did: getGlobalOptions().lpu_id,
								ReabOutCause_id: paramIn.StageOff
							},
							callback: function (options, success, response)
							{
								loadMask.hide();
								//console.log('response=',response.responseText);
								if (success == true)
								{
									var response_obj = Ext.util.JSON.decode(response.responseText);
									if (response_obj.success == true)
									{

										//Передергивание Grida -- Работает
										form.GridReabObjects.getGrid().getStore().reload({
											callback: function (success) {
												// console.log('success=', success);
												if (success.length > 0)
												{
													//Попробуем сразу сформировать этапы
													Ext.getCmp('ufa_personReabRegistryWindow').setReabStageNew(directTypeId);
													form.clickToPN = 0;
													if (Ext.getCmp('GridReabUser').getGrid().getSelectionModel().getSelected())
													{
														Ext.getCmp('GridReabUser').getGrid().fireEvent('rowclick', Ext.getCmp('GridReabUser').getGrid(), Ext.getCmp('GridReabUser').getGrid().getStore().find('DirectType_id', directTypeId))
													}
												}
											}});
									}//Если false - система скажет сама
								} else {
									sw.swMsg.alert(lang['soobschenie'], lang['oshibka_pri_vyipolnenii_zaprosa_k_serveru']);
								}
							}
						})
					},
					// отмена закрытия этапа
					CanselCloseReabProfStage: function (paramIn) {
						//alert('Прювет');
						var form = Ext.getCmp('ufa_personReabRegistryWindow');
						var directTypeId = Ext.getCmp('GridReabUser').getGrid().getSelectionModel().getSelected().data.DirectType_id;
						//по ID редактирование записи - ReabOutCause_id,ReabEvent_disDate,MedPersonal_did,Lpu_did,pmUser_updID,ReabEvent_updDT
						var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
						loadMask.show();
						Ext.Ajax.request({
							url: '?c=Ufa_Reab_Register_User&m=CanselCloseStage',
							params: {
								Person_id: Ext.getCmp('ufa_personReabRegistryWindow').Person_id,
								ReabEvent_id: paramIn
							},
							callback: function (options, success, response)
							{
								loadMask.hide();
								console.log('response=', response.responseText);
								if (success == true)
								{
									var response_obj = Ext.util.JSON.decode(response.responseText);
									if (response_obj.success == true)
									{
										//Передергивание Grida -- Работает
										form.GridReabObjects.getGrid().getStore().reload({
											callback: function (success) {
												// console.log('success=', success);
												if (success.length > 0)
												{
													//Попробуем сразу сформировать этапы
													Ext.getCmp('ufa_personReabRegistryWindow').setReabStageNew(directTypeId);
													form.clickToPN = 0;
													if (Ext.getCmp('GridReabUser').getGrid().getSelectionModel().getSelected())
													{
														Ext.getCmp('GridReabUser').getGrid().fireEvent('rowclick', Ext.getCmp('GridReabUser').getGrid(), Ext.getCmp('GridReabUser').getGrid().getStore().find('DirectType_id', directTypeId))
													}
												}
											}});
									}//Если false - система скажет сама
								} else {
									sw.swMsg.alert(lang['soobschenie'], lang['oshibka_pri_vyipolnenii_zaprosa_k_serveru']);
								}
							}
						})
					},
					//Обновление гридов ICF
					ICFRefresh: function (domen,num)
					{
						Ext.getCmp('ReabICF_b_pan').setDisabled(true);
						Ext.getCmp('ReabICF_d_pan').setDisabled(true);
						Ext.getCmp('ReabICF_e_pan').setDisabled(true);
						Ext.getCmp('ReabICF_s_pan').setDisabled(true);
						var form = Ext.getCmp('ufa_personReabRegistryWindow');
						Ext.getCmp('ReabICF_' + domen).getGrid().getStore().load({
							params: {
								Person_id: form.Person_id,
								ReabEvent_id: form.GridReabObjects.getGrid().getSelectionModel().getSelected().data.ReabEvent_id,
								Domen: domen
							},
							callback: function (success) {
								//console.log('ЕНЕНЕНЕНЕ');
								if (success.length > 0 && num > 1)
								{
									Ext.getCmp('ReabICF_' + domen).getGrid().getSelectionModel().clearSelections();
								}
								Ext.getCmp('ReabICF_' + domen).ViewActions.action_edit.setDisabled(true);
								Ext.getCmp('ReabICF_' + domen).ViewActions.action_delete.setDisabled(true);
								Ext.getCmp('ReabICF_b_pan').setDisabled(false);
								Ext.getCmp('ReabICF_d_pan').setDisabled(false);
								Ext.getCmp('ReabICF_e_pan').setDisabled(false);
								Ext.getCmp('ReabICF_s_pan').setDisabled(false);
							}
						});
					},
					//Удаление проведенной оценки по ICF
					ICFVerdictDelete: function (domen)
					{
						var form = Ext.getCmp('ufa_personReabRegistryWindow');
						// Отсекаем закрытые этапы
						if (form.GridReabObjects.getGrid().getSelectionModel().getSelected().get('OutCause_id') != 0)
						{

							sw.swMsg.alert(lang['soobschenie'], 'Удаление оценки невозможно. Этап закрыт!');
							return
						}
						console.log('MedStaffFact_id=',Ext.getCmp('ReabICF_' + domen).getGrid().getSelectionModel().getSelected().data.MedStaffFact_id);
						console.log('CurMedStaffFact_id=',getGlobalOptions().CurMedStaffFact_id);
						if (Ext.getCmp('ReabICF_' + domen).getGrid().getSelectionModel().getSelected().data.MedStaffFact_id == getGlobalOptions().CurMedStaffFact_id
								|| isSuperAdmin())
						{
							//alert('Удаление!');
							var loadMask = new Ext.LoadMask(this.getEl(), {msg: lang['podojdite_idet_sohranenie']});
							sw.swMsg.show({
								buttons: Ext.Msg.YESNO,
								fn: function (buttonId, text, obj) {
									if (buttonId == 'yes')
									{
										loadMask.show();
										Ext.Ajax.request({
											url: '?c=Ufa_Reab_Register_User&m=DeleteICFRating', //удаление
											params: {
												Person_id: form.Person_id,
												ReabEvent_id: form.GridReabObjects.getGrid().getSelectionModel().getSelected().data.ReabEvent_id,
												ReabICFRating_id: Ext.getCmp('ReabICF_' + domen).getGrid().getSelectionModel().getSelected().data.ReabICFRating_id
											},
											callback: function (options, success, response)
											{
												loadMask.hide(); // Обязательно сделать

												if (success == true)
												{
													var response_obj = Ext.util.JSON.decode(response.responseText);
													//console.log('response_obj=', response_obj);

													if (response_obj.success == true)
													{
														Ext.getCmp('ufa_personReabRegistryWindow').ICFRefresh(domen, 2);
														sw.swMsg.alert(lang['soobschenie'], 'Проведенная оценка удалена!');
														return;
													}

												} else
												{
													loadMask.hide();
													sw.swMsg.alert(lang['soobschenie'], lang['oshibka_pri_vyipolnenii_zaprosa_k_serveru']);
												}
												return;
											}
										})

									}
								}.createDelegate(this),
								icon: Ext.MessageBox.QUESTION,
								msg: langs('Вы хотите удалить запись?'),
								title: langs('Подтверждение')
							});
						} else
						{
							sw.swMsg.alert(lang['soobschenie'], lang['u_vas_net_prav_na_udalenie']);
							Ext.getCmp('ReabICF_' + domen).ViewActions.action_delete.setDisabled(true);
							return;
						}
					},
					// Запуск формы процедения оценки по МКФ (ввод/редактирование)
					ICFVerdict: function (domen, func)
					{
						var form = Ext.getCmp('ufa_personReabRegistryWindow');
						if (Ext.getCmp('ufa_personReabRegistryWindow').ARMType == 'spec_mz')
						{
							return;
						};
						// Отсекаем закрытые этапы
						if (form.GridReabObjects.getGrid().getSelectionModel().getSelected().get('OutCause_id') != 0)
						{
							if (func == 'add')
							{
								sw.swMsg.alert(lang['soobschenie'], 'Проведение оценки невозможно. Этап закрыт!');
							} else
							{
								sw.swMsg.alert(lang['soobschenie'], 'Редактирование оценки невозможно. Этап закрыт!');
							}
							return
						}
//					//Отправка даты случая на форму
						var Inparams = new Object();
						Inparams.Domen = domen; //Весь справочник или 1 раздел (с указанием раздела)
						Inparams.Person_id = form.Person_id;
						Inparams.ReabEvent_id = form.GridReabObjects.getGrid().getSelectionModel().getSelected().data.ReabEvent_id;
						Inparams.Func = func;
						Inparams.ICFSpr = form.ICFSpr;

						//Сформируем дату
						var form1 = Ext.getCmp('GridReabUser').getGrid().getSelectionModel().getSelected().data.Event_setDate;
						var cDate = ('0' + form1.getDate()).slice(-2) + '.' + ('0' + (form1.getMonth() + 1)).slice(-2) + '.' + form1.getFullYear();
						//console.log('cDate=',cDate);
						Inparams.Event_setDate = cDate;

						if (func == 'edit')
						{
							//if (form.ScalesDatesTreePanel.objDateScale[k].MedPersonal == getGlobalOptions().medpersonal_id || isSuperAdmin())
							if (Ext.getCmp('ReabICF_' + domen).getGrid().getSelectionModel().getSelected().data.MedStaffFact_id == getGlobalOptions().CurMedStaffFact_id
									|| isSuperAdmin())
							{
								Inparams.Code = Ext.getCmp('ReabICF_' + domen).getGrid().getSelectionModel().getSelected().data.ICF_Code;
								Inparams.Description = Ext.getCmp('ReabICF_' + domen).getGrid().getSelectionModel().getSelected().data.ICF_Description;
								Inparams.ICF_Name = Ext.getCmp('ReabICF_' + domen).getGrid().getSelectionModel().getSelected().data.ICF_Name;
								Inparams.ReabICFRating_id = Ext.getCmp('ReabICF_' + domen).getGrid().getSelectionModel().getSelected().data.ReabICFRating_id;
								Inparams.ICFDate = Ext.getCmp('ReabICF_'  + domen).getGrid().getSelectionModel().getSelected().data.ICFSetDate;
								switch (domen)
								{
									case 'b':
										//console.log('HHHHHHHHH=');
										Inparams.ICF_EvalRealiz = Ext.getCmp('ReabICF_' + domen).getGrid().getSelectionModel().getSelected().data.ICF_EvalRealiz_id;
										Inparams.ICF_TargetRealiz = Ext.getCmp('ReabICF_' + domen).getGrid().getSelectionModel().getSelected().data.ICF_TargetRealiz_id;
										break;
									case 'd':
										Inparams.ICF_EvalRealiz = Ext.getCmp('ReabICF_' + domen).getGrid().getSelectionModel().getSelected().data.ICF_EvalRealiz_id;
										Inparams.ICF_TargetRealiz = Ext.getCmp('ReabICF_' + domen).getGrid().getSelectionModel().getSelected().data.ICF_TargetRealiz_id;
										Inparams.ICF_EvalCapasit = Ext.getCmp('ReabICF_' + domen).getGrid().getSelectionModel().getSelected().data.ICF_CapasitEval_id;
										Inparams.ICF_TargetCapasit = Ext.getCmp('ReabICF_' + domen).getGrid().getSelectionModel().getSelected().data.ICF_TargetCapasit_id;
										break;
									case 's':
										Inparams.ICF_EvalRealiz = Ext.getCmp('ReabICF_' + domen).getGrid().getSelectionModel().getSelected().data.ICF_EvalRealiz_id;
										Inparams.ICF_TargetRealiz = Ext.getCmp('ReabICF_' + domen).getGrid().getSelectionModel().getSelected().data.ICF_TargetRealiz_id;
										Inparams.ICFSeverity_Nature = Ext.getCmp('ReabICF_' + domen).getGrid().getSelectionModel().getSelected().data.ICFNature_id;
										Inparams.CFSeverity_Localization = Ext.getCmp('ReabICF_' + domen).getGrid().getSelectionModel().getSelected().data.ICFLocalization_id;
										break;
									case 'e':
										Inparams.ICF_EnvFactors = Ext.getCmp('ReabICF_' + domen).getGrid().getSelectionModel().getSelected().data.ICF_EnvFactors_id;
										Inparams.ICF_FactorsTarget = Ext.getCmp('ReabICF_' + domen).getGrid().getSelectionModel().getSelected().data.ICF_FactorsTarget_id;
										break;
									default :
										sw.swMsg.alert(lang['soobschenie'], 'Косяк!!');
										break;
								}
							} else
							{
								sw.swMsg.alert(lang['soobschenie'], lang['u_vas_net_prav_na_redaktirovanie']);
								Ext.getCmp('ReabICF_' + domen).ViewActions.action_edit.setDisabled(true);
								return;
							}
						}
						//console.log('Inparams=', Inparams);
						getWnd('ufaMkfDiagSearchTreeWindow').show({
							callback1: function (data)
							{
								console.log('data1=', data[0].Func);
								switch (data[0].Func)
								{
									case 'add':
										console.log('data=', data);
										//form.SaveICFRating(domen,func,data);  //!!!!!!!!!!!!!!!!!!!!!!!
										break;
									case 'edit':
										form.showMsg('Редактирование будем делать');
										break;
									case 'out':
										form.ICFRefresh(domen,2);
										//form.showMsg('Это выход');
										break;
								}

								this.hide();
							},
							Inparams: Inparams
						});
					},
					//Печать оценок по МКФ
					ICFPrint: function (domen)
					{
						var form = Ext.getCmp('ufa_personReabRegistryWindow');
						//console.log("Печать");
						var url = ((getGlobalOptions().birtpath) ? getGlobalOptions().birtpath : '') + '/run?__report=report/formICF.rptdesign';

						var titleICF = "";
						switch (domen)
						{
							case 'd': //Активность и Участие
								titleICF = "Активность и Участие";
								codeICF = ""
								break;
							case 'b': // Функции организма
								titleICF = "Функции организма";
								break;
							case 's': // Структура организма
								titleICF = "Структура организма";
								break;
							case 'e': // Факторы окружающей среды
								titleICF = "Факторы окружающей среды";
								break;

							default :
								form.showMsg('Косяк');
								break;
						}
						url += '&titleICF=' + titleICF;
						url += '&Domen=' + domen;

						//Данные по пациенту
						url += '&Pacient=' + form.PersonInfoPanelReab.DataView.store.data.items[0].data.Person_Surname + " " +
								form.PersonInfoPanelReab.DataView.store.data.items[0].data.Person_Firname + " " +
								form.PersonInfoPanelReab.DataView.store.data.items[0].data.Person_Secname;
						url += '&SexPacient=' + form.PersonInfoPanelReab.DataView.store.data.items[0].data.Sex_Name;
						url += '&Pacient_Birthday=' + form.PersonInfoPanelReab.DataView.store.data.items[0].json.Person_Birthday;
						url += '&Person_id=' + Ext.getCmp('ufa_personReabRegistryWindow').Person_id;


						//профиль,этап,случай,дата и время
						var fff = form.GridReabObjects.getGrid().getSelectionModel().getSelected().get('DirectType_Name');
						url += '&DirectType=' + fff.substr(0, fff.indexOf(" "));
						url += '&StageName=' + form.GridReabObjects.getGrid().getSelectionModel().getSelected().get('StageName');
						url += '&DatePrint=' + getGlobalOptions().date.trim().substr(0, 10);
						url += '&ReabEvent_id=' + Ext.getCmp('GridReabUser').getGrid().getSelectionModel().getSelected().data.ReabEvent_id;

						url += '&__format=pdf';
						window.open(url, '_blank');
					},
					/**
					 * Прорисовка заполненной шкалы
					 */
					ScaleLoadData: function (ScaleSysNick, DataScale)
					{
						var form = Ext.getCmp('ufa_personReabRegistryWindow');
						//Отработка даты и времени для оценки
						Ext.getCmp('FillindScaleDateReab').setValue(DataScale.setDate.date.substr(0, 10));
						Ext.getCmp('FillindScaleTimeReab').setValue(DataScale.setDate.date.substr(11, 5));
						if (ScaleSysNick == 'renkin' || ScaleSysNick == 'rivermid' || ScaleSysNick == 'Killip' ||
								ScaleSysNick == 'Ashworth' || ScaleSysNick == 'Hauser')
						{
							//Обнуление значений новой шкалы
							for (j = 0; j < Ext.getCmp('ReabScaleGrid').getGrid().getStore().data.items.length; j++)
							{
								var vrecord = Ext.getCmp('ReabScaleGrid').ViewGridPanel.getStore().data.items[j].data;
								if (vrecord.selrow == '1')
								{
									// console.log('1111111111');
									Ext.getCmp('ReabScaleGrid').getGrid().getSelectionModel().selectRow(j);
									vrecord = Ext.getCmp('ReabScaleGrid').getGrid().getSelectionModel().getSelected();
									vrecord.set('selrow', '0');
									vrecord.commit();
									Ext.getCmp('ReabScaleGrid').getGrid().getSelectionModel().deselectRow(j);
									break;
								}
							}
							//Выбор значения для шкалы
							for (j = 0; j < Ext.getCmp('ReabScaleGrid').getGrid().getStore().data.items.length; j++)
							{
								var vrecord = Ext.getCmp('ReabScaleGrid').ViewGridPanel.getStore().data.items[j].data;
								if (vrecord.ScaleParameterResult_id == DataScale.scaleParam)
								{
									Ext.getCmp('ReabScaleGrid').getGrid().getSelectionModel().selectRow(j);
									vrecord = Ext.getCmp('ReabScaleGrid').getGrid().getSelectionModel().getSelected();
									vrecord.set('selrow', '1');
									vrecord.commit();
									break;
								}
							}
						}
						if (ScaleSysNick == 'glasgow')
						{
							// console.log('ScaleSysNick=',ScaleSysNick);
							var mParam = [];
							mParam = DataScale.scaleParam.split(';');
							Ext.getCmp('Param_1_id').setValue(mParam[0].substr(2));
							var index = Ext.getCmp('Param_1_id').getStore().find('ScaleParameterResult_id', mParam[0].substr(2));//нахожу индекс в store комбо по ScaleParameterResult_id из БД
							var rec = Ext.getCmp('Param_1_id').getStore().getAt(index);  // нахожу record по index,
							Ext.getCmp('Param_1_id').fireEvent('select', Ext.getCmp('Param_1_id'), rec, index); // запуск события в комбо

							Ext.getCmp('Param_2_id').setValue(mParam[1].substr(2));
							index = Ext.getCmp('Param_2_id').getStore().find('ScaleParameterResult_id', mParam[1].substr(2));//нахожу индекс в store комбо по ScaleParameterResult_id из БД
							rec = Ext.getCmp('Param_2_id').getStore().getAt(index);  // нахожу record по index,
							Ext.getCmp('Param_2_id').fireEvent('select', Ext.getCmp('Param_2_id'), rec, index); // запуск события в комбо
							Ext.getCmp('Param_3_id').setValue(mParam[2].substr(2));
							index = Ext.getCmp('Param_3_id').getStore().find('ScaleParameterResult_id', mParam[2].substr(2));//нахожу индекс в store комбо по ScaleParameterResult_id из БД
							rec = Ext.getCmp('Param_3_id').getStore().getAt(index);  // нахожу record по index,
							Ext.getCmp('Param_3_id').fireEvent('select', Ext.getCmp('Param_3_id'), rec, index); // запуск события в комбо

							//Интерпретация
							form.GraceTotals(ScaleSysNick, Ext.getCmp('ReabScaleValue_id').getValue());
							Ext.getCmp('FirstPanel' + ScaleSysNick).expand();

						}
						if (ScaleSysNick == 'Harris' || ScaleSysNick == 'Alarm_HADS' || ScaleSysNick == 'Depression_HADS' || ScaleSysNick == 'МоСА' || ScaleSysNick == 'Berg' ||
								ScaleSysNick == 'Frenchay' || ScaleSysNick == 'Bartel' || ScaleSysNick == 'Vasserman' || ScaleSysNick == 'FIM' || ScaleSysNick == 'dysarthria' || ScaleSysNick == 'Lequesne' ||
								ScaleSysNick == 'rivermid_DAA' || ScaleSysNick == 'nihss')
						{
							//console.log('ScaleSysNick=',ScaleSysNick);
							var m_param = [];
							m_param = DataScale.scaleParam.split(';');
							var m_refinement = [];
							if (ScaleSysNick == 'nihss' && DataScale.ScaleRefinement != null)
							{
								m_refinement = DataScale.ScaleRefinement.split(';');
							}
							// console.log('m_refinement=',m_refinement);

							if (ScaleSysNick == 'Lequesne')
							{
								// console.log('mParam=',m_param);
								var o_param = m_param[0].split('-');
								Ext.getCmp('LequesneType_of_joint_id').setValue(o_param[1]);
								o_param = m_param[1].split('-');
								Ext.getCmp('LequesneSide_id').setValue(o_param[1]);
								m_param.shift();
								m_param.shift();
								//console.log('mParam1=',m_param);
							}

							for (var ii = 0; ii < m_param.length; ii++)
							{
								var oParam = m_param[ii].split('-');

								Ext.getCmp(ScaleSysNick + 'Param_' + (ii + 1).toString()).setValue(oParam[1]);
								var index = Ext.getCmp(ScaleSysNick + 'Param_' + (ii + 1).toString()).getStore().find('ScaleParameterResult_id', oParam[1]);//нахожу индекс в store комбо по ScaleParameterResult_id из БД
								var rec = Ext.getCmp(ScaleSysNick + 'Param_' + (ii + 1).toString()).getStore().getAt(index);  // нахожу record по index,
								Ext.getCmp(ScaleSysNick + 'Param_' + (ii + 1).toString()).fireEvent('select', Ext.getCmp(ScaleSysNick + 'Param_' + (ii + 1).toString()), rec, index); // запуск события в комбо
								if (m_refinement.length > 0 && ScaleSysNick == 'nihss')
								{
									if (oParam[0] == 260 && oParam[1] == 1177 || oParam[0] == 261 && oParam[1] == 1183 || oParam[0] == 262 && oParam[1] == 1189 ||
											oParam[0] == 263 && oParam[1] == 1195 || oParam[0] == 264 && oParam[1] == 1199 || oParam[0] == 267 && oParam[1] == 1210)
									{
										for (var jj = 0; jj < m_refinement.length; jj++)
										{
											var oRefinement = m_refinement[jj].split('-');
											// console.log('oRefinement=',oRefinement);
											//  console.log('oParam=',oParam[0]);
											//     console.log('oRefinement=',oRefinement[0]);
											if (oParam[0] == oRefinement[0])
											{
												Ext.getCmp('nihssArea_' + (ii + 1)).setValue(oRefinement[1]);
												break;
											}
										}
									}
								}
							}

							if (ScaleSysNick == 'Berg' || ScaleSysNick == 'Vasserman' || ScaleSysNick == 'FIM' || ScaleSysNick == 'dysarthria' || ScaleSysNick == 'Lequesne' ||
									ScaleSysNick == 'rivermid_DAA' || ScaleSysNick == 'nihss' || ScaleSysNick == 'Alarm_HADS' || ScaleSysNick == 'Depression_HADS' || ScaleSysNick == 'Harris')
							{
								//Интерпретация
								form.GraceTotals(ScaleSysNick, Ext.getCmp('ReabScaleValue_id').getValue());
								// Ext.getCmp('FirstPanelBerg').focus(true,1000);
								Ext.getCmp('FirstPanel' + ScaleSysNick).expand();
							}
						}
						if (ScaleSysNick == 'GRACE')
						{
							Ext.getCmp('FirstPanelGRACE').setDisabled(true);
							//   console.log('DataScale=',DataScale.scaleParam);

							var mParam = [];
							mParam = DataScale.scaleParam.split(';');
							//console.log('mParam=', mParam);
							//Установка ОКС
							Ext.getCmp('Creating_OKS_id').setValue(mParam[0]);
							if (mParam[0] == 415)
							{
								var oks = 0;
							} else
							{
								var oks = 1;
							}

							//Загрузили COMBO и тянем параметры
							Ext.getCmp('FirstPanelGRACE').LoadTemplGrace(oks, mParam);
						}
						if (ScaleSysNick == 'VAScale')
						{
							Ext.getCmp('VASc_Panel').hide();
							Ext.getCmp('ReabGrid_VAScale').getGrid().store.removeAll();
							var mParam = [];
							var mParam1 = [];
							mParam = DataScale.scaleParam.split(';');
							console.log('mParam=', mParam);
							//Заполнение GRIDa
							for (var k = 0; k < mParam.length; k++)
							{
								mParam1 = mParam[k].split('-');
								console.log('mParam1=', mParam1);
								//Выбор значения(балла) для шкалы
								for (j = 0; j < Ext.getCmp('VASValue_id').getStore().data.items.length; j++)
								{
									if (Ext.getCmp('VASValue_id').getStore().data.items[j].data.ScaleParameterResult_id == mParam1[1])
									{
										var num_parametr = j;
										break;
									}
								}
								Ext.getCmp('ReabGrid_VAScale').getGrid().store.insert(k, [new Ext.data.Record({
									Id: k + 1,
									Localization_of_pain_id: Ext.getCmp('VASLocalization_id').getStore().data.items[mParam1[0] - 1].json[0],
									Localization_of_pain: Ext.getCmp('VASLocalization_id').getStore().data.items[mParam1[0] - 1].json[1],
									VASValue_id: Ext.getCmp('VASValue_id').getStore().data.items[num_parametr].data.ScaleParameterResult_id,
									VASName: Ext.getCmp('VASValue_id').getStore().data.items[num_parametr].data.ScaleParameterResult_Name
								})]);
							}
							Ext.getCmp('ReabGrid_VAScale').getGrid().getSelectionModel().selectRow(0);
							//Ext.getCmp('ReabGrid_VAScale').getGrid().getSelectionModel().deselectRow(0);
						}
						if (ScaleSysNick == 'MedResCouncil')
						{
							Ext.getCmp('MRCSc_Panel').hide();
							Ext.getCmp('ReabGrid_MRCScale').getGrid().store.removeAll();
							var mParam = [];
							var mParam1 = [];
							mParam = DataScale.scaleParam.split(';');
							//console.log('mParam=',mParam);
							//Заполнение GRIDa
							for (var k = 0; k < mParam.length; k++)
							{
								mParam1 = mParam[k].split('-');
								console.log('mParam1=', mParam1);
								//Выбор значения(балла и трактовки) для шкалы
								for (j = 0; j < Ext.getCmp('MRCValue_id').getStore().data.items.length; j++)
								{
									if (Ext.getCmp('MRCValue_id').getStore().data.items[j].data.ScaleParameterResult_id == mParam1[3])
									{
										var num_parametr = j;
										break;
									}
								}
								Ext.getCmp('ReabGrid_MRCScale').getGrid().store.insert(k, [new Ext.data.Record({
									Id: k + 1,
									limb_id: Ext.getCmp('MRClimb_id').getStore().data.items[mParam1[0] - 1].json[0],
									limb: Ext.getCmp('MRClimb_id').getStore().data.items[mParam1[0] - 1].json[1],
									Position_id: Ext.getCmp('MRCPosition_id').getStore().data.items[mParam1[1] - 1].json[0],
									Position: Ext.getCmp('MRCPosition_id').getStore().data.items[mParam1[1] - 1].json[1],
									Lateralization_id: Ext.getCmp('MRCLateralization_id').getStore().data.items[mParam1[2] - 1].json[0],
									Lateralization: Ext.getCmp('MRCLateralization_id').getStore().data.items[mParam1[2] - 1].json[1],
									Muscle_Strength: Ext.getCmp('MRCValue_id').getStore().data.items[num_parametr].json[2],
									MRCValue_id: Ext.getCmp('MRCValue_id').getStore().data.items[num_parametr].json[1],
									MRCName: Ext.getCmp('MRCValue_id').getStore().data.items[num_parametr].json[3]
								})]);

							}
							Ext.getCmp('ReabGrid_MRCScale').getGrid().getSelectionModel().selectRow(0);
						}

						if (ScaleSysNick == 'ARAT')
						{
							Ext.getCmp('ReabGrid_ARATScale').noHand = true;
							Ext.getCmp('Reab' + ScaleSysNick + 'input').setDisabled(true);
							//Обнуление полей ввода
							Ext.getCmp('ReabARATHandSide').selectedIndex = -1;
							Ext.getCmp('ReabARATHandSide').setValue('Введите параметр');
							Ext.getCmp('ReabAratCombo').selectedIndex = -1;
							Ext.getCmp('ReabAratCombo').setValue('Введите параметр');
							Ext.getCmp('missing_hand').setValue(false);
							Ext.getCmp('Reab_ARAT_Parameter').setText("");

							var a_param = [];
							// var record ;
							a_param = DataScale.scaleParam.split(';');
							var a_name_parametr = new Array();
							var parameters = 1;
							var cKey = form.ObjScale.SprScale[0].ScaleParameterType_id;
							a_name_parametr.push([form.ObjScale.SprScale[0].ScaleParameterType_Name, form.ObjScale.SprScale[0].ScaleParameterType_id]);
							for (var ii = 0; ii < Object.keys(form.ObjScale.SprScale).length; ii++)
							{
								if (form.ObjScale.SprScale[ii].ScaleParameterType_id != cKey)
								{
									cKey = form.ObjScale.SprScale[ii].ScaleParameterType_id;
									a_name_parametr.push([form.ObjScale.SprScale[ii].ScaleParameterType_Name, form.ObjScale.SprScale[ii].ScaleParameterType_id]);
									parameters++;
								}
							}

							Ext.getCmp('ReabGrid_ARATScale').getGrid().store.removeAll();
							Ext.getCmp('ReabGrid_ARATScale').noHand = true;

							var name_group = "";
							//console.log('a_param=',a_param);
							//console.log('a_name_parametr=',a_name_parametr);

							var hand_right = "";
							var hand_right_Weight = "";
							var hand_left = "";
							var hand_left_Weight = "";
							var hand_left_id = "";
							var hand_right_id = "";
//
							for (var j = 1; j <= parameters; j++)
							{
								//Определение группы
								if (j < 7)
								{
									name_group = "1 раздел: Захват пятью пальцами";
								} else
								{
									if (j < 11)
									{
										name_group = "2 раздел: Удержание цилиндрического тела";
									} else
									{
										if (j < 16)
										{
											name_group = "3 раздел: Пинцетообразный захват";
										} else
										{
											name_group = "4 раздел: Крупная моторика";
										}

									}
								}

								var a_param1 = [];
								a_param1 = a_param[j - 1].split('-');
								// console.log('a_param1=',a_param1);
								// Работа с правой рукой
								if (a_param1[1] !== " ")
								{
									//Ищем значение в справочнике
									for (var ii = 0; ii < Object.keys(form.ObjScale.SprScale).length; ii++)
									{
										if (form.ObjScale.SprScale[ii].ScaleParameterType_id == a_param1[0] && form.ObjScale.SprScale[ii].ScaleParameterResult_id == a_param1[1])
										{
											hand_right = form.ObjScale.SprScale[ii].ScaleParameterResult_Name;
											hand_right_id = form.ObjScale.SprScale[ii].ScaleParameterResult_id;
											hand_right_Weight = form.ObjScale.SprScale[ii].ScaleParameterResult_Value;
											break;
										}
									}
								} else
								{
									hand_right = "Отсутствует";
									hand_right_id = "";
									hand_right_Weight = "";

								}
								// Работа с левой рукой
								if (a_param1[2] !== " ")
								{
									//Ищем значение в справочнике
									for (var ii = 0; ii < Object.keys(form.ObjScale.SprScale).length; ii++)
									{
										if (form.ObjScale.SprScale[ii].ScaleParameterType_id == a_param1[0] && form.ObjScale.SprScale[ii].ScaleParameterResult_id == a_param1[2])
										{
											hand_left = form.ObjScale.SprScale[ii].ScaleParameterResult_Name;
											hand_left_id = form.ObjScale.SprScale[ii].ScaleParameterResult_id;
											hand_left_Weight = form.ObjScale.SprScale[ii].ScaleParameterResult_Value;
											break;
										}
									}
								} else
								{
									hand_left = "Отсутствует";
									hand_left_id = "";
									hand_left_Weight = "";

								}

								Ext.getCmp('ReabGrid_ARATScale').getGrid().store.insert(j, [new Ext.data.Record({
									Id: j,
									ScaleParameterType_id: a_name_parametr[j - 1][1],
									ScaleParameterType_Name: a_name_parametr[j - 1][0],
									Section: name_group,
									Hand_right: hand_right,
									Hand_right_Weight: hand_right_Weight,
									Hand_left: hand_left,
									Hand_left_Weight: hand_left_Weight,
									Hand_left_id: hand_left_id,
									Hand_right_id: hand_right_id

								})]);


							}

							form.GraceTotals(ScaleSysNick, 0);
							Ext.getCmp('FirstPanel' + ScaleSysNick).expand();
						}
					},
					// Удаление измерения по шкале
					DeleteScaleData: function () {
						//alert('Будем удалять');
						var form = Ext.getCmp('ufa_personReabRegistryWindow');

						// Отсекаем закрытые этапы
						if (form.GridReabObjects.getGrid().getSelectionModel().getSelected().get('OutCause_id') != 0)
						{
							sw.swMsg.alert(lang['soobschenie'], 'Удадление шкал невозможно. Этап закрыт!');
							return
						}

						//Поиск требуемой записи
						var nRecord = null;
						var rr13 = Ext.getCmp('FillindScaleDateReab').value.substr(6, 4) + '-' + Ext.getCmp('FillindScaleDateReab').value.substr(3, 2) + '-' + Ext.getCmp('FillindScaleDateReab').value.substr(0, 2) + ' ';
						var uu12 = Ext.getCmp('FillindScaleTimeReab').getValue();
						rr13 = rr13 + uu12;
						// console.log('rr14=',rr13);
						for (var k in form.ScalesDatesTreePanel.objDateScale)
						{
							if (form.ScalesDatesTreePanel.objDateScale[k].setDate.date.substr(0, 16) == rr13)
							{
								nRecord = k;
								break;
							}
						}
						if (nRecord >= 0)
						{ //Продолжаем работать
							//Контроль на правомочность удаления
							if (form.ScalesDatesTreePanel.objDateScale[k].MedPersonal == getGlobalOptions().medpersonal_id || isSuperAdmin())
							{
								//Продолжаем удалять
								//работа с датой и временем
								var rr = Ext.getCmp('FillindScaleDateReab').getValue();
								var uu = Ext.getCmp('FillindScaleTimeReab').getValue();
								rr.setHours(parseInt(uu.substr(0, 2)));
								rr.setMinutes(parseInt(uu.substr(3, 2)));
								var oGridReabScales = Ext.getCmp('GridReabScales').getGrid().getSelectionModel().getSelected().get('ScaleType_SysNick');

								if (oGridReabScales == 'renkin' || oGridReabScales == 'rivermid' || oGridReabScales == 'glasgow' || oGridReabScales == 'Harris' ||
										oGridReabScales == 'Alarm_HADS' || oGridReabScales == 'Depression_HADS' || oGridReabScales == 'МоСА' || oGridReabScales == 'GRACE' ||
										oGridReabScales == 'Killip' || oGridReabScales == 'Ashworth' || oGridReabScales == 'Hauser' || oGridReabScales == 'Berg' ||
										oGridReabScales == 'Frenchay' || oGridReabScales == 'VAScale' || oGridReabScales == 'MedResCouncil' || oGridReabScales == 'Bartel' ||
										oGridReabScales == 'Vasserman' || oGridReabScales == 'FIM' || oGridReabScales == 'dysarthria' || oGridReabScales == 'Lequesne' ||
										oGridReabScales == 'rivermid_DAA' || oGridReabScales == 'ARAT' || oGridReabScales == 'nihss')
								{
									//console.log('Удаление=');
									var loadMask = new Ext.LoadMask(this.getEl(), {msg: lang['udalenie_zapisi']});
									loadMask.show();

									Ext.Ajax.request({
										url: '?c=Ufa_Reab_Register_User&m=deleteRegistrScale',
										params: {
											Person_id: form.Person_id,
											DirectType_id: form.DirectType_id,
											ReabEvent_id: form.GridReabObjects.getGrid().getSelectionModel().getSelected().data.ReabEvent_id,
											Scale_SysNick: oGridReabScales,
											ReabScale_setDate: rr,
											MedPersonal_did: getGlobalOptions().medpersonal_id,
											Lpu_did: getGlobalOptions().lpu_id
										},
										callback: function (options, success, response)
										{
											loadMask.hide(); // Обязательно сделать
											//console.log('success=',success);
											// console.log('response=',response);

											if (success == true)
											{
												var response_obj = Ext.util.JSON.decode(response.responseText);
												if (response_obj.success == true)
												{
													// Перерисовка результатов - грузим последнюю актуальную

													if (oGridReabScales == 'renkin' || oGridReabScales == 'rivermid' || oGridReabScales == 'Killip' ||
															oGridReabScales == 'Ashworth' || oGridReabScales == 'Hauser')
													{
														Ext.getCmp('ReabScaleGrid').setDisabled(true);
													}
													if (oGridReabScales == 'glasgow')
													{
														Ext.getCmp('QuestionPanel_' + oGridReabScales).setDisabled(true);
													}
													if (oGridReabScales == 'VAScale')
													{
														Ext.getCmp('ReabGrid_VAScale').getGrid().store.removeAll();
														Ext.getCmp('GridResult').setDisabled(true);
														Ext.getCmp('VASc_Panel').hide();
													}
													if (oGridReabScales == 'MedResCouncil')
													{
														Ext.getCmp('ReabGrid_MRCScale').getGrid().store.removeAll();
														Ext.getCmp('GridMRCScResult').setDisabled(true);
														Ext.getCmp('MRCSc_Panel').hide();
													}
													if (oGridReabScales == 'Alarm_HADS' || oGridReabScales == 'Depression_HADS' || oGridReabScales == 'Berg' || oGridReabScales == 'Frenchay' || oGridReabScales == 'Bartel' ||
															oGridReabScales == 'Vasserman' || oGridReabScales == 'FIM' || oGridReabScales == 'dysarthria' || oGridReabScales == 'Lequesne' || oGridReabScales == 'rivermid_DAA' ||
															oGridReabScales == 'Harris' || oGridReabScales == 'nihss')
													{
														Ext.getCmp('Question_' + oGridReabScales).setDisabled(true);
													}
													var tree = form.ScalesDatesTreePanel;
													if (response_obj.listScales.length > 0)
													{
														//form.getEl().mask(lang['podojdite_idet_sohranenie']).show();
														form.getEl().mask(lang['udalenie_zapisi']).show();

														form.ScaleLoadData(oGridReabScales, response_obj.listScales[0]);


														tree.nDateScale = 0; //Надо отобразить позднюю дату
														form.ScalesDatesTreePanel.objDateScale = response_obj.listScales;

//                               for(j = 0; j < tree.getRootNode().childNodes.length; j++)
//                               {
//                                   tree.getRootNode().childNodes[j].remove(true);
//                               }
														tree.getLoader().load(tree.getRootNode());  //Перегрузка дат
														Ext.getCmp('printReabScaleButton').setDisabled(false);  //Кнопка печати шкалы
													} else
													{
														//нет измерений
														form.ScalesDatesTreePanel.objDateScale = null;
														form.ScalesDatesTreePanel.nDateScale = null;

														//Пустая шкала

														if (oGridReabScales == 'renkin' || oGridReabScales == 'rivermid' || oGridReabScales == 'Killip' ||
																oGridReabScales == 'Ashworth' || oGridReabScales == 'Hauser')
														{
															for (j = 0; j < Ext.getCmp('ReabScaleGrid').getGrid().getStore().data.items.length; j++)
															{
																var vrecord = Ext.getCmp('ReabScaleGrid').ViewGridPanel.getStore().data.items[j].data;
																if (vrecord.selrow == '1')
																{
																	Ext.getCmp('ReabScaleGrid').getGrid().getSelectionModel().selectRow(j);
																	vrecord = Ext.getCmp('ReabScaleGrid').getGrid().getSelectionModel().getSelected();
																	vrecord.set('selrow', '0');
																	vrecord.commit();
																	Ext.getCmp('ReabScaleGrid').getGrid().getSelectionModel().deselectRow(j);
																	break;
																}
															}
														}
														if (oGridReabScales == 'glasgow')
														{
															Ext.getCmp('QuestionPanel_' + oGridReabScales).setDisabled(false);
															Ext.getCmp('Param_1_id').selectedIndex = -1;
															Ext.getCmp('Param_1_id').setValue('Введите параметр');
															Ext.getCmp('Param_2_id').selectedIndex = -1;
															Ext.getCmp('Param_2_id').setValue('Введите параметр');
															Ext.getCmp('Param_3_id').selectedIndex = -1;
															Ext.getCmp('Param_3_id').setValue('Введите параметр');
															Ext.getCmp('eye_response').setValue('');
															Ext.getCmp('verbal_response').setValue('');
															Ext.getCmp('motor_response').setValue('');
															Ext.getCmp('ReabScaleValue_id').setValue('');
															Ext.getCmp('Reab' + oGridReabScales + 'Summ').hide();
														}

														if (oGridReabScales == 'Harris' || oGridReabScales == 'Alarm_HADS' || oGridReabScales == 'Depression_HADS' || oGridReabScales == 'Berg' ||
																oGridReabScales == 'Frenchay' || oGridReabScales == 'Bartel' || oGridReabScales == 'Vasserman' || oGridReabScales == 'FIM' ||
																oGridReabScales == 'dysarthria' || oGridReabScales == 'Lequesne' || oGridReabScales == 'rivermid_DAA' || oGridReabScales == 'nihss')
														{

															if (oGridReabScales == 'Berg' || oGridReabScales == 'Vasserman' || oGridReabScales == 'FIM' || oGridReabScales == 'dysarthria' || oGridReabScales == 'Lequesne' ||
																	oGridReabScales == 'rivermid_DAA' || oGridReabScales == 'Harris' || oGridReabScales == 'nihss')
															{
																Ext.getCmp('Reab' + oGridReabScales + 'Summ').hide();
																if (oGridReabScales == 'FIM')
																{
																	// Суммы по группе
																	Ext.getCmp('ReabFIMMotor_Func').setValue("");
																	Ext.getCmp('ReabFIMIntellig').setValue("");
																} else
																{
																	Ext.getCmp('Reab' + oGridReabScales + 'Total').setValue("");
																	if (oGridReabScales == 'Lequesne')
																	{
																		Ext.getCmp(oGridReabScales + 'Type_of_joint_id').selectedIndex = -1;
																		Ext.getCmp(oGridReabScales + 'Type_of_joint_id').setValue('Введите параметр');
																		Ext.getCmp(oGridReabScales + 'Side_id').selectedIndex = -1;
																		Ext.getCmp(oGridReabScales + 'Side_id').setValue('Введите параметр');
																	}
																}
															}
															Ext.getCmp('Question_' + oGridReabScales).setDisabled(true);


															//Определяем кол-во параметров
															var nRec = 1;
															var cKey = form.ObjScale.SprScale[0].ScaleParameterType_id;
															for (var ii = 0; ii < Object.keys(form.ObjScale.SprScale).length; ii++)
															{
																if (form.ObjScale.SprScale[ii].ScaleParameterType_id != cKey)
																{
																	cKey = form.ObjScale.SprScale[ii].ScaleParameterType_id;
																	nRec++;
																}
															}
															for (var k = 1; k <= nRec; k++)
															{
																Ext.getCmp(oGridReabScales + 'Param_' + k).selectedIndex = -1;
																Ext.getCmp(oGridReabScales + 'Param_' + k).setValue('Введите параметр');
																Ext.getCmp(oGridReabScales + 'Field_' + k).setValue("");
															}
															Ext.getCmp('ReabScaleValue_id').setValue('');
														}

														if (oGridReabScales == 'GRACE')
														{
															Ext.getCmp('FirstPanelGRACE').setDisabled(true);
															Ext.getCmp('ReabGraceSumm').hide();
															Ext.getCmp('ReabGraceParameters').hide();

														}

														if (oGridReabScales == 'VAScale')
														{
															Ext.getCmp('ReabGrid_VAScale').getGrid().store.removeAll();
															Ext.getCmp('GridResult').setDisabled(true);
															Ext.getCmp('VASc_Panel').hide();
															Ext.getCmp('ReabGrid_VAScale').getGrid().tbar.dom.firstChild.firstChild.firstChild.firstChild.lastChild.firstChild.innerText = '0 / 0';
														}
														if (oGridReabScales == 'MedResCouncil')
														{
															Ext.getCmp('ReabGrid_MRCScale').getGrid().store.removeAll();
															Ext.getCmp('GridMRCScResult').setDisabled(true);
															Ext.getCmp('MRCSc_Panel').hide();
															Ext.getCmp('ReabGrid_MRCScale').getGrid().tbar.dom.firstChild.firstChild.firstChild.firstChild.lastChild.firstChild.innerText = '0 / 0';
														}

														if (oGridReabScales == 'ARAT')
														{
															//Обнуление GRIDa
															Ext.getCmp('ReabGrid_ARATScale').noHand = true;

															for (var ii = 0; ii < form.ObjScale.SprScale.length; ii++)
															{
																Ext.getCmp('ReabGrid_ARATScale').getGrid().getSelectionModel().selectRow(ii);
																var record = Ext.getCmp('ReabGrid_ARATScale').getGrid().getSelectionModel().getSelected();
																record.set("Hand_right", "");
																record.set("Hand_right_Weight", "");
																record.set("Hand_left", "");
																record.set("Hand_left_Weight", "");
																record.set("Hand_left_id", "");
																record.set("Hand_right_id", "");
																record.commit();
															}
															Ext.getCmp('ReabGrid_ARATScale').noHand = false;
															Ext.getCmp('ReabARATHandSide').selectedIndex = -1;
															Ext.getCmp('ReabARATHandSide').setValue('Введите параметр');
															Ext.getCmp('ReabAratCombo').selectedIndex = -1;
															Ext.getCmp('ReabAratCombo').setValue('Введите параметр');
															Ext.getCmp('missing_hand').setValue(false);
															Ext.getCmp('Reab_ARAT_Parameter').setText("");
															Ext.getCmp('ReabARATinput').setDisabled(true);
															Ext.getCmp('ReabARATSumm').hide();
														}

														// закрыть панель дат
														Ext.getCmp('scaleReabCenterPanel').hide();
														// текущая дата  // обнуление часов
														Ext.getCmp('FillindScaleDateReab').setValue(new Date());
														Ext.getCmp('FillindScaleTimeReab').setValue('');
														Ext.getCmp('ReabScaleValue_id').setValue('');
														Ext.getCmp('printReabScaleButton').setDisabled(true);  //Кнопка печати шкалы

													}

													//  console.log('Конец=');
												}
											} else {
												form.getEl().mask().hide();
												sw.swMsg.alert(lang['soobschenie'], lang['oshibka_pri_vyipolnenii_zaprosa_k_serveru']);
											}

										}
									})
								} else
								{
									//Изменять при добавлении
									sw.swMsg.alert(lang['soobschenie'], 'Для данной шкалы нет реализации');
								}
							} else
							{
								sw.swMsg.alert(lang['soobschenie'], lang['u_vas_net_prav_na_udalenie']);

							}
						} else
						{
							sw.swMsg.alert(lang['soobschenie'], lang['oshibka_pri_vyipolnenii_zaprosa_k_serveru']);
						}

						Ext.getCmp('deleteReabScaleDataButton').setDisabled(true);
					},
					//Сохранение измерений по шкале
					SaveScaleData: function () {
						var form = Ext.getCmp('ufa_personReabRegistryWindow');
						var scaleName = Ext.getCmp('GridReabScales').getGrid().getSelectionModel().getSelected().get('ScaleType_SysNick');
						var ScaleParameter = '';
						var ScaleValue = '';
						var scale_Refinement = ''; //для уточнений шкалы nihss

						if (Ext.getCmp('FillindScaleTimeReab').isValid() == false)
						{
							if (Ext.getCmp('FillindScaleTimeReab').getValue() == '')
							{
								form.showMsg(lang['zadat_vremya']);
								return;
							}
							form.showMsg('Не верно указано время измерения');
							return;
						}

						//Ограничение  на измерение 1 раз в сутки (кроме GRACE)
						if (scaleName != 'GRACE' && scaleName != 'Lequesne')
						{
							//Контроль наличия шкалы на дату заполнения
							if (form.ScalesDatesTreePanel.objDateScale != null)
							{
								var cDate1 = Ext.getCmp('FillindScaleDateReab').value.substr(6, 4) + '-' + Ext.getCmp('FillindScaleDateReab').value.substr(3, 2) + '-' + Ext.getCmp('FillindScaleDateReab').value.substr(0, 2);
								var nDateScales = Ext.getCmp('ufa_personReabRegistryWindow').ScalesDatesTreePanel.objDateScale.length;
								for (var kk = 0; kk < nDateScales; kk++)
								{
									var cDate = Ext.getCmp('ufa_personReabRegistryWindow').ScalesDatesTreePanel.objDateScale[kk].setDate.date.substr(0, 10);
									// console.log('cDate=',cDate);
									// console.log('cDate1=',cDate1);
									if (cDate1 === cDate)
									{
										sw.swMsg.alert(lang['soobschenie'], 'Данная шкала на указанную дату уже заполнена!');
										return;
									}
								}
							}
						}

						//работа с датой и временем
						var rr = Ext.getCmp('FillindScaleDateReab').getValue();
						var uu = Ext.getCmp('FillindScaleTimeReab').getValue();
						rr.setHours(parseInt(uu.substr(0, 2)));
						rr.setMinutes(parseInt(uu.substr(3, 2)));

						//Формирование данных для сохранения
						// Рэнкин и Ривермид
						if (scaleName == 'renkin' || scaleName == 'rivermid' || scaleName == 'Killip' ||
								scaleName == 'Ashworth' || scaleName == 'Hauser')
						{
							if (Ext.getCmp('ReabScaleGrid').getGrid().getSelectionModel().selections.items.length == 0)
							{
								sw.swMsg.alert(lang['soobschenie'], lang['ne_vyibrana_zapis_iz_spiska']);
								return;
							}
							//Формируем данные для сохранения
							ScaleParameter = Ext.getCmp('ReabScaleGrid').getGrid().getSelectionModel().getSelected().get('ScaleParameterResult_id');
							ScaleValue = Ext.getCmp('ReabScaleGrid').getGrid().getSelectionModel().getSelected().get('ScaleParameterResult_Value');
						}

						//Grace
						if (scaleName == 'GRACE')
						{
							var cMessage = 'Не указано значение:';
							if (Ext.getCmp('Creating_OKS_id').getValue() == 'Введите параметр' || Ext.getCmp('Creating_OKS_id').getValue() == "")
							{
								cMessage = cMessage + '<br>' + '1. ' + Ext.getCmp('Creating_OKS_id').fieldLabel;
							} else
							{
								if (Ext.getCmp('ReabGraceCreatinine').getValue() == 'Введите параметр' || Ext.getCmp('ReabGraceCreatinine').getValue() == "")
								{
									cMessage = cMessage + '<br>' + '1. ' + Ext.getCmp('ReabGraceCreatinine').fieldLabel;
								}
								if (Ext.getCmp('ReabGraceHeartRate').getValue() == 'Введите параметр' || Ext.getCmp('ReabGraceHeartRate').getValue() == "")
								{
									cMessage = cMessage + '<br>' + '2. ' + Ext.getCmp('ReabGraceHeartRate').fieldLabel;
								}
								if (Ext.getCmp('ReabGraceKillipT').getValue() == 'Введите параметр' || Ext.getCmp('ReabGraceKillipT').getValue() == "")
								{
									cMessage = cMessage + '<br>' + '3. ' + Ext.getCmp('ReabGraceKillipT').fieldLabel;
								}

								if (Ext.getCmp('ReabGraceArterial_pressure').getValue() == 'Введите параметр' || Ext.getCmp('ReabGraceArterial_pressure').getValue() == "")
								{
									cMessage = cMessage + '<br>' + '4. ' + Ext.getCmp('ReabGraceArterial_pressure').fieldLabel;
								}
								if (Ext.getCmp('GraceIncreasedMarkers_Field').getValue() == "" )
								{
									cMessage = cMessage + '<br>' + '5. ' + Ext.getCmp('GraceIncreasedMarkers_Field').fieldLabel;
								}
								if (Ext.getCmp('GraceHeartFailure_Field').getValue() == "")
								{
									cMessage = cMessage + '<br>' + '6. ' + Ext.getCmp('GraceHeartFailure_Field').fieldLabel;
								}
								if (Ext.getCmp('GraceDeviationST_Field').getValue() == "")
								{
									cMessage = cMessage + '<br>' + '7. ' + Ext.getCmp('GraceDeviationST_Field').fieldLabel;
								}

							}

							if (cMessage != 'Не указано значение:')
							{
								//Сообщаем
								form.showMsg(cMessage);
								return;
							} else
							{
								//Обрабатываем результаты
								if (Ext.getCmp('Creating_OKS_id').getValue() == 415)
								{
									var cScale = 'GRACE-ST';
									var increas_markers = "91";
									var deviation_ST = "92";
									var heart_failure = "93";
								} else
								{
									var cScale = 'GRACE+ST';
									var increas_markers = "99";
									var deviation_ST = "100";
									var heart_failure = "101";
								}
								//Возраст
								Ext.getCmp('ufa_personReabRegistryWindow').PersonInfoPanelReab.DataView.store.data.items[0].json.Person_Age;

								var Person_Birthday = Ext.getCmp('ufa_personReabRegistryWindow').PersonInfoPanelReab.DataView.store.data.items[0].json.Person_Birthday;
								var age = Ext.getCmp('ufa_personReabRegistryWindow').getAge(Person_Birthday, Ext.getCmp('FillindScaleDateReab').getValue());
								Ext.getCmp('FirstPanelGRACE').setAge(cScale, form.ObjScale.SprScale, age);

								// интерпретация результатов (процедура на основной форме)
								var nGRACEResult = Ext.getCmp('ReabScaleValue_id').getValue();
								//console.log('nGRACEResult=',nGRACEResult);
								//console.log('cScale=',cScale);

								form.GraceTotals(cScale, nGRACEResult);
								//Готовим данные
								ScaleParameter = '';
								ScaleParameter = Ext.getCmp('Creating_OKS_id').getValue() + ';' + Ext.getCmp('ReabGraceAge').getValue() + ';' +
										Ext.getCmp('ReabGraceCreatinine').getStore().data.items[Ext.getCmp('ReabGraceCreatinine').selectedIndex].data.ScaleParameterType_id + '-' +
										Ext.getCmp('ReabGraceCreatinine').getStore().data.items[Ext.getCmp('ReabGraceCreatinine').selectedIndex].data.ScaleParameterResult_id + ';' +
										Ext.getCmp('ReabGraceHeartRate').getStore().data.items[Ext.getCmp('ReabGraceHeartRate').selectedIndex].data.ScaleParameterType_id + '-' +
										Ext.getCmp('ReabGraceHeartRate').getStore().data.items[Ext.getCmp('ReabGraceHeartRate').selectedIndex].data.ScaleParameterResult_id + ';' +
										Ext.getCmp('ReabGraceKillipT').getStore().data.items[Ext.getCmp('ReabGraceKillipT').selectedIndex].data.ScaleParameterType_id + '-' +
										Ext.getCmp('ReabGraceKillipT').getStore().data.items[Ext.getCmp('ReabGraceKillipT').selectedIndex].data.ScaleParameterResult_id + ';' +
										Ext.getCmp('ReabGraceArterial_pressure').getStore().data.items[Ext.getCmp('ReabGraceArterial_pressure').selectedIndex].data.ScaleParameterType_id + '-' +
										Ext.getCmp('ReabGraceArterial_pressure').getStore().data.items[Ext.getCmp('ReabGraceArterial_pressure').selectedIndex].data.ScaleParameterResult_id + ';';
								if (Ext.getCmp('IncreasedMarkers').checked === true)
								{
									if (cScale == 'GRACE-ST')
									{
										ScaleParameter = ScaleParameter + increas_markers + "-448;";
									} else
									{
										ScaleParameter = ScaleParameter + increas_markers + "-487;";
									}
									//ScaleParameter = ScaleParameter + "6-2;";
								} else
								{
									if (cScale == 'GRACE-ST')
									{
										ScaleParameter = ScaleParameter + increas_markers + "-447;";
									} else
									{
										ScaleParameter = ScaleParameter + increas_markers + "-486;";
									}
									//ScaleParameter = ScaleParameter + "6-1;";
								}
								if (Ext.getCmp('DeviationST').checked === true)
								{
									if (cScale == 'GRACE-ST')
									{
										ScaleParameter = ScaleParameter + deviation_ST + "-450;";
									} else
									{
										ScaleParameter = ScaleParameter + deviation_ST + "-489;";
									}
									//ScaleParameter = ScaleParameter + "7-2;";
								} else
								{
									if (cScale == 'GRACE-ST')
									{
										ScaleParameter = ScaleParameter + deviation_ST + "-449;";
									} else
									{
										ScaleParameter = ScaleParameter + deviation_ST + "-488;";
									}
									//ScaleParameter = ScaleParameter + "7-1;";
								}
								if (Ext.getCmp('HeartFailure').checked === true)
								{
									if (cScale == 'GRACE-ST')
									{
										ScaleParameter = ScaleParameter + heart_failure + "-452;";
									} else
									{
										ScaleParameter = ScaleParameter + heart_failure + "-491;";
									}
									//ScaleParameter = ScaleParameter + "8-2;";
								} else
								{
									if (cScale == 'GRACE-ST')
									{
										ScaleParameter = ScaleParameter + heart_failure + "-451;";
									} else
									{
										ScaleParameter = ScaleParameter + heart_failure + "-490;";
									}
									//ScaleParameter = ScaleParameter + "8-1;";
								}
								ScaleValue = Ext.getCmp('ReabScaleValue_id').getValue();
							}
							//  console.log('ScaleParameterGrace=',ScaleParameter);
						}
						//Глазго
						if (scaleName == 'glasgow')
						{
							var cMessage = 'Не указано значение:';
							// Валидация Glsgow
							// console.log('Param_1_id=',Ext.getCmp('Param_1_id').selectedIndex);
							if (Ext.getCmp('Param_1_id').getValue() == 'Введите параметр' || Ext.getCmp('Param_1_id').getValue() == "")
							{
								cMessage = cMessage + '<br>' + '1. ' + Ext.getCmp('cName1').text;
							}
							if (Ext.getCmp('Param_2_id').getValue() == 'Введите параметр' || Ext.getCmp('Param_2_id').getValue() == "")
							{
								cMessage = cMessage + '<br>' + '2. ' + Ext.getCmp('cName2').text;
							}
							if (Ext.getCmp('Param_3_id').getValue() == 'Введите параметр' || Ext.getCmp('Param_3_id').getValue() == "")
							{
								cMessage = cMessage + '<br>' + '3. ' + Ext.getCmp('cName3').text;
							}

							if (cMessage != 'Не указано значение:')
							{
								//Сообщаем
								form.showMsg(cMessage);
								return;
							} else
							{
								//Готовим данные
								ScaleParameter = '';
								ScaleParameter = Ext.getCmp('Param_1_id').getStore().data.items[Ext.getCmp('Param_1_id').selectedIndex].data.ScaleParameterType_id + '-' +
										Ext.getCmp('Param_1_id').getStore().data.items[Ext.getCmp('Param_1_id').selectedIndex].data.ScaleParameterResult_id + ';' +
										Ext.getCmp('Param_2_id').getStore().data.items[Ext.getCmp('Param_2_id').selectedIndex].data.ScaleParameterType_id + '-' +
										Ext.getCmp('Param_2_id').getStore().data.items[Ext.getCmp('Param_2_id').selectedIndex].data.ScaleParameterResult_id + ';' +
										Ext.getCmp('Param_3_id').getStore().data.items[Ext.getCmp('Param_3_id').selectedIndex].data.ScaleParameterType_id + '-' +
										Ext.getCmp('Param_3_id').getStore().data.items[Ext.getCmp('Param_3_id').selectedIndex].data.ScaleParameterResult_id;
								ScaleValue = Ext.getCmp('ReabScaleValue_id').getValue();
								// Интерпретация результата
								var glasgowResult = Ext.getCmp('ReabScaleValue_id').getValue();
								form.GraceTotals(scaleName, glasgowResult);
							}

						}
						if (scaleName == 'Harris' || scaleName == 'Alarm_HADS' || scaleName == 'Depression_HADS' || scaleName == 'МоСА' || scaleName == 'Berg' ||
								scaleName == 'Frenchay' || scaleName == 'Bartel' || scaleName == 'Vasserman' || scaleName == 'FIM' || scaleName == 'dysarthria' ||
								scaleName == 'Lequesne' || scaleName == 'rivermid_DAA' || scaleName == 'nihss')
						{
							//Определяем кол-во параметров
							var nRec = 1;
							var cKey = form.ObjScale.SprScale[0].ScaleParameterType_id;

							// console.log('cKey=',cKey);
							for (var ii = 0; ii < Object.keys(form.ObjScale.SprScale).length; ii++)
							{
								if (form.ObjScale.SprScale[ii].ScaleParameterType_id != cKey)
								{
									cKey = form.ObjScale.SprScale[ii].ScaleParameterType_id;
									nRec++;
								}
							}
							// Валидация
							var cMessage = 'Не указано значение:';
							if (scaleName == 'Lequesne')
							{
								if (Ext.getCmp(scaleName + 'Type_of_joint_id').getValue() == 'Введите параметр' || Ext.getCmp(scaleName + 'Type_of_joint_id').getValue() == "")
								{
									cMessage = cMessage + '<br>' + Ext.getCmp(scaleName + 'Type_of_joint_id').fieldLabel;
								}
								if (Ext.getCmp(scaleName + 'Side_id').getValue() == 'Введите параметр' || Ext.getCmp(scaleName + 'Side_id').getValue() == "")
								{
									cMessage = cMessage + '<br>' + Ext.getCmp(scaleName + 'Side_id').fieldLabel;
								}
							}
							var pref = "";
							for (var ii = 1; ii <= nRec; ii++)
							{
								pref = "";
								if (scaleName == 'FIM')
								{


									if (ii < 13)
									{
										pref = pref + '<br>Двигательные функции: ';
										switch (ii) {
											case 1:
											case 2:
											case 3:
											case 4:
											case 5:
											case 6:
												pref = pref + "Самообслуживание: ";
												break;
											case 7:
											case 8:
												pref = pref + "Контроль функции тазовых органов: ";
												break;
											case 9:
											case 10:
											case 11:
												pref = pref + "Перемещение: ";
												break;
											case 12:
											case 13:
												pref = pref + "Подвижностье: ";
												break;
										}
										;

									} else
									{
										pref = pref + '<br>Интеллект: ';
										switch (ii) {
											case 14:
											case 15:
												pref = pref + "Общение: ";
												break;
											case 16:
											case 17:
											case 18:
												pref = pref + "Социальная активность: ";
												break;
										}
										;
									}

								}
								if (scaleName == 'dysarthria')
								{
									switch (ii) {
										case 1:
										case 2:
											pref = "<br>1.Оценка V пары ЧМН: ";
											break;
										case 3:
										case 4:
										case 5:
											pref = "<br>2.Оценка VII пары ЧМН: ";
											break;
										case 6:
										case 7:
										case 8:
										case 9:
										case 10:
										case 11:
											pref = "<br>3.Оценка XI и XII пар ЧМН: ";
											break;
										case 12:
										case 13:
										case 14:
										case 15:
											pref = "<br>4. Оценка IX и X пар ЧМН: ";
											break;
										case 16:
										case 17:
										case 18:
										case 19:
											pref = "<br>5.Оценка голоса, темпа, ритма, интонационно-мелодической окраски речи и звукопроизношения: ";
											break;
									}
									;
								}
								if (scaleName == 'Lequesne')
								{
									switch (ii) {
										case 1:
										case 2:
										case 3:
										case 4:
										case 5:
											pref = "<br>1.Боль или дискомфорт: ";
											break;
										case 6:
										case 7:
											pref = "<br>2.Максимальная дистанция передвижения: ";
											break;
										case 8:
										case 9:
										case 10:
										case 11:
											pref = "<br>3 Повседневная активность: ";
											break;
									}
									;
								}
								if (scaleName == 'rivermid_DAA')
								{
									switch (ii) {
										case 1:
										case 2:
										case 3:
										case 4:
										case 5:
										case 6:
										case 7:
										case 8:
										case 9:
										case 10:
										case 11:
										case 12:
										case 13:
										case 14:
										case 15:
										case 16:
											pref = "<br>1.Самообслуживание: ";
											break;
										case 17:
										case 18:
										case 19:
										case 20:
										case 21:
										case 22:
										case 23:
										case 24:
										case 25:
											pref = "<br>2.Домашнее хозяйство I: ";
											break;
										case 26:
										case 27:
										case 28:
										case 29:
										case 30:
										case 31:
											pref = "<br>3.Домашнее хозяйство II: ";
											break;
									}
									;
								}


								if (Ext.getCmp(scaleName + 'Param_' + ii).getValue() == 'Введите параметр' || Ext.getCmp(scaleName + 'Param_' + ii).getValue() == "")
								{
									cMessage = cMessage + pref + '<br>' + ii + '. ' + Ext.getCmp('cName_' + ii).text;
								}

								if (scaleName == 'nihss')
								{
									switch (ii) {
										case 7:
										case 8:
										case 9:
										case 10:
										case 11:
										case 14:
											var nihss_Id = Ext.getCmp('nihssParam_' + ii).id;
											var nihss_Value = Ext.getCmp('nihssParam_' + ii).getValue();
											if (nihss_Id == "nihssParam_7" && nihss_Value == 1177 || nihss_Id == "nihssParam_8" && nihss_Value == 1183 ||
													nihss_Id == "nihssParam_9" && nihss_Value == 1189 || nihss_Id == "nihssParam_10" && nihss_Value == 1195 ||
													nihss_Id == "nihssParam_11" && nihss_Value == 1199 || nihss_Id == "nihssParam_14" && nihss_Value == 1210)
											{
												if (Ext.getCmp('nihssArea_' + ii).getValue() == "")
												{
													cMessage = cMessage + '<br>' + ii + '. ' + Ext.getCmp('cName_' + ii).text + " - отсутствует уточнение";
												}
												if (Ext.getCmp('nihssArea_' + ii).validate() == false)
												{
													cMessage = cMessage + '<br>' + ii + '. ' + Ext.getCmp('cName_' + ii).text + " - " + Ext.getCmp('nihssArea_' + ii).maxLengthText;
												}
											}

											break;
										default:
											pref = "";
											break;
									}
									;

								}
							}

							if (cMessage != 'Не указано значение:')
							{
								form.showMsg(cMessage);
								return;
							} else
							{
								if (scaleName == 'Berg' || scaleName == 'Vasserman' || scaleName == 'FIM' || scaleName == 'dysarthria' || scaleName == 'Lequesne' ||
										scaleName == 'rivermid_DAA' || scaleName == 'nihss' || scaleName == 'Alarm_HADS' || scaleName == 'Depression_HADS' || scaleName == 'Harris')
								{
									// Интерпретация результата
									var nGRACEResult = Ext.getCmp('ReabScaleValue_id').getValue();
									//console.log('nGRACEResult=',nGRACEResult);
									form.GraceTotals(scaleName, nGRACEResult);

									Ext.getCmp('FirstPanel' + scaleName).collapse();
								}
								//Подготовка объекта
								ScaleParameter = '';
								if (scaleName == 'Lequesne')
								{
									ScaleParameter = ScaleParameter + 'A-' +
											Ext.getCmp('LequesneType_of_joint_id').getStore().data.items[Ext.getCmp('LequesneType_of_joint_id').selectedIndex].data.ReabSpr_Elem_id + ';';
									ScaleParameter = ScaleParameter + 'B-' +
											Ext.getCmp('LequesneSide_id').getStore().data.items[Ext.getCmp('LequesneSide_id').selectedIndex].data.ReabSpr_Elem_id + ';';
								}

								for (var k = 1; k <= nRec; k++)
								{
									ScaleParameter = ScaleParameter + Ext.getCmp(scaleName + 'Param_' + k).getStore().data.items[Ext.getCmp(scaleName + 'Param_' + k).selectedIndex].data.ScaleParameterType_id + '-' +
											Ext.getCmp(scaleName + 'Param_' + k).getStore().data.items[Ext.getCmp(scaleName + 'Param_' + k).selectedIndex].data.ScaleParameterResult_id + ';';
									if (scaleName == 'nihss' && (k == 7 || k == 8 || k == 9 || k == 10 || k == 11 || k == 14))
									{
										var nihss_Id = Ext.getCmp('nihssParam_' + k).id;
										var nihss_Value = Ext.getCmp('nihssParam_' + k).getValue();
										if (nihss_Id == "nihssParam_7" && nihss_Value == 1177 || nihss_Id == "nihssParam_8" && nihss_Value == 1183 ||
												nihss_Id == "nihssParam_9" && nihss_Value == 1189 || nihss_Id == "nihssParam_10" && nihss_Value == 1195 ||
												nihss_Id == "nihssParam_11" && nihss_Value == 1199 || nihss_Id == "nihssParam_14" && nihss_Value == 1210)
										{
											scale_Refinement = scale_Refinement + Ext.getCmp('nihssParam_' + k).getStore().data.items[0].data.ScaleParameterType_id + '-' +
													Ext.getCmp(scaleName + 'Area_' + k).getValue() + ';';
											// console.log('rrrrrrrr=');
										}
									}


								}
								ScaleParameter = ScaleParameter.substr(0, ScaleParameter.lastIndexOf(';'));
								if (scale_Refinement != "")
								{
									scale_Refinement = scale_Refinement.substr(0, scale_Refinement.lastIndexOf(';'));
								}

								ScaleValue = Ext.getCmp('ReabScaleValue_id').getValue();
								//console.log('ScaleParameter=', ScaleParameter);
								//console.log('scale_Refinement=', scale_Refinement);
							}
							// return;
						}
						if (scaleName == 'VAScale')
						{
							nRec = Ext.getCmp('ReabGrid_VAScale').getGrid().getStore().data.items.length;
							if (nRec == 0)
							{
								sw.swMsg.alert(lang['soobschenie'], lang['otsutstvuyut_parametryi']);
								return;
							}
							//Подготовка объекта
							ScaleParameter = '';

							for (var k = 0; k < nRec; k++)
							{
								ScaleParameter = ScaleParameter + Ext.getCmp('ReabGrid_VAScale').getGrid().getStore().data.items[k].data.Localization_of_pain_id + '-' +
										Ext.getCmp('ReabGrid_VAScale').getGrid().getStore().data.items[k].data.VASValue_id + ';';
							}
							ScaleParameter = ScaleParameter.substr(0, ScaleParameter.lastIndexOf(';'));
							ScaleValue = '0';
							//   console.log('ScaleParameter=',ScaleParameter);
						}

						if (scaleName == 'ARAT')
						{
							//Валидация
							var a_left = new Array();
							var a_right = new Array();
							var mess_validat = 'Не указано значение:';
							var mess_validat_left = "";
							var mess_validat_right = "";
							nRec = Ext.getCmp('ReabGrid_ARATScale').getGrid().getStore().data.items.length;

							for (var k = 0; k < nRec; k++)
							{
								//Левая рука
								if (Ext.getCmp('ReabGrid_ARATScale').getGrid().getStore().data.items[k].data.Hand_left_id == "")
								{
									if (Ext.getCmp('ReabGrid_ARATScale').getGrid().getStore().data.items[k].data.Hand_left == "Отсутствует")
									{
										//Просчет отсутствия руки
										a_left.push(Ext.getCmp('ReabGrid_ARATScale').getGrid().getStore().data.items[k].data.Hand_left);
										mess_validat_left = mess_validat_left + "<br>" + Ext.getCmp('ReabGrid_ARATScale').getGrid().getStore().data.items[k].data.Section + ": " +
												Ext.getCmp('ReabGrid_ARATScale').getGrid().getStore().data.items[k].data.ScaleParameterType_id + '. ' +
												Ext.getCmp('ReabGrid_ARATScale').getGrid().getStore().data.items[k].data.ScaleParameterType_Name.replace(new RegExp("<br>", 'g'), " ");
									} else
									{
										mess_validat_left = mess_validat_left + "<br>" + Ext.getCmp('ReabGrid_ARATScale').getGrid().getStore().data.items[k].data.Section + ": " +
												Ext.getCmp('ReabGrid_ARATScale').getGrid().getStore().data.items[k].data.Id + '. ' +
												Ext.getCmp('ReabGrid_ARATScale').getGrid().getStore().data.items[k].data.ScaleParameterType_Name.replace(new RegExp("<br>", 'g'), " ");
									}
								}
								//Правая рука
								if (Ext.getCmp('ReabGrid_ARATScale').getGrid().getStore().data.items[k].data.Hand_right_id == "")
								{
									if (Ext.getCmp('ReabGrid_ARATScale').getGrid().getStore().data.items[k].data.Hand_right == "Отсутствует")
									{
										//Просчет отсутствия руки
										a_right.push(Ext.getCmp('ReabGrid_ARATScale').getGrid().getStore().data.items[k].data.Hand_right);
										mess_validat_right = mess_validat_right + "<br>" + Ext.getCmp('ReabGrid_ARATScale').getGrid().getStore().data.items[k].data.Section + ": " +
												Ext.getCmp('ReabGrid_ARATScale').getGrid().getStore().data.items[k].data.ScaleParameterType_id + '. ' +
												Ext.getCmp('ReabGrid_ARATScale').getGrid().getStore().data.items[k].data.ScaleParameterType_Name.replace(new RegExp("<br>", 'g'), " ");
									} else
									{
										mess_validat_right = mess_validat_right + "<br>" + Ext.getCmp('ReabGrid_ARATScale').getGrid().getStore().data.items[k].data.Section + ": " +
												Ext.getCmp('ReabGrid_ARATScale').getGrid().getStore().data.items[k].data.Id + '. ' +
												Ext.getCmp('ReabGrid_ARATScale').getGrid().getStore().data.items[k].data.ScaleParameterType_Name.replace(new RegExp("<br>", 'g'), " ");
									}
								}
							}

							if ((a_left.length > 0 && a_left.length != nRec) || (a_left.length == 0 && mess_validat_left != ""))
							{
								form.showMsg(mess_validat + "(левая рука)" + mess_validat_left);
								return;
							}
							//console.log('a_right.length=',a_right.length);
							//console.log('nRec=',nRec);
							//console.log('mess_validat_right=',mess_validat_right);
							if ((a_right.length > 0 && a_right.length != nRec) || (a_right.length == 0 && mess_validat_right != ""))
							{
								form.showMsg(mess_validat + "(правая рука)" + mess_validat_right);
								return;
							}

							form.GraceTotals(scaleName, 0);
							Ext.getCmp('FirstPanel' + scaleName).collapse();

							//Формирование объекта для сохранения
							ScaleParameter = '';
							for (var ii = 0; ii < Ext.getCmp('ReabGrid_ARATScale').getGrid().getStore().data.items.length; ii++)
							{
								// Параметр - ID значение (правая рука) - ID значение (левая рука)
								ScaleParameter = ScaleParameter + Ext.getCmp('ReabGrid_ARATScale').getGrid().getStore().data.items[ii].data.ScaleParameterType_id + "-";
								if (Ext.getCmp('ReabGrid_ARATScale').getGrid().getStore().data.items[ii].data.Hand_right_id == "")
								{
									ScaleParameter = ScaleParameter + " -";
								} else
								{
									ScaleParameter = ScaleParameter + Ext.getCmp('ReabGrid_ARATScale').getGrid().getStore().data.items[ii].data.Hand_right_id + "-";
								}
								if (Ext.getCmp('ReabGrid_ARATScale').getGrid().getStore().data.items[ii].data.Hand_left_id == "")
								{
									ScaleParameter = ScaleParameter + " ;";
								} else
								{
									ScaleParameter = ScaleParameter + Ext.getCmp('ReabGrid_ARATScale').getGrid().getStore().data.items[ii].data.Hand_left_id + ";";
								}
							}
							ScaleParameter = ScaleParameter.substr(0, ScaleParameter.lastIndexOf(';'));
							ScaleValue = '0';
							console.log('ScaleParameter=', ScaleParameter);
						}

						if (scaleName == 'MedResCouncil')
						{

							nRec = Ext.getCmp('ReabGrid_MRCScale').getGrid().getStore().data.items.length;
							if (nRec == 0)
							{
								sw.swMsg.alert(lang['soobschenie'], lang['otsutstvuyut_parametryi']);
								return;
							}
							//Подготовка объекта
							ScaleParameter = '';

							for (var k = 0; k < nRec; k++)
							{
								ScaleParameter = ScaleParameter + Ext.getCmp('ReabGrid_MRCScale').getGrid().getStore().data.items[k].data.limb_id + '-';
								ScaleParameter = ScaleParameter + Ext.getCmp('ReabGrid_MRCScale').getGrid().getStore().data.items[k].data.Position_id + '-';
								ScaleParameter = ScaleParameter + Ext.getCmp('ReabGrid_MRCScale').getGrid().getStore().data.items[k].data.Lateralization_id + '-' +
										Ext.getCmp('ReabGrid_MRCScale').getGrid().getStore().data.items[k].data.MRCValue_id + ';';
							}
							ScaleParameter = ScaleParameter.substr(0, ScaleParameter.lastIndexOf(';'));
							ScaleValue = '0';
							//   console.log('ScaleParameter=',ScaleParameter);
						}


//               if( Ext.getCmp('GridReabScales').getGrid().getSelectionModel().getSelected().get('ScaleType_SysNick') == 'nihss' )
//               {
//                   form.isButtonAdd = false; // Временно
//               }


						//Сохранение
						//console.log('Сохранение=');
						var loadMask = new Ext.LoadMask(this.getEl(), {msg: lang['podojdite_idet_sohranenie']});
						loadMask.show();
						Ext.Ajax.request({
							url: '?c=Ufa_Reab_Register_User&m=saveRegistrScale',
							params: {
								Person_id: form.Person_id,
								DirectType_id: form.DirectType_id,
								Scale_SysNick: scaleName,
								//StageType_id: form.StageType_id,
								ReabEvent_id: form.GridReabObjects.getGrid().getSelectionModel().getSelected().data.ReabEvent_id,
								ReabScale_setDate: rr,
								MedPersonal_iid: getGlobalOptions().medpersonal_id,
								Lpu_iid: getGlobalOptions().lpu_id,
								ReabScaleParameter: ScaleParameter,
								ReabScaleRefinement: scale_Refinement,
								ReabScaleResult: ScaleValue,
//                   // parameter : Ext.util.JSON.encode(oSave),
								isButtonAdd: form.isButtonAdd,
								isButtonEdit: form.isButtonEdit
							},
							callback: function (options, success, response)
							{
								//console.log('Поперло сохранение');

								loadMask.hide(); // Обязательно сделать

								if (success == true)
								{
									var response_obj = Ext.util.JSON.decode(response.responseText);

									if (response_obj.success == true)
									{
										//Поиск требуемой записи
										var nRecord = null;
										var rr13 = Ext.getCmp('FillindScaleDateReab').value.substr(6, 4) + '-' + Ext.getCmp('FillindScaleDateReab').value.substr(3, 2) + '-' + Ext.getCmp('FillindScaleDateReab').value.substr(0, 2) + ' ';
										//console.log('rr13=', rr13);
										var uu12 = Ext.getCmp('FillindScaleTimeReab').getValue();
										rr13 = rr13 + uu12;
										// console.log('rr14=',rr13);
										for (var k in response_obj.listScales)
										{
											if (response_obj.listScales[k].setDate.date.substr(0, 16) == rr13)
											{
												nRecord = k;
												break;
											}
										}
										console.log('Начало=');
										if (nRecord >= 0)
										{
											form.getEl().mask(lang['podojdite_idet_sohranenie']).show();
											//form.getEl().mask('rrrrr').show();
											//Восстановление данных
											if (scaleName == 'renkin' || scaleName == 'rivermid' || scaleName == 'Killip' ||
													scaleName == 'Ashworth' || scaleName == 'Hauser')
											{
												// Перерисовка результатов
												Ext.getCmp('ReabScaleGrid').setDisabled(true);
												form.ScaleLoadData(scaleName, response_obj.listScales[nRecord]);
											}
											if (scaleName == 'glasgow')
											{
												// Блокирование окна
												Ext.getCmp('QuestionPanel_' + scaleName).setDisabled(true);
											}

//											if (scaleName == 'Harris')
//											{
//												Ext.getCmp('Question_' + scaleName).setDisabled(true);
//											}
											if (scaleName == 'МоСА')
											{
												Ext.getCmp('VisualSkillsQuestion_' + scaleName).setDisabled(true);
												Ext.getCmp('NamingQuestion_' + scaleName).setDisabled(true);
												Ext.getCmp('AttentionQuestion_' + scaleName).setDisabled(true);
												Ext.getCmp('SpeechQuestion_' + scaleName).setDisabled(true);
												Ext.getCmp('AbstractionQuestion_' + scaleName).setDisabled(true);
												Ext.getCmp('PlaybackQuestion_' + scaleName).setDisabled(true);
												Ext.getCmp('OrientationQuestion_' + scaleName).setDisabled(true);
											}
											if (scaleName == 'Alarm_HADS' || scaleName == 'Depression_HADS' || scaleName == 'Berg' || scaleName == 'Frenchay' || scaleName == 'Bartel' ||
													scaleName == 'Vasserman' || scaleName == 'FIM' || scaleName == 'dysarthria' || scaleName == 'Lequesne' || scaleName == 'rivermid_DAA' || scaleName == 'nihss' ||
													scaleName == 'Harris')
											{
												// Перерисовка результатов
												Ext.getCmp('Question_' + scaleName).setDisabled(true);
												if (scaleName == 'Berg' || scaleName == 'Vasserman' || scaleName == 'FIM' || scaleName == 'dysarthria' || scaleName == 'Lequesne' ||
														scaleName == 'rivermid_DAA' || scaleName == 'nihss' || scaleName == 'Alarm_HADS' || scaleName == 'Depression_HADS' || scaleName == 'Harris')
												{
													Ext.getCmp('FirstPanel' + scaleName).expand();
												}

											}
											if (scaleName == 'GRACE')
											{
												Ext.getCmp('FirstPanelGRACE').setDisabled(true);
											}

											if (scaleName == 'VAScale')
											{
												Ext.getCmp('GridResult').setDisabled(true);
												Ext.getCmp('VASc_Panel').hide();
											}

											if (scaleName == 'MedResCouncil')
											{
												Ext.getCmp('GridMRCScResult').setDisabled(true);
												Ext.getCmp('MRCSc_Panel').hide();
											}
											if (scaleName == 'ARAT')
											{
												//Ext.getCmp('Question_' + scaleName).setDisabled(true);
												Ext.getCmp('Reab' + scaleName + 'input').setDisabled(true);
												//Обнуление полей ввода
												Ext.getCmp('ReabARATHandSide').selectedIndex = -1;
												Ext.getCmp('ReabARATHandSide').setValue('Введите параметр');
												Ext.getCmp('ReabAratCombo').selectedIndex = -1;
												Ext.getCmp('ReabAratCombo').setValue('Введите параметр');
												Ext.getCmp('missing_hand').setValue(false);
												Ext.getCmp('Reab_ARAT_Parameter').setText("");

												Ext.getCmp('ReabGrid_ARATScale').noHand = true;
												Ext.getCmp('FirstPanel' + scaleName).expand();
											}


											var tree = form.ScalesDatesTreePanel;
											tree.nDateScale = nRecord; //Надо отобразить позднюю дату
											form.ScalesDatesTreePanel.objDateScale = response_obj.listScales;

											for (j = 0; j < tree.getRootNode().childNodes.length; j++)
											{
												tree.getRootNode().childNodes[j].remove(true);
											}
											tree.getLoader().load(tree.getRootNode());  //Перегрузка дат
											Ext.getCmp('scaleReabCenterPanel').show();
											Ext.getCmp('printReabScaleButton').setDisabled(false);  //Кнопка печати шкалы
										} else
										{
											//нет измерений
											form.ScalesDatesTreePanel.objDateScale = null;
											Ext.getCmp('scaleReabCenterPanel').hide();
											Ext.getCmp('printReabScaleButton').setDisabled(true);  //Кнопка печати шкалы

										}

										form.showMsg(lang['dannyie_sohranenyi']);
										Ext.getCmp('saveReabScaleDataButton').setDisabled(true);
										Ext.getCmp('addReabScaleDataButton').setDisabled(false);
										Ext.getCmp('FillindScaleDateReab').setDisabled(true);
										Ext.getCmp('FillindScaleTimeReab').setDisabled(true);
										form.isButtonAdd = false;
										return;
										//  console.log('Конец=');
									} else
									{
										if (scaleName == 'Lequesne')
										{
											Ext.getCmp('FirstPanel' + scaleName).expand();
											return;
										}
									}
								} else {
									form.getEl().mask().hide();
									sw.swMsg.alert(lang['soobschenie'], lang['oshibka_pri_vyipolnenii_zaprosa_k_serveru']);
								}
								form.isButtonEdit = false;
								//form.isButtonAdd = false;

								return;
							}
						})
					},
					//Интерпретация итогов по шкале Grace + Berg + Vasserman + dysarthria
					GraceTotals: function (cScale, nGRACEResult)
					{
						var form = Ext.getCmp('ufa_personReabRegistryWindow');
						switch (cScale) {
							case 'GRACE-ST':
								if (nGRACEResult < 109)
								{
									Ext.getCmp('ReabRiskHospitalDegree').setValue("Низкая");
									Ext.getCmp('ReabProbability_of_hosp_death').setValue("< 1.0");
								} else
								{
									if (nGRACEResult < 141)
									{
										Ext.getCmp('ReabRiskHospitalDegree').setValue("Средняя");
										Ext.getCmp('ReabProbability_of_hosp_death').setValue("1,0 - 3,0");
									} else
									{
										Ext.getCmp('ReabRiskHospitalDegree').setValue("Высокая");
										Ext.getCmp('ReabProbability_of_hosp_death').setValue("> 3,0");
									}
								}
								if (nGRACEResult < 89)
								{
									Ext.getCmp('ReabRisk6MonthsDegree').setValue("Низкая");
									Ext.getCmp('ReabProbability6Months_death').setValue("< 3.0");
								} else
								{
									if (nGRACEResult < 119)
									{
										Ext.getCmp('ReabRisk6MonthsDegree').setValue("Средняя");
										Ext.getCmp('ReabProbability6Months_death').setValue("3.0 - 8.0");
									} else
									{
										Ext.getCmp('ReabRisk6MonthsDegree').setValue("Высокая");
										Ext.getCmp('ReabProbability6Months_death').setValue("> 8.0");
									}
								}
								Ext.getCmp('ReabGraceSumm').show();
								break;
							case 'GRACE+ST':
								if (nGRACEResult < 126)
								{
									Ext.getCmp('ReabRiskHospitalDegree').setValue("Низкая");
									Ext.getCmp('ReabProbability_of_hosp_death').setValue("< 2.0");
								} else
								{
									if (nGRACEResult < 155)
									{
										Ext.getCmp('ReabRiskHospitalDegree').setValue("Промежуточная");
										Ext.getCmp('ReabProbability_of_hosp_death').setValue("2,0 - 5,0");
									} else
									{
										Ext.getCmp('ReabRiskHospitalDegree').setValue("Высокая");
										Ext.getCmp('ReabProbability_of_hosp_death').setValue("> 5,0");
									}
								}
								if (nGRACEResult < 100)
								{
									Ext.getCmp('ReabRisk6MonthsDegree').setValue("Низкая");
									Ext.getCmp('ReabProbability6Months_death').setValue("< 4.4");
								} else
								{
									if (nGRACEResult < 128)
									{
										Ext.getCmp('ReabRisk6MonthsDegree').setValue("Промежуточная");
										Ext.getCmp('ReabProbability6Months_death').setValue("4,5 - 11.0");
									} else
									{
										Ext.getCmp('ReabRisk6MonthsDegree').setValue("Высокая");
										Ext.getCmp('ReabProbability6Months_death').setValue("> 11.0");
									}
								}
								Ext.getCmp('ReabGraceSumm').show();
								break;
							case 'Berg':
								if (nGRACEResult < 43)
								{
									Ext.getCmp('ReabBergTotal').setValue("Высокий риск падения");
								} else
								{
									Ext.getCmp('ReabBergTotal').setValue("Ходьба с помощью");
								}
								Ext.getCmp('ReabBergSumm').show();
								break;
							case 'nihss':
								if (nGRACEResult < 1)
								{
									Ext.getCmp('ReabnihssTotal').setValue("Нет симптомов инсульта");
								} else
								{
									if (nGRACEResult < 5)
									{
										Ext.getCmp('ReabnihssTotal').setValue("Легкой степени тяжести");
									} else
									{
										if (nGRACEResult < 16)
										{
											Ext.getCmp('ReabnihssTotal').setValue("Средней степени тяжести");
										} else
										{
											if (nGRACEResult < 21)
											{
												Ext.getCmp('ReabnihssTotal').setValue("Тяжелый инсульт");
											} else
											{
												Ext.getCmp('ReabnihssTotal').setValue("Крайне тяжелый инсульт");
											}
										}
									}
								}
								Ext.getCmp('ReabnihssSumm').show();
								break;
							case 'Vasserman':
								if (nGRACEResult < 21)
								{
									Ext.getCmp('ReabVassermanTotal').setValue("Лёгкая");
								} else
								{
									if (nGRACEResult < 41)
									{
										Ext.getCmp('ReabVassermanTotal').setValue("Средняя");
									} else
									{
										Ext.getCmp('ReabVassermanTotal').setValue("Грубая");
									}
								}
								Ext.getCmp('ReabVassermanSumm').show();
								break;
							case 'Harris':
								if (nGRACEResult < 70)
								{
									Ext.getCmp('ReabHarrisTotal').setValue("Неудовлетворительный");
								} else
								{
									if (nGRACEResult < 80)
									{
										Ext.getCmp('ReabHarrisTotal').setValue("Удовлетворительный");
									} else
									{
										if (nGRACEResult < 90)
										{
											Ext.getCmp('ReabHarrisTotal').setValue("Хороший");
										} else
										{
											Ext.getCmp('ReabHarrisTotal').setValue("Отличный");
										}
									}
								}
								Ext.getCmp('ReabHarrisSumm').show();
								break;
							case 'Alarm_HADS':
								if (nGRACEResult < 8)
								{
									Ext.getCmp('Reab' + cScale + 'Total').setValue("Норма");
								} else
								{
									if (nGRACEResult < 11)
									{
										Ext.getCmp('Reab' + cScale + 'Total').setValue("Cубклинически выраженная тревога");
									} else
									{
										Ext.getCmp('Reab' + cScale + 'Total').setValue("Клинически выраженная тревога");
									}
								}
								Ext.getCmp('Reab' + cScale + 'Summ').show();
								break;
							case 'Depression_HADS':
								if (nGRACEResult < 8)
								{
									Ext.getCmp('Reab' + cScale + 'Total').setValue("Норма");
								} else
								{
									if (nGRACEResult < 11)
									{
										Ext.getCmp('Reab' + cScale + 'Total').setValue("Cубклинически выраженная депрессия");
									} else
									{
										Ext.getCmp('Reab' + cScale + 'Total').setValue("Клинически выраженная депрессия");
									}
								}
								Ext.getCmp('Reab' + cScale + 'Summ').show();
								break;
							case 'Lequesne':
								if (nGRACEResult < 1)
								{
									Ext.getCmp('ReabLequesneTotal').setValue("Нет");
								} else
								{
									if (nGRACEResult < 5)
									{
										Ext.getCmp('ReabLequesneTotal').setValue("Легкое");
									} else
									{
										if (nGRACEResult < 8)
										{
											Ext.getCmp('ReabLequesneTotal').setValue("Умеренное");
										} else
										{
											if (nGRACEResult < 11)
											{
												Ext.getCmp('ReabLequesneTotal').setValue("Выраженное");
											} else
											{
												if (nGRACEResult < 14)
												{
													Ext.getCmp('ReabLequesneTotal').setValue("Резко выраженное");
												} else
												{
													Ext.getCmp('ReabLequesneTotal').setValue("Крайне выраженное");
												}
											}
										}
									}
								}
								Ext.getCmp('ReabLequesneSumm').show();
								break;
							case 'dysarthria':
								if (nGRACEResult < 6)
								{
									Ext.getCmp('ReabdysarthriaTotal').setValue("Речь в норме");
								} else
								{
									if (nGRACEResult < 20)
									{
										Ext.getCmp('ReabdysarthriaTotal').setValue("Дизартрия легкой степени выраженности");
									} else
									{
										if (nGRACEResult < 40)
										{
											Ext.getCmp('ReabdysarthriaTotal').setValue("Дизартрия умеренной степени выраженности");
										} else
										{
											if (nGRACEResult < 57)
											{
												Ext.getCmp('ReabdysarthriaTotal').setValue("Дизартрия тяжелой степени выраженности");
											} else
											{
												Ext.getCmp('ReabdysarthriaTotal').setValue("Анартрия");
											}
										}
									}
								}
								Ext.getCmp('ReabdysarthriaSumm').show();
								break;
							case 'FIM':
								Ext.getCmp('ReabFIMSumm').show();
								break;
							case 'rivermid_DAA':
								var n_result = ((nGRACEResult - 31) / 62) * 100;
								//console.log('n_result=',n_result);
								Ext.getCmp('Reabrivermid_DAATotal').setValue(nGRACEResult + "     " + Math.round(n_result) + "%");
								Ext.getCmp('Reabrivermid_DAASumm').show();
								break;
							case 'glasgow':
								if (nGRACEResult >= 15)
								{
									Ext.getCmp('ReabglasgowTotal').setValue("Сознание ясное");
								} else
								{
									if (nGRACEResult >= 13)
									{
										Ext.getCmp('ReabglasgowTotal').setValue("Умеренное оглушение");
									} else
									{
										if (nGRACEResult >= 11)
										{
											Ext.getCmp('ReabglasgowTotal').setValue("Глубокое оглушение");
										} else
										{
											if (nGRACEResult >= 8)
											{
												Ext.getCmp('ReabglasgowTotal').setValue("Сопор");
											} else
											{
												if (nGRACEResult >= 6)
												{
													Ext.getCmp('ReabglasgowTotal').setValue("Умеренная кома");
												} else
												{
													if (nGRACEResult >= 4)
													{
														Ext.getCmp('ReabglasgowTotal').setValue("Глубокая кома");
													} else
													{
														Ext.getCmp('ReabglasgowTotal').setValue("Запредельная кома, смерть мозга");
													}
												}
											}
										}
									}
								}
								Ext.getCmp('ReabglasgowSumm').show();
								break;
							case 'ARAT':
								// Просчитаем сумму
								var result_right = 0;
								var result_left = 0;
								var n_records = Ext.getCmp('ReabGrid_ARATScale').getGrid().getStore().data.items.length;
								for (var k = 0; k < n_records; k++)
								{
									if (Ext.getCmp('ReabGrid_ARATScale').getGrid().getStore().data.items[k].data.Hand_left_id == "")
									{
										result_left = "Отсутствует";
									} else
									{
										result_left = result_left + Ext.getCmp('ReabGrid_ARATScale').getGrid().getStore().data.items[k].data.Hand_left_Weight;
									}
									if (Ext.getCmp('ReabGrid_ARATScale').getGrid().getStore().data.items[k].data.Hand_right_id == "")
									{
										result_right = "Отсутствует";
									} else
									{
										result_right = result_right + Ext.getCmp('ReabGrid_ARATScale').getGrid().getStore().data.items[k].data.Hand_right_Weight;
									}
								}
								//console.log('result_right=',result_right);
								//console.log('result_left=',result_left);
								Ext.getCmp('ReabARATright_hand').setValue(result_right);
								Ext.getCmp('ReabARATleft_hand').setValue(result_left);
								Ext.getCmp('ReabARATSumm').show();
								break;
							default:
								form.showMsg('Косяк');
								break;
						}
						;
						return;
					},
					//Блоктровка заполненной шкалы
					disabledScaleData: function () {
						var scaleName = Ext.getCmp('GridReabScales').getGrid().getSelectionModel().getSelected().get('ScaleType_SysNick');
						if (scaleName == 'renkin' || scaleName == 'rivermid' || scaleName == 'Killip' ||
								scaleName == 'Ashworth' || scaleName == 'Hauser')
						{
							// Для заполения даты и шкалы
							Ext.getCmp('ReabScaleGrid').setDisabled(true);
						}
						;
						if (scaleName == 'GRACE')
						{
							Ext.getCmp('Creating_OKS_id').setDisabled(true);
							Ext.getCmp('FirstPanelGRACE').setDisabled(true);
						}
						if (scaleName == 'glasgow')
						{
							Ext.getCmp('QuestionPanel_' + scaleName).setDisabled(true);
						}
						if (scaleName == 'Harris' || scaleName == 'МоСА')
						{
							if (scaleName == 'Harris')
							{
								Ext.getCmp('Question_' + scaleName).setDisabled(true);
							}
							if (scaleName == 'МоСА')
							{
								Ext.getCmp('VisualSkillsQuestion_' + scaleName).setDisabled(true);
								Ext.getCmp('NamingQuestion_' + scaleName).setDisabled(true);
								Ext.getCmp('AttentionQuestion_' + scaleName).setDisabled(true);
								Ext.getCmp('SpeechQuestion_' + scaleName).setDisabled(true);
								Ext.getCmp('AbstractionQuestion_' + scaleName).setDisabled(true);
								Ext.getCmp('PlaybackQuestion_' + scaleName).setDisabled(true);
								Ext.getCmp('OrientationQuestion_' + scaleName).setDisabled(true);
							}
						}
						if (scaleName == 'Alarm_HADS' || scaleName == 'Depression_HADS' || scaleName == 'Berg' || scaleName == 'Frenchay' || scaleName == 'Bartel' || scaleName == 'Vasserman' ||
								scaleName == 'FIM' || scaleName == 'dysarthria' || scaleName == 'Lequesne' || scaleName == 'rivermid_DAA' || scaleName == 'nihss')
						{
							Ext.getCmp('Question_' + scaleName).setDisabled(true);
						}
						if (scaleName == 'VAScale')
						{
							Ext.getCmp('GridResult').setDisabled(true);
						}
						if (scaleName == 'MedResCouncil')
						{
							Ext.getCmp('GridMRCScResult').setDisabled(true);
						}
						if (scaleName == 'ARAT')
						{
							Ext.getCmp('Reab' + scaleName + 'input').setDisabled(true);
						}
					},
					//Подготовка к добавлению нового измерения
					addScaleData: function () {
						var form = Ext.getCmp('ufa_personReabRegistryWindow');
						var scaleName = Ext.getCmp('GridReabScales').getGrid().getSelectionModel().getSelected().get('ScaleType_SysNick');

						// Отсекаем закрытые этапы
						if (form.GridReabObjects.getGrid().getSelectionModel().getSelected().get('OutCause_id') != 0)
						{
							sw.swMsg.alert(lang['soobschenie'], 'Добавление шкал невозможно. Этап закрыт!');
							return
						}

						//Контроль единственности шкалы  GRACE для данного случая
						if (scaleName == 'GRACE' && form.ScalesDatesTreePanel.objDateScale != null)
						{
							var cDateScale = form.ScalesDatesTreePanel.objDateScale[0].setDate.date.substr(0, 10)
							sw.swMsg.alert(lang['soobschenie'], 'Данная шкала заполнена ' + cDateScale);
							return;
						}

						Ext.getCmp('FillindScaleDateReab').setDisabled(false);
						Ext.getCmp('FillindScaleTimeReab').setDisabled(false);
						Ext.getCmp('FillindScaleDateReab').setValue(new Date());
						Ext.getCmp('FillindScaleTimeReab').setValue('');
						Ext.getCmp('saveReabScaleDataButton').setDisabled(false);
						Ext.getCmp('addReabScaleDataButton').setDisabled(true);
						Ext.getCmp('deleteReabScaleDataButton').setDisabled(true);
						form.isButtonAdd = true;


						if (scaleName == 'renkin' || scaleName == 'rivermid' || scaleName == 'Killip' ||
								scaleName == 'Ashworth' || scaleName == 'Hauser')
						{
							// Для заполения даты и шкалы
							Ext.getCmp('ReabScaleGrid').setDisabled(false);

							//Обнуление значений новой шкалы
							for (j = 0; j < Ext.getCmp('ReabScaleGrid').getGrid().getStore().data.items.length; j++)
							{
								var vrecord = Ext.getCmp('ReabScaleGrid').ViewGridPanel.getStore().data.items[j].data;
								if (vrecord.selrow == '1')
								{
									Ext.getCmp('ReabScaleGrid').getGrid().getSelectionModel().selectRow(j);
									vrecord = Ext.getCmp('ReabScaleGrid').getGrid().getSelectionModel().getSelected();
									vrecord.set('selrow', '0');
									vrecord.commit();
									Ext.getCmp('ReabScaleGrid').getGrid().getSelectionModel().deselectRow(j);
									break;
								}
							}
							return;
						}
						;
						if (scaleName == 'GRACE')
						{
							Ext.getCmp('Creating_OKS_id').setDisabled(false);
							Ext.getCmp('Creating_OKS_id').selectedIndex = -1;
							Ext.getCmp('Creating_OKS_id').setValue('Введите параметр');
							Ext.getCmp('FirstPanelGRACE').setDisabled(false);
							return;
						}
						if (scaleName == 'glasgow')
						{
							Ext.getCmp('QuestionPanel_' + scaleName).setDisabled(false);
							Ext.getCmp('Param_1_id').selectedIndex = -1;
							Ext.getCmp('Param_1_id').setValue('Введите параметр');
							Ext.getCmp('Param_2_id').selectedIndex = -1;
							Ext.getCmp('Param_2_id').setValue('Введите параметр');
							Ext.getCmp('Param_3_id').selectedIndex = -1;
							Ext.getCmp('Param_3_id').setValue('Введите параметр');
							Ext.getCmp('eye_response').setValue('');
							Ext.getCmp('verbal_response').setValue('');
							Ext.getCmp('motor_response').setValue('');
							Ext.getCmp('ReabScaleValue_id').setValue('');
							Ext.getCmp('Reab' + scaleName + 'Summ').hide();
							return;
						}
						if (scaleName == 'Harris' || scaleName == 'МоСА')
						{
							if (scaleName == 'Harris')
							{

								Ext.getCmp('Question_' + scaleName).setDisabled(false);
								Ext.getCmp('Reab' + scaleName + 'Summ').hide();
								Ext.getCmp('Reab' + scaleName + 'Total').setValue("");
							}
							if (scaleName == 'МоСА')
							{
								Ext.getCmp('VisualSkillsQuestion_' + scaleName).setDisabled(false);
								Ext.getCmp('NamingQuestion_' + scaleName).setDisabled(false);
								Ext.getCmp('AttentionQuestion_' + scaleName).setDisabled(false);
								Ext.getCmp('SpeechQuestion_' + scaleName).setDisabled(false);
								Ext.getCmp('AbstractionQuestion_' + scaleName).setDisabled(false);
								Ext.getCmp('PlaybackQuestion_' + scaleName).setDisabled(false);
								Ext.getCmp('OrientationQuestion_' + scaleName).setDisabled(false);
							}
							//Обнуление параметров
							//Определяем кол-во параметров
							var nRec = 1;
							var cKey = form.ObjScale.SprScale[0].ScaleParameterType_id;
							// console.log('cKey=',cKey);
							for (var ii = 0; ii < Object.keys(form.ObjScale.SprScale).length; ii++)
							{
								//  console.log('cKey1=',cKey);
								if (form.ObjScale.SprScale[ii].ScaleParameterType_id != cKey)
								{
									cKey = form.ObjScale.SprScale[ii].ScaleParameterType_id;
									nRec++;
								}
							}
							// console.log('nRec=',nRec);

							for (var k = 1; k <= nRec; k++)
							{
								Ext.getCmp(scaleName + 'Param_' + k).selectedIndex = -1;
								Ext.getCmp(scaleName + 'Param_' + k).setValue('Введите параметр');
								Ext.getCmp(scaleName + 'Param_' + k).setValue("");
								Ext.getCmp(scaleName + 'Field_' + k).setValue("");
							}
							Ext.getCmp('ReabScaleValue_id').setValue('');

							return;
						}
						if (scaleName == 'Alarm_HADS' || scaleName == 'Depression_HADS' || scaleName == 'Berg' || scaleName == 'Frenchay' || scaleName == 'Bartel' || scaleName == 'Vasserman' ||
								scaleName == 'FIM' || scaleName == 'dysarthria' || scaleName == 'Lequesne' || scaleName == 'rivermid_DAA' || scaleName == 'nihss')
						{
							Ext.getCmp('Question_' + scaleName).setDisabled(false);
							//Обнуление параметров
							//Определяем кол-во параметров
							var nRec = 1;
							var cKey = form.ObjScale.SprScale[0].ScaleParameterType_id;
							// console.log('cKey=',cKey);
							for (var ii = 0; ii < Object.keys(form.ObjScale.SprScale).length; ii++)
							{
								//  console.log('cKey1=',cKey);
								if (form.ObjScale.SprScale[ii].ScaleParameterType_id != cKey)
								{
									cKey = form.ObjScale.SprScale[ii].ScaleParameterType_id;
									nRec++;
								}
							}
							for (var k = 1; k <= nRec; k++)
							{
								Ext.getCmp(scaleName + 'Param_' + k).selectedIndex = -1;
								Ext.getCmp(scaleName + 'Param_' + k).setValue('Введите параметр');
								Ext.getCmp(scaleName + 'Param_' + k).setValue("");
								Ext.getCmp(scaleName + 'Field_' + k).setValue("");
								if (scaleName == 'nihss' && (k == 7 || k == 8 || k == 9 || k == 10 || k == 11 || k == 14))
								{
									Ext.getCmp(scaleName + 'Area_' + k).setValue('');
									Ext.getCmp(scaleName + 'Area_' + k).hide();
								}
							}
							Ext.getCmp('ReabScaleValue_id').setValue('');
							if (scaleName == 'Berg' || scaleName == 'Vasserman' || scaleName == 'FIM' || scaleName == 'dysarthria' || scaleName == 'Lequesne' || scaleName == 'rivermid_DAA' ||
									scaleName == 'nihss' || scaleName == 'Alarm_HADS' || scaleName == 'Depression_HADS')
							{
								Ext.getCmp('Reab' + scaleName + 'Summ').hide();
								if (scaleName == 'FIM')
								{

								} else
								{
									Ext.getCmp('Reab' + scaleName + 'Total').setValue("");
									if (scaleName == 'Lequesne')
									{
										Ext.getCmp(scaleName + 'Type_of_joint_id').selectedIndex = -1;
										Ext.getCmp(scaleName + 'Type_of_joint_id').setValue('Введите параметр');
										Ext.getCmp(scaleName + 'Side_id').selectedIndex = -1;
										Ext.getCmp(scaleName + 'Side_id').setValue('Введите параметр');
									}
								}
							}

							return;
						}
						if (scaleName == 'VAScale')
						{
							Ext.getCmp('ReabGrid_VAScale').getGrid().store.removeAll();
							Ext.getCmp('GridResult').setDisabled(false);
							Ext.getCmp('VASc_Panel').hide();
							return;
						}
						if (scaleName == 'MedResCouncil')
						{
							Ext.getCmp('ReabGrid_MRCScale').getGrid().store.removeAll();
							Ext.getCmp('GridMRCScResult').setDisabled(false);
							Ext.getCmp('MRCSc_Panel').hide();
							return;
						}
						if (scaleName == 'ARAT')
						{
							//Обнуление GRIDa
							Ext.getCmp('ReabGrid_ARATScale').noHand = true;

							for (var ii = 0; ii < form.ObjScale.SprScale.length; ii++)
							{
								Ext.getCmp('ReabGrid_ARATScale').getGrid().getSelectionModel().selectRow(ii);
								var record = Ext.getCmp('ReabGrid_ARATScale').getGrid().getSelectionModel().getSelected();
								record.set("Hand_right", "");
								record.set("Hand_right_Weight", "");
								record.set("Hand_left", "");
								record.set("Hand_left_Weight", "");
								record.set("Hand_left_id", "");
								record.set("Hand_right_id", "");
								record.commit();
							}

							Ext.getCmp('ReabGrid_ARATScale').noHand = false;
							Ext.getCmp('ReabARATHandSide').selectedIndex = -1;
							Ext.getCmp('ReabARATHandSide').setValue('Введите параметр');
							Ext.getCmp('ReabAratCombo').selectedIndex = -1;
							Ext.getCmp('ReabAratCombo').setValue('Введите параметр');
							Ext.getCmp('missing_hand').setValue(false);
							Ext.getCmp('Reab_ARAT_Parameter').setText("");

							//id: 'Reab' + cSysNick + 'input',
							Ext.getCmp('Reab' + scaleName + 'input').setDisabled(false);
							Ext.getCmp('Reab' + scaleName + 'Summ').hide();
							return;
						}

						//Изменять при добавлении
						sw.swMsg.alert(lang['soobschenie'], 'Для данной шкалы нет реализации');
						Ext.getCmp('saveReabScaleDataButton').setDisabled(true);
						Ext.getCmp('addReabScaleDataButton').setDisabled(false);
						return;
					},
					//загрузка имеющихся шкал у пациента и дат их заполнения
					loadScale: function (cSysNick, cScaleName) {
						var form = Ext.getCmp('ufa_personReabRegistryWindow');
						//Обнулим 2 панели
						// console.log('Обнулим 2 панели=');
						Ext.getCmp('DateScale_id').removeAll();
						Ext.getCmp('ViewScale_id').removeAll();

						var loadMask = new Ext.LoadMask(this.getEl(), {msg: lang['zagruzka']});
						loadMask.show();

						//Тянем данные
						Ext.Ajax.request({
							url: '?c=Ufa_Reab_Register_User&m=scaleSpr_Data',
							params: {
								Person_id: this.Person_id,
								DirectType_id: this.DirectType_id,
								SysNick: cSysNick,
								ReabEvent_id: form.GridReabObjects.getGrid().getSelectionModel().getSelected().data.ReabEvent_id

							},
							callback: function (options, success, response)
							{
								// console.log('success=',success);
								// console.log('response=',response);
								loadMask.hide(); // Обязательно сделать
								if (success == true)
								{
									var ObjScale = Ext.util.JSON.decode(response.responseText);

									form.createScale(cSysNick, cScaleName, ObjScale); //Создали объект шкалы
									form.ObjScale = ObjScale;
									//form.ScalesDatesTreePanel.getLoader().load(form.ScalesDatesTreePanel.getRootNode());  //Перегрузка дат

									if (ObjScale.DataScale.length > 0)
									{ // Проставление значений - имеются заполненные шкалы
										//  console.log('Надо грузить=');

										form.ScalesDatesTreePanel.nDateScale = 0; //Надо отобразить позднюю дату
										form.ScaleLoadData(cSysNick, ObjScale.DataScale[0]);
										form.ScalesDatesTreePanel.objDateScale = ObjScale.DataScale;
										Ext.getCmp('DateScale_id').doLayout();
										Ext.getCmp('scaleReabCenterPanel').show();
										Ext.getCmp('printReabScaleButton').setDisabled(false);  //Кнопка печати шкалы
										//form.ScalesDatesTreePanel.getSelectionModel().select(form.ScalesDatesTreePanel.getRootNode( ).childNodes[0]);
									} else
									{//Нет заполненных шкал
										form.ScalesDatesTreePanel.nDateScale = null;
										form.ScalesDatesTreePanel.objDateScale = null;
										Ext.getCmp('scaleReabCenterPanel').hide();
										Ext.getCmp('ReabScaleValue_id').setValue('');

										// Обработка для GRACE
										if (cSysNick == 'GRACE')
										{
											//console.log('Попали=');
											//console.log('cSysNick=',cSysNick);
											Ext.getCmp('ReabGraceSumm').hide(); //!!!!!!!!!!!!!!!!!!!!!
											Ext.getCmp('ReabGraceParameters').hide(); //!!!!!!!!!!!!!!!!!!!!!!
											Ext.getCmp('Creating_OKS_id').setDisabled(true);
										}
										// Обработка для GRACE (закрываем интерапетацию)
										if (cSysNick == 'Berg' || cSysNick == 'Vasserman' || cSysNick == 'FIM' || cSysNick == 'dysarthria' || cSysNick == 'Lequesne' || cSysNick == 'rivermid_DAA' ||
												cSysNick == 'glasgow' || cSysNick == 'nihss' || cSysNick == 'Alarm_HADS' || cSysNick == 'Depression_HADS' || cSysNick == 'Harris')
										{
											//console.log('Попали=');
											//console.log('cSysNick=',cSysNick);
											Ext.getCmp('Reab' + cSysNick + 'Summ').hide();
										}
										if (cSysNick == 'nihss')
										{
											Ext.getCmp(cSysNick + 'Area_7').hide();
											Ext.getCmp(cSysNick + 'Area_8').hide();
											Ext.getCmp(cSysNick + 'Area_9').hide();
											Ext.getCmp(cSysNick + 'Area_10').hide();
											Ext.getCmp(cSysNick + 'Area_11').hide();
											Ext.getCmp(cSysNick + 'Area_14').hide();
//                                        Ext.getCmp('nihssArea_7').hide()
										}

										Ext.getCmp('printReabScaleButton').setDisabled(true);  //Кнопка печати шкалы
									}

									Ext.getCmp('addReabScaleDataButton').setDisabled(false);
								} else {
									Ext.getCmp('addReabScaleDataButton').setDisabled(true);
									sw.swMsg.alert(lang['soobschenie'], lang['oshibka_pri_vyipolnenii_zaprosa_k_serveru']);
								}
							}
						});

					},
					//Формирование основных панелей шкал и анкет
					createFirstPanel: function (id_panel, name_panel)
					{
						var first_panel = new Ext.Panel({
							id: id_panel,
							title: '<span style="font-size:12px">' + name_panel + '</span>',
							collapsible: true,
							tabIndex: -1,
							frame: true,
							border: false,
							listeners: {
								'render': function (panel) {
									panel.header.on('click', function () {
										if (panel.collapsed) {
											panel.expand();
										} else {
											panel.collapse();
										}
									});
								}
							}
						});
						return first_panel
					},
					// Формирование шкал и дат их заполнения
					createScale: function (cSysNick, cScaleName, ObjScale) {

						var cTitleScale = ""; // Полное наименование шкалы
						var nameScale = ""; // Полное наименование шкалы
						var bGrid = false;  // поле навык/Характеристрика
						var bGridParam = false;  // поле Параметр
						var bGridClass = false;  // поле Класс
						var cField1 = "";  //Наименование поля параметра ScaleParameterResult_Name
						var cField2 = "";  //Наименование поля параметра ScaleParameterResult_Name1
						var cTextPosition1 = ""; //положение текста в поле ScaleParameterResult_Name
						var nWidth1 = 0;  //Длина поля ScaleParameterResult_Name
						var nWidth2 = 0;  //Длина поля ScaleParameterResult_Name1
						//var nWidth3 = 0;  //Длина поля ScaleParameterResult_Name2
						var cStyle = ""; // Размер COMBO для 'Alarm_HADS','Depression_HADS','Berg'
						var nHeight = 0;
						var form = Ext.getCmp('ufa_personReabRegistryWindow');
						//Формы шкал
						switch (cSysNick) {
							case 'renkin':
								//Шкала Рэнкина
								cTitleScale = cScaleName;
								bGridParam = false;
								bGridClass = true;
								bGrid = true;
								nWidth1 = 750;
								cField1 = lang['tekuschee_sostoyanie'];
								cTextPosition1 = 'left';
								nHeight = Ext.getBody().getHeight() - 350;
								Ext.getCmp('ViewScale_id').height = Ext.getBody().getHeight() - 180;
								break;
							case 'Hauser':
								//Индекс ходьбы Хаузера
								cTitleScale = cScaleName;
								bGridParam = false;
								bGridClass = true;
								bGrid = true;
								nWidth1 = 750;
								cField1 = lang['tekuschee_sostoyanie'];
								cTextPosition1 = 'left';
								nHeight = Ext.getBody().getHeight() - 350;
								Ext.getCmp('ViewScale_id').height = Ext.getBody().getHeight() - 180;
								break;
							case 'Ashworth':
								//Шкала Ашфорта
								cTitleScale = 'Модифицированная шкала Ашфорт';
								bGridParam = false;
								bGridClass = true;
								bGrid = true;
								nWidth1 = 750;
								cField1 = lang['tekuschee_sostoyanie'];
								cTextPosition1 = 'left';
								nHeight = Ext.getBody().getHeight() - 350;
								Ext.getCmp('ViewScale_id').height = Ext.getBody().getHeight() - 180;
								break;
							case 'rivermid':
								cTitleScale = cScaleName;
								bGridParam = false;
								bGridClass = true;
								bGrid = false;
								cField2 = 'Навык:';
								var nWidth2 = 250;
								nWidth1 = 500;
								cField1 = lang['voprosyi'];
								cTextPosition1 = 'left';
								nHeight = Ext.getBody().getHeight() - 100;
								Ext.getCmp('ViewScale_id').height = Ext.getBody().getHeight() - 240;
								break;
							case 'Killip':
								cTitleScale = 'Классификация острой сердечной недостаточности по Киллип';
								bGridParam = true;
								bGridClass = false;
								bGrid = false;
								cField2 = lang['harakteristika'];
								var nWidth2 = 350;
								nWidth1 = 200;
								cField1 = 'Летальность,%';
								cTextPosition1 = 'center';
								nHeight = Ext.getBody().getHeight() - 350;
								Ext.getCmp('ViewScale_id').height = Ext.getBody().getHeight() - 240;
								break;
							case 'VAScale':
								cTitleScale = 'Визуально-аналоговая шкала (ВАШ) боли';
								//nHeight = Ext.getBody().getHeight()-350;
								nHeight = 400;
								Ext.getCmp('ViewScale_id').height = Ext.getBody().getHeight() - 180;
								break;
							case 'MedResCouncil':
								nHeight = 450;
								// Ext.getCmp('ViewScale_id').height = Ext.getBody().getHeight()-160;
								break;
								break;
							case 'glasgow':
							case 'Harris':
							case 'МоСА':
							case 'GRACE':
								break;
							case 'FIM':
								cStyle = "width: 200px; border:none;font-size:1.1em",
										nameScale = "МЕРА ФУНКЦИОНАЛЬНОЙ НЕЗАВИСИМОСТИ (FIM)";
								break;
							case 'ARAT':
								nHeight = 450;
								break;
							case 'Frenchay':
								cTitleScale = cScaleName;
								cStyle = "width: 250px; border:none;font-size:1.1em";
								nWidth1 = 500;
								break;
							case 'Vasserman':
								cTitleScale = "Шкала Вассерман Л.И. для оценки степени выраженности речевых нарушений <br>у больных с локальными поражениями мозга";
								cStyle = 'width: 450px; border:none;font-size:1.1em';
								nWidth1 = 650;
								break;
							case 'dysarthria':
								nameScale = cScaleName;
								cStyle = 'width: 300px; border:none;font-size:1.1em';
								nWidth1 = 500;
								break;
							case 'nihss':
								nameScale = 'Шкала тяжести инсульта национальных институтов США (NIHSS)';
								cStyle = 'width: 300px; border:none;font-size:1.1em';
								nWidth1 = 500;
								break;
							case 'rivermid_DAA':
								nameScale = "Шкала активностей повседневной жизни Ривермид";
								cStyle = 'width: 300px; border:none;font-size:1.1em';
								nWidth1 = 500;
								break;
							case 'Lequesne':
								nameScale = cScaleName;
								cStyle = 'width: 300px; border:none;font-size:1.1em';
								nWidth1 = 500;
								break;
							case 'Bartel':
							case 'Alarm_HADS':
							case 'Depression_HADS':
								cTitleScale = cScaleName;
								cStyle = 'width: 450px; border:none;font-size:1.1em';
								nWidth1 = 650;
								break;
							case 'Berg':
								cTitleScale = cScaleName;
								cStyle = 'width: 550px; border:none;font-size:1.1em';
								nWidth1 = 700;
								break;
							default :
								form.showMsg('Косяк');
								break;
						};

						//Шкала GRACE
						if (cSysNick == 'GRACE')
						{
							//Ext.getCmp('ReabScaleValue').hide();
							Ext.getCmp('ReabScaleValue').show();
							// Формирование ОКС для шкалы
							var aSprOks = new Array();
							//console.log('ObjScale.SprScale=',ObjScale.SprScale);
							for (jj = 0; jj < ObjScale.SprScale.length; jj++)
							{
								//console.log('kk=',jj);
								aSprOks.push([jj,
									ObjScale.SprScale[jj].ScaleParameterResult_id,
									ObjScale.SprScale[jj].ScaleParameterResult_Name,
									ObjScale.SprScale[jj].ScaleParameterResult_Value]);
							}
							;
							// console.log('aSprOks=',aSprOks);
							var first_panel = new Ext.Panel({
								id: 'FirstPanel' + cSysNick,
								title: '<span style="font-size:13px">' + 'Оценка риска смерти больных ОКС в стационаре и через 6 месяцев' + '</span>',
								collapsible: false,
								tabIndex: -1,
								frame: true,
								border: false,
								items: [
									// Основная панель шкалы
									new Ext.Panel({
										layout: 'form',
										border: true,
										bodyStyle: 'padding: 10px;background-color: #E2FFE9', //+bgcolor
										style: 'border-top: 1px solid #99bbe8; border-left: 1px solid #99bbe8; border-bottom: 1px solid #ffffff;border-right: 1px solid #ffffff; padding: 2px 0px 0px 0px; margin-top: 5px;background-color: transparent ;',
										items: [
											// панель ОКС
											{// панель ОКС
												layout: 'form',
												border: true,
												items: [
													{
														layout: 'form',
														border: false,
														labelWidth: 40,
														labelAlign: 'left',
														labelStyle: 'font-style:italic;font-size:1.3em;color:blue;',
														items: [
															//combo ОКС
															{
																allowBlank: false,
																style: 'text-align : center; font-size:1.1em; ',
																id: 'Creating_OKS_id',
																xtype: 'combo',
																width: 185,
																fieldLabel: 'ОКС',
																labelStyle: 'font-style:italic;font-size:1.3em;color:blue;',
																hideTrigger: false, // Chek
																mode: 'local',
																editable: false,
																triggerAction: 'all',
																displayField: 'ScaleParameterResult_Name',
																valueField: 'ScaleParameterResult_id',
																hiddenName: 'ReabSpr_Elem_id',
																tabIndex: -1,
																emptyText: 'Введите параметр',
																listWidth: 'auto',
																autoscroll: false,
																store: new Ext.data.SimpleStore({
																	fields: [
																		{name: 'ScaleParameterType_id', type: 'int'},
																		{name: 'ScaleParameterResult_id', type: 'int'},
																		{name: 'ScaleParameterResult_Name', type: 'string'},
																		{name: 'ScaleParameterResult_Value', type: 'string'}
																	],
																	data: aSprOks
																}),
																tpl: '<tpl for="."><div class="x-combo-list-item">' +
																'{ScaleParameterResult_Name} ' + '&nbsp;' +
																'</div></tpl>',
																listeners: {
																	select: function (combo, record, index) {
																		var mParam = [];
																		Ext.getCmp('FirstPanel' + cSysNick).LoadTemplGrace(record.data.ScaleParameterType_id, mParam);
																	}
																},
															}
														]
													}
												]
											},
											{
												layout: 'form',
												border: false,
												height: 10
											},
											//Итоги
											{
												layout: 'form',
												//width: 1200,
												// heigth : 150,
												//labelWidth: 90,
												// border:true,
												id: 'ReabGraceSumm',
												items: [
													// Риск госпитальной смерти больных инфарктом миокарда
													new Ext.form.FieldSet(
															{
																border: true,
																autoHeight: true,
																hidden: false,
																bodyStyle: 'color:blue;',
																style: 'padding: 5px 10px;font-color: #AA0000;color:blue;',
																// labelWidth: 220,
																// labelAlign: 'right',
																title: 'Риск госпитальной смерти',
																id: 'ReabRiskHospitalDeath',
																items: [
																	{
																		layout: 'form',
																		border: false,
																		items: [
																			{
																				layout: 'form',
																				border: false,
																				labelWidth: 100,
																				labelAlign: 'left',
																				items: [
																					new Ext.form.TextField({
																						allowBlank: true,
																						disabled: true,
																						style: 'font-style:normal;font-size:1.1em;color:black;text-align: center;font-width: bold',
																						labelStyle: 'font-style:italic;font-size:1.1em;color:blue;',
																						fieldLabel: 'Степень риска',
																						id: 'ReabRiskHospitalDegree',
																						width: 120
																					})
																				]
																			},
																			{
																				layout: 'form',
																				border: false,
																				//style: 'position:relative;  left:20px ',
																				labelWidth: 260,
																				labelAlign: 'left',
																				items: [
																					new Ext.form.TextField({
																						allowBlank: true,
																						disabled: true,
																						style: 'font-style:normal;font-size:1.1em;color:black;text-align: center;font-width: bold',
																						labelStyle: 'font-style:italic;font-size:1.1em;color:blue;',
																						fieldLabel: 'Вероятность госпитальной смерти,%',
																						id: 'ReabProbability_of_hosp_death',
																						width: 80
																					})
																				]}
																		]
																	}
																]
															}),
													{
														layout: 'form',
														border: false,
														height: 10
													},
													// Риск смерти в течение 6 месяцев
													new Ext.form.FieldSet(
															{
																border: true,
																autoHeight: true,
																hidden: false,
																bodyStyle: 'color:blue;',
																style: 'padding: 5px 10px;font-color: #AA0000;color:blue;',
																labelWidth: 220,
																labelAlign: 'right',
																title: 'Риск смерти в течение 6 месяцев',
																id: 'ReabRiskDeath6Months',
																items: [
																	{
																		layout: 'form',
																		border: false,
																		items: [
																			{
																				layout: 'form',
																				border: false,
																				labelWidth: 100,
																				labelAlign: 'left',
																				items: [
																					new Ext.form.TextField({
																						allowBlank: true,
																						disabled: true,
																						style: 'font-style:normal;font-size:1.1em;color:black;text-align: center;font-width: bold',
																						labelStyle: 'font-style:italic;font-size:1.1em;color:blue;',
																						fieldLabel: 'Степень риска',
																						id: 'ReabRisk6MonthsDegree',
																						width: 120
																					})
																				]
																			},
																			{
																				layout: 'form',
																				border: false,
																				labelWidth: 270,
																				labelAlign: 'left',
																				items: [
																					new Ext.form.TextField({
																						allowBlank: true,
																						disabled: true,
																						style: 'font-style:normal;font-size:1.1em;color:black;text-align: center;font-width: bold',
																						labelStyle: 'font-style:italic;font-size:1.1em;color:blue;',
																						fieldLabel: 'Вероятность смерти в течение 6 мес.,%',
																						id: 'ReabProbability6Months_death',
																						width: 80
																					})
																				]}
																		]
																	}
																]
															})
												]
											},
											{
												layout: 'form',
												border: false,
												height: 15
											},
											// панель Параметров
											{// панель Параметров
												layout: 'form',
												//width: 1200,
												//labelWidth: 90,
												border: false,
												id: 'ReabGraceParameters',
												items: [
													//Возраст
													{
														layout: 'form',
														border: false,
														//height : 20
														items: [
															new Ext.form.Label({
																text: 'Возраст',
																height: 10,
																style: 'font-style:italic;font-size:1.3em;color:blue; '
															})
														]
													},
													{//Возраст
														layout: 'column',
														// width: 1200,
														items: [
															{
																layout: 'form',
																border: false,
																labelWidth: 50,
																labelAlign: 'right',
																items: [
																	new Ext.form.NumberField({
																		allowBlank: false,
																		hideLabel: true,
																		disabled: true,
																		minLength: 1,
																		maxLength: 3,
																		maxValue: 120,
																		minValue: 1,
																		hideLabel: true,
																		id: 'ReabGraceAge',
																		style: 'text-align:center;font-weight:bold;color: black;',
																		width: 120,
																		listeners: {
																			'show': function () {
																				//alert('Изменили возраст');
																				form.calcReabScale(cSysNick);
																			}
																		}
																	})
																]
															},
															new Ext.form.TextField({
																allowBlank: true,
																disabled: true,
																style: 'margin: 0px 0px 0px 125px;font-style:normal;font-size:1.2em;color:blue;text-align: center;font-width: bold',
																id: 'ReabGraceAgeField',
																width: 40
															})
														]
													},
													//Креатинин
													{//Креатинин
														layout: 'form',
														border: false,
														//height : 20
														items: [
															new Ext.form.Label({
																text: 'Креатинин, мг/дл / мкмоль/л',
																height: 10,
																style: 'font-style:italic;font-size:1.3em;color:blue; '
															})
														]
													},
													{//
														border: false,
														layout: 'column',
														bodyStyle: 'padding: 5px;background-color: #E2FFE9',
														items: [
															//combo Креатинин
															{
																allowBlank: false,
																style: 'text-align : center; font-size:1.1em; ',
																id: 'ReabGraceCreatinine',
																xtype: 'combo',
																width: 200,
																hideLabel: true,
																fieldLabel: 'Креатинин, мг/дл / мкмоль/л',
																hideTrigger: false, // Chek
																mode: 'local',
																editable: false,
																triggerAction: 'all',
																displayField: 'ScaleParameterResult_Name',
																valueField: 'ScaleParameterResult_id',
																hiddenName: 'ReabSpr_Elem_id',
																tabIndex: -1,
																emptyText: 'Введите параметр',
																//listWidth:'auto',
																autoscroll: false,
																store: new Ext.data.SimpleStore({
																	fields: [
																		{name: 'ScaleParameterType_id', type: 'int'},
																		{name: 'ScaleParameterResult_id', type: 'int'},
																		{name: 'ScaleParameterResult_Name', type: 'string'},
																		{name: 'ScaleParameterResult_Value', type: 'string'}
																	],
																}),
																tpl: '<tpl for="."><div class="x-combo-list-item">' +
																'{ScaleParameterResult_Name} ' + '&nbsp;' +
																'</div></tpl>',
																listeners: {
																	select: function (combo, record, index) {
																		Ext.getCmp('GraceCreatinine_Field').setValue(parseFloat(record.json[3]).toFixed(0));
																		form.calcReabScale(cSysNick);
																	}
																}
															},
															new Ext.form.TextField({
																allowBlank: true,
																disabled: true,
																style: 'margin: 0px 0px 0px 40px;font-style:normal;font-size:1.2em;color:blue;text-align: center;font-width: bold',
																id: 'GraceCreatinine_Field',
																width: 40
															})
														]
													},
													//ЧСС, уд/мин
													{//ЧСС, уд/мин
														layout: 'form',
														border: false,
														items: [
															new Ext.form.Label({
																text: 'ЧСС, уд/мин',
																height: 10,
																style: 'font-style:italic;font-size:1.3em;color:blue; '
															})
														]
													},
													{//
														border: false,
														layout: 'column',
														bodyStyle: 'padding: 5px;background-color: #E2FFE9',
														items: [
															//combo ЧСС, уд/мин
															{
																allowBlank: false,
																style: 'text-align : center; font-size:1.1em; ',
																id: 'ReabGraceHeartRate',
																xtype: 'combo',
																width: 200,
																hideLabel: true,
																fieldLabel: 'ЧСС, уд/мин',
																hideTrigger: false, // Chek
																mode: 'local',
																editable: false,
																triggerAction: 'all',
																displayField: 'ScaleParameterResult_Name',
																valueField: 'ScaleParameterResult_id',
																hiddenName: 'ReabSpr_Elem_id',
																tabIndex: -1,
																emptyText: 'Введите параметр',
																// listWidth:'auto',
																autoscroll: false,
																xtype: 'combo',
																store: new Ext.data.SimpleStore({
																	fields: [
																		{name: 'ScaleParameterType_id', type: 'int'},
																		{name: 'ScaleParameterResult_id', type: 'int'},
																		{name: 'ScaleParameterResult_Name', type: 'string'},
																		{name: 'ScaleParameterResult_Value', type: 'string'}
																	],
																}),
																tpl: '<tpl for="."><div class="x-combo-list-item">' +
																'{ScaleParameterResult_Name} ' + '&nbsp;' +
																'</div></tpl>',
																listeners: {
																	select: function (combo, record, index) {
																		// console.log('record = ', record.json[3]);
																		Ext.getCmp('GraceHeartRate_Field').setValue(parseFloat(record.json[3]).toFixed(0));
																		form.calcReabScale(cSysNick);
																	}
																}
															},
															new Ext.form.TextField({
																allowBlank: true,
																disabled: true,
																style: 'margin: 0px 0px 0px 40px;font-style:normal;font-size:1.2em;color:blue;text-align: center;font-width: bold',
																id: 'GraceHeartRate_Field',
																width: 40
															})
														]
													},
													//Класс сердечной недостаточности по KillipT
													{//Класс сердечной недостаточности по KillipT
														layout: 'form',
														border: false,
														//height : 20
														items: [
															new Ext.form.Label({
																text: 'Класс сердечной недостаточности по KillipT',
																height: 10,
																style: 'font-style:italic;font-size:1.3em;color:blue; '
															})
														]
													},
													{//
														border: false,
														layout: 'column',
														bodyStyle: 'padding: 5px;background-color: #E2FFE9',
														items: [
															//combo Класс сердечной недостаточности по KillipT
															{
																allowBlank: false,
																style: 'text-align : center; font-size:1.1em; ',
																id: 'ReabGraceKillipT',
																xtype: 'combo',
																width: 200,
																hideLabel: true,
																fieldLabel: 'Класс сердечной недостаточности по KillipT',
																hideTrigger: false, // Chek
																mode: 'local',
																editable: false,
																triggerAction: 'all',
																displayField: 'ScaleParameterResult_Name',
																valueField: 'ScaleParameterResult_id',
																hiddenName: 'ReabSpr_Elem_id',
																tabIndex: -1,
																emptyText: 'Введите параметр',
																autoscroll: false,
																xtype: 'combo',
																store: new Ext.data.SimpleStore({
																	fields: [
																		{name: 'ScaleParameterType_id', type: 'int'},
																		{name: 'ScaleParameterResult_id', type: 'int'},
																		{name: 'ScaleParameterResult_Name', type: 'string'},
																		{name: 'ScaleParameterResult_Value', type: 'string'}
																	],

																}),
																tpl: '<tpl for="."><div class="x-combo-list-item">' +
																'{ScaleParameterResult_Name} ' + '&nbsp;' +
																'</div></tpl>',
																listeners: {
																	select: function (combo, record, index) {
																		// console.log('record = ', record.json[3]);
																		Ext.getCmp('GraceKillipT_Field').setValue(parseFloat(record.json[3]).toFixed(0));
																		form.calcReabScale(cSysNick);
																	}
																}
															},
															new Ext.form.TextField({
																allowBlank: true,
																disabled: true,
																style: 'margin: 0px 0px 0px 40px;font-style:normal;font-size:1.2em;color:blue;text-align: center;font-width: bold',
																id: 'GraceKillipT_Field',
																width: 40
															})
														]
													},
													// САД, мм рт.ст.
													{//САД, мм рт.ст.
														layout: 'form',
														border: false,
														//height : 20
														items: [
															new Ext.form.Label({
																text: 'САД, мм рт.ст.',
																height: 10,
																style: 'font-style:italic;font-size:1.3em;color:blue; '
															})
														]
													},
													{//
														border: false,
														layout: 'column',
														bodyStyle: 'padding: 5px;background-color: #E2FFE9',
														items: [
															//combo САД, мм рт.ст.
															{
																allowBlank: false,
																style: 'text-align : center; font-size:1.1em; ',
																id: 'ReabGraceArterial_pressure',
																xtype: 'combo',
																width: 200,
																hideLabel: true,
																fieldLabel: 'САД, мм рт.ст.',
																hideTrigger: false, // Chek
																mode: 'local',
																editable: false,
																triggerAction: 'all',
																displayField: 'ScaleParameterResult_Name',
																valueField: 'ScaleParameterResult_id',
																hiddenName: 'ReabSpr_Elem_id',
																tabIndex: -1,
																emptyText: 'Введите параметр',
																// listWidth:'auto',
																autoscroll: false,
																xtype: 'combo',
																store: new Ext.data.SimpleStore({
																	fields: [
																		{name: 'ScaleParameterType_id', type: 'int'},
																		{name: 'ScaleParameterResult_id', type: 'int'},
																		{name: 'ScaleParameterResult_Name', type: 'string'},
																		{name: 'ScaleParameterResult_Value', type: 'string'}
																	],
																}),
																tpl: '<tpl for="."><div class="x-combo-list-item">' +
																'{ScaleParameterResult_Name} ' + '&nbsp;' +
																'</div></tpl>',
																listeners: {
																	select: function (combo, record, index) {
																		// console.log('record = ', record.json[3]);
																		Ext.getCmp('GraceArterial_pressure_Field').setValue(parseFloat(record.json[3]).toFixed(0));
																		form.calcReabScale(cSysNick);
																	}
																}
															},
															new Ext.form.TextField({
																allowBlank: true,
																disabled: true,
																style: 'margin: 0px 0px 0px 40px;font-style:normal;font-size:1.2em;color:blue;text-align: center;font-width: bold',
																id: 'GraceArterial_pressure_Field',
																width: 40
															})
														]
													},
													{
														layout: 'form',
														border: false,
														height: 10
													},
													//Повышение маркеров некроза
													{//Повышение маркеров некроза
														border: false,
														layout: 'column',
														bodyStyle: 'padding: 5px;background-color: #E2FFE9',
														items: [
															{
																layout: 'form',
																border: false,
																width: 240,
																items: [
																	{
																		fieldLabel: 'Повышение маркеров некроза',
																		hideLabel: false,
																		labelStyle: 'width:220px;font-style:italic;font-size:1.2em;color:blue;',
																		// name: 'HardOnly',
																		id: 'IncreasedMarkers',
																		xtype: 'checkbox',
																		listeners: {
																			check: function (checked) {
																				if (Ext.getCmp('Creating_OKS_id').getValue() == 415)
																				{
																					var cScaleName = "GRACE-ST";
																					var IncMark_id = (checked.checked == true) ? 448 : 447;
																				} else
																				{
																					var cScaleName = "GRACE+ST";
																					var IncMark_id = (checked.checked == true) ? 487 : 486;
																				}
																				//console.log('IncMark_id=',IncMark_id);
																				for (var kk in form.ObjScale.SprScale)
																				{
																					if (form.ObjScale.SprScale[kk].ScaleType_SysNick == cScaleName && form.ObjScale.SprScale[kk].ParameterType_SysNick == 'IncreasedMarkers' &&
																							form.ObjScale.SprScale[kk].ScaleParameterResult_id == IncMark_id)
																					{
																						Ext.getCmp('GraceIncreasedMarkers_Field').setValue(parseFloat(form.ObjScale.SprScale[kk].ScaleParameterResult_Value).toFixed(0));
																						break;
																					}
																				}
																				form.calcReabScale(cSysNick);
																			}
																		}
																	}
																]
															},
															new Ext.form.TextField({
																allowBlank: true,
																hideLabel: true,
																fieldLabel: 'Повышение маркеров некроза',
																disabled: true,
																style: 'margin: 0px 0px 0px 5px;font-style:normal;font-size:1.2em;color:blue;text-align: center;font-width: bold',
																id: 'GraceIncreasedMarkers_Field',
																width: 40
															})
														]
													},
													//Остановка сердца
													{//Остановка сердца
														border: false,
														layout: 'column',
														bodyStyle: 'padding: 5px;background-color: #E2FFE9',
														items: [
															{
																layout: 'form',
																border: false,
																width: 240,
																items: [
																	{
																		fieldLabel: 'Остановка сердца при поступлении',
																		hideLabel: false,
																		labelStyle: 'width:220px;font-style:italic;font-size:1.2em;color:blue;',
																		// name: 'HardOnly',
																		id: 'HeartFailure',
																		xtype: 'checkbox',
																		listeners: {
																			check: function (checked) {
																				//console.log('Остановка сердца при поступлении');
																				if (Ext.getCmp('Creating_OKS_id').getValue() == 415)
																				{
																					var cScaleName = "GRACE-ST";
																					var IncMark_id = (checked.checked == true) ? 452 : 451;
																				} else
																				{
																					var cScaleName = "GRACE+ST";
																					var IncMark_id = (checked.checked == true) ? 491 : 490;
																				}
//
																				for (var kk in form.ObjScale.SprScale)
																				{
																					if (form.ObjScale.SprScale[kk].ScaleType_SysNick == cScaleName && form.ObjScale.SprScale[kk].ParameterType_SysNick == 'HeartFailure' &&
																							form.ObjScale.SprScale[kk].ScaleParameterResult_id == IncMark_id)
																					{
																						Ext.getCmp('GraceHeartFailure_Field').setValue(parseFloat(form.ObjScale.SprScale[kk].ScaleParameterResult_Value).toFixed(0));
																						break;
																					}
																				}
																				form.calcReabScale(cSysNick);
																			}
																		}
																	}
																]},
															new Ext.form.TextField({
																allowBlank: true,
																hideLabel: true,
																fieldLabel: 'Остановка сердца при поступлении',
																disabled: true,
																style: 'margin: 0px 0px 0px 5px;font-style:normal;font-size:1.2em;color:blue;text-align: center;font-width: bold',
																id: 'GraceHeartFailure_Field',
																width: 40
															})
														]
													},
													//Девиация ST
													{//Девиация ST
														border: false,
														layout: 'column',
														bodyStyle: 'padding: 5px;background-color: #E2FFE9',
														items: [
															{
																layout: 'form',
																border: false,
																width: 240,
																items: [
																	{
																		fieldLabel: 'Девиация ST',
																		hideLabel: false,
																		labelStyle: 'width:220px;font-style:italic;font-size:1.2em;color:blue;',
																		id: 'DeviationST',
																		xtype: 'checkbox',
																		listeners: {
																			check: function (checked)
																			{
																				if (Ext.getCmp('Creating_OKS_id').getValue() == 415)
																				{
																					var cScaleName = "GRACE-ST";
																					var IncMark_id = (checked.checked == true) ? 450 : 449;
																				} else
																				{
																					var cScaleName = "GRACE+ST";
																					var IncMark_id = (checked.checked == true) ? 489 : 488;
																				}

																				for (var kk in form.ObjScale.SprScale)
																				{
																					if (form.ObjScale.SprScale[kk].ScaleType_SysNick == cScaleName && form.ObjScale.SprScale[kk].ParameterType_SysNick == 'DeviationST' &&
																							form.ObjScale.SprScale[kk].ScaleParameterResult_id == IncMark_id)
																					{
																						//console.log('SprScale[kk]=',form.ObjScale.SprScale[kk]);
																						Ext.getCmp('GraceDeviationST_Field').setValue(parseFloat(form.ObjScale.SprScale[kk].ScaleParameterResult_Value).toFixed(0));
																						break;
																					}
																				}
																				form.calcReabScale(cSysNick);
																			}
																		}
																	}
																]},
															new Ext.form.TextField({
																allowBlank: true,
																hideLabel: true,
																fieldLabel: 'Девиация ST',
																disabled: true,
																style: 'margin: 0px 0px 0px 5px;font-style:normal;font-size:1.2em;color:blue;text-align: center;font-width: bold',
																id: 'GraceDeviationST_Field',
																width: 40
															})
														]},
												]
											}
										]
									})
								],
								LoadTemplGrace: function (nParam, mParam)
								{
									//console.log('nParam=', nParam);

									var form = Ext.getCmp('ufa_personReabRegistryWindow');
									if (nParam == 0)
									{
										//Загрузка шаблона Без подъёма сегмента ST
										var cScaleName = 'GRACE-ST';
									} else
									{
										//Загрузка шаблона  С подъёмом сегмента ST
										var cScaleName = 'GRACE+ST';
									}
									//Обнуление шкалы
									this.ResetScale();
									//Тянем данные
									var loadMask = new Ext.LoadMask(form.getEl(), {msg: lang['zagruzka']});
									loadMask.show();
									Ext.Ajax.request({
										url: '?c=Ufa_Reab_Register_User&m=scaleSpr',
										params: {
											SysNick: cScaleName
										},
										callback: function (options, success, response)
										{
											loadMask.hide(); // Обязательно сделать
											if (success == true)
											{

												var ObjScaleTempl = Ext.util.JSON.decode(response.responseText);
												//console.log('ObjScaleTempl=', ObjScaleTempl);

												var aSprComboCreatinine = new Array();
												var aSprComboHeartRate = new Array();
												var aSprComboKillipT = new Array();
												var aSprComboArterial_pressure = new Array();

												//Заполнение COMBO
												for (var kk in ObjScaleTempl.SprScale)
												{
													if (ObjScaleTempl.SprScale[kk].ScaleType_SysNick == cScaleName)
													{
														if (ObjScaleTempl.SprScale[kk].ParameterType_SysNick == 'Creatinine')
														{
															aSprComboCreatinine.push([ObjScaleTempl.SprScale[kk].ScaleParameterType_id,
																ObjScaleTempl.SprScale[kk].ScaleParameterResult_id,
																ObjScaleTempl.SprScale[kk].ScaleParameterResult_Name,
																ObjScaleTempl.SprScale[kk].ScaleParameterResult_Value]);
														}
														if (ObjScaleTempl.SprScale[kk].ParameterType_SysNick == 'HeartRat')
														{
															aSprComboHeartRate.push([ObjScaleTempl.SprScale[kk].ScaleParameterType_id,
																ObjScaleTempl.SprScale[kk].ScaleParameterResult_id,
																ObjScaleTempl.SprScale[kk].ScaleParameterResult_Name,
																ObjScaleTempl.SprScale[kk].ScaleParameterResult_Value]);
														}
														if (ObjScaleTempl.SprScale[kk].ParameterType_SysNick == 'KillipT')
														{
															aSprComboKillipT.push([ObjScaleTempl.SprScale[kk].ScaleParameterType_id,
																ObjScaleTempl.SprScale[kk].ScaleParameterResult_id,
																ObjScaleTempl.SprScale[kk].ScaleParameterResult_Name,
																ObjScaleTempl.SprScale[kk].ScaleParameterResult_Value]);
														}
														if (ObjScaleTempl.SprScale[kk].ParameterType_SysNick == 'arterial_pressure')
														{
															aSprComboArterial_pressure.push([ObjScaleTempl.SprScale[kk].ScaleParameterType_id,
																ObjScaleTempl.SprScale[kk].ScaleParameterResult_id,
																ObjScaleTempl.SprScale[kk].ScaleParameterResult_Name,
																ObjScaleTempl.SprScale[kk].ScaleParameterResult_Value]);
														}
													}
//
												}
												;

												// console.log('aSprComboCreatinine=',aSprComboCreatinine);

												Ext.getCmp('ReabGraceCreatinine').getStore().loadData(aSprComboCreatinine);
												Ext.getCmp('ReabGraceHeartRate').getStore().loadData(aSprComboHeartRate);
												Ext.getCmp('ReabGraceKillipT').getStore().loadData(aSprComboKillipT);
												Ext.getCmp('ReabGraceArterial_pressure').getStore().loadData(aSprComboArterial_pressure);
												//ReabGraceArterial_pressure

												//работа с возрастом
												if (mParam.length == 0)
												{
													var Person_Birthday = Ext.getCmp('ufa_personReabRegistryWindow').PersonInfoPanelReab.DataView.store.data.items[0].json.Person_Birthday
													var age = Ext.getCmp('ufa_personReabRegistryWindow').getAge(Person_Birthday, Ext.getCmp('FillindScaleDateReab').getValue());
													Ext.getCmp('FirstPanelGRACE').setAge(cScaleName, ObjScaleTempl.SprScale, age);

													console.log('Попробуем решить проблему галочек=');
													//Попробуем решить проблему галочек
													Ext.getCmp('IncreasedMarkers').setValue(false);
													Ext.getCmp('DeviationST').setValue(false);
													Ext.getCmp('HeartFailure').setValue(false);

													Ext.getCmp('GraceDeviationST_Field').setValue(0);
													Ext.getCmp('GraceHeartFailure_Field').setValue(0);
													Ext.getCmp('GraceIncreasedMarkers_Field').setValue(0);

												}
												//Открываем панель параметров
												Ext.getCmp('ReabGraceParameters').show();

												form.ObjScale = ObjScaleTempl;
												//Подтягивание данных (проблема Ext.Ajax.request)
												if (mParam.length != 0)
												{
													//Возраст
													Ext.getCmp('FirstPanelGRACE').setAge(cScaleName, ObjScaleTempl.SprScale, mParam[1]);
													//COMBO
													var aParam = new Array();
													aParam = mParam[2].split('-');
													Ext.getCmp('ReabGraceCreatinine').setValue(aParam[1]);
													var index = Ext.getCmp('ReabGraceCreatinine').getStore().find('ScaleParameterResult_id', aParam[1]);//нахожу индекс в store комбо по ScaleParameterResult_id из БД
													var rec = Ext.getCmp('ReabGraceCreatinine').getStore().getAt(index);  // нахожу record по index,
													Ext.getCmp('ReabGraceCreatinine').fireEvent('select', Ext.getCmp('ReabGraceCreatinine'), rec, index); // запуск события в комбо
													aParam.length = 0;
													aParam = mParam[3].split('-');
													Ext.getCmp('ReabGraceHeartRate').setValue(aParam[1]);
													var index = Ext.getCmp('ReabGraceHeartRate').getStore().find('ScaleParameterResult_id', aParam[1]);//нахожу индекс в store комбо по ScaleParameterResult_id из БД
													var rec = Ext.getCmp('ReabGraceHeartRate').getStore().getAt(index);  // нахожу record по index,
													Ext.getCmp('ReabGraceHeartRate').fireEvent('select', Ext.getCmp('ReabGraceCreatinine'), rec, index); // запуск события в комбо
													aParam.length = 0;
													aParam = mParam[4].split('-');
													Ext.getCmp('ReabGraceKillipT').setValue(aParam[1]);
													var index = Ext.getCmp('ReabGraceKillipT').getStore().find('ScaleParameterResult_id', aParam[1]);//нахожу индекс в store комбо по ScaleParameterResult_id из БД
													var rec = Ext.getCmp('ReabGraceKillipT').getStore().getAt(index);  // нахожу record по index,
													Ext.getCmp('ReabGraceKillipT').fireEvent('select', Ext.getCmp('ReabGraceCreatinine'), rec, index); // запуск события в комбо
													aParam.length = 0;
													aParam = mParam[5].split('-');
													Ext.getCmp('ReabGraceArterial_pressure').setValue(aParam[1]);
													var index = Ext.getCmp('ReabGraceArterial_pressure').getStore().find('ScaleParameterResult_id', aParam[1]);//нахожу индекс в store комбо по ScaleParameterResult_id из БД
													var rec = Ext.getCmp('ReabGraceArterial_pressure').getStore().getAt(index);  // нахожу record по index,
													Ext.getCmp('ReabGraceArterial_pressure').fireEvent('select', Ext.getCmp('ReabGraceCreatinine'), rec, index); // запуск события в комбо

													//Checkbox
													aParam.length = 0;
													aParam = mParam[6].split('-');
													if (aParam[1] == 448 || aParam[1] == 487)
													{
														Ext.getCmp('IncreasedMarkers').setValue(true);
													}
													;
													if (aParam[1] == 447 || aParam[1] == 486)
													{
														Ext.getCmp('IncreasedMarkers').setValue(true);
														Ext.getCmp('IncreasedMarkers').setValue(false);
													}
													;
													aParam.length = 0;
													aParam = mParam[7].split('-');
													if (aParam[1] == 450 || aParam[1] == 489)
													{
														Ext.getCmp('DeviationST').setValue(true);
													}
													;
													if (aParam[1] == 449 || aParam[1] == 488)
													{
														Ext.getCmp('DeviationST').setValue(true);
														Ext.getCmp('DeviationST').setValue(false);
													}
													;
													aParam.length = 0;
													aParam = mParam[8].split('-');
													if (aParam[1] == 452 || aParam[1] == 491)
													{
														Ext.getCmp('HeartFailure').setValue(true);
													}
													;
													if (aParam[1] == 451 || aParam[1] == 490)
													{
														Ext.getCmp('HeartFailure').setValue(true);
														Ext.getCmp('HeartFailure').setValue(false);
													}
													;

													//Интерпретация
													form.GraceTotals(cScaleName, Ext.getCmp('ReabScaleValue_id').getValue());
												}
											}
											;
										}
									});
								},
								// Сброс параметров
								ResetScale: function ()
								{
									Ext.getCmp('ReabGraceCreatinine').selectedIndex = -1;
									Ext.getCmp('ReabGraceCreatinine').setValue('Введите параметр');
									Ext.getCmp('GraceCreatinine_Field').setValue('');
									Ext.getCmp('ReabGraceHeartRate').selectedIndex = -1;
									Ext.getCmp('ReabGraceHeartRate').setValue('Введите параметр');
									Ext.getCmp('GraceHeartRate_Field').setValue('');
									Ext.getCmp('ReabGraceKillipT').selectedIndex = -1;
									Ext.getCmp('ReabGraceKillipT').setValue('Введите параметр');
									Ext.getCmp('GraceKillipT_Field').setValue('');
									Ext.getCmp('ReabGraceArterial_pressure').selectedIndex = -1;
									Ext.getCmp('ReabGraceArterial_pressure').setValue('Введите параметр');
									Ext.getCmp('GraceArterial_pressure_Field').setValue('');

									Ext.getCmp('IncreasedMarkers').setValue(false);
									Ext.getCmp('GraceIncreasedMarkers_Field').setValue('');
									Ext.getCmp('HeartFailure').setValue(false);
									Ext.getCmp('GraceHeartFailure_Field').setValue('');
									Ext.getCmp('DeviationST').setValue(false);
									Ext.getCmp('GraceDeviationST_Field').setValue('');
									Ext.getCmp('ReabGraceSumm').hide(); //!!!!!!!!!!!!!!!!!!!!!
								},
								// Работа с возрастом
								setAge: function (cScaleName, SprScale, age)
								{
									//console.log('Пришли=');
									//работа с возрастом
//                                                var Person_Birthday = Ext.getCmp('ufa_personReabRegistryWindow').personInfo.Person_Birthday;
//                                                var age =  Ext.getCmp('ufa_personReabRegistryWindow').getAge(Person_Birthday, Ext.getCmp('FillindScaleDateReab').getValue());
									Ext.getCmp('ReabGraceAge').setValue(age);

									if (cScaleName == 'GRACE-ST')
									{
										if (age < 41)
										{
											var age_id = 417;
										} else
										{
											if (age < 50)
											{
												var age_id = 418;
											} else
											{
												if (age < 60)
												{
													var age_id = 419;
												} else
												{
													if (age < 70)
													{
														var age_id = 420;
													} else
													{
														if (age < 80)
														{
															var age_id = 421;
														} else
														{
															var age_id = 422;
														}
													}
												}
											}
										}
									} else
									{ //'GRACE+ST'
										if (age < 30)
										{
											var age_id = 453;
										} else
										{
											if (age < 40)
											{
												var age_id = 454;
											} else
											{
												if (age < 50)
												{
													var age_id = 455;
												} else
												{
													if (age < 60)
													{
														var age_id = 456;
													} else
													{
														if (age < 70)
														{
															var age_id = 457;
														} else
														{
															if (age < 80)
															{
																var age_id = 458;
															} else
															{
																if (age < 90)
																{
																	var age_id = 459;
																} else
																{
																	var age_id = 460;
																}
															}
														}
													}
												}
											}
										}
									}

									for (var kk in SprScale)
									{
										if (SprScale[kk].ScaleType_SysNick == cScaleName &&
												SprScale[kk].ParameterType_SysNick == 'age' && SprScale[kk].ScaleParameterResult_id == age_id)
										{
											//console.log('SprScale[kk]222=',parseFloat(SprScale[kk].ScaleParameterResult_Value).toFixed(0));
											Ext.getCmp('ReabGraceAgeField').setValue(parseFloat(SprScale[kk].ScaleParameterResult_Value).toFixed(0));
											break;
										}
									}
									Ext.getCmp('ReabGraceAge').show();//Передергиваем баллы по возрасту
								},
								listeners: {
									'render': function (panel) {
										panel.header.on('click', function () {
											if (panel.collapsed) {
												panel.expand();
											} else {
												panel.collapse();
											}
										});
									}
								}
							});

							Ext.getCmp('ViewScale_id').add(first_panel);
							Ext.getCmp('ViewScale_id').doLayout();
						}

						if (cSysNick == 'renkin' || cSysNick == 'rivermid' || cSysNick == 'Killip' || cSysNick == 'Ashworth' ||
								cSysNick == 'Hauser')
						{
							Ext.getCmp('ReabScaleValue').hide();
							//Grid
							var reab_scale_grid = new sw.Promed.ViewFrame(
									{
										actions: [
											{name: 'action_add', hidden: true, text: 'Создать', disabled: true},
											{name: 'action_edit', hidden: true,  disabled: true},
											{name: 'action_delete', hidden: true,  disabled: true},
											{name: 'action_view', hidden: true},
											{name: 'action_refresh', hidden: true},
											{name: 'action_print', hidden: true}
										],
										autoExpandMin: 100,
										title: cTitleScale,
										autoLoadData: false,
										id: 'ReabScaleGrid',
										object: 'ReabScaleGrid',
										disabled: true,
										autoLoad: false,
										pageSize: 50,
										// height: Ext.getBody().getHeight()-110,
										height: nHeight,
										paging: false, // навигатор
										// autoHeight: true, // не работает
										// height: 'auto',
										region: 'center',
										root: 'data', //Обертка ответа(формируется в контроллере)
										stringfields: [
											{name: '[ScaleParameter', type: 'int', header: 'ID', key: true},
											{name: 'selrow', type: 'string', header: lang['vyibrat'], width: 80, align: 'center', sortable: false, hidden: true},
											//{name: 'ScaleParameterResult_Value', type: 'string', header: lang['parametr'], width: 80, align: 'center', vertical: 'middle', sortable: false, hidden: bGridParam, textalign: 'center', resizable: false},
											{name: 'ScaleParameterResult_Value', type: 'string',
												header: '<div style="width:80px;text-align:center; font-family:serif;font-weight: bold;font-size:1.2em;">' + lang['parametr'] + '</div>',
												width: 80, align: 'center', vertical: 'middle', sortable: false, hidden: bGridParam, textalign: 'center', resizable: false},
											{name: 'ScaleParameterResult_Name2', type: 'string',
												header: '<div style="width:100px;text-align:center; font-family:serif;font-weight: bold;font-size:1.2em;">' + lang['klass'] + '</div>',
												width: 120, align: 'center', sortable: false, hidden: bGridClass},
											{name: 'ScaleParameterResult_Name1', type: 'string',
												header: '<div style="width:100px;text-align:center; font-family:serif;font-weight: bold;font-size:1.2em;">' + cField2 + '</div>',
												width: nWidth2, sortable: false, hidden: bGrid},
											{name: 'ScaleParameterResult_Name', type: 'string',
												header: '<div style="width:' + nWidth1 + 'px;text-align:center;font-family:serif;font-weight: bold;font-size:1.2em;">' + cField1 + '</div>',
												width: nWidth1, sortable: false, align: cTextPosition1, resizable: true, id: 'voprosyi'},
											{name: 'ScaleParameterResult_id', type: 'string', width: 350, hidden: true}
										],
										autoExpandColumn: 'voprosyi',
										focusOnFirstLoad: false,
										toolbar: false,
										onBeforeLoadData: function () {
											//this.getButtonSearch().disable();
										}.createDelegate(this),
										onLoadData: function () {
											//this.getButtonSearch().enable();
										}.createDelegate(this),
										onRowSelect: function (sm, index, record) {

										}
									});
							//Для раскраски GRIDa
							Ext.getCmp('ReabScaleGrid').getGrid().view = new Ext.grid.GridView({
								getRowClass: function (row, index) {
									//alert('Раскраска');
									var cls = '';
									if (row.get('selrow') == 1) {//  Выбранное значение шкалы
										// alert('Раскраска');
										cls = cls + ' x-grid-rowblue ';
										cls = cls + ' x-grid-rowbold ';
									}
									return cls;
								}
							});

							Ext.getCmp('ViewScale_id').add(reab_scale_grid);
							Ext.getCmp('ViewScale_id').doLayout();

							// Заполнение Grida
							var nRecord = Ext.getCmp('ReabScaleGrid').getGrid().getStore().data.items.length;
							if (nRecord > 0)
							{//Очистка GRIDa
								Ext.getCmp('ReabScaleGrid').getGrid().store.removeAll( );
							}

							if (cSysNick == 'renkin' || cSysNick == 'Ashworth' || cSysNick == 'Hauser')
							{
								for (jj = 0; jj < ObjScale.SprScale.length; jj++)
								{
									Ext.getCmp('ReabScaleGrid').getGrid().store.insert(jj, [new Ext.data.Record({
										ScaleParameter: jj + 1,
										selrow: 0,
										ScaleParameterResult_Name: ObjScale.SprScale[jj].ScaleParameterResult_Name,
										ScaleParameterResult_Value: ObjScale.SprScale[jj].ScaleParameterResult_Value,
										ScaleParameterResult_id: ObjScale.SprScale[jj].ScaleParameterResult_id
									})]);
								}
							}
							if (cSysNick == 'rivermid')
							{
								for (jj = 0; jj < ObjScale.SprScale.length; jj++)
								{
									//Разделение записи
									var cName1 = ObjScale.SprScale[jj].ScaleParameterResult_Name.substr(0, ObjScale.SprScale[jj].ScaleParameterResult_Name.indexOf('~'));
									var cName = ObjScale.SprScale[jj].ScaleParameterResult_Name.substr(ObjScale.SprScale[jj].ScaleParameterResult_Name.indexOf('~') + 1);

									Ext.getCmp('ReabScaleGrid').getGrid().store.insert(jj, [new Ext.data.Record({
										ScaleParameter: jj + 1,
										selrow: 0,
										ScaleParameterResult_Name: cName,
										ScaleParameterResult_Name1: cName1,
										ScaleParameterResult_Value: ObjScale.SprScale[jj].ScaleParameterResult_Value,
										ScaleParameterResult_id: ObjScale.SprScale[jj].ScaleParameterResult_id
									})]);
								}
							}

							if (cSysNick == 'Killip')
							{
								for (jj = 0; jj < ObjScale.SprScale.length; jj++)
								{
									//Разделение записи
									var mName = new Array();
									mName = ObjScale.SprScale[jj].ScaleParameterResult_Name.split('~');
									// console.log('mName=',mName);
									Ext.getCmp('ReabScaleGrid').getGrid().store.insert(jj, [new Ext.data.Record({
										ScaleParameter: jj + 1,
										selrow: 0,
										ScaleParameterResult_Name2: mName[0],
										ScaleParameterResult_Name: mName[2],
										ScaleParameterResult_Name1: mName[1],
										ScaleParameterResult_Value: ObjScale.SprScale[jj].ScaleParameterResult_Value,
										ScaleParameterResult_id: ObjScale.SprScale[jj].ScaleParameterResult_id
									})]);
								}
							}

						}

						if (cSysNick == 'glasgow')
						{
							Ext.getCmp('ReabScaleValue').show();
							var first_panel = form.createFirstPanel('FirstPanel' + cSysNick, cScaleName);
							Ext.getCmp('ViewScale_id').add(first_panel);

							//Интерпретация результатов
							var delimiter = new Ext.Panel({
								layout: 'form',
								border: false,
								height: 10
							});
							var field_label = 'Интерпретация результата';
							var label_width = 200;

							var total_panel = new Ext.Panel({
								layout: 'form',
								// frame : true,
								border: false,
								style: 'border-top: 1px solid #99bbe8; border-left: 1px solid #99bbe8; border-bottom: 1px solid #ffffff;border-right: 1px solid #ffffff; padding: 2px 0px 0px 0px; margin-top: 5px;background-color: transparent ;',
								id: 'Reab' + cSysNick + 'Summ',
								items: [
									// Интерпретация результата
									new Ext.form.FieldSet(
											{
												border: false,
												autoHeight: true,
												hidden: false,
												bodyStyle: 'color:blue;',
												style: 'padding: 5px 10px;font-color: #AA0000;color:blue;',
												items: [
													{
														layout: 'form',
														border: false,
														items: [
															{
																layout: 'form',
																border: false,
																labelWidth: label_width,
																labelAlign: 'left',
																items: [
																	new Ext.form.TextField({
																		allowBlank: true,
																		disabled: true,
																		style: 'font-style:normal;font-size:1.1em;color:black;text-align: center;font-width: bold',
																		labelStyle: 'font-style:italic;font-size:1.1em;color:blue;',
																		fieldLabel: field_label,
																		id: 'Reab' + cSysNick + 'Total',
																		width: 300
																	})
																]
															}
														]
													}
												]
											})
								]
							});

							//Загрузка Combo
							var aSprCombo1 = new Array();
							var aSprCombo2 = new Array();
							var aSprCombo3 = new Array();
							var cName1 = "";
							var cName2 = "";
							var cName3 = "";

							for (var k in ObjScale.SprScale)
							{

								if (ObjScale.SprScale[k].ScaleParameterType_id == 3)
								{
									aSprCombo1.push([ObjScale.SprScale[k].ScaleParameterType_id,
										ObjScale.SprScale[k].ScaleParameterResult_id,
										ObjScale.SprScale[k].ScaleParameterResult_Name,
										ObjScale.SprScale[k].ScaleParameterResult_Value]);
									cName1 = ObjScale.SprScale[k].ScaleParameterType_Name;
								}

								if (ObjScale.SprScale[k].ScaleParameterType_id == 4)
								{
									aSprCombo2.push([ObjScale.SprScale[k].ScaleParameterType_id,
										ObjScale.SprScale[k].ScaleParameterResult_id,
										ObjScale.SprScale[k].ScaleParameterResult_Name,
										ObjScale.SprScale[k].ScaleParameterResult_Value]);
									cName2 = ObjScale.SprScale[k].ScaleParameterType_Name;
								}
								if (ObjScale.SprScale[k].ScaleParameterType_id == 5)
								{
									aSprCombo3.push([ObjScale.SprScale[k].ScaleParameterType_id,
										ObjScale.SprScale[k].ScaleParameterResult_id,
										ObjScale.SprScale[k].ScaleParameterResult_Name,
										ObjScale.SprScale[k].ScaleParameterResult_Value]);
									cName3 = ObjScale.SprScale[k].ScaleParameterType_Name;
								}
							}
							// console.log('aSprCombo = ', aSprCombo);

							var Combo1 = new Ext.form.ComboBox(
									{
										allowBlank: false,
										id: 'Param_1_id',
										hideLabel: true,
										hideTrigger: false, // Chek
										//style: 'position:relative; top: 15px ',
										//anchor: '100%',
										mode: 'local',
										editable: false,
										triggerAction: 'all',
										displayField: 'ScaleParameterResult_Name',
										valueField: 'ScaleParameterResult_id',
										style: 'width: 450px; border:none;font-size:1.1em',
										width: 'auto',
										tabIndex: -1,
										emptyText: 'Введите параметр',
										listWidth: 'auto',
										hiddenName: 'ReabSpr_Elem_id',
										autoscroll: false,
										xtype: 'combo',
										store: new Ext.data.SimpleStore({
											fields: [
												{name: 'ScaleParameterType_id', type: 'int'},
												{name: 'ScaleParameterResult_id', type: 'int'},
												{name: 'ScaleParameterResult_Name', type: 'string'},
												{name: 'ScaleParameterResult_Value', type: 'string'}
											],
											data: aSprCombo1
										}),

										tpl: '<tpl for="."><div class="x-combo-list-item">' +
										'{ScaleParameterResult_Name} ' + '&nbsp;' +
										'</div></tpl>',
										listeners: {
											specialkey: function (field, e) {
												console.log('FIELD', field)
												if (e.getKey() == e.ENTER) {
													//Ext.getCmp('getMorbusType_id').handler();
												}
											},
											select: function (combo, record, index) {
												//console.log('record = ', record.json[3]);
												Ext.getCmp('eye_response').setValue(record.json[3]);
												form.calcReabScale(cSysNick);
											}
										}
									});

							var Combo2 = new Ext.form.ComboBox(
									{
										allowBlank: false,
										id: 'Param_2_id',
										hideLabel: true,
										hideTrigger: false, // Chek
										mode: 'local',
										editable: false,
										triggerAction: 'all',
										displayField: 'ScaleParameterResult_Name',
										valueField: 'ScaleParameterResult_id',
										style: 'width: 450px; border:none;font-size:1.1em',
										width: 'auto',
										tabIndex: -1,
										emptyText: 'Введите параметр',
										listWidth: 'auto',
										hiddenName: 'ReabSpr_Elem_id',
										autoscroll: false,
										border: true,
										xtype: 'combo',
										store: new Ext.data.SimpleStore({
											fields: [
												{name: 'ScaleParameterType_id', type: 'int'},
												{name: 'ScaleParameterResult_id', type: 'int'},
												{name: 'ScaleParameterResult_Name', type: 'string'},
												{name: 'ScaleParameterResult_Value', type: 'string'}
											],
											data: aSprCombo2
										}),

										tpl: '<tpl for="."><div class="x-combo-list-item">' +
										'{ScaleParameterResult_Name} ' + '&nbsp;' +
										'</div></tpl>',
										listeners: {
											specialkey: function (field, e) {
												console.log('FIELD', field)
												if (e.getKey() == e.ENTER) {
													//Ext.getCmp('getMorbusType_id').handler();
												}
											},
											select: function (combo, record, index) {
												Ext.getCmp('verbal_response').setValue(record.json[3]);
												form.calcReabScale(cSysNick);
											}
										}
									});

							var Combo3 = new Ext.form.ComboBox(
									{
										allowBlank: false,
										id: 'Param_3_id',
										hideLabel: true,
										hideTrigger: false, // Chek
										//style: 'position:relative; top: 15px ',
										//anchor: '100%',
										mode: 'local',
										editable: false,
										triggerAction: 'all',
										displayField: 'ScaleParameterResult_Name',
										valueField: 'ScaleParameterResult_id',
										style: 'width: 450px; border:none;font-size:1.1em',
										width: 'auto',
										tabIndex: -1,
										emptyText: 'Введите параметр',
										listWidth: 'auto',
										hiddenName: 'ReabSpr_Elem_id',
										autoscroll: false,
										xtype: 'combo',
										store: new Ext.data.SimpleStore({
											fields: [
												{name: 'ScaleParameterType_id', type: 'int'},
												{name: 'ScaleParameterResult_id', type: 'int'},
												{name: 'ScaleParameterResult_Name', type: 'string'},
												{name: 'ScaleParameterResult_Value', type: 'string'}
											],
											data: aSprCombo3
										}),

										tpl: '<tpl for="."><div class="x-combo-list-item">' +
										'{ScaleParameterResult_Name} ' + '&nbsp;' +
										'</div></tpl>',
										listeners: {
											specialkey: function (field, e) {
												//console.log('FIELD', field)
												if (e.getKey() == e.ENTER) {
												}
											},
											select: function (combo, record, index) {
												Ext.getCmp('motor_response').setValue(record.json[3]);
												form.calcReabScale(cSysNick);
											}
										}
									});

							var QuestionPanel = new Ext.Panel({
								baseCls: 'x-plain',
								id: 'QuestionPanel_' + cSysNick,
								border: false,
								width: 750,
								disabled: true,
								layout: 'form',
								bodyStyle: 'padding: 10px;background-color: #E2FFE9', //+bgcolor
								tabIndex: -1,
								items: [
									total_panel,
									delimiter,
									new Ext.form.Label({
												id: 'cName1',
												text: cName1,
												height: 10,
												style: 'font-style:italic;font-size:1.2em;color:blue; '
											}
									),
									{
										// xtype: 'panel',
										border: false,
										layout: 'column',
										// style: 'padding: 10px;border: 0px solid #ffffff;',
										bodyStyle: 'padding: 5px;background-color: #E2FFE9',
										items: [
											Combo1,
											new Ext.form.TextField({
												allowBlank: true,
												disabled: true,
												style: 'margin: 0px 0px 0px 20px;font-style:normal;font-size:1.2em;color:blue;text-align: center;font-width: bold',
												id: 'eye_response',
												width: 40
											})
										]
									},
									new Ext.form.Label({
												text: cName2,
												id: 'cName2',
												height: 10,
												style: 'font-style:italic;font-size:1.2em;color:blue; '
											}
									),
									{
										xtype: 'panel',
										border: false,
										layout: 'column',
										bodyStyle: 'padding: 5px;background-color: #E2FFE9',
										items: [
											Combo2,
											new Ext.form.TextField({
												allowBlank: true,
												disabled: true,
												style: 'margin: 0px 0px 0px 20px;font-style:normal;font-size:1.2em;color:blue;text-align: center;font-width: bold',
												id: 'verbal_response',
												width: 40
											})
										]
									},
									new Ext.form.Label({
												text: cName3,
												id: 'cName3',
												height: 10,
												style: 'font-style:italic;font-size:1.2em;color:blue; '
											}
									),
									{
										xtype: 'panel',
										border: false,
										layout: 'column',
										bodyStyle: 'padding: 5px;background-color: #E2FFE9',
										items: [
											Combo3,
											new Ext.form.TextField({
												allowBlank: true,
												disabled: true,
												style: 'margin: 0px 0px 0px 20px;font-style:normal;font-size:1.2em;color:blue;text-align: center;font-width: bold',
												id: 'motor_response',
												width: 40
											})
										]
									},
								]
							});
							Ext.getCmp('FirstPanel' + cSysNick).add(QuestionPanel);
							Ext.getCmp('ViewScale_id').doLayout();
						}

						if (cSysNick == 'Harris')
						{
							Ext.getCmp('ReabScaleValue').show();
							var first_panel = form.createFirstPanel('FirstPanel' + cSysNick, cScaleName);
							//Ext.getCmp('ViewScale_id').add(first_panel);

							var func_question = new Ext.Panel({
								baseCls: 'x-plain',
								id: 'Question_' + cSysNick,
								border: false,
								width: 500, /////////////////
								disabled: true, //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
								layout: 'form',
								// frame: true,
								bodyStyle: 'padding: 5px;background-color: #E2FFE9', //+bgcolor
								tabIndex: -1,
								items: []
							});
							var tt = new Ext.Panel({
								layout: 'form',
								border: false,
								height: 10
							});

							var field_label = 'Результат теста';
							var label_width = 160;
							var total_panel = new Ext.Panel({
								layout: 'form',
								// frame : true,
								border: false,
								//bodyStyle: 'padding: 10px;background-color: #E2FFE9', //+bgcolor
								style: 'border-top: 1px solid #99bbe8; border-left: 1px solid #99bbe8; border-bottom: 1px solid #ffffff;border-right: 1px solid #ffffff; padding: 2px 0px 0px 0px; margin-top: 5px;background-color: transparent ;',
								id: 'Reab' + cSysNick + 'Summ',
								items: [
									// Интерпретация результата
									new Ext.form.FieldSet(
											{
												border: false,
												autoHeight: true,
												hidden: false,
												bodyStyle: 'color:blue;',
												style: 'padding: 5px 10px;font-color: #AA0000;color:blue;',
												items: [
													{
														layout: 'form',
														border: false,
														items: [
															{
																layout: 'form',
																border: false,
																labelWidth: label_width,
																labelAlign: 'left',
																items: [
																	new Ext.form.TextField({
																		allowBlank: true,
																		disabled: true,
																		style: 'font-style:normal;font-size:1.1em;color:black;text-align: center;font-width: bold',
																		labelStyle: 'font-style:italic;font-size:1.1em;color:blue;',
																		fieldLabel: field_label,
																		id: 'Reab' + cSysNick + 'Total',
																		width: 200
																	})
																]
															}
														]
													}
												]
											})
								]
							});
							Ext.getCmp('Question_' + cSysNick).add(total_panel);
							Ext.getCmp('Question_' + cSysNick).add(tt);


							//Боль
							var panel_pain = form.createFirstPanel('Panelpain' + cSysNick, 'Боль');
							var panel_function = form.createFirstPanel('PanelFunction' + cSysNick, 'Функции');
							var panel_deformation = form.createFirstPanel('PanelDeformation' + cSysNick, 'Деформация');
							var panel_amplitude = form.createFirstPanel('PanelAmplitude' + cSysNick, 'Амплитуда движений');

							//Загрузка Combo
							var oSprCombo = new Object();
							var oCombo = new Object();
							var mName = new Array();
							// console.log('Object.keys(ObjScale.SprScale)=',Object.keys(ObjScale.SprScale));
							//console.log('ObjScale.SprScale.length=',Object.keys(ObjScale.SprScale).length);
							//Определяем кол-во параметров
							var nRec = 1;
							var cKey = ObjScale.SprScale[0].ScaleParameterType_id;
							mName.push([ObjScale.SprScale[0].ScaleParameterType_Name, ObjScale.SprScale[0].ScaleParameterType_id, 1]);
							for (var ii = 0; ii < Object.keys(ObjScale.SprScale).length; ii++)
							{
								//console.log('nRec=',nRec);
								if (ObjScale.SprScale[ii].ScaleParameterType_id != cKey)
								{
									nRec++;
									cKey = ObjScale.SprScale[ii].ScaleParameterType_id;
									mName.push([ObjScale.SprScale[ii].ScaleParameterType_Name, ObjScale.SprScale[ii].ScaleParameterType_id, nRec]);
								}
							}
							//console.log('mName=', mName);
							// Формируем параметры
							for (var j = 0; j < nRec; j++)
							{
								var aSprCombo = new Array();
								for (var kk in ObjScale.SprScale)
								{
									if (ObjScale.SprScale[kk].ScaleParameterType_id == mName[j][1])
									{
										aSprCombo.push([ObjScale.SprScale[kk].ScaleParameterType_id,
											ObjScale.SprScale[kk].ScaleParameterResult_id,
											ObjScale.SprScale[kk].ScaleParameterResult_Name,
											ObjScale.SprScale[kk].ScaleParameterResult_Value]);
									}

								}
								;
								oSprCombo[j] = aSprCombo;
								var Combo = new Ext.form.ComboBox(
										{
											allowBlank: false,
											id: 'HarrisParam_' + mName[j][2],
											hideLabel: true,
											hideTrigger: false, // Chek
											//style: 'position:relative; top: 15px ',
											mode: 'local',
											editable: false,
											triggerAction: 'all',
											displayField: 'ScaleParameterResult_Name',
											valueField: 'ScaleParameterResult_id',
											style: 'width: 300px; border:none;font-size:1.1em',
											width: 'auto',
											tabIndex: -1,
											emptyText: 'Введите параметр',
											listWidth: 'auto',
											hiddenName: 'ReabSpr_Elem_id',
											autoscroll: false,
											xtype: 'combo',
											store: new Ext.data.SimpleStore({
												fields: [
													{name: 'ScaleParameterType_id', type: 'int'},
													{name: 'ScaleParameterResult_id', type: 'int'},
													{name: 'ScaleParameterResult_Name', type: 'string'},
													{name: 'ScaleParameterResult_Value', type: 'string'}
												],
												data: aSprCombo
											}),

											tpl: '<tpl for="."><div class="x-combo-list-item">' +
											'{ScaleParameterResult_Name} ' + '&nbsp;' +
											'</div></tpl>',
											listeners: {
												specialkey: function (field, e) {
													// console.log('FIELD', field)
													if (e.getKey() == e.ENTER) {
													}
												},
												select: function (combo, record, index) {
													Ext.getCmp(combo.id.replace("Param", "Field")).setValue(record.json[3]);
													form.calcReabScale(cSysNick);
												}
											}
										});
								oCombo[j] = Combo;
							}

							var PainQuestion = new Ext.Panel({
								baseCls: 'x-plain',
								id: 'PainQuestion_' + cSysNick,
								border: false,
								//width: 750,
								width: 'auto',
								disabled: false,
								layout: 'form',
								bodyStyle: 'padding: 10px;background-color: #E2FFE9', //+bgcolor
								tabIndex: -1,
								items: [
									new Ext.form.Label({
												id: 'cName_' + mName[0][2],
												text: mName[0][0],
												height: 10,
												style: 'font-style:italic;font-size:1.2em;color:blue; '
											}
									),
									{
										border: false,
										layout: 'column',
										bodyStyle: 'padding: 5px;background-color: #E2FFE9',
										items: [
											oCombo[0],
											new Ext.form.TextField({
												allowBlank: true,
												disabled: true,
												style: 'margin: 0px 0px 0px 20px;font-style:normal;font-size:1.2em;color:blue;text-align: center;font-width: bold',
												id: 'HarrisField_' + mName[0][2],
												width: 40
											})
										]
									}
								]
							});
							Ext.getCmp('Panelpain' + cSysNick).add(PainQuestion);

							var FuncQuestion = new Ext.Panel({
								baseCls: 'x-plain',
								id: 'FuncQuestion_' + cSysNick,
								border: false,
								width: 750,
								disabled: false,
								layout: 'form',
								bodyStyle: 'padding: 10px;background-color: #E2FFE9', //+bgcolor
								tabIndex: -1,
								items: [
									new Ext.form.Label({
												id: 'cName_' + mName[1][2],
												text: mName[1][0],
												height: 10,
												style: 'font-style:italic;font-size:1.2em;color:blue; '
											}
									),
									{
										// xtype: 'panel',
										border: false,
										layout: 'column',
										// style: 'padding: 10px;border: 0px solid #ffffff;',
										bodyStyle: 'padding: 5px;background-color: #E2FFE9',
										items: [
											oCombo[1],
											new Ext.form.TextField({
												allowBlank: true,
												disabled: true,
												style: 'margin: 0px 0px 0px 20px;font-style:normal;font-size:1.2em;color:blue;text-align: center;font-width: bold',
												id: 'HarrisField_' + mName[1][2],
												width: 40
											})
										]
									},
									new Ext.form.Label({
												id: 'cName_' + mName[2][2],
												text: mName[2][0],
												height: 10,
												style: 'font-style:italic;font-size:1.2em;color:blue; '
											}
									),
									{
										// xtype: 'panel',
										border: false,
										layout: 'column',
										// style: 'padding: 10px;border: 0px solid #ffffff;',
										bodyStyle: 'padding: 5px;background-color: #E2FFE9',
										items: [
											oCombo[2],
											new Ext.form.TextField({
												allowBlank: true,
												disabled: true,
												style: 'margin: 0px 0px 0px 20px;font-style:normal;font-size:1.2em;color:blue;text-align: center;font-width: bold',
												id: 'HarrisField_' + mName[2][2],
												width: 40
											})
										]
									},
									new Ext.form.Label({
												id: 'cName_' + mName[3][2],
												text: mName[3][0],
												height: 10,
												style: 'font-style:italic;font-size:1.2em;color:blue; '
											}
									),
									{
										// xtype: 'panel',
										border: false,
										layout: 'column',
										// style: 'padding: 10px;border: 0px solid #ffffff;',
										bodyStyle: 'padding: 5px;background-color: #E2FFE9',
										items: [
											oCombo[3],
											new Ext.form.TextField({
												allowBlank: true,
												disabled: true,
												style: 'margin: 0px 0px 0px 20px;font-style:normal;font-size:1.2em;color:blue;text-align: center;font-width: bold',
												id: 'HarrisField_' + mName[3][2],
												width: 40
											})
										]
									},
									new Ext.form.Label({
												id: 'cName_' + mName[4][2],
												text: mName[4][0],
												height: 10,
												style: 'font-style:italic;font-size:1.2em;color:blue; '
											}
									),
									{
										// xtype: 'panel',
										border: false,
										layout: 'column',
										bodyStyle: 'padding: 5px;background-color: #E2FFE9',
										items: [
											oCombo[4],
											new Ext.form.TextField({
												allowBlank: true,
												disabled: true,
												style: 'margin: 0px 0px 0px 20px;font-style:normal;font-size:1.2em;color:blue;text-align: center;font-width: bold',
												id: 'HarrisField_' + mName[4][2],
												width: 40
											})
										]
									},
									new Ext.form.Label({
												id: 'cName_' + mName[5][2],
												text: mName[5][0],
												height: 10,
												style: 'font-style:italic;font-size:1.2em;color:blue; '
											}
									),
									{
										// xtype: 'panel',
										border: false,
										layout: 'column',
										bodyStyle: 'padding: 5px;background-color: #E2FFE9',
										items: [
											oCombo[5],
											new Ext.form.TextField({
												allowBlank: true,
												disabled: true,
												style: 'margin: 0px 0px 0px 20px;font-style:normal;font-size:1.2em;color:blue;text-align: center;font-width: bold',
												id: 'HarrisField_' + mName[5][2],
												width: 40
											})
										]
									},
									new Ext.form.Label({
												id: 'cName_' + mName[6][2],
												text: mName[6][0],
												height: 10,
												style: 'font-style:italic;font-size:1.2em;color:blue; '
											}
									),
									{
										border: false,
										layout: 'column',
										bodyStyle: 'padding: 5px;background-color: #E2FFE9',
										items: [
											oCombo[6],
											new Ext.form.TextField({
												allowBlank: true,
												disabled: true,
												style: 'margin: 0px 0px 0px 20px;font-style:normal;font-size:1.2em;color:blue;text-align: center;font-width: bold',
												id: 'HarrisField_' + mName[6][2],
												width: 40
											})
										]
									},
									new Ext.form.Label({
												id: 'cName_' + mName[7][2],
												text: mName[7][0],
												height: 10,
												style: 'font-style:italic;font-size:1.2em;color:blue; '
											}
									),
									{
										border: false,
										layout: 'column',
										bodyStyle: 'padding: 5px;background-color: #E2FFE9',
										items: [
											oCombo[7],
											new Ext.form.TextField({
												allowBlank: true,
												disabled: true,
												style: 'margin: 0px 0px 0px 20px;font-style:normal;font-size:1.2em;color:blue;text-align: center;font-width: bold',
												id: 'HarrisField_' + mName[7][2],
												width: 40
											})
										]
									}
								]
							});
							Ext.getCmp('PanelFunction' + cSysNick).add(FuncQuestion);

							var DeformQuestion = new Ext.Panel({
								baseCls: 'x-plain',
								id: 'DeformQuestion_' + cSysNick,
								border: false,
								width: 750,
								disabled: false,
								layout: 'form',
								bodyStyle: 'padding: 10px;background-color: #E2FFE9', //+bgcolor
								tabIndex: -1,
								items: [
									new Ext.form.Label({
												id: 'cName_' + mName[8][2],
												text: mName[8][0],
												height: 10,
												style: 'font-style:italic;font-size:1.2em;color:blue; '
											}
									),
									{
										border: false,
										layout: 'column',
										bodyStyle: 'padding: 5px;background-color: #E2FFE9',
										items: [
											oCombo[8],
											new Ext.form.TextField({
												allowBlank: true,
												disabled: true,
												style: 'margin: 0px 0px 0px 20px;font-style:normal;font-size:1.2em;color:blue;text-align: center;font-width: bold',
												id: 'HarrisField_' + mName[8][2],
												width: 40
											})
										]
									},
									new Ext.form.Label({
												id: 'cName_' + mName[9][2],
												text: mName[9][0],
												height: 10,
												style: 'font-style:italic;font-size:1.2em;color:blue; '
											}
									),
									{
										border: false,
										layout: 'column',
										bodyStyle: 'padding: 5px;background-color: #E2FFE9',
										items: [
											oCombo[9],
											new Ext.form.TextField({
												allowBlank: true,
												disabled: true,
												style: 'margin: 0px 0px 0px 20px;font-style:normal;font-size:1.2em;color:blue;text-align: center;font-width: bold',
												id: 'HarrisField_' + mName[9][2],
												width: 40
											})
										]
									},
									new Ext.form.Label({
												id: 'cName_' + mName[10][2],
												text: mName[10][0],
												height: 10,
												style: 'font-style:italic;font-size:1.2em;color:blue; '
											}
									),
									{
										border: false,
										layout: 'column',
										bodyStyle: 'padding: 5px;background-color: #E2FFE9',
										items: [
											oCombo[10],
											new Ext.form.TextField({
												allowBlank: true,
												disabled: true,
												style: 'margin: 0px 0px 0px 20px;font-style:normal;font-size:1.2em;color:blue;text-align: center;font-width: bold',
												id: 'HarrisField_' + mName[10][2],
												width: 40
											})
										]
									},
									new Ext.form.Label({
												id: 'cName_' + mName[11][2],
												text: mName[11][0],
												height: 10,
												style: 'font-style:italic;font-size:1.2em;color:blue; '
											}
									),
									{
										border: false,
										layout: 'column',
										bodyStyle: 'padding: 5px;background-color: #E2FFE9',
										items: [
											oCombo[11],
											new Ext.form.TextField({
												allowBlank: true,
												disabled: true,
												style: 'margin: 0px 0px 0px 20px;font-style:normal;font-size:1.2em;color:blue;text-align: center;font-width: bold',
												id: 'HarrisField_' + mName[11][2],
												width: 40
											})
										]
									},
								]
							});
							Ext.getCmp('PanelDeformation' + cSysNick).add(DeformQuestion);

							var AmplitQuestion = new Ext.Panel({
								baseCls: 'x-plain',
								id: 'AmplitQuestion_' + cSysNick,
								border: false,
								width: 750,
								disabled: false,
								layout: 'form',
								bodyStyle: 'padding: 10px;background-color: #E2FFE9', //+bgcolor
								tabIndex: -1,
								items: [
									new Ext.form.Label({
												id: 'cName_' + mName[12][2],
												text: mName[12][0],
												height: 10,
												style: 'font-style:italic;font-size:1.2em;color:blue; '
											}
									),
									{
										// xtype: 'panel',
										border: false,
										layout: 'column',
										bodyStyle: 'padding: 5px;background-color: #E2FFE9',
										items: [
											oCombo[12],
											new Ext.form.TextField({
												allowBlank: true,
												disabled: true,
												style: 'margin: 0px 0px 0px 20px;font-style:normal;font-size:1.2em;color:blue;text-align: center;font-width: bold',
												id: 'HarrisField_' + mName[12][2],
												width: 40
											})
										]
									},
									new Ext.form.Label({
												id: 'cName_' + mName[13][2],
												text: mName[13][0],
												height: 10,
												style: 'font-style:italic;font-size:1.2em;color:blue; '
											}
									),
									{
										border: false,
										layout: 'column',
										bodyStyle: 'padding: 5px;background-color: #E2FFE9',
										items: [
											oCombo[13],
											new Ext.form.TextField({
												allowBlank: true,
												disabled: true,
												style: 'margin: 0px 0px 0px 20px;font-style:normal;font-size:1.2em;color:blue;text-align: center;font-width: bold',
												id: 'HarrisField_' + mName[13][2],
												width: 40
											})
										]
									},
									new Ext.form.Label({
												id: 'cName_' + mName[14][2],
												text: mName[14][0],
												height: 10,
												style: 'font-style:italic;font-size:1.2em;color:blue; '
											}
									),
									{
										border: false,
										layout: 'column',
										bodyStyle: 'padding: 5px;background-color: #E2FFE9',
										items: [
											oCombo[14],
											new Ext.form.TextField({
												allowBlank: true,
												disabled: true,
												style: 'margin: 0px 0px 0px 20px;font-style:normal;font-size:1.2em;color:blue;text-align: center;font-width: bold',
												id: 'HarrisField_' + mName[14][2],
												width: 40
											})
										]
									},
									new Ext.form.Label({
												id: 'cName_' + mName[15][2],
												text: mName[15][0],
												height: 10,
												style: 'font-style:italic;font-size:1.2em;color:blue; '
											}
									),
									{
										border: false,
										layout: 'column',
										bodyStyle: 'padding: 5px;background-color: #E2FFE9',
										items: [
											oCombo[15],
											new Ext.form.TextField({
												allowBlank: true,
												disabled: true,
												style: 'margin: 0px 0px 0px 20px;font-style:normal;font-size:1.2em;color:blue;text-align: center;font-width: bold',
												id: 'HarrisField_' + mName[15][2],
												width: 40
											})
										]
									},
									new Ext.form.Label({
												id: 'cName_' + mName[16][2],
												text: mName[16][0],
												height: 10,
												style: 'font-style:italic;font-size:1.2em;color:blue; '
											}
									),
									{
										border: false,
										layout: 'column',
										bodyStyle: 'padding: 5px;background-color: #E2FFE9',
										items: [
											oCombo[16],
											new Ext.form.TextField({
												allowBlank: true,
												disabled: true,
												style: 'margin: 0px 0px 0px 20px;font-style:normal;font-size:1.2em;color:blue;text-align: center;font-width: bold',
												id: 'HarrisField_' + mName[16][2],
												width: 40
											})
										]
									},
								]
							});
							Ext.getCmp('PanelAmplitude' + cSysNick).add(AmplitQuestion);

							Ext.getCmp('Question_' + cSysNick).add(panel_pain);
							Ext.getCmp('Question_' + cSysNick).add(panel_function);
							Ext.getCmp('Question_' + cSysNick).add(panel_deformation);
							Ext.getCmp('Question_' + cSysNick).add(panel_amplitude);

							Ext.getCmp('FirstPanel' + cSysNick).add(func_question);
							Ext.getCmp('ViewScale_id').add(first_panel);
							Ext.getCmp('ViewScale_id').doLayout();
						}


						if (cSysNick == 'FIM' || cSysNick == 'dysarthria' || cSysNick == 'Lequesne' || cSysNick == 'rivermid_DAA' || cSysNick == 'nihss')
						{
							Ext.getCmp('ReabScaleValue').show();
							var first_panel = form.createFirstPanel('FirstPanel' + cSysNick, nameScale);

							var func_question = new Ext.Panel({
								baseCls: 'x-plain',
								id: 'Question_' + cSysNick,
								border: false,
								width: 500, /////////////////
								disabled: true, //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
								layout: 'form',
								// frame: true,
								bodyStyle: 'padding: 5px;background-color: #E2FFE9', //+bgcolor
								tabIndex: -1,
								items: []
							});
							var tt = new Ext.Panel({
								layout: 'form',
								border: false,
								height: 10
							});

							if (cSysNick == 'nihss')
							{
								var field_label = 'Тяжесть инсульта';
								var label_width = 160;

								var total_panel = new Ext.Panel({
									layout: 'form',
									// frame : true,
									border: false,
									//bodyStyle: 'padding: 10px;background-color: #E2FFE9', //+bgcolor
									style: 'border-top: 1px solid #99bbe8; border-left: 1px solid #99bbe8; border-bottom: 1px solid #ffffff;border-right: 1px solid #ffffff; padding: 2px 0px 0px 0px; margin-top: 5px;background-color: transparent ;',
									id: 'Reab' + cSysNick + 'Summ',
									items: [
										// Интерпретация результата
										new Ext.form.FieldSet(
												{
													border: false,
													autoHeight: true,
													hidden: false,
													bodyStyle: 'color:blue;',
													style: 'padding: 5px 10px;font-color: #AA0000;color:blue;',
													items: [
														{
															layout: 'form',
															border: false,
															items: [
																{
																	layout: 'form',
																	border: false,
																	labelWidth: label_width,
																	labelAlign: 'left',
																	items: [
																		new Ext.form.TextField({
																			allowBlank: true,
																			disabled: true,
																			style: 'font-style:normal;font-size:1.1em;color:black;text-align: center;font-width: bold',
																			labelStyle: 'font-style:italic;font-size:1.1em;color:blue;',
																			fieldLabel: field_label,
																			id: 'Reab' + cSysNick + 'Total',
																			width: 300
																		})
																	]
																}
															]
														}
													]
												})
									]
								});
								Ext.getCmp('Question_' + cSysNick).add(total_panel);
								Ext.getCmp('Question_' + cSysNick).add(tt);

								var level_consciousness_panel = form.createFirstPanel('Consciousness_panel' + cSysNick, 'Уровень сознания');
								var move_eyeballs_panel = form.createFirstPanel('Move_eyeballs_panel' + cSysNick, 'Движения глазных яблок');
								var fields_view_panel = form.createFirstPanel('Fields_view_panel' + cSysNick, 'Поля зрения');
								var func_facial_nerve_panel = form.createFirstPanel('Func_facial_nerve_panel' + cSysNick, 'Функция лицевого нерва');
								var strength_muscles_arm_panel = form.createFirstPanel('Strength_muscles_arm_panel' + cSysNick, 'Сила мышц верхних конечностей');
								var strength_muscles_leg_panel = form.createFirstPanel('Strength_muscles_leg_panel' + cSysNick, 'Сила мышц нижних конечностей');
								var ataxia_limb_panel = form.createFirstPanel('Ataxia_limb_panel' + cSysNick, 'Атаксия конечности');
								var sensitivity_panel = form.createFirstPanel('Sensitivity_panel' + cSysNick, 'Чувствительность');
								var speech_panel = form.createFirstPanel('Speech_panel' + cSysNick, 'Речь');
								var dysatria_panel = form.createFirstPanel('Dysatria_panel' + cSysNick, 'Дизартрия');
								var ignoring_panel = form.createFirstPanel('Ignoring_panel' + cSysNick, 'Игнорирование');

								Ext.getCmp('Question_' + cSysNick).add(level_consciousness_panel);
								Ext.getCmp('Question_' + cSysNick).add(move_eyeballs_panel);
								Ext.getCmp('Question_' + cSysNick).add(fields_view_panel);
								Ext.getCmp('Question_' + cSysNick).add(func_facial_nerve_panel);
								Ext.getCmp('Question_' + cSysNick).add(strength_muscles_arm_panel);
								Ext.getCmp('Question_' + cSysNick).add(strength_muscles_leg_panel);
								Ext.getCmp('Question_' + cSysNick).add(ataxia_limb_panel);
								Ext.getCmp('Question_' + cSysNick).add(sensitivity_panel);
								Ext.getCmp('Question_' + cSysNick).add(speech_panel);
								Ext.getCmp('Question_' + cSysNick).add(dysatria_panel);
								Ext.getCmp('Question_' + cSysNick).add(ignoring_panel);

							}

							if (cSysNick == 'FIM')
							{
								var total_panel = new Ext.Panel({
									layout: 'form',
									// frame : true,
									border: false,
									// style: 'border-top: 1px solid #99bbe8; border-left: 1px solid #99bbe8; border-bottom: 1px solid #ffffff;border-right: 1px solid #ffffff; padding: 2px 0px 0px 0px; margin-top: 5px;background-color: transparent ;',
									id: 'Reab' + cSysNick + 'Summ',
									items: [
										// Интерпретация результата
										new Ext.form.FieldSet(
												{
													border: false,
													autoHeight: true,
													hidden: false,
													bodyStyle: 'color:blue;',
													style: 'border-top: 1px solid #99bbe8; border-left: 1px solid #99bbe8; border-bottom: 1px solid #ffffff;border-right: 1px solid #ffffff;padding: 2px 5px;font-color: #AA0000;color:blue;',
													border: true,
													title: 'Суммарные баллы по разделам',
													items: [
														{
															layout: 'form',
															border: false,
															items: [
																{
																	layout: 'column',
																	border: false,
																	items: [
																		{
																			layout: 'form',
																			border: false,
																			labelWidth: 160,
																			labelAlign: 'left',
																			items: [
																				new Ext.form.TextField({
																					allowBlank: true,
																					disabled: true,
																					style: 'font-style:normal;font-size:1.1em;color:black;text-align: center;font-width: bold',
																					labelStyle: 'font-style:italic;font-size:1.1em;color:blue;',
																					fieldLabel: 'Двигательные функции',
																					id: 'Reab' + cSysNick + 'Motor_Func',
																					width: 60})
																			]
																		},
																		{
																			layout: 'form',
																			border: false,
																			labelWidth: 90,
																			labelAlign: 'rigth',
																			style: 'position:relative;  left:40px ',
																			//labelAlign: 'left',
																			items: [
																				new Ext.form.TextField({
																					allowBlank: true,
																					disabled: true,
																					style: 'font-style:normal;font-size:1.1em;color:black;text-align: center;font-width: bold',
																					labelStyle: 'font-style:italic;font-size:1.1em;color:blue;',
																					fieldLabel: 'Интеллект',
																					id: 'Reab' + cSysNick + 'Intellig',
																					width: 60})
																			]
																		}
																	]
																}
															]
														}
													]
												}),
									]
								});
								var motor_func_panel = form.createFirstPanel('MotorFuncPanelFIM', 'Двигательные функции');
								var panel_intellig = form.createFirstPanel('Panel_Intellig' + cSysNick, 'Интеллект');
								Ext.getCmp('Question_' + cSysNick).add(total_panel);
								Ext.getCmp('Question_' + cSysNick).add(tt);

								Ext.getCmp('Question_' + cSysNick).add(motor_func_panel);
								Ext.getCmp('Question_' + cSysNick).add(panel_intellig);

								//Самообслуживание
								var panel_service = form.createFirstPanel('Panel_Service' + cSysNick, 'Самообслуживание');
								// Контроль функции тазовых органов
								var panel_func_pelvic = form.createFirstPanel('Panel_Pelvic' + cSysNick, 'Контроль функции тазовых органов');

								//Перемещение
								var panel_moving = form.createFirstPanel('Panel_Moving' + cSysNick, 'Перемещение');

								//Подвижность
								var panel_mobility = form.createFirstPanel('Panel_Mobility' + cSysNick, 'Подвижность');

								//Общение
								var panel_communicat = form.createFirstPanel('Panel_Communicat' + cSysNick, 'Общение');

								//Социальная активность
								var panel_soc_activ = form.createFirstPanel('Panel_SocActiv' + cSysNick, 'Социальная активность');

							}
							if (cSysNick == 'dysarthria')
							{
								var field_label = 'Интерпретация теста';
								var label_width = 160;

								var total_panel = new Ext.Panel({
									layout: 'form',
									// frame : true,
									border: false,
									//bodyStyle: 'padding: 10px;background-color: #E2FFE9', //+bgcolor
									style: 'border-top: 1px solid #99bbe8; border-left: 1px solid #99bbe8; border-bottom: 1px solid #ffffff;border-right: 1px solid #ffffff; padding: 2px 0px 0px 0px; margin-top: 5px;background-color: transparent ;',
									id: 'Reab' + cSysNick + 'Summ',
									items: [
										// Интерпретация результата
										new Ext.form.FieldSet(
												{
													border: false,
													autoHeight: true,
													hidden: false,
													bodyStyle: 'color:blue;',
													style: 'padding: 5px 10px;font-color: #AA0000;color:blue;',
													items: [
														{
															layout: 'form',
															border: false,
															items: [
																{
																	layout: 'form',
																	border: false,
																	labelWidth: label_width,
																	labelAlign: 'left',
																	items: [
																		new Ext.form.TextField({
																			allowBlank: true,
																			disabled: true,
																			style: 'font-style:normal;font-size:1.1em;color:black;text-align: center;font-width: bold',
																			labelStyle: 'font-style:italic;font-size:1.1em;color:blue;',
																			fieldLabel: field_label,
																			id: 'Reab' + cSysNick + 'Total',
																			width: 300
																		})
																	]
																}
															]
														}
													]
												})
									]
								});
								Ext.getCmp('Question_' + cSysNick).add(total_panel);
								Ext.getCmp('Question_' + cSysNick).add(tt);
								//1.Оценка V пары ЧМН
								var panel_eval1 = form.createFirstPanel('Panel_eval1' + cSysNick, '1.Оценка V пары ЧМН');
								//2.Оценка VII пары ЧМН
								var panel_eval2 = form.createFirstPanel('Panel_eval2' + cSysNick, '2.Оценка VII пары ЧМН');
								//3.Оценка XI и XII пар ЧМН
								var panel_eval3 = form.createFirstPanel('Panel_eval3' + cSysNick, '3.Оценка XI и XII пар ЧМН');
								//4. Оценка IX и X пар ЧМН
								var panel_eval4 = form.createFirstPanel('Panel_eval4' + cSysNick, '4. Оценка IX и X пар ЧМНН');
								//5.Оценка голоса, темпа, ритма, интонационно-мелодической окраски речи и звукопроизношения
								var panel_eval5 = form.createFirstPanel('Panel_eval5' + cSysNick, '5.Оценка голоса, темпа, ритма, интонационно-мелодической окраски речи и звукопроизношения');

								Ext.getCmp('Question_' + cSysNick).add(panel_eval1);
								Ext.getCmp('Question_' + cSysNick).add(panel_eval2);
								Ext.getCmp('Question_' + cSysNick).add(panel_eval3);
								Ext.getCmp('Question_' + cSysNick).add(panel_eval4);
								Ext.getCmp('Question_' + cSysNick).add(panel_eval5);
							}

							if (cSysNick == 'rivermid_DAA')
							{
								var field_label = 'Интегральный показатель - ADL';
								var label_width = 230;

								var total_panel = new Ext.Panel({
									layout: 'form',
									// frame : true,
									border: false,
									style: 'border-top: 1px solid #99bbe8; border-left: 1px solid #99bbe8; border-bottom: 1px solid #ffffff;border-right: 1px solid #ffffff; padding: 2px 0px 0px 0px; margin-top: 5px;background-color: transparent ;',
									id: 'Reab' + cSysNick + 'Summ',
									items: [
										// Интерпретация результата
										new Ext.form.FieldSet(
												{
													border: false,
													autoHeight: true,
													hidden: false,
													bodyStyle: 'color:blue;',
													style: 'padding: 5px 10px;font-color: #AA0000;color:blue;',
													items: [
														{
															layout: 'form',
															border: false,
															items: [
																{
																	layout: 'form',
																	border: false,
																	labelWidth: label_width,
																	labelAlign: 'left',
																	items: [
																		new Ext.form.TextField({
																			allowBlank: true,
																			disabled: true,
																			style: 'font-style:normal;font-size:1.1em;color:black;text-align: center;font-width: bold',
																			labelStyle: 'font-style:italic;font-size:1.1em;color:blue;',
																			fieldLabel: field_label,
																			id: 'Reab' + cSysNick + 'Total',
																			width: 100
																		})
																	]
																}
															]
														}
													]
												})
									]
								});
								Ext.getCmp('Question_' + cSysNick).add(total_panel);
								Ext.getCmp('Question_' + cSysNick).add(tt);
								//1.Самообслуживание
								var panel_eval1 = form.createFirstPanel('Panel_eval1' + cSysNick, '1.Самообслуживание');
								//2.Домашнее хозяйство I
								var panel_eval2 = form.createFirstPanel('Panel_eval2' + cSysNick, '2.Домашнее хозяйство I');
								//3.Домашнее хозяйство II
								var panel_eval3 = form.createFirstPanel('Panel_eval3' + cSysNick, '3.Домашнее хозяйство II');
								Ext.getCmp('Question_' + cSysNick).add(panel_eval1);
								Ext.getCmp('Question_' + cSysNick).add(panel_eval2);
								Ext.getCmp('Question_' + cSysNick).add(panel_eval3);
							}
							if (cSysNick == 'Lequesne')
							{
								var total_panel = new Ext.Panel({
									layout: 'form',
									// frame : true,
									border: false,
									id: 'Reab' + cSysNick + 'Summ',
									items: [
										// Интерпретация результата
										new Ext.form.FieldSet(
												{
													border: false,
													autoHeight: true,
													hidden: false,
													bodyStyle: 'color:blue;',
													style: 'border-top: 1px solid #99bbe8; border-left: 1px solid #99bbe8; border-bottom: 1px solid #ffffff;border-right: 1px solid #ffffff;padding: 2px 5px;font-color: #AA0000;color:blue;',
													border: true,
													title: 'Результат',
													items: [
														{
															layout: 'form',
															border: false,
															items: [
																{
																	layout: 'column',
																	border: false,
																	items: [
																		{
																			layout: 'form',
																			border: false,
																			labelWidth: 250,
																			labelAlign: 'left',
																			items: [
																				new Ext.form.TextField({
																					allowBlank: true,
																					disabled: true,
																					style: 'font-style:normal;font-size:1.1em;color:black;text-align: center;font-width: bold',
																					labelStyle: 'font-style:italic;font-size:1.1em;color:blue;',
																					fieldLabel: 'Ограничение жизнедеятельности',
																					id: 'Reab' + cSysNick + 'Total',
																					width: 150})
																			]
																		}
																	]
																}
															]
														}
													]
												}),
									]
								});
								Ext.getCmp('Question_' + cSysNick).add(total_panel);
								Ext.getCmp('Question_' + cSysNick).add(tt);
								//Готовим данные для Combo критериев
								var aSprComboJoint = new Array();
								var aSprComboSide = new Array();

								for (var ii = 0; ii < ObjScale.SprParam1.length; ii++)
								{
									aSprComboJoint.push([ObjScale.SprParam1[ii].paramId, ObjScale.SprParam1[ii].paramName]);
								}
								for (var ii = 0; ii < ObjScale.SprParam2.length; ii++)
								{
									aSprComboSide.push([ObjScale.SprParam2[ii].paramId, ObjScale.SprParam2[ii].paramName]);
								}

								// критерии справочника
								var criteria = new Ext.Panel({
									layout: 'form',
									// frame : true,
									border: false,
									items: [
										{
											layout: 'column',
											border: false,
											items: [
												//combo Вид сустава, Сторона
												{
													layout: 'form',
													border: false,
													labelWidth: 100,
													labelAlign: 'left',
													items: [
														//Вид сустава
														{
															allowBlank: false,
															style: 'text-align : center; font-size:1.1em; ',
															id: cSysNick + 'Type_of_joint_id',
															xtype: 'combo',
															width: 150,
															fieldLabel: 'Вид сустава',
															labelStyle: 'font-style:italic;font-size:1.1em;color:blue;',
															hideTrigger: false, // Chek
															mode: 'local',
															editable: false,
															triggerAction: 'all',
															displayField: 'SprName',
															valueField: 'ReabSpr_Elem_id',
															tabIndex: -1,
															emptyText: 'Введите параметр',
															// listWidth:'auto',
															hiddenName: 'ReabSpr_Elem_id',
															autoscroll: false,
															xtype: 'combo',
															store: new Ext.data.SimpleStore({
																fields: [
																	{name: 'ReabSpr_Elem_id', type: 'int'},
																	{name: 'SprName', type: 'string'},
																	// {name: 'ReabSpr_Elem_Weight', type: 'string'}
																],
																data: aSprComboJoint
															}),
															tpl: '<tpl for="."><div class="x-combo-list-item">' +
															'{SprName} ' + '&nbsp;' +
															'</div></tpl>'
														}
													]
												},
												{
													layout: 'form',
													border: false,
													labelWidth: 80,
													labelAlign: 'rigth',
													style: 'position:relative;  left:20px ',
													items: [
														//Сторона
														{
															allowBlank: false,
															style: 'text-align : center; font-size:1.1em; ',
															id: cSysNick + 'Side_id',
															xtype: 'combo',
															fieldLabel: 'Сторона',
															labelStyle: 'font-style:italic;font-size:1.1em;color:blue;',
															hideTrigger: false, // Chek
															mode: 'local',
															editable: false,
															triggerAction: 'all',
															displayField: 'SprName',
															valueField: 'ReabSpr_Elem_id',
															width: 150,
															emptyText: 'Введите параметр',
															// listWidth:'auto',
															hiddenName: 'ReabSpr_Elem_id',
															autoscroll: false,
															xtype: 'combo',
															store: new Ext.data.SimpleStore({
																fields: [
																	{name: 'ReabSpr_Elem_id', type: 'int'},
																	{name: 'SprName', type: 'string'}
																],
																data: aSprComboSide
															}),
															tpl: '<tpl for="."><div class="x-combo-list-item">' +
															'{SprName} ' + '&nbsp;' +
															'</div></tpl>'
														}]
												},
											]}
									]
								});
								Ext.getCmp('Question_' + cSysNick).add(criteria);
								//1.Боль и дискомфорт
								var panel_eval1 = form.createFirstPanel('Panel_eval1' + cSysNick, '1.Боль или дискомфорт');
								//2.Максимальная дистанция передвижения
								var panel_eval2 = form.createFirstPanel('Panel_eval2' + cSysNick, '2.Максимальная дистанция передвижения');
								//3.Повседневная активность
								var panel_eval3 = form.createFirstPanel('Panel_eval3' + cSysNick, '3.Повседневная активность');
								Ext.getCmp('Question_' + cSysNick).add(panel_eval1);
								Ext.getCmp('Question_' + cSysNick).add(panel_eval2);
								Ext.getCmp('Question_' + cSysNick).add(panel_eval3);
							}

							// Наполнение панелей
							//console.log('ObjScale=',ObjScale);
							//Загрузка Combo
							var oSprCombo = new Object();
							// var oCombo = new Object();
							var mName = new Array();

							//Определяем кол-во параметров
							var nRec = 1;
							var cKey = ObjScale.SprScale[0].ScaleParameterType_id;
							//mName.push(ObjScale.SprScale[0].ScaleParameterType_Name);
							mName.push([ObjScale.SprScale[0].ScaleParameterType_Name, ObjScale.SprScale[0].ScaleParameterType_id, 1]);

							for (var ii = 0; ii < Object.keys(ObjScale.SprScale).length; ii++)
							{
								//console.log('nRec=',nRec);
								if (ObjScale.SprScale[ii].ScaleParameterType_id != cKey)
								{
									nRec++;
									cKey = ObjScale.SprScale[ii].ScaleParameterType_id;
									//mName.push(ObjScale.SprScale[ii].ScaleParameterType_Name);
									mName.push([ObjScale.SprScale[ii].ScaleParameterType_Name, ObjScale.SprScale[ii].ScaleParameterType_id, nRec]);
								}
							}
							// console.log('mName=',mName);
							// Формируем параметры
							for (var j = 0; j < nRec; j++)
							{
								var aSprCombo = new Array();
								for (var kk in ObjScale.SprScale)
								{
									if (ObjScale.SprScale[kk].ScaleParameterType_id == mName[j][1])
									{
										aSprCombo.push([ObjScale.SprScale[kk].ScaleParameterType_id,
											ObjScale.SprScale[kk].ScaleParameterResult_id,
											ObjScale.SprScale[kk].ScaleParameterResult_Name,
											ObjScale.SprScale[kk].ScaleParameterResult_Value]);
									}

								}
								;
								oSprCombo[j] = aSprCombo;
								var Combo = new Ext.form.ComboBox(
										{
											allowBlank: false,
											id: cSysNick + 'Param_' + mName[j][2],
											hideLabel: true,
											hideTrigger: false, // Chek
											//style: 'position:relative; top: 15px ',
											mode: 'local',
											editable: false,
											triggerAction: 'all',
											displayField: 'ScaleParameterResult_Name',
											valueField: 'ScaleParameterResult_id',
											hiddenName: 'ReabSpr_Elem_id',
											style: cStyle,
											width: 'auto',
											tabIndex: -1,
											emptyText: 'Введите параметр',
											//listWidth:'auto',
											autoscroll: false,
											xtype: 'combo',
											store: new Ext.data.SimpleStore({
												fields: [
													{name: 'ScaleParameterType_id', type: 'int'},
													{name: 'ScaleParameterResult_id', type: 'int'},
													{name: 'ScaleParameterResult_Name', type: 'string'},
													{name: 'ScaleParameterResult_Value', type: 'string'}
												],
												data: aSprCombo
											}),

											tpl: '<tpl for="."><div class="x-combo-list-item">' +
											'{ScaleParameterResult_Name} ' + '&nbsp;' +
											'</div></tpl>',
											listeners: {
												specialkey: function (field, e) {
													// console.log('FIELD', field)
													if (e.getKey() == e.ENTER) {
													}
												},
												select: function (combo, record, index) {
													//Ext.getCmp(cSysNick + 'Field_'+ record.json[0]).setValue(record.json[3]);
													Ext.getCmp(combo.id.replace('Param', 'Field')).setValue(record.json[3]);
													form.calcReabScale(cSysNick);
													if (combo.id == "nihssParam_7" && record.data.ScaleParameterResult_id == 1177 || combo.id == "nihssParam_8" && record.data.ScaleParameterResult_id == 1183 ||
															combo.id == "nihssParam_9" && record.data.ScaleParameterResult_id == 1189 || combo.id == "nihssParam_10" && record.data.ScaleParameterResult_id == 1195 ||
															combo.id == "nihssParam_11" && record.data.ScaleParameterResult_id == 1199 || combo.id == "nihssParam_14" && record.data.ScaleParameterResult_id == 1210)
													{
														//console.log('record=',record);
														Ext.getCmp(combo.id.replace('Param', 'Area')).show();
													}
													if (combo.id == "nihssParam_7" && record.data.ScaleParameterResult_id != 1177 || combo.id == "nihssParam_8" && record.data.ScaleParameterResult_id != 1183 ||
															combo.id == "nihssParam_9" && record.data.ScaleParameterResult_id != 1189 || combo.id == "nihssParam_10" && record.data.ScaleParameterResult_id != 1195 ||
															combo.id == "nihssParam_11" && record.data.ScaleParameterResult_id != 1199 || combo.id == "nihssParam_14" && record.data.ScaleParameterResult_id != 1210)
													{
														Ext.getCmp(combo.id.replace('Param', 'Area')).hide();
														Ext.getCmp(combo.id.replace('Param', 'Area')).setValue();
													}

												}
											}
										});

								// Пробуем по новому
								var o_label = new Ext.form.Label({
											id: 'cName_' + mName[j][2],
											text: mName[j][0],
											height: 10,
											style: 'font-style:italic;font-size:1.2em;color:blue;'
										}
								);
								//console.log('oLabel=',o_label);
								if (cSysNick == 'nihss' && (mName[j][1] == 260 || mName[j][1] == 261 || mName[j][1] == 262 || mName[j][1] == 263 || mName[j][1] == 264 || mName[j][1] == 267))
								{

									//console.log('mName[j]=',mName[j]);
									var o_panel_combo = new Ext.Panel({
										border: false,
										layout: 'form',
										bodyStyle: 'padding: 5px;background-color: #E2FFE9',
										items: [
											new Ext.Panel({
												border: false,
												layout: 'column',
												bodyStyle: 'padding: 5px;background-color: #E2FFE9',
												items: [
													Combo,
													new Ext.form.TextField({
														allowBlank: true,
														disabled: true,
														style: 'margin: 0px 0px 0px 20px;font-style:normal;font-size:1.2em;color:blue;text-align: center;font-width: bold',
														id: cSysNick + 'Field_' + mName[j][2],
														width: 40
													})
												]
											}),
											new Ext.form.TextArea({
												allowBlank: true,
												disabled: false,
												labelSeparator: "",
												maxLength: 100,
												maxLengthText: "Превышен максимальный размер текста(100 символов)",
												hideLabel: true,
												//  style:'margin: 0px 0px 0px 20px;font-style:normal;font-size:1.2em;color:blue;text-align: center;font-width: bold',
												id: cSysNick + 'Area_' + mName[j][2],
												height: 40,
												width: 350
											})
										]
									});

								} else
								{
									var o_panel_combo = new Ext.Panel({
										border: false,
										layout: 'column',
										bodyStyle: 'padding: 5px;background-color: #E2FFE9',
										items: [
											Combo,
											new Ext.form.TextField({
												allowBlank: true,
												disabled: true,
												style: 'margin: 0px 0px 0px 20px;font-style:normal;font-size:1.2em;color:blue;text-align: center;font-width: bold',
												id: cSysNick + 'Field_' + mName[j][2],
												width: 40
											})
										]
									});
								}

								//console.log('o_panel_combo=',o_panel_combo);

								var pan_id = "";
								if (cSysNick == 'FIM')
								{
									switch (mName[j][2]) {
										case 1:
										case 2:
										case 3:
										case 4:
										case 5:
										case 6:
											pan_id = "Panel_Service";
											break;
										case 7:
										case 8:
											pan_id = "Panel_Pelvic";
											break;
										case 9:
										case 10:
										case 11:
											pan_id = 'Panel_Moving';
											break;
										case 12:
										case 13:
											pan_id = 'Panel_Mobility';
											break;
										case 14:
										case 15:
											pan_id = 'Panel_Communicat';
											break;
										case 16:
										case 17:
										case 18:
											pan_id = 'Panel_SocActiv';
											break;
										default :
											pan_id = "";
											break;
									}
									;
								}
								//console.log('pan_id=',pan_id);
								if (cSysNick == 'nihss')
								{
									switch (mName[j][2]) {
										case 1:
										case 2:
										case 3:
											pan_id = "Consciousness_panel";
											break;
										case 4:
											pan_id = "Move_eyeballs_panel";
											break;
										case 5:
											pan_id = "Fields_view_panel";
											break;
										case 6:
											pan_id = "Func_facial_nerve_panel";
											break;
										case 7:
										case 8:
											pan_id = "Strength_muscles_arm_panel";
											break;
										case 9:
										case 10:
											pan_id = 'Strength_muscles_leg_panel';
											break;
										case 11:
											pan_id = 'Ataxia_limb_panel';
											break;
										case 12:
											pan_id = 'Sensitivity_panel';
											break;
										case 13:
											pan_id = 'Speech_panel';
											break;
										case 14:
											pan_id = 'Dysatria_panel';
											break;
										case 15:
											pan_id = 'Ignoring_panel';
											break;
										default :
											pan_id = "";
											break;
									}
									;
								}
								if (cSysNick == 'dysarthria')
								{
									switch (mName[j][2]) {
										case 1:
										case 2:
											pan_id = "Panel_eval1";
											break;
										case 3:
										case 4:
										case 5:
											pan_id = "Panel_eval2";
											break;
										case 6:
										case 7:
										case 8:
										case 9:
										case 10:
										case 11:
											pan_id = "Panel_eval3";
											break;
										case 12:
										case 13:
										case 14:
										case 15:
											pan_id = "Panel_eval4";
											break;
										case 16:
										case 17:
										case 18:
										case 19:
											pan_id = "Panel_eval5";
											break;
										default :
											pan_id = "";
											break;
									}
									;
								}
								if (cSysNick == 'Lequesne')
								{
									switch (mName[j][2]) {
										case 1:
										case 2:
										case 3:
										case 4:
										case 5:
											pan_id = "Panel_eval1";
											break;
										case 6:
										case 7:
											pan_id = "Panel_eval2";
											break;
										case 8:
										case 9:
										case 10:
										case 11:
											pan_id = "Panel_eval3";
											break;
										default :
											pan_id = "";
											break;
									}
									;
								}
								if (cSysNick == 'rivermid_DAA')
								{
									switch (mName[j][2]) {
										case 1:
										case 2:
										case 3:
										case 4:
										case 5:
										case 6:
										case 7:
										case 8:
										case 9:
										case 10:
										case 11:
										case 12:
										case 13:
										case 14:
										case 15:
										case 16:
											pan_id = "Panel_eval1";
											break;
										case 17:
										case 18:
										case 19:
										case 20:
										case 21:
										case 22:
										case 23:
										case 24:
										case 25:
											pan_id = "Panel_eval2";
											break;
										case 26:
										case 27:
										case 28:
										case 29:
										case 30:
										case 31:
											pan_id = "Panel_eval3";
											break;
										default :
											pan_id = "";
											break;
									}
									;
								}
								if (pan_id != "")
								{
									Ext.getCmp(pan_id + cSysNick).add(o_label);
									Ext.getCmp(pan_id + cSysNick).add(o_panel_combo);
								}
							}
							//console.log('FIM22=');
							if (cSysNick == 'FIM')
							{
								Ext.getCmp('MotorFuncPanel' + cSysNick).add(panel_service);
								Ext.getCmp('MotorFuncPanel' + cSysNick).add(panel_func_pelvic);
								Ext.getCmp('MotorFuncPanel' + cSysNick).add(panel_moving);
								Ext.getCmp('MotorFuncPanel' + cSysNick).add(panel_mobility);

								Ext.getCmp('Panel_Intellig' + cSysNick).add(panel_communicat);
								Ext.getCmp('Panel_Intellig' + cSysNick).add(panel_soc_activ);
							}


							Ext.getCmp('FirstPanel' + cSysNick).add(func_question);
							Ext.getCmp('ViewScale_id').add(first_panel);
							Ext.getCmp('ViewScale_id').doLayout();
						}

						if (cSysNick == 'Alarm_HADS' || cSysNick == 'Depression_HADS' || cSysNick == 'Berg' || cSysNick == 'Frenchay' || cSysNick == 'Bartel' || cSysNick == 'Vasserman')
						{
							//console.log('Будем делать=',cSysNick);

							Ext.getCmp('ReabScaleValue').show();
							var first_panel = form.createFirstPanel('FirstPanel' + cSysNick, cTitleScale);
							Ext.getCmp('ViewScale_id').add(first_panel);

							//Загрузка Combo
							var oSprCombo = new Object();
							var oCombo = new Object();
							var mName = new Array();
							//console.log('Object.keys(ObjScale.SprScale)=',Object.keys(ObjScale.SprScale));
							//  console.log('ObjScale.SprScale.length=',Object.keys(ObjScale.SprScale).length);
							//Определяем кол-во параметров
							var nRec = 1;
							var cKey = ObjScale.SprScale[0].ScaleParameterType_id;
							//mName.push(ObjScale.SprScale[0].ScaleParameterType_Name);
							mName.push([ObjScale.SprScale[0].ScaleParameterType_Name, ObjScale.SprScale[0].ScaleParameterType_id, 1]);

							for (var ii = 0; ii < Object.keys(ObjScale.SprScale).length; ii++)
							{
								//console.log('nRec=',nRec);
								if (ObjScale.SprScale[ii].ScaleParameterType_id != cKey)
								{
									nRec++;
									cKey = ObjScale.SprScale[ii].ScaleParameterType_id;
									//  mName.push(ObjScale.SprScale[ii].ScaleParameterType_Name);
									mName.push([ObjScale.SprScale[ii].ScaleParameterType_Name, ObjScale.SprScale[ii].ScaleParameterType_id, nRec]);
								}
							}
							//  console.log('mName=',mName);

							for (var j = 0; j < nRec; j++)
							{
								var aSprCombo = new Array();
								for (var kk in ObjScale.SprScale)
								{
									if (ObjScale.SprScale[kk].ScaleParameterType_id == mName[j][1])
									{
										aSprCombo.push([ObjScale.SprScale[kk].ScaleParameterType_id,
											ObjScale.SprScale[kk].ScaleParameterResult_id,
											ObjScale.SprScale[kk].ScaleParameterResult_Name,
											ObjScale.SprScale[kk].ScaleParameterResult_Value]);
									}

								}
								;
								oSprCombo[j] = aSprCombo;
								var Combo = new Ext.form.ComboBox(
										{
											allowBlank: false,
											id: cSysNick + 'Param_' + mName[j][2],
											hideLabel: true,
											hideTrigger: false, // Chek
											//style: 'position:relative; top: 15px ',
											mode: 'local',
											editable: false,
											triggerAction: 'all',
											displayField: 'ScaleParameterResult_Name',
											valueField: 'ScaleParameterResult_id',
											//style : 'width: 450px; border:none;font-size:1.1em',
											style: cStyle,
											width: 'auto',
											tabIndex: -1,
											emptyText: 'Введите параметр',
											listWidth: 'auto',
											hiddenName: 'ReabSpr_Elem_id',
											autoscroll: false,
											xtype: 'combo',
											store: new Ext.data.SimpleStore({
												fields: [
													{name: 'ScaleParameterType_id', type: 'int'},
													{name: 'ScaleParameterResult_id', type: 'int'},
													{name: 'ScaleParameterResult_Name', type: 'string'},
													{name: 'ScaleParameterResult_Value', type: 'string'}
												],
												data: aSprCombo
											}),

											tpl: '<tpl for="."><div class="x-combo-list-item">' +
											'{ScaleParameterResult_Name} ' + '&nbsp;' +
											'</div></tpl>',
											listeners: {
												specialkey: function (field, e) {
													console.log('FIELD', field)
													if (e.getKey() == e.ENTER) {
													}
												},
												select: function (combo, record, index) {
													// console.log('combo1=',combo.id.replace('Param','Field'));
													Ext.getCmp(combo.id.replace('Param', 'Field')).setValue(record.json[3]);
													form.calcReabScale(cSysNick);
													// console.log('Просчитали');
												}
											}
										});
								oCombo[j] = Combo;
							}
							//  console.log('nRec=',nRec);

							var FuncQuestion = new Ext.Panel({
								baseCls: 'x-plain',
								id: 'Question_' + cSysNick,
								border: false,
								width: nWidth1,
								disabled: true,
								layout: 'form',
								bodyStyle: 'padding: 10px;background-color: #E2FFE9', //+bgcolor
								tabIndex: -1,
								items: []
							});

							//Формирование итогов для Berg
							if (cSysNick == 'Berg' || cSysNick == 'Vasserman' || cSysNick == 'Alarm_HADS' || cSysNick == 'Depression_HADS')
							{
								var cFieldLabel = "";
								var cLabelWidth = 0;
								var nFieldLabelWidth = 0;
								switch (cSysNick) {
									case 'Berg':
										cFieldLabel = 'Итог';
										cLabelWidth = 50;
										nFieldLabelWidth = 200;
										break;
									case 'Vasserman':
										cFieldLabel = 'Cтепень нарушения';
										cLabelWidth = 150;
										nFieldLabelWidth = 200;
										break;
									case 'Alarm_HADS':
									case 'Depression_HADS':
										cFieldLabel = 'Интерпретация результата';
										cLabelWidth = 180;
										nFieldLabelWidth = 400;
										break;
									default :
										form.showMsg('Косяк');
										return;
										break;
								}
								;

								var TotalBergPanel = new Ext.Panel({
									layout: 'form',
									// frame : true,
									border: false,
									//bodyStyle: 'padding: 10px;background-color: #E2FFE9', //+bgcolor
									style: 'border-top: 1px solid #99bbe8; border-left: 1px solid #99bbe8; border-bottom: 1px solid #ffffff;border-right: 1px solid #ffffff; padding: 2px 0px 0px 0px; margin-top: 5px;background-color: transparent ;',
									id: 'Reab' + cSysNick + 'Summ',
									items: [
										// Интерпретация результата
										new Ext.form.FieldSet(
												{
													border: false,
													autoHeight: true,
													hidden: false,
													bodyStyle: 'color:blue;',
													style: 'padding: 5px 10px;font-color: #AA0000;color:blue;',
													items: [
														{
															layout: 'form',
															border: false,
															items: [
																{
																	layout: 'form',
																	border: false,
																	labelWidth: cLabelWidth,
																	labelAlign: 'left',
																	items: [
																		new Ext.form.TextField({
																			allowBlank: true,
																			disabled: true,
																			style: 'font-style:normal;font-size:1.1em;color:black;text-align: center;font-width: bold',
																			labelStyle: 'font-style:italic;font-size:1.1em;color:blue;',
																			fieldLabel: cFieldLabel,
																			id: 'Reab' + cSysNick + 'Total',
																			width: nFieldLabelWidth
																		})
																	]
																}
															]
														}
													]
												})
									]
								});

								var tt = new Ext.Panel({
									layout: 'form',
									border: false,
									height: 20
								});

								Ext.getCmp('Question_' + cSysNick).add(TotalBergPanel);
								Ext.getCmp('Question_' + cSysNick).add(tt);

							}
							//
							//Вставляем параметры шкалы
							for (var jj = 0; jj < nRec; jj++)
							{
								var oScalrLabel = new Ext.form.Label({
											id: 'cName_' + mName[jj][2],
											text: mName[jj][0],
											height: 10,
											style: 'font-style:italic;font-size:1.2em;color:blue; '
										}
								);
								//mName[0][2]
								var oScaleCombo = new Ext.Panel({
									border: false,
									layout: 'column',
									// style: 'padding: 10px;border: 0px solid #ffffff;',
									bodyStyle: 'padding: 5px;background-color: #E2FFE9',
									items: [
										oCombo[jj],
										new Ext.form.TextField({
											allowBlank: true,
											disabled: true,
											style: 'margin: 0px 0px 0px 20px;font-style:normal;font-size:1.2em;color:blue;text-align: center;font-width: bold',
											//  id: cSysNick+'Field_'+ oSprCombo[jj][0][0],
											id: cSysNick + 'Field_' + mName[jj][2],
											width: 40
										})
									]
								});
								Ext.getCmp('Question_' + cSysNick).add(oScalrLabel);
								Ext.getCmp('Question_' + cSysNick).add(oScaleCombo);
							}

							Ext.getCmp('FirstPanel' + cSysNick).add(FuncQuestion);


							Ext.getCmp('ViewScale_id').doLayout();
						}

						//Шкала МоСА
						if (cSysNick == 'МоСА')
						{
							Ext.getCmp('ReabScaleValue').show();
							var first_panel = form.createFirstPanel('FirstPanel' + cSysNick, cScaleName);

							Ext.getCmp('ViewScale_id').add(first_panel);

							//Зрительно-конструктивные навыки
							var panel_visual_skills = form.createFirstPanel('PanelVisualSkills' + cSysNick, 'Зрительно-конструктивные/исполнительные навыки');
							//Называние
							var panel_naming = form.createFirstPanel('PanelNaming' + cSysNick, 'Называние');
							//Внимание
							var panel_attention = form.createFirstPanel('PanelAttention' + cSysNick, 'Внимание');
							//Речь
							var panel_speech = form.createFirstPanel('PanelSpeech' + cSysNick, 'Речь');
							//Абстракция
							var panel_abstraction = form.createFirstPanel('PanelAbstraction' + cSysNick, 'Абстракция');
							//Отсроченное воспроизведение
							var panel_playback = form.createFirstPanel('PanelPlayback' + cSysNick, 'Отсроченное воспроизведение');
							//Ориентация
							var panel_orientation = form.createFirstPanel('PanelOrientation' + cSysNick, 'Ориентация');


							//Загрузка Combo
							var oSprCombo = new Object();
							var oCombo = new Object();
							var mName = new Array();
							// console.log('Object.keys(ObjScale.SprScale)=',Object.keys(ObjScale.SprScale));
							//console.log('ObjScale.SprScale.length=',Object.keys(ObjScale.SprScale).length);
							//Определяем кол-во параметров
							var nRec = 1;
							var cKey = ObjScale.SprScale[0].ScaleParameterType_id;
							mName.push([ObjScale.SprScale[0].ScaleParameterType_Name, ObjScale.SprScale[0].ScaleParameterType_id, 1]);
							// console.log('mName=',mName);
							for (var ii = 0; ii < Object.keys(ObjScale.SprScale).length; ii++)
							{
								if (ObjScale.SprScale[ii].ScaleParameterType_id != cKey)
								{
									nRec++;
									cKey = ObjScale.SprScale[ii].ScaleParameterType_id;
									mName.push([ObjScale.SprScale[ii].ScaleParameterType_Name, ObjScale.SprScale[ii].ScaleParameterType_id, nRec]);
								}
							}
							//console.log('nRec=',nRec);
							//console.log('mName=',mName);
							// Формируем параметры
							for (var j = 0; j < nRec; j++)
							{
								var aSprCombo = new Array();
								for (var kk in ObjScale.SprScale)
								{
									if (ObjScale.SprScale[kk].ScaleParameterType_id == mName[j][1])
									{
										aSprCombo.push([ObjScale.SprScale[kk].ScaleParameterType_id,
											ObjScale.SprScale[kk].ScaleParameterResult_id,
											ObjScale.SprScale[kk].ScaleParameterResult_Name,
											ObjScale.SprScale[kk].ScaleParameterResult_Value]);
									}

								}
								;
								oSprCombo[j] = aSprCombo;
								var Combo = new Ext.form.ComboBox(
										{
											allowBlank: false,
											id: cSysNick + 'Param_' + mName[j][2],
											hideLabel: true,
											hideTrigger: false, // Chek
											//style: 'position:relative; top: 15px ',
											mode: 'local',
											editable: false,
											triggerAction: 'all',
											displayField: 'ScaleParameterResult_Name',
											valueField: 'ScaleParameterResult_id',
											hiddenName: 'ReabSpr_Elem_id',
											style: 'width: 450px; border:none;font-size:1.1em',
											width: 'auto',
											tabIndex: -1,
											emptyText: 'Введите параметр',
											listWidth: 'auto',
											autoscroll: false,
											xtype: 'combo',
											store: new Ext.data.SimpleStore({
												fields: [
													{name: 'ScaleParameterType_id', type: 'int'},
													{name: 'ScaleParameterResult_id', type: 'int'},
													{name: 'ScaleParameterResult_Name', type: 'string'},
													{name: 'ScaleParameterResult_Value', type: 'string'}
												],
												data: aSprCombo
											}),

											tpl: '<tpl for="."><div class="x-combo-list-item">' +
											'{ScaleParameterResult_Name} ' + '&nbsp;' +
											'</div></tpl>',
											listeners: {
												specialkey: function (field, e) {
													console.log('FIELD', field)
													if (e.getKey() == e.ENTER) {
														//Ext.getCmp('getMorbusType_id').handler();
													}
												},
												select: function (combo, record, index) {
													Ext.getCmp(combo.id.replace('Param', 'Field')).setValue(record.json[3]);
													form.calcReabScale(cSysNick);
												}
											}
										});
								oCombo[j] = Combo;
							}

//							console.log('mName=', mName);
//							console.log('oCombo=', oCombo);
//							console.log('oSprCombo=', oSprCombo);

							var VisualSkillsQuestion = new Ext.Panel({
								baseCls: 'x-plain',
								id: 'VisualSkillsQuestion_' + cSysNick,
								border: false,
								width: 750,
								disabled: true,
								layout: 'form',
								bodyStyle: 'padding: 10px;background-color: #E2FFE9', //+bgcolor
								tabIndex: -1,
								items: [
									new Ext.form.Label({
												id: 'cName_' + mName[0][2],
												text: mName[0][0],
												height: 10,
												style: 'font-style:italic;font-size:1.2em;color:blue; '
											}
									),
									{
										border: false,
										layout: 'column',
										bodyStyle: 'padding: 5px;background-color: #E2FFE9',
										items: [
											oCombo[0],
											new Ext.form.TextField({
												allowBlank: true,
												disabled: true,
												style: 'margin: 0px 0px 0px 20px;font-style:normal;font-size:1.2em;color:blue;text-align: center;font-width: bold',
												id: cSysNick + 'Field_' + mName[0][2],
												width: 40
											})
										]
									},
									new Ext.form.Label({
												id: 'cName_' + mName[1][2],
												text: mName[1][0],
												height: 10,
												style: 'font-style:italic;font-size:1.2em;color:blue; '
											}
									),
									{
										border: false,
										layout: 'column',
										bodyStyle: 'padding: 5px;background-color: #E2FFE9',
										items: [
											oCombo[1],
											new Ext.form.TextField({
												allowBlank: true,
												disabled: true,
												style: 'margin: 0px 0px 0px 20px;font-style:normal;font-size:1.2em;color:blue;text-align: center;font-width: bold',
												id: cSysNick + 'Field_' + mName[1][2],
												width: 40
											})
										]
									},
									new Ext.form.Label({
												id: 'cName_' + mName[2][2],
												text: mName[2][0],
												height: 10,
												style: 'font-style:italic;font-size:1.2em;color:blue; '
											}
									),
									{
										border: false,
										layout: 'column',
										bodyStyle: 'padding: 5px;background-color: #E2FFE9',
										items: [
											oCombo[2],
											new Ext.form.TextField({
												allowBlank: true,
												disabled: true,
												style: 'margin: 0px 0px 0px 20px;font-style:normal;font-size:1.2em;color:blue;text-align: center;font-width: bold',
												id: cSysNick + 'Field_' + mName[2][2],
												width: 40
											})
										]
									},
									new Ext.form.Label({
												id: 'cName_' + mName[3][2],
												text: mName[3][0],
												height: 10,
												style: 'font-style:italic;font-size:1.2em;color:blue; '
											}
									),
									{
										border: false,
										layout: 'column',
										bodyStyle: 'padding: 5px;background-color: #E2FFE9',
										items: [
											oCombo[3],
											new Ext.form.TextField({
												allowBlank: true,
												disabled: true,
												style: 'margin: 0px 0px 0px 20px;font-style:normal;font-size:1.2em;color:blue;text-align: center;font-width: bold',
												id: cSysNick + 'Field_' + mName[3][2],
												width: 40
											})
										]
									},
									new Ext.form.Label({
												id: 'cName_' + mName[4][2],
												text: mName[4][0],
												height: 10,
												style: 'font-style:italic;font-size:1.2em;color:blue; '
											}
									),
									{
										border: false,
										layout: 'column',
										bodyStyle: 'padding: 5px;background-color: #E2FFE9',
										items: [
											oCombo[4],
											new Ext.form.TextField({
												allowBlank: true,
												disabled: true,
												style: 'margin: 0px 0px 0px 20px;font-style:normal;font-size:1.2em;color:blue;text-align: center;font-width: bold',
												id: cSysNick + 'Field_' + mName[4][2],
												width: 40
											})
										]
									}
								]
							});
							Ext.getCmp('PanelVisualSkills' + cSysNick).add(VisualSkillsQuestion);

							var NamingQuestion = new Ext.Panel({
								baseCls: 'x-plain',
								id: 'NamingQuestion_' + cSysNick,
								border: false,
								width: 750,
								disabled: true,
								layout: 'form',
								bodyStyle: 'padding: 10px;background-color: #E2FFE9', //+bgcolor
								tabIndex: -1,
								items: [
									new Ext.form.Label({
												id: 'cName_' + mName[5][2],
												text: mName[5][0],
												height: 10,
												style: 'font-style:italic;font-size:1.2em;color:blue; '
											}
									),
									{
										border: false,
										layout: 'column',
										bodyStyle: 'padding: 5px;background-color: #E2FFE9',
										items: [
											oCombo[5],
											new Ext.form.TextField({
												allowBlank: true,
												disabled: true,
												style: 'margin: 0px 0px 0px 20px;font-style:normal;font-size:1.2em;color:blue;text-align: center;font-width: bold',
												id: cSysNick + 'Field_' + mName[5][2],
												width: 40
											})
										]
									}
								]
							});
							Ext.getCmp('PanelNaming' + cSysNick).add(NamingQuestion);

							var AttentionQuestion = new Ext.Panel({
								baseCls: 'x-plain',
								id: 'AttentionQuestion_' + cSysNick,
								border: false,
								width: 750,
								disabled: true,
								layout: 'form',
								bodyStyle: 'padding: 10px;background-color: #E2FFE9', //+bgcolor
								tabIndex: -1,
								items: [
									new Ext.form.Label({
												id: 'cName_' + mName[6][2],
												text: mName[6][0],
												height: 10,
												style: 'font-style:italic;font-size:1.2em;color:blue; '
											}
									),
									{
										border: false,
										layout: 'column',
										bodyStyle: 'padding: 5px;background-color: #E2FFE9',
										items: [
											oCombo[6],
											new Ext.form.TextField({
												allowBlank: true,
												disabled: true,
												style: 'margin: 0px 0px 0px 20px;font-style:normal;font-size:1.2em;color:blue;text-align: center;font-width: bold',
												id: cSysNick + 'Field_' + mName[6][2],
												width: 40
											})
										]
									},
									new Ext.form.Label({
												id: 'cName_' + mName[7][2],
												text: mName[7][0],
												height: 10,
												style: 'font-style:italic;font-size:1.2em;color:blue; '
											}
									),
									{
										border: false,
										layout: 'column',
										bodyStyle: 'padding: 5px;background-color: #E2FFE9',
										items: [
											oCombo[7],
											new Ext.form.TextField({
												allowBlank: true,
												disabled: true,
												style: 'margin: 0px 0px 0px 20px;font-style:normal;font-size:1.2em;color:blue;text-align: center;font-width: bold',
												id: cSysNick + 'Field_' + mName[7][2],
												width: 40
											})
										]
									},
									new Ext.form.Label({
												id: 'cName_' + mName[8][2],
												text: mName[8][0],
												height: 10,
												style: 'font-style:italic;font-size:1.2em;color:blue; '
											}
									),
									{
										border: false,
										layout: 'column',
										bodyStyle: 'padding: 5px;background-color: #E2FFE9',
										items: [
											oCombo[8],
											new Ext.form.TextField({
												allowBlank: true,
												disabled: true,
												style: 'margin: 0px 0px 0px 20px;font-style:normal;font-size:1.2em;color:blue;text-align: center;font-width: bold',
												id: cSysNick + 'Field_' + mName[8][2],
												width: 40
											})
										]
									},
									new Ext.form.Label({
												id: 'cName_' + mName[9][2],
												text: mName[9][0],
												height: 10,
												style: 'font-style:italic;font-size:1.2em;color:blue; '
											}
									),
									{
										border: false,
										layout: 'column',
										bodyStyle: 'padding: 5px;background-color: #E2FFE9',
										items: [
											oCombo[9],
											new Ext.form.TextField({
												allowBlank: true,
												disabled: true,
												style: 'margin: 0px 0px 0px 20px;font-style:normal;font-size:1.2em;color:blue;text-align: center;font-width: bold',
												id: cSysNick + 'Field_' + mName[9][2],
												width: 40
											})
										]
									}
								]
							});
							Ext.getCmp('PanelAttention' + cSysNick).add(AttentionQuestion);

							var SpeechQuestion = new Ext.Panel({
								baseCls: 'x-plain',
								id: 'SpeechQuestion_' + cSysNick,
								border: false,
								width: 750,
								disabled: true,
								layout: 'form',
								bodyStyle: 'padding: 10px;background-color: #E2FFE9', //+bgcolor
								tabIndex: -1,
								items: [
									new Ext.form.Label({
												id: 'cName_' + mName[10][2],
												text: mName[10][0],
												height: 10,
												style: 'font-style:italic;font-size:1.2em;color:blue; '
											}
									),
									{
										border: false,
										layout: 'column',
										bodyStyle: 'padding: 5px;background-color: #E2FFE9',
										items: [
											oCombo[10],
											new Ext.form.TextField({
												allowBlank: true,
												disabled: true,
												style: 'margin: 0px 0px 0px 20px;font-style:normal;font-size:1.2em;color:blue;text-align: center;font-width: bold',
												id: cSysNick + 'Field_' + mName[10][2],
												width: 40
											})
										]
									},
									new Ext.form.Label({
												id: 'cName_' + mName[11][2],
												text: mName[11][0],
												height: 10,
												style: 'font-style:italic;font-size:1.2em;color:blue; '
											}
									),
									{
										border: false,
										layout: 'column',
										bodyStyle: 'padding: 5px;background-color: #E2FFE9',
										items: [
											oCombo[11],
											new Ext.form.TextField({
												allowBlank: true,
												disabled: true,
												style: 'margin: 0px 0px 0px 20px;font-style:normal;font-size:1.2em;color:blue;text-align: center;font-width: bold',
												id: cSysNick + 'Field_' + mName[11][2],
												width: 40
											})
										]
									}
								]
							});
							Ext.getCmp('PanelSpeech' + cSysNick).add(SpeechQuestion);

							var AbstractionQuestion = new Ext.Panel({
								baseCls: 'x-plain',
								id: 'AbstractionQuestion_' + cSysNick,
								border: false,
								width: 750,
								disabled: true,
								layout: 'form',
								bodyStyle: 'padding: 10px;background-color: #E2FFE9', //+bgcolor
								tabIndex: -1,
								items: [
									new Ext.form.Label({
												id: 'cName_' + mName[12][2],
												text: mName[12][0],
												height: 10,
												style: 'font-style:italic;font-size:1.2em;color:blue; '
											}
									),
									{
										border: false,
										layout: 'column',
										bodyStyle: 'padding: 5px;background-color: #E2FFE9',
										items: [
											oCombo[12],
											new Ext.form.TextField({
												allowBlank: true,
												disabled: true,
												style: 'margin: 0px 0px 0px 20px;font-style:normal;font-size:1.2em;color:blue;text-align: center;font-width: bold',
												id: cSysNick + 'Field_' + mName[12][2],
												width: 40
											})
										]
									}
								]
							});
							Ext.getCmp('PanelAbstraction' + cSysNick).add(AbstractionQuestion);

							var PlaybackQuestion = new Ext.Panel({
								baseCls: 'x-plain',
								id: 'PlaybackQuestion_' + cSysNick,
								border: false,
								width: 750,
								disabled: true,
								layout: 'form',
								bodyStyle: 'padding: 10px;background-color: #E2FFE9', //+bgcolor
								tabIndex: -1,
								items: [
									new Ext.form.Label({
												id: 'cName_' + mName[13][2],
												text: mName[13][0],
												height: 10,
												style: 'font-style:italic;font-size:1.2em;color:blue; '
											}
									),
									{
										border: false,
										layout: 'column',
										bodyStyle: 'padding: 5px;background-color: #E2FFE9',
										items: [
											oCombo[13],
											new Ext.form.TextField({
												allowBlank: true,
												disabled: true,
												style: 'margin: 0px 0px 0px 20px;font-style:normal;font-size:1.2em;color:blue;text-align: center;font-width: bold',
												id: cSysNick + 'Field_' + mName[13][2],
												width: 40
											})
										]
									}
								]
							});
							Ext.getCmp('PanelPlayback' + cSysNick).add(PlaybackQuestion);

							var OrientationQuestion = new Ext.Panel({
								baseCls: 'x-plain',
								id: 'OrientationQuestion_' + cSysNick,
								border: false,
								width: 750,
								disabled: true,
								layout: 'form',
								bodyStyle: 'padding: 10px;background-color: #E2FFE9', //+bgcolor
								tabIndex: -1,
								items: [
									new Ext.form.Label({
												id: 'cName_' + mName[14][2],
												text: mName[14][0],
												height: 10,
												style: 'font-style:italic;font-size:1.2em;color:blue; '
											}
									),
									{
										border: false,
										layout: 'column',
										bodyStyle: 'padding: 5px;background-color: #E2FFE9',
										items: [
											oCombo[14],
											new Ext.form.TextField({
												allowBlank: true,
												disabled: true,
												style: 'margin: 0px 0px 0px 20px;font-style:normal;font-size:1.2em;color:blue;text-align: center;font-width: bold',
												id: cSysNick + 'Field_' + mName[14][2],
												width: 40
											})
										]
									}
								]
							});
							Ext.getCmp('PanelOrientation' + cSysNick).add(OrientationQuestion);

							Ext.getCmp('FirstPanel' + cSysNick).add(panel_visual_skills);
							Ext.getCmp('FirstPanel' + cSysNick).add(panel_naming);
							Ext.getCmp('FirstPanel' + cSysNick).add(panel_attention);
							Ext.getCmp('FirstPanel' + cSysNick).add(panel_speech);
							Ext.getCmp('FirstPanel' + cSysNick).add(panel_abstraction);
							Ext.getCmp('FirstPanel' + cSysNick).add(panel_playback);
							Ext.getCmp('FirstPanel' + cSysNick).add(panel_orientation);
							Ext.getCmp('ViewScale_id').doLayout();
						}

						//Шкала ВАШ
						if (cSysNick == 'VAScale')
						{
							//console.log('ObjScale=',ObjScale);
							Ext.getCmp('ReabScaleValue').hide();
							var first_panel = form.createFirstPanel('FirstPanel' + cSysNick, cTitleScale);
							Ext.getCmp('ViewScale_id').add(first_panel);

							var GridResult = new Ext.Panel({
								height: 250,
								width: 600,
								layout: 'border',
								id: 'GridResult',
								border: true,
								style: 'border-top: 1px solid #99bbe8; border-left: 1px solid #99bbe8; border-bottom: 1px solid #ffffff;border-right: 1px solid #ffffff; padding: 0px; ',
								items: [
									new sw.Promed.ViewFrame(
											{
												actions: [
													{name: 'action_add', handler: function () {
														Ext.getCmp('ReabGrid_VAScale').AddRecord();
													}.createDelegate(this)},
													{name: 'action_view', hidden: true},
													{name: 'action_edit', hidden: true},
													{name: 'action_delete', disabled: true, handler: function () {
														Ext.getCmp('ReabGrid_VAScale').DelRecord();
													}.createDelegate(this)},
													{name: 'action_refresh', hidden: true},
													{name: 'action_print', hidden: true}
												],
												autoExpandColumn: 'autoexpand',
												autoExpandMin: 100,
												autoLoadData: false,
												id: 'ReabGrid_VAScale',
												pageSize: 50,
												height: 110,
												width: 600,
												paging: false, // навигатор
												region: 'center',
												root: 'data',
												stringfields: [
													{name: 'Id', type: 'int', header: 'ID', key: true},
													{name: 'Localization_of_pain_id', type: 'string', width: 100, hidden: true}, //1
													{name: 'Localization_of_pain', type: 'string',
														header: '<div style="width:200px;text-align:center;align:center; font-family:serif;font-weight: bold;font-size:medium;">' + 'Локализация боли' + '</div>',
														width: 200},
													{name: 'VASValue_id', type: 'string', width: 100, hidden: true}, //2VASName
													//{name: 'VASValue', type: 'int', width: 50,hidden: true}, //2
													{name: 'VASName', type: 'string',
														header: '<div style="width:250px;text-align:center; align:center; font-family:serif;font-weight: bold;font-size:medium;">' + 'Оценка в баллах' + '</div>',
														align: 'center', id: 'autoexpand', width: 150}
												],
												totalProperty: 'totalCount',
												focusOnFirstLoad: false,
												toolbar: true,
												onBeforeLoadData: function () {
													//this.getButtonSearch().disable();
												}.createDelegate(this),
												onLoadData: function () {
													// alert('Хрень');
													//this.getButtonSearch().enable();
												}.createDelegate(this),
												onRowSelect: function (sm, index, record) {
													Ext.getCmp('VASc_Panel').hide();
													Ext.getCmp('ReabGrid_VAScale').ViewActions.action_delete.setDisabled(false);
													//Ext.getCmp('ReabGrid_'+ params.data.id).ViewActions.action_add.setDisabled(true);
												},
												//Удаление диагноза
												DelRecord: function ()
												{
													// alert('Удаляем');
													var record = Ext.getCmp('ReabGrid_VAScale').getGrid().getSelectionModel().getSelected();
													Ext.getCmp('ReabGrid_VAScale').getGrid().store.remove(record);
													var nRecord = Ext.getCmp('ReabGrid_VAScale').getGrid().getStore().data.items.length;
													if (nRecord == 0)
													{
														//console.log('nRecord=',nRecord);
														Ext.getCmp('ReabGrid_VAScale').getGrid().tbar.dom.firstChild.firstChild.firstChild.firstChild.lastChild.firstChild.innerText = '0 / 0';
														Ext.getCmp('ReabGrid_VAScale').ViewActions.action_delete.setDisabled(true);
													}
													Ext.getCmp('ReabGrid_VAScale').getGrid().getSelectionModel().selectRow(0);
													Ext.getCmp('ReabGrid_VAScale').getGrid().getSelectionModel().deselectRow(0);
													Ext.getCmp('ReabGrid_VAScale').ViewActions.action_delete.setDisabled(true);
												},
												//Добавление измерения
												AddRecord: function ()
												{
													Ext.getCmp('VASLocalization_id').setValue('Введите параметр');
													Ext.getCmp('VASLocalization_id').selectedIndex = -1;
													Ext.getCmp('VASValue_id').setValue('Введите параметр');
													Ext.getCmp('VASValue_id').selectedIndex = -1;

													Ext.getCmp('VASc_Panel').show();
													Ext.getCmp('VAS_ButtonSave').focus();
													Ext.getCmp('ReabGrid_VAScale').ViewActions.action_add.setDisabled(true);
													Ext.getCmp('ReabGrid_VAScale').ViewActions.action_add.setDisabled(true);
													Ext.getCmp('saveReabScaleDataButton').setDisabled(true);
												}
											})

								]
							});

							Ext.getCmp('FirstPanel' + cSysNick).add(GridResult);
							var aSprScale = new Array();
							var aSprParam = new Array();
							for (var ii = 0; ii < ObjScale.SprScale.length; ii++)
							{

								aSprScale.push([ObjScale.SprScale[ii].ScaleParameterType_id,
									ObjScale.SprScale[ii].ScaleParameterResult_id,
									ObjScale.SprScale[ii].ScaleParameterResult_Name,
									ObjScale.SprScale[ii].ScaleParameterResult_Value]);
							}
							;

							//console.log('SprParam=',ObjScale.SprParam);
							for (var ii = 0; ii < ObjScale.SprParam.length; ii++)
							{

								aSprParam.push([ObjScale.SprParam[ii].paramId,
									ObjScale.SprParam[ii].paramName,
									ObjScale.SprParam[ii].paramWeight]);
							}
							;

							//console.log('aSprParam=',aSprParam);

							var Field = new Ext.Panel({
								id: 'VASc_Panel',
								layout: 'form',
								border: true,
								//hidden : true,
								width: 600,
								heigth: 150,
								bodyStyle: 'padding: 10px;background-color: #E2FFE9', //+bgcolor
								// style: 'background-color:#E3E3E3!important'
								style: 'border-top: 1px solid #99bbe8; border-left: 1px solid #99bbe8; border-bottom: 1px solid #ffffff;border-right: 1px solid #ffffff; padding: 2px 0px 0px 0px; margin-top: 5px;background-color: transparent ;',
								items: [
									//Панель для заполнения RGRID
									{
										layout: 'form',
										//width: 1200,
										//labelWidth: 90,
										border: true,
										bodyStyle: 'background-color: transparent',
										style: 'background-color: transparent',
										items: [
											{layout: 'column',
												// width: 1200,
												items: [
													//combo Локализация боли, Оценка по шкале
													{
														layout: 'form',
														border: false,
														// width: 320,
														labelWidth: 110,
														labelAlign: 'left',
														items: [
															//Локализация боли
															{
																allowBlank: false,
																style: 'text-align : center; font-size:1.1em; ',
																id: 'VASLocalization_id',
																xtype: 'combo',
																//labelAlign: 'rigth',
																//labelWidth: 100,
																width: 165,
																fieldLabel: 'Локализация боли',
																hideTrigger: false, // Chek
																mode: 'local',
																editable: false,
																triggerAction: 'all',
																displayField: 'paramName',
																valueField: 'paramId',
																tabIndex: -1,
																emptyText: 'Введите параметр',
																listWidth: 'auto',
																listWidth: 'auto',
																hiddenName: 'paramId',
																autoscroll: false,
																xtype: 'combo',
																store: new Ext.data.SimpleStore({
																	fields: [
																		{name: 'paramId', type: 'int'},
																		{name: 'paramName', type: 'string'},
																		{name: 'paramWeight', type: 'string'}
																	],
																	data: aSprParam
																}),
																tpl: '<tpl for="."><div class="x-combo-list-item">' +
																'{paramName} ' + '&nbsp;' +
																'</div></tpl>'
															}
														]
													},
													{
														layout: 'form',
														border: false,
														labelWidth: 100,
														labelAlign: 'rigth',
														style: 'position:relative;  left:15px ',
														items: [
															//Оценка по шкале
															{
																allowBlank: false,
																style: 'text-align : center; font-size:1.1em; ',
																id: 'VASValue_id',
																xtype: 'combo',
																fieldLabel: 'Оценка по шкале',
																hideTrigger: false, // Chek
																mode: 'local',
																editable: false,
																triggerAction: 'all',
																displayField: 'ScaleParameterResult_Name',
																valueField: 'ScaleParameterResult_id',
																width: 150,
																emptyText: 'Введите параметр',
																//listWidth:'auto',
																// hiddenName: 'ReabSpr_Elem_id',
																autoscroll: false,
																xtype: 'combo',
																store: new Ext.data.SimpleStore({
																	fields: [
																		{name: 'ScaleParameterType_id', type: 'int'},
																		{name: 'ScaleParameterResult_id', type: 'int'},
																		{name: 'ScaleParameterResult_Name', type: 'string'},
																		{name: 'ScaleParameterResult_Value', type: 'string'}
																	],
																	data: aSprScale
																}),
																tpl: '<tpl for="."><div class="x-combo-list-item">' +
																'{ScaleParameterResult_Name} ' + '&nbsp;' +
																'</div></tpl>'
															}
														]
													}
												]
											},
											{
												layout: 'form',
												border: false,
												width: 1200,
												height: 20
											},
											//Кнопки
											{
												layout: 'column',
												// width: 1200,
												items: [
													// кнопка сохранить
													{
														layout: 'form',
														width: 100,
														items: [
															new Ext.Button({
																id: 'VAS_ButtonSave',
																iconCls: 'save16',
																text: 'Сохранить',
																handler: function (b, e)
																{
																	Ext.getCmp('VASc_Panel').SaveParam();
																}.createDelegate(this)
															})
														]
													},
													// кнопка закрыть
													{
														layout: 'form',
														width: 100,
														style: 'position:relative;  left:250px ',
														items: [
															new Ext.Button({
																//id: params.data.Grid  + '_'+ params.data.id +'ButtonSave',
																iconCls: 'cancel16',
																text: 'Закрыть',
																handler: function (b, e)
																{
																	Ext.getCmp('VASc_Panel').ExitForm();
																}.createDelegate(this)
															})
														]
													}
												]
											}
										]
									}
								],
								SaveParam: function () {

									var nRecord = Ext.getCmp('ReabGrid_VAScale').getGrid().getStore().data.items.length;
									if (this.RecordValidat(nRecord) == false)
									{
										return
									}

									//Заполнение GRIDa
									Ext.getCmp('ReabGrid_VAScale').getGrid().store.insert(nRecord, [new Ext.data.Record({
										Id: nRecord + 1,
										Localization_of_pain_id: Ext.getCmp('VASLocalization_id').getStore().data.items[Ext.getCmp('VASLocalization_id').selectedIndex].data.paramId,
										Localization_of_pain: Ext.getCmp('VASLocalization_id').getStore().data.items[Ext.getCmp('VASLocalization_id').selectedIndex].data.paramName,
										VASValue_id: Ext.getCmp('VASValue_id').getStore().data.items[Ext.getCmp('VASValue_id').selectedIndex].data.ScaleParameterResult_id,
										VASName: Ext.getCmp('VASValue_id').getStore().data.items[Ext.getCmp('VASValue_id').selectedIndex].data.ScaleParameterResult_Name
									})]);
									Ext.getCmp('ReabGrid_VAScale').getGrid().getSelectionModel().selectRow(0);
									Ext.getCmp('ReabGrid_VAScale').getGrid().getSelectionModel().deselectRow(0);
									Ext.getCmp('ReabGrid_VAScale').ViewActions.action_delete.setDisabled(true);
									Ext.getCmp('VASc_Panel').hide();
									Ext.getCmp('ReabGrid_VAScale').ViewActions.action_add.setDisabled(false);
									Ext.getCmp('saveReabScaleDataButton').setDisabled(false);
									return;
								},
								RecordValidat: function (nRecord) {
									var cMessage = 'Отсутствует ответ на вопрос :';
									if (Ext.getCmp('VASLocalization_id').selectedIndex == -1 || Ext.getCmp('VASLocalization_id').getValue() == "")
									{
										cMessage = cMessage + '<br>' + '1.Локализация боли;';
									}
									if (Ext.getCmp('VASValue_id').selectedIndex == -1 || Ext.getCmp('VASValue_id').getValue() == "")
									{
										cMessage = cMessage + '<br>' + '2.Оценка в баллах;';
									}
									if (cMessage != 'Отсутствует ответ на вопрос :')
									{
										form.showMsg(cMessage);
										return false;
									} else {
										//Валидация на дублирование параметра
										if (nRecord > 0)
										{
											for (var i = 0; i < nRecord; i++)
											{
												if (Ext.getCmp('ReabGrid_VAScale').getGrid().getStore().data.items[i].data.Localization_of_pain_id == Ext.getCmp('VASLocalization_id').getValue())
												{
													form.showMsg("Параметр " + Ext.getCmp('VASLocalization_id').store.data.items[Ext.getCmp('VASLocalization_id').getValue() - 1].data.paramName + " уже прошел оценку");
													return false;
												}
												// else{return true;}
											}
											return true;
										} else
										{
											return true;
										}
									}
								},
								ExitForm: function ()
								{
									Ext.getCmp('ReabGrid_VAScale').ViewActions.action_delete.setDisabled(true);
									Ext.getCmp('VASc_Panel').hide();
									Ext.getCmp('ReabGrid_VAScale').ViewActions.action_add.setDisabled(false);
									Ext.getCmp('saveReabScaleDataButton').setDisabled(false);
									return;
								}
							});

							Ext.getCmp('FirstPanel' + cSysNick).add(Field);

							Ext.getCmp('ViewScale_id').doLayout();
							Ext.getCmp('VASc_Panel').hide();
							Ext.getCmp('GridResult').setDisabled(true);
						}
						//Шкала Комитета медицинских исследований
						if (cSysNick == 'MedResCouncil')
						{
							Ext.getCmp('ReabScaleValue').hide();
							var first_panel = form.createFirstPanel('FirstPanel' + cSysNick, ObjScale.SprScale[0].ScaleParameterType_Name);
							Ext.getCmp('ViewScale_id').add(first_panel);

							var grid_result = new Ext.Panel({
								height: 250,
								//autoHeight : true,
								width: 700,
								layout: 'border',
								id: 'GridMRCScResult',
								border: true,
								style: 'border-top: 1px solid #99bbe8; border-left: 1px solid #99bbe8; border-bottom: 1px solid #ffffff;border-right: 1px solid #ffffff; padding: 0px; ',
								items: [
									new sw.Promed.ViewFrame(
											{
												actions: [
													{name: 'action_add', handler: function () {
														Ext.getCmp('ReabGrid_MRCScale').AddRecord();
													}.createDelegate(this)},
													{name: 'action_view', hidden: true},
													{name: 'action_edit', hidden: true},
													{name: 'action_delete', disabled: true, handler: function () {
														Ext.getCmp('ReabGrid_MRCScale').DelRecord();
													}.createDelegate(this)},
													{name: 'action_refresh', hidden: true},
													{name: 'action_print', hidden: true}
												],
												autoExpandColumn: 'autoexpand',
												autoExpandMin: 100,
												autoLoadData: false,
												id: 'ReabGrid_MRCScale',
												pageSize: 50,
												height: 110,
												width: 700,
												paging: false, // навигатор
												region: 'center',
												root: 'data',
												stringfields: [
													{name: 'Id', type: 'int', header: 'ID', key: true},
													{name: 'limb_id', type: 'string', width: 10, hidden: true}, //1
													{name: 'limb', type: 'string',
														header: '<div style="width:80px;text-align:center;align:center; font-family:serif;font-weight: bold;font-size:1.1em;">' + 'Конечность' + '</div>',
														width: 80},
													{name: 'Position_id', type: 'string', width: 10, hidden: true}, //1
													{name: 'Position', type: 'string',
														header: '<div style="width:90px;text-align:center;align:center; font-family:serif;font-weight: bold;font-size:1.1em;">' + 'Положение' + '</div>',
														width: 100},
													{name: 'Lateralization_id', type: 'string', width: 10, hidden: true}, //1
													{name: 'Lateralization', type: 'string',
														header: '<div style="width:100px;text-align:center;align:center; font-family:serif;font-weight: bold;font-size:1.1em;">' + 'Латерализация' + '</div>',
														width: 100},
													{name: 'Muscle_Strength', type: 'string',
														header: '<div style="width:250px;text-align:center;align:center; font-family:serif;font-weight: bold;font-size:1.1em;">' + 'Мышечная масса' + '</div>',
														width: 300},
													{name: 'MRCValue_id', type: 'string', width: 10, hidden: true}, //2VASName
													{name: 'MRCName', type: 'string',
														header: '<div style="width:60px;text-align:center; align:center; font-family:serif;font-weight: bold;font-size:1.1em;">' + 'Баллы' + '</div>',
														align: 'center', width: 60}
												],
												totalProperty: 'totalCount',
												focusOnFirstLoad: false,
												toolbar: true,
												onBeforeLoadData: function () {
													//this.getButtonSearch().disable();
												}.createDelegate(this),
												onLoadData: function () {
													// alert('Хрень');
													//this.getButtonSearch().enable();
												}.createDelegate(this),
												onRowSelect: function (sm, index, record) {
													Ext.getCmp('MRCSc_Panel').hide();
													Ext.getCmp('ReabGrid_MRCScale').ViewActions.action_delete.setDisabled(false);
												},
												//Удаление диагноза
												DelRecord: function ()
												{
													// alert('Удаляем');
													var record = Ext.getCmp('ReabGrid_MRCScale').getGrid().getSelectionModel().getSelected();
													Ext.getCmp('ReabGrid_MRCScale').getGrid().store.remove(record);
													var nRecord = Ext.getCmp('ReabGrid_MRCScale').getGrid().getStore().data.items.length;
													if (nRecord == 0)
													{
														//console.log('nRecord=',nRecord);
														Ext.getCmp('ReabGrid_MRCScale').getGrid().tbar.dom.firstChild.firstChild.firstChild.firstChild.lastChild.firstChild.innerText = '0 / 0';
														Ext.getCmp('ReabGrid_MRCScale').ViewActions.action_delete.setDisabled(true);
													}
													Ext.getCmp('ReabGrid_MRCScale').getGrid().getSelectionModel().selectRow(0);
													Ext.getCmp('ReabGrid_MRCScale').getGrid().getSelectionModel().deselectRow(0);
													Ext.getCmp('ReabGrid_MRCScale').ViewActions.action_delete.setDisabled(true);
												},
												//Добавление диагноза
												AddRecord: function ()
												{
													//MRClimb_id MRCPosition_id MRCLateralization_id MRCValue_id
													Ext.getCmp('MRClimb_id').setValue('Введите параметр');
													Ext.getCmp('MRClimb_id').selectedIndex = -1;
													Ext.getCmp('MRCPosition_id').setValue('Введите параметр');
													Ext.getCmp('MRCPosition_id').selectedIndex = -1;
													Ext.getCmp('MRCLateralization_id').setValue('Введите параметр');
													Ext.getCmp('MRCLateralization_id').selectedIndex = -1;
													Ext.getCmp('MRCValue_id').setValue('Введите параметр');
													Ext.getCmp('MRCValue_id').selectedIndex = -1;

													Ext.getCmp('MRCSc_Panel').show();
													Ext.getCmp('MRCSc_ButtonSave').focus();
													Ext.getCmp('ReabGrid_MRCScale').ViewActions.action_add.setDisabled(true);
													Ext.getCmp('ReabGrid_MRCScale').ViewActions.action_add.setDisabled(true);
													Ext.getCmp('saveReabScaleDataButton').setDisabled(true);
												}
											})

								]
							});

							Ext.getCmp('FirstPanel' + cSysNick).add(grid_result);
							var aSprScale = new Array();
							var aSprParam1 = new Array();
							var aSprParam2 = new Array();
							var aSprParam3 = new Array();

							// console.log('ObjScale=',ObjScale);
							for (var ii = 0; ii < ObjScale.SprScale.length; ii++)
							{
								aSprScale.push([ObjScale.SprScale[ii].ScaleParameterType_id,
									ObjScale.SprScale[ii].ScaleParameterResult_id,
									ObjScale.SprScale[ii].ScaleParameterResult_Name,
									ObjScale.SprScale[ii].ScaleParameterResult_Value]);
							}
							;
							// console.log('aSprScale=',aSprScale);
							for (var ii = 0; ii < ObjScale.SprParam1.length; ii++)
							{

								aSprParam1.push([ObjScale.SprParam1[ii].paramId,
									ObjScale.SprParam1[ii].paramName,
									ObjScale.SprParam1[ii].paramWeight]);
							}
							;
							for (var ii = 0; ii < ObjScale.SprParam2.length; ii++)
							{

								aSprParam2.push([ObjScale.SprParam2[ii].paramId,
									ObjScale.SprParam2[ii].paramName,
									ObjScale.SprParam2[ii].paramWeight]);
							}
							;
							for (var ii = 0; ii < ObjScale.SprParam3.length; ii++)
							{

								aSprParam3.push([ObjScale.SprParam3[ii].paramId,
									ObjScale.SprParam3[ii].paramName,
									ObjScale.SprParam3[ii].paramWeight]);
							}
							;

							var field = new Ext.Panel({
								id: 'MRCSc_Panel',
								layout: 'form',
								border: true,
								//hidden : true,
								width: 650,
								heigth: 150,
								bodyStyle: 'padding: 10px;background-color: #E2FFE9', //+bgcolor
								// style: 'background-color:#E3E3E3!important'
								style: 'border-top: 1px solid #99bbe8; border-left: 1px solid #99bbe8; border-bottom: 1px solid #ffffff;border-right: 1px solid #ffffff; padding: 2px 0px 0px 0px; margin-top: 5px;background-color: transparent ;',
								items: [
									//Панель для заполнения RGRID
									{
										layout: 'form',
										border: true,
										bodyStyle: 'background-color: transparent',
										style: 'background-color: transparent',
										items: [
											{layout: 'column',
												// width: 1200,
												items: [
													//combo Конечность Положение
													{
														layout: 'form',
														border: false,
														// width: 320,
														labelWidth: 80,
														labelAlign: 'left',
														items: [
															//Конечность
															{
																allowBlank: false,
																style: 'text-align: center; font-size:1.1em; ',
																id: 'MRClimb_id',
																xtype: 'combo',
																width: 150,
																fieldLabel: 'Конечность',
																hideTrigger: false, // Chek
																mode: 'local',
																editable: false,
																triggerAction: 'all',
																displayField: 'SprName',
																valueField: 'ReabSpr_Elem_id',
																hiddenName: 'ReabSpr_Elem_id',
																tabIndex: -1,
																emptyText: 'Введите параметр',
																//listWidth:'auto',
																autoscroll: false,
																xtype: 'combo',
																store: new Ext.data.SimpleStore({
																	fields: [
																		{name: 'ReabSpr_Elem_id', type: 'int'},
																		{name: 'SprName', type: 'string'},
																		{name: 'ReabSpr_Elem_Weight', type: 'string'}
																	],
																	data: aSprParam1
																}),
																tpl: '<tpl for="."><div class="x-combo-list-item">' +
																'{SprName} ' + '&nbsp;' +
																'</div></tpl>'
															}
														]
													},
													{
														layout: 'form',
														border: false,
														labelWidth: 80,
														labelAlign: 'rigth',
														style: 'position:relative;  left:15px ',
														items: [
															//Положение
															{
																allowBlank: false,
																style: 'text-align: center; font-size:1.1em; ',
																id: 'MRCPosition_id',
																xtype: 'combo',
																fieldLabel: 'Положение',
																hideTrigger: false, // Chek
																mode: 'local',
																editable: false,
																triggerAction: 'all',
																displayField: 'SprName',
																valueField: 'ReabSpr_Elem_id',
																hiddenName: 'ReabSpr_Elem_id',
																width: 150,
																emptyText: 'Введите параметр',
																//listWidth:'auto',
																autoscroll: false,
																xtype: 'combo',
																store: new Ext.data.SimpleStore({
																	fields: [
																		{name: 'ReabSpr_Elem_id', type: 'int'},
																		{name: 'SprName', type: 'string'},
																		{name: 'ReabSpr_Elem_Weight', type: 'string'}
																	],
																	data: aSprParam2
																}),
																tpl: '<tpl for="."><div class="x-combo-list-item">' +
																'{SprName} ' + '&nbsp;' +
																'</div></tpl>'
															}
														]
													}
												]
											},
											{
												layout: 'form',
												border: false,
												height: 15
											},
											{layout: 'column',
												// width: 1200,
												items: [
													//combo Латерация
													{
														layout: 'form',
														border: false,
														labelWidth: 80,
														labelAlign: 'left',
														items: [
															//Латерация
															{
																allowBlank: false,
																style: 'text-align: center; font-size:1.1em; ',
																id: 'MRCLateralization_id',
																xtype: 'combo',
																width: 150,
																fieldLabel: 'Латерация',
																hideTrigger: false, // Chek
																mode: 'local',
																editable: false,
																triggerAction: 'all',
																displayField: 'SprName',
																valueField: 'ReabSpr_Elem_id',
																tabIndex: -1,
																emptyText: 'Введите параметр',
																//  listWidth:'auto',
																hiddenName: 'ReabSpr_Elem_id',
																autoscroll: false,
																xtype: 'combo',
																store: new Ext.data.SimpleStore({
																	fields: [
																		{name: 'ReabSpr_Elem_id', type: 'int'},
																		{name: 'SprName', type: 'string'},
																		{name: 'ReabSpr_Elem_Weight', type: 'string'}
																	],
																	data: aSprParam3
																}),
																tpl: '<tpl for="."><div class="x-combo-list-item">' +
																'{SprName} ' + '&nbsp;' +
																'</div></tpl>'
															}
														]
													},

													{
														layout: 'form',
														border: false,
														labelWidth: 80,
														labelAlign: 'rigth',
														style: 'position:relative;  left:15px ',
														items: [
															//Баллы
															{
																allowBlank: false,
																style: 'text-align:center;font-size:1.1em;',
																id: 'MRCValue_id',
																xtype: 'combo',
																fieldLabel: 'Баллы',
																hideTrigger: false, // Chek
																mode: 'local',
																editable: false,
																triggerAction: 'all',
																displayField: 'ScaleParameterResult_Value',
																valueField: 'ScaleParameterResult_id',
																width: 150,
																emptyText: 'Введите параметр',
																//listWidth:'auto',
																// hiddenName: 'ReabSpr_Elem_id',
																autoscroll: false,
																xtype: 'combo',
																store: new Ext.data.SimpleStore({
																	fields: [
																		{name: 'ScaleParameterType_id', type: 'int'},
																		{name: 'ScaleParameterResult_id', type: 'int'},
																		{name: 'ScaleParameterResult_Name', type: 'string'},
																		{name: 'ScaleParameterResult_Value', type: 'string'}
																	],
																	data: aSprScale
																}),
																tpl: '<tpl for="."><div class="x-combo-list-item">' +
																'&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp;' + '{ScaleParameterResult_Value} ' + '&nbsp;' +
																'</div></tpl>'
															}
														]
													}
												]
											},
											{
												layout: 'form',
												border: false,
												//width: 1200,
												height: 20
											},
											//Кнопки
											{
												layout: 'column',
												// width: 1200,
												items: [
													// кнопка сохранить
													{
														layout: 'form',
														width: 100,
														items: [
															new Ext.Button({
																id: 'MRCSc_ButtonSave',
																iconCls: 'save16',
																text: 'Сохранить',
																handler: function (b, e)
																{
																	Ext.getCmp('MRCSc_Panel').SaveParam();
																}.createDelegate(this)
															})
														]
													},
													// кнопка закрыть
													{
														layout: 'form',
														width: 100,
														style: 'position:relative;  left:250px ',
														items: [
															new Ext.Button({
																//id: params.data.Grid  + '_'+ params.data.id +'ButtonSave',
																iconCls: 'cancel16',
																text: 'Закрыть',
																handler: function (b, e)
																{
																	Ext.getCmp('MRCSc_Panel').ExitForm();
																}.createDelegate(this)
															})
														]
													}
												]
											}
										]
									}
								],
								SaveParam: function () {

									var nRecord = Ext.getCmp('ReabGrid_MRCScale').getGrid().getStore().data.items.length;
									if (this.RecordValidat(nRecord) == false)
									{
										return
									}

									//Заполнение GRIDa
									Ext.getCmp('ReabGrid_MRCScale').getGrid().store.insert(nRecord, [new Ext.data.Record({
										Id: nRecord + 1,
										limb_id: Ext.getCmp('MRClimb_id').getStore().data.items[Ext.getCmp('MRClimb_id').selectedIndex].data.ReabSpr_Elem_id,
										limb: Ext.getCmp('MRClimb_id').getStore().data.items[Ext.getCmp('MRClimb_id').selectedIndex].data.SprName,
										Position_id: Ext.getCmp('MRCPosition_id').getStore().data.items[Ext.getCmp('MRCPosition_id').selectedIndex].data.ReabSpr_Elem_id,
										Position: Ext.getCmp('MRCPosition_id').getStore().data.items[Ext.getCmp('MRCPosition_id').selectedIndex].data.SprName,
										Lateralization_id: Ext.getCmp('MRCLateralization_id').getStore().data.items[Ext.getCmp('MRCLateralization_id').selectedIndex].data.ReabSpr_Elem_id,
										Lateralization: Ext.getCmp('MRCLateralization_id').getStore().data.items[Ext.getCmp('MRCLateralization_id').selectedIndex].data.SprName,
										Muscle_Strength: Ext.getCmp('MRCValue_id').getStore().data.items[Ext.getCmp('MRCValue_id').selectedIndex].data.ScaleParameterResult_Name,
										//Muscle_Strength: '12345', Ext.getCmp('MRCValue_id').getStore().data.items[1].data.ScaleParameterResult_Name
										MRCValue_id: Ext.getCmp('MRCValue_id').getStore().data.items[Ext.getCmp('MRCValue_id').selectedIndex].data.ScaleParameterResult_id,
										MRCName: Ext.getCmp('MRCValue_id').getStore().data.items[Ext.getCmp('MRCValue_id').selectedIndex].data.ScaleParameterResult_Value
									})]);
									//console.log('nRecord3=',nRecord);
									Ext.getCmp('ReabGrid_MRCScale').getGrid().getSelectionModel().selectRow(0);
									Ext.getCmp('ReabGrid_MRCScale').getGrid().getSelectionModel().deselectRow(0);
									Ext.getCmp('ReabGrid_MRCScale').ViewActions.action_delete.setDisabled(true);
									Ext.getCmp('MRCSc_Panel').hide();
									Ext.getCmp('ReabGrid_MRCScale').ViewActions.action_add.setDisabled(false);
									Ext.getCmp('saveReabScaleDataButton').setDisabled(false);
									return;
								},
								RecordValidat: function (nRecord) {
									//  console.log('nRecord1=',nRecord);
									var cMessage = 'Отсутствует ответ на вопрос :';
									if (Ext.getCmp('MRClimb_id').selectedIndex == -1 || Ext.getCmp('MRClimb_id').getValue() == "")
									{
										cMessage = cMessage + '<br>' + '1.Конечность;';
									}
									if (Ext.getCmp('MRCPosition_id').selectedIndex == -1 || Ext.getCmp('MRCPosition_id').getValue() == "")
									{
										cMessage = cMessage + '<br>' + '2.Положение;';
									}
									if (Ext.getCmp('MRCLateralization_id').selectedIndex == -1 || Ext.getCmp('MRCLateralization_id').getValue() == "")
									{
										cMessage = cMessage + '<br>' + '3.Лотерация;';
									}
									if (Ext.getCmp('MRCValue_id').selectedIndex == -1 || Ext.getCmp('MRCValue_id').getValue() == "")
									{
										cMessage = cMessage + '<br>' + '4.Оценка в баллах;';
									}
									if (cMessage != 'Отсутствует ответ на вопрос :')
									{
										form.showMsg(cMessage);
										return false;
									} else {
										//Валидация на дублирование параметра
										// console.log('nRecord2=',nRecord);
										if (nRecord > 0)
										{
											console.log('GRID=', Ext.getCmp('ReabGrid_MRCScale').getGrid().getStore().data.items);
											for (var i = 0; i < nRecord; i++)
											{
												if (Ext.getCmp('ReabGrid_MRCScale').getGrid().getStore().data.items[i].data.limb_id == Ext.getCmp('MRClimb_id').getValue() &&
														Ext.getCmp('ReabGrid_MRCScale').getGrid().getStore().data.items[i].data.Position_id == Ext.getCmp('MRCPosition_id').getValue() &&
														Ext.getCmp('ReabGrid_MRCScale').getGrid().getStore().data.items[i].data.Lateralization_id == Ext.getCmp('MRCLateralization_id').getValue()
												)
												{
													form.showMsg("Указанный набор параметров уже прошел оценку!");
													return false;
												}
												// else{return true;}
											}
											return true;
										} else
										{
											return true;
										}
									}
								},
								ExitForm: function ()
								{

									Ext.getCmp('ReabGrid_MRCScale').ViewActions.action_delete.setDisabled(true);
									Ext.getCmp('MRCSc_Panel').hide();
									Ext.getCmp('ReabGrid_MRCScale').ViewActions.action_add.setDisabled(false);
									Ext.getCmp('saveReabScaleDataButton').setDisabled(false);
									return;
								}
							});

							Ext.getCmp('FirstPanel' + cSysNick).add(field);

							Ext.getCmp('ViewScale_id').doLayout();
							Ext.getCmp('MRCSc_Panel').hide();
							Ext.getCmp('GridMRCScResult').setDisabled(true);
						}

						//Тест двигательной активности руки
						if (cSysNick == 'ARAT')
						{
							Ext.getCmp('ReabScaleValue').hide();
							var first_panel = form.createFirstPanel('FirstPanel' + cSysNick, 'Тест двигательной активности руки');

							Ext.getCmp('ViewScale_id').add(first_panel);
							var grid_result = new Ext.Panel({
								height: 250,
								//autoHeight : true,
								width: 700,
								layout: 'border',
								id: 'GridARATResult',
								border: true,
								style: 'border-top: 1px solid #99bbe8; border-left: 1px solid #99bbe8; border-bottom: 1px solid #ffffff;border-right: 1px solid #ffffff; padding: 0px; ',
								items: [
									new sw.Promed.ViewFrame(
											{
												actions: [
//                                 {name: 'action_add', handler: function() { Ext.getCmp('ReabGrid_ARATScale').AddRecord(); }.createDelegate(this)},
//                                 {name:'action_view', disabled: false, handler: function() { Ext.getCmp('ReabGrid_ARATScale').ViewRecord(); }.createDelegate(this)},
													{name: 'action_add', hidden: true},
													{name: 'action_view', hidden: true},
													{name: 'action_edit', hidden: true},
//				{name: 'action_delete',disabled: false, handler: function() { Ext.getCmp('ReabGrid_ARATScale').DelRecord(); }.createDelegate(this)},
													{name: 'action_delete', hidden: true},
													{name: 'action_refresh', hidden: true},
													{name: 'action_print', hidden: true}
												],
												autoExpandColumn: 'autoexpand',
												autoExpandMin: 100,
												autoLoadData: false,
												id: 'ReabGrid_ARATScale',
												pageSize: 50,
												height: 110,
												width: 750,
												paging: false, // навигатор
												region: 'center',
												root: 'data',
												//grouping: true,
												stringfields: [
													{name: 'Id', type: 'int', header: 'ID', key: true},
													{name: 'ScaleParameterType_id', type: 'string', width: 10, hidden: true}, //1
													{name: 'ScaleParameterType_Name', type: 'string',
														header: '<div style="width:150px;text-align:center;align:center; font-family:serif;font-weight: bold;font-size:1.1em;">' + 'Предмет/Действие' + '</div>',
														width: 150, sortable: false}, // {name: 'Section', type: 'string', width: 100, header: 'Rаздел',group: true, hidden: true,direction: 'ASC'}, //1
													{name: 'Section', type: 'string', width: 100, header: 'Rаздел', group: true, hidden: true}, //1
													{name: 'Hand_right', type: 'string',
														header: '<div style="width:210px;text-align:center;align:center; font-family:serif;font-weight: bold;font-size:1.1em;">' + 'Правая рука' + '</div>',
														width: 210, sortable: false},
													{name: 'Hand_right_id', type: 'string', width: 10, hidden: true}, //1
													{name: 'Hand_right_Weight', type: 'string',
														header: '<div style="width:60px;text-align:center; align:center; font-family:serif;font-weight: bold;font-size:1.1em;">' + 'Баллы' + '</div>',
														align: 'center', width: 60, sortable: false},
													{name: 'Hand_left_id', type: 'string', width: 10, hidden: true}, //1
													{name: 'Hand_left', type: 'string',
														header: '<div style="width:210px;text-align:center;align:center; font-family:serif;font-weight: bold;font-size:1.1em;">' + 'Левая рука' + '</div>',
														width: 210, sortable: false},
													{name: 'Hand_left_Weight', type: 'string',
														header: '<div style="width:60px;text-align:center; align:center; font-family:serif;font-weight: bold;font-size:1.1em;">' + 'Баллы' + '</div>',
														align: 'center', width: 60, sortable: false}

												],
												focusOnFirstLoad: false,
												noHand: false,
												toolbar: true,
												onBeforeLoadData: function () {
													//this.getButtonSearch().disable();
												}.createDelegate(this),
												onLoadData: function () {
													// alert('Хрень');
													//this.getButtonSearch().enable();
												}.createDelegate(this),
												onRowSelect: function (sm, index, record) {
													var form = Ext.getCmp('ufa_personReabRegistryWindow');
													//Усли рука отсутствует, то
													if (this.noHand == true)
													{
														return;
													}
													//console.log('SprScale=', form.ObjScale.SprScale);
													Ext.getCmp('Reab_ARAT_Parameter').setText(record.get('ScaleParameterType_Name').replace(new RegExp("<br>", 'g'), " "));
													//Формирование данных для Combo
													var a_combo = new Array();
													for (var ii = 0; ii < form.ObjScale.SprScale.length; ii++)
													{
														if (form.ObjScale.SprScale[ii].ScaleParameterType_id == record.get('ScaleParameterType_id'))
														{
															a_combo.push([form.ObjScale.SprScale[ii].ScaleParameterType_id,
																form.ObjScale.SprScale[ii].ScaleParameterResult_id,
																form.ObjScale.SprScale[ii].ScaleParameterResult_Name.replace(new RegExp("<br>", 'g'), " "),
																form.ObjScale.SprScale[ii].ScaleParameterResult_Value]);
														}
													}
													//   console.log('a_combo=',a_combo);
													Ext.getCmp('ReabAratCombo').getStore().removeAll();
													Ext.getCmp('ReabAratCombo').getStore().loadData(a_combo);
													Ext.getCmp('ReabAratCombo').setValue('Введите параметр');
													Ext.getCmp('ReabAratCombo').selectedIndex = -1;
													Ext.getCmp('Reab' + cSysNick + 'input').setDisabled(false);
													Ext.getCmp('ARAT_ButtonSave').focus();
												},
												//Сохранение параметра в GRID
												saveGridArat: function ()
												{
													// console.log('Шлеп = ');
													// console.log('Шлеп = ');
													//Валидация
													if (Ext.getCmp('ReabARATHandSide').getValue() == "" || Ext.getCmp('ReabARATHandSide').getValue() == "Введите параметр")
													{
														form.showMsg("Не указана рука для тестирования");
														return;
													}

													var record = Ext.getCmp('ReabGrid_ARATScale').getGrid().getSelectionModel().getSelected();
													if (Ext.getCmp('ReabARATHandSide').store.data.items[Ext.getCmp('ReabARATHandSide').selectedIndex].data.ReabHandSide_id == 1)
													{
														var side = "right";
													} else
													{
														var side = "left";
													}

													//Отработка  Checkbox !!!!!!!!!!!!!!!!!!!!!!!
													if (Ext.getCmp('missing_hand').checked == true)
													{
														//Нет руки
														this.noHand = true;
														// контроль, что не 2 руки !!!!!!!!!!!!!
														if (side == "right" && record.get("Hand_left") == "Отсутствует" ||
																side == "left" && record.get("Hand_right") == "Отсутствует")
														{
															form.showMsg("Обе руки отсутствовать не могут!!!!");
															return;
														}

														//Прописываем все значения для выбранной руки
														var row_grid = record.get("Id");
														console.log('row_grid = ', row_grid);
														for (var ii = 0; ii < form.ObjScale.SprScale.length; ii++)
														{
															Ext.getCmp('ReabGrid_ARATScale').getGrid().getSelectionModel().selectRow(ii);
															record = Ext.getCmp('ReabGrid_ARATScale').getGrid().getSelectionModel().getSelected();
															record.set("Hand_" + side, "Отсутствует");
															record.set("Hand_" + side + "_Weight", "");
															record.set("Hand_" + side + "_id", "");
															record.commit();
														}
														Ext.getCmp('ReabGrid_ARATScale').getGrid().getSelectionModel().selectRow(row_grid - 1);

														this.noHand = false;
													} else
													{
														//Есть рука
														this.noHand = false;
														if (Ext.getCmp('ReabAratCombo').getValue() == "" || Ext.getCmp('ReabAratCombo').getValue() == "Введите параметр")
														{
															form.showMsg("Не указан результат");
															return;
														}

														//Отловим оригинал результата
														for (var ii = 0; ii < form.ObjScale.SprScale.length; ii++)
														{
															if (form.ObjScale.SprScale[ii].ScaleParameterType_id == record.get('ScaleParameterType_id') &&
																	form.ObjScale.SprScale[ii].ScaleParameterResult_id == Ext.getCmp('ReabAratCombo').getValue())
															{
																var name_parameter = form.ObjScale.SprScale[ii].ScaleParameterResult_Name;
																var weight_parameter = form.ObjScale.SprScale[ii].ScaleParameterResult_Value;
																var name_parameter_id = Ext.getCmp('ReabAratCombo').getValue();
																break;
															}
														}

														//Попробуем записать

														record.set("Hand_" + side, name_parameter);
														record.set("Hand_" + side + "_Weight", weight_parameter);
														record.set("Hand_" + side + "_id", name_parameter_id);
														record.commit();
													}

												},
												//Удаление диагноза
												DelRecord: function ()
												{
													// alert('Удаляем');

												},
												//Добавление диагноза
												AddRecord: function ()
												{
													//MRClimb_id MRCPosition_id MRCLateralization_id MRCValue_id

												}
											})

								]
							});

							Ext.getCmp('ReabGrid_ARATScale').getGrid().view = new Ext.grid.GroupingView(
									{
										//Далее два свойства (enableGroupingMenu и enableNoGroups) - пока что скрываем в меню столбца
										//пункты, связанные с группировкой (на будущее - можно с ними поиграться (смена столбца группировки,
										//включение/выключение группировки...)):
										enableGroupingMenu: false,
										enableNoGroups: false,
										showGroupName: false,
										showGroupsText: true,
										//groupByText: 'NationalCalendarVac_vaccineTypeName',
										//groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? (values.rs.length>4 ? "записей" : "записи") : "запись"]})'
										groupTextTpl: '{text} '
									});

							console.log('ObjScale=', ObjScale);
							//Первоначальное заполнение GRIDa
							var mName = new Array();
							var nRec = 1;
							var cKey = ObjScale.SprScale[0].ScaleParameterType_id;
							mName.push([ObjScale.SprScale[0].ScaleParameterType_Name, ObjScale.SprScale[0].ScaleParameterType_id]);
							for (var ii = 0; ii < Object.keys(ObjScale.SprScale).length; ii++)
							{
								//console.log('nRec=',nRec);
								if (ObjScale.SprScale[ii].ScaleParameterType_id != cKey)
								{
									cKey = ObjScale.SprScale[ii].ScaleParameterType_id;
									mName.push([ObjScale.SprScale[ii].ScaleParameterType_Name, ObjScale.SprScale[ii].ScaleParameterType_id]);
									nRec++;
								}
							}
							//console.log('mName=',mName);
							var name_group = "";
							for (var j = 1; j <= nRec; j++)
							{

								//Определение группы
								if (j < 7)
								{
									name_group = "1 раздел: Захват пятью пальцами";
								} else
								{
									if (j < 11)
									{
										name_group = "2 раздел: Удержание цилиндрического тела";
									} else
									{
										if (j < 16)
										{
											name_group = "3 раздел: Пинцетообразный захват";
										} else
										{
											name_group = "4 раздел: Крупная моторика";
										}

									}
								}

								Ext.getCmp('ReabGrid_ARATScale').getGrid().store.insert(j, [new Ext.data.Record({
									Id: j,
									ScaleParameterType_id: mName[j - 1][1],
									ScaleParameterType_Name: mName[j - 1][0],
									Section: name_group,
									Hand_right: "",
									Hand_right_Weight: "",
									Hand_left: "",
									Hand_left_Weight: "",
									Hand_left_id: "",
									Hand_right_id: ""

								})]);


							}

							var func_question = new Ext.Panel({
								baseCls: 'x-plain',
								id: 'Question_' + cSysNick,
								border: false,
								// width: 600, /////////////////
								disabled: false, //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
								layout: 'form',
								// frame: true,
								bodyStyle: 'padding: 5px;background-color: #E2FFE9', //+bgcolor
								tabIndex: -1,
								items: []
							});
							var tt = new Ext.Panel({
								layout: 'form',
								border: false,
								height: 10
							});
							var total_panel = new Ext.Panel({
								layout: 'form',
								// frame : true,
								border: false,
								id: 'Reab' + cSysNick + 'Summ',
								items: [
									// Итоговый результат оценки
									new Ext.form.FieldSet(
											{
												border: false,
												autoHeight: true,
												hidden: false,
												bodyStyle: 'color:blue;',
												style: 'border-top: 1px solid #99bbe8; border-left: 1px solid #99bbe8; border-bottom: 1px solid #ffffff;border-right: 1px solid #ffffff;padding: 2px 5px;font-color: #AA0000;color:blue;',
												border: true,
												title: 'Результат',
												items: [
													{
														layout: 'form',
														border: false,
														items: [
															{
																layout: 'column',
																border: false,
																items: [
																	{
																		layout: 'form',
																		border: false,
																		labelWidth: 90,
																		labelAlign: 'left',
																		items: [
																			new Ext.form.TextField({
																				allowBlank: true,
																				disabled: true,
																				style: 'font-style:normal;font-size:1.1em;color:black;text-align: center;font-width: bold',
																				labelStyle: 'font-style:italic;font-size:1.1em;color:blue;',
																				fieldLabel: 'Правая рука',
																				id: 'Reab' + cSysNick + 'right_hand',
																				width: 100})
																		]
																	},
																	{
																		layout: 'form',
																		border: false,
																		labelWidth: 80,
																		//   style: 'font-style:italic;font-size:1.1em;color:blue;position:relative;  left:60px' ,
																		style: 'position:relative;  left:60px',
																		labelAlign: 'left',
																		items: [
																			new Ext.form.TextField({
																				allowBlank: true,
																				disabled: true,
																				style: 'font-style:normal;font-size:1.1em;color:black;text-align: center;font-width: bold',
																				labelStyle: 'font-style:italic;font-size:1.1em;color:blue;',
																				fieldLabel: 'Левая рука',
																				id: 'Reab' + cSysNick + 'left_hand',
																				width: 100})
																		]
																	}
																]
															}
														]
													}
												]
											}),
								]
							});
							Ext.getCmp('Question_' + cSysNick).add(total_panel);
							Ext.getCmp('Question_' + cSysNick).add(tt);
							Ext.getCmp('Question_' + cSysNick).add(grid_result);

							var input_panel = new Ext.Panel({
								layout: 'form',
								border: false,
								border: true,
								disabled: true,
//		                         width:1240,
//                                         heigth : 150,
								bodyStyle: 'padding: 10px;background-color: #E2FFE9', //+bgcolor
								style: 'border-top: 1px solid #99bbe8; border-left: 1px solid #99bbe8; border-bottom: 1px solid #ffffff;border-right: 1px solid #ffffff; padding: 2px 0px 0px 0px; margin-top: 5px;background-color: transparent ;',
								id: 'Reab' + cSysNick + 'input',
								items: [
									// ВВод параметров
									//Параметры стороны
									{
										layout: 'column',
										items: [
											{
												layout: 'form',
												border: false,
												labelWidth: 40,
												labelAlign: 'rigth',
												style: 'position:relative; left:40px',
												items: [
													//Рука
													{
														allowBlank: false,
														style: 'text-align : center; font-size:1.1em; ',
														id: 'ReabARATHandSide',
														xtype: 'combo',
														fieldLabel: 'Рука',
														hideTrigger: false, // Chek
														mode: 'local',
														editable: false,
														triggerAction: 'all',
														displayField: 'ReabHandSide',
														valueField: 'ReabHandSide_id',
														width: 150,
														// emptyText: 'Введите параметр',
														// listWidth:'auto',
														hiddenName: 'ReabSpr_Elem_id',
														autoscroll: false,
														store: new Ext.data.SimpleStore({
															fields: [
																{name: 'ReabHandSide', type: 'string'},
																{name: 'ReabHandSide_id', type: 'int'}
															],
															data: [
																['Правая', 1],
																['Левая', 2]
															]
														}),
														tpl: '<tpl for="."><div class="x-combo-list-item">' +
														'{ReabHandSide} ' + '&nbsp;' +
														'</div></tpl>'
													}
												]
											},
											{
												layout: 'form',
												border: false,
												style: 'position:relative; left:70px',
												labelWidth: 90,
												labelAlign: 'rigth',
												// width: 240,
												items: [
													{
														fieldLabel: 'Отсутствует',
														hideLabel: false,
														id: 'missing_hand',
														xtype: 'checkbox',
														listeners: {
															check: function (checked) {
																console.log('checked = ', checked.checked);
																if (checked.checked == true && Ext.getCmp('ReabARATHandSide').getValue() == "")
																{
																	form.showMsg("Не указана рука для тестирования");
																	Ext.getCmp('missing_hand').setValue(false)
																	return;
																}
																if (checked.checked == true)
																{
																	Ext.getCmp('ReabAratCombo').setDisabled(true);
																} else
																{
																	Ext.getCmp('ReabAratCombo').setDisabled(false);
																}

															}
														}
													}
												]
											},
										]
									},
									{
										layout: 'form',
										border: false,
										// width: 1200,
										height: 5
									},
									//Что-то далее
									new Ext.form.Label({
												id: 'Reab_ARAT_Parameter',
												text: "",
												height: 10,
												style: 'font-style:italic;font-size:1.2em;color:blue; font-width: bold;position:relative;  left:80px;'
											}
									),
									{
										layout: 'form',
										border: false,
										// width: 1200,
										height: 5
									},
									{
										layout: 'form',
										border: false,
										// width: 320,
										labelWidth: 60,
										style: 'position:relative;  left:20px;',
										labelAlign: 'left',
										items: [
											//Результат
											{
												allowBlank: false,
												style: 'text-align : center; font-size:1.1em; ',
												id: 'ReabAratCombo',
												xtype: 'combo',
												width: 450,
												fieldLabel: 'Результат',
												hideTrigger: false, // Chek
												mode: 'local',
												editable: false,
												triggerAction: 'all',
												displayField: 'ScaleParameterResult_Name',
												valueField: 'ScaleParameterResult_id',
												hiddenName: 'ReabSpr_Elem_id',
												tabIndex: -1,
												emptyText: 'Введите параметр',
												// listWidth:'auto',
												//   listWidth:'auto',
												autoscroll: false,
												store: new Ext.data.SimpleStore({
													fields: [
														{name: 'ScaleParameterType_id', type: 'int'},
														{name: 'ScaleParameterResult_id', type: 'int'},
														{name: 'ScaleParameterResult_Name', type: 'string'},
														{name: 'ScaleParameterResult_Value', type: 'string'}
													],
													//  data: aSprComboJoint
												}),
												tpl: '<tpl for="."><div class="x-combo-list-item">' +
												'{ScaleParameterResult_Name} ' + '&nbsp;' +
												'</div></tpl>'
											}
										]
									},
									{
										layout: 'form',
										border: false,
										// width: 1200,
										height: 20
									},
									//Кнопки
									{
										layout: 'column',
										items: [
											// кнопка сохранить
											{
												layout: 'form',
												width: 100,
												style: 'position:relative;  left:20px ',
												items: [
													new Ext.Button({
														id: 'ARAT_ButtonSave',
														iconCls: 'save16',
														text: 'Сохранить',
														handler: function (b, e)
														{
															Ext.getCmp('ReabGrid_ARATScale').saveGridArat();
														}.createDelegate(this)
													})
												]
											},
											// кнопка закрыть
//                                                {
//                                                       layout:'form',
//				                       width: 100,
//                                                       style: 'position:relative;  left:250px ',
//                                                      items:[
//					                  new Ext.Button({
//					                 //id: params.data.Grid  + '_'+ params.data.id +'ButtonSave',
//					                  iconCls: 'cancel16',
//					                  text: lang['otmena'],
//					                  handler: function(b,e)
//					                 {
//                                                             //   Ext.getCmp('VASc_Panel').ExitForm();
//					                  }.createDelegate(this)
//                                                         })
//                                                      ]
//                                                  }
										]
									}

								]
							});
							Ext.getCmp('Question_' + cSysNick).add(input_panel);

							Ext.getCmp('FirstPanel' + cSysNick).add(func_question);

							Ext.getCmp('Reab' + cSysNick + 'Summ').hide();
							Ext.getCmp('ReabGrid_ARATScale').noHand = true;

							Ext.getCmp('ViewScale_id').doLayout();
						}

						// Дерево дат
						var form = Ext.getCmp('ufa_personReabRegistryWindow');
						//Дерево дат оценок + управление панелью
						form.ScalesDatesTreePanel = new Ext.tree.TreePanel({
							region: 'west',
							width: 120,
							autoScroll: true,
							id: 'ScalesDatesTreePanel',
							loaded: false,
							border: false,
							height: Ext.getBody().getHeight() - 80,
							root: {
								nodeType: 'async',
								text: 'Даты проведения',
								id: 'root',
								expanded: false
							},
							loader: new Ext.tree.TreeLoader({
								dataUrl: '/?c=Ufa_Reab_Register_User&m=getTreeDatesScales',
								clearOnLoad: true
							}),
							rootVisible: false,
							lastSelectedId: 0,
							listeners: {
								'beforeload': function () {
									form.getEl().mask('Получение оценок по шкалам').show();
								},
								'load': function (node) {
									//   form.getEl().mask().hide();
									//   alert(form.ScalesDatesTreePanel.nDateScale);
//                                                                 if(node.childNodes.length > 0)
//                                                                 {
//
//                                                                 }
									// console.log('node!!!!!!', node);
								},
								'click': function (node, e) {
									//console.log('Шлепнули');
									//console.log('node=', node);

									var nRec = 0;
									for (var k in node.parentNode.childNodes)
									{
										// console.log('childNodes=',node.parentNode.childNodes[k].id);
										if (node.parentNode.childNodes[k].id == node.id)
										{
											nRec = k;
											break;
										}
									}
									if (form.ScalesDatesTreePanel.objDateScale != null)
									{
										form.ScaleLoadData(Ext.getCmp('GridReabScales').getGrid().getSelectionModel().getSelected().get('ScaleType_SysNick'), form.ScalesDatesTreePanel.objDateScale[nRec]);
										Ext.getCmp('deleteReabScaleDataButton').setDisabled(false);
									}
									Ext.getCmp('FillindScaleDateReab').setDisabled(true);
									Ext.getCmp('FillindScaleTimeReab').setDisabled(true);
									Ext.getCmp('saveReabScaleDataButton').setDisabled(true);
									Ext.getCmp('addReabScaleDataButton').setDisabled(false);

									//Разносим по шкалам // disabled
									form.disabledScaleData();
//									if (Ext.getCmp('GridReabScales').getGrid().getSelectionModel().getSelected().get('ScaleType_SysNick') == 'renkin')
//									{
//										Ext.getCmp('ReabScaleGrid').setDisabled(true);
//									}
								}
							}
						});

						// загрузка дат
						var tree = form.ScalesDatesTreePanel;
						tree.getLoader().baseParams = {
							DirectType_id: form.DirectType_id,
							Person_id: form.Person_id,
							Scale_SysNick: cSysNick,
							ReabEvent_id: form.GridReabObjects.getGrid().getSelectionModel().getSelected().data.ReabEvent_id
						}

						form.ScalesDatesTreePanel.getLoader().on(
								'load',
								function (This, node, response) {

									form.getEl().mask().hide();
									Ext.getCmp('DateScale_id').doLayout();
									// console.log('tree.nDateScale=',tree.nDateScale);
									//console.log('tree.getSelectionModel()=',tree.getSelectionModel());
									//console.log('node=',node);
									if (node.childNodes != null && node.childNodes.length > 0)
									{
										tree.getSelectionModel().select(tree.getRootNode( ).childNodes[tree.nDateScale]); //Установка фокуса
									}
									//console.log('Загрузка дат!!!!!!');
									return
								}
						);

						Ext.getCmp('DateScale_id').add(form.ScalesDatesTreePanel);

					},
					//Просчет суммы баллов по шкале
					calcReabScale: function (cSysNick)
					{
						var form = Ext.getCmp('ufa_personReabRegistryWindow');
						var nSummScale = 0;
						var rrr = 0;
						if (cSysNick == 'glasgow')
						{
							console.log('glasgow');

							if (Ext.getCmp('eye_response').getValue() != "")
							{
								rrr = parseFloat(Ext.getCmp('eye_response').value);
							} else
							{
								rrr = 0;
							}
							//rrr = parseFloat( Ext.getCmp('eye_response').value);
							// console.log('rrr=',rrr);
							nSummScale = nSummScale + rrr;

							if (Ext.getCmp('verbal_response').getValue() != "")
							{
								rrr = parseFloat(Ext.getCmp('verbal_response').value);
							} else
							{
								rrr = 0;
							}
							nSummScale = nSummScale + rrr;

							if (Ext.getCmp('motor_response').getValue() != "")
							{
								rrr = parseFloat(Ext.getCmp('motor_response').value);
							} else
							{
								rrr = 0;
							}
							nSummScale = nSummScale + rrr;
							// console.log('nSummScale=',nSummScale);
							Ext.getCmp('ReabScaleValue_id').setValue(nSummScale);

						}
						if (cSysNick == 'Harris' || cSysNick == 'Alarm_HADS' || cSysNick == 'Depression_HADS' || cSysNick == 'МоСА' || cSysNick == 'Berg' || cSysNick == 'Frenchay' || cSysNick == 'Bartel' ||
								cSysNick == 'Vasserman' || cSysNick == 'FIM' || cSysNick == 'dysarthria' || cSysNick == 'Lequesne' || cSysNick == 'rivermid_DAA' || cSysNick == 'nihss')
						{
							//Определяем кол-во параметров
							var nRec = 1;
							var cKey = form.ObjScale.SprScale[0].ScaleParameterType_id;
							// console.log('cKey=',cKey);
							for (var ii = 0; ii < Object.keys(form.ObjScale.SprScale).length; ii++)
							{
								if (form.ObjScale.SprScale[ii].ScaleParameterType_id != cKey)
								{
									cKey = form.ObjScale.SprScale[ii].ScaleParameterType_id;
									nRec++;
								}
							}

							var cname_field = "";
							switch (cSysNick) {
								//Шкала харриса
								case 'Harris':
								//Госпитальная шкала тревоги (HADS)
								case 'Alarm_HADS':
								//Госпитальная шкала тревоги (HADS)
								case 'Depression_HADS':
								//Монреальская шкала оценки когнитивных функций (МоСА)
								case 'МоСА':
								// Шкала равновесия Берга
								case 'Berg':
								//Тест для руки Френчай
								case 'Frenchay':
								//Шкала Бартела
								case 'Bartel':
								//Шкала Вассермана
								case 'Vasserman':
								//Тест оценки дизартрии
								case 'dysarthria':
								//Шкала тяжести инсультов
								case 'nihss':
								//Шкала Лекена
								case 'Lequesne':
								//Шкала активностей повседнгевной жизни Ривермид
								case 'rivermid_DAA':
									cname_field = cSysNick + 'Field_';
									break;
								//Шкала FIM
								case 'FIM':
									cname_field = cSysNick + 'Field_';
									var n_motor_func = 0;
									var n_intellect = 0;
									break;
								default :
									form.showMsg('Косяк');
									return;
									break;
							}
							;

							for (var k = 1; k <= nRec; k++)
							{

								if (Ext.getCmp(cname_field + k).getValue() != "")
								{
									rrr = parseFloat(Ext.getCmp(cname_field + k).getValue());
								} else
								{
									rrr = 0;
								}
								if (cSysNick == 'FIM')
								{
									if (k < 14)
									{
										n_motor_func = n_motor_func + rrr;
									} else
									{
										n_intellect = n_intellect + rrr;
									}
								}
								nSummScale = nSummScale + rrr;
							}
							Ext.getCmp('ReabScaleValue_id').setValue(nSummScale);
							if (cSysNick == 'FIM')
							{
								// Суммы по группе
								Ext.getCmp('Reab' + cSysNick + 'Motor_Func').setValue(n_motor_func);
								Ext.getCmp('Reab' + cSysNick + 'Intellig').setValue(n_intellect);
							}

						}

						if (cSysNick == 'GRACE')
						{
							// console.log('попали!!!!');
							if (Ext.getCmp('ReabGraceAgeField').getValue() != "")
							{
								rrr = parseFloat(Ext.getCmp('ReabGraceAgeField').value);
							} else
							{
								rrr = 0;
							}
							nSummScale = nSummScale + rrr;
							if (Ext.getCmp('GraceCreatinine_Field').getValue() != "")
							{
								rrr = parseFloat(Ext.getCmp('GraceCreatinine_Field').value);
							} else
							{
								rrr = 0;
							}
							nSummScale = nSummScale + rrr;
							if (Ext.getCmp('GraceHeartRate_Field').getValue() != "")
							{
								rrr = parseFloat(Ext.getCmp('GraceHeartRate_Field').value);
							} else
							{
								rrr = 0;
							}
							nSummScale = nSummScale + rrr;
							if (Ext.getCmp('GraceKillipT_Field').getValue() != "")
							{
								rrr = parseFloat(Ext.getCmp('GraceKillipT_Field').value);
							} else
							{
								rrr = 0;
							}
							nSummScale = nSummScale + rrr;
							if (Ext.getCmp('GraceArterial_pressure_Field').getValue() != "")
							{
								rrr = parseFloat(Ext.getCmp('GraceArterial_pressure_Field').value);
							} else
							{
								rrr = 0;
							}
							nSummScale = nSummScale + rrr;
							if (Ext.getCmp('GraceIncreasedMarkers_Field').getValue() != "")
							{
								rrr = parseFloat(Ext.getCmp('GraceIncreasedMarkers_Field').value);
							} else
							{
								rrr = 0;
							}
							nSummScale = nSummScale + rrr;
							if (Ext.getCmp('GraceHeartFailure_Field').getValue() != "")
							{
								rrr = parseFloat(Ext.getCmp('GraceHeartFailure_Field').value);
							} else
							{
								rrr = 0;
							}
							nSummScale = nSummScale + rrr;
							if (Ext.getCmp('GraceDeviationST_Field').getValue() != "")
							{
								rrr = parseFloat(Ext.getCmp('GraceDeviationST_Field').value);
							} else
							{
								rrr = 0;
							}
							nSummScale = nSummScale + rrr;
							//console.log('nSummScale=',nSummScale);
							Ext.getCmp('ReabScaleValue_id').setValue(nSummScale);
						}

						return;
					},
					// Пока отрабатываем рисрвания формы
					Erunda: function (aSpr) {
						var form = Ext.getCmp('ufa_personReabRegistryWindow');
						this.pmuser_id = getGlobalOptions().pmuser_id;
						this.Lpu_id = getGlobalOptions().lpu_id;

						Ext.getCmp('ufa_personReabRegistryWindow').ReabRegistry_id = false;
						Ext.getCmp('informReab').removeAll();



						//Запускаем справочник
						console.log('Запускаем справочник');
//              Ext.getCmp('ReabResp_1').getStore().load(
//               { params:{
//                    SprNumber: 1,
//                    SprNumberGroup:1
//                      }});


						Ext.getCmp('ReabResp_2_1').getStore().load(
								{params: {
									SprNumber: 2,
									SprNumberGroup: 1
								}});




						//По 1 типу
						var cResp_3 = new Ext.form.ComboBox(
								{

									allowBlank: false,
									id: 'ReabResp_3',
									hideLabel: true,
									hideTrigger: true, // Chek
									mode: 'local',
									editable: false,
									triggerAction: 'all',
									displayField: 'SprName',
									valueField: 'ReabSpr_Elem_id',
									style: 'width: 400px; border:none;font-size:1.1em',
									width: 'auto',
									tabIndex: -1,
									emptyText: 'Введите параметр',
									listWidth: 'auto',
									hiddenName: 'ReabSpr_Elem_id',
									autoscroll: false,
									xtype: 'combo',
									store: new Ext.data.JsonStore(
											{
												url: '?c=Ufa_Reab_Register_User&m=ReabSpr',
												autoLoad: false,
												fields:
														[
															{name: 'ReabSpr_Elem_id', type: 'int'},
															{name: 'SprName', type: 'string'}
														],
												key: 'ReabSpr_Elem_id',
											}),
									tpl: '<tpl for="."><div class="x-combo-list-item">' +
									'{SprName} ' + '&nbsp;' +
									'</div></tpl>',
									listeners: {
										specialkey: function (field, e) {
											console.log('FIELD', field)
											if (e.getKey() == e.ENTER) {
												//Ext.getCmp('getMorbusType_id').handler();
											}
										}
									}
								});


						//Запускаем справочник
						console.log('Запускаем справочник');

						Ext.getCmp('ReabResp_3').getStore().load(
								{params: {
									SprNumber: 3,
									SprNumberGroup: 1
								}});
						Ext.getCmp('ReabResp_4').getStore().load(
								{params: {
									SprNumber: 4,
									SprNumberGroup: 1
								}});
						Ext.getCmp('ReabResp_5').getStore().load(
								{params: {
									SprNumber: 5,
									SprNumberGroup: 1
								}});




						var cResp_10 = new Ext.form.ComboBox(
								{
									allowBlank: false,
									id: 'ReabResp_10',
									hideLabel: true,
									hideTrigger: true, // Chek
									mode: 'local',
									editable: false,
									triggerAction: 'all',
									displayField: 'SprName',
									valueField: 'ReabSpr_Elem_id',
									style: 'width: 400px; border:none;font-size:1.1em',
									width: 'auto',
									tabIndex: -1,
									emptyText: 'Введите параметр',
									listWidth: 'auto',
									hiddenName: 'ReabSpr_Elem_id',
									autoscroll: false,
									xtype: 'combo',
									store: new Ext.data.JsonStore(
											{
												url: '?c=Ufa_Reab_Register_User&m=ReabSpr',
												autoLoad: false,
												fields:
														[
															{name: 'ReabSpr_Elem_id', type: 'int'},
															{name: 'SprName', type: 'string'}
														],
												key: 'ReabSpr_Elem_id',
											}),
									tpl: '<tpl for="."><div class="x-combo-list-item">' +
									'{SprName} ' + '&nbsp;' +
									'</div></tpl>',
									listeners: {
										specialkey: function (field, e) {
											console.log('FIELD', field)
											if (e.getKey() == e.ENTER) {
												//Ext.getCmp('getMorbusType_id').handler();
											}
										}
									}
								});


						var Quest_15 = new Ext.form.Label({
									text: '15. Наличие дыхательной недостаточности',
									height: 10,
									style: 'font-style:italic;font-size:1.2em;color:blue; '
								}
						);
						var cResp_11 = new Ext.form.ComboBox(
								{
									allowBlank: false,
									id: 'ReabResp_11',
									hideLabel: true,
									hideTrigger: true, // Chek
									mode: 'local',
									editable: false,
									triggerAction: 'all',
									displayField: 'SprName',
									valueField: 'ReabSpr_Elem_id',
									style: 'width: 400px; border:none;font-size:1.1em',
									width: 'auto',
									tabIndex: -1,
									emptyText: 'Введите параметр',
									listWidth: 'auto',
									hiddenName: 'ReabSpr_Elem_id',
									autoscroll: false,
									xtype: 'combo',
									store: new Ext.data.JsonStore(
											{
												url: '?c=Ufa_Reab_Register_User&m=ReabSpr',
												autoLoad: false,
												fields:
														[
															{name: 'ReabSpr_Elem_id', type: 'int'},
															{name: 'SprName', type: 'string'}
														],
												key: 'ReabSpr_Elem_id',
											}),
									tpl: '<tpl for="."><div class="x-combo-list-item">' +
									'{SprName} ' + '&nbsp;' +
									'</div></tpl>',
									listeners: {
										specialkey: function (field, e) {
											console.log('FIELD', field)
											if (e.getKey() == e.ENTER) {
												//Ext.getCmp('getMorbusType_id').handler();
											}
										}
									}
								});

						var Quest_16 = new Ext.form.Label({
									text: '16. Эмоциональная сфера',
									height: 10,
									style: 'font-style:italic;font-size:1.2em;color:blue; '
								}
						);

						var cResp_12 = new Ext.form.ComboBox(
								{
									allowBlank: false,
									id: 'ReabResp_12',
									hideLabel: true,
									hideTrigger: true, // Chek
									mode: 'local',
									editable: false,
									triggerAction: 'all',
									displayField: 'SprName',
									valueField: 'ReabSpr_Elem_id',
									style: 'width: 400px; border:none;font-size:1.1em',
									width: 'auto',
									tabIndex: -1,
									emptyText: 'Введите параметр',
									listWidth: 'auto',
									hiddenName: 'ReabSpr_Elem_id',
									autoscroll: false,
									xtype: 'combo',
									store: new Ext.data.JsonStore(
											{
												url: '?c=Ufa_Reab_Register_User&m=ReabSpr',
												autoLoad: false,
												fields:
														[
															{name: 'ReabSpr_Elem_id', type: 'int'},
															{name: 'SprName', type: 'string'}
														],
												key: 'ReabSpr_Elem_id',
											}),
									tpl: '<tpl for="."><div class="x-combo-list-item">' +
									'{SprName} ' + '&nbsp;' +
									'</div></tpl>',
									listeners: {
										specialkey: function (field, e) {
											console.log('FIELD', field)
											if (e.getKey() == e.ENTER) {
												//Ext.getCmp('getMorbusType_id').handler();
											}
										}
									}
								});
						var Quest_17 = new Ext.form.Label({
									text: '17. Наличие в анамнезе ИБС',
									height: 10,
									style: 'font-style:italic;font-size:1.2em;color:blue; '
								}
						);

						var cResp_13 = new Ext.form.ComboBox(
								{
									allowBlank: false,
									id: 'ReabResp_13',
									hideLabel: true,
									hideTrigger: true, // Chek
									mode: 'local',
									editable: false,
									triggerAction: 'all',
									displayField: 'SprName',
									valueField: 'ReabSpr_Elem_id',
									style: 'width: 400px; border:none;font-size:1.1em',
									width: 'auto',
									tabIndex: -1,
									emptyText: 'Введите параметр',
									listWidth: 'auto',
									hiddenName: 'ReabSpr_Elem_id',
									autoscroll: false,
									xtype: 'combo',
									store: new Ext.data.JsonStore(
											{
												url: '?c=Ufa_Reab_Register_User&m=ReabSpr',
												autoLoad: false,
												fields:
														[
															{name: 'ReabSpr_Elem_id', type: 'int'},
															{name: 'SprName', type: 'string'}
														],
												key: 'ReabSpr_Elem_id',
											}),
									tpl: '<tpl for="."><div class="x-combo-list-item">' +
									'{SprName} ' + '&nbsp;' +
									'</div></tpl>',
									listeners: {
										specialkey: function (field, e) {
											console.log('FIELD', field)
											if (e.getKey() == e.ENTER) {
												//Ext.getCmp('getMorbusType_id').handler();
											}
										}
									}
								});

						var Quest_18 = new Ext.form.Label({
									text: '18. Когнитивные нарушения',
									height: 10,
									style: 'font-style:italic;font-size:1.2em;color:blue; '
								}
						);
						var cResp_14 = new Ext.form.ComboBox(
								{
									allowBlank: false,
									id: 'ReabResp_14',
									hideLabel: true,
									hideTrigger: true, // Chek
									mode: 'local',
									editable: false,
									triggerAction: 'all',
									displayField: 'SprName',
									valueField: 'ReabSpr_Elem_id',
									style: 'width: 400px; border:none;font-size:1.1em',
									width: 'auto',
									tabIndex: -1,
									emptyText: 'Введите параметр',
									listWidth: 'auto',
									hiddenName: 'ReabSpr_Elem_id',
									autoscroll: false,
									xtype: 'combo',
									store: new Ext.data.JsonStore(
											{
												url: '?c=Ufa_Reab_Register_User&m=ReabSpr',
												autoLoad: false,
												fields:
														[
															{name: 'ReabSpr_Elem_id', type: 'int'},
															{name: 'SprName', type: 'string'}
														],
												key: 'ReabSpr_Elem_id',
											}),
									tpl: '<tpl for="."><div class="x-combo-list-item">' +
									'{SprName} ' + '&nbsp;' +
									'</div></tpl>',
									listeners: {
										specialkey: function (field, e) {
											console.log('FIELD', field)
											if (e.getKey() == e.ENTER) {
												//Ext.getCmp('getMorbusType_id').handler();
											}
										}
									}
								});

						//2  раз Combo2
						var Quest_19 = new Ext.form.Label({
									text: '19. Наличие аномалий сосудов головного мозга',
									height: 10,
									style: 'font-style:italic;font-size:1.2em;color:blue; '
								}
						);
						//Ответ - 1(2 combo)
						var cResp_15 = new Ext.Panel({
							layout: 'form',
							border: false,
							//autoWidth: true,
							width: '100%',
							frame: false, //Отражение панели
							items: [
								{
									layout: 'form',
									border: false,
									labelWidth: 70,
									labelAlign: 'left',
									items: [
										{
											allowBlank: false,
											anyparam: 'anyparam',
											id: 'ReabResp_15_1',
											listWidth: 'auto',
											emptyText: 'Введите параметр',
											hideTrigger: true,
											hideLabel: true,
											style: 'width: 700px; border:none;font-size:1.1em',
											mode: 'local',
											store: new Ext.data.JsonStore(
													{
														url: '?c=Ufa_Reab_Register_User&m=ReabSpr',
														autoLoad: false,
														fields:
																[
																	{name: 'ReabSpr_Elem_id', type: 'int'},
																	{name: 'SprName', type: 'string'},
																	{name: 'ReabSpr_Level', type: 'string'}
																],
														key: 'ReabSpr_Elem_id',
													}),
											editable: false,
											triggerAction: 'all',
											displayField: 'SprName',
											valueField: 'ReabSpr_Elem_id',
											// width: 150,
											autoscroll: false,
											xtype: 'combo',
											listeners: {
												specialkey: function (field, e) {
													console.log('FIELD', field)
													if (e.getKey() == e.ENTER) {
														//Ext.getCmp('getMorbusType_id').handler();
													}
												},

												select: function (combo, record, index) {
													console.log('record.ReabSpr_Elem_Weight=', record.data.ReabSpr_Level);
													if (record.data.ReabSpr_Level != '')
													{
														//Запускаем 2 combo
														Ext.getCmp('ReabResp_15_2').show();
														// Ext.getCmp('ReabResp_2_2').url = '?c=Ufa_Reab_Register_User&m=' + record.data.ReabSpr_Level;
														//  Ext.getCmp('ReabResp_2_2').getStore().url = '?c=Ufa_Reab_Register_User&m=' + record.data.ReabSpr_Level;
														// Ext.getCmp('ReabResp_2_2').store.url = '?c=Ufa_Reab_Register_User&m=' + record.data.ReabSpr_Level;
														Ext.getCmp('ReabResp_15_2').getStore().load(
																{
																	params: {
																		SprNumber: 15,
																		SprNumberGroup: 2
																	},
																});
														Ext.getCmp('ReabResp_15_2').selectedIndex = -1;
														Ext.getCmp('ReabResp_15_2').setValue('Введите параметр');
													} else
													{
														Ext.getCmp('ReabResp_15_2').hide();

													}
												}
											}

										}
									]
								},
								{
									layout: 'form',
									border: false,
									//style: 'position:relative;  left:40px ',
									// labelWidth: 130,
									//width: 250,
									labelAlign: 'left',
									items: [
										{
											xtype: 'combo',
											disabled: false,
											id: 'ReabResp_15_2',
											emptyText: 'Введите параметр',
											mode: 'local',
											style: 'width: 700px; border:none;font-size:1.1em',
											hidden: true,
											hideTrigger: true,
											hideLabel: true,
											listWidth: 'auto',
											triggerAction: 'all',
											store: new Ext.data.JsonStore(
													{
														url: '?c=Ufa_Reab_Register_User&m=ReabSpr',
														autoLoad: false,
														fields:
																[
																	{name: 'ReabSpr_Elem_id', type: 'int'},
																	{name: 'SprName', type: 'string'},
																	{name: 'ReabSpr_Level', type: 'string'}
																],
														key: 'ReabSpr_Elem_id',
													}),
											editable: false,
											triggerAction: 'all',
											displayField: 'SprName',
											valueField: 'ReabSpr_Elem_id',
											// width: 150,
											autoscroll: false
										}
									]
								}
							]
						})



						Ext.getCmp('ReabResp_9').getStore().load(
								{params: {
									SprNumber: 9,
									SprNumberGroup: 1
								}});
						Ext.getCmp('ReabResp_10').getStore().load(
								{params: {
									SprNumber: 10,
									SprNumberGroup: 1
								}});
						Ext.getCmp('ReabResp_11').getStore().load(
								{params: {
									SprNumber: 11,
									SprNumberGroup: 1
								}});
						Ext.getCmp('ReabResp_12').getStore().load(
								{params: {
									SprNumber: 12,
									SprNumberGroup: 1
								}});
						Ext.getCmp('ReabResp_13').getStore().load(
								{params: {
									SprNumber: 13,
									SprNumberGroup: 1
								}});
						Ext.getCmp('ReabResp_14').getStore().load(
								{params: {
									SprNumber: 14,
									SprNumberGroup: 1
								}});

						Ext.getCmp('ReabResp_15_1').getStore().load(
								{params: {
									SprNumber: 15,
									SprNumberGroup: 1
								}});

						var QuestionPanel_7 = new Ext.Panel({
							baseCls: 'x-plain',
							//id : 'QuestionPanel_'+params.data.id,
							id: 'QuestionPanel_7',
							border: false,
							layout: 'form',
							//autoWidth: true,
							width: '100%',
							//tabIndex:-1,
							items: [
								{
									xtype: 'panel',
									border: false,
									layout: 'column',
									//id : 'itemsQuestionPanel_'+params.data.id,
									id: 'itemsQuestionPanel_7',
									items: []
								}
							]
						});

						Ext.getCmp('QuestionPanel_7').add(Quest_13);
						Ext.getCmp('QuestionPanel_7').add(cResp_9);
						Ext.getCmp('QuestionPanel_7').add(Quest_14);
						Ext.getCmp('QuestionPanel_7').add(cResp_10);
						Ext.getCmp('QuestionPanel_7').add(Quest_15);
						Ext.getCmp('QuestionPanel_7').add(cResp_11);
						Ext.getCmp('QuestionPanel_7').add(Quest_16);
						Ext.getCmp('QuestionPanel_7').add(cResp_12);
						Ext.getCmp('QuestionPanel_7').add(Quest_17);
						Ext.getCmp('QuestionPanel_7').add(cResp_13);
						Ext.getCmp('QuestionPanel_7').add(Quest_18);
						Ext.getCmp('QuestionPanel_7').add(cResp_14);
						Ext.getCmp('QuestionPanel_7').add(Quest_19);
						Ext.getCmp('QuestionPanel_7').add(cResp_15);
						Ext.getCmp('QuestionPanel_7').doLayout();

						Ext.getCmp('informReab').add(
								new Ext.Panel({
									title: 'Анамнез',
									id: 'group_7',
									border: false,
									layout: 'column',
									bodyStyle: 'margin: 10px;',
									collapsible: true,
									// autoWidth: false,
									autoHeight: true,
									//  height: 'auto',
									border: false,
									listeners: {
										'render': function (panel) {
											panel.header.on('click', function () {
												if (panel.collapsed) {
													panel.expand();
												} else {
													panel.collapse();
												}
											});
										}
									},
									items: [
										QuestionPanel_7
									]
								})
						);

						Ext.getCmp('group_7').doLayout();

						//   console.log('zzzzzzzzzzzzz');

//                var questionsForm = form.createForm();
//                             questionsForm.removeAll();
						console.log('response2');
						console.log('response3');

						Ext.getCmp('informReab').doLayout();
					},
					//Валидация данных анкеты
					validatRegistryData: function () {
						var form = Ext.getCmp('ufa_personReabRegistryWindow');
						var cMessage = 'Отсутствует ответ на вопрос :';
						//console.log('form.TemplObject=', form.TemplObject);
						for (var k in form.TemplObject)
						{
							if ((form.TemplObject[k].Elem_Type == 'Grid1' && form.TemplObject[k].Parameter_id == 1 || form.TemplObject[k].Elem_Type == 'Grid2' || form.TemplObject[k].Elem_Type == 'RGrid1') //|| form.TemplObject[k].Elem_Type == 'Grid4'
									&& Ext.getCmp('ReabGrid_' + form.TemplObject[k].Parameter_id).getGrid().getStore().data.items.length == 0)
							{
								cMessage = cMessage + '<br>' + form.TemplObject[k].Number + '. ' + form.TemplObject[k].Parameter_Name;
							}

							if (form.TemplObject[k].Elem_Type == 'Combo')
							{
								if (form.TemplObject[k].Global == 2)
								{
									//Общий признак
									if (Ext.getCmp('ComboGroup_' + form.TemplObject[k].Group_id).selectedIndex == -1 || Ext.getCmp('ComboGroup_' + form.TemplObject[k].Group_id).selectedIndex == 0)
									{
										var cTitle = Ext.getCmp('ReabgroupPanel_' + form.TemplObject[k].Group_id).title;
										// console.log('cTitle=',cTitle);

										if (Ext.getCmp('ReabComboAnk_' + form.TemplObject[k].Parameter_id).selectedIndex == -1 || Ext.getCmp('ReabComboAnk_' + form.TemplObject[k].Parameter_id).getValue() == ""
												|| Ext.getCmp('ReabComboAnk_' + form.TemplObject[k].Parameter_id).getValue() == "Параметр не определен")
										{
											cMessage = cMessage + '<br>' + cTitle.substr(cTitle.indexOf('>') + 1).replace('</span>', '') + ": "
													+ form.TemplObject[k].Number + '. ' + form.TemplObject[k].Parameter_Name;
										}
									}

								} else
								{
									if (Ext.getCmp('ReabComboAnk_' + form.TemplObject[k].Parameter_id).selectedIndex == -1 || Ext.getCmp('ReabComboAnk_' + form.TemplObject[k].Parameter_id).getValue() == ""
											|| Ext.getCmp('ReabComboAnk_' + form.TemplObject[k].Parameter_id).getValue() == "Параметр не определен")
									{
										cMessage = cMessage + '<br>' + form.TemplObject[k].Number + '. ' + form.TemplObject[k].Parameter_Name;
									}
								}
							}
							if (form.TemplObject[k].Elem_Type == 'Combo2')
							{
								//console.log('Combo2=');
								if (Ext.getCmp('ReabComboAnk_' + form.TemplObject[k].Parameter_id).selectedIndex == -1 || Ext.getCmp('ReabComboAnk_' + form.TemplObject[k].Parameter_id).getValue() == "")
								{
									cMessage = cMessage + '<br>' + form.TemplObject[k].Number + '. ' + form.TemplObject[k].Parameter_Name;
								} else
								{
									var zz = Ext.getCmp('ReabComboAnk_' + form.TemplObject[k].Parameter_id).getStore().data.items[Ext.getCmp('ReabComboAnk_' + form.TemplObject[k].Parameter_id).selectedIndex].data.ReabSpr_Level;
									//console.log('zz=',zz);
									if (zz != "" && (Ext.getCmp('ReabComboAnk_' + form.TemplObject[k].Parameter_id + '_2').selectedIndex == -1 || Ext.getCmp('ReabComboAnk_' + form.TemplObject[k].Parameter_id + '_2').getValue() == ""))
									{
										cMessage = cMessage + '<br>' + form.TemplObject[k].Number + '. ' + form.TemplObject[k].Parameter_Name + '(Уточнение)';
									}
								}
							}

							if (form.TemplObject[k].Elem_Type == 'Field2')
							{
								if (Ext.getCmp('ReabFieldAnk_' + form.TemplObject[k].Spr_Cod).getValue() == "")
								{
									cMessage = cMessage + '<br>' + form.TemplObject[k].Number + '. ' + form.TemplObject[k].Parameter_Name;
								}
								if (Ext.getCmp('ReabFieldAnk_' + form.TemplObject[k].Spr_Cod).isValid() == false)
								{
									cMessage = cMessage + '<br>' + form.TemplObject[k].Number + '. ' + form.TemplObject[k].Parameter_Name + " (неправильный ввод параметра)"
								}
							}


							if (form.TemplObject[k].Elem_Type == 'RGrid2' && Ext.getCmp('ReabGrid_' + form.TemplObject[k].Parameter_id).getGrid().getStore().data.items.length > 0)
							{
								if (Ext.getCmp('HipPower_id').selectedIndex == -1 || Ext.getCmp('HipPower_id').getValue() == "")
								{
									cMessage = cMessage + '<br>' + form.TemplObject[k].Number + '. Степень';
								}
								if (Ext.getCmp('HipStage_id').selectedIndex == -1 || Ext.getCmp('HipStage_id').getValue() == "")
								{
									cMessage = cMessage + '<br>' + form.TemplObject[k].Number + '. Стадия';
								}
								if (Ext.getCmp('HipRisk_Id').selectedIndex == -1 || Ext.getCmp('HipRisk_Id').getValue() == "")
								{
									cMessage = cMessage + '<br>' + form.TemplObject[k].Number + '. Риск';
								}
							}
						}
						if (cMessage != 'Отсутствует ответ на вопрос :')
						{
							form.showMsg(cMessage);
							return false;
						} else {
							return true;
						}

					},
					//Расчет реабилитационного потенциала
					calcReabPotent: function ()
					{
						var form = Ext.getCmp('ufa_personReabRegistryWindow');
						var PotentSumm = 0;
						var nPotentSumm = 0;
						for (var k in form.TemplObject)
						{
							//console.log('Шаб=',form.TemplObject[k].PriznSumm);
							if (form.TemplObject[k].PriznSumm == 2)
							{
								if (form.TemplObject[k].Elem_Type == 'Combo')
								{
									if (form.TemplObject[k].Global == 2)
									{
										if (Ext.getCmp('ComboGroup_' + form.TemplObject[k].Group_id).selectedIndex == -1 || Ext.getCmp('ComboGroup_' + form.TemplObject[k].Group_id).selectedIndex == 0)
										{
											//console.log('Просчет суммы для Combo ??????');
											// console.log('ComboGroup_' + form.TemplObject[k].Group_id);
											var rrr = parseFloat(Ext.getCmp('ReabComboAnk_' + form.TemplObject[k].Parameter_id).getStore().data.items[Ext.getCmp('ReabComboAnk_' + form.TemplObject[k].Parameter_id).selectedIndex].data.ReabSpr_Elem_Weight);
											nPotentSumm = nPotentSumm + rrr;
										}

									} else
									{
										var rrr = parseFloat(Ext.getCmp('ReabComboAnk_' + form.TemplObject[k].Parameter_id).getStore().data.items[Ext.getCmp('ReabComboAnk_' + form.TemplObject[k].Parameter_id).selectedIndex].data.ReabSpr_Elem_Weight);
										// console.log('rrr',rrr);
										PotentSumm = PotentSumm + Ext.getCmp('ReabComboAnk_' + form.TemplObject[k].Parameter_id).getStore().data.items[Ext.getCmp('ReabComboAnk_' + form.TemplObject[k].Parameter_id).selectedIndex].data.ReabSpr_Elem_Weight;
										nPotentSumm = nPotentSumm + rrr;
									}
									//  console.log('PotencSumm(Combo)=',nPotentSumm);
									// console.log('form.TemplObject[k]=',form.TemplObject[k]);
								}
								if (form.TemplObject[k].Elem_Type == 'Combo2')
								{
									//console.log('Просчет суммы для Combo2');
									if (Ext.getCmp('ReabComboAnk_' + form.TemplObject[k].Parameter_id).getStore().data.items[Ext.getCmp('ReabComboAnk_' + form.TemplObject[k].Parameter_id).selectedIndex].data.ReabSpr_Elem_Weight != "")
									{
										//console.log('Просчет суммы для Combo22');
										var rrr = parseFloat(Ext.getCmp('ReabComboAnk_' + form.TemplObject[k].Parameter_id).getStore().data.items[Ext.getCmp('ReabComboAnk_' + form.TemplObject[k].Parameter_id).selectedIndex].data.ReabSpr_Elem_Weight);
										nPotentSumm = nPotentSumm + rrr;
										//console.log('nPotencSumm=',nPotentSumm);

									}
									//console.log('Combo444=', Ext.getCmp('ReabComboAnk_' + form.TemplObject[k].Parameter_id).getStore().data.items[Ext.getCmp('ReabComboAnk_' + form.TemplObject[k].Parameter_id).selectedIndex].data.ReabSpr_Level);
									if (Ext.getCmp('ReabComboAnk_' + form.TemplObject[k].Parameter_id).getStore().data.items[Ext.getCmp('ReabComboAnk_' + form.TemplObject[k].Parameter_id).selectedIndex].data.ReabSpr_Level != "")
									{
										var rrr = parseFloat(Ext.getCmp('ReabComboAnk_' + form.TemplObject[k].Parameter_id + '_2').getStore().data.items[Ext.getCmp('ReabComboAnk_' + form.TemplObject[k].Parameter_id + '_2').selectedIndex].data.ReabSpr_Elem_Weight);
										nPotentSumm = nPotentSumm + rrr;
										//console.log('nPotencSumm1=', nPotentSumm);
									}

								}
								// console.log('nPotencSumm=',nPotencSumm);
								if (form.TemplObject[k].Elem_Type == 'Grid3')
								{
									var nRecord = Ext.getCmp('ReabGrid_' + form.TemplObject[k].Parameter_id).getGrid().getStore().data.items.length;
									for (var i = 0; i < nRecord; i++)
									{
										if (Ext.getCmp('ReabGrid_' + form.TemplObject[k].Parameter_id).getGrid().getStore().data.items[i].data.selrow == 'Да')
										{
											var rrr = parseFloat(Ext.getCmp('ReabGrid_' + form.TemplObject[k].Parameter_id).getGrid().getStore().data.items[i].data.ReabSpr_Elem_Weight);
											nPotentSumm = nPotentSumm + rrr;
										}
									}
								}
								if (form.TemplObject[k].Elem_Type == 'Grid4')
								{
									var nRecord = Ext.getCmp('ReabGrid_' + form.TemplObject[k].Parameter_id).getGrid().getStore().data.items.length;
									for (var i = 0; i < nRecord; i++) {

										var rrr = parseFloat(Ext.getCmp('ReabGrid_' + form.TemplObject[k].Parameter_id).getGrid().getStore().data.items[i].data.StageComp_Weight);
										nPotentSumm = nPotentSumm + rrr;
									}
									//console.log('Просчет суммы для Grid4');
								}
								if (form.TemplObject[k].Elem_Type == 'Grid5')
								{
									if (Ext.getCmp('ComboGroup_' + form.TemplObject[k].Group_id).selectedIndex == -1 || Ext.getCmp('ComboGroup_' + form.TemplObject[k].Group_id).selectedIndex == 0)
									{
										var nRecord = Ext.getCmp('ReabGrid_' + form.TemplObject[k].Parameter_id).getGrid().getStore().data.items.length;
										for (var i = 0; i < nRecord; i++) {
											if (Ext.getCmp('ReabGrid_' + form.TemplObject[k].Parameter_id).getGrid().getStore().data.items[i].data.selrow == 'Да')
											{
												var rrr = parseFloat(Ext.getCmp('ReabGrid_' + form.TemplObject[k].Parameter_id).getGrid().getStore().data.items[i].data.ReabSpr_Elem_Weight);
												nPotentSumm = nPotentSumm + rrr;
											}
										}
									}
								}
								if (form.TemplObject[k].Elem_Type == 'Grid7')
								{
									var nRecord = Ext.getCmp('ReabGrid_' + form.TemplObject[k].Parameter_id).getGrid().getStore().data.items.length;
									if (nRecord > 0)
									{
										nPotentSumm = nPotentSumm + 1;
									}

									//console.log('Просчет суммы для Grid7');
								}
								if (form.TemplObject[k].Elem_Type == 'Grid8')
								{
									var nRecord = Ext.getCmp('ReabGrid_' + form.TemplObject[k].Parameter_id).getGrid().getStore().data.items.length;
									nPotentSumm = nPotentSumm + nRecord;

									//console.log('Просчет суммы для Grid8');
								}
								if (form.TemplObject[k].Elem_Type == 'RGrid1')
								{
									var nRecord = Ext.getCmp('ReabGrid_' + form.TemplObject[k].Parameter_id).getGrid().getStore().data.items.length;
									for (var i = 0; i < nRecord; i++) {
										var rrr = parseFloat(Ext.getCmp('ReabGrid_' + form.TemplObject[k].Parameter_id).getGrid().getStore().data.items[i].data.StageComp_Weight);
										nPotentSumm = nPotentSumm + rrr;
									}
									console.log('Просчет суммы для RGrid1');
								}

							}
						}
						console.log('PotencSumm(итог)=', PotentSumm);
						console.log('nPotencSumm(итог)=', nPotentSumm);

						/*
						 if(form.Templ == 'cnsReab1')
						 {
						 var Person_Birthday = Ext.getCmp('ufa_personReabRegistryWindow').personInfo.Person_Birthday;
						 var age =  Ext.getCmp('ufa_personReabRegistryWindow').getAge(Person_Birthday, Ext.getCmp('TextFieldDateReab').getValue());
						 console.log('Добавить балл по возрасту');
						 console.log('age =',age);

						 if(parseInt(age) <= 49)
						 {
						 nPotentSumm = nPotentSumm + 1;
						 }
						 else
						 {
						 if(parseInt(age) >= 50 && parseInt(age) <=69 )
						 {
						 nPotentSumm = nPotentSumm + 2;
						 }
						 else{ nPotentSumm = nPotentSumm + 3}
						 }
						 console.log('nPotencSumm(Возраст)=',nPotentSumm);
						 }
						 */
						if (form.DirectSysNick == 'travmReab')
						{
							var age = Ext.getCmp('ReabFieldAnk_age').getValue();
							//Возраст
							if (parseInt(age) >= 18 && parseInt(age) <= 45)
							{
								nPotentSumm = nPotentSumm + 1;
							}
							if (parseInt(age) >= 46 && parseInt(age) <= 75)
							{
								nPotentSumm = nPotentSumm + 2;
							}
							if (parseInt(age) >= 76 && parseInt(age) <= 100)
							{
								nPotentSumm = nPotentSumm + 3;
							}
							//ИМТ
							console.log('parseFloat=', parseFloat(Ext.getCmp('ReabFieldAnk_Index').getValue()));
							var nIndex = parseFloat(Ext.getCmp('ReabFieldAnk_Index').getValue());
							if (nIndex < 18.5)
							{
								nPotentSumm = nPotentSumm + 2;
							}
							if (nIndex >= 25.0 && nIndex <= 30.0)
							{
								nPotentSumm = nPotentSumm + 1;
							}
							if (nIndex > 30.0)
							{
								nPotentSumm = nPotentSumm + 2;
							}
						}
						if (form.DirectSysNick == 'cardiologyReab')
						{
							//ИМТ
							var nIndex = parseFloat(Ext.getCmp('ReabFieldAnk_Index').getValue());
							if (nIndex < 18.5)
							{
								nPotentSumm = nPotentSumm + 2;
							}
							if (nIndex >= 25.0 && nIndex <= 30.0)
							{
								nPotentSumm = nPotentSumm + 1;
							}
							if (nIndex > 30.0)
							{
								nPotentSumm = nPotentSumm + 2;
							}
							//Талия
							var nWaist = parseFloat(Ext.getCmp('ReabFieldAnk_waist').getValue());
							console.log('nWaist=', nWaist);
							// Ext.getCmp('ufa_personReabRegistryWindow').PersonInfoPanelReab.DataView.store.data.items[0].json.Person_Birthday
							if (form.PersonInfoPanelReab.DataView.store.data.items[0].json.Sex_id == "1" && nWaist > 94) //Мужик
							{
								nPotentSumm = nPotentSumm + 1;
							}
							if (form.PersonInfoPanelReab.DataView.store.data.items[0].json.Sex_id == "2" && nWaist > 80) //НЕ Мужик
							{
								nPotentSumm = nPotentSumm + 1;
							}
							//Гипергликемия
							var nHyperglycaemia = Ext.getCmp('ReabFieldAnk_hyperglycaemia').getValue();
							if (nHyperglycaemia >= 5.6 && nHyperglycaemia <= 6.9)
							{
								nPotentSumm = nPotentSumm + 1;
							}
							if (nHyperglycaemia > 6.9)
							{
								nPotentSumm = nPotentSumm + 2;
							}
						}

						console.log('nPotencSumm(конец)=', nPotentSumm);
						return nPotentSumm;
					},
					//Сохранение данных
					saveRegistryData: function () {
						var form = Ext.getCmp('ufa_personReabRegistryWindow');
						// console.log('Сохранение1');


						// Ограничение по времени
						var diff = Math.ceil((new Date().getTime() - Ext.getCmp('TextFieldDateReab').getValue().getTime()) / (1000 * 60 * 60 * 24)) - 1;
						if (Ext.getCmp('TextFieldDateReab').getValue() > new Date)
						{
							form.showMsg('Недопустимо указывать дату больше текущей!');
							Ext.getCmp('TextFieldDateReab').setValue(new Date());
							return;
						}
						if (diff > 30)
						{
							form.showMsg('Дата проведения анкетирования не может быть ранее 30 дней от текущей даты. Пожалуйста, проверьте указанную дату анкетирования.');
							Ext.getCmp('TextFieldDateReab').setValue(new Date());
							return;
						}

						//Валидация
						if (form.validatRegistryData() == false)
						{
							return;
						}

						var loadMask = new Ext.LoadMask(this.getEl(), {msg: lang['podojdite_idet_sohranenie']});
						loadMask.show();

						//Подготовка к сохранению
						var oSave = new Object();
						for (var k in form.TemplObject)
						{
							var cValue = "";
							// console.log('Сохранение3');
							if (form.TemplObject[k].Elem_Type == 'Grid1' || form.TemplObject[k].Elem_Type == 'Grid8')
							{
								var nRecord = Ext.getCmp('ReabGrid_' + form.TemplObject[k].Parameter_id).getGrid().getStore().data.items.length;
								for (var i = 0; i < nRecord; i++) {
									//var record = Ext.getCmp('ReabGrid_' + form.TemplObject[k].Parameter_id).getGrid().getStore().data.items[i].data;
									cValue = cValue + Ext.getCmp('ReabGrid_' + form.TemplObject[k].Parameter_id).getGrid().getStore().data.items[i].data.Diag_Code + ';';
								}
								oSave[form.TemplObject[k].Parameter_id] = form.TemplObject[k].id + "~" + cValue;
							}

							if (form.TemplObject[k].Elem_Type == 'Grid2' || form.TemplObject[k].Elem_Type == 'Grid7')
							{
								var nRecord = Ext.getCmp('ReabGrid_' + form.TemplObject[k].Parameter_id).getGrid().getStore().data.items.length;
								for (var i = 0; i < nRecord; i++)
								{
									var objDate = Ext.getCmp('ReabGrid_' + form.TemplObject[k].Parameter_id).getGrid().getStore().data.items[i].data.SetDate
									var day = ('0' + objDate.getDate()).slice(-2);
									var month = ('0' + (objDate.getMonth() + 1)).slice(-2);
									var Year = objDate.getFullYear();
									var cDateDiag = day + '.' + month + '.' + Year;
									//console.log('cDateDiag=',cDateDiag);
									cValue = cValue + cDateDiag + '||';
									//cValue = cValue + Ext.getCmp('ReabGrid_' + form.TemplObject[k].Parameter_id).getGrid().getStore().data.items[i].data.SetDate.substr(0, 10) + '||';
									// cValue = cValue + cDate + '||';
									cValue = cValue + Ext.getCmp('ReabGrid_' + form.TemplObject[k].Parameter_id).getGrid().getStore().data.items[i].data.Diag_Code + ';';
								}

								oSave[form.TemplObject[k].Parameter_id] = form.TemplObject[k].id + "~" + cValue;
							}
							//  console.log('Сохранение5');
							if (form.TemplObject[k].Elem_Type == 'Combo')
							{
								if (form.TemplObject[k].Global == 2)
								{
									//Общий признак
									if (Ext.getCmp('ComboGroup_' + form.TemplObject[k].Group_id).selectedIndex == -1 || Ext.getCmp('ComboGroup_' + form.TemplObject[k].Group_id).selectedIndex == 0)
									{ //Параметры обязаны быть
										cValue = Ext.getCmp('ReabComboAnk_' + form.TemplObject[k].Parameter_id).getStore().data.items[Ext.getCmp('ReabComboAnk_' + form.TemplObject[k].Parameter_id).selectedIndex].data.ReabSpr_Elem_id + ';';
										// oSave[form.TemplObject[k].Parameter_id] = cValue;
									} else
									{
										// Параматры отсутствуют
										cValue = '0;';
									}
									//oSave[form.TemplObject[k].Parameter_id] = cValue + ';';
								} else
								{
									// cValue = Ext.getCmp('ReabComboAnk_'+ form.TemplObject[k].Parameter_id).getStore().data.items[Ext.getCmp('ReabComboAnk_'+ form.TemplObject[k].Parameter_id).selectedIndex].data.ReabSpr_Elem_id + ';';
									cValue = Ext.getCmp('ReabComboAnk_' + form.TemplObject[k].Parameter_id).getValue() + ';';
									//  console.log('cValue=',cValue);
									//  var cValue1 = Ext.getCmp('ReabComboAnk_'+ form.TemplObject[k].Parameter_id).getValue();
									//  console.log('cValue1=',cValue1);

								}
								oSave[form.TemplObject[k].Parameter_id] = form.TemplObject[k].id + "~" + cValue;
							}
							// console.log('Сохранение6');
							if (form.TemplObject[k].Elem_Type == 'Combo2')
							{
								var zz = Ext.getCmp('ReabComboAnk_' + form.TemplObject[k].Parameter_id).getStore().data.items[Ext.getCmp('ReabComboAnk_' + form.TemplObject[k].Parameter_id).selectedIndex].data.ReabSpr_Level;
								cValue = Ext.getCmp('ReabComboAnk_' + form.TemplObject[k].Parameter_id).getStore().data.items[Ext.getCmp('ReabComboAnk_' + form.TemplObject[k].Parameter_id).selectedIndex].data.ReabSpr_Elem_id + ';';
								if (zz != "")
								{
									//Записываем 2 значение
									cValue = cValue + '||' + Ext.getCmp('ReabComboAnk_' + form.TemplObject[k].Parameter_id + '_2').getStore().data.items[Ext.getCmp('ReabComboAnk_' + form.TemplObject[k].Parameter_id + '_2').selectedIndex].data.ReabSpr_Elem_id + ';';

								}
								oSave[form.TemplObject[k].Parameter_id] = form.TemplObject[k].id + "~" + cValue;
							}
							if (form.TemplObject[k].Elem_Type == 'Grid3')
							{
								var nRecord = Ext.getCmp('ReabGrid_' + form.TemplObject[k].Parameter_id).getGrid().getStore().data.items.length;
								for (var i = 0; i < nRecord; i++) {
									if (Ext.getCmp('ReabGrid_' + form.TemplObject[k].Parameter_id).getGrid().getStore().data.items[i].data.selrow == 'Да')
									{
										cValue = cValue + 'Да;'
									} else
									{
										cValue = cValue + 'Нет;'
									}
								}
								oSave[form.TemplObject[k].Parameter_id] = form.TemplObject[k].id + "~" + cValue;
							}
							if (form.TemplObject[k].Elem_Type == 'Grid4')
							{
								var nRecord = Ext.getCmp('ReabGrid_' + form.TemplObject[k].Parameter_id).getGrid().getStore().data.items.length;
								if (nRecord == 0)
								{
									//Нет записей
									cValue = '0;'
								} else
								{
									for (var i = 0; i < nRecord; i++) {
										console.log('cValue=', cValue);
										cValue = cValue + Ext.getCmp('ReabGrid_' + form.TemplObject[k].Parameter_id).getGrid().getStore().data.items[i].data.Diag_Code + '||';
										cValue = cValue + Ext.getCmp('ReabGrid_' + form.TemplObject[k].Parameter_id).getGrid().getStore().data.items[i].data.StageComp_id + ';';
									}
								}
								oSave[form.TemplObject[k].Parameter_id] = form.TemplObject[k].id + "~" + cValue;
								console.log('oSave=', oSave[form.TemplObject[k].Parameter_id]);
							}
							if (form.TemplObject[k].Elem_Type == 'Grid5')
							{
								var nRecord = Ext.getCmp('ReabGrid_' + form.TemplObject[k].Parameter_id).getGrid().getStore().data.items.length;
								if (form.TemplObject[k].Global == 2)
								{
									//Общий признак
									if (Ext.getCmp('ComboGroup_' + form.TemplObject[k].Group_id).selectedIndex == -1 || Ext.getCmp('ComboGroup_' + form.TemplObject[k].Group_id).selectedIndex == 0)
									{ //Параметры обязаны быть
										for (var i = 0; i < nRecord; i++) {
											if (Ext.getCmp('ReabGrid_' + form.TemplObject[k].Parameter_id).getGrid().getStore().data.items[i].data.selrow == 'Да')
											{
												cValue = cValue + 'Да;'
											} else
											{
												cValue = cValue + 'Нет;'
											}
										}
									} else
									{
										for (var i = 0; i < nRecord; i++) {
											cValue = cValue + 'Нет;'
										}
									}
								} else
								{
									for (var i = 0; i < nRecord; i++) {
										if (Ext.getCmp('ReabGrid_' + form.TemplObject[k].Parameter_id).getGrid().getStore().data.items[i].data.selrow == 'Да')
										{
											cValue = cValue + 'Да;'
										} else
										{
											cValue = cValue + 'Нет;'
										}
									}
								}
								oSave[form.TemplObject[k].Parameter_id] = form.TemplObject[k].id + "~" + cValue;
							}

							if (form.TemplObject[k].Elem_Type == 'RGrid1')
							{
								var nRecord = Ext.getCmp('ReabGrid_' + form.TemplObject[k].Parameter_id).getGrid().getStore().data.items.length;
								for (var i = 0; i < nRecord; i++) {
									console.log('cValue=', cValue);
									cValue = cValue + Ext.getCmp('ReabGrid_' + form.TemplObject[k].Parameter_id).getGrid().getStore().data.items[i].data.Type_of_joint_id + '||';
									cValue = cValue + Ext.getCmp('ReabGrid_' + form.TemplObject[k].Parameter_id).getGrid().getStore().data.items[i].data.Side_id + '||';
									cValue = cValue + Ext.getCmp('ReabGrid_' + form.TemplObject[k].Parameter_id).getGrid().getStore().data.items[i].data.Motion_Id + '||';
									cValue = cValue + Ext.getCmp('ReabGrid_' + form.TemplObject[k].Parameter_id).getGrid().getStore().data.items[i].data.Deformation_id + '||';
									cValue = cValue + Ext.getCmp('ReabGrid_' + form.TemplObject[k].Parameter_id).getGrid().getStore().data.items[i].data.JointContracture_id + '||';
									cValue = cValue + Ext.getCmp('ReabGrid_' + form.TemplObject[k].Parameter_id).getGrid().getStore().data.items[i].data.Intervention_id + '||';
									cValue = cValue + Ext.getCmp('ReabGrid_' + form.TemplObject[k].Parameter_id).getGrid().getStore().data.items[i].data.StageComp_Weight + ';';
								}

								oSave[form.TemplObject[k].Parameter_id] = form.TemplObject[k].id + "~" + cValue;
								//  console.log('oSave=',oSave[form.TemplObject[k].Parameter_id]);
							}

							if (form.TemplObject[k].Elem_Type == 'RGrid2')
							{
								var nRecord = Ext.getCmp('ReabGrid_' + form.TemplObject[k].Parameter_id).getGrid().getStore().data.items.length;
								if (nRecord > 0)
								{
									for (var i = 0; i < nRecord; i++)
									{
										var objDate = Ext.getCmp('ReabGrid_' + form.TemplObject[k].Parameter_id).getGrid().getStore().data.items[i].data.SetDate
										var day = ('0' + objDate.getDate()).slice(-2);
										var month = ('0' + (objDate.getMonth() + 1)).slice(-2);
										var Year = objDate.getFullYear();
										var cDateDiag = day + '.' + month + '.' + Year;
										console.log('cDateDiag=', cDateDiag);
										cValue = cValue + cDateDiag + '||';
										//cValue = cValue + Ext.getCmp('ReabGrid_' + form.TemplObject[k].Parameter_id).getGrid().getStore().data.items[i].data.SetDate.substr(0, 10) + '||';
										cValue = cValue + Ext.getCmp('ReabGrid_' + form.TemplObject[k].Parameter_id).getGrid().getStore().data.items[i].data.Diag_Code + ';';
									}
									//console.log('cValue=', cValue);
									cValue = cValue + Ext.getCmp('HipPower_id').getValue() + '``';
									cValue = cValue + Ext.getCmp('HipStage_id').getValue() + '``';
									cValue = cValue + Ext.getCmp('HipRisk_Id').getValue() + ';';
								} else
								{
									cValue = "";
								}
								oSave[form.TemplObject[k].Parameter_id] = form.TemplObject[k].id + "~" + cValue;
							}

							if (form.TemplObject[k].Elem_Type == 'Field1' || form.TemplObject[k].Elem_Type == 'Field2')
							{
								cValue = Ext.getCmp('ReabFieldAnk_' + form.TemplObject[k].Spr_Cod).getValue() + ';';
								oSave[form.TemplObject[k].Parameter_id] = form.TemplObject[k].id + "~" + cValue;
							}


						}
						//console.log('oSave=', oSave);

						// РАсчет потенциала
						var nPotent = 0;
						nPotent = form.calcReabPotent();
						//console.log('nPotent=', nPotent);


						//Временное ограничение
						if (form.DirectSysNick != 'travmReab' && form.DirectSysNick != 'cardiologyReab' && form.DirectSysNick != 'cnsReab'	)
						{
							loadMask.hide();
							//alert('Сохранения нет');
							// form.AnketaDisabled(true);
							Ext.getCmp('TextFieldDateReab').setDisabled(true);
							Ext.getCmp('editReabDataButton').setDisabled(true);
							Ext.getCmp('addReabDataButton').setDisabled(false);
							Ext.getCmp('saveReabDataButton').setDisabled(false);

							return
						}
						//Защита
						if (form.isButtonAdd == true && form.isButtonEdit == true)
						{
							loadMask.hide();
							//lang['oshibka_zaprosa_k_serveru_poprobuyte_povtorit_operatsiyu']
							sw.swMsg.alert(lang['soobschenie'], lang['oshibka_pri_vyipolnenii_zaprosa_k_serveru'] + 'isButtonAdd');
							return;
						}

						//---------------------------------------
						//console.log('Сохранение1',form.isButtonAdd);
						//console.log('ReabRegister_Stage', Ext.getCmp('ufa_personReabRegistryWindow').StageType_id);
						//Само сохранение
						Ext.Ajax.request({
							url: '?c=Ufa_Reab_Register_User&m=saveRegistrAnketa',
							params: {
								Person_id: Ext.getCmp('ufa_personReabRegistryWindow').Person_id,
								DirectType_id: Ext.getCmp('ufa_personReabRegistryWindow').DirectType_id,
								StageType_id: Ext.getCmp('ufa_personReabRegistryWindow').StageType_id,
								ReabQuestion_setDate: Ext.getCmp('TextFieldDateReab').getValue(),
								MedPersonal_iid: getGlobalOptions().medpersonal_id,
								Lpu_iid: getGlobalOptions().lpu_id,
								ReabPotent: nPotent,
								parameter: Ext.util.JSON.encode(oSave),
								isButtonAdd: form.isButtonAdd,
								isButtonEdit: form.isButtonEdit

							},
							callback: function (options, success, response)
							{
								// console.log('success=',success);
								// console.log('response=',response);
								loadMask.hide(); // Обязательно сделать
								if (success == true)
								{
									Ext.getCmp('saveReabDataButton').setDisabled(true);
									var response_obj = Ext.util.JSON.decode(response.responseText);

									if (response_obj.success == true)
									{

										Ext.getCmp('TextFieldDateReab').setDisabled(true);
										Ext.getCmp('calday').setValue(Ext.getCmp('TextFieldDateReab').getValue());

										form.showMsg(lang['dannyie_sohranenyi']);  //!!!!!!!!!!!!!!!!!! Переставить

										form.HeadAnketa = response_obj.headAnketa;
										//console.log('form.HeadAnketa=', form.HeadAnketa);
										//Отработка периодов
										Ext.getCmp('calday').getPeriod();
										// Отработка окна потенциала
										if (form.isButtonAdd == true)
										{
											form.getReabPotentPanel(0);
										}
										if (form.isButtonEdit == true)
										{
											var cDate = Ext.getCmp('calday').value;
											var nPeriod = 0;
											for (var k in form.HeadAnketa)
											{
												if (form.HeadAnketa[k].ReabAnketa_Data.date.substr(0, 10) == (cDate.substr(6, 4) + '-' + cDate.substr(3, 2) + '-' + cDate.substr(0, 2)))
												{
													nPeriod = k;
													break;
												}
											}
											form.getReabPotentPanel(nPeriod);
										}
										//Открываем Просмотр дат анкет
										Ext.getCmp('ViewReabDataButton').setDisabled(false);
										console.log('Все хорошо');
									}
									Ext.getCmp('editReabDataButton').setDisabled(false);
									Ext.getCmp('addReabDataButton').setDisabled(false);
									form.isButtonEdit = false;
									form.isButtonAdd = false;
									form.AnketaDisabled(true);
								} else {
									sw.swMsg.alert(lang['soobschenie'], lang['oshibka_pri_vyipolnenii_zaprosa_k_serveru']);
								}


							}
						})
					},
					/**
					 * Редактирование анкеты
					 */
					AnketUpdate: function ()
					{
						//Валидация на доступ к редактированию
						var form = Ext.getCmp('ufa_personReabRegistryWindow');
						form.isButtonEdit = true;
						form.isButtonAdd = false;

						var cDate = Ext.getCmp('calday').value;
						var nPeriod = 0;
						for (var k in form.HeadAnketa)
						{
							if (form.HeadAnketa[k].ReabAnketa_Data.date.substr(0, 10) == (cDate.substr(6, 4) + '-' + cDate.substr(3, 2) + '-' + cDate.substr(0, 2)))
							{
								nPeriod = k;
								break;
							}
						}

						form.isButtonAdd = false;
						//console.log('form.HeadAnketa[nPeriod]', form.HeadAnketa[nPeriod]);
						if ((form.HeadAnketa[nPeriod].MedPersonal_iid == getGlobalOptions().medpersonal_id && form.HeadAnketa[nPeriod].Lpu_iid == getGlobalOptions().lpu_id) || isSuperAdmin())
						{

							var diff = Math.ceil((new Date().getTime() - Ext.getCmp('TextFieldDateReab').getValue().getTime()) / (1000 * 60 * 60 * 24)) - 1;
							if (diff > 30)
							{
								form.showMsg('Дата редактирования анкеты не может быть ранее 30 дней от текущей даты. Пожалуйста, проверьте указанную дату анкетирования.');
								return;
							}
							if (form.HeadAnketa[nPeriod].ReabRegister_OutCause != "0")
							{
								form.showMsg('Анкета не может быть отредактирована. Этап закрыт.');
								return;
							}

							//Продолжаем
							Ext.getCmp('saveReabDataButton').setDisabled(false);
							Ext.getCmp('editReabDataButton').setDisabled(true);
							Ext.getCmp('addReabDataButton').setDisabled(true);

							this.AnketaDisabled(false);
							for (var k in form.TemplObject)
							{
								if (form.TemplObject[k].Elem_Type == 'Grid7' && form.TemplObject[k].TemplJoin !== null)
								{
									//console.log('form.TemplObject[k]',form.TemplObject[k]);
									//console.log('form.TemplObject[k].Parameter_id=',form.TemplObject[k].Parameter_id);
									var nRecord = Ext.getCmp('ReabGrid_' + form.TemplObject[k].Parameter_id).getGrid().getStore().data.items.length;
									if (nRecord > 0)
									{
										Ext.getCmp('ComboGroup_' + form.TemplObject[k].TemplJoin).setValue(1);
										Ext.getCmp('ComboGroup_' + form.TemplObject[k].TemplJoin).setDisabled(true);
									} else
									{
										for (var i in form.TemplObject)
										{
											if (form.TemplObject[i].Group_id == form.TemplObject[k].TemplJoin)
											{
												if (Ext.getCmp('ReabComboAnk_' + form.TemplObject[i].Parameter_id).getValue() == "" ||
														Ext.getCmp('ReabComboAnk_' + form.TemplObject[i].Parameter_id).getValue() == "Введите параметр")
												{
													Ext.getCmp('ComboGroup_' + form.TemplObject[k].TemplJoin).setValue(2);
												} else
												{
													Ext.getCmp('ComboGroup_' + form.TemplObject[k].TemplJoin).setValue(1);
												}
											}
										}


										Ext.getCmp('ComboGroup_' + form.TemplObject[k].TemplJoin).setDisabled(false);


									}
								}

								if (form.TemplObject[k].Elem_Type == 'Grid10')
								{
									form.getReabDiagICF('ReabGrid_' + form.TemplObject[k].Parameter_id);
								}
							}
						} else
						{
							sw.swMsg.alert(lang['soobschenie'], lang['u_vas_net_prav_na_redaktirovanie']);
						}

					},
					/**
					 * Сброс данных в анкекте
					 */
					AnketReset: function () {
						var form = Ext.getCmp('ufa_personReabRegistryWindow');
						for (var k in form.TemplObject)
						{
							if (form.TemplObject[k].Elem_Type == 'Grid1' || form.TemplObject[k].Elem_Type == 'Grid7' || form.TemplObject[k].Elem_Type == 'Grid8' || form.TemplObject[k].Elem_Type == 'RGrid1'|| form.TemplObject[k].Elem_Type == 'Grid10')
							{
								Ext.getCmp('ReabGrid_' + form.TemplObject[k].Parameter_id).getGrid().store.removeAll();
								Ext.getCmp('ReabGrid_' + form.TemplObject[k].Parameter_id).getGrid().tbar.dom.firstChild.firstChild.firstChild.firstChild.lastChild.firstChild.innerText = '0 / 0';
							}

							if (form.TemplObject[k].Elem_Type == 'Field1' || form.TemplObject[k].Elem_Type == 'Field2')
							{
								Ext.getCmp('ReabFieldAnk_' + form.TemplObject[k].Spr_Cod).setValue("");
							}

							if (form.TemplObject[k].Elem_Type == 'Combo')
							{
								Ext.getCmp('ReabComboAnk_' + form.TemplObject[k].Parameter_id).setValue('Введите параметр');
								Ext.getCmp('ReabComboAnk_' + form.TemplObject[k].Parameter_id).selectedIndex = -1;
							}

							if (form.TemplObject[k].Elem_Type == 'Combo2')
							{
								Ext.getCmp('ReabComboAnk_' + form.TemplObject[k].Parameter_id).setValue('Введите параметр');
								Ext.getCmp('ReabComboAnk_' + form.TemplObject[k].Parameter_id).selectedIndex = -1;
								Ext.getCmp('ReabComboAnk_' + form.TemplObject[k].Parameter_id + '_2').setValue('Введите параметр');
								Ext.getCmp('ReabComboAnk_' + form.TemplObject[k].Parameter_id + '_2').selectedIndex = -1;
								Ext.getCmp('ReabComboAnk_' + form.TemplObject[k].Parameter_id + '_2').hide();
							}

							if (form.TemplObject[k].Elem_Type == 'RGrid1' || form.TemplObject[k].Elem_Type == 'RGrid2')
							{
								Ext.getCmp(form.TemplObject[k].Elem_Type + '_' + form.TemplObject[k].Parameter_id + '_Panel').ComplaintReset();
							}
							if (form.TemplObject[k].Elem_Type == 'Grid5')
							{
								// Если надо - проставить 'Нет'
								var records = Ext.getCmp('ReabGrid_' + form.TemplObject[k].Parameter_id).getGrid().getStore().data.items;
								if (records.length > 0)
								{
									Ext.getCmp('ReabGrid_' + form.TemplObject[k].Parameter_id).getGrid().store.removeAll( );
								}
								for (jj = 0; jj < records.length; jj++)
								{
									Ext.getCmp('ReabGrid_' + form.TemplObject[k].Parameter_id).getGrid().store.insert(jj, [new Ext.data.Record({
										ReabSpr_Elem_id: records[jj].data.ReabSpr_Elem_id,
										selrow: "Нет",
										ReabSpr_Elem_Name: records[jj].data.ReabSpr_Elem_Name,
										ReabSpr_Elem_Weight: records[jj].data.ReabSpr_Elem_Weight
									})]);
								}
							}
						}

					},

					/**
					 * Добавление анкеты
					 */
					addRegistryData: function ()
					{
						//Обнулять дланные ????????????????????????
						var form = Ext.getCmp('ufa_personReabRegistryWindow');
						// Отсекаем закрытые этапы
						if (form.GridReabObjects.getGrid().getSelectionModel().getSelected().get('OutCause_id') != 0)
						{
							sw.swMsg.alert(lang['soobschenie'], 'Добавление анкет невозможно. Этап закрыт!');
							return
						}
						form.isButtonEdit = false;
						form.isButtonAdd = true;
						// Ext.getCmp('TextFieldDateReab').setValue(oDate.date.substr(0,10));
						Ext.getCmp('TextFieldDateReab').setValue(new Date());
						Ext.getCmp('TextFieldDateReab').setDisabled(false);
						Ext.getCmp('editReabDataButton').setDisabled(true);
						Ext.getCmp('addReabDataButton').setDisabled(true);
						//
						this.AnketaDisabled(false);
						Ext.getCmp('panelReabInfo').hide(); // Закрываем панель потенциала


						//для Травмы 1 этап
						if (form.DirectSysNick == 'travmReab' || form.DirectSysNick == 'cnsReab' || form.DirectSysNick == 'cardiologyReab')
						{
							var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет поиск..."});
							loadMask.show();
							//Обнуление Анкет
							form.AnketReset();
							//
							if (form.DirectSysNick == 'travmReab')
							{
								//Возраст
								var age = form.getAge(Ext.getCmp('ufa_personReabRegistryWindow').PersonInfoPanelReab.DataView.store.data.items[0].json.Person_Birthday, Ext.getCmp('TextFieldDateReab').getValue());
								Ext.getCmp('ReabFieldAnk_age').setValue(age);
							}

							//Программное заполнение Эндокринология и кровеносные сосуды, cnsReab2, кардиология
							for (var k in form.TemplObject)
							{
								if (form.TemplObject[k].Elem_Type == 'Grid7' || form.TemplObject[k].Elem_Type == 'RGrid2')
								{
									form.getDiagPerson(form.TemplObject[k].Parameter_id, form.TemplObject[k].Spr_Cod, form.TemplObject[k].Elem_Type, form.TemplObject[k].TemplJoin);
									//console.log('TemplJoin=',form.TemplObject[k].TemplJoin);
								}
								if (form.TemplObject[k].Elem_Type == 'Grid10')
								{
									form.getReabDiagICF('ReabGrid_' + form.TemplObject[k].Parameter_id);
								}


							}
							loadMask.hide();
						}


						Ext.getCmp('saveReabDataButton').setDisabled(false);
					},
					/**
					 * Подтягивание анкеты на дату
					 */
					loadAnketaInDate: function (direction) {
						//console.log(' Подтягивание анкеты на дату');
						var loadMask = new Ext.LoadMask(this.getEl(), {msg: lang['zagruzka']});
						//loadMask.show();
						var form = Ext.getCmp('ufa_personReabRegistryWindow');
						// Тянем только данные на определенную дату
						if (direction.length > 1)
						{
							//Через кнопку - просмотр

							var nPeriod = 0;
							for (var k in form.HeadAnketa)
							{
								if (form.HeadAnketa[k].ReabAnketa_Data.date.substr(0, 10) == direction)
								{
									nPeriod = k;
									break;
								}
							}

							var oDate = form.HeadAnketa[nPeriod].ReabAnketa_Data;
							//console.log('nPeriod=', nPeriod);
							form.getReabPotentPanel(nPeriod);
							//return;
						} else
						{
							//формируем дату - обязательно через calday
							var cDate = Ext.getCmp('calday').value;
							var nPeriod = 0;
							for (var k in form.HeadAnketa)
							{
								if (form.HeadAnketa[k].ReabAnketa_Data.date.substr(0, 10) == (cDate.substr(6, 4) + '-' + cDate.substr(3, 2) + '-' + cDate.substr(0, 2)))
								{
									nPeriod = k;
									break;
								}
							}

							if (direction == '<')
							{
								//console.log('назад');
								nPeriod++;
								console.log('nPeriod=', nPeriod);
							}
							if (direction == '>')
							{
								nPeriod--;
							}
							var oDate = form.HeadAnketa[nPeriod].ReabAnketa_Data;
							form.getReabPotentPanel(nPeriod);

							//Временно!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
							if (Ext.getCmp('ufa_personReabRegistryWindow').DirectType_id == 0 || Ext.getCmp('ufa_personReabRegistryWindow').StageType_id == 0)
							{
								Ext.getCmp('ufa_personReabRegistryWindow').showMsg('Для данного профиля или этапа отсутствует анкета');
								Ext.getCmp('TextFieldDateReab').setValue(oDate.date.substr(0, 10));
								Ext.getCmp('calday').setValue(oDate.date.substr(0, 10));
								//Отработка периодов
								Ext.getCmp('calday').getPeriod();
								return;
							}
						}

						/////////////////////////////////////////////////////

						Ext.Ajax.request({
							url: '/?c=Ufa_Reab_Register_User&m=loadAnketa',
							params: {
								DirectType_id: Ext.getCmp('ufa_personReabRegistryWindow').DirectType_id,
								StageType_id: Ext.getCmp('ufa_personReabRegistryWindow').StageType_id,
								Person_id: Ext.getCmp('ufa_personReabRegistryWindow').Person_id,
								//DateAnketa: oDate.date.substr(0,10)
								DateAnketa: new Date(oDate.date).dateFormat('d.m.Y')
							},
							callback: function (options, success, response)
							{
								//console.log('success=',success);
								loadMask.hide();
								if (success === true)
								{
									var ObjAnketa = Ext.util.JSON.decode(response.responseText);
									//  var obodyAnketa = ObjAnketa.bodyAnketa;
									form.AnketaLoadData(ObjAnketa.bodyAnketa);
									//  console.log('rec=');
									Ext.getCmp('TextFieldDateReab').setValue(oDate.date.substr(0, 10));
									//Ext.getCmp('calday').setValue(allObjAnketa.headAnketa[0].ReabAnketa_Data.date.dateFormat('d.m.Y'));
									Ext.getCmp('calday').setValue(oDate.date.substr(0, 10));
									//Отработка периодов
									Ext.getCmp('calday').getPeriod();
								}
							}
						})

					},
					/**
					 * Первоначальная загрузка (Справочники, шаблон, данные)
					 */
					AnketLoad: function () {
						//alert('Хочу анкету по шаблону');
						var form = Ext.getCmp('ufa_personReabRegistryWindow');
						Ext.getCmp('informReab').removeAll(); // Очистили панель

						Ext.getCmp('saveReabDataButton').setDisabled(true);  //!!!!!!!!!!!!!!!!!!
						var loadMask = new Ext.LoadMask(this.getEl(), {msg: lang['zagruzka']});
						loadMask.show();

						// Формируем дату анкетирования и перемещение по датам
						var TextFieldDate = new sw.Promed.SwDateField({
							id: 'TextFieldDateReab',
							labelField: 'Дата проведения',
							labelSeparator: ':',
							disabled: Ext.getCmp('ufa_personReabRegistryWindow').elemDisabled(),
							labelWidth: '50px',
							width: '100px',
							plugins: [
								new Ext.ux.InputTextMask('99.99.9999', false)
							],
							xtype: 'swdatefield',
							format: 'd.m.Y',
							value: Ext.getCmp('calday').getValue(),
							maxValue: getGlobalOptions().date,
							listeners: {
								'change': function () {
									//   Ext.getCmp('calday').setValue(this.getValue());
									//   alert('Время');
									// Удаленная дата
									if (this.getValue() == '') {
										form.showMsg('Введите дату анкетирования!');
										this.setValue(new Date());
										return;
									}

									var Person_Birthday = Ext.getCmp('ufa_personReabRegistryWindow').PersonInfoPanelReab.DataView.store.data.items[0].json.Person_Birthday;


									var diff = Math.ceil((new Date().getTime() - this.getValue().getTime()) / (1000 * 60 * 60 * 24)) - 1;

									if (this.getValue() > new Date)
									{
										// form.showMsg('Недопустимо указывать дату позднее текущей!');
										// this.setValue(new Date());


										var age = Ext.getCmp('ufa_personReabRegistryWindow').getAge(Person_Birthday, new Date());
										//Ext.getCmp('Answer_'+answerAge).setValue(age);
										return;
									} else if (diff > 30) {
										form.showMsg('Дата проведения анкетирования не может быть ранее 30 дней от текущей даты. Пожалуйста, проверьте указанную дату анкетирования.');
										// this.setValue(new Date());
										return;
									} else {

										//var age =  Ext.getCmp('ufa_personReabRegistryWindow').getAge(Person_Birthday, this.getValue());
									}
								},
								'blur': function () {}
							}
						});
						// Формируем сведения: реабилитационный потенциал, этап,Дата начала этапа, дата завершения этапа, причина завершения
						var ReabPotentField = new Ext.form.FieldSet(
								{
									border: false,
									autoHeight: true,
									style: 'padding:0px;margin:0px;',
									labelWidth: 220,
									labelAlign: 'right',
									id: 'ReabPotentField',
									items: [
										new Ext.form.TextField({
											allowBlank: true,
											disabled: true,
											fieldLabel: 'Реабилитационный потенциал',
											id: 'ReabPotent_id',
											width: 40
										})
									]
								});
						var ReabStageField = new Ext.form.FieldSet(
								{
									border: false,
									autoHeight: true,
									style: 'padding:0px;margin:0px;',
									labelWidth: 130,
									labelAlign: 'right',
									id: 'ReabStageField',
									items: [
										new Ext.form.TextField({
											allowBlank: true,
											disabled: true,
											fieldLabel: 'Этап реабилитации',
											width: 25,
											id: 'ReabStage_id',
											style: 'text-align:center;',
										})
									]
								});
						var ReabDateStartField = new Ext.form.FieldSet(
								{
									border: false,
									autoHeight: true,
									style: 'padding:0px;margin:0px;',
									labelWidth: 90,
									labelAlign: 'right',
									id: 'ReabDateStartField',
									items: [
										new Ext.form.TextField({
											allowBlank: true,
											disabled: true,
											fieldLabel: 'Дата начала',
											width: 80,
											id: 'ReabDateStart_id',
										})
									]
								});
						var ReabDateFinishField = new Ext.form.FieldSet(
								{
									border: false,
									autoHeight: true,
									style: 'padding:0px;margin:0px;',
									labelWidth: 120,
									labelAlign: 'right',
									id: 'ReabDateFinishField',
									items: [
										new Ext.form.TextField({
											allowBlank: true,
											disabled: true,
											fieldLabel: 'Дата окончания',
											width: 80,
											id: 'ReabDateFinish_id',
										})
									]
								});
						var ReabOutCauseField = new Ext.form.FieldSet(
								{
									border: false,
									autoHeight: true,
									style: 'padding:0px;margin:0px;',
									labelWidth: 80,
									labelAlign: 'right',
									id: 'ReabOutCauseField',
									items: [
										new Ext.form.TextField({
											allowBlank: true,
											disabled: true,
											fieldLabel: 'Причина',
											width: 160,
											id: 'ReabOutCause_id',
										})
									]
								});
						//Создаем панель дату проведения анкетирования и включаем дату
						Ext.getCmp('informReab').add(
								new Ext.Panel({
									title: 'Дата проведения анкетирования/Реабилитационный потенциал',
									id: 'ReabQuestionPanel_Date',
									border: false,
									layout: 'column',
									bodyStyle: 'margin: 10px;',
									items: [

										{xtype: 'panel',
											frame: false,
											layout: 'column',
											width: Ext.getBody().getWidth() - 263,
											border: false,
											items: [
												{
													xtype: 'panel',
													region: 'west',
													border: false,
													layout: 'column',
													id: 'ReabAnketPanel_Date',
													items: [
														TextFieldDate
													]
												},
												{
													xtype: 'panel',
													border: false,
													hidden: true,
													region: 'ost',
													layout: 'column',
													id: 'panelReabInfo',
													width: Ext.getBody().getWidth() - 400,
													//style: "left:200px;width: 100px;",
													items: [
														ReabPotentField,
														ReabStageField,
														ReabDateStartField,
														ReabDateFinishField,
														ReabOutCauseField
													]
												}


											]
										}


									]
								})
						);
						Ext.getCmp('informReab').doLayout(); // Визуализация
						// Тянем шаблон + данные по пациенту на этот профиль
						Ext.Ajax.request({
							url: '/?c=Ufa_Reab_Register_User&m=CreateAnketa',
							params: {
								DirectType_id: Ext.getCmp('ufa_personReabRegistryWindow').DirectType_id,
								StageType_id: Ext.getCmp('ufa_personReabRegistryWindow').StageType_id,
								Person_id: Ext.getCmp('ufa_personReabRegistryWindow').Person_id

								//Может сразу и данные по персонажу
							},
							callback: function (options, success, response)
							{
								loadMask.hide();
								//console.log('success=',success);
								// Основная работа
								if (success === true)
								{
									var allObjAnketa = Ext.util.JSON.decode(response.responseText); // Для заполнения формы (возможно с ответами)
									//console.log('Для анкет=',allObjAnketa);
									// console.log('allObjAnketa1=',allObjAnketa.success);
									if (typeof allObjAnketa.success != 'undefined')
									{
										return;
									}
									//  Рисуем анкету и заполняем справочники
									//console.log('allObjAnketa=',allObjAnketa);
									form.drawAnketa(allObjAnketa);

									// return;

									if (allObjAnketa.headAnketa.length == 0 || allObjAnketa.bodyAnketa.length == 0)
									{
										// console.log('Останавливаемся - готовы к работе');
										Ext.getCmp('editReabDataButton').setDisabled(true);
										if (Ext.getCmp('ufa_personReabRegistryWindow').ARMType == 'spec_mz')
										{
											Ext.getCmp('addReabDataButton').setDisabled(true);
										} else
										{
											Ext.getCmp('addReabDataButton').setDisabled(false);
										}
										//Ext.getCmp('addReabDataButton').setDisabled(false);
										Ext.getCmp('deleteReabDataButton').setDisabled(true);
										Ext.getCmp('panelReabInfo').hide(); // Закрываем панель потенциала
										//Закрываем Просмотр дат анкет
										Ext.getCmp('ViewReabDataButton').setDisabled(true);
										Ext.getCmp('TextFieldDateReab').setValue(new Date());
										Ext.getCmp('calday').setValue(new Date());
										Ext.getCmp('prevButton').setDisabled(true);
										Ext.getCmp('nextButton').setDisabled(true);
									} else
									{
										// console.log('Грузим последние данные.');
										var obodyAnketa = allObjAnketa.bodyAnketa;
										//  console.log('obodyAnketa=',obodyAnketa);
										form.AnketaLoadData(obodyAnketa);

										Ext.getCmp('editReabDataButton').setDisabled(false);
										Ext.getCmp('deleteReabDataButton').setDisabled(true); //!!!!!!!!!!!!!!!!!!!
										//console.log('шапка=',allObjAnketa.headAnketa[0].ReabAnketa_Data);
										Ext.getCmp('TextFieldDateReab').setValue(allObjAnketa.headAnketa[0].ReabAnketa_Data.date.substr(0, 10));
										//Ext.getCmp('calday').setValue(allObjAnketa.headAnketa[0].ReabAnketa_Data.date.dateFormat('d.m.Y'));
										Ext.getCmp('calday').setValue(allObjAnketa.headAnketa[0].ReabAnketa_Data.date.substr(0, 10));
										form.HeadAnketa = allObjAnketa.headAnketa;
										//   console.log('rec11=');
										//Отработка периодов
										Ext.getCmp('calday').getPeriod();
										// console.log('rec121=');
										// Работа с панелью потенциала
										form.getReabPotentPanel(0);
										//Открываем Просмотр дат анкет
										Ext.getCmp('ViewReabDataButton').setDisabled(false);
									}
									//Запрет на изменение формы
									form.AnketaDisabled(true);
									//Блокировка даты
									Ext.getCmp('TextFieldDateReab').setDisabled(true);
								}
							}
						})
						//  console.log('rec444=');

					},
					/**
					 * Блокировка(разблокировка) полей анкеты
					 */
					AnketaDisabled: function (bPrizn)
					{
						var form = Ext.getCmp('ufa_personReabRegistryWindow');
						for (var k in form.TemplObject)
						{
							//console.log('Блокировка');
							if (form.TemplObject[k].Elem_Type == 'Grid1' || form.TemplObject[k].Elem_Type == 'Grid2' || form.TemplObject[k].Elem_Type == 'Grid4'
									|| form.TemplObject[k].Elem_Type == 'Grid8' || form.TemplObject[k].Elem_Type == 'RGrid1')
							{
								Ext.getCmp('ReabGrid_' + form.TemplObject[k].Parameter_id).ViewActions.action_add.setDisabled(bPrizn);
							}
							if (form.TemplObject[k].Elem_Type == 'Grid7')
							{
								if (bPrizn == false && form.isButtonEdit == true && form.isButtonAdd == false)
								{
									Ext.getCmp('ReabGrid_' + form.TemplObject[k].Parameter_id).ViewActions.action_refresh.setDisabled(false);
									Ext.getCmp('ReabGrid_' + form.TemplObject[k].Parameter_id).setDisabled(false);
								} else
								{
									Ext.getCmp('ReabGrid_' + form.TemplObject[k].Parameter_id).ViewActions.action_refresh.setDisabled(true);
									var nRecord = Ext.getCmp('ReabGrid_' + form.TemplObject[k].Parameter_id).getGrid().getStore().data.items.length;
									if (nRecord > 0)
									{
										Ext.getCmp('ReabGrid_' + form.TemplObject[k].Parameter_id).setDisabled(false);
									} else
									{
										Ext.getCmp('ReabGrid_' + form.TemplObject[k].Parameter_id).setDisabled(true);
									}
								}
							}
							// Для Реабилитационного диагноза по МКФ
							if (form.TemplObject[k].Elem_Type == 'Grid10')
							{
//								console.log('Блокировка');
//								console.log('bPrizn=',bPrizn);
//								console.log('ReabGrid_=','ReabGrid_' + form.TemplObject[k].Parameter_id);
								Ext.getCmp('ReabGrid_' + form.TemplObject[k].Parameter_id).ViewActions.action_refresh.setDisabled(bPrizn);

							}

							if (form.TemplObject[k].Elem_Type == 'RGrid2')
							{
								if (bPrizn == false && form.isButtonEdit == true && form.isButtonAdd == false)
								{
									Ext.getCmp('ReabGrid_' + form.TemplObject[k].Parameter_id).ViewActions.action_refresh.setDisabled(false);
									Ext.getCmp('ReabGrid_' + form.TemplObject[k].Parameter_id).setDisabled(false);
								} else
								{
									Ext.getCmp('ReabGrid_' + form.TemplObject[k].Parameter_id).ViewActions.action_refresh.setDisabled(true);
									var nRecord = Ext.getCmp('ReabGrid_' + form.TemplObject[k].Parameter_id).getGrid().getStore().data.items.length;
									if (nRecord > 0)
									{
										Ext.getCmp('ReabGrid_' + form.TemplObject[k].Parameter_id).setDisabled(false);
									} else
									{
										Ext.getCmp('ReabGrid_' + form.TemplObject[k].Parameter_id).setDisabled(true);
									}
								}
								Ext.getCmp(form.TemplObject[k].Elem_Type + '_' + form.TemplObject[k].Parameter_id + '_Panel').setDisabled(bPrizn);
							}

							//console.log('Блокировка2');
							if (form.TemplObject[k].Elem_Type == 'Grid3' || form.TemplObject[k].Elem_Type == 'Grid5')
							{
								Ext.getCmp('ReabGrid_' + form.TemplObject[k].Parameter_id).setDisabled(bPrizn);
							}


							if (form.TemplObject[k].Elem_Type == 'Combo' || form.TemplObject[k].Elem_Type == 'Combo2')
							{
								if (form.TemplObject[k].Global == 2)
								{
									//console.log('Блокировка3');

									Ext.getCmp('ComboGroup_' + form.TemplObject[k].Group_id).setDisabled(bPrizn);
								}
								Ext.getCmp('ReabComboAnk_' + form.TemplObject[k].Parameter_id).setDisabled(bPrizn);
								if (form.TemplObject[k].Elem_Type == 'Combo2')
								{
									Ext.getCmp('ReabComboAnk_' + form.TemplObject[k].Parameter_id + '_2').setDisabled(bPrizn);
								}
							}
							if (form.TemplObject[k].Elem_Type == 'Field2')
							{
								Ext.getCmp('ReabFieldAnk_' + form.TemplObject[k].Spr_Cod).setDisabled(bPrizn);
							}
						}
					},
					/**
					 * Прорисовка анкеты и заполнение параметров справочниками (Доработать Gridы)
					 */
					AnketaLoadData: function (obodyAnketa)
					{
						var form = Ext.getCmp('ufa_personReabRegistryWindow');

						for (var k in form.TemplObject)
						{
							//По МКФ
							if (form.TemplObject[k].Elem_Type == 'Grid10')
							{
								//Загрузка
								form.getReabDiagICF('ReabGrid_' + form.TemplObject[k].Parameter_id);
							}

							// console.log('Elem_Type = ', form.TemplObject[k].Elem_Type);
							if (form.TemplObject[k].Elem_Type == 'Grid1' || form.TemplObject[k].Elem_Type == 'Grid8')
							{
								//Обнуляеем
								var nRecord = Ext.getCmp('ReabGrid_' + form.TemplObject[k].Parameter_id).getGrid().getStore().data.items.length;
								if (nRecord > 0)
								{//Очистка GRIDa
									Ext.getCmp('ReabGrid_' + form.TemplObject[k].Parameter_id).getGrid().store.removeAll( );
									nRecord = 0;
								}

								if (obodyAnketa[form.TemplObject[k].Parameter_id].DataAnketa.length > 0)
								{
									//console.log('Заполняем GRID1-8');
									for (jj = 0; jj < obodyAnketa[form.TemplObject[k].Parameter_id].DataAnketa.length; jj++)
									{
										Ext.getCmp('ReabGrid_' + form.TemplObject[k].Parameter_id).getGrid().store.insert(jj, [new Ext.data.Record({
											Id: nRecord + 1,
											Diag_Code: obodyAnketa[form.TemplObject[k].Parameter_id].DataAnketa[jj].Diag_Code, //             diagData[0].Diag_Code,
											Diag_Name: obodyAnketa[form.TemplObject[k].Parameter_id].DataAnketa[jj].Diag_Name,
											Diag_id: obodyAnketa[form.TemplObject[k].Parameter_id].DataAnketa[jj].Diag_id
										})]);
									}
									Ext.getCmp('ReabGrid_' + form.TemplObject[k].Parameter_id).getGrid().getSelectionModel().selectRow(nRecord);
									Ext.getCmp('ReabGrid_' + form.TemplObject[k].Parameter_id).getGrid().getSelectionModel().deselectRow(nRecord);
								}
							}

							if (form.TemplObject[k].Elem_Type == 'Grid2' || form.TemplObject[k].Elem_Type == 'Grid7')
							{
								if (obodyAnketa[form.TemplObject[k].Parameter_id].DataAnketa.length > 0)
								{
									var nRecord = Ext.getCmp('ReabGrid_' + form.TemplObject[k].Parameter_id).getGrid().getStore().data.items.length;
									if (nRecord > 0)
									{//Очистка GRIDa
										Ext.getCmp('ReabGrid_' + form.TemplObject[k].Parameter_id).getGrid().store.removeAll( );
										nRecord = 0;
									}
									for (jj = 0; jj < obodyAnketa[form.TemplObject[k].Parameter_id].DataAnketa.length; jj++)
									{
										var cDate = obodyAnketa[form.TemplObject[k].Parameter_id].DataAnketa[jj].Travm_setDate;
										//console.log('cDate = ', cDate);
										cDate = cDate.substr(6, 4) + '-' + cDate.substr(3, 2) + '-' + cDate.substr(0, 2);
										//console.log('cDate1 = ', cDate);
										var dateDiag = new Date(cDate.replace(/(\d+)-(\d+)-(\d+)/, '$2/$3/$1'));

										Ext.getCmp('ReabGrid_' + form.TemplObject[k].Parameter_id).getGrid().store.insert(jj, [new Ext.data.Record({
											Id: nRecord + 1,
											Diag_Code: obodyAnketa[form.TemplObject[k].Parameter_id].DataAnketa[jj].Diag_Code, //             diagData[0].Diag_Code,
											Diag_Name: obodyAnketa[form.TemplObject[k].Parameter_id].DataAnketa[jj].Diag_Name,
											Diag_id: obodyAnketa[form.TemplObject[k].Parameter_id].DataAnketa[jj].Diag_id,
											SetDate: dateDiag
										})]);
									}
									Ext.getCmp('ReabGrid_' + form.TemplObject[k].Parameter_id).getGrid().getSelectionModel().selectRow(nRecord);
									Ext.getCmp('ReabGrid_' + form.TemplObject[k].Parameter_id).getGrid().getSelectionModel().deselectRow(nRecord);
									if (form.TemplObject[k].Elem_Type == 'Grid7')
									{
										Ext.getCmp('ReabGrid_' + form.TemplObject[k].Parameter_id).setDisabled(false);
									}
								} else
								{
									if (form.TemplObject[k].Elem_Type == 'Grid7')
									{
										Ext.getCmp('ReabGrid_' + form.TemplObject[k].Parameter_id).getGrid().store.removeAll( );
										Ext.getCmp('ReabGrid_' + form.TemplObject[k].Parameter_id).getGrid().tbar.dom.firstChild.firstChild.firstChild.firstChild.lastChild.firstChild.innerText = '0 / 0';
										Ext.getCmp('ReabGrid_' + form.TemplObject[k].Parameter_id).setDisabled(true);
									}
								}
							}

							if (form.TemplObject[k].Elem_Type == 'Grid4')
							{

								if (obodyAnketa[form.TemplObject[k].Parameter_id].DataAnketa.length > 0)
								{
									var nRecord = Ext.getCmp('ReabGrid_' + form.TemplObject[k].Parameter_id).getGrid().getStore().data.items.length;
									if (nRecord > 0)
									{//Очистка GRIDa
										Ext.getCmp('ReabGrid_' + form.TemplObject[k].Parameter_id).getGrid().store.removeAll( );
										nRecord = 0;
									}
									for (jj = 0; jj < obodyAnketa[form.TemplObject[k].Parameter_id].DataAnketa.length; jj++)
									{
										Ext.getCmp('ReabGrid_' + form.TemplObject[k].Parameter_id).getGrid().store.insert(jj, [new Ext.data.Record({
											Id: nRecord + 1,
											Diag_Code: obodyAnketa[form.TemplObject[k].Parameter_id].DataAnketa[jj].Diag_Code, //             diagData[0].Diag_Code,
											Diag_Name: obodyAnketa[form.TemplObject[k].Parameter_id].DataAnketa[jj].Diag_Name,
											Diag_id: obodyAnketa[form.TemplObject[k].Parameter_id].DataAnketa[jj].Diag_id,
											StageComp_id: obodyAnketa[form.TemplObject[k].Parameter_id].DataAnketa[jj].StageComp_id,
											StageComp_Name: obodyAnketa[form.TemplObject[k].Parameter_id].DataAnketa[jj].StageComp_Name,
											StageComp_Weight: obodyAnketa[form.TemplObject[k].Parameter_id].DataAnketa[jj].StageComp_Weight
										})]);
									}
									Ext.getCmp('ReabGrid_' + form.TemplObject[k].Parameter_id).getGrid().getSelectionModel().selectRow(nRecord);
									Ext.getCmp('ReabGrid_' + form.TemplObject[k].Parameter_id).getGrid().getSelectionModel().deselectRow(nRecord);
								}
							}

							if (form.TemplObject[k].Elem_Type == 'Grid3')
							{
								var aReqw = new Array();
								aReqw = obodyAnketa[form.TemplObject[k].Parameter_id].DataAnketa.split(';');
								var Old = Ext.getCmp('ReabGrid_' + form.TemplObject[k].Parameter_id).getGrid().getStore().data.items;
								if (Old.length > 0)
								{
									Ext.getCmp('ReabGrid_' + form.TemplObject[k].Parameter_id).getGrid().store.removeAll( );
								}
								for (jj = 0; jj < Old.length; jj++)
								{
									Ext.getCmp('ReabGrid_' + form.TemplObject[k].Parameter_id).getGrid().store.insert(jj, [new Ext.data.Record({
										ReabSpr_Elem_id: Old[jj].data.ReabSpr_Elem_id,
										selrow: aReqw[jj],
										ReabSpr_Elem_Name: Old[jj].data.SprName,
										ReabSpr_Elem_Weight: Old[jj].data.ReabSpr_Elem_Weight
									})]);
								}
							}


							if (form.TemplObject[k].Elem_Type == 'Combo')
							{
								//console.log('typeof =',typeof (obodyAnketa[form.TemplObject[k].Parameter_id]));
								if (typeof (obodyAnketa[form.TemplObject[k].Parameter_id]) == 'undefined')
								{
									// console.log('Нет объекта');
									var dd = "Параметр не определен";
								} else
								{
									var dd = obodyAnketa[form.TemplObject[k].Parameter_id].DataAnketa;
								}
								if (form.TemplObject[k].Global == 2)
								{
									// console.log('Будем делать');
									if (dd == 0)
									{
										//скрываем панель
										Ext.getCmp('ComboGroup_' + form.TemplObject[k].Group_id).setValue(2);
										Ext.getCmp('ComboGroup_' + form.TemplObject[k].Group_id).selectedIndex = 1;
										Ext.getCmp('ReabComboAnk_' + form.TemplObject[k].Parameter_id).selectedIndex = -1;
										Ext.getCmp('ReabComboAnk_' + form.TemplObject[k].Parameter_id).setValue('Введите параметр');
										Ext.getCmp('ReabComboAnk_' + form.TemplObject[k].Parameter_id).hide();
										Ext.getCmp('Quest_' + form.TemplObject[k].Parameter_id).hide();
									} else
									{
										Ext.getCmp('ComboGroup_' + form.TemplObject[k].Group_id).setValue(1);
										Ext.getCmp('ComboGroup_' + form.TemplObject[k].Group_id).selectedIndex = 0;
										if (dd == "Параметр не определен")
										{
											Ext.getCmp('ReabComboAnk_' + form.TemplObject[k].Parameter_id).selectedIndex = -1;
											Ext.getCmp('ReabComboAnk_' + form.TemplObject[k].Parameter_id).setValue(dd);
										} else
										{
											Ext.getCmp('ReabComboAnk_' + form.TemplObject[k].Parameter_id).setValue(dd);
											Ext.getCmp('ReabComboAnk_' + form.TemplObject[k].Parameter_id).selectedIndex = (dd - 1);
										}

										Ext.getCmp('ReabComboAnk_' + form.TemplObject[k].Parameter_id).show();
										Ext.getCmp('Quest_' + form.TemplObject[k].Parameter_id).show();
										//console.log('form.TemplObject[k]',form.TemplObject[k]);
									}
								} else
								{
									// console.log('делаем');
									Ext.getCmp('ReabComboAnk_' + form.TemplObject[k].Parameter_id).setValue(dd);
									Ext.getCmp('ReabComboAnk_' + form.TemplObject[k].Parameter_id).selectedIndex = (dd - 1);
								}
							}
							if (form.TemplObject[k].Elem_Type == 'Grid5')
							{
								var dd = obodyAnketa[form.TemplObject[k].Parameter_id].DataAnketa.split(';');
								//console.log('dd=', dd);
								var Old = Ext.getCmp('ReabGrid_' + form.TemplObject[k].Parameter_id).getGrid().getStore().data.items;
								if (Old.length > 0)
								{
									Ext.getCmp('ReabGrid_' + form.TemplObject[k].Parameter_id).getGrid().store.removeAll( );
								}
								for (jj = 0; jj < Old.length; jj++)
								{
									Ext.getCmp('ReabGrid_' + form.TemplObject[k].Parameter_id).getGrid().store.insert(jj, [new Ext.data.Record({
										ReabSpr_Elem_id: Old[jj].data.ReabSpr_Elem_id,
										selrow: dd[jj],
										ReabSpr_Elem_Name: Old[jj].data.ReabSpr_Elem_Name,
										ReabSpr_Elem_Weight: Old[jj].data.ReabSpr_Elem_Weight
									})]);
								}

								if (form.TemplObject[k].Global == 2)
								{
									if (Ext.getCmp('ComboGroup_' + form.TemplObject[k].Group_id).selectedIndex == -1 || Ext.getCmp('ComboGroup_' + form.TemplObject[k].Group_id).selectedIndex == 1)
									{
										//console.log('dd=',dd);
										Ext.getCmp('ReabGrid_' + form.TemplObject[k].Parameter_id).hide();
										Ext.getCmp('Quest_' + form.TemplObject[k].Parameter_id).hide();

									} else
									{
										Ext.getCmp('ReabGrid_' + form.TemplObject[k].Parameter_id).show();
										Ext.getCmp('Quest_' + form.TemplObject[k].Parameter_id).show();
									}
								}
							}

							if (form.TemplObject[k].Elem_Type == 'Combo2')
							{
								//console.log('начало ddтрудовая=');
								// console.log('obodyAnketa[form.TemplObject[k].Parameter_id]=',obodyAnketa[form.TemplObject[k].Parameter_id]);
								if (typeof (obodyAnketa[form.TemplObject[k].Parameter_id]) == 'undefined')
								{
									// console.log('Нет объекта');
									var dd = "Параметр не определен";
									Ext.getCmp('ReabComboAnk_' + form.TemplObject[k].Parameter_id).setValue(dd);
									Ext.getCmp('ReabComboAnk_' + form.TemplObject[k].Parameter_id).selectedIndex = -1;
									Ext.getCmp('ReabComboAnk_' + form.TemplObject[k].Parameter_id + '_2').hide();
								} else
								{
									var dd = obodyAnketa[form.TemplObject[k].Parameter_id].DataAnketa;

									//console.log('ddтрудовая=',dd);
									if (dd.indexOf('||') > 0)
									{
										dd = dd.replace(';', '');
										var dd1 = dd.substr(0, dd.indexOf('||'));
										Ext.getCmp('ReabComboAnk_' + form.TemplObject[k].Parameter_id).setValue(dd1);
										Ext.getCmp('ReabComboAnk_' + form.TemplObject[k].Parameter_id).selectedIndex = (dd1 - 1);
										//console.log('dd2=',dd.substr(dd.indexOf('||')+2));
										Ext.getCmp('ReabComboAnk_' + form.TemplObject[k].Parameter_id + '_2').show();
										dd1 = dd.substr(dd.indexOf('||') + 2);
										Ext.getCmp('ReabComboAnk_' + form.TemplObject[k].Parameter_id + '_2').setValue(dd1);
										Ext.getCmp('ReabComboAnk_' + form.TemplObject[k].Parameter_id + '_2').selectedIndex = (dd1 - 1);
									} else
									{
										//Одно значение
										var dd1 = dd.replace(';', '');
										Ext.getCmp('ReabComboAnk_' + form.TemplObject[k].Parameter_id).setValue(dd1);
										Ext.getCmp('ReabComboAnk_' + form.TemplObject[k].Parameter_id).selectedIndex = (dd1 - 1);
										Ext.getCmp('ReabComboAnk_' + form.TemplObject[k].Parameter_id + '_2').hide();
									}
								}
								// console.log('конец  dтрудовая=');
							}

							if (form.TemplObject[k].Elem_Type == 'Field1' || form.TemplObject[k].Elem_Type == 'Field2')
							{

								Ext.getCmp('ReabFieldAnk_' + form.TemplObject[k].Spr_Cod).setValue(obodyAnketa[form.TemplObject[k].Parameter_id].DataAnketa);
							}

							if (form.TemplObject[k].Elem_Type == 'RGrid1')
							{
								//console.log('obodyAnketa[form.TemplObject[k].Parameter_id].DataAnketa = ',obodyAnketa[form.TemplObject[k].Parameter_id].DataAnketa);
								// console.log('length = ',obodyAnketa[form.TemplObject[k].Parameter_id].DataAnketa.length);
								if (obodyAnketa[form.TemplObject[k].Parameter_id].DataAnketa.length > 0)
								{
									var nRecord = Ext.getCmp('ReabGrid_' + form.TemplObject[k].Parameter_id).getGrid().getStore().data.items.length;
									if (nRecord > 0)
									{//Очистка GRIDa
										Ext.getCmp('ReabGrid_' + form.TemplObject[k].Parameter_id).getGrid().store.removeAll( );
										nRecord = 0;
									}
									//Начинаем заполнять
									var aRecords = new Array();
									aRecords = obodyAnketa[form.TemplObject[k].Parameter_id].DataAnketa.split(';');
									console.log('aRecords = ', aRecords);

									for (jj = 0; jj < aRecords.length; jj++)
									{
										var mRecord = new Array();
										mRecord = aRecords[jj].split('||');

										Ext.getCmp('ReabGrid_' + form.TemplObject[k].Parameter_id).getGrid().store.insert(jj, [new Ext.data.Record({
											Id: nRecord + 1,
											Type_of_joint_id: mRecord[0],
											Type_of_joint: Ext.getCmp('Type_of_joint_id').getStore().data.items[mRecord[0] - 1].data.SprName,
											Side_id: mRecord[1],
											Side: Ext.getCmp('Side_id').getStore().data.items[mRecord[1] - 1].data.SprName,
											Motion_Id: mRecord[2],
											Motion: Ext.getCmp('Motion_Id').getStore().data.items[mRecord[2] - 1].data.SprName,
											Deformation_id: mRecord[3],
											Deformation: Ext.getCmp('Deformation_id').getStore().data.items[mRecord[3] - 1].data.SprName,
											JointContracture_id: mRecord[4],
											JointContracture: Ext.getCmp('JointContracture_id').getStore().data.items[mRecord[4] - 1].data.SprName,
											Intervention_id: mRecord[5],
											Intervention: Ext.getCmp('Intervention_id').getStore().data.items[mRecord[5] - 1].data.SprName,
											StageComp_Weight: mRecord[6]
										})]);
										Ext.getCmp('ReabGrid_' + form.TemplObject[k].Parameter_id).getGrid().getSelectionModel().selectRow(nRecord);
										Ext.getCmp('ReabGrid_' + form.TemplObject[k].Parameter_id).getGrid().getSelectionModel().deselectRow(nRecord);
									}
								} //
								else
								{
									Ext.getCmp('ReabGrid_' + form.TemplObject[k].Parameter_id).getGrid().store.removeAll( );
									Ext.getCmp('ReabGrid_' + form.TemplObject[k].Parameter_id).getGrid().tbar.dom.firstChild.firstChild.firstChild.firstChild.lastChild.firstChild.innerText = '0 / 0';
									Ext.getCmp('RGrid1_13_Panel').hide();
									//Закрыть панель
								}
							}

							if (form.TemplObject[k].Elem_Type == 'RGrid2')
							{
								//console.log('RGrid2=', obodyAnketa[form.TemplObject[k].Parameter_id].DataAnketa);
								if (obodyAnketa[form.TemplObject[k].Parameter_id].DataAnketa.length > 0)
								{
									//console.log('RGrid2=1');
									var nRecord = Ext.getCmp('ReabGrid_' + form.TemplObject[k].Parameter_id).getGrid().getStore().data.items.length;
									if (nRecord > 0)
									{//Очистка GRIDa
										Ext.getCmp('ReabGrid_' + form.TemplObject[k].Parameter_id).getGrid().store.removeAll( );
										nRecord = 0;
									}

									//console.log('RGrid2=2');
									for (jj = 0; jj < obodyAnketa[form.TemplObject[k].Parameter_id].DataAnketa.length; jj++)
									{
										// console.log('Anketa=',obodyAnketa[form.TemplObject[k].Parameter_id].DataAnketa[jj]);
										//  console.log('Anketa=',typeof (obodyAnketa[form.TemplObject[k].Parameter_id].DataAnketa[jj].Data_Combo));

										if (typeof (obodyAnketa[form.TemplObject[k].Parameter_id].DataAnketa[jj].Data_Combo) != 'undefined')
										{
											// Data_Combo
											var aCombo = new Array();
											aCombo = obodyAnketa[form.TemplObject[k].Parameter_id].DataAnketa[jj].Data_Combo.split('``');
											// console.log('aCombo=',aCombo);
											Ext.getCmp('HipPower_id').setValue(aCombo[0]);
											Ext.getCmp('HipPower_id').selectedIndex = (dd - 1);
											Ext.getCmp('HipStage_id').setValue(aCombo[1]);
											Ext.getCmp('HipStage_id').selectedIndex = (aCombo[1] - 1);
											Ext.getCmp('HipRisk_Id').setValue(aCombo[2]);
											Ext.getCmp('HipRisk_Id').selectedIndex = (aCombo[2] - 1);
											Ext.getCmp('RGrid2_10_Panel').show();
										} else
										{
											var cDate = obodyAnketa[form.TemplObject[k].Parameter_id].DataAnketa[jj].Travm_setDate;
											cDate = cDate.substr(6, 4) + '-' + cDate.substr(3, 2) + '-' + cDate.substr(0, 2);
											var dateDiag = new Date(cDate.replace(/(\d+)-(\d+)-(\d+)/, '$2/$3/$1'));

											Ext.getCmp('ReabGrid_' + form.TemplObject[k].Parameter_id).getGrid().store.insert(jj, [new Ext.data.Record({
												Id: nRecord + 1,
												Diag_Code: obodyAnketa[form.TemplObject[k].Parameter_id].DataAnketa[jj].Diag_Code, //             diagData[0].Diag_Code,
												Diag_Name: obodyAnketa[form.TemplObject[k].Parameter_id].DataAnketa[jj].Diag_Name,
												Diag_id: obodyAnketa[form.TemplObject[k].Parameter_id].DataAnketa[jj].Diag_id,
												SetDate: dateDiag
											})]);
										}
									}

									Ext.getCmp('ReabGrid_' + form.TemplObject[k].Parameter_id).getGrid().getSelectionModel().selectRow(nRecord);
									Ext.getCmp('ReabGrid_' + form.TemplObject[k].Parameter_id).getGrid().getSelectionModel().deselectRow(nRecord);
									Ext.getCmp('ReabGrid_' + form.TemplObject[k].Parameter_id).getGrid().getSelectionModel().deselectRow(nRecord);
									Ext.getCmp('ReabGrid_' + form.TemplObject[k].Parameter_id).setDisabled(false);
								} else
								{
									Ext.getCmp('ReabGrid_' + form.TemplObject[k].Parameter_id).setDisabled(true);
									Ext.getCmp('ReabGrid_' + form.TemplObject[k].Parameter_id).getGrid().store.removeAll( );
									Ext.getCmp('ReabGrid_' + form.TemplObject[k].Parameter_id).getGrid().tbar.dom.firstChild.firstChild.firstChild.firstChild.lastChild.firstChild.innerText = '0 / 0';
									Ext.getCmp('RGrid2_10_Panel').hide();
									//Закрыть панель
								}
							}

							// console.log('recorddddd=');
						}

					},
					/**
					 * Прорисовка анкеты и заполнение параметров справочниками (Доработать Gridы)
					 */
					drawAnketa: function (allObjAnketa)
					{
						var form = Ext.getCmp('ufa_personReabRegistryWindow');
						// Это группы
						//console.log('allObjAnketa.groups=', allObjAnketa.groups);
						for (var k in allObjAnketa.groups) {
							var field = form.createField(0, {
								data: {
									group: allObjAnketa.groups[k].group,
									id: allObjAnketa.groups[k].id
								}
							});
							Ext.getCmp('informReab').add(field);
						}

						// А теперь вовнуть панелей
						var QuestionPanel = ''; //Для формирования панелей н группах (если это нужно)
						var TemplGlobal = ''; //Для формирования общего combo на группу

						//console.log('Element==', allObjAnketa.Element);
						for (var k in allObjAnketa.Element) {
							if (allObjAnketa.Element[k].Elem_Type == 'Grid1' || allObjAnketa.Element[k].Elem_Type == 'Grid4')
							{
								var field = form.createField(1, {
									data: {
										id: allObjAnketa.Element[k].Parameter_id,
										Grid: allObjAnketa.Element[k].Elem_Type,
										Number: allObjAnketa.Element[k].Number
									}
								});
								Ext.getCmp('ReabgroupPanel_' + allObjAnketa.Element[k].Group_id).add(field);
							}
							if (allObjAnketa.Element[k].Elem_Type == 'Grid10')
							{
								var field = form.createField(15, {
									data: {
										id: allObjAnketa.Element[k].Parameter_id,
										Grid: allObjAnketa.Element[k].Elem_Type,
										Number: allObjAnketa.Element[k].Number
									}
								});
								Ext.getCmp('ReabgroupPanel_' + allObjAnketa.Element[k].Group_id).add(field);
							}
							//Отработка с Борисом
							if (allObjAnketa.Element[k].Elem_Type == 'RGrid1')
							{
								var field = form.createField(12, {
									data: {
										id: allObjAnketa.Element[k].Parameter_id,
										Grid: allObjAnketa.Element[k].Elem_Type,
										Number: allObjAnketa.Element[k].Number
									}
								});
								Ext.getCmp('ReabgroupPanel_' + allObjAnketa.Element[k].Group_id).add(field);

								field = form.createField(13, {
									data: {
										id: allObjAnketa.Element[k].Parameter_id,
										Grid: allObjAnketa.Element[k].Elem_Type,
										Spr: allObjAnketa.Spr
									}
								});
								Ext.getCmp('ReabgroupPanel_' + allObjAnketa.Element[k].Group_id).add(field);
								// Ext.getCmp(allObjAnketa.Element[k].Elem_Type  + '_'+ allObjAnketa.Element[k].Parameter_id + '_Panel').hide();
							}

							//Создаем Grid c шаблоном ввода МКБ с фильтром и датой приобретения травмы ответов(1)
							if (allObjAnketa.Element[k].Elem_Type == 'Grid2')
							{
								var field = form.createField(2, {
									data: {
										id: allObjAnketa.Element[k].Parameter_id
									}
								});
								Ext.getCmp('ReabgroupPanel_' + allObjAnketa.Element[k].Group_id).add(field);
							}

							if (allObjAnketa.Element[k].Elem_Type == 'Grid3')
							{
								var field = form.createField(6, {
									data: {
										Name: allObjAnketa.Element[k].Parameter_Name,
										Number: allObjAnketa.Element[k].Number,
										id: allObjAnketa.Element[k].Parameter_id,
										Spr_Cod: allObjAnketa.Element[k].Spr_Cod,
										Spr: allObjAnketa.Spr
									}
								});
								Ext.getCmp('ReabgroupPanel_' + allObjAnketa.Element[k].Group_id).add(field);
							}

							if (allObjAnketa.Element[k].Elem_Type == 'Combo' || allObjAnketa.Element[k].Elem_Type == 'Combo2' || allObjAnketa.Element[k].Elem_Type == 'Grid5' ||
									allObjAnketa.Element[k].Elem_Type == 'Field1' || allObjAnketa.Element[k].Elem_Type == 'Field2' || allObjAnketa.Element[k].Elem_Type == 'Field3' ||
									allObjAnketa.Element[k].Elem_Type == 'Field4' || allObjAnketa.Element[k].Elem_Type == 'Grid7' || allObjAnketa.Element[k].Elem_Type == 'Grid8' ||
									allObjAnketa.Element[k].Elem_Type == 'RGrid2')
							{
								//сначала QuestionPanel 1 раз
								// console.log('QuestionPanel=',QuestionPanel);
								if (allObjAnketa.Element[k].Group_id != QuestionPanel)
								{
									//console.log('Формируем QuestionPanel');
									QuestionPanel = allObjAnketa.Element[k].Group_id;
									//Создаем панель вопросов ответов(55)
									var field = form.createField(55, {
										data: {
											id: allObjAnketa.Element[k].Group_id
										}}
									);
									Ext.getCmp('ReabgroupPanel_' + allObjAnketa.Element[k].Group_id).add(field);
									// Ext.getCmp('groupPanel_'+ allObjAnketa.Element[k].Group_id).doLayout();
								}
								// Если есть признак общего combo, то его формируем
								// 'Combo' общее на группу (56)
								if (allObjAnketa.Element[k].Global == 2 && allObjAnketa.Element[k].Group_id + allObjAnketa.Element[k].Global != TemplGlobal)
								{
									TemplGlobal = allObjAnketa.Element[k].Group_id + allObjAnketa.Element[k].Global;
									var field = form.createField(56, {
										data: {
											id: allObjAnketa.Element[k].Group_id,
											element: allObjAnketa.Element
										}}
									);
									Ext.getCmp('QuestionPanel_' + allObjAnketa.Element[k].Group_id).add(field);

								}

								//Засовываем параметры в LABEL(3)
								var field = form.createField(3, {
											data: {
												id: allObjAnketa.Element[k].Parameter_id,
												Name: allObjAnketa.Element[k].Parameter_Name,
												Number: allObjAnketa.Element[k].Number
											}
										}
								);
								Ext.getCmp('QuestionPanel_' + allObjAnketa.Element[k].Group_id).add(field);

								//Засовываем справочники
								// 'Combo' (4)
								if (allObjAnketa.Element[k].Elem_Type == 'Combo')
								{
									var field = form.createField(4, {
										data: {
											id: allObjAnketa.Element[k].Parameter_id,
											Spr_Cod: allObjAnketa.Element[k].Spr_Cod,
											Spr: allObjAnketa.Spr
										}
									});
									Ext.getCmp('QuestionPanel_' + allObjAnketa.Element[k].Group_id).add(field);
								}
								// 'Combo2' (5)
								if (allObjAnketa.Element[k].Elem_Type == 'Combo2')
								{
									var field = form.createField(5, {
												data: {
													id: allObjAnketa.Element[k].Parameter_id,
													Spr_Cod: allObjAnketa.Element[k].Spr_Cod.substr(0, allObjAnketa.Element[k].Spr_Cod.indexOf(';')),
													Spr: allObjAnketa.Spr
												}
											}
									);

									Ext.getCmp('QuestionPanel_' + allObjAnketa.Element[k].Group_id).add(field);
								}
								if (allObjAnketa.Element[k].Elem_Type == 'Grid5')
								{
									var field = form.createField(6, {
												data: {
													Name: '',
													id: allObjAnketa.Element[k].Parameter_id,
													Spr_Cod: allObjAnketa.Element[k].Spr_Cod,
													Spr: allObjAnketa.Spr
												}
											}
									);
									Ext.getCmp('QuestionPanel_' + allObjAnketa.Element[k].Group_id).add(field);
								}

								if (allObjAnketa.Element[k].Elem_Type == 'Field1')
								{
									var field = form.createField(10, {
										data: {
											id: allObjAnketa.Element[k].Spr_Cod//'age'
										}
									});
									Ext.getCmp('QuestionPanel_' + allObjAnketa.Element[k].Group_id).add(field);
								}
//
								if (allObjAnketa.Element[k].Elem_Type == 'Field2')
								{
									var field = form.createField(11, {
										data: {
											id: allObjAnketa.Element[k].Spr_Cod //'height'
										}
									});
									Ext.getCmp('QuestionPanel_' + allObjAnketa.Element[k].Group_id).add(field);
								}

								if (allObjAnketa.Element[k].Elem_Type == 'Grid7')
								{
									var field = form.createField(7, {
												data: {
													id: allObjAnketa.Element[k].Parameter_id,
													Name: allObjAnketa.Element[k].Elem_Type,
													Spr_Cod: allObjAnketa.Element[k].Spr_Cod,
													TemplJoin: allObjAnketa.Element[k].TemplJoin
												}
											}
									);
									Ext.getCmp('QuestionPanel_' + allObjAnketa.Element[k].Group_id).add(field);
								}

								if (allObjAnketa.Element[k].Elem_Type == 'Grid8')
								{
									var field = form.createField(1, {
										data: {
											id: allObjAnketa.Element[k].Parameter_id,
											Grid: allObjAnketa.Element[k].Elem_Type,
											Number: allObjAnketa.Element[k].Number
										}
									});
									Ext.getCmp('QuestionPanel_' + allObjAnketa.Element[k].Group_id).add(field);
								}

								if (allObjAnketa.Element[k].Elem_Type == 'RGrid2')
								{
									var field = form.createField(7, {
										data: {
											id: allObjAnketa.Element[k].Parameter_id,
											Name: allObjAnketa.Element[k].Elem_Type,
											Spr_Cod: allObjAnketa.Element[k].Spr_Cod,
											TemplJoin: allObjAnketa.Element[k].TemplJoin
										}
									});
									Ext.getCmp('QuestionPanel_' + allObjAnketa.Element[k].Group_id).add(field);
									field = form.createField(14, {
										data: {
											id: allObjAnketa.Element[k].Parameter_id,
											Grid: allObjAnketa.Element[k].Elem_Type,
											Spr: allObjAnketa.Spr
										}
									});
									Ext.getCmp('QuestionPanel_' + allObjAnketa.Element[k].Group_id).add(field);
								}

								Ext.getCmp('QuestionPanel_' + allObjAnketa.Element[k].Group_id).doLayout();
							}
						} //Форма сформирована
						/*
						 // Загрузка справочников
						 //                           for(var k in allObjAnketa.Element){

						 //                               if(allObjAnketa.Element[k].Elem_Type == 'Grid5' )
						 //                               {
						 //                                     //console.log('ReabGrid_=',allObjAnketa.Element[k].Elem_Type);
						 //                                     //   console.log('ReabGrid_=',Ext.getCmp('ReabGrid_'+ allObjAnketa.Element[k].Parameter_id));
						 //                                   Ext.getCmp('ReabGrid_'+ allObjAnketa.Element[k].Parameter_id).ViewGridPanel.getStore().load(
						 //                                  { params:{
						 //                                   SprNumber: allObjAnketa.Element[k].Spr_Cod,
						 //                                   SprNumberGroup:1
						 //                                 }});
						 //                               }
						 //                           }
						 */
						Ext.getCmp('informReab').doLayout(); // Визуализация

						form.TemplObject = allObjAnketa.Element;

						if (form.Templ == 'travmReab1')
						{
							Ext.getCmp('RGrid1_13_Panel').hide();
						}

						if (form.DirectSysNick == 'cnsReab')
						{
							Ext.getCmp('RGrid2_10_Panel').hide();
						}

					},
					/**
					 * Метод динамического создания нужного типа field
					 * @format int - тип создаваемого объекта
					 * @param object - параметры создаваемого объекта
					 */
					createField: function (format, params) {
						var form = Ext.getCmp('ufa_personReabRegistryWindow');

						switch (parseInt(format)) {
							case 0:
								var GroupPanel = form.createFirstPanel('ReabgroupPanel_' + params.data.id, params.data.group);
								return GroupPanel;
								break;
							case 1:
								if (params.data.Grid == 'Grid1' || params.data.Grid == 'Grid8')
								{
									var bPrizn = true;
								} else {
									var bPrizn = false
								}

								if (params.data.Grid == 'Grid8')
								{
									var nWidth = 620;
								} else
								{
									var nWidth = 1240;
								}

								var Grid = new sw.Promed.ViewFrame(
										{
											actions: [
												{name: 'action_add', handler: function () {
													Ext.getCmp('ReabGrid_' + params.data.id).AddClinDiag();
												}.createDelegate(this)},
												{name: 'action_view', hidden: true},
												{name: 'action_edit', hidden: true},
												//{name: 'action_edit',disabled: true, handler: function() {  }.createDelegate(this)}, // Выход на списочную форму
												{name: 'action_delete', disabled: true, handler: function () {
													Ext.getCmp('ReabGrid_' + params.data.id).DelDiag();
												}.createDelegate(this)},
												{name: 'action_refresh', hidden: true},
												{name: 'action_print', hidden: true}
											],
											autoExpandColumn: 'autoexpand',
											autoExpandMin: 100,
											autoLoadData: false,
											id: 'ReabGrid_' + params.data.id,
											pageSize: 50,
											height: 110,
											width: nWidth,
											paging: false, // навигатор
											region: 'center',
											root: 'data',
											stringfields: [
												{name: 'Id', type: 'int', header: 'ID', key: true},
												{name: 'Diag_Code', type: 'string', header: lang['kod'], width: 80, sortable: bPrizn}, //1
												{name: 'Diag_Name', type: 'string', id: 'autoexpand', header: lang['naimenovanie'], width: 450, sortable: bPrizn},
												{name: 'Diag_id', type: 'int', header: '', hidden: true},
												{name: 'StageComp_id', type: 'int', header: 'Стадия_Id', hidden: true},
												{name: 'StageComp_Name', type: 'int', header: 'Стадия', hidden: bPrizn, width: 250, sortable: bPrizn},
												{name: 'StageComp_Weight', type: 'string', header: 'Стадия', hidden: true}
											],
											focusOnFirstLoad: false,
											toolbar: true,
											//totalProperty: 'totalCount',
											onBeforeLoadData: function () {
												//this.getButtonSearch().disable();
											}.createDelegate(this),
											onLoadData: function () {
												// alert('Хрень');
												//this.getButtonSearch().enable();
											}.createDelegate(this),
											onRowSelect: function (sm, index, record) {
												// alert('выбрали запись');
												// console.log('form.isButtonAdd=',form.isButtonAdd);
												if (form.isButtonEdit == true || form.isButtonAdd == true)
												{
													//console.log('form.isButtonAdd=',form.isButtonAdd);
													Ext.getCmp('ReabGrid_' + params.data.id).ViewActions.action_delete.setDisabled(false);
												}
											},
											//Удаление диагноза
											DelDiag: function ()
											{
												// alert('Удаляем');
												var record = Ext.getCmp('ReabGrid_' + params.data.id).getGrid().getSelectionModel().getSelected();
												Ext.getCmp('ReabGrid_' + params.data.id).getGrid().store.remove(record);
												var nRecord = Ext.getCmp('ReabGrid_' + params.data.id).getGrid().getStore().data.items.length;
												if (nRecord == 0)
												{
													Ext.getCmp('ReabGrid_' + params.data.id).getGrid().tbar.dom.firstChild.firstChild.firstChild.firstChild.lastChild.firstChild.innerText = '0 / 0';
													Ext.getCmp('ReabGrid_' + params.data.id).ViewActions.action_delete.setDisabled(true);
												}
												Ext.getCmp('ReabGrid_' + params.data.id).getGrid().getSelectionModel().selectRow(0);
												Ext.getCmp('ReabGrid_' + params.data.id).getGrid().getSelectionModel().deselectRow(0);
												Ext.getCmp('ReabGrid_' + params.data.id).ViewActions.action_delete.setDisabled(true);
											},
											//Добавление диагноза
											AddClinDiag: function ()
											{
												var Inparams = new Object();
												Inparams.Person_id = Ext.getCmp('ufa_personReabRegistryWindow').Person_id;
												//console.log('Grid=',params.data.Grid);
												if (params.data.Grid == 'Grid1')
												{
													Inparams.Filter = '';
													Inparams.InDate = 0;
													Inparams.InStage = 0;
												}
												if (params.data.Grid == 'Grid4')
												{
													Inparams.Filter = '';
													Inparams.InDate = 0;
													Inparams.InStage = 1;
												}
												//Inparams.Filter = '';
												//Inparams.InDate = 0;
												getWnd('ufa_ReabMkbDateSearchWindow').show({
													callback1: function (diagData) {
														// console.log('pdata=',diagData);
														var nRecord = Ext.getCmp('ReabGrid_' + params.data.id).getGrid().getStore().data.items.length;
														Ext.getCmp('ReabGrid_' + params.data.id).getGrid().store.insert(nRecord, [new Ext.data.Record({
															Id: nRecord + 1,
															Diag_Code: diagData[0].Diag_Code,
															Diag_Name: diagData[0].Diag_Name,
															Diag_id: diagData[0].Diag_id,
															StageComp_id: diagData[0].ReabSpr_Elem_id,
															StageComp_Name: diagData[0].SprName,
															StageComp_Weight: diagData[0].ReabSpr_Elem_Weight
														})]);
														Ext.getCmp('ReabGrid_' + params.data.id).getGrid().getSelectionModel().selectRow(nRecord);
														Ext.getCmp('ReabGrid_' + params.data.id).getGrid().getSelectionModel().deselectRow(nRecord);
														Ext.getCmp('ReabGrid_' + params.data.id).ViewActions.action_delete.setDisabled(true);
													},
													Inparams: Inparams
												});
											}
										});
								return Grid;
								break;
							case 2:
								var Grid = new sw.Promed.ViewFrame(
										{
											actions: [
												{name: 'action_add', handler: function () {
													Ext.getCmp('ReabGrid_' + params.data.id).AddClinDiag();
												}.createDelegate(this)},
												{name: 'action_view', hidden: true},
												// {name: 'action_edit',disabled: true, handler: function() {  }.createDelegate(this)}, // Выход на списочную форму
												{name: 'action_edit', hidden: true},
												{name: 'action_delete', disabled: true, handler: function () {
													Ext.getCmp('ReabGrid_' + params.data.id).DelDiag();
												}.createDelegate(this)},
												{name: 'action_refresh', hidden: true},
												{name: 'action_print', hidden: true}
											],
											autoExpandColumn: 'autoexpand',
											autoExpandMin: 100,
											autoLoadData: false,
											id: 'ReabGrid_' + params.data.id,
											object: 'ReabGrid_' + params.data.id,
											pageSize: 50,
											height: 110,
											paging: false, // навигатор
											region: 'center',
											root: 'data',
											stringfields: [
												{name: 'Id', type: 'int', header: 'ID', key: true},
												//{name: 'Travm_setDate', type: 'string',  header: lang['data_ustanovki'], width: 90},
												{name: 'SetDate', type: 'date', format: 'd.m.Y', header: lang['data'], width: 90},
												{name: 'Diag_Code', type: 'string', header: lang['kod'], width: 80}, //1
												{name: 'Diag_Name', type: 'string', header: lang['naimenovanie'], width: 350},
												{name: 'Diag_id', type: 'int', id: 'autoexpand', header: '', hidden: true}
											],
											focusOnFirstLoad: false,
											toolbar: true,
											//totalProperty: 'totalCount',
											onBeforeLoadData: function () {
												//this.getButtonSearch().disable();
											}.createDelegate(this),
											onLoadData: function () {
												alert('Хрень');
												//this.getButtonSearch().enable();
											}.createDelegate(this),
											onRowSelect: function (sm, index, record) {
												// console.log('form.isButtonAdd=',form.isButtonAdd);
												if (form.isButtonEdit == true || form.isButtonAdd == true)
												{
													console.log('form.isButtonAdd=', form.isButtonAdd);
													Ext.getCmp('ReabGrid_' + params.data.id).ViewActions.action_delete.setDisabled(false);
												}
											},
											//Удаление диагноза
											DelDiag: function ()
											{
												// alert('Удаляем');
												var record = Ext.getCmp('ReabGrid_' + params.data.id).getGrid().getSelectionModel().getSelected();
												Ext.getCmp('ReabGrid_' + params.data.id).getGrid().store.remove(record);
												var nRecord = Ext.getCmp('ReabGrid_' + params.data.id).getGrid().getStore().data.items.length;
												if (nRecord == 0)
												{
													Ext.getCmp('ReabGrid_' + params.data.id).getGrid().tbar.dom.firstChild.firstChild.firstChild.firstChild.lastChild.firstChild.innerText = '0 / 0';
													Ext.getCmp('ReabGrid_' + params.data.id).ViewActions.action_delete.setDisabled(true);
												}
												Ext.getCmp('ReabGrid_' + params.data.id).getGrid().getSelectionModel().selectRow(0);
												Ext.getCmp('ReabGrid_' + params.data.id).getGrid().getSelectionModel().deselectRow(0);
												Ext.getCmp('ReabGrid_' + params.data.id).ViewActions.action_delete.setDisabled(true);
											},
											//Добавление диагноза
											AddClinDiag: function ()
											{
												console.log('Запускаем диагноз1');
												var Inparams = new Object();
												Inparams.Person_id = Ext.getCmp('ufa_personReabRegistryWindow').Person_id;
												Inparams.Filter = 'S;T';
												Inparams.InDate = 1;
												Inparams.InStage = 0;
												getWnd('ufa_ReabMkbDateSearchWindow').show({
															callback1: function (diagData) {

																//console.log('pdata=',diagData[0].Travm_setDate);
																var cDateYear = diagData[0].Travm_setDate.getFullYear();// + '-' +
																var nDateMonth = diagData[0].Travm_setDate.getMonth() + 1;
																var cDateMonth = '';
																if (nDateMonth < 10)
																{
																	cDateMonth = '0' + nDateMonth;
																} else
																{
																	cDateMonth = nDateMonth;
																}
																var nDateDay = diagData[0].Travm_setDate.getDate();
																var cDateDay = '';
																if (nDateDay < 10)
																{
																	cDateDay = '0' + nDateDay;
																} else
																{
																	cDateDay = nDateDay;
																}
																var cDate = cDateYear + '-' + cDateMonth + '-' + cDateDay;
																console.log('cDate=', cDate);



																var nRecord = Ext.getCmp('ReabGrid_' + params.data.id).getGrid().getStore().data.items.length;
																console.log('Diag_Code=', diagData[0].Diag_Code);
																Ext.getCmp('ReabGrid_' + params.data.id).getGrid().store.insert(nRecord, [new Ext.data.Record({
																	Id: nRecord + 1,
																	Diag_Code: diagData[0].Diag_Code,
																	Diag_Name: diagData[0].Diag_Name,
																	Diag_id: diagData[0].Diag_id,
																	// Travm_setDate: diagData[0].Travm_setDate
																	SetDate: cDate
																})]);
																Ext.getCmp('ReabGrid_' + params.data.id).getGrid().getSelectionModel().selectRow(nRecord);
																Ext.getCmp('ReabGrid_' + params.data.id).getGrid().getSelectionModel().deselectRow(nRecord);
																Ext.getCmp('ReabGrid_' + params.data.id).ViewActions.action_delete.setDisabled(true);
															},
															Inparams: Inparams
														}

												);
											}
										});
								return Grid;
								break;
							case 3:
								var Quest = new Ext.form.Label({
											text: params.data.Number + '. ' + params.data.Name, //'5. Нарушение ритма',
											height: 10,
											id: 'Quest_' + params.data.id,
											style: 'font-style:italic;font-size:1.2em;color:blue; '
										}
								);
								return Quest;
								break;
							case 4:
								//Готовим данные для Combo
								var aSprCombo = new Array();
								// console.log('params = ', params);
								for (var k in params.data.Spr)
								{
									if (params.data.Spr[k].ReabSpr_Cod == params.data.Spr_Cod)
									{
										aSprCombo.push([params.data.Spr[k].ReabSpr_Elem_id, params.data.Spr[k].SprName, params.data.Spr[k].ReabSpr_Elem_Weight]);
									}
								}
								// console.log('aSprCombo = ', aSprCombo);

								var Combo = new Ext.form.ComboBox(
										{
											allowBlank: false,
											id: 'ReabComboAnk_' + params.data.id,
											hideLabel: true,
											hideTrigger: true, // Chek
											//style: 'position:relative; top: 15px ',
											//anchor: '100%',
											mode: 'local',
											editable: false,
											triggerAction: 'all',
											displayField: 'SprName',
											valueField: 'ReabSpr_Elem_id',
											style: 'width: 550px; border:none;font-size:1.1em',
											//autoWidth: true,
											width: 'auto',
											tabIndex: -1,
											emptyText: 'Введите параметр',
											listWidth: 'auto',
											hiddenName: 'ReabSpr_Elem_id',
											autoscroll: false,
											xtype: 'combo',
											store: new Ext.data.SimpleStore({
												fields: [
													{name: 'ReabSpr_Elem_id', type: 'int'},
													{name: 'SprName', type: 'string'},
													{name: 'ReabSpr_Elem_Weight', type: 'string'}
												],
												data: aSprCombo
											}),

											tpl: '<tpl for="."><div class="x-combo-list-item">' +
											'{SprName} ' + '&nbsp;' +
											'</div></tpl>',
											listeners: {
												specialkey: function (field, e) {
													console.log('FIELD', field)
													if (e.getKey() == e.ENTER) {
														//Ext.getCmp('getMorbusType_id').handler();
													}
												}
											}
										});
								return Combo;
								break;
							case 5:
								//Готовим данные для Combo2
								var aSprCombo1 = new Array();
								var aSprCombo2 = new Array();
								for (var k in params.data.Spr)
								{
									if (params.data.Spr[k].ReabSpr_Cod == params.data.Spr_Cod && params.data.Spr[k].ReabSpr_Group == 1)
									{
										aSprCombo1.push([params.data.Spr[k].ReabSpr_Elem_id, params.data.Spr[k].SprName, params.data.Spr[k].ReabSpr_Elem_Weight, params.data.Spr[k].ReabSpr_Level]);
									}
									if (params.data.Spr[k].ReabSpr_Cod == params.data.Spr_Cod && params.data.Spr[k].ReabSpr_Group == 2)
									{
										aSprCombo2.push([params.data.Spr[k].ReabSpr_Elem_id, params.data.Spr[k].SprName, params.data.Spr[k].ReabSpr_Elem_Weight, params.data.Spr[k].ReabSpr_Level]);
									}
								}

								var Combo2 = new Ext.Panel({
									layout: 'form',
									border: false,
									//autoWidth: true,
									width: 'auto',
									frame: false, //Отражение панели
									items: [
										{
											layout: 'form',
											border: false,
											labelWidth: 70,
											labelAlign: 'left',
											items: [
												{
													allowBlank: false,
													anyparam: 'anyparam',
													id: 'ReabComboAnk_' + params.data.id,
													listWidth: 'auto',
													emptyText: 'Введите параметр',
													hideTrigger: true,
													hideLabel: true,
													style: 'width: 600px; border:none;font-size:1.1em',
													mode: 'local',
													store: new Ext.data.SimpleStore({
														fields: [
															{name: 'ReabSpr_Elem_id', type: 'int'},
															{name: 'SprName', type: 'string'},
															{name: 'ReabSpr_Elem_Weight', type: 'string'},
															{name: 'ReabSpr_Level', type: 'string'}
														],
														data: aSprCombo1
													}),
													editable: false,
													triggerAction: 'all',
													displayField: 'SprName',
													valueField: 'ReabSpr_Elem_id',
													// width: 150,
													autoscroll: false,
													xtype: 'combo',
													listeners: {
														specialkey: function (field, e) {
															console.log('FIELD', field)
															if (e.getKey() == e.ENTER) {
																//Ext.getCmp('getMorbusType_id').handler();
															}
														},

														select: function (combo, record, index) {
															console.log('record.ReabSpr_Elem_Weight=', record.data.ReabSpr_Level);
															console.log('SprNumber=', record.data.ReabSpr_Level.trim().substr(0, 1));
															console.log('SprNumberGroup=', record.data.ReabSpr_Level.trim().substr(2, 1));
															if (record.data.ReabSpr_Level != '')
															{
																//Запускаем 2 combo
																Ext.getCmp('ReabComboAnk_' + params.data.id + '_2').show();

//                                            Ext.getCmp('ReabComboAnk_' + params.data.id +'_2').getStore().load(
//                                                    {
//                                                        params:{
////                                                SprNumber: 2,
////                                                SprNumberGroup:2  var cCodSpr =  allObjAnketa.Element[k].Spr_Cod.substr(0,allObjAnketa.Element[k].Spr_Cod.indexOf(';'));
//                                                SprNumber: params.data.Spr_Cod.substr(0,params.data.Spr_Cod.indexOf(';')),
//                                                SprNumberGroup: params.data.Spr_Cod.substr(params.data.Spr_Cod.indexOf(';')+1,1)  //Временно!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
//                                                      },
//                                                    });
																Ext.getCmp('ReabComboAnk_' + params.data.id + '_2').selectedIndex = -1;
																Ext.getCmp('ReabComboAnk_' + params.data.id + '_2').setValue('Введите параметр');
															} else
															{
																Ext.getCmp('ReabComboAnk_' + params.data.id + '_2').hide();

															}
														}
													}

												}
											]
										},
										{
											layout: 'form',
											border: false,
											//style: 'position:relative;  left:40px ',
											// labelWidth: 130,
											//width: 250,
											labelAlign: 'left',
											items: [
												{
													xtype: 'combo',
													//labelAlign: 'right',
													disabled: false,
													id: 'ReabComboAnk_' + params.data.id + '_2',
													emptyText: 'Введите параметр',
													mode: 'local',
													style: 'width: 600px; border:none;font-size:1.1em',
													//width: 50,
													hidden: true,
													hideTrigger: true,
													hideLabel: true,
													listWidth: 'auto',
													triggerAction: 'all',
													store: new Ext.data.SimpleStore({
														fields: [
															{name: 'ReabSpr_Elem_id', type: 'int'},
															{name: 'SprName', type: 'string'},
															{name: 'ReabSpr_Elem_Weight', type: 'string'},
															{name: 'ReabSpr_Level', type: 'string'}
														],
														data: aSprCombo2
													}),
													editable: false,
													triggerAction: 'all',
													displayField: 'SprName',
													valueField: 'ReabSpr_Elem_id',
													// width: 150,
													autoscroll: false
												}
											]
										}
									]
								})
								// console.log('Combo2==', Combo2);
								return Combo2;
								break;
							case 6:
								if (params.data.Name == '')
								{
									var cTitle = '';
								} else {
									var cTitle = params.data.Number + '. ' + params.data.Name;
								}
								if (params.data.Spr_Cod == 61)
								{
									var cField1 = 'Применение';
									var cField2 = 'Препарат';
								} else
								{
									var cField1 = lang['vyibor'];
									var cField2 = lang['parametr'];
								}

								var Grid = new sw.Promed.ViewFrame(
										{
											actions: [],
											autoExpandColumn: 'autoexpand',
											autoExpandMin: 100,
											title: cTitle,
											autoLoadData: false,
											id: 'ReabGrid_' + params.data.id,
											object: 'ReabGrid_' + params.data.id,
											autoLoad: false,
											pageSize: 50,
											height: 150,
											paging: false, // навигатор
											// autoHeight: true, // не работает
											// height: 'auto',
											region: 'center',
											root: 'data', //Обертка ответа(формируется в контроллере)
											stringfields: [
												{name: 'ReabSpr_Elem_id', type: 'int', header: 'ID', key: true},
												{name: 'selrow', type: 'string', header: cField1, width: 80, sortable: false},
												{name: 'ReabSpr_Elem_Name', type: 'string', header: cField2, width: 350, sortable: false},
												{name: 'ReabSpr_Elem_Weight', type: 'string', width: 350, hidden: true}

											],
											focusOnFirstLoad: false,
											toolbar: false,
											onBeforeLoadData: function () {
												//this.getButtonSearch().disable();
											}.createDelegate(this),
											onLoadData: function () {
												//   alert('Загрузка');
												//this.getButtonSearch().enable();
											}.createDelegate(this),
											onRowSelect: function (sm, index, record) {
												// alert('выбрали запись');
												// Ext.getCmp('ReabCns1ClinicGrid').ViewActions.action_delete.setDisabled(false);
											}

										});
								Ext.getCmp('ReabGrid_' + params.data.id).getGrid().on(
										'cellclick',
										function (grid, rowNum, columnIndex, e) {
											//  console.log('rowNum=',rowNum);
											// console.log('columnIndex=',columnIndex);
											if (columnIndex != 1)
												return;
											var record = grid.getStore().getAt(rowNum);  // Get the Record
											console.log('record=', record);
											if (record.get('selrow') == 'Да')
												record.set('selrow', 'Нет');
											else
												record.set('selrow', 'Да');
											//      console.log('record12=',record);
										});

								//Попробуем заполнить GRID
								var aSprCombo = new Array();
								//console.log('params = ', params.data.Spr);
								for (var k in params.data.Spr)
								{
									if (params.data.Spr[k].ReabSpr_Cod == params.data.Spr_Cod)
									{
										aSprCombo.push([params.data.Spr[k].ReabSpr_Elem_id, params.data.Spr[k].SprName, params.data.Spr[k].ReabSpr_Elem_Weight]);
									}
								}
								//console.log('aSprCombo=', aSprCombo);
								var nRecord = Ext.getCmp('ReabGrid_' + params.data.id).getGrid().getStore().data.items.length;

								if (nRecord > 0)
								{//Очистка GRIDa
									Ext.getCmp('ReabGrid_' + params.data.id).getGrid().store.removeAll( );
								}

								for (jj = 0; jj < aSprCombo.length; jj++)
								{
									Ext.getCmp('ReabGrid_' + params.data.id).getGrid().store.insert(jj, [new Ext.data.Record({
										ReabSpr_Elem_id: aSprCombo[jj][0],
										selrow: 'Нет',
										ReabSpr_Elem_Name: aSprCombo[jj][1],
										ReabSpr_Elem_Weight: aSprCombo[jj][2]
									})]);
								}
								return Grid;
								break;
							case 7 :
								var Grid = new sw.Promed.ViewFrame(
										{
											actions: [
												//  {name: 'action_add', handler: function() { Ext.getCmp('ReabGrid_'+params.data.id).AddClinDiag(); }.createDelegate(this)},
												{name: 'action_add', hidden: true},
												{name: 'action_view', hidden: true},
												{name: 'action_edit', hidden: true},
												{name: 'action_delete', hidden: true},
												{name: 'action_refresh', hidden: false, handler: function () {
													Ext.getCmp('ReabGrid_' + params.data.id).RefreshDiag();
												}.createDelegate(this)},
												{name: 'action_print', hidden: true}
											],
											autoExpandColumn: 'autoexpand',
											autoExpandMin: 100,
											autoLoadData: false,
											disabled: true,
											id: 'ReabGrid_' + params.data.id,
											pageSize: 50,
											height: 150,
											// width: 1240,
											paging: false, // навигатор
											region: 'center',
											root: 'data',
											stringfields: [
												{name: 'Id', type: 'int', header: 'ID', key: true},
												{name: 'SetDate', type: 'date', format: 'd.m.Y', header: lang['data'], width: 90},
												{name: 'Diag_Code', type: 'string', header: lang['kod'], width: 80, sortable: true}, //1
												{name: 'Diag_Name', type: 'string', id: 'autoexpand', header: lang['naimenovanie'], width: 450, sortable: true}
											],
											focusOnFirstLoad: false,
											toolbar: true,
											//totalProperty: 'totalCount',
											onBeforeLoadData: function () {
												//this.getButtonSearch().disable();
											}.createDelegate(this),
											onLoadData: function () {
												// alert('Хрень');
												//this.getButtonSearch().enable();
											}.createDelegate(this),
											//Обновление диагнозов
											RefreshDiag: function ()
											{
												var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет поиск..."});
												loadMask.show();

												form.getDiagPerson(params.data.id, params.data.Spr_Cod, params.data.Name, params.data.TemplJoin);
												loadMask.hide();
												// alert('Прювет!!')
											}
										});
								return Grid;
								break;
							case 10 :
								var Field = new Ext.form.TextField({
									allowBlank: false,
									hideLabel: true,
									disabled: true,
									id: 'ReabFieldAnk_' + params.data.id,
									style: 'text-align:center;font-weight:bold;',
									width: 120
								});
								return Field;
								break;
							case 11 :
								//Параметры ограничения
								var nMinLength = 0;
								var nMaxLength = 0;
								var nMaxValue = 0;
								var nMinValue = 0;
								if (params.data.id == 'waist')
								{
									nMinLength = 2;
									nMaxLength = 5;
									nMaxValue = 400;
									nMinValue = 20;
								}
								if (params.data.id == 'height')
								{
									nMinLength = 2;
									nMaxLength = 3;
									nMaxValue = 300;
									nMinValue = 45;
								}
								if (params.data.id == 'weight')
								{
									nMinLength = 2;
									nMaxLength = 5;
									nMaxValue = 400;
									nMinValue = 30;
								}
								if (params.data.id == 'hyperglycaemia')
								{
									nMinLength = 1;
									nMaxLength = 5;
									nMaxValue = 100;
									nMinValue = 0;
								}

								//hyperglycaemia
								var Field = new Ext.form.NumberField({
									allowBlank: true,
									hideLabel: true,
									disabled: false,
									invalidText: "erererer",
									minLength: nMinLength,
									maxLength: nMaxLength,
									maxValue: nMaxValue,
									minValue: nMinValue,
									id: 'ReabFieldAnk_' + params.data.id,
									style: 'text-align:center;font-weight:bold;color: black;',
									//  plugins:[ new Ext.ux.InputTextMask('999', true) ],
									width: 120,
									listeners: {
										'change': function () {
											Ext.getCmp('ufa_personReabRegistryWindow').getbodyMassIndex();
										},
//                         'valid' : function(  msg ){
//                            alert("eeeeeeee");
//                         }
									}

								});
								return Field;
								break;
							case 12 :
								var Grid = new Ext.Panel({
									height: 211,
									layout: 'border',
									border: true,
									style: 'border-top: 1px solid #99bbe8; border-left: 1px solid #99bbe8; border-bottom: 1px solid #ffffff;border-right: 1px solid #ffffff; padding: 0px; ',
									items: [
										new sw.Promed.ViewFrame(
												{
													actions: [
														{name: 'action_add', handler: function () {
															Ext.getCmp('ReabGrid_' + params.data.id).AddClinDiag();
														}.createDelegate(this)},
														{name: 'action_view', hidden: true},
														{name: 'action_edit', hidden: true},
														// {name: 'action_edit',disabled: true, handler: function() {  }.createDelegate(this)}, // Выход на списочную форму
														{name: 'action_delete', disabled: true, handler: function () {
															Ext.getCmp('ReabGrid_' + params.data.id).DelDiag();
														}.createDelegate(this)},
														{name: 'action_refresh', hidden: true},
														{name: 'action_print', hidden: true}
													],
													autoExpandColumn: 'autoexpand',
													autoExpandMin: 100,
													autoLoadData: false,
													id: 'ReabGrid_' + params.data.id,
													pageSize: 50,
													height: 110,
													width: 1240,
													paging: false, // навигатор
													region: 'center',
													root: 'data',
													stringfields: [
														{name: 'Id', type: 'int', header: 'ID', key: true},
														{name: 'Type_of_joint_id', type: 'string', width: 100, hidden: true}, //1
														{name: 'Type_of_joint', type: 'string', header: 'Вид сустава', width: 100},
														{name: 'Side_id', type: 'string', width: 100, hidden: true}, //2
														{name: 'Side', type: 'string', header: 'Сторона', align: 'center', width: 100},
														{name: 'Motion_Id', type: 'string', width: 100, hidden: true}, //3
														{name: 'Motion', type: 'string', header: 'Движения <br>в суставе', align: 'center', width: 100},
														{name: 'Deformation_id', type: 'string', width: 100, hidden: true}, //4
														{name: 'Deformation', type: 'string', header: 'Деформация <br> сустава', align: 'center', width: 100},
														{name: 'JointContracture_id', type: 'string', width: 100, hidden: true}, //5
														{name: 'JointContracture', type: 'string', header: 'Наличие <br>контрактуры<br> сустава', align: 'center', width: 100},
														{name: 'Intervention_id', type: 'string', width: 100, hidden: true}, //5
														{name: 'Intervention', type: 'string', header: 'Наличие<br> оперативного<br> вмешательства', align: 'center', width: 100},
														{name: 'StageComp_Weight', type: 'string', header: 'Потенциал', align: 'center', hidden: false}

													],
													totalProperty: 'totalCount',
													focusOnFirstLoad: false,
													toolbar: true,
													onBeforeLoadData: function () {
														//this.getButtonSearch().disable();
													}.createDelegate(this),
													onLoadData: function () {
														// alert('Хрень');
														//this.getButtonSearch().enable();
													}.createDelegate(this),
													onRowSelect: function (sm, index, record) {
														if (form.isButtonEdit == true || form.isButtonAdd == true)
														{
															//console.log('form.isButtonAdd=',form.isButtonAdd);
															Ext.getCmp('ReabGrid_' + params.data.id).ViewActions.action_delete.setDisabled(false);
														}
														Ext.getCmp('ReabGrid_' + params.data.id).ViewActions.action_add.setDisabled(true);
													},
													//Удаление диагноза
													DelDiag: function ()
													{
														// alert('Удаляем');
														var record = Ext.getCmp('ReabGrid_' + params.data.id).getGrid().getSelectionModel().getSelected();
														Ext.getCmp('ReabGrid_' + params.data.id).getGrid().store.remove(record);
														var nRecord = Ext.getCmp('ReabGrid_' + params.data.id).getGrid().getStore().data.items.length;
														if (nRecord == 0)
														{
															Ext.getCmp('ReabGrid_' + params.data.id).getGrid().tbar.dom.firstChild.firstChild.firstChild.firstChild.lastChild.firstChild.innerText = '0 / 0';
															Ext.getCmp('ReabGrid_' + params.data.id).ViewActions.action_delete.setDisabled(true);
														}
														Ext.getCmp('ReabGrid_' + params.data.id).getGrid().getSelectionModel().selectRow(0);
														Ext.getCmp('ReabGrid_' + params.data.id).getGrid().getSelectionModel().deselectRow(0);
														Ext.getCmp('ReabGrid_' + params.data.id).ViewActions.action_delete.setDisabled(true);
														Ext.getCmp('ReabGrid_' + params.data.id).ViewActions.action_add.setDisabled(false);
													},
													//Добавление диагноза
													AddClinDiag: function ()
													{
														Ext.getCmp(params.data.Grid + '_' + params.data.id + '_Panel').show();
														Ext.getCmp(params.data.Grid + '_' + params.data.id + 'ButtonSave').focus();
														Ext.getCmp('ReabGrid_' + params.data.id).ViewActions.action_add.setDisabled(true);
													}
												})

									]
								});
								return Grid;
								break;
							case 13 :
								//Готовим данные для Combo
								var aSprComboJoint = new Array();
								var aSprComboSide = new Array();
								var aSprComboMotion = new Array();
								var aSprComboJointContracture = new Array();
								var aSprComboDeformation = new Array();
								var aSprComboIntervention = new Array();
								for (var k in params.data.Spr)
								{
									if (params.data.Spr[k].ReabSpr_Cod == 46)
									{
										aSprComboJoint.push([params.data.Spr[k].ReabSpr_Elem_id, params.data.Spr[k].SprName, params.data.Spr[k].ReabSpr_Elem_Weight, params.data.Spr[k].ReabSpr_Level]);
									}
									if (params.data.Spr[k].ReabSpr_Cod == 47)
									{
										aSprComboSide.push([params.data.Spr[k].ReabSpr_Elem_id, params.data.Spr[k].SprName, params.data.Spr[k].ReabSpr_Elem_Weight, params.data.Spr[k].ReabSpr_Level]);
									}
									if (params.data.Spr[k].ReabSpr_Cod == 48)
									{
										aSprComboMotion.push([params.data.Spr[k].ReabSpr_Elem_id, params.data.Spr[k].SprName, params.data.Spr[k].ReabSpr_Elem_Weight, params.data.Spr[k].ReabSpr_Level]);
									}
									if (params.data.Spr[k].ReabSpr_Cod == 49)
									{
										aSprComboJointContracture.push([params.data.Spr[k].ReabSpr_Elem_id, params.data.Spr[k].SprName, params.data.Spr[k].ReabSpr_Elem_Weight, params.data.Spr[k].ReabSpr_Level]);
									}
									if (params.data.Spr[k].ReabSpr_Cod == 50)
									{
										aSprComboDeformation.push([params.data.Spr[k].ReabSpr_Elem_id, params.data.Spr[k].SprName, params.data.Spr[k].ReabSpr_Elem_Weight, params.data.Spr[k].ReabSpr_Level]);
									}
									if (params.data.Spr[k].ReabSpr_Cod == 51)
									{
										aSprComboIntervention.push([params.data.Spr[k].ReabSpr_Elem_id, params.data.Spr[k].SprName, params.data.Spr[k].ReabSpr_Elem_Weight, params.data.Spr[k].ReabSpr_Level]);
									}
								}
								//  console.log('aSprComboJoint=',aSprComboJoint);

								var Field = new Ext.Panel({
									id: params.data.Grid + '_' + params.data.id + '_Panel',
									layout: 'form',
									border: true,
									//hidden : true,
									width: 1240,
									heigth: 150,
									bodyStyle: 'padding: 10px;background-color: #E2FFE9', //+bgcolor
									// style: 'background-color:#E3E3E3!important'
									style: 'border-top: 1px solid #99bbe8; border-left: 1px solid #99bbe8; border-bottom: 1px solid #ffffff;border-right: 1px solid #ffffff; padding: 2px 0px 0px 0px; margin-top: 5px;background-color: transparent ;',
									items: [
										//Панель для заполнения RGRID
										{
											layout: 'form',
											width: 1200,
											//labelWidth: 90,
											border: true,
											bodyStyle: 'background-color: transparent',
											style: 'background-color: transparent',
											items: [
												{layout: 'column',
													width: 1200,
													items: [
														//combo Вид сустава, Сторона, Движение в суставе
														{
															layout: 'form',
															border: false,
															// width: 320,
															labelWidth: 80,
															labelAlign: 'left',
															items: [
																//Вид сустава
																{
																	allowBlank: false,
																	style: 'text-align : center; font-size:1.1em; ',
																	id: 'Type_of_joint_id',
																	xtype: 'combo',
																	//labelAlign: 'rigth',
																	//labelWidth: 100,
																	width: 150,
																	fieldLabel: 'Вид сустава',
																	hideTrigger: false, // Chek
																	mode: 'local',
																	editable: false,
																	triggerAction: 'all',
																	displayField: 'SprName',
																	valueField: 'ReabSpr_Elem_id',
																	tabIndex: -1,
																	emptyText: 'Введите параметр',
																	listWidth: 'auto',
																	listWidth: 'auto',
																	hiddenName: 'ReabSpr_Elem_id',
																	autoscroll: false,
																	xtype: 'combo',
																	store: new Ext.data.SimpleStore({
																		fields: [
																			{name: 'ReabSpr_Elem_id', type: 'int'},
																			{name: 'SprName', type: 'string'},
																			{name: 'ReabSpr_Elem_Weight', type: 'string'}
																		],
																		data: aSprComboJoint
																	}),
																	tpl: '<tpl for="."><div class="x-combo-list-item">' +
																	'{SprName} ' + '&nbsp;' +
																	'</div></tpl>'
																}
															]
														},
														{
															layout: 'form',
															border: false,
															labelWidth: 50,
															labelAlign: 'rigth',
															style: 'position:relative;  left:20px ',
															items: [
																//Сторона
																{
																	allowBlank: false,
																	style: 'text-align : center; font-size:1.1em; ',
																	id: 'Side_id',
																	xtype: 'combo',
																	fieldLabel: 'Сторона',
																	hideTrigger: false, // Chek
																	mode: 'local',
																	editable: false,
																	triggerAction: 'all',
																	displayField: 'SprName',
																	valueField: 'ReabSpr_Elem_id',
																	width: 150,
																	emptyText: 'Введите параметр',
																	listWidth: 'auto',
																	hiddenName: 'ReabSpr_Elem_id',
																	autoscroll: false,
																	xtype: 'combo',
																	store: new Ext.data.SimpleStore({
																		fields: [
																			{name: 'ReabSpr_Elem_id', type: 'int'},
																			{name: 'SprName', type: 'string'},
																			{name: 'ReabSpr_Elem_Weight', type: 'string'}
																		],
																		data: aSprComboSide
																	}),

																	tpl: '<tpl for="."><div class="x-combo-list-item">' +
																	'{SprName} ' + '&nbsp;' +
																	'</div></tpl>'
																}
															]
														},
														{
															layout: 'form',
															border: false,
															labelWidth: 120,
															labelAlign: 'rigth',
															style: 'position:relative;  left:50px ',
															items: [
																{
																	allowBlank: false,
																	style: 'text-align : center; font-size:1.1em; ',
																	id: 'Motion_Id',
																	xtype: 'combo',
																	fieldLabel: 'Движения в суставе',
																	hideTrigger: false, // Chek
																	labelAlign: 'rigth',
																	mode: 'local',
																	editable: false,
																	triggerAction: 'all',
																	displayField: 'SprName',
																	valueField: 'ReabSpr_Elem_id',
																	//  style : 'width: 150px; font-size:1.1em',
																	width: 150,
																	emptyText: 'Введите параметр',
																	listWidth: 'auto',
																	hiddenName: 'ReabSpr_Elem_id',
																	autoscroll: false,
																	xtype: 'combo',
																	store: new Ext.data.SimpleStore({
																		fields: [
																			{name: 'ReabSpr_Elem_id', type: 'int'},
																			{name: 'SprName', type: 'string'},
																			{name: 'ReabSpr_Elem_Weight', type: 'string'}
																		],
																		data: aSprComboMotion
																	}),

																	tpl: '<tpl for="."><div class="x-combo-list-item">' +
																	'{SprName} ' + '&nbsp;' +
																	'</div></tpl>'
																},
															]
														},
														{
															layout: 'form',
															border: false,
															labelWidth: 130,
															labelAlign: 'rigth',
															style: 'position:relative;  left:80px ',
															items: [
																{
																	allowBlank: false,
																	style: 'text-align : center; font-size:1.1em; ',
																	id: 'Deformation_id',
																	xtype: 'combo',
																	fieldLabel: 'Деформация сустава',
																	hideTrigger: false, // Chek
																	labelAlign: 'rigth',
																	mode: 'local',
																	editable: false,
																	triggerAction: 'all',
																	displayField: 'SprName',
																	valueField: 'ReabSpr_Elem_id',
																	//  style : 'width: 150px; font-size:1.1em',
																	width: 150,
																	emptyText: 'Введите параметр',
																	listWidth: 'auto',
																	hiddenName: 'ReabSpr_Elem_id',
																	autoscroll: false,
																	xtype: 'combo',
																	store: new Ext.data.SimpleStore({
																		fields: [
																			{name: 'ReabSpr_Elem_id', type: 'int'},
																			{name: 'SprName', type: 'string'},
																			{name: 'ReabSpr_Elem_Weight', type: 'string'}
																		],
																		data: aSprComboDeformation
																	}),

																	tpl: '<tpl for="."><div class="x-combo-list-item">' +
																	'{SprName} ' + '&nbsp;' +
																	'</div></tpl>'
																},
															]
														}
													]
												},
												{
													layout: 'form',
													border: false,
													width: 1200,
													height: 20
												},
												{layout: 'column',
													width: 1200,
													//labelWidth: 90,
													//  id: '4444',
													items: [
														//combo
														{
															layout: 'form',
															border: false,
															// width: 320,
															labelWidth: 180,
															labelAlign: 'left',
															items: [
																{
																	allowBlank: false,
																	//style : 'text-align : center; font-size:1.1em; ',
																	id: 'JointContracture_id',
																	xtype: 'combo',
																	labelStyle: "text-align : center;",
																	width: 120,
																	fieldLabel: 'Наличие контрактуры сустава',
																	hideTrigger: false, // Chek
																	mode: 'local',
																	editable: false,
																	triggerAction: 'all',
																	displayField: 'SprName',
																	valueField: 'ReabSpr_Elem_id',
																	style: 'text-align:center;',
																	// style : 'width: 100px; font-size:1.1em;left:10px;',
																	emptyText: 'Введите параметр',
																	//listWidth:'auto',
																	hiddenName: 'ReabSpr_Elem_id',
																	autoscroll: false,
																	xtype: 'combo',
																	store: new Ext.data.SimpleStore({
																		fields: [
																			{name: 'ReabSpr_Elem_id', type: 'int'},
																			{name: 'SprName', type: 'string'},
																			{name: 'ReabSpr_Elem_Weight', type: 'string'}
																		],
																		data: aSprComboJointContracture
																	}),

																	tpl: '<tpl for="."><div class="x-combo-list-item">' +
																	'{SprName} ' + '&nbsp;' +
																	'</div></tpl>'
																}
															]
														},
														{
															layout: 'form',
															border: false,
															labelWidth: 300,
															labelAlign: 'rigth',
															style: 'position:relative;  left:40px ',
															items: [
																{
																	allowBlank: false,
																	id: 'Intervention_id',
																	xtype: 'combo',
																	fieldLabel: 'Наличие оперативного вмешательства на пораженный /травмированный сустав',
																	hideTrigger: false, // Chek
																	mode: 'local',
																	editable: false,
																	triggerAction: 'all',
																	displayField: 'SprName',
																	valueField: 'ReabSpr_Elem_id',
																	width: 120,
																	emptyText: 'Введите параметр',
																	// listWidth:'auto',
																	hiddenName: 'ReabSpr_Elem_id',
																	autoscroll: false,
																	style: 'text-align:center;',
																	xtype: 'combo',
																	store: new Ext.data.SimpleStore({
																		fields: [
																			{name: 'ReabSpr_Elem_id', type: 'int'},
																			{name: 'SprName', type: 'string'},
																			{name: 'ReabSpr_Elem_Weight', type: 'string'}
																		],
																		data: aSprComboIntervention
																	}),

																	tpl: '<tpl for="."><div class="x-combo-list-item">' +
																	'{SprName} ' + '&nbsp;' +
																	'</div></tpl>'
																}
															]
														}
													]
												}
											]
										},
										// кнопка сохранить
										{
											layout: 'form',
											width: 100,
											items: [
												new Ext.Button({
													id: params.data.Grid + '_' + params.data.id + 'ButtonSave',
													iconCls: 'save16',
													text: 'Сохранить',
													handler: function (b, e)
													{
														Ext.getCmp(params.data.Grid + '_' + params.data.id + '_Panel').ComplaintSave();
													}.createDelegate(this),

												})
											]
										}
									],
									ComplaintCalc: function ()
									{
										//Подсчитаем Потенциал
										var nPotentSumm = 0;

										var rrr = parseFloat(Ext.getCmp('Motion_Id').getStore().data.items[Ext.getCmp('Motion_Id').selectedIndex].data.ReabSpr_Elem_Weight);
										// console.log('rrr',rrr);
										nPotentSumm = nPotentSumm + rrr;
										var rrr = parseFloat(Ext.getCmp('Deformation_id').getStore().data.items[Ext.getCmp('Deformation_id').selectedIndex].data.ReabSpr_Elem_Weight);
										nPotentSumm = nPotentSumm + rrr;
										var rrr = parseFloat(Ext.getCmp('JointContracture_id').getStore().data.items[Ext.getCmp('JointContracture_id').selectedIndex].data.ReabSpr_Elem_Weight);
										nPotentSumm = nPotentSumm + rrr;
										var rrr = parseFloat(Ext.getCmp('Intervention_id').getStore().data.items[Ext.getCmp('Intervention_id').selectedIndex].data.ReabSpr_Elem_Weight);
										nPotentSumm = nPotentSumm + rrr;
										return nPotentSumm
									},
									ComplaintReset: function () {
										Ext.getCmp('Type_of_joint_id').setValue('Введите параметр');
										Ext.getCmp('Type_of_joint_id').selectedIndex = -1;
										Ext.getCmp('Side_id').setValue('Введите параметр');
										Ext.getCmp('Side_id').selectedIndex = -1;
										Ext.getCmp('Motion_Id').setValue('Введите параметр');
										Ext.getCmp('Motion_Id').selectedIndex = -1;
										Ext.getCmp('Deformation_id').setValue('Введите параметр');
										Ext.getCmp('Deformation_id').selectedIndex = -1;
										Ext.getCmp('JointContracture_id').setValue('Введите параметр');
										Ext.getCmp('JointContracture_id').selectedIndex = -1;
										Ext.getCmp('Intervention_id').setValue('Введите параметр');
										Ext.getCmp('Intervention_id').selectedIndex = -1;
									},
									ComplaintSave: function () {
										if (this.ComplaintValidat() == false)
										{
											return
										}

										var nPotent = 0;
										nPotent = this.ComplaintCalc();

										//Заполнение GRIDa
										var nRecord = Ext.getCmp('ReabGrid_' + params.data.id).getGrid().getStore().data.items.length;
										Ext.getCmp('ReabGrid_' + params.data.id).getGrid().store.insert(nRecord, [new Ext.data.Record({
											Id: nRecord + 1,
											Type_of_joint_id: Ext.getCmp('Type_of_joint_id').getStore().data.items[Ext.getCmp('Type_of_joint_id').selectedIndex].data.ReabSpr_Elem_id,
											Type_of_joint: Ext.getCmp('Type_of_joint_id').getStore().data.items[Ext.getCmp('Type_of_joint_id').selectedIndex].data.SprName,
											Side_id: Ext.getCmp('Side_id').getStore().data.items[Ext.getCmp('Side_id').selectedIndex].data.ReabSpr_Elem_id,
											Side: Ext.getCmp('Side_id').getStore().data.items[Ext.getCmp('Side_id').selectedIndex].data.SprName,
											Motion_Id: Ext.getCmp('Motion_Id').getStore().data.items[Ext.getCmp('Motion_Id').selectedIndex].data.ReabSpr_Elem_id,
											Motion: Ext.getCmp('Motion_Id').getStore().data.items[Ext.getCmp('Motion_Id').selectedIndex].data.SprName,
											Deformation_id: Ext.getCmp('Deformation_id').getStore().data.items[Ext.getCmp('Deformation_id').selectedIndex].data.ReabSpr_Elem_id,
											Deformation: Ext.getCmp('Deformation_id').getStore().data.items[Ext.getCmp('Deformation_id').selectedIndex].data.SprName,
											JointContracture_id: Ext.getCmp('JointContracture_id').getStore().data.items[Ext.getCmp('JointContracture_id').selectedIndex].data.ReabSpr_Elem_id,
											JointContracture: Ext.getCmp('JointContracture_id').getStore().data.items[Ext.getCmp('JointContracture_id').selectedIndex].data.SprName,
											Intervention_id: Ext.getCmp('Intervention_id').getStore().data.items[Ext.getCmp('Intervention_id').selectedIndex].data.ReabSpr_Elem_id,
											Intervention: Ext.getCmp('Intervention_id').getStore().data.items[Ext.getCmp('Intervention_id').selectedIndex].data.SprName,
											StageComp_Weight: nPotent
										})]);
										Ext.getCmp('ReabGrid_' + params.data.id).getGrid().getSelectionModel().selectRow(0);
										Ext.getCmp('ReabGrid_' + params.data.id).getGrid().getSelectionModel().deselectRow(0);
										Ext.getCmp('ReabGrid_' + params.data.id).ViewActions.action_delete.setDisabled(true);
										Ext.getCmp(params.data.Grid + '_' + params.data.id + '_Panel').hide();
										Ext.getCmp('ReabGrid_' + params.data.id).ViewActions.action_add.setDisabled(false);
										this.ComplaintReset();

									},
									ComplaintValidat: function () {
										var cMessage = 'Отсутствует ответ на вопрос :';
										if (Ext.getCmp('Type_of_joint_id').selectedIndex == -1 || Ext.getCmp('Type_of_joint_id').getValue() == "")
										{
											cMessage = cMessage + '<br>' + '1.Вид сустава;';
										}
										if (Ext.getCmp('Side_id').selectedIndex == -1 || Ext.getCmp('Side_id').getValue() == "")
										{
											cMessage = cMessage + '<br>' + '2.Сторона;';
										}
										if (Ext.getCmp('Motion_Id').selectedIndex == -1 || Ext.getCmp('Motion_Id').getValue() == "")
										{
											cMessage = cMessage + '<br>' + '3.Движение в суставе;';
										}
										if (Ext.getCmp('Deformation_id').selectedIndex == -1 || Ext.getCmp('Deformation_id').getValue() == "")
										{
											cMessage = cMessage + '<br>' + '4.Деформация сустава;';
										}
										if (Ext.getCmp('JointContracture_id').selectedIndex == -1 || Ext.getCmp('JointContracture_id').getValue() == "")
										{
											cMessage = cMessage + '<br>' + '5.Наличие контрактуры сустава;';
										}
										if (Ext.getCmp('Intervention_id').selectedIndex == -1 || Ext.getCmp('Intervention_id').getValue() == "")
										{
											cMessage = cMessage + '<br>' + '6.Наличие оперативного вмешательства на пораженный /травмированный сустав;';
										}
										if (cMessage != 'Отсутствует ответ на вопрос :')
										{
											form.showMsg(cMessage);
											return false;
										} else {
											return true;
										}
									}
								});

								return Field;
								break;
							case 14 :
								var aSprComboPower = new Array();
								var aSprComboStage = new Array();
								var aSprComboRisk = new Array();
								for (var k in params.data.Spr)
								{
									if (params.data.Spr[k].ReabSpr_Cod == 58)
									{
										aSprComboPower.push([params.data.Spr[k].ReabSpr_Elem_id, params.data.Spr[k].SprName, params.data.Spr[k].ReabSpr_Elem_Weight, params.data.Spr[k].ReabSpr_Level]);
									}
									if (params.data.Spr[k].ReabSpr_Cod == 59)
									{
										aSprComboStage.push([params.data.Spr[k].ReabSpr_Elem_id, params.data.Spr[k].SprName, params.data.Spr[k].ReabSpr_Elem_Weight, params.data.Spr[k].ReabSpr_Level]);
									}
									if (params.data.Spr[k].ReabSpr_Cod == 60)
									{
										aSprComboRisk.push([params.data.Spr[k].ReabSpr_Elem_id, params.data.Spr[k].SprName, params.data.Spr[k].ReabSpr_Elem_Weight, params.data.Spr[k].ReabSpr_Level]);
									}
								}

								var Field = new Ext.Panel({
									id: params.data.Grid + '_' + params.data.id + '_Panel',
									layout: 'form',
									border: true,
									disabled: true,
									heigth: 150,
									bodyStyle: 'padding: 10px;background-color: #E2FFE9', //+bgcolor
									// style: 'background-color:#E3E3E3!important'
									style: 'border-top: 1px solid #99bbe8; border-left: 1px solid #99bbe8; border-bottom: 1px solid #ffffff;border-right: 1px solid #ffffff; padding: 2px 0px 0px 0px; margin-top: 5px;background-color: transparent ;',
									items: [
										//Панель для заполнения RGRID
										{
											layout: 'form',
											//width: 1200,
											//labelWidth: 90,
											border: true,
											bodyStyle: 'background-color: transparent',
											style: 'background-color: transparent',
											items: [
												{layout: 'column',
													width: 1200,
													items: [
														//combo Степень, стадия, риск
														{
															layout: 'form',
															border: false,
															// width: 320,
															labelWidth: 60,
															labelAlign: 'left',
															items: [
																//Вид сустава
																{
																	allowBlank: false,
																	style: 'text-align: center; font-size:1.1em; ',
																	id: 'HipPower_id',
																	xtype: 'combo',
																	//labelAlign: 'rigth',
																	//labelWidth: 100,
																	width: 140,
																	fieldLabel: 'Степень',
																	hideTrigger: false, // Chek
																	mode: 'local',
																	editable: false,
																	triggerAction: 'all',
																	displayField: 'SprName',
																	valueField: 'ReabSpr_Elem_id',
																	tabIndex: -1,
																	emptyText: 'Введите параметр',
																	// listWidth:'auto',
																	hiddenName: 'ReabSpr_Elem_id',
																	autoscroll: false,
																	xtype: 'combo',
																	store: new Ext.data.SimpleStore({
																		fields: [
																			{name: 'ReabSpr_Elem_id', type: 'int'},
																			{name: 'SprName', type: 'string'},
																			{name: 'ReabSpr_Elem_Weight', type: 'string'}
																		],
																		data: aSprComboPower
																	}),

																	tpl: '<tpl for="."><div class="x-combo-list-item">' +
																	'{SprName} ' + '&nbsp;' +
																	'</div></tpl>'
																}
															]
														},
														{
															layout: 'form',
															border: false,
															labelWidth: 40,
															labelAlign: 'rigth',
															style: 'position:relative;  left:10px ',
															items: [
																//Сторона
																{
																	allowBlank: false,
																	style: 'text-align: center; font-size:1.1em; ',
																	id: 'HipStage_id',
																	xtype: 'combo',
																	fieldLabel: 'Стадия',
																	hideTrigger: false, // Chek
																	mode: 'local',
																	editable: false,
																	triggerAction: 'all',
																	displayField: 'SprName',
																	valueField: 'ReabSpr_Elem_id',
																	width: 140,
																	emptyText: 'Введите параметр',
																	// listWidth:'auto',
																	hiddenName: 'ReabSpr_Elem_id',
																	autoscroll: false,
																	xtype: 'combo',
																	store: new Ext.data.SimpleStore({
																		fields: [
																			{name: 'ReabSpr_Elem_id', type: 'int'},
																			{name: 'SprName', type: 'string'},
																			{name: 'ReabSpr_Elem_Weight', type: 'string'}
																		],
																		data: aSprComboStage
																	}),

																	tpl: '<tpl for="."><div class="x-combo-list-item">' +
																	'{SprName} ' + '&nbsp;' +
																	'</div></tpl>'
																}
															]
														},
														{
															layout: 'form',
															border: false,
															labelWidth: 40,
															labelAlign: 'rigth',
															style: 'position:relative;  left:30px ',
															items: [
																{
																	allowBlank: false,
																	style: 'text-align: center; font-size:1.1em; ',
																	id: 'HipRisk_Id',
																	xtype: 'combo',
																	fieldLabel: 'Риск',
																	hideTrigger: false, // Chek
																	labelAlign: 'rigth',
																	mode: 'local',
																	editable: false,
																	triggerAction: 'all',
																	displayField: 'SprName',
																	valueField: 'ReabSpr_Elem_id',
																	width: 140,
																	emptyText: 'Введите параметр',
																	//listWidth:'auto',
																	hiddenName: 'ReabSpr_Elem_id',
																	autoscroll: false,
																	xtype: 'combo',
																	store: new Ext.data.SimpleStore({
																		fields: [
																			{name: 'ReabSpr_Elem_id', type: 'int'},
																			{name: 'SprName', type: 'string'},
																			{name: 'ReabSpr_Elem_Weight', type: 'string'}
																		],
																		data: aSprComboRisk
																	}),

																	tpl: '<tpl for="."><div class="x-combo-list-item">' +
																	'{SprName} ' + '&nbsp;' +
																	'</div></tpl>'
																},
															]
														}
													]
												},
												{
													layout: 'form',
													border: false,
													width: 1200,
													height: 20
												}
											]
										}
									],
									ComplaintReset: function () {
										Ext.getCmp('HipPower_id').setValue('Введите параметр');
										Ext.getCmp('HipPower_id').selectedIndex = -1;
										Ext.getCmp('HipStage_id').setValue('Введите параметр');
										Ext.getCmp('HipStage_id').selectedIndex = -1;
										Ext.getCmp('HipRisk_Id').setValue('Введите параметр');
										Ext.getCmp('HipRisk_Id').selectedIndex = -1;
									},
								});
								return Field;
								break;
							case 15:		//Новый реабилитационный диагноз
								var nWidth = 1240;
								var Grid = new sw.Promed.ViewFrame(
										{
											actions: [
												{name: 'action_add', hidden: true},
												{name: 'action_view', hidden: true},
												{name: 'action_edit', hidden: true},
												{name: 'action_delete', hidden: true},
												{name: 'action_refresh', disabled: true,handler: function () {
													Ext.getCmp('ufa_personReabRegistryWindow').getReabDiagICF('ReabGrid_' + params.data.id);
													//alert("Передернем");
												}.createDelegate(this)},
												{name: 'action_print', hidden: true}
											],
											autoExpandColumn: 'autoexpand',
											autoExpandMin: 100,
											dataUrl: '?c=Ufa_Reab_Register_User&m=getReabDiagICF',
											autoLoadData: false,
											id: 'ReabGrid_' + params.data.id,
											pageSize: 50,
											height: 210,
											width: nWidth,
											paging: false, // навигатор
											//region: 'center',
											//root: 'data',
											stringfields: [
												{name: 'vID', type: 'int', header: 'ID', key: true},
												{name: 'ICF_Code', type: 'string', header: lang['kod'], width: 80,align: 'center'}, //1
												{name: 'ICF_Name', type: 'string',  header: lang['naimenovanie'], width: 550},
												{name: 'setDate1', type: 'date', format: 'd.m.Y', header: "Дата первой<br>оценки", width: 120,align: 'center'},
												{name: 'setDate2', type: 'date', format: 'd.m.Y', header: "Дата последней<br>оценки", width: 120,align: 'center'},
												{name: 'result', type: 'string', header: lang['rezultat'], id: 'autoexpand',align: 'center'}
											],
											focusOnFirstLoad: false,
											toolbar: true,
											//totalProperty: 'totalCount',
											onBeforeLoadData: function () {
												//this.getButtonSearch().disable();
											}.createDelegate(this),
											onLoadData: function () {
												Ext.getCmp('ufa_personReabRegistryWindow').getColorSell('ReabGrid_' + params.data.id);
											}.createDelegate(this),
											onRowSelect: function (sm, index, record) {
												// alert('выбрали запись');
												// console.log('form.isButtonAdd=',form.isButtonAdd);
//												if (form.isButtonEdit == true || form.isButtonAdd == true)
//												{
//													//console.log('form.isButtonAdd=',form.isButtonAdd);
//													Ext.getCmp('ReabGrid_' + params.data.id).ViewActions.action_delete.setDisabled(false);
//												}
											}
										});
								return Grid;
								break;
							case 55:
								var QuestionPanel = new Ext.Panel({
									baseCls: 'x-plain',
									id: 'QuestionPanel_' + params.data.id,
									border: false,
									width: 650,
									layout: 'form',
									bodyStyle: 'padding: 10px;background-color: #E2FFE9', //+bgcolor
									tabIndex: -1,
									items: [
										{
											xtype: 'panel',
											border: false,
											layout: 'column',
											items: []
										}
									]
								});
								return QuestionPanel;
								break;
							case 56:
								var ComboGroup = new Ext.form.ComboBox(
										{
											allowBlank: false,
											id: 'ComboGroup_' + params.data.id,
											hideLabel: true,
											hideTrigger: true, // Chek
											mode: 'local',
											editable: false,
											triggerAction: 'all',
											displayField: 'ParamGlobal',
											valueField: 'ReabSpr_Elem_id',
											style: 'width: 400px; border:none;font-size:1.1em',
											width: 'auto',
											tabIndex: 1,
											listWidth: 'auto',
											hiddenName: 'ReabSpr_Elem_id',
											autoscroll: false,
											value: 1,
											xtype: 'combo',
											store: new Ext.data.SimpleStore({
												fields: [{name: 'ParamGlobal', type: 'string'}, {name: 'ReabSpr_Elem_id', type: 'int'}],
												data: [
													['Присутствует', 1],
													['Отсутствует', 2]
												]
											}),
											listeners: {
												select: function (combo, record, index) {

													// Вызов процедуры
													Ext.getCmp('ufa_personReabRegistryWindow').madeGroup(combo, record, index, params.data.element, params.data.id);
												}
											}
										})
								return ComboGroup;
								break;
							default:
								alert('Косяк');
								break;
						}

					},
					//Обработка общего combo
					madeGroup: function (combo, record, index, elements, id) {
						//console.log('id==', id);
						//console.log('elements==', elements);
						//console.log('combo==', combo);
						if (combo.selectedIndex == 1)
						{
							for (var j in elements) {
								if (elements[j].Group_id == id && elements[j].Global != null)
								{
									if (elements[j].Elem_Type == 'Combo')
									{
										Ext.getCmp('ReabComboAnk_' + elements[j].Parameter_id).selectedIndex = -1;
										Ext.getCmp('ReabComboAnk_' + elements[j].Parameter_id).setValue('Введите параметр');
										Ext.getCmp('ReabComboAnk_' + elements[j].Parameter_id).hide();
									}
//                        if(elements[j].Elem_Type == 'Grid5')
//                        {
//                                Ext.getCmp('ReabGrid_' + elements[j].Parameter_id).hide();
//                        }
									Ext.getCmp('Quest_' + elements[j].Parameter_id).hide();
								}
							}
						} else
						{
							for (var jj in elements) {
								if (elements[jj].Group_id == id)
								{
									// console.log('elements[jj]==', elements[jj]);
									if (elements[jj].Elem_Type == 'Combo')
									{
										//Ext.getCmp('Quest_' + elements[jj].Parameter_id).show();
										Ext.getCmp('ReabComboAnk_' + elements[jj].Parameter_id).show();
									}
									if (elements[jj].Elem_Type == 'Grid5')
									{
										Ext.getCmp('ReabGrid_' + elements[jj].Parameter_id).show();
									}
									Ext.getCmp('Quest_' + elements[jj].Parameter_id).show();
								}
							}

						}
					},

					elemDisabled: function () {
						//Разобраться
						return (Ext.getCmp('ufa_personReabRegistryWindow').Lpu_id == getGlobalOptions().lpu_id) || (typeof Ext.getCmp('ufa_personReabRegistryWindow').Lpu_id == 'undefined') ? false : true;
					},
					//Управление панелью реабилитационного потенциала
					getReabPotentPanel: function (nHead) {

						var form = Ext.getCmp('ufa_personReabRegistryWindow');
						//console.log('nHead=', form.HeadAnketa[nHead]);
						Ext.getCmp('panelReabInfo').show(); // Открываем панель потенциала
						Ext.getCmp('ReabPotent_id').setValue(form.HeadAnketa[nHead].ReabPotent);

						Ext.getCmp('ReabStage_id').setValue(form.HeadAnketa[nHead].StageTypeSysNick);
						var cDate = form.HeadAnketa[nHead].ReabRegister_setDate.date.substr(0, 10);
						cDate = cDate.substr(8, 2) + '.' + cDate.substr(5, 2) + '.' + cDate.substr(0, 4);
						//console.log('Перерисовка окна',form.HeadAnketa[nHead]);
						Ext.getCmp('ReabDateStart_id').setValue(cDate);
						if (form.HeadAnketa[nHead].ReabRegister_disDate === null)
						{
							Ext.getCmp('ReabDateFinishField').hide();
							Ext.getCmp('ReabOutCauseField').hide();
						} else
						{
							Ext.getCmp('ReabDateFinishField').show();
							var cDate = form.HeadAnketa[nHead].ReabRegister_disDate.date.substr(0, 10);
							cDate = cDate.substr(8, 2) + '.' + cDate.substr(5, 2) + '.' + cDate.substr(0, 4);
							Ext.getCmp('ReabDateFinish_id').setValue(cDate);

							Ext.getCmp('ReabOutCause_id').setValue(form.HeadAnketa[nHead].ReabOutCauseName);
							Ext.getCmp('ReabOutCauseField').show();
						}
						return;
					},

					//метод создания формы (пока анкеты для 1-го этапа ЦНС)
					createForm: function () {
						// alert('Опять проблемы!!!!!!');
						var formQuestions = new sw.Promed.FormPanel({
							frame: false,
							bodyBorder: false,
							bodyStyle: 'background-color:white!important; padding:20px!important',
							frame: false,
							autoWidth: false,
							autoHeight: false,
							region: 'center',
							id: 'TTTTTTTTTTT',
							items: [
								{
									xtype: 'panel',
									html: '<p>&nbsp;</p>',
									border: false
								}
							]
						});

						return formQuestions;
					},


					/**
					 * Создание и прорисовка поля после предложения добавить ответ
					 * @param object
					 */
					renderAnswer: function (params) {
						//console.log('>>>', params);


					},
					getValueOnDb: function (url, answer_field) {


						return;
					},
					showMsg: function (msg) {
						sw.swMsg.show(
								{
									buttons: Ext.Msg.OK,
									icon: Ext.Msg.WARNING,
									width: 600,
									msg: msg,
									title: ERR_INVFIELDS_TIT
								});
					}
				});

				sw.Promed.ufa_personReabRegistryWindow.superclass.initComponent.apply(this, arguments);
			},
			//заболевания в анамнезе пациента
			getDiagPerson: function (id, cSpr, cName, cTemplJoin)
			{
				var form = Ext.getCmp('ufa_personReabRegistryWindow');
				//  console.log('oGrid=',oGrid);
				// console.log('Person_id=',this.Person_id);
				var cGrid = 'ReabGrid_' + id;

				$.ajax({
					mode: "abort",
					type: "post",
					async: false,
					url: '/?c=Ufa_Reab_Register_User&m=getDiagPerson',
					data: {Person_id: this.Person_id,
						DiagMKB: cSpr.trim()},
					success: function (response) {
						var ObjMKBDiag = Ext.util.JSON.decode(response);
						console.log('ObjMKBDiag=', ObjMKBDiag);
						if ('Error_Code' in ObjMKBDiag)
						{
							form.showMsg('Ошибка при выполнении запроса. Обратитесь к разработчику!');
							return
						}

						//Заполним таблицу диагнозов
						var nRecord = Ext.getCmp(cGrid).getGrid().getStore().data.items.length;
						if (nRecord > 0)
						{//Очистка GRIDa
							Ext.getCmp(cGrid).getGrid().store.removeAll( );
							nRecord = 0;

						}
						for (jj = 0; jj < ObjMKBDiag.data.length; jj++)
						{
							var dateDiag = new Date();
							if (ObjMKBDiag.data[jj].diagDate == null)
							{
								//console.log('Косяк с датой=', ObjMKBDiag.data[jj].diagDate);
								//console.log('dateDiag=', dateDiag);
								dateDiag = Ext.getCmp('TextFieldDateReab').originalValue;
							} else
							{
								dateDiag = new Date(ObjMKBDiag.data[jj].diagDate.replace(/(\d+)-(\d+)-(\d+)/, '$2/$3/$1'));
							}

							Ext.getCmp(cGrid).getGrid().store.insert(jj, [new Ext.data.Record({
								Id: nRecord + 1,
								Diag_Code: ObjMKBDiag.data[jj].Diag_CodeReab,
								Diag_Name: ObjMKBDiag.data[jj].Diag_Name,
								//SetDate: ObjMKBDiag.data[jj].diagDate
								SetDate: dateDiag
							})]);
							Ext.getCmp(cGrid).getGrid().getSelectionModel().selectRow(nRecord);
						}

						nRecord = Ext.getCmp(cGrid).getGrid().getStore().data.items.length;
						if (nRecord == 0)
						{
							Ext.getCmp(cGrid).getGrid().tbar.dom.firstChild.firstChild.firstChild.firstChild.lastChild.firstChild.innerText = '0 / 0';
							Ext.getCmp(cGrid).setDisabled(true);
						} else
						{
							Ext.getCmp(cGrid).getGrid().getSelectionModel().selectRow(0);
							Ext.getCmp(cGrid).getGrid().getSelectionModel().deselectRow(0);
							Ext.getCmp(cGrid).setDisabled(false);
						}

						//console.log('cName=', cName);
						//console.log('cTemplJoin=', cTemplJoin);

						if (cName == 'RGrid2')
						{
							nRecord = Ext.getCmp(cGrid).getGrid().getStore().data.items.length;
							if (nRecord == 0)
							{
								Ext.getCmp(cName + '_' + id + '_Panel').hide();
							} else
							{
								Ext.getCmp(cName + '_' + id + '_Panel').show();
							}
						}
						if (cName == 'Grid7' && cTemplJoin !== null)
						{
							//console.log('cTemplJoin1=', cTemplJoin);
							nRecord = Ext.getCmp(cGrid).getGrid().getStore().data.items.length;
							if (nRecord > 0)
							{
								Ext.getCmp('ComboGroup_' + cTemplJoin).setValue(1);
								Ext.getCmp('ComboGroup_' + cTemplJoin).setDisabled(true);

								for (var j in form.TemplObject)
								{
									if (form.TemplObject[j].Group_id == cTemplJoin && form.TemplObject[j].Elem_Type == 'Combo')
									{
										Ext.getCmp('ReabComboAnk_' + form.TemplObject[j].Parameter_id).selectedIndex = -1;
										Ext.getCmp('ReabComboAnk_' + form.TemplObject[j].Parameter_id).setValue('Введите параметр');
										Ext.getCmp('ReabComboAnk_' + form.TemplObject[j].Parameter_id).show();
										Ext.getCmp('Quest_' + form.TemplObject[j].Parameter_id).show();
									}
								}
							} else
							{
								Ext.getCmp('ComboGroup_' + cTemplJoin).setValue(2);
								Ext.getCmp('ComboGroup_' + cTemplJoin).setDisabled(false);
								for (var j in form.TemplObject)
								{
									if (form.TemplObject[j].Group_id == cTemplJoin && form.TemplObject[j].Elem_Type == 'Combo')
									{
										Ext.getCmp('ReabComboAnk_' + form.TemplObject[j].Parameter_id).selectedIndex = -1;
										Ext.getCmp('ReabComboAnk_' + form.TemplObject[j].Parameter_id).setValue('Введите параметр');
										Ext.getCmp('ReabComboAnk_' + form.TemplObject[j].Parameter_id).hide();
										Ext.getCmp('Quest_' + form.TemplObject[j].Parameter_id).hide();
									}
								}
							}

						}

					},
					error: function () {
						alert("При обработке запроса на сервере произошла ошибка!");
					}
				});
			},
			//Расчет индекса массы тела
			getbodyMassIndex: function () {
				var weight = Ext.getCmp('ReabFieldAnk_weight').getValue();
				var height = Ext.getCmp('ReabFieldAnk_height').getValue();
				if (weight != '' && height != '')
				{ //Расчет
					console.log('Расчет');
					var tt = (height / 100);
					var tt1 = tt * tt;
					var Index = weight / tt1;
					//   console.log('Ind=',Ind);
					//   console.log('Index11=',Index.toFixed(2));
					Ext.getCmp('ReabFieldAnk_Index').setValue(Index.toFixed(2));
				}
				return;
			},
			//Получение списка Оценок по МКФ пациента для реабилитационного диагноза (анкета)
			getReabDiagICF: function (id)
			{
				Ext.getCmp(id).getGrid().getStore().load({
					params: {
						Person_id: Ext.getCmp('ufa_personReabRegistryWindow').Person_id,
						ReabEvent_id: Ext.getCmp('GridReabUser').getGrid().getSelectionModel().getSelected().data.ReabEvent_id
					},
					callback: function (success) {
						// console.log('success11=', success);
					}
				});
			},
			//Нет шаблонов - временное решение
			noWorkingPanels: function () {
				//Обрезаем иные профили и этапы
				// Временно

				Ext.getCmp('ufa_personReabRegistryWindow').HeadAnketa = [];
				Ext.getCmp('ufa_personReabRegistryWindow').clickToPN = 0;
				Ext.getCmp('infotabReab').setDisabled(true);
				Ext.getCmp('scalesReab').setDisabled(false);
				//Ext.getCmp('scalesReab').setDisabled(true);
				Ext.getCmp('eventsReab').setDisabled(true);
				//Ext.getCmp('MeasurementsReab').setDisabled(true);
				Ext.getCmp('recommendReab').setDisabled(true);
				//Ext.getCmp('tabpanelReab').hideTabStripItem('scalesReab'); //скрываем панель шкал
				Ext.getCmp('ViewReabDataButton').setDisabled(true);
				Ext.getCmp('addReabDataButton').setDisabled(true);
				Ext.getCmp('saveReabDataButton').setDisabled(true);
				Ext.getCmp('editReabDataButton').setDisabled(true);
				//Ext.getCmp('MKFReab').setDisabled(false);
			},
			// рабочие панели (есть шаблоны)
			workingPanels: function () {
				Ext.getCmp('tabpanelReab').unhideTabStripItem('scalesReab'); // открываем панель шкал
				Ext.getCmp('infotabReab').setDisabled(false);
				Ext.getCmp('scalesReab').setDisabled(false);
				Ext.getCmp('MeasurementsReab').setDisabled(false);
				//Ext.getCmp('MKFReab').setDisabled(false);
				Ext.getCmp('ReabICFVal').setDisabled(false);
				Ext.getCmp('ufa_personReabRegistryWindow').AnketLoad(); //Загрузка Анкеты
				Ext.getCmp('ufa_personReabRegistryWindow').hideShowButtons('infotabReab');

				if (this.ARMType == 'spec_mz')
				{
					Ext.getCmp('addReabDataButton').setDisabled(false);
					Ext.getCmp('addReabDataButton').setDisabled(true);
				}
				else
				{
					Ext.getCmp('addReabDataButton').setDisabled(false);
				}
				// console.log('Ind1212=');
			},
			getAge: function (dateString, TextFieldDate) {
				var day = parseInt(dateString.substr(0, 2));
				var month = parseInt(dateString.substr(3, 2)) - 1;
				var year = parseInt(dateString.substr(6, 4));

				var birthDate = new Date(year, month, day);
				var age = TextFieldDate.getFullYear() - birthDate.getFullYear();
				var m = TextFieldDate.getMonth() - birthDate.getMonth();
				if (m < 0 || (m === 0 && TextFieldDate.getDate() < birthDate.getDate())) {
					age--;
				}
				//console.log('age=',age);
				return age;
			},
			getScalesName: function (cName) {
				//Наименование шкал
				$.ajax({
					mode: "abort",
					type: "post",
					async: false,
					url: '/?c=Ufa_Reab_Register_User&m=getListScales',
					data: {ScaleType_SysNick: cName},
					success: function (response) {
						//Заполним наименование шкал
						var ObjNameScales = Ext.util.JSON.decode(response);
						//console.log('NameScalesdata=',ObjNameScales.data);
						var nRecord = Ext.getCmp('GridReabScales').getGrid().getStore().data.items.length;
						if (nRecord > 0)
						{//Очистка GRIDa
							Ext.getCmp('GridReabScales').getGrid().store.removeAll( );
							nRecord = 0;
						}

						for (jj = 0; jj < ObjNameScales.data.length; jj++)
						{
							Ext.getCmp('GridReabScales').getGrid().store.insert(jj, [new Ext.data.Record({
								Id: nRecord + 1,
								ScaleType_id: ObjNameScales.data[jj].ScaleType_id,
								ScaleType_SysNick: ObjNameScales.data[jj].ScaleType_SysNick,
								ScaleType_Name: ObjNameScales.data[jj].ScaleType_Name
							})]);
						}
					},
					error: function () {
						alert("При обработке запроса на сервере произошла ошибка!");
					}
				});
			},
			//Загрузка справочников определителей ICF
			getICFSpr: function ()
			{
				$.ajax({
					mode: "abort",
					type: "post",
					async: false,
					url: '/?c=Ufa_Reab_Register_User&m=ICFSpr',
					success: function (response) {
						//console.log('response1=', Ext.util.JSON.decode(response));
						var form = Ext.getCmp('ufa_personReabRegistryWindow');
						form.ICFSpr = Ext.util.JSON.decode(response);
					},
					error: function () {
						alert("При обработке запроса справочников определителей ICF на сервере произошла ошибка!");
					}
				});
			},
			//Формирование отображения цветных символов
			getColorSell: function (id)
			{
				//console.log('5555555=');
				//var form = Ext.getCmp('ZNOSuspectRegistry');
				var nRecords = Ext.getCmp(id).ViewGridPanel.getStore().data.items.length;
				if (nRecords > 0)
				{
					//Формируем значения поля Результат
					for (var r = 0; r <= nRecords - 1; r++) {
						Ext.getCmp(id).getGrid().getSelectionModel().selectRow(r);
						var record = Ext.getCmp(id).getGrid().getSelectionModel().getSelected();
						if (record.get('result') != '')
						{
							if (record.get('result') == '!')
							{
								record.set('result', "<span style='color:red;font-size:14px; font-width:bold'>" + record.get('result') + "</span>");
							}
							if (record.get('result') == 'V')
							{
								record.set('result', '<img src="/img/icons/tick16.png" width="12" height="12"/>');
							}
						}
						record.commit();
					}
					Ext.getCmp(id).getGrid().getSelectionModel().deselectRow(nRecords - 1);
				}
				return;
			},

			show: function (params) {
				//console.log('form');
				var body = Ext.getBody();
				var form = this;
				console.log('params',params);

				this.Person_id = params.Person_id;
				this.ARMType = params.ARMType;
				// this.getInfoPacient(params.Person_id); lev
				this.PersonInfoPanelReab.personId = params.Person_id;
				this.PersonInfoPanelReab.serverId = params.Server_id;

				this.PersonInfoPanelReab.setTitle('...');
				this.PersonInfoPanelReab.load({
					callback: function () {
						this.PersonInfoPanelReab.setPersonTitle();
						Ext.getCmp('GridReabUser').setDisabled(false);
					}.createDelegate(this),
					Person_id: this.PersonInfoPanelReab.personId,
					Server_id: this.PersonInfoPanelReab.serverId
				});

				//Подгрузка справочников определителей
				this.getICFSpr();
				this.GridReabObjects.getGrid().getStore().load({
					params: {
						Person_id: params.Person_id
					},
					callback: function (success) {
						//console.log('success11=', success);
						if (success.length > 0)
						{
							//Попробуем сразу сформировать этапы
							Ext.getCmp('ufa_personReabRegistryWindow').setReabStageNew();
							Ext.getCmp('GridReabUser').getGrid().getSelectionModel().deselectRow(Ext.getCmp('GridReabUser').getGrid().getStore().data.items.length - 1);
							//!!!!!!!!!! перезагрузка вкладок(пока 2-х),
						}
					}
				});

				//this.getScalesName();
				//this.resizePanel();

				Ext.getCmp('informReab').removeAll();
				Ext.getCmp('tabpanelReab').setActiveTab(Ext.getCmp('infotabReab'));

				Ext.getCmp('tabpanelReab').hideTabStripItem('eventsReab');
				Ext.getCmp('tabpanelReab').hideTabStripItem('recommendReab');

				//Убираем 2 панели
				this.isButtonAdd = false;//Кнопка "Добавить" не нажата
				this.isButtonEdit = false; //Кнопка "Изменить" не нажата
				this.Templ = ''; //Имя шаблона
				this.TemplObject = ''; //Шаблон
				this.HeadAnketa = []; //список шапок анкет


				//   this.hideShowButtons('');
				if (this.ARMType == 'spec_mz')
				{
					Ext.getCmp('addReabObjectButton').setDisabled(true);
					Ext.getCmp('GridReabUser').ViewActions.action_delete.setHidden(true);
					Ext.getCmp('GridReabUser').ViewActions.action_edit.setHidden(true); //Отмена закрытия этапа
				}

				sw.Promed.ufa_personReabRegistryWindow.superclass.show.apply(this, arguments);

			},
			refresh: function () {
				sw.codeInfo.lastObjectName = this.objectName;
				sw.codeInfo.lastObjectClass = this.objectClass;
				if (sw.Promed.Actions.loadLastObjectCode)
				{
					sw.Promed.Actions.loadLastObjectCode.setHidden(false);
					sw.Promed.Actions.loadLastObjectCode.setText('Обновить ' + this.objectName + ' ...');
				}
				// Удаляем полностью объект из DOM, функционал которого хотим обновить
				this.hide();
				this.close();
				window[this.objectName] = null;
				delete sw.Promed[this.objectName];

			},

			listeners: {
				'render': function () {

				},
				'hide': function () {
					// alert('tttt');
					Ext.getCmp('ufa_personReabRegistryWindow').clickToPN = 0;
//			if (this.refresh)
//				this.onHide();
					this.refresh();

				}
			}

		});
