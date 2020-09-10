/**
 * swLpuAdminWorkPlaceWindow - окно рабочего места администратора ЛПУ
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/projects/promed
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009-2011, Swan.
 * @author       Chebukin Alexander
 * @prefix       lawpw
 * @version      март 2011
 */
/*NO PARSE JSON*/


sw.Promed.swLpuAdminWorkPlaceWindow = Ext.extend(sw.Promed.BaseForm,
{
	objectName: 'swLpuAdminWorkPlaceWindow',
	objectSrc: '/jscore/Forms/Common/swLpuAdminWorkPlaceWindow.js',
	closable: true,
	closeAction: 'hide',
	layout: 'border',
	maximized: true,
	title: langs('Рабочее место администратора ЛПУ'),
	iconCls: 'admin16',
	id: 'swLpuAdminWorkPlaceWindow',
	listeners: {
		hide: function() {
			this.FilterPanel.getForm().reset();
			this.setTitleFieldSet();
		}
	},
	show: function()
	{
		sw.Promed.swLpuAdminWorkPlaceWindow.superclass.show.apply(this, arguments);
		/*var loadMask = new Ext.LoadMask(Ext.get('swLpuAdminWorkPlaceWindow'), {msg: LOAD_WAIT});
		loadMask.show();
		var form = this;

		form.loadGridWithFilter(true);
		*/
		if (arguments[0]) {
			sw.Promed.MedStaffFactByUser.setMenuTitle(this, arguments[0]);
		}
		this.ARMType = arguments[0].ARMType;

		if(!this.Grid.getAction('action_sync')) {
			this.Grid.addActions({
				name: 'action_sync',
				hidden: !isAdmin && !isLpuAdmin(),
				text: langs('Перекэшировать данные'),
				tooltip: langs('Перекэшировать данные'),
				handler: this.syncLdapAndCacheUserData.createDelegate(this)
			});
		}

		//loadMask.hide();
		var store = this.Grid.ViewGridPanel.getStore();
		store.baseParams.Org_id = getGlobalOptions().org_id;
		this.doSearch();


	},
	clearFilters: function ()
	{
		this.findById('lawpwOrg_Nick').setValue('');
		this.findById('lawpwOrg_Name').setValue('');
	},
	loadGridWithFilter: function(clear)
	{
		var form = this;
		if (clear)
			form.clearFilters();
		var Org_id = getGlobalOptions().org_id;
		var OrgNick = this.findById('lawpwOrg_Nick').getValue();
		var OrgName = this.findById('lawpwOrg_Name').getValue();
		var filters = {Org_id: Org_id, Nick: OrgNick, Name: OrgName, start: 0, limit: 100, mode: 'lpu'};
		form.LpuGrid.loadData({globalFilters: filters});
	},

	editUser: function(action) {
		var grid = this.Grid.ViewGridPanel,
			record = grid.getSelectionModel().getSelected();

		if( !record && action !== 'add') return false;

		var user_login = ( action !== 'add' ) ? record.get('login') : 0;

		var params = {
			action: action,
			fields: {
				action: action,
				org_id: getGlobalOptions().org_id,
				user_login: user_login
			},
			owner: this,
			callback: function(owner) {
				this.Grid.ViewActions.action_refresh.execute();
			}.createDelegate(this),
			onClose: function() {
				//
			}
		}

		getWnd('swUserEditWindow').show(params);
	},

	deleteUser: function() {
		var grid = this.Grid.ViewGridPanel,
			record = grid.getSelectionModel().getSelected();
		if( !record ) return false;

		sw.swMsg.show({
			title: langs('Подтверждение удаления'),
			msg: langs('Вы действительно желаете удалить этого пользователя?'),
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId) {
				if (buttonId == 'yes') {
					Ext.Ajax.request({
						url: C_USER_DROP,
						params: {
							user_login: record.get('login')
						},
						callback: function(o, s, r) {
							if(s) {
								// Поскольку релоадить негут, так как данные из лдапа читаются, то просто удаляем запись
								grid.getStore().remove(record);
							}
						}
					});
				}
			}
		});
	},

	doSearch: function() {
		// Ставим заголовок фильтра
		this.setTitleFieldSet();
		var form = this.FilterPanel.getForm(),
			params = form.getValues(),
			grid = this.Grid.ViewGridPanel;
		for(par in params)
			grid.getStore().baseParams[par] = params[par];
		grid.getStore().load({params: { start: 0, limit: 100 }});
	},

	doReset: function() {
		this.FilterPanel.getForm().reset();
		this.doSearch();
	},

	setTitleFieldSet: function() {
		var fset = this.FilterPanel.find('xtype', 'fieldset')[0],
			isfilter = false,
			title = langs('Поиск: фильтр ');

		fset.findBy(function(f) {
			if( f.xtype && f.xtype.inlist(['textfield', 'swusersgroupscombo']) ) {
				if( f.getValue() != '' && f.getValue() != null ) {
					isfilter = true;
				}
			}
		});

		fset.setTitle( title + ( isfilter == true ? '' : 'не ' ) + 'установлен' );
	},

	syncLdapAndCacheUserData: function() {
		sw.swMsg.show({
			title: langs('Подтверждение перекэширования'),
			msg: langs('Вы действительно желаете перекэшировать данные?'),
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId) {
				if (buttonId == 'yes') {
					this.getLoadMask(langs('Подождите, выполняется перекэширование данных...')).show();
					Ext.Ajax.request({
						url: '/?c=User&m=syncLdapAndCacheUserData',
						params: {
							Org_id: getGlobalOptions().org_id
						},
						callback: function(o, s, r) {
							this.getLoadMask().hide();
							if(s) {
								this.doSearch();
							}
						}.createDelegate(this)
					});
				}
			}.createDelegate(this)
		});
	},

	initComponent: function()
	{
		Ext.apply(sw.Promed.Actions, {
			PersonDoublesSearchAction: {
				text: langs('Работа с двойниками'),
				tooltip: langs('Работа с двойниками'),
				iconCls: 'doubles16',
				handler: function() {
					getWnd('swPersonDoublesSearchWindow').show();
				},
				hidden: !isAdmin
			},
			PersonDoublesModerationAction: {
				text: langs('Модерация двойников'),
				tooltip: langs('Модерация двойников'),
				iconCls: 'doubles-mod16',
				handler: function() {
					var params = {};
					if(!isAdmin && (isLpuAdmin() && isUserGroup('106'))){
						params.LpuOnly = true;
					}
					getWnd('swPersonDoublesModerationWindow').show(params);
				},
				hidden: !(isAdmin || (isLpuAdmin() && isUserGroup('106')))
			},
			PersonUnionHistoryAction: {
				text: langs('История модерации двойников'),
				tooltip: langs('История модерации двойников'),
				iconCls: 'doubles-history16',
				handler: function() {
					getWnd('swPersonUnionHistoryWindow').show();
				}
			},
            VaccinePlan: {
                text: langs('Планирование вакцинации'),
                tooltip: langs('Планирование вакцинации'),
                iconCls : 'pol-immuno16',
                handler: function()
                {
                    getWnd('amm_StartVacPlanForm').show();
                }
            },
            TaskVaccinePlan: {
                text: langs('Открыть список заданий на планирование вакцинации'),
                tooltip: langs('Открыть список заданий на планирование вакцинации'),
                iconCls : 'pol-immuno16',
                handler: function()
                {
                    getWnd('amm_ListTaskForm').show();
                }
            },
			/*mainForm: {
				text: langs('Журнал вакцинации'),
				tooltip: langs('Просмотр журналов вакцинации'),
				iconCls : 'pol-immuno16',
				handler: function()
				{
					getWnd('amm_mainForm').show();
				}
			},
			PresenceVacForm: {
				text: langs('Национальный календарь прививок'),
				tooltip: langs('Национальный календарь прививок'),
				iconCls : 'pol-immuno16',
				handler: function()
				{
					getWnd('amm_PresenceVacForm').show();
				}
			},
			SprVaccineForm: {
				text: langs('Список вакцин'),
				tooltip: langs('Список вакцин'),
				iconCls : 'pol-immuno16',
				handler: function()
				{
					getWnd('amm_SprVaccineForm').show();
				}
			},*/
			GlossaryAction: {
				tooltip: langs('Глоссарий'),
				hidden: getRegionNick().inlist(['by']),
				text: langs('Глоссарий'),
				iconCls : 'glossary16',
				handler: function()
				{
					getWnd('swGlossarySearchWindow').show();
				}
			},
			TemplatesAction: {
				tooltip: langs('Шаблоны документов'),
				text: langs('Шаблоны документов'),
				iconCls : 'test16',
				handler: function()
				{
					getWnd('swTemplSearchWindow').show();
				}
			},
			ExportDBF: {
				tooltip: langs('Выгрузка ЗП в DBF'),
				hidden: getRegionNick().inlist(['by']),
				text: langs('Выгрузка ЗП в DBF'),

				handler: function()
				{
					getWnd('swExportDBFWindow').show();
				}
			},
			SkladMO:
				{
					tooltip:langs('Склад МО'),
					text: langs('Склад МО'),
					handler: function() {
						getWnd('swSkladMO').show();
					}
				},
			QueryEvn:
				{
					handler: function() {
						getWnd('swQueryEvnListWindow').show({ARMType: this.ARMType});
					},
					text: 'Журнал запросов',
					tooltip: 'Журнал запросов',
				},
			ExportAttachedList: {
				tooltip: 'Выгрузка списка прикреплённого населения',
				text: 'Выгрузка списка прикреплённого населения',
				hidden: (!(isLpuAdmin(getGlobalOptions().lpu_id)&&isExpPop())||(getGlobalOptions().region.nick=='saratov')),
				handler: function()
				{
					getWnd('swPersonXmlWindow').show();
				}
			},
			ExportAttachedListCSV: {
				tooltip: langs('Выгрузка списка прикрепленного населения в CSV'),
				text: langs('Выгрузка списка прикрепленного населения в CSV'),
				hidden: (getRegionNick() != 'pskov'),
				handler: function()
				{
					getWnd('swPersonCSVWindow').show();
				}
			},
			ExportNonworkPersonList: {
				tooltip: langs('Реестры неработающих застрахованных лиц'),
				text: langs('Реестры неработающих застрахованных лиц'),
				hidden: !getRegionNick().inlist([ 'buryatiya' ]),
				disabled: false,
				handler: function() {
					getWnd('swPersonPolisExportWindow').show({
						AttachLpu_id: getGlobalOptions().lpu_id
					});
				}
			},
			ExportHospDataForTfomsToXml: {
				tooltip: langs('Экспорт данных для ТФОМС и СМО'),
				text: langs('Экспорт данных для ТФОМС и СМО'),

				hidden: !getRegionNick().inlist(['astra','kareliya','ekb','khak','penza','vologda']),
				handler: function()
				{
					getWnd('swHospDataExportForTfomsWindow').show({ARMType: this.ARMType});
				}.createDelegate(this)
			},
			ImportHospDataFromTfomsXml: {
				tooltip: langs('Импорт данных для ТФОМС и СМО'),
				text: langs('Импорт данных для ТФОМС и СМО'),

				hidden: getRegionNick() != 'astra',
				handler: function()
				{
					getWnd('swHospDataImportFromTfomsWindow').show();
				}.createDelegate(this)
			},
			ImportAnswerFromSMO: {
				tooltip: 'Импорт ответа по прикрепленному населению от СМО',
				text: 'Импорт ответа по прикрепленному населению от СМО',
				hidden: getRegionNick() != 'astra',
				handler: function()
				{
					getWnd('swImportAnswerFromSMO').show();
				}.createDelegate(this)
			},
			ImportAmbulanceCardFromDbf: {
				tooltip: langs('Импорт карт СМП'),
				text: langs('Импорт карт СМП'),
				hidden: !getRegionNick().inlist([ 'ekb', 'kareliya' ]),
				handler: function()
				{
					getWnd('swAmbulanceCardImportDbfWindow').show();
				}
			},
			ExportDirectionHTME: {
				name: 'export_direction_htm',
				text: 'Выгрузка направлений на ВМП',
				hidden: getRegionNick() != 'kareliya',
				handler: function() {
					getWnd('swDirectionHTMExportWindow').show();
				}.createDelegate(this)
			},
			ExportPersonProf: {
				name: 'ExportPersonProf',
				handler: function() {
					getWnd('swPersonProfExportWindow').show();
				},
				hidden: (getRegionNick() != 'kareliya'),
				text: 'Выгрузка данных по профилактическим мероприятиям',
				tooltip: 'Выгрузка данных по профилактическим мероприятиям'
			},
			swLpuUslugaAction: {
				text: langs('Услуги МО'),
				tooltip: langs('Услуги МО'),
				iconCls: 'lpu-services-lpu16',
				handler: function()
				{
					sw.swMsg.alert(langs('Внимание'),langs('Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.'));
				},
				hidden: true // !isAdmin refs #16869
			},
			swUslugaGostAction: {
				text: langs('Услуги ГОСТ'),
				tooltip: langs('Услуги ГОСТ'),
				iconCls: 'lpu-services-gost16',
				handler: function()
				{
					sw.swMsg.alert(langs('Внимание'),langs('Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.'));
				},
				hidden: true // !isAdmin refs #16869
			},
			UslugaComplexViewAction: {
				text: langs('Комплексные услуги'),
				tooltip: langs('Комплексные услуги'),
				iconCls: 'services-complex16',
				handler: function() {
					getWnd('swUslugaComplexViewWindow').show();
				},
				hidden: true // !isAdmin refs #16869
			},
			TariffVolumeViewAction: {
				text: langs('Тарифы и объемы'),
				tooltip: langs('Тарифы и объемы'),
				// iconCls : 'service-reestrs16',
				handler: function () {
					var params = {};
					params.readOnly = (getRegionNick() == 'vologda') ? false : true;
					getWnd('swTariffVolumesViewWindow').show(params);
				},
				hidden: false//(getRegionNick() != 'perm')
			},
			TariffValueListAction: {
				text: langs('Тарифы ТФОМС'),
				tooltip: langs('Тарифы ТФОМС'),
				handler: function() {
					getWnd('swTariffValueListWindow').show({
						ARMType: this.ARMType
					});
				}.createDelegate(this),
				hidden: !getRegionNick().inlist([ 'penza' ])
			},
			UslugaTreeAction: {
				text: langs('Справочник услуг'),
				tooltip: langs('Справочник услуг'),
				iconCls: 'services-complex16',
				handler: function() {
					getWnd('swUslugaTreeWindow').show();
				},
				hidden: !isAdmin && !isLpuAdmin()
			},
			swMKB10Action: {
				text: langs('МКБ-10'),
				tooltip: langs('Справочник МКБ-10'),
				iconCls: 'spr-mkb16',
				handler: function()
				{
					sw.swMsg.alert(langs('Внимание'),langs('Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.'));
				},
				hidden: !isAdmin
			},
			swMESAction: {
				text: langs('Новые ') + getMESAlias(),
				tooltip: langs('Справочник новых ') + getMESAlias(),
				iconCls: 'spr-mes16',
				handler: function()
				{
					getWnd('swMesSearchWindow').show();
				},
				hidden: !isAdmin || getRegionNick().inlist(['by'])
			},
			swMESOldAction: {
				text: getMESAlias(),
				tooltip: langs('Справочник') + getMESAlias(),
				iconCls: 'spr-mes16',
				handler: function()
				{
					getWnd('swMesOldSearchWindow').show();
				},
				hidden: getRegionNick().inlist(['by'])
			},
			ElectronicQueueAction: {
				text: 'Справочник электронных очередей', //'Электронная очередь',
				tooltip: 'Справочник электронных очередей',
				//iconCls: '',
				handler: function()
				{
					if ( !getWnd('swElectronicQueueListWindow').isVisible() )
						getWnd('swElectronicQueueListWindow').show({
							mode: 'LpuAdmin'
						});
				}
			},
			ElectronicScoreboardAction: {
				text: 'Справочник электронных табло', //'Электронное табло',
				tooltip: 'Справочник электронных табло',
				//iconCls: '',
				handler: function()
				{
					if ( !getWnd('swElectronicScoreboardListWindow').isVisible() )
						getWnd('swElectronicScoreboardListWindow').show({
							mode: 'LpuAdmin'
						});
				}
			},
			ElectronicInfomatAction: {
				text: 'Справочник инфоматов', //'Инфомат',
				tooltip: 'Справочник инфоматов',
				//iconCls: '',
				handler: function()
				{
					if ( !getWnd('swElectronicInfomatListWindow').isVisible() )
						getWnd('swElectronicInfomatListWindow').show({
							mode: 'LpuAdmin'
						});
				}
			},
			ElectronicTreatmentAction: {
				text: 'Справочник поводов обращений',
				tooltip: 'Справочник поводов обращений',
				//iconCls: '',
				handler: function()
				{
					if ( !getWnd('swElectronicTreatmentListWindow').isVisible() )
						getWnd('swElectronicTreatmentListWindow').show({
							mode: 'LpuAdmin'
						});
				}
			},
			GoodsStorageAction: {
				text: langs('Наименования мест хранения'),
				iconCls: '',
				handler: function()
				{
					getWnd('swGoodsStorageViewWindow').show();
				}
			},

			swRoutingManageAction: {
				text: langs('Маршрутизация и сферы ответственности МО'),
				tooltip: langs('Маршрутизация и сферы ответственности МО'),
				disabled: !isSuperAdmin() && !isUserGroup('RoutingManger'),
				handler: function() {
					arguments.action = 'view';
					getWnd('swRoutingManagerWindow').show(arguments);
				},
			},
			swSprPromedAction: {
				text: langs('Справочники'),
				tooltip: langs('Справочники'),
				iconCls: 'spr-promed16',
				handler: function() {
					arguments.action = 'view';
					getWnd('swDirectoryViewWindow').show(arguments);
				},
			},
			SprLpuAction: {
				text: langs('Справочники МО'),
				tooltip: langs('Справочники МО'),
				iconCls: 'spr-lpu16',
				handler: function()
				{
					sw.swMsg.alert(langs('Внимание'),langs('Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.'));
				},
				hidden: !isAdmin
			},
			SprOmsAction: {
				text: langs('Справочники ОМС'),
				tooltip: langs('Справочники ОМС'),
				iconCls: 'spr-oms16',
				handler: function()
				{
					sw.swMsg.alert(langs('Внимание'),langs('Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.'));
				},
				hidden: !isAdmin || getRegionNick().inlist(['by'])
			},
			SprDloAction: {
				text: langs('Справочники ЛЛО'),
				tooltip: langs('Справочники ЛЛО'),
				iconCls: 'spr-dlo16',
				handler: function()
				{
					sw.swMsg.alert(langs('Внимание'),langs('Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.'));
				},
				hidden: !isAdmin
			},
			DrugListAction: {
				name: 'action_DrugListSpr',
				text: 'Перечни медикаментов',
				iconCls : '',
				handler: function()
				{
					getWnd('swDrugListSprWindow').show({ARMType: this.ARMType});
				}.createDelegate(this)
			},
			DrugNomenAction: {
				name: 'action_DrugNomenSpr',
				text: langs('Номенклатурный справочник'),
				iconCls : '',
				handler: function()
				{
					getWnd('swDrugNomenSprWindow').show({readOnly: false});
				}
			},
			ExtemporalAction: {
				name: 'action_Extemporal',
				tooltip: langs('Экстемпоральные рецептуры'),
				text: langs('Экстемпоральные рецептуры'),
				handler: function() {
					getWnd('swExtemporalViewWindow').show();
				}
			},
			DrugMarkupAction: {
				name: 'action_DrugMarkup',
				hidden: getRegionNick().inlist(['by','kz']),
				text: langs('Предельные надбавки на ЖНВЛП'),
				iconCls : 'lpu-finans16',
				handler: function() {
					getWnd('swDrugMarkupViewWindow').show();
				}
			},
			PriceJNVLPAction: {
				name: 'action_PriceJNVLP',
				hidden: getRegionNick().inlist(['by','kz']),
				text: langs('Цены на ЖНВЛП'),
				iconCls : 'dlo16',
				handler: function() {
					getWnd('swJNVLPPriceViewWindow').show();
				}
			},
			PrepBlockSprAction: {
				text: langs('Справочник фальсификатов и забракованных серий ЛС'),
				tooltip: langs('Справочник фальсификатов и забракованных серий ЛС'),
				handler: function()
				{
					getWnd('swPrepBlockViewWindow').show();
				}
			},
			SprRlsAction: {
				text: getRLSTitle(),
				tooltip: getRLSTitle(),
				iconCls: 'rls16',
				handler: function()
				{
					getWnd('swRlsViewForm').show();
				},
				hidden: false
			},
			SprPostAction: {
				text: langs('Должности'),
				tooltip: langs('Должности'),
				iconCls: '',
				handler: function()
				{
					window.gwtBridge.runDictionary(getPromedUserInfo(), 'Post', main_center_panel);
				}/*,
				hidden: !isAdmin*/
			},
			SprSkipPaymentReasonAction: {
				text: langs('Причины невыплат'),
				tooltip: langs('Причины невыплат'),
				iconCls: '',
				handler: function()
				{
					window.gwtBridge.runDictionary(getPromedUserInfo(), 'SkipPaymentReason', main_center_panel);
				}/*,
				hidden: !isAdmin*/
			},
			SprWorkModeAction: {
				text: langs('Режимы работы'),
				tooltip: langs('Режимы работы'),
				iconCls: '',
				handler: function()
				{
					window.gwtBridge.runDictionary(getPromedUserInfo(), 'WorkMode', main_center_panel);
				}/*,
				hidden: !isAdmin*/
			},
			SprSpecialityAction: {
				text: langs('Специальности'),
				tooltip: langs('Специальности'),
				iconCls: '',
				handler: function()
				{
					window.gwtBridge.runDictionary(getPromedUserInfo(), 'Speciality', main_center_panel);
				}/*,
				hidden: !isAdmin*/
			},
			SprDiplomaSpecialityAction: {
				text: langs('Дипломные специальности'),
				tooltip: langs('Дипломные специальности'),
				iconCls: '',
				handler: function()
				{
					window.gwtBridge.runDictionary(getPromedUserInfo(), 'DiplomaSpeciality', main_center_panel);
				}/*,
				hidden: !isAdmin*/
			},
			SprLeaveRecordTypeAction: {
				text: langs('Тип записи окончания работы'),
				tooltip: langs('Тип записи окончания работы'),
				iconCls: '',
				handler: function()
				{
					window.gwtBridge.runDictionary(getPromedUserInfo(), 'LeaveRecordType', main_center_panel);
				}/*,
				hidden: !isAdmin*/
			},
			SprEducationTypeAction: {
				text: langs('Тип образования'),
				tooltip: langs('Тип образования'),
				iconCls: '',
				handler: function()
				{
					window.gwtBridge.runDictionary(getPromedUserInfo(), 'EducationType', main_center_panel);
				}/*,
				hidden: !isAdmin*/
			},
			SprEducationInstitutionAction: {
				text: langs('Учебное учреждение'),
				tooltip: langs('Учебное учреждение'),
				iconCls: '',
				handler: function()
				{
					window.gwtBridge.runDictionary(getPromedUserInfo(), 'EducationInstitution', main_center_panel);
				}/*,
				hidden: !isAdmin*/
			},
			swOMSSprTerrAction: {
				text: langs('Территории субъекта РФ'),
				tooltip: langs('Территории субъекта РФ'),
				iconCls: 'spr-terr-oms16',
				handler: function()
				{
					sw.swMsg.alert(langs('Внимание'),langs('Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.'));
				},
				hidden: !isAdmin || getRegionNick().inlist(['by'])
			},
			swClassAddrAction: {
				text: langs('Классификатор адресов'),
				tooltip: langs('Классификатор адресов'),
				iconCls: 'spr-terr-addr16',
				handler: function()
				{
					sw.swMsg.alert(langs('Внимание'),langs('Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.'));
				},
				hidden: !isAdmin
			},
			ExportPersonCardForPeriod: {
				text: langs('Экспорт прикрепленного населения за период'),
				tooltip: langs('Экспорт прикрепленного населения за период'),
				handler: function()
				{
					getWnd('swExportPersonCardForPeriodWindow').show();
				},
				hidden: (getRegionNick() != 'perm')
			},
			ExportPersonDispForPeriod: {
				text: langs('Экспорт карт диспансерного наблюдения за период'),
				tooltip: langs('Экспорт карт диспансерного наблюдения за период'),
				handler: function()
				{
					getWnd('swExportPersonDispForPeriodWindow').show();
				},
				hidden: !getRegionNick().inlist([ 'astra', 'perm', 'ekb' ])
			},
			ExportPersonDispCard: {
				text: 'Выгрузка карт диспансерного наблюдения',
				tooltip: 'Выгрузка карт диспансерного наблюдения',
				handler: function()
				{
					getWnd('swExportPersonDispCardWindow').show();
				},
				hidden: (getRegionNick() != 'kareliya')
			},
			PersonIdentPackage: {
				text: 'Пакетная идентификация ТФОМС',
				tooltip: 'Пакетная идентификация ТФОМС',
				handler: function()
				{
					getWnd('swPersonIdentPackageWindow').show({ARMType: this.ARMType});
				}.createDelegate(this),
				hidden: !getRegionNick().inlist(['ekb','krym'])
			},
			ExportEvnPrescrMse: {
				nn: 'action_ExportEvnPrescrMse',
				text: 'Экспорт направлений на МСЭ',
				tooltip: 'Экспорт направлений на МСЭ',
				hidden: getRegionNick() == 'kz',
				handler: function() {
					getWnd('swEvnPrescrMseExportWindow').show({ARMType: this.ARMType});
				}.createDelegate(this)
			},
			ExportToFRMO: {
				text: 'Передача данных в сервис ФРМО',
				tooltip: 'Передача данных в сервис ФРМО',
				handler: function()
				{
					//getWnd('swExportToFRMOWindow').show();
					//todo: запустить выгрузку по текущему МО пользователя
					sw.swMsg.alert(lang['vnimanie'],lang['dannyiy_funktsional_v_tekuschee_vremya_nedostupen_poskolku_nahoditsya_v_stadii_razrabotki']);
				}.createDelegate(this)
			},
			ExportEvnPrescrMse: {
				nn: 'action_ExportEvnPrescrMse',
				text: 'Экспорт направлений на МСЭ',
				tooltip: 'Экспорт направлений на МСЭ',
				hidden: getRegionNick() == 'kz',
				handler: function() {
					getWnd('swEvnPrescrMseExportWindow').show({ARMType: this.ARMType});
				}.createDelegate(this)
			},
			ImportEvnMse: {
				nn: 'action_ImportEvnMse',
				text: 'Импорт обратных талонов',
				tooltip: 'Импорт обратных талонов',
				hidden: getRegionNick() == 'kz',
				handler: function() {
					getWnd('swEvnMseImportWindow').show({ARMType: this.ARMType});
				}.createDelegate(this)
			},
			ExportPersonCardAttach: {
				nn: 'action_ExportPersonCardAttach',
				text: 'Экспорт заявлений о прикреплении',
				tooltip: 'Экспорт заявлений о прикреплении',
				hidden: !getRegionNick().inlist(['vologda']) || !isUserGroup('ExportAttachedPopulation'),
				handler: function() {
					getWnd('swPersonCardAttachExportWindow').show({ARMType: this.ARMType});
				}.createDelegate(this)
			},
			ImportPersonCardAttachResponse: {
				nn: 'action_ImportPersonCardAttachResponse',
				text: 'Импорт ответа по заявлениям о прикреплении',
				tooltip: 'Импорт ответа по заявлениям о прикреплении',
				hidden: !getRegionNick().inlist(['vologda']) || !isUserGroup('ExportAttachedPopulation'),
				handler: function() {
					getWnd('swPersonCardAttachImportResponseWindow').show({ARMType: this.ARMType});
				}.createDelegate(this)
			},
			ImportDetachedPersonCard: {
				nn: 'action_ImportDetachPersonCard',
				text: 'Импорт сведений о ЗЛ, открепленных от МО',
				tooltip: 'Импорт сведений о ЗЛ, открепленных от МО',
				hidden: !getRegionNick().inlist(['vologda']) || !isUserGroup('ExportAttachedPopulation'),
				handler: function() {
					getWnd('swPersonCardImportDetachWindow').show({ARMType: this.ARMType});
				}.createDelegate(this)
			},
			ImportPersonCardRegister: {
				nn: 'action_ImportPersonCardRegister',
				text: 'Импорт регистра прикрепленного населения',
				tooltip: 'Импорт регистра прикрепленного населения',
				hidden: !getRegionNick().inlist(['vologda']) || !isUserGroup('ExportAttachedPopulation'),
				handler: function() {
					getWnd('swPersonCardImportRegisterWindow').show({ARMType: this.ARMType});
				}.createDelegate(this)
			}
		});

		var form = this;
		// Формирование списка всех акшенов
		var configActions =
		{
			action_LpuPassport: {
				nn: 'action_LpuPassport',
				text: langs('Паспорт ЛПУ'),
				tooltip: langs('Паспорт ЛПУ'),
				iconCls: 'lpu-passport32',
				handler: function()
				{
					getWnd('swLpuPassportEditWindow').show({
							action: 'edit',
							Lpu_id: getGlobalOptions().lpu_id
					});
				},
				hidden: !isAdmin && !isLpuAdmin()
			},
			action_LpuStructureView: {
				nn: 'action_LpuStructureView',
				text: MM_LPUSTRUC,
				tooltip: langs('Структура ЛПУ'),
				iconCls : 'structure32',
				hidden: !isAdmin && !isLpuAdmin() && !isCadrUserView(),
				handler: function()
				{
					getWnd('swLpuStructureViewForm').show();
				}
			},
			action_AutoPersonCard: {
				handler: function() {
					getWnd('swPersonSearchPersonCardAutoWindow').show();
				}.createDelegate(this),
				iconCls: 'pcard-new32',
				hidden:  getGlobalOptions().lpu_isLab == 2,
				nn: 'action_AutoPersonCard',
				text: langs('Групповое прикрепление'),
				tooltip: langs('Групповое прикрепление')
			},
			action_WorkGraph: {
				handler: function() {
					getWnd('swWorkGraphSearchWindow').show();
				}.createDelegate(this),
				hidden: !(isUserGroup('WorkGraph') || isLpuAdmin()),
				iconCls: 'sched-16',
				nn: 'action_WorkGraph',
				text: 'Графики дежурств',
				tooltip: 'Графики дежурств'
			},
			action_OrgView:
			{
				nn: 'action_OrgView',
				tooltip: langs('Все организации'),
				text: langs('Организации'),
				iconCls : 'org32',
				disabled: false,
				hidden: getGlobalOptions().lpu_isLab == 2,
				handler: function()
				{
					getWnd('swOrgViewForm').show();
				}
			},
			action_InvoiceView:
			{
				nn: 'action_InvoiceView',
				tooltip: langs('Учет ТМЦ'),
				text: langs('Учет ТМЦ'),
				iconCls : 'product16',
				disabled: false,
				hidden: getGlobalOptions().lpu_isLab == 2,
				handler: function()
				{
					getWnd('swInvoiceViewWindow').show();
				}
			},
			action_mmunoprof:
			{
				nn: 'action_mmunoprof',
				tooltip: langs('Иммунопрофилактика'),
				text: langs('Иммунопрофилактика'),
				iconCls : 'immunoprof32',
				disabled: false,
				menuAlign: 'tr?',
				menu: new Ext.menu.Menu({
					items: [
						sw.Promed.Actions.VaccinePlan,
						sw.Promed.Actions.TaskVaccinePlan
						//sw.Promed.Actions.SprVaccineForm
					]
				})
			},
			action_staff_actions: {
				nn: 'action_staff_actions',
				disabled: isMedPersView(),
				text:langs('Действия'),
				menuAlign: 'tr',
				iconCls : 'database-export32',
				tooltip: langs('Действия'),
				hidden: !getRegionNick().inlist(['kareliya','krym']),
				menu: [
					{
						text: langs('Журнал работы сервисов'),
						tooltip: langs('Журнал работы сервисов'),
						hidden: !getRegionNick().inlist(['krym']),
						handler: function(){
							getWnd('swServiceListLogWindow').show({ARMType: this.ARMType});
						}.createDelegate(this)
					},
					{
						name: 'download_med_staff',
						text: langs('Импорт данных ФРМР'),
						hidden: !getRegionNick().inlist(['kareliya']),
						handler: function()
						{
							getWnd('swXmlImportWindow').show({Fl:1,RegisterList_Name:'MedPersonal',RegisterList_id:4});
						}.createDelegate(this)
					},
					{
						nn: 'action_importZL',
						tooltip: langs('Импорт данных ЗЛ'),
						text: langs('Импорт данных ЗЛ'),
						hidden: !getRegionNick().inlist(['kareliya']),
						disabled: false,
						handler: function(){
							getWnd('swImportZLWindow').show({RegisterList_id:26});
						}
					},{
						nn: 'action_importWindow',
						tooltip: langs('Обновление регистров'),
						text: langs('Обновление регистров'),
						hidden: !getRegionNick().inlist(['kareliya']),
						disabled: false,
						handler: function(){
							getWnd('swImportWindow').show({RegisterList_Name:'PersonZL'});
						}
					}
				]
			},
			action_Documents:
			{
				nn: 'action_Documents',
				tooltip: langs('Инструментарий'),
				text: langs('Инструментарий'),
				iconCls : 'document32',
				disabled: false,
				hidden: ((getGlobalOptions().region.nick=='saratov') || (getGlobalOptions().lpu_isLab == 2)),
				menuAlign: 'tr',
				menu: new Ext.menu.Menu({
					items: [
						sw.Promed.Actions.GlossaryAction,
						sw.Promed.Actions.TemplatesAction,
						sw.Promed.Actions.ExportAttachedList,
						sw.Promed.Actions.ExportAttachedListCSV,
						sw.Promed.Actions.ExportDBF,
						sw.Promed.Actions.ExportNonworkPersonList,
						sw.Promed.Actions.ExportHospDataForTfomsToXml,
						sw.Promed.Actions.ImportHospDataFromTfomsXml,
						sw.Promed.Actions.ImportAnswerFromSMO,
						sw.Promed.Actions.SkladMO,
						sw.Promed.Actions.QueryEvn,
						sw.Promed.Actions.ImportAmbulanceCardFromDbf,
						sw.Promed.Actions.ExportDirectionHTME,
						sw.Promed.Actions.ExportPersonCardForPeriod,
						sw.Promed.Actions.ExportPersonDispForPeriod,
						sw.Promed.Actions.ExportPersonDispCard,
						sw.Promed.Actions.ExportPersonProf,
						{
							name: langs('Выгрузка регистра медработников для ФРМР новый'),
							text: langs('Выгрузка регистра медработников для ФРМР новый'),
							hidden: false,
							handler: function()
							{
								getWnd('swExportMedPersonalToXMLFRMPWindow').show();
							}.createDelegate(this)
						}, {
							name: langs('Выгрузка штатного расписания для ФРМР новый'),
							text: langs('Выгрузка штатного расписания для ФРМР новый'),
							hidden: false,
							handler: function()
							{
								getWnd('swExportMedPersonalToXMLFRMPStaffWindow').show();
							}.createDelegate(this)
						},
						sw.Promed.Actions.PersonIdentPackage,
						sw.Promed.Actions.ExportToFRMO,
						{
							text: langs('Экспорт прикреплений/ заявлений'),
							tooltip: langs('Экспорт прикреплений/ заявлений'),
							hidden: (!getRegionNick().inlist(['buryatiya']) || !isUserGroup('ExportAttachedPopulation') ),
							handler: function()
							{
								getWnd('swExportAttachmentsApplicationsWindow').show({ARMType: this.ARMType});
							}.createDelegate(this)

						},
						{
							text: langs('Импорт ошибок ФЛК по ЗЛ'),
							tooltip: langs('Импорт ошибок ФЛК по ЗЛ'),
							hidden: (!getRegionNick().inlist(['buryatiya']) || !isUserGroup('ExportAttachedPopulation') ),
							handler: function()
							{
								getWnd('swUploadFileWindowFor_ImportErrors_ResponseFromSMO_ChangesLocation').show({ARMType: this.ARMType, Form: 'ImportErrors_FLKforZL'});
							}.createDelegate(this)
						},
						{
							text: langs('Импорт ответа от СМО по ЗЛ'),
							tooltip: langs('Импорт ответа от СМО по ЗЛ'),
							hidden: (!getRegionNick().inlist(['buryatiya']) || !isUserGroup('ExportAttachedPopulation') ),
							handler: function()
							{
								getWnd('swUploadFileWindowFor_ImportErrors_ResponseFromSMO_ChangesLocation').show({ARMType: this.ARMType, Form: 'ImportResponseFrom_SMOforPL'});
							}.createDelegate(this)
						},
						{
							text: langs('Импорт территориальных прикреплений/ откреплений'),
							tooltip: langs('Импорт территориальных прикреплений/ откреплений'),
							hidden: (!getRegionNick().inlist(['buryatiya']) || !isUserGroup('ExportAttachedPopulation') ),
							handler: function()
							{
								getWnd('swUploadFileWindowFor_ImportErrors_ResponseFromSMO_ChangesLocation').show({ARMType: this.ARMType, Form: 'ImportOfTerritorialAttachmentsDetachments'});
							}.createDelegate(this)
						},
						{
							text: langs('Журнал работы сервисов'),
							tooltip: langs('Журнал работы сервисов'),
							handler: function(){
								getWnd('swServiceListLogWindow').show({ARMType: this.ARMType});
							}.createDelegate(this)
						},
						sw.Promed.Actions.ExportEvnPrescrMse,
						sw.Promed.Actions.ImportEvnMse,
						{
							name: langs('Сравнение кадров ЕЦИС-ПроМед'),
							text: langs('Сравнение кадров ЕЦИС-ПроМед'),
							hidden: false,
							handler: function()
							{
								getWnd('swCompareECISMedStaffFact').show();
							}.createDelegate(this)
						},
						{
							name: langs('Планы контрольных посещений в рамках диспансерного наблюдения'),
							text: langs('Планы контрольных посещений в рамках диспансерного наблюдения'),
							hidden: !getRegionNick().inlist(['buryatiya','ekb','pskov']),
							handler: function()
							{
								getWnd('swPlanObsDispListWindow').show();
							}.createDelegate(this)
						},
						{
							text: langs('Журнал импорта/экспорта данных о прикреплениях'),
							hidden: !getRegionNick().inlist(['vologda']) || !isUserGroup('ExportAttachedPopulation'),
							handler: function() {
								getWnd('swImportWindow').show({RegisterList_Name:'AttachJournal', ARMType: this.ARMType});
							}.createDelegate(this)
						},
						sw.Promed.Actions.ExportPersonCardAttach,
						sw.Promed.Actions.ImportPersonCardAttachResponse,
						sw.Promed.Actions.ImportDetachedPersonCard,
						sw.Promed.Actions.ImportPersonCardRegister,
						{
							name: langs('Отчеты в DBF формате'),
							text: langs('Отчеты в DBF формате'),
							hidden: getRegionNick() !== 'vologda',
							handler: function()	{
								getWnd('swReportsInDBFFormat').show();
							}.createDelegate(this)
						},
						{
							name: langs('Удаленные данные'),
							text: langs('Удаленные данные'),
							hidden: getRegionNick() !== 'vologda',
							handler: function() {
								var cur = sw.Promed.MedStaffFactByUser.current;
								getWnd('delDocsSearchWindow').show({MedStaffFact_id: (cur ? cur.MedStaffFact_id : undefined)});
							}
						},


						// #175117
						// Кнопка для открытия формы "Журнал учета рабочего времени сотрудников":
						{
							text: langs('Журнал учета рабочего времени сотрудников'),

							handler: function()
							{
								var cur = sw.Promed.MedStaffFactByUser.current;

								getWnd('swTimeJournalWindow').show(
									{
										ARMType: (cur ? cur.ARMType : undefined),
										MedStaffFact_id: (cur ? cur.MedStaffFact_id : undefined),
										Lpu_id: (cur ? cur.Lpu_id : undefined)
									});
							}
						}
					]
				})
			},
			action_Users:
			{
				nn: 'action_Users',
				tooltip: langs('Пользователи'),
				text: langs('Просмотр и редактирование пользователей'),
				iconCls : 'users32',
				disabled: false,
				hidden: true,
				handler: function()
				{
					getWnd('swUsersTreeViewWindow').show();
				}
			},
			action_Spr:
			{
				nn: 'action_Spr',
				tooltip: langs('Справочники'),
				text: langs('Справочники'),
				iconCls : 'book32',
				disabled: false,
				menuAlign: 'tr',
				menu: new Ext.menu.Menu({
					items: [
					sw.Promed.Actions.TariffVolumeViewAction,
					sw.Promed.Actions.TariffValueListAction,
					sw.Promed.Actions.swLpuUslugaAction,
					sw.Promed.Actions.swUslugaGostAction,
					sw.Promed.Actions.UslugaComplexViewAction,
					sw.Promed.Actions.UslugaTreeAction,
					sw.Promed.Actions.swMKB10Action,
					sw.Promed.Actions.swMESAction,
					sw.Promed.Actions.swMESOldAction,
					sw.Promed.Actions.GoodsStorageAction,
					'-',
					sw.Promed.Actions.swRoutingManageAction,
					sw.Promed.Actions.swSprPromedAction,
					sw.Promed.Actions.SprLpuAction,
					sw.Promed.Actions.SprOmsAction,
					sw.Promed.Actions.SprDloAction,
					sw.Promed.Actions.swDrugDocumentSprAction,
					sw.Promed.Actions.DrugListAction,
					sw.Promed.Actions.DrugNomenAction,
					{
						text: langs('Лекарственные средства'),
						tooltip: langs('Лекарственные средства'),
						menu: new Ext.menu.Menu({
							items: [
								sw.Promed.Actions.ExtemporalAction,
								sw.Promed.Actions.DrugMarkupAction,
								sw.Promed.Actions.PriceJNVLPAction,
								sw.Promed.Actions.PrepBlockSprAction,
								sw.Promed.Actions.SprRlsAction
							]
						})
					},
					'-',
					{
						text: langs('ЕРМП'),
						tooltip: langs('ЕРМП'),
						iconCls: '',
						menu: new Ext.menu.Menu({
							items: [
								sw.Promed.Actions.SprPostAction,
								sw.Promed.Actions.SprSkipPaymentReasonAction,
								sw.Promed.Actions.SprWorkModeAction,
								sw.Promed.Actions.SprSpecialityAction,
								sw.Promed.Actions.SprDiplomaSpecialityAction,
								sw.Promed.Actions.SprLeaveRecordTypeAction,
								sw.Promed.Actions.SprEducationTypeAction,
								sw.Promed.Actions.SprEducationInstitutionAction
							]
						})
					},
					'-',
					{
						text: langs('Территории'),
						tooltip: langs('Территории'),
						iconCls: 'spr-terr16',
						menu: new Ext.menu.Menu({
							items: [
								sw.Promed.Actions.swOMSSprTerrAction,
								sw.Promed.Actions.swClassAddrAction
							]
						})
					},
					{
						text: 'Справочник связи МО с бюро МСЭ',
						tooltip: 'Справочник связи МО с бюро МСЭ',
						iconCls: '',
						handler: function() {
							getWnd('swLpuMseLinkViewWindow').show({ARMType: this.ARMType});
						}.createDelegate(this)
					}
					]
				})
			},
			action_Contragents: {
				nn: 'action_Contragents',
				tooltip: langs('Справочник: Контрагенты'),
				text: langs('Контрагенты'),
				iconCls : 'org32',
				disabled: false,
				hidden: getGlobalOptions().lpu_isLab == 2,
				handler: function(){
					getWnd('swContragentViewWindow').show();
				}
			},
			action_DBStructure:
			{
				nn: 'action_DBStructure',
				tooltip: langs('Структура БД'),
				text: langs('Структура БД'),
				iconCls : 'database32',
				disabled: false,
				handler: function()
				{
					getWnd('swReportDBStructureOptionsWindow').show();
				}
			},
			action_Doubles:
			{
				nn: 'action_Doubles',
				tooltip: langs('Двойники'),
				text: langs('Двойники'),
				iconCls : 'doubles32',
				disabled: false,
				menuAlign: 'tr',
				menu: new Ext.menu.Menu({
					items: [
						sw.Promed.Actions.PersonDoublesSearchAction,
						sw.Promed.Actions.PersonDoublesModerationAction,
						sw.Promed.Actions.PersonUnionHistoryAction
					]
				})
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
                        getWnd('swOrgFarmacyLinkedByLpuViewWindow').show({ARMType: form.ARMType});
                    }
                }
            },
			action_ES:
			{
				nn: 'action_ES',
				tooltip: 'ЭЛН',
				text: 'ЭЛН',
				iconCls: 'lvn-search16',
				hidden: (getRegionNick().inlist([ 'kz' ]) || (getGlobalOptions().lpu_isLab == 2)),
				menuAlign: 'tr',
				menu: new Ext.menu.Menu({
					items: [{
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
			action_ReportEngine:
			{
				nn: 'action_ReportEngine',
				tooltip: langs('Просмотр отчетов'),
				text: langs('Просмотр отчетов'),
				iconCls : 'report32',
				disabled: false,
				handler: function()
				{
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
			action_UserProfile: {
				nn: 'action_UserProfile',
				text: langs('Мой профиль'),
				tooltip: langs('Профиль пользователя'),
				iconCls : 'user32',
				hidden: false,
				handler: function()
				{
					args = {}
					args.action = 'edit';
					getWnd('swUserProfileEditWindow').show(args);
				}
			},
			action_OptionsView: {
				nn: 'action_OptionsView',
				tooltip: langs('Настройки'),
				tooltip: langs('Просмотр и редактирование настроек'),
				iconCls : 'settings32',
				disabled: false,
				menuAlign: 'tr',
				menu: new Ext.menu.Menu({
					items: [{
						text: langs('Настройки'),
						handler: function () {
							getWnd('swOptionsWindow').show();
						}
					}, {
						text: langs('Нумераторы'),
						handler: function () {
							getWnd('swNumeratorListWindow').show();
						}
					}]
				})
			},
			action_PersonUnionHistoryWindow: {
				nn:'action_PersonUnionHistoryWindow',
				text: langs('Модерация двойников'),
				tooltip: langs('История модерации двойников'),
				iconCls: 'doubles-history32',
				hidden: getGlobalOptions().lpu_isLab == 2,
				handler: function()
				{
					getWnd('swPersonUnionHistoryWindow').show();
				}

			},
			action_PersonDoublesModerationAction: {
				nn:'action_PersonDoublesModerationAction',
				text: langs('Модерация двойников'),
				tooltip: langs('Модерация двойников'),
				iconCls: 'doubles-mod16',
				handler: function() {
					var params = {};
					if(!isAdmin && (isLpuAdmin() && isUserGroup('106'))){
						params.LpuOnly = true;
					}
					getWnd('swPersonDoublesModerationWindow').show(params);
				},
				hidden: !(isAdmin || (isLpuAdmin() && isUserGroup('106')))
			},
			action_SURDataView: {
				nn: '',
				text: langs('Просмотр данных СУР'),
				tooltip: langs('Просмотр данных СУР'),
				iconCls: 'structure32',
				hidden: getRegionNick()!='kz',
				handler: function()
				{
					getWnd('swSURDataViewWindow').show({ARMType: this.ARMType});
				}.createDelegate(this)
			},
			action_PersonDopDispPlan: {
				nn: 'action_PersonDopDispPlan',
				text: 'Планы диспансеризации и профилактических медицинских осмотров',
				tooltip: 'Планы диспансеризации и профилактических медицинских осмотров',
				iconCls: 'pol-dopdisp16',
				handler: function()
				{
					getWnd('swPersonDopDispPlanListWindow').show();
				}.createDelegate(this)
			},
			action_DispPlan: {
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
			action_StoragePlacement:
			{
				nn: 'action_StoragePlacement',
				tooltip: langs('Размещение на складах'),
				text: langs('Размещение на складах'),
				iconCls : 'storage-place32',
				handler: function()
				{
					getWnd('swStorageZoneViewWindow').show();
				}
			},
			action_ElectronicQueueSpr:
			{
				nn: 'action_ElectronicQueueSpr',
				tooltip: 'Электронная очередь',
				text: 'Электронная очередь',
				iconCls : 'eq32',
				disabled: false,
				menuAlign: 'tr',
				menu: new Ext.menu.Menu({
					items: [
						sw.Promed.Actions.ElectronicQueueAction,
						sw.Promed.Actions.ElectronicScoreboardAction,
						sw.Promed.Actions.ElectronicInfomatAction,
						sw.Promed.Actions.ElectronicTreatmentAction
					]
				})
			},
			action_MedStaffFactReplace:
			{
				nn: 'action_MedStaffFactReplace',
				tooltip: 'График замещений',
				text: 'График замещений',
				iconCls : 'consult32',
				handler: function()
				{
					getWnd('swMedStaffFactReplaceViewWindow').show();
				}
			},
			action_LpuBuildingOffice:
			{
				nn: 'action_LpuBuildingOffice',
				iconCls : 'cabinet32',
				text: 'Кабинеты',
				tooltip: 'Кабинеты',

				menu: new Ext.menu.Menu({
					items: [
						{
							nn: 'action_LpuBuildingOffice',
							handler: function() {
								if ( ! getWnd('swLpuBuildingOfficeListWindow').isVisible() ){
									getWnd('swLpuBuildingOfficeListWindow').show();
								}
							},
							iconCls : 'cabinet32',
							text: 'Справочник кабинетов',
							tooltip: 'Справочник кабинетов'
						}, {
							nn: 'action_LpuScheduleWorkDoctor',
							handler: function() {
								if ( ! getWnd('swLpuScheduleWorkDoctorWindow').isVisible() ){
									getWnd('swLpuBuildingScheduleWorkDoctorWindow').show();
								}
							},
							iconCls : 'cabinet32',
							text: 'Расписание работы врачей',
							tooltip: 'Расписание работы врачей'
						}
					]
				})
			},
			action_EMD:{
				nn: 'action_EMD',
				hidden: getRegionNick() == 'kz',
				iconCls : 'remd32',
				text: 'Региональный РЭМД',
				tooltip: 'Региональный РЭМД',
				menu: [{
					text: 'Подписание медицинской документации',
					handler: function() {
						getWnd('swEMDSearchUnsignedWindow').show();
					}
				}, {
					text: 'Региональный РЭМД',
					handler: function() {
						getWnd('swEMDSearchWindow').show({ArmType: 'lpuadmin'});
					}
				}, {
					text: 'Настройки правил подписания документов',
					handler: function() {
						getWnd('swEMDDocumentSignRulesWindow').show({
							ArmType: 'lpuadmin'
						});
					}
				}]
			},
			action_EMD_EGISZ:{
					nn: 'action_EMD_EGISZ',
					tooltip: 'РЭМД ЕГИСЗ',
					text: 'РЭМД ЕГИСЗ',
					hidden: getRegionNick() == 'kz',
					iconCls : 'remd-egisz32',
					disabled: false,
					handler: function() {
						getWnd('swEMDJournalQueryWindow').show();
					}
			},
			action_TFOMSQueryList: {
				nn: 'action_TFOMSQueryList',
				text: 'Запросы на просмотр ЭМК',
				tooltip: 'Запросы на просмотр ЭМК',
				iconCls: 'tfoms-query32',
				handler: function () {
					getWnd('swTFOMSQueryWindow').show({ARMType: form.ARMType});
								}
			},
			action_Treatments: {
				nn: 'action_Treatments',
				text: 'Обращения',
				menuAlign: 'tr',
				tooltip: 'Обращения',
				iconCls: 'reports32',
					menu: new Ext.menu.Menu({
						items: [
										{
											nn: 'action_swTreatmentSearchAction',
											handler: function () {
												if (!getWnd('swTreatmentSearchWindow').isVisible()) {
													getWnd('swTreatmentSearchWindow').show();
												}
											},
											iconCls: 'petition-search16',
											text: langs('Регистрация обращений: Поиск'),
											tooltip: langs('Регистрация обращений: Поиск')
													//hidden: !isAccessTreatment()
										},
										{
											nn: 'action_LpuScheduleWorkDoctor',
											handler: function () {
												if (!getWnd('swTreatmentReportWindow').isVisible()) {
													getWnd('swTreatmentReportWindow').show();
												}
											},
											iconCls: 'petition-report16',
											text: langs('Регистрация обращений: Отчетность'),
											tooltip: langs('Регистрация обращений: Отчетность'),
											//hidden: isUserGroup(['lpuadmin', '5555'])
										}
								]
						})
			},
			action_LpuVIPPerson: {
				nn: 'action_LpuVIPPerson',
				text: langs('VIP Пациенты'),
				//tooltip: langs('Паспорт ЛПУ'),
				tooltip: langs('VIP Пациенты'),
				iconCls: 'patient32',
				//hidden: !isSuperAdmin() && !isLpuAdmin(),
				hidden: !isSuperAdmin() && !(isUserGroup('VIPRegistry')&& isLpuAdmin()),
				//hidden: !(isUserGroup('WorkGraph') || isSuperAdmin()),
				handler: function ()
					{
						if (getWnd('AdminVIPPersonWindow').isVisible()) {
							sw.swMsg.show({
								buttons: Ext.Msg.OK,
								fn: Ext.emptyFn,
								icon: Ext.Msg.WARNING,
								msg: langs('Окно уже открыто'),
								title: ERR_WND_TIT
								});
							return false;
						}
						//getWnd('AdminVIPPersonWindow').show({userMedStaffFact: this.userMedStaffFact});
						getWnd('AdminVIPPersonWindow').show();
						//alert("RRRRRRRRR");
								}
			},
			action_WaitingListJournal:
				{
					nn: 'action_WaitingListJournal',
					tooltip: 'Листы ожидания',
					text: 'Листы ожидания',
					iconCls : 'receipt-incorrect32',
					disabled: false,
					handler: function() {
						getWnd('swEvnQueueWaitingListJournal').show();
					}
				},
			action_AnalyzerQualityControl: {
				nn: 'action_AnalyzerQualityControl',
				tooltip: 'Контроль качества',
				text: 'Контроль качества',
				iconCls : 'testtubes32',
				handler: function() {
					getWnd('swAnalyzerQualityControlWindow').show({
						MedService_id: form.MedService_id,
						ARMType: form.ARMType
					});
				}
			},
			action_Ers: {
				nn: 'action_Ers',
				text: 'ЭРС',
				menuAlign: 'tr',
				tooltip: 'ЭРС',
				iconCls: 'ers32',
				// todo
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
						getWnd('swEvnErsTicketJournalWindow').show();
					}
				}, {
					text: 'Журнал учета детей',
					handler: function () {
						getWnd('swEvnErsChildJournalWindow').show();
					}
				}, {
					text: 'Реестры талонов и счета на оплату',
					handler: function () {
						getWnd('swErsRegistryJournalWindow').show();
					}
				}]
			}
		};
		// Копируем все действия для создания панели кнопок
		form.PanelActions = {};
		for(var key in configActions)
		{
			var iconCls = configActions[key].iconCls;
			var z = Ext.applyIf({cls: 'x-btn-large', iconCls: iconCls, text: ''}, configActions[key]);
			this.PanelActions[key] = new Ext.Action(z);
		}
		var actions_list = ['action_staff_actions', 'action_LpuPassport', 'action_LpuStructureView', 'action_AutoPersonCard', 'action_WorkGraph', 'action_OrgView', 'action_Documents', 'action_Users', /*'action_Doubles',*/ 'action_Spr', /*'action_DBStructure',*/ 'action_ReportEngine', 'action_mmunoprof', 'action_UserProfile', 'action_OptionsView','action_PersonUnionHistoryWindow','action_PersonDoublesModerationAction','action_OrgFarmacyByLpuView','action_Contragents','action_InvoiceView','action_ES','action_PlanVolume','action_SURDataView','action_PersonDopDispPlan','action_StoragePlacement', 'action_ElectronicQueueSpr', 'action_MedStaffFactReplace', 'action_LpuBuildingOffice', 'action_TFOMSQueryList', 'action_Treatments','action_LpuVIPPerson','action_Ers','action_EMD','action_EMD_EGISZ','action_AnalyzerQualityControl', 'action_WaitingListJournal'];
		if(getRegionNick() == 'penza') {
			actions_list.push('action_DispPlan');
		}
		// Создание кнопок для панели
		form.BtnActions = new Array();
		var i = 0;
		for(var key in form.PanelActions)
		{
			if (key.inlist(actions_list))
			{
				form.BtnActions.push(new Ext.Button(form.PanelActions[key]));
				i++;
			}
		}

		this.leftMenu = new Ext.Panel(
		{
			region: 'center',
			id:form.id+'_hhd',
			border: false,
			layout:'form',
			layoutConfig:
			{
				titleCollapse: true,
				animate: true,
				activeOnTop: false
			},
			items: form.BtnActions
		});

		this.leftPanel =
		{
			animCollapse: false,
			width: 52,
			minSize: 52,
			maxSize: 120,
			id: 'lawpwLeftPanel',
			region: 'west',
			floatable: false,
			collapsible: true,
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
					el = form.findById(form.id+'_slid');
					if(el)
						el.setHeight(this.body.dom.clientHeight-42);
					return;
				}
			},
			border: true,
			title: ' ',
			split: true,
			items: [
				new Ext.Button(
				{
					cls:'upbuttonArr',
					disabled: false,
					iconCls: 'uparrow',
					handler: function()
					{
						var el = form.findById(form.id+'_hhd');
						var d = el.body.dom;
						d.scrollTop -=38;
					}
				}),
				{
					layout:'border',
					id:form.id+'_slid',
					height:100,
					items:[this.leftMenu]
				},
				new Ext.Button(
				{
				cls:'upbuttonArr',
				iconCls:'downarrow',
				style:{width:'48px'},
				disabled: false,
				handler: function()
				{
					var el = form.findById(form.id+'_hhd');
					var d = el.body.dom;
					d.scrollTop +=38;

				}
				})]
		};

		/*
		this.LpuFilterPanel = new Ext.form.FieldSet(
		{
			bodyStyle:'width:100%;background:#DFE8F6;padding:0px;',
			border: true,
			autoHeight: true,
			region: 'north',
			layout: 'column',
			title: langs('Фильтры'),
			id: 'OrgLpuFilterPanel',
			items:
			[{
				// Левая часть фильтров
				labelAlign: 'top',
				layout: 'form',
				border: false,
				bodyStyle:'background:#DFE8F6;padding-right:5px;',
				columnWidth: .44,
				items:
				[{
					name: 'Org_Name',
					anchor: '100%',
					disabled: false,
					fieldLabel: langs('Наименование организации'),
					tabIndex: 0,
					xtype: 'textfield',
					id: 'lawpwOrg_Name'
				},
				{
					xtype: 'hidden',
					anchor: '100%'
				}]
			},
			{
				// Средняя часть фильтров
				labelAlign: 'top',
				layout: 'form',
				border: false,
				bodyStyle:'background:#DFE8F6;padding-left:5px;',
				columnWidth: .44,
				items:
				[{
					name: 'Org_Nick',
					anchor: '100%',
					disabled: false,
					fieldLabel: langs('Краткое наименование'),
					tabIndex: 0,
					xtype: 'textfield',
					id: 'lawpwOrg_Nick'
				},
				{
					xtype: 'hidden',
					anchor: '100%'
				}]
			},
			{
				// Правая часть фильтров (кнопка)
				layout: 'form',
				border: false,
				bodyStyle:'background:#DFE8F6;padding-left:5px;',
				columnWidth: .12,
				items:
				[{
					xtype: 'button',
					text: langs('Установить'),
					tabIndex: 4217,
					minWidth: 110,
					disabled: false,
					topLevel: true,
					allowBlank:true,
					id: 'lawpwButtonSetFilter',
					handler: function ()
					{
						Ext.getCmp('swLpuAdminWorkPlaceWindow').loadGridWithFilter();
					}
				},
				{
					xtype: 'button',
					text: langs('Отменить'),
					tabIndex: 4218,
					minWidth: 110,
					disabled: false,
					topLevel: true,
					allowBlank:true,
					id: 'lawpwButtonUnSetFilter',
					handler: function ()
					{
						Ext.getCmp('swLpuAdminWorkPlaceWindow').loadGridWithFilter(true);
					}
				}]
			}],
			keys: [{
				key: [
					Ext.EventObject.ENTER
				],
				fn: function(inp, e) {
					e.stopEvent();

					if ( e.browserEvent.stopPropagation )
						e.browserEvent.stopPropagation();
					else
						e.browserEvent.cancelBubble = true;

					if ( e.browserEvent.preventDefault )
						e.browserEvent.preventDefault();
					else
						e.browserEvent.returnValue = false;

					e.browserEvent.returnValue = false;
					e.returnValue = false;

					if (Ext.isIE)
					{
						e.browserEvent.keyCode = 0;
						e.browserEvent.which = 0;
					}

					Ext.getCmp('swLpuAdminWorkPlaceWindow').loadGridWithFilter();
				},
				stopEvent: true
			}]
		});

		// Организации
		this.LpuGrid = new sw.Promed.ViewFrame(
		{
			id: 'lawpwLpuGridPanel',
			tbar: this.gridToolbar,
			region: 'center',
			layout: 'fit',
			paging: true,
			object: 'Org',
			dataUrl: '/?c=Org&m=getOrgView',
			keys: [{
				key: [
					Ext.EventObject.F6
				],
				fn: function(inp, e) {
					e.stopEvent();

					if ( e.browserEvent.stopPropagation )
						e.browserEvent.stopPropagation();
					else
						e.browserEvent.cancelBubble = true;

					if ( e.browserEvent.preventDefault )
						e.browserEvent.preventDefault();
					else
						e.browserEvent.returnValue = false;

					e.browserEvent.returnValue = false;
					e.returnValue = false;

					if (Ext.isIE)
					{
						e.browserEvent.keyCode = 0;
						e.browserEvent.which = 0;
					}
					var grid = Ext.getCmp('lawpwLpuGridPanel');
					if (!grid.getAction('action_new').isDisabled()) {
						if (e.altKey) {
							AddRecordToUnion(
								grid.getGrid().getSelectionModel().getSelected(),
								'Org',
								langs('Организации'),
								function () {
									grid.loadData();
								}
							)
						}
					}
				},
				stopEvent: true
			}],
			//toolbar: true,
			root: 'data',
			totalProperty: 'totalCount',
			autoLoadData: false,
			stringfields:
			[
				// Поля для отображение в гриде
				{name: 'Org_id', type: 'int', header: 'ID', key: true},
				{name: 'Lpu_id', type: 'int', header: langs('ID ЛПУ'), key: true},
				{name: 'DLO', type:'checkbox', header: langs('ЛЛО'), width: 40},
				{name: 'OMS', type:'checkbox', header: langs('ОМС'), width: 40},
				{id: 'Lpu_Ouz', name: 'Lpu_Ouz', header: langs('Код ОУЗ'), width: 80},
				{name: 'Org_Name', id: 'autoexpand', header: langs('Полное наименование')},
				{name: 'Org_Nick', header: langs('Краткое наимнование'), width: 240},
				{name: 'KLArea_Name', header: langs('Территория'), width: 160},
				{name: 'Org_OGRN', header: langs('ОГРН'), width: 120},
				{name: 'Lpu_begDate', header: langs('Дата начала деятельности'), width: 80},
				{name: 'Lpu_endDate', header: langs('Дата закрытия'), width: 80},
				// Поля для отображения в дополнительной панели
				{name: 'UAddress_Address', hidden: true},
				{name: 'PAddress_Address', hidden: true}
			],
			actions:
			[
				{name:'action_add', handler: function()
					{
						getWnd('swLpuPassportEditWindow').show({
							action: 'add'
						});
					}
				},
				{name:'action_edit', iconCls : 'x-btn-text', icon: 'img/icons/lpu16.png', text: langs('Паспорт ЛПУ'), handler: function()
					{
						this.Lpu_id = Ext.getCmp('lawpwLpuGridPanel').ViewGridPanel.getSelectionModel().getSelected().get('Lpu_id');
						getWnd('swLpuPassportEditWindow').show({
							action: 'edit',
							Lpu_id: this.Lpu_id
						});
					}
				},
				{name:'action_view', iconCls : 'x-btn-text', icon: 'img/icons/lpu-struc16.png', text: langs('Структура ЛПУ'), handler: function()
					{
						this.Lpu_id = Ext.getCmp('lawpwLpuGridPanel').ViewGridPanel.getSelectionModel().getSelected().get('Lpu_id');
						getWnd('swLpuStructureViewForm').show({
							Lpu_id: this.Lpu_id
						});
					}
				},
				{name:'action_delete', hidden: true},
				{name:'action_refresh'},
				{name:'action_print'}
			],
			onRowSelect: function(sm,index,record)
			{
				var win = Ext.getCmp('swLpuAdminWorkPlaceWindow');
				var form = Ext.getCmp('lawpwLpuGridPanel');
				if ( win.mode && win.mode == 'lpu')
				{
					var Lpu_id = form.ViewGridPanel.getSelectionModel().getSelected().get('Lpu_id');
					form.getAction('action_edit').setDisabled( Lpu_id != getGlobalOptions().lpu_id && !isSuperAdmin() );
					form.getAction('action_view').setDisabled( Lpu_id != getGlobalOptions().lpu_id && !isSuperAdmin() );
				}
				var UAddress_Address = record.get('UAddress_Address');
				var PAddress_Address = record.get('PAddress_Address');
				win.LpuDetailTpl.overwrite(win.LpuDetailPanel.body, {UAddress_Address:UAddress_Address, PAddress_Address:PAddress_Address});
			}
		});

		this.LpuGrid.getGrid().view = new Ext.grid.GridView(
		{
			getRowClass : function (row, index)
			{
				var cls = '';
				if (row.get('Lpu_endDate')!=null && row.get('Lpu_endDate').length > 0)
					cls = cls+'x-grid-rowgray ';
				return cls;
			}
		});

		var LpuDetailTplMark =
		[
			'<div style="height:44px;">'+
				'<div>Юридический адрес: <b>{UAddress_Address}</b></div>'+
				'<div>Фактический адрес: <b>{PAddress_Address}</b></div>'+
			'</div>'
		];
		this.LpuDetailTpl = new Ext.Template(LpuDetailTplMark);
		this.LpuDetailPanel = new Ext.Panel(
		{
			id: 'LpuDetailPanel',
			bodyStyle: 'padding:2px',
			layout: 'fit',
			region: 'south',
			border: true,
			frame: true,
			height: 44,
			maxSize: 44,
			html: ''
		});

		Ext.apply(this,
		{
			layout: 'border',
			items:
			[
				this.LpuFilterPanel,
				this.leftPanel,
				{
					layout: 'fit',
					region: 'center',
					border: false,
					items:
					[
						this.LpuGrid
					]
				},
				this.LpuDetailPanel
			],
			buttons:
			[{
				text: '-'
			},
			HelpButton(this, TABINDEX_MPSCHED + 98),
			{
				iconCls: 'cancel16',
				text: BTN_FRMCLOSE,
				handler: function() {this.hide();}.createDelegate(this)
			}]
		});
		*/

		// <!-- #9565


		this.FilterPanel = new Ext.form.FormPanel({
			autoHeight: true,
			region: 'north',
			frame: true,
			items: [
				{
					layout: 'form',
					xtype: 'fieldset',
					autoHeight: true,
					collapsible: true,
					listeners: {
						collapse: function() {
							this.FilterPanel.doLayout();
							this.doLayout();
						}.createDelegate(this),
						expand: function() {
							this.FilterPanel.doLayout();
							this.doLayout();
						}.createDelegate(this)
					},
					labelAlign: 'right',
					title: langs('Поиск: фильтр не установлен'),
					items: [
						{
							layout: 'column',
							items: [
								{
									layout: 'form',
									defaults: {
										anchor: '100%'
									},
									labelWidth: 60,
									width: 300,
									items: [
										{
											xtype: 'textfield',
											name: 'Person_SurName',
											fieldLabel: langs('Фамилия')
										}, {
											xtype: 'textfield',
											name: 'Person_FirName',
											fieldLabel: langs('Имя')
										}, {
											xtype: 'textfield',
											name: 'Person_SecName',
											fieldLabel: langs('Отчество')
										}
									]
								}, {
									layout: 'form',
									width: 320,
									defaults: {
										anchor: '100%'
									},
									items: [
										{
											xtype: 'textfield',
											name: 'login',
											fieldLabel: langs('Логин')
										}, {
											xtype: 'swusersgroupscombo',
											hiddenName: 'group',
											listeners: {
												render: function() {
													if(this.getStore().getCount()==0)
														this.getStore().load();
												}
											},
											valueField: 'Group_Name',
											fieldLabel: langs('Группа')
										}, {
											xtype: 'textfield',
											name: 'desc',
											fieldLabel: langs('Описание')
										}
									]
								}
							]
						}, {
							layout: 'column',
							style: 'padding: 3px;',
							items: [
								{
									layout: 'form',
									items: [
										{
											handler: function() {
												this.doSearch();
											}.createDelegate(this),
											xtype: 'button',
											iconCls: 'search16',
											text: BTN_FRMSEARCH
										}
									]
								}, {
									layout: 'form',
									style: 'margin-left: 5px;',
									items: [
										{
											handler: function() {
												this.doReset();
											}.createDelegate(this),
											xtype: 'button',
											iconCls: 'resetsearch16',
											text: langs('Сброс')
										}
									]
								}
							]
						}
					]
				}
			],
			keys: [{
				fn: function(inp, e) {
					this.doSearch();
				},
				key: [ Ext.EventObject.ENTER ],
				scope: this,
				stopEvent: true
			}]
		});

		this.Grid = new sw.Promed.ViewFrame({
			id: this.id + '_Grid',
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 100,
			region: 'center',
			pageSize: 100,
			actions: [
				{ name: 'action_add', handler: this.editUser.createDelegate(this, ['add']) },
				{ name: 'action_edit', handler: this.editUser.createDelegate(this, ['edit']) },
				{ name: 'action_view', hidden: true, handler: this.editUser.createDelegate(this, ['view']) },
				{ name: 'action_delete', handler: this.deleteUser.createDelegate(this) },
				{ name: 'action_refresh' },
				{ name: 'action_print' }
			],
			autoLoadData: false,
			stripeRows: true,
			stringfields: [
				{ name: 'pmUser_id', key: true },
				{ name: 'login', header: langs('Логин'), width: 150 },
				{ name: 'surname', header: langs('Фамилия'), width: 150 },
				{ name: 'name', header: langs('Имя'), width: 150 },
				{ name: 'secname', header: langs('Отчество'), width: 150 },
				{ name: 'groups', header: langs('Группы'), renderer: function(value, cellEl, rec) {
					var result = '';
					if (!Ext.isEmpty(value)) {
						// разджейсониваем
						var groups = Ext.util.JSON.decode(value);
						for(var k in groups) {
							// Костыль для Chrome - берем только элементы с числовыми идентификаторами
							// @task https://redmine.swan.perm.ru/issues/102122
							if (parseInt(k) == k && !Ext.isEmpty(groups[k].name)) {
								if (!Ext.isEmpty(result)) {
									result += ', ';
								}
								result += groups[k].name;
							}
						}
					}
					return result;
				}, width: 300 },
				{ name: 'desc', header: langs('Описание'), id: "autoexpand" },
				{ name: 'IsMedPersonal', type: 'checkcolumn', header: langs('Врач') }
			],
			paging: true,
			dataUrl: '/?c=User&m=getUsersListOfCache',
			root: 'data',
			totalProperty: 'totalCount'
		});

		this.Grid.ViewGridPanel.getStore().on('load', function() {
			this.Grid.ViewGridPanel.getSelectionModel().selectFirstRow();
		}.createDelegate(this));

		this.Grid.ViewGridPanel.getSelectionModel().on('rowselect', function(sm, rowIndex, rec) {
			var groups = (rec.get('groups'))?rec.get('groups').split(', '):[],
				actions = this.Grid.ViewActions,
				thereIsSuperAdmin = false;
			for(var i=0; i<groups.length; i++) {
				if(groups[i] == 'SuperAdmin') {
					thereIsSuperAdmin = true;
					break;
				}
			}
			actions.action_delete.setDisabled(!(isSuperAdmin() || isLpuAdmin()));
			actions.action_edit.setDisabled( thereIsSuperAdmin );
		}.createDelegate(this));

		Ext.apply(this, {
			layout: 'border',
			items: [
				this.FilterPanel,
				this.leftPanel,
				this.Grid
			],
			buttons: [{
				text: '-'
			},
			HelpButton(this, TABINDEX_MPSCHED + 98),
			{
				iconCls: 'cancel16',
				text: BTN_FRMCLOSE,
				handler: this.hide.createDelegate(this, [])
			}]
		});
		// -->

		sw.Promed.swLpuAdminWorkPlaceWindow.superclass.initComponent.apply(this, arguments);



	}

});

