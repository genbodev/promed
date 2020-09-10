/**
* Базовый АРМ
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @autor		
* @copyright    Copyright (c) 2011 Swan Ltd.
* @version      март.2012
*/
sw.Promed.swWorkPlaceWindow = Ext.extend(sw.Promed.BaseForm,
{
	title: lang['rabochee_mesto'],
	modal: false, // FALSE для отключения обновления при открытии других модальных окон
	shim: false,
	maximized: true,
	plain: true,
	onSelect: Ext.emptyFn,
	layout: 'fit',
	buttonAlign: "right",
	closeAction: 'hide',
	id: 'swWorkPlaceWindow',
	gridPanelAutoLoad: true,
	showToolbar: true,
	showLeftMenu: true,
/*
	currentViewFrameIndex: null,
	setCurrentViewFrame: function(options) {
		if ( typeof this.GridPanelList != 'object' || !this.GridPanelList[index] || typeof options != 'object' ) {
			return false;
		}

		if ( this.FilterPanelList[this.currentViewFrameIndex] ) {
			this.FilterPanelList[this.currentViewFrameIndex].hide();
		}

		if ( this.GridPanelList[this.currentViewFrameIndex] ) {
			this.GridPanelList[this.currentViewFrameIndex].hide();
		}

		this.currentViewFrameIndex = oprions.index;

		if ( options.hideToolbar == true ) {
			this.WindowToolbar.hide();
		}
		else {
			this.WindowToolbar.show();
		}

		// this.FilterPanel = this.FilterPanelList[this.currentViewFrameIndex];
		// this.GridPanel = this.GridPanelList[this.currentViewFrameIndex];

		// this.doLayout();

		return true;
	},
*/
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
			text      : lang['zakryit'],
			tabIndex  : -1,
			tooltip   : lang['zakryit'],
			iconCls   : 'cancel16',
			handler   : function()
			{
				this.ownerCt.hide();
			}
		}
	],
	
	show: function()
	{
		sw.Promed.swWorkPlaceWindow.superclass.show.apply(this, arguments);
		if ( arguments[0] ) {
			if ( arguments[0].userMedStaffFact && arguments[0].userMedStaffFact.ARMType ) {
				this.ARMType = arguments[0].userMedStaffFact.ARMType;
				this.userMedStaffFact = arguments[0].userMedStaffFact;
			}
			else {
				if ( arguments[0].MedService_id ) {
					this.MedService_id = arguments[0].MedService_id;
					this.userMedStaffFact = arguments[0];
				}
				else {
					if ( arguments[0].ARMType ) { // Это АРМ без привязки к врачу - АРМ администратора или кадровика
						this.userMedStaffFact = arguments[0];
					} else {
						this.hide();
						sw.swMsg.alert('Ошибка открытия формы', 'Ошибка открытия формы "'+this.title+'".<br/>Не указан тип АРМа.');
						return false;
					}
				}
			}
		}
		else {
			this.hide();
			sw.swMsg.alert('Ошибка открытия формы', 'Ошибка открытия формы "'+this.title+'".<br/>Отсутствуют необходимые параметры.');
			return false;
		}
		if (this.id.inlist([ 'swWorkPlaceSMPDispatcherCallWindow','swWorkPlaceSMPDispatcherDirectWindow','swWorkPlaceSMPHeadDutyWindow','swWorkPlaceSMPAdminWindow' ])) {
			this.comboHourSelect.show();
		} else {
			this.comboHourSelect.hide();
		}
		if (this.id.inlist([ 'swSmpStacDiffDiagJournal' ])) {
			this.diffDiagView.show();
		} else {
			this.diffDiagView.hide();
		}
		if(this.id ==='swSmpStacDiffDiagJournal' && isUserGroup('smpAdminRegion')){
			this.lpu_id.show();
			this.lpu_id.setFieldValue('Lpu_id', arguments[0].Lpu_id ? arguments[0].Lpu_id : sw.Promed.MedStaffFactByUser.current.Lpu_id)
		}else{
			this.lpu_id.hide();
		}
		if (this.id.inlist(['swWorkPlaceSMPDispatcherDirectWindow','swWorkPlaceSMPHeadDutyWindow','swWorkPlaceSMPAdminWindow' ])) {
			this.dispatchCallSelect.show();
			this.dispatchCallSelect.getStore().removeAll();
			this.dispatchCallSelect.reset();
			this.dispatchCallSelect.getStore().load();
			
			this.emergencyTeamCombo.show();
			this.emergencyTeamCombo.getStore().removeAll();
			this.emergencyTeamCombo.reset();
			this.emergencyTeamCombo.getStore().load();

			this.LpuBuildingSelect.show();
			this.LpuBuildingSelect.getStore().removeAll();
			this.LpuBuildingSelect.reset();
			this.LpuBuildingSelect.getStore().load();

		} else {
			this.dispatchCallSelect.hide();
			this.emergencyTeamCombo.hide();
			this.LpuBuildingSelect.hide();
		}
		this.getCurrentDateTime();

		sw.Promed.MedStaffFactByUser.setMenuTitle(this, this.userMedStaffFact);
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
						// Проставляем время и режим
						this.mode = 'day';
						this.currentDay();

						if ( this.gridPanelAutoLoad == true ) {
							this.doSearch('day');
						}

						this.getLoadMask().hide();
					}
				}.createDelegate(this)
			});
		} else {
			this.curDate = getGlobalOptions().date;
			// Проставляем время и режим
			this.mode = 'day';
			this.currentDay();

			if ( this.gridPanelAutoLoad == true ) {
				this.doSearch('day');
			}
		}
	},

	setTitleFieldset: function()
	{
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
	getPeriodToggle: function (mode)
	{	
		switch(mode)
		{
		case 'day':
			return this.formActions.day.items[0];
			break;
		case 'week':
			return this.formActions.week.items[0];
			break;
		case 'month':
			return this.formActions.month.items[0];
			break;
		case 'range':
			return this.formActions.range.items[0];
			break;
		case 'diffDiagView':
			return this.formActions.diffDiagView.items[0];
			break;
		default:
			return null;
			break;
		}
	},
	doSearch: function(mode){

		var w = Ext.WindowMgr.getActive();
		// Не выполняем если открыто модальное окно. Иначе при обновлении списка,
		// выделение с текущего элемента снимается и устанавливается на первом элементе
		// в списке. В свою очередь все рабочие места получают не верные данные из
		// выделенного объекта, вместо ранее выделенного пользователем.
		// @todo Проверка неудачная. Необходимо найти другое решение.
		
		// Текущее активное окно является модальным?
		if ( w.modal && !this.disableCheckModal) {
			return;
		}

		if (mode == "period"){
			var dateFrom = new Date(this.dateMenu.getValue1()),
				dateTo = new Date(this.dateMenu.getValue2());

			if (((dateTo - dateFrom) / 86400000) >= 31){
				Ext.Msg.alert('Ошибка', 'заданный период не может превышать 31 день');
				return false;
			}
		}


		var params = Ext.apply(this.FilterPanel.getForm().getValues(), this.searchParams || {});
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
		var displayDeletedCards = Ext.getCmp('displayDeletedCards');
		if(displayDeletedCards){
			// Показывать удаленные вызовы
			params.displayDeletedCards = ( displayDeletedCards.getValue() ) ? 'on' : 0;
		}
		if(this.lpu_id){
			params.Lpu_id = this.lpu_id.getValue();
		}
		params.begDate = Ext.util.Format.date(this.dateMenu.getValue1(), 'd.m.Y');
		params.endDate = Ext.util.Format.date(this.dateMenu.getValue2(), 'd.m.Y');
		params.hours = this.comboHourSelect.getValue();
		params.diffDiagView = this.diffDiagView.getValue();
		params.dispatchCallPmUser_id = (this.dispatchCallSelect.getValue()==0)?0:this.dispatchCallSelect.getValue();
		params.EmergencyTeam_id = (this.emergencyTeamCombo.getValue()==0)?0:this.emergencyTeamCombo.getValue();
		params.LpuBuilding_id = (this.LpuBuildingSelect.getValue()==0)?0:this.LpuBuildingSelect.getValue();
		this.GridPanel.removeAll({clearAll:true});
		this.GridPanel.loadData({globalFilters: params});
		this.emergencyTeamCombo.store.load({
			params: {
				begDate: params.begDate,
				endDate: params.endDate,
				LpuBuilding_id: params.LpuBuilding_id
			}
		})
	},
	
	doReset: function()
	{
		this.FilterPanel.getForm().reset();
		//this.FilterPanel.getForm().findField('Message_isRead').setValue(0);
		//this.setTitleFieldset();
		//this.GridPanel.getStore().baseParams = {};
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
	setActionDisabled: function(action, flag)
	{
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
	
	createFormActions: function() {
		
		var parent_object = this;
		this.timeMenu = new Ext.form.TimeField ({
			//disabled: true,
			fieldLabel: lang['vremya_do_kontsa_ojidaniya'],
			name: 'PPD_WaitingTime',
			id: this.id+'PPD_WaitingTime',
			format: 'H:i',
			plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
			validateOnBlur: false,
			width: 40,
			xtype: 'swtimefield',
			triggerAction: function () {
				//alert('click');
			},
			triggerClass: 'hidden-class',
			hidden:true,
			listeners: {
				focus: function(){
					this.disable();
					var parent_object = this;
					var SetWaitingPPDTimeWindow = new Ext.Window({
						width:400,
						heigth:300,
						title:lang['vvedite_novoe_vremya_ojidaniya'],
						modal: true,
						draggable:false,
						resizable:false,
						closable : false,
						items:[{
							xtype: 'form',
							bodyStyle: {padding: '10px'},
							disabledClass: 'field-disabled',
							items:
							[{																	
							//comboSubject: 'CmpReason',
								disabledClass: 'field-disabled',
								fieldLabel: lang['vremya_ojidaniya_prinyatiya_vyizova_v_nmp_min'],
								allowBlank: false,
								xtype: 'textfield',
								autoCreate: {tag: "input",  maxLength: "3", autocomplete: "off"},
								maskRe: /[0-9]/,
								id:'SetWaitingPPDTimeWindow_time',
								width:250
							},
							{
								disabledClass: 'field-disabled',
								fieldLabel: lang['vash_parol'],
								allowBlank: false,
								id: 'refuse_comment',
								// tabIndex: TABINDEX_PEF + 5,
								width: 250,
								inputType:'password',
								xtype: 'textfield',
								id:'SetWaitingPPDTimeWindow_pass'
							}]
						}],
						buttons:[{
							text:lang['ok'],
							handler:function(){
								var time = Ext.getCmp('SetWaitingPPDTimeWindow_time').getValue();
								var password = Ext.getCmp('SetWaitingPPDTimeWindow_pass').getValue();

								if ((!time)||(!password)) {
									sw.swMsg.show({
										buttons: Ext.Msg.OK,
										icon: Ext.Msg.WARNING,
										msg: lang['vse_polya_doljnyi_byit_zapolnenyi'],
										title: ERR_INVFIELDS_TIT
									});
									return false;
								}
								
								Ext.Ajax.request({
									params: {
										PPD_WaitingTime: time,
										Password: password
									},
									callback: function(options, success, response) {
										var response_obj = Ext.util.JSON.decode(response.responseText);
										if (success) {
											if ((!response_obj.success) ) {
												sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg);
											}
											else {
												SetWaitingPPDTimeWindow.close();
											}
										}
										else {
											sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_ustanovke_vremeni_ojidaniya_prinyatiya_vyizova']);
										}
									},
									url: '/?c=CmpCallCard&m=setPPDWaitingTime'
								});
								SetWaitingPPDTimeWindow.close();
							}
						},
						{
							text: lang['otmena'],
							handler: function(){
								SetWaitingPPDTimeWindow.close();
							}
						}]
					})
					SetWaitingPPDTimeWindow.show();
	
					this.enable();//TODO: Убрать этот комментарий потом
				}
			}
		});
		this.diffDiagView = new Ext.form.Checkbox({
			boxLabel: lang['pokazat_rashozhdeniya_diag_SMP_stac'],
			handler: function(ct, checked)
			{
				// показать с разными диагнозами
				this.doSearch('period');
			}.createDelegate(this),
			width: 300,
			hiddenName:'diffDiagView',
			name:'diffDiagView',
			enableKeyEvents: true,
			ctCls: 'diffDiagView',
			id: 'diffDiagView'
		});

		this.lpu_id= new sw.Promed.swlpuwithopersmpcombo({
			emptyText : langs('МО'),
			listeners:{
				select: function(combo,record,index) {
					this.doSearch('period');
				}.createDelegate(this)
			}
		});

		this.timeMenuLabel = new Ext.form.Label ({
			disabled: false,
			text: lang['vremya_do_kontsa_ojidaniya'],
			width: 180,
			hidden:true
		});
		
		this.comboHourSelect = new Ext.form.ComboBox({
			forceSelection:true,
			editable: false,
			hidden: true,
			store: new Ext.data.JsonStore({
				fields: [
					{name: 'name', type: 'string'},
					{name: 'val',  type: 'string'}
				],
				data : [
					{name:lang['za_posledniy_chas'],val:'1'},
					{name:lang['za_poslednie_2_chasa'],val:'2'},
					{name:lang['za_poslednie_3_chasa'],val:'3'},
					{name:lang['za_poslednie_6_chasov'],val:'6'},
					{name:lang['za_poslednie_12_chasov'],val:'12'},
					{name:lang['za_poslednie_sutki'],val:'24'}
				]}),
			displayField:'name',
			valueField:'val',
			typeAhead: true,
			mode: 'local',
			triggerAction: 'all',
			selectOnFocus:true,
			width:145,
			tpl: '<tpl for="."><div class="x-combo-list-item" style="height:16px;">'+'{name} '+'</div></tpl>'
		});
		this.dispatchCallSelect = new sw.Promed.SwSmpDispatchCallCombo({
			hidden: true,
			forceSelection:true,
			editable: false,
			typeAhead: true,
			selectOnFocus:true,
			emptyText : lang['dispetcher_vyizovov'],
			width: 150,
			listeners:{
				select: function(combo,record,index) {
					this.doSearch('period');
				}.createDelegate(this)
			}
		});
		this.LpuBuildingSelect = new sw.Promed.SmpUnits({
			autoload: true,
			hidden: !getRegionNick().inlist([ 'krym', 'ufa' ]),
			allowBlank : true,
			forceSelection:true,
			editable: false,
			typeAhead: true,
			selectOnFocus:true,
			emptyText : lang['podstantsiya'],
			width: 250,
			listeners:{
				select: function(combo,record,index) {
					var rec = this.emergencyTeamCombo.store.getById(this.emergencyTeamCombo.getValue());
					if(rec && (rec.get('LpuBuilding_id') != record.get('LpuBuilding_id') )){
						this.emergencyTeamCombo.reset()
					}
					this.doSearch('period');
				}.createDelegate(this)
			}
		});
		this.emergencyTeamCombo = new sw.Promed.swEmergencyTeamCombo({
			autoload: true,
			hidden: true,
			allowBlank : true,
			forceSelection:true,
			editable: false,
			typeAhead: true,
			selectOnFocus:true,
			emptyText : lang['brigada'],
			width: 250,
			listeners:{
				select: function(combo,record,index) {
					this.doSearch('period');
				}.createDelegate(this)
			}
		});
		this.emergencyTeamCombo.store.addListener('load',function(){if(parent_object.emergencyTeamCombo){parent_object.emergencyTeamCombo.collapse()}});
		//Проверяем, являются ли обе даты из this.dateMenu равными и являются сегодняшней датой
		this.checkBothDatesEqualAndToday = function() {
			var today = new Date();
			var dd = today.getDate();
			var mm = today.getMonth()+1; //January is 0!

			var yyyy = today.getFullYear();
			if(dd<10){dd='0'+dd} 
			if(mm<10){mm='0'+mm} 
			today = dd+'.'+mm+'.'+yyyy;
			return ((this.dateMenu.getValue1().valueOf() == this.dateMenu.getValue2().valueOf())&&Ext.util.Format.date(this.dateMenu.getValue1(), 'd.m.Y')==today && Ext.util.Format.date(this.dateMenu.getValue2(), 'd.m.Y')==today)
		}.createDelegate(this);
		
		this.comboHourSelect.addListener('select',function(combo,record,index) {
			console.log(combo.getValue());
			this.doSearch('period');
		}.createDelegate(this));

		this.dateMenu = new Ext.form.DateRangeField({
			width: 150,
			fieldLabel: lang['period'],
			id: this.id+'_periodField',
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
			this.comboHourSelect.setDisabled(!this.checkBothDatesEqualAndToday());
			// Читаем расписание за период
			this.doSearch('period');
		}.createDelegate(this));
		this.dateMenu.addListener('blur',function () 
		{
			this.comboHourSelect.setDisabled(!this.checkBothDatesEqualAndToday());
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
				this.comboHourSelect.setDisabled(!this.checkBothDatesEqualAndToday());
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
				this.comboHourSelect.setDisabled(!this.checkBothDatesEqualAndToday());
				this.doSearch('range');
			}.createDelegate(this)
		});
		this.formActions.day = new Ext.Action(
		{
			text: lang['den'],
			xtype: 'button',
			toggleGroup: 'periodToggle',
			iconCls: 'datepicker-day16',
			id: 'periodDey',
			pressed: true,
			handler: function()
			{
				this.currentDay();
				this.comboHourSelect.setDisabled(!this.checkBothDatesEqualAndToday());
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
				this.comboHourSelect.setDisabled(!this.checkBothDatesEqualAndToday());
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
				this.comboHourSelect.setDisabled(!this.checkBothDatesEqualAndToday());
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
			iconCls: 'datepicker-range16',
			handler: function()
			{
				this.comboHourSelect.setDisabled(!this.checkBothDatesEqualAndToday());
				this.doSearch('range');
			}.createDelegate(this)
		});
		this.formActions.displayDeletedCards = new Ext.Action(
		{
			name: 'displayDeletedCards',
			labelSeparator: '',
			boxLabel: 'Показывать удаленные вызовы',
			xtype: 'checkbox'
		});
	},
	buttonPanelActions: {},
	
	setTimeMenuVisibility: function() {
		this.timeMenu.show();
		this.timeMenuLabel.show();
		
	},

	setTimeMenu: function (time) {
		this.timeMenu.setRawValue(time);
	},

	initComponent: function()
	{
		var form = this;

		this.buttonPanelActions.action_Report = { //http://redmine.swan.perm.ru/issues/18509
				nn: 'action_Report',
					tooltip: lang['prosmotr_otchetov'],
					text: lang['prosmotr_otchetov'],
					iconCls: 'report32',
					//hidden: !this.enableDefaultActions,//( !document.getElementById('swWorkPlaceCallCenterWindow') || !this.enableDefaultActions ),
					handler: function() {
						var ARMType = '';
						if(Ext.isEmpty(form.ARMType))
						{
							if(form.userMedStaffFact && form.userMedStaffFact.ARMType)
								ARMType = form.userMedStaffFact.ARMType;
						}
						else
							ARMType = form.ARMType;
					if (sw.codeInfo.loadEngineReports)
					{
						getWnd('swReportEndUserWindow').show({ARMType:ARMType});
					}
					else
					{
						getWnd('reports').load(
							{
								callback: function(success)
								{
									sw.codeInfo.loadEngineReports = success;
									// здесь можно проверять только успешную загрузку
									getWnd('swReportEndUserWindow').show({ARMType:ARMType});
								}
							});
					}
				}
			};
		this.createFormActions();
		
		if( this.redefinedWindowToolbar && this.redefinedWindowToolbar.length>0 ) {
			var formActionsArrFields = ['prev','next','day','week','month','range'];
			var fieldsTBar = this.redefinedWindowToolbar;
			var itemsToolbar = [];
			fieldsTBar.forEach(function(item){
				if( item.inlist(formActionsArrFields) ){
					if( form.formActions[item] ) itemsToolbar.push(form.formActions[item]);
				}else{
					if( form[item] ) itemsToolbar.push(form[item]);
				}
			});
			this.WindowToolbar = new Ext.Toolbar({
				items: itemsToolbar
			});
		}else{
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
						xtype : "tbseparator"
					},
					this.lpu_id,
					{
						xtype : "tbseparator"
					},
					this.diffDiagView,
					{
						xtype : "tbseparator"
					},
					this.comboHourSelect,
					{
						xtype: 'tbfill'
					},
					this.dispatchCallSelect,
					this.LpuBuildingSelect,
					this.emergencyTeamCombo,
					{
						xtype : "tbseparator"
					},
					this.timeMenuLabel,
					this.timeMenu,
					{
						xtype : "tbseparator"
					},
					this.formActions.day, 
					this.formActions.week, 
					this.formActions.month,
					this.formActions.range
				]
			});
		}
		
		if ( !this.FilterPanel && typeof this.FilterPanelList != 'object' ) {
			this.FilterPanel = new sw.Promed.BaseWorkPlaceFilterPanel({
				owner: form
			});
		}
		this.LeftPanel = new sw.Promed.BaseWorkPlaceButtonsPanel({
			animCollapse: false,
			width: 60,
			minSize: 60,
			maxSize: 120,
			region: 'west',
			floatable: false,
			collapsible: true,
			id: form.id + '_buttPanel',
			layoutConfig:
			{
				titleCollapse: true,
				animate: true,
				activeOnTop: false
			},
			listeners:
			{
				collapse: function()
				{
					return;
				},
				resize: function (p,nW, nH, oW, oH)
				{
					var el = null;
					el = form.findById(form.id + '_buttPanel_slid');
					if(el)
						el.setHeight(this.body.dom.clientHeight-42);
					
					return;
				}
				
			},
			border: false,
			title: ' ',
			titleCollapse: true,
			hidden: !form.showLeftMenu,
			enableDefaultActions: (typeof form.enableDefaultActions == 'boolean')?form.enableDefaultActions:true,
			panelActions: form.buttonPanelActions
		});

		var centralPanelItems = [this.LeftPanel,this.GridPanel];
		if (this.ElectronicQueuePanel) centralPanelItems.push(this.ElectronicQueuePanel);

		if (!this.CenterPanel) {
			this.CenterPanel = new sw.Promed.Panel({
				region: 'center',
				border: false,
				layout: 'border',
				items: centralPanelItems
			});
		}
		
		Ext.apply(this,	{
			layout: 'border',
			items: [
				this.FilterPanel,
				this.CenterPanel
			]
		});

		if ( this.showToolbar == true ) {
			Ext.apply(this,	{
				tbar: this.WindowToolbar
			});
		}

		sw.Promed.swWorkPlaceWindow.superclass.initComponent.apply(this, arguments);
	}
});