/**
 * swEvnQueueWaitingListJournal - Журнал листов ожидания
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2019 Swan Ltd.
 */
Ext6.define('common.Admin.swEvnQueueWaitingListJournal', {
    extend: 'base.BaseForm',
    alias: 'widget.swEvnQueueWaitingListJournal',
    autoShow: false,
    maximized: true,
    cls: 'arm-window-new',
    title: 'Листы ожидания: Поиск',
    constrain: true,
    header: true,
    show: function(data) {

        var wnd = this;
        wnd.callParent(arguments);

        if (data) {

            // присваиваем все пришедшие переменные окну
            Object.keys(data).forEach(function(obj){
                wnd[obj] = data[obj];
            });

        } else data = {};

        if (data.callback) wnd.callback = data.callback;
        else wnd.callback = Ext6.emptyFn;

        var filter = wnd.FilterPanel.getForm();
        var msfCombo = filter.findField('MedStaffFact_id');
        msfCombo.getStore().load();

        wnd.initFilter();
    },
    doLoad: function(params) { this.doSearch(); },
    initFilter: function() {

        var wnd = this;
        var filter = wnd.FilterPanel.getForm();

        var LpuCombo = filter.findField('Lpu_id');

        LpuCombo.getStore().load({
            callback: function(){
                var lpu_id = getGlobalOptions().lpu_id;
                LpuCombo.select(lpu_id);
                LpuCombo.fireEvent('select', LpuCombo, LpuCombo.getSelection());
            }
        });

        filter.findField('EvnQueue_insDT_period').clear();
        //filter.findField('EvnQueue_insDT_period').loadDefaultValue();

    },
    doSearch: function() {

        var wnd = this;
        var grid = wnd.RegistryGrid;

        var filter = wnd.FilterPanel.getForm();
        var params = filter.getValues();

        if (params.MedStaffFact_id && !Ext6.isNumeric(params.MedStaffFact_id)) {
            params.MedStaffFact_id = null;
        }

        grid.getStore().removeAll();
        grid.getStore().load({params: params});
    },
    doReset: function () {

        var wnd = this;
        var grid = wnd.RegistryGrid;
        var filter = wnd.FilterPanel.getForm();

        filter.reset();
        wnd.initFilter();
    },
    initComponent: function() {

        var wnd = this;

        wnd.FilterPanel = Ext6.create('Ext6.form.FormPanel', {
            autoScroll: true,
            region: 'north',
            border: false,
            bodyStyle: 'padding: 0 20px 20px 20px;',
            fieldDefaults: {
                labelAlign: 'top',
                msgTarget: 'side'
            },
            defaults: {
                border: false,
                xtype: 'fieldset',
                layout: 'anchor'
            },
            layout: 'vbox',
            items: [
                {
                    layout: 'hbox',
                    title: 'Пациент',
                    collapsible: true,
                    width: '100%',
                    defaults: {
                        margin: '0 0 0 10'
                    },
                    items: [
                        {
                            xtype: 'textfield',
                            fieldLabel: 'Фамилия',
                            plugins: [ new Ext6.ux.Translit(true, false) ],
                            name: 'Person_SurName'
                        }, {
                            xtype: 'textfield',
                            fieldLabel: 'Имя',
                            plugins: [ new Ext6.ux.Translit(true, false) ],
                            name: 'Person_FirName'
                        }, {
                            xtype: 'textfield',
                            fieldLabel: 'Отчество',
                            plugins: [ new Ext6.ux.Translit(true, false) ],
                            name: 'Person_SecName'
                        },
                        {
                            xtype: 'swDateField',
                            fieldLabel: 'Дата рождения',
                            name: 'Person_BirthDay',
                            allowBlank: true
                        },
                        {
                            xtype: 'textfield',
                            width: 250,
                            labelWidth: 95,
                            fieldLabel: 'Единый номер полиса',
                            name: 'Polis_EdNum',
                            hidden: getRegionNick() == 'kz'
                        }
                    ]
                },
                {
                    layout: 'hbox',
                    title: 'Медицинская организация',
                    collapsible: true,
                    width: '100%',
                    items: [
                        {
                            border: false,
                            defaults: {
                                margin: '0 0 0 10'
                            },
                            items: [
                                {
                                    xtype: 'swLpuCombo',
                                    fieldLabel: 'МО',
                                    allowBlank: true,
                                    width: 350,
                                    anyMatch: true,
                                    name: 'Lpu_id',
                                    disabled: !isUserGroup('SuperAdmin'),
                                    reference: 'Lpu_id',
                                    anchor: '-5',
                                    plugins: [ new Ext6.ux.Translit(true, false) ],
                                    listeners: {
                                        select: function(combo, record, index) {

                                            if (record && record.get('Lpu_id')) {

                                                var filter = wnd.FilterPanel.getForm();

                                                var MedPersonalCombo = filter.findField('MedStaffFact_id');
                                                MedPersonalCombo.getStore().proxy.extraParams['Lpu_id'] = record.get('Lpu_id');
                                                MedPersonalCombo.getStore().reload();

                                                var LpuBuildingCombo = filter.findField('LpuBuilding_id');
                                                LpuBuildingCombo.getStore().load({
                                                    params: {
                                                        where: 'where Lpu_id = ' + record.get('Lpu_id')
                                                    },
                                                    callback: function(){
                                                        if (wnd.LpuBuilding_id) {
                                                            LpuBuildingCombo.select(wnd.LpuBuilding_id);
                                                            LpuBuildingCombo.fireEvent('select', LpuBuildingCombo, LpuBuildingCombo.getSelection());
                                                        }
                                                    }
                                                });
                                            }
                                        }
                                    }
                                }, {
                                    xtype: 'swLpuSectionProfileCombo',
                                    fieldLabel: 'Профиль',
                                    width: 350,
                                    anyMatch: true,
                                    name: 'LpuSectionProfile_id',
                                    reference: 'LpuSectionProfile_id',
                                    allowBlank: true,
                                    anchor: '-5',
                                    listeners: {
                                        select: function(combo, record, index) { }
                                    },
                                    tpl: new Ext6.XTemplate(
                                        '<tpl for="."><div class="x6-boundlist-item">',
                                        '<table>',
                                        '<tr><td style="color: red; width:60px;">{LpuSectionProfile_Code}</td>',
                                        '<td>{LpuSectionProfile_Name}</td>',
                                        '</tr></table>',
                                        '</div></tpl>'
                                    )
                                }
                            ]
                        },
                        {
                            border: false,
                            defaults: {
                                margin: '0 0 0 10'
                            },
                            items: [
                                {
                                    xtype: 'swLpuBuildingCombo',
                                    fieldLabel: 'Подразделение',
                                    name: 'LpuBuilding_id',
                                    reference: 'LpuBuilding_id',
                                    allowBlank: true,
                                    anyMatch: true,
                                    width: 350,
                                    anchor: '-5',
                                    listeners: {
                                        select: function(combo, record, index) { }
                                    }
                                },
                                {
                                    minWidth: 350,
                                    xtype: 'swMedStaffFactCombo',
                                    fieldLabel: 'Врач',
                                    anyMatch: true,
                                    name: 'MedStaffFact_id',
                                    anchor: '-5',
                                    allowBlank: true,
                                    lastQuery: ''
                                }
                            ]
                        }
                    ]
                },
                {
                    layout: 'hbox',
                    title: 'Лист ожидания',
                    collapsible: true,
                    width: '100%',
                    defaults: {
                        margin: '0 0 0 10'
                    },
                    items: [
                        {
                            xtype: 'swEvnQueueStatusCombo',
                            fieldLabel: 'Статус листа ожидания',
                            name: 'EvnQueueStatus_id',
                            anchor: '-5'
                        },
                        Ext6.create('Ext6.date.RangeField', {
                            fieldLabel: 'Период',
                            name: 'EvnQueue_insDT_period',
                            anchor: '-5',
                            minWidth: 200,
                            listeners: {},
                            loadDefaultValue: function(){

                                var menu = this.getMenu(true);

                                // ставим период 7 дней
                                menu.setMode(2);
                                menu.fireEvent('apply', this, menu.getDates());
                            }
                        })
                    ]
                },
                {
                    width: 300,
                    xtype: 'panel',
                    items: [{
                        border: false,
                        cls: 'panel-80',
                        layout: 'column',
                        items: [
                            {
                                cls: 'button-primary',
                                text: 'Найти',
                                //iconCls: 'person-search-btn-icon action_find_white',
                                xtype: 'button',
                                handler: function () {
                                    wnd.doSearch();
                                }
                            }, {
                                cls: 'button-secondary',
                                text: 'Очистить',
                                xtype: 'button',
                                //iconCls: 'person-clear-btn-icon action_clear',
                                style: 'margin-left: 10px;',
                                handler: function () {
                                    wnd.doReset();
                                }
                            }
                        ]
                    }]
                }
            ]
        });

        wnd.RegistryGrid = Ext6.create('Ext6.grid.Panel', {
            cls: 'grid-common',
            region: 'center',
            xtype: 'grid',
            selModel: {
                mode: 'SINGLE',
                listeners: {
                    select: function(model, record, index) {

                    }
                }
            },

            listeners: {},
            store: {
                fields: [
                    { name: 'EvnQueue_id', type: 'int' },
                    { name: 'EvnQueue_index', type: 'int' },
                    { name: 'Person_FullName', type: 'string' },
                    { name: 'Person_BirthDay', type: 'date', dateFormat: 'd.m.Y' },
                    { name: 'LpuSectionProfile_Name', type: 'string' },
                    { name: 'MedPersonal_Name', type: 'string'},
                    { name: 'EvnQueueStatus_id', type: 'int'},
                    { name: 'EvnQueueStatus_Name', type: 'string'},
                    { name: 'EvnQueue_DeclineCount', type: 'int'},
                    { name: 'EvnQueue_insDT', type: 'date', dateFormat: 'd.m.Y' },
                    { name: 'RecordPrognoz', type: 'string'},
                    { name: 'Lpu_Nick', type: 'string'}
                ],
                proxy: {
                    type: 'ajax',
                    actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
                    url: '/?c=queue&m=loadWaitingListJournal',
                    reader: {
                        type: 'json',
                        rootProperty: 'data'
                    }
                },
                sorters: [
                    {
                        property: 'EvnQueue_insDT',
                        direction: 'DESC'
                    }],
                listeners: {}
            },
            columns: [
                {text: 'МО', width: 150, dataIndex: 'Lpu_Nick'},
                {text: 'ФИО', width: 300, dataIndex: 'Person_FullName'},
                {text: 'Дата рождения', width: 120, dataIndex: 'Person_BirthDay', renderer: Ext6.util.Format.dateRenderer('d.m.Y')},
                {text: 'Профиль', width: 200, dataIndex: 'LpuSectionProfile_Name'},
                {text: 'Врач', width: 300, dataIndex: 'MedPersonal_Name', flex: 1},
                {text: 'Статус ЛО', width: 200, dataIndex: 'EvnQueueStatus_Name'},
                {text: 'Кол-во отказов', width: 120, dataIndex: 'EvnQueue_DeclineCount',renderer: function(val) {
                        return (val) ? val : '';
                    }},
                {text: 'Позиция', width: 120, dataIndex: 'EvnQueue_index', renderer: function(val) {
                        return (val) ? val : '';
                    }},
                {text: 'Прогноз ожидания записи', width: 200, dataIndex: 'RecordPrognoz'},
                {text: 'Дата постановки', width: 200, dataIndex: 'EvnQueue_insDT', renderer: Ext6.util.Format.dateRenderer('d.m.Y')}
            ]
        });


        Ext6.apply(wnd, {
            layout: 'border',
            referenceHolder: true,
            reference: 'swEvnQueueWaitingListJournal_' + wnd.id,
            items: [
                wnd.FilterPanel,
                wnd.RegistryGrid
            ],
            buttonAlign: 'left'
        });

        wnd.callParent(arguments);
    }
});
