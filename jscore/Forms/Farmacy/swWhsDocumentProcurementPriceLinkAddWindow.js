/**
* swWhsDocumentProcurementPriceLinkAddWindow - окно выбора медикаментов
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Farmacy
* @access       public
* @copyright    Copyright (c) 2016 Swan Ltd.
* @author       Salakhov R.
* @version      03.2016
* @comment      
*/
sw.Promed.swWhsDocumentProcurementPriceLinkAddWindow = Ext.extend(sw.Promed.BaseForm, {
    autoHeight: false,
    title: 'Выбор тарифа',
    layout: 'border',
    id: 'WhsDocumentProcurementPriceLinkAddWindow',
    modal: true,
    shim: false,
    width: 750,
    resizable: false,
    maximizable: false,
    maximized: true,
    listeners: {
        hide: function() {
            this.onHide();
        }
    },
    onHide: Ext.emptyFn,
    loadGrid: function() {
        var wnd = this;
        var params = new Object();

        params.DrugRequestPurchaseSpec_id = this.DrugRequestPurchaseSpec_id;
        params.WhsDocumentProcurementRequestSpec_id = this.WhsDocumentProcurementRequestSpec_id;

        wnd.SearchGrid.removeAll();
        wnd.SearchGrid.loadData({
            globalFilters: params,
            callback: function() {}
        });

        document.getElementById('wdppla_checkAll_checkbox').checked = false;
    },
    doSelect:  function() {
        if (this.SearchGrid.getSelectedData().length > 0) {
            if (typeof this.onSelect == 'function' ) {
                this.onSelect(this.SearchGrid.getSelectedData());
            }
            this.hide();
        }
        return true;
    },
    show: function() {
        var wnd = this;
        sw.Promed.swWhsDocumentProcurementPriceLinkAddWindow.superclass.show.apply(this, arguments);
        this.action = 'add';
        this.onSelect = Ext.emptyFn;
        this.DrugRequestPurchaseSpec_id = null;
        this.WhsDocumentProcurementRequestSpec_id = null;

        if ( !arguments[0] ) {
            sw.swMsg.alert('Ошибка', 'Не указаны входные данные', function() { wnd.hide(); });
            return false;
        }
        if ( arguments[0].onSelect && typeof arguments[0].onSelect == 'function' ) {
            this.onSelect = arguments[0].onSelect;
        }
        if ( arguments[0].owner ) {
            this.owner = arguments[0].owner;
        }
        if ( arguments[0].DrugRequestPurchaseSpec_id ) {
            this.DrugRequestPurchaseSpec_id = arguments[0].DrugRequestPurchaseSpec_id;
        }
        if ( arguments[0].WhsDocumentProcurementRequestSpec_id ) {
            this.WhsDocumentProcurementRequestSpec_id = arguments[0].WhsDocumentProcurementRequestSpec_id;
        }

        var loadMask = new Ext.LoadMask(this.getEl(), {msg:'Загрузка...'});
        loadMask.show();
        wnd.loadGrid();
        loadMask.hide();

    },
    initComponent: function() {
        var wnd = this;

        this.SearchGrid = new sw.Promed.ViewFrame({
            actions: [
                {name: 'action_add', hidden: true},
                {name: 'action_edit', hidden: true},
                {name: 'action_view', hidden: true},
                {name: 'action_delete', hidden: true},
                {name: 'action_print', hidden: true}
            ],
            autoExpandColumn: 'autoexpand',
            autoExpandMin: 150,
            autoLoadData: false,
            border: true,
            dataUrl: '/?c=MzDrugRequest&m=loadWhsDocumentProcurementPriceLinkSourceList',
            height: 180,
            region: 'center',
            object: null,
            editformclassname: null,
            id: wnd.id + 'Grid',
            paging: false,
            style: 'margin-bottom: 10px',
            stringfields: [
                { name: 'Nomen_id', type: 'int', header: 'ID', key: true },
                {
                    name: 'false_checked',
                    width: 35,
                    sortable: false,
                    hideable: false,
                    renderer: function(v, p, record) {
                        var nomen_id = record.get('Nomen_id');
                        return '<input type="checkbox" value="'+nomen_id+'"'+(record.get('checked') ? ' checked="checked"' : '')+'" onClick="getWnd(\'swWhsDocumentProcurementPriceLinkAddWindow\').SearchGrid.checkOne(this.value);">';
                    },
                    header: '<input type="checkbox" id="wdppla_checkAll_checkbox" onClick="getWnd(\'swWhsDocumentProcurementPriceLinkAddWindow\').SearchGrid.checkAll(this.checked);">'
                },
                { name: 'checked',  hidden: true },
                { name: 'WhsDocumentProcurementPriceLink_PriceRub', type: 'money', header: 'Цена производителя' },
                { name: 'WhsDocumentProcurementPriceLink_PriceDate', type: 'date', header: 'Дата регистрации цены' },
                { name: 'Wholesale', hidden: true },
                { name: 'Retail', hidden: true },
                { name: 'DrugPrepFas_Name', type: 'string', header: 'Наименование', id: 'autoexpand' }
            ],
            title: false,
            toolbar: true,
            onLoadData: function() {
                document.getElementById('wdppla_checkAll_checkbox').checked = false;
            },
            onDblClick: function(grid) {
                var record = grid.getSelectionModel().getSelected();
                record.set('checked', !record.get('checked'));
                record.commit();
            },
            checkAll: function(checked) {
                if (!this.readOnly) {
                    this.getGrid().getStore().each(function(record){
                        if (record.get('Nomen_id') > 0) {
                            record.set('checked', checked);
                            record.commit();
                        }
                    });
                }
            },
            checkOne: function(nomen_id) {
                var grid = this.getGrid();
                var record = grid.getStore().getAt(grid.getStore().findBy(function(r) { return r.get('Nomen_id') == nomen_id; }));
                if (record) {
                    record.set('checked', !record.get('checked'));
                    record.commit();
                }
            },
            getSelectedData: function(){ //возвращает выбранные цены
                var data = new Array();
                this.getGrid().getStore().each(function(record) {
                    if (record.get('checked') && record.get('Nomen_id') > 0) {
                        data.push(record.data);
                    }
                });
                return data;
            },
            getJSONSelectedData: function(){ //возвращает выбранные цены в виде закодированной JSON строки
                var dataObj = this.getSelectedData();
                return dataObj.length > 0 ? Ext.util.JSON.encode(dataObj) : "";
            }
        });

        Ext.apply(this, {
            layout: 'border',
            buttons: [
                {
                    handler: function() {
                        this.ownerCt.doSelect();
                    },
                    iconCls: 'ok16',
                    text: BTN_FRMSELECT
                },
                {
                    text: '-'
                },
                HelpButton(this, 0),
                {
                    handler: function()  {
                        this.ownerCt.hide();
                    },
                    iconCls: 'cancel16',
                    text: BTN_FRMCANCEL
                }
            ],
            items:[
                this.SearchGrid
            ]
        });
        sw.Promed.swWhsDocumentProcurementPriceLinkAddWindow.superclass.initComponent.apply(this, arguments);
    }
});