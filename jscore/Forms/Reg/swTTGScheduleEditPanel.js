/**
 * Панель редактирования расписания врача
 */
sw.Promed.swTTGScheduleEditPanel = Ext.extend(Ext.Panel, {
	id: 'schedule',
	frame: false,
	loadMask : true,

	/**
	 * Идентификатор выбранной бирки
	 */
	TimetableGraf_id: null,
	
	/**
	 * Идентификатор связанного направления
	 */
	EvnDirection_id: null,
	
	/**
	 * Бирка занята через интернет
	 */
	Inet_user: null,
	
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
	 * Набор выбранных бирок для групповой работы с ними
	 */
	selectedTTG: [],

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

		this.getSchedule().load(
			{
				url: C_TTG_LISTFOREDIT,
				params: {
					StartDay: this.date,
					MedStaffFact_id: this.MedStaffFact_id,
					PanelID: this.id, // отправляем идентификатор панели для правильной генерации HTML
					readOnly: this.readOnly
				},
				scripts:true,
				text: lang['podojdite_idet_zagruzka_raspisaniya'],
				callback: function () {
					// Очищаем массив выбранных бирок при перезагрузке расписания
					this.selectedTTG = [];
				}.createDelegate(this),
				failure: function () {
					Ext.Msg.alert(lang['oshibka'], lang['oshibka_polucheniya_raspisaniya_poprobuyte_esche_raz']);
				}
			}
		);
		
		this.TTGSAnnotationGrid.getGrid().getStore().baseParams = {MedStaffFact_id: this.MedStaffFact_id ? this.MedStaffFact_id : getGlobalOptions().CurMedStaffFact_id};
		Ext.getCmp('TTGSAnnotationFilter').doSearch();
	},
	
	/**
	 * Открытие окна для заполнения расписания
	 */
	openFillWindow: function(dt)
	{
		getWnd('swTTGScheduleFillWindow').show({
			date: dt,
			MedStaffFact_id: this.MedStaffFact_id,
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
	openContextMenu : function ( el, timeId, PersonId, evndirection_id, inet_user) {
		if ( this.selectedTTG.length <= 1 ) { // если не выделена ни одна бирка или только одна бирка
			this.TimetableGraf_id = timeId;
			this.TimetableGraf_Element = el;
			this.EvnDirection_id = evndirection_id;
			this.Inet_user = inet_user;

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
			if ( el.classList.contains('old') && getGlobalOptions().disallow_tt_actions_for_elapsed_time == 'true' ) {
				this.getContextMenu().items.item('settype-time').disable();
				this.getContextMenu().items.item('clear-time').disable();
				this.getContextMenu().items.item('delete-time').disable();				
			}
			
			if ( this.getSetTypeMenu().items.length > 0 ) {
				this.getSetTypeMenu().items.each(function(item) {
					item.setChecked(false, true);
				})
				if (this.TimetableGraf_Element) {
					var classes = this.TimetableGraf_Element.className.split(" ");
					for (var i = 0; i < classes.length; i++) {
						if (classes[i].indexOf( 'TimetableType_' ) >= 0 && classes[i].indexOf( '_person' ) == -1 ) {
							var id = parseInt(classes[i].substr(14));
							if ( this.getSetTypeMenu().items.item(id) ) {
								this.getSetTypeMenu().items.item(id).setChecked(true, true);
							}
							break;
						}
					}
				}
				
			}
			
			this.getContextMenu().show(el);
		} else {
			this.TimetableGraf_id = null;
			
			this.getContextMenu().items.item('settype-time').enable();
			this.getContextMenu().items.item('clear-time').disable();
			this.getContextMenu().items.item('delete-time').enable();
			this.getContextMenu().items.item('show-history').disable();
			this.getContextMenu().items.item('create-annotation').disable();
			
			// Бирки на прошедшие дни удалять и отменять нельзя
			var minTTG = Math.min.apply(null, this.selectedTTG);
			if ( minTTG && Ext.getDom('TTG_'+minTTG).classList.contains('old') && getGlobalOptions().disallow_tt_actions_for_elapsed_time == 'true' ) {
				this.getContextMenu().items.item('settype-time').disable();
				this.getContextMenu().items.item('delete-time').disable();				
			}
			
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
			this.selectedTTG.splice($.inArray(timeId, this.selectedTTG),1);
		} else {
			// Если бирка не выделена - выделяем
			$(el).addClass('selected');
			// Добавляем бирку в массив выбранных бирок
			this.selectedTTG.push(timeId);
		}
	},
	
	/**
	 * Сообщение об невозможности очистки дня целиком и предложение удалить только свободные бирки
	 */
	clearFreeTTG: function(day)
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
							MedStaffFact_id: this.MedStaffFact_id,
							UslugaComplex_id: null,
							type: 'polka'
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
							MedStaffFact_id: this.MedStaffFact_id,
							UslugaComplex_id: null,
							type: 'polka'
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
		var MedStaffFact_id = this.MedStaffFact_id ? this.MedStaffFact_id : getGlobalOptions().CurMedStaffFact_id;
		if (!date && this.TimetableGraf_id) {
			var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Подождите, идет загрузка..." });
			loadMask.show();
			Ext.Ajax.request({
				url: '/?c=TimetableGraf&m=getTTGInfo',
				params: { TimetableGraf_id: this.TimetableGraf_id },
				failure: function(response, options) {
					loadMask.hide();
					Ext.Msg.alert(lang['oshibka'], 'При создании примечания произошла ошибка');
				},
				success: function(response, action) {
					loadMask.hide();
					var response_data = Ext.util.JSON.decode(response.responseText);
					if (response_data && response_data.length && response_data[0].TimetableGraf_abegTime) {
						var dtb = Date.parseDate(response_data[0].TimetableGraf_abegTime, 'Y-m-d H:i:s');
						if (!Ext.isEmpty(response_data[0].TimetableGraf_nextTime)) {
							var dte = Date.parseDate(response_data[0].TimetableGraf_nextTime, 'Y-m-d H:i:s');
							dte.setTime(dte.getTime() - 1*60*1000);
						} 
						else if (!Ext.isEmpty(response_data[0].MedStaffFact_PriemTime)) {
							var dte = Date.parseDate(response_data[0].TimetableGraf_abegTime, 'Y-m-d H:i:s');
							dte.setTime(dte.getTime() + ( response_data[0].MedStaffFact_PriemTime - 1 )*60*1000 );
						}
						else {
							var dte = Date.parseDate(response_data[0].TimetableGraf_abegTime, 'Y-m-d H:i:s');
							dte.setTime(dte.getTime() + 14*60*1000);
						}
						getWnd('swAnnotationEditWindow').show({
							action: 'add',
							Date: Ext.util.Format.date(dtb, 'd.m.Y'),
							Annotation_begTime: Ext.util.Format.date(dtb, 'H:i'),
							Annotation_endTime: Ext.util.Format.date(dte, 'H:i'),
							MedStaffFact_id: MedStaffFact_id,
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
				MedStaffFact_id: MedStaffFact_id,
				AnnotationType_id: 4,
				callback: function() {
					win.loadSchedule();
				}
			});
		}
	},
	
	/**
	 * Открытие окна для комментария на врача
	 */
	openMedStaffFactCommentWindow: function(day)
	{
		getWnd('swEditMedStaffFactCommentWindow').show({
			MedStaffFact_id: this.MedStaffFact_id,
			callback: function() {
				this.loadSchedule();
			}.createDelegate(this)
		})
	},
	
	/**
	 * Открытие окна для добавления дополнительной бирки
	 */
	openAddDopWindow: function(dt)
	{
		getWnd('swTTGScheduleAddDopWindow').show({
			date: dt,
			MedStaffFact_id: this.MedStaffFact_id,
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
			TimetableGraf_id: this.TimetableGraf_id,
			callback: function() {
				
			}.createDelegate(this)
		});
	},
	/**
	 * Список записанных из расписания
	 */
	openDayListTTG: function(date, userMedStaffFact)
	{
		getWnd('swDirectionMasterWindow').show({
			type: 'RecordTTGOneDay',
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
		var win_id = 'print_ttg_edit' + Math.floor(id_salt * 10000);
		var win = window.open(C_TTG_LISTFOREDITPRINT + '&StartDay=' + this.date + '&MedStaffFact_id=' + this.MedStaffFact_id, win_id);
	},
	
	/**
	 * Изменение даты в фильтре примечений
	 */
	doResetAnnotationDate: function(date, isforce) {
		this.findById('TTGSAnnotationFilter').doResetDate(date, isforce);
	},

	/**
	 * Перемещение по календарю
	 */
	stepDay: function(day)
	{
		var date = (this.calendar.getValue() || Date.parseDate(this.date, 'd.m.Y')).add(Date.DAY, day).clearTime();
		this.calendar.setValue(Ext.util.Format.date(date, 'd.m.Y'));
		this.date = Ext.util.Format.date(date, 'd.m.Y');
		this.doResetAnnotationDate(Ext.util.Format.date(date, 'd.m.Y'), true);
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
	clearTime: function(time_id) 
	{
		if (time_id) {
			this.TimetableGraf_id = time_id;
		}
		if (this.EvnDirection_id) {
			getWnd('swSelectDirFailTypeWindow').show({
				time_id: this.TimetableGraf_id,
                LpuUnitType_SysNick: 'polka',
				onClear: function() {
					this.loadSchedule();
				}.createDelegate(this)
			});
		} else if (this.Inet_user) {
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
	 * Удаление бирки
	 */
	deleteTime: function(time_id) 
	{
		if (time_id) {
			this.TimetableGraf_id = time_id;
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
							TimetableGraf_id: this.TimetableGraf_id,
							TimetableGrafGroup: Ext.util.JSON.encode(this.selectedTTG)
						},
						function(response) {
							loadMask.hide();
							var response_data = Ext.util.JSON.decode(response.responseText);
							if (response_data && response_data.success) {
								if (this.selectedTTG.length == 0) {
									// если работает только с одной биркой, то скрываем ее без обновления с сервера
									this.TimetableGraf_Element.style.visibility = 'hidden';
								} else {
									// иначе берем все целиком с сервера
									this.loadSchedule();
								}
							}
						}.createDelegate(this),
						function() {
							loadMask.hide();
						},
						C_TTG_DELETE
					);
				}
			}.createDelegate(this)
		});
	},
	
	/**
	 * Изменение типа бирки
	 */
	changeTTGType: function(time_id, type) 
	{
		if (time_id) {
			this.TimetableGraf_id = time_id;
		}
		
		var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Подождите, идет сохранение..." });
		loadMask.show();
		submitChangeTTType(
			{
				TimetableGraf_id: this.TimetableGraf_id,
				TimetableType_id: type,
				TimetableGrafGroup: Ext.util.JSON.encode(this.selectedTTG)
			},
			function(options, success, response) {
				loadMask.hide();
				console.log(this.selectedTTG);
				if (this.selectedTTG.length <=1) {
					// если работает только с одной биркой, то меняем ее тип без обновления с сервера
					var addclass = '';
					// если была дополнительной, то дополнительной и остаётся
					if (this.TimetableGraf_Element.className.indexOf('dop') >= 0) {
						addclass += ' dop';
					}
					var response_data = Ext.util.JSON.decode(options.responseText);
					if(response_data&&response_data.TimetableType_Name){
						this.TimetableGraf_Element.setAttribute('ext:qtip',lang['svobodno']+response_data.TimetableType_Name);
					}
					// Если на сервере не поменяли статус, зачем менять на клиенте?
					if(response_data&&response_data.success)
						this.TimetableGraf_Element.className = 'work active ' + 'TimetableType_' + type + addclass;
					this.selectedTTG = [];
				} else {
					// иначе берем все целиком с сервера
					this.loadSchedule();
				}
			}.createDelegate(this),
			function() {
				loadMask.hide();
			},
			C_TTG_SETTYPE
		);
	},
		
	/**
	 * Открытие окна редактирования примечания
	 */
	openAnnotationWindow: function(action) {
	
		if (action != 'add') {
			var grid = this.findById('TTGSAnnotationGrid').getGrid();
			if ( grid.getSelectionModel().getSelected() && grid.getSelectionModel().getSelected().get('Annotation_id') ) {
				var selectedRecord = grid.getSelectionModel().getSelected();
			}
		}
		
		getWnd('swAnnotationEditWindow').show({
			action: action,
			Annotation_id: selectedRecord ? selectedRecord.get('Annotation_id') : null,
			MedStaffFact_id: this.MedStaffFact_id ? this.MedStaffFact_id : getGlobalOptions().CurMedStaffFact_id,
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
        var grid = this.findById('TTGSAnnotationGrid').getGrid();
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
						this.doResetAnnotationDate(Ext.util.Format.date(inp.getValue(), 'd.m.Y'), true);
						this.loadSchedule(Ext.util.Format.date(inp.getValue(), 'd.m.Y'));
					}
				}.createDelegate(this),
				'select': function () 
				{
					this.doResetAnnotationDate(this.calendar.value, true);
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
	        loadMask : true,
		});
		
		this.TTGSAnnotationGrid = new sw.Promed.ViewFrame({
			region: 'center',
			id: 'TTGSAnnotationGrid',
			object: 'Annotation',
			dataUrl: '/?c=Annotation&m=loadList',
			editformclassname: '',
			autoExpandColumn: 'autoexpand',
			autoLoadData: false,
			saveAtOnce: false,
			stringfields: [
				{ name: 'Annotation_id', key: true, type:'int', hidden:true },
				// это поле необходимо для сортировки
				{ name: 'Annotation_Beg_Date', type: 'date', hidden:true },
				{ name: 'AnnotationType_Name', header: lang['tip'], width: 150},
				{ name: 'AnnotationClass_Name', header: lang['vid'], width: 120},
				{ name: 'AnnotationVison_Name', header: lang['vidimost'], width: 80},
				//это поле сортиуруется по скрытому полю 'Annotation_Beg_Date', листенер 'datachanged' внизу
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

				var grid = win.TTGSAnnotationGrid.getGrid();

				if ( grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('Annotation_id') ) {
					grid.getStore().removeAll();
				}

				//добавляем листенер события сортировки скрытого поля 'Annotation_Beg_Date'
				grid.getStore().addListener('datachanged', function(store) {

					// если сортировка по полю включена
					if (store.sortInfo) {

						//если это поле комбинированное поле даты
						if (store.sortInfo['field'] == 'Annotation_Date')
						{
							var direction = '';

							// если до этого уже была выполнена сортировка по этому полю
							if (store.sortToggle.Annotation_Beg_Date) {

								d = store.sortToggle.Annotation_Beg_Date;

								//меняем направление сортировки
								if (d == 'ASC')
									direction = 'DESC';

								if (d == 'DESC')
									direction = 'ASC';
							}
							//иначе берем значение направления из комбинированного поля
							else
								direction = store.sortInfo['direction'];

							//сортируем поле
							store.sort( 'Annotation_Beg_Date', direction);
						}
					}

				});
			}
		});

		this.TTGSAnnotationPanel = new Ext.Panel({
			title: lang['primechaniya'],
			layout: 'border',
			region: 'south',
			border: true,
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
					id: 'TTGSAnnotationFilter',
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
						if (Ext.getCmp('TTGSAnnotationFilterFieldset').collapsed) {
							Ext.getCmp('TTGSAnnotationFilterFieldset').expand();
							Ext.getCmp('TTGSAnnotationFilterFieldset').collapse();
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
							win.TTGSAnnotationGrid.getGrid().getStore().baseParams = Ext.apply(win.TTGSAnnotationGrid.getGrid().getStore().baseParams, params);
							win.TTGSAnnotationGrid.getGrid().getStore().load();
						}
					},
					items: [{
						id: 'TTGSAnnotationFilterFieldset',
						xtype: 'fieldset',
						title: lang['filtr'],
						style: 'padding: 5px;',
						autoHeight: true,
						collapsible: true,
						collapsed: true,
						keys: [{
							key: Ext.EventObject.ENTER,
							fn: function(e) {
								var form = this.findById('TTGSAnnotationFilter');
								form.doSearch();
							}.createDelegate(this),
							stopEvent: true
						}, {
							ctrl: true,
							fn: function(inp, e) {
								var form = this.findById('TTGSAnnotationFilter');
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
										var form = Ext.getCmp('TTGSAnnotationFilter');
										form.doSearch();
									}
								}, {
									style: "padding-left: 30px; padding-top: 5px;",
									xtype: 'button',
									id: 'swStaffBtnClean',
									text: lang['sbros'],
									iconCls: 'reset16',
									handler: function() {
										var form = Ext.getCmp('TTGSAnnotationFilter');
										form.doReset();
										form.doSearch();
									}
								}]
							}]
						}]
					}]
				}),
				this.TTGSAnnotationGrid
			]
		});
		
		/**
		 * Меню смены типа бирки
		 */
		this.mnuSetType = new Ext.ux.menu.StoreMenu({
			url:'/?c=Reg&m=getTimetableTypeMenu',
			listeners: {
				itemclick: function(item) {
					this.changeTTGType(this.TimetableGraf_id, item.id)
				}.createDelegate(this)
			},
			onLoad: function(store, records) {
				this.getSetTypeMenu().updateMenuItems(true,records);
				// При первоначальной загрузке проставляем класс бирки
				if (this.TimetableGraf_Element) {
					var classes = this.TimetableGraf_Element.className.split(" ");
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
				Place_id: 1
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
					hidden:!isCallCenterAdmin() && !isPolkaRegistrator() && !(getRegionNick() == 'pskov' && (isSuperAdmin() || isLpuAdmin())),
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
							this.clearTime();
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
				this.TTGSAnnotationPanel
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
	    sw.Promed.swTTGScheduleEditPanel.superclass.initComponent.apply(this, arguments);
    }
});