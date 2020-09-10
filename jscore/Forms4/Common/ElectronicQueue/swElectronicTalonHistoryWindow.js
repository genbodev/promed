/**
 * swElectronicTalonHistoryWindow - История изменения талона ЭО
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 */

Ext6.define('common.ElectronicQueue.swElectronicTalonHistoryWindow', {
    extend: 'base.BaseForm',
    title: 'История талона электронной очереди',
    layout: 'border',
    width: 600,
    height: 500,
    resizable: true,
    maximizable: false,
    closable: true,
    modal: true,
    header: true,
    constrain: true,
    show: function(data) {

        var wnd = this;
        this.callParent(arguments);

        if (!data || !data.ElectronicTalon_id) {
            Ext6.Msg.alert(langs('Сообщение'), langs('Неверные параметры'), function() {
                wnd.hide();
            });
            return false;
        }

        if (data.callback) wnd.callback = data.callback;
        else wnd.callback = Ext6.emptyFn;

        wnd.ElectronicTalon_id = data.ElectronicTalon_id;
        this.doLoad();
    },
    onRecordSelect: function() {},
    onLoadGrid: function() {},
    doLoad: function() {

        var wnd = this;

        this.Grid.getStore().removeAll();
        this.Grid.getStore().load({
            params: {
                ElectronicTalon_id: wnd.ElectronicTalon_id
            }
        });
    },
    initComponent: function() {

        var wnd = this;

        wnd.Grid = Ext6.create('Ext6.grid.Panel', {
            cls: 'grid-common',
            xtype: 'grid',
            border: false,
            region: 'center',
            selModel: {
                mode: 'SINGLE',
                listeners: {
                    select: function(model, record, index) {
                        wnd.onRecordSelect();
                    }
                }
            },
            dockedItems: [{
                padding: "0 10px",
                xtype: 'toolbar',
                dock: 'top',
                cls: 'grid-toolbar',
                items: [ {
                    xtype: 'button',
                    text: 'Обновить',
                    itemId: 'action_refresh',
                    iconCls: 'action_refresh',
                    handler: function(){
                        wnd.doLoad();
                    }
                }, {
                    xtype: 'button',
                    text: 'Печать',
                    itemId: 'action_print',
                    iconCls: 'action_print',
                    handler: function(){
                        Ext6.ux.GridPrinter.print(wnd.Grid);
                    }
                }]
            }],
            store: {
                fields: [
                    {name: 'ElectronicTalon_id', type: 'int'},
                    {name: 'ElectronicTalon_Num', type: 'string'},
                    {name: 'ElectronicTalonStatus_id', type: 'int'},
                    {name: 'ElectronicTalonStatus_Name]', type: 'string'},
                    {name: 'ElectronicService_id', type: 'int'},
                    {name: 'ElectronicQueueInfo_id', type: 'int'},
                    {name: 'pmUser_insID', type: 'int'},
                    {name: 'PMUser_Name', type: 'string'},
                    {name: 'ElectronicTalonHist_insDT', type: 'date', dateFormat: 'd.m.Y H:i:s'}
                ],
                proxy: {
                    type: 'ajax',
                    actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
                    url: '/?c=ElectronicTalon&m=getElectronicTalonHistory',
                    reader: {
                        type: 'json',
                        rootProperty: 'data'
                    }
                },
                sorters: [
                    'ElectronicTalonHist_insDT'
                ],
                listeners: {
                    load: function(grid, records) {
                        wnd.onLoadGrid();
                    }
                }
            },
            columns: [
                {text: 'Дата записи', width: 150, dataIndex: 'ElectronicTalonHist_insDT', renderer: Ext6.util.Format.dateRenderer('d.m.Y H:i:s')},
                {text: '№ талона', width: 80, dataIndex: 'ElectronicTalon_Num'},
                {text: 'Статус талона', width: 150, dataIndex: 'ElectronicTalonStatus_Name'},
                {text: 'Оператор', width: 150, dataIndex: 'PMUser_Name', flex: 1}
            ]
        });

        Ext6.apply(wnd, {
            items: [
                wnd.Grid
            ],
            buttons: ['->', {
                handler: function () {
                    wnd.hide();
                },
                cls: 'flat-button-primary',
                text: 'Закрыть'
            }]
        });

        this.callParent(arguments);
    }
});