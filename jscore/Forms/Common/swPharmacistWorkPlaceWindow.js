/**
 * swPharmacistWorkPlaceWindow - окно рабочего места аптекаря/провизора
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/projects/promed
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009-2011, Swan.
 * @author       Salakhov R.R. (по мотивам творчества Маркова и Арефьева)
 * @prefix       pwp
 * @version      холодная зима 2011-го
 */
/*NO PARSE JSON*/

sw.Promed.swPharmacistWorkPlaceWindow = Ext.extend(sw.Promed.BaseForm,
{
	codeRefresh: true,
	objectName: 'swPharmacistWorkPlaceWindow',
	objectSrc: '/jscore/Forms/Common/swPharmacistWorkPlaceWindow.js',
	ARMType: null,
	closable: true,
	closeAction: 'hide',
	maximized: true,
	title: 'Рабочее место «фармацевта»',
	iconCls: 'workplace-mp16',
	id: 'swPharmacistWorkPlaceWindow',
	readOnly: false,
	/*getCalendar: function () 
	{
		//return this.calendar;
	},*/
	getGrid: function () {
		return this.DocumentGrid;
	},
	getPeriodToggle: function (mode) {
		switch(mode) {
			case 'day':
				return this.PharmacistToolbar.items.items[6];
				break;
			case 'week':
				return this.PharmacistToolbar.items.items[7];
				break;
			case 'month':
				return this.PharmacistToolbar.items.items[8];
				break;
			case 'range':
				return this.PharmacistToolbar.items.items[9];
				break;
			default:
				return null;
				break;
		}
	},	
	documentLoad: function(mode) {
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
		var params = new Object();
		var filter_form = Ext.getCmp('pwpFilterForm').getForm();
		params.begDate = Ext.util.Format.date(this.dateMenu.getValue1(), 'd.m.Y');
		params.endDate = Ext.util.Format.date(this.dateMenu.getValue2(), 'd.m.Y');
		params.DrugFinance_id = filter_form.findField('DrugFinance_id').getValue();
		params.DocumentUc_DogNum = filter_form.findField('DocumentUc_DogNum').getValue();
		params.DocumentUc_Num = filter_form.findField('DocumentUc_Num').getValue();
		params.DrugDocumentType_id = filter_form.findField('DrugDocumentType_id').getValue();
		params.DrugDocumentStatus_id = filter_form.findField('DrugDocumentStatus_id').getValue();
		params.DocumentUc_Date = Ext.util.Format.date(filter_form.findField('DocumentUc_Date').getValue(), 'd.m.Y');
		params.Contragent_sid = filter_form.findField('Contragent_sid').getValue();
		params.Contragent_tid = filter_form.findField('Contragent_tid').getValue();
		params.DrugMnn_id = filter_form.findField('DrugMnn_id').getValue();
		params.Drug_id = filter_form.findField('Drug_id').getValue();
		this.getGrid().loadStore(params);
	},
	
	documentRefresh:function() {
		this.documentLoad();
		/*
		var params = new Object();
		params.begDate = Ext.util.Format.date(this.dateMenu.getValue1(), 'd.m.Y');
		params.endDate = Ext.util.Format.date(this.dateMenu.getValue2(), 'd.m.Y');		
		this.getGrid().loadStore(params);
		*/
	},
	documentPrint:function() {
		Ext.ux.GridPrinter.print(this.getGrid());
	},
	/*

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
	},*/
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
	/*setActionDisabled: function(action, flag) {
		if (this.gridActions[action]) {
			this.gridActions[action].initialConfig.initialDisabled = flag;
			this.gridActions[action].setDisabled(flag);
		}
	},*/
	documentCollapseDates: function() {
		this.getGrid().getView().collapseAllGroups();
	},
	documentExpandDates: function() {
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
	},
	currentWeek: function () {
		var frm = this;
		var date1 = (Date.parseDate(frm.curDate, 'd.m.Y'));
		var dayOfWeek = (date1.getDay() + 6) % 7;
		date1 = date1.add(Date.DAY, -dayOfWeek).clearTime();
		var date2 = date1.add(Date.DAY, 6).clearTime();
		frm.dateMenu.setValue(Ext.util.Format.date(date1, 'd.m.Y')+' - '+Ext.util.Format.date(date2, 'd.m.Y'));
	},
	currentMonth: function () {
		var frm = this;
		var date1 = (Date.parseDate(frm.curDate, 'd.m.Y')).getFirstDateOfMonth();
		var date2 = date1.getLastDateOfMonth();
		frm.dateMenu.setValue(Ext.util.Format.date(date1, 'd.m.Y')+' - '+Ext.util.Format.date(date2, 'd.m.Y'));		
	},
	getLoadMask: function(MSG) {
		if (MSG) {
			delete(this.loadMask);
		}
		if (!this.loadMask) {
			this.loadMask = new Ext.LoadMask(Ext.get(this.id), { msg: MSG });
		}
		return this.loadMask;
	},
	getCurrentDateTime: function() {
		var frm = this;
		frm.getLoadMask(LOAD_WAIT).show();
		Ext.Ajax.request({
			url: C_LOAD_CURTIME,
			callback: function(opt, success, response) {
				if (success && response.responseText != '') {
					var result  = Ext.util.JSON.decode(response.responseText);
					frm.curDate = result.begDate;
					frm.curTime = result.begTime;
					frm.userName = result.pmUser_Name;
					frm.userName = result.pmUser_Name;
					// Проставляем время и режим
					this.mode = 'day';
					frm.currentDay();
					frm.documentLoad('day');
					frm.getLoadMask().hide();
				}
			}
		});
	},
	countOnGroup: function(start, count) {
		var result = 0;		
		for (i=start; i < start+count; i++) {
			if (this.gridStore.data.items[i].get('DocumentUc_id')>0)
				result++;
		}
		return result;
	},
	/*listeners: {
		beforeshow: function() {			
			if ((!getGlobalOptions().medstafffact) || (getGlobalOptions().medstafffact.length==0)) {
				Ext.Msg.alert(lang['soobschenie'], lang['tekuschiy_login_ne_sootnesen_s_vrachom_dostup_k_interfeysu_vracha_nevozmojen']);
				return false;
			}
		}
	},*/
	searchFieldKeydown: function(inp, e) { //обработчик нажатия enter для полей фильтра
		var form = Ext.getCmp('swPharmacistWorkPlaceWindow');
		if (e.getKey() == Ext.EventObject.ENTER) {
			e.stopEvent();
			form.documentLoad();
		}
	},
	setARMType: function() {
		var form = this;
	
		//устанавливаем заголовок АРМ-а
		
		if (this.ARMType == 'pharmacist')
			form.setTitle('Рабочее место «фармацевта»' + ' (' + getGlobalOptions().OrgFarmacy_Nick + ' / ' + UserName + ')');
		if (this.ARMType == 'storehouse')
			form.setTitle(lang['rabochee_mesto_operatora_rs'] + ' (' + getGlobalOptions().OrgFarmacy_Nick + ' / ' + UserName + ')');
		
		// TODO: Тут надо разобраться и разобрать форму на два разных функционала - или использовать форму реально для двух разных армов
		
		// Создаем свой заголовок, единый для всех армов, на основании данных пришедших с сервера ( из User_model)
		// sw.Promed.MedStaffFactByUser.setMenuTitle(this, this.userMedStaffFact);
		
		
		//фильтруем экшены допустимые для даннотого типа АРМ-а
		for (btnAction in form.BtnActions)
			if ( typeof form.BtnActions[btnAction] == 'object' ) {
				if (this.BtnActions[btnAction].ARMType == form.ARMType || this.BtnActions[btnAction].ARMType == 'all')
					this.BtnActions[btnAction].show();
				else
					this.BtnActions[btnAction].hide();
			}
	},
	openDocument: function() {
		var base_wnd = this;
		var grid = this.DocumentGrid.getView().grid;
		var data = grid.getSelectionModel().getSelected().data;
		
		data.ARMType = this.ARMType;
		
		if (data.DrugDocumentType_id == 1) { //док прихода.расхода
			data.callback = function() { base_wnd.documentRefresh(); }
			if (data['DrugDocumentStatus_id'] == 1 || data['DrugDocumentStatus_id'] == 4)
				data.action = 'edit';
			else
				data.action = 'view';
			getWnd('swDocumentUcEditWindow').show(data);
		}		
		if (data.DrugDocumentType_id == 8) { //заявка
			//TODO привинтить открытие заявки на редактирование, когда та будет допилена
			alert(lang['funktsional_nahoditsya_na_stadii_razrabotki']);
		}
	},
	deleteDocument: function() {
		var base_wnd = this;
		var grid = this.DocumentGrid.getView().grid;
		var id = grid.getSelectionModel().getSelected().data.DocumentUc_id;
		
		sw.swMsg.show({
			icon: Ext.MessageBox.QUESTION,
			msg: lang['vyi_hotite_udalit_dokument'],
			title: lang['podtverjdenie'],
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ('yes' == buttonId) {
					Ext.Ajax.request({
						url: '/?c=Farmacy&m=deleteDocumentUc',
						params: {DocumentUc_id: id},
						failure: function(response, options) {
							Ext.Msg.alert(lang['oshibka'], lang['pri_udalenii_proizoshla_oshibka']);
						},
						success: function(response, action) {							
							if (response.responseText) {
								var answer = Ext.util.JSON.decode(response.responseText);
								if (!answer.success) {
									/*if (answer.Error_Msg){
										Ext.Msg.alert(lang['oshibka'], answer.Error_Msg);
									} else {
										Ext.Msg.alert(lang['oshibka'], lang['udalenie_nevozmojno']);
									}*/
								} else {
									grid.getStore().reload();
								}
							} else {
								Ext.Msg.alert(lang['oshibka'], lang['oshibka_pri_udalenii_otsutstvuet_otvet_servera']);
							}
						}
					});
				} else {
					if (grid.getStore().getCount()>0) {
						grid.getView().focusRow(0);
					}
				}
			}
		});
		
	},
	show: function() {
		sw.Promed.swPharmacistWorkPlaceWindow.superclass.show.apply(this, arguments);		
		
		this.ARMType = arguments[0] && arguments[0].ARMType ? arguments[0].ARMType : 'pharmacist';
		this.setARMType();
		
		loadContragent(this, 'pwpSearch_Contragent_sid', {mode:'sender'}, function(){});
		loadContragent(this, 'pwpSearch_Contragent_tid', {mode:'reciver'}, function(){});

		/*if ((arguments[0]) && (arguments[0].formMode) && (arguments[0].formMode == 'open'))
		{
			// Да просто активация
			// this.formMode = 'open';
			this.documentRefresh();
			// если formMode был = send, то все вертаем назад на сохраненные значения 
		}
		else 
		{
			// Очистка грида 
			this.getGrid().clearStore();
			// При открытии формы сначала получаем текущую дату с сервера, затем получаем список записанных на текущую дату
			this.getCurrentDateTime();
		}*/
		
		// Очистка грида 
		//this.getGrid().clearStore();
		// При открытии формы сначала получаем текущую дату с сервера, затем получаем список записанных на текущую дату
		this.getCurrentDateTime();
	
		/*if ((this.formMode = 'send') && (this.saveParams))
		{
			this.getGrid().clearStore();
			this.curDate = this.saveParams.curDate;
			this.begTime = this.saveParams.begTime;
			this.dateMenu.setValue(Ext.util.Format.date(this.saveParams.selectBegDate, 'd.m.Y')+' - '+Ext.util.Format.date(this.saveParams.selectEndDate, 'd.m.Y'));
			this.Person_id = this.saveParams.Person_id;
			this.Server_id = this.saveParams.Server_id;
			this.PersonEvn_id = this.saveParams.PersonEvn_id;
			this.mode = this.saveParams.mode;
			delete(this.saveParams);
			this.documentLoad(this.mode);
		}
		*/
		//this.formMode = 'open';
		this.TopPanel.show();
		//this.gridActions.create.show();
		//this.setActionDisabled('create',false);
		//this.gridActions.open.show();
		//this.setActionDisabled('open',false);
		
		
		this.findById('pwpDocumentPanel').syncSize();

		// Потом читаем расписание на этот день согласно установленным настройкам 
		//this.dateMenu.setValue(this.curDate);
		//this.dateMenu.setValue2(this.curDate);
		//this.dateTpl.overwrite(this.dateText.getEl(), {period:'Текущая дата'});
		// Переключатель
		this.syncSize();
	},

	initComponent: function() {	
		var base_window = this;
		// Actions
		var Actions =
		[
			{name:'add', text:lang['sozdat'], tooltip: lang['sozdat_novyiy_dokument'], iconCls : 'x-btn-text',  icon : 'img/icons/add16.png', handler: function() { this.SelectCreateTypeWindow.show(); }.createDelegate(this)},
			{name:'edit', text:lang['otkryit'], tooltip: lang['otkryit_novyiy_dokument'], iconCls : 'x-btn-text', icon : 'img/icons/edit16.png', handler: function() { this.openDocument(); }.createDelegate(this)},
			{name:'del', text:lang['udalit'], tooltip: lang['udalit_dokument'], iconCls : 'x-btn-text', icon: 'img/icons/delete16.png', handler: function() { this.deleteDocument(); }.createDelegate(this)},
			{name:'export', text:'Экспорт', tooltip: 'Экспорт документа', iconCls : 'open16', handler: function() {alert('Функционал находится на стадии разработки'); /*TODO: добыть информацию по экспорту и реализовать*/ }.createDelegate(this)},
			{name:'refresh', text:BTN_GRIDREFR, tooltip: BTN_GRIDREFR, iconCls : 'x-btn-text', icon: 'img/icons/refresh16.png', handler: function() {this.documentRefresh()}.createDelegate(this)},			
			{name:'actions', key: 'actions', text:lang['deystviya'], menu: [
				new Ext.Action({name:'collapse_all', text:lang['svernut_vse'], tooltip: lang['svernut_vse'], handler: function() {this.documentCollapseDates()}.createDelegate(this)}),
				new Ext.Action({name:'expand_all', text:lang['razvernut_vse'], tooltip: lang['razvernut_vse'], handler: function() {this.documentExpandDates()}.createDelegate(this)})
			], tooltip: lang['deystviya'], iconCls : 'x-btn-text', icon: 'img/icons/actions16.png', handler: function() {}},
			{name:'print', text:BTN_GRIDPRINT, tooltip: BTN_GRIDPRINT, iconCls : 'x-btn-text', icon: 'img/icons/print16.png', handler: function() {this.documentPrint()}.createDelegate(this)}
		];
		this.gridActions = new Array();
		
		for (i=0; i < Actions.length; i++)
		{
			this.gridActions[Actions[i]['name']] = new Ext.Action(Actions[i]);
		}
		delete(Actions);
		
		// Создание popup - меню и кнопок в ToolBar. Формирование коллекции акшенов
		this.ViewContextMenu = new Ext.menu.Menu();
		this.toolItems = new Ext.util.MixedCollection(true);
		var i = 0;
		for (key in this.gridActions)
		{
			if (key!='remove')
			{
				this.toolItems.add(this.gridActions[key],key);
				if ((i == 1) || (i == 8) || (i == 9)) // || (i == 5)
					this.ViewContextMenu.add('-');
				this.ViewContextMenu.add(this.gridActions[key]);
				i++;
			}
		}
		this.ViewContextMenu.items.items[5].hide();				
		
		this.gridToolbar = new Ext.Toolbar(
		{
			id: 'pwpToolbar',
			items: [
				this.gridActions.add,
				this.gridActions.edit,
				this.gridActions.del,
				this.gridActions['export'],
				{xtype : "tbseparator"},
				this.gridActions.refresh,
				{xtype : "tbseparator"},
				this.gridActions.print,
				{xtype : "tbseparator"},
				this.gridActions.actions,
				{xtype : "tbseparator"},
				{xtype : "tbfill"},
				{xtype : "tbseparator"},
				{
					text: '0 / 0',
					xtype: 'tbtext'
				}
			]
		});		
		
		this.reader = new Ext.data.JsonReader(
		{
			id: 'DocumentUc_id'
		},
		[{
			name: 'DocType'
		}, {
			name: 'DrugDocumentType_id'
		}, {
			name: 'DocumentUc_id'
		}, {
			name: 'DocumentUc_Date'
		}, {
			name: 'DocumentUc_Num'
		}, {
			name: 'DocumentUc_DogNum'
		}, {
			name: 'Contragent_tName'
		}, {
			name: 'Contragent_sName'
		}, {
			name: 'DrugFinance_Name'
		}, {
			name: 'DrugDocumentStatus_id'
		}, {
			name: 'DrugDocumentStatus_Name'
		}		
		/*{
			name: 'TimetableGraf_Date',
			type: 'date',
			dateFormat: 'd.m.Y'
		}, 
		{
			name: 'TimetableGraf_begTime'//,
			//type: 'date',
			//dateFormat: 'H:i'
		},
		{
			name: 'TimetableGraf_factTime',
			type: 'date',
			dateFormat: 'H:i'
		},
		{
			name: 'Person_FIO'
		}, 
		{
			name: 'Person_Age',
			type: 'int'
		}*/		
		]);
        
		this.gridStore = new Ext.data.GroupingStore(
		{
			reader: this.reader,
			autoLoad: false,
			url: '?c=Farmacy&m=loadDocumentListByDay',
			sortInfo: {
				field: 'DocumentUc_id',
				direction: 'ASC'
			},
			groupField: 'DocType',
			listeners:
			{
				load: function(store, record, options)
				{
					callback:
					{
						/*var count = store.getCount();
						var form = Ext.getCmp('swPharmacistWorkPlaceWindow');
						var grid = form.DocumentGrid;
						if (count>0)
						{
							// Если ставится фокус при первом чтении или количество чтений больше 0
							if (!grid.getTopToolbar().hidden)
							{
								grid.getTopToolbar().items.last().el.innerHTML = '0 / '+count;
							}
							if (!form.readOnly)
							{
								if (!form.gridActions.open.initialConfig.initialDisabled)
									form.gridActions.open.setDisabled(false);
								if (!form.gridActions.create.initialConfig.initialDisabled)
									form.gridActions.create.setDisabled(false);
								if (!form.gridActions.add.initialConfig.initialDisabled)
									form.gridActions.add.setDisabled(false);
								if (!form.gridActions.queue.initialConfig.initialDisabled)
									form.gridActions.queue.setDisabled(false);
								if (!form.gridActions.edit.initialConfig.initialDisabled)
									form.gridActions.edit.setDisabled(false);
								if (!form.gridActions.del.initialConfig.initialDisabled)
									form.gridActions.del.setDisabled(false);
								if (!form.gridActions.copy.initialConfig.initialDisabled)
									form.gridActions.copy.setDisabled(false);
								if (!form.gridActions.refresh.initialConfig.initialDisabled)
									form.gridActions.refresh.setDisabled(false);
							}
							form.restorePosition();
							//grid.focus();
							store.each(function(record) 
							{
								//log(record.get('TimetableGraf_factTime'));
								if (record.get('TimetableGraf_factTime')!='')
								{
									record.set('Person_IsEvents', "true");
									record.commit();
								}
							});
							
							//form.documentCollapseDates(); // Беру на себя ответственность - пока это явно лишнее 
						}
						else
						{
							grid.focus();
						}*/
					}
				},
				clear: function()
				{
					/*var form = Ext.getCmp('swPharmacistWorkPlaceWindow');
					form.gridActions.open.setDisabled(true);
					form.gridActions.create.setDisabled(true);
					form.gridActions.add.setDisabled(true);
					form.gridActions.queue.setDisabled(true);
					form.gridActions.edit.setDisabled(true);
					form.gridActions.copy.setDisabled(true);
					form.gridActions.del.setDisabled(true);*/
				},
				beforeload: function()
				{

				}
			}
		});
		this.dateMenu = new Ext.form.DateRangeField(
		{
			width: 150,
			fieldLabel: lang['period'],
			plugins: 
			[
				new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)
			]
		});
		
		this.dateMenu.addListener('keydown',function (inp, e) 
		{
			var form = Ext.getCmp('swPharmacistWorkPlaceWindow');
			if (e.getKey() == Ext.EventObject.ENTER)
			{
				e.stopEvent();
				form.documentLoad('period');
			}
		});
		this.dateMenu.addListener('select',function () 
		{
			// Читаем расписание за период
			var form = Ext.getCmp('swPharmacistWorkPlaceWindow');
			form.documentLoad('period');
		});
		
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
				this.documentLoad('range');
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
				this.documentLoad('range');
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
				this.documentLoad('day');
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
				this.documentLoad('week');
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
				this.documentLoad('month');
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
				this.documentLoad('range');
			}.createDelegate(this)
		});

		this.PharmacistToolbar = new Ext.Toolbar(
		{
			items: 
			[
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
		
		this.SearchPanel = new Ext.Panel({
			frame: true,
			animCollapse: false,
			bodyStyle: 'padding: 0px;',
			height: 234,
			minSize: 0,
			maxSize: 234,
			floatable: false,
			collapsible: true,
			border: true,
			title: lang['poisk'],
			split: true,
			layoutConfig: {
				titleCollapse: true,
				animate: true,
				activeOnTop: false
			},
			listeners: {
				expand: function() {
					var filter_form = Ext.getCmp('pwpFilterForm');
					var top_panel = Ext.getCmp('pwpFilterForm').ownerCt.ownerCt;
					top_panel.setHeight(top_panel.height);
					top_panel.ownerCt.doLayout();
				},
				collapse: function() {
					var filter_form = Ext.getCmp('pwpFilterForm');
					var top_panel = Ext.getCmp('pwpFilterForm').ownerCt.ownerCt;
					top_panel.setHeight(top_panel.height - 209);
					top_panel.ownerCt.doLayout();
				},
				resize: function (p,nW, nH, oW, oH) {
					return;
				}
			},
			items:[{
				xtype: 'form',
				id: 'pwpFilterForm',
				labelAlign: 'right',
				labelWidth: 100,
				height: 218,
				layout: 'column',
				items: [{
					xtype: 'fieldset',
					autoHeight: true,
					width: 950,
					style: 'padding: 3px; margin-bottom:2px; display:block;',
					title: lang['dokument'],
					items: [{
						layout: 'column',						
						labelAlign: 'right',
						labelWidth: 55,
						items: [{
							layout: 'form',
							labelWidth: 80,
							items: [{
								xtype: 'swcustomobjectcombo',
								disabled: true,
								width: 120,
								id: 'pwpSearch_DrugDocumentMotivation',
								comboSubject: 'DrugDocumentMotivation',
								sortField: 'DrugDocumentMotivation_Code',
								fieldLabel: lang['osnovanie'],
								value: '',
								listeners: {'keydown': this.searchFieldKeydown}
							}]
						}, {
							layout: 'form',
							items: [{
								xtype: 'textfieldpmw',
								width: 120,
								id: 'pwpSearch_Num',
								name: 'DocumentUc_Num',
								fieldLabel: lang['nomer'],
								value: '',
								listeners: {'keydown': this.searchFieldKeydown}
							}]
						}, {
							layout: 'form',
							items: [{
								xtype: 'swdatefield',
								disabled: false,
								width: 120,
								format: 'd.m.Y',
								plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
								id: 'pwpSearch_Date',
								name: 'DocumentUc_Date',
								fieldLabel: lang['data'],
								listeners: {'keydown': this.searchFieldKeydown}
							}]
						}, {
							layout: 'form',
							items: [{
								xtype: 'swcustomobjectcombo',
								width: 120,
								id: 'pwpSearch_DrugDocumentStatus',
								comboSubject: 'DrugDocumentStatus',
								sortField: 'DrugDocumentStatus_Code',
								fieldLabel: lang['status'],
								value: '',
								listeners: {'keydown': this.searchFieldKeydown}
							}]
						}, {
							layout: 'form',
							items: [{
								xtype: 'swcustomobjectcombo',
								width: 120,
								listWidth: 300,
								id: 'pwpSearch_DrugDocumentType',
								comboSubject: 'DrugDocumentType',
								sortField: 'DrugDocumentType_Code',
								//TODO: ограничить комбо только теми типами которы отображаются в гриде (на данный момент это док учета - 1 и заявки - 8)
								fieldLabel: lang['vid'],
								value: '',
								listeners: {'keydown': this.searchFieldKeydown}
							}]
						}]
					}]
				}, {
					layout: 'column',						
					labelAlign: 'right',
					labelWidth: 155,
					width: 950,
					style: 'margin-top: 5px; padding-left: 4px;',
					items: [{
						layout: 'form',
						labelWidth: 180,
						items: [{
							xtype: 'textfieldpmw',
							width: 200,
							id: 'pwpSearch_DogNum',
							name: 'DocumentUc_DogNum',
							fieldLabel: lang['№_dogovora'],
							value: '',
							listeners: {'keydown': this.searchFieldKeydown}
						}]
					}, {
						layout: 'form',
						items: [{
							xtype: 'swcontragentcombo',
							disabled: false,
							width: 380,
							id: 'pwpSearch_Contragent_sid',
							name: 'Contragent_sid',
							hiddenName:'Contragent_sid',
							fieldLabel: lang['postavschik'],
							listeners: {'keydown': this.searchFieldKeydown}
						}]
					}]
				}, {
					layout: 'column',						
					labelAlign: 'right',
					labelWidth: 155,
					width: 950,
					style: 'padding-left: 4px;',
					items: [{
						layout: 'form',
						labelWidth: 180,
						items: [{
							xtype: 'swdrugfinancecombo',
							width: 200,
							id: 'pwpSearch_DrugFinance_id',
							name: 'DrugFinance_id',
							fieldLabel: lang['istochnik_finansirovaniya'],
							listeners: {'keydown': this.searchFieldKeydown}
						}]
					}, {
						layout: 'form',
						items: [{
							xtype: 'swcontragentcombo',
							disabled: false,
							width: 380,
							id: 'pwpSearch_Contragent_tid',
							name: 'Contragent_tid',
							hiddenName:'Contragent_tid',
							fieldLabel: lang['poluchatel'],
							listeners: {'keydown': this.searchFieldKeydown}
						}]
					}]
				}, {
					layout: 'column',						
					labelAlign: 'right',
					labelWidth: 180,
					width: 950,
					style: 'padding-left: 4px;',
					items: [{
						layout: 'form',
						labelWidth: 180,
						items: [{
							xtype: 'textfieldpmw',
							disabled: true,
							width: 200,
							id: 'pwpSearch_FirName90',
							fieldLabel: lang['programma_llo'],
							//TODO: комбо
							listeners: {'keydown': this.searchFieldKeydown}
						}]
					}]
				}, {
					xtype: 'fieldset',
					autoHeight: true,
					width: 950,
					style: 'padding: 3px; margin-bottom:2px; display:block;',
					title: lang['medikament'],
					items: [{
						layout: 'column',						
						labelAlign: 'right',
						labelWidth: 80,
						items: [{
							layout: 'form',
							items: [{
								xtype: 'swdrugmnncombo',
								disabled: false,
								width: 300,
								id: 'pwpSearch_DrugMnn_id',
								name: 'DrugMnn_id',
								hiddenName: 'DrugMnn_id',
								fieldLabel: lang['mnn'],
								listeners: {
									'keydown': this.searchFieldKeydown,
									'render': function() {										
										Ext.getCmp('pwpSearch_DrugMnn_id').getStore().proxy.conn.url = "/?c=RlsDrug&m=loadDrugMnnList";
									}.createDelegate(this)
								},
								loadingText: lang['idet_poisk'],
								onTrigger2Click: function() {
									var base_form = Ext.getCmp('pwpFilterForm').getForm();
									if ( base_form.findField('DrugMnn_id').disabled ) {
										return false;
									}
									var drug_mnn_combo = base_form.findField('DrugMnn_id');
									getWnd('swRlsDrugMnnSearchWindow').show({
										searchFull: true,
										onHide: function() {
											drug_mnn_combo.focus(false);
										},
										onSelect: function(drugMnnData) {											
											drug_mnn_combo.getStore().removeAll();
											drug_mnn_combo.getStore().loadData([ drugMnnData ]);											
											drug_mnn_combo.setValue(drugMnnData.DrugMnn_id);
											getWnd('swRlsDrugMnnSearchWindow').hide();
										}
									});
								}.createDelegate(this)
							}]
						}, {
							layout: 'form',
							labelWidth: 155,
							items: [{
								xtype: 'swdrugcombo',
								disabled: false,
								allowBlank: true,
								width: 380,
								listWidth: 400,
								fieldLabel: lang['torg_naimenovanie'],
								id: 'pwpSearch_Drug_id',
								name: 'Drug_id',
								hiddenName: 'Drug_id',
								listeners: {
									'keydown': this.searchFieldKeydown,
									'render': function() {										
										Ext.getCmp('pwpSearch_Drug_id').getStore().proxy.conn.url = "/?c=RlsDrug&m=loadDrugList";
									}
								},
								loadingText: lang['idet_poisk'],
								onTrigger2Click: function() {
									var drug_combo = this;
									getWnd('swRlsDrugTorgSearchWindow').show({
										searchFull: true,
										onHide: function() {
											drug_combo.focus(false);
										},
										onSelect: function(drugTorgData) {
											drug_combo.getStore().removeAll();
											drug_combo.getStore().loadData([{
												Drug_Code: drugTorgData.Drug_Code,
												Drug_id: drugTorgData.Drug_id,
												Drug_Name: drugTorgData.Drug_Name,
												DrugMnn_id: drugTorgData.DrugMnn_id
											}]);
											drug_combo.setValue(drugTorgData.Drug_id);
											getWnd('swRlsDrugTorgSearchWindow').hide();
										}
									});
								}
							}]
						}]
					}]
				}, {
					layout: 'column',						
					labelAlign: 'right',
					labelWidth: 120,
					width: 950,
					style: "margin-top: 5px",
					items: [{
						layout: 'form',
						items: [{
							style: "padding-left: 0px",
							xtype: 'button',
							id: 'pwpBtnSearch',
							text: lang['poisk'],
							iconCls: 'search16',
							handler: function() {
								var form = Ext.getCmp('swPharmacistWorkPlaceWindow');
								form.documentLoad();
							}
						}]
					}, {
						layout: 'form',
						items: [{
							style: "padding-left: 10px",
							xtype: 'button',
							id: 'pwpBtnClear',
							text: lang['sbros'],
							iconCls: 'resetsearch16',
							handler: function() {								
								var filter_form = Ext.getCmp('pwpFilterForm').getForm();
								var form = Ext.getCmp('swPharmacistWorkPlaceWindow');
								filter_form.findField('DrugFinance_id').setValue(null);
								filter_form.findField('DocumentUc_DogNum').setValue(null);
								filter_form.findField('DocumentUc_Num').setValue(null);								
								filter_form.findField('DrugDocumentType_id').setValue(null);
								filter_form.findField('DrugDocumentStatus_id').setValue(null);
								filter_form.findField('DocumentUc_Date').setValue(null);								
								filter_form.findField('Contragent_sid').setValue(null);
								filter_form.findField('Contragent_tid').setValue(null);
								filter_form.findField('DrugMnn_id').setValue(null);
								filter_form.findField('Drug_id').setValue(null);
								form.documentLoad();
							}
						}]
					}]
				}]
			}]
		});
		
		
		this.TopPanel = new Ext.Panel({
			region: 'north',
			frame: false,
			border: false,
			height: 260,
			tbar: this.PharmacistToolbar,
			items: [
				this.SearchPanel
			]
		})

		this.DocumentGrid = new Ext.grid.GridPanel({
			region: 'center',
			layout: 'fit',
			frame: true,
			tbar: this.gridToolbar,
			store: this.gridStore,
			loadMask: true,
			stripeRows: true,
			columns: [
				{header: lang['tip_dokumenta'], dataIndex: 'DocType', hidden: true, hideable: false},
				{header: lang['identifikator_tipa_dokumenta'], dataIndex: 'DrugDocumentType_id', hidden: true, hideable: false},
				{header: lang['identifikator_statusa_dokumenta'], dataIndex: 'DrugDocumentStatus_id', hidden: true, hideable: false},
				{header: 'id', dataIndex: 'DocumentUc_id', hidden: true, hideable: false},
				{header: lang['data_dokumenta'], dataIndex: 'DocumentUc_Date', width: 15, sortable: true},
				{header: lang['nomer_dokumenta'], dataIndex: 'DocumentUc_Num', width: 15, sortable: true},
				{header: lang['osnovanie'], dataIndex: 'Field3', width: 15, sortable: true},
				{header: lang['status_dokumenta'], dataIndex: 'DrugDocumentStatus_Name', width: 15, sortable: true},
				{header: lang['naznachen'], dataIndex: 'Field5', width: 15, sortable: true},
				{header: lang['dogovor'], dataIndex: 'DocumentUc_DogNum', width: 15, sortable: true},
				{header: lang['postavschik'], dataIndex: 'Contragent_sName', width: 15, sortable: true},
				{header: lang['poluchatel'], dataIndex: 'Contragent_tName', width: 15, sortable: true},
				{header: lang['programma_llo'], dataIndex: 'Field9', width: 15, sortable: true},
				{header: lang['istochnik_finansirovaniya'], dataIndex: 'DrugFinance_Name', width: 15, sortable: true},
				{header: lang['primechanie'], dataIndex: 'Field11', width: 15, sortable: true}
			],
            title: lang['jurnal_rabochego_mesta'],
			view: new Ext.grid.GroupingView(
			{
				forceFit: true,
                //enableGrouping:false,
                enableGroupingMenu:false,
				groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length.inlist([2,3,4]) ? "записи" : "записей"]})'
			}),
			loadStore: function(params)
			{
				if (!this.params)
					this.params = null;
				if (params)
				{
					this.params = params;
				}
				this.clearStore();
				this.getStore().load({params: this.params});
			},
			clearStore: function()
			{
				if (this.getEl())
				{
					if (this.getTopToolbar().items.last())
						this.getTopToolbar().items.last().el.innerHTML = '0 / 0';
					this.getStore().removeAll();
				}
			},
			focus: function () 
			{
				if (this.getStore().getCount()>0)
				{
					this.getView().focusRow(0);
					this.getSelectionModel().selectFirstRow();
				}
			},
			hasPersonData: function()
			{
				return this.getStore().fields.containsKey('Person_id') && this.getStore().fields.containsKey('Server_id');
			},
			sm: new Ext.grid.RowSelectionModel(
			{
				singleSelect: true,
				listeners:
				{
					'rowselect': function(sm, rowIdx, record)
					{
						var form = Ext.getCmp('swPharmacistWorkPlaceWindow');
						var count = this.grid.getStore().getCount();
						var rowNum = rowIdx + 1;
						if ((record.get('TimetableGraf_id')==null) || (record.get('TimetableGraf_id')==''))
						{
							count = 0;
							rowNum = 0;
						}
						if (!this.grid.getTopToolbar().hidden)
						{
							this.grid.getTopToolbar().items.last().el.innerHTML = rowNum+' / '+count;
						}
						// Проверка ввода человека
						/*if (!form.readOnly)
						{
							if ((record.get('Person_id')==null) || (record.get('Person_id')==''))
							{
								var TimetableGraf_Date = record.get('TimetableGraf_Date');
								var current_date = Date.parseDate(form.curDate, 'd.m.Y');
								if (!form.gridActions.open.initialConfig.initialDisabled)
									form.gridActions.open.setDisabled(true);
								if (!form.gridActions.create.initialConfig.initialDisabled)
									// блокируем кнопку "Без записи" в предыдущих днях, т.к. запись все равно происходит на текущий день
									form.gridActions.create.setDisabled(current_date > TimetableGraf_Date);
								if (!form.gridActions.add.initialConfig.initialDisabled)
									// запрещаем запись пациента на прошедшую дату
									form.gridActions.add.setDisabled(current_date > TimetableGraf_Date);
								if (!form.gridActions.queue.initialConfig.initialDisabled)
									// запрещаем запись из очереди на прошедшую дату
									form.gridActions.queue.setDisabled(current_date > TimetableGraf_Date);
								if (!form.gridActions.edit.initialConfig.initialDisabled)
									form.gridActions.edit.setDisabled(true);
								if (!form.gridActions.copy.initialConfig.initialDisabled)
									form.gridActions.copy.setDisabled(true);
								if (!form.gridActions.paste.initialConfig.initialDisabled)
									form.gridActions.paste.setDisabled(true);
								if (!form.gridActions.del.initialConfig.initialDisabled)
									form.gridActions.del.setDisabled(true);
							}
							else 
							{
								if (!form.gridActions.open.initialConfig.initialDisabled)
									form.gridActions.open.setDisabled(false);
								if (!form.gridActions.create.initialConfig.initialDisabled)
									form.gridActions.create.setDisabled(false);
								if (!form.gridActions.add.initialConfig.initialDisabled)
									form.gridActions.add.setDisabled(true);
								if (!form.gridActions.queue.initialConfig.initialDisabled)
									form.gridActions.queue.setDisabled(true);
								if (!form.gridActions.edit.initialConfig.initialDisabled)
									form.gridActions.edit.setDisabled(false);
								if (!form.gridActions.copy.initialConfig.initialDisabled)
									form.gridActions.copy.setDisabled(false);
								if (!form.gridActions.paste.initialConfig.initialDisabled)
									form.gridActions.paste.setDisabled(false);
								//form.gridActions.del.setDisabled(false);
								// (!record.get('MedStaffFact_id').inlist(getGlobalOptions().medstafffact)) || 
								
								var TimetableGraf_Date = record.get('TimetableGraf_Date');
								var current_date = Date.parseDate(form.curDate, 'd.m.Y');
								if (!form.gridActions.del.initialConfig.initialDisabled)
									form.gridActions.del.setDisabled( // Disabled where
										(!isAdmin) // this user
										&& (
										(record.get('pmUser_updId') != getGlobalOptions().pmuser_id) // this other autor of record
										|| (current_date > TimetableGraf_Date) 
										|| (current_date.format('d.m.Y') == TimetableGraf_Date.format('d.m.Y') && record.get('Person_IsEvents') == 'true') // in current day opened TAP
										)
									);
							}
						}*/
					},
					'rowdeselect': function(sm, rowIdx, record)
					{
						//
					}
				}
			})
		});
		
		// Добавляем созданное popup-меню к гриду
		this.DocumentGrid.addListener('rowcontextmenu', onMessageContextMenu,this);
		this.DocumentGrid.on('rowcontextmenu', function(grid, rowIndex, event)
		{
			// На правый клик переходим на выделяемую запись
			grid.getSelectionModel().selectRow(rowIndex);
		});
		// Функция вывода меню по клику правой клавиши
		function onMessageContextMenu(grid, rowIndex, e)
		{
			e.stopEvent();
			var coords = e.getXY();
			this.ViewContextMenu.showAt([coords[0], coords[1]]);
		}
		this.DocumentGrid.on('rowdblclick', function(grid, row, col, object) {
			var win = Ext.getCmp('swPharmacistWorkPlaceWindow');
			win.gridActions.edit.execute();			
		});
		
		// Даблклик на редактирование
		/*this.DocumentGrid.on('celldblclick', function(grid, row, col, object) {
			var win = Ext.getCmp('swPharmacistWorkPlaceWindow');
			var rec = grid.getSelectionModel().getSelected();
			if (col == 14 && rec.get('EvnDirection_id') != '') { // столбец с направлением
				*//*getWnd('swEvnDirectionEditWindow').show({
					EvnDirection_id: rec.get('EvnDirection_id'),
					action: 'view',
					formParams: new Object()
				});*//*
			} else {
				var isPerson = (rec.get('Person_id')>0);
				if (isPerson)
				{
					if (!win.gridActions.open.isDisabled())
					{
						win.gridActions.open.execute();
					}
				}
				else 
				{
					if (!win.gridActions.add.isDisabled())
					{
						win.gridActions.add.execute();
					}
				}
			}
		});*/
		// Клин на иконку направления
		/*this.DocumentGrid.on('cellclick', function(grid, row, col, object)
		{
			var win = Ext.getCmp('swPharmacistWorkPlaceWindow');
			var rec = grid.getSelectionModel().getSelected();
			if (col == 14 && rec.get('EvnDirection_id') != '' && rec.get('EvnDirection_id') != undefined ) { // столбец с направлением
				getWnd('swEvnDirectionEditWindow').show({
					EvnDirection_id: rec.get('EvnDirection_id'),
					action: 'view',
					formParams: new Object()
				});
			}
		});*/
		
		
		// Добавляем события на keydown
		this.DocumentGrid.on('keydown', function(e)
		{
			var win = Ext.getCmp('swPharmacistWorkPlaceWindow');
			var grid = win.getGrid();
			if (e.getKey().inlist([e.INSERT, e.F4, e.F5, e.ENTER, e.DELETE, e.END, e.HOME, e.PAGE_DOWN, e.PAGE_UP, e.TAB])
				|| (grid.hasPersonData() && e.getKey().inlist([e.F6, e.F10, e.F11, e.F12]))
				|| (e.getKey().inlist([e.C, e.V]) && (e.ctrlKey)))
			{
				e.stopEvent();
				if ( e.browserEvent.stopPropagation )
					e.browserEvent.stopPropagation();
				else
					e.browserEvent.cancelBubble = true;

				if ( e.browserEvent.preventDefault )
					e.browserEvent.preventDefault();
				else
					e.browserEvent.returnValue = false;

				e.returnValue = false;

				if (Ext.isIE)
				{
					e.browserEvent.keyCode = 0;
					e.browserEvent.which = 0;
				}
			}
			var countRecords = this.getStore().getCount();

			// Собираем информацию о человеке в случае, если в гриде есть поля по человеку
			var isPerson = false;
			if (grid.hasPersonData())
			{
				var selected_record = grid.getSelectionModel().getSelected();
				var params = new Object();
				params.Person_id = selected_record.get('Person_id');
				params.Server_id = selected_record.get('Server_id');
				isPerson = (params.Person_id>0);
				if ( selected_record.get('Person_BirthDay') )
					params.Person_BirthDay = selected_record.get('Person_BirthDay');
				else
					params.Person_BirthDay = selected_record.get('Person_Birthday');
				if ( selected_record.get('Person_Surname') )
					params.Person_Surname = selected_record.get('Person_Surname');
				else
					params.Person_Surname = selected_record.get('Person_SurName');
				if ( selected_record.get('Person_Firname') )
					params.Person_Firname = selected_record.get('Person_Firname');
				else
					params.Person_Firname = selected_record.get('Person_FirName');
				if ( selected_record.get('Person_Secname') )
					params.Person_Secname = selected_record.get('Person_Secname');
				else
					params.Person_Secname = selected_record.get('Person_SecName');
				params.onHide = function()
				{
					var index = grid.getStore().findBy(function(rec) { return rec.get('TimetableGraf_id') == selected_record.data['TimetableGraf_id']; });
					grid.focus();
					grid.getView().focusRow(index);
					grid.getSelectionModel().selectRow(index);
				}
			}

			switch (e.getKey())
			{
				case e.ENTER:
					if (isPerson)
					{
						if (!win.gridActions.open.isDisabled())
						{
							win.gridActions.open.execute();
						}
					}
					else 
					{
						if (!win.gridActions.add.isDisabled())
						{
							win.gridActions.add.execute();
						}
					}
					break;
				case e.F4:
					if (!win.gridActions.edit.isDisabled())
					{
						win.gridActions.edit.execute();
					}
					break;
				case e.F5:
					if (!win.gridActions.refresh.isDisabled())
					{
						win.gridActions.refresh.execute();
					}
					break;
				case e.INSERT:
					if (e.ctrlKey)
					{
						if (!win.gridActions.create.isDisabled())
						{
							win.gridActions.create.execute();
						}
					}
					else 
					{
						if (!win.gridActions.add.isDisabled())
						{
							win.gridActions.add.execute();
						}
					}
					break;
				case e.DELETE:
					if (!win.gridActions.del.isDisabled())
					{
						win.gridActions.del.execute();
					}
					break;
				case e.C:
					if (e.ctrlKey)
					{
						/*
						if (!win.gridActions.copy.isDisabled())
						{
							win.gridActions.copy.execute(); // просто бред ...
						}
						*/
					}
					break;
				case e.V:
					if (e.ctrlKey)
					{
						/*
						if (!win.gridActions.paste.isDisabled())
						{
							win.gridActions.paste.execute();
						}
						*/
					}
					break;

				case e.END:
					GridEnd(this);
					break;
				case e.HOME:
					GridHome(this);
					break;
				case e.PAGE_DOWN:
					GridPageDown(this);
					break;
					case e.PAGE_UP:
					GridPageUp(this);
					break;

				case e.TAB:
					if (e.shiftKey)
					{
						var o = win.findById('pwpBtnClear');
					}
					else
					{
						//var o = win.findById('pwpSearch_FIO');
						var o = win.findById('pwpSearch_SurName');
					}
					if (o)
					{
						o.focus(true, 100);
					}
					break;
				case e.F6: // Прикрепление и объединение
					if (grid.hasPersonData() && isPerson) 
					{
						if (!e.altKey && !e.ctrlKey && !e.shiftKey) 
						{ // прикрепление
							ShowWindow('swPersonCardHistoryWindow', params);
						}
						else if (e.altKey)
						{ 
							// TO-DO: сделать процедуру объединения человека, когда в этом возникнет нужда
						}
						return false;
					}
					break;
				case e.F10: // Редактирование
					if (grid.hasPersonData() && !e.altKey && !e.ctrlKey && !e.shiftKey && isPerson)
					{
						ShowWindow('swPersonEditWindow', params);
						return false;
					}
					break;
				case e.F11: // История лечения
					if (grid.hasPersonData() && !e.altKey && !e.ctrlKey && !e.shiftKey && isPerson)
					{
						ShowWindow('swPersonCureHistoryWindow', params);
						return false;
					}
					/*if (grid.hasPersonData() && !e.altKey && e.ctrlKey && !e.shiftKey && isPerson)
					{
						ShowWindow('swPersonEmkWindow', params);
						return false;
					}*/
				break;
				case e.F12: // Льготы и диспансеризация
					if (grid.hasPersonData() && isPerson)
					{
						if (e.ctrlKey)
						{ // Диспансеризация
							ShowWindow('swPersonDispHistoryWindow', params);
						}
						else if (!e.altKey && !e.shiftKey && isPerson)
						{ // Льготы
							ShowWindow('swPersonPrivilegeViewWindow', params);
						}
						return false;
					}
				break;
			}
			return false;
		});

		// метод получения данных: "ЛПУ прикрепления", "Тип прикрепления:", "Тип участка:", "Участок:", на котором врач АРМа является врачом на участке
		this.getAttachDataShowWindow = function(wnd) {
			var global_options = getGlobalOptions();
			Ext.Ajax.request(
			{
				url: '/?c=LpuRegion&m=getMedPersLpuRegionList',
				callback: function(options, success, response) 
				{
					if (success)
					{
						var response_obj = Ext.util.JSON.decode(response.responseText);
						if (response_obj[0] && response_obj[0].LpuRegion_id)
						{
							getWnd(wnd).show({
								LpuAttachType_id: response_obj[0].LpuAttachType_id, 
								LpuRegionType_id: response_obj[0].LpuRegionType_id, 
								LpuRegion_id: response_obj[0].LpuRegion_id
							});
						}
						else
						{
							getWnd(wnd).show();
						}
					}
				},
				params: {MedPersonal_id: global_options.medpersonal_id, Lpu_id: global_options.lpu_id}
			});
		};
		var form = this;
		// Формирование списка всех акшенов 
		var configActions = {
			action_RecipeAction: {
				ARMType: 'pharmacist',
				tooltip: lang['realizatsiya_ls_po_retseptam'],
				text: lang['retseptyi'],
				iconCls : 'receipt-new32',
				disabled: false, 
				hidden: false,
				handler: function() {
					//alert('вызов формы: Форма потоковой обработки рецептов (форма создана)');
					getWnd('swEvnRPStreamInputWindow').show();
				}
			},
			action_DelayJournalAction: {
				ARMType: 'all',
				tooltip: lang['retseptyi_na_otsrochennom_obslujivanii'],
				text: lang['jurnal_otsrochki'],
				iconCls : 'receipt-incorrect16',
				disabled: false, 
				hidden: false,
				handler: function() {
					//alert('вызов формы: Журнал движения рецептов, с автозаполнением поля статус рецепта = ‘отложен’');
					getWnd('swEvnReceptTrafficBookViewWindow').show({
						filters: {
							ReceptDelayType_id: 2
						}
					});
				}
			},
			action_MedOstatAction: {
				ARMType: 'all',
				tooltip: lang['ostatki_poisk_medikamentov'],
				text: lang['ostatki_medikamentov'],
				iconCls : 'rls-torg32',
				disabled: false, 
				hidden: false,
				handler: function() {
					//alert('Вызов формы: (или доработка формы Остатки медикаментов, или новая форма с возможностями получения остатков как по аптеке в целом, так и по конкретному ЛС, в т.ч. просмотр остатков по системе)');
					getWnd('swMedOstatSearchWindow').show();
				}
			},
			action_DefecturaAction: {
				ARMType: 'all',
				tooltip: 'Журнал «Дефектура»',
				text: lang['defektura'],
				iconCls : 'mp-timetable32',
				disabled: false, 
				hidden: false,
				handler: function() {
					//TODO: вызов формы: Журнал дефектур
					//getWnd('swTTGDocumentEditWindow').show();
				}
			},
			action_MedOrderAction: {
				ARMType: 'all',
				tooltip: lang['sozdanie_zayavki_na_postavku_medikamentov'],
				text: lang['zakaz_medikamentov'],
				iconCls : 'document-template32',
				disabled: false, 
				hidden: false,
				handler: function() {
					//TODO: Вызов формы: вызов формы «Заявка: добавление» с передачей в качестве параметров значений полей: основание – поставка на ПО, Получа0тель – текущая аптека, МОЛ – текущий пользователь, Поставщик – РС
					getWnd('swDokDemandEditWindow').show({
						callback: function(){base_window.documentRefresh();},						
						action: 'add',
						filters: {
							Contragent_tid: getGlobalOptions().Contragent_id
						}
					});
				}
			},
			action_MedAcceptAction: {
				ARMType: 'all',
				tooltip: lang['prihod_ls'],
				text: lang['priem_ls'],
				iconCls : 'dlo32',
				disabled: false, 
				hidden: false,
				handler: function() {
					//TODO вызов формы: «Документы учета медикаментов: Добавление» с передачей в качестве параметров значений полей: основание – поставка на ПО, Получатель – текущая аптека, МОЛ – текущий пользователь
					getWnd('swDocumentUcEditWindow').show({
						callback: function(){base_window.documentRefresh();},
						action: 'add',
						filters: {
							ReceptDelayType_id: 2
						}
					});
				}
			},
			action_ContragentAction: {
				ARMType: 'all',
				tooltip: lang['spravochnik_kontragentyi'],
				text: lang['kontragentyi'],
				iconCls : 'org32',
				disabled: false, 
				hidden: false,
				handler: function() {
					//alert('вызов формы «Контрагенты» (возм.модификация потребуется);');
					getWnd('swContragentViewWindow').show();
				}
			},
			action_DokOstAction: {
				ARMType: 'all',
				tooltip: lang['dokumentyi_vvoda_ostatkov'],
				text: lang['vvod_ostatkov'],
				iconCls : 'report32',
				disabled: false, 
				hidden: false,
				handler: function() {
					//alert('вызов формы «Документы ввода остатков»');
					getWnd('swDokOstViewWindow').show({
						ARMType: base_window.ARMType
					});
				}
			},
			action_DokSpisAction: {
				ARMType: 'all',
				tooltip: lang['aktyi_spisaniya_medikamentov'],
				text: lang['spisanie_medikamentov'],
				iconCls : 'mp-drugrequest32',
				disabled: false, 
				hidden: false,
				handler: function() {
					//alert('вызов формы «Акты списания медикаментов»');
					getWnd('swDokSpisViewWindow').show();
				}
			},
			action_DokInvAction: {
				ARMType: 'all',
				tooltip: lang['inventarizatsionnyie_vedomosti'],
				text: lang['inventarizatsiya'],
				iconCls : 'document32',
				disabled: false, 
				hidden: false,
				handler: function() {
					//alert('вызов формы «Инвентаризационные ведомости»');
					getWnd('swDokInvViewWindow').show();
				}
			},
			action_DokNakAction: {
				ARMType: 'pharmacist',
				tooltip: lang['prihodnyie_nakladnyie'],
				text: lang['prihodnyie_nakladnyie'],
				iconCls : 'pl-stream32',
				disabled: false, 
				hidden: false,
				handler: function() {					
					getWnd('swDokNakViewWindow').show();
				}
			}, 
			action_JourNotice: {
				handler: function() {
					getWnd('swMessagesViewWindow').show();
				}.createDelegate(this),
				iconCls: 'notice32',
				nn: 'action_JourNotice',
				text: lang['jurnal_uvedomleniy'],
				tooltip: lang['jurnal_uvedomleniy']
			}
		}
		// Копируем все действия для создания панели кнопок
		form.PanelActions = {};
		for(var key in configActions)
		{
			// width:'265',minWidth:'265', style: {width: '100%'}, 
			var iconCls = configActions[key].iconCls.replace(/16/g, '32');
			var z = Ext.applyIf({cls: 'x-btn-large', iconCls: iconCls, text: ''}, configActions[key]);
			this.PanelActions[key] = new Ext.Action(z);
		}
		// Создание кнопок для панели
		form.BtnActions = new Array();
		var i = 0;
		for(var key in form.PanelActions) {
			form.BtnActions.push(Ext.apply(new Ext.Button(form.PanelActions[key]), {key: key}));
			i++;
		}
		this.leftMenu = new Ext.Panel({			
			region: 'west',			
			border: false,
			layout:'form',
			layoutConfig: {
				titleCollapse: true,
				animate: true,
				activeOnTop: false
			},
			items: form.BtnActions
		});
		this.leftPanel = {
			animCollapse: false,
			bodyStyle: 'padding: 5px',
			width: 60,
			minSize: 60,
			maxSize: 120,
			id: 'pwpLeftPanel',
			region: 'west',
			floatable: false,
			collapsible: true,
			layoutConfig: {
				titleCollapse: true,
				animate: true,
				activeOnTop: false
			},
			listeners: {
				collapse: function() {
					return;
				},
				resize: function (p,nW, nH, oW, oH) {
					return;
				}
			},
			border: true,
			title: ' ',
			split: true,
			items: [this.leftMenu]
		};
		
		this.SelectCreateTypeWindow = new Ext.Window({
			id: 'pwp_SelectCreateTypeWindow',
			closable: false,
			width : 500,
			height : 120,
			modal: true,
			resizable: false,
			autoHeight: false,
			closeAction :'hide',
			border : false,
			plain : false,
			title: lang['vyibor_tipa_dokumenta'],
			items : [new Ext.form.FormPanel({
				height : 60,
				layout : 'form',
				border : false,
				frame : true,
				style : 'padding: 10px',
				labelWidth : 120,
				items : [{
					id: 'createtypecombo',
					xtype:'combo',
					store: new Ext.data.SimpleStore({
						id: 0,
						fields: [
							'code',
							'name'
						],
						data: [
							['create_demand', lang['zayavka']],
							//['create_recipe', 'Рецепт'],
							['create_get', lang['prihodnyiy_dokument']],
							['create_lose', lang['rashodnyiy_dokument']]
						]
					}),
					displayField: 'name',
					valueField: 'code',
					editable: false,
					allowBlank: false,
					mode: 'local',
					forceSelection: true,
					triggerAction: 'all',
					fieldLabel: lang['tip_dokumenta'],
					width:  300,
					value: 'create_demand',
					selectOnFocus: true
				}]
			})],
			buttons : [{
				text : lang['vyibrat'],
				iconCls : 'ok16',
				handler : function(button, event) {
					var create_type = Ext.getCmp('createtypecombo').getValue();
					var base_wnd = Ext.getCmp('swPharmacistWorkPlaceWindow');
					switch(create_type) {
						case 'create_demand':
							//TODO: вызов формы «Заявка: добавление» передачей в качестве параметров значений полей: Получатель – текущая аптека, МОЛ – текущий пользователь.
							getWnd('swDokDemandEditWindow').show({
								callback: function() {base_wnd.documentRefresh();},
								action: 'add',
								filters: {
									Contragent_tid: getGlobalOptions().Contragent_id
								}
							});
							break;
						case 'create_recipe':
							//TODO: вызвать форму «Обработка рецептов»
							break;
						case 'create_get':
							//TODO: вызвать форму «Документ учета: добавление» с автозаполнением поля Получатель = текущий участник системы, МОЛ – текущий пользователь.
							getWnd('swDocumentUcEditWindow').show({
								callback: function() {base_wnd.documentRefresh();},
								action: 'add',
								ARMType: base_wnd.ARMType,
								filters: {
									Contragent_tid: getGlobalOptions().Contragent_id
								}
							});
							break;
						case 'create_lose':						
							//TODO: вызвать форму вызвать форму «Документ учета: добавление» с автозаполнением поля Поставщик = текущий участник системы, МОЛ – текущий пользователь.
							getWnd('swDocumentUcEditWindow').show({
								callback: function() {base_wnd.documentRefresh();},
								action: 'add',
								ARMType: base_wnd.ARMType,
								filters: {
									Contragent_sid: getGlobalOptions().Contragent_id
								}
							});
							break;
					};
					Ext.getCmp('pwp_SelectCreateTypeWindow').hide();
				}.createDelegate(this)
			}, {
				text: '-'
			}, {
				handler: function() {
					this.ownerCt.hide();
				},
				iconCls: 'close16',
				text: BTN_FRMCLOSE
			}],
			buttonAlign : "right"
		});
		
		Ext.apply(this, {
			layout: 'border',
			items: [
				this.TopPanel,
				this.leftPanel, {
					layout: 'border',
					region: 'center',
					id: 'pwpDocumentPanel',
					items: [
						this.DocumentGrid
					]
				}
			],
			buttons: [{
				text: '-'
			}, 
			HelpButton(this, TABINDEX_MPSCHED + 98),
			{
				iconCls: 'cancel16',
				text: BTN_FRMCLOSE,
				handler: function() { this.hide(); }.createDelegate(this)
			}]
		});
		sw.Promed.swPharmacistWorkPlaceWindow.superclass.initComponent.apply(this, arguments);
	}
});
