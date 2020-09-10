/**
* swEvnLabSampleDefectViewWindow - Журнал отбраковки
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      All
* @access       public
* @autor		Марков Андрей
* @copyright    Copyright (c) 2011 Swan Ltd.
* @version      март.2012
*/
sw.Promed.swEvnLabSampleDefectViewWindow = Ext.extend(sw.Promed.BaseForm, {
    convertDates: function (obj){
        for(var field_name in obj) {
            if (obj.hasOwnProperty(field_name)) {
                if (typeof(obj[field_name]) == 'object') {
                    if (obj[field_name] instanceof Date) {
                        obj[field_name] = obj[field_name].format('d.m.Y H:i');
                    }
                }
            }
        }
        return obj;
    },
    title: lang['jurnal_otbrakovki'],
	//iconCls: '',
	maximized: true,
	maximizable: true,
	modal: false,
	shim: false,
	plain: true,
	buttonAlign: "right",
	closeAction: 'hide',
	id: 'swEvnLabSampleDefectViewWindow',
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
		sw.Promed.swEvnLabSampleDefectViewWindow.superclass.show.apply(this, arguments);
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
		/*this.Grid.addActions({name:'action_lis_sync', text: lang['sinhronizatsiya_s_lis'], handler: function (){
			var g = that.Grid;
			
			var selections = g.getGrid().getSelectionModel().getSelections();
			var ArrayId = [];

			for	(var key in selections) {
				if (selections[key].data) {
					ArrayId.push(selections[key].data['EvnLabSample_id']);
				}
			}
			var params = {}
			params.EvnLabSamples = Ext.util.JSON.encode(ArrayId);
			
			that.getLoadMask(lang['sinhronizatsiya_s_lis']).show();
			// получаем выделенную запись
			Ext.Ajax.request({
				url: '/?c=Lis&m=syncSampleDefects',
				params: params,
				callback: function(opt, success, response) {
					that.getLoadMask().hide();
					if (success && response.responseText != '') {
						var result = Ext.util.JSON.decode(response.responseText);
						if (result.success) {
							g.getGrid().getStore().reload();
						} else {
							sw.swMsg.show({
								buttons: Ext.Msg.OK,
								fn: function() {
								},
								icon: Ext.Msg.WARNING,
								msg: result.Error_Msg,
								title: lang['sinhronizatsiya_s_lis']
							});
						}
					}
				}
			});
		}});*/
	},
	setFilter: function(newValue) {
		var form = this.FilterPanel.getForm();
		var lpusection_combo = form.findField('LpuSection_id');
		setLpuSectionGlobalStoreFilter(/*{
			onDate: Ext.util.Format.date(newValue, 'd.m.Y')
		}*/);
		lpusection_combo.getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
		
		var medstafffact_combo = form.findField('MedStaffFact_id');
		setMedStaffFactGlobalStoreFilter({
			LpuSection_id: getGlobalOptions().CurLpuSection_id/*,
			onDate: Ext.util.Format.date(newValue, 'd.m.Y')
			*/
		});
		medstafffact_combo.getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
		
		if (getGlobalOptions().CurLpuSection_id>0) {
			//lpusection_combo.setValue(getGlobalOptions().CurLpuSection_id);
		}
		
		if (getGlobalOptions().CurMedStaffFact_id>0) {
			index = medstafffact_combo.getStore().findBy(function(rec) {
				if ( rec.get('MedPersonal_id') == getGlobalOptions().CurMedPersonal_id && rec.get('LpuSection_id') == lpusection_combo.getValue() ) {
					return true;
				}
				else {
					return false;
				}
			});

			if ( index >= 0 ) {
				medstafffact_combo.setValue(medstafffact_combo.getStore().getAt(index).get('MedStaffFact_id'));
			}
		}
		lpusection_combo.setDisabled(true);
		medstafffact_combo.setDisabled(true);
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
	openEvnLabSampleDefectEditWindow: function(action)
	{
		var win = this;
		var formParams = new Object();
		
		if (getRegionNick() == 'vologda') {
			formParams.MedService_id = this.MedService_id;
			formParams.MedService_sid = this.MedService_id;
			formParams.MedServiceType_SysNick = this.MedServiceType_SysNick;
		}

		if (action != 'add') {
			var record = win.Grid.getGrid().getSelectionModel().getSelected();
			if (!record || Ext.isEmpty(record.get('EvnLabSample_id'))) return;
			
			formParams.EvnLabSample_id = record.get('EvnLabSample_id');
			formParams.EvnLabSample_BarCode = record.get('EvnLabSample_BarCode');
			formParams.DefectCauseType_id = record.get('DefectCauseType_id');
		}
				
		getWnd('swLabSampleDefectEditWindow').show({
			formParams: formParams,
			action: action,
			callback: function() {
				win.Grid.getGrid().getStore().reload();
			}
		});
	},
	deleteEvnLabSampleDefect: function(action) {
		var win = this;
		var record = win.Grid.getGrid().getSelectionModel().getSelected();
		if (!record || Ext.isEmpty(record.get('EvnLabSample_id'))) return;
		
		win.getLoadMask(lang['udalenie_braka_probyi']).show();
		Ext.Ajax.request({
			url: '/?c=EvnLabSample&m=deleteEvnLabSampleDefect',
			params: {
				EvnLabSample_id: record.get('EvnLabSample_id')
			},
			callback: function(opt, success, response) {
				win.getLoadMask().hide();
				win.Grid.getGrid().getStore().reload();
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
		params.MedService_sid  =this.MedService_id;
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
							labelWidth: 100,
							border: false,
							items: [{
								fieldLabel: 'Cito',
								comboSubject: 'YesNo',
								name: 'EvnDirection_IsCito',
								hiddenName: 'EvnDirection_IsCito',
								xtype: 'swcommonsprcombo'
							}]
						},
						{
							layout: 'form',
							labelWidth: 140,
							border: false,
							items: [{
								fieldLabel: lang['issledovanie'],
								name: 'UslugaComplex_id',
								to: 'EvnUslugaPar',
								xtype: 'swuslugacomplexnewcombo',
								showUslugaComplexLpuSection: false,
								listWidth: 450,
								width: 400
							}]
						}]
					}, {
						layout: 'column',
						items: [{
							layout: 'form',
							labelWidth: 100,
							border: false,
							items: [{
								comboSubject:'RefMaterial',
								fieldLabel:lang['biomaterial'],
								hiddenName:'RefMaterial_id',
								anchor: '100%',
								xtype:'swcommonsprcombo'
							}]
						},
						{
							layout: 'form',
							labelWidth: 140,
							border: false,
							items: [{
								prefix: 'lis_',
								comboSubject: 'DefectCauseType',
								fieldLabel:lang['prichina_otbrakovki'],
								typeCode: 'int',
								hiddenName:'DefectCauseType_id',
								xtype:'swcommonsprcombo'
							}]
						}]
					},
					{
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
		// Грид с бубенчиками (основной Журнал отбраковки с группировкой)
		this.Grid = new sw.Promed.ViewFrame({
			selectionModel: 'multiselect',
	        useEmptyRecord: false,
			region: 'center',
			layout: 'fit',
			autoLoadData: false,
			object: 'EvnLabSample',
			dataUrl: '/?c=EvnLabSample&m=loadDefectList',
			autoExpandColumn: 'autoexpand',
			grouping: true,
			//saveAtOnce: false,
			groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length.inlist([2,3,4]) ? "заявки" : "заявок"]})',
			groupingView: {showGroupName: false, showGroupsText: true},
			stringfields:[
				// Поля для отображение в гриде
				// Получить отмеченные галочкой записи: swEvnLabSampleDefectViewWindow.Grid.getGrid().getStore().data.filterBy(function (el) {return el.data.access});
                {name: 'EvnLabSample_id', type: 'int', header: 'ID', key: true},
                {name: 'DefectCauseType_id', type: 'int', hidden: true},
				{name: 'MedService_flag', type: 'int', hidden: true},
				{name: 'EvnLabSample_Status', hidden: true, group: true, sort: true, direction: 'ASC', header: langs('Статус') },
				{name: 'EvnLabSample_BarCode', type:'string', header: langs('Штрих-код пробы'), width: 100},
				{name: 'EvnLabSample_setDT', type:'string', header: langs('Дата и время взятия пробы'), width: 120},
				{name: 'EvnDirection_Num', type:'string', header: langs('Номер направления'), width: 100},
				{name: 'RefMaterial_Name', header: langs('Биоматериал'), width: 160},
				{name: 'DefectCauseType_Name', type:'string', header: langs('Причина отбраковки'), width: 120, autoexpand: true}, 
				{name: 'lis_id', hidden: true, header: 'LisId' }
			],
			actions:[
				{name:'action_add', handler: function () { win.openEvnLabSampleDefectEditWindow('add'); } }, // 
				{name:'action_edit', handler: function () { win.openEvnLabSampleDefectEditWindow('edit'); } },
				{name:'action_view', handler: function () { win.openEvnLabSampleDefectEditWindow('view'); } },
				{name:'action_delete', handler: function () { win.deleteEvnLabSampleDefect(); } },
				{name:'action_refresh'},
				{name:'action_print'}
			],
            onLoadData: function(sm, index, record){
				if (!this.getGrid().getStore().totalLength) {
					this.getGrid().getStore().removeAll();
				}
			}
		});

		var that = this;
		if (getRegionNick() == 'vologda' && this.MedServiceType_SysNick == 'pzm')
		{
			this.Grid.ViewGridPanel.on('rowclick', function (grid, rowIndex) {
				var row = grid.store.data.items[rowIndex].data;
				if (!Ext.isEmpty(row)) {
					var flag = row.MedService_flag != 2;
					that.Grid.setActionDisabled('action_edit', flag);
				}
			});
		}

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
		sw.Promed.swEvnLabSampleDefectViewWindow.superclass.initComponent.apply(this, arguments);
	}
});