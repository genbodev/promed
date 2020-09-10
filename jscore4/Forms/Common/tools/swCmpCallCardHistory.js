/*История вызова*/
Ext.define('sw.tools.swCmpCallCardHistory', {
    alias: 'widget.swCmpCallCardHistory',
    extend: 'Ext.window.Window',
    title: 'История вызова',
    width: 500,
    height: 300,
    layout: 'fit',
    modal: true,

    initComponent: function () {

        var me = this;
        me.on('show', function () {
            var config = arguments[0],
                grid = me.down('gridpanel[refId=cmpCallCardHistoryGrid]');

            if(Ext.isEmpty(config.card_id)) return;

            grid.getStore().load({
                params: {CmpCallCard_id: config.card_id}
            })
        });

        Ext.applyIf(me, {
            items: [
                {
                    xtype: 'gridpanel',
                    flex: 1,
                    refId: 'cmpCallCardHistoryGrid',
                    viewConfig: {
                        loadMask: false,
                    },
                    store: new Ext.data.Store({
                        extend: 'Ext.data.Store',
                        autoLoad: false,
                        storeId: 'cmpCallCardHistoryStore',
                        fields: [
                            {
                                name: 'EventDT',
                                type: 'date'
                            },
                            {
                                name: 'CmpCallCardEventType_Name',
                                type: 'string'
                            },
                            {
                                name: 'pmUser_FIO',
                                type: 'string'
                            },
                            {
                                name: 'EventValue',
                                type: 'string'
                            }
                        ],
                        sorters: [
                            {
                                direction: 'DESC',
                                property: 'EventDT',
                                //transform: function(val){
                                  //  return Ext.Date.parse(val,"d.m.Y H:i:s")
                                //}
                            }
                        ],
                        proxy: {
                            limitParam: 100,
                            startParam: undefined,
                            paramName: undefined,
                            pageParam: undefined,
                            type: 'ajax',
                            url: '/?c=CmpCallCard4E&m=loadCmpCallCardEventHistory',
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
                        }
                    }),

                    columns: [
                        {
                            dataIndex: 'EventDT',
                            text: 'Дата и время',
                            width: 140,
                            xtype: 'datecolumn',
                            format: 'd.m.Y H:i:s'
                        },
                        {
                            dataIndex: 'CmpCallCardEventType_Name',
                            text: 'Событие',
                            flex: 1
                        },
                        {
                            dataIndex: 'pmUser_FIO',
                            text: 'ФИО',
                            flex: 1
                        },
                        {
                            dataIndex: 'EventValue',
                            text: 'Значение события',
                            flex: 1
                        }
                    ]
                }

            ],
            dockedItems: [{
                xtype: 'toolbar',
                dock: 'bottom',
                items: [
                    '->',
                    {
                        xtype: 'button',
                        refId: 'cancelBtn',
                        iconCls: 'cancel16',
                        text: 'Закрыть',
                        margin: '0 5',
                        handler: function () {
                            this.up('window').close()
                        }
                    }]
            }]
        });

        me.callParent(arguments);

    }
})