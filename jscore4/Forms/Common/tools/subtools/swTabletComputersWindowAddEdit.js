/*
 Форма Планшетный компьютер - добавление\редактирование
 */

Ext.define('sw.tools.subtools.swTabletComputersWindowAddEdit', {
    alias: 'widget.swTabletComputersWindowAddEdit',
    extend: 'Ext.window.Window',
    width: 400,
    layout: 'fit',
    resizable: false,
    modal: true,
    initComponent: function () {
        var me = this,
			curArm = sw.Promed.MedStaffFactByUser.current.ARMType || sw.Promed.MedStaffFactByUser.last.ARMType;

        me.height = 230;

		me.isNmpArm = curArm.inlist(['dispnmp','dispcallnmp', 'dispdirnmp']);

        var conf = me.initialConfig,
            mytitle = 'Планшетный компьютер';
        switch (conf.action) {
            case 'add' :
            {
                mytitle += ': Добавление';
                break;
            }
            case 'edit' :
            {
                mytitle += ': Редактирование';
                break;
            }
            case 'view' :
            {
                mytitle += ': Просмотр';
                break;
            }
        }
        me.title = mytitle;

        me.on('show', function () {
            if(conf.CMPTabletPC_id){
                me.loadForm(conf.CMPTabletPC_id)
            }
            me.down('form').isValid();
            if (conf.action == 'view') {
                var bForm = me.down('form'),
                    buttonSave = me.down('button[refId=saveBtn]');
                bForm.getForm().applyToFields({
                    readOnly: true,
                    hideTrigger: true
                });
                buttonSave.disable();
            }
        })

        var BuildingsWorkAccess = [];
        var smpUnits = Ext.create('sw.SmpUnits', {
                name: 'LpuBuilding_id',
                fieldLabel: me.isNmpArm ? 'Подстанция НМП' : 'Подстанция СМП',
                tabIndex: 1,
                labelWidth: 170,
                allowBlank: false,
                labelAlign: 'right',
                readOnly: (me.initialConfig.action == 'view'),
                listeners: {
                    expand: function(){
                        this.getStore().suspendEvent('refresh')
                    }
                }

            }),

            tabletComputersEditFormPanel = Ext.create('sw.BaseForm', {
                refId: 'tabletComputersEditFormPanel',
                frame: true,
                border: false,
                //dock: 'top',
                layout: 'auto',
                items: [
                    {
                        xtype: 'hidden',
                        name: 'CMPTabletPC_id'
                    },
                    smpUnits,
                    {
                        xtype: 'numberfield',
                        fieldLabel: 'Код',
                        labelAlign: 'right',
                        labelWidth: 170,
                        hideTrigger: true,
                        keyNavEnabled: false,
                        mouseWheelEnabled: false,
                        allowBlank: false,
                        name: 'CMPTabletPC_Code',
                        readOnly: (me.initialConfig.action == 'view')
                    }, {
                        xtype: 'transFieldDelbut',
                        fieldLabel: 'Наименование',
                        labelAlign: 'right',
                        labelWidth: 170,
                        translate: false,
                        allowBlank: true,
                        name: 'CMPTabletPC_Name',
                        readOnly: (me.initialConfig.action == 'view')
                    }, {
                        xtype: 'transFieldDelbut',
                        fieldLabel: 'Номер SIM карты',
                        labelAlign: 'right',
                        labelWidth: 170,
                        translate: false,
                        allowBlank: true,
                        name: 'CMPTabletPC_SIM',
                        plugins: [new Ux.InputTextMask('+7(999)999-99-99', false)],
                        validator: function(a){	return (a.match(/_/))?"Введите номер полностью":true;},
                        invalidText: false,
                        readOnly: (me.initialConfig.action == 'view')
                    }
                ]

            });

        Ext.Ajax.request({
            url: '/?c=Options&m=getLpuBuildingsWorkAccess',
            callback: function (opt, success, response) {
                if (success) {
                    var res = Ext.JSON.decode(response.responseText);
                    BuildingsWorkAccess = res.lpuBuildingsWorkAccess;
                    smpUnits.getStore().on('load',function(store){
                        store.filterBy(function(rec,ind){
                            return rec.get('LpuBuilding_id').inlist(BuildingsWorkAccess);
                        });
                        if(conf.action == 'add' && store.getCount() == 1){
                            smpUnits.setValue(store.getAt(0))
                        }
                    })

                }
            }.bind(this)
        })
        Ext.applyIf(me, {
            items: [
                tabletComputersEditFormPanel
            ]
            ,
            dockedItems: [
                {
                    xtype: 'container',
                    dock: 'bottom',

                    layout: 'fit',
                    items: [
                        {
                            xtype: 'container',
                            dock: 'bottom',
                            refId: 'bottomButtons',
                            margin: '5 4',
                            layout: {
                                align: 'top',
                                pack: 'end',
                                type: 'hbox'
                            },
                            items: [
                                {
                                    xtype: 'container',
                                    flex: 1,
                                    items: [
                                        //leftButtons
                                        {
                                            xtype: 'button',
                                            refId: 'saveBtn',
                                            iconCls: 'save16',
                                            text: 'Сохранить',
                                            handler: function () {
                                                me.saveTabletComputer()
                                            }
                                        }
                                    ]
                                },
                                {
                                    xtype: 'container',
                                    layout: {
                                        type: 'hbox',
                                        align: 'middle'
                                    },
                                    items: [
                                        //rightButtons
                                        {
                                            xtype: 'button',
                                            refId: 'helpBtn',
                                            text: 'Помощь',
                                            iconCls: 'help16',
                                            handler: function () {
                                                ShowHelp(me.title);
                                            }
                                        },
                                        {
                                            xtype: 'button',
                                            refId: 'cancelBtn',
                                            iconCls: 'cancel16',
                                            text: 'Закрыть',
                                            margin: '0 5',
                                            handler: function () {
                                                this.up('window').close()
                                            }
                                        }
                                    ]
                                }

                            ]
                        }
                    ]
                }
            ]
        })

        me.callParent(arguments)
    },

    loadForm: function (id) {
        var cmp = this;

        Ext.Ajax.request({
            url: '/?c=TabletComputers&m=loadTabletComputer',
            params: {CMPTabletPC_id: id},
            callback: function (opt, success, response) {
                if (success) {
                    var res = Ext.JSON.decode(response.responseText)[0],
                        frm = this.down('form').getForm();
                    frm.setValues(res);
                }
            }.bind(this)
        })
    },

    saveTabletComputer: function () {

        if (!this.down('form').getForm().isValid()) {
            Ext.Msg.alert('Проверка данных формы', 'Не все поля формы заполнены.<br>Незаполненные поля выделены особо.');
        }
        else {
            var conf = {},
                form = this.down('form');
            conf = this.down('form').getForm().getValues();

            Ext.Ajax.request({
                url: '/?c=TabletComputers&m=saveTabletComputer',
                params: conf,
                callback: function (opt, success, response) {
                    if (success) {
                        if(response.responseText) {
                            var res = Ext.JSON.decode(response.responseText);
                            if(res.success == true){
                                var grid = Ext.ComponentQuery.query('swTabletComputersWindow grid[refId=tabletComputersWindowGrid]')[0];
                                if (res.CMPTabletPC_id && grid) {
                                    grid.store.reload({
                                        callback: function (records, operation, success) {
                                            var rec = grid.store.findRecord('CMPTabletPC_id', res.CMPTabletPC_id);
                                            if (rec) {
                                                grid.getView().select(rec);
                                                grid.down('toolbar button[itemId=editTabletComputersWindowButton]').enable()
                                                grid.down('toolbar button[itemId=viewTabletComputersWindowButton]').enable()
                                                grid.down('toolbar button[itemId=deleteTabletComputersWindowButton]').enable()
                                                form.up('window').close();
                                            }
                                        }
                                    })

                                }
                            }else{
                                Ext.Msg.alert('Ошибка', res.Error_Msg);
                            }

                        }
                    }
                }
            });

        }
    }
})

