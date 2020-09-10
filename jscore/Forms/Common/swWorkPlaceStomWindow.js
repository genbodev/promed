/**
 * swWorkPlaceStomWindow - окно рабочего места врача-стоматолога поликлиники
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/projects/promed
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009-2013, Swan.
 * @author       Пермяков Александр
 * @prefix       wpsf
 * @version      08.2013
 *
 *
 Доступ к АРМ стоматолога определяется профилем отделения и типом группы отделений из рабочего места врача.
 Тип группы отделений:
 - LpuUnitType_SysNick должен быть из списка polka, ccenter, traumcenter, fap.
 Профиль отделения:
 - LpuSectionProfile_SysNick != priem
 - коды профиля для Перми - первые 2 цифры в коде 18 или код профиля 7181, 7182
 - коды профиля для Уфы - 526, 562, 626, 662, 527, 528, 529, 530, 559, 560, 561, 627, 628, 629, 630, 659, 660, 661, 827, 828, 829, 830, 859, 860, 861
 - для остальных регионов надо уточнять.
 */
/*NO PARSE JSON*/

sw.Promed.swWorkPlaceStomWindow = Ext.extend(sw.Promed.BaseForm,
{
	//объект с параметрами рабочего места, с которыми была открыта форма
	userMedStaffFact: null,
	closable: true,
	closeAction: 'hide',
	maximized: true,
	title: WND_WPMP,
	iconCls: 'workplace-mp16',
	id: 'swWorkPlaceStomWindow',
	readOnly: false,
	calledRecord: null,
	listeners: {

		beforeshow: function() {
			if ((!getGlobalOptions().medstafffact) || (getGlobalOptions().medstafffact.length==0))
			{
				Ext.Msg.alert(lang['soobschenie'], lang['tekuschiy_login_ne_sootnesen_s_vrachom_dostup_k_interfeysu_vracha_nevozmojen']);
				return false;
			}
		},

		'beforehide': function() {
			sw.Applets.uec.stopUecReader();
			sw.Applets.bdz.stopBdzReader();
			sw.Applets.BarcodeScaner.stopBarcodeScaner();
		}
	},

	getDataFromUec: function(uec_data, person_data) {
		var form = this;
		var grid = form.ScheduleGrid;
		var f = false;
		grid.getStore().each(function(record) {
			if (record.get('Person_id') == person_data.Person_id) {
				log(lang['nayden_v_gride']);

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
			log(lang['ne_nayden_v_gride']);
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
						Ext.Msg.alert('Сообщение', 'Пациент '+ response_obj.result['Person_FIO'] +' ('
							+ response_obj.result['Person_BirthDay'] +' г.р., возраст: '+
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
				log(lang['nayden_v_gride']);

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
			log(lang['ne_nayden_v_gride']);
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
						Ext.Msg.alert('Сообщение', 'Пациент '+ response_obj.result['Person_FIO'] +' ('
							+ response_obj.result['Person_BirthDay'] +' г.р., возраст: '+
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
	getCalendar: function () 
	{
		//return this.calendar;
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
			Ext.Msg.alert(lang['oshibka'], lang['spisok_raspisaniy_ne_nayden']);
			return false;
		}
		else if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('TimetableGraf_id') )
		{
			Ext.Msg.alert(lang['oshibka'], lang['ne_vyibrana_zapis']);
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
		/*if (this.findById('wpsfSearch_FIO').getValue().length>0)
		{
			params.Person_FIO = this.findById('wpsfSearch_FIO').getValue();
		}*/
		if ( this.findById('wpsfSearch_FirName').getValue().length > 0 )
		{
			params.Person_FirName = this.findById('wpsfSearch_FirName').getValue();
		}
		if ( this.findById('wpsfSearch_SecName').getValue().length > 0 )
		{
			params.Person_SecName = this.findById('wpsfSearch_SecName').getValue();
		}
		if ( this.findById('wpsfSearch_SurName').getValue().length > 0 )
		{
			params.Person_SurName = this.findById('wpsfSearch_SurName').getValue();
		}
		/*
		if (this.findById('wpsfSearch_UslugaComplexPid').getValue())
		{
			params.UslugaComplex_id = this.findById('wpsfSearch_UslugaComplexPid').getValue();
		}
		*/
		if (Ext.util.Format.date(this.findById('wpsfSearch_BirthDay').getValue(), 'd.m.Y').length>0)
		{
			params.Person_BirthDay = Ext.util.Format.date(this.findById('wpsfSearch_BirthDay').getValue(), 'd.m.Y');
		}

		/*
		params.callback = function() {
			this.scheduleCollapseDates();
		}.createDelegate(this);
		*/

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
                ,onSaveRecord: function() {
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

            this.getLoadMask(lang['podojdite_sohranyaetsya_zapis']).show();
            Ext.Ajax.request({
                url: C_QUEUE_APPLY,
                params: data,
                callback: function(options, success, response)
                {
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
			EvnDirectionData: pdata.EvnDirectionData || null,
			Person_id: pdata.Person_id,
			Server_id: pdata.Server_id,
			PersonEvn_id: pdata.PersonEvn_id,
			userMedStaffFact: this.userMedStaffFact,
			TimetableGraf_id: pdata.TimetableGraf_id || null,
			mode: 'workplace',
			ARMType: this.userMedStaffFact.ARMType,
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
		if (!(getGlobalOptions().region && getGlobalOptions().region.nick == 'ufa')) {
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
								sw.swMsg.alert(lang['oshibka'], lang['zapis_nevozmojna_v_svyazi_so_smertyu_patsienta']);
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
			Ext.Msg.alert(lang['soobschenie'], lang['okno_poiska_cheloveka_uje_otkryito']);
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
							useCase: 'create_evnplstom_without_recording',
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
									if (begTime && Ext.util.Format.date(begTime, 'd.m.Y') == getGlobalOptions().date && response_obj[0]['MedStaffFact_did'] == win.userMedStaffFact.MedStaffFact_id) {
										Ext.Msg.alert(lang['soobschenie'], lang['patsient_zapisan_na_tekuschiy_den_vospolzuytes_funktsiey_priema_po_zapisi']);
										return false;
									}
									var msg, buttons;
									if (17 == response_obj[0]['EvnStatus_id'] || response_obj[0]['Timetable_begTime']) {
										if (response_obj[0]['EvnDirection_IsAuto'] != 2) {
											msg = lang['obslujit_napravlenie_№'] + response_obj[0]['EvnDirection_Num'] + lang['po_profilyu'] + response_obj[0]['LpuSectionProfile_Name']
												+ ' (записано на ' + response_obj[0]['Timetable_begTime'] + ', врач ' + response_obj[0]['MSF_Person_Fin'] + ')?';
										} else {
											msg = 'Пациент записан на ' + response_obj[0]['Timetable_begTime'] + ', врач ' + response_obj[0]['MSF_Person_Fin'] + '.';
										}
										buttons = {
											yes: (response_obj[0]['EvnDirection_IsAuto'] != 2) ? lang['obslujit_napravlenie'] : 'Принять по этому направлению',
											no: lang['prinyat_bez_napravleniya'],
											cancel: lang['otmena']
										};
									} else if (10 == response_obj[0]['EvnStatus_id'] || !response_obj[0]['Timetable_begTime']) {
										if (response_obj[0]['EvnDirection_IsAuto'] != 2) {
											msg = lang['obslujit_napravlenie_№'] + response_obj[0]['EvnDirection_Num'] + lang['po_profilyu'] + response_obj[0]['LpuSectionProfile_Name']
												+ lang['i_ubrat_patsienta_iz_ocheredi'];
										} else {
											msg = lang['ubrat_patsienta_iz_ocheredi_po_profilyu'] + response_obj[0]['LpuSectionProfile_Name'] + '?';
										}
										buttons = {
											yes: lang['ubrat_iz_ocheredi'],
											no: lang['ostavit_v_ocheredi_i_prinyat_patsienta'],
											cancel: lang['otmena']
										};
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
										title: lang['vopros']
									});
								} else if ( response_obj.length > 1 ) {
									// выводим список этих направлений с возможностью выбрать одно из них
									getWnd('swEvnDirectionSelectWindow').show({
										useCase: 'create_evnplstom_without_recording',
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
							sw.swMsg.alert(lang['oshibka'], lang['zapis_nevozmojna_v_svyazi_so_smertyu_patsienta']);
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
	
	scheduleAdd: function() {
        if (getWnd('swPersonSearchWindow').isVisible())
        {
            Ext.Msg.alert(lang['soobschenie'], lang['okno_poiska_cheloveka_uje_otkryito']);
            //getWnd('swPersonSearchWindow').hide();
            return false;
        }

        getWnd('swPersonSearchWindow').show({
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
                        Ext.getCmp('swWorkPlaceStomWindow').scheduleSave(pdata);
                    },
                    onIsDead: function(res) {
                        sw.swMsg.alert(lang['oshibka'], lang['zapis_nevozmojna_v_svyazi_so_smertyu_patsienta']);
                    }
                });
            },
            searchMode: 'all'
        });
	},
	/**
	 * Записать из очереди
	 */
	sheduleAddFromQueue: function() {
		var grid = this.getGrid(), me = this;
		if (!grid || !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('TimetableGraf_id')) {
			Ext.Msg.alert(lang['oshibka'], lang['ne_vyibrana_zapis']);
			return false;
		}
		var record = grid.getSelectionModel().getSelected();
		getWnd('swMPQueueWindow').show({
			mode: 'select',
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
						} else  {
							sw.swMsg.alert(lang['oshibka'], lang['proizoshla_oshibka']);
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
	scheduleOpen: function()
	{
		var form = this;
		var grid = form.getGrid();

		if (!grid) {
			Ext.Msg.alert(lang['oshibka'], lang['spisok_raspisaniy_ne_nayden']);
			return false;
		} else if (
			!grid.getSelectionModel().getSelected()
			|| !grid.getSelectionModel().getSelected().get('TimetableGraf_id')
		){
			Ext.Msg.alert(lang['oshibka'], lang['ne_vyibrana_zapis']);
			return false;
		}

		var record = grid.getSelectionModel().getSelected();
		var isMyOwnRecord = record.get('pmUser_updId') == getGlobalOptions().pmuser_id;

		// если неизвестный не показываем ЭМКу
		if (!form.ElectronicQueuePanel.checkIsUnknown({record: record})) {log('is_unknown'); return false; }

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
					msg = lang['obslujit_napravlenie_№'] + record.get('EvnDirection_Num') + lang['po_profilyu'] + record.get('LpuSectionProfile_Name') 
						+ lang['i_osvobodit_birku_na'] + record.get('TimetableGraf_Date').format('d.m.Y')
						+ ' ' + record.get('TimetableGraf_begTime') + ', врач ' + record.get('MSF_Person_Fin') + '?';
				} else {
					msg = lang['osvobodit_birku_na'] + record.get('TimetableGraf_Date').format('d.m.Y')
						+ ' ' + record.get('TimetableGraf_begTime') + ', врач ' + record.get('MSF_Person_Fin') + '?';
				}
				sw.swMsg.show(
				{
					buttons: {
						yes: ('true' == record.get('IsEvnDirection')) ? lang['obslujit_napravlenie'] : lang['osvobodit_zapis'],
						no: ('true' == record.get('IsEvnDirection')) ? lang['prinyat_bez_napravleniya'] : lang['prinyat_bez_zapisi'],
						cancel: lang['otmena']
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
					title: lang['vopros']
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
			loadMask: this.getLoadMask(lang['pojaluysta_podojdite']),
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
			loadMask: this.getLoadMask(lang['pojaluysta_podojdite']),
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
		var win = this;
		var record = this.getSelectedRecord();
		var grid = this.getGrid();
		if (record == false || !record.get('TimetableGraf_id')) {
			return false;
		}
		if(typeof(options) != 'object') {
			options = {};
		}

		if(!options.ignoreLinkCheck) {
			Ext.Ajax.request({
				url: '/?c=EvnDirection&m=checkEQMedStaffFactLink',
				success: function(responseText) {
					var response = Ext.util.JSON.decode(responseText.responseText);
					if(response.MedStaffFactLinked == true) {
						sw.swMsg.show({
							icon: Ext.MessageBox.QUESTION,
							msg: 'При подтверждении действия пациент будет исключён из электронной очереди и поставлен в очередь на приём. Приём данного пациента по электронной очереди будет недоступен.',
							title: langs('Внимание'),
							buttons: Ext.Msg.YESNO,
							fn: function(buttonId, text, obj){
								if ('yes' == buttonId){	
									options.ignoreLinkCheck = 1;
									win.returnToQueue(options);
								}
							}
						});
					} else {
						options.ignoreLinkCheck = 1;
						win.returnToQueue(options);
					}
					return false;
				}
			});	
			return false;	
		}

		return sw.Promed.Direction.returnToQueue({
			loadMask: this.getLoadMask(lang['pojaluysta_podojdite']),
			EvnDirection_id: record.get('EvnDirection_id'),
			TimetableGraf_id: record.get('TimetableGraf_id'),
			EvnQueue_id: record.get('EvnQueue_id'),
			callback: function (data) {
				grid.getStore().reload({
					callback: function () {
						if (data.EvnDirection_id) {
							var index = grid.getStore().findBy( function(record) {
								if ( rec.get('TimetableGraf_id') == record.get('TimetableGraf_id') ) {
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
		if (typeof options != 'object') { options = new Object() }

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
            sw.swMsg.alert(lang['oshibka'], lang['zapis_ne_vyibrana']);
            return false;
        }

		var params = {};
        if (action && action == 'row') {
			params.rowId = record.id;
            Ext.ux.GridPrinter.print(this.getGrid(), params);
        } else {
            Ext.ux.GridPrinter.print(this.getGrid(),params);
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
	show: function()
	{
		var wnd = this;
		sw.Promed.swWorkPlaceStomWindow.superclass.show.apply(this, arguments);
		
		//this.findById('wpsfSearch_FIO').setValue(null);
		this.findById('wpsfSearch_SurName').setValue(null);
		this.findById('wpsfSearch_SecName').setValue(null);
		this.findById('wpsfSearch_FirName').setValue(null);
		//this.findById('wpsfSearch_FIO').setValue(null);
		this.findById('wpsfSearch_BirthDay').setValue(null);
		//this.findById('wpsfSearch_UslugaComplexPid').clearValue();
		// Проверяем права пользователя открывшего форму 

		if ((!arguments[0]) || (!arguments[0].userMedStaffFact) || (!arguments[0].userMedStaffFact.ARMType))
		{
			sw.swMsg.alert('Ошибка открытия формы', 'Ошибка открытия формы "'+this.title+'".<br/>Не указан тип АРМа врача.', function() { wnd.hide(); });
			return false;
		}

		this.userMedStaffFact = arguments[0].userMedStaffFact;

		log('userMedStaffFact', this.userMedStaffFact);
		// Форма может открываться с разных мест, поэтому если она откывается для того, чтобы записать пациента к другому врачу
		// то предварительно надо запомнить параметры.

		sw.Applets.uec.startUecReader({callback: this.getDataFromUec.createDelegate(this)});
		sw.Applets.bdz.startBdzReader({callback: this.getDataFromBdz.createDelegate(this)});
		sw.Applets.BarcodeScaner.startBarcodeScaner({ callback: this.getDataFromUec.createDelegate(this) });

        // Создаем свой заголовок, единый для всех армов, на основании данных пришедших с сервера ( из User_model)

        //log(this.userMedStaffFact);
        //log(sw.Promed.MedStaffFactByUser);

        sw.Promed.MedStaffFactByUser.setMenuTitle(this, this.userMedStaffFact);

		this.curDate = getGlobalOptions().date;

        this.dateMenu.setValue(getGlobalOptions().date +' - '+ getGlobalOptions().date);
        this.TopPanel.show();
        //this.gridActions.create.show();
        this.setActionDisabled('create',false);
        this.gridActions.open.show();
        this.setActionDisabled('open',false);
        this.findById('wpsfSchedulePanel').syncSize();

        for ( btnAction in this.BtnActions ) {
            if ( typeof this.BtnActions[btnAction] == 'object' ) {
                this.BtnActions[btnAction].show();
            }
        }

		// запустим ЭО
		this.ElectronicQueuePanel.initElectronicQueue();

		this.scheduleRefresh();
		this.checkMedStaffFactReplace();
		this.syncSize();
	},

	checkMedStaffFactReplace: function() {
		var win = this;
		win.findById('wpsf_MedStaffFactFilterType').hide();
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
					win.findById('wpsf_MedStaffFactFilterType').show();
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
			{name:'open_emk', text:lang['otkryit_emk'], tooltip: lang['otkryit_elektronnuyu_meditsinskuyu_kartu_patsienta'], iconCls : 'open16', handler: function() {this.scheduleOpen();}.createDelegate(this)},
			{name:'open', text:lang['otkryit'], tooltip: lang['otkryit'], iconCls : 'x-btn-text', icon: 'img/icons/open16.png', handler: function() {this.scheduleOpen()}.createDelegate(this)},
			{name:'reception_soc', text:lang['prinyat_po_sots_karte'], hidden: !(getGlobalOptions().region && getGlobalOptions().region.nick == 'ufa'), tooltip: lang['prinyat_po_sots_karte'], iconCls : 'copy16', handler: function() {this.receptionBySocCard()}.createDelegate(this)},
			{name:'create', text:lang['prinyat_bez_zapisi'], tooltip: lang['patsient_bez_zapisi'], iconCls : 'copy16', handler: function() {this.scheduleNew()}.createDelegate(this)},
			{name:'add', text:lang['zapisat_patsienta'], tooltip: lang['zapisat_patsienta'], iconCls : 'x-btn-text', icon: 'img/icons/add16.png', handler: function() {this.scheduleAdd()}.createDelegate(this)},
			{name:'queue', text:lang['zapisat_iz_ocheredi'], tooltip: lang['zapisat_iz_ocheredi'], iconCls : 'x-btn-text', icon: 'img/icons/add16.png', handler: function() {this.sheduleAddFromQueue()}.createDelegate(this)},
			{name:'reject', text:lang['otklonit'], tooltip: lang['otklonit'], iconCls : 'x-btn-text', icon: 'img/icons/delete16.png', handler: function() {win.reject()}},
			{name:'returnToQueue', text:lang['ubrat_v_ochered'], tooltip: lang['ubrat_v_ochered'], iconCls : 'x-btn-text', icon: 'img/icons/delete16.png', handler: function() {win.returnToQueue()}},
			{name:'refresh', text:BTN_GRIDREFR, tooltip: BTN_GRIDREFR, iconCls : 'x-btn-text', icon: 'img/icons/refresh16.png', handler: function() {this.scheduleRefresh()}.createDelegate(this)},
			{name:'additionally', key: 'additionally', text:lang['dopolnitelno'], menu: [
				new Ext.Action({name:'rewrite', text:lang['perezapisat'], tooltip: lang['perezapisat'], handler: function() {win.rewrite()}}),
				new Ext.Action({name:'redirect', text:lang['perenapravit'], tooltip: lang['perenapravit'], handler: function() {win.redirect()}, hidden: true})
			], tooltip: lang['dopolnitelno'], iconCls : 'x-btn-text', icon: 'img/icons/actions16.png'},
			{name:'actions', key: 'actions', text:lang['deystviya'], menu: [
				new Ext.Action({name:'collapse_all', text:lang['svernut_vse'], tooltip: lang['svernut_vse'], handler: function() {this.scheduleCollapseDates()}.createDelegate(this)}),
				new Ext.Action({name:'expand_all', text:lang['razvernut_vse'], tooltip: lang['razvernut_vse'], handler: function() {this.scheduleExpandDates()}.createDelegate(this)})
			], tooltip: lang['deystviya'], iconCls : 'x-btn-text', icon: 'img/icons/actions16.png', handler: function() {}},
			{name:'printPacList', iconCls: 'print16', text: 'Печать списка пациентов', tooltip : "Печать списка пациентов", hidden: (getGlobalOptions().region.nick != 'ufa'), handler: function () {	this.printPacList();}.createDelegate(this)},
			{name:'print', text:BTN_GRIDPRINT, tooltip: BTN_GRIDPRINT, iconCls : 'x-btn-text', icon: 'img/icons/print16.png', menu: [
				new Ext.Action({name:'print_rec', text:lang['pechat'], handler: function() {this.schedulePrint('row')}.createDelegate(this)}),
				new Ext.Action({name:'print_all', text:lang['pechat_spiska'], handler: function() {this.schedulePrint()}.createDelegate(this)})
            ]},
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
			id: 'wpsfToolbar',
			items:
			[
				this.gridActions.open_emk,
				this.gridActions.reception_soc,
				this.gridActions.create,
				this.gridActions.add,
				this.gridActions.queue,
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
            name: 'Person_Phone_all'
        },
		{
			name: 'Person_IsEvents'
		},
		{
			name: 'pmUser_updId'
		},
		{
			name: 'PersonQuarantine_IsOn'
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
		{ name: 'IsEvnDirection'}
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
						var form = Ext.getCmp('swWorkPlaceStomWindow');
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
								if (!form.gridActions.reject.initialConfig.initialDisabled)
									form.gridActions.reject.setDisabled(false);
								if (!form.gridActions.returnToQueue.initialConfig.initialDisabled)
									form.gridActions.returnToQueue.setDisabled(false);
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
					var form = Ext.getCmp('swWorkPlaceStomWindow');
					form.gridActions.open.setDisabled(true);
					//form.gridActions.create.setDisabled(true);
					form.gridActions.add.setDisabled(true);
					form.gridActions.queue.setDisabled(true);
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
			fieldLabel: lang['period'],
			plugins: 
			[
				new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)
			]
		});
		
		this.dateMenu.addListener('keydown',function (inp, e) 
		{
			var form = Ext.getCmp('swWorkPlaceStomWindow');
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
			var form = Ext.getCmp('swWorkPlaceStomWindow');
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
			text: lang['predyiduschiy'],
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
			text: lang['sleduyuschiy'],
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
			text: lang['den'],
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
			text: lang['nedelya'],
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
			text: lang['mesyats'],
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
			text: lang['period'],
			disabled: true,
			xtype: 'button',
			toggleGroup: 'periodToggle',
			iconCls: 'datepicker-range16',
			handler: function()
			{
				this.scheduleLoad('range');
			}.createDelegate(this)
		});

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
				this.formActions.range
			]
		});

		var wnd = this;

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
				title: lang['poisk'],
				collapsible: true,
				layout: 'column',
				items:
				[{
					layout: 'form',
					labelWidth: 55,
					items:
					[{
						xtype: 'textfieldpmw',
						width: 120,
						id: 'wpsfSearch_SurName',
						fieldLabel: lang['familiya'],
						listeners:
						{
							'keydown': function (inp, e)
							{
								var form = Ext.getCmp('swWorkPlaceStomWindow');
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
						id: 'wpsfSearch_FirName',
						fieldLabel: lang['imya'],
						listeners:
						{
							'keydown': function (inp, e)
							{
								var form = Ext.getCmp('swWorkPlaceStomWindow');
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
						id: 'wpsfSearch_SecName',
						fieldLabel: lang['otchestvo'],
						listeners:
						{
							'keydown': function (inp, e)
							{
								var form = Ext.getCmp('swWorkPlaceStomWindow');
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
						id: 'wpsfSearch_BirthDay',
						fieldLabel: lang['data_rojdeniya'],
						listeners:
						{
							'keydown': function (inp, e)
							{
								var form = Ext.getCmp('swWorkPlaceStomWindow');
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
					items:
					[{
						style: "padding-left: 20px",
						xtype: 'button',
						id: 'wpsfBtnSearch',
						text: lang['nayti'],
						iconCls: 'search16',
						handler: function()
						{
							var form = Ext.getCmp('swWorkPlaceStomWindow');
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
						id: 'wpsfBtnClear',
						text: lang['sbros'],
						iconCls: 'resetsearch16',
						handler: function()
						{
							var form = Ext.getCmp('swWorkPlaceStomWindow');
							//form.findById('wpsfSearch_FIO').setValue(null);
							form.findById('wpsfSearch_SurName').setValue(null);
							form.findById('wpsfSearch_FirName').setValue(null);
							form.findById('wpsfSearch_SecName').setValue(null);
							form.findById('wpsfSearch_BirthDay').setValue(null);
							form.scheduleLoad();
						}
					}]
				}]
			}, {
				title: 'Список записанных пациентов',
				id: 'wpsf_MedStaffFactFilterType',
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
								form.scheduleRefresh();
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
								form.scheduleRefresh();
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
								form.scheduleRefresh();
							}
						},
						checked: true
					}]
				}]
			}]
		});

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
				dataIndex: 'TimetableGraf_id'
			},
			{
				header: "Место работы",
				hidden: true,
				hideable: false,
				dataIndex: 'MedStaffFact_id'
			},
			{
				header: lang['otdelenie'],
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
				header: "Карантин",
				hidden: true,
				hideable: false,
				dataIndex: 'PersonQuarantine_IsOn'
			},
			{
				header: lang['osmotr'],
				width: 65,
				sortable: true,
				dataIndex: 'Person_IsEvents',
				renderer: sw.Promed.Format.checkColumn
			},
			{
				header: lang['data'],
				width: 60,
				hidden: true,
				sortable: true,
				dataIndex: 'TimetableGraf_Date',
				renderer: Ext.util.Format.dateRenderer('d.m.Y')
			}, 
			{
				header: 'Записан (когда)',
				width: 90,
				sortable: true,
				//renderer: Ext.util.Format.dateRenderer('H:i'),
				dataIndex: 'TimetableGraf_begTime'
			},
			{
				header: lang['priem'],
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
				header: "Фамилия Имя Отчество",
				width: 250,
				sortable: true,
				dataIndex: 'Person_FIO'
			}, 
			{
				header: "Дата рождения",
				width: 100,
				sortable: true,
				dataIndex: 'Person_BirthDay',
				renderer: Ext.util.Format.dateRenderer('d.m.Y')
			},
			{
				header: lang['vozrast'],
				width: 55,
				sortable: true,
				dataIndex: 'Person_Age'
			},
            {
                header: lang['telefon'],
                width: 80,
                sortable: true,
                dataIndex: 'Person_Phone_all'
            },
			{
				header: lang['napravlenie'],
				width: 100,
				sortable: false,
				dataIndex: 'IsEvnDirection',
				renderer: sw.Promed.Format.dirColumn
			},
			{ header: "EvnDirection_id", hidden: true, hideable: false, dataIndex: 'EvnDirection_id' },
			{ header: "EvnQueue_id", hidden: true, hideable: false, dataIndex: 'EvnQueue_id' },
			{ header: "EvnStatus_id", hidden: true, hideable: false, dataIndex: 'EvnStatus_id' },
			{
				header: lang['bdz'],
				width: 40,
				sortable: false,
				dataIndex: 'Person_IsBDZ',
				renderer: sw.Promed.Format.checkColumn
			},
			{
				header: (getRegionNick().inlist([ 'kz' ]) ? "Льгота" : "ФЛ"),
				width: 40,
				sortable: false,
				dataIndex: 'Person_IsFedLgot',
				renderer: sw.Promed.Format.checkColumn
			},
			{
				header: lang['rl'],
				width: 40,
				sortable: false,
				dataIndex: 'Person_IsRegLgot',
				renderer: sw.Promed.Format.checkColumn,
				hideable: !getRegionNick().inlist([ 'kz' ]),
				hidden: getRegionNick().inlist([ 'kz' ])
			},
			{
				header: "ЛПУ прикр.",
				width: 80,
				sortable: false,
				dataIndex: 'Lpu_Nick'
			},
			{
				header: lang['uchastok'],
				width: 60,
				sortable: false,
				dataIndex: 'LpuRegion_Name'
			},
			{
				header: lang['zapisan'],
				width: 100,
				sortable: true,
				dataIndex: 'TimetableGraf_updDT',
				renderer: Ext.util.Format.dateRenderer('d.m.Y H:i')
			}, 
			{
				header: lang['operator'],
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
			}

			],
			
			view: new Ext.grid.GroupingView(
			{
                enableGroupingMenu:false,
				groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length.inlist([2,3,4]) ? "записи" : "записей"]})'
			}),
			loadStore: function(params, callback)
			{
				if (!this.params) this.params = null;

				if (params) {

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

						if (success) {
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
							if (callback && typeof callback == 'function') { callback() }
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
						var form = Ext.getCmp('swWorkPlaceStomWindow');
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
							if ((record.get('Person_id')==null) || (record.get('Person_id')==''))
							{
								var TimetableGraf_Date = record.get('TimetableGraf_Date');
								var current_date = Date.parseDate(getGlobalOptions().date, 'd.m.Y');
								if (!form.gridActions.open.initialConfig.initialDisabled)
									form.gridActions.open.setDisabled(true);
								if (!form.gridActions.create.initialConfig.initialDisabled)
									// блокируем кнопку "Без записи" в предыдущих днях, т.к. запись все равно происходит на текущий день
									form.gridActions.create.setDisabled(current_date > TimetableGraf_Date);
								if (!form.gridActions.add.initialConfig.initialDisabled)
									// запрещаем запись пациента на прошедшую дату
									form.gridActions.add.setDisabled(current_date > TimetableGraf_Date);
								if (!form.gridActions.queue.initialConfig.initialDisabled)
									// запрещаем запись из очереди на прошедшую дату
									form.gridActions.queue.setDisabled(current_date > TimetableGraf_Date);
								if (!form.gridActions.reject.initialConfig.initialDisabled)
									form.gridActions.reject.setDisabled(true);
								if (!form.gridActions.returnToQueue.initialConfig.initialDisabled)
									form.gridActions.returnToQueue.setDisabled(true);
							}
							else 
							{
								if (!form.gridActions.open.initialConfig.initialDisabled)
									form.gridActions.open.setDisabled(false);
								if (!form.gridActions.create.initialConfig.initialDisabled)
									form.gridActions.create.setDisabled(false);
								if (!form.gridActions.add.initialConfig.initialDisabled)
									form.gridActions.add.setDisabled(true);
								if (!form.gridActions.queue.initialConfig.initialDisabled)
									form.gridActions.queue.setDisabled(true);

								var TimetableGraf_Date = record.get('TimetableGraf_Date');
								var current_date = Date.parseDate(getGlobalOptions().date, 'd.m.Y');
								if (!form.gridActions.reject.initialConfig.initialDisabled)
									form.gridActions.reject.setDisabled( // Disabled where
										((!isAdmin) // this user
										&& (
										(record.get('pmUser_updId') != getGlobalOptions().pmuser_id) // this other autor of record
										|| (current_date > TimetableGraf_Date) 
										|| (15 == record.get('EvnStatus_id')) 
										|| (current_date.format('d.m.Y') == TimetableGraf_Date.format('d.m.Y') && record.get('Person_IsEvents') == 'true') // in current day opened TAP
										)) || !record.get('EvnDirection_id')
									);
								if (!form.gridActions.returnToQueue.initialConfig.initialDisabled)
									form.gridActions.returnToQueue.setDisabled( // Disabled where
										((!isAdmin) // this user
										&& (
										(record.get('pmUser_updId') != getGlobalOptions().pmuser_id) // this other autor of record
										|| (current_date > TimetableGraf_Date) 
										|| (15 == record.get('EvnStatus_id')) 
										|| (current_date.format('d.m.Y') == TimetableGraf_Date.format('d.m.Y') && record.get('Person_IsEvents') == 'true') // in current day opened TAP
										)) || !record.get('EvnDirection_id')
									);
							}
						}
					},
					'rowdeselect': function(sm, rowIdx, record){}
				}
			})
		});

		this.ScheduleGrid.view = new Ext.grid.GridView({
			getRowClass : function (row, index) {
				var cls = '';
				if (cls.length == 0) {
					cls = 'x-grid-panel ';
				}

				if (row.get('PersonQuarantine_IsOn') == 'true') {
					cls = cls + 'x-grid-rowbackred ';
				}

				return cls;
			}
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
			var win = Ext.getCmp('swWorkPlaceStomWindow');
			var rec = grid.getSelectionModel().getSelected();
			if (col == 14 && rec.get('EvnDirection_id') != '') { // столбец с направлением
				/*getWnd('swEvnDirectionEditWindow').show({
					Person_id: rec.get('Person_id'),
					EvnDirection_id: rec.get('EvnDirection_id'),
					action: 'view',
					formParams: new Object()
				});*/
			} else {
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
			}
		});
		// Клин на иконку направления
		this.ScheduleGrid.on('cellclick', function(grid, row, col, object)
		{
			var win = Ext.getCmp('swWorkPlaceStomWindow');
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
			var win = Ext.getCmp('swWorkPlaceStomWindow');
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
						var o = win.findById('wpsfBtnClear');
					}
					else
					{
						//var o = win.findById('wpsfSearch_FIO');
						var o = win.findById('wpsfSearch_SurName');
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
			Ext.Ajax.request({
				url: '/?c=LpuRegion&m=getMedPersLpuRegionList',
				callback: function(options, success, response) 
				{
					if (success)
					{
						var response_obj = Ext.util.JSON.decode(response.responseText);
						if (response_obj[0])
						{
							getWnd(wnd).show({
								LpuAttachType_id: response_obj[0].LpuAttachType_id || null,
								LpuRegionType_id: response_obj[0].LpuRegionType_id || null,
								LpuRegion_id: response_obj[0].LpuRegion_id || null
							});
						}
						else
						{
							getWnd(wnd).show();
						}
					}
				},
				params: {
                    LpuSectionProfile_Code: form.userMedStaffFact.LpuSectionProfile_Code,
                    MedPersonal_id: form.userMedStaffFact.MedPersonal_id,
                    Lpu_id: form.userMedStaffFact.Lpu_id
                }
			});
		};
		
		var swPromedActions = {
				PersonDispOrpSearch: {
					tooltip: lang['registr_detey-sirot'],
					text: lang['registr_detey-sirot'],
					iconCls : 'doc-reg16',
					handler: function() 
					{
						getWnd('swPersonDispOrpSearchWindow').show();
					}
				},
				PersonPrivilegeWOWSearch: {
					tooltip: lang['registr_vov'],
					text: lang['registr_vov'],
					iconCls : 'doc-reg16',
					handler: function() 
					{
						getWnd('swPersonPrivilegeWOWSearchWindow').show();
					}
				},
				PersonDopDispSearch: {
					tooltip: lang['registr_dd'],
					text: lang['registr_dd'],
					iconCls : 'doc-reg16',
					handler: function() 
					{
						getWnd('swPersonDopDispSearchWindow').show();
					}
				},
				EvnPLDispTeen14Search: {
					tooltip: lang['registr_dekretirovannyih_vozrastov'],
					text: lang['registr_dekretirovannyih_vozrastov'],
					iconCls : 'doc-reg16',
					handler: function() 
					{
						getWnd('swEvnPLDispTeen14SearchWindow').show();
					}
				},
				OrphanRegistry: sw.Promed.personRegister.getOrphanBtnConfig(form.id, form),
				EvnNotifyOrphan: sw.Promed.personRegister.getEvnNotifyOrphanBtnConfig(form.id, form),
				CrazyRegistry:
				{
					tooltip: lang['registr_po_psihiatrii'],
					text: lang['registr_po_psihiatrii'],
					iconCls : 'doc-reg16',
					disabled: (String(getGlobalOptions().groups).indexOf('Crazy', 0) < 0),
					handler: function()
					{
						getWnd('swCrazyRegistryWindow').show({userMedStaffFact: this.userMedStaffFact});
					}.createDelegate(this)
				},
				EvnNotifyCrazy:
				{
					tooltip: lang['jurnal_izvescheniy_po_psihiatrii'],
					text: lang['jurnal_izvescheniy_po_psihiatrii'],
					iconCls : 'journal16',
					disabled: (String(getGlobalOptions().groups).indexOf('Crazy', 0) < 0),
					handler: function()
					{
						getWnd('swEvnNotifyCrazyListWindow').show({userMedStaffFact: this.userMedStaffFact});
					}.createDelegate(this)
				},
				NarkoRegistry:
				{
					tooltip: lang['registr_po_narkologii'],
					text: lang['registr_po_narkologii'],
					iconCls : 'doc-reg16',
					disabled: (String(getGlobalOptions().groups).indexOf('Narko', 0) < 0),
					handler: function()
					{
						getWnd('swNarkoRegistryWindow').show({userMedStaffFact: this.userMedStaffFact});
					}.createDelegate(this)
				},
				EvnNotifyNarko:
				{
					tooltip: lang['jurnal_izvescheniy_po_narkologii'],
					text: lang['jurnal_izvescheniy_po_narkologii'],
					iconCls : 'journal16',
					disabled: (String(getGlobalOptions().groups).indexOf('Narko', 0) < 0),
					handler: function()
					{
						getWnd('swEvnNotifyNarkoListWindow').show({userMedStaffFact: this.userMedStaffFact});
					}.createDelegate(this)
				},
				TubRegistry:
				{
					tooltip: lang['registr_bolnyih_tuberkulezom'],
					text: lang['registr_po_tuberkuleznyim_zabolevaniyam'],
					iconCls : 'doc-reg16',
					disabled: (String(getGlobalOptions().groups).indexOf('Tub', 0) < 0),
					handler: function()
					{
						getWnd('swTubRegistryWindow').show({userMedStaffFact: this.userMedStaffFact});
					}.createDelegate(this)
				},
				EvnNotifyTub:
				{
					tooltip: lang['jurnal_izvescheniy_o_bolnyih_tuberkulezom'],
					text: lang['jurnal_izvescheniy_po_tuberkuleznyim_zabolevaniyam'],
					iconCls : 'journal16',
					disabled: (String(getGlobalOptions().groups).indexOf('Tub', 0) < 0),
					handler: function()
					{
						getWnd('swEvnNotifyTubListWindow').show({userMedStaffFact: this.userMedStaffFact});
					}.createDelegate(this)
				},
				VenerRegistry:
				{
					tooltip: lang['registr_bolnyih_venericheskim_zabolevaniem'],
					text: lang['registr_bolnyih_venericheskim_zabolevaniem'],
					iconCls : 'doc-reg16',
					disabled: (String(getGlobalOptions().groups).indexOf('Vener', 0) < 0),
					handler: function()
					{
						getWnd('swVenerRegistryWindow').show({userMedStaffFact: this.userMedStaffFact});
					}.createDelegate(this)
				},
				EvnNotifyVener:
				{
					tooltip: lang['jurnal_izvescheniy_o_bolnyih_venericheskim_zabolevaniem'],
					text: lang['jurnal_izvescheniy_o_bolnyih_venericheskim_zabolevaniem'],
					iconCls : 'journal16',
					disabled: (String(getGlobalOptions().groups).indexOf('Vener', 0) < 0),
					handler: function()
					{
						getWnd('swEvnNotifyVenerListWindow').show({userMedStaffFact: this.userMedStaffFact});
					}.createDelegate(this)
				},
				HIVRegistry:
				{
					tooltip: lang['registr_vich-infitsirovannyih'],
					text: lang['registr_vich-infitsirovannyih'],
					iconCls : 'doc-reg16',
					disabled: !allowHIVRegistry(),
					handler: function()
					{
						getWnd('swHIVRegistryWindow').show({userMedStaffFact: this.userMedStaffFact});
					}.createDelegate(this)
				},
				EvnNotifyHIV:
				{
					tooltip: lang['jurnal_izvescheniy_o_vich-infitsirovannyih'],
					text: lang['jurnal_izvescheniy_o_vich-infitsirovannyih'],
					iconCls : 'journal16',
					disabled: (String(getGlobalOptions().groups).indexOf('HIV', 0) < 0),
					handler: function()
					{
						getWnd('swEvnNotifyHIVListWindow').show({userMedStaffFact: this.userMedStaffFact});
					}.createDelegate(this)
				},
				HepatitisRegistry: 
				{
					tooltip: lang['registr_po_virusnomu_gepatitu'],
					text: lang['registr_po_virusnomu_gepatitu'],
					iconCls : 'doc-reg16',
					handler: function()
					{
						if ( getWnd('swHepatitisRegistryWindow').isVisible() ) {
							sw.swMsg.show({
								buttons: Ext.Msg.OK,
								fn: Ext.emptyFn,
								icon: Ext.Msg.WARNING,
								msg: lang['okno_uje_otkryito'],
								title: ERR_WND_TIT
							});
							return false;
						}
						getWnd('swHepatitisRegistryWindow').show({userMedStaffFact: this.userMedStaffFact});
					}.createDelegate(this)
				},
				EvnNotifyHepatitis: 
				{
					tooltip: lang['jurnal_izvescheniy_po_virusnomu_gepatitu'],
					text: lang['jurnal_izvescheniy_po_virusnomu_gepatitu'],
					iconCls : 'journal16',
					handler: function()
					{
						if ( getWnd('swEvnNotifyHepatitisListWindow').isVisible() ) {
							sw.swMsg.show({
								buttons: Ext.Msg.OK,
								fn: Ext.emptyFn,
								icon: Ext.Msg.WARNING,
								msg: lang['okno_uje_otkryito'],
								title: ERR_WND_TIT
							});
							return false;
						}
						getWnd('swEvnNotifyHepatitisListWindow').show({userMedStaffFact: this.userMedStaffFact});
					}.createDelegate(this)
				},
				EvnInfectNotify: 
				{
					tooltip: lang['jurnal_izvescheniy_forma_№058_u'],
					text: lang['jurnal_izvescheniy_forma_№058_u'],
					iconCls : 'journal16',
					disabled: false, 
					handler: function()
					{
						if ( getWnd('swEvnInfectNotifyListWindow').isVisible() ) {
							sw.swMsg.show({
								buttons: Ext.Msg.OK,
								fn: Ext.emptyFn,
								icon: Ext.Msg.WARNING,
								msg: lang['okno_uje_otkryito'],
								title: ERR_WND_TIT
							});
							return false;
						}
						getWnd('swEvnInfectNotifyListWindow').show({userMedStaffFact: this.userMedStaffFact});
					}.createDelegate(this)
				},
				OnkoRegistry: 
				{
					tooltip: lang['registr_po_onkologii'],
					text: lang['registr_po_onkologii'],
					iconCls : 'doc-reg16',
					disabled: (String(getGlobalOptions().groups).indexOf('OnkoRegistry', 0) < 0), 
					handler: function()
					{
						if ( getWnd('swOnkoRegistryWindow').isVisible() ) {
							sw.swMsg.show({
								buttons: Ext.Msg.OK,
								fn: Ext.emptyFn,
								icon: Ext.Msg.WARNING,
								msg: lang['okno_uje_otkryito'],
								title: ERR_WND_TIT
							});
							return false;
						}
						getWnd('swOnkoRegistryWindow').show({userMedStaffFact: this.userMedStaffFact});
					}.createDelegate(this)
				},
				EvnOnkoNotify: 
				{
					tooltip: lang['jurnal_izvescheniy_ob_onkobolnyih'],
					text: lang['jurnal_izvescheniy_ob_onkobolnyih'],
					iconCls : 'journal16',
					handler: function()
					{
						if ( getWnd('swEvnOnkoNotifyListWindow').isVisible() ) {
							sw.swMsg.show({
								buttons: Ext.Msg.OK,
								fn: Ext.emptyFn,
								icon: Ext.Msg.WARNING,
								msg: lang['okno_uje_otkryito'],
								title: ERR_WND_TIT
							});
							return false;
						}
						getWnd('swEvnOnkoNotifyListWindow').show({userMedStaffFact: this.userMedStaffFact});
					}.createDelegate(this)
				}
		};

		// Формирование списка всех акшенов 
		var configActions = 
		{
			action_Timetable: 
			{
				nn: 'action_Timetable',
				tooltip: lang['rabota_s_raspisaniem'],
				text: lang['raspisanie'],
				iconCls : 'mp-timetable32',
				disabled: false, 
				hidden: !IS_DEBUG,
				handler: function() 
				{
					getWnd('swTTGScheduleEditWindow').show();
				}
			},
			action_Turn: 
			{
				nn: 'action_Turn',
				iconCls : 'mp-queue32',
				disabled: false, 
				text: lang['jurnal_napravleniy'],
				tooltip: lang['jurnal_napravleniy'],
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
			action_Calendar: 
			{
				nn: 'action_Calendar',
				tooltip: lang['rabota_s_kalendarem'],
				text: lang['kalendar'],
				iconCls : 'mp-calendar32',
				disabled: false, 
				hidden: !IS_DEBUG,
				handler: function() 
				{
					return;
				}
			},
            action_MidMedPersonal:
            {
                disabled: false,
                handler: function()
                {
                    getWnd('swMedStaffFactLinkViewWindow').show({
                        MedStaffFact_id: form.userMedStaffFact.MedStaffFact_id
                        ,onHide: Ext.emptyFn
                    });
                },
                hidden: !IS_DEBUG,
                iconCls: 'staff32',
                nn: 'action_MidMedPersonal',
                text: lang['sredniy_med_personal'],
                tooltip: lang['dostup_sr_med_personala_k_emk']
            },
            action_directories: {
                nn: 'action_directories',
                text: lang['spravochniki'],
                tooltip: lang['spravochniki'],
                iconCls: 'book32',
                menuAlign: 'tr',
                menu: new Ext.menu.Menu({
                    items: [
                        {
                            text: lang['spravochnik_uslug'],
                            tooltip: lang['spravochnik_uslug'],
                            iconCls: 'services-complex16',
                            handler: function() {
                                getWnd('swUslugaTreeWindow').show({action: (isUserGroup('LpuUser') || isUserGroup('OrgUser'))?'view':''});
                            }
                        },{
							tooltip: lang['mkb-10'],
							text: lang['spravochnik_mkb-10'],
							iconCls: 'spr-mkb16',
							handler: function() {
								if ( !getWnd('swMkb10SearchWindow').isVisible() )
									getWnd('swMkb10SearchWindow').show();
							}
						},
                        {
							tooltip: lang['spravochnik'] + getMESAlias(),
							text: lang['spravochnik'] + getMESAlias(),
							iconCls: 'spr-mes16',
                            handler: function() {
								if ( !getWnd('swMesOldSearchWindow').isVisible() )
									getWnd('swMesOldSearchWindow').show();
                            }.createDelegate(this)
                        },
						sw.Promed.Actions.swDrugDocumentSprAction,
                        {
                            text: lang['mnn_vvod_latinskih_naimenovaniy'],
                            tooltip: lang['mnn_vvod_latinskih_naimenovaniy'],
                            iconCls : 'drug-viewmnn16',
                            handler: function() {
                                getWnd('swDrugMnnViewWindow').show({privilegeType: 'all',action: (isUserGroup('LpuUser') || isUserGroup('OrgUser'))?'view':''});
                            }
                        },
                        {
                            text: lang['torg_naim_vvod_latinskih_naimenovaniy'],
                            tooltip: lang['torg_naim_vvod_latinskih_naimenovaniy'],
                            iconCls : 'drug-viewtorg16',
                            handler: function() {
                                getWnd('swDrugTorgViewWindow').show({action: (isUserGroup('LpuUser') || isUserGroup('OrgUser'))?'view':''});
                            }
                        },
                        {
                            text: getRLSTitle(),
                            tooltip: getRLSTitle(),
                            iconCls: 'rls16',
                            handler: function()
                            {
                                getWnd('swRlsViewForm').show({action: (isUserGroup('LpuUser') || isUserGroup('OrgUser'))?'view':''});
                            },
                            hidden: false
                        },
                        {
                            text: lang['glossariy'],
                            tooltip: lang['glossariy'],
                            iconCls : 'glossary16',
                            handler: function()
                            {
                                getWnd('swGlossarySearchWindow').show();
                            }
                        }
                    ]
                })
            },
			action_PersDispSearchView: 
			{
				nn: 'action_PersDispSearchView',
				tooltip: lang['dispansernyiy_uchet'],
				text: WND_POL_PERSDISPSEARCHVIEW,
				iconCls : 'epl-ddisp-new32',
				disabled: false,
                menuAlign: 'tr?',
                menu: new Ext.menu.Menu({
                    items:[
                        {
                            text: lang['dispansernyie_kartyi_patsientov_poisk'],
                            tooltip: lang['dispansernyie_kartyi_patsientov_poisk'],
                            iconCls: 'disp-search16',
                            handler: function()
                            {
                                getWnd('swPersonDispSearchWindow').show({ARMType: 'stom'});
                            }
                        },
                        {
                            text: lang['dispansernyie_kartyi_patsientov_spisok'],
                            tooltip: lang['dispansernyie_kartyi_patsientov_spisok'],
                            iconCls: 'disp-view16',
                            handler: function()
                            {
                                var grid = Ext.getCmp('swWorkPlaceStomWindow').getGrid(),
                                    selected_record = grid.getSelectionModel().getSelected(),
                                    Person_id = (selected_record && selected_record.get('Person_id')) || null;
                                getWnd('swPersonDispViewWindow').show({mode: 'view', Person_id: Person_id, view_one_doctor: true, ARMType: 'stom'});
                            }
                        }
                    ]
                })

				/*handler: function()
				{
					var grid = Ext.getCmp('swWorkPlaceStomWindow').getGrid(),
						selected_record = grid.getSelectionModel().getSelected(),
						Person_id = (selected_record && selected_record.get('Person_id')) || null;
					getWnd('swPersonDispViewWindow').show({mode: 'view', Person_id: Person_id, view_one_doctor: true});
				}*/
			},
            action_DopDisp:
            {
                nn: 'action_DopDisp',
                hidden: getRegionNick().inlist(['by']),
                tooltip: lang['dispanserizatsiya'],
                text: lang['dispanserizatsiya'],
                iconCls : 'epl-ddisp-new32',
                disabled: false,
                menuAlign: 'tr',
                menu: new Ext.menu.Menu({
                    items: [{
                        text:lang['dispanserizatsiya_vzroslogo_naseleniya'],
                        iconCls: 'pol-dopdisp16',
                        hidden: false,
                        menu: new Ext.menu.Menu({
                            items: [{
                                text: lang['obsledovaniya_vov_poisk'],
                                tooltip: lang['obsledovaniya_vov_poisk'],
                                iconCls : 'dopdisp-search16',
                                handler: function() {
                                    getWnd('EvnPLWOWSearchWindow').show();
                                }
                            },
                                '-',
                                {
                                    text: (getRegionNick().inlist(['ufa','ekb','penza','astra'])) ? langs('Регистр инвалидов, подлежащих ДВН: Поиск') : langs('Регистр ВОВ: Поиск'),
                                    tooltip: (getRegionNick().inlist(['ufa','ekb','penza','astra'])) ? langs('Регистр инвалидов, подлежащих ДВН: Поиск') : langs('Регистр ВОВ: Поиск'),
                                    iconCls : 'dopdisp-search16',
                                    handler: function() {
                                        getWnd('swPersonPrivilegeWOWSearchWindow').show();
                                    }
                                }, {
                                    text: (getRegionNick().inlist(['ufa','ekb','penza','astra'])) ? langs('Регистр инвалидов, подлежащих ДВН: Поточный ввод') : langs('Регистр ВОВ: Поточный ввод'),
                                    tooltip: (getRegionNick().inlist(['ufa','ekb','penza','astra'])) ? langs('Регистр инвалидов, подлежащих ДВН: Поточный ввод') : langs('Регистр ВОВ: Поточный ввод'),
                                    iconCls : 'dopdisp-search16',
                                    handler: function() {
                                        getWnd('swPersonPrivilegeWOWStreamInputWindow').show();
                                    }
                                },
                                '-',
                                {
                                    text: MM_POL_PERSDDSEARCH,
                                    tooltip: lang['dopolnitelnaya_dispanserizatsiya_poisk'],
                                    iconCls : 'dopdisp-search16',
                                    handler: function() {
                                        getWnd('swPersonDopDispSearchWindow').show();
                                    }
                                },
                                '-',
                                {
                                    text: lang['talon_po_dopolnitelnoy_dispanserizatsii_vzroslyih_do_2013g_poisk'],
                                    tooltip: lang['talon_po_dopolnitelnoy_dispanserizatsii_vzroslyih_do_2013g_poisk'],
                                    iconCls : 'dopdisp-epl-search16',
                                    handler: function() {
                                        getWnd('swEvnPLDispDopSearchWindow').show();
                                    }
                                },
                                '-',
                                {
                                    text: MM_POL_EPLDD13SEARCH,
                                    tooltip: MM_POL_EPLDD13SEARCH,
                                    iconCls : 'dopdisp-epl-search16',
                                    handler: function() {
                                        getWnd('swEvnPLDispDop13SearchWindow').show();
                                    }
                                }, {
                                    text: MM_POL_EPLDD13SECONDSEARCH,
                                    tooltip: MM_POL_EPLDD13SECONDSEARCH,
                                    iconCls : 'dopdisp-epl-search16',
                                    handler: function() {
                                        getWnd('swEvnPLDispDop13SecondSearchWindow').show();
                                    }
                                }]
                        })
                    }, {
                        text:lang['profilakticheskie_osmotryi_vzroslyih'],
                        iconCls: 'pol-dopdisp16',
                        hidden: false,
                        menu: new Ext.menu.Menu({
                            items: [{
                                text: MM_POL_EPLDPSEARCH,
                                tooltip: MM_POL_EPLDPSEARCH,
                                iconCls : 'dopdisp-epl-search16',
                                handler: function() {
                                    getWnd('swEvnPLDispProfSearchWindow').show();
                                }
                            }]
                        })
                    }, {
                        text:lang['dispanserizatsiya_detey-sirot'],
                        iconCls: 'pol-dopdisp16',
                        hidden: false,
                        menu: new Ext.menu.Menu({
                            items: [{
                                text: lang['registr_detey-sirot_do_2013g_poisk'],
                                tooltip: lang['registr_detey-sirot_poisk'],
                                iconCls: 'orphdisp-search16',
                                handler: function() {
                                    getWnd('swPersonDispOrpSearchWindow').show();
                                }
                            }, {
                                text: lang['talon_po_dispanserizatsii_detey-sirot_do_2013g_poisk'],
                                tooltip: lang['talon_po_dispanserizatsii_detey-sirot_poisk'],
                                iconCls: 'orphdisp-epl-search16',
                                handler: function() {
                                    getWnd('swEvnPLDispOrpSearchWindow').show();
                                }
                            },
                                '-',
                                {
                                    text: lang['registr_detey-sirot_s_2013g_poisk'],
                                    tooltip: lang['registr_detey-sirot_s_2013g_poisk'],
                                    iconCls : 'dopdisp-search16',
                                    handler: function() {
                                        getWnd('swPersonDispOrp13SearchWindow').show({
                                            CategoryChildType: 'orp'
                                        });
                                    }
                                }, {
                                    text: lang['registr_detey-sirot_usyinovlennyih_poisk'],
                                    tooltip: lang['registr_detey-sirot_usyinovlennyih_poisk'],
                                    iconCls : 'dopdisp-search16',
                                    handler: function() {
                                        getWnd('swPersonDispOrp13SearchWindow').show({
                                            CategoryChildType: 'orpadopted'
                                        });
                                    }
                                }, {
                                    text: lang['karta_dispanserizatsii_nesovershennoletnego_-_1_etap_poisk'],
                                    tooltip: lang['karta_dispanserizatsii_nesovershennoletnego_-_1_etap_poisk'],
                                    iconCls : 'dopdisp-epl-search16',
                                    handler: function() {
                                        getWnd('swEvnPLDispOrp13SearchWindow').show({
                                            stage: 1
                                        });
                                    }
                                }, {
                                    text: lang['karta_dispanserizatsii_nesovershennoletnego_-_2_etap_poisk'],
                                    tooltip: lang['karta_dispanserizatsii_nesovershennoletnego_-_2_etap_poisk'],
                                    iconCls : 'dopdisp-epl-search16',
                                    handler: function() {
                                        getWnd('swEvnPLDispOrp13SearchWindow').show({
                                            stage: 2
                                        });
                                    }
                                },
                                '-',
                                {
                                    text: lang['eksport_kart_po_dispanserizatsii_nesovershennoletnih'],
                                    tooltip: lang['eksport_kart_po_dispanserizatsii_nesovershennoletnih'],
                                    iconCls : 'database-export16',
                                    handler: function() {
                                        getWnd('swEvnPLDispTeenExportWindow').show();
                                    }
                                }]
                        })
                    }, {
                        text:lang['meditsinskie_osmotryi_nesovershennoletnih'],
                        iconCls: 'pol-dopdisp16',
                        hidden: false,
                        menu: new Ext.menu.Menu({
                            items: [{
                                text: lang['registr_periodicheskih_osmotrov_nesovershennoletnih_poisk'],
                                tooltip: lang['registr_periodicheskih_osmotrov_nesovershennoletnih_poisk'],
                                iconCls : 'dopdisp-search16',
                                hidden: getRegionNick().inlist(['adygeya', 'yakutiya']),
                                handler: function() {
                                    getWnd('swPersonDispOrpPeriodSearchWindow').show();
                                }
                            }, {
                                text: lang['periodicheskie_osmotryi_nesovershennoletnih_poisk'],
                                tooltip: lang['periodicheskie_osmotryi_nesovershennoletnih_poisk'],
                                iconCls : 'dopdisp-epl-search16',
                                hidden: getRegionNick().inlist(['adygeya', 'yakutiya']),
                                handler: function() {
                                    getWnd('swEvnPLDispTeenInspectionSearchWindow').show();
                                }
                            },
                                '-',
                                {
                                    text: lang['napravleniya_na_profilakticheskie_osmotryi_nesovershennoletnih_poisk'],
                                    tooltip: lang['napravleniya_na_profilakticheskie_osmotryi_nesovershennoletnih_poisk'],
                                    iconCls : 'dopdisp-search16',
                                    handler: function() {
                                        getWnd('swPersonDispOrpProfSearchWindow').show();
                                    }
                                }, {
                                    text: lang['profilakticheskie_osmotryi_nesovershennoletnih_-_1_etap_poisk'],
                                    tooltip: lang['profilakticheskie_osmotryi_nesovershennoletnih_-_1_etap_poisk'],
                                    iconCls : 'dopdisp-epl-search16',
                                    handler: function() {
                                        getWnd('swEvnPLDispTeenInspectionProfSearchWindow').show();
                                    }
                                }, {
                                    text: lang['profilakticheskie_osmotryi_nesovershennoletnih_-_2_etap_poisk'],
                                    tooltip: lang['profilakticheskie_osmotryi_nesovershennoletnih_-_2_etap_poisk'],
                                    iconCls : 'dopdisp-epl-search16',
                                    handler: function() {
                                        getWnd('swEvnPLDispTeenInspectionProfSecSearchWindow').show();
                                    }
                                },
                                '-',
                                {
                                    text: lang['napravleniya_na_predvaritelnyie_osmotryi_nesovershennoletnih_poisk'],
                                    tooltip: lang['napravleniya_na_predvaritelnyie_osmotryi_nesovershennoletnih_poisk'],
                                    iconCls : 'dopdisp-search16',
                                    hidden: getRegionNick().inlist(['adygeya', 'yakutiya']),
                                    handler: function() {
                                        getWnd('swPersonDispOrpPredSearchWindow').show();
                                    }
                                }, {
                                    text: lang['predvaritelnyie_osmotryi_nesovershennoletnih_-_1_etap_poisk'],
                                    tooltip: lang['predvaritelnyie_osmotryi_nesovershennoletnih_-_1_etap_poisk'],
                                    iconCls : 'dopdisp-epl-search16',
                                    hidden: getRegionNick().inlist(['adygeya', 'yakutiya']),
                                    handler: function() {
                                        getWnd('swEvnPLDispTeenInspectionPredSearchWindow').show();
                                    }
                                }, {
                                    text: lang['predvaritelnyie_osmotryi_nesovershennoletnih_-_2_etap_poisk'],
                                    tooltip: lang['predvaritelnyie_osmotryi_nesovershennoletnih_-_2_etap_poisk'],
                                    iconCls : 'dopdisp-epl-search16',
                                    hidden: getRegionNick().inlist(['adygeya', 'yakutiya']),
                                    handler: function() {
                                        getWnd('swEvnPLDispTeenInspectionPredSecSearchWindow').show();
                                    }
                                }]
                        })
                    }, {
                        text:lang['dispanserizatsiya_podrostki_14ti_let'],
                        iconCls: 'pol-dopdisp16',
                        hidden: false,
                        menu: new Ext.menu.Menu({
                            items: [{
                                text: lang['dispanserizatsiya_14-letnih_podrostkov_poisk'],
                                tooltip: lang['dispanserizatsiya_14-letnih_podrostkov_poisk'],
                                iconCls : 'dopdisp-teens-search16',
                                handler: function() {
                                    getWnd('swEvnPLDispTeen14SearchWindow').show();
                                }
                            }]
                        })
                    }]
                })
            },
            action_DispPlan: 
			{
				nn: 'action_DispPlan',
				text: langs('Планирование диспансеризации'),
				tooltip: langs('Планирование диспансеризации'),
				hidden: getRegionNick() != 'penza',
				iconCls: 'worksheets32',
				handler: function ()
				{
					var phpSessId = window.getCookie('PHPSESSID');
					var url = DISP_PLANNING_GIT + '?PHPSESSID=' + phpSessId;
					window.open(url);
				}.createDelegate(this)
			},
            /*action_SromSearch:
            {
                nn: 'action_SromSearch',
                tooltip: lang['poisk_tap_po_stomatologii'],
                text: lang['poisk_tap_po_stomatologii'],
                iconCls : 'eph-tooth32',
                disabled: false,
                handler: function()
                {
                    getWnd('swEvnPLStomSearchWindow').show();
                }
            },
            action_SromStream:
            {
                nn: 'action_SromStream',
                tooltip: lang['potochnyiy_vvod_tap_po_stomatologii'],
                text: lang['potochnyiy_vvod_tap_po_stomatologii'],
                iconCls : 'eph-tooth32',
                disabled: false,
                handler: function()
                {
                    getWnd('swEvnPLStomStreamInputWindow').show();
                }
            },*/
            action_Stom:{
                text:lang['tap_po_stomatologii'],
                tooltip: lang['tap_po_stomatologii'],
                iconCls: 'eph-tooth32',
                hidden: false,
                menu: new Ext.menu.Menu({
                    items:[
                        {
                            tooltip: lang['poisk_tap_po_stomatologi'],
                            text: lang['poisk_tap_po_stomatologi'],
                            iconCls : 'eph-tooth16',
                            disabled: false,
                            handler: function()
                            {
                                getWnd('swEvnPLStomSearchWindow').show();
                            }
                        },
                        {
                            tooltip: lang['potochnyiy_vvod_tap_po_stomatologii'],
                            text: lang['potochnyiy_vvod_tap_po_stomatologii'],
                            iconCls : 'eph-tooth16',
                            disabled: false,
                            handler: function()
                            {
                                getWnd('swEvnPLStomStreamInputWindow').show();
                            }
                        }
                    ]
                })
            },
			action_PathoMorph:
			{
				nn: 'action_PathoMorph',
				tooltip: 'Патоморфология',
				text: 'Патоморфология',
				iconCls: 'pathomorph-32',
				disabled: false, 
				menuAlign: 'tr?',
				menu: new Ext.menu.Menu({
					items: [
						sw.Promed.Actions.EvnDirectionHistologicViewAction,
						sw.Promed.Actions.EvnHistologicProtoViewAction,
						'-',
						sw.Promed.Actions.EvnDirectionMorfoHistologicViewAction,
						sw.Promed.Actions.EvnMorfoHistologicProtoViewAction
					]
				})
			},
            action_PersonSearch:
            {
                nn: 'action_PersonSearch',
                tooltip: lang['poisk_patsienta'],
                text: lang['poisk_patsienta'],
                iconCls : 'patient-search32',
                disabled: false,
                handler: function()
                {
					getWnd('swPersonSearchWindow').show({
						onSelect: function(person_data) {
							getWnd('swPersonEditWindow').show({
								Person_id: person_data.Person_id,
								Server_id: person_data.Server_id
							});
						},
						searchMode: 'all'
					});
                }
            },
			action_PersCardSearch: 
			{
				nn: 'action_PersCardSearch',
				tooltip: WND_POL_PERSCARDSEARCH,
				text: WND_POL_PERSCARDSEARCH,
				iconCls : 'mp-region32',
				disabled: false, 
				handler: function() 
				{
					form.getAttachDataShowWindow('swPersonCardSearchWindow');
				}
			},
			action_EvnVKJournal:
			{
				nn: 'action_EvnVKJournal',
				tooltip: lang['otkryit_jurnal_vk'],
				text: lang['jurnal_vk'],
				iconCls : 'vk-journal32',
				handler: function()
				{
					if ( getWnd('swClinExWorkSearchWindow').isVisible() ) {
						sw.swMsg.show({
							buttons: Ext.Msg.OK,
							fn: Ext.emptyFn,
							icon: Ext.Msg.WARNING,
							msg: lang['okno_uje_otkryito'],
							title: ERR_WND_TIT
						});
						return false;
					}
					getWnd('swClinExWorkSearchWindow').show({mode: 'view'});
				}
			},
			action_EvnPLSearch:
			{
				nn: 'action_EvnPLSearch',
				tooltip: lang['otkryit_poisk_po_poliklinike'],
				text: lang['poisk_po_poliklinike'],
				iconCls : 'test16',
				handler: function()
				{
					if ( getWnd('swEvnPLSearchWindow').isVisible() ) {
						sw.swMsg.show({
							buttons: Ext.Msg.OK,
							fn: Ext.emptyFn,
							icon: Ext.Msg.WARNING,
							msg: lang['okno_uje_otkryito'],
							title: ERR_WND_TIT
						});
						return false;
					}
					getWnd('swEvnPLSearchWindow').show();
				}
			},
			action_DirectionPerson:
			{
				nn: 'action_DirectionPerson',
				tooltip: lang['napravit_patsienta_k_vrachu'],
				text: lang['zapisat_k_vrachu'],
				iconCls : 'vk-prescr32',
				handler: function()
				{
					// запись пациента к себе или другому врачу с выпиской электр.направления
					var grid = this.getGrid(),
						selected_record = grid.getSelectionModel().getSelected(),
						onHide = function(){
							if ( selected_record ) {
								grid.getView().focusRow(grid.getStore().indexOf(selected_record));
							} else {
								//grid.getView().focusRow(0);
								grid.getSelectionModel().selectFirstRow();
							}
						},
						openDirectionWindow = function(personData) {
							var win = 'swDirectionMasterWindow'; 
							var my_params = new Object({
								userMedStaffFact: this.userMedStaffFact
								,personData: personData
								,directionData: {
									LpuUnitType_SysNick: null
									,EvnQueue_id: null
									,QueueFailCause_id: null
									,Lpu_did: null // ЛПУ куда направляем
									,LpuUnit_did: null
									,LpuSection_did: null
									,EvnUsluga_id: null
									,LpuSection_id: null
									,EvnDirection_pid: null
									,EvnPrescr_id: null
									,PrescriptionType_Code: null
									,DirType_id: null
									,LpuSectionProfile_id: null
									,Diag_id: null
									,MedService_id: this.userMedStaffFact.MedService_id
									,MedStaffFact_id: null
									,MedPersonal_id: this.userMedStaffFact.MedPersonal_id
									,MedPersonal_did: null
								}
								/*
								,DirType_id:
								,DirType_Name:
								*/
								,onHide: onHide
								,onDirection: function(){
									getWnd(win).hide();
								}
							});
							
							if ( getWnd(win).isVisible() ) {
								getWnd(win).hide()
							}
							getWnd(win).show(my_params);
						}.createDelegate(this);

					if ( selected_record && selected_record.get('Person_id') ) {
						// человек выбран
						var person_fio = selected_record.get('Person_FIO').split(' ');
						var personData = {
							PersonEvn_id: selected_record.get('PersonEvn_id'),
							Person_id: selected_record.get('Person_id'),
							Server_id: selected_record.get('Server_id'),
							Person_Surname: person_fio[0] || '',
							Person_Firname: person_fio[1] || '',
							Person_Secname: person_fio[2] || '',
							Person_Birthday: selected_record.get('Person_BirthDay')
						};
						
						checkPersonDead({
							Person_id: personData.Person_id,
							onIsLiving: function() {
								openDirectionWindow(personData);
							},
							onIsDead: function(res) {
								sw.swMsg.alert(lang['oshibka'], lang['napravit_patsienta_k_vrachu_nevozmojno_v_svyazi_so_smertyu_patsienta']);
							}
						});
					} else {
						// человек не выбран
						if ( getWnd('swPersonSearchWindow').isVisible() ) {
							getWnd('swPersonSearchWindow').hide()
						}
						getWnd('swPersonSearchWindow').show({
							onClose: onHide,
							onSelect: function(pdata) 
							{
								checkPersonDead({
									Person_id: pdata.Person_id,
									onIsLiving: function() {
										getWnd('swPersonSearchWindow').hide();
										openDirectionWindow(pdata);
									},
									onIsDead: function(res) {
										sw.swMsg.alert(lang['oshibka'], lang['napravit_patsienta_k_vrachu_nevozmojno_v_svyazi_so_smertyu_patsienta']);
									}
								});
							},
							searchMode: 'all'
						});
					}

				}.createDelegate(this)
			},
			action_Notify: 
			{
				nn: 'action_Notify',
				tooltip: lang['izvescheniya_napravleniya'],
				text: lang['izvescheniya_napravleniya'],
				iconCls : 'doc-notify32',
				disabled: false,
				menuAlign: 'tr?',
				menu: new Ext.menu.Menu({
					items: [
						//swPromedActions.EvnInfectNotify,
						swPromedActions.EvnNotifyHepatitis,
						swPromedActions.EvnOnkoNotify,
						swPromedActions.EvnNotifyOrphan,
						swPromedActions.EvnNotifyCrazy,
						swPromedActions.EvnNotifyNarko,
						swPromedActions.EvnNotifyTub,
						swPromedActions.EvnNotifyVener,
						swPromedActions.EvnNotifyHIV
					]
				})
			},
			action_Register: 
			{
				nn: 'action_Register',
				tooltip: lang['registryi'],
				text: lang['registryi'],
				iconCls : 'registry32',
				disabled: false,
				menuAlign: 'tr?',
				menu: new Ext.menu.Menu({
					items: [
						swPromedActions.PersonDispOrpSearch,
						swPromedActions.PersonPrivilegeWOWSearch,
						swPromedActions.PersonDopDispSearch,
						swPromedActions.EvnPLDispTeen14Search,
						swPromedActions.HepatitisRegistry,
						swPromedActions.OnkoRegistry,
						swPromedActions.OrphanRegistry,
						swPromedActions.CrazyRegistry,
						swPromedActions.NarkoRegistry,
						swPromedActions.TubRegistry,
						swPromedActions.VenerRegistry,
						swPromedActions.HIVRegistry
					]
				})
			},
            actions_settings: {
                nn: 'actions_settings',
                iconCls: 'settings32',
                text: lang['servis'],
                tooltip: lang['servis'],
                listeners: {
                    'click': function(){
                        var menu = Ext.menu.MenuMgr.get('wpsw_menu_windows');
                        menu.removeAll();
                        var number = 1;
                        Ext.WindowMgr.each(function(wnd){
                            if ( wnd.isVisible() )
                            {
                                if ( Ext.WindowMgr.getActive().id == wnd.id )
                                {
                                    menu.add(new Ext.menu.Item(
                                        {
                                            text: number + ". " + wnd.title,
                                            iconCls : 'checked16',
                                            checked: true,
                                            handler: function()
                                            {
                                                Ext.getCmp(wnd.id).toFront();
                                            }
                                        })
                                    );
                                    number++;
                                }
                                else
                                {
                                    menu.add(new Ext.menu.Item(
                                        {
                                            text: number + ". " + wnd.title,
                                            iconCls : 'x-btn-text',
                                            handler: function()
                                            {
                                                Ext.getCmp(wnd.id).toFront();
                                            }
                                        })
                                    );
                                    number++;
                                }
                            }
                        });
                        if ( menu.items.getCount() == 0 )
                            menu.add({
                                text: lang['otkryityih_okon_net'],
                                iconCls : 'x-btn-text',
                                handler: function()
                                {
                                }
                            });
                        else
                        {
                            menu.add(new Ext.menu.Separator());
                            menu.add(new Ext.menu.Item(
                                {
                                    text: lang['zakryit_vse_okna'],
                                    iconCls : 'close16',
                                    handler: function()
                                    {
                                        Ext.WindowMgr.each(function(wnd){
                                            if ( wnd.isVisible() )
                                            {
                                                wnd.hide();
                                            }
                                        });
                                    }
                                })
                            );
                        }
                    }
                },
                menu: new Ext.menu.Menu({
                    items: [
                        {
                            nn: 'action_UserProfile',
                            text: lang['moy_profil'],
                            tooltip: lang['profil_polzovatelya'],
                            iconCls : 'user16',
                            hidden: false,
                            handler: function()
                            {
                                args = {};
                                args.action = 'edit';
                                getWnd('swUserProfileEditWindow').show(args);
                            }
                        },
                        {
                            nn: 'action_settings',
                            text: lang['nastroyki'],
                            tooltip: lang['prosmotr_i_redaktirovanie_nastroek'],
                            iconCls : 'settings16',
                            handler: function()
                            {
                                getWnd('swOptionsWindow').show();
                            }
                        },
                        {
                            nn: 'action_selectMO',
                            text: lang['vyibor_mo'],
                            tooltip: lang['vyibor_mo'],
                            hidden: !isSuperAdmin(),
                            iconCls: 'lpu-select16',
                            handler: function()
                            {
                                Ext.WindowMgr.each(function(wnd){
                                    if ( wnd.isVisible() )
                                    {
                                        wnd.hide();
                                    }
                                });
                                getWnd('swSelectLpuWindow').show({});
                            }
                        },
                        {
                            text:lang['pomosch'],
                            nn: 'action_help',
                            iconCls: 'help16',
                            menu: new Ext.menu.Menu(
                                {
                                    //plain: true,
                                    id: 'menu_help',
                                    items:
                                        [
                                            {
                                                text: lang['vyizov_spravki'],
                                                tooltip: lang['pomosch_po_programme'],
                                                iconCls : 'help16',
                                                handler: function()
                                                {
                                                    ShowHelp(lang['soderjanie']);
                                                }
                                            },
                                            {
                                                text: lang['forum_podderjki'],
                                                iconCls: 'support16',
                                                xtype: 'tbbutton',
                                                handler: function() {
                                                    window.open(ForumLink);
                                                }
                                            },
                                            {
                                                text: lang['o_programme'],
                                                tooltip: lang['informatsiya_o_programme'],
                                                iconCls : 'promed16',
                                                testId: 'mainmenu_help_about',
                                                handler: function()
                                                {
                                                    getWnd('swAboutWindow').show();
                                                }
                                            }
                                        ]
                                }),
                            tabIndex: -1
                        },
                        {
                            //text: 'Информация о пользователе',
                            text: lang['dannyie_ob_uchetnoy_zapisi_polzovatelya'],
                            nn: 'action_user_about',
                            iconCls: 'user16',
                            menu: new Ext.menu.Menu(
                                {
                                    //plain: true,
                                    id: 'user_menu',
                                    items:
                                        [
                                            {
                                                disabled: true,
                                                iconCls: 'user16',
                                                text: '<b>Информация о пользователе</b><br/>'+'Имя : '+UserName+'<br/>'+'E-mail : '+UserEmail+'<br/>'+'Описание : '+UserDescr+'<br/>'+'МО : '+Ext.globalOptions.globals.lpu_nick,
                                                xtype: 'tbtext'
                                            }
                                        ]
                                })
                        },
                        {
                            text: lang['okna'],
                            nn: 'action_windows',
                            iconCls: 'windows16',
                            listeners: {
                                'click': function(e) {
                                    var menu = Ext.menu.MenuMgr.get('wpsw_menu_windows');
                                    menu.removeAll();
                                    var number = 1;
                                    Ext.WindowMgr.each(function(wnd){
                                        if ( wnd.isVisible() )
                                        {
                                            if ( Ext.WindowMgr.getActive().id == wnd.id )
                                            {
                                                menu.add(new Ext.menu.Item(
                                                    {
                                                        text: number + ". " + wnd.title,
                                                        iconCls : 'checked16',
                                                        checked: true,
                                                        handler: function()
                                                        {
                                                            Ext.getCmp(wnd.id).toFront();
                                                        }
                                                    })
                                                );
                                                number++;
                                            }
                                            else
                                            {
                                                menu.add(new Ext.menu.Item(
                                                    {
                                                        text: number + ". " + wnd.title,
                                                        iconCls : 'x-btn-text',
                                                        handler: function()
                                                        {
                                                            Ext.getCmp(wnd.id).toFront();
                                                        }
                                                    })
                                                );
                                                number++;
                                            }
                                        }
                                    });
                                    if ( menu.items.getCount() == 0 )
                                        menu.add({
                                            text: lang['otkryityih_okon_net'],
                                            iconCls : 'x-btn-text',
                                            handler: function()
                                            {
                                            }
                                        });
                                    else
                                    {
                                        menu.add(new Ext.menu.Separator());
                                        menu.add(new Ext.menu.Item(
                                            {
                                                text: lang['zakryit_vse_okna'],
                                                iconCls : 'close16',
                                                handler: function()
                                                {
                                                    Ext.WindowMgr.each(function(wnd){
                                                        if ( wnd.isVisible() )
                                                        {
                                                            wnd.hide();
                                                        }
                                                    });
                                                }
                                            })
                                        );
                                    }
                                },
                                'mouseover': function() {
                                    var menu = Ext.menu.MenuMgr.get('wpsw_menu_windows');
                                    menu.removeAll();
                                    var number = 1;
                                    Ext.WindowMgr.each(function(wnd){
                                        if ( wnd.isVisible() )
                                        {
                                            if ( Ext.WindowMgr.getActive().id == wnd.id )
                                            {
                                                menu.add(new Ext.menu.Item(
                                                    {
                                                        text: number + ". " + wnd.title,
                                                        iconCls : 'checked16',
                                                        checked: true,
                                                        handler: function()
                                                        {
                                                            Ext.getCmp(wnd.id).toFront();
                                                        }
                                                    })
                                                );
                                                number++;
                                            }
                                            else
                                            {
                                                menu.add(new Ext.menu.Item(
                                                    {
                                                        text: number + ". " + wnd.title,
                                                        iconCls : 'x-btn-text',
                                                        handler: function()
                                                        {
                                                            Ext.getCmp(wnd.id).toFront();
                                                        }
                                                    })
                                                );
                                                number++;
                                            }
                                        }
                                    });
                                    if ( menu.items.getCount() == 0 )
                                        menu.add({
                                            text: lang['otkryityih_okon_net'],
                                            iconCls : 'x-btn-text',
                                            handler: function()
                                            {
                                            }
                                        });
                                    else
                                    {
                                        menu.add(new Ext.menu.Separator());
                                        menu.add(new Ext.menu.Item(
                                            {
                                                text: lang['zakryit_vse_okna'],
                                                iconCls : 'close16',
                                                handler: function()
                                                {
                                                    Ext.WindowMgr.each(function(wnd){
                                                        if ( wnd.isVisible() )
                                                        {
                                                            wnd.hide();
                                                        }
                                                    });
                                                }
                                            })
                                        );
                                    }
                                }
                            },
                            menu: new Ext.menu.Menu(
                                {
                                    //plain: true,
                                    id: 'wpsw_menu_windows',
                                    items: [
                                        '-'
                                    ]
                                }),
                            tabIndex: -1
                        }/*,
                        {
                            nn: 'action_exit',
                            text:lang['vyihod'],
                            iconCls: 'exit16',
                            handler: function()
                            {
                                sw.swMsg.show({
                                    title: lang['podtverdite_vyihod'],
                                    msg: lang['vyi_deystvitelno_hotite_vyiyti'],
                                    buttons: Ext.Msg.YESNO,
                                    fn: function ( buttonId ) {
                                        if ( buttonId == 'yes' ) {
                                            window.onbeforeunload = null;
                                            window.location=C_LOGOUT;
                                        }
                                    }
                                });
                            }
                        }*/
                    ]
                })
            },
			action_JourNotice: {
				handler: function() {
					getWnd('swMessagesViewWindow').show();
				}.createDelegate(this),
				iconCls: 'notice32',
				nn: 'action_JourNotice',
				text: lang['jurnal_uvedomleniy'],
				tooltip: lang['jurnal_uvedomleniy']
			},
			action_FindRegions: {
				handler: function() {
					getWnd('swFindRegionsWindow').show();
				},
				iconCls : 'mp-region32',
				nn: 'action_FindRegions',
				text: WND_FRW,
				tooltip: lang['poisk_uchastkov_i_vrachey_po_adresu']
			},
			action_Templ: {
                handler: function() {
                    var params = {
                        EvnClass_id: 13,
                        XmlType_id: 3,
                        allowSelectXmlType: true,
                        LpuSection_id: form.userMedStaffFact.LpuSection_id,
                        MedPersonal_id: form.userMedStaffFact.MedPersonal_id,
                        MedStaffFact_id: form.userMedStaffFact.MedStaffFact_id
                    };
                    getWnd('swTemplSearchWindow').show(params);
                },
				iconCls : 'document32',
                nn: 'action_Templ',
                text: lang['shablonyi_dokumentov'],
                tooltip: lang['shablonyi_dokumentov']
            },			
			action_reports: //http://redmine.swan.perm.ru/issues/18509
			{
				nn: 'action_Report',
				tooltip: lang['prosmotr_otchetov'],
				text: lang['prosmotr_otchetov'],
				iconCls: 'report32',
				//hidden: !this.enableDefaultActions,//( !document.getElementById('swWorkPlaceCallCenterWindow') || !this.enableDefaultActions ),
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
			}
		};
		// Копируем все действия для создания панели кнопок
		form.PanelActions = {};
		for(var key in configActions)
		{
			// width:'265',minWidth:'265', style: {width: '100%'}, 
			var iconCls = configActions[key].iconCls.replace(/16/g, '32');
			var z = Ext.applyIf({cls: 'x-btn-large', iconCls: iconCls, text: ''}, configActions[key]);
			this.PanelActions[key] = new Ext.Action(z);
		}
		var actions_list = ['action_Turn','action_PersDispSearchView','action_MidMedPersonal','action_directories','action_DopDisp','action_Stom','action_PathoMorph','action_PersonSearch','action_PersCardSearch','actions_settings',
            'action_Timetable', //'action_Notify','action_Register',
            'action_JourNotice', 'action_FindRegions', 'action_Templ', 'action_reports'];
		if ((getGlobalOptions().region) && (getGlobalOptions().region.nick == 'pskov')) {
			actions_list.push('action_DirectionPerson');
		}

		if(getRegionNick() == 'penza') {
			actions_list.push('action_DispPlan');
		}
		// Создание кнопок для панели
		form.BtnActions = [];
		i = 0;
		for(key in form.PanelActions)
		{
			if (key.inlist(actions_list))
			{
				form.BtnActions.push(new Ext.Button(form.PanelActions[key]));
				i++;
			}
		}
		this.leftMenu = new Ext.Panel(
		{
			region: 'center',
			border: false,
			id: form.id + '_hhd',
			layout:'form',
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
			width: 60,
			minSize: 60,
			maxSize: 120,
			id: 'wpsfLeftPanel',
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
			border: true,
			title: ' ',
			items: [
				new Ext.Button(
				{	
					cls:'upbuttonArr',
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
				style:{width:'48px'},
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

		this.ElectronicQueuePanel = new sw.Promed.ElectronicQueuePanel({
			ownerWindow: wnd,
			ownerGrid: wnd.ScheduleGrid, // передаем грид для работы с ЭО
			storeInitObject: wnd.storeInitObject, // передаем параметры для конструктора стора
			gridRefreshFn: function(options){ wnd.scheduleRefresh(options) }, // передаем метод обновления грида
			applyCallActionFn: function(){ wnd.scheduleOpen() }, // передаем метод открытия эмки
			layoutPanelId: 'wpsfSchedulePanel', // лэйаут для перерисовки
			region: 'south',
		});
		
		Ext.apply(this, 
		{
			layout: 'border',
			items: 
			[
				this.TopPanel,
				this.leftPanel,
				{
					layout: 'border',
					region: 'center',
					id: 'wpsfSchedulePanel',
					items:
					[
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
		sw.Promed.swWorkPlaceStomWindow.superclass.initComponent.apply(this, arguments);
	}
});
