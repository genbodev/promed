/**
 * swEMDSearchWindow - Форма назначения ролей для места работы
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2018 Swan Ltd.
 */

Ext6.define('emd.swEMDMedStaffFactRoleWindow', {
    extend: 'base.BaseForm',
    alias: 'widget.swEMDMedStaffFactRoleWindow',
    autoShow: false,
    maximized: false,
    width: 750,
    height: 410,
    cls: 'arm-window-new emd-search',
    title: 'Роли при подписании',
    constrain: true,
    header: true,
    resizable: false,
    maximizable: false,
    findWindow: false,
    closable: true,
    modal: true,
    msfRolesOriginal: [],
    show: function(data) {

        var wnd = this;
        wnd.callParent(arguments);

        wnd.msfRolesOriginal = [];

        if (data) {

            // присваиваем все пришедшие переменные окну
            Object.keys(data).forEach(function(obj){
                wnd[obj] = data[obj];
            });

        } else data = {};

        log('data', data);

        if (data.callback) wnd.callback = data.callback;
        else wnd.callback = Ext6.emptyFn;

        wnd.rolesPanel.getForm().reset();
        wnd.rolesPanel.getForm().setValues(data);
        wnd.clearComponents();

        var combo = Ext6.ComponentQuery.query('swEMDPersonRole',  wnd.rolesPanel);
        if (combo.length) {
            combo[0].getStore().load();
        }

        wnd.loadRoles();
    },
    clearComponents: function() {

        var wnd = this;
        var panels = Ext6.ComponentQuery.query('fieldset panel', wnd.rolesPanel);
        if (panels.length > 1) {
            panels.forEach(function(component, i){
                if (i!=0) {
                    Ext6.defer(component.destroy, 0, component);
                }
            });
        }
    },
    doSave: function() {

        var wnd = this;
        var form = wnd.rolesPanel.getForm();

        if ( !form.isValid() ) {
            Ext6.Msg.show({
                buttons: Ext6.Msg.OK,
                fn: function() {
                    me.formPanel.getFirstInvalidEl().focus(false);
                },
                icon: Ext6.Msg.WARNING,
                msg: ERR_INVFIELDS_MSG,
                title: ERR_INVFIELDS_TIT
            });

            return false;
        }

        var comboList = Ext6.ComponentQuery.query('swEMDPersonRole', wnd.rolesPanel);

        if (comboList.length) {

            if (comboList[0].getValue() === null) {
                Ext6.Msg.show({
                    buttons: Ext6.Msg.OK,
                    fn: function() {
                        combo[0].focus(false);
                    },
                    icon: Ext6.Msg.WARNING,
                    msg: 'Не выбран ни одна роль',
                    title: ERR_INVFIELDS_TIT
                });

                return false;
            }
        }

        var msf_roles = [],
            msfRolesChanged = [],
            panels = Ext6.ComponentQuery.query('fieldset panel', wnd.rolesPanel);

        var combo = Ext6.ComponentQuery.query('swEMDPersonRole', wnd.rolesPanel),
            checkbox = Ext6.ComponentQuery.query('checkbox', wnd.rolesPanel),
            period = Ext6.ComponentQuery.query('swDateRangeField', wnd.rolesPanel),
            msf_role = Ext6.ComponentQuery.query('hidden[cls=EMDMedStaffFactRole]', wnd.rolesPanel);

        if (panels.length) {
            panels.forEach(function(panel, i){

                var item = {};
                item.EMDMedStaffFactRole_id = msf_role[i].getValue();
                msfRolesChanged.push(item.EMDMedStaffFactRole_id);

                if (!Ext.isEmpty(combo[i].getValue())) {

                    item.EMDPersonRole_id = combo[i].getValue();
                    item.checked = checkbox[i].getValue();
                    item.period = period[i].getValue();

                    msf_roles.push(item);
                }
            });
        }

        if (wnd.msfRolesOriginal.length) {

            var deletedRoles = Ext6.Array.difference(wnd.msfRolesOriginal, msfRolesChanged);
            log('diff', deletedRoles);

            if (deletedRoles.length) {
                deletedRoles.forEach(function(role_id){
                    msf_roles.push({EMDMedStaffFactRole_id: role_id, isDeleted: true});
                })
            }
        }

        wnd.mask('Сохранение ролей...');
        form.submit({
            url: '/?c=EMD&m=saveEMDMedStaffFactRoles',
            params: {
               RolesList: Ext.util.JSON.encode(msf_roles)
            },
            failure: function (form, action) {
                wnd.unmask();
                sw.swMsg.alert(langs('Ошибка'), langs('Во время сохранения произошла ошибка.'));
            },
            success: function (form, action) {
                wnd.unmask();
                wnd.callback();
                wnd.hide();
            }
        });
    },
    doLoad: function(params) { this.doSearch(); },
    // прамис для любого аякс запроса
    ajaxRequestPromise: function(url, ajax_params) {
        return new Promise(function(resolve, reject) {
            Ext6.Ajax.request({

                params: ajax_params,
                url: url,
                success: function(response) {resolve(JSON.parse(response.responseText))},
                failure: function(response) {reject(response)}
            })
        })
    },
    loadRoles: function () {

        var wnd = this;

        if (wnd.MedStaffFact_id) {

            var params = {
                MedStaffFact_id: wnd.MedStaffFact_id
            };

            wnd.getLoadMask("Загрузка...").show();
            wnd.ajaxRequestPromise('/?c=EMD&m=loadEMDMedStaffFactRoles', params).then(function(response){
                wnd.getLoadMask().hide();
                if (response.length) {

                    var panels = Ext6.ComponentQuery.query('fieldset panel', wnd.rolesPanel),
                        times = response.length; // количество дублирований

                    if (panels.length) {

                        initPanel = panels[0];

                        // создадим элементы
                        initPanel.fireEvent('cloneComponent', initPanel, times);

                        var combo = Ext6.ComponentQuery.query('swEMDPersonRole',  wnd.rolesPanel),
                            checkbox = Ext6.ComponentQuery.query('checkbox',  wnd.rolesPanel),
                            period = Ext6.ComponentQuery.query('swDateRangeField',  wnd.rolesPanel),
                            msf_role = Ext6.ComponentQuery.query('hidden[cls=EMDMedStaffFactRole]', wnd.rolesPanel);

                        response.forEach(function(role, i) {

                            if (combo[i]) {
                                combo[i].getStore().load({
                                    callback: function(){
                                        combo[i].select(role.EMDPersonRole_id);
                                        combo[i].fireEvent('select', combo[i], role.EMDPersonRole_id);
                                    }
                                });
                            }

                            if (checkbox[i]) {
                                checkbox[i].setValue(role.EMDMedStaffFactRole_IsDefault);
                            }

                            if (period[i]) {
                                period[i].setDates([
                                    new Date(role.EMDMedStaffFactRole_begDate),
                                    new Date(role.EMDMedStaffFactRole_endDate)
                                ]);
                            }

                            if (msf_role[i]) {
                                msf_role[i].setValue(role.EMDMedStaffFactRole_id);
                            }

                            wnd.msfRolesOriginal.push(role.EMDMedStaffFactRole_id);
                        });
                    }

                } else {

                    var combo = Ext6.ComponentQuery.query('swEMDPersonRole',  wnd.rolesPanel);

                    if (combo.length) {
                        combo[0].getStore().load();
                    }
                }
            });
        }
    },
    clearCheckboxes: function(checked){

        var wnd = this;
        var cboxes = Ext6.ComponentQuery.query('checkbox', wnd.rolesPanel);

        cboxes.forEach(function(checkbox) {
            if (checked != checkbox) checkbox.setValue('off');
        })
    },
    initComponent: function() {

        var wnd = this;

        var leftDate = new Date(),
            rightDate = new Date(leftDate.getFullYear() + 1, leftDate.getMonth(), leftDate.getDate());

        wnd.rolesPanel = Ext6.create('Ext6.form.FormPanel', {
            autoScroll: true,
            region: 'center',
            border: false,
            bodyStyle: 'padding: 20px 20px 20px 20px;',
            fieldDefaults: {
                labelAlign: 'left',
                msgTarget: 'side'
            },
            defaults: {
                border: false,
                xtype: 'panel',
                layout: 'anchor'
            },
            layout: 'vbox',
            items: [
                {
                    defaults: {
                        width: 430
                    },
                    items: [
                    {
                        xtype:'hidden',
                        name: 'MedStaffFact_id'
                    },{
                        xtype: 'textfield',
                        fieldLabel: 'МО',
                        name: 'Lpu_Name',
                        disabled: true
                    }, {
                        xtype: 'textfield',
                        fieldLabel: 'Отделение',
                        name: 'LpuSection_FullName',
                        disabled: true
                    }, {
                        xtype: 'textfield',
                        fieldLabel: 'Должность',
                        name: 'MedSpec_Name',
                        disabled: true
                    } ]
                },
                {
                    xtype: 'fieldset',
                    title: 'Список ролей',
                    items: [
                        {
                            xtype: 'panel',
                            border: false,
                            plugins: ['panelreplicator'],
                            replicatorTarget: 'swEMDPersonRole', // поле отвечающее за действие репликации
                            replicatorTargetEvent: 'select', // событие поля отвечающее за репликацию
                            layout: 'hbox',
                            onReplicate: function(panel) {
                                var combo = Ext6.ComponentQuery.query('swEMDPersonRole', panel);
                                if (combo.length) combo[0].getStore().load();
                            },
                            items: [
                                {
                                    xtype:'hidden',
                                    name: 'EMDMedStaffFactRole_id',
                                    cls: 'EMDMedStaffFactRole'
                                },
                                {
                                    xtype: 'swEMDPersonRole',
                                    name : 'EMDPersonRole_id',
                                    width: 300,
                                    fieldLabel: 'Роль',
                                    listeners: {
                                        select: function(combo, newValue) {

                                            var panel = combo.ownerCt,
                                                checkbox = Ext6.ComponentQuery.query('checkbox', panel),
                                                dt = Ext6.ComponentQuery.query('swDateRangeField', panel);

                                            if (checkbox.length) {
                                                if (newValue !== null) checkbox[0].enable();
                                                else checkbox[0].disable();
                                            }

                                            if (dt.length) {
                                                if (newValue !== null) dt[0].enable();
                                                else dt[0].disable();
                                            }
                                        }
                                    }
                                },
                                {
                                    width: 200,
                                    xtype: 'swDateRangeField',
                                    fieldLabel: 'Период действия',
                                    hideLabel: true,
                                    value: [
                                        leftDate,
                                        rightDate
                                    ],
                                    placeholder: 'Период действия',
                                    name: 'EMDMedStaffFactRole_period',
                                    allowBlank: false,
                                    style: 'margin-left: 10px;',
                                    listeners: {
                                        render: function(sender, element){
                                            sender.toggleActivity(sender);
                                        }
                                    },
                                    toggleActivity: function(dt) {

                                        var panel = dt.ownerCt,
                                            combo = Ext6.ComponentQuery.query('swEMDPersonRole', panel);

                                        if (combo.length) {

                                            if (combo[0].getValue() === null) {
                                                dt.disable();
                                            }
                                        }
                                    }
                                },
                                {
                                    width: 150,
                                    xtype: 'checkbox',
                                    boxLabel: 'по умолчанию',
                                    fieldLabel: 'по умолчанию',
                                    hideLabel: true,
                                    name: 'EMDMedStaffFactRole_IsDefault',
                                    style: 'margin-left: 10px; margin-top: -2px;',
                                    listeners: {
                                        render: function(sender, element){
                                            sender.toggleActivity(sender);
                                        },
                                        change: function(sender, value){
                                            if (value === true) {
                                                wnd.clearCheckboxes(sender);
                                            }
                                        }
                                    },
                                    toggleActivity: function(checkbox) {

                                        var panel = checkbox.ownerCt,
                                            combo = Ext6.ComponentQuery.query('swEMDPersonRole', panel);

                                        if (combo.length) {

                                            if (combo[0].getValue() === null) {
                                                checkbox.disable();
                                            }
                                        }
                                    }
                                }
                            ]
                        }
                    ]
                }
            ]
        });

        Ext6.apply(wnd, {
            layout: 'border',
            referenceHolder: true,
            reference: 'swEMDMedStaffFactRoleWindow_' + wnd.id,
            buttons: [{
                handler: function() {
                    wnd.hide();
                },
                text: BTN_FRMCANCEL
            }, '->', {
                handler: function() {
                    wnd.doSave();
                },
                cls: 'flat-button-primary',
                text: langs('Сохранить')
            }],
            items: [
                wnd.rolesPanel
            ],
            buttonAlign: 'left'
        });

        wnd.callParent(arguments);
    }
});
