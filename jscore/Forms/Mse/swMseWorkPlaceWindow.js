/**
 * swMseWorkPlaceWindow.js - окно рабочего места Mse
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

sw.Promed.swMseWorkPlaceWindow = Ext.extend(sw.Promed.BaseForm,
{
	codeRefresh: true,
	objectName: 'swMseWorkPlaceWindow',
	objectSrc: '/jscore/Forms/Mse/swMseWorkPlaceWindow.js',
	//тип АРМа, определяется к каким функциям будет иметь доступ врач через ЭМК, например у стоматолога появится ввод талона по стоматологии,
	//у врача параклиники будет доступ только к параклиническим услугам
	ARMType: 'mse',
	closable: true,
	closeAction: 'hide',
	maximized: true,
	title: WND_WPMP,
	iconCls: 'workplace-mp16',
	id: 'swMseWorkPlaceWindow',
	readOnly: false,
	getCalendar: function () 
	{
		//return this.calendar;
	},
	getGrid: function () 
	{
		return this.ScheduleGrid;
	},
	getPeriodToggle: function (mode)
	{
		switch(mode)
		{
		case 'day':
			return this.DoctorToolbar.items.items[6];
			break;
		case 'week':
			return this.DoctorToolbar.items.items[7];
			break;
		case 'month':
			return this.DoctorToolbar.items.items[8];
			break;
		case 'range':
			return this.DoctorToolbar.items.items[9];
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
		//params.MedStaffFact_id = getGlobalOptions().CurMedStaffFact_id;
		params.MedService_id = this.MedService_id;
		
		//if (this.findById('msewpSearch_UslugaComplexPid').getValue())
		//	params.UslugaComplex_id = this.findById('msewpSearch_UslugaComplexPid').getValue();
		/*
		// При направлении указывается ЛПУ и врач этого ЛПУ 
		if (this.findById('msewpLpu_id').getValue()>0) {
			params.Lpu_id = this.findById('msewpLpu_id').getValue();
		}
		if (this.findById('msewpMedPersonal_id').getValue()>0) {
			params.MedPersonal_id = this.findById('msewpMedPersonal_id').getValue();
		}
		*/
		this.getGrid().loadStore(params);
	},
	scheduleSave: function(data)
	{
		var grid = this.getGrid();
	
		if (!data.TimetableGraf_id) { // если среди данных нет id бирки, извлекаем id из грида
			if ((grid.getSelectionModel().getSelected() == null) || 
			(grid.getSelectionModel().getSelected().get('TimetableGraf_id') == null) || 
			(grid.getSelectionModel().getSelected().get('Person_id') != null))
				return false;
				
			data.TimetableGraf_id = grid.getSelectionModel().getSelected().get('TimetableGraf_id');
		}		
		data.LpuUnitType_SysNick = 'polka';		
		
		this.savePosition();		
		this.getLoadMask(lang['podojdite_sohranyaetsya_zapis']).show();
		Ext.Ajax.request(
		{
			url: !data.EvnQueue_id ? C_TTG_APPLY : C_QUEUE_APPLY,
			params: data,
			callback: function(options, success, response) 
			{
				grid.getStore().reload();
				this.getLoadMask().hide();
			}.createDelegate(this),
			failure: function() 
			{
				this.getLoadMask().hide();
			}
		});
	},
	showDrugRequestEditForm: function()
	{
		var grid = this.getGrid(),
			selected_record = grid.getSelectionModel().getSelected(),
			Person_id = (selected_record && selected_record.get('Person_id')) || null;
		/*
		Получаем данные:
		является ли текущий пользователь врачом ЛЛО
		заявка на последний период, из имеющихся в системе или последняя имеющияся заявка.
		*/
		var params = {
			LpuSection_id: getGlobalOptions().CurLpuSection_id,
			MedPersonal_id: getGlobalOptions().medpersonal_id
		};
		this.getLoadMask(LOAD_WAIT).show();
		Ext.Ajax.request(
		{
			url: '/?c=DrugRequest&m=getDrugRequestLast',
			params: params,
			callback: function(options, success, response) 
			{
				this.getLoadMask().hide();
				var response_obj = Ext.util.JSON.decode(response.responseText);
				if (response_obj[0].is_dlo != 1)
				{
					Ext.Msg.alert(lang['soobschenie'], lang['vrach_ne_imeet_prava_na_vyipisku_retseptov_po_llo']);
					return false;
				}
				params.owner = this;
				params.Person_id = Person_id;
				if (response_obj[0].next_DrugRequest_id > 0)
				{
					params.action = 'edit';
					params.DrugRequest_id = response_obj[0].next_DrugRequest_id;
					params.DrugRequestStatus_id = response_obj[0].next_DrugRequestStatus_id;
					params.DrugRequestPeriod_id = response_obj[0].next_DrugRequestPeriod_id;
					getWnd('swNewDrugRequestEditForm').show(params);
				}
				else
				{
					sw.swMsg.show(
					{
						icon: Ext.MessageBox.QUESTION,
						msg: lang['na_sleduyuschiy_period']+ (response_obj[0].next_DrugRequestPeriod) +lang['zayavka_po_vrachu']+ (response_obj[0].MedPersonal_Fin) +lang['ne_naydena_otkryit_imeyuschuyusya_da_ili_sozdat_net'],
						title: lang['vopros'],
						buttons: Ext.Msg.YESNOCANCEL,
						fn: function(buttonId, text, obj)
						{
							if ('yes' == buttonId)
							{
								if(response_obj[0].last_DrugRequest_id > 0)
								{
									params.action = 'edit';
									params.DrugRequest_id = response_obj[0].last_DrugRequest_id;
									params.DrugRequestStatus_id = response_obj[0].last_DrugRequestStatus_id;
									params.DrugRequestPeriod_id = response_obj[0].last_DrugRequestPeriod_id;
									getWnd('swNewDrugRequestEditForm').show(params);
								}
								else
								{
									Ext.Msg.alert(lang['soobschenie'], lang['u_vracha_net_ni_odnoy_zayavki_na_lekarstvennyie_sredstva']);
								}
							}
							if ('no' == buttonId)
							{
								params.action = 'add';
								params.DrugRequest_id = 0;
								params.DrugRequestStatus_id = 1;
								params.DrugRequestPeriod_id = response_obj[0].next_DrugRequestPeriod_id;
								getWnd('swNewDrugRequestEditForm').show(params);
							}
						}
					});
				}
			}.createDelegate(this),
			failure: function() 
			{
				this.getLoadMask().hide();
			}
		});
	},
	/*
	*	Открывает ЭМК при нажатии без записи
	*/
	createTtgAndOpenPersonEPHForm: function(pdata)
	{
/*
		Ext.Ajax.request({
			params: {LpuUnitType_SysNick: 'polka',Person_id: pdata.Person_id, LpuSection_id: getGlobalOptions().CurLpuSection_id, MedStaffFact_id: getGlobalOptions().CurMedStaffFact_id},
			callback: function(opt, success, response) {
				if ( success && response.responseText != '' ) {
					var response_obj = Ext.util.JSON.decode(response.responseText);
					if (response_obj.Error_Msg)
					{
						Ext.Msg.alert(lang['oshibka'], response_obj.Error_Msg);
						return false;
					}
					if (response_obj.TimetableGraf_id)
					{
						this.scheduleRefresh();
						// После создания бирки открываем ЭМК*/
						getWnd('swPersonEmkWindow').show(
						{
							Person_id: pdata.Person_id,
							Server_id: pdata.Server_id,
							PersonEvn_id: pdata.PersonEvn_id,
							MedStaffFact_id: getGlobalOptions().CurMedStaffFact_id,
							LpuSection_id: getGlobalOptions().CurLpuSection_id,
							//TimetableGraf_id: response_obj.TimetableGraf_id,
							mode: 'workplace',
							ARMType: this.ARMType,
							callback: function() 
							{
								if (this.mode == 'workplace')
								{
									getWnd('swMseWorkPlaceWindow').show({formMode:'open', ARMType: this.ARMType});
								}
							}
						});
/*
						return true;
					}
				}
				Ext.Msg.alert(lang['oshibka'], lang['oshibka_pri_sozdanii_birki_patsienta_bez_zapisi']);
				return false;
			}.createDelegate(this),
			url: '/?c=TimetableGraf&m=Create'
		});*/
	},
	scheduleNew: function()
	{
		var win = this;
		// Добавление пациента вне записи
		if (getWnd('swPersonSearchWindow').isVisible()) {
			sw.Msg.alert(lang['soobschenie'], lang['okno_poiska_cheloveka_uje_otkryito']);
			getWnd('swPersonSearchWindow').hide();
			return false;
		}
		getWnd('swPersonSearchWindow').show({
			onSelect: function(pdata) {
				if (pdata.Person_IsDead != 'true') {
					getWnd('swPersonSearchWindow').hide();
					getWnd('swProtocolMseEditForm').show({
						action: 'add',
						Person_id: pdata.Person_id,
						Server_id: pdata.Server_id,
						MedService_id: win.MedService_id,
						onHide: function() {
							win.scheduleRefresh();
						}
					});
				} else if (pdata.Person_IsDead == 'true') {
					sw.swMsg.alert(lang['oshibka'], lang['zapis_nevozmojna_v_svyazi_so_smertyu_patsienta']);
				}
			}.createDelegate(this),
			searchMode: 'all'
		});
	},
	
	scheduleAdd: function()
	{
		if (this.formMode=='send')
		{
			sw.swMsg.show(
			{
				icon: Ext.MessageBox.QUESTION,
				msg: lang['zapisat_patsienta_na_vyibrannoe_vremya'],
				title: lang['vopros'],
				buttons: Ext.Msg.YESNO,
				fn: function(buttonId, text, obj)
				{
					if ('yes' == buttonId)
					{
						var form = Ext.getCmp('swMseWorkPlaceWindow');
						
						var data = 
						{
							Person_id: form.saveParams.Person_id,
							Server_id: form.saveParams.Server_id,
							PersonEvn_id: form.saveParams.PersonEvn_id
						}
						form.scheduleSave(data);
					}
				}
			});
		}
		else 
		{
			if (getWnd('swPersonSearchWindow').isVisible())
			{
				Ext.Msg.alert(lang['soobschenie'], lang['okno_poiska_cheloveka_uje_otkryito']);
				//getWnd('swPersonSearchWindow').hide();
				return false;
			}
			
			getWnd('swPersonSearchWindow').show(
			{
				onClose: function() 
				{
					// do nothing
				},
				onSelect: function(pdata) 
				{
					if (pdata.Person_IsDead == 'false') {
						getWnd('swPersonSearchWindow').hide();
						Ext.getCmp('swMseWorkPlaceWindow').scheduleSave(pdata);
					} else {
						sw.swMsg.alert(lang['oshibka'], lang['zapis_nevozmojna_v_svyazi_so_smertyu_patsienta']);
					}
				},
				searchMode: 'all'
			});
		}
	},
	sheduleAddFromQueue: function() {
		var grid = this.getGrid();
		if (!grid || !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('TimetableGraf_id')) {
			Ext.Msg.alert(lang['oshibka'], lang['ne_vyibrana_zapis']);
			return false;
		}
		var record = grid.getSelectionModel().getSelected();
		getWnd('swMPQueueWindow').show({
			mode: 'select',
			params: record.data,
/*
			callback: function(data) {
				this.createTtgAndOpenPersonEPHForm(data);
			}.createDelegate(this),
*/
			onSelect: function(data) {
				getWnd('swMPQueueWindow').hide();
				Ext.getCmp('swMseWorkPlaceWindow').scheduleSave(data);
			}
		});
	},
	scheduleEdit: function()
	{
		alert(lang['redaktirovanie_birki']);
	},
	scheduleOpen: function()
	{
		var form = this,
			grid = this.getGrid(),
			record = grid.getSelectionModel().getSelected();
		if (!grid) {
			Ext.Msg.alert(lang['oshibka'], lang['spisok_raspisaniy_ne_nayden']);
			return false;
		}
		else if ( !record ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_vyibrana_zapis']);
			return false;
		}
		grid.fireEvent('rowdblclick', grid, grid.getStore().indexOf(record));
	},
	setAppointDT: function()
	{
		var form = this,
			grid = this.getGrid(),
			record = grid.getSelectionModel().getSelected();
		if (!grid) {
			Ext.Msg.alert(lang['oshibka'], lang['spisok_raspisaniy_ne_nayden']);
			return false;
		}
		else if ( !record || !record.get('EvnPrescrMse_id') ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_vyibrana_zapis']);
			return false;
		}

		getWnd('swMseSetAppointDTWindow').show({
			EvnPrescrMse_id: record.get('EvnPrescrMse_id'),
			callback: function() {
				grid.getStore().reload();
			}
		});
	},
	scheduleCopy: function()
	{
		alert('Ctrl+C');
	},
	schedulePaste: function()
	{
		alert('Ctrl+V');
	},
	scheduleDelete:function()
	{
		var form = this;
		var grid = form.getGrid();
		if (!grid)
		{
			Ext.Msg.alert(lang['oshibka'], lang['spisok_raspisaniy_ne_nayden']);
			return false;
		}
		else if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('TimetableGraf_id') )
		{
			Ext.Msg.alert(lang['oshibka'], lang['ne_vyibrana_zapis']);
			return false;
		}
		var id = grid.getSelectionModel().getSelected().data['TimetableGraf_id'];
		form.savePosition();
		if (grid.getSelectionModel().getSelected().data['EvnDirection_id'] != undefined
			&& grid.getSelectionModel().getSelected().data['EvnDirection_id'] != '' ) {
			getWnd('swSelectDirFailTypeWindow').show({LpuUnitType_SysNick: 'polka', time_id: id, onClear: function() {grid.getStore().reload();}});
		} else {
			sw.swMsg.show({
				icon: Ext.MessageBox.QUESTION,
				msg: lang['vyi_hotite_osvobodit_vremya_priema'],
				title: lang['vopros'],
				buttons: Ext.Msg.YESNO,
				fn: function(buttonId, text, obj)
				{
					if ('yes' == buttonId)
					{
						submitClearTime(
							{
								id: id,
								type: 'polka',
								DirFailType_id: null,
								EvnComment_Comment: null
							},
							function(response) {
								if (response.responseText)
								{
									var answer = Ext.util.JSON.decode(response.responseText);
									if (!answer.success)
									{
										if (answer.Error_Code)
										{
											Ext.Msg.alert(lang['oshibka_#']+answer.Error_Code, answer.Error_Message);
										}
										else
											if (!answer.Error_Msg) 
											{
												Ext.Msg.alert(lang['oshibka'], lang['pri_vyipolnenii_operatsii_osvobojdeniya_vremeni_priemaproizoshla_oshibka_osvobojdenie_priema_nevozmojno']);
											}
									}
									else
									{
										grid.getStore().reload();
									}
								}
								else
								{
									Ext.Msg.alert(lang['oshibka'], lang['pri_vyipolnenii_operatsii_osvobojdeniya_vremeni_priemaproizoshla_oshibka_otsutstvuet_otvet_servera']);
								}
							}.createDelegate(this),
							null
						);
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
		}
		
	},
	talonDelete:function()
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
	scheduleRefresh:function()
	{
		var params = this.TopPanel.getForm().getValues();
		params.begDate = Ext.util.Format.date(this.dateMenu.getValue1(), 'd.m.Y');
		params.endDate = Ext.util.Format.date(this.dateMenu.getValue2(), 'd.m.Y');
		//params.MedStaffFact_id = getGlobalOptions().CurMedStaffFact_id;
		params.MedService_id = this.MedService_id;
		this.getGrid().loadStore(params);
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
	countOnGroup: function(start, count)
	{
		var result = 0;
		
		for (i=start; i < start+count; i++)
		{
			if (this.gridStore.data.items[i].get('Person_id')>0)
				result++;
		}
		/*
		this.gridStore.each(function(record) 
		{
			log(record);
			if (record.get('Person_id')>0)
				result++;
		});
		*/
		//log(this.gridStore);
		return result;
	},
	listeners:
	{
		beforeshow: function()
		{
			//log(getGlobalOptions().medstafffact);
			if ((!getGlobalOptions().medstafffact) || (getGlobalOptions().medstafffact.length==0))
			{
				Ext.Msg.alert(lang['soobschenie'], lang['tekuschiy_login_ne_sootnesen_s_vrachom_dostup_k_interfeysu_vracha_nevozmojen']);
				return false;
			}
		}
	},
	show: function()
	{
		sw.Promed.swMseWorkPlaceWindow.superclass.show.apply(this, arguments);
		
		if ( !arguments[0] || !arguments[0].MedService_id ) { 
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() { this.hide(); }.createDelegate(this) );
			return false;
		}
		
		this.MedService_id = arguments[0].MedService_id;
		this.MedService_Name = arguments[0].MedService_Name;
		
		this.TopPanel.getForm().reset();

		// Форма может открываться с разных мест, поэтому если она откывается для того, чтобы записать пациента к другому врачу
		// то предварительно надо запомнить параметры.

		//this.setTitle('Рабочее место МСЭ' + ' (' + this.MedService_Name + ' / ' + getGlobalOptions().CurMedPersonal_FIO + ')');
        sw.Promed.MedStaffFactByUser.setMenuTitle(this, arguments[0]);//ref. 21540
		// Создаем свой заголовок, единый для всех армов, на основании данных пришедших с сервера ( из User_model)
		//sw.Promed.MedStaffFactByUser.setMenuTitle(this, {MedService_id: this.MedService_id, MedService_Name: this.MedService_Name, MedPersonal_id: getGlobalOptions().CurMedPersonal_id});
		
		if ((arguments[0]) && (arguments[0].formMode) && (arguments[0].formMode == 'open')) {
			// Да просто активация
			// this.formMode = 'open';
			this.scheduleRefresh();
			// если formMode был = send, то все вертаем назад на сохраненные значения 
		} else {
			// Очистка грида 
			this.getGrid().clearStore();
			// Медперсонал
			this.MedPersonalPanel.findById('msewpMedPersonal_id').getStore().clearFilter();
			// При открытии формы сначала получаем текущую дату с сервера, затем получаем список записанных на текущую дату
			this.getCurrentDateTime();
		}
	
		if ((this.formMode = 'send') && (this.saveParams)) {
			this.getGrid().clearStore();
			this.curDate = this.saveParams.curDate;
			this.begTime = this.saveParams.begTime;
			this.dateMenu.setValue(Ext.util.Format.date(this.saveParams.selectBegDate, 'd.m.Y')+' - '+Ext.util.Format.date(this.saveParams.selectEndDate, 'd.m.Y'));
			this.Person_id = this.saveParams.Person_id;
			this.Server_id = this.saveParams.Server_id;
			this.PersonEvn_id = this.saveParams.PersonEvn_id;
			this.mode = this.saveParams.mode;
			//this.MedPersonalPanel.findById('msewpLpu_id').setValue('');
			this.MedPersonalPanel.findById('msewpMedPersonal_id').setValue('');
			delete(this.saveParams);
			this.scheduleLoad(this.mode);
		}
		this.formMode = 'open';
		this.TopPanel.setVisible(true);
		this.findById('msewpTopPanelFieldset').collapse();
		this.MedPersonalPanel.hide();
		//this.gridActions.create.show();
		//this.setActionDisabled('create',true);
		this.gridActions.open.show();
		this.setActionDisabled('open',false);
				
		//this.findById('msewpSchedulePanel').syncSize();
	
		for ( btnAction in this.BtnActions ) {
			if ( typeof this.BtnActions[btnAction] == 'object' ) {
				/*if ( this.BtnActions[btnAction].nn.inlist([ 'action_UslugaComplexTree' ]) ) {
					this.BtnActions[btnAction].hide();
				}
				else {*/
					this.BtnActions[btnAction].show();
				//}
			}
		}

		// Переключатель
		this.syncSize();
	},
	
	openEmk: function()
	{
		var win = this;
		var grid = this.getGrid();
		var record = grid.getSelectionModel().getSelected();
		if(!record) return false;
		getWnd('swPersonEmkWindow').show({
			Person_id: record.get('Person_id'),
			Server_id: record.get('Server_id'),
			PersonEvn_id: record.get('PersonEvn_id'),
			mode: 'workplace',
			ARMType: win.ARMType,
			callback: function() {
				
			}
		});
	},
	
	openEvnMse: function()
	{
		var win = this;
		var grid = this.getGrid();
		var record = grid.getSelectionModel().getSelected();
		if(!record) return false;
		getWnd('swProtocolMseEditForm').show({
			Person_id: record.get('Person_id'),
			Server_id: record.get('Server_id'),
			EvnMse_id: record.get('EvnMse_id'),
			EvnPrescrMse_id: record.get('EvnPrescrMse_id'),
			onHide: function() {
				win.scheduleRefresh();
			}
		});
	},
	
	setTitleFieldSet: function()
	{
		var fset = this.TopPanel.find('xtype', 'fieldset')[0],
			isfilter = false,
			title = lang['poisk_filtr'];
		
		fset.findBy(function(f) {
			if( f.xtype && f.xtype.inlist(['textfieldpmw', 'combo', 'swlpulocalcombo', 'swdatefield', 'swsnilsfield']) ) {
				if( !f.getValue().toString().inlist(['', '3']) && f.getValue() != null ) {
					isfilter = true;
				}
			}
		});
		
		fset.setTitle( title + ( isfilter == true ? '' : 'не ' ) + 'установлен' );
	},

	initComponent: function()
	{
		var win = this;

		// Actions
		var Actions =
		[
			{name:'open_emk', text:langs('Открыть ЭМК'), hidden: false, tooltip: langs('Открыть электронную медицинскую карту пациента'), iconCls : 'open16', handler: function() { this.openEmk(); }.createDelegate(this)},
			{name:'open_evnmse', text: 'Обратный талон', tooltip: 'Обратный талон', handler: function(){ this.openEvnMse(); }.createDelegate(this) },
			{name:'open', text: 'Открыть направление', tooltip: 'Открыть направление', iconCls : 'x-btn-text', icon: 'img/icons/open16.png', handler: function() {this.scheduleOpen()}.createDelegate(this)},
			{name:'create', text:langs('Без записи'), tooltip: langs('Пациент без записи'), iconCls : 'x-btn-text', icon: 'img/icons/add16.png', handler: function() {this.scheduleNew()}.createDelegate(this)},
			//{name:'add', hidden: true, text:langs('Записать пациента'), tooltip: langs('Записать пациента'), iconCls : 'x-btn-text', icon: 'img/icons/add16.png', handler: function() {this.scheduleAdd()}.createDelegate(this)},
			//{name:'queue', hidden: true, text:langs('Записать из очереди'), tooltip: langs('Записать из очереди'), iconCls : 'x-btn-text', icon: 'img/icons/add16.png', handler: function() {this.sheduleAddFromQueue()}.createDelegate(this)},
			{name:'edit', text:BTN_GRIDEDIT, tooltip: BTN_GRIDEDIT, iconCls : 'x-btn-text', icon: 'img/icons/edit16.png', handler: function() {this.scheduleEdit()}.createDelegate(this)},
			{name:'copy', text:BTN_GRIDCOPY, tooltip: BTN_GRIDCOPY, hidden: true, iconCls : 'x-btn-text', icon: 'img/icons/copy16.png', handler: function() {this.scheduleCopy()}.createDelegate(this)},
			{name:'paste', text:BTN_GRIDPASTE, tooltip: BTN_GRIDPASTE, hidden: true, iconCls : 'x-btn-text', icon: 'img/icons/paste16.png', handler: function() {this.schedulePaste()}.createDelegate(this)},
			//{name:'del', true, text:langs('Освободить запись'), tooltip: langs('Снять запись пациента'), iconCls : 'x-btn-text', icon: 'img/icons/delete16.png', handler: function() {this.scheduleDelete()}.createDelegate(this)},
			{name:'nazn', text: 'Назначить время', tooltip: 'Назначить время', iconCls : 'x-btn-text', icon: 'img/icons/edit16.png', handler: function() {this.setAppointDT()}.createDelegate(this)},
			{name:'refresh', text:BTN_GRIDREFR, tooltip: BTN_GRIDREFR, iconCls : 'x-btn-text', icon: 'img/icons/refresh16.png', handler: function() {this.scheduleRefresh()}.createDelegate(this)},
			{name:'actions', key: 'actions', text:lang['deystviya'], menu: [
				new Ext.Action({name:'collapse_all', text:lang['svernut_vse'], tooltip: lang['svernut_vse'], handler: function() {this.scheduleCollapseDates()}.createDelegate(this)}),
				new Ext.Action({name:'expand_all', text:lang['razvernut_vse'], tooltip: lang['razvernut_vse'], handler: function() {this.scheduleExpandDates()}.createDelegate(this)})
			], tooltip: lang['deystviya'], iconCls : 'x-btn-text', icon: 'img/icons/actions16.png', handler: function() {}},
			{name:'sign_actions', key: 'sign_actions', hidden: getRegionNick() == 'kz', text:langs('Подписать'), menu: [
					new Ext.Action({
						name: 'action_signEvnMse',
						text: langs('Подписать обратный талон'),
						tooltip: langs('Подписать обратный талон'),
						handler: function() {
							var me = this;
							var rec = win.getGrid().getSelectionModel().getSelected();
							if (rec && rec.get('EvnMse_id')) {
								getWnd('swEMDSignWindow').show({
									EMDRegistry_ObjectName: 'EvnMse',
									EMDRegistry_ObjectID: rec.get('EvnMse_id'),
									callback: function(data) {
										if (data.preloader) {
											me.disable();
										}

										if (data.success || data.error) {
											me.enable();
										}
									}
								});
							}
						}
					}),
					new Ext.Action({
						name: 'action_showEvnMseVersionList',
						text: langs('Версии документа «Обратный талон»'),
						tooltip: langs('Версии документа «Обратный талон»'),
						handler: function() {
							var rec = win.getGrid().getSelectionModel().getSelected();
							if (rec && rec.get('EvnMse_id')) {
								getWnd('swEMDVersionViewWindow').show({
									EMDRegistry_ObjectName: 'EvnMse',
									EMDRegistry_ObjectID: rec.get('EvnMse_id')
								});
							}
						}
					})
			], tooltip: langs('Подписать'), iconCls : 'x-btn-text', icon: 'img/icons/digital-sign16.png', handler: function() {}},
			{name:'print', text:BTN_GRIDPRINT, tooltip: BTN_GRIDPRINT, iconCls : 'x-btn-text', icon: 'img/icons/print16.png', handler: function() {this.schedulePrint()}.createDelegate(this)},
			{name:'del_talon', text:lang['udalit_obratnyiy_talon'], tooltip: lang['udalit_obratnyiy_talon'], iconCls : 'x-btn-text', icon: 'img/icons/delete16.png', handler: function() {this.talonDelete()}.createDelegate(this)}
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
			id: 'msewpToolbar',
			items:
			[
				this.gridActions.open_emk,
				this.gridActions.open_evnmse,
				this.gridActions.open,
				this.gridActions.del_talon,
				this.gridActions.create,
				//this.gridActions.add,
				//this.gridActions.queue,
				//this.gridActions.edit,
				this.gridActions.copy,
				this.gridActions.paste,
				//this.gridActions.del,
				this.gridActions.nazn,
				{
					xtype : "tbseparator"
				},
				this.gridActions.refresh,
				{
					xtype : "tbseparator"
				},
				this.gridActions.print,
				{
					xtype : "tbseparator"
				},
				this.gridActions.actions,
				{
					xtype : "tbseparator"
				},
				this.gridActions.sign_actions,
				{
					xtype : "tbseparator"
				},
				{
					xtype : "tbfill"
				},
				{
					xtype : "tbseparator"
				},
				{
					text: '0 / 0',
					xtype: 'tbtext'
				}
			]
		});		
		
		this.reader = new Ext.data.JsonReader(
		{
			id: 'EvnPrescrMse_id'
		},
		[{
			name: 'Person_id'
		}, {
			name: 'Server_id'
		}, {
			name: 'EvnVK_id'
		}, {
			name: 'EvnMse_id'
		}, {
			name: 'EvnPrescrMse_id'
		}, {
			name: 'EvnPrescrMse_setDT'
		}, {
			name: 'EvnPrescrMse_setDate'
		}, {
			name: 'EvnPrescrMse_issueDT'
		}, {
			name: 'EvnPrescrMse_setTime'
		}, {
			name: 'EvnStatus_Name'
		}, {
			name: 'EvnStatus_SysNick'
		}, {
			name: 'EvnPrescrMse_IsFirstTime'
		}, {
			name: 'MseDirectionAimType_Name'
		}, {
			name: 'EvnDirection_From'
		}, {
			name: 'Diag_Name'
		}, {
			name: 'Person_Fio'
		}, {
			name: 'Person_BirthDay'
		}, {
			name: 'Lpu_Nick'
		}, {
			name: 'EvnPrescrMse_appointDT'
		}, {
			name: 'EvnMse'
		}, {
			name: 'EvnMse_Sign'
		}, {
			name: 'Signatures_id'
		}, {
			name: 'EvnMse_setDT'
		}, {
			name: 'DiagMse_Name'
		}, {
			name: 'InvalidGroupType_Name'
		}, {
			name: 'EvnMse_ReExamDate'
		}]);
        
		this.gridStore = new Ext.data.GroupingStore(
		{
			reader: this.reader,
			autoLoad: false,
			url: '/?c=Mse&m=loadEvnPrescrMseGrid',
			sortInfo: 
			{
				field: 'EvnPrescrMse_setDT',
				direction: 'ASC'
			},
			groupField: 'EvnPrescrMse_setDT',
			listeners:
			{
				load: function(store, record, options)
				{
					callback:
					{
						var count = store.getCount();
						var form = Ext.getCmp('swMseWorkPlaceWindow');
						var grid = form.ScheduleGrid;
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
								/*if (!form.gridActions.add.initialConfig.initialDisabled)
									form.gridActions.add.setDisabled(false);
								if (!form.gridActions.queue.initialConfig.initialDisabled)
									form.gridActions.queue.setDisabled(false);*/
								if (!form.gridActions.edit.initialConfig.initialDisabled)
									form.gridActions.edit.setDisabled(false);
								//if (!form.gridActions.del.initialConfig.initialDisabled)
								//	form.gridActions.del.setDisabled(false);
								if (!form.gridActions.copy.initialConfig.initialDisabled)
									form.gridActions.copy.setDisabled(false);
								if (!form.gridActions.refresh.initialConfig.initialDisabled)
									form.gridActions.refresh.setDisabled(false);
								if (!form.gridActions.del_talon.initialConfig.initialDisabled)
									form.gridActions.del_talon.setDisabled(true);
							}
							//form.restorePosition();
							//grid.focus();
						}
						else
						{
							grid.focus();
						}
					}
				},
				clear: function()
				{
					var form = Ext.getCmp('swMseWorkPlaceWindow');
					form.gridActions.open.setDisabled(true);
					//form.gridActions.create.setDisabled(true);
					//form.gridActions.add.setDisabled(true);
					//form.gridActions.queue.setDisabled(true);
					form.gridActions.edit.setDisabled(true);
					form.gridActions.copy.setDisabled(true);
					//form.gridActions.del.setDisabled(true);
					form.gridActions.refresh.setDisabled(true);
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
			var form = Ext.getCmp('swMseWorkPlaceWindow');
			if (e.getKey() == Ext.EventObject.ENTER)
			{
				e.stopEvent();
				form.scheduleLoad('period');
			}
		});
		this.dateMenu.addListener('select',function () 
		{
			// Читаем расписание за период
			var form = Ext.getCmp('swMseWorkPlaceWindow');
			form.scheduleLoad('period');
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
				this.scheduleLoad('range');
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
				this.scheduleLoad('range');
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
				this.scheduleLoad('day');
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
				this.scheduleLoad('week');
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
				this.scheduleLoad('month');
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
				this.scheduleLoad('range');
			}.createDelegate(this)
		});
		/*
		this.dateTpl = new Ext.Template(['<span style="font-weight: bold; font-size: 16px">{period}</span>']);
		
		this.dateText = new Ext.Toolbar.TextItem(
		{
			tpl: this.dateTpl
		});
		*/
		this.DoctorToolbar = new Ext.Toolbar(
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

		this.TopPanel = new Ext.form.FormPanel(
		{
			region: 'north',
			frame: true,
			border: false,
			//height: 150,
			autoHeight: true,
			tbar: this.DoctorToolbar,
			items: 
			[{
				layout: 'form',
				labelAlign: 'right',
				labelWidth: 50,
				items: 
				[{
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
					id: 'msewpTopPanelFieldset',
					title: lang['poisk_filtr_ne_ustanovlen'],
					layout: 'form',
					items: 
					[
						{
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
											'keydown': function (inp, e) {
												var form = Ext.getCmp('swMseWorkPlaceWindow');
												if (e.getKey() == Ext.EventObject.ENTER) {
													e.stopEvent();
													form.setTitleFieldSet();
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
											'keydown': function (inp, e) {
												var form = Ext.getCmp('swMseWorkPlaceWindow');
												if (e.getKey() == Ext.EventObject.ENTER) {
													e.stopEvent();
													form.setTitleFieldSet();
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
											'keydown': function (inp, e) {
												var form = Ext.getCmp('swMseWorkPlaceWindow');
												if (e.getKey() == Ext.EventObject.ENTER) {
													e.stopEvent();
													form.setTitleFieldSet();
													form.scheduleLoad();
												}
											}
										}
									},
									{
										xtype: 'swdatefield',
										format: 'd.m.Y',
										plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
										name: 'Person_BirthDay',
										id: 'msewpSearch_BirthDay',
										fieldLabel: lang['dr'],
										listeners: {
											'keydown': function (inp, e) {
												var form = Ext.getCmp('swMseWorkPlaceWindow');
												if (e.getKey() == Ext.EventObject.ENTER) {
													e.stopEvent();
													form.setTitleFieldSet();
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
										xtype: 'numberfield',
										anchor: '100%',
										hiddenName: 'EvnPrescrMse_Num', // такого поля нет в БД, пока не действует
										allowBlank: true,
										fieldLabel: langs('№ направления')
									},{
										xtype: 'swlpulocalcombo',
										anchor: '100%',
										hiddenName: 'Lpu_id',
										allowBlank: true,
										fieldLabel: lang['mo_prikrepleniya']
									},{
										layout: 'form',
										//hidden: getRegionNick() != 'perm',
										items: [{
											xtype: 'combo',
											mode: 'local',
											hiddenName: 'MSEDirStatus_id',
											displayField: 'MSEDirStatus_Text',
											valueField: 'MSEDirStatus_id',
											tpl: new Ext.XTemplate(
												'<tpl for="."><div class="x-combo-list-item">',
												'{MSEDirStatus_Text}&nbsp;',
												'</div></tpl>'
											),
											store: new Ext.data.SimpleStore({
												key: '',
												autoLoad: true,
												fields: [
													{name:'MSEDirStatus_id', type:'int'},
													{name:'MSEDirStatus_Text', type:'string'}
												],
												data: [[1, 'Все'], [2, 'Отправлено'], [3, 'Доработка в МО'], [4, 'Принято'], [5, 'Выполнено']]
											}),
											listeners: {
												render: function(combo){
													combo.setValue(2);
												}
											},
											triggerAction: 'all',
											editable: false,
											anchor: '100%',
											fieldLabel: 'Статус напр-я на МСЭ'
										}]
									},{
										xtype: 'combo',
										mode: 'local',
										hiddenName: 'isEvnMse',
										displayField: 'isEvnMse_Text',
										valueField: 'isEvnMse_id',
										tpl: new Ext.XTemplate(
											'<tpl for="."><div class="x-combo-list-item">',
											'{isEvnMse_Text}&nbsp;',
											'</div></tpl>'
										),
										store: new Ext.data.SimpleStore({
											key: '',
											autoLoad: true,
											fields: [
												{name:'isEvnMse_id', type:'int'},
												{name:'isEvnMse_Text', type:'string'}
											],
											data: [[1, lang['sozdan']], [2, lang['ne_sozdan']], [3, lang['vse']]]
										}),
										listeners: {
											render: function(combo){
												combo.setValue(3);
											}
										},
										triggerAction: 'all',
										editable: false,
										anchor: '100%',
										fieldLabel: lang['obratnyiy_talon']
									}, 
									{
										fieldLabel: 'СНИЛС',
										name: 'Person_Snils',
										xtype: 'swsnilsfield',
										hidden: getRegionNick() != 'ufa',
										readOnly: true
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
											form.setTitleFieldSet();
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
											form.setTitleFieldSet();
											this.scheduleLoad();
										}.createDelegate(this)
									}]
								}
							]
						}
					]
				}]
			}]
		})
	
		this.MedPersonalPanel = new Ext.Panel(
		{
			region: 'north',
			border: false,
			frame: true,
			autoHeight: true,
			items: 
			[{
				xtype: 'form',
				labelAlign: 'right',
				labelWidth: 50,
				items: 
				[{
					xtype: 'fieldset',
					height: 60,
					title: lang['lpu'],
					layout: 'column',
					items: 
					[{
						layout: 'form',
						columnWidth: .3,
						items: 
						[{
							fieldLabel: lang['lpu'],
							anchor:'100%',
							hiddenName: 'Lpu_id',
							id: 'msewpLpu_id',
							lastQuery: '',
							width : 300,
							xtype: 'swlpulocalcombo',
							listeners: 
							{
								change: function(combo, nv, ov)
								{
									var form = Ext.getCmp('swMseWorkPlaceWindow');
									form.MedPersonalPanel.findById('msewpMedPersonal_id').getStore().load(
									{
										params:
										{
											Lpu_id: nv
										},
										callback: function()
										{
											form.MedPersonalPanel.findById('msewpMedPersonal_id').setValue('');
										}
									});
								}
							}
						}]
					}, 
					{
						layout: 'form',
						columnWidth: .35,
						items: 
						[{
							fieldLabel: lang['vrach'],
							anchor:'100%',
							hiddenName: 'MedPersonal_id',
							id: 'msewpMedPersonal_id',
							lastQuery: '',
							listWidth: 420,
							editable: true,
							xtype: 'swmedpersonalcombo'
						}]
					}, 
					{
						layout: 'form',
						items: 
						[{
							style: "padding-left: 20px",
							xtype: 'button',
							id: 'msewpBtnMPSearch',
							text: lang['pokazat_raspisanie'],
							iconCls: 'search16',
							handler: function()
							{
								var form = Ext.getCmp('swMseWorkPlaceWindow');
								form.scheduleLoad();
							}
						}]
					}, 
					{
						layout: 'form',
						items: 
						[{
							style: "padding-left: 20px",
							xtype: 'button',
							id: 'msewpBtnMPClose',
							text: lang['vernutsya_k_patsientu'],
							iconCls: 'close16',
							handler: function()
							{
								var form = Ext.getCmp('swMseWorkPlaceWindow');
								if (getWnd('swPersonEmkWindow').isVisible())
									getWnd('swPersonEmkWindow').toFront();
								else
								{
									if (form.saveParams)
									{
										getWnd('swPersonEmkWindow').show(
										{
											Person_id: form.saveParams.Person_id,
											Server_id: form.saveParams.Server_id,
											PersonEvn_id: form.saveParams.PersonEvn_id,
											mode: 'workplace',
											ARMType: sw.Promed.swMseWorkPlaceWindow.ARMType,
											callback: function() 
											{
												if (this.mode == 'workplace')
												{
													// Открываем форму и если formMode был = send, то все вертаем назад на сохраненные значения 
													getWnd('swMseWorkPlaceWindow').show({formMode:'open', ARMType: sw.Promed.swMseWorkPlaceWindow.ARMType});
												}
											}
										});
									}
									else 
									{
										getWnd('swMseWorkPlaceWindow').show({formMode:'open'});
									}
								}
							}
						}]
					}]
				}]
			}]
		})
		
		this.ScheduleGrid = new Ext.grid.GridPanel(
		{
			region: 'center',
			layout: 'fit',
			frame: true,
			tbar: this.gridToolbar,
			store: this.gridStore,
			loadMask: true,
			stripeRows: true,
			enableColumnHide: false,
			columns:
			[{
				key: true,
				hidden: true,
				dataIndex: 'EvnPrescrMse_id'
			},
			{
				hidden: true,
				dataIndex: 'Server_id'
			},
			{
				hidden: true,
				dataIndex: 'EvnVK_id'
			},
			{
				hidden: true,
				dataIndex: 'EvnMse_id'
			},
			{
				header: lang['data_napravleniya'],
				width: 60,
				hidden: true,
				sortable: true,
				dataIndex: 'EvnPrescrMse_setDT'
			}, {
				header: lang['zapis'],
				hidden: getRegionNick() == 'perm',
				width: 40,
				sortable: true,
				dataIndex: 'EvnPrescrMse_setTime'
			}, {
				header: 'Дата направления',
				width: 80,
				sortable: true,
				dataIndex: 'EvnPrescrMse_issueDT'
			}, {
				header: 'Статус направления',
				width: 80,
				sortable: true,
				dataIndex: 'EvnStatus_Name'
			}, {
				header: lang['napravlyaetsya'],
				width: 50,
				sortable: true,
				dataIndex: 'EvnPrescrMse_IsFirstTime'
			},
			{
				header: lang['tsel_napravleniya'],
				width: 100,
				sortable: true,
				dataIndex: 'MseDirectionAimType_Name'
			}, {
				header: 'Кем направлен',
				width: 120,
				sortable: true,
				dataIndex: 'EvnDirection_From'
			}, {
				header: lang['diagnoz_osnovnoy'],
				width: 100,
				sortable: true,
				dataIndex: 'Diag_Name'
			},
			{
				header: langs('№ направления'),
				width: 60,
				sortable: true,
				hidden: getRegionNick() != 'kz',
				dataIndex: 'EvnPrescrMse_setDate'
			},
			{
				header: langs('ИД пациента'),
				width: 80,
				sortable: true,
				hidden: !getGlobalOptions().use_depersonalized_expertise,
				dataIndex: 'Person_id'
			},
			{
				header: langs('ФИО Пациента'),
				width: 120,
				sortable: true,
				hidden: getGlobalOptions().use_depersonalized_expertise,
				dataIndex: 'Person_Fio'
			},
			{
				header: lang['data_rojdeniya'],
				width: 60,
				sortable: true,
				hidden: getGlobalOptions().use_depersonalized_expertise,
				dataIndex: 'Person_BirthDay'
			},
			{
				header: lang['mo_prikrepleniya'],
				width: 70,
				sortable: true,
				dataIndex: 'Lpu_Nick'
			}, {
				header: 'Дата и время назначения МСЭ',
				width: 100,
				sortable: true,
				dataIndex: 'EvnPrescrMse_appointDT'
			}, {
				header: lang['obratnyiy_talon'],
				width: 80,
				sortable: true,
				dataIndex: 'EvnMse',
				renderer: function(v, p, rec){
					if(v!='')
						return '<a href="javascript:">'+v+'</a>';
					else
						return '';
				}
			}, {
				header: langs('Обратный талон подписан'),
				width: 180,
				sortable: true,
				dataIndex: 'EvnMse_Sign'
			},
			{
				header: lang['data_osvidet'],
				hidden: getRegionNick() == 'perm',
				width: 60,
				sortable: true,
				dataIndex: 'EvnMse_setDT'
			},
			{
				header: lang['diagnoz_mse'],
				//width: 7,
				sortable: true,
				dataIndex: 'DiagMse_Name'
			},
			{
				header: lang['ustanovlena_invalidnost'],
				//width: 7,
				sortable: true,
				dataIndex: 'InvalidGroupType_Name'
			},
			{
				header: lang['data_pereosvidet'],
				width: 60,
				sortable: true,
				dataIndex: 'EvnMse_ReExamDate'
			}],
			
			view: new Ext.grid.GroupingView(
			{
				forceFit: true,
                enableGroupingMenu:false,
				groupTextTpl: '{[values.group]} ({[values.rs.length]} {[values.rs.length.inlist([2,3,4]) ? "записи" : "записей"]})'
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
						var form = Ext.getCmp('swMseWorkPlaceWindow');
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
						if (!form.readOnly)
						{
							if (record.get('EvnStatus_SysNick') && record.get('EvnStatus_SysNick') == 'Sended') {
								if (!form.gridActions.nazn.initialConfig.initialDisabled)
									form.gridActions.nazn.setDisabled(false);
							} else {
								if (!form.gridActions.nazn.initialConfig.initialDisabled)
									form.gridActions.nazn.setDisabled(true);
							}

							form.gridActions.sign_actions.setDisabled(Ext.isEmpty(record.get('EvnMse_id')));

							if ((record.get('Person_id')==null) || (record.get('Person_id')=='')) {
								var TimetableGraf_Date = record.get('TimetableGraf_Date');
								var current_date = Date.parseDate(form.curDate, 'd.m.Y');
								if (!form.gridActions.open.initialConfig.initialDisabled)
									form.gridActions.open.setDisabled(true);
								if (!form.gridActions.create.initialConfig.initialDisabled)
									// блокируем кнопку "Без записи" в предыдущих днях, т.к. запись все равно происходит на текущий день
									form.gridActions.create.setDisabled(current_date > TimetableGraf_Date);
								/*if (!form.gridActions.add.initialConfig.initialDisabled)
									// запрещаем запись пациента на прошедшую дату
									form.gridActions.add.setDisabled(current_date > TimetableGraf_Date);*/
								/*if (!form.gridActions.queue.initialConfig.initialDisabled)
									// запрещаем запись из очереди на прошедшую дату
									form.gridActions.queue.setDisabled(current_date > TimetableGraf_Date);*/
								if (!form.gridActions.edit.initialConfig.initialDisabled)
									form.gridActions.edit.setDisabled(true);
								if (!form.gridActions.copy.initialConfig.initialDisabled)
									form.gridActions.copy.setDisabled(true);
								if (!form.gridActions.paste.initialConfig.initialDisabled)
									form.gridActions.paste.setDisabled(true);
								/*if (!form.gridActions.del.initialConfig.initialDisabled)
									form.gridActions.del.setDisabled(true);*/
								if (!form.gridActions.del_talon.initialConfig.initialDisabled)
									form.gridActions.del_talon.setDisabled(record.get('EvnMse_id')==null);
							} else {
								if (!form.gridActions.open.initialConfig.initialDisabled)
									form.gridActions.open.setDisabled(false);
								if (!form.gridActions.create.initialConfig.initialDisabled)
									form.gridActions.create.setDisabled(false);
								/*if (!form.gridActions.add.initialConfig.initialDisabled)
									form.gridActions.add.setDisabled(true);*/
								/*if (!form.gridActions.queue.initialConfig.initialDisabled)
									form.gridActions.queue.setDisabled(true);*/
								if (!form.gridActions.edit.initialConfig.initialDisabled)
									form.gridActions.edit.setDisabled(false);
								if (!form.gridActions.copy.initialConfig.initialDisabled)
									form.gridActions.copy.setDisabled(false);
								if (!form.gridActions.paste.initialConfig.initialDisabled)
									form.gridActions.paste.setDisabled(false);
								if (!form.gridActions.del_talon.initialConfig.initialDisabled)
									form.gridActions.del_talon.setDisabled(record.get('EvnMse_id')==null);
								//form.gridActions.del.setDisabled(false);
								// (!record.get('MedStaffFact_id').inlist(getGlobalOptions().medstafffact)) || 
								
								var TimetableGraf_Date = record.get('TimetableGraf_Date');
								var current_date = Date.parseDate(form.curDate, 'd.m.Y');
								/*if (!form.gridActions.del.initialConfig.initialDisabled)
									form.gridActions.del.setDisabled( // Disabled where
										(!isAdmin) // this user
										&& (
										(record.get('pmUser_updId') != getGlobalOptions().pmuser_id) // this other autor of record
										|| (current_date > TimetableGraf_Date) 
										|| (current_date.format('d.m.Y') == TimetableGraf_Date.format('d.m.Y') && record.get('Person_IsEvents') == 'true') // in current day opened TAP
										)
									);
								*/
							}
						}
					},
					'rowdeselect': function(sm, rowIdx, record)
					{
						//
					}
				}
			})
		});
		
		// Добавляем созданное popup-меню к гриду
		this.ScheduleGrid.addListener('rowcontextmenu', onMessageContextMenu,this);
		this.ScheduleGrid.on('rowcontextmenu', function(grid, rowIndex, event)
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
		
		
		this.ScheduleGrid.on('rowdblclick', function(grid, row)
		{
			var win = this;
			var rec = grid.getSelectionModel().getSelected();
			if(!rec) return false;
			if(rec.get('EvnPrescrMse_id') != null){
				getWnd('swDirectionOnMseEditForm').show({
					ARMType: this.ARMType,
					action: 'view',
					Person_id: rec.get('Person_id'),
					Server_id: rec.get('Server_id'),
					EvnVK_id: rec.get('EvnVK_id'),
					EvnPrescrMse_id: rec.get('EvnPrescrMse_id'),
					onClose: function() {
						win.scheduleRefresh();
					}
				});
			}
		}.createDelegate(this));
		
		this.ScheduleGrid.on('celldblclick', function(grid, row, col, object)
		{
			
		});
		
		this.ScheduleGrid.on('cellclick', function(grid, rowIdx, colIdx) {
			var flag_idx = grid.getColumnModel().findColumnIndex('EvnMse');
			var rec = grid.getSelectionModel().getSelected();
			if(!rec || rec.get('EvnMse_id') == null) return false;
			if(colIdx == flag_idx){
				getWnd('swProtocolMseEditForm').show({
					Person_id: rec.get('Person_id'),
					Server_id: rec.get('Server_id'),
					EvnPrescrMse_id: rec.get('EvnPrescrMse_id'),
					EvnMse_id: rec.get('EvnMse_id'),
					action: 'edit',
					onHide: function(){this.scheduleRefresh();}.createDelegate(this)
				});
			}
		}.createDelegate(this));
		
		
		// Добавляем события на keydown
		this.ScheduleGrid.on('keydown', function(e)
		{
			var win = Ext.getCmp('swMseWorkPlaceWindow');
			var grid = win.getGrid();
			if (e.getKey().inlist([e.INSERT, e.F4, e.F5, e.ENTER, e.DELETE, e.END, e.HOME, e.PAGE_DOWN, e.PAGE_UP, e.TAB])
				|| (grid.hasPersonData() && e.getKey().inlist([e.F6, e.F10, e.F11, e.F12]) && !getGlobalOptions().use_depersonalized_expertise)
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
			if (grid.hasPersonData() && !getGlobalOptions().use_depersonalized_expertise)
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
						var o = win.findById('msewpBtnClear');
					}
					else
					{
						//var o = win.findById('msewpSearch_FIO');
						var o = win.findById('msewpSearch_SurName');
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
		var configActions = 
		{
			action_reports:
			{
				nn: 'action_Report',
				tooltip: lang['prosmotr_otchetov'],
				text: lang['prosmotr_otchetov'],
				iconCls: 'report32',
				//hidden: !this.enableDefaultActions,//( !document.getElementById('swWorkPlaceCallCenterWindow') || !this.enableDefaultActions ),
				handler: function() {
					if (sw.codeInfo.loadEngineReports)
					{
						getWnd('swReportEndUserWindow').show();
					}
					else
					{
						getWnd('reports').load(
							{
								callback: function(success)
								{
									sw.codeInfo.loadEngineReports = success;
									// здесь можно проверять только успешную загрузку
									getWnd('swReportEndUserWindow').show();
								}
							});
					}
				}
			},
			action_Timetable:
			{
				nn: 'action_Timetable',
				tooltip: lang['rabota_s_raspisaniem_mse'],
				text: lang['raspisanie_mse'],
				iconCls : 'mp-timetable32',
				disabled: false,
				hidden: getRegionNick() == 'perm',
				handler: function() {
					getWnd('swTTMSScheduleEditWindow').show({
						MedService_id: this.MedService_id,
						MedService_Name: this.MedService_Name,
						userClearTimeMS: function() {
							this.getLoadMask(lang['osvobojdenie_zapisi']).show();
							Ext.Ajax.request({
								url: '/?c=Mse&m=clearTimeMSOnEvnPrescrMse',
								params: {
									TimetableMedService_id: this.TimetableMedService_id
								},
								callback: function(o, s, r) {
									this.getLoadMask().hide();
									if(s) {
										this.loadSchedule();
									}
								}.createDelegate(this)
							});
						}
					});
				}.createDelegate(this)
			},
			action_EvnMseJournal:
			{
				nn: 'action_EvnMseJournal',
				tooltip: lang['otkryit_jurnal_mse'],
				text: lang['jurnal_mse'],
				iconCls: 'mse-journal32',
				disabled: false,
				//hidden: !IS_DEBUG,
				handler: function() {
					getWnd('swEvnMseJournalWindow').show();
				}
			},
			action_RejectJournal:
			{
				nn: 'action_RejectJournal',
				tooltip: 'Открыть журнал отказов в направлении на МСЭ',
				text: 'Журнал отказов в направлении на МСЭ',
				iconCls : 'mp-queue32',
				disabled: false,
				handler: function() {
					getWnd('swEvnVKRejectJournalWindow').show({
						MedService_id: form.MedService_id
					});
				}.createDelegate(this)
			},
			action_ExportEvnPrescrMse:
			{
				nn: 'action_ExportEvnPrescrMse',
				text: 'Экспорт направлений на МСЭ',
				tooltip: 'Экспорт направлений на МСЭ',
				iconCls : 'database-export32',
				hidden: getRegionNick() == 'kz',
				handler: function() {
					getWnd('swEvnPrescrMseExportWindow').show({
						ARMType: this.ARMType,
						MedService_id: this.MedService_id
					});
				}.createDelegate(this)
			},
			action_ImportEvnMse:
			{
				nn: 'action_ImportEvnMse',
				text: 'Импорт обратных талонов',
				tooltip: 'Импорт обратных талонов',
				iconCls : 'database32',
				hidden: getRegionNick() == 'kz',
				handler: function() {
					getWnd('swEvnMseImportWindow').show({
						ARMType: this.ARMType,
						MedService_id: this.MedService_id
					});
				}.createDelegate(this)
			},
			action_reports: //http://redmine.swan.perm.ru/issues/18509
			{
				nn: 'action_Report',
				tooltip: lang['prosmotr_otchetov'],
				text: lang['prosmotr_otchetov'],
				iconCls: 'report32',
				//hidden: !this.enableDefaultActions,//( !document.getElementById('swWorkPlaceCallCenterWindow') || !this.enableDefaultActions ),
				handler: function() {
					if (sw.codeInfo.loadEngineReports)
					{
						getWnd('swReportEndUserWindow').show();
					}
					else
					{
						getWnd('reports').load(
							{
								callback: function(success)
								{
									sw.codeInfo.loadEngineReports = success;
									// здесь можно проверять только успешную загрузку
									getWnd('swReportEndUserWindow').show();
								}
							});
					}
				}
			}
		}
		
		this.leftPanel = new sw.Promed.BaseWorkPlaceButtonsPanel({
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
			panelActions: configActions
		});
		
		Ext.apply(this, 
		{
			layout: 'border',
			items: 
			[
				this.TopPanel,
				this.leftPanel,
				/*{
					layout: 'border',
					region: 'center',
					id: 'msewpSchedulePanel',
					items:
					[
						this.MedPersonalPanel,
						this.ScheduleGrid
					]
				}*/
				this.ScheduleGrid
			],
			buttons: 
			[{
				text: '-'
			}, 
			HelpButton(this, TABINDEX_MPSCHED + 98), 
			{
				iconCls: 'cancel16',
				text: BTN_FRMCLOSE,
				handler: function() { this.hide(); }.createDelegate(this)
			}]
		});
		sw.Promed.swMseWorkPlaceWindow.superclass.initComponent.apply(this, arguments);
	}
});
