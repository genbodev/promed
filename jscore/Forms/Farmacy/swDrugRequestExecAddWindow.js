/**
* swDrugRequestExecAddWindow - окно добавления информации о исполнении сводной заявки
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Farmacy
* @access       public
* @copyright    Copyright (c) 2012 Swan Ltd.
* @author       Salakhov R.
* @version      02.2016
* @comment      
*/
sw.Promed.swDrugRequestExecAddWindow = Ext.extend(sw.Promed.BaseForm, {
    autoHeight: false,
    title: 'Исполнение сводной заявки: выбор остатков',
    layout: 'border',
    id: 'DrugRequestExecAddWindow',
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

        //params.limit = 100;
        //params.start =  0;

        //params.Org_id = getGlobalOptions().org_id;
        params.DrugRequest_id = this.DrugRequest_id;

        wnd.SearchGrid.removeAll();
        wnd.SearchGrid.loadData({
            globalFilters: params,
            callback: function() {}
        });

        document.getElementById('drea_checkAll_checkbox').checked = false;
    },
    doSave:  function() {
        var wnd = this;

        if (wnd.SearchGrid.getSelectedData().length < 1) {
            sw.swMsg.show( {
                buttons: Ext.Msg.OK,
                fn: function() {},
                icon: Ext.Msg.WARNING,
                msg: 'Для создания сводной заявки необоходимо выбрать хотя бы одну заявку ЛЛО',
                title: 'Ошибка'
            });
            return false;
        }

        Ext.Ajax.request({
            url: '/?c=MzDrugRequest&m=saveDrugRequestExecFromJSON',
            params: {
                DrugRequest_id: wnd.DrugRequest_id,
                json_str: wnd.SearchGrid.getJSONSelectedData()
            },
            success: function(response){
                var result = Ext.util.JSON.decode(response.responseText);

                if (result && result.success) {
                    if (typeof wnd.callback == 'function' ) {
                        wnd.callback(wnd.owner);
                    }
                    wnd.hide();
                }
            }
        });

        return true;
    },
    show: function() {
        var wnd = this;
        sw.Promed.swDrugRequestExecAddWindow.superclass.show.apply(this, arguments);
        this.action = 'add';
        this.callback = Ext.emptyFn;
        this.DrugRequest_id = null;

        if ( !arguments[0] ) {
            sw.swMsg.alert('Ошибка', 'Не указаны входные данные', function() { wnd.hide(); });
            return false;
        }
        if ( arguments[0].callback && typeof arguments[0].callback == 'function' ) {
            this.callback = arguments[0].callback;
        }
        if ( arguments[0].owner ) {
            this.owner = arguments[0].owner;
        }
        if ( arguments[0].DrugRequest_id ) {
            this.DrugRequest_id = arguments[0].DrugRequest_id;
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
            dataUrl: '/?c=MzDrugRequest&m=loadDrugRequestExecSourceList',
            height: 180,
            region: 'center',
            object: null,
            editformclassname: null,
            id: wnd.id + 'Grid',
            paging: false,
            style: 'margin-bottom: 10px',
            stringfields: [
                {name: 'DrugOstatRegistry_id', type: 'int', header: 'ID', key: true},
                {name: 'WhsDocumentSupplySpec_id', hidden: true},
                {name: 'DrugRequestPurchaseSpec_id', hidden: true},
                {name: 'DrugRequestPurchaseSpec_Kolvo', hidden: true},
                {name: 'checked', header: '<input type="checkbox" id="drea_checkAll_checkbox" onClick="getWnd(\'swDrugRequestExecAddWindow\').SearchGrid.checkAll(this.checked);">', width: 65, renderer: sw.Promed.Format.checkColumn},
                {name: 'WhsDocumentSupply_Year', type: 'string', header: 'Год', width: 50},
                {name: 'WhsDocumentSupply_Name', type: 'string', header: 'Контракт', width: 200},
                {name: 'SubAccountType_Name', type: 'string', header: 'Субсчет', width: 100},
                {name: 'Drug_Name', type: 'string', header: 'Торговое наименование', width: 100, id: 'autoexpand'},
                {name: 'DrugOstatRegistry_Kolvo', type: 'string', header: 'Остаток', width: 100},
                {name: 'DrugOstatRegistry_Cost', type: 'string', header: 'Цена', width: 100},
                {name: 'Kolvo', type: 'string', header: 'Кол-во', width: 100}
            ],
            title: 'Медикаменты, доступные для включения в разнарядку',
            toolbar: true,
            onLoadData: function() {
                document.getElementById('drea_checkAll_checkbox').checked = false;
            },
            onDblClick: function(grid) {
                var record = grid.getSelectionModel().getSelected();
                record.set('checked', !record.get('checked'));
                record.commit();
            },
            checkAll: function(checked) {
                if (!this.readOnly) {
                    this.getGrid().getStore().each(function(record){
                        if (record.get('DrugOstatRegistry_id') > 0) {
                            record.set('checked', checked);
                            record.commit();
                        }
                    });
                }
            },
            getSelectedData: function(){ //возвращает выбранные остатки
                var data = new Array();
                this.getGrid().getStore().each(function(record) {
                    if (record.get('checked')) {
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
                        this.ownerCt.doSave();
                    },
                    iconCls: 'save16',
                    text: BTN_FRMSAVE
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
        sw.Promed.swDrugRequestExecAddWindow.superclass.initComponent.apply(this, arguments);
    }
});