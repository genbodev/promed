/**
 * swMPRecordWindow - окно записи
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/projects/promed
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009-2010, Swan.
 * @author       Марков Андрей (по интерфейсу Александра Арефьева)
 * @prefix       mprw
 * @tabindex     TABINDEX_MPRW
 * @version      июнь 2010 
 */
 
/*NO PARSE JSON*/

sw.Promed.swMPRecordWindow = Ext.extend(sw.Promed.BaseForm,
{
	codeRefresh: true,
	objectName: 'swMPRecordWindow',
	objectSrc: '/jscore/Forms/Common/swMPRecordWindow.js',
	
	closable: true,
	closeAction: 'hide',
	maximized: true,
	title: lang['zapis_patsienta'],
	iconCls: 'workplace-mp16',
	id: 'swMPRecordWindow',
	readOnly: false,
    returnFunc: Ext.emptyFn,
	getItem: function () 
	{
		var id = this.Wizard.Panel.layout.activeItem.id;
		if(id == 'LpuUnitCard')
			id = 'LpuUnit';
		return this.Wizard[id];
	},
	scheduleLoad: function(mode)
	{
		var prm = new Object();
		// Читаем список, а предварительно помещаем
		if ((this.params.Lpu_id) && (this.params.Lpu_id>0))
		{
			prm.Lpu_id = this.params.Lpu_id;
		}
		if (this.findById('mprwLpu_id').getValue())
		{
			prm.Lpu_id = this.findById('mprwLpu_id').getValue();
		}
		if (!prm.Lpu_id)
		{
			prm.Lpu_id = null;
		}
		
		if ((this.params.LpuUnit_id)  && (this.params.LpuUnit_id>0))
		{
			prm.LpuUnit_id = this.params.LpuUnit_id;
		}
		if (this.params.LpuUnitType_SysNick)
		{
			prm.LpuUnitType_SysNick = this.params.LpuUnitType_SysNick;
		}
		
		if ((this.params.UslugaComplex_id)  && (this.params.UslugaComplex_id > 0))
		{
			prm.UslugaComplex_id = this.params.UslugaComplex_id;
		}
		
		if ((this.params.MedPersonal_id)  && (this.params.MedPersonal_id>0))
		{
			prm.MedPersonal_id = this.params.MedPersonal_id;
		}
		if (this.findById('mprwMedPersonal_id').getValue())
		{
			prm.MedPersonal_id = this.findById('mprwMedPersonal_id').getValue();
		}
		prm.LpuSectionProfile_id = this.findById('mprwLpuSectionProfile_id').getValue();
		
		if ((this.params.LpuSectionProfile_id)  && (this.paramsLpuSectionProfile_id>0))
		{
			prm.LpuSectionProfile_id = this.params.LpuSectionProfile_id;
		}
		
		prm.LpuUnitType_id = this.findById('mprwLpuUnitType_id').getValue();
		if ((this.params.LpuSection_id)  && (this.params.LpuSection_id>0))
		{
			prm.LpuSection_id = this.params.LpuSection_id;
		}
		if ((this.params.MedStaffFact_id)  && (this.params.MedStaffFact_id>0))
		{
			prm.MedStaffFact_id = this.params.MedStaffFact_id;
		}
		
		if ((this.params.MedService_id)  && (this.params.MedService_id>0))
		{
			prm.MedService_id = this.params.MedService_id;
		}
		
		if ((this.params.UslugaComplex_id)  && (this.params.UslugaComplex_id>0))
		{
			prm.UslugaComplex_id = this.params.UslugaComplex_id;
		}
		
		if ((!prm.LpuSectionProfile_id || !prm.LpuUnitType_id) && !prm.MedStaffFact_id)
		{
			prm.MedStaffFact_id = this.params.UserMedStaffFact_id;
		}
		
		prm.start = 0; 
		prm.limit = 100;
		
		if(this.Wizard.Panel.layout.activeItem.id == 'Lpu') {
			this.Wizard.Lpu.removeAll(true);
			this.Wizard.Lpu.loadData({globalFilters:prm});
			this.Wizard.MedServiceLpu.removeAll(true);
			prm.LpuUnitLevel = '1';
			this.Wizard.MedServiceLpu.loadData({globalFilters:prm});
		} else {
			if (this.Wizard.Panel.layout.activeItem.id.inlist(['SchedulePolka', 'ScheduleStac', 'ScheduleMedService'])) {
				if(this.fromEmk){this.getItem().FilterData = prm;}
				this.getItem().loadSchedule();
			} else {
				this.getItem().removeAll(true);
				this.getItem().loadData({globalFilters:prm});
			}
		}
		
		//log(this.getItem());
	},
	scheduleSave: function(data)
	{
		var form = this;
		var object = form.getTimetableObject();
		data[object+'_id'] = data['Timetable_id'];

		if (object == 'TimetableStac')
			data.Evn_pid = this.params.EvnDirection_pid;
		else
			data.Evn_id = this.params.EvnDirection_pid;
		if (!((data['Person_id']>0) && (data[object+'_id']>0)))
			return false;
		
		if(form.onSheduleSave) { //если есть спец обработчик для выбора расписания - используем его		
			//собираем дополнительные данные для конкретной бирки
			data.ttg_Lpu_id = this.params.Lpu_id;
			data.ttg_LpuUnit_id = this.params.LpuUnit_id;
			data.ttg_UslugaComplex_id = this.params.UslugaComplex_id;
			data.ttg_MedPersonal_id = this.params.MedPersonal_id;
			data.ttg_LpuSectionProfile_id = this.params.LpuSectionProfile_id;
			
			form.onSheduleSave(data);
		} else { // иначе стандартный обработчик
			var needDirection;
			if(Ext.getCmp('mprwIsThis').pressed) {
				needDirection = false;
			}
			sw.Promed.Direction.recordPerson({
				Timetable_id: data.Timetable_id,
				person: {
					Person_Surname: form.params.Person_Surname,
					Person_Firname: form.params.Person_Firname,
					Person_Secname: form.params.Person_Secname,
					Person_Birthday: form.params.Person_Birthday,
					Person_id: form.params.Person_id,
					Server_id: form.params.Server_id,
					PersonEvn_id: form.params.PersonEvn_id
				},
				direction: {
					LpuUnitType_SysNick: data.LpuUnitType_SysNick
					,EvnQueue_id: form.params.EvnQueue_id || null
					,QueueFailCause_id: form.params.QueueFailCause_id || null
					,UslugaComplex_id: form.params.UslugaComplex_id || null
					,LpuSection_Name: form.params.LpuSection_Name || ''
					,LpuSection_uid: data.LpuSection_id || null
					,PrehospDirect_id: (getGlobalOptions().lpu_id == form.params.Lpu_id)?1:2
					,LpuSectionProfile_id: form.params.LpuSectionProfile_id || null
					,MedStaffFact_id: this.userMedStaffFact.MedStaffFact_id
					,EvnDirection_pid: form.params.EvnDirection_pid || 0
					,Diag_id: form.params.Diag_id || null
					,EvnPrescr_id: form.params.EvnPrescr_id || null
					,PrescriptionType_Code: form.params.PrescriptionType_Code || null
					,MedService_id: form.params.MedService_id || null
					,MedService_Nick: form.params.MedService_Nick || ''
					,Lpu_did: form.params.Lpu_id //form.Filters.findById('mprwLpu_id').getValue()
					,MedPersonal_id: this.userMedStaffFact.MedPersonal_id
					,Lpu_id: this.userMedStaffFact.Lpu_id
					,LpuSection_id: this.userMedStaffFact.LpuSection_id
                    ,From_MedStaffFact_id: data.MedStaffFact_id
                    ,ARMType_id: this.userMedStaffFact.ARMType_id
                    ,timetable: object
                    ,time: data.Timetable_datetime
				},
				order: (form.params.order)?form.params.order:{}, // если при записи сделан заказ, то передаем его данные
				callback: function(data){
					if (typeof this.onEvnDirectionSave == 'function')
					{
						this.onEvnDirectionSave(data);
					}
					if (this.fromEmk || 'evn_prescr' == this.formMode) {
						this.hide();
						return;
					}
					this.getItem().loadSchedule();
					this.getItem().focus();
				}.createDelegate(this),
				onSaveRecord: form.onSaveRecord,
				onHide: null,
				needDirection: needDirection,
				fromEmk: form.fromEmk,
				//mode: form.formMode, // todo: надо протестировать какой смысл в передаче этого параметра
				mode: 'nosave',
				loadMask: true,
				windowId: 'swMPRecordWindow'
			});
		}
	},
	
	scheduleAdd: function(time_id, tt_date, tt_time)
	{
		/*
		sw.swMsg.show(
		{
			icon: Ext.MessageBox.QUESTION,
			msg: lang['zapisat_patsienta_na_vyibrannuyu_birku'],
			title: lang['vopros'],
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj)
			{
				if ('yes' == buttonId)
				{*/
		var form = Ext.getCmp('swMPRecordWindow');
		var LpuSection_id;
		var MedPersonal_id;
		var MedStaffFact_id;

		switch (form.params.LpuUnitType_SysNick)
		{
			case 'parka': case 'stac': case 'dstac': case 'hstac': case 'pstac': 
				if( form.params.LpuUnitType_SysNick == 'parka' )
					var rec = form.Wizard.MedService.getGrid().getSelectionModel().getSelected();
				else
					var rec = form.Wizard.LpuSection.getGrid().getSelectionModel().getSelected();
				if (rec)
				{
					LpuSection_id = rec.get('LpuSection_id');
				}
				// при записи к себе надо брать параметры текущего пользователя
				if(Ext.getCmp('mprwIsThis').pressed)
				{
					LpuSection_id = form.userMedStaffFact.LpuSection_id;
				}
				// при записи через избранные расписания берем принятые параметры 
				if ('top_timetable' == form.formMode)
				{
					LpuSection_id = form.params.LpuSection_id;
				}
				break;
			case 'polka':
				var rec = form.Wizard.MedPersonal.getGrid().getSelectionModel().getSelected();
				if (rec)
				{
					MedPersonal_id = rec.get('MedPersonal_id');
					MedStaffFact_id = rec.get('MedStaffFact_id');
				}
				// при записи к себе надо брать параметры текущего пользователя
				if(Ext.getCmp('mprwIsThis').pressed)
				{
					MedPersonal_id = getGlobalOptions().medpersonal_id;
					MedStaffFact_id = form.userMedStaffFact.MedStaffFact_id;
				}
				// при записи через избранные расписания берем принятые параметры
				if ('top_timetable' == form.formMode)
				{
					MedPersonal_id = form.params.MedPersonal_id;
					MedStaffFact_id = form.params.MedStaffFact_id;
				}
				break;
		}
		var data = 
		{
			Person_id: form.params.Person_id,
			Server_id: form.params.Server_id,
			PersonEvn_id: form.params.PersonEvn_id,
			MedStaffFact_id: MedStaffFact_id,
			MedPersonal_id: MedPersonal_id,
			LpuSection_id: LpuSection_id,
			LpuUnitType_SysNick: form.params.LpuUnitType_SysNick,
			Timetable_id: time_id,
            Timetable_datetime: (tt_date && tt_time)?(tt_date +' '+ tt_time):null
		}
		form.scheduleSave(data);
					/*
				}
			}
		});*/
	},
	scheduleDelete:function()
	{
		var form = this;
		var grid = form.getItem().getGrid();
		var object = form.getTimetableObject();
		
		if (!grid)
		{
			//Ext.Msg.alert('Ошибка', 'Список расписаний не найден!');
			return false;
		}
		var params = {};
		
		var index = grid.getSelectionModel().getSelectedCell()[1];
		params[object+'_id'] = grid.getSelectionModel().selection.record.get(object+'_id'+index);
		if (!((index>0) && (params[object+'_id']>0)))
		{
			//Ext.Msg.alert('Ошибка', 'Не выбрана запись!');
			return false;
		}
		params['LpuUnitType_SysNick'] = this.params.LpuUnitType_SysNick;
		sw.swMsg.show(
		{
			icon: Ext.MessageBox.QUESTION,
			msg: lang['vyi_hotite_osvobodit_vremya_priema'],
			title: lang['vopros'],
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj)
			{
				if ('yes' == buttonId)
				{
					form.getLoadMask(lang['podojdite_osvobojdaetsya_zapis']).show();
					Ext.Ajax.request(
					{
						url: C_TTG_CLEAR,
						params: params,
						failure: function(response, options)
						{
							form.getLoadMask().hide();
							Ext.Msg.alert(lang['oshibka'], lang['pri_vyipolnenii_operatsii_osvobojdeniya_vremeni_priema_proizoshla_oshibka']);
						},
						success: function(response, action)
						{
							form.getLoadMask().hide();
							if (response.responseText)
							{
								var answer = Ext.util.JSON.decode(response.responseText);
								if (!answer.success)
								{
									if (answer.Error_Code)
									{
										Ext.Msg.alert(lang['oshibka_#']+answer.Error_Code, answer.Error_Message);
									}
									else
										if (!answer.Error_Msg) 
										{
											Ext.Msg.alert(lang['oshibka'], lang['pri_vyipolnenii_operatsii_osvobojdeniya_vremeni_priemaproizoshla_oshibka_osvobojdenie_priema_nevozmojno']);
										}
								}
								else
								{
									grid.getStore().reload();
								}
							}
							else
							{
								Ext.Msg.alert(lang['oshibka'], lang['pri_vyipolnenii_operatsii_osvobojdeniya_vremeni_priemaproizoshla_oshibka_otsutstvuet_otvet_servera']);
							}
						}
					});
				}
				else
				{
					//
					grid.getView().focusCell(grid.getSelectionModel().getSelectedCell()[0],grid.getSelectionModel().getSelectedCell()[1]);
				}
			}
		});
	},
	getTimetableObject:function()
	{
		var object = '';
		if (this.params.LpuUnitType_SysNick != undefined) {
			switch (this.params.LpuUnitType_SysNick)
			{
				case 'polka': 
					object = 'TimetableGraf';
					break;
				case 'stac': case 'dstac': case 'hstac': case 'pstac': 
					object = 'TimetableStac';
					break;
				case 'parka': 
					object = 'TimetablePar';
					break;
			}
		} else {
			if (ARMIsPolka())
				object = 'TimetableGraf';
			if (ARMIsParka())
				object = 'TimetablePar';
			if (ARMIsStac())
				object = 'TimetableStac';
		}
		return object;
	},
	scheduleQueue:function(conf)
	{
        if ( typeof conf != 'object' ) {
            conf = new Object();
        }
		var form = this;
		var object = form.getTimetableObject();
		var params = {};
		params['LpuUnit_did'] = this.params.LpuUnit_id;
		params['LpuSection_did'] = this.params.LpuSection_id;
		params['MedService_did'] = this.params.MedService_id;
		params['LpuSectionProfile_id'] = this.params.LpuSectionProfile_id;
		if (object == 'TimetableGraf')
		{
			params.MedPersonal_did = this.params.MedPersonal_id;
		}
		// при записи к себе надо брать параметры текущего пользователя
		if(Ext.getCmp('mprwIsThis').pressed)
		{
			params.LpuSection_did = this.userMedStaffFact.LpuSection_id;
			params.LpuUnit_did = this.userMedStaffFact.LpuUnit_id;
			params.LpuSectionProfile_id = this.userMedStaffFact.LpuSectionProfile_id;
			params.MedStaffFact_id = this.userMedStaffFact.MedStaffFact_id;
			if (object == 'TimetableGraf')
			{
				params.MedPersonal_did = getGlobalOptions().medpersonal_id;
			}
		}
		sw.Promed.Direction.queuePerson({
			person: {
				Person_Surname: form.params.Person_Surname,
				Person_Firname: form.params.Person_Firname,
				Person_Secname: form.params.Person_Secname,
				Person_Birthday: form.params.Person_Birthday,
				Person_id: form.params.Person_id,
				Server_id: form.params.Server_id,
				PersonEvn_id: form.params.PersonEvn_id
			},
			direction: {
				LpuUnitType_SysNick: form.params.LpuUnitType_SysNick
				,UslugaComplex_id: form.params.UslugaComplex_id || null
				,LpuSection_Name: form.params.LpuSection_Name || ''
				,LpuSection_did: params.LpuSection_did
				,LpuUnit_did: params.LpuUnit_did
				//,LpuSection_uid: data.LpuSection_id || null
				,MedPersonal_did: params.MedPersonal_did || null
				,PrehospDirect_id: (getGlobalOptions().lpu_id == form.params.Lpu_id)?1:2
				,LpuSectionProfile_id: params.LpuSectionProfile_id || null
				,MedStaffFact_id: this.userMedStaffFact.MedStaffFact_id
				,EvnDirection_pid: form.params.EvnDirection_pid || 0
				,Diag_id: form.params.Diag_id || null
				,EvnPrescr_id: form.params.EvnPrescr_id || null
				,PrescriptionType_Code: form.params.PrescriptionType_Code || null
				,MedService_did: params.MedService_did || null
				,MedService_id: form.params.MedService_id || null
				,MedService_Nick: form.params.MedService_Nick || ''
				,Lpu_did: form.params.Lpu_id //form.Filters.findById('mprwLpu_id').getValue()
				,MedPersonal_id: this.userMedStaffFact.MedPersonal_id
				,Lpu_id: this.userMedStaffFact.Lpu_id
                ,LpuSection_id: this.userMedStaffFact.LpuSection_id
                ,From_MedStaffFact_id: params.MedStaffFact_id
                ,ARMType_id: this.userMedStaffFact.ARMType_id
                ,timetable: object
			},
			order: (form.params.order)?form.params.order:{}, // если при записи сделан заказ, то передаем его данные
			callback: function(answer){
				if(answer && answer.success) {
					if (typeof this.onEvnDirectionSave == 'function')
					{
						this.onEvnDirectionSave(answer);
					}
					if (this.fromEmk || 'evn_prescr' == this.formMode) { // открывали из ЭМК => закрываем форму, возвращаясь в ЭМК, где видим результат постановки в очередь (refs #8958)
						if (!params.needDirection) {
							this.hide();
						}
					} else {
						//Ext.Msg.alert('Сообщение', 'Пациент <b>'+this.params.Person_Surname+' '+this.params.Person_Firname+' '+this.params.Person_Secname+' '+'</b><br/> успешно поставлен в очередь!');
						this.getItem().focus();
						this.hide();						
					}
				} else if (!answer) { // если вообще нет ответа, выводим свою ошибку
					Ext.Msg.alert(lang['oshibka'], lang['pri_vyipolnenii_operatsii_postanovki_v_ocheredproizoshla_oshibka']);//: +lang['otsutstvuet']+ +lang['otvet']+ +lang['servera']+
					this.getItem().focus();
				}
			}.createDelegate(this),
			onHide: null,
			needDirection: null,
			fromEmk: form.fromEmk,
			mode: 'nosave',
			loadMask: true,
			windowId: 'swMPRecordWindow'
		});
	},
	/**
	 * Открытие формы заказа услуги в параклинике
	 */
	addOrder: function(options) {
		var w = this;
		if (w.params.LpuUnitType_SysNick == 'parka') {
			var p = (options && options.params)?options.params:{};
			var record = w.Wizard.MedService.getGrid().getSelectionModel().getSelected();
			p.MedService_id = record.get('MedService_id'); //Служба, которой назначается оказание услуги
			p.MedService_Nick = record.get('MedService_Nick');
			p.Lpu_uid = record.get('Lpu_id');
			p.LpuSection_uid = record.get('LpuSection_id');
			p.UslugaComplexMedService_id = record.get('UslugaComplexMedService_id');
			p.LpuSectionProfile_id = record.get('LpuSectionProfile_id');
			p.UslugaComplex_id = record.get('UslugaComplex_id');
			p.UslugaComplex_Name = record.get('UslugaComplex_Name');
			p.MedServiceType_SysNick = record.get('MedServiceType_SysNick');
			p.Person_id = w.params.Person_id;
			p.PersonEvn_id = w.params.PersonEvn_id;
			p.Server_id = w.params.Server_id;
			// Назначенная услуга
			p.UslugaComplex_prescid = w.params.UslugaComplex_prescid;

			p.action = 'add';
			p.mode = 'nosave'; // просто возвращаем в калбэке данные
			p.callback = function(scope, id, values) {
				// здесь показываем расписание на определенную выбранную службу
				this.params.order = values;

				// подменяем данные на выбранные
				var params = {
					LpuSection_id: values.LpuSection_id,
					// службу выбираем из пункта забора, если он выбран
					MedService_id: (values.MedService_pzid>0)?values.MedService_pzid:values.MedService_id,
					MedService_Nick: (values.MedService_pzid>0)?values.MedService_pzNick:values.MedService_Nick,
					UslugaComplex_id: values.UslugaComplex_id,
					UslugaComplex_Name: values.UslugaComplex_Name,
					LpuSectionProfile_id: values.LpuSectionProfile_id,
					UslugaComplexMedService_id: values.UslugaComplexMedService_id
				};
				// todo: Если в форме выбора выбрали ПЗ вместо лаборатории, то вместо UslugaComplexMedService_id нужно выбрать связанную услугу
				// скорее всего выбор связанной услуги надо делать прямо на форме заказа, но пока связываемости услуг нет, поэтому просто обнуляем услугу
				if (values.MedService_pzid>0) { // Если в расписании выбрали лабораторию, а в форме заказа изменили на ПЗ
					params.UslugaComplexMedService_id = null; // то обнуляем услугу службы, если она была выбрана
					params.UslugaComplex_id = null;
					params.UslugaComplex_Name = null;
				}
				this.scheduleOpen('ScheduleMedService', params);

			}.createDelegate(w);
			//w.params.UslugaComplex_id
			getWnd('swEvnUslugaOrderEditWindow').show(p);
		}
	},
	scheduleOpen: function(mode, params)
	{
		if (!mode)
		{
			mode = this.Wizard.Panel.layout.activeItem.id;
		}
		//console.info('Открываем визард = %i', mode);
		
		// Изменения в парамсах
		switch (mode)
		{
			case 'Lpu':
				this.params.LpuUnit_id = null;
				this.params.UslugaComplex_id = null;
				this.params.Lpu_id = null;
				this.params.Lpu_Nick = null;
				this.params.LpuUnit_Name = null;
				this.params.MedPersonal_id = null;
				this.params.MedStaffFact_id = null;
				this.params.MedPersonal_FIO = null;
				this.params.LpuSection_Name = null;
				this.params.LpuSection_id = null;
				this.params.LpuSectionProfile_id = null;
				this.params.LpuUnitType_SysNick = null;
				this.params.MedService_id = null;
				this.params.MedService_Nick = null;
				break;
			case 'LpuUnitCard':
				if (!(this.Wizard.Panel.layout.activeItem.id.inlist(['SchedulePolka', 'ScheduleStac', 'ScheduleMedService'])))
				{
					var rec = this.getItem().getGrid().getSelectionModel().getSelected();
					if ((rec) && (rec.get('LpuUnit_id')) && (rec.get('Lpu_id')))
					{
						this.params.LpuUnit_id = rec.get('LpuUnit_id');
						this.params.Lpu_id = rec.get('Lpu_id');
						this.params.Lpu_Nick = rec.get('Lpu_Nick');
						this.params.LpuUnit_Name = rec.get('LpuUnit_Name');
					}
				}
				this.params.MedPersonal_id = null;
				this.params.MedStaffFact_id = null;
				this.params.MedPersonal_FIO = null;
				this.params.LpuSection_Name = null;
				this.params.LpuUnitType_SysNick = null;
				this.params.MedService_id = null;
				this.params.UslugaComplex_id = null;
				this.params.MedService_Nick = null;
				this.Wizard.MedPersonal.removeAll();
				
				this.Wizard.ScheduleMedService.MedService_id = null;
				this.Wizard.ScheduleMedService.UslugaComplexMedService_id = null;
				this.Wizard.ScheduleMedService.EvnUsluga_pid = null;
				
				break;
			case 'SchedulePolka':
				var rec = this.Wizard.MedPersonal.getGrid().getSelectionModel().getSelected();
				this.params.MedPersonal_id = rec.get('MedPersonal_id');
				this.params.MedPersonal_FIO = rec.get('MedPersonal_FIO');
				this.params.LpuSection_id = rec.get('LpuSection_id');
				this.params.LpuSectionProfile_id = rec.get('LpuSectionProfile_id');
				this.params.LpuSection_Name = null;
				this.params.MedService_Nick = null;

				if (rec)
				{
					// Передаем в форму расписания идентификатор врача
					this.Wizard.SchedulePolka.MedStaffFact_id = rec.get('MedStaffFact_id');
				}
				break;
			case 'ScheduleStac':
				var rec = this.Wizard.LpuSection.getGrid().getSelectionModel().getSelected();
				if (rec)
				{
					this.params.LpuSection_id = rec.get('LpuSection_id');
					this.params.LpuSectionProfile_id = rec.get('LpuSectionProfile_id');
					this.params.LpuSection_Name = rec.get('LpuSection_Name');
					
					this.Wizard.ScheduleStac.LpuSection_id = rec.get('LpuSection_id');
				}
				this.params.MedService_id = null;
				this.params.MedService_Nick = null;
				break;
			case 'ScheduleMedService':
				// ЛПУ в параметрах подменяем, если поменялось
				var lpu_combo = this.findById('mprwLpu_id');
				this.params.Lpu_Nick = lpu_combo.getStore().getById(lpu_combo.getValue()).get('Lpu_Nick');
				if (params) { // Если параметры переданы, то используем их, а не читаем из грида
					this.params = Ext.apply(this.params, params);
					// todo: !night проверить здесь еще все
					this.Wizard.ScheduleMedService.EvnUsluga_pid = this.params.EvnDirection_pid; // todo: ?
					this.Wizard.ScheduleMedService.MedService_id = this.params.MedService_id; // todo: ?
					this.Wizard.ScheduleMedService.UslugaComplexMedService_id = this.params.UslugaComplexMedService_id;
				} else {
					var rec = this.Wizard.MedService.getGrid().getSelectionModel().getSelected();
					/*
					 var rec = this.Wizard.MedService.getGrid().getSelectionModel().getSelected();
					 if(!rec)
					 rec = this.Wizard.MedServiceLpu.getGrid().getSelectionModel().getSelected();*/
					if (rec)
					{
						this.params.LpuSection_id = rec.get('LpuSection_id');
						this.params.MedService_id = rec.get('MedService_id');
						this.params.UslugaComplex_id = rec.get('UslugaComplex_id');
						this.params.UslugaComplex_Name = rec.get('UslugaComplex_Name');
						this.params.MedService_Nick = rec.get('MedService_Nick');
						this.params.LpuSectionProfile_id = rec.get('LpuSectionProfile_id');
						// Передаем в форму расписания идентификатор службы, идентификатор услуги
						this.Wizard.ScheduleMedService.EvnUsluga_pid = this.params.EvnDirection_pid;
						this.Wizard.ScheduleMedService.MedService_id = rec.get('MedService_id');
						this.Wizard.ScheduleMedService.UslugaComplexMedService_id = rec.get('UslugaComplexMedService_id');
					}
				}
				this.params.MedPersonal_FIO = null;
				this.params.LpuSection_Name = null;
				break;
			/*
			case 'ListByDay':
				var rec;
				switch (this.params.LpuUnitType_SysNick)
				{
					case 'polka':
						rec = this.Wizard.MedPersonal.getGrid().getSelectionModel().getSelected();
						this.Wizard.ListByDay.layout.setActiveItem(0);
						if (rec)
						{
							this.params.MedPersonal_id = rec.get('MedPersonal_id');
							this.params.MedPersonal_FIO = rec.get('MedPersonal_FIO');
						}
						this.Wizard.ListByDay.doLayout();
						break;
					case 'stac': case 'dstac': case 'hstac': case 'pstac': 
						rec = this.Wizard.LpuSection.getGrid().getSelectionModel().getSelected();
						this.Wizard.ListByDay.layout.setActiveItem(1);
						if (rec)
						{
							this.params.LpuSection_id = rec.get('LpuSection_id');
							this.params.LpuSection_Name = rec.get('LpuSection_Name');
						}
						this.Wizard.ListByDay.doLayout();
						break;
				}
				*/
		}
		this.Wizard.Panel.layout.setActiveItem(mode);
		this.Wizard.Panel.doLayout();
		this.getItem().removeAll();
		this.setMyTitle();
		this.getFilterButton('mprwBtnMPPrevious').enable();
		this.scheduleLoad();
	},
	valuesFilters: null,
	saveIsThis: function(button, check)
	{
		var form = this;
		form.findById('mprwLpu_id').setDisabled(check);
		form.findById('mprwMedPersonal_id').setDisabled(check);
		
		form.findById('mprwLpuSectionProfile_id').setDisabled(check);
		form.findById('mprwLpuUnitType_id').setDisabled(check);
		// TODO: mprwLpu_id где то фильтруется, этот момент в дальнейшем надо разобрать и переделать возможно 
		form.Filters.findById('mprwLpu_id').clearFilter();
		if (check)
		{
			button.setText('<b style="font-color:#555;">Выбрать другого врача</b>');
			// при записи к себе запомним исходные параметры, чтобы в случае отмены записи к себе вернуться к ним
			form.Filters.findById('mprwLpu_id').setValue(getGlobalOptions().lpu_id);
			form.Filters.findById('mprwLpuSectionProfile_id').setValue(form.userMedStaffFact.LpuSectionProfile_id);
			form.Filters.findById('mprwLpuUnitType_id').setValue(form.userMedStaffFact.LpuUnitType_id);
			
			this.valuesFilters = {
				LpuSectionProfile_id: form.Filters.findById('mprwLpuSectionProfile_id').getValue(),
				LpuUnitType_id: form.Filters.findById('mprwLpuUnitType_id').getValue(),
				Lpu_id: form.Filters.findById('mprwLpu_id').getValue(),
				MedPersonal_id: form.Filters.findById('mprwMedPersonal_id').getValue(),
				mode: form.Wizard.Panel.layout.activeItem.id,
				params: Ext.apply({}, this.params)
			};

			// при записи к себе надо брать параметры текущего пользователя
			this.params.Lpu_id = getGlobalOptions().lpu_id;
			this.params.LpuSection_id = this.userMedStaffFact.LpuSection_id;
			this.params.MedPersonal_id = getGlobalOptions().medpersonal_id;
			this.params.MedStaffFact_id = this.userMedStaffFact.MedStaffFact_id;
			this.Wizard.SchedulePolka.MedStaffFact_id = this.userMedStaffFact.MedStaffFact_id;
			this.params.LpuSectionProfile_id = this.userMedStaffFact.LpuSectionProfile_id;
			this.params.LpuSection_Name = this.userMedStaffFact.LpuSection_Name;
			this.params.MedPersonal_FIO = this.userMedStaffFact.MedPersonal_FIO;
			this.params.MedService_id = null;
			this.params.MedService_Nick = null;
			
			form.Filters.findById('mprwMedPersonal_id').getStore().load(
			{
				params:
				{
					Lpu_id: getGlobalOptions().lpu_id
				},
				callback: function()
				{
					form.Filters.findById('mprwMedPersonal_id').setValue(getGlobalOptions().medpersonal_id);
					form.Wizard.Panel.layout.setActiveItem('SchedulePolka');
					form.scheduleLoad();
					if (ARMIsPolka())
						form.params.LpuUnitType_SysNick = 'polka';
					if (ARMIsParka())
						form.params.LpuUnitType_SysNick = 'parka';
					if (ARMIsStac())
						form.params.LpuUnitType_SysNick = 'stac';
				}
			});
			Ext.getCmp('mprwBtnMPClear').disable();
			this.setMyTitle();
		}
		else 
		{
			button.setText('<b style="font-color:#555;">Записать к себе</b>');
			//form.findById('mprwLpu_id').setValue(null);
			//form.findById('mprwMedPersonal_id').getStore().removeAll();
			//form.findById('mprwMedPersonal_id').setValue(null);
			// При отмене записи к себе возвращаем фильтры в исходное состояние
			this.params = Ext.apply({}, this.valuesFilters.params);
			form.Filters.findById('mprwLpu_id').setValue(form.valuesFilters.Lpu_id);
			form.Filters.findById('mprwLpuSectionProfile_id').setValue(form.valuesFilters.LpuSectionProfile_id);
			form.Filters.findById('mprwLpuUnitType_id').setValue(form.valuesFilters.LpuUnitType_id);
			if ( form.valuesFilters.Lpu_id == getGlobalOptions().lpu_id ) 
			{
				form.Filters.findById('mprwMedPersonal_id').setValue(form.valuesFilters.MedPersonal_id);
				form.Wizard.Panel.layout.setActiveItem(form.valuesFilters.mode);
				form.scheduleLoad();
				Ext.getCmp('mprwBtnMPClear').enable();
				form.valuesFilters = null;
			}
			else
			{
				form.Filters.findById('mprwMedPersonal_id').getStore().load(
				{
					params:
					{
						Lpu_id: form.valuesFilters.Lpu_id
					},
					callback: function()
					{
						form.Filters.findById('mprwMedPersonal_id').setValue(form.valuesFilters.MedPersonal_id);
						form.Wizard.Panel.layout.setActiveItem(form.valuesFilters.mode);
						form.scheduleLoad();
						Ext.getCmp('mprwBtnMPClear').enable();
						form.valuesFilters = null;
					}
				});
			}
			this.setMyTitle();
		}
	},

	openDirectionEditWindow: function(options)
	{
		var callback = (typeof options.callback == 'function')?options.callback:Ext.emptyFn;
		var form = this,
			object = form.getTimetableObject(),
			formParams = options.formParams || {};
		formParams.Diag_id = form.params.Diag_id;
		formParams.timetable = object;
		formParams.Person_id = form.params.Person_id;
		formParams.PersonEvn_id = form.params.PersonEvn_id;
		formParams.Server_id = form.params.Server_id;
		formParams.Lpu_did = form.Filters.findById('mprwLpu_id').getValue();
		formParams.LpuSectionProfile_id = form.params.LpuSectionProfile_id;
		if (options.is_cito) {
			formParams.DirType_id = 5;
		}
		// здесь сделать cellCol
		var index;
		try{
			index = grid.getSelectionModel().getSelectedCell()[1];
		}
		catch(e){
			//что-то делаем, если бирка не выбрана
		}
		if (options.Time_id)
		{
			formParams[object+'_id'] = (formParams[object+'_id'])?formParams[object+'_id']:options.Time_id;
			formParams['time'] = '00:00';
		}
		if (form.params.UserMedStaffFact_id)
		{
			formParams.MedStaffFact_id = form.params.UserMedStaffFact_id;
		}
		// log(formParams);
		// Кроме того, надо передавать врача 
		// Тип направления 
		// ЛПУ направления 
		// Время и дату записи
		this.getItem().loadSchedule();
		switch (form.formMode)
		{
			case 'jodirection':
				formParams.EvnDirection_id = form.params.EvnDirection_id;
				formParams.EvnDirection_pid = form.params.EvnDirection_pid;
				var onEvnDirectionSave = function(data) {
					if ( !data || !data.evnDirectionData ) {
						return false;
					}

					var ed_view_frame = Ext.getCmp('EJDW_EvnDirectionGrid');
					var edgrid = ed_view_frame.getGrid();
					var record = edgrid.getStore().getById(data.evnDirectionData.EvnDirection_id);

					if ( !record ) {
						if ( edgrid.getStore().getCount() == 1 && !edgrid.getStore().getAt(0).get('EvnDirection_id') ) {
							edgrid.getStore().removeAll();
						}

						data.evnDirectionData.limit = 100;
						data.evnDirectionData.start = 0;
						ed_view_frame.loadData({
							globalFilters: data.evnDirectionData
						});
					}
					else {
						var evn_direction_fields = new Array();
						var i = 0;

						edgrid.getStore().fields.eachKey(function(key, item) {
							evn_direction_fields.push(key);
						});

						for ( i = 0; i < evn_direction_fields.length; i++ ) {
							record.set(evn_direction_fields[i], data.evnDirectionData[evn_direction_fields[i]]);
						}

						record.commit();
					}
				}.createDelegate(this);
				var onHide = function() {
					Ext.getCmp('swMPRecordWindow').hide();
					var edgrid = Ext.getCmp('EJDW_EvnDirectionGrid').getGrid();
					//edgrid.getView().focusRow(0);
					edgrid.getSelectionModel().selectFirstRow();
				}.createDelegate(this);
				break;
			default: 
				formParams.EvnDirection_pid = form.params.EvnDirection_pid || 0;
				formParams.EvnPrescr_id = form.params.EvnPrescr_id || null;
				formParams.PrescriptionType_Code = form.params.PrescriptionType_Code || null;
				var onEvnDirectionSave = function(data) {
					callback(data);
					if (typeof form.onEvnDirectionSave == 'function')
					{
						form.onEvnDirectionSave(data);
					}
					else
					{
						form.getItem().focus();
					}
				};

				var onHide = function() {
					var form = Ext.getCmp('swMPRecordWindow');
					form.getItem().focus();
				};
				break;
		}
		getWnd('swEvnDirectionEditWindow').show({
			action: 'add',
			callback: onEvnDirectionSave,
			onHide: onHide,
			Person_id: form.params.Person_id,
			Person_Surname: form.params.Person_Surname,
			Person_Firname: form.params.Person_Firname,
			Person_Secname: form.params.Person_Secname,
			Person_Birthday: form.params.Person_Birthday,
			is_cito: options.is_cito || false,
			formParams: formParams
		});
	},
	/*
	scheduleCopy: function()
	{
		alert('Ctrl+C');
	},
	schedulePaste: function()
	{
		alert('Ctrl+V');
	},
	
	scheduleRefresh:function()
	{
		var params = new Object();
		params.MedStaffFact_id = getGlobalOptions().msf_id;
		this.getItem().loadStore(params);
	},
	schedulePrint:function()
	{
		Ext.ux.GridPrinter.print(this.getItem());
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
		this.getItem().getView().collapseAllGroups();
	},
	scheduleExpandDates: function() {
		this.getItem().getView().expandAllGroups();
	},
	*/
	getFilterButton: function(id)
	{
		//log('Вызов: getFilterButton');
		return this.Filters.getBottomToolbar().items.get(id);
	},
	setMyTitle: function()
	{
		// Вначале формируем заголовок WND_WPR
		var title = 'Запись пациента: <font color="blue">'+this.params.Person_Surname+' '+this.params.Person_Firname+' '+this.params.Person_Secname+'</font>';
		//log(this.params);
		if ((this.params) && (this.params.Lpu_Nick))
		{
			title = title + " / " + this.params.Lpu_Nick;
			if (this.params.LpuUnit_Name)
				title = title + " - " + this.params.LpuUnit_Name;
			if (this.params.MedPersonal_FIO)
				title = title + " / "+lang['vrach']+ " " + this.params.MedPersonal_FIO;
			if (this.params.LpuSection_Name)
				title = title + " / " +lang['otdelenie']+ " " + this.params.LpuSection_Name;
			if (this.params.MedService_Nick) {
				if ( !this.params.UslugaComplex_id ) {
					title = title + " / "+lang['slujba']+ " " + this.params.MedService_Nick;
				} else {
					title = title + " / "+lang['slujba']+ " " + this.params.MedService_Nick + lang['usluga'] + this.params.UslugaComplex_Name;
				}	
			}
		}
		this.setTitle(title);
	},
	getLoadMask: function(MSG)
	{
		if (MSG) 
		{
			delete(this.loadMask);
		}
		if (!this.loadMask)
		{
			this.loadMask = new Ext.LoadMask(Ext.get(this.id), { msg: MSG });
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
					frm.scheduleLoad('day');
					frm.getLoadMask().hide();
				}
				else
				{
					frm.getLoadMask().hide();
				}
			}
		});
	},
	listeners: 
	{
		hide: function()
		{
			this.returnFunc(this.owner, -1);
		},
		beforeshow: function()
		{
			
		}
	},
	show: function()
	{
		sw.Promed.swMPRecordWindow.superclass.show.apply(this, arguments);
		var lpu_filter = this.Filters.findById('mprwLpu_id');
		var medpers_filter = this.Filters.findById('mprwMedPersonal_id');
		var lpuunittype_filter = this.Filters.findById('mprwLpuUnitType_id');
		var lpusectionprofile_filter = this.Filters.findById('mprwLpuSectionProfile_id');
		// Проверяем права пользователя открывшего форму 
		// Форма может открываться с разных мест, 
		// formMode='direction' - означает открытие из формы редактирования направления  
		// formMode='jodirection' - означает открытие из формы журнала направлений
		// formMode='vizit_PL' - означает открытие из формы посещения полки 
		// formMode='workplace' - означает открытие из формы места работы врача
		// formMode='mpqueue_rec' - означает открытие из очереди по профилю, для записи
		// formMode='mpqueue_redir' - означает открытие из очереди по профилю, для перенаправления
		// formMode='top_timetable' - означает открытие из списка наиболее часто используемых расписаний
		// formMode='evn_prescr' - означает открытие из списка назначений
		if ((arguments[0]) && (arguments[0].formMode))
		{
			delete (this.params);
			this.params = {}; 
			this.params.Person_id = arguments[0].Person_id;
			this.params.Server_id = arguments[0].Server_id;
			this.params.PersonEvn_id = arguments[0].PersonEvn_id;
			this.params.Person_Surname = arguments[0].Person_Surname;
			this.params.Person_Firname = arguments[0].Person_Firname;
			this.params.Person_Secname = arguments[0].Person_Secname;
			this.params.Person_Birthday = arguments[0].Person_Birthday;		
			this.formMode = arguments[0].formMode;
			if ('jodirection' == this.formMode)
			{
				this.params.EvnDirection_pid = arguments[0].EvnDirection_pid;
				this.params.EvnDirection_id = arguments[0].EvnDirection_id;
			}
			if ('evn_prescr' == this.formMode)
			{
				// если 
				this.params.EvnPrescr_id = arguments[0].EvnPrescr_id;
				this.params.PrescriptionType_Code = arguments[0].PrescriptionType_Code;
				this.params.parentEvnClass_SysNick = arguments[0].parentEvnClass_SysNick;
				this.params.EvnDirection_rid = arguments[0].EvnDirection_rid;
				this.params.Lpu_id = arguments[0].Lpu_id;
				if(this.params.PrescriptionType_Code == '4') {
					this.params.LpuSectionProfile_id = arguments[0].LpuSectionProfile_id;
				} else {
					this.params.uslugaList = arguments[0].uslugaList;
					this.params.LpuUnitType_id = arguments[0].LpuUnitType_id;
					this.params.LpuUnitType_SysNick = arguments[0].LpuUnitType_SysNick;
					this.params.UslugaComplex_id = arguments[0].UslugaComplex_id || null;
					// ссылка на назначенную услугу
					this.params.UslugaComplex_prescid = arguments[0].UslugaComplex_id || null;
				}
				if(this.params.PrescriptionType_Code == '6') {
					this.params.MedServiceType_SysNick = 'prock'; // фильтр по службам заданного типа
				}
				/*
				this.params.MedService_id = arguments[0].MedService_id;
				this.params.MedServiceType_SysNick = arguments[0].MedServiceType_SysNick;
				this.params.Lpu_id = arguments[0].Lpu_id;
				this.params.LpuUnit_id = arguments[0].LpuUnit_id;
				this.params.LpuUnitType_id = arguments[0].LpuUnitType_id;
				this.params.LpuUnitType_SysNick = arguments[0].LpuUnitType_SysNick;
				this.params.LpuSection_id = arguments[0].LpuSection_id;
				this.params.LpuSectionProfile_id = arguments[0].LpuSectionProfile_id;
				this.params.LpuBuilding_id = arguments[0].LpuBuilding_id;
				this.params.MedService_Nick = arguments[0].MedService_Nick;
				this.params.UslugaComplex_Name = arguments[0].UslugaComplex_Name;
				this.params.UslugaComplex_id = arguments[0].UslugaComplex_id;
				*/
			}
			this.params.EvnDirection_pid = arguments[0].EvnDirection_pid || null;//посещение
			this.params.Diag_id = arguments[0].Diag_id || null;//основной диагноз посещения
			this.params.mode = arguments[0].mode;
			this.setMyTitle();
			this.onEvnDirectionSave = arguments[0].onEvnDirectionSave ? arguments[0].onEvnDirectionSave : null;
			this.onSheduleSave = arguments[0].onSheduleSave ? arguments[0].onSheduleSave : null;
			this.onSaveRecord = arguments[0].onSaveRecord || Ext.emptyFn;
			this.owner = arguments[0].owner || null;
			this.returnFunc = arguments[0].onHide || Ext.emptyFn;
			this.ARMType = arguments[0].ARMType || null;
			if (arguments[0].userMedStaffFact)
			{
				this.userMedStaffFact = arguments[0].userMedStaffFact;
			} else {
				if( IS_DEBUG && this.ARMType != 'regpol')
				{
					Ext.Msg.alert('Уведомление', 'Ошибка открытия формы "'+this.title+'".<br/>Не указаны параметры АРМа врача.');
				}
				// тут форма может работать неправильно при переключении между несколькими рабочими местами
				this.userMedStaffFact = {
					MedStaffFact_id: getGlobalOptions().CurMedStaffFact_id || null,
					LpuSection_id: getGlobalOptions().CurLpuSection_id || null,
					MedPersonal_id: getGlobalOptions().CurMedPersonal_id || null,
					LpuSectionProfile_id: getGlobalOptions().CurLpuSectionProfile_id || null,
					LpuUnitType_id: getGlobalOptions().CurLpuUnitType_id || null,
					LpuSection_Name: getGlobalOptions().CurLpuSection_Name || '',
					MedPersonal_FIO: getGlobalOptions().CurMedPersonal_FIO || ''
				};
			}
			this.params.UserMedStaffFact_id = this.userMedStaffFact.MedStaffFact_id;
		}
		else 
		{
			this.hide();
			Ext.Msg.alert(lang['oshibka'], lang['ne_ukazan_rejim_otkryitiya_formyi']);
		}
		
		var mode;
		if ('top_timetable' == this.formMode && arguments[0].top_timetable_params)
		{
			var top_tt_params = arguments[0].top_timetable_params;
			lpu_filter.setValue([top_tt_params.Lpu_id]);
			lpu_filter.setValue.defer(100, lpu_filter, [top_tt_params.Lpu_id]);
			switch (top_tt_params.TimetableObject_id)
			{
				case '1':
					mode = 'SchedulePolka';
					if (top_tt_params.MedPersonal_id && top_tt_params.MedStaffFact_id)
					{
						this.params.MedPersonal_id = top_tt_params.MedPersonal_id;
						this.params.MedStaffFact_id = top_tt_params.MedStaffFact_id;
						this.params.LpuSectionProfile_id = top_tt_params.LpuSectionProfile_id;
						this.params.MedPersonal_FIO = top_tt_params.MedPersonal_FIO;
						this.params.Lpu_id = top_tt_params.Lpu_id;
						this.params.LpuUnitType_SysNick = 'polka';
						medpers_filter.getStore().load({
							params:
							{
								Lpu_id: lpu_filter.getValue()
							},
							callback: function() {
								medpers_filter.setValue(top_tt_params.MedPersonal_id);
							}
						});
					}
					break;
				case '2':
					mode = 'ScheduleStac';
					if (top_tt_params.LpuSection_id)
					{
						this.params.LpuSection_id = top_tt_params.LpuSection_id;
						this.params.LpuSectionProfile_id = top_tt_params.LpuSectionProfile_id;
						this.params.LpuSection_Name = top_tt_params.LpuSection_Name;
						this.params.LpuUnitType_SysNick = 'stac';
						medpers_filter.setValue(null);
					}
					break;
				case '3':
					mode = 'ScheduleMedService';
					if (top_tt_params.LpuSection_id)
					{
						this.params.LpuSection_id = top_tt_params.LpuSection_id;
						this.params.MedService_id = top_tt_params.MedService_id;
						this.params.UslugaComplex_id = top_tt_params.UslugaComplex_id;
						this.params.MedService_Name = top_tt_params.MedService_Name;
						this.params.LpuUnitType_SysNick = 'parka';
						medpers_filter.setValue(null);
					}
					break;
			}
			this.params.Lpu_Nick = top_tt_params.Lpu_Nick;
			this.setMyTitle();
			lpusectionprofile_filter.setValue( [top_tt_params.LpuSectionProfile_id] );
			lpuunittype_filter.setValue(null);
			this.Wizard.Panel.layout.setActiveItem(mode);
			this.getItem().removeAll();
			Ext.getCmp('mprwIsThis').disable();
			Ext.getCmp('mprwBtnMPSearch').disable();
			Ext.getCmp('mprwBtnMPClear').disable();
		}
		if ('evn_prescr' == this.formMode)
		{
			lpu_filter.setValue(this.params.Lpu_id);
			lpu_filter.setValue.defer(100, lpu_filter, [this.params.Lpu_id]);
			medpers_filter.setValue(null);
			if(this.params.PrescriptionType_Code == '4') {
				lpuunittype_filter.setValue(null);
				lpusectionprofile_filter.setValue([this.params.LpuSectionProfile_id]);
				mode = 'Lpu';
			} else {
				lpuunittype_filter.setValue(this.params.LpuUnitType_id);
				lpusectionprofile_filter.setValue(null);
				mode = 'LpuUnitCard';
			}
			this.Wizard.Panel.layout.setActiveItem(mode);
			this.getItem().removeAll();
			this.setMyTitle();
			this.is_firstLpuUnitLoad = true;
		}
		//this.Wizard.MedServiceLpu.setVisible('evn_prescr' != this.formMode);
		//this.Wizard.MedService.setVisible('evn_prescr' != this.formMode);
		this.syncSize();

		if (!mode)
		{
			this.Wizard.Panel.layout.setActiveItem('Lpu');
			this.getItem().removeAll();
			this.Wizard.MedServiceLpu.removeAll();
			this.Filters.clear(false);
			Ext.getCmp('mprwIsThis').enable();
			Ext.getCmp('mprwBtnMPSearch').enable();
			Ext.getCmp('mprwBtnMPClear').enable();
			// по умолчанию просто к ЛПУ
			lpu_filter.setValue( [getGlobalOptions().lpu_id] );
			lpu_filter.setValue.defer(100, lpu_filter, [getGlobalOptions().lpu_id]);
			lpu_filter.fireEvent('change', lpu_filter, getGlobalOptions().lpu_id);
			//можно так, не будет повторного запроса к серваку (не срабатывает метод saveIsThis)
			Ext.getCmp('mprwIsThis').toggle(false, true);
			Ext.getCmp('mprwIsThis').setText('<b style="font-color:#555;">Записать к себе</b>');
			lpu_filter.setDisabled(false);
			medpers_filter.setDisabled(false);
			lpusectionprofile_filter.setDisabled(false);
			lpuunittype_filter.setDisabled(false);
			Ext.getCmp('mprwBtnMPClear').enable();
		}
		
		
		
		if (arguments[0].formMode && arguments[0].formMode == 'mpqueue_rec') { //настройка для перехода с окна очереди по профилю
			//if (!isSuperAdmin()) {
				this.Wizard.Panel.layout.setActiveItem('LpuUnitCard');
				this.Wizard.MedPersonal.removeAll();
				if ( this.ARMType == 'regpol' ) {
					Ext.getCmp('mprwBtnMPSearch').enable();
					lpu_filter.disable();
				}
				else {
					Ext.getCmp('mprwBtnMPSearch').disable();
					Ext.getCmp('mprwIsThis').fireEvent('toggle', Ext.getCmp('mprwIsThis'), true);
				}
			
				if (!Ext.isEmpty(arguments[0].LpuSectionProfile_id)) {
					lpusectionprofile_filter.setValue(arguments[0].LpuSectionProfile_id);
				}

				Ext.getCmp('mprwIsThis').disable();
				// Ext.getCmp('mprwBtnMPSearch').disable();
			//}
		}
		this.getFilterButton('mprwBtnMPPrevious').disable();
		this.getFilterButton('mprwBtnMPNext').disable();
		//this.Filters.findById('mprwLpuSectionProfile_id').focus(true, 100);
		// Обнулим значения фильтра
		this.getCurrentDateTime();
		
		//this.syncSize();
		if (arguments[0].formMode && (arguments[0].formMode == 'mpqueue_rec' || arguments[0].formMode == 'mpqueue_redir')) {
			this.params.EvnQueue_id = arguments[0].EvnQueue_id || null;// EvnQueue_id
			this.params.QueueFailCause_id = (arguments[0].formMode == 'mpqueue_redir')?7:null; //при редиректе закрываем запись в очереди
			//Ext.getCmp('mprwWizardSchedulePolka').ViewActions['action_add'].hide();
			//Ext.getCmp('mprwWizardScheduleStac').ViewActions['action_add'].hide();
			//Ext.getCmp('mprwWizardScheduleMedService').ViewActions['action_add'].hide();
		} else {
			this.params.EvnQueue_id = null;
			this.params.QueueFailCause_id = null;
			//Ext.getCmp('mprwWizardSchedulePolka').ViewActions['action_add'].show();
			//Ext.getCmp('mprwWizardScheduleStac').ViewActions['action_add'].show();
			//Ext.getCmp('mprwWizardScheduleMedService').ViewActions['action_add'].show();
		}
		
		
		if (arguments[0].isThis)
		{
			// Функция "к себе"
			Ext.getCmp('mprwIsThis').toggle(true, false);
		} 
		
		if (arguments[0].fromEmk) {
			this.fromEmk = true;
		} else {
			this.fromEmk = false;
		}
	},
  initComponent: function()
	{
		var wnd = this;
		
		this.Filters = new Ext.Panel(
		{
			region: 'north',
			border: false,
			frame: true,
			//defaults: {bodyStyle:'background:#DFE8F6;'},
			autoHeight: true,
			//style: 'padding: 5px;',
			bbar:
			[{
				tabIndex: TABINDEX_MPRW+5,
				xtype: 'button',
				id: 'mprwBtnMPSearch',
				text: lang['nayti'],
				iconCls: 'search16',
				handler: function()
				{
					var form = Ext.getCmp('swMPRecordWindow');
					form.scheduleLoad();
				}
			},
			{
				tabIndex: TABINDEX_MPRW+6,
				xtype: 'button',
				id: 'mprwBtnMPClear',
				text: lang['sbros'],
				iconCls: 'resetsearch16',
				handler: function()
				{
					var form = Ext.getCmp('swMPRecordWindow');
					// Очистка полей фильтра И перезагрузка
					form.Filters.clear(true);
				}
			},
			{
				xtype: 'tbseparator'
			},
			{
				tabIndex: TABINDEX_MPRW+7,
				xtype: 'button',
				id: 'mprwBtnMPPrevious',
				disabled: true,
				text: lang['nazad'],
				iconCls: 'arrow-previous16',
				handler: function()
				{
					var form = Ext.getCmp('swMPRecordWindow');
					var isthis_btn = Ext.getCmp('mprwIsThis');
					if(isthis_btn.pressed)
					{
						// отменяем запись к себе 
						isthis_btn.toggle(false, true);
						isthis_btn.setText('<b style="font-color:#555;">Записать к себе</b>');
						form.Filters.findById('mprwMedPersonal_id').setValue(null);
					}
					// Возврат назад
					var mode = form.Wizard.Panel.layout.activeItem.id;
					
					switch (mode)
					{
						case 'Lpu':
							break;
						case 'LpuUnitCard':
							form.scheduleOpen('Lpu');
							break;
						case 'SchedulePolka': case 'ScheduleStac': case 'ScheduleMedService':
							form.scheduleOpen('LpuUnitCard');
							break;
						case 'ScheduleMedService':
							if(form.params.LpuUnit_id)
								form.scheduleOpen('LpuUnitCard');
							else
								form.scheduleOpen('Lpu');
							break;
					}
				}
			},
			{
				tabIndex: TABINDEX_MPRW+7,
				xtype: 'button',
				id: 'mprwBtnMPNext',
				text: lang['vpered'],
				disabled: true,
				iconCls: 'arrow-next16',
				handler: function()
				{
					var form = Ext.getCmp('swMPRecordWindow');
					// Очистка полей фильтра без перезагрузки
					form.Filters.clear(false);
					// И возврат на первую ? возможно 
				}
			},
			{
				xtype: 'tbseparator'
			},
			{
				enableToggle: true,
				xtype: 'button',
				name: 'IsThis',
				id: 'mprwIsThis',
				text: '<b style="font-color:#555;">Записать к себе</b>',
				listeners: 
				{
					toggle: function(button, check)
					{
						var form = Ext.getCmp('swMPRecordWindow');
						form.saveIsThis(button, check);
					}
				}
			}],
			items:
			[{
				xtype: 'form',
				autoHeight: true,
				layout: 'column',
				items: 
				[{
					layout: 'form',
					columnWidth: .35,
					labelAlign: 'right',
					labelWidth: 125,
					items: 
					[{
						fieldLabel: lang['profil'],
						anchor:'100%',
						tabIndex: TABINDEX_MPRW+1,
						hiddenName: 'LpuSectionProfile_id',
						id: 'mprwLpuSectionProfile_id',
						lastQuery: '',
						width : 300,
						xtype: 'swlpusectionprofilecombo',
						listeners: 
						{
							change: function(combo, nv, ov)
							{
								var form = Ext.getCmp('swMPRecordWindow');
								/*
								form.Filters.findById('mprwMedPersonal_id').getStore().load(
								{
									params:
									{
										Lpu_id: nv
									},
									callback: function()
									{
										form.Filters.findById('mprwMedPersonal_id').setValue('');
									}
								});
								*/
							},
							'keydown': function (inp, e) 
							{
								var form = Ext.getCmp('swMPRecordWindow');
								if (e.getKey() == Ext.EventObject.ENTER)
								{
									e.stopEvent();
									
									form.scheduleLoad();
								}
							}
						}
					},
					{
						fieldLabel: lang['tip_podrazdeleniya'],
						tabIndex: TABINDEX_MPRW+2,
						anchor:'100%',
						hiddenName: 'LpuUnitType_id',
						id: 'mprwLpuUnitType_id',
						lastQuery: '',
						xtype: 'swlpuunittypecombo',
						listeners: 
						{
							'keydown': function (inp, e) 
							{
								var form = Ext.getCmp('swMPRecordWindow');
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
					columnWidth: .35,
					labelAlign: 'right',
					labelWidth: 60,
					items: 
					[{
						fieldLabel: lang['lpu'],
						allowBlank: true,
						anchor:'100%',
						tabIndex: TABINDEX_MPRW+3,
						hiddenName: 'Lpu_id',
						id: 'mprwLpu_id',
						lastQuery: '',
						width : 300,
						//typeAhead: true,
						xtype: 'swlpulocalcombo',
						listeners: 
						{
							change: function(combo, nv, ov)
							{
								var form = Ext.getCmp('swMPRecordWindow');
								form.Filters.findById('mprwMedPersonal_id').getStore().load(
								{
									params:
									{
										Lpu_id: nv
									},
									callback: function()
									{
										form.Filters.findById('mprwMedPersonal_id').setValue('');
									}
								});
							},
							'keypress': function (inp, e) 
							{
								var form = Ext.getCmp('swMPRecordWindow');
								if (e.getKey() == Ext.EventObject.ENTER)
								{
									e.stopEvent();
									form.scheduleLoad();
								}
							}
						}
					},
					{
						fieldLabel: lang['vrach'],
						tabIndex: TABINDEX_MPRW+4,
						allowBlank: true,
						anchor:'100%',
						editable: true,
						hiddenName: 'MedPersonal_id',
						id: 'mprwMedPersonal_id',
						lastQuery: '',
						listWidth: 420,
						xtype: 'swmedpersonalcombo',
						listeners: 
						{
							'keydown': function (inp, e) 
							{
								var form = Ext.getCmp('swMPRecordWindow');
								if (e.getKey() == Ext.EventObject.ENTER)
								{
									e.stopEvent();
									
									form.scheduleLoad();
								}
							}
						}
					}]
				}]
			}],
			clear: function(scheduleLoad)
			{
				// возвращаемся к состоянию при открытии формы
				var form = Ext.getCmp('swMPRecordWindow');
				var filters = this;
				form.Wizard.Panel.layout.setActiveItem('Lpu');
				form.Wizard.Lpu.removeAll({clearAll: true});
				form.Wizard.MedServiceLpu.removeAll({clearAll: true});
				form.getFilterButton('mprwBtnMPPrevious').disable();
				form.getFilterButton('mprwBtnMPNext').disable();
				form.params.LpuSectionProfile_id = '';
				filters.findById('mprwLpuSectionProfile_id').setValue(null);
				form.params.LpuUnit_id = '';
				form.params.LpuUnitType_SysNick = '';
				filters.findById('mprwLpuUnitType_id').setValue(null);
				form.params.Lpu_id = getGlobalOptions().lpu_id;
				form.params.MedPersonal_id = '';
				if ( filters.findById('mprwLpu_id').getValue() != getGlobalOptions().lpu_id )
				{
					filters.findById('mprwLpu_id').setValue(getGlobalOptions().lpu_id);
					filters.findById('mprwMedPersonal_id').getStore().load({
						params:
						{
							Lpu_id: getGlobalOptions().lpu_id
						},
						callback: function() {
							form.findById('mprwMedPersonal_id').setValue(null);
							if (scheduleLoad)
							{
								form.scheduleLoad();
							}
						}
					});
				}
				else
				{
					form.findById('mprwMedPersonal_id').setValue(null);
					if (scheduleLoad)
						form.scheduleLoad();
				}
			}
		});
		
		
		this.Wizard = {Lpu:null, LpuUnit: null, LpuSection: null, MedPersonal: null};
		
		this.Wizard.Lpu = new sw.Promed.ViewFrame(
		{
			id: 'mprwWizardLpu',
			region: 'center',
			object: 'Timetable',
			border: true,
			dataUrl: '/?c=TimetableGraf&m=getListTimetableLpu',
			toolbar: true,
			autoLoadData: false,
			paging: true,
			root: 'data',
			totalProperty: 'totalCount',
			stringfields:
			[
				{name: 'LpuUnit_id', type: 'int', header: 'ID', key: true},
				{name: 'Lpu_id', hidden: true, isparams: true},
				{name: 'Lpu_Nick', width: 280, header: lang['lpu']},
				{name: 'LpuUnit_Name', id: 'autoexpand', header: lang['podrazdelenie']},
				{name: 'LpuUnit_Address', width: 250, header: lang['adres']},
				{name: 'FreeTime', width: 180, header: lang['pervoe_svobodnoe_vremya']}
			],
			actions:
			[
				{name:'action_add', disabled: true, hidden: true},
				{name:'action_edit', handler: function() {this.scheduleOpen('LpuUnitCard');}.createDelegate(this), text: lang['vyibrat'], tooltip: lang['vyibor_zapisi']},
				{name:'action_view', disabled: true, hidden: true},
				{name:'action_delete', disabled: true, hidden: true}
			],
			/*
			onDblClick: function()
			{
				var form = Ext.getCmp('swMPRecordWindow');
				form.scheduleOpen('LpuUnitCard');
			},
			onEnter: function ()
			{
				var form = Ext.getCmp('swMPRecordWindow');
				form.scheduleOpen('LpuUnitCard');
			},
			*/
			onRowSelect: function (sm,index,record)
			{
				//var form = Ext.getCmp('swMPRecordWindow');
			}
		});
		
		this.Wizard.LpuUnit = new sw.Promed.ViewFrame(
		{
			id: 'mprwWizardLpuUnit',
			region: 'north',
			object: 'LpuUnit',
			border: true,
			height: 170,
			dataUrl: '/?c=TimetableGraf&m=getListTimetableLpuUnit',
			toolbar: true,
			autoLoadData: false,
			/*
			paging: true,
			root: 'data',
			totalProperty: 'totalCount',
			*/
			stringfields:
			[
				{name: 'LpuUnit_id', type: 'int', header: 'ID', key: true},
				{name: 'Lpu_id', hidden: true, isparams: true},
				{name: 'LpuUnitType_SysNick', hidden: true, isparams: true},
				{name: 'LpuUnit_Name', id: 'autoexpand', header: lang['podrazdelenie']},
				{name: 'Address_Address', width: 250, header: lang['adres']},
				{name: 'LpuUnit_Phone', width: 250, header: lang['telefonyi']},
				{name: 'FreeTime', width: 180, header: lang['pervoe_svobodnoe_vremya']}
			],
			actions:
			[
				{name:'action_add', disabled: true, hidden: true},
				{name:'action_edit', handler: function() {this.Wizard.LpuUnit.selectLpuUnit();}.createDelegate(this), text: lang['vyibrat'], tooltip: lang['vyibor_zapisi']},
				{name:'action_view', disabled: true, hidden: true},
				{name:'action_delete', disabled: true, hidden: true}
			],
			onLoadData: function(isData)
			{
				// После того как загрузили данные, надо выбрать ту надпись которая выбрана (form.params.LpuUnit_id)
				var form = Ext.getCmp('swMPRecordWindow');
				if ((form.params.LpuUnit_id) && (isData))
				{
					GridAtRecord(this.getGrid(), 'LpuUnit_id', form.params.LpuUnit_id);
					this.selectLpuUnit();
				}
				if (form.is_firstLpuUnitLoad)
				{
					this.getGrid().getSelectionModel().selectFirstRow();
					//this.getGrid().getView().focusRow(0); 
					this.selectLpuUnit();
					form.is_firstLpuUnitLoad = false;
				}
			},
			onRowSelect: function (sm,index,record)
			{
				//this.selectLpuUnit();
			},
			selectLpuUnit: function()
			{

				var record = this.Wizard.LpuUnit.getGrid().getSelectionModel().getSelected();
				
				var params = {};
				params.Lpu_id = this.findById('mprwLpu_id').getValue() || this.params.Lpu_id;
				params.MedPersonal_id = this.findById('mprwMedPersonal_id').getValue();
				params.LpuSectionProfile_id = this.findById('mprwLpuSectionProfile_id').getValue();
				params.Filter_LpuSectionProfile_id = this.findById('mprwLpuSectionProfile_id').getValue();
				params.LpuUnitType_id = this.findById('mprwLpuUnitType_id').getValue();
				if (wnd.params.MedServiceType_SysNick) {
					params.MedServiceType_SysNick = wnd.params.MedServiceType_SysNick;
				}
				params.start = 0; 
				params.limit = 100;
				
				if (record)
				{
					this.params.LpuUnit_id = record.get('LpuUnit_id');
					this.params.LpuUnit_Name = record.get('LpuUnit_Name');
					params.LpuUnit_id = this.params.LpuUnit_id;
					this.params.LpuUnitType_SysNick = record.get('LpuUnitType_SysNick');
					params.LpuUnitType_SysNick = record.get('LpuUnitType_SysNick');
					if (record.get('LpuUnitType_SysNick') == 'polka')
					//if (record.get('LpuUnit_id').inlist([237, 3]))
					{
						// Поликлиника
						this.Wizard.LpuUnitType.layout.setActiveItem(0);
						this.Wizard.MedPersonal.loadData({globalFilters:params});
					}
					else if (record.get('LpuUnitType_SysNick') == 'parka')
					{
						// Параклиника
						this.Wizard.LpuUnitType.layout.setActiveItem(2);
						this.Wizard.MedService.loadData({globalFilters:params});
					}
					else
					{
						// стационар
						this.Wizard.LpuUnitType.layout.setActiveItem(1);
						this.Wizard.LpuSection.loadData({globalFilters:params});
					}
					//log(record);
					
					this.Wizard.LpuUnit.getGrid().getStore().each(function(r) 
					{
						r.commit();
					});
					this.setMyTitle();
				}
			}.createDelegate(this)
		});
		this.Wizard.LpuUnit.getGrid().view = new Ext.grid.GridView(
		{
			getRowClass : function (row, index)
			{
				var cls = '';
				if (row.get('LpuUnit_id') == Ext.getCmp('swMPRecordWindow').params.LpuUnit_id)
					cls = cls+'x-grid-rowselect x-grid-rowbackgreen ';
				if (cls.length == 0)
					cls = 'x-grid-panel'; 
				return cls;
			}
		});
		
		
		this.Wizard.MedPersonal = new sw.Promed.ViewFrame(
		{
			id: 'mprwWizardMedPersonal',
			region: 'center',
			object: 'MedPersonal',
			border: true,
			dataUrl: '/?c=Reg&m=getRecordMedPersonalList',
			toolbar: false,
			autoLoadData: false,

			stringfields:
			[
				{name: 'MedStaffFact_id', type: 'int', header: 'ID', key: true},
				{name: 'LpuSection_id', hidden: true, isparams: true},
				{name: 'LpuUnit_id', hidden: true, isparams: true},
				{name: 'Lpu_id', hidden: true, isparams: true},
				{name: 'MedPersonal_id', hidden: true, isparams: true},
				{name: 'LpuSection_id', hidden: true},
				{name: 'LpuSectionProfile_id', hidden: true, isparams: true},
				{name: 'Comments', width: 24, header: '...'},
				{name: 'MedPersonal_FIO', id: 'autoexpand', header: lang['vrach']},
				{name: 'LpuSectionProfile_Name', width: 250, header: lang['profil']},
				{name: 'Queue', width: 80, header: lang['ochered']},
				{name: 'LpuRegion_Names', width: 100, header: lang['uchastki']},
				{name: 'Dates', width: 500, header: 'Даты приёма'}
			],
			actions:
			[
				{name:'action_add', disabled: true, hidden: true},
				{name:'action_edit', text: lang['vyibrat'], handler: function() {this.scheduleOpen('SchedulePolka');}.createDelegate(this) },
				{name:'action_view', disabled: true, hidden: true},
				{name:'action_delete', disabled: true, hidden: true}
			],
			onLoadData: function(isData)
			{
				//Нужно вернуть фокус в грид Wizard.LpuUnit на подразделение
				var lpu_unit_grid = this.Wizard.LpuUnit.getGrid();
				var lpu_unit_record = lpu_unit_grid.getSelectionModel().getSelected();
				if (lpu_unit_record)
				{
					lpu_unit_grid.getView().focusRow(lpu_unit_grid.getStore().indexOf(lpu_unit_record)); 
				}
			}.createDelegate(this),
			/**
			 * Получение значение переданного поля текущей выбранной записи
			 */
			getSelectedParam: function(field) {
				var rec = this.getGrid().getSelectionModel().getSelected();
				if (rec)
					return rec.get(field);
				else
					return false;
			}
		});
		
		
		this.Wizard.LpuSection = new sw.Promed.ViewFrame(
		{
			id: 'mprwWizardLpuSection',
			region: 'center',
			object: 'LpuSection',
			border: true,
			dataUrl: '/?c=Reg&m=getRecordLpuSectionList',
			toolbar: false,
			autoLoadData: false,

			stringfields:
			[
				{name: 'LpuSection_id', type: 'int', header: 'ID', key: true},
				{name: 'LpuUnit_id', hidden: true, isparams: true},
				{name: 'Lpu_id', hidden: true, isparams: true},
				{name: 'LpuSectionProfile_id', hidden: true, isparams: true},
				{name: 'Comments', width: 24, header: '...'},
				{name: 'LpuSection_Name', id: 'autoexpand', header: lang['otdelenie']},
				{name: 'LpuSectionProfile_Name', width: 250, header: lang['profil']},
				{name: 'Queue', width: 80, header: lang['ochered']},
				{name: 'LpuSectionType_id', width: 100, header: lang['tip']},
				{name: 'Dates', width: 500, header: 'Даты приёма'}
			],
			actions:
			[
				{name:'action_add', disabled: true, hidden: true},
				{name:'action_edit', text: lang['vyibrat'], handler: function() {this.scheduleOpen('ScheduleStac');}.createDelegate(this) },
				{name:'action_view', disabled: true, hidden: true},
				{name:'action_delete', disabled: true, hidden: true}
			],
			onLoadData: function(isData)
			{
				//
			}.createDelegate(this),
			/**
			 * Получение значение переданного поля текущей выбранной записи
			 */
			getSelectedParam: function(field) {
				var rec = this.getGrid().getSelectionModel().getSelected();
				if (rec)
					return rec.get(field);
				else
					return false;
			}
		});
		
		this.Wizard.MedService = new sw.Promed.ViewFrame(
		{
			id: 'mprwWizardMedService',
			region: 'center',
			object: 'MedService',
			border: true,
			dataUrl: '/?c=Reg&m=getRecordMedServiceList',
			toolbar: false,
			autoLoadData: false,

			stringfields:
			[
				{name: 'MedService_id', type: 'int', header: 'ID', key: true},
				{name: 'UslugaComplex_id', type: 'int', header: 'ID', key: true},
				{name: 'UslugaComplexMedService_id', hidden: true, isparams: true},
				{name: 'LpuSection_id', hidden: true, isparams: true},
				{name: 'LpuSectionProfile_id', hidden: true},
				{name: 'LpuUnit_id', hidden: true, isparams: true},
				{name: 'Lpu_id', hidden: true, isparams: true},
				{name: 'MedServiceType_id', hidden: true, isparams: true},
				{name: 'MedServiceType_SysNick', hidden: true},
				{name: 'MedService_Nick', hidden: true},
				{name: 'MedService_Name', width: 200, header: lang['slujba']},
				{name: 'UslugaComplex_Name', id: 'autoexpand', header: lang['usluga']},
				{name: 'Queue', width: 80, header: lang['ochered']},
				{name: 'Dates', width: 500, header: 'Даты приёма'}
			],
			actions:
			[
				{name:'action_add', disabled: true, hidden: true},
				{name:'action_edit', text: lang['vyibrat'], handler: function() {this.addOrder();}.createDelegate(this) },
				{name:'action_view', disabled: true, hidden: true},
				{name:'action_delete', disabled: true, hidden: true}
			],
			onLoadData: function()
			{
				if (this.is_firstUslugaComplexLoad)
				{
					var index = -1;
					if (this.params.UslugaComplex_id) {
						index = this.Wizard.MedService.getGrid().getStore().findBy(function(rec) { return rec.get('UslugaComplex_id') == this.params.UslugaComplex_id; }.createDelegate(this));
					}
					if(index < 0) index = 0;
					this.Wizard.MedService.getGrid().getSelectionModel().selectRow(index);
					this.Wizard.MedService.getGrid().getView().focusRow(index); 
					this.is_firstUslugaComplexLoad = false;
				}
			}.createDelegate(this),
			/**
			 * Получение значение переданного поля текущей выбранной записи
			 */
			getSelectedParam: function(field) {
				var rec = this.getGrid().getSelectionModel().getSelected();
				if (rec)
					return rec.get(field);
				else
					return false;
			}
		});

		// Запись в поликлинику к врачу
		this.Wizard.SchedulePolka = new sw.Promed.swTTGRecordPanel({
			id:'SchedulePolka',
			frame: false,
			border: false,
			region: 'center',
			recordPerson: function(time_id, tt_date, tt_time) {
				this.scheduleAdd(time_id, tt_date, tt_time);
			}.createDelegate(this),
			queuePerson: function() {
				this.scheduleQueue();
			}.createDelegate(this)
		});
		
		// Запись в стационар на койку
		this.Wizard.ScheduleStac = new sw.Promed.swTTSRecordPanel({
			id:'ScheduleStac',
			frame: false,
			border: false,
			region: 'center',
			recordPerson: function(time_id, tt_date, tt_time) {
				this.scheduleAdd(time_id, tt_date, tt_time);
			}.createDelegate(this),
			queuePerson: function() {
				this.scheduleQueue();
			}.createDelegate(this)
		});
		
		// Запись в параклинику на службу/услугу
		this.Wizard.ScheduleMedService = new sw.Promed.swTTMSRecordPanel({
			id:'ScheduleMedService',
			frame: false,
			border: false,
			region: 'center',
			recordPerson: function(time_id, tt_date, tt_time) {
				this.scheduleAdd(time_id, tt_date, tt_time);
			}.createDelegate(this),
			queuePerson: function() {
				this.scheduleQueue();
			}.createDelegate(this)
		});
		
		this.Wizard.ScheduleStac.getTopToolbar().on('render', function(vt){
			vt.insertButton(1,new Ext.Action({
				name:'action_cito_dir',
				id: 'id_action_cito_dir',
				handler: function() {
					this.openDirectionEditWindow({is_cito:true});
				}.createDelegate(this),
				text:lang['napravit_ekstrenno'],
				tooltip: lang['napravit_ekstrenno'],
				iconCls : 'x-btn-text',
				icon: 'img/icons/add16.png'
			}));
			return true;
		}, this);
		
		/*var med_service_conf = {
			id: 'mprwWizardMedService',
			region: 'south',
			height: 160,
			object: 'MedService',
			//border: false,
			dataUrl: '/?c=TimetableGraf&m=getListTimetableMedService',
			toolbar: true,
			autoLoadData: false,
			paging: true,
			root: 'data',
			totalProperty: 'totalCount',
			stringfields:
			[
				{name: 'UslugaComplex_id', type: 'int', header: 'ID', key: true},
				{name: 'uslugaList', type: 'string', hidden: true, isparams: true},
				{name: 'MedService_id', type: 'int', hidden: true, isparams: true},
				{name: 'Lpu_id', type: 'int', hidden: true, isparams: true},
				{name: 'LpuBuilding_id', type: 'int', hidden: true, isparams: true},
				{name: 'LpuUnitType_id', type: 'int', hidden: true, isparams: true},
				{name: 'LpuUnitType_SysNick', type: 'string', hidden: true},
				{name: 'LpuUnit_id', type: 'int', hidden: true, isparams: true},
				{name: 'LpuSection_id', type: 'int', hidden: true, isparams: true},
				{name: 'LpuSectionProfile_id', type: 'int', hidden: true, isparams: true},
				{name: 'MedServiceType_id', type: 'int', hidden: true, isparams: true},
				{name: 'MedServiceType_SysNick', type: 'string', hidden: true},
				{name: 'MedService_Nick', type: 'string', width: 70, header: lang['slujba']},
				{name: 'UslugaComplex_Name', type: 'string', id: 'autoexpand', header: lang['usluga']},
				{name: 'EvnQueue_Names', width: 80, header: lang['ochered']},
				{name: 'FreeTime', width: 180, header: lang['pervoe_svobodnoe_vremya']}
			],
			actions:
			[
				{name:'action_add', disabled: true, hidden: true},
				{name:'action_edit', handler: function() {
					var rec = this.Wizard.MedService.getGrid().getSelectionModel().getSelected();
					if(!rec)
						rec = this.Wizard.MedServiceLpu.getGrid().getSelectionModel().getSelected();
					if (rec) {
						var arguments = {
							MedService_id: rec.get('MedService_id'),
							MedService_Name: rec.get('MedService_Nick'),
							PersonEvn_id: this.params.PersonEvn_id,
							Server_id: this.params.Server_id,
							Person_id: this.params.Person_id
						};
						
						if(rec.get('MedService_Nick') == lang['vk']) {
							var params = this.params;
							arguments.onRecordPerson = function() {
								getWnd('swEvnDirectionVKWindow').show({
									Person_id: params.Person_id,
									PersonEvn_id: params.PersonEvn_id,
									Server_id: params.Server_id,
									Diag_id: params.Diag_id,
									EvnDirection_pid: params.EvnDirection_pid,
									TimetableMedService_id: this.TimetableMedService_id,
									MedService_id: this.MedService_id,
									onHide: function() {
										this.getLoadMask(lang['podojdite_sohranyaetsya_zapis']).show();
										Ext.Ajax.request({
											url: C_TTMS_APPLY,
											params: {
												TimetableMedService_id: this.TimetableMedService_id,
												Person_id: this.Person_id,
												Evn_id: params.EvnDirection_pid
											},
											callback: function(options, success, response) {
												this.loadSchedule();
												this.getLoadMask().hide();
												getWnd('swTTMSScheduleRecordWindow').hide();
											}.createDelegate(this),
											failure: function() {
												this.getLoadMask().hide();
											}
										});
									}.createDelegate(this)
								});
							};
							arguments.userClearTimeMS = function() {
								this.getLoadMask().hide();
								sw.swMsg.alert(lang['soobschenie'], lang['nelzya_udalyat_napravleniya']);
							};
						}
						
						getWnd('swTTMSScheduleRecordWindow').show(arguments);
					}
				}.createDelegate(this), text: lang['vyibrat'], tooltip: lang['vyibor_zapisi']},
				{name:'action_view', disabled: true, hidden: true},
				{name:'action_delete', disabled: true, hidden: true}
			],
			onLoadData: function()
			{
			},
			onRowSelect: function (sm,index,record)
			{
				//var form = Ext.getCmp('swMPRecordWindow');
				
			}
		};
		//this.Wizard.MedService = new sw.Promed.ViewFrame(med_service_conf);
		med_service_conf.id = 'mprwWizardMedServiceLpu';
		med_service_conf.height = 220;
		this.Wizard.MedServiceLpu = new sw.Promed.ViewFrame(med_service_conf);
		*/
		// службы уровня ЛПУ
		this.Wizard.MedServiceLpu = new sw.Promed.ViewFrame({
			id: 'mprwWizardMedServiceLpu',
			region: 'south',
			height: 220,
			object: 'MedService',
			border: true,
			dataUrl: '/?c=Reg&m=getRecordMedServiceList',
			toolbar: false,
			autoLoadData: false,

			stringfields:
			[
				{name: 'MedService_id', type: 'int', header: 'ID', key: true},
				{name: 'UslugaComplex_id', type: 'int', header: 'ID', key: true},
				{name: 'UslugaComplexMedService_id', hidden: true, isparams: true},
				{name: 'LpuSection_id', hidden: true},
				{name: 'MedServiceType_id', hidden: true },
				{name: 'MedServiceType_SysNick', type: 'string', hidden: true},
				{name: 'LpuSectionProfile_id', hidden: true},
				{name: 'LpuUnit_id', hidden: true, isparams: true},
				{name: 'Lpu_id', hidden: true, isparams: true},
				{name: 'MedService_Nick', hidden: true},
				{name: 'MedService_Name', width: 200, header: lang['slujba']},
				{name: 'UslugaComplex_Name', id: 'autoexpand', header: lang['usluga']},
				{name: 'Queue', width: 80, header: lang['ochered']},
				{name: 'Dates', width: 500, header: 'Даты приёма'}
			],
			actions:
			[
				{name:'action_add', disabled: true, hidden: true},
				{name:'action_edit', //disabled: false, hidden: true,
				text: lang['vyibrat'], tooltip: lang['vyibrat'],
					handler: function() {
						var rec = this.Wizard.MedServiceLpu.getGrid().getSelectionModel().getSelected();
						if (rec) {
							// Проверяем можно ли записать на данный тип службы
							if(false == rec.get('MedServiceType_SysNick').inlist(['vk','mse','lab','pzm','func','prock'])) {
								sw.swMsg.alert(lang['soobschenie'], lang['na_vyibrannuyu_slujbu_zapis_ne_podderjivaetsya']);
								return false;
							}
						
							var arguments = {
								ARMType: this.ARMType,
								fromEmk: this.fromEmk,
								mode: this.formMode,
								onSaveRecord: (typeof this.onSaveRecord == 'function') ? this.onSaveRecord : Ext.emptyFn,
								callback: function(data){
									if (typeof this.onEvnDirectionSave == 'function')
									{
										this.onEvnDirectionSave(data);
									}
									if (this.fromEmk || 'evn_prescr' == this.formMode) {
										this.hide();
										return;
									}
								}.createDelegate(this),
								Person: {
									Person_Surname: this.params.Person_Surname,
									Person_Firname: this.params.Person_Firname,
									Person_Secname: this.params.Person_Secname,
									Person_Birthday: this.params.Person_Birthday,
									Person_id: this.params.Person_id,
									Server_id: this.params.Server_id,
									PersonEvn_id: this.params.PersonEvn_id
								},
								userMedStaffFact: this.userMedStaffFact,
								MedService_id: rec.get('MedService_id'),
								MedServiceType_id: rec.get('MedServiceType_id'),
								MedService_Nick: rec.get('MedService_Nick'),
								MedService_Name: rec.get('MedService_Name'),
								MedServiceType_SysNick: rec.get('MedServiceType_SysNick'),
								Lpu_did: rec.get('Lpu_id'),
								UslugaComplexMedService_id: rec.get('UslugaComplexMedService_id') || null,
								UslugaComplex_id: rec.get('UslugaComplex_id') || null,
								Diag_id: this.params.Diag_id || null,
								EvnDirection_pid: this.params.EvnDirection_pid || null,
								EvnQueue_id: this.params.EvnQueue_id || null,
								QueueFailCause_id: this.params.QueueFailCause_id || null,
								EvnPrescr_id: this.params.EvnPrescr_id || null,
								PrescriptionType_Code: this.params.PrescriptionType_Code || null,
								// служба уровня ЛПУ, поэтому эти параметры не могут быть определены
								LpuUnitType_SysNick: null,
								LpuSection_uid: null,
								LpuSection_Name: null,
								LpuUnit_did: null,
								LpuSectionProfile_id: null
								
							};
							getWnd('swTTMSScheduleRecordWindow').show(arguments);
						}
						
					}.createDelegate(this) 
				},
				{name:'action_view', disabled: true, hidden: true},
				{name:'action_delete', disabled: true, hidden: true}
			],
			onLoadData: function()
			{
				if (this.is_firstUslugaComplexLoad)
				{
					var index = -1;
					if (this.params.UslugaComplex_id) {
						index = this.Wizard.MedService.getGrid().getStore().findBy(function(rec) { return rec.get('UslugaComplex_id') == this.params.UslugaComplex_id; }.createDelegate(this));
					}
					if(index < 0) index = 0;
					this.Wizard.MedService.getGrid().getSelectionModel().selectRow(index);
					this.Wizard.MedService.getGrid().getView().focusRow(index); 
					this.is_firstUslugaComplexLoad = false;
				}
			}.createDelegate(this),
			/**
			 * Получение значение переданного поля текущей выбранной записи
			 */
			getSelectedParam: function(field) {
				var rec = this.getGrid().getSelectionModel().getSelected();
				if (rec)
					return rec.get(field);
				else
					return false;
			}
		});
		
		this.Wizard.LpuUnitType = new Ext.Panel(
		{
			region: 'center',
			layout: 'card',
			border: false,
			activeItem: 0, 
			defaults: 
			{
				border:false
			},
			items: 
			[
				this.Wizard.MedPersonal,
				this.Wizard.LpuSection,
				this.Wizard.MedService
			]
		});
		/*
		this.Wizard.ListByDay = new Ext.Panel(
		{
			region: 'center',
			layout: 'card',
			border: false,
			activeItem: 0, 
			defaults: 
			{
				border:false
			},
			items: 
			[
				this.Wizard.SchedulePolka,
				this.Wizard.ScheduleStac
			]
		});
		*/
		// Три этапа
		this.Wizard.Panel = new Ext.Panel(
		{
			region: 'center',
			layout: 'card',
			border: false,
			activeItem: 0, 
			defaults: 
			{
				border:false
			},
			items: 
			[{
				id: 'Lpu',
				layout: 'border',
				region:'center',
				defaults: {split: true},
				items:[this.Wizard.Lpu, this.Wizard.MedServiceLpu]
			},{
				id: 'LpuUnitCard',
				layout: 'border',
				region:'center',
				defaults: {split: true},
				items:[this.Wizard.LpuUnit, this.Wizard.LpuUnitType]
			},
			this.Wizard.SchedulePolka,
			this.Wizard.ScheduleStac,
			this.Wizard.ScheduleMedService
			]
		});
		
		Ext.apply(this, 
		{
			layout: 'border',
			defaults: {split: true},
			items: 
			[
				this.Filters,
				this.Wizard.Panel
			],
			buttons: 
			[{
				text: '-'
			}, 
			{
				text: BTN_FRMHELP,
				iconCls: 'help16',
				handler: function(button, event) {
					ShowHelp(WND_RMW);
				}.createDelegate(this),
				tabIndex: TABINDEX_MPSCHED + 98
			}, 
			{
				iconCls: 'cancel16',
				text: BTN_FRMCLOSE,
				handler: function() { this.hide(); }.createDelegate(this)
			}]
		});
		sw.Promed.swMPRecordWindow.superclass.initComponent.apply(this, arguments);
	}
});
