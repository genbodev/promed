/* 
 Форма Результат обслуживания НМП
 */


Ext.define('sw.tools.swSelectNmpReasonWindow', {
    alias: 'widget.swSelectNmpReasonWindow',
    extend: 'Ext.window.Window',
    title: 'Выберите результат',
    width: 1000,
    height: 400,
    resizable: true,
    modal: true,
    layout: {
        align: 'stretch',
        type: 'vbox'
    },
    initComponent: function () {
        var me = this;

        me.addEvents({
            saveResult: true
        });

        var cellEditing = Ext.create('Ext.grid.plugin.CellEditing', {
            clicksToEdit: 1
        });

        var ppdResultCombo = Ext.create('swCmpPPDResultCombo');

        me.grid = Ext.create('Ext.grid.Panel', {
            plugins: cellEditing,
            store: new Ext.data.Store({
                extend: 'Ext.data.Store',
                autoLoad: false,
                storeId: 'selectNmpReasonStore',
                fields: [
                    {
                        name: 'CmpCallCard_id',
                        type: 'int'
                    },
                    {
                        name: 'CmpCallCard_Numv',
                        type: 'string'
                    },
                    {
                        name: 'CmpCallCard_Ngod',
                        type: 'string'
                    },
                    {
                        name: 'EmergencyTeam_Num',
                        type: 'string'
                    },
                    {
                        name: 'Person_FIO',
                        type: 'string'
                    },
                    {
                        name: 'Person_Age',
                        type: 'string'
                    },
                    {
                        name: 'CmpCallType_Name',
                        type: 'string'
                    },
                    {
                        name: 'CmpReason_Name',
                        type: 'string'
                    },
                    {
                        name: 'CmpCallCard_Address',
                        type: 'string'
                    },
                    {
                        name: 'CmpPPDResult_Name',
                        type: 'string'
                    },
                    {
                        name: 'EmergencyTeam_id',
                        type: 'int'
                    }
                ],
                proxy: {
                    limitParam: 100,
                    startParam: undefined,
                    paramName: undefined,
                    pageParam: undefined,
                    type: 'ajax',
                    url: '/?c=CmpCallCard4E&m=loadSelectNmpReasonWindow',
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
                {dataIndex: 'CmpCallCard_id', text: 'id вызова', hidden: true},
                {dataIndex: 'CmpCallCard_Numv',sortable: true, text: '№ В/Г', width: 60},
                {dataIndex: 'CmpCallCard_Ngod',sortable: true, text: '№ В/Д', width: 60},
                {dataIndex: 'EmergencyTeam_Num',sortable: true, text: 'БР', width: 60},
                {dataIndex: 'Person_FIO',sortable: true, text: 'Пациент', width: 150},
                {dataIndex: 'Person_Age',sortable: true, text: 'Возраст', width: 60},
                {dataIndex: 'CmpCallType_Name',type: 'date', text: 'Тип вызова', width: 100},
                {dataIndex: 'CmpReason_Name',type: 'date', text: 'Повод', width: 160},
                {dataIndex: 'CmpCallCard_Address', text: 'Адрес', width: 200},
                {dataIndex: 'CmpPPDResult_id', hidden: (me.operation != 'ppdResult'), sortable: false, text: 'Результат обслуж. НМП', minWidth: 140, flex: 1, editor: ppdResultCombo,
                    renderer:function() {
                        return ppdResultCombo.getRawValue();
                    }
                },
                {
                    dataIndex: 'resetTeam', hidden: (me.operation != 'resetTeam'), text: 'Отклонить', xtype: 'checkcolumn', sortable: false, minWidth: 140, flex: 1
                }

            ]

        });

        me.on('show', function() {
            me.CmpCallCard_id = arguments[0].CmpCallCard_id;
            me.operation = arguments[0].operation;

            switch( me.operation){
                case 'ppdResult' :  { me.setTitle('Результат обслуживания НМП'); break; }
                case 'resetTeam' : { me.setTitle('Выбор вызовов для отклонения'); break; }
            }

            me.grid.getStore().load({
                params: {CmpCallCard_id: this.CmpCallCard_id}
            })
        });


        Ext.applyIf(me, {
            items: [
                me.grid
            ],
            dockedItems: [
                {
                    xtype: 'container',
                    dock: 'bottom',

                    layout: {
                        align: 'stretch',
                        type: 'vbox',
                        padding: 3
                    },
                    items: [
                        {
                            xtype: 'container',
                            height: 30,
                            layout: {
                                type: 'hbox'
                            },
                            items: [
                                {
                                    xtype: 'button',
                                    iconCls: 'ok16',
                                    text: 'Выбрать',
                                    margin: '0 5',
                                    handler: function () {
                                        me.saveResult();
                                    }
                                },
                                {
                                    xtype: 'tbfill'
                                },
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
    },

    saveResult: function() {
        var me = this,
            data = [],
            errMsg = '';

        var loadMask = new Ext.LoadMask(me,{msg:"Сохранение..."});
        loadMask.show();

        switch(me.operation){
            case 'ppdResult':

                me.grid.getStore().each(function(rec){
                    if(!Ext.isEmpty(rec.get('CmpPPDResult_id'))){
                        data.push({
                            CmpCallCard_id: rec.get('CmpCallCard_id'),
                            EmergencyTeam_id: rec.get('EmergencyTeam_id'),
                            CmpPPDResult_id: rec.get('CmpPPDResult_id')
                        })
                    }else{
                        errMsg = 'Заполните результат обслуживания для каждого вызова';
                    }
                });

                if(errMsg.length > 0){
                    Ext.Msg.alert('Ошибка', errMsg);
                    loadMask.hide();
                    return false;
                }

                Ext.Ajax.request({
                    url: '/?c=CmpCallCard4E&m=setResultCmpCallCards',
                    params: {
                        calls: Ext.JSON.encode(data)
                    },
                    success: function (response, opts) {

                        me.fireEvent('saveResult', me, me.grid.getStore());
                        loadMask.hide();
                        me.close();
                    }
                });

                break;
            case 'resetTeam':

                me.grid.getStore().each(function (rec) {
                    data.push({
                        CmpCallCard_id: rec.get('CmpCallCard_id'),
                        EmergencyTeam_id: rec.get('EmergencyTeam_id'),
                        reset: rec.get('resetTeam') ? rec.get('resetTeam') : false
                    })
                });

                Ext.Ajax.request({
                    url: '/?c=CmpCallCard4E&m=cancelEmergencyTeamFromCalls',
                    params: {
                        calls: Ext.JSON.encode(data)
                    },
                    success: function (response, opts) {
                        var params = Ext.decode(opts.params.calls),
                            win = Ext.ComponentQuery.query('[id=swDispatcherStationWorkPlace]')[0];

                        if (win.socket) {
                            win.socket.emit('cancelCmpCard', {
                                CmpCallCard_id: params[0].CmpCallCard_id,
                                EmergencyTeam_id: params[0].EmergencyTeam_id
                            }, function(data){
                                log('NODE emit cancelCmpCard : apk='+data);
                            });
                        }
                        me.fireEvent('saveResult', me, me.grid.getStore());

                        loadMask.hide();
                        me.close();
                    }
                });

                break;
        }

    }
})

