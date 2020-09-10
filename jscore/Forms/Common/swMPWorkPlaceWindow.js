/**
 * swMPWorkPlaceWindow - окно рабочего места врача
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

sw.Promed.swMPWorkPlaceWindow = Ext.extend(sw.Promed.BaseForm,
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
	id: 'swMPWorkPlaceWindow',
	readOnly: false,
	listeners: {
		activate: function(){
			sw.Applets.uec.startUecReader({callback: this.getDataFromUec.createDelegate(this)});
			sw.Applets.bdz.startBdzReader({callback: this.getDataFromBdz.createDelegate(this)});
			sw.Applets.BarcodeScaner.startBarcodeScaner({callback: this.getDataFromBarScan.createDelegate(this), ARMType: 'common'});
			this.startHomeVisitCounter();
		},
		deactivate: function() {
			sw.Applets.uec.stopUecReader();
			sw.Applets.bdz.stopBdzReader();
			sw.Applets.BarcodeScaner.stopBarcodeScaner();
			this.stopHomeVisitCounter();
		},
		beforeshow: function() {
			if ((!getGlobalOptions().medstafffact) || (getGlobalOptions().medstafffact.length==0))
			{
				Ext.Msg.alert(lang['soobschenie'], lang['tekuschiy_login_ne_sootnesen_s_vrachom_dostup_k_interfeysu_vracha_nevozmojen']);
				return false;
			}
		},
		hide: function() {
			if(getRegionNick() != 'kz' && this.VideoChatBtn) {
				this.VideoChatBtn.hide();
			}
		}
	},
	getDataFromBarScan: function(person_data) {
		var _this = this,
			form = Ext.getCmp('swMPWorkPlaceWindow');

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
			var form = Ext.getCmp('swMPWorkPlaceWindow'),
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
						Ext.Msg.alert(lang['soobschenie'], lang['patsient']+ response_obj.result['Person_FIO'] +' ('
							+ response_obj.result['Person_BirthDay'] +' '+lang['g_r_vozrast']+
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
						Ext.Msg.alert(lang['soobschenie'], lang['patsient']+ response_obj.result['Person_FIO'] +' ('
							+ response_obj.result['Person_BirthDay'] +' '+lang['g_r_vozrast']+
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
	getHomeVisitCount: function(callback) {
		if (!this.userMedStaffFact) {
			return;
		}

		var params = {
			MedPersonal_id: this.userMedStaffFact.MedPersonal_id,
			date: new Date().format('d.m.Y')
		};

		Ext.Ajax.request({
			url: '/?c=HomeVisit&m=getHomeVisitCount',
			params: params,
			success: function(response) {
				var response_obj = Ext.util.JSON.decode(response.responseText);

				if (!Ext.isEmpty(response_obj.HomeVisitCount)) {
					callback(response_obj.HomeVisitCount);
				}
			}
		});
	},
	refreshHomeVisitCount: function() {
		var button = this.leftMenu.items.find(function(item){return item.nn == 'action_HomeVisitJournal';});
		var tpl = new Ext.XTemplate('<div class="textOnPromedAction">{value}</div>');

		this.getHomeVisitCount(function(HomeVisitCount) {
			button.setText(tpl.apply({value: HomeVisitCount}));
		});
	},
	startHomeVisitCounter: function() {
		var wnd = this;
		var delay = (getRegionNick() == 'kareliya' ? 60 : 600); //Интервал в секундах

		if (wnd.homeVisitCounterId) {
			wnd.stopHomeVisitCounter();
		}

		wnd.homeVisitCounterId = setInterval(function(){
			wnd.refreshHomeVisitCount();
		}, delay*1000);
		wnd.refreshHomeVisitCount();
	},
	stopHomeVisitCounter: function() {
		if (this.homeVisitCounterId) {
			clearInterval(this.homeVisitCounterId);
			this.homeVisitCounterId = null;
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

            this.getLoadMask(lang['podojdite_sohranyaetsya_zapis']).show();
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
                                    title: lang['vnimanie'],
                                    buttons: Ext.Msg.YESNO,
                                    fn: function(buttonId, text, obj)
                                    {
                                        if ('yes' == buttonId)
                                        {
                                            var form = Ext.getCmp('swMPWorkPlaceWindow');
                                            var data =
                                            {
                                                Person_id: response_text.Person_id,
                                                Server_id: response_text.Server_id,
                                                PersonEvn_id: response_text .PersonEvn_id,
                                                OverrideWarning: 1
                                            };

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
	showDrugRequestEditForm: function()
	{
		var grid = this.getGrid(),
			selected_record = grid.getSelectionModel().getSelected(),
			Person_id = (selected_record && selected_record.get('Person_id')) || null;
		/*
		Получаем данные:
		является ли текущий пользователь врачом ЛЛО
		заявка на последний период, из имеющихся в системе или последняя имеющияся заявка.
		*/
		var params = {
			LpuSection_id: this.userMedStaffFact.LpuSection_id,
			MedPersonal_id: this.userMedStaffFact.MedPersonal_id
		};
		this.getLoadMask(LOAD_WAIT).show();
		Ext.Ajax.request(
		{
			url: '/?c=DrugRequest&m=getDrugRequestLast',
			params: params,
			callback: function(options, success, response) 
			{
				this.getLoadMask().hide();
				var response_obj = Ext.util.JSON.decode(response.responseText);
				if (response_obj.length == 0)
				{
					return false;
				}
				var row = response_obj[0];
				if (response_obj.length > 1)
				{
					for(var i in response_obj) {
						if(response_obj[i].next_DrugRequest_id > 0) {
							row = response_obj[i];
							break;
						}
					}
				}
				//log(row);
				if (row.is_dlo != 1)
				{
					Ext.Msg.alert(lang['soobschenie'], lang['vrach_ne_imeet_prava_na_vyipisku_retseptov_po_llo']);
					return false;
				}
				params.owner = this;
				params.Person_id = Person_id;
				if (row.next_DrugRequest_id > 0)
				{
					params.action = 'edit';
					params.DrugRequest_id = row.next_DrugRequest_id;
					params.DrugRequestStatus_id = row.next_DrugRequestStatus_id;
					params.DrugRequestPeriod_id = row.next_DrugRequestPeriod_id;
					getWnd('swNewDrugRequestEditForm').show(params);
				}
				else
				{
					sw.swMsg.show(
					{
						icon: Ext.MessageBox.QUESTION,
						msg: lang['na_sleduyuschiy_period']+ (row.next_DrugRequestPeriod) +' '+lang['zayavka_po_vrachu']+' '+ (row.MedPersonal_Fin) +' '+lang['ne_naydena_otkryit_poslednyuyu_imeyuschuyusya_ili_sozdat'],
						title: lang['zayavka_ne_naydena'],
						buttons: {yes: lang['otkryit'], no: lang['sozdat'], cancel: lang['otmena']},
						fn: function(buttonId, text, obj)
						{
							if ('yes' == buttonId)
							{
								if(row.last_DrugRequest_id > 0)
								{
									params.action = 'edit';
									params.DrugRequest_id = row.last_DrugRequest_id;
									params.DrugRequestStatus_id = row.last_DrugRequestStatus_id;
									params.DrugRequestPeriod_id = row.last_DrugRequestPeriod_id;
									getWnd('swNewDrugRequestEditForm').show(params);
								}
								else
								{
									Ext.Msg.alert(lang['soobschenie'], lang['u_vracha_net_ni_odnoy_zayavki_na_lekarstvennyie_sredstva']);
								}
							}
							if ('no' == buttonId)
							{
								params.action = 'add';
								params.DrugRequest_id = 0;
								params.DrugRequestStatus_id = 1;
								params.DrugRequestPeriod_id = row.next_DrugRequestPeriod_id;
								getWnd('swNewDrugRequestEditForm').show(params);
							}
						}
					});
				}
			}.createDelegate(this),
			failure: function() 
			{
				this.getLoadMask().hide();
			}.createDelegate(this)
		});
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
			readOnly: pdata.readOnly || null,
			acceptWithoutRecording: true,
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
			callback: function(){getWnd('swPersonEmkWindow').show(params);}
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
								//sw.swMsg.alert(langs('Ошибка'), langs('Запись невозможна в связи со смертью пациента'));
								// #140110 добавление возможности просмотра ЭМК умерших пациентов
								getWnd('swPersonSearchWindow').hide();
								pdata.readOnly = true;
								win.createTtgAndOpenPersonEPHForm(pdata);
								showSysMsg('Установлена дата смерти пациента. ЭМК доступна только для просмотра', 'Внимание', 'warning', {closable: true, delay: 15000, bodyStyle: 'text-align:left; margin-left:7px; padding: 0px 0px 20px 20px; background:transparent;'});
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
					var loadMask = new Ext.LoadMask(win.getEl(), {
						msg: "Получение списка направлений и записей..."
					});
					loadMask.show();
					Ext.Ajax.request({
						params: {
							useCase: 'create_evnpl_without_recording',
							Person_id: pdata.Person_id,
							MedStaffFact_id: win.userMedStaffFact.MedStaffFact_id
						},
						callback: function(opt, success, response) {
							loadMask.hide();
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
										Ext.Msg.alert(lang['soobschenie'], lang['patsient_zapisan_na_tekuschiy_den_vospolzuytes_funktsiey_priema_po_zapisi']);
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

											//msg = langs('Пациент ') + surnameAndInitials + langs(' имеет направление № ') + directionNum + langs(' по профилю ') + profileName + langs('. Обслужить направление? Пациент будет удален из очереди.');

											msg = langs('У пациента ') + surnameAndInitials + langs(' имеется очередь к врачу ') + fin + '.' + langs(' Принять ') + surnameAndInitials + langs(' и исключить его/ее из очереди врача ') + fin + '?';

											buttons.yes = langs('Обслужить направление и убрать из очереди');

										} else {
											//msg = langs('Пациент ') + surnameAndInitials + langs(' находится в очереди по профилю ') + profileName + langs('. Убрать пациента из очереди?');

											msg = langs('У пациента ') + surnameAndInitials + langs(' имеется очередь к врачу по профилю  ') + profileName + '.' + langs(' Принять ') + surnameAndInitials + langs(' и исключить его/ее из очереди по профилю ') + profileName + '?';
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
							//sw.swMsg.alert(langs('Ошибка'), langs('Запись невозможна в связи со смертью пациента'));
							// #140110 добавление возможности просмотра ЭМК умерших пациентов
							getWnd('swPersonSearchWindow').hide();
							pdata.readOnly = true;
							win.createTtgAndOpenPersonEPHForm(pdata);
							showSysMsg('Установлена дата смерти пациента. ЭМК доступна только для просмотра', 'Внимание', 'warning', {closable: true, delay: 15000, bodyStyle: 'text-align:left; margin-left:7px; padding: 0px 0px 20px 20px; background:transparent;'});
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
				msg: lang['zapisat_patsienta_na_vyibrannoe_vremya'],
				title: lang['vopros'],
				buttons: Ext.Msg.YESNO,
				fn: function(buttonId, text, obj)
				{
					if ('yes' == buttonId)
					{
						var form = Ext.getCmp('swMPWorkPlaceWindow');
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
				Ext.Msg.alert(lang['soobschenie'], lang['okno_poiska_cheloveka_uje_otkryito']);
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
							Ext.getCmp('swMPWorkPlaceWindow').scheduleSave(pdata);
						},
						onIsDead: function(res) {
							sw.swMsg.alert(lang['oshibka'], lang['zapis_nevozmojna_v_svyazi_so_smertyu_patsienta']);
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
			Ext.Msg.alert(lang['oshibka'], lang['ne_vyibrana_zapis']);
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
		alert(lang['redaktirovanie_birki']);
	},
	tryMe: function(msg){
		log(msg);
	},
	scheduleOpen: function()
	{
		var form = this;
		var grid = form.getGrid();

		if (!grid) {
			Ext.Msg.alert(lang['oshibka'], lang['spisok_raspisaniy_ne_nayden']);
			return false;
		} else if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('TimetableGraf_id') ){
			Ext.Msg.alert(lang['oshibka'], lang['ne_vyibrana_zapis']);
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

				this.scheduleRefresh();
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
					callback: function(){getWnd('swPersonEmkWindow').show(params);}
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
								callback: function(){getWnd('swPersonEmkWindow').show(params);}
							});
						} else if ( buttonId == 'no') {
							params.TimetableGraf_id = null;
							params.EvnDirectionData = null;
							checkPersonPhoneVerification({
								Person_id: params.Person_id,
								MedStaffFact_id: params.userMedStaffFact.MedStaffFact_id,
								callback: function(){getWnd('swPersonEmkWindow').show(params);}
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
					callback: function(){getWnd('swPersonEmkWindow').show(params);}
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
				title: lang['vnimanie'],
				buttons: Ext.Msg.YESNO,
				fn: function(buttonId, text, obj){
					if ('yes' == buttonId){
						var form = Ext.getCmp('swMPWorkPlaceWindow');
						options.ignorePriemCheck = 1;
						form.returnToQueue(options);
					}
				}
			});
			return false;
		}

		if(!options.ignoreLinkCheck) {
			Ext.Ajax.request({
				url: '/?c=EvnDirection&m=checkEQMedStaffFactLink',
				success: function(responseText) {
					var form = Ext.getCmp('swMPWorkPlaceWindow');
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
									form.returnToQueue(options);
								}
							}
						});
					} else {
						options.ignoreLinkCheck = 1;
						form.returnToQueue(options);
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
	requestAmbulatCardWindow: function(record){
		var indx = this.getGrid().getStore().find('TimetableGraf_id', record);
		if(indx && indx>=0){
			//выделим строку
			this.getGrid().getSelectionModel().selectRow(indx);
		}
		sw.swMsg.show({
			title: 'Запрос амбулаторной карты!',
			icon: Ext.Msg.WARNING,
			msg: "Запросить амбулаторную карту у картохранилища?",
			buttons: {ok: 'Запросить', yes: '&nbsp;Карта уже на приёме&nbsp;'},
			scope: {win: this},
			closable: true,
			fn: function(butn){
				if (butn == 'yes'){
					this.receptionAmbulatCard();
				}
				if(butn == 'ok'){
					this.requestAmbulatCard();
				}
			}.bind(this)
		});
	},
	requestAmbulatCard: function(){
		//	Запросить амб. карту у картохранилища 
		var record = this.getGrid().getSelectionModel().getSelected();
		if(!record || !record.get('PersonAmbulatCard_id')) return false;
		if(record.get('AmbulatCardRequestStatus_id') == 1){
			sw.swMsg.alert(langs('Сообщение'), 'Уже существует запрос амбулаторной карты №'+record.get('PersonAmbulatCard_Num'));
			return false;
		}
		sw.swMsg.show({
			title: 'Внимание!',
			icon: Ext.Msg.WARNING,
			msg: "Сделать запрос указанной карты №<b>"+record.get('PersonAmbulatCard_Num')+"</b> в картохранилище?",
			buttons: {yes: 'Да', no: 'Отмена'},
			scope: {win: this, record: record},
			fn: function(butn){
				if (butn == 'no'){
					return false;
				}else{
					var params = {};
					params.PersonAmbulatCard_id = record.get('PersonAmbulatCard_id');
					params.TimetableGraf_id = record.get('TimetableGraf_id');
					params.AmbulatCardRequest_id = (record.get('AmbulatCardRequest_id')) ? record.get('AmbulatCardRequest_id') : null;
					params.AmbulatCardRequestStatus_id = 1;
					Ext.Ajax.request({
						params: params,
						failure: function (result_form, action) {
							log('Ошибка при запросе амб.карты у картохранилища');
						},
						callback: function(options, success, response) {
							if (success && response.responseText != ''){
								var response_obj = Ext.util.JSON.decode(response.responseText);
								if(response_obj.AmbulatCardRequest_id){
									var record = this.win.getGrid().getSelectionModel().getSelected();
									var aHref = '&nbsp;&nbsp;<a href="#" onClick="Ext.getCmp(\'swMPWorkPlaceWindow\').requestAmbulatCardWindow('+record.get("TimetableGraf_id")+')">Запросить</a>';
									record.set('AmbulatCardRequestStatus_id', 1);
									record.set('AmbulatCardRequest', 'Да'+aHref);
									record.set('AmbulatCardRequest_id', response_obj.AmbulatCardRequest_id);
									record.commit();
								}else{
									log('Ошибка при запросе амб.карты у картохранилища. Запрос не вернул идентификатор AmbulatCardRequest_id');
								}
							}
							else{
								log('Ошибка при запросе амб.карты у картохранилища');
							}
						}.createDelegate(this),
						url: '?c=PersonAmbulatCard&m=setAmbulatCardRequest'
					});
				}
			}
		});
	},
	receptionAmbulatCard: function(){
		//	Карта уже на приёме 
		var record = this.getGrid().getSelectionModel().getSelected();
		if(!record || !record.get('PersonAmbulatCard_id')) return false;
		sw.swMsg.show({
			title: 'Внимание!',
			icon: Ext.Msg.WARNING,
			msg: "Карта №"+record.get('PersonAmbulatCard_Num')+" находится на приеме? Продолжить?",
			buttons: {yes: 'Да', no: 'Отмена'},
			scope: {win: this, record: record},
			fn: function(butn){
				if (butn == 'no'){
					return false;
				}else{
					var params = {};
					params.PersonAmbulatCard_id = record.get('PersonAmbulatCard_id');
					params.TimetableGraf_id = record.get('TimetableGraf_id');
					params.AmbulatCardRequest_id = (record.get('AmbulatCardRequest_id')) ? record.get('AmbulatCardRequest_id') : null;
					params.AmbulatCardRequestStatus_id = 2;
					params.MedStaffFact_id = this.win.userMedStaffFact.MedStaffFact_id;
					Ext.Ajax.request({
						params: params,
						failure: function (result_form, action) {
							log('Ошибка при запросе амб.карты у картохранилища');
						},
						callback: function(options, success, response) {
							if (success && response.responseText != ''){
								var response_obj = Ext.util.JSON.decode(response.responseText);
								var record = this.win.getGrid().getSelectionModel().getSelected();
								var AmbulatCardRequestStatus = parseInt(record.get('AmbulatCardRequestStatus_id'));
								if(AmbulatCardRequestStatus == 1) record.set('AmbulatCardRequestStatus_id', 2);
								var aHref = '&nbsp;<a href="#" onClick="Ext.getCmp(\'swMPWorkPlaceWindow\').requestAmbulatCardWindow('+record.get("TimetableGraf_id")+')">Запросить</a>';
								record.set('AmbulatCardRequest', 'Нет'+aHref);
								record.commit();
							}
							else{
								log('Ошибка при запросе амб.карты у картохранилища');
							}
						}.createDelegate(this),
						url: '?c=PersonAmbulatCard&m=setAmbulatCardRequest'
					});
				}
			}
		});
	},
	chooseAmbulatCard: function(){
		//	Выбрать другую амб. карту 
		var record = this.getGrid().getSelectionModel().getSelected();
		if(!record) return false;
		var id = record.get('Person_id');
		var fio = record.get('Person_FIO');
		var TimetableGraf_id = record.get('TimetableGraf_id');

		if(!id || !TimetableGraf_id) return false;
		var params = {
			Person_id: id,
			Person_FIO: fio,
			TimetableGraf_id: TimetableGraf_id
		};
		params.callback= function(params, id){
			this.setDisabled(false);
			if(!params || !params.PersonAmbulatCard_id || !id) return false;
			var record = this.getGrid().getSelectionModel().getSelected();
			var prm = {};
			prm.PersonAmbulatCard_id = params.PersonAmbulatCard_id;
			prm.TimetableGraf_id = record.get('TimetableGraf_id');
			Ext.Ajax.request({
				params: prm,
				scope: {grid: this.getGrid(), params: params},
				failure: function (result_form, action) {
					log('ошибка при установке амб.карты на бирку');
				},
				callback: function(options, success, response) {
					if (success && response.responseText != ''){
						var response_obj = Ext.util.JSON.decode(response.responseText);
						this.grid.getStore().reload();
						/*var record = this.grid.getSelectionModel().getSelected();
						record.set('PersonAmbulatCard_id', this.params.PersonAmbulatCard_id);
						record.set('PersonAmbulatCard_Num', this.params.PersonAmbulatCard_Num);
						record.commit();*/
					}
					else{
						log('ошибка при установке амб.карты на бирку');
					}
				},
				url: '?c=PersonAmbulatCard&m=savePersonAmbulatCardInTimetableGraf'
			});
		}.createDelegate(this);
		if(getWnd('swPersonAmbulatCardWindow')){
			if(getWnd('swPersonAmbulatCardWindow').isVisible()){
				sw.swMsg.alert(langs('Сообщение'), langs('Форма "Амбулаторные карты" уже открыта'));
				return false;
			}
			this.setDisabled(true);
			getWnd('swPersonAmbulatCardWindow').show(params);
		}
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
		this.linktitle = ' <a id="header_link_'+this.id+'" href="#" onClick="Ext.getCmp(&quot;swMPWorkPlaceWindow&quot;).showMenu(&quot;'+'header_link_'+this.id+'&quot;);">'+lang['tekuschee_nazvanie_arma_i_otdelenie']+'</a>';
	},
	*/
	printAddressLeaf: function(leaf_type) {
		var grid = this.getGrid();
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
		var r = grid.getSelectionModel().getSelected();

		if ( typeof r != 'object' || !leaf_type || !leaf_type.inlist(['arrival','departure']) ) {
			return false;
		}

		var Person_id = r.get('Person_id');
		if ( Ext.isEmpty(Person_id) ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_ukazan_patsient']);
			return false;
		}

		var Lpu_id = getGlobalOptions().lpu_id;
		if ( Ext.isEmpty(Lpu_id) ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_ukazano_lpu']);
			return false;
		}

		var tpl = '';
		if (leaf_type == 'arrival') {
			tpl = 'LeafArrival.rptdesign';
		} else
		if (leaf_type == 'departure') {
			tpl = 'LeafDeparture.rptdesign';
		}

		printBirt({
			'Report_FileName': tpl,
			'Report_Params': '&paramPerson_id='+Person_id+'&paramLpu='+Lpu_id,
			'Report_Format': 'pdf'
		});
	},
	showRecList: function(TimetableGraf_id){
		if(!TimetableGraf_id)
			return false;
		getWnd('swTimeTableGrafRecListWindow').show({
			TimetableGraf_id: TimetableGraf_id,
			callback: function() {
			}
		});
	},
	show: function()
	{
		sw.Promed.swMPWorkPlaceWindow.superclass.show.apply(this, arguments);

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

		if(getRegionNick() != 'kz' && sw.Promed.Actions.VideoChatBtn) {
			win.VideoChatBtn = sw.Promed.Actions.VideoChatBtn;
			win.VideoChatBtn.show();
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

		//this.setTitle(this.title+this.ARMSelectBth.getEl());
		/*this.findById('mpwpSearch_UslugaComplexPid').getStore().removeAll();

		switch ( this.ARMType ) {
			case 'common':
				this.findById('mpwpSearch_UslugaComplexPid').setContainerVisible(false);*/

				for ( btnAction in this.BtnActions ) {
					if ( typeof this.BtnActions[btnAction] == 'object' ) {
						/*if ( this.BtnActions[btnAction].nn.inlist([ 'action_UslugaComplexTree' ]) ) {
							this.BtnActions[btnAction].hide();
						}
						else {*/
						if ( !Ext.isEmpty(this.userMedStaffFact.MedStaffFactLink_id) && !Ext.isEmpty(this.BtnActions[btnAction].nn) && this.BtnActions[btnAction].nn.inlist([ 'action_MidMedPersonal' ]) ) {
							this.BtnActions[btnAction].hide();
						} else {
							this.BtnActions[btnAction].show();
						}
					}
				}
			/*break;

			case 'par':
				this.findById('mpwpSearch_UslugaComplexPid').setContainerVisible(true);
				this.findById('mpwpSearch_UslugaComplexPid').getStore().load();

				for ( btnAction in this.BtnActions ) {
					if ( typeof this.BtnActions[btnAction] == 'object' ) {
						if ( this.BtnActions[btnAction].nn.inlist([ 'action_DrugRequestEditForm', 'action_PersDispSearchView', 'action_PersCardSearch', 'action_JournalHospit', 'action_PrivilegeSearch' ]) ) {
							this.BtnActions[btnAction].hide();
						}
						else {
							this.BtnActions[btnAction].show();
						}
					}
				}
			break;

			default:
				this.findById('mpwpSearch_UslugaComplexPid').setContainerVisible(false);

				for ( btnAction in this.BtnActions ) {
					if ( typeof this.BtnActions[btnAction] == 'object' ) {
						this.BtnActions[btnAction].show();
					}
				}
			break;
		}
		*/
		// Потом читаем расписание на этот день согласно установленным настройкам 
		//this.dateMenu.setValue(this.curDate);
		//this.dateMenu.setValue2(this.curDate);
		//this.dateTpl.overwrite(this.dateText.getEl(), {period:'Текущая дата'});

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

		this.startHomeVisitCounter();

		// Переключатель
		this.checkMedStaffFactReplace();
		this.syncSize();
		//автозапуск сигнальной информации #137508
		if (getGlobalOptions().region.nick == 'ufa' && (this.userMedStaffFact.PostMed_id == "74" || this.userMedStaffFact.PostMed_id == "47" || this.userMedStaffFact.PostMed_id == "40" || this.userMedStaffFact.PostMed_id == "117" || this.userMedStaffFact.PostMed_id == "111" || this.userMedStaffFact.PostMed_id == "41")) {
			function setUser(cname, cvalue) {
				var currentDate = new Date();
				expirationDate = new Date(currentDate.getFullYear(), currentDate.getMonth(), currentDate.getDate() + 1, 0, 0, 0);// очищаем в конце дня
				var expires = "expires=" + expirationDate.toGMTString();
				document.cookie = cname + "=" + cvalue + "; " + expires;
			}
			function getCookie(cname) {
				var name = cname + "=";
				var ca = document.cookie.split(';');
				for (var i = 0; i < ca.length; i++) {
					var c = ca[i];
					while (c.charAt(0) == ' ')
						c = c.substring(1);
					if (c.indexOf(name) == 0) {
						return c.substring(name.length, c.length);
					}
				}
				return "";
			}

			var session = this.userMedStaffFact.MedPersonal_id;

			var user = getCookie(session);
			if (user != '') {
				return "";
			} else {
				win.autoloadSignalInfo();
				setUser(session, 1);
			}
		}
	},

	autoloadSignalInfo: function () {
		getWnd('swSignalInfoWindow').show({
			userMedStaffFact: this.userMedStaffFact,
			MedSvidDeath: 1

		});
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
			{name:'open_emk', text:lang['otkryit_emk'], tooltip: lang['otkryit_elektronnuyu_meditsinskuyu_kartu_patsienta'], iconCls : 'open16', handler: function() {this.scheduleOpen();}.createDelegate(this)},
			{name:'open', text:lang['otkryit'], tooltip: lang['otkryit'], iconCls : 'x-btn-text', icon: 'img/icons/open16.png', handler: function() {this.scheduleOpen();}.createDelegate(this)},
			{name:'reception_soc', text:lang['prinyat_po_sots_karte'], hidden: !(getGlobalOptions().region && getRegionNick() == 'ufa'), tooltip: lang['prinyat_po_sots_karte'], iconCls : 'copy16', handler: function() {this.receptionBySocCard();}.createDelegate(this)},
			{name:'create', text:lang['prinyat_bez_zapisi'], tooltip: lang['patsient_bez_zapisi'], iconCls : 'copy16', handler: function() {this.scheduleNew();}.createDelegate(this)},
			{name:'add', text:lang['zapisat_patsienta'], tooltip: lang['zapisat_patsienta'], iconCls : 'x-btn-text', icon: 'img/icons/add16.png', handler: function() {this.scheduleAdd();}.createDelegate(this)},
			{name:'queue', text:lang['zapisat_iz_ocheredi'], tooltip: lang['zapisat_iz_ocheredi'], iconCls : 'x-btn-text', icon: 'img/icons/add16.png', handler: function() {this.sheduleAddFromQueue();}.createDelegate(this)},
			{name:'edit', text:BTN_GRIDEDIT, tooltip: BTN_GRIDEDIT, iconCls : 'x-btn-text', icon: 'img/icons/edit16.png', handler: function() {this.scheduleEdit();}.createDelegate(this)},
			{name:'copy', text:BTN_GRIDCOPY, tooltip: BTN_GRIDCOPY, hidden: true, iconCls : 'x-btn-text', icon: 'img/icons/copy16.png', handler: function() {this.scheduleCopy();}.createDelegate(this)},
			{name:'paste', text:BTN_GRIDPASTE, tooltip: BTN_GRIDPASTE, hidden: true, iconCls : 'x-btn-text', /*icon: 'img/icons/paste16.png',*/ handler: function() {win.schedulePaste();}},
			{name:'reject', text:lang['otklonit'], tooltip: lang['otklonit'], iconCls : 'x-btn-text', icon: 'img/icons/delete16.png', handler: function() {win.reject();}},
			{name:'returnToQueue', text:lang['ubrat_v_ochered'], tooltip: lang['ubrat_v_ochered'], iconCls : 'x-btn-text', icon: 'img/icons/delete16.png', handler: function() {win.returnToQueue();}},
			{name:'refresh', text:BTN_GRIDREFR, tooltip: BTN_GRIDREFR, iconCls : 'x-btn-text', icon: 'img/icons/refresh16.png', handler: function() {this.scheduleRefresh();}.createDelegate(this)},
			{name:'additionally', key: 'additionally', text:lang['dopolnitelno'], menu: [
				new Ext.Action({name:'rewrite', text:lang['perezapisat'], tooltip: lang['perezapisat'], handler: function() {win.rewrite();}}),
				new Ext.Action({name:'redirect', text:lang['perenapravit'], tooltip: lang['perenapravit'], handler: function() {win.redirect();}, hidden: true})
			], tooltip: lang['dopolnitelno'], iconCls : 'x-btn-text', icon: 'img/icons/actions16.png'},
			{name:'actions', key: 'actions', text:lang['deystviya'], menu: [
				new Ext.Action({name:'collapse_all', text:lang['svernut_vse'], tooltip: lang['svernut_vse'], handler: function() {this.scheduleCollapseDates();}.createDelegate(this)}),
				new Ext.Action({name:'expand_all', text:lang['razvernut_vse'], tooltip: lang['razvernut_vse'], handler: function() {this.scheduleExpandDates();}.createDelegate(this)}),
				new Ext.Action({name:'check_evnpldispdop', text:'Проф. осмотры', tooltip: 'Профилактические осмотры', handler: function() {this.checkEvnPLDispDop();}.createDelegate(this)})
			], tooltip: lang['deystviya'], iconCls : 'x-btn-text', icon: 'img/icons/actions16.png', handler: function() {}},
			{name:'printPacList', iconCls: 'print16', text: 'Печать списка пациентов', tooltip : "Печать списка пациентов", hidden: (getRegionNick() != 'ufa' && getRegionNick() != 'kz'), handler: function () {this.printPacList();}.createDelegate(this)},
			{name:'print', text:BTN_GRIDPRINT, tooltip: BTN_GRIDPRINT, iconCls : 'x-btn-text', icon: 'img/icons/print16.png', menu: [
				new Ext.Action({name:'print_rec', text:lang['pechat'], handler: function() {this.schedulePrint('row');}.createDelegate(this)}),
				new Ext.Action({name:'print_all', text:lang['pechat_spiska'], handler: function() {this.schedulePrint();}.createDelegate(this)}),
				new Ext.Action({name:'leaf_arrival', text: lang['listok_pribyitiya'], hidden: getRegionNick()=='kz', handler: function () {this.printAddressLeaf('arrival');}.createDelegate(this)}),
				new Ext.Action({name:'leaf_departure', text: lang['listok_ubyitiya'], hidden: getRegionNick()=='kz', handler: function () {this.printAddressLeaf('departure');}.createDelegate(this)})
            ]}
            /*{name:'ambulatCard', text: langs('Амбулаторная карта'), tooltip: langs('Амбулаторная карта'), iconCls : 'x-btn-text', icon: 'img/icons/pers-card16.png', menu: [
				new Ext.Action({name:'request_ambulatCard', text:langs('Запросить амб. карту у картохранилища'), handler: function() {this.requestAmbulatCard()}.createDelegate(this)}),
				new Ext.Action({name:'reception_ambulatCard', text:langs('Карта уже на приёме'), handler: function() {this.receptionAmbulatCard()}.createDelegate(this)}),
				new Ext.Action({name:'choose_ambulatCard', text: langs('Выбрать другую амб. карту'), handler: function () {this.chooseAmbulatCard();}.createDelegate(this)})
			]}*/
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
				//this.gridActions.ambulatCard,
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
			[
				{
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
					name: 'TimetableGrafRecList_id'
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
				{name: 'PersonAmbulatCard_id'},
				{name: 'PersonAmbulatCard_Num'},
				{name: 'AmbulatCardRequest_id'},
				{name: 'locationMedStaffFact_id'},
				{name: 'AmbulatCardRequestStatus_id'},
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
				{name: 'EvnDirection_id'},
				{name: 'EvnQueue_id'},
				{name: 'EvnStatus_id'},
				{name: 'MSF_Person_Fin'},
				{name: 'EvnDirection_Num'},
				{name: 'LpuSectionProfile_Name'},
				{name: 'IsEvnDirection'},
				{name: 'PersonEncrypHIV_Encryp'},
				{name: 'ARMType_id'},
				{name: 'TimetableType_id'},
				{name: 'TimetableGrafRecList_id'},
				{name: 'TimeTableGraf_PersRecLim'},
				{name: 'TimeTableGraf_countRec'},
				{name: 'PersonQuarantine_IsOn'}
			]
		);
        
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
						var form = Ext.getCmp('swMPWorkPlaceWindow');
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
								// if (!form.gridActions.ambulatCard.initialConfig.initialDisabled)
								// 	form.gridActions.ambulatCard.setDisabled(false);
							}
							form.restorePosition();
							//grid.focus();
							store.each(function(record) 
							{
								//log(record.get('TimetableGraf_factTime'));
								if (record.get('TimetableGraf_factTime')!='' || !(Ext.isEmpty(record.get('TimetableGrafRecList_id'))))
								{
									record.set('Person_IsEvents', "true");
									//record.commit();
								}
								
								var locationMedStaffFact = parseInt(record.get('locationMedStaffFact_id'));
								var MedStaffFact = parseInt(record.get('MedStaffFact_id'));
								var AmbulatCardRequest = parseInt(record.get('AmbulatCardRequest_id'));
								var AmbulatCardRequestStatus = parseInt(record.get('AmbulatCardRequestStatus_id'));
								var set = '';
								if(!record.get('PersonAmbulatCard_id')){
									set = '';
								}else if(AmbulatCardRequestStatus == '1'){
									set = 'Да&nbsp;';
								}else if(AmbulatCardRequestStatus !=1 && MedStaffFact == locationMedStaffFact){
									//нет открытых запросов карты в картохранилище от врача и местонахождение карты совпадает с врачом приёма
									set = 'Нет';
								}else if(AmbulatCardRequestStatus !=1 && MedStaffFact != locationMedStaffFact){
									set = 'Карта будет доставлена планово';
								}

								var aRec = (record.get('PersonAmbulatCard_id')) ? '&nbsp;&nbsp;<a href="#" onClick="Ext.getCmp(\'swMPWorkPlaceWindow\').requestAmbulatCardWindow('+record.get("TimetableGraf_id")+')">Запросить</a>' : '';
								
								record.set('AmbulatCardRequest', set + aRec);
									record.commit();
							});

							//form.scheduleCollapseDates(); // Беру на себя ответственность - пока это явно лишнее 
						} else {
							grid.focus();
						}
					}
				},
				clear: function()
				{
					var form = Ext.getCmp('swMPWorkPlaceWindow');
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
		};
        
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
			var form = Ext.getCmp('swMPWorkPlaceWindow');
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
			var form = Ext.getCmp('swMPWorkPlaceWindow');
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
					['1','•	АРМ врача поликлиники', 'swMPWorkPlaceWindow'],
					['2','•	АРМ приемного отделения', 'swMPWorkPlaceWindow']
				]
			}),
			anchor: '100%',
			editable: false,
			fieldLabel: lang['arm'],
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
				title: lang['poisk'],
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
						fieldLabel: lang['fio'],
						listeners:
						{
							'keydown': function (inp, e)
							{
								var form = Ext.getCmp('swMPWorkPlaceWindow');
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
						fieldLabel: lang['familiya'],
						listeners:
						{
							'keydown': function (inp, e)
							{
								var form = Ext.getCmp('swMPWorkPlaceWindow');
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
						fieldLabel: lang['imya'],
						listeners:
						{
							'keydown': function (inp, e)
							{
								var form = Ext.getCmp('swMPWorkPlaceWindow');
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
						fieldLabel: lang['otchestvo'],
						listeners:
						{
							'keydown': function (inp, e)
							{
								var form = Ext.getCmp('swMPWorkPlaceWindow');
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
						fieldLabel: lang['data_rojdeniya'],
						listeners:
						{
							'keydown': function (inp, e)
							{
								var form = Ext.getCmp('swMPWorkPlaceWindow');
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
							fieldLabel: lang['tsel_napravleniya'],
							listeners:
							{
								'keydown': function (inp, e)
								{
									var form = Ext.getCmp('swMPWorkPlaceWindow');
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
						text: lang['nayti'],
						iconCls: 'search16',
						handler: function()
						{
							var form = Ext.getCmp('swMPWorkPlaceWindow');
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
						text: lang['sbros'],
						iconCls: 'resetsearch16',
						handler: function()
						{
							var form = Ext.getCmp('swMPWorkPlaceWindow');
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
						text: lang['schitat_s_kartyi'],
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
		});
	
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
					title: lang['lpu'],
					layout: 'column',
					items: 
					[{
						layout: 'form',
						columnWidth: .3,
						items: 
						[{
							fieldLabel: lang['lpu'],
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
									var form = Ext.getCmp('swMPWorkPlaceWindow');
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
							fieldLabel: lang['vrach'],
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
							text: lang['pokazat_raspisanie'],
							iconCls: 'search16',
							handler: function()
							{
								var form = Ext.getCmp('swMPWorkPlaceWindow');
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
							text: lang['vernutsya_k_patsientu'],
							iconCls: 'close16',
							handler: function()
							{
								var form = Ext.getCmp('swMPWorkPlaceWindow');
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
										getWnd('swMPWorkPlaceWindow').show({formMode:'open'});
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
				width: 80,
				sortable: true,
				renderer: function(v,cell,rec){
					var resStr = v;
					if (rec && rec.get('TimetableType_id') == 14){
						var group = "<span class='timetable-type-group' data-qtip='Групповой приём'></span>";
						resStr = "<span style='float:left;padding-right:4px;'>"+resStr+"</span>"+group;
					}
					return resStr;
				},
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
				renderer: function(v,cell,rec){
					if(rec && rec.get('TimetableType_id') == 14
						&& !Ext.isEmpty(rec.get('TimeTableGraf_countRec')) && parseInt(rec.get('TimeTableGraf_countRec'))>0){
						v = 'Групповой приём';
						var tooltip = 'Просмотр записавшихся',
							spanClass = 'group-visit',
							spanText = '';
						if(rec.get('TimeTableGraf_countRec') && rec.get('TimeTableGraf_PersRecLim')){
							spanText = '<a href="#" ' +
								'onclick="Ext.getCmp(\'' + win.id + '\').showRecList(' +
								"'" + rec.get('TimetableGraf_id') + "'" +
								')">'+'('+rec.get('TimeTableGraf_countRec')+' из '+rec.get('TimeTableGraf_PersRecLim')+')'+'</a>';
						}
						return "<span style='float: left;'>" + v + "</span><span class='" + spanClass + "' data-qtip='"+tooltip+"'>"+spanText+"</span>";
					}

					if (!v) {
						return '';
					}

					return v;
				},
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
			/* Убрал простое отображение льготы, добавил федеральную и региональную (http://172.19.61.24:85/issues/show/2095)*/
			/*{
				header: lang['lgota'],
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
				header: lang['rl'],
				width: 40,
				sortable: false,
				dataIndex: 'Person_IsRegLgot',
				renderer: sw.Promed.Format.checkColumn,
				hideable: !getRegionNick().inlist([ 'kz' ]),
				hidden: getRegionNick().inlist([ 'kz' ])
			},
			{
				hidden: true,
				header: langs("№ амб. карты"),
				width: 80,
				sortable: false,
				dataIndex: 'PersonCard_Code'
			},
			{
				header: langs("№ амб. карты"),
				width: 100,
				sortable: false,
				dataIndex: 'PersonAmbulatCard_Num',
				renderer: function(a,b,r) {
					var str = '';
					if(getPolkaOptions().allow_access_to_the_functionality_card_store){
						if(r.get('Person_id')){
							var num = (r.get('PersonAmbulatCard_Num')) ? r.get('PersonAmbulatCard_Num') : '&nbsp;';
							str = '<a style="display:block;float:left;" href="#" onClick="Ext.getCmp(\'swMPWorkPlaceWindow\').chooseAmbulatCard('+r.get("TimetableGraf_id")+')">'+num+'</a>';
						}
						//var aRec =  '<a style="display:block;float:right;" href="#" onClick="Ext.getCmp(\'swMPWorkPlaceWindow\').requestAmbulatCardWindow('+r.get("TimetableGraf_id")+')">Запросить</a>'
					}else{
						str = r.get('PersonAmbulatCard_Num');
					}
					return str;
				}
			},
			{
				header: "Карта запрошена у картохранилища",
				width: 250,
				sortable: false,
				dataIndex: 'AmbulatCardRequest',
				hidden: !getPolkaOptions().allow_access_to_the_functionality_card_store
				/*renderer: function(a,b,r) {
					if(!r) return false;
					var locationMedStaffFact = parseInt(r.get('locationMedStaffFact_id'));
					var MedStaffFact = parseInt(r.get('MedStaffFact_id'));
					var AmbulatCardRequest = parseInt(r.get('AmbulatCardRequest_id'));
					var AmbulatCardRequestStatus = parseInt(r.get('AmbulatCardRequestStatus_id'));

					if(!r.get('PersonAmbulatCard_id')){
						return '';
					}else if(AmbulatCardRequestStatus == '1'){
						return 'Да';
					}else if(AmbulatCardRequestStatus !=1 && MedStaffFact == locationMedStaffFact){
						//нет открытых запросов карты в картохранилище от врача и местонахождение карты совпадает с врачом приёма
						return 'Нет';
					}else if(AmbulatCardRequestStatus !=1 && MedStaffFact != locationMedStaffFact){
						return 'Карта будет доставлена планово';
					}else{
						return '';
					}
				},*/
			},
			{ header: "locationMedStaffFact_id", hidden: true, dataIndex: 'locationMedStaffFact_id'},
			{ header: "AmbulatCardRequest_id", hidden: true, dataIndex: 'AmbulatCardRequest_id'},
			{ header: "AmbulatCardRequestStatus_id", hidden: true, dataIndex: 'AmbulatCardRequestStatus_id'},
			{ header: "PersonAmbulatCard_id", hidden: true/*, hideable: false*/, dataIndex: 'PersonAmbulatCard_id' },
			{
				header: langs("ЛПУ прикр."),
				width: 80,
				sortable: false,
				dataIndex: 'Lpu_Nick'
			},
			{
				header: lang['uchastok'],
				width: 80,
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
				groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length.inlist([2,3,4]) ? "записи" : "записей"]})'
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
				var form = Ext.getCmp('swMPWorkPlaceWindow');
				var print_menu = form.gridActions.print.initialConfig.menu;
				print_menu[2].setDisabled(true);	//Листок прибытия
				print_menu[3].setDisabled(true);	//Листок убытия
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
						var form = Ext.getCmp('swMPWorkPlaceWindow');
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
								// if (!form.gridActions.ambulatCard.initialConfig.initialDisabled)
								// 	form.gridActions.ambulatCard.setDisabled(true);
								
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

								print_menu[2].setDisabled(true);	//Листок прибытия
								print_menu[3].setDisabled(true);	//Листок убытия
							}
							else 
							{
								var TimetableGraf_Date = null;
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
								// if (!form.gridActions.ambulatCard.initialConfig.initialDisabled)
								// 	form.gridActions.ambulatCard.setDisabled( (record.get('Person_IsEvents')) );

								print_menu[2].setDisabled(!Ext.isEmpty(record.get('PersonEncrypHIV_Encryp')));	//Листок прибытия
								print_menu[3].setDisabled(!Ext.isEmpty(record.get('PersonEncrypHIV_Encryp')));	//Листок убытия

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

		this.ScheduleGrid.view = new Ext.grid.GridView({
			getRowClass : function (row, index) {
				var cls = '';

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
			var win = Ext.getCmp('swMPWorkPlaceWindow');
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
			var win = Ext.getCmp('swMPWorkPlaceWindow');
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
			var win = Ext.getCmp('swMPWorkPlaceWindow');
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
				};
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
								LpuRegion_id: response_obj[0].LpuRegion_id,
								userMedStaffFact: form.userMedStaffFact,
								armType: 'mp'
							});
						}
						else
						{
							getWnd(wnd).show({
								armType: 'mp'
							});
						}
					}
				},
				params: {MedPersonal_id: global_options.medpersonal_id, Lpu_id: global_options.lpu_id}
			});
		};
		var form = this;
		
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
				mainForm: {
					text: lang['jurnal_vaktsinatsii'],
					tooltip: lang['prosmotr_jurnalov_vaktsinatsii'],
					iconCls : 'pol-immuno16',
					handler: function()
					{
						getWnd('amm_mainForm').show();
					}
				},
				PresenceVacForm: {
					text: lang['natsionalnyiy_kalendar_privivok'],
					tooltip: lang['natsionalnyiy_kalendar_privivok'],
					iconCls : 'pol-immuno16',
					handler: function()
					{
                        getWnd('amm_SprNacCalForm').show();
					}
				},
				SprVaccineForm: {
					text: lang['spravochnik_vaktsin'],
					tooltip: lang['spravochnik_vaktsin'],
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
				PrivilegeSearch: {
					tooltip: MM_DLO_LGOTSEARCH,
					text: lang['poisk_lgotnikov'],
					iconCls : 'lgot-search16',
					hidden: getRegionNick().inlist(['pskov']),
					handler: function() 
					{
						getWnd('swPrivilegeSearchWindow').show();
					}
				},
				EvnReceptInCorrectFind: {
					text: lang['jurnal_otsrochki'],
					tooltip: lang['jurnal_otsrochki'],
					iconCls : 'receipt-incorrect16',
					handler: function()
					{
						getWnd('swReceptInCorrectSearchWindow').show();
					}
				},
				OstAptekaViewAction: {
					text: MM_DLO_MEDAPT,
					tooltip: lang['rabota_s_ostatkami_medikamentov_po_aptekam'],
					iconCls : 'drug-farm16',
					handler: function()
					{
						getWnd('swDrugOstatByFarmacyViewWindow').show();
					},
					hidden: !(getRegionNick() == 'perm')
				},
				OstDrugViewAction: {
					text: MM_DLO_MEDNAME,
					tooltip: lang['rabota_s_ostatkami_medikamentov_po_naimenovaniyu'],
					iconCls : 'drug-name16',
					handler: function()
					{
						getWnd('swDrugOstatViewWindow').show();
					},
					hidden: !(getRegionNick() == 'perm')
				},
				DrugOstat:
				{
					text: lang['prosmotr_ostatkov'],
					tooltip: lang['prosmotr_ostatkov'],
					iconCls: 'pill16',
					handler: function() {
						getWnd('swDrugOstatRegistryListWindow').show({
                            mode: 'suppliers',
                            userMedStaffFact: this.userMedStaffFact
                        });
					}.createDelegate(this),
					hidden: getRegionNick().inlist(['perm','ufa'])
				},
				DrugOstatAptRAS:
				{
					text: langs('Просмотр остатков по складам Аптек и РАС'),
					tooltip: langs('Просмотр остатков по складам Аптек и РАС'),
					iconCls: 'pill16',
					handler: function() {
						getWnd('swDrugOstatRegistryListWindow').show({mode: 'farmacy_and_store'});
					}.createDelegate(this),
					hidden: getRegionNick().inlist(['perm','ufa'])
				},
				PregnancyRegistry: 
				{
					tooltip: lang['registr_beremennyih'],
					text: lang['registr_beremennyih'],
					iconCls : 'doc-reg16',
					disabled: !isPregnancyRegisterAccess(),
					hidden: false,
					handler: function()
					{
						getWnd('swPersonPregnancyWindow').show();
					}
				},
				CVIRegistry: {
					tooltip: 'Регистр КВИ',
					text: 'Регистр КВИ',
					iconCls : 'doc-reg16',
					hidden: false,
					handler: function() {
						form.getAttachDataShowWindow('swCVIRegistryWindow');
					}
				},
				/**
				NolosRegistry:
				{
					tooltip: 'Регистр по ВЗН',
					text: 'Регистр по ВЗН',
					iconCls : 'doc-reg16',
					disabled: (false == sw.Promed.personRegister.isVznRegistryOperator()),
					hidden: (false == sw.Promed.personRegister.isAllow('nolos')),
					handler: function()
					{
						if ( getWnd('swPersonRegisterNolosListWindow').isVisible() ) {
							getWnd('swPersonRegisterNolosListWindow').hide();
						}
						getWnd('swPersonRegisterNolosListWindow').show({userMedStaffFact: form.userMedStaffFact});
					}
				},
				 * duplicate
				 */
				NolosRegistry: sw.Promed.personRegister.getVznBtnConfig(form.id, form),
				EvnNotifyNolos: sw.Promed.personRegister.getEvnNotifyVznBtnConfig(form.id, form),
				OrphanRegistry: sw.Promed.personRegister.getOrphanBtnConfig(form.id, form),
				EvnNotifyOrphan: sw.Promed.personRegister.getEvnNotifyOrphanBtnConfig(form.id, form),
				EvnNotifyPalliat: sw.Promed.personRegister.getEvnNotifyPalliatBtnConfig(form.id, form),
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
				DiabetesRegistry:{
					tooltip: lang['registr_po_saharnomu_diabetu'],
					text: lang['registr_po_saharnomu_diabetu'],
					iconCls : 'doc-reg16',
					hidden: !getRegionNick().inlist([ 'pskov','khak','saratov','buryatiya' ]),
					handler: function()
					{
						if ( getWnd('swDiabetesRegistryWindow').isVisible() ) {
							sw.swMsg.show({
								buttons: Ext.Msg.OK,
								fn: Ext.emptyFn,
								icon: Ext.Msg.WARNING,
								msg: lang['okno_uje_otkryito'],
								title: ERR_WND_TIT
							});
							return false;
						}
						getWnd('swDiabetesRegistryWindow').show({userMedStaffFact: this.userMedStaffFact});
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
					text: lang['registr_bolnyih_tuberkulezom'],
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
					text: lang['registr_bolnyih_venericheskimi_zabolevaniyami'],
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
					tooltip: lang['registr_vich-infitsirovannyih'],
					text: lang['registr_vich-infitsirovannyih'],
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
				PreOnkoRegistry: {
					text: 'Регистр по предраковому состоянию',
					iconCls : 'doc-reg16',
					hidden: !getRegionNick().inlist(['perm', 'msk']),
                    disabled: !isUserGroup('PreOnkoRegistryFull') && !isUserGroup('PreOnkoRegistryView'),
					handler: function() {
						getWnd('swPreOnkoRegistryWindow').show({userMedStaffFact: this.userMedStaffFact});
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
					disabled: !isUserGroup('OnkoRegistry') && !isUserGroup('OnkoRegistryFullAccess'), 
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
						getWnd('swOnkoRegistryWindow').show({userMedStaffFact: this.userMedStaffFact, ARMType:this.ARMType});
					}.createDelegate(this)
				},
				EvnOnkoNotify: 
				{
					tooltip: lang['jurnal_izvescheniy_ob_onkobolnyih'],
					text: lang['jurnal_izvescheniy_ob_onkobolnyih'],
					iconCls : 'journal16',
					disabled: !isUserGroup('OnkoRegistry') && !isUserGroup('OnkoRegistryFullAccess'),
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
				},
				GeriatricsRegistry: 
				{
					tooltip: langs('Регистр по гериатрии'),
					text: langs('Регистр по гериатрии'),
					iconCls : 'doc-reg16',
					hidden: getRegionNick() == 'kz',
					disabled: !isUserGroup('GeriatryRegistry') && !isUserGroup('GeriatryRegistryFullAccess') && !isSuperAdmin(), 
					handler: function() {
						if ( getWnd('swGeriatricsRegistryWindow').isVisible() ) {
							sw.swMsg.show({
								buttons: Ext.Msg.OK,
								fn: Ext.emptyFn,
								icon: Ext.Msg.WARNING,
								msg: langs('Окно уже открыто'),
								title: ERR_WND_TIT
							});
							return false;
						}
						getWnd('swGeriatricsRegistryWindow').show({userMedStaffFact: this.userMedStaffFact});
					}.createDelegate(this)
				},
                NephroRegistry:
                {
                    tooltip: lang['registr_po_nefrologii'],
                    text: lang['registr_po_nefrologii'],
                    iconCls : 'doc-reg16',
					hidden: !getRegionNick().inlist([ 'perm', 'ufa' ,'buryatiya']),
                    disabled: (String(getGlobalOptions().groups).indexOf('NephroRegistry', 0) < 0),
                    handler: function()
                    {
                        if ( getWnd('swNephroRegistryWindow').isVisible() ) {
                            sw.swMsg.show({
                                buttons: Ext.Msg.OK,
                                fn: Ext.emptyFn,
                                icon: Ext.Msg.WARNING,
                                msg: lang['okno_uje_otkryito'],
                                title: ERR_WND_TIT
                            });
                            return false;
                        }
                        getWnd('swNephroRegistryWindow').show({userMedStaffFact: this.userMedStaffFact});
                    }.createDelegate(this)
                },
                IBSRegistry:
                {
                    tooltip: lang['registr_ibs'],
                    text: lang['registr_ibs'],
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
                                msg: lang['okno_uje_otkryito'],
                                title: ERR_WND_TIT
                            });
                            return false;
                        }
                        getWnd('swIBSRegistryWindow').show({userMedStaffFact: this.userMedStaffFact});
                    }.createDelegate(this)
                },
                EvnNotifyIBS:
                {
                    tooltip: lang['jurnal_izvescheniy_po_ibs'],
                    text: lang['jurnal_izvescheniy_po_ibs'],
                    iconCls : 'journal16',
                    hidden: true,//('perm' != getRegionNick()),
                    handler: function()
                    {
                        if ( getWnd('swEvnNotifyIBSListWindow').isVisible() ) {
                            sw.swMsg.show({
                                buttons: Ext.Msg.OK,
                                fn: Ext.emptyFn,
                                icon: Ext.Msg.WARNING,
                                msg: lang['okno_uje_otkryito'],
                                title: ERR_WND_TIT
                            });
                            return false;
                        }
                        getWnd('swEvnNotifyIBSListWindow').show({userMedStaffFact: this.userMedStaffFact});
                    }.createDelegate(this)
                },
				ProfRegistry:
                {
                    tooltip: lang['registr_po_profzabolevaniyam'],
                    text: lang['registr_po_profzabolevaniyam'],
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
                                msg: lang['okno_uje_otkryito'],
                                title: ERR_WND_TIT
                            });
                            return false;
                        }
                        getWnd('swProfRegistryWindow').show({userMedStaffFact: this.userMedStaffFact});
                    }.createDelegate(this)
                },
                EvnNotifyNephro:
                {
                    tooltip: lang['jurnal_izvescheniy_po_nefrologii'],
                    text: lang['jurnal_izvescheniy_po_nefrologii'],
                    iconCls : 'journal16',
					hidden: !getRegionNick().inlist([ 'perm', 'ufa' ,'buryatiya']),
                    handler: function()
                    {
                        if ( getWnd('swEvnNotifyNephroListWindow').isVisible() ) {
                            sw.swMsg.show({
                                buttons: Ext.Msg.OK,
                                fn: Ext.emptyFn,
                                icon: Ext.Msg.WARNING,
                                msg: lang['okno_uje_otkryito'],
                                title: ERR_WND_TIT
                            });
                            return false;
                        }
                        getWnd('swEvnNotifyNephroListWindow').show({userMedStaffFact: this.userMedStaffFact});
                    }.createDelegate(this)
                },
				EvnNotifyProf:
                {
                    tooltip: lang['jurnal_izvescheniy_po_profzabolevaniyam'],
                    text: lang['jurnal_izvescheniy_po_profzabolevaniyam'],
                    iconCls : 'journal16',
                    hidden: ('perm' != getRegionNick()),
                    handler: function()
                    {
                        if ( getWnd('swEvnNotifyProfListWindow').isVisible() ) {
                            sw.swMsg.show({
                                buttons: Ext.Msg.OK,
                                fn: Ext.emptyFn,
                                icon: Ext.Msg.WARNING,
                                msg: lang['okno_uje_otkryito'],
                                title: ERR_WND_TIT
                            });
                            return false;
                        }
                        getWnd('swEvnNotifyProfListWindow').show({userMedStaffFact: this.userMedStaffFact});
                    }.createDelegate(this)
                },
				DrugRequestView: {
					text: lang['prosmotr_zayavok'],
					hidden: getRegionNick().inlist(['by']),
					tooltip: lang['prosmotr_zayavok'],
					iconCls: 'mp-drugrequest32',
					handler: function() {
						getWnd('swMzDrugRequestSelectWindow').show({
							ARMType: this.userMedStaffFact.ARMType
						});
					}.createDelegate(this)
				},
				DrugRequestPlanDelivery: {
					text: lang['plan_potrebleniya_mo'],
					tooltip: lang['plan_potrebleniya_mo'],
					iconCls: 'pill16',
					handler: function() {
						getWnd('swDrugRequestPlanDeliveryViewWindow').show();
					}
				},
				BskRegistry: {
					tooltip: lang['registr_po_bsk'],
					text: lang['registr_po_bsk'],
					iconCls : 'doc-reg16',
					/*hidden: (!getRegionNick().inlist([ 'ufa' ])),*/
					disabled: getRegionNick().inlist(['ufa']) ? false : (!isUserGroup("BSKRegistry")),
					handler: function()
					{   
					   
						if ( getWnd('swBskRegistryWindow').isVisible() ) {
							sw.swMsg.show({
								buttons: Ext.Msg.OK,
								fn: Ext.emptyFn,
								icon: Ext.Msg.WARNING,
								msg: lang['okno_uje_otkryito'],
								title: ERR_WND_TIT
							});
							return false;
						}
						getWnd('swBskRegistryWindow').show({userMedStaffFact: this.userMedStaffFact});
					}.createDelegate(this)
				},
				ReabRegistry: {
					tooltip: lang['registr_reability'],
					text: lang['registr_reability'],
					iconCls : 'doc-reg16',
					hidden: (getGlobalOptions().region.nick != 'ufa'),
					handler: function()
					{ 
						if ( getWnd('swReabRegistryWindow').isVisible() ) {
							sw.swMsg.show({
								buttons: Ext.Msg.OK,
								fn: Ext.emptyFn,
								icon: Ext.Msg.WARNING,
								msg: lang['okno_uje_otkryito'],
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
					menu: [{
						tooltip: 'Регистр подозрений на ЗНО',
						text: 'Регистр подозрений на ЗНО',
						iconCls : 'doc-reg16',
						hidden: (getGlobalOptions().region.nick != 'ufa'),
						handler: function() {
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
						getWnd('swZNOSuspectRegistryWindow').show({userMedStaffFact: this.userMedStaffFact});
						}.createDelegate(this)
					}, {
						tooltip: 'Периодические задания Регистра',
						text: 'Периодические задания Регистра',
						iconCls : 'doc-reg16',
						hidden: (!isSuperAdmin()),
						//disabled: (!isSuperAdmin()),
						//disabled: false,
						handler: function() {
							if ( getWnd('swZNOSuspectAdminWindow').isVisible() ) {
							sw.swMsg.show({
								buttons: Ext.Msg.OK,
								fn: Ext.emptyFn,
								icon: Ext.Msg.WARNING,
								msg: langs('Окно уже открыто'),
								title: ERR_WND_TIT
							});
							return false;
						}
						getWnd('swZNOSuspectAdminWindow').show();
						}.createDelegate(this)
					}]
//					handler: function()
//					{ 
//						if ( getWnd('swZNOSuspectRegistryWindow').isVisible() ) {
//							sw.swMsg.show({
//								buttons: Ext.Msg.OK,
//								fn: Ext.emptyFn,
//								icon: Ext.Msg.WARNING,
//								msg: langs('Окно уже открыто'),
//								title: ERR_WND_TIT
//							});
//							return false;
//						}
//						getWnd('swZNOSuspectRegistryWindow').show({userMedStaffFact: this.userMedStaffFact});
//					}.createDelegate(this)
				},
				OKSRegistry: {
					tooltip: lang['registr_po_oks'],
					text: lang['registr_po_oks'],
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
								msg: lang['okno_uje_otkryito'],
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
					tooltip: 'Регистр по ВРТ',
					text: 'Регистр по ВРТ',
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
				RegisterSixtyPlus: {
					tooltip: 'Регистр "Скрининг населения 60+"',
					text: 'Регистр "Скрининг населения 60+"',
					iconCls : 'doc-reg16',
					hidden: !getRegionNick().inlist(['perm', 'ufa']),
					handler: function()
					{   

							if ( getWnd('swRegisterSixtyPlusViewWindow').isVisible() ) {
									sw.swMsg.show({
											buttons: Ext.Msg.OK,
											fn: Ext.emptyFn,
											icon: Ext.Msg.WARNING,
											msg: 'Окно уже открыто',
											title: ERR_WND_TIT
									});
									return false;
							}
							getWnd('swRegisterSixtyPlusViewWindow').show({userMedStaffFact: this.userMedStaffFact});
					}.createDelegate(this)
				},
				EndoRegistry: {
					tooltip: lang['registr_po_endoprotezirovaniyu'],
					text: lang['registr_po_endoprotezirovaniyu'],
					iconCls : 'doc-reg16',
					disabled: !isUserGroup('EndoRegistry'),
					handler: function()
					{

						if ( getWnd('swEndoRegistryWindow').isVisible() ) {
							sw.swMsg.show({
								buttons: Ext.Msg.OK,
								fn: Ext.emptyFn,
								icon: Ext.Msg.WARNING,
								msg: lang['okno_uje_otkryito'],
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
								msg: lang['okno_uje_otkryito'],
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
								msg: lang['okno_uje_otkryito'],
								title: ERR_WND_TIT
							});
							return false;
						}
						getWnd('swPersonRegisterPalliatListWindow').show({userMedStaffFact: this.userMedStaffFact});
					}.createDelegate(this)
				},
				HTMRegister: {
					tooltip: langs('Регистр ВМП'),
					text: langs('Регистр ВМП'),
					iconCls : 'doc-reg16',
					hidden: !isUserGroup('SuperAdmin') && !isUserGroup('HTMRegister') && !isUserGroup('HTMRegister_Admin') || !getRegionNick().inlist(['ufa']),
					handler: function() {
						ShowWindow('swHTMRegisterWindow',{});
					}
				},
				SportRegistry: {
					tooltip: 'Регистр спортсменов',
					text: 'Регистр спортсменов',
					iconCls : 'doc-reg16',
					hidden: !isUserGroup('SportRegistry') || !getRegionNick().inlist(['ufa','perm']),
					handler: function()
					{
						if ( getWnd('swSportRegistryWindow').isVisible() ) {
							sw.swMsg.show({
								buttons: Ext.Msg.OK,
								fn: Ext.emptyFn,
								icon: Ext.Msg.WARNING,
								msg: 'Окно уже открыто',
								title: ERR_WND_TIT
							});
							return false;
						}
						getWnd("swSportRegistryWindow").show();
					}.createDelegate(this)
				},

				EvnDirectionHTMRegistry:
				{
					text: lang['HTM_registry'],
					tooltip: lang['HTM_registry'],
					iconCls: 'doc-reg16',
					hidden: getRegionNick().inlist(['ufa', 'kz']) || !isUserGroup('HTMRegistry'),

					handler: function()
					{
						var wnd = Ext.getCmp('swMPWorkPlaceWindow');

						getWnd('swEvnDirectionHTMRegistryWindow')
							.show({
								ARMType: 'common',
								userMedStaffFact: wnd.userMedStaffFact
							});
					}
				}
		};

		// Формирование списка всех акшенов 
		var configActions = 
		{
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
			},
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
			action_HomeVisitJournal:
			{
				nn: 'action_HomeVisitJournal',
				tooltip: lang['rabota_s_jurnalom_vyizova_na_dom'],
				text: lang['jurnal_vyizavov_na_dom'],
				iconCls : 'mp-region32',
				disabled: false,
				hidden: !IS_DEBUG,
				handler: function()
				{
					getWnd('swMPHomeVisitListWindow').show({
						userMedStaffFact: form.userMedStaffFact
					});
				}
			},
			action_SignalInfo:
			{
				nn: 'action_SignalInfo',
				tooltip: langs('Сигнальная информация для врача'),
				text: langs('Сигнальная информация для врача'),
				iconCls : 'epicrisis32',
				disabled: false,
				handler: function()
				{
					getWnd('swSignalInfoWindow').show({
						userMedStaffFact: form.userMedStaffFact
					});
				}
			},
            action_AddHomeVisit:{
                nn: 'action_AddHomeVisit',
                tooltip: lang['vyizov_vracha_na_dom'],
                text: lang['vyizov_vracha_na_dom'],
                iconCls: 'epl-new32',
                disabled: false,
                hidden: false,
                handler: function(){
                    getWnd('swPersonSearchWindow').show({
                        onSelect: function(personData) {
                            if ( personData.Person_id > 0 ) {
                                getWnd('swHomeVisitAddWindow').show({
                                    Person_id: personData.Person_id,
                                    Server_id: personData.Server_id,
                                    action:'add',
                                    Lpu_id: getGlobalOptions().lpu_id,
                                    callback : function() {
                                        //this.loadHomeVisits();
                                    }.createDelegate(this)
                                });
                            }
                            getWnd('swPersonSearchWindow').hide();
                        }.createDelegate(this)
                    });
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
			action_EvnPrescrMseJournal:
			{
				disabled: false, 
				handler: function() 
				{
					getWnd('swEvnPrescrMseJournalWindow').show({
						 userMedStaffFact: form.userMedStaffFact.MedStaffFact_id
					});
				},
				iconCls: 'mse-direction32',
				nn: 'action_EvnPrescrMseJournal',
				text: 'Журнал направлений на МСЭ',
				tooltip: 'Журнал направлений на МСЭ'
			},
			action_DispPlan: {
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
			action_Turn: 
			{
				nn: 'action_Turn',
				iconCls : 'mp-queue32',
				disabled: false, 
				text:  WND_DIRECTION_JOURNAL,
				tooltip:  WND_DIRECTION_JOURNAL,
				handler: function() {
					getWnd('swMPQueueWindow').show({
						ARMType: 'common',
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
			action_DrugRequestEditForm: 
			{
				nn: 'action_DrugRequestEditForm',
				tooltip: lang['zayavka_na_lekarstvennyie_sredstva'],
				text: lang['zayavka_na_lekarstvennyie_sredstva_vvod'],
				iconCls : 'mp-drugrequest32',
				disabled: false, 
				handler: function() 
				{
					form.showDrugRequestEditForm();
				}
			},
            /*action_drugrequestview: {
                text: lang['zayavka_na_lekarstvennyie_sredstva_prosmotr'],
				hidden: getRegionNick().inlist(['by']),
                tooltip: lang['prosmotr_zayavok'],
                iconCls: 'mp-drugrequest32',
                handler: function() {
                    getWnd('swMzDrugRequestSelectWindow').show();
                    //getWnd('swMzDrugRequestMoViewWindow').show();
                }
            },*/
            /*action_PrivilegeSearch:
			{
				nn: 'action_PrivilegeSearch',
				text: MM_DLO_LGOTSEARCH,
				tooltip: lang['poisk_lgotnikov'],
				iconCls : 'mp-lgot32',
				disabled: false,
				handler: function() 
				{
					form.getAttachDataShowWindow('swPrivilegeSearchWindow');
				}
			},*/
			action_PersDispSearchView: 
			{
				hidden: (getRegionNick() == 'kz'), 
				nn: 'action_PersDispSearchView',
				tooltip: lang['dispansernyiy_uchet'],
				text: WND_POL_PERSDISPSEARCHVIEW,
				iconCls : 'mp-disp32',
				disabled: (getRegionNick() == 'kz'), 
				handler: function() 
				{
					var grid = Ext.getCmp('swMPWorkPlaceWindow').getGrid(),
						selected_record = grid.getSelectionModel().getSelected(),
						Person_id = (selected_record && selected_record.get('Person_id')) || null,
						MedPersonal_id = this.userMedStaffFact.MedPersonal_id;
					getWnd('swPersonDispViewWindow').show({
						mode: 'view', Person_id: Person_id, MedPersonal_id: MedPersonal_id, view_one_doctor: true, ARMType: "common"
					});
				}.createDelegate(this)
			},
			action_monitoring:
			{
				nn: 'action_monitoring',
				tooltip: langs('Дистанционный мониторинг'),
				text: langs('Дистанционный мониторинг'),
				iconCls : 'dist-monitoring32',
				disabled: false, 
				handler: function() 
				{
					getWnd('swRemoteMonitoringWindow').show();
				}
			},
			action_PersCardSearch: 
			{
				nn: 'action_PersCardSearch',
				tooltip: WND_POL_PERSCARDSEARCH,
				text: WND_POL_PERSCARDSEARCH,
				iconCls : 'mp-region32',
				disabled:  false, 
				handler: function() 
				{
					form.getAttachDataShowWindow('swPersonCardSearchWindow');
				}
			},
			action_JournalHospit: 
			{
				nn: 'action_JournalHospit',
				tooltip: lang['otkryit_jurnal_gospitalizatsiy'],
				text: lang['jurnal_gospitalizatsiy'],
				iconCls : 'mp-hospital-list32',
				disabled: false,
				handler: function() 
				{
					if ( getWnd('swJournalHospitWindow').isVisible() ) {
						sw.swMsg.show({
							buttons: Ext.Msg.OK,
							fn: Ext.emptyFn,
							icon: Ext.Msg.WARNING,
							msg: lang['okno_uje_otkryito'],
							title: ERR_WND_TIT
						});
						return false;
					}
					getWnd('swJournalHospitWindow').show({userMedStaffFact: form.userMedStaffFact});
				}
			},
			action_CmpCallCardJournal:
			{
				nn: 'action_CmpCallCardJournal',
				hidden: getRegionNick().inlist(['by']),
				tooltip: lang['otkryit_jurnal_vyizovov_smp'],
				text: lang['jurnal_vyizovov_smp'],
				iconCls : 'emergency-list32',
				disabled: false,
				handler: function()
				{
					if ( getWnd('swCmpCallCardJournalWindow').isVisible() ) {
						sw.swMsg.show({
							buttons: Ext.Msg.OK,
							fn: Ext.emptyFn,
							icon: Ext.Msg.WARNING,
							msg: lang['okno_uje_otkryito'],
							title: ERR_WND_TIT
						});
						return false;
					}
					getWnd('swCmpCallCardJournalWindow').show({userMedStaffFact: form.userMedStaffFact});
				}
			},
			action_NotificationLogAdverseReactions:
			{
				nn: 'action_NotificationLogAdverseReactions',
				tooltip: 'Журнал извещений о неблагоприятных реакциях',
				text: 'Журнал извещений о неблагоприятных реакциях',
				iconCls : 'card-state32',
				disabled: false,
				hidden: !getRegionNick().inlist(['perm', 'astra', 'penza', 'krym']),
				handler: function()
				{
					if ( getWnd('swNotificationLogAdverseReactions').isVisible() ) {
						sw.swMsg.show({
							buttons: Ext.Msg.OK,
							fn: Ext.emptyFn,
							icon: Ext.Msg.WARNING,
							msg: lang['okno_uje_otkryito'],
							title: ERR_WND_TIT
						});
						return false;
					}
					getWnd('swNotificationLogAdverseReactions').show({userMedStaffFact: form.userMedStaffFact});
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
            action_Vaccination:
            {
                nn: 'action_Vaccination',
				hidden: getRegionNick().inlist(['by','perm']),
                tooltip: lang['otkryit_kartu_profilakticheskih_privivok'],
                text: lang['immunoprofilaktika'],
                iconCls : 'vac-plan32',
				disabled: false, 
				//hidden: (getRegionNick()=='perm'),
                handler: function()
                {
                    var grid = Ext.getCmp('swMPWorkPlaceWindow').getGrid(),
                        selected_record = grid.getSelectionModel().getSelected();
                    if (!selected_record || !selected_record.get('Person_id')) {
                        sw.swMsg.show({
                            buttons: Ext.Msg.OK,
                            fn: Ext.emptyFn,
                            icon: Ext.Msg.WARNING,
                            msg: lang['vyiberite_cheloveka'],
                            title: ERR_WND_TIT
                        });
                        return false;
                    }
                    getWnd('amm_Kard063').show({person_id: selected_record.get('Person_id')});
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
									,ARMType_id: this.userMedStaffFact.ARMType_id
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
								getWnd(win).hide();
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
							getWnd('swPersonSearchWindow').hide();
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
			action_FundHolding:
			{
				nn: 'action_FundHolding',
				tooltip: lang['otkryit_fondoderjanie'],
				text: lang['fondoderjanie'],
				iconCls : 'structure32',
				disabled: false,
				handler: function()
				{
					if ( getWnd('swFundHoldingViewForm').isVisible() ) {
						sw.swMsg.show({
							buttons: Ext.Msg.OK,
							fn: Ext.emptyFn,
							icon: Ext.Msg.WARNING,
							msg: lang['okno_uje_otkryito'],
							title: ERR_WND_TIT
						});
						return false;
					}
					getWnd('swFundHoldingViewForm').show();
				}
			},
			action_QueryEvn:
			{
				disabled: false, 
				handler: function() 
				{
					getWnd('swQueryEvnListWindow').show({ARMType: form.ARMType});
				},
				iconCls: 'mail32',
				nn: 'action_QueryEvn',
				text: 'Журнал запросов',
				tooltip: 'Журнал запросов'
			},
			action_References: {
				nn: 'action_References',
				tooltip: lang['spravochniki'],
				text: lang['spravochniki'],
				iconCls : 'book32',
				disabled: false,
				menuAlign: 'tr?',
				menu: new Ext.menu.Menu({
					items: [
                        {
                            text: lang['spravochnik_uslug'],
                            tooltip: lang['spravochnik_uslug'],
                            iconCls: 'services-complex16',
                            handler: function() {
                                getWnd('swUslugaTreeWindow').show({action: 'view'});
                            }
                        },{
						tooltip: lang['mkb-10'],
						text: lang['spravochnik_mkb-10'],
						iconCls: 'spr-mkb16',
						handler: function() {
							if ( !getWnd('swMkb10SearchWindow').isVisible() )
								getWnd('swMkb10SearchWindow').show();
						}
                        }, {
                            name: 'action_DrugNomenSpr',
                            text: lang['nomenklaturnyiy_spravochnik'],
                            iconCls : '',
                            handler: function()
                            {
                                getWnd('swDrugNomenSprWindow').show();
                            }
                        }, {
                            name: 'action_PriceJNVLP',
                            hidden: getRegionNick().inlist(['by']),
                            text: lang['tsenyi_na_jnvlp'],
                            iconCls : 'dlo16',
                            handler: function() {
                                getWnd('swJNVLPPriceViewWindow').show();
                            }
                        }, {
                            name: 'action_DrugMarkup',
                            hidden: getRegionNick().inlist(['by']),
                            text: lang['predelnyie_nadbavki_na_jnvlp'],
                            iconCls : 'lpu-finans16',
                            handler: function() {
                                getWnd('swDrugMarkupViewWindow').show({readOnly: true});
                            }
                        },
						sw.Promed.Actions.swPrepBlockSprAction,
						sw.Promed.Actions.swDrugDocumentSprAction
                    ]
				})
			},
			action_DLO: 
			{
				nn: 'action_DLO',
				tooltip: lang['llo'],
				text: lang['llo'],
				iconCls : 'dlo32',
				disabled: false, 
				menuAlign: 'tr?',
				menu: new Ext.menu.Menu({
					items: [
						swPromedActions.OrgFarmacyByLpuView,
						swPromedActions.PrivilegeSearch,
						swPromedActions.EvnReceptInCorrectFind,
						swPromedActions.OstAptekaViewAction,
						swPromedActions.OstDrugViewAction,
						swPromedActions.DrugRequestView,
						swPromedActions.DrugOstat,
						swPromedActions.DrugOstatAptRAS,
						swPromedActions.DrugRequestPlanDelivery
					]
				})
			},
			action_mmunoprof: 
			{
				nn: 'action_mmunoprof',
				tooltip: lang['immunoprofilaktika'],
				text: lang['immunoprofilaktika'],
				iconCls : 'immunoprof32',
				disabled: false, 
				menuAlign: 'tr?',
				menu: new Ext.menu.Menu({
					items: [
						swPromedActions.mainForm,
						swPromedActions.PresenceVacForm,
						swPromedActions.SprVaccineForm
					]
				})
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
                        swPromedActions.EvnNotifyNolos,
                        swPromedActions.EvnNotifyPalliat
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
						swPromedActions.PregnancyRegistry,
						swPromedActions.CVIRegistry,
						{
						text:lang['registr_detey-sirot'],
						iconCls: 'doc-reg16',
						hidden: getRegionNick().inlist(['by','kz']),
						menu: new Ext.menu.Menu({
							items: [{
								text: lang['registr_detey-sirot_statsionarnyih'],
								tooltip: lang['registr_detey-sirot_statsionarnyih'],
								iconCls : 'doc-reg16',
								handler: function() {
									getWnd('swPersonDispOrp13SearchWindow').show({
										CategoryChildType: 'orp'
									});
								}
							},{
								text: lang['registr_detey-sirot_usyinovlennyih_opekaemyih'],
								tooltip: lang['registr_detey-sirot_usyinovlennyih_opekaemyih'],
								iconCls : 'doc-reg16',
								handler: function() {
									getWnd('swPersonDispOrp13SearchWindow').show({
										CategoryChildType: 'orpadopted'
									});
								}
							}]
						})
					},
						swPromedActions.PersonPrivilegeWOWSearch,
						swPromedActions.PersonDopDispSearch,
						swPromedActions.EvnPLDispTeen14Search,
						swPromedActions.HepatitisRegistry,
						swPromedActions.OnkoRegistry,
						swPromedActions.OrphanRegistry,
						swPromedActions.CrazyRegistry,
						swPromedActions.NarkoRegistry,
						swPromedActions.DiabetesRegistry,
						swPromedActions.TubRegistry,
						swPromedActions.VenerRegistry,
						swPromedActions.HIVRegistry,
                        swPromedActions.NephroRegistry,
						swPromedActions.ProfRegistry,
						swPromedActions.IBSRegistry,
						swPromedActions.NolosRegistry,
                        swPromedActions.BskRegistry,
						swPromedActions.ReabRegistry,
						swPromedActions.ZNOSuspectRegistry,
						swPromedActions.IPRARegistry,
						swPromedActions.OKSRegistry,
                        swPromedActions.EndoRegistry,
                        swPromedActions.SuicideRegistry,
						swPromedActions.PalliatRegistry,
						swPromedActions.action_MonitorBirthSpec,
						swPromedActions.ECORegistry,
						swPromedActions.RegisterSixtyPlus,
						swPromedActions.GeriatricsRegistry,
						swPromedActions.HTMRegister,
						swPromedActions.SportRegistry,
						swPromedActions.PreOnkoRegistry,
						swPromedActions.EvnDirectionHTMRegistry
					]
				})
			},
			action_DopDisp: 
			{
				nn: 'action_DopDisp',
				hidden: getRegionNick().inlist(['by']),
				tooltip: lang['dispanserizatsiya'],
				text: lang['dispanserizatsiya'],
				iconCls : 'mp-disp32',
				disabled: false, 
				menuAlign: 'tr?',
				menu: new Ext.menu.Menu({
					items: [{
						text:lang['dispanserizatsiya_vzroslogo_naseleniya'],
						iconCls: 'pol-dopdisp16',
						hidden: getRegionNick().inlist(['by','kz']),
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
						hidden: getRegionNick().inlist(['by','kz']),
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
						hidden: getRegionNick().inlist(['by','kz']),
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
						hidden: getRegionNick().inlist(['by','kz']),
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
						hidden: getRegionNick().inlist(['by','kz']),
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
					}, {
						text:lang['dispansernyiy_uchet'],
						iconCls: 'pol-disp16',
						hidden: !getRegionNick().inlist(['kz']),
						menu: new Ext.menu.Menu({
							items: [
								{
									text: WND_POL_PERSDISPSEARCH,
									tooltip: langs('Поиск диспансерной карты пациента'),
									iconCls : 'disp-search16',
									handler: function()
									{
										getWnd('swPersonDispSearchWindow').show({ARMType: 'common'});
									}
								},
								{
									text: WND_POL_PERSDISPSEARCHVIEW,
									tooltip: langs('Просмотр диспансерной карты пациента'),
									iconCls : 'disp-view16',
									handler: function()
									{
										getWnd('swPersonDispViewWindow').show({mode: 'view', ARMType: 'common'});
									}
								}
								/*
								sw.Promed.Actions.PersonDispSearchAction,
								sw.Promed.Actions.PersonDispViewAction
								*/
							]
						})
					}, {
						text:'Медицинское освидетельствование мигрантов',
						iconCls: 'pol-dopdisp16',
						hidden: !isUserGroup('MedOsvMigr'),
						handler: function() {
							getWnd('swEvnPLDispMigrSearchWindow').show();
						}
					}, {
						text:'Медицинское освидетельствование водителей',
						iconCls: 'pol-dopdisp16',
						hidden: !isDrivingCommission(),
						handler: function() {
							getWnd('swEvnPLDispDriverSearchWindow').show();
						}
					}]
				})
			},
			action_ScreenSearch:
			{
				nn: 'action_ScreenSearch',
				hidden: !getRegionNick().inlist(['kz']),
				tooltip: lang['skriningovyie_issledovaniya'],
				text: lang['skriningovyie_issledovaniya'],
				iconCls : 'mp-disp32',
				disabled: false,
				menuAlign: 'tr?',
				menu: new Ext.menu.Menu({
					items: [
						sw.Promed.Actions.EvnPLDispScreenSearchAction,
						sw.Promed.Actions.EvnPLDispScreenChildSearchAction
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
			action_MedSvidDeath: {
				handler: function() {
					getWnd('swMedSvidDeathStreamWindow').show();
				},
				//hidden: !isUserGroup('MedSvidDeath'),
				iconCls : 'medsvid32',
				nn: 'action_MedSvidDeath',
				text: WND_FRW,
				tooltip: lang['med_svidetelstva_o_smerti']
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
						sw.Promed.Actions.EvnMorfoHistologicProtoViewAction,
						'-',
						sw.Promed.Actions.DirectionsForCytologicalDiagnosticExaminationViewAction,
						sw.Promed.Actions.CytologicalDiagnosticTestProtocolsViewAction
					]
				})
			},
            action_Templ: {
                handler: function() {
                    var params = {
                        EvnClass_id: 11,
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
			action_Newslatter: {
				handler: function() {
					getWnd('swNewslatterListWindow').show();
				}.createDelegate(this),
				hidden: !isUserGroup('Newslatter'),
				iconCls: 'mail32',
				nn: 'action_Newslatter',
				id: 'wpprw_action_Newslatter',
				text: 'Управление рассылками',
				tooltip: 'Управление рассылками'
			},
			action_VKJournal: {
				handler: function() {
					getWnd('swVKJournalWindow').show({
						userMedStaffFact: form.userMedStaffFact
					});
				},
				hidden: !getRegionNick().inlist(['perm', 'vologda']) || !isUserGroup('DepHead'),
				iconCls: 'vkqueryjournal32',
				nn: 'action_VKJournal',
				text: 'Журнал запросов ВК',
				tooltip: 'Журнал запросов ВК'
			},
			action_Ers: {
				nn: 'action_Ers',
				text: 'ЭРС',
				menuAlign: 'tr',
				tooltip: 'ЭРС',
				iconCls: 'ers32',
				// todo
				//hidden: !(isUserGroup('WorkGraph')),
				/*Кнопка доступна для пользователей, включенных в любую из групп доступа:
				•	ЭРС. Оформление документов
				•	ЭРС. Руководитель МО
				•	ЭРС. Бухгалтер*/
				menu: [{
					text: 'Журнал Родовых сертификатов',
					handler: function () {
						getWnd('swEvnErsJournalWindow').show();
					}
				}, {
					text: 'Журнал Талонов',
					handler: function () {
						getWnd('swEvnErsTicketJournalWindow').show();
					}
				}, {
					text: 'Журнал учета детей',
					handler: function () {
						getWnd('swEvnErsChildJournalWindow').show();
					}
				}, {
					text: 'Реестры талонов и счета на оплату',
					handler: function () {
						getWnd('swErsRegistryJournalWindow').show();
					}
				}]
			},
			action_CarAccidents: {
				iconCls : 'stac-accident-injured16',
				nn: 'action_CarAccidents',
				text: lang['izvescheniya_o_dtp'],
				tooltip: lang['izvescheniya_o_dtp'],
				menu: new Ext.menu.Menu({
					items: [
						{
							text: lang['izvescheniya_dtp_o_ranenom_prosmotr'],
							tooltip: lang['izvescheniya_dtp_o_ranenom_prosmotr'],
							iconCls: 'stac-accident-injured16',
							handler: function()
							{
								getWnd('swEvnDtpWoundWindow').show();
							},
							hidden: false
						},
						{
							text: lang['izvescheniya_dtp_o_skonchavshemsya_prosmotr'],
							tooltip: lang['izvescheniya_dtp_o_skonchavshemsya_prosmotr'],
							iconCls: 'stac-accident-dead16',
							handler: function()
							{
								getWnd('swEvnDtpDeathWindow').show();
							},
							hidden: false
						}
					]
				})
			}
		};
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
		var actions_list = ['action_Turn'/*,'action_DrugRequestEditForm'*/,/*'action_drugrequestview'*/, /*'action_PrivilegeSearch',*/'action_PersCardSearch','action_Timetable','action_HomeVisitJournal','action_SignalInfo','action_AddHomeVisit','action_MidMedPersonal','action_EvnPrescrMseJournal','action_JournalHospit', 'action_CmpCallCardJournal'/*,'action_UslugaComplexTree', 'action_EvnVKJournal'*/, (getRegionNick() == 'perm')?'':'action_Vaccination', 'action_FundHolding', 'action_QueryEvn', 'action_DLO', 'action_References', 'action_Notify',(getRegionNick() == 'ufa')?'action_mmunoprof':'', 'action_Register', 'action_DopDisp','action_JourNotice', 'action_FindRegions',(isUserGroup('MedSvidDeath'))?'action_MedSvidDeath':'', 'action_PathoMorph', 'action_Templ','action_Svid','action_reports', (getRegionNick() != 'kz')?'action_CarAccidents':'', 'action_Newslatter', 'action_Ers'];
		var blocked_list = [];
		if (getRegionNick().inlist(['perm', 'vologda']) && isUserGroup('DepHead')) {
			actions_list.push('action_VKJournal');
		}
		if (getRegionNick() == 'pskov') {
			actions_list.push('action_DirectionPerson');
			//blocked_list.push('action_DrugRequestEditForm');
		}
		if (getRegionNick() != 'kz') {
			actions_list.push('action_PersDispSearchView');
		}
		if (getRegionNick() == 'kz') {
			actions_list.push('action_ScreenSearch');
		}
		if(getRegionNick() == 'vologda') {
			actions_list.push('action_monitoring');
		}
		if(getRegionNick() == 'penza') {
			actions_list.push('action_DispPlan');
		}

		actions_list.push('action_NotificationLogAdverseReactions');
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
			gridRefreshFn: function(options){ wnd.scheduleRefresh(options);}, // связываем метод обновления грида
			applyCallActionFn: function(){ wnd.scheduleOpen();}, // связываем метод открытия эмки
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
		sw.Promed.swMPWorkPlaceWindow.superclass.initComponent.apply(this, arguments);
	}
						});
