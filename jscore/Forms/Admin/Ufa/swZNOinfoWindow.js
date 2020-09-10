/**
 * swZNOinfoWindow - #154675 ТАП. Формирование системного сообщения при сохранении ТАПа со случаем подозрения на ЗНО
 * @author       Alexander Apaev
 * @version      1-05.02.2019
 */

sw.Promed.swZNOinfoWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swZNOinfoWindow',
	DirectType_id: false,
	modal: true,
	title: langs('Подозрение на ЗНО'),
	width: 950,
	height: 400,
	closable: false,
	//closeAction: 'hide',
	bodyStyle: 'padding:10px;border:0px;',
	persData: null,
	userMedStaffFact: null,
	onHide: Ext.emptyFn,
	callback: Ext.emptyFn,
	initComponent: function ()
	{

		var win = this;
		this.SummaryTpl = new Ext.XTemplate(
				langs('<span style="color: red; font-size: 17px; text-indent: 25px"> <p> В соответствии с п. 12 приказа МЗ РФ от 15.11.2012 г. № 915н «Об утверждении порядка оказания медицинской помощи населению по профилю «Онкология» при подозрении у пациента ЗНО его необходимо направить: <br>') +
				langs('<b>1.</b> или на прижизненное патолого-анатомическое исследование биопсийного материала (создание в РМИАС РБ направления с типом «7. Направление на патологогистологическое исследование»);<br>') +
				langs('<b>2.</b> и/или на проведение диагностических исследований (создание в РМИАС РБ направления с типом «2. На обследование»);<br>') +
				langs('<b>3.</b> или на консультацию к врачу-онкологу в медицинскую организацию следующего уровня в соответствии с приказом по маршрутизации МЗ РБ от 11.07.2016 г. № 2109-Д «О совершенствовании организации оказания медицинской помощи взрослому населению по профилю «онкология» в МО РБ» (в случае отсутствия возможности проведения исследований. Создание в РМИАС РБ направления с типом «3. На консультацию»). <br> Соблюдение пользователем всех необходимых действий при подозрении у пациента ЗНО контролируется в ЦСиПР МЗ РБ.</span > </p> <br><br>')

				);
		this.SummaryTpl1 = new Ext.XTemplate(
				langs('<span style="color: red; font-size: 17px"> <b>1.</b> В соответствии с п. 11 пр. МЗ РФ от 15.11.2012 г. № 915н «Об утверждении порядка оказания медицинской помощи населению по профилю «Онкология» при подозрении у пациента онкологического заболевания он должен быть направлен на консультацию в первичный онкологический кабинет. <br>') +
				langs('<b>2.</b> После сохранения ТАП с установленным значением «Z03.1 Наблюдение при подозрении на злокачественную опухоль» в параметре «Диагноз» необходимо в обязательном порядке с использованием функций РМИАС РБ создать пациенту направление к врачу-онкологу с типом «3.На консультацию».<br>') +
				langs('<b>3.</b> Выбор медицинской организации (ПОК или ММОЦ) для консультации врачом-онкологом необходимо осуществлять в соответствии с приказом по маршрутизации МЗ РБ от 11.07.2016 г. № 2109-Д «О совершенствовании организации оказания медицинской помощи взрослому населению по профилю «онкология» в МО РБ».<br>') +
				langs('<b>4.</b> Запись пациента необходимо осуществить на «бирку» с типом «По направлению» («бирка» коричневого цвета) для проведения консультации не позднее 5 дней от текущей даты. Соблюдение пользователем всех необходимых действий при подозрении у пациента ЗНО контролируется в ЦСиПР МЗ РБ."</span ><br><br><br>')

				);

		this.PersonInfoPanel = new sw.Promed.PersonInfoPanel({
			floatable: false,
			collapsed: true,
			region: 'north',
			title: lang['zagruzka'],
			plugins: [Ext.ux.PanelCollapsedTitle],
			titleCollapse: true,
			collapsible: true,
			id: 'PersonInfoPanel',
			hidden: true

		});

		Ext.apply(this,
				{
					layout: 'border',
					buttons: [
						{
							text: BTN_FRMSAVE,
							tabIndex: -1,
							tooltip: 'Сохранить данные',
							iconCls: 'save16',
							type: 'submit',
							hidden: true
						},
						{
							text: 'Отменить',
							tabIndex: -1,
							tooltip: 'Отменить сохранение',
							hidden: true
						},
						{
							text: BTN_FRMHELP,
							tabIndex: -1,
							tooltip: BTN_FRMHELP_TIP,
							iconCls: 'help16',
							hidden: true
						},
						{
							text: '-'
						},
						{
							text: BTN_FRMCLOSE,
							iconCls: 'cancel16',
							id: 'button_close',
							hidden: false,
							handler: function () {
								var wnd = this;
								var base_form = wnd.findById('FormPanel').getForm();
								var EvnDirection_pid = base_form.findField('EvnDirection_id').getValue();
								var direction = false;
								sw.Promed.Direction.loadDirectionDataForZNO({
									typeofdirection: 'all',
									EvnDirection_pid: EvnDirection_pid,
									callback: function (data) {
										if (data) {
											direction = true;
										}

										if (direction == false) {
											sw.swMsg.show({
												buttons: Ext.Msg.OK,
												fn: function () {
												}.createDelegate(this),
												icon: Ext.Msg.WARNING,
												msg: langs('Не выполнены требования по созданию направления при подозрении на ЗНО.'),
												title: ERR_INVFIELDS_TIT
											});
											return false;
											} else {
												getWnd('swZNOinfoWindow').hide();
												var win = getWnd('swPersonEmkWindow');
												if (win.isVisible()) {
													win.openEmkEditWindow(false , getWnd('swPersonEmkWindow').Tree.getSelectionModel().selNode);
												}
											}


									}
								});
							
							}.createDelegate(this)
						}
					],
					items:
							[
								this.PersonInfoPanel,
								//this.InformationPanel,
								this.FormPanel = new Ext.form.FormPanel({
									//title: 'Добавление',
									//height: 50,
									id: 'FormPanel',
									bodyStyle: 'padding:2px;border:0px;',
									//bodyStyle: 'padding:5px; border: 0px;',
									border: true,
									region: 'center',
									frame: true,
									bodyBorder: true,
									labelAlign: 'right',
									labelWidth: 200,
									listWidth: 250,
									items: [{
											id: 'EvnDirection_id',
											name: 'EvnDirection_id',
											value: 0,
											xtype: 'hidden'
										},
										{
											id: 'Diag_id',
											name: 'Diag_id',
											value: 0,
											xtype: 'hidden'
										},
										{
											id: 'EvnPL_id',
											name: 'EvnPL_id',
											value: 0,
											xtype: 'hidden'
										},
										{
											id: 'EvnUslugaCommon_id',
											name: 'EvnUslugaCommon_id',
											value: 0,
											xtype: 'hidden'
										},
										{
											id: 'EvnVizitPL_id',
											name: 'EvnVizitPL_id',
											value: 0,
											xtype: 'hidden'
										},
										this.InformationPanel = new sw.Promed.Panel({
											//height: 300,
											frame: false,
											bodyBorder: false,
											border: false,
											region: 'center',
											id: 'InformationPanel',
											html: ''
										}),
										{
											layout: 'column',
											align: 'middle',
											items: [
												{
													layout: 'form',
													//labelAlign: 'right',
													//labelWidth: 300,
													//columnWidth: .33,
													align: 'center',
													buttonAlign: 'center',
													labelAlign: 'top',
													hidden: false,
													bodyStyle: 'padding-left:150px;',
													items: [

														{
															xtype: 'button',
															text: langs('Направление на биопсию'),
															minWidth: 80,
															align: 'center',
															labelAlign: 'top',
															name: 'biops',
															id: 'biops',
															//labelWidth: 300,
															handler: function () {
																//var Direction = 'biopsy';
																win.openEvnDirectionHistologicEditWindow();
															}
														}]
												}, {
													layout: 'form',
													//columnWidth: .33,
													align: 'center',
													labelAlign: 'top',
													//bodyStyle: 'background:#DFE8F6;padding-left:15px;padding-right:5px;',
													bodyStyle: 'padding-left:50px;',
													items: [{
															xtype: 'button',
															name: 'examination',
															id: 'examination',
															text: langs('Направление на обследование'),
															minWidth: 80,
															handler: function () {
																var param = new Object();
																param.Direction = 'examination';
																param.MedSpecOms = true;
																win.openDirectionMasterWindow(param);
															}
														}]
												},
												{
													layout: 'form',
													//columnWidth: .33,
													align: 'center',
													//bodyStyle: 'background:#DFE8F6;padding-left:15px;padding-right:5px;',
													bodyStyle: 'padding-left:50px;',
													items: [{
															xtype: 'button',
															id: 'consultation',
															text: langs('Направление на консультацию'),
															minWidth: 80,
															//labelWidth: 400,
															handler: function () {
																var param = new Object();
																param.Direction = 'consultation';
																param.MedSpecOms = true;
																win.openDirectionMasterWindow(param);
															}
														}]
												}
												,
												{
													layout: 'form',
													//columnWidth: .33,
													align: 'center',
													//bodyStyle: 'background:#DFE8F6;padding-left:15px;padding-right:5px;',
													bodyStyle: 'padding-left:360px;',
													items: [{
															xtype: 'button',
															text: langs('Направление на консультацию'),
															id: 'consultations',
															minWidth: 80,
															//labelWidth: 400,
															handler: function () {
																var param = new Object();
																param.Direction = 'consultation';
																param.MedSpecOms = false;
																win.openDirectionMasterWindow(param);
															}
														}]
												}
											]
										}]

								})
							]
				}
		);
		sw.Promed.swZNOinfoWindow.superclass.initComponent.apply(this, arguments);
	},
	openEvnDirectionHistologicEditWindow: function () {
		//Направление на биопсию
		var win = this;
		var base_form = win.findById('FormPanel').getForm();
		var params = new Object();
		params.action = 'add';
		params.formParams = new Object();
		params.onHide = function () {
			//
		}
		params.ZNOinfo = 1;
		params.formParams.EvnDirection_pid = base_form.findField('EvnDirection_id').getValue();
		params.formParams.DopDispInfoConsent_id = null;
		params.formParams.Diag_id = base_form.findField('Diag_id').getValue();
		params.formParams.DirType_id = 7
		params.formParams.MedService_id = win.userMedStaffFact.MedService_id;
		params.formParams.MedStaffFact_id = win.userMedStaffFact.MedStaffFact_id;
		params.formParams.MedPersonal_id = win.userMedStaffFact.MedPersonal_id;
		params.formParams.LpuSection_id = win.userMedStaffFact.LpuSection_id;
		params.formParams.ARMType_id = win.userMedStaffFact.ARMType_id;
		params.formParams.Lpu_sid = getGlobalOptions().lpu_id;
		params.formParams.withDirection = true;
		params.formParams.EvnDirectionHistologic_pid = base_form.findField('EvnDirection_id').getValue();

		params.formParams.Person_id = win.PersonInfoPanel.getFieldValue('Person_id');
		params.formParams.PersonEvn_id = win.PersonInfoPanel.getFieldValue('PersonEvn_id');
		params.formParams.Server_id = win.PersonInfoPanel.getFieldValue('Server_id');

		getWnd('swEvnDirectionHistologicEditWindow').show(params);
	},
	openDirectionMasterWindow: function (param) {
		var win = this;
		this.isrecordPerson = false;
		//recordPerson
		if (param.Direction == 'examination') {
			var DirType_id = 2,
					DirType_Code = 2,
					DirType_Name = 'На обследование';

		} else if (param.Direction == 'consultation') {
			var DirType_id = 3,
					DirType_Code = 3,
					DirType_Name = 'На консультацию';
		}

		var base_form = win.findById('FormPanel').getForm();
		var EvnDirection_pid = base_form.findField('EvnDirection_id').getValue();
		var Diag_id = base_form.findField('Diag_id').getValue();
		var EvnPL_id = base_form.findField('EvnPL_id').getValue();
		var EvnUslugaCommon_id = base_form.findField('EvnUslugaCommon_id').getValue();
		var EvnVizitPL_id = base_form.findField('EvnVizitPL_id').getValue();
		var params = new Object();
		params = {
			//ZNOinfo: 'yes',
			userMedStaffFact: sw.Promed.MedStaffFactByUser.last,
			personData: {
				Person_id: win.PersonInfoPanel.getFieldValue('Person_id'),
				Server_id: win.PersonInfoPanel.getFieldValue('Server_id'),
				PersonEvn_id: win.PersonInfoPanel.getFieldValue('PersonEvn_id'),
				Person_Firname: win.PersonInfoPanel.getFieldValue('Person_Firname'),
				Person_Secname: win.PersonInfoPanel.getFieldValue('Person_Secname'),
				Person_Surname: win.PersonInfoPanel.getFieldValue('Person_Surname'),
				Person_Birthday: win.PersonInfoPanel.getFieldValue('Person_Birthday')
			},
			dirTypeData: {
				DirType_id: DirType_id,
				DirType_Code: DirType_Code,
				DirType_Name: DirType_Name
			},
			//dirTypeCodeIncList: ['1','4','5','6','7','8','9','10','11','13','14','15','16','17','18','12'],//['9'], // типы направления
			directionData: {
				EvnDirection_pid: EvnDirection_pid,
				Diag_id: Diag_id,
				MedService_id: win.userMedStaffFact.MedService_id,
				MedStaffFact_id: win.userMedStaffFact.MedStaffFact_id,
				MedPersonal_id: win.userMedStaffFact.MedPersonal_id,
				LpuSection_id: win.userMedStaffFact.LpuSection_id,
				ARMType_id: win.userMedStaffFact.ARMType_id,
				Lpu_sid: getGlobalOptions().lpu_id,
				withDirection: true,
				DirType_id: DirType_id,
				Person_id: win.PersonInfoPanel.getFieldValue('Person_id'),
				Server_id: win.PersonInfoPanel.getFieldValue('Server_id'),
				PersonEvn_id: win.PersonInfoPanel.getFieldValue('PersonEvn_id'),
				ZNOinfo: true, // при установления диагноза z03.1 #
				//EvnPL_id: EvnPL_id,
				//EvnUslugaCommon_id: EvnUslugaCommon_id,
				//EvnVizitPL_id: EvnVizitPL_id,
				MedSpecOms: param.MedSpecOms
			},
			onHide: function () {
				//win.EvnDirectionGrid.getGrid().getStore().reload();
			}
		};
		getWnd('swDirectionMasterWindow').show(params);

	},

	show: function (params)
	{
		sw.Promed.swZNOinfoWindow.superclass.show.apply(this, arguments);
		var wnd = this;
		this.onHide = Ext.emptyFn;
		this.callback = Ext.emptyFn;
		this.isrecordPerson = false;
		if (params.MedSpecOms == true) {
			this.SummaryTpl.overwrite(this.InformationPanel.body);
			wnd.findById('biops').show();
			wnd.findById('examination').show();
			wnd.findById('consultation').show();
			wnd.findById('consultations').hide();
			this.buttons[4].show();
		} else if (params.MedSpecOms == false) {
			wnd.SummaryTpl1.overwrite(this.InformationPanel.body);
			wnd.findById('biops').hide();
			wnd.findById('examination').hide();
			wnd.findById('consultation').hide();
			wnd.findById('consultations').show();
			this.buttons[4].hide(); //скрываем кнопку удаления
		}
		var base_form = wnd.findById('FormPanel').getForm();
		base_form.findField('EvnDirection_id').setValue(params.EvnDirection_id);
		base_form.findField('Diag_id').setValue(params.Diag_id);
		base_form.findField('EvnPL_id').setValue(params.EvnPL_id);
		base_form.findField('EvnUslugaCommon_id').setValue(params.EvnUslugaCommon_id);
		base_form.findField('EvnVizitPL_id').setValue(params.EvnVizitPL_id);


		wnd.PersonInfoPanel.personId = params.Person_id;
		wnd.PersonInfoPanel.serverId = params.Server_id;
		wnd.PersonInfoPanel.load({
			callback: function () {
				wnd.PersonInfoPanel.setPersonTitle();
			}.createDelegate(this),
			Person_id: wnd.PersonInfoPanel.personId,
			Server_id: wnd.PersonInfoPanel.serverId
		});

		this.userMedStaffFact = sw.Promed.MedStaffFactByUser.last;
		/*
		if ((!arguments[0]) || (!arguments[0].userMedStaffFact))
		{
			this.hide();
			Ext.Msg.alert('Ошибка открытия формы', 'Ошибка открытия формы "' + this.title + '".<br/>Не указаны параметры АРМа врача.');
		} else {
			this.userMedStaffFact = arguments[0].userMedStaffFact;
		}*/

	},
	listeners: {
		hide: function () {
			this.onHide();
		},
		close: function () {
			this.onHide();
		}
	}
});

