/**
 * swWorkPlacePhysWindow - окно рабочего места врача
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/projects/promed
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009-2010, Swan.
 * @author       Марков Андрей (по интерфейсу Александра Арефьева)
 * @prefix       mpwp
 * @version      июнь 2010 
 */
/*NO PARSE JSON*/

sw.Promed.swWorkPlacePhysWindow = Ext.extend(sw.Promed.BaseForm,
{
	//тип АРМа, определяется к каким функциям будет иметь доступ врач через ЭМК, например у стоматолога появится ввод талона по стоматологии,
	//у врача параклиники будет доступ только к параклиническим услугам
	ARMType: null,
	//объект с параметрами рабочего места, с которыми была открыта форма
	userMedStaffFact: null,
	closable: true,
	closeAction: 'hide',
	maximized: true,
	title: WND_WPMP,
	iconCls: 'workplace-mp16',
	id: 'swWorkPlacePhysWindow',
	readOnly: false,
	listeners: {
		activate: function(){
			sw.Applets.uec.startUecReader({callback: this.getDataFromUec.createDelegate(this)});
			sw.Applets.bdz.startBdzReader({callback: this.getDataFromBdz.createDelegate(this)});
			sw.Applets.BarcodeScaner.startBarcodeScaner({callback: this.getDataFromBarScan.createDelegate(this), ARMType: 'common'});
		},
		deactivate: function() {
			sw.Applets.uec.stopUecReader();
			sw.Applets.bdz.stopBdzReader();
			sw.Applets.BarcodeScaner.stopBarcodeScaner();
		},
		beforeshow: function() {
			if ((!getGlobalOptions().medstafffact) || (getGlobalOptions().medstafffact.length==0))
			{
				Ext.Msg.alert(langs('Сообщение'), langs('Текущий логин не соотнесен с врачом. <br/>Доступ к интерфейсу врача невозможен.'));
				return false;
			}
		}
	},
	getDataFromBarScan: function(person_data) {
		var _this = this,
			form = Ext.getCmp('swWorkPlacePhysWindow');

		if (!Ext.isEmpty(person_data.Person_Surname)){
			form.findById('mpwpSearch_SurName').setValue(person_data.Person_Surname);
		} else {
			form.findById('mpwpSearch_SurName').setValue(null);
		}

		if (!Ext.isEmpty(person_data.Person_Firname)){
			form.findById('mpwpSearch_FirName').setValue(person_data.Person_Firname);
		} else {
			form.findById('mpwpSearch_FirName').setValue(null);
		}

		if (!Ext.isEmpty(person_data.Person_Secname)){
			form.findById('mpwpSearch_SecName').setValue(person_data.Person_Secname);
		} else {
			form.findById('mpwpSearch_SecName').setValue(null);
		}

		if (!Ext.isEmpty(person_data.Person_Birthday)){
			form.findById('mpwpSearch_BirthDay').setValue(person_data.Person_Birthday);
		} else {
			form.findById('mpwpSearch_BirthDay').setValue(null);
		}

		var callback = function() {
			var form = Ext.getCmp('swWorkPlacePhysWindow'),
			grid = form.ScheduleGrid;

			if (grid.getStore().getCount() == 1) {
				_this.scheduleOpen();
			} else if (grid.getStore().getCount() == 0) {
				_this.scheduleNew(person_data);
			}
		};

		form.scheduleLoad(_this.mode, callback, person_data);

	},
	getDataFromUec: function(uec_data, person_data) {
		var form = this;
		var grid = form.ScheduleGrid;
		var f = false;
		grid.getStore().each(function(record) {
			if (record.get('Person_id') == person_data.Person_id) {
				log(langs('Найден в гриде'));

				var index = grid.getStore().indexOf(record);
				grid.getView().focusRow(index);
				grid.getSelectionModel().selectRow(index);
				form.scheduleOpen();
				f = true;
				return;
			}
		});
		if (!f) { // Если не нашли в гриде
			// todo: Еще надо проверку в принципе на наличие такого человека в БД, и если нет - предлагать добавлять
			// Открываем на добавление
			log(langs('Не найден в гриде'));
			var params = {};
			params.action = 'add';
			params.Person_id = person_data.Person_id;
			params.PersonEvn_id = (person_data.PersonEvn_id)?person_data.PersonEvn_id:null;
			params.Server_id = (person_data.Server_id)?person_data.Server_id:null;
			params.swPersonSearchWindow = getWnd('swPersonSearchWindow');
			Ext.Ajax.request({
				params: {LpuUnitType_SysNick: 'polka',Person_id: params.Person_id, LpuSection_id: form.userMedStaffFact.LpuSection_id, MedStaffFact_id: form.userMedStaffFact.MedStaffFact_id},
				callback: function(opt, success, response) {
					var response_obj = Ext.util.JSON.decode(response.responseText);
					if ( response_obj.success && response_obj.result )
					{
						// да, записан
						Ext.Msg.alert(langs('Сообщение'), langs('Пациент')+ response_obj.result['Person_FIO'] +' ('
							+ response_obj.result['Person_BirthDay'] +' '+langs(' г.р., возраст: ')+
							response_obj.result['Person_Age'] +') записан сегодня на '+ response_obj.result['TimetableGraf_begTime']);
						return false;
					}
					// ? pdata.EvnDirectionData = null;
					this.createTtgAndOpenPersonEPHForm(params);
				}.createDelegate(form),
				url: '/?c=TimetableGraf&m=checkPersonByToday'
			});
			//form.scheduleNew();
		}
	},
	getDataFromBdz: function(bdz_data, person_data) {
		var form = this;
		var grid = form.ScheduleGrid;
		var f = false;
		grid.getStore().each(function(record) {
			if (record.get('Person_id') == person_data.Person_id) {
				log(langs('Найден в гриде'));

				var index = grid.getStore().indexOf(record);
				grid.getView().focusRow(index);
				grid.getSelectionModel().selectRow(index);
				form.scheduleOpen();
				f = true;
				return;
			}
		});
		if (!f) { // Если не нашли в гриде
			// todo: Еще надо проверку в принципе на наличие такого человека в БД, и если нет - предлагать добавлять
			// Открываем на добавление
			log(langs('Не найден в гриде'));
			var params = {};
			params.action = 'add';
			params.Person_id = person_data.Person_id;
			params.PersonEvn_id = (person_data.PersonEvn_id)?person_data.PersonEvn_id:null;
			params.Server_id = (person_data.Server_id)?person_data.Server_id:null;
			params.swPersonSearchWindow = getWnd('swPersonSearchWindow');
			Ext.Ajax.request({
				params: {LpuUnitType_SysNick: 'polka',Person_id: params.Person_id, LpuSection_id: form.userMedStaffFact.LpuSection_id, MedStaffFact_id: form.userMedStaffFact.MedStaffFact_id},
				callback: function(opt, success, response) {
					var response_obj = Ext.util.JSON.decode(response.responseText);
					if ( response_obj.success && response_obj.result )
					{
						// да, записан
						Ext.Msg.alert(langs('Сообщение'), langs('Пациент')+ response_obj.result['Person_FIO'] +' ('
							+ response_obj.result['Person_BirthDay'] +' '+langs(' г.р., возраст: ')+
							response_obj.result['Person_Age'] +') записан сегодня на '+ response_obj.result['TimetableGraf_begTime']);
						return false;
					}
					// ? pdata.EvnDirectionData = null;
					this.createTtgAndOpenPersonEPHForm(params);
				}.createDelegate(form),
				url: '/?c=TimetableGraf&m=checkPersonByToday'
			});
			//form.scheduleNew();
		}
	},
	getGrid: function ()
	{
		return this.ScheduleGrid;
	},
	getPeriodToggle: function (mode)
	{
		switch(mode)
		{
		case 'day':
			return this.DoctorToolbar.items.items[6];
			break;
		case 'week':
			return this.DoctorToolbar.items.items[7];
			break;
		case 'month':
			return this.DoctorToolbar.items.items[8];
			break;
		case 'range':
			return this.DoctorToolbar.items.items[9];
			break;
		default:
			return null;
			break;
		}
	},
	printPacList: function() {
		var form = this;
		var grid = form.getGrid();
		if (!grid)
		{
			Ext.Msg.alert(langs('Ошибка'), langs('Список расписаний не найден!'));
			return false;
		}
		else if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('TimetableGraf_id') )
		{
			Ext.Msg.alert(langs('Ошибка'), langs('Не выбрана запись!'));
			return false;
		}
		var record = grid.getSelectionModel().getSelected();
	
		var id_salt = Math.random();
		var win_id = 'print_pac_list' + Math.floor(id_salt * 10000);
		window.open('/?c=TimetableGraf&m=printPacList&Day=' + record.get('TimetableGraf_Date').dateFormat('d.m.Y') + '&MedStaffFact_id=' + record.get('MedStaffFact_id'), win_id);
		
	},
	scheduleLoad: function(mode, callback) {
		var btn = this.getPeriodToggle(mode);
		if (btn) 
		{
			if (mode != 'range')
			{
				if (this.mode == mode)
				{
					btn.toggle(true);
					if (mode != 'day') // чтобы при повторном открытии тоже происходила загрузка списка записанных на этот день
						return false;
				}
				else 
				{
					this.mode = mode;
				}
			}
			else 
			{
				btn.toggle(true);
				this.mode = mode;
			}
		}
		var params = new Object();
		params.begDate = Ext.util.Format.date(this.dateMenu.getValue1(), 'd.m.Y');
		params.endDate = Ext.util.Format.date(this.dateMenu.getValue2(), 'd.m.Y');
		params.MedStaffFact_id = this.userMedStaffFact.MedStaffFact_id;
		/*if (this.findById('mpwpSearch_FIO').getValue().length>0)
		{
			params.Person_FIO = this.findById('mpwpSearch_FIO').getValue();
		}*/
		if ( this.findById('mpwpSearch_FirName').getValue().length > 0 )
		{
			params.Person_FirName = this.findById('mpwpSearch_FirName').getValue();
		}
		if ( this.findById('mpwpSearch_SecName').getValue().length > 0 )
		{
			params.Person_SecName = this.findById('mpwpSearch_SecName').getValue();
		}
		if ( this.findById('mpwpSearch_SurName').getValue().length > 0 )
		{
			params.Person_SurName = this.findById('mpwpSearch_SurName').getValue();
		}
		/*
		if (this.findById('mpwpSearch_UslugaComplexPid').getValue())
		{
			params.UslugaComplex_id = this.findById('mpwpSearch_UslugaComplexPid').getValue();
		}
		*/
		if (Ext.util.Format.date(this.findById('mpwpSearch_BirthDay').getValue(), 'd.m.Y').length>0)
		{
			params.Person_BirthDay = Ext.util.Format.date(this.findById('mpwpSearch_BirthDay').getValue(), 'd.m.Y');
		}
		
		// При направлении указывается ЛПУ и врач этого ЛПУ 
		if (this.findById('mpwpLpu_id').getValue()>0)
		{
			params.Lpu_id = this.findById('mpwpLpu_id').getValue();
		}
		if (this.findById('mpwpMedPersonal_id').getValue()>0)
		{
			params.MedPersonal_id = this.findById('mpwpMedPersonal_id').getValue();
		}

		this.getGrid().loadStore(params, callback);
	},
	scheduleSave: function(data)
	{
		var grid = this.getGrid();
	
		if (!data.TimetableGraf_id) { // если среди данных нет id бирки, извлекаем id из грида
			if ((grid.getSelectionModel().getSelected() == null) || 
			(grid.getSelectionModel().getSelected().get('TimetableGraf_id') == null) || 
			(grid.getSelectionModel().getSelected().get('Person_id') != null))
				return false;
				
			data.TimetableGraf_id = grid.getSelectionModel().getSelected().get('TimetableGraf_id');
		}
        this.savePosition();
        if (data.TimetableGraf_id) {
            sw.Promed.Direction.recordHimSelf({
                userMedStaffFact: this.userMedStaffFact
                ,TimetableGraf_id: data.TimetableGraf_id
                ,personData: data
                ,windowId: this.getId()
                ,onSaveRecord: function(conf) {
                    grid.getStore().reload();
                }
            });
        } else {
            data.LpuUnitType_SysNick = 'polka';
            data.addDirection = 1;
            data.DirType_id=16;
            data.EvnDirection_Num = 0;
            data.MedPersonal_zid = 0;
            data.EvnDirection_setDate = this.curDate;
            data.From_MedStaffFact_id = this.userMedStaffFact.MedStaffFact_id;
            data.MedStaffFact_id = this.userMedStaffFact.MedStaffFact_id;
            if (getGlobalOptions().lpu_id>0)
            {
                data.Lpu_did = getGlobalOptions().lpu_id;
            }
            if (getGlobalOptions().CurMedPersonal_id>0)
            {
                data.MedPersonal_id = getGlobalOptions().CurMedPersonal_id;
                data.MedPersonal_did = getGlobalOptions().CurMedPersonal_id;
            }
            if(getGlobalOptions().CurLpuSection_id>0 ){
                data.LpuSection_did=getGlobalOptions().CurLpuSection_id;
                data.LpuUnit_did=getGlobalOptions().CurLpuSection_id;
            }
            if(getGlobalOptions().CurMedService_id>0){
                data.MedService_id=getGlobalOptions().CurMedService_id;
            }
            if(getGlobalOptions().CurLpuSectionProfile_id>0){
                data.LpuSectionProfile_id=getGlobalOptions().CurLpuSectionProfile_id;
            }

            this.getLoadMask(langs('Подождите, сохраняется запись...')).show();
            Ext.Ajax.request({
                url: C_QUEUE_APPLY,
                params: data,
                callback: function(options, success, response)
                {
                    if (response) {
                        var response_text = Ext.util.JSON.decode(response.responseText);
                        if (response_text.warning) {
                            sw.swMsg.show(
                                {
                                    icon: Ext.MessageBox.QUESTION,
                                    msg: response_text.warning,
                                    title: langs('Внимание'),
                                    buttons: Ext.Msg.YESNO,
                                    fn: function(buttonId, text, obj)
                                    {
                                        if ('yes' == buttonId)
                                        {
                                            var form = Ext.getCmp('swWorkPlacePhysWindow');
                                            var data =
                                            {
                                                Person_id: response_text.Person_id,
                                                Server_id: response_text.Server_id,
                                                PersonEvn_id: response_text .PersonEvn_id,
                                                OverrideWarning: 1
                                            }

                                            form.scheduleSave(data);
                                        }
                                    }
                                });
                        }
                    }
                    this.getLoadMask().hide();
                    grid.getStore().reload();
                }.createDelegate(this)
            });
        }
	},
	/*
	*	Открывает ЭМК при нажатии без записи
	*/
	createTtgAndOpenPersonEPHForm: function(pdata)
	{
		var params = {
			Person_id: pdata.Person_id,
			Server_id: pdata.Server_id,
			PersonEvn_id: pdata.PersonEvn_id,
			userMedStaffFact: this.userMedStaffFact,
			TimetableGraf_id: pdata.TimetableGraf_id || null,
			EvnDirectionData:pdata.EvnDirectionData || null,
			mode: 'workplace',
			ARMType: this.ARMType,
			callback: function() {
				sw.Applets.uec.startUecReader();
				sw.Applets.bdz.startBdzReader();
				sw.Applets.BarcodeScaner.startBarcodeScaner();
				this.scheduleRefresh();
			}.createDelegate(this)
		};

		checkPersonPhoneVerification({
			Person_id: params.Person_id,
			MedStaffFact_id: params.userMedStaffFact.MedStaffFact_id,
			callback: function(){getWnd('swPersonEmkWindow').show(params)}
		});
	},
	/** Идентифицировать по карте, найти, открыть ЭМК
	*/
	receptionBySocCard: function()
	{
		if (!(getGlobalOptions().region && getRegionNick() == 'ufa')) {
			return false;
		}
		if (!getGlobalOptions().card_reader_is_enable) {
			return false;
		}
		var response = getSocCardNumFromReader();
		if ( response.success == true ) {
		//if (true ) {
			personSearchRequest(
			{
				params: {
					soc_card_id: response.SocCard_id,
					//PersonFirName_FirName: 'иван',
					//PersonSurName_SurName: 'петухов',
					searchMode: 'all'
				},
				onFound: function(data,totalCount) 
				{
					var pdata = data[0];
					var onIsLiving = function(){
						// проверка - записан ли такой пациент на сегодня по этому профилю к этому врачу (на это рабочее место врача)
						Ext.Ajax.request({
							params: {returnId: 1, LpuUnitType_SysNick: 'polka',Person_id: pdata.Person_id, LpuSection_id: this.userMedStaffFact.LpuSection_id, MedStaffFact_id: this.userMedStaffFact.MedStaffFact_id},
							callback: function(opt, success, response) {
								var response_obj = Ext.util.JSON.decode(response.responseText);
								if ( response_obj.success && response_obj.result )
								{
									pdata.TimetableGraf_id = response_obj.result;
									// ? pdata.EvnDirectionData = null;
								}
								this.createTtgAndOpenPersonEPHForm(pdata);
							}.createDelegate(this),
							url: '/?c=TimetableGraf&m=checkPersonByToday'
						});
					}.createDelegate(this);
					
					if(isAdmin){
						onIsLiving();
					} else {
						checkPersonDead({
							Person_id: pdata.Person_id,
							onIsLiving: onIsLiving,
							onIsDead: function(res) {
								sw.swMsg.alert(langs('Ошибка'), langs('Запись невозможна в связи со смертью пациента'));
							}
						});
					}
				}.createDelegate(this)
			});
		}
		else
			Ext.Msg.alert("Ошибка", response.ErrorMessage);

	},
	scheduleNew: function(params)
	{
		var win = this;
		// Добавление пациента вне записи
		if (getWnd('swPersonSearchWindow').isVisible())
		{
			Ext.Msg.alert(langs('Сообщение'), langs('Окно поиска человека уже открыто'));
			//getWnd('swPersonSearchWindow').hide();
			return false;
		}
		sw.Applets.uec.stopUecReader();
		sw.Applets.bdz.stopBdzReader();
		sw.Applets.BarcodeScaner.stopBarcodeScaner();
		var personParams = {
			onClose: function()
			{
				if ( win.getGrid().getSelectionModel().getSelected() ) {
					win.getGrid().getView().focusRow(win.getGrid().getStore().indexOf(win.getGrid().getSelectionModel().getSelected()));
				}
				else {
					win.getGrid().getSelectionModel().selectFirstRow();
				}
			},
			onSelect: function(pdata)
			{
				var onIsLiving = function(){
					getWnd('swPersonSearchWindow').hide();
					// проверка - стоит ли этот пациент в очереди по профилю этого отделения в данное МО или записан к этому врачу (на это рабочее место врача)
					Ext.Ajax.request({
						params: {
							useCase: 'create_evnpl_without_recording',
							Person_id: pdata.Person_id,
							MedStaffFact_id: win.userMedStaffFact.MedStaffFact_id
						},
						callback: function(opt, success, response) {
							var response_obj = Ext.util.JSON.decode(response.responseText);
							if ( Ext.isArray(response_obj) ) {
								if ( response_obj.length == 1 ) {
									var begTime = response_obj[0]['Timetable_begTime'] || null;
									if (begTime) {
										begTime = Date.parseDate(begTime, 'd.m.Y H:i');
									}
									// Создаем правильные фамилию и инициалы - добавляем точки в инициалы
									var fin = response_obj[0]['MSF_Person_Fin'] || null,
										recTime = Ext.util.Format.date(begTime, 'd-m-Y H:i'), // формат даты с '-' по ТЗ
										profileName = response_obj[0]['LpuSectionProfile_Name'] || null,
										directionNum = response_obj[0]['EvnDirection_Num'];
									if (fin !==  null)
									{
										var finArray = fin.split(' ');
										if (finArray.length > 1)
										{
											finArray[1] = finArray[1].split('').join('.');
											fin = finArray.join(' ');
										}
									}
									var initials = [pdata.PersonFirName_FirName, pdata.PersonSecName_SecName],
										IO = '';

									Ext.each(initials, function (word, index) {
										if (word === undefined || word.length === 0)
										{
										} else
										{
											IO += word[0].toUpperCase() + '.';
										}
									});
									var surnameAndInitials = pdata.PersonSurName_SurName + IO;

									if (begTime && Ext.util.Format.date(begTime, 'd.m.Y') == getGlobalOptions().date && response_obj[0]['MedStaffFact_did'] == win.userMedStaffFact.MedStaffFact_id) {
										Ext.Msg.alert(langs('Сообщение'), langs('Пациент записан на текущий день. Воспользуйтесь функцией приема по записи'));
										return false;
									}
									var msg, buttons;
									if (17 == response_obj[0]['EvnStatus_id'] || response_obj[0]['Timetable_begTime'])
									{
										msg = langs('У пациента ') + surnameAndInitials + langs(' имеется запись к врачу ') + fin + langs('. Дата и время приема: ') + recTime + langs('. Использовать имеющуюся бирку для приема? Запись пациента будет удалена из расписания');

										buttons = {
											yes: langs('Принять и удалить запись'),
											no: langs('Принять и оставить запись'),
											cancel: langs('Отмена')
										};
									} else if (10 == response_obj[0]['EvnStatus_id'] || !response_obj[0]['Timetable_begTime'])
									{
										buttons = {
											yes: langs('Принять и убрать из очереди'),
											no: langs('Принять и оставить в очереди'),
											cancel: langs('Отмена')
										};

										if (response_obj[0]['EvnDirection_IsAuto'] != 2) {

											msg = langs('Пациент ') + surnameAndInitials + langs(' имеет направление № ') + directionNum + langs(' по профилю ') + profileName + langs('. Обслужить направление? Пациент будет удален из очереди.');

											buttons.yes = langs('Обслужить направление и убрать из очереди');

										} else {
											msg = langs('Пациент ') + surnameAndInitials + langs(' находится в очереди по профилю ') + profileName + langs('. Убрать пациента из очереди?');
										}

									} else {
										// Данное направление не имеет статуса "Поставлено в очередь" или "Записано". Создать случай без связи с направлением
										pdata.EvnDirectionData=null;
										win.createTtgAndOpenPersonEPHForm(pdata);
										return false;
									}
									sw.swMsg.show(
									{
										buttons: buttons,
										fn: function( buttonId ) 
										{
											if ( buttonId == 'yes') {
												// создавать случай со связью с направлением
												pdata.EvnDirectionData=response_obj[0];
												pdata.EvnDirectionData.Diag_did = pdata.EvnDirectionData.Diag_id;
												delete pdata.EvnDirectionData.Diag_id;
												pdata.EvnDirectionData.Org_did = pdata.EvnDirectionData.Org_id;
												delete pdata.EvnDirectionData.Org_id;
												win.createTtgAndOpenPersonEPHForm(pdata);
											} else if ( buttonId == 'no') {
												// создать случай без связи с направлением
												pdata.EvnDirectionData=null;
												win.createTtgAndOpenPersonEPHForm(pdata);
											}
										},
										msg: msg,
										title: langs('Внимание!')
									});
								} else if ( response_obj.length > 1 ) {
									// выводим список этих направлений с возможностью выбрать одно из них
									getWnd('swEvnDirectionSelectWindow').show({
										useCase: 'create_evnpl_without_recording',
										storeData: response_obj,
										Person_Birthday: pdata.Person_Birthday,
										Person_Firname: pdata.Person_Firname,
										Person_Secname: pdata.Person_Secname,
										Person_Surname: pdata.Person_Surname,
										Person_id:pdata.Person_id,
										callback: function(evnDirectionData){
											if (evnDirectionData && evnDirectionData.EvnDirection_id){
												// создавать случай со связью с направлением
												pdata.EvnDirectionData=evnDirectionData;
											} else {
												// создать случай без связи с направлением
												pdata.EvnDirectionData=null;
											}
										},
										onHide: function(){
											// если направление не выбрано, то создавать случай без связи с направлением
											win.createTtgAndOpenPersonEPHForm(pdata);
										}
									});
								} else {
									// создать случай без связи с направлением
									pdata.EvnDirectionData=null;
									win.createTtgAndOpenPersonEPHForm(pdata);
								}
							}
						},
						url: '/?c=EvnDirection&m=loadEvnDirectionList'
					});
				};
				if (isAdmin){
					onIsLiving();
				} else {
					checkPersonDead({
						Person_id: pdata.Person_id,
						onIsLiving: onIsLiving,
						onIsDead: function(res) {
							sw.swMsg.alert(langs('Ошибка'), langs('Запись невозможна в связи со смертью пациента'));
						}
					});
				}
			},
			needUecIdentification: true,
			searchMode: 'all'
		};

		if (!Ext.isEmpty(params)){
			if (!Ext.isEmpty(params.Person_Firname)){
				personParams.personFirname = params.Person_Firname;
			}
			if (!Ext.isEmpty(params.Person_Secname)){
				personParams.personSecname = params.Person_Secname;
			}
			if (!Ext.isEmpty(params.Person_Surname)){
				personParams.personSurname = params.Person_Surname;
			}
			if (!Ext.isEmpty(params.Person_Birthday)){
				personParams.PersonBirthDay_BirthDay = params.Person_Birthday;
			}
			if (!Ext.isEmpty(params.Polis_Num)){
				personParams.Polis_EdNum = params.Polis_Num;
			}
		}

		getWnd('swPersonSearchWindow').show(personParams);
	},
	
	scheduleAdd: function()
	{
		
		if (this.formMode=='send')
		{
			sw.swMsg.show(
			{
				icon: Ext.MessageBox.QUESTION,
				msg: langs('Записать пациента на выбранное время?'),
				title: langs('Вопрос'),
				buttons: Ext.Msg.YESNO,
				fn: function(buttonId, text, obj)
				{
					if ('yes' == buttonId)
					{
						var form = Ext.getCmp('swWorkPlacePhysWindow');
						var data = {
							Person_id: form.saveParams.Person_id,
							Server_id: form.saveParams.Server_id,
							PersonEvn_id: form.saveParams.PersonEvn_id
						};
						form.scheduleSave(data);
					}
				}
			});
		}
		else 
		{
			if (getWnd('swPersonSearchWindow').isVisible())
			{
				Ext.Msg.alert(langs('Сообщение'), langs('Окно поиска человека уже открыто'));
				//getWnd('swPersonSearchWindow').hide();
				return false;
			}
			
			getWnd('swPersonSearchWindow').show(
			{
				onClose: function() 
				{
					// do nothing
				},
				onSelect: function(pdata) 
				{
					checkPersonDead({
						Person_id: pdata.Person_id,
						onIsLiving: function() {
							getWnd('swPersonSearchWindow').hide();
							Ext.getCmp('swWorkPlacePhysWindow').scheduleSave(pdata);
						},
						onIsDead: function(res) {
							sw.swMsg.alert(langs('Ошибка'), langs('Запись невозможна в связи со смертью пациента'));
						}
					});
				},
				searchMode: 'all'
			});
		}
	},
	/**
	 * Записать из очереди
	 */
	
	sheduleAddFromQueue: function() {
		
		var grid = this.getGrid(), me = this;
		if (!grid || !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('TimetableGraf_id')) {
			Ext.Msg.alert(langs('Ошибка'), langs('Не выбрана запись!'));
			return false;
		}
		var record = grid.getSelectionModel().getSelected();
		getWnd('swMPQueueWindow').show({
			useCase: 'record_from_queue',
			LpuSectionProfile_id: this.userMedStaffFact.LpuSectionProfile_id,
			Lpu_id: this.userMedStaffFact.Lpu_id,
			TimetableGraf_id: record.get('TimetableGraf_id'),
			params: record.data,
			userMedStaffFact: this.userMedStaffFact,
			onSelect: function(data) {
				// Действия после выбора ЭН при записи из очереди
				getWnd('swMPQueueWindow').hide();
				sw.Promed.Direction.recordFromQueue({
					queue: {
						EvnDirection_id: data['EvnDirection_id'],
						EvnQueue_id: data['EvnQueue_id']
					},
					params: {
						EvnDirection_id: data['EvnDirection_id'],
						TimetableGraf_id: record.get('TimetableGraf_id')
					},
					url: C_TTG_APPLY,
					loadMask: true,
					windowId: me.id,
					Timetable_id: record.get('TimetableGraf_id'),
					callback: function(success, response) {
						if (success) {
							var result  = Ext.util.JSON.decode(response.responseText);
							success = result.success;
						}
						if (success) {
							// обновляем грид и позиционируемся на добавленное направление
							grid.getStore().reload({
								callback: function () {
									var index = grid.getStore().findBy(function(rec) { return rec.get('TimetableGraf_id') == record.data['TimetableGraf_id']; });
									grid.focus();
									grid.getView().focusRow(index);
									grid.getSelectionModel().selectRow(index);
								}
							});
						}
					}
				});
			}
		});
	},
	scheduleEdit: function()
	{
		alert(langs('редактирование бирки'));
	},
	tryMe: function(msg){
		log(msg);
	},
	scheduleOpen: function()
	{
		var form = this;
		var grid = form.getGrid();

		if (!grid) {
			Ext.Msg.alert(langs('Ошибка'), langs('Список расписаний не найден!'));
			return false;
		} else if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('TimetableGraf_id') ){
			Ext.Msg.alert(langs('Ошибка'), langs('Не выбрана запись!'));
			return false;
		}

		var record = grid.getSelectionModel().getSelected();

		if (getGlobalOptions().fer_Person_id && record.get('Person_id') == getGlobalOptions().fer_Person_id) {
			// открываем форму поиска человека
			getWnd('swPersonSearchWindow').show({
				personFirname: record.get('Person_Firname'),
				personSecname: record.get('Person_Secname'),
				personSurname: record.get('Person_Surname'),
				autoSearch: true,
				onSelect: function(pdata)
				{
					getWnd('swPersonSearchWindow').hide();
					// апдейтим бирку
					Ext.Ajax.request({
						params: {
							TimetableGraf_id: record.get('TimetableGraf_id'),
							Person_id: pdata.Person_id
						},
						callback: function(opt, success, response) {
							var response_obj = Ext.util.JSON.decode(response.responseText);
							if (response_obj.success) {
								// апдейтим важные для открытия ЭМК данные в гриде
								record.set('Person_id', pdata.Person_id);
								record.set('PersonEvn_id', pdata.PersonEvn_id);
								record.set('Server_id', pdata.Server_id);
								record.commit();

								// запускаем заного открытие эмк
								form.scheduleOpen();
							}
						},
						url: '/?c=TimetableGraf&m=updatePersonForFerRecord'
					});

				},
				searchMode: 'all'
			});
			return false;
		}
		
		var isMyOwnRecord = record.get('pmUser_updId') == getGlobalOptions().pmuser_id;

		// если неизвестный не показываем ЭМКу
		if (!form.ElectronicQueuePanel.checkIsUnknown({record: record})) {log('scheduleOpen() is_unknown'); return false; }

		var electronicQueueData = (form.ElectronicQueuePanel.electronicQueueData
				? form.ElectronicQueuePanel.electronicQueueData
				: form.ElectronicQueuePanel.getElectronicQueueData()
		);

		if (form.ElectronicQueuePanel.electronicQueueData) form.ElectronicQueuePanel.electronicQueueData = null;

		var params = {
			Person_id: record.get('Person_id'),
			Server_id: record.get('Server_id'),
			PersonEvn_id: record.get('PersonEvn_id'),
			userMedStaffFact: this.userMedStaffFact,
			mode: 'workplace',
			isMyOwnRecord: isMyOwnRecord,
			electronicQueueData: electronicQueueData,
			ARMType: this.userMedStaffFact.ARMType,

			callback: function(owner, somethingElse, retParams) {
				// выполняем кэллбэк
				if (retParams && retParams.callback && typeof retParams.callback == 'function') retParams.callback();

			}.createDelegate(this)
		};

		switch (true) {
			case ( 15 == record.get('EvnStatus_id') ):
			case ( !Ext.isEmpty(record.get('TimetableGraf_factTime')) ):
			case ( Ext.isEmpty(record.get('TimetableGraf_Date')) ):
			case ( Ext.isEmpty(record.get('TimetableGraf_begTime')) ):
				params.TimetableGraf_id = null;
				params.EvnDirectionData = null;
				checkPersonPhoneVerification({
					Person_id: params.Person_id,
					MedStaffFact_id: params.userMedStaffFact.MedStaffFact_id,
					callback: function(){getWnd('swPersonEmkWindow').show(params)}
				});
				break;
			case (
				// в ЭО это условие не учитывается
				Ext.isEmpty(form.userMedStaffFact.ElectronicService_id)
				&& record.get('TimetableGraf_Date').getTime() != Date.parseDate(getGlobalOptions().date, 'd.m.Y').getTime() ):
				var msg;
				if ('true' == record.get('IsEvnDirection')) {
					msg = langs('Обслужить направление № ') + record.get('EvnDirection_Num') + langs(' по профилю ') + record.get('LpuSectionProfile_Name') 
						+ langs(' и освободить бирку на ') + record.get('TimetableGraf_Date').format('d.m.Y') 
						+ ' ' + record.get('TimetableGraf_begTime') + ', врач ' + record.get('MSF_Person_Fin') + '?';
				} else {
					msg = langs('Освободить бирку на ') + record.get('TimetableGraf_Date').format('d.m.Y') 
						+ ' ' + record.get('TimetableGraf_begTime') + ', врач ' + record.get('MSF_Person_Fin') + '?';
				}
				sw.swMsg.show(
				{
					buttons: {
						yes: ('true' == record.get('IsEvnDirection')) ? langs('Обслужить направление') : langs('Освободить запись'),
						no: ('true' == record.get('IsEvnDirection')) ? langs('Принять без направления') : langs('Принять без записи'),
						cancel: langs('Отмена')
					},
					fn: function( buttonId ) 
					{
						var mode;
						if ( buttonId == 'yes') {
							params.TimetableGraf_id = record.get('TimetableGraf_id');
							params.EvnDirectionData = record.data;
							checkPersonPhoneVerification({
								Person_id: params.Person_id,
								MedStaffFact_id: params.userMedStaffFact.MedStaffFact_id,
								callback: function(){getWnd('swPersonEmkWindow').show(params)}
							});
						} else if ( buttonId == 'no') {
							params.TimetableGraf_id = null;
							params.EvnDirectionData = null;
							checkPersonPhoneVerification({
								Person_id: params.Person_id,
								MedStaffFact_id: params.userMedStaffFact.MedStaffFact_id,
								callback: function(){getWnd('swPersonEmkWindow').show(params)}
							});
						}
					},
					msg: msg,
					title: langs('Вопрос')
				});
				break;
			default:
				params.TimetableGraf_id = record.get('TimetableGraf_id');
				params.EvnDirectionData = record.data;
				checkPersonPhoneVerification({
					Person_id: params.Person_id,
					MedStaffFact_id: params.userMedStaffFact.MedStaffFact_id,
					callback: function(){getWnd('swPersonEmkWindow').show(params)}
				});
				break;
		}
	},
	scheduleCopy: function()
	{
		alert('Ctrl+C');
	},
	schedulePaste: function()
	{
		alert('Ctrl+V');
	},
	getSelectedRecord: function() {
		var record = this.getGrid().getSelectionModel().getSelected();
		if ( !record || !record.get('TimetableGraf_id') ) {
			return false;
		}
		return record;
	},
	rewrite:function()
	{
		var record = this.getSelectedRecord();
		var grid = this.getGrid();
		if (record == false) {
			return false;
		}
		return sw.Promed.Direction.rewrite({
			loadMask: this.getLoadMask(langs('Пожалуйста подождите...')),
			userMedStaffFact: this.userMedStaffFact,
			EvnDirection_id: record.get('EvnDirection_id'),
			callback: function (data) {
				grid.getStore().reload({
					callback: function () {
						if (data.EvnDirection_id) {
							var index = grid.getStore().findBy( function(record) {
								if( record.get('EvnDirection_id') == data.EvnDirection_id ) {
									return true;
								}
							});
							if (index > -1) {
								grid.getView().focusRow(index);
								grid.getSelectionModel().selectRow(index);
							}
						}
					}
				});
			}
		});
	},
	redirect:function()
	{
		var record = this.getSelectedRecord();
		var grid = this.getGrid();
		if (record == false) {
			return false;
		}
		return sw.Promed.Direction.redirect({
			loadMask: this.getLoadMask(langs('Пожалуйста подождите...')),
			userMedStaffFact: this.userMedStaffFact,
			EvnDirection_id: record.get('EvnDirection_id'),
			callback: function (data) {
				grid.getStore().reload({
					callback: function () {
						if (data.EvnDirection_id) {
							var index = grid.getStore().findBy( function(record) {
								if( record.get('EvnDirection_id') == data.EvnDirection_id ) {
									return true;
								}
							});
							if (index > -1) {
								grid.getView().focusRow(index);
								grid.getSelectionModel().selectRow(index);
							}
						}
					}
				});
			}
		});
	},
	reject:function()
	{
		var record = this.getSelectedRecord();
		var grid = this.getGrid();
		if (record == false || !record.get('TimetableGraf_id') || !sw.Promed.Direction) {
			return false;
		}
		return sw.Promed.Direction.cancel({
			cancelType: 'decline',
			ownerWindow: this,
			userMedStaffFact: this.userMedStaffFact,
			EvnDirection_id: record.get('EvnDirection_id'),
			TimetableGraf_id: record.get('TimetableGraf_id'),
			personData: {
				Person_id: record.get('Person_id'),
				Server_id: record.get('Server_id'),
				PersonEvn_id: record.get('PersonEvn_id'),
				Person_IsDead: record.get('Person_IsDead'),
				Person_Firname: record.get('Person_Firname'),
				Person_Secname: record.get('Person_Secname'),
				Person_Surname: record.get('Person_Surname'),
				Person_Birthday: record.get('Person_Birthday')
			},
			callback: function (cfg) {
				grid.getStore().reload({
					callback: function () {
						var index = grid.getStore().findBy( function(rec) {
							if ( rec.get('TimetableGraf_id') == cfg.TimetableGraf_id ) {
								return true;
							}
						});
						if (index > -1) {
							grid.getView().focusRow(index);
							grid.getSelectionModel().selectRow(index);
						}
					}
				});
			}
		});
	},
	returnToQueue:function(options)
	{
		if (!options) options = {};
		var record = this.getSelectedRecord();
		var grid = this.getGrid();
		if (record == false || !record.get('TimetableGraf_id')) {
			return false;
		}

		if (!Ext.isEmpty(record.get('TimetableGraf_factTime')) && !options.ignorePriemCheck) {
			sw.swMsg.show({
				icon: Ext.MessageBox.QUESTION,
				msg: 'По данному направлению был осуществлен прием. В случае возвращения направления в очередь, связь с добавленным случаем лечения будет утеряна. Продолжить?',
				title: langs('Внимание'),
				buttons: Ext.Msg.YESNO,
				fn: function(buttonId, text, obj){
					if ('yes' == buttonId){
						var form = Ext.getCmp('swWorkPlacePhysWindow');
						form.returnToQueue({ignorePriemCheck: 1});
					}
				}
			});
			return false;
		}
		
		return sw.Promed.Direction.returnToQueue({
			loadMask: this.getLoadMask(langs('Пожалуйста подождите...')),
			EvnDirection_id: record.get('EvnDirection_id'),
			TimetableGraf_id: record.get('TimetableGraf_id'),
			EvnQueue_id: record.get('EvnQueue_id'),
			noask: !Ext.isEmpty(record.get('TimetableGraf_factTime')),
			callback: function (data) {
				grid.getStore().reload({
					callback: function () {
						if (data.EvnDirection_id) {
							var index = grid.getStore().findBy( function(record) {
								if ( data.EvnDirection_id == record.get('EvnDirection_id') ) {
									return true;
								}
							});
							if (index > -1) {
								grid.getView().focusRow(index);
								grid.getSelectionModel().selectRow(index);
							}
						}
					}
				});
			}
		});
	},
	scheduleRefresh: function(options)
	{
		if (typeof options != 'object') { options = new Object();}

		var params = new Object();
		params.begDate = Ext.util.Format.date(this.dateMenu.getValue1(), 'd.m.Y');
		params.endDate = Ext.util.Format.date(this.dateMenu.getValue2(), 'd.m.Y');
		params.MedStaffFact_id = this.userMedStaffFact.MedStaffFact_id;

		this.getGrid().loadStore(params, function() {
			if (options.callback && typeof options.callback == 'function') {
				options.callback();
			}
		});

	},
	schedulePrint:function(action)
	{
		var record = this.getGrid().getSelectionModel().getSelected();

		if (!record) {
            sw.swMsg.alert(langs('Ошибка'), langs('Запись не выбрана'));
            return false;
        }

        if (action && action == 'row') {
            Ext.ux.GridPrinter.print(this.getGrid(), {rowId: record.id});
        } else {
            Ext.ux.GridPrinter.print(this.getGrid());
        }
	},
	savePosition: function()
	{
		var record = this.getGrid().getSelectionModel().getSelected();
		if (record)
		{
			this.position = record.get('TimetableGraf_id');
		}
		else 
		{
			this.position = 0;
		}
	},
	restorePosition: function()
	{
		if ((this.position) && (this.position>0))
		{
			GridAtRecord(this.getGrid(), 'TimetableGraf_id', this.position);
		}
		else
		{
			this.getGrid().focus();
		}
		this.position = 0;
	},
	stepDay: function(day)
	{
		var frm = this;
		var date1 = (frm.dateMenu.getValue1() || Date.parseDate(frm.curDate, 'd.m.Y')).add(Date.DAY, day).clearTime();
		var date2 = (frm.dateMenu.getValue2() || Date.parseDate(frm.curDate, 'd.m.Y')).add(Date.DAY, day).clearTime();
		frm.dateMenu.setValue(Ext.util.Format.date(date1, 'd.m.Y')+' - '+Ext.util.Format.date(date2, 'd.m.Y'));
		frm.checkMedStaffFactReplace();
		// frm.dateMenu.fireEvent("select", frm.dateMenu);
	},
	prevDay: function ()
	{
		this.stepDay(-1);
	},
	setActionDisabled: function(action, flag)
	{
		if (this.gridActions[action])
		{
			this.gridActions[action].initialConfig.initialDisabled = flag;
			this.gridActions[action].setDisabled(flag);
		}
	},
	scheduleCollapseDates: function() {
		this.getGrid().getView().collapseAllGroups();
	},
	scheduleExpandDates: function() {
		this.getGrid().getView().expandAllGroups();
	},
	checkEvnPLDispDop: function() {
		var id_salt = Math.random();
		var win_id = 'print_pac_list' + Math.floor(id_salt * 10000);

		var personIds = [];
		this.getGrid().getStore().each(function(rec) {
			if (!Ext.isEmpty(rec.get('Person_id'))) {
				personIds.push(rec.get('Person_id'));
			}
		});
		
		if (personIds.length > 0) {
			window.open('/?c=EvnPLDispDop13&m=checkPersons&personIds='+Ext.util.JSON.encode(personIds), win_id);
		}
	},
	nextDay: function ()
	{
		this.stepDay(1);
	},
	currentDay: function ()
	{
		var frm = this;
		var date1 = Date.parseDate(frm.curDate, 'd.m.Y');
		var date2 = Date.parseDate(frm.curDate, 'd.m.Y');
		frm.dateMenu.setValue(Ext.util.Format.date(date1, 'd.m.Y')+' - '+Ext.util.Format.date(date2, 'd.m.Y'));
		frm.checkMedStaffFactReplace();
		// frm.dateMenu.fireEvent("select", frm.dateMenu);
	},
	currentWeek: function ()
	{
		var frm = this;
		var date1 = (Date.parseDate(frm.curDate, 'd.m.Y'));
		var dayOfWeek = (date1.getDay() + 6) % 7;
		date1 = date1.add(Date.DAY, -dayOfWeek).clearTime();
		var date2 = date1.add(Date.DAY, 6).clearTime();
    	frm.dateMenu.setValue(Ext.util.Format.date(date1, 'd.m.Y')+' - '+Ext.util.Format.date(date2, 'd.m.Y'));
		frm.checkMedStaffFactReplace();
		// frm.dateMenu.fireEvent("select", frm.dateMenu);
	},
	currentMonth: function ()
	{
		var frm = this;
		var date1 = (Date.parseDate(frm.curDate, 'd.m.Y')).getFirstDateOfMonth();
		var date2 = date1.getLastDateOfMonth();
    	frm.dateMenu.setValue(Ext.util.Format.date(date1, 'd.m.Y')+' - '+Ext.util.Format.date(date2, 'd.m.Y'));
		frm.checkMedStaffFactReplace();
		// frm.dateMenu.fireEvent("select", frm.dateMenu);
	},
	getLoadMask: function(MSG)
	{
		if (MSG) 
		{
			delete(this.loadMask);
		}
		if (!this.loadMask)
		{
			this.loadMask = new Ext.LoadMask(Ext.get(this.id), {msg: MSG});
		}
		return this.loadMask;
	},
	getCurrentDateTime: function() 
	{
		var frm = this;
		frm.getLoadMask(LOAD_WAIT).show();
		Ext.Ajax.request(
		{
			url: C_LOAD_CURTIME,
			callback: function(opt, success, response) 
			{
				if (success && response.responseText != '')
				{
					var result  = Ext.util.JSON.decode(response.responseText);
					frm.curDate = result.begDate;
					frm.curTime = result.begTime;
					frm.userName = result.pmUser_Name;
					// Проставляем время и режим
					this.mode = 'day';
					frm.currentDay();
					frm.scheduleLoad('day');
					frm.getLoadMask().hide();
				}
			}
		});
	},
	countOnGroup: function(start, count)
	{
		var result = 0;
		
		for (i=start; i < start+count; i++)
		{
			if (this.gridStore.data.items[i].get('Person_id')>0)
				result++;
		}
		/*
		this.gridStore.each(function(record) 
		{
			log(record);
			if (record.get('Person_id')>0)
				result++;
		});
		*/
		//log(this.gridStore);
		return result;
	},
	/*
	showMenu: function(id) 
	{
		if (this.menu) {
			this.menu.show(Ext.fly(id));
		}
	},
	setMenu: function(winName) {
		this.menu = sw.Promed.MedStaffFactByUser.createListWorkPlaces(10);
		this.linktitle = ' <a id="header_link_'+this.id+'" href="#" onClick="Ext.getCmp(&quot;swWorkPlacePhysWindow&quot;).showMenu(&quot;'+'header_link_'+this.id+'&quot;);">'+langs('Текущее название АРМа и отделение')+'</a>';
	},
	*/
	printAddressLeaf: function(leaf_type) {
		var grid = this.getGrid();
		if (!grid)
		{
			Ext.Msg.alert(langs('Ошибка'), langs('Список расписаний не найден!'));
			return false;
		}
		else if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('TimetableGraf_id') )
		{
			Ext.Msg.alert(langs('Ошибка'), langs('Не выбрана запись!'));
			return false;
		}
		var r = grid.getSelectionModel().getSelected();

		if ( typeof r != 'object' || !leaf_type || !leaf_type.inlist(['arrival','departure']) ) {
			return false;
		}

		var Person_id = r.get('Person_id');
		if ( Ext.isEmpty(Person_id) ) {
			sw.swMsg.alert(langs('Ошибка'), langs('Не указан пациент'));
			return false;
		}

		var Lpu_id = getGlobalOptions().lpu_id;
		if ( Ext.isEmpty(Lpu_id) ) {
			sw.swMsg.alert(langs('Ошибка'), langs('Не указано ЛПУ'));
			return false;
		}

		var tpl = '';
		if (leaf_type == 'arrival') {
			tpl = 'LeafArrival.rptdesign'
		} else
		if (leaf_type == 'departure') {
			tpl = 'LeafDeparture.rptdesign'
		}

		printBirt({
			'Report_FileName': tpl,
			'Report_Params': '&paramPerson_id='+Person_id+'&paramLpu='+Lpu_id,
			'Report_Format': 'pdf'
		});
	},

	show: function()
	{
		sw.Promed.swWorkPlacePhysWindow.superclass.show.apply(this, arguments);

		var win = this;
		//this.findById('mpwpSearch_FIO').setValue(null);
		this.findById('mpwpSearch_SurName').setValue(null);
		this.findById('mpwpSearch_SecName').setValue(null);
		this.findById('mpwpSearch_FirName').setValue(null);
		//this.findById('mpwpSearch_FIO').setValue(null);
		this.findById('mpwpSearch_BirthDay').setValue(null);
		//this.findById('mpwpSearch_UslugaComplexPid').clearValue();
		// Проверяем права пользователя открывшего форму 

		if ((!arguments[0]) || (!arguments[0].userMedStaffFact) || (!arguments[0].userMedStaffFact.ARMType))
		{
			this.hide();
			Ext.Msg.alert('Ошибка открытия формы', 'Ошибка открытия формы "'+this.title+'".<br/>Не указан тип АРМа врача.');
		} else {
			this.ARMType = arguments[0].userMedStaffFact.ARMType;
			this.userMedStaffFact = arguments[0].userMedStaffFact;
		}

		log('userMedStaffFact', this.userMedStaffFact);
		// Форма может открываться с разных мест, поэтому если она откывается для того, чтобы записать пациента к другому врачу
		// то предварительно надо запомнить параметры.



		if ((arguments[0]) && (arguments[0].formMode) && (arguments[0].formMode=='send'))
		{
			this.formMode = 'send';
			//log(this.formMode);
			this.setTitle(WND_WPMP+'/ <font color="blue">Запись пациента: '+arguments[0].Person_Surname+' '+arguments[0].Person_Firname+' '+arguments[0].Person_Secname+'</font>');
			
			if (this.saveParams)
			{
				delete(this.saveParams);
			}
			if (!this.saveParams)
			{
				this.saveParams = new Array();
				this.saveParams.curDate = this.curDate;
				this.saveParams.begTime = this.begTime;
				this.saveParams.selectBegDate = this.dateMenu.getValue1();
				this.saveParams.selectEndDate = this.dateMenu.getValue2();
				this.saveParams.Person_id = arguments[0].Person_id;
				this.saveParams.Server_id = arguments[0].Server_id;
				this.saveParams.PersonEvn_id = arguments[0].PersonEvn_id;
				this.saveParams.mode = arguments[0].mode;
				var cdate = Date.parseDate(this.curDate, 'd.m.Y');
				this.dateMenu.setValue(Ext.util.Format.date(cdate, 'd.m.Y')+' - '+Ext.util.Format.date(cdate.add(Date.DAY, 14).clearTime(), 'd.m.Y'));
				this.checkMedStaffFactReplace();
			}

			/*
			this.gridActions.create.hide();
			this.setActionDisabled('create',true);
			*/
			this.gridActions.open.hide();
			this.setActionDisabled('open',true);
			
			// Очистка грида 
			this.getGrid().clearStore();
			// Обнулим значения 
			this.MedPersonalPanel.findById('mpwpLpu_id').setValue('');
			this.MedPersonalPanel.findById('mpwpMedPersonal_id').setValue('');
			
			// Далее скроем панель выбора даты и поиска 
			this.TopPanel.hide();
			// И выведем панель врача
			this.MedPersonalPanel.show();
			this.MedPersonalPanel.findById('mpwpLpu_id').focus(true, 100);
		} else {

			// Создаем свой заголовок, единый для всех армов, на основании данных пришедших с сервера ( из User_model)
			
			//log(this.userMedStaffFact);
			//log(sw.Promed.MedStaffFactByUser);
			
			sw.Promed.MedStaffFactByUser.setMenuTitle(this, this.userMedStaffFact);
			//this.setTitle(WND_WPMP + ' (' + this.userMedStaffFact.LpuSection_Name + ' / ' + this.userMedStaffFact.MedPersonal_FIO + ')');
			if ((arguments[0]) && (arguments[0].formMode) && (arguments[0].formMode == 'open'))
			{
				// Да просто активация
				// this.formMode = 'open';
				this.scheduleRefresh();
				// если formMode был = send, то все вертаем назад на сохраненные значения
			}
			else
			{
				// Очистка грида
				this.getGrid().clearStore();
				// Медперсонал
				this.MedPersonalPanel.findById('mpwpMedPersonal_id').getStore().clearFilter();
				// При открытии формы сначала получаем текущую дату с сервера, затем получаем список записанных на текущую дату
				this.getCurrentDateTime();
			}
		
			if ((this.formMode = 'send') && (this.saveParams))
			{
				this.getGrid().clearStore();
				this.curDate = this.saveParams.curDate;
				this.begTime = this.saveParams.begTime;
				this.dateMenu.setValue(Ext.util.Format.date(this.saveParams.selectBegDate, 'd.m.Y')+' - '+Ext.util.Format.date(this.saveParams.selectEndDate, 'd.m.Y'));
				this.checkMedStaffFactReplace();
				this.Person_id = this.saveParams.Person_id;
				this.Server_id = this.saveParams.Server_id;
				this.PersonEvn_id = this.saveParams.PersonEvn_id;
				this.mode = this.saveParams.mode;
				this.MedPersonalPanel.findById('mpwpLpu_id').setValue('');
				this.MedPersonalPanel.findById('mpwpMedPersonal_id').setValue('');
				delete(this.saveParams);
				this.scheduleLoad(this.mode);
			}
			this.formMode = 'open';
			this.TopPanel.show();
			this.MedPersonalPanel.hide();
			//this.gridActions.create.show();
			this.setActionDisabled('create',false);
			this.gridActions.open.show();
			this.setActionDisabled('open',false);
			
			
			this.findById('mpwpSchedulePanel').syncSize();
		}

		for ( btnAction in this.BtnActions ) {
			if ( typeof this.BtnActions[btnAction] == 'object' ) {

				if ( !Ext.isEmpty(this.userMedStaffFact.MedStaffFactLink_id) && !Ext.isEmpty(this.BtnActions[btnAction].nn) ) {
					this.BtnActions[btnAction].hide();
				}
				else {
					this.BtnActions[btnAction].show();
				}
			}
		}

		if ( !Ext.isEmpty(this.userMedStaffFact.MedStaffFactLink_id) ) {
			this.dateMenu.setMinValue(getValidDT(this.userMedStaffFact.MedStaffFactLink_begDT, ''));
			this.dateMenu.setMaxValue(getValidDT(this.userMedStaffFact.MedStaffFactLink_endDT, ''));
		}
		else {
			this.dateMenu.setMinValue(undefined);
			this.dateMenu.setMaxValue(undefined);
		}

		// запустим ЭО
		this.ElectronicQueuePanel.initElectronicQueue();

		// Переключатель
		this.checkMedStaffFactReplace();
		this.syncSize();
	},

	checkMedStaffFactReplace: function() {
		var win = this;
		win.findById('mpwp_MedStaffFactFilterType').hide();
		win.doLayout();
		//win.getLoadMask('Проверка наличия замещаемых врачей').show();
		Ext.Ajax.request({
			url: '/?c=MedStaffFactReplace&m=checkExist',
			params: {
				MedStaffFact_id: this.userMedStaffFact.MedStaffFact_id,
				begDate: Ext.util.Format.date(this.dateMenu.getValue1(), 'd.m.Y'),
				endDate: Ext.util.Format.date(this.dateMenu.getValue1(), 'd.m.Y')
			},
			callback: function (opt, success, response) {
				win.getLoadMask().hide();

				var resp = JSON.parse(response.responseText);
				if (resp.success && resp.exist) {
					// есть замещаемые врачи
					win.findById('mpwp_MedStaffFactFilterType').show();
					win.doLayout();
				}
			}
		});
	},

	initComponent: function()
	{
		var win = this;

		// Actions
		var Actions =
		[
			{name:'open_emk', text:langs('Открыть ЭМК'), tooltip: langs('Открыть электронную медицинскую карту пациента'), iconCls : 'open16', handler: function() {this.scheduleOpen();}.createDelegate(this)},
			{name:'open', text:langs('Открыть'), tooltip: langs('Открыть'), iconCls : 'x-btn-text', icon: 'img/icons/open16.png', handler: function() {this.scheduleOpen()}.createDelegate(this)},
			{name:'reception_soc', text:langs('Принять по соц. карте'), hidden: !(getGlobalOptions().region && getRegionNick() == 'ufa'), tooltip: langs('Принять по соц. карте'), iconCls : 'copy16', handler: function() {this.receptionBySocCard()}.createDelegate(this)},
			{name:'create', text:langs('Принять без записи'), tooltip: langs('Пациент без записи'), iconCls : 'copy16', handler: function() {this.scheduleNew()}.createDelegate(this)},
			{name:'add', text:langs('Записать пациента'), tooltip: langs('Записать пациента'), iconCls : 'x-btn-text', icon: 'img/icons/add16.png', handler: function() {this.scheduleAdd()}.createDelegate(this)},
			{name:'queue', text:langs('Записать из очереди'), tooltip: langs('Записать из очереди'), iconCls : 'x-btn-text', icon: 'img/icons/add16.png', handler: function() {this.sheduleAddFromQueue()}.createDelegate(this)},
			{name:'edit', text:BTN_GRIDEDIT, tooltip: BTN_GRIDEDIT, iconCls : 'x-btn-text', icon: 'img/icons/edit16.png', handler: function() {this.scheduleEdit()}.createDelegate(this)},
			{name:'copy', text:BTN_GRIDCOPY, tooltip: BTN_GRIDCOPY, hidden: true, iconCls : 'x-btn-text', icon: 'img/icons/copy16.png', handler: function() {this.scheduleCopy()}.createDelegate(this)},
			{name:'paste', text:BTN_GRIDPASTE, tooltip: BTN_GRIDPASTE, hidden: true, iconCls : 'x-btn-text', /*icon: 'img/icons/paste16.png',*/ handler: function() {win.schedulePaste()}},
			{name:'reject', text:langs('Отклонить'), tooltip: langs('Отклонить'), iconCls : 'x-btn-text', icon: 'img/icons/delete16.png', handler: function() {win.reject()}},
			{name:'returnToQueue', text:langs('Убрать в очередь'), tooltip: langs('Убрать в очередь'), iconCls : 'x-btn-text', icon: 'img/icons/delete16.png', handler: function() {win.returnToQueue()}},
			{name:'refresh', text:BTN_GRIDREFR, tooltip: BTN_GRIDREFR, iconCls : 'x-btn-text', icon: 'img/icons/refresh16.png', handler: function() {this.scheduleRefresh()}.createDelegate(this)},
			{name:'additionally', key: 'additionally', text:langs('Дополнительно'), menu: [
				new Ext.Action({name:'rewrite', text:langs('Перезаписать'), tooltip: langs('Перезаписать'), handler: function() {win.rewrite()}}),
				new Ext.Action({name:'redirect', text:langs('Перенаправить'), tooltip: langs('Перенаправить'), handler: function() {win.redirect()}, hidden: true})
			], tooltip: langs('Дополнительно'), iconCls : 'x-btn-text', icon: 'img/icons/actions16.png'},
			{name:'actions', key: 'actions', text:langs('Действия'), menu: [
				new Ext.Action({name:'collapse_all', text:langs('Свернуть все'), tooltip: langs('Свернуть все'), handler: function() {this.scheduleCollapseDates()}.createDelegate(this)}),
				new Ext.Action({name:'expand_all', text:langs('Развернуть все'), tooltip: langs('Развернуть все'), handler: function() {this.scheduleExpandDates()}.createDelegate(this)}),
				//new Ext.Action({name:'check_evnpldispdop', text:'Проф. осмотры', tooltip: 'Профилактические осмотры', handler: function() {this.checkEvnPLDispDop()}.createDelegate(this)})
			], tooltip: langs('Действия'), iconCls : 'x-btn-text', icon: 'img/icons/actions16.png', handler: function() {}},
			{name:'printPacList', iconCls: 'print16', text: 'Печать списка пациентов', tooltip : "Печать списка пациентов", hidden: (getRegionNick() != 'ufa' && getRegionNick() != 'kz'), handler: function () {this.printPacList();}.createDelegate(this)},
			{name:'print', text:BTN_GRIDPRINT, tooltip: BTN_GRIDPRINT, iconCls : 'x-btn-text', icon: 'img/icons/print16.png', menu: [
				new Ext.Action({name:'print_rec', text:langs('Печать'), handler: function() {this.schedulePrint('row')}.createDelegate(this)}),
				new Ext.Action({name:'print_all', text:langs('Печать списка'), handler: function() {this.schedulePrint()}.createDelegate(this)}),
				//new Ext.Action({name:'leaf_arrival', text: langs('Листок прибытия'), hidden: getRegionNick()=='kz', handler: function () {this.printAddressLeaf('arrival');}.createDelegate(this)}),
				//new Ext.Action({name:'leaf_departure', text: langs('Листок убытия'), hidden: getRegionNick()=='kz', handler: function () {this.printAddressLeaf('departure');}.createDelegate(this)})
            ]}
		];
		this.gridActions = new Array();
		
		for (i=0; i < Actions.length; i++)
		{
			this.gridActions[Actions[i]['name']] = new Ext.Action(Actions[i]);
		}
		delete(Actions);
		
		// Создание popup - меню и кнопок в ToolBar. Формирование коллекции акшенов
		this.ViewContextMenu = new Ext.menu.Menu();
		this.toolItems = new Ext.util.MixedCollection(true);
		var i = 0;
		for (key in this.gridActions)
		{
			if (key!='remove')
			{
				this.toolItems.add(this.gridActions[key],key);
				if ((i == 1) || (i == 9) || (i == 10)) // || (i == 5)
					this.ViewContextMenu.add('-');
				this.ViewContextMenu.add(this.gridActions[key]);
				i++;
			}
		}
		this.ViewContextMenu.items.items[5].hide();				
		
		this.gridToolbar = new Ext.Toolbar(
		{
			id: 'mpwpToolbar',
			items:
			[
				this.gridActions.open_emk,
				this.gridActions.reception_soc,
				this.gridActions.create,
				this.gridActions.add,
				this.gridActions.queue,
				//this.gridActions.edit,
				this.gridActions.copy,
				this.gridActions.paste,
				this.gridActions.reject,
				this.gridActions.returnToQueue,
				{
					xtype : "tbseparator"
				},
				this.gridActions.refresh,
				{
					xtype : "tbseparator"
				},
				this.gridActions.print,
				this.gridActions.printPacList,
				{
					xtype : "tbseparator"
				},
				this.gridActions.additionally,
				this.gridActions.actions,
				{
					xtype : "tbseparator"
				},
				{
					xtype : "tbfill"
				},
				{
					xtype : "tbseparator"
				},
				{
					text: '0 / 0',
					xtype: 'tbtext'
				}
			]
		});		
		
		this.reader = new Ext.data.JsonReader(
		{
			id: 'TimetableGraf_id'
		},
		[{
			name: 'TimetableGraf_id'
		},
		{
			name: 'MedStaffFact_id'
		},
		{name: 'MedPersonal_id'},
		{name: 'MedPersonal_did'},
		{
			name: 'LpuSection_id'
		},
		{
			name: 'Person_id'
		},
		{
			name: 'Person_IsUnknown',
			type: 'int'
		},
		{
			name: 'Server_id'
		},
		{
			name: 'PersonEvn_id'
		},
		{
			name: 'Person_IsEvents'
		},
		{
			name: 'pmUser_updId'
		},
		{
			name: 'TimetableGraf_Date',
			type: 'date',
			dateFormat: 'd.m.Y'
		}, 
		{
			name: 'TimetableGraf_begTime'//,
			//type: 'date',
			//dateFormat: 'H:i'
		},
		{
			name: 'TimetableGraf_factTime',
			type: 'date',
			dateFormat: 'H:i'
		},
		{
			name: 'Person_FIO'
		},
		{
			name: 'Person_Surname'
		},
		{
			name: 'Person_Firname'
		},
		{
			name: 'Person_Secname'
		},
		{
			name: 'Person_Age',
			type: 'int'
		},
		{
			name: 'Person_IsBDZ'
		},
        {
            name: 'Person_Phone_all'
        },
		{
			name: 'Person_IsFedLgot'
		},
		{
			name: 'Person_IsRegLgot'
		},
		{
			name: 'Lpu_id'
		},
		{
			name: 'PersonCard_Code'
		},
		{
			name: 'Lpu_Nick'
		},
		{
			name: 'LpuRegion_Name'
		},
		{
			name: 'Person_BirthDay',
			type: 'date',
			dateFormat: 'd.m.Y'
		},
		{
			name: 'TimetableGraf_updDT',
			type: 'date',
			dateFormat: 'd.m.Y H:i'
		},
		{
			name: 'pmUser_Name'
		},
		{ name: 'EvnDirection_id' },
		{ name: 'EvnQueue_id' },
		{ name: 'EvnStatus_id' },
		{ name: 'MSF_Person_Fin' },
		{ name: 'EvnDirection_Num' },
		{ name: 'LpuSectionProfile_Name' },
		{ name: 'IsEvnDirection' },
		{ name: 'PersonEncrypHIV_Encryp' },
		{ name: 'ARMType_id' }
		]);

		this.storeInitObject = {
			reader: this.reader,
			autoLoad: false,
			url: C_TTG_LISTDAY,
			sortInfo:
			{
				field: 'TimetableGraf_begTime',
				direction: 'ASC'
			},
			groupField: 'TimetableGraf_Date',
			listeners:
			{
				load: function(store, record, options)
				{
					callback:
					{
						var count = store.getCount();
						var form = Ext.getCmp('swWorkPlacePhysWindow');
						var grid = form.ScheduleGrid;
						if (count>0)
						{
							// Если ставится фокус при первом чтении или количество чтений больше 0
							if (!grid.getTopToolbar().hidden)
							{
								grid.getTopToolbar().items.last().el.innerHTML = '0 / '+count;
							}
							if (!form.readOnly)
							{
								if (!form.gridActions.open.initialConfig.initialDisabled)
									form.gridActions.open.setDisabled(false);
								if (!form.gridActions.create.initialConfig.initialDisabled)
									form.gridActions.create.setDisabled(false);
								if (!form.gridActions.add.initialConfig.initialDisabled)
									form.gridActions.add.setDisabled(false);
								if (!form.gridActions.queue.initialConfig.initialDisabled)
									form.gridActions.queue.setDisabled(false);
								if (!form.gridActions.edit.initialConfig.initialDisabled)
									form.gridActions.edit.setDisabled(false);
								if (!form.gridActions.reject.initialConfig.initialDisabled)
									form.gridActions.reject.setDisabled(false);
								if (!form.gridActions.returnToQueue.initialConfig.initialDisabled)
									form.gridActions.returnToQueue.setDisabled(false);
								if (!form.gridActions.copy.initialConfig.initialDisabled)
									form.gridActions.copy.setDisabled(false);
								if (!form.gridActions.refresh.initialConfig.initialDisabled)
									form.gridActions.refresh.setDisabled(false);
							}
							form.restorePosition();
							//grid.focus();
							store.each(function(record)
							{
								//log(record.get('TimetableGraf_factTime'));
								if (record.get('TimetableGraf_factTime')!='')
								{
									record.set('Person_IsEvents', "true");
									record.commit();
								}
							});

							//form.scheduleCollapseDates(); // Беру на себя ответственность - пока это явно лишнее
						} else {
							grid.focus();
						}
					}
				},
				clear: function()
				{
					var form = Ext.getCmp('swWorkPlacePhysWindow');
					form.gridActions.open.setDisabled(true);
					//form.gridActions.create.setDisabled(true);
					form.gridActions.add.setDisabled(true);
					form.gridActions.queue.setDisabled(true);
					form.gridActions.edit.setDisabled(true);
					form.gridActions.copy.setDisabled(true);
					form.gridActions.reject.setDisabled(true);
					form.gridActions.returnToQueue.setDisabled(true);
				},
				beforeload: function()
				{

				}
			}
		}
        
		this.gridStore = new Ext.data.GroupingStore(this.storeInitObject);

		this.dateMenu = new Ext.form.DateRangeField(
		{
			width: 150,
			testId: 'wnd_workplace_dateMenu',
			fieldLabel: langs('Период'),
			plugins: 
			[
				new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)
			]
		});
		
		this.dateMenu.addListener('keydown',function (inp, e) 
		{
			var form = Ext.getCmp('swWorkPlacePhysWindow');
			if (e.getKey() == Ext.EventObject.ENTER)
			{
				e.stopEvent();
				form.scheduleLoad('period');
				form.checkMedStaffFactReplace();
			}
		});
		this.dateMenu.addListener('select',function () 
		{
			// Читаем расписание за период
			var form = Ext.getCmp('swWorkPlacePhysWindow');
			form.scheduleLoad('period');
			form.checkMedStaffFactReplace();
		});
		
		this.formActions = new Array();
		this.formActions.selectDate = new Ext.Action(
		{
			text: ''
		});
		this.formActions.prev = new Ext.Action(
		{
			text: langs('Предыдущий'),
			xtype: 'button',
			iconCls: 'arrow-previous16',
			handler: function()
			{
				// на один день назад
				this.prevDay();
				this.scheduleLoad('range');
			}.createDelegate(this)
		});
		this.formActions.next = new Ext.Action(
		{
			text: langs('Следующий'),
			xtype: 'button',
			iconCls: 'arrow-next16',
			handler: function()
			{
				// на один день вперед
				this.nextDay();
				this.scheduleLoad('range');
			}.createDelegate(this)
		});
		this.formActions.day = new Ext.Action(
		{
			text: langs('День'),
			xtype: 'button',
			toggleGroup: 'periodToggle',
			iconCls: 'datepicker-day16',
			pressed: true,
			handler: function()
			{
				this.currentDay();
				this.scheduleLoad('day');
			}.createDelegate(this)
		});
		this.formActions.week = new Ext.Action(
		{
			text: langs('Неделя'),
			xtype: 'button',
			toggleGroup: 'periodToggle',
			iconCls: 'datepicker-week16',
			handler: function()
			{
				this.currentWeek();
				this.scheduleLoad('week');
			}.createDelegate(this)
		});
		this.formActions.month = new Ext.Action(
		{
			text: langs('Месяц'),
			xtype: 'button',
			toggleGroup: 'periodToggle',
			iconCls: 'datepicker-month16',
			handler: function()
			{
				this.currentMonth();
				this.scheduleLoad('month');
			}.createDelegate(this)
		});
		this.formActions.range = new Ext.Action(
		{
			text: langs('Период'),
			disabled: true,
			xtype: 'button',
			toggleGroup: 'periodToggle',
			iconCls: 'datepicker-range16',
			handler: function()
			{
				this.scheduleLoad('range');
			}.createDelegate(this)
		});
		
		/*
		this.ARMCombo = Ext.extend(sw.Promed.SwBaseLocalCombo, {
			store: new Ext.data.SimpleStore({
				key: 'arm_id',
				autoLoad: true,
				fields: [
					{name:'arm_id',type:'int'},
					{name:'arm_name',type:'string'},
					{name:'arm_form',type:'string'}
				],
				data : [
					['1','•	АРМ врача поликлиники', 'swWorkPlacePhysWindow'],
					['2','•	АРМ приемного отделения', 'swWorkPlacePhysWindow']
				]
			}),
			anchor: '100%',
			editable: false,
			fieldLabel: langs('АРМ'),
			//emptyText:'Выберите АРМ...',
			displayField:'arm_name',
			value: 1,
			valueField:'arm_id',
			hiddenName:'arm_id',
			tpl: '<tpl for="."><div class="x-combo-list-item">{arm_name}</div></tpl>',
			initComponent: function() {
				sw.Promed.ARMCombo.superclass.initComponent.apply(this, arguments);
			}
		});
		*/
		
		/*
		this.dateTpl = new Ext.Template(['<span style="font-weight: bold; font-size: 16px">{period}</span>']);
		
		this.dateText = new Ext.Toolbar.TextItem(
		{
			tpl: this.dateTpl
		});
		*/
		this.DoctorToolbar = new Ext.Toolbar(
		{
			items: 
			[
				this.formActions.prev, 
				{
					xtype : "tbseparator"
				},
				this.dateMenu,
				//this.dateText,
				{
					xtype : "tbseparator"
				},
				this.formActions.next, 
				{
					xtype: 'tbfill'
				},
				this.formActions.day, 
				this.formActions.week, 
				this.formActions.month,
				this.formActions.range/*,
				this.ARMSelectBth*/
			]
		});

		this.filtersPanel = new Ext.FormPanel(
		{
			xtype: 'form',
			labelAlign: 'right',
			labelWidth: 50,
			autoHeight: true,
			items:
			[{
				listeners: {
					collapse: function(p) {
						wnd.doLayout();
					},
					expand: function(p) {
						wnd.doLayout();
					}
				},
				xtype: 'fieldset',
				style: 'margin: 5px 0 0 0',
				height: 60,
				title: langs('Поиск'),
				collapsible: true,
				layout: 'column',
				items:
				[/*{
					layout: 'form',
					items:
					[{
						xtype: 'textfieldpmw',
						width: 300,
						id: 'mpwpSearch_FIO',
						fieldLabel: langs('ФИО'),
						listeners:
						{
							'keydown': function (inp, e)
							{
								var form = Ext.getCmp('swWorkPlacePhysWindow');
								if (e.getKey() == Ext.EventObject.ENTER)
								{
									e.stopEvent();
									form.scheduleLoad();
								}
							}
						}
					}]
				}*/{
					layout: 'form',
					labelWidth: 55,
					items:
					[{
						xtype: 'textfieldpmw',
						width: 120,
						id: 'mpwpSearch_SurName',
						fieldLabel: langs('Фамилия'),
						listeners:
						{
							'keydown': function (inp, e)
							{
								var form = Ext.getCmp('swWorkPlacePhysWindow');
								if (e.getKey() == Ext.EventObject.ENTER)
								{
									e.stopEvent();
									form.scheduleLoad();
								}
							}
						}
					}]
				},{
					layout: 'form',
					items:
					[{
						xtype: 'textfieldpmw',
						width: 120,
						id: 'mpwpSearch_FirName',
						fieldLabel: langs('Имя'),
						listeners:
						{
							'keydown': function (inp, e)
							{
								var form = Ext.getCmp('swWorkPlacePhysWindow');
								if (e.getKey() == Ext.EventObject.ENTER)
								{
									e.stopEvent();
									form.scheduleLoad();
								}
							}
						}
					}]
				},{
					layout: 'form',
					labelWidth: 75,
					items:
					[{
						xtype: 'textfieldpmw',
						width: 120,
						id: 'mpwpSearch_SecName',
						fieldLabel: langs('Отчество'),
						listeners:
						{
							'keydown': function (inp, e)
							{
								var form = Ext.getCmp('swWorkPlacePhysWindow');
								if (e.getKey() == Ext.EventObject.ENTER)
								{
									e.stopEvent();
									form.scheduleLoad();
								}
							}
						}
					}]
				},
				{
					layout: 'form',
					labelWidth: 110,
					items:
					[{
						xtype: 'swdatefield',
						format: 'd.m.Y',
						plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
						id: 'mpwpSearch_BirthDay',
						fieldLabel: langs('Дата рождения'),
						listeners:
						{
							'keydown': function (inp, e)
							{
								var form = Ext.getCmp('swWorkPlacePhysWindow');
								if (e.getKey() == Ext.EventObject.ENTER)
								{
									e.stopEvent();
									form.scheduleLoad();
								}
							}
						}
					}]
				},
				/*{
						layout: 'form',
						labelWidth: 150,
						items:
						[{
							xtype: 'swuslugacomplexpidcombo',
							width: 200,
							id: 'mpwpSearch_UslugaComplexPid',
							fieldLabel: langs('Цель направления'),
							listeners:
							{
								'keydown': function (inp, e)
								{
									var form = Ext.getCmp('swWorkPlacePhysWindow');
									if (e.getKey() == Ext.EventObject.ENTER)
									{
										e.stopEvent();
										form.scheduleLoad();
									}
								}
							}
						}]
					},*/
				{
					layout: 'form',
					items:
					[{
						style: "padding-left: 20px",
						xtype: 'button',
						id: 'mpwpBtnSearch',
						text: langs('Найти'),
						iconCls: 'search16',
						handler: function()
						{
							var form = Ext.getCmp('swWorkPlacePhysWindow');
							form.scheduleLoad();
						}
					}]
				},
				{
					layout: 'form',
					items:
					[{
						style: "padding-left: 10px",
						xtype: 'button',
						id: 'mpwpBtnClear',
						text: langs('Сброс'),
						iconCls: 'resetsearch16',
						handler: function()
						{
							var form = Ext.getCmp('swWorkPlacePhysWindow');
							//form.findById('mpwpSearch_FIO').setValue(null);
							form.findById('mpwpSearch_SurName').setValue(null);
							form.findById('mpwpSearch_FirName').setValue(null);
							form.findById('mpwpSearch_SecName').setValue(null);
							form.findById('mpwpSearch_BirthDay').setValue(null);
							form.scheduleLoad();
						}
					}]
				},
				{
					layout: 'form',
					items:
					[{
						style: "padding-left: 10px",
						xtype: 'button',
						text: langs('Считать с карты'),
						iconCls: 'idcard16',
						handler: function()
						{
							win.readFromCard();
						}
					}]
				}]
			}, {
				title: 'Список записанных пациентов',
				id: 'mpwp_MedStaffFactFilterType',
				xtype: 'fieldset',
				layout: 'column',
				autoHeight: true,
				items: [{
					layout: 'form',
					items: [{
						xtype: 'radio',
						hideLabel: true,
						boxLabel: 'Свои',
						inputValue: 1,
						name: 'MedStaffFactFilterType_id',
						listeners: {
							'check': function() {
								form.scheduleLoad();
							}
						}
					}]
				}, {
					layout: 'form',
					items: [{
						xtype: 'radio',
						hideLabel: true,
						boxLabel: 'Пациенты врача по замещению',
						inputValue: 2,
						name: 'MedStaffFactFilterType_id',
						listeners: {
							'check': function() {
								form.scheduleLoad();
							}
						}
					}]
				}, {
					layout: 'form',
					items: [{
						xtype: 'radio',
						hideLabel: true,
						boxLabel: 'Все',
						inputValue: 3,
						name: 'MedStaffFactFilterType_id',
						listeners: {
							'check': function() {
								form.scheduleLoad();
							}
						},
						checked: true
					}]
				}]
			}]
		}
		)

		this.TopPanel = new Ext.Panel(
		{
			region: 'north',
			frame: true,
			border: false,
			autoHeight: true,
			tbar: this.DoctorToolbar,
			items: 
			[
				this.filtersPanel
			]
		})
	
		this.MedPersonalPanel = new Ext.Panel(
		{
			region: 'north',
			border: false,
			frame: true,
			autoHeight: true,
			items: 
			[{
				xtype: 'form',
				labelAlign: 'right',
				labelWidth: 50,
				items: 
				[{
					xtype: 'fieldset',
					height: 60,
					title: langs('ЛПУ'),
					layout: 'column',
					items: 
					[{
						layout: 'form',
						columnWidth: .3,
						items: 
						[{
							fieldLabel: langs('ЛПУ'),
							anchor:'100%',
							hiddenName: 'Lpu_id',
							id: 'mpwpLpu_id',
							lastQuery: '',
							width : 300,
							xtype: 'swlpulocalcombo',
							listeners: 
							{
								change: function(combo, nv, ov)
								{
									var form = Ext.getCmp('swWorkPlacePhysWindow');
									form.MedPersonalPanel.findById('mpwpMedPersonal_id').getStore().load(
									{
										params:
										{
											Lpu_id: nv
										},
										callback: function()
										{
											form.MedPersonalPanel.findById('mpwpMedPersonal_id').setValue('');
										}
									});
								}
							}
						}]
					}, 
					{
						layout: 'form',
						columnWidth: .35,
						items: 
						[{
							fieldLabel: langs('Врач'),
							anchor:'100%',
							hiddenName: 'MedPersonal_id',
							id: 'mpwpMedPersonal_id',
							lastQuery: '',
							listWidth: 420,
							editable: true,
							xtype: 'swmedpersonalcombo'
						}]
					}, 
					{
						layout: 'form',
						items: 
						[{
							style: "padding-left: 20px",
							xtype: 'button',
							id: 'mpwpBtnMPSearch',
							text: langs('Показать расписание'),
							iconCls: 'search16',
							handler: function()
							{
								var form = Ext.getCmp('swWorkPlacePhysWindow');
								form.scheduleLoad();
							}
						}]
					}, 
					{
						layout: 'form',
						items: 
						[{
							style: "padding-left: 20px",
							xtype: 'button',
							id: 'mpwpBtnMPClose',
							text: langs('Вернуться к пациенту'),
							iconCls: 'close16',
							handler: function()
							{
								var form = Ext.getCmp('swWorkPlacePhysWindow');
								if (getWnd('swPersonEmkWindow').isVisible())
									getWnd('swPersonEmkWindow').toFront();
								else
								{
									if (form.saveParams)
									{
										getWnd('swPersonEmkWindow').show(
										{
											Person_id: form.saveParams.Person_id,
											Server_id: form.saveParams.Server_id,
											PersonEvn_id: form.saveParams.PersonEvn_id,
											mode: 'workplace',
											userMedStaffFact: form.userMedStaffFact,
											ARMType: form.ARMType,
											callback: function() 
											{
												this.scheduleRefresh();
											}.createDelegate(form)
										});
									}
									else 
									{
										getWnd('swWorkPlacePhysWindow').show({formMode:'open'});
									}
								}
							}
						}]
					}]
				}]
			}]
		});
		
		this.ScheduleGrid = new Ext.grid.GridPanel(
		{
			region: 'center',
			//layout: 'fit',
			//frame: true,
			tbar: this.gridToolbar,
			store: this.gridStore,
			loadMask: true,
			stripeRows: true,
			columns:
			[{
				header: "id",
				hidden: true,
				hideable: false,
				dataIndex: 'TimetableGraf_id',

			},
			{
				header: "Место работы",
				hidden: true,
				hideable: false,
				dataIndex: 'MedStaffFact_id'
			},
			{
				header: langs('Отделение'),
				hidden: true,
				hideable: false,
				dataIndex: 'LpuSection_id'
			},
			{
				header: "pid",
				hidden: true,
				hideable: false,
				dataIndex: 'Person_id'
			},
			{
				header: "sid",
				hidden: true,
				hideable: false,
				dataIndex: 'Server_id'
			},
			{
				header: "uid",
				hidden: true,
				hideable: false,
				dataIndex: 'pmUser_updId'
			},
			{
				header: langs('Осмотр'),
				width: 65,
				sortable: true,
				dataIndex: 'Person_IsEvents',
				renderer: sw.Promed.Format.checkColumn
			},
			{
				header: langs('Дата'),
				width: 60,
				hidden: true,
				sortable: true,
				dataIndex: 'TimetableGraf_Date',
				renderer: Ext.util.Format.dateRenderer('d.m.Y')
			}, 
			{
				header: 'Записан (когда)',
				width: 80,
				sortable: true,
				//renderer: Ext.util.Format.dateRenderer('H:i'),
				dataIndex: 'TimetableGraf_begTime'
			},
			{
				header: langs('Прием'),
				width: 60,
				sortable: true,
				renderer: Ext.util.Format.dateRenderer('H:i'),
				dataIndex: 'TimetableGraf_factTime'
			}, 
			{
				header: "EvnVizit",
				hidden: true,
				hideable: false,
				dataIndex: 'EvnVizit_id'
			},
			{
				header: langs("Фамилия Имя Отчество"),
				width: 250,
				sortable: true,
				dataIndex: 'Person_FIO'
			}, 
			{
				header: langs("Дата рождения"),
				width: 100,
				sortable: true,
				dataIndex: 'Person_BirthDay',
				renderer: Ext.util.Format.dateRenderer('d.m.Y')
			},
			{
				header: langs('Возраст'),
				width: 55,
				sortable: true,
				dataIndex: 'Person_Age'
			},
            {
                header: langs('Телефон'),
                width: 80,
                sortable: true,
                dataIndex: 'Person_Phone_all'
            },
			{
				header: langs('Направление'),
				width: 100,
				sortable: false,
				dataIndex: 'IsEvnDirection',
				renderer: sw.Promed.Format.dirColumn
			},
			{ header: "EvnDirection_id", hidden: true, hideable: false, dataIndex: 'EvnDirection_id' },
			{ header: "EvnQueue_id", hidden: true, hideable: false, dataIndex: 'EvnQueue_id' },
			{ header: "EvnStatus_id", hidden: true, hideable: false, dataIndex: 'EvnStatus_id' },
			{
				header: langs('БДЗ'),
				width: 40,
				sortable: false,
				dataIndex: 'Person_IsBDZ',
				renderer: sw.Promed.Format.checkColumn
			},
			/* Убрал простое отображение льготы, добавил федеральную и региональную (http://172.19.61.24:85/issues/show/2095)*/
			/*{
				header: langs('Льгота'),
				width: 5,
				sortable: false,
				dataIndex: 'Person_IsLgot',
				renderer: sw.Promed.Format.checkColumn
			},*/
			{
				header: (getRegionNick().inlist([ 'kz' ]) ? "Льгота" : "ФЛ"),
				width: 40,
				sortable: false,
				dataIndex: 'Person_IsFedLgot',
				renderer: sw.Promed.Format.checkColumn
			},
			{
				header: langs('РЛ'),
				width: 40,
				sortable: false,
				dataIndex: 'Person_IsRegLgot',
				renderer: sw.Promed.Format.checkColumn,
				hideable: !getRegionNick().inlist([ 'kz' ]),
				hidden: getRegionNick().inlist([ 'kz' ])
			},
			{
				header: langs("№ амб. карты"),
				width: 80,
				sortable: false,
				dataIndex: 'PersonCard_Code'
			},
			{
				header: langs("МО прикр."),
				width: 80,
				sortable: false,
				dataIndex: 'Lpu_Nick'
			},
			{
				header: langs('Участок'),
				width: 80,
				sortable: false,
				dataIndex: 'LpuRegion_Name'
			},
			{
				header: langs('Записан'),
				width: 100,
				sortable: true,
				dataIndex: 'TimetableGraf_updDT',
				renderer: Ext.util.Format.dateRenderer('d.m.Y H:i')
			}, 
			{
				header: langs('Оператор'),
				width: 150,
				sortable: true,
				dataIndex: 'pmUser_Name'
			},
			{
				header: 'Записан (к кому)',
				width: 115,
				sortable: true,
				//renderer: Ext.util.Format.dateRenderer('H:i'),
				dataIndex: 'MSF_Person_Fin'
			},
			{
				header: "ARMType_id",
				hidden: true,
				dataIndex: 'ARMType_id'
			}],
			
			view: new Ext.grid.GroupingView(
			{
				//forceFit: true,
                enableGroupingMenu:false,
				groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length.inlist([2,3,4]) ? langs("записи") : langs("записей")]})'
			}),
			loadStore: function(params, callback)
			{
				if (!this.params) this.params = null;
				if (params){

					if (win.filtersPanel.getForm().findField('MedStaffFactFilterType_id').getGroupValue())
						params.MedStaffFactFilterType_id = win.filtersPanel.getForm().findField('MedStaffFactFilterType_id').getGroupValue();

					this.params = params;
					win.ElectronicQueuePanel.setElectronicQueueLoadStoreParams();
				}

				// если есть интервал авто-обновления грида, не показываем прелоадер
				if (!win.refreshInterval) {win.ScheduleGrid.loadMask.show();}

				win.ScheduleGrid.getStore().baseParams = this.params;
				Ext.Ajax.request({
					params: this.params,
					url: C_TTG_LISTDAY,
					callback: function(options, success, response)
					{
						win.ScheduleGrid.loadMask.hide();
						if (success)
						{
							// запоминаем выделенную запись
							var TimetableGraf_id = null;
							var record = win.ScheduleGrid.getSelectionModel().getSelected();
							if (record && record.get('TimetableGraf_id')) {
								TimetableGraf_id = record.get('TimetableGraf_id');
							}

							// запоминаем скролл
							var scrollState = win.ScheduleGrid.getView().getScrollState();

							var response_obj = Ext.util.JSON.decode(response.responseText);
							win.ScheduleGrid.getStore().loadData(response_obj);

							if (TimetableGraf_id) {
								// чтобы фокус не слетал после рефреша.
								index = win.ScheduleGrid.getStore().findBy(function(record) {
									if (record.get('TimetableGraf_id') == TimetableGraf_id) {
										return true;
									} else {
										return false;
									}
								});
								if (index >= 0) {
									win.ScheduleGrid.getView().focusRow(index);
									win.ScheduleGrid.getSelectionModel().selectRow(index);
								}
							}

							win.ScheduleGrid.getView().restoreScroll(scrollState);
							if (callback && typeof callback == 'function'){callback();}
						}
					}
				});
			},
			clearStore: function()
			{
				if (this.getEl())
				{
					if (this.getTopToolbar().items.last())
						this.getTopToolbar().items.last().el.innerHTML = '0 / 0';
					this.getStore().removeAll();
				}
				var form = Ext.getCmp('swWorkPlacePhysWindow');
				var print_menu = form.gridActions.print.initialConfig.menu;
				//print_menu[2].setDisabled(true);	//Листок прибытия
				//print_menu[3].setDisabled(true);	//Листок убытия
			},
			focus: function () 
			{
				if (this.getStore().getCount()>0)
				{
					if (!Ext.isEmpty(win.userMedStaffFact.ElectronicService_id)) {
						//win.electronicQueueRowFocus();
					} else {
						this.getView().focusRow(0);
						this.getSelectionModel().selectFirstRow();
					}
				}
			},
			hasPersonData: function()
			{
				return this.getStore().fields.containsKey('Person_id') && this.getStore().fields.containsKey('Server_id');
			},
			sm: new Ext.grid.RowSelectionModel(
			{
				singleSelect: true,
				listeners:
				{
					'rowselect': function(sm, rowIdx, record)
					{
						var form = Ext.getCmp('swWorkPlacePhysWindow');
						var count = this.grid.getStore().getCount();
						var rowNum = rowIdx + 1;
						if ((record.get('TimetableGraf_id')==null) || (record.get('TimetableGraf_id')==''))
						{
							count = 0;
							rowNum = 0;
						}
						if (!this.grid.getTopToolbar().hidden)
						{
							this.grid.getTopToolbar().items.last().el.innerHTML = rowNum+' / '+count;
						}

						// Проверка ввода человека
						if (!form.readOnly)
						{
							var print_menu = form.gridActions.print.initialConfig.menu;

							if ((record.get('Person_id')==null) || (record.get('Person_id')==''))
							{
								var TimetableGraf_Date = null;
								if (record.get('TimetableGraf_Date')) {
									TimetableGraf_Date = Date.parseDate(record.get('TimetableGraf_Date').dateFormat('d.m.Y') + ' ' + record.get('TimetableGraf_begTime'), 'd.m.Y H:i');
								}
								var current_date = Date.parseDate(form.curDate + ' ' + form.curTime, 'd.m.Y H:i:s');
								if (!form.gridActions.open.initialConfig.initialDisabled)
									form.gridActions.open.setDisabled(true);
								if (!form.gridActions.open_emk.initialConfig.initialDisabled)
									form.gridActions.open_emk.setDisabled(true);
								/*if (!form.gridActions.create.initialConfig.initialDisabled)
								// блокируем кнопку "Без записи" в предыдущих днях, т.к. запись все равно происходит на текущий день
								{
									form.gridActions.create.setDisabled(current_date > TimetableGraf_Date);
								}*/
								if (!form.gridActions.add.initialConfig.initialDisabled)
									// запрещаем запись пациента на прошедшую дату
									form.gridActions.add.setDisabled(
										Ext.isEmpty(form.userMedStaffFact.ElectronicService_id) &&
										getGlobalOptions().disallow_recording_for_elapsed_time == true &&
										current_date > TimetableGraf_Date
									);
								if (!form.gridActions.queue.initialConfig.initialDisabled)
									// запрещаем запись из очереди на прошедшую дату
									form.gridActions.queue.setDisabled(
										Ext.isEmpty(form.userMedStaffFact.ElectronicService_id) &&
										getGlobalOptions().disallow_recording_for_elapsed_time == true &&
										current_date > TimetableGraf_Date
									);
								if (!form.gridActions.edit.initialConfig.initialDisabled)
									form.gridActions.edit.setDisabled(true);
								if (!form.gridActions.copy.initialConfig.initialDisabled)
									form.gridActions.copy.setDisabled(true);
								if (!form.gridActions.paste.initialConfig.initialDisabled)
									form.gridActions.paste.setDisabled(true);
								if (!form.gridActions.reject.initialConfig.initialDisabled)
									form.gridActions.reject.setDisabled(true);
								if (!form.gridActions.returnToQueue.initialConfig.initialDisabled)
									form.gridActions.returnToQueue.setDisabled(true);

								//print_menu[2].setDisabled(true);	//Листок прибытия
								//print_menu[3].setDisabled(true);	//Листок убытия
							}
							else 
							{
								var TimetableGraf_Date = null
								if (record.get('TimetableGraf_Date')) {
									TimetableGraf_Date = Date.parseDate(record.get('TimetableGraf_Date').dateFormat('d.m.Y') + ' ' + record.get('TimetableGraf_begTime'), 'd.m.Y H:i');
								}
								var current_date = Date.parseDate(form.curDate + ' ' + form.curTime, 'd.m.Y H:i:s');
								
								if (!form.gridActions.open.initialConfig.initialDisabled)
									form.gridActions.open.setDisabled(false);
								if (!form.gridActions.open_emk.initialConfig.initialDisabled)
									form.gridActions.open_emk.setDisabled(false);
								if (!form.gridActions.create.initialConfig.initialDisabled)
									form.gridActions.create.setDisabled(false);
								if (!form.gridActions.add.initialConfig.initialDisabled)
									form.gridActions.add.setDisabled(true);
								if (!form.gridActions.queue.initialConfig.initialDisabled)
									form.gridActions.queue.setDisabled(true);
								if (!form.gridActions.edit.initialConfig.initialDisabled)
									form.gridActions.edit.setDisabled(false);
								if (!form.gridActions.copy.initialConfig.initialDisabled)
									form.gridActions.copy.setDisabled(false);
								if (!form.gridActions.paste.initialConfig.initialDisabled)
									form.gridActions.paste.setDisabled(false);

								//print_menu[2].setDisabled(!Ext.isEmpty(record.get('PersonEncrypHIV_Encryp')));	//Листок прибытия
								//print_menu[3].setDisabled(!Ext.isEmpty(record.get('PersonEncrypHIV_Encryp')));	//Листок убытия

								if (!form.gridActions.reject.initialConfig.initialDisabled)
									form.gridActions.reject.setDisabled( // Disabled where
										record.get('ARMType_id') == 24 || 
										(record.get('EvnStatus_id') && record.get('EvnStatus_id').inlist([12,13,15])) || 
										(
											getGlobalOptions().disallow_canceling_el_dir_for_elapsed_time == true &&
											record.get('EvnDirection_id') &&
											current_date > TimetableGraf_Date
										) ||
										(
											getGlobalOptions().allow_canceling_without_el_dir_for_past_days != true &&
											! record.get('EvnDirection_id') &&
											current_date > TimetableGraf_Date
										) ||
										! (
											getGlobalOptions().evn_direction_cancel_right_mo_where_adressed === '2' ||
											'toCurrMoDirCancel'.inlist(getGlobalOptions().groups.split('|')) ||
											(
												(record.get('MedPersonal_did') || getGlobalOptions().CurMedPersonal_id) &&
												record.get('MedPersonal_did') == getGlobalOptions().CurMedPersonal_id
											)
										)
									);
								if (!form.gridActions.returnToQueue.initialConfig.initialDisabled)
									form.gridActions.returnToQueue.setDisabled( // Disabled where
										(getGlobalOptions().disallow_canceling_el_dir_for_elapsed_time == true && 
										record.get('EvnDirection_id') && 
										current_date > TimetableGraf_Date)
										||
										(getGlobalOptions().allow_canceling_without_el_dir_for_past_days != true && 
										!record.get('EvnDirection_id') && 
										current_date > TimetableGraf_Date)
										|| 
										(record.get('pmUser_updId') >= 1000000 && record.get('pmUser_updId') <= 5000000 && getRegionNick() == 'kareliya')
									);
							}
						}
					},
					'rowdeselect': function(sm, rowIdx, record){}
				}
			})
		});
		
		// Добавляем созданное popup-меню к гриду
		this.ScheduleGrid.addListener('rowcontextmenu', onMessageContextMenu,this);
		this.ScheduleGrid.on('rowcontextmenu', function(grid, rowIndex, event)
		{
			// На правый клик переходим на выделяемую запись
			grid.getSelectionModel().selectRow(rowIndex);
		});
		// Функция вывода меню по клику правой клавиши
		function onMessageContextMenu(grid, rowIndex, e)
		{
			e.stopEvent();
			var coords = e.getXY();
			this.ViewContextMenu.showAt([coords[0], coords[1]]);
		}
		// Даблклик на редактирование
		this.ScheduleGrid.on('celldblclick', function(grid, row, col, object)
		{
			var win = Ext.getCmp('swWorkPlacePhysWindow');
			var rec = grid.getSelectionModel().getSelected();
			var isPerson = (rec.get('Person_id')>0);
			if (isPerson)
			{
				if (!win.gridActions.open.isDisabled())
				{
					win.gridActions.open.execute();
				}
			}
			else
			{
				if (!win.gridActions.add.isDisabled())
				{
					win.gridActions.add.execute();
				}
			}
		});
		// Клин на иконку направления
		this.ScheduleGrid.on('cellclick', function(grid, row, col, object)
		{
			var win = Ext.getCmp('swWorkPlacePhysWindow');
			var rec = grid.getSelectionModel().getSelected();

			var column = grid.getColumnModel().getColumnById(grid.getColumnModel().getColumnId(col));

			if ( column && column.dataIndex == 'IsEvnDirection' && 'true' == rec.get('IsEvnDirection') ) { // столбец с направлением
				getWnd('swEvnDirectionEditWindow').show({
					Person_id: rec.get('Person_id'),
					EvnDirection_id: rec.get('EvnDirection_id'),
					action: 'view',
					formParams: new Object()
				});
			}
		});
		
		
		// Добавляем события на keydown
		this.ScheduleGrid.on('keydown', function(e)
		{
			var win = Ext.getCmp('swWorkPlacePhysWindow');
			var grid = win.getGrid();
			if (e.getKey().inlist([e.INSERT, e.F4, e.F5, e.ENTER, e.DELETE, e.END, e.HOME, e.PAGE_DOWN, e.PAGE_UP, e.TAB])
				|| (grid.hasPersonData() && e.getKey().inlist([e.F6, e.F10, e.F11, e.F12]))
				|| (e.getKey().inlist([e.C, e.V]) && (e.ctrlKey)))
			{
				e.stopEvent();
				if ( e.browserEvent.stopPropagation )
					e.browserEvent.stopPropagation();
				else
					e.browserEvent.cancelBubble = true;

				if ( e.browserEvent.preventDefault )
					e.browserEvent.preventDefault();
				else
					e.browserEvent.returnValue = false;

				e.returnValue = false;

				if (Ext.isIE)
				{
					e.browserEvent.keyCode = 0;
					e.browserEvent.which = 0;
				}
			}
			var countRecords = this.getStore().getCount();

			// Собираем информацию о человеке в случае, если в гриде есть поля по человеку
			var isPerson = false;
			if (grid.hasPersonData())
			{
				var selected_record = grid.getSelectionModel().getSelected();
				var params = new Object();
				params.Person_id = selected_record.get('Person_id');
				params.Server_id = selected_record.get('Server_id');
				isPerson = (params.Person_id>0);
				if ( selected_record.get('Person_BirthDay') )
					params.Person_BirthDay = selected_record.get('Person_BirthDay');
				else
					params.Person_BirthDay = selected_record.get('Person_Birthday');
				if ( selected_record.get('Person_Surname') )
					params.Person_Surname = selected_record.get('Person_Surname');
				else
					params.Person_Surname = selected_record.get('Person_SurName');
				if ( selected_record.get('Person_Firname') )
					params.Person_Firname = selected_record.get('Person_Firname');
				else
					params.Person_Firname = selected_record.get('Person_FirName');
				if ( selected_record.get('Person_Secname') )
					params.Person_Secname = selected_record.get('Person_Secname');
				else
					params.Person_Secname = selected_record.get('Person_SecName');
				params.onHide = function()
				{
					var index = grid.getStore().findBy(function(rec) { return rec.get('TimetableGraf_id') == selected_record.data['TimetableGraf_id']; });
					grid.focus();
					grid.getView().focusRow(index);
					grid.getSelectionModel().selectRow(index);
				}
			}

			switch (e.getKey())
			{
				case e.ENTER:
					if (isPerson)
					{
						if (!win.gridActions.open.isDisabled())
						{
							win.gridActions.open.execute();
						}
					}
					else 
					{
						if (!win.gridActions.add.isDisabled())
						{
							win.gridActions.add.execute();
						}
					}
					break;
				case e.F4:
					if (!win.gridActions.edit.isDisabled())
					{
						win.gridActions.edit.execute();
					}
					break;
				case e.F5:
					if (!win.gridActions.refresh.isDisabled())
					{
						win.gridActions.refresh.execute();
					}
					break;
				case e.INSERT:
					if (e.ctrlKey)
					{
						if (!win.gridActions.create.isDisabled())
						{
							win.gridActions.create.execute();
						}
					}
					else 
					{
						if (!win.gridActions.add.isDisabled())
						{
							win.gridActions.add.execute();
						}
					}
					break;
				case e.C:
					if (e.ctrlKey)
					{
						/*
						if (!win.gridActions.copy.isDisabled())
						{
							win.gridActions.copy.execute(); // просто бред ...
						}
						*/
					}
					break;
				case e.V:
					if (e.ctrlKey)
					{
						/*
						if (!win.gridActions.paste.isDisabled())
						{
							win.gridActions.paste.execute();
						}
						*/
					}
					break;

				case e.END:
					GridEnd(this);
					break;
				case e.HOME:
					GridHome(this);
					break;
				case e.PAGE_DOWN:
					GridPageDown(this);
					break;
					case e.PAGE_UP:
					GridPageUp(this);
					break;

				case e.TAB:
					if (e.shiftKey)
					{
						var o = win.findById('mpwpBtnClear');
					}
					else
					{
						//var o = win.findById('mpwpSearch_FIO');
						var o = win.findById('mpwpSearch_SurName');
					}
					if (o)
					{
						o.focus(true, 100);
					}
					break;
				case e.F6: // Прикрепление и объединение
					if (grid.hasPersonData() && isPerson) 
					{
						if (!e.altKey && !e.ctrlKey && !e.shiftKey) 
						{ // прикрепление
							ShowWindow('swPersonCardHistoryWindow', params);
						}
						else if (e.altKey)
						{ 
							// TO-DO: сделать процедуру объединения человека, когда в этом возникнет нужда
						}
						return false;
					}
					break;
				case e.F10: // Редактирование
					if (grid.hasPersonData() && !e.altKey && !e.ctrlKey && !e.shiftKey && isPerson)
					{
						ShowWindow('swPersonEditWindow', params);
						return false;
					}
					break;
				case e.F11: // История лечения
					if (grid.hasPersonData() && !e.altKey && !e.ctrlKey && !e.shiftKey && isPerson)
					{
						ShowWindow('swPersonCureHistoryWindow', params);
						return false;
					}
					/*if (grid.hasPersonData() && !e.altKey && e.ctrlKey && !e.shiftKey && isPerson)
					{
						ShowWindow('swPersonEmkWindow', params);
						return false;
					}*/
				break;
				case e.F12: // Льготы и диспансеризация
					if (grid.hasPersonData() && isPerson)
					{
						if (e.ctrlKey)
						{ // Диспансеризация
							ShowWindow('swPersonDispHistoryWindow', params);
						}
						else if (!e.altKey && !e.shiftKey && isPerson)
						{ // Льготы
							ShowWindow('swPersonPrivilegeViewWindow', params);
						}
						return false;
					}
				break;
			}
			return false;
		});

        var form = this;

		// метод получения данных: "ЛПУ прикрепления", "Тип прикрепления:", "Тип участка:", "Участок:", на котором врач АРМа является врачом на участке
		this.getAttachDataShowWindow = function(wnd) {
			var global_options = getGlobalOptions();
			Ext.Ajax.request(
			{
				url: '/?c=LpuRegion&m=getAttachData',
				callback: function(options, success, response) 
				{
					if (success)
					{
						var response_obj = Ext.util.JSON.decode(response.responseText);
						if (response_obj[0] && response_obj[0].LpuRegion_id)
						{
							getWnd(wnd).show({
								LpuAttachType_id: response_obj[0].LpuAttachType_id, 
								LpuRegionType_id: response_obj[0].LpuRegionType_id, 
								LpuRegion_id: response_obj[0].LpuRegion_id
							});
						}
						else
						{
							getWnd(wnd).show();
						}
					}
				},
				params: {MedPersonal_id: global_options.medpersonal_id, Lpu_id: global_options.lpu_id}
			});
		};
		var form = this;
		
		var swPromedActions = {

				PersonDispOrpSearch: {
					tooltip: langs('Регистр детей-сирот'),
					text: langs('Регистр детей-сирот'),
					iconCls : 'doc-reg16',
					handler: function() 
					{
						getWnd('swPersonDispOrpSearchWindow').show();
					}
				},
				PersonPrivilegeWOWSearch: {
					tooltip: langs('Регистр ВОВ'),
					text: langs('Регистр ВОВ'),
					iconCls : 'doc-reg16',
					handler: function() 
					{
						getWnd('swPersonPrivilegeWOWSearchWindow').show();
					}
				},
				PersonDopDispSearch: {
					tooltip: langs('Регистр ДД'),
					text: langs('Регистр ДД'),
					iconCls : 'doc-reg16',
					handler: function() 
					{
						getWnd('swPersonDopDispSearchWindow').show();
					}
				},
				EvnPLDispTeen14Search: {
					tooltip: langs('Регистр декретированных возрастов'),
					text: langs('Регистр декретированных возрастов'),
					iconCls : 'doc-reg16',
					handler: function() 
					{
						getWnd('swEvnPLDispTeen14SearchWindow').show();
					}
				},
				mainForm: {
					text: langs('Журнал вакцинации'),
					tooltip: langs('Просмотр журналов вакцинации'),
					iconCls : 'pol-immuno16',
					handler: function()
					{
						getWnd('amm_mainForm').show();
					}
				},
				PresenceVacForm: {
					text: langs('Национальный календарь прививок'),
					tooltip: langs('Национальный календарь прививок'),
					iconCls : 'pol-immuno16',
					handler: function()
					{
                        getWnd('amm_SprNacCalForm').show();
					}
				},
				SprVaccineForm: {
					text: langs('Справочник вакцин'),
					tooltip: langs('Справочник вакцин'),
					iconCls : 'pol-immuno16',
					handler: function()
					{
						getWnd('amm_SprVaccineForm').show();
					}
				},
				OrgFarmacyByLpuView: {
					tooltip: langs('Прикрепление аптек к МО'),
					text: langs('Прикрепление аптек к МО'),
					iconCls : 'therapy-plan32',
					disabled: false,
					handler: function() {
						if (getRegionNick().inlist(['perm', 'ufa'])) {
							getWnd('swOrgFarmacyByLpuViewWindow').show();
						} else {
							getWnd('swOrgFarmacyLinkedByLpuViewWindow').show({ARMType: form.ARMType});
						}
					}
           		},
				EvnReceptInCorrectFind: {
					text: langs('Журнал отсрочки'),
					tooltip: langs('Журнал отсрочки'),
					iconCls : 'receipt-incorrect16',
					handler: function()
					{
						getWnd('swReceptInCorrectSearchWindow').show();
					}
				},
				OstAptekaViewAction: {
					text: MM_DLO_MEDAPT,
					tooltip: langs('Работа с остатками медикаментов по аптекам'),
					iconCls : 'drug-farm16',
					handler: function()
					{
						getWnd('swDrugOstatByFarmacyViewWindow').show();
					},
					hidden: !(getRegionNick() == 'perm')
				},
				OstDrugViewAction: {
					text: MM_DLO_MEDNAME,
					tooltip: langs('Работа с остатками медикаментов по наименованию'),
					iconCls : 'drug-name16',
					handler: function()
					{
						getWnd('swDrugOstatViewWindow').show();
					},
					hidden: !(getRegionNick() == 'perm')
				},
				DrugOstat:
				{
					text: langs('Просмотр остатков'),
					tooltip: langs('Просмотр остатков'),
					iconCls: 'pill16',
					handler: function() {
						getWnd('swDrugOstatRegistryListWindow').show({
                            mode: 'suppliers',
                            userMedStaffFact: this.userMedStaffFact
                        });
					}.createDelegate(this),
					hidden: getRegionNick().inlist(['perm','ufa'])
				},
				PregnancyRegistry: 
				{
					tooltip: langs('Регистр беременных'),
					text: langs('Регистр беременных'),
					iconCls : 'doc-reg16',
					disabled: !isPregnancyRegisterAccess(),
					hidden: false,
					handler: function()
					{
						getWnd('swPersonPregnancyWindow').show();
					}
				},
				NolosRegistry: sw.Promed.personRegister.getVznBtnConfig(form.id, form),
				EvnNotifyNolos: sw.Promed.personRegister.getEvnNotifyVznBtnConfig(form.id, form),
				OrphanRegistry: sw.Promed.personRegister.getOrphanBtnConfig(form.id, form),
				EvnNotifyOrphan: sw.Promed.personRegister.getEvnNotifyOrphanBtnConfig(form.id, form),
				CrazyRegistry:
				{
					tooltip: langs('Регистр по Психиатрии'),
					text: langs('Регистр по Психиатрии'),
					iconCls : 'doc-reg16',
					disabled: (String(getGlobalOptions().groups).indexOf('Crazy', 0) < 0),
					handler: function()
					{
						getWnd('swCrazyRegistryWindow').show({userMedStaffFact: this.userMedStaffFact});
					}.createDelegate(this)
				},
				EvnNotifyCrazy:
				{
					tooltip: langs('Журнал Извещений по Психиатрии'),
					text: langs('Журнал Извещений по Психиатрии'),
					iconCls : 'journal16',
					disabled: (String(getGlobalOptions().groups).indexOf('Crazy', 0) < 0),
					handler: function()
					{
						getWnd('swEvnNotifyCrazyListWindow').show({userMedStaffFact: this.userMedStaffFact});
					}.createDelegate(this)
				},
				NarkoRegistry:
				{
					tooltip: langs('Регистр по Наркологии'),
					text: langs('Регистр по Наркологии'),
					iconCls : 'doc-reg16',
					disabled: (String(getGlobalOptions().groups).indexOf('Narko', 0) < 0),
					handler: function()
					{
						getWnd('swNarkoRegistryWindow').show({userMedStaffFact: this.userMedStaffFact});
					}.createDelegate(this)
				},
				DiabetesRegistry:{
					tooltip: langs('Регистр по сахарному диабету'),
					text: langs('Регистр по сахарному диабету'),
					iconCls : 'doc-reg16',
					hidden: !getRegionNick().inlist([ 'pskov','khak','saratov','buryatiya' ]),
					handler: function()
					{
						if ( getWnd('swDiabetesRegistryWindow').isVisible() ) {
							sw.swMsg.show({
								buttons: Ext.Msg.OK,
								fn: Ext.emptyFn,
								icon: Ext.Msg.WARNING,
								msg: langs('Окно уже открыто'),
								title: ERR_WND_TIT
							});
							return false;
						}
						getWnd('swDiabetesRegistryWindow').show({userMedStaffFact: this.userMedStaffFact});
					}.createDelegate(this)
				},
				EvnNotifyNarko:
				{
					tooltip: langs('Журнал Извещений по Наркологии'),
					text: langs('Журнал Извещений по Наркологии'),
					iconCls : 'journal16',
					disabled: (String(getGlobalOptions().groups).indexOf('Narko', 0) < 0),
					handler: function()
					{
						getWnd('swEvnNotifyNarkoListWindow').show({userMedStaffFact: this.userMedStaffFact});
					}.createDelegate(this)
				},
				TubRegistry:
				{
					tooltip: langs('Регистр больных туберкулезом'),
					text: langs('Регистр больных туберкулезом'),
					iconCls : 'doc-reg16',
					menu: [{
						tooltip: 'Регистр по туберкулёзным заболеваниям',
						text: 'Регистр по туберкулёзным заболеваниям',
						iconCls : 'doc-reg16',
						disabled: (String(getGlobalOptions().groups).indexOf('Tub', 0) < 0),
						handler: function() {
							getWnd('swTubRegistryWindow').show({userMedStaffFact: this.userMedStaffFact});
						}.createDelegate(this)
					}, {
						tooltip: 'Экспорт сведений в ФРБТ',
						text: 'Экспорт сведений в ФРБТ',
						iconCls : 'doc-reg16',
						disabled: (String(getGlobalOptions().groups).indexOf('Tub', 0) < 0),
						handler: function() {
							getWnd('swExportTubToXLSWindow').show({userMedStaffFact: this.userMedStaffFact});
						}.createDelegate(this)
					}]
				},
				EvnNotifyTub:
				{
					tooltip: langs('Журнал Извещений о больных туберкулезом'),
					text: langs('Журнал Извещений по туберкулезным заболеваниям'),
					iconCls : 'journal16',
					disabled: (String(getGlobalOptions().groups).indexOf('Tub', 0) < 0),
					handler: function()
					{
						getWnd('swEvnNotifyTubListWindow').show({userMedStaffFact: this.userMedStaffFact});
					}.createDelegate(this)
				},
				VenerRegistry:
				{
					tooltip: langs('Регистр больных венерическим заболеванием'),
					text: langs('Регистр больных венерическими заболеваниями'),
					iconCls : 'doc-reg16',
					disabled: (String(getGlobalOptions().groups).indexOf('Vener', 0) < 0),
					handler: function()
					{
						getWnd('swVenerRegistryWindow').show({userMedStaffFact: this.userMedStaffFact});
					}.createDelegate(this)
				},
				EvnNotifyVener:
				{
					tooltip: langs('Журнал Извещений о больных венерическим заболеванием'),
					text: langs('Журнал Извещений о больных венерическим заболеванием'),
					iconCls : 'journal16',
					disabled: (String(getGlobalOptions().groups).indexOf('Vener', 0) < 0),
					handler: function()
					{
						getWnd('swEvnNotifyVenerListWindow').show({userMedStaffFact: this.userMedStaffFact});
					}.createDelegate(this)
				},
				action_MonitorBirthSpec: { 
					disabled: !(isUserGroup('OperBirth')||isUserGroup('OperRegBirth')),
					text: getRegionNick().inlist(['ufa']) ? 'Мониторинг детей первого года жизни' : 'Мониторинг новорожденных',
					tooltip: 'Мониторинг новорожденных',
					iconCls : 'doc-reg16', 
					handler: function() {
						getWnd('swMonitorBirthSpecWindow').show({userMedStaffFact: this.userMedStaffFact});
					}.createDelegate(this)
				},
				// Экспорт для Казахстана не нужен
				// @task https://redmine.swan.perm.ru/issues/110217
				HIVRegistry: (getRegionNick() == 'kz' ? {
					tooltip: 'Регистр ВИЧ',
					text: 'Регистр ВИЧ',
					iconCls : 'doc-reg16',
					disabled: !allowHIVRegistry(),
					handler: function() {
						getWnd('swHIVRegistryWindow').show({userMedStaffFact: this.userMedStaffFact});
					}.createDelegate(this)
				} : {
					tooltip: langs('Регистр ВИЧ-инфицированных'),
					text: langs('Регистр ВИЧ-инфицированных'),
					iconCls : 'doc-reg16',
					menu: [{
						tooltip: 'Регистр ВИЧ',
						text: 'Регистр ВИЧ',
						iconCls : 'doc-reg16',
						disabled: !allowHIVRegistry(),
						handler: function() {
							getWnd('swHIVRegistryWindow').show({userMedStaffFact: this.userMedStaffFact});
						}.createDelegate(this)
					}, {
						tooltip: 'Экспорт регистра ВИЧ в ФРВИЧ',
						text: 'Экспорт регистра ВИЧ в ФРВИЧ',
						iconCls : 'doc-reg16',
						disabled: !allowHIVRegistry(),
						handler: function() {
							getWnd('swExportHivToXLSWindow').show({userMedStaffFact: this.userMedStaffFact});
						}.createDelegate(this)
					}]
				}),
				EvnNotifyHIV:
				{
					tooltip: langs('Журнал Извещений о ВИЧ-инфицированных'),
					text: langs('Журнал Извещений о ВИЧ-инфицированных'),
					iconCls : 'journal16',
					disabled: (String(getGlobalOptions().groups).indexOf('HIV', 0) < 0),
					handler: function()
					{
						getWnd('swEvnNotifyHIVListWindow').show({userMedStaffFact: this.userMedStaffFact});
					}.createDelegate(this)
				},
				HepatitisRegistry: 
				{
					tooltip: langs('Регистр по Вирусному гепатиту'),
					text: langs('Регистр по Вирусному гепатиту'),
					iconCls : 'doc-reg16',
					handler: function()
					{
						if ( getWnd('swHepatitisRegistryWindow').isVisible() ) {
							sw.swMsg.show({
								buttons: Ext.Msg.OK,
								fn: Ext.emptyFn,
								icon: Ext.Msg.WARNING,
								msg: langs('Окно уже открыто'),
								title: ERR_WND_TIT
							});
							return false;
						}
						getWnd('swHepatitisRegistryWindow').show({userMedStaffFact: this.userMedStaffFact});
					}.createDelegate(this)
				},
				EvnNotifyHepatitis: 
				{
					tooltip: langs('Журнал Извещений по Вирусному гепатиту'),
					text: langs('Журнал Извещений по Вирусному гепатиту'),
					iconCls : 'journal16',
					handler: function()
					{
						if ( getWnd('swEvnNotifyHepatitisListWindow').isVisible() ) {
							sw.swMsg.show({
								buttons: Ext.Msg.OK,
								fn: Ext.emptyFn,
								icon: Ext.Msg.WARNING,
								msg: langs('Окно уже открыто'),
								title: ERR_WND_TIT
							});
							return false;
						}
						getWnd('swEvnNotifyHepatitisListWindow').show({userMedStaffFact: this.userMedStaffFact});
					}.createDelegate(this)
				},
				EvnInfectNotify: 
				{
					tooltip: langs('Журнал Извещений форма №058/У'),
					text: langs('Журнал Извещений форма №058/У'),
					iconCls : 'journal16',
					disabled: false, 
					handler: function()
					{
						if ( getWnd('swEvnInfectNotifyListWindow').isVisible() ) {
							sw.swMsg.show({
								buttons: Ext.Msg.OK,
								fn: Ext.emptyFn,
								icon: Ext.Msg.WARNING,
								msg: langs('Окно уже открыто'),
								title: ERR_WND_TIT
							});
							return false;
						}
						getWnd('swEvnInfectNotifyListWindow').show({userMedStaffFact: this.userMedStaffFact});
					}.createDelegate(this)
				},
				OnkoRegistry: 
				{
					tooltip: langs('Регистр по онкологии'),
					text: langs('Регистр по онкологии'),
					iconCls : 'doc-reg16',
				disabled: (String(getGlobalOptions().groups).indexOf('OnkoRegistry', 0) < 0), 
					handler: function()
					{
						if ( getWnd('swOnkoRegistryWindow').isVisible() ) {
							sw.swMsg.show({
								buttons: Ext.Msg.OK,
								fn: Ext.emptyFn,
								icon: Ext.Msg.WARNING,
								msg: langs('Окно уже открыто'),
								title: ERR_WND_TIT
							});
							return false;
						}
						getWnd('swOnkoRegistryWindow').show({userMedStaffFact: this.userMedStaffFact, ARMType:this.ARMType});
					}.createDelegate(this)
				},
				EvnOnkoNotify: 
				{
					tooltip: langs('Журнал Извещений об онкобольных'),
					text: langs('Журнал Извещений об онкобольных'),
					iconCls : 'journal16',
					handler: function()
					{
						if ( getWnd('swEvnOnkoNotifyListWindow').isVisible() ) {
							sw.swMsg.show({
								buttons: Ext.Msg.OK,
								fn: Ext.emptyFn,
								icon: Ext.Msg.WARNING,
								msg: langs('Окно уже открыто'),
								title: ERR_WND_TIT
							});
							return false;
						}
						getWnd('swEvnOnkoNotifyListWindow').show({userMedStaffFact: this.userMedStaffFact});
					}.createDelegate(this)
				},
                NephroRegistry:
                {
                    tooltip: langs('Регистр по нефрологии'),
                    text: langs('Регистр по нефрологии'),
                    iconCls : 'doc-reg16',
                    hidden: !getRegionNick().inlist([ 'perm', 'ufa','buryatiya']),
                    disabled: (String(getGlobalOptions().groups).indexOf('NephroRegistry', 0) < 0),
                    handler: function()
                    {
                        if ( getWnd('swNephroRegistryWindow').isVisible() ) {
                            sw.swMsg.show({
                                buttons: Ext.Msg.OK,
                                fn: Ext.emptyFn,
                                icon: Ext.Msg.WARNING,
                                msg: langs('Окно уже открыто'),
                                title: ERR_WND_TIT
                            });
                            return false;
                        }
                        getWnd('swNephroRegistryWindow').show({userMedStaffFact: this.userMedStaffFact});
                    }.createDelegate(this)
                },
                IBSRegistry:
                {
                    tooltip: langs('Регистр ИБС'),
                    text: langs('Регистр ИБС'),
                    iconCls : 'doc-reg16',
                    hidden: ('perm' != getRegionNick()),
                    disabled: (String(getGlobalOptions().groups).indexOf('IBSRegistry', 0) < 0),
                    handler: function()
                    {
                        if ( getWnd('swIBSRegistryWindow').isVisible() ) {
                            sw.swMsg.show({
                                buttons: Ext.Msg.OK,
                                fn: Ext.emptyFn,
                                icon: Ext.Msg.WARNING,
                                msg: langs('Окно уже открыто'),
                                title: ERR_WND_TIT
                            });
                            return false;
                        }
                        getWnd('swIBSRegistryWindow').show({userMedStaffFact: this.userMedStaffFact});
                    }.createDelegate(this)
                },
                EvnNotifyIBS:
                {
                    tooltip: langs('Журнал Извещений по ИБС'),
                    text: langs('Журнал Извещений по ИБС'),
                    iconCls : 'journal16',
                    hidden: true,//('perm' != getRegionNick()),
                    handler: function()
                    {
                        if ( getWnd('swEvnNotifyIBSListWindow').isVisible() ) {
                            sw.swMsg.show({
                                buttons: Ext.Msg.OK,
                                fn: Ext.emptyFn,
                                icon: Ext.Msg.WARNING,
                                msg: langs('Окно уже открыто'),
                                title: ERR_WND_TIT
                            });
                            return false;
                        }
                        getWnd('swEvnNotifyIBSListWindow').show({userMedStaffFact: this.userMedStaffFact});
                    }.createDelegate(this)
                },
				ProfRegistry:
                {
                    tooltip: langs('Регистр по профзаболеваниям'),
                    text: langs('Регистр по профзаболеваниям'),
                    iconCls : 'doc-reg16',
                    hidden: ('perm' != getRegionNick()),
                    disabled: (String(getGlobalOptions().groups).indexOf('ProfRegistry', 0) < 0),
                    handler: function()
                    {
                        if ( getWnd('swProfRegistryWindow').isVisible() ) {
                            sw.swMsg.show({
                                buttons: Ext.Msg.OK,
                                fn: Ext.emptyFn,
                                icon: Ext.Msg.WARNING,
                                msg: langs('Окно уже открыто'),
                                title: ERR_WND_TIT
                            });
                            return false;
                        }
                        getWnd('swProfRegistryWindow').show({userMedStaffFact: this.userMedStaffFact});
                    }.createDelegate(this)
                },
                EvnNotifyNephro:
                {
                    tooltip: langs('Журнал Извещений по нефрологии'),
                    text: langs('Журнал Извещений по нефрологии'),
                    iconCls : 'journal16',
                    hidden: !getRegionNick().inlist([ 'perm', 'ufa','buryatiya']),
                    handler: function()
                    {
                        if ( getWnd('swEvnNotifyNephroListWindow').isVisible() ) {
                            sw.swMsg.show({
                                buttons: Ext.Msg.OK,
                                fn: Ext.emptyFn,
                                icon: Ext.Msg.WARNING,
                                msg: langs('Окно уже открыто'),
                                title: ERR_WND_TIT
                            });
                            return false;
                        }
                        getWnd('swEvnNotifyNephroListWindow').show({userMedStaffFact: this.userMedStaffFact});
                    }.createDelegate(this)
                },
				EvnNotifyProf:
                {
                    tooltip: langs('Журнал Извещений по профзаболеваниям'),
                    text: langs('Журнал Извещений по профзаболеваниям'),
                    iconCls : 'journal16',
                    hidden: ('perm' != getRegionNick()),
                    handler: function()
                    {
                        if ( getWnd('swEvnNotifyProfListWindow').isVisible() ) {
                            sw.swMsg.show({
                                buttons: Ext.Msg.OK,
                                fn: Ext.emptyFn,
                                icon: Ext.Msg.WARNING,
                                msg: langs('Окно уже открыто'),
                                title: ERR_WND_TIT
                            });
                            return false;
                        }
                        getWnd('swEvnNotifyProfListWindow').show({userMedStaffFact: this.userMedStaffFact});
                    }.createDelegate(this)
                },
				BskRegistry: {
					tooltip: langs('Регистр по БСК'),
					text: langs('Регистр по БСК'),
					iconCls : 'doc-reg16',
					/*hidden: (!getRegionNick().inlist([ 'ufa' ])),*/
					disabled: (!isUserGroup("BSKRegistry")),
					handler: function()
					{   
					   
						if ( getWnd('swBskRegistryWindow').isVisible() ) {
							sw.swMsg.show({
								buttons: Ext.Msg.OK,
								fn: Ext.emptyFn,
								icon: Ext.Msg.WARNING,
								msg: langs('Окно уже открыто'),
								title: ERR_WND_TIT
							});
							return false;
						}
						getWnd('swBskRegistryWindow').show({userMedStaffFact: this.userMedStaffFact});
					}.createDelegate(this)
				},
                ReabRegistry: {
					tooltip: langs('Регистр Реабилитации'),
					text: langs('Регистр Реабилитации'),
					iconCls : 'doc-reg16',
					hidden: (getGlobalOptions().region.nick != 'ufa'),
					handler: function()
					{ 
						if ( getWnd('swReabRegistryWindow').isVisible() ) {
							sw.swMsg.show({
								buttons: Ext.Msg.OK,
								fn: Ext.emptyFn,
								icon: Ext.Msg.WARNING,
								msg: langs('Окно уже открыто'),
								title: ERR_WND_TIT
							});
							return false;
						}
						getWnd('swReabRegistryWindow').show({userMedStaffFact: this.userMedStaffFact});
					}.createDelegate(this)
				},
				 ZNOSuspectRegistry: {
					tooltip: langs('Регистр пациентов с подозрением на ЗНО'),
					text: langs('Регистр пациентов с подозрением на ЗНО'),
					iconCls : 'doc-reg16',
					hidden: (getGlobalOptions().region.nick != 'ufa'),
					//disabled: (!isSuperAdmin()),
					handler: function()
					{ 
						if ( getWnd('swZNOSuspectRegistryWindow').isVisible() ) {
							sw.swMsg.show({
								buttons: Ext.Msg.OK,
								fn: Ext.emptyFn,
								icon: Ext.Msg.WARNING,
								msg: langs('Окно уже открыто'),
								title: ERR_WND_TIT
							});
							return false;
						}
						//getWnd('swZNOSuspectRegistryWindow').show({userMedStaffFact: this.userMedStaffFact});
						//alert("dfdfdfd");
					}.createDelegate(this)
				},
				OKSRegistry: {
					tooltip: langs('Регистр по ОКС'),
					text: langs('Регистр по ОКС'),
					iconCls : 'doc-reg16',
					hidden:!getRegionNick().inlist(['astra', 'buryatiya']),
					disabled: (!isUserGroup("OKSRegistry")),
					handler: function()
					{
						if ( getWnd('swOKSRegistryWindow').isVisible() ) {
							sw.swMsg.show({
								buttons: Ext.Msg.OK,
								fn: Ext.emptyFn,
								icon: Ext.Msg.WARNING,
								msg: langs('Окно уже открыто'),
								title: ERR_WND_TIT
							});
							return false;
						}
						getWnd('swACSRegistryWindow').show({userMedStaffFact: this.userMedStaffFact});
					}.createDelegate(this)
				},
				IPRARegistry: {
					tooltip: 'Регистр ИПРА',
					text: 'Регистр ИПРА',
					iconCls : 'doc-reg16',
					hidden: !(isUserGroup('IPRARegistry') || isUserGroup('IPRARegistryEdit')),
					handler: function()
					{   
					   
						if ( getWnd('swIPRARegistryViewWindow').isVisible() ) {
							sw.swMsg.show({
								buttons: Ext.Msg.OK,
								fn: Ext.emptyFn,
								icon: Ext.Msg.WARNING,
								msg: 'Окно уже открыто',
								title: ERR_WND_TIT
							});
							return false;
						}
						getWnd('swIPRARegistryViewWindow').show({userMedStaffFact: this.userMedStaffFact});
					}.createDelegate(this)
				},  
				ECORegistry: {
					tooltip: 'Регистр ЭКО',
					text: 'Регистр ЭКО',
					iconCls : 'doc-reg16',
					disabled: !((String(getGlobalOptions().groups).indexOf('EcoRegistry', 0) > 0)||(String(getGlobalOptions().groups).indexOf('EcoRegistryRegion', 0) > 0)),
					handler: function()
					{   

							if ( getWnd('swECORegistryViewWindow').isVisible() ) {
									sw.swMsg.show({
											buttons: Ext.Msg.OK,
											fn: Ext.emptyFn,
											icon: Ext.Msg.WARNING,
											msg: 'Окно уже открыто',
											title: ERR_WND_TIT
									});
									return false;
							}
							getWnd('swECORegistryViewWindow').show({userMedStaffFact: this.userMedStaffFact});
					}.createDelegate(this)
				},			
				EndoRegistry: {
					tooltip: langs('Регистр по эндопротезированию'),
					text: langs('Регистр по эндопротезированию'),
					iconCls : 'doc-reg16',
					disabled: !isUserGroup('EndoRegistry'),
					handler: function()
					{

						if ( getWnd('swEndoRegistryWindow').isVisible() ) {
							sw.swMsg.show({
								buttons: Ext.Msg.OK,
								fn: Ext.emptyFn,
								icon: Ext.Msg.WARNING,
								msg: langs('Окно уже открыто'),
								title: ERR_WND_TIT
							});
							return false;
						}
						getWnd('swEndoRegistryWindow').show({userMedStaffFact: this.userMedStaffFact});
					}.createDelegate(this)
				},  
				SuicideRegistry: {
					tooltip: 'Регистр по суицидам',
					text: 'Регистр по суицидам',
					iconCls : 'doc-reg16',
					disabled: !isUserGroup('SuicideRegistry'),
					handler: function()
					{

						if ( getWnd('swPersonRegisterSuicideListWindow').isVisible() ) {
							sw.swMsg.show({
								buttons: Ext.Msg.OK,
								fn: Ext.emptyFn,
								icon: Ext.Msg.WARNING,
								msg: langs('Окно уже открыто'),
								title: ERR_WND_TIT
							});
							return false;
						}
						getWnd('swPersonRegisterSuicideListWindow').show({userMedStaffFact: this.userMedStaffFact});
					}.createDelegate(this)
				},
				PalliatRegistry: {
					tooltip: 'Регистр по паллиативной помощи',
					text: 'Регистр по паллиативной помощи',
					iconCls : 'doc-reg16',
					disabled: !(isUserGroup('RegistryPalliatCare')||isUserGroup('RegistryPalliatCareAll')),
					hidden: (getRegionNick() == 'kz'),
					handler: function()
					{
						if ( getWnd('swPersonRegisterPalliatListWindow').isVisible() ) {
							sw.swMsg.show({
								buttons: Ext.Msg.OK,
								fn: Ext.emptyFn,
								icon: Ext.Msg.WARNING,
								msg: langs('Окно уже открыто'),
								title: ERR_WND_TIT
							});
							return false;
						}
						getWnd('swPersonRegisterPalliatListWindow').show({userMedStaffFact: this.userMedStaffFact});
					}.createDelegate(this)
				}
		}

		// Формирование списка всех акшенов 
		var configActions = 
		{
			action_Turn:
			{
				nn: 'action_Turn',
				iconCls : 'mp-queue32',
				text:  WND_DIRECTION_JOURNAL,
				tooltip:  WND_DIRECTION_JOURNAL,
				handler: function() {
					getWnd('swMPQueueWindow').show({
						useCase: 'open_from_polka',
						LpuSectionProfile_id: form.userMedStaffFact.LpuSectionProfile_id,
						Lpu_id: form.userMedStaffFact.Lpu_id,
						callback: function() {
							form.scheduleRefresh();
						},
						userMedStaffFact: form.userMedStaffFact
					});
				}
			},
			action_Timetable:
			{
				nn: 'action_Timetable',
				tooltip: langs('Работа с расписанием'),
				text: langs('Рaсписание'),
				iconCls : 'mp-timetable32',
				hidden: !IS_DEBUG,
				handler: function()
				{
					getWnd('swTTGScheduleEditWindow').show();
				}
			},
			action_TimetableProcCab: {
				nn: 'action_TimetableProcCab',
				tooltip: langs('Работа с расписанием процедурного кабинета'),
				text: langs('Работа с расписанием процедурного кабинета'),
				iconCls : 'mp-timetable32',
				handler: function()
				{
					getWnd('swTTMSScheduleEditWindow').show({
						MedService_id: getGlobalOptions().CurMedService_id,
						MedService_Name:getGlobalOptions().CurMedService_Name
					});
				}.createDelegate(this)
			},
			action_TimetableGraf: {
				nn: 'action_TimetableGraf',
				tooltip: langs('Работа процедурного кабинета'),
				text: langs('Работа процедурного кабинета'),
				iconCls : 'mp-timetable32',
				handler: function()
				{
					getWnd('swProcCabinetWindow').show({
						userMedStaffFact: form.userMedStaffFact
					});
				}.createDelegate(this)
			},
			action_doneProceduresReports: //http://redmine.swan.perm.ru/issues/18509
			{
				nn: 'action_doneProceduresReports',
				tooltip: langs('Журнал проведенных процедур'),
				text: langs('Журнал проведенных процедур'),
				iconCls: 'report32',
				handler: function() {
					getWnd('swJournalDoneProceduresWindow').show({
						userMedStaffFact: form.userMedStaffFact
					});
				}.createDelegate(this)
			},
			action_reports: //http://redmine.swan.perm.ru/issues/18509
			{
				nn: 'action_Report',
				tooltip: langs('Просмотр отчетов'),
				text: langs('Просмотр отчетов'),
				iconCls: 'report32',
				handler: function() {
					if (sw.codeInfo.loadEngineReports)
					{
						getWnd('swReportEndUserWindow').show();
					}
					else
					{
						getWnd('reports').load(
							{
								callback: function(success)
								{
									sw.codeInfo.loadEngineReports = success;
									// здесь можно проверять только успешную загрузку
									getWnd('swReportEndUserWindow').show();
								}
							});
					}
				}
			},
			action_PersDispSearchView:
			{
				hidden: (getRegionNick() == 'kz'),
				nn: 'action_PersDispSearchView',
				tooltip: langs('Диспансерное наблюдение'),
				text: WND_POL_PERSDISPSEARCHVIEW,
				iconCls : 'mp-disp32',
				handler: function()
				{
					var grid = Ext.getCmp('swWorkPlacePhysWindow').getGrid(),
						selected_record = grid.getSelectionModel().getSelected(),
						Person_id = (selected_record && selected_record.get('Person_id')) || null,
						MedPersonal_id = this.userMedStaffFact.MedPersonal_id;
					getWnd('swPersonDispViewWindow').show({
						mode: 'view', Person_id: Person_id, MedPersonal_id: MedPersonal_id, view_one_doctor: true, ARMType: "common"
					});
				}.createDelegate(this)
			},
			action_PersCardSearch:
			{
				nn: 'action_PersCardSearch',
				tooltip: WND_POL_PERSCARDSEARCH,
				text: WND_POL_PERSCARDSEARCH,
				iconCls : 'mp-region32',
				handler: function()
				{
					form.getAttachDataShowWindow('swPersonCardSearchWindow');
				}
			},
			action_JournalHospit:
			{
				nn: 'action_JournalHospit',
				tooltip: langs('Открыть журнал госпитализаций'),
				text: langs('Журнал госпитализаций'),
				iconCls : 'mp-hospital-list32',
				handler: function()
				{
					if ( getWnd('swJournalHospitWindow').isVisible() ) {
						sw.swMsg.show({
							buttons: Ext.Msg.OK,
							fn: Ext.emptyFn,
							icon: Ext.Msg.WARNING,
							msg: langs('Окно уже открыто'),
							title: ERR_WND_TIT
						});
						return false;
					}
					getWnd('swJournalHospitWindow').show({userMedStaffFact: form.userMedStaffFact});
				}
			},
			action_References: {
				nn: 'action_References',
				tooltip: langs('Справочники'),
				text: langs('Справочники'),
				iconCls : 'book32',
				menuAlign: 'tr?',
				menu: new Ext.menu.Menu({
					items: [
						{
							text: langs('Справочник услуг'),
							tooltip: langs('Справочник услуг'),
							iconCls: 'services-complex16',
							handler: function() {
								getWnd('swUslugaTreeWindow').show({action: 'view'});
							}
						},{
							tooltip: langs('МКБ-10'),
							text: langs('Справочник МКБ-10'),
							iconCls: 'spr-mkb16',
							handler: function() {
								if ( !getWnd('swMkb10SearchWindow').isVisible() )
									getWnd('swMkb10SearchWindow').show();
							}
						}
					]
				})
			},
			action_Notify:
			{
				nn: 'action_Notify',
				tooltip: langs('Извещения/Направления'),
				text: langs('Извещения/Направления'),
				iconCls : 'doc-notify32',
				disabled: false,
				menuAlign: 'tr?',
				menu: new Ext.menu.Menu({
					items: [
						swPromedActions.EvnInfectNotify,
						swPromedActions.EvnNotifyHepatitis,
						swPromedActions.EvnOnkoNotify,
						swPromedActions.EvnNotifyOrphan,
						swPromedActions.EvnNotifyCrazy,
						swPromedActions.EvnNotifyNarko,
						swPromedActions.EvnNotifyTub,
						swPromedActions.EvnNotifyVener,
						swPromedActions.EvnNotifyHIV,
						swPromedActions.EvnNotifyNephro,
						swPromedActions.EvnNotifyProf,
						swPromedActions.EvnNotifyIBS,
						swPromedActions.EvnNotifyNolos
					]
				})
			},
			action_JourNotice: {
				handler: function() {
					getWnd('swMessagesViewWindow').show();
				}.createDelegate(this),
				iconCls: 'notice32',
				nn: 'action_JourNotice',
				text: langs('Журнал уведомлений'),
				tooltip: langs('Журнал уведомлений')
			},
			action_Newslatter: {
				handler: function() {
					getWnd('swNewslatterListWindow').show();
				}.createDelegate(this),
				hidden: !isUserGroup('Newslatter'),
				iconCls: 'mail32',
				nn: 'action_Newslatter',
				id: 'wpprw_action_Newslatter',
				text: langs('Управление рассылками'),
				tooltip: langs('Управление рассылками')
			}
		}
		// Копируем все действия для создания панели кнопок
		form.PanelActions = {};
		var Only16pxIconActions = ['action_Templ', 'action_CarAccidents'];

		for(var key in configActions)
		{
			var iconCls = '';

			if (key.inlist(Only16pxIconActions))
				iconCls = configActions[key].iconCls;
			else
				iconCls = configActions[key].iconCls.replace(/16/g, '32');

			var z = Ext.applyIf({cls: 'x-btn-large', iconCls: iconCls, text: ''}, configActions[key]);
			this.PanelActions[key] = new Ext.Action(z);
		}
		var actions_list = ['action_Turn','action_Timetable', 'action_TimetableProcCab', 'action_TimetableGraf', 'action_PersCardSearch','action_JournalHospit', 'action_References', 'action_Notify','action_Register', 'action_DopDisp','action_JourNotice', 'action_FindRegions',(isUserGroup('MedSvidDeath'))?'action_MedSvidDeath':'', 'action_PathoMorph', 'action_Templ','action_Svid','action_reports', 'action_Newslatter', 'action_doneProceduresReports'];
		var blocked_list = [];

		if (getRegionNick() != 'kz') {
			actions_list.push('action_PersDispSearchView');
		}
		if (getRegionNick() == 'kz') {
			actions_list.push('action_ScreenSearch');
		}
		// Создание кнопок для панели
		form.BtnActions = new Array();
		var i = 0;
		for(var key in form.PanelActions)
		{
			if (key.inlist(actions_list) && !key.inlist(blocked_list))
			{
				form.BtnActions.push(new Ext.Button(form.PanelActions[key]));
				i++;
			}
		}
		this.leftMenu = new Ext.Panel(
		{
			region: 'center',
			border: false,
			layout:'form',
			id: form.id + '_hhd',
			layoutConfig:
			{
				titleCollapse: true,
				animate: true,
				activeOnTop: false
			},
			items: form.BtnActions
		});
		this.leftPanel =
		{
			animCollapse: false,
			bodyStyle: 'padding-left: 5px',
			width: 60,
			id: 'mpwpLeftPanel',
			region: 'west',
			floatable: false,
			collapsible: true,
			layoutConfig:
			{
				titleCollapse: true,
				animate: true,
				activeOnTop: false
			},
			listeners:
			{
				collapse: function()
				{
					return;
				},
				resize: function (p,nW, nH, oW, oH)
				{
					var el = null;
					el = form.findById(form.id + '_slid');
					if(el)
						el.setHeight(this.body.dom.clientHeight-42);
					return;
				}
			},
			
			title: ' ',
			
			items: [
				new Ext.Button(
				{	cls:'upbuttonArr',
					iconCls:'uparrow',
					disabled: false, 
					handler: function() 
					{
						var el = form.findById(form.id + '_hhd');
						var d = el.body.dom;
						d.scrollTop -=38;
					}
				}),
				{
					border: false,
					layout:'border',
					id: form.id + '_slid',
					height:100,
					items:[this.leftMenu]
				},			
				new Ext.Button(
				{
				cls:'upbuttonArr',
				iconCls:'downarrow',
				disabled: false, 
				handler: function() 
				{
					var el = form.findById(form.id + '_hhd');
					var d = el.body.dom;
					d.scrollTop +=38;
					
				}
				})
			]
		};

		var wnd = this;

		this.ElectronicQueuePanel = new sw.Promed.ElectronicQueuePanel({
			ownerWindow: wnd,
			ownerGrid: wnd.ScheduleGrid, // передаем грид для работы с ЭО
			storeInitObject: wnd.storeInitObject, // передаем параметры для конструктора стора
			gridRefreshFn: function(options){ wnd.scheduleRefresh(options) }, // связываем метод обновления грида
			applyCallActionFn: function(){ wnd.scheduleOpen() }, // связываем метод открытия эмки
			layoutPanelId: 'mpwpSchedulePanel', // лэйаут для перерисовки в которой находится панель
			region: 'south'
		});
		
		Ext.apply(this, 
		{
			layout: 'border',
			items: 
			[{xtype:'datepickerrange'},
				this.TopPanel,
				this.leftPanel,
				{
					layout: 'border',
					region: 'center',
					id: 'mpwpSchedulePanel',
					items: [
						this.MedPersonalPanel,
						this.ScheduleGrid,
						this.ElectronicQueuePanel
					]
				}
				
			],
			buttons: 
			[{
				text: '-'
			}, 
			HelpButton(this, TABINDEX_MPSCHED + 98), 
			{
				iconCls: 'cancel16',
				text: BTN_FRMCLOSE,
				handler: function() {this.hide();}.createDelegate(this)
			}]
		});
		sw.Promed.swWorkPlacePhysWindow.superclass.initComponent.apply(this, arguments);
	}
});
