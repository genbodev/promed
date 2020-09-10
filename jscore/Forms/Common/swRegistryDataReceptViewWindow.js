/**
* swRegistryDataReceptViewWindow - окно просмотра содержимого реестра рецептов
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2015 Swan Ltd.
* @author       Salakhov R.
* @version      11.2015
* @comment      
*/
sw.Promed.swRegistryDataReceptViewWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: 'Список рецептов реестра',
	layout: 'border',
	id: 'RegistryDataReceptViewWindow',
	modal: true,
	shim: false,
	width: 400,
	resizable: false,
	maximizable: true,
	maximized: true,
    setComboValueById: function(combo_name, id) {
        var combo = this.FilterPanel.getForm().findField(combo_name);

        if (combo) {
            combo.store.baseParams[combo_name] = id;
            combo.store.load({
                callback: function(){
                    combo.setValue(id);
                    combo.store.baseParams[combo_name] = null;
                }
            });
        }
    },
    setDefaultFilters: function() {
        var form = this.FilterPanel.getForm();

        var filters = new Object();
        filters.EvnRecept_otpDate_Range = this.RegistryLLO_Period;
        filters.WhsDocumentUc_Num = this.WhsDocumentUc_Num;
        filters.DrugFinance_id = this.DrugFinance_id;
        filters.WhsDocumentCostItemType_id = this.WhsDocumentCostItemType_id;
        filters.SupplierContragent_id = this.SupplierContragent_id;

        this.setComboValueById('SupplierContragent_id', filters.SupplierContragent_id);

        form.setValues(filters);
    },
	doSearch: function() {
		var wnd = this;
		var form = wnd.FilterPanel.getForm();
		var grid = wnd.SearchGrid;

		grid.removeAll();
		params = form.getValues();

        params.Person_Snils = form.findField('Person_Snils_Hidden').getValue();
        params.RegistryLLO_id = this.RegistryLLO_id;
        params.start = 0;
		params.limit = 100;

		grid.loadData({globalFilters: params});
	},
	doReset: function() {
		var wnd = this;
		var form = wnd.FilterPanel.getForm();

		form.reset();
		wnd.SearchGrid.removeAll();
	},
    changeReceptStatus: function() {
        var wnd = this;
        var record = wnd.SearchGrid.getGrid().getSelectionModel().getSelected();

        if (Ext.isEmpty(record.get('RegistryDataRecept_id'))) {
            return false;
        }

        var recept_id = record.get('RegistryDataRecept_id');

        getWnd('swReceptStatusFLKMEKSelectWindow').show({
            onSelect: function(data) {
                if (data && data.ReceptStatusFLKMEK_id > 0) {
                    wnd.setReceptStatus(recept_id, data.ReceptStatusFLKMEK_Code);
                }
            }
        });
    },
    setReceptStatus: function(recept_id, status_code) {
        var wnd = this;

        Ext.Ajax.request({
            params: {
                RegistryDataRecept_id: recept_id,
                ReceptStatusFLKMEK_Code: status_code
            },
            callback: function(options, success, response) {
                if (response.responseText != '') {
                    var response_obj = Ext.util.JSON.decode(response.responseText);
                    if (success && response_obj.success) {
                        wnd.SearchGrid.refreshRecords(null,0);
                    } else {
                        sw.swMsg.alert('Ошибка', response_obj.Error_Msg && response_obj.Error_Msg != '' ? response_obj.Error_Msg : 'При сохранении возникла ошибка');
                    }
                }
            },
            url: '/?c=RegistryLLO&m=setReceptStatus'
        });
    },
    show: function() {
		sw.Promed.swRegistryDataReceptViewWindow.superclass.show.apply(this, arguments);

		var wnd = this;
        var form = wnd.FilterPanel.getForm();

        if (!arguments[0]) {
            sw.swMsg.alert('Ошибка', 'Не указаны входные данные', function() { wnd.hide(); });
            return false;
        }

        this.ARMType = null;
        this.RegistryLLO_id = null;
        this.RegistryLLO_Period = null;
        this.WhsDocumentUc_Num = null;
        this.DrugFinance_id = null;
        this.WhsDocumentCostItemType_id = null;
        this.SupplierContragent_id = null;
        this.RegistryStatus_Code = null;
        this.Org_id = null;

        if (!Ext.isEmpty(arguments[0].ARMType)) {
            this.ARMType = arguments[0].ARMType;
        }
        if (arguments[0].RegistryLLO_id > 0) {
            this.RegistryLLO_id = arguments[0].RegistryLLO_id;
        }
        if (!Ext.isEmpty(arguments[0].RegistryLLO_Period)) {
            this.RegistryLLO_Period = arguments[0].RegistryLLO_Period;
        }
        if (!Ext.isEmpty(arguments[0].WhsDocumentUc_Num)) {
            this.WhsDocumentUc_Num = arguments[0].WhsDocumentUc_Num;
        }
        if (arguments[0].DrugFinance_id > 0) {
            this.DrugFinance_id = arguments[0].DrugFinance_id;
        }
        if (arguments[0].WhsDocumentCostItemType_id > 0) {
            this.WhsDocumentCostItemType_id = arguments[0].WhsDocumentCostItemType_id;
        }
        if (arguments[0].SupplierContragent_id > 0) {
            this.SupplierContragent_id = arguments[0].SupplierContragent_id;
        }
        if (arguments[0].owner && !Ext.isEmpty(arguments[0].owner.RegistryStatus_Code)) {
            this.RegistryStatus_Code = arguments[0].owner.RegistryStatus_Code;
        }
        if (arguments[0].Org_id > 0) {
            this.Org_id = arguments[0].Org_id;
        }

        this.SearchGrid.addActions({
            name:'action_rdrv_change_status',
            text:'Изменить статус по экспертизе',
            tooltip: 'Изменить статус по экспертизе',
            handler: function() {
                wnd.changeReceptStatus();
            },
            iconCls: 'actions16'
        });

        this.FilterTabPanel.setActiveTab(3);
        this.FilterTabPanel.setActiveTab(2);
        this.FilterTabPanel.setActiveTab(1);
        this.FilterTabPanel.setActiveTab(0);

        this.doReset();
        form.findField('FarmacyContragent_id').getStore().baseParams.ContragentType_CodeList = '3'; //3 - Аптка

        this.setDefaultFilters();
		this.doSearch();

        //настройка доступности действий
        if (haveArmType('spesexpertllo') || haveArmType('mekllo')) { //spesexpertllo - АРМ специалиста по экспертизе ЛЛО; mekllo - АРМ МЭК ЛЛО.
            this.SearchGrid.ViewActions.action_rdrv_change_status.enable_blocked = false;
            this.ErrorSearchGrid.ViewActions.action_add.enable_blocked = false;
            this.ErrorSearchGrid.ViewActions.action_delete.enable_blocked = false;
        } else {
            this.SearchGrid.ViewActions.action_rdrv_change_status.enable_blocked = true;
            this.ErrorSearchGrid.ViewActions.action_add.enable_blocked = true;
            this.ErrorSearchGrid.ViewActions.action_delete.enable_blocked = true;
        }

        if (this.RegistryStatus_Code == '1' && this.ARMType == 'merch' && (Ext.isEmpty(this.Org_id) || this.Org_id == getGlobalOptions().org_id)) { //1 - Сформированные; merch - АРМ товароведа.
            this.SearchGrid.ViewActions.action_delete.enable_blocked = false;
        } else {
            this.SearchGrid.ViewActions.action_delete.enable_blocked = true;
        }

        this.SearchGrid.setColumnHidden('check', getGlobalOptions().region.nick != 'saratov');
	},
	initComponent: function() {
		var wnd = this;

        this.lpu_combo = new sw.Promed.SwBaseRemoteCombo ({
            fieldLabel: 'МО',
            hiddenName: 'Lpu_id',
            displayField: 'Lpu_Name',
            valueField: 'Lpu_id',
            editable: true,
            width: 300,
            tpl: new Ext.XTemplate(
                '<tpl for="."><div class="x-combo-list-item">',
                '{Lpu_Name}&nbsp;',
                '</div></tpl>'
            ),
            store: new Ext.data.SimpleStore({
                autoLoad: false,
                fields: [
                    { name: 'Lpu_id', mapping: 'Lpu_id' },
                    { name: 'Lpu_Name', mapping: 'Lpu_Name' }
                ],
                key: 'Lpu_id',
                sortInfo: { field: 'Lpu_Name' },
                url:'/?c=RegistryLLO&m=loadLpuCombo'
            }),
            onTrigger2Click: function() {
                var combo = this;

                if (combo.disabled) {
                    return false;
                }

                combo.clearValue();
                combo.lastQuery = '';
                combo.getStore().removeAll();
                combo.getStore().baseParams.query = '';
                combo.fireEvent('change', combo, null);
            }
        });

        this.exp_status_combo = new sw.Promed.SwBaseRemoteCombo ({
            fieldLabel: 'Статус рецепта',
            hiddenName: 'ReceptStatusFLKMEK_id',
            displayField: 'ReceptStatusFLKMEK_Name',
            valueField: 'ReceptStatusFLKMEK_id',
            editable: false,
            triggerAction: 'all',
            trigger2Class: 'hideTrigger',
            width: 317,
            listWidth: 300,
            tpl: new Ext.XTemplate(
                '<tpl for="."><div class="x-combo-list-item">',
                '<font color="red">{ReceptStatusFLKMEK_Code}</font>&nbsp;{ReceptStatusFLKMEK_Name}&nbsp;',
                '</div></tpl>'
            ),
            store: new Ext.data.SimpleStore({
                autoLoad: false,
                fields: [
                    { name: 'ReceptStatusFLKMEK_id', mapping: 'ReceptStatusFLKMEK_id' },
                    { name: 'ReceptStatusFLKMEK_Code', mapping: 'ReceptStatusFLKMEK_Code' },
                    { name: 'ReceptStatusFLKMEK_Name', mapping: 'ReceptStatusFLKMEK_Name' }
                ],
                key: 'ReceptStatusFLKMEK_id',
                sortInfo: { field: 'ReceptStatusFLKMEK_Code' },
                url:'/?c=RegistryLLO&m=loadReceptStatusFLKMEKCombo'
            })
        });

        this.exp_error_combo = new sw.Promed.SwBaseRemoteCombo ({
            fieldLabel: 'Ошибка',
            hiddenName: 'RegistryReceptErrorType_id',
            displayField: 'RegistryReceptErrorType_Name',
            valueField: 'RegistryReceptErrorType_id',
            editable: false,
            triggerAction: 'all',
            trigger2Class: 'hideTrigger',
            width: 317,
            listWidth: 300,
            tpl: new Ext.XTemplate(
                '<tpl for="."><div class="x-combo-list-item">',
                '{RegistryReceptErrorType_Name}&nbsp;',
                '</div></tpl>'
            ),
            store: new Ext.data.SimpleStore({
                autoLoad: false,
                fields: [
                    { name: 'RegistryReceptErrorType_id', mapping: 'RegistryReceptErrorType_id' },
                    { name: 'RegistryReceptErrorType_Name', mapping: 'RegistryReceptErrorType_Name' }
                ],
                key: 'RegistryReceptErrorType_id',
                sortInfo: { field: 'RegistryReceptErrorType_Name' },
                url:'/?c=RegistryLLO&m=loadRegistryReceptErrorTypeCombo'
            })
        });

        //Финансирование
        this.FilterFinancePanel = new sw.Promed.Panel({
            layout: 'form',
            autoScroll: true,
            bodyBorder: false,
            labelAlign: 'right',
            labelWidth: 140,
            border: false,
            frame: true,
            items: [{
                xtype: 'swcontragentcombo',
                fieldLabel: 'Поставщик',
                hiddenName: 'SupplierContragent_id',
                width: 300
            }, {
                xtype: 'textfield',
                fieldLabel: 'Контракт',
                name: 'WhsDocumentUc_Num',
                width: 300
            }, {
                xtype: 'swdrugfinancecombo',
                fieldLabel: 'Финансирование',
                name: 'DrugFinance_id',
                width: 300
            }, {
                xtype: 'swwhsdocumentcostitemtypecombo',
                fieldLabel: 'Статья расхода',
                name: 'WhsDocumentCostItemType_id',
                width: 300
            }]
        });

        //Отпуск ЛС
        this.FilterProvidePanel = new sw.Promed.Panel({
            layout: 'form',
            autoScroll: true,
            bodyBorder: false,
            labelAlign: 'right',
            labelWidth: 140,
            border: false,
            frame: true,
            items: [{
                xtype: 'daterangefield',
                name: 'EvnRecept_otpDate_Range',
                fieldLabel: 'Период',
                plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
                width: 200
            }, {
                xtype: 'swcontragentcombo',
                fieldLabel: 'Аптека',
                hiddenName: 'FarmacyContragent_id',
                width: 300
            }, {
                xtype: 'swyesnocombo',
                fieldLabel: 'После отсрочки',
                hiddenName: 'Recept_isAfterDelay',
                width: 300
            }, {
                xtype: 'textfield',
                fieldLabel: 'МНН',
                name: 'DrugComplexMnn_Name',
                width: 300
            }, {
                xtype: 'textfield',
                fieldLabel: 'Торговое наим.',
                name: 'Drug_Name',
                width: 300
            }]
        });

        //Выписка рецепта
        this.FilterReceptPanel = new sw.Promed.Panel({
            layout: 'form',
            autoScroll: true,
            bodyBorder: false,
            labelAlign: 'right',
            labelWidth: 110,
            border: false,
            frame: true,
            items: [{
                layout: 'column',
                items: [{
                    layout: 'form',
                    items: [{
                        xtype: 'textfield',
                       fieldLabel: 'Фамилия',
                        name: 'Person_SurName'
                    }]
                }, {
                    layout: 'form',
                    items: [{
                        xtype: 'textfield',
                        fieldLabel: 'Имя',
                        name: 'Person_FirName'
                    }]
                }, {
                    layout: 'form',
                    items: [{
                        xtype: 'textfield',
                        fieldLabel: 'Отчество',
                        name: 'Person_SecName'
                    }]
                }]
            }, {
                layout: 'column',
                items: [{
                    layout: 'form',
                    items: [{
                        xtype: 'swsnilsfield',
                        fieldLabel: 'СНИЛС',
                        name: 'Person_Snils'
                    }]
                }, {
                    layout: 'form',
                    items: [{
                        xtype: 'textfield',
                        fieldLabel: 'Серия рецепта',
                        name: 'EvnRecept_Ser'
                    }]
                }, {
                    layout: 'form',
                    items: [{
                        xtype: 'textfield',
                        fieldLabel: 'Номер рецепта',
                        name: 'EvnRecept_Num'
                    }]
                }]
            }, {
                xtype: 'swprivilegetypecombo',
                fieldLabel: 'Льгота',
                name: 'PrivilegeType_id',
                width: 300
            }, {
                xtype: 'textfield',
                fieldLabel: 'Врач',
                name: 'MedPersonal_Name',
                width: 300
            },
            wnd.lpu_combo
            ]
        });

        //Экспертиза
        this.FilterExpertisePanel = new sw.Promed.Panel({
            layout: 'form',
            autoScroll: true,
            bodyBorder: false,
            labelAlign: 'right',
            labelWidth: 140,
            border: false,
            frame: true,
            items: [
                wnd.exp_status_combo,
                wnd.exp_error_combo
            ]
        });

        this.FilterTabPanel = new Ext.TabPanel({
            id: 'DNSW_DrugNomenTabsPanel',
            autoScroll: true,
            activeTab: 0,
            border: true,
            resizeTabs: true,
            region: 'north',
            enableTabScroll: true,
            height: 170,
            minTabWidth: 120,
            tabWidth: 'auto',
            layoutOnTabChange: true,
            items:[{
                title: 'Финансирование',
                layout: 'fit',
                border: false,
                items: [wnd.FilterFinancePanel]
            }, {
                title: 'Отпуск ЛС',
                layout: 'fit',
                border: false,
                items: [wnd.FilterProvidePanel]
            }, {
                title: 'Выписка рецепта',
                layout: 'fit',
                border: false,
                items: [wnd.FilterReceptPanel]
            }, {
                title: 'Экспертиза',
                layout: 'fit',
                border: false,
                items: [wnd.FilterExpertisePanel]
            }]
        });

		this.FilterButtonsPanel = new sw.Promed.Panel({
			autoScroll: true,
			bodyBorder: false,
			border: false,
			frame: true,
			items: [{
				layout: 'column',
				items: [{
					layout:'form',
					items: [{
						style: "padding-left: 10px",
						xtype: 'button',
						text: 'Поиск',
						iconCls: 'search16',
						minWidth: 100,
						handler: function() {
							wnd.doSearch();
						}
					}]
				}, {
					layout:'form',
					items: [{
						style: "padding-left: 10px",
						xtype: 'button',
						text: 'Очистить',
						iconCls: 'reset16',
						minWidth: 100,
						handler: function() {
							wnd.doReset();
							wnd.doSearch();
						}
					}]
				}]
			}]
		});

		this.FilterPanel = getBaseFiltersFrame({
			region: 'north',
			defaults: {bodyStyle:'background:#DFE8F6;width:100%;'},
			ownerWindow: wnd,
			toolBar: this.WindowToolbar,
			items: [
				this.FilterTabPanel,
				this.FilterButtonsPanel
			]
		});

		this.SearchGrid = new sw.Promed.ViewFrame({
            region: 'center',
			actions: [
				{name: 'action_add', disabled: true, hidden: true},
				{name: 'action_edit', disabled: true, hidden: true},
				{name: 'action_view', text: 'Информация о рецепте', tooltip: 'Информация о рецепте'},
				{name: 'action_delete', url: '/?c=RegistryLLO&m=deleteRegistryDataRecept'},
				{name: 'action_print'}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 125,
			autoLoadData: false,
			border: true,
			dataUrl: '/?c=RegistryLLO&m=loadRegistryDataReceptList',
			height: 180,
			object: 'RegistryDataRecep',
			editformclassname: 'swEvnReceptRlsEditWindow',
			id: 'rdrvRegistryDataReceptSearchGrid',
			paging: true,
			root: 'data',
			totalProperty: 'totalCount',
			style: 'margin-bottom: 10px',
			stringfields: [
				{ name: 'RegistryDataRecept_id', type: 'int', header: 'ID', key: true },
				{ name: 'ReceptOtov_id', hidden: true },
				{ name: 'EvnRecept_id', hidden: true, isparams: true },
                {
                    name: 'check',
                    header: 'Рецепт получен',
                    width: 150,
                    sortable: false,
                    hideable: false,
                    renderer: function(v, p, record) {
                        var id = record.get('RegistryDataRecept_id');
                        var value = ' value="'+id+'"';
                        var checked = record.get('IsReceived_Code') > 0 ? ' checked="checked"' : '';
                        var onclick = ' onClick="getWnd(\'swRegistryDataReceptViewWindow\').setReceptStatus(this.value, this.checked ? 5 : 0);"'; //0 - В обработке; 5 - Принят к оплате.
                        var disabled = wnd.SearchGrid.ViewActions.action_rdrv_change_status.enable_blocked ? ' disabled="disabled"' : '';

                        return id > 0 ? ' <input type="checkbox" '+value+checked+onclick+disabled+'>' : '';
                    }
                },
                { name: 'IsReceived_Code', hidden: true },
                { name: 'ReceptStatusFLKMEK_Name', type: 'string', header: 'Статус рецепта', width: 150 },
                { name: 'RegistryDataRecept_Sum', type: 'money', header: 'Стоимость ЛС', width: 150 },
                { name: 'RegistryDataRecept_Sum2', type: 'money', header: 'Стоимость услуги', width: 150 },
                { name: 'FarmacyOrg_Code', type: 'string', header: 'Код Аптеки', width: 150 },
                { name: 'FarmacyOrg_Name', type: 'string', header: 'Аптека', width: 150 },
                { name: 'EvnRecept_Ser', type: 'string', header: 'Серия рецепта', width: 150 },
                { name: 'EvnRecept_Num', type: 'string', header: '№ рецепта', width: 150 },
                { name: 'EvnRecept_obrDate', type: 'date', header: 'Дата выписки', width: 150 },
                { name: 'EvnRecept_otpDate', type: 'date', header: 'Дата отпуска ЛС', width: 150 },
                { name: 'LS_Code', type: 'string', header: 'Выписано: Код ЛС', width: 150 },
                { name: 'LS_Name', type: 'string', header: 'Выписано: ЛС', width: 150 },
                { name: 'EvnRecept_Kolvo', type: 'string', header: 'Выписано: Кол-во (уп.)', width: 150 },
                { name: 'Drug_Name', type: 'string', header: 'Отпущено: Наименование ЛП', width: 150 },
                { name: 'PrepSeries_Ser', type: 'string', header: 'Отпущено: Серия выпуска', width: 150 },
                { name: 'DocumentUcStr_Count', type: 'string', header: 'Отпущено: Количество', width: 150 },
                { name: 'DocumentUcStr_PriceR', type: 'string', header: 'Отпущено: Цена', width: 150 },
                { name: 'DocumentUcStr_SumR', type: 'string', header: 'Отпущено: Сумма', width: 150 },
                { name: 'WhsDocumentUc_Num', type: 'string', header: 'Отпущено: № ГК', width: 150 },
                { name: 'PrivilegeType_Code', type: 'string', header: 'Льгота', width: 150 },
                { name: 'PaymentPercent', type: 'string', header: 'Процент оплаты', width: 150 },
                { name: 'Diag_Code', type: 'string', header: 'Диагноз', width: 150 },
                { name: 'MedPersonal_Fio', type: 'string', header: 'Врач', width: 150 },
                { name: 'Lpu_Name', type: 'string', header: 'МО', width: 150 },
                { name: 'EvnRecept_isVK', type: 'string', header: 'ВК', width: 150 },
                { name: 'Person_Snils', type: 'string', header: 'Пациент: СНИЛС', width: 150 },
                { name: 'Person_Fio', type: 'string', header: 'Пациент: ФИО', width: 150 },
                { name: 'Person_BirthDay', type: 'date', header: 'Пациент: Дата рождения', width: 150 }
			],
			title: 'Рецепты',
			toolbar: true,
            onRowSelect: function(sm, rowIdx, record) {
                if (record.get('ReceptOtov_id') > 0) {
                    wnd.ErrorSearchGrid.loadData({
                        globalFilters: {
                            RegistryLLO_id: wnd.RegistryLLO_id,
                            ReceptOtov_id: record.get('ReceptOtov_id')
                        }
                    });
                } else {
                    wnd.ErrorSearchGrid.removeAll();
                    wnd.ErrorSearchGrid.ViewActions.action_add.setDisabled(true);
                }

                if (record.get('RegistryDataRecept_id') > 0 && !this.ViewActions.action_delete.enable_blocked) {
                    this.ViewActions.action_delete.setDisabled(false);
                } else {
                    this.ViewActions.action_delete.setDisabled(true);
                }

                if (record.get('RegistryDataRecept_id') > 0 && !this.ViewActions.action_rdrv_change_status.enable_blocked) {
                    this.ViewActions.action_rdrv_change_status.setDisabled(false);
                } else {
                    this.ViewActions.action_rdrv_change_status.setDisabled(true);
                }

                if (record.get('EvnRecept_id') > 0) {
                    this.ViewActions.action_view.setDisabled(false);
                } else {
                    this.ViewActions.action_view.setDisabled(true);
                }
            }
		});

		this.ErrorSearchGrid = new sw.Promed.ViewFrame({
            region: 'south',
			actions: [
				{name: 'action_add'},
				{name: 'action_edit', disabled: true, hidden: true},
				{name: 'action_view', disabled: true, hidden: true},
				{name: 'action_delete', url: '/?c=RegistryLLO&m=deleteRegistryLLOError'},
				{name: 'action_print'}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 125,
			autoLoadData: false,
			border: true,
			dataUrl: '/?c=RegistryLLO&m=loadRegistryLLOErrorList',
			height: 180,
			object: 'RegistryDataRecept',
			editformclassname: 'swRegistryReceptErrorTypeSelectWindow',
			id: 'rdrvRegistryDataReceptErrorSearchGrid',
			style: 'margin-bottom: 10px',
			stringfields: [
				{ name: 'RegistryLLOError_id', type: 'int', header: 'ID', key: true },
                { name: 'RegistryLLOError_insDT', type: 'date', header: 'Дата и время', width: 150 },
                { name: 'RegistryReceptErrorType_Type', type: 'string', header: 'Код ошибки', width: 150 },
                { name: 'RegistryReceptErrorType_Name', type: 'string', header: 'Наименование ошибки', width: 150 },
                { name: 'RegistryReceptErrorType_Descr', type: 'string', header: 'Описание', width: 150 }
            ],
			title: 'Ошибки рецепта',
			toolbar: true,
            onRowSelect: function(sm, rowIdx, record) {
                var recept_record = wnd.SearchGrid.getGrid().getSelectionModel().getSelected();

                if (recept_record.get('ReceptOtov_id') > 0 && !this.ViewActions.action_add.enable_blocked) {
                    this.ViewActions.action_add.setDisabled(false);
                } else {
                    this.ViewActions.action_add.setDisabled(true);
                }

                if (record.get('RegistryLLOError_id') > 0 && !this.ViewActions.action_delete.enable_blocked) {
                    this.ViewActions.action_delete.setDisabled(false);
                } else {
                    this.ViewActions.action_delete.setDisabled(true);
                }
            }
		});

        this.ErrorSearchGrid.setParam('onSelect', function(data) {
            var recept_record = wnd.SearchGrid.getGrid().getSelectionModel().getSelected();

            if (data && data.RegistryReceptErrorType_id > 0 && recept_record.get('ReceptOtov_id') > 0) {
                Ext.Ajax.request({
                    params: {
                        RegistryLLO_id: wnd.RegistryLLO_id,
                        ReceptOtov_id: recept_record.get('ReceptOtov_id'),
                        RegistryReceptErrorType_id: data.RegistryReceptErrorType_id
                    },
                    callback: function(options, success, response) {
                        if (response.responseText != '') {
                            var response_obj = Ext.util.JSON.decode(response.responseText);
                            if (success && response_obj.success) {
                                wnd.ErrorSearchGrid.refreshRecords(null,0);
                            } else {
                                sw.swMsg.alert('Ошибка', response_obj.Error_Msg && response_obj.Error_Msg != '' ? response_obj.Error_Msg : 'При сохранении возникла ошибка');
                            }
                        }
                    },
                    url: '/?c=RegistryLLO&m=saveRegistryLLOError'
                });
            }

        }, false);

		Ext.apply(this, {
			layout: 'border',
			buttons:
			[{
				text: '-'
			},
			HelpButton(this, 0),
			{
				handler: function() 
				{
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],
			items:[
				wnd.FilterPanel,
				wnd.SearchGrid,
				wnd.ErrorSearchGrid
			]
		});
		sw.Promed.swRegistryDataReceptViewWindow.superclass.initComponent.apply(this, arguments);
	}	
});