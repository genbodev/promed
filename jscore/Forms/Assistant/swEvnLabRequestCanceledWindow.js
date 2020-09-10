/**
 * Окно показа отменёных заявок
 */
sw.Promed.swEvnLabRequestCanceledWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: 'Журнал отклонённых заявок',
	layout: 'border',
	region: 'center',
	id: 'swEvnLabRequestCanceledWindow',
	modal: true,
	shim: false,
	width: 400,
	resizable: false,
	maximizable: true,
	maximized: true,
	show: function (params) {
		if (params !== undefined && params.MedService_id !== undefined) {
			this.MedService_id = params.MedService_id;
		}
		sw.Promed.swEvnLabRequestCanceledWindow.superclass.show.apply(this, arguments);
		this.getCurrentDateTime();
	},
	getCurrentDateTime: function () {
		var that = this;
		if (!getGlobalOptions().date) {
			frm.getLoadMask(LOAD_WAIT).show();
			Ext.Ajax.request({
				url: C_LOAD_CURTIME,
				callback: function (opt, success, response) {
					if (success && response.responseText !== '') {
						var result = Ext.util.JSON.decode(response.responseText);
						that.curDate = result.begDate;
						// Проставляем время и режим
						that.mode = 'day';
						that.currentDay();
						that.doSearch('day');
						that.getLoadMask().hide();
					}
				}
			});
		} else {
			this.curDate = getGlobalOptions().date;
			// Проставляем время и режим
			this.mode = 'day';
			this.currentDay();
			this.doSearch('day');
		}
	},
	currentDay: function () {
		var frm = this;
		var date1 = Date.parseDate(frm.curDate, 'd.m.Y');
		var date2 = Date.parseDate(frm.curDate, 'd.m.Y');
		frm.dateMenu.setValue(Ext.util.Format.date(date1, 'd.m.Y') + ' - ' + Ext.util.Format.date(date2, 'd.m.Y'));
	},
	currentWeek: function () {
		var frm = this;
		var date1 = (Date.parseDate(frm.curDate, 'd.m.Y'));
		var dayOfWeek = (date1.getDay() + 6) % 7;
		date1 = date1.add(Date.DAY, -dayOfWeek).clearTime();
		var date2 = date1.add(Date.DAY, 6).clearTime();
		frm.dateMenu.setValue(Ext.util.Format.date(date1, 'd.m.Y') + ' - ' + Ext.util.Format.date(date2, 'd.m.Y'));
	},
	currentMonth: function () {
		var frm = this;
		var date1 = (Date.parseDate(frm.curDate, 'd.m.Y')).getFirstDateOfMonth();
		var date2 = date1.getLastDateOfMonth();
		frm.dateMenu.setValue(Ext.util.Format.date(date1, 'd.m.Y') + ' - ' + Ext.util.Format.date(date2, 'd.m.Y'));
	},
	stepDay: function (day) {
		var frm = this;
		var date1 = (frm.dateMenu.getValue1() || Date.parseDate(frm.curDate, 'd.m.Y')).add(Date.DAY, day).clearTime();
		var date2 = (frm.dateMenu.getValue2() || Date.parseDate(frm.curDate, 'd.m.Y')).add(Date.DAY, day).clearTime();
		frm.dateMenu.setValue(Ext.util.Format.date(date1, 'd.m.Y') + ' - ' + Ext.util.Format.date(date2, 'd.m.Y'));
	},
	prevDay: function () {
		this.stepDay(-1);
	},
	nextDay: function () {
		this.stepDay(1);
	},
	getPeriodToggle: function (mode) {
		switch (mode) {
			case 'day':
				return this.WindowToolbar.items.items[9];
			case 'week':
				return this.WindowToolbar.items.items[10];
			case 'month':
				return this.WindowToolbar.items.items[11];
			default:
				return null;
		}
	},
	doSearch: function (mode) {
		var params = {};
		var btn = this.getPeriodToggle(mode);
		if (btn) {
			if (mode !== 'range') {
				if (this.mode === mode) {
					btn.toggle(true);
					if (mode !== 'day')
						return false;
				} else {
					this.mode = mode;
				}
			} else {
				btn.toggle(true);
				this.mode = mode;
			}
		}

		params.MedService_id = this.MedService_id;
		params.begDate = Ext.util.Format.date(this.dateMenu.getValue1(), 'd.m.Y');
		params.endDate = Ext.util.Format.date(this.dateMenu.getValue2(), 'd.m.Y');
		this.GridELRC.removeAll(true);
		this.GridELRC.loadData({globalFilters: params});
	},
	createFormActions: function () {

		this.dateMenu = new Ext.form.DateRangeField({
			width: 150,
			fieldLabel: lang['period'],
			plugins:
				[
					new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)
				]
		});

		this.dateMenu.addListener('keydown', function (inp, e) {
			if (e.getKey() === Ext.EventObject.ENTER) {
				e.stopEvent();
				this.doSearch('period');
			}
		}.createDelegate(this));
		this.dateMenu.addListener('select', function () {
			// Читаем расписание за период
			this.doSearch('period');
		}.createDelegate(this));

		this.formActions = [];
		this.formActions.selectDate = new Ext.Action({text: ''});
		this.formActions.prev = new Ext.Action(
			{
				text: lang['predyiduschiy'],
				xtype: 'button',
				iconCls: 'arrow-previous16',
				handler: function () {
					// на один день назад
					this.prevDay();
					this.doSearch('range');
				}.createDelegate(this)
			});
		this.formActions.next = new Ext.Action(
			{
				text: lang['sleduyuschiy'],
				xtype: 'button',
				iconCls: 'arrow-next16',
				handler: function () {
					// на один день вперед
					this.nextDay();
					this.doSearch('range');
				}.createDelegate(this)
			});
		this.formActions.day = new Ext.Action(
			{
				text: lang['den'],
				xtype: 'button',
				toggleGroup: 'periodToggle',
				iconCls: 'datepicker-day16',
				pressed: true,
				handler: function () {
					this.currentDay();
					this.doSearch('day');
				}.createDelegate(this)
			});
		this.formActions.week = new Ext.Action(
			{
				text: lang['nedelya'],
				xtype: 'button',
				toggleGroup: 'periodToggle',
				iconCls: 'datepicker-week16',
				handler: function () {
					this.currentWeek();
					this.doSearch('week');
				}.createDelegate(this)
			});
		this.formActions.month = new Ext.Action(
			{
				text: lang['mesyats'],
				xtype: 'button',
				toggleGroup: 'periodToggle',
				iconCls: 'datepicker-month16',
				handler: function () {
					this.currentMonth();
					this.doSearch('month');
				}.createDelegate(this)
			});
	},
	initComponent: function () {
		this.createFormActions();
		this.WindowToolbar = new Ext.Toolbar({
			items: [
				{
					xtype: 'tbfill'
				},
				{
					iconCls: 'refresh16',
					text: langs('Обновить'),
					tooltip : langs("Обновить <b>(F5)</b>"),
					handler: function () {
						this.doSearch('period');
					}.createDelegate(this)
				},
				'-',
				this.formActions.prev,
				{
					xtype: "tbseparator"
				},
				this.dateMenu,
				{
					xtype: "tbseparator"
				},
				this.formActions.next,
				'-',
				this.formActions.day,
				this.formActions.week,
				this.formActions.month
			]
		});

		var uslugaNameRendererFn = function (value) {
			var result = '';
			if (!Ext.isEmpty(value) && value[0] === "[" && value[value.length - 1] === "]") {
				var uslugas = Ext.util.JSON.decode(value);
				for (var k in uslugas) {
					if (uslugas[k].UslugaComplex_Name) {
						if (!Ext.isEmpty(result)) {
							result += '<br />';
						}
						result += uslugas[k].UslugaComplex_Name;
					}
				}

				return result;
			} else {
				return value;
			}
		};

		this.GridELRC = new sw.Promed.ViewFrame({
			useEmptyRecord: false,
			region: 'center',
			layout: 'fit',
			autoLoadData: false,
			dataUrl: '/?c=EvnLabRequest&m=getCanceledEvnLabRequests',
			autoExpandColumn: 'autoexpand',
			grouping: true,
			id: this.id + '_grid',
			stringfields: [
				{name: 'EvnDirection_id', type: 'int', hidden: true},
				{name: 'Person_Fio', type: 'string', header: langs('ФИО'), width: 280},
				{name: 'EvnLabRequest_prmTime', type: 'string', header: langs('Дата записи'), width: 100},
				{
					name: 'EvnLabRequest_UslugaName',
					header: langs('Услуга (исследование)'),
					width: 280,
					renderer: uslugaNameRendererFn
				},
				{name: 'EvnDirection_Num', type: 'string', header: langs('Номер направления'), width: 100},
				{name: 'EvnDirection_setDate', type: 'string', header: langs('Дата направления'), width: 100},
				{name: 'Lpu_Nick', type: 'string', header: langs('Медицинская организация'), width: 100},
				{name: 'LpuSection_Name', type: 'string', header: langs('Отделение направления'), width: 200},
				{name: 'EDMedPersonalSurname', type: 'string', header: langs('Фамилия врача'), width: 150},
				{name: 'DirFailType_Name', type: 'string', header: langs('Причина отклонения'), width: 150},
				{name: 'EvnStatusHistory_Cause', type: 'string', header: langs('Комментарий'), width: 300},
			],
			toolbar: false,
			contextmenu: false,
			onLoadData: function () {
				if (!this.getGrid().getStore().totalLength) {
					this.getGrid().getStore().removeAll();
				}
			}
		});
		
		Ext.apply(this, {
			layout: 'border',
			buttons:
				[
					{text: '-'},
					{
						handler: function () {
							this.ownerCt.hide();
						},
						iconCls: 'cancel16',
						text: BTN_FRMCANCEL
					}
				],
			tbar: this.WindowToolbar,
			items: [
				this.GridELRC
			]
		});
		sw.Promed.swEvnLabRequestCanceledWindow.superclass.initComponent.apply(this, arguments);
	}
});