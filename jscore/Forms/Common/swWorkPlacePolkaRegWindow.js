/**
* АРМ регистратора поликлиники
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @autor		
* @copyright    Copyright (c) 2012 Swan Ltd.
* @version      апрель.2012
*/
sw.Promed.swWorkPlacePolkaRegWindow = Ext.extend(sw.Promed.swWorkPlaceWindow, {
	useUecReader: false,
	electronicQueueCancelCallTimer: 30, // настрока находится на очереди, по умолчанию пусть будет 30
	electronicQueueCancelCallTimerActivated: false,
	cancelCallTimerRunner: null,
	cancelCallTimerTask: null,
	calledRecord: null,
	AttachmentLpuBuilding_id: 0,
	buttons: [
		'-',
		{
			text: BTN_FRMHELP,
			iconCls: 'help16',
			handler: function(button, event) {
				ShowHelp(this.ownerCt.title);
			}
		}, {
			text      : 'Закрыть',
			tabIndex  : -1,
			tooltip   : 'Закрыть',
			iconCls   : 'cancel16',
			handler   : function() {
				this.ownerCt.hide();
			}
		}
	],
	listeners: {
		activate: function(){
			sw.Applets.BarcodeScaner.startBarcodeScaner({callback: this.getDataFromBarcode.createDelegate(this)});
			sw.Applets.bdz.startBdzReader({callback: this.getDataFromBdz.createDelegate(this)});
			sw.Applets.uec.startUecReader({callback: this.getDataFromUec.createDelegate(this)});
		},
		deactivate: function() {
			sw.Applets.bdz.stopBdzReader();
			sw.Applets.uec.stopUecReader();
			sw.Applets.BarcodeScaner.stopBarcodeScaner();
		}
	},

	getLoadMask: function() {
		if ( !this.loadMask ) {
			this.loadMask = new Ext.LoadMask(this.getEl(), {msg: 'Подождите... '});
		}

		return this.loadMask;
	},
	id: 'swWorkPlacePolkaRegWindow',
	getDataFromUec: function(uecData, person_data) {
		log(uecData);
		log(person_data);
		// для армов: ищем и заполняем фильтры, выполняем поиск
		// обычно это this.FilterPanel, для остальных вводим параметр..
		if (this.FilterPanel) {
			var filterpanel = this.FilterPanel;
		} else {
			
		}
		// если фильтры свернуты - разворачиваем
		if (filterpanel.fieldSet) {
			// для BaseWorkPlaceFilterPanel именно так:
			if (!filterpanel.fieldSet.expanded) {
				filterpanel.fieldSet.expand();
			}
		} else {
			// для остальных ищем свёрнутую панельку с фильтрами
			if (filterpanel.items && filterpanel.items.items && filterpanel.items.items[0]) {
				var fieldSet = filterpanel.items.items[0];
				if (typeof fieldSet.expand == 'function' && !fieldSet.expanded) {
					fieldSet.expand();
				}
			}
		}
		var filterform = filterpanel.getForm();
		// ищем на форме нужные поля для заполнения в фильтрах
		var surnamefield = this.findFieldByNames(filterform, ['Search_SurName', 'Person_Surname', 'Person_SurName']); // фамилия
		var firnamefield = this.findFieldByNames(filterform, ['Search_FirName', 'Person_Firname', 'Person_FirName']); // имя
		var secnamefield = this.findFieldByNames(filterform, ['Search_SecName', 'Person_Secname', 'Person_SecName']); // отчество
		var birthdayfield = this.findFieldByNames(filterform, ['Search_BirthDay', 'Person_Birthday', 'Person_BirthDay']); // дата рождения
		
		var polisfield = null;
		if (getRegionNick().inlist(['ufa'])) {
			polisfield = this.findFieldByNames(filterform, ['Polis_Num']); // единый номер полиса
		} else {
			polisfield = this.findFieldByNames(filterform, ['Person_Code']); // единый номер полиса
		}
		 
		// если нашли хоть одно поле, то заполняем
		var count = 0;
		if (!Ext.isEmpty(surnamefield) && typeof surnamefield.setValue == 'function') {
			surnamefield.setValue(uecData.surName);
			count++;
		}
		if (!Ext.isEmpty(firnamefield) && typeof firnamefield.setValue == 'function') {
			firnamefield.setValue(uecData.firName);
			count++;
		}
		if (!Ext.isEmpty(secnamefield) && typeof secnamefield.setValue == 'function') {
			secnamefield.setValue(uecData.secName);
			count++;
		}
		if (!Ext.isEmpty(birthdayfield) && typeof birthdayfield.setValue == 'function') {
			birthdayfield.setValue(uecData.birthDay);
			count++;
		}
		if (!Ext.isEmpty(polisfield) && typeof polisfield.setValue == 'function') {
			polisfield.setValue(uecData.polisNum);
			count++;
		}
		// выполняем поиск, обычно это this.doSearch()
		if (count > 0) {
			this.doSearch();
		}
	},
	getDataFromBdz: function(barcodeData, person_data) {
		var form = this;
		var grid = this.MainViewFrame.getGrid();

		var f = form.FilterPanel.getForm();
		if (f && f.findField('Person_Surname')) {
			form.FilterPanel.getForm().findField('Person_Surname').setValue(barcodeData.surName);
			form.FilterPanel.getForm().findField('Person_Firname').setValue(barcodeData.firName);
			form.FilterPanel.getForm().findField('Person_Secname').setValue(barcodeData.secName);
			form.FilterPanel.getForm().findField('Person_Birthday').setValue(barcodeData.birthDay);
			//Если человек был найден по коду ОМС, подставляем его в форму поиска. Иначе - оставляем пустое значение:
			(person_data['resultType']==1)?form.FilterPanel.getForm().findField('Polis_Num').setValue(barcodeData.polisNum):form.FilterPanel.getForm().findField('Polis_Num').setValue('');
		}
		var p = {};
		p.Person_Surname = barcodeData.surName;
		p.Person_Firname = barcodeData.firName;
		p.Person_Secname = barcodeData.secName;
		p.Person_Birthday = barcodeData.birthDay;
		(person_data['resultType']==1)?p.Polis_Num = barcodeData.polisNum: p.Polis_Num=''; //Если человек был найден по коду ОМС, подставляем его в форму поиска. Иначе - оставляем пустое значение.
		//var params = Ext.apply(form.FilterPanel.getForm().getValues(), form.searchParams || {});
		form.MainViewFrame.removeAll({clearAll:true});
		form.MainViewFrame.loadData({
			globalFilters: p,
			callback: function() {
				var form = this;
				var f = false;
				grid.getStore().each(function(record) {
					if (record.get('Person_id') == person_data.Person_id) {
						var index = grid.getStore().indexOf(record);
						grid.getView().focusRow(index);
						grid.getSelectionModel().selectRow(index);
						f = true;
						return;
					}
				});

				//form.openPersonCardEditWindow('view');
				if (!f) { // Если не нашли в гриде
					// todo: Еще надо проверку в принципе на наличие такого человека в БД, и если нет - предлагать добавлять
					// Открываем на добавление
					/*
					var params = {};
					params.action = 'add';
					params.Person_id = Person_id;
					params.PersonEvn_id = (response_obj[0].PersonEvn_id)?response_obj[0].PersonEvn_id:null;
					params.Server_id = (response_obj[0].Server_id)?response_obj[0].Server_id:null;
					params.swPersonSearchWindow = getWnd('swPersonSearchWindow');
					*/
				}
			}.createDelegate(form)
		});
	},
	getLpuBuildingByMedServiceId: function(cb){
		var params = {};
		var callback = cb || false;
		Ext.Ajax.request({
			params: params,
			failure: function (result_form, action) {
				log('error getLpuBuildingByMedServiceId');
			},
			callback: function(options, success, response) {
				if (success && response.responseText != ''){
					var response_obj = Ext.util.JSON.decode(response.responseText);
					if(response_obj && parseInt(response_obj[0]['LpuBuilding_id'])){
						this.AttachmentLpuBuilding_id = parseInt(response_obj[0]['LpuBuilding_id']);
					}else{
						this.AttachmentLpuBuilding_id = 0;
					}
					if(callback && typeof callback == 'function') callback();
				}
				else{
					this.AttachmentLpuBuilding_id = null;
					log('error getLpuBuildingByMedServiceId');
				}
			}.createDelegate(this),
			url: '?c=PersonAmbulatCard&m=getLpuBuildingByMedServiceId'
		});
	},
	getDataFromBarcode: function(barcodeData, person_data) {
		var form = this;
		var grid = this.MainViewFrame.getGrid();

		var f = form.FilterPanel.getForm();
		if (f && f.findField('Person_Surname')) {
			form.FilterPanel.getForm().findField('Person_Surname').setValue(barcodeData.Person_Surname);
			form.FilterPanel.getForm().findField('Person_Firname').setValue(barcodeData.Person_Firname);
			form.FilterPanel.getForm().findField('Person_Secname').setValue(barcodeData.Person_Secname);
			form.FilterPanel.getForm().findField('Person_Birthday').setValue(barcodeData.Person_Birthday);
			//Если человек был найден по коду ОМС, подставляем его в форму поиска. Иначе - оставляем пустое значение:
			(person_data['resultType']==1)?form.FilterPanel.getForm().findField('Polis_Num').setValue(barcodeData.Polis_Num):form.FilterPanel.getForm().findField('Polis_Num').setValue('');
		}
		var p = {};
		p.Person_Surname = barcodeData.Person_Surname;
		p.Person_Firname = barcodeData.Person_Firname;
		p.Person_Secname = barcodeData.Person_Secname;
		p.Person_Birthday = barcodeData.Person_Birthday;
		(person_data['resultType']==1)?p.Polis_Num = barcodeData.Polis_Num: p.Polis_Num=''; //Если человек был найден по коду ОМС, подставляем его в форму поиска. Иначе - оставляем пустое значение.
		//var params = Ext.apply(form.FilterPanel.getForm().getValues(), form.searchParams || {});
		form.MainViewFrame.removeAll({clearAll:true});
		form.MainViewFrame.loadData({
			globalFilters: p,
			callback: function() {
				var form = this;
				var f = false;
				grid.getStore().each(function(record) {
					if (record.get('Person_id') == person_data.Person_id) {
						var index = grid.getStore().indexOf(record);
						grid.getView().focusRow(index);
						grid.getSelectionModel().selectRow(index);
						f = true;
						return;
					}
				});

				//form.openPersonCardEditWindow('view');
				if (!f) { // Если не нашли в гриде
					// todo: Еще надо проверку в принципе на наличие такого человека в БД, и если нет - предлагать добавлять
					// Открываем на добавление
					/*
					var params = {};
					params.action = 'add';
					params.Person_id = Person_id;
					params.PersonEvn_id = (response_obj[0].PersonEvn_id)?response_obj[0].PersonEvn_id:null;
					params.Server_id = (response_obj[0].Server_id)?response_obj[0].Server_id:null;
					params.swPersonSearchWindow = getWnd('swPersonSearchWindow');
					*/
				}
			}.createDelegate(form)
		});
	},
	openPersonCardEditWindow: function(action) {
		if ( !action || !action.toString().inlist([ /*'add', 'edit',*/ 'view']) ) {
			return false;
		}

		if ( getWnd('swPersonCardEditWindow').isVisible() ) {
			sw.swMsg.alert('Сообщение', 'Окно редактирования карты пациента уже открыто');
			return false;
		}

		var grid = this.MainViewFrame.getGrid();

		if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('PersonCard_id') ) {
			return false;
		}

		var selected_record = grid.getSelectionModel().getSelected();

		getWnd('swPersonCardEditWindow').show({
			action: action,
			callback: function() {
				grid.getStore().reload();
			},
			onHide: function() {
				//sw.Applets.uec.startUecReader();
				//sw.Applets.bdz.startBdzReader();
				//sw.Applets.BarcodeScaner.startBarcodeScaner();
			}.createDelegate(this),
			PersonCard_id: selected_record.get('PersonCard_id'),
			Person_id: selected_record.get('Person_id'),
			Server_id: selected_record.get('Server_id')
		});
	},
	/**
	 * Очищает поля фильтра и гриды
	 * Перекрывает родительский метод
	 */
	doReset: function()
	{
		sw.Promed.swWorkPlacePolkaRegWindow.superclass.doReset.apply(this, arguments); // выполняем базовый метод
		this.MainViewFrame.removeAll({clearAll:true});
		this.currentRecord = null;
		this.PersonAmbulatCard.removeAll();
		this.MainViewFrame.onRowSelect(null, null, null);
		this.bj.mainGrid.removeAll();
		// кнопка "обновить" должна быть недоступна если нет данных
		//this.HepatitisRegistrySearchFrame.ViewActions.open_emk.setDisabled(true);

		this.FilterPanel.getForm().findField(getRegionNick() == 'kz' ? 'Person_Inn' : 'Person_Surname').focus(true, 200);
	},
	addUser: function()
	{
		var base_form = this.FilterPanel.getForm();
		
		getWnd('swPersonEditWindow').show({
			action: 'add',
			fields: {
				'Person_SurName': base_form.findField('Person_Surname').getValue().toUpperCase(),
				'Person_FirName': base_form.findField('Person_Firname').getValue().toUpperCase(),
				'Person_SecName': base_form.findField('Person_Secname').getValue().toUpperCase(),
				'Person_BirthDay': base_form.findField('Person_Birthday').getValue(),
				'Polis_Ser': base_form.findField('Polis_Ser').getValue(),
				'Polis_Num': base_form.findField('Polis_Num').getValue(),
				'Federal_Num': base_form.findField('Person_Code').getValue()
			},
			callback: function(callback_data) {
				getWnd('swPersonEditWindow').hide();
			},
			onClose: function() {
				base_form.findField('Person_Surname').focus(true, 500);
			}
		});
	},
	/**
	* Осуществить незапланированную запись к выбранному врачу
	*/
	recordUnScheduled: function() {
		var personData = this.MainViewFrame.getParamsIfHasPersonData();
		if(personData && personData.Person_IsDead=="true"){
			log(personData);
				Ext.Msg.alert('Ошибка', 'Запись на незапланированный прием невозможна в связи со смертью пациента!');
			return false;
		}
		//log(personData);return false;
		if (!personData || !personData.Person_id) {
            personData = null;
		}
		getWnd('swMedStaffFactSelectWindow').show({
			medStaffFactGlobalStoreFilters: {
				onDate: getGlobalOptions().date,
				isPolkaAndStom: true
			},
			onSelect: function(selectedParams) {
				if (!selectedParams || !selectedParams.medStaffFactRecord || !selectedParams.lpuSectionRecord) {
					sw.swMsg.alert('Ошибка', 'Ошибка получения параметров выбранного врача');
					return false;
				}
				// осуществлять незапланированную запись к выбранному врачу с текущим временем
				var directionData = {
					LpuUnitType_SysNick: 'polka'//selectedParams.lpuSectionRecord.get('LpuSectionProfile_Code')
					,EvnQueue_id: null
					,QueueFailCause_id: null
					,Lpu_did: selectedParams.medStaffFactRecord.get('Lpu_id') // ЛПУ куда направляем
					,LpuUnit_did: selectedParams.lpuSectionRecord.get('LpuUnit_id')
					,LpuSection_did: selectedParams.medStaffFactRecord.get('LpuSection_id')
					,EvnUsluga_id: null
					,LpuSection_id: null
					,EvnDirection_pid: null
					,EvnPrescr_id: null
					,PrescriptionType_Code: null
					,DirType_id: null
					,LpuSectionProfile_id: selectedParams.lpuSectionRecord.get('LpuSectionProfile_id')
					,Diag_id: null
					,ARMType_id: this.userMedStaffFact.ARMType_id
					,MedStaffFact_id: selectedParams.medStaffFactRecord.get('MedStaffFact_id')
					,MedPersonal_id: this.userMedStaffFact.MedPersonal_id
					,MedPersonal_did: selectedParams.medStaffFactRecord.get('MedPersonal_id')
                    ,time: getGlobalOptions().date +' 00:00'
				};
				var params = {
					Timetable_id: 0
					,direction: directionData
					,person: personData
					,loadMask: true
					,windowId: 'swWorkPlacePolkaRegWindow'
					,callback: Ext.emptyFn
					,onSaveRecord:  function(conf) {
						if (conf && conf.Timetable_id) {
							// После успешного осуществления записи обновлять грид Записи пациента
							if (personData) {
                               this.bj.doSearch({Person_id: personData.Person_id,person_data:personData});
                            }
							/*
							убрал автоматическое открытие печатной формы талона #17462
							var pr_params = new Object();
							pr_params.type = 'EvnPL';
							pr_params.personId = personData.Person_id;
							pr_params.TimetableGraf_id = conf.Timetable_id;
							if ( getGlobalOptions().region ) {
								switch ( getGlobalOptions().region.nick ) {
									case 'ufa':
										getWnd('swEvnPLBlankSettingsWindow').show(pr_params);
									break;

									default:
										printEvnPLBlank(pr_params);
									break;
								}
							}
							else {
								printEvnPLBlank(pr_params);
							}
							*/
						}
					}.createDelegate(this)
					,onHide: Ext.emptyFn
					,needDirection: null
					,fromEmk: false
					,mode: 'nosave'
					,Unscheduled: true
					,date: getGlobalOptions().date
				};
				sw.Promed.Direction.recordPerson(params);
			}.createDelegate(this),
			onHide: function() {
				//
			}.createDelegate(this)
		});
	},
	printFreeTemplate: function() {		
	
		var personData = this.MainViewFrame.getParamsIfHasPersonData();
		
		if(!personData){
			return false;
		}
		
		var params = {
			Person_id: personData.Person_id
		};
		
		getWnd('swPrintTemplateSelectWindow').show(params);
	},
	checkAccessToPersonEncrypHIV: function(){
		var wnd = this;
		//Доступ к шифрованию пациентов
		Ext.Ajax.request({
			params:{
				MedPersonal_id: wnd.userMedStaffFact.MedPersonal_id
			},
			success: function (response) {
				var result = Ext.util.JSON.decode(response.responseText);
				if (result.success) {
					Ext.getCmp('wpprw_action_PersonEncrypHIV').setVisible(isUserGroup('HIVRegistry') && result.flag);
				}
			},
			url:'/?c=LpuStructure&m=hasMedStaffFactInAIDSCenter'
		});

	},
	_printMedCard: function(personCard, personId, personAmbulatCard_id, personAmbulatCard_num){// #137782
		if(! personCard) personCard = 0;
		if(! personId) personId = 0;
		if(! personAmbulatCard_id) personAmbulatCard_id = 0;
		if(! personAmbulatCard_num) personAmbulatCard_num = 0;
		var lpu = getLpuIdForPrint();
		if(getRegionNick().inlist(['kz'])){
			var params = {
				PersonCard_id: personCard,
				Person_id: personId
			};
			if(personAmbulatCard_num){
				params.PersonAmbulatCard_Num = personAmbulatCard_num;
			}
			Ext.Ajax.request({
				callback: function(options, success, response) {
					if(success){
						var response_obj = Ext.util.JSON.decode(response.responseText);
						openNewWindow(response_obj.result);
					}
					else{
						sw.swMsg.alert('Ошибка', 'При получении данных для печати мед. карты произошла ошибка');
					}
				}.createDelegate(this),
				params: params,
				url : '/?c=PersonCard&m=printMedCard'
			});
		}
		else if(getRegionNick() == 'ufa'){
			//printMedCard4Ufa(gridSelected.get('PersonCard_id'));// функцию не трогаю, может вызываться откуда-то ещё
			printBirt({
				'Report_FileName': 'f025u_oborot.rptdesign',
				'Report_Params': '&paramPerson=' + personId + '&paramPersonAmbulatCard=' + personAmbulatCard_id + '&paramPersonCard=' + personCard + '&paramLpu=' + lpu,
				'Report_Format': 'pdf'
			});
			printBirt({
				'Report_FileName': 'f025u.rptdesign',
				'Report_Params': '&paramPerson=' + personId + '&paramPersonAmbulatCard=' + personAmbulatCard_id + '&paramPersonCard=' + personCard + '&paramLpu=' + lpu,
				'Report_Format': 'pdf'
			});
		}
		else{
			printBirt({
				'Report_FileName': 'pan_PersonCard_f025u.rptdesign',
				'Report_Params': '&paramPerson=' + personId + '&paramPersonAmbulatCard=' + personAmbulatCard_id + '&paramPersonCard=' + personCard + '&paramLpu=' + lpu,
				'Report_Format': 'pdf'
			});
		}
	},
	show: function() {
		sw.Promed.swWorkPlacePolkaRegWindow.superclass.show.apply(this, arguments);
		
		this.MainViewFrame.removeAll({clearAll:true});
		this.PersonAmbulatCard.removeAll();
		//this.hist = false;
		this.currentRecord = null;
		var wnd = this;
        //log(wnd.LeftPanel.actions.action_Moderation.items[0].menu.items.items[0]);
        var InternetModeration = wnd.LeftPanel.actions.action_Moderation.items[0].menu.items.items[0];
		//wnd.findById('InternetModeration').show();
        InternetModeration.show();
		if(getGlobalOptions().region.nick == 'ufa')
		{
			//wnd.findById('InternetModeration').hide();
            InternetModeration.hide();
			Ext.Ajax.request({
				params:{
					Lpu_id: getGlobalOptions().lpu_id
				},
				success: function (response) {
					var result = Ext.util.JSON.decode(response.responseText);
					if ((result[0])&&(result[0].Lpu_IsAllowInternetModeration==2)){
						//wnd.findById('InternetModeration').show();
						InternetModeration.show();
					}
				},
				url:'/?c=LpuPassport&m=getIsAllowInternetModeration'
			});
		}

		this.getLpuBuildingByMedServiceId();
		
		// Реализация уведомлений вызова врача на дом
		var hv_last = 0,
			hv_send = false;
		
		setInterval(function(){
			Ext.Ajax.request({
				params:{
					Lpu_id: getGlobalOptions().lpu_id,
					start: 0,
					type: 'regpool',
					HomeVisitStatus_id: 1,
					begDate: getGlobalOptions().date,
					endDate: getGlobalOptions().date
				},
				success: function (response) {
					var result = Ext.util.JSON.decode(response.responseText),
						item = result.data[0];
					
					if (result.totalCount > 0) {
						//for(var i=0; i < result.data.length; i++) {}
						if (hv_send == true && hv_last != item.HomeVisit_id) {
							showSysMsg(
								item.Person_Firname+' '+item.Person_Secname+' '+item.Person_Surname+'<br/><br/>'+
								'Тел: <b>'+item.HomeVisit_Phone+'<br/>'+
								'Жалобы: '+item.HomeVisit_Symptoms+'<br/> <a href="javascript:getWnd(\'swHomeVisitListWindow\').show({\'type\':\'regpol\'});">принять</a>',
								'Новый вызов врача на дом',
								'info',
								{closable: true, delay: 5000, bodyStyle: 'text-align:left; margin-left:7px; padding: 0px 0px 20px 20px;background:transparent'}
							);
						}
						hv_last = item.HomeVisit_id;
					}
					hv_send = true;
				},
				url:'/?c=HomeVisit&m=getHomeVisitList'
			});
		}, 300000);// было 10000 - #111330 уменьшение нагрузки на сервер

		//this.userMedStaffFact = sw.Promed.MedStaffFactByUser.last; // убрал, поскольку this.userMedStaffFact заполняется в базовой форме



        if ( !this.MainViewFrame.getAction('print') ) {
            this.MainViewFrame.addActions({
                name: 'print',
                text:'Печать',
                tooltip: 'Печать',
                iconCls : 'x-btn-text',
                icon: 'img/icons/print16.png',
                handler: function() {},
				menu: this.MainPrintMenu
            });
        }

		if ( !this.MainViewFrame.getAction('actions') ) {
			this.MainViewFrame.addActions({
				name: 'actions',
				text:'Действия',
				tooltip: 'Действия',
				iconCls : 'x-btn-text',
				icon: 'img/icons/actions16.png',
				handler: function() {},
				menu: this.MainActionsMenu
			});
		}
		
		if ( !this.MainViewFrame.getAction('action_print_lpuunit') ) {
			this.MainViewFrame.addActions({
				name: 'action_print_lpuunit',
				//iconCls: 'print16',
				tooltip: 'Список записанных по всем врачам',
				text: 'Список записанных по всем врачам',
				handler: function() {getWnd('swPrintLpuUnitScheduleWindow').show();}
			});
		}

		// дублирующий фунционал базового журнала, сказали нужен для удобства. %)
		if ( !this.MainViewFrame.getAction('action_adddirection_regpolka') ) {
			this.MainViewFrame.addActions({
				name: 'action_adddirection_regpolka',
				iconCls: 'add16',
				text: 'Записать',
				handler: function() {},
				menu: new Ext.menu.Menu({
					items: [{
						text:'Записать',
						handler:function() {
							wnd.bj.openEvnDirectionEditWindow('add', 1);
						}
					}, {
						text:'Записать с электронным направлением',
						handler:function() {
							wnd.bj.openEvnDirectionEditWindow('add', 2);
						}
					}]
				})
			}, 0);
		}

		if ( !this.MainViewFrame.getAction('action_add_incoming_regpolka') ) {
			this.MainViewFrame.addActions({
				name: 'action_add_incoming_regpolka',
				iconCls: 'add16',
				text: 'Внешнее направление',
				handler: function() {
					wnd.bj.openEvnDirectionEditWindow('add', 3);
				}
			}, 1);
		}

		if ( !this.bj.mainGrid.getAction('show_history') ) {
			this.bj.mainGrid.addActions({
					name: 'show_history',
					iconCls: 'journal16',
					hidden:!isAdmin,
					disabled:true,
					tooltip: 'Показать/Скрыть историю',
					text: 'Показать историю',
					handler: function() {

						var selected_record = wnd.MainViewFrame.getGrid().getSelectionModel().getSelected();
						if (!selected_record){
							return false;
						}

						var person_data = wnd.MainViewFrame.getParamsIfHasPersonData();

						if(Ext.isEmpty(wnd.bj.loadAddFields)){
						//if(!wnd.bj.hist){
							wnd.bj.hist = true;
							wnd.bj.loadAddFields = 1;
							wnd.bj.mainGrid.setActionText('show_history', 'Скрыть историю');
							wnd.bj.mainGrid.setColumnHidden('pmUser_Name',false);
							wnd.bj.mainGrid.setColumnHidden('EvnDirection_insDT',false);
							wnd.bj.doSearch({Person_id: selected_record.get('Person_id'), person_data:person_data});
						}else{
							wnd.bj.hist = false;
							wnd.bj.loadAddFields = null;
							wnd.bj.mainGrid.setActionText('show_history', 'Показать историю');
							wnd.bj.mainGrid.setColumnHidden('pmUser_Name',true);
							wnd.bj.mainGrid.setColumnHidden('EvnDirection_insDT',true);
							wnd.bj.doSearch({Person_id: selected_record.get('Person_id'), person_data:person_data});
						}
					}
			});
		}

		//Ufa, gaf #116422, для ГАУЗ РВФД
		if (getRegionNick() == 'ufa' && (isSuperAdmin() || getGlobalOptions().lpu_id == 81)) {
			if ( !this.bj.mainGrid.getAction('route_list_rvfd') ) {
				this.bj.mainGrid.addActions({
					name: 'route_list_rvfd',					
					hidden:false,
					disabled:true,					
					text: 'Печать маршрутного листа(РВФД)',
					iconCls : 'x-btn-text', 
					icon: 'img/icons/print16.png',                                        
					handler: function() {
						var selected_record = wnd.MainViewFrame.getGrid().getSelectionModel().getSelected();

						if ( typeof selected_record != 'object') {
							return false;
						}

						var person_data = wnd.MainViewFrame.getParamsIfHasPersonData();

						printRVFDRouteList(person_data);
					}
				}, 14);
			}               
        }

		log('userMedStaffFact',this.userMedStaffFact);
		wnd.ElectronicQueuePanel.initElectronicQueue();

		this.checkAttachAllow();
		this.doReset();
		//sw.Applets.BarcodeScaner.startBarcodeScaner({callback: this.getDataFromBarcode.createDelegate(this)});
		//sw.Applets.bdz.startBdzReader({callback: this.getDataFromBdz.createDelegate(this)});

		this.FilterPanel.fieldSet.expand();
		this.FilterPanel.getForm().findField(getRegionNick() == 'kz' ? 'Person_Inn' : 'Person_Surname').focus(true, 200);

		this.checkAccessToPersonEncrypHIV();
	},

	checkAttachAllow: function () {
		this.findById('wpprw_action_AutoPersonCard_id').setVisible(isUserGroup('SuperAdmin') ||
			(Ext.isEmpty(getGlobalOptions().check_attach_allow) || getGlobalOptions().check_attach_allow != 1) ||
			(getGlobalOptions().check_attach_allow == 1 && isUserGroup('CardEditUser') && (isUserGroup('LpuAdmin') || isUserGroup('RegAdmin')))
		);
	},
	doSearch: function(mode,callback){
		
		/*
		var w = Ext.WindowMgr.getActive();
		// Не выполняем если открыто модальное окно. Иначе при обновлении списка,
		// выделение с текущего элемента снимается и устанавливается на первом элементе
		// в списке. В свою очередь все рабочие места получают не верные данные из
		// выделенного объекта, вместо ранее выделенного пользователем.
		// @todo Проверка неудачная. Необходимо найти другое решение.
		
		// Текущее активное окно является модальным?
		if ( w.modal ) {
			return;
		}
		*/

		if ( this.FilterPanel.isEmpty() ) {
			/*sw.swMsg.alert('Ошибка', 'Не заполнено ни одно поле', function() {
			});
			*/
			this.doReset(); // ничего не задали - ничего не нашли
			return false;
		}

		var formParams = this.FilterPanel.getForm().getValues();

		if (
			Ext.isEmpty(formParams.Person_Surname) && Ext.isEmpty(formParams.Person_Firname) && Ext.isEmpty(formParams.Person_Secname)
			&& (!Ext.isEmpty(formParams.Address_Street) || !Ext.isEmpty(formParams.Address_House))
		) {
			sw.swMsg.alert('Ошибка', 'Для поиска по адресу требуется заполнить хотя бы одно поле из ФИО');
			return false;
		}

		var params = Ext.apply(formParams, this.searchParams || {});
		var btn = this.getPeriodToggle(mode);
		if (btn) {
			if (mode != 'range') {
				if (this.mode == mode) {
					btn.toggle(true);
					if (mode != 'day') // чтобы при повторном открытии тоже происходила загрузка списка записанных на этот день
						return false;
				} else {
					this.mode = mode;
				}
			}
			else {
				btn.toggle(true);
				this.mode = mode;
			}
		}

		if (!Ext.isEmpty(params.PartMatchSearch) && !Ext.isEmpty(params.PersonCard_Code)) {
			if (params.PersonCard_Code.length < 2) {
				sw.swMsg.alert('Ошибка', 'Поиск по частичному совпадению номера амбулаторной карты не возможен менее чем по 2-м символам');
				return false;
			}
		}
		
		params.begDate = Ext.util.Format.date(this.dateMenu.getValue1(), 'd.m.Y');
		params.endDate = Ext.util.Format.date(this.dateMenu.getValue2(), 'd.m.Y');
		params.limit = 50;
		params.start = 0;
		params.dontShowUnknowns = 1;// #158923 не показывать неизвестных
		this.MainViewFrame.removeAll({clearAll:true});
		this.MainViewFrame.loadData({globalFilters: params, callback:callback});
	},
	/**
	 * Запись человека
	 */
	openDirectionMasterWindow: function(evnqueue_id) {
		var win = this;
		var grid = this.MainViewFrame.getGrid();
		var record = grid.getSelectionModel().getSelected();
		var params = new Object({
			userMedStaffFact: this.userMedStaffFact,
			type: 'LpuReg',
			onDirection: function(data) {
				if (record && record.get('Person_id') ) {
					win.bj.doSearch({Person_id: record.get('Person_id')});
				}
			}
		});
		var personData = new Object();

		if ( record && record.get('Person_id') ) {
			if (record.get('Person_IsDead') == "true") {
				params.isDead = true;
			}
			personData.Person_Firname = record.get('Person_Firname');
			personData.Person_id = record.get('Person_id');
			personData.PersonEvn_id = record.get('PersonEvn_id');
			personData.Server_id = record.get('Server_id');
			personData.Person_IsDead = record.get('Person_IsDead');
			personData.Person_Secname = record.get('Person_Secname');
			personData.Person_Surname = record.get('Person_Surname');
			personData.AttachLpu_Name = record.get('AttachLpu_Name');
			personData.Person_Birthday = record.get('Person_Birthday');
			params.personData = personData;
		}

		params.directionData = {
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
			,MedStaffFact_id: null
			,MedPersonal_id: this.userMedStaffFact.MedPersonal_id
			,MedPersonal_did: null
			,type: 'LpuReg'
		};

		getWnd('swDirectionMasterWindow').show(params);
	},
	addPerson: function() {
		var win = this;
		var filterForm = this.FilterPanel.getForm();
		var grid = this.MainViewFrame.getGrid();

		getWnd('swPersonEditWindow').show({
			action: 'add',
			fields: {
				Person_SurName: filterForm.findField('Person_Surname').getValue(),
				Person_FirName: filterForm.findField('Person_Firname').getValue(),
				Person_SecName: filterForm.findField('Person_Secname').getValue(),
				Person_BirthDay: filterForm.findField('Person_Birthday').getValue()
			},
			callback: function (saved_person){
				// обновить грид и выбрать добавленного человека
				var globalFilters = {
					Person_id: saved_person.Person_id,
					limit: 50,
					start: 0
				};

				win.MainViewFrame.removeAll({clearAll:true});
				win.MainViewFrame.loadData({
					globalFilters: globalFilters,
					callback: function() {
						grid.getStore().each(function(record) {
							if (record.get('Person_id') == saved_person.Person_id) {
								var index = grid.getStore().indexOf(record);
								grid.getView().focusRow(index);
								grid.getSelectionModel().selectRow(index);
								return;
							}
						});
					}
				});
			}
		});
	},
	addPersonToUnion: function() {
		var params = this.MainViewFrame.getParamsIfHasPersonData();
		if (params && params.Person_id) {
			AddPersonToUnion(
				{
					data: {
						Person_id: params['Person_id'],
						PersonSurName_SurName: params['Person_Surname'],
						PersonFirName_FirName: params['Person_Firname'],
						PersonSecName_SecName: params['Person_Secname'],
						PersonBirthDay_BirthDay: params['Person_Birthday'],
						Server_id: params['Server_id']
					}
				},
				params.onHide
			);
		}
	},

	DeletePersonAmbulatCard:function(){
		var mGrid = this.MainViewFrame.getGrid();
		var grid = this.PersonAmbulatCard.getGrid();
		
		if(!grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('PersonAmbulatCard_id')){
			return false;
		}
		var record = grid.getSelectionModel().getSelected();
		var personData = this.MainViewFrame.getParamsIfHasPersonData();
		if(personData && personData.Person_IsDead=="true"){
			sw.swMsg.show({
				buttons: sw.swMsg.OK,
				//title: 'Предупреждение',
				msg: 'Невозможно создать новую карту. Причина: смерть пациента',
				icon: sw.swMsg.WARNING
			});
			return false;
		}
		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( buttonId == 'yes' ) {
					Ext.Ajax.request({
						params:{
							PersonAmbulatCard_id:record.get('PersonAmbulatCard_id')
						},
						success: function (response) {
							 grid.getStore().reload();
						},
						failure: function(response, options) {
							sw.swMsg.alert('Ошибка', 'При удалении оригинала АК.');
						},
						url:'/?c=PersonAmbulatCard&m=deletePersonAmbulatCard'
					});
				}
				else {
					return false;
				}
			}.createDelegate(this),
			icon: Ext.MessageBox.QUESTION,
			msg: 'Удалить выбранную амбулаторную карту?',
			title: 'Вопрос'
		});
		
	},
	OpenPersonAmbulatCard:function(action){
	    var mGrid = this.MainViewFrame.getGrid();
	    var grid = this.PersonAmbulatCard.getGrid();
		if ( !mGrid.getSelectionModel().getSelected() || !mGrid.getSelectionModel().getSelected().get('Person_id') ) {
			return false;
		}

		var mSelected_record = mGrid.getSelectionModel().getSelected();
		var params = {};
		if(action=='add'){
			var personData = this.MainViewFrame.getParamsIfHasPersonData();
			if(personData && personData.Person_IsDead=="true"){
				sw.swMsg.show({
					buttons: sw.swMsg.OK,
					//title: 'Предупреждение',
					msg: 'Невозможно создать новую карту. Причина: смерть пациента',
					icon: sw.swMsg.WARNING
				});
				return false;
			}
		    params ={
			action:action,
			Person_id: mSelected_record.get('Person_id'),
			Server_id: mSelected_record.get('Server_id'),
			PersonFIO: mSelected_record.get('Person_Surname')+' '+mSelected_record.get('Person_Firname').substr(0,1)+' '+mSelected_record.get('Person_Secname').substr(0,1)
		    }
		}else{
		   
		    if(!grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('PersonAmbulatCard_id')){
			return false;
		    }
			var record = grid.getSelectionModel().getSelected();
		    params ={
			action:action,
			PersonAmbulatCard_id:record.get('PersonAmbulatCard_id')
		    }
		}
		params.callback= function(){
		    grid.getStore().reload();
		}
		getWnd('swPersonAmbulatCardEditWindow').show(params);
	},	
	openNewslatterAcceptEditWindow: function(NewslatterAccept_id, Person_id) {
	
		if (!isUserGroup('Newslatter')) {
			return false;
		}
		
		var win = this;
	    var grid = this.MainViewFrame.getGrid();
		var params = {};
		params.NewslatterAccept_id = NewslatterAccept_id;
		params.Person_id = Person_id;
		params.action = Ext.isEmpty(NewslatterAccept_id) ? 'add' : 'edit';
		params.callback = function(options, success, response) {
		    grid.getStore().reload();
			if (success == true && response) {
				win.askPrintNewslatterAccept(response);
			}
		}

        getWnd('swNewslatterAcceptEditForm').show(params);
    },
    askPrintNewslatterAccept: function(params) {
	
		if (!params || !params.NewslatterAccept_id) {
			return false;
		}
		
		var win = this;
		
		if (Ext.isEmpty(params.NewslatterAccept_endDate)) {	
			
			sw.swMsg.show({
				title: 'Вопрос',
				msg: 'Распечатать документ?',
				icon: Ext.MessageBox.QUESTION,
				buttons: Ext.Msg.YESNO,
				fn: function ( buttonId ) {
					if ( buttonId == 'yes' ) {
						win.printNewslatterAccept('printAccept', params.NewslatterAccept_id);
					}
				}
			});	
		} else {
			
			sw.swMsg.show({
				title: 'Вопрос',
				msg: 'Распечатать документ?',
				icon: Ext.MessageBox.QUESTION,
				buttons: {
					yes: 'Печать Согласия',
					no: 'Печать Отказа',
					cancel: 'Отмена'
				},
				fn: function( buttonId ) {				
					if ( buttonId == 'yes') {
						win.printNewslatterAccept('printAccept', params.NewslatterAccept_id);
					} else if ( buttonId == 'no') {
						win.printNewslatterAccept('printDenial', params.NewslatterAccept_id);
					}
				}
			});
		}		
    },
    printNewslatterAccept: function(method, NewslatterAccept_id) { 
		
		if (!method || !NewslatterAccept_id) {
			return false;
		}
	
		window.open('/?c=NewslatterAccept&m=' + method + '&NewslatterAccept_id=' + NewslatterAccept_id, '_blank');
	},
	printPregnancyBlank: function() { 
		printBirt({
			'Report_FileName': 'han_ParturientCard_f111_u.rptdesign',
			'Report_Params': '&paramPersonRegister_id=0',
			'Report_Format': 'pdf'
		});
	},
	initComponent: function() {

		var curWnd = this;

		this.gridPanelAutoLoad = false;
		this.showToolbar = false;
		this.MainPrintMenu = new Ext.menu.Menu({
			items: [
				{
					disabled: false,
					handler: function() {
						var grid = this.MainViewFrame.getGrid();
						var gridTTG = this.bj.mainGrid.getGrid();
						var params = new Object();

						params.type = 'EvnPL';

						params.personId = grid.getSelectionModel().getSelected().get('Person_id');

						var TTGrecord = gridTTG.getSelectionModel().getSelected();

						if ( typeof TTGrecord == 'object' && !Ext.isEmpty(TTGrecord.get('TimetableGraf_id')) ) {
							params.TimetableGraf_id = gridTTG.getSelectionModel().getSelected().get('TimetableGraf_id');
						}

						if ( getGlobalOptions().region ) {
							switch ( getGlobalOptions().region.nick ) {
								case 'ufa':
									if ( typeof TTGrecord == 'object' && !Ext.isEmpty(TTGrecord.get('TimetableGraf_id')) ) {
										params.MedPersonal_id = TTGrecord.get('MedPersonal_id');
										params.LpuSectionProfile_id = TTGrecord.get('LpuSectionProfile_id');
									}
									getWnd('swEvnPLBlankSettingsWindow').show(params);
									break;

								default:
									printEvnPLBlank(params);
									break;
							}
						}
						else {
							printEvnPLBlank(params);
						}
					}.createDelegate(this),
					iconCls: 'print16',
					name: 'print_evnpl_blank',
					text: 'Печать бланка ТАП',
					tooltip: 'Печать бланка талона амбулаторного пациента'
				},
				{
					disabled: false,
					hidden: getRegionNick() == 'kareliya',
					handler: function() {
						var grid = this.MainViewFrame.getGrid();
						var selected_record = grid.getSelectionModel().getSelected();

						if (getRegionNick() == 'ufa') {
							var gridTTG = this.bj.mainGrid.getGrid();
							var params = new Object();
							params.type = 'EvnPL';
							params.before2015 = true;
							params.personId = grid.getSelectionModel().getSelected().get('Person_id');
							var TTGrecord = gridTTG.getSelectionModel().getSelected();
							if ( typeof TTGrecord == 'object' && !Ext.isEmpty(TTGrecord.get('TimetableGraf_id')) ) {
								params.TimetableGraf_id = gridTTG.getSelectionModel().getSelected().get('TimetableGraf_id');
							}
							if ( typeof TTGrecord == 'object' && !Ext.isEmpty(TTGrecord.get('TimetableGraf_id')) ) {
								params.MedPersonal_id = TTGrecord.get('MedPersonal_id');
								params.LpuSectionProfile_id = TTGrecord.get('LpuSectionProfile_id');
							}
							getWnd('swEvnPLBlankSettingsWindow').show(params);
						} else {
							var url = "";
							url = '/?c=EvnPL&m=printEvnPLBlank';
							if (selected_record && selected_record.get('Person_id')) {
								url = url + '&Person_id=' + selected_record.get('Person_id');
							}
							window.open(url, '_blank');
						}
					}.createDelegate(this),
					iconCls: 'print16',
					name: 'print_evnpl_blank_old',
					text: 'Печать бланка ТАП (до 2015г)',
					tooltip: 'Печать бланка талона амбулаторного пациента (до 2015г)'
				},
				{
					disabled: false,
					hidden: getRegionNick() != 'ekb',
					handler: function(btn) {
						var grid = curWnd.MainViewFrame.getGrid();
						var Person_id = grid.getSelectionModel().getSelected().get('Person_id');
						printBirt({
							'Report_FileName': 'tap_66_blank.rptdesign',
							'Report_Params': '&paramPerson=' + Person_id,
							'Report_Format': 'pdf'
						});
					},
					iconCls: 'print16',
					name: 'print_tap_66_blank',
					text: 'ТАП Свердловской области',
					tooltip: 'Печать ТАП Свердловской области'
				},
				{
					disabled: false,
					handler: function() {
						var mGrid = this.MainViewFrame.getGrid(),
							gridSelected = mGrid.getSelectionModel().getSelected();// главный грид и выбранный ряд

						var personAmbulatCard_id = 0;
						if(!Ext.isEmpty(gridSelected.get('PersonAmbulatCard_id'))){// создать амбулаторную карту можно в этом же окне, но главный грид не обновляется
							personAmbulatCard_id = parseInt(gridSelected.get('PersonAmbulatCard_id'));
						}

						var personCard = 0,
							personId = 0,
							personAmbulatCard_num = 0;
						if(!Ext.isEmpty(gridSelected.get('PersonCard_id')) && gridSelected.get('AttachLpu_id') == getGlobalOptions().lpu_id) {
							personCard = parseInt(gridSelected.get('PersonCard_id'));
						}

						if(!Ext.isEmpty(gridSelected.get('Person_id'))){
							personId = parseInt(gridSelected.get('Person_id'));
						}

						if(!Ext.isEmpty(gridSelected.get('PersonAmbulatCard_Num'))){
							personAmbulatCard_num = parseInt(gridSelected.get('PersonAmbulatCard_Num'));
						}

						this._printMedCard(personCard, personId, personAmbulatCard_id, personAmbulatCard_num);

						//this._printMedCard(PersonCard, PersonId, PersonAmbulatCard_id);

						return true;
					}.createDelegate(this),
					iconCls: 'print16',
					name: 'print_personcard',
					text: 'Печать амбулаторной карты',
					tooltip: 'Печать амбулатороной карты пациента'
				},
				{
					disabled: false,
					iconCls: 'print16',
					name: 'print_personstomcard',
					text: 'Печать стом. карты',
					tooltip: 'Печать стоматологической карты пациента',
					menu: sw.Promed.StomHelper.Report.getPrintMenu(function(callback){
						var grid = curWnd.MainViewFrame.getGrid(),
							Person_id = grid.getSelectionModel().getSelected().get('Person_id'),
							me = this;
						if (!me.Person_id || me.Person_id != Person_id) {
							me.Person_id = Person_id;
							me.lastEvnPLStomData = null;
						}
						if (!me.lastEvnPLStomData && Person_id) {
							sw.Promed.StomHelper.loadLastEvnPLStomData(Person_id, function(data) {
								me.lastEvnPLStomData = data;
								callback(Person_id, data);
							});
						} else {
							callback(Person_id, me.lastEvnPLStomData);
						}
					}, {
						hideCostPrint: true
					})
				},
				{
					disabled: false,
					hidden: !(getRegionNick()=='pskov'),
					handler: function(btn) {
						var grid = curWnd.MainViewFrame.getGrid();
						var Person_id = grid.getSelectionModel().getSelected().get('Person_id');
						printBirt({
							'Report_FileName': 'Person_soglasie.rptdesign',
							'Report_Params': '&paramPerson=' + Person_id,
							'Report_Format': 'pdf'
						});
					},
					iconCls: 'print16',
					name: 'print_personsogl',
					text: 'Согласие на мед. вмешательство (A4)',
					tooltip: 'Печать согласия на мед. вмешательство в формате A4'
				},
				{
					disabled: false,
					hidden: !(getRegionNick()=='pskov'),
					handler: function(btn) {
						var grid = curWnd.MainViewFrame.getGrid(),
							PersonCard_id = grid.getSelectionModel().getSelected().get('PersonCard_id');

						if (Ext.isEmpty(PersonCard_id)) {
							sw.swMsg.alert('Ошибка', 'Невозможно напечатать документ. Проверьте прикрепление пациента.');
							return false;
						}

						printBirt({
							'Report_FileName': 'PersonCardMedicalIntervent.rptdesign',
							'Report_Params': '&paramPersonCard=' + PersonCard_id + '&paramMedPersonal='+curWnd.userMedStaffFact.MedPersonal_id,
							'Report_Format': 'pdf'
						});
					},
					iconCls: 'print16',
					name: 'print_personotkaz',
					text: 'Отказ от мед. вмешательства',
					tooltip: 'Печать отказа от мед. вмешательства'
				},
				{
					disabled: false,
					hidden: !(getRegionNick()=='pskov'),
					handler: function(btn) {
						var grid = curWnd.MainViewFrame.getGrid();
						var Person_id = grid.getSelectionModel().getSelected().get('Person_id');
						printBirt({
							'Report_FileName': 'PersonInfoSoglasie_Vac.rptdesign',
							'Report_Params': '&paramPerson=' + Person_id,
							'Report_Format': 'pdf'
						});
					},
					iconCls: 'print16',
					name: 'print_personsoglvac',
					text: 'Согласие на вакцинацию',
					tooltip: 'Печать согласия на на вакцинацию'
				},
				/*{
					disabled: false,
					hidden: !(getRegionNick()=='pskov'),
					handler: function(btn) {
						var grid = curWnd.MainViewFrame.getGrid();
						var Person_id = grid.getSelectionModel().getSelected().get('Person_id');
						var Lpu_id = grid.getSelectionModel().getSelected().get('AttachLpu_id');
						printBirt({
							'Report_FileName': 'Person_Consent.rptdesign',
							'Report_Params': '&paramPerson=' + Person_id +'&paramLpu='+Lpu_id,
							'Report_Format': 'pdf'
						});
					},
					iconCls: 'print16',
					name: 'print_personsoglvac',
					text: 'Согласие на обработку перс. данных',
					tooltip: 'Печать согласия на обработку перс. данных'
				},*/
				{
					disabled: false,
					handler: function(btn) {
						var grid = curWnd.MainViewFrame.getGrid();
						var record = grid.getSelectionModel().getSelected();
						var index = grid.getStore().indexOf(record);
						var Person_id = grid.getSelectionModel().getSelected().get('Person_id');
						Ext.Ajax.request({
							url: '/?c=Person&m=savePersonLpuInfo',
							success: function(response){
								var response_obj = Ext.util.JSON.decode(response.responseText);
								if (response_obj && response_obj.Error_Msg ) {
									sw.swMsg.alert('Ошибка', 'Ошибка при сохранении согласие на обработку перс. данных');
									return false;
								} else if ( response_obj && !Ext.isEmpty(response_obj.PersonLpuInfo_id) ) {
									curWnd.doSearch(false,function(){
										curWnd.MainViewFrame.getGrid().getSelectionModel().selectRow(index);
									});
									if (getRegionNick() == 'kz') {
										var lan = (getAppearanceOptions().language == 'ru' ? 1 : 2);
										printBirt({
											'Report_FileName': 'PersonSoglasie_PersData.rptdesign',
											'Report_Params': '&paramPersonLpuInfo_id=' + response_obj.PersonLpuInfo_id + '&paramLang=' + lan,
											'Report_Format': 'pdf'
										});
									} else {
										printBirt({
											'Report_FileName': 'PersonSoglasie_PersData.rptdesign',
											'Report_Params': '&paramPersonLpuInfo_id=' + response_obj.PersonLpuInfo_id,
											'Report_Format': 'pdf'
										});
									}
								}
							}.createDelegate(this),
							params: {
								Person_id: Person_id,
								PersonLpuInfo_IsAgree: 2
							}
						});

					},
					iconCls: 'print16',
					name: 'print_personsogl_persdata',
					text: 'Согласие на обработку перс. данных (A4)',
					tooltip: 'Печать согласия на обработку персональных данных данных в формате A4'
				},
				{
					disabled: false,
					handler: function(btn) {
						var grid = curWnd.MainViewFrame.getGrid();
						var record = grid.getSelectionModel().getSelected();
						var index = grid.getStore().indexOf(record);
						var Person_id = grid.getSelectionModel().getSelected().get('Person_id');
						Ext.Ajax.request({
							url: '/?c=Person&m=savePersonLpuInfo',
							success: function(response){
								var response_obj = Ext.util.JSON.decode(response.responseText);
								if (response_obj && response_obj.Error_Msg ) {
									sw.swMsg.alert('Ошибка', 'Ошибка при сохранении Отзыва согласия на обработку перс. данных');
									return false;
								} else if ( response_obj && !Ext.isEmpty(response_obj.PersonLpuInfo_id) ) {
									curWnd.doSearch(false,function(){
										curWnd.MainViewFrame.getGrid().getSelectionModel().selectRow(index);
									});
									printBirt({
										'Report_FileName': 'PersonOtkaz_PersData.rptdesign',
										'Report_Params': '&paramPersonLpuInfo_id=' + response_obj.PersonLpuInfo_id,
										'Report_Format': 'pdf'
									});
								}
							}.createDelegate(this),
							params: {
								Person_id: Person_id,
								PersonLpuInfo_IsAgree: 1
							}
						});

					},
					iconCls: 'print16',
					name: 'print_personotzyvsogl_persdata',
					text: 'Отзыв согласия на обработку перс. данных',
					tooltip: 'Печать отзыва согласия на обработку перс. данных'
				}, {
					hidden: getRegionNick().inlist(['kz', 'ekb']),
					iconCls: 'print16',
					name: 'print_personsogl_persdata_a5',
					text: 'Согласие на обработку ПД (A5)',
					tooltip: 'Печать согласия на обработку персональных данных в формате A5',
					handler: function (btn) {
						if (getRegionNick().inlist(['kz', 'ekb']))
						{
							return false;
						}

						var grid = curWnd.MainViewFrame.getGrid();
						var record = grid.getSelectionModel().getSelected();
						var index = grid.getStore().indexOf(record);
						var Person_id = grid.getSelectionModel().getSelected().get('Person_id');
						Ext.Ajax.request({
							url: '/?c=Person&m=savePersonLpuInfo',
							success: function(response){

								var response_obj = Ext.util.JSON.decode(response.responseText);
								if (response_obj && response_obj.Error_Msg )
								{
									sw.swMsg.alert('Ошибка', 'Ошибка при сохранении согласие на обработку перс. данных');
									return false;

								} else if ( response_obj && !Ext.isEmpty(response_obj.PersonLpuInfo_id) )
								{
									curWnd.doSearch(false,function(){
										curWnd.MainViewFrame.getGrid().getSelectionModel().selectRow(index);
									});

									printBirt({
										'Report_FileName': 'PersonSoglasie_PersData_A5oborot.rptdesign',
										'Report_Params': '&paramPersonLpuInfo_id=' + response_obj.PersonLpuInfo_id,
										'Report_Format': 'pdf'
									});

									printBirt({
										'Report_FileName': 'PersonSoglasie_PersData_A5.rptdesign',
										'Report_Params': '&paramPersonLpuInfo_id=' + response_obj.PersonLpuInfo_id,
										'Report_Format': 'pdf'
									});
								}

								return true;

							}.createDelegate(this),
							params: {
								Person_id: Person_id,
								PersonLpuInfo_IsAgree: 2
							}
						});

						return true;

					}

				}, {
					hidden: getRegionNick().inlist(['kz', 'ekb']),
					iconCls: 'print16',
					name: 'print_personscard_infoconsent_a5',
					text: 'Согласие на вмешательство (А5)',
					tooltip: 'Печать согласия на вмешательство в формате A5',
					handler: function (btn) {
						if (getRegionNick().inlist(['kz', 'ekb']))
						{
							return false;
						}

						var grid = curWnd.MainViewFrame.getGrid(),
							PersonCard_id = grid.getSelectionModel().getSelected().get('PersonCard_id'),
							MedPersonal_id = curWnd.userMedStaffFact.MedPersonal_id;


						if (Ext.isEmpty(PersonCard_id)) {
							sw.swMsg.alert('Ошибка', 'Невозможно напечатать документ. Проверьте прикрепление пациента.');
							return false;
						}


						printBirt({
							'Report_FileName': 'PersonCardInfoConsent_A5oborot.rptdesign',
							'Report_Params': '&paramPersonCard=' + PersonCard_id + '&paramMedPersonal=' + MedPersonal_id,
							'Report_Format': 'pdf'
						});

						printBirt({
							'Report_FileName': 'PersonCardInfoConsent_A5.rptdesign',
							'Report_Params': '&paramPersonCard=' + PersonCard_id + '&paramMedPersonal=' + MedPersonal_id,
							'Report_Format': 'pdf'
						});

						return true;

					}

				}, {
					disabled: false,
					hidden: ! getRegionNick().inlist(['perm']),
					handler: function(btn) {
						// открываем форму печати справки
						var params = {};

						var grid = curWnd.MainViewFrame.getGrid();
						params.Person_id = grid.getSelectionModel().getSelected().get('Person_id');
						params.type = '';

						getWnd('swCostPrintWindow').show(params);
					},
					iconCls: 'print16',
					name: 'print_cost',
					text: 'Справка о стоимости лечения',
					tooltip: 'Справка о стоимости лечения'
				},
				{
					disabled: false,
					hidden: (getRegionNick().inlist(['kz'])),
					handler: function() {
						curWnd.printPregnancyBlank();
					},
					iconCls: 'print16',
					name: 'print_pregnancy',
					text: 'Бланк Индивидуальной карты беременной',
					tooltip: 'Бланк Индивидуальной карты беременной'
				},
				{
					disabled: false,
					handler: function() {
						curWnd.printFreeTemplate();
					},
					iconCls: 'print16',
					name: 'print_freedoc',
					text: 'Печать шаблона документа',
					tooltip: 'Печать шаблона документа'
				},
				{
					disabled: false,
					handler: function() {
						getWnd('swHomeVisitBookPrintParamsWindow').show({ARMType: 'reg'});
					},
					iconCls: 'print16',
					name: 'print_book',
					text: langs('Печать книги записи вызовов на дом'),
					tooltip: langs('Печать книги записи вызовов на дом')
				}
			]
		});
		this.MainActionsMenu = new Ext.menu.Menu({
			items: [
				{
					name:'add_person',
					text:'Добавить человека',
					iconCls : 'add16',
					tooltip: 'Добавить нового человека',
					handler: function() {
						this.addPerson();
					}.createDelegate(this)
				},
				{
					name:'record_un_scheduled',
					text:'Дополнительный прием',
					iconCls : 'copy16',
					tooltip: 'Незапланированная запись к выбранному врачу',
					handler: function() {
						this.recordUnScheduled();
					}.createDelegate(this)
				},
				{
					name:'add_person_to_union',
					text:'Это двойник (Alt+F6)',
					iconCls : 'x-btn-text',
					icon: 'img/icons/actions16.png',
					tooltip: 'Открыть форму «Объединение людей»',
					handler: function() {
						this.addPersonToUnion();
					}.createDelegate(this)
				},
				{
					name:'show_person_edit_window',
					text: BTN_PERSEDIT+' (F10)',
					iconCls: 'edit16',
					tooltip: BTN_PERSEDIT_TIP,
					handler: function() {
						var params = this.MainViewFrame.getParamsIfHasPersonData();
						var grid = this.MainViewFrame.getGrid();
						if (params && params.Person_id) {
							params.PersonEvn_id = null;
							params.onClose = function(){
								grid.getStore().reload();
							};
							ShowWindow('swPersonEditWindow', params);
						}
					}.createDelegate(this)
				},
				{
					name:'show_person_card_history',
					text: BTN_PERSCARD+' (F6)',
					iconCls: 'pers-card16',
					tooltip: BTN_PERSCARD_TIP,
					handler: function() {
						var params = this.MainViewFrame.getParamsIfHasPersonData();
						var grid = this.MainViewFrame.getGrid();
						if (params && params.Person_id) {
							params.onHide = function(){
								grid.getStore().reload();
							};
							ShowWindow('swPersonCardHistoryWindow', params);
						}
					}.createDelegate(this)
				},
				{
					name:'show_person_card_attach_list',
					text: 'Заявления о выборе МО',
					iconCls: 'pers-card16',
					tooltip: 'Заявления о выборе МО',
					hidden: (getRegionNick()!='kz'),
					handler: function() {
						var params = this.MainViewFrame.getParamsIfHasPersonData();
						var grid = this.MainViewFrame.getGrid();
						if (params && params.Person_id) {
							var birthday = params.Person_Birthday.format('d.m.Y');
							var filterParams = {
								Person_SurName: params.Person_Surname,
								Person_FirName: params.Person_Firname,
								Person_SecName: params.Person_Secname,
								Person_BirthDay_Range: birthday+' - '+birthday
							};
							ShowWindow('swPersonCardAttachListWindow', {filterParams: filterParams});
						}
					}.createDelegate(this)
				},
				{
					name:'show_person_cure_history',
					text: BTN_PERSCUREHIST+' (F11)',
					iconCls: 'pers-curehist16',
					tooltip: BTN_PERSCUREHIST_TIP,
					handler: function() {
						var params = this.MainViewFrame.getParamsIfHasPersonData();
						if (params && params.Person_id) {
							ShowWindow('swPersonCureHistoryWindow', params);
						}
					}.createDelegate(this)
				}
			]
		});
/*
	// 6.3. Очередь по профилю (АРМ пол-ки)		TODO: переделать форму для использования в службах!
*/
		this.onkeypress = function (e) {
			// Ctrl + Б
			if ( Ext.isGecko ) {
				if (( e.getKey() == 1073 || e.getKey() == 1041 ) && e.ctrlKey == true ) {
					curWnd.doReset();
					curWnd.FilterPanel.getForm().findField('Person_Surname').focus(1);
				}
			}
			if (e.getKey() == Ext.EventObject.F6 && e.altKey)
			{
				curWnd.addPersonToUnion();
			}
		};
		
		this.buttonPanelActions = {
			action_EditSchedule: {
				handler: function() {
					getWnd('swScheduleEditMasterWindow').show();
				},
				iconCls: 'schedule32',
				nn: 'action_EditSchedule',
				text: 'Ведение расписания',
				tooltip: 'Ведение расписания'
			},
			action_RecordPerson: {
				handler: function() {
					curWnd.openDirectionMasterWindow();
				},
				iconCls : 'record-new32',
				nn: 'action_RecordPerson',
				text: 'Запись к врачу',
				tooltip: 'Запись к врачу'
			},
			action_HomeVisit: {
				handler: function() {
					getWnd('swHomeVisitListWindow').show({type:'regpol'});
				},
				iconCls : 'mp-region32',
				nn: 'action_HomeVisit',
				text: 'Вызовы на дом',
				tooltip: 'Журнал вызовов на дом'
			},
			action_QuoteEditor: {
				handler: function() {
					getWnd('swTimetableQuoteEditorWindow').show();
				},
				iconCls : 'quota32',
				nn: 'action_QuoteEditor',
				text: 'Редактор квот',
				tooltip: 'Редактирование квот приема'
			},
			action_ProfileQueue: {
				handler: function() {
					getWnd('swMPQueueWindow').show({
						ARMType: 'regpol',
						callback: function(data) {
							// this.createTtgAndOpenPersonEPHForm(data);
							// this.scheduleRefresh();
						}.createDelegate(this),
						mode: 'view',
						userMedStaffFact: this.userMedStaffFact,
						onSelect: function(data) { // на тот случай если из режима просмотра очереди будет сделана запись							
							getWnd('swMPQueueWindow').hide();
							getWnd('swMPRecordWindow').hide();
							// Ext.getCmp('swMPWorkPlaceWindow').scheduleSave(data);
						}						
					}); 
				}.createDelegate(this),
				iconCls : 'mp-queue32',
				nn: 'action_ProfileQueue',
				text: WND_DIRECTION_JOURNAL,
				tooltip: WND_DIRECTION_JOURNAL
			},
			action_WorkplaceOfTheEmployee: {
				handler: function() {
					if(getWnd('swWorkplaceOfTheEmployeeWindow').isVisible()){
						sw.swMsg.alert(langs('Сообщение'), langs('Окно рабочего места сотрудника картохранилища уже открыто'));
						return false;
					}
					getWnd('swWorkplaceOfTheEmployeeWindow').show({type:'regpol', LpuBuilding_id: this.AttachmentLpuBuilding_id});
				}.createDelegate(this),
				iconCls : 'workplaceOfTheEmployee32',
				nn: 'action_HomeVisit',
				text: 'Рабочее место сотрудника картохранилища',
				tooltip: 'Рабочее место сотрудника картохранилища',
				hidden: (!isStorageCardUser() || !getPolkaOptions().allow_access_to_the_functionality_card_store)// скрывать если нет группы StorageCard (Сотрудник картохранилища) или нет доступа в настройках
			},
			action_StorageCard: {
				handler: function() {
					getWnd('swStorageCardWindow').show();
				},
				iconCls : 'cabinet32',
				nn: 'action_StorageCard',
				text: 'Форма предназначена для поиска, просмотра и печати документов посещений пациентом поликлиники',
				tooltip: 'Печать ТАП',
				//hidden: !isStorageCardUser() // скрывать если нет группы StorageCard (Сотрудник картохранилища)
			},


            actions_settings: {
                nn: 'actions_settings',
                iconCls: 'settings32',
                text: 'Сервис',
                tooltip: 'Сервис',
                listeners: {
                    'click': function(){
                        var menu = Ext.menu.MenuMgr.get('wpprw_menu_windows');
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
                                text: 'Открытых окон нет',
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
                                    text: 'Закрыть все окна',
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
                            text: 'Мой профиль',
                            tooltip: 'Профиль пользователя',
                            iconCls : 'user16',
                            hidden: false,
                            handler: function()
                            {
                                args = {}
                                args.action = 'edit';
                                getWnd('swUserProfileEditWindow').show(args);
                            }
                        },
                        {
                            nn: 'action_settings',
                            text: 'Настройки',
                            tooltip: 'Просмотр и редактирование настроек',
                            iconCls : 'settings16',
                            handler: function()
                            {
                                getWnd('swOptionsWindow').show();
                            }
                        },
                        {
                            nn: 'action_selectMO',
                            text: 'Выбор МО',
                            tooltip: 'Выбор МО',
                            iconCls: 'lpu-select16',
                            hidden: !isSuperAdmin(),
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
                            text:'Помощь',
                            nn: 'action_help',
                            iconCls: 'help16',
                            menu: new Ext.menu.Menu(
                                {
                                    //plain: true,
                                    id: 'menu_help',
                                    items:
                                        [
                                            {
                                                text: 'Вызов справки',
                                                tooltip: 'Помощь по программе',
                                                iconCls : 'help16',
                                                handler: function()
                                                {
                                                    ShowHelp('Содержание');
                                                }
                                            },
                                            {
                                                text: 'Форум поддержки',
                                                iconCls: 'support16',
                                                xtype: 'tbbutton',
                                                handler: function() {
                                                    window.open(ForumLink);
                                                }
                                            },
                                            {
                                                text: 'О программе',
                                                tooltip: 'Информация о программе',
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
                            text: 'Информация о пользователе',
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
                            text: 'Окна',
                            nn: 'action_windows',
                            iconCls: 'windows16',
                            listeners: {
                                'click': function(e) {
                                    var menu = Ext.menu.MenuMgr.get('wpprw_menu_windows');
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
                                            text: 'Открытых окон нет',
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
                                                text: 'Закрыть все окна',
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
                                    var menu = Ext.menu.MenuMgr.get('wpprw_menu_windows');
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
                                            text: 'Открытых окон нет',
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
                                                text: 'Закрыть все окна',
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
                                    id: 'wpprw_menu_windows',
                                    items: [
                                        '-'
                                    ]
                                }),
                            tabIndex: -1
                        }/*,
                        {
                            nn: 'action_exit',
                            text:'Выход',
                            iconCls: 'exit16',
                            handler: function()
                            {
                                sw.swMsg.show({
                                    title: 'Подтвердите выход',
                                    msg: 'Вы действительно хотите выйти?',
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
            action_staff_actions: {
				nn: 'action_staff_actions',
				text:'Действия',
				menuAlign: 'tr',
				iconCls : 'database-export32',
				tooltip: 'Действия',
				hidden: !isExpPop(), // Скрывать если нет группы ExportAttachedPopulation (Экспорт прикрепленного населения)
				menu: [
					{
						name: 'download_attached_list',
						text: 'Выгрузить список прикрепленного населения в XML',
						hidden: !isExpPop(),
						handler: function()
						{
							getWnd('swPersonXmlWindow').show();
						}.createDelegate(this)
					}
				]
			},
            action_Spr:{
                nn: 'action_Spr',
                iconCls: 'book32',
                text: 'Справочники',
                tooltip: 'Справочники',
                menu: new Ext.menu.Menu({
                    items: [
                        {
                            text: getRLSTitle(),
                            tooltip: getRLSTitle(),
                            iconCls: 'rls16',
                            handler: function()
                            {
                                getWnd('swRlsViewForm').show();
                            },
                            hidden: false
                        },
                        {
							tooltip: 'Справочник ' + getMESAlias(),
							text: 'Справочник ' + getMESAlias(),
							iconCls: 'spr-mes16',
                            handler: function() {
								if ( !getWnd('swMesOldSearchWindow').isVisible() )
									getWnd('swMesOldSearchWindow').show();
                            }.createDelegate(this)
                        },
                        {
                            text: 'Справочник услуг',
                            tooltip: 'Справочник услуг',
                            iconCls: 'services-complex16',
                            handler: function() {
                                //getWnd('swUslugaTreeWindow').show({action: (isUserGroup('LpuUser') || isUserGroup('OrgUser'))?'view':''});
                                getWnd('swUslugaTreeWindow').show({action: 'view'});
                            }
                        },
						sw.Promed.Actions.swDrugDocumentSprAction
                    ]
                })
            },
            action_Lvn: {
                nn: 'action_Lvn',
                text: 'ЛВН: Поиск',
                tooltip: 'Поиск листков временной нетрудоспособности',
                iconCls : 'lvn-search16',
                handler: function() {
                    getWnd('swEvnStickViewWindow').show();
                }
            },
            action_Rpn:{
                nn: 'action_Rpn',
                iconCls: 'card-search32',
                text: 'РПН',
                tooltip: 'РПН',
                menu: new Ext.menu.Menu({
                    items:[
                        {
                            handler: function() {
                                getWnd('swPersonCardSearchWindow').show();
                            },
                            iconCls : 'card-search32',
                            nn: 'action_PersonCardSearch',
                            text: WND_POL_PERSCARDSEARCH,
                            tooltip: 'РПН: Поиск'
                        },
                        {
                            nn: 'action_RPNPrikr',
                            tooltip: 'РПН: Прикрепление',
                            text: 'РПН: Прикрепление',
                            iconCls : 'card-view32',
                            handler: function()
                            {
                                getWnd('swPersonCardViewAllWindow').show();
                            }
                        },
                        {
                            handler: function() {
                                getWnd('swPersonCardStateViewWindow').show();
                            },
                            iconCls : 'card-state32',
                            nn: 'action_PersonCardState',
                            text: WND_POL_PERSCARDSTATEVIEW,
                            tooltip: 'РПН: Журнал движения'
                        },
                        {
                            nn: 'action_PersonCardAttachList',
							hidden: getRegionNick().inlist(['by']),
                            text: 'РПН: Заявления о выборе МО',
                            tooltip: 'РПН: Заявления о выборе МО',
                            iconCls : 'personcard-attach32',
                            handler: function() {
                                getWnd('swPersonCardAttachListWindow').show();
                            }
                        }
                    ]
                })
            },
			action_EvnPLMovement: {
				handler: function() {
					// getWnd('swPersonCardStateViewWindow').show();
				},
				iconCls : 'pers-cards32',
				nn: 'action_EvnPLMovement',
                hidden: true,
				text: 'Учет движения амбулаторных карт',
				tooltip: 'Учет движения амбулаторных карт'
			},
            action_Srch:{
                nn: 'action_Srch',
                iconCls: 'patient-search32',
                text: 'Поиск',//isPolkaRegistrator()
                tooltip: 'Поиск',
                menu: new Ext.menu.Menu({
                    items: [
                        {
                            handler: function()
                            {
                                getWnd('swPersonSearchWindow').show({
                                    onSelect: function(person_data) {
                                        getWnd('swPersonEditWindow').show({
                                            onHide: function () {
                                                if ( person_data.onHide && typeof person_data.onHide == 'function' ) {
                                                    person_data.onHide();
                                                }
                                            },
                                            Person_id: person_data.Person_id,
                                            Server_id: person_data.Server_id
                                        });
                                    },
                                    searchMode: 'all'
                                });
                            },
                            iconCls : 'patient-search32',
                            nn: 'action_PersonSearch',
                            text: 'Поиск людей',
                            tooltip: 'Поиск людей'
                        },
                        {
                            handler: function() {
                                getWnd('swPrivilegeSearchWindow').show();
                            },
                            iconCls : 'mp-lgot32',
                            nn: 'action_PrivilegeSearch',
                            text: MM_DLO_LGOTSEARCH,
                            tooltip: 'Поиск льготников'
                        },
                        {
                            handler: function() {
                                getWnd('swFindRegionsWindow').show();
                            },
                            iconCls : 'mp-region32',
                            nn: 'action_FindRegions',
                            text: 'Поиск участков и врачей по адресу',
                            tooltip: 'Поиск участков и врачей по адресу'
                        },
                        {
                            text: 'Регистр льготников: Список',
                            tooltip: 'Просмотр льгот по категориям',
                            iconCls : 'lgot-tree16',
                            handler: function() {
                                getWnd('swLgotTreeViewWindow').show();
                            }
                        },
                        {
                            text: MM_DLO_UDOSTLIST,
                            tooltip: 'Удостоверения льготников: Поиск',
                            iconCls : 'udost-list16',
                            handler: function() {
                                getWnd('swUdostViewWindow').show();
                            }
                        }
                    ]
                })
            },
            action_Lpu:{
                nn: 'action_Lpu',
                iconCls: 'structure32',
                text: 'Структура',
                tooltip: 'Структура',
                hidden: !isLpuAdmin() && !isRegAdmin(),
                menu: new Ext.menu.Menu({
                    items: [
                        {
                            handler: function() {
                                getWnd('swLpuStructureViewForm').show();
                            },
                            iconCls : 'structure32',
                            nn: 'action_LpuStructure',
                            text: 'Структура МО',
                            tooltip: 'Структура МО'
                        }, {
                            handler: function() {
                                getWnd('swLpuPassportEditWindow').show({
                                    action: 'edit',
                                    Lpu_id: getGlobalOptions().lpu_id
                                });
                            },
                            iconCls : 'lpu-passport16',
                            hidden: !isLpuAdmin(),
                            nn: 'action_LpuPassport',
                            text: 'Паспорт МО',
                            tooltip: 'Паспорт МО'
                        }
                    ]
                })
            },
			action_MedStaffFactReplace:
			{
				nn: 'action_MedStaffFactReplace',
				tooltip: 'График замещений',
				text: 'График замещений',
				iconCls : 'consult32',
				handler: function()
				{
					getWnd('swMedStaffFactReplaceViewWindow').show();
				}
			},
			action_LpuBuildingOffice:
			{
				nn: 'action_LpuBuildingOffice',
				iconCls : 'cabinet32',
				text: 'Кабинеты',
				tooltip: 'Кабинеты',

				menu: new Ext.menu.Menu({
					items: [
						{
							nn: 'action_LpuBuildingOffice',
							handler: function() {
								if ( ! getWnd('swLpuBuildingOfficeListWindow').isVisible() ){
									getWnd('swLpuBuildingOfficeListWindow').show();
								}
							},
							iconCls : 'cabinet32',
							text: 'Справочник кабинетов',
							tooltip: 'Справочник кабинетов'
						}, {
							nn: 'action_LpuScheduleWorkDoctor',
							handler: function() {
								if ( ! getWnd('swLpuScheduleWorkDoctorWindow').isVisible() ){
									getWnd('swLpuBuildingScheduleWorkDoctorWindow').show();
								}
							},
							iconCls : 'cabinet32',
							text: 'Расписание работы врачей',
							tooltip: 'Расписание работы врачей'
						}
					]
				})
			},
			action_JourNotice: {
				handler: function() {
					getWnd('swMessagesViewWindow').show();
				}.createDelegate(this),
				iconCls: 'notice32',
				nn: 'action_JourNotice',
				text: 'Журнал уведомлений',
				tooltip: 'Журнал уведомлений'
			},
            action_Moderation: {
                nn: 'action_Moderation',
				hidden: getRegionNick().inlist(['by']),
                iconCls: 'web-record32',
                text: 'Модерация',
                tooltip: 'Модерация',
                menu: new Ext.menu.Menu({
                    items: [
                        {
                            handler: function() {
                                getWnd('swTimetableGrafModerationWindow').show();
                            }.createDelegate(this),
                            iconCls: 'web-record32',
                            nn: 'action_TimetableMModeration',
                            text: 'Модерация интернет-записи',
                            id:'InternetModeration',
                            tooltip: 'Модерация интернет-записи'
                        },
                        {
                            handler: function() {
                                getWnd('swInetPersonModerationWindow').show();
                            }.createDelegate(this),
                            iconCls: 'web-record32',
                            nn: 'action_InetPersonModeration',
                            text: 'Модерация людей',
                            id:'InetPersonModeration',
                            tooltip: 'Модерация людей с портала самозаписи'
                        }
                    ]
                })
            },
            action_AutoPersonCard: {
                handler: function() {
					//проверка на случай изменения параметров системы с открытым АРМ регистратора
					if (!Ext.isEmpty(getGlobalOptions().check_attach_allow) && getGlobalOptions().check_attach_allow == 1 && !(isUserGroup('LpuAdmin') || isUserGroup('RegAdmin') || isUserGroup('SuperAdmin'))) {
						sw.swMsg.alert('Сообщение', 'У вас нет прав для редактирования прикрепления.');
						return false;
					}

                    getWnd('swPersonSearchPersonCardAutoWindow').show();
                }.createDelegate(this),
                iconCls: 'pcard-new32',
                nn: 'action_AutoPersonCard',
				id: 'wpprw_action_AutoPersonCard_id',
                text: 'Групповое прикрепление',
                tooltip: 'Групповое прикрепление'
            },
            action_PersonEncrypHIV: {
                handler: function() {
                    getWnd('swPersonEncrypHIVViewWindow').show();
                }.createDelegate(this),
				hidden: true,
                iconCls: 'registry32',
                nn: 'action_PersonEncrypHIV',
				id: 'wpprw_action_PersonEncrypHIV',
                text: 'Шифрование ВИЧ-инфицированных',
                tooltip: 'Шифрование ВИЧ-инфицированных'
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
			action_TFOMSQueryList: {
				nn: 'action_TFOMSQueryList',
				//hidden: getRegionNick().inlist(['by']),
				text: 'Запросы на просмотр ЭМК',
				tooltip: 'Запросы на просмотр ЭМК',
				iconCls: 'tfoms-query32',
				handler: function () {
					getWnd('swTFOMSQueryWindow').show({ARMType: 'mstat'});
				}
			},
			action_Treatments: {
				nn: 'action_Treatments',
					text: 'Обращения',
					menuAlign: 'tr',
					tooltip: 'Обращения',
					iconCls: 'reports32',
					menu: new Ext.menu.Menu({
					items: [
						{
							nn: 'action_swTreatmentSearchAction',
							handler: function() {
								if ( ! getWnd('swTreatmentSearchWindow').isVisible() ){
									getWnd('swTreatmentSearchWindow').show();
								}
							},
							iconCls : 'petition-search16',
							text: langs('Регистрация обращений: Поиск'),
							tooltip: langs('Регистрация обращений: Поиск')
							//hidden: !isAccessTreatment()
						},
						{
							nn: 'action_LpuScheduleWorkDoctor',
							handler: function() {
								if ( ! getWnd('swTreatmentReportWindow').isVisible() ){
									getWnd('swTreatmentReportWindow').show();
								}
							},
							iconCls : 'petition-report16',
							text: langs('Регистрация обращений: Отчетность'),
							tooltip: langs('Регистрация обращений: Отчетность'),
							//hidden: isUserGroup(['lpuadmin', '5555'])
						}
					]
				})
			},
			action_Templ: {
				handler: function() {
					var params = {
						EvnClass_id: 11,
						XmlType_id: 3,
						allowSelectXmlType: true,
						LpuSection_id: curWnd.userMedStaffFact.LpuSection_id,
						MedPersonal_id: curWnd.userMedStaffFact.MedPersonal_id,
						MedStaffFact_id: curWnd.userMedStaffFact.MedStaffFact_id
					};
					getWnd('swTemplSearchWindow').show(params);
				},
				iconCls : 'document32',
				nn: 'action_Templ',
				text: langs('Шаблоны документов'),
				tooltip: langs('Шаблоны документов')
			}
		};

		this.FilterPanel = new sw.Promed.BaseWorkPlaceFilterPanel({
			owner: curWnd,
			keys: [{
				key: Ext.EventObject.ENTER,
				fn: function(e) {
					curWnd.doSearch();
				},
				scope: this,
				stopEvent: true
			}],
			labelWidth: 100,
			filter: {
				title: 'Фильтры',
				layout: 'form',
				items: [{
					layout: 'column',
					items: [{
						layout: 'form',
						hidden: getRegionNick() != 'kz',
						items: [{
							enableKeyEvents: true,
							fieldLabel: 'ИИН',
							maskRe: /\d/,
							name: 'Person_Inn',
							autoCreate: {tag: "input", type: "text", size: "30", maxLength: "12", autocomplete: "off"},
							width: 200,
							maxLength: 12,
							minLength: 12,
							xtype: 'textfield'
						}]
					}, {
						layout: 'form',
						items: [{
							fieldLabel: 'Фамилия',
							name: 'Person_Surname',
							maskRe: /[^_%]/,
							width: (getRegionNick() == 'kz' ? 120 : 200),
							xtype: 'textfieldpmw'
						}]
					}, {
						layout: 'form',
						labelWidth: (getRegionNick() == 'kz' ? 120 : 100),
						items: [{
							fieldLabel: 'Имя',
							name: 'Person_Firname',
							maskRe: /[^_%]/,
							width: 120,
							xtype: 'textfieldpmw'
						}]
					}, {
						layout: 'form',
						labelWidth: (getRegionNick() == 'kz' ? 100 : 120),
						items: [{
							fieldLabel: 'Отчество',
							name: 'Person_Secname',
							maskRe: /[^_%]/,
							width: 120,
							xtype: 'textfieldpmw'
						}]
					}, {
						layout: 'form',
						labelWidth: 75,
						items: [{
							fieldLabel: 'ДР',
							format: 'd.m.Y',
							name: 'Person_Birthday',
							plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
							width: 100,
							xtype: 'swdatefield'
						}]
					}]
				}, {
					layout: 'column',
					items: [{
						layout: 'form',
						items: [{
							enableKeyEvents: true,
							fieldLabel: 'Улица',
							name: 'Address_Street',
							width: 200,
							xtype: 'textfield'
						}]
					}, {
						layout: 'form',
						items: [{
							enableKeyEvents: true,
							fieldLabel: 'Дом',
							name: 'Address_House',
							width: 120,
							xtype: 'textfield'
						}]
					}, {
						layout: 'form',
						labelWidth: 120,
						items: [{
							enableKeyEvents: true,
							fieldLabel: 'Номер амб. карты',
							name: 'PersonCard_Code',
							width: 120,
							xtype: 'textfield'
						}]
					},{
						layout: 'form',
						labelWidth: 160,
						items: [{
							enableKeyEvents: true,
							fieldLabel: 'Учитывать историю карт',
							name: 'checkHistoryCard',
							width:80,
							xtype: 'checkbox'
						}]
					}, {
						layout: 'form',
						labelWidth: 75,
						hidden: getRegionNick() != 'ekb',
						style: "padding-left: 5px",
						items: [{
							name: 'PartMatchSearch',
							hideLabel: true,
							boxLabel: 'Поиск по частичному совпадению',
							xtype: 'checkbox'
						}]
					}]
				}, {
					layout: 'column',
					hidden: getRegionNick() == 'kz',
					items: [{
						layout: 'form',
						items: [{
							enableKeyEvents: true,
							fieldLabel: 'Серия полиса',
							name: 'Polis_Ser',
							width: 100,
							xtype: 'textfield'
						}]
					}, {
						layout: 'form',
						labelWidth: 200,
						items: [{
							allowNegative: false,
							enableKeyEvents: true,
							allowLeadingZeroes: true,
							fieldLabel: 'Номер полиса',
							name: 'Polis_Num',
							width: 120,
							xtype: 'numberfield'
						}]
					}, {
						layout: 'form',
						labelWidth: 120,
						items: [{
							allowNegative: false,
							enableKeyEvents: true,
							allowLeadingZeroes: true,
							fieldLabel: 'Ед. номер',
							name: 'Person_Code',
							width: 120,
							xtype: 'numberfield'
						}]
					}]
				}, {
					layout: 'column',
					items: [{
						layout: 'form',
						items: [{
							style: "padding-left: 20px",
							xtype: 'button',
							id: curWnd.id + 'BtnSearch',
							text: 'Найти',
							iconCls: 'search16',
							handler: function() {
								curWnd.doSearch();
							}
						}]
					}, {
						layout: 'form',
						items: [{
							style: "padding-left: 10px",
							xtype: 'button',
							id: curWnd.id + 'BtnClear',
							text: 'С<u>б</u>рос',
							iconCls: 'reset16',
							handler: function() {
								curWnd.doReset();
							}
						}]
					}, {
						layout: 'form',
						items:
							[{
								style: "padding-left: 10px",
								xtype: 'button',
								text: 'Считать с карты',
								iconCls: 'idcard16',
								handler: function()
								{
									curWnd.readFromCard();
								}
							}]
					}, {
						layout: 'form',
						items: [{
							style: "padding-left: 10px",
							xtype: 'button',
							id: curWnd.id + 'BtnAdd',
							text: 'Добавить',
							iconCls: 'add16',
							hidden: !(getGlobalOptions().region.nick && getGlobalOptions().region.nick == 'ufa'),
							handler: function() {
								curWnd.addUser();
							}
						}]
					}]
				}]
			}
		});

		this.MainViewFrame = new sw.Promed.ViewFrame({
			actions: [
				{name: 'action_add', hidden: true},
				{name: 'action_edit', handler: function() {curWnd.openDirectionMasterWindow();}, text: 'Выписать направление', hidden: true}, // эта кнопка полностью дублирует имеюищуюся записать в дочернем гриде, скрыл
				{name: 'action_view', handler: function() {curWnd.openPersonCardEditWindow('view');}},
				{name: 'action_delete', hidden: true},
				{name: 'action_refresh'},
				{name: 'action_print', text: 'Печать списка'}
			],
			allowedPersonKeys: ((getGlobalOptions().region.nick == 'kareliya') ? (['F6', 'Alt+F6', 'F10', 'F11', 'F12', 'Ctrl+F12']) : ((getRegionNick() == 'ekb')?(['F6', 'Alt+F6', 'F10', 'F12', 'Ctrl+F12']):(null))),
			autoExpandColumn: 'autoexpand',
			autoLoadData: false,
			dataUrl: '/?c=Person&m=getPersonGrid',
			stateful: true,
			id: curWnd.id + 'PolkaRegWorkPlacePanel',
			onDblClick: function() {
				this.onEnter();
			},
			onEnter: function() {
				curWnd.openDirectionMasterWindow();
			},
			onRowSelect: function(sm, index, record) {
				var person_data = this.getParamsIfHasPersonData();

				this.setActionDisabled('action_edit', true);
				this.setActionDisabled('action_view', true);
				this.setActionDisabled('print', true);
				this.setActionDisabled('action_adddirection_regpolka', true);
				this.setActionDisabled('action_add_incoming_regpolka', true);
				curWnd.bj.mainGrid.setActionDisabled('action_adddirection', true);
				curWnd.bj.mainGrid.setActionDisabled('action_add_incoming', true);
				curWnd.MainActionsMenu.items.itemAt(1).disable();
				curWnd.MainActionsMenu.items.itemAt(2).disable();
				curWnd.MainActionsMenu.items.itemAt(3).disable();
				curWnd.MainActionsMenu.items.itemAt(4).disable();
				curWnd.MainActionsMenu.items.itemAt(5).disable();

				if (record && Ext.isEmpty(record.get('PersonAmbulatCard_id'))) {
					curWnd.MainPrintMenu.items.itemAt(3).disable();
				} else {
					curWnd.MainPrintMenu.items.itemAt(3).enable();
				}

				curWnd.bj.mainGrid.removeAll({clearAll:true});
				curWnd.PersonAmbulatCard.removeAll({clearAll:true});

				curWnd.bj.mainGrid.setActionDisabled('show_history', !( record && !Ext.isEmpty(record.get('Person_id')) ));
				if (record && record.get('Person_id')) {
					this.setActionDisabled('action_edit', false);
					this.setActionDisabled('print', false);
					this.setActionDisabled('action_adddirection_regpolka', false);
					this.setActionDisabled('action_add_incoming_regpolka', false);
					curWnd.bj.mainGrid.setActionDisabled('action_adddirection', false);
					curWnd.bj.mainGrid.setActionDisabled('action_add_incoming', false);
					curWnd.MainActionsMenu.items.itemAt(1).enable();
					curWnd.MainActionsMenu.items.itemAt(2).enable();
					curWnd.MainActionsMenu.items.itemAt(3).enable();
					curWnd.MainActionsMenu.items.itemAt(4).enable();
					curWnd.MainActionsMenu.items.itemAt(5).enable();

					curWnd.currentRecord = record;
					switch(curWnd.Regpol_tabs.getActiveTab().id){
						case 'tab_AmbulatCard':
							curWnd.PersonAmbulatCard.loadData({
								globalFilters: {
									Person_id: record.get('Person_id'),
									Lpu_id: getGlobalOptions().lpu_id
								}
								//noFocusOnLoad: true
							});
							break;
						case 'tab_Direction':
							curWnd.bj.doSearch({Person_id: record.get('Person_id'),person_data:person_data});
							break;
					}
				}

				if (record && record.get('PersonCard_id')) {
					this.setActionDisabled('action_view', false);
				}
			},
			paging: true,
			pageSize: 50,
			region: 'center',
			root: 'data',
			stringfields: [
				// Поля для отображение в гриде
				{name: 'Person_id', type: 'int', header: 'ID', key: true},
				{name: 'Server_id', type: 'int', hidden: true},
				{name: 'PersonEvn_id', type: 'int', hidden: true},
				{name: 'PersonCard_id', type: 'int', hidden: true},
				{name: 'Person_IsDead', type: 'string', hidden: true},
				{name: 'PersonQuarantine_IsOn', type: 'string', hidden: true},
                {name: 'AttachLpu_id', type: 'string', hidden: true},
                {name: 'PersonAmbulatCard_id', type: 'int', hidden: true},

                {name: 'PersonAmbulatCard_Num', hidden: true},

				{name: 'PersonCard_Code', header: '№ амб карты', width: 80, renderer: function(v, p, row) {
					var store = curWnd.MainViewFrame.getGrid().getStore();
					if (!Ext.isEmpty(store.baseParams.PartMatchSearch) && !Ext.isEmpty(store.baseParams.PersonCard_Code) && !Ext.isEmpty(v)) {
						// подсвечиваем
						v = v.replace(new RegExp(store.baseParams.PersonCard_Code), "<span style='background-color:yellow;'>"+store.baseParams.PersonCard_Code+"</span>");
					}
					return v;
				}},
				{name: 'Person_Surname', type: 'string', header: 'Фамилия', id: 'autoexpand', width: 100},
				{name: 'Person_Firname', type: 'string', header: 'Имя', width: 100},
				{name: 'Person_Secname', type: 'string', header: 'Отчество', width: 100},
				{name: 'Person_Birthday', type: 'date', header: 'Дата рождения', renderer: Ext.util.Format.dateRenderer('d.m.Y')},
				{name: 'Person_deadDT', type: 'date', header: 'Дата смерти', renderer: Ext.util.Format.dateRenderer('d.m.Y')},
				{name: 'Person_Age', type: 'int', header: 'Возраст', width: 100},
				getGlobalOptions().region.nick != 'kz' ? {name: 'Person_PolisInfo', type: 'string', header: 'Полис', width: 160} : {name: 'Person_Inn', type: 'string', header: 'ИИН', width: 160},
				{name: 'Person_Phone', type: 'string', header: 'Телефон', width: 100},
				{name: 'AttachLpu_Name', type: 'string', header: 'МО прик.'},
				{name: 'PersonCard_begDate', type: 'date', header: 'Прикрепление', renderer: Ext.util.Format.dateRenderer('d.m.Y')},
				{name: 'PersonCard_endDate', type: 'date', header: 'Открепление', renderer: Ext.util.Format.dateRenderer('d.m.Y')},
				{name: 'LpuAttachType_Name', type: 'string', header: 'Тип прикрепления', width: 200},
				{name: 'LpuRegionType_Name', type: 'string', header: getRegionNick() === 'perm' ? 'Тип основного участка' : 'Тип участка', width: 200},
				{name: 'LpuRegion_Name', type: 'string', header: getRegionNick() === 'perm' ? 'Основной участок' : 'Участок'},
                {name: 'LpuRegion_FapName', type: 'string', header: 'ФАП участок',width:100, hidden: (getRegionNick() != 'perm' && getRegionNick() != 'ufa' && getRegionNick() != 'penza')},
				{name: 'PersonCard_IsAttachCondit',  header: 'Усл. прикрепл.', type: 'checkbox'},
				{name: 'PersonLpuInfo_IsAgree', header: 'Согласие', type: 'string', align: 'center'},
				{name: 'NewslatterAccept_id', type: 'int', hidden: true},
				{name: 'NewslatterAccept',  header: 'СМС/e-mail уведомления', type: 'string'},
				{name: 'Person_IsBDZ', header: 'БДЗ', type: 'checkcolumn', width: 30},
				{name: 'Person_IsFedLgot', header: (getRegionNick().inlist([ 'kz' ]) ? 'Льгота' : 'Фед.льг.'), type: 'checkbox', width: 40},
				{name: 'Person_IsRefuse', header: 'Отказ', type: 'checkbox', width: 40},
				{name: 'Person_IsRegLgot', header: 'Рег.льг.', type: 'checkbox', width: 40, hidden: getRegionNick().inlist([ 'kz' ])},
				{name: 'Person_Is7Noz', header: '7 ноз.', type: 'checkbox', width: 40},
				{name: 'Person_UAddress', header: 'Адрес регистрации', type: 'string', width: 240},
				{name: 'Person_PAddress', header: 'Адрес проживания', type: 'string', width: 240}
			],
			title: 'Журнал рабочего места',
			totalProperty: 'totalCount',
			passPersonEvn: false, // по просьбе уфы (refs #18175)			
            onLoadData: function(sm, index, records) {
				this.MainViewFrame.getGrid().getStore().each(function(rec,idx,count) {
					var naHref;
					if (naHref = rec.get('NewslatterAccept')) {
						naHref = '<a href="javascript://" onClick="Ext.getCmp(\''+this.id+'\').openNewslatterAcceptEditWindow(\''+rec.get('NewslatterAccept_id')+'\', \''+rec.get('Person_id')+'\')">'+rec.get('NewslatterAccept')+'</a>';							
						rec.set('NewslatterAccept', naHref);
						rec.commit();
					}
				}.createDelegate(this));
            }.createDelegate(this),
			getParamsIfHasPersonData: function() {
				var viewframe = this;
				var selected_record = viewframe.ViewGridPanel.getSelectionModel().getSelected();
				
				// Собираем информацию о человеке в случае, если в гриде есть поля по человеку
				if (viewframe.hasPersonData() && selected_record != undefined)
				{
					var params = new Object();
					//log('hasPersonData selected record');
					params.Person_IsDead = selected_record.get('Person_IsDead');
					params.Person_id = selected_record.get('Person_id');
                    params.Server_id = selected_record.get('Server_id');
                    params.PersonEvn_id = selected_record.get('PersonEvn_id');
					// некоторые именуют как в базе, но почему-то изначально выбрано не такое именование
					// но так-то надо в гридах переделать
					if ( selected_record.get('Person_Birthday') )
						params.Person_Birthday = selected_record.get('Person_Birthday');
					else
						params.Person_Birthday = selected_record.get('Person_BirthDay');
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
						var index = viewframe.ViewGridPanel.getStore().findBy(function(rec) { return rec.get(viewframe.jsonData['key_id']) == selected_record.data[viewframe.jsonData['key_id']]; });
						viewframe.ViewGridPanel.focus();
						viewframe.ViewGridPanel.getView().focusRow(index);
						viewframe.ViewGridPanel.getSelectionModel().selectRow(index);
					}
					if (viewframe.callbackPersonEdit)
					{
						viewframe.selectedRecord = selected_record;
						params.callback = function() {this.callbackPersonEdit()}.createDelegate(viewframe);
					}
					return params;
				}
				return false;
			}
		});
		this.MainViewFrame.getGrid().on('keypress', this.onkeypress);
		this.MainViewFrame.getGrid().keys = {
			key: 188,
			ctrl: true,
			handler: function() {
				curWnd.doReset();
				curWnd.FilterPanel.getForm().findField('Person_Surname').focus(1);
			}
		};

		var mainViewFrameView = curWnd.MainViewFrame.getGrid().getView();
		this.MainViewFrame.getGrid().view = new Ext.grid.GridView({
			getRowClass: function (row, index) {
				var cls = mainViewFrameView.getRowClass(row, index);

				if (row.get('PersonQuarantine_IsOn') == 'true') {
					cls = cls + ' x-grid-rowbackred ';
				}

				return cls;
			}
		});
		
		this.PersonAmbulatCard = new sw.Promed.ViewFrame({
			tbActions:true,
			actions: [
				{name: 'action_add', handler:function(){curWnd.OpenPersonAmbulatCard('add')}},
				{name: 'action_edit', handler:function(){curWnd.OpenPersonAmbulatCard('edit')}},
				{name: 'action_view', handler:function(){curWnd.OpenPersonAmbulatCard('view')}},
				{name: 'action_delete', handler:function(){curWnd.DeletePersonAmbulatCard('view')}},
				{name: 'action_refresh'},
				{name: 'action_print', hidden: true},
				{
					name: 'print',
					text:'Печать',
					tooltip: 'Печать',
					disabled: true,
					iconCls : 'x-btn-text',
					icon: 'img/icons/print16.png',
					handler: function() {},
					menu: [{
						disabled: false,
						handler: function() {
							var grid = this.PersonAmbulatCard.getGrid(),
								record = grid.getSelectionModel().getSelected();

							if (Ext.isEmpty(record)){
								return false;
							}

							var personCard = 0,
								personId = 0,
								personAmbulatCard_id = 0,
								personAmbulatCard_num = 0;

							if(! Ext.isEmpty(record.get('PersonCard_id'))){
								personCard = record.get('PersonCard_id');
							}

							if(! Ext.isEmpty(record.get('Person_id'))){
								personId = parseInt(record.get('Person_id'));
							}

							if(! Ext.isEmpty(record.get('PersonAmbulatCard_id'))){
								personAmbulatCard_id = parseInt(record.get('PersonAmbulatCard_id'));
							}

							if(! Ext.isEmpty(record.get('PersonAmbulatCard_Num'))){
								personAmbulatCard_num = parseInt(record.get('PersonAmbulatCard_Num'));
							}

							this._printMedCard(personCard, personId, personAmbulatCard_id, personAmbulatCard_num);

							return true;
						}.createDelegate(this),
						iconCls: 'print16',
						name: 'print_personcard',
						text: 'Печать амбулаторной карты',
						tooltip: 'Печать амбулатороной карты пациента'
					},{
						disabled: false,
						handler: function(btn) {
							var grid = this.PersonAmbulatCard.getGrid(),
								record = grid.getSelectionModel().getSelected();
							var PersonAmbulatCard_id = record.get('PersonAmbulatCard_id');
							if(!PersonAmbulatCard_id) return false;
							printBirt({
								'Report_FileName': 'BarCodesPrint_AmbulatCard.rptdesign',
								'Report_Params': '&AmbulatCard=' + PersonAmbulatCard_id,
								'Report_Format': 'pdf'
							});
						}.createDelegate(this),
						iconCls: 'print16',
						name: 'printed_barcode_ambulatory_card',
						text: 'Печать штрих-кода амбулаторной карты',
						tooltip: 'Печать штрих-кода амбулаторной карты'
					},{
						disabled: false,
						name: 'print_personstomcard',
						tooltip: 'Печать стоматологической карты пациента',
						text: 'Печать стомат. карты (форма 043/у)',
						iconCls: 'print16',
						handler: function() {
							var grid = this.PersonAmbulatCard.getGrid(),
								record = grid.getSelectionModel().getSelected();

							if (Ext.isEmpty(record)){
								return false;
							}

							var Person_id = grid.getSelectionModel().getSelected().get('Person_id');

							printBirt({
								'Report_FileName': 'f043u.rptdesign',
								'Report_Params': '&paramLpu=' + (!Ext.isEmpty(getGlobalOptions().lpu_id)?getGlobalOptions().lpu_id:0) + '&paramEvnVizitPLStom_id=0&paramPerson_id=' + Person_id,
								'Report_Format': 'pdf'
							});
						}.createDelegate(this)
					}
				]}
			],
			autoExpandColumn: 'autoexpand',
			height: 200,
			autoLoadData: false,
			dataUrl: '/?c=PersonAmbulatCard&m=getPersonAmbulatCardList',
			id: curWnd.id + 'PersonAmbulatCardGrid',
			onDblClick: function() {
				curWnd.OpenPersonAmbulatCard('edit');
			},
			onEnter: function() {
				
			},
            onRowSelect: function(sm, index, record) {
               curWnd.PersonAmbulatCard.setActionDisabled('action_delete',(record.get('isAttach')=='true'));
               curWnd.PersonAmbulatCard.setActionDisabled('print',Ext.isEmpty(record.get('Person_id')));
            },
			border:false,
			frame:false,
			paging: false,
			region: 'center',
			stringfields: [
				// Поля для отображение в гриде
				{name: 'PersonAmbulatCard_id', type: 'string', header: 'ID', key: true},
				{name: 'Person_id', type: 'int', hidden: true},
				{name: 'PersonCard_id', type: 'int', hidden: true},
				{name: 'PersonAmbulatCard_Num', type:'string', header: "Номер карты"},
				{name: 'PersonAmbulatCardLocat_begDate', type: 'date',renderer: Ext.util.Format.dateRenderer('d.m.Y'), header: "Дата последнего движения", width: 150},
				{name: 'AmbulatCardLocatType_id', type: 'int',hidden: true },
				{name: 'isAttach', hidden: true, type: 'checkbox'},
				{name: 'AmbulatCardLocatType_Name', type:'string', header: "Текущее местонахождение", id: 'autoexpand'}
				
			]
		});
		
		this.bj = new sw.Promed.BaseJournal({
			height: 215,
			region: 'center',
			ARMType: 'regpol',
			winType: 'reg',
			border:false,
			ownerWindow:curWnd,
			noFocusOnLoad: true,
			checkBeforeLoadData: function(store, options) {
				// запрещаем грузить сторе, если в параметрах не указан Person_id.
				if ((options.params && options.params.Person_id) || (store.baseParams && store.baseParams.Person_id)) {
					return true;
				}
				return false;;
			},
			//Ufa, gaf #116422, для ГАУЗ РВФД
			onLoadData: function(sm, index, records) {                
				if (curWnd.bj.mainGrid.rowCount >0){
					curWnd.bj.mainGrid.setActionDisabled('route_list_rvfd', false);    
				}else{
					curWnd.bj.mainGrid.setActionDisabled('route_list_rvfd', true);    
				}                                
			}.createDelegate(this),			                        
			actions:[
				'action_add',
				'action_delete',
				'action_add_incoming',
				'action_leave_queue',
				'action_in_queue',
				'action_redirect',
				'action_rewrite',
				'action_view',
				'action_print',
				'show_history',
				//Ufa, gaf #116422, для ГАУЗ РВФД
				'route_list_rvfd'
			]
		});

		this.Regpol_tabs = new Ext.TabPanel({
			region: 'center',
			id: 'Regpol-tabs-panel',
			//autoScroll: true,
			//height: 220,
			border:true,
			activeTab: 0,
			//resizeTabs: true,
			//enableTabScroll: true,
			//autoWidth: true,
			//tabWidth: 'auto',
			layoutOnTabChange: true,
			listeners: {
				'tabchange': function(tab, panel) {
					if(curWnd.currentRecord!=null){
						var person_data=curWnd.MainViewFrame.getParamsIfHasPersonData();
						switch(panel.id){
						case 'tab_AmbulatCard':
							curWnd.PersonAmbulatCard.loadData({
								globalFilters: {
									Person_id: curWnd.currentRecord.get('Person_id'),
									Lpu_id: getGlobalOptions().lpu_id
								}
								//noFocusOnLoad: true
							});
							break;
						case 'tab_Direction':
							curWnd.bj.doSearch({Person_id: curWnd.currentRecord.get('Person_id'),person_data:person_data});
							break;
						}
					}
				}
			},
			items:[{
				title: 'Направления и записи',
				id: 'tab_Direction',
				iconCls: 'info16',
				border:false,
				items: [curWnd.bj]
			},{
				title: 'Амбулаторные карты',
				id: 'tab_AmbulatCard',
				iconCls: 'info16',
				border:false,
				items: [curWnd.PersonAmbulatCard]
			}]
		});

		this.ElectronicQueuePanel = new sw.Promed.ElectronicQueuePanel({
			panelType: 3, // панель со своим собственным гридом и функционалом прямой ЭО (без бирок)
			ownerWindow: curWnd,
			layoutPanelId: 'wpprBottomPanel', // лэйаут для перерисовки
			region: 'south',
			hideDirectQueueGrid: false, // скрыть особый грид для работы с ЭО
			refreshTimer: 30000
		});

		this.GridPanel = new sw.Promed.Panel({
			region: 'center',
			border: false,
			id: 'wpprWorkingPanel',
			layout: 'border',
			items: [
				this.ElectronicQueuePanel.ElectronicQueueGrid, // грид для ЭО, свернут
				this.MainViewFrame,
				new sw.Promed.Panel({
					region: 'south',
					border: false,
					height: (this.ElectronicQueuePanel.isVisible() ? 276 : 218),
					layout: 'border',
					id: 'wpprBottomPanel',
					items: [
						this.Regpol_tabs,
						this.ElectronicQueuePanel
					]
				})
			]
		});

		sw.Promed.swWorkPlacePolkaRegWindow.superclass.initComponent.apply(this, arguments);
	},
});