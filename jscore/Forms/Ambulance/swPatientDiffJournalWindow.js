/**
* swPatientDiffJournalWindow - Журнал расхождения пациентов в учетных документах
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Ambulance
* @access       public
* @copyright    Copyright (c) 2017 Swan Ltd.
*/

sw.Promed.swPatientDiffJournalWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'PatientDiffJournalWindow',
	maximized: true,	
	border: false,
	buttonAlign: 'right',
	closable: true,
	closeAction: 'hide',
	modal: true,
	plain: false,
	resizable: false,
	title: 'Журнал расхождения пациентов в учетных документах',
	userMedStaffFact: null,
	changePerson: function() {
		var win = this;
		this.grid.getMultiSelections().forEach(function (el){
			win.setAnotherPersonForDocument({
				CmpCallCard_id: el.get('CmpCallCard_id'),
				Person_id: el.get('Person_id'),
				PersonEvn_id: el.get('PersonEvn_id'),
				Server_id: el.get('Server_id')
			});
		});
	},
	setAnotherPersonForDocument: function(params) {
		var win = this;
		var grid = this.grid.getGrid();

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Переоформление документа на другого человека..." });
		loadMask.show();

		Ext.Ajax.request({
			callback: function(options, success, response) {
				loadMask.hide();
				if ( success ) {
					var response_obj = Ext.util.JSON.decode(response.responseText);

					if ( response_obj.success == false ) {
						sw.swMsg.alert('Ошибка', response_obj.Error_Msg ? response_obj.Error_Msg : 'Ошибка при переоформлении документа на другого человека');
					}
					else if ( response_obj.Alert_Msg ) {
						sw.swMsg.show({
							buttons: Ext.Msg.YESNO,
							fn: function ( buttonId ) {
								if ( buttonId == 'yes' ) {
									switch ( response_obj.Alert_Code ) {
										case 1:
											params.allowEvnStickTransfer = 2;
										case 2:
											params.ignoreAgeFioCheck = 2;
										break;
									}

									win.setAnotherPersonForDocument(params);
								}
							},
							msg: response_obj.Alert_Msg,
							title: 'Вопрос'
						});
					}
					else {
						var index = grid.getStore().findBy(function(rec) {
							return rec.get('CmpCallCard_id') == params.CmpCallCard_id;
						});
						grid.getStore().remove(grid.getStore().getAt(index));

                        /*var info_msg = 'Документ успешно переоформлен на другого человека';
                        if (response_obj.Info_Msg) {
                            info_msg += '<br>' + response_obj.Info_Msg;
                        }
						sw.swMsg.alert('Сообщение', info_msg, function() {
							grid.getView().focusRow(0);
							grid.getSelectionModel().selectFirstRow();
						});*/
					}
				}
				else {
					sw.swMsg.alert('Ошибка', 'При переоформлении документа на другого человека произошли ошибки');
				}
			},
			params: params,
			url: C_CHANGEPERSONFORDOC
		});
	},
	openCmpCallCardEditWindow: function(action) {
		if ( !action || !action.toString().inlist([ 'add', 'edit', 'view']) ) {
			return false;
		}
		
		var wnd = 'swCmpCallCardNewShortEditWindow';

		if ( getWnd(wnd).isVisible() ) {
			sw.swMsg.alert('Сообщение', 'Окно редактирования карты вызова уже открыто');
			return false;
		}

		var formParams = new Object();
		var grid = this.grid;
		var params = new Object();
		var parentObject = this;
		params.action = action;
		
		if ( !grid.getGrid().getSelectionModel().getSelected() ) {
			return false;
		}

		var selected_record = grid.getGrid().getSelectionModel().getSelected();

		if ( !selected_record.get('CmpCallCard_id') ) {
			return false;
		}

		formParams.CmpCallCard_id = selected_record.get('CmpCallCard_id');
		if (selected_record) {
			formParams.CmpCallCard_id = selected_record.get('CmpCallCard_id');
		}
		formParams.ARMType = this.ARMType;
		params.formParams = formParams;
		getWnd(wnd).show(params);
	},
	openCmpCloseCard110: function() {
		var record = this.grid.getGrid().getSelectionModel().getSelected();
		
		if (!record || !record.get('CmpCallCard_id')) {
			return false;
		}
		
		if ( getWnd('swCmpCallCardNewCloseCardWindow').isVisible() ) {
			sw.swMsg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_kartyi_vyizova_uje_otkryito']);
			return false;
		}
		else{
			var params = {
				action: 'view',
				searchWindow: true,
				formParams: {
					ARMType: 'smpadmin',
					CmpCallCard_id: record.get('CmpCallCard_id')
				}
			};
			 getWnd('swCmpCallCardNewCloseCardWindow').show(params);
		}
	},
	openForm: function(action)
	{
		var record = this.grid.getGrid().getSelectionModel().getSelected();
		if (!record)
		{
			Ext.Msg.alert(lang['oshibka'], lang['oshibka_vyibora_zapisi']);
			return false;
		}
		var id = record.get('EvnPS_id');
		var Person_id = record.get('Person_id');
		var PersonEvn_id = record.get('PersonEvn_id');
		var Server_id = record.get('Server_id');
		var open_form = 'swEvnPSEditWindow';
		var params = {action: action, Person_id: Person_id, PersonEvn_id: PersonEvn_id, Server_id: Server_id, EvnPS_id: id};
		getWnd(open_form).show(params);
	},
	doSearch: function() {
		var params = {};

		if (!this.lpu_id.hidden) {
			params.lpu_id = this.lpu_id.getValue();
		}
		params.begDate = Ext.util.Format.date(this.dateMenu.getValue1(), 'd.m.Y');
		params.endDate = Ext.util.Format.date(this.dateMenu.getValue2(), 'd.m.Y');
		this.grid.removeAll({clearAll:true});
		this.grid.loadData({globalFilters: params});
	},
	show: function()
	{
		sw.Promed.swPatientDiffJournalWindow.superclass.show.apply(this, arguments);
		this.userMedStaffFact = null;
		/*if ((!arguments[0]) || (!arguments[0].userMedStaffFact)){
			this.hide();
			Ext.Msg.alert('Ошибка открытия формы', 'Ошибка открытия формы "'+this.title+'".<br/>Не указаны параметры АРМа врача.');
		} else {
			this.userMedStaffFact = arguments[0].userMedStaffFact;
		}*/
		this.center();
		var win = this;
		this.getCurrentDateTime();

		if(!this.lpu_id.hidden){
			this.lpu_id.setFieldValue('Lpu_id', arguments[0].Lpu_id ? arguments[0].Lpu_id : sw.Promed.MedStaffFactByUser.current.Lpu_id)
		}

	},
	stepDay: function(day)
	{
		var frm = this;
		var date1 = (frm.dateMenu.getValue1() || Date.parseDate(frm.curDate, 'd.m.Y')).add(Date.DAY, day).clearTime();
		var date2 = (frm.dateMenu.getValue2() || Date.parseDate(frm.curDate, 'd.m.Y')).add(Date.DAY, day).clearTime();
		frm.dateMenu.setValue(Ext.util.Format.date(date1, 'd.m.Y')+' - '+Ext.util.Format.date(date2, 'd.m.Y'));
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
	},
	currentWeek: function ()
	{
		var frm = this;
		var date1 = (Date.parseDate(frm.curDate, 'd.m.Y'));
		var dayOfWeek = (date1.getDay() + 6) % 7;
		date1 = date1.add(Date.DAY, -dayOfWeek).clearTime();
		var date2 = date1.add(Date.DAY, 6).clearTime();
		frm.dateMenu.setValue(Ext.util.Format.date(date1, 'd.m.Y')+' - '+Ext.util.Format.date(date2, 'd.m.Y'));
	},
	currentMonth: function ()
	{
		var frm = this;
		var date2 = Date.parseDate(frm.curDate, 'd.m.Y');
		var date1 = date2.add(Date.MONTH, -1).clearTime();
		frm.dateMenu.setValue(Ext.util.Format.date(date1, 'd.m.Y')+' - '+Ext.util.Format.date(date2, 'd.m.Y'));
	},
	getCurrentDateTime: function() {
		if (!getGlobalOptions().date) {
			frm.getLoadMask(LOAD_WAIT).show();
			Ext.Ajax.request({
				url: C_LOAD_CURTIME,
				callback: function(opt, success, response) {
					if (success && response.responseText != '') {
						var result  = Ext.util.JSON.decode(response.responseText);
						this.curDate = result.begDate;
						this.mode = 'month';
						this.currentMonth();
						this.doSearch('month');

						this.getLoadMask().hide();
					}
				}.createDelegate(this)
			});
		} else {
			this.curDate = getGlobalOptions().date;
			// Проставляем время и режим
			this.DoctorToolbar.items.items[11].toggle(true);
			this.mode = 'month';
			this.currentMonth();
			this.doSearch('month');
		}
	},
	initComponent: function()
	{
		var win = this;
		
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
			if (e.getKey() == Ext.EventObject.ENTER)
			{
				e.stopEvent();
				win.doSearch('period');
			}
		});
		this.dateMenu.addListener('select',function () 
		{
			// Читаем расписание за период
			win.doSearch('period');
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

		this.lpu_id = new sw.Promed.swlpuwithopersmpcombo({
			name: 'lpuCombo',
			allowBlank: true,
			listeners: {
				select: function () {
					this.doSearch()
				}.createDelegate(this)
			},
			hidden: !isUserGroup('smpAdminRegion')
		});

		this.DoctorToolbar = new Ext.Toolbar({
			items:
				[
					this.formActions.prev,
					{
						xtype : "tbseparator"
					},
					this.dateMenu,
					{
						xtype : "tbseparator"
					},
					this.formActions.next,
					{
						xtype : "tbseparator"
					},
					{
						xtype: 'label',
						style: 'margin-left: 7px; margin-right: 3px',
						text: 'МО:',
						hidden: !isUserGroup('smpAdminRegion')
					},
					this.lpu_id,
					{
						xtype: 'tbfill'
					},
					this.formActions.day,
					this.formActions.week,
					this.formActions.month,
					this.formActions.range
				]
		});
		
		this.FilterPanel = new Ext.Panel({
			bodyStyle: 'border-bottom: none;',
			border: true,
			autoHeight: true,
			region: 'north',
			layout: 'column',
			tbar: this.DoctorToolbar,
			id: 'OrgLpuFilterPanel'
		});

		this.grid = new sw.Promed.ViewFrame(
		{
			id: 'PDJW_JournalGrid',
			object: 'CmpCallCard',
			selectionModel: 'multiselect2',
			dataUrl: '/?c=CmpCallCard&m=getPatientDiffList',
			layout: 'fit',
			region: 'center',
			paging: false,
			root: '',
			totalProperty: 'totalCount',
			toolbar: true,
			autoLoadData: false,
			useEmptyRecord: false,
			noSelectFirstRowOnFocus: true,
			stringfields:
			[
				{name: 'CmpCallCard_id', type: 'int', header: 'ID', key: true},
				{name: 'CmpCloseCard_id', type: 'int', hidden: true},
				{name: 'Person_id', type: 'int', hidden: true},
				{name: 'Server_id', type: 'int', hidden: true},
				{name: 'PersonEvn_id', type: 'int', hidden: true},
				{name: 'CmpCallCard_prmDT', type: 'datetime', header: 'Дата/время вызова', width: 120},
				{name: 'CmpCallCard_Ngod', type: 'string', width: 120, header: '№ вызова (за год)'},
				{name: 'CmpCallCard_Dspp', type: 'string', width: 150, header: 'Диспетчер вызовов'},
				{name: 'Person_Fio_v', autoexpand: true, type: 'string', width: 250, header: 'Пациент (при вызове)'},
				{name: 'Lpu_Nick', type: 'string', width: 150, header: 'МО передачи'},
				{name: 'Person_Fio', type: 'string', width: 250, header: 'Пациент (МО передачи)'}
			],
			actions:
			[
				{name:'action_add', text: 'Сменить пациента', icon: '', disabled: false, handler: this.changePerson.createDelegate(this)},
				{name:'action_edit', text: 'Талон вызова', icon: '', disabled: false, handler: this.openCmpCallCardEditWindow.createDelegate(this, ['view'])},
				{name:'action_view', text: 'Карта 110у', icon: '', disabled: false, handler: this.openCmpCloseCard110.createDelegate(this, ['view'])},
				{name:'action_delete', hidden: true, disabled: true},
				{name:'action_refresh', hidden: true, disabled: true},
				{name:'action_print', hidden: true, disabled: true}
			]
		});

		Ext.apply(this,
		{
			region: 'center',
			layout: 'border',
			items: [
				win.FilterPanel, {
					border: false,
					region: 'center',
					layout: 'border',
					items: [{
						border: false,
						region: 'center',
						layout: 'fit',
						items: [win.grid]
					}]
				}
			],
			buttons: [{
				text: '-'
			},
			{
				text: BTN_FRMHELP,
				iconCls: 'help16',
				id: 'PDJW_HelpButton',
				handler: function(button, event)
				{
					ShowHelp(this.title);
				}.createDelegate(this)
			},
			{
				iconCls: 'cancel16',
				text: BTN_FRMCLOSE,
				tabIndex: 50050,
				handler: function() {
					this.hide();
				}.createDelegate(this)
			}],
			enableKeyEvents: true,
			keys:
			[{
				alt: true,
				fn: function(inp, e)
				{
					if (e.getKey() == Ext.EventObject.ESC)
					{
						Ext.getCmp('PatientDiffJournalWindow').hide();
						return false;
					}
				},
				key: [ Ext.EventObject.ESC ],
				scope: this,
				stopEvent: false
			}]
		});
		sw.Promed.swPatientDiffJournalWindow.superclass.initComponent.apply(this, arguments);
	}
});