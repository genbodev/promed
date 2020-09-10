/**
 * swVKWorkPlaceWindow - окно рабочего места ВК
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/projects/promed
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009-2010, Swan.
 * @author       Dmitry Storozhev
 * @version      14.10.2011 
 */
/*NO PARSE JSON*/

sw.Promed.swVKWorkPlaceWindow = Ext.extend(sw.Promed.BaseForm,
{
	codeRefresh: true,
	objectName: 'swVKWorkPlaceWindow',
	objectSrc: '/jscore/Forms/Mse/swVKWorkPlaceWindow.js',
	//тип АРМа, определяется к каким функциям будет иметь доступ врач через ЭМК, например у стоматолога появится ввод талона по стоматологии,
	//у врача параклиники будет доступ только к параклиническим услугам
	ARMType: 'vk',
	closable: true,
	closeAction: 'hide',
	maximized: true,
	title: WND_WPMP,
	MedService_id: null,
	MedService_Nmae: null,
	iconCls: 'workplace-mp16',
	id: 'swVKWorkPlaceWindow',
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
			if (mode != 'range'){
				if (this.mode == mode){
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

		if((this.dateMenu.getValue2()-this.dateMenu.getValue1())/86400000>= 31) {
			Ext.Msg.alert('Ошибка', 'Диапазон дат не должен превышать 31 день');
			return false;
		}

		var params = this.TopPanel.getForm().getValues();
		params.begDate = Ext.util.Format.date(this.dateMenu.getValue1(), 'd.m.Y');
		params.endDate = Ext.util.Format.date(this.dateMenu.getValue2(), 'd.m.Y');
		params.MedStaffFact_id = getGlobalOptions().CurMedStaffFact_id;
		params.MedService_id = this.MedService_id;

		// При направлении указывается ЛПУ и врач этого ЛПУ 
		/*if (this.findById('vkwpLpu_id').getValue()>0) {
			params.Lpu_id = this.findById('vkwpLpu_id').getValue();
		}
		if (this.findById('vkwpMedPersonal_id').getValue()>0) {
			params.MedPersonal_id = this.findById('vkwpMedPersonal_id').getValue();
		}*/

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
		Ext.Ajax.request({
			url: !data.EvnQueue_id ? C_TTG_APPLY : C_QUEUE_APPLY,
			params: data,
			callback: function(options, success, response) {
				grid.getStore().reload();
				this.getLoadMask().hide();
			}.createDelegate(this),
			failure: function() {
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
						msg: lang['na_sleduyuschiy_period']+ (response_obj[0].next_DrugRequestPeriod) +' '+lang['zayavka_po_vrachu']+' '+ (response_obj[0].MedPersonal_Fin) +lang['ne_naydena_otkryit_imeyuschuyusya_da_ili_sozdat_net'],
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
							userMedStaffFact: this.userMedStaffFact,
							mode: 'workplace',
							ARMType: this.ARMType,
							callback: function() 
							{
								if (this.mode == 'workplace')
								{
									getWnd('swVKWorkPlaceWindow').show({formMode:'open', ARMType: this.ARMType});
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
		// Добавление пациента вне записи
		if (getWnd('swPersonSearchWindow').isVisible()) {
			Ext.Msg.alert(lang['soobschenie'], lang['okno_poiska_cheloveka_uje_otkryito']);
			return false;
		}
		var win = this;
		getWnd('swPersonSearchWindow').show({
			onSelect: function(persData) {
				this.hide();
				Ext.Ajax.request({
					url: '/?c=ClinExWork&m=getNewEvnVKNumber',
					method: 'POST',
					callback: function(o, s, r) {
						win.getLoadMask().hide();
						if(s) {
							var result = Ext.util.JSON.decode(r.responseText)[0];
							getWnd('swClinExWorkEditWindow').show({
								EvnVK_NumProtocol: parseInt(result.EvnVK_NumProtocol) + 1,
								MedService_id: win.MedService_id,
								showtype: 'add',
								PersonData: {
									Person_id: persData.Person_id,
									Server_id: persData.Server_id
								},
								onHide: function() {
									win.scheduleRefresh();
								}
							});
						} else {
							sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_opredelit_nomer_novogo_protokola_vk']);
						}
					}
				});
			}
		});
	},
	
	scheduleAdd: function()
	{
		if (getWnd('swPersonSearchWindow').isVisible())	{
			sw.swMsg.alert(lang['soobschenie'], lang['okno_poiska_cheloveka_uje_otkryito']);
			return false;
		}
		
		var cur_win = this;
		getWnd('swPersonSearchWindow').show({
			onClose: function() {
				//
			},
			onSelect: function(pdata) {
				if (pdata.Person_IsDead == 'false') {
					getWnd('swPersonSearchWindow').hide();
					//log(pdata); return false;
					
					getWnd('swTTMSScheduleRecordWindow').show({
						Person: pdata,
						MedService_id: cur_win.msData.MedService_id,
						MedServiceType_id: cur_win.msData.MedServiceType_id,
						MedService_Nick: cur_win.msData.MedService_Nick,
						MedService_Name: cur_win.msData.MedService_Name,
						MedServiceType_SysNick: cur_win.msData.MedServiceType_SysNick,
						Lpu_did: cur_win.msData.Lpu_id,
						LpuUnit_did: cur_win.msData.LpuUnit_id,
						LpuUnitType_SysNick: cur_win.msData.LpuUnitType_SysNick,
						LpuSection_uid: cur_win.msData.LpuSection_id,
						LpuSection_Name: cur_win.msData.LpuSection_Name,
						LpuSectionProfile_id: cur_win.msData.LpuSectionProfile_id,
						/*
						
						ARMType: this.ARMType,
						fromEmk: this.fromEmk,
						mode: this.formMode,
						onSaveRecord: (typeof this.onSaveRecord == 'function') ? this.onSaveRecord : Ext.emptyFn,
						UslugaComplexMedService_id: rec.get('UslugaComplexMedService_id') || null,
						UslugaComplex_id: rec.get('UslugaComplex_id') || null,
						Diag_id: this.params.Diag_id || null,
						EvnDirection_pid: this.params.EvnDirection_pid || null,
						EvnQueue_id: this.params.EvnQueue_id || null,
						QueueFailCause_id: this.params.QueueFailCause_id || null,
						EvnPrescr_id: this.params.EvnPrescr_id || null,
						PrescriptionType_Code: this.params.PrescriptionType_Code || null,
						*/
						callback: function(data){
							getWnd('swTTMSScheduleRecordWindow').hide();
							cur_win.scheduleLoad();
						},
						userMedStaffFact: cur_win.userMedStaffFact,
						userClearTimeMS: function() {
							this.getLoadMask(lang['osvobojdenie_zapisi']).show();
							Ext.Ajax.request({
								url: '/?c=Mse&m=clearTimeMSOnEvnPrescrVK',
								params: {
									TimetableMedService_id: this.TimetableMedService_id
								},
								callback: function(o, s, r) {
									this.getLoadMask().hide();
									if(s) {
										this.scheduleLoad();
									}
								}.createDelegate(this)
							});
						}
					});
				} else {
					sw.swMsg.alert(lang['oshibka'], lang['zapis_nevozmojna_v_svyazi_so_smertyu_patsienta']);
				}
			},
			searchMode: 'all'
		});
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
				Ext.getCmp('swVKWorkPlaceWindow').scheduleSave(data);
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
			grid = form.getGrid(),
			record = grid.getSelectionModel().getSelected();
		if (!grid) {
			Ext.Msg.alert(lang['oshibka'], lang['spisok_raspisaniy_ne_nayden']);
			return false;
		}
		if ( !record ) {
			Ext.Msg.alert(lang['oshibka'], lang['ne_vyibrana_zapis']);
			return false;
		}

		var params = {
			Person_id: record.get('Person_id'),
			Server_id: record.get('Server_id'),
			PersonEvn_id: record.get('PersonEvn_id'),
			ARMType: 'vk',
			callback: function()
			{
				this.scheduleRefresh();
			}.createDelegate(this)
		}

		if ( !Ext.isEmpty(getGlobalOptions().CurMedStaffFact_id) ) {
			Ext.apply(params, {
				MedStaffFact_id: getGlobalOptions().CurMedStaffFact_id,
				LpuSection_id: record.get('LpuSection_id'),
				userMedStaffFact: this.userMedStaffFact,
				mode: 'workplace'
			});
		}
		if ( !Ext.isEmpty(record.get('EvnVK_id')) ) {
			params.searchNodeObj = {
				parentNodeId: 'root',
				last_child: false,
				disableLoadViewForm: false,
				EvnClass_SysNick: 'EvnVK',
				Evn_id: record.get('EvnVK_id')
			};
		}  else {
			params.EvnPrescr_id = record.get('EvnPrescrVK_id');
		}

		checkPersonPhoneVerification({
			Person_id: params.Person_id,
			MedStaffFact_id: params.userMedStaffFact?params.userMedStaffFact.MedStaffFact_id:null,
			callback: function(){getWnd('swPersonEmkWindow').show(params)}
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
	openVoteListVK: function() {
		var form = this,
			grid = form.getGrid(),
			rec = grid.getSelectionModel().getSelected();
			
		if (!rec || !rec.get('EvnPrescrVK_id')) return false;
		
		getWnd('swVoteListVKWindow').show({
			EvnPrescrVK_id: rec.get('EvnPrescrVK_id'),
			EvnStatusVK_id: rec.get('EvnStatusVK_id'),
			callback: function() {
				grid.getStore().reload();
			}
		});
	},
	openVoteExpertVK: function() {
		var form = this,
			grid = form.getGrid(),
			rec = grid.getSelectionModel().getSelected();
			
		if (!rec || !rec.get('VoteExpertVK_id')) return false;
		
		getWnd('swVoteExpertVKEditWindow').show({
			VoteExpertVK_id: rec.get('VoteExpertVK_id'),
			callback: function() {
				grid.getStore().reload();
			}
		});
	},
	vkDelete: function()
	{
		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( buttonId == 'yes' ) {
					var form = this;
					var grid = form.getGrid();
					Ext.Ajax.request({
						url: '/?c=ClinExWork&m=deleteEvnVK',
						params: {EvnVK_id: grid.getSelectionModel().getSelected().data['EvnVK_id']},
						method: 'post',
						callback: function(opt, success, response){
							if (success && response.responseText != '')
							{
								grid.getStore().reload();
								form.gridActions.vk.items[0].menu.items.items[3].setDisabled(true);
							}
						}
					});
				}
			}.createDelegate(this),
			icon: Ext.MessageBox.QUESTION,
			msg: lang['vyi_deystvitelno_hotite_udalit_vyibrannyiy_protokol'],
			title: lang['vopros']
		});
	},
	mseDelete: function()
	{
		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( buttonId == 'yes' ) {
					var form = this;
					var grid = form.getGrid();
					Ext.Ajax.request({
						url: '/?c=ClinExWork&m=deleteEvnPrescrMse',
						params: {EvnPrescrMse_id: grid.getSelectionModel().getSelected().data['EvnPrescrMse_id']},
						method: 'post',
						callback: function(opt, success, response){
							if (success && response.responseText != '')
							{
								grid.getStore().reload();
								//form.gridActions.vk_mse_vmp.setDisabled(true);
							}
						}
					});
				}
			}.createDelegate(this),
			icon: Ext.MessageBox.QUESTION,
			msg: lang['vyi_deystvitelno_hotite_udalit_vyibranoe_napravlenie'],
			title: lang['vopros']
		});
	},
	mseVmpDelete: function()
	{
		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( buttonId == 'yes' ) {
					var form = this;
					var grid = form.getGrid();
					Ext.Ajax.request({
						url: '/?c=ClinExWork&m=deleteEvnPrescrMseEvnDirectionHTM',
						params: {EvnVK_id: grid.getSelectionModel().getSelected().data['EvnVK_id']},
						method: 'post',
						callback: function(opt, success, response){
							if (success && response.responseText != '')
							{
								grid.getStore().reload();
								//form.gridActions.vk_mse_vmp.setDisabled(true);
							}
						}
					});
				}
			}.createDelegate(this),
			icon: Ext.MessageBox.QUESTION,
			msg: lang['vyi_deystvitelno_hotite_udalit_vyibranoe_napravlenie'],
			title: lang['vopros']
		});
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
		else if ( !grid.getSelectionModel().getSelected() )
		{
			Ext.Msg.alert(lang['oshibka'], lang['ne_vyibrana_zapis']);
			return false;
		}
		else if ( !!grid.getSelectionModel().getSelected().get('EvnVK_id') )
		{
			Ext.Msg.alert(lang['oshibka'], 'У выбранной записи заполнен Протокол ВК. <br>Удаление невозможно');
			return false;
		}
		var id = grid.getSelectionModel().getSelected().data['TimetableMedService_id'];
		var vkid = grid.getSelectionModel().getSelected().data['EvnPrescrVK_id'];
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
						form.getLoadMask(lang['osvobojdenie_zapisi']).show();
							Ext.Ajax.request({
								url: '/?c=Mse&m=clearTimeMSOnEvnPrescrVK',
								params: {
									TimetableMedService_id: id,
									EvnPrescrVK_id: vkid
								},
								callback: function(o, s, r) {
									form.getLoadMask().hide();
									if(s) {
										form.scheduleLoad();
									}
								}.createDelegate(this)
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
		}
		
	},
	scheduleRefresh: function()
	{
		var params = this.TopPanel.getForm().getValues();
		params.begDate = Ext.util.Format.date(this.dateMenu.getValue1(), 'd.m.Y');
		params.endDate = Ext.util.Format.date(this.dateMenu.getValue2(), 'd.m.Y');
		//params.MedStaffFact_id = getGlobalOptions().CurMedStaffFact_id;
		params.MedService_id = this.MedService_id;
		this.getGrid().loadStore(params);
	},
	schedulePrint:function(action)
	{
		var record = this.getGrid().getSelectionModel().getSelected();

		if (!record) {
            sw.swMsg.alert(lang['oshibka'], lang['zapis_ne_vyibrana']);
            return false;
        }

        var params = {};
        if (action && action == 'row') {
			params.rowId = record.id;
            Ext.ux.GridPrinter.print(this.getGrid(), params);
        } else {
            Ext.ux.GridPrinter.print(this.getGrid(),params);
        }
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
	// метод только доля Вологды
	addEvnVk: function(fromVote) {
		var win = this,
			grid = this.getGrid(),
			rec = grid.getSelectionModel().getSelected(),
			args = {};

		args.EvnPrescrVK_id = rec.get('EvnPrescrVK_id');
		args.MedService_id = this.MedService_id;
		args.MedPersonal_id = rec.get('MedPersonal_id');

		if (getRegionNick() != 'vologda' || !rec.get('EvnPrescrVK_id')) {
			return false;
		}

		args.onHide = function() {
			this.scheduleRefresh();
		}.createDelegate(this);

		args.showtype = 'add';
		args.PersonData = {
			Person_id: rec.get('Person_id'),
			Server_id: rec.get('Server_id')
		};
		//args.LpuSection_id = rec.get('LpuSection_id');

		args.Diag_id = rec.get('Diag_id');
		args.CauseTreatmentType_id = rec.get('CauseTreatmentType_id');

		args.EvnVK_NumCard = rec.get('EvnVK_NumCard');
		args.EvnPrescrVK_pid = rec.get('EvnPrescrVK_pid');

		args.EvnStickBase_id = rec.get('EvnStickBase_id');
		args.EvnStick_all = rec.get('EvnStick_all');
		args.EvnVK_LVN = rec.get('EvnVK_LVN');
		args.EvnVK_Note = rec.get('EvnVK_Note');

		args.EvnVK_isInternal = fromVote ? 1 : 2;

		if(rec.get('EvnPrescrMse_id')){
			args.EvnPrescrMse_id = rec.get('EvnPrescrMse_id');
		}

		win.getLoadMask().show();
		Ext.Ajax.request({
			url: '/?c=ClinExWork&m=getNewEvnVKNumber',
			method: 'POST',
			callback: function(options, success, response)
			{
				win.getLoadMask().hide();
				if(success)	{
					var result = Ext.util.JSON.decode(response.responseText);
					args.EvnVK_NumProtocol = parseInt(result[0].EvnVK_NumProtocol) + 1;
					getWnd('swClinExWorkEditWindow').show(args);
				} else {
					sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_opredelit_nomer_novogo_protokola_vk']);
				}
			}
		});
	},
	listeners:
	{
		
	},
	show: function()
	{
		sw.Promed.swVKWorkPlaceWindow.superclass.show.apply(this, arguments);
		
		if ( !arguments[0] || !arguments[0].MedService_id ) { 
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() { this.hide(); }.createDelegate(this) );
			return false;
		}
		
		this.MedService_id = arguments[0].MedService_id;
		this.MedService_Name = arguments[0].MedService_Name;
		
		this.TopPanel.getForm().reset();
		
		//this.setTitle('Рабочее место врачебной комиссии' + ' (' + this.MedService_Name + ' / ' + getGlobalOptions().CurMedPersonal_FIO + ')');
		
		this.userMedStaffFact = arguments[0];
		sw.Promed.MedStaffFactByUser.setMenuTitle(this, this.userMedStaffFact);
		
		//Подгружаем данные службы, нужные для записи
		var ms_combo = this.TopPanel.getForm().findField('MedService_id');
		this.msData = {};
		ms_combo.getStore().load({
			params: { MedService_id: this.MedService_id },
			callback: function(r,o,s) {
				if(r.length > 0) {
					this.msData = r[0].data;
				}
			}.createDelegate(this)
		});
		
		var store, record;
		
		store = this.TopPanel.getForm().findField('EvnStatus_id').getStore();
		record = new Ext.data.Record.create(store);
		this.TopPanel.getForm().findField('EvnStatus_id').lastQuery = '';
		store.clearFilter();
		store.filterBy(function(rec) {
			return rec.get('EvnStatus_id').inlist([0,27,28,29,30,31,32]);
		});
		if (store.getCount() && store.getAt(0).get('EvnStatus_id') != 0) {
			store.insert(0, new record({
				EvnStatus_id: 0,
				EvnStatus_Name: 'Все'
			}));
		}
		this.TopPanel.getForm().findField('EvnStatus_id').setValue(0);
		
		store = this.TopPanel.getForm().findField('CauseTreatmentType_id').getStore();
		record = new Ext.data.Record.create(store);
		this.TopPanel.getForm().findField('CauseTreatmentType_id').lastQuery = '';
		store.clearFilter();
		store.filterBy(function(rec) {
			return !Ext.isEmpty(rec.get('CauseTreatmentType_id')) > 0;
		});
		if (store.getAt(0).get('CauseTreatmentType_id') != 0) {
			store.insert(0, new record({
				CauseTreatmentType_id: 0,
				CauseTreatmentType_Name: 'Все'
			}));
		}
		this.TopPanel.getForm().findField('CauseTreatmentType_id').setValue(0);
		
		/*
		if (sw.Promed.MedStaffFactByUser.last) {
			this.userMedStaffFact = sw.Promed.MedStaffFactByUser.last;
			// Создаем свой заголовок, единый для всех армов, на основании данных пришедших с сервера ( из User_model)
			sw.Promed.MedStaffFactByUser.setMenuTitle(this, arguments[0]);
		} else {
			// TODO: Надо правильно сделать, selectMedStaffFact должен выполнться перед открытием формы
			sw.Promed.MedStaffFactByUser.selectARM({
				selectFirst: true,
				ARMType: 'vk',
				onSelect: function(data) {
					this.userMedStaffFact = data;
					this.gridActions.open_emk.setDisabled(false);
					// Создаем свой заголовок, единый для всех армов, на основании данных пришедших с сервера ( из User_model)
					sw.Promed.MedStaffFactByUser.setMenuTitle(this, arguments[0]);
				}.createDelegate(this)
			});
		}
		*/
		
		if ((arguments[0]) && (arguments[0].formMode) && (arguments[0].formMode == 'open')) {
			// Да просто активация
			// this.formMode = 'open';
			this.scheduleRefresh();
			// если formMode был = send, то все вертаем назад на сохраненные значения 
		} else {
			// Очистка грида 
			this.getGrid().clearStore();
			// Медперсонал
			//this.MedPersonalPanel.findById('vkwpMedPersonal_id').getStore().clearFilter();
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
			//this.MedPersonalPanel.findById('vkwpLpu_id').setValue('');
			//this.MedPersonalPanel.findById('vkwpMedPersonal_id').setValue('');
			delete(this.saveParams);
			this.scheduleLoad(this.mode);
		}
		this.formMode = 'open';
		this.TopPanel.show();
		
		//this.MedPersonalPanel.hide();
		//this.gridActions.create.show();
		//this.setActionDisabled('create',true);
//		this.gridActions.open.show();
//		this.setActionDisabled('open',false);
		
		//this.findById('vkwpSchedulePanel').syncSize();

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
		
		this.syncSize();
	},

	openEvnPrescrMseWindow: function()
	{
		var rec = this.getGrid().getSelectionModel().getSelected();

		var cur_win = this;
		
		if (!rec) return false;

		if (rec.get('EvnPrescrMse_id') == null) {
			if(rec.get('EvnVK_id') == null || rec.get('ExpertiseNameType_id') != 2 || Date.parseDate(rec.get('EvnVK_setDT'), 'd.m.Y') >= new Date(2019,8,16)) return false;
			getWnd('swSelectMedServiceWindow').show({
				isRecord: true, // на запись
				ARMType: 'mse',
				onSelect: function(msData) {
					if (getRegionNick() == 'perm') {
						// на Перми сразу ставим в очередь
						sw.Promed.Direction.queuePerson({
							person: {
								Person_Surname: rec.get('Person_Surname') || rec.get('Person_Fio'),
								Person_Firname: rec.get('Person_Firname'),
								Person_Secname: rec.get('Person_Secname'),
								Person_Birthday: rec.get('Person_Birthday'),
								Person_id: rec.get('Person_id'),
								PersonEvn_id: rec.get('PersonEvn_id'),
								Server_id: rec.get('Server_id')
							},
							direction: {
								DirType_id: 9
								,MedService_id: msData.MedService_id
								,MedServiceType_id: msData.MedServiceType_id
								,MedService_Nick: msData.MedService_Nick
								,MedService_Name: msData.MedService_Name
								,MedServiceType_SysNick: msData.MedServiceType_SysNick
								,Lpu_did: msData.Lpu_id
								,LpuUnit_did: msData.LpuUnit_id
								,LpuUnitType_SysNick: 'mse'
								,LpuSection_uid: msData.LpuSection_id
								,LpuSection_Name: msData.LpuSection_Name
								,LpuSectionProfile_id: msData.LpuSectionProfile_id
								,Diag_id: rec.get('Diag_id')
								,EvnDirection_pid: rec.get('EvnPrescrVK_pid')
								,EvnVK_id: rec.get('EvnVK_id')
								,EvnQueue_id: cur_win.EvnQueue_id || null
								,QueueFailCause_id: cur_win.QueueFailCause_id || null
								,UslugaComplex_id: cur_win.UslugaComplex_id || null
								,PrehospDirect_id: (getGlobalOptions().lpu_id == msData.Lpu_id)?1:2
								,EvnVK_setDT: null
								,EvnPrescr_id: null
								,PrescriptionType_Code: null
								,MedStaffFact_id: cur_win.userMedStaffFact.MedStaffFact_id
								,MedPersonal_id: cur_win.userMedStaffFact.MedPersonal_id
								,Lpu_id: cur_win.userMedStaffFact.Lpu_id
								,LpuSection_id: cur_win.userMedStaffFact.LpuSection_id
								//передаем фейковые данные, т.к. они обязательные при сохранении очереди. Но при постановке в очередь на службу уровня ЛПУ этих данных просто нет
								,LpuSection_did: cur_win.LpuSection_uid || cur_win.userMedStaffFact.LpuSection_id //куда направлен
								,LpuUnit_did: cur_win.LpuUnit_did || cur_win.userMedStaffFact.LpuUnit_id //куда направлен
							},
							order: {}, // если при записи сделан заказ, то передаем его данные
							callback: function(data) {
								cur_win.scheduleLoad();
							}.createDelegate(this),
							onHide: null,
							needDirection: null,
							fromEmk: null,
							mode: 'nosave',
							loadMask: true,
							windowId: 'TTMSScheduleRecordWindow'
						});
					} else {
						getWnd('swTTMSScheduleRecordWindow').show({
							Person: {
								Person_Surname: rec.get('Person_Surname') || rec.get('Person_Fio'),
								Person_Firname: rec.get('Person_Firname'),
								Person_Secname: rec.get('Person_Secname'),
								Person_Birthday: rec.get('Person_Birthday'),
								Person_id: rec.get('Person_id'),
								PersonEvn_id: rec.get('PersonEvn_id'),
								Server_id: rec.get('Server_id')
							},
							MedService_id: msData.MedService_id,
							MedServiceType_id: msData.MedServiceType_id,
							MedService_Nick: msData.MedService_Nick,
							MedService_Name: msData.MedService_Name,
							MedServiceType_SysNick: msData.MedServiceType_SysNick,
							Lpu_did: msData.Lpu_id,
							LpuUnit_did: msData.LpuUnit_id,
							LpuUnitType_SysNick: msData.LpuUnitType_SysNick,
							LpuSection_uid: msData.LpuSection_id,
							LpuSection_Name: msData.LpuSection_Name,
							LpuSectionProfile_id: msData.LpuSectionProfile_id,
							ARMType: 'mse',
							Diag_id: rec.get('Diag_id'),
							EvnDirection_pid: rec.get('EvnPrescrVK_pid'),
							EvnVK_id: rec.get('EvnVK_id'),
							/*
							fromEmk: this.fromEmk,
							mode: this.formMode,
							onSaveRecord: (typeof this.onSaveRecord == 'function') ? this.onSaveRecord : Ext.emptyFn,
							UslugaComplexMedService_id: rec.get('UslugaComplexMedService_id') || null,
							UslugaComplex_id: rec.get('UslugaComplex_id') || null,
							EvnQueue_id: this.params.EvnQueue_id || null,
							QueueFailCause_id: this.params.QueueFailCause_id || null,
							EvnPrescr_id: this.params.EvnPrescr_id || null,
							PrescriptionType_Code: this.params.PrescriptionType_Code || null,
							*/
							callback: function(data){
								getWnd('swTTMSScheduleRecordWindow').hide();
								cur_win.scheduleLoad();
							},
							userMedStaffFact: cur_win.userMedStaffFact,
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
											this.scheduleLoad();
										}
									}.createDelegate(this)
								});
							}
						});
					}
				}
			});
		} else {
			var action = (rec.get('EvnStatus_SysNick') && rec.get('EvnStatus_SysNick').inlist(['New', 'Rework'])) ? 'edit' : 'view';
			getWnd('swDirectionOnMseEditForm').show({
				Person_id: rec.get('Person_id'),
				Server_id: rec.get('Server_id'),
				EvnPrescrMse_id: rec.get('EvnPrescrMse_id'),
				EvnPL_id: rec.get('EvnPL_id'),
				action: action,
				onClose: function(){
					this.scheduleRefresh();
				}.createDelegate(this)
			});
		}
	},

	openEvnDirectionHTMWindow: function()
	{
		var rec = this.getGrid().getSelectionModel().getSelected();
		if(
			!rec || 
			(rec.get('EvnVK_id') == null && getRegionNick() != 'vologda')
		) return false;

		var cur_win = this;

// 		if (rec.get('EvnDirectionHTM_id') == null && getRegionNick().inlist(['perm'])) {
// 			getWnd('swDirectionOnHTMEditForm').show({
// 				action: 'add',
// 				EvnDirectionHTM_pid: rec.get('EvnVK_id'),
// 				Person_id: rec.get('Person_id'),
// 				PersonEvn_id: rec.get('PersonEvn_id'),
// 				Server_id: rec.get('Server_id'),
// 				//MedService_id: rec.get('MedService_id'),
// 				LpuSection_id: getGlobalOptions().CurLpuSection_id,
// 				LpuSection_did: getGlobalOptions().CurLpuSection_id,
// 				EvnDirectionHTM_VKProtocolNum: rec.get('EvnVK_NumProtocol'),
// 				EvnDirectionHTM_VKProtocolDate: rec.get('EvnVK_setDT'),
// 				EvnVK_setDT: rec.get('EvnVK_setDT'),
// 				Diag_id: rec.get("Diag_id"),
// 				onHide: function() {
// 					cur_win.scheduleLoad();
// 				}
// 			});
// 		} else
		if (rec.get('EvnDirectionHTM_id') == null) {

			if(getRegionNick() != 'kz') {

				var params = {
					type: 'HTM',
					dirTypeData: {DirType_id: 19, DirType_Code: 15, DirType_Name: 'На высокотехнологичную помощь'},
					personData: {
						Person_id: rec.get('Person_id'),
						PersonEvn_id: rec.get('PersonEvn_id'),
						Server_id: rec.get('Server_id'),
					},
					directionData: {
						action: "add",
						Person_id: rec.get('Person_id'),
						PersonEvn_id: rec.get('PersonEvn_id'),
						Server_id: rec.get('Server_id'),
						MedService_id: sw.Promed.MedStaffFactByUser.current.MedService_id,
						LpuSection_id: null,
						LpuSection_did: null,
						ARMType: 'htm',
						Diag_id: rec.get('Diag_id'),
						EvnDirection_pid: rec.get('EvnPrescrVK_pid'),
						EvnDirectionHTM_pid: rec.get('EvnVK_id'),
						EvnVK_setDT: rec.get('EvnVK_setDT'),
						EvnDirectionHTM_VKProtocolNum: rec.get('EvnVK_NumProtocol'),
						EvnDirectionHTM_VKProtocolDate: rec.get('EvnVK_setDT')
					},
					MedService_id: sw.Promed.MedStaffFactByUser.current.MedService_id,
				};
				// окно мастера направлений #101026
				getWnd('swDirectionMasterWindow').show(params);

			}else {

				getWnd('swSelectMedServiceWindow').show({
					isRecord: true, // на запись
					ARMType: 'htm',
					onSelect: function (msData) {
						if (!msData) {
							getWnd('swDirectionOnHTMEditForm').show({
								action: "add",
								Person_id: rec.get('Person_id'),
								PersonEvn_id: rec.get('PersonEvn_id'),
								Server_id: rec.get('Server_id'),
								MedService_id: sw.Promed.MedStaffFactByUser.current.MedService_id,
								LpuSection_id: null,
								LpuSection_did: null,
								ARMType: 'htm',
								Diag_id: rec.get('Diag_id'),
								EvnDirection_pid: rec.get('EvnPrescrVK_pid'),
								EvnVK_id: rec.get('EvnVK_id'),
								EvnVK_setDT: rec.get('EvnVK_setDT'),
								EvnVK_NumProtocol: rec.get('EvnVK_NumProtocol')
							});
							return false;
						}
						getWnd('swTTMSScheduleRecordWindow').show({
							Person: {
								Person_Surname: rec.get('Person_Surname') || rec.get('Person_Fio'),
								Person_Firname: rec.get('Person_Firname'),
								Person_Secname: rec.get('Person_Secname'),
								Person_Birthday: rec.get('Person_Birthday'),
								Person_id: rec.get('Person_id'),
								PersonEvn_id: rec.get('PersonEvn_id'),
								Server_id: rec.get('Server_id')
							},
							MedService_id: msData.MedService_id,
							MedServiceType_id: msData.MedServiceType_id,
							MedService_Nick: msData.MedService_Nick,
							MedService_Name: msData.MedService_Name,
							MedServiceType_SysNick: msData.MedServiceType_SysNick,
							Lpu_did: msData.Lpu_id,
							LpuUnit_did: msData.LpuUnit_id,
							LpuUnitType_SysNick: msData.LpuUnitType_SysNick,
							LpuSection_uid: msData.LpuSection_id,
							LpuSection_Name: msData.LpuSection_Name,
							LpuSectionProfile_id: msData.LpuSectionProfile_id,
							ARMType: 'htm',
							Diag_id: rec.get('Diag_id'),
							EvnDirection_pid: rec.get('EvnPrescrVK_pid'),
							EvnVK_id: rec.get('EvnVK_id'),
							EvnVK_setDT: rec.get('EvnVK_setDT'),
							EvnVK_NumProtocol: rec.get('EvnVK_NumProtocol'),

							// fromEmk: this.fromEmk,
							// mode: this.formMode,
							// onSaveRecord: (typeof this.onSaveRecord == 'function') ? this.onSaveRecord : Ext.emptyFn,
							// UslugaComplexMedService_id: rec.get('UslugaComplexMedService_id') || null,
							// UslugaComplex_id: rec.get('UslugaComplex_id') || null,
							// EvnQueue_id: this.params.EvnQueue_id || null,
							// QueueFailCause_id: this.params.QueueFailCause_id || null,
							// EvnPrescr_id: this.params.EvnPrescr_id || null,
							// PrescriptionType_Code: this.params.PrescriptionType_Code || null,

							callback: function (data) {
								getWnd('swTTMSScheduleRecordWindow').hide();
								cur_win.scheduleLoad();
							},
							userMedStaffFact: cur_win.userMedStaffFact,
							userClearTimeMS: function () {
								this.getLoadMask(lang['osvobojdenie_zapisi']).show();
								Ext.Ajax.request({
									url: '/?c=Mse&m=clearTimeMSOnEvnPrescrMse',
									params: {
										TimetableMedService_id: this.TimetableMedService_id
									},
									callback: function (o, s, r) {
										this.getLoadMask().hide();
										if (s) {
											this.scheduleLoad();
										}
									}.createDelegate(this)
								});
							}
						});
					}
				});

			}

		} else {
			getWnd('swDirectionOnHTMEditForm').show({
				Person_id: rec.get('Person_id'),
				Server_id: rec.get('Server_id'),
				EvnDirectionHTM_id: rec.get('EvnDirectionHTM_id'),
				action: 'edit'
			});
		}
	},

	updateEpmStatus: function(Evn_id, EvnStatus_SysNick, EvnStatusHistory_Cause)
	{
		if(!Evn_id) return false;
		var win = this,
			global_options = getGlobalOptions();
		win.getLoadMask().show();
		Ext.Ajax.request({
			url: '/?c=Mse&m=updateEvnPrescrMseStatus',
			method: 'POST',
			callback: function(options, success, response)
			{
				win.getLoadMask().hide();
				win.scheduleLoad();
			},
			params: {
				Evn_id: Evn_id,
				EvnStatus_SysNick: EvnStatus_SysNick,
				EvnStatusHistory_Cause: EvnStatusHistory_Cause?EvnStatusHistory_Cause:'',
				MedService_id: win.MedService_id || global_options.CurMedService_id,
				MedPersonal_id: global_options.medpersonal_id
			}
		});
	},

	sendToMse: function(){
		if(getRegionNick() == 'kz'){
			this.sendToMseContinuation();
			return false;
		}
		var rec = this.getGrid().getSelectionModel().getSelected();
		if(!rec || rec.get('EvnPrescrMse_id') == null) return false;
		
		var win = this;
		Ext.Ajax.request({
			url: '/?c=Mse&m=completenessTestMSE',
			method: 'POST',
			callback: function(options, success, response) {
				var response_obj = Ext.util.JSON.decode(response.responseText);
				if(Ext.isArray(response_obj) && response_obj.length>0){
					sw.swMsg.alert(langs('Сообщение'), langs(response_obj[0]['msg']));
				}else{
					this.sendToMseContinuation();
				}
			}.bind(this),
			params: {
				EvnPrescrMse_id: rec.get('EvnPrescrMse_id'),
			}
		});
	},
	
	sendToMseContinuation: function()
	{
		var rec = this.getGrid().getSelectionModel().getSelected();
		if(!rec || rec.get('EvnPrescrMse_id') == null) return false;
		
		var win = this;
		
		if (Ext.isEmpty(rec.get('EPM_MedService_id')) || true) {
			getWnd('swSelectMedServiceWindow').show({
				action: 'edit',
				isRecord: true,
				ARMType: 'mse',
				onSelect: function(msData) {
					Ext.Ajax.request({
						url: '/?c=Mse&m=setEpmMedService',
						callback: function(options, success, response) {
							if (success) {
								win.updateEpmStatus(rec.get('EvnPrescrMse_id'), 'Sended');
							}
						},
						params: {
							EvnPrescrMse_id: rec.get('EvnPrescrMse_id'),
							MedService_id: msData.MedService_id
						}
					});
				}
			});
		}
		else {
			this.updateEpmStatus(rec.get('EvnPrescrMse_id'), 'Sended');
		}
	},
	
	sendToRevision: function()
	{
		// на доработку
		var rec = this.getGrid().getSelectionModel().getSelected();
		if(!rec || rec.get('EvnPrescrMse_id') == null) return false;
		
		this.updateEpmStatus(rec.get('EvnPrescrMse_id'), 'RefusalVK');
	},
	
	refuseEvnPrescrMse: function()
	{
		var rec = this.getGrid().getSelectionModel().getSelected();
		if(!rec || rec.get('EvnVK_id') == null) return false;
		var win = this,
			status_sysnick = rec.get('EvnVK_IsFail') == 2 ? 'New' : 'RefusalDir',
			is_fail = rec.get('EvnVK_IsFail') == 2 ? null : 2;
		var cbFn = function(EvnStatusHistory_Cause){
			win.updateEpmStatus(rec.get('EvnPrescrMse_id'), status_sysnick, EvnStatusHistory_Cause);
			//Если обычного отказа будет недостаточно, оставлю код
			win.setIsFail(rec,is_fail);
		};
		if(status_sysnick == 'New'){
			cbFn('');
		}
		else{
			if (getRegionNick() == 'vologda') {
				win.getLoadMask(LOAD_WAIT).show();
				Ext.Ajax.request({
					url: '/?c=ClinExWork&m=getDecisionVK',
					method: 'POST',
					callback: function(options, success, response) {
						win.getLoadMask().hide();
						var result = Ext.util.JSON.decode(response.responseText);
						cbFn(result[0].EvnVK_DecisionVK);
					},
					params: {
						EvnVK_id: rec.get('EvnVK_id')
					}
				});
			} else {
				getWnd('swRefuseEvnPrescrMseWindow').show({
					EvnVK_DecisionVK: rec.get('EvnVK_DecisionVK'),
					callback: function(EvnStatusCauseData) {
						if (!Ext.isEmpty(EvnStatusCauseData.EvnStatusHistory_Cause)) {
							cbFn(EvnStatusCauseData.EvnStatusHistory_Cause);
						}
					}
				});
			}
		}
	},
	setIsFail: function(rec,is_fail){
		var win = this;
		Ext.Ajax.request({
			url: '/?c=Mse&m=setEvnVKIsFail',
			method: 'POST',
			callback: function(options, success, response)
			{
				win.scheduleLoad();
			},
			params: {
				EvnVK_id: rec.get('EvnVK_id'),
				EvnVK_IsFail: is_fail
			}
		});
	},
	setTitleFieldSet: function()
	{
		var fset = this.TopPanel.find('xtype', 'fieldset')[0],
			isfilter = false,
			title = lang['poisk_filtr'];
		
		fset.findBy(function(f) {
			if( f.xtype && f.xtype.inlist(['textfieldpmw', 'combo', 'swdatefield']) ) {
				if( !f.getValue().toString().inlist(['', '3']) && f.getValue() != null ) {
					isfilter = true;
				}
			}
			if (f.xtype && f.xtype.inlist(['swcommonsprcombo']) && !!parseInt(f.getValue())) {
				isfilter = true;
			}
		});
		
		fset.setTitle( title + ( isfilter == true ? '' : 'не ' ) + 'установлен' );
	},
	
	openVKJournal: function() {
		getWnd('swClinExWorkSearchWindow').show({
			userMedStaffFact: this.userMedStaffFact
		});
	},
				
	initComponent: function()
	{
		var win = this;

		// Actions
		var Actions =
		[
			{name:'open_emk', text:lang['otkryit_emk'], tooltip: lang['otkryit_elektronnuyu_meditsinskuyu_kartu_patsienta'], iconCls : 'open16', handler: function() { this.scheduleOpen(); }.createDelegate(this)},
			{name:'prescrmse', text: lang['napravlenie_na_mse'], disabled: true, menu: [{
                    text: 'Открыть',
                    handler: function () {
                        this.openEvnPrescrMseWindow();
                    }.createDelegate(this)
                }, {
					text: 'Создать',
					handler: function () {
						this.openEvnPrescrMseWindow();
					}.createDelegate(this)
				}, {
					text: 'На доработку лечащему врачу',
					handler: function () {
						this.sendToRevision();
					}.createDelegate(this)
				}, {
					text: 'Отказ в направлении на МСЭ',
					handler: function () {
						this.refuseEvnPrescrMse();
					}.createDelegate(this)
				}, {
					text: 'Отправить в бюро МСЭ',
					handler: function () {
						this.sendToMse();
					}.createDelegate(this)
				}, {
					text: 'Удалить',
					iconCls: 'delete16',
					handler: function () {
						this.mseDelete()
					}.createDelegate(this)
				}]
			},
			{name:'directionhtm', text: 'Направление на ВМП', disabled: true, menu: [{
					text: 'Создать',
					handler: function () {
						this.openEvnDirectionHTMWindow();
					}.createDelegate(this)
				}, {
					text: 'Открыть',
					handler: function () {
						this.openEvnDirectionHTMWindow();
					}.createDelegate(this)
				}, {
					text: 'Удалить',
					iconCls: 'delete16',
					handler: function () {
						this.mseVmpDelete()
					}.createDelegate(this)
				}]
			},
			{name: 'vk', text: 'Протокол ВК', menu: [{
					text: 'Создать по итогам голосования',
					iconCls: 'add16',
					hidden: getRegionNick() != 'vologda',
					handler: function () {
						this.addEvnVk(true)
					}.createDelegate(this)
				}, {
					text: 'Создать по итогам очной ВК',
					iconCls: 'add16',
					hidden: getRegionNick() != 'vologda',
					handler: function () {
						this.addEvnVk(false)
					}.createDelegate(this)
				}, {
					text: lang['otkryit'],
					iconCls: 'open16',
					handler: function () {
						var grid = this.getGrid(),
							record = grid.getSelectionModel().getSelected();
						if ( !record ) 	return false;
						grid.fireEvent('rowdblclick', grid, grid.getStore().indexOf(record));
					}.createDelegate(this)
				}, {
					name: 'vk_del',
					text: 'Удалить',
					iconCls: 'delete16',
					handler: function () {
						this.vkDelete()
					}.createDelegate(this)
				}]
			},
			{name:'shedule', text: 'Запись', iconCls : 'add16', menu: [{
					text: lang['bez_zapisi'],
					iconCls: 'add16',
					disabled: getRegionNick() == 'vologda',
					handler: function () {
						this.scheduleNew()
					}.createDelegate(this)
				}, {
					text: lang['zapisat_patsienta'],
					iconCls: 'add16',
					handler: function () {
						this.scheduleAdd()
					}.createDelegate(this)
				}, {
					text: lang['osvobodit_zapis'],
					iconCls: 'delete16',
					handler: function () {
						this.scheduleDelete()
					}.createDelegate(this)
				}]
			},
			{name:'expert', text: 'Экспертиза', hidden: getRegionNick() != 'vologda', menu: [{
					text: 'Назначить комиссию',
					handler: function () {
						this.openVoteListVK()
					}.createDelegate(this)
				}, {
					text: 'Изменить состав комиссии',
					handler: function () {
						this.openVoteListVK()
					}.createDelegate(this)
				}, {
					text: 'Решение эксперта',
					handler: function () {
						this.openVoteExpertVK()
					}.createDelegate(this)
				}]
			},
//			{name:'queue', text:lang['zapisat_iz_ocheredi'], tooltip: lang['zapisat_iz_ocheredi'], iconCls : 'x-btn-text', icon: 'img/icons/add16.png', handler: function() {this.sheduleAddFromQueue()}.createDelegate(this)},
//			{name:'edit', text:BTN_GRIDEDIT, tooltip: BTN_GRIDEDIT, iconCls : 'x-btn-text', icon: 'img/icons/edit16.png', handler: function() {this.scheduleEdit()}.createDelegate(this)},
			{name:'copy', text:BTN_GRIDCOPY, tooltip: BTN_GRIDCOPY, hidden: true, iconCls : 'x-btn-text', icon: 'img/icons/copy16.png', handler: function() {this.scheduleCopy()}.createDelegate(this)},
			{name:'paste', text:BTN_GRIDPASTE, tooltip: BTN_GRIDPASTE, hidden: true, iconCls : 'x-btn-text', /*icon: 'img/icons/paste16.png',*/ handler: function() {this.schedulePaste()}.createDelegate(this)},
			{name:'sign_actions', key: 'sign_actions', hidden: getRegionNick() == 'kz', text:langs('Подписать'), menu: [
					new Ext.Action({
						name: 'action_signEvnVK',
						text: langs('Подписать протокол ВК'),
						tooltip: langs('Подписать протокол ВК'),
						handler: function() {
							var me = this;
							var rec = win.getGrid().getSelectionModel().getSelected();
							if (rec && rec.get('EvnVK_id')) {
								getWnd('swEMDSignWindow').show({
									EMDRegistry_ObjectName: 'EvnVK',
									EMDRegistry_ObjectID: rec.get('EvnVK_id'),
									callback: function(data) {
										if (data.preloader) {
											me.disable();
										}

										if (data.success || data.error) {
											me.enable();
										}

										if (data.success) {
											win.getGrid().getStore().reload();
										}
									}
								});
							}
						}
					}),
					new Ext.Action({
						name: 'action_showEvnVKVersionList',
						text: langs('Версии документа «Протокол ВК»'),
						tooltip: langs('Версии документа «Протокол ВК»'),
						handler: function() {
							var rec = win.getGrid().getSelectionModel().getSelected();
							if (rec && rec.get('EvnVK_id')) {
								getWnd('swEMDVersionViewWindow').show({
									EMDRegistry_ObjectName: 'EvnVK',
									EMDRegistry_ObjectID: rec.get('EvnVK_id')
								});
							}
						}
					}),
					new Ext.Action({
						name: 'action_signEvnPrescrMse',
						text: langs('Подписать направление на МСЭ'),
						tooltip: langs('Подписать направление на МСЭ'),
						handler: function() {
							var me = this;
							var rec = win.getGrid().getSelectionModel().getSelected();
							if (rec && rec.get('EvnPrescrMse_id')) {
								getWnd('swEMDSignWindow').show({
									EMDRegistry_ObjectName: 'EvnPrescrMse',
									EMDRegistry_ObjectID: rec.get('EvnPrescrMse_id'),
									callback: function(data) {
										if (data.preloader) {
											me.disable();
										}

										if (data.success || data.error) {
											me.enable();
										}

										if (data.success) {
											win.getGrid().getStore().reload();
										}
									}
								});
							}
						}
					}),
					new Ext.Action({
						name: 'action_showEvnPrescrMseVersionList',
						text: langs('Версии документа «Направление на МСЭ»'),
						tooltip: langs('Версии документа «Направление на МСЭ»'),
						handler: function() {
							var rec = win.getGrid().getSelectionModel().getSelected();
							if (rec && rec.get('EvnPrescrMse_id')) {
								getWnd('swEMDVersionViewWindow').show({
									EMDRegistry_ObjectName: 'EvnPrescrMse',
									EMDRegistry_ObjectID: rec.get('EvnPrescrMse_id')
								});
							}
						}
					}), {
						text: 'Подписать Направление на ВМП',
						hidden: getRegionNick() != 'vologda',
						handler: function() {
							var me = this;
							var rec = win.getGrid().getSelectionModel().getSelected();
							if (rec && rec.get('EvnDirectionHTM_id')) {
								getWnd('swEMDSignWindow').show({
									EMDRegistry_ObjectName: 'EvnDirectionHTM',
									EMDRegistry_ObjectID: rec.get('EvnDirectionHTM_id'),
									callback: function(data) {
										if (data.preloader) {
											me.disable();
										}

										if (data.success || data.error) {
											me.enable();
										}

										if (data.success) {
											win.getGrid().getStore().reload();
										}
									}
								});
							}
						}
					}, {
						text: 'Версии документа «Направление на ВМП»',
						hidden: getRegionNick() != 'vologda',
						handler: function() {
							var rec = win.getGrid().getSelectionModel().getSelected();
							if (rec && rec.get('EvnDirectionHTM_id')) {
								getWnd('swEMDVersionViewWindow').show({
									EMDRegistry_ObjectName: 'EvnDirectionHTM',
									EMDRegistry_ObjectID: rec.get('EvnDirectionHTM_id')
								});
							}
						}
					}
			], tooltip: langs('Подписать'), iconCls : 'x-btn-text', icon: 'img/icons/digital-sign16.png', handler: function() {}},
			{name:'refresh', text:BTN_GRIDREFR, tooltip: BTN_GRIDREFR, iconCls : 'x-btn-text', icon: 'img/icons/refresh16.png', handler: function() {this.scheduleRefresh()}.createDelegate(this)},
			{name:'actions', key: 'actions', text:lang['deystviya'], menu: [
				new Ext.Action({name:'collapse_all', text:lang['svernut_vse'], tooltip: lang['svernut_vse'], handler: function() {this.scheduleCollapseDates()}.createDelegate(this)}),
				new Ext.Action({name:'expand_all', text:lang['razvernut_vse'], tooltip: lang['razvernut_vse'], handler: function() {this.scheduleExpandDates()}.createDelegate(this)})
			], tooltip: lang['deystviya'], iconCls : 'x-btn-text', icon: 'img/icons/actions16.png', handler: function() {}},
			{name:'print', text:BTN_GRIDPRINT, tooltip: BTN_GRIDPRINT, iconCls : 'x-btn-text', icon: 'img/icons/print16.png', menu: [
				new Ext.Action({name:'print_rec', text:lang['pechat'], handler: function() {this.schedulePrint('row')}.createDelegate(this)}),
				new Ext.Action({name:'print_all', text:lang['pechat_spiska'], handler: function() {this.schedulePrint()}.createDelegate(this)})
            ]}
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
			id: 'vkwpToolbar',
			items:
			[
				this.gridActions.open_emk,
				this.gridActions.prescrmse,
				this.gridActions.directionhtm,
				this.gridActions.shedule,
				this.gridActions.expert,
				this.gridActions.vk,
				this.gridActions.copy,
				this.gridActions.paste,
				//this.gridActions.mse_vmp_del,
				//this.gridActions.mse_refusal,
				{
					xtype : "tbseparator"
				},
				this.gridActions.sign_actions,
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
			id: 'TimetableMedService_id'
		},
		[{
			name: 'EvnPrescrVK_id'
		}, {
			name: 'pmUser_updID'
		}, {
			name: 'TimetableMedService_id'
		}, {
			name: 'EvnPrescrVK_pid'
		}, {
			name: 'EvnVK_id'
		}, {
			name: 'isExpert'
		}, {
			name: 'EvnVK_IsSigned'
		}, {
			name: 'EvnVK_signDT'
		}, {
			name: 'pmUser_signName'
		}, {
			name: 'EvnPrescrMse_id'
		}, {
			name: 'EvnPrescrMse_IsSigned'
		}, {
			name: 'EvnPrescrMse_SignCount'
		}, {
			name: 'EvnPrescrMse_MinSignCount'
		}, {
			name: 'EvnPrescrMse_signDT'
		}, {
			name: 'EvnMse_id'
		}, {
			name: 'Person_id'
		}, {
			name: 'PersonEvn_id'
		}, {
			name: 'Server_id'
		}, {
			name: 'LpuSection_id'
		}, {
			name: 'MedPersonal_id'
		}, {
			name: 'Diag_id'
		}, {
			name: 'EvnVK_NumCard'
		}, {
			name: 'CauseTreatmentType_id'
		}, {
			name: 'EvnPrescrVK_setDate'
		}, {
			name: 'EvnPrescrVK_setTime'
		}, {
			name: 'CauseTreatmentType_Name'
		}, {
			name: 'EvnDirection_From'
		}, {
			name: 'Diag_Name'
		}, {
			name: 'Person_Fio'
		}, {
			name: 'Person_BirthDay'
		}, {
			name: 'LpuSection_FullName'
		}, {
			name: 'MedPerson_Fio'
		}, {
			name: 'EvnVK_setDT'
		}, {
			name: 'EvnVK_setDT'
		}, {
			name: 'EvnVK_NumProtocol'
		}, {
			name: 'ExpertiseEventType_Code'
		},{
			name: 'ExpertiseEventType_Name'
		},{
			name: 'EvnPrescrMse_setDT'
		}, {
			name: 'EvnDirectionHTM_setDT'
		}, {
			name: 'EvnStatusVK_id'
		}, {
			name: 'VoteListVK_id'
		}, {
			name: 'VoteListVK_isFinished'
		}, {
			name: 'VoteExpertVK_id'
		}, {
			name: 'EvnVK_isAccepted'
		}, {
			name: 'EvnStatus_id'
		}, {
			name: 'EvnStatus_SysNick'
		}, {
			name: 'EvnStatus_Name'
		}, {
			name: 'EvnPrescrMse_appointDT'
		}, {
			name: 'EvnVK_IsFail'
		}, {
			name: 'ExpertiseNameType_id'
		}, {
			name: 'ExpertiseEventType_id'
		}, {
			name: 'EvnMse'
		}, {
			name: 'EvnStickBase_id'
		}, {
			name: 'EvnStick_all'
		}, {
			name: 'EvnVK_LVN'
		}, {
			name: 'EvnVK_Note'
		}, {
			name: 'EvnDirectionHTM_id'
		}, {
			name: 'EvnDirectionHTM_IsSigned'
		}, {
			name: 'EPM_MedService_id'
		}, {
			name: 'EPM_MedService_Name'
		}, {
			name: 'EvnVK_DecisionVK'
		}]);
        
		this.gridStore = new Ext.data.GroupingStore(
		{
			reader: this.reader,
			autoLoad: false,
			url: '/?c=Mse&m=loadEvnPrescrVKGrid',
			sortInfo: 
			{
				field: 'EvnPrescrVK_setDate',
				direction: 'ASC'
			},
			groupField: 'EvnPrescrVK_setDate',
			listeners:
			{
				load: function(store, record, options)
				{
					callback:
					{
						var count = store.getCount();
						var form = Ext.getCmp('swVKWorkPlaceWindow');
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
//								if (!form.gridActions.open.initialConfig.initialDisabled)
//									form.gridActions.open.setDisabled(false);
								//if (!form.gridActions.shedule.items[1].menu.items.items[0].initialConfig.initialDisabled)
									//form.gridActions.shedule.items[1].menu.items.items[0].setDisabled(false);
								if (!form.gridActions.shedule.items[1].menu.items.items[1].initialConfig.initialDisabled)
									form.gridActions.shedule.items[1].menu.items.items[1].setDisabled(false);
//								if (!form.gridActions.queue.initialConfig.initialDisabled)
									//form.gridActions.queue.setDisabled(false);
//								if (!form.gridActions.edit.initialConfig.initialDisabled)
//									form.gridActions.edit.setDisabled(false);
								//if (!form.gridActions.del.initialConfig.initialDisabled)
									//form.gridActions.del.setDisabled(false);
								if (!form.gridActions.copy.initialConfig.initialDisabled)
									form.gridActions.copy.setDisabled(false);
								if (!form.gridActions.refresh.initialConfig.initialDisabled)
									form.gridActions.refresh.setDisabled(false);
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
					var form = Ext.getCmp('swVKWorkPlaceWindow');
//					form.gridActions.open.setDisabled(true);
					//form.gridActions.shedule.items[1].menu.items.items[0].setDisabled(true);
					//form.gridActions.shedule.items[1].menu.items.items[1].setDisabled(true);
//					form.gridActions.queue.setDisabled(true);
//					form.gridActions.edit.setDisabled(true);
					form.gridActions.copy.setDisabled(true);
					form.gridActions.shedule.items[1].menu.items.items[2].setDisabled(true);
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
			var form = Ext.getCmp('swVKWorkPlaceWindow');
			if (e.getKey() == Ext.EventObject.ENTER)
			{
				e.stopEvent();
				form.scheduleLoad('period');
			}
		});
		this.dateMenu.addListener('select',function () 
		{
			// Читаем расписание за период
			var form = Ext.getCmp('swVKWorkPlaceWindow');
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
					collapsed: true,
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
									labelWidth: 70,
									items:
									[{
										border: false,
										hidden: true,
										layout: 'form',
										items: [{
											xtype: 'swmedserviceglobalcombo',
											disable: true
										}]
									},{
										xtype: 'textfieldpmw',
										anchor: '100%',
										id: 'vkwpSearch_SurName',
										name: 'Person_SurName',
										fieldLabel: lang['familiya'],
										listeners: {
											'keydown': function (inp, e) {
												var form = Ext.getCmp('swVKWorkPlaceWindow');
												if (e.getKey() == Ext.EventObject.ENTER) {
													e.stopEvent();
													form.setTitleFieldSet();
													form.scheduleLoad();
												}
											}
										}
									},{
										xtype: 'textfieldpmw',
										anchor: '100%',
										id: 'vkwpSearch_FirName',
										name: 'Person_FirName',
										fieldLabel: lang['imya'],
										listeners: {
											'keydown': function (inp, e) {
												var form = Ext.getCmp('swVKWorkPlaceWindow');
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
										anchor: '100%',
										id: 'vkwpSearch_SecName',
										name: 'Person_SecName',
										fieldLabel: lang['otchestvo'],
										listeners: {
											'keydown': function (inp, e) {
												var form = Ext.getCmp('swVKWorkPlaceWindow');
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
										id: 'vkwpSearch_BirthDay',
										name: 'Person_BirthDay',
										fieldLabel: lang['dr'],
										listeners: {
											'keydown': function (inp, e) {
												var form = Ext.getCmp('swVKWorkPlaceWindow');
												if (e.getKey() == Ext.EventObject.ENTER) {
													e.stopEvent();
													form.setTitleFieldSet();
													form.scheduleLoad();
												}
											}
										}
									},
									{
										xtype: 'swcommonsprcombo',
										comboSubject: 'CauseTreatmentType',
										labelStyle: 'margin-top: -5px',
										showCodefield: false,
										anchor: '100%',
										name: 'CauseTreatmentType_id',
										fieldLabel: 'Причина обращения'
									}]
								}, {
									layout: 'form',
									labelWidth: 140,
									items:
									[{
										xtype: 'combo',
										anchor: '100%',
										mode: 'local',
										hiddenName: 'isEvnVK',
										displayField: 'isEvnVK_Text',
										valueField: 'isEvnVK_id',
										store: new Ext.data.SimpleStore({
											autoLoad: true,
											fields:
											[
												{name:'isEvnVK_id', type:'int'},
												{name:'isEvnVK_Text', type:'string'}
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
										fieldLabel: lang['protokol_vk']
									},
									{
										xtype: 'combo',
										anchor: '100%',
										mode: 'local',
										hiddenName: 'isEvnPrescrMse',
										displayField: 'isEvnPrescrMse_Text',
										valueField: 'isEvnPrescrMse_id',
										store: new Ext.data.SimpleStore({
											autoLoad: true,
											fields:
											[
												{name:'isEvnPrescrMse_id', type:'int'},
												{name:'isEvnPrescrMse_Text', type:'string'}
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
										fieldLabel: lang['napr-e_na_mse']
									},
									{
										xtype: 'combo',
										mode: 'local',
										hiddenName: 'isEvnMse',
										displayField: 'isEvnMse_Text',
										valueField: 'isEvnMse_id',
										store: new Ext.data.SimpleStore({
											autoLoad: true,
											fields:
											[
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
										xtype: 'swevnstatuscombo',
										hiddenName: 'EvnStatus_id',
										fieldLabel: 'Статус напр-я на МСЭ',
										width: 200,
										allowBlank: false
									}]
								},
								{
									layout: 'form',
									items: 
									[{
										style: "padding-left: 20px",
										xtype: 'button',
										id: 'vkwpBtnSearch',
										text: lang['nayti'],
										iconCls: 'search16',
										handler: function()
										{
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
										id: 'vkwpBtnClear',
										text: lang['sbros'],
										iconCls: 'clear16',
										handler: function()
										{
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
							id: 'vkwpLpu_id',
							lastQuery: '',
							width : 300,
							xtype: 'swlpulocalcombo',
							listeners: 
							{
								change: function(combo, nv, ov)
								{
									var form = Ext.getCmp('swVKWorkPlaceWindow');
									form.MedPersonalPanel.findById('vkwpMedPersonal_id').getStore().load(
									{
										params:
										{
											Lpu_id: nv
										},
										callback: function()
										{
											form.MedPersonalPanel.findById('vkwpMedPersonal_id').setValue('');
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
							id: 'vkwpMedPersonal_id',
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
							id: 'vkwpBtnMPSearch',
							text: lang['pokazat_raspisanie'],
							iconCls: 'search16',
							handler: function()
							{
								var form = Ext.getCmp('swVKWorkPlaceWindow');
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
							id: 'vkwpBtnMPClose',
							text: lang['vernutsya_k_patsientu'],
							iconCls: 'close16',
							handler: function()
							{
								var form = Ext.getCmp('swVKWorkPlaceWindow');
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
											ARMType: sw.Promed.swVKWorkPlaceWindow.ARMType,
											callback: function() 
											{
												if (this.mode == 'workplace')
												{
													// Открываем форму и если formMode был = send, то все вертаем назад на сохраненные значения 
													getWnd('swVKWorkPlaceWindow').show({formMode:'open', ARMType: sw.Promed.swVKWorkPlaceWindow.ARMType});
												}
											}
										});
									}
									else 
									{
										getWnd('swVKWorkPlaceWindow').show({formMode:'open'});
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
			autoExpandColumn: 'autoexpand',
			tbar: this.gridToolbar,
			store: this.gridStore,
			loadMask: true,
			stripeRows: true,
			columns:
			[{
				key: true,
				hidden: true,
				hideable: false,
				dataIndex: 'EvnPrescrVK_id'
			},
			{
				hidden: true,
				hideable: false,
				dataIndex: 'pmUser_updID'
			},
			{
				hidden: true,
				hideable: false,
				dataIndex: 'EvnPrescrVK_pid'
			},
			{
				hidden: true,
				hideable: false,
				dataIndex: 'EvnVK_id'
			},
			{
				hidden: true,
				hideable: false,
				dataIndex: 'isExpert'
			},
			{
				hidden: true,
				hideable: false,
				dataIndex: 'EvnPrescrMse_id'
			},
			{
				hidden: true,
				hideable: false,
				dataIndex: 'EvnMse_id'
			},
			{
				hidden: true,
				hideable: false,
				dataIndex: 'EPM_MedService_id'
			},
			{
				hidden: true,
				hideable: false,
				dataIndex: 'Person_id'
			},
			{
				hidden: true,
				hideable: false,
				dataIndex: 'PersonEvn_id'
			},
			{
				hidden: true,
				hideable: false,
				dataIndex: 'Server_id'
			},
			{
				hidden: true,
				hideable: false,
				dataIndex: 'LpuSection_id'
			},
			{
				hidden: true,
				hideable: false,
				dataIndex: 'MedPersonal_id'
			},
			{
				hidden: true,
				hideable: false,
				dataIndex: 'Diag_id'
			},
			{
				hidden: true,
				hideable: false,
				dataIndex: 'EvnVK_NumCard'
			},
			{
				hidden: true,
				hideable: false,
				dataIndex: 'CauseTreatmentType_id'
			},
			{
				hidden: true,
				hideable: false,
				dataIndex: 'TimetableMedService_id'
			},
			{
				hidden: true,
				hideable: false,
				dataIndex: 'EvnStickBase_id'
			},
			{
				hidden: true,
				hideable: false,
				dataIndex: 'EvnStick_all'
			},
			{
				hidden: true,
				hideable: false,
				dataIndex: 'EvnVK_LVN'
			},
			{
				hidden: true,
				hideable: false,
				dataIndex: 'EvnVK_Note'
			},
			{
				hidden: true,
				hideable: false,
				dataIndex: 'EvnDirectionHTM_id'
			},
			{
				hidden: true,
				hideable: false,
				dataIndex: 'EvnDirectionHTM_IsSigned'
			},
			{
				hidden: true,
				hideable: false,
				dataIndex: 'ExpertiseEventType_Code'
			},
			{
				hidden: true,
				hideable: false,
				dataIndex: 'EvnVK_IsFail'
			},
			{
				hidden: true,
				hideable: false,
				dataIndex: 'EvnStatusVK_id'
			},
			{
				hidden: true,
				hideable: false,
				dataIndex: 'VoteListVK_id'
			},
			{
				hidden: true,
				hideable: false,
				dataIndex: 'VoteListVK_isFinished'
			},
			{
				hidden: true,
				hideable: false,
				dataIndex: 'VoteExpertVK_id'
			},
			{
				hidden: true,
				hideable: false,
				dataIndex: 'EvnVK_isAccepted'
			},
			{
				hidden: true,
				hideable: false,
				dataIndex: 'EvnStatus_id'
			},
			{
				header: lang['data'],
				width: 80,
				hidden: true,
				sortable: true,
				dataIndex: 'EvnPrescrVK_setDate'
			},
			{
				header: lang['zapis'],
				width: 60,
				sortable: true,
				dataIndex: 'EvnPrescrVK_setTime'
			}, 
			{
				header: "Причина обращения",
				width: 150,
				sortable: true,
				dataIndex: 'CauseTreatmentType_Name'
			}, 
			{
				header: 'Кем направлен',
				width: 120,
				sortable: true,
				dataIndex: 'EvnDirection_From'
			},
			{
				header: "Диагноз основной",
				width: 150,
				sortable: true,
				dataIndex: 'Diag_Name'
			},
			{
				header: "Фамилия Имя Отчество",
				width: 200,
				sortable: true,
				dataIndex: 'Person_Fio'
			},
			{
				header: "Дата рождения",
				width: 100,
				sortable: true,
				dataIndex: 'Person_BirthDay'
			},
			{
				header: lang['otdelenie'],
				width: 100,
				sortable: true,
				dataIndex: 'LpuSection_FullName'
			},
			{
				header: lang['vrach'],
				width: 150,
				sortable: true,
				dataIndex: 'MedPerson_Fio'
			},
			{
				header: "Дата экспертизы",
				width: 120,
				sortable: true,
				dataIndex: 'EvnVK_setDT'
			},
			{
				header: "Вид экспертизы",
				width: 120,
				sortable: true,
				dataIndex: 'ExpertiseNameType_Name'
			},
			{
				header: "Характеристика случая экспертизы",
				width: 100,
				sortable: true,
				dataIndex: 'ExpertiseEventType_Name'
			},
			{
				header: "Направление на МСЭ",
				width: 140,
				sortable: true,
				dataIndex: 'EvnPrescrMse_setDT',
				renderer: function(v, p, r){
					if (r.get('EvnVK_IsFail') == 2) {
						return 'Отказано';
					} else if (v != null) {
						var signTxt = '';
						if (!Ext.isEmpty(r.get('EvnPrescrMse_IsSigned'))) {
							if (r.get('EvnPrescrMse_IsSigned') == 2) {
								if (r.get('EvnPrescrMse_SignCount') == r.get('EvnPrescrMse_MinSignCount')) {
									signTxt += '<img src="/img/icons/emd/doc_signed.png">';
								} else {
									signTxt += '<span class="sp_doc_signed" data-qtip="' + r.get('EvnPrescrMse_SignCount') + ' из ' + r.get('EvnPrescrMse_MinSignCount') + '">' + r.get('EvnPrescrMse_SignCount') + '</span>';
								}
							} else {
								signTxt += '<img src="/img/icons/emd/doc_notactual.png">';
							}

							signTxt += r.get('EvnPrescrMse_signDT');
						} else if (r.get('EvnPrescrMse_SignCount') > 0) {
							signTxt = '<span class="sp_doc_unsigned" data-qtip="' + r.get('EvnPrescrMse_SignCount') + ' из ' + r.get('EvnPrescrMse_MinSignCount') + '">' + r.get('EvnPrescrMse_SignCount') + '</span>';
						} else {
							signTxt = '<img src="/img/icons/emd/doc_notsigned.png">';
						}

						return '<a href="javascript:">' + v + '</a> (' + signTxt + ')';
					} else {
						return '';
					}
				}
			},
			{
				header: "Статус направления на МСЭ",
				width: 140,
				sortable: true,
				dataIndex: 'EvnStatus_Name'
			},
			{
				header: "Дата и время назначения МСЭ",
				width: 140,
				sortable: true,
				dataIndex: 'EvnPrescrMse_appointDT'
			},
			{
				header: "Обратный талон",
				id: 'autoexpand',
				sortable: true,
				dataIndex: 'EvnMse',
				renderer: function(v, p, rec){
					if(v != '')
						return '<a href="javascript:">'+v+'</a>';
					else
						return '';
				}
			},
			{
				header: "Служба МСЭ",
				sortable: true,
				dataIndex: 'EPM_MedService_Name',
				renderer: function(v, p, rec){
					if(!Ext.isEmpty(v))
						return '<a href="javascript:">'+v+'</a>';
					else
						return '';
				}
			},
			{
				header: "Направление на ВМП",
				width: 140,
				sortable: true,
				dataIndex: 'EvnDirectionHTM_setDT',
				renderer: function(v, p, rec){
					if(rec.get('EvnVK_IsFail') == 2)
						return 'Отказано';
					else if(v != null)
						return '<a href="javascript:">'+v+'</a>';
					else
						return '';
				}
			}, {
				header: langs('Документ подписан'),
				width: 200,
				sortable: true,
				dataIndex: 'EvnVK_IsSigned',
				renderer: function(v, p, r) {
					var s = '';
					if (!Ext.isEmpty(r.get('EvnVK_id'))) {
						if (!Ext.isEmpty(r.get('EvnVK_IsSigned'))) {
							if (r.get('EvnVK_IsSigned') == 2) {
								s += '<img src="/img/icons/emd/doc_signed.png">';
							} else {
								s += '<img src="/img/icons/emd/doc_notactual.png">';
							}

							s += r.get('EvnVK_signDT') + ' ' + r.get('pmUser_signName');
						} else {
							s += '<img src="/img/icons/emd/doc_notsigned.png">';
						}
					}
					return s;
				}
			}],
			
			view: new Ext.grid.GroupingView(
			{
				//forceFit: true,
				enableGroupingMenu:false,
				groupTextTpl: '{[values.group]} ({[values.rs.length]} {[values.rs.length.inlist([1,21,31]) ? "запись" : values.rs.length.inlist([2,3,4,22,23,24,32,33,34]) ? "записи" : "записей"]})'
			}),
			loadStore: function(params)
			{
				if (!this.params)
					this.params = null;
				if (params) {
					this.params = params;
				}
				this.clearStore();
				this.getStore().load({params: this.params, callback: function(){this.getStore().sort('EvnPrescrVK_setTime','ASC');}.createDelegate(this)});
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
						var form = Ext.getCmp('swVKWorkPlaceWindow');
                        
						if (!this.grid.getTopToolbar().hidden) {
							this.grid.getTopToolbar().items.last().el.innerHTML = '1 / ' + this.grid.getStore().getCount();
						}
						// if(record.get('EvnVK_id') != null && record.get('EvnVK_id') != '' && record.get('EvnVK_IsFail') != 2){
						// 	form.gridActions.prescrmse.items[1].menu.items.items[0].setDisabled(false);
						// } else {
						// 	form.gridActions.prescrmse.items[1].menu.items.items[0].setDisabled(true);
						// }
						if(
							(record.get('EvnPrescrMse_id') != null && record.get('EvnPrescrMse_id') != '')
							||
							(record.get('EvnVK_id') != null && record.get('EvnVK_id') != '' && record.get('ExpertiseNameType_id') == 2 && Date.parseDate(record.get('EvnVK_setDT'), 'd.m.Y') < new Date(2019,8,16) )
						){
							form.gridActions.prescrmse.setDisabled(false);
							form.gridActions.prescrmse.items[1].menu.items.items[0].setDisabled(Ext.isEmpty(record.get('EvnPrescrMse_id')));
							form.gridActions.prescrmse.items[1].menu.items.items[1].setDisabled(!Ext.isEmpty(record.get('EvnPrescrMse_id')));
						}else{
							form.gridActions.prescrmse.setDisabled(true);
							form.gridActions.prescrmse.items[1].menu.items.items[0].setDisabled(true);
							form.gridActions.prescrmse.items[1].menu.items.items[1].setDisabled(true);
						}

						if ((
							getRegionNick() != 'vologda' &&
							record.get('EvnVK_id') != null &&
							record.get('EvnVK_id') != '' &&
							record.get('EvnVK_IsFail') != 2 &&
							record.get('ExpertiseNameType_id') == 5 &&
							record.get('ExpertiseEventType_id') == 61
						) || (
							getRegionNick() == 'vologda' && 
							!!record.get('EvnDirectionHTM_id')
						)) {
							form.gridActions.directionhtm.setDisabled(false);
							form.gridActions.directionhtm.items[1].menu.items.items[0].setDisabled(!Ext.isEmpty(record.get('EvnDirectionHTM_id')));
							form.gridActions.directionhtm.items[1].menu.items.items[1].setDisabled(Ext.isEmpty(record.get('EvnDirectionHTM_id')));
						} else {
							form.gridActions.directionhtm.setDisabled(true);
							form.gridActions.directionhtm.items[1].menu.items.items[0].setDisabled(true);
							form.gridActions.directionhtm.items[1].menu.items.items[1].setDisabled(true);
						}

						if(
							record.get('EvnVK_id') != null &&
							record.get('EvnPrescrMse_id') != null && record.get('EvnPrescrMse_id') != '' &&
							record.get('EvnStatus_SysNick') &&
							record.get('EvnStatus_SysNick').inlist(['New', 'Rework']) &&
							(
								getRegionNick() != 'vologda' || (
									record.get('EvnPrescrMse_IsSigned') == 2 &&
									record.get('EvnPrescrMse_SignCount') == record.get('EvnPrescrMse_MinSignCount')
								)
							)
						){
							form.gridActions.prescrmse.items[1].menu.items.items[4].setDisabled(false);
						} else {
							form.gridActions.prescrmse.items[1].menu.items.items[4].setDisabled(true);
						}

						if(
							record.get('EvnVK_id') != null && record.get('EvnVK_id') != '' &&
							record.get('EvnPrescrMse_id') != null && record.get('EvnPrescrMse_id') != '' &&
							record.get('EvnStatus_SysNick') &&
							record.get('EvnStatus_SysNick').inlist(['New', 'Rework'])
						){
							form.gridActions.prescrmse.items[1].menu.items.items[2].setDisabled(false);
						} else {
							form.gridActions.prescrmse.items[1].menu.items.items[2].setDisabled(true);
						}
						if (
							!Ext.isEmpty(record.get('EvnVK_id')) && !Ext.isEmpty(record.get('EvnPrescrMse_id'))
							&& (
								(record.get('EvnStatus_SysNick') == 'New' && (record.get('EvnVK_isAccepted') == 1 || getRegionNick() != 'vologda'))
								|| record.get('EvnStatus_SysNick') == 'RefusalDir'
							)
							&& record.get('CauseTreatmentType_id') == 5
						) {
							form.gridActions.prescrmse.items[1].menu.items.items[3].setDisabled(false);
						} else {
							form.gridActions.prescrmse.items[1].menu.items.items[3].setDisabled(true);
						}
						
						if(record.get('EvnVK_IsFail') == 2){
							form.gridActions.prescrmse.items[1].menu.items.items[3].setText('Отменить отказ');
						} else {
							form.gridActions.prescrmse.items[1].menu.items.items[3].setText('Отказ в направлении на МСЭ');
						}

						if (getRegionNick() == 'vologda') {
							form.gridActions.expert.items[1].menu.items.items[0].setDisabled(!Ext.isEmpty(record.get('VoteListVK_id')) || !Ext.isEmpty(record.get('EvnVK_id')));
							form.gridActions.expert.items[1].menu.items.items[1].setDisabled(Ext.isEmpty(record.get('VoteListVK_id')) || !Ext.isEmpty(record.get('EvnVK_id')));
							form.gridActions.expert.items[1].menu.items.items[2].setDisabled(Ext.isEmpty(record.get('VoteExpertVK_id')) || !Ext.isEmpty(record.get('EvnVK_id')));
						}

						form.gridActions.sign_actions.items[0].menu.items.items[0].setDisabled(Ext.isEmpty(record.get('EvnVK_id')));
						form.gridActions.sign_actions.items[0].menu.items.items[1].setDisabled(Ext.isEmpty(record.get('EvnVK_id')));
						form.gridActions.sign_actions.items[0].menu.items.items[2].setDisabled(Ext.isEmpty(record.get('EvnPrescrMse_id')));
						form.gridActions.sign_actions.items[0].menu.items.items[3].setDisabled(Ext.isEmpty(record.get('EvnPrescrMse_id')));

						form.gridActions.sign_actions.items[1].menu.items.items[0].setDisabled(Ext.isEmpty(record.get('EvnVK_id')));
						form.gridActions.sign_actions.items[1].menu.items.items[1].setDisabled(Ext.isEmpty(record.get('EvnVK_id')));
						form.gridActions.sign_actions.items[1].menu.items.items[2].setDisabled(Ext.isEmpty(record.get('EvnPrescrMse_id')));
						form.gridActions.sign_actions.items[1].menu.items.items[3].setDisabled(Ext.isEmpty(record.get('EvnPrescrMse_id')));

						if (getRegionNick() == 'vologda') {
							form.gridActions.sign_actions.items[1].menu.items.items[0].setDisabled(Ext.isEmpty(record.get('EvnVK_id')) || record.get('isExpert') != 2);
							form.gridActions.sign_actions.items[1].menu.items.items[1].setDisabled(Ext.isEmpty(record.get('EvnVK_id')) || record.get('EvnVK_IsSigned') != 2);
							form.gridActions.sign_actions.items[1].menu.items.items[2].setDisabled(Ext.isEmpty(record.get('EvnPrescrMse_id')) || record.get('isExpert') != 2 || record.get('EvnVK_isAccepted') != 2);
							form.gridActions.sign_actions.items[1].menu.items.items[3].setDisabled(Ext.isEmpty(record.get('EvnPrescrMse_id')) || record.get('EvnPrescrMse_IsSigned') != 2);
							form.gridActions.sign_actions.items[1].menu.items.items[4].setDisabled(Ext.isEmpty(record.get('EvnDirectionHTM_id')) || record.get('isExpert') != 2 || record.get('EvnVK_isAccepted') != 2);
							form.gridActions.sign_actions.items[1].menu.items.items[5].setDisabled(Ext.isEmpty(record.get('EvnDirectionHTM_id')) || record.get('EvnDirectionHTM_IsSigned') != 2);
						}

						// Проверка ввода человека
						if (!form.readOnly)
						{
							if ((record.get('Person_id')==null) || (record.get('Person_id')==''))
							{
								var TimetableGraf_Date = record.get('TimetableGraf_Date');
								var current_date = Date.parseDate(form.curDate, 'd.m.Y');
								/*if (!form.gridActions.open.initialConfig.initialDisabled)
									form.gridActions.open.setDisabled(true);*/
								if (!form.gridActions.shedule.items[1].menu.items.items[0].initialConfig.initialDisabled)
									// блокируем кнопку "Без записи" в предыдущих днях, т.к. запись все равно происходит на текущий день
									form.gridActions.shedule.items[1].menu.items.items[0].setDisabled(current_date > TimetableGraf_Date || getRegionNick() == 'vologda');
								if (!form.gridActions.shedule.items[1].menu.items.items[1].initialConfig.initialDisabled)
									// запрещаем запись пациента на прошедшую дату
									form.gridActions.shedule.items[1].menu.items.items[1].setDisabled(current_date > TimetableGraf_Date);
								//if (!form.gridActions.queue.initialConfig.initialDisabled)
									// запрещаем запись из очереди на прошедшую дату
									//form.gridActions.queue.setDisabled(current_date > TimetableGraf_Date);
								//if (!form.gridActions.edit.initialConfig.initialDisabled)
									//form.gridActions.edit.setDisabled(true);
								if (!form.gridActions.copy.initialConfig.initialDisabled)
									form.gridActions.copy.setDisabled(true);
								if (!form.gridActions.paste.initialConfig.initialDisabled)
									form.gridActions.paste.setDisabled(true);
								if (!form.gridActions.shedule.items[1].menu.items.items[2].initialConfig.initialDisabled)
									form.gridActions.shedule.items[1].menu.items.items[2].setDisabled(true);
							}
							else
							{
								/*if (!form.gridActions.open.initialConfig.initialDisabled)
									form.gridActions.open.setDisabled(false);*/
								if (!form.gridActions.shedule.items[1].menu.items.items[0].initialConfig.initialDisabled)
									form.gridActions.shedule.items[1].menu.items.items[0].setDisabled(getRegionNick() == 'vologda');
								//if (!form.gridActions.shedule.items[1].menu.items.items[1].initialConfig.initialDisabled)
									//form.gridActions.shedule.items[1].menu.items.items[1].setDisabled(true);
								//if (!form.gridActions.queue.initialConfig.initialDisabled)
									//form.gridActions.queue.setDisabled(true);
								//if (!form.gridActions.edit.initialConfig.initialDisabled)
									//form.gridActions.edit.setDisabled(false);
								if (!form.gridActions.copy.initialConfig.initialDisabled)
									form.gridActions.copy.setDisabled(false);
								if (!form.gridActions.paste.initialConfig.initialDisabled)
									form.gridActions.paste.setDisabled(false);
								//form.gridActions.shedule.items[1].menu.items.items[2].setDisabled(false);
								// (!record.get('MedStaffFact_id').inlist(getGlobalOptions().medstafffact)) ||

								var TimetableGraf_Date = record.get('TimetableGraf_Date');
								var current_date = Date.parseDate(form.curDate, 'd.m.Y');
								var del_disabled = ((record.get('EvnVK_id')!=null) && (record.get('EvnVK_id')!='')); //Если протокол ВК не создан, то можно освободить запись.
								if (!form.gridActions.shedule.items[1].menu.items.items[2].initialConfig.initialDisabled)
									form.gridActions.shedule.items[1].menu.items.items[2].setDisabled( // Disabled where
										del_disabled
										/*(!isAdmin) // this user
										&& (
										(record.get('pmUser_updId') != getGlobalOptions().pmuser_id) // this other autor of record
										|| (current_date > TimetableGraf_Date)
										|| (current_date.format('d.m.Y') == TimetableGraf_Date.format('d.m.Y') && record.get('Person_IsEvents') == 'true') // in current day opened TAP
										)*/
									);
							}
						}

						if (getRegionNick() == 'vologda') {
							form.gridActions.vk.items[1].menu.items.items[0].setDisabled(!Ext.isEmpty(record.get('EvnVK_id')) || record.get('VoteListVK_isFinished') != 2);
							form.gridActions.vk.items[1].menu.items.items[1].setDisabled(!Ext.isEmpty(record.get('EvnVK_id')));
						}

						if (
							((record.get('EvnVK_id')!=null) && (record.get('EvnVK_id')!=''))
							&&
							((record.get('EvnPrescrMse_setDT')==null) || (record.get('EvnPrescrMse_setDT')==''))
							&&
							((record.get('EvnMse')==null) || (record.get('EvnMse')==''))
							&&
							(
								(record.get('ExpertiseEventType_Code') != 14)
								||(record.get('ExpertiseEventType_Code') == 14 && (record.get('EvnDirectionHTM_id') == null || record.get('EvnDirectionHTM_id') == ''))
							)
						)
						{
							form.gridActions.vk.items[1].menu.items.items[3].setDisabled(false);
						}
						else
							form.gridActions.vk.items[1].menu.items.items[3].setDisabled(true);

						if (getRegionNick() != 'vologda' || !Ext.isEmpty(record.get('EvnVK_id'))) {
							form.gridActions.vk.items[1].menu.items.items[2].setDisabled(false);
						} else {
							form.gridActions.vk.items[1].menu.items.items[2].setDisabled(true);
						}

						if (
							((record.get('EvnVK_id')!=null) && (record.get('EvnVK_id')!=''))
							&&
							( record.get('EvnPrescrMse_setDT')!=null )
							&&
							((record.get('EvnMse')==null) || (record.get('EvnMse')==''))
						)
						{
							form.gridActions.prescrmse.items[1].menu.items.items[5].setDisabled(false);
						}
						else
							form.gridActions.prescrmse.items[1].menu.items.items[5].setDisabled(true);

						if (
							((record.get('EvnVK_id')!=null) && (record.get('EvnVK_id')!=''))
							&&
							( record.get('EvnDirectionHTM_id')!=null )
						)
						{
							form.gridActions.directionhtm.items[1].menu.items.items[2].setDisabled(false);
						}
						else
							form.gridActions.directionhtm.items[1].menu.items.items[2].setDisabled(true);
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
			var args = {};
			args.EvnPrescrVK_id = rec.get('EvnPrescrVK_id');
			args.MedService_id = this.MedService_id;
			if (getRegionNick() == 'vologda' && !rec.get('EvnVK_id')) {
				return false;
			}
			if(rec.get('EvnPrescrVK_id')){
				args.onHide = function(){
					this.scheduleRefresh();
				}.createDelegate(this)
				
				if( rec.get('EvnPrescrVK_id') > 0 && rec.get('TimetableMedService_id') > 0 ) {
					args.MedPersonal_id = rec.get('MedPersonal_id');
				}
				
				if(rec.get('EvnVK_id') == null) {
					args.showtype = 'add';
					args.PersonData = {
						Person_id: rec.get('Person_id'),
						Server_id: rec.get('Server_id')
					};
					//args.LpuSection_id = rec.get('LpuSection_id');
					
					args.Diag_id = rec.get('Diag_id');
					args.CauseTreatmentType_id = rec.get('CauseTreatmentType_id');
					
					args.EvnVK_NumCard = rec.get('EvnVK_NumCard');
					args.EvnPrescrVK_pid = rec.get('EvnPrescrVK_pid');

					args.EvnStickBase_id = rec.get('EvnStickBase_id');
					args.EvnStick_all = rec.get('EvnStick_all');
					args.EvnVK_LVN = rec.get('EvnVK_LVN');
					args.EvnVK_Note = rec.get('EvnVK_Note');

					if(rec.get('EvnPrescrMse_id')){
						args.EvnPrescrMse_id = rec.get('EvnPrescrMse_id');
					}

					win.getLoadMask().show();
					Ext.Ajax.request({
						url: '/?c=ClinExWork&m=getNewEvnVKNumber',
						method: 'POST',
						callback: function(options, success, response)
						{
							win.getLoadMask().hide();
							if(success)	{
								var result = Ext.util.JSON.decode(response.responseText);
								args.EvnVK_NumProtocol = parseInt(result[0].EvnVK_NumProtocol) + 1;
								getWnd('swClinExWorkEditWindow').show(args);
							} else {
								sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_opredelit_nomer_novogo_protokola_vk']);
							}
						}
					});
				} else {
					args.showtype = 'edit';
					args.EvnVK_id = rec.get('EvnVK_id');
					if(rec.get('EvnPrescrMse_id')){
						args.EvnPrescrMse_id = rec.get('EvnPrescrMse_id');
					}
					getWnd('swClinExWorkEditWindow').show(args);
				}
			} else {
				if(rec.get('EvnVK_id')){
					args.onHide = function(){
						this.scheduleRefresh();
					}.createDelegate(this)
					args.showtype = 'edit';
					args.EvnVK_id = rec.get('EvnVK_id');
					getWnd('swClinExWorkEditWindow').show(args);
				} else {
					win.scheduleAdd();
				}
			}
		}.createDelegate(this));
		
		this.ScheduleGrid.on('celldblclick', function(grid, row, col, object)
		{
			
		});
		
		this.ScheduleGrid.on('cellclick', function(grid, rowIdx, colIdx) {
			var win = this;
			var flag_idx = grid.getColumnModel().findColumnIndex('EvnPrescrMse_setDT');
			var flag1_idx = grid.getColumnModel().findColumnIndex('EvnDirectionHTM_setDT');
			var flag2_idx = grid.getColumnModel().findColumnIndex('EvnMse');
			var flag3_idx = grid.getColumnModel().findColumnIndex('EPM_MedService_Name');
			var rec = grid.getSelectionModel().getSelected();
			if(colIdx == flag_idx){
				if(!rec || rec.get('EvnPrescrMse_id') == null) return false;
				getWnd('swDirectionOnMseEditForm').show({
					action: 'view',
					Person_id: rec.get('Person_id'),
					Server_id: rec.get('Server_id'),
					EvnPrescrMse_id: rec.get('EvnPrescrMse_id'),
					EvnPL_id: rec.get('EvnPrescrVK_pid'),
					onClose: function(){
						//Если открываем только на просмотр рефрешить нет смысла
						//this.scheduleRefresh();
					}.createDelegate(this)
				});
			}
			if(colIdx == flag1_idx){
				if(!rec || rec.get('EvnDirectionHTM_id') == null) return false;
				getWnd('swDirectionOnHTMEditForm').show({
					Person_id: rec.get('Person_id'),
					Server_id: rec.get('Server_id'),
					EvnDirectionHTM_id: rec.get('EvnDirectionHTM_id'),
					action: 'view'
				});
			}
			if(colIdx == flag2_idx){
				if(!Ext.isEmpty(rec.get('EvnMse_id'))){
					getWnd('swProtocolMseEditForm').show({
						action: 'view',
						Person_id: rec.get('Person_id'),
						Server_id: rec.get('Server_id'),
						EvnPrescrMse_id: rec.get('EvnPrescrMse_id'),
						EvnMse_id: rec.get('EvnMse_id'),
						onHide: function(){
							this.scheduleRefresh();
						}.createDelegate(this)
					});
				}
			}
			if(colIdx == flag3_idx){
				if(!Ext.isEmpty(rec.get('EPM_MedService_id'))){
					getWnd('swSelectMedServiceWindow').show({
						action: rec.get('EvnStatus_SysNick') == 'New' ? 'edit' : 'view',
						isRecord: true, // на запись
						ARMType: 'mse',
						MedService_id: rec.get('EPM_MedService_id'),
						onSelect: function(msData) {
							Ext.Ajax.request({
								url: '/?c=Mse&m=setEpmMedService',
								callback: function(options, success, response) {
									if (success) {
										win.scheduleRefresh();
									}
								},
								params: {
									EvnPrescrMse_id: rec.get('EvnPrescrMse_id'),
									MedService_id: msData.MedService_id
								}
							});
						}
					});
				}
			}
		}.createDelegate(this));
		
		// Добавляем события на keydown
		this.ScheduleGrid.on('keydown', function(e)
		{
			var win = Ext.getCmp('swVKWorkPlaceWindow');
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
						var o = win.findById('vkwpBtnClear');
					}
					else
					{
						//var o = win.findById('vkwpSearch_FIO');
						var o = win.findById('vkwpSearch_SurName');
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
							if(!Ext.isEmpty(grid) && !Ext.isEmpty(grid.getStore())){
							    params.onHide = function(){grid.getStore().reload()};
							}
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
						if(!Ext.isEmpty(grid) && !Ext.isEmpty(grid.getStore())){
						    params.onHide = function(){grid.getStore().reload()};
						}
						ShowWindow('swPersonEditWindow', params);
						return false;
					}
					break;
				case e.F11: // История лечения
					if (grid.hasPersonData() && !e.altKey && !e.ctrlKey && !e.shiftKey && isPerson)
					{
						if(!Ext.isEmpty(grid) && !Ext.isEmpty(grid.getStore())){
						    params.onHide = function(){grid.getStore().reload()};
						}
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
							if(!Ext.isEmpty(grid) && !Ext.isEmpty(grid.getStore())){
							    params.onHide = function(){grid.getStore().reload()};
							}
							ShowWindow('swPersonDispHistoryWindow', params);
						}
						else if (!e.altKey && !e.shiftKey && isPerson)
						{ // Льготы
							if(!Ext.isEmpty(grid) && !Ext.isEmpty(grid.getStore())){
							    params.onHide = function(){grid.getStore().reload()};
							}
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
			action_Timetable: 
			{
				nn: 'action_Timetable',
				tooltip: lang['rabota_s_raspisaniem_vk'],
				text: lang['raspisanie'],
				iconCls : 'mp-timetable32',
				disabled: false,
				//hidden: !IS_DEBUG,
				handler: function() {
					getWnd('swTTMSScheduleEditWindow').show({
						MedService_id: this.MedService_id,
						MedService_Name: this.MedService_Name,
						userClearTimeMS: function() {
							this.getLoadMask(lang['osvobojdenie_zapisi']).show();
							Ext.Ajax.request({
								url: '/?c=Mse&m=clearTimeMSOnEvnPrescrVK',
								params: {
									TimetableMedService_id: this.TimetableMedService_id
								},
								callback: function(o, s, r) {
									this.getLoadMask().hide();
									if(s) {
										this.scheduleLoad();
									}
								}.createDelegate(this)
							});
						}
					});
				}.createDelegate(this)
			},
			action_EvnPrescrMse:
			{
				nn: 'action_EvnPrescrMse',
				tooltip: lang['otkryit_sozdat_napravlenie_na_mse'],
				text: lang['napravlenie_na_mse'],
				iconCls: 'mse-direction32',
				handler: this.openEvnPrescrMseWindow.createDelegate(this)
			},
			//Журнал учета клинико-экспертной работы МУ
			action_openVKJournal:
			{
				nn: 'action_openVKJournal',
				tooltip: lang['otkryit_jurnal_ucheta_kliniko-ekspertnoy_rabotyi_mu'],
				text: lang['jurnal_vk'],
				iconCls: 'vk-journal32',
				handler: this.openVKJournal.createDelegate(this)
			},
			action_RegistryES:
			{
				nn: 'action_RegistryES',
				tooltip: lang['reestryi_lvn'],
				text: lang['reestryi_lvn'],
				iconCls: 'book32',
				handler: function(){
					getWnd('swRegistryESViewWindow').show()
				}
			},
			action_Lvn: {
				nn: 'action_Lvn',
				text: 'ЛВН: Поиск',
				tooltip: 'Поиск листков временной нетрудоспособности',
				iconCls : 'lvn-search16',
				hidden: getRegionNick() == 'kz',
				handler: function() {
					getWnd('swEvnStickViewWindow').show();
				}
			},
			action_QueryEvn:
			{
				disabled: false, 
				handler: function() 
				{
					getWnd('swQueryEvnListWindow').show({ARMType: 'vk'});
				},
				iconCls: 'mail32',
				nn: 'action_QueryEvn',
				text: 'Журнал запросов',
				tooltip: 'Журнал запросов',
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
			},
			action_Notify:
			{
				nn: 'action_Notify',
				tooltip: langs('Извещения'),
				text: langs('Извещения'),
				iconCls : 'doc-notify32',
				hidden: getRegionNick() != 'perm', 
				menuAlign: 'tr?',
				menu: new Ext.menu.Menu({
					items: [ {
						tooltip: 'Извещения по паллиативной помощи',
						text: 'Извещения по паллиативной помощи',
						handler: function() {   
							if ( getWnd('swEvnNotifyRegisterPalliatListWindow').isVisible() ) {
								sw.swMsg.show({
									buttons: Ext.Msg.OK,
									fn: Ext.emptyFn,
									icon: Ext.Msg.WARNING,
									msg: 'Окно уже открыто',
									title: ERR_WND_TIT
								});
								return false;
							}

							getWnd('swEvnNotifyRegisterPalliatListWindow').show({userMedStaffFact: this.userMedStaffFact});
						}.createDelegate(this)
					}]
				})
			},
			action_Register: {
				nn: 'action_Register',
				tooltip: lang['registryi'],
				text: lang['registryi'],
				iconCls : 'registry32',
				disabled: getRegionNick() != 'perm', 
				hidden: getRegionNick() != 'perm', 
				menuAlign: 'tr?',
				menu: new Ext.menu.Menu({
					items: [{
						tooltip: 'Регистр ИПРА',
						text: 'Регистр ИПРА',
						iconCls : 'doc-reg16',
						hidden: !(isUserGroup('IPRARegistry') || isUserGroup('IPRARegistryEdit')),
						handler: function() {   
							if ( getWnd('swIPRARegistryViewWindow').isVisible() ) {
								sw.swMsg.show({
									buttons: Ext.Msg.OK,
									fn: Ext.emptyFn,
									icon: Ext.Msg.WARNING,
									msg: 'Окно уже открыто',
									title: ERR_WND_TIT
								});
								return false;
							}

							getWnd('swIPRARegistryViewWindow').show({userMedStaffFact: this.userMedStaffFact});
						}.createDelegate(this)
					}, {
						tooltip: 'Регистр паллиативной помощи',
						text: 'Регистр паллиативной помощи',
						iconCls : 'doc-reg16',
						handler: function() {   
							if ( getWnd('swPersonRegisterPalliatListWindow').isVisible() ) {
								sw.swMsg.show({
									buttons: Ext.Msg.OK,
									fn: Ext.emptyFn,
									icon: Ext.Msg.WARNING,
									msg: 'Окно уже открыто',
									title: ERR_WND_TIT
								});
								return false;
							}

							getWnd('swPersonRegisterPalliatListWindow').show({userMedStaffFact: this.userMedStaffFact});
						}.createDelegate(this)
					},
					{
						text: lang['HTM_registry'],
						tooltip: lang['HTM_registry'],
						iconCls: 'doc-reg16',
						hidden: getRegionNick().inlist(['ufa', 'kz']) || !isUserGroup('HTMRegistry'),

						handler: function()
						{
							getWnd('swEvnDirectionHTMRegistryWindow')
								.show({
										ARMType: 'vk',
										userMedStaffFact: form.userMedStaffFact
									});
						}
					}
					]
				})
			},
			action_EMD:{
				nn: 'action_EMD',
				hidden: getRegionNick() == 'kz',
				iconCls : 'remd32',
				text: 'Подписание медицинской документации',
				tooltip: 'Подписание медицинской документации',
				handler: function() {
					getWnd('swEMDSearchUnsignedWindow').show();
				}
			},
			action_ExportEvnPrescrMse: {
				nn: 'action_ExportEvnPrescrMse',
				text: 'Экспорт направлений на МСЭ',
				tooltip: 'Экспорт направлений на МСЭ',
				iconCls: 'database-export32',
				hidden: getRegionNick() == 'kz',
				handler: function() {
					getWnd('swEvnPrescrMseExportWindow').show({
						ARMType: this.ARMType,
						MedService_id: this.MedService_id
					});
				}.createDelegate(this)
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
			border: true,
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
					id: 'vkwpSchedulePanel',
					items:
					[
						//this.MedPersonalPanel,
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
		sw.Promed.swVKWorkPlaceWindow.superclass.initComponent.apply(this, arguments);
	}
});

