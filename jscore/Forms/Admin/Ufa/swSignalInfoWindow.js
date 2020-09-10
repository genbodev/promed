/*
 * Апаев А.В. Сигнальная информация для АРМ врача поликлиники 
 * https://redmine.swan.perm.ru/issues/137508
 */


//диапазон дат для сигнальной информации
sw.Promed.SignalDateRangeField = Ext.extend(Ext.form.DateRangeField, {
	plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
	formatDate: function (date) {
		return Ext.isDate(date) ? date.dateFormat(this.format) : date;
	},
	validateValue: function (value) {
		var ar = value.split(' - ');
		if (ar[0] == '__.__.____' || ar[1] == '__.__.____') {
			this.markInvalid(langs('Введите диапазон дат'));
			return false;
		}
		if (0 == value.length)
		{
			if (this.allowBlank) {
				return true;
			} else {
				this.markInvalid("Поле не может быть пустым");
				return false;
			}
		}
		ar[0] = this.formatDate(ar[0]);
		d1 = this.parseDate(ar[0]);
		if (!d1 && ar[0] != '__.__.____') {
			this.markInvalid(String.format("Первая дата введена неправильно", ar[0], this.format));
			return false;
		}

		ar[1] = this.formatDate(ar[1]);
		d2 = this.parseDate(ar[1]);
		if (!d2 && ar[1] != '__.__.____') {
			this.markInvalid(String.format("Вторая дата введена неправильно", ar[1], this.format));
			return false;
		}
		if (d1 > d2) {
			this.markInvalid(langs('Дата начала должна быть меньше даты конца'));
			return false;
		}
		var time1 = d1.getTime();
		var time2 = d2.getTime();
		if (this.minValue && time1 < this.minValue.getTime()) {
			this.markInvalid(String.format(this.minText, this.formatDate(this.minValue)));
			return false;
		}
		if (this.maxValue && time1 > this.maxValue.getTime()) {
			this.markInvalid(String.format(this.maxText, this.formatDate(this.maxValue)));
			return false;
		}

		if (this.minValue && time2 < this.minValue.getTime()) {
			this.markInvalid(String.format(this.minText, this.formatDate(this.minValue)));
			return false;
		}
		if (this.maxValue && time2 > this.maxValue.getTime()) {
			this.markInvalid(String.format(this.maxText, this.formatDate(this.maxValue)));
			return false;
		}
		return true;
	}
});

//дата для сигнальной информации
sw.Promed.SignalDateField = Ext.extend(Ext.form.DateField, {
	plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
	formatDate: function (date) {
		return Ext.isDate(date) ? date.dateFormat(this.format) : date;
	},
	validateValue: function (value) {
		return true;
		var ar = value.split(' - ');
		if (ar[0] == '__.__.____' || ar[1] == '__.__.____') {
			this.markInvalid(langs('Введите диапазон дат'));
			return false;
		}
		if (0 == value.length)
		{
			if (this.allowBlank) {
				return true;
			} else {
				this.markInvalid("Поле не может быть пустым");
				return false;
			}
		}
		ar[0] = this.formatDate(ar[0]);
		d1 = this.parseDate(ar[0]);
		if (!d1 && ar[0] != '__.__.____') {
			this.markInvalid(String.format("Первая дата введена неправильно", ar[0], this.format));
			return false;
		}

		ar[1] = this.formatDate(ar[1]);
		d2 = this.parseDate(ar[1]);
		if (!d2 && ar[1] != '__.__.____') {
			this.markInvalid(String.format("Вторая дата введена неправильно", ar[1], this.format));
			return false;
		}
		if (d1 > d2) {
			this.markInvalid(langs('Дата начала должна быть меньше даты конца'));
			return false;
		}
		var time1 = d1.getTime();
		var time2 = d2.getTime();
		if (this.minValue && time1 < this.minValue.getTime()) {
			this.markInvalid(String.format(this.minText, this.formatDate(this.minValue)));
			return false;
		}
		if (this.maxValue && time1 > this.maxValue.getTime()) {
			this.markInvalid(String.format(this.maxText, this.formatDate(this.maxValue)));
			return false;
		}

		if (this.minValue && time2 < this.minValue.getTime()) {
			this.markInvalid(String.format(this.minText, this.formatDate(this.minValue)));
			return false;
		}
		if (this.maxValue && time2 > this.maxValue.getTime()) {
			this.markInvalid(String.format(this.maxText, this.formatDate(this.maxValue)));
			return false;
		}
		return true;
	}
});

