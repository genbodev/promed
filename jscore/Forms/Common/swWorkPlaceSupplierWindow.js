/**
* АРМ поставщика
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @autor		
* @copyright    Copyright (c) 2015 Swan Ltd.
*/
sw.Promed.swWorkPlaceSupplierWindow = Ext.extend(sw.Promed.swWorkPlaceWindow, {
	id: 'swWorkPlaceSupplierWindow',
	show: function() {
		var wnd = this;

		sw.Promed.swWorkPlaceSupplierWindow.superclass.show.apply(this, arguments);
		this.userMedStaffFact = arguments[0];

		this.GridPanel.addActions({
			name: 'action_viewuc',
			text: 'Просмотр',
			iconCls: 'view16',
			handler: function() {
				var record = wnd.GridPanel.getGrid().getSelectionModel().getSelected();
				if (record && record.get('WhsDocumentSupply_id')) {
					getWnd('swWhsDocumentSupplyEditWindow').show({
						action: 'view',
						WhsDocumentSupply_id: record.get('WhsDocumentSupply_id'),
						WhsDocumentType_id: record.get('WhsDocumentType_id')
					});
				}
			}
		}, 7);

		this.GridPanel.addActions({
			name: 'action_viewap',
			text: 'Аптеки',
			iconCls: 'view16',
			handler: function() {
				var record = wnd.GridPanel.getGrid().getSelectionModel().getSelected();
				if (record && record.get('WhsDocumentSupply_id')) {
					getWnd('swWhsDocumentTitleSelectWindow').show({
						WhsDocumentSupply_id: record.get('WhsDocumentSupply_id'),
						callback: function(data) {
							if (data && data.WhsDocumentTitle_id) {
								getWnd('swWhsDocumentTitleEditWindow').show({
									action: 'view',
									WhsDocumentTitle_id: data.WhsDocumentTitle_id
								});
							}
						}
					});
				}
			}
		}, 8);
	},
	doSearch: function(mode) {
		var params = this.FilterPanel.getForm().getValues();
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
		params.limit = 100;
		params.start = 0;
		params.mode = 'supplier';
		this.GridPanel.removeAll({addEmptyRecord: false});
		this.GridPanel.loadData({globalFilters: params});
	},
	initComponent: function() {
		var form = this;

		this.buttonPanelActions = {
			action_ReceptUploadLogWindow: {
				text: 'Журнал загрузок',
				tooltip: 'Журнал загрузок',
				iconCls : 'receipt-incorrect32',
				handler: function()
				{
					getWnd('swReceptUploadLogWindow').show({
						begDate: Ext.util.Format.date(form.dateMenu.getValue1(), 'd.m.Y'),
						endDate: Ext.util.Format.date(form.dateMenu.getValue2(), 'd.m.Y')
					});
				}
			},
			action_Recept: {
				nn: 'action_Register',
				tooltip: 'Рецепты',
				text: 'Рецепты',
				iconCls : 'recept-search32',
				disabled: false,
				menuAlign: 'tr?',
				menu: new Ext.menu.Menu({
					items: [{
						text: 'Поиск рецептов',
						tooltip: 'Поиск рецептов',
						iconCls: 'receipt-search16',
						handler: function() {
							getWnd('swEvnReceptSearchWindow').show();
						}
					}, {
						text: 'Журнал отсрочки',
						tooltip: 'Журнал отсрочки',
						iconCls : 'receipt-incorrect16',
						handler: function()	{
							getWnd('swReceptInCorrectSearchWindow').show();
						}
					}]
				})
			},
			action_RegistryLLO: {
				handler: function() {
					getWnd('swRegistryLLOViewWindow').show({
						ARMType: sw.Promed.MedStaffFactByUser.current.MedServiceType_SysNick
					});
				},
                hidden: !(
                    getGlobalOptions().region.nick.inlist([/*'ufa',*/ 'khak', 'saratov', 'krym', 'ekb']) &&
                    (Ext.isEmpty(getGlobalOptions().lpu_id) || getGlobalOptions().lpu_id == 0) &&
                    getGlobalOptions().dlo_logistics_system == 'level2' &&
                    getGlobalOptions().ContragentType_SysNick == 'org'
                ),
				iconCls: 'service-reestrs16',
				nn: 'action_RegistryLLO',
				text: lang['oplata_reestrov_receptov'],
				tooltip: lang['oplata_reestrov_receptov']
			},
			action_DrugOstatRegistryList: {
				nn: 'action_DrugOstatRegistryList',
				tooltip: 'Просмотр регистра остатков',
				text: 'Просмотр регистра остатков',
				iconCls : 'pers-cards32',
				menuAlign: 'tr?',
				menu: new Ext.menu.Menu({
					items: [{
						text: 'Просмотр остатков организации пользователя',
						tooltip: 'Просмотр остатков организации пользователя',
						iconCls: 'pill16',
						handler: function() {
							getWnd('swDrugOstatRegistryListWindow').show({
                                mode: 'suppliers',
                                userMedStaffFact: getWnd('swWorkPlaceSupplierWindow').userMedStaffFact
                            });
                        }.createDelegate(this)
					}, {
						text: 'Просмотр остатков по складам Аптек и РАС',
						tooltip: 'Просмотр остатков по складам Аптек и РАС',
						iconCls: 'pill16',
						handler: function() {
							getWnd('swDrugOstatRegistryListWindow').show({mode: 'farmacy_and_store'});
						}
					}]
				})
			},
			action_Contragents: {
				nn: 'action_Contragents',
				tooltip: 'Справочник "Контрагенты"',
				text: 'Контрагенты',
				iconCls : 'org32',
				disabled: false,
				handler: function(){
					getWnd('swContragentViewWindow').show({
						ARMType: sw.Promed.MedStaffFactByUser.current.MedServiceType_SysNick
					});
				}
			},
            action_WhsDocumentSupply: {
                nn: 'action_WhsDocumentSupply',
                tooltip: lang['dopolnitelnyie_soglasheniya'],
                text: lang['dopolnitelnyie_soglasheniya'],
                iconCls : 'card-state32',
                handler: function(){
                    getWnd('swWhsDocumentSupplyAdditionalViewWindow').show({
                        ARMType: sw.Promed.MedStaffFactByUser.current.MedServiceType_SysNick
                    });
                }
            },
			action_References: {
				nn: 'action_References',
				tooltip: 'Справочники',
				text: 'Справочники',
				iconCls : 'book32',
				disabled: false,
				menuAlign: 'tr?',
				menu: new Ext.menu.Menu({
					items: [{
						tooltip: 'МКБ-10',
						text: 'Справочник МКБ-10',
						iconCls: 'spr-mkb16',
						handler: function() {
							if ( !getWnd('swMkb10SearchWindow').isVisible() )
								getWnd('swMkb10SearchWindow').show();
						}
					}, {
						name: 'action_DrugNomenSpr',
						text: 'Номенклатурный справочник',
						iconCls : '',
						handler: function()
						{
							getWnd('swDrugNomenSprWindow').show();
						}
					},
					sw.Promed.Actions.swDrugDocumentSprAction,
					{
						name: 'action_PriceJNVLP',
						text: 'Цены на ЖНВЛП',
						iconCls : 'dlo16',
						handler: function() {
							getWnd('swJNVLPPriceViewWindow').show();
						}
					}, {
						name: 'action_DrugMarkup',
						text: 'Предельные надбавки на ЖНВЛП',
						iconCls : 'lpu-finans16',
						handler: function() {
							getWnd('swDrugMarkupViewWindow').show();
						}
					}, {
						text: 'Справочник фальсификатов и забракованных серий ЛС',
						tooltip: 'Справочник фальсификатов и забракованных серий ЛС',
						handler: function()
						{
							getWnd('swPrepBlockViewWindow').show();
						}
					}, {
						tooltip: lang['spravochnik_medikamentov'],
						text: lang['spravochnik_medikamentov'],
						iconCls: 'rls16',
						handler: function() {
							if ( !getWnd('swRlsViewForm').isVisible() )
								getWnd('swRlsViewForm').show();
						}
					}]
				})
			},
			action_JourNotice: {
				handler: function() {
					getWnd('swMessagesViewWindow').show();
				}.createDelegate(this),
				iconCls: 'notice32',
				nn: 'action_JourNotice',
				text: 'Журнал уведомлений',
				tooltip: 'Журнал уведомлений'
			}
		};

		this.onKeyDown = function (inp, e) {
			if (e.getKey() == Ext.EventObject.ENTER) {
				e.stopEvent();
				this.doSearch();
			}
		}.createDelegate(this);
		this.FilterPanel = new sw.Promed.BaseWorkPlaceFilterPanel({
			owner: form,
			hidden: true,
			filter: {
				title: 'Фильтр',
				layout: 'form',
				items: []
			}
		});
		this.GridPanel = new sw.Promed.ViewFrame({
			region: 'center',
			autoExpandColumn: 'autoexpand',
			actions: [
				{name:'action_add', disabled: true, hidden: true},
				{name:'action_edit', disabled: true, hidden: true},
				{name:'action_view', disabled: true, hidden: true},
				{name:'action_delete', disabled: true, hidden: true},
				{name: 'action_refresh'},
				{name: 'action_print'}
			],
			autoLoadData: false,
			paging: true,
			pageSize: 100,
			stringfields: [
				{ name: 'WhsDocumentSupply_id', type: 'int', header: 'ID', key: true },
				{ name: 'WhsDocumentStatusType_Name', type: 'string', header: 'Статус', width:75 },
				{ name: 'WhsDocumentStatusType_id', type: 'int', header: '', hidden: true },
				{ name: 'WhsDocumentUc_Num', type: 'string', header: '№ контракта', width:220 },
				{ name: 'ActualDateRange', type: 'string', header: 'Период действия контракта', width:150 },
				{ name: 'WhsDocumentUc_Sum', type: 'string', header: 'Сумма', width:95 },
				{ name: 'GraphLink', header: 'График поставки', width:125, renderer: function(v, p, record)	{ if(!v) { return ""; }	return '<a href="javascript:getWnd(\'swWhsDocumentDeliveryGraphViewWindow\').show({WhsDocumentSupply_id: '+v+'});" style="cursor: pointer; color: #0000EE;">график поставки</a>'; }	},
				{ name: 'DrugFinance_Name', type: 'string', header: 'Финансирование', width:125 },
				{ name: 'WhsDocumentCostItemType_Name', type: 'string', header: 'Статья расходов', width:125 },
				{ name: 'BudgetFormType_Name', type: 'string', header: 'Целевая статья', width:125 },
				{ name: 'ProtInf', type: 'string', header: 'Протокол', width:175 },
				{ name: 'WhsDocumentUc_pName', type: 'string', header: 'Лот', width:125 },
				{ name: 'Org_sid_Nick', type: 'string', header: 'Поставщик', width:100 },
				{ name: 'WhsDocumentType_id', hidden: true, isparams: true },
				{ name: 'WhsDocumentType_Name', hidden: true, isparams: true }
			],
			object: 'WhsDocumentSupply',
			editformclassname: 'swWhsDocumentSupplyEditWindow',
			dataUrl: '/?c=WhsDocumentSupply&m=loadList',
			root: 'data',
			totalProperty: 'totalCount',
			title: 'Журнал рабочего места'
		});

		sw.Promed.swWorkPlaceSupplierWindow.superclass.initComponent.apply(this, arguments);
	}
});