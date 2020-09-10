/**
 * swEvnVKRejectJournalWindow - Журнал отказов в направлении на МСЭ
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

sw.Promed.swEvnVKRejectJournalWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swEvnVKRejectJournalWindow',
	objectSrc: '/jscore/Forms/Mse/swEvnVKRejectJournalWindow.js',
	//тип АРМа, определяется к каким функциям будет иметь доступ врач через ЭМК, например у стоматолога появится ввод талона по стоматологии,
	//у врача параклиники будет доступ только к параклиническим услугам
	ARMType: 'mse',
	closable: true,
	closeAction: 'hide',
	maximized: true,
	title: 'Журнал отказов в направлении на МСЭ',
	iconCls: 'workplace-mp16',
	id: 'swEvnVKRejectJournalWindow',
	readOnly: false,
	getCalendar: function ()
	{
		//return this.calendar;
	},
	getGrid: function ()
	{
		return this.EvnVKGrid.getGrid();
	},
	getPeriodToggle: function (mode)
	{
		switch(mode)
		{
			case 'day':
				return this.TopToolbar.items.items[6];
				break;
			case 'week':
				return this.TopToolbar.items.items[7];
				break;
			case 'month':
				return this.TopToolbar.items.items[8];
				break;
			case 'range':
				return this.TopToolbar.items.items[9];
				break;
			default:
				return null;
				break;
		}
	},
	scheduleLoad: function(mode)
	{
		var btn = this.getPeriodToggle(mode);
		if (btn) {
			if (mode != 'range') {
				if (this.mode == mode) {
					btn.toggle(true);
					if (mode != 'day') // чтобы при повторном открытии тоже происходила загрузка списка записанных на этот день
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

		params.begDate = Ext.util.Format.date(this.dateMenu.getValue1(), 'd.m.Y');
		params.endDate = Ext.util.Format.date(this.dateMenu.getValue2(), 'd.m.Y');
		params.MedService_id = this.MedService_id;


		this.EvnVKGrid.loadData({globalFilters: params});
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
						frm.currentDay();
						frm.scheduleLoad('day');
						frm.getLoadMask().hide();
					}
				}
			});
	},
	show: function()
	{
		sw.Promed.swEvnVKRejectJournalWindow.superclass.show.apply(this, arguments);

		if ( !arguments[0] || !arguments[0].MedService_id ) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() { this.hide(); }.createDelegate(this) );
			return false;
		}

		this.MedService_id = arguments[0].MedService_id;
		this.MedService_Name = arguments[0].MedService_Name;

		this.TopPanel.getForm().reset();

		this.EvnVKGrid.removeAll({ clearAll: true });
		this.getCurrentDateTime();

		this.TopPanel.setVisible(true);

		this.syncSize();
	},
	deleteEvnMse: function()
	{
		var form = this;
		var grid = form.getGrid();
		if (!grid)
		{
			Ext.Msg.alert(lang['oshibka'], lang['spisok_raspisaniy_ne_nayden']);
			return false;
		}
		else if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('EvnMse_id') )
		{
			Ext.Msg.alert(lang['oshibka'], lang['ne_vyibrana_zapis']);
			return false;
		}
		var id = grid.getSelectionModel().getSelected().data['EvnMse_id'];

		sw.swMsg.show({
			icon: Ext.MessageBox.QUESTION,
			msg: lang['vyi_hotite_udalit_obratnyiy_talon'],
			title: lang['vopros'],
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj)
			{
				if ('yes' == buttonId)
				{
					Ext.Ajax.request({
						callback: function(options, success, response) {
							if ( success ) {
								var obj = Ext.util.JSON.decode(response.responseText);
								if(!obj.success) {
									return false;
								}
								grid.getStore().reload();
							}
							else {
								grid.getStore().reload();
								sw.swMsg.alert(lang['oshibka'], lang['pri_udalenii_obratnogo_talonaa_voznikli_oshibki']);
							}
						},
						params: {
							EvnMse_id: id
						},
						url: '/?c=Mse&m=deleteEvnMse'
					});
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
	},
	openEvnMseEditForm: function()
	{
		var win = this;
		var grid = this.getGrid();
		var record = grid.getSelectionModel().getSelected();
		if(!record) return false;
		getWnd('swProtocolMseEditForm').show({
			Person_id: record.get('Person_id'),
			Server_id: record.get('Server_id'),
			EvnMse_id: record.get('EvnMse_id'),
			EvnVK_id: record.get('EvnVK_id'),
			MedService_id: win.MedService_id,
			onHide: function() {
				grid.getStore().reload();
			}
		});
	},
	initComponent: function()
	{
		var win = this;
		this.dateMenu = new Ext.form.DateRangeField({
			width: 150,
			fieldLabel: lang['period'],
			plugins: [
				new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)
			]
		});

		this.dateMenu.addListener('keydown', function(inp, e) {
			var form = Ext.getCmp('swEvnVKRejectJournalWindow');
			if (e.getKey() == Ext.EventObject.ENTER) {
				e.stopEvent();
				form.scheduleLoad('period');
			}
		});
		this.dateMenu.addListener('select', function() {
			// Читаем расписание за период
			var form = Ext.getCmp('swEvnVKRejectJournalWindow');
			form.scheduleLoad('period');
		});

		this.formActions = new Array();
		this.formActions.selectDate = new Ext.Action({
			text: ''
		});
		this.formActions.prev = new Ext.Action({
			text: lang['predyiduschiy'],
			xtype: 'button',
			iconCls: 'arrow-previous16',
			handler: function() {
				// на один день назад
				this.prevDay();
				this.scheduleLoad('range');
			}.createDelegate(this)
		});
		this.formActions.next = new Ext.Action({
			text: lang['sleduyuschiy'],
			xtype: 'button',
			iconCls: 'arrow-next16',
			handler: function() {
				// на один день вперед
				this.nextDay();
				this.scheduleLoad('range');
			}.createDelegate(this)
		});
		this.formActions.day = new Ext.Action({
			text: lang['den'],
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
			text: lang['nedelya'],
			xtype: 'button',
			toggleGroup: 'periodToggle',
			iconCls: 'datepicker-week16',
			handler: function() {
				this.currentWeek();
				this.scheduleLoad('week');
			}.createDelegate(this)
		});
		this.formActions.month = new Ext.Action({
			text: lang['mesyats'],
			xtype: 'button',
			toggleGroup: 'periodToggle',
			iconCls: 'datepicker-month16',
			handler: function() {
				this.currentMonth();
				this.scheduleLoad('month');
			}.createDelegate(this)
		});
		this.formActions.range = new Ext.Action({
			text: lang['period'],
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
							this.TopPanel.doLayout();
							this.doLayout();
						}.createDelegate(this),
						collapse: function() {
							this.TopPanel.doLayout();
							this.doLayout();
						}.createDelegate(this)
					},
					collapsed: false,
					title: lang['filtr'],
					layout: 'form',
					items: [{
						layout: 'column',
						items: [
							{
								layout: 'form',
								width: 300,
								labelWidth: 60,
								hidden: getGlobalOptions().use_depersonalized_expertise,
								items:
									[{
										xtype: 'textfieldpmw',
										name: 'Person_SurName',
										anchor: '100%',
										id: 'msewpSearch_SurName',
										fieldLabel: lang['familiya'],
										listeners: {
											'keydown': function(inp, e) {
												var form = Ext.getCmp('swEvnVKRejectJournalWindow');
												if (e.getKey() == Ext.EventObject.ENTER) {
													e.stopEvent();
													form.scheduleLoad();
												}
											}
										}
									}, {
										xtype: 'textfieldpmw',
										name: 'Person_FirName',
										anchor: '100%',
										id: 'msewpSearch_FirName',
										fieldLabel: lang['imya'],
										listeners: {
											'keydown': function(inp, e) {
												var form = Ext.getCmp('swEvnVKRejectJournalWindow');
												if (e.getKey() == Ext.EventObject.ENTER) {
													e.stopEvent();
													form.scheduleLoad();
												}
											}
										}
									},
										{
											xtype: 'textfieldpmw',
											name: 'Person_SecName',
											anchor: '100%',
											id: 'msewpSearch_SecName',
											fieldLabel: lang['otchestvo'],
											listeners: {
												'keydown': function(inp, e) {
													var form = Ext.getCmp('swEvnVKRejectJournalWindow');
													if (e.getKey() == Ext.EventObject.ENTER) {
														e.stopEvent();
														form.scheduleLoad();
													}
												}
											}
										}]
							},
							{
								layout: 'form',
								width: 300,
								labelWidth: 140,
								items:
									[{
										xtype: 'swdatefield',
										format: 'd.m.Y',
										plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
										name: 'Person_BirthDay',
										id: 'msewpSearch_BirthDay',
										fieldLabel: lang['dr'],
										hidden: getGlobalOptions().use_depersonalized_expertise,
										hideLabel: getGlobalOptions().use_depersonalized_expertise,
										listeners: {
											'keydown': function(inp, e) {
												var form = Ext.getCmp('swEvnVKRejectJournalWindow');
												if (e.getKey() == Ext.EventObject.ENTER) {
													e.stopEvent();
													form.scheduleLoad();
												}
											}
										}
									}, {
										xtype: 'swlpulocalcombo',
										anchor: '100%',
										hiddenName: 'Lpu_id',
										allowBlank: true,
										fieldLabel: lang['mo_prikrepleniya']
									}, {
										xtype: 'combo',
										mode: 'local',
										hiddenName: 'isEvnMse',
										displayField: 'isEvnMse_Text',
										valueField: 'isEvnMse_id',
										store: new Ext.data.SimpleStore({
											key: '',
											autoLoad: true,
											fields: [
												{name: 'isEvnMse_id', type: 'int'},
												{name: 'isEvnMse_Text', type: 'string'}
											],
											data: [[1, lang['sozdan']], [2, lang['ne_sozdan']], [3, lang['vse']]]
										}),
										listeners: {
											render: function(combo) {
												combo.setValue(3);
											}
										},
										triggerAction: 'all',
										editable: false,
										anchor: '100%',
										fieldLabel: lang['obratnyiy_talon']
									}, {
										xtype: 'checkbox',
										mode: 'local',
										name: 'onlyOwnLpu',
										listeners: {
											render: function(combo) {
												combo.setValue(true);
											}
										},
										fieldLabel: 'Только свои МО'
									}]
							},
							{
								layout: 'form',
								items:
									[{
										style: "padding-left: 20px",
										xtype: 'button',
										id: 'msewpBtnSearch',
										text: lang['nayti'],
										iconCls: 'search16',
										handler: function() {
											this.scheduleLoad();
										}.createDelegate(this)
									}]
							},
							{
								layout: 'form',
								items:
									[{
										style: "padding-left: 20px",
										xtype: 'button',
										id: 'msewpBtnClear',
										text: lang['sbros'],
										iconCls: 'clear16',
										handler: function() {
											this.TopPanel.getForm().reset();
											this.scheduleLoad();
										}.createDelegate(this)
									}]
							}
						]
					}
					]
				}]
			}]
		});

		this.EvnVKGrid = new sw.Promed.ViewFrame({
			dataUrl: '/?c=Mse&m=loadEvnVKRejectGrid',
			uniqueId: true,
			border: true,
			autoLoadData: false,
			object: 'EvnVK',
			region: 'center',
			stringfields: [
				{name: 'EvnVK_id', type: 'int', header: 'EvnVK_id', key: true},
				{name: 'Server_id', type: 'int', hidden: true},
				{name: 'EvnMse_id', type: 'int', hidden: true},
				{name: 'EvnVK_setDT', type: 'date', header: 'Дата экспертизы ВК', width: 120},
				{name: 'Diag_Name', type: 'string', header: 'Диагноз основной', width: 200},
				{name: 'Person_Fio', type: 'string', header: 'ФИО пациента', hidden: getGlobalOptions().use_depersonalized_expertise, width: 120, id: 'autoexpand'},
				{name: 'Person_BirthDay', type: 'date', header: 'Дата рождения', hidden: getGlobalOptions().use_depersonalized_expertise, width: 120},
				{name: 'Person_id', type: 'int', header: langs('ИД Пациента'), hidden: !getGlobalOptions().use_depersonalized_expertise, width: 100 },
				{name: 'Lpu_Nick', type: 'string', header: 'МО прикрепления', width: 120},
				{name: 'EvnMse', header: 'Обратный талон', width: 120, renderer: function(v, p, r) {
					if (r.get('EvnMse_id')) {
						return "<a href='javascript:getWnd(\"swEvnVKRejectJournalWindow\").openEvnMseEditForm();'>" + v + "</a>";
					} else {
						return '';
					}
				}},
				{name: 'DiagMse_Name', type: 'string', header: 'Диагноз МСЭ', width: 120},
				{name: 'InvalidGroupType_Name', type: 'string', header: 'Установлена инвалидность', width: 120},
				{name: 'EvnMse_ReExamDate', type: 'string', header: 'Дата переосвидетельствования', width: 120}
			],
			actions: [
				{name:'action_add', hidden: true, disabled: true},
				{name:'action_edit', text: 'Обратный талон', handler: function() {
					win.openEvnMseEditForm();
				}},
				{name:'action_view', hidden: true, disabled: true},
				{name:'action_delete', text: 'Удалить обратный талон', handler: function() {
					win.deleteEvnMse();
				}},
				{name:'action_refresh'},
				{name:'action_print'}
			],
			onRowSelect: function(sm,index,record){
				if (record.get('EvnMse_id')) {
					win.EvnVKGrid.setActionDisabled('action_delete', false);
				} else {
					win.EvnVKGrid.setActionDisabled('action_delete', true);
				}
			}.createDelegate(this)
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
					this.hide();
				}.createDelegate(this)
			}]
		});

		sw.Promed.swEvnVKRejectJournalWindow.superclass.initComponent.apply(this, arguments);
	}
});
