/**
* swTTMSScheduleRecordWindow - окно для работы с записью на службу
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Reg
* @access       public
* @copyright    Copyright (c) 2009-2011 Swan Ltd.
* @author       Petukhov Ivan aka Lich (megatherion@list.ru)
* @version      03.10.2011
*/

sw.Promed.swTTMSScheduleRecordWindow = Ext.extend(sw.Promed.BaseForm, {
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	draggable: true,
	maximized: true,
	id: 'TTMSScheduleRecordWindow',
	title: '',
	
	/**
	 * Панель с расписанием для записи
	 */
	TTMSRecordPanel: null,
	
	createDirection: null,
	
	/**
	 * Загрузка расписания
	 */
	loadSchedule: function() {
		this.TTMSRecordPanel.loadSchedule();
	},
	
	/**
	 * Открытие списка записанных на выбранный день для службы/услуги
	 */
	openDayListTTMS: function(date)
	{
		this.MainPanel.layout.setActiveItem('TTMSRecordOneDayPanel');
		this.TTMSRecordOneDayPanel.date =  date;
		this.TTMSRecordOneDayPanel.personData = this.Person;
		this.TTMSRecordOneDayPanel.calendar.setValue(date);
		this.TTMSRecordOneDayPanel.MedService_id = this.MedService_id;
		this.TTMSRecordOneDayPanel.UslugaComplexMedService_id = this.UslugaComplexMedService_id;
		this.TTMSRecordOneDayPanel.loadSchedule(this.TTMSRecordOneDayPanel.calendar.value);
	},
	
	
	/**
	 * Запись человека
	 */
	recordPerson: function(time_id, date, time)
	{
        if (this.disableRecord) {
            this.callback({
                TimetableMedService_id: time_id,
                TimetableMedService_begTime: date +' '+ time
            });
            return true;
        }

		sw.Promed.Direction.recordPerson({
			Timetable_id: time_id,
			person: this.Person || null,
			direction: {
				LpuUnitType_SysNick: this.LpuUnitType_SysNick
				,EvnQueue_id: this.EvnQueue_id || null
				,QueueFailCause_id: this.QueueFailCause_id || null
				,UslugaComplex_id: this.UslugaComplex_id || null
				,LpuSection_Name: this.LpuSection_Name || ''
				,LpuSection_uid: this.LpuSection_uid || null
				,PrehospDirect_id: (getGlobalOptions().lpu_id == this.Lpu_did)?1:2
				,LpuSectionProfile_id: this.LpuSectionProfile_id || null
				,MedStaffFact_id: null
				,EvnDirection_pid: this.EvnDirection_pid || 0
				,Diag_id: this.Diag_id || null
				,EvnVK_id: this.EvnVK_id || null
				,EvnVK_setDT: this.EvnVK_setDT || null
				,EvnVK_NumProtocol: this.EvnVK_NumProtocol || null
				,EvnPrescr_id: this.EvnPrescr_id || null
				,PrescriptionType_Code: this.PrescriptionType_Code || null
				,EvnPrescrVKData: this.EvnPrescrVKData || null
				,MedService_id: this.MedService_id || null
				,MedService_Nick: this.MedService_Nick || ''
				,MedServiceType_SysNick: this.MedServiceType_SysNick
				,Lpu_did: this.Lpu_did 
				,MedPersonal_id: this.userMedStaffFact.MedPersonal_id
				,Lpu_id: this.userMedStaffFact.Lpu_id
				,LpuSection_id: this.userMedStaffFact.LpuSection_id
                ,time: (date && time)?(date +' '+ time):null
			},
			order: (this.params && this.params.order)?this.params.order:{}, // если при записи сделан заказ, то передаем его данные
			callback: function(data){
				if (typeof this.callback == 'function')
				{
					this.callback(data);
				}
				if (this.fromEmk || 'evn_prescr' == this.mode) {
					this.hide();
					return;
				}
				this.TTMSRecordPanel.loadSchedule();
			}.createDelegate(this),
			onSaveRecord: this.onSaveRecord,
			onHide: null,
			needDirection: null,
			fromEmk: this.fromEmk,
			//mode: this.mode,, // todo: надо протестировать какой смысл в передаче этого параметра
			mode: 'nosave',
			loadMask: true,
			windowId: 'TTMSScheduleRecordWindow'
		});
	},
	
	/**
	 * Постановка человека в очередь на службу
	 */
	queuePerson: function()
	{
        if (this.disableRecord && typeof this.callback == 'function') {
            this.callback({
                TimetableMedService_id: null,
                TimetableMedService_begTime: null
            });
            return true;
        }

		sw.Promed.Direction.queuePerson({
			person: this.Person || null,
			direction: {
				LpuUnitType_SysNick: this.LpuUnitType_SysNick 
				,EvnQueue_id: this.EvnQueue_id || null
				,QueueFailCause_id: this.QueueFailCause_id || null
				,UslugaComplex_id: this.UslugaComplex_id || null
				,LpuSection_Name: this.LpuSection_Name || ''
				,LpuSection_uid: this.LpuSection_uid || null
				,PrehospDirect_id: (getGlobalOptions().lpu_id == this.Lpu_did)?1:2
				,LpuSectionProfile_id: this.LpuSectionProfile_id || null
				,EvnDirection_pid: this.EvnDirection_pid || 0
				,Diag_id: this.Diag_id || null
				,EvnVK_id: this.EvnVK_id || null
				,EvnVK_setDT: this.EvnVK_setDT || null
				,EvnVK_NumProtocol: this.EvnVK_NumProtocol || null
				,EvnPrescr_id: this.EvnPrescr_id || null
				,PrescriptionType_Code: this.PrescriptionType_Code || null
				,EvnPrescrVKData: this.EvnPrescrVKData || null
				,MedService_id: this.MedService_id || null
				,MedService_Nick: this.MedService_Nick || ''
				,MedServiceType_SysNick: this.MedServiceType_SysNick
				,Lpu_did: this.Lpu_did 
				,MedStaffFact_id: this.userMedStaffFact.MedStaffFact_id
				,MedPersonal_id: this.userMedStaffFact.MedPersonal_id
				,Lpu_id: this.userMedStaffFact.Lpu_id
				,LpuSection_id: this.userMedStaffFact.LpuSection_id
				//передаем фейковые данные, т.к. они обязательные при сохранении очереди. Но при постановке в очередь на службу уровня ЛПУ этих данных просто нет
				,LpuSection_did: this.LpuSection_uid || this.userMedStaffFact.LpuSection_id //куда направлен
				,LpuUnit_did: this.LpuUnit_did || this.userMedStaffFact.LpuUnit_id //куда направлен
				,EvnDirectionHTM_pid: this.EvnDirectionHTM_pid || null
				
			},
			order: (this.params && this.params.order)?this.params.order:{}, // если при записи сделан заказ, то передаем его данные
			callback: function(data){
				if (typeof this.callback == 'function')
				{
					this.callback(data);
				}
				if (this.fromEmk || 'evn_prescr' == this.mode) {
					this.hide();
					return;
				}
				this.TTMSRecordPanel.loadSchedule();
			}.createDelegate(this),
			onHide: null,
			needDirection: null,
			fromEmk: this.fromEmk,
			//mode: this.mode,, // todo: надо протестировать какой смысл в передаче этого параметра
			mode: 'nosave',
			loadMask: true,
			windowId: 'TTMSScheduleRecordWindow'
		});
	},
	
    initComponent: function() {
		
		// Панель расписания для записи
		this.TTMSRecordPanel = new sw.Promed.swTTMSRecordPanel({
			id:'TTMSRecordPanel',
			frame: false,
			border: false,
			region: 'center',
			/**
			 * Получение родителя
			 */
			getOwner: function ()
			{
				return this;
			}.createDelegate(this),
			/**
			 * Запись человека
			 */
			recordPerson: function(time_id, date, time)
			{
				this.recordPerson(time_id, date, time);
			}.createDelegate(this),
			/**
			 * Постановка человека в очередь на службу
			 */
			queuePerson: function()
			{
				this.queuePerson();
			}.createDelegate(this)
		});
		
		// Панель расписания на один день для записи на службу/услугу
		this.TTMSRecordOneDayPanel = new sw.Promed.swTTMSRecordOneDayPanel({
			id:'TTMSRecordOneDayPanel',
			frame: false,
			border: false,
			region: 'center',
			/**
			 * Получение родителя
			 */
			getOwner: function ()
			{
				return this;
			}.createDelegate(this),
			/**
			 * Запись человека
			 */
			recordPerson: function(time_id, date, time)
			{
				this.recordPerson(time_id, date, time);
			}.createDelegate(this),
			/**
			 * Постановка человека в очередь на службу
			 */
			queuePerson: function()
			{
				this.queuePerson();
			}.createDelegate(this)
		});
		

		this.MainPanel = new Ext.Panel(
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
				this.TTMSRecordPanel,
				this.TTMSRecordOneDayPanel
			]
		});
		
	    Ext.apply(this, {
	    	//autoHeight: true,
	    	layout: 'border',
			items: [
				this.MainPanel
			],
			buttons: [
				{
					text: '-'
				}, {
					text: BTN_FRMHELP,
					iconCls: 'help16',
					handler: function(button, event) 
					{
						ShowHelp(WND_TTMSRW);
					}
				}, {
					iconCls: 'cancel16',
					text: BTN_FRMCLOSE,
					handler: function() { this.hide() }.createDelegate(this)
				}
			],
			keys: [{
				key: [
					Ext.EventObject.F2,
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
						case Ext.EventObject.F2:
							this.openFillWindow();
						break;
						
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
	    sw.Promed.swTTMSScheduleRecordWindow.superclass.initComponent.apply(this, arguments);
    },
	
    show: function () {
    	sw.Promed.swTTMSScheduleRecordWindow.superclass.show.apply(this, arguments);

		if (!arguments[0] || !arguments[0]['MedService_id'] || !arguments[0]['MedServiceType_SysNick'] || !arguments[0]['Lpu_did']) {
			Ext.Msg.alert(lang['oshibka'], 'Не переданы необходимые параметры, повторите попытку открытия формы');
			this.hide();
			return false;
		}

		// Если в качестве параметра был передан MedService_Name, то берём название службы из переданных параметров
		if (arguments[0]['MedService_Name'])
		{
			this.setTitle(WND_TTMSRW + ' (' + arguments[0]['MedService_Name'] + ')');
		}
		this.ARMType = arguments[0].ARMType || null;
		if (arguments[0]['userMedStaffFact'])
		{
			this.userMedStaffFact = arguments[0]['userMedStaffFact'];
		}
		else if (sw.Promed.MedStaffFactByUser.last)
		{
			this.userMedStaffFact = sw.Promed.MedStaffFactByUser.last;
		}
		else
		{
			sw.Promed.MedStaffFactByUser.selectARM({
				selectFirst: true,
				ARMType: this.ARMType,
				onSelect: function(data) {
					this.userMedStaffFact = data;
				}.createDelegate(this)
			});
		}

        this.disableRecord = arguments[0]['disableRecord'] || false;

        this.Person = arguments[0]['Person'] || null;

        if (arguments[0]['Order']) {
            if (!this.params) this.params = {};
            this.params.order = arguments[0]['Order'];
        }
		
		this.MedService_id = arguments[0]['MedService_id'];
		this.MedService_Name = arguments[0]['MedService_Name'];
		this.MedService_Nick = arguments[0]['MedService_Nick'];
		this.MedServiceType_id = arguments[0]['MedServiceType_id'];
		this.MedServiceType_SysNick = arguments[0]['MedServiceType_SysNick'];
		this.TTMSRecordPanel.MedService_id = arguments[0]['MedService_id'];
		this.UslugaComplexMedService_id = arguments[0]['UslugaComplexMedService_id'] || null;
		this.TTMSRecordPanel.UslugaComplexMedService_id = arguments[0]['UslugaComplexMedService_id'] || null;
		this.Lpu_did = arguments[0]['Lpu_did'];//ЛПУ куда направляем
		this.LpuUnit_did = arguments[0].LpuUnit_did || null;
		this.LpuSection_uid = arguments[0].LpuSection_uid || null;
		this.LpuSection_Name = arguments[0].LpuSection_Name || null;
		this.LpuSectionProfile_id = arguments[0].LpuSectionProfile_id || null;
		
		this.EvnPrescr_id = arguments[0].EvnPrescr_id || null;
		this.PrescriptionType_Code = arguments[0].PrescriptionType_Code || null;
		this.EvnPrescrVKData = arguments[0].EvnPrescrVKData || null;
		this.EvnDirection_pid = arguments[0].EvnDirection_pid || null;//Посещение, движение
		this.Evn_id = arguments[0].Evn_id || this.EvnDirection_pid;
		this.EvnDirection_rid = arguments[0].EvnDirection_rid || null;//КВС, ТАП
		this.Diag_id = arguments[0].Diag_id || null;
		this.EvnVK_id = arguments[0].EvnVK_id || null;
		this.EvnVK_setDT = arguments[0].EvnVK_setDT || null;
		this.EvnVK_NumProtocol = arguments[0].EvnVK_NumProtocol || null;
		
		this.QueueFailCause_id = arguments[0].QueueFailCause_id || null;
		this.EvnQueue_id = arguments[0].EvnQueue_id || null;
		this.UslugaComplex_id = arguments[0].UslugaComplex_id || null;
		this.callback = arguments[0].callback || Ext.emptyFn;
		this.onSaveRecord = arguments[0].onSaveRecord || Ext.emptyFn;
		this.userClearTimeMS = arguments[0].userClearTimeMS || null;
		this.mode = arguments[0].mode || null;
		this.fromEmk = arguments[0].fromEmk || null;

		if (arguments[0].date) {
			this.date = arguments[0].date;
		}
		if (!this.date) {
			this.date = new Date().format('d.m.Y');
		}
		
		if(this.MedServiceType_SysNick.inlist(['vk','mse'])) {
			this.LpuUnitType_SysNick = this.MedServiceType_SysNick;
			if(!this.userClearTimeMS) {
				this.userClearTimeMS = function() {
					this.getLoadMask().hide();
					sw.swMsg.alert(lang['soobschenie'], lang['nelzya_udalit_napravlenie']);
				}.createDelegate(this);
			}
		} else /*if(this.UslugaComplexMedService_id)*/ {
			//на остальные службы записываем как в параклинику
			this.LpuUnitType_SysNick = 'parka';
		}
		/*
		this.parentEvnClass_SysNick = arguments[0].parentEvnClass_SysNick || null;//Посещение, движение
		this.TTMSRecordPanel.getTopToolbar().items.items[6].setDisabled(typeof this.createDirection != 'function');
		*/
		
		this.MainPanel.layout.setActiveItem('TTMSRecordPanel');
		
		//Сразу загружаем расписание на текущий день
		this.TTMSRecordPanel.calendar.setValue(this.date);
    	this.TTMSRecordPanel.loadSchedule(this.TTMSRecordPanel.calendar.value);
		
		this.TTMSRecordPanel.getTopToolbar().items.items[6].setDisabled(false);
		return true;
    }
});
