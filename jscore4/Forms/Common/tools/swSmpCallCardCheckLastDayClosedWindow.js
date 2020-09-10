Ext.define('sw.tools.swSmpCallCardCheckLastDayClosedWindow', {
    alias: 'widget.swSmpCallCardCheckLastDayClosedWindow',
    extend: 'Ext.window.Window',
    title: 'Повторный вызов. Выбор первичного вызова',
    closeAction: 'hide',
    width: 1200,
    height: 300,
    minHeight: 150,
    refId: 'SmpCallCardCheckLastDayClosedWindow',
    modal: true,
    layout: {
        align: 'stretch',
        type: 'vbox'
    },
    onEsc: function () {
        var params = {};
        params.typeSave = 'notclear';
        this.close();
    },
    SelectedCmpReason_id: null,
    SelectedCmpReason_Name: '',
    data: [],
    initComponent: function () {
        var win = this;

        win.addEvents({
            selectLastDayClosedCall: true
        });

        this.selectBtn = Ext.create('Ext.button.Button', {
            iconCls: 'ok16',
            text: 'Выбрать',
            handler: function () {
                var params = {};
                var grid = this.SmpCallCardCheckLastDayClosedGrid,
                    selRec = grid.getSelectionModel().getSelection()[0];
                params.CmpCallCard_rid = selRec.raw.CallCard_id;
                params.CmpCallCard_DayNumberRid = selRec.raw.CmpCallCard_Numv;
                params.typeSave = 'double';
                win.fireEvent('selectLastDayClosedCall', true, params, selRec);
            }.bind(this)
        });


        this.cancel2Btn = Ext.create('Ext.button.Button', {
            iconCls: 'cancel16',
            text: 'Отмена',
            handler: function () {
                var params = {};
                if (typeof this.callback == 'function') {
                    this.callback(false, params);
                }
                this.close();
            }.bind(this)
        });

        this.SmpCallCardCheckLastDayClosedGrid = Ext.create('Ext.grid.Panel', {
            width: '100%',
            autoHeight: true,
            refId: 'smpCallCardCheckLastDayClosedGrid',
            flex: 1,
            onEsc: function () {
                this.close();
            },
            autoScroll: true,
            scroll: 'both',
            stripeRows: true,
            store: new Ext.data.JsonStore({
                fields: [
                    {name: 'CmpCallCard_prmDate', type: 'string'},
                    {name: 'CmpCallCard_Ngod', type: 'int'},
                    {name: 'Person_FIO', type: 'string'},
                    {name: 'CmpCallType_Name', type: 'string'},
                    {name: 'CmpReason_Name', type: 'string'},
                    {name: 'Adress_Name', type: 'string'},
                    {name: 'EmergencyTeam_id', type: 'int'},
                    {name: 'CmpCallCard_Tper', type: 'string'},
                    {name: 'CmpCallCard_Numv', type: 'int', hidden: true},
                    {name: 'Person_id', type: 'int', hidden: true},
                    {name: 'PersonEvn_id', type: 'int', hidden: true},
                    {name: 'Person_Surname', type: 'string', hidden: true},
                    {name: 'Person_Firname', type: 'string', hidden: true},
                    {name: 'Person_Secname', type: 'string', hidden: true},
                    {name: 'Person_Birthday', type: 'string', hidden: true},
                    {name: 'CmpReason_id', type: 'int', hidden: true},
                    {name: 'KLRgn_id', type: 'int', hidden: true},
                    {name: 'KLSubRgn_id', type: 'int', hidden: true},
                    {name: 'KLCity_id', type: 'int', hidden: true},
                    {name: 'KLCity_Name', type: 'string', hidden: true},
                    {name: 'KLTown_id', type: 'int', hidden: true},
                    {name: 'KLTown_Name', type: 'string', hidden: true},
                    {name: 'CmpCallCard_id', type: 'int', hidden: true},
                    {name: 'CmpCallCard_rid', type: 'int', hidden: true},
                    {name: 'CmpCallPlaceType_id', type: 'int', hidden: true},
                    {name: 'Server_id', type: 'int', hidden: true},
                    {name: 'Person_Age', type: 'int', hidden: true},
                    {name: 'pmUser_insID', type: 'srting', hidden: true},
                    {name: 'CmpCallCard_isLocked', type: 'int', hidden: true},
                    {name: 'CmpCallCard_Telf', type: 'string', hidden: true},
                    {name: 'CmpGroup_id', type: 'int', hidden: true},
                    {name: 'CmpGroupName_id', type: 'int', hidden: true},
                    {name: 'CmpLpu_Name', type: 'string', hidden: true},
                    {name: 'CmpDiag_Name', type: 'string', hidden: true},
                    {name: 'StacDiag_Name', type: 'string', hidden: true},
                    {name: 'SendLpu_Nick', type: 'string', hidden: true},
                    {name: 'PPDUser_Name', type: 'string', hidden: true},
                    {name: 'ServeDT', type: 'string', hidden: true},
                    {name: 'Sex_id', type: 'int', hidden: true},
                    {name: 'CmpCallCard_Ktov', type: 'string', hidden: true},
                    {name: 'CmpCallerType_id', type: 'int', hidden: true},
                    {name: 'PPDResult', type: 'string', hidden: true},
                    {name: 'KLStreet_id', type: 'int', hidden: true},
                    {name: 'KLStreet_FullName', type: 'string', hidden: true},
                    {name: 'CmpCallCard_Dom', type: 'string', hidden: true},
                    {name: 'CmpCallCard_Kvar', type: 'string', hidden: true},
                    {name: 'CmpCallCard_Comm', type: 'string', hidden: true},
                    {name: 'CmpCallCard_Podz', type: 'string', hidden: true},
                    {name: 'CmpCallCard_Etaj', type: 'string', hidden: true},
                    {name: 'CmpCallCard_Kodp', type: 'string', hidden: true},
                    {name: 'CmpCallCard_Korp', type: 'string', hidden: true},
                    {name: 'lpuLocalCombo', type: 'int', hidden: true},
                    {name: 'LpuBuilding_id', type: 'int', hidden: true},
                    {name: 'UnformalizedAddressDirectory_id', type: 'int', hidden: true},
                    {name: 'UnformalizedAddressType_id', type: 'int', hidden: true},
                    {name: 'UnformalizedAddressDirectory_Dom', type: 'string', hidden: true},
                    {name: 'UnformalizedAddressDirectory_Name', type: 'string', hidden: true},
                    {name: 'StreetAndUnformalizedAddressDirectory_id', type: 'string', hidden: true},
                    {name: 'CmpCallCardStatus_insDT', type: 'string'},
                    {name: 'CmpCallCard_IsDeterior', type: 'int', hidden: true},
                    {name: 'CmpCallCard_IsExtra', type: 'string', hidden: true}
                ],
                data: {}

            }),
            listeners: {
                itemdblclick: function (gridview, record) {

                    var params = {};
                    var grid = this.SmpCallCardCheckLastDayClosedGrid;
                    params.CmpCallCard_rid = record.raw.CallCard_id;
                    params.CmpCallCard_DayNumberRid = record.raw.CmpCallCard_Numv;
                    win.fireEvent('selectLastDayClosedCall', true, params, record);
                },
                keydown: function (cmp, td, cellIndex, record, tr, rowIndex, e, eOpts) {
                    if (e.getKey() == e.ENTER) {
                        win.selectBtn.handler();
                    }
                }
            },
            columns: [
                {
                    dataIndex: 'CmpCallCard_prmDate',
                    renderer: function (value) {
                        value = new Date(value);
                        value = Ext.Date.format(value, 'd.m.Y');
                        return Ext.String.format('<span style="font-size:16px;">{0}</span>', value, value)
                    },
                    header: 'Дата приема', width: 100
                },
                {
                    dataIndex: 'CmpCallCard_prmDate',
                    renderer: function (value) {
                        value = new Date(value);
                        value = Ext.Date.format(value, 'H:i');
                        return Ext.String.format('<span style="font-size:16px;">{0}</span>', value, value)
                    },
                    header: 'Время приема', width: 80
                },
                {
                    dataIndex: 'CmpCallCard_Numv',
                    header: 'Номер за день',
                    width: 90,
                    hidden: !getGlobalOptions().region.nick.inlist(['ufa', 'krym']),
                    renderer: function (value) {
                        return Ext.String.format('<span style="font-size:16px;">{0}</span>', value, value);
                    }
                },
                {
                    dataIndex: 'CmpCallCard_Ngod',
                    header: 'Номер за год',
                    width: 90,
                    hidden: getGlobalOptions().region.nick.inlist(['ufa', 'krym']),
                    renderer: function (value) {
                        return Ext.String.format('<span style="font-size:16px;">{0}</span>', value, value);
                    }
                },
                {
                    dataIndex: 'Person_FIO', header: 'ФИО', width: 250, renderer: function (value) {
                    return Ext.String.format('<span style="font-size:16px;">{0}</span>', value, value);
                }
                },
                {
                    dataIndex: 'CmpCallType_Name', header: 'Тип вызова', width: 120, renderer: function (value) {
                    return Ext.String.format('<span style="font-size:16px;">{0}</span>', value, value);
                }
                },
                {
                    dataIndex: 'CmpReason_Name', header: 'Повод', width: 200, renderer: function (value) {
                    return Ext.String.format('<span style="font-size:16px;">{0}</span>', value, value);
                }
                },
                {
                    dataIndex: 'Adress_Name', header: 'Место', width: 270, renderer: function (value) {
                    return Ext.String.format('<span style="font-size:16px;">{0}</span>', value, value);
                }
                },
                {
                    dataIndex: 'EmergencyTeam_id', header: 'Назначение бригады',
                    width: 120,
                    renderer: function (value) {
                        value = (value) ? 'Да' : 'Нет';
                        return Ext.String.format('<span style="font-size:16px;">{0}</span>', value, value);
                    }
                },
                {
                    dataIndex: 'CmpCallCard_Tper',
                    header: 'Время назначения бригады',
                    width: 150,
                    renderer: function (value) {
                        if (value) {
                            value = new Date(value);
                            value = Ext.Date.format(value, 'H:i');
                        }
                        return Ext.String.format('<span style="font-size:16px;">{0}</span>', value, value);
                    }
                },
                {
                    dataIndex: 'CmpCallCardStatus_insDT', header: 'Принят НМП', width: 80, renderer: function (value) {
                    if (value) {
                        value = new Date(value);
                        value = Ext.Date.format(value, 'H:i');
                    }
                    return Ext.String.format('<span style="font-size:16px;">{0}</span>', value, value);
                }
                }
            ]
        })

        Ext.applyIf(this, {
            layout: {
                type: 'vbox',
                align: 'stretch'
            },
            items: [
                this.SmpCallCardCheckLastDayClosedGrid
            ],
            buttons: [
                this.selectBtn,
                '->',
                this.cancel2Btn
            ]
        });

        this.callParent(arguments);
    },
    show: function (a) {
        this.callParent(arguments);
        this.SmpCallCardCheckLastDayClosedGrid.getStore().loadRawData(arguments[0].closedCards);
        this.SmpCallCardCheckLastDayClosedGrid.getSelectionModel().select(0);
    }
},
    function() {
        /**
         * @singleton
         * Singleton instance of {@link Ext.window.MessageBox}.
         */
        new this();
    });