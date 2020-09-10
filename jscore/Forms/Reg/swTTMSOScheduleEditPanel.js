/**
 * Панель редактирования расписания службы
 */
sw.Promed.swTTMSOScheduleEditPanel = Ext.extend(Ext.Panel, {
	id: 'schedule',
	frame: false,
	loadMask : true,
	
	listeners: {
		hide: function() {
			this.userClearTimeMS = null;
		}
	},
	
	/**
	 * Идентификатор выбранной бирки
	 */
	TimetableMedServiceOrg_id: null,
	
	/**
	 * Идентификатор связанного направления
	 */
	EvnDirection_id: null,
	
	/**
	 * Элемент выбранной бирки
	 */
	TimetableMedServiceOrg_Element: null,
	
	/**
	 * Дата, с которой отображается расписание
	 */
	date: null,
	
	/**
	 * Служба, с расписанием которой идет работа
	 */
	MedService_id: null,
	
	/**
	 * Услуга, с расписанием которой идет работа
	 */
	UslugaComplexMedService_id: null,
	
	/**
	 * Набор выбранных бирок для групповой работы с ними
	 */
	selectedTTMSO: [],

	/**
	 * Флаг только для чтения
	 */
	readOnly: false,

	/**
	 * Включение/выключение режима просмотра
	 */
	setReadOnly: function(readOnly) {
		this.readOnly = readOnly;

		Ext.getCmp(this.id + '_createScheduleBtn').setDisabled(readOnly);

		this.getContextMenu().items.item('settype-time').setVisible(!readOnly);
		this.getContextMenu().items.item('clear-time').setVisible(!readOnly);
		this.getContextMenu().items.item('delete-time').setVisible(!readOnly);
		this.getContextMenu().items.item('edit-time').setVisible(!readOnly);
	},

	/**
	 * Получение ссылки на объект расписания
	 */
	getSchedule : function () {
		return this.schedule;
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

		if ( !this.UslugaComplexMedService_id ) {
			var url = C_TTMSO_LISTFOREDIT;
		} else {
			url = C_TTUC_LISTFOREDIT;
		}
		this.getSchedule().load(
			{
				url: url,
				params: {
					StartDay: this.date,
					MedService_id: this.MedService_id,
					UslugaComplexMedService_id: this.UslugaComplexMedService_id,
					PanelID: this.id, // отправляем идентификатор панели для правильной генерации HTML
					readOnly: this.readOnly
				},
				scripts:true,
				timeout: 300,
				text: lang['podojdite_idet_zagruzka_raspisaniya'],
				callback: function () {
					// Очищаем массив выбранных бирок при перезагрузке расписания
					this.selectedTTMSO = [];
				}.createDelegate(this),
				failure: function () {
					Ext.Msg.alert(lang['oshibka'], lang['oshibka_polucheniya_raspisaniya_poprobuyte_esche_raz']);
				}
			}
		);
	},
	
	/**
	 * Открытие окна для заполнения расписания
	 */
	openFillWindow: function(dt)
	{
		getWnd('swTTMSOScheduleFillWindow').show({
			date: dt,
			MedService_id: this.MedService_id,
			UslugaComplexMedService_id: this.UslugaComplexMedService_id,
			callback: function() {
				this.loadSchedule();
			}.createDelegate(this)
		});
	},
	
	/**
	 * Получение ссылки на объект контекстного меню
	 */
	getContextMenu : function () {
		return this.mnuContext;
	},
	
	/**
	 * Получение ссылки на объект меню смены типа бирки
	 */
	getSetTypeMenu : function () {
		return this.mnuSetType;
	},
	
	/**
	 * Открытие контекстного меню для редактирования бирки
	 */
	openContextMenu : function ( el, timeId, PersonId, evndirection_id ) {
		//debugger;
		if ( this.selectedTTMSO.length <= 1 ) { // если не выделена ни одна бирка или только одна бирка
			this.TimetableMedServiceOrg_id = timeId;
			this.TimetableMedServiceOrg_Element = el;
			this.EvnDirection_id = evndirection_id;
			
			this.getContextMenu().show(el);
			// Если на бирке есть человек, то открываем пункт "Освободить бирку",
			// но закрываем "Удалить бирку" и "Редактировать бирку"
			if ( PersonId != 0 ) {
				this.getContextMenu().items.item('settype-time').disable();
				this.getContextMenu().items.item('clear-time').enable();
				this.getContextMenu().items.item('delete-time').disable();
				this.getContextMenu().items.item('edit-time').disable();
			} else { // и наоборот
				this.getContextMenu().items.item('settype-time').enable();
				this.getContextMenu().items.item('clear-time').disable();
				this.getContextMenu().items.item('delete-time').enable();
				this.getContextMenu().items.item('edit-time').enable();
			}
			this.getContextMenu().items.item('show-history').enable();
			
			if ( this.getSetTypeMenu().items.length > 0 ) {
				this.getSetTypeMenu().items.each(function(item) {
					item.setChecked(false, true);
				})
				
				if (this.TimetableMedServiceOrg_Element) {
					var classes = this.TimetableMedServiceOrg_Element.className.split(" ");
					for (var i = 0; i < classes.length; i++) {
						if (classes[i].indexOf( 'TimetableType_' ) >= 0 && classes[i].indexOf( '_person' ) == -1 ) {
							var id = classes[i].substr(14);
							this.getSetTypeMenu().items.item(id).setChecked(true, true);
							break;
						}
					}
				}
			}
		} else {
			this.TimetableMedServiceOrg_id = null;
			
			this.getContextMenu().items.item('settype-time').enable();
			this.getContextMenu().items.item('clear-time').disable();
			this.getContextMenu().items.item('delete-time').enable();
			this.getContextMenu().items.item('show-history').disable();
			this.getContextMenu().items.item('edit-time').enable();
			
			if ( this.getSetTypeMenu().items.length > 0 ) {
				this.getSetTypeMenu().items.each(function(item) {
					item.setChecked(false, true);
				})
			}
			
			this.getContextMenu().show(el);
		}
	},
	
	/**
	 * Выделение/удаление выделения с бирки
	 */
	toggleSelection : function ( el, timeId ) {
		if ($(el).hasClass( 'selected' )) {
			// Если бирка выделена - удаляем выделение
			$(el).removeClass('selected');
			// Удаляем бирку из массива выбранных бирок
			this.selectedTTMSO.splice($.inArray(timeId, this.selectedTTMSO),1);
		} else {
			// Если бирка не выделена - выделяем
			$(el).addClass('selected');
			// Добавляем бирку в массив выбранных бирок
			this.selectedTTMSO.push(timeId);
		}
	},

	/**
	 * Сообщение об невозможности очистки дня целиком и предложение удалить только свободные бирки
	 */
	clearFreeTTMSO: function(day)
	{
		sw.swMsg.show({
			title: lang['vnimanie'],
			msg: lang['nelzya_tselikom_ochistit_den_tak_kak_na_nem_est_zanyatyie_birki_udalit_tolko_svobodnyie_birki'],
			buttons: Ext.Msg.YESNO,
			fn: function ( buttonId ) {
				if ( buttonId == 'yes' )
				{
					var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Подождите, идет сохранение..." });
					loadMask.show();
					
					submitClearDay(
						{
							day: day,
							MedService_id: this.MedService_id,
							UslugaComplexMedService_id: this.UslugaComplexMedService_id,
							type: 'medservicedlo'
						},
						function(response) {
							var response_data = Ext.util.JSON.decode(response.responseText);
							if (response_data.success) {
								this.loadSchedule();
							}
							loadMask.hide();
						}.createDelegate(this)
					);
				}
			}.createDelegate(this)
		});
	},
	
	/**
	 * Очистка дня
	 */
	clearDay: function(day)
	{
		sw.swMsg.show({
			title: lang['podtverjdenie'],
			msg: lang['vyi_deystvitelno_jelaete_ochistit_raspisanie_na_den'],
			buttons: Ext.Msg.YESNO,
			fn: function ( buttonId ) {
				if ( buttonId == 'yes' )
				{
					var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Подождите, идет сохранение..." });
					loadMask.show();
					
					submitClearDay(
						{
							day: day,
							MedService_id: this.MedService_id,
							UslugaComplexMedService_id: this.UslugaComplexMedService_id,
							type: 'medservicedlo'
						},
						function(response) {
							var response_data = Ext.util.JSON.decode(response.responseText);
							if (response_data.success) {
								this.loadSchedule();
							}
							loadMask.hide();
						}.createDelegate(this)
					);
				}
			}.createDelegate(this)
		});
	},
	
	/**
	 * Открытие окна комментария на день
	 */
	openEditDayCommentWindow: function(day)
	{
		getWnd('swTTMSOScheduleEditDayCommentWindow').show({
			day: day,
			MedService_id: this.MedService_id,
			UslugaComplexMedService_id: this.UslugaComplexMedService_id,
			callback: function() {
				this.loadSchedule();
			}.createDelegate(this)
		});
	},
	
	/**
	 * Открытие окна для комментария на службу
	 */
	openMedServiceCommentWindow: function(day)
	{
		getWnd('swEditMedServiceCommentWindow').show({
			MedService_id: this.MedService_id,
			callback: function() {
				this.loadSchedule();
			}.createDelegate(this)
		});
	},
	
	/**
	 * Открытие окна для добавления дополнительной бирки
	 */
	openAddDopWindow: function(dt)
	{
		getWnd('swTTMSOScheduleAddDopWindow').show({
			date: dt,
			MedService_id: this.MedService_id,
			UslugaComplexMedService_id: this.UslugaComplexMedService_id,
			callback: function() {
				this.loadSchedule();
			}.createDelegate(this)
		});
	},
	
	/**
	 * Открытие окна истории изменения бирки
	 */
	openTTHistoryWindow: function()
	{
		getWnd('swTTHistoryWindow').show({
			TimetableMedServiceOrg_id: this.TimetableMedServiceOrg_id,
			callback: function() {
				
			}.createDelegate(this)
		});
	},
	
	/**
	 * Список записанных из расписания
	 */
	openDayListTTMSO: function(date, MedService_id)
	{
		var id_salt = Math.random();
		var win_id = 'print_TTMSO_edit' + Math.floor(id_salt * 10000);
		window.open(C_TTMSO_LISTONEDAYFORRECPRINT + '&StartDay=' + date + '&MedService_id=' + MedService_id, win_id);
	},
	
	/**
	 * Печать расписания врача as is
	 */
	printSchedule: function() {
		var id_salt = Math.random();
		var win_id = 'print_TTMSO_edit' + Math.floor(id_salt * 10000);
		window.open(C_TTMSO_LISTFOREDITPRINT + '&StartDay=' + this.date + '&MedService_id=' + this.MedService_id, win_id);
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
	*	Передаваемый метод из другого окна для освобождения записи на время
	*/
	userClearTimeMS: null,
	
	/**
	 * Освобождение времени
	 */
	clearTimeMS: function(time_id) 
	{
		if (time_id) {
			this.TimetableMedServiceOrg_id = time_id;
		}
		sw.swMsg.show({
			title: lang['podtverjdenie'],
			msg: lang['vyi_deystvitelno_jelaete_osvobodit_vremya_priema'],
			buttons: Ext.Msg.YESNO,
			fn: function ( buttonId ) {
				if ( buttonId == 'yes' )
				{
					var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Подождите, идет освобождение бирки..." });
					loadMask.show();
					
					if(typeof this.userClearTimeMS == 'function') {
						this.userClearTimeMS();
					} else {
						submitClearTime(
							{
								id: this.TimetableMedServiceOrg_id,
								type: 'medserviceorg',
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
				}
			}.createDelegate(this)
		});
	},
	
	/**
	 * Удаление бирки
	 */
	deleteTime: function(time_id) 
	{
		if (time_id) {
			this.TimetableMedServiceOrg_id = time_id;
		}
		
		sw.swMsg.show({
			title: lang['podtverjdenie'],
			msg: lang['vyi_deystvitelno_jelaete_udalit_birku'],
			buttons: Ext.Msg.YESNO,
			fn: function ( buttonId ) {
				if ( buttonId == 'yes' )
				{
					var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Подождите, идет удаление..." });
					loadMask.show();
					
					submitDeleteTime(
						{
							TimetableMedServiceOrg_id: this.TimetableMedServiceOrg_id,
							TimetableMedServiceOrgGroup: Ext.util.JSON.encode(this.selectedTTMSO)
						},
						function(response) {
							var response_data = Ext.util.JSON.decode(response.responseText);
							if (response_data && response_data.success) {
								if (this.selectedTTMSO.length == 0) {
									// если работает только с одной биркой, то меняем ее тип без обновления с сервера
									this.TimetableMedServiceOrg_Element.style.visibility = 'hidden';
								} else {
									// иначе берем все целиком с сервера
									this.loadSchedule();
								}
							}
							loadMask.hide();
						}.createDelegate(this),
						function() {
							loadMask.hide();
						},
						C_TTMSO_DELETE
					);
				}
			}.createDelegate(this)
		});
	},
	
	/**
	 * Редактирование бирки
	 */
	editTime: function(time_id)
	{
		var editedTTMSO = [];
		// Если нет выделенных бирок, то берем только текущую бирку
		if ( this.selectedTTMSO.length == 0 ) {
			editedTTMSO.push(time_id);
		} else {
			editedTTMSO = this.selectedTTMSO;
		}

		getWnd('swTTMSOScheduleEditTTMSOWindow').show({
			selectedTTMSO: editedTTMSO,
			callback: function() {
				this.loadSchedule();
			}.createDelegate(this)
		});
	},
	
	/**
	 * Изменение типа бирки
	 */
	changeTTMSOType: function(time_id, type) 
	{
		if (time_id) {
			this.TimetableMedServiceOrg_id = time_id;
		}
		
		var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Подождите, идет сохранение..." });
		loadMask.show();
		
		submitChangeTTType(
			{
				TimetableMedServiceOrg_id: this.TimetableMedServiceOrg_id,
				TimetableType_id: type,
				TimetableMedServiceOrgGroup: Ext.util.JSON.encode(this.selectedTTMSO)
			},
			function(options, success, response) {
				loadMask.hide();
				if (this.selectedTTMSO.length >= 1) {
					// если работает только с одной биркой, то меняем ее тип без обновления с сервера
					var addclass = '';
					// если была дополнительной, то дополнительной и остаётся
					if (this.TimetableMedServiceOrg_Element.className.indexOf('dop') >= 0) {
						addclass += ' dop';
					}
					var response_data = Ext.util.JSON.decode(options.responseText);
					if(response_data&&response_data.TimetableType_Name){
						this.TimetableMedServiceOrg_Element.setAttribute('ext:qtip',lang['svobodno']+response_data.TimetableType_Name);
					}
					this.TimetableMedServiceOrg_Element.className = 'work active ' + 'TimetableType_' + type + addclass;
				} else {
					// иначе берем все целиком с сервера
					this.loadSchedule();
				}
			}.createDelegate(this),
			function() {
				loadMask.hide();
			},
			C_TTMSO_SETTYPE
		);
	},
	
    initComponent: function() {
		
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
				text: lang['pred'],
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
				text: lang['sled'],
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
			{
				id: this.id + '_createScheduleBtn',
				iconCls: 'create-schedule16',
				text: lang['sozdat_raspisanie'],
				tooltip : "Создать расписание <b>(F2)</b>",
				handler: function () {
					this.openFillWindow();
				}.createDelegate(this)
			},
			'-', 
			{
				iconCls: 'print16',
				text: lang['pechat'],
				tooltip : "Печать расписания <b>(F9)</b>",
				handler: function () {
					this.printSchedule();
				}.createDelegate(this)
			}
			],
			style: "border-bottom: 1px solid #99BBE8;"
		});
		
		/**
		 * Панель с расписанием. Данные в панель формируются прямо на сервере и загружаются в виде чистого HTML
		 */
		this.schedule = new Ext.Panel({
			autoScroll:true,
	    	region: 'center',
	        id: 'schedule',
	        frame: false,
	        loadMask : true
		});
		
		/**
		 * Меню смены типа бирки
		 */
		this.mnuSetType = new Ext.ux.menu.StoreMenu({
			url:'/?c=Reg&m=getTimetableTypeMenu',
			listeners: {
				itemclick: function(item) {
					this.changeTTMSOType(this.TimetableMedServiceOrg_id, item.id)
				}.createDelegate(this)
			},
			onLoad: function(store, records) {
				this.getSetTypeMenu().updateMenuItems(true,records);
				
				// При первоначальной загрузке проставляем класс бирки
				if ( this.TimetableMedServiceOrg_Element ) {
					var classes = this.TimetableMedServiceOrg_Element.className.split(" ");
					for (var i = 0; i < classes.length; i++) {
						if (classes[i].indexOf( 'TimetableType_' ) >= 0 ) {
							var id = classes[i].substr(14);
							this.getSetTypeMenu().items.item(id).setChecked(true, true);
							break;
						}
					}
				}
			}.createDelegate(this),
			baseParams: {
				Place_id: 3
			}
		});
		
		/**
		 * Меню редактирования бирки
		 */
		this.mnuContext = new Ext.menu.Menu({
			items: [
				{
					id: 'settype-time',
					hidden: true,
					text: lang['izmenit_tip_birki'],
					icon: 'img/icons/drug-viewmnn16.png',
					menu: this.mnuSetType
				}, {
					id: 'clear-time',
					text: lang['osvobodit_birku'],
					icon: 'img/icons/delete16.png'
				}, {
					id: 'delete-time',
					text: lang['udalit_birku'],
					icon: 'img/icons/delete16.png'
				}, {
					id: 'edit-time',
					hidden: true,
					text: lang['redaktirovat'],
					icon: 'img/icons/edit16.png'
				}, {
					id: 'show-history',
					hidden: true,
					text: lang['prosmotr_istorii'],
					icon: 'img/icons/history16.png'
				}
			],
			listeners: {
				itemclick: function(item) {
					switch (item.id) {
						case 'delete-time':
							this.deleteTime();
						break;
								
						case 'clear-time':
							this.clearTimeMS();
						break;
						
						case 'edit-time':
							this.editTime(this.TimetableMedServiceOrg_id);
						break;
							
						case 'show-history':
							this.openTTHistoryWindow();
						break;
							
					}
				}.createDelegate(this)
			}
		});
	    
	    Ext.apply(this, {
	    	//autoHeight: true,
	    	layout: 'border',
			items: [
				this.schedule
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
	    sw.Promed.swTTMSOScheduleEditPanel.superclass.initComponent.apply(this, arguments);
    }
});
