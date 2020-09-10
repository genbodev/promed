/**
 * Панель редактирования расписания службы
 */
sw.Promed.swTTRScheduleEditPanel = Ext.extend(Ext.Panel, {
	id: 'schedule',
	frame: false,
	loadMask : true,
	
	listeners: {
		hide: function() {
			this.userClearTimeR = null;
		}
	},
	
	/**
	 * Идентификатор выбранной бирки
	 */
	TimetableResource_id: null,
	
	/**
	 * Идентификатор связанного направления
	 */
	EvnDirection_id: null,
	
	/**
	 * Элемент выбранной бирки
	 */
	TimetableResource_Element: null,
	
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
	Resource_id: null,
	
	/**
	 * Набор выбранных бирок для групповой работы с ними
	 */
	selectedTTR: [],

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
		this.getContextMenu().items.item('create-annotation').setVisible(!readOnly);
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

		var url = '';
		if ( !this.Resource_id ) {
			// если ресурс не задан то вообще не выдаём расписание, т.к. расписание на службу настроенную на работу с ресурсами не ведется.
			return false;

			url = C_TTMS_LISTFOREDIT;
		} else {
			url = C_TTR_LISTFOREDIT;
		}
		this.getSchedule().load(
			{
				url: url,
				params: {
					StartDay: this.date,
					MedService_id: this.MedService_id,
					Resource_id: this.Resource_id,
					PanelID: this.id, // отправляем идентификатор панели для правильной генерации HTML
					readOnly: this.readOnly
				},
				scripts:true,
				timeout: 300,
				text: lang['podojdite_idet_zagruzka_raspisaniya'],
				callback: function () {
					// Очищаем массив выбранных бирок при перезагрузке расписания
					this.selectedTTR = [];
				}.createDelegate(this),
				failure: function () {
					Ext.Msg.alert(lang['oshibka'], lang['oshibka_polucheniya_raspisaniya_poprobuyte_esche_raz']);
				}
			}
		);
		
		this.TTRAnnotationGrid.getGrid().getStore().baseParams = {Resource_id: this.Resource_id};
		Ext.getCmp('TTRAnnotationFilter').doSearch();
	},
	
	/**
	 * Открытие окна для заполнения расписания
	 */
	openFillWindow: function(dt)
	{
		getWnd('swTTRScheduleFillWindow').show({
			date: dt,
			MedService_id: this.MedService_id,
			Resource_id: this.Resource_id,
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
		if ( this.selectedTTR.length <= 1 ) { // если не выделена ни одна бирка или только одна бирка
			this.TimetableResource_id = timeId;
			this.TimetableResource_Element = el;
			this.EvnDirection_id = evndirection_id;
			
			this.getContextMenu().show(el);
			// Если на бирке есть человек, то открываем пункт "Освободить бирку",
			// но закрываем "Удалить бирку" и "Редактировать бирку"
			if ( PersonId != 0 ) {
				this.getContextMenu().items.item('settype-time').disable();
				this.getContextMenu().items.item('clear-time').enable();
				this.getContextMenu().items.item('delete-time').disable();
			} else { // и наоборот
				this.getContextMenu().items.item('settype-time').enable();
				this.getContextMenu().items.item('clear-time').disable();
				this.getContextMenu().items.item('delete-time').enable();
			}
			this.getContextMenu().items.item('show-history').enable();
			this.getContextMenu().items.item('create-annotation').enable();
			
			// Бирки на прошедшие дни удалять и отменять нельзя https://redmine.swan.perm.ru/issues/78656
			if ( el.classList.contains('old') && getGlobalOptions().disallow_tt_actions_for_elapsed_time == true ) {
				this.getContextMenu().items.item('clear-time').disable();
				this.getContextMenu().items.item('delete-time').disable();				
			}
			
			if ( this.getSetTypeMenu().items.length > 0 ) {
				this.getSetTypeMenu().items.each(function(item) {
					item.setChecked(false, true);
				})
				
				if (this.TimetableResource_Element) {
					var classes = this.TimetableResource_Element.className.split(" ");
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
			this.TimetableResource_id = null;
			
			this.getContextMenu().items.item('settype-time').enable();
			this.getContextMenu().items.item('clear-time').disable();
			this.getContextMenu().items.item('delete-time').enable();
			this.getContextMenu().items.item('show-history').disable();
			this.getContextMenu().items.item('create-annotation').disable();
			
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
			this.selectedTTR.splice($.inArray(timeId, this.selectedTTR),1);
		} else {
			// Если бирка не выделена - выделяем
			$(el).addClass('selected');
			// Добавляем бирку в массив выбранных бирок
			this.selectedTTR.push(timeId);
		}
	},

	/**
	 * Сообщение об невозможности очистки дня целиком и предложение удалить только свободные бирки
	 */
	clearFreeTTR: function(day)
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
							Resource_id: this.Resource_id,
							type: 'resource'
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
							Resource_id: this.Resource_id,
							type: 'resource'
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
	openEditDayCommentWindow: function(date)
	{
		var win = this; 
		if (!date && this.TimetableResource_id) {
			var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Подождите, идет загрузка..." });
			loadMask.show();
			Ext.Ajax.request({
				url: '/?c=TimetableResource&m=getTTRInfo',
				params: { TimetableResource_id: this.TimetableResource_id },
				failure: function(response, options) {
					loadMask.hide();
					Ext.Msg.alert(lang['oshibka'], 'При создании примечания произошла ошибка');
				},
				success: function(response, action) {
					loadMask.hide();
					var response_data = Ext.util.JSON.decode(response.responseText);
					if (response_data && response_data.length && response_data[0].TimetableResource_abegTime) {
						var dtb = Date.parseDate(response_data[0].TimetableResource_abegTime, 'Y-m-d H:i:s');
						if (!Ext.isEmpty(response_data[0].TimetableResource_nextTime)) {
							var dte = Date.parseDate(response_data[0].TimetableResource_nextTime, 'Y-m-d H:i:s');
							dte.setTime(dte.getTime() - 1*60*1000);
						}
						else {
							var dte = Date.parseDate(response_data[0].TimetableResource_abegTime, 'Y-m-d H:i:s');
							dte.setTime(dte.getTime() + 14*60*1000);
						}
						getWnd('swAnnotationEditWindow').show({
							action: 'add',
							Date: Ext.util.Format.date(dtb, 'd.m.Y'),
							Annotation_begTime: Ext.util.Format.date(dtb, 'H:i'),
							Annotation_endTime: Ext.util.Format.date(dte, 'H:i'),
							MedService_id: win.MedService_id,
							Resource_id: win.Resource_id,
							AnnotationType_id: 4,
							callback: function() {
								win.loadSchedule();
							}
						});
					} else {
						Ext.Msg.alert(lang['oshibka'], 'При создании примечания произошла ошибка');
					}
				}
			});
		} 
		else {
			getWnd('swAnnotationEditWindow').show({
				action: 'add',
				Date: date,
				MedService_id: win.MedService_id,
				Resource_id: win.Resource_id,
				AnnotationType_id: 4,
				callback: function() {
					win.loadSchedule();
				}
			});
		}
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
		getWnd('swTTRScheduleAddDopWindow').show({
			date: dt,
			MedService_id: this.MedService_id,
			Resource_id: this.Resource_id,
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
			TimetableResource_id: this.TimetableResource_id,
			callback: function() {
				
			}.createDelegate(this)
		});
	},

	/**
	 * Список записанных из расписания
	 */
	openDayListTTR: function(date, userMedStaffFact)
	{
		getWnd('swDirectionMasterWindow').show({
			type: 'RecordTTROneDay',
			date: date,
			useCase: 'show_list_only',
			personData: {},
			onClose: function() {
				this.buttons[0].show();
				this.buttons[1].show();
			},
			userMedStaffFact: userMedStaffFact
		});
	},
	
	/**
	 * Печать расписания врача as is
	 */
	printSchedule: function() {
		var id_salt = Math.random();
		var win_id = 'print_TTR_edit' + Math.floor(id_salt * 10000);
		window.open(C_TTR_LISTFOREDITPRINT + '&StartDay=' + this.date + '&Resource_id=' + this.Resource_id, win_id);
	},
	
	/**
	 * Изменение даты в фильтре примечений
	 */
	doResetAnnotationDate: function(date, isforce) {
		this.findById('TTRAnnotationFilter').doResetDate(date, isforce);
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

	onDateChange: function() {
		this.doResetAnnotationDate(this.date, true);
		this.loadSchedule();
	},
	
	/**
	*	Передаваемый метод из другого окна для освобождения записи на время
	*/
	userClearTimeR: null,

	/**
	 * Освобождение времени
	 */
	clearTimeR: function(time_id) 
	{
		if (time_id) {
			this.TimetableResource_id = time_id;
		}
		if (this.EvnDirection_id) {
			getWnd('swSelectDirFailTypeWindow').show({
				time_id: this.TimetableResource_id,
                LpuUnitType_SysNick: 'resource',
				onClear: function() {
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
						
						if(typeof this.userClearTimeR == 'function') {
							this.userClearTimeR();
						} else {
							submitClearTime(
								{
									id: this.TimetableResource_id,
									type: 'resource',
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
		}
	},
	
	/**
	 * Удаление бирки
	 */
	deleteTime: function(time_id) 
	{
		if (time_id) {
			this.TimetableResource_id = time_id;
		}
		
		sw.swMsg.show({
			title: lang['podtverjdenie'],
			msg: lang['vyi_deystvitelno_jelaete_udalit_birku'],
			buttons: Ext.Msg.YESNO,
			fn: function ( buttonId ) {
				if ( buttonId == 'yes' )
				{
					var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Подождите, идет сохранение..." });
					loadMask.show();
					
					submitDeleteTime(
						{
							TimetableResource_id: this.TimetableResource_id,
							TimetableResourceGroup: Ext.util.JSON.encode(this.selectedTTR)
						},
						function(response) {
							var response_data = Ext.util.JSON.decode(response.responseText);
							if (response_data && response_data.success) {
								if (this.selectedTTR.length == 0) {
									// если работает только с одной биркой, то меняем ее тип без обновления с сервера
									this.TimetableResource_Element.style.visibility = 'hidden';
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
						C_TTR_DELETE
					);
				}
			}.createDelegate(this)
		});
	},
	
	/**
	 * Изменение типа бирки
	 */
	changeTTRType: function(time_id, type) 
	{
		if (time_id) {
			this.TimetableResource_id = time_id;
		}
		
		var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Подождите, идет сохранение..." });
		loadMask.show();
		
		submitChangeTTType(
			{
				TimetableResource_id: this.TimetableResource_id,
				TimetableType_id: type,
				TimetableResourceGroup: Ext.util.JSON.encode(this.selectedTTR)
			},
			function(options, success, response) {
				loadMask.hide();
				if (this.selectedTTR.length == 0) {
					// если работает только с одной биркой, то меняем ее тип без обновления с сервера
					var addclass = '';
					// если была дополнительной, то дополнительной и остаётся
					if (this.TimetableResource_Element.className.indexOf('dop') >= 0) {
						addclass += ' dop';
					}
					this.TimetableResource_Element.className = 'work active ' + 'TimetableType_' + type + addclass;
				} else {
					// иначе берем все целиком с сервера
					this.loadSchedule();
				}
			}.createDelegate(this),
			function() {
				loadMask.hide();
			},
			C_TTR_SETTYPE
		);
	},
		
	/**
	 * Открытие окна редактирования примечания
	 */
	openAnnotationWindow: function(action) {
	
		if (action != 'add') {
			var grid = this.findById('TTRAnnotationGrid').getGrid();
			if ( grid.getSelectionModel().getSelected() && grid.getSelectionModel().getSelected().get('Annotation_id') ) {
				var selectedRecord = grid.getSelectionModel().getSelected();
			}
		}
		
		getWnd('swAnnotationEditWindow').show({
			action: action,
			Annotation_id: selectedRecord ? selectedRecord.get('Annotation_id') : null,
			Resource_id: this.Resource_id,
			callback: function() {
				this.loadSchedule();
			}.createDelegate(this)
		});
	},
	
		
	/**
	 * Удаление примечания
	 */
	deleteAnnotation: function() {
	
		var win = this;
        var grid = this.findById('TTRAnnotationGrid').getGrid();
        if ( grid.getSelectionModel().getSelected() && grid.getSelectionModel().getSelected().get('Annotation_id') ) {
            var selectedRecord = grid.getSelectionModel().getSelected();
        }
		
		if (!selectedRecord) {
			return false;
		}
		
		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( buttonId == 'yes' ) {
					Ext.Ajax.request({
						url: '/?c=Annotation&m=delete',
						params: { Annotation_id: selectedRecord.get('Annotation_id') },
						failure: function(response, options) {
							Ext.Msg.alert(lang['oshibka'], lang['pri_udalenii_primechaniya_proizoshla_oshibka']);
						},
						success: function(response, action) {
							if (response.responseText) {
								var action = Ext.util.JSON.decode(response.responseText);

								if (!action.success) {
									if ( action.Error_Msg ) {
										sw.swMsg.alert(lang['oshibka'], action.Error_Msg);
									} else {
										Ext.Msg.alert(lang['oshibka'], lang['pri_udalenii_primechaniya_proizoshla_oshibka']);
									}
								} else {
									win.loadSchedule();
								}
							} else {
								Ext.Msg.alert(lang['oshibka'], lang['pri_udalenii_primechaniya_proizoshla_oshibka']);
							}
						}
					});
				}
			}.createDelegate(this),
			icon: Ext.MessageBox.QUESTION,
			msg: lang['udalit_primechanie'],
			title: lang['vopros']
		});
	},
	
    initComponent: function() {
	
		var win = this;
		
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
						this.date = Ext.util.Format.date(inp.getValue(), 'd.m.Y');
						this.onDateChange();
					}
				}.createDelegate(this),
				'select': function () 
				{
					this.date = this.calendar.value;
					this.onDateChange();
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
					this.onDateChange();
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
					this.onDateChange();
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
		
		this.TTRAnnotationGrid = new sw.Promed.ViewFrame({
			region: 'center',
			id: 'TTRAnnotationGrid',
			object: 'Annotation',
			dataUrl: '/?c=Annotation&m=loadList',
			editformclassname: '',
			autoExpandColumn: 'autoexpand',
			autoLoadData: false,
			saveAtOnce: false,
			stringfields: [
				{ name: 'Annotation_id', key: true, type:'int', hidden:true },
				{ name: 'AnnotationType_Name', header: lang['tip'], width: 150},
				{ name: 'AnnotationClass_Name', header: lang['vid'], width: 120},
				{ name: 'AnnotationVison_Name', header: lang['vidimost'], width: 80},
				{ name: 'Annotation_Date', header: lang['period_deystviya'], width: 150},
				{ name: 'Annotation_Time', header: lang['vremya_deystviya'], width: 100},
				{ name: 'Annotation_Comment', header: lang['tekst'], id: 'autoexpand'}
			],
			actions: [
				{name:'action_add', handler: function() { win.openAnnotationWindow('add'); }},
				{name:'action_edit', handler: function() { win.openAnnotationWindow('edit'); }},
				{name:'action_view', handler: function() { win.openAnnotationWindow('view'); }},
				{name: 'action_delete', handler: function() { win.deleteAnnotation(); }},
				{name: 'action_refresh'},
				{name: 'action_print'},
				{name: 'action_save', hidden: true}
			],
			onAfterEdit: function(o) {
				
			},
			onLoadData: function() {
				var grid = win.TTRAnnotationGrid.getGrid();
				if ( grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('Annotation_id') ) {
					grid.getStore().removeAll();
				}
			}
		});
		
		this.TTRAnnotationPanel = new Ext.Panel({
			title: lang['primechaniya'],
			layout: 'border',
			region: 'south',
			border: false,
			height: 270,
			collapsible: true,
			style: 'padding-bottom: 27px;',
			plugins: [ Ext.ux.PanelCollapsedTitle ],
			listeners:
			{
				collapse: function()
				{
					this.ownerCt.ownerCt.ownerCt.doLayout();
				}
			},
			items: [
				new Ext.form.FormPanel({
					id: 'TTRAnnotationFilter',
					labelAlign: 'right',
					region: 'north',
					height: 30,
					frame: true,
					doReset: function() {
						this.getForm().reset();
						this.doResetDate(win.date, true);
					},
					doResetDate: function(date, isforce) {
						if (!date) return;
						// какая-то магия, чтобы данные с формы нормально уходили
						if (Ext.getCmp('TTRAnnotationFilterFieldset').collapsed) {
							Ext.getCmp('TTRAnnotationFilterFieldset').expand();
							Ext.getCmp('TTRAnnotationFilterFieldset').collapse();
						}
						var form = this.getForm();
						if (!isforce && !Ext.isEmpty(form.findField('Annotation_DateRange').getValue1())) {
							return false;
						}
						var enddate = Date.parseDate(date, 'd.m.Y');
						enddate.setDate(enddate.getDate() + 13);
						var dt = date + ' - ' + enddate.dateFormat('d.m.Y');
						form.findField('Annotation_DateRange').setValue(dt);
					},
					doSearch: function() {
						var form = this.getForm();
						if (form.isValid()) {
							var params = form.getValues();
							win.TTRAnnotationGrid.getGrid().getStore().baseParams = Ext.apply(win.TTRAnnotationGrid.getGrid().getStore().baseParams, params);
							win.TTRAnnotationGrid.getGrid().getStore().load();
						}
					},
					items: [{
						id: 'TTRAnnotationFilterFieldset',
						xtype: 'fieldset',
						title: lang['filtr'],
						style: 'padding: 5px;',
						autoHeight: true,
						collapsible: true,
						collapsed: true,
						keys: [{
							key: Ext.EventObject.ENTER,
							fn: function(e) {
								var form = this.findById('TTRAnnotationFilter');
								form.doSearch();
							}.createDelegate(this),
							stopEvent: true
						}, {
							ctrl: true,
							fn: function(inp, e) {
								var form = this.findById('TTRAnnotationFilter');
								form.doReset();
							}.createDelegate(this),
							key: 188,
							scope: this,
							stopEvent: true
						}],
						listeners:{
							expand:function () {
								this.ownerCt.setHeight(90);
								this.ownerCt.ownerCt.setHeight(330);
								this.ownerCt.ownerCt.ownerCt.syncSize();
							},
							collapse:function () {
								this.ownerCt.setHeight(30);
								this.ownerCt.ownerCt.setHeight(270);
								this.ownerCt.ownerCt.ownerCt.syncSize();
							}
						},
						items: [{
							layout: 'column',
							items: [{
								width: 380,
								layout: 'form',
								labelWidth: 200,
								items:[{
									xtype: 'daterangefield',
									plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false) ],
									name: 'Annotation_DateRange',
									width: 170,
									fieldLabel: 'Период действия примечания'
								}, {
									comboSubject: 'AnnotationVison',
									fieldLabel: lang['vidimost'],
									hiddenName: 'AnnotationVison_id',
									width: 170,
									xtype: 'swcommonsprcombo'
								}]
							}, {
								width: 300,
								layout: 'form',
								labelWidth: 70,
								items:[{
									comboSubject: 'AnnotationType',
									fieldLabel: lang['tip'],
									hiddenName: 'AnnotationType_id',
									width: 200,
									xtype: 'swcommonsprcombo'
								}, {
									fieldLabel: lang['tekst'],
									width: 200,
									name: 'Annotation_Comment',
									xtype: 'textfield'
								}]
							}, {
								layout: 'form',
								items: [{
									style: "padding-left: 30px; padding-top: 0;",
									xtype: 'button',
									id: 'swStaffBtnSearch',
									text: lang['nayti'],
									iconCls: 'search16',
									handler: function() {
										var form = Ext.getCmp('TTRAnnotationFilter');
										form.doSearch();
									}
								}, {
									style: "padding-left: 30px; padding-top: 5px;",
									xtype: 'button',
									id: 'swStaffBtnClean',
									text: lang['sbros'],
									iconCls: 'reset16',
									handler: function() {
										var form = Ext.getCmp('TTRAnnotationFilter');
										form.doReset();
										form.doSearch();
									}
								}]
							}]
						}]
					}]
				}),
				this.TTRAnnotationGrid
			]
		});
		
		/**
		 * Меню смены типа бирки
		 */
		this.mnuSetType = new Ext.ux.menu.StoreMenu({
			url:'/?c=Reg&m=getTimetableTypeMenu',
			listeners: {
				itemclick: function(item) {
					this.changeTTRType(this.TimetableResource_id, item.id)
				}.createDelegate(this)
			},
			onLoad: function(store, records) {
				this.getSetTypeMenu().updateMenuItems(true,records);
				
				// При первоначальной загрузке проставляем класс бирки
				if ( this.TimetableResource_Element ) {
					var classes = this.TimetableResource_Element.className.split(" ");
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
					id: 'show-history',
					hidden:!isCallCenterAdmin() && !isPolkaRegistrator(),
					text: lang['prosmotr_istorii'],
					icon: 'img/icons/history16.png'
				}, {
					id: 'create-annotation',
					text: 'Создать примечание',
					icon: 'img/icons/info16.png'
				}
			],
			listeners: {
				itemclick: function(item) {
					switch (item.id) {
						case 'delete-time':
							this.deleteTime();
						break;
								
						case 'clear-time':
							this.clearTimeR();
						break;
							
						case 'show-history':
							this.openTTHistoryWindow();
						break;
						
						case 'create-annotation':
							this.openEditDayCommentWindow();
						break;
							
					}
				}.createDelegate(this)
			}
		});
	    
	    Ext.apply(this, {
	    	//autoHeight: true,
	    	layout: 'border',
			items: [
				this.schedule,
				this.TTRAnnotationPanel
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
	    sw.Promed.swTTRScheduleEditPanel.superclass.initComponent.apply(this, arguments);

		this.date = Ext.util.Format.date(this.calendar.value, 'd.m.Y');
    }
});
