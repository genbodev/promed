/**
* swWhsDocumentCommercialOfferDrugAddWindow - окно выбора цены контракта
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
sw.Promed.swWhsDocumentCommercialOfferDrugAddWindow = Ext.extend(sw.Promed.BaseForm, {
    autoHeight: false,
    title: 'Выбор цены коммерческого предложения',
    layout: 'border',
    id: 'WhsDocumentCommercialOfferDrugAddWindow',
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

        params.UserOrg_id = getGlobalOptions().org_id;
        params.DrugRequestPurchaseSpec_id = this.DrugRequestPurchaseSpec_id;
        params.WhsDocumentProcurementRequestSpec_id = this.WhsDocumentProcurementRequestSpec_id;

        wnd.SearchGrid.removeAll();
        wnd.SearchGrid.loadData({
            globalFilters: params,
            callback: function() {}
        });

        document.getElementById('wdcoda_checkAll_checkbox').checked = false;
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
        sw.Promed.swWhsDocumentCommercialOfferDrugAddWindow.superclass.show.apply(this, arguments);
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
                {name: 'action_add'},
                {name: 'action_edit'},
                {name: 'action_view', hidden: true},
                {name: 'action_delete', hidden: true},
                {name: 'action_print', hidden: true}
            ],
            autoExpandColumn: 'autoexpand',
            autoExpandMin: 150,
            autoLoadData: false,
            border: true,
            dataUrl: '/?c=MzDrugRequest&m=loadWhsDocumentCommercialOfferDrugSourceList',
            height: 180,
            region: 'center',
            object: null,
            editformclassname: 'swCommercialOfferEditWindow',
            id: wnd.id + 'Grid',
            paging: false,
            style: 'margin-bottom: 10px',
            stringfields: [
                { name: 'CommercialOfferDrug_id', type: 'int', header: 'ID', key: true },
                { name: 'CommercialOffer_id', hidden: true, isparams: true },
                {
                    name: 'false_checked',
                    width: 35,
                    sortable: false,
                    hideable: false,
                    renderer: function(v, p, record) {
                        var spec_id = record.get('CommercialOfferDrug_id');
                        return '<input type="checkbox" value="'+spec_id+'"'+(record.get('checked') ? ' checked="checked"' : '')+'" onClick="getWnd(\'swWhsDocumentCommercialOfferDrugAddWindow\').SearchGrid.checkOne(this.value);">';
                    },
                    header: '<input type="checkbox" id="wdcoda_checkAll_checkbox" onClick="getWnd(\'swWhsDocumentCommercialOfferDrugAddWindow\').SearchGrid.checkAll(this.checked);">'
                },
                { name: 'checked',  hidden: true },
                { name: 'CommercialOffer_begDT', type: 'date', header: 'Дата' },
                { name: 'Supplier_Name', type: 'string', header: 'Поставщик' },
                { name: 'CommercialOfferDrug_Price', type: 'money', header: 'Цена' },
                { name: 'DrugComplexMnnName_Name', type: 'string', header: 'МНН' },
                { name: 'DrugPrepFasCode_Code', type: 'string', header: 'Код' },
                { name: 'DrugPrepFas_Name', type: 'string', header: 'Наименование', id: 'autoexpand' },
                { name: 'Org_D_Name', type: 'string', header: 'Организация' }
            ],
            title: false,
            toolbar: true,
            onLoadData: function() {
                document.getElementById('wdcoda_checkAll_checkbox').checked = false;
            },
            onDblClick: function(grid) {
                var record = grid.getSelectionModel().getSelected();
                record.set('checked', !record.get('checked'));
                record.commit();
            },
            checkAll: function(checked) {
                if (!this.readOnly) {
                    this.getGrid().getStore().each(function(record){
                        if (record.get('CommercialOfferDrug_id') > 0) {
                            record.set('checked', checked);
                            record.commit();
                        }
                    });
                }
            },
            checkOne: function(spec_id) {
                var grid = this.getGrid();
                var record = grid.getStore().getAt(grid.getStore().findBy(function(r) { return r.get('CommercialOfferDrug_id') == spec_id; }));
                if (record) {
                    record.set('checked', !record.get('checked'));
                    record.commit();
                }
            },
            getSelectedData: function(){ //возвращает выбранные остатки
                var data = new Array();
                this.getGrid().getStore().each(function(record) {
                    if (record.get('checked') && record.get('CommercialOfferDrug_id') > 0) {
                        data.push(record.data);
                    }
                });
                return data;
            },
            getJSONSelectedData: function(){ //возвращает выбранные остатки в виде закодированной JSON строки
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
        sw.Promed.swWhsDocumentCommercialOfferDrugAddWindow.superclass.initComponent.apply(this, arguments);
    }
});