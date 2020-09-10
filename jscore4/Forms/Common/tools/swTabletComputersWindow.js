/* 
 Форма Планшетные компьютеры
 */


Ext.define('sw.tools.swTabletComputersWindow', {
    alias: 'widget.swTabletComputersWindow',
    extend: 'Ext.window.Window',
    title: 'Планшетные компьютеры',
    width: 800,
    height: 400,
    resizable: false,
    modal: true,
    layout: {
        align: 'stretch',
        type: 'vbox'
    },
    initComponent: function () {
        var me = this;

        var tabletComputersWindowGrid = Ext.create('sw.lib.MongoGrid', {
            flex: 1,
            autoScroll: true,
            stripeRows: true,
            refId: 'tabletComputersWindowGrid',
            tbar: [
                {
                    xtype: 'button',
                    itemId: 'addTabletComputersWindowButton',
                    text: 'Добавить',
                    iconCls: 'add16',
                    handler: function () {
                        Ext.create('sw.tools.subtools.swTabletComputersWindowAddEdit', {action: 'add'}).show()
                    }
                },
                {
                    xtype: 'button',
                    disabled: true,
                    itemId: 'editTabletComputersWindowButton',
                    text: 'Изменить',
                    iconCls: 'edit16',
                    handler: function () {
                        if (tabletComputersWindowGrid.getSelectionModel().getSelection())
                            var CMPTabletPC_id = tabletComputersWindowGrid.getSelectionModel().getSelection()[0].get('CMPTabletPC_id')
                        Ext.create('sw.tools.subtools.swTabletComputersWindowAddEdit', {
                            action: 'edit',
                            CMPTabletPC_id: CMPTabletPC_id
                        }).show()
                    }
                },
                {
                    xtype: 'button',
                    disabled: true,
                    itemId: 'viewTabletComputersWindowButton',
                    text: 'Просмотр',
                    iconCls: 'search16',
                    handler: function () {
                        if (tabletComputersWindowGrid.getSelectionModel().getSelection())
                            var CMPTabletPC_id = tabletComputersWindowGrid.getSelectionModel().getSelection()[0].get('CMPTabletPC_id')
                        Ext.create('sw.tools.subtools.swTabletComputersWindowAddEdit', {
                            action: 'view',
                            CMPTabletPC_id: CMPTabletPC_id
                        }).show()
                    }
                },
                {
                    xtype: 'button',
                    disabled: true,
                    itemId: 'deleteTabletComputersWindowButton',
                    text: 'Удалить',
                    iconCls: 'delete16',
                    handler: function () {
                        Ext.Msg.show({
                            title: 'Удаление планшета',
                            msg: 'Удалить планшетный компьютер?',
                            buttons: Ext.Msg.YESNO,
                            icon: Ext.Msg.WARNING,
                            fn: function (btn) {
                                if (btn == 'yes') {
                                    var opts = getGlobalOptions();
                                    Ext.Ajax.request({
                                        url: '/?c=TabletComputers&m=deleteTabletComputer',
                                        params: {
                                            CMPTabletPC_id: tabletComputersWindowGrid.getSelectionModel().getSelection()[0].get('CMPTabletPC_id')
                                        },
                                        callback: function (opt, success, response) {
                                            if (success) {
                                                var res = Ext.JSON.decode(response.responseText);
                                                if (res.success == false) {
                                                    Ext.Msg.alert('Ошибка', res.Error_Msg);
                                                }
                                                else {
                                                    var rec = tabletComputersWindowGrid.getSelectionModel().getSelection()[0];
                                                    tabletComputersWindowGrid.store.remove(rec);
                                                    me.down('toolbar button[itemId=editTabletComputersWindowButton]').disable();
                                                    me.down('toolbar button[itemId=viewTabletComputersWindowButton]').disable();
                                                    me.down('toolbar button[itemId=deleteTabletComputersWindowButton]').disable();
                                                }
                                            }
                                        }
                                    })
                                }
                            }
                        })
                    }
                }
            ],
            store: new Ext.data.JsonStore({
                autoLoad: true,
                numLoad: 0,
                storeId: 'tabletComputersWindowGridGridStore',
                fields: [
                    {name: 'CMPTabletPC_id', type: 'int'},
                    {name: 'LpuBuilding_id', type: 'int'},
                    {name: 'CMPTabletPC_Code', type: 'string'},
                    {name: 'CMPTabletPC_Name', type: 'string'},
                    {name: 'CMPTabletPC_SIM', type: 'string'},
                    {name: 'LpuBuilding_Name', type: 'string'}
                ],
                proxy: {
                    limitParam: undefined,
                    startParam: undefined,
                    paramName: undefined,
                    pageParam: undefined,
                    type: 'ajax',
                    url: '/?c=TabletComputers&m=loadTabletComputersList',
                    reader: {
                        type: 'json',
                        successProperty: 'success',
                        root: 'data'
                    },
                    actionMethods: {
                        create: 'POST',
                        read: 'POST',
                        update: 'POST',
                        destroy: 'POST'
                    }
                },
                listeners: {
                    datachanged: function () {
                    }
                }
            }),
            columns: [
                {dataIndex: 'CMPTabletPC_id', text: 'ID', key: true, hidden: true, hideable: false},
                {dataIndex: 'LpuBuilding_id',  hidden: true},
                {dataIndex: 'CMPTabletPC_Code', text: 'Код', width: 120},
                {dataIndex: 'CMPTabletPC_Name', text: 'Наименование', flex: 1},
                {dataIndex: 'CMPTabletPC_SIM', text: 'Номер SIM-карты', flex: 1},
                {dataIndex: 'LpuBuilding_Name', text: 'Подстанция', flex: 1}
            ],
            listeners: {
                beforecellclick: function (cmp, td, cellIndex, record, tr, rowIndex, e, eOpts) {
                    me.down('toolbar button[itemId=editTabletComputersWindowButton]').enable()
                    me.down('toolbar button[itemId=viewTabletComputersWindowButton]').enable()
                    me.down('toolbar button[itemId=deleteTabletComputersWindowButton]').enable()
                }
            }
        })


        Ext.applyIf(me, {
            items: [
                tabletComputersWindowGrid
            ],
            dockedItems: [
                {
                    xtype: 'container',
                    dock: 'bottom',

                    layout: {
                        align: 'right',
                        type: 'vbox',
                        padding: 3
                    },
                    items: [
                        {
                            xtype: 'container',
                            height: 30,
                            layout: {
                                align: 'middle',
                                pack: 'center',
                                type: 'hbox'
                            },
                            items: [
                                {
                                    xtype: 'button',
                                    iconCls: 'cancel16',
                                    text: 'Закрыть',
                                    margin: '0 5',
                                    handler: function () {
                                        me.close()
                                    }
                                },
                                {
                                    xtype: 'button',
                                    text: 'Помощь',
                                    iconCls: 'help16',
                                    handler: function () {
                                        ShowHelp(me.title);
                                    }
                                }
                            ]
                        }
                    ]
                }
            ]
        })

        me.callParent()
    }
})

