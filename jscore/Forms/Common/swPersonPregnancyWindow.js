/**
 * swPersonPregnancyWindow - окно просмотра регистра беременных
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009-2014 Swan Ltd.
 * @author       Dmitry Vlasenko
 * @version      11.2014
 *
 */

sw.Promed.swPersonPregnancyWindow = Ext.extend(sw.Promed.BaseForm, {
	buttonAlign: 'right',
	modal: true,
	layout: 'border',
	maximized: true,
	resizable: false,
	closable: true,
	shim: false,
	width: 500,
	closeAction: 'hide',
	id: 'swPersonPregnancyWindow',
	objectName: 'swPersonPregnancyWindow',
	title: lang['registr_beremennyih'],
	plain: true,
	buttons: [
		{
			text: BTN_FRMSEARCH,
			handler: function(){
				Ext.getCmp('swPersonPregnancyWindow').doSearch();
			},
			iconCls: 'search16'
		}, {
			text: lang['sbros'],
			handler: function(){
				Ext.getCmp('swPersonPregnancyWindow').doSearch(true);
			},
			iconCls: 'resetsearch16'
		},
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

	resetFilters: function(tabId) {
		if (!tabId) {
			tabId = this.TabPanel.getActiveTab().getId();
		}

		var pp_form = this.PersonPregnancyFiltersPanel.getForm();
		var evn_form = this.EvnFiltersPanel.getForm();
		var mon_form = this.MonitorCenterFiltersPanel.getForm();

		pp_form.reset();
		evn_form.reset();

		if (!this.isFullAccess() && !haveArmType('spec_mz')) {
			pp_form.findField('Lpu_iid').setValue(getGlobalOptions().lpu_id);
			evn_form.findField('Lpu_oid').setValue(getGlobalOptions().lpu_id);
		}
		pp_form.findField('Lpu_iid').fireEvent('change', pp_form.findField('Lpu_iid'), pp_form.findField('Lpu_iid').getValue());
		//evn_form.findField('Lpu_oid').fireEvent('change', evn_form.findField('Lpu_oid'), evn_form.findField('Lpu_oid').getValue());
				if (evn_form.findField('Lpu_oid').getValue() == ""){
                    evn_form.findField('Lpu_oid').fireEvent('change', evn_form.findField('Lpu_oid'), 0);
                }else{
		evn_form.findField('Lpu_oid').fireEvent('change', evn_form.findField('Lpu_oid'), evn_form.findField('Lpu_oid').getValue());
                }		

		var date = new Date().format('d.m.Y');
		var date_range = date+' - '+date;

		pp_form.findField('PersonRegister_setDateRange').setValue(date_range);
		pp_form.findField('PersonRegister_disDateRange').setValue(tabId=='Out'?date_range:null);
		evn_form.findField('Evn_setDateRange').setValue(date_range);
	},

	openPersonPregnancyEditWindow: function(action, gridPanel) {
		if (!action || !action.inlist(['add','edit','view'])) {
			return false;
		}

		var base_form = this.PersonPregnancyFiltersPanel.getForm();
		var params = {
			action: action,
			userMedStaffFact: this.userMedStaffFact
		};
		params.callback = function() {
			gridPanel.getAction('action_refresh').execute();
		};
		var that = this;
		if (action == 'add') {
			getWnd('swPersonSearchWindow').show({
				onClose: Ext.emptyFn,
				onSelect: function(person_data) {
					if (person_data.Sex_id == 1) {
						sw.swMsg.alert(lang['soobschenie'], 'Не возможно добавить пациента мужского пола в регистр беременных');
					} else {
						getWnd('swPersonSearchWindow').hide();
						params.Person_id = person_data.Person_id;
						getWnd('swPersonPregnancyEditWindow').show(params);
					}
				},
				personFirname: base_form.findField('Person_FirName').getValue(),
				personSecname: base_form.findField('Person_SecName').getValue(),
				personSurname: base_form.findField('Person_SurName').getValue(),
				viewOnly: (that.editType=='onlyRegister')?true:false,
				searchMode: 'all'
			});
		} else {
			var record = gridPanel.getGrid().getSelectionModel().getSelected();
			if (!record || Ext.isEmpty(record.get('PersonRegister_id'))) {
				return false;
			}
			params.Person_id = record.get('Person_id');
			params.PersonRegister_id = record.get('PersonRegister_id');
			params.PersonDisp_id = record.get('PersonDisp_id');
			params.PersonPregnancy_id = record.get('PersonPregnancy_id');
			params.editType = that.editType;
			//params.action = (this.editType=='onlyRegister')?'view':'edit';
			if(params.action == 'edit' && !isPregnancyRegisterAccess())
				params.action = 'view';
			getWnd('swPersonPregnancyEditWindow').show(params);
		}
		return true;
	},

	deletePersonRegister: function(grid_panel) {
		var grid = grid_panel.getGrid();

		var record = grid.getSelectionModel().getSelected();

		if (!record || !record.get('PersonRegister_id')) {
			return false;
		}

		sw.swMsg.show({
			buttons:Ext.Msg.YESNO,
			fn:function (buttonId, text, obj) {
				if (buttonId == 'yes') {
					var params = {PersonRegister_id: record.get('PersonRegister_id')};

					Ext.Ajax.request({
						callback: function(opt, scs, response) {
							var response_obj = Ext.util.JSON.decode(response.responseText);

							if (!response_obj.Error_Msg) {
								grid_panel.getAction('action_refresh').execute();
							}
						}.createDelegate(this),
						params: params,
						url: '/?c=PersonPregnancy&m=deletePersonRegister'
					});
				}
			}.createDelegate(this),
			icon:Ext.MessageBox.QUESTION,
			msg:lang['vyi_hotite_udalit_zapis'],
			title:lang['podtverjdenie']
		});
	},

	isFullAccess: function() {
		return isUserGroup('RegOperPregnRegistry') || (isUserGroup('OperPregnRegistry') && getGlobalOptions().birth_mes_level_code > 1);
	},

	show: function()
	{
		sw.Promed.swPersonPregnancyWindow.superclass.show.apply(this, arguments);

		var win = this;
		var pp_form = win.PersonPregnancyFiltersPanel.getForm();
		var evn_form = win.EvnFiltersPanel.getForm();
		var mon_form = win.MonitorCenterFiltersPanel.getForm();

		this.userMedStaffFact = sw.Promed.MedStaffFactByUser.last;

		if (arguments[0] && arguments[0].userMedStaffFact) {
			this.userMedStaffFact = arguments[0].userMedStaffFact;
		}

		pp_form.findField('PersonPregnancy_IsKDO').setContainerVisible(getRegionNick() == 'perm');

		this.NewGridPanel.removeAll();
		this.AllGridPanel.removeAll();
		this.OutGridPanel.removeAll();
		this.NotIncludeGridPanel.removeAll();
		this.RecommRouterGridPanel.removeAll();
		this.MonitorCenterGridPanel.removeAll();

		//pp_form.findField('Lpu_iid').setDisabled(!this.isFullAccess() && !haveArmType('spec_mz'));
		if (!this.isFullAccess() && !haveArmType('spec_mz')) {
			pp_form.findField('Lpu_iid').setAllowBlank(false);
			pp_form.findField('Lpu_iid').baseFilterFn = function(record) {
				return record.get('Lpu_id').inlist([getGlobalOptions().lpu_id]);
			};
		} else {
			pp_form.findField('Lpu_iid').setAllowBlank(true);
			pp_form.findField('Lpu_iid').baseFilterFn = null;
		}
		evn_form.findField('Lpu_oid').setDisabled(!this.isFullAccess() && !haveArmType('spec_mz'));

		this.NewGridPanel.getGrid().getStore().baseParams.Type = 'new';
		this.OutGridPanel.getGrid().getStore().baseParams.Type = 'out';
		this.AllGridPanel.getGrid().getStore().baseParams.Type = 'all';
		this.RecommRouterGridPanel.getGrid().getStore().baseParams.Type = 'rou';
		this.MonitorCenterGridPanel.getGrid().getStore().baseParams.Type = 'mon';

		this.resetFilters();

		this.searchOnTabChange = false;
		this.TabPanel.setActiveTab(3);
		this.TabPanel.setActiveTab(2);
		this.TabPanel.setActiveTab(1);
		this.TabPanel.setActiveTab(0);
		this.searchOnTabChange = true;

		this.chartMenu = [{
			text: 'Гравидограмма',
			disabled: true,
			handler: function() {
				var tabId = win.TabPanel.getActiveTab().getId(),
					gridPanel;
				
				switch(tabId) {
					case 'New': gridPanel = win.NewGridPanel; break;
					case 'All': gridPanel = win.AllGridPanel; break;
					case 'Out': gridPanel = win.OutGridPanel; break;
				}
				
				if (!gridPanel) return false;

				var record = gridPanel.getGrid().getSelectionModel().getSelected();
				if (!record || !record.get('Person_id') || !record.get('PersonRegister_id')) {
					return false;
				}
				
				getWnd('swPregnancyGravidogamExt6').show({
					PersonRegister_id: record.get('PersonRegister_id'),
					Person_id: record.get('Person_id')
				});
			}
		}, {
			text: 'Партограмма',
			disabled: true,
			handler: function() {
				// todo
			}
		}];

		//Добавление кнопок для вкладки "Новые"
		if (!this.NewGridPanel.getAction('action_charts')) {
			this.NewGridPanel.addActions({
				id: this.id + 'action_charts_new',
				hidden: !isDebug(), // todo: когда будет готово - убрать
				name: 'action_charts',
				text: 'Диаграммы',
				menu: this.chartMenu
			});
		}
		if (!this.NewGridPanel.getAction('action_out') && !getWnd('swWorkPlaceMZSpecWindow').isVisible()) {
			this.NewGridPanel.addActions({
				name: 'action_out',
				text: lang['isklyuchit_iz_registra'],
				handler: function() {
					this.doPersonPregnancyOut(this.NewGridPanel);
				}.createDelegate(this)
			});
		}
		if (!this.NewGridPanel.getAction('action_openemk')) {
			this.NewGridPanel.addActions({
				name: 'action_openemk',
				text: lang['otkryit_emk'],
				handler: function() {
					this.openEmk(this.NewGridPanel);
				}.createDelegate(this)
			});
		}
		if (!this.NewGridPanel.getAction('action_opendirectionmaster')) {
			this.NewGridPanel.addActions({
				name:'action_opendirectionmaster',
				tooltip: 'Мастер выписки направлений',
				text: langs('Записать'),
				handler: function() {
					this.masterOpen(this.NewGridPanel)
				}.createDelegate(this),
			});
		}

		//Добавление кнопок для вкладки "Все"
		if (!this.AllGridPanel.getAction('action_charts')) {
			this.AllGridPanel.addActions({
				id: this.id + 'action_charts_all',
				hidden: !isDebug(), // todo: когда будет готово - убрать
				name: 'action_charts',
				text: 'Диаграммы',
				menu: this.chartMenu
			});
		}
		if (!this.AllGridPanel.getAction('action_out') && !getWnd('swWorkPlaceMZSpecWindow').isVisible()) {
			this.AllGridPanel.addActions({
				name: 'action_out',
				text: lang['isklyuchit_iz_registra'],
				handler: function() {
					this.doPersonPregnancyOut(this.AllGridPanel);
				}.createDelegate(this)
			});
		}
		if (!this.AllGridPanel.getAction('action_openemk')) {
			this.AllGridPanel.addActions({
				name: 'action_openemk',
				text: lang['otkryit_emk'],
				handler: function() {
					this.openEmk(this.AllGridPanel);
				}.createDelegate(this)
			});
		}
		if (!this.AllGridPanel.getAction('action_opendirectionmaster')) {
			this.AllGridPanel.addActions({
				name:'action_opendirectionmaster',
				tooltip: 'Мастер выписки направлений',
				text: langs('Записать'),
				handler: function() {
					this.masterOpen(this.AllGridPanel)
				}.createDelegate(this),
			});
		}

		//Добавление кнопок для вкладки "Выбывшие"
		if (!this.OutGridPanel.getAction('action_charts')) {
			this.OutGridPanel.addActions({
				id: this.id + 'action_charts_out',
				hidden: !isDebug(), // todo: когда будет готово - убрать
				name: 'action_charts',
				text: 'Диаграммы',
				menu: this.chartMenu
			});
		}
		if (!this.OutGridPanel.getAction('action_cancel_out')) {
			this.OutGridPanel.addActions({
				name: 'action_cancel_out',
				text: 'Вернуть в регистр',
				handler: function() {
					this.cancelPersonPregnancyOut(this.OutGridPanel);
				}.createDelegate(this)
			});
		}
		if (!this.OutGridPanel.getAction('action_openemk')) {
			this.OutGridPanel.addActions({
				name: 'action_openemk',
				text: lang['otkryit_emk'],
				handler: function() {
					this.openEmk(this.OutGridPanel);
				}.createDelegate(this)
			});
		}
		if (!this.OutGridPanel.getAction('action_opendirectionmaster')) {
			this.OutGridPanel.addActions({
				name:'action_opendirectionmaster',
				tooltip: 'Мастер выписки направлений',
				text: langs('Записать'),
				handler: function() {
					this.masterOpen(this.OutGridPanel)
				}.createDelegate(this),
			});
		}

		//Добавление кнопок для вкладки "Не включенные в регистр"
		if (!this.NotIncludeGridPanel.getAction('action_include')) {
			this.NotIncludeGridPanel.addActions({
				name: 'action_include',
				text: lang['vklyuchit_v_registr'],
				handler: function() {
					this.includePersonPregnancy(this.NotIncludeGridPanel);
				}.createDelegate(this)
			});
		}
		if (!this.NotIncludeGridPanel.getAction('action_openemk')) {
			this.NotIncludeGridPanel.addActions({
				name: 'action_openemk',
				text: lang['otkryit_emk'],
				handler: function() {
					this.openEmk(this.NotIncludeGridPanel);
				}.createDelegate(this)
			});
		}
		if (!this.NotIncludeGridPanel.getAction('action_opendirectionmaster')) {
			this.NotIncludeGridPanel.addActions({
				name:'action_opendirectionmaster',
				tooltip: 'Мастер выписки направлений',
				text: langs('Записать'),
				handler: function() {
					this.masterOpen(this.NotIncludeGridPanel)
				}.createDelegate(this),
			});
		}

		if(!isPregnancyRegisterAccess()){
			this.NewGridPanel.setActionHidden('action_add',true);
			this.NewGridPanel.setActionHidden('action_edit',true);
			this.NewGridPanel.setActionHidden('action_delete',true);
			this.NewGridPanel.setActionHidden('action_out',true);
			this.AllGridPanel.setActionHidden('action_add',true);
			this.AllGridPanel.setActionHidden('action_edit',true);
			this.AllGridPanel.setActionHidden('action_delete',true);
			this.AllGridPanel.setActionHidden('action_out',true);
			this.OutGridPanel.setActionHidden('action_add',true);
			this.OutGridPanel.setActionHidden('action_edit',true);
			this.OutGridPanel.setActionHidden('action_delete',true);
			this.OutGridPanel.setActionHidden('action_cancel_out',true);
			this.NotIncludeGridPanel.setActionHidden('action_include',true);
			this.RecommRouterGridPanel.setActionHidden('action_edit',true);
		}
		else
		{
			this.NewGridPanel.setActionHidden('action_add',false);
			this.NewGridPanel.setActionHidden('action_edit',false);
			this.NewGridPanel.setActionHidden('action_delete',false);
			this.NewGridPanel.setActionHidden('action_out',false);
			this.AllGridPanel.setActionHidden('action_add',false);
			this.AllGridPanel.setActionHidden('action_edit',false);
			this.AllGridPanel.setActionHidden('action_delete',false);
			this.AllGridPanel.setActionHidden('action_out',false);
			this.OutGridPanel.setActionHidden('action_add',false);
			this.OutGridPanel.setActionHidden('action_edit',false);
			this.OutGridPanel.setActionHidden('action_delete',false);
			this.OutGridPanel.setActionHidden('action_cancel_out',false);
			this.NotIncludeGridPanel.setActionHidden('action_include',false);
			this.RecommRouterGridPanel.setActionHidden('action_edit',false);
		}
		this.editType = 'all';
		if(arguments[0] && arguments[0].editType)
		{
			this.editType = arguments[0].editType;
		}
				
		var med_personal_combo = pp_form.findField('MedPersonal_iid');
		if (typeof med_personal_combo != 'undefined'){
			med_personal_combo.getStore().load({
				params: {Lpu_id: getGlobalOptions().lpu_id, All_Rec:1}
			});		
		}
	},
	doSearch: function(clear, tabId) {
		if (!tabId) {
			tabId = this.TabPanel.getActiveTab().getId();
		}
		var gridPanel = this[tabId+'GridPanel'];
		gridPanel.removeAll();
		var filtersPanel = (tabId == 'NotInclude')?this.EvnFiltersPanel:((tabId == 'MonitorCenter')?this.MonitorCenterFiltersPanel:this.PersonPregnancyFiltersPanel);
		var base_form = filtersPanel.getForm();

		if (clear) {
			this.resetFilters(tabId);
		}

		if (!base_form.isValid()) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					filtersPanel.getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return;
		}

		var params = getAllFormFieldValues(filtersPanel);

		if (base_form.findField('ADKS')){
			params.ADKS = base_form.findField('ADKS').getValue() ? 'on' : null;
		}
		
		params.start = 0;
		params.limit = 100;

		gridPanel.getGrid().getStore().load({
			params: params
		});		
	},

	masterOpen: function(gridPanel) {
		var grid = gridPanel.getGrid();
		var record = grid.getSelectionModel().getSelected();
		if (record !== undefined && !Ext.isEmpty(record.get('Person_id'))) {
			getWnd('swDirectionMasterWindow').show({
				personData: record.data
			});
		}
	},

	openEmk: function(gridPanel) {
		var record = gridPanel.getGrid().getSelectionModel().getSelected();
		var userMedStaffFact = this.userMedStaffFact;
		var emk = getWnd('swPersonEmkWindow');
		var that = this;
		if (!record || Ext.isEmpty(record.get('Person_id'))) return;

		if (emk.isVisible()) {
			if (record.get('Person_id') == emk.Person_id) {
				if (record.get('Evn_id')) {
					var sparams = {
						parent_node: emk.Tree.getRootNode(),
						last_child: false,
						disableLoadViewForm: false,
						node_attr_name: 'id',
						node_attr_value: record.get('EvnClass_SysNick') +'_'+ record.get('Evn_id')
					};
					emk.searchNodeInTreeAndLoadViewForm(sparams);
				}
				emk.toFront();
			} else {
				sw.swMsg.alert(lang['soobschenie'], lang['forma_elektronnoy_istorii_bolezni_emk_v_dannyiy_moment_otkryita']);
			}
		} else {
			var searchNodeObj = null;
			if (record.get('Evn_id')) {
				searchNodeObj = {
					parentNodeId: 'root',
					last_child: false,
					disableLoadViewForm: false,
					EvnClass_SysNick: record.get('EvnClass_SysNick'),
					Evn_id: record.get('Evn_id')
				};
			}
			var params = {
				Person_id: record.get('Person_id'),
				searchNodeObj: searchNodeObj,
				userMedStaffFact: userMedStaffFact,
				ARMType: 'common'//userMedStaffFact.ARMType
			};
			if (params.ARMType == 'stac') {
				params.ARMType = 'common';
				params.addStacActions = ['action_New_EvnPS', 'action_StacSvid', 'action_EvnPrescrVK'];

				Ext.Ajax.request({
					url: '/?c=EvnPS&m=beforeOpenEmk',
					params: {Person_id: params.Person_id},
					success: function(response) {
						var answer = Ext.util.JSON.decode(response.responseText);
						if(!Ext.isArray(answer) || !answer[0]) {
							showSysMsg(lang['pri_poluchenii_dannyih_dlya_proverok_proizoshla_oshibka_nepravilnyiy_otvet_servera']);
							return false;
						}
						if (answer[0].countOpenEvnPS > 0) {
							//showSysMsg('Создание новых КВС недоступно','У пациента имеются открытые КВС в даннном ЛПУ! Количество открытых КВС: '+ answer[0].countOpenEvnPS);
							//emk_params.addStacActions = ['action_StacSvid']; //лочить кнопку создания случая лечения, если есть незакрытые КВС в данном ЛПУ #13272
							params.disAddPS = answer[0].countOpenEvnPS;
						}
						params.readOnly = (that.editType=='onlyRegister')?true:false;
						emk.show(params);
					}
				});
			} else {
				params.readOnly = (that.editType=='onlyRegister')?true:false;
				if(that.editType=='onlyRegister')
					params.ARMType = 'common';
				emk.show(params);
			}
		}
	},

	includePersonPregnancy: function(gridPanel) {
		var record = gridPanel.getGrid().getSelectionModel().getSelected();

		if (!record || Ext.isEmpty(record.get('Person_id'))) {
			return false;
		}

		var params = {
			action: 'add',
			Person_id: record.get('Person_id'),
			userMedStaffFact: this.userMedStaffFact,
			callback: function() {
				gridPanel.getAction('action_refresh').execute();
			}
		};

		getWnd('swPersonPregnancyEditWindow').show(params);
	},

	doPersonPregnancyOut: function(gridPanel) {
		var record = gridPanel.getGrid().getSelectionModel().getSelected();

		if (!record || Ext.isEmpty(record.get('Person_id')) ||
			Ext.isEmpty(record.get('PersonRegister_id')) || !Ext.isEmpty(record.get('PersonRegisterOutCause_id'))
		) {
			return false;
		}

		var params = {
			PersonRegister_id: record.get('PersonRegister_id'),
			Person_id: record.get('Person_id'),
			Lpu_did: this.userMedStaffFact?this.userMedStaffFact.Lpu_id:null,
			MedPersonal_did: this.userMedStaffFact?this.userMedStaffFact.MedPersonal_id:null,
			callback: function() {
				gridPanel.getAction('action_refresh').execute();
			}
		};

		getWnd('swPersonPregnancyOutWindow').show(params);
	},

	cancelPersonPregnancyOut: function(gridPanel) {
		var record = gridPanel.getGrid().getSelectionModel().getSelected();

		if (!record || Ext.isEmpty(record.get('PersonRegister_id')) ||
			!record.get('PersonRegisterOutCause_Code').inlist(['2','7'])
		) {
			return false;
		}

		var params = {
			PersonRegister_id: record.get('PersonRegister_id')
		};

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Отмена исключения из регистра..."});
		loadMask.show();

		Ext.Ajax.request({
			params: params,
			url: '/?c=PersonPregnancy&m=cancelPersonPregnancyOut',
			failure: function(response) {
				loadMask.hide();
			},
			success: function() {
				loadMask.hide();
				gridPanel.getAction('action_refresh').execute();
			}
		});
	},

	printCard: function (gridmode,blank) {
		var win = this;
		var record = false;
		if(blank){
        	record_id = 0;
        } else {
        	switch(gridmode){
				case 'new':
				record = win.NewGridPanel.getGrid().getSelectionModel().getSelected();
				break;
				case 'all':
				record = win.AllGridPanel.getGrid().getSelectionModel().getSelected();
				break;
				case 'out':
				record = win.OutGridPanel.getGrid().getSelectionModel().getSelected();
				break;
			}
	        if (!record) {
	            Ext.Msg.alert(lang['oshibka'], 'Не выбрана запись');
	            return false;
	        }
	        var record_id = record.get('PersonRegister_id');
	        if ( !record_id )
	            return false;
        }
		
    	printBirt({
			'Report_FileName': 'han_ParturientCard_f111_u.rptdesign',
			'Report_Params': '&paramPersonRegister_id=' + record_id,
			'Report_Format': 'pdf'
		});
	},
	printAnketa: function(gridmode) {
		var win = this;
		var record = false;

		switch(gridmode){
			case 'new':
				record = win.NewGridPanel.getGrid().getSelectionModel().getSelected();
				break;
			case 'all':
				record = win.AllGridPanel.getGrid().getSelectionModel().getSelected();
				break;
			case 'out':
				record = win.OutGridPanel.getGrid().getSelectionModel().getSelected();
				break;
		}

		if (!record) {
			Ext.Msg.alert(lang['oshibka'], 'Не выбрана запись');
			return false;
		}
		var record_id = record.get('PersonRegister_id');
		if ( !record_id )
			return false;

		printBirt({
			'Report_FileName': 'PregnantProfile.rptdesign',
			'Report_Params': '&paramPersonRegister=' + record_id,
			'Report_Format': 'pdf'
		});
	},
	printScreen: function(PregnancyScreen_id) {
		if ( !PregnancyScreen_id )
			return false;

		printBirt({
			'Report_FileName': 'PregnancyScreen_Print.rptdesign',
			'Report_Params': '&paramPregnancyScreen=' + PregnancyScreen_id,
			'Report_Format': 'pdf'
		});
	},
	refreshPrintScreenMenu: function(gridPanel) {
		var win = this;
		var printScreen = gridPanel.getAction('action_print').menu.printScreen;
		var printScreenMenu = printScreen.items[0].menu;

		printScreen.disable();
		printScreenMenu.removeAll();

		var record = gridPanel.getGrid().getSelectionModel().getSelected();
		if (!record || Ext.isEmpty(record.get('ScreenData'))) {
			return;
		}

		var ScreenData = Ext.util.JSON.decode(record.get('ScreenData'));
		if (Ext.isArray(ScreenData) && ScreenData.length > 0) {
			ScreenData.forEach(function(screen) {
				var PregnancyScreen_id = screen.PregnancyScreen_id;
				printScreenMenu.add(new Ext.Action({
					text: screen.text,
					handler: function() {win.printScreen(PregnancyScreen_id)}
				}));
			});
			printScreen.enable();
		}
	},
	printResult: function(gridmode) {
		var win = this;
		var record = false;

		switch(gridmode){
			case 'new':
				record = win.NewGridPanel.getGrid().getSelectionModel().getSelected();
				break;
			case 'all':
				record = win.AllGridPanel.getGrid().getSelectionModel().getSelected();
				break;
			case 'out':
				record = win.OutGridPanel.getGrid().getSelectionModel().getSelected();
				break;
			case 'rou':
				record = win.RecommRouterGridPanel.getGrid().getSelectionModel().getSelected();				
 				break;
			case 'mon':
				record = win.MonitorCenterGridPanel.getGrid().getSelectionModel().getSelected();
 				break;
		}

		if (!record) {
			Ext.Msg.alert(lang['oshibka'], 'Не выбрана запись');
			return false;
		}
		var record_id = record.get('PersonRegister_id');
		if ( !record_id )
			return false;

		printBirt({
			'Report_FileName': 'PregnancyResult_print.rptdesign',
			'Report_Params': '&paramPersonRegister=' + record_id,
			'Report_Format': 'pdf'
		});
	},
	printFullResult: function(gridmode) {
		var win = this;
		var record = false;

		switch(gridmode){
			case 'new':
				record = win.NewGridPanel.getGrid().getSelectionModel().getSelected();
				break;
			case 'all':
				record = win.AllGridPanel.getGrid().getSelectionModel().getSelected();
				break;
			case 'out':
				record = win.OutGridPanel.getGrid().getSelectionModel().getSelected();
			case 'rou':
				record = win.RecommRouterGridPanel.getGrid().getSelectionModel().getSelected();				
				break;
			case 'mon':
				record = win.MonitorCenterGridPanel.getGrid().getSelectionModel().getSelected();				
				break;
		}

		if (!record) {
			Ext.Msg.alert(langs('Ошибка'), 'Не выбрана запись');
			return false;
		}
		var record_id = record.get('PersonRegister_id');
		if ( !record_id )
			return false;

		printBirt({
			'Report_FileName': 'PregnancyFullResult_print.rptdesign',
			'Report_Params': '&paramPersonRegister=' + record_id,
			'Report_Format': 'pdf'
		});
	},		
	printRegister: function() {
		getWnd('swPersonPregnancyRegisterPrintWindow').show();
	},
	printHightRiskCard: function() {
		getWnd('swHighRiskPregnancyCardPrintWindow').show();
	},
	printHighRiskHosp: function() {
		getWnd('swHighRiskPregnancyHospPrintWindow').show();
	},
	initComponent: function()
	{
		var win = this;

		var stringfields = [
			{name: 'PersonRegister_id', type: 'int', header: 'ID', key: true},
			{name: 'PersonPregnancy_id', hidden: true, type: 'int'},
			{name: 'PersonRegisterOutCause_id', hidden: true, type: 'int'},
			{name: 'PersonRegisterOutCause_Code', hidden: true, type: 'string'},
			{name: 'PersonRegisterOutCause_SysNick', hidden: true, type: 'string'},
			{name: 'Person_id', hidden: true, type: 'int'},
			{name: 'ScreenData', hidden: true, type: 'string'},
			{name: 'PersonQuarantine_IsOn', hidden: true, type: 'int'},
			{name: 'PersonRegister_Code', header: 'Номер индивидуальной карты беременной', type: 'string', width: 80},
			{name: 'Person_Fio', header: 'ФИО', type: 'string', width: 160},
			{name: 'Person_Age', header: 'Возраст', type: 'int', width: 50},
			{name: 'Person_BirthDay', header: 'Д/р', type: 'date', width: 120},
			{name: 'PersonRegister_setDate', header: 'Дата постановки на учет', type: 'date', width: 120},
			{name: 'Lpu_Nick', header: 'МО учета', type: 'string', width: 200},
			{name: 'MedPersonal_Fio', header: 'Врач учета', type: 'string', width: 160},
			{name: 'PersonPregnancy_Period', header: 'Срок', type: 'int', width: 50},
			{name: 'Diag_FullName', header: 'Основной диагноз', type: 'string', width: 200},
			{name: 'RiskType_Name', header: 'Степень риска по Радзинскому', type: 'string', width: 160},
			{name: 'RiskPR', header: 'Риск ПР', type: 'string', width: 100},
			{name: 'Risk572N', header: 'Риск по 572н', type: 'string', width: 100},
			{name: 'RiskGlobal', header: 'Общий риск', type: 'string', width: 100},
			{name: 'PersonPregnancy_ObRisk', header: 'Баллы риска по Радзинскому', type: 'int', width: 180},
			{name: 'PregnancyResult_Name', header: 'Исход беременности', type: 'string', width: 160},
			{name: 'LpuAttach_Nick', header: 'МО прикрепления', type: 'string', width: 200},
			{name: 'LpuHosp_Nick', header: 'МО госпитализации', type: 'string', width: 200},
			{name: 'Person_PAddress', header: 'Адрес проживания', type: 'string', width: 200},
			{name: 'PersonPregnancy_CountKDO', header: 'Кол-во посещений КДО', width: 140, hidden: getRegionNick() != 'perm'},
			{name: 'PRRiskFactor', header: 'Фактор риска ПР', type: 'string', width: 500, hidden:true},
			{name: 'PersonDisp_id', hidden: true, type: 'int'},
			{name: 'RiskFactor572N', header: 'Фактор риска по 572н', type: 'string', width: 400, hidden:true}
		];

		var onRowSelect = function(gridPanel, sm, index, record) {
			var disable = false;
			if (gridPanel.getAction('action_openemk')) {
				disable = (!record || Ext.isEmpty(record.get('Person_id')));
				gridPanel.getAction('action_openemk').setDisabled(disable);
			}
			if (gridPanel.getAction('action_opendirectionmaster')) {
				disable = (!record || Ext.isEmpty(record.get('Person_id')));
				gridPanel.getAction('action_opendirectionmaster').setDisabled(disable);
			}

			if (gridPanel.getAction('action_out')) {
				disable = (
					!record || Ext.isEmpty(record.get('Person_id')) ||
					Ext.isEmpty(record.get('PersonRegister_id')) || !Ext.isEmpty(record.get('PersonRegisterOutCause_id'))
				);
				gridPanel.getAction('action_out').setDisabled(disable);
			}
			if (gridPanel.getAction('action_cancel_out')) {
				disable = (
					!record || Ext.isEmpty(record.get('PersonRegister_id')) ||
					!record.get('PersonRegisterOutCause_Code').inlist(['2','7'])
				);
				gridPanel.getAction('action_cancel_out').setDisabled(disable);
			}

			var isEmptyRecord = (!record || Ext.isEmpty(record.get('PersonRegister_id')));
			var printMenu = gridPanel.getAction('action_print').menu;

			printMenu.printCard.setDisabled(isEmptyRecord);
			printMenu.printAnketa.setDisabled(isEmptyRecord);
			printMenu.printScreen.setDisabled(isEmptyRecord);

			gridPanel.getAction('action_charts').items[0].menu.items.items[0].setDisabled(isEmptyRecord);

			win.refreshPrintScreenMenu(gridPanel);
		};

		this.NewGridPanel = new sw.Promed.ViewFrame({
			actions: [
				{ name: 'action_add', handler: function(){win.openPersonPregnancyEditWindow('add', win.NewGridPanel)} },
				{ name: 'action_edit', handler: function(){win.openPersonPregnancyEditWindow('edit', win.NewGridPanel)} },
				{ name: 'action_view', handler: function(){win.openPersonPregnancyEditWindow('view', win.NewGridPanel)} },
				{ name: 'action_delete', handler: function(){win.deletePersonRegister(win.NewGridPanel)} },
				{ name: 'action_print',
					menuConfig: {
						printCard: { name: 'printCard', text: 'Печать Индивидуальной карты беременной', handler: function(){win.printCard('new');} },
						printCardBlank: { name: 'printCardBlank', text: 'Печать бланка Индивидуальной карты беременной', handler: function(){win.printCard('new',true);} },
						printAnketa: { name: 'printAnketa', text: 'Печать анкеты', handler: function(){win.printAnketa('new')} },
						printScreen: { name: 'printScreen', text: 'Печать скрининга', menu: new Ext.menu.Menu},
						printRegister: { name: 'printRegister', text: 'Печать реестра беременных и родильниц', handler: function(){win.printRegister()}},
						printHighRiskCard: { name: 'printHighRiskCard', text: 'Печать карты отчета о наблюдении за беременными группы высокого риска', handler: function(){win.printHightRiskCard()}},
						printHighRiskHosp: { name: 'printHighRiskHosp', text: 'Печать формы учета случаев госпитализации беременных высокого риска', hidden: (getRegionNick() != 'astra'), handler: function(){win.printHighRiskHosp()}},
						printFullPersonRegister: { name: 'printFullPersonRegister', text: 'Печать формы "Сведения о беременности"', handler: function(){win.printFullResult('new')}}
                    }
                }
			],
			border: false,
			uniqueId: true,
			autoLoadData: false,
			forcePrintMenu: true,
			dataUrl: '/?c=PersonPregnancy&m=loadList',
			pageSize: 100,
			paging: true,
			root: 'data',
			totalProperty: 'totalCount',
			region: 'center',
			stringfields: stringfields,
			onRowSelect: function(sm, index, record) {
				onRowSelect(this.NewGridPanel, sm, index, record);
			}.createDelegate(this)
		});
		
		this.NewGridPanel.getGrid().view = new Ext.grid.GridView({
			getRowClass: function (row, index) {
				var cls = '';

				if (row.get('PersonQuarantine_IsOn') == 2) {
					cls = cls + 'x-grid-rowbackred ';
				}

				return cls;
			}
		});

		this.AllGridPanel = new sw.Promed.ViewFrame({
			actions: [
				{ name: 'action_add', handler: function(){win.openPersonPregnancyEditWindow('add', win.AllGridPanel)} },
				{ name: 'action_edit', handler: function(){win.openPersonPregnancyEditWindow('edit', win.AllGridPanel)} },
				{ name: 'action_view', handler: function(){win.openPersonPregnancyEditWindow('view', win.AllGridPanel)} },
				{ name: 'action_delete', handler: function(){win.deletePersonRegister(win.AllGridPanel)} },
				{ name: 'action_print',
					menuConfig: {
                        printCard: { name: 'printCard', text: 'Печать Индивидуальной карты беременной', hidden: (getRegionNick() == 'kz'), handler: function(){win.printCard('all');} },
                        printCardBlank: { name: 'printCardBlank', text: 'Печать бланка Индивидуальной карты беременной', hidden: (getRegionNick() == 'kz'), handler: function(){win.printCard('all',true);} },
						printAnketa: { name: 'printAnketa', text: 'Печать анкеты', handler: function(){win.printAnketa('all')} },
						printScreen: { name: 'printScreen', text: 'Печать скрининга', menu: new Ext.menu.Menu},
						printRegister: { name: 'printRegister', text: 'Печать реестра беременных и родильниц', handler: function(){win.printRegister()}},
						printHighRiskCard: { name: 'printHighRiskCard', text: 'Печать карты отчета о наблюдении за беременными группы высокого риска', handler: function(){win.printHightRiskCard()}},
						printHighRiskHosp: { name: 'printHighRiskHosp', text: 'Печать формы учета случаев госпитализации беременных высокого риска', hidden: (getRegionNick() != 'astra'), handler: function(){win.printHighRiskHosp()}},
						printFullPersonRegister: { name: 'printFullPersonRegister', text: 'Печать формы "Сведения о беременности"', handler: function(){win.printFullResult('all')}}
                    }
                }
			],
			border: false,
			uniqueId: true,
			autoLoadData: false,
			forcePrintMenu: true,
			dataUrl: '/?c=PersonPregnancy&m=loadList',
			pageSize: 100,
			paging: true,
			root: 'data',
			totalProperty: 'totalCount',
			region: 'center',
			stringfields: stringfields,
			onRowSelect: function(sm, index, record) {
				onRowSelect(this.AllGridPanel, sm, index, record);
			}.createDelegate(this)
		});
		
		this.AllGridPanel.getGrid().view = new Ext.grid.GridView({
			getRowClass: function (row, index) {
				var cls = '';

				if (row.get('PersonQuarantine_IsOn') == 2) {
					cls = cls + 'x-grid-rowbackred ';
				}

				return cls;
			}
		});

		var stringfieldsOutGrid = [
			{name: 'PersonRegister_id', type: 'int', header: 'ID', key: true},
			{name: 'PersonPregnancy_id', hidden: true, type: 'int'},
			{name: 'PersonRegisterOutCause_id', hidden: true, type: 'int'},
			{name: 'PersonRegisterOutCause_Code', hidden: true, type: 'string'},
			{name: 'PersonRegisterOutCause_SysNick', hidden: true, type: 'string'},
			{name: 'Person_id', hidden: true, type: 'int'},
			{name: 'ScreenData', hidden: true, type: 'string'},
			{name: 'PersonRegister_Code', header: 'Номер индивидуальной карты беременной', type: 'string', width: 80},
			{name: 'Person_Fio', header: 'ФИО', type: 'string', width: 160},
			{name: 'Person_Age', header: 'Возраст', type: 'int', width: 50},
			{name: 'Person_BirthDay', header: 'Д/р', type: 'date', width: 120},
			{name: 'PersonRegister_setDate', header: 'Дата постановки на учет', type: 'date', width: 120},
			{name: 'Lpu_Nick', header: 'МО учета', type: 'string', width: 200},
			{name: 'MedPersonal_Fio', header: 'Врач учета', type: 'string', width: 160},
			{name: 'PersonPregnancy_Period', header: 'Срок', type: 'int', width: 50},
			{name: 'Diag_FullName', header: 'Основной диагноз', type: 'string', width: 200},
			{name: 'RiskType_Name', header: 'Степень риска по Радзинскому', type: 'string', width: 160},
			{name: 'RiskPR', header: 'Риск ПР', type: 'string', width: 100},
			{name: 'Risk572N', header: 'Риск по 572н', type: 'string', width: 100},
			{name: 'RiskGlobal', header: 'Общий риск', type: 'string', width: 100},
			{name: 'PersonPregnancy_ObRisk', header: 'Баллы риска по Радзинскому', type: 'int', width: 180},
			{name: 'PregnancyResult_Name', header: 'Исход беременности', type: 'string', width: 160},
			{name: 'LpuAttach_Nick', header: 'МО прикрепления', type: 'string', width: 200},
			{name: 'LpuHosp_Nick', header: 'МО госпитализации', type: 'string', width: 200},
			{name: 'Person_PAddress', header: 'Адрес проживания', type: 'string', width: 200},
			{name: 'PersonPregnancy_CountKDO', header: 'Кол-во посещений КДО', width: 140, hidden: getRegionNick() != 'perm'},
			{name: 'PRRiskFactor', header: 'Фактор риска ПР', type: 'string', width: 500, hidden:true},
			{name: 'PersonDisp_id', hidden: true, type: 'int'},
			{name: 'RiskFactor572N', header: 'Фактор риска по 572н', type: 'string', width: 400, hidden:true}
		];

		this.OutGridPanel = new sw.Promed.ViewFrame({
			actions: [
				{ name: 'action_add', handler: function(){win.openPersonPregnancyEditWindow('add', win.OutGridPanel)} },
				{ name: 'action_edit', handler: function(){win.openPersonPregnancyEditWindow('edit', win.OutGridPanel)} },
				{ name: 'action_view', handler: function(){win.openPersonPregnancyEditWindow('view', win.OutGridPanel)} },
				{ name: 'action_delete', handler: function(){win.deletePersonRegister(win.OutGridPanel)} },
				{ name: 'action_print',
					menuConfig: {
                        printCard: { name: 'printCard', text: 'Печать Индивидуальной карты беременной', hidden: (getRegionNick() == 'kz'), handler: function(){win.printCard('out');} },
                        printCardBlank: { name: 'printCardBlank', text: 'Печать бланка Индивидуальной карты беременной', hidden: (getRegionNick() == 'kz'), handler: function(){win.printCard('out',true);} },
                        printAnketa: { name: 'printAnketa', text: 'Печать анкеты', handler: function(){win.printAnketa('out')} },
						printScreen: { name: 'printScreen', text: 'Печать скрининга', menu: new Ext.menu.Menu},
						printResult: { name: 'printResult', text: 'Печать исхода беременности', handler: function() {win.printResult('out')}},
						printRegister: { name: 'printRegister', text: 'Печать реестра беременных и родильниц', handler: function(){win.printRegister()}},
						printHighRiskCard: { name: 'printHighRiskCard', text: 'Печать карты отчета о наблюдении за беременными группы высокого риска', handler: function(){win.printHightRiskCard()}},
						printHighRiskHosp: { name: 'printHighRiskHosp', text: 'Печать формы учета случаев госпитализации беременных высокого риска', hidden: (getRegionNick() != 'astra'), handler: function(){win.printHighRiskHosp()}}
                    }
                }
			],
			border: false,
			uniqueId: true,
			autoLoadData: false,
			forcePrintMenu: true,
			dataUrl: '/?c=PersonPregnancy&m=loadList',
			pageSize: 100,
			paging: true,
			root: 'data',
			totalProperty: 'totalCount',
			region: 'center',
			stringfields: stringfieldsOutGrid,
			onRowSelect: function(sm, index, record) {
				onRowSelect(this.OutGridPanel, sm, index, record);
			}.createDelegate(this)
		});

		this.RecommRouterGridPanel = new sw.Promed.ViewFrame({
			actions: [
			    { name: 'action_add', hidden: true},
				{ name: 'action_edit', handler: function(){win.openPersonPregnancyEditWindow('edit', win.RecommRouterGridPanel)} },
				{ name: 'action_view', handler: function(){win.openPersonPregnancyEditWindow('view', win.RecommRouterGridPanel)} },
				{ name: 'action_delete', hidden: true},
				{ name: 'action_print',
					menuConfig: {
						printFullPersonRegister: { name: 'printFullPersonRegister', text: 'Печать формы "Сведения о беременности"', handler: function(){win.printFullResult('rou')}}
					}
				}
			],
			border: false,
			uniqueId: true,
			autoLoadData: false,
			forcePrintMenu: true,
			dataUrl: '/?c=PersonPregnancy&m=loadListRecommRouter',
			pageSize: 100,
			paging: true,
			root: 'data',
			totalProperty: 'totalCount',
			region: 'center',
			stringfields: [
				{name: 'PersonRegister_id', type: 'int', header: 'ID', key: true},
				{name: 'PersonPregnancy_id', hidden: true, type: 'int'},
				{name: 'PersonRegisterOutCause_id', hidden: true, type: 'int'},
				{name: 'PersonRegisterOutCause_Code', hidden: true, type: 'string'},
				{name: 'PersonRegisterOutCause_SysNick', hidden: true, type: 'string'},
				{name: 'Person_id', hidden: true, type: 'int'},
				{name: 'ScreenData', hidden: true, type: 'string'},
				{name: 'PersonRegister_Code', header: 'Номер индивидуальной карты беременной', type: 'string', width: 80},
				{name: 'Person_Fio', header: 'ФИО', type: 'string', width: 160},
				{name: 'Person_Age', header: 'Возраст', type: 'int', width: 50},
				{name: 'Person_BirthDay', header: 'Д/р', type: 'date', width: 120},
				{name: 'PersonRegister_setDate', header: 'Дата постановки на учет', type: 'date', width: 120},
				{name: 'Lpu_Nick', header: 'МО учета', type: 'string', width: 200},
				{name: 'MedPersonal_Fio', header: 'Врач учета', type: 'string', width: 160},
				{name: 'PersonPregnancy_Period', header: 'Срок', type: 'int', width: 50},				
				{name: 'Diag_FullName', header: 'Основной диагноз', hidden: true, type: 'string', width: 200},				
				{name: 'PersonPregnancy_ObRisk', header: 'Баллы перинатального риска', type: 'int', width: 80},
				{name: 'RiskType_Name', header: 'Степень риска по Радзинскому', type: 'string', width: 100},
				{name: 'RiskPR', header: 'Риск ПР', type: 'string', width: 100},
				{name: 'Risk572N', header: 'Риск по 572н', type: 'string', width: 100},
				{name: 'RiskGlobal', header: 'Общий риск', type: 'string', width: 100},
				{name: 'PregnancyResult_Name', header: 'Исход беременности', hidden: true, type: 'string', width: 160},
				{name: 'LpuAttach_Nick', header: 'МО прикрепления', hidden: true, type: 'string', width: 200},
				{name: 'LpuHosp_Nick', header: 'МО госпитализации', hidden: true, type: 'string', width: 200},
				{name: 'Person_PAddress', header: 'Адрес проживания', hidden: true, type: 'string', width: 200},
				{name: 'PersonPregnancy_CountKDO', header: 'Кол-во посещений КДО', width: 140, hidden: true},
				{name: 'lstfactorrisk', header: 'Наличие ключевых факторов риска', width: 200},
				{name: 'RiskType_AName', header: 'Степень риска с учетом ключ. факт.', width: 100},
				{name: 'MesLevel_Name', header: 'МО родоразрешения', width: 100},
				{name: 'VK_Date', header: 'Дата явки на ВК', width: 100},
				{name: 'VK', header: 'ВК', width: 100},
				{name: 'MO_hospital', header: 'МО госпитализации', width: 100},
				{name: 'PRRiskFactor', header: 'Фактор риска ПР', type: 'string', width: 500, hidden:true},
				{name: 'PersonDisp_id', hidden: true, type: 'int'},
				{name: 'RiskFactor572N', header: 'Фактор риска по 572н', type: 'string', width: 400, hidden:true}
			],
			onRowSelect: function(sm, index, record) {
				var disable = false;
				if (this.RecommRouterGridPanel.getAction('action_openemk')) {
					disable = (!record || Ext.isEmpty(record.get('Person_id')));
					this.RecommRouterGridPanel.getAction('action_openemk').setDisabled(disable);
				}
				if (this.RecommRouterGridPanel.getAction('action_opendirectionmaster')) {
					disable = (!record || Ext.isEmpty(record.get('Person_id')));
					this.RecommRouterGridPanel.getAction('action_opendirectionmaster').setDisabled(disable);
				}

				var isEmptyRecord = (!record || Ext.isEmpty(record.get('PersonRegister_id')));

			}.createDelegate(this)
		});
		
		this.MonitorCenterGridPanel = new sw.Promed.ViewFrame({
			actions: [
			    { name: 'action_add', hidden: true},
				{ name: 'action_edit', hidden: true},
				{ name: 'action_view', handler: function(){win.openPersonPregnancyEditWindow('view', win.MonitorCenterGridPanel)} },
				{ name: 'action_delete', hidden: true},
				{ name: 'action_print',
					menuConfig: {
						printFullPersonRegister: { name: 'printFullPersonRegister', text: 'Печать формы "Сведения о беременности"', handler: function(){win.printFullResult('rou')}}
					}
				}
			],		
			border: false,
			uniqueId: true,
			autoLoadData: false,
			forcePrintMenu: true,
			dataUrl: '/?c=PersonPregnancy&m=loadListMonitorCenter',
			pageSize: 100,
			paging: true,
			root: 'data',
			id: 'MonitorCenterGridPanel',
			totalProperty: 'totalCount',
			region: 'center',
			stringfields: [
				{name: 'PersonRegister_id', type: 'int', header: 'ID', key: true},
				{name: 'PersonPregnancy_id', hidden: true, type: 'int'},
				{name: 'Person_id', hidden: true, type: 'int'},
				{name: 'ScreenData', hidden: true, type: 'string'},
				{name: 'PersonRegister_Code', header: 'Номер индивидуальной карты беременной', type: 'string', width: 80},
				{name: 'Person_Fio', header: 'ФИО', type: 'string', width: 160},
				{name: 'Person_Age', header: 'Возраст', type: 'int', width: 50},
				{name: 'Person_BirthDay', header: 'Д/р', type: 'date', width: 120},
				//{name: 'PersonRegister_setDate', header: 'Дата постановки на учет', type: 'date', width: 120},
				{name: 'Lpu_Nick', header: 'МО учета', type: 'string', width: 200},
				{name: 'PersonPregnancy_Period', header: 'Срок', type: 'int', width: 50},
				{name: 'LpuAttach_Nick', header: 'МО прикрепления', type: 'string', width: 200},
				{name: 'RiskType_AName', header: 'Степень риска с учетом ключ. факт.', width: 100, renderer: function (v, p, r) {						
					return (r.get('has_highrisk') == 0 && r.get('notopen_highrisk') == 0) ? r.get('RiskType_AName') : '<span style="font-weight: bold;">'+r.get('RiskType_AName')+'</span>';
				}},
				{name: 'HighRisk_setDT', header: 'Дата устан. выскокой степени риска', type: 'string', width: 100, renderer: function (v, p, r) {						
					return (r.get('has_highrisk') == 0 && r.get('notopen_highrisk') == 0) ? r.get('HighRisk_setDT') : '<span style="font-weight: bold;">'+r.get('HighRisk_setDT')+'</span>';
				}},
				{name: 'PersonPregnancy_ObRisk', header: 'Баллы перинатального риска', type: 'int', width: 80, renderer: function (v, p, r) {						
					return (r.get('has_highrisk') == 0 && r.get('notopen_highrisk') == 0) ? r.get('PersonPregnancy_ObRisk') : '<span style="font-weight: bold;">'+r.get('PersonPregnancy_ObRisk')+'</span>';
				}},
				{name: 'lstfactorrisk', header: 'Наличие ключевых факторов риска', width: 200, renderer: function (v, p, r) {						
					return (r.get('has_highrisk') == 0 && r.get('notopen_highrisk') == 0) ? r.get('lstfactorrisk') : '<span style="font-weight: bold;">'+r.get('lstfactorrisk')+'</span>';
				}},
				{name: 'MesLevel_Name', header: 'МО родоразрешения', width: 100},
				//{name: 'LpuHosp_Nick', header: 'МО госпитализации', hidden: true, type: 'string', width: 200},
				{name: 'NickHospital', header: 'МО госпитализации', type: 'string', width: 200, renderer: function (v, p, r) {						
					return (r.get('has_evnps') == 0 && r.get('notopen_evnps') == 0) ? r.get('NickHospital') : '<span style="font-weight: bold;">'+r.get('NickHospital')+'</span>';
				}},
				{name: 'DateHospital', header: 'Дата поступления', type: 'string', width: 100, renderer: function (v, p, r) {						
					return (r.get('has_evnps') == 0 && r.get('notopen_evnps') == 0) ? r.get('DateHospital') : '<span style="font-weight: bold;">'+r.get('DateHospital')+'</span>';
				}},
				{name: 'BaseDiagnozHospital', header: 'Основной диагноз случая лечения', type: 'string', width: 200, renderer: function (v, p, r) {						
					return (r.get('has_evnps') == 0 && r.get('notopen_evnps') == 0) ? r.get('BaseDiagnozHospital') : '<span style="font-weight: bold;">'+r.get('BaseDiagnozHospital')+'</span>';
				}},
				{name: 'ProfilHospital', header: 'Профиль', type: 'string', width: 200},
				{name: 'has_evnps', header: 'имеется КВС открытая', width: 100, hidden: true },
				{name: 'has_highrisk', header: 'имеется ВСР', width: 100, hidden: true },
				{name: 'notopen_evnps', header: 'нет просмотра КВС', width: 100, hidden: true },
				{name: 'notopen_highrisk', header: 'нет просмотра ВСР', width: 100, hidden: true },
				{name: 'PRRiskFactor', header: 'Фактор риска ПР', type: 'string', width: 500, hidden:true},
				{name: 'RiskType_Name', header: 'Степень риска по Радзинскому', type: 'string', width: 160, hidden:true},
				{name: 'RiskPR', header: 'Риск ПР', type: 'string', width: 100},
				{name: 'Risk572N', header: 'Риск по 572н', type: 'string', width: 100},
				{name: 'RiskGlobal', header: 'Общий риск', type: 'string', width: 100},
				{name: 'PersonDisp_id', hidden: true, type: 'int'},
				{name: 'RiskFactor572N', header: 'Фактор риска по 572н', type: 'string', width: 400, hidden:true},
				{name: 'MedPersonal_iid', header: 'Врач учета', width: 100, hidden: true },
				{name: 'Lpu_iid', header: 'МО учета', width: 100, hidden: true },
				{name: 'pmUser_id', header: 'pmUser_id', width: 100, hidden: true },
				{name: 'pmUser_Login', header: 'pmUser_Login', width: 100, hidden: true },
				{name: 'pmUser_Name', header: 'pmUser_Name', width: 100, hidden: true },
				{name: 'GroupPregnancy', header: 'Врач учета', width: 100, hidden: true }
			],
			onRowSelect: function(sm, index, record) {
				var disable = false;
				if (this.MonitorCenterGridPanel.getAction('action_openemk')) {
					disable = (!record || Ext.isEmpty(record.get('Person_id')));
					this.MonitorCenterGridPanel.getAction('action_openemk').setDisabled(disable);
				}
				if (this.MonitorCenterGridPanel.getAction('action_opendirectionmaster')) {
					disable = (!record || Ext.isEmpty(record.get('Person_id')));
					this.MonitorCenterGridPanel.getAction('action_opendirectionmaster').setDisabled(disable);
				}
				var isEmptyRecord = (!record || Ext.isEmpty(record.get('PersonRegister_id')));

			}.createDelegate(this)
		});		
		
		this.MonitorCenterGridPanel.getGrid().view = new Ext.grid.GridView({
			getRowClass: function (row, index) {			 
				if (row.get('notopen_evnps') == 1 || row.get('notopen_highrisk') == 1) {
					return ' x-grid-rowlightpink';
				}
				return '';
			}
		});		
		
		this.NotIncludeGridPanel = new sw.Promed.ViewFrame({
			actions: [
				{ name: 'action_add', hidden: true},
				{ name: 'action_edit', hidden: true},
				{ name: 'action_view', hidden: true},
				{ name: 'action_delete', hidden: true}
			],
			border: false,
			uniqueId: true,
			autoLoadData: false,
			dataUrl: '/?c=PersonPregnancy&m=loadNotIncludeList',
			pageSize: 100,
			paging: true,
			root: 'data',
			totalProperty: 'totalCount',
			region: 'center',
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
				//gaf #106851 29112017
				{name: 'MedPersonal', header: 'Врач', type: 'string', width: 200},
				{name: 'PRRiskFactor', header: 'Фактор риска ПР', type: 'string', width: 500, hidden:true},
				{name: 'RiskType_Name', header: 'Степень риска по Радзинскому', type: 'string', width: 160, hidden:true},
				{name: 'RiskPR', header: 'Риск ПР', type: 'string', width: 100},
				{name: 'Risk572N', header: 'Риск по 572н', type: 'string', width: 100},
				{name: 'RiskGlobal', header: 'Общий риск', type: 'string', width: 100},
				{name: 'RiskFactor572N', header: 'Фактор риска по 572н', type: 'string', width: 400, hidden:true}
			],
			onRowSelect: function(sm, index, record) {
				var isEmptyRecord = (!record || Ext.isEmpty(record.get('Person_id')));

				if (this.NotIncludeGridPanel.getAction('action_openemk')) {
					this.NotIncludeGridPanel.getAction('action_openemk').setDisabled(isEmptyRecord);
				}
				if (this.NotIncludeGridPanel.getAction('action_include')) {
					this.NotIncludeGridPanel.getAction('action_include').setDisabled(isEmptyRecord);
				}
				if (this.NotIncludeGridPanel.getAction('action_opendirectionmaster')) {
					this.NotIncludeGridPanel.getAction('action_opendirectionmaster').setDisabled(isEmptyRecord);
				}
			}.createDelegate(this)
		});
		
		this.NotIncludeGridPanel.getGrid().view = new Ext.grid.GridView({
			getRowClass: function (row, index) {
				var cls = '';

				if (row.get('PersonQuarantine_IsOn') == 2) {
					cls = cls + 'x-grid-rowbackred ';
				}

				return cls;
			}
		});
		
		if (isUserGroup('RegOperPregnRegistry')){
			if (getRegionNick() == 'ufa'){
				tabPanelItems = [{
						id: 'New',
						layout: 'fit',
						title: 'Новые',
						items: [this.NewGridPanel]
					}, {
						id: 'All',
						layout: 'fit',
						title: 'Все',
						items: [this.AllGridPanel]
					}, {
						id: 'Out',
						layout: 'fit',
						title: 'Выбывшие',
						items: [this.OutGridPanel]
					}, {
						id: 'NotInclude',
						layout: 'fit',
						title: 'Не включенные в регистр',
						items: [this.NotIncludeGridPanel]
					}, {
						id: 'RecommRouter',
						layout: 'fit',
						title: 'Рекомендации по маршрутизации беременных женщин',
						items: [this.RecommRouterGridPanel]
					}, {
						id: 'MonitorCenter',
						layout: 'fit',
						title: 'Центр мониторинга',
						items: [this.MonitorCenterGridPanel]
					}];
			}else{
				tabPanelItems = [{
					id: 'New',
					layout: 'fit',
					title: 'Новые',
					items: [this.NewGridPanel]
				}, {
					id: 'All',
					layout: 'fit',
					title: 'Все',
					items: [this.AllGridPanel]
				}, {
					id: 'Out',
					layout: 'fit',
					title: 'Выбывшие',
					items: [this.OutGridPanel]
				}, {
					id: 'NotInclude',
					layout: 'fit',
					title: 'Не включенные в регистр',
					items: [this.NotIncludeGridPanel]
				}, {
					id: 'RecommRouter',
					layout: 'fit',
					title: 'Рекомендации по маршрутизации беременных женщин',
					items: [this.RecommRouterGridPanel]				
				}];
			}
		}else{
			tabPanelItems = [{
					id: 'New',
					layout: 'fit',
					title: 'Новые',
					items: [this.NewGridPanel]
				}, {
					id: 'All',
					layout: 'fit',
					title: 'Все',
					items: [this.AllGridPanel]
				}, {
					id: 'Out',
					layout: 'fit',
					title: 'Выбывшие',
					items: [this.OutGridPanel]
				}, {
					id: 'NotInclude',
					layout: 'fit',
					title: 'Не включенные в регистр',
					items: [this.NotIncludeGridPanel]
				}, {
					id: 'RecommRouter',
					layout: 'fit',
					title: 'Рекомендации по маршрутизации беременных женщин',
					items: [this.RecommRouterGridPanel]				
				}];			
		}
		
		this.TabPanel = new Ext.TabPanel({
			border: true,
			activeTab: 0,
			id: 'PPW_TabPanel',
			region: 'center',
			items: tabPanelItems,
			listeners:
			{
				tabchange: function(tab, panel) {

					var pp_form = this.PersonPregnancyFiltersPanel.getForm();
					var evn_form = this.EvnFiltersPanel.getForm();
					var mon_form = this.MonitorCenterFiltersPanel.getForm();

					if (pp_form.items.getCount() == 0 || evn_form.items.getCount() == 0) {
						return;
					}

					var date = new Date().format('d.m.Y');
					var date_range = date+' - '+date;

					var setDiagCode = function(field, code) {
						field.getStore().load({
							callback: function() {
								field.setValue(code);
								field.fireEvent('change', field, field.getValue());
							},
							params: {where: "where DiagLevel_id = 4 and Diag_Code = '" + code + "'"}
						});
					};

					var func = function(form, values) {
						var old_values = form.getValues();
						for(name in values) {
							var field = form.findField(name);
							switch(name) {
								case 'Diag_Code_From':
								case 'Diag_Code_To':
									if (old_values[name] != values[name]) {
										setDiagCode(field, values[name]);
									}
									break;
								default:
									if (old_values[name] != values[name]) {
										field.setValue(values[name]);
										field.fireEvent('change', field, field.getValue());
									}
							}
						}
					};

					if (panel.getId() == 'NotInclude') {
						this.EvnFiltersPanel.show();
						this.PersonPregnancyFiltersPanel.hide();
						this.MonitorCenterFiltersPanel.hide();

						var pp_values = getAllFormFieldValues(this.MonitorCenterFiltersPanel);
						var values = {
							Person_SurName: pp_values.Person_SurName,
							Person_FirName: pp_values.Person_FirName,
							Person_SecName: pp_values.Person_SecName,
							Diag_Code_From: pp_values.Diag_Code_From,
							Diag_Code_To: pp_values.Diag_Code_To,
							//Lpu_oid: pp_values.Lpu_iid,
							Evn_setDateRange: date_range
						};

						//func(mon_form, values);
					} else if (panel.getId() == 'MonitorCenter') {
						this.PersonPregnancyFiltersPanel.hide();
						this.EvnFiltersPanel.hide();
						this.MonitorCenterFiltersPanel.show();
								
					} else {
						this.PersonPregnancyFiltersPanel.show();
						this.EvnFiltersPanel.hide();
						this.MonitorCenterFiltersPanel.hide();

						var evn_values = getAllFormFieldValues(this.EvnFiltersPanel);
						var values = {
							Person_SurName: evn_values.Person_SurName,
							Person_FirName: evn_values.Person_FirName,
							Person_SecName: evn_values.Person_SecName,
							Diag_Code_From: evn_values.Diag_Code_From,
							Diag_Code_To: evn_values.Diag_Code_To,
						};

						func(pp_form, values);

						if (getRegionNick() == 'khak'){
							pp_form.findField('PregnancyType_id').showContainer();
						}else{
							pp_form.findField('PregnancyType_id').hideContainer();
						}

						if (panel.getId() == 'Out') {
							pp_form.findField('PersonRegister_disDateRange').showContainer();
							pp_form.findField('PregnancyResult_id').showContainer();

							pp_form.findField('PersonRegister_setDateRange').setValue(null);
							pp_form.findField('PersonRegister_disDateRange').setValue(date_range);
							pp_form.findField('RiskType_gid').hideContainer();
						} else {
							if (panel.getId() == 'All') {
								pp_form.findField('PersonRegister_setDateRange').setValue(null);
							} else {
								pp_form.findField('PersonRegister_setDateRange').setValue(date_range);
							}
							pp_form.findField('PersonRegister_disDateRange').setValue(null);
							pp_form.findField('PregnancyResult_id').setValue(null);

							pp_form.findField('PersonRegister_disDateRange').hideContainer();
							pp_form.findField('PregnancyResult_id').hideContainer();
							pp_form.findField('RiskType_gid').hideContainer();

						}
						if (panel.getId() == 'RecommRouter') {
							
							pp_form.findField('MesLevel_id').showContainer();														
							pp_form.findField('YesNo_id').showContainer();
							pp_form.findField('PregnancyResult_id').showContainer();
							pp_form.findField('PersonRegister_disDateRange').showContainer();							
							pp_form.findField('ADKS').showContainer();
							pp_form.findField('RiskType_gid').showContainer();
							var oob = pp_form.findField('MesLevel_id');																					
							
							pp_form.findField('RiskType_id').setFieldLabel("Степень риска с учетом ключ. факт.");

							var item_3 = oob.store.getAt(3);
							var item_4 = oob.store.getAt(4);							
							oob.store.remove(item_3);
							oob.store.remove(item_4);

							if (typeof item_3 != 'undefined'){
								item_3.data.MesLevel_Name = "МПЦ";	
								oob.store.add(item_3);								
							}

						}else{
							pp_form.findField('MesLevel_id').hideContainer();
							pp_form.findField('YesNo_id').hideContainer();
							pp_form.findField('ADKS').hideContainer();
							pp_form.findField('RiskType_gid').hideContainer();
							
							if (panel.getId() != 'Out'){
								pp_form.findField('PregnancyResult_id').hideContainer();
								pp_form.findField('PersonRegister_disDateRange').hideContainer();							
							}							
							pp_form.findField('RiskType_id').setFieldLabel("Степень риска по Радзинскому");
						}						
					}

					this.doLayout();
					
					if (panel.id == "RecommRouter"){						
						pp_form.findField('YesNo_id').setValue(2);
						if (!this.RecommRouterGridPanel.getAction('action_openemk')) {
							this.RecommRouterGridPanel.addActions({
								name: 'action_openemk',
								text: langs('Открыть ЭМК'),
								handler: function() {
									this.openEmk(this.RecommRouterGridPanel);
								}.createDelegate(this)
							});
						}
						if (!this.RecommRouterGridPanel.getAction('action_opendirectionmaster')) {
							this.RecommRouterGridPanel.addActions({
								name:'action_opendirectionmaster',
								tooltip: 'Мастер выписки направлений',
								text: langs('Записать'),
								handler: function() {
									this.masterOpen(this.RecommRouterGridPanel)
								}.createDelegate(this),
							});
						}
					}
					
					if (panel.id == "MonitorCenter"){						
						if (!this.MonitorCenterGridPanel.getAction('action_openemk')) {
							this.MonitorCenterGridPanel.addActions({
								name: 'action_openemk',
								text: langs('Открыть ЭМК'),
								handler: function() {
									this.openEmk(this.MonitorCenterGridPanel);
								}.createDelegate(this)
							});
						}
						if (!this.MonitorCenterGridPanel.getAction('action_opendirectionmaster')) {
							this.MonitorCenterGridPanel.addActions({
								name:'action_opendirectionmaster',
								tooltip: 'Мастер выписки направлений',
								text: langs('Записать'),
								handler: function() {
									this.masterOpen(this.MonitorCenterGridPanel)
								}.createDelegate(this),
							});
						}
						if (!this.MonitorCenterGridPanel.getAction('action_notice')) {
							this.MonitorCenterGridPanel.addActions({
								name: 'action_notice',
								text: 'Создать сообщение',
								handler: function() {
									var record = this.MonitorCenterGridPanel.getGrid().getSelectionModel().getSelected();
									if (record){
										var params = {
											Message_Subject: 'Регистр беременных',
											ToUser: record.get('MedPersonal_iid'),
											Message_Text: record.get('Person_Fio') + '<br><a href="#" onclick="getWnd(\'swPersonEmkWindow\').show({\'ARMType\': \'common\',\'Person_id\': \''+record.get('Person_id')+'\',\'readOnly\': \'false\'});">Открыть ЭМК</a><br><a href="#" onclick="getWnd(\'swPersonPregnancyEditWindow\').show({\'PersonRegister_id\': \''+record.get('PersonRegister_id')+'\', \'Person_id\': \''+record.get('Person_id')+'\', \'action\': \'edit\', \'editType\': \'all\'});">Открыть Регистр беременных</a>',
											pmUser_id: record.get('pmUser_id'),
											pmUser_Login: record.get('pmUser_Name') + ' (' + record.get('pmUser_Login') +')',
											PersonRegister_id: record.get('PersonRegister_id'),
											GroupPregnancy: record.get('GroupPregnancy'),
										};

										getWnd('swMessagesViewWindow').show({
											mode: 'newMessage',
											params: params
										});

									}else{
										sw.swMsg.show({
											buttons: Ext.Msg.OK,
											fn: Ext.emptyFn,
											icon: Ext.Msg.WARNING,
											msg: 'Выберите запись в таблице \'Центр мониторинга\'',
											title: ERR_WND_TIT
										});
									}
								}.createDelegate(this)
							});
						}
					}
					
					/*if (this.searchOnTabChange) {
						this.doSearch(false, panel.getId());
					}*/
				}.createDelegate(this)
			}
		});
		
		this.EvnFiltersPanel = new Ext.form.FormPanel({
			frame: true,
			autoHeight: true,
			border: true,
			labelAlign: 'right',
			items: [{
				labelWidth: 110,
				layout: 'column',
				items: [{
					layout: 'form',
					items: [{
						xtype: 'textfield',
						name: 'Person_SurName',
						fieldLabel: 'Фамилия',
						width: 180
					}, {
						xtype: 'textfield',
						name: 'Person_FirName',
						fieldLabel: 'Имя',
						width: 180
					}, {
						xtype: 'textfield',
						name: 'Person_SecName',
						fieldLabel: 'Отчество',
						width: 180
					}]
				}, {
					layout: 'form',
					labelWidth: 125,
					items: [{
						xtype: 'swdiagcombo',
						hiddenName: 'Diag_Code_From',
						valueField: 'Diag_Code',
						fieldLabel: 'Основной диагноз с',
						width: 200
					}, {
						xtype: 'swdiagcombo',
						hiddenName: 'Diag_Code_To',
						valueField: 'Diag_Code',
						fieldLabel: 'по',
						width: 200
					},{           
						//gaf #106851 29112017
						xtype: 'swcommonsprcombo',
						comboSubject: 'EvnType',
						hiddenName: 'EvnType_id',
						fieldLabel: 'Тип случая',
						width: 210                                                
					}]
				}, {
					layout: 'form',
					items: [{
						xtype: 'swlpucombo',
						hiddenName: 'Lpu_oid',
						fieldLabel: 'МО',
						//gaf #106851 29112017
						listeners: {
							'change': function(combo, newValue, oldValue) {
								var base_form = this.EvnFiltersPanel.getForm();

								var med_personal_combo = base_form.findField('MedPersonal_iidd');

								if (Ext.isEmpty(newValue) || newValue == -1) {
									med_personal_combo.setValue(null);
									med_personal_combo.getStore().removeAll();
								} else {
									med_personal_combo.getStore().load({
										params: {Lpu_id: newValue}
									});
								}
							}.createDelegate(this)
						},       						
						width: 210
					}, {
						//gaf #106851 29112017
                        allowBlank: true,
						xtype: 'swmedpersonalcombo',
						hiddenName: 'MedPersonal_iidd',
						fieldLabel: 'Врач',
						width: 210,
						listWidth: 300                                            
					},{						
						xtype: 'daterangefield',
						plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
						name: 'Evn_setDateRange',
						fieldLabel: 'Период',
						width: 210
					}, {
						layout: 'form',
						items: [{
							xtype: 'swrisktypecombo',
							hiddenName: 'RiskType_cid',
							fieldLabel: 'Риск по 572н',
							width: 210,
							onLoadStore: function() {
								this.lastQuery = '';
								this.getStore().clearFilter();
								this.getStore().filterBy(function(rec) {
									return rec.get('RiskType_IsRisk572') == 2;
								});
							}
						}]	
					}]
				}, {
					labelWidth: 200,
					layout: 'form',
					items: [{
						xtype: 'swrisktypecombo',
						hiddenName: 'RiskType_did',
						fieldLabel: 'Риск по ПР',
						width: 100,
						onLoadStore: function() {
							this.lastQuery = '';
							this.getStore().clearFilter();
							this.getStore().filterBy(function(rec) {
								return rec.get('RiskType_IsRiskPR') == 2;
							});
						}
					},{
						xtype: 'swrisktypecombo',
						hiddenName: 'RiskType_fid',
						fieldLabel: 'Общий риск',
						width: 100,
						onLoadStore: function() {
							this.lastQuery = '';
							this.getStore().clearFilter();
							this.getStore().filterBy(function(rec) {
								return rec.get('RiskType_IsRiskTotal') == 2;
							});
						}
					},{
						layout: 'form',
						hidden: getRegionNick() != 'ufa',
						border: false,
						items: [{
							xtype: 'swcommonsprcombo',
							comboSubject: 'ObstetricComplication',
							hiddenName: 'ObstetricComplication_id',
							fieldLabel: 'Акушерское осложнение',
							width: 240
						}]
					}]
				}]
			}],
			keys: [{
				fn: function() {
					win.doSearch();
				},
				key: Ext.EventObject.ENTER,
				stopEvent: true
			}]
		});
		
		this.MonitorCenterFiltersPanel = new Ext.form.FormPanel({
			frame: true,
			autoHeight: true,
			border: true,
			labelAlign: 'right',
			items: [{
				labelWidth: 80,
				layout: 'column',
				items: [{
					layout: 'form',
					items: [{
						xtype: 'textfield',
						name: 'Person_SurName',
						fieldLabel: 'Фамилия',
						width: 180
					}, {
						xtype: 'textfield',
						name: 'Person_FirName',
						fieldLabel: 'Имя',
						width: 180
					}, {
						xtype: 'textfield',
						name: 'Person_SecName',
						fieldLabel: 'Отчество',
						width: 180
					}, {
						xtype: 'textfield',
						name: 'PersonRegister_Code',
						fieldLabel: 'Номер карты',
						width: 180
					}, {
						layout: 'column',
						items: [{
							layout: 'form',
							items: [{
								xtype: 'numberfield',
								name: 'Person_AgeFrom',
								fieldLabel: 'Возраст с',
								width: 75
							}]
						}, {
							layout: 'form',
							labelWidth: 25,
							items: [{
								xtype: 'numberfield',
								name: 'Person_AgeTo',
								fieldLabel: 'по',
								width: 75
							}]
						}]
					}]
				}, {
					layout: 'form',
					labelWidth: 145,
					items: [{
						xtype: 'swdiagcombo',
						hiddenName: 'Diag_Code_From',
						valueField: 'Diag_Code',
						fieldLabel: 'Основной диагноз с',
						width: 200
					}, {
						xtype: 'swdiagcombo',
						hiddenName: 'Diag_Code_To',
						valueField: 'Diag_Code',
						fieldLabel: 'по',
						width: 200
					}, {	
						xtype: 'swlpucombo',
						hiddenName: 'Lpu_oid',
						fieldLabel: 'МО госпитализации',
						width: 200						
					}, {
						xtype: 'daterangefield',
						plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
						name: 'EvnPS_setDate',
						fieldLabel: 'Дата поступления',
						width: 200
					}, {						
						layout: 'column',
						id: 'PPW_PeriodFields',
						items: [{
							layout: 'form',
							items: [{
								xtype: 'numberfield',
								name: 'PersonPregnancy_PeriodFrom',
								fieldLabel: 'Срок, нед. с',
								width: 85
							}]
						}, {
							layout: 'form',
							labelWidth: 25,
							items: [{
								xtype: 'numberfield',
								name: 'PersonPregnancy_PeriodTo',
								fieldLabel: 'по',
								width: 85
							}]
						}]
					}]
				}, {
					labelWidth: 200,
					layout: 'form',
					items: [{
						xtype: 'swlpucombo',
						hiddenName: 'Lpu_iid',
						fieldLabel: 'МО постановки на учет',
						additionalRecord: {value: -1, text: 'Не заполнено'},
						listeners: {
							'change': function (combo, newValue, oldValue) {
								var base_form = this.PersonPregnancyFiltersPanel.getForm();

								var med_personal_combo = base_form.findField('MedPersonal_iid');

								if (Ext.isEmpty(newValue) || newValue == -1) {
									med_personal_combo.setValue(null);
									med_personal_combo.getStore().removeAll();
								} else {
									med_personal_combo.getStore().load({
										params: {Lpu_id: newValue, All_Rec: 1}
									});
								}
							}.createDelegate(this)
						},
						width: 210
					}, {
						xtype: 'daterangefield',
						plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
						name: 'PersonRegister_setDateRange',
						fieldLabel: 'Дата постановки на учет',
						width: 210
					}, {
						xtype: 'swrisktypecombo',
						hiddenName: 'RiskType_id',
						fieldLabel: 'Степень риска',
						width: 210,
						onLoadStore: function() {
							this.lastQuery = '';
							this.getStore().clearFilter();
							this.getStore().filterBy(function(rec) {
								return rec.get('RiskType_IsRadzin') == 2;
							});
						}
					}, {
						xtype: 'daterangefield',
						plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
						name: 'HighRisk_setDT',
						fieldLabel: 'Дата высокой степени риска',
						width: 210
					}, {
						xtype: 'swcommonsprcombo',
						comboSubject: 'MesLevel',
						hiddenName: 'MesLevel_id',
						fieldLabel: 'МО родоразрешения',
						width: 210
					}]
				}, {
					labelWidth: 200,
					layout: 'form',
					hidden: getRegionNick() != 'ufa',
					items: [{
						xtype: 'swcommonsprcombo',
						comboSubject: 'ObstetricComplication',
						hiddenName: 'ObstetricComplication_id',
						fieldLabel: 'Акушерское осложнение',
						width: 240
					}]
				}, {
					labelWidth: 200,
					layout: 'form',
					items: [{
						xtype: 'swrisktypecombo',
						hiddenName: 'RiskType_fid',
						fieldLabel: 'Общий риск',
						width: 100,
						onLoadStore: function() {
							this.lastQuery = '';
							this.getStore().clearFilter();
							this.getStore().filterBy(function(rec) {
								return rec.get('RiskType_IsRiskTotal') == 2;
							});
						}
					}]
				}]
			}],
			keys: [{
				fn: function() {
					win.doSearch();
				},
				key: Ext.EventObject.ENTER,
				stopEvent: true
			}]
		});

		this.PersonPregnancyFiltersPanel = new Ext.form.FormPanel({
			frame: true,
			autoHeight: true,
			border: true,
			labelAlign: 'right',
			items: [{
				labelWidth: 80,
				layout: 'column',
				items: [{
					layout: 'form',
					items: [{
						xtype: 'textfield',
						name: 'Person_SurName',
						fieldLabel: 'Фамилия',
						width: 180
					}, {
						xtype: 'textfield',
						name: 'Person_FirName',
						fieldLabel: 'Имя',
						width: 180
					}, {
						xtype: 'textfield',
						name: 'Person_SecName',
						fieldLabel: 'Отчество',
						width: 180
					}, {
						xtype: 'textfield',
						name: 'PersonRegister_Code',
						fieldLabel: 'Номер карты',
						width: 180
					}, {
						layout: 'column',
						items: [{
							layout: 'form',
							items: [{
								xtype: 'numberfield',
								name: 'Person_AgeFrom',
								fieldLabel: 'Возраст с',
								width: 75
							}]
						}, {
							layout: 'form',
							labelWidth: 25,
							items: [{
								xtype: 'numberfield',
								name: 'Person_AgeTo',
								fieldLabel: 'по',
								width: 75
							}]
						}]
					}]
				}, {
					layout: 'form',
					labelWidth: 140,
					items: [{
						xtype: 'swdiagcombo',
						hiddenName: 'Diag_Code_From',
						valueField: 'Diag_Code',
						fieldLabel: 'Основной диагноз с',
						width: 200
					}, {
						xtype: 'swdiagcombo',
						hiddenName: 'Diag_Code_To',
						valueField: 'Diag_Code',
						fieldLabel: 'по',
						width: 200
					}, {
						xtype: 'swdiagcombo',
						hiddenName: 'Diag_sCode_From',
						valueField: 'Diag_Code',
						fieldLabel: 'Соп. диагноз с',
						width: 200
					}, {
						xtype: 'swdiagcombo',
						hiddenName: 'Diag_sCode_To',
						valueField: 'Diag_Code',
						fieldLabel: 'по',
						width: 200
					}, {
						layout: 'column',
						id: 'PPW_PeriodFields',
						items: [{
							layout: 'form',
							items: [{
								xtype: 'numberfield',
								name: 'PersonPregnancy_PeriodFrom',
								fieldLabel: 'Срок, нед. с',
								width: 85
							}]
						}, {
							layout: 'form',
							labelWidth: 25,
							items: [{
								xtype: 'numberfield',
								name: 'PersonPregnancy_PeriodTo',
								fieldLabel: 'по',
								width: 85
							}]
						}]
					}, {
						xtype: 'swcheckbox',
						name: 'ADKS',
						fieldLabel: 'Госпитализация по согласованию с АДКЦ',
						width: 100
					}]
				}, {
					labelWidth: 230,
					layout: 'form',
					items: [{
						xtype: 'swlpucombo',
						hiddenName: 'Lpu_iid',
						fieldLabel: 'МО постановки на учет',
						additionalRecord: {value: -1, text: 'Не заполнено'},
						listeners: {
							'change': function(combo, newValue, oldValue) {
								var base_form = this.PersonPregnancyFiltersPanel.getForm();

								var med_personal_combo = base_form.findField('MedPersonal_iid');

								if (Ext.isEmpty(newValue) || newValue == -1) {
									med_personal_combo.setValue(null);
									med_personal_combo.getStore().removeAll();
								} else {
									med_personal_combo.getStore().load({
										params: {Lpu_id: newValue, All_Rec:1}
									});
								}
							}.createDelegate(this)
						},
						width: 210
					}, {
						allowBlank: true,
						xtype: 'swmedpersonalcombo',
						hiddenName: 'MedPersonal_iid',
						fieldLabel: 'Врач',
						width: 210,
						listWidth: 300
					}, {
						xtype: 'daterangefield',
						plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
						name: 'PersonRegister_setDateRange',
						fieldLabel: 'Дата постановки на учет',
						width: 210
					}, {
						xtype: 'swrisktypecombo',
						hiddenName: 'RiskType_id',
						fieldLabel: 'Степень риска',
						width: 210,
						onLoadStore: function() {
							this.lastQuery = '';
							this.getStore().clearFilter();
							this.getStore().filterBy(function(rec) {
								return rec.get('RiskType_IsRadzin') == 2;
							});
						}
					}, {
						xtype: 'swyesnocombo',
						hiddenName: 'PersonPregnancy_IsKDO',
						fieldLabel: 'Посещения КДО',
						width: 210
					}, {
						xtype: 'swcommonsprcombo',
						comboSubject: 'MesLevel',
						hiddenName: 'MesLevel_id',
						fieldLabel: 'МО родоразрешения',
						width: 210		
					}, {
						xtype: 'swrisktypecombo',
						hiddenName: 'RiskType_bid',
						fieldLabel: 'Риск по 572н',
						width: 210,
						onLoadStore: function() {
							this.lastQuery = '';
							this.getStore().clearFilter();
							this.getStore().filterBy(function(rec) {
								return rec.get('RiskType_IsRisk572') == 2;
							});
						}
					}]
				}, {
					labelWidth: 260,
					layout: 'form',
					id: 'PPW_OutFields',
					items: [{
						xtype: 'daterangefield',
						plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
						name: 'PersonRegister_disDateRange',
						fieldLabel: 'Дата исхода',
						width: 210
					}, {
						xtype: 'swcommonsprcombo',
						comboSubject: 'PregnancyResult',
						hiddenName: 'PregnancyResult_id',
						fieldLabel: 'Исход',
						width: 210
					}, {
						xtype: 'swcommonsprcombo',
						comboSubject: 'PregnancyType',
						hiddenName: 'PregnancyType_id',
						fieldLabel: 'Вид исхода',
						width: 210
					}, {
						xtype: 'swcommonsprcombo',
						comboSubject: 'YesNo',
						hiddenName: 'YesNo_id',
						fieldLabel: 'Состоящие на учете',
						width: 100
					}, {
						xtype: 'swcheckbox',
						name: 'ADKS',
						fieldLabel: 'Госпитализация по согласованию с АДКЦ',
						width: 100
					}, {
						xtype: 'swrisktypecombo',
						hiddenName: 'RiskType_did',
						fieldLabel: 'Риск по ПР',
						width: 100,
						onLoadStore: function() {
							this.lastQuery = '';
							this.getStore().clearFilter();
							this.getStore().filterBy(function(rec) {
								return rec.get('RiskType_IsRiskPR') == 2;
							});
						}
					}, {
						xtype: 'swrisktypecombo',
						hiddenName: 'RiskType_gid',
						fieldLabel: 'Степень риска по Радзинскому',
						width: 210,
						onLoadStore: function() {
							this.lastQuery = '';
							this.getStore().clearFilter();
							this.getStore().filterBy(function(rec) {
								return rec.get('RiskType_IsRadzin') == 2;
							});
						}
					},{
						layout: 'form',
						hidden: getRegionNick() != 'ufa',
						border: false,
						items: [{
							xtype: 'swcommonsprcombo',
							comboSubject: 'ObstetricComplication',
							hiddenName: 'ObstetricComplication_id',
							fieldLabel: 'Акушерское осложнение',
							width: 240
						}]
					}, {
						xtype: 'swrisktypecombo',
						hiddenName: 'RiskType_fid',
						fieldLabel: 'Общий риск',
						width: 210,
						onLoadStore: function() {
							this.lastQuery = '';
							this.getStore().clearFilter();
							this.getStore().filterBy(function(rec) {
								return rec.get('RiskType_IsRiskTotal') == 2;
							});
						}
					}]
				}]
			}],
			keys: [{
				fn: function() {
					win.doSearch();
				},
				key: Ext.EventObject.ENTER,
				stopEvent: true
			}]
		});

		Ext.apply(this,{
			items: [
				{
					region: 'north',
					layout: 'form',
					border: false,
					autoHeight: true,
					items: [
						this.PersonPregnancyFiltersPanel,
						this.EvnFiltersPanel,
						this.MonitorCenterFiltersPanel
					]
				},
				this.TabPanel
			]
		});
		sw.Promed.swPersonPregnancyWindow.superclass.initComponent.apply(this, arguments);
	}
});