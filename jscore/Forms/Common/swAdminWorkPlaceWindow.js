/**
 * swAdminWorkPlaceWindow - окно рабочего места администратора
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/projects/promed
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009-2011, Swan.
 * @author       Chebukin Alexander
 * @prefix       awpw
 * @version      декабрь 2011 
 */
/*NO PARSE JSON*/


sw.Promed.swAdminWorkPlaceWindow = Ext.extend(sw.Promed.BaseForm,
{
	objectName: 'swAdminWorkPlaceWindow',
	objectSrc: '/jscore/Forms/Common/swAdminWorkPlaceWindow.js',
	closable: true,
	closeAction: 'hide',
	layout: 'border',
	maximized: true,
	title: langs('Рабочее место администратора'),
	iconCls: 'admin16',
	id: 'swAdminWorkPlaceWindow',
	show: function()
	{
		sw.Promed.swAdminWorkPlaceWindow.superclass.show.apply(this, arguments);

		var loadMask = new Ext.LoadMask(Ext.get('swAdminWorkPlaceWindow'), {msg: LOAD_WAIT});
		loadMask.show();
		var form = this;
		
		form.loadGridWithFilter(true);
		if (arguments[0]) {
			sw.Promed.MedStaffFactByUser.setMenuTitle(this, arguments[0]);
		}
		this.ARMType = arguments[0].ARMType;
		
		loadMask.hide();

	},
	clearFilters: function ()
	{
		this.findById('awpwOrg_Nick').setValue('');
		this.findById('awpwOrg_Name').setValue('');
	},
	loadGridWithFilter: function(clear)
	{
		var form = this;
		if (clear)
			form.clearFilters();
		var OrgNick = this.findById('awpwOrg_Nick').getValue();
		var OrgName = this.findById('awpwOrg_Name').getValue();
		var filters = {Nick: OrgNick, Name: OrgName, start: 0, limit: 100, mode: 'lpu'};
		form.LpuGrid.loadData({globalFilters: filters});
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
				iconCls : 'docs_templ-16',
				handler: function() 
				{
					getWnd('swTemplSearchWindow').show();
				}
			},
			MarkerAction: {
				tooltip: langs('Список маркеров'),
				text: langs('Список маркеров'),
				iconCls : 'test16',
				handler: function() 
				{
					getWnd('swMarkerSearchWindow').show();
				},
				hidden: !isSuperAdmin()
			},
			ParameterValueAction: {
				tooltip: langs('Список параметров'),
				text: langs('Список параметров'),
				iconCls : 'test16',
				handler: function() 
				{
					getWnd('swParameterValueListWindow').show();
				}
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
			UslugaTreeAction: {
				text: langs('Справочник услуг'),
				tooltip: langs('Справочник услуг'),
				iconCls: 'services-complex16',
				handler: function() {
					getWnd('swUslugaTreeWindow').show();
				},
				hidden: !isAdmin
			},
			swMKB10Action: {
				text: langs('Справочник МКБ-10'),
				tooltip: langs('Справочник МКБ-10'),
				iconCls: 'spr-mkb16',
				handler: function()
				{
					if ( !getWnd('swMkb10SearchWindow').isVisible() )
						getWnd('swMkb10SearchWindow').show();
				},
				hidden: !isAdmin
			},
			worksheetList: {
				text: langs('Конструктор анкет'),
				tooltip: langs('Конструктор анкет'),
				handler: function() {
					getWnd('worksheetListWindow').show();
				},
				hidden: !isAdmin
			},
			ElectronicQueueAction: {
				text: 'Справочник электронных очередей', //'Электронная очередь',
				tooltip: 'Справочник электронных очередей',
				//iconCls: '',
				handler: function()
				{
					if ( !getWnd('swElectronicQueueListWindow').isVisible() )
						getWnd('swElectronicQueueListWindow').show();
				},
				hidden: !isAdmin
			},
			ElectronicScoreboardAction: {
				text: 'Справочник электронных табло', //'Электронное табло',
				tooltip: 'Справочник электронных табло',
				//iconCls: '',
				handler: function()
				{
					if ( !getWnd('swElectronicScoreboardListWindow').isVisible() )
						getWnd('swElectronicScoreboardListWindow').show();
				},
				hidden: !isAdmin
			},
			ElectronicInfomatAction: {
				text: 'Справочник инфоматов', //'Инфомат',
				tooltip: 'Справочник инфоматов',
				//iconCls: '',
				handler: function()
				{
					if ( !getWnd('swElectronicInfomatListWindow').isVisible() )
						getWnd('swElectronicInfomatListWindow').show();
				},
				hidden: !isAdmin
			},
			ElectronicTreatmentAction: {
				text: 'Справочник поводов обращений',
				tooltip: 'Справочник поводов обращений',
				//iconCls: '',
				handler: function()
				{
					if ( !getWnd('swElectronicTreatmentListWindow').isVisible() )
						getWnd('swElectronicTreatmentListWindow').show();
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
					getWnd('swMesOldSearchWindow').show({ARMType: 'superadmin'});
				},
				hidden: getRegionNick().inlist(['by'])
			},
			swOrgTypeAction: {
				text: langs('Типы организаций'),
				tooltip: langs('Справочник типов организаций'),
				// iconCls: 'spr-mes16',
				handler: function()
				{
					getWnd('swOrgTypeWindow').show();
				},
				hidden: false
			},
			swDrugDocumentClassAction: {
				text: langs('Виды заявок на медикаменты'),
				tooltip: langs('Справочник видов заявок на медикаменты'),
				// iconCls: 'spr-mes16',
				handler: function()
				{
					getWnd('swDrugDocumentClassViewWindow').show();
				},
				hidden: false
			},
			swDrugDocumentStatusAction: {
				text: langs('Статусы заявок на медикаменты'),
				tooltip: langs('Справочник статусов заявок на медикаменты'),
				// iconCls: 'spr-mes16',
				handler: function()
				{
					getWnd('swDrugDocumentStatusViewWindow').show();
				},
				hidden: false
			},
			swUnitSprAction: {
				text: langs('Единицы измерения'),
				tooltip: langs('Единицы измерения'),
				// iconCls: 'spr-mes16',
				handler: function()
				{
					getWnd('swUnitSprViewWindow').show();
				},
				hidden: false
			},
			swGoodsUnitAction: {
				text: langs('Единицы измерения товара'),
				tooltip: langs('Единицы измерения товара'),
				handler: function()
				{
					getWnd('swGoodsUnitViewWindow').show({allowImportFromRls: true});
				}
			},
			swStructuredParamsEditorAction: {
				text: langs('Структурированные параметры'),
				tooltip: langs('Справочник структурированных параметров'),
				iconCls: 'spr-lpu16',
				handler: function()
				{
					getWnd('swStructuredParamsEditorWindow').show();
				},
				hidden: false
			},
			swQuestionTypeVisionAction: {
				text: 'Настройка отображения анкет',
				tooltip: 'Настройка отображения анкет',
				iconCls: '',
				handler: function() {
					getWnd('swQuestionTypeVisionEditWindow').show({action: 'edit'});
				},
				hidden: !isAdmin || IS_DEBUG != 1
			},
			swAttributeAction: {
				text: langs('Атрибуты'),
				tooltip: langs('Атрибуты'),
				iconCls: '',
				handler: function() {
					getWnd('swAttributeViewWindow').show();
				},
				hidden: !isAdmin
			},
			swTableDirectAction: {
				text: langs('Базовые справочники атрибутов'),
				tooltip: langs('Базовые справочники атрибутов'),
				iconCls: '',
				handler: function() {
					getWnd('swTableDirectViewWindow').show();
				},
				hidden: !isAdmin
			},
			TariffVolumeViewAction: {
				text: langs('Тарифы и объемы'),
				tooltip: langs('Тарифы и объемы'),
				// iconCls : 'service-reestrs16',
				handler: function() {
					getWnd('swTariffVolumesViewWindow').show();
				}
			},
			TariffVolumeAttributeViewAction: {
				text: langs('Справочник атрибутов тарифов и объемов'),
				tooltip: langs('Справочник атрибутов тарифов и объемов'),
				// iconCls : 'service-reestrs16',
				handler: function() {
					getWnd('swTariffVolumesAttributeViewWindow').show();
				}
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
			swAttributeVisionAction: {
				text: langs('Области видимости атрибутов'),
				tooltip: langs('Области видимости атрибутов'),
				iconCls: '',
				handler: function() {
					getWnd('swAttributeVisionViewWindow').show();
				},
				hidden: !isAdmin
			},
			swSprPromedAction: {
				text: langs('Справочники'),
				tooltip: langs('Справочники'),
				iconCls: 'spr-promed16',
				handler: function() {
					getWnd('swDirectoryViewWindow').show();
				},
				hidden: !isAdmin
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
				hidden: !(getGlobalOptions().superadmin && IS_DEBUG)
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
			DrugNonpropNamesAction: {
				name: 'action_DrugNonpropNames',
				tooltip: langs('Непатентованные наименования'),
				text: langs('Непатентованные наименования'),
				handler: function() {
					getWnd('swDrugNonpropNamesViewWindow').show();
				}.createDelegate(this)
			},
			ExtemporalAction: {
				name: 'action_Extemporal',
				tooltip: langs('Экстемпоральные рецептуры'),
				text: langs('Экстемпоральные рецептуры'),
				handler: function() {
					getWnd('swExtemporalViewWindow').show();
				}.createDelegate(this)
			},
			DrugRMZAction: {
				name: 'action_DrugRMZ',
				text: langs('Справочник РЗН'),
				iconCls : 'view16',
				handler: function() {
					getWnd('swDrugRMZViewWindow').show();
				}.createDelegate(this)
			},
			GoodsStorageAction: {
				name: 'action_GoodsStorageView',
				text: langs('Наименования мест хранения'),
				iconCls : '',
				handler: function()
				{
					getWnd('swGoodsStorageViewWindow').show({readOnly: false});
				}
			},
			DrugMnnCodeAction: {
				name: 'action_DrugMnnCodeSpr',
				text: langs('Справочник МНН'),
				iconCls : '',
				handler: function()
				{
					getWnd('swDrugMnnCodeViewWindow').show({readOnly: false});
				}
			},
			DrugTorgCodeAction: {
				name: 'action_DrugTorgCodeSpr',
				text: langs('Справочник Торговых наименований'),
				iconCls : '',
				handler: function()
				{
					getWnd('swDrugTorgCodeViewWindow').show({readOnly: false});
				}
			},
			DrugTorgSearchCodeAction: {
				name: 'action_DrugTorgSearch',
				text: langs('Поиск медикаментов по торговому наименованию'),
				iconCls : '',
				hidden: !(getRegionNick() == 'ufa' || getGlobalOptions().pmuser_id.inlist(['255436352404', '256064768703', '257186302627'])),
				handler: function()
				{
					getWnd('swRlsDrugTorgSearchWindow').show({parent: 'swAdminWorkPlaceWindow', EanFilterEnabled: true});
				}
			},
			swPrepBlockSprAction: {
				text: langs('Справочник фальсификатов и забракованных серий ЛС'),
				tooltip: langs('Справочник фальсификатов и забракованных серий ЛС'),
				handler: function()
				{
					getWnd('swPrepBlockViewWindow').show();
				}
			},
			PriceJNVLPAction: {
				name: 'action_PriceJNVLP',
				hidden: getRegionNick().inlist(['by']),
				text: langs('Цены на ЖНВЛП'),
				iconCls : 'dlo16',
				handler: function() {
					getWnd('swJNVLPPriceViewWindow').show();
				}
			},
			DrugMarkupAction: {
				name: 'action_DrugMarkup',
				hidden: getRegionNick().inlist(['by']),
				text: langs('Предельные надбавки на ЖНВЛП'),
				iconCls : 'lpu-finans16',
				handler: function() {
					getWnd('swDrugMarkupViewWindow').show();
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
				text: (getRegionNick() == 'kz') ? langs('Территории субъекта РК') : langs('Территории субъекта РФ'),
				tooltip: (getRegionNick() == 'kz') ? langs('Территории субъекта РК') : langs('Территории субъекта РФ'),
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
			}
		});

		var form = this;
		// Формирование списка всех акшенов 
		var configActions = 
		{
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
					sw.Promed.Actions.swLpuUslugaAction,
					sw.Promed.Actions.swUslugaGostAction,
					sw.Promed.Actions.UslugaComplexViewAction,
					sw.Promed.Actions.UslugaTreeAction,
					sw.Promed.Actions.swMKB10Action,
					sw.Promed.Actions.worksheetList,
                    {
                        text: langs('МЭС'),
                        tooltip: langs('МЭС'),
                        iconCls: 'spr-mes16',
                        menu: new Ext.menu.Menu({
                            items:[
                                sw.Promed.Actions.swMESAction,
                                sw.Promed.Actions.swMESOldAction
                            ]
                        })
                    },	{
						text: langs('Стандарты лечения'),
						tooltip: langs('Стандарты лечения'),
						iconCls: '',
						handler: function()
						{
							getWnd('swCureStandartListWindow').show({ARMType: this.ARMType});
						}.createDelegate(this)
					}, {
						text: langs('Маршрутизация и сферы ответственности МО'),
						tooltip: langs('Маршрутизация и сферы ответственности МО'),
						iconCls: '',
						handler: function()
						{
							getWnd('swRoutingManagerWindow').show();
						}.createDelegate(this)
					},
					//sw.Promed.Actions.swMESAction,
					//sw.Promed.Actions.swMESOldAction,
					{
						text: langs('Справочники системы учета медикаментов'),
						tooltip: langs('Справочники системы учета медикаментов'),
						iconCls: '',
						handler: function()
						{
							getWnd('swDrugDocumentSprWindow').show({ARMType: this.ARMType});
						}.createDelegate(this)
					},
					sw.Promed.Actions.DrugListAction,
                    sw.Promed.Actions.DrugNomenAction,
					{
						text: langs('Лекарственные средства'),
						tooltip: langs('Лекарственные средства'),
						iconCls: '',
						menu: new Ext.menu.Menu({
							items:[
								sw.Promed.Actions.DrugMnnCodeAction,
								sw.Promed.Actions.DrugTorgCodeAction,
								sw.Promed.Actions.DrugTorgSearchCodeAction,
								sw.Promed.Actions.DrugNonpropNamesAction,
								sw.Promed.Actions.ExtemporalAction,
								sw.Promed.Actions.DrugMarkupAction,
								sw.Promed.Actions.PriceJNVLPAction,
								sw.Promed.Actions.swPrepBlockSprAction,
								sw.Promed.Actions.SprRlsAction,
								sw.Promed.Actions.DrugRMZAction
							]
						})
					},
					sw.Promed.Actions.swOrgTypeAction,
					sw.Promed.Actions.GoodsStorageAction,
                    {
                        text: langs('Заявки на медикаменты'),
                        tooltip: langs('Заявки на медикаменты'),
                        iconCls: '',
                        menu: new Ext.menu.Menu({
                            items:[
                                sw.Promed.Actions.swDrugDocumentClassAction,
                                sw.Promed.Actions.swDrugDocumentStatusAction
                            ]
                        })
                    },
					//sw.Promed.Actions.swDrugDocumentClassAction,
					//sw.Promed.Actions.swDrugDocumentStatusAction,
					sw.Promed.Actions.swUnitSprAction,
					sw.Promed.Actions.swGoodsUnitAction,
					sw.Promed.Actions.swStructuredParamsEditorAction,
					sw.Promed.Actions.swQuestionTypeVisionAction,
                    {
                        text: langs('Атрибуты'),
                        tooltip: langs('Атрибуты'),
                        iconCls: '',
                        menu: new Ext.menu.Menu({
                            items:[
                                sw.Promed.Actions.swAttributeAction,
                                sw.Promed.Actions.swAttributeVisionAction,
                                sw.Promed.Actions.swTableDirectAction
                            ]
                        })
                    },
					//sw.Promed.Actions.swAttributeAction,
					//sw.Promed.Actions.swAttributeVisionAction,
					//sw.Promed.Actions.swTableDirectAction,
					sw.Promed.Actions.SprDloAction,
					{
						text: langs('Настройка тарифов и объемов'),
						tooltip: langs('Настройка тарифов и объемов'),
						iconCls: '',
						menu: new Ext.menu.Menu({
							items: [
								sw.Promed.Actions.TariffVolumeViewAction,
								sw.Promed.Actions.TariffVolumeAttributeViewAction,
								sw.Promed.Actions.TariffValueListAction
							]
						})
					},
					'-',
					sw.Promed.Actions.swSprPromedAction,
					sw.Promed.Actions.SprLpuAction,
					sw.Promed.Actions.SprOmsAction,
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
        				text: langs('Регистр БСК: Администрирование'),
        				tooltip: langs('Регистр БСК: Администрирование'),
        				iconCls: 'otd-profile16',
        				handler: function()
        				{
        					getWnd('AdminBSKViewForm').show();
        				},
        				hidden: getRegionNick().inlist(['kz'])
        			},
					{
						text: 'Справочник связи МО с бюро МСЭ',
						tooltip: 'Справочник связи МО с бюро МСЭ',
						iconCls: '',
						handler: function() {
							getWnd('swLpuMseLinkViewWindow').show({ARMType: this.ARMType});
						}.createDelegate(this)
					},
					{
						text: langs('Профилактические прививки'),
						tooltip: langs('Профилактические прививки'),
						iconCls: 'immunoprof16',
						hidden: getRegionNick().inlist(['ufa']),
						handler: function() {
							getWnd('swVaccinationTypeWindow').show();
						}.createDelegate(this)
					}
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
				menu: [{
					text: langs('Обновление регистров'),//<-refs #20773
					tooltip: langs('Обновление регистров'),//<-refs #20773
					iconCls: 'patient-search16',
					hidden: !getGlobalOptions().superadmin || getRegionNick().inlist(['by']),
					handler: function(){
						getWnd('swImportWindow').show();
					}
				},{
					text: langs('Журнал работы сервисов'),
					tooltip: langs('Журнал работы сервисов'),
					handler: function(){
						getWnd('swServiceListLogWindow').show({ARMType: this.ARMType});
					}.createDelegate(this)
				},{
					text: langs('Заполнение OID'),
					disabled: false,
					handler: function(){
						getWnd('swOIDsetWindow').show();
					}.createDelegate(this)
				},{
					name: 'download_med_staff',
					text: langs('Выгрузить ФРМР'),
					hidden: true,//!isAdmin || getRegionNick().inlist(['by']),
					handler: function()
					{
						getWnd('swErmpExportSelectWindow').show();
					}.createDelegate(this)
				},{
					name: 'download_med_staff',
					text: langs('Импорт данных ФРМР'),
					hidden: (getGlobalOptions().region.nick!='kareliya'),
					handler: function()
					{
						getWnd('swXmlImportWindow').show({Fl:1,RegisterList_Name:'MedPersonal',RegisterList_id:4});
					}.createDelegate(this)
				},{
					name: 'download_med_staff_ermp',
					text: langs('Выгрузить штатное расписание для ФРМР'),
					hidden: true,//!isAdmin || getRegionNick().inlist(['by']),
					handler: function()
					{
						getWnd('swErmpStaffExportSelectWindow').show();
					}.createDelegate(this)
				},{
					name: 'download_attached_list',
					tooltip: 'Выгрузка списка прикреплённого населения',
					text: 'Выгрузка списка прикреплённого населения',
					hidden: !(isAdmin&&isExpPop()),
					handler: function()
					{
						getWnd('swPersonXmlWindow').show();
					}.createDelegate(this)
				},{
					name: 'download_attached_list_csv',
					text: langs('Выгрузить список прикрепленного населения в CSV'),
					hidden: ((getRegionNick() != 'pskov') || !(isAdmin&&isExpPop())),
					handler: function()
					{
						getWnd('swPersonCSVWindow').show();
					}.createDelegate(this)
				},{
					name: 'export_person_attaches',
					text: langs('Выгрузить список прикрепленного населения за период'),
					hidden: ((getRegionNick() != 'buryatiya') || !(isAdmin&&isExpPop())),
					handler: function()
					{
						getWnd('swPersonAttachesExportWindow').show();
					}.createDelegate(this)
				},{
					name: langs('Реестр медицинских работников (ОМС)'),
					text: langs('Выгрузка реестра мед работников на дату'),
					handler: function()
					{
						getWnd('swMPExportToXML').show();
					}.createDelegate(this)
				},{
					name: langs('Выгрузка регистра медработников для ФРМР новый'),
					text: langs('Выгрузка регистра медработников для ФРМР новый'),
					hidden: false,
					handler: function()
					{
						getWnd('swExportMedPersonalToXMLFRMPWindow').show();
					}.createDelegate(this)
				},{
					name: langs('Выгрузка штатного расписания для ФРМР новый'),
					text: langs('Выгрузка штатного расписания для ФРМР новый'),
					hidden: false,
					handler: function()
					{
						getWnd('swExportMedPersonalToXMLFRMPStaffWindow').show();
					}.createDelegate(this)
				},{
					name: 'download_qwerty_lpu_q',
					text: langs('ЛЛО. Выгрузка справочников в Dbase (*.dbf)'),
					hidden: getRegionNick().inlist(['kz']),
					handler: function()
                    {
                        getWnd('swQueryToDbfExporterWindow').show();
                    }.createDelegate(this)
				},{
					name: 'download_qwerty_lpu_q',
					text: langs('Выгрузка для QWERTY LPU_Q'),
					hidden: !isAdmin || getRegionNick().inlist(['by']),
					handler: function()
					{
						var fd = 'swLpuStructureStaffExport2Dbf';
						var params = {
							onHide: Ext.emptyFn,
							query2export: 'LPU_Q',
							queryName: langs('Выгрузка для QWERTY LPU_Q')
						};
						getWnd(fd).show(params);
					}.createDelegate(this)
				},{
					name: 'download_qwerty_frl',
					text: langs('Выгрузка для QWERTY ФРЛ'),
					hidden: !isAdmin || getRegionNick().inlist(['by']),
					handler: function()
					{
						var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Файл формируется..." });
						loadMask.show();
						Ext.Ajax.request({
							method: 'post',
							url: "/?c=ImportSchema&m=Export",
							callback: function(opt, success, r) {
								loadMask.hide();
								var obj = Ext.util.JSON.decode(r.responseText);
								if( obj.success ) {
									window.open('/'+obj.url);
								}
							}.createDelegate(this)
						});
					}.createDelegate(this)
				},
				{
					name: 'download_qwerty_frl',
					text: langs('Выгрузка для QWERTY ФРЛ (Полная)'),
					hidden: !isAdmin || getRegionNick().inlist(['by']),
					handler: function()
					{
						var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Файл формируется..." });
						loadMask.show();
						Ext.Ajax.request({
							params:{full:true},
							method: 'post',
							url: "/?c=ImportSchema&m=Export",
							callback: function(opt, success, r) {
								loadMask.hide();
								var obj = Ext.util.JSON.decode(r.responseText);
								if( obj.success ) {
									window.open('/'+obj.url);
								}
							}.createDelegate(this)
						});
					}.createDelegate(this)
				},{
					name: 'download_qwerty_svf_q',
					text: langs('Выгрузка для QWERTY SVF_Q'),
					hidden: !isAdmin || getRegionNick().inlist(['by']),
					handler: function()
					{
						var fd = 'swLpuStructureStaffExport2Dbf';
						var params = {
							onHide: Ext.emptyFn,
							query2export: 'SVF_Q',
							queryName: langs('Выгрузка для QWERTY SVF_Q')
						};
						getWnd(fd).show(params);
					}.createDelegate(this)
				},{
					name: 'download_qwerty_svf_q_2',
					text: langs('Выгрузка для QWERTY SVF_Q_2'),
					hidden: !isAdmin || getRegionNick().inlist(['by']),
					handler: function()
					{
						var fd = 'swLpuStructureStaffExport2Dbf';
						var params = {
							onHide: Ext.emptyFn,
							query2export: 'SVF_Q_2',
							queryName: langs('Выгрузка для QWERTY SVF_Q_2')
						};
						getWnd(fd).show(params);
					}.createDelegate(this)
				},{
					name: 'download_reg_fond',
					text: langs('Регистр ФОМС (старый)'),
					hidden: !isAdmin || getRegionNick().inlist(['by']),
					handler: function()
					{
						var fd = 'swLpuStructureStaffExport2Dbf';
						var params = {
							onHide: Ext.emptyFn,
							query2export: 'REG_FOND',
							queryName: langs('Регистр ФОМС (старый)')
						};
						getWnd(fd).show(params);
					}.createDelegate(this)
				},{
					name: 'download_reg_fond',
					text: langs('Регистр ФОМС (новый)'),
					hidden: !isAdmin || getRegionNick().inlist(['by']),
					handler: function()
					{
						var fd = 'swLpuStructureStaffExport2Dbf';
						var params = {
							onHide: Ext.emptyFn,
							query2export: 'REG_FOND_NEW',
							queryName: langs('Регистр ФОМС (новый)')
						};
						getWnd(fd).show(params);
					}.createDelegate(this)
				},{
					name: 'check_lpu_FRMP',
					hidden: getRegionNick().inlist(['by']),
					text: langs('Проверить ЛПУ ФРМР'),
					handler: function() {
						getWnd('swUploadFileForCheckLpuWindow').show();
					}
				},{
					text: langs('Реестры неработающих застрахованных лиц'),
					hidden: getRegionNick().inlist(['by']),
					disabled: false,
					handler: function(){
						getWnd('swPersonPolisExportWindow').show();
					}
				},{
					text: langs('Экспорт данных для ТФОМС и СМО'),
					disabled: false,
					hidden: !getRegionNick().inlist(['astra','kareliya','khak','krym', 'vologda']),
					handler: function(){
						getWnd('swHospDataExportForTfomsWindow').show({ARMType: this.ARMType});
					}.createDelegate(this)
				},{
					text: langs('Импорт данных из ТФОМС и СМО'),
					disabled: false,
					hidden: getRegionNick() != 'astra',
					handler: function(){
						getWnd('swHospDataImportFromTfomsWindow').show();
					}.createDelegate(this)
				},{
					text: langs('Выгрузка регистра онкобольных'),
					disabled: false,
					handler: function(){
						getWnd('swMorbusOnkoDataExportWindow').show();
					}.createDelegate(this)
				},{
					text: langs('Мониторинг паспортов мед. организаций'),
					disabled: false,
					handler: function(){
						getWnd('swLpuPassportReportWindow').show();
					}.createDelegate(this)
				},{
					text: langs('Выгрузка паспортов МО'),
					disabled: false,
					hidden: !getRegionNick().inlist(['astra','perm','pskov','ekb']),
					handler: function(){
						getWnd('swLpuPassportExportXmlWindow').show();
					}.createDelegate(this)
				},{
					name: 'download_medsert_list',
					text: langs('Выгрузка сертификатов мед. работников в XML'),
					hidden: getRegionNick() != 'astra',
					handler: function()
					{
						getWnd('swMedCert2XMLWindow').show();
					}.createDelegate(this)
				},{
					name: 'import_ksg_volumes',
					text: 'Загрузка объемов КСГ',
					hidden: getRegionNick() != 'ufa',
					handler: function()
					{
						getWnd('swKSGVolumesImportWindow').show();
					}.createDelegate(this)
				}, {
					tooltip: langs('Экспорт данных для ТФОМС и СМО'),
					text: langs('Экспорт данных для ТФОМС и СМО'),
					hidden: !getRegionNick().inlist(['penza']),
					handler: function() {
						getWnd('swHospDataExportForTfomsWindow').show({ARMType: this.ARMType});
					}.createDelegate(this)
				}, {
					name: 'export_data_to_frmo',
					text: 'Передача данных в сервис ФРМО',
					handler: function() {
						getWnd('swExportToFRMOWindow').show();
					}.createDelegate(this)
				}, {
					name: 'export_data_to_frmr',
					text: 'Передача данных в сервис ФРМР',
					handler: function() {
						getWnd('swExportToFRMRWindow').show();
					}.createDelegate(this)
				}, {
					name: 'import_data_from_frmo',
					text: 'Импорт данных из сервиса ФРМО',
					hidden: !getRegionNick().inlist([ 'vologda' ]),
					handler: function() {
						getWnd('swImportFromFRMOWindow').show();
					}.createDelegate(this)
				}, {
					nn: 'action_ExportEvnPrescrMse',
					text: 'Экспорт направлений на МСЭ',
					tooltip: 'Экспорт направлений на МСЭ',
					hidden: getRegionNick() == 'kz',
					handler: function() {
						getWnd('swEvnPrescrMseExportWindow').show({ARMType: this.ARMType});
					}.createDelegate(this)
				}, {
					nn: 'action_ImportEvnMse',
					text: 'Импорт обратных талонов',
					tooltip: 'Импорт обратных талонов',
					hidden: getRegionNick() == 'kz',
					handler: function() {
						getWnd('swEvnMseImportWindow').show({ARMType: this.ARMType});
					}.createDelegate(this)
				}, {
					nn: 'action_PersonIdentPackage',
					text: 'Пакетная идентификация ТФОМС',
					tooltip: 'Пакетная идентификация ТФОМС',
					hidden: getRegionNick() != 'krym',
					handler: function() {
						getWnd('swPersonIdentPackageWindow').show({ARMType: this.ARMType});
					}.createDelegate(this)
				}, {
					nn: 'action_ReceptTaskView',
					text: getRegionNick() == 'vologda' ? 'Журнал запросов ГП Фармация' : 'Журнал работы заданий',
					tooltip: getRegionNick() == 'vologda' ? 'Журнал запросов ГП Фармация' : 'Журнал работы заданий',
					hidden: getRegionNick() != 'vologda',
					handler: function() {
                        getWnd('swReceptTaskViewWindow').show();
					}.createDelegate(this)
				}, {
					nn: 'action_RecalcKSGKSLP',
					text: 'Переопределение КСГ/КСЛП',
					tooltip: 'Переопределение КСГ/КСЛП',
					hidden: getRegionNick() == 'kz',
					handler: function() {
						if (!getWnd('swRecalcKSGKSLPWindow').isVisible())
							getWnd('swRecalcKSGKSLPWindow').show();
					}.createDelegate(this)
				}, {
					text: langs('Импорт карт СМП'),
					hidden: (!getGlobalOptions().region || getGlobalOptions().region.nick != 'perm'),
					handler: function() {
						getWnd('swAmbulanceCardImportDbfWindow').show();
					}
				}, {
					nn: 'action_ExportPersonCardAttach',
					text: 'Экспорт заявлений о прикреплении',
					tooltip: 'Экспорт заявлений о прикреплении',
					hidden: !getRegionNick().inlist(['vologda']) || !isUserGroup('ExportAttachedPopulation'),
					handler: function() {
						getWnd('swPersonCardAttachExportWindow').show({ARMType: this.ARMType});
					}.createDelegate(this)
				}, {
					nn: 'action_ImportPersonCardAttachResponse',
					text: 'Импорт ответа по заявлениям о прикреплении',
					tooltip: 'Импорт ответа по заявлениям о прикреплении',
					hidden: !getRegionNick().inlist(['vologda']) || !isUserGroup('ExportAttachedPopulation'),
					handler: function() {
						getWnd('swPersonCardAttachImportResponseWindow').show({ARMType: this.ARMType});
					}.createDelegate(this)
				}, {
					nn: 'action_ImportDetachPersonCard',
					text: 'Импорт сведений о ЗЛ, открепленных от МО',
					tooltip: 'Импорт сведений о ЗЛ, открепленных от МО',
					hidden: !getRegionNick().inlist(['vologda']) || !isUserGroup('ExportAttachedPopulation'),
					handler: function() {
						getWnd('swPersonCardImportDetachWindow').show({ARMType: this.ARMType});
					}.createDelegate(this)
				}, {
					nn: 'action_ImportPersonCardRegister',
					text: 'Импорт регистра прикрепленного населения',
					tooltip: 'Импорт регистра прикрепленного населения',
					hidden: !getRegionNick().inlist(['vologda']) || !isUserGroup('ExportAttachedPopulation'),
					handler: function() {
						getWnd('swPersonCardImportRegisterWindow').show({ARMType: this.ARMType});
					}.createDelegate(this)
				}, {
					nn: 'action_ExportAttachments_Applications',
					text: 'Экспорт прикреплений/ заявлений',
					tooltip: 'Экспорт прикреплений/ заявлений',
					hidden: !getRegionNick().inlist(['buryatiya']) || !isUserGroup('ExportAttachedPopulation'),
					handler: function() {
						getWnd('swExportAttachmentsApplicationsWindow').show({ARMType: 'superadmin'});
					}.createDelegate(this)
				}, {
					nn: 'action_ImportErrors_FLKforZL',
					text: 'Импорт ошибок ФЛК по ЗЛ',
					tooltip: 'Импорт ошибок ФЛК по ЗЛ',
					hidden: !getRegionNick().inlist(['buryatiya']) || !isUserGroup('ExportAttachedPopulation'),
					handler: function() {
						getWnd('swUploadFileWindowFor_ImportErrors_ResponseFromSMO_ChangesLocation').show({ARMType: 'superadmin', Form: 'ImportErrors_FLKforZL'});
					}.createDelegate(this)
				}, {
					nn: 'action_ImportResponseFrom_SMOforPL',
					text: 'Импорт ответа от СМО по ЗЛ',
					tooltip: 'Импорт ответа от СМО по ЗЛ',
					hidden: !getRegionNick().inlist(['buryatiya']) || !isUserGroup('ExportAttachedPopulation'),
					handler: function() {
						getWnd('swUploadFileWindowFor_ImportErrors_ResponseFromSMO_ChangesLocation').show({ARMType: 'superadmin', Form: 'ImportResponseFrom_SMOforPL'});
					}.createDelegate(this)
				}, {
					nn: 'action_ImportOfTerritorialAttachmentsDetachments',
					text: 'Импорт территориальных прикреплений/ откреплений',
					tooltip: 'Импорт территориальных прикреплений/ откреплений',
					hidden: !getRegionNick().inlist(['buryatiya']) || !isUserGroup('ExportAttachedPopulation'),
					handler: function() {
						getWnd('swUploadFileWindowFor_ImportErrors_ResponseFromSMO_ChangesLocation').show({ARMType: 'superadmin', Form: 'ImportOfTerritorialAttachmentsDetachments'});
					}.createDelegate(this)
				}, {
					name: langs('Отчеты в DBF формате'),
					text: langs('Отчеты в DBF формате'),
					hidden: getRegionNick() !== 'vologda',
					handler: function()
					{
						getWnd('swReportsInDBFFormat').show();
					}.createDelegate(this)
				}
				]
			},
			action_OrgView:
			{
				nn: 'action_OrgView',
				tooltip: langs('Все организации'),
				text: langs('Организации'),
				iconCls : 'org32',
				disabled: false, 
				handler: function() 
				{
					getWnd('swOrgViewForm').show();
				}
			},
			action_Documents: 
			{
				nn: 'action_Documents',
				tooltip: langs('Документы'),
				text: langs('Документы'),
				iconCls : 'document32',
				disabled: false,
				hidden: (getGlobalOptions().region.nick=='saratov'),
				menuAlign: 'tr',
				menu: new Ext.menu.Menu({
					items: [
						sw.Promed.Actions.GlossaryAction,
						sw.Promed.Actions.TemplatesAction,
						sw.Promed.Actions.MarkerAction,
						sw.Promed.Actions.ParameterValueAction
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
				handler: function() 
				{
					getWnd('swUsersTreeViewWindow').show();
				}
			},
            action_AutoPersonCard: {
                handler: function() {
                    getWnd('swPersonSearchPersonCardAutoWindow').show();
                }.createDelegate(this),
                iconCls: 'pcard-new32',
                nn: 'action_AutoPersonCard',
                text: langs('Групповое прикрепление'),
                tooltip: langs('Групповое прикрепление')
            },
			action_System: 
			{
				nn: 'action_System',
				tooltip: langs('Система'),
				text: langs('Система'),
				iconCls : 'settings32',
				disabled: false,
				menuAlign: 'tr',
				menu: new Ext.menu.Menu({
					items: [{
						text: langs('Параметры системы'),
						hidden: getGlobalOptions().IsSMPServer,
						handler: function () {
							getWnd('swGlobalOptionsWindow').show();
						}
					}, {
						text: langs('Нумераторы'),
						handler: function () {
							getWnd('swNumeratorListWindow').show();
						}
					}, {
						text: langs('Склад МО'),
						handler: function () {
							getWnd('swSkladMO').show();
						}
					}, {
						text: langs('Журнал событий системы'),
						hidden: (getRegionNick() === 'vologda'),
						handler: function () {
							getWnd('swPhpLogViewWindow').show();
						}
					}, {
						text: langs('Журнал авторизаций и событий безопасности'),
						hidden: !getRegionNick().inlist(['vologda']),
						handler: function () {
							getWnd('swPhpLogAndUserSessionViewWindow').show();
						}
					}, {
						text: langs('Управление версиями локальных справочников'),
						handler: function () {
							getWnd('swDBLocalVersionWindow').show();
						}
					}, {
						text: langs('Управление кэшируемыми объектами'),
						handler: function() {
							getWnd('swMongoCacheViewWindow').show();
						}
					}, {
						text: langs('Обновить список АРМов в БД'),
						handler: function () {
							this.getLoadMask(langs('Обновление списка АРМов в БД...')).show();
							Ext.Ajax.request({
								url: '/?c=User&m=updateARMList',
								callback: function (o, s, r) {
									this.getLoadMask().hide();
									//
								}.createDelegate(this)
							});
						}.createDelegate(this)
					}, {
						text: langs('Связи МО с организациями в ЛИС'),
						handler: function () {
							getWnd('swOrganizationListWindow').show();
						}
					}, {
						text: langs('Обновить данные из ЛИС'),
						handler: function () {
							this.getLoadMask(langs('Обновление данных из ЛИС...')).show();
							Ext.Ajax.request({
								url: '/?c=LisUpdater&m=getDirectories',
								callback: function (o, s, r) {
									this.getLoadMask().hide();
									//
								}.createDelegate(this)
							});
						}.createDelegate(this)
					}, {
						name: 'data_exchange_settings',
						text: langs('Настройка информационного обмена с АО'),
						iconCls: 'database-export32',
						hidden: getRegionNick() == 'kz',
						handler: function () {
							var fd = 'swExpQueryViewWindow';
							getWnd(fd).show();
						}
					}, {
						text: langs('Мониторинг системы'),
						handler: function () {
							getWnd('swSystemMonitorWindow').show();
						}
					}, {
						text: langs('Журнал авторизаций в системе'),
						hidden: (getRegionNick() === 'vologda'),
						handler: function () {
							getWnd('swUserSessionsViewForm').show();
						}
					}, {
						text: langs('Журнал ошибок'),
						handler: function () {
							getWnd('swSystemErrorsWindow').show();
						}
					}, {
						text: 'Журнал ошибок передачи данных в ИЭМК',
						hidden: (getRegionNick() != 'buryatiya'),
						handler: function () {
							getWnd('swMISErrorWindow').show();
						}
					}, {
						text: 'Работа с реестрами',
						handler: function () {
							getWnd('swRegistryHistoryViewWindow').show();
						}
					}, {
						text: 'ЕРМП. Настройка соответствия должностей и специальностей',
						hidden: (getRegionNick() == 'kazahstan'),
						handler: function() {
							getWnd('swPostSpecialityConformSettingsWindow').show();
						}
					}, {
						text: 'Удаленные данные',
						hidden: (getRegionNick() !== 'vologda'),
						handler: function() {
							var cur = sw.Promed.MedStaffFactByUser.current;
							getWnd('delDocsSearchWindow').show(
								{
									ArmType: 'superadmin',
									MedStaffFact_id: (cur ? cur.MedStaffFact_id : undefined),
								}
							);
						}
					}]
				})
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
			action_accessibility: {
				menuAlign: 'tr',
				text: langs('Льготники'),
				tooltip: langs('Льготники'),
				iconCls: 'lgot32',
				menu: new Ext.menu.Menu({
					items: [{
						text: langs('Регистр льготников: Список'),
						tooltip: langs('Просмотр льгот по категориям'),
						iconCls : 'lgot-tree16',
						handler: function() {
							getWnd('swLgotTreeViewWindow').show({ARMType: 'superadmin'});
						}
					}, {
						text: MM_DLO_LGOTSEARCH,
						tooltip: langs('Поиск льготников'),
						iconCls : 'lgot-search16',
						handler: function() {
							getWnd('swPrivilegeSearchWindow').show({ARMType: 'superadmin'});
						}
					},
						'-',
						{
							text: MM_DLO_UDOSTLIST,
							tooltip: langs('Просмотр удостоверений'),
							iconCls : 'udost-list16',
							handler: function() {
								getWnd('swUdostViewWindow').show({ARMType: 'superadmin'});
							}
						}]
				})
			},
			action_ReportEngine: 
			{
				nn: 'action_ReportEngine',
				tooltip: langs('Репозиторий отчетов'),
				text: langs('Репозиторий отчетов'),
				iconCls : 'report32',
				disabled: false, 
				handler: function() 
				{
					// Пример предварительной загрузки блока кода 
					if (sw.codeInfo.loadEngineReports)
					{
						getWnd('swReportEngineWindow').show();
					}
					else 
					{
						getWnd('reports').load(
						{
							callback: function(success) 
							{
								sw.codeInfo.loadEngineReports = success;
								// здесь можно проверять только успешную загрузку 
								getWnd('swReportEngineWindow').show();
							}
						});
					}
				}
			},
			action_AnalyzerModel:
			{
				nn: 'action_AnalyzerModel',
				tooltip: langs('Модели анализаторов'),
				text: langs('Модели анализаторов'),
				iconCls : 'analyzer32',
				disabled: false,
				hidden: (getGlobalOptions().region.nick=='saratov'),
				handler: function()
				{
					getWnd('swAnalyzerModelWindow').show();
				}
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
			action_StoragePlacement:
			{
				nn: 'action_StoragePlacement',
				tooltip: langs('Размещение на складах'),
				text: langs('Размещение на складах'),
				iconCls : 'storage-place32',
				handler: function()
				{
					getWnd('swStorageZoneViewWindow').show({fromARM:'superadmin'});
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
			action_EMD:
			{
				nn: 'action_EMD',
				tooltip: 'Региональный РЭМД',
				text: 'Региональный РЭМД',
				hidden: getRegionNick() == 'kz',
				iconCls : 'remd32',
				disabled: false,
				menuAlign: 'tr',
				menu: [{
					text: 'Подписание медицинской документации',
					handler: function() {
						getWnd('swEMDSearchUnsignedWindow').show();
					}
				}, {
					text: 'Региональный РЭМД',
					handler: function() {
						getWnd('swEMDSearchWindow').show({ArmType: 'superadmin'});
					}
				}, {
					text: 'Настройки правил подписания документов',
					hidden: getRegionNick() != 'msk',
					handler: function() {
						getWnd('swEMDDocumentSignRulesWindow').show({
							ArmType: 'superadmin'
						});
					}
				}]
			},
			action_EMD_EGISZ:
			{
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
			action_EmergencyTeamProposalLogic: {
				tooltip: 'Логика предложения бригады на вызов',
				iconCls : 'mp-queue32',
				handler: function(){
					getWnd('swSmpEmergencyTeamProposalLogicWindow').show({
						Lpu_id: form.LpuGrid.getGrid().getSelectionModel().getSelected().get('Lpu_id')
					});
				}
			},

			action_DecisionTreeEditWindow: {
				tooltip: 'Дерево принятия решений',
				iconCls : 'structure-vert32',
				handler: function(){
					swExt4.app.getController('smp.controllers.DecisionTree').showStucturesWindow();
				}
			},
			action_smpStacDiffDiagJournal: {
				iconCls: 'pers-cards32',
				tooltip: 'Журнал госпитализаций из СМП',
				handler: function(){
					getWnd('swSmpStacDiffDiagJournal').show({
						ARMType: 'superadmin',
						Lpu_id: form.LpuGrid.getGrid().getSelectionModel().getSelected().get('Lpu_id')
					});
				}
			},
			action_Register: {
				nn: 'action_Register',
				tooltip: langs('Регистры'),
				text: langs('Регистры'),
				iconCls: 'registry32',
				disabled: false,
				menuAlign: 'tr?',
				menu: new Ext.menu.Menu({
					items: [{
						tooltip: langs('Регистр часто обращающихся'),
						text: langs('Регистр часто обращающихся'),
						//iconCls: 'report16',
						disabled: false,
						//hidden: (getRegionNick() == 'perm'),
						handler: function () {
							if (getWnd('swOftenCallersRegisterWindow').isVisible()) {
								sw.swMsg.show({
									buttons: Ext.Msg.OK,
									fn: Ext.emptyFn,
									icon: Ext.Msg.WARNING,
									msg: langs('Окно уже открыто'),
									title: ERR_WND_TIT
								});
								return false;
							}
							getWnd('swOftenCallersRegisterWindow').show({
								Lpu_id: form.LpuGrid.getGrid().getSelectionModel().getSelected().get('Lpu_id')
							});
						}
					}, {
						tooltip: langs('Регистр случаев противоправных действий в отношении персонала СМП'),
						text: langs('Регистр случаев противоправных действий в отношении персонала СМП'),
						//iconCls: 'report32',
						disabled: false,
						handler: function () {
							if (getWnd('swSmpIllegalActWindow').isVisible()) {
								sw.swMsg.show({
									buttons: Ext.Msg.OK,
									fn: Ext.emptyFn,
									icon: Ext.Msg.WARNING,
									msg: langs('Окно уже открыто'),
									title: ERR_WND_TIT
								});
								return false;
							}
							getWnd('swSmpIllegalActWindow').show();
						}.createDelegate(this)
					}
					]
				})
			},
			action_PatientDiffJournal: {
				hidden: getRegionNick() == 'buryatiya',
				nn: 'action_PatientDiffJournal',
				tooltip: 'Журнал расхождения пациентов в учетных документах',
				text: 'Журнал расхождения пациентов в учетных документах',
				iconCls : 'report32',
				handler: function(){
					getWnd('swPatientDiffJournalWindow').show({
						Lpu_id: form.LpuGrid.getGrid().getSelectionModel().getSelected().get('Lpu_id')
					});
				}
			},
		};

		if ( isUserGroup('EGISSOAdmin') ) {
			configActions.action_EGISSO = {
                text: langs('ЕГИССО'),
                tooltip: langs('ЕГИССО'),
				hidden: getRegionNick() == 'kz',
                iconCls: 'egisso32',
                menu: new Ext.menu.Menu({
					id: 'menu_egisso_superadmin',
					items:[{
						text: langs('Сформировать данные'),
						tooltip: langs('Сформировать данные'),
						iconCls: '',
						handler: function() {
							getWnd('swEgissoDataImportWindow').show();
						}
					}, {
						text: langs('Открыть модуль'),
						tooltip: langs('Открыть модуль'),
						iconCls: '',
						handler: function() {
							var url = '/ext03_6/directions_spa_treatment.html?PHPSESSID=' + getCookie('PHPSESSID');
							window.open(url);
						}
					}, {
						text: langs('Журнал ручного экспорта МСЗ'),
						tooltip: langs('Журнал ручного экспорта МСЗ'),
						iconCls: '',
						handler: function() {
							getWnd('swEgissoReceptExportListWindow').show();
						}
					}]
				})
            }
		}
		else if ( isUserGroup('EGISSOUser') ) {
			configActions.action_EGISSO = {
				text: langs('ЕГИССО'),
				tooltip: langs('ЕГИССО'),
				hidden: getRegionNick() == 'kz',
				iconCls: 'egisso32',
				handler: function() {
					var url = '/ext03_6/directions_spa_treatment.html?PHPSESSID=' + getCookie('PHPSESSID');
					window.open(url);
				}
			}
		}

		// Копируем все действия для создания панели кнопок
		form.PanelActions = {};
		for(var key in configActions)
		{
			var iconCls = configActions[key].iconCls;
			var z = Ext.applyIf({cls: 'x-btn-large', iconCls: iconCls, text: ''}, configActions[key]);
			this.PanelActions[key] = new Ext.Action(z);
		}
		var actions_list = ['action_OrgView', 'action_Documents', 'action_Users', 'action_AutoPersonCard', 'action_Doubles', 'action_accessibility', 'action_Spr', 'action_DBStructure', 'action_System', 'action_ReportEngine', 'action_staff_actions', 'action_AnalyzerModel', 'action_SURDataView', 'action_StoragePlacement', 'action_ElectronicQueueSpr', 'action_EMD', 'action_EMD_EGISZ','action_WaitingListJournal'];
		if (getGlobalOptions().region.nick=='saratov')
			actions_list = ['action_OrgView', 'action_Documents', 'action_Users', 'action_Doubles', 'action_accessibility', 'action_Spr', 'action_DBStructure', 'action_System', 'action_ReportEngine', 'action_staff_actions'];

		var groups = getGlobalOptions().groups.split('|');
		if( groups && ( groups.includes('DispDirNMP') || groups.includes('DispCallNMP' ) || groups.includes('smpAdminRegion' )) ) {
			actions_list.push('action_EmergencyTeamProposalLogic');
			actions_list.push('action_DecisionTreeEditWindow');
		}

		if(isUserGroup('smpAdminRegion')){
			actions_list.push('action_smpStacDiffDiagJournal');
			actions_list.push('action_Register');
			actions_list.push('action_PatientDiffJournal');
		}
		if ( isUserGroup('EGISSOAdmin') || isUserGroup('EGISSOUser') ) {
			actions_list.push('action_EGISSO');
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
			id: form.id + '_hhd',
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
			bodyStyle: 'padding-left: 5px',
			width: 60,
			minSize: 60,
			maxSize: 120,
			id: 'awpwLeftPanel',
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
					el = form.findById(form.id + '_slid');
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
					iconCls:'uparrow',
					disabled: false,
					handler: function() 
					{
						var el = form.findById(form.id + '_hhd');
						var d = el.body.dom;
						d.scrollTop -=38;
					}
				}),
				{
					border: false,
					layout:'border',
					id: form.id + '_slid',
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
					var el = form.findById(form.id + '_hhd');
					var d = el.body.dom;
					d.scrollTop +=38;
					
					
				}
				})]
		};
		
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
					id: 'awpwOrg_Name'
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
					id: 'awpwOrg_Nick'
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
					id: 'awpwButtonSetFilter',
					handler: function ()
					{
						Ext.getCmp('swAdminWorkPlaceWindow').loadGridWithFilter();
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
					id: 'awpwButtonUnSetFilter',
					handler: function ()
					{
						Ext.getCmp('swAdminWorkPlaceWindow').loadGridWithFilter(true);
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
					
					Ext.getCmp('swAdminWorkPlaceWindow').loadGridWithFilter();
				},
				stopEvent: true
			}]
		});
		
		// Организации
		this.LpuGrid = new sw.Promed.ViewFrame(
		{
			id: 'awpwLpuGridPanel',
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
					var grid = Ext.getCmp('awpwLpuGridPanel');
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
				{name: 'Org_IsAccess', type:'checkbox', header: langs('Доступ в систему'), width: 60},
				{name: 'DLO', type:'checkbox', header: langs('ЛЛО'), width: 40},
				{name: 'OMS', type:'checkbox', header: langs('ОМС'), width: 40},
				{id: 'Lpu_Ouz', name: 'Lpu_Ouz', header: langs('Код ОУЗ'), width: 80},
				{name: 'Org_Name', id: 'autoexpand', header: langs('Полное наименование')},
				{name: 'Org_Nick', header: langs('Краткое наименование'), width: 240},
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
				{name:'action_edit', iconCls : 'x-btn-text', icon: 'img/icons/lpu16.png', text: langs('Паспорт МО'), handler: function()
					{
						this.Lpu_id = Ext.getCmp('awpwLpuGridPanel').ViewGridPanel.getSelectionModel().getSelected().get('Lpu_id');
						getWnd('swLpuPassportEditWindow').show({
							action: 'edit',
							Lpu_id: this.Lpu_id
						});
					}
				},		
				{name:'action_view', iconCls : 'x-btn-text', icon: 'img/icons/lpu-struc16.png', text: langs('Структура МО'), handler: function()
					{
						this.Lpu_id = Ext.getCmp('awpwLpuGridPanel').ViewGridPanel.getSelectionModel().getSelected().get('Lpu_id');
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
				var win = Ext.getCmp('swAdminWorkPlaceWindow');
				var form = Ext.getCmp('awpwLpuGridPanel');
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

		sw.Promed.swAdminWorkPlaceWindow.superclass.initComponent.apply(this, arguments);
	}	
	
});

