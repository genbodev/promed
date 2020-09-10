/**
* swSendMBUViewWindow - Журнал отправки результатов в ПАК НИЦ МБУ
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      All
* @access       public
* @autor		Марков Андрей
* @copyright    Copyright (c) 2011-2019 Swan Ltd.
* @version      ноябрь.2019
*/
sw.Promed.swSendMBUViewWindow = Ext.extend(sw.Promed.BaseForm, {
	title: langs('Передача данных в ПАК НИЦ МБУ'),
	//iconCls: '',
	maximized: true,
	maximizable: true,
	modal: false,
	shim: false,
	plain: true,
	buttonAlign: "right",
	closeAction: 'hide',
	id: 'swSendMBUViewWindow',
	buttons: [
		'-',
		{
			text: BTN_FRMHELP,
			iconCls: 'help16',
			handler: function(button, event)
			{
				ShowHelp(this.ownerCt.title);
			}
		}, {
			text      : BTN_FRMCLOSE,
			tabIndex  : -1,
			tooltip   : lang['zakryit'],
			iconCls   : 'cancel16',
			handler   : function()
			{
				this.ownerCt.hide();
			}
		}
	],
	listeners: {
		'hide': function(w)
		{
			w.doReset();
		}
	},
	show: function() {
		var that = this;
		sw.Promed.swSendMBUViewWindow.superclass.show.apply(this, arguments);
		this.viewOnly = false;
		if (arguments[0]) {
			this.MedService_id = arguments[0].MedService_id || null;
			this.MedServiceType_SysNick = arguments[0].MedServiceType_SysNick || null;
			if(arguments[0].viewOnly)
				this.viewOnly = arguments[0].viewOnly;
		}

		this.Grid.setReadOnly(this.viewOnly);
		
		this.getCurrentDateTime();
		// сбросить фильтр 
		this.setTitleFieldset();
	},
	setFilter: function(newValue) {
		var form = this.FilterPanel.getForm();
	},
	getCurrentDateTime: function() {
		var that = this;
		if (!getGlobalOptions().date) {
			frm.getLoadMask(LOAD_WAIT).show();
			Ext.Ajax.request({
				url: C_LOAD_CURTIME,
				callback: function(opt, success, response) {
					if (success && response.responseText != '') {
						var result  = Ext.util.JSON.decode(response.responseText);
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
	sendMbu: function(action) {
		// Отправка в ПАК НИЦ МБУ, ручной метод
		var win = this;
		var formParams = new Object();

		var record = win.Grid.getGrid().getSelectionModel().getSelected();
		if (!record || Ext.isEmpty(record.get('MbuPerson_id'))) return;

		formParams.MedService_id = this.MedService_id;
		formParams.MbuPerson_id = record.get('MbuPerson_id');
		formParams.mode = 'manual';
		win.getLoadMask('Отправка данных в ПАК НИЦ МБУ...').show();
		Ext.Ajax.request({
			url: '/?c=Mbu&m=sendMbu',
			params: formParams,
			callback: function(opt, success, response) {
				if (success && response.responseText != '') {
					var result  = Ext.util.JSON.decode(response.responseText);
					win.doSearch();
					win.getLoadMask().hide();
				}
			}
		});
	},
	sendMbuTest: function(action) {
		// Отправка в ПАК НИЦ МБУ, ручной метод, метод для суперадминов для тестирования
		var win = this;
		var formParams = new Object();

		var record = win.Grid.getGrid().getSelectionModel().getSelected();
		if (!record || Ext.isEmpty(record.get('MbuPerson_id'))) return;

		formParams.MedService_id = this.MedService_id;
		formParams.MbuPerson_id = record.get('MbuPerson_id');
		formParams.getDebug = 1;
		Ext.Ajax.request({
			url: '/?c=Mbu&m=sendMbu',
			params: formParams,
			callback: function(opt, success, response) {
				if (success && response.responseText != '') {
					var result  = Ext.util.JSON.decode(response.responseText);
					win.doSearch();
				}
			}
		});
	},
	setTitleFieldset: function() {
		var fieldset = this.FilterPanel.find('xtype', 'fieldset')[0];
		var flag = false;
		fieldset.findBy(function(field){
			if(typeof field.xtype != 'undefined' && field.xtype.inlist(['combo','daterangefield','swnoticetypecombo']))
			{
				if(field.getRawValue() != '')
					flag = true;
			}
		});
		fieldset.setTitle((flag)?lang['filtr_ustanovlen']:lang['filtr']);
	},
	getPeriodToggle: function (mode) {
		switch(mode)
		{
		case 'day':
			return this.WindowToolbar.items.items[6];
			break;
		case 'week':
			return this.WindowToolbar.items.items[7];
			break;
		case 'month':
			return this.WindowToolbar.items.items[8];
			break;
		case 'range':
			return this.WindowToolbar.items.items[9];
			break;
		default:
			return null;
			break;
		}
	},
	doSearch: function(mode) {
		var params = this.FilterPanel.getForm().getValues();
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
			}
			else {
				btn.toggle(true);
				this.mode = mode;
			}
		}
		params.MedService_id = this.MedService_id;
		params.begDate = Ext.util.Format.date(this.dateMenu.getValue1(), 'd.m.Y');
		params.endDate = Ext.util.Format.date(this.dateMenu.getValue2(), 'd.m.Y');
		this.Grid.removeAll();
		this.Grid.loadData({globalFilters: params});
	},
	doReset: function() {
		this.FilterPanel.getForm().reset();
		//this.FilterPanel.getForm().findField('Message_isRead').setValue(0);
		//this.setTitleFieldset();
		//this.Grid.getStore().baseParams = {};
	},
	stepDay: function(day) {
		var frm = this;
		var date1 = (frm.dateMenu.getValue1() || Date.parseDate(frm.curDate, 'd.m.Y')).add(Date.DAY, day).clearTime();
		var date2 = (frm.dateMenu.getValue2() || Date.parseDate(frm.curDate, 'd.m.Y')).add(Date.DAY, day).clearTime();
		frm.dateMenu.setValue(Ext.util.Format.date(date1, 'd.m.Y')+' - '+Ext.util.Format.date(date2, 'd.m.Y'));
		// frm.dateMenu.fireEvent("select", frm.dateMenu);
	},
	prevDay: function () {
		this.stepDay(-1);
	},
	setActionDisabled: function(action, flag) {
		if (this.gridActions[action])
		{
			this.gridActions[action].initialConfig.initialDisabled = flag;
			this.gridActions[action].setDisabled(flag);
		}
	},
	scheduleCollapseDates: function() {
		this.getGrid().getView().collapseAllGroups();
	},
	scheduleExpandDates: function() {
		this.getGrid().getView().expandAllGroups();
	},
	nextDay: function () {
		this.stepDay(1);
	},
	currentDay: function () {
		var frm = this;
		var date1 = Date.parseDate(frm.curDate, 'd.m.Y');
		var date2 = Date.parseDate(frm.curDate, 'd.m.Y');
		frm.dateMenu.setValue(Ext.util.Format.date(date1, 'd.m.Y')+' - '+Ext.util.Format.date(date2, 'd.m.Y'));
		// frm.dateMenu.fireEvent("select", frm.dateMenu);
	},
	currentWeek: function () {
		var frm = this;
		var date1 = (Date.parseDate(frm.curDate, 'd.m.Y'));
		var dayOfWeek = (date1.getDay() + 6) % 7;
		date1 = date1.add(Date.DAY, -dayOfWeek).clearTime();
		var date2 = date1.add(Date.DAY, 6).clearTime();
	frm.dateMenu.setValue(Ext.util.Format.date(date1, 'd.m.Y')+' - '+Ext.util.Format.date(date2, 'd.m.Y'));
		// frm.dateMenu.fireEvent("select", frm.dateMenu);
	},
	currentMonth: function () {
		var frm = this;
		var date1 = (Date.parseDate(frm.curDate, 'd.m.Y')).getFirstDateOfMonth();
		var date2 = date1.getLastDateOfMonth();
	frm.dateMenu.setValue(Ext.util.Format.date(date1, 'd.m.Y')+' - '+Ext.util.Format.date(date2, 'd.m.Y'));
		// frm.dateMenu.fireEvent("select", frm.dateMenu);
	},
	createFormActions: function() {
		
		this.dateMenu = new Ext.form.DateRangeField({
			width: 150,
			fieldLabel: lang['period'],
			plugins: 
			[
				new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)
			]
		});
		
		this.dateMenu.addListener('keydown',function (inp, e) {
			if (e.getKey() == Ext.EventObject.ENTER) {
				e.stopEvent();
				this.doSearch('period');
			}
		}.createDelegate(this));
		this.dateMenu.addListener('select',function () {
			// Читаем расписание за период
			this.doSearch('period');
		}.createDelegate(this));
		
		this.formActions = new Array();
		this.formActions.selectDate = new Ext.Action(
		{
			text: ''
		});
		this.formActions.prev = new Ext.Action(
		{
			text: lang['predyiduschiy'],
			xtype: 'button',
			iconCls: 'arrow-previous16',
			handler: function()
			{
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
			handler: function()
			{
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
			handler: function()
			{
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
			handler: function()
			{
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
			handler: function()
			{
				this.currentMonth();
				this.doSearch('month');
			}.createDelegate(this)
		});
		this.formActions.range = new Ext.Action(
		{
			text: lang['period'],
			disabled: true,
			xtype: 'button',
			toggleGroup: 'periodToggle',
			iconCls: 'datepicker-range16',
			handler: function()
			{
				this.doSearch('range');
			}.createDelegate(this)
		});
	},
	onKeyDown: function (inp, e) {
		if (e.getKey() == Ext.EventObject.ENTER) {
			e.stopEvent();
			this.doSearch();
		}
	},
	initComponent: function() {
		var win = this;
		this.createFormActions();
		this.WindowToolbar = new Ext.Toolbar({
			items: [
				this.formActions.prev, 
				{
					xtype : "tbseparator"
				},
				this.dateMenu,
				//this.dateText,
				{
					xtype : "tbseparator"
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
		this.FilterPanel = new Ext.form.FormPanel({
			floatable: false,
			autoHeight: true,
			animCollapse: false,
			labelAlign: 'right',
			//plugins: [ Ext.ux.PanelCollapsedTitle ],
			defaults: {
				bodyStyle: 'background: #DFE8F6;'
			},
			region: 'north',
			frame: true,
			buttonAlign: 'left',
			keys: [{
				key: Ext.EventObject.ENTER,
				fn: function(e) {
					win.doSearch();
				},
				stopEvent: true
			}],
			items: [{
					xtype: 'fieldset',
					style:'padding: 0px 3px 3px 6px;',
					autoHeight: true,
					listeners: {
						expand: function() {
							this.ownerCt.doLayout();
							win.syncSize();
						},
						collapse: function() {
							win.syncSize();
						}
					},
					collapsible: true,
					collapsed: true,
					title: lang['filtr'],
					bodyStyle: 'background: #DFE8F6;',
					items: [{
						layout: 'column',
						items: [{
							layout: 'form',
							labelWidth: 65,
							items:
								[{
									xtype: 'textfieldpmw',
									width: 150,
									name: 'Search_SurName',
									fieldLabel: 'Фамилия',
									listeners: {
										'keydown': win.onKeyDown.createDelegate(win)
									}
								}]
						}, {
							layout: 'form',
							labelWidth: 45,
							items:
								[{
									xtype: 'textfieldpmw',
									width: 150,
									name: 'Search_FirName',
									fieldLabel: 'Имя',
									listeners: {
										'keydown': win.onKeyDown.createDelegate(win)
									}
								}]
						}, {
							layout: 'form',
							labelWidth: 75,
							items:
								[{
									xtype: 'textfieldpmw',
									width: 150,
									name: 'Search_SecName',
									fieldLabel: 'Отчество',
									listeners: {
										'keydown': win.onKeyDown.createDelegate(win)
									}
								}]
						}/*, {
							layout: 'form',
							labelWidth: 100,
							border: false,
							items: [{
								comboSubject:'MbuStatus',
								fieldLabel:langs('Статус'),
								hiddenName:'MbuStatus_id',
								anchor: '100%',
								xtype:'swcommonsprcombo'
							}]
						}*/]
					}, {
						layout: 'column',
						items: [{
							layout: 'form',
							items: [{
								xtype: 'button',
								handler: function()
								{
									this.doSearch();
								}.createDelegate(this),
								iconCls: 'search16',
								text: BTN_FRMSEARCH
							}]
						},
						{
							layout: 'form',
							style: 'margin-left: 10px;',
							items: [{
								xtype: 'button',
								handler: function()
								{
									win.doReset();
								},
								iconCls: 'resetsearch16',
								text: BTN_FRMRESET
							}]
						}]
					}]
				}
			]
		});
		// Меню с кнопками слева // пока видимо не надо и может даже и не понадобится 
		this.leftMenu = new Ext.Panel({
			region: 'west',
			border: false,
			layout:'form',
			layoutConfig: {
				titleCollapse: true,
				animate: true,
				activeOnTop: false
			},
			items: [] // здесь надо определять и создавать кнопки // я бы вынес это в отдельную функцию (пример: items: this.getLeftButtons)
		});
		// Журнал отображения записей для отправки в ПАК НИЦ МБУ
		this.Grid = new sw.Promed.ViewFrame({
			selectionModel: 'multiselect',
			useEmptyRecord: false,
			region: 'center',
			layout: 'fit',
			autoLoadData: false,
			object: 'MbuPerson',
			dataUrl: '/?c=Mbu&m=loadList',
			autoExpandColumn: 'autoexpand',
			stringfields:[
				// Поля для отображение в гриде
				// Получить отмеченные галочкой записи: swSendMBUViewWindow.Grid.getGrid().getStore().data.filterBy(function (el) {return el.data.access});
				{name: 'MbuPerson_id', type: 'int', header: 'ID', key: true},
				{name: 'Person_id', type: 'int', hidden: true},
				{name: 'MbuStatus_id', type: 'int', hidden: true},
				{name: 'Person_Fio', type:'string', header: langs('Пациент'), width: 200},
				{name: 'MbuPerson_sendDT', type: 'date', format: 'd.m.Y', header: langs('Дата получения результатов'), width: 120},
				{name: 'UslugaComplex_Name', type:'string', direction: 'ASC', header: langs('Тест'), autoexpand: true },
				{name: 'UslugaTest_ResultValue', type:'string', direction: 'ASC', header: langs('Результат'), width: 200 },
				{name: 'MbuStatus_Name', type:'string', direction: 'ASC', header: langs('Статус'), width: 120 }
			],
			actions:[
				{name:'action_add', hidden:true }, // 
				{name:'action_edit', text:'Передать в ПАК НИЦ МБУ', handler: function () { win.sendMbu(); }, iconCls: 'x-btn-text', icon: 'img/icons/actions16.png' },
				{name:'action_view', text:'Передать в ПАК НИЦ МБУ (тестирование)', handler: function () { win.sendMbuTest(); }, hidden: !(isSuperAdmin() && isDebug()), iconCls: 'x-btn-text', icon: 'img/icons/actions16.png' },
				{name:'action_delete', hidden:true },
				{name:'action_refresh'},
				{name:'action_print'}
			],
			onLoadData: function(sm, index, record){
				if (!this.getGrid().getStore().totalLength) {
					this.getGrid().getStore().removeAll();
				}
			},
			onRowSelect: function(sm,rowIdx,record) {
				this.setActionDisabled('action_edit', (record.get('MbuStatus_id') == 3));
				this.setActionDisabled('action_view', (record.get('MbuStatus_id') == 3));
			},
			onDblClick: function() {
				return false;
			},
			onEnter: function() {
				return false;
			}
		});
		this.Grid.ViewGridPanel.view = new Ext.grid.GridView({ // цветовая дифференциация штанов
			getRowClass: function (row, index) {
				var cls = '';
				if ( row.get('MbuStatus_id') == 1 ) { // Готов к отправке
					cls = cls+'x-grid-row ';
				}
				if ( row.get('MbuStatus_id') == 2 ) { // Не возможно отправить
					cls = cls+'x-grid-rowblue ';
				}
				if ( row.get('MbuStatus_id') == 3 ) { // Данные переданы
					cls = cls+'x-grid-rowgray ';
				}
				if ( row.get('MbuStatus_id') == 4 ) { // Ошибка при передаче
					cls = cls+'x-grid-rowred ';
				}

				if ( cls.length == 0 ) {
					cls = 'x-grid-panel'; 
				}

				return cls;
			}
		});


		var that = this;
		/*this.Grid.ViewGridPanel.on('rowclick', function (grid, rowIndex) {
			var row = grid.store.data.items[rowIndex].data;
			if (!Ext.isEmpty(row)) {
				that.Grid.setActionDisabled('action_edit', (row.MbuStatus_id != 2));
			}
		});*/


		this.CenterPanel = new sw.Promed.Panel({
			region: 'center',
			border: false,
			layout: 'border',
			items: [ /*this.leftMenu,*/ this.Grid]
		});
		Ext.apply(this, {
			layout: 'border',
			tbar: this.WindowToolbar,
			items: [
				this.FilterPanel,
				this.CenterPanel
			]
		});
		sw.Promed.swSendMBUViewWindow.superclass.initComponent.apply(this, arguments);
	}
});