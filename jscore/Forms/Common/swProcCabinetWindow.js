/**
* АРМ медсестры процедурного кабинета
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @autor		Dmitry Vlasenko
* @copyright    Copyright (c) 2011 Swan Ltd.
* @version      15.01.2013
*/

/*NO PARSE JSON*/
sw.Promed.swProcCabinetWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swProcCabinetWindow',
	maximized: true,
	border: false,
	buttonAlign: 'right',
	closable: true,
	closeAction: 'hide',
	modal: false,
	plain: false,
	resizable: false,
	title: langs('Работа процедурного кабинета'),
	userMedStaffFact: null,
	show: function()
	{
		var win = this;

		sw.Promed.swProcCabinetWindow.superclass.show.apply(win, arguments);
		if ((!arguments[0]) || (!arguments[0].userMedStaffFact))
		{
			win.hide();
			Ext.Msg.alert('Ошибка открытия формы', 'Ошибка открытия формы "'+win.title+'".<br/>Не указаны параметры АРМа врача.');
		} else {
			win.userMedStaffFact = arguments[0].userMedStaffFact;
		}

		var action = {
			name:'action_signin',
			text:'Записать',
			icon: 'img/icons/add16.png',
			iconCls : 'x-btn-text',
			handler: function(){
				win.openDirectionMasterWindow();
				//getWnd('swDirectionMasterWindow').show();
			}
		};
		win.GridPanel.addActions(action, 3);

		win.datePeriodToolbar.onShow(win);

		var uslugaCombo = this.FilterPanel.getForm().findField('UslugaComplex_id');

		uslugaCombo.getStore().baseParams = {
			MedService_id: win.userMedStaffFact.MedService_id,
			allowedUslugaComplexAttributeList : 'manproc'
		}
		uslugaCombo.getStore().load();
		//base_form.findField('UslugaComplex_id').getStore().baseParams['allowedUslugaComplexAttributeList'] = Ext.util.JSON.encode(['manproc']);
	},
	showMask: function(msg){
		if(!msg){
			msg = 'Подождите, идет загрузка...';
		}
		this.mask = new Ext.LoadMask(Ext.get('swProcCabinetWindow'), { msg: msg });
		this.mask.show();
	},
	hideMask: function(){
		if(this.mask){
			this.mask.hide();
		}
	},
	onKeyDown: function (inp, e) {
		if (e.getKey() == Ext.EventObject.ENTER) {
			e.stopEvent();
			this.doSearch();
		}
	},
	initComponent: function()
	{
		var win = this;

		win.datePeriodToolbar = new sw.Promed.datePeriodToolbar({
			curDate: getGlobalOptions().date,
			mode: 'day',
			onSelectPeriod: function(begDate,endDate,allowLoad)
			{
				win.doSearch();
			}
		});


		win.FilterPanel = new sw.Promed.BaseWorkPlaceFilterPanel({
			owner: win,
			tbar: win.datePeriodToolbar,
			filter: {
				title: 'Фильтр',
				layout: 'form',
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
					}, {
						layout: 'form',
						labelWidth: 35,
						items:
							[{
								xtype:'swdatefield',
								format:'d.m.Y',
								plugins:[ new Ext.ux.InputTextMask('99.99.9999', false) ],
								name: 'Search_BirthDay',
								fieldLabel: 'ДР',
								listeners: {
									'keydown': win.onKeyDown.createDelegate(win)
								}
							}]
					}, {
						layout: 'form',
						labelWidth: 145,
						items:
							[{
								xtype: 'textfield',
								width: 100,
								name: 'EvnDirection_Num',
								fieldLabel: 'Номер направления',
								listeners: {
									'keydown': win.onKeyDown.createDelegate(win)
								}
							}]
					}]
				}, {
					layout: 'column',
					items: [{
						layout: 'form',
						labelWidth: 65,
						items:
							[{
								xtype: 'swuslugacomplexpidcombo',
								width: 450,
								name: 'Search_Usluga',
								hiddenName: 'UslugaComplex_id',
								fieldLabel: 'Услуга',
								//allowBlank: false,
								listeners: {
									'keydown': win.onKeyDown.createDelegate(win)
								}
							}]
					}, {
						layout: 'form',
						labelWidth: 55,
						items:
							[{
								fieldLabel: 'Cito',
								comboSubject: 'YesNo',
								name: 'EvnDirection_IsCito',
								hiddenName: 'EvnDirection_IsCito',
								xtype: 'swcommonsprcombo'
							}]
					}]
				}, {
					layout: 'column',
					items: [{
						layout: 'form',
						items:
							[{
								xtype: 'button',
								id: win.id+'BtnSearch',
								text: 'Найти',
								iconCls: 'search16',
								handler: function()
								{
									win.doSearch();
								}.createDelegate(win)
							}]
					}, {
						layout: 'form',
						items:
							[{
								style: "padding-left: 10px",
								xtype: 'button',
								id: win.id+'BtnClear',
								text: 'Сброс',
								iconCls: 'reset16',
								handler: function()
								{
									win.doReset();
								}.createDelegate(win)
							}]
					}]
				}]
			}
		});

		win.GridPanel = new sw.Promed.ViewFrame({
			id: 'WorkPlaceGridPanel',
			region: 'center',
			autoExpandColumn: 'autoexpand',
			grouping: true,
			groupTextTpl: '{[values.text == "(Пусто)" ? "Очередь" : values.text]} ({[values.rs.length]} {[parseInt(values.rs.length.toString().charAt(values.rs.length.toString().length-1)).inlist([1]) ?"заявка" :(parseInt(values.rs.length.toString().charAt(values.rs.length.toString().length-1)).inlist([2,3,4]) ? "заявки" : "заявок")]})',
			groupingView: {showGroupName: false, showGroupsText: true},
			actions:
				[
					{name:'action_add', hidden: true, handler: function() { this.openEvnFuncRequestEditWindow('add', false);}.createDelegate(this) },
					{name:'action_edit', handler: function() { this.openEvnFuncRequestEditWindow('edit', false);}.createDelegate(this) },
					{name:'action_view', handler: function() { this.openEvnFuncRequestEditWindow('view', false);}.createDelegate(this) },
					{name:'action_delete',text:'Отклонить', handler: function() { win.deleteEvnFuncRequest();} },
					{name: 'action_refresh'},
					{name: 'action_print'}
				],
			autoLoadData: false,
			pageSize: 20,
			useEmptyRecord: false,
			stringfields:
				[
					// Поля для отображение в гриде
					{name: 'EvnDirection_id', type: 'int', header: 'ID', key: true},
					{name: 'EvnFuncRequest_id', type: 'int', hidden:true},
					{name: 'EvnQueue_id', type: 'int', hidden:true},
					{name: 'TimetableMedService_id', type: 'int', hidden:true},
					{name: 'TimetableMedService_begDate', type: 'date', hidden: true, group: true, sort: true, direction: 'DESC' },
					{name: 'Person_id', type: 'int', hidden: true},
					{name: 'PersonEvn_id', type: 'int', hidden: true},
					{name: 'Server_id', type: 'int', hidden: true},
					{name: 'EvnDirection_IsCito', header: 'Cito!', type: 'checkbox', width: 40},
					{name: 'FuncRequestState', header: 'Приём', type: 'checkbox', width: 60 },
					{name: 'EvnDirection_setDT', dateFormat: 'd.m.Y', type: 'date', header: 'Дата направления', width: 120},
					{name: 'TimetableMedService_begTime', type: 'string', header: 'Запись', width: 120/*, sort: true, direction: 'ASC'*/},
					{name: 'EvnUslugaPar_setDate', dateFormat: 'd.m.Y', type: 'date', header: 'Дата исследования', width: 120, hidden: (getGlobalOptions().region.nick != 'kz')},
					{name: 'TimetableMedServiceType', type: 'string', header: 'Расписание', width: 120},
					{name: 'EvnDirection_Num', header: 'Номер направления', type: 'string', width: 160},
					{name: 'Person_FIO', header: 'ФИО пациента', type: 'string', width: 320},
					{name: 'EvnFuncRequest_UslugaCache', header: 'Список услуг', renderer: function(value, cellEl, rec) {
						var result = '';
						if (!Ext.isEmpty(value)) {
							// разджейсониваем
							var uslugas = Ext.util.JSON.decode(value);
							for(var k in uslugas) {
								if (uslugas[k].UslugaComplex_Name) {
									if (!Ext.isEmpty(result)) {
										result += '<br />';
									}
									result += uslugas[k].UslugaComplex_Name;

									if (!Ext.isEmpty(uslugas[k].EvnUslugaPar_setDate)) {
										result += ' <a title="Отменить выполнение услуги" href="javascript://" onClick="Ext.getCmp(\'swWorkPlaceFuncDiagWindow\').cancelEvnUslugaPar({' +
											'EvnUslugaPar_id: ' + uslugas[k].EvnUslugaPar_id +
											'});"><img width="14" src="/img/icons/cancel_blue16.png" /></a>';
									}
								}
							}
						}
						return result;
					}, width: 420, id: 'autoexpand'},
					{name: 'pmUser_insID', type: 'int', hidden: true}
				],
			dataUrl: '/?c=EvnFuncRequestProc&m=loadEvnFuncRequestList',
			totalProperty: 'totalCount',
			title: 'Список заявок',
			onLoadData: function(sm, index, record)
			{
				if (!this.getGrid().getStore().totalLength) {
					this.getGrid().getStore().removeAll();
				}
				if( Ext.get(this.getGrid().getView().getGroupId('(Пусто)')) != null ) {
					this.getGrid().getView().toggleGroup(this.getGrid().getView().getGroupId('(Пусто)'), false);
				}

			},
			onDblClick: function(grid, number, object){
				this.onEnter();
			},
			onRowSelect: function(sm,index,record) {
				var disabled = record.get('EvnDirection_id') ? false : true,
					disabledState = record.get('FuncRequestState') == "true" ? true : false;
				this.getAction('action_edit').setDisabled( disabled );
				this.getAction('action_view').setDisabled( disabled );
				this.getAction('action_delete').setDisabled( disabled || disabledState );
				this.getAction('action_signin').setDisabled( disabled );
			},
			onEnter: function()
			{
				var record = this.GridPanel.ViewGridPanel.getSelectionModel().getSelected();
				if (Ext.isEmpty(record.get('EvnDirection_id'))) {
					this.openEvnFuncRequestEditWindow('add', true);
				} else {
					this.openEvnFuncRequestEditWindow('edit', false);
				}
			}.createDelegate(this)
		});

		Ext.apply(this,
			{
				region: 'center',
				layout: 'border',
				items: [
					this.FilterPanel,
					this.GridPanel
				],
				buttons: [{
					id: 'EJHW_BtnSearch',
					text: langs('Найти'),
					tabIndex: TABINDEX_EJHW + 19,
					iconCls: 'search16',
					handler: function()
					{
						this.doSearch();
					}.createDelegate(this)
				},
				{
					id: 'EJHW_BtnClear1',
					text: langs('Сброс'),
					tabIndex: TABINDEX_EJHW + 21,
					iconCls: 'resetsearch16',
					handler: function()
					{
						this.resetForm(true);
					}.createDelegate(this)
				},
				{
					text: '-'
				},
				{
					text: BTN_FRMHELP,
					iconCls: 'help16',
					id: 'EJHW_HelpButton',
					handler: function(button, event)
					{
						ShowHelp(this.title);
					}.createDelegate(this)
				},
				{
					iconCls: 'cancel16',
					text: BTN_FRMCLOSE,
					tabIndex: TABINDEX_EJHW + 50,
					handler: function() {
						this.hide();
					}.createDelegate(this)
				}]
			});
		sw.Promed.swProcCabinetWindow.superclass.initComponent.apply(this, arguments);
	},
	doSearch: function(mode){

		var win = this;

		if(!mode)mode = win.datePeriodToolbar.mode;
		var w = Ext.WindowMgr.getActive();

		if ( w.modal && !win.disableCheckModal) {
			return;
		}

		var params = {};

		win.FilterPanel.getForm().items.each(function(cmp){
			params[cmp.name] = cmp.getValue();
		});

		params.begDate = Ext.util.Format.date(win.datePeriodToolbar.dateMenu.getValue1(), 'd.m.Y');
		params.endDate = Ext.util.Format.date(win.datePeriodToolbar.dateMenu.getValue2(), 'd.m.Y');
		params.Search_BirthDay = Ext.util.Format.date(params.Search_BirthDay, 'd.m.Y');
		params.MedService_id = win.userMedStaffFact.MedService_id;
		params.wnd_id = 'swWorkPlaceProcCabinetWindow';

		this.GridPanel.removeAll({clearAll:true});
		this.GridPanel.loadData({globalFilters: params});
		/*
		this.emergencyTeamCombo.store.load({
			params: {
				begDate: params.begDate,
				endDate: params.endDate,
				LpuBuilding_id: params.LpuBuilding_id
			}
		})*/
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

	// запись пациента к себе или другому врачу с выпиской электр.направления
	openDirectionMasterWindow: function(){

		var win = this,
			grid = win.GridPanel.getGrid(),
			selected_record = grid.getSelectionModel().getSelected(),
			onHide = function(){
				if ( selected_record ) {
					grid.getView().focusRow(grid.getStore().indexOf(selected_record));
				} else {
					//grid.getView().focusRow(0);
					grid.getSelectionModel().selectFirstRow();
				}
			};

		if ( selected_record ){

			win.showMask('Подождите, идет проверка на свободные назначения');

			Ext.Ajax.request({
				url: '/?c=EvnDirection&m=getDataEvnDirection',
				callback: function (options, success, response) {
					win.hideMask();

					if (success) {
						var result  = Ext.util.JSON.decode(response.responseText);
						var data = result[0];

						var EvnFuncRequest_UslugaCache = selected_record.get('EvnFuncRequest_UslugaCache') ? JSON.parse(selected_record.get('EvnFuncRequest_UslugaCache'))[0] : null;
						var Usluga_isCito = (selected_record.get('EvnDirection_IsCito') && selected_record.get('EvnDirection_IsCito') == 'true') ? 2 : 1;
						if(!data.EvnPrescrProc_id){
							Ext.Msg.alert('Ошибка записи', 'Нет свободных назначений.');
							win.hideMask();
							return false;
						}

						var params =
						{
							//useCase: 'record_from_queue',
							Diag_id: data.Diag_id,
							personData: {
								Person_id: data.Person_id,
								Server_id: data.Server_id,
								PersonEvn_id: data.PersonEvn_id,
								Person_Birthday: data.Person_BirthDay,
								Person_Surname: data.Person_SurName,
								Person_Firname: data.Person_FirName,
								Person_Secname: data.Person_SecName
							},
							EvnQueue_id: data.EvnQueue_id,
							EvnDirection_pid: data.EvnDirection_pid || null,
							LpuSectionProfile_id: data.LpuSectionProfile_did,
							Filter_Lpu_Nick: data.Lpu_Nick,
							userMedStaffFact: win.userMedStaffFact,
							TimetableData: {
								type: 'TimetableMedService',
								//EvnQueue_id: selected_record.get('EvnQueue_id'),
								//EvnDirection_id: data.EvnDirection_id,
								EvnDirection_pid: data.EvnDirection_pid || null,
								//EvnDirection_Num: data.EvnDirection_Num,
								EvnDirection_setDate: data.EvnDirection_setDate,
								EvnDirection_IsAuto: data.EvnDirection_IsAuto,
								EvnDirection_IsReceive: data.EvnDirection_IsReceive,
								MedStaffFact_id: data.MedStaffFact_id,
								From_MedStaffFact_id: data.From_MedStaffFact_id,
								LpuUnit_did: data.LpuUnit_did,
								Lpu_did: data.Lpu_did,
								MedPersonal_did: data.MedPersonal_did,
								LpuSection_did: data.LpuSection_did,
								LpuSectionProfile_id: data.LpuSectionProfile_id,
								DirType_id: data.DirType_id,
								DirType_Code: data.DirType_Code,
								ARMType_id: data.ARMType_id,
								MedServiceType_SysNick: data.MedServiceType_SysNick,
								MedService_id: data.MedService_id,
								isAllowRecToUslugaComplexMedService: data.isAllowRecToUslugaComplexMedService,
								isAllowRecToUslugaComplexMedService: true,
								order: {
									"LpuSectionProfile_id": data.LpuSectionProfile_id,
									"UslugaComplex_id":data.UslugaComplex_id,
									"checked":"[]",
									//"Usluga_isCito":1,
									"Usluga_isCito":Usluga_isCito,
									"UslugaComplex_Name": EvnFuncRequest_UslugaCache ? EvnFuncRequest_UslugaCache.UslugaComplex_Name : '',
									"UslugaComplexMedService_id":data.UslugaComplexMedService_id,
									"MedService_id":data.MedService_id
								},
								//UslugaComplexMedService_id: (data.isAllowRecToUslugaComplexMedService && data.UslugaComplexMedService_id) ? data.UslugaComplexMedService_id : null,
								UslugaComplexMedService_id :data.UslugaComplexMedService_id,
								MedService_Nick: data.MedService_Nick
							},
							ARMType: (this.ARMType)?this.ARMType:'regpol',
							onDirection: function (success,dataDir) {

								var resultDir  = Ext.util.JSON.decode(dataDir.responseText);

								if(success && resultDir && resultDir.EvnDirection_id){
									Ext.Ajax.request({
										url: '/?c=EvnPrescr&m=directEvnPrescr',
										params: {
											EvnDirection_id: resultDir.EvnDirection_id,
											EvnPrescr_id: data.EvnPrescrProc_id
										},
										callback: function (options, success, response) {
											getWnd('swDirectionMasterWindow').hide();
											if (success) {}
										}
									});
								}

								grid.getStore().reload();
							}.createDelegate(this),
						};

						if (getWnd('swDirectionMasterWindow').isVisible()) {
							getWnd('swDirectionMasterWindow').hide()
						}
						getWnd('swDirectionMasterWindow').show(params);
					}
				},
				params: {
					EvnDirection_id: selected_record.get('EvnDirection_id')
				}
			});

		}

			/*
			var openDirectionWindow = function(personData) {
				var win = 'swDirectionMasterWindow';
				var my_params = new Object({
					userMedStaffFact: this.userMedStaffFact
					,personData: personData
					,directionData: {
						LpuUnitType_SysNick: null
						,EvnQueue_id: null
						,QueueFailCause_id: null
						,Lpu_did: null // ЛПУ куда направляем
						,LpuUnit_did: null
						,LpuSection_did: null
						,EvnUsluga_id: null
						,LpuSection_id: null
						,EvnDirection_pid: null
						,EvnPrescr_id: null
						,PrescriptionType_Code: null
						,DirType_id: null
						,LpuSectionProfile_id: null
						,Diag_id: null
						,MedService_id: this.userMedStaffFact.MedService_id
						,MedStaffFact_id: null
						,MedPersonal_id: this.userMedStaffFact.MedPersonal_id
						,MedPersonal_did: null
					}
					,onHide: onHide
					,onDirection: function(){
						getWnd(win).hide();
					}
				});

				if ( getWnd(win).isVisible() ) {
					getWnd(win).hide()
				}
				getWnd(win).show(my_params);
			}.createDelegate(this);

		if ( selected_record && selected_record.get('Person_id') ) {
			// человек выбран
			var person_fio = selected_record.get('Person_FIO').split(' ');
			var personData = {
				PersonEvn_id: selected_record.get('PersonEvn_id'),
				Person_id: selected_record.get('Person_id'),
				Server_id: selected_record.get('Server_id'),
				Person_Surname: person_fio[0] || '',
				Person_Firname: person_fio[1] || '',
				Person_Secname: person_fio[2] || '',
				Person_Birthday: selected_record.get('Person_BirthDay')
			};

			checkPersonDead({
				Person_id: personData.Person_id,
				onIsLiving: function() {
					openDirectionWindow(personData);
				},
				onIsDead: function(res) {
					sw.swMsg.alert(langs('Ошибка'), langs('Направить пациента к врачу невозможно в связи со смертью пациента'));
				}
			});
		} else {
			// человек не выбран
			if ( getWnd('swPersonSearchWindow').isVisible() ) {
				getWnd('swPersonSearchWindow').hide()
			}
			getWnd('swPersonSearchWindow').show({
				onClose: onHide,
				onSelect: function(pdata)
				{
					checkPersonDead({
						Person_id: pdata.Person_id,
						onIsLiving: function() {
							getWnd('swPersonSearchWindow').hide();
							openDirectionWindow(pdata);
						},
						onIsDead: function(res) {
							sw.swMsg.alert(langs('Ошибка'), langs('Направить пациента к врачу невозможно в связи со смертью пациента'));
						}
					});
				},
				searchMode: 'all'
			});
		}
		*/
	},

	openEvnFuncRequestEditWindow: function(action, is_time) {
		var form = this;
		if ( action != 'add' && action != 'edit' && action != 'view' ) {
			return false;
		}

		sw.Applets.commonReader.stopReaders();

		var swPersonSearchWindow = getWnd('swPersonSearchWindow');
		if ( action == 'add' && swPersonSearchWindow.isVisible() ) {
			sw.swMsg.alert(langs('Окно поиска человека уже открыто'), langs('Для продолжения необходимо закрыть окно поиска человека.'));
			return false;
		}

		var grid = form.GridPanel.getGrid();

		var params = new Object();

		params.MedService_id = form.userMedStaffFact.MedService_id;

		params.action = action;
		params.callback = function(data) {};
		params.swWorkPlaceProcCabinetWindow = form;

		if ( action == 'add' ) {

			if ( grid.getSelectionModel().getSelected() && grid.getSelectionModel().getSelected().get('TimetableMedService_id') && is_time == true ) {
				var record = grid.getSelectionModel().getSelected();
				params.TimetableMedService_id = record.get('TimetableMedService_id');
			}

			swPersonSearchWindow.show({
				onClose: function() {
					sw.Applets.commonReader.startReaders({callback: this.getDataFromUec.createDelegate(this)});
					if ( grid.getSelectionModel().getSelected() ) {
						grid.getView().focusRow(grid.getStore().indexOf(grid.getSelectionModel().getSelected()));
					}
					else {
						//grid.getView().focusRow(0);
						grid.getSelectionModel().selectFirstRow();
					}
				}.createDelegate(this),
				onSelect: function(person_data) {
					params.Person_id = person_data.Person_id;
					params.PersonEvn_id = person_data.PersonEvn_id;
					params.Server_id = person_data.Server_id;
					params.swPersonSearchWindow = swPersonSearchWindow;
					params.callback = function () {
						form.GridPanel.refreshRecords(null, 0);
					};
					params.onHide = function() {
						if (swPersonSearchWindow.isVisible()) {
							//На форме поиска человека нет такого метода
							//sw.Applets.commonReader.startReaders({callback: this.getDataFromUec.createDelegate(this)});
						}
					}
					this.hide(); // закрываем форму поиска человека
					getWnd('swEvnProcRequestEditWindow').show(params);
				},
				searchMode: 'all',
				needUecIdentification: true
			});

		} else {

			if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('EvnDirection_id') ) {
				sw.swMsg.alert(langs('Ошибка'), langs('Не выбрана заявка или направление!'));
				return false;
			}

			var record = grid.getSelectionModel().getSelected();

			params.EvnFuncRequest_id = record.get('EvnFuncRequest_id');
			params.EvnDirection_id = record.get('EvnDirection_id');
			params.Person_id = record.get('Person_id');
			params.PersonEvn_id = record.get('PersonEvn_id');
			params.Server_id = record.get('Server_id');
			params.callback = function () {
				form.GridPanel.refreshRecords(null, 0);
			};
			params.onHide = function() {
				grid.getView().focusRow(grid.getStore().indexOf(record));
				grid.getSelectionModel().selectRow(grid.getStore().indexOf(record));

				sw.Applets.commonReader.startReaders({callback: this.getDataFromUec.createDelegate(this)});
			}

			getWnd('swEvnProcRequestEditWindow').show(params);

		}
	},

	deleteEvnFuncRequest: function(){
		var win = this,
			grid = win.GridPanel.getGrid(),
			record = grid.getSelectionModel().getSelected();

		sw.swMsg.show(
			{
				buttons: Ext.Msg.YESNOCANCEL,
				fn: function(buttonId)
				{
					if ( buttonId == 'yes' )
					{
						Ext.Ajax.request({
							url: '/?c=EvnFuncRequestProc&m=delete',
							params: { EvnFuncRequest_id : record.get('EvnFuncRequest_id')},
							callback: function (options, success, response) {
								Ext.Ajax.request({
									url: '/?c=EvnDirection&m=deleteEvnDirection',
									params: { EvnDirection_id : record.get('EvnDirection_id')},
									callback: function (options, success, response) {
										grid.getStore().reload();
									}
								});
							}
						});
					}
				},
				icon: Ext.Msg.QUESTION,
				msg: 'Вы действительно хотите отклонить заявку?',
				title: 'Отклонение заявки'
			}
		);



	}
});