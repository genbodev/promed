/**
 * swVKJournalWindow - Журнал запросов ВК
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/projects/promed
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009-2010, Swan.
 * @author       Dmitry Storozhev
 * @version      01.11.2011
 */
/*NO PARSE JSON*/

sw.Promed.swVKJournalWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swVKJournalWindow',
	objectSrc: '/jscore/Forms/Mse/swVKJournalWindow.js',
	closable: true,
	closeAction: 'hide',
	maximized: true,
	title: 'Журнал запросов ВК',
	id: 'swVKJournalWindow',
	readOnly: false,
	getCalendar: function() {
		//return this.calendar;
	},
	getGrid: function() {
		return this.EvnVKGrid.getGrid();
	},
	getPeriodToggle: function(mode) {
		switch (mode) {
			case 'day':
				return this.TopToolbar.items.items[6];
			case 'week':
				return this.TopToolbar.items.items[7];
			case 'month':
				return this.TopToolbar.items.items[8];
			case 'range':
				return this.TopToolbar.items.items[9];
			default:
				return null;
		}
	},
	doReset: function() {
		var filtersForm = this.TopPanel.getForm();
		filtersForm.reset();
		filtersForm.findField('Person_BirthDay_To').setMaxValue(getGlobalOptions().date);
		filtersForm.findField('Person_BirthDay_From').fireEvent('change', filtersForm.findField('Person_BirthDay_From'), filtersForm.findField('Person_BirthDay_From').getValue());
		filtersForm.findField('Person_BirthDay_To').fireEvent('change', filtersForm.findField('Person_BirthDay_To'), filtersForm.findField('Person_BirthDay_To').getValue());
		filtersForm.findField('EvnStatus_id').setFieldValue('EvnStatus_SysNick', 'Agreement');
		if (this.userMedStaffFact.LpuSection_id) {
			filtersForm.findField('LpuSection_id').setValue(this.userMedStaffFact.LpuSection_id);
			filtersForm.findField('LpuSection_id').fireEvent('change', filtersForm.findField('LpuSection_id'), filtersForm.findField('LpuSection_id').getValue());
		}
	},
	scheduleLoad: function(mode) {
		var btn = this.getPeriodToggle(mode);
		if (btn) {
			if (mode !== 'range') {
				if (this.mode === mode) {
					btn.toggle(true);
					if (mode !== 'day') // чтобы при повторном открытии тоже происходила загрузка списка записанных на этот день
						return false;
				} else {
					this.mode = mode;
				}
			} else {
				btn.toggle(true);
				this.mode = mode;
			}
		}
		var params = this.TopPanel.getForm().getValues();

		params.start = 0;
		params.limit = 100;
		params.begDate = Ext.util.Format.date(this.dateMenu.getValue1(), 'd.m.Y');
		params.endDate = Ext.util.Format.date(this.dateMenu.getValue2(), 'd.m.Y');
		params.MedService_id = this.MedService_id;

		this.EvnVKGrid.loadData({globalFilters: params});
	},
	schedulePrint: function() {
		Ext.ux.GridPrinter.print(this.getGrid());
	},
	savePosition: function() {
		var record = this.getGrid().getSelectionModel().getSelected();
		if (record) {
			this.position = record.get('TimetableGraf_id');
		} else {
			this.position = 0;
		}
	},
	restorePosition: function() {
		if ((this.position) && (this.position > 0)) {
			GridAtRecord(this.getGrid(), 'TimetableGraf_id', this.position);
		} else {
			this.getGrid().focus();
		}
		this.position = 0;
	},
	stepDay: function(day) {
		var frm = this;
		var date1 = (frm.dateMenu.getValue1() || Date.parseDate(frm.curDate, 'd.m.Y')).add(Date.DAY, day).clearTime();
		var date2 = (frm.dateMenu.getValue2() || Date.parseDate(frm.curDate, 'd.m.Y')).add(Date.DAY, day).clearTime();
		frm.dateMenu.setValue(Ext.util.Format.date(date1, 'd.m.Y') + ' - ' + Ext.util.Format.date(date2, 'd.m.Y'));
		// frm.dateMenu.fireEvent("select", frm.dateMenu);
	},
	prevDay: function() {
		this.stepDay(-1);
	},
	scheduleCollapseDates: function() {
		this.getGrid().getView().collapseAllGroups();
	},
	scheduleExpandDates: function() {
		this.getGrid().getView().expandAllGroups();
	},
	nextDay: function() {
		this.stepDay(1);
	},
	currentDay: function() {
		var frm = this;
		var date1 = Date.parseDate(frm.curDate, 'd.m.Y');
		var date2 = Date.parseDate(frm.curDate, 'd.m.Y');
		frm.dateMenu.setValue(Ext.util.Format.date(date1, 'd.m.Y') + ' - ' + Ext.util.Format.date(date2, 'd.m.Y'));
		// frm.dateMenu.fireEvent("select", frm.dateMenu);
	},
	currentWeek: function() {
		var frm = this;
		var date1 = (Date.parseDate(frm.curDate, 'd.m.Y'));
		var dayOfWeek = (date1.getDay() + 6) % 7;
		date1 = date1.add(Date.DAY, -dayOfWeek).clearTime();
		var date2 = date1.add(Date.DAY, 6).clearTime();
		frm.dateMenu.setValue(Ext.util.Format.date(date1, 'd.m.Y') + ' - ' + Ext.util.Format.date(date2, 'd.m.Y'));
		// frm.dateMenu.fireEvent("select", frm.dateMenu);
	},
	currentMonth: function() {
		var frm = this;
		var date1 = (Date.parseDate(frm.curDate, 'd.m.Y')).getFirstDateOfMonth();
		var date2 = date1.getLastDateOfMonth();
		frm.dateMenu.setValue(Ext.util.Format.date(date1, 'd.m.Y') + ' - ' + Ext.util.Format.date(date2, 'd.m.Y'));
		// frm.dateMenu.fireEvent("select", frm.dateMenu);
	},
	getLoadMask: function(MSG) {
		if (MSG) {
			delete (this.loadMask);
		}
		if (!this.loadMask) {
			this.loadMask = new Ext.LoadMask(Ext.get(this.id), {msg: MSG});
		}
		return this.loadMask;
	},
	getCurrentDateTime: function() {
		var frm = this;
		frm.getLoadMask(LOAD_WAIT).show();
		Ext.Ajax.request(
			{
				url: C_LOAD_CURTIME,
				callback: function(opt, success, response) {
					if (success && response.responseText !== '') {
						var result = Ext.util.JSON.decode(response.responseText);
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
	show: function() {
		sw.Promed.swVKJournalWindow.superclass.show.apply(this, arguments);

		this.userMedStaffFact = arguments[0].userMedStaffFact;

		var win = this;
		var filtersForm = this.TopPanel.getForm();
		filtersForm.reset();

		this.EvnVKGrid.removeAll({clearAll: true});
		this.getCurrentDateTime();

		setLpuSectionGlobalStoreFilter();
		setMedStaffFactGlobalStoreFilter();
		filtersForm.findField('LpuSection_id').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
		filtersForm.findField('MedStaffFact_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));

		this.TopPanel.setVisible(true);
		win.doReset();

		this.viewMenu = new Ext.menu.Menu({
			items: [{
				text: 'Направление',
				handler: function() {
					win.openEvnPrescrVKWindow();
				}
			}, {
				text: 'Сопутствующий документ',
				handler: function() {
					win.openSoputDocumentWindow();
				}
			}]
		});

		this.EvnVKGrid.addActions({
			name: 'action_viewMenu',
			text: langs('Просмотр'),
			iconCls: 'view16',
			menu: this.viewMenu
		}, 1);

		this.EvnVKGrid.addActions({
			name: 'action_toRevision',
			text: langs('На доработку'),
			handler: function() {
				win.toRevision();
			}
		}, 2);

		this.EvnVKGrid.addActions({
			name: 'action_toReception',
			text: langs('На очный прием'),
			handler: function() {
				win.toReception();
			}
		}, 3);

		this.EvnVKGrid.addActions({
			name: 'action_recordVK',
			text: langs('Записать на ВК'),
			handler: function() {
				win.recordVK();
			}
		}, 4);

		this.syncSize();
	},
	openClinExWorkEditWindow: function() {
		var win = this;
		var grid = this.getGrid();
		var record = grid.getSelectionModel().getSelected();
		if (!record || !record.get('EvnVK_id')) return false;
		getWnd('swClinExWorkEditWindow').show({
			EvnVK_id: record.get('EvnVK_id'),
			showtype: 'view'
		});
	},
	toRevision: function(text) {
		var win = this;
		var grid = this.getGrid();
		var record = grid.getSelectionModel().getSelected();
		if (!record || !record.get('EvnPrescrVK_id')) return false;

		if (!text || Ext.isEmpty(text)) {
			Ext.Msg.prompt(langs('Причина отправки на доработку'), langs('Причина'), function(btn, txt) {
				if (btn == 'ok') {
					win.toRevision(txt);
				}
			});
			return;
		}

		win.getLoadMask(LOAD_WAIT_SAVE).show();
		setEvnStatus({
			EvnClass_SysNick: 'EvnPrescrVK',
			EvnStatus_SysNick: 'Rework',
			EvnStatusHistory_Cause: text,
			Evn_id: record.get('EvnPrescrVK_id'),
			callback: function() {
				win.getLoadMask().hide();
				grid.getStore().reload();
			}
		});
	},
	toReception: function(text) {
		var win = this;
		var grid = this.getGrid();
		var record = grid.getSelectionModel().getSelected();
		if (!record || !record.get('EvnPrescrVK_id')) return false;

		if (!text || Ext.isEmpty(text)) {
			Ext.Msg.prompt(langs('Причина запроса очного приема пациента'), langs('Причина'), function(btn, txt) {
				if (btn == 'ok') {
					win.toReception(txt);
				}
			});
			return;
		}

		win.getLoadMask(LOAD_WAIT_SAVE).show();
		setEvnStatus({
			EvnClass_SysNick: 'EvnPrescrVK',
			EvnStatus_SysNick: 'RequestReception',
			EvnStatusHistory_Cause: text,
			Evn_id: record.get('EvnPrescrVK_id'),
			callback: function() {
				win.getLoadMask().hide();
				grid.getStore().reload();
			}
		});
	},
	recordVK: function() {
		var win = this;
		var grid = this.getGrid();
		var record = grid.getSelectionModel().getSelected();
		if (!record || !record.get('EvnPrescrVK_id')) return false;

		getWnd('swUslugaComplexMedServiceListWindow').show({
			userMedStaffFact: this.userMedStaffFact,
			personData: {
				Person_id: record.get('Person_id'),
				Server_id: record.get('Server_id'),
				PersonEvn_id: record.get('PersonEvn_id'),
				Person_IsDead: record.get('Person_IsDead'),
				Person_Firname: record.get('Person_Firname'),
				Person_Secname: record.get('Person_Secname'),
				Person_Surname: record.get('Person_Surname'),
				Person_Birthday: record.get('Person_Birthday')
			},
			dirTypeData: {
				DirType_id: 9,
				DirType_Code: 8
			},
			directionData: {
				Person_id: record.get('Person_id'),
				Server_id: record.get('Server_id'),
				PersonEvn_id: record.get('PersonEvn_id'),
				EvnDirection_pid: null,
				DopDispInfoConsent_id: null,
				Diag_id: null,
				DirType_id: 9,
				MedService_id: this.userMedStaffFact.MedService_id,
				MedStaffFact_id: this.userMedStaffFact.MedStaffFact_id,
				MedPersonal_id: this.userMedStaffFact.MedPersonal_id,
				LpuSection_id: this.userMedStaffFact.LpuSection_id,
				ARMType_id: this.userMedStaffFact.ARMType_id,
				Lpu_sid: getGlobalOptions().lpu_id,
				EvnPrescrVKData: {
					EvnPrescrVK_id: record.get('EvnPrescrVK_id'),
					Diag_id: record.get('Diag_id')
				},
				withDirection: true
			},
			onDirection: function() {
				grid.getStore().reload();
			}
		});
	},
	openEvnPrescrVKWindow: function() {
		var win = this;
		var grid = this.getGrid();
		var record = grid.getSelectionModel().getSelected();
		if (!record || !record.get('EvnPrescrVK_id')) return false;
		getWnd('swEvnPrescrVKWindow').show({
			EvnPrescrVK_id: record.get('EvnPrescrVK_id'),
			action: 'view'
		});
	},
	openSoputDocumentWindow: function() {
		var win = this;
		var grid = this.getGrid();
		var record = grid.getSelectionModel().getSelected();
		if (record && record.get('EvnPrescrMse_id')) {
			getWnd('swDirectionOnMseEditForm').show({
				Person_id: record.get('Person_id'),
				EvnPrescrMse_id: record.get('EvnPrescrMse_id'),
				action: 'view'
			});
		} else if (record && record.get('EvnDirectionHTM_id')) {
			getWnd('swDirectionOnHTMEditForm').show({
				Person_id: record.get('Person_id'),
				EvnDirectionHTM_id: record.get('EvnDirectionHTM_id'),
				action: 'view'
			});
		}
	},
	initComponent: function() {
		var win = this;
		this.dateMenu = new Ext.form.DateRangeField({
			allowBlank: false,
			width: 150,
			fieldLabel: langs('Период'),
			plugins: [
				new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)
			]
		});

		this.dateMenu.addListener('keydown', function(inp, e) {
			var form = Ext.getCmp('swVKJournalWindow');
			if (e.getKey() === Ext.EventObject.ENTER) {
				e.stopEvent();
				form.scheduleLoad('period');
			}
		});
		this.dateMenu.addListener('select', function() {
			// Читаем расписание за период
			var form = Ext.getCmp('swVKJournalWindow');
			form.scheduleLoad('period');
		});

		this.formActions = [];
		this.formActions.selectDate = new Ext.Action({
			text: ''
		});
		this.formActions.prev = new Ext.Action({
			text: langs('Предыдущий'),
			xtype: 'button',
			iconCls: 'arrow-previous16',
			handler: function() {
				// на один день назад
				this.prevDay();
				this.scheduleLoad('range');
			}.createDelegate(this)
		});
		this.formActions.next = new Ext.Action({
			text: langs('Следующий'),
			xtype: 'button',
			iconCls: 'arrow-next16',
			handler: function() {
				// на один день вперед
				this.nextDay();
				this.scheduleLoad('range');
			}.createDelegate(this)
		});
		this.formActions.day = new Ext.Action({
			text: langs('День'),
			xtype: 'button',
			toggleGroup: 'periodToggle',
			iconCls: 'datepicker-day16',
			pressed: true,
			handler: function() {
				this.currentDay();
				this.scheduleLoad('day');
			}.createDelegate(this)
		});
		this.formActions.week = new Ext.Action({
			text: langs('Неделя'),
			xtype: 'button',
			toggleGroup: 'periodToggle',
			iconCls: 'datepicker-week16',
			handler: function() {
				this.currentWeek();
				this.scheduleLoad('week');
			}.createDelegate(this)
		});
		this.formActions.month = new Ext.Action({
			text: langs('Месяц'),
			xtype: 'button',
			toggleGroup: 'periodToggle',
			iconCls: 'datepicker-month16',
			handler: function() {
				this.currentMonth();
				this.scheduleLoad('month');
			}.createDelegate(this)
		});
		this.formActions.range = new Ext.Action({
			text: langs('Период'),
			disabled: true,
			xtype: 'button',
			toggleGroup: 'periodToggle',
			iconCls: 'datepicker-range16',
			handler: function() {
				this.scheduleLoad('range');
			}.createDelegate(this)
		});

		this.TopToolbar = new Ext.Toolbar({
			items: [
				this.formActions.prev,
				{
					xtype: "tbseparator"
				},
				this.dateMenu,
				//this.dateText,
				{
					xtype: "tbseparator"
				},
				this.formActions.next,
				{
					xtype: 'tbfill'
				},
				this.formActions.day,
				this.formActions.week,
				this.formActions.month,
				this.formActions.range
			]
		});

		this.TopPanel = new Ext.form.FormPanel({
			region: 'north',
			frame: true,
			border: false,
			//height: 150,
			autoHeight: true,
			tbar: this.TopToolbar,
			items: [{
				layout: 'form',
				labelAlign: 'right',
				labelWidth: 50,
				items: [{
					xtype: 'fieldset',
					//height: 110,
					autoHeight: true,
					collapsible: true,
					style: 'margin: 5px 0 0 0',
					listeners: {
						expand: function() {
							win.TopPanel.doLayout();
							win.doLayout();
						},
						collapse: function() {
							win.TopPanel.doLayout();
							win.doLayout();
						}
					},
					collapsed: false,
					title: langs('Фильтр'),
					layout: 'form',
					items: [{
						layout: 'column',
						items: [{
							layout: 'form',
							width: 400,
							labelWidth: 120,
							items: [{
								xtype: 'textfieldpmw',
								name: 'Person_SurName',
								anchor: '100%',
								fieldLabel: langs('Фамилия'),
								listeners: {
									'keydown': function(inp, e) {
										var form = Ext.getCmp('swVKJournalWindow');
										if (e.getKey() === Ext.EventObject.ENTER) {
											e.stopEvent();
											form.scheduleLoad();
										}
									}
								}
							}, {
								xtype: 'textfieldpmw',
								name: 'Person_FirName',
								anchor: '100%',
								fieldLabel: langs('Имя'),
								listeners: {
									'keydown': function(inp, e) {
										var form = Ext.getCmp('swVKJournalWindow');
										if (e.getKey() === Ext.EventObject.ENTER) {
											e.stopEvent();
											form.scheduleLoad();
										}
									}
								}
							}, {
								xtype: 'textfieldpmw',
								name: 'Person_SecName',
								anchor: '100%',
								fieldLabel: langs('Отчество'),
								listeners: {
									'keydown': function(inp, e) {
										var form = Ext.getCmp('swVKJournalWindow');
										if (e.getKey() === Ext.EventObject.ENTER) {
											e.stopEvent();
											form.scheduleLoad();
										}
									}
								}
							}, {
								layout: 'column',
								items: [{
									layout: 'form',
									width: 240,
									labelWidth: 120,
									items: [{
										xtype: 'swdatefield',
										format: 'd.m.Y',
										plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
										name: 'Person_BirthDay_From',
										fieldLabel: langs('Дата рождения с'),
										listeners: {
											'keydown': function(inp, e) {
												var form = Ext.getCmp('swVKJournalWindow');
												if (e.getKey() === Ext.EventObject.ENTER) {
													e.stopEvent();
													form.scheduleLoad();
												}
											},
											'change': function(field, newValue) {
												var filtersForm = win.TopPanel.getForm();
												if (!Ext.isEmpty(newValue)) {
													filtersForm.findField('Person_BirthDay_To').setMinValue(newValue);
												} else {
													filtersForm.findField('Person_BirthDay_To').setMinValue(null);
												}
											}
										}
									}]
								}, {
									layout: 'form',
									width: 140,
									labelWidth: 15,
									items: [{
										xtype: 'swdatefield',
										format: 'd.m.Y',
										plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
										name: 'Person_BirthDay_To',
										fieldLabel: langs('по'),
										listeners: {
											'keydown': function(inp, e) {
												var form = Ext.getCmp('swVKJournalWindow');
												if (e.getKey() === Ext.EventObject.ENTER) {
													e.stopEvent();
													form.scheduleLoad();
												}
											},
											'change': function(field, newValue) {
												var filtersForm = win.TopPanel.getForm();
												if (!Ext.isEmpty(newValue)) {
													filtersForm.findField('Person_BirthDay_From').setMaxValue(newValue);
												} else {
													filtersForm.findField('Person_BirthDay_From').setMaxValue(getGlobalOptions().date);
												}
											}
										}
									}]
								}]
							}]
						}, {
							layout: 'form',
							width: 400,
							labelWidth: 140,
							items: [{
								anchor: '100%',
								allowBlank: true,
								comboSubject: 'CauseTreatmentType',
								fieldLabel: langs('Причина обращения'),
								hiddenName: 'CauseTreatmentType_id',
								xtype: 'swcommonsprcombo'
							}, {
								allowBlank: true,
								anchor: '100%',
								comboSubject: 'EvnStatus',
								fieldLabel: langs('Статус направления'),
								hiddenName: 'EvnStatus_id',
								moreFields: [
									{name: 'EvnStatus_SysNick', mapping: 'EvnStatus_SysNick'}
								],
								loadParams: {
									params: {
										where: " where EvnClass_id = 73"
									}
								},
								xtype: 'swcommonsprcombo'
							}, {
								allowBlank: true,
								anchor: '100%',
								fieldLabel: langs('Отделение'),
								hiddenName: 'LpuSection_id',
								disabled: true,
								id: 'VKJW_LpuSectionCombo',
								linkedElements: [
									'VKJW_MedStaffactCombo'
								],
								listWidth: 500,
								xtype: 'swlpusectionglobalcombo'
							}, {
								allowBlank: true,
								anchor: '100%',
								fieldLabel: langs('Врач'),
								hiddenName: 'MedStaffFact_id',
								id: 'VKJW_MedStaffactCombo',
								listWidth: 500,
								parentElementId: 'VKJW_LpuSectionCombo',
								xtype: 'swmedstafffactglobalcombo'
							}]
						}, {
							layout: 'form',
							items:
								[{
									style: "padding-left: 20px",
									xtype: 'button',
									text: langs('Найти'),
									iconCls: 'search16',
									handler: function() {
										this.scheduleLoad();
									}.createDelegate(this)
								}]
						}, {
							layout: 'form',
							items: [{
								style: "padding-left: 20px",
								xtype: 'button',
								text: langs('Сброс'),
								iconCls: 'clear16',
								handler: function() {
									win.doReset();
									win.scheduleLoad();
								}
							}]
						}]
					}]
				}]
			}]
		});

		this.EvnVKGrid = new sw.Promed.ViewFrame({
			dataUrl: '/?c=Mse&m=loadVKJournalGrid',
			uniqueId: true,
			border: true,
			autoLoadData: false,
			object: 'EvnPrescrVK',
			region: 'center',
			paging: true,
			root: 'data',
			totalProperty: 'totalCount',
			stringfields: [
				{name: 'EvnPrescrVK_id', type: 'int', header: 'EvnVK_id', key: true},
				{name: 'EvnVK_id', type: 'int', hidden: true},
				{name: 'EvnStatus_SysNick', type: 'string', hidden: true},
				{name: 'Person_id', type: 'int', hidden: true},
				{name: 'Server_id', type: 'int', hidden: true},
				{name: 'PersonEvn_id', type: 'int', hidden: true},
				{name: 'Person_IsDead', type: 'int', hidden: true},
				{name: 'Person_Firname', type: 'string', hidden: true},
				{name: 'Person_Secname', type: 'string', hidden: true},
				{name: 'Person_Surname', type: 'string', hidden: true},
				{name: 'EvnPrescrMse_id', type: 'int', hidden: true},
				{name: 'EvnDirectionHTM_id', type: 'int', hidden: true},
				{name: 'EvnPrescrVK_setDT', type: 'date', header: 'Дата запроса ВК', width: 120},
				{name: 'Person_Fio', type: 'string', header: 'ФИО', width: 120, id: 'autoexpand'},
				{name: 'Person_BirthDay', type: 'date', header: 'Дата рождения', width: 120},
				{name: 'CauseTreatmentType_Name', type: 'string', header: 'Причина обращения', width: 150},
				{name: 'EvnStatus_Name', type: 'string', header: 'Статус направления', width: 150},
				{name: 'MedPersonal_Fio', type: 'string', header: 'Направивший врач', width: 150},
				{name: 'EvnVK_setDT', type: 'date', header: 'Дата проведения ВК', width: 120},
				{
					name: 'EvnVK_link', header: 'Протокол ВК', width: 120, renderer: function(v, p, r) {
						if (r.get('EvnVK_id')) {
							return "<a href='javascript:getWnd(\"swVKJournalWindow\").openClinExWorkEditWindow();'>Протокол ВК</a>";
						} else {
							return '';
						}
					}
				}
			],
			actions: [
				{name: 'action_add', disabled: true, hidden: true},
				{name: 'action_edit', disabled: true, hidden: true},
				{name: 'action_view', disabled: true, hidden: true},
				{name: 'action_delete', disabled: true, hidden: true},
				{name: 'action_refresh'},
				{name: 'action_print'}
			],
			onRowSelect: function(sm, index, record) {
				this.setActionDisabled('action_toRevision', !record.get('EvnStatus_SysNick') || !record.get('EvnStatus_SysNick').inlist(['RequestReception', 'Agreement']));
				this.setActionDisabled('action_toReception', !record.get('EvnStatus_SysNick') || !record.get('EvnStatus_SysNick').inlist(['Agreement']));
				this.setActionDisabled('action_recordVK', !record.get('EvnStatus_SysNick') || !record.get('EvnStatus_SysNick').inlist(['RequestReception', 'Agreement']));
			}
		});

		Ext.apply(this, {
			layout: 'border',
			items: [
				this.TopPanel,
				this.EvnVKGrid
			],
			buttons: [{
				text: '-'
			}, HelpButton(this, TABINDEX_MPSCHED + 98), {
				iconCls: 'cancel16',
				text: BTN_FRMCLOSE,
				handler: function() {
					win.hide();
				}
			}]
		});

		sw.Promed.swVKJournalWindow.superclass.initComponent.apply(this, arguments);
	}
});
