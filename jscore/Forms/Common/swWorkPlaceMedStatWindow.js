/**
* АРМ медицинского статистика
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @autor		
* @copyright    Copyright (c) 2012 Swan Ltd.
* @version      апрель.2012
*/
sw.Promed.swWorkPlaceMedStatWindow = Ext.extend(sw.Promed.swWorkPlaceWindow, {
	useUecReader: true,
	buttons: [
		'-',
		{
			text: BTN_FRMHELP,
			iconCls: 'help16',
			handler: function(button, event) {
				ShowHelp(this.ownerCt.title);
			}
		}, {
			text      : langs('Закрыть'),
			tabIndex  : -1,
			tooltip   : langs('Закрыть'),
			iconCls   : 'cancel16',
			handler   : function() {
				this.ownerCt.hide();
			}
		}
	],
	deleteEvn: function() {
		var grid = this.GridPanel.getGrid();

		if ( !grid.getSelectionModel().getSelected() ) {
			return false;
		}

		var selected_record = grid.getSelectionModel().getSelected();

		var evn_class_id = parseInt(selected_record.get('EvnClass_id'));
		var evn_class_nick;
		var msg;
		var params = new Object();
		var url;

		if ( !evn_class_id || !evn_class_id.toString().inlist([ '3', '6', '30' ]) ) {
			return false;
		}
		url = '/?c=Evn&m=deleteEvn';
		params.Evn_id = selected_record.get('Evn_id');


		switch ( parseInt(evn_class_id) ) {
			case 3:
			case 6:
				evn_class_nick = langs('ТАП');
				msg = langs('Удалить талон амбулаторного пациента?');
			break;

			case 30:
				evn_class_nick = langs('КВС');
				msg = langs('Удалить карту выбывшего из стационара?');
			break;
		}

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( 'yes' == buttonId ) {
					Ext.Ajax.request({
						failure: function(response, options) {
							sw.swMsg.alert(langs('Ошибка'), langs('При удалении ') + evn_class_nick + langs(' возникли ошибки [Тип ошибки: 2]'));
						},
						params: params,
						success: function(response, options) {
							var response_obj = Ext.util.JSON.decode(response.responseText);

							if ( response_obj.success == false ) {
								sw.swMsg.alert(langs('Ошибка'), response_obj.Error_Msg ? response_obj.Error_Msg : langs('Ошибка при удалении') + evn_class_nick);
							}
							else {
								grid.getStore().remove(selected_record);

								if ( grid.getStore().getCount() == 0 ) {
									grid.addEmptyRecord(grid.getStore());
								}
							}
						},
						url: url
					});
				}
			},
			icon: Ext.MessageBox.QUESTION,
			msg: msg,
			title: langs('Вопрос')
		});
	},
	getLoadMask: function() {
		if ( !this.loadMask ) {
			this.loadMask = new Ext.LoadMask(this.getEl(), { msg: langs('Подождите...') });
		}

		return this.loadMask;
	},
	id: 'swWorkPlaceMedStatWindow',
	openEvnEditWindow: function(action) {
		if ( !action || !action.inlist([ 'edit', 'view']) ) {
			return false;
		}

		var formParams = new Object();
		var grid = this.GridPanel.getGrid();
		var params = new Object();

		if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('Evn_id') ) {
			return false;
		}

		var selected_record = grid.getSelectionModel().getSelected();

		var evn_class_id = parseInt(selected_record.get('EvnClass_id'));

		if ( !evn_class_id || !evn_class_id.toString().inlist([ '3', '6', '30' ]) ) {
			return false;
		}

		switch ( parseInt(evn_class_id) ) {
			case 3:
				if ( getWnd('swEvnPLEditWindow').isVisible() ) {
					sw.swMsg.alert(langs('Сообщение'), langs('Окно редактирования талона амбулаторного пациента уже открыто'));
					return false;
				}

				params.EvnPL_id = selected_record.get('Evn_id');
			break;

			case 6:
				if ( getWnd('swEvnPLStomEditWindow').isVisible() ) {
					sw.swMsg.alert(langs('Сообщение'), langs('Окно редактирования талона амбулаторного пациента уже открыто'));
					return false;
				}

				params.EvnPLStom_id = selected_record.get('Evn_id');
			break;

			case 30:
				if ( getWnd('swEvnPSEditWindow').isVisible() ) {
					sw.swMsg.alert(langs('Сообщение'), langs('Окно редактирования карты выбывшего из стационара уже открыто'));
					return false;
				}

				params.EvnPS_id = selected_record.get('Evn_id');
			break;
		}

		params.Person_id = selected_record.get('Person_id');
		params.PersonEvn_id = selected_record.get('PersonEvn_id');
		params.Server_id = selected_record.get('Server_id');

		params.action = action;
		params.callback = function(data) {
			if ( !data || (!data.evnPSData && !data.evnPLData && !data.evnPLStomData) ) {
				return false;
			}

			switch ( parseInt(evn_class_id) ) {
				case 3:
					selected_record.set('PersonEvn_id', data.evnPLData.PersonEvn_id);
					selected_record.set('Server_id', data.evnPLData.Server_id);
					selected_record.set('Evn_NumCard', data.evnPLData.EvnPL_NumCard);
					selected_record.set('Diag_Name', data.evnPLData.Diag_Name);
					selected_record.set('Evn_setDate', data.evnPLData.EvnPL_setDate);
					selected_record.set('Evn_disDate', data.evnPLData.EvnPL_disDate);
					selected_record.set('LpuSection_Name', data.evnPLData.LpuSection_Name);
					selected_record.set('MedPersonal_Fio', data.evnPLData.MedPersonal_Fio);
				break;

				case 6:
					selected_record.set('PersonEvn_id', data.evnPLStomData.PersonEvn_id);
					selected_record.set('Server_id', data.evnPLStomData.Server_id);
					selected_record.set('Evn_NumCard', data.evnPLStomData.EvnPLStom_NumCard);
					selected_record.set('Diag_Name', data.evnPLStomData.Diag_Name);
					selected_record.set('Evn_setDate', data.evnPLStomData.EvnPLStom_setDate);
					selected_record.set('Evn_disDate', data.evnPLStomData.EvnPLStom_disDate);
					selected_record.set('LpuSection_Name', data.evnPLStomData.LpuSection_Name);
					selected_record.set('MedPersonal_Fio', data.evnPLStomData.MedPersonal_Fio);
				break;

				case 30:
					selected_record.set('PersonEvn_id', data.evnPSData.PersonEvn_id);
					selected_record.set('Server_id', data.evnPSData.Server_id);
					selected_record.set('Evn_NumCard', data.evnPSData.EvnPS_NumCard);
					selected_record.set('Diag_Name', data.evnPSData.Diag_Name);
					selected_record.set('Evn_setDate', data.evnPSData.EvnPS_setDate);
					selected_record.set('Evn_disDate', data.evnPSData.EvnPS_disDate);
					selected_record.set('LpuSection_Name', data.evnPSData.LpuSection_Name);
					selected_record.set('MedPersonal_Fio', data.evnPSData.MedPersonal_Fio);
				break;
			}

			selected_record.commit();
		};
		params.formParams = formParams;
		params.onHide = function() {
			grid.getView().focusRow(grid.getStore().indexOf(selected_record));
		};

		switch ( parseInt(evn_class_id) ) {
			case 3:
				getWnd('swEvnPLEditWindow').show(params);
			break;

			case 6:
				getWnd('swEvnPLStomEditWindow').show(params);
			break;

			case 30:
				getWnd('swEvnPSEditWindow').show(params);
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
		
		params.begDate = Ext.util.Format.date(this.dateMenu.getValue1(), 'd.m.Y');
		params.endDate = Ext.util.Format.date(this.dateMenu.getValue2(), 'd.m.Y');
		params.hours = this.comboHourSelect.getValue();
		params.dispatchCallPmUser_id = (this.dispatchCallSelect.getValue()==0)?0:this.dispatchCallSelect.getValue();
		params.EmergencyTeam_id = (this.emergencyTeamCombo.getValue()==0)?0:this.emergencyTeamCombo.getValue();
		params.LpuBuilding_id = (this.FilterPanel.getForm().findField('LpuBuilding_id').getValue()==0)?0:this.FilterPanel.getForm().findField('LpuBuilding_id').getValue();
		this.GridPanel.removeAll({clearAll:true});
		if(!getGlobalOptions().IsSMPServer){
			this.GridPanel.loadData({globalFilters: params});
		}

	},
	show: function() {
		sw.Promed.swWorkPlaceMedStatWindow.superclass.show.apply(this, arguments);
        this.userMedStaffFact = arguments[0];
        var base_form = this.FilterPanel.getForm();

        if (arguments[0] && arguments[0].Lpu_id) {
            this.Lpu_id = arguments[0].Lpu_id;
        } else {
            this.Lpu_id = getGlobalOptions().lpu_id;
        }

		base_form.findField('LpuBuilding_id').getStore().baseParams.Lpu_id = this.Lpu_id;
        base_form.findField('LpuBuilding_id').getStore().load();
/*
		if ( !this.GridPanel.getAction('open_emk') ) {
			this.GridPanel.addActions({
				disabled: true,
				handler: function() {
					this.openPersonEmkWindow();
				}.createDelegate(this),
				name: 'open_emk',
				text: langs('Открыть ЭМК'),
				tooltip: langs('Открыть электронную медицинскую карту пациента')
			});
		}
*/
	},
	initComponent: function() {
		var form = this;

		var swPromedActions = {

			PersonDispOrpSearch: {
				tooltip: langs('Регистр детей-сирот'),
				text: langs('Регистр детей-сирот'),
				iconCls : 'doc-reg16',
				handler: function()
				{
					getWnd('swPersonDispOrpSearchWindow').show();
				}
			},
			PersonPrivilegeWOWSearch: {
				tooltip: langs('Регистр ВОВ'),
				text: langs('Регистр ВОВ'),
				iconCls : 'doc-reg16',
				handler: function()
				{
					getWnd('swPersonPrivilegeWOWSearchWindow').show();
				}
			},
			PersonDopDispSearch: {
				tooltip: langs('Регистр ДД'),
				text: langs('Регистр ДД'),
				iconCls : 'doc-reg16',
				handler: function()
				{
					getWnd('swPersonDopDispSearchWindow').show();
				}
			},
			EvnPLDispTeen14Search: {
				tooltip: langs('Регистр декретированных возрастов'),
				text: langs('Регистр декретированных возрастов'),
				iconCls : 'doc-reg16',
				handler: function()
				{
					getWnd('swEvnPLDispTeen14SearchWindow').show();
				}
			},
			OrphanRegistry: sw.Promed.personRegister.getOrphanBtnConfig(this.id, this),
			EvnNotifyOrphan: sw.Promed.personRegister.getEvnNotifyOrphanBtnConfig(form.id, form),
			CrazyRegistry:
			{
				tooltip: langs('Регистр по Психиатрии'),
				text: langs('Регистр по Психиатрии'),
				iconCls : 'doc-reg16',
				disabled: (String(getGlobalOptions().groups).indexOf('Crazy', 0) < 0),
				handler: function()
				{
					getWnd('swCrazyRegistryWindow').show({userMedStaffFact: this.userMedStaffFact});
				}.createDelegate(this)
			},
			EvnNotifyCrazy:
			{
				tooltip: langs('Журнал Извещений по Психиатрии'),
				text: langs('Журнал Извещений по Психиатрии'),
				iconCls : 'journal16',
				disabled: (String(getGlobalOptions().groups).indexOf('Crazy', 0) < 0),
				handler: function()
				{
					getWnd('swEvnNotifyCrazyListWindow').show({userMedStaffFact: this.userMedStaffFact});
				}.createDelegate(this)
			},
			NarkoRegistry:
			{
				tooltip: langs('Регистр по Наркологии'),
				text: langs('Регистр по Наркологии'),
				iconCls : 'doc-reg16',
				disabled: (String(getGlobalOptions().groups).indexOf('Narko', 0) < 0),
				handler: function()
				{
					getWnd('swNarkoRegistryWindow').show({userMedStaffFact: this.userMedStaffFact});
				}.createDelegate(this)
			},
			EvnNotifyNarko:
			{
				tooltip: langs('Журнал Извещений по Наркологии'),
				text: langs('Журнал Извещений по Наркологии'),
				iconCls : 'journal16',
				disabled: (String(getGlobalOptions().groups).indexOf('Narko', 0) < 0),
				handler: function()
				{
					getWnd('swEvnNotifyNarkoListWindow').show({userMedStaffFact: this.userMedStaffFact});
				}.createDelegate(this)
			},
			TubRegistry:
			{
				tooltip: langs('Регистр больных туберкулезом'),
				text: langs('Регистр по туберкулезным заболеваниям'),
				iconCls : 'doc-reg16',
				disabled: (String(getGlobalOptions().groups).indexOf('Tub', 0) < 0),
				handler: function()
				{
					getWnd('swTubRegistryWindow').show({userMedStaffFact: this.userMedStaffFact});
				}.createDelegate(this)
			},
			EvnNotifyTub:
			{
				tooltip: langs('Журнал Извещений о больных туберкулезом'),
				text: langs('Журнал Извещений по туберкулезным заболеваниям'),
				iconCls : 'journal16',
				disabled: (String(getGlobalOptions().groups).indexOf('Tub', 0) < 0),
				handler: function()
				{
					getWnd('swEvnNotifyTubListWindow').show({userMedStaffFact: this.userMedStaffFact});
				}.createDelegate(this)
			},
			IPRARegistry: {
				tooltip: 'Регистр ИПРА',
				text: 'Регистр ИПРА',
				iconCls : 'doc-reg16',
				hidden: !(isUserGroup('IPRARegistry') || isUserGroup('IPRARegistryEdit')),
				handler: function()
				{   

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
			}, 
			ECORegistry: {
				tooltip: 'Регистр по ВРТ',
				text: 'Регистр по ВРТ',
				iconCls : 'doc-reg16',
				hidden: !(isUserGroup('EcoRegistry') || isUserGroup('EcoRegistryRegion')),
				handler: function()
				{   

					if ( getWnd('swECORegistryViewWindow').isVisible() ) {
						sw.swMsg.show({
							buttons: Ext.Msg.OK,
							fn: Ext.emptyFn,
							icon: Ext.Msg.WARNING,
							msg: 'Окно уже открыто',
							title: ERR_WND_TIT
						});
						return false;
					}
					getWnd('swECORegistryViewWindow').show({userMedStaffFact: this.userMedStaffFact});
				}.createDelegate(this)
			}, 
			RzhdRegistry:
			{
				tooltip: langs('Регистр РЖД'),
				text: langs('Регистр РЖД'),
				hidden: !isUserGroup('RzhdRegistry') || getRegionNick() != 'ufa',
				iconCls : 'doc-reg16',
				handler: function()
				{
					getWnd('swRzhdRegistryWindow').show();
				}.createDelegate(this)
			},
			BskRegistry: {
				tooltip: langs('Регистр по БСК'),
				text: langs('Регистр по БСК'),
				iconCls : 'doc-reg16',
				/*hidden: (!getRegionNick().inlist([ 'ufa' ])),*/
				disabled: (!isUserGroup("BSKRegistry")),
				handler: function()
				{ 
					if ( getWnd('swBskRegistryWindow').isVisible() ) {
						sw.swMsg.show({
							buttons: Ext.Msg.OK,
							fn: Ext.emptyFn,
							icon: Ext.Msg.WARNING,
							msg: langs('Окно уже открыто'),
							title: ERR_WND_TIT
						});
						return false;
					}
					getWnd('swBskRegistryWindow').show({userMedStaffFact: this.userMedStaffFact});
				}.createDelegate(this)
			},
                        ReabRegistry: {
					tooltip: lang['registr_reability'],
					text: lang['registr_reability'],
					iconCls : 'doc-reg16',
					hidden: (getGlobalOptions().region.nick != 'ufa'),
					handler: function()
					{ 
						if ( getWnd('swReabRegistryWindow').isVisible() ) {
							sw.swMsg.show({
								buttons: Ext.Msg.OK,
								fn: Ext.emptyFn,
								icon: Ext.Msg.WARNING,
								msg: lang['okno_uje_otkryito'],
								title: ERR_WND_TIT
							});
							return false;
						}
						getWnd('swReabRegistryWindow').show({userMedStaffFact: this.userMedStaffFact});
					}.createDelegate(this)
				},
			VenerRegistry:
			{
				tooltip: langs('Регистр больных венерическим заболеванием'),
				text: langs('Регистр больных венерическим заболеванием'),
				iconCls : 'doc-reg16',
				disabled: (String(getGlobalOptions().groups).indexOf('Vener', 0) < 0),
				handler: function()
				{
					getWnd('swVenerRegistryWindow').show({userMedStaffFact: this.userMedStaffFact});
				}.createDelegate(this)
			},
			EvnNotifyVener:
			{
				tooltip: langs('Журнал Извещений о больных венерическим заболеванием'),
				text: langs('Журнал Извещений о больных венерическим заболеванием'),
				iconCls : 'journal16',
				disabled: (String(getGlobalOptions().groups).indexOf('Vener', 0) < 0),
				handler: function()
				{
					getWnd('swEvnNotifyVenerListWindow').show({userMedStaffFact: this.userMedStaffFact});
				}.createDelegate(this)
			},
			HIVRegistry:
			{
				tooltip: langs('Регистр ВИЧ-инфицированных'),
				text: langs('Регистр ВИЧ-инфицированных'),
				iconCls : 'doc-reg16',
				disabled: !allowHIVRegistry(),
				handler: function()
				{
					getWnd('swHIVRegistryWindow').show({userMedStaffFact: this.userMedStaffFact});
				}.createDelegate(this)
			},
			EvnNotifyHIV:
			{
				tooltip: langs('Журнал Извещений о ВИЧ-инфицированных'),
				text: langs('Журнал Извещений о ВИЧ-инфицированных'),
				iconCls : 'journal16',
				disabled: (String(getGlobalOptions().groups).indexOf('HIV', 0) < 0),
				handler: function()
				{
					getWnd('swEvnNotifyHIVListWindow').show({userMedStaffFact: this.userMedStaffFact});
				}.createDelegate(this)
			},
			HepatitisRegistry:
			{
				tooltip: langs('Регистр по Вирусному гепатиту'),
				text: langs('Регистр по Вирусному гепатиту'),
				iconCls : 'doc-reg16',
				handler: function()
				{
					if ( getWnd('swHepatitisRegistryWindow').isVisible() ) {
						sw.swMsg.show({
							buttons: Ext.Msg.OK,
							fn: Ext.emptyFn,
							icon: Ext.Msg.WARNING,
							msg: langs('Окно уже открыто'),
							title: ERR_WND_TIT
						});
						return false;
					}
					getWnd('swHepatitisRegistryWindow').show({userMedStaffFact: this.userMedStaffFact});
				}.createDelegate(this)
			},
			EvnNotifyHepatitis:
			{
				tooltip: langs('Журнал Извещений по Вирусному гепатиту'),
				text: langs('Журнал Извещений по Вирусному гепатиту'),
				iconCls : 'journal16',
				handler: function()
				{
					if ( getWnd('swEvnNotifyHepatitisListWindow').isVisible() ) {
						sw.swMsg.show({
							buttons: Ext.Msg.OK,
							fn: Ext.emptyFn,
							icon: Ext.Msg.WARNING,
							msg: langs('Окно уже открыто'),
							title: ERR_WND_TIT
						});
						return false;
					}
					getWnd('swEvnNotifyHepatitisListWindow').show({userMedStaffFact: this.userMedStaffFact});
				}.createDelegate(this)
			},
			EvnInfectNotify:
			{
				tooltip: langs('Журнал Извещений форма №058/У'),
				text: langs('Журнал Извещений форма №058/У'),
				iconCls : 'journal16',
				disabled: false,
				handler: function()
				{
					if ( getWnd('swEvnInfectNotifyListWindow').isVisible() ) {
						sw.swMsg.show({
							buttons: Ext.Msg.OK,
							fn: Ext.emptyFn,
							icon: Ext.Msg.WARNING,
							msg: langs('Окно уже открыто'),
							title: ERR_WND_TIT
						});
						return false;
					}
					getWnd('swEvnInfectNotifyListWindow').show({userMedStaffFact: this.userMedStaffFact});
				}.createDelegate(this)
			},
			OnkoRegistry:
			{
				tooltip: langs('Регистр по онкологии'),
				text: langs('Регистр по онкологии'),
				iconCls : 'doc-reg16',
				disabled: (String(getGlobalOptions().groups).indexOf('OnkoRegistry', 0) < 0),
				handler: function()
				{
					if ( getWnd('swOnkoRegistryWindow').isVisible() ) {
						sw.swMsg.show({
							buttons: Ext.Msg.OK,
							fn: Ext.emptyFn,
							icon: Ext.Msg.WARNING,
							msg: langs('Окно уже открыто'),
							title: ERR_WND_TIT
						});
						return false;
					}
					getWnd('swOnkoRegistryWindow').show({userMedStaffFact: this.userMedStaffFact});
				}.createDelegate(this)
			},
			EvnOnkoNotify:
			{
				tooltip: langs('Журнал Извещений об онкобольных'),
				text: langs('Журнал Извещений об онкобольных'),
				iconCls : 'journal16',
				handler: function()
				{
					if ( getWnd('swEvnOnkoNotifyListWindow').isVisible() ) {
						sw.swMsg.show({
							buttons: Ext.Msg.OK,
							fn: Ext.emptyFn,
							icon: Ext.Msg.WARNING,
							msg: langs('Окно уже открыто'),
							title: ERR_WND_TIT
						});
						return false;
					}
					getWnd('swEvnOnkoNotifyListWindow').show({userMedStaffFact: this.userMedStaffFact});
				}.createDelegate(this)
			},
			PregnancyRegistry:
			{
				tooltip: langs('Регистр беременных'),
				text: langs('Регистр беременных'),
				iconCls : 'doc-reg16',
				disabled: !isPregnancyRegisterAccess(),
				hidden: !getRegionNick().inlist(['vologda']),
				handler: function()
				{
					getWnd('swPersonPregnancyWindow').show();
				}
			},
			VznRegistry: sw.Promed.personRegister.getVznBtnConfig(this.id, this)
		};

		this.buttonPanelActions = {
			action_EvnPL:{
				text:langs('ТАП'),
				tooltip: langs('ТАП'),
				iconCls: 'pl-stream32',
				hidden: getGlobalOptions().lpu_isLab == 2 || getGlobalOptions().IsSMPServer,
				menu: new Ext.menu.Menu({
					items:[
						{
							tooltip: langs('Поточный ввод ТАП'),
							text: langs('Поточный ввод ТАП'),
							iconCls : 'pol-eplstream16',
							disabled: false,
							handler: function()
							{
								getWnd('swEvnPLStreamInputWindow').show();
							}
						},
						{
							tooltip: langs('Талон амбулаторного пациента: Поиск'),
							text: langs('Талон амбулаторного пациента: Поиск'),
							iconCls : 'pol-eplsearch16',
							disabled: false,
							handler: function()
							{
								getWnd('swEvnPLSearchWindow').show();
							}
						}
					]
				})
			},
			action_EvnPS:{
				text:langs('КВС'),
				tooltip: langs('КВС'),
				iconCls: 'ps-stream32',
				hidden: getGlobalOptions().lpu_isLab == 2 || getGlobalOptions().IsSMPServer,
				menu: new Ext.menu.Menu({
					items:[
						{
							tooltip: langs('Поточный ввод КВС'),
							text: langs('Поточный ввод КВС'),
							iconCls : 'ps-stream16.png',
							disabled: false,
							handler: function()
							{
								getWnd('swEvnPSStreamInputWindow').show();
							}
						},
						{
							tooltip: langs('Карты выбывшего из стационара: Поиск'),
							text: langs('Карты выбывшего из стационара: Поиск'),
							iconCls : 'ps-search16.png',
							disabled: false,
							handler: function()
							{
								getWnd('swEvnPSSearchWindow').show();
							}
						}
					]
				})
			},
			action_Stom:{
				text:langs('Стоматология'),
				tooltip: langs('Стоматология'),
				iconCls: 'eph-tooth32',
				hidden: getGlobalOptions().lpu_isLab == 2 || getGlobalOptions().IsSMPServer,
				menu: new Ext.menu.Menu({
					items:[
						{
							tooltip: langs('Поточный ввод ТАП'),
							text: langs('Поточный ввод ТАП'),
							iconCls : 'eph-tooth16',
							disabled: false,
							handler: function()
							{
								getWnd('swEvnPLStomStreamInputWindow').show();
							}
						},
						{
							tooltip: langs('Талон амбулаторного пациента: Поиск'),
							text: langs('Талон амбулаторного пациента: Поиск'),
							iconCls : 'eph-tooth16',
							disabled: false,
							handler: function()
							{
								getWnd('swEvnPLStomSearchWindow').show();
							}
						}
					]
				})
			},
			action_Smp:{
				text:langs('СМП'),
				tooltip: langs('СМП'),
				iconCls: 'ambulance32',
				menuAlign: 'tr',
				hidden: (getRegionNick().inlist(['by']) || (getGlobalOptions().lpu_isLab == 2)),
				menu: new Ext.menu.Menu({
					items:[
						{
							tooltip: langs('Карты СМП: Поиск'),
							text: langs('Карты СМП: Поиск'),
							iconCls : 'ambulance_search16',
							disabled: false,
							handler: function()
							{
								getWnd('swCmpCallCardSearchWindow').show();
							}
						},
						{
							tooltip: langs('Карты СМП: Поточный ввод'),
							text: langs('Карты СМП: Поточный ввод'),
							iconCls : 'ambulance_search16',
							disabled: false,
							handler: function()
							{
								//пытаемся запустить новую поточную карту
								getWnd('swCmpCallCardNewCloseCardWindow').show({
									action: 'stream',
									formParams: {
										ARMType: 'smpadmin'			   
									},
									callback: function(data) {
										if ( !data || !data.CmpCloseCard_id ) {
											return false;
										}
									}
								});
								
								//временно закомментил, вдруг что-то пойдет не так
								//getWnd('swCmpCallCardCloseStreamWindow').show();
							},
							hidden: (!getGlobalOptions().region.nick.inlist(['pskov', 'astra', 'kareliya', 'ekb']))
						}
					]
				})
			},
			/*action_EvnPL_StreamInput: {
				disabled: false,
				handler: function() {
					getWnd('swEvnPLStreamInputWindow').show();
				}.createDelegate(this),
				iconCls : 'pl-stream32',
				nn: 'action_EvnPL_StreamInput',
				text: langs('Поточный ввод ТАП'),
				tooltip: langs('Поточный ввод ТАП')
			},*/
			/*action_EvnPS_StreamInput: {
				disabled: false,
				handler: function() {
					getWnd('swEvnPSStreamInputWindow').show();
				}.createDelegate(this),
				iconCls : 'ps-stream32',
				nn: 'action_EvnPS_StreamInput',
				text: langs('Поточный ввод КВС'),
				tooltip: langs('Поточный ввод КВС')
			},*/
			/*action_MedSvidDeath: {
				handler: function() {
					getWnd('swMedSvidDeathStreamWindow').show();
				},
				//hidden: !isUserGroup('MedSvidDeath'),
				iconCls : 'svid-death32',
				nn: 'action_MedSvidDeath',
				text: WND_FRW,
				tooltip: langs('Мед. свидетельства о смерти')
			},*/
			action_Registry:{
				hidden: (getRegionNick() == 'ufa' && !isUserGroup([ 'LpuPowerUser', 'SuperAdmin' ]))
					|| (getRegionNick() == 'ekb' && !isUserGroup([ 'RegistryUser', 'RegistryUserReadOnly' ]))
					|| (getRegionNick() == 'krasnoyarsk' && !isUserGroup([ 'LpuAdmin', 'RegistryUser', 'RegistryUserReadOnly', 'SuperAdmin' ]))
					|| getGlobalOptions().IsSMPServer,
				nn: 'action_Registry',
				tooltip: langs('Реестры'),
				text: langs('Реестры'),
				iconCls : 'service-reestrs16',
				disabled: false,
				menuAlign: 'tr',
				menu: new Ext.menu.Menu({
					items: [{
						handler: function() {
							getWnd('swRegistryViewWindow').show();
						},
						iconCls: 'service-reestrs16',
						nn: 'action_Registry',
						text: langs('Реестры счетов'),
						tooltip: langs('Реестры счетов')
					}, {
						handler: function() {
							if ( getRegionNick() == 'vologda' ) {
								getWnd('swRegistryNewViewWindow').show();
							}
							else {
								getWnd('swRegistryViewWindow').show({Registry_IsNew: 2});
							}
						},
						hidden: !getRegionNick().inlist(['ufa','vologda']),
						iconCls: 'service-reestrs16',
						nn: 'action_Registry',
						text: langs('Реестры счетов (новые)'),
						tooltip: langs('Реестры счетов (новые)')
					}]
				})
			},
			action_Notify:
			{
				nn: 'action_Notify',
				tooltip: langs('Извещения/Направления'),
				text: langs('Извещения/Направления'),
				iconCls : 'doc-notify32',
				disabled: false,
				hidden: getGlobalOptions().lpu_isLab == 2 || getGlobalOptions().IsSMPServer,
				menuAlign: 'tr',
				menu: new Ext.menu.Menu({
					items: [
						//swPromedActions.EvnInfectNotify,
						swPromedActions.EvnNotifyHepatitis,
						swPromedActions.EvnOnkoNotify,
						swPromedActions.EvnNotifyOrphan,
						swPromedActions.EvnNotifyCrazy,
						swPromedActions.EvnNotifyNarko,
						swPromedActions.EvnNotifyTub,
						swPromedActions.EvnNotifyVener,
						swPromedActions.EvnNotifyHIV
					]
				})
			},
			action_Register:
			{
				nn: 'action_Register',
				tooltip: langs('Регистры'),
				text: langs('Регистры'),
				iconCls : 'registry32',
				disabled: false,
				menuAlign: 'tr',
				hidden: getGlobalOptions().lpu_isLab == 2 || getGlobalOptions().IsSMPServer,
				menu: new Ext.menu.Menu({
					items: [
						swPromedActions.PersonDispOrpSearch,
						swPromedActions.PersonPrivilegeWOWSearch,
						swPromedActions.PersonDopDispSearch,
						swPromedActions.EvnPLDispTeen14Search,
						swPromedActions.HepatitisRegistry,
						swPromedActions.OnkoRegistry,
						swPromedActions.PregnancyRegistry,
						swPromedActions.OrphanRegistry,
						swPromedActions.CrazyRegistry,
						swPromedActions.NarkoRegistry,
						swPromedActions.TubRegistry,
						swPromedActions.VznRegistry,
						swPromedActions.BskRegistry,
						swPromedActions.ReabRegistry,
						swPromedActions.IPRARegistry,
						swPromedActions.ECORegistry,
						swPromedActions.VenerRegistry,
						swPromedActions.HIVRegistry,
						swPromedActions.RzhdRegistry
					]
				})
			},
			action_EvnDirectionExt:
			{
				handler: function() {
					getWnd('swEvnDirectionExtWindow').show();
				},
				hidden: !getRegionNick().inlist(['astra']) || getGlobalOptions().IsSMPServer,
				iconCls : 'pers-cards32',
				nn: 'action_EvnDirectionExt',
				text: langs('Внешние направления'),
				tooltip: langs('Внешние направления')
			},
			action_DopDisp:
			{
				nn: 'action_DopDisp',
				hidden: (getRegionNick().inlist(['by']) || (getGlobalOptions().lpu_isLab == 2) || getGlobalOptions().IsSMPServer),
				tooltip: langs('Диспансеризация'),
				text: langs('Диспансеризация'),
				iconCls : 'mp-disp32',
				disabled: false,
				menuAlign: 'tr?',
				menu: new Ext.menu.Menu({
					items: [{
						text:langs('Диспансеризация взрослого населения'),
						iconCls: 'pol-dopdisp16',
						hidden: getRegionNick().inlist(['by','kz']),
						menu: new Ext.menu.Menu({
							items: [{
								text: langs('Обследования ВОВ: Поиск'),
								tooltip: langs('Обследования ВОВ: Поиск'),
								iconCls : 'dopdisp-search16',
								handler: function() {
									getWnd('EvnPLWOWSearchWindow').show();
								}
							},
								'-',
								{
									text: (getRegionNick().inlist(['ufa','ekb','penza','astra'])) ? langs('Регистр инвалидов, подлежащих ДВН: Поиск') : langs('Регистр ВОВ: Поиск'),
									tooltip: (getRegionNick().inlist(['ufa','ekb','penza','astra'])) ? langs('Регистр инвалидов, подлежащих ДВН: Поиск') : langs('Регистр ВОВ: Поиск'),
									iconCls : 'dopdisp-search16',
									handler: function() {
										getWnd('swPersonPrivilegeWOWSearchWindow').show();
									}
								}, {
									text: (getRegionNick().inlist(['ufa','ekb','penza','astra'])) ? langs('Регистр инвалидов, подлежащих ДВН: Поточный ввод') : langs('Регистр ВОВ: Поточный ввод'),
									tooltip: (getRegionNick().inlist(['ufa','ekb','penza','astra'])) ? langs('Регистр инвалидов, подлежащих ДВН: Поточный ввод') : langs('Регистр ВОВ: Поточный ввод'),
									iconCls : 'dopdisp-search16',
									handler: function() {
										getWnd('swPersonPrivilegeWOWStreamInputWindow').show();
									}
								},
								'-',
								{
									text: MM_POL_PERSDDSEARCH,
									tooltip: langs('Дополнительная диспансеризация: поиск'),
									iconCls : 'dopdisp-search16',
									handler: function() {
										getWnd('swPersonDopDispSearchWindow').show();
									}
								},
								'-',
								{
									text: langs('Талон по дополнительной диспансеризации взрослых (до 2013г.): поиск'),
									tooltip: langs('Талон по дополнительной диспансеризации взрослых (до 2013г.): поиск'),
									iconCls : 'dopdisp-epl-search16',
									handler: function() {
										getWnd('swEvnPLDispDopSearchWindow').show();
									}
								},
								'-',
								{
									text: MM_POL_EPLDD13SEARCH,
									tooltip: MM_POL_EPLDD13SEARCH,
									iconCls : 'dopdisp-epl-search16',
									handler: function() {
										getWnd('swEvnPLDispDop13SearchWindow').show();
									}
								}, {
									text: MM_POL_EPLDD13SECONDSEARCH,
									tooltip: MM_POL_EPLDD13SECONDSEARCH,
									iconCls : 'dopdisp-epl-search16',
									handler: function() {
										getWnd('swEvnPLDispDop13SecondSearchWindow').show();
									}
								}]
						})
					}, {
						text:langs('Профилактические осмотры взрослых'),
						iconCls: 'pol-dopdisp16',
						hidden: getRegionNick().inlist(['by','kz']),
						menu: new Ext.menu.Menu({
							items: [{
								text: MM_POL_EPLDPSEARCH,
								tooltip: MM_POL_EPLDPSEARCH,
								iconCls : 'dopdisp-epl-search16',
								handler: function() {
									getWnd('swEvnPLDispProfSearchWindow').show();
								}
							}]
						})
					}, {
						text:langs('Диспансеризация детей-сирот'),
						iconCls: 'pol-dopdisp16',
						hidden: getRegionNick().inlist(['by','kz']),
						menu: new Ext.menu.Menu({
							items: [{
								text: langs('Регистр детей-сирот (до 2013г.): Поиск'),
								tooltip: langs('Регистр детей-сирот: Поиск'),
								iconCls: 'orphdisp-search16',
								handler: function() {
									getWnd('swPersonDispOrpSearchWindow').show();
								}
							}, {
								text: langs('Талон по диспансеризации детей-сирот (до 2013г.): Поиск'),
								tooltip: langs('Талон по диспансеризации детей-сирот: Поиск'),
								iconCls: 'orphdisp-epl-search16',
								handler: function() {
									getWnd('swEvnPLDispOrpSearchWindow').show();
								}
							},
								'-',
								{
									text: langs('Регистр детей-сирот (с 2013г.): Поиск'),
									tooltip: langs('Регистр детей-сирот (с 2013г.): Поиск'),
									iconCls : 'dopdisp-search16',
									handler: function() {
										getWnd('swPersonDispOrp13SearchWindow').show({
											CategoryChildType: 'orp'
										});
									}
								}, {
									text: langs('Регистр детей-сирот усыновленных: Поиск'),
									tooltip: langs('Регистр детей-сирот усыновленных: Поиск'),
									iconCls : 'dopdisp-search16',
									handler: function() {
										getWnd('swPersonDispOrp13SearchWindow').show({
											CategoryChildType: 'orpadopted'
										});
									}
								}, {
									text: langs('Карта диспансеризации несовершеннолетнего - 1 этап: Поиск'),
									tooltip: langs('Карта диспансеризации несовершеннолетнего - 1 этап: Поиск'),
									iconCls : 'dopdisp-epl-search16',
									handler: function() {
										getWnd('swEvnPLDispOrp13SearchWindow').show({
											stage: 1
										});
									}
								}, {
									text: langs('Карта диспансеризации несовершеннолетнего - 2 этап: Поиск'),
									tooltip: langs('Карта диспансеризации несовершеннолетнего - 2 этап: Поиск'),
									iconCls : 'dopdisp-epl-search16',
									handler: function() {
										getWnd('swEvnPLDispOrp13SearchWindow').show({
											stage: 2
										});
									}
								},
								'-',
								{
									text: langs('Экспорт карт по диспансеризации несовершеннолетних'),
									tooltip: langs('Экспорт карт по диспансеризации несовершеннолетних'),
									iconCls : 'database-export16',
									handler: function() {
										getWnd('swEvnPLDispTeenExportWindow').show();
									}
								}]
						})
					}, {
						text:langs('Медицинские осмотры несовершеннолетних'),
						iconCls: 'pol-dopdisp16',
						hidden: getRegionNick().inlist(['by','kz']),
						menu: new Ext.menu.Menu({
							items: [{
								text: langs('Регистр периодических осмотров несовершеннолетних: Поиск'),
								tooltip: langs('Регистр периодических осмотров несовершеннолетних: Поиск'),
								iconCls : 'dopdisp-search16',
								hidden: getRegionNick().inlist(['adygeya', 'yakutiya']),
								handler: function() {
									getWnd('swPersonDispOrpPeriodSearchWindow').show();
								}
							}, {
								text: langs('Периодические осмотры несовершеннолетних: Поиск'),
								tooltip: langs('Периодические осмотры несовершеннолетних: Поиск'),
								iconCls : 'dopdisp-epl-search16',
								hidden: getRegionNick().inlist(['adygeya', 'yakutiya']),
								handler: function() {
									getWnd('swEvnPLDispTeenInspectionSearchWindow').show();
								}
							},
								'-',
								{
									text: langs('Направления на профилактические осмотры несовершеннолетних: Поиск'),
									tooltip: langs('Направления на профилактические осмотры несовершеннолетних: Поиск'),
									iconCls : 'dopdisp-search16',
									handler: function() {
										getWnd('swPersonDispOrpProfSearchWindow').show();
									}
								}, {
									text: langs('Профилактические осмотры несовершеннолетних - 1 этап: Поиск'),
									tooltip: langs('Профилактические осмотры несовершеннолетних - 1 этап: Поиск'),
									iconCls : 'dopdisp-epl-search16',
									handler: function() {
										getWnd('swEvnPLDispTeenInspectionProfSearchWindow').show();
									}
								}, {
									text: langs('Профилактические осмотры несовершеннолетних - 2 этап: Поиск'),
									tooltip: langs('Профилактические осмотры несовершеннолетних - 2 этап: Поиск'),
									iconCls : 'dopdisp-epl-search16',
									handler: function() {
										getWnd('swEvnPLDispTeenInspectionProfSecSearchWindow').show();
									}
								},
								'-',
								{
									text: langs('Направления на предварительные осмотры несовершеннолетних: Поиск'),
									tooltip: langs('Направления на предварительные осмотры несовершеннолетних: Поиск'),
									iconCls : 'dopdisp-search16',
									hidden: getRegionNick().inlist(['adygeya', 'yakutiya']),
									handler: function() {
										getWnd('swPersonDispOrpPredSearchWindow').show();
									}
								}, {
									text: langs('Предварительные осмотры несовершеннолетних - 1 этап: Поиск'),
									tooltip: langs('Предварительные осмотры несовершеннолетних - 1 этап: Поиск'),
									iconCls : 'dopdisp-epl-search16',
									hidden: getRegionNick().inlist(['adygeya', 'yakutiya']),
									handler: function() {
										getWnd('swEvnPLDispTeenInspectionPredSearchWindow').show();
									}
								}, {
									text: langs('Предварительные осмотры несовершеннолетних - 2 этап: Поиск'),
									tooltip: langs('Предварительные осмотры несовершеннолетних - 2 этап: Поиск'),
									iconCls : 'dopdisp-epl-search16',
									hidden: getRegionNick().inlist(['adygeya', 'yakutiya']),
									handler: function() {
										getWnd('swEvnPLDispTeenInspectionPredSecSearchWindow').show();
									}
								}]
						})
					}, {
						text:langs('Диспансеризация (подростки 14ти лет)'),
						iconCls: 'pol-dopdisp16',
						hidden: getRegionNick().inlist(['by','kz']),
						menu: new Ext.menu.Menu({
							items: [{
								text: langs('Диспансеризация 14-летних подростков: Поиск'),
								tooltip: langs('Диспансеризация 14-летних подростков: Поиск'),
								iconCls : 'dopdisp-teens-search16',
								handler: function() {
									getWnd('swEvnPLDispTeen14SearchWindow').show();
								}
							}]
						})
					}, {
						handler: function() {
							getWnd('swPersonProfExportWindow').show();
						},
						hidden: (getRegionNick() != 'kareliya'),
						text: 'Выгрузка данных по профилактическим мероприятиям',
						tooltip: 'Выгрузка данных по профилактическим мероприятиям'
					}, {
						text:langs('Диспансерное наблюдение'),
						iconCls: 'pol-disp16',
						hidden: !getRegionNick().inlist(['kz']),
						menu: new Ext.menu.Menu({
							items: [
								sw.Promed.Actions.PersonDispSearchAction,
								sw.Promed.Actions.PersonDispViewAction
							]
						})
					}]
				})
			},
			action_DispPlan: 
			{
				nn: 'action_DispPlan',
				text: langs('Планирование диспансеризации'),
				tooltip: langs('Планирование диспансеризации'),
				hidden: getRegionNick() != 'penza',
				iconCls: 'worksheets32',
				handler: function ()
				{
					var phpSessId = window.getCookie('PHPSESSID');
					var url = DISP_PLANNING_GIT + '?PHPSESSID=' + phpSessId;
					window.open(url);
				}.createDelegate(this)
			},
			action_PlanObsDisp:
			{
				nn: 'action_PlanObsDisp',
				text: langs('Планы контрольных посещений в рамках диспансерного наблюдения'),
				tooltip: langs('Планы контрольных посещений в рамках диспансерного наблюдения'),
				hidden: !getRegionNick().inlist(['buryatiya','ekb','pskov']),
				iconCls: 'pol-dopdisp16',
				handler: function ()
				{
					getWnd('swPlanObsDispListWindow').show();
				}.createDelegate(this)
			},
			action_ScreenSearch:
			{
				nn: 'action_ScreenSearch',
				hidden: !getRegionNick().inlist(['kz']) || getGlobalOptions().IsSMPServer,
				tooltip: langs('Скрининговые исследования'),
				text: langs('Скрининговые исследования'),
				iconCls : 'mp-disp32',
				disabled: false,
				menuAlign: 'tr?',
				menu: new Ext.menu.Menu({
					items: [
						sw.Promed.Actions.EvnPLDispScreenSearchAction,
						sw.Promed.Actions.EvnPLDispScreenChildSearchAction
					]
				})
			},
			action_PersonSearch: {
				text: langs('Человек: поиск'),
				tooltip: langs('Человек: поиск'),
				iconCls: 'patient-search16',
				hidden:  getGlobalOptions().IsSMPServer,
				handler: function()
				{
					getWnd('swPersonSearchWindow').show({
						onSelect: function(person_data) {
							if (!person_data.afterAdd) {
								getWnd('swPersonEditWindow').show({
									onHide: function () {
										if ( person_data.onHide && typeof person_data.onHide == 'function' ) {
											person_data.onHide();
										}
									},
									callback: function(callback_data) {
										if ( typeof callback_data != 'object' ) {
											return false;
										}

										var grid = getWnd('swPersonSearchWindow').PersonSearchViewFrame.getGrid();

										if ( typeof grid != 'object' ) {
											return false;
										}

										grid.getStore().each(function(record) {
											if ( record.get('Person_id') == callback_data.Person_id ) {
												record.set('Server_id', callback_data.Server_id);
												record.set('PersonEvn_id', callback_data.PersonEvn_id);
												record.set('PersonSurName_SurName', callback_data.PersonData.Person_SurName);
												record.set('PersonFirName_FirName', callback_data.PersonData.Person_FirName);
												record.set('PersonSecName_SecName', callback_data.PersonData.Person_SecName);
												record.set('PersonBirthDay_BirthDay', callback_data.PersonData.Person_BirthDay);
												record.commit();
											}
										});

										grid.getView().focusRow(0);
									},
									Person_id: person_data.Person_id,
									Server_id: person_data.Server_id
								});
							}
						},
						searchMode: 'all'
					});
				}
			},
			action_ES:
			{
				nn: 'action_ES',
				tooltip: 'ЛВН/ЭЛН',
				text: 'ЛВН/ЭЛН',
				iconCls: 'lvn-search16',
				hidden: (getRegionNick().inlist([ 'kz' ]) || (getGlobalOptions().lpu_isLab == 2) || getGlobalOptions().IsSMPServer),
				menuAlign: 'tr',
				menu: new Ext.menu.Menu({
					items: [{
						text: 'Поиск ЛВН',
						handler: function(){
							getWnd('swEvnStickViewWindow').show();
						}
					}, {
						text: langs('Реестры ЛВН'),
						handler: function(){
							getWnd('swRegistryESViewWindow').show();
						}
					}, {
						text: 'Запросы в ФСС',
						handler: function(){
							getWnd('swStickFSSDataViewWindow').show();
						}
					}, {
						text: 'Номера ЭЛН',
						handler: function(){
							getWnd('swRegistryESStorageViewWindow').show();
						}
					}]
				})
			},
			action_Cytologic:
			{
				nn: 'action_Cytologic',
				tooltip: 'Цитология',
				text: 'Цитология',
				iconCls: 'cytologica16',
				hidden: (getRegionNick().inlist([ 'kz' ])),
				menuAlign: 'tr',
				menu: new Ext.menu.Menu({
					items: [{
						text: 'Направления на цитологическое диагностическое исследование',
						iconCls: 'cytologica16',
						handler: function(){
							getWnd('swEvnDirectionCytologicViewWindows').show({curentMedStaffFactByUser: sw.Promed.MedStaffFactByUser.current});
						}
					}, {
						text: langs('Протоколы цитологических диагностических исследований'),
						iconCls: 'cytologica16',
						handler: function(){
							getWnd('swEvnCytologicProtoViewWindow').show({curentMedStaffFactByUser: sw.Promed.MedStaffFactByUser.current});
						}
					}]
				})
			},
			action_References: {
				nn: 'action_References',
				tooltip: langs('Справочники'),
				text: langs('Справочники'),
				iconCls : 'book32',
				disabled: false,
				hidden:  getGlobalOptions().IsSMPServer,
				menuAlign: 'tr?',
				menu: new Ext.menu.Menu({
					items: [
						{
							tooltip: langs('МКБ-10'),
							text: langs('Справочник МКБ-10'),
							iconCls: 'spr-mkb16',
							handler: function() {
								if ( !getWnd('swMkb10SearchWindow').isVisible() )
									getWnd('swMkb10SearchWindow').show({action:'view'});
							}
						},
						{
							tooltip: (getRegionNick().inlist(['vologda','buryatiya'])) ? 'МЭС' : langs('Справочник') + getMESAlias(),
							text: (getRegionNick().inlist(['vologda','buryatiya'])) ? 'МЭС' : langs('Справочник') + getMESAlias(),
							iconCls: 'spr-mes16',
							hidden: !(getRegionNick().inlist(['perm', 'ekb']) || (getRegionNick().inlist(['vologda','buryatiya']) && isUserGroup('EditingMES'))),
							handler: function() {
								if ( !getWnd('swMesOldSearchWindow').isVisible() )
									getWnd('swMesOldSearchWindow').show({ARMType: 'mstat'});
							}.createDelegate(this)
						},
						sw.Promed.Actions.swDrugDocumentSprAction,
						{
							text: langs('Справочник услуг'),
							tooltip: langs('Справочник услуг'),
							iconCls: 'services-complex16',
							handler: function() {
								getWnd('swUslugaTreeWindow').show({action: 'view'});
							}
						},
						{
							text: langs('Тарифы и объемы'),
							tooltip: langs('Тарифы и объемы'),
							// iconCls : 'service-reestrs16',
							handler: function() {
								getWnd('swTariffVolumesViewWindow').show();
							},
							hidden: (getRegionNick() != 'perm')
						},
						sw.Promed.Actions.swPrepBlockSprAction
					]
				})
			},
			action_Documents: {
				nn: 'action_Documents',
				tooltip: langs('Инструментарий'),
				text: langs('Инструментарий'),
				iconCls : 'document32',
				disabled: false,
				hidden: getRegionNick()!=='vologda',
				menuAlign: 'tr',
				menu: new Ext.menu.Menu({
					items: [
						{
							name: langs('Отчеты в DBF формате'),
							text: langs('Отчеты в DBF формате'),
							hidden: getRegionNick() !== 'vologda',
							handler: function()
							{
								getWnd('swReportsInDBFFormat').show();
							}.createDelegate(this)
						}
					]
				})
			},
			actions_settings: {
				nn: 'actions_settings',
				iconCls: 'settings32',
				text: langs('Сервис'),
				tooltip: langs('Сервис'),
				hidden:  getGlobalOptions().IsSMPServer,
				listeners: {
					'click': function(){
						var menu = Ext.menu.MenuMgr.get('wpfdw_menu_windows');
						menu.removeAll();
						var number = 1;
						Ext.WindowMgr.each(function(wnd){
							if ( wnd.isVisible() )
							{
								if ( Ext.WindowMgr.getActive().id == wnd.id )
								{
									menu.add(new Ext.menu.Item(
										{
											text: number + ". " + wnd.title,
											iconCls : 'checked16',
											checked: true,
											handler: function()
											{
												Ext.getCmp(wnd.id).toFront();
											}
										})
									);
									number++;
								}
								else
								{
									menu.add(new Ext.menu.Item(
										{
											text: number + ". " + wnd.title,
											iconCls : 'x-btn-text',
											handler: function()
											{
												Ext.getCmp(wnd.id).toFront();
											}
										})
									);
									number++;
								}
							}
						});
						if ( menu.items.getCount() == 0 )
							menu.add({
								text: langs('Открытых окон нет'),
								iconCls : 'x-btn-text',
								handler: function()
								{
								}
							});
						else
						{
							menu.add(new Ext.menu.Separator());
							menu.add(new Ext.menu.Item(
								{
									text: langs('Закрыть все окна'),
									iconCls : 'close16',
									handler: function()
									{
										Ext.WindowMgr.each(function(wnd){
											if ( wnd.isVisible() )
											{
												wnd.hide();
											}
										});
									}
								})
							);
						}
					}
				},
				menu: new Ext.menu.Menu({
					items: [
						{
							text: langs('Данные об учетной записи пользователя'),
							nn: 'action_user_about',
							iconCls: 'user16',
							menu: new Ext.menu.Menu(
								{
									//plain: true,
									id: 'user_menu',
									items:
										[
											{
												disabled: true,
												iconCls: 'user16',
												text: '<b>Информация о пользователе</b><br/>'+'Имя : '+UserName+'<br/>'+'E-mail : '+UserEmail+'<br/>'+'Описание : '+UserDescr+'<br/>'+'МО : '+Ext.globalOptions.globals.lpu_nick,
												xtype: 'tbtext'
											}
										]
								})
						},
						{
							nn: 'action_settings',
							text: langs('Настройки'),
							tooltip: langs('Просмотр и редактирование настроек'),
							iconCls : 'settings16',
							handler: function()
							{
								getWnd('swOptionsWindow').show();
							}
						},
						{
							nn: 'action_selectMO',
							text: langs('Выбор МО'),
							tooltip: langs('Выбор МО'),
							hidden: !isSuperAdmin(),
							iconCls: 'lpu-select16',
							handler: function()
							{
								Ext.WindowMgr.each(function(wnd){
									if ( wnd.isVisible() )
									{
										wnd.hide();
									}
								});
								getWnd('swSelectLpuWindow').show({});
							}
						},
						{
							text: langs('Выбор АРМ по умолчанию'),
							tooltip: langs('Выбор АРМ по умолчанию'),
							iconCls: 'lab-assist16',
							handler: function()
							{
								getWnd('swSelectWorkPlaceWindow').show();
							}
						},
						{
							nn: 'action_UserProfile',
							text: langs('Мой профиль'),
							tooltip: langs('Профиль пользователя'),
							iconCls : 'user16',
							hidden: false,
							handler: function()
							{
								args = {};
								args.action = 'edit';
								getWnd('swUserProfileEditWindow').show(args);
							}
						},
						{
							text: langs('План выхода на смену автомобилей и бригад'),
							tooltip: langs('План выхода на смену автомобилей и бригад'),
							hidden: getRegionNick() != 'ufa',
							handler: function(){
								getWnd('swSmpWorkPlanSearchWindow').show();
							}
						},
						{
							nn: 'action_TemperedDrugs',
							text: langs('Импорт отпущенных ЛС'),
							tooltip: langs('Отпущенные ЛС'),
							iconCls: 'adddrugs-icon16',
							handler: function()
							{
								getWnd('swTemperedDrugsWindow').show();
							},
							//hidden: (getGlobalOptions().region.nick != 'ufa')
							hidden: !(getRegionNick() == 'ufa' && isSuperAdmin())
						},
						{
							nn: 'action_ImportCardNumbers',
							text: langs('Загрузка номеров карт 110/у'),
							tooltip: langs('Загрузка номеров карт 110/у'),
							iconCls: 'adddrugs-icon16',
							hidden: true,
							listeners:{
								render: function(){
									var sysNick = getGlobalOptions().CurMedServiceType_SysNick;
									if((sysNick == 'mstat' && getGlobalOptions().lpu_id == 150011) || location.hostname == '127.0.0.1'){
											this.show();
									}
								},
							},
							handler: function(){
								getWnd('swSmoCallCardWindow').show();
							}
						},
						{
							nn: 'action_Miac',
							text: langs('Выгрузка для МИАЦ'),
							tooltip: langs('Выгрузка данных для МИАЦ'),
							iconCls : 'service-reestrs16',
							handler: function() {
								getWnd('swMiacExportWindow').show();
							},
							hidden: (getGlobalOptions().region.nick != 'ufa')
						},
						{
							nn: 'action_Rrl',
							text: langs('Выгрузка РРЛ'),
							tooltip: langs('Выгрузка регистра региональных льготников'),
							handler: function()
							{
								getWnd('swRrlExportWindow').show();
							},
							hidden: (getGlobalOptions().region.nick != 'ufa')
						},
						{
							text: langs('Окна'),
							nn: 'action_windows',
							iconCls: 'windows16',
							listeners: {
								'click': function(e) {
									var menu = Ext.menu.MenuMgr.get('wpfdw_menu_windows');
									menu.removeAll();
									var number = 1;
									Ext.WindowMgr.each(function(wnd){
										if ( wnd.isVisible() )
										{
											if ( Ext.WindowMgr.getActive().id == wnd.id )
											{
												menu.add(new Ext.menu.Item(
													{
														text: number + ". " + wnd.title,
														iconCls : 'checked16',
														checked: true,
														handler: function()
														{
															Ext.getCmp(wnd.id).toFront();
														}
													})
												);
												number++;
											}
											else
											{
												menu.add(new Ext.menu.Item(
													{
														text: number + ". " + wnd.title,
														iconCls : 'x-btn-text',
														handler: function()
														{
															Ext.getCmp(wnd.id).toFront();
														}
													})
												);
												number++;
											}
										}
									});
									if ( menu.items.getCount() == 0 )
										menu.add({
											text: langs('Открытых окон нет'),
											iconCls : 'x-btn-text',
											handler: function()
											{
											}
										});
									else
									{
										menu.add(new Ext.menu.Separator());
										menu.add(new Ext.menu.Item(
											{
												text: langs('Закрыть все окна'),
												iconCls : 'close16',
												handler: function()
												{
													Ext.WindowMgr.each(function(wnd){
														if ( wnd.isVisible() )
														{
															wnd.hide();
														}
													});
												}
											})
										);
									}
								},
								'mouseover': function() {
									var menu = Ext.menu.MenuMgr.get('wpfdw_menu_windows');
									menu.removeAll();
									var number = 1;
									Ext.WindowMgr.each(function(wnd){
										if ( wnd.isVisible() )
										{
											if ( Ext.WindowMgr.getActive().id == wnd.id )
											{
												menu.add(new Ext.menu.Item(
													{
														text: number + ". " + wnd.title,
														iconCls : 'checked16',
														checked: true,
														handler: function()
														{
															Ext.getCmp(wnd.id).toFront();
														}
													})
												);
												number++;
											}
											else
											{
												menu.add(new Ext.menu.Item(
													{
														text: number + ". " + wnd.title,
														iconCls : 'x-btn-text',
														handler: function()
														{
															Ext.getCmp(wnd.id).toFront();
														}
													})
												);
												number++;
											}
										}
									});
									if ( menu.items.getCount() == 0 )
										menu.add({
											text: langs('Открытых окон нет'),
											iconCls : 'x-btn-text',
											handler: function()
											{
											}
										});
									else
									{
										menu.add(new Ext.menu.Separator());
										menu.add(new Ext.menu.Item(
											{
												text: langs('Закрыть все окна'),
												iconCls : 'close16',
												handler: function()
												{
													Ext.WindowMgr.each(function(wnd){
														if ( wnd.isVisible() )
														{
															wnd.hide();
														}
													});
												}
											})
										);
									}
								}
							},
							menu: new Ext.menu.Menu(
								{
									//plain: true,
									id: 'wpfdw_menu_windows',
									items: [
										'-'
									]
								}),
							tabIndex: -1
						}
					]
				})
			},
			action_Svid: {
				nn: 'action_Svid',
				tooltip: langs('Свидетельства'),
				text: langs('Свидетельства'),
				iconCls : 'medsvid32',
				disabled: false,
				hidden: (!isMedSvidAccess() || (getGlobalOptions().lpu_isLab == 2) || getGlobalOptions().IsSMPServer),
				menuAlign: 'tr?',
				menu: new Ext.menu.Menu({
					items: [
						{
							text: langs('Свидетельства о рождении'),
							tooltip: langs('Свидетельства о рождении'),
							iconCls: 'svid-birth16',
							handler: function()
							{
								getWnd('swMedSvidBirthStreamWindow').show({action: 'view', ARMType: 'mstat'});
							},
							hidden: false
						},
						{
							text: langs('Свидетельства о смерти'),
							tooltip: langs('Свидетельства о смерти'),
							iconCls: 'svid-death16',
							handler: function()
							{
								getWnd('swMedSvidDeathStreamWindow').show({ARMType: 'mstat'});
							}
						},
						{
							text: langs('Свидетельства о перинатальной смерти'),
							tooltip: langs('Свидетельства о перинатальной смерти'),
							iconCls: 'svid-pdeath16',
							handler: function()
							{
								getWnd('swMedSvidPntDeathStreamWindow').show({ARMType: 'mstat'});
							}
						},
						{
							text: langs('Печать бланков свидетельств'),
							tooltip: langs('Печать бланков свидетельств'),
							iconCls: 'svid-blank16',
							handler: function()
							{
								getWnd('swMedSvidSelectSvidType').show();
							},
							hidden: false
						}
					]
				})
			},
			action_PlanVolume: {
				iconCls : 'monitoring32',
				nn: 'action_PlanVolume',
				text: 'Планирование объёмов мед. помощи (бюджет)',
				tooltip: 'Планирование объёмов мед. помощи (бюджет)',
				hidden: !getRegionNick().inlist(['astra', 'ufa', 'kareliya', 'krym', 'perm', 'pskov']),
				handler: function() {
					getWnd('swPlanVolumeViewWindow').show();
				}
			},
			action_VolPlan: {
				iconCls : 'monitoring32',
				nn: 'action_VolPlan',
				text: 'Планирование объёмов',
				tooltip: 'Планирование объёмов',
				hidden: (getRegionNick() != 'ufa') || (String(getGlobalOptions().groups).indexOf('VolumePlanLPU', 0) < 0),
                                menuAlign: 'tr?',
				menu: new Ext.menu.Menu({
					items: [
						{
							text: 'Периоды фактических объёмов',
							tooltip: 'Периоды фактических объёмов',
							iconCls: 'datepicker-day16',
							handler: function()
							{
                                                            getWnd('swVolPeriodViewWindow').show();
							},
							hidden: true
						},
						{
							text: 'Расчёт фактических объёмов',
							tooltip: 'Расчёт фактических объёмов',
							iconCls: 'farm-inv16',
							handler: function()
							{
                                                            getWnd('swVolPlanCalcWindow').show();
							},
							hidden: true
						},
                                                {
							text: 'Заявки МО',
							tooltip: 'Заявки МО',
							iconCls: 'pol-eplstream16',
							handler: function()
							{
                                                            var params = {};
                                                            params.functionality = 'stat';
                                                            getWnd('swVolRequestViewWindow').show(params);
							},
							hidden: false
						}
					]
				})
			},
			action_PersonDopDispPlan: {
				nn: 'action_PersonDopDispPlan',
				text: 'Планы диспансеризации и профилактических медицинских осмотров',
				tooltip: 'Планы диспансеризации и профилактических медицинских осмотров',
				iconCls: 'pol-dopdisp16',
				hidden:  getGlobalOptions().IsSMPServer,
				handler: function()
				{
					getWnd('swPersonDopDispPlanListWindow').show();
				}.createDelegate(this)
			},
			action_CarAccidents: {
				iconCls : 'pol-dtp16',
				nn: 'action_CarAccidents',
				text: langs('Извещения о ДТП'),
				tooltip: langs('Извещения о ДТП'),
				hidden: (getRegionNick() == 'kz' || getGlobalOptions().IsSMPServer),
				menu: new Ext.menu.Menu({
					items: [
						{
							text: langs('Извещения ДТП о раненом: Просмотр'),
							tooltip: langs('Извещения ДТП о раненом: Просмотр'),
							iconCls: 'stac-accident-injured16',
							handler: function()
							{
								getWnd('swEvnDtpWoundWindow').show();
							},
							hidden: false
						},
						{
							text: langs('Извещения ДТП о скончавшемся: Просмотр'),
							tooltip: langs('Извещения ДТП о скончавшемся: Просмотр'),
							iconCls: 'stac-accident-dead16',
							handler: function()
							{
								getWnd('swEvnDtpDeathWindow').show();
							},
							hidden: false
						}
					]
				})
			},
			action_TFOMSQueryList: {
				nn: 'action_TFOMSQueryList',
				//hidden: getRegionNick().inlist(['by']),
				text: 'Запросы на просмотр ЭМК',
				tooltip: 'Запросы на просмотр ЭМК',
				iconCls: 'tfoms-query32',
				handler: function () {
					getWnd('swTFOMSQueryWindow').show({ARMType: 'mstat'});
				}
			},
			action_Treatments: {
				nn: 'action_Treatments',
				text: 'Обращения',
				menuAlign: 'tr',
				tooltip: 'Обращения',
				iconCls: 'reports32',
				hidden: !isUserGroup('TreatmentSpecialist'),
				menu: new Ext.menu.Menu({
					items: [
						{
							nn: 'action_swTreatmentSearchAction',
							handler: function() {
								if ( ! getWnd('swTreatmentSearchWindow').isVisible() ){
									getWnd('swTreatmentSearchWindow').show();
								}
							},
							iconCls : 'petition-search16',
							text: langs('Регистрация обращений: Поиск'),
							tooltip: langs('Регистрация обращений: Поиск')
							//hidden: !isAccessTreatment()
						},
						{
							nn: 'action_LpuScheduleWorkDoctor',
							handler: function() {
								if ( ! getWnd('swTreatmentReportWindow').isVisible() ){
									getWnd('swTreatmentReportWindow').show();
								}
							},
							iconCls : 'petition-report16',
							text: langs('Регистрация обращений: Отчетность'),
							tooltip: langs('Регистрация обращений: Отчетность')
							//hidden: !isAccessTreatment()
						}
					]
				})
			},
			action_Ers: {
				nn: 'action_Ers',
				text: 'ЭРС',
				menuAlign: 'tr',
				tooltip: 'ЭРС',
				// todo
				//iconCls: 'reports32',
				//hidden: !(isUserGroup('WorkGraph')),
				/*Кнопка доступна для пользователей, включенных в любую из групп доступа:
				•	ЭРС. Оформление документов
				•	ЭРС. Руководитель МО
				•	ЭРС. Бухгалтер*/
				menu: [{
					text: 'Журнал Родовых сертификатов',
					handler: function () {
						getWnd('swEvnErsJournalWindow').show();
					}
				}, {
					text: 'Журнал Талонов',
					handler: function () {

					}
				}, {
					text: 'Журнал учета детей',
					handler: function () {

					}
				}, {
					text: 'Журнал талонов и счета на оплату',
					handler: function () {

					}
				}]
			},
			action_ServiceListLog: {
				nn: 'action_ServiceListLog',
				text: langs('Журнал работы сервисов'),
				tooltip: langs('Журнал работы сервисов'),
				iconCls: 'reports32',
				hidden: getRegionNick().inlist(['kz']),
				handler: function(){
					getWnd('swServiceListLogWindow').show({ARMType: 'mstat'});
				}.createDelegate(this)
			}
		};

		//добавляем элементы в "Извещения" по 184895
		if (getRegionNick()=='vologda'){
			this.buttonPanelActions.action_Notify.menu.addMenuItem(swPromedActions.EvnInfectNotify);
		}

		if(isUserGroup('MedSvidDeath')){
			this.buttonPanelActions.action_MedSvidDeath= {
				handler: function() {
					getWnd('swMedSvidDeathStreamWindow').show();
				},
				//hidden: !isUserGroup('MedSvidDeath'),
				hidden: isMedSvidAccess() || getGlobalOptions().IsSMPServer, // При добавлению пользователю группы "Медсвидетельства" в АРМ статистика не должно отображаться кнопки Медсвидетельства о смерти.
				iconCls : 'medsvid32',
				nn: 'action_MedSvidDeath',
				text: WND_FRW,
				tooltip: langs('Мед. свидетельства о смерти')
			}
		}
		this.onKeyDown = function (inp, e) {
			if (e.getKey() == Ext.EventObject.ENTER) {
				e.stopEvent();
				this.doSearch();
			}
		}.createDelegate(this);

		this.FilterPanel = new sw.Promed.BaseWorkPlaceFilterPanel({
			owner: form,
			labelWidth: 120,
			filter: {
				title: langs('Фильтры'),
				layout: 'column',
				items: [{
					layout: 'form',
					items: [{
						layout: 'form',
						labelWidth: 100,
						items: [{
							xtype: 'textfieldpmw',
							width: 220,
							name: 'Search_SurName',
							fieldLabel: langs('Фамилия'),
							listeners: {
								'keydown': form.onKeyDown
							}
						}]
					}, {
						layout: 'form',
						labelWidth: 100,
						items: [{
							allowBlank: true,
							codeField: 'EvnClass_Code',
							displayField: 'EvnClass_Name',
							editable: false,
							enableKeyEvents: true,
							fieldLabel: langs('Тип документа'),
							hiddenName: 'EvnClass_id',
							hideEmptyRow: false,
							ignoreIsEmpty: true,
							listeners: {
								'keydown': form.onKeyDown
							},
							listWidth: 300,
							store: new Ext.data.SimpleStore({
								autoLoad: true,
								data: [
									[ 2, 1, langs('Талон амбулаторного пациента') ],
									[ 30, 2, langs('Карта выбывшего из стационара') ]
								],
								fields: [
									{ name: 'EvnClass_id', type: 'int'},
									{ name: 'EvnClass_Code', type: 'int'},
									{ name: 'EvnClass_Name', type: 'string'}
								],
								key: 'EvnClass_id',
								sortInfo: { field: 'EvnClass_Code' }
							}),
							tpl: new Ext.XTemplate(
									'<tpl for="."><div class="x-combo-list-item">',
									'<font color="red">{EvnClass_Code}</font>&nbsp;{EvnClass_Name}',
									'</div></tpl>'
							),
							valueField: 'EvnClass_id',
							width: 220,
							xtype: 'swbaselocalcombo'
						}]
					},{
                        layout: 'column',
                        items: [{
                            border: false,
                            layout: 'form',
                            items: [{
                                style: "padding-left: 20px",
                                xtype: 'button',
                                id: form.id + 'BtnSearch',
                                text: langs('Найти'),
                                iconCls: 'search16',
                                handler: function() {
                                    form.doSearch();
                                }.createDelegate(form)
                            }]
                        },{
                            border: false,
                            layout: 'form',
                            items: [{
                                style: "padding-left: 10px",
                                xtype: 'button',
                                id: form.id + 'BtnClear',
                                text: langs('Сброс'),
                                iconCls: 'reset16',
                                handler: function() {
                                    form.doReset();
                                }.createDelegate(form)
                            }]
                        }, {
							layout: 'form',
							items:
								[{
									style: "padding-left: 10px",
									xtype: 'button',
									text: langs('Считать с карты'),
									iconCls: 'idcard16',
									handler: function()
									{
										form.readFromCard();
									}
								}]
						}]
                    }]
				}, {
					layout: 'form',
					items: [{
						layout: 'form',
						labelWidth: 75,
						items: [{
							xtype: 'textfieldpmw',
							width: 120,
							name: 'Search_FirName',
							fieldLabel: langs('Имя'),
							listeners: {
								'keydown': form.onKeyDown
							}
						}]
					}, {
						layout: 'form',
						labelWidth: 75,
						items: [{
							enableKeyEvents: true,
							fieldLabel: langs('Номер'),
							listeners: {
								'keydown': form.onKeyDown
							},
							name: 'Evn_NumCard',
							width: 120,
							xtype: 'textfield'
						}]
					}]
				}, {
					layout: 'form',
					items: [{
						layout: 'form',
						labelWidth: 100,
						items: [{
							xtype: 'textfieldpmw',
							width: 120,
							name: 'Search_SecName',
							fieldLabel: langs('Отчество'),
							listeners: {
								'keydown': form.onKeyDown
							}
						}]
					}, {
						layout: 'form',
						labelWidth: 100,
						items: [{
							fieldLabel: langs('Подразделение'),
							listeners: {
                                'keydown': form.onKeyDown
							},
                            listWidth: 400,
	                        hiddenName:'LpuBuilding_id',
							width: 120,
							xtype: 'swlpubuildingcombo'
						}]
					}]
				}, {
					layout: 'form',
					items: [{
						layout: 'form',
						labelWidth: 50,
						items: [{
							xtype: 'swdatefield',
							format: 'd.m.Y',
							plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
							name: 'Search_BirthDay',
							fieldLabel: langs('ДР'),
							listeners: {
								'keydown': form.onKeyDown
							}
						}]
					}]
				},{
                    autoHeight: true,
                    labelWidth: 75,
                    style: 'padding: 2px; margin: 0 0 0 5px; ',
                    title: langs('Диагноз'),
                    xtype: 'fieldset',
                    width: 300,
                    items: [{
                        fieldLabel: langs('Диазноз с'),
                        width: 200,
                        hiddenName: 'Diag_From',
                        valueField:'Diag_Code',
                        xtype: 'swdiagcombo'
                    }, {
                        fieldLabel: langs('по'),
                        width: 200,
                        hiddenName: 'Diag_To',
                        valueField:'Diag_Code',
                        xtype: 'swdiagcombo'
                    }]
                }]
			}
		});

		this.GridPanel = new sw.Promed.ViewFrame({
			actions: [
				{ name: 'action_add', hidden: true },
				{ name: 'action_edit', disabled: getGlobalOptions().IsSMPServer, hidden: getGlobalOptions().IsSMPServer, handler: function() { form.openEvnEditWindow('edit'); } },
				{ name: 'action_view', disabled: getGlobalOptions().IsSMPServer, handler: function() { form.openEvnEditWindow('view'); } },
				{ name: 'action_delete', disabled: getGlobalOptions().IsSMPServer, handler: function() { form.deleteEvn(); } },
				{ name: 'action_refresh', disabled: getGlobalOptions().IsSMPServer },
				{ name: 'action_print', text: langs('Печать'), tooltip: BTN_GRIDPRINT_TIP, icon: 'img/icons/print16.png',
					menuConfig: {
						print_008y: {
							name: 'print008yKVS',
							text: 'Форма 008/у для КВС',
							hidden: true,
							handler: function(){
								var record = form.GridPanel.ViewGridPanel.getSelectionModel().getSelected()
								printBirt({
									'Report_FileName': 'f008u_all.rptdesign',
									'Report_Params': '&paramEvnPS=' + record.get('Evn_id'),
									'Report_Format': 'pdf'
								});
							}
						},
						print_list_008y: {
							name: 'printList008yKVS',
							text: 'Форма 008/у для списка КВС',
							hidden: true,
							handler: function(){
								var periodStartDate = new Date(form.dateMenu.getValue1());
								var periodEndDate = new Date(form.dateMenu.getValue2());
								var timeDiff = Math.abs(periodEndDate.getTime() - periodStartDate.getTime());
								var diffDays = Math.ceil(timeDiff / (1000 * 3600 * 24));

								if( diffDays > 31 ){
									sw.swMsg.alert(langs('Сообщение'), langs('Печать формы 008/у доступна только для периода, не превышающего 31 день.'));
									return false;
								}

								var KVC_items = [];
								var rows = form.GridPanel.getGrid().getStore().getRange();
								rows.forEach(function(row, i, arr) {
									if(row.get('EvnClass_id') === 30){
										KVC_items.push(row.get('Evn_id'));
									}
								});

								if(KVC_items.length){
									printBirt({
										'Report_FileName': 'f008u_all.rptdesign',
										'Report_Params': '&paramEvnPS=' + KVC_items,
										'Report_Format': 'pdf'
									});
								}

								return false;
							}
						},
					}
				},
			],
			autoExpandColumn: 'autoexpand',
			autoLoadData: false,
			dataUrl: '/?c=Common&m=loadMedStatWorkPlace',
			grouping: true,
			groupingView: {
				showGroupName: true,
				showGroupsText: true
			},
			id: form.id + 'MedStatWorkPlacePanel',
			onDblClick: function() {
				this.onEnter();
			},
			onEnter: function() {
				if ( !this.ViewActions.action_edit.isDisabled() ) {
					this.ViewActions.action_edit.execute();
				}
				else {
					this.ViewActions.action_view.execute();
				}
			},
			onLoadData: function(sm, index, record) {
				if( getGlobalOptions().region.nick == 'vologda' ){
					// проверяем что в списке есть документы с типом "КВС" (EvnClass_id == 30)
					var KVC_items = [];
					var rows = this.getGrid().getStore().getRange();
					rows.forEach(function(row, i, arr) {
						if(row.get('EvnClass_id') === 30){
							KVC_items.push(row.get('Evn_id'));
						}
					});
					if( KVC_items.length ){
						this.getAction('action_print').menu.print_list_008y.setHidden(false);
					}else{
						this.getAction('action_print').menu.print_list_008y.setHidden(true);
					}
				}
			},
			onRowSelect: function(sm, index, record) {
				// печать формы 008/y доступна только для документов с типом "КВС" (EvnClass_id == 30)
				if (record && record.get('EvnClass_id') === 30 && getGlobalOptions().region.nick == 'vologda') {
					this.GridPanel.getAction('action_print').menu.print_008y.setHidden(false);
				}else{
					this.GridPanel.getAction('action_print').menu.print_008y.setHidden(true);
				}
			}.createDelegate(this),
			pageSize: 20,
			region: 'center',
			stringfields: [
				// Поля для отображение в гриде
				{ name: 'Evn_id', type: 'int', header: 'ID', key: true },
				{ name: 'EvnClass_id', type: 'int', hidden: true },
				{ name: 'Person_id', type: 'int', hidden: true },
				{ name: 'PersonEvn_id', type: 'int', hidden: true },
				{ name: 'Server_id', type: 'int', hidden: true },
				{ name: 'Person_Surname', hidden: true },
				{ name: 'Person_Firname', hidden: true },
				{ name: 'Person_Secname', hidden: true },
				{ name: 'pmUser_insID', type: 'int', hidden: true },
				{ name: 'EvnClass_Name', header: langs('Тип документа'), type: 'string', sortable: true, width: 100 },
				{ name: 'Evn_NumCard', header: langs('Номер'), type: 'string', sortable: true, width: 80 },
				{ name: 'Person_FIO', header: langs('Пациент'), type: 'string', id: 'autoexpand' },
				{ name: 'Person_BirthDay', header: langs('Дата рождения'), type: 'date', width: 100 },
				{ name: 'LpuBuilding_Name', header: langs('Подразделение'), type: 'string', width: 200 },
				{ name: 'Polis_SerNum', header: 'Полис', type: 'string', width: 100, hidden:(getRegionNick() == 'kz') },
				{ name: 'Person_Snils', header: 'СНИЛС', type: 'string', width: 100, hidden:(getRegionNick() == 'kz') },
				{ name: 'LpuSection_Name', header: langs('Отделение'), width: 200 },
				{ name: 'MedPersonal_Fio', header: langs('Врач'), width: 200 },
				{ name: 'Diag_Name', header: langs('Диагноз'), width: 200 },
				{ name: 'Evn_setDate', header: langs('Дата начала'), type: 'date', width: 100 },
				{ name: 'Evn_disDate', header: langs('Дата окончания'), type: 'date', width: 100 },
				{ name: 'PayType_Name', header: langs('Вид оплаты'), type: 'string', width: 100 },
				{ name: 'PrehospType_Name', header: langs('Тип госпитализации'), type: 'string', width: 100 },
				{ name: 'EvnPS_HospCount', header: langs('Количество госпитализаций'), type: 'string', width: 100 },
				{ name: 'LeaveType_Name', header: langs('Исход госпитализации'), type: 'string', width: 100 }
			],
			title: langs('Журнал рабочего места'),
			totalProperty: 'totalCount'
		});

		sw.Promed.swWorkPlaceMedStatWindow.superclass.initComponent.apply(this, arguments);
	}
});
