/**
* swDrugOstatRegistryListWindow - список строк регистра остатков
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      RegistryRecept
* @access       public
* @copyright    Copyright (c) 2012 Swan Ltd.
* @author       Dmitry Vlasenko
* @version      19.01.2012
* @comment      
*/
/*NO PARSE JSON*/
sw.Promed.swDrugOstatRegistryListWindow = Ext.extend(sw.Promed.BaseForm,
{
	maximizable: true,
	maximized: true,
	height: 500,
	width: 800,
	id: 'DrugOstatRegistryListWindow',
	title: WND_DRUGOSTATREGISTRY_LIST, 
	layout: 'border',
	resizable: true,
	doSearch: function() {
		var wnd = this;
		if (wnd.doings.hasDoings()) {
			wnd.doings.doLater('doSearch', function(){wnd.doSearch()});
			return;
		}
		var base_form = this.FilterPanel.getForm();
		
		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.FilterPanel.getFirstInvalidEl().focus(false);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		
		var filters = base_form.getValues();
		filters.AllowReservation = base_form.findField('AllowReservation').getValue()?1:0;
		filters.isPKU = base_form.findField('isPKU').getValue()?1:0;
		filters.PrepSeries_isDefect = base_form.findField('PrepSeries_isDefect').getValue()?1:0;
		filters.start = 0;
		filters.limit = 100;

		if (getRegionNick() != 'kz') {
			if (this.mode == 'mo' || base_form.findField('OrgType_Filter').getValue() == 'mo' || (!Ext.isEmpty(base_form.findField('Org_id').getValue()) && base_form.findField('Org_id').getFieldValue('OrgType_SysNick') == 'lpu')) {
				// 	Колонка видима, если:
				// форма открыта в режиме «Просмотр остатков организации пользователя» и организация пользователя МО.
				// Или в фильтрах формы просмотра остатков указаны значения хотя бы для одного из полей: «Тип организации» - МО или «Организация» -  указана организация с типом МО.
				this.RegistryGrid.setActionHidden('GoodsUnit_Nick', false);
				this.RegistryGrid.setActionHidden('DrugOstatRegistry_KolvoLek', false);
				this.RegistryGrid.setActionHidden('DrugOstatRegistry_PriceLek', false);
			} else {
				this.RegistryGrid.setActionHidden('GoodsUnit_Nick', true);
				this.RegistryGrid.setActionHidden('DrugOstatRegistry_KolvoLek', true);
				this.RegistryGrid.setActionHidden('DrugOstatRegistry_PriceLek', true);
			}
		}

        if (!Ext.isEmpty(wnd.DefaultFilters.Storage_id_state)) {
            filters.Storage_id_state = wnd.DefaultFilters.Storage_id_state;
        } else {
        	filters.Storage_id_state = null;
        }

		if (getGlobalOptions().region.nick == 'ufa' && getGlobalOptions().orgtype == 'farm')
		    filters.Org_id = getGlobalOptions().org_id;

		base_form.items.each(function(field) {
			if (field.disabled) {
				filters[field.getName()] = field.value;
			}
		});
		
		/*if (Ext.isEmpty(base_form.findField('Org_id').getValue()) && getGlobalOptions().org_id) {
			filters.Org_id = getGlobalOptions().org_id;
		} else {
			filters.Org_id = base_form.findField('Org_id').getValue();
		}*/
		console.log('filters ='); console.log(filters);
		this.RegistryGrid.loadData({ globalFilters: filters });
	},
	doReset: function() {
		this.FilterPanel.getForm().reset();
		this.setDefaultFilters();

		/*var base_form = this.FilterPanel.getForm();
		
		if ( getGlobalOptions().org_id > 0 ) {
			base_form.findField('Org_id').setValue(getGlobalOptions().org_id);
			
			base_form.findField('Org_id').getStore().load({
				params: {
					Object:'Org',
					Org_id: getGlobalOptions().org_id,
					Org_Name:''
				},
				callback: function() {
					base_form.findField('Org_id').setValue(getGlobalOptions().org_id);
					base_form.findField('Org_id').fireEvent('change', base_form.findField('Org_id'));
				}
			});
		}*/

        this.RegistryGrid.removeAll();
        this.RegistryGrid.setActionDisabled('action_refresh', true);

        if (!this.disabledSearchOnReset) {
            this.doSearch();
        }
	},
	exportToCSV: function() {
		var wnd = this;
		var form = wnd.FilterPanel.getForm();
		var params = new Object();

		params = form.getValues();

		wnd.getLoadMask(langs('Формирование файла...')).show();
		Ext.Ajax.request({
			scope: this,
			params: params,
			url: '/?c=RegistryRecept&m=exportDrugOstatRegistryList',
			callback: function(o, s, r) {
				wnd.getLoadMask().hide();
				if(s) {
					var obj = Ext.util.JSON.decode(r.responseText);
					if( obj.success ) {
						window.open(obj.url);
					}
				}
			}
		});
	},
	printCardRecord: function() {
		var record = this.RegistryGrid.getGrid().getSelectionModel().getSelected();
		var curr_date = new Date();

		if (record.get('Org_id') > 0 && record.get('Drug_id') > 0) {
			var org_id = record.get('Org_id');
			var storage_id = record.get('Storage_id');
			var finance_id = record.get('DrugFinance_id');
			var cost_id = record.get('WhsDocumentCostItemType_id');
			var drug_id = record.get('Drug_id');
			var shipment_id = record.get('DrugShipment_id');
			var beg_date = record.get('DrugShipment_setDT');
			var end_date = curr_date.format('d.m.Y');
			var contragent_id = record.get('Contragent_id');
			var params = '&paramOrgn='+org_id+'&paramStorage='+storage_id+'&paramDrugFinance='+finance_id+'&paramWhsDocumentCostItemType='+cost_id+'&paramDrug='+drug_id+'&paramDrugShipment='+shipment_id+'&paramBegDate='+beg_date+'&paramEndDate='+end_date;
			if(getRegionNick() == 'khak') params = '&paramContragentByUser='+contragent_id+'&paramStorageByContragent='+storage_id+'&paramDrugShipment='+shipment_id+'&paramEndDate='+end_date;

			printBirt({
				'Report_FileName': 'Card.rptdesign',
				'Report_Params': params,
				'Report_Format': 'xls'
			});
		}
	},
	setMode: function() {
		var wnd = this;
        var form = wnd.FilterPanel.getForm();
        var wp_nick = !Ext.isEmpty(wnd.userMedStaffFact.MedServiceType_SysNick) ? wnd.userMedStaffFact.MedServiceType_SysNick : wnd.userMedStaffFact.ARMType;

		wnd.DefaultFilters = {};
		wnd.DefaultFilters.Storage_id_state = null;

        form.findField('KLAreaStat_id').showContainer();
		form.findField('AllowReservation').hideContainer();
		form.findField('SubAccountType_id').hideContainer();
		form.findField('LpuBuilding_id').hideContainer();
		form.findField('LpuSection_id').hideContainer();
		form.findField('OrgType_Filter').enable();
		form.findField('Org_id').enable();

        form.findField('Storage_id').FirstValueSelect = false;

		wnd.FilterTabs.setHeight(175);

		switch(wnd.mode) {
			case 'simple':
				wnd.setTitle(WND_DRUGOSTATREGISTRY_SUPPLIERS_LIST);
				form.findField('AllowReservation').showContainer();
				form.findField('SubAccountType_id').showContainer();
                wnd.DefaultFilters.SubAccountType_id = 1;
				wnd.FilterTabs.setHeight(220);
				break;
			case 'suppliers':
				wnd.setTitle(WND_DRUGOSTATREGISTRY_SUPPLIERS_LIST);
				form.findField('AllowReservation').showContainer();
				form.findField('SubAccountType_id').showContainer();
                wnd.DefaultFilters.Org_id = getGlobalOptions().org_id;
                wnd.DefaultFilters.SubAccountType_id = 1;
				wnd.FilterTabs.setHeight(220);
				break;
			case 'farmacy_and_store':
				wnd.setTitle(WND_DRUGOSTATREGISTRY_SUPPLIERS_LIST);
				if (getGlobalOptions().region.nick != 'ufa' )
					form.findField('AllowReservation').showContainer();
				form.findField('SubAccountType_id').showContainer();
                wnd.DefaultFilters.SubAccountType_id = 1;
				wnd.DefaultFilters.OrgType_Filter = 'farmacy_and_store';
				wnd.DefaultFilters.Org_id = null;
				wnd.FilterTabs.setHeight(200);
				break;
            case 'mo':
				wnd.setTitle(WND_DRUGOSTATREGISTRY_MO_LIST);

                form.findField('OrgType_Filter').disable();
                form.findField('Org_id').disable();
                form.findField('LpuBuilding_id').enable();
                form.findField('LpuSection_id').enable();

                form.findField('KLAreaStat_id').hideContainer();
                form.findField('LpuBuilding_id').showContainer();
                form.findField('LpuSection_id').showContainer();

                form.findField('Storage_id').FirstValueSelect = true;

                var org_id = !Ext.isEmpty(wnd.userMedStaffFact.Org_id) ? wnd.userMedStaffFact.Org_id : null;
                wnd.DefaultFilters.Org_id = org_id;
                wnd.DefaultFilters.OrgType_Filter = 'mo';

                if (!Ext.isEmpty(wnd.userMedStaffFact.LpuBuilding_id)) {
                    form.findField('LpuBuilding_id').disable();
                    wnd.DefaultFilters.LpuBuilding_id = wnd.userMedStaffFact.LpuBuilding_id;
                }
                if (!Ext.isEmpty(wnd.userMedStaffFact.LpuSection_id)) {
                    form.findField('LpuSection_id').disable();
                    wnd.DefaultFilters.LpuSection_id = wnd.userMedStaffFact.LpuSection_id;
                }
                if (!Ext.isEmpty(wnd.userMedStaffFact.MedService_id)) {
                    wnd.DefaultFilters.StorageMedService_id = wnd.userMedStaffFact.MedService_id;
                }

                if (wp_nick == 'merch') { //АРМ Товароведа
                    wnd.DefaultFilters.Storage_id_state = 'not_empty';
                }
                if (['polka', 'polkallo', 'leadermo'].indexOf(wp_nick) > -1) { //АРМ врача поликлиники; АРМ врача ЛЛО поликлиники; АРМ Руководителя МО;
                    wnd.DefaultFilters.Storage_id_state = 'empty';
                    form.findField('Storage_id').FirstValueSelect = false;
                }
				break;
			default:
				wnd.setTitle(WND_DRUGOSTATREGISTRY_LIST);
                wnd.DefaultFilters.SubAccountType_id = null;
                wnd.DefaultFilters.OrgType_Filter = 'supplier';
				break;
		}
		wnd.doLayout();
	},
	setDefaultFilters: function() {

		var wnd = this;
		var base_form = this.FilterPanel.getForm();

		if (wnd.DefaultFilters.OrgType_Filter) {
			base_form.findField('OrgType_Filter').setValue(wnd.DefaultFilters.OrgType_Filter);
		}

		if (wnd.DefaultFilters.Org_id && wnd.DefaultFilters.Org_id > 0) {
			base_form.findField('Org_id').setValue(wnd.DefaultFilters.Org_id);

			base_form.findField('Org_id').getStore().load({
				params: {
					Object:'Org',
					Org_id: wnd.DefaultFilters.Org_id,
					Org_Name:''
				},
				callback: function() {
					base_form.findField('Org_id').setValue(wnd.DefaultFilters.Org_id);
					base_form.findField('Org_id').fireEvent('change', base_form.findField('Org_id'));
				}
			});

			this.FilterPanel.getForm().findField('WhsDocumentUc_Num').getStore().load({
				params: {
					Org_id: getGlobalOptions().org_id
				}
			});
		} else {
			base_form.findField('Org_id').getStore().removeAll();
		}

		if (wnd.DefaultFilters.LpuBuilding_id && wnd.DefaultFilters.LpuBuilding_id > 0) {
			base_form.findField('LpuBuilding_id').setValue(wnd.DefaultFilters.LpuBuilding_id);
			base_form.findField('LpuBuilding_id').fireEvent('change', base_form.findField('LpuBuilding_id'), base_form.findField('LpuBuilding_id').getValue());
		}

		if (wnd.DefaultFilters.LpuSection_id && wnd.DefaultFilters.LpuSection_id > 0) {
			base_form.findField('LpuSection_id').setValue(wnd.DefaultFilters.LpuSection_id);
			base_form.findField('LpuSection_id').fireEvent('change', base_form.findField('LpuSection_id'), base_form.findField('LpuSection_id').getValue());
		}

		if (wnd.DefaultFilters.StorageMedService_id && wnd.DefaultFilters.StorageMedService_id) {
			wnd.doings.start('loadStorage');
            base_form.findField('Storage_id').getStore().baseParams.MedService_id = wnd.DefaultFilters.StorageMedService_id;
			base_form.findField('Storage_id').getStore().load({
				params: {MedService_id: wnd.DefaultFilters.StorageMedService_id},
				callback: function() {
					/*if (base_form.findField('Storage_id').getStore().getCount() > 0) {
						var record = base_form.findField('Storage_id').getStore().getAt(0);
						base_form.findField('Storage_id').setValue(record.get('Storage_id'));
					}*/
					wnd.doings.finish('loadStorage');
				}
			});
		} else {
            base_form.findField('Storage_id').getStore().baseParams.MedService_id = null;
        }

        if (wnd.DefaultFilters.SubAccountType_id) {
            base_form.findField('SubAccountType_id').setValue(wnd.DefaultFilters.SubAccountType_id);
        }
	},
	initComponent: function() {
		var form = this;
		
		this.RegistryGrid = new sw.Promed.ViewFrame({
			id: form.id+'RegistryGrid',
			title:'',
			object: 'Registry',
			dataUrl: '/?c=RegistryRecept&m=loadDrugOstatRegistryList',
			autoLoadData: false,
			// selectionModel: 'multiselect',
			region: 'center',
			toolbar: true,
			paging: true,
			root: 'data',
			totalProperty: 'totalCount',
			stringfields:
			[
				{name: 'DrugOstatRegistry_id', type: 'int', header: 'ID', key: true, hidden: true},
				{name: 'Org_id', hidden: true},
				{name: 'Org_Area', header: langs('Территория'), width: 120},
				{name: 'Org_Name', header: langs('Организация'), width: 180},
				{name: 'Storage_id', hidden: true},
				{name: 'Storage_Name', header: langs('Склад'), width: 120},
				{name: 'SubAccountType_Name', header: langs('Тип субсчета'), width: 90},
				{name: 'ActMatters_RusName', header: langs('МНН'), width: 80 },
				{name: 'DrugNomen_Code', header: langs('Код ЛП'), width: 80},
				{name: 'Reg_Num', header: '№ РУ', width: 80},
				{name: 'Drug_id', hidden: true},
				{name: 'Prep_Name', header: langs('Торговое наим.'), width: 80, id: 'autoexpand' },
				{name: 'DrugForm_Name', header: langs('Форма выпуска'), width: 95 },
				{name: 'Drug_Dose', header: langs('Дозировка'), width: 80 },
				{name: 'Drug_Fas', header: langs('Фасовка'), width: 80 },
				/*{name: 'Okei_Name', renderer: function(v, p, r) {
					if (getRegionNick() == 'kz' && r.get('IsGoodsUnit') == 0) {
						v = '<span ext:qtip="Для медикамента не заданы единицы измерения, отличные от упаковки, выведено наименование лекарственной формы" style="color:#FF0000;">' + v + '</span>';
					}
					return v;
				}, header: 'Ед. изм.', width: 90},*/
                {name: 'IsGoodsUnit', type: 'int', hidden: true},
				{name: 'GoodsUnit_Nick', header: 'Ед. учета', width: 90},
				{name: 'DrugOstatRegistry_Kolvo', renderer: function(v, p, r) {
					if (!Ext.isEmpty(v)) {
						v = '<span style="font-weight:bold;">' + v + '</span>';
					}
					return v;
				}, header: 'Кол-во', width: 120},
				{name: 'Okei_NameLek', renderer: function(v, p, r) {
					if (getRegionNick() != 'kz' && r.get('IsGoodsUnit') == 0) {
						v = '<span ext:qtip="Для медикамента не заданы единицы измерения, отличные от упаковки, выведено наименование лекарственной формы" style="color:#FF0000;">' + v + '</span>';
					}
					return v;
				}, header: 'Ед. лек. формы', hidden: getRegionNick() == 'kz', width: 120},
				{name: 'DrugOstatRegistry_KolvoLek', header: 'Кол-во (лек. форм)', hidden: getRegionNick() == 'kz', width: 120},
				{name: 'DrugOstatRegistry_Price', header: langs('Цена'), width: 80},
				{name: 'DrugOstatRegistry_PriceLek', header: 'Цена за ед. лек. формы', hidden: getRegionNick() == 'kz', width: 80},
				{name: 'DrugOstatRegistry_Sum', header: langs('Сумма'), width: 80},
				{name: 'PrepSeries_Ser', header: langs('Серия выпуска'), width: 100},
				{name: 'PrepSeries_GodnDate', header: langs('Срок годности'), type: 'date', width: 100},
				{name: 'Firm_Name', header: langs('Производитель'), width: 80 },
				{name: 'WhsDocumentUc_Num', header: langs('№ ГК'), width: 90},
				{name: 'WhsDocumentSupply_Year', header: langs('Год'), width: 90},
				{name: 'WhsDocumentUc_Name', header: langs('Наименование'), width: 120, hidden: true, hideable: true},
				{name: 'WhsDocumentUc_Date', type: 'date', header: langs('Дата'), width: 120, hidden: true, hideable: true},
				{name: 'DrugFinance_id', hidden: true},
				{name: 'DrugFinance_Name', header: langs('Источник финансирования'), width: 120},
				{name: 'WhsDocumentCostItemType_id', hidden: true},
				{name: 'WhsDocumentCostItemType_Name', header: langs('Статья расхода'), width: 120},
				{name: 'DrugShipment_id', hidden: true},
				{name: 'DrugShipment_setDT', hidden: true},
				{name: 'DrugShipment_Name', header: langs('Партия'), width: 120}, 
				{name: 'DrugOstatRegistry_insDT', header: langs('Дата поступления'), width: 120, hidden: getGlobalOptions().region.nick == 'ufa'},
				{name: 'GodnDate_Ctrl', header: 'GodnDate_Ctrl', hidden: true},
				{name: 'Contragent_id', hidden: true}
			],
			onDblClick: function() {
				// form.showRegistryDataRecept();
			},
            onLoadData: function() {
                this.setActionDisabled('action_refresh', false);
            },
			actions: [
				{name:'action_add', disabled: true, hidden: true},
				{name:'action_edit', disabled: true, hidden: true},
				{name:'action_print', disabled: false},
				{name:'action_view', disabled: true, hidden: true},
				{name:'action_delete', disabled: true}
			],
            onRowSelect: function (sm, rowIdx, record) {
                if (record.get('DrugOstatRegistry_id') > 0) {
                    form.DocumentUcGrid.loadData({
                        globalFilters: {
                            DrugOstatRegistry_id: record.get('DrugOstatRegistry_id')
                        }
                    })
                } else {
                    form.DocumentUcGrid.removeAll({
                        addEmptyRecord: false
                    })
                }
            }
		});

		this.DocumentUcGrid = new sw.Promed.ViewFrame({
			id: form.id+'DocumentUcGrid',
			title: null,
			object: 'DocumentUc',
			dataUrl: '/?c=RegistryRecept&m=loadDocumentUcList',
			autoLoadData: false,
			toolbar: false,
			paging: false,
            actions: [
                {name:'action_add', disabled: true, hidden: true},
                {name:'action_edit', disabled: true, hidden: true},
                {name:'action_print', disabled: false},
                {name:'action_view', disabled: true, hidden: true},
                {name:'action_delete', disabled: true}
            ],
			stringfields: [
				{name: 'DocumentUc_id', type: 'int', header: 'ID', key: true, hidden: true},
                {name: 'DrugDocumentStatus_Name', header: 'Статус', width: 100},
                {name: 'DocumentUc_Num', header: '№', width: 80, id: 'autoexpand'},
                {name: 'DocumentUc_setDate', header: 'Дата', width: 80},
                {name: 'DrugDocumentType_Name', header: 'Тип документа', width: 200},
                {name: 'S_Name', header: 'Поставщик', width: 250},
                {name: 'T_Name', header: 'Получатель', width: 250}
            ],
            onLoadData: function() {
                this.setActionDisabled('action_refresh', false);
            }
		});
        
        this.RegistryGrid.getGrid().view = new Ext.grid.GridView({
			getRowClass : function (row, index) {
                var cls = '';
                if (getGlobalOptions().region.nick == 'ufa') {
                    var $dd = row.get('GodnDate_Ctrl');
                    if ($dd == 1) {
                        cls = cls+'x-grid-rowred';
                    }
                }
                return cls;
			}
		});

		//Вкладка "Организация"
		this.FilterOrgPanel = new sw.Promed.Panel({
			layout: 'form',
			autoScroll: true,
			bodyBorder: false,
			labelAlign: 'right',
			labelWidth: 170,
			border: false,
			frame: true,
			items: [ /*{
				xtype: 'swcommonsprcombo',
				fieldLabel: langs('Тип организации'),
				tabIndex: TABINDEX_DORLW + 0,
				hiddenName: 'OrgType_id',
				allowBlank: true,
				comboSubject: 'OrgType',
				typeCode: 'int',
				tabIndex: 603,
				width: 650
			},*/
				{
					xtype:'combo',
					store: new Ext.data.SimpleStore({
						id: 0,
						fields: [
							'code',
							'name'
						],
						data: [
							['touz', langs('ТОУЗ')],
							['mo', langs('МО')],
							['supplier', langs('Поставщик')],
							['farmacy_and_store', langs('Аптеки и Региональный склад ДЛО')]
						]
					}),
					tpl: new Ext.XTemplate(
						'<tpl for="."><div class="x-combo-list-item">',
						'{name}&nbsp;',
						'</div></tpl>'
					),
					displayField: 'name',
					valueField: 'code',
					editable: false,
					allowBlank: true,
					mode: 'local',
					forceSelection: true,
					triggerAction: 'all',
					fieldLabel: langs('Тип организации'),
					hiddenName: 'OrgType_Filter',
					width:  650,
					selectOnFocus: true
				}, {
				xtype: 'sworgcombo',
				fieldLabel : langs('Организация'),
				tabIndex: TABINDEX_DORLW + 0,
				hiddenName: 'Org_id',
				width: 650,
				disabled: false,
				allowBlank: true,
				editable: false,
				needOrgType: true,
				listeners: {
					'change': function() {
						var base_form = form.FilterPanel.getForm();
						var org_id = base_form.findField('Org_id').getValue();
						if (org_id) {
							base_form.findField('WhsDocumentUc_Num').getStore().load({
								params: {
									Org_id: org_id
								}
							});
						}
                        this.loadStorageCombo();
						//form.setBaseParamsForChildrens(this, org_id, ['Storage_id'])
					}
				},
				onTrigger1Click: function() {
					if(!this.disabled){
						var combo = this;
						getWnd('swOrgSearchWindow').show({
							object: 'Org_Served',
							onSelect: function(orgData) {
								if ( orgData.Org_id > 0 ) {
									combo.getStore().load({
										params: {
											Object:'Org',
											Org_id: orgData.Org_id,
											Org_Name:''
										},
										callback: function() {
											combo.setValue(orgData.Org_id);
											combo.focus(true, 500);
											combo.fireEvent('change', combo);
										}
									});
								}
								combo.loadStorageCombo();

								getWnd('swOrgSearchWindow').hide();
							},
							onClose: function() {combo.focus(true, 200)}
						});
					}
				},
				onTrigger2Click: function() {
					if(!this.disabled){
						this.setValue(null);
						this.loadStorageCombo();
					}
				},
				loadStorageCombo: function() {
					var base_form = form.FilterPanel.getForm();
					var org_id = base_form.findField('Org_id').getValue();
					var s_combo = base_form.findField('Storage_id');

					if (s_combo.getStore().baseParams.Org_id != org_id) {
						s_combo.setValue(null);
						s_combo.lastQuery = 'This query sample that is not will never appear';
						s_combo.getStore().removeAll();
						s_combo.getStore().baseParams.Org_id = org_id > 0 ? org_id : null;
						s_combo.getStore().load({
                            callback: function() {
                                form.doings.finish('loadStorage');
                            }
                        });
					}
				}
			}, {
				hiddenName: 'LpuBuilding_id',
				fieldLabel: langs('Подразделение'),
				id: 'DORLW_LpuBuildingCombo',
				lastQuery: '',
				linkedElements: [
					'DORLW_LpuSectionCombo'
				],
				listeners: {
					'change': function(combo, newValue, oldValue) {
						var base_form = form.FilterPanel.getForm();

						var s_combo = base_form.findField('Storage_id');
						s_combo.setValue(null);
						s_combo.lastQuery = 'This query sample that is not will never appear';
						s_combo.getStore().removeAll();
						s_combo.getStore().baseParams.LpuBuilding_id = newValue;
					}
				},
				listWidth: 700,
				width: 650,
				xtype: 'swlpubuildingglobalcombo'
			}, {
				hiddenName: 'LpuSection_id',
				id: 'DORLW_LpuSectionCombo',
				lastQuery: '',
				listeners: {
					'change': function(combo, newValue, oldValue) {
						var base_form = form.FilterPanel.getForm();

						var s_combo = base_form.findField('Storage_id');
						s_combo.setValue(null);
						s_combo.lastQuery = 'This query sample that is not will never appear';
						s_combo.getStore().removeAll();
						s_combo.getStore().baseParams.LpuSection_id = newValue;
					}
				},
				parentElementId: 'DORLW_LpuBuildingCombo',
				listWidth: 700,
				width: 650,
				xtype: 'swlpusectionglobalcombo'
			}, {
				xtype: 'swstoragecombo',
				fieldLabel: langs('Склад'),
				hiddenName: 'Storage_id',
				width: 650,
                onLoadStore: function(store) {
                    if (this.FirstValueSelect === true) {
                        if (store.getCount() > 0) {
                            this.setValue(store.getAt(0).get(this.valueField));
                        }
                        this.FirstValueSelect = false;
                    }
                },
                resetBaseParams: function() {
                    this.getStore().baseParams = new Object();
                }
			}, {
				layout: 'column',
				items: [{
					layout: 'form',
					border: false,
					bodyStyle: 'background:#DFE8F6;',
					items: [{
						fieldLabel: langs('Тип субсчета'),
						hiddenName: 'SubAccountType_id',
						xtype: 'swcommonsprcombo',
						anchor: false,
						width: 300,
						tabindex: TABINDEX_DORLW + 1,
						comboSubject: 'SubAccountType'
					}]
				}, {
					layout: 'form',
					border: false,
					bodyStyle: 'background:#DFE8F6;',
					items: [{
						xtype: 'checkbox',
						tabindex: TABINDEX_DORLW + 2,
						name: 'AllowReservation',
						fieldLabel: langs('Учитывать резервирование'),
						hidden: getGlobalOptions().region.nick == 'ufa'
					}]
				}]
			}, {
				codeField: 'KLAreaStat_Code',
				disabled: false,
				displayField: 'KLArea_Name',
				editable: true,
				enableKeyEvents: true,
				fieldLabel: langs('Территория'),
				hiddenName: 'KLAreaStat_id',
				store: new Ext.db.AdapterStore({
					autoLoad: true,
					dbFile: 'Promed.db',
					fields: [
						{ name: 'KLAreaStat_id', type: 'int' },
						{ name: 'KLAreaStat_Code', type: 'int' },
						{ name: 'KLArea_Name', type: 'string' },
						{ name: 'KLCountry_id', type: 'int' },
						{ name: 'KLRGN_id', type: 'int' },
						{ name: 'KLSubRGN_id', type: 'int' },
						{ name: 'KLCity_id', type: 'int' },
						{ name: 'KLTown_id', type: 'int' }
					],
					key: 'KLAreaStat_id',
					sortInfo: {
						field: 'KLAreaStat_Code',
						direction: 'ASC'
					},
					tableName: 'KLAreaStat'
				}),
				tpl: new Ext.XTemplate(
					'<tpl for="."><div class="x-combo-list-item">',
					'<font color="red">{KLAreaStat_Code}</font>&nbsp;{KLArea_Name}',
					'</div></tpl>'
				),
				valueField: 'KLAreaStat_id',
				width: 300,
				xtype: 'swbaselocalcombo'
			}]
		});

		//Вкладка "Финансирование"
		this.FilterFinancePanel = new sw.Promed.Panel({
			layout: 'form',
			autoScroll: true,
			bodyBorder: false,
			labelAlign: 'right',
			labelWidth: 170,
			border: false,
			frame: true,
			items: [{
				fieldLabel: langs('Источник финансирования'),
				hiddenName: 'DrugFinance_id',
				xtype: 'swcommonsprcombo',
				anchor: '80%',
				comboSubject: 'DrugFinance'
			}, {
				fieldLabel: langs('Статья расхода'),
				hiddenName: 'WhsDocumentCostItemType_id',
				xtype: getGlobalOptions().region.nick != 'ufa' ? 'swcommonsprcombo' : 'swwhsdocumentcostitemtypecombo',
				anchor: '80%',
				comboSubject: 'WhsDocumentCostItemType'
			}, {
				fieldLabel: langs('№ ГК'),
				anchor: '80%',
				name: 'WhsDocumentUc_Num',
				disabled: (getGlobalOptions().region.nick == 'ufa'),
				xtype: 'swwhsdocumentsupplynumcombo'
			}, {
				name: 'WhsDocumentUc_Name',
				fieldLabel: langs('Наименование'),
				disabled: (getGlobalOptions().region.nick == 'ufa'),
				anchor: '80%',
				xtype: 'textfield'
			}, {
				layout: 'column',
				items: [{
					layout: 'form',
					items: [{
						name: 'WhsDocumentUc_Date',
						fieldLabel: langs('Дата'),
						disabled: (getGlobalOptions().region.nick == 'ufa'),
						xtype: 'swdatefield',
						width: 120
					}]
				}, {
					layout: 'form',
					labelWidth: 60,
					items: [{
						xtype: 'textfield',
						fieldLabel: langs('Год'),
						disabled: (getGlobalOptions().region.nick == 'ufa'),
						name: 'WhsDocumentSupply_Year',
						width: 120
					}]
				}]
			}, {
                layout: 'form',
                items: [{
                    fieldLabel: 'Тип учета',
                    hiddenName: 'AccountType_id',
                    xtype: 'swcommonsprcombo',
                    anchor: false,
                    width: 307,
                    comboSubject: 'AccountType'
                }]
            }]
		});

		//Вкладка "Медикаменты"
		this.FilterDrugPanel = new sw.Promed.Panel({
			layout: 'form',
			autoScroll: true,
			bodyBorder: false,
			labelAlign: 'right',
			labelWidth: 170,
			border: false,
			frame: true,
			items: [{
				layout: 'column',
				items: [{
					layout: 'form',
					items: [{
						xtype: 'textfield',
						fieldLabel: langs('Код'),
						name: 'DrugNomen_Code',
						width: 120
					}]
				}, {
					layout: 'form',
					labelWidth: 110,
					items: [{
						xtype: 'textfield',
						fieldLabel: langs('Код компл. МНН'),
						name: 'DrugComplexMnnCode_Code',
						width: 120
					}]
				}]
			}, {
				fieldLabel: langs('МНН'),
				anchor: '80%',
				name: 'RlsActmatters_RusName',
				xtype: 'textfield'
			}, {
				fieldLabel: langs('Торг. наименование'),
				anchor: '80%',
				name: 'RlsTorg_Name',
				xtype: 'textfield'
			}, {
				fieldLabel: langs('Форма выпуска'),
				anchor: '80%',
				name: 'RlsClsdrugforms_Name',
				xtype: 'textfield'
			}, {
				layout: 'column',
				items: [{
					layout: 'form',
					items: [{
                        fieldLabel: langs('Серия выпуска'),
                        width: 240,
                        name: 'PrepSeries_Ser',
                        xtype: 'textfield'
                    }]
                }, {
					layout: 'form',
                    labelWidth: 120,
					items: [{
                        xtype: 'swcommonsprcombo',
                        comboSubject: 'GoodsUnit',
                        fieldLabel: 'Ед. учета',
                        hiddenName: 'GoodsUnit_id',
                        editable: true
                    }]
                }]
            }, {
				layout: 'column',
				items: [{
					layout: 'form',
					items: [{
						xtype: 'checkbox',
						tabindex: TABINDEX_DORLW + 2,
						name: 'PrepSeries_isDefect',
						fieldLabel: langs('Фальсификат')
					}]
				}, {
					layout: 'form',
					labelWidth: 300,
					items: [{
						//fieldLabel: 'Остаточный срок годности (мес.) от',  //getGlobalOptions().region.nick == 'ufa'
                                                fieldLabel: getGlobalOptions().region.nick == 'ufa' ? langs('Остаточный срок годности (дн.) от') : langs('Остаточный срок годности (мес.) от'),
                                                name: 'PrepSeries_godnMinMonthCount',
						xtype: 'numberfield',
						allowNegative: false,
						allowDecimals: false,
						width: 60
					}]
				}, {
					layout: 'form',
					labelWidth: 28,
					items: [{
						fieldLabel: langs('До'),
						name: 'PrepSeries_godnMaxMonthCount',
						xtype: 'numberfield',
						allowNegative: false,
						allowDecimals: false,
						width: 60
					}]
				}, {
					layout: 'form',
					labelWidth: 300,
					items: [{
						fieldLabel: langs('Товар без движения (количество дней)'),
						name: 'LastUpdateDayCount',
						xtype: 'numberfield',
						allowNegative: false,
						allowDecimals: false,
						width: 60
					}]
				}]
			}]
		});

		//Вкладка "Классификация"
		this.FilterClassPanel = new sw.Promed.Panel({
			layout: 'form',
			autoScroll: true,
			bodyBorder: false,
			labelAlign: 'right',
			labelWidth: 170,
			border: false,
			frame: true,
			items: [{
				xtype: 'swrlsclspharmagroupremotecombo',
				width: 500,
				anchor: '80%',
				fieldLabel: langs('Фармгруппа'),
				hiddenName: 'CLSPHARMAGROUP_ID'
			}, {
				xtype: 'swrlsclsatcremotecombo',
				width: 500,
				anchor: '80%',
				fieldLabel: langs('АТХ'),
				hiddenName: 'CLSATC_ID'
			}, {
				xtype: 'swrlsclsmzphgroupremotecombo',
				width: 500,
				anchor: '80%',
				fieldLabel: langs('ФТГ'),
				hiddenName: 'CLS_MZ_PHGROUP_ID'
			}, {
				xtype: 'swrlsstronggroupscombo',
				fieldLabel: langs('Сильнодействующие'),
				hiddenName: 'STRONGGROUPS_ID'
			}, {
				xtype: 'swrlsnarcogroupscombo',
				fieldLabel: langs('Наркотические'),
				hiddenName: 'NARCOGROUPS_ID'
			}, {
                layout: 'form',
                border: false,
                bodyStyle: 'background:#DFE8F6;',
                items: [{
                    xtype: 'checkbox',
                    name: 'isPKU',
                    fieldLabel: 'ПКУ'
                }]
            }]
		});

		//Вкладка "Производитель"
		this.FilterProducerPanel = new sw.Promed.Panel({
			layout: 'form',
			autoScroll: true,
			bodyBorder: false,
			labelAlign: 'right',
			labelWidth: 170,
			border: false,
			frame: true,
			items: [{
				xtype: 'swrlsfirmscombo',
				fieldLabel: langs('Фирма'),
				hiddenName: 'FIRMS_ID'
			}, {
				xtype: 'swrlscountrycombo',
				fieldLabel: langs('Страна'),
				hiddenName: 'COUNTRIES_ID'
			}]
		});

		this.FilterTabs = new Ext.TabPanel({
			autoScroll: true,
			activeTab: 0,
			border: true,
			resizeTabs: true,
			region: 'north',
			enableTabScroll: true,
			height: 175,
			minTabWidth: 120,
			tabWidth: 'auto',
			layoutOnTabChange: true,
			items:[{
				title: langs('Организация'),
				layout: 'fit',
				border:false,
				listeners: {
					'activate': function(panel) {
						var base_form = this.FilterPanel.getForm();

						if ( base_form.findField('LpuBuilding_id').getStore().getCount() == 0 ) {
							swLpuBuildingGlobalStore.clearFilter();
							base_form.findField('LpuBuilding_id').getStore().loadData(getStoreRecords(swLpuBuildingGlobalStore));
						}

						if ( base_form.findField('LpuSection_id').getStore().getCount() == 0 ) {
							setLpuSectionGlobalStoreFilter({allowLowLevel: 'yes'});
							base_form.findField('LpuSection_id').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
						}
					}.createDelegate(this)
				},
				items: [this.FilterOrgPanel]
			}, {
				title: langs('Финансирование'),
				layout: 'fit',
				border:false,
				items: [this.FilterFinancePanel]
			}, {
				title: langs('Медикаменты'),
				layout: 'fit',
				border:false,
				items: [this.FilterDrugPanel]
			}, {
				title: langs('Классификация'),
				layout: 'fit',
				border:false,
				items: [this.FilterClassPanel]
			}, {
				title: langs('Производитель'),
				layout: 'fit',
				border:false,
				items: [this.FilterProducerPanel]
			}]
		});

		this.FilterPanel = getBaseFiltersFrame({
			defaults: {bodyStyle:'background:#DFE8F6;width:100%;'},
			ownerWindow: form,
			toolBar: this.WindowToolbar,
			items: [this.FilterTabs]
		});

		this.DocumentUcGridPanel = new Ext.Panel({
            title: 'Документы учета',
            titleCollapse: true,
            collapsible: true,
			region: 'south',
			labelAlign: 'right',
			layout: 'fit',
			border: false,
            height: 135,
            plugins: [Ext.ux.PanelCollapsedTitle],
			items: [
                this.DocumentUcGrid
			]
		});

		this.formPanel = new Ext.Panel({
			region: 'center',
			labelAlign: 'right',
			layout: 'border',
			labelWidth: 50,
			border: false,
			items:
			[
				this.FilterPanel,
				this.RegistryGrid,
                this.DocumentUcGridPanel
			]
		});
		
		Ext.apply(this, {
			items: 
			[ 
				form.formPanel
			],
			buttons:
			[{
				text: 'Найти',
				tabIndex: TABINDEX_DORLW + 20,
				handler: function() {
					form.doSearch();
				},
				iconCls: 'search16'
			}, 
			{
				text: BTN_RESETFILTER,
				tabIndex: TABINDEX_DORLW + 21,
				handler: function() {
					form.doReset();
				},
				iconCls: 'resetsearch16'
			}, {
				text: '-'
			},
			HelpButton(this, TABINDEX_DORLW + 30),
			{
				iconCls: 'close16',
				tabIndex: TABINDEX_DORLW + 31,
				onTabAction: function()
				{
					form.FilterPanel.getForm().findField('WhsDocumentUc_Num').focus();
				},
				handler: function() {
					form.hide();
				},
				text: BTN_FRMCLOSE
			}]
		});
		sw.Promed.swDrugOstatRegistryListWindow.superclass.initComponent.apply(this, arguments);
	},
	show: function() {
		sw.Promed.swDrugOstatRegistryListWindow.superclass.show.apply(this, arguments);

        this.doings = new sw.Promed.Doings();
        this.mode = null;
        this.userMedStaffFact = {};
        this.disabledSearchOnReset = false;

        var base_form = this.FilterPanel.getForm();

        base_form.reset();
        base_form.findField('Storage_id').resetBaseParams();

		if (arguments[0]) {
            if(arguments[0].mode) {
                this.mode = arguments[0].mode;
            }
            if (arguments[0].userMedStaffFact) {
                this.userMedStaffFact = arguments[0].userMedStaffFact;
            }
            if (arguments[0].disabledSearchOnReset) {
                this.disabledSearchOnReset = arguments[0].disabledSearchOnReset;
            }
        }

		this.FilterTabs.setActiveTab(4);
		this.FilterTabs.setActiveTab(3);
		this.FilterTabs.setActiveTab(2);
		this.FilterTabs.setActiveTab(1);
		this.FilterTabs.setActiveTab(0);

        //еслим форма открывается в режиме просмотра остатков организации и к АРМ привязано ЛПУ, то открываем форму в режиме просмотра остатков МО
        if (this.mode == 'suppliers' && !Ext.isEmpty(this.userMedStaffFact.Lpu_id)) {
            this.mode = 'mo';
        }

		this.setMode();
		
		if (getGlobalOptions().region.nick == 'ufa' ) {
			if (Ext.isEmpty(getGlobalOptions().lpu_id) || getGlobalOptions().lpu_id == '0') {
			this.FilterTabs.hideTabStripItem (4);
			this.FilterTabs.hideTabStripItem (3);
			if ( getGlobalOptions().groups.toString().indexOf('AdminLLO') == -1) {
				base_form.findField('Org_id').disable();
				//this.FilterTabs.hideTabStripItem (0);
				//this.FilterTabs.setActiveTab(1);
			} else {
				base_form.findField('Org_id').enable();
			}           
				Ext.getCmp('DrugOstatRegistryListWindowRegistryGrid').ViewActions.action_view.setHidden(true);
			}
		}

	

	if(!this.RegistryGrid.getAction('dorl_export_csv')) {
		this.RegistryGrid.addActions({
			name: 'dorl_export_csv',
			iconCls: 'rpt-xls',
			text: langs('Экспорт в CSV'),
			hidden:  (getGlobalOptions().region.nick == 'ufa'),
			handler: this.exportToCSV.createDelegate(this)
		});
	}

	if(!this.RegistryGrid.getAction('dorl_print_cardreport')) {
		this.RegistryGrid.addActions({
			name: 'dorl_print_cardreport',
			iconCls: 'rpt-report',
			text: langs('Карточка партии'),
			handler: this.printCardRecord.createDelegate(this)
		});
	}

        base_form.findField('CLSATC_ID').getStore().load({params: {maxCodeLength: 5}});
		base_form.findField('CLSPHARMAGROUP_ID').getStore().load();
		base_form.findField('CLS_MZ_PHGROUP_ID').getStore().load();
		base_form.findField('Storage_id').getStore().proxy.conn.url = '/?c=RegistryRecept&m=loadStorageList';
		this.doReset()
		/*
		if (getGlobalOptions().region.nick != 'ufa') {
		    this.doReset()
		} else  if (getGlobalOptions().groups.toString().indexOf('AdminLLO') == -1) {
		    this.doReset()
		}; 
		*/		
	}
});