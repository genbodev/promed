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
	getDataFromUec: function(uec_data, person_data) {
		var form = this;
		var grid = form.ScheduleGrid;
		var f = false;
		grid.getStore().each(function(record) {
			if (record.get('Person_id') == person_data.Person_id) {
				log('Найден в гриде');

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
			log('Не найден в гриде');
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
				log('Найден в гриде');

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
			log('Не найден в гриде');
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
			Ext.Msg.alert('Ошибка', 'Список расписаний не найден!');
			return false;
		}
		else if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('TimetableGraf_id') )
		{
			Ext.Msg.alert('Ошибка', 'Не выбрана запись!');
			return false;
		}
		var record = grid.getSelectionModel().getSelected();
	
		var id_salt = Math.random();
		var win_id = 'print_pac_list' + Math.floor(id_salt * 10000);
		window.open('/?c=TimetableGraf&m=printPacList&Day=' + record.get('TimetableGraf_Date').dateFormat('d.m.Y') + '&MedStaffFact_id=' + record.get('MedStaffFact_id'), win_id);
		
	},
	scheduleLoad: function(mode)
	{
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
		/*
		params.callback = function() {
			this.scheduleCollapseDates();
		}.createDelegate(this);
		*/
		
		//var frm = this;
		this.getGrid().loadStore(params);
		/*
		this.getGrid().getStore().load(
		{
			params: params,
			callback: function(store, record, options)
			{
				// Проверки на загруженные данные и прочее 
			}
		}
		);
		*/
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
            data.addDirection = 0;
            data.From_MedStaffFact_id = this.userMedStaffFact.MedStaffFact_id;
            this.getLoadMask('Подождите, сохраняется запись...').show();
            Ext.Ajax.request({
                url: !data.EvnQueue_id ? C_TTG_APPLY : C_QUEUE_APPLY,
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
                                    title: 'Внимание!',
                                    buttons: Ext.Msg.YESNO,
                                    fn: function(buttonId, text, obj)
                                    {
                                        if ('yes' == buttonId)
                                        {
                                            var form = Ext.getCmp('swMPWorkPlaceWindow');
                                            var data = {
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
			MedPersonal_id: getGlobalOptions().medpersonal_id
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
					Ext.Msg.alert('Сообщение', 'Врач не имеет права на выписку рецептов по ЛЛО.');
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
						msg: 'На следующий период '+ (row.next_DrugRequestPeriod) +' заявка по врачу '+ (row.MedPersonal_Fin) +' не найдена. Открыть последнюю имеющуюся или создать?',
						title: 'Заявка не найдена',
						buttons: {yes: 'Открыть', no: 'Создать', cancel: 'Отмена'},
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
									Ext.Msg.alert('Сообщение', 'У врача нет ни одной заявки на лекарственные средства.');
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
								sw.swMsg.alert('Ошибка', 'Запись невозможна в связи со смертью пациента');
							}
						});
					}
				}.createDelegate(this)
			});
		}
		else
			Ext.Msg.alert("Ошибка", response.ErrorMessage);

	},
	scheduleNew: function()
	{
		// Добавление пациента вне записи
		if (getWnd('swPersonSearchWindow').isVisible())
		{
			Ext.Msg.alert('Сообщение', 'Окно поиска человека уже открыто');
			//getWnd('swPersonSearchWindow').hide();
			return false;
		}
		sw.Applets.uec.stopUecReader();
		sw.Applets.bdz.stopBdzReader();
		sw.Applets.BarcodeScaner.stopBarcodeScaner();
		getWnd('swPersonSearchWindow').show(
		{
			onClose: function() 
			{
				// do nothing
				if ( this.getGrid().getSelectionModel().getSelected() ) {
					this.getGrid().getView().focusRow(this.getGrid().getStore().indexOf(this.getGrid().getSelectionModel().getSelected()));
				}
				else {
					//grid.getView().focusRow(0);
					this.getGrid().getSelectionModel().selectFirstRow();
				}
			}.createDelegate(this),
			onSelect: function(pdata) 
			{
				var onIsLiving = function(){
					getWnd('swPersonSearchWindow').hide();
					// проверка - записан ли такой пациент на сегодня по этому профилю к этому врачу (на это рабочее место врача)
					Ext.Ajax.request({
						params: {LpuUnitType_SysNick: 'polka',Person_id: pdata.Person_id, LpuSection_id: this.userMedStaffFact.LpuSection_id, MedStaffFact_id: this.userMedStaffFact.MedStaffFact_id},
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
							sw.swMsg.alert('Ошибка', 'Запись невозможна в связи со смертью пациента');
						}
					});
				}
			}.createDelegate(this),
			needUecIdentification: true,
			searchMode: 'all'
		});
	},
	
	scheduleAdd: function()
	{
		
		if (this.formMode=='send')
		{
			sw.swMsg.show(
			{
				icon: Ext.MessageBox.QUESTION,
				msg: 'Записать пациента на выбранное время?',
				title: 'Вопрос',
				buttons: Ext.Msg.YESNO,
				fn: function(buttonId, text, obj)
				{
					if ('yes' == buttonId)
					{
						var form = Ext.getCmp('swMPWorkPlaceWindow');
						
						var data = 
						{
							Person_id: form.saveParams.Person_id,
							Server_id: form.saveParams.Server_id,
							PersonEvn_id: form.saveParams.PersonEvn_id
						}
						form.scheduleSave(data);
					}
				}
			});
		}
		else 
		{
			if (getWnd('swPersonSearchWindow').isVisible())
			{
				Ext.Msg.alert('Сообщение', 'Окно поиска человека уже открыто');
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
							sw.swMsg.alert('Ошибка', 'Запись невозможна в связи со смертью пациента');
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
		var grid = this.getGrid();
		if (!grid || !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('TimetableGraf_id')) {
			Ext.Msg.alert('Ошибка', 'Не выбрана запись!');
			return false;
		}
		var record = grid.getSelectionModel().getSelected();
		getWnd('swMPQueueWindow').show({
			mode: 'select',
			params: record.data,
			userMedStaffFact: this.userMedStaffFact,
			onSelect: function(data) {
				// Действия после сохранения ЭН при записи из очереди
				getWnd('swMPQueueWindow').hide();
				this.scheduleSave(data);
			}.createDelegate(this)
		});
	},
	scheduleEdit: function()
	{
		alert('редактирование бирки');
	},
	scheduleOpen: function()
	{
		var form = this;
		var grid = form.getGrid();
		if (!grid)
		{
			Ext.Msg.alert('Ошибка', 'Список расписаний не найден!');
			return false;
		}
		else if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('TimetableGraf_id') )
		{
			Ext.Msg.alert('Ошибка', 'Не выбрана запись!');
			return false;
		}
		var record = grid.getSelectionModel().getSelected();
		
		var isMyOwnRecord = false;
		if (record.get('pmUser_updId') == getGlobalOptions().pmuser_id) {
			isMyOwnRecord = true;
		}

		var params = {
			Person_id: record.get('Person_id'),
			Server_id: record.get('Server_id'),
			PersonEvn_id: record.get('PersonEvn_id'),
			userMedStaffFact: this.userMedStaffFact,
			TimetableGraf_id: record.get('TimetableGraf_id'),
			mode: 'workplace',
			isMyOwnRecord: isMyOwnRecord,
			ARMType: this.ARMType,
			callback: function()
			{
				this.scheduleRefresh();
			}.createDelegate(this)
		};

		checkPersonPhoneVerification({
			Person_id: params.Person_id,
			MedStaffFact_id: params.userMedStaffFact.MedStaffFact_id,
			callback: function(){getWnd('swPersonEmkWindow').show(params)}
		});
	},
	scheduleCopy: function()
	{
		alert('Ctrl+C');
	},
	schedulePaste: function()
	{
		alert('Ctrl+V');
	},
	scheduleDelete:function()
	{
		var form = this;
		var grid = form.getGrid();
		if (!grid)
		{
			Ext.Msg.alert('Ошибка', 'Список расписаний не найден!');
			return false;
		}
		else if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('TimetableGraf_id') )
		{
			Ext.Msg.alert('Ошибка', 'Не выбрана запись!');
			return false;
		}
		var id = grid.getSelectionModel().getSelected().data['TimetableGraf_id'];
		form.savePosition();
		if (grid.getSelectionModel().getSelected().data['EvnDirection_id'] != undefined
			&& grid.getSelectionModel().getSelected().data['EvnDirection_id'] != '' ) {
			getWnd('swSelectDirFailTypeWindow').show({LpuUnitType_SysNick: 'polka', time_id: id, onClear: function() {grid.getStore().reload();}});
		} else {
			sw.swMsg.show({
				icon: Ext.MessageBox.QUESTION,
				msg: 'Вы хотите освободить время приема?',
				title: 'Вопрос',
				buttons: Ext.Msg.YESNO,
				fn: function(buttonId, text, obj)
				{
					if ('yes' == buttonId)
					{
						submitClearTime(
							{
								id: id,
								type: 'polka',
								DirFailType_id: null,
								EvnComment_Comment: null
							},
							function(response) {
								if (response.responseText)
								{
									var answer = Ext.util.JSON.decode(response.responseText);
									if (!answer.success)
									{
										if (answer.Error_Code)
										{
											Ext.Msg.alert('Ошибка #'+answer.Error_Code, answer.Error_Message);
										}
										else
											if (!answer.Error_Msg) 
											{
												Ext.Msg.alert('Ошибка', 'При выполнении операции освобождения времени приема<br/>произошла ошибка: <b>освобождение приема невозможно</b>!');
											}
									}
									else
									{
										grid.getStore().reload();
									}
								}
								else
								{
									Ext.Msg.alert('Ошибка', 'При выполнении операции освобождения времени приема<br/>произошла ошибка: <b>отсутствует ответ сервера</b>.');
								}
							}.createDelegate(this),
							null
						);
					}
					else
					{
						if (grid.getStore().getCount()>0)
						{
							grid.getView().focusRow(0);
						}
					}
				}
			});
		}
		
	},
	scheduleRefresh:function()
	{
		var params = new Object();
		params.begDate = Ext.util.Format.date(this.dateMenu.getValue1(), 'd.m.Y');
		params.endDate = Ext.util.Format.date(this.dateMenu.getValue2(), 'd.m.Y');
		params.MedStaffFact_id = this.userMedStaffFact.MedStaffFact_id;
		this.getGrid().loadStore(params);
	},
	schedulePrint:function()
	{
		Ext.ux.GridPrinter.print(this.getGrid());
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
		// frm.dateMenu.fireEvent("select", frm.dateMenu);
	},
	currentMonth: function ()
	{
		var frm = this;
		var date1 = (Date.parseDate(frm.curDate, 'd.m.Y')).getFirstDateOfMonth();
		var date2 = date1.getLastDateOfMonth();
    frm.dateMenu.setValue(Ext.util.Format.date(date1, 'd.m.Y')+' - '+Ext.util.Format.date(date2, 'd.m.Y'));
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
	listeners:
	{
		beforeshow: function()
		{
			//log(getGlobalOptions().medstafffact);
			if ((!getGlobalOptions().medstafffact) || (getGlobalOptions().medstafffact.length==0))
			{
				Ext.Msg.alert('Сообщение', 'Текущий логин не соотнесен с врачом. <br/>Доступ к интерфейсу врача невозможен.');
				return false;
			}
		},
		'beforehide': function()
		{
			sw.Applets.uec.stopUecReader();
			sw.Applets.bdz.stopBdzReader();
			sw.Applets.BarcodeScaner.stopBarcodeScaner();
		}

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
		this.linktitle = ' <a id="header_link_'+this.id+'" href="#" onClick="Ext.getCmp(&quot;swMPWorkPlaceWindow&quot;).showMenu(&quot;'+'header_link_'+this.id+'&quot;);">'+'Текущее название АРМа и отделение'+'</a>';
	},
	*/
	show: function()
	{
		sw.Promed.swMPWorkPlaceWindow.superclass.show.apply(this, arguments);



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
		// Форма может открываться с разных мест, поэтому если она откывается для того, чтобы записать пациента к другому врачу
		// то предварительно надо запомнить параметры.

		sw.Applets.uec.startUecReader({callback: this.getDataFromUec.createDelegate(this)});
		sw.Applets.bdz.startBdzReader({callback: this.getDataFromBdz.createDelegate(this)});
		sw.Applets.BarcodeScaner.startBarcodeScaner({callback: this.getDataFromUec.createDelegate(this)});

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
		}
		else 
		{
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
						}
						else {
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

		// Переключатель
		this.syncSize();
	},

	initComponent: function()
	{	
		// Actions
		var Actions =
		[
			{name:'open_emk', text:'<b>Открыть ЭМК</b>', tooltip: 'Открыть электронную медицинскую карту пациента', iconCls : 'open16', handler: function() {this.scheduleOpen();}.createDelegate(this)},
			{name:'open', text:'<b>Открыть</b>', tooltip: 'Открыть', iconCls : 'x-btn-text', icon: 'img/icons/open16.png', handler: function() {this.scheduleOpen()}.createDelegate(this)},
			{name:'reception_soc', text:'Принять по соц. карте', hidden: !(getGlobalOptions().region && getGlobalOptions().region.nick == 'ufa'), tooltip: 'Принять по соц. карте', iconCls : 'copy16', handler: function() {this.receptionBySocCard()}.createDelegate(this)},
			{name:'create', text:'Принять без записи', tooltip: 'Пациент без записи', iconCls : 'copy16', handler: function() {this.scheduleNew()}.createDelegate(this)},
			{name:'add', text:'Записать пациента', tooltip: 'Записать пациента', iconCls : 'x-btn-text', icon: 'img/icons/add16.png', handler: function() {this.scheduleAdd()}.createDelegate(this)},
			{name:'queue', text:'Записать из очереди', tooltip: 'Записать из очереди', iconCls : 'x-btn-text', icon: 'img/icons/add16.png', handler: function() {this.sheduleAddFromQueue()}.createDelegate(this)},
			{name:'edit', text:BTN_GRIDEDIT, tooltip: BTN_GRIDEDIT, iconCls : 'x-btn-text', icon: 'img/icons/edit16.png', handler: function() {this.scheduleEdit()}.createDelegate(this)},
			{name:'copy', text:BTN_GRIDCOPY, tooltip: BTN_GRIDCOPY, hidden: true, iconCls : 'x-btn-text', icon: 'img/icons/copy16.png', handler: function() {this.scheduleCopy()}.createDelegate(this)},
			{name:'paste', text:BTN_GRIDPASTE, tooltip: BTN_GRIDPASTE, hidden: true, iconCls : 'x-btn-text', /*icon: 'img/icons/paste16.png',*/ handler: function() {this.schedulePaste()}.createDelegate(this)},
			{name:'del', text:'Освободить запись', tooltip: 'Снять запись пациента', iconCls : 'x-btn-text', icon: 'img/icons/delete16.png', handler: function() {this.scheduleDelete()}.createDelegate(this)},
			{name:'refresh', text:BTN_GRIDREFR, tooltip: BTN_GRIDREFR, iconCls : 'x-btn-text', icon: 'img/icons/refresh16.png', handler: function() {this.scheduleRefresh()}.createDelegate(this)},			
			{name:'actions', key: 'actions', text:'Действия', menu: [
				new Ext.Action({name:'collapse_all', text:'Свернуть все', tooltip: 'Свернуть все', handler: function() {this.scheduleCollapseDates()}.createDelegate(this)}),
				new Ext.Action({name:'expand_all', text:'Развернуть все', tooltip: 'Развернуть все', handler: function() {this.scheduleExpandDates()}.createDelegate(this)})
			], tooltip: 'Действия', iconCls : 'x-btn-text', icon: 'img/icons/actions16.png', handler: function() {}},
			{name:'printPacList', iconCls: 'print16', text: 'Печать списка пациентов', tooltip : "Печать списка пациентов", hidden: (getGlobalOptions().region.nick != 'ufa'), handler: function () {this.printPacList();}.createDelegate(this)},
			{name:'print', text:BTN_GRIDPRINT, tooltip: BTN_GRIDPRINT, iconCls : 'x-btn-text', icon: 'img/icons/print16.png', handler: function() {this.schedulePrint()}.createDelegate(this)}
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
				this.gridActions.del,
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
		{
			name: 'EvnDirection_id'
		},
		{
			name: 'IsEvnDirection'
		}]);
        
		this.gridStore = new Ext.data.GroupingStore(
		{
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
								if (!form.gridActions.del.initialConfig.initialDisabled)
									form.gridActions.del.setDisabled(false);
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
						}
						else
						{
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
					form.gridActions.del.setDisabled(true);
				},
				beforeload: function()
				{

				}
			}
		});
		this.dateMenu = new Ext.form.DateRangeField(
		{
			width: 150,
			testId: 'wnd_workplace_dateMenu',
			fieldLabel: 'Период',
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
			}
		});
		this.dateMenu.addListener('select',function () 
		{
			// Читаем расписание за период
			var form = Ext.getCmp('swMPWorkPlaceWindow');
			form.scheduleLoad('period');
		});
		
		this.formActions = new Array();
		this.formActions.selectDate = new Ext.Action(
		{
			text: ''
		});
		this.formActions.prev = new Ext.Action(
		{
			text: 'Предыдущий',
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
			text: 'Следующий',
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
			text: 'День',
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
			text: 'Неделя',
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
			text: 'Месяц',
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
			text: 'Период',
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
			fieldLabel: 'АРМ',
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

		var wnd = this;

		this.filtersPanel = new Ext.FormPanel(
		{
			xtype: 'form',
			labelAlign: 'right',
			labelWidth: 50,
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
				title: 'Поиск',
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
						fieldLabel: 'ФИО',
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
						fieldLabel: 'Фамилия',
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
						fieldLabel: 'Имя',
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
						fieldLabel: 'Отчество',
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
						fieldLabel: 'Дата рождения',
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
							fieldLabel: 'Цель направления',
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
						text: 'Найти',
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
						id: 'mpwpBtnClear',
						text: 'Сброс',
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
					title: 'ЛПУ',
					layout: 'column',
					items: 
					[{
						layout: 'form',
						columnWidth: .3,
						items: 
						[{
							fieldLabel: 'ЛПУ',
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
							fieldLabel: 'Врач',
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
							text: 'Показать расписание',
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
							text: 'Вернуться к пациенту',
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
				header: "Отделение",
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
				header: "Осмотр",
				width: 65,
				sortable: true,
				dataIndex: 'Person_IsEvents',
				renderer: sw.Promed.Format.checkColumn
			},
			{
				header: "Дата",
				width: 60,
				hidden: true,
				sortable: true,
				dataIndex: 'TimetableGraf_Date',
				renderer: Ext.util.Format.dateRenderer('d.m.Y')
			}, 
			{
				header: "Запись",
				width: 65,
				sortable: true,
				//renderer: Ext.util.Format.dateRenderer('H:i'),
				dataIndex: 'TimetableGraf_begTime'
			},
			{
				header: "Прием",
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
				header: "Возраст",
				width: 55,
				sortable: true,
				dataIndex: 'Person_Age'
			},
            {
                header: "Телефон",
                width: 80,
                sortable: true,
                dataIndex: 'Person_Phone_all'
            },
			{
				header: "Направление",
				width: 100,
				sortable: false,
				dataIndex: 'IsEvnDirection',
				renderer: sw.Promed.Format.dirColumn
			},
			{
				header: "EvnDirection_id",
				hidden: true,
				hideable: false,
				dataIndex: 'EvnDirection_id'
			},
			{
				header: "БДЗ",
				width: 40,
				sortable: false,
				dataIndex: 'Person_IsBDZ',
				renderer: sw.Promed.Format.checkColumn
			},
			/* Убрал простое отображение льготы, добавил федеральную и региональную (http://172.19.61.24:85/issues/show/2095)*/
			/*{
				header: "Льгота",
				width: 5,
				sortable: false,
				dataIndex: 'Person_IsLgot',
				renderer: sw.Promed.Format.checkColumn
			},*/
			{
				header: "ФЛ",
				width: 40,
				sortable: false,
				dataIndex: 'Person_IsFedLgot',
				renderer: sw.Promed.Format.checkColumn
			},
			{
				header: "РЛ",
				width: 40,
				sortable: false,
				dataIndex: 'Person_IsRegLgot',
				renderer: sw.Promed.Format.checkColumn
			},
			{
				header: "№ амб. карты",
				width: 80,
				sortable: false,
				dataIndex: 'PersonCard_Code'
			},
			{
				header: "ЛПУ прикр.",
				width: 80,
				sortable: false,
				dataIndex: 'Lpu_Nick'
			},
			{
				header: "Участок",
				width: 80,
				sortable: false,
				dataIndex: 'LpuRegion_Name'
			},
			{
				header: "Записан",
				width: 100,
				sortable: true,
				dataIndex: 'TimetableGraf_updDT',
				renderer: Ext.util.Format.dateRenderer('d.m.Y H:i')
			}, 
			{
				header: "Оператор",
				width: 300,
				sortable: true,
				dataIndex: 'pmUser_Name'
			}],
			
			view: new Ext.grid.GroupingView(
			{
				//forceFit: true,
                enableGroupingMenu:false,
				groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length.inlist([2,3,4]) ? "записи" : "записей"]})'
			}),
			loadStore: function(params)
			{
				if (!this.params)
					this.params = null;
				if (params)
				{
					this.params = params;
				}
				this.clearStore();
				this.getStore().load({params: this.params});
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
					this.getView().focusRow(0);
					this.getSelectionModel().selectFirstRow();
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
							if ((record.get('Person_id')==null) || (record.get('Person_id')==''))
							{
								var TimetableGraf_Date = record.get('TimetableGraf_Date');
								var current_date = Date.parseDate(form.curDate, 'd.m.Y');
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
								if (!form.gridActions.edit.initialConfig.initialDisabled)
									form.gridActions.edit.setDisabled(true);
								if (!form.gridActions.copy.initialConfig.initialDisabled)
									form.gridActions.copy.setDisabled(true);
								if (!form.gridActions.paste.initialConfig.initialDisabled)
									form.gridActions.paste.setDisabled(true);
								if (!form.gridActions.del.initialConfig.initialDisabled)
									form.gridActions.del.setDisabled(true);
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
								if (!form.gridActions.edit.initialConfig.initialDisabled)
									form.gridActions.edit.setDisabled(false);
								if (!form.gridActions.copy.initialConfig.initialDisabled)
									form.gridActions.copy.setDisabled(false);
								if (!form.gridActions.paste.initialConfig.initialDisabled)
									form.gridActions.paste.setDisabled(false);
								//form.gridActions.del.setDisabled(false);
								// (!record.get('MedStaffFact_id').inlist(getGlobalOptions().medstafffact)) || 
								
								var TimetableGraf_Date = record.get('TimetableGraf_Date');
								var current_date = Date.parseDate(form.curDate, 'd.m.Y');
								if (!form.gridActions.del.initialConfig.initialDisabled)
									form.gridActions.del.setDisabled( // Disabled where
										(!isAdmin) // this user
										&& (
										(record.get('pmUser_updId') != getGlobalOptions().pmuser_id) // this other autor of record
										|| (current_date > TimetableGraf_Date) 
										|| (current_date.format('d.m.Y') == TimetableGraf_Date.format('d.m.Y') && record.get('Person_IsEvents') == 'true') // in current day opened TAP
										)
									);
							}
						}
					},
					'rowdeselect': function(sm, rowIdx, record)
					{
						//
					}
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
			var win = Ext.getCmp('swMPWorkPlaceWindow');
			var rec = grid.getSelectionModel().getSelected();
			if (col == 14 && rec.get('EvnDirection_id') != '') { // столбец с направлением
				/*getWnd('swEvnDirectionEditWindow').show({
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
			var win = Ext.getCmp('swMPWorkPlaceWindow');
			var rec = grid.getSelectionModel().getSelected();
			if (col == 14 && rec.get('EvnDirection_id') != '' && rec.get('EvnDirection_id') != undefined ) { // столбец с направлением
				getWnd('swEvnDirectionEditWindow').show({
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
				case e.DELETE:
					if (!win.gridActions.del.isDisabled())
					{
						win.gridActions.del.execute();
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

		// метод получения данных: "ЛПУ прикрепления", "Тип прикрепления:", "Тип участка:", "Участок:", на котором врач АРМа является врачом на участке
		this.getAttachDataShowWindow = function(wnd) {
			var global_options = getGlobalOptions();
			Ext.Ajax.request(
			{
				url: '/?c=LpuRegion&m=getMedPersLpuRegionList',
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
					tooltip: 'Регистр детей-сирот',
					text: 'Регистр детей-сирот',
					iconCls : 'doc-reg16',
					handler: function() 
					{
						getWnd('swPersonDispOrpSearchWindow').show();
					}
				},
				PersonPrivilegeWOWSearch: {
					tooltip: 'Регистр ВОВ',
					text: 'Регистр ВОВ',
					iconCls : 'doc-reg16',
					handler: function() 
					{
						getWnd('PersonPrivilegeWOWSearchWindow').show();
					}
				},
				PersonDopDispSearch: {
					tooltip: 'Регистр ДД',
					text: 'Регистр ДД',
					iconCls : 'doc-reg16',
					handler: function() 
					{
						getWnd('swPersonDopDispSearchWindow').show();
					}
				},
				EvnPLDispTeen14Search: {
					tooltip: 'Регистр декретированных возрастов',
					text: 'Регистр декретированных возрастов',
					iconCls : 'doc-reg16',
					handler: function() 
					{
						getWnd('swEvnPLDispTeen14SearchWindow').show();
					}
				},
				mainForm: {
					text: 'Журнал вакцинации',
					tooltip: 'Просмотр журналов вакцинации',
					iconCls : 'pol-immuno16',
					handler: function()
					{
						getWnd('amm_mainForm').show();
					}
				},
				PresenceVacForm: {
					text: 'Национальный календарь прививок',
					tooltip: 'Национальный календарь прививок',
					iconCls : 'pol-immuno16',
					handler: function()
					{
                        getWnd('amm_SprNacCalForm').show();
					}
				},
				SprVaccineForm: {
					text: 'Справочник вакцин',
					tooltip: 'Справочник вакцин',
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
					text: 'Поиск льготников',
					iconCls : 'lgot-search16',
					hidden: getGlobalOptions().region.nick.inlist(['pskov']),
					handler: function() 
					{
						getWnd('swPrivilegeSearchWindow').show();
					}
				},
				EvnReceptInCorrectFind: {
					text: 'Журнал отсрочки',
					tooltip: 'Журнал отсрочки',
					iconCls : 'receipt-incorrect16',
					handler: function()
					{
						getWnd('swReceptInCorrectSearchWindow').show();
					}
				},
				OstAptekaViewAction: {
					text: MM_DLO_MEDAPT,
					tooltip: 'Работа с остатками медикаментов по аптекам',
					iconCls : 'drug-farm16',
					handler: function()
					{
						getWnd('swDrugOstatByFarmacyViewWindow').show();
					}
				},
				OstDrugViewAction: {
					text: MM_DLO_MEDNAME,
					tooltip: 'Работа с остатками медикаментов по наименованию',
					iconCls : 'drug-name16',
					handler: function()
					{
						getWnd('swDrugOstatViewWindow').show();
					}
				},
				OrphanRegistry: sw.Promed.personRegister.getOrphanBtnConfig(form.id, form),
				EvnNotifyOrphan: sw.Promed.personRegister.getEvnNotifyOrphanBtnConfig(form.id, form),
				CrazyRegistry:
				{
					tooltip: 'Регистр по психиатрии',
					text: 'Регистр по психиатрии',
					iconCls : 'doc-reg16',
					disabled: (String(getGlobalOptions().groups).indexOf('Crazy', 0) < 0),
					handler: function()
					{
						getWnd('swCrazyRegistryWindow').show({userMedStaffFact: this.userMedStaffFact});
					}.createDelegate(this)
				},
				EvnNotifyCrazy:
				{
					tooltip: 'Журнал Извещений по психиатрии',
					text: 'Журнал Извещений по психиатрии',
					iconCls : 'journal16',
					disabled: (String(getGlobalOptions().groups).indexOf('Crazy', 0) < 0),
					handler: function()
					{
						getWnd('swEvnNotifyCrazyListWindow').show({userMedStaffFact: this.userMedStaffFact});
					}.createDelegate(this)
				},
				NarkoRegistry:
				{
					tooltip: 'Регистр по наркологии',
					text: 'Регистр по наркологии',
					iconCls : 'doc-reg16',
					disabled: (String(getGlobalOptions().groups).indexOf('Narko', 0) < 0),
					handler: function()
					{
						getWnd('swNarkoRegistryWindow').show({userMedStaffFact: this.userMedStaffFact});
					}.createDelegate(this)
				},
				EvnNotifyNarko:
				{
					tooltip: 'Журнал Извещений по наркологии',
					text: 'Журнал Извещений по наркологии',
					iconCls : 'journal16',
					disabled: (String(getGlobalOptions().groups).indexOf('Narko', 0) < 0),
					handler: function()
					{
						getWnd('swEvnNotifyNarkoListWindow').show({userMedStaffFact: this.userMedStaffFact});
					}.createDelegate(this)
				},
				TubRegistry:
				{
					tooltip: 'Регистр больных туберкулезом',
					text: 'Регистр по туберкулезным заболеваниям',
					iconCls : 'doc-reg16',
					disabled: (String(getGlobalOptions().groups).indexOf('Tub', 0) < 0),
					handler: function()
					{
						getWnd('swTubRegistryWindow').show({userMedStaffFact: this.userMedStaffFact});
					}.createDelegate(this)
				},
				EvnNotifyTub:
				{
					tooltip: 'Журнал Извещений о больных туберкулезом',
					text: 'Журнал Извещений по туберкулезным заболеваниям',
					iconCls : 'journal16',
					disabled: (String(getGlobalOptions().groups).indexOf('Tub', 0) < 0),
					handler: function()
					{
						getWnd('swEvnNotifyTubListWindow').show({userMedStaffFact: this.userMedStaffFact});
					}.createDelegate(this)
				},
				VenerRegistry:
				{
					tooltip: 'Регистр больных венерическим заболеванием',
					text: 'Регистр больных венерическим заболеванием',
					iconCls : 'doc-reg16',
					disabled: (String(getGlobalOptions().groups).indexOf('Vener', 0) < 0),
					handler: function()
					{
						getWnd('swVenerRegistryWindow').show({userMedStaffFact: this.userMedStaffFact});
					}.createDelegate(this)
				},
				EvnNotifyVener:
				{
					tooltip: 'Журнал Извещений о больных венерическим заболеванием',
					text: 'Журнал Извещений о больных венерическим заболеванием',
					iconCls : 'journal16',
					disabled: (String(getGlobalOptions().groups).indexOf('Vener', 0) < 0),
					handler: function()
					{
						getWnd('swEvnNotifyVenerListWindow').show({userMedStaffFact: this.userMedStaffFact});
					}.createDelegate(this)
				},
				HIVRegistry:
				{
					tooltip: 'Регистр ВИЧ-инфицированных',
					text: 'Регистр ВИЧ-инфицированных',
					iconCls : 'doc-reg16',
					disabled: !allowHIVRegistry(),
					handler: function()
					{
						getWnd('swHIVRegistryWindow').show({userMedStaffFact: this.userMedStaffFact});
					}.createDelegate(this)
				},
				EvnNotifyHIV:
				{
					tooltip: 'Журнал Извещений о ВИЧ-инфицированных',
					text: 'Журнал Извещений о ВИЧ-инфицированных',
					iconCls : 'journal16',
					disabled: (String(getGlobalOptions().groups).indexOf('HIV', 0) < 0),
					handler: function()
					{
						getWnd('swEvnNotifyHIVListWindow').show({userMedStaffFact: this.userMedStaffFact});
					}.createDelegate(this)
				},
				HepatitisRegistry: 
				{
					tooltip: 'Регистр по Вирусному гепатиту',
					text: 'Регистр по Вирусному гепатиту',
					iconCls : 'doc-reg16',
					handler: function()
					{
						if ( getWnd('swHepatitisRegistryWindow').isVisible() ) {
							sw.swMsg.show({
								buttons: Ext.Msg.OK,
								fn: Ext.emptyFn,
								icon: Ext.Msg.WARNING,
								msg: 'Окно уже открыто',
								title: ERR_WND_TIT
							});
							return false;
						}
						getWnd('swHepatitisRegistryWindow').show({userMedStaffFact: this.userMedStaffFact});
					}.createDelegate(this)
				},
				EvnNotifyHepatitis: 
				{
					tooltip: 'Журнал Извещений по Вирусному гепатиту',
					text: 'Журнал Извещений по Вирусному гепатиту',
					iconCls : 'journal16',
					handler: function()
					{
						if ( getWnd('swEvnNotifyHepatitisListWindow').isVisible() ) {
							sw.swMsg.show({
								buttons: Ext.Msg.OK,
								fn: Ext.emptyFn,
								icon: Ext.Msg.WARNING,
								msg: 'Окно уже открыто',
								title: ERR_WND_TIT
							});
							return false;
						}
						getWnd('swEvnNotifyHepatitisListWindow').show({userMedStaffFact: this.userMedStaffFact});
					}.createDelegate(this)
				},
				EvnInfectNotify: 
				{
					tooltip: 'Журнал Извещений форма №058/У',
					text: 'Журнал Извещений форма №058/У',
					iconCls : 'journal16',
					disabled: false, 
					handler: function()
					{
						if ( getWnd('swEvnInfectNotifyListWindow').isVisible() ) {
							sw.swMsg.show({
								buttons: Ext.Msg.OK,
								fn: Ext.emptyFn,
								icon: Ext.Msg.WARNING,
								msg: 'Окно уже открыто',
								title: ERR_WND_TIT
							});
							return false;
						}
						getWnd('swEvnInfectNotifyListWindow').show({userMedStaffFact: this.userMedStaffFact});
					}.createDelegate(this)
				},
				OnkoRegistry: 
				{
					tooltip: 'Регистр по онкологии',
					text: 'Регистр по онкологии',
					iconCls : 'doc-reg16',
				disabled: (String(getGlobalOptions().groups).indexOf('OnkoRegistry', 0) < 0), 
					handler: function()
					{
						if ( getWnd('swOnkoRegistryWindow').isVisible() ) {
							sw.swMsg.show({
								buttons: Ext.Msg.OK,
								fn: Ext.emptyFn,
								icon: Ext.Msg.WARNING,
								msg: 'Окно уже открыто',
								title: ERR_WND_TIT
							});
							return false;
						}
						getWnd('swOnkoRegistryWindow').show({userMedStaffFact: this.userMedStaffFact});
					}.createDelegate(this)
				},
				EvnOnkoNotify: 
				{
					tooltip: 'Журнал Извещений об онкобольных ',
					text: 'Журнал Извещений об онкобольных ',
					iconCls : 'journal16',
					handler: function()
					{
						if ( getWnd('swEvnOnkoNotifyListWindow').isVisible() ) {
							sw.swMsg.show({
								buttons: Ext.Msg.OK,
								fn: Ext.emptyFn,
								icon: Ext.Msg.WARNING,
								msg: 'Окно уже открыто',
								title: ERR_WND_TIT
							});
							return false;
						}
						getWnd('swEvnOnkoNotifyListWindow').show({userMedStaffFact: this.userMedStaffFact});
					}.createDelegate(this)
				},
				EvnNotifyNolos: sw.Promed.personRegister.getEvnNotifyVznBtnConfig(form.id, form),
				NolosRegistry: sw.Promed.personRegister.getVznBtnConfig(form.id, form)
		}

		// Формирование списка всех акшенов 
		var configActions = 
		{
			action_reports: //http://redmine.swan.perm.ru/issues/18509
			{
				nn: 'action_Report',
				tooltip: 'Просмотр отчетов',
				text: 'Просмотр отчетов',
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
				tooltip: 'Работа с расписанием',
				text: 'Расписание',
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
				tooltip: 'Работа с журналом вызова на дом',
				text: 'Журнал вызавов на дом',
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
				text: 'Средний мед. персонал',
				tooltip: 'Доступ ср. мед. персонала к ЭМК'
			},
			action_Turn: 
			{
				nn: 'action_Turn',
				iconCls : 'mp-queue32',
				disabled: false, 
				text: 'Журнал направлений',
				tooltip: 'Журнал направлений',
				handler: function() {
					getWnd('swMPQueueWindow').show({
						mode: 'view',
						callback: function() {
							this.scheduleRefresh();
						}.createDelegate(this),
						userMedStaffFact: this.userMedStaffFact
					}); 
				}.createDelegate(this)
			},
			action_Calendar: 
			{
				nn: 'action_Calendar',
				tooltip: 'Работа с календарем',
				text: 'Календарь',
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
				tooltip: 'Заявка на лекарственные средства',
				text: 'Заявка на лекарственные средства: Ввод',
				iconCls : 'mp-drugrequest32',
				disabled: false, 
				handler: function() 
				{
					form.showDrugRequestEditForm();
				}
			},
            action_drugrequestview: {
                text: 'Заявка на лекарственные средства: Просмотр',
                tooltip: 'Просмотр заявок',
                iconCls: 'mp-drugrequest32',
                handler: function() {
                    getWnd('swMzDrugRequestSelectWindow').show();
                    //getWnd('swMzDrugRequestMoViewWindow').show();
                }
            },
            /*action_PrivilegeSearch:
			{
				nn: 'action_PrivilegeSearch',
				text: MM_DLO_LGOTSEARCH,
				tooltip: 'Поиск льготников',
				iconCls : 'mp-lgot32',
				disabled: false,
				handler: function() 
				{
					form.getAttachDataShowWindow('swPrivilegeSearchWindow');
				}
			},*/
			action_PersDispSearchView: 
			{
				nn: 'action_PersDispSearchView',
				tooltip: 'Диспансерный учет',
				text: WND_POL_PERSDISPSEARCHVIEW,
				iconCls : 'mp-disp32',
				disabled: false, 
				handler: function() 
				{
					var grid = Ext.getCmp('swMPWorkPlaceWindow').getGrid(),
						selected_record = grid.getSelectionModel().getSelected(),
						Person_id = (selected_record && selected_record.get('Person_id')) || null;
					getWnd('swPersonDispViewWindow').show({mode: 'view', Person_id: Person_id, view_one_doctor: true});
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
			action_JournalHospit: 
			{
				nn: 'action_JournalHospit',
				tooltip: 'Открыть журнал госпитализаций',
				text: 'Журнал госпитализаций',
				iconCls : 'mp-hospital-list32',
				handler: function() 
				{
					if ( getWnd('swJournalHospitWindow').isVisible() ) {
						sw.swMsg.show({
							buttons: Ext.Msg.OK,
							fn: Ext.emptyFn,
							icon: Ext.Msg.WARNING,
							msg: 'Окно уже открыто',
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
				tooltip: 'Открыть журнал вызовов СМП',
				text: 'Журнал вызовов СМП',
				iconCls : 'emergency-list32',
				handler: function()
				{
					if ( getWnd('swCmpCallCardJournalWindow').isVisible() ) {
						sw.swMsg.show({
							buttons: Ext.Msg.OK,
							fn: Ext.emptyFn,
							icon: Ext.Msg.WARNING,
							msg: 'Окно уже открыто',
							title: ERR_WND_TIT
						});
						return false;
					}
					getWnd('swCmpCallCardJournalWindow').show({userMedStaffFact: form.userMedStaffFact});
				}
			},
			action_EvnVKJournal:
			{
				nn: 'action_EvnVKJournal',
				tooltip: 'Открыть журнал ВК',
				text: 'Журнал ВК',
				iconCls : 'vk-journal32',
				handler: function()
				{
					if ( getWnd('swClinExWorkSearchWindow').isVisible() ) {
						sw.swMsg.show({
							buttons: Ext.Msg.OK,
							fn: Ext.emptyFn,
							icon: Ext.Msg.WARNING,
							msg: 'Окно уже открыто',
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
                tooltip: 'Открыть карту профилактических прививок',
                text: 'Иммунопрофилактика',
                iconCls : 'vac-plan32',
                handler: function()
                {
                    var grid = Ext.getCmp('swMPWorkPlaceWindow').getGrid(),
                        selected_record = grid.getSelectionModel().getSelected();
                    if (!selected_record || !selected_record.get('Person_id')) {
                        sw.swMsg.show({
                            buttons: Ext.Msg.OK,
                            fn: Ext.emptyFn,
                            icon: Ext.Msg.WARNING,
                            msg: 'Выберите человека',
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
				tooltip: 'Открыть поиск по поликлинике',
				text: 'Поиск по поликлинике',
				iconCls : 'test16',
				handler: function()
				{
					if ( getWnd('swEvnPLSearchWindow').isVisible() ) {
						sw.swMsg.show({
							buttons: Ext.Msg.OK,
							fn: Ext.emptyFn,
							icon: Ext.Msg.WARNING,
							msg: 'Окно уже открыто',
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
				tooltip: 'Направить пациента к врачу',
				text: 'Записать к врачу',
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
								sw.swMsg.alert('Ошибка', 'Направить пациента к врачу невозможно в связи со смертью пациента');
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
										sw.swMsg.alert('Ошибка', 'Направить пациента к врачу невозможно в связи со смертью пациента');
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
				tooltip: 'Открыть фондодержание',
				text: 'Фондодержание',
				iconCls : 'structure32',
				handler: function()
				{
					if ( getWnd('swFundHoldingViewForm').isVisible() ) {
						sw.swMsg.show({
							buttons: Ext.Msg.OK,
							fn: Ext.emptyFn,
							icon: Ext.Msg.WARNING,
							msg: 'Окно уже открыто',
							title: ERR_WND_TIT
						});
						return false;
					}
					getWnd('swFundHoldingViewForm').show();
				}
			},
			action_References: {
				nn: 'action_References',
				tooltip: 'Справочники',
				text: 'Справочники',
				iconCls : 'book32',
				disabled: false,
				menuAlign: 'tr?',
				menu: new Ext.menu.Menu({
					items: [{
						tooltip: 'МКБ-10',
						text: 'Справочник МКБ-10',
						iconCls: 'spr-mkb16',
						handler: function() {
							if ( !getWnd('swMkb10SearchWindow').isVisible() )
								getWnd('swMkb10SearchWindow').show();
						}
					},
					sw.Promed.Actions.swDrugDocumentSprAction,
					{
						name: 'action_DrugNomenSpr',
						text: 'Номенклатурный справочник',
						iconCls : '',
						handler: function()
						{
							getWnd('swDrugNomenSprWindow').show();
						}
					}, {
						name: 'action_PriceJNVLP',
						text: 'Цены на ЖНВЛП',
						iconCls : 'dlo16',
						handler: function() {
							getWnd('swJNVLPPriceViewWindow').show();
						}
					}, {
						name: 'action_DrugMarkup',
						text: 'Предельные надбавки на ЖНВЛП',
						iconCls : 'lpu-finans16',
						handler: function() {
							getWnd('swDrugMarkupViewWindow').show();
						}
					},
					sw.Promed.Actions.swPrepBlockSprAction
					]
				})
			},
			action_DLO: 
			{
				nn: 'action_DLO',
				tooltip: 'ЛЛО',
				text: 'ЛЛО',
				iconCls : 'dlo32',
				disabled: false, 
				menuAlign: 'tr?',
				menu: new Ext.menu.Menu({
					items: [
						swPromedActions.OrgFarmacyByLpuView,
						swPromedActions.PrivilegeSearch,
						swPromedActions.EvnReceptInCorrectFind,
						swPromedActions.OstAptekaViewAction,
						swPromedActions.OstDrugViewAction
					]
				})
			},
			action_mmunoprof: 
			{
				nn: 'action_mmunoprof',
				tooltip: 'Иммунопрофилактика',
				text: 'Иммунопрофилактика',
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
				tooltip: 'Извещения/Направления',
				text: 'Извещения/Направления',
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
						swPromedActions.EvnNotifyHIV,
						swPromedActions.EvnNotifyNolos
					]
				})
			},
			action_Register: 
			{
				nn: 'action_Register',
				tooltip: 'Регистры',
				text: 'Регистры',
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
						swPromedActions.NolosRegistry,
						swPromedActions.VenerRegistry,
						swPromedActions.HIVRegistry
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
			action_FindRegions: {
				handler: function() {
					getWnd('swFindRegionsWindow').show();
				},
				iconCls : 'mp-region32',
				nn: 'action_FindRegions',
				text: WND_FRW,
				tooltip: 'Поиск участков и врачей по адресу'
			}
		}
		// Копируем все действия для создания панели кнопок
		form.PanelActions = {};
		for(var key in configActions)
		{
			// width:'265',minWidth:'265', style: {width: '100%'}, 
			var iconCls = configActions[key].iconCls.replace(/16/g, '32');
			var z = Ext.applyIf({cls: 'x-btn-large', iconCls: iconCls, text: ''}, configActions[key]);
			this.PanelActions[key] = new Ext.Action(z);
		}
		var actions_list = ['action_Turn','action_DrugRequestEditForm', 'action_drugrequestview', /*'action_PrivilegeSearch',*/'action_PersDispSearchView','action_PersCardSearch','action_Timetable','action_HomeVisitJournal','action_MidMedPersonal','action_JournalHospit', 'action_CmpCallCardJournal'/*,'action_UslugaComplexTree', 'action_EvnVKJournal'*/, 'action_Vaccination', 'action_FundHolding', 'action_DLO', 'action_References', 'action_Notify',(getGlobalOptions().region.nick == 'ufa')?'action_mmunoprof':'', 'action_Register','action_JourNotice', 'action_FindRegions','action_reports'];
		var blocked_list = [];
		if ((getGlobalOptions().region) && (getGlobalOptions().region.nick == 'pskov')) {
			actions_list.push('action_DirectionPerson');
			blocked_list.push('action_DrugRequestEditForm');
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
					id: 'mpwpSchedulePanel',
					items:
					[
						this.MedPersonalPanel,
						this.ScheduleGrid
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
