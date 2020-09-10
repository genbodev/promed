/**
 * Панель суммарного сердечно-сосудистого риска
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 */
Ext6.define('common.EMK.SignalInfo.PersonCardioRiskCalcPanel', {
    extend: 'swPanel',
    title: 'СУММАРНЫЙ СЕРДЕЧНО-СОСУДИСТЫЙ РИСК',
    btnAddClickEnable: false,
    refId: 'PersonCardioRiskCalcPanel',
    //plusMenu: false,
    onBtnAddClick: function(){
        if(this.plusMenu)
            this.plusMenu.showBy(this);
        if (this.plusMenu.hidden == false)
            this.btnAddClick.setStyle('visibility','visible');
    },
    allTimeExpandable: false,
    collapseOnOnlyTitle: true,
    collapsed: true,
    setParams: function(params) {
        var me = this;

        me.Person_id = params.Person_id;
        me.Server_id = params.Server_id;
        me.Person_Birthday = params.Person_Birthday;

        me.loaded = false;

        if (!me.collapsed) {
            me.load();
        }
        me.setAddMenuVisibility();
    },
    setAddMenuVisibility: function(visible){
        var me = this;

        var person_age = swGetPersonAge(me.Person_Birthday, new Date()),
            enabled = (person_age && person_age >= 40 && person_age <= 60);
        me.plusMenu.down('[refId=cardioRiskAddBtn]').setDisabled(!enabled);

    },
    loaded: false,
    listeners: {
        'expand': function() {
            if (!this.loaded) {
                this.load();
            }
        }
    },
    load: function() {
        var me = this;
        this.loaded = true;
        this.CardioRiskCalcGrid.getStore().load({
            params: {
                Person_id: me.Person_id
            }
        });
    },
    deleteCardioRiskCalc: function() {
        var me = this;

        var CardioRiskCalc_id = me.CardioRiskCalcGrid.recordMenu.CardioRiskCalc_id;
        if (CardioRiskCalc_id) {
            checkDeleteRecord({
                callback: function () {
                    me.mask('Удаление записи...');
                    Ext6.Ajax.request({
                        url: '/?c=CardioRiskCalc&m=deleteCardioRiskCalc',
                        params: {
                            CardioRiskCalc_id: CardioRiskCalc_id
                        },
                        callback: function () {
                            me.unmask();
                            me.load();
                        }
                    })
                }
            });
        }
    },
    openCardioRiskCalcEditWindow: function(action) {
        var me = this;
        var formParams = new Object();

        formParams.Person_id = me.Person_id;
        formParams.Server_id = me.Server_id;

        if ( action == 'add' ) {
            formParams.CardioRiskCalc_id = 0;
        } else {
            var CardioRiskCalc_id = me.CardioRiskCalcGrid.recordMenu.CardioRiskCalc_id;
            if (!CardioRiskCalc_id) {
                return false;
            }

            formParams.CardioRiskCalc_id = CardioRiskCalc_id;
        }

        getWnd('swCardioRiskCalcEditWindow').show({
            action: action,
            CardioRiskCalc_id: formParams.CardioRiskCalc_id,
            callback: function(data) {

                me.load();
            }.createDelegate(this),
            formParams: formParams
        });
    },
    initComponent: function() {
        var me = this;

        me.plusMenu = Ext6.create('Ext6.menu.Menu', {
            userCls: 'menuWithoutIcons',
            items: [
                {
                    text: 'Печать',
                    refId: 'cardioRiskPrintBtn',
                    handler: function () {
                        Ext6.ux.GridPrinter.print(me.CardioRiskCalcGrid);
                    }
                },
                {
                    text: 'Добавить',
                    refId: 'cardioRiskAddBtn',
                    disabled: true,
                    handler: function() {
                        me.openCardioRiskCalcEditWindow('add');
                    }
                }
            ]
        });

        this.CardioRiskCalcGrid = Ext6.create('Ext6.grid.Panel', {
            border: false,
            minHeight: 33,
            recordMenu: Ext6.create('Ext6.menu.Menu', {
                items: [{
                    text: 'Редактировать',
                    handler: function() {
                        me.openCardioRiskCalcEditWindow('edit');
                    }
                }, {
                    text: 'Удалить запись',
                    handler: function() {
                        me.deleteCardioRiskCalc();
                    }
                }]
            }),
            showRecordMenu: function(el, CardioRiskCalc_id) {
                this.recordMenu.CardioRiskCalc_id = CardioRiskCalc_id;
                this.recordMenu.showBy(el);
            },
            //userCls: 'blood-group-type',
            columns: [{
                flex: 1,
                minWidth: 100,
                //tdCls: 'padLeft20',
                header: 'Дата измерения',
                dataIndex: 'CardioRiskCalc_setDT'
            }, {
                flex: 1,
                minWidth: 100,
                //tdCls: 'padLeft20',
                header: 'Систолическое АД (мм рт.ст.)',
                dataIndex: 'CardioRiskCalc_SistolPress'
            }, {
                flex: 1,
                minWidth: 100,
                //tdCls: 'padLeft20',
                header: 'Общий холестерин (ммоль/л)',
                dataIndex: 'CardioRiskCalc_Chol',
                renderer: function (value) {
                    return  value.toString();
                }
            }, {
                flex: 1,
                minWidth: 100,
                //tdCls: 'padLeft20',
                header: 'Курение',
                dataIndex: 'CardioRiskCalc_IsSmoke',
                renderer: function (value, metaData, record) {
                    return record.get('CardioRiskCalc_IsSmoke') == 2 ? "Да" : "Нет";
                }
            }, {
                flex: 1,
                minWidth: 100,
                //tdCls: 'padLeft20',
                header: 'Процент (%)',
                dataIndex: 'CardioRiskCalc_Percent'
            }, {
                flex: 1,
                minWidth: 100,
                //tdCls: 'padLeft20',
                header: 'Тип риска',
                dataIndex: 'RiskType_Name'
            }, {
                width: 40,
                dataIndex: 'CardioRiskCalc_Action',
                renderer: function (value, metaData, record) {
                    return "<div class='x6-tool-plusmenu'></div>";
                }
            }, {
                width: 40,
                dataIndex: 'CardioRiskCalc_Action',
                renderer: function (value, metaData, record) {
                    return "<div class='x6-tool-threedots' onclick='Ext6.getCmp(\"" + me.CardioRiskCalcGrid.id + "\").showRecordMenu(this, " + record.get('CardioRiskCalc_id') + ");'></div>";
                }
            }],
            disableSelection: true,
            store: Ext6.create('Ext6.data.Store', {
                fields: [
                    { name: 'CardioRiskCalc_id' },
                    { name: 'CardioRiskCalc_setDate' },
                    { name: 'CardioRiskCalc_SistolPress' },
                    { name: 'CardioRiskCalc_Chol', type: 'float'},
                    { name: 'CardioRiskCalc_IsSmoke' },
                    { name: 'CardioRiskCalc_Percent' },
                    { name: 'RiskType_id' },
                    { name: 'RiskType_Name' }
                ],
                listeners: {
                    'load': function(store, records) {
                        me.setTitleCounter(records.length);
                    }
                },
                proxy: {
                    type: 'ajax',
                    actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
                    url: '/?c=CardioRiskCalc&m=loadCardioRiskCalcPanel',
                    reader: {
                        type: 'json',
                        rootProperty: 'data',
                        totalProperty: 'totalCount'
                    }
                },
                sorters: [
                    'CardioRiskCalc_id'
                ]
            })
        });

        Ext6.apply(this, {
            items: [
                this.CardioRiskCalcGrid
            ]
        });

        this.callParent(arguments);
    }
});