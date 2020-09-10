/**
 * swReabSelectWindow - окно выбора Профиля наблюдения регистра Реабилитации
 *
 */

sw.Promed.swReabSelectWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'choiceReabObjectWindow',
	DirectType_id: false,
	modal: true,
	title: lang['vyibor_profilja_registra_reab'],
	//title: 'Запись',
	width: 600,
	height: 270,
	closable: false,
	closeAction: 'hide',
	bodyStyle: 'padding:10px;border:0px;',
	lastSelectedDirectType_id: false,

	initComponent: function ()
	{
		this.InformationPanel = new sw.Promed.PersonInformationPanelShort({
			region: 'north',
			id: 'ReabInsInformPanel'
		});

		this.FormPanel = new Ext.form.FormPanel(
				{
					frame: true,
					layout: 'form',
					region: 'center',
					id: 'ReabInsPanel',
					bodyStyle: 'padding: 5px',
					autoHeight: false,
					labelAlign: 'right',
					labelWidth: 200,
					items:
							[
								{layout: 'form',
									id: 'ReabsetDate',
									items:
											[
												{
													allowBlank: true,
													fieldLabel: lang['data_vklyucheniya_v_registr'],
													name: 'Register_setDate',
													xtype: 'swdatefield',
													//hidden: true,
													plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
													maxValue: getGlobalOptions().date
												}
											]
								},
								{layout: 'form',
									id: 'ReabdisDate',
									items:
											[
												{
													allowBlank: true,
													fieldLabel: lang['data_off_Stage_Reab'],
													name: 'PersonRegister_disDate',
													xtype: 'swdatefield',
													plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
													maxValue: getGlobalOptions().date
												}
											]
								},
								{layout: 'form',
									id: 'cause_Off_Reab',
									items:
											[{
												allowBlank: false,
												xtype: 'combo',
												fieldLabel: 'Дальнейшая маршрутизация',
												hiddenName: 'OutCause_id',
												editable: false,
												emptyText: 'Введите параметр',
												//labelAlign: 'right',
												disabled: false,
												id: 'stageOff',
												mode: 'local',
												listWidth: 250,
												width: 250,
												triggerAction: 'all',
												tabIndex: -1,
												displayField: 'OutCause_Name',
												valueField: 'OutCause_id',
												store: new Ext.data.JsonStore({
													url: '?c=Ufa_Reab_Register_User&m=SeekOutCauseReab',
													autoLoad: false,
													fields: [
														{name: 'OutCause_id', type: 'int'},
														{name: 'OutCause_Name', type: 'string'}
													],
													key: 'OutCause_id',
												}),
												tpl: '<tpl for="."><div class="x-combo-list-item">' +
												'{OutCause_Name} ' + '&nbsp;' +
												'</div></tpl>'
											}]
								},
								{
									xtype: 'combo',
									fieldLabel: 'Врач',
									hiddenName: 'MedPersonal_iid',
									labelAlign: 'left',
									disabled: true,
									name: 'MedPersonal',
									id: 'FIOMedPersonal',
									mode: 'local',
									width: 320,
									triggerAction: 'all',
									store: new Ext.data.SimpleStore({
										fields: [{name: 'FIOMedPersonal', type: 'string'}, {name: 'MedPersonal_id', type: 'int'}],
										data: [
											[getGlobalOptions().CurMedPersonal_FIO, 1]
										]
									}),
									displayField: 'FIOMedPersonal',
									valueField: 'MedPersonal_id',
									tpl: '<tpl for="."><div class="x-combo-list-item">' +
									'{quest} ' + '&nbsp;' +
									'</div></tpl>'
								}
							]
				}); // Конец формы


		Ext.apply(this,
				{

					items:
							[
								this.InformationPanel, this.FormPanel,
								{
									layout: 'column',
									border: false,
									frame: true,
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
													id: 'ReabObjectCombo',
													labelAlign: 'left',
													fieldLabel: 'Профиль',
													hideLabel: false,
													//anchor: '100%',
													mode: 'local',
													store: new Ext.data.JsonStore(
															{
																url: '?c=Ufa_Reab_Register_User&m=SeekProfReab',
																autoLoad: false,
																fields:
																		[
																			{name: 'DirectType_id', type: 'int'},
																			{name: 'DirectType_name', type: 'string'}
																		],
																key: 'DirectType_id',
															}),
													editable: false,
													triggerAction: 'all',
													displayField: 'DirectType_name',
													valueField: 'DirectType_id',
													width: 150,
													hiddenName: 'DirectType_id',
													autoscroll: false,
													xtype: 'combo',
													listeners: {
														specialkey: function (field, e) {
															//console.log('FIELD', field)
															if (e.getKey() == e.ENTER) {
																Ext.getCmp('getDirectType_id').handler();
															}
														}
													}

												}
											]
										},
										{
											layout: 'form',
											border: false,
											style: 'position:relative;  left:40px ',
											labelWidth: 130,
											width: 260,
											labelAlign: 'left',
											items: [
												{
													xtype: 'combo',
													allowBlank: false,
													fieldLabel: 'Этап реабилитации',
													hiddenName: 'StageType_id',
													disabled: false,
													id: 'StageReab',
													style: 'text-align:center;',
													mode: 'local',
													listWidth: 40,
													width: 50,
													triggerAction: 'all',
													store: new Ext.data.JsonStore({
														url: '?c=Ufa_Reab_Register_User&m=SeekStageReab',
														autoLoad: false,
														fields: [
															{name: 'StageType_id', type: 'int'},
															{name: 'StageType', type: 'string'}
														],
														key: 'StageType_id',
													}),
													displayField: 'StageType',
													valueField: 'StageType_id',
													tpl: '<tpl for="."><div class="x-combo-list-item">' +
													'&nbsp;' + '{StageType} ' + '&nbsp;' +
													'</div></tpl>',
													/*
													 listeners: {
													 'select': function (combo, record, index) {
													 // alert('Сведения');
													 //Ext.getCmp('stageOff').setDisabled(false);
													 if(Ext.getCmp('choiceReabObjectWindow').inp == 2)
													 {
													 Ext.getCmp('stageOff').focus(true);
													 Ext.getCmp('stageOff').getStore().clearFilter();

													 if(record.data.StageType_id == 1 )
													 {
													 if(Ext.getCmp('stageOff').getValue().inlist(['6','8','9','10','1','3','4']) == false)
													 {
													 Ext.getCmp('stageOff').setValue('Введите параметр');
													 }
													 Ext.getCmp('stageOff').store.filterBy(function(rec) {
													 return (rec.get('OutCause_id').toString().inlist(['6','8','9','10','1','3','4']));
													 });
													 }
													 }
													 }
													 }
													 */
												}
											]
										}
									]
								}
							],
					buttons: [
						{
							iconCls: 'save16',
							text: BTN_FRMSAVE,
							// text: lang['vyibrat'],
							// style: 'margin-right:150px',
							id: 'getDirectType_id',
							handler: function ()
							{
								//Контроль даты постановки на учет
								if (!Ext.getCmp('ReabInsPanel').getForm().findField('Register_setDate').isValid())
								{
									sw.swMsg.show(
											{icon: Ext.MessageBox.ERROR,
												title: lang['oshibka'],
												msg: lang['Date_Insert_Reab'],
												buttons: Ext.Msg.OK
											});
									return false;
								}
								//Контроль даты завершения этапа
								if(Ext.getCmp('choiceReabObjectWindow').inp != 3)
								{
									if (!Ext.getCmp('ReabInsPanel').getForm().findField('PersonRegister_disDate').isValid())
									{
										sw.swMsg.show(
												{icon: Ext.MessageBox.ERROR,
													title: lang['oshibka'],
													msg: lang['Date_disDate_Reab'],
													buttons: Ext.Msg.OK
												});
										return false;
									}
								}

								//Контроль профиля
								var DirectType_id = Ext.getCmp('ReabObjectCombo').getValue();
								Ext.getCmp('choiceReabObjectWindow').lastSelectedDirectType_id = DirectType_id;
								if (typeof DirectType_id == 'string')
								{
									sw.swMsg.show(
											{icon: Ext.MessageBox.ERROR,
												title: lang['oshibka'],
												msg: lang['neobhodimo_vyibrat_profil_nablyudeniya'],
												buttons: Ext.Msg.OK
											});
									return false;
								}
								//Контроль этапа
								var stageId = Ext.getCmp('StageReab').getValue();
								var stageName = Ext.getCmp('StageReab').lastSelectionText;
								if (typeof stageId == 'string')
								{
									sw.swMsg.show(
											{icon: Ext.MessageBox.ERROR,
												title: lang['oshibka'],
												msg: lang['Stage_Reab'],
												buttons: Ext.Msg.OK
											});
									return false;
								}
								//Контроль причины завершения этапа - только для 2 типа
								if (Ext.getCmp('choiceReabObjectWindow').inp == 2 || Ext.getCmp('choiceReabObjectWindow').inp == 3)
								{
									var stageOff = Ext.getCmp('stageOff').getValue();
									if (typeof stageOff == 'string')
									{
										sw.swMsg.show(
												{icon: Ext.MessageBox.ERROR,
													title: lang['oshibka'],
													msg: lang['Stage_off_Reab'],
													buttons: Ext.Msg.OK
												});
										return false;
									}
								}

								// alert('ВЫход на сохранение');
								Ext.getCmp('ReabObjectCombo').setValue('');
								Ext.getCmp('choiceReabObjectWindow').refresh();
								var paramOut = new Object();
								paramOut.DirectType_id = DirectType_id;
								paramOut.DateIn = Ext.getCmp('ReabInsPanel').getForm().findField('Register_setDate').getValue();
								paramOut.StageId = stageId;
								paramOut.StageName = stageName;
								paramOut.StageOff = stageOff;

								if (Ext.getCmp('choiceReabObjectWindow').inp == 0) {
									Ext.getCmp('swReabRegistryWindow').ReabRegistrIns(paramOut); // ВЫход на списочную форму
								}
								if (Ext.getCmp('choiceReabObjectWindow').inp == 1) {
									Ext.getCmp('ufa_personReabRegistryWindow').addReabProfStage(paramOut); // ВЫход на страничную форму
								}
								if (Ext.getCmp('choiceReabObjectWindow').inp == 2) {
									paramOut.DateOff = Ext.getCmp('ReabInsPanel').getForm().findField('PersonRegister_disDate').getValue();
									//alert('Пока ничего');
									Ext.getCmp('ufa_personReabRegistryWindow').CloseReabProfStage(paramOut); // ВЫход на страничную форму
								}
								if (Ext.getCmp('choiceReabObjectWindow').inp == 3) {
									Ext.getCmp('ufa_personReabRegistryWindow').CanselCloseReabProfStage(Ext.getCmp('choiceReabObjectWindow').ReabEvent_id); // ВЫход на страничную форму
								}
							}
						},
						'-',
						{
							//text: lang['otmena'],
							iconCls: 'cancel16',
							text: BTN_FRMCANCEL,
							id: 'cansel',
							handler: function () {
								Ext.getCmp('choiceReabObjectWindow').MorbusType_id = false;
								Ext.getCmp('choiceReabObjectWindow').refresh();
							}
						}
					],
				}
		);
		sw.Promed.swReabSelectWindow.superclass.initComponent.apply(this, arguments);
	},


	refresh: function () {
		//  alert('Обновление');
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
	show: function (params)
	{
		//Установка текущего времени
		this.FormPanel.getForm().findField('Register_setDate').setValue(getGlobalOptions().date);
		this.FormPanel.getForm().findField('PersonRegister_disDate').setValue(getGlobalOptions().date);

		// Установка минимального времени (-30 дней)
		var sss = getGlobalOptions().date.trim().substr(0, 10);
		var rdatetime = Date.parseDate(sss, 'd.m.Y');
		rdatetime.setDate(rdatetime.getDate() - 30);
		//var first_array =   sss.match(/(\d{2})\.(\d{2})\.(\d{4})/);
		//minDate = new Date(first_array[3],first_array[2]-1,first_array[1]);
		this.FormPanel.getForm().findField('Register_setDate').setMinValue(rdatetime);
		this.FormPanel.getForm().findField('PersonRegister_disDate').setMinValue(rdatetime);

		//Подсунем врача
		Ext.getCmp('FIOMedPersonal').setValue(1);
		//Панель пациента
		this.InformationPanel.load({
			Person_id: params.Person_id
		});
//		Ext.getCmp('ReabObjectCombo').getStore().reload();

		Ext.getCmp('ReabObjectCombo').getStore().load({
			callback: function (records, options, success) {
				if (params.inp == 2 || params.inp == 3)
				{
					Ext.getCmp('ReabObjectCombo').setValue(params.DirectType_id);
				}
			}
		});
		Ext.getCmp('StageReab').getStore().load({
			callback: function (records, options, success) {
				if (params.inp == 2 || params.inp == 3)
				{
					Ext.getCmp('StageReab').setValue(params.StageType_id);
				}
			}
		});
		//Запоминаем, откуда запущено окно (0 - первоначальное включение в регистр; 1 -  добавление профиля или изменение этапа;2 - закрытие этапа;3-отмена закрытия этапа)
		//console.log('params=',params);
		switch (params.inp)
		{
			case 0:
				Ext.getCmp('choiceReabObjectWindow').inp = 0;
				Ext.getCmp('choiceReabObjectWindow').setTitle(lang['vklyuchit_v_registr']);
				Ext.getCmp('cause_Off_Reab').hide();
				Ext.getCmp('ReabsetDate').show();
				Ext.getCmp('ReabdisDate').hide();
				break;
			case 1:
				Ext.getCmp('choiceReabObjectWindow').inp = 1;
				Ext.getCmp('choiceReabObjectWindow').setTitle(lang['dobavit_Reab_Profil']);
				Ext.getCmp('cause_Off_Reab').hide();
				Ext.getCmp('ReabsetDate').show();
				Ext.getCmp('ReabdisDate').hide();
				break;
			case 2:
				Ext.getCmp('choiceReabObjectWindow').inp = 2;
				Ext.getCmp('choiceReabObjectWindow').setTitle(lang['zakryit_Stage_Reab']);
				Ext.getCmp('cause_Off_Reab').show();
				Ext.getCmp('ReabsetDate').hide();
				Ext.getCmp('ReabdisDate').show();
				Ext.getCmp('stageOff').setValue('Введите параметр');
				Ext.getCmp('ReabObjectCombo').setDisabled(true);
				Ext.getCmp('StageReab').setDisabled(true);
//				var listCombo = "";
//				if (params.StageType_id == 1)
//				{
//					listCombo = "(11,6,8,9,10,1,3,4)";
//				}
//				if (params.StageType_id == 2)
//				{
//					listCombo = "(5,6,8,9,10,1,3,4)";
//				}
//				if (params.StageType_id == 3)
//				{
//					listCombo = "(7,10,1,3,4)";
//				}

//				Ext.getCmp('stageOff').getStore().load({
//					params: {
//								ListCombo: listCombo
//									},
//				    callback: function (records, options, success) {
//						}
//			});
				Ext.getCmp('stageOff').getStore().load({

				});
				break;
			case 3:
				Ext.getCmp('choiceReabObjectWindow').inp = 3;
				Ext.getCmp('choiceReabObjectWindow').ReabEvent_id = params.ReabEvent_id;
				Ext.getCmp('choiceReabObjectWindow').setTitle(lang['otmena_Stage_Reab']);
				Ext.getCmp('cause_Off_Reab').show();
				Ext.getCmp('ReabsetDate').hide();
				Ext.getCmp('ReabdisDate').show();
				//Ext.getCmp('stageOff').setValue('Введите параметр');
				Ext.getCmp('ReabObjectCombo').setDisabled(true);
				Ext.getCmp('StageReab').setDisabled(true);
				Ext.getCmp('stageOff').setDisabled(true);
				this.FormPanel.getForm().findField('PersonRegister_disDate').setDisabled(true);
				this.FormPanel.getForm().findField('PersonRegister_disDate').setValue(params.Event_disDate);
//				var listCombo = "";
//				if (params.StageType_id == 1)
//				{
//					listCombo = "(11,6,8,9,10,1,3,4)";
//				}
//				if (params.StageType_id == 2)
//				{
//					listCombo = "(5,6,8,9,10,1,3,4)";
//				}
//				if (params.StageType_id == 3)
//				{
//					listCombo = "(7,10,1,3,4)";
//				}

				Ext.getCmp('stageOff').getStore().load({

					callback: function (records, options, success) {
						Ext.getCmp('stageOff').setValue(params.OutCause_id);
					}
				});

				break;
			default :
				Ext.Msg.alert('Ошибка ', 'Некорректность работы программы');
				break;
		}

		sw.Promed.swReabSelectWindow.superclass.show.apply(this, arguments);
		//

	},
	listeners: {
		'hide': function () {
			if (this.refresh)
				this.onHide();
		},
		'close': function () {
			if (this.refresh)
				this.onHide();
		}
	}
});

