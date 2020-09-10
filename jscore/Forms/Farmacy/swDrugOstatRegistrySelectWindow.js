/**
 * swDrugOstatRegistry - выбор медикаментов из регистра остатков
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      	Farmacy
 * @access       	public
 * @copyright		Copyright (c) 2016 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			21.03.2016
 */
/*NO PARSE JSON*/

sw.Promed.swDrugOstatRegistrySelectWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swDrugOstatRegistrySelectWindow',
	title: 'Остатки организации: Выбор',
	width: 800,
	height: 640,
	layout: 'border',
	modal: true,
	maximizable: true,
	maximized: true,

    doSave: function() {
		var wnd = this;

		var grid = this.GridPanel.getGrid();
		var selected_data = [];

        for (var dor_id in this.SelectedData) {
            selected_data.push({
                DrugOstatRegistry_id: dor_id,
                DrugOstatRegistry_Kolvo: this.SelectedData[dor_id]
            })
        }

    	this.onSelect(selected_data);
		this.hide();

		return true;
	},

    doReset: function() {
        this.SelectedData = new Object();
        this.FilterPanel.getForm().reset();
    },

    doSearch: function() {
		var wnd = this;
        var grid = this.GridPanel.getGrid();
        var params = this.params;
        var form = this.FilterPanel.getForm();

        this.SelectedData = new Object();

        Ext.apply(params, form.getValues());

        params.start = 0;
        params.limit = 100;
        params.PrepSeries_MonthCount_Max = form.findField('LessSixMonth').getValue() ? 5 : null;

        grid.getStore().load({
            params: params,
            callback: function() {
                /*var cnt = 0;
                grid.getStore().each(function(record) {
                    if(record.get('DrugOstatRegistry_id') > 0) {
                        cnt++;
                    }
                });
                if(cnt == 0) {
                    sw.swMsg.alert(lang['oshibka'], 'Остатки не найдены', function() { wnd.hide(); });
                }*/
            }.createDelegate(this)
        });
	},

	show: function() {
		sw.Promed.swDrugOstatRegistrySelectWindow.superclass.show.apply(this, arguments);

		var grid = this.GridPanel.getGrid();
        var wnd = this;

		grid.removeAll();

		this.onSelect = Ext.emptyFn;
        this.SelectedData = new Object(); //для сохранения данных при перелистывании страниц и обновлении грида
		this.params = {
            Org_id: null,
            Storage_id: null,
            WhsDocumentSupply_id: null,
            DrugFinance_id: null,
            WhsDocumentCostItemType_id: null,
            Actmatters_id: null,
            DrugComplexMnn_id: null,
            Tradenames_id: null,
            DrugPrep_id: null,
            Sort_Type: null
        };

		if (!arguments[0] || !arguments[0].Org_id) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_ukazanyi_vhodnyie_dannyie'], function() { wnd.hide(); });
			return false;
		}
		this.params.Org_id = arguments[0].Org_id;

		if (arguments[0].Storage_id) {
			this.params.Storage_id = arguments[0].Storage_id;
		}
		if (arguments[0].WhsDocumentSupply_id) {
			this.params.WhsDocumentSupply_id = arguments[0].WhsDocumentSupply_id;
		}
		if (arguments[0].DrugFinance_id) {
			this.params.DrugFinance_id = arguments[0].DrugFinance_id;
		}
		if (arguments[0].WhsDocumentCostItemType_id) {
			this.params.WhsDocumentCostItemType_id = arguments[0].WhsDocumentCostItemType_id;
		}
		if (arguments[0].Actmatters_id) {
			this.params.Actmatters_id = arguments[0].Actmatters_id;
		}
		if (arguments[0].DrugComplexMnn_id) {
			this.params.DrugComplexMnn_id = arguments[0].DrugComplexMnn_id;
		}
		if (arguments[0].Tradenames_id) {
			this.params.Tradenames_id = arguments[0].Tradenames_id;
		}
		if (arguments[0].DrugPrep_id) {
			this.params.DrugPrep_id = arguments[0].DrugPrep_id;
		}

		if (arguments[0].Sort_Type) {
			this.params.Sort_Type = arguments[0].Sort_Type;
		}
		if (arguments[0].onSelect) {
			this.onSelect = arguments[0].onSelect;
		}

        this.doReset();
        this.doSearch();
	},

	initComponent: function() {
		var wnd = this;

        this.FilterFormPanel = new sw.Promed.Panel({
            layout: 'form',
            autoScroll: true,
            bodyBorder: false,
            labelAlign: 'right',
            labelWidth: 170,
            border: false,
            frame: true,
            items: [{
                xtype: 'textfield',
                fieldLabel: 'МНН',
                name: 'DrugComplexMnnName_Name',
                width: 250
            }, {
                xtype: 'textfield',
                fieldLabel: 'Торговое наименование',
                name: 'DrugTorg_Name',
                width: 250
            }, {
                xtype: 'swcommonsprcombo',
                hiddenName: 'SubAccountType_id',
                fieldLabel: 'Субсчет',
                comboSubject: 'SubAccountType',
                width: 250
            }, {
                xtype: 'swyesnocombo',
                fieldLabel: 'Брак',
                hiddenName: 'PrepSeries_IsDefect',
                width: 250
            }, {
                xtype: 'checkbox',
                fieldLabel: 'Истекающий срок годности',
                name: 'LessSixMonth',
                width: 250
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
                        text: 'Найти',
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
                        text: 'Сброс',
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
            ownerWindow: this,
            toolBar: this.WindowToolbar,
            items: [
                this.FilterFormPanel,
                this.FilterButtonsPanel
            ]
        });

		this.GridPanel = new sw.Promed.ViewFrame({
			actions: [
				{name: 'action_add', hidden: true},
				{name: 'action_edit', hidden: true},
				{name: 'action_view', hidden: true},
				{name: 'action_delete', hidden: true},
				{name: 'action_refresh'},
				{name: 'action_print'},
				{name: 'action_save', hidden: true}
			],
			autoLoadData: false,
			border: true,
			dataUrl: '/?c=Farmacy&m=loadDrugOstatRegistryGrid',
			region: 'center',
            paging: true,
            pageSize: 100,
            root: 'data',
            totalProperty: 'totalCount',
            saveAtOnce: false,
			stringfields: [
				{name: 'DrugOstatRegistry_id', type: 'int', header: 'ID', key: true},
                {name: 'Kolvo', header: 'Заказать', editor: new Ext.form.NumberField(), css: 'font-weight: bolder;', width: 80},
                {name: 'GoodsUnit_id', hidden: true},
                {name: 'GoodsUnit_Name', type: 'string', header: 'Ед. учета', width: 80},
                {name: 'DrugOstatRegistry_Kolvo', type: 'string', header: 'Остаток', width: 80},
                {name: 'Drug_Code', type: 'string', header: 'Код ЛП', width: 65},
				{name: 'DrugComplexMnnName_Name', type: 'string', header: 'МНН', id: 'autoexpand'},
				{name: 'DrugTorg_Name', type: 'string', header: 'Торговое наим.', width: 140},
				{name: 'DrugForm_Name', type: 'string', header: 'Форма выпуска'},
				{name: 'Drug_Dose', type: 'string', header: 'Дозировка'},
				{name: 'Drug_Fas', type: 'string', header: 'Фасовка', width: 60},
                {name: 'PrepSeries_Ser', type: 'string', header: 'Серия', width: 80},
                {name: 'PrepSeries_GodnDate', header: 'Срок годности', type: 'date', width: 80, renderer: function(v, p, r){
                    if (v != null && r.get('PrepSeries_MonthCount') != '' && r.get('PrepSeries_MonthCount') < 6) {
                        v = '<font color="#ff0000">'+v+'</font>';
                    }
                    return v;
                }},
                {name: 'PrepSeries_isDefect_CK', type: 'checkcolumn', header: 'Брак', width: 80},
				{name: 'Drug_Firm', type: 'string', header: 'Производитель'},
				{name: 'Drug_RegNum', type: 'string', header: '№ РУ'},
                {name: 'WhsDocumentUc_Num', type: 'string', header: '№ ГК'},
                {name: 'DrugFinance_Name', type: 'string', header: 'Источник финансирования'},
                {name: 'WhsDocumentCostItemType_Name', type: 'string', header: 'Статья расхода'},
                {name: 'SubAccountType_Name', type: 'string', header: 'Тип субсчета', width: 80},
                {name: 'Org_Nick', type: 'string', header: 'Организация', width: 240},
                {name: 'Storage_Name', type: 'string', header: 'Склад', width: 80},
				{name: 'DrugOstatRegistry_Cost', type: 'string', header: 'Цена', width: 80, hidden: true},
                {name: 'PrepSeries_MonthCount', type: 'int', hidden: true}
			],
            onAfterEdit: function(o) {
                if (o.field == 'Kolvo') {
                    var kolvo = o.record.get('Kolvo');
                    var max_kolvo = o.record.get('DrugOstatRegistry_Kolvo');

                    if (kolvo > max_kolvo) {
                        kolvo = max_kolvo;
                    }

                    if (kolvo <= 0) {
                        kolvo = null;
                        if (!Ext.isEmpty(wnd.SelectedData[o.record.get('DrugOstatRegistry_id')])) {
                            delete wnd.SelectedData[o.record.get('DrugOstatRegistry_id')];
                        }
                    } else {
                        wnd.SelectedData[o.record.get('DrugOstatRegistry_id')] = kolvo;
                    }

                    o.record.set('Kolvo', kolvo);
                    o.record.commit();
                }
            },
            onLoadData: function() {
                this.getGrid().getStore().each(function(record) {
                    if (!Ext.isEmpty(wnd.SelectedData[record.get('DrugOstatRegistry_id')])) {
                        record.set('Kolvo', wnd.SelectedData[record.get('DrugOstatRegistry_id')]);
                        record.commit();
                    }
                });
            }
		});

		Ext.apply(this,{
			buttons: [
				{
					id: 'DORSW_ButtonSelect',
					text: lang['vyibrat'],
					tooltip: lang['vyibrat'],
					iconCls: 'ok16',
					handler: function()
					{
						this.doSave();
					}.createDelegate(this)
				},
				{
					text: '-'
				},
				HelpButton(this),
				{
					handler: function()
					{
						this.hide();
					}.createDelegate(this),
					iconCls: 'cancel16',
					text: BTN_FRMCLOSE
				}
			],
			items: [
                this.FilterPanel,
                this.GridPanel
            ]
		});

		sw.Promed.swDrugOstatRegistrySelectWindow.superclass.initComponent.apply(this, arguments);
		//this.form = this.FormPanel.getForm();
	}
});