sw.Promed.swSignalInfoWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'SignalInfoWindow',
	MorbusType_id: false,
	modal: true,
	title: langs('Сигнальная информация для врача'),
	maximized: true,
	maximizable: true,
	layout: 'border',
	//filterName: 'swSignalInfoWindow',
	titleCollapse: true,
	closeAction: 'hide',
	closable: false,
	//collapsible: true,
	userMedStaffFact: null,
	// onHide: Ext.emptyFn
	// Поиск
	doSearch: function (tabId, tabIdP)
	{
		var win = this;
		if (!tabId) {
			tabId = this.tabPanel.getActiveTab().getId();
		}


		var params = new Object();
		params.start = 0;
		params.limit = 100;

		if (tabId == 'tab_grid') {
			// Список госпитализированных (Выписка)
			win.grid.setDataUrl('/?c=EvnPS&m=loadHospitalizationsGrid');
			var base_form = win.filtergrid.getForm();
			if (!base_form.isValid() ) return this.warning();
			params.EvnPS_disDateTime_Start = base_form.getValues().EvnPS_disDateTime.slice(0,10);
			params.EvnPS_disDateTime_End = base_form.getValues().EvnPS_disDateTime.slice(13,23);
			params.Lpu_aid = this.userMedStaffFact.Lpu_id; // id ЛПУ
			params.MedPersonal_id = this.userMedStaffFact.MedPersonal_id; //id врача
			params.LpuRegion_id = this.userMedStaffFact.LpuRegion_id;
			params.SignalInfo = 1;

			this.grid.removeAll();
			this.grid.loadData({globalFilters: params});
		}
		/*var and = ' - ';
		 var dataStart = Ext.util.Format.date(this.dateMenu.getValue1(), 'd.m.Y');
		 if (dataStart == '') {
		 and = '';
		 }*/

		if (tabId == 'tab_EvnUsluga') {
			// Параклинические услуги
			win.EvnUsluga.setDataUrl('/?c=Search&m=searchData');
			var base_form = win.filterEvnUsluga.getForm();
			if (!base_form.isValid() ) return this.warning();
			params.PersonPeriodicType_id = 1;
			params.PersonCardStateType_id = 1;
			params.PrivilegeStateType_id = 1;
			params.MedPersonal_id = this.userMedStaffFact.MedPersonal_id;
			//params.MedPersonal_did = this.userMedStaffFact.MedPersonal_id; // кем направлен (врач)
			params.AttachLpu_id = this.userMedStaffFact.Lpu_id;
			params.SearchFormType = 'EvnUslugaPar';
			//params.PrehospDirect_id = 1; //Кем направлен - Отделение МО
			params.EvnUslugaPar_setDate_Range = base_form.getValues().EvnUslugaPar_setDate_Range;
			params.SearchType_id = 1;
			params.SignalInfo = 1;

			this.EvnUsluga.removeAll();
			this.EvnUsluga.loadData({
				globalFilters: params
			});
		}

		if (tabId == 'tab_SearchGrid') {
			//ЛВН поиск
			win.SearchGrid.setDataUrl('/?c=Stick&m=loadEvnStickSearchGrid');
			//var base_form = this.findById('EvnStickBase_begDate');
			//params.EvnStickBase_begDate = (Ext.util.Format.date(base_form.getValue1(), 'd.m.Y') + ' - ' + '__.__.____');
			//params.EvnStickBase_endDate = ('__.__.____' + ' - ' + Ext.util.Format.date(base_form.getValue2(), 'd.m.Y'));
			params.EvnStick_IsClosed = 1;
			params.StickType_id = 1;
			params.SearchType_id = 1;
			params.CurLpuSection_id = 0;
			params.CurLpuUnit_id = 0;
			params.CurLpuBuilding_id = 0;
			params.MedPersonal1_id = this.userMedStaffFact.MedPersonal_id;
			params.SignalInfo = 1;

			this.SearchGrid.removeAll();
			this.SearchGrid.loadData({globalFilters: params});
		}

		if (tabId == 'tab_MedSvidDeath') {
			// Свидетельство о смерти 
			win.MedSvidDeath.setDataUrl('/?c=MedSvid&m=loadMedSvidDeathListGrid');
			var base_form = win.filterMedSvidDeath.getForm();
			if (!base_form.isValid() ) return this.warning();
			params.Lpu_id = this.userMedStaffFact.Lpu_id;
			params.Death_Date = base_form.getValues().Death_Date;
			params.viewMode = 2;
			params.IsActual = 2;
			params.MedPersonal_id = this.userMedStaffFact.MedPersonal_id;

			this.MedSvidDeath.removeAll();
			this.MedSvidDeath.loadData({globalFilters: params});
		}
		if (tabId == 'tab_PrivGridPanel') {
			// Регистр льготников
			win.PrivGridPanel.setDataUrl('/?c=Search&m=searchData');
			//var base_form = this.findById('Privilege_begDate');
			params.AttachLpu_id = this.userMedStaffFact.Lpu_id;
			//params.Privilege_endDate_Range = (Ext.util.Format.date(base_form.getValue1(), 'd.m.Y') + ' - ' + Ext.util.Format.date(base_form.getValue2(), 'd.m.Y'));
			params.PrivilegeStateType_id = 2;
			params.PersonCardStateType_id = 1;
			params.SearchFormType = 'PersonPrivilege';
			params.MedPersonal_id = this.userMedStaffFact.MedPersonal_id;
			params.SignalInfo = 1;
			this.PrivGridPanel.removeAll();
			this.PrivGridPanel.loadData({globalFilters: params});

		}
		if (tabId == 'tab_CmpCallCardSearch') {
			// Карты СМП
			var base_form = win.filterCmpCallCardSearch.getForm();
			if (!base_form.isValid() ) return this.warning();
			if (this.SignalInfoPersonPregnancy == true) {
				win.CmpCallCardSearch.setDataUrl('/?c=SignalInfo&m=loadPregnancyRouteSMP');
				params.MedPersonal_iid = this.userMedStaffFact.MedPersonal_id;
				params.Yesterday = 0;
				params.Lpu_iid = this.userMedStaffFact.Lpu_id;
			} else {
				params.PrivilegeStateType_id = 1;
				win.CmpCallCardSearch.setDataUrl('/?c=Search&m=searchData');
			}
			params.CmpCallCard_prmDate_Range = base_form.getValues().CmpCallCard_prmDate_Range;
			params.SearchType_id = 1;
			params.SearchFormType = 'CmpCloseCard';
			params.AttachLpu_id = this.userMedStaffFact.Lpu_id;
			params.MedPersonal_id = this.userMedStaffFact.MedPersonal_id;
			params.CmpCallCard_InRegistry = 0;
			params.PersonPeriodicType_id = 1;
			params.PersonCardStateType_id = 1;
			params.SignalInfo = 1;

			this.CmpCallCardSearch.removeAll();
			this.CmpCallCardSearch.loadData({globalFilters: params});

		}

		if (tabId == "tab_tabPanelPersonPregnancy") {
			if (!tabIdP) {
				tabIdP = this.tabPanelPersonPregnancy.getActiveTab().getId();
			}

			if (tabIdP == 'tab_PersonPregnancy') {
				//var filtersPanel = this.PersonPregnancyFiltersPanel.getForm();
				// var params = getAllFormFieldValues(filtersPanel);
				params.MedPersonal_iid = this.findById('S_MedPersonal_id').getValue();
				params.Type = 'rou';
				params.YesNo_id = 2;
				params.SignalInfo = 1;
				params.Lpu_iid = this.userMedStaffFact.Lpu_id;
				//params.PersonRegister_setDateRange = (Ext.util.Format.date(this.dateMenu.getValue1(), 'd.m.Y') + and + Ext.util.Format.date(this.dateMenu.getValue2(), 'd.m.Y')) || '';

				this.SignalInfoRecommRouterGrid.removeAll();
				this.SignalInfoRecommRouterGrid.getGrid().getStore().load({
					params: params
				});
			}

			if (tabIdP == 'tab_PregnancyRegion') {
				params.Type = 'PregnancyRegion';
				this.RecommRouterRegionGridPanel.removeAll();
				this.RecommRouterRegionGridPanel.getGrid().getStore().load({
					params: params
				});

			}

			if (tabIdP == 'tab_PregnancyNotInclude') {
				this.NotIncludeGridPanel.removeAll();
				this.NotIncludeGridPanel.getGrid().getStore().load();
			}
		}

		if (tabId == "tab_DispGridPanel") {

			if (!tabIdP) {
				tabIdP = this.tabDispGridPanel.getActiveTab().getId();
			}

			if (tabIdP == 'tab_DispGridPanel1') {
				params.MedPersonal_id = this.userMedStaffFact.MedPersonal_id;
				params.Lpu_id = this.userMedStaffFact.Lpu_id;
				this.DispGridPanel1.removeAll();
				this.DispGridPanel1.getGrid().getStore().load({
					params: params
				});
			}

			if (tabIdP == 'tab_DispGridPanel2') {
				params.MedPersonal_id = this.userMedStaffFact.MedPersonal_id;
				params.Lpu_id = this.userMedStaffFact.Lpu_id;
				this.DispGridPanel2.removeAll();
				this.DispGridPanel2.getGrid().getStore().load({
					params: params
				});
			}
		}

		if (tabId == 'tab_PersonNoVisitGridPanel') {
			var base_form = win.filterPersonNoVisit.getForm();
			if (!base_form.isValid() ) return this.warning();
			params.begDate = base_form.getValues().PersonNoVisit_prmDate_Range;
			//params.endDate = Ext.util.Format.date(base_form.getValue2(), 'Y-m-d');
			params.MedStaffFact_id = this.userMedStaffFact.MedStaffFact_id;
			params.Lpu_id = this.userMedStaffFact.Lpu_id;
			this.PersonNoVisitGridPanel.removeAll();
			this.PersonNoVisitGridPanel.loadData({globalFilters: params});

		}

		if (tabId == 'tab_cdkPanel') {
			var base_form = win.cdkFilterPanel.getForm();
			if (!base_form.isValid() ) return this.warning();
			params.EvnDirection_setDate = base_form.getValues().EvnDirection_setDate;
			//params.endDate = Ext.util.Format.date(base_form.getValue2(), 'Y-m-d');
			params.MedStaffFact_id = this.userMedStaffFact.MedStaffFact_id;
			params.Lpu_id = this.userMedStaffFact.Lpu_id;
			this.cdkGridPanel.removeAll();
			this.cdkGridPanel.loadData({globalFilters: params});
		}

		//Вкладка «Не проведена консультация» 
		if (tabId == "tab_tabPanelPregnancyRouteNoConsultation") {
			if (!tabIdP) {
				tabIdP = this.tabPanelPregnancyRouteNoConsultation.getActiveTab().getId();
			}
			//по МО
			if (tabIdP == 'tab_PregnancyRouteNoConsultation') {
				params.MedPersonal_iid = this.findById('NoConsultationMedPersonal_id').getValue();
				params.Lpu_iid = this.userMedStaffFact.Lpu_id;
				this.PregnancyRouteNoConsultation.removeAll();
				this.PregnancyRouteNoConsultation.loadData({globalFilters: params});
			}
			//по Региону
			if (tabIdP == 'tab_PregnancyRouteNotConsultationRegion') {
				params.Type = 'notConsultationRegion';
				this.PregnancyRouteNotConsultationRegion.removeAll();
				this.PregnancyRouteNotConsultationRegion.getGrid().getStore().load({
					params: params
				});

			}
		}

		//Вкладка «Находятся на госпитализации» 
		if (tabId == "tab_tabPanelPregnancyRouteHospital") {
			if (!tabIdP) {
				tabIdP = this.tabPanelPregnancyRouteHospital.getActiveTab().getId();
			}
			//по МО
			if (tabIdP == 'tab_PregnancyRouteHospital') {
				params.MedPersonal_iid = this.findById('HospitalMedPersonal_id').getValue();
				params.Lpu_iid = this.userMedStaffFact.Lpu_id;
				params.PregnancyRouteType = 'Hospital';
				this.PregnancyRouteHospital.removeAll();
				this.PregnancyRouteHospital.loadData({globalFilters: params});
			}
			//по Региону
			if (tabIdP == 'tab_PregnancyRouteHospitalRegion') {
				params.Type = 'HospitalRegion';
				this.PregnancyRouteHospitalRegion.removeAll();
				this.PregnancyRouteHospitalRegion.getGrid().getStore().load({
					params: params
				});

			}
		}

		if (tabId == 'tab_PregnancyRouteDisHospital') {
			var base_form = win.filterPregnancyRouteDisHospital.getForm();
			if (!base_form.isValid() ) return this.warning();
			params.PregnancyRouteType = 'DisHospital';
			params.MedPersonal_iid = this.userMedStaffFact.MedPersonal_id;
			params.Lpu_iid = this.userMedStaffFact.Lpu_id;
			params.Date_Range = base_form.getValues().PregnancyRouteDisHospital_disDateTime;
			this.PregnancyRouteDisHospital.removeAll();
			this.PregnancyRouteDisHospital.loadData({globalFilters: params});
		}
		if (tabId == 'tab_PregnancyRouteRiskСhange') {
			params.PregnancyRouteType = 'RiskСhange';
			params.MedPersonal_iid = this.userMedStaffFact.MedPersonal_id;
			params.Lpu_iid = this.userMedStaffFact.Lpu_id;
			this.PregnancyRouteRiskСhange.removeAll();
			this.PregnancyRouteRiskСhange.loadData({globalFilters: params});
		}
		//Регистр БСК
		if (tabId == 'tab_RegisterBSK') {
			win.RegisterBSK.setDataUrl('/?c=SignalInfo&m=loadListRegistBSK');
			var base_form = win.filterRegisterBSK.getForm();
			if (!base_form.isValid() ) return this.warning();
			params.Lpu_id = this.userMedStaffFact.Lpu_id;
			params.BSKRegistry_setDateNext = base_form.getValues().RegisterBSK_prmDate_Range;
			this.RegisterBSK.removeAll();
			this.RegisterBSK.loadData({globalFilters: params});
		}
		//КВИ
		if (tabId == 'tab_CVI') {
			params.MedPersonal_id = this.userMedStaffFact.MedPersonal_id;
			this.CVI.removeAll();
			this.CVI.loadData({globalFilters: params});
		}
		//Диспансерный учёт
		if (tabId == 'tab_PersonDispInfo') {
			var base_form = win.filterPersonDispInfo.getForm();
			if (!base_form.isValid() ) return this.warning();
			params.Lpu_id = this.userMedStaffFact.Lpu_id;
			params.MedPersonal_id = this.userMedStaffFact.MedPersonal_id;
			params.PersonDispInfo_prmDate_Range = base_form.getValues().PersonDispInfo_prmDate_Range;
			this.PersonDispInfo.removeAll();
			this.PersonDispInfo.loadData({globalFilters: params});
		}

		//Дистанционнный мониторинг
		if (tabId == 'tab_DistObserv') {
			var base_form = win.filterDistObserv.getForm();
			if (!base_form.isValid() ) return this.warning();
			params.Lpu_id = this.userMedStaffFact.Lpu_id;
			params.MedPersonal_id = this.userMedStaffFact.MedPersonal_id;
			params.DistObserv_Range = base_form.getValues().DistObserv_Range;
			this.DistObservSearch.removeAll();
			this.DistObservSearch.loadData({globalFilters: params});
		}

	},
	getButtonSearch: function () {
		return Ext.getCmp('SignalInfo_Search');
	},

	warning: function () {
		sw.swMsg.show(
				{icon: Ext.MessageBox.ERROR,
					icon: Ext.Msg.WARNING,
					msg: ERR_INVFIELDS_MSG,
					title: ERR_INVFIELDS_TIT,
					buttons: Ext.Msg.OK
				});
		return false;
		
	},
	//Открытие ЭМК 
	openEPHForm: function () {
		var tabId = this.tabPanel.getActiveTab().getId();
		var tabIdP;

		switch (tabId) {
			case 'tab_EvnUsluga':
				var record = this.EvnUsluga.getGrid().getSelectionModel().getSelected();
				break;
			case 'tab_grid':
				var record = this.grid.getGrid().getSelectionModel().getSelected();
				break;
			case 'tab_PrivGridPanel':
				var record = this.PrivGridPanel.getGrid().getSelectionModel().getSelected();
				break;
			case 'tab_SearchGrid':
				var record = this.SearchGrid.getGrid().getSelectionModel().getSelected();
				break;
			case 'tab_MedSvidDeath':
				var record = this.MedSvidDeath.getGrid().getSelectionModel().getSelected();
				break;
			case 'tab_CmpCallCardSearch':
				var record = this.CmpCallCardSearch.getGrid().getSelectionModel().getSelected();
				break;
			case 'tab_PersonNoVisitGridPanel':
				var record = this.PersonNoVisitGridPanel.getGrid().getSelectionModel().getSelected();
				break;
			case 'tab_cdkPanel':
				var record = this.cdkGridPanel.getGrid().getSelectionModel().getSelected();
				break;
			case 'tab_PregnancyRouteDisHospital':
				var record = this.PregnancyRouteDisHospital.getGrid().getSelectionModel().getSelected();
				break;
			case 'PregnancyRouteRiskСhange':
				var record = this.PregnancyRouteRiskСhange.getGrid().getSelectionModel().getSelected();
				break;
			case 'tab_tabPanelPersonPregnancy':
				tabIdP = this.tabPanelPersonPregnancy.getActiveTab().getId();
				if (tabIdP == 'tab_PersonPregnancy') {
					var record = this.SignalInfoRecommRouterGrid.getGrid().getSelectionModel().getSelected();
				}
				if (tabIdP == 'tab_PregnancyNotInclude') {
					var record = this.NotIncludeGridPanel.getGrid().getSelectionModel().getSelected();
				}
				break;
			case 'tab_tabPanelPregnancyRouteNoConsultation':
				var record = this.PregnancyRouteNoConsultation.getGrid().getSelectionModel().getSelected();
				break;
			case 'tab_tabPanelPregnancyRouteHospital':
				var record = this.PregnancyRouteHospital.getGrid().getSelectionModel().getSelected();
				break;
			case 'tab_PregnancyRouteRiskСhange':
				var record = this.PregnancyRouteRiskСhange.getGrid().getSelectionModel().getSelected();
				break;
			case 'tab_RegisterBSK':
				var record = this.RegisterBSK.getGrid().getSelectionModel().getSelected();
				break;
			case 'tab_CVI':
				var record = this.CVI.getGrid().getSelectionModel().getSelected();
				break;
			case 'tab_PersonDispInfo':
				var record = this.PersonDispInfo.getGrid().getSelectionModel().getSelected();
				break;
			case 'tab_DistObserv':
				var record = this.DistObservSearch.getGrid().getSelectionModel().getSelected();
				break;
		}
		
		var emk_wnd = getWnd('swPersonEmkWindow'+(sw.isExt6Menu ? 'Ext6':''));

		if (!record) 
		{
			Ext.Msg.alert(langs('Ошибка'), langs('Ошибка выбора записи!'));
			return false;
		}
		if (emk_wnd.isVisible()) 
		{
			Ext.Msg.alert(langs('Сообщение'), langs('Форма ЭМК (ЭПЗ) в данный момент открыта.'));
			return false;
		} else 
		{
			var params = {
				userMedStaffFact: this.userMedStaffFact,
				Person_id: record.get('Person_id'),
				Server_id: record.get('Server_id'),
				PersonEvn_id: record.get('PersonEvn_id'),
				mode: 'workplace',
				ARMType: 'common'
			};
			emk_wnd.show(params);
		}
	},
	openPersonPregnancyEditWindow: function (gridPanel) {
		var record = gridPanel.getGrid().getSelectionModel().getSelected();
		if (!record || Ext.isEmpty(record.get('PersonRegister_id'))) {
			return false;
		}
		var params = {};
		params.Person_id = record.get('Person_id');
		params.PersonRegister_id = record.get('PersonRegister_id');
		params.action = 'view';
		getWnd('swPersonPregnancyEditWindow').show(params);

	},

	//Получение периода за вчерашний день
	getPeriod1DaysLast: function (dayfrom,dayto)
	{	
		dayfrom == undefined ? dayfrom = -1 : dayfrom; 
		dayto == undefined ? dayto = -1 : dayto;
		var date = (Date.parseDate(getGlobalOptions().date, 'd.m.Y'));
		var date2 = date.add(Date.DAY, dayto).clearTime();
		var date1 = date.add(Date.DAY, dayfrom).clearTime();
		return Ext.util.Format.date(date1, 'd.m.Y') + ' - ' + Ext.util.Format.date(date2, 'd.m.Y');
	},
	//Получение периода за вчерашний день для Списка неявившихся
	getPeriod1DaysLast2: function ()
	{
		var date = (Date.parseDate(getGlobalOptions().date, 'd.m.Y'));
		var date1 = date.add(Date.DAY, -1).clearTime();
		return Ext.util.Format.date(date1, 'd.m.Y');
	},
	initComponent: function () {

		var win = this;

		//фильтры
		//Параклинические услуги
		this.filterEvnUsluga = new Ext.form.FormPanel(
				{
					bodyStyle: 'padding: 5px',
					border: false,
					region: 'north',
					autoHeight: true,
					labelAlign: 'left',
					frame: true,
					labelWidth: 150,
					items:
							[

								new sw.Promed.SignalDateRangeField({
									minValue: (Date.parseDate(getGlobalOptions().date, 'd.m.Y')).add(Date.DAY, -10).clearTime(),
									maxValue: getGlobalOptions().date,
									width: 177,
									plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
									fieldLabel: langs('Дата выполнения услуги'),
									id: 'EvnUslugaPar_setDate_Range',
									allowBlank: false,
									listeners: {
										'select': function (field, newValue, oldValue) {
											win.doSearch('tab_EvnUsluga');
										}
									}
								})
							],
							keys: [{
								fn: function() {
									win.doSearch('tab_EvnUsluga');
								},
								key: Ext.EventObject.ENTER,
								stopEvent: true
							}]
				});
		//Журнал госпитализации

		this.filtergrid = new Ext.form.FormPanel(
				{
					bodyStyle: 'padding: 5px',
					border: false,
					region: 'north',
					autoHeight: true,
					labelAlign: 'left',
					frame: true,
					labelWidth: 150,
					items:
							[
								new sw.Promed.SignalDateRangeField({
									minValue: (Date.parseDate(getGlobalOptions().date, 'd.m.Y')).add(Date.DAY, -10).clearTime(),
									maxValue: (Date.parseDate(getGlobalOptions().date, 'd.m.Y')),
									width: 160,
									plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
									fieldLabel: langs('Дата выписки'),
									id: 'EvnPS_disDateTime',
									allowBlank: false,
									listeners: {
										'select': function (field, newValue, oldValue) {
											if (this.SignalInfoPersonPregnancy == true) {
												win.doSearch('tab_PregnancyRouteDisHospital');
											} else {
												win.doSearch('tab_grid');
											}
										}.createDelegate(this)
									}

								})
							],
							keys: [{
								fn: function() {
									if (this.SignalInfoPersonPregnancy == true) {
										win.doSearch('tab_PregnancyRouteDisHospital');
									} else {
										win.doSearch('tab_grid');
									}
								},
								key: Ext.EventObject.ENTER,
								stopEvent: true
							}]
				});
		// Медсвид. о смерти
		this.filterMedSvidDeath = new Ext.form.FormPanel(
				{
					bodyStyle: 'padding: 5px',
					border: false,
					region: 'north',
					autoHeight: true,
					labelAlign: 'left',
					frame: true,
					labelWidth: 150,
					items:
							[
								new sw.Promed.SignalDateRangeField({
									minValue: (Date.parseDate(getGlobalOptions().date, 'd.m.Y')).add(Date.DAY, -10).clearTime(),
									maxValue: (Date.parseDate(getGlobalOptions().date, 'd.m.Y')),
									width: 160,
									plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
									fieldLabel: langs('Дата смерти'),
									id: 'Death_Date',
									allowBlank: false,
									listeners: {
										'select': function (field, newValue, oldValue) {
											win.doSearch('tab_MedSvidDeath');
										}
									}
								})
							],
							keys: [{
								fn: function() {
									win.doSearch('tab_MedSvidDeath');
								},
								key: Ext.EventObject.ENTER,
								stopEvent: true
							}]
			});
		// Карты СМП
		this.filterCmpCallCardSearch = new Ext.form.FormPanel(
				{
					bodyStyle: 'padding: 5px',
					border: false,
					region: 'north',
					autoHeight: true,
					labelAlign: 'left',
					frame: true,
					labelWidth: 150,
					items:
							[
								new sw.Promed.SignalDateRangeField({
									minValue: (Date.parseDate(getGlobalOptions().date, 'd.m.Y')).add(Date.DAY, -10).clearTime(),
									maxValue: (Date.parseDate(getGlobalOptions().date, 'd.m.Y')),
									width: 160,
									plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
									fieldLabel: langs('Дата приёма вызова'),
									id: 'CmpCallCard_prmDate_Range',
									allowBlank: false,
									listeners: {
										'select': function (field, newValue, oldValue) {
											win.doSearch('tab_CmpCallCardSearch');
										}
									}
								})
							],
							keys: [{
								fn: function() {
									win.doSearch('tab_CmpCallCardSearch');
								},
								key: Ext.EventObject.ENTER,
								stopEvent: true
							}]
				});

		// Карты СМП
		this.filterDistObserv = new Ext.form.FormPanel(
			{
				bodyStyle: 'padding: 5px',
				border: false,
				region: 'north',
				autoHeight: true,
				labelAlign: 'left',
				frame: true,
				labelWidth: 150,
				items:
						[
							new sw.Promed.SignalDateField({
								minValue: (Date.parseDate(getGlobalOptions().date, 'd.m.Y')).add(Date.DAY, -10).clearTime(),
								maxValue: (Date.parseDate(getGlobalOptions().date, 'd.m.Y')),
								value:(Date.parseDate(getGlobalOptions().date, 'd.m.Y')),
								width: 160,
								plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
								fieldLabel: langs('Дата'),
								id: 'DistObserv_Range',
								allowBlank: false,
								listeners: {
									'select': function (field, newValue, oldValue) {
										win.doSearch('tab_DistObserv');
									}
								}
							})
						],
						keys: [{
							fn: function() {
								win.doSearch('tab_DistObserv');
							},
							key: Ext.EventObject.ENTER,
							stopEvent: true
						}]
			});

		// Диспансеризация
		this.filterDispGridPanel = new Ext.form.FormPanel(
				{
					bodyStyle: 'padding: 5px',
					border: false,
					region: 'north',
					autoHeight: true,
					labelAlign: 'left',
					frame: true,
					labelWidth: 150,
					items:
							[
								new sw.Promed.SignalDateRangeField({
									minValue: (Date.parseDate(getGlobalOptions().date, 'd.m.Y')).add(Date.DAY, -10).clearTime(),
									maxValue: (Date.parseDate(getGlobalOptions().date, 'd.m.Y')),
									width: 160,
									plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
									fieldLabel: langs('Дата приёма вызова'),
									id: 'DispGridPanel_prmDate_Range',
									allowBlank: false
								})]


				});
		// Фильтр для списка не явившихся
		this.filterPersonNoVisit = new Ext.form.FormPanel(
				{
					bodyStyle: 'padding: 5px',
					border: false,
					region: 'north',
					autoHeight: true,
					labelAlign: 'left',
					frame: true,
					labelWidth: 100,
					items:
							[
								{
									minValue: (Date.parseDate(getGlobalOptions().date, 'd.m.Y')).add(Date.DAY, -10).clearTime(),
									maxValue: (Date.parseDate(getGlobalOptions().date, 'd.m.Y')),
									width: 100,
									allowBlank: false,
									fieldLabel: langs('Дата приёма'),
									id: 'PersonNoVisit_prmDate_Range',
									//format: 'd.m.Y',
									plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
									xtype: 'swdatefield',
									listeners: {
										'select': function (field, newValue, oldValue) {
											win.doSearch('tab_PersonNoVisitGridPanel');
										}
									}
								}
							],
							keys: [{
								fn: function() {
									win.doSearch('tab_PersonNoVisitGridPanel');
								},
								key: Ext.EventObject.ENTER,
								stopEvent: true
							}]
				});

		this.cdkFilterPanel = new Ext.form.FormPanel({
			bodyStyle: 'padding: 5px',
			border: false,
			region: 'north',
			autoHeight: true,
			labelAlign: 'left',
			frame: true,
			labelWidth: 150,
			items: [{
				fieldLabel: langs('Дата направления'),
				xtype: 'swdatefield',
				minValue: (Date.parseDate(getGlobalOptions().date, 'd.m.Y')).add(Date.DAY, -10).clearTime(),
				maxValue: (Date.parseDate(getGlobalOptions().date, 'd.m.Y')),
				value: getGlobalOptions().date,
				plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
				name: 'EvnDirection_setDate',
				listeners: {
					'select': function (field, newValue, oldValue) {
						win.doSearch('tab_cdkPanel');
					}
				}
			}],
			keys: [{
				fn: function() {
					win.doSearch('tab_cdkPanel');
				},
				key: Ext.EventObject.ENTER,
				stopEvent: true
			}]
		});

		this.filterPregnancyRouteDisHospital = new Ext.form.FormPanel(
			{
				bodyStyle: 'padding: 5px',
				border: false,
				region: 'north',
				autoHeight: true,
				labelAlign: 'left',
				frame: true,
				labelWidth: 150,
				items:
					[
						new sw.Promed.SignalDateRangeField({
							minValue: (Date.parseDate(getGlobalOptions().date, 'd.m.Y')).add(Date.DAY, -10).clearTime(),
							maxValue: (Date.parseDate(getGlobalOptions().date, 'd.m.Y')),
							width: 160,
							plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
							fieldLabel: langs('Дата выписки'),
							id: 'PregnancyRouteDisHospital_disDateTime',
							allowBlank: false,
							listeners: {
								'select': function (field, newValue, oldValue) {
									win.doSearch('tab_PregnancyRouteDisHospital');
								}.createDelegate(this)
							}

						})
					],
					keys: [{
						fn: function() {
							win.doSearch('tab_PregnancyRouteDisHospital');
						},
						key: Ext.EventObject.ENTER,
						stopEvent: true
					}]
			});

			this.filterRegisterBSK = new Ext.form.FormPanel(
				{
					bodyStyle: 'padding: 5px',
					border: false,
					region: 'north',
					autoHeight: true,
					labelAlign: 'left',
					frame: true,
					labelWidth: 150,
					items:
						[
							new sw.Promed.SignalDateRangeField({
								minValue: (Date.parseDate(getGlobalOptions().date, 'd.m.Y')).add(Date.DAY, -10).clearTime(),
								maxValue: (Date.parseDate(getGlobalOptions().date, 'd.m.Y')),
								width: 160,
								plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
								fieldLabel: langs('Дата анкетирования'),
								id: 'RegisterBSK_prmDate_Range',
								allowBlank: false,
								listeners: {
									'select': function (field, newValue, oldValue) {
										win.doSearch('tab_RegisterBSK');
									}.createDelegate(this)
								}

							})
						],
						keys: [{
							fn: function() {
								win.doSearch('tab_RegisterBSK');
							},
							key: Ext.EventObject.ENTER,
							stopEvent: true
						}]
				});
			
			//Вкладка «Диспансерный учёт». Фильтр Дата планового осмотра 
			this.filterPersonDispInfo = new Ext.form.FormPanel({
				bodyStyle: 'padding: 5px',
				border: false,
				region: 'north',
				autoHeight: true,
				labelAlign: 'left',
				frame: true,
				labelWidth: 150,
				items:
					[
						new sw.Promed.SignalDateRangeField({
							minValue: (Date.parseDate(getGlobalOptions().date, 'd.m.Y')).add(Date.DAY, -10).clearTime(),
							//maxValue: (Date.parseDate(getGlobalOptions().date, 'd.m.Y')),
							width: 160,
							plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
							fieldLabel: langs('Дата планового осмотра'),
							id: 'PersonDispInfo_prmDate_Range',
							allowBlank: false,
							listeners: {
								'select': function (field, newValue, oldValue) {
									win.doSearch('tab_PersonDispInfo');
								}.createDelegate(this)
							}
						})
					],
				keys: [{
					fn: function() {
						win.doSearch('tab_PersonDispInfo');},
					key: Ext.EventObject.ENTER,
					stopEvent: true
				}]
			});

		//Параклинические услуги

		this.EvnUsluga = new sw.Promed.ViewFrame({
			actions: [
				{name: 'action_add', hidden: true, disabled: true},
				{name: 'action_view', hidden: true, disabled: true},
				{name: 'action_delete', hidden: true, disabled: true},
				{name: 'action_edit', hidden: true, disabled: true},
				{name: 'action_refresh', handler: function () {
						this.findById('EUPSW_EvnUslugaParSearchGrid').getGrid().getStore().reload();
					}.createDelegate(this)},
				{name: 'action_print',
					menuConfig: {
						printCost: {name: 'printCost', hidden: !getRegionNick().inlist(['perm']), text: langs('Справка о стоимости лечения'), handler: function () {
								win.printCost()
							}}
					}
				}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			dataUrl: C_SEARCH,
			id: 'EUPSW_EvnUslugaParSearchGrid',
			pageSize: 100,
			paging: true,
			region: 'center',
			root: 'data',
			useEmptyRecord: false, // убираем пустую строку для подсчета
			stringfields: [
				{name: 'EvnUslugaPar_id', type: 'int', header: 'ID', key: true},
				{name: 'accessType', type: 'string', hidden: true},
				{name: 'EvnUslugaPar_IsSigned', type: 'int', hidden: true},
				{name: 'EvnXml_id', type: 'int', hidden: true},
				{name: 'XmlTemplate_HtmlTemplate', type: 'string', hidden: true},
				{name: 'Person_id', type: 'int', hidden: true},
				{name: 'PersonEvn_id', type: 'int', hidden: true},
				{name: 'Server_id', type: 'int', hidden: true},
				{name: 'Person_Surname', type: 'string', header: langs('Фамилия'), width: 150},
				{name: 'Person_Firname', type: 'string', header: langs('Имя'), width: 150},
				{name: 'Person_Secname', type: 'string', header: langs('Отчество'), width: 150},
				{name: 'Person_Birthday', type: 'date', format: 'd.m.Y', header: langs('Д/р'), width: 100},
				{name: 'Person_Age', type: 'int', header: langs('Возраст'), width: 100},
				{name: 'EvnUslugaPar_setDate', type: 'date', format: 'd.m.Y', header: langs('Дата выполнения'), width: 100},
				{name: 'MedPersonal_Fio', type: 'string', header: langs('Врач'), width: 250},
				{name: 'Usluga_Code', type: 'string', header: langs('Код услуги'), width: 100},
				{name: 'Usluga_Name', header: langs('Наименование услуги'), id: 'autoexpand', renderer: function (value, cellEl, rec) {
						if (Ext.isEmpty(rec.get('EvnXml_id'))) {
							return value;
						} else {
							return "<a href='#' onClick='getWnd(\"swEvnXmlViewWindow\").show({ EvnXml_id: " + rec.get('EvnXml_id') + " });'>" + value + "</a>";
						}
					}},
			],
			toolbar: true,
			totalProperty: 'totalCount',
			onBeforeLoadData: function () {
				this.getButtonSearch().disable();
			}.createDelegate(this),
			onLoadData: function () {
				this.getButtonSearch().enable();
				win.addGridFilter();
			}.createDelegate(this),
			onRowSelect: function (sm, index, record) {
			},
			onDblClick: function () {
				win.openEPHForm();
			},
		});

		this.EvnUsluga.ViewGridPanel.view = new Ext.grid.GridView(
				{
					getRowClass: function (row, index)
					{
						var cls = '';
						var red = row.get('XmlTemplate_HtmlTemplate');
						if (red.includes('#F00')) {
							cls = "x-grid-rowbackred"; //x-grid-rowred
						} else {
							cls = 'x-grid-panel';
						}
						return cls;
					}
				});

		// Журнал госпитализации

		this.grid = new sw.Promed.ViewFrame(
				{
					id: 'EJHW_HospitalizationsGrid',
					object: 'EvnPS',
					dataUrl: '/?c=SignalInfo&m=loadEvnPS',
					layout: 'fit',
					region: 'center',
					paging: true,
					root: 'data',
					totalProperty: 'totalCount',
					toolbar: true,
					autoLoadData: false,
					noSelectFirstRowOnFocus: true,// убираем выделение первой строки
					useEmptyRecord: false,
					onLoadData: function () {
						win.addGridFilter();
					},
					stringfields:
							[
								{name: 'EvnSection_id', type: 'int', header: 'ID', key: true},
								{name: 'EvnPS_id', type: 'int', hidden: true},
								{name: 'Person_id', type: 'int', hidden: true},
								{name: 'PersonEvn_id', type: 'int', hidden: true},
								{name: 'Server_id', type: 'int', hidden: true},
								{name: 'Lpu_id', type: 'int', hidden: true},
								{name: 'Lpu_did', type: 'int', hidden: true},
								{name: 'PrehospType_id', type: 'int', hidden: true},
								//{name: 'MedPersonal_aid', type: 'int', hidden: true},
								{name: 'MedPersonal_did', type: 'int', hidden: true},
								{name: 'MedPersonal_zdid', type: 'int', hidden: true},
								{name: 'DaysDiff', type: 'int', hidden: true},
								{name: 'EvnPS_IsPrehospAcceptRefuse', type: 'int', hidden: true},
								{name: 'Diag_Code', type: 'string', hidden: true},
								{name: 'Person_Fio', type: 'string', header: langs('ФИО'), width: 200},
								{name: 'Person_Birthday', type: 'date', header: langs('Дата рождения'), width: 90},
								{name: 'Person_Age', type: 'int', header: langs('Возраст'), width: 100},
								{name: 'EvnDirection_id', type: 'int', header: 'EvnDirection_id', hidden: true, hideable: false},
								{name: 'Lpu_Name', header: langs('МО госпитализации'), width: 120},
								{name: 'LpuSections_Name', header: langs('Отделение'), width: 250},
								{name: 'PrehospType_Name', header: langs('Тип госпитализации'), width: 140},
								{name: 'Diag_Name', header: langs('Основной диагноз'), width: 300},
								{name: 'EvnSection_PlanDisDT', type: 'date', header: langs('Планируемая дата выписки'), width: 170},
								{name: 'EvnPS_setDateTime', type: 'string', header: langs('Дата госпитализации'), width: 130},
								{name: 'EvnPS_disDateTime', type: 'string', header: langs('Дата выписки'), id: 'autoexpand'}
							],
					actions:
							[
								{name: 'action_add', hidden: true, disabled: true},
								{name: 'action_view', hidden: true, disabled: true},
								{name: 'action_edit', hidden: true, disabled: true},
								{name: 'action_delete', hidden: true, disabled: true},
								{name: 'action_refresh'},
								{name: 'action_print'}
							],
					onCellClick: function (grid, rowIdx, colIdx, e) {
						var record = grid.getStore().getAt(rowIdx);
						if (!record) {
							return false;
						}
						var fieldName = grid.getColumnModel().getDataIndex(colIdx);
						// Открываем просмотр направления по клику по иконке направления
						if (fieldName == 'EvnDirection_Num' && record.data.IsEvnDirection && record.data.EvnDirection_id)
						{
							getWnd('swEvnDirectionEditWindow').show({
								action: 'view',
								formParams: new Object(),
								EvnDirection_id: record.data.EvnDirection_id
							});
						}
						return true;
					},
					onRowSelect: function (sm, rowIdx, record) {
					},
					onDblClick: function () {
						win.openEPHForm();
					},
				});

		this.grid.ViewGridPanel.view = new Ext.grid.GridView({
			getRowClass: function (row, index) {
				var cls = '';
				//if ( row.get('Lpu_id') != row.get('Lpu_did') && parseInt(row.get('PrehospType_id')) == 2 ) {
				//#8935
				if (parseInt(row.get('PrehospType_id')) == 1) {
					cls = cls + 'x-grid-rowbold ';
				}

				if (cls.length == 0) {
					cls = 'x-grid-panel';
				}

				return cls;
			}
		});

		// Грид ЛВН поиск
		this.SearchGrid = new sw.Promed.ViewFrame({
			actions: [
				{name: 'action_add', hidden: true, disabled: true},
				{name: 'action_view', hidden: true, disabled: true},
				{name: 'action_delete', hidden: true, disabled: true},
				{name: 'action_edit', hidden: true, disabled: true},
				{name: 'action_print'}
			],
			autoLoadData: false,
			dataUrl: '/?c=Stick&m=loadEvnStickSearchGrid',
			//height: 203,
			id: this.id + 'SearchGrid',
			useEmptyRecord: false,
			onLoadData: function () {
				win.addGridFilter();
			},
			onDblClick: function () {
				win.openEPHForm();
			},
			onRowSelect: function (sm, index, record) {
			},
			paging: true,
			pageSize: 100,
			region: 'center',
			root: 'data',
			stringfields: [
				{name: 'EvnStickBase_id', type: 'int', header: 'ID', key: true},
				{name: 'accessType', type: 'string', hidden: true},
				{name: 'evnStickType', type: 'int', hidden: true},
				{name: 'parentClass', type: 'string', hidden: true},
				{name: 'Person_id', type: 'int', hidden: true},
				{name: 'Person_pid', type: 'int', hidden: true},
				{name: 'PersonEvn_id', type: 'int', hidden: true},
				{name: 'Server_id', type: 'int', hidden: true},
				{name: 'EvnStick_mid', type: 'int', hidden: true},
				{name: 'EvnStick_pid', type: 'int', hidden: true},
				{name: 'StickCause_Code', type: 'string', hidden: true},
				{name: 'StickOrder_Code', type: 'string', header: langs('Порядок выдачи'), hidden: true},
				{name: 'EvnStickClass_Name', type: 'string', header: langs('Документ/тип занятости'), width: 120},
				{name: 'EvnStickBase_Ser', type: 'string', header: langs('Серия'), width: 120},
				{name: 'EvnStickBase_Num', type: 'string', header: langs('Номер'), width: 120},
				{name: 'Lpu_Name', type: 'string', header: langs('МО выдачи ЛВН'), width: 200},
				{name: 'Person_Surname', type: 'string', header: langs('Фамилия'), width: 80},
				{name: 'Person_Firname', type: 'string', header: langs('Имя'), width: 80},
				{name: 'Person_Secname', type: 'string', header: langs('Отчество'), width: 80},
				{name: 'Person_Birthday', type: 'date', format: 'd.m.Y', header: langs('Дата рождения')},
				{name: 'Person_Age', type: 'int', header: langs('Возраст'), width: 100},
				{name: 'MedPersonalFirst_Fio', type: 'string', header: langs('Врач, выдавший ЛВН'), width: 200},
				{name: 'EvnStickWorkRelease_begDate', type: 'date', format: 'd.m.Y', header: langs('Освобождение от работы: с какого числа')},
				{name: 'EvnStickWorkRelease_endDate', type: 'date', format: 'd.m.Y', header: langs('Освобождение от работы: по какое число')},
				{name: 'EvnStickWorkRelease_DaysCount', type: 'int', header: langs('Число календарных дней освобождения от работы'), id: 'autoexpand'},
				{name: 'EvnStick_IsDelQueue', type: 'int', hidden: true}
			],
			totalProperty: 'totalCount'
		});

		this.SearchGrid.ViewGridPanel.view = new Ext.grid.GridView({
			getRowClass: function (row, index) {
				var cls = '';
				if (row.get('EvnStick_IsDelQueue') == 2) {
					cls = cls + 'x-grid-rowbackgray ';
				}
				if (cls.length == 0)
					cls = 'x-grid-panel';
				return cls;
			}
		});

		//Регистр льготников

		this.PrivGridPanel = new sw.Promed.ViewFrame({
			autoExpandColumn: 'autoexpand_privilege',
			autoExpandMin: 100,
			tbar: false,
			paging: true,
			border: false,
			autoLoadData: false,
			useEmptyRecord: false,
			onLoadData: function () {
				win.addGridFilter();
			},
			stringfields: [
				{name: 'Person_id', type: 'string', header: 'Person_id', hidden: true},
				{name: 'PersonPrivilege_id', type: 'string', header: 'PersonPrivilege_id', key: true, hidden: true},
				{name: 'Lpu_id', type: 'string', header: 'Lpu_id', hidden: true},
				{name: 'Lpu_did', type: 'string', header: langs('МО выдачи льготы'), hidden: true},
				{name: 'Lpu_Nick', type: 'string', header: 'Lpu_Nick', hidden: true},
				{name: 'PersonEvn_id', type: 'string', header: 'PersonEvn_id', hidden: true},
				{name: 'ReceptFinance_id', type: 'int', header: 'ReceptFinance_id', hidden: true},
				{name: 'ReceptFinance_Code', type: 'int', header: 'ReceptFinance_Code', hidden: true},
				{name: 'PrivilegeType_id', type: 'string', header: 'PrivilegeType_id', hidden: true},
				{name: 'Server_id', type: 'int', header: 'Server_id', hidden: true},
				{name: 'cntPC', type: 'int', header: 'cntPC', hidden: true},
				{name: 'Person_Surname', type: 'string', header: langs('Фамилия'), sort: true, width: 150},
				{name: 'Person_Firname', type: 'string', header: langs('Имя'), sort: true, width: 150},
				{name: 'Person_Secname', type: 'string', header: langs('Отчество'), sort: true, width: 150},
				{name: 'Person_Birthday', type: 'date', header: langs('Дата рождения'), sort: true, width: 150},
				{name: 'Person_Age', type: 'int', header: langs('Возраст'), width: 100},
				{name: 'PrivilegeType_Code', type: 'string', header: langs('Код льготы'), sort: true, width: 150},
				{name: 'PrivilegeType_Name', type: 'string', header: langs('Категория льготы'), sort: true, width: 150},
				{name: 'Privilege_begDate', type: 'date', header: langs('Дата начала'), sort: true, width: 150},
				{name: 'Privilege_endDate', type: 'date', header: langs('Дата окончания'), sort: true, width: 150},
				{name: 'Person_IsFedLgot', type: 'checkcolumn', header: langs('Фед. льг'), sort: true, width: 100, hidden: getRegionNick() == 'kz'},
				{name: 'Person_IsRegLgot', type: 'checkcolumn', header: langs('Рег. льг'), sort: true, width: 100, hidden: getRegionNick() == 'kz'},
				{name: 'Person_Is7Noz', type: 'checkcolumn', header: langs('7 ноз.'), sort: true, id: 'autoexpand', hidden: getRegionNick() == 'kz'},
				{name: 'PersonPrivilege_deletedInfo', type: 'string', header: langs('Удалена'), width: 350, hidden: true}
			],
			id: 'PrivSF_PersonPrivilegeGrid',
			region: 'center',
			onRowSelect: function (sm, index, record) {
			},
			onDblClick: function () {
				win.openEPHForm();
			},
			stripeRows: true,
			actions: [
				{name: 'action_add', hidden: true, disabled: true},
				{name: 'action_view', hidden: true, disabled: true},
				{name: 'action_delete', hidden: true, disabled: true},
				{name: 'action_edit', hidden: true, disabled: true},
			],
			root: 'data',
			paging: true,
			totalProperty: 'totalCount',
			dataUrl: C_SEARCH

		});
		this.PrivGridPanel.view = new Ext.grid.GridView({
			getRowClass: function (row, index)
			{
				var cls = '';

				if (row.get('Person_deadDT')) {
					cls = cls + 'x-grid-rowgray ';
				}
				return cls;
			},
			listeners:
					{
						rowupdated: function (view, first, record)
						{
							view.getRowClass(record);
						}
					}
		});


		//Диспансернизация 1 этап
		this.DispGridPanel1 = new sw.Promed.ViewFrame({
			dataUrl: '/?c=SignalInfo&m=loadListByDayDisp',
			pageSize: 100,
			paging: true,
			autoLoadData: false,
			useEmptyRecord: false,
			region: 'center',
			root: 'data',
			id: 'DispGridPanel1',
			onRowSelect: function (sm, index, record) {
			},
			onLoadData: function () {
				//win.addGridFilter();
			},
			stringfields: [
				{name: 'Person_id', type: 'string', header: 'Person_id', hidden: true},
				{name: 'Person_Surname', type: 'string', header: langs('Фамилия'), sort: true, width: 150},
				{name: 'Person_Firname', type: 'string', header: langs('Имя'), sort: true, width: 150},
				{name: 'Person_Secname', type: 'string', header: langs('Отчество'), sort: true, width: 150},
				{name: 'Person_BirthD', type: 'date', format: 'd.m.Y', header: langs('Дата рождения'), sort: true, width: 150},
				{name: 'Person_Age', type: 'int', header: langs('Возраст'), width: 100},
				{name: 'EvnPLDispDop13_setDate', type: 'date', header: langs('Дата начала'), sort: true, width: 150}
			],
			id: 'DispGridPanel1',
			stripeRows: true,
			actions: [
				{name: 'action_add', hidden: true, disabled: true},
				{name: 'action_view', hidden: true, disabled: true},
				{name: 'action_delete', hidden: true, disabled: true},
				{name: 'action_edit', hidden: true, disabled: true},
			],
			totalProperty: 'totalCount'

		});

		//Диспансернизация 1 этап
		this.DispGridPanel2 = new sw.Promed.ViewFrame({
			dataUrl: '/?c=SignalInfo&m=loadListByDayDisp2',
			pageSize: 100,
			paging: true,
			autoLoadData: false,
			useEmptyRecord: false,
			region: 'center',
			root: 'data',
			id: 'DispGridPanel2',
			onRowSelect: function (sm, index, record) {
			},
			onLoadData: function () {
				//win.addGridFilter();
			},
			stringfields: [
				{name: 'Person_id', type: 'string', header: 'Person_id', hidden: true},
				{name: 'Person_Surname', type: 'string', header: langs('Фамилия'), sort: true, width: 150},
				{name: 'Person_Firname', type: 'string', header: langs('Имя'), sort: true, width: 150},
				{name: 'Person_Secname', type: 'string', header: langs('Отчество'), sort: true, width: 150},
				{name: 'Person_BirthD', type: 'date', format: 'd.m.Y', header: langs('Дата рождения'), sort: true, width: 150},
				{name: 'Person_Age', type: 'int', header: langs('Возраст'), width: 100},
				{name: 'EvnPLDispDop13_setDate', type: 'date', header: langs('Дата начала'), sort: true, width: 150}
			],
			id: 'DispGridPanel2',
			stripeRows: true,
			actions: [
				{name: 'action_add', hidden: true, disabled: true},
				{name: 'action_view', hidden: true, disabled: true},
				{name: 'action_delete', hidden: true, disabled: true},
				{name: 'action_edit', hidden: true, disabled: true},
			],
			totalProperty: 'totalCount'

		});

		// Свидетельство о смерти

		this.MedSvidDeath = new sw.Promed.ViewFrame(
				{
					id: 'MedSvidDeath',
					region: 'center',
					height: 203,
					dataUrl: '/?c=MedSvid&m=loadMedSvidDeathListGrid',
					paging: true,
					root: 'data',
					totalProperty: 'totalCount',
					autoLoadData: false,
					useEmptyRecord: false,
					stringfields:
							[
								// {name: 'DeathSvid_id', type: 'int', header: 'ID', key: true},
								{name: 'DeathSvid_IsBad', type: 'int', hidden: true},
								{name: 'DeathSvid_IsActual', type: 'int', hidden: true},
								{name: 'DeathSvid_IsLose', type: 'int', hidden: true},
								{name: 'DeathSvidType_id', type: 'int', hidden: true},
								{name: 'Lpu_id', type: 'int', hidden: true},
								{name: 'LpuType_Code', type: 'string', hidden: true},
								{name: 'Person_rid', type: 'int', hidden: true},
								{name: 'DeathSvid_IsDuplicate', type: 'int', hidden: true},
								{name: 'Person_id', type: 'int', hidden: true, isparams: true},
								{name: 'Person_FIO', type: 'string', header: langs('ФИО'), width: 250/*, id: 'autoexpand'*/},
								{name: 'Person_Birthday', type: 'string', format: 'd.m.Y', header: langs('Дата рождения')},
								{name: 'Person_Age', type: 'string', header: langs('Возраст')},
								{name: 'DeathSvid_DeathDate', type: 'string', format: 'd.m.Y', header: langs('Дата смерти')},
								{name: 'Lpu_Nick', type: 'string', header: langs('МО выдачи'), id: 'autoexpand'}
							],
					actions:
							[
								{name: 'action_add', hidden: true, disabled: true},
								{name: 'action_view', hidden: true, disabled: true},
								{name: 'action_delete', hidden: true, disabled: true},
								{name: 'action_edit', hidden: true, disabled: true},
								{name: 'action_refresh'},
								{
									name: 'action_print'
								}
							],
					saveAllParams: true,
					saveAtOnce: true,
					onLoadData: function ()
					{
						win.addGridFilter();
					},
					onDblClick: function () {
						win.openEPHForm();
					},
				});

		this.MedSvidDeath.getGrid().view = new Ext.grid.GridView({
			getRowClass: function (row, index) {
				var cls = '';
				if (row.get('DeathSvid_IsActual') == 1)
					cls = cls + 'x-grid-rowgray ';
				if (cls.length == 0)
					cls = 'x-grid-panel';
				return cls;
			}
		});

		// Карты СМП

		this.CmpCallCardSearch = new sw.Promed.ViewFrame({
			actions: [
				{name: 'action_add', disabled: true, handler: Ext.emptyFn, hidden: true},
				{name: 'action_edit', disabled: true, handler: Ext.emptyFn, hidden: true},
				{name: 'action_view', disabled: true, handler: Ext.emptyFn, hidden: true},
				{name: 'action_delete', disabled: true, handler: Ext.emptyFn, hidden: true},
				{name: 'action_print'}
			],
			autoLoadData: false,
			useEmptyRecord: false,
			dataUrl: C_SEARCH,
			id: 'CmpCallCardSearch',
			onLoadData: function () {
				win.addGridFilter();
			},
			onRowSelect: function (sm, index, record) {
			},
			onDblClick: function () {
				win.openEPHForm();
			},
			pageSize: 100,
			paging: true,
			region: 'center',
			root: 'data',
			stringfields: [
				//{name: 'CmpCallCard_uid', type: 'int', key: true},// бывает null
				{name: 'CmpCallCard_id', type: 'int', key: true}, //hidden: true
				{name: 'accessType', type: 'string', hidden: true},
				{name: 'Person_id', type: 'int', hidden: true},
				{name: 'MedPersonal_id', type: 'int', hidden: true},
				{name: 'PersonEvn_id', type: 'int', hidden: true},
				{name: 'Server_id', type: 'int', hidden: true},
				{name: 'CmpCloseCard_id', type: 'int', hidden: true},
				{name: 'CmpCallCardInputType_id', type: 'int', hidden: true},
				{name: 'LpuBuilding_Name', type: 'string', header: langs('Подстанция'), width: 200},
				{name: 'CmpCallCard_prmDate', type: 'date', format: 'd.m.Y', header: langs('Дата вызова')},
				{name: 'CmpCallCard_prmTime', type: 'string', header: langs('Время вызова'), width: 100},
				{name: 'Person_Surname', type: 'string', header: langs('Фамилия'), width: 100},
				{name: 'Person_Firname', type: 'string', header: langs('Имя'), width: 100},
				{name: 'Person_Secname', type: 'string', header: langs('Отчество'), width: 100},
				{name: 'Person_Birthday', type: 'date', format: 'd.m.Y', header: langs('Дата рождения')},
				{name: 'Person_Age', type: 'int', header: langs('Возраст'), width: 100},
				{name: 'CmpSecondReason_Name', hidden: true},
				/*{name: 'CmpReason_Name', header: langs('Повод'), width: 200, renderer: function (value, cell, record) {
				 return record.get('CmpSecondReason_Name') || record.get('CmpReason_Name');
				 }},*/
				{name: 'EvnVizitPL_setDate', hidden: sw.Promed.MedStaffFactByUser.last.PostMed_id == "12" ? false : true, header: 'Дата предыдущего осмотра', type: 'date', width: 160},
				{name: 'PersonPregnancy_birthDate', hidden: sw.Promed.MedStaffFactByUser.last.PostMed_id == "12" ? false : true, header: 'Предполагаемый срок родов', type: 'date', width: 160},
				{name: 'MesLevel_Name', hidden: sw.Promed.MedStaffFactByUser.last.PostMed_id == "12" ? false : true, header: 'МО родоразрешения', width: 140},
				{name: 'CmpLpu_Name', type: 'string', header: langs('МО госпитализации'), width: 250},
				{name: 'CmpDiag_Name', type: 'string', header: langs('Диагноз СМП'), id: 'autoexpand'}
			],
			totalProperty: 'totalCount'/*,
			 onBeforeLoadData: function() {
			 this.getButtonSearch().disable();
			 }.createDelegate(this)*/

		});
		
		// Дистанционный мониторинг

		this.DistObservSearch = new sw.Promed.ViewFrame({
			actions: [
				{name: 'action_add', disabled: true, handler: Ext.emptyFn, hidden: true},
				{name: 'action_edit', disabled: true, handler: Ext.emptyFn, hidden: true},
				{name: 'action_view', disabled: true, handler: Ext.emptyFn, hidden: true},
				{name: 'action_delete', disabled: true, handler: Ext.emptyFn, hidden: true},
				{name: 'action_print'}
			],
			autoLoadData: false,
			useEmptyRecord: false,
			dataUrl: '/?c=SignalInfo&m=loadDistObservList',
			onLoadData: function () {
				win.addGridFilter();
			},
			onRowSelect: function (sm, index, record) {
			},
			onDblClick: function () {
				win.openEPHForm();
			},
			pageSize: 100,
			paging: true,
			region: 'center',
			root: 'data',
			stringfields: [
				{name: 'accessType', type: 'string', hidden: true},
				{name: 'Person_id', type: 'int', hidden: true},
				{name: 'Server_id', type: 'int', hidden: true},
				{name: 'Person_Surname', type: 'string', header: langs('Фамилия'), width: 100},
				{name: 'Person_Firname', type: 'string', header: langs('Имя'), width: 100},
				{name: 'Person_Secname', type: 'string', header: langs('Отчество'), width: 100},
				{name: 'Person_Birthday', type: 'date', format: 'd.m.Y', header: langs('Дата рождения')},
				{name: 'Person_Age', type: 'int', header: langs('Возраст'), width: 100},
				{name: 'HypertensionRiskGroup', type: 'string', header: langs('Группа риска'), width: 100},
				{name: 'AD', type: 'string', header: langs('Показатель АД'), width: 100},
				{name: 'Day_Count', type: 'int', header: langs('Количество дней превышения'), width: 100},
				{name: 'Address_Address', type: 'string', header: langs('Адрес'), width: 100},
				{name: 'Person_Phone', type: 'string', header: langs('Телефон'), width: 100}
			],
			totalProperty: 'totalCount'
		});
		
		// Список не явившихся
		this.PersonNoVisitGridPanel = new sw.Promed.ViewFrame({
			dataUrl: '/?c=SignalInfo&m=loadPersonNoVisit',
			pageSize: 100,
			paging: true,
			autoLoadData: false,
			useEmptyRecord: false,
			region: 'center',
			root: 'data',
			id: 'PersonNoVisitGridPanel',
			onRowSelect: function (sm, index, record) {
			},
			onDblClick: function () {
				win.openEPHForm();
			},
			onLoadData: function () {
				win.addGridFilter();
			},
			stringfields: [
				{name: 'Person_id', type: 'string', header: 'Person_id', hidden: true},
				{name: 'LpuED_Nick', header: langs('МО направления'), width: 150},
				{name: 'EvnDirection_setDate', renderer: Ext.util.Format.dateRenderer('d.m.Y'), header: langs('Дата направления'), width: 120},
				{name: 'TimeTableGraf_begTime', renderer: Ext.util.Format.dateRenderer('d.m.Y H:i'), header: langs('Дата записи'), width: 120},
				{name: 'Person_surName', type: 'string', header: langs('Фамилия'), width: 150},
				{name: 'Person_firName', type: 'string', header: langs('Имя'),  width: 150},
				{name: 'Person_secName', type: 'string', header: langs('Отчество'), width: 150},
				{name: 'Person_Birthday', renderer: Ext.util.Format.dateRenderer('d.m.Y'), header: langs('Дата рождения'), width: 120},
				{name: 'Person_Age', type: 'int', header: langs('Возраст'), width: 60},
				{name: 'Person_Phone', header: langs('Телефон'), width: 80},
				{name: 'Address_Address', header: langs('Адрес проживания'), width: 590},
				{name: 'Lpu_Nick', header: langs('МО прикрепления'), id: 'autoexpand'}
			],
			//stripeRows: true,
			actions: [
				{name: 'action_add', hidden: true, disabled: true},
				{name: 'action_view', hidden: true, disabled: true},
				{name: 'action_delete', hidden: true, disabled: true},
				{name: 'action_edit', hidden: true, disabled: true},
			],
			totalProperty: 'totalCount'
		});

		this.cdkGridPanel = new sw.Promed.ViewFrame({
			dataUrl: '/?c=SignalInfo&m=loadCdk',
			pageSize: 100,
			paging: true,
			autoLoadData: false,
			useEmptyRecord: false,
			region: 'center',
			root: 'data',
			id: win.id + '_cdkGridPanel',
			onRowSelect: function (sm, index, record) {
			},
			onDblClick: function () {
				win.openEPHForm();
			},
			onLoadData: function () {
				win.addGridFilter();
			},
			stringfields: [
				{ type: 'int', name: 'EvnDirection_id', hidden: true },
				{ type: 'int', name: 'Person_id', hidden: true },
				{ type: 'int', name: 'Server_id', hidden: true },
				{ type: 'int', name: 'RemoteConsultCause_id',  hidden: true },
				{ type: 'int', name: 'ConsultationForm_id', hidden: true },
				{ type: 'int', name: 'Diag_id', hidden: true },
				{ name: 'Person_SurName', header: langs('Фамилия'), width: 100 },
				{ name: 'Person_FirName', header: langs('Имя'),  width: 100 },
				{ name: 'Person_SecName', header: langs('Отчество'), width: 100 },
				{ type: 'date', name: 'Person_Birthday', header: langs('Дата рождения'), width: 88 },
				{ type: 'int', name: 'Person_Age', header: langs('Возраст'), width: 60 },
				{ type: 'datetime', name: 'EvnVizitPL_setDT', header: langs('Дата приёма'), width: 100 },
				{ type: 'date', name: 'EvnDirection_setDate', header: langs('Дата направления'), width: 105 },
				{ name: 'EvnDirection_Num', header: langs('Номер направления'), width: 100 },
				{ name: 'LpuDid_Nick', header: langs('МО направления'), id: 'autoexpand' },
				{ name: 'RemoteConsultCause_Name', header: langs('Цель консультации') },
				{ type: 'string', name: 'ConsultationForm_Name', header: langs('Форма оказания консультации') },
				{ type: 'int', name: 'EvnDirection_IsCito', header: 'Cito!', width: 50,
					renderer: function(value, cellEl, rec) {
						return value == 2 ? 'Да' : 'Нет';
					}
				},
				{ type: 'string', name: 'EDDiag_Name', header: langs('Диагноз направления'), width: 150 },
				{ type: 'datetime', name: 'EvnUslugaTelemed_setDT', header: langs('Дата выполнения') },
				{ type: 'string', name: 'EUTDiag_Name', header: langs('Диагноз'), width: 150 },
				{ type: 'string', name: 'UslugaTelemedResultType_Name', header: langs('Результат'), width: 200 }

			],
			actions: [
				{ name: 'action_add', hidden: true, disabled: true },
				{ name: 'action_view', hidden: true, disabled: true },
				{ name: 'action_delete', hidden: true, disabled: true },
				{ name: 'action_edit', hidden: true, disabled: true },
				{ name: 'action_refresh', handler: function () { win.doSearch(); }}
			],
			totalProperty: 'totalCount'
		});

		// Рекомендации по маршрутизации беременных женщин

		this.SignalInfoRecommRouterGrid = new sw.Promed.ViewFrame({
			actions: [
				{name: 'action_add', hidden: true},
				{name: 'action_edit', hidden: true},
				{name: 'action_view', handler: function () {
					win.openPersonPregnancyEditWindow(win.SignalInfoRecommRouterGrid)
				}},
				{name: 'action_refresh', handler: function () {
					win.doSearch(); 
				}},
				{name: 'action_delete', hidden: true}
			],
			dataUrl: '/?c=PersonPregnancy&m=loadListRecommRouter',
			pageSize: 100,
			paging: true,
			autoLoadData: false,
			useEmptyRecord: false,
			region: 'center',
			root: 'data',
			id: 'SignalInfoRecommRouterGrid',
			onRowSelect: function (sm, index, record) {
			},
			onLoadData: function () {
				//win.addGridFilter();
			},
			stringfields: [
				{name: 'PersonRegister_id', type: 'int', header: 'ID', key: true},
				{name: 'PersonPregnancy_id', hidden: true, type: 'int'},
				{name: 'PersonRegisterOutCause_id', hidden: true, type: 'int'},
				{name: 'PersonRegisterOutCause_Code', hidden: true, type: 'string'},
				{name: 'PersonRegisterOutCause_SysNick', hidden: true, type: 'string'},
				{name: 'Person_id', hidden: true, type: 'int'},
				{name: 'ScreenData', hidden: true, type: 'string'},
				{name: 'PersonRegister_Code', header: 'Номер индивидуальной карты беременной', type: 'string', width: 120},
				{name: 'Person_Fio', header: 'ФИО', type: 'string', width: 160},
				{name: 'Person_BirthDay', header: 'Д/р', type: 'date', width: 90},
				{name: 'Person_Age', type: 'int', header: langs('Возраст'), width: 90},
				{name: 'PersonPregnancy_Period', header: 'Срок', type: 'int', width: 50},
				{name: 'Trimester', header: 'Триместр', type: 'string', width: 100},
				{name: 'RiskType_AName', header: 'Степень риска с учетом ключ. факт.', width: 200},
				{name: 'EvnVizitPL_setDate', header: 'Дата предыдущего осмотра', type: 'date', width: 160},
				{name: 'PersonPregnancy_birthDate', header: 'Предполагаемый срок родов', type: 'date', width: 160},
				{name: 'MesLevel_Name', header: 'МО родоразрешения', width: 140},
				{name: 'NickHospital', header: 'МО госпитализации', id: 'autoexpand'}
			]
		});

		this.RecommRouterRegionGridPanel = new sw.Promed.ViewFrame({
			actions: [
				{name: 'action_add', hidden: true},
				{name: 'action_view', handler: function () {
					win.openRouterViewWindow('RecommRouterRegionGridPanel');
					}},
				{name: 'action_edit', hidden: true},
				{name: 'action_delete', hidden: true}
			],
			dataUrl: '/?c=SignalInfo&m=loadTrimesterListMO',
			autoLoadData: false,
			useEmptyRecord: false,
			focusOnFirstLoad: false,
			noFocusOnLoadOneTime: true,
			region: 'center',
			root: 'data',
			id: 'RecommRouterRegionGridPanel',
			autoExpandColumn: 'autoexpand',
			//autoExpandMax: 2000,
			autoExpandMin: 140,
			enableColumnMove: false,
			stripeRows: true,
			stringfields: [
				{name: 'Lpu_iid', type: 'int', header: 'ID', key: true},
				{name: 'Lpu_Nick', header: 'МО учета', type: 'string', width: 200},
				{name: 'Trimester1', header: '1 триместр', type: 'string', width: 160},
				{name: 'Trimester2', header: '2 триместр', type: 'string', width: 160},
				{name: 'Trimester3', header: '3 триместр', type: 'string', id: 'autoexpand'}
			],
			selectionModel: 'cell',
			onCellDblClick: function (grid, rowIdx, colIdx, event) {
				win.openRouterViewWindow('RecommRouterRegionGridPanel');
			}.createDelegate(this),
			keys: [{
					key: Ext.EventObject.ENTER,
					fn: function (e) {
						//var wnd = Ext.getCmp('SignalInfoWindow');
						win.openRouterViewWindow('RecommRouterRegionGridPanel');
					},
					stopEvent: true
				}]
		});

		this.RecommRouterRegionGridPanel.ViewGridModel.on('selectionchange', function (obj) {
		});

		// Беременные "Не включенные в регистр"
		this.NotIncludeGridPanel = new sw.Promed.ViewFrame({
			actions: [
				{ name: 'action_add', hidden: true},
				{ name: 'action_edit', hidden: true},
				{ name: 'action_view', hidden: true},
				{ name: 'action_delete', hidden: true}
			],
			dataUrl: '/?c=SignalInfo&m=loadPregnancyNotIncludeList',
			autoLoadData: false,
			region: 'center',
			paging: true,
			root: 'data',
			id: 'NotIncludeGridPanel',
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 140,
			enableColumnMove: false,
			stringfields: [
				{name: 'Evn_id', type: 'int', header: 'ID', key: true},
				{name: 'Person_id', hidden: true, type: 'int'},
				{name: 'EvnClass_SysNick', hidden: true, type: 'string'},
				{name: 'PersonQuarantine_IsOn', hidden: true, type: 'int'},
				{name: 'Evn_setDate', header: 'Дата начала', type: 'date', width: 80},
				{name: 'Evn_disDate', header: 'Дата окончания', type: 'date', width: 80},
				{name: 'EvnType', header: 'Тип случая', type: 'string', width: 100},
				{name: 'Lpu_Nick', header: 'МО', type: 'string', width: 220},
				{name: 'Evn_NumCard', header: 'Номер карты', type: 'string', width: 100},
				{name: 'Person_Fio', header: 'ФИО', type: 'string', width: 200},
				{name: 'Person_BirthDay', header: 'Д/р', type: 'date', width: 100},
				{name: 'Diag_FullName', header: 'Диагноз', type: 'string', id: 'autoexpand'},
				{name: 'EvnResult', header: 'Результат', type: 'string', width: 120},
				{name: 'LpuAttach_Nick', header: 'МО прикрепления', type: 'string', width: 220},
				{name: 'Person_PAddress', header: 'Адрес проживания', type: 'string', width: 200},
				{name: 'MedPersonal', header: 'Врач', type: 'string', width: 200}
			],
			onDblClick: function () {
				win.openEPHForm();
			}
		});

		//По региону для "Не проведена консультация"
		this.PregnancyRouteNotConsultationRegion = new sw.Promed.ViewFrame({
			actions: [
				{name: 'action_add', hidden: true},
				{name: 'action_view', handler: function () {
					win.openRouterViewWindow('PregnancyRouteNotConsultationRegion');
				}},
				{name: 'action_refresh', handler: function () {
					win.doSearch(); 
				}},
				{name: 'action_edit', hidden: true},
				{name: 'action_delete', hidden: true}
			],
			dataUrl: '/?c=SignalInfo&m=loadTrimesterListMO',
			autoLoadData: false,
			useEmptyRecord: false,
			focusOnFirstLoad: false,
			root: 'data',
			noFocusOnLoadOneTime: true,
			region: 'center',
			id: 'PregnancyRouteNotConsultationRegion',
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 140,
			enableColumnMove: false,
			stripeRows: true,
			stringfields: [
				{name: 'Lpu_iid', type: 'int', header: 'ID', key: true},
				{name: 'Lpu_Nick', header: 'МО учета', type: 'string', width: 200},
				{name: 'Trimester1', header: '1 триместр', type: 'string', width: 160},
				{name: 'Trimester2', header: '2 триместр', type: 'string', width: 160},
				{name: 'Trimester3', header: '3 триместр', type: 'string', id: 'autoexpand'}
			],
			selectionModel: 'cell',
			onCellDblClick: function (grid, rowIdx, colIdx, event) {
				win.openRouterViewWindow('PregnancyRouteNotConsultationRegion');
			}.createDelegate(this),
			keys: [{
				key: Ext.EventObject.ENTER,
				fn: function (e) {
					//var wnd = Ext.getCmp('SignalInfoWindow');
					win.openRouterViewWindow('PregnancyRouteNotConsultationRegion');
				},
				stopEvent: true
			}]
		});

		this.PregnancyRouteNotConsultationRegion.ViewGridModel.on('selectionchange', function (obj) {
		});

		//По региону для "Находятся на госпитализации"
		this.PregnancyRouteHospitalRegion = new sw.Promed.ViewFrame({
			actions: [
				{name: 'action_add', hidden: true},
				{name: 'action_view', handler: function () {
					win.openRouterViewWindow('PregnancyRouteHospitalRegion');
				}},
				{name: 'action_refresh', handler: function () {
					win.doSearch(); 
				}},
				{name: 'action_edit', hidden: true},
				{name: 'action_delete', hidden: true}
			],
			dataUrl: '/?c=SignalInfo&m=loadTrimesterListMO',
			autoLoadData: false,
			useEmptyRecord: false,
			focusOnFirstLoad: false,
			noFocusOnLoadOneTime: true,
			region: 'center',
			root: 'data',
			id: 'PregnancyRouteHospitalRegion',
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 140,
			enableColumnMove: false,
			stripeRows: true,
			stringfields: [
				{name: 'Lpu_iid', type: 'int', header: 'ID', key: true},
				{name: 'Lpu_Nick', header: 'МО учета', type: 'string', width: 200},
				{name: 'Trimester1', header: '1 триместр', type: 'string', width: 160},
				{name: 'Trimester2', header: '2 триместр', type: 'string', width: 160},
				{name: 'Trimester3', header: '3 триместр', type: 'string', id: 'autoexpand'}
			],
			selectionModel: 'cell',
			onCellDblClick: function (grid, rowIdx, colIdx, event) {
				win.openRouterViewWindow('PregnancyRouteHospitalRegion');
			}.createDelegate(this),
			keys: [{
				key: Ext.EventObject.ENTER,
				fn: function (e) {
					//var wnd = Ext.getCmp('SignalInfoWindow');
					win.openRouterViewWindow('PregnancyRouteHospitalRegion');
				},
				stopEvent: true
			}]
		});

		this.PregnancyRouteHospitalRegion.ViewGridModel.on('selectionchange', function (obj) {
		});


		this.PersonPregnancyFiltersPanel = new Ext.form.FormPanel({
			frame: true,
			autoHeight: true,
			id: 'PersonPregnancyFiltersPanel',
			border: true, //false
			region: 'north',
			autoLoadData: false,
			bodyStyle: 'padding: 5px',
			labelAlign: 'left',
			labelWidth: 50,
			items: [new Ext.ux.Andrie.Select({
					allowBlank: true,
					multiSelect: true,
					mode: 'local',
					anchor: '20%',
					fieldLabel: 'Врач',
					store: new Ext.data.Store({
						autoLoad: false,
						reader: new Ext.data.JsonReader(
								{
									id: 'MedPersonal_id'
								},
								[
									{name: 'MedPersonal_id', mapping: 'MedPersonal_id'},
									{name: 'MedPersonal_Code', mapping: 'MedPersonal_Code'},
									{name: 'MedPersonal_Fio', mapping: 'MedPersonal_Fio'},
									{name: 'WorkData_begDate', mapping: 'WorkData_endDate'},
									{name: 'WorkData_endDate', mapping: 'WorkData_endDate'}
								]),

						url: C_MP_LOADLIST
					}),
					xtype: 'swmedpersonalcombo',
					displayField: 'MedPersonal_Fio',
					valueField: 'MedPersonal_id',
					name: 'MedPersonal_id',
					id: 'S_MedPersonal_id',
					tpl: new Ext.XTemplate(
							'<tpl for="."><div class="x-combo-list-item">',
							'<table style="border: 0;"><td style="width: 70px"><font color="red">{MedPersonal_Code}</font></td><td><b>{MedPersonal_Fio}</b>&nbsp;</td></tr></table>',
							'</div></tpl>'
							),
				})
			],
			keys: [{
					fn: function () {
						win.doSearch();
					},
					key: Ext.EventObject.ENTER,
					stopEvent: true
				}]
		}
		);

		//фильтр по врачам для "Не проведена консультация" по МО
		this.filterPregnancyRouteNoConsultation = new Ext.form.FormPanel({
			frame: true,
			autoHeight: true,
			id: 'filterPregnancyRouteNoConsultation',
			border: true, //false
			region: 'north',
			autoLoadData: false,
			bodyStyle: 'padding: 5px',
			labelAlign: 'left',
			labelWidth: 50,
			items: [new Ext.ux.Andrie.Select({
				allowBlank: true,
				multiSelect: true,
				mode: 'local',
				anchor: '20%',
				fieldLabel: 'Врач',
				store: new Ext.data.Store({
					autoLoad: false,
					reader: new Ext.data.JsonReader(
						{
							id: 'MedPersonal_id'
						},
						[
							{name: 'MedPersonal_id', mapping: 'MedPersonal_id'},
							{name: 'MedPersonal_Code', mapping: 'MedPersonal_Code'},
							{name: 'MedPersonal_Fio', mapping: 'MedPersonal_Fio'},
							{name: 'WorkData_begDate', mapping: 'WorkData_endDate'},
							{name: 'WorkData_endDate', mapping: 'WorkData_endDate'}
						]),

					url: C_MP_LOADLIST
				}),
				xtype: 'swmedpersonalcombo',
				displayField: 'MedPersonal_Fio',
				valueField: 'MedPersonal_id',
				name: 'NoConsultationMedPersonal_id',
				id: 'NoConsultationMedPersonal_id',
				tpl: new Ext.XTemplate(
					'<tpl for="."><div class="x-combo-list-item">',
					'<table style="border: 0;"><td style="width: 70px"><font color="red">{MedPersonal_Code}</font></td><td><b>{MedPersonal_Fio}</b>&nbsp;</td></tr></table>',
					'</div></tpl>'
				),
			})
			],
			keys: [{
				fn: function () {
					win.doSearch();
				},
				key: Ext.EventObject.ENTER,
				stopEvent: true
			}]
		}
		);

		//фильтр по врачам для "Находятся на госпитализации" по МО
		this.filterPregnancyRouteHospital = new Ext.form.FormPanel({
			frame: true,
			autoHeight: true,
			id: 'filterPregnancyRouteHospital',
			border: true, //false
			region: 'north',
			autoLoadData: false,
			bodyStyle: 'padding: 5px',
			labelAlign: 'left',
			labelWidth: 50,
			items: [new Ext.ux.Andrie.Select({
				allowBlank: true,
				multiSelect: true,
				mode: 'local',
				anchor: '20%',
				fieldLabel: 'Врач',
				store: new Ext.data.Store({
					autoLoad: false,
					reader: new Ext.data.JsonReader(
						{
							id: 'MedPersonal_id'
						},
						[
							{name: 'MedPersonal_id', mapping: 'MedPersonal_id'},
							{name: 'MedPersonal_Code', mapping: 'MedPersonal_Code'},
							{name: 'MedPersonal_Fio', mapping: 'MedPersonal_Fio'},
							{name: 'WorkData_begDate', mapping: 'WorkData_endDate'},
							{name: 'WorkData_endDate', mapping: 'WorkData_endDate'}
						]),

					url: C_MP_LOADLIST
				}),
				xtype: 'swmedpersonalcombo',
				displayField: 'MedPersonal_Fio',
				valueField: 'MedPersonal_id',
				name: 'HospitalMedPersonal_id',
				id: 'HospitalMedPersonal_id',
				tpl: new Ext.XTemplate(
					'<tpl for="."><div class="x-combo-list-item">',
					'<table style="border: 0;"><td style="width: 70px"><font color="red">{MedPersonal_Code}</font></td><td><b>{MedPersonal_Fio}</b>&nbsp;</td></tr></table>',
					'</div></tpl>'
				),
			})
			],
			keys: [{
				fn: function () {
					win.doSearch();
				},
				key: Ext.EventObject.ENTER,
				stopEvent: true
			}]
		}
		);

		// Не проведена консультация
		this.PregnancyRouteNoConsultation = new sw.Promed.ViewFrame({
			dataUrl: '/?c=SignalInfo&m=loadPregnancyRouteNotConsultation',
			pageSize: 100,
			paging: true,
			autoLoadData: false,
			useEmptyRecord: true,
			region: 'center',
			root: 'data',
			id: 'PregnancyRouteNoConsultation',
			onLoadData: function () {
				win.addGridFilter();
			},
			stringfields: [
				{name: 'PersonRegister_id', type: 'int', header: 'PersonRegister_id', hidden: true},
				{name: 'PersonRegister_Code', header: langs('Номер индивидуальной карты беременной'), width: 150},
				{name: 'Person_id', type: 'string', header: 'Person_id', hidden: true},
				{name: 'Lpu_iid', type: 'string', header: langs('ЛПУ учета'), width: 120, hidden: true},
				{name: 'MedPersonal_iid', type: 'string', header: langs('Врач учета'), width: 150, hidden: true},
				{name: 'PregnancyRouteNoConsultation', header: langs('Скрининг'), width: 150, hidden: true},
				{name: 'PersonPregnancy_id', hidden: true},
				{name: 'Person_Fio', header: langs('ФИО'), width: 200},
				{name: 'Person_BirthDay', type: 'date', header: langs('Дата рождения'), width: 90},
				{name: 'PersonPregnancy_Period', type: 'int', header: langs('Срок'), width: 60},
				{name: 'Trimester', header: langs('Триместр'), width: 150},
				{name: 'RiskType_AName', header: langs('Степень риска с учетом ключ. факт.'), width: 150},
				{name: 'lstfactorrisk', header: langs('Наличие ключевых факторов риска'), width: 400},
				{name: 'PersonPregnancy_ObRisk', type: 'int', header: langs('Баллы перинатального риска'), width: 60},
				{name: 'EvnVizitPL_setDate', header: 'Дата предыдущего осмотра', type: 'date', width: 160},
				{name: 'PersonPregnancy_birthDate', header: 'Предполагаемый срок родов', type: 'date', width: 160},
				{name: 'MesLevel_Name', header: 'МО родоразрешения', width: 140},
				{name: 'NickHospital', header: langs('МО госпитализации'), width: 150}
			],
			actions: [
				{name: 'action_add', hidden: true, disabled: true},
				{
					name: 'action_view', handler: function () {
						win.openPersonPregnancyEditWindow(win.PregnancyRouteNoConsultation)
					}
				},
				{name: 'action_delete', hidden: true, disabled: true},
				{name: 'action_edit', hidden: true}
			],
			totalProperty: 'totalCount',
			onDblClick: function () {
				win.openEPHForm();
			},
		});

		//Находятся на госпитализации
		this.PregnancyRouteHospital = new sw.Promed.ViewFrame({
			dataUrl: '/?c=SignalInfo&m=loadPregnancyRouteHospital',
			pageSize: 100,
			paging: true,
			autoLoadData: false,
			useEmptyRecord: true,
			region: 'center',
			root: 'data',
			id: 'PregnancyRouteHospital',
			onLoadData: function () {
				win.addGridFilter();
			},
			stringfields: [
				{name: 'PersonRegister_id', type: 'int', header: 'PersonRegister_id', hidden: true},
				{name: 'Person_id', type: 'string', header: 'Person_id', hidden: true},
				{name: 'PersonRegister_Code', header: langs('Номер индивидуальной карты беременной'), width: 150},
				{name: 'Lpu_iid', type: 'string', header: langs('ЛПУ учета'), width: 120, hidden: true},
				{name: 'MedPersonal_iid', type: 'string', header: langs('Врач учета'), width: 150, hidden: true},
				{name: 'PersonPregnancy_id', hidden: true},
				{name: 'Person_Fio', header: langs('ФИО'), width: 150},
				{name: 'Person_BirthDay', type: 'date', header: langs('Дата рождения'), width: 90},
				{name: 'PersonPregnancy_Period', type: 'int', header: langs('Срок'), width: 60},
				{name: 'Trimester', header: langs('Триместр'), width: 150},
				{name: 'RiskType_AName', header: langs('Степень риска с учетом ключ. факт.'), width: 150},
				{name: 'LpuUnitType_Name', header: langs('Тип стационара'), width: 150},
				{name: 'NickHospital', header: langs('МО госпитализации'), width: 150},
				{name: 'EvnPS_setDate', type: 'date', header: langs('Дата госпитализации'), width: 130},
				{name: 'ProfilHospital', header: langs('Профиль'), width: 150},
				{name: 'EvnVizitPL_setDate', header: 'Дата предыдущего осмотра', type: 'date', width: 160},
				{name: 'PersonPregnancy_birthDate', header: 'Предполагаемый срок родов', type: 'date', width: 160},
				{name: 'MesLevel_Name', header: 'МО родоразрешения', width: 140}
			],
			actions: [
				{name: 'action_add', hidden: true, disabled: true},
				{
					name: 'action_view', handler: function () {
						win.openPersonPregnancyEditWindow(win.PregnancyRouteHospital)
					}
				},
				{name: 'action_delete', hidden: true, disabled: true},
				{name: 'action_edit', hidden: true}
			],
			totalProperty: 'totalCount',
			onDblClick: function () {
				win.openEPHForm();
			},
		});

		//Выписанные из стационара
		this.PregnancyRouteDisHospital = new sw.Promed.ViewFrame({
			dataUrl: '/?c=SignalInfo&m=loadPregnancyRouteHospital',
			pageSize: 100,
			paging: true,
			autoLoadData: false,
			useEmptyRecord: true,
			region: 'center',
			root: 'data',
			id: 'PregnancyRouteDisHospital',
			onLoadData: function () {
				win.addGridFilter();
			},
			stringfields: [
				{name: 'PersonPregnancy_id', type: 'int', hidden: true},
				{name: 'PersonRegister_id', type: 'int', header: 'PersonRegister_id', hidden: true},
				{name: 'Person_id', type: 'string', header: 'Person_id', hidden: true},
				{name: 'PersonRegister_Code', header: langs('Номер индивидуальной карты беременной'), width: 150},
				{name: 'Lpu_iid', type: 'string', header: langs('ЛПУ учета'), width: 120, hidden: true},
				{name: 'MedPersonal_iid', type: 'string', header: langs('Врач учета'), width: 150, hidden: true},
				{name: 'Person_Fio', header: langs('ФИО'), width: 150},
				{name: 'Person_BirthDay', type: 'date', header: langs('Дата рождения'), width: 90},
				{name: 'PersonPregnancy_Period', type: 'int', header: langs('Срок'), width: 60},
				{name: 'Trimester', header: langs('Триместр'), width: 150},
				{name: 'RiskType_AName', header: langs('Степень риска с учетом ключ. факт.'), width: 150},
				{name: 'NickHospital', header: langs('МО госпитализации'), width: 150},
				{name: 'diag_FullName', header: langs('Основной диагноз'), width: 200},
				{name: 'EvnPS_setDate', type: 'date', header: langs('Дата госпитализации'), width: 90},
				{name: 'ProfilHospital', header: langs('Профиль'), width: 150},
				{name: 'EvnPS_disDate', type: 'date', header: langs('Дата выписки'), width: 90},
				{name: 'EvnVizitPL_setDate', header: 'Дата предыдущего осмотра', type: 'date', width: 160},
				{name: 'PersonPregnancy_birthDate', header: 'Предполагаемый срок родов', type: 'date', width: 160},
				{name: 'MesLevel_Name', header: 'МО родоразрешения', width: 140}
			],
			actions: [
				{name: 'action_add', hidden: true, disabled: true},
				{
					name: 'action_view', handler: function () {
						win.openPersonPregnancyEditWindow(win.PregnancyRouteDisHospital)
					}
				},
				{name: 'action_delete', hidden: true, disabled: true},
				{name: 'action_edit', hidden: true}
			],
			totalProperty: 'totalCount',
			onDblClick: function () {
				win.openEPHForm();
			},
		});

		//Изменилась группа риска
		this.PregnancyRouteRiskСhange = new sw.Promed.ViewFrame({
			dataUrl: '/?c=SignalInfo&m=loadPregnancyRouteHospital',
			pageSize: 100,
			paging: true,
			autoLoadData: false,
			useEmptyRecord: true,
			region: 'center',
			root: 'data',
			id: 'PregnancyRouteRiskСhange',
			onLoadData: function () {
				win.addGridFilter();
			},
			stringfields: [
				{name: 'PersonPregnancy_id', type: 'int', hidden: true},
				{name: 'PersonRegister_id', type: 'int', header: 'PersonRegister_id', hidden: true},
				{name: 'Person_id', type: 'string', header: 'Person_id', hidden: true},
				{name: 'PersonRegister_Code', header: langs('Номер индивидуальной карты беременной'), width: 150},
				{name: 'Lpu_iid', type: 'string', header: langs('ЛПУ учета'), width: 120, hidden: true},
				{name: 'MedPersonal_iid', type: 'string', header: langs('Врач учета'), width: 150, hidden: true},
				{name: 'Person_Fio', header: langs('ФИО'), width: 150},
				{name: 'Person_BirthDay', type: 'date', header: langs('Дата рождения'), width: 90},
				{name: 'PersonPregnancy_Period', type: 'int', header: langs('Срок'), width: 60},
				{name: 'Trimester', header: langs('Триместр'), width: 150},
				{name: 'lstfactorrisk', header: langs('Наличие ключевых факторов риска'), width: 400},
				{name: 'RiskType_AName', header: langs('Степень риска с учетом ключ. факт.'), width: 150},
				{name: 'EvnVizitPL_setDate', header: 'Дата предыдущего осмотра', type: 'date', width: 160},
				{name: 'PersonPregnancy_birthDate', header: 'Предполагаемый срок родов', type: 'date', width: 160},
				{name: 'MesLevel_Name', header: 'МО родоразрешения', width: 140},
				{name: 'NickHospital', header: langs('МО госпитализации'), width: 150}

			],
			actions: [
				{name: 'action_add', hidden: true, disabled: true},
				{
					name: 'action_view', handler: function () {
						win.openPersonPregnancyEditWindow(win.PregnancyRouteRiskСhange)
					}
				},
				{name: 'action_delete', hidden: true, disabled: true},
				{name: 'action_edit', hidden: true}
			],
			totalProperty: 'totalCount',
			onDblClick: function () {
				win.openEPHForm();
			},
		});

		//Регистр БСК
		this.RegisterBSK = new sw.Promed.ViewFrame({
			dataUrl: '/?c=SignalInfo&m=loadListRegistBSK',
			pageSize: 100,
			paging: true,
			autoLoadData: false,
			useEmptyRecord: true,
			region: 'center',
			root: 'data',
			id: 'RegisterBSK',
			onLoadData: function () {
				win.addGridFilter();
			},
			stringfields: [
				{name: 'PersonPregnancy_id', type: 'int', hidden: true},
				{name: 'BSKRegistry_id', string: 'string', hidden: true},
				{name: 'MorbusType_id', string: 'string', hidden: true},
				{name: 'Person_id', type: 'string', header: 'Person_id', hidden: true},
				{name: 'Person_Fio', header: langs('ФИО'), width: 250},
				{name: 'Person_BirthDay', type: 'date', header: langs('Дата рождения'), width: 120},
				{name: 'Person_Age', type: 'int', header: langs('Возраст'), width: 100},
				{name: 'MorbusType_Name', header: langs('Предмет наблюдения'), width: 300, renderer: function (value, cellEl, rec) {
					var BSKObject_id = '';
					switch (rec.get('MorbusType_id')) {
						case "84": BSKObject_id = 2; break;
						case "50": BSKObject_id = 12; break;
						case "89": BSKObject_id = 10; break;
						case "88": BSKObject_id = 9; break;
						default: return '';
					}
					return "<a href='#' onClick='getWnd(\"personBskRegistryDataWindow\").show({\"Person_id\": " + rec.get('Person_id') + ", \"BSKRegistry_id\": " + rec.get('BSKRegistry_id') + ", \"BSKObject_id\": " + BSKObject_id + " });'>" + value + "</a>";
					//getWnd('personBskRegistryWindow').show(params);
				}},
				{name: 'BSKRegistry_riskGroup', header: langs('Фактор риска'), width: 150, renderer: function (value, cellEl, rec) {
					if (Ext.isEmpty(rec.get('BSKRegistry_riskGroup'))) {
						return value;
					}
					if(rec.get('MorbusType_id') == 84 || rec.get('MorbusType_id') == 89) {
						switch(rec.get('BSKRegistry_riskGroup')){
							case '1': value = "I"; break;
							case '2': value = "II"; break;
							case '3': value = "III"; break;
							default: value;
						};
						return value + ' группа риска';
					} else if (rec.get('MorbusType_id') == 88) {
						return value + ' функциональный класс';
					}

				}},
				{name: 'BSKRegistry_setDateNext', type: 'date', header: langs('Дата планового осмотра'), width: 160}
			],
			actions: [
				{name: 'action_add', hidden: true, disabled: true},
				{name: 'action_view', hidden: true, disabled: true},
				{name: 'action_refresh', handler: function () {
					win.doSearch();
				}},
				{name: 'action_delete', hidden: true, disabled: true},
				{name: 'action_edit', hidden: true}
			],
			totalProperty: 'totalCount',
			onDblClick: function () {
				win.openEPHForm();
			},
		});

		//Карты КВИ
		this.CVI = new sw.Promed.ViewFrame({
			dataUrl: '/?c=SignalInfo&m=loadCVI',
			pageSize: 100,
			paging: true,
			autoLoadData: false,
			useEmptyRecord: true,
			region: 'center',
			root: 'data',
			id: 'SignalInfoCVI',
			onLoadData: function () {
				win.addGridFilter();
			},
			stringfields: [
				{name: 'PersonQuarantine_id', header: 'ID', type: 'int', header: 'PersonQuarantine_id', hidden: true},
				{name: 'Person_id', type: 'int', header: 'Person_id', hidden: true},
				{name: 'Server_id', type: 'int', header: 'Person_id', hidden: true},
				{name: 'psign', type: 'int', header: 'psign', hidden: true},
				{name: 'QuarantineDays', type: 'int', header: langs('День карантина'), width: 100},
				{name: 'Person_Fio', header: langs('ФИО'), width: 250},
				{name: 'Person_BirthDay', type: 'date', header: langs('Дата рождения'), width: 110},
				{name: 'Person_Age', type: 'int', header: langs('Возраст'), width: 80},
				{name: 'Person_Phone', header: langs('Телефон'), width: 90},
				{name: 'PersonQuarantine_begDate', type: 'date', header: langs('Дата открытия КК'), width: 120},
				{name: 'PersonQuarantineOpenReason_Name', header: langs('Причина открытия КК'), width: 140},
				{name: 'PersonQuarantine_approveDate', type: 'date', header: langs('Дата выявления заболевания'), width: 140},
				{name: 'NickHospital', header: langs('МО госпитализации'), width: 150}
			],
			actions: [
					{name: 'action_add', handler: function() { 
						getWnd('swPersonSearchWindow').show({
							onSelect: function (person_data) {
								if (person_data.Person_IsDead == "true"){
									sw.swMsg.show(
									{
										buttons: Ext.Msg.OK,
										fn: function() 
										{
										},
										icon: Ext.Msg.WARNING,
										msg: 'Невозможно выбрать умершего человека',
										title: 'Добавление пациента'		
									});	
								} else {
									getWnd('swPersonQuarantineEditWindow').show({
										action: "add",
										Person_id: person_data.Person_id,
										Server_id: person_data.Server_id,
										MedStaffFact_id: this.userMedStaffFact.MedStaffFact_id,
										callback : function() {
											this.doSearch("tab_CVI");
										}.createDelegate(this)
									});
									getWnd('swPersonSearchWindow').hide();
								}
							}.createDelegate(this)
						});
					}.createDelegate(this) },
					{name: 'action_view', text: 'Открыть контрольную карту', handler: function () {
						var record = this.CVI.getGrid().getSelectionModel().getSelected();
						if (!record || Ext.isEmpty(record.get('PersonQuarantine_id'))) {
							return false;
						}
						getWnd('swPersonQuarantineEditWindow').show({
							action: "edit",
							Person_id: record.get('Person_id'),
							Server_id: record.get('Server_id'),
							PersonQuarantine_id: record.get('PersonQuarantine_id'),
							MedStaffFact_id: this.userMedStaffFact.MedStaffFact_id,
							callback : function() {
								this.doSearch("tab_CVI");
							}.createDelegate(this)
						});
					}.createDelegate(this) },
					{name: 'action_edit', hidden: true, disabled: true},
					{name: 'action_refresh', handler: function () {
						this.doSearch();
					}.createDelegate(this)},
					{name: 'action_delete', hidden: true, disabled: true}
			],
			totalProperty: 'totalCount',
			onDblClick: function () {
				var record = this.CVI.getGrid().getSelectionModel().getSelected();
				if (!record || Ext.isEmpty(record.get('PersonQuarantine_id'))) {
					return false;
				}
				getWnd('swPersonQuarantineEditWindow').show({
					action: "edit",
					Person_id: record.get('Person_id'),
					Server_id: record.get('Server_id'),
					PersonQuarantine_id: record.get('PersonQuarantine_id'),
					MedStaffFact_id: this.userMedStaffFact.MedStaffFact_id,
					callback : function() {
						this.doSearch("tab_CVI");
					}.createDelegate(this)
				});
			}.createDelegate(this)
		});
		
		this.CVI.ViewGridPanel.view = new Ext.grid.GridView(
			{
				getRowClass: function (row, index)
				{
					var cls = '';
					var red = row.get('psign');
					if (red == 1) {
						cls = "x-grid-rowbackred";
					} else {
						cls = 'x-grid-panel';
					}
					return cls;
				}
			});

		this.PersonDispInfo = new sw.Promed.ViewFrame({
			dataUrl: '/?c=SignalInfo&m=loadPersonDispInfo',
			pageSize: 100,
			paging: true,
			autoLoadData: false,
			useEmptyRecord: true,
			region: 'center',
			root: 'data',
			id: 'SignalInfoPersonDispInfo',
			stringfields: [
				{name: 'PersonDispVizit_id', header: 'ID', type: 'int', header: 'PersonDisp_id', hidden: true},
				{name: 'PersonDisp_id', type: 'int', header: 'PersonDisp_id', hidden: true},
				{name: 'Person_id', type: 'int', header: 'Person_id', hidden: true},
				{name: 'Server_id', type: 'int', header: 'Person_id', hidden: true},
				{name: 'Person_Fio', type: 'string', header: langs('ФИО'), width: 250},
				{name: 'Person_BirthDay', type: 'date', header: langs('Дата рождения'), width: 110},
				{name: 'Person_Age', type: 'int', header: langs('Возраст'), width: 80},
				{name: 'Diag_FullName', type: 'string', header: langs('Диагноз ДУ'), width: 300},
				{name: 'PersonDisp_NextDate', type: 'date', header: langs('Дата планового осмотра'), width: 150},
				{name: 'Person_Phone', header: langs('Контактный телефон'), width: 130}
			],
			actions: [
					{name: 'action_add', hidden: true, disabled: true},
					{name: 'action_view', hidden: true, disabled: true},
					{name: 'action_edit', hidden: true, disabled: true},
					{name: 'action_refresh', handler: function () {
						this.doSearch();
					}.createDelegate(this)},
					{name: 'action_delete', hidden: true, disabled: true}
			],
			totalProperty: 'totalCount',
			onDblClick: function () {
				win.openEPHForm();
			}
		});

		this.PersonDispInfo.ViewGridPanel.view = new Ext.grid.GridView(
			{
				getRowClass: function (row, index)
				{
					var cls = '';
					var CurrDate = Date.parseDate(getGlobalOptions().date, 'd.m.Y');
					var PersonDisp_NextDate =  row.get('PersonDisp_NextDate');
					if (PersonDisp_NextDate != null && PersonDisp_NextDate < CurrDate) {
						cls = "x-grid-rowbackred";
					} else {
						cls = 'x-grid-panel';
					}
					return cls;
				}
			});

		//var click1 = false;
		var click2 = false;
		var click3 = false;
		var click4 = false;
		var click5 = false;
		var click6 = false;
		var click7 = false;

		win.tabPanelPersonPregnancy = new Ext.TabPanel({
			region: 'center',
			id: 'tabPanelPersonPregnancy',
			activeTab: 0,
			autoScroll: true,
			enableTabScroll: true,
			layoutOnTabChange: true,
			deferredRender: false, //чтоб рендились все вкладки при создании таба
			plain: true,
			items: [
				{
					layout: 'border',
					title: 'По МО',
					id: 'tab_PersonPregnancy',
					items: [
						win.PersonPregnancyFiltersPanel,
						win.SignalInfoRecommRouterGrid
					]
				},
				{
					layout: 'border',
					title: 'По региону',
					id: 'tab_PregnancyRegion',
					items: [
						win.RecommRouterRegionGridPanel
					]
				},
				{
					layout: 'border',
					title: 'Не включенные в регистр',
					id: 'tab_PregnancyNotInclude',
					items: [
						win.NotIncludeGridPanel
					]
				}
			]
		})


		win.tabDispGridPanel = new Ext.TabPanel({
			region: 'center',
			id: 'tabDispGridPanel',
			activeTab: 0,
			autoScroll: true,
			enableTabScroll: true,
			layoutOnTabChange: true,
			deferredRender: false,
			plain: true,
			items: [
				{
					layout: 'border',
					title: 'I этап',
					id: 'tab_DispGridPanel1',
					items: [
						win.DispGridPanel1
					]
				},
				{
					layout: 'border',
					title: 'II этап',
					id: 'tab_DispGridPanel2',
					items: [
						win.DispGridPanel2
					]
				}
			]
		});

		//Группа вкладок для "Не проведена консультация"
		win.tabPanelPregnancyRouteNoConsultation = new Ext.TabPanel({
			region: 'center',
			id: 'tabPanelPregnancyRouteNoConsultation',
			activeTab: 0,
			autoScroll: true,
			enableTabScroll: true,
			layoutOnTabChange: true,
			deferredRender: false,
			plain: true,
			items: [
				{
					layout: 'border',
					title: 'По МО',
					id: 'tab_PregnancyRouteNoConsultation',
					items: [
						win.filterPregnancyRouteNoConsultation,
						win.PregnancyRouteNoConsultation
					]
				},
				{
					layout: 'border',
					title: 'По региону',
					id: 'tab_PregnancyRouteNotConsultationRegion',
					items: [
						win.PregnancyRouteNotConsultationRegion
					]
				}
			]
		});

		//Группа вкладок для "Находятся на госпитализации"
		win.tabPanelPregnancyRouteHospital = new Ext.TabPanel({
			region: 'center',
			id: 'tabPanelPregnancyRouteHospital',
			activeTab: 0,
			autoScroll: true,
			enableTabScroll: true,
			layoutOnTabChange: true,
			deferredRender: false,
			plain: true,
			items: [
				{
					layout: 'border',
					title: 'По МО',
					id: 'tab_PregnancyRouteHospital',
					items: [
						win.filterPregnancyRouteHospital,
						win.PregnancyRouteHospital
					]
				},
				{
					layout: 'border',
					title: 'По региону',
					id: 'tab_PregnancyRouteHospitalRegion',
					items: [
						win.PregnancyRouteHospitalRegion
					]
				}
			]
		});
	
		win.tabPanel = new Ext.TabPanel({
			//border: false,
			region: 'center',
			id: 'tabPanel',
			activeTab: 0,
			autoScroll: true,
			enableTabScroll: true,
			layoutOnTabChange: true,
			deferredRender: false, //чтоб рендились все вкладки при создании таба
			plain: true,
			items: [{
					layout: 'border',
					title: 'Параклинические услуги',
					id: 'tab_EvnUsluga',
					items: [
						win.filterEvnUsluga,
						win.EvnUsluga
					],
					listeners: {
						beforeshow: function () {
							//click1 = true;
						}
					}
				},
				{
					layout: 'border',
					title: "Выписанные из стационара",
					id: 'tab_grid',
					items: [
						win.filtergrid,
						win.grid
					],
					listeners: {
						beforeshow: function () {
							click2 = true;
						}
					}
				},
				{
					layout: 'border',
					title: 'Свидетельство о смерти',
					id: 'tab_MedSvidDeath',
					items: [
						win.filterMedSvidDeath,
						win.MedSvidDeath
					],
					listeners: {
						beforeshow: function () {
							click5 = true;
						}
					}
				},
				{
					layout: 'border',
					title: 'Карты СМП: поиск',
					id: 'tab_CmpCallCardSearch',
					items: [
						win.filterCmpCallCardSearch,
						win.CmpCallCardSearch
					],
					listeners: {
						beforeshow: function () {
							click6 = true;
						}
					}
				},
				{
					layout: 'border',
					title: 'Регистр льготников',
					id: 'tab_PrivGridPanel',
					items: [
						win.PrivGridPanel
					],
					listeners: {
						beforeshow: function () {

							click3 = true;
						}
					}
				},
				{
					layout: 'border',
					title: 'Открытые ЭЛН',
					id: 'tab_SearchGrid',
					items: [
						win.SearchGrid
					],
					listeners: {
						beforeshow: function () {
							click4 = true;
						}
					}
				},
				{
					layout: 'border',
					title: 'Беременные женщины',
					id: 'tab_tabPanelPersonPregnancy',
					items: [
						win.tabPanelPersonPregnancy
					]
				},
				{
					layout: 'border',
					title: 'Диспансеризация',
					id: 'tab_DispGridPanel',
					items: [
						win.tabDispGridPanel
					]
				},
				{
					layout: 'border',
					title: 'Список неявившихся',
					id: 'tab_PersonNoVisitGridPanel',
					items: [
						win.filterPersonNoVisit,
						win.PersonNoVisitGridPanel
					]
				},
				{
					layout: 'border',
					title: 'ЦДК',
					id: 'tab_cdkPanel',
					items: [
						win.cdkFilterPanel,
						win.cdkGridPanel
					],
					listeners: {
						"activate": function(){
							this.doSearch();
						}.createDelegate(this)
					}
				},
				//Сигнальная информация для врача поликлиники (доработка по беременным)
				{
					layout: 'border',
					title: 'Не проведена консультация',
					id: 'tab_tabPanelPregnancyRouteNoConsultation',
					items: [
						win.tabPanelPregnancyRouteNoConsultation
					]
				},
				{
					layout: 'border',
					title: 'Находятся на госпитализации',
					id: 'tab_tabPanelPregnancyRouteHospital',
					items: [
						win.tabPanelPregnancyRouteHospital
					]
				},
				{
					layout: 'border',
					title: 'Выписанные из стационара',
					id: 'tab_PregnancyRouteDisHospital',
					items: [
						win.filterPregnancyRouteDisHospital,
						win.PregnancyRouteDisHospital
					]
				},
				{
					layout: 'border',
					title: 'Изменилась группа риска',
					id: 'tab_PregnancyRouteRiskСhange',
					items: [
						win.PregnancyRouteRiskСhange
					]
				},
				{
					layout: 'border',
					title: 'Регистр БСК',
					id: 'tab_RegisterBSK',
					items: [
						win.filterRegisterBSK,
						win.RegisterBSK
					]
				},
				{
					layout: 'border',
					title: 'КВИ',
					id: 'tab_CVI',
					items: [
						win.CVI
					]
				},
				{
					layout: 'border',
					title: 'Диспансерный учет',
					id: 'tab_PersonDispInfo',
					items: [
						win.filterPersonDispInfo,
						win.PersonDispInfo
					]/*,
					listeners: {
						"activate": function(){
							this.doSearch();
						}.createDelegate(this)
					}*/
				},
				{
					layout: 'border',
					title: 'Дистанционный мониторинг: поиск',
					id: 'tab_DistObserv',
					items: [
						win.filterDistObserv,
						win.DistObservSearch
					],
					listeners: {
						"activate": function(){
							this.doSearch();
						}.createDelegate(this)
					}
				}
			],
			listeners:
					{
						tabchange: function (tab, panel)
						{
							win.addGridFilter(true);
						}
					}


		});

		Ext.apply(this,
				{
					layout: 'border',
					items: [
						this.tabPanel
					],
					buttons: [

						{
							id: 'SignalInfo_Search',
							text: langs('Найти'),
							tabIndex: TABINDEX_EJHW + 19,
							iconCls: 'search16',
							handler: function ()
							{
								this.doSearch();
							}.createDelegate(this)
						},
						{
							id: 'SignalInfo_Clear',
							text: langs('Сброс'),
							tabIndex: TABINDEX_EJHW + 21,
							iconCls: 'resetsearch16',
							handler: function ()
							{
								// this.dateMenu.setValue(null);
								//this.getCurrentDateTime();
								this.findById('EvnUslugaPar_setDate_Range').setValue(this.getPeriod1DaysLast());
								this.findById('EvnPS_disDateTime').setValue(this.getPeriod1DaysLast());
								this.findById('PregnancyRouteDisHospital_disDateTime').setValue(this.getPeriod1DaysLast());
								this.findById('Death_Date').setValue(this.getPeriod1DaysLast());
								this.findById('CmpCallCard_prmDate_Range').setValue(this.getPeriod1DaysLast());
								this.findById('PersonNoVisit_prmDate_Range').setValue(this.getPeriod1DaysLast());
								this.findById('RegisterBSK_prmDate_Range').setValue(this.getPeriod1DaysLast());
								this.findById('PersonDispInfo_prmDate_Range').setValue(this.getPeriod1DaysLast());
								this.doSearch();
							}.createDelegate(this)
						},
						{
							text: '-'
						},
						{
							text: BTN_FRMHELP,
							iconCls: 'help16',
							handler: function (button, event) {
								ShowHelp(this.ownerCt.title);
							}
						},
						{
							text: BTN_FRMCLOSE,
							iconCls: 'cancel16',
							id: 'button_close',
							handler: function () {

								//var tab_EvnUsluga = win.EvnUsluga.getCount();
								var tab_grid = win.grid.getCount();
								var tab_PrivGridPanel = win.PrivGridPanel.getCount();
								var tab_SearchGrid = win.SearchGrid.getCount();
								var tab_MedSvidDeath = win.MedSvidDeath.getCount();
								var tab_CmpCallCardSearch = win.CmpCallCardSearch.getCount();

								var title_grid = '';
								var title_PrivGridPanel = '';
								var title_SearchGrid = '';
								var title_MedSvidDeath = '';
								var title_CmpCallCardSearch = '';
								var title_DistObserv = '';


								/*if (tab_EvnUsluga == 0 ){
								 click1 = true;
								 } else {
								 var title_EvnUsluga = 'Параклинические услуги <br>';
								 }*/
								if (tab_grid == 0) {
									click2 = true;
								} else {
									title_grid = 'Выписанные из стационара <br>';
								}
								if (tab_PrivGridPanel == 0) {
									click3 = true;
								} else {
									title_PrivGridPanel = 'Регистр льготников <br>';
								}
								if (tab_SearchGrid == 0) {
									click4 = true;
								} else {
									title_SearchGrid = 'Открытые ЛВН <br>';
								}
								if (tab_MedSvidDeath == 0) {
									click5 = true;
								} else {
									title_MedSvidDeath = 'Медсвидетельства о смерти <br>';
								}
								if (tab_CmpCallCardSearch == 0) {
									click6 = true;
								} else {
									title_CmpCallCardSearch = 'Вызовы СМП <br>';
								}
								
								if (tab_DistObserv == 0) {
									click6 = true;
								} else {
									title_DistObserv = 'Дистанционный мониторинг <br>';
								}
								/* if (tab_SignalInfoRecommRouterGridPanel == 0) {
								 click7 = true;
								 } else {
								 title_SignalInfoRecommRouterGridPanel = 'Беременные женщины <br>';
								 }*/
								if (click2 == true) {
									title_grid = '';
								}
								if (click3 == true) {
									title_PrivGridPanel = '';
								}
								if (click4 == true) {
									title_SearchGrid = '';
								}
								if (click5 == true) {
									title_MedSvidDeath = '';
								}
								if (click6 == true) {
									title_CmpCallCardSearch = '';
								}

								if (((click2 == true) && (click3 == true) && (click4 == true) && (click5 == true) && (click6 == true)) || this.SignalInfoPersonPregnancy == true) {
									Ext.getCmp('SignalInfoWindow').refresh();
								} else {
									Ext.getCmp('SignalInfoWindow').showMsg('Пожалуйста, откройте таблицы: <br>' + title_grid + title_PrivGridPanel + title_SearchGrid + title_MedSvidDeath + title_CmpCallCardSearch);
									return false;
								}

							}
						}

					],

				}
		);
		sw.Promed.swSignalInfoWindow.superclass.initComponent.apply(this, arguments);
	},

	// считаем количество записей и выводим в header

	addGridFilter: function (tabChange) {
		var win = this;

		win.EvnUsluga.getGrid().getStore().clearFilter();
		win.grid.getGrid().getStore().clearFilter();
		win.PrivGridPanel.getGrid().getStore().clearFilter();
		win.SearchGrid.getGrid().getStore().clearFilter();
		win.CmpCallCardSearch.getGrid().getStore().clearFilter();
		win.PersonNoVisitGridPanel.getGrid().getStore().clearFilter();
		win.PregnancyRouteDisHospital.getGrid().getStore().clearFilter();
		win.PregnancyRouteRiskСhange.getGrid().getStore().clearFilter();
		win.CVI.getGrid().getStore().clearFilter();
		win.DistObservSearch.getGrid().getStore().clearFilter();

		var tab_EvnUsluga = win.EvnUsluga.getCount();
		var tab_grid = win.grid.getCount();
		var tab_PrivGridPanel = win.PrivGridPanel.getCount();
		var tab_SearchGrid = win.SearchGrid.getCount();
		var tab_MedSvidDeath = win.MedSvidDeath.getCount();
		var tab_CmpCallCardSearch = win.CmpCallCardSearch.getCount();
		var tab_DistObserv = win.DistObservSearch.getCount();
		var tab_PersonNoVisitGridPanel = win.PersonNoVisitGridPanel.getCount();
		var tab_PregnancyRouteDisHospital = win.PregnancyRouteDisHospital.getCount();
		var tab_PregnancyRouteRiskСhange = win.PregnancyRouteRiskСhange.getCount();
		var tab_CVI = win.CVI.getCount();

		win.tabPanel.getComponent('tab_EvnUsluga').setTitle("Параклинические услуги: " + "<b>" + tab_EvnUsluga + "</b>");
		win.tabPanel.getComponent('tab_grid').setTitle("Выписанные из стационара: " + "<b>" + tab_grid + "</b>");
		win.tabPanel.getComponent('tab_MedSvidDeath').setTitle("Медсвидетельства о смерти: " + "<b>" + tab_MedSvidDeath + "</b>");
		win.tabPanel.getComponent('tab_CmpCallCardSearch').setTitle("Вызовы СМП: " + "<b>" + tab_CmpCallCardSearch + "</b>");
		win.tabPanel.getComponent('tab_PrivGridPanel').setTitle("Регистр льготников: " + "<b>" + tab_PrivGridPanel + "</b>");
		win.tabPanel.getComponent('tab_SearchGrid').setTitle("Открытые ЛВН: " + "<b>" + tab_SearchGrid + "</b>");
		win.tabPanel.getComponent('tab_PersonNoVisitGridPanel').setTitle("Список неявившихся: " + "<b>" + tab_PersonNoVisitGridPanel + "</b>");
		win.tabPanel.getComponent('tab_PregnancyRouteDisHospital').setTitle("Выписанные из стационара: " + "<b>" + tab_PregnancyRouteDisHospital + "</b>");
		win.tabPanel.getComponent('tab_PregnancyRouteRiskСhange').setTitle("Изменилась группа риска: " + "<b>" + tab_PregnancyRouteRiskСhange + "</b>");
		win.tabPanel.getComponent('tab_CVI').setTitle("КВИ: " + "<b>" + tab_CVI + "</b>");
		//win.tabPanel.setTabTitle(16, "Дистанционный мониторинг: " + "<b>" + tab_DistObserv + "</b>");
	},
	showMsg: function (msg) {
		sw.swMsg.show(
				{
					buttons: Ext.Msg.OK,
					icon: Ext.Msg.WARNING,
					width: 600,
					msg: msg,
					title: langs('Ошибка')
				});
	},

	refresh: function () {
		sw.codeInfo.lastObjectName = this.objectName;
		sw.codeInfo.lastObjectClass = this.objectClass;
		if (sw.Promed.Actions.loadLastObjectCode)
		{
			sw.Promed.Actions.loadLastObjectCode.setHidden(false);
			sw.Promed.Actions.loadLastObjectCode.setText('Обновить ' + this.objectName + ' ...');
		}
		// Удаляем полностью объект из DOM, функционал которого хотим обновить
		this.hide();
		this.close();
		window[this.objectName] = null;
		delete sw.Promed[this.objectName];
	},

	show: function (params) {

		sw.Promed.swSignalInfoWindow.superclass.show.apply(this, arguments);
		var win = this;
		this.viewOnly = false;

		this.SignalInfoPersonPregnancy = false;
		this.userMedStaffFact = sw.Promed.MedStaffFactByUser.last;
		if ((!arguments[0]) || (!arguments[0].userMedStaffFact)) {
			this.hide();
			Ext.Msg.alert('Ошибка открытия формы', 'Ошибка открытия формы "' + this.title + '".<br/>Не указаны параметры АРМа врача.');
		} else {
			this.userMedStaffFact = arguments[0].userMedStaffFact;
		}
		this.viewOnly = false;
		if (arguments[0] && arguments[0].viewOnly) {
			this.viewOnly = arguments[0].viewOnly;
		}

		this.center();
		//this.getCurrentDateTime();
		//this.doSearch('period');
		this.findById('EvnUslugaPar_setDate_Range').setValue(this.getPeriod1DaysLast());
		this.findById('EvnPS_disDateTime').setValue(this.getPeriod1DaysLast());
		this.findById('PregnancyRouteDisHospital_disDateTime').setValue(this.getPeriod1DaysLast());
		this.findById('Death_Date').setValue(this.getPeriod1DaysLast());
		this.findById('CmpCallCard_prmDate_Range').setValue(this.getPeriod1DaysLast());
		this.findById('PersonNoVisit_prmDate_Range').setValue(this.getPeriod1DaysLast2());
		this.findById('RegisterBSK_prmDate_Range').setValue(this.getPeriod1DaysLast());
		this.findById('PersonDispInfo_prmDate_Range').setValue(this.getPeriod1DaysLast(-1,5));
		var PostMed_id = this.userMedStaffFact.PostMed_id;
		var PostMed_Code = this.userMedStaffFact.PostMed_Code;
		var tabPanel = Ext.getCmp('tabPanel');
		for (var i = 0; i < tabPanel.items.length; i++) {
			tabPanel.hideTabStripItem(i);
		}
		tabPanel.unhideTabStripItem('tab_PersonDispInfo');
		tabPanel.unhideTabStripItem('tab_DistObserv');
		if (getRegionNick() == 'ufa' && PostMed_Code.inlist([25,41])) {
			tabPanel.unhideTabStripItem('tab_cdkPanel');
		}
		if (PostMed_id == '41') {//врач-онколог
			tabPanel.unhideTabStripItem('tab_PersonNoVisitGridPanel');
			tabPanel.setActiveTab('tab_PersonNoVisitGridPanel');
			this.doSearch('tab_PersonNoVisitGridPanel');
			//Врач-терапевт участковый, Врач-педиатр участковый, Врач общей практики (семейный врач), Фельдшер, Заведующий фельдшерско-акушерским пунктом фельдшер (акушерка, медицинская сестра)
		} else if (PostMed_id == '74' || PostMed_id == '47' || PostMed_id == '40' || PostMed_id == '117' || PostMed_id == '111') {
			tabPanel.unhideTabStripItem('tab_EvnUsluga');
			tabPanel.unhideTabStripItem('tab_grid');
			//скрываем вкладку мед. свидетельство при автозагрузки
			if (params.MedSvidDeath == undefined) {
				tabPanel.unhideTabStripItem('tab_MedSvidDeath');
			}
			tabPanel.unhideTabStripItem('tab_CmpCallCardSearch');
			tabPanel.unhideTabStripItem('tab_PrivGridPanel');
			tabPanel.unhideTabStripItem('tab_SearchGrid');
			tabPanel.unhideTabStripItem('tab_DispGridPanel');
			tabPanel.unhideTabStripItem('tab_RegisterBSK');
			tabPanel.unhideTabStripItem('tab_CVI');
			tabPanel.setActiveTab('tab_CVI');
			this.doSearch("tab_CVI");
			win.doSearch1dey(params.MedSvidDeath);
			// Врач-акушер-гинеколог
		} else if (PostMed_id == '12') {
			win.findById('S_MedPersonal_id').getStore().load({
				params: {Lpu_id: getGlobalOptions().lpu_id, All_Rec: 1},
				callback: function () {
					win.findById('S_MedPersonal_id').setValue(getGlobalOptions().medpersonal_id);
				}.createDelegate(this)
			});
			win.findById('NoConsultationMedPersonal_id').getStore().load({
				params: {Lpu_id: getGlobalOptions().lpu_id, All_Rec: 1},
				callback: function () {
					win.findById('NoConsultationMedPersonal_id').setValue(getGlobalOptions().medpersonal_id);
				}.createDelegate(this)
			});
			win.findById('HospitalMedPersonal_id').getStore().load({
				params: {Lpu_id: getGlobalOptions().lpu_id, All_Rec: 1},
				callback: function () {
					win.findById('HospitalMedPersonal_id').setValue(getGlobalOptions().medpersonal_id);
				}.createDelegate(this)
			});
			this.SignalInfoPersonPregnancy = true;
			tabPanel.unhideTabStripItem('tab_tabPanelPersonPregnancy'); 
			tabPanel.unhideTabStripItem('tab_tabPanelPregnancyRouteNoConsultation'); 
			tabPanel.unhideTabStripItem('tab_tabPanelPregnancyRouteHospital');
			tabPanel.unhideTabStripItem('tab_PregnancyRouteDisHospital');
			tabPanel.unhideTabStripItem('tab_CmpCallCardSearch'); 
			tabPanel.unhideTabStripItem('tab_PregnancyRouteRiskСhange');
			tabPanel.setActiveTab('tab_tabPanelPersonPregnancy');
			//Если пользователь без группы прав МПЦ
			if (!win.isFullAccess()) {
				win.tabPanelPersonPregnancy.getItem('tab_PregnancyRegion').setDisabled(true);
				win.tabPanelPregnancyRouteNoConsultation.getItem('tab_PregnancyRouteNotConsultationRegion').setDisabled(true);
				win.tabPanelPregnancyRouteHospital.getItem('tab_PregnancyRouteHospitalRegion').setDisabled(true);
			}
			this.doSearch('tab_PregnancyRouteDisHospital');
			this.doSearch('tab_PregnancyRouteRiskСhange');
			this.doSearch('tab_tabPanelPersonPregnancy', 'tab_PregnancyNotInclude');
			win.doSearch1deyPregnancy();
		} else if (PostMed_id == '179' || PostMed_id == '182'){ //Врач - детский кардиолог, Врач-кардиолог
			tabPanel.unhideTabStripItem('tab_RegisterBSK');
			tabPanel.setActiveTab('tab_RegisterBSK');
		} else {
			tabPanel.setActiveTab('tab_PersonDispInfo');
		}

		var emkmenu = {
			handler: function () {
				win.openEPHForm();
			}.createDelegate(this),
			iconCls: 'open16',
			name: 'open_emk',
			text: langs('Открыть ЭМК'),
			tooltip: langs('Открыть электронную медицинскую карту пациента'),
			disabled: false
		};

		this.EvnUsluga.addActions(emkmenu);
		this.grid.addActions(emkmenu);
		this.PrivGridPanel.addActions(emkmenu);
		this.SearchGrid.addActions(emkmenu);
		this.MedSvidDeath.addActions(emkmenu);
		this.CmpCallCardSearch.addActions(emkmenu);
		this.PersonNoVisitGridPanel.addActions(emkmenu);
		this.cdkGridPanel.addActions(emkmenu);
		this.PregnancyRouteNoConsultation.addActions(emkmenu);
		this.PregnancyRouteHospital.addActions(emkmenu);
		this.PregnancyRouteDisHospital.addActions(emkmenu);
		this.PregnancyRouteRiskСhange.addActions(emkmenu);
		this.NotIncludeGridPanel.addActions(emkmenu);
		this.SignalInfoRecommRouterGrid.addActions(emkmenu);
		this.RegisterBSK.addActions(emkmenu);
		this.CVI.addActions(emkmenu);
		this.PersonDispInfo.addActions(emkmenu);
		this.DistObservSearch.addActions(emkmenu);

	},
	isFullAccess: function () {
		return isUserGroup('InterdistrictPerCenter'); // группа прав МПЦ
	},
	doSearch1dey: function (isMedSvidDeath) {

		var win = this;

		win.EvnUsluga.setDataUrl('/?c=SignalInfo&m=loadEvnUsluga');
		var params1 = new Object();
		params1.start = 0;
		params1.limit = 100;
		params1.Lpu_id = this.userMedStaffFact.Lpu_id; // id ЛПУ
		params1.MedPersonal_id = this.userMedStaffFact.MedPersonal_id; //id врача
		this.EvnUsluga.removeAll();
		this.EvnUsluga.loadData({globalFilters: params1});

		win.grid.setDataUrl('/?c=SignalInfo&m=loadEvnPS');
		var params2 = new Object();
		params2.start = 0;
		params2.limit = 100;
		params2.Lpu_aid = this.userMedStaffFact.Lpu_id; // id ЛПУ
		params2.MedPersonal_id = this.userMedStaffFact.MedPersonal_id; //id врача
		this.grid.removeAll();
		this.grid.loadData({globalFilters: params2});

		win.SearchGrid.setDataUrl('/?c=SignalInfo&m=loadEvnStick');
		var params3 = new Object();
		params3.start = 0;
		params3.limit = 100;
		params3.Lpu_id = this.userMedStaffFact.Lpu_id; // id ЛПУ
		params3.MedPersonal_id = this.userMedStaffFact.MedPersonal_id; //id врача
		this.SearchGrid.removeAll();
		this.SearchGrid.loadData({globalFilters: params3});

		if (isMedSvidDeath == undefined) {
			win.MedSvidDeath.setDataUrl('/?c=SignalInfo&m=loadDeathSvid');
			var params4 = new Object();
			params4.start = 0;
			params4.limit = 100;
			params4.Lpu_id = this.userMedStaffFact.Lpu_id; // id ЛПУ
			params4.MedPersonal_id = this.userMedStaffFact.MedPersonal_id; //id врача
			this.MedSvidDeath.removeAll();
			this.MedSvidDeath.loadData({globalFilters: params4});
		}

		win.PrivGridPanel.setDataUrl('/?c=SignalInfo&m=loadRegisterPrivilege');
		var params5 = new Object();
		params5.start = 0;
		params5.limit = 100;
		params5.Lpu_id = this.userMedStaffFact.Lpu_id; // id ЛПУ
		params5.MedPersonal_id = this.userMedStaffFact.MedPersonal_id; //id врача
		this.PrivGridPanel.removeAll();
		this.PrivGridPanel.loadData({globalFilters: params5});

		win.CmpCallCardSearch.setDataUrl('/?c=SignalInfo&m=loadCmpCallCard');
		var params6 = new Object();
		params6.start = 0;
		params6.limit = 100;
		params6.Lpu_id = this.userMedStaffFact.Lpu_id; // id ЛПУ
		params6.MedPersonal_id = this.userMedStaffFact.MedPersonal_id; //id врача
		this.CmpCallCardSearch.removeAll();
		this.CmpCallCardSearch.loadData({globalFilters: params6});

	},
	//поиск для беременных
	doSearch1deyPregnancy: function () {
		var win = this;
		win.CmpCallCardSearch.setDataUrl('/?c=SignalInfo&m=loadPregnancyRouteSMP');
		var params2 = new Object();
		params2.start = 0;
		params2.limit = 100;
		params2.Lpu_iid = this.userMedStaffFact.Lpu_id; // id ЛПУ
		params2.MedPersonal_iid = this.userMedStaffFact.MedPersonal_id; //id врача
		params2.Yesterday = 1;
		this.CmpCallCardSearch.removeAll();
		this.CmpCallCardSearch.loadData({globalFilters: params2});
	},
	listeners: {
		'hide': function () {
			if (this.refresh)
				this.onHide();
		},
		'close': function () {
			if (this.refresh)
				this.onHide();
		}
	},
	openRouterViewWindow: function (params) {
		switch (params) {
			case 'RecommRouterRegionGridPanel':
				var grid = this.RecommRouterRegionGridPanel.getGrid();
				break;
			case 'PregnancyRouteNotConsultationRegion':
				var grid = this.PregnancyRouteNotConsultationRegion.getGrid();
				break;
			case 'PregnancyRouteHospitalRegion':
				var grid = this.PregnancyRouteHospitalRegion.getGrid();
				break;
		}

		var selectedCell = grid.getSelectionModel().getSelectedCell();
		if (!selectedCell)
			return;
		var rowIndex = selectedCell[0];
		var columnIndex = selectedCell[1];
		var columnIndex = grid.getColumnModel().getDataIndex(columnIndex);

		getWnd('swRecommRouterViewWindow').show({
			Lpu_iid: grid.getStore().getAt(rowIndex).data.Lpu_iid,
			start: 0,
			limit: 100,
			type: params,
			mode: columnIndex, //триместер
			onHide: function () {
				//current_window.doSearch();
			}
		})
	}

});