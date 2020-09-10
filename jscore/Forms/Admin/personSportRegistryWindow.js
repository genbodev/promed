/**
 * Окно редактирования УМО спортсмена
 *
 *
 * @package      SportRegistry
 * @access       public
 * @autor        Хамитов Марат
 * @version      12.2018
 */

sw.Promed.personSportRegistryWindow = Ext.extend(sw.Promed.BaseForm,
	{
		title: langs('УМО спортсмена'),
		bodyStyle: 'padding:5px; border: 0px;',
		maximized: true,
		robot: true,
		closable: true,
		editableForm: true,
		closeAction: 'hide',
		clickToRow: false,
		Reverse: false,
		modal: true,
		Lpu_id: false,
		frame: true,
		exceptionalCase: false,
		buttonAlign: "left",
		pageMedPersonalP: 0,
		pageMedPersonalS: 0,
		objectName: 'personSportRegistryWindow',
		id: 'personSportRegistryWindow',
		objectSrc: '/jscore/Forms/Admin/personSportRegistryWindow.js',
		resizePanel: function () {
			var widthp = Ext.getBody().getWidth() - 10;
			Ext.getCmp('mainPanel').setWidth(widthp);
			var heightp = Ext.getBody().getHeight();
			if (Ext.getCmp('infoPacient').collapsed) {
				heightp = heightp - 115;
			} else {
				heightp = heightp - 230;
			}
			Ext.getCmp('mainPanel').setHeight(heightp);
		},
		initComponent: function () {
			var wnd = this;

			this.GridObjects = new sw.Promed.ViewFrame({
				id: 'GridObjectsUser',
				hideHeaders: true,
				disabled: false,
				enableColumnHide: true,
				contextmenu: false,
				border: false,
				height: Ext.getBody().getHeight() * 0.897,
				object: 'GridObjectsUser',
				dataUrl: '/?c=SportRegister&m=getPersonUMODates',
				autoLoadData: false,
				focusOnFirstLoad: false,
				stringfields: [
					{
						name: 'SportRegisterUMO_id',
						header: 'Идентификатор УМО',
						hidden: true
					},
					{
						name: 'SportRegisterUMO_UMODate',
						header: 'Дата УМО',
						width: 100,
						renderer: function (value) {
							if (value) {
								var test = new Date(value).format("d.m.Y");
								return '<div title="' + value + '" style="padding:5px; font-size:12px; font-family:tahoma; float:left; display:block; font-weight: bold">' + value + '</div>';
							} else {
								return '<div title=" — " style="padding:5px; font-size:12px; font-family:tahoma; float:left; display:block; font-weight: bold"> — </div>';
							}
						}
					},
					{
						name: 'SportType_name',
						header: 'Вид спорта',
						width: 190,
						renderer: function (value) {
							if (value) {
								return '<div title="' + value + '" style="padding:5px; font-size:12px; font-family:tahoma; float:left; display:block; font-weight: bold">' + value + '</div>';
							} else {
								return '<div title="' + langs('Нет данных') + '" style="padding:5px; font-size:12px; font-family:tahoma; float:left; display:block; font-weight: bold">' + langs('Нет данных') + '</div>';
							}
						}
					}, {
						name: 'SportRegisterUMO_delDT',
						header: 'Дата удаления УМО',
						width: 200,
						hidden: true
					}
				],
				onLoadData: function () {
					var index = personSportRegistryWindow.GridObjects.getGrid().store.data.findIndex('id', personSportRegistryWindow.currentSportRegisterUMO_id);
					personSportRegistryWindow.GridObjects.getGrid().getSelectionModel().selectRow(index);
					for (let i = this.getGrid().getStore().data.length - 1; i >=0; i-=1) {
						personSportRegistryWindow.GridObjects.getGrid().getStore().data.items[i].data.SportRegisterUMO_delDT ? personSportRegistryWindow.GridObjects.getGrid().getStore().removeAt(i) : '';
					}
					if (this.getGrid().getStore().data.items.length != 0) {
						personSportRegistryWindow.lastSportRegisterUMO_UMODate = this.getGrid().getStore().data.items[0].data.SportRegisterUMO_UMODate ? this.getGrid().getStore().data.items[0].data.SportRegisterUMO_UMODate : '';
					} else {
						personSportRegistryWindow.exceptionalCase = true; // устанавливаем исключительный случай (переписывать дату включения спортмсена без активных УМО в регистр при добавлении нового УМО + присутсвии уже удаленных УМО)
						personSportRegistryWindow.GridObjects.getGrid().getStore().loadData([]);
					}
				},
				onDblClick: function () {

				},
				listeners: {
					'render': function () {
						this.getGrid().getTopToolbar().hidden = true;
						//
					}
				}
			});

			this.GridObjects.getGrid().on(
				'rowclick',
				function (grid, row) {

					var SportRegisterUMO_id = this.getSelectionModel().getSelected().get('SportRegisterUMO_id');
					var SportRegisterUMO_UMODate = this.getSelectionModel().getSelected().get('SportRegisterUMO_UMODate');

					personSportRegistryWindow.currentSportRegisterUMO_id = SportRegisterUMO_id;
					personSportRegistryWindow.currentSportRegisterUMO_UMODate = SportRegisterUMO_UMODate;
					
					console.log('Режим isEdit ' + Ext.getCmp('editSportDataButton').disabled);
					if (Ext.getCmp('editSportDataButton').disabled == true) {
						sw.swMsg.show({
						buttons: Ext.Msg.YESNO,
						fn: function (buttonId, text, obj) {
							if (buttonId == 'yes') {
								Ext.Ajax.request({
									url: '/?c=SportRegister&m=getSportRegisterUMO',
									params: {
										SportRegisterUMO_id: SportRegisterUMO_id
									},
									callback: function (options, success, response) {
										if (success === true) {
											var resp = Ext.util.JSON.decode(response.responseText);
											//console.log('resp', resp[0]);
											personSportRegistryWindow.fillSportRegisterUMOFields(resp[0]);
											personSportRegistryWindow.editMode(false, false);
											console.log('[0] ' + personSportRegistryWindow.GridObjects.getGrid().getStore().data.items[0].id);
											if (personSportRegistryWindow.GridObjects.getGrid().getStore().data.items[0].id == resp[0].SportRegisterUMO_id) {
												Ext.getCmp('deleteSportRegisterUMO').enable();
											} else {
												Ext.getCmp('deleteSportRegisterUMO').disable();
											}
											//Ext.getCmp('deleteSportRegister').setVisible(true);
										} else {
											return false;
										}
									}
								});
							}
						}.createDelegate(this),
						icon: Ext.MessageBox.QUESTION,
						msg: langs('Открыть УМО спортсмена за ' + SportRegisterUMO_UMODate + '? Все несохраненные данные будут потеряны.'),
						title: langs('Предупреждение')
					});
					} else {
					Ext.Ajax.request({
									url: '/?c=SportRegister&m=getSportRegisterUMO',
									params: {
										SportRegisterUMO_id: SportRegisterUMO_id
									},
									callback: function (options, success, response) {
										if (success === true) {
											var resp = Ext.util.JSON.decode(response.responseText);
											//console.log('resp', resp[0]);
											personSportRegistryWindow.fillSportRegisterUMOFields(resp[0]);
											personSportRegistryWindow.editMode(false, false);
											console.log('[0] ' + personSportRegistryWindow.GridObjects.getGrid().getStore().data.items[0].id);
											if (personSportRegistryWindow.GridObjects.getGrid().getStore().data.items[0].id == resp[0].SportRegisterUMO_id) {
												Ext.getCmp('deleteSportRegisterUMO').enable();
											} else {
												Ext.getCmp('deleteSportRegisterUMO').disable();
											}
											//Ext.getCmp('deleteSportRegister').setVisible(true);
										} else {
											return false;
										}
									}
								});
				}
			}
			);
			var form = this;
			
			//Панель с перс данными
			this.PersonInfoPanel = new sw.Promed.PersonInfoPanel({
				floatable: false,
				collapsed: true,
				region: 'north',
				title: lang['zagruzka'],
				plugins: [Ext.ux.PanelCollapsedTitle],
				titleCollapse: true,
				collapsible: true,
				id: 'infoPacient'
			});
		
			Ext.apply(this,
				{	layout: 'border',
					items: [
						/*{
							xtype: 'panel',
							collapsible: false,
							collapsed: true,
							id: 'infoPacient',
							title: langs('Ожидание данных...'),
							style: 'cursor:pointer',
							layout: 'column',
							frame: true,
							items: [
								{
									xtype: 'panel',
									border: false,
									defaults: {
										style: 'background-color:#99BBE8;',
										xtype: 'button'
									},
									align: 'right',
									style: 'float:right!important; margin-top:-100px; width:100%; padding-left:90%',
									items: [{
										disabled: false,
										width: '130px',
										handler: function () {
											Ext.getCmp('personSportRegistryWindow').panelButtonClick(1);
										},
										text: BTN_PERSCARD,
										iconCls: 'pers-card16',
										tooltip: BTN_PERSCARD_TIP
									}, {
										disabled: false,
										width: '130px',
										handler: function () {
											Ext.getCmp('personSportRegistryWindow').panelButtonClick(2);
										},
										text: BTN_PERSEDIT,
										iconCls: 'edit16',
										tooltip: BTN_PERSEDIT_TIP
									}, {
										disabled: false,
										width: '130px',
										handler: function () {
											Ext.getCmp('personSportRegistryWindow').panelButtonClick(3);
										},
										text: BTN_PERSCUREHIST,
										iconCls: 'pers-curehist16',
										tooltip: BTN_PERSCUREHIST_TIP
									}, {
										disabled: false,
										width: '130px',
										handler: function () {
											Ext.getCmp('personSportRegistryWindow').panelButtonClick(4);
										},
										text: BTN_PERSPRIV,
										iconCls: 'pers-priv16',
										tooltip: BTN_PERSPRIV_TIP
									}, {
										disabled: false,
										width: '130px',
										handler: function () {
											Ext.getCmp('personSportRegistryWindow').panelButtonClick(5);
										},
										text: BTN_PERSDISP,
										iconCls: 'pers-disp16',
										tooltip: BTN_PERSDISP_TIP
									}]
								}
							],
							listeners: {

								'render': function (panel) {
									panel.header.on('click', function () {
										if (panel.collapsed) {
											panel.expand();

										} else {
											panel.collapse();

										}
									});
								},
								'expand': function (p) {
									Ext.getCmp('personSportRegistryWindow').resizePanel();
								},
								'collapse': function (p) {
									Ext.getCmp('personSportRegistryWindow').resizePanel();
								}
							}
						},*/
					this.PersonInfoPanel,
						{
							xtype: 'panel',
							region: 'center',
							layout: 'border',
							id: 'mainPanel',
							items: [
								{
									xtype: 'panel',
									collapsible: true,
									title: langs('УМО'),
									width: 280,
									region: 'west',
									bodyBorder: false,
									id: 'leftPanelmenu',
									border: false,
									items: [
										{
											items: [Ext.getCmp('personSportRegistryWindow').GridObjects]
										}],
									listeners: {}
								},
								{
									xtype: 'tabpanel',
									id: 'tabpanelSport',
									plain: false,
									border: false,
									bodyBorder: false,
									autoScroll: false,
									activeTab: 0,
									region: 'center',
									tbar: [
										{
											xtype: 'button',
											text: langs('Добавить'),
											id: 'addSportDataButton',
											iconCls: 'add16',
											disabled: true,
											handler: function () {
												sw.swMsg.show({
													buttons: Ext.Msg.YESNO,
													fn: function (buttonId, text, obj) {
														if (buttonId == 'yes') {
															personSportRegistryWindow.editMode(true, true);
															//debugger;
															if (personSportRegistryWindow.currentSportRegisterUMO_id) {
																Ext.Ajax.request({
																	url: '/?c=SportRegister&m=getSportRegisterUMO',
																	params: {
																		SportRegisterUMO_id: personSportRegistryWindow.currentSportRegisterUMO_id
																	},
																	callback: function (options, success, response) {
																		if (success === true) {
																			var resp = Ext.util.JSON.decode(response.responseText);
																			console.log('resp', resp[0]);
																			personSportRegistryWindow.fillSportRegisterUMOFields(resp[0]);
																			Ext.getCmp('SportRegisterUMO_AdmissionDtBeg').setValue('');
																			Ext.getCmp('SportRegisterUMO_AdmissionDtEnd').setValue('');
																			Ext.getCmp('UMOResult').setValue('');
																			Ext.getCmp('UMOResult_comment').setValue('');
																		} else {
																			return false;
																		}
																	}
																});
															}
														}
													}.createDelegate(this),
													icon: Ext.MessageBox.QUESTION,
													msg: langs('Перейти в режим добавления?'),
													title: langs('Предупреждение')
												});
											},
										},
										{
											xtype: 'button',
											text: langs('Сохранить'),
											id: 'saveSportDataButton',
											iconCls: 'save16',
											disabled: false,
											handler: function () {
												if (personSportRegistryWindow.currentSportRegisterUMO_UMODate != personSportRegistryWindow.lastSportRegisterUMO_UMODate && !personSportRegistryWindow.isAdding) {
													sw.swMsg.show({
														buttons: Ext.Msg.OK,
														icon: Ext.MessageBox.WARNING,
														msg: langs('Сохранение УМО тоже заблокировано!'),
														title: langs('Предупреждение')
													});
													this.setValue(oldVal);
												} else {
													if (personSportRegistryWindow.checkRequiredField()) {
														sw.Promed.vac.utils.msgBoxNoValidForm();
													} else personSportRegistryWindow.isAdding ? personSportRegistryWindow.addSportRegisterUMO() : personSportRegistryWindow.updateSportRegisterUMO();
												}
											}
										},
										{
											xtype: 'button',
											text: langs('Изменить'),
											disabled: true,
											id: 'editSportDataButton',
											iconCls: 'edit16',
											handler: function () {
												if (personSportRegistryWindow.currentSportRegisterUMO_UMODate != personSportRegistryWindow.lastSportRegisterUMO_UMODate && !personSportRegistryWindow.isAdding) {
													sw.swMsg.show({
														buttons: Ext.Msg.OK,
														icon: Ext.MessageBox.WARNING,
														msg: langs('Редактирование УМО заблокировано!'),
														title: langs('Предупреждение')
													});
													this.setValue(oldVal);
												} else {
													sw.swMsg.show({
														buttons: Ext.Msg.YESNO,
														fn: function (buttonId, text, obj) {
															if (buttonId == 'yes') {
																personSportRegistryWindow.editMode(true, false);
															}
														}.createDelegate(this),
														icon: Ext.MessageBox.QUESTION,
														msg: langs('Перейти в режим редактирования?'),
														title: langs('Предупреждение')
													});
												}
											},
										},
										{
											xtype: 'button',
											text: langs('Удалить'),
											disabled: true,
											id: 'deleteSportRegisterUMO',
											iconCls: 'delete16',
											handler: function () {
												personSportRegistryWindow.deleteSportRegisterUMO(personSportRegistryWindow.GridObjects.getGrid().getSelectionModel().getSelected().id)
											},
										},
										{
											xtype: 'button',
											text: langs('Печать'),
											id: 'printSportDataButton',
											iconCls: 'print16',
											handler: function() {
												window.open('/?c=SportRegister&m=PrintSportRegisterUMO&SportRegisterUMO_id=' + personSportRegistryWindow.GridObjects.getGrid().getSelectionModel().getSelected().id);
												//wnd.printHtml();
											}.createDelegate(this)
										},
										{
											xtype: 'button',
											text: langs('Открыть ЭМК'),
											id: 'open_emk',
											iconCls: 'open16',
											tooltip: langs('Открыть электронную медицинскую карту пациента'),
											handler: function () {
												getWnd('swPersonEmkWindow').show({
													Person_id: personSportRegistryWindow.Person_id,
													//Server_id: record.get('Server_id'),
													//PersonEvn_id: record.get('PersonEvn_id'),
													//usergetMedPersonalP: this.usergetMedPersonalP,
													//getMedPersonalP_id: this.usergetMedPersonalP.getMedPersonalP_id,
													//LpuSection_id: this.usergetMedPersonalP.LpuSection_id,
													ARMType: 'common',
													callback: function () {

													}.createDelegate(this)
												});
											}.createDelegate(this)
										},
										{
											xtype: 'button',
											text: langs('Исключить из регистра'),
											hidden: true,
											id: 'deleteSportRegister',
											iconCls: 'delete16',
											handler: function () {
												var params = {
													SportRegister_id: personSportRegistryWindow.SportRegister_id,
													Person_id: personSportRegistryWindow.Person_id
												}
												getWnd('swSportRegistryOutCause').show(params);
											},
										},
										{
											xtype: 'button',
											text: langs('Восстановить в регистр'),
											hidden: true,
											id: 'restoreSportRegister',
											iconCls: 'refresh16',
											handler: function () {
												var params = {
													SportRegister_id: personSportRegistryWindow.SportRegister_id
												}
												personSportRegistryWindow.restoreSportRegister(params);
											},
										}
									],
									items: [
										{
											title: langs('Сведения'),
											xtype: 'panel',
											layout: 'fit',
											border: false,
											id: 'infotab',
											autoScroll: true,
											items: [
												{
													id: 'information',
													border: false,
													layout: 'border',
													bodyStyle: 'width: 100%; height: 100%',
													items: [{
														layout: 'form',
														region: 'center',
														border: false,
														bodyStyle: 'padding: 20px',
														items: [{
															layout: 'table',
															border: false,
															defaults: {
																bodyStyle: 'height: 20px; padding: 7px;',
															},
															layoutConfig: {
																columns: 2
															},
															items: [{
																html: '<p>Дата УМО</p>',
																width: 230,
																bodyStyle: 'background-color: #dedede; height: 20px; padding: 7px; font-weight: bold'
															}, {
																width: 500,
																bodyStyle: 'background-color: #dedede; height: 20px; padding: 7px; font-weight: bold',
																items: [
																	{
																		id: 'SportRegisterUMO_UMODate',
																		allowBlank: false,
																		disabled: true,
																		bodyStyle: 'padding: 0',
																		format: 'd.m.Y',
																		plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
																		width: 90,
																		xtype: 'datefield',
																		listeners: {
																			
																		},
																	}
																]
															}, {
																html: '<p>Возраст спортсмена</p>',
																bodyStyle: 'height: 20px; padding: 7px; font-weight: bold',
															}, {
																width: 500,
																items: [
																	{
																		id: 'Person_age',
																		width: 90,
																		xtype: 'textfield',
																		disabled: true,
																	}
																]
															}, {
																html: '<p>Группа инвалидности</p>',
																bodyStyle: 'height: 20px; padding: 7px; font-weight: bold',
															}, {
																width: 500,
																items: [{
																	mode: 'local',
																	id: 'InvalidGroupType',
																	allowBlank: false,
																	disabled: true,
																	store: new Ext.data.JsonStore({
																		url: '/?c=SportRegister&m=getDisabilityGroup',
																		autoLoad: true,
																		fields: [
																			{name: 'InvalidGroupType_id', type: 'int'},
																			{
																				name: 'InvalidGroupType_Name',
																				type: 'string'
																			}
																		],
																		key: 'InvalidGroupType_id',
																	}),
																	editable: false,
																	triggerAction: 'all',
																	hiddenName: 'InvalidGroupType_id',
																	displayField: 'InvalidGroupType_Name',
																	valueField: 'InvalidGroupType_id',
																	width: 200,
																	xtype: 'combo',
																	tpl: '<tpl for="."><div class="x-combo-list-item">' +
																		'{InvalidGroupType_Name} ' + '&nbsp;' +
																		'</div></tpl>',
																}]
															}, {
																html: '<p>Паралимпийская группа</p>',
																bodyStyle: 'height: 20px; padding: 7px; font-weight: bold',
															}, {
																width: 500,
																items: [{
																	mode: 'local',
																	id: 'SportParaGroup',
																	allowBlank: false,
																	disabled: true,
																	store: new Ext.data.JsonStore({
																		url: '/?c=SportRegister&m=getSportParaGroup',
																		autoLoad: true,
																		fields: [
																			{
																				name: 'SportParaGroup_id',
																				type: 'int'
																			},
																			{
																				name: 'SportParaGroup_name',
																				type: 'string'
																			}
																		],
																		key: 'SportParaGroup_id',
																	}),
																	editable: false,
																	triggerAction: 'all',
																	hiddenName: 'SportParaGroup_id',
																	displayField: 'SportParaGroup_name',
																	valueField: 'SportParaGroup_id',
																	width: 200,
																	xtype: 'combo',
																	tpl: '<tpl for="."><div class="x-combo-list-item">' +
																		'{SportParaGroup_name} ' + '&nbsp;' +
																		'</div></tpl>',
																}]
															}, {
																html: '<p>Сборник</p>',
																bodyStyle: 'height: 20px; padding: 7px; font-weight: bold',
															}, {
																width: 500,
																items: [{
																	xtype: 'combo',
																	hiddenName: 'SportRegisterUMO_IsTeamMember_id',
																	labelAlign: 'left',
																	editable: false,
																	id: 'SportRegisterUMO_IsTeamMember',
																	allowBlank: false,
																	disabled: true,
																	mode: 'local',
																	width: 50,
																	triggerAction: 'all',
																	allowBlank: false,
																	store: new Ext.data.SimpleStore({
																		fields: [
																			{name: 'SportRegisterUMO_IsTeamMember_id', type: 'int'},
																			{
																				name: 'SportRegisterUMO_IsTeamMember_Name',
																				type: 'string'
																			}
																		],
																		data: [
																			['1', langs('Нет')],
																			['2', langs('Да')]
																		],
																	}),
																	displayField: 'SportRegisterUMO_IsTeamMember_Name',
																	valueField: 'SportRegisterUMO_IsTeamMember_id',
																	listeners: {
																		scope: this,
																		'render': function () {
																			Ext.getCmp('SportRegisterUMO_IsTeamMember').setValue(null);
																		}
																	}
																}]
															}, {
																html: '<p>ФИО врача</p>',
																bodyStyle: 'height: 20px; padding: 7px; font-weight: bold',
															}, {
																width: 500,
																items: [{
																	mode: 'local',
																	id: 'MedPersonalP',
																	allowBlank: false,
																	disabled: true,
																	store: new Ext.data.SimpleStore({
																		//url: '/?c=SportRegister&m=getMedPersonalP',
																		//autoLoad: true,
																		fields: [
																			{name: 'MedPersonal_pid', type: 'string'},
																			{name: 'MedPersonal_pname', type: 'string'}
																		],
																		key: 'MedPersonal_pid',
																	}),
																	editable: true,
																	triggerAction: 'all',
																	hiddenName: 'MedPersonal_pid',
																	displayField: 'MedPersonal_pname',
																	valueField: 'MedPersonal_pid',
																	style: 'float: left;',
																	width: 350,
																	xtype: 'combo',
																	tpl: '<tpl for="."><div class="x-combo-list-item">' +
																		'{MedPersonal_pname} ' + '&nbsp;' +
																		'</div></tpl>',
																}]
															}, {
																html: '<p>ФИО медсестры</p>',
																bodyStyle: 'height: 20px; padding: 7px; font-weight: bold',
															}, {
																width: 500,
																items: [{
																	mode: 'local',
																	id: 'MedPersonalS',
																	allowBlank: false,
																	disabled: true,
																	store: new Ext.data.SimpleStore({
																		//url: '/?c=SportRegister&m=getMedPersonalS',
																		//autoLoad: true,
																		fields: [
																			{name: 'MedPersonal_sid', type: 'int'},
																			{name: 'MedPersonal_sname', type: 'string'}
																		],
																		key: 'MedPersonal_sid',
																	}),
																	editable: true,
																	triggerAction: 'all',
																	hiddenName: 'MedPersonal_sid',
																	displayField: 'MedPersonal_sname',
																	valueField: 'MedPersonal_sid',
																	style: 'float: left;',
																	width: 350,
																	xtype: 'combo',
																	tpl: '<tpl for="."><div class="x-combo-list-item">' +
																		'{MedPersonal_sname} ' + '&nbsp;' +
																		'</div></tpl>',
																}]
															}, {
																html: '<p>Вид спорта</p>',
																bodyStyle: 'height: 20px; padding: 7px; font-weight: bold',
															}, {
																width: 500,
																items: [{
																	mode: 'local',
																	id: 'SportType',
																	allowBlank: false,
																	disabled: true,
																	store: new Ext.data.JsonStore({
																		url: '/?c=SportRegister&m=getSportType',
																		autoLoad: true,
																		fields: [
																			{name: 'SportType_id', type: 'int'},
																			{name: 'SportType_name', type: 'string'}
																		],
																		key: 'SportType_id',
																	}),
																	editable: true,
																	triggerAction: 'all',
																	hiddenName: 'SportType_id',
																	displayField: 'SportType_name',
																	valueField: 'SportType_id',
																	width: 350,
																	xtype: 'combo',
																	tpl: '<tpl for="."><div class="x-combo-list-item">' +
																		'{SportType_name} ' + '&nbsp;' +
																		'</div></tpl>',
																}]
															}, {
																html: '<p>Спортивная организация</p>',
																bodyStyle: 'height: 20px; padding: 7px; font-weight: bold',
															}, {
																width: 500,
																items: [{
																	mode: 'local',
																	id: 'SportOrg',
																	allowBlank: false,
																	disabled: true,
																	store: new Ext.data.JsonStore({
																		url: '/?c=SportRegister&m=getSportOrg',
																		autoLoad: true,
																		fields: [
																			{name: 'SportOrg_id', type: 'int'},
																			{name: 'SportOrg_name', type: 'string'}
																		],
																		key: 'SportOrg_id',
																	}),
																	editable: true,
																	triggerAction: 'all',
																	hiddenName: 'SportOrg_id',
																	displayField: 'SportOrg_name',
																	valueField: 'SportOrg_id',
																	width: 350,
																	xtype: 'combo',
																	tpl: '<tpl for="."><div class="x-combo-list-item">' +
																		'{SportOrg_name} ' + '&nbsp;' +
																		'</div></tpl>',
																}]
															}, {
																html: '<p>Спортивный разряд</p>',
																bodyStyle: 'height: 20px; padding: 7px; font-weight: bold',
															}, {
																width: 500,
																items: [{
																	mode: 'local',
																	id: 'SportCategory',
																	allowBlank: false,
																	disabled: true,
																	store: new Ext.data.JsonStore({
																		url: '/?c=SportRegister&m=getSportCategory',
																		autoLoad: true,
																		fields: [
																			{name: 'SportCategory_id', type: 'int'},
																			{name: 'SportCategory_name', type: 'string'}
																		],
																		key: 'SportCategory_id',
																	}),
																	editable: false,
																	triggerAction: 'all',
																	hiddenName: 'SportCategory_id',
																	displayField: 'SportCategory_name',
																	valueField: 'SportCategory_id',
																	width: 200,
																	xtype: 'combo',
																	tpl: '<tpl for="."><div class="x-combo-list-item">' +
																		'{SportCategory_name} ' + '&nbsp;' +
																		'</div></tpl>',
																}]
															}, {
																html: '<p>Этап спортивной подготовки</p>',
																bodyStyle: 'height: 20px; padding: 7px; font-weight: bold',
															}, {
																width: 500,
																items: [{
																	mode: 'local',
																	id: 'SportStage',
																	allowBlank: false,
																	disabled: true,
																	store: new Ext.data.JsonStore({
																		url: '/?c=SportRegister&m=getSportStage',
																		autoLoad: true,
																		fields: [
																			{name: 'SportStage_id', type: 'int'},
																			{name: 'SportStage_name', type: 'string'}
																		],
																		key: 'SportStage_id',
																	}),
																	editable: false,
																	triggerAction: 'all',
																	hiddenName: 'SportStage_id',
																	displayField: 'SportStage_name',
																	valueField: 'SportStage_id',
																	width: 250,
																	xtype: 'combo',
																	tpl: '<tpl for="."><div class="x-combo-list-item">' +
																		'{SportStage_name} ' + '&nbsp;' +
																		'</div></tpl>',
																}]
															}, {
																html: '<p>ФИО тренера</p>',
																bodyStyle: 'height: 20px; padding: 7px; font-weight: bold',
															}, {
																width: 500,
																items: [{
																	mode: 'local',
																	store: new Ext.data.SimpleStore({
																		url: '/?c=SportRegister&m=getSportTrainer',
																		params: {
																			SportTrainer_name: '%'
																		},
																		fields: [
																			{name: 'SportTrainer_id', type: 'int'},
																			{name: 'SportTrainer_name', type: 'string'}
																		],
																		sortInfo: {
																			direction: 'ASC',
																			field: 'SportTrainer_name'
																		},
																		key: 'SportTrainer_id',
																	}),
																	style: 'float: left;',
																	editable: true,
																	triggerAction: 'all',
																	id: 'SportTrainer',
																	allowBlank: false,
																	disabled: true,
																	hiddenName: 'SportTrainer_id',
																	displayField: 'SportTrainer_name',
																	valueField: 'SportTrainer_id',
																	width: 350,
																	xtype: 'combo',
																	tpl: '<tpl for="."><div class="x-combo-list-item">' +
																		'{SportTrainer_name} ' + '&nbsp;' +
																		'</div></tpl>',
																	listeners: {
																		beforequery: function () {
																			Ext.Ajax.request({
																				url: '/?c=SportRegister&m=getSportTrainer',
																				params: {
																					SportTrainer_name: this.getRawValue() == '' ? '%' : this.getRawValue()
																				},
																				callback: function (options, success, response) {
																					if (success === true) {
																						var resp = Ext.util.JSON.decode(response.responseText);
																						//console.log('resp', resp);
																						var finalResp = resp.map(function (obj) {
																							return [obj.SportTrainer_id, obj.SportTrainer_name];
																						});
																						Ext.getCmp('SportTrainer').getStore().loadData(finalResp)
																					} else {
																						return false;
																					}
																				}
																			});
																		}
																	}
																}, {
																	xtype: 'button',
																	text: langs('Очистить'),
																	hidden: true,
																	id: 'clearSportTrainer',
																	style: 'float: left; margin-left: 20px;',
																	iconCls: 'clear16',
																	handler: function () {
																		Ext.getCmp('SportTrainer').enable();
																		Ext.getCmp('SportTrainer').setValue('');
																		Ext.getCmp('SportTrainer').setRawValue('');
																		Ext.getCmp('SportTrainer_SearchButton').show();
																		this.hide();
																	},
																}, {
																	handler: function () {
																		getWnd('swPersonSearchWindow').show({
																			onSelect: function (person_data) {
																				personSportRegistryWindow.checkInSportTrainer(person_data);
																			}
																		});
																	},
																	xtype: 'button',
																	iconCls: 'search16',
																	id: 'SportTrainer_SearchButton',
																	disabled: true,
																	style: 'float: left; margin-left: 20px;',
																	text: BTN_FRMSEARCH
																}
																]
															}, {
																html: '<p>Заключение врача</p>',
																bodyStyle: 'height: 20px; padding: 7px; font-weight: bold',
															}, {
																width: 500,
																items: [{
																	mode: 'local',
																	id: 'UMOResult',
																	allowBlank: false,
																	disabled: true,
																	store: new Ext.data.JsonStore({
																		url: '/?c=SportRegister&m=getUMOResult',
																		autoLoad: true,
																		fields: [
																			{name: 'UMOResult_id', type: 'int'},
																			{name: 'UMOResult_name', type: 'string'}
																		],
																		key: 'UMOResult_id',
																	}),
																	editable: false,
																	triggerAction: 'all',
																	hiddenName: 'UMOResult_id',
																	displayField: 'UMOResult_name',
																	valueField: 'UMOResult_id',
																	width: 250,
																	xtype: 'combo',
																	tpl: '<tpl for="."><div class="x-combo-list-item">' +
																		'{UMOResult_name} ' + '&nbsp;' +
																		'</div></tpl>',
																	listeners: {
																		'change' : function(combo, newValue, oldValue) {
																			if (combo.value == 7) {
																				Ext.getCmp('personSportRegistryWindowNote').show();
																				Ext.getCmp('personSportRegistryWindowComment').show();
																				Ext.getCmp('UMOResult_comment').enable();
																			} else {
																				Ext.getCmp('personSportRegistryWindowNote').hide();
																				Ext.getCmp('personSportRegistryWindowComment').hide();
																				Ext.getCmp('UMOResult_comment').setValue('');
																			}

																		}
																	}
																}]
															}, 
															//Доработка по задаче
															{
																html: '<p>Причина недопуска</p>',
																id: 'personSportRegistryWindowNote',
																bodyStyle: 'height: 70px; padding: 7px; font-weight: bold'
															}, {
																width: 500,
																bodyStyle: 'height: 70px; padding: 7px; font-weight: bold',
																id: 'personSportRegistryWindowComment',
																items: [{
																	id: 'UMOResult_comment',
																	allowBlank: true,
																	//disabled: true,
																	maxLength: 150,
																	bodyStyle: 'padding: 0',
																	width: 480,
																	maxLengthText: langs('Максимальная длина этого поля 150 символов'),
																	xtype: 'textarea',
																	listeners: {
																		/*'focus': function(c, t, o) {
																			debugger;
																			new Ext.ToolTip({
																				target: c.getEl(),
																				html: langs('Данное поле доступно тольно при выборе Заключение врача: Недопущен'),
																			});
																		},*/
																	}
																}]
															},
															{
																html: '<p>Допуск с</p>',
																bodyStyle: 'height: 20px; padding: 7px; font-weight: bold',
															}, {
																width: 500,
																items: [{
																	id: 'SportRegisterUMO_AdmissionDtBeg',
																	allowBlank: false,
																	disabled: true,
																	bodyStyle: 'padding: 0',
																	format: 'd.m.Y',
																	plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
																	width: 90,
																	xtype: 'datefield'
																}]
															}, {
																html: '<p>Допуск до</p>',
																bodyStyle: 'height: 20px; padding: 7px; font-weight: bold',
															}, {
																width: 500,
																items: [{
																	id: 'SportRegisterUMO_AdmissionDtEnd',
																	allowBlank: false,
																	disabled: true,
																	bodyStyle: 'padding: 0',
																	format: 'd.m.Y',
																	plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
																	width: 90,
																	xtype: 'datefield'
																}]
															}
															]
														}]
													}, {
														layout: 'form',
														region: 'east',
														width: 300,
														border: false,
														bodyStyle: 'padding:20px',
														items: [
															{
																layout: 'form',
																border: false,
																labelWidth: 130,
																items: [{
																	fieldLabel: langs('Дата включения'),
																	labelStyle: 'font-weight: bold',
																	disabled: true,
																	id: 'UMO_includeDate',
																	width: 110,
																	xtype: 'textfield'
																}]
															}, {
																layout: 'form',
																border: false,
																labelWidth: 130,
																items: [{
																	fieldLabel: langs('Дата исключения'),
																	labelStyle: 'font-weight: bold',
																	disabled: true,
																	id: 'UMO_excludeDate',
																	width: 110,
																	xtype: 'textfield'
																}]
															}
														]
													}
													]
												}
											],
											listeners: {
												'activate': function (p) {
													
												}
											}
										}
									],
								}

							],
							buttons: [
									{
										xtype: 'button',
										id: 'closef',
										text: langs('Закрыть'),
										iconCls: 'close16',
										handler: function () {
											personSportRegistryWindow.hide();
											personSportRegistryWindow.close();
										}
									}
								],
						}
					],
					buttons: [],
					elemDisabled: function () {
						return (Ext.getCmp('personSportRegistryWindow').Lpu_id == getGlobalOptions().lpu_id) || (typeof Ext.getCmp('personSportRegistryWindow').Lpu_id == 'undefined') ? false : true;
					},
				});

			sw.Promed.personSportRegistryWindow.superclass.initComponent.apply(this, arguments);
		},
		getAge: function (dateString, TextFieldDate) {
			var day = parseInt(dateString.substr(0, 2));
			var month = parseInt(dateString.substr(3, 5));
			var year = parseInt(dateString.substr(6, 10));
			var birthDate = new Date(year, month, day);
			var age = TextFieldDate.getFullYear() - birthDate.getFullYear();
			var m = TextFieldDate.getMonth() - birthDate.getMonth();
			if (m < 0 || (m === 0 && TextFieldDate.getDate() < birthDate.getDate())) {
				age--;
			}
			return age;
		},
		getCommonPersonInfo: function (Person_id) {
			Ext.Ajax.request({
				url: '/?c=SportRegister&m=loadPersonData',
				params: {
					Person_id: Person_id
				},
				callback: function (options, success, response) {

					if (success === true) {
						var personData = Ext.util.JSON.decode(response.responseText);
						Ext.getCmp('personSportRegistryWindow').personInfo = personData.personInfo;
						Ext.getCmp('personSportRegistryWindow').personInfo.age = Ext.getCmp('personSportRegistryWindow').getAge(personData.personInfo.Person_Birthday, new Date());
						Ext.getCmp('infoPacient').setTitle(personData.title);
						Ext.getCmp('infoPacient').body.update(personData.text, true);

						Ext.getCmp('GridObjectsUser').setDisabled(false);
					}
				}
			});
		},
		addSportRegisterUMO: function () {
			sw.swMsg.show({
				buttons: Ext.Msg.YESNO,
				fn: function (buttonId, text, obj) {
					if (buttonId == 'yes') {
						var loadMask = new Ext.LoadMask(this.getEl(), {msg: langs("Подождите, идет сохранение...")});
						loadMask.show();
						Ext.Ajax.request({
							url: '/?c=SportRegister&m=addSportRegisterUMO',
							params: {
								SportRegister_id: personSportRegistryWindow.SportRegister_id,
								pmUser_id: parseInt(getGlobalOptions().pmuser_id),
								SportRegisterUMO_UMODate: Ext.getCmp('SportRegisterUMO_UMODate').getValue(),
								InvalidGroupType_id: Ext.getCmp('InvalidGroupType').getValue(),
								SportParaGroup_id: Ext.getCmp('SportParaGroup').getValue(),
								SportRegisterUMO_IsTeamMember: Ext.getCmp('SportRegisterUMO_IsTeamMember').getValue(),
								MedPersonal_pid: Ext.getCmp('MedPersonalP').getValue(),
								MedPersonal_sid: Ext.getCmp('MedPersonalS').getValue(),
								SportType_id: Ext.getCmp('SportType').getValue(),
								SportOrg_id: Ext.getCmp('SportOrg').getValue(),
								SportTrainer_id: Ext.getCmp('SportTrainer').getValue(),
								SportStage_id: Ext.getCmp('SportStage').getValue(),
								Lpu_id: parseInt(getGlobalOptions().lpu_id),
								SportCategory_id: Ext.getCmp('SportCategory').getValue(),
								UMOResult_id: Ext.getCmp('UMOResult').getValue(),
								SportRegisterUMO_AdmissionDtBeg: Ext.getCmp('SportRegisterUMO_AdmissionDtBeg').getValue(),
								SportRegisterUMO_AdmissionDtEnd: Ext.getCmp('SportRegisterUMO_AdmissionDtEnd').getValue(),
								UMOResult_comment: Ext.getCmp('UMOResult_comment').getValue()
							},
							callback: function (options, success, response) {
								loadMask.hide();
								if (success) {
									var resp = Ext.util.JSON.decode(response.responseText);
									Ext.getCmp('GridObjectsUser').getGrid().getStore().load();
									sw.swMsg.show({
										title: langs('Успешно'),
										msg: langs('Данные УМО были сохранены'),
										buttons: Ext.Msg.OK,
										icon: Ext.MessageBox.INFO
									});
									personSportRegistryWindow.editMode(false, false);
									if (personSportRegistryWindow.exceptionalCase) {
										Ext.Ajax.request({
											url: '/?c=SportRegister&m=SportRegisterDateUpdate',
											params: {
												SportRegister_id: personSportRegistryWindow.SportRegister_id,
												pmUser_id: parseInt(getGlobalOptions().pmuser_id),
											},
										});
									}
								} else {
									sw.swMsg.show({
										title: langs('Ошибка'),
										msg: langs('Произошла ошибка при сохранении'),
										buttons: Ext.Msg.OK,
										icon: Ext.MessageBox.WARNING
									});
									return false;
								}
							}.createDelegate(this)
						});
					}
				}.createDelegate(this),
				icon: Ext.MessageBox.QUESTION,
				msg: langs('Вы действительно хотите сохранить данный УМО?'),
				title: langs('Предупреждение')
			});
		},
		updateSportRegisterUMO: function () {
			sw.swMsg.show({
				buttons: Ext.Msg.YESNO,
				fn: function (buttonId, text, obj) {
					if (buttonId == 'yes') {
						var loadMask = new Ext.LoadMask(this.getEl(), {msg: langs("Подождите, идет сохранение...")});
						loadMask.show();
						Ext.Ajax.request({
							url: '/?c=SportRegister&m=updateSportRegisterUMO',
							params: {
								SportRegisterUMO_id: personSportRegistryWindow.currentSportRegisterUMO_id,
								SportRegister_id: personSportRegistryWindow.SportRegister_id,
								pmUser_id: parseInt(getGlobalOptions().pmuser_id),
								SportRegisterUMO_UMODate: Ext.getCmp('SportRegisterUMO_UMODate').getValue(),
								InvalidGroupType_id: Ext.getCmp('InvalidGroupType').getValue(),
								SportParaGroup_id: Ext.getCmp('SportParaGroup').getValue(),
								SportRegisterUMO_IsTeamMember: Ext.getCmp('SportRegisterUMO_IsTeamMember').getValue(),
								MedPersonal_pid: Ext.getCmp('MedPersonalP').getValue(),
								MedPersonal_sid: Ext.getCmp('MedPersonalS').getValue(),
								SportType_id: Ext.getCmp('SportType').getValue(),
								SportOrg_id: Ext.getCmp('SportOrg').getValue(),
								SportTrainer_id: Ext.getCmp('SportTrainer').getValue(),
								SportStage_id: Ext.getCmp('SportStage').getValue(),
								Lpu_id: parseInt(getGlobalOptions().lpu_id),
								SportCategory_id: Ext.getCmp('SportCategory').getValue(),
								UMOResult_id: Ext.getCmp('UMOResult').getValue(),
								SportRegisterUMO_AdmissionDtBeg: Ext.getCmp('SportRegisterUMO_AdmissionDtBeg').getValue(),
								SportRegisterUMO_AdmissionDtEnd: Ext.getCmp('SportRegisterUMO_AdmissionDtEnd').getValue(),
								UMOResult_comment: Ext.getCmp('UMOResult_comment').getValue()
							},
							callback: function (options, success, response) {
								loadMask.hide();
								if (success) {
									var resp = Ext.util.JSON.decode(response.responseText);
									Ext.getCmp('GridObjectsUser').getGrid().getStore().load();
									sw.swMsg.show({
										title: langs('Успешно'),
										msg: langs('Данные УМО были сохранены'),
										buttons: Ext.Msg.OK,
										icon: Ext.MessageBox.INFO
									});
									personSportRegistryWindow.editMode(false, false);
								} else {
									sw.swMsg.show({
										title: langs('Ошибка'),
										msg: langs('Произошла ошибка при сохранении'),
										buttons: Ext.Msg.OK,
										icon: Ext.MessageBox.WARNING
									});
									return false;
								}
							}.createDelegate(this)
						});
					}
				}.createDelegate(this),
				icon: Ext.MessageBox.QUESTION,
				msg: langs('Вы действительно хотите сохранить данный УМО?'),
				title: langs('Предупреждение')
			});
		},
		deleteSportRegisterUMO: function (currentSportRegisterUMO_id) {
			if (personSportRegistryWindow.currentSportRegisterUMO_id) {
				sw.swMsg.show({
					buttons: Ext.Msg.YESNO,
					fn: function (buttonId, text, obj) {
						if (buttonId == 'yes') {
							var loadMask = new Ext.LoadMask(this.getEl(), {msg: langs("Подождите, идет удаление...")});
							loadMask.show();
							Ext.Ajax.request({
								url: '/?c=SportRegister&m=deleteSportRegisterUMO',
								params: {
									SportRegisterUMO_id: currentSportRegisterUMO_id,
								},
								callback: function (options, success, response) {
									loadMask.hide();
									if (success) {
										var resp = Ext.util.JSON.decode(response.responseText);
										Ext.getCmp('GridObjectsUser').getGrid().getStore().load();
										sw.swMsg.show({
											title: langs('Успешно'),
											msg: langs('Данные УМО были удалены'),
											buttons: Ext.Msg.OK,
											icon: Ext.MessageBox.INFO
										});
										personSportRegistryWindow.editMode(false, false);
									} else {
										sw.swMsg.show({
											title: langs('Ошибка'),
											msg: langs('Произошла ошибка при удалении'),
											buttons: Ext.Msg.OK,
											icon: Ext.MessageBox.WARNING
										});
										return false;
									}
								}.createDelegate(this)
							});
						}
					}.createDelegate(this),
					icon: Ext.MessageBox.QUESTION,
					msg: langs('Вы действительно хотите удалить эту УМО?'),
					title: langs('Предупреждение')
				});
			} else {
				sw.swMsg.show({
					buttons: Ext.Msg.OK,
					icon: Ext.MessageBox.QUESTION,
					msg: langs('Для начала выберите УМО'),
					title: langs('Предупреждение')
				});
			}
		},
		checkInSportTrainer: function (person_data) {
			Ext.Ajax.request({
				url: '/?c=SportRegister&m=checkInSportTrainer',
				params: {
					Person_id: person_data.Person_id
				},
				callback: function (options, success, response) {

					if (success === true) {
						var resp = Ext.util.JSON.decode(response.responseText);
						if (resp.length != 0) {
							var resp = Ext.util.JSON.decode(response.responseText);
							person_data.SportTrainer_id = resp[0].SportTrainer_id;

							Ext.getCmp('SportTrainer').setValue(person_data.SportTrainer_id);
							Ext.getCmp('SportTrainer').setRawValue(person_data.PersonSurName_SurName + ' ' + person_data.PersonFirName_FirName + ' ' + person_data.PersonSecName_SecName);
							Ext.getCmp('SportTrainer').disable();
							Ext.getCmp('clearSportTrainer').show();
							Ext.getCmp('SportTrainer_SearchButton').hide();
							getWnd('swPersonSearchWindow').hide();
						} else {
							sw.swMsg.show({
								buttons: Ext.Msg.YESNO,
								fn: function (buttonId, text, obj) {
									if (buttonId == 'yes') {
										Ext.Ajax.request({
											url: '/?c=SportRegister&m=addSportTrainer',
											params: {
												Person_id: person_data.Person_id,
												pmUser_id: parseInt(getGlobalOptions().pmuser_id)
											},
											callback: function (options, success, response) {
												if (success) {
													var resp = Ext.util.JSON.decode(response.responseText);
													person_data.SportTrainer_id = resp[0].SportTrainer_id;

													Ext.getCmp('SportTrainer').setValue(person_data.SportTrainer_id);
													Ext.getCmp('SportTrainer').setRawValue(person_data.PersonSurName_SurName + ' ' + person_data.PersonFirName_FirName + ' ' + person_data.PersonSecName_SecName);
													Ext.getCmp('SportTrainer').disable();
													Ext.getCmp('clearSportTrainer').show();
													Ext.getCmp('SportTrainer_SearchButton').hide();
													getWnd('swPersonSearchWindow').hide();
												} else {
													return false;
												}
											}.createDelegate(this)
										});
									}
								}.createDelegate(this),
								icon: Ext.MessageBox.QUESTION,
								msg: langs('Выбранный тренер отсутствует в регистре. Добавить его?'),
								title: langs('Предупреждение')
							});
						}
					}
				}
			});
		},
		fillSportRegisterUMOFields: function (data) {
			Ext.getCmp('SportRegisterUMO_UMODate').setValue(new Date(data.SportRegisterUMO_UMODate.date));
			Ext.getCmp('Person_age').setValue(personSportRegistryWindow.Person_Age);
			Ext.getCmp('InvalidGroupType').setValue(data.InvalidGroupType_id);
			Ext.getCmp('InvalidGroupType').setRawValue(data.InvalidGroupType_name);
			Ext.getCmp('SportParaGroup').setValue(data.SportParaGroup_id);
			Ext.getCmp('SportParaGroup').setRawValue(data.SportParaGroup_name);
			Ext.getCmp('SportRegisterUMO_IsTeamMember').setValue(data.SportRegisterUMO_IsTeamMember);
			Ext.getCmp('SportType').setValue(data.SportType_id);
			Ext.getCmp('SportType').setRawValue(data.SportType_name);
			Ext.getCmp('SportOrg').setValue(data.SportOrg_id);
			Ext.getCmp('SportOrg').setRawValue(data.SportOrg_name);
			Ext.getCmp('SportStage').setValue(data.SportStage_id);
			Ext.getCmp('SportStage').setRawValue(data.SportStage_name);
			Ext.getCmp('SportCategory').setValue(data.SportCategory_id);
			Ext.getCmp('SportCategory').setRawValue(data.SportCategory_name);
			Ext.getCmp('UMOResult').setValue(data.UMOResult_id);
			Ext.getCmp('UMOResult').setRawValue(data.UMOResult_name);
			Ext.getCmp('SportRegisterUMO_AdmissionDtBeg').setValue(new Date(data.SportRegisterUMO_AdmissionDtBeg.date));
			Ext.getCmp('SportRegisterUMO_AdmissionDtEnd').setValue(new Date(data.SportRegisterUMO_AdmissionDtEnd.date));
			Ext.getCmp('UMOResult_comment').setValue(data.UMOResult_comment);

			Ext.Ajax.request({
				url: '/?c=SportRegister&m=getNames',
				params: {
					SportRegisterUMO_id: data.SportRegisterUMO_id,
				},
				callback: function (options, success, response) {
					if (success) {
						var resp = Ext.util.JSON.decode(response.responseText);
						Ext.getCmp('SportTrainer').setValue(resp[0].SportTrainer_id);
						Ext.getCmp('SportTrainer').setRawValue(resp[0].SportTrainer_name);
						Ext.getCmp('MedPersonalP').setValue(resp[0].MedPersonal_pid);
						Ext.getCmp('MedPersonalP').setRawValue(resp[0].MedPersonal_pname);
						Ext.getCmp('MedPersonalS').setValue(resp[0].MedPersonal_sid);
						Ext.getCmp('MedPersonalS').setRawValue(resp[0].MedPersonal_sname);
					} else {
						return false;
					}
				}.createDelegate(this)
			});
		},
		restoreSportRegister: function (params) {
			Ext.Msg.show({
				title: langs('Вопрос'),
				msg: langs('Восстановить спортсмена?'),
				buttons: Ext.Msg.YESNO,
				fn: function (btn) {
					if (btn === 'yes') {
						this.getLoadMask(langs('Восстановление...')).show();
						Ext.Ajax.request({
							url: '/?c=SportRegister&m=restoreSportRegister',
							params: {
								SportRegister_id: params.SportRegister_id
							},
							callback: function (options, success, response) {
								this.getLoadMask().hide();
								if (success) {
									sw.swMsg.alert(langs('Успешно'), langs('Спортсмен восстановлен в регистер!'));
									personSportRegistryWindow.blockEdit = false;
									Ext.getCmp('UMO_excludeDate').setValue('');
									Ext.getCmp('restoreSportRegister').setVisible(false);
									Ext.getCmp('deleteSportRegister').setVisible(true);
									personSportRegistryWindow.editMode(false);
									swSportRegistryWindow.SportRegistrySearchFrame.getGrid().getStore().load();
								} else {
									sw.swMsg.alert(langs('Ошибка'), langs('Ошибка при восстановлении!'));
								}
							}.createDelegate(this)
						});
					}
				}.createDelegate(this),
				icon: Ext.MessageBox.QUESTION
			});
		},
		checkRequiredField: function () {
			var fieldsId = [
				'SportRegisterUMO_UMODate',
				'InvalidGroupType',
				'SportParaGroup',
				'MedPersonalP',
				'MedPersonalS',
				'SportType',
				'SportOrg',
				'SportCategory',
				'SportStage',
				'SportTrainer',
				'UMOResult',
				'SportRegisterUMO_AdmissionDtBeg',
				'SportRegisterUMO_AdmissionDtEnd'
			]
			var isEmptyFounded = false;
			fieldsId.forEach(function (field) {
				if (Ext.getCmp(field).getValue() == '') {
					isEmptyFounded = true;
				}
			});
			var UMOResult_comment = Ext.getCmp('UMOResult_comment');
			if (!UMOResult_comment.isValid())  {
				isEmptyFounded = true;
			}
			return isEmptyFounded;
		},
		show: function (params) {
			console.log("тест" +params.Server_id);
			var body = Ext.getBody();
			var form = this;
			console.log('Incoming params from "swSportRegistryWindow"', params);
			this.SportRegister_id = params.SportRegister_id;
			this.isEdit = params.isEdit;
			this.isAdding = params.isAdding;
			this.Person_id = params.Person_id;
			this.Person_Age = params.Person_Age;
			this.currentSportRegisterUMO_id = params.SportRegisterUMO_id;
			//this.getCommonPersonInfo(params.Person_id);

			this.blockEdit = params.SportRegister_delDT ? true : false;
			this.editMode(params.isEdit, params.isAdding);

			this.GridObjects.getGrid().getStore().load({
				params: {
					SportRegister_id: params.SportRegister_id
				}
			});
			
			this.PersonInfoPanel.personId = params.Person_id;
			this.PersonInfoPanel.serverId = params.Server_id;
			this.PersonInfoPanel.setTitle('...');
			this.PersonInfoPanel.load({
				callback: function () {
					this.PersonInfoPanel.setPersonTitle();
				}.createDelegate(this),
				Person_id: this.PersonInfoPanel.personId,
				Server_id: this.PersonInfoPanel.serverId
			});
			Ext.Ajax.request({
				url: '/?c=SportRegister&m=getMedPersonalP',
				params: {
					Lpu_id: getGlobalOptions().lpu_id
				},
				callback: function (options, success, response) {
					if (success === true) {
						var resp = Ext.util.JSON.decode(response.responseText);
						//console.log('resp', resp);
						var finalResp = resp.map(function (obj) {
							return [obj.MedPersonal_pid, obj.MedPersonal_pname];
						});
						Ext.getCmp('MedPersonalP').getStore().loadData(finalResp)
					} else {
						return false;
					}
				}
			});

			Ext.Ajax.request({
				url: '/?c=SportRegister&m=getMedPersonalS',
				params: {
					Lpu_id: getGlobalOptions().lpu_id
				},
				callback: function (options, success, response) {
					if (success === true) {
						var resp = Ext.util.JSON.decode(response.responseText);
						//console.log('resp', resp);
						var finalResp = resp.map(function (obj) {
							return [obj.MedPersonal_sid, obj.MedPersonal_sname];
						});
						Ext.getCmp('MedPersonalS').getStore().loadData(finalResp)
					} else {
						return false;
					}
				}
			});
			
			if (params.SportRegisterUMO_id) {
				Ext.Ajax.request({
					url: '/?c=SportRegister&m=getSportRegisterUMO',
					params: {
						SportRegisterUMO_id: params.SportRegisterUMO_id
					},
					callback: function (options, success, response) {
						if (success === true) {
							var resp = Ext.util.JSON.decode(response.responseText);
							//console.log('resp', resp[0]);
							personSportRegistryWindow.fillSportRegisterUMOFields(resp[0]);
							personSportRegistryWindow.currentSportRegisterUMO_UMODate = new Date(resp[0].SportRegisterUMO_UMODate.date).format('d.m.Y');
							personSportRegistryWindow.editMode(params.isEdit, params.isAdding);
							if (personSportRegistryWindow.GridObjects.getGrid().getStore().data.items[0].id == resp[0].SportRegisterUMO_id) {
									Ext.getCmp('deleteSportRegisterUMO').enable();
								} else {
									Ext.getCmp('deleteSportRegisterUMO').disable();
								}
						} else {
							return false;
						}
					}
				});
			}
			Ext.getCmp('UMO_includeDate').setValue(params.SportRegister_updDT.date ? new Date(params.SportRegister_updDT.date).format('d.m.Y') : new Date(params.SportRegister_updDT).format('d.m.Y'));
			Ext.getCmp('UMO_excludeDate').setValue(params.SportRegister_delDT ? params.SportRegister_delDT.date ? new Date(params.SportRegister_delDT.date).format('d.m.Y') : new Date(params.SportRegister_delDT).format('d.m.Y') : '');

			if (params.PersonRegisterOutCause_id == '') {
				Ext.getCmp('deleteSportRegister').setVisible(true);
				Ext.getCmp('restoreSportRegister').setVisible(false);
			} else {
				Ext.getCmp('restoreSportRegister').setVisible(true);
				Ext.getCmp('deleteSportRegister').setVisible(false);
				if (params.PersonRegisterOutCause_id == 1){
					Ext.getCmp('restoreSportRegister').setVisible(false);
					Ext.getCmp('deleteSportRegister').setVisible(false);
				}
			}
			sw.Promed.personSportRegistryWindow.superclass.show.apply(this, arguments);

		},
		editMode: function (isEdit, isAdding) {
			if (!personSportRegistryWindow.blockEdit) {
				this.isAdding = isAdding;
				if (isEdit) {
					Ext.getCmp('addSportDataButton').disable();
					Ext.getCmp('saveSportDataButton').enable();
					Ext.getCmp('editSportDataButton').disable();
					Ext.getCmp('deleteSportRegister').disable();
					Ext.getCmp('deleteSportRegisterUMO').disable();

					isAdding ? Ext.getCmp('SportRegisterUMO_UMODate').enable().setValue('') : Ext.getCmp('SportRegisterUMO_UMODate').enable();
					isAdding ? Ext.getCmp('InvalidGroupType').enable().setValue('') : Ext.getCmp('InvalidGroupType').enable();
					isAdding ? Ext.getCmp('SportParaGroup').enable().setValue('') : Ext.getCmp('SportParaGroup').enable();
					isAdding ? Ext.getCmp('SportRegisterUMO_IsTeamMember').enable().setValue('') : Ext.getCmp('SportRegisterUMO_IsTeamMember').enable();
					isAdding ? Ext.getCmp('MedPersonalP').enable().setValue('') : Ext.getCmp('MedPersonalP').enable();
					isAdding ? Ext.getCmp('MedPersonalS').enable().setValue('') : Ext.getCmp('MedPersonalS').enable();
					isAdding ? Ext.getCmp('SportType').enable().setValue('') : Ext.getCmp('SportType').enable();
					isAdding ? Ext.getCmp('SportOrg').enable().setValue('') : Ext.getCmp('SportOrg').enable();
					isAdding ? Ext.getCmp('SportStage').enable().setValue('') : Ext.getCmp('SportStage').enable();
					isAdding ? Ext.getCmp('SportCategory').enable().setValue('') : Ext.getCmp('SportCategory').enable();
					isAdding ? Ext.getCmp('UMOResult').enable().setValue('') : Ext.getCmp('UMOResult').enable();
					isAdding ? Ext.getCmp('SportRegisterUMO_AdmissionDtBeg').enable().setValue('') : Ext.getCmp('SportRegisterUMO_AdmissionDtBeg').enable();
					isAdding ? Ext.getCmp('SportRegisterUMO_AdmissionDtEnd').enable().setValue('') : Ext.getCmp('SportRegisterUMO_AdmissionDtEnd').enable();
					isAdding ? Ext.getCmp('SportTrainer').enable().setValue('') : Ext.getCmp('SportTrainer').enable();
					personSportRegistryWindow.setTitle(isAdding ? 'УМО спортсмена: добавление' : 'УМО спортсмена: редактирование');

					Ext.getCmp('SportTrainer_SearchButton').enable();

					Ext.getCmp('Person_age').setValue(personSportRegistryWindow.Person_Age);
					Ext.getCmp('SportRegisterUMO_UMODate').setValue(new Date.now().format("d.m.Y"));
					Ext.getCmp('UMOResult_comment').enable();
					if(Ext.getCmp('UMOResult').getValue() == 7){
						Ext.getCmp('personSportRegistryWindowNote').show();
						Ext.getCmp('personSportRegistryWindowComment').show();
					} else {
						Ext.getCmp('personSportRegistryWindowNote').hide();
						Ext.getCmp('personSportRegistryWindowComment').hide();
					}
				} else {
					Ext.getCmp('addSportDataButton').enable();
					Ext.getCmp('saveSportDataButton').disable();
					Ext.getCmp('editSportDataButton').enable();
					Ext.getCmp('deleteSportRegister').enable();
					Ext.getCmp('deleteSportRegisterUMO').enable();

					Ext.getCmp('SportRegisterUMO_UMODate').disable();
					Ext.getCmp('InvalidGroupType').disable();
					Ext.getCmp('SportParaGroup').disable();
					Ext.getCmp('SportRegisterUMO_IsTeamMember').disable();
					Ext.getCmp('MedPersonalP').disable();
					Ext.getCmp('MedPersonalS').disable();
					Ext.getCmp('SportType').disable();
					Ext.getCmp('SportOrg').disable();
					Ext.getCmp('SportStage').disable();
					Ext.getCmp('SportCategory').disable();
					Ext.getCmp('UMOResult').disable();
					Ext.getCmp('SportRegisterUMO_AdmissionDtBeg').disable();
					Ext.getCmp('SportRegisterUMO_AdmissionDtEnd').disable();
					Ext.getCmp('UMOResult_comment').disable();
					Ext.getCmp('SportTrainer').disable();
					Ext.getCmp('SportTrainer_SearchButton').disable();

					personSportRegistryWindow.setTitle('УМО спортсмена: просмотр');
				}
			} else {
				Ext.getCmp('addSportDataButton').disable();
				Ext.getCmp('saveSportDataButton').disable();
				Ext.getCmp('editSportDataButton').disable();
				Ext.getCmp('deleteSportRegister').disable();
				Ext.getCmp('deleteSportRegisterUMO').disable();
			}
		},
		listeners: {
			'render': function () {

			},
			'hide': function () {
				if (this.refresh)
					this.onHide();
			},
		}
	});