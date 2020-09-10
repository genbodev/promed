/**
 * Панель записи к врачу поликлиники
 */
sw.Promed.swTTGRecordPanel = Ext.extend(Ext.Panel, {
	id: 'TTGRecordPanel',
	frame: false,
	loadMask : true,
	
	/**
	 * Функция возврашающся ссылку на родительский элемент
	 */
	getOwner: null,
	
	/**
	 * Фильтр для часто используемываыва расписания
	 */
	FilterData:null,
	/**
	 * Идентификатор выбранной бирки
	 */
	TimetableGraf_id: null,
	
	/**
	 * Элемент выбранной бирки
	 */
	TimetableGraf_Element: null,
	
	/**
	 * Дата, с которой отображается расписание
	 */
	date: null,
	
	/**
	 * Место работы для которого отображается расписание
	 */
	MedStaffFact_id: null,

	/**
	 * Маска для загрузки
	 */
	loadMask: null,
	
	/**
	 * Данные человека
	 */
	personData: null,
	
	/**
	 * Данные для направления
	 */
	directionData: null,
	
	/**
	 * Функция вызываемая после успешной записи или постановки в очередь
	 */
	onDirection: Ext.emptyFn,
	/**
	 * Функция, вызываемая непосредственно после успешной записи на бирку
	 */
	onSaveRecord:  Ext.emptyFn,
	
	/**
	 * Получить вариант использования
	 */
	getUseCase: function() {
		return this._useCase || 'undefined';
	},
	/**
	 * Установить вариант использования
	 */
	setUseCase:  function(use_case) {
		this._useCase = use_case || 'undefined';
		switch (this._useCase) {
			case 'rewrite':
			case 'record_from_queue':
			case 'show_list_only':
				this.btnQueuePerson.setVisible(false);
				this.btnUnScheduledRecord.setVisible(false);
				break;
			default:
				this.btnQueuePerson.setVisible(true);
				//this.btnUnScheduledRecord.setVisible(true);
				var ARMType = sw.Promed.MedStaffFactByUser.current && sw.Promed.MedStaffFactByUser.current.ARMType;
				var flagBtnUSR = true;
				if( !Ext.isEmpty(this.directionData) && !Ext.isEmpty(this.userMedStaffFact) ){
					if(ARMType == 'common' && this.directionData['MedPersonal_did'] == this.directionData['MedPersonal_id']){
						// врач может создать бирку с типом «дополнительная» только в своем расписании
						flagBtnUSR = true;
					}else if( ['regpol','regpol6'].in_array(ARMType) && this.userMedStaffFact['Lpu_id'] == this.directionData['Lpu_did']){
						// регистратор поликлиники может создать бирку с типом «дополнительная» только в расписании врачей своей МО
						flagBtnUSR = true;
					}else{
						flagBtnUSR = false;
					}
				}else{
					flagBtnUSR = false;
				}

				if ( isSmoTfomsUser() || sw.readOnly ) {
					if(!isCallCenterAdmin())
						this.btnQueuePerson.setVisible(false);
					flagBtnUSR = false;
				}

				this.btnUnScheduledRecord.setVisible(flagBtnUSR);
				/*
				if ( !((sw.Promed.MedStaffFactByUser.current && sw.Promed.MedStaffFactByUser.current.ARMType == 'common')
					|| (sw.Promed.MedStaffFactByUser.current && sw.Promed.MedStaffFactByUser.current.ARMType == 'regpol'))
				) {
					this.btnUnScheduledRecord.setVisible(false);
				}
				*/
				break;
		}
		if(getWnd('swWorkPlaceMZSpecWindow').isVisible() || getWnd('swPMWorkPlaceWindow').isVisible() || getWnd('swMiacWorkPlaceWindow').isVisible())
		{
			this.btnQueuePerson.setVisible(false);
			this.btnUnScheduledRecord.setVisible(false);
		}
	},
	
	/**
	 * Загрузка расписания
	 *
	 * @param date Дата, начиная с которой загружать расписание
	 */

	loadSchedule: function(date)
	{
		if (date) {
			this.date = date;
		}
		var params = {};
		if(this.FilterData==null){
			params= {
					StartDay: this.date,
					MedStaffFact_id: this.MedStaffFact_id,
					PanelID: this.id, // отправляем идентификатор панели для правильной генерации HTML
					filterByLpu: (getWnd('swWorkPlaceMZSpecWindow').isVisible() || getWnd('swPMWorkPlaceWindow').isVisible() || getWnd('swMiacWorkPlaceWindow').isVisible()) ? 'false' : 'true'
				}
		}else{
			params = this.FilterData;
			params.PanelID= this.id
		}
		this.load(
			{
				url: C_TTG_LISTFORREC,
				params:params,
				scripts:true,
				text: lang['podojdite_idet_zagruzka_raspisaniya'],
				success: function () {
					//
				},
				failure: function () {
					Ext.Msg.alert(lang['oshibka'], lang['oshibka_polucheniya_raspisaniya_poprobuyte_esche_raz']);
				}
			}
		);
	},
	
	/**
	 * Открытие окна для комментария на врача
	 */
	openMedStaffFactCommentWindow: function(day)
	{	
		if ( isSmoTfomsUser() || sw.readOnly ) {
			return false;
		}

		getWnd('swEditMedStaffFactCommentWindow').show({
			MedStaffFact_id: this.MedStaffFact_id,
			callback: function() {
				this.loadSchedule();
			}.createDelegate(this)
		})
		
	},
	
	/**
	 * Открытие окна истории изменения бирки
	 */
	openTTHistoryWindow: function()
	{
		getWnd('swTTHistoryWindow').show({
			TimetableGraf_id: this.TimetableGraf_id,
			callback: function() {
				
			}.createDelegate(this)
		});
	},
	
	/**
	 * Печать расписания врача as is
	 */
	printSchedule: function() {
		var id_salt = Math.random();
		var win_id = 'print_ttg_edit' + Math.floor(id_salt * 10000);
		var win = window.open(C_TTG_LISTFOREDITPRINT + '&StartDay=' + (this.date?this.date:'') + '&MedStaffFact_id=' + this.MedStaffFact_id, win_id);
	},

	/**
	 * Перемещение по календарю
	 */
	stepDay: function(day)
	{
		var date = (this.calendar.getValue() || Date.parseDate(this.date, 'd.m.Y')).add(Date.DAY, day).clearTime();
		this.calendar.setValue(Ext.util.Format.date(date, 'd.m.Y'));
		this.date = Ext.util.Format.date(date, 'd.m.Y');
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
	
	/**
	 * Освобождение времени
	 */
	clearTime: function(time_id, evndirection_id, inet_user) 
	{
		return sw.Promed.Direction.cancel({
			cancelType: 'cancel',
			ownerWindow: this,
			EvnDirection_id: evndirection_id,
			TimetableGraf_id: time_id,
			callback: function (cfg) {
				this.loadSchedule();
			}.createDelegate(this)
		});
		if (time_id) {
			this.TimetableGraf_id = time_id;
		}
		if (evndirection_id) {
			getWnd('swSelectDirFailTypeWindow').show({
				time_id: this.TimetableGraf_id,
                LpuUnitType_SysNick: 'polka',
				onClear: function() {
					this.loadSchedule();
				}.createDelegate(this)
			});
		} else if (inet_user) {
			getWnd('swTimetableGrafSetFailWindow').show({
				TimetableGraf_id: this.TimetableGraf_id, 
				callback: function() {
					this.loadSchedule();
				}.createDelegate(this)
			});
		} else {
			sw.swMsg.show({
				title: lang['podtverjdenie'],
				msg: lang['vyi_deystvitelno_jelaete_osvobodit_vremya_priema'],
				buttons: Ext.Msg.YESNO,
				fn: function ( buttonId ) {
					if ( buttonId == 'yes' )
					{
						var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Подождите, идет сохранение..." });
						loadMask.show();
						submitClearTime(
							{
								id: this.TimetableGraf_id,
								type: 'polka',
								DirFailType_id: null,
								EvnComment_Comment: null
							},
							function(options, success, response) {
								loadMask.hide();
								this.loadSchedule();
							}.createDelegate(this),
							function() {
								loadMask.hide();
							}
						);
					}
				}.createDelegate(this)
			});
		}
	},
	
	
	/**
	 * Запись человека на бирку
	 */
    recordPerson: function(time_id, date, time) {
        this.directionData['time'] = (date && time)?(date +' '+ time):null;
		var params = {
			Timetable_id: time_id
            ,userMedStaffFact: this.userMedStaffFact || {}
			,direction: this.directionData
			,person: this.personData || null
			,loadMask: true
			,windowId: 'swDirectionMasterWindow'
			,callback: this.onDirection || Ext.emptyFn
			,onSaveRecord: this.onSaveRecord || Ext.emptyFn
			,onHide: Ext.emptyFn
			,needDirection: null
			,fromEmk: false
			,mode: 'nosave'
			,date: date
		};
		sw.Promed.Direction.recordPerson(params);
	},
	
	/**
	 * Незапланированный прием человека
	 * Автоматически добавляется дополнительная бирка текущим временем
	 * На нее сразу происходит запись человека
	 */
	unScheduledRecord: function(dt)
	{
        this.directionData['time'] = dt +' 00:00';
		var params = {
			Timetable_id: 0
            ,userMedStaffFact: this.userMedStaffFact || {}
			,direction: this.directionData
			,person: this.personData || null
			,loadMask: true
			,windowId: 'swDirectionMasterWindow'
			,callback: this.onDirection || Ext.emptyFn
			,onSaveRecord: this.onSaveRecord || Ext.emptyFn
			,onHide: Ext.emptyFn
			,needDirection: null
			,fromEmk: false
			,mode: 'nosave'
			,Unscheduled: true
			,date: dt
		};
		debugger;
		sw.Promed.Direction.recordPerson(params);
	},
	
	/**
	 * Помещение человека в очередь
	 */
	queuePerson: function() 
	{
		var params = {
			direction: this.directionData
            ,userMedStaffFact: this.userMedStaffFact || {}
			,person: this.personData || null
			,loadMask: true
			,windowId: 'swDirectionMasterWindow'
			,callback: this.onDirection || Ext.emptyFn
			,onSaveRecord: Ext.emptyFn
			,onHide: Ext.emptyFn
			,needDirection: null
			,fromEmk: false
			,mode: ''
		};
		sw.Promed.Direction.queuePerson(params);
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
	
	printPacList:  function() {
		var id_salt = Math.random();
		var win_id = 'print_pac_list' + Math.floor(id_salt * 10000);
		window.open('/?c=TimetableGraf&m=printPacList&Day=' + this.date + '&MedStaffFact_id=' + this.MedStaffFact_id, win_id);
	},
	
	initComponent: function() {
		var me = this;
		
		me.btnQueuePerson = new Ext.Toolbar.Button({
			iconCls: 'add16',
			text: lang['postavit_v_ochered'],
			tooltip : "Поставить в очередь <b>(F2)</b>",
			handler: function () {
				me.queuePerson();
			}
		});
		me.btnUnScheduledRecord = new Ext.Toolbar.Button({
			iconCls: 'copy16',
			text: lang['dopolnitelnyiy_priem'],
			tooltip: "Незапланированная запись к выбранному врачу",
			handler: function () {
				// передаем "текущую" дату
				me.unScheduledRecord(Ext.globalOptions.globals.date);
			}
		});
		/**
		 * Поле ввода даты для движения по календарю
		 */
		this.calendar = new sw.Promed.SwDateField(
		{
			fieldLabel: lang['data_raspisaniya'],
			plugins:
			[
				new Ext.ux.InputTextMask('99.99.9999', false)
			],
			xtype: 'swdatefield',
			format: 'd.m.Y',
			listeners:
			{
				'keydown': function (inp, e) 
				{
					if (e.getKey() == Ext.EventObject.ENTER) {
						e.stopEvent();
						this.loadSchedule(Ext.util.Format.date(inp.getValue(), 'd.m.Y'));
					}
				}.createDelegate(this),
				'select': function () 
				{
					this.loadSchedule(this.calendar.value);
				}.createDelegate(this)
			},
			value: new Date()
		});
		
		this.tbar = new Ext.Toolbar({
			autoHeight: true,
			buttons: [
			{
				text: lang['predyiduschiy'],
				xtype: 'button',
				iconCls: 'arrow-previous16',
				handler: function()
				{
					// на один день назад
					this.prevDay();
					this.loadSchedule();
				}.createDelegate(this)
			},
			this.calendar, 
			{
				text: lang['sleduyuschiy'],
				xtype: 'button',
				iconCls: 'arrow-next16',
				handler: function()
				{
					// на один день вперед
					this.nextDay();
					this.loadSchedule();
				}.createDelegate(this)
			},
			'-', 
			{
				iconCls: 'refresh16',
				text: lang['obnovit'],
				tooltip : "Обновить расписание <b>(F5)</b>",
				handler: function () {
					this.loadSchedule(this.calendar.value);
				}.createDelegate(this)
			},
			'-',
			me.btnQueuePerson,
			me.btnUnScheduledRecord,
			'-', 
			{
				iconCls: 'print16',
				text: lang['pechat'],
				tooltip : "Печать расписания <b>(F9)</b>",
				handler: function () {
					this.printSchedule();
				}.createDelegate(this)
			},
			{
				iconCls: 'print16',
				text: lang['pechat_spiska_patsientov'],
				disabled: false,
				//hidden: (getGlobalOptions().region.nick != 'ufa' && getGlobalOptions().region.nick != 'kz'),
				tooltip : "Печать списка пациентов",
				handler: function () {
					this.printPacList();
				}.createDelegate(this)
			}
			],
			style: "border-bottom: 1px solid #99BBE8;"
		});
	    
	    Ext.apply(this, {
			autoScroll: true,
			layout: 'fit',
			keys: [{
				key: [
					Ext.EventObject.F5,
					Ext.EventObject.F9
				],
				fn: function(inp, e) {
					e.stopEvent();
					if ( e.browserEvent.stopPropagation )
						e.browserEvent.stopPropagation();
					else
						e.browserEvent.cancelBubble = true;

					if ( e.browserEvent.preventDefault )
						e.browserEvent.preventDefault();
					else
						e.browserEvent.returnValue = false;

					e.browserEvent.returnValue = false;
					e.returnValue = false;

					if (Ext.isIE)
					{
						e.browserEvent.keyCode = 0;
						e.browserEvent.which = 0;
					}

					switch (e.getKey())
					{
						case Ext.EventObject.F5:
							this.loadSchedule();
						break;

						case Ext.EventObject.F9:
							this.printSchedule();
						break;
					}
				},
				scope: this,
				stopEvent: false
			}]
	    });
	    sw.Promed.swTTGRecordPanel.superclass.initComponent.apply(this, arguments);

		this.getTopToolbar().on('render', function(tb){
			me.setUseCase(null);
		});
    }
});