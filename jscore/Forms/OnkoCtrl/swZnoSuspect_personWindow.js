/**
 * Окно  просмотра специфики регистра ZNO по пациенту
 * пользовательсякая часть 
 *
 *
 * @package      Reab
 * @access       All
 * @autor		
 * @version      24.10.2018
 */

sw.Promed.swZnoSuspect_personWindow = Ext.extend(sw.Promed.BaseForm,
			{
			title: lang['pacient_Registr_ZNO'],
			bodyStyle: 'padding:5px; border: 0px;',
			maximized: true,
			robot: true,
			isUserClick: true, //Кликаем по полям с помощью мыши или таба
			listIDSfocus: [],
			clickToPN: 0,
			closable: true,
			editableForm: true,
			closeAction: 'hide',
			clickToRow: false,
			Reverse: false,
			modal: true,
			Lpu_id: false,
			newAnkets: false,
			// frame: true,
			open1: true, //Первое открытие окна
			buttonAlign: "left",
			objectName: 'swZnoSuspect_personWindow',
			id: 'swZnoSuspect_personWindow',
			objectSrc: '/jscore/Forms/OnkoCtrl/swZnoSuspect_personWindow.js',
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
			
						
			initComponent: function ()
			{
				

				this.GridZnoSuspectObjects = new sw.Promed.ViewFrame({
					id: 'GridZnoSuspectUser',
					hideHeaders: true,
					disabled: true,
					enableColumnHide: true,
					bbar: [],
					contextmenu: true,
					border: false,
					height: Ext.getBody().getHeight() * 0.897,
					object: 'GridZnoSuspectUser',
					dataUrl: '/?c=ZnoSuspectRegister_User&m=getListZnoSuspectUser',
					autoLoadData: false,
					focusOnFirstLoad: false,
					stringfields: [
						{name: 'ZNOSuspectRout_id', type: 'int', header: 'ID'},
						{name: 'Person_id', type: 'int', hidden: true},
						{name: 'Diag_Fid', type: 'int', hidden: true},
						{name: 'ZNOSuspect_happening', type: 'int', hidden: true},
						{name: 'ZNOSuspect_setDate', type: 'date', format: 'd.m.Y', hidden: true},
						{name: 'ZNOSuspect_disDate', type: 'date', format: 'd.m.Y', hidden: true},
						{name: 'Diag_Name', header: 'Наименование', width: '250px', renderer: function (value,p,row) {
								//console.log('value=', value);
								//console.log('row=', row);
								
								if (value)
								{
									var value = value.toUpperCase();
								}
								var t_val = value.split(/\(/);
								var color = '';
								
								switch (row.get('ZNOSuspect_happening') ){
									case 1:
										color = 'red';
										break;
									case 2:
										color = 'blue';
										break;
									case 3:
										color = 'black';
										break;
								}
								return '<div title="' + value + '" style="padding:5px;font-size:11px; font-family:tahoma; text-align:center; color:'+ color + ';font-weight:bold;">' + t_val + '</div>' ;
							}}
					],
					
					actions: [
						{name: 'action_add', hidden: true, text: 'Создать', disabled: true},
						{name: 'action_edit', hidden: true, text: 'Отмена закрытия этапа', disabled: true, handler: function () {
								//Ext.getCmp('GridReabUser').CancelCloseStage();
							}.createDelegate(this)},
						{name: 'action_delete', hidden: true, disabled: true, text: 'Закрытие этапа', iconCls: 'resetsearch16', handler: function () {
								//Ext.getCmp('GridReabUser').CloseStage();
							}.createDelegate(this)},
						{name: 'action_view', hidden: true},
						{name: 'action_refresh', hidden: true},
						{name: 'action_print', hidden: true}
					],
					onLoadData: function () {

					},
					onDblClick: function () {
						//  alert('pppppp');
						return;
					},
					listeners: {
						'render': function () {
							this.getGrid().getTopToolbar().hidden = true;
						}
					},
					clickToRow: function () {
						// alert('tttt');
					}
				});

				// Событие - клик по меню случая
				this.GridZnoSuspectObjects.getGrid().on(
						'rowclick',
						function (grid, row)
						{
							var form = Ext.getCmp('swZnoSuspect_personWindow');
							
							Ext.getCmp('infoRouteZno').setDisabled(false);
							Ext.getCmp('ZNOresearch').setDisabled(false);
							Ext.getCmp('ZnoSuspectWithoutDirect').setDisabled(false);
							Ext.getCmp('tabpanelZnoSuspect').setActiveTab(Ext.getCmp('ZnoSuspectWithoutDirect'));
							Ext.getCmp('tabpanelZnoSuspect').setActiveTab(Ext.getCmp('ZNOresearch'));
							Ext.getCmp('tabpanelZnoSuspect').setActiveTab(Ext.getCmp('infoRouteZno'));
							Ext.getCmp('tabpanelZnoSuspect').doLayout();
							
							
							Ext.getCmp('PersonRoutZno').RefreshRout();  // Маршрутизация
							Ext.getCmp('PersonResearchZno').RefreshResearch();  // Исследования
							Ext.getCmp('PersonWithoutDirectZno').RefreshWithoutDirect();  // Лечение без направления
							
							
							
							//id: 'PersonRoutZno',

							//var directTypeSysNick = this.getSelectionModel().getSelected().get('DirectType_SysNick');
							//var stageTypeId = this.getSelectionModel().getSelected().get('StageType_id');

							//console.log('form.clickToPN=', form.clickToPN);

//							if (form.Templ == directTypeSysNick + stageTypeId && form.clickToPN > 0)
//							{
//								sw.swMsg.alert(lang['soobschenie'], 'Форма уже загружена!');
//								return;
//							} else
//							{
//								
//								
//							}

							//Ext.getCmp('tabpanelReab').hideTabStripItem('MeasurementsReab'); // закрываем панель измерений !!!!!!!!!!!!!!!!!!!! 

							

							//console.log('isSuperAdmin=', isSuperAdmin());


						}
				);

				
				//Панель с перс данными
				this.PersonInfoPanelZnoSuspect = new sw.Promed.PersonInfoPanel({
					floatable: false,
					collapsed: true,
					region: 'north',
					title: lang['zagruzka'],
					plugins: [Ext.ux.PanelCollapsedTitle],
					titleCollapse: true,
					collapsible: true,
					id: 'ZnoSuspectPersonInfoFrame'
				});


				Ext.apply(this, {
					layout: 'border',
					items: [

						this.PersonInfoPanelZnoSuspect,
						{
							xtype: 'panel',
							collapsible: true,
							title: lang['podozrenie']+'/'+lang['diagnoz'], 
							width: 253,
							region: 'west',
							bodyBorder: false,
							id: 'leftPanelZnoSuspect',
							border: false,
							items: [
								{
									xtype: 'panel',
									tbar: [
										{
											xtype: 'button',
											id: 'ZnoSuspect_Emk_Button',
											style: 'position:relative;  left:40px ',
											text: lang['otkryit_emk'],
											tooltip: lang['otkryit_elektronnuyu_meditsinskuyu_kartu_patsienta'],
											iconCls: 'open16',
											hidden: false,
											handler: function () {
												var form = Ext.getCmp('swZnoSuspect_personWindow');
												//console.log('form=',form.params);
												form.emkOpen(form.params);
											}
										}
									],
									items: [Ext.getCmp('swZnoSuspect_personWindow').GridZnoSuspectObjects]
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
							id: 'tabpanelZnoSuspect',
							plain: false,
							//layout: 'border',
							border: false,
							bodyBorder: false,
							autoScroll: true,
							
							activeTab: 0,
							//columnWidth : 1, 
							region: 'center',
							tbar: [	],
							items: [
								{
									title: 'Сведения',
									xtype: 'panel',
									id: 'infoRouteZno',
									disabled: true,
									autoScroll: true,
									items: [
										new Ext.Panel({
													height: 520,
													width: 1250,
													layout: 'border',
													frame: true,
													border: true,
													style: 'border-top: 1px solid #99bbe8; border-left: 1px solid #99bbe8; border-bottom: 1px solid #ffffff;border-right: 1px solid #ffffff; padding: 0px; ',
													items: [
														new sw.Promed.ViewFrame(
																{
																	actions: [
																		{name: 'action_add', hidden: true},
																		{name: 'action_view', hidden: true},
																		{name: 'action_edit', hidden: true}, 
																		{name: 'action_delete', hidden: true},
																		{name: 'action_refresh', hidden: false, handler: function () {
																				Ext.getCmp('PersonRoutZno').RefreshRout();
																			}.createDelegate(this)},
																		{name: 'action_print', hidden: false, handler: function () {
																				alert("Печать");
																				//Ext.getCmp('FuncTestsCns2_id').RefreshTest();
																			}.createDelegate(this)}
																	],
																	autoExpandColumn: 'autoexpand',
																	autoExpandMin: 100,
																	autoLoadData: false,
																	id: 'PersonRoutZno',
																	pageSize: 50,
																	height: 110,
																	//width: 1200,
																	paging: false, // навигатор
																	region: 'center',
																	dataUrl: '/?c=ZnoSuspectRegister_User&m=getListZnoRoutPerson',
																	stringfields: [
																		{name: 'vID', type: 'int', header: 'ID'},
																		{name: 'VizitPL_setDT', type: 'datetime', dateFormat: 'd.m.Y H:i', header: lang['data_i_vremya_provedeniya'],align: 'center', vertical: 'middle', width: 150},
																		{name: 'ReabTestNameId', type: 'string', width: 100, hidden: true}, //1
																		{name: 'LpuInName', type: 'string', header: 'МО',align: 'center', vertical: 'middle', width: 300},
																		{name: 'DiagCode_spid', type: 'string', header: 'Подозрение на <br>диагноз ЗНО',align: 'center', vertical: 'middle', width: 110},
																		{name: 'LpuOutName', type: 'string', header: 'Маршрутизация', align: 'center', vertical: 'middle',width: 300},
																		{name: 'Diag_CodeFin', type: 'string', header: lang['ustanovlennyiy_br_diagnoz'], align: 'center', vertical: 'middle',width: 110},
																		{name: 'confirm', type: 'string', header: 'Подтверждение', align: 'center', vertical: 'middle',width: 120,id: 'autoexpand'},
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
																	},
																	//Обновление GRIDa
																	RefreshRout: function ()
																	{
																		Ext.getCmp('PersonRoutZno').getGrid().getStore().load({
																			params: {
																				Person_id: Ext.getCmp('GridZnoSuspectUser').getGrid().getSelectionModel().getSelected().data.Person_id,
																				ZNOSuspectRout_id: Ext.getCmp('GridZnoSuspectUser').getGrid().getSelectionModel().getSelected().data.ZNOSuspectRout_id
																			},
																			callback: function (success) {
																				// console.log('success11=', success);

//																				var nRec = Ext.getCmp('FuncTestsCns2_id').getGrid().getStore().data.items.length;
//																				if (nRec == 0)
//																				{
//																					Ext.getCmp('FuncTestsCns2_id').getGrid().tbar.dom.firstChild.firstChild.firstChild.firstChild.lastChild.firstChild.innerText = '0 / 0';
//																				} else
//																				{
//																					Ext.getCmp('FuncTestsCns2_id').getGrid().getSelectionModel().selectRow(0);
//																					Ext.getCmp('FuncTestsCns2_id').getGrid().getSelectionModel().deselectRow(0);
//																				}
//																				// console.log('RefreshTest=');
//																				Ext.getCmp('FuncTestsCns2_id').ViewActions.action_delete.setDisabled(true);
//																				Ext.getCmp('FuncTestsCns2_id').ViewActions.action_edit.setDisabled(true);
																			}
																		});
																	},
																	
																})
													]
												})
									],
									listeners: {
										'activate': function (p) {
											// alert('Сведения');
											//console.log('Сведения=', p);
											//Ext.getCmp('swZnoSuspect_personWindow').hideShowButtons(p.id);
//											if (typeof Ext.getCmp('saveReabScaleDataButton') === 'object') {
//												Ext.getCmp('saveReabScaleDataButton').setDisabled(true);
//												Ext.getCmp('addReabDataButton').setDisabled(false);
//											}

										}
									}
								},
								{
									title: lang['issledovaniya'],
									id: 'ZNOresearch',
									disabled: true,
									xtype: 'panel',
									autoScroll: true,
									items: [
										new Ext.Panel({
													height: 520,
													width: 1250,
													layout: 'border',
													frame: true,
													border: true,
													style: 'border-top: 1px solid #99bbe8; border-left: 1px solid #99bbe8; border-bottom: 1px solid #ffffff;border-right: 1px solid #ffffff; padding: 0px; ',
													items: [
														new sw.Promed.ViewFrame(
																{
																	actions: [
																		{name: 'action_add', hidden: true},
																		{name: 'action_view', hidden: true},
																		{name: 'action_edit', hidden: true}, 
																		{name: 'action_delete', hidden: true},
																		{name: 'action_refresh', hidden: false, handler: function () {
																				Ext.getCmp('PersonResearchZno').RefreshResearch();
																			}.createDelegate(this)},
																		{name: 'action_print', hidden: false, handler: function () {
																				alert("Печать");
																				//Ext.getCmp('FuncTestsCns2_id').RefreshTest();
																			}.createDelegate(this)}
																	],
																	autoExpandColumn: 'autoexpand',
																	autoExpandMin: 100,
																	autoLoadData: false,
																	id: 'PersonResearchZno',
																	pageSize: 50,
																	height: 110,
																	//width: 1200,
																	paging: false, // навигатор
																	region: 'center',
																	dataUrl: '/?c=ZnoSuspectRegister_User&m=getListZnoResearchPerson',
																	stringfields: [
																		{name: 'vID', type: 'int', header: 'ID'},
																		{name: 'EvnUsluga_setDT', type: 'datetime', dateFormat: 'd.m.Y H:i', header: lang['data_i_vremya_provedeniya'],align: 'center', vertical: 'middle', width: 150},
																		{name: 'Lpu_Nick', type: 'string', header: 'МО',align: 'center', vertical: 'middle', width: 300},
																		{name: 'MedPerson_FIO', type: 'string', header: lang['vrach'], align: 'center', vertical: 'middle',width: 300},
																		{name: 'UslugaComplex_Code', type: 'string', header: lang['kod_uslugi'],align: 'center', vertical: 'middle', width: 150},
																		{name: 'UslugaComplex_Name', type: 'string', header: lang['naimenovanie'], align: 'center', vertical: 'middle',width: 300,id: 'autoexpand'},
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
																		
																	},
																	//Обновление GRIDa
																	RefreshResearch: function ()
																	{
																		Ext.getCmp('PersonResearchZno').getGrid().getStore().load({
																			params: {
																				Person_id: Ext.getCmp('GridZnoSuspectUser').getGrid().getSelectionModel().getSelected().data.Person_id,
																				ZNOSuspect_setDate: Ext.getCmp('GridZnoSuspectUser').getGrid().getSelectionModel().getSelected().data.ZNOSuspect_setDate,
																				ZNOSuspect_disDate: Ext.getCmp('GridZnoSuspectUser').getGrid().getSelectionModel().getSelected().data.ZNOSuspect_disDate
																			},
																			callback: function (success) {
																				 console.log('success11=', success);

//																				
																			}
																		});
																	},
																})
													]
												})
									],
									listeners: {
										'activate': function (p) {
											//Ext.getCmp('swZnoSuspect_personWindow').hideShowButtons(p.id);
											Ext.getCmp('ZNOresearch').doLayout();
										}
									}
								},
								{
									title: 'Случаи лечения без направления',
									id: 'ZnoSuspectWithoutDirect',
									xtype: 'panel',
									disabled: true,
									autoScroll: true,
									items: [
										new Ext.Panel({
													height: 520,
													width: 1250,
													layout: 'border',
													frame: true,
													border: true,
													style: 'border-top: 1px solid #99bbe8; border-left: 1px solid #99bbe8; border-bottom: 1px solid #ffffff;border-right: 1px solid #ffffff; padding: 0px; ',
													items: [
														new sw.Promed.ViewFrame(
																{
																	actions: [
																		{name: 'action_add', hidden: true},
																		{name: 'action_view', hidden: true},
																		{name: 'action_edit', hidden: true}, 
																		{name: 'action_delete', hidden: true},
																		{name: 'action_refresh', hidden: false, handler: function () {
																				Ext.getCmp('PersonWithoutDirectZno').RefreshWithoutDirect();
																			}.createDelegate(this)},
																		{name: 'action_print', hidden: false, handler: function () {
																				alert("Печать");
																				//Ext.getCmp('FuncTestsCns2_id').RefreshTest();
																			}.createDelegate(this)}
																	],
																	autoExpandColumn: 'autoexpand',
																	autoExpandMin: 100,
																	autoLoadData: false,
																	id: 'PersonWithoutDirectZno',
																	pageSize: 50,
																	height: 110,
																	//width: 1200,
																	paging: false, // навигатор
																	region: 'center',
																	dataUrl: '/?c=ZnoSuspectRegister_User&m=getListPersonZnoWithoutDirect',
																	stringfields: [
																		{name: 'vID', type: 'int', header: 'ID'},
																		{name: 'EvnVizitPL_setDT', type: 'datetime', dateFormat: 'd.m.Y H:i', header: lang['data_i_vremya_provedeniya'],align: 'center', vertical: 'middle', width: 150},
																		{name: 'Lpu_Nick', type: 'string', header: 'МО',align: 'center', vertical: 'middle', width: 300},
																		{name: 'MedPerson_FIO', type: 'string', header: lang['vrach'], align: 'center', vertical: 'middle',width: 400},
																		{name: 'Diag_Code', type: 'string', header: lang['diagnoz_predvaritelniy'], align: 'center', vertical: 'middle',width: 150,id: 'autoexpand'}
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
																		
																	},
																	//Обновление GRIDa
																	RefreshWithoutDirect: function ()
																	{
																		Ext.getCmp('PersonWithoutDirectZno').getGrid().getStore().load({
																			params: {
																				Person_id: Ext.getCmp('GridZnoSuspectUser').getGrid().getSelectionModel().getSelected().data.Person_id,
																				ZNOSuspect_setDate: Ext.getCmp('GridZnoSuspectUser').getGrid().getSelectionModel().getSelected().data.ZNOSuspect_setDate,
																				ZNOSuspect_disDate: Ext.getCmp('GridZnoSuspectUser').getGrid().getSelectionModel().getSelected().data.ZNOSuspect_disDate
																			},
																			callback: function (success) {
																				// console.log('success11=', success);

//																				
																			}
																		});
																	},
																})
													]
												})
									],
									
									listeners: {
										'activate': function (p) {
											Ext.getCmp('ZnoSuspectWithoutDirect').doLayout();
										}
									}
								},
								{
									title: 'Лекарственное лечение',
									//id: 'MeasurementsReab',
									xtype: 'panel',
									disabled: true,
									autoScroll: true,
									items: [
										
									],
									listeners: {
										'activate': function (p) {
											//Ext.getCmp('swZnoSuspect_personWindow').hideShowButtons(p.id);
											// Загрузка справочников
											

										}
									}
								}
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
								Ext.getCmp('swZnoSuspect_personWindow').refresh();
							}
						}
					],
					//Открытие электронной карты
					emkOpen: function (params)
					{
						if (getWnd('swPersonEmkWindow').isVisible()) {
							sw.swMsg.alert(langs('Сообщение'), lang['emk'] + ' уже открыта'); //lang['emk'] = 'ЭМК';
							return false;
						}
						
						getWnd('swPersonEmkWindow').show({
							Person_id: params.Person_id,
							Server_id: params.Server_id,
							PersonEvn_id: params.PersonEvn_id,
							userMedStaffFact: params.userMedStaffFact,
							MedStaffFact_id: params.MedStaffFact_id,
							LpuSection_id: params.LpuSection_id,
							ARMType: 'common',
							callback: function ()
							{
								//
							}.createDelegate(this)
						});
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

				sw.Promed.swZnoSuspect_personWindow.superclass.initComponent.apply(this, arguments);
			},
			
			
			//Нет шаблонов - временное решение
			noWorkingPanels: function () {
				//Обрезаем иные профили и этапы
				// Временно

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
				//Ext.getCmp('tabpanelReab').unhideTabStripItem('scalesReab'); // открываем панель шкал
				Ext.getCmp('infotabReab').setDisabled(false);
				Ext.getCmp('scalesReab').setDisabled(false);
				Ext.getCmp('MeasurementsReab').setDisabled(false);
				Ext.getCmp('addReabDataButton').setDisabled(false);
				// console.log('Ind1212=');
			},
			
			show: function (params) {
				var body = Ext.getBody();
				var form = this;
				//console.log('form',form);

				this.params = params;

				this.Person_id = params.Person_id;
				this.PersonInfoPanelZnoSuspect.personId = params.Person_id;
				this.PersonInfoPanelZnoSuspect.serverId = params.Server_id;

				this.PersonInfoPanelZnoSuspect.setTitle('...');
				this.PersonInfoPanelZnoSuspect.load({
					callback: function () {
						this.PersonInfoPanelZnoSuspect.setPersonTitle();
						Ext.getCmp('GridZnoSuspectUser').setDisabled(false);
					}.createDelegate(this),
					Person_id: this.PersonInfoPanelZnoSuspect.personId,
					Server_id: this.PersonInfoPanelZnoSuspect.serverId
				});


				this.GridZnoSuspectObjects.getGrid().getStore().load({
					params: {
						Person_id: params.Person_id
					},
					callback: function (success) {
						//console.log('success11=', success);
						if (success.length > 0)
						{
							Ext.getCmp('GridZnoSuspectUser').getGrid().getSelectionModel().deselectRow(Ext.getCmp('GridZnoSuspectUser').getGrid().getStore().data.items.length - 1);
						}
					}
				});

				

//				Ext.getCmp('informReab').removeAll();
//				Ext.getCmp('tabpanelReab').setActiveTab(Ext.getCmp('infotabReab'));
//				
//				Ext.getCmp('tabpanelReab').hideTabStripItem('eventsReab');
//				Ext.getCmp('tabpanelReab').hideTabStripItem('recommendReab');




						//   this.hideShowButtons('');

						sw.Promed.swZnoSuspect_personWindow.superclass.show.apply(this, arguments);
				// Ext.getCmp('scaleReabRightPan').hide();
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
					
					this.refresh();

				}
			}

		});


