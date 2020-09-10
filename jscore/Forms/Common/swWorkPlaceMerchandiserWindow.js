/**
* АРМ товароведа
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


sw.Promed.swWorkPlaceMerchandiserWindow = Ext.extend(sw.Promed.swWorkPlaceWindow, {
	id: 'swWorkPlaceMerchandiserWindow',
	isSmpMainStorage: false,
	isSmpSubStorage: false,
	
    executeRequest: function() {
        var wnd = this;
        var request_grid = this.findById('wpmwWorkPlaceGridRequestPanel');
        var selected_record = request_grid.getGrid().getSelectionModel().getSelected();

        if (selected_record.get('WhsDocumentUc_id') > 0 && confirm(langs('После исполнения, редактирование документа станет недоступно. Продолжить?'))) {
            Ext.Ajax.request({
                params: {
                    WhsDocumentUc_id: selected_record.get('WhsDocumentUc_id'),
                    MedService_id: wnd.userMedStaffFact.MedService_id
                },
                url: '/?c=WhsDocumentUc&m=executeWhsDocumentSpecificity',
                callback: function(options, success, response) {
                    if (response.responseText != '') {
                        var response_obj = Ext.util.JSON.decode(response.responseText);
                        if (response_obj.success && !Ext.isEmpty(response_obj.DocumentUc_id)) {
                            //открытие на редактирование нового документа учета
                            getWnd(wnd.DocumentUcEditWindow).show({
                                action: 'edit',
                                DocumentUc_id: response_obj.DocumentUc_id,
                                DrugDocumentType_Code: response_obj.DrugDocumentType_Code
                            });
                        } else {
                            sw.swMsg.alert('Ошибка', response_obj.Error_Msg && response_obj.Error_Msg != '' ? response_obj.Error_Msg : 'При исполнении документа возникла ошибка');
                        }
                        request_grid.refreshRecords(null,0);
                    }
                }
            });
        }

        return false;
    },
	deleteRequest: function() {
		var wnd = this;
		var request_grid = this.findById('wpmwWorkPlaceGridRequestPanel');
		var selected_record = request_grid.getGrid().getSelectionModel().getSelected();

		if (selected_record.get('WhsDocumentUc_id') > 0 && confirm('Вы хотите удалить заявку?')) {
			Ext.Ajax.request({
				params: {
					WhsDocumentUc_id: selected_record.get('WhsDocumentUc_id')
				},
				url: '/?c=WhsDocumentUc&m=deleteWhsDocumentSpecificity',
				callback: function(options, success, response) {
					if (response.responseText != '') {
						var response_obj = Ext.util.JSON.decode(response.responseText);
						if (!response_obj.success) {
							sw.swMsg.alert('Ошибка', response_obj.Error_Msg && response_obj.Error_Msg != '' ? response_obj.Error_Msg : 'При удалении документа возникла ошибка');
						}
						request_grid.refreshRecords(null,0);
					}
				}
			});
		}

		return false;
	},
    cancelRequest: function() {
        var wnd = this;
        var request_grid = this.findById('wpmwWorkPlaceGridRequestPanel');
        var selected_record = request_grid.getGrid().getSelectionModel().getSelected();

        if (selected_record.get('WhsDocumentUc_id') > 0 && confirm('Вы хотите отменить заявку?')) {
            Ext.Ajax.request({
                params: {
                    WhsDocumentUc_id: selected_record.get('WhsDocumentUc_id')
                },
                url: '/?c=WhsDocumentUc&m=cancelWhsDocumentSpecificity',
                callback: function(options, success, response) {
                    if (response.responseText != '') {
                        var response_obj = Ext.util.JSON.decode(response.responseText);
                        if (!response_obj.success) {
                            sw.swMsg.alert('Ошибка', response_obj.Error_Msg && response_obj.Error_Msg != '' ? response_obj.Error_Msg : 'При исполнении документа возникла ошибка');
                        }
                        request_grid.refreshRecords(null,0);
                    }
                }
            });
        }

        return false;
    },
	show: function() {
		var wnd = this;
		var filter_form = wnd.FilterPanel.getForm();
        var region_nick = getGlobalOptions().region.nick;

		// Определение свойств в зависимости от региона и типа организации  (Разделение АРМ)
		if (region_nick == 'ufa' && (getGlobalOptions().orgtype == 'farm' || getGlobalOptions().orgtype == 'dep')) {
		    wnd.sprType = 'dbo';
		    wnd.DocumentUcEditWindow = 'swFarmDocumentUcEditWindow';
		     this.findById('wpmwWorkPlaceGridPanel').setDataUrl('/?c=Farmacy&m=load&method=farm_AllDok');
		} 
		else {
		    wnd.sprType = 'rls';
		    wnd.DocumentUcEditWindow = 'swNewDocumentUcEditWindow';
		    this.findById('wpmwWorkPlaceGridPanel').setDataUrl('/?c=Farmacy&m=load&method=AllDok');
		}

		sw.Promed.swWorkPlaceMerchandiserWindow.superclass.show.apply(this, arguments);

		this.userMedStaffFact = arguments[0];

		this.GridPanel.enabledSearch = false;
		this.GridPanel.setActiveTab(1);
		this.GridPanel.enabledSearch = true;
		this.GridPanel.setActiveTab(0);
		
		
		console.log('sprType = ' + Ext.getCmp('swWorkPlaceMerchandiserWindow').sprType); 
		console.log('DocumentUcEditWindow = ' + wnd.DocumentUcEditWindow);

		this.findById('wpmwWorkPlaceGridPanel').addActions({
			name: 'action_import',
			text: langs('Импорт'),
			iconCls: 'add16',
			hidden: (wnd.sprType == 'dbo' || region_nick.inlist(['krym'])),
			handler: function() {
				wnd.doImport();
			}
		});

		this.findById('wpmwWorkPlaceGridPanel').addActions({
			name: 'action_import_nak',
			text: 'Импортировать накладную',
			iconCls: 'add16',
			hidden: (getGlobalOptions().region.nick != 'kz'),
			handler: function() {
                wnd.GetDocNumWindow.show();
			}
		});

		this.findById('wpmwWorkPlaceGridPanel').addActions({
			name: 'action_Canceling',
			iconCls: 'delete16',
			//text: 'Отменить обеспечение',
			text: 'Отменить',
			hidden: true,
			handler: function() {
				wnd.doCanceling();
			}
		});

        if(!this.findById('wpmwWorkPlaceGridPanel').getAction('action_wpm_actions')) {
            this.findById('wpmwWorkPlaceGridPanel').addActions({
                name:'action_wpm_actions',
                text:langs('Действия'),
                menu: [{
                    name: 'create_by_contract',
                    iconCls: 'add16',
                    text: 'Создать наряд на выполнение работ',
                    handler: function() {
                        wnd.createDocumentUcStorageWork();
                    }
                }],
                iconCls: 'actions16'
            });
        }
        this.findById('wpmwWorkPlaceGridPanel').getAction('action_wpm_actions').setHidden(getDrugControlOptions().doc_uc_operation_control != '1');

        this.findById('wpmwWorkPlaceGridRequestPanel').addActions({
			name: 'action_wpm_execute',
			iconCls: 'actions16',
			text: 'Исполнить',
			hidden: false,
			handler: function() {
				wnd.executeRequest();
			}
		}, 4);

		this.findById('wpmwWorkPlaceGridRequestPanel').addActions({
			name: 'action_wpm_cancel',
			iconCls: 'delete16',
			text: 'Отменить',
			hidden: false,
			handler: function() {
				wnd.cancelRequest();
			}
		}, 5);

        this.findById('wpmwWorkPlaceGridPanel').setColumnHidden('Supply_State', !Ext.isEmpty(getGlobalOptions().lpu_id) && getGlobalOptions().lpu_id > 0);
        this.findById('wpmwWorkPlaceGridPanel').setColumnHidden('StorageWork_State', getDrugControlOptions().doc_uc_operation_control != '1');

		filter_form.reset();

		this.mol_mp_combo.fullReset();
		this.ls_combo.fullReset();
		this.s_combo.fullReset();
		this.s_s_combo.fullReset();
		this.s_t_combo.fullReset();
		this.m_s_combo.fullReset();
		this.m_t_combo.fullReset();

        this.s_combo.getStore().baseParams.Field_Name = this.s_combo.hiddenName;
        this.s_s_combo.getStore().baseParams.Field_Name = this.s_s_combo.hiddenName;
        this.s_t_combo.getStore().baseParams.Field_Name = this.s_t_combo.hiddenName;

        this.s_s_combo.getStore().baseParams.MedService_Storage_id = !Ext.isEmpty(this.userMedStaffFact.Storage_id) ? this.userMedStaffFact.Storage_id : null;
        this.s_t_combo.getStore().baseParams.MedService_Storage_id = !Ext.isEmpty(this.userMedStaffFact.Storage_id) ? this.userMedStaffFact.Storage_id : null;
        this.m_s_combo.getStore().baseParams.MedService_Storage_id = !Ext.isEmpty(this.userMedStaffFact.Storage_id) ? this.userMedStaffFact.Storage_id : null;
        this.m_t_combo.getStore().baseParams.MedService_Storage_id = !Ext.isEmpty(this.userMedStaffFact.Storage_id) ? this.userMedStaffFact.Storage_id : null;

        this.s_combo.getStore().baseParams.UserOrg_Type = getGlobalOptions().orgtype;
        this.s_s_combo.getStore().baseParams.UserOrg_id = getGlobalOptions().org_id;
        this.s_t_combo.getStore().baseParams.UserOrg_id = getGlobalOptions().org_id;
        this.m_s_combo.getStore().baseParams.UserOrg_id = getGlobalOptions().org_id;
        this.m_t_combo.getStore().baseParams.UserOrg_id = getGlobalOptions().org_id;

		this.mol_mp_combo.enable();
        this.s_s_combo.disable();
        this.s_t_combo.disable();
        this.m_s_combo.disable();
        this.m_t_combo.disable();

		//Установка дефолтных значений
		/*filter_form.findField('Org_id').setValue(getGlobalOptions().org_id);
		if(getGlobalOptions().org_nick){
			filter_form.findField('Org_id').setRawValue(getGlobalOptions().org_nick);
		} else if(getGlobalOptions().orgtype == 'farm' && getGlobalOptions().OrgFarmacy_Nick){
			filter_form.findField('Org_id').setRawValue(getGlobalOptions().OrgFarmacy_Nick);
		} else if(getGlobalOptions().orgtype == 'contractor' && getGlobalOptions().Contragent_Name){
			filter_form.findField('Org_id').setRawValue(getGlobalOptions().Contragent_Name);
		}*/
        var org_id = getGlobalOptions().org_id;
        var org_combo = filter_form.findField('Org_id');
        if (org_id > 0) {
            org_combo.getStore().load({
                params: {
                    Org_id: org_id
                },
                callback: function() {
                    org_combo.setValue(org_id);
                }
            });
        }

        filter_form.findField('LpuBuilding_id').enable();

        var type_list = [2, 3, 6, 10, 12, 15, 17, 18, 33];
		if(getGlobalOptions().orgtype == 'lpu'){
            type_list.push(22);
			this.setFiltersByArmLevel();
		} else {
			this.s_combo.getStore().baseParams.Org_id = getGlobalOptions().org_id;
            this.s_combo.clearComboValue();

			if(getGlobalOptions().orgtype == 'farm'){
                type_list.push(11);
            }
		}

        //для Уфы отображаем тип "Приход в отделение"
        if (getGlobalOptions().region.nick == 'ufa') {
            type_list.push(32);
        }

        wnd.findById('wpmwDrugDocumentType').getStore().reload({params: {where: ' where DrugDocumentType_id in ('+type_list.join(',')+')'}});

		//Подгрузка комбобоксов с контрагентами
		loadContragent(this, 'wpmwContragent_sid', {mode:'sender'});
		loadContragent(this, 'wpmwContragent_tid', {mode:'receiver'});

		//Проверка открыт ли АРМ в режиме главного склада СМП
		this.isSmpMainStorage = false;
		this.getLoadMask(langs('Загрузка...')).show();
		this.checkIfMerchandiserIsInSmp(function(response) {
			wnd.isSmpMainStorage = response.isSmpMainStorage;
			wnd.isSmpSubStorage = response.isSmpSubStorage;
			wnd.getLoadMask().hide();
		});

		this.isAptMu = false;
		if (!Ext.isEmpty(this.userMedStaffFact.Lpu_id) && this.userMedStaffFact.MedServiceType_SysNick == 'merch') {
			this.isAptMu = true;
		}

		if (this.isAptMu) {
			this.LeftPanel.actions.action_PrivilegeSearch.hide();
			this.LeftPanel.actions.EvnReceptInCorrectFind.hide();
			this.LeftPanel.actions.action_EvnReceptSearch.hide();
			this.LeftPanel.actions.action_SupPlan.hide();
		} else {
			this.LeftPanel.actions.action_PrivilegeSearch.show();
			this.LeftPanel.actions.EvnReceptInCorrectFind.show();
			this.LeftPanel.actions.action_EvnReceptSearch.show();
			this.LeftPanel.actions.action_SupPlan.show();
		};
		
		Ext.getCmp('MerchandiserPrescr').hide();
		
		if (getGlobalOptions().region.nick == 'ufa') {
		    Ext.getCmp('MerchandiserProsmotrRas').hide();
			Ext.getCmp('MerchandiserObVed').show();
			if (getGlobalOptions().orgtype == 'lpu')
				Ext.getCmp('MerchandiserPrescr').show();
			if (getGlobalOptions().orgtype == 'dep') {
				wnd.LeftPanel.toggleCollapse();
				wnd.LeftPanel.hide();
				wnd.doLayout();
				wnd.setTitle('Документы учета РАС');
			}				
		} else {
		    Ext.getCmp('MerchandiserProsmotrRas').show(); 
			Ext.getCmp('MerchandiserObVed').hide(); 
		}
		
		//  В рамках "разделение АРМ"
		if (wnd.sprType != 'dbo') {
		    Ext.getCmp('MerchandiserPlanPostavok').show();

			if (getGlobalOptions().orgtype == 'lpu') {
				wnd.mol_mp_combo.showContainer();
				if (!Ext.isEmpty(getGlobalOptions().medpersonal_id)) {
					wnd.mol_mp_combo.setDefaultValue();
					wnd.mol_mp_combo.disable();
				}
			} else {
				wnd.mol_mp_combo.hideContainer();
			}
		    wnd.m_s_combo.showContainer();
		    wnd.m_t_combo.showContainer();
		    wnd.s_combo.showContainer(); 
		    wnd.findById('wpmwOrg_id').showContainer();
		    
		    Ext.getCmp('wpmwWorkPlaceGridRequestPanel').ownerCt.setDisabled(false);
		} else {
		    Ext.getCmp('MerchandiserPlanPostavok').hide();
		    Ext.getCmp('wpmwWorkPlaceGridRequestPanel').ownerCt.setDisabled(true);

		    wnd.mol_mp_combo.hideContainer();
		    wnd.m_s_combo.hideContainer();
		    wnd.m_t_combo.hideContainer();
		    wnd.s_combo.hideContainer(); 
		    wnd.findById('wpmwOrg_id').hideContainer();
		    
		    //  Получаем дату закрытия отчетного периода
		    Ext.Ajax.request({
		   
				callback: function(options, success, response) {
					if (success) {
						var response_obj = Ext.util.JSON.decode(response.responseText);
						//console.log('response_obj! = ');console.log(response_obj[0]);
						if(response_obj[0] && response_obj[0].DrugPeriodClose_DT) {
							wnd.DrugPeriodClose_DT = response_obj[0].DrugPeriodClose_DT;  // дата закрытия отчетного периода
							//if (response_obj[0].WhsDocumentUcInvent_DT  > wnd.DrugPeriodClose_DT)
							wnd.WhsDocumentUcInvent_DT = response_obj[0].WhsDocumentUcInvent_DT;  // дата не сформированой инвентаризационной ведомости
							//wnd.WhsDocumentUcInvent_DT = response_obj[0].WhsDocumentUcInvent_DT;  // дата не сформированой инвентаризационной ведомости
						    }
					} else {
						sw.swMsg.alert('Ошибка', 'При получении даты закрытия отчетного периода возникла ошибка');
					}
				},			
				url: '/?c=RegistryRecept&m=geDrugPeriodCloseDT'
			});
		}
	},
	buttonPanelActions: {
		action_JourNotice: {
			handler: function() {
				getWnd('swMessagesViewWindow').show();
			}.createDelegate(this),
			iconCls: 'notice32',
			nn: 'action_JourNotice',
			text: langs('Журнал уведомлений'),
			tooltip: langs('Журнал уведомлений')
		},
		action_PrivilegeSearch: {
			nn: 'action_PrivilegeSearch',
			tooltip: langs('Поиск льготников'),
			text: langs('Льготники'),
			iconCls : 'mse-journal32',
			disabled: false, 
			handler: function(){
				getWnd('swPrivilegeSearchWindow').show();
			}
		},
        EvnReceptInCorrectFind: {
            text: langs('Журнал отсрочки'),
            tooltip: langs('Журнал отсрочки'),
            iconCls : 'receipt-incorrect32',
            handler: function()
            {
                getWnd('swReceptInCorrectSearchWindow').show();
            }
        },
		action_EvnReceptSearch: {
			nn: 'action_EvnReceptSearch',
			tooltip: langs('Льготные рецепты: поиск'),
			text: langs('Льготные рецепты'),
			iconCls : 'priv-new32',
			disabled: false, 
			handler: function(){
				getWnd('swEvnReceptSearchWindow').show();
			}
		},
		action_Contragents: {
			nn: 'action_Contragents',
			tooltip: langs('Справочник: Контрагенты'),
			text: langs('Контрагенты'),
			iconCls : 'org32',
			disabled: false, 
			handler: function(){
				getWnd('swContragentViewWindow').show({
					ARMType: 'merch'
				});
			}
		},
        action_OrgFarmacyByLpuView: {
            nn: 'action_OrgFarmacyByLpuView',
            tooltip: langs('Прикрепление аптек к МО'),
            text: langs('Прикрепление аптек к МО'),
            iconCls : 'therapy-plan32',
            disabled: false,
            handler: function(){
                if (getRegionNick().inlist(['perm', 'ufa'])) {
                    getWnd('swOrgFarmacyByLpuViewWindow').show();
                } else {
                    getWnd('swOrgFarmacyLinkedByLpuViewWindow').show({ARMType: 'merch'});
                }
            }
        },
        action_MedOstat: {
            nn: 'action_MedOstat',
            tooltip: langs('Остатки медикаментов'),
            text: langs('Остатки медикаментов'),
            iconCls : 'rls-torg32',
            disabled: false,
            menuAlign: 'tr',
            menu: new Ext.menu.Menu({
                items: [
					/*{
						text: langs('Просмотр регистра остатков'),
						tooltip: langs('Просмотр регистра остатков'),
						iconCls: 'pill16',
						menuAlign: 'tr',
						menu: new Ext.menu.Menu({
							items: [{
								text: langs('Просмотр остатков организации пользователя'),
								tooltip: langs('Просмотр остатков организации пользователя'),
								iconCls: 'pill16',
								handler: function() {
									getWnd('swDrugOstatRegistryListWindow').show({mode: 'suppliers'});
								}
							}, {
								text: langs('Просмотр остатков по складам Аптек и РАС'),
								tooltip: langs('Просмотр остатков по складам Аптек и РАС'),
								iconCls: 'pill16',
								handler: function() {
									getWnd('swDrugOstatRegistryListWindow').show({mode: 'farmacy_and_store'});
								}
							}]
						})
					},*/
					{
						text: 'Оборотная ведомость',
						tooltip: 'Оборотная ведомость',
						iconCls: 'pill16',
						id: 'MerchandiserObVed',
						//hidden: (Ext.getCmp('swWorkPlaceMerchandiserWindow').sprType != 'dbo'),
						handler: function() {
							var wnd = Ext.getCmp('swWorkPlaceMerchandiserWindow');
							var params = new Object();
							if(getGlobalOptions().orgtype == 'lpu')
								params.LpuSection_id =  wnd.userMedStaffFact.LpuSection_id;
							getWnd('swDrugTurnoverListWindow').show(params);
						}
					},
					{
						text: langs('Просмотр остатков организации пользователя'),
						tooltip: langs('Просмотр остатков организации пользователя'),
						iconCls: 'pill16',
						handler: function() {
							var wnd = Ext.getCmp('swWorkPlaceMerchandiserWindow');

							getWnd('swDrugOstatRegistryListWindow').show({
								mode: 'suppliers',
								userMedStaffFact: wnd.userMedStaffFact
							});
						}
					}, {
						text: langs('Просмотр остатков по складам Аптек и РАС'),
						tooltip: langs('Просмотр остатков по складам Аптек и РАС'),
						iconCls: 'pill16',
						id: 'MerchandiserProsmotrRas',
						handler: function() {
							var wnd = Ext.getCmp('swWorkPlaceMerchandiserWindow');

							getWnd('swDrugOstatRegistryListWindow').show({
								mode: 'farmacy_and_store',
								userMedStaffFact: wnd.userMedStaffFact
							});
						}
					}/*,
                    {
                        text: langs('Просмотр остатков'),
                        tooltip: langs('Просмотр остатков'),
                        iconCls: 'farm-ostat16',
                        handler: function() {
                            getWnd('swMzMedOstatViewWindow').show();
                        },
                        hidden: !isSuperAdmin()
                    },
					{
						text: MM_DLO_MEDAPT,
						tooltip: langs('Работа с остатками медикаментов по аптекам'),
						iconCls : 'drug-farm16',
						hidden: !isSuperAdmin(),
						handler: function()
						{
							getWnd('swDrugOstatByFarmacyViewWindow').show();
						}
					},
					{
						text: MM_DLO_MEDNAME,
						tooltip: langs('Работа с остатками медикаментов по наименованию'),
						iconCls : 'drug-name16',
						hidden: !isSuperAdmin(),
						handler: function()
						{
							getWnd('swDrugOstatViewWindow').show();
						}
					}*/
                    //sw.Promed.Actions.OstAptekaViewAction,
                    //sw.Promed.Actions.OstDrugViewAction
                ]
            })
        },
        /*action_DokOstAction: {
            ARMType: 'all',
            tooltip: langs('Документы ввода остатков'),
            text: langs('Ввод остатков'),
            iconCls : 'report32',
            disabled: false,
			hidden: !isSuperAdmin(),
            handler: function() {
                //alert('вызов формы «Документы ввода остатков»');
				var wnd = this;
					getWnd('swDokOstViewWindow').show({
                    ARMType: wnd.ARMType
                });
            }
        },*/
       /* action_DokSpisAction: {
            ARMType: 'all',
            tooltip: langs('Акты списания медикаментов'),
            text: langs('Списание медикаментов'),
            iconCls : 'mp-drugrequest32',
            disabled: false,
			hidden: !isSuperAdmin(),
            handler: function() {
                //alert('вызов формы «Акты списания медикаментов»');
                getWnd('swDokSpisViewWindow').show();
            }
        },*/
		action_JournalNazn: {
			handler: function () {
				var wnd = Ext.getCmp('swWorkPlaceMerchandiserWindow');
				var params = new Object();
				if (getGlobalOptions().orgtype == 'lpu')
					params.Lpu_id = wnd.userMedStaffFact.Lpu_id;
				params.LpuSection_id = wnd.userMedStaffFact.LpuSection_id;
				getWnd('swEvnPrescrPerformanceJournalWindow').show(params);	
			},
			id: 'MerchandiserPrescr',
			hidden: true,
			iconCls: 'rls-torg32',
			//nn: 'action_SupPlan',
			text: 'Журнал назначений и выполнений',
			tooltip: 'Журнал назначений и выполнений',
			//iconCls: 'pill16',
		},
		action_WhsDocumentUcInvent: {
			nn: 'action_WhsDocumentUcInvent',
			tooltip: langs('Инвентаризация'),
			text: langs('Инвентаризация'),
			iconCls : 'invent32',
			disabled: false,
			menuAlign: 'tr?',
			menu: new Ext.menu.Menu({
				items: [{
					tooltip: langs('Приказы на проведение инвентаризации'),
					text: langs('Приказы на проведение инвентаризации'),
					iconCls : 'document16',
					handler: function() {
						getWnd('swWhsDocumentUcInventOrderViewWindow').show({
							ARMType: 'merch'
						});
					}
				}, {
					tooltip: langs('Инвентаризационные ведомости'),
					text: langs('Инвентаризационные ведомости'),
					iconCls : 'document16',
					disabled: false,
					//hidden: !isSuperAdmin(),
					handler: function() {
                        var wnd = getWnd('swWorkPlaceMerchandiserWindow');
                        var wndParams = {
							ARMType: 'merch',
                            MedService_id: wnd.userMedStaffFact.MedService_id,
                            Lpu_id: wnd.userMedStaffFact.Lpu_id,
                            LpuSection_id: wnd.userMedStaffFact.LpuSection_id,
                            LpuBuilding_id: wnd.userMedStaffFact.LpuBuilding_id,
                            Storage_id: wnd.userMedStaffFact.Storage_id,
                            Storage_pid: wnd.userMedStaffFact.Storage_pid
						};
                        if(getGlobalOptions().orgtype != 'lpu' && wnd.userMedStaffFact.MedService_id > 0){
                        	Ext.Ajax.request({
		   						params:{MedService_id:wnd.userMedStaffFact.MedService_id},
								callback: function(options, success, response) {
									if (success) {
										var response_obj = Ext.util.JSON.decode(response.responseText);
										if(response_obj[0] && response_obj[0].OrgStruct_id) {
											wndParams.OrgStruct_id = response_obj[0].OrgStruct_id;
										}
									}
									getWnd('swWhsDocumentUcInventViewWindow').show(wndParams);
								},			
								url: '/?c=MedService&m=loadEditForm'
							});
                        } else {
                        	getWnd('swWhsDocumentUcInventViewWindow').show(wndParams);
                        }
					}
				}]
			})
		},
		/*action_Recipe: {
			nn: 'action_Recipe',
			tooltip: langs('Рецепты'),
			text: langs('Рецепты'),
			iconCls : 'receipt-new32',
			disabled: false,
			menuAlign: 'tr',
			menu: new Ext.menu.Menu({
				items: [					
					sw.Promed.Actions.EvnReceptProcessAction,
					sw.Promed.Actions.EvnReceptTrafficBookViewAction,
					sw.Promed.Actions.EvnReceptInCorrectFindAction
				]
			})
		},*/
        action_References: {
            nn: 'action_References',
            tooltip: langs('Справочники'),
            text: langs('Справочники'),
            iconCls : 'book32',
            disabled: false,
            menuAlign: 'tr?',
            menu: new Ext.menu.Menu({
                items: [{
                    tooltip: getRLSTitle(),
                    text: getRLSTitle(),
                    iconCls: 'rls16',
                    handler: function() {
                        if ( !getWnd('swRlsViewForm').isVisible() )
                            getWnd('swRlsViewForm').show();
                    }
                }, {
                    tooltip: langs('МКБ-10'),
                    text: langs('Справочник МКБ-10'),
                    iconCls: 'spr-mkb16',
                    handler: function() {
                        getWnd('swMkb10SearchWindow').show();
                    }
                }, {
                    tooltip: langs('Просмотр') + getMESAlias(),
                    text: langs('Просмотр') + getMESAlias(),
                    iconCls: 'spr-mes16',
                    handler: function() {
                        if ( !getWnd('swMesOldSearchWindow').isVisible() )
                            getWnd('swMesOldSearchWindow').show();
                    }
                },
				sw.Promed.Actions.swDrugDocumentSprAction,
				{
					name: 'action_GoodsStorageView',
					text: langs('Наименования мест хранения'),
					iconCls : '',
					handler: function()
					{
						getWnd('swGoodsStorageViewWindow').show({armType: 'merch'});
					}
				},{
					name: 'action_DrugNomenSpr',
					text: langs('Номенклатурный справочник'),
					iconCls : '',
					handler: function()
					{
						getWnd('swDrugNomenSprWindow').show();
					}
				}, {
					name: 'action_PriceJNVLP',
					text: langs('Цены на ЖНВЛП'),
					iconCls : 'dlo16',
					handler: function() {
						getWnd('swJNVLPPriceViewWindow').show();
					}
				}, {
					name: 'action_DrugMarkup',
					text: langs('Предельные надбавки на ЖНВЛП'),
					iconCls : 'lpu-finans16',
					handler: function() {
						getWnd('swDrugMarkupViewWindow').show();
					}
				},
				sw.Promed.Actions.swPrepBlockSprAction
				]
            })
        },
        action_RegistryLLO: {
            handler: function() {
                getWnd('swRegistryLLOViewWindow').show({
					ARMType: 'merch'
				});
            },
            hidden: !(
                getGlobalOptions().region.nick.inlist([/*'ufa',*/ 'khak', 'saratov', 'krym', 'ekb']) &&
                (Ext.isEmpty(getGlobalOptions().lpu_id) || getGlobalOptions().lpu_id == 0) &&
                getGlobalOptions().dlo_logistics_system == 'level3' &&
                getGlobalOptions().ContragentType_SysNick == 'apt'
            ),
            iconCls: 'service-reestrs16',
            nn: 'action_RegistryLLO',
            text: langs('Оплата реестров рецептов'),
            tooltip: langs('Оплата реестров рецептов')
        },
        action_SupPlan: {
            handler: function() {
				getWnd('swMinzdravDLODocumentsWindow').show({WhsDocumentType_id: 15});
            },
	    id: 'MerchandiserPlanPostavok',
            hidden: true,
            iconCls: 'plan32',
            nn: 'action_SupPlan',
            text: langs('Планы поставок'),
            tooltip: langs('Планы поставок')
        },
        action_StoragePlacement:
		{
			nn: 'action_StoragePlacement',
			tooltip: langs('Размещение на складах'),
			text: langs('Размещение на складах'),
			iconCls : 'storage-place32',
			handler: function()
			{
				var wnd = Ext.getCmp('swWorkPlaceMerchandiserWindow');
				wnd.getArmLevelByMedService(function(data){
					if(!data || typeof data != 'object'){
						var data = {};
					}
					data.fromARM = 'merch';
					if(
						(data.LpuBuildingType_id && data.LpuBuildingType_id == 27)
						|| (data.LpuBuildingTypeByLpuSection && data.LpuBuildingTypeByLpuSection == 27)
						|| (data.LpuBuildingTypeByLpuUnit && data.LpuBuildingTypeByLpuUnit == 27)
						|| (data.LpuBuildingTypeByStorage && data.LpuBuildingTypeByStorage == 27)
					)
					{
						data.mode = 'smp';
						if(data.LpuBuilding_id && data.LpuBuildingType_id && data.LpuBuildingType_id == 27){
							data.smp = {LpuBuilding_id:data.LpuBuilding_id};
						}
					}
					getWnd('swStorageZoneViewWindow').show(data);
				});
			}
		}
	},
    setFiltersByArmLevel: function() {
        var wnd = this;
        var filter_form = wnd.FilterPanel.getForm();

        this.getArmLevelByMedService(function(response_obj) {
        	var struct_level = null;

        	//определение уровня
            if(Ext.isEmpty(response_obj.LpuBuilding_id) && Ext.isEmpty(response_obj.LpuUnit_id) && Ext.isEmpty(response_obj.LpuSection_id) && !Ext.isEmpty(response_obj.Lpu_id)) {
                //уровень МО
                struct_level = 'lpu';
            } else if(!Ext.isEmpty(response_obj.LpuBuilding_id) && Ext.isEmpty(response_obj.LpuUnit_id) && Ext.isEmpty(response_obj.LpuSection_id) && !Ext.isEmpty(response_obj.Lpu_id)) {
                //уровень подразделения
                struct_level = 'lpu_building';
            } else if(!Ext.isEmpty(response_obj.LpuBuilding_id) && !Ext.isEmpty(response_obj.LpuSection_id) && !Ext.isEmpty(response_obj.Lpu_id)) {
                //уровень отделения
                struct_level = 'lpu_section';
            }

			//настройка комбобокса "Подразделение"
			var lb_id = !Ext.isEmpty(response_obj.LpuBuilding_id) ? response_obj.LpuBuilding_id : null;
			var lb_combo = filter_form.findField('LpuBuilding_id');
			var lb_combo_enabled = Ext.isEmpty(wnd.userMedStaffFact.Storage_pid);

			if (lb_combo_enabled && !Ext.isEmpty(getGlobalOptions().lpu_id)) {
                lb_combo.enable();
                lb_combo.getStore().load({
					params: {
						Lpu_id: getGlobalOptions().lpu_id
					},
					callback: function() {
                        lb_combo.enable();
						if(!Ext.isEmpty(lb_id)) {
                            var idx = lb_combo.getStore().findBy(function(record) {
                                return (record.get('LpuBuilding_id') == lb_id);
                            })
                            if (idx > -1) {
                            	lb_combo.setValue(lb_id);
                                if(!Ext.isEmpty(response_obj.LpuSection_id)){
                                    filter_form.findField('LpuSection_id').setValueById(response_obj.LpuSection_id);
                                }
                            }
						}
					}
				});
			} else if(!Ext.isEmpty(lb_id)){
                lb_combo.setValue(lb_id);
                lb_combo.setRawValue(response_obj.LpuBuilding_Name);
                lb_combo.disable();

                if(!Ext.isEmpty(response_obj.LpuSection_id)){
                    filter_form.findField('LpuSection_id').setValueById(response_obj.LpuSection_id);
                }
			}

            //настройка комбобокса "Отделение"
            wnd.ls_combo.getStore().baseParams.Lpu_id = null;
            wnd.ls_combo.getStore().baseParams.LpuBuilding_id = null;
            wnd.ls_combo.getStore().baseParams.MedService_Storage_id = null;

            if(struct_level == 'lpu') { //уровень МО
                wnd.ls_combo.getStore().baseParams.Lpu_id = getGlobalOptions().lpu_id;
                wnd.ls_combo.enable();
            } else if(struct_level == 'lpu_building' || Ext.isEmpty(wnd.userMedStaffFact.Storage_pid)) { //уровень подразделения или склад службы не имеет родителя
            	wnd.ls_combo.getStore().baseParams.LpuBuilding_id = response_obj.LpuBuilding_id;
                wnd.ls_combo.enable();
            } else if (struct_level == 'lpu_section' && !Ext.isEmpty(wnd.userMedStaffFact.Storage_id)) { //уровень отделения и указан склад службы
                wnd.ls_combo.getStore().baseParams.MedService_Storage_id = wnd.userMedStaffFact.Storage_id;
                wnd.ls_combo.enable();
			}

			if (!Ext.isEmpty(response_obj.LpuSection_id) && !Ext.isEmpty(lb_id)) {
				wnd.ls_combo.setValue(response_obj.LpuSection_id);
			} else {
				wnd.ls_combo.clearComboValue();
			}


            //настройка комбобокса "Склад"
            wnd.s_combo.getStore().baseParams.Org_id = null;
            wnd.s_combo.getStore().baseParams.Lpu_id = null;
            wnd.s_combo.getStore().baseParams.LpuBuilding_id = null;
            wnd.s_combo.getStore().baseParams.LpuSection_id = null;
            wnd.s_combo.getStore().baseParams.MedService_id = !Ext.isEmpty(wnd.userMedStaffFact.MedService_id) ? wnd.userMedStaffFact.MedService_id : null;

            if(struct_level == 'lpu') { //уровень МО
                wnd.s_combo.getStore().baseParams.Lpu_id = getGlobalOptions().lpu_id;
            } else if(struct_level == 'lpu_building') { //уровень подразделения
                wnd.s_combo.getStore().baseParams.Lpu_id = getGlobalOptions().lpu_id;
                wnd.s_combo.getStore().baseParams.LpuBuilding_id = response_obj.LpuBuilding_id;
            } else if(struct_level == 'lpu_section') { //уровень отделения
                wnd.s_combo.getStore().baseParams.Lpu_id = getGlobalOptions().lpu_id;
                wnd.s_combo.getStore().baseParams.LpuBuilding_id = response_obj.LpuBuilding_id;
                wnd.s_combo.getStore().baseParams.LpuSection_id = response_obj.LpuSection_id;
            }

            if (!wnd.s_combo.disabled) {
                wnd.s_combo.clearComboValue();
            }

            wnd.doSearch();
        });
    },
	getArmLevelByMedService: function(callback) { //Получаем данные: на каком уровне запущен АРМ: МО, подразделение, отделение.
		var params = {
			MedService_id: this.userMedStaffFact.MedService_id
		};
		this.getLoadMask(LOAD_WAIT).show();
		Ext.Ajax.request({
			url: '/?c=MedService&m=getArmLevelByMedService',
			params: params,
			callback: function(options, success, response) {
				this.getLoadMask().hide();
				var response_obj = Ext.util.JSON.decode(response.responseText)[0];
				callback(response_obj);
			}.createDelegate(this),
			failure: function()  {
				this.getLoadMask().hide();
			}
		});
	},
	showDrugRequestEditForm: function() {
		/*
		Получаем данные:
		является ли текущий пользователь врачом ЛЛО
		заявка на последний период, из имеющихся в системе или последняя имеющияся заявка.
		*/
		var params = {
			LpuSection_id: this.userMedStaffFact.LpuSection_id,
			MedPersonal_id: getGlobalOptions().medpersonal_id
		};
		this.getLoadMask(LOAD_WAIT).show();
		Ext.Ajax.request({
			url: '/?c=DrugRequest&m=getDrugRequestLast',
			params: params,
			callback: function(options, success, response) {
				this.getLoadMask().hide();
				var response_obj = Ext.util.JSON.decode(response.responseText);
				if (response_obj[0].is_dlo != 1) {
					Ext.Msg.alert(langs('Сообщение'), langs('Врач не имеет права на выписку рецептов по ЛЛО.'));
					return false;
				}
				params.owner = this;
				params.Person_id = null;
				if (response_obj[0].next_DrugRequest_id > 0) {
					params.action = 'edit';
					params.DrugRequest_id = response_obj[0].next_DrugRequest_id;
					params.DrugRequestStatus_id = response_obj[0].next_DrugRequestStatus_id;
					params.DrugRequestPeriod_id = response_obj[0].next_DrugRequestPeriod_id;
					getWnd('swNewDrugRequestEditForm').show(params);
				} else {
					sw.swMsg.show({
						icon: Ext.MessageBox.QUESTION,
						msg: langs('На следующий период ')+ (response_obj[0].next_DrugRequestPeriod) +langs(' заявка по врачу ')+ (response_obj[0].MedPersonal_Fin) +langs(' не найдена. Открыть последнюю имеющуюся или создать?'),
						title: langs('Заявка не найдена'),
						buttons: {yes: langs('Открыть'), no: langs('Создать'), cancel: langs('Отмена')},
						fn: function(buttonId, text, obj) {
							if ('yes' == buttonId) {
								if(response_obj[0].last_DrugRequest_id > 0) {
									params.action = 'edit';
									params.DrugRequest_id = response_obj[0].last_DrugRequest_id;
									params.DrugRequestStatus_id = response_obj[0].last_DrugRequestStatus_id;
									params.DrugRequestPeriod_id = response_obj[0].last_DrugRequestPeriod_id;
									getWnd('swNewDrugRequestEditForm').show(params);
								} else {
									Ext.Msg.alert(langs('Сообщение'), langs('У врача нет ни одной заявки на лекарственные средства.'));
								}
							}
							if ('no' == buttonId) {
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
			failure: function()  {
				this.getLoadMask().hide();
			}
		});
	},
    createDocumentUcStorageWork: function() {
        var viewframe = Ext.getCmp('wpmwWorkPlaceGridPanel');
        var selected_row = viewframe.getGrid().getSelectionModel().getSelected();
        if (!Ext.isEmpty(selected_row)/* && selected_row.get('DrugDocumentType_Code') == '6'*/) { //6 - Приходная накладная
            getWnd('swDocumentUcStorageWorkCreateWindow').show({
                DocumentUc_id: selected_row.get('DocumentUc_id'),
                DrugDocumentType_Code: selected_row.get('DrugDocumentType_Code'),
                callback: function() {
                    viewframe.refreshRecords(null,0);
                }
            });
        }
    },
	doCanceling: function() {
        var $GridPanel = Ext.getCmp('wpmwWorkPlaceGridPanel');
        var rowSelected = $GridPanel.getGrid().getSelectionModel().getSelected();
        if (rowSelected != undefined) {
            if ((rowSelected.data.DrugDocumentStatus_Code == 4 || rowSelected.data.DrugDocumentType_Code == 11) &&  rowSelected.data.isKM != 2) {
                var params = {
					DocumentUc_id: rowSelected.data.DocumentUc_id
				};
				var $msg = 'Вы действительно хотите отменить исполнение документа?';
				var $title = 'Отмена исполнения документа'
				if (rowSelected.data.DrugDocumentType_Code == 11 && rowSelected.data.DrugDocumentStatus_Code == 1) {
					$msg = 'Вы действительно хотите отменить Оповещение?';
					$title = 'Отмена Оповещения';
				}
                sw.swMsg.show({
					icon: Ext.MessageBox.QUESTION,
					msg: $msg,
					title: $title,
					buttons: {yes: 'Да', no: 'Нет'},
					fn: function(buttonId, text, obj) {
						if ('yes' == buttonId) {
							Ext.Ajax.request({
                                url: '/?c=DocumentUc&m=Canceling',
                                params: params,
                                callback: function() {
                                    //console.log('refresh');
                                    $GridPanel.refreshRecords(null,0);
                                }
                            });
						}
						if ('no' == buttonId) {
							return false;
						}
					}
				})
            }
        };
    },
	doImport: function() {
		var wnd = this;
		getWnd('swDocumentUcImportWindow').show({
			callback: function() {
				wnd.findById('wpmwWorkPlaceGridPanel').refreshRecords(null,0);
			}
		});
	},
	doSearch: function(mode) {
		var filter_form = this.FilterPanel.getForm();
		var params = filter_form.getValues();
		if(!getGlobalOptions().superadmin) {
			params.Org_id = getGlobalOptions().org_id;
		}
		params.MedService_id = this.userMedStaffFact.MedService_id;

		if(filter_form.findField('LpuBuilding_id').disabled){
			params.LpuBuilding_id = filter_form.findField('LpuBuilding_id').getValue();
		}
		if(filter_form.findField('LpuSection_id').disabled){
			params.LpuSection_id = filter_form.findField('LpuSection_id').getValue();
		}

		if (getGlobalOptions().orgtype != 'lpu' && !isOrgAdmin()) {
			params.filterByOrgUser = true;
		}
		if (!Ext.isEmpty(this.userMedStaffFact.LpuSection_id) && !Ext.isEmpty(this.userMedStaffFact.Storage_id)) { //если АРМ на уровне отделения и для службы прописан склад
			params.MedService_Storage_id = this.userMedStaffFact.Storage_id;
		}
			
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

		params.begDate = Ext.util.Format.date(this.dateMenu.getValue1(), 'd.m.Y');
		params.endDate = Ext.util.Format.date(this.dateMenu.getValue2(), 'd.m.Y');
		params.UserMedPersonal_id = getGlobalOptions().medpersonal_id;
		/*var DocumentUc_date_range = filter_form.findField('DocumentUc_date_range');
		if(!Ext.isEmpty(DocumentUc_date_range.getValue1()) || !Ext.isEmpty(DocumentUc_date_range.getValue2())){
			params.begDate = '';
			params.endDate = '';
			params.begDate = Ext.util.Format.date(DocumentUc_date_range.getValue1(), 'd.m.Y');
			params.endDate = Ext.util.Format.date(DocumentUc_date_range.getValue2(), 'd.m.Y');
		}*/
		
		params.limit = 100;
		params.start = 0;
		var activeGrid = this.GridPanel.getActiveTab().items.items[0].id;
		switch (activeGrid) {
			case 'wpmwWorkPlaceGridPanel':
				this.findById('wpmwWorkPlaceGridPanel').removeAll({addEmptyRecord: false});
				if (this.sprType != 'dbo') {
		                    this.findById('wpmwWorkPlaceGridPanel').loadData({globalFilters: params});
		                 }
				else {
					//  Для аптеки Уфы отмена обеспечения рецепта
					$GridPanel = this.findById('wpmwWorkPlaceGridPanel');
					this.findById('wpmwWorkPlaceGridPanel').loadData({
						globalFilters: params,
						 callback: function(){
							$GridPanel.updateContextMenu();
						 }  
						});
						$GridPanel.getGrid().on(
							'cellclick',
							$GridPanel.updateContextMenu
						);
						$GridPanel.getGrid().on(
							'cellcontextmenu',
							$GridPanel.updateContextMenu
						);
				}
			break;
			case 'wpmwWorkPlaceGridRequestPanel':
				this.findById('wpmwWorkPlaceGridRequestPanel').removeAll({addEmptyRecord: false});
                this.findById('wpmwWorkPlaceGridRequestPanel').loadData({globalFilters: params});		
			break;
			default:
			break;
		}
		
	},
	doReset: function() {
		sw.Promed.swWorkPlaceMerchandiserWindow.superclass.doReset.apply(this, arguments);;

		//установка значений фильтров по умолчанию
		this.mol_mp_combo.setDefaultValue();
	},
	// Функция определения принадлежности рабочего места товароведа к центральному складу СМП
	checkIfMerchandiserIsInSmp: function(callback) {
		Ext.Ajax.request({
			url: '/?c=Storage&m=checkIfMerchandiserIsInSmp',
			callback: function(options, success, response) {

				var response_obj = Ext.util.JSON.decode(response.responseText);
				if ( typeof response_obj[0].MedServiceHasSmpMainStorage === 'undefined' 
					|| typeof response_obj[0].MedServiceHasSmpSubStorage === 'undefined' 
					|| response_obj[0].Error_Msg) {
					Ext.Msg.alert(langs('Сообщение'), response_obj[0].Error_Msg || langs('Ошибка получения типа склада СМП'));
					return false;
				}
				
				callback( {
					isSmpMainStorage : ( response_obj[0].MedServiceHasSmpMainStorage === 1 ),
					isSmpSubStorage : ( response_obj[0].MedServiceHasSmpSubStorage === 1 )
				} );
			}
		});
	},
	loadStorage: function(fieldName) {
		var base_form = this.FilterPanel.getForm();

		var storage_combo = base_form.findField(fieldName);
		var contragent_combo = null;
		var mol_combo = null;

		var contragent = null;
		if (fieldName == 'Storage_tid') {
			contragent_combo = base_form.findField('Contragent_tid');
			contragent = contragent_combo.getStore().getById(contragent_combo.getValue());
			mol_combo = this.m_t_combo;
		} else if (fieldName == 'Storage_sid') {
			contragent_combo = base_form.findField('Contragent_sid');
			contragent = contragent_combo.getStore().getById(contragent_combo.getValue());
			mol_combo = this.m_s_combo;
		}

		//сброс фильтров
        storage_combo.getStore().baseParams.Org_id = null;
        mol_combo.getStore().baseParams.Org_id = null;

        storage_combo.disable();
        mol_combo.disable();

		var storage_id = storage_combo.getValue();
		if (contragent) {
			if (contragent.get('Org_id') == getGlobalOptions().org_id) {
                storage_combo.getStore().baseParams.Org_id = contragent.get('Org_id');

                storage_combo.getStore().load({
                    callback: function() {
                        var storage = storage_combo.getStore().getById(storage_id);
                        if (storage) {
                            storage_combo.setValue(storage_id);
                        } else {
                            storage_combo.clearValue();
                        }
                        mol_combo.getStore().baseParams.Org_id = contragent.get('Org_id');
                        mol_combo.clearComboValue();
                    }
                });

                storage_combo.enable();
                mol_combo.enable();
			}
		} else {
            storage_combo.clearComboValue();
            mol_combo.clearComboValue();

            storage_combo.enable();
            mol_combo.enable();
		}
	},
	initComponent: function() {
		var form = this;

		this.onKeyDown = function (inp, e) {
			if (e.getKey() == Ext.EventObject.ENTER) {
				e.stopEvent();
				this.doSearch();
			}
		}.createDelegate(this);

		//комбобокс "МОЛ" для организации (насамом деле содержит список MedPersonal_id)
		this.mol_mp_combo = new sw.Promed.SwCustomRemoteCombo({
			fieldLabel: langs('МОЛ'),
			hiddenName: 'MolMedPersonal_id',
			displayField: 'MedPersonal_Fio',
			valueField: 'MedPersonal_id',
			editable: true,
			allowBlank: true,
			width: 200,
			listWidth: 300,
			triggerAction: 'all',
			tpl: new Ext.XTemplate(
				'<tpl for="."><div class="x-combo-list-item">',
				'<table style="border: 0;"><td style="width: 70px"><font color="red">{MedPersonal_Code}</font></td><td><h3>{MedPersonal_Fio}</h3></td></tr></table>',
				'</div></tpl>'
			),
			store: new Ext.data.Store({
				autoLoad: false,
				reader: new Ext.data.JsonReader({
					id: 'MedPersonal_id'
				}, [
					{ name: 'MedPersonal_id', mapping: 'MedPersonal_id' },
					{ name: 'MedPersonal_Code', mapping: 'MedPersonal_Code' },
					{ name: 'MedPersonal_Fio', mapping: 'MedPersonal_Fio' }
				]),
				url: C_MP_DLO_LOADLIST
			}),
			setDefaultValue: function() {
				if (form.sprType != 'dbo' && getGlobalOptions().orgtype == 'lpu' && !Ext.isEmpty(getGlobalOptions().medpersonal_id)) {
					form.mol_mp_combo.setValueById(getGlobalOptions().medpersonal_id);
				}
			}
		});

		//комбобокс "Отделение"
        this.ls_combo = new sw.Promed.SwCustomRemoteCombo({
            fieldLabel: langs('Отделение'),
            hiddenName: 'LpuSection_id',
            displayField: 'LpuSection_Name',
            valueField: 'LpuSection_id',
            editable: true,
            allowBlank: true,
            width: 230,
            listWidth: 400,
            triggerAction: 'all',
            tpl: new Ext.XTemplate(
                '<tpl for="."><div class="x-combo-list-item">',
                '<font color="red">{LpuSection_Code}</font>&nbsp;{LpuSection_Name}',
                '</div></tpl>'
            ),
            store: new Ext.data.Store({
                autoLoad: false,
                reader: new Ext.data.JsonReader({
                    id: 'LpuSection_id'
                }, [
                    {name: 'LpuSection_id', mapping: 'LpuSection_id'},
                    {name: 'LpuSection_Code', mapping: 'LpuSection_Code'},
                    {name: 'LpuSection_Name', mapping: 'LpuSection_Name'},
                    {name: 'LpuBuilding_id', mapping: 'LpuBuilding_id'}
                ]),
                url: '/?c=DocumentUc&m=loadLpuSectionMerchCombo'
            }),
			listeners: {
                'select': function() {
                	this.setLinkedFieldValues('change');
				}
            },
            setLinkedFieldValues: function(event_name) {
                if (event_name == 'change' || event_name == 'clear') {
                    var lb_combo = form.findById('wpmwLpuBuilding_id');
                    var ls_data = this.getSelectedRecordData();
                    var ls_id = this.getValue();
                    var lb_id = !Ext.isEmpty(ls_data.LpuBuilding_id) ? ls_data.LpuBuilding_id : null;

                    if (!lb_combo.disabled && ls_id > 0){
                        if(!Ext.isEmpty(lb_id)){
                            lb_combo.setValue(lb_id);
                        } else {
                            lb_combo.setValue('');
                        }
                    }
                    form.s_combo.getStore().baseParams.LpuBuilding_id = null;
                    form.s_combo.getStore().baseParams.LpuSection_id = ls_id;
                    form.s_combo.clearComboValue();
                }
            },
            clearComboValue: function() {
                this.setValue(null);
                this.reset();
                this.getStore().removeAll();
                delete this.lastQuery;
			}
        })


		//комбобоксы "Склад"
        var s_combo_config = {
            fieldLabel: langs('Склад'),
            hiddenName: 'Storage_id',
            displayField: 'Storage_Name',
            valueField: 'Storage_id',
            allowBlank: true,
            width: 525,
            ownerWindow: form,
            listeners: {
                'keydown': form.onKeyDown
			},
            clearComboValue: function() {
                this.setValue(null);
                this.reset();
                this.getStore().removeAll();
                delete this.lastQuery;
            }
        };

        var s_combo_store_config = {
            autoLoad: false,
            reader: new Ext.data.JsonReader({
                id: 'Storage_id'
            }, [
                {name: 'Storage_id', mapping: 'Storage_id'},
                {name: 'Storage_Code', mapping: 'Storage_Code'},
                {name: 'Storage_Name', mapping: 'Storage_Name'}
            ]),
            url: '/?c=DocumentUc&m=loadStorageMerchCombo'
        };

        s_combo_config.hiddenName = 'Storage_id';
        s_combo_config.store = new Ext.data.Store(s_combo_store_config);
        s_combo_config.width = 230;
        s_combo_config.setLinkedFieldValues = function(event_name) {
            if (event_name == 'change' || event_name == 'clear') {
            	var s_id = this.getValue();

                if (!form.ls_combo.disabled && s_id > 0){
                	var s_data = this.getSelectedRecordData();
                    var ls_id = !Ext.isEmpty(s_data.LpuSection_id) ? s_data.LpuSection_id : null;

                    if(!Ext.isEmpty(form.ls_combo.getStore().getById(ls_id))){
                        form.ls_combo.setValue(ls_id);
                    } else {
                        form.ls_combo.setValue('');
                    }
                }
            }
        }
        this.s_combo = new sw.Promed.SwCustomOwnerCombo(s_combo_config);

        s_combo_config.hiddenName = 'Storage_sid';
        s_combo_config.store = new Ext.data.Store(s_combo_store_config);
        s_combo_config.width = 300;
        s_combo_config.setLinkedFieldValues = function(event_name) {
            if (event_name == 'change' || event_name == 'clear') {
                var ct_combo = form.FilterPanel.getForm().findField('Contragent_sid');
                var ct_id = ct_combo.getValue();
                var mol_combo = form.FilterPanel.getForm().findField('Mol_sid');
                var s_data = this.getSelectedRecordData();

                if(ct_id > 0){
                    var org_id = ct_combo.getStore().getById(ct_id).get('Org_id');
                    var ms_id = ct_combo.getStore().getById(ct_id).get('MedService_id');
                } else {
                    var org_id = !Ext.isEmpty(s_data.Org_id) ? s_data.Org_id : null;
                    var ms_id = !Ext.isEmpty(s_data.MedService_id) ? s_data.MedService_id : null;
                }

                mol_combo.getStore().load({params: {Org_id: org_id, MedService_id: ms_id}});
            }
        }
        this.s_s_combo = new sw.Promed.SwCustomOwnerCombo(s_combo_config);

        s_combo_config.hiddenName = 'Storage_tid';
        s_combo_config.store = new Ext.data.Store(s_combo_store_config);
        s_combo_config.width = 300;
        s_combo_config.setLinkedFieldValues = function(event_name) {
            if (event_name == 'change' || event_name == 'clear') {
                var ct_combo = form.FilterPanel.getForm().findField('Contragent_tid');
                var ct_id = ct_combo.getValue();
                var mol_combo = form.FilterPanel.getForm().findField('Mol_tid');
                var s_data = this.getSelectedRecordData();

                if(ct_id > 0){
                    var org_id = ct_combo.getStore().getById(ct_id).get('Org_id');
                    var ms_id = ct_combo.getStore().getById(ct_id).get('MedService_id');
                } else {
                    var org_id = !Ext.isEmpty(s_data.Org_id) ? s_data.Org_id : null;
                    var ms_id = !Ext.isEmpty(s_data.MedService_id) ? s_data.MedService_id : null;
                }

                mol_combo.getStore().load({params: {Org_id: org_id, MedService_id: ms_id}});
            }
        }
        this.s_t_combo = new sw.Promed.SwCustomOwnerCombo(s_combo_config);
        

        //комбобоксы "МОЛ"
        var m_combo_config = {
            fieldLabel: langs('МОЛ'),
            hiddenName: 'Mol_id',
            displayField: 'Mol_Name',
            valueField: 'Mol_id',
            allowBlank: true,
            width: 525,
            ownerWindow: form,
            listeners: {
                'keydown': form.onKeyDown
			},
            clearComboValue: function() {
                this.setValue(null);
                this.reset();
                this.getStore().removeAll();
                delete this.lastQuery;
            }
        };

        var m_combo_store_config = {
            autoLoad: false,
            reader: new Ext.data.JsonReader({
                id: 'Mol_id'
            }, [
                {name: 'Mol_id', mapping: 'Mol_id'},
                {name: 'Mol_Code', mapping: 'Mol_Code'},
                {name: 'Mol_Name', mapping: 'Mol_Name'}
            ]),
            url: '/?c=DocumentUc&m=loadMolMerchCombo'
        };

        m_combo_config.hiddenName = 'Mol_sid';
        m_combo_config.store = new Ext.data.Store(m_combo_store_config);
        m_combo_config.width = 260;
        m_combo_config.setLinkedFieldValues = function(event_name) {
            if (event_name == 'change' || event_name == 'clear') {
                var m_data = this.getSelectedRecordData();
                var s_id = !Ext.isEmpty(m_data.Storage_id) ? m_data.Storage_id : null;
                var storage_combo = form.s_s_combo;
                if(s_id && storage_combo.getStore().getById(s_id)){
                    storage_combo.setValue(s_id);
                } else {
                    storage_combo.setValue('');
                }
            }
        }
        this.m_s_combo = new sw.Promed.SwCustomOwnerCombo(m_combo_config);

        m_combo_config.hiddenName = 'Mol_tid';
        m_combo_config.store = new Ext.data.Store(m_combo_store_config);
        m_combo_config.width = 260;
        m_combo_config.setLinkedFieldValues = function(event_name) {
            if (event_name == 'change' || event_name == 'clear') {
                var m_data = this.getSelectedRecordData();
                var s_id = !Ext.isEmpty(m_data.Storage_id) ? m_data.Storage_id : null;
                var storage_combo = form.s_t_combo;
                if(s_id && storage_combo.getStore().getById(s_id)){
                    storage_combo.setValue(s_id);
                } else {
                    storage_combo.setValue('');
                }
            }
        }
        this.m_t_combo = new sw.Promed.SwCustomOwnerCombo(m_combo_config);


		this.FilterPanel = new Ext.form.FormPanel({
			autoScroll: true,
			autoHeight: true,
			bodyBorder: false,
			collapsible: true,
			collapsed: false,
			floatable: false,
			titleCollapse: false,
			title: '<div>Фильтры</div>',
			bodyStyle:'background:#DFE8F6',
			border: false,
			buttonAlign: 'left',
			frame: false,
			plugins: [Ext.ux.PanelCollapsedTitle],
			animCollapse: false,
			height: 190,
			id: 'MerchandiserFilterForm',
			items:[new Ext.TabPanel({
					activeTab: 0,
					height: 190,
					border: false,
					defaults: { bodyStyle: 'padding: 0px;background:#DFE8F6' },
					bodyStyle: 'background:#DFE8F6',
					id: 'MerchandiserFilterTabPanel',
					layoutOnTabChange: true,
					listeners: {
						'tabchange': function(panel, tab) {
							this.findById('MerchandiserFilterForm').fireEvent('expand', this);
						}.createDelegate(this)
					},
					plain: true,
					region: 'north',
					items: [{
							autoHeight: true,
							bodyStyle: 'margin-top: 5px;background:#DFE8F6',
							border: false,
							layout: 'form',
							title: '1. Документы',
							items:[{
								layout: 'column',
								border: false,
								bodyStyle:'background:#DFE8F6',
								hidden: (!getGlobalOptions().orgtype == 'lpu'),
								items: [{
										layout: 'form',
										labelWidth: 115,
										border: false,
										bodyStyle:'background:#DFE8F6',
										items: [{
											xtype: 'sworgcomboex',
											hiddenName: 'Org_id',
											id: 'wpmwOrg_id',
											fieldLabel: 'Организация',
											width: 230,
											disabled: (!getGlobalOptions().superadmin)
										}]
									}, {
										layout: 'form',
										labelWidth: 45,
										border: false,
										bodyStyle:'background:#DFE8F6',
										items: [
											form.mol_mp_combo
										]
									}, {
										layout: 'form',
										labelWidth: 115,
										border: false,
										bodyStyle:'background:#DFE8F6',
										hidden: (getGlobalOptions().orgtype != 'lpu'),
										items: [{
											disabled: true,
											hiddenName: 'LpuBuilding_id',
											fieldLabel: 'Подразделение',
											xtype: 'swlpubuildingglobalcombo',
											id: 'wpmwLpuBuilding_id',
											listeners:{
												'select':function (combo) {
													combo.fireEvent('change',combo);
												}.createDelegate(this),
												'change':function (combo, newValue, oldValue) {
													this.ls_combo.clearValue();
                                                    this.ls_combo.getStore().baseParams.Lpu_id = getGlobalOptions().lpu_id;
                                                    this.ls_combo.getStore().baseParams.LpuBuilding_id = newValue;
                                                    this.ls_combo.clearComboValue();

													this.s_combo.getStore().baseParams.LpuSection_id = null;
													this.s_combo.getStore().baseParams.LpuBuilding_id = newValue;
													this.s_combo.clearComboValue();
												}.createDelegate(this)
											},
											width: 230
										}]
									}, {
										layout: 'form',
										labelWidth: 85,
										border: false,
										bodyStyle:'background:#DFE8F6',
										hidden: (getGlobalOptions().orgtype != 'lpu'),
										items: [
											form.ls_combo
										]
									}, {
										layout: 'form',
										labelWidth: 50,
										border: false,
										bodyStyle:'background:#DFE8F6',
										items: [
											form.s_combo
										]
									}]
								}, {
									layout: 'column',
									border: false,
									bodyStyle:'background:#DFE8F6',
									items: [{
										layout: 'form',
										labelWidth: 115,
										border: false,
										bodyStyle:'background:#DFE8F6',
										items: [{
											xtype: 'swcommonsprcombo',
											width: 330,
											name: 'DrugDocumentType_id',
											comboSubject: 'DrugDocumentType',
											id: 'wpmwDrugDocumentType',
											loadParams: (getGlobalOptions().orgtype == 'lpu' ? {params: {where: ' where DrugDocumentType_id in (2, 3, 6, 10, 12, 15, 17, 18, 22, 33)'}} : {}),
											typeCode: 'int',
											fieldLabel: 'Тип документа',
											value: '',
											listeners: {
												'keydown': form.onKeyDown,
												'change': function(combo, newValue, oldValue) {
													switch (newValue)
													{
														case 2:
															this.findById('wpmwDocumentUcStr_ReasonFrom').show();
															this.findById('wpmwPostmsForm').hide();
															this.findById('wpmwPatientForm').hide();
															break;
														case 11:
															this.findById('wpmwPostmsForm').hide();
															this.findById('wpmwPatientForm').show();
															this.findById('wpmwDocumentUcStr_ReasonFrom').hide();
															break;
														case 22:
															this.findById('wpmwPostmsForm').show();
															this.findById('wpmwPatientForm').show();
															this.findById('wpmwDocumentUcStr_ReasonFrom').hide();
															break;
														default:
															this.findById('wpmwPostmsForm').hide();
															this.findById('wpmwPatientForm').hide();
															this.findById('wpmwDocumentUcStr_ReasonFrom').hide();
															break;
													}
												}.createDelegate(this)
											}
										}]
									}, {
										layout: 'form',
										labelWidth: 115,
										border: false,
										bodyStyle:'background:#DFE8F6',
										items: [{
											xtype: 'swcommonsprcombo',
											width: 300,
											name: 'DrugDocumentStatus_id',
											comboSubject: 'DrugDocumentStatus',
                                            loadParams: {params: {where: ' where DrugDocumentStatus_id in (1, 2, 11)'}},
											typeCode: 'int',
											fieldLabel: 'Статус документа',
											value: '',
											listeners: {
												'keydown': form.onKeyDown
											}
										}]
									}, {
										layout: 'form',
										labelWidth: 55,
										border: false,
										bodyStyle:'background:#DFE8F6',
										items: [{
											xtype: 'textfieldpmw',
											width: 260,
											id: 'wpmwSearch_Num',
											name: 'DocumentUc_Num',
											fieldLabel: '№',
											value: '',
											listeners: {'keydown': form.onKeyDown}
										}]
									}]
								}, {
									layout: 'column',
									border: false,
									bodyStyle:'background:#DFE8F6',
									items: [{
										layout: 'form',
										labelWidth: 115,
										bodyStyle:'background:#DFE8F6',
										border: false,
										items: [{
											xtype: 'swcontragentcombo',
											disabled: false,
											width: 390,
											id: 'wpmwContragent_sid',
											name: 'Contragent_sid',
											hiddenName:'Contragent_sid',
											fieldLabel: 'Поставщик',
											listeners: {
												'keydown': form.onKeyDown,
												'change': function(combo, newValue, oldValue) {
													this.loadStorage('Storage_sid');
												}.createDelegate(this)
											},
											onTrigger2Click: function() {
												var oldValue = this.getValue();
												if (!Ext.isEmpty(oldValue)) {
													this.clearValue();
													this.fireEvent('change', this.getValue(), oldValue);
												}
											}
										}]
									}, {
										layout: 'form',
										labelWidth: 55,
										border: false,
										bodyStyle:'background:#DFE8F6',
										items: [
                                            form.s_s_combo
										]
									}, {
										layout: 'form',
										labelWidth: 55,
										border: false,
										bodyStyle:'background:#DFE8F6',
										items: [
											form.m_s_combo
										]
									}]
								}, {
									layout: 'form',
									labelWidth: 115,
									border: false,
									bodyStyle: 'background:#DFE8F6',
									items: [{
											xtype: 'textfieldpmw',
											width: 150,
											name: 'Org_sINN',
											fieldLabel: 'ИНН поставщика',
											value: '',
											listeners: {'keydown': form.onKeyDown}
										}]
								}, {
									layout: 'column',
									border: false,
									bodyStyle:'background:#DFE8F6',
									items: [{
										layout: 'form',
										labelWidth: 115,
										border: false,
										bodyStyle:'background:#DFE8F6',
										items: [{
											xtype: 'swcontragentcombo',
											disabled: false,
											width: 390,
											id: 'wpmwContragent_tid',
											name: 'Contragent_tid',
											hiddenName:'Contragent_tid',
											fieldLabel: 'Получатель',
											listeners: {
												'keydown': form.onKeyDown,
												'change': function(combo, newValue, oldValue) {
													this.loadStorage('Storage_tid');
												}.createDelegate(this)
											},
											onTrigger2Click: function() {
												var oldValue = this.getValue();
												if (!Ext.isEmpty(oldValue)) {
													this.clearValue();
													this.fireEvent('change', this.getValue(), oldValue);
												}
											}
										}]
									}, {
										layout: 'form',
										labelWidth: 55,
										border: false,
										bodyStyle:'background:#DFE8F6',
										items: [
                                            form.s_t_combo
										]
									}, {
										layout: 'form',
										labelWidth: 55,
										border: false,
										bodyStyle:'background:#DFE8F6',
										items: [
                                            form.m_t_combo
										]
									}]
								}, {
									layout: 'form',
									labelWidth: 115,
									border: false,
									bodyStyle: 'background:#DFE8F6',
									items: [{
											xtype: 'textfieldpmw',
											width: 150,
											name: 'Org_tINN',
											fieldLabel: 'ИНН получателя',
											value: '',
											listeners: {'keydown': form.onKeyDown}
										}]
								}, {
									layout: 'column',
									border: false,
									bodyStyle:'background:#DFE8F6',
									items: [{
										layout: 'form',
										labelWidth: 115,
										border: false,
										hidden: true,
										bodyStyle:'background:#DFE8F6',
										id: 'wpmwPostmsForm',
										items: [{

											xtype: 'textfieldpmw',
											width: 123,
											id: 'wpmwPostms',
											name: 'Postms',
											fieldLabel: 'Постовая м/с',
											value: '',
											listeners: {'keydown': form.onKeyDown}
										}]
									}, {
										layout: 'form',
										labelWidth: 115,
										border: false,
										hidden: true,
										bodyStyle:'background:#DFE8F6',
										id: 'wpmwPatientForm',
										items: [{
											xtype: 'textfieldpmw',
											width: 123,
											id: 'wpmwPatient',
											name: 'Patient',
											fieldLabel: 'Пациент',
											value: '',
											listeners: {'keydown': form.onKeyDown}
										}]
									}]
								}, {
									layout: 'form',
									labelWidth: 115,
									border: false,
									hidden: true,
									bodyStyle:'background:#DFE8F6',
									id: 'wpmwDocumentUcStr_ReasonFrom',
									items: [{
										xtype: 'textfieldpmw',
										fieldLabel: 'Причина списания',
										name: 'DocumentUcStr_Reason',
										width: 123,
										listeners: {'keydown': form.onKeyDown}
									}]
								}]
						}, {
							autoHeight: true,
							bodyStyle: 'margin-top: 5px;background:#DFE8F6',
							border: false,
							layout: 'form',
							title: '2. Медикаменты',
							items:[{
									layout: 'form',
									bodyStyle:'background:#DFE8F6',
									labelWidth: 120,
									border: false,
									items: [{
										xtype: 'textfield',
										fieldLabel: 'МНН',
										name: 'DrugMnn_Name',
										width: 300,
										listeners: {'keydown': form.onKeyDown}
									}]
								},{
									layout: 'form',
									bodyStyle:'background:#DFE8F6',
									labelWidth: 120,
									border: false,
									items: [{
										xtype: 'textfield',
										fieldLabel: 'Торг.наим.',
										name: 'DrugTorg_Name',
										width: 300,
										listeners: {'keydown': form.onKeyDown}
									}]
							}]
						}, {
							autoHeight: true,
							bodyStyle: 'margin-top: 5px;background:#DFE8F6',
							border: false,
							layout: 'form',
							title: '3. Финансирование',
							items:[{
								layout: 'form',
								labelWidth: 140,
								bodyStyle:'background:#DFE8F6',
								border: false,
								items: [{
									xtype: 'textfield',
									width: 280,
									name: 'WhsDocumentUc_Num',
									fieldLabel: 'Номер контракта',
									value: '',
									listeners: {'keydown': form.onKeyDown}
								}]
							}, {
								layout: 'form',
								labelWidth: 140,
								bodyStyle:'background:#DFE8F6',
								border: false,
								items: [{
									xtype: 'swdrugfinancecombo',
									width: 280,
									name: 'DrugFinance_id',
									fieldLabel: 'Ист. финансирования',
									value: '',
									listeners: {'keydown': form.onKeyDown}
								}]
							}, {
								layout: 'form',
								labelWidth: 140,
								bodyStyle:'background:#DFE8F6',
								border: false,
								items: [{
									xtype: 'swwhsdocumentcostitemtypecombo',
									width: 280,
									name: 'WhsDocumentCostItemType_id',
									fieldLabel: 'Статья расхода',
									value: '',
									listeners: {'keydown': form.onKeyDown}
								}]
							}]
						}, {
							autoHeight: true,
							bodyStyle: 'margin-top: 5px;background:#DFE8F6',
							border: false,
							layout: 'form',
							title: '4. Исполнение документов',
							items:[{
								layout: 'column',
								border: false,
								bodyStyle:'background:#DFE8F6',
								items: [{
									layout: 'form',
									labelWidth: 120,
									bodyStyle:'background:#DFE8F6',
									border: false,
									items: [{
										fieldLabel: 'Дата исполнения',
										xtype: 'daterangefield',
										plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false) ],
										width: 180,
										name: 'DocumentUc_date_range',
										listeners: {'keydown': form.onKeyDown}
									}]
								}, {
									layout: 'form',
									labelWidth: 160,
									bodyStyle:'background:#DFE8F6',
									border: false,
									items: [{
										xtype: 'textfieldpmw',
										fieldLabel: 'Исполнитель документа',
										name: 'pmUser',
										width: 250,
										listeners: {'keydown': form.onKeyDown}
									}]
								}]
							}]
						}]
			})],
			keys: [{
				fn: function(e) {
					Ext.getCmp('swWorkPlaceMerchandiserWindow').doSearch();
				},
				key: Ext.EventObject.ENTER,
				scope: this,
				stopEvent: true
			}],
			labelAlign: 'right',
			labelWidth: 130,
			bodyStyle: "background:#DFE8F6",
			region: 'north'
		}),

		this.GridPanel = new Ext.TabPanel({
					activeTab: 0,
					defaults: { bodyStyle: 'padding: 0px;background:#DFE8F6;' },
					bodyStyle: 'background:#DFE8F6;',
					id: 'MerchandiserTabPanel',
					layoutOnTabChange: true,
					listeners: {
						'tabchange': function(panel, tab) {
                            if (this.GridPanel.enabledSearch) {
                                this.doSearch();
                            }
						}.createDelegate(this)
					},
					plain: true,
					region: 'center',
					items: [{
							bodyStyle: 'background:#DFE8F6;',
							border: false,
							layout: 'border',
							region: 'center',
							title: 'Документы учета',
							items:[new sw.Promed.ViewFrame({
								id: 'wpmwWorkPlaceGridPanel',
								autoExpandColumn: 'autoexpand',
								bodyStyle: 'background:#DFE8F6;',
								border: false,
								grouping: true,
								groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length.inlist([2,3,4]) ? "записи" : "записей"]})',
								groupingView: {showGroupName: true, showGroupsText: true},
								groupField: 'DrugDocumentType_Name',
								actions: [
									//{name:'action_add', handler: function() { this.SelectCreateTypeWindow.show(); }.createDelegate(this)},
									{
										id: 'wpmw_action_add',
										name: 'action_add',
										tooltip: 'Добавить',
										disabled: getGlobalOptions().orgtype == 'dep' && getRegionNick() == 'ufa',
										handler: function() {
                                            form.SelectCreateTypeWindow.show();
                                        }.createDelegate(this)
									},
									{name:'action_edit' , handler: function() {
											var params = {
												callback: function() {form.doSearch(); this.hide(); },
												isSmpMainStorage: form.isSmpMainStorage,
												userMedStaffFact: form.userMedStaffFact,
												action: "edit"
											};

											var selection = form.findById('wpmwWorkPlaceGridPanel').getGrid().getSelectionModel().getSelected();
											var fields = form.findById('wpmwWorkPlaceGridPanel').stringfields;

											for (var i = 0; i<fields.length; i++) {
												if (fields[i].key || fields[i].isparams) {
													params[ fields[i].name ] = selection.get( fields[i].name );
												}
											}
											params.owner = this.findById('wpmwWorkPlaceGridPanel');
											getWnd(this.findById('wpmwWorkPlaceGridPanel').editformclassname).show( params );
										}.createDelegate(this)
									},
									{name:'action_view'},
									//{name:'action_delete', url: $urlDelete},
									{name:'action_delete', url: this.sprType != 'dbo' ? '/?c=DocumentUc&m=delete' : '/?c=DocumentUc&m=farm_delete' },
									{name: 'action_refresh'},
									{name: 'action_print'}
								],
								autoLoadData: false,
								paging: true,
								pageSize: 100,
								stringfields: [
									// Поля для отображение в гриде
									{name: 'DocumentUc_id', type: 'int', header: 'ID', key: true},
									{name: 'DrugDocumentStatus_Name', header: 'Статус', width: 100},
									{name: 'DrugDocumentStatus_Code', hidden: true, isparams: true},
									{name: 'DrugDocumentType_Code', hidden: true, isparams: true},
									{name: 'Contragent_sName', type: 'string', hidden: true},
									{name: 'Contragent_tName', type: 'string', hidden: true},
									{name: 'Storage_sName', type: 'string', hidden: true},
									{name: 'Storage_tName', type: 'string', hidden: true},
									{name: 'DrugDocumentType_Name', header: 'Тип документа учета', group: true, sort: true, direction: 'ASC', width: 100},
									{name: 'DocumentUc_Num', header: '№ док-та', width: 100},
									{name: 'DocumentUc_setDate', header: 'Дата подписания', type: 'date', width: 100},
									{name: 'DocumentUc_txtdidDate', header: 'Дата поставки', type: 'date', width: 100},
									{name: 'Org_sINN', width: 120, header: 'ИНН поставщика', type: 'string'},
									{name: 'sFullName', header: 'Поставщик', width: 260, renderer: function(v, p, record) {
										var fullName = '--------------';
										if (!Ext.isEmpty(record.get('Contragent_sName'))) {
											fullName = record.get('Contragent_sName');
										}
										if (!Ext.isEmpty(record.get('Storage_sName'))) {
											fullName += '<br/>'+record.get('Storage_sName');
										}
										return fullName;
									}},
								{name: 'Org_tINN', width: 120, header: 'ИНН получателя', type: 'string'},
									{id: 'autoexpand', name: 'tFullName', header: 'Получатель', width: 120, renderer: function(v, p, record) {
										var fullName = '--------------';
										if (!Ext.isEmpty(record.get('Contragent_tName'))) {
											fullName = record.get('Contragent_tName');
										}
										if (!Ext.isEmpty(record.get('Storage_tName'))) {
											fullName += '<br/>'+record.get('Storage_tName');
										}
										return fullName;
									}},
									{name: 'DrugFinance_Name', width: 150, header: 'Источник финансирования', type: 'string', align: 'left'},
									{name: 'WhsDocumentCostItemType_Name', width: 150, header: 'Статья расхода', type: 'string', align: 'left'},
									{name: 'SubAccountType_tName', width: 150, header: 'Субсчет', type: 'string', align: 'left', hidden: getGlobalOptions().region.nick != 'ufa'},
									{name: 'DocumentUcStr_NdsSum', width: 110, header: 'Сумма', type: 'money', align: 'right'},
                                    {name: 'Supply_State', width: 130, header: 'Состояние поставки', type: 'string'},
                                    {name: 'StorageWork_State', width: 200, header: 'Выполнение работ', type: 'string'},
									{name: 'isKM', header: 'isKM', type: 'int', hidden: true}
								],
								object: 'DocumentUc',
								params: {ARMType: getGlobalOptions().OrgFarmacy_id == 1 ? 'storehouse' : 'undefined'},
								editformclassname: 'swDocumentUcEditWindow',			
								dataUrl: '/?c=Farmacy&m=load&method=AllDok',
								//dataUrl: this.sprType != 'dbo' ? '/?c=Farmacy&m=load&method=AllDok' : '/?c=Farmacy&m=load&method=farm_AllDok',
								root: 'data',
								totalProperty: 'totalCount',
								//title: 'Журнал рабочего места',
								onRowSelect: function(sm, index, record) {
									this.editformclassname = record.get('DrugDocumentType_Code') && record.get('DrugDocumentType_Code').inlist([2,3,6,10,11,12,15,16,17,18,21,22,23,25,26,27,28,29,30,31,32,33,34]) ? form.DocumentUcEditWindow : 'swDocumentUcEditWindow';
								    if (form.sprType != 'dbo') {
                                        this.getAction('action_import').setHidden(record.get('DrugDocumentType_Code') != 6);
                                    }
                                    this.ViewActions.action_wpm_actions.setDisabled(Ext.isEmpty(record.get('DocumentUc_id')));
									if (getGlobalOptions().orgtype == 'dep' && getRegionNick() == 'ufa') //  Для ГКУ
										this.ViewActions.action_edit.setDisabled(true);
								},
								onLoadData: function(sm, index, record) {
									if (this.getGrid().getStore().totalLength <= 0 || this.getGrid().getStore().getAt(0).get('DocumentUc_id') <= 0) {
										this.getGrid().getStore().removeAll({addEmptyRecord: false});
									}
								},
								updateContextMenu: function() {
									var $GridPanel = Ext.getCmp('wpmwWorkPlaceGridPanel');
									var rowSelected = $GridPanel.getGrid().getSelectionModel().getSelected();
									if (rowSelected != undefined) {
										var actionObj = new Object();
										actionObj.isHidden = 1;
										actionObj.btnDel_isHidden = 0
//										if (rowSelected.data.DrugDocumentStatus_Code == 4 && rowSelected.data.DrugDocumentType_Code == 11) {
//											actionObj.isHidden = 0
//										}
										if (form.sprType == 'dbo' && (rowSelected.data.DrugDocumentStatus_Code == 4 && rowSelected.data.DrugDocumentType_Code.inlist ([11, 17]))
												|| (rowSelected.data.DrugDocumentStatus_Code == 1 && rowSelected.data.DrugDocumentType_Code.inlist ([11])) ) {
										    var DocumentUc_setDate = rowSelected.data.DocumentUc_setDate;
										    var setDate = DocumentUc_setDate.getFullYear() + '/' + String(DocumentUc_setDate.getMonth() + 1).replace(/^(.)$/, "0$1")  + '/' +  String(DocumentUc_setDate.getDate()).replace(/^(.)$/, "0$1");
										    //console.log ('setDate! = ' + setDate + ': DrugPeriodClose_DT = ' + form.DrugPeriodClose_DT + ': WhsDocumentUcInvent_DT = ' + form.WhsDocumentUcInvent_DT);
										    // Если дата закрытия меньше даты документа, то открываем кнопку отмены
										    if (setDate >= form.DrugPeriodClose_DT && setDate <= form.WhsDocumentUcInvent_DT && rowSelected.data.isKM != 2) {
												actionObj.isHidden = 0
											}
										}
										if (form.sprType == 'dbo' && ((rowSelected.data.DrugDocumentStatus_Code == 4 || rowSelected.data.DrugDocumentStatus_Code == 10))
											|| (rowSelected.data.DrugDocumentStatus_Code == 1 && rowSelected.data.DrugDocumentType_Code.inlist ([11]))) {
										    actionObj.btnDel_isHidden = 1
										}
										//console.log('actionObj.isHidden = ' + actionObj.isHidden);
										$GridPanel.getAction('action_Canceling').setHidden(actionObj.isHidden);
										$GridPanel.getAction('action_delete').setHidden(actionObj.btnDel_isHidden);
									}
								}
							})]
						}, {
							bodyStyle: 'background:#DFE8F6',
							border: false,
							layout: 'border',
							region: 'center',
							title: 'Заявки',
							items:[new sw.Promed.ViewFrame({
								id: 'wpmwWorkPlaceGridRequestPanel',
								autoExpandColumn: 'autoexpand',
								bodyStyle: 'background:#DFE8F6;',
								border: false,
								//grouping: true,
								//groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length.inlist([2,3,4]) ? "записи" : "записей"]})',
								//groupingView: {showGroupName: true, showGroupsText: true},
								//groupField: 'DrugDocumentType_Name',
								actions: [
									{name:'action_add', handler: function(){this.SelectCreateTypeRequestWindow.show(); }.createDelegate(this)},
									{name:'action_edit', handler: function(){form.findById('wpmwWorkPlaceGridRequestPanel').openEditForm('edit');}},
									{name:'action_view', handler: function(){form.findById('wpmwWorkPlaceGridRequestPanel').openEditForm('view');}},
									{name:'action_delete', handler: function(){this.deleteRequest()}.createDelegate(this)},
									{name: 'action_refresh'},
									{name: 'action_print'}
								],
								autoLoadData: false,
								paging: true,
								pageSize: 100,
								stringfields: [
									// Поля для отображение в гриде
									{name: 'WhsDocumentUc_id', type: 'int', header: 'ID', key: true},
									{name: 'WhsDocumentStatusType_Name', header: 'Статус', width: 100},
									{name: 'WhsDocumentStatusType_Code', hidden: true, isparams: true},
									//{name: 'DrugDocumentType_Code', hidden: true, isparams: true},
									//{name: 'Contragent_sName', type: 'string', hidden: true},
									//{name: 'Contragent_tName', type: 'string', hidden: true},
									{name: 'Storage_sName', type: 'string', hidden: true},
									{name: 'Storage_tName', type: 'string', hidden: true},
									{name: 'allow_delete', type: 'int', hidden: true},
									{name: 'allow_execute', type: 'int', hidden: true},
									{name: 'RequestDirectionType_id', type: 'int', hidden: true},
									{name: 'RequestDirectionType_Name', header: 'Вид заявки', group: true, sort: true, direction: 'ASC', width: 100},
									{name: 'WhsDocumentClass_Name', header: 'Класс документа учета', /*group: true, sort: true, direction: 'ASC',*/ width: 100},
									{name: 'WhsDocumentClass_Code', hidden: true},
									{name: 'WhsDocumentUc_Num', header: '№ док-та', width: 100},
									{name: 'WhsDocumentUc_Date', header: 'Дата подписания', type: 'date', width: 100},
									//{name: 'DocumentUc_txtdidDate', header: 'Дата поставки', type: 'date', width: 100},
									{name: 'Org_tid_Nick', header: 'Поставщик', width: 260, renderer: function(v, p, record) {
										var fullName = '--------------';
										if (!Ext.isEmpty(record.get('Org_tid_Nick'))) {
											fullName = record.get('Org_tid_Nick');
										}
										if (!Ext.isEmpty(record.get('Storage_tName'))) {
											fullName += '<br/>'+record.get('Storage_tName');
										}
										return fullName;
									}},
									{id: 'autoexpand', name: 'Org_sid_Nick', header: 'Потребитель', width: 120, renderer: function(v, p, record) {
										var fullName = '--------------';
										if (!Ext.isEmpty(record.get('Org_sid_Nick'))) {
											fullName = record.get('Org_sid_Nick');
										}
										if (!Ext.isEmpty(record.get('Storage_sName'))) {
											fullName += '<br/>'+record.get('Storage_sName');
										}
										return fullName;
									}},
									{name: 'WhsDocumentUc_Sum', width: 110, header: 'Сумма', type: 'money', align: 'right'}
								],
								object: 'WhsDocumentUc',
								params: {ARMType: getGlobalOptions().OrgFarmacy_id == 1 ? 'storehouse' : 'undefined'},
								editformclassname: 'swWhsDocumentUcEditWindow',			
								dataUrl: '/?c=Farmacy&m=loadWhsDocumentRequestList',
								root: 'data',
								totalProperty: 'totalCount',
								onRowSelect: function(sm, index, record) {
									var WhsDocumentClass_Code = Number(record.get('WhsDocumentClass_Code'));
									var WhsDocumentStatusType_Code = Number(record.get('WhsDocumentStatusType_Code'));

									var RequestDirectionType_id = Number(record.get('RequestDirectionType_id'));

									var allow_edit = (!this.readOnly && RequestDirectionType_id == 2 && WhsDocumentStatusType_Code.inlist([1,4,5]));
									var allow_delete = (!this.readOnly && record.get('allow_delete') && RequestDirectionType_id == 2 && WhsDocumentStatusType_Code.inlist([1,4,5]));
									var allow_cancel = (!this.readOnly && record.get('allow_delete') && RequestDirectionType_id == 2 && WhsDocumentStatusType_Code.inlist([1,4,5,7]));
									var allow_execute = (!this.readOnly && record.get('allow_execute') && WhsDocumentClass_Code == 2 && !WhsDocumentStatusType_Code.inlist([1,3,6,7,8]));

									this.ViewActions.action_edit.setDisabled(!allow_edit);
									this.ViewActions.action_delete.setDisabled(!allow_delete);
									this.ViewActions.action_wpm_cancel.setDisabled(!allow_cancel);
									this.ViewActions.action_wpm_execute.setDisabled(!allow_execute);
								},
								onLoadData: function(sm, index, record) {
									if (this.getGrid().getStore().totalLength <= 0 || this.getGrid().getStore().getAt(0).get('WhsDocumentUc_id') <= 0) {
										this.getGrid().getStore().removeAll({addEmptyRecord: false});
									}
								},
								updateContextMenu: function() {
									var $GridPanel = Ext.getCmp('wpmwWorkPlaceGridRequestPanel');
									var rowSelected = $GridPanel.getGrid().getSelectionModel().getSelected();
									if (rowSelected != undefined) {
										var actionObj = new Object();
										actionObj.isHidden = 1;
										//console.log('rowSelected');
										//console.log(rowSelected.data);
										/*if (rowSelected.data.DrugDocumentStatus_Code == 4 && rowSelected.data.DrugDocumentType_Code == 11) {
											actionObj.isHidden = 0
										}*/
										//console.log('actionObj.isHidden = ' + actionObj.isHidden);
										//$GridPanel.getAction('action_Canceling').setHidden(actionObj.isHidden);
									}
								},
								openEditForm: function(action) {
                                    var params = {
                                        callback: function() {
                                        	form.doSearch();
                                        	this.hide();
                                        },
                                        isSmpMainStorage: form.isSmpMainStorage,
                                        userMedStaffFact: form.userMedStaffFact,
                                        action: action
                                    };
                                    var selection = this.getGrid().getSelectionModel().getSelected();
                                    var fields = this.stringfields;

                                    for (var i = 0; i < fields.length; i++) {
                                        if (fields[i].key || fields[i].isparams) {
                                            params[fields[i].name] = selection.get(fields[i].name);
                                        }
                                    }

                                    getWnd(this.editformclassname).show(params);
								}
							})]
						}]
		});

		/*this.GridPanel = new sw.Promed.ViewFrame({
			id: 'wpmwWorkPlaceGridPanel',
			region: 'center',
			autoExpandColumn: 'autoexpand',
			grouping: true,
			groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length.inlist([2,3,4]) ? "записи" : "записей"]})',
			groupingView: {showGroupName: true, showGroupsText: true},
			groupField: 'DrugDocumentType_Name',
			actions: [
				{name:'action_add', handler: function() { this.SelectCreateTypeWindow.show(); }.createDelegate(this)},
				{name:'action_edit' , handler: function() {
						var params = {
							callback: function() {form.doSearch(); this.hide(); },
							isSmpMainStorage: form.isSmpMainStorage,
							userMedStaffFact: form.userMedStaffFact,
							action: "edit"
						};

						var selection = form.GridPanel.getGrid().getSelectionModel().getSelected();
						var fields = form.GridPanel.stringfields;

						for (var i = 0; i<fields.length; i++) {
							if (fields[i].key || fields[i].isparams) {
								params[ fields[i].name ] = selection.get( fields[i].name );
							}
						}

						getWnd(this.GridPanel.editformclassname).show( params );
					}.createDelegate(this)
				},
				{name:'action_view'},
				{name:'action_delete', url: '/?c=DocumentUc&m=delete'},
				{name: 'action_refresh'},
				{name: 'action_print'}
			],
			autoLoadData: false,
			paging: true,
			pageSize: 100,
			stringfields: [
				// Поля для отображение в гриде
				{name: 'DocumentUc_id', type: 'int', header: 'ID', key: true},
				{name: 'DrugDocumentStatus_Name', header: langs('Статус'), width: 100},
				{name: 'DrugDocumentStatus_Code', hidden: true, isparams: true},
				{name: 'DrugDocumentType_Code', hidden: true, isparams: true},
				{name: 'Contragent_sName', type: 'string', hidden: true},
				{name: 'Contragent_tName', type: 'string', hidden: true},
				{name: 'Storage_sName', type: 'string', hidden: true},
				{name: 'Storage_tName', type: 'string', hidden: true},
				{name: 'DrugDocumentType_Name', header: langs('Тип документа учета'), group: true, sort: true, direction: 'ASC', width: 100},
				{name: 'DocumentUc_Num', header: langs('№ док-та'), width: 100},
				{name: 'DocumentUc_setDate', header: langs('Дата подписания'), type: 'date', width: 100},
				{name: 'DocumentUc_txtdidDate', header: langs('Дата поставки'), type: 'date', width: 100},
				{name: 'sFullName', header: langs('Поставщик'), width: 260, renderer: function(v, p, record) {
					var fullName = '--------------';
					if (!Ext.isEmpty(record.get('Contragent_sName'))) {
						fullName = record.get('Contragent_sName');
					}
					if (!Ext.isEmpty(record.get('Storage_sName'))) {
						fullName += '<br/>'+record.get('Storage_sName');
					}
					return fullName;
				}},
				{id: 'autoexpand', name: 'tFullName', header: langs('Получатель'), width: 120, renderer: function(v, p, record) {
					var fullName = '--------------';
					if (!Ext.isEmpty(record.get('Contragent_tName'))) {
						fullName = record.get('Contragent_tName');
					}
					if (!Ext.isEmpty(record.get('Storage_tName'))) {
						fullName += '<br/>'+record.get('Storage_tName');
					}
					return fullName;
				}},
				{name: 'DocumentUcStr_NdsSum', width: 110, header: langs('Сумма'), type: 'money', align: 'right'}
			],
			object: 'DocumentUc',
			params: {ARMType: getGlobalOptions().OrgFarmacy_id == 1 ? 'storehouse' : 'undefined'},
			editformclassname: 'swDocumentUcEditWindow',			
			dataUrl: '/?c=Farmacy&m=load&method=AllDok',
			root: 'data',
			totalProperty: 'totalCount',
			title: langs('Журнал рабочего места'),
			onRowSelect: function(sm, index, record) {
				this.editformclassname = record.get('DrugDocumentType_Code') && record.get('DrugDocumentType_Code').inlist([2,3,6,10,11,12,15,16,17,18]) ? 'swNewDocumentUcEditWindow' : 'swDocumentUcEditWindow';
			},
			onLoadData: function(sm, index, record) {
				if (this.getGrid().getStore().totalLength <= 0 || this.getGrid().getStore().getAt(0).get('DocumentUc_id') <= 0) {
					this.getGrid().getStore().removeAll({addEmptyRecord: false});
				}
			},
			updateContextMenu: function() {
				var $GridPanel = Ext.getCmp('wpmwWorkPlaceGridPanel');
				var rowSelected = $GridPanel.getGrid().getSelectionModel().getSelected();
				if (rowSelected != undefined) {
					var actionObj = new Object();
					actionObj.isHidden = 1;
					//console.log('rowSelected');
					//console.log(rowSelected.data);
					if (rowSelected.data.DrugDocumentStatus_Code == 4 && rowSelected.data.DrugDocumentType_Code == 11) {
						actionObj.isHidden = 0
					}
					//console.log('actionObj.isHidden = ' + actionObj.isHidden);
					$GridPanel.getAction('action_Canceling').setHidden(actionObj.isHidden);
				}
			}
		});*/
		
		this.GetDocNumWindow = new Ext.Window({
			id: 'wpmw_GetDocNumWindow',
			width : 500,
			height : 120,
			modal: true,
			resizable: false,
			autoHeight: false,
			closeAction :'hide',
			border : false,
			plain : false,
			title: 'Импорт накладной из ИС ТОО СК "Фармация"',
			items: [
				new Ext.form.FormPanel({
					height : 60,
					layout : 'form',
					border : false,
					frame : true,
					style : 'padding: 10px',
					labelWidth : 120,
					items: [{
						allowBlank: false,
						id: 'wpmw_DocNum',
						xtype: 'textfield',
						fieldLabel: 'Номер накладной',
						width: 300
					}]
				})
			],
			buttons:[
				{
					text: langs('Импорт'),
					iconCls : 'ok16',
					handler: function(button, event) {
						var base_wnd = Ext.getCmp('swWorkPlaceMerchandiserWindow');
						var Document_Number = Ext.getCmp('wpmw_DocNum').getValue();

						if(Ext.isEmpty(Document_Number)){
							sw.swMsg.alert('Ошибка', 'Поле "Номер накладной" обязательно для заполнения');
							return false;
						}

						form.getLoadMask(langs('Загрузка...')).show();
						var params = {
							Document_Number: Document_Number,
							MedService_id: form.userMedStaffFact.MedService_id
						};
						Ext.Ajax.request({
							url: '/?c=ServiceEFIS&m=importDocumentUc',
							params: params,
							callback: function(options, success, response) {
								form.getLoadMask().hide();
								var response_obj = Ext.util.JSON.decode(response.responseText);
								if(!(response_obj.success))
								{
									sw.swMsg.alert('Ошибка импорта',response_obj.errorMsg);
								}
								else
									sw.swMsg.alert('Протокол импорта', response_obj.info);
								form.doSearch();
							}.createDelegate(this),
							failure: function()  {
								form.getLoadMask().hide();
							}
						});
						Ext.getCmp('wpmw_GetDocNumWindow').hide();
					}.createDelegate(this)
				},
				{
					text: '-'
				}, {
					handler: function() {
						this.ownerCt.hide();
					},
					iconCls: 'close16',
					text: BTN_FRMCLOSE
				}
			],
			buttonAlign : "right"
		});
		this.SelectCreateTypeWindow = new Ext.Window({
			id: 'wpmw_SelectCreateTypeWindow',
			closable: false,
			width : 500,
			height : 120,
			modal: true,
			resizable: false,
			autoHeight: false,
			closeAction :'hide',
			border : false,
			plain : false,
			title: langs('Выбор типа документа'),
			items : [new Ext.form.FormPanel({
				height : 60,
				layout : 'form',
				border : false,
				frame : true,
				style : 'padding: 10px',
				labelWidth : 120,
				items : [{
					id: 'wpmw_createtypecombo',
					xtype:'combo',
					store: new Ext.data.SimpleStore({
						id: 0,
						fields: [
							'code',
							'name',
							'type_code'
						],
						data: [							
							['create_dokost', langs('Документ ввода остатков'), 3],
							['create_doknak', langs('Приходная накладная'), 6],
							['create_docras', langs('Расходная накладная'), 10],
							['create_dokspis', langs('Документ списания'), 2],
							['create_docnvp', langs('Накладная на внутреннее перемещение'), 15],
							['create_docvoznakr', langs('Возвратная накладная (расходная)'), 17],
							['create_packsmp', langs('Пополнение укладки со склада подстанции'), 20],
							['create_docpro', langs('Списание в производство'), 23],
							//['create_docoprih', 'Оприходование', 12],
							['create_docuc', 'Учет готовой продукции', 22],
							['create_docnpvs', 'Накладная на перемещение внутри склада', 31],
							['create_doknakvo', 'Приход в отделение', 32],
							['create_docrazspis', 'Разукомплектация', 34]
						]
					}),
					displayField: 'name',
					valueField: 'code',
					editable: false,
					allowBlank: false,
					mode: 'local',
					forceSelection: true,
					triggerAction: 'all',
					fieldLabel: langs('Тип документа'),
					width:  300,
					selectOnFocus: true,
					listeners: {
						'expand': function() {
                            this.setStoreFilter();
						}
					},
                    setStoreFilter: function() {
                        var org_type = getGlobalOptions().orgtype;
                        var is_smp_sub = form.isSmpSubStorage;

                        this.getStore().clearFilter();
                        this.getStore().filterBy(function(record){
                            return (
                                ((is_smp_sub || record.get('code') != 'create_packsmp') && //Пополнение укладки со склада подстанции
                                    (org_type != 'contractor' || record.get('code') == 'create_docras') //Поставщикам доступно только создание расходной накладной
									//  Доступен приход в отделение только уфимской МО
									&& record.get('code') != 'create_doknakvo')
								|| (org_type == 'lpu' && getGlobalOptions().region.nick == 'ufa' && record.get('code') == 'create_doknakvo')
                                );
                        });
                    },
                    setFirstDocType: function() {
                        this.setStoreFilter();
                        if (this.getStore().getCount() > 0) {
                            this.setValue(this.getStore().getAt(0).get(this.valueField));
                        } else {
                            this.setValue(null);
                        }
                    },
					getSelectedRecordData: function() {
						var combo = this;
						var value = combo.getValue();
						var data = new Object();
						if (!Ext.isEmpty(value)) {
							var idx = this.getStore().findBy(function(record) {
								return (record.get('code') == value);
							});
							if (idx > -1) {
								Ext.apply(data, this.getStore().getAt(idx).data);
							}
						}
						return data;
					}
				}]
			})],
			buttons : [{
				text : langs('Выбрать'),
				iconCls : 'ok16',
				handler : function(button, event) {
					var doc_type_combo = Ext.getCmp('wpmw_createtypecombo');
					var doc_type_data = doc_type_combo.getSelectedRecordData();
					var base_wnd = getWnd('swWorkPlaceMerchandiserWindow');

					if (!Ext.isEmpty(doc_type_data.code)) {
						var show_params = {
							callback: function() { base_wnd.doSearch(); this.hide(); },
							action: 'add',
							DrugDocumentType_Code: doc_type_data.type_code,
							userMedStaffFact: base_wnd.userMedStaffFact
						};

						if (base_wnd.mol_mp_combo.getValue() > 0) {
							show_params.MolMedPersonal_id = base_wnd.mol_mp_combo.getValue();
						}

						switch(doc_type_data.code) {
							case 'create_dokost':
							case 'create_doknak':
							case 'create_docoprih':
							case 'create_docuc':
								show_params.FormParams = {
									Contragent_tid: getGlobalOptions().Contragent_id
								};
								getWnd(form.DocumentUcEditWindow).show(show_params);
								break;
							case 'create_docras':
							case 'create_dokspis':
							case 'create_docnvp':
							case 'create_packsmp':
							case 'create_docpro':
							case 'create_docrazspis':
								show_params.FormParams = {
									Contragent_sid: getGlobalOptions().Contragent_id
								};
								getWnd(form.DocumentUcEditWindow).show(show_params);
								break;
							case 'create_docnpvs':
							case 'create_doknakvo':
								show_params.FormParams = {
									Contragent_sid: getGlobalOptions().Contragent_id,
									Contragent_tid: getGlobalOptions().Contragent_id
								};
								getWnd(form.DocumentUcEditWindow).show(show_params);
								break;
							case 'create_docvoznakr':
								if (form.sprType != 'dbo') {
									getWnd('swDocNakSelectWindow').show({
										onSelect: function(data) {
											if (data && data[0]) {
												data[0].DrugDocumentType_id = show_params.DrugDocumentType_Code; //для данного типа ид совпадает с кодом
												data[0].DrugDocumentType_Code = show_params.DrugDocumentType_Code;
												if (getGlobalOptions().Contragent_id) {
													data[0].Contragent_sid = getGlobalOptions().Contragent_id;
												}
												show_params.FormParams = data[0];
												getWnd(form.DocumentUcEditWindow).show(show_params);
											} else {
												sw.swMsg.alert(langs('Ошибка'),langs('Накладная с заданными параметрами не найдена.'));
											}
										}
									});
								} else {
									//  Для аптеки уфы свое формирование возврата
									var data = {};
									data.CurrentDate = getGlobalOptions().date;
									data.Contragent_id = getGlobalOptions().Contragent_id;
									data.Contragent_sid = getGlobalOptions().Contragent_id;
									data.ContragentTidType_code = 6;
									data.Contragent_Name = getGlobalOptions().Contragent_Name;
									data.Org_id = getGlobalOptions().Org_id;
									data.DrugDocumentType_id = 17;
									data.DrugDocumentType_Code = 17;
									getWnd(form.DocumentUcEditWindow).show({
										callback: function() {base_wnd.doSearch(); this.hide(); },
										action: 'add',
										DrugDocumentType_id: data.DrugDocumentType_id,
										DrugDocumentType_Code: data.DrugDocumentType_Code,
										FormParams: data,
										userMedStaffFact: base_wnd.userMedStaffFact
									})
								}
								break;
							default:
								sw.swMsg.alert(langs('Ошибка'),langs('Неверный тип документа.'));
								break;
						}
					}

					Ext.getCmp('wpmw_SelectCreateTypeWindow').hide();
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
			buttonAlign : "right",
			listeners: {
			    'beforeshow': function() {
                    var type_combo = Ext.getCmp('wpmw_createtypecombo');
                    type_combo.setFirstDocType();
                }
			}
		});
		this.SelectCreateTypeRequestWindow = new Ext.Window({
			id: 'wpmw_SelectCreateTypeRequestWindow',
			closable: false,
			width : 500,
			height : 120,
			modal: true,
			resizable: false,
			autoHeight: false,
			closeAction :'hide',
			border : false,
			plain : false,
			title: 'Выбор типа документа',
			items : [new Ext.form.FormPanel({
				height : 60,
				layout : 'form',
				border : false,
				frame : true,
				style : 'padding: 10px',
				labelWidth : 120,
				items : [{
					id: 'wpmw_createtyperequestcombo',
					xtype:'combo',
					store: new Ext.data.SimpleStore({
						id: 0,
						fields: [
							'code',
							'name'
						],
						data: [							
							['request_get', 'Заявка на поставку'],
							['request_require', 'Заявка-требование']
						]
					}),
					displayField: 'name',
					valueField: 'code',
					editable: false,
					allowBlank: false,
					mode: 'local',
					forceSelection: true,
					triggerAction: 'all',
					fieldLabel: 'Тип документа',
					width:  300,
					value: 'request_get',
					selectOnFocus: true,
					listeners: {
						'expand': function() {
							if (form.isSmpSubStorage) {
								this.getStore().clearFilter();
							} else {
								this.getStore().filterBy(function(record){
									return (record.get('code') != 'create_packsmp'); //Пополнение укладки со склада подстанции
								});
							}
						}
					}
				}]
			})],
			buttons : [{
				text : "Выбрать",
				iconCls : 'ok16',
				handler : function(button, event) {
					var create_type = Ext.getCmp('wpmw_createtyperequestcombo').getValue();
					var base_wnd = Ext.getCmp('swWorkPlaceMerchandiserWindow');
                    var request_grid = base_wnd.findById('wpmwWorkPlaceGridRequestPanel');

					switch(create_type) {
						case 'request_get':
							getWnd('swWhsDocumentUcEditWindow').show({
                                owner: request_grid,
								callback: function() {base_wnd.doSearch(); this.hide(); },
								action: 'add',
								WhsDocumentClass_id: 1,
								WhsDocumentClass_Code: 1,
								userMedStaffFact: base_wnd.userMedStaffFact
							});
							break;
						case 'request_require':
							getWnd('swWhsDocumentUcEditWindow').show({
                                owner: request_grid,
								callback: function() {base_wnd.doSearch(); this.hide(); },
								action: 'add',
								WhsDocumentClass_id: 2,
								WhsDocumentClass_Code: 2,
								userMedStaffFact: base_wnd.userMedStaffFact
							});
							break;
						default: 
							sw.swMsg.alert('Ошибка','Неверный тип документа.');
							break;
					};
					Ext.getCmp('wpmw_SelectCreateTypeRequestWindow').hide();
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
		buttons: [{
					id: this.id+'BtnSearch',
					style: "margin-left: 50px",
					text: 'Найти',
					iconCls: 'search16',
					handler: function()	{this.doSearch();}.createDelegate(this)
				}, {
					id: this.id+'BtnClear',
					text: 'Сброс',
					iconCls: 'clear16',
					handler: function() {this.doReset();}.createDelegate(this)
				}, {
					text: '-'
				}, {
					text: BTN_FRMHELP,
					iconCls: 'help16',
					handler: function(button, event) {
						ShowHelp(this.ownerCt.title);
					}
				}, {
					handler: function() {
						this.ownerCt.hide();
					},
					iconCls: 'close16',
					text: BTN_FRMCLOSE
				}]
		});
		sw.Promed.swWorkPlaceMerchandiserWindow.superclass.initComponent.apply(this, arguments);
	}
});