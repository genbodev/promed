/**
 * swTTRScheduleRecordWindow - окно для работы с записью на ресурс
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      	Reg
 * @access       	public
 * @copyright		Copyright (c) 2014 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			27.11.2015
 */
/*NO PARSE JSON*/

sw.Promed.swTTRScheduleRecordWindow = Ext.extend(sw.Promed.BaseForm, {
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	draggable: true,
	maximized: true,
	id: 'TTRScheduleRecordWindow',
	title: '',

	/**
	 * Панель с расписанием для записи
	 */
	TTRRecordPanel: null,

	createDirection: null,

	loadResourceMedServiceGrid: function() {
		var params = {UslugaComplexMedService_id: this.UslugaComplexMedService_id};

		if (this.MainPanel.layout.activeItem.getId() == 'SelectTTRPanel') {
			params.Resource_begDate = this.TTRRecordPanel.date;
			params.TimetableResource_begDate = this.TTRRecordPanel.date;
		}

		params.MedService_id = this.MedService_id;

		if (this.UslugaComplex_ids) {
			params.UslugaComplex_ids = Ext.util.JSON.encode(this.UslugaComplex_ids);
		}

		this.ResourceMedServiceGrid.getStore().load({
			params: params,
			callback: function() {
				if (this.ResourceMedServiceGrid.getStore().getCount() > 0) {
					var index = this.ResourceMedServiceGrid.getStore().findBy(function(rec) { return rec.get('Resource_id') == this.Resource_id; }.createDelegate(this));

					if (index >= 0) {
						this.ResourceMedServiceGrid.getSelectionModel().selectRow(index);
					} else {
						this.ResourceMedServiceGrid.getSelectionModel().selectFirstRow();
					}
				} else {
					this.ResourceMedServiceGrid.getTopToolbar().items.items[3].el.innerHTML = '0 / 0';

					this.Resource_id = null;
					this.Resource_Name = '';
					//this.TTRRecordPanel.Resource_id = null;
					this.TTRRecordPanel.loadSchedule(this.TTRRecordPanel.calendar.value);
					this.TTRRecordPanel.btnQueuePerson.disable();
				}
			}.createDelegate(this)
		});
	},

	/**
	 * Загрузка расписания
	 */
	loadSchedule: function() {
		this.TTRRecordPanel.btnQueuePerson.enable();
		this.TTRRecordPanel.loadSchedule();
	},

	/**
	 * Открытие списка записанных на выбранный день для службы/услуги
	 */
	openDayListTTR: function(date)
	{
		this.MainPanel.layout.setActiveItem('TTRRecordOneDayPanel');
		this.TTRRecordOneDayPanel.date =  date;
		this.TTRRecordOneDayPanel.personData = this.Person;
		this.TTRRecordOneDayPanel.calendar.setValue(date);
		this.TTRRecordOneDayPanel.Resource_id = this.Resource_id;
		this.TTRRecordOneDayPanel.loadSchedule(this.TTRRecordOneDayPanel.calendar.value);
	},


	/**
	 * Запись человека
	 */
	recordPerson: function(time_id, date, time)
	{
		log([this.id, 'recordPerson', time_id, date, time]);
		if (this.disableRecord) {
			this.callback({
				TimetableResource_id: time_id,
				TimetableResource_begTime: date +' '+ time
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
				,EvnPrescr_id: this.EvnPrescr_id || null
				,PrescriptionType_Code: this.PrescriptionType_Code || null
				,MedService_id: this.MedService_id || null
				,MedService_Nick: this.MedService_Nick || ''
				,MedServiceType_SysNick: this.MedServiceType_SysNick
				,Resource_id: this.Resource_id
				,Resource_Name: this.Resource_Name
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
				this.TTRRecordPanel.loadSchedule();
			}.createDelegate(this),
			onSaveRecord: this.onSaveRecord,
			onHide: null,
			needDirection: null,
			fromEmk: this.fromEmk,
			//mode: this.mode,, // todo: надо протестировать какой смысл в передаче этого параметра
			mode: 'nosave',
			loadMask: true,
			windowId: 'TTRScheduleRecordWindow'
		});
	},

	/**
	 * Постановка человека в очередь на службу
	 */
	queuePerson: function()
	{
		if (this.disableRecord && typeof this.callback == 'function') {
			this.callback({
				TimetableResource_id: null,
				TimetableResource_begTime: null
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
				,EvnPrescr_id: this.EvnPrescr_id || null
				,PrescriptionType_Code: this.PrescriptionType_Code || null
				,MedService_id: this.MedService_id || null
				,MedService_Nick: this.MedService_Nick || ''
				,MedServiceType_SysNick: this.MedServiceType_SysNick
				,Resource_id: this.Resource_Name
				,Lpu_did: this.Lpu_did
				,MedStaffFact_id: this.userMedStaffFact.MedStaffFact_id
				,MedPersonal_id: this.userMedStaffFact.MedPersonal_id
				,Lpu_id: this.userMedStaffFact.Lpu_id
				,LpuSection_id: this.userMedStaffFact.LpuSection_id
				//передаем фейковые данные, т.к. они обязательные при сохранении очереди. Но при постановке в очередь на службу уровня ЛПУ этих данных просто нет
				,LpuSection_did: this.LpuSection_uid || this.userMedStaffFact.LpuSection_id //куда направлен
				,LpuUnit_did: this.LpuUnit_did || this.userMedStaffFact.LpuUnit_id //куда направлен

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
				this.TTRRecordPanel.loadSchedule();
			}.createDelegate(this),
			onHide: null,
			needDirection: null,
			fromEmk: this.fromEmk,
			//mode: this.mode,, // todo: надо протестировать какой смысл в передаче этого параметра
			mode: 'nosave',
			loadMask: true,
			windowId: 'TTRScheduleRecordWindow'
		});
	},

	initComponent: function() {

		// Панель расписания для записи
		this.TTRRecordPanel = new sw.Promed.swTTRRecordPanel({
			id:'TTRRecordPanel',
			frame: false,
			border: false,
			region: 'center',
			/**
			 * Действие при изменении даты
			 */
			onDateChange: function() {
				this.loadResourceMedServiceGrid();
			}.createDelegate(this),
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
		this.TTRRecordOneDayPanel = new sw.Promed.swTTRRecordOneDayPanel({
			id:'TTRRecordOneDayPanel',
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

		this.ResourceMedServiceGrid = new Ext.grid.GridPanel({
			autoExpandColumn: 'autoexpand',
			border: true,
			region: 'west',
			width: 250,
			split: true,
			header: false,
			//hidden: true,
			id: 'TTRSRW_ResourceMedServiceGrid',
			autoExpandMax: 2000,
			loadMask: true,
			stripeRows: true,
			enableKeyEvents: true,
			keys: [{
				key: [
					Ext.EventObject.TAB
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
						case Ext.EventObject.TAB:
							if ( e.shiftKey )
							{
								this.ResourceMedServiceGrid.getTopToolbar().items.item('ResurceFilter').focus();
							} else {
								this.buttons[this.buttons.length - 2].focus(true);
							}
							break;
					}
				}.createDelegate(this),
				stopEvent: true
			}],
			store: new Ext.data.JsonStore({
				autoLoad: false,
				url: '/?c=Reg&m=getResourceListForSchedule',
				fields: [
					'Resource_id',
					'Resource_Name',
					{ name: 'TimetableResource_begDate', type: 'date', dateFormat: 'd.m.Y' }
				],
				listeners: {
					'load': function(store) {
						var field = this.ResourceMedServiceGrid.getTopToolbar().items.item('ResourceFilter');
						var exp = field.getValue();
						if (exp != "") {
							this.ResourceMedServiceGrid.getStore().filter('Resource_Name', new RegExp(exp, "i"));
						}
						this.ResourceMedServiceGrid.getTopToolbar().items.items[3].el.innerHTML = '0 / ' + store.getCount();
					}.createDelegate(this)
				}
			}),
			columns: [
				{dataIndex: 'Resource_id', hidden: true, hideable: false},
				{id: 'autoexpand', header: lang['resurs'], dataIndex: 'Resource_Name', sortable: true},
				{header: 'Ближайшая дата', dataIndex: 'TimetableResource_begDate', renderer: Ext.util.Format.dateRenderer('d.m.Y'), width: 100, sortable: true}
			],
			tbar: new sw.Promed.Toolbar({
				autoHeight: true,
				items: [{
					xtype: 'label',
					text: lang['filtr'],
					style: 'margin-left: 5px; font-weight: bold'
				}, {
					xtype: 'textfield',
					id: 'ResourceFilter',
					tabIndex: TABINDEX_SEMW + 5,
					style: 'margin-left: 5px',
					enableKeyEvents: true,
					listeners: {
						'keyup': function(field, e) {
							if (tm) {
								clearTimeout(tm);
							} else {
								var tm = null;
							}
							tm = setTimeout(function () {
								var field = this.ResourceMedServiceGrid.getTopToolbar().items.item('ResourceFilter');
								var exp = field.getValue();
								this.ResourceMedServiceGrid.getStore().filter('Resource_Name', new RegExp(exp, "i"));
								this.ResourceMedServiceGrid.getTopToolbar().items.items[3].el.innerHTML = '0 / ' + this.ResourceMedServiceGrid.getStore().getCount();
								field.focus();
							}.createDelegate(this),
								100
							);
						}.createDelegate(this),
						'keydown': function (inp, e) {
							if (e.getKey() == Ext.EventObject.TAB )
							{
								e.stopEvent();
								if  (e.shiftKey == false) {
									if ( this.ResourceMedServiceGrid.getStore().getCount() > 0 )
									{
										this.ResourceMedServiceGrid.getView().focusRow(0);
										this.ResourceMedServiceGrid.getSelectionModel().selectFirstRow();
									}
								} /*else {
							 this.StructureTree.focus();
							 }*/
							}
						}.createDelegate(this)
					}
				},
					{
						xtype: 'tbfill'
					}, {
						text: '0 / 0',
						xtype: 'tbtext'
					}]
			}),
			sm: new Ext.grid.RowSelectionModel({
				singleSelect: true,
				listeners: {
					'rowselect': function(sm, rowIdx, r) {
						this.TTRRecordPanel.btnQueuePerson.enable();
						this.ResourceMedServiceGrid.getTopToolbar().items.items[3].el.innerHTML = (rowIdx + 1) + ' / ' + this.ResourceMedServiceGrid.getStore().getCount();

						this.Resource_id = r.get('Resource_id');
						this.Resource_Name = r.get('Resource_Name');
						this.TTRRecordPanel.Resource_id = r.get('Resource_id');
						this.TTRRecordPanel.loadSchedule(this.TTRRecordPanel.calendar.value);
					}.createDelegate(this)
				}
			})
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
						{
							id: 'SelectTTRPanel',
							region: 'center',
							layout: 'border',
							items: [
								this.ResourceMedServiceGrid,
								this.TTRRecordPanel
							]
						},
						this.TTRRecordOneDayPanel
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
		sw.Promed.swTTRScheduleRecordWindow.superclass.initComponent.apply(this, arguments);
	},

	show: function () {
		sw.Promed.swTTRScheduleRecordWindow.superclass.show.apply(this, arguments);

		if (!arguments[0] || !arguments[0]['Lpu_did']) {
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

		//this.Resource_id = arguments[0]['Resource_id'];
		//this.Resource_Name = arguments[0]['Resource_Name'];
		this.UslugaComplexMedService_id = arguments[0]['UslugaComplexMedService_id'];
		this.UslugaComplex_ids = arguments[0]['UslugaComplex_ids'] || null;
		this.MedService_id = arguments[0]['MedService_id'];
		this.MedService_Name = arguments[0]['MedService_Name'];
		this.MedService_Nick = arguments[0]['MedService_Nick'];
		this.MedServiceType_id = arguments[0]['MedServiceType_id'];
		this.MedServiceType_SysNick = arguments[0]['MedServiceType_SysNick'];
		//this.TTRRecordPanel.Resource_id = arguments[0]['Resource_id'];
		this.Lpu_did = arguments[0]['Lpu_did'];//ЛПУ куда направляем
		this.LpuUnit_did = arguments[0].LpuUnit_did || null;
		this.LpuSection_uid = arguments[0].LpuSection_uid || null;
		this.LpuSection_Name = arguments[0].LpuSection_Name || null;
		this.LpuSectionProfile_id = arguments[0].LpuSectionProfile_id || null;

		this.EvnPrescr_id = arguments[0].EvnPrescr_id || null;
		this.PrescriptionType_Code = arguments[0].PrescriptionType_Code || null;
		this.EvnDirection_pid = arguments[0].EvnDirection_pid || null;//Посещение, движение
		this.Evn_id = arguments[0].Evn_id || this.EvnDirection_pid;
		this.EvnDirection_rid = arguments[0].EvnDirection_rid || null;//КВС, ТАП
		this.Diag_id = arguments[0].Diag_id || null;
		this.EvnVK_id = arguments[0].EvnVK_id || null;
		this.EvnVK_setDT = arguments[0].EvnVK_setDT || null;

		this.QueueFailCause_id = arguments[0].QueueFailCause_id || null;
		this.EvnQueue_id = arguments[0].EvnQueue_id || null;
		this.UslugaComplex_id = arguments[0].UslugaComplex_id || null;
		this.callback = arguments[0].callback || Ext.emptyFn;
		this.onSaveRecord = arguments[0].onSaveRecord || Ext.emptyFn;
		this.userClearTimeR = arguments[0].userClearTimeR || null;
		this.mode = arguments[0].mode || null;
		this.fromEmk = arguments[0].fromEmk || null;
		if (arguments[0].date) {
			this.date = arguments[0].date;
		}
		if (!this.date) {
			this.date = new Date().format('d.m.Y');
		}

		this.TTRRecordPanel.Resource_id = null;

		if(this.MedServiceType_SysNick.inlist(['vk','mse'])) {
			this.LpuUnitType_SysNick = this.MedServiceType_SysNick;
			if(!this.userClearTimeR) {
				this.userClearTimeR = function() {
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
		 this.TTRRecordPanel.getTopToolbar().items.items[6].setDisabled(typeof this.createDirection != 'function');
		 */

		this.MainPanel.layout.setActiveItem('SelectTTRPanel');

		//Сразу загружаем расписание на текущий день
		//this.TTRRecordPanel.loadSchedule(this.TTRRecordPanel.calendar.value);

		this.TTRRecordPanel.date = this.date;
		this.TTRRecordPanel.calendar.setValue(this.TTRRecordPanel.date);
		this.TTRRecordPanel.onDateChange();
		//this.loadResourceMedServiceGrid();

		this.TTRRecordPanel.getTopToolbar().items.items[6].setDisabled(false);
		return true;
	}
});
