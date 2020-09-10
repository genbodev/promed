/**
 * swMPWorkPlaceStacWindow - окно рабочего места врача стационара
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/projects/promed
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009-2011, Swan.
 * @author       Марков Андрей, Александр Пермяков
 * @prefix       mpwp
 * @version      07.2013
 */

/*NO PARSE JSON*/


sw.Promed.swMPWorkPlaceStacWindow = Ext.extend(sw.Promed.BaseForm, {
	//useUecReader: true,
	codeRefresh: true,
	objectName: 'swMPWorkPlaceStacWindow',
	objectSrc: '/jscore/Forms/Common/swMPWorkPlaceStacWindow.js',
	closable: true,
	closeAction: 'hide',
	maximized: true,
	title: WND_WPMP,
	iconCls: 'workplace-mp16',
	id: 'swMPWorkPlaceStacWindow',
	readOnly: false,
	//тип АРМа, определяется к каким функциям будет иметь доступ врач через ЭМК
	ARMType: null,
	curDate: null,
	curTime: null,
	userName: null,
	//объект с параметрами рабочего места, с которыми была открыта форма
	userMedStaffFact: null,
	firstLoad: true,
	//вариант отображения периода (день, неделя, месяц) по умолчанию
	mode: 'day',

	timeoutMsgKVCbezVrachaMore24hInterval: 3600000,
	windowMsgKVCbezVrachaMore24h: null,
	clearMsgKVCbezVrachaMore24hInterval: null,
	configMsgKVCbezVrachaMore24h: {
		isReturn:true,
		closable: true,
		delay: 100000000000000,
		bodyStyle: 'text-align:left; margin-left:7px; padding: 0px 0px 20px 20px;background:transparent'
	},

	createMsgKVCbezVrachaMore24h: function(kvc_list){

		var msg = 'В КВС';
		msg += ' ';

		var prefix = '';
		for(var i=0;i<=kvc_list.length;i++){
			if(typeof kvc_list[i] == 'object' && ! Ext.isEmpty(kvc_list[i])){
				var EvnPS_id = kvc_list[i]['EvnPS_id'];
				var Server_id = kvc_list[i]['Server_id'];
				var Person_id = kvc_list[i]['Person_id'];
				var EvnPS_NumCard = kvc_list[i]['EvnPS_NumCard'];
				var fio = kvc_list[i]['fio'];

				msg += prefix + fio + ' + (<a href="javascript:getWnd(\'swEvnPSEditWindow\').show({action: \'edit\', Person_id: ' + Person_id + ', Server_id: ' + Server_id + ', EvnPS_id: ' + EvnPS_id + '});">№ ' + EvnPS_NumCard + '</a>)';
				prefix = ', ';
			}
		}
		msg += ' ';
		msg += 'есть движения без указания врача. Заполните поле «Врач» для корректной передачи данных в государственные сервисы';

		return msg;

	},



	showMsgKVCbezVrachaMore24h: function(kvc_list){
		var me = this;

		var msg = me.createMsgKVCbezVrachaMore24h(kvc_list);

		me.windowMsgKVCbezVrachaMore24h = showSysMsg(msg, null, null, me.configMsgKVCbezVrachaMore24h);

		return true;

	},
	
	hideWindowMsgKVCbezVrachaMore24h: function(){
		var me = this;
		
		if(me.windowMsgKVCbezVrachaMore24h){
			me.windowMsgKVCbezVrachaMore24h.hide();
		}

		return true;
	},

	getKVCbezVrachaMore24h: function(){

		var me = this;

		me.hideWindowMsgKVCbezVrachaMore24h();

		Ext.Ajax.request({
			url: '/?c=EvnSection&m=getKVCbezVrachaMore24h',
			params: {
				LpuSection_id: me.userMedStaffFact.LpuSection_id
			},
			success: function(result_form, action){
				var response = Ext.util.JSON.decode(result_form.responseText);

				if(response.length > 0  && ! Ext.isEmpty(response)){
					me.showMsgKVCbezVrachaMore24h(response);
				}

			},
			failure: function(result_form, action){
				sw.swMsg.alert(langs('Ошибка'), action.result.Message);
			}
		});
	},

	// создание различного вида меню
	setMenu: function() {
		var menu;
		menu = sw.Promed.MedPersonal.getMenu({
			LpuSection_id: this.findById('mpwpsSearch_LpuSection_id').getValue(),
			id: 'ListMenuMedPersonal',
			getParams: function(){
				var params = {};
				var node = this.Tree.getSelectionModel().selNode;
				if (node && node.attributes && node.attributes.EvnSection_id) {
					params.LpuSection_id = this.findById('mpwpsSearch_LpuSection_id').getValue();
					params.EvnSection_id = node.attributes.EvnSection_id;
					params.EvnSection_pid = null;
					params.Person_id = node.attributes.Person_id;
					params.PersonEvn_id = node.attributes.PersonEvn_id;
					params.Server_id = node.attributes.Server_id;
					params.MedPersonalCur_id = node.attributes.MedPersonal_id;
				}
				return params;
			}.createDelegate(this),
			onSuccess: function(){
				this.reloadTree();
			}.createDelegate(this)
		});
        this.getTreeAction('update_doctor').each(function(cmp){
            cmp.menu = menu;
        }, this);
		this.createListLeaveType();
	},
	
	getTreeAction: function(name) {
		if( this.treeActions[name] ) {
			return this.treeActions[name];
		}
		var actions = this.treeActions['actions'].menu;
		return actions[name] || null;
	},
	
	show: function()
	{

		var me = this;

		sw.Promed.swMPWorkPlaceStacWindow.superclass.show.apply(this, arguments);
		// Проверяем права пользователя открывшего форму

		if ((!arguments[0]) || (!arguments[0].userMedStaffFact) || (!arguments[0].userMedStaffFact.ARMType))
		{
			this.hide();
			Ext.Msg.alert('Ошибка открытия формы', 'Ошибка открытия формы "'+this.title+'".<br/>Не указан тип АРМа врача.');
			return false;
		} else {
			this.ARMType = arguments[0].userMedStaffFact.ARMType;
			this.userMedStaffFact = arguments[0].userMedStaffFact;
			// общий режим отображения рабочего места врача стационара, независимо от LpuUnitType
			//this.ARMType = 'stac';
		}
		me.showJourBedDowntime = true;
		if(this.ARMType == 'reanimation' || getRegionNick() != 'msk') me.showJourBedDowntime = false;

		Ext.getCmp("action_JourBedDowntime").setVisible(me.showJourBedDowntime);

		// refs #127324 На форму АРМ врача стационара добавить всплывающее сообщение (не блокирует форму) согласно п. 2.3.2
		if(getGlobalOptions().region.nick == 'kz'){
			me.getKVCbezVrachaMore24h();
			me.clearMsgKVCbezVrachaMore24hInterval = setInterval(function() {
				me.getKVCbezVrachaMore24h();
			}, me.timeoutMsgKVCbezVrachaMore24hInterval);
		}





        this.emptyevndoc();
        this.resetFilter();
		var lpusection_combo = Ext.getCmp('mpwpsSearch_LpuSection_id');
		if ( lpusection_combo.getStore().getCount() == 0 ) {
			setLpuSectionGlobalStoreFilter({
				allowLowLevel: 'yes',
				isStac: true
			});
			lpusection_combo.getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
		}
		lpusection_combo.setValue(this.userMedStaffFact.LpuSection_id);
        lpusection_combo.fireEvent('change', lpusection_combo, this.userMedStaffFact.LpuSection_id, null);
		lpusection_combo.disable();

		var medstafffact_combo = this.findById('mpwpsSearch_MedStaffFact_id');
        medstafffact_combo.getStore().removeAll();
        setMedStaffFactGlobalStoreFilter({
            isStac: true,
            LpuSection_id: this.userMedStaffFact.LpuSection_id
        });
        medstafffact_combo.getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
		//medstafffact_combo.setValue(this.userMedStaffFact.MedStaffFact_id);

        // Создаем свой заголовок, единый для всех армов, на основании данных пришедших с сервера ( из User_model)
        sw.Promed.MedStaffFactByUser.setMenuTitle(this, this.userMedStaffFact);
        //this.setTitle(WND_WPMP + '. Отделение: ' + this.userMedStaffFact.LpuSection_Name + '. Врач: ' + this.userMedStaffFact.MedPersonal_FIO);
        this.TopPanel.show();
		this.firstLoad = true;
		// Меню для кнопок
		this.setMenu();
		// Переключатель
		this.syncSize();
		this.getCurrentDateTime();
		// Кол-во коек
		//this.loadNumberBeds();
                //
		//BOB - 15.03.2017
        if(this.ARMType == 'stac')  {
 			//BOB - 05.06.2018
            this.getTreeAction('accept').show();
            this.getTreeAction('update_ward').show();
            this.getTreeAction('update_doctor').show();
            this.getTreeAction('leave').show();
            this.getTreeAction('add_patient').show();
			//BOB - 05.06.2018
        	this.getTreeAction('move_reanimation').disable();
			lpusection_combo.show();       //BOB - 23.10.2019
			medstafffact_combo.show();     //BOB - 23.10.2019
		}
        else {
			//BOB - 05.06.2018
            this.getTreeAction('accept').hide();
            this.getTreeAction('update_ward').hide();
            this.getTreeAction('update_doctor').hide();
            this.getTreeAction('leave').hide();
            this.getTreeAction('add_patient').hide();
			//BOB - 05.06.2018
        	this.getTreeAction('move_reanimation').enable();
			lpusection_combo.hide();       //BOB - 23.10.2019
			medstafffact_combo.hide();     //BOB - 23.10.2019
		}
        this.getTreeAction('end_reanimat_period').disable(); //BOB - 17.11.2017
        this.getTreeAction('del_reanimat_period').disable(); //BOB - 12.05.2017
        this.getTreeAction('edit_reanimat_period').disable(); //BOB - 19.06.2019
		this.getTreeAction('change_reanimat_period').disable(); //BOB - 02.10.2019

		if(getRegionNick() != 'kz' && sw.Promed.Actions.VideoChatBtn) {
			me.VideoChatBtn = sw.Promed.Actions.VideoChatBtn;
			me.VideoChatBtn.show();
		}
	}, // end show()
	createEvnPS: function(pdata)
	{
		var win = this;
		
//		//BOB - 29.05.2018	- закомментарил 01.06.2018 т.к. пока не решили нужна ли вообще функция «Поступление в Реанимацию миную приёмное отделение»
//		var vLpuSection_id = '';
//		win.getLoadMask(LOAD_WAIT).show();
//		if (win.ARMType == 'reanimation') {
//			$.ajax({
//				mode: "abort",
//				type: "post",
//				async: false,
//				url: '/?c=EvnReanimatPeriod&m=getProfilSectionId',
//				data: {	
//						MedService_id: win.userMedStaffFact.MedService_id,
//						Lpu_id: win.userMedStaffFact.Lpu_id
//					  },
//				success: function(response) {
//					vLpuSection_id = Ext.util.JSON.decode(response);
//					win.getLoadMask(LOAD_WAIT).hide();
//				}, 
//				error: function() {
//					win.getLoadMask(LOAD_WAIT).hide();
//					sw.swMsg.alert(langs('Сообщение'), 'При обработке запроса на сервере произошла ошибка!');
//				} 
//			});				
//			if(Ext.isEmpty(vLpuSection_id)){
//				sw.swMsg.alert(langs('Сообщение'), 'Не найдено профильное отделение, обслуживаемое службой реанимации!');
//				return false;
//			}
//		} //BOB - 29.05.2018	
		
		getWnd('swEvnPSEditWindow').show({
			action: 'add',
			Person_id: pdata.Person_id,
			PersonEvn_id: pdata.PersonEvn_id,
			Server_id: pdata.Server_id,
			LpuSection_id: this.ARMType == 'stac' ? win.userMedStaffFact.LpuSection_id : vLpuSection_id, //BOB - 29.05.2018
			MedPersonal_id: win.userMedStaffFact.MedPersonal_id,
			onHide: function(evn_ps_id) {
			//				//BOB - 29.05.2018 - закомментарил 01.06.2018 т.к. пока не решили нужна ли вообще функция «Поступление в Реанимацию миную приёмное отделение»
			//				if (win.ARMType == 'reanimation') {
			//					win.moveToReanimationOutPriem({
			//						Person_id: pdata.Person_id,
			//						PersonEvn_id: pdata.PersonEvn_id,
			//						Server_id: pdata.Server_id,
			//						EvnPS_id: evn_ps_id
			//					});
			//				}
			//				else {
			//				//BOB - 29.05.2018				
				win.reloadTree();
				win.emptyevndoc();
			//				} //BOB - 29.05.2018 - закомментарил 01.06.2018 т.к. пока не решили нужна ли вообще функция «Поступление в Реанимацию миную приёмное отделение»
			}
			,EvnDirectionData: pdata.EvnDirectionData || null
			//чтобы создавалось движение при заполнении LpuSection_id
			,form_mode: 'arm_stac_add_patient'
			,EvnPS_setDate: getGlobalOptions().date
		});
	},
	
	addPatient: function(params)
	{
		if (getWnd('swPersonSearchWindow').isVisible()) {
			Ext.Msg.alert(langs('Сообщение'), langs('Окно поиска человека уже открыто'));
			return false;
		}
		
		var win = this,
		personParams = {
			onSelect: function(pdata) {
				getWnd('swPersonSearchWindow').hide();
                //нужно проверить сущестование открытых КВС на этого пациента

                Ext.Ajax.request({
                    url: '/?c=EvnPS&m=beforeOpenEmk',
                    params: {Person_id: pdata.Person_id},
                    failure: function() {
                        showSysMsg(langs('При получении данных для проверок произошла ошибка!'));
                    },
                    success: function(response)
                    {
                        if (response.responseText) {
                            var answer = Ext.util.JSON.decode(response.responseText);
                            if(!Ext.isArray(answer) || !answer[0])
                            {
                                showSysMsg(langs('При получении данных для проверок произошла ошибка! Неправильный ответ сервера.'));
                                return false;
                            }
                            if (answer[0].countOpenEvnPS > 0)
                            {
                                showSysMsg(langs('Создание новых КВС недоступно'),langs('У пациента имеются открытые КВС в даннном ЛПУ! Количество открытых КВС: ')+ answer[0].countOpenEvnPS);
                                return false;
                            }

							Ext.Ajax.request({
								params: {
									useCase: 'create_evnps_from_workplacestac',
									LpuSection_id: win.userMedStaffFact.LpuSection_id,
									Person_id: pdata.Person_id
								},
								callback: function(opt, success, response) {
									var response_obj = Ext.util.JSON.decode(response.responseText);
									if ( Ext.isArray(response_obj) ) {
										if ( response_obj.length > 0 ) {
											// выводим список этих направлений с возможностью выбрать одно из них
											getWnd('swEvnDirectionSelectWindow').show({
												useCase: 'create_evnps_from_workplacestac',
												storeData: response_obj,
												formType: 'stac',
												Person_Birthday: pdata.Person_Birthday,
												Person_Firname: pdata.Person_Firname,
												Person_Secname: pdata.Person_Secname,
												Person_Surname: pdata.Person_Surname,
												Person_id:pdata.Person_id,
												callback: function(evnDirectionData){
													if (evnDirectionData && evnDirectionData.EvnDirection_id){
														// создавать КВС со связью с направлением
														pdata.EvnDirectionData=evnDirectionData;
													} else {
														// создать КВС без связи с направлением
														pdata.EvnDirectionData=null;
													}
												},
												onHide: function(){
													// если направление не выбрано, то создавать КВС без связи с направлением
													win.createEvnPS(pdata);
												}
											});
										} else {
											// создать КВС без связи с направлением
											pdata.EvnDirectionData=null;
											win.createEvnPS(pdata);
										}
									} else {
										showSysMsg(langs('При получении данных для проверок произошла ошибка! Неправильный ответ сервера.'));
										return false;
									}
								},
								url: '/?c=EvnDirection&m=loadEvnDirectionList'
							});
						} else {
							showSysMsg(langs('При получении данных для проверок произошла ошибка! Отсутствует ответ сервера.'));
							return false;
						}
					}
				});
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

		if (getRegionNick() == 'ufa') {
			personParams.allowUnknownPerson = true;
		}
		
		if (getRegionNick() == 'kz') {
			var onSelect = personParams.onSelect;
			personParams.onSelect = function(pdata) {
				var conf = {
					Person_id: pdata.Person_id,
					PersonEvn_id: pdata.PersonEvn_id,
					Server_id: pdata.Server_id,
					win: this,
					callback: function() {
						onSelect(pdata);
					}
				};
				sw.Promed.PersonPrivilege.checkExists(conf);
			}.createDelegate(this);
		}

		getWnd('swPersonSearchWindow').show(personParams);
	},
	listeners: {
		activate: function(){
			sw.Applets.uec.startUecReader({callback: this.getDataFromUec.createDelegate(this)});
			sw.Applets.bdz.startBdzReader({callback: this.getDataFromBdz.createDelegate(this)});
			sw.Applets.BarcodeScaner.startBarcodeScaner({callback: this.getDataFromBarScan.createDelegate(this), ARMType: 'stac'});
		},
		deactivate: function() {
			sw.Applets.uec.stopUecReader();
			sw.Applets.bdz.stopBdzReader();
			sw.Applets.BarcodeScaner.stopBarcodeScaner();
		},
		hide: function(wnd){
			if(wnd.clearMsgKVCbezVrachaMore24hInterval){
				clearInterval(wnd.clearMsgKVCbezVrachaMore24hInterval);
			}
			if(getRegionNick() != 'kz' && this.VideoChatBtn) {
				this.VideoChatBtn.hide();
			}
		}
	},
	getDataFromBarScan: function(person_data) {
		var _this = this,
			base_form = this.FilterPanel.getForm();

		if (!Ext.isEmpty(person_data.Person_Surname)){
			base_form.findField('Person_SurName').setValue(person_data.Person_Surname);
		} else {
			base_form.findField('Person_SurName').setValue(null);
		}

		if (!Ext.isEmpty(person_data.Person_Firname)){
			base_form.findField('Person_FirName').setValue(person_data.Person_Firname);
		} else {
			base_form.findField('Person_FirName').setValue(null);
		}

		if (!Ext.isEmpty(person_data.Person_Secname)){
			base_form.findField('Person_SecName').setValue(person_data.Person_Secname);
		} else {
			base_form.findField('Person_SecName').setValue(null);
		}

		if (!Ext.isEmpty(person_data.Person_Birthday)){
			base_form.findField('Person_BirthDay').setValue(person_data.Person_Birthday);
		} else {
			base_form.findField('Person_BirthDay').setValue(null);
		}

		var params = {
			filter_Person_F: person_data.Person_Surname,
			filter_Person_I: person_data.Person_Firname,
			filter_Person_O: person_data.Person_Secname,
			filter_PSNumCard: person_data.Person_PSNumCard,
			filter_Person_BirthDay: person_data.Person_Birthday,
			object_value: -3, // все пациенты
			date: Ext.util.Format.date(Ext.getCmp('mpwpsSearch_date').getValue(), 'd.m.Y'),
			LpuSection_id: Ext.getCmp('mpwpsSearch_LpuSection_id').getValue()
		};

		Ext.Ajax.request({
			url: '/?c=EvnSection&m=getLpuSectionPatientList',
			params: params,
			callback: function(options, success, response)
			{
				if(success)
				{
					if(response.responseText != '')
					{
						var result  = Ext.util.JSON.decode(response.responseText);

						if (result.length == 1) {
							log(result);
							log(result[0]);
							_this.OpenEMK(result[0]);
						} else if (result.length == 0) {
							_this.addPatient(person_data);
						}
						log('result');
						log(result);
					}
				} else {
					sw.swMsg.alert(langs('Сообщение'), langs('Ошибка при получении записанных пациентов'));
				}
			}
		});

		this.personData = person_data;
		this.doSearch();

	},
	menuLpuSectionWard: null,
	/** Создание меню палат
	 */
	createListLpuSectionWard: function() {
		sw.Promed.LpuSectionWard.createListLpuSectionWard({
			LpuSection_id: this.findById('mpwpsSearch_LpuSection_id').getValue(),
			date: Ext.util.Format.date(Ext.getCmp('mpwpsSearch_date').getValue(), 'd.m.Y'),
			id: 'ListMenuWard',
			getParams: function(){
				var params = {};
				var node = this.Tree.getSelectionModel().selNode;
				if (node && node.attributes && node.attributes.EvnSection_id) {
					params.LpuSection_id = this.findById('mpwpsSearch_LpuSection_id').getValue();
					params.EvnSection_id = node.attributes.EvnSection_id;
					params.ignore_sex = false;
					params.Sex_id = node.attributes.Sex_id;
					params.Person_id = node.attributes.Person_id;
					params.LpuSectionWardCur_id = node.attributes.LpuSectionWard_id;
				}
				return params;
			}.createDelegate(this),
			callback: function(menu){
				this.menuLpuSectionWard = menu;
                this.getTreeAction('update_ward').each(function(cmp){
                    cmp.menu = menu;
                }, this);
			}.createDelegate(this),
			onSuccess: function(params){
				this.reloadTree({
					beforeRestorePosition: function(){
						if(params && Ext.isArray(this.position) && this.position.length == 4) {
							//правим id ноды с палатой
							this.position[1] = (params.LpuSectionWard_id > 0)?('LpuSectionWard_id_'+params.LpuSectionWard_id):'noward';
						}
					}.createDelegate(this)
				});
				//также обновляем меню палат LpuSectionWard_id_10
				this.createListLpuSectionWard();
			}.createDelegate(this)
		});
	},
	doSearch: function() {
		//this.loadTree();
		//this.openNodeSearch();
        this.firstLoad = true;
        this.Tree.getRootNode().select();
        this.loadTree();
	},
	/** Создание меню исходов госпитализации
	 */
	createListLeaveType: function() {
        var win = this;
		var node = win.Tree.getSelectionModel().selNode;
		var begDate, endDate, birthDay, age;
		if (node) {
			begDate = Date.parseDate(node.attributes.EvnSection_setDate, 'd.m.Y');
			endDate = Date.parseDate(win.curDate, 'd.m.Y');
			birthDay = Date.parseDate(node.attributes.Person_BirthDay, 'd.m.Y');
			age = swGetPersonAge(birthDay, begDate);
		}

        sw.Promed.EvnSection.createListLeaveTypeMenu({
            LpuUnitType_SysNick: this.userMedStaffFact.LpuUnitType_SysNick,
			begDate: begDate,
			endDate: endDate,
            ownerWindow: win,
            id: 'ListLeaveTypeMenu',
            getParams: function(){
                var node = win.Tree.getSelectionModel().selNode;
                return {
                    Person_id: node.attributes.Person_id,
                    PersonEvn_id: node.attributes.PersonEvn_id,
                    Server_id: node.attributes.Server_id,
                    EvnPS_id: node.attributes.EvnPS_id,
                    ARMType_id: win.userMedStaffFact.ARMType_id,
                    EvnSection_disDT: Date.parseDate((win.curDate+' '+win.curTime.substr(0,5)), 'd.m.Y H:i'),
                    EvnSection_id: node.attributes.EvnSection_id,
					childPS: age === 0
                };
            },
            callbackEditWindow: function(){
                win.reloadTree();
            },
            onCreate: function(m){
                win.treeActions['actions'].menu.leave.items[0].menu = m;
                win.treeActions['actions'].menu.leave.items[1].menu = m;
            }
        });
	},
	printLpuSectionPacients: function()
	{
		this.getLoadMask(LOAD_WAIT).show();


		var params = {};
		var url = '';
		//BOB - 24.12.2019
		if (this.ARMType == 'stac') {
			params = {
				Lpu_id: getGlobalOptions().lpu_id,
				LpuSection_id: this.findById('mpwpsSearch_LpuSection_id').getValue(),
				date: Ext.getCmp('mpwpsSearch_date').getValue()
			};
			url ='/?c=EvnSection&m=printPatientList';
		} else {
			params = {
				Lpu_id: getGlobalOptions().lpu_id,
				MedService_id: this.userMedStaffFact.MedService_id
			};
			url ='/?c=EvnReanimatPeriod&m=printPatientList';
		}
		
		Ext.Ajax.request({
			//url: '/?c=EvnSection&m=printPatientList',
			url: url,
			params: params,
			callback: function(options, success, response)
			{
				Ext.getCmp('swMPWorkPlaceStacWindow').getLoadMask(LOAD_WAIT).hide();
				if(success)
				{
					if(response.responseText != '')
					{
						openNewWindow(response.responseText);
					}
					else
					{
						sw.swMsg.alert(langs('Сообщение'), langs('Нет ни одного пациента!'));
					}
				}
			}
		});
	},
	
	//Для очистки правой панели от интерактивного документа
    emptyevndoc: function()
    {
        var tp = [];
        this.EvnJournalPanel.tpl = new Ext.Template(tp);
        this.EvnJournalPanel.tpl.overwrite(this.EvnJournalPanel.body, tp);
    },
    resetFilter: function()
    {
        this.findById('mpwpsSearch_F').setValue(null);
        this.findById('mpwpsSearch_I').setValue(null);
        this.findById('mpwpsSearch_O').setValue(null);
        this.findById('mpwpsSearch_PSNumCard').setValue(null);
        this.findById('mpwpsSearch_BirthDay').setValue(null);
        this.findById('mpwpsSearch_MedStaffFact_id').setValue(null);
    },
	//Метод, вызывающий форму редактирования КВС
	openEvnPSEditWindow: function()
	{
		this.emptyevndoc();
		var node = this.Tree.getSelectionModel().selNode,
			_this = this;

		if (getWnd('swEvnPSEditWindow').isVisible())
		{
			sw.swMsg.alert(langs('Сообщение'), langs('Окно редактирования карты выбывшего из стационара уже открыто'));
			return false;
		}
		
		var params = {
			EvnPS_id: node.attributes.EvnPS_id,
			userMedStaffFact: _this.userMedStaffFact,
			Person_id: node.attributes.Person_id,
			Server_id: node.attributes.Server_id,
			onChangeLpuSectionWard: function(params){
				this.reloadTree({
					beforeRestorePosition: function(){
						if(params && Ext.isArray(this.position) && this.position.length == 4) {
							//правим id ноды с палатой
							this.position[1] = (params.LpuSectionWard_id > 0)?('LpuSectionWard_id_'+params.LpuSectionWard_id):'noward';
						}
					}.createDelegate(this)
				});
			}.createDelegate(this),
			onHide: function() {
				this.reloadTree();
				//также обновляем меню палат
				this.createListLpuSectionWard();
				this.createListLeaveType();
			}.createDelegate(this),
			action: 'edit'
		};
		getWnd('swEvnPSEditWindow').show(params);
	},

	printEvnDoc: function()
	{
		var data = this.findById('journalPanel').body.dom.innerHTML;
		if(data != '')
		{
			openNewWindow(data);
		}
	},
	printEvnStick: function(EvnStick_id) {	
		window.open('/?c=Stick&m=printEvnStick&evnStickType=1&EvnStick_id='+EvnStick_id, '_blank');
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
					frm.userName = result.pmUser_Name;
					// Проставляем время
					frm.currentDay();
					frm.Tree.getRootNode().select();
					frm.getLoadMask().hide();
					frm.loadTree();
				}
			}
		});
	},
	stepDay: function(day)
	{
		var datefield = Ext.getCmp('mpwpsSearch_date');
		var date = (datefield.getValue() || Date.parseDate(this.curDate, 'd.m.Y')).add(Date.DAY, day).clearTime();
		datefield.setValue(Ext.util.Format.date(date, 'd.m.Y'));
		datefield.fireEvent('change', datefield, date, null);
	},
	prevDay: function ()
	{
		this.stepDay(-1);
	},
	nextDay: function ()
	{
		this.stepDay(1);
	},
	currentDay: function ()
	{
		var date = Date.parseDate(this.curDate, 'd.m.Y');
		var datefield = Ext.getCmp('mpwpsSearch_date');
		datefield.setValue(Ext.util.Format.date(date, 'd.m.Y'));
		datefield.fireEvent('change', datefield, date, null);
    },
    loadTree: function (period_mode)
    {
		var tree_loader = this.Tree.getLoader();
		if (period_mode != undefined)
		{
			//tree_loader.baseParams.= ;
		}
		var node = this.Tree.getSelectionModel().selNode;
		if (node)
		{
			if (node.parentNode)
			{
				node = node.parentNode;
			}
		}
		else 
			node = this.Tree.getRootNode();
		if (node)
		{
			node.collapse();
			tree_loader.load(node);
			node.expand();
		}
	},
	reloadTree: function(option) {
		if(typeof option != 'object') {
			option = {};
		}
		this.savePosition(option);
		
		var root = this.Tree.getRootNode();
		this.Tree.getLoader().load(root,function(tl,n){
			this.restorePosition(option);
		}.createDelegate(this));
		root.expand();
	},
	/** Сохраняет состояние дерева перед перезагрузкой:
	 *  идeнтификатор выделенной ноды, идентификаторы раскрытых нод
	 */
	savePosition: function(option)
	{
		this.position = null;
		var savePathToSelNode = function (node){
			if (node)
			{
				if( !Ext.isArray(this.position) )
					this.position = [];
				this.position.push(node.attributes.id);
				savePathToSelNode(node.parentNode);
			}
		}.createDelegate(this);
		
		savePathToSelNode(this.Tree.getSelectionModel().selNode);

		var saveExpandedNodes = function (node){
			var expanded_nodes = [];
			if (node && node.childNodes)
			{
				var childNodes = node.childNodes;
				for(var i=0;i<childNodes.length;i++){
					if (childNodes[i].isExpanded()) {
						expanded_nodes.push({
							id: childNodes[i].attributes.id
							,child: saveExpandedNodes(childNodes[i])
						});
					}
				}
			}
			return expanded_nodes;
		}.createDelegate(this);
		
		this.expandedNodes = saveExpandedNodes(this.Tree.getRootNode());

		// log(['savePosition',this.position, this.expandedNodes]);
	},
	restorePosition: function(option)
	{		
		var selNode = function (node){
			this.Tree.getSelectionModel().select(node); 
			this.Tree.fireEvent('select', node);
			this.Tree.fireEvent('click', node);
		}.createDelegate(this);

		var node;
		if(typeof option.beforeRestorePosition == 'function') {
			option.beforeRestorePosition();
		}
		if (Ext.isArray(this.position))
		{
			node = this.Tree.getNodeById(this.position[0]);
			if (node)
			{
				selNode(node);
			}
			else
			{ // была выделена койка
				var restorePositionToSelNode = function (parent_node, path, i){
					var node = parent_node.findChild('id', path[i]);
					//log(['restorePositionToSelNode',node,parent_node, path, i]);
					if (node)
					{
						if(i == 3)
						{
							selNode(node);
						}
						else if( !node.isExpanded() )
						{
							node.expand(false,false,function(n){
								restorePositionToSelNode(n,path,(i+1));
							});
						}
						else
						{
							restorePositionToSelNode(node,path,(i+1));
						}
					}
				};
				this.position.reverse();
				// 0 root
				// 1 группа
				// 2 палата
				// 3 койка
				if(this.position[1] && this.position[2] && this.position[3])
				{
					restorePositionToSelNode(this.Tree.getRootNode(),this.position,1);
				}
			}
		}
		
		var restoreExpandedNodes = function (parent_node, list){
			var enode;
			if( Ext.isArray(list) ) {
				for(var i=0;i<list.length;i++){
					var data = list[i];
					enode = parent_node.findChild('id', data.id);
					if( enode && !enode.isExpanded() ) {
						enode.expand(false,false,function(n){
							restoreExpandedNodes(n,data.child);
						});
					}
				}
			}
		};
		restoreExpandedNodes(this.Tree.getRootNode(),this.expandedNodes);
	},

	/**
	 * Обработчик клика по меню группировки
	 * @param group_by string "по палатам|по режимам"
	 */
	loadGrouped: function(group_by, title){
		if(!('string' === typeof(group_by) && group_by.inlist(['po_palatam', 'po_rejimam', 'po_statusam']))) return;

		this.GroupByButton.setText('Группировать: ' + title);

		var actionGroupBy = this.treeActions['group_by'];
		if(actionGroupBy){
			for(var elname in actionGroupBy.menu){
				actionGroupBy.menu[elname].setIconClass('');
			}
			actionGroupBy.menu[group_by].setIconClass('grouping_by');
		}

		this.emptyevndoc();// закрыть открытый документ

		var root = this.Tree.getRootNode();
		var loader = this.Tree.getLoader();

		loader.baseParams.group_by = group_by;// #181814 группировать: по палатам, по режимам
		this.firstLoad = true;
		loader.baseParams.level = 0;// перезагрузка всего
		//loader.baseParams.object_value = -3;
		loader.load(root);
		root.expand();
		//this.reloadTree();
	},

	openNodeSearch: function()
	{
		var lm = this.getLoadMask(LOAD_WAIT);
		lm.show();
		var datefield = this.dateMenu;
		var params =
		{
			LpuSection_id: this.findById('mpwpsSearch_LpuSection_id').getValue(),
			level: 1,
			object_value: -3,
			date: Ext.util.Format.date(datefield.getValue(), 'd.m.Y'),
			filter_Person_F: this.findById('mpwpsSearch_F').getValue(),
			filter_Person_I: this.findById('mpwpsSearch_I').getValue(),
			filter_Person_O: this.findById('mpwpsSearch_O').getValue(),
			filter_PSNumCard: this.findById('mpwpsSearch_PSNumCard').getValue(),
			filter_Person_BirthDay: Ext.util.Format.date(this.findById('mpwpsSearch_BirthDay').getValue(), 'd.m.Y'),
			filter_MedStaffFact_id: this.findById('mpwpsSearch_MedStaffFact_id').getValue()
		};
		Ext.Ajax.request({
			url: '/?c=EvnSection&m=getSectionTreeData',
			params: params,
			callback: function(options, success, response)
			{
				lm.hide();
				var node, obj = Ext.util.JSON.decode(response.responseText);
				if(obj[0])
				{
					if(obj[0].LpuSectionWard_id != 0)
					{
						node = Ext.getCmp('mpwpsTree').getNodeById('LpuSectionWard_id_'+obj[0].LpuSectionWard_id);
					}
					else
					{
						node = Ext.getCmp('mpwpsTree').getNodeById('-1');
					}
					node.SearchSign = true;
					node.expand();
				}
				else
				{
					//sw.swMsg.alert('Сообщение', 'Не найдено ни одного пациента!');
				}
			}
		});
	},
    /**
     *
     * @return {Boolean}
     */
	acceptFromOtherSection: function()
	{
		var form = this;
		var node = this.Tree.getSelectionModel().selNode;
		if (!node)
		{
			sw.swMsg.alert(langs('Внимание'), langs('Вы не выбрали элемент коечной структуры отделения!'));
			return false;
		}

        var request = function (params) {
            // создаём движение
            Ext.Ajax.request({
                url: '/?c=EvnSection&m=saveEvnSectionFromOtherLpu',
                params: params,
                callback: function(options, success, response)
                {
                    if (success) {
                        var answer = Ext.util.JSON.decode(response.responseText);
                        if (answer.success) {
                            form.reloadTree();
                        } else if (answer.Error_Msg && 'YesNo' != answer.Error_Msg) {
                            Ext.Msg.alert(langs('Ошибка #')+answer.Error_Code, answer.Error_Msg);
                        } else if (answer.Alert_Msg && 'YesNo' == answer.Error_Msg) {
                            sw.swMsg.show({
                                icon: Ext.MessageBox.QUESTION,
                                msg: answer.Alert_Msg + langs('<br /> Продолжить?'),
                                title: langs('Вопрос'),
                                buttons: Ext.Msg.YESNO,
                                fn: function(buttonId)
                                {
                                    if ('yes' == buttonId)
                                    {
										switch ( answer.Error_Code )  {
											case 112:
												params.vizit_direction_control_check = 1;
											break;

											default:
												params.ignore_sex = 1;
											break;
										}
                                        
                                        request(params);
                                    }
                                }
                            });
						} else {
							sw.swMsg.alert(langs('Ошибка'), langs('При сохранении произошли ошибки [Тип ошибки: 1]'));
                        }
                    }
                }
            });
        };

        getWnd('swEvnSectionParamsSelectWindow').show({
            params: {
                Sex_id: node.attributes.Sex_id,
                EvnSection_id: node.attributes.EvnSection_id,
                MedStaffFact_id: form.userMedStaffFact.MedStaffFact_id,
                LpuSection_id: form.userMedStaffFact.LpuSection_id,
                MedPersonal_id: form.userMedStaffFact.MedPersonal_id
            },
            onHide: null,
            onSelect: function(params) {
                params.ignore_sex = 0;
                params.ignoreEvnUslugaKSGCheck = 1;
                request(params);
            }
        });
        return true;
	},
	OpenEMK: function(cb_params)
	{
		var form = this;
		var node = this.Tree.getSelectionModel().selNode;
		if (!node && Ext.isEmpty(cb_params))
		{
			sw.swMsg.alert(langs('Внимание'), langs('Вы не выбрали элемент коечной структуры отделения!'));
			return false;
		}
		if (getWnd('swPersonEmkWindow').isVisible())
		{
			sw.swMsg.alert(langs('Сообщение'), langs('Форма электронной истории болезни (ЭМК) в данный момент открыта.'));
			return false;
		}
		var Person_id,Server_id,PersonEvn_id, EvnPS_id;
		if (!Ext.isEmpty(cb_params)){
			Person_id = cb_params.Person_id;
			Server_id = cb_params.Server_id;
			PersonEvn_id = cb_params.PersonEvn_id;
			EvnPS_id = cb_params.EvnPS_id;
		} else {
			Person_id = node.attributes.Person_id;
			Server_id = node.attributes.Server_id;
			PersonEvn_id = node.attributes.PersonEvn_id;
		}

		if (!Person_id && !Server_id && !PersonEvn_id)
		{
			sw.swMsg.alert(langs('Ошибка'), langs('Вы не выбрали пациента!'));
			return false;
		}
		var searchNodeObj = false;
		if((node && node.attributes && node.attributes.EvnPS_id) || EvnPS_id) {
			searchNodeObj = {
				parentNodeId: 'root',
				last_child: false,
				disableLoadViewForm: false,
				EvnClass_SysNick: 'EvnPS',
				Evn_id: !Ext.isEmpty(EvnPS_id)?EvnPS_id:node.attributes.EvnPS_id
			};
		}

		var emk_params = {
			Person_id: Person_id,
			Server_id: Server_id,
			PersonEvn_id: PersonEvn_id,
			userMedStaffFact: this.userMedStaffFact,
			MedStaffFact_id: this.userMedStaffFact.MedStaffFact_id,
			LpuSection_id: this.userMedStaffFact.LpuSection_id,
			ARMType: 'common',
			ARMTypeFrom: this.ARMType,														//BOB - 13.06.2018
			addStacActions: ['action_New_EvnPS', 'action_StacSvid', 'action_EvnPrescrVK'],
			searchNodeObj: searchNodeObj,
			onChangeLpuSectionWard: function(params){
				this.reloadTree({
					beforeRestorePosition: function(){
						if(params && Ext.isArray(this.position) && this.position.length == 4) {
							//правим id ноды с палатой
							this.position[1] = (params.LpuSectionWard_id > 0)?('LpuSectionWard_id_'+params.LpuSectionWard_id):'noward';
						}
					}.createDelegate(this)
				});
			}.createDelegate(this),
			callback: function()
			{
				this.reloadTree();
				//также обновляем меню палат
				this.createListLpuSectionWard();
				this.createListLeaveType();
			}.createDelegate(this)
		};

		// #182475 - для Вологды в левой панели ЭМК отображаем кнопку
		// "Открыть карту профилактических прививок":
		if (getRegionNick() == 'vologda')
			emk_params.addStacActions.push('action_Kard063');

		checkPersonPhoneVerification({
			Person_id: Person_id,
			MedStaffFact_id: this.userMedStaffFact.MedStaffFact_id,
			callback: function(){
				Ext.Ajax.request({
					url: '/?c=EvnPS&m=beforeOpenEmk',
					params: {Person_id: Person_id},
					failure: function(response, options) {
						showSysMsg(langs('При получении данных для проверок произошла ошибка!'));
					},
					success: function(response, action)
					{
						if (response.responseText) {
							var answer = Ext.util.JSON.decode(response.responseText);
							if(!Ext.isArray(answer) || !answer[0])
							{
								showSysMsg(langs('При получении данных для проверок произошла ошибка! Неправильный ответ сервера.'));
								return false;
							}
							if (answer[0].countOpenEvnPS > 0)
							{
								//showSysMsg('Создание новых КВС недоступно','У пациента имеются открытые КВС в даннном ЛПУ! Количество открытых КВС: '+ answer[0].countOpenEvnPS);
								//emk_params.addStacActions = ['action_StacSvid']; //лочить кнопку создания случая лечения, если есть незакрытые КВС в данном ЛПУ #13272
								emk_params.disAddPS = answer[0].countOpenEvnPS;
							} 
							getWnd('swPersonEmkWindow').show(emk_params);
						}
						else {
							showSysMsg(langs('При получении данных для проверок произошла ошибка! Отсутствует ответ сервера.'));
						}
					}
				});
			}
		});
	},

	openEMKFromEvnJournal: function(params) {
		var searchNodeObj = {
			parentNodeId: 'root',
			last_child: false,
			disableLoadViewForm: false,
			EvnClass_SysNick: params.EvnClass_rSysNick,
			Evn_id: params.Evn_rid
		};
		if (getWnd('swPersonEmkWindow').isVisible() && getWnd('swPersonEmkWindow').Person_id != params.Person_id) {
			sw.swMsg.alert(langs('Сообщение'), langs('Форма электронной истории болезни (ЭМК) в данный момент открыта.'));
			return false;
		}
		var emk_params = {
			Person_id: params.Person_id,
			Server_id: params.Server_id,
			PersonEvn_id: params.PersonEvn_id,
			userMedStaffFact: this.userMedStaffFact,
			MedStaffFact_id: this.userMedStaffFact.MedStaffFact_id,
			LpuSection_id: this.userMedStaffFact.LpuSection_id,
			ARMType: 'common',
			addStacActions:['action_New_EvnPS', 'action_StacSvid', 'action_EvnPrescrVK'],
			searchNodeObj: searchNodeObj,
			onChangeLpuSectionWard: function(params){
				this.reloadTree({
					beforeRestorePosition: function(){
						if(params && Ext.isArray(this.position) && this.position.length == 4) {
							//правим id ноды с палатой
							this.position[1] = (params.LpuSectionWard_id > 0)?('LpuSectionWard_id_'+params.LpuSectionWard_id):'noward';
						}
					}.createDelegate(this)
				});
			}.createDelegate(this),
			callback: function()
			{
				this.reloadTree();
				//также обновляем меню палат
				this.createListLpuSectionWard();
				this.createListLeaveType();
			}.createDelegate(this)
		};

		Ext.Ajax.request({
			url: '/?c=EvnPS&m=beforeOpenEmk',
			params: {Person_id: params.Person_id},
			failure: function(response, options) {
				showSysMsg(langs('При получении данных для проверок произошла ошибка!'));
			},
			success: function(response, action)
			{
				if (response.responseText) {
					var answer = Ext.util.JSON.decode(response.responseText);
					if(!Ext.isArray(answer) || !answer[0])
					{
						showSysMsg(langs('При получении данных для проверок произошла ошибка! Неправильный ответ сервера.'));
						return false;
					}
					if (answer[0].countOpenEvnPS > 0)
					{
						//showSysMsg('Создание новых КВС недоступно','У пациента имеются открытые КВС в даннном ЛПУ! Количество открытых КВС: '+ answer[0].countOpenEvnPS);
						emk_params.addStacActions = ['action_StacSvid','action_EvnPrescrVK']; //лочить кнопку создания случая лечения, если есть незакрытые КВС в данном ЛПУ #13272
					}
					getWnd('swPersonEmkWindow').show(emk_params);
				}
				else {
					showSysMsg(langs('При получении данных для проверок произошла ошибка! Отсутствует ответ сервера.'));
				}
			}
		});
	},

	printAddressLeaf: function(leaf_type) {
		var node = this.Tree.getSelectionModel().selNode;

		if (!leaf_type || !leaf_type.inlist(['arrival','departure']) ) {
			return false;
		}

		var Person_id = node.attributes.Person_id;
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

	printCmp_f114u: function() {
		var node = this.Tree.getSelectionModel().selNode;

		var EvnPS_id = node.attributes.EvnPS_id;
		if ( Ext.isEmpty(EvnPS_id) ) {
			return false;
		}

		printBirt({
			'Report_FileName': 'cmp_f114u.rptdesign',
			'Report_Params': '&paramEvnPS='+EvnPS_id,
			'Report_Format': 'pdf'
		});
	},

	printCmp_f009u: function() {
		getWnd('swTransfusionMediaJournalForm').show();
	},


	printCmp_f005u: function() {
		var node = this.Tree.getSelectionModel().selNode;

		var EvnPS_id = node.attributes.EvnPS_id;
		if ( Ext.isEmpty(EvnPS_id) ) {
			return false;
		}

		printBirt({
			'Report_FileName': 'f005u.rptdesign',
			'Report_Params': '&paramEvnPs='+EvnPS_id,
			'Report_Format': 'pdf'
		});
	},
	
	printRankinScale: function() {
		var node = this.Tree.getSelectionModel().selNode;

		var EvnPS_id = node.attributes.EvnPS_id;
		if ( Ext.isEmpty(EvnPS_id) ) {
			return false;
		}

		printRankinScale(EvnPS_id);
	},

	//BOB - 15.03.2017
	// функция перевода пациента в реанимацию
	// извлечение параметров пациента
	moveToReanimation: function() {
		var node;

		//если текущий АРМ - врача стационара
		if(this.ARMType == 'stac') {
				node = this.Tree.getSelectionModel().selNode;
				this.moveToReanimationLogic(node);
		}
		else {  //если текущий АРМ - врача реаниматолога
			//BOB - 19.06.2019
			var personParams = {
				onSelect: function(pdata) {
					getWnd('swPersonSearchWindow').hide();
					Ext.getCmp('swMPWorkPlaceStacWindow').moveToReanimationLogic(pdata);
				},
				searchMode: 'hasopenevnps'	//BOB - 19.06.2019
			};
			getWnd('swPersonSearchWindow').show(personParams);
			//BOB - 19.06.2019

		}
	},

	// функция перевода пациента в реанимацию
	//провенрка не находится ли пациент уже в реанимации
	//непосредственно перевод в реанимацию
	moveToReanimationLogic: function(pdata) {

		var thas = this;

		var PatientKeis;
		//из окна выбора карты ВС / реанимационных медслужб, если их было много
		if (pdata['Status'] && pdata['Status'] == 'FromManyEvnPS'){

			PatientKeis = {
				Person_id : pdata.Person_id,
				Server_id : pdata.Server_id,
				PersonEvn_id : pdata.PersonEvn_id,
				EvnPS_id : pdata.EvnPS_id,
				EvnSection_id : pdata.EvnSection_id, //BOB - 19.04.2017   -  !!!!!!!!!!!!!ещё не добавил
				LpuSection_id : pdata.LpuSection_id,
				MedService_id : pdata.MedService_id,       //this.userMedStaffFact.MedService_id,
				ARMType : 'FromManyEvnPS'
			};

		} else {

			if(this.ARMType == 'stac'){
				//Из списка (дерева)
				//BOB - 17.03.2017
				PatientKeis = {
					Person_id : pdata.attributes.Person_id,
					Server_id : pdata.attributes.Server_id,
					PersonEvn_id : pdata.attributes.PersonEvn_id,
					EvnPS_id : pdata.attributes.EvnPS_id,
					EvnSection_id : pdata.attributes.EvnSection_id, //BOB - 19.04.2017   -  добавил
					MedService_id : 0,
					LpuSection_id : this.userMedStaffFact.LpuSection_id,
					ARMType : 'stac'
				};
			} else {
				//Из окна поиска
				//BOB - 19.04.2017   -  добавил EvnSection_id
				PatientKeis = {
					Person_id : pdata.Person_id,
					Server_id : pdata.Server_id,
					PersonEvn_id : pdata.PersonEvn_id,
					EvnPS_id : 0,
					EvnSection_id : 0,
					MedService_id : this.userMedStaffFact.MedService_id,
					LpuSection_id : 0,
					ARMType : 'reanimation'
				};
			}
		} //из окна выбора карты ВС если их было много
		PatientKeis['Lpu_id'] = this.userMedStaffFact.Lpu_id;
		PatientKeis['MedPersonal_id'] = this.userMedStaffFact.MedPersonal_id;

		//непосредственно перевод в реанимацию
		Ext.Ajax.request({
			url: '/?c=EvnReanimatPeriod&m=moveToReanimation',   //'/?c=EvnSection&m=moveToReanimation',
			params: PatientKeis,
			failure: function() {
				showSysMsg(langs('При получении данных для проверок произошла ошибка!'));
			},
			callback: function(options, success, response){

				if(success){
					var answer = Ext.util.JSON.decode(response.responseText);

					// катастрофическая ошибка наверняка связанная с неправильным программированием
					if (answer['success'] == false) {
						sw.swMsg.alert(langs('Сообщение'), answer['success']+ ' ' + answer['Error_Msg']);
						return false;
					}

					if(answer['Status'] == 'AlreadyInReanimation')   // уже в реанимации
						sw.swMsg.alert(langs('Сообщение'), answer['Message']);
					else if (answer['Status'] == 'DoneSuccessfully'){ // удачное создание РП

						thas.reloadTree();
						thas.emptyevndoc();

						var params = {
							EvnReanimatPeriod_id: answer['EvnReanimatPeriod_id'],
							ERPEW_title: langs('Редактирование реанимационного периода'),
							action: 'edit',
							UserMedStaffFact_id: thas.userMedStaffFact.MedStaffFact_id,
							userMedStaffFact: thas.userMedStaffFact,
							from: 'moveToReanimation',
							ARMType: thas.ARMType
						};

						var RP_saved = false;
						params.Callback = function(pdata) {
							getWnd('swEvnReanimatPeriodEditWindow').hide();
							RP_saved = pdata;
							//console.log('BOB_RP_saved=',RP_saved);
							sw.swMsg.alert(langs('Сообщение'), 'Пациент переведён в реанимацию');
						};
						getWnd('swEvnReanimatPeriodEditWindow').show(params);


					}
					else if (answer['Status'] == 'ManyEvnPS'){  //несколько КВС

						var personParams = {
							callback: function(pdata) {
								getWnd('ufa_ToReanimationFromFewPSWindow').hide();
								Ext.getCmp('swMPWorkPlaceStacWindow').moveToReanimationLogic(pdata);
							},
							Server_id: answer['Server_id'],
							Person_id: answer['Person_id'],
							PersonEvn_id: answer['PersonEvn_id'],
							Lpu_id: thas.userMedStaffFact.Lpu_id,
							Status:answer['Status'],
							EvnPS_id : 0,
							EvnSection_id : 0,
							LpuSection_id : 0,
							MedService_id : thas.userMedStaffFact.MedService_id
						};

						getWnd('ufa_ToReanimationFromFewPSWindow').show(personParams);
					}
					else if (answer['Status'] == 'NoReanimatMedService'){  //отсутствуют службы реанимации   //BOB - 19.06.2019
						sw.swMsg.alert(langs('Сообщение'), answer['Message']);
					}
					else if (answer['Status'] == 'ManyReanimatMedService'){  //несколько служб реанимации    //BOB - 19.06.2019

						var personParams = {
							callback: function(pdata) {
								getWnd('ufa_ToReanimationFromFewPSWindow').hide();
								Ext.getCmp('swMPWorkPlaceStacWindow').moveToReanimationLogic(pdata);
							},
							Server_id: answer['Server_id'],
							Person_id: answer['Person_id'],
							PersonEvn_id: answer['PersonEvn_id'],
							Lpu_id: thas.userMedStaffFact.Lpu_id,
							Status:answer['Status'],
							EvnPS_id : answer['EvnPS_id'],
							EvnSection_id : answer['EvnSection_id'],
							LpuSection_id : answer['LpuSection_id'],
							MedService_id : 0
						};

						getWnd('ufa_ToReanimationFromFewPSWindow').show(personParams);
					}
					else   // ошибка обращения к БД
						sw.swMsg.alert(langs('Сообщение'), answer['Message']);

				} else {
					sw.swMsg.alert(langs('Сообщение'), langs('Ошибка при получении записанных пациентов'));
				}
			}
		});


	},
	//BOB - 29.05.2018
	moveToReanimationOutPriem: function(pdata) {
		var win = this;
		//console.log('BOB_Object_pdata=',pdata); //BOB - 29.05.2018

		if(pdata.EvnPS_id != '0') {
			//alert('Формирование РП');
			pdata.MedPersonal_id = this.userMedStaffFact.MedPersonal_id;
			pdata.MedService_id = this.userMedStaffFact.MedService_id;
			pdata.Lpu_id = this.userMedStaffFact.Lpu_id;
			pdata.ARMType = 'reanimation';


			$.ajax({
				mode: "abort",
				type: "post",
				async: false,
				url: '/?c=EvnReanimatPeriod&m=moveToReanimationOutPriem',
				data: pdata,
				success: function(response) {
					var answer = Ext.util.JSON.decode(response);
					//console.log('BOB_answer=',answer); 

					if(answer['Status'] == 'AlreadyInReanimation')
						sw.swMsg.alert(langs('Сообщение'), answer['Message']);
					else if (answer['Status'] == 'DoneSuccessfully'){

						//win.reloadTree();
						//win.emptyevndoc();

						var params = {
							EvnReanimatPeriod_id: answer['EvnReanimatPeriod_id'],
							ERPEW_title: langs('Редактирование реанимационного периода'),
							action: 'edit',
							UserMedStaffFact_id: win.userMedStaffFact.MedStaffFact_id,
							userMedStaffFact: win.userMedStaffFact,
							from: 'moveToReanimation',
							ARMType: win.ARMType
						};

						params.Callback = function(pdata) {
							getWnd('swEvnReanimatPeriodEditWindow').hide();
							sw.swMsg.alert(langs('Сообщение'), 'Пациент переведён в реанимацию');
							win.reloadTree();
							win.emptyevndoc();
						};
						getWnd('swEvnReanimatPeriodEditWindow').show(params);
					}
					else
						sw.swMsg.alert(langs('Сообщение'), answer['Message']);
				},
				error: function() {
					sw.swMsg.alert(langs('Сообщение'), 'При обработке запроса на сервере произошла ошибка!');
				}
			});
		}
	},
	//BOB - 29.05.2018  
	
	//функция завершения реанимационного периода
	endReanimatReriod: function(from) {
		var node = this.Tree.getSelectionModel().selNode;
		//console.log('BOB_node=',node);
		//Обращение к серверу:
		//	Поиск открытого реаимационного периода.
		//	Подготовка реквизитов для открытия окна РП.
		var win = this;

		var params = {
			Person_id : node.attributes.Person_id,
			Server_id : node.attributes.Server_id,
			PersonEvn_id : node.attributes.PersonEvn_id//,
		};

		$.ajax({
			mode: "abort",
			type: "post",
			async: false,
			url: '/?c=EvnReanimatPeriod&m=endReanimatReriod',
			data: params,
			success: function(response) {
				params = Ext.util.JSON.decode(response);
			},
			error: function() {
				sw.swMsg.alert(langs('Сообщение'), 'При обработке запроса на сервере произошла ошибка!');
			}
		});

		//ЕСЛИ в ответе - сообщение об отсутствии РП
		if(params['Status'] == 'NotInReanimation'){
			sw.swMsg.alert(langs('Сообщение'), params['Message']);
			return false;
		}

		//ИНАЧЕ
		//	подготовка параметров для открытия окна РП
		//	в т.ч. точка вызова и в рамках какой функции
		params.ERPEW_title = langs('Редактирование реанимационного периода');
		params.action = 'edit'; //  : mode,  ++++++
		params.UserMedStaffFact_id = this.userMedStaffFact.MedStaffFact_id;
		params.userMedStaffFact = this.userMedStaffFact;
		params.from = from, //'endReanimatReriod';
		params.ARMType = this.ARMType;

		var RP_saved = false;
		params.Callback = function(pdata) {
			getWnd('swEvnReanimatPeriodEditWindow').hide();
			if (from == 'endReanimatReriod'){
				RP_saved = pdata;
				if (RP_saved) {
					win.reloadTree();
					win.emptyevndoc();
					sw.swMsg.alert(langs('Сообщение'), 'Реанимационный период завершён');
				}
			}
		};

		getWnd('swEvnReanimatPeriodEditWindow').show(params);
	},
    //BOB - 15.03.2017
	//BOB - 12.05.2018	
	delReanimatPeriod:	function() {
		var node = this.Tree.getSelectionModel().selNode;
		//alert("удалить!!!! " + node.attributes.Person_id);
		var win = this;


		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( buttonId == 'yes' ) {

					var params = {
						Person_id : node.attributes.Person_id
					};
					$.ajax({
						mode: "abort",
						type: "post",
						async: false,
						url: '/?c=EvnReanimatPeriod&m=delReanimatPeriod',
						data: params,
						success: function(response) {
							var response_obj = Ext.util.JSON.decode(response);
							if ( response_obj.success == false ) {
								sw.swMsg.alert(langs('Ошибка'), response_obj.Error_Msg ? response_obj.Error_Msg : error);
							}
							else {
								win.reloadTree();
								win.emptyevndoc();
								sw.swMsg.alert(langs('Сообщение'), 'Реанимационный период удалён');
							}
						},
						error: function() {
							sw.swMsg.alert(langs('Сообщение'), 'При обработке запроса на сервере произошла ошибка!');
						}
					});
				}
			}.createDelegate(this),
			icon: Ext.MessageBox.QUESTION,
			msg: langs('Вы действительно хотите удалить реанимационный период?'),
			title: langs('Вопрос')
		});
	},
	//BOB - 12.05.2018
	//BOB - 02.10.2019
	//функция перевода в другую реанимацию
	changeReanimatPeriod: function()
	{
		var node = this.Tree.getSelectionModel().selNode;
		var thas = this;
		var СheckResponse = {};
		var LogicResponse = {}

		//1. Отыскивается незакрытый РП, для врача стационара может не найтись - по сути проверка, а есть ли вообще – тогда отображается сообщение об отсутствии РП.
		//   возвращает Id медслужбы
		//   Проверяется количество реанимационных медслужб в МО, если одна, то - сообщение об этом.
		var params = {
			Person_id : node.attributes.Person_id,
			EvnPS_id: node.attributes.EvnPS_id
		};

		Ext.Ajax.request({
			url: '/?c=EvnReanimatPeriod&m=changeReanimatPeriodCheck',
			params: params,
			success: function(response, action) {
				СheckResponse = Ext.util.JSON.decode(response.responseText);

				//ЕСЛИ в ответе - сообщение об отсутствии РП
				if(СheckResponse['success'] == false){
					sw.swMsg.alert(langs('Сообщение'), СheckResponse['Error_Msg']);
					return false;
				};

				//функция дальнейших действий после открытия окна выбора реанимационной медслужбы
				changeReanimatPeriodLogic = function(pdata) {
					//3. Закрытие всех незакрытых дочерних наблюдений и реанимационных мероприятий, может ещё и измерений.
					//   Закрытие РП с исходом – «перевод в другую службу реанимации», для этого дополняем справочник исходов.
					//   Формирование нового РП,
					//   Изменение кода РП в записи регистра реанимации

					Ext.Ajax.request({
						url: '/?c=EvnReanimatPeriod&m=changeReanimatPeriod',
						params: {EvnReanimatPeriod_id: СheckResponse['EvnReanimatPeriod_id'],
								MedService_id: pdata.MedService_id},
						success: function(response, action) {
							LogicResponse = Ext.util.JSON.decode(response.responseText);
							if (LogicResponse['success'] == 'false'){
								Ext.MessageBox.alert('Ошибка!', LogicResponse['Error_Msg'].substr(1).replace(/~/g,'<br>'));
							}
							else { // удачный  перевод в другую ренимацию РП

								thas.reloadTree();
								thas.emptyevndoc();

								//4. открытие формы ввода РП.
								var params = {
									EvnReanimatPeriod_id: LogicResponse['EvnReanimatPeriod_id'],
									ERPEW_title: langs('Редактирование реанимационного периода'),
									action: 'edit',
									UserMedStaffFact_id: thas.userMedStaffFact.MedStaffFact_id,
									userMedStaffFact: thas.userMedStaffFact,
									from: 'moveToReanimation',
									ARMType: thas.ARMType
								};
								//коллбак по закрытию формы ввода РП
								params.Callback = function(pdata) {
									getWnd('swEvnReanimatPeriodEditWindow').hide();
									sw.swMsg.alert(langs('Сообщение'), 'Пациент переведён в другую реанимацию');
								};
								getWnd('swEvnReanimatPeriodEditWindow').show(params);
							}
						},
						failure: function() {
							sw.swMsg.alert(langs('Сообщение'), 'При обработке запроса на сервере произошла ошибка!');
						}
					});
				};

				//2. Выводится диалог выбора реанимационной службы, при выборе проверяется не совпала ли выбранная с той, которая была.
				var personParams = {
					//коллбак по закрытию формы выбора медслужбы
					callback: function(pdata) {
						getWnd('ufa_ToReanimationFromFewPSWindow').hide();
						changeReanimatPeriodLogic(pdata);
					},
					Server_id: node.attributes.Server_id,
					Person_id: node.attributes.Person_id,
					PersonEvn_id: node.attributes.PersonEvn_id,
					Lpu_id: thas.userMedStaffFact.Lpu_id,
					Status:'ManyReanimatMedService',
					EvnPS_id : СheckResponse['EvnPS_id'],
					EvnSection_id : СheckResponse['EvnSection_id'],
					LpuSection_id : СheckResponse['LpuSection_id'],
					MedService_id : СheckResponse['MedService_id']
				};

				getWnd('ufa_ToReanimationFromFewPSWindow').show(personParams);
			},
			failure: function() {
				sw.swMsg.alert(langs('Сообщение'), 'При обработке запроса на сервере произошла ошибка!');
			}
		});
	},
	//BOB - 12.05.2018	

	initComponent: function()
	{
		var thas = this;		
		
		this.FilterPanel = new Ext.FormPanel(
		{
			xtype: 'form',
			labelAlign: 'right',
			labelWidth: 50,
			style: 'margin-top: 5px',
			items:
			[{
				layout: 'column',
				items:
				[{
					layout: 'form',
					items:
					[{
						hiddenName: 'LpuSection_id',
						id: 'mpwpsSearch_LpuSection_id',
						emptyText: langs('Отделение'),
						hideLabel: true,
						lastQuery: '',
						linkedElements: [
						'mpwpsSearch_MedStaffFact_id'
						],
						listWidth: 250,
						width: 250,
						xtype: 'swlpusectionglobalcombo',
						listeners:
						{
							'keydown': function (inp, e)
							{
								if (e.getKey() == Ext.EventObject.ENTER)
								{
									e.stopEvent();
                                    thas.doSearch();
								}
							}
						}
					}]
				},
				{
					layout: 'form',
					style: 'padding-left: 20px',
					items:
					[{
						id: 'mpwpsSearch_MedStaffFact_id',
						parentElementId: 'mpwpsSearch_LpuSection_id',
						emptyText: langs('Врач'),
						hideLabel: true,
						hiddenName: 'MedStaffFact_id',
						lastQuery: '',
						listWidth: 350,
						//tabIndex: ,
						width: 300,
						xtype: 'swmedstafffactglobalcombo',
						tpl: new Ext.XTemplate(
							'<tpl for="."><div class="x-combo-list-item">',
							'<table style="border: 0;">',
							'<td style="width: 45px;"><font color="red">{MedPersonal_TabCode}&nbsp;</font></td>',
							'<td><span style="font-weight: bold;">{MedPersonal_Fio}</span></td>',
							'</tr></table>',
							'</div></tpl>'
							),
						listeners:
						{
							'keydown': function (inp, e)
							{
								if (e.getKey() == Ext.EventObject.ENTER)
								{
									e.stopEvent();
                                    thas.doSearch();
								}
							}
						}
					}]
				}]
			},
			{
				xtype: 'fieldset',
				autoHeight: true,
				title: langs('Пациент'),
				collapsible: true,
				layout: 'column',
				style: 'margin: 5px 0 0 0',
				listeners: {
					collapse: function(p) {
						thas.doLayout();
					},
					expand: function(p) {
						thas.doLayout();
					}
				},

				items:
				[{
					layout: 'form',
					labelWidth: 60,
					width: 200,
					items:
					[{
						xtype: 'textfieldpmw',
						anchor: '100%',
						id: 'mpwpsSearch_F',
						name: 'Person_SurName',
						fieldLabel: langs('Фамилия'),
						listeners:
						{
							'keydown': function (inp, e)
							{
								if (e.getKey() == Ext.EventObject.ENTER)
								{
									e.stopEvent();
                                    thas.doSearch();
								}
							}
						}
					}]
				},
				{
					layout: 'form',
					width: 200,
					items: [{
						xtype: 'textfieldpmw',
						anchor: '100%',
						id: 'mpwpsSearch_I',
						name: 'Person_FirName',
						fieldLabel: langs('Имя'),
						listeners:
						{
							'keydown': function (inp, e)
							{
								if (e.getKey() == Ext.EventObject.ENTER)
								{
									e.stopEvent();
                                    thas.doSearch();
								}
							}
						}
					}]
				},
				{
					layout: 'form',
					labelWidth: 80,
					width: 220,
					items: [{
						xtype: 'textfieldpmw',
						anchor: '100%',
						id: 'mpwpsSearch_O',
						name: 'Person_SecName',
						fieldLabel: langs('Отчество'),
						listeners:
						{
							'keydown': function (inp, e)
							{
								if (e.getKey() == Ext.EventObject.ENTER)
								{
									e.stopEvent();
                                    thas.doSearch();
								}
							}
						}
					}]
				},
				{
					layout: 'form',
					items:
					[{
						xtype: 'swdatefield',
						//renderer: Ext.util.Format.dateRenderer('d.m.Y'),
						format: 'd.m.Y',
						plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
						id: 'mpwpsSearch_BirthDay',
						name: 'Person_BirthDay',
						fieldLabel: langs('ДР'),
						listeners:
						{
							'keydown': function (inp, e)
							{
								if (e.getKey() == Ext.EventObject.ENTER)
								{
									e.stopEvent();
                                    thas.doSearch();
								}
							}
						}
					}]
				}, 
				{
					layout: 'form',
					labelWidth: 75,
					hidden: getRegionNick() != 'msk',
					items:
						[{
							xtype: 'textfieldpmw',
							width: 60,
							margins: '20 0',
							id: 'mpwpsSearch_PSNumCard',
							name:'PSNumCard',
							fieldLabel: lang['nomer_kvs'],
							listeners:
								{
									'keydown': function (inp, e)
									{
										if (e.getKey() == Ext.EventObject.ENTER)
										{
											e.stopEvent();
											thas.doSearch();
										}
									}
								}
						}]
				},
				{
					layout: 'form',
					items:
					[{
						style: "padding-left: 10px",
						xtype: 'button',
						id: 'mpwpsBtnSearch',
						text: langs('Найти'),
						iconCls: 'search16',
						handler: function()
						{
                            thas.doSearch();
						}
					}]
				},
				{
					layout: 'form',
					items:
					[{
						style: "padding-left: 10px",
						xtype: 'button',
						id: 'mpwpsBtnClear',
						text: langs('Сброс'),
						iconCls: 'resetsearch16',
						handler: function()
						{
                            thas.resetFilter();
                            thas.firstLoad = true;
                            thas.Tree.getRootNode().select();
                            thas.loadTree();
						}
					}]
				}, {
					layout: 'form',
					items:
						[{
							style: "padding-left: 10px",
							xtype: 'button',
							text: langs('Считать с карты'),
							iconCls: 'idcard16',
							handler: function()
							{
								thas.readFromCard();
							}
						}]
				}]
			}]
		});

		// Конфиги экшенов для контекстного меню и тулзбара грида списка больных
		var Actions =
		[
			{name:'open_stac_emk', disabled: true, text:langs('Открыть ЭМК'), tooltip: langs('Открыть электронную медицинскую карту пациента'), iconCls : 'open16', handler: function() {this.OpenEMK();}.createDelegate(this)},
			{name:'refresh', text:BTN_GRIDREFR, tooltip: BTN_GRIDREFR, iconCls : 'refresh16', handler: function() {this.reloadTree();this.emptyevndoc();}.createDelegate(this)},
			{
				name:'print', text:BTN_GRIDPRINT, tooltip: BTN_GRIDPRINT, iconCls : 'print16',  menu:
				new Ext.menu.Menu([
					{text: langs('Список'), handler: function () {this.printLpuSectionPacients();}.createDelegate(this)},
					{text: langs('Листок прибытия'), hidden: getRegionNick()=='kz', handler: function () {this.printAddressLeaf('arrival');}.createDelegate(this)},
					{text: langs('Листок убытия'), hidden: getRegionNick()=='kz', handler: function () {this.printAddressLeaf('departure');}.createDelegate(this)},
					{text: '114/у - Сопроводительный лист и талон к нему', handler: function () {this.printCmp_f114u();}.createDelegate(this)},
					{text: langs('Журнал регистрации переливания трансфузионных сред (009/у)'), hidden: getRegionNick()=='kz', handler: function () {this.printCmp_f009u();}.createDelegate(this)},
					{text: 'Лист регистрации переливания трансфузионных сред (005/у)', handler: function () {this.printCmp_f005u();}.createDelegate(this), hidden: getRegionNick()=='kz'},
					{text: 'Печать шкалы Рэнкина', handler: function () {this.printRankinScale();}.createDelegate(this), hidden: true}
				])
			},
		//	{name:'kvs_edit', disabled: true, text:'Редактировать КВС', tooltip: 'Редактировать КВС', iconCls : 'edit16', handler: function() {this.openEvnPSEditWindow();}.createDelegate(this)},
		//	{name:'update_ward', disabled: true, text:'Перевод в палату', tooltip: 'Перевести пациента в другую палату', iconCls : 'update-ward16', menu: new Ext.menu.Menu({id:'ListMenuWard'})},
			{name:'update_section', disabled: true, text:langs('Перевод в отделение'), tooltip: langs('Перевести пациента в другое отделение'), iconCls : 'update-section16', handler: function() {return false;}.createDelegate(this)},
		//	{name:'update_doctor', disabled: true, text:'Изменить врача', tooltip: 'Изменить лечащего врача пациента', iconCls : 'update-doctor16', menu: new Ext.menu.Menu({id:'ListMenuMedPersonal'})},
		//	{name:'leave', disabled: true, text:'Выписать', tooltip: 'Выписать пациента', iconCls : 'leave16', menu: new Ext.menu.Menu({id:'ListLeaveTypeMenu'})},
		//	{name:'accept', disabled: true, text:'Принять в отделение', tooltip: 'Принять пациента из другого отделения', iconCls : 'update-ward16', handler: function() {this.acceptFromOtherSection();}.createDelegate(this)}
		
			{name:'actions', key: 'actions', text:langs('Действия'), menu: [
				{name:'accept', disabled: true, text:langs('Принять в отделение'), tooltip: langs('Принять пациента из другого отделения'), iconCls : 'update-ward16', handler: function() {this.acceptFromOtherSection();}.createDelegate(this)},
				{name:'kvs_edit', disabled: true, text:langs('Редактировать КВС'), tooltip: langs('Редактировать КВС'), iconCls : 'edit16', handler: function() {this.openEvnPSEditWindow();}.createDelegate(this)},
				{name:'update_ward', disabled: true, text:langs('Перевод в палату'), tooltip: langs('Перевести пациента в другую палату'), iconCls : 'update-ward16', menu: new Ext.menu.Menu({id:'ListMenuWard'})},
				{name:'update_doctor', disabled: true, text:langs('Изменить врача'), tooltip: langs('Изменить лечащего врача пациента'), iconCls : 'update-doctor16', menu: new Ext.menu.Menu({id:'ListMenuMedPersonal'})},
				{name:'leave', disabled: true, text:langs('Выписать'), tooltip: langs('Выписать пациента'), iconCls : 'leave16', menu: new Ext.menu.Menu({id:'ListLeaveTypeMenu'})},
				{
					name:'add_patient',
					text: langs('Добавить пациента'),
					tooltip: langs('Добавить пациента'),
					iconCls: 'add16',
					handler: this.addPatient.createDelegate(this)
				},
				{name:'move_reanimation', text: langs('Перевод в реанимацию'), tooltip: langs('Перевод в реанимацию'), iconCls: 'ambulance16', handler: this.moveToReanimation.createDelegate(this) },     //BOB - 15.03.2017
				{name:'edit_reanimat_period', text: langs('Редактирование реанимационного периода'), tooltip: langs('Редактирование реанимационного периода'), iconCls: 'edit16', handler: function () { this.endReanimatReriod('editReanimatReriod'); }.createDelegate(this) },     //BOB - 19.06.2019
				{name:'change_reanimat_period', text: langs('Перевод в другую реанимацию'), tooltip: langs('Перевод в другую реанимацию'), iconCls: 'hospitalization-cancel16', handler: this.changeReanimatPeriod.createDelegate(this) },     //BOB - 02.10.2019
				{name:'end_reanimat_period', text: langs('Завершение реанимационного периода'), tooltip: langs('Завершение реанимационного периода'), iconCls: 'update-ward16', handler: function () { this.endReanimatReriod('endReanimatReriod'); }.createDelegate(this) },     //BOB - 17.11.2017
				{name:'del_reanimat_period', text: langs('Удаление реанимационного периода'), tooltip: langs('Удаление реанимационного периода'), iconCls: 'delete16', handler: this.delReanimatPeriod.createDelegate(this) }     //BOB - 12.05.2018
			], tooltip: langs('Действия'),
				iconCls : 'x-btn-text',
				icon: 'img/icons/actions16.png',
				handler: function() {}},
			{
				name: 'group_by',
				key: 'group_by',
				text: langs('Группировать'),
				menu: [{
					name: 'po_palatam',
					text: langs('По палатам'),
					tooltip: langs('По палатам'),
					handler: this.loadGrouped.createDelegate(this, ['po_palatam', 'по палатам'])
				},{
					name: 'po_rejimam',
					text: langs('По режимам'),
					tooltip: langs('По режимам'),
					handler: this.loadGrouped.createDelegate(this, ['po_rejimam', 'по режимам'])
				}, {
					name: 'po_statusam',
					text: langs('По статусам'),
					tooltip: langs('По статусам'),
					handler: this.loadGrouped.createDelegate(this, ['po_statusam', 'по статусам'])
				}
				]
			}
		];
		this.treeActions = new Array();
		for (i=0; i < Actions.length; i++) {
			this.treeActions[Actions[i]['name']] = new Ext.Action(Actions[i]);
			if( Actions[i].menu ) {
				this.treeActions[Actions[i]['name']]['menu'] = {};
				for(var j=0; j<Actions[i].menu.length; j++) {
					this.treeActions[Actions[i]['name']]['menu'][Actions[i].menu[j]['name']] = new Ext.Action(Actions[i].menu[j]);
				}
			}
		}
		delete(Actions);

		this.GroupByButton = new Ext.Button({
			key: 'group_by',
			text: langs('Группировать'),
			tooltip: langs('Группировать'),
			iconCls : 'x-btn-text',
			hidden: false,
			menu: [
				this.treeActions['group_by'].menu.po_palatam,
				this.treeActions['group_by'].menu.po_rejimam,
				this.treeActions['group_by'].menu.po_statusam
			]
		});

		this.treeToolbar = new Ext.Toolbar(
		{
			id: 'mpwpsToolbar',
			items:
			[
				this.treeActions.open_stac_emk,
				{
					xtype : "tbseparator"
				},
				this.treeActions.refresh,
				{
					xtype : "tbseparator"
				},
				this.treeActions.print,
				{
					xtype : "tbseparator"
				},
			//	this.treeActions.kvs_edit,
			//	{
			//		xtype : "tbseparator"
			//	},
			//	this.treeActions.update_ward,
			//	{
			//		xtype : "tbseparator"
			//	},
				/*this.treeActions.update_section,
				{
					xtype : "tbseparator"
				},*/
			//	this.treeActions.update_doctor,
			//	{
			//		xtype : "tbseparator"
			//	},
			//	this.treeActions.leave,
			//	{
			//		xtype : "tbseparator"
			//	},
			//	this.treeActions.accept,
			//	{
			//		xtype : "tbseparator"
			//	},
			//	this.treeActions.actions,
				{
					text: langs('Действия'),
					tooltip: langs('Действия'),
					iconCls : 'x-btn-text',
					icon: 'img/icons/actions16.png',
					menu: [
						this.treeActions.actions.menu.accept,
						this.treeActions.actions.menu.kvs_edit,
						this.treeActions.actions.menu.update_ward,
						this.treeActions.actions.menu.update_doctor,
						this.treeActions.actions.menu.leave,
						this.treeActions.actions.menu.add_patient,
						this.treeActions.actions.menu.move_reanimation,     //BOB - 15.03.2017
						this.treeActions.actions.menu.edit_reanimat_period, //BOB - 19.06.2019
						this.treeActions.actions.menu.change_reanimat_period, //BOB - 02.10.2019
						this.treeActions.actions.menu.end_reanimat_period,	//BOB - 17.11.2017
						this.treeActions.actions.menu.del_reanimat_period	//BOB - 12.05.2018
					],
					listeners: {
						menushow: function(c) {
							this.getTreeAction('add_patient').setDisabled(getStacOptions().disable_patient_additions_for_profile_branches);
						}.createDelegate(this)
					}
				},
				this.GroupByButton,
				{
					xtype : "tbseparator"
				},
				{
					xtype : "tbfill"
				}
			]
		});

		this.contextMenuOtherLpu = new Ext.menu.Menu(
		{
			items: [
				//this.treeActions.open_stac_emk,
				this.treeActions.actions.menu.accept
			],
			listeners: {
				show: function(c) {
					//c.items.items[0].enable();
					//c.items.items[1].enable();
					//this.getTreeAction('open_stac_emk').enable();
					this.getTreeAction('accept').enable();
				}.createDelegate(this),
				hide: function(c) {
					//log(c);
				}
			}
		});
		
		this.contextMenu  = new Ext.menu.Menu(
		{
			items: [
				this.treeActions.open_stac_emk,
				this.treeActions.refresh,
				this.treeActions.print,
			//	this.treeActions.kvs_edit,
			//	this.treeActions.update_ward,
			//	this.treeActions.update_doctor,
			//	this.treeActions.leave
				{
					text: langs('Действия'),
					tooltip: langs('Действия'),
					iconCls : 'x-btn-text',
					icon: 'img/icons/actions16.png',
					menu: [
						//this.treeActions.actions.menu.accept,
						this.treeActions.actions.menu.kvs_edit,
						this.treeActions.actions.menu.update_ward,
						this.treeActions.actions.menu.update_doctor,
						this.treeActions.actions.menu.leave,
						this.treeActions.actions.menu.add_patient,
						this.treeActions.actions.menu.move_reanimation,     //BOB - 15.03.2017
						this.treeActions.actions.menu.edit_reanimat_period, //BOB - 19.06.2019
						this.treeActions.actions.menu.change_reanimat_period, //BOB - 02.10.2019
						this.treeActions.actions.menu.end_reanimat_period,	//BOB - 17.11.2017
						this.treeActions.actions.menu.del_reanimat_period	//BOB - 12.05.2018
					]
				}
			],
			listeners: {
				show: function(c) {
					this.getTreeAction('open_stac_emk').enable();
					this.getTreeAction('refresh').enable();
					this.getTreeAction('print').enable();
					this.getTreeAction('kvs_edit').enable();
					this.getTreeAction('update_ward').enable();
					this.getTreeAction('update_doctor').enable();
					this.getTreeAction('leave').enable();
					this.getTreeAction('add_patient').setDisabled(getStacOptions().disable_patient_additions_for_profile_branches);
					if(this.ARMType == 'stac') { 
						this.getTreeAction('move_reanimation').enable(); //BOB - 15.03.2017
					}      
					this.getTreeAction('end_reanimat_period').enable(); //BOB - 17.11.2017
					this.getTreeAction('del_reanimat_period').enable(); //BOB - 12.05.2017
					this.getTreeAction('edit_reanimat_period').enable(); //BOB - 19.06.2019
					this.getTreeAction('change_reanimat_period').enable(); //BOB - 02.10.2019
				}.createDelegate(this),
				hide: function(c) {
					//log(c);
				}
			}
		});
		
		this.Tree = new Ext.tree.TreePanel(
		{
			id: 'mpwpsTree',
			region: 'center',
			animate:false,
			width: 800,
			enableDD: false,
			autoScroll: true,
			autoLoad:false,
			border: false,
			//rootVisible: false,
			split: true,
			tbar: thas.treeToolbar,
			contextMenu: this.contextMenu,
			listeners:
			{
				contextmenu: function(node, e)
				{

					if (node.attributes.Person_id && !node.attributes.AnotherSection)
					{
						node.getOwnerTree().contextMenu = thas.contextMenu; // меню
						var c = node.getOwnerTree().contextMenu;
						c.contextNode = node;
						c.showAt(e.getXY());
						node.select();
					} else if (node.attributes.Person_id && node.attributes.AnotherSection) {
						node.getOwnerTree().contextMenu = thas.contextMenuOtherLpu; // меню для переведенных (Открыть ЭМК / Принять в отделение)
						var c = node.getOwnerTree().contextMenu;
						c.contextNode = node;
						c.showAt(e.getXY());
						node.select();
					}
					let sm = node.getOwnerTree().getSelectionModel();
					sm.fireEvent('selectionchange', sm, node);
				},
				load: function(node)
				{
					//
				}
			},
			root:
			{
				nodeType: 'async',
				text: langs('Коечная структура отделения'),
				id:'root',
				expanded: false
			},
			rootVisible: false,
			loader: new Ext.tree.TreeLoader(
			{
				listeners:
				{
					load: function(loader, node, response)
					{
						callback:
						{
							thas.getLoadMask(LOAD_WAIT).hide();
							if(node.getDepth() == 0 && thas.firstLoad)
							{
								var cns = node.childNodes;
								for(var i = 0; i < cns.length; i++) {
									if(cns[i].attributes.object && cns[i].attributes.object == 'LpuSectionWard')
									{
										cns[i].expand();
									}
								}
								thas.firstLoad = false;
							}
							if(node.getDepth() > 0 && node.SearchSign)
							{
								var cn = node.childNodes[0];
								//cn.fireEvent('select', cn);
								cn.fireEvent('click', cn);
							}
						}
					},
					loadexception: function(node)
					{
						thas.getLoadMask(LOAD_WAIT).hide();
					},
					beforeload: function (tl, node)
					{
						thas.getLoadMask(LOAD_WAIT).show();
						//запрещаем загрузку при инициализации до получения текущей даты
						if (!thas.curDate)
						{
							return false;
						}
						var LpuSection_id = thas.findById('mpwpsSearch_LpuSection_id').getValue();
						if (node.getDepth()==0)
						{
							tl.baseParams.object = 'LpuSection';
							tl.baseParams.object_id = 'LpuSection_id';
							tl.baseParams.object_value = LpuSection_id;
							if (tl.baseParams.object_value == null) tl.baseParams.object_value = 0; //BOB - 09.06.2018
						}
						else
						{
							tl.baseParams.object = node.attributes.object;
							tl.baseParams.object_id = node.attributes.object_id;
							tl.baseParams.object_value = node.attributes.object_value;
							tl.baseParams.group = node.attributes.group;
						}
						tl.baseParams.level = node.getDepth();
						tl.baseParams.LpuSection_id = LpuSection_id;
						tl.baseParams.ARMType = thas.ARMType;
						var datefield  = Ext.getCmp('mpwpsSearch_date');
						tl.baseParams.date = Ext.util.Format.date(datefield.getValue(), 'd.m.Y');
						tl.baseParams.filter_Person_F = thas.findById('mpwpsSearch_F').getValue();
						tl.baseParams.filter_Person_I = thas.findById('mpwpsSearch_I').getValue();
						tl.baseParams.filter_Person_O = thas.findById('mpwpsSearch_O').getValue();
						tl.baseParams.filter_PSNumCard = thas.findById('mpwpsSearch_PSNumCard').getValue();
						tl.baseParams.filter_Person_BirthDay = Ext.util.Format.date(thas.findById('mpwpsSearch_BirthDay').getValue(), 'd.m.Y');
						tl.baseParams.filter_MedStaffFact_id = thas.findById('mpwpsSearch_MedStaffFact_id').getValue();

                        tl.baseParams.MedService_id = (thas.ARMType == 'reanimation') ? thas.userMedStaffFact.MedService_id : '0';  //BOB - 21.03.2017

                        return true;
					}
				},
				dataUrl:'/?c=EvnSection&m=getSectionTreeData'
			}),
			selModel: new Ext.tree.KeyHandleTreeSelectionModel()
		});

		this.Tree.on('dblclick', function(node)
		{
			if(node.attributes.Person_id)
			{
				Ext.getCmp('swMPWorkPlaceStacWindow').OpenEMK();
			}
		});

		var NumberBedsMark = [
		'<table width="800" style="font-size: 10pt;">'+
		'<tr><td width="100">На <b>'+new Date().format('d.m.Y')+'</b></td><td align="right" width="220">Количество мест в отделении: <b>{LpuSection_BedCount}</b></td><td align="center" width="120">из них:</td><td width="140"><img align="left" style="margin-right: 2px; width: 16px; height: 16px;" src="/img/icons/male16.png" border="0">мужских - <b>{LpuSection_BedCount_men}</b></td><td width="140"><img align="left" style="margin-right: 2px; width: 16px; height: 16px;" src="/img/icons/female16.png" border="0">женских - <b>{LpuSection_BedCount_women}</b></td><td></td></tr>'+
		'<tr><td></td><td align="right">Свободно: <b>{free_BedCount}</b></td><td align="center">из них:</td><td><img align="left" style="margin-right: 2px; width: 16px; height: 16px;" src="/img/icons/male16.png" border="0">мужских - <b>{free_BedCount_men}</b></td><td><img align="left" style="margin-right: 2px; width: 16px; height: 16px;" src="/img/icons/female16.png" border="0">женских - <b>{free_BedCount_women}</b></td><td></td></tr>'+
		'</table>'
		];
		this.NumberBedsTpl = new Ext.Template(NumberBedsMark);

		this.dateMenu = new sw.Promed.SwDateField(
		{
			fieldLabel: langs('Дата'),
			id: 'mpwpsSearch_date',
			plugins: 
			[
				new Ext.ux.InputTextMask('99.99.9999', false)
			],
			xtype: 'swdatefield',
			format: 'd.m.Y',
			hideLabel: true,
			listeners:
			{
				'keydown': function (inp, e) 
				{
					if (e.getKey() == Ext.EventObject.ENTER)
					{
						e.stopEvent();
                        thas.loadTree();
					}
				},
				'change': function (field, newValue, oldValue) 
				{
                    thas.createListLpuSectionWard();
				}
			}
		});
		
		this.formActions = new Array();
		this.formActions.prev = new Ext.Action(
		{
			text: langs('Предыдущий'),
			xtype: 'button',
			iconCls: 'arrow-previous16',
			handler: function()
			{
				// на один день назад
				this.prevDay();
				this.loadTree();
				//this.loadNumberBeds();
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
				this.loadTree();
				//this.loadNumberBeds();
			}.createDelegate(this)
		});
			
		this.EvnJournalPanel = new sw.Promed.EvnJournalFrame({
			id: 'mpwpsEvnJournalPanel',
			region: 'east',
			animCollapse: false,
			collapsible: true,
			split: true,
			layoutConfig:
			{
				titleCollapse: true,
				animate: true,
				activeOnTop: false,
				style: 'border 0px'
			},
			listeners:
			{
				render: function(p) {
					var body_width = Ext.getBody().getViewSize().width;
					p.setWidth(body_width * (1/2));
				}
			}
		});
		this.EvnJournalPanel.setActionHandler('openEmk', function(e,c,p){thas.openEMKFromEvnJournal(p);});

		this.NumberBedsPanel = new Ext.Panel(
		{
			id: 'NumberBedsPanel',
			bodyStyle: 'padding:5px',
			layout: 'fit',
			width: '100%',
			region: 'center',
			border: false,
			frame: false,
			height: 45,
			maxSize: 45,
			html: ''
		});
		
		/*var DocumentStac = [
			'<div id="rightEmkPanel" style="font-family: tahoma,arial,helvetica,sans-serif; font-size: 13px;" onMouseOver="document.getElementById(&quot;toolbar&quot;).style.display=&quot;block&quot;" onMouseOut="document.getElementById(&quot;toolbar&quot;).style.display=&quot;none&quot;">'+
			'<div class="frame evn_pl">'+
			'<div style="width: 0px; margin: 0px; display: none; float: right;" class="columns" id="toolbar"><div class="right"><div class="toolbar"><a onClick="Ext.getCmp(&quot;'+this.id+'&quot;).printEvnDoc();" class="button icon icon-print16" title="Печатать документ"><span></span></a></div></div></div>'+
			'<h1 style="font-size: 12pt;" align="center">{Person_Fio}</h1><br />'+
			'<table border="0" width="100%"><tr>'+
			'<td width="5%" style="vertical-align: top;"><!--<img height="106" width="68" src="/img/{sex_img}" />--></td>'+
			'<td style="font-size: 10pt;">'+
			'<b>Пол:</b> {Sex}<br />'+
			'<b>Дата рождения:</b> {Person_BirthDay}<br />'+
			'<b>Соц. статус:</b> {SocStatus_Name}, <b>СНИЛС:</b> {Person_Snils}<br />'+
			'<b>Регистрация:</b> {Address_Address}<br />'+
			'<b>Полис:</b> {Polis}<br />'+
			'<b>Основное прикрепление:</b> {Lpu_data}<br /><br />'+
			'<b>Отделение:</b> {LpuSection_FullName}<br />'+
			'<b>Лечащий врач:</b> {MPFio}<br /><br />'+
			'<b>Дата и время поступления:</b> {setDT}<br />'+
			'<b>Дата и время выписки:</b> {disDT}<br /><br />'+
			'<b>Нетрудоспособность:</b> {Sticks}<br /><br />'+
			langs('<b>Диагноз:</b> {diag_FullName}<br /><br />')+
			'<b>' + getMESAlias() + ':</b> {Mes}<br /><br />'+
			langs('<b>Оперативное лечение:</b><br />{Surgery}')+
			'</td>'+
			'</tr></table>'+
			'</div></div>'
		];*/
		
		this.Tree.getSelectionModel().on('selectionchange', function(sm, node) {
			// log(node.attributes);
			if(node && node.attributes.EvnPS_id) {
				var EvnPS_id = node.attributes.EvnPS_id;
				var print_menu = this.getTreeAction('print').initialConfig.menu;
			}else{
				return false;
			}

			if (node && node.attributes.Person_id && !node.attributes.AnotherSection) {
				this.getTreeAction('accept').disable();
				this.getTreeAction('refresh').enable();
				this.getTreeAction('open_stac_emk').setDisabled(!EvnPS_id);
				this.getTreeAction('update_ward').setDisabled(!EvnPS_id);
				this.getTreeAction('update_section').setDisabled(!EvnPS_id);
				this.getTreeAction('update_doctor').setDisabled(!EvnPS_id);
				this.getTreeAction('leave').setDisabled(!EvnPS_id);
				this.getTreeAction('kvs_edit').setDisabled(!EvnPS_id);
				this.getTreeAction('end_reanimat_period').setDisabled(!EvnPS_id);  //BOB - 17.11.2017
				this.getTreeAction('del_reanimat_period').setDisabled(!EvnPS_id); //BOB - 12.05.2017
				this.getTreeAction('edit_reanimat_period').setDisabled(!EvnPS_id); //BOB - 19.06.2019
				this.getTreeAction('change_reanimat_period').setDisabled(!EvnPS_id); //BOB - 02.10.2019
				if (this.ARMType == 'stac') {
					this.getTreeAction('move_reanimation').setDisabled(!EvnPS_id); //BOB - 15.03.2017
				}
				if (Ext.isEmpty(node.attributes.PersonEncrypHIV_Encryp)) {
					print_menu.items.itemAt(1).enable();	//Листок прибытия
					print_menu.items.itemAt(2).enable();	//Листок убытия
					print_menu.items.itemAt(3).enable();	//Сопроводительный лист
				} else {
					print_menu.items.itemAt(1).disable();	//Листок прибытия
					print_menu.items.itemAt(2).disable();	//Листок убытия
					print_menu.items.itemAt(3).disable();	//Сопроводительный лист
				}

				if (getRegionNick().inlist(['adygeya']) && node.attributes.DiagFinance_IsRankin == 2) {
					print_menu.items.itemAt(6).show();
				} else {
					print_menu.items.itemAt(6).hide();
				}

				if(this.menuLpuSectionWard) {
					this.menuLpuSectionWard.items.each(function(item,i,l) {
						if(item.Sex_id)
							item.setVisible(item.Sex_id==node.attributes.Sex_id);
						if (getRegionNick() == 'kz' && node.attributes.PayType_id.inlist([150, 151]) && item.LpuSectionWard_id == '0')
							item.hide();
					});
				}
				this.createListLeaveType();
			}
			else if (node && node.attributes.Person_id && node.attributes.AnotherSection) {
                this.getTreeAction('accept').enable();
				this.getTreeAction('open_stac_emk').enable();
                this.getTreeAction('refresh').enable();
				this.getTreeAction('update_ward').disable();
				this.getTreeAction('update_section').disable();
				this.getTreeAction('update_doctor').disable();
				this.getTreeAction('leave').disable();
				this.getTreeAction('kvs_edit').disable();
				if(this.ARMType == 'stac') {
					this.getTreeAction('move_reanimation').disable(); //BOB - 15.03.2017
				}      
				this.getTreeAction('end_reanimat_period').disable();  //BOB - 17.11.2017					
				this.getTreeAction('del_reanimat_period').disable(); //BOB - 12.05.2017
				this.getTreeAction('edit_reanimat_period').disable(); //BOB - 19.06.2019
				this.getTreeAction('change_reanimat_period').disable(); //BOB - 02.10.2019

				print_menu.items.itemAt(1).disable();	//Листок прибытия
				print_menu.items.itemAt(2).disable();	//Листок убытия
				print_menu.items.itemAt(3).disable();	//Сопроводительный лист
			} else {
                this.getTreeAction('accept').disable();
				this.getTreeAction('open_stac_emk').disable();
                this.getTreeAction('refresh').enable();
				this.getTreeAction('update_ward').disable();
				this.getTreeAction('update_section').disable();
				this.getTreeAction('update_doctor').disable();
				this.getTreeAction('leave').disable();
				this.getTreeAction('kvs_edit').disable();
				if(this.ARMType == 'stac') {
					this.getTreeAction('move_reanimation').disable(); //BOB - 15.03.2017
				}      
				this.getTreeAction('end_reanimat_period').disable();  //BOB - 17.11.2017					
				this.getTreeAction('del_reanimat_period').disable(); //BOB - 12.05.2017
				this.getTreeAction('edit_reanimat_period').disable(); //BOB - 19.06.2019
				this.getTreeAction('change_reanimat_period').disable(); //BOB - 02.10.2019

				print_menu.items.itemAt(1).disable();	//Листок прибытия
				print_menu.items.itemAt(2).disable();	//Листок убытия
				print_menu.items.itemAt(3).disable();	//Сопроводительный лист
			}
		}.createDelegate(this));
		
		this.Tree.on('click', function(node){
			if(node.attributes.Person_id)
			{
				this.EvnJournalPanel.loadPage({Person_id: node.attributes.Person_id, reset: true});
			}
			else
			{
                thas.emptyevndoc();
			}
		}.createDelegate(this));

        var form = this;

		var swPromedActions = {
				action_MonitorBirthSpec: { 
					disabled: !(isUserGroup('OperBirth')||isUserGroup('OperRegBirth')),
					text: getRegionNick().inlist(['ufa']) ? 'Мониторинг детей первого года жизни' : 'Мониторинг новорожденных',
					tooltip: 'Мониторинг новорожденных',
					iconCls : 'doc-reg16', 
					handler: function() {
						getWnd('swMonitorBirthSpecWindow').show({userMedStaffFact: this.userMedStaffFact});
					}.createDelegate(this)
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
                        getWnd('swOnkoRegistryWindow').show({userMedStaffFact: this.userMedStaffFact});
                    }.createDelegate(this)
                },
                OrphanRegistry: sw.Promed.personRegister.getOrphanBtnConfig(form.id, form),
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
                VenerRegistry:
                {
                    tooltip: langs('Регистр больных венерическим заболеванием'),
                    text: langs('Регистр больных венерическим заболеванием'),
                    iconCls : 'doc-reg16',
                    disabled: (String(getGlobalOptions().groups).indexOf('Vener', 0) < 0),
                    handler: function()
                    {
                        getWnd('swVenerRegistryWindow').show({userMedStaffFact: this.userMedStaffFact});
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
				PreOnkoRegistry: {
					text: 'Регистр по предраковому состоянию',
					iconCls : 'doc-reg16',
					hidden: !getRegionNick().inlist(['perm', 'msk']),
                    disabled: !isUserGroup('PreOnkoRegistryFull') && !isUserGroup('PreOnkoRegistryView'),
					handler: function() {
						getWnd('swPreOnkoRegistryWindow').show({userMedStaffFact: this.userMedStaffFact});
					}.createDelegate(this)
				},
                NephroRegistry:
                {
                    tooltip: langs('Регистр по нефрологии'),
                    text: langs('Регистр по нефрологии'),
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
                                msg: langs('Окно уже открыто'),
                                title: ERR_WND_TIT
                            });
                            return false;
                        }
                        getWnd('swNephroRegistryWindow').show({userMedStaffFact: this.userMedStaffFact});
                    }.createDelegate(this)
                },
                ProfRegistry:
                {
                    tooltip: langs('Регистр по профзаболеваниям'),
                    text: langs('Регистр по профзаболеваниям'),
                    iconCls : 'doc-reg16',
                    hidden: (true || 'perm' != getRegionNick()),
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
                NolosRegistry: sw.Promed.personRegister.getVznBtnConfig(form.id, form),
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
					tooltip: lang['registr_reability'],
					text: lang['registr_reability'],
					iconCls: 'doc-reg16',
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
				},
				//BOB - 09.10.2017
                ReanimatRegistry: {
                    tooltip: 'Регистр реанимации', // langs('Регистр реанимации'),
                    text: 'Регистр реанимации', //langs('Регистр реанимации'),
                    iconCls : 'doc-reg16',
                    hidden: (!getRegionNick().inlist([ 'ufa' ])),
                    /*disabled: (String(getGlobalOptions().groups).indexOf('ReanimatRegistry', 0) < 0),*/
                    handler: function()
                    {

                        if ( getWnd('swReanimatRegistryWindow').isVisible() ) {
                            sw.swMsg.show({
                                buttons: Ext.Msg.OK,
                                fn: Ext.emptyFn,
                                icon: Ext.Msg.WARNING,
                                msg: langs('Окно уже открыто'),
                                title: ERR_WND_TIT
                            });
                            return false;
                        }
						//BOB - 25.01.2017
                        getWnd('swReanimatRegistryWindow').show({userMedStaffFact: this.userMedStaffFact});
                    }.createDelegate(this)
                },
				//BOB - 09.10.2017
				HTMRegister: {
					tooltip: langs('Регистр ВМП'),
					text: langs('Регистр ВМП'),
					iconCls: 'doc-reg16',
					hidden: !isUserGroup('SuperAdmin') && !isUserGroup('HTMRegister') && !isUserGroup('HTMRegister_Admin') || !getRegionNick().inlist(['ufa']),
					handler: function() {
						ShowWindow('swHTMRegisterWindow',{});
					}
				},
				ONMKRegistry: {
					tooltip: 'Регистр ОНМК',
					text: 'Регистр ОНМК',
					iconCls : 'doc-reg16',					
					handler: function()
					{   
						if ( getWnd('swONMKRegistryViewWindow').isVisible() ) {
							sw.swMsg.show({
								buttons: Ext.Msg.OK,
								fn: Ext.emptyFn,
								icon: Ext.Msg.WARNING,
								msg: 'Окно уже открыто',
								title: ERR_WND_TIT
							});
							return false;
						}
						getWnd('swONMKRegistryViewWindow').show({userMedStaffFact: this.userMedStaffFact});
					}.createDelegate(this)
				}
				
		};
		// Конфиги акшенов для левой панели
		var configActions = 
		{
			action_EditSchedule: {
				handler: function() {
					getWnd('swScheduleEditMasterWindow').show({
						UserMedStaffFact_id: thas.userMedStaffFact.MedStaffFact_id,
						fromArm: 'stac'
					});
				},
				iconCls: 'schedule32',
				nn: 'action_EditSchedule',
				hidden: !(getRegionNick().inlist(['msk','vologda', 'ufa']) && isUserGroup('SchedulingPS')),
				text: 'Ведение расписания',
				tooltip: 'Ведение расписания'
			},
			action_JourDirection: {nn:'action_JourDirection', text:langs('Журнал направлений'), tooltip: langs('Открыть журнал направлений'), iconCls : 'mp-directions32', /*disabled: getGlobalOptions().minimal,*/ handler: function() {getWnd('swEvnDirectionJournalWindow').show({ userMedStaffFact: this.userMedStaffFact });}.createDelegate(this)},
            action_JourReception: {nn:'action_JourReception', hidden: true, disabled: true, text:langs('Журнал приемного отделения'), tooltip: langs('Открыть журнал приемного отделения'), iconCls : 'reception32', handler: function() {return false;}.createDelegate(this)},
            //action_JourScreening: {nn:'action_JourScreening', hidden: (IS_DEBUG != 1), disabled: true, text:'Журнал обследования', tooltip: 'Открыть журнал исследований и консультаций', iconCls : 'consult32', handler: function() {return false;}.createDelegate(this)},
            action_JourSurgery: {nn:'action_JourSurgery', hidden: true, disabled: true, text:langs('Журнал оперативных вмешательств'), tooltip: langs('Открыть журнал оперативных вмешательств'), iconCls : 'surgery32', handler: function() {return false;}.createDelegate(this)},
			action_JourBedDowntime: 
			{
				id: 'action_JourBedDowntime',
				nn: 'action_JourBedDowntime', 
				text:langs('Журнал простоя коек'), 
				tooltip: langs('Открыть журнал простоя коек'), 
				iconCls : 'mp-hospital-list32', 
				hidden: ('msk' != getRegionNick() || this.ARMType == 'reanimation'), 
				handler: function() {
					getWnd('swBedDowntimeJournalWindow').show({userMedStaffFact: this.userMedStaffFact});
				}.createDelegate(this)
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
			/**
			 * Добавлен метод по задаче: https://redmine.swan-it.ru/issues/193369
			 * от 10.04.2020 ggegamyan
			 */
			action_EvnJournalDirectionRecord:
				{
					// Доступен только для Москвы, согласно ТЗ.
					disabled: !(getRegionNick().inlist(['msk'])),
					handler: function()
					{
						getWnd('swMPQueueWindow').show({
							userMedStaffFact: form.userMedStaffFact
						});
					},
					iconCls: 'mp-queue32',
					nn: 'action_EvnJournalDirectionRecord',
					hidden: !(getRegionNick().inlist(['msk'])),
					text: WND_DIRECTION_JOURNAL,
					tooltip: WND_DIRECTION_JOURNAL
				},
			action_JourEvnPrescr: {nn:'action_JourEvnPrescr', text:langs('Журнал назначений'), tooltip: langs('Открыть журнал назначений'), iconCls : 'therapy-plan32', disabled: false, handler: function() {getWnd('swEvnPrescrJournalWindow').show({userMedStaffFact: this.userMedStaffFact});}.createDelegate(this)},
			//action_JourCmpProc: {nn:'action_JourCmpProc', text:langs('Журнал выполнения процедур'), tooltip: langs('Открыть журнал выполнения процедур'), iconCls : 'book32', handler: function() {getWnd('swEvnPrescrProcCmpJournalWindow').show({userMedStaffFact: this.userMedStaffFact});}.createDelegate(this)},
			action_MonitorBirthSpec: {nn:'action_MonitorBirthSpec', hidden: (!isSuperAdmin()),  text:langs('Мониторинг новорожденных'), tooltip: langs('Мониторинг новорожденных'), iconCls : 'surgery32', handler: function() {getWnd('swMonitorBirthSpecWindow').show({userMedStaffFact: this.userMedStaffFact});}.createDelegate(this)},
			/*action_JournalHospit: 
			{
				nn: 'action_JournalHospit',
				tooltip: langs('Открыть журнал госпитализаций'),
				text: langs('Журнал госпитализаций'),
				iconCls : '',
				hidden: !IS_DEBUG,
				disabled: !isAdmin, 
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
					getWnd('swJournalHospitWindow').show();
				}
			},*/
			action_JourNotice: {nn:'action_JourNotice', text:langs('Журнал уведомлений'), tooltip: langs('Открыть журнал уведомлений'), iconCls : 'notice32', handler: function() {getWnd('swMessagesViewWindow').show();}.createDelegate(this)},
            action_JourLeave: {nn:'action_JourLeave', text:langs('Журнал выбывших'), tooltip: langs('Открыть журнал выбывших'), iconCls : 'mp-region32', handler: function() {getWnd('swJournalLeaveWindow').show({userMedStaffFact: this.userMedStaffFact});}.createDelegate(this)},

			// #175117. Кнопка для открытия формы "Журнал учета рабочего времени сотрудников":
			action_TimeJournal:
			{
				nn: 'action_TimeJournal',
				text: langs('Журнал учета рабочего времени сотрудников'),
				tooltip: langs('Открыть журнал учета рабочего времени сотрудников'),
				iconCls: 'report32',
				disabled: false,

				handler:
					function()
					{
						var cur = sw.Promed.MedStaffFactByUser.current;

						getWnd('swTimeJournalWindow').show(
							{
								ARMType: (cur ? cur.ARMType : undefined),
								MedStaffFact_id: (cur ? cur.MedStaffFact_id : undefined),
								Lpu_id: (cur ? cur.Lpu_id : undefined)
							});
					}
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
			action_Vaccination:
			{
				nn: 'action_Vaccination',
				tooltip: 'Открыть карту профилактических прививок',
				text: 'Иммунопрофилактика',
				iconCls : 'vac-plan32',
				hidden: ('vologda' != getRegionNick()),
				handler: function()
				{
					var form = this;
					var node = this.Tree.getSelectionModel().selNode;
					if (!node || !node.attributes || !node.attributes.Person_id) {
						sw.swMsg.show({
							buttons: Ext.Msg.OK,
							fn: Ext.emptyFn,
							icon: Ext.Msg.WARNING,
							msg: 'Выберите человека',
							title: ERR_WND_TIT
						});
						return false;
					}
					getWnd('amm_Kard063').show({person_id: node.attributes.Person_id});
				}.createDelegate(this)
			},
			action_Register: 
			{
				nn: 'action_Register',
				tooltip: langs('Регистры'),
				text: langs('Регистры'),
				iconCls : 'registry32',
				disabled: false, 
				menuAlign: 'tr?',
				menu: new Ext.menu.Menu({
					items: [
                        swPromedActions.PregnancyRegistry,
                        swPromedActions.PersonDispOrpSearch,
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
						swPromedActions.IPRARegistry,
						swPromedActions.ECORegistry,
						swPromedActions.OKSRegistry,
						swPromedActions.EndoRegistry,
						swPromedActions.SuicideRegistry,
						swPromedActions.PalliatRegistry,
						swPromedActions.action_MonitorBirthSpec,
						swPromedActions.ReanimatRegistry,  //BOB - 09.10.2017
						swPromedActions.HTMRegister,
						swPromedActions.ONMKRegistry,
						swPromedActions.PreOnkoRegistry
					]
				})
			},
			action_References: {
				nn: 'action_References',
				tooltip: langs('Справочники'),
				text: langs('Справочники'),
				iconCls : 'book32',
				disabled: false,
				menuAlign: 'tr?',
				menu: new Ext.menu.Menu({
					items: [{
						tooltip: langs('МКБ-10'),
						text: langs('Справочник МКБ-10'),
						iconCls: 'spr-mkb16',
						handler: function() {
							if ( !getWnd('swMkb10SearchWindow').isVisible() )
								getWnd('swMkb10SearchWindow').show();
						}
					},
					sw.Promed.Actions.swPrepBlockSprAction,
					sw.Promed.Actions.swDrugDocumentSprAction
					]
				})
			},
			action_WorkGraph: {
				handler: function() {
					getWnd('swWorkGraphSearchWindow').show();
				}.createDelegate(this),
				hidden: !(isUserGroup('WorkGraph') || isLpuAdmin()),
				iconCls: 'sched-16',
				nn: 'action_WorkGraph',
				text: 'Графики дежурств',
				tooltip: 'Графики дежурств'
			},
			action_reports: //http://redmine.swan.perm.ru/issues/18509
			{
				nn: 'action_Report',
				tooltip: langs('Просмотр отчетов'),
				text: langs('Просмотр отчетов'),
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
						swPromedActions.EvnOnkoNotify
					]
				})
			},
            action_Templ: {
                handler: function() {
                    var params = {
                        LpuSection_id: thas.userMedStaffFact.LpuSection_id,
                        MedPersonal_id: thas.userMedStaffFact.MedPersonal_id,
                        MedStaffFact_id: thas.userMedStaffFact.MedStaffFact_id,
                        XmlType_id: 8,
                        allowSelectXmlType: true,
                        EvnClass_id: 32
                    };
                    getWnd('swTemplSearchWindow').show(params);
                },
                iconCls : 'docs_templ-16',
                nn: 'action_Templ',
                text: langs('Шаблоны документов'),
                tooltip: langs('Шаблоны документов')
            },
			action_VKJournal: {
				handler: function() {
					getWnd('swVKJournalWindow').show({
						userMedStaffFact: thas.userMedStaffFact
					});
				},
				hidden: !getRegionNick().inlist(['perm', 'vologda']) || !isUserGroup('DepHead'),
				iconCls: 'vkqueryjournal32',
				nn: 'action_VKJournal',
				text: 'Журнал запросов ВК',
				tooltip: 'Журнал запросов ВК'
			},
			action_CarAccidents: {
				iconCls : 'pol-dtp16',
				nn: 'action_CarAccidents',
				text: langs('Извещения о ДТП'),
				tooltip: langs('Извещения о ДТП'),
				menu: new Ext.menu.Menu({
					items: [
						{
							text: langs('Извещения ДТП о раненом: Просмотр'),
							tooltip: langs('Извещения ДТП о раненом: Просмотр'),
							iconCls: 'stac-accident-injured16',
							handler: function()
							{
								getWnd('swEvnDtpWoundWindow').show();
							},
							hidden: false
						},
						{
							text: langs('Извещения ДТП о скончавшемся: Просмотр'),
							tooltip: langs('Извещения ДТП о скончавшемся: Просмотр'),
							iconCls: 'stac-accident-dead16',
							handler: function()
							{
								getWnd('swEvnDtpDeathWindow').show();
							},
							hidden: false
						}
					]
				})
			},
			action_Svid: {
				nn: 'action_Svid',
				tooltip: langs('Свидетельства'),
				text: langs('Свидетельства'),
				iconCls : 'medsvid32',
				disabled: false,
				menuAlign: 'tr?',
				hidden: !isMedSvidAccess(),
				menu: new Ext.menu.Menu({
					items: [
						{
							text: langs('Свидетельства о рождении'),
							tooltip: langs('Свидетельства о рождении'),
							iconCls: 'svid-birth16',
							handler: function()
							{
								getWnd('swMedSvidBirthStreamWindow').show({action: 'view', ARMType: 'stac'});
							},
							hidden: false
						},
						{
							text: langs('Свидетельства о смерти'),
							tooltip: langs('Свидетельства о смерти'),
							iconCls: 'svid-death16',
							handler: function()
							{
								getWnd('swMedSvidDeathStreamWindow').show({ARMType: 'stac'});
							}
						},
						{
							text: langs('Свидетельства о перинатальной смерти'),
							tooltip: langs('Свидетельства о перинатальной смерти'),
							iconCls: 'svid-pdeath16',
							handler: function()
							{
								getWnd('swMedSvidPntDeathStreamWindow').show({ARMType: 'stac'});
							}
						},
						{
							text: langs('Печать бланков свидетельств'),
							tooltip: langs('Печать бланков свидетельств'),
							iconCls: 'svid-blank16',
							handler: function()
							{
								getWnd('swMedSvidSelectSvidType').show();
							},
							hidden: false
						}
					]
				})
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
			action_QueryEvn:
			{
				disabled: false, 
				handler: function() 
				{
					getWnd('swQueryEvnListWindow').show({ARMType: 'stac'});
				},
				iconCls: 'mail32',
				nn: 'action_QueryEvn',
				text: 'Журнал запросов',
				tooltip: 'Журнал запросов'
			}
		};
		// Копируем все действия для создания панели кнопок
        thas.PanelActions = {};
		var Only16pxIconActions = ['action_WorkGraph','action_Templ', 'action_CarAccidents'];

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
		var actions_list = ['action_EditSchedule', 'action_JourBedDowntime', 'action_WorkGraph', 'action_EvnPrescrMseJournal', 'action_JourEvnPrescr','action_JourDirection','action_JourReception'/*,'action_JourScreening'*/,'action_JourSurgery','action_JourNotice','action_JourCmpProc','action_JourCons', 'action_JourLeave', 'action_TimeJournal' /* #175117 */, 'action_PathoMorph','action_Vaccination','action_Register', 'action_References', 'action_reports','action_Notify', 'action_Templ', (getRegionNick() != 'kz')?'action_CarAccidents':'', 'action_Svid', 'action_Ers', 'action_QueryEvn',
			// Добавлен по задаче: https://redmine.swan-it.ru/issues/193369 от 10.04.2020 ggegamyan
			'action_EvnJournalDirectionRecord'];
		if (getRegionNick().inlist(['perm', 'vologda']) && isUserGroup('DepHead')) {
			actions_list.push('action_VKJournal');
		}
		// Создание кнопок для панели
        thas.BtnActions = new Array();
		var i = 0;
		for(var key in thas.PanelActions)
		{
			if (key.inlist(actions_list))
			{
                thas.BtnActions.push(new Ext.Button(thas.PanelActions[key]));
				i++;
			}
		}
		this.leftMenu = new Ext.Panel(
		{
			region: 'center',
			border: false,
			id: thas.id + '_hhd',
			layout:'form',
			layoutConfig:
			{
				titleCollapse: true,
				animate: true,
				activeOnTop: false
			},
			items: thas.BtnActions
		});
		this.leftPanel =
		{
			animCollapse: false,
			width: 60,
			minSize: 60,
			maxSize: 120,
			id: 'mpwpsLeftPanel',
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
					el = thas.findById(thas.id + '_slid');
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
						var el = thas.findById(thas.id + '_hhd');
						var d = el.body.dom;
						d.scrollTop -=38;
					}
				}),
				{
					border: false,
					layout:'border',
					id: thas.id + '_slid',
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
					var el = thas.findById(thas.id + '_hhd');
					var d = el.body.dom;
					d.scrollTop +=38;
					
				}
				})
			]
		};

		this.DoctorToolbar = new Ext.Toolbar(
		{
			id: 'DoctorToolbar',
			items:
			[
				this.formActions.prev,
				{
					xtype : "tbseparator"
				},
				this.dateMenu,
				{
					xtype : "tbseparator"
				},
				this.formActions.next
			]
		});

		// Элементы верхней панели (период, поиск)
		this.TopPanel = new Ext.Panel(
		{
			region: 'north',
			frame: true,
			border: false,
			autoHeight: true,
			tbar: this.DoctorToolbar,
			items:
			[
				this.FilterPanel
			]
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
					id: 'mpwpsSchedulePanel',
					items:
					[
						this.Tree,
						this.EvnJournalPanel
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
		sw.Promed.swMPWorkPlaceStacWindow.superclass.initComponent.apply(this, arguments);
	}
});
