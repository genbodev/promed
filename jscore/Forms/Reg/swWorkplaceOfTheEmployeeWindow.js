/**
 * swWorkplaceOfTheEmployeeWindow - форма "Рабочее место сотрудника картохранилища"
 */
/*NO PARSE JSON*/
sw.Promed.swWorkplaceOfTheEmployeeWindow = Ext.extend(sw.Promed.BaseForm, {
	closable: true,
	closeAction: 'hide',
	maximized: true,
	title: 'Рабочее место сотрудника картохранилища',
	iconCls: 'workplace-mp16',
	id: 'swWorkplaceOfTheEmployeeWindow',
	readOnly: false,
	
	/**
	 * Идентификатор выбранного МО
	 */
	Lpu_id: null,
	LpuBuilding_id: null,
	timer: null,
	params: {},
	timerSeconds: 60000,
	divPopUpMessage: document.createElement('div'),
	FindByBarcode: false,
	arrayDeliverCard: [],
	/**
	 * Функция возврашающся ссылку на родительский элемент
	 */
	//getOwner: null,
	
	/**
	 * Дата, на которую отображаются записи пациентов на приём к врачам 
	 */
	date: null,

	/**
	 * Маска для загрузки
	 */
	loadMask: null,
	
	listeners: {
		hide: function () {
			this.stopTimerLoadRecords();
			if(this.ScanningBarcodeService.running_in_shape().form == this.id){
				this.ScanningBarcodeService.stop();
			}
		},
		'deactivate': function() {
			//sw.Applets.BarcodeScaner.stopBarcodeScaner();
			if(!this.FindByBarcode) this.ScanningBarcodeService.stop();
		}
	},
	/**
	 * @param date Дата, на которую загружать записи
	 */
	//loadHomeVisits
	downloadPatientRecords: function(mode)
	{
		this.Lpu_id = getGlobalOptions()['lpu_id'];
		var btn = this.getPeriodToggle(mode);
		if (btn) 
		{
			if (mode != 'range')
			{
				if (this.mode == mode)
				{
					btn.toggle(true);
					if (mode != 'day') // чтобы при повторном открытии тоже происходила загрузка списка на этот день
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

		params.limit = 100;
		params.start = 0;
		
		var form = this.filtersPanel.getForm();
		log('getgrid',this.getGrid())
		this.getGrid().getStore().removeAll();

		params.Person_FirName = form.findField('Person_Firname').getValue();
		params.Person_SecName = form.findField('Person_Secname').getValue();
		params.Person_SurName = form.findField('Person_Surname').getValue();
		params.Person_BirthDay = Ext.util.Format.date(form.findField('Person_Birthday').getValue(), 'd.m.Y');
		params.Lpu_id = this.Lpu_id;

		params.type = this.type;
        //params.MedPersonal_id = form.findField('MedPersonal_id').getValue();
        params.MedStaffFact_id = form.findField('MedStaffFact_id').getValue();
        params.LpuBuilding_id = form.findField('PR_LpuBuilding_id').getValue();
        params.CardAtTheReception = form.findField('CardAtTheReception').getValue();
		params.RequestFromTheDoctor = form.findField('RequestFromTheDoctor').getValue();
		params.field_numberCard = form.findField('field_numberCard').getValue();
		params.attachmentLpuBuilding_id = this.LpuBuilding_id;
		params.Person_id = null;
		params.PersonAmbulatCard_id = null;
		this.params = params;

		// не загружаем без выбранного подразделения
		if(!params.LpuBuilding_id) return false;
		
		this.getGrid().getStore().load({
			params: params,
			callback: function(){
				this.timerLoadRecords();
			}.createDelegate(this)
			
		});
	},
	loadDataReceptionTableGrid: function(func){
		var params = this.params;
		var func = func || false;
		if(Object.keys(params).length == 0) {
			if(func && typeof func == 'function') func(false);
			return false;
		}
		Ext.Ajax.request(
		{
			params: params,
			url: '?c=EvnVizit&m=loadReceptionTableGrid',
			failure: function (result_form, action) {
				log('ошибка при загрузке данных');
				if(func && typeof func == 'function') func(false);
			},
			callback: function(opt, success, response) 
			{
				var response_obj = Ext.util.JSON.decode(response.responseText);
				
				if(response_obj && response_obj.length>0) this.getGrid().getStore().loadData(response_obj);
				if(func && typeof func == 'function') func(response_obj);
			}.createDelegate(this)
		});
	},
	timerLoadRecords: function(){
		//обновление записей
		if(this.timer) return false;
		this.timer = setInterval(function(){
			this.loadDataReceptionTableGrid();
		}.bind(this), this.timerSeconds);
	},
	stopTimerLoadRecords: function(){
		if(this.timer) {
			clearInterval(this.timer);
			this.timer = null;
		}
	},
	/**
	 * Возвращает грид
	 */
	getGrid: function ()
	{
		return this.ReceptionTableGrid.getGrid();
	},
	getStore: function(){
		return this.getGrid().getStore();
	},
	getSelected: function(){
		return this.getGrid().getSelectionModel().getSelected();
	},
	getPeriodToggle: function (mode)
	{
		switch(mode)
		{
		case 'day':
			return this.WindowToolbar.items.items[6];
			break;
		case 'week':
			return this.WindowToolbar.items.items[7];
			break;
		case 'month':
			return this.WindowToolbar.items.items[8];
			break;
		case 'range':
			return this.WindowToolbar.items.items[9];
			break;
		default:
			return null;
			break;
		}
	},

	/**
	 * Перемещение по календарю
	 */
	stepDay: function(day)
	{
		var frm = this;
		var date1 = (frm.dateMenu.getValue1() || Date.parseDate(frm.curDate, 'd.m.Y')).add(Date.DAY, day).clearTime();
		var date2 = (frm.dateMenu.getValue2() || Date.parseDate(frm.curDate, 'd.m.Y')).add(Date.DAY, day).clearTime();
		frm.dateMenu.setValue(Ext.util.Format.date(date1, 'd.m.Y')+' - '+Ext.util.Format.date(date2, 'd.m.Y'));
	},

	/**
	 * На день назад
	 */
	prevDay: function ()
	{
		this.stepDay(-1);
	},

	/**
	 * И на день вперед
	 */
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
	},
	currentWeek: function ()
	{
		var frm = this;
		var date1 = (Date.parseDate(frm.curDate, 'd.m.Y'));
		var dayOfWeek = (date1.getDay() + 6) % 7;
		date1 = date1.add(Date.DAY, -dayOfWeek).clearTime();
		var date2 = date1.add(Date.DAY, 6).clearTime();
    	frm.dateMenu.setValue(Ext.util.Format.date(date1, 'd.m.Y')+' - '+Ext.util.Format.date(date2, 'd.m.Y'));
	},
	currentMonth: function ()
	{
		var frm = this;
		var date1 = (Date.parseDate(frm.curDate, 'd.m.Y')).getFirstDateOfMonth();
		var date2 = date1.getLastDateOfMonth();
    	frm.dateMenu.setValue(Ext.util.Format.date(date1, 'd.m.Y')+' - '+Ext.util.Format.date(date2, 'd.m.Y'));
	},

	/**
	 * Маска при загрузке
	 */
	getLoadMask: function(MSG)
	{
		if (MSG)
		{
			delete(this.loadMask);
		}
		if (!this.loadMask)
		{
			this.loadMask = new Ext.LoadMask(this.getEl(), { msg: MSG });
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
					frm.userName = result.pmUser_Name;
					// Проставляем время и режим
					this.mode = 'day';
					frm.currentDay();
					frm.downloadPatientRecords('day');
					frm.getLoadMask().hide();
				}
			}
		});
	},
	deliverCard: function(data){
		//доставить карту
		var params = {};
		var record = this.getSelected();
		
		var PersonAmbulatCard_id = record.get('PersonAmbulatCard_id');
		if(!record || !PersonAmbulatCard_id){
			sw.swMsg.show(
				{
					buttons: Ext.Msg.OK,
					icon: Ext.Msg.ERROR,
					msg: langs('Запись не найдена. Попробуйте изменить фильтры.'),
					title: langs('Ошибка'),
					/*fn: function () {
						//...
					}.createDelegate(this)*/
				}
			);
			return false;
		};
		var cardNum = record.get('PersonAmbulatCard_Num').match(/<a[^>]+>(.+?)<\/a>/);

		/*if(this.arrayDeliverCard.length > 0 && this.arrayDeliverCard.indexOf(PersonAmbulatCard_id) >= 0){
			sw.swMsg.show(
				{
					buttons: Ext.Msg.OK,
					icon: Ext.Msg.WARNING,
					msg: langs('Амбулаторная карта №'+cardNum+' только что была отмечена к доставке'),
					title: langs('Ошибка'),
					fn: function () {
						//...
					}.createDelegate(this)
				}
			);
			//return false;
		}*/

		params.PersonAmbulatCardLocat_id = null;
		params.PersonAmbulatCard_id = PersonAmbulatCard_id;
		params.MedStaffFact_id = record.get('MedStaffFact_id');
		params.TimetableGraf_id = record.get('TimetableGraf_id');
		params.MedPersonal_id = record.get('MedPersonal_id');
		params.AmbulatCardRequestStatus_id = record.get('AmbulatCardRequestStatus_id');
		params.AmbulatCardRequest_id = record.get('AmbulatCardRequest_id');
		params.AmbulatCardLocatType_id = 2 //Местонахождения Сотрудник МО

		var CardLocationMedStaffFact_id = record.get('CardLocationMedStaffFact_id');
		if(CardLocationMedStaffFact_id == params.MedStaffFact_id){
			sw.swMsg.show(
				{
					buttons: Ext.Msg.OK,
					msg: langs('Амбулаторная карта уже отмечена, что находится у врача '+record.get('Doctor')+'.'),
					title: langs('Внимание'),
					fn: function () {
						return false;
					}.createDelegate(this)
				}
			);
			return false;
		}

		cardNum = (cardNum.length == 2) ? cardNum[1] : record.get('PersonAmbulatCard_Num');
		if(params.PersonAmbulatCard_id && params.MedStaffFact_id && params.TimetableGraf_id && params.MedPersonal_id){
			sw.swMsg.show({
				title: 'Внимание!',
				icon: Ext.Msg.INFO,
				minWidth: 500,
				//msg: "<b>Врач</b> " + record.get('Doctor') + " <br><b>Время приема:</b> " + record.get('TimetableGraf_Date') + "<br><br><div style='float: left'>ДОСТАВИТЬ КАРТУ №</div><b>" + record.get('PersonAmbulatCard_Num').replace(/<span.*?<\/span>/g,'') + "</b><div style='float: left'>&nbsp;НА ПРИЕМ?</div>",
				msg: "<b>Врач</b> " + record.get('Doctor') + " <br><b>Время приема:</b> " + record.get('TimetableGraf_Date') + "<br><b>Пациент:</b> "+record.get('Person_FIO')+"<br><br><div style='text-align: center'>ДОСТАВИТЬ КАРТУ №<b>" + cardNum + "</b>&nbsp;НА ПРИЕМ?</div>",
				buttons: {yes: 'Да', no: 'Нет'},
				fn: function(butn){
					if (butn == 'no'){
						return false;
					}else{
						Ext.Ajax.request({
							params: params,
							failure: function (result_form, action) {
								log('ошибка при доставке карты');
							},
							scope: {win: this, params: params},
							callback: function(options, success, response) {
								if (success && response.responseText != ''){
									var response_obj = Ext.util.JSON.decode(response.responseText);
									if(response_obj.PersonAmbulatCardLocat_id) {
										this.win.arrayDeliverCard.push(response_obj.PersonAmbulatCardLocat_id);
										
										if(this.win.FindByBarcode) {
											var indx = this.win.getStore().find('TimetableGraf_id', this.params.TimetableGraf_id);
											if(indx>=0){
												var rec = this.win.getStore().getAt(indx);
												if(rec){
													rec.set('RequestFromTheDoctor', 'Нет');
													rec.set('Location_Amb_Cards', 'Сотрудник МО. ' + rec.get('Doctor'));
													rec.set('CardAtTheReception', 'Да');
													rec.commit();
												}
											}
										}else{
											this.win.getStore().reload();
										}
									}
									/*
									showSysMsg(langs('Не забудьте отнести карты врачам'),'', 'info', {delay: 4000, bodyStyle: 'text-align:left; margin-left:7px; padding: 0px 0px 20px 20px;background:transparent'});
									*/
								}
								else{
									log('ошибка при доставке карты');
								}
							},
							url: '?c=PersonAmbulatCard&m=savePersonAmbulatDeliverCard'
						});
					}
				}.createDelegate(this)
			});
		}
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
						this.LpuBuilding_id = parseInt(response_obj[0]['LpuBuilding_id']);
					}else{
						this.LpuBuilding_id = 0;
					}
					//this.LpuBuilding_id = 1823; //test
					this.loadComboLpuBuilding();
					this.loadComboMedStaffFact();
				}
				else{
					log('error getLpuBuildingByMedServiceId');
				}
			}.createDelegate(this),
			url: '?c=PersonAmbulatCard&m=getLpuBuildingByMedServiceId'
		});
	},
	loadComboMedPersonal: function(){
		var params = {};
		var form = this.filtersPanel;
		var ComboMedPersonal = form.getForm().findField('MedPersonal_id');
		var ComboLpuBuilding = form.findById('PRLpuBuilding_id');
		params.Lpu_id = this.lpu_id;
		if(ComboLpuBuilding.getValue()) params.LpuBuilding_id = ComboLpuBuilding.getValue();
		ComboMedPersonal.getStore().removeAll();
		ComboMedPersonal.clearValue();		
		ComboMedPersonal.getStore().baseParams = params;
		ComboMedPersonal.getStore().load();
	},
	loadComboMedStaffFact: function(){
		var win = this;
		var form = this.filtersPanel;
        var MedStaffFact = form.getForm().findField('MedStaffFact_id');
        var ComboLpuBuilding = form.findById('PRLpuBuilding_id');
		this.medstafffact_filter_params = { 
			arrayLpuUnitType: [1, 11], //отделений с типом группы отделений «Поликлиника» или «Фельдшерско-акушерский пункт».
			//isDoctor:true, //Только врачи
			Lpu_id:getGlobalOptions()['lpu_id'], 
			LpuBuilding_id: ComboLpuBuilding.getValue()
			//withLpuRegionOnly:true 
		};

		if(swMedStaffFactGlobalStore.data.length == 0){
			MedStaffFact.getStore().load({
				callback:function(){
					var direct_store = setMedStaffFactGlobalStoreFilter(win.medstafffact_filter_params,MedStaffFact.getStore());
					MedStaffFact.getStore().loadData(getStoreRecords(direct_store));
				}
			});
		} else {
			setMedStaffFactGlobalStoreFilter(win.medstafffact_filter_params);
			MedStaffFact.getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
		}
	},
	loadComboLpuBuilding: function(){
		var params = {};
		var form = this.filtersPanel;
		var ComboLpuBuilding = form.findById('PRLpuBuilding_id');
		ComboLpuBuilding.enable();
		params.Lpu_id = this.lpu_id;
		if(this.LpuBuilding_id) params.LpuBuilding_id = this.LpuBuilding_id;
		ComboLpuBuilding.getStore().removeAll();
		ComboLpuBuilding.clearValue();		
		ComboLpuBuilding.getStore().baseParams = params;
		ComboLpuBuilding.getStore().load({
			callback: function(){
				var ComboLpuBuilding = this.filtersPanel.findById('PRLpuBuilding_id');
				if(this.LpuBuilding_id){
					ComboLpuBuilding.setValue(this.LpuBuilding_id);
					ComboLpuBuilding.disable();
				}else if(ComboLpuBuilding.getStore().getCount()>0){
					var LpuBuilding_id = ComboLpuBuilding.getStore().getAt(0).get('LpuBuilding_id');
					if(LpuBuilding_id) ComboLpuBuilding.setValue(LpuBuilding_id);
				}
				ComboLpuBuilding.fireEvent('change',ComboLpuBuilding,ComboLpuBuilding.getValue());
				this.getCurrentDateTime();
			}.createDelegate(this)
		})
	},
	openPersonAmbulatCardWindow: function(id, fio, TimetableGraf_id){
		//выбрать карту
		var win = this;
		if(!id) return false;
		var fio = fio || null;
		var TimetableGraf_id = TimetableGraf_id || null;

		var params = {
			Person_id: id,
			Person_FIO: fio,
			TimetableGraf_id: TimetableGraf_id
		};
		params.callback= function(params, id){
			this.setDisabled(false);
			this.timerLoadRecords();
			if(!params || !params.PersonAmbulatCard_id || !id) return false;
			var store = this.getStore();
			var index = store.indexOfId(id);
			var record = store.getAt(index);
			if(params.PersonAmbulatCard_id == record.get('PersonAmbulatCard_id')) return false;
			if(record){
				/*var span = ' <span title="выбрать другую амбулаторную карту пациента" class="button-in-the-cell" onClick="Ext.getCmp(\'swWorkplaceOfTheEmployeeWindow\').openPersonAmbulatCardWindow('+record.get('Person_id')+', \''+record.get('Person_FIO') +'\', '+record.get('TimetableGraf_id')+')">карты</span>';
				record.set('PersonAmbulatCard_id', params.PersonAmbulatCard_id);
				record.set('PersonAmbulatCard_Num', '<div style="float:left">'+params.PersonAmbulatCard_Num+'</div>' + span);*/

				record.set('PersonAmbulatCard_id', params.PersonAmbulatCard_id);
				var aHref = '<a href="#" title="выбрать другую амбулаторную карту пациента" onClick="Ext.getCmp(\'swWorkplaceOfTheEmployeeWindow\').openPersonAmbulatCardWindow('+record.get('Person_id')+', \''+record.get('Person_FIO') +'\', '+record.get('TimetableGraf_id')+')">' + params.PersonAmbulatCard_Num + '</a>';
				record.set('PersonAmbulatCard_Num', aHref);

				record.set('AttachmentLpuBuilding_Name', params.AttachmentLpuBuilding_Name);
				record.set('CardLocationMedStaffFact_id', params.CardLocationMedStaffFact_id);
				record.set('Location_Amb_Cards', params.MapLocation);
				if(params.CardLocationMedStaffFact_id && params.CardLocationMedStaffFact_id == record.get('MedStaffFact_id')){
					record.set('CardAtTheReception', 'да');
				}else{
					record.set('CardAtTheReception', 'нет');
				}
				record.commit();
			}
			
			//отмечаем на бирке выбранную пользователем амбулаторную карту
			var TimetableGraf_id = record.get('TimetableGraf_id');
			if(!TimetableGraf_id) return false;
			params.TimetableGraf_id = TimetableGraf_id;
			Ext.Ajax.request({
				params: params,
				failure: function (result_form, action) {
					log('Ошибка при выборе карты');
				},
				callback: function(options, success, response) {
					if (success && response.responseText != ''){
						var response_obj = Ext.util.JSON.decode(response.responseText);
					}
					else{
						log('Ошибка при выборе карты');
					}
				},
				url: '?c=PersonAmbulatCard&m=savePersonAmbulatCardInTimetableGraf'
			});
			//this.ownerCt.ownerCt.setDisabled(false);
		    //grid.getStore().reload();
		}.createDelegate(this)

		var formPersonAmbulatCardWindow = getWnd('swPersonAmbulatCardWindow');
		if(formPersonAmbulatCardWindow){
			if(formPersonAmbulatCardWindow.isVisible()){
				sw.swMsg.alert(langs('Сообщение'), langs('Форма "Амбулаторные карты" уже открыта'));
				return false;
			}

			this.setDisabled(true);
			this.stopTimerLoadRecords();
			formPersonAmbulatCardWindow.show(params);
		}
	},
	schedulePrint: function(action){
		var record = this.getSelected();
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
	formReset: function(){
		var form = this.filtersPanel.getForm();
		var comboLpuBuilding = form.findField('PR_LpuBuilding_id');
		form.reset();
		if(this.LpuBuilding_id){
			comboLpuBuilding.setValue(this.LpuBuilding_id);
		}
	},
	deliverCardsByBarcode: function(){
		/*---------test---------------------*/
		/*
		debugger;
		var test = {
			CARD_ID: 472795,
			MO_ID: 10011194,
			PERSON_ID: 59500904
		}
		this.getAmbulatFromScanner(test);
		return false;
		*/
		/*--------end test-----------------*/
		this.FindByBarcode = (this.FindByBarcode) ? false : true;
		if(this.FindByBarcode){
			if(this.ScanningBarcodeService.running_in_shape().scanning){
				sw.swMsg.alert(langs('Ошибка'), langs('Считывание штрих-кода уже запущено в другой форме !!!'));
				this.FindByBarcode = false;
				return false;
			}
			this.ScanningBarcodeService.start({form: this.id});
			this.arrayDeliverCard = [];
		}else{
			this.ScanningBarcodeService.stop();
			if(this.arrayDeliverCard.length>0){
				this.getStore().reload();
				showSysMsg(langs('Не забудьте отнести карты врачам'),'', 'info', {delay: 4000, bodyStyle: 'text-align:left; margin-left:7px; padding: 0px 0px 20px 20px;background:transparent'});
				this.arrayDeliverCard = [];
			}
		}
		this.disableForm(this.FindByBarcode);
	},
	disableForm: function(look, error){
		var error = error || false;
		var button = Ext.getCmp('id_deliver_cards_by_barcode');
		var elButton = button.getEl();
		var el = elButton.dom.getElementsByClassName('x-btn-text')[0];
		if(look){
			this.TopPanel.disable();
			this.MainActionsMenu.disable();
			this.ReceptionTableGrid.ViewActions["deliver_card"].disable();
			this.ReceptionTableGrid.ViewActions["print"].disable();
			this.ReceptionTableGrid.ViewActions["action_refresh"].disable();

			button.setText('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Завершить&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');
			el.style.cssText = 'background-image: url("img/icons/stop_red16.png");'
			el.insertBefore(this.divPopUpMessage, el.firstChild);
			//this.formReset();
		}else{
			this.TopPanel.enable();
			this.MainActionsMenu.enable();
			this.ReceptionTableGrid.ViewActions["deliver_card"].enable();
			this.ReceptionTableGrid.ViewActions["print"].enable();
			this.ReceptionTableGrid.ViewActions["action_refresh"].enable();

			button.setText('Доставить карты по штрих-коду');
			el.style.cssText = 'background-image: url("img/icons/idcard16.png");'
			this.divPopUpMessage.innerText = "Отсканируйте штрих-код амбулаторной карты.";
			this.divPopUpMessage.remove();
		}
		if(error){
			sw.swMsg.alert(langs('Ошибка'), langs('При считывании штрих-кода произошла ошибка !!!'));
		}
	},
	getAmbulatFromScanner: function(data)
	{
		//- данные со сканера расшифрованные сервисом
		if(data && !data.CARD_ID || !data.MO_ID || !data.PERSON_ID) {
			this.disableForm(false, true);
			return false;
		}
		this.divPopUpMessage.innerText = "Отсканируйте штрих-код амбулаторной карты.";

		// ищем в загруженных записях
		var indx = this.getStore().find('PersonAmbulatCard_id', data.CARD_ID);
		
		if(indx >=0 ){
			this.getGrid().getSelectionModel().selectRow(indx);
			var rec = this.getGrid().getSelectionModel().getSelected();
			if(rec){
				this.deliverCard();
			}else{
				this.getGrid().getSelectionModel().clearSelections();
				sw.swMsg.alert('Карта не найдена. Попробуйте изменить фильтр !!!');
			}
		}else{
			this.getGrid().getSelectionModel().clearSelections();
			sw.swMsg.alert('Карта не найдена. Попробуйте изменить фильтр !!!');
		}
		/*return false;	
		var dd = new Date();
		this.params.begDate = Ext.util.Format.date(dd, 'd.m.Y');
		this.params.endDate = Ext.util.Format.date(dd.addDays(7),'d.m.Y');

		this.params.limit = 100;
		this.params.start = 0;

		this.params.Lpu_id = data.MO_ID;
		this.params.attachmentLpuBuilding_id = this.LpuBuilding_id;
		this.params.Person_id = data.PERSON_ID;
		this.params.PersonAmbulatCard_id = data.CARD_ID;

		this.params.Person_FirName = null;
		this.params.Person_SecName = null;
		this.params.Person_SurName = null;
		this.params.Person_BirthDay = null;
        this.params.MedPersonal_id = null;
        this.params.MedStaffFact_id = null;
        this.params.LpuBuilding_id = null;
        this.params.CardAtTheReception = null;
		this.params.RequestFromTheDoctor = null;
		this.params.field_numberCard = null;

		this.getLoadMask(LOAD_WAIT).show();
		this.loadDataReceptionTableGrid(function(res){
			this.getLoadMask().hide();
			if(res && res.length>0){
				var dd = res[0].TimetableGraf_Date;
				if(dd){
					dd = Date.parseDate(dd, 'd.m.Y H:i');
					if(dd) this.dateMenu.setValue(Ext.util.Format.date(dd, 'd.m.Y')+' - '+Ext.util.Format.date(dd, 'd.m.Y'));
				}
				var form = this.filtersPanel.getForm();
				form.findField('CardAtTheReception').setValue('all');
				if(res[0].Person_Firname) form.findField('Person_Firname').setValue(res[0].Person_Firname);
				if(res[0].Person_Secname) form.findField('Person_Secname').setValue(res[0].Person_Secname);
				if(res[0].Person_Surname) form.findField('Person_Surname').setValue(res[0].Person_Surname);
				this.deliverCard(res);
			}else{
				showSysMsg(langs('Записей не найдено'),'', 'warning', {delay: 2000, bodyStyle: 'text-align:left; margin-left:7px; padding: 0px 0px 20px 20px;background:transparent'});
			}
		}.bind(this));
		this.disableForm(false);
		*/
	},
	initComponent: function() {
        var that = this;

		that.divPopUpMessage.style.cssText = 'display: block; background-color: #feff00b0;z-index: 1000;position: fixed; margin: 22px 0 0 -10px;border: #15428b 1px solid;border-radius: 1em;opacity: 0.9; word-wrap: break-word;font-size: 1.4em;font-weight: bold;padding: 15px;';
		that.divPopUpMessage.innerText = "Отсканируйте штрих-код амбулаторной карты.";

		this.ScanningBarcodeService = sw.Promed.ScanningBarcodeService.init({
			interval: 1000,
			form: this.id,
			callback: function(ambulatCardObject) {
				if(!ambulatCardObject) this.disableForm(false, true);
				Ext.Ajax.request({
					params: {code: ambulatCardObject},
					failure: function (result_form, action) {
						log('Error decodeBarCode');
						this.disableForm(false, true);
					},
					callback: function(options, success, response) {
						if (success && response.responseText != ''){
							var response_obj = Ext.util.JSON.decode(response.responseText);
							if(response_obj.success && response_obj.obj){
								this.getAmbulatFromScanner(response_obj.obj);
							}else{
								log('Ошибка при считывании штрих кода');
								if(this.divPopUpMessage.parentNode){
									this.divPopUpMessage.innerText = "Ошибка при считывании штрих-кода !!!"
								}
							}
						}
						else{
							log('Ошибка при считывании штрих кода');
							this.disableForm(false, true);
						}
					}.createDelegate(this),
					url: '?c=Barcode&m=decodeBarCode'
				});
			}.createDelegate(this)
		});

        this.MainActionsMenu = new Ext.Panel({
			region: 'west',
			title: ' ',
			collapsible: true,
			titleCollapse: true,
			animCollapse: false,
			split: true,
			floatable: false,
			width: 60,
			bodyStyle: 'background: white; padding: 5px;',
			minSize: 60,
			maxSize: 120,
        	items: [
				{
					text: '',
					tooltip: langs('Картохранилище'),
					cls: 'x-btn-large',
					iconCls: 'registry_k32',
					handler: function()
					{
						var formCardStorageWindow = getWnd('swCardStorageWindow');
						if(formCardStorageWindow .isVisible()){
							sw.swMsg.alert(langs('Сообщение'), langs('Окно картохранилища уже открыто'));
							return false;
						}
						getWnd('swCardStorageWindow').show({
							LpuBuilding_id: this.LpuBuilding_id
						});
					}.createDelegate(this),
					xtype: 'button'
				}, {
					text: '',
					tooltip: langs('Печать ТАП'),
					cls: 'x-btn-large',
					iconCls: 'print16',
					handler: function()
					{
						var formStorageCardWindow = getWnd('swStorageCardWindow');
						if(formStorageCardWindow  .isVisible()){
							sw.swMsg.alert(langs('Сообщение'), langs('Окно Печать ТАП уже открыто'));
							return false;
						}
						formStorageCardWindow .show({LpuBuilding_id: this.LpuBuilding_id});
					}.createDelegate(this),
					xtype: 'button'
				},
			]
        });

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
			var form = Ext.getCmp('swWorkplaceOfTheEmployeeWindow');
			if (e.getKey() == Ext.EventObject.ENTER)
			{
				e.stopEvent();
				form.downloadPatientRecords('period');
			}
		});
		this.dateMenu.addListener('select',function () 
		{
			// Читаем расписание за период
			var form = Ext.getCmp('swWorkplaceOfTheEmployeeWindow');
			form.downloadPatientRecords('period');
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
				this.downloadPatientRecords('range');
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
				this.downloadPatientRecords('range');
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
				this.downloadPatientRecords('day');
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
				this.downloadPatientRecords('week');
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
				this.downloadPatientRecords('month');
			}.createDelegate(this)
		});

		this.WindowToolbar = new Ext.Toolbar(
		{
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
				this.formActions.next, 
				{
					xtype: 'tbfill',
				},
				this.formActions.day, 
				this.formActions.week, 
				this.formActions.month
			]
		});

		this.filtersPanel = new Ext.FormPanel({
			xtype: 'form',
			labelAlign: 'right',
			labelWidth: 50,
			items:
			[{
				listeners: {
					collapse: function(p) {
						this.doLayout();
					}.createDelegate(this),
					expand: function(p) {
						this.doLayout();
					}.createDelegate(this)
				},
				xtype: 'fieldset',
				style: 'margin: 5px 0 0 0',
				height: 140,
				title: langs('Поиск'),
				collapsible: true,
				layout: 'column',
				items:
				[
				{
					layout: 'form',
					labelWidth: 100,
					items:
					[
						{
							fieldLabel: langs('Подразделение'),
							listWidth: 400,
							hiddenName:'PR_LpuBuilding_id',
							id: 'PRLpuBuilding_id',
							width: 220,
							//xtype: 'swlpubuildingcombo',
							xtype: 'swlpubuildingglobalcombo',
							listeners: {
								'change': function(combo, newValue, oldValue) {
									//this.loadComboMedPersonal();
									this.loadComboMedStaffFact();
								}.createDelegate(this)
							}
						},
						{
							fieldLabel:langs('Врач'),
							hiddenName:'MedStaffFact_id',
							xtype:'swmedstafffactglobalcombo',
							width:220,
							listWidth:700,
							tpl: new Ext.XTemplate(
								'<tpl for="."><div class="x-combo-list-item">',
								'<table style="border: 0;">',
								'<td style="width: 45px;"><font color="red">{MedPersonal_TabCode}&nbsp;</font></td>',
								'<td>',
									'<div style="font-weight: bold;">{MedPersonal_Fio}&nbsp;{[Ext.isEmpty(values.LpuSection_Name)?"":values.LpuSection_Name]}</div>',
									'<div style="font-size: 10px;">{PostMed_Name}</div>',
								'</td>',
								'</tr></table>',
								'</div></tpl>'
							),
							anchor:'auto'
						},
						/*{
							fieldLabel: langs('Врач'),
							listWidth: 400,
							hiddenName: 'MedPersonal_id',
							xtype: 'swmedpersonalcombo',
							width: 220,
							allowBlank: true
						}*/
                    ]
				}, 
				{
					layout: 'form',
					labelWidth: 110,
					items:
					[{
						xtype: 'textfieldpmw',
						width: 120,
						id: 'hvlwSearch_SurName',
						fieldLabel: langs('Фамилия'),
						listeners:
						{
							'keydown': function (inp, e)
							{
								if (e.getKey() == Ext.EventObject.ENTER)
								{
									e.stopEvent();
									this.downloadPatientRecords();
								}
							}.createDelegate(this)
						},
						name: 'Person_Surname'
					}, {
						xtype: 'textfieldpmw',
						width: 120,
						id: 'hvlwSearch_FirName',
						fieldLabel: langs('Имя'),
						listeners:
						{
							'keydown': function (inp, e)
							{
								if (e.getKey() == Ext.EventObject.ENTER)
								{
									e.stopEvent();
									this.downloadPatientRecords();
								}
							}.createDelegate(this)
						},
						name: 'Person_Firname'
					}, {
						xtype: 'textfieldpmw',
						width: 120,
						id: 'hvlwSearch_SecName',
						fieldLabel: langs('Отчество'),
						listeners:
						{
							'keydown': function (inp, e)
							{
								if (e.getKey() == Ext.EventObject.ENTER)
								{
									e.stopEvent();
									this.downloadPatientRecords();
								}
							}.createDelegate(this)
						},
						name: 'Person_Secname'
					}, {
						xtype: 'swdatefield',
						format: 'd.m.Y',
						plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
						id: 'hvlwSearch_BirthDay',
						fieldLabel: langs('Дата рождения'),
						listeners:
						{
							'keydown': function (inp, e)
							{
								if (e.getKey() == Ext.EventObject.ENTER)
								{
									e.stopEvent();
									this.downloadPatientRecords();
								}
							}.createDelegate(this)
						},
						name: 'Person_Birthday'
					}]
				}, {
					layout: 'form',
					labelWidth: 160,
					items:
					[
						{
							allowBlank: false,
							valueField: 'header',
							comboData: [
								['not','Нет'],
								['yes','Да'],
								['all','Все']
							],
							comboFields: [
								{name: 'header', type:'string'},
								{name: 'header_Name', type:'string'}
							],
							value: 'not',
							fieldLabel: langs('Карта на приёме?'),
							width: 130,
							xtype: 'swstoreinconfigcombo',
							hiddenName: 'CardAtTheReception',
							name: 'CardAtTheReception'
						},
						{
							allowBlank: false,
							valueField: 'header',
							comboData: [
								['not','Нет'],
								['yes','Да'],
								['all','Все']
							],
							comboFields: [
								{name: 'header', type:'string'},
								{name: 'header_Name', type:'string'}
							],
							value: 'all',
							fieldLabel: langs('Запрос от врача приёма'),
							width: 130,
							xtype: 'swstoreinconfigcombo',
							hiddenName: 'RequestFromTheDoctor',
							name: 'RequestFromTheDoctor'
						},
						{
							xtype: 'textfieldpmw',
							width: 120,
							id: 'field_numberCard',
							fieldLabel: langs('№ амб. карты '),
							listeners:
							{
								'keydown': function (inp, e)
								{
									if (e.getKey() == Ext.EventObject.ENTER)
									{
										e.stopEvent();
										this.downloadPatientRecords();
									}
								}.createDelegate(this)
							},
							name: 'field_numberCard'
						}
					]
				},
				{
					layout: 'form',
					style: "padding-top: 40px; padding-left: 40px",
					items:
					[{
						style: "padding-left: 20px",
						xtype: 'button',
						id: 'mpwpBtnSearch',
						text: langs('Найти'),
						iconCls: 'search16',
						handler: function()
						{
							var form = this.filtersPanel.getForm();
							if(form.findField('PR_LpuBuilding_id').getValue()){
								this.downloadPatientRecords();
							}else{
								sw.swMsg.alert('Внимание', 'Не выбрано подразделение для поиска');
							}
						}.createDelegate(this)
					}]
				}, {
					layout: 'form',
					style: "padding-top: 40px",
					items:
					[{
						style: "padding-left: 20px",
						xtype: 'button',
						id: 'hvlwBtnClear',
						text: langs('Сброс'),
						iconCls: 'resetsearch16',
						handler: function()
						{
							this.formReset();
							this.downloadPatientRecords();
						}.createDelegate(this)
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
			tbar: this.WindowToolbar,
			items:
			[
				this.filtersPanel
			]
		});
		
		this.ReceptionTableGrid = new sw.Promed.ViewFrame({	
			tbActions: true,
			actions: [
				{ name: 'action_add', hidden: true },
				{ name: 'action_edit', hidden: true },
				{ name: 'action_view', hidden: true },
				{ name: 'action_delete', hidden: true },
				{ name: 'action_print', hidden: true },
				{ name: 'action_refresh'},
				{
					name: 'print',
					text:'Печать',
					tooltip: 'Печать',
					disabled: false,
					iconCls : 'x-btn-text',
					icon: 'img/icons/print16.png',
					handler: function() {},
					menu: [{
						disabled: false,
						handler: function() {
							this.schedulePrint('row');
						}.createDelegate(this),
						iconCls: 'print16',
						name: 'print_selected_line',
						text: 'Печать',
						tooltip: 'печать выбранной строки таблицы'
					},{
						disabled: false,
						name: 'print_all_table',
						tooltip: 'печать всей таблицы',
						text: 'Печать всего списка',
						iconCls: 'print16',
						handler: function() {
							this.schedulePrint();
						}.createDelegate(this)
					}]
				},
				{
					disabled: false,
					name: 'deliver_card',
					tooltip: 'Доставить карту',
					text: 'Доставить карту',
					icon: 'img/icons/idcard16.png',
					handler: function() {
						this.deliverCard();
					}.createDelegate(this)
				},
				{
					disabled: false,
					name: 'deliver_cards_by_barcode',
					id: 'deliver_cards_by_barcode',
					xtype: 'button',
					tooltip: 'Доставить карты по штрих-коду',
					text: 'Доставить карты по штрих-коду',
					icon: 'img/icons/idcard16.png',
					handler: function() {
						this.deliverCardsByBarcode();
					}.createDelegate(this)
				}
			],
			autoLoadData: false,
			//dataUrl: C_HOMEVISIT_LIST,
			dataUrl: '?c=EvnVizit&m=loadReceptionTableGrid',
			//stateful: true,
			id: 'ReceptionTable',
			onLoadData: function(sm, index, record) {
				if(!sm) {
					this.ViewGridStore.removeAll();
					return false;
				}
				this.ViewGridStore.each(function(rec,idx,count) {
					var personCardNum = rec.get('PersonAmbulatCard_Num');
					personCardNum = (personCardNum) ? personCardNum : '&nbsp;';
					/*var	naHref = (personCardNum) ? '<div style="float:left;">' + personCardNum + '</div>' : '';					
					var span = ' <span title="выбрать другую амбулаторную карту пациента" class="button-in-the-cell" onClick="Ext.getCmp(\'swWorkplaceOfTheEmployeeWindow\').openPersonAmbulatCardWindow('+rec.get('Person_id')+', \''+rec.get('Person_FIO') +'\', '+rec.get('TimetableGraf_id')+')">карты</span>';						
					rec.set('PersonAmbulatCard_Num', naHref+span);*/
					var aHref = '<a href="#" onClick="Ext.getCmp(\'swWorkplaceOfTheEmployeeWindow\').openPersonAmbulatCardWindow('+rec.get('Person_id')+', \''+rec.get('Person_FIO') +'\', '+rec.get('TimetableGraf_id')+')">' + personCardNum + '</a>';
					rec.set('PersonAmbulatCard_Num', aHref);

					var ambulatCardRequestStatus = parseInt(rec.get('AmbulatCardRequestStatus_id'));
					var ambulatCardRequestField = '';
					if(ambulatCardRequestStatus == 1){
						ambulatCardRequestField = '<img src="../img/grid/checkednonborder-red.gif" width="10" height="10" alt="lorem"> Да';
					}else{
						ambulatCardRequestField = ' Нет'
					}
					rec.set('RequestFromTheDoctor', ambulatCardRequestField);
					rec.commit();
				}.createDelegate(this));
			},
			onRowSelect: function(sm, index, record) {
				var actionDeliverCard = this.ReceptionTableGrid.ViewActions["deliver_card"];
				if(!sm || !record.get('PersonAmbulatCard_id')) {
					actionDeliverCard.disable();
				}else{
					actionDeliverCard.enable();
				}
			}.createDelegate(this),			
			stringfields: [
				//Поля для отображение в гриде
				{ name: 'TimetableGraf_id', type: 'int', header: 'ID', key: true },
				{ name: 'Person_id', type: 'int', hidden: true },
				{ name: 'AmbulatCardRequestStatus_id', type: 'int', hidden: true },
				{ name: 'AmbulatCardRequest_id', type: 'int', hidden: true },
				{ name: 'MedStaffFact_id', type: 'int', hidden: true },
				{ name: 'CardLocationMedStaffFact_id', type: 'int', hidden: true },
				{ name: 'MedPersonal_id', type: 'int', hidden: true },
				{ name: 'PersonAmbulatCard_id', type: 'int', hidden: true },
				{ name: 'LpuRegion_id', type: 'int', hidden: true },
				{ name: 'CardAtTheReception', type: 'string', header: langs('Карта на приёме?'), /*width: 100*/ },
				{ name: 'RequestFromTheDoctor', type: 'string', header: langs('Запрос от врача приёма'), width: 60 },			
				{ name: 'TimetableGraf_Date', type: 'string', header: langs('Время приёма'), /*width: 100*/ },
				{ name: 'LpuBuilding_Name', type: 'string', header: langs('подразделение'), /*width: 100*/ },
				{ name: 'LpuSection_Name', type: 'string', header: langs('отделение'),/* width: 100*/ },
				{ name: 'Doctor', type: 'string', header: langs('ФИО врача'), width: 200},
				{ name: 'Person_FIO', type: 'string', header: langs('ФИО пациента'), width: 200},
				{ name: 'Person_Birthday', type: 'date', header: langs('Дата рождения пациента'), renderer: Ext.util.Format.dateRenderer('d.m.Y') },
				{ name: 'MainLpu_Nick', type: 'string', header: langs('МО прикрепления (осн.)'), width: 100 },
				{ name: 'LpuRegion_Name', type: 'string', header: langs('участок'), width: 100, /*id: 'autoexpand'*/ },
				{ name: 'GinLpu_Nick', type: 'string', header: langs('МО прикрепления (гинек.)'), width: 80},
				{ name: 'StomLpu_Nick', type: 'string', header: langs('МО прикрепления (стомат.)'), width: 60},
				{ name: 'PersonAmbulatCard_Num', type: 'string', header: langs('№ амб. карты'), width: 150},
				{ name: 'AttachmentLpuBuilding_Name', type: 'string', header: langs('Подразделение прикрепления карты'), width: 200},
				{ name: 'Location_Amb_Cards', type: 'string', header: langs('Местонахождение амб. карты'), width: 300 },
				{ name: 'TimetableGraf_insDT', type: 'date', header: langs('Когда записан'), renderer: Ext.util.Format.dateRenderer('d.m.Y') },
				{ name: 'TimetableType_Name', type: 'string', header: langs('Тип записи'), width: 120 }
			],
			title: null,
			paging: true,
			pageSize: 100,
			root: 'data',
			totalProperty: 'totalCount'
		});
		
	    Ext.apply(this, {
			autoScroll: true,
			buttons:
			[
			{
				text: '-'
			},
			{
				text: BTN_FRMHELP,
				iconCls: 'help16',
				handler: function(button, event) {
					ShowHelp(WND_WORKPLACE_OF_THE_EMPLOYEE);
				}.createDelegate(this)
			},
			{
				iconCls: 'cancel16',
				text: BTN_FRMCLOSE,
				handler: function() { this.hide(); }.createDelegate(this)
			}],
			layout: 'border',
			items: 
			[
				this.TopPanel,
				{
					layout: 'border',
					region: 'center',
					id: 'hvlwMainPanel',
					items:
					[
						this.ReceptionTableGrid,
						this.MainActionsMenu
					]
				}
			]
	    });
	    sw.Promed.swWorkplaceOfTheEmployeeWindow.superclass.initComponent.apply(this, arguments);

    },

	show: function()
	{
		sw.Promed.swWorkplaceOfTheEmployeeWindow.superclass.show.apply(this, arguments);
		
		var win = this;
		win.lpu_id = getGlobalOptions()['lpu_id'];

		this.type='';
		if(arguments[0]){
			if(arguments[0].type){
				this.type=arguments[0].type;
			}
		}

		if(!Ext.isEmpty(arguments[0].LpuBuilding_id)){
			this.LpuBuilding_id=arguments[0].LpuBuilding_id;
			this.loadComboLpuBuilding();
			//this.loadComboMedStaffFact();
			this.getCurrentDateTime();
		}else{
			this.getLpuBuildingByMedServiceId(function(){
				this.getCurrentDateTime();
			}.createDelegate(this));
		}

		this.disableForm(false);
	}
});