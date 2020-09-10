/**
 * swDrugRequestExecViewWindow - произвольное просмотра информации о исполнении сводной заявки
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Farmacy
 * @access       public
 * @copyright    Copyright (c) 2014 Swan Ltd.
 * @author       Salakhov R.
 * @version      02.2016
 * @comment
 */
sw.Promed.swDrugRequestExecViewWindow = Ext.extend(sw.Promed.BaseForm, {
    autoHeight: false,
    title: 'Исполнение сводной заявки',
    layout: 'border',
    id: 'DrugRequestExecViewWindow',
    modal: true,
    shim: false,
    width: 400,
    resizable: false,
    maximizable: false,
    maximized: true,
    listeners: {
        hide: function() {
            this.onHide();
        }
    },
    setDrugGridInformationPanelData: function(record) {
        var wnd = this;

        var price = 0;
        var request_kolvo = 0;
        var request_sum = 0;
        var supply_kolvo = 0;
        var supply_sum = 0;
        var kolvo = 0;

        wnd.DrugGrid.getGrid().getStore().each(function(record) {
            price = record.get('WhsDocumentSupplySpec_PriceNDS')*1 > 0 ? record.get('WhsDocumentSupplySpec_PriceNDS')*1 : 0;
            request_kolvo += record.get('DrugRequestExec_PurchCount')*1 > 0 ? record.get('DrugRequestExec_PurchCount')*1 : 0;
            request_sum += record.get('DrugRequestExec_PurchCount')*price > 0 ? record.get('DrugRequestExec_PurchCount')*price : 0;
            supply_kolvo += record.get('DrugRequestExec_SupplyCount')*1 > 0 ? record.get('DrugRequestExec_SupplyCount')*1 : 0;
            supply_sum += record.get('DrugRequestExec_SupplyCount')*price > 0 ? record.get('DrugRequestExec_SupplyCount')*price : 0;
            kolvo += record.get('DrugRequestExec_Count')*1 > 0 ? record.get('DrugRequestExec_Count')*1 : 0;
        });

        wnd.DrugGridInformationPanel.clearData();
        wnd.DrugGridInformationPanel.setData('request_kolvo', request_kolvo);
        wnd.DrugGridInformationPanel.setData('request_sum', sw.Promed.Format.rurMoney(request_sum));
        wnd.DrugGridInformationPanel.setData('supply_kolvo', supply_kolvo);
        wnd.DrugGridInformationPanel.setData('supply_sum', sw.Promed.Format.rurMoney(supply_sum));
        wnd.DrugGridInformationPanel.setData('kolvo', kolvo);
        wnd.DrugGridInformationPanel.showData();
    },
    setPurchaseGridInformationPanelData: function(record) {
        var wnd = this;

        var uot_kolvo = 0;
        var uot_sum = 0;
        var supply_kolvo = 0;
        var supply_sum = 0;

        wnd.PurchaseGrid.getGrid().getStore().each(function(record) {
            uot_kolvo += record.get('WhsDocumentProcurementRequestSpec_Kolvo')*1 > 0 ? record.get('WhsDocumentProcurementRequestSpec_Kolvo')*1 : 0;
            uot_sum += record.get('WhsDocumentProcurementRequestSpec_Sum')*1 > 0 ? record.get('WhsDocumentProcurementRequestSpec_Sum')*1 : 0;
            supply_kolvo += record.get('WhsDocumentSupplySpec_KolvoUnit')*1 > 0 ? record.get('WhsDocumentSupplySpec_KolvoUnit')*1 : 0;
            supply_sum += record.get('WhsDocumentSupplySpec_SumNDS')*1 > 0 ? record.get('WhsDocumentSupplySpec_SumNDS')*1 : 0;
        });

        wnd.PurchaseGridInformationPanel.clearData();
        wnd.PurchaseGridInformationPanel.setData('uot_kolvo', uot_kolvo);
        wnd.PurchaseGridInformationPanel.setData('uot_sum', sw.Promed.Format.rurMoney(uot_sum));
        wnd.PurchaseGridInformationPanel.setData('supply_kolvo', supply_kolvo);
        wnd.PurchaseGridInformationPanel.setData('supply_sum', sw.Promed.Format.rurMoney(supply_sum));
        wnd.PurchaseGridInformationPanel.showData();
    },
    doSearch: function() {
        var wnd = this;
        var params = new Object();

        //params.start = 0;
        //params.limit = 100;
        params.DrugRequest_id = wnd.DrugRequest_id;

        wnd.DrugGrid.loadData({globalFilters: params});
    },
    show: function() {
        var wnd = this;
        sw.Promed.swDrugRequestExecViewWindow.superclass.show.apply(this, arguments);

        this.DrugRequest_id = null;
        this.onHide = Ext.emptyFn;;

        if (arguments[0]) {
            if (!Ext.isEmpty(arguments[0].DrugRequest_id)) {
                this.DrugRequest_id = arguments[0].DrugRequest_id;
            }
            if (arguments[0].onHide && typeof arguments[0].onHide == 'function') {
                this.onHide = arguments[0].onHide;
            }
        }

        this.DrugGrid.setParam('DrugRequest_id', this.DrugRequest_id, false);
        this.DrugGrid.setParam('callback', function() {
            wnd.doSearch();
        }, false);
        this.doSearch();
    },
    initComponent: function() {
        var wnd = this;

        this.DrugGridInformationPanel = new sw.Promed.HtmlTemplatePanel({
            region: 'north',
            win: wnd
        });
        tpl = "";
        tpl += "<table style='margin: 5px; float: left;'>";
        tpl += "<tr><td>Выделено из текущих остатков {request_kolvo} на сумму {request_sum} р.; {supply_kolvo} на сумму {supply_sum} р., из них включить в закуп {kolvo}</td></tr>";
        tpl += "</table>";
        this.DrugGridInformationPanel.setTemplate(tpl);

        this.PurchaseGridInformationPanel = new sw.Promed.HtmlTemplatePanel({
            region: 'south',
            win: wnd
        });
        tpl = "";
        tpl += "<table style='margin: 5px; float: left;'>";
        tpl += "<tr><td>Включено в закуп  {uot_kolvo} на сумму {uot_sum} р.; закуплено - {supply_kolvo} на сумму {supply_sum} р.</td></tr>";
        tpl += "</table>";
        this.PurchaseGridInformationPanel.setTemplate(tpl);

        this.DrugGrid = new sw.Promed.ViewFrame({
            region: 'center',
            editing: true,
            saveAtOnce: false,
            actions: [
                {name: 'action_add'},
                {name: 'action_edit', disabled: true, hidden: true},
                {name: 'action_view', disabled: true, hidden: true},
                {name: 'action_delete', url: '/?c=MzDrugRequest&m=deleteDrugRequestExec'},
                {name: 'action_print'},
                {name: 'action_save', hidden: true, disabled: true}
            ],
            autoExpandColumn: 'autoexpand',
            autoExpandMin: 125,
            autoLoadData: false,
            border: true,
            dataUrl: '/?c=MzDrugRequest&m=loadDrugRequestExecList',
            height: 250,
            editformclassname: 'swDrugRequestExecAddWindow',
            id: 'DrugRequestExecDrugGrid',
            paging: false,
            style: 'margin-bottom: 10px',
            stringfields: [
                { name: 'DrugRequestExec_id', type: 'int', header: 'ID', key: true },
                { name: 'DrugRequestPurchaseSpec_id', hidden: true },
                { name: 'WhdDocumentSupplySpec_id', hidden: true },
                { name: 'DrugRequestExec_PurchCount', type: 'string', header: 'Кол-во в заявке', width: 100 },
                { name: 'WhsDocumentSupply_Name', type: 'string', header: 'Контракт', width: 100 },
                { name: 'Drug_Name', type: 'string', header: 'Торговое наименование', width: 100, id: 'autoexpand' },
                { name: 'WhsDocumentSupplySpec_PriceNDS', type: 'money', header: 'Цена', width: 100 },
                { name: 'DrugRequestExec_SupplyCount', type: 'float', header: 'Кол-во из ГК', width: 100, editor: new Ext.form.NumberField(), css: 'background-color: #dfe8f6;' },
                { name: 'Supply_Sum', header: 'Сумма', width: 100, renderer: function(v, p, r){
                    var sum = (r.get('DrugRequestExec_SupplyCount')*1)*(r.get('WhsDocumentSupplySpec_PriceNDS')*1);
                    return sum > 0 ? sw.Promed.Format.rurMoney(sum, p, r) : null;
                } },
                { name: 'DrugRequestExec_Count', type: 'float', header: 'Из них включить в закуп', width: 100, editor: new Ext.form.NumberField(), css: 'background-color: #dfe8f6;' }
            ],
            title: 'Из текущих остатков',
            toolbar: true,
            onLoadData: function() {
                wnd.setDrugGridInformationPanelData();
            },
            onDblClick: function() {},
            onAfterEdit: function(o) {
                var count = o.record.get('DrugRequestExec_Count');
                var supply_count = o.record.get('DrugRequestExec_SupplyCount');

                if (supply_count <= 0) {
                    supply_count = 1;
                }

                if (count > supply_count) {
                    count = supply_count;
                }

                Ext.Ajax.request({
                    params: {
                        DrugRequestExec_id: o.record.get('DrugRequestExec_id'),
                        DrugRequestPurchaseSpec_id: o.record.get('DrugRequestPurchaseSpec_id'),
                        DrugRequestExec_Count: count,
                        DrugRequestExec_SupplyCount: supply_count
                    },
                    callback: function (options, success, response) {
                        var result = Ext.util.JSON.decode(response.responseText);
                        if (result.success) {
                            o.record.set('DrugRequestExec_Count', result.DrugRequestExec_Count);
                            o.record.set('DrugRequestExec_SupplyCount', result.DrugRequestExec_SupplyCount);
                            o.record.commit();
                            wnd.setDrugGridInformationPanelData();
                        }
                    },
                    url:'/?c=MzDrugRequest&m=saveDrugRequestExecCount'
                });
            },
            onRowSelect: function(sm, rowIdx, record) {
                if (record.get('DrugRequestPurchaseSpec_id') > 0) {
                    wnd.PurchaseGrid.loadData({
                        globalFilters: {
                            DrugRequestPurchaseSpec_id: record.get('DrugRequestPurchaseSpec_id')
                        }
                    });
                } else {
                    wnd.PurchaseGrid.removeAll();
                }
            }
        });

        this.PurchaseGrid = new sw.Promed.ViewFrame({
            region: 'center',
            actions: [
                {name: 'action_add', disabled: true, hidden: true},
                {name: 'action_edit', disabled: true, hidden: true},
                {name: 'action_view', disabled: true, hidden: true},
                {name: 'action_delete', disabled: true, hidden: true},
                {name: 'action_print'}
            ],
            autoExpandColumn: 'autoexpand',
            autoExpandMin: 125,
            autoLoadData: false,
            border: true,
            dataUrl: '/?c=MzDrugRequest&m=loadDrugRequestExecPurchaseList',
            editformclassname: null,
            id: 'DrugRequestExecPurchaseGrid',
            paging: false,
            style: 'margin-bottom: 10px',
            stringfields: [
                { name: 'WhsDocumentSupplySpec_id', type: 'int', header: 'ID', key: true },
                { name: 'WhsDocumentProcurementRequest_Name', type: 'string', header: 'Лот', width: 100, id: 'autoexpand' },
                { name: 'WhsDocumentProcurementRequestSpec_Kolvo', type: 'string', header: 'Кол-во в лоте', width: 100 },
                { name: 'WhsDocumentProcurementRequestSpec_Sum', type: 'string', header: 'На сумму', width: 100 },
                { name: 'WhsDocumentSupply_Name', type: 'string', header: 'Контракт', width: 100 },
                { name: 'Drug_Name', type: 'string', header: 'Торговое наименование', width: 200 },
                { name: 'WhsDocumentSupplySpec_KolvoUnit', type: 'string', header: 'Количество упаковок', width: 100 },
                { name: 'WhsDocumentSupplySpec_PriceNDS', type: 'money', header: 'Цена', width: 100 },
                { name: 'WhsDocumentSupplySpec_SumNDS', type: 'money', header: 'Сумма', width: 100 }
            ],
            title: 'Закуплено',
            toolbar: true,
            onLoadData: function() {
                wnd.setPurchaseGridInformationPanelData();
            }
        });

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
            items: [
                wnd.DrugGrid,
                {
                    height: 400,
                    region: 'south',
                    layout: 'border',
                    items: [
                        wnd.DrugGridInformationPanel,
                        wnd.PurchaseGrid,
                        wnd.PurchaseGridInformationPanel
                    ]
                }
            ]
        });
        sw.Promed.swDrugRequestExecViewWindow.superclass.initComponent.apply(this, arguments);
    }
});