/**
* swReagentAutoRateWindow - окно расхода реактивов - Статистика
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package	  Common
* @access	   public
* @author	   Arslanov
* @version	  06.2015
* @comment	  
*/
sw.Promed.swReagentAutoRateWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: 'Расход реактивов - Статистика',
	layout: 'border',
	region:'center',
	id: 'ReagentAutoRateWindow',
	modal: true,
	shim: false,
	width: 400,
	resizable: false,
	maximizable: true,
	maximized: true,
	listeners: {
		hide: function() {
			this.onHide();
		}
	},
	onHide: Ext.emptyFn,
	show: function(param) {
		var wnd = this;
		sw.Promed.swReagentAutoRateWindow.superclass.show.apply(this, arguments);

		var curr_date = new Date();
		var curMonth = curr_date.getMonth() + 1;
		curMonth = (curMonth < 10 ? '0'+curMonth.toString() : curMonth.toString());

		this.getCurrentDateTime();
		var curDateStr = curr_date.getDate() +'.'+ curMonth +'.'+ curr_date.getFullYear();
		
		//Ставим комбобокс на id лаборатории (медслужбы), в которой сейчас находимся:
		if (param != undefined && param.MedService_id != undefined) {
			//Ext.getCmp('serviceListComboStat').setValue(param.MedService_id);
		}
		
		wnd.doSearch('period');
		
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
		var params = {};
		var btn = this.getPeriodToggle(mode);
		if (btn) {
			if (mode != 'range') {
				if (this.mode == mode) {
					btn.toggle(true);
//					if (mode != 'day') // чтобы при повторном открытии тоже происходила загрузка списка записанных на этот день
//						return false;
				} else {
					this.mode = mode;
				}
			}
			else {
				btn.toggle(true);
				this.mode = mode;
			}
		}
		params.MedService_id = this.formPanel.getForm().findField('MedService_id').getValue();
		params.begDate = Ext.util.Format.date(this.dateMenu.getValue1(), 'd.m.Y');
		params.endDate = Ext.util.Format.date(this.dateMenu.getValue2(), 'd.m.Y');
		//console.log('params:'); console.log(params);
		switch (this.tabPanel.getActiveTab().id)
		{
			case 'tab_onanalyser':
				this.DataGridOnAnalyser.removeAll();
				this.DataGridOnAnalyser.loadData({globalFilters: params});
				break;
		}
	},
	doReset: function() {
//		this.FilterPanel.getForm().reset();
	},
	stepDay: function(day)
	{
		var frm = this;
		var date1 = (frm.dateMenu.getValue1() || Date.parseDate(frm.curDate, 'd.m.Y')).add(Date.DAY, day).clearTime();
		var date2 = (frm.dateMenu.getValue2() || Date.parseDate(frm.curDate, 'd.m.Y')).add(Date.DAY, day).clearTime();
		frm.dateMenu.setValue(Ext.util.Format.date(date1, 'd.m.Y')+' - '+Ext.util.Format.date(date2, 'd.m.Y'));
		frm.dateMenu.fireEvent("select", frm.dateMenu);
	},
	prevDay: function ()
	{
		this.stepDay(-1);
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
		frm.dateMenu.mode = 'oneday';
		frm.dateMenu.fireEvent("select", frm.dateMenu);
	},
	currentWeek: function ()
	{
		var frm = this;
		var date1 = (Date.parseDate(frm.curDate, 'd.m.Y'));
		var dayOfWeek = (date1.getDay() + 6) % 7;
		date1 = date1.add(Date.DAY, -dayOfWeek).clearTime();
		var date2 = date1.add(Date.DAY, 6).clearTime();
		frm.dateMenu.setValue(Ext.util.Format.date(date1, 'd.m.Y')+' - '+Ext.util.Format.date(date2, 'd.m.Y'));
		frm.dateMenu.mode = 'twodays';
		frm.dateMenu.fireEvent("select", frm.dateMenu);
	},
	currentMonth: function ()
	{
		var frm = this;
		var date1 = (Date.parseDate(frm.curDate, 'd.m.Y')).getFirstDateOfMonth();
		var date2 = date1.getLastDateOfMonth();
		frm.dateMenu.setValue(Ext.util.Format.date(date1, 'd.m.Y')+' - '+Ext.util.Format.date(date2, 'd.m.Y'));
		frm.dateMenu.mode = 'twodays';
		frm.dateMenu.fireEvent("select", frm.dateMenu);
	},
	
	createFormActions: function() {
		
		this.dateMenu = new Ext.form.DateRangeFieldAdvanced({
			width: 150,
			showApply: false,
			id:'dateRangeStat',
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
			// период
			this.doSearch('period');
		}.createDelegate(this));
		
		this.formActions = new Array();
		this.formActions.selectDate = new Ext.Action(
		{
			text: ''
		});
		this.formActions.prev = new Ext.Action(
		{
			//text: lang['predyiduschiy'],
			text: '',
			id:'prevArrowLis',
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
			//text: lang['sleduyuschiy'],
			text: '',
			id:'nextArrowList',
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
			id:'dayLis',
			xtype: 'button',
			toggleGroup: 'periodToggle',
			//iconCls: 'datepicker-day16',
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
			id:'weekLis',
			xtype: 'button',
			toggleGroup: 'periodToggle',
			//iconCls: 'datepicker-week16',
			handler: function()
			{
				this.currentWeek();
				this.doSearch('week');
			}.createDelegate(this)
		});
		this.formActions.month = new Ext.Action(
		{
			text: lang['mesyats'],
			id:'monthLis',
			xtype: 'button',
			toggleGroup: 'periodToggle',
			//iconCls: 'datepicker-month16',
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
			hidden: true,
			xtype: 'button',
			toggleGroup: 'periodToggle',
			//iconCls: 'datepicker-range16',
			handler: function()
			{
				this.doSearch('range');
			}.createDelegate(this)
		});
	},
	initComponent: function() {
		var wnd = this;		
		
		this.createFormActions();
		this.WindowToolbar = new Ext.Toolbar({
			id:'WindowToolbarStat',
			buttonAlign: 'right',
			align: 'right',
			//height: 50,
			items: [
				{
					xtype: 'tbfill'
				},
				this.formActions.prev, 
				this.dateMenu,
				//this.dateText,
				this.formActions.next, 
				
				this.formActions.day, 
				this.formActions.week, 
				this.formActions.month,
				this.formActions.range
			]
		});
		
		wnd.DataGridOnAnalyser = new sw.Promed.ViewFrame({
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			border: true,
			dataUrl: '/?c=EvnUsluga&m=getReagentAutoRateCountOnAnalyser',
			region: 'center',
			id: this.id + '_OnAnalyser',
			saveAtOnce:false,
			style: 'margin-bottom: 0px',
			grouping: true,
			//groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length.inlist([2,3,4]) ? "заявки" : "заявок"]})',
			groupTextTpl: '{text}',
			groupingView: {showGroupName: false, showGroupsText: true},
			stringfields: [
				{name: 'idRate', header: 'idRate', key: true},
				{name: 'analyzerFullName', type: 'string', group: true, sort: true, direction: 'ASC', header: 'Анализатор'},
				{name: 'DrugNomen_Name', type: 'string', header: 'Реактив', id: 'autoexpand'},
				{name: 'reagentRateSum', type: 'string', header: 'Расход реактива', width: 110},
				{name: 'unit_Name', type: 'string', header: 'Ед. изм.', width: 80},
				{name: 'test', type: 'string', header: 'Тест', width: 300},
				//{name: 'ReagentNormRate_RateValue', type: 'string', header: 'Норма расхода реактива', width: 130}
				{name: 'testCountSum', type: 'string', header: 'Выполнено тестов', width: 110}
			],
			title: 'Расход реактива по анализаторам',
			//toolbar: false,
			actions: [
				{ name: 'action_add', hidden: true },
				{ name: 'action_edit', hidden: true },
				{ name: 'action_view', hidden: true },
				{ name: 'action_delete', hidden: true },
				//{ name: 'action_refresh', hidden: true },
				{ name: 'action_print'}
			],
			contextmenu: false,
		});

		wnd.formPanel = new Ext.form.FormPanel({
			autoHeight: true,
			buttonAlign: 'left',
			frame: true,
			labelAlign: 'right',
			labelWidth: 120,
			region: 'north',
			items: [{
				
				xtype: 'swmedservicelistcombostat',
				fieldLabel : 'Лаборатория',
				id: 'serviceListComboStat',
				width: 400,
				listWidth: 500,
				hiddenName: 'MedService_id',
				value: '',
				allowBlank: false,
				triggerAction: 'all',
				trigger2Class: 'hideTrigger',
				listeners: {
					'render': function() {
						this.getStore().proxy.conn.url = '/?c=MedService&m=loadMedServiceListStat';
					},
					'keydown': function (inp, e) {
						if (e.getKey() == Ext.EventObject.ENTER) {
							e.stopEvent();
							wnd.doSearch('period');
						}
					},
					'select': function () {
						wnd.doSearch('period');
					}
				}
			}]
		});
		
		wnd.tabPanel = new Ext.TabPanel({
			border: false,
			region: 'center',
			id: 'tabPanel',
			activeTab: 0,
			autoScroll: true,
			enableTabScroll:true,
			layoutOnTabChange: true,
			deferredRender: false,//чтоб рендились все вкладки при создании таба
			plain:true,
			listeners: {
				'tabchange': function() {
					wnd.doSearch('period');
				}
			},
			items:[{
					layout:'border',
					title: 'По анализаторам',
					id: 'tab_onanalyser',
					items:[
						wnd.formPanel,
						wnd.DataGridOnAnalyser
					]
				}
			]
		});
		
		Ext.apply(this, {
			layout: 'border',
			//region:'center',
			buttons:
			[{
				//id: 'ReagentAutoRateWindow_searchButton',
				handler: function() {
					wnd.doSearch('period');
				},
				text : BTN_FRMSEARCH,
				iconCls: 'search16'
			}, 
			/*{
				handler: function() {
					Ext.Ajax.request({
						url: '/?c=AsMlo&m=checkAsMloLabSamples',
						callback: function(options, success, response) {
							if (success) {
								console.log("checkAsMloLabSamples!!!");
								//var result = Ext.util.JSON.decode(response.responseText)[0];
							}
						}
					});
				},
				text : 'Получить результаты с АСМЛО'
			}, */
			{
				text: '-'
			},
			HelpButton(this, 0),
			{
				handler: function() 
				{
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],
			tbar: this.WindowToolbar,
			items:[
				wnd.tabPanel
			]
		});
		sw.Promed.swReagentAutoRateWindow.superclass.initComponent.apply(this, arguments);
	}
});