/**
 * swEMDSearchWindow - Форма поиска неподписанных документов для подписи и регистрации в РЭМД
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2018 Swan Ltd.
 */
Ext6.define('emd.swEMDSearchUnsignedWindow', {
    requires: [
        'sw.frames.EMD.swEMDPanel'
    ],
    extend: 'base.BaseForm',
    alias: 'widget.swEMDSearchUnsignedWindow',
    autoShow: false,
    maximized: true,
    cls: 'arm-window-new emd-search',
    title: 'Подписание медицинской документации',
    constrain: true,
    header: getGlobalOptions().client == 'ext2',
    armType: 'common',
    show: function(data) {

        var wnd = this;
        wnd.callParent(arguments);
        wnd.armType = 'common';

        if (data) {

            // присваиваем все пришедшие переменные окну
            Object.keys(data).forEach(function(obj){
                wnd[obj] = data[obj];
            });

        } else data = {};

        log('data', data);

        if (data.callback) wnd.callback = data.callback;
        else wnd.callback = Ext6.emptyFn;

        wnd.initFilter();
    },
    doLoad: function() { this.doSearch(); },
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

        if (wnd.armType == 'leadermo') {
            filter.findField('LpuSection_id').getStore().proxy.extraParams['LpuUnitType_id'] = 1;
        } else {
            filter.findField('LpuSection_id').getStore().proxy.extraParams['LpuUnitType_id'] = null;
        }

        filter.findField('EMDDocumentTypeLocal_id').getStore().load({
            callback: function(){
                if (wnd.armType == 'leadermo') {
                    filter.findField('EMDDocumentTypeLocal_id').select(16);
                    filter.findField('EMDDocumentTypeLocal_id').getStore().filterBy(function(rec) {
                        if (rec.get('EMDDocumentTypeLocal_id').toString().inlist(['16', '17', '18', '19', '11'])) {
                            return true;
                        }

                        return false;
                    });
                } else {
                    filter.findField('EMDDocumentTypeLocal_id').select(1);
                    filter.findField('EMDDocumentTypeLocal_id').getStore().clearFilter();
                }
            }
        });
        filter.findField('EMDDocumentTypeLocal_id').select(0);
        //filter.findField('Evn_insDT_period').clear();
        //filter.findField('Evn_updDT_period').clear();

    },
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
    doSearch: function() {

        var wnd = this;
        var grid = wnd.DocsGrid;

        var filter = wnd.FilterPanel.getForm();
        var params = filter.getValues();

        params.start = 0;
        params.limit = 100;

        wnd.clearVersions();
        grid.getStore().removeAll();
        grid.getStore().load({params: params});
    },
    clearVersions: function(){

        var wnd = this;

        var label = Ext6.ComponentQuery.query('label', wnd.VersionTitleBar);
        if (label[0]) label[0].setHtml('Версии документа');

        wnd.VersionGrid.removeAll();
    },
    doReset: function () {

        var wnd = this;

        wnd.clearVersions();
        var filter = wnd.FilterPanel.getForm();

        filter.reset();
        wnd.initFilter();
    },
    loadVersions: function (record) {

        if (record) {
            var wnd = this;
            var filter = wnd.FilterPanel.getForm();

            var params = {
                EMDRegistry_id: record.get('EMDRegistry_id')
            };
            wnd.getLoadMask("Загрузка...").show();

            // подгружаем версии EMD
            wnd.ajaxRequestPromise('/?c=EMD&m=loadEMDVersions', params).then(function(response){

                wnd.getLoadMask().hide();
                var label = Ext6.ComponentQuery.query('label', wnd.VersionTitleBar);

                if (response.length > 0) {

                    if (label[0]) label[0].setHtml('Версии документа' + '<i class="emd-version-counter"> '+response.length+'</i>');

                    wnd.VersionGrid.removeAll();

                    response.forEach(function(doc, num){
                        wnd.VersionGrid.add({
                            xtype: 'swPanel',
                            threeDotMenu: null,
                            cls: 'accordion-panel-window',
                            tpl: new Ext6.XTemplate(
                                '<ul class="emdv-wrapper collapsible-list-item">',
                                '<li><span>Дата версии:</span><span>{EMDVersion_insDT}</span></li>',
                                '<li><span>ЭМД:</span><span><a href="{EMDVersion_FilePath}" target="_blank">{EMDVersion_FilePath}</a></span></li>',
                                '<li><span>Подписи:</span><span>{signs}</span></li>',
                                '<li><span>Регистрация ЕГИСЗ:</span><span>{RegistrationInfo}</span></li>',
                                '</ul>'
                            ),
                            data: doc,
                            title: ((doc.EMDVersion_VersionNum) ? 'Версия ' + doc.EMDVersion_VersionNum : 'Без версии'),
                            collapsed: (num > 0)
                        });
                    })
                } else {
                    wnd.VersionGrid.removeAll();
                    if (label[0]) label[0].setHtml('Версии документа');
                }
            });
        }
    },
    doSign: function(record) {

        var wnd = this;

        if (record && record.get('EMDRegistry_ObjectID')) {
            wnd.doCheckBeforeSign(record).then(function(){
                getWnd('swEMDSignWindow').show({
                    backgroundProcessing: false, // чтобы прелодер был нормальный
                    EMDRegistry_ObjectName: record.get('EMDRegistry_ObjectName'),
                    EMDRegistry_ObjectID: record.get('EMDRegistry_ObjectID'),
                    callback: function(){
                        wnd.doLoad({
                            grid: wnd.DocsGrid,
                            loadParams: {}
                        });
                    }
                });
            });
        }
    },
    doCheckBeforeSign: function(record) {

        var wnd = this;

        return new Promise(function(resolve, reject) {

            if (
                record.get('EMDRegistry_id') > 0
                && record.get('Evn_updDT').getTime() == record.get('EMDVersion_actualDT').getTime()
            ) {
                Ext6.Msg.show({
                    buttons: Ext6.Msg.YESNO,
                    fn: function(buttonId, text, obj) {
                        if ( buttonId == 'yes' ) {
                            resolve(true);
                        }
                    },
                    icon: Ext6.MessageBox.WARNING,
                    msg: "По данным случая оказания медицинской помощи создан ЭМД, и данные ЭМД актуальны. " +
                    "Подписание не требуется, но может быть выполнено. При подписании будет создана новая версия ЭМД.",
                    title: langs('Предупреждение')
                });
            } else {
                resolve(true);
            }

        });
    },
    initComponent: function() {

        var wnd = this;

        wnd.FilterPanel = Ext6.create('Ext6.form.FormPanel', {
            autoScroll: true,
            region: 'north',
            border: false,
            bodyStyle: 'padding: 0 20px 20px 20px;',
            layout: 'hbox',
            fieldDefaults: {
                labelAlign: 'top',
                msgTarget: 'side'
            },
            items: [
                {
                    xtype: 'panel',
                    border: false,
                    layout: 'hbox',
                    style: 'margin-right: 5px;',
                    items:[
                        {
                            xtype: 'swTagEMDDocumentTypeLocal',
                            name: 'EMDDocumentTypeLocal_id',
                            reference: 'EMDDocumentTypeLocal_id',
                            fieldLabel: 'Виды документов',
                            anchor: '-5',
                            width: 250,
                            growMax: 87,
                            growMin: 87,
                            stacked: true,
                            triggerOnClick:false
                        }
                    ]
                },
                {
                    xtype: 'panel',
                    border: false,
                    defaults: {
                        border: false,
                        xtype: 'panel',
                        width: 250,
                        layout: 'anchor'
                    },
                    layout: 'hbox',
                    fieldDefaults: {
                        labelAlign: 'top',
                        msgTarget: 'side'
                    },
                    items: [
                         {
                            width: 300,
                            items: [{
                                xtype: 'swLpuCombo',
                                fieldLabel: 'МО',
                                allowBlank: false,
                                name: 'Lpu_id',
                                disabled: !isUserGroup('SuperAdmin'),
                                reference: 'Lpu_id',
                                anchor: '-5',
                                plugins: [ new Ext6.ux.Translit(true, false) ],
                                listeners: {
                                    select: function(combo, record, index) {

                                        if (record && record.get('Lpu_id')) {

                                            var filter = wnd.FilterPanel.getForm();

                                            var MedPersonalCombo = filter.findField('MedPersonal_id');
                                            MedPersonalCombo.getStore().proxy.extraParams['Lpu_id'] = record.get('Lpu_id');

                                            var LpuSectionCombo = filter.findField('LpuSection_id');
                                            LpuSectionCombo.getStore().proxy.extraParams['Lpu_id'] = record.get('Lpu_id');

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
                                xtype: 'swLpuBuildingCombo',
                                fieldLabel: 'Подразделение',
                                name: 'LpuBuilding_id',
                                reference: 'LpuBuilding_id',
                                allowBlank: false,
                                anchor: '-5',
                                listeners: {
                                    select: function(combo, record, index) {

                                        if (record && record.get('LpuBuilding_id')) {

                                            var filter = wnd.FilterPanel.getForm();
                                            var LpuSectionCombo = filter.findField('LpuSection_id');

                                            LpuSectionCombo.getStore().proxy.extraParams['mode'] = 'combo';
                                            LpuSectionCombo.getStore().proxy.extraParams['LpuBuilding_id'] = record.get('LpuBuilding_id');
                                            LpuSectionCombo.getStore().load({
                                                callback:function(){
                                                    if (wnd.LpuSection_id) {
                                                        LpuSectionCombo.select(wnd.LpuSection_id);
                                                        LpuSectionCombo.fireEvent('select', LpuSectionCombo, LpuSectionCombo.getSelection());
                                                    }
                                                }
                                            });
                                        }
                                    }
                                }
                            }]
                        }, {
                            width: 300,
                            items: [{
                                xtype: 'SwLpuSectionGlobalCombo',
                                fieldLabel: 'Отделение',
                                name: 'LpuSection_id',
                                reference: 'LpuSection_id',
                                anchor: '-5',
                                listeners: {
                                    select: function(combo, record, index) {

                                        if (record && record.get('LpuSection_id')) {
                                            var filter = wnd.FilterPanel.getForm();
                                            var MedPersonalCombo = filter.findField('MedPersonal_id');

                                            MedPersonalCombo.getStore().proxy.extraParams['LpuSection_id'] = record.get('LpuSection_id');

                                            MedPersonalCombo.getStore().load({
                                                callback: function(){
                                                    // только для АРМ ВРАЧА
                                                    if (wnd.MedPersonal_id) {
                                                        MedPersonalCombo.select(wnd.MedPersonal_id);
                                                    }
                                                }
                                            });
                                        }

                                    }
                                }
                            },
                                {
                                    xtype: 'swMedPersonalCombo',
                                    fieldLabel: 'Врач',
                                    name: 'MedPersonal_id',
                                    anchor: '-5'
                                }]
                        },{
                            width: 220,
                            items: [
                                Ext6.create('Ext6.date.RangeField', {
                                    fieldLabel: 'Период создания',
                                    name: 'Evn_insDT_period',
                                    allowBlank: false,
                                    anchor: '-5',
                                    listeners: {}
                                }),
                                Ext6.create('Ext6.date.RangeField', {
                                    fieldLabel: 'Период изменения',
                                    name: 'Evn_updDT_period',
                                    allowBlank: false,
                                    anchor: '-5'
                                })]
                        },{
                            items: [{
                                xtype: 'textfield',
                                fieldLabel: 'Номер',
                                name: 'Doc_Num',
                                anchor: '-5',
                                listeners: {
                                    specialkey: function(field, e, eOpts) {
                                        if (e.getKey() == e.ENTER) {
                                            wnd.doSearch();
                                        }
                                    }
                                }
                            }, {
                                xtype: 'textfield',
                                fieldLabel: 'Пациент',
                                plugins: [new Ext6.ux.Translit(true, false)],
                                name: 'Person_FIO',
                                anchor: '-5',
                                listeners: {
                                    specialkey: function(field, e, eOpts) {
                                        if (e.getKey() == e.ENTER) {
                                            wnd.doSearch();
                                        }
                                    }
                                }
                            }]
                        },
                        {
                            items: [
                                {
                                    border: false,
                                    cls: 'panel-80',
                                    layout: 'column',
                                    style: 'margin-top: 27px;',
                                    items: {
                                        xtype: 'checkbox',
                                        boxLabel: 'Без подписи',
                                        fieldLabel: 'Без подписи',
                                        hideLabel: true,
                                        checked: true,
                                        name: 'isWithoutSign'
                                    }
                                },
                                {
                                    border: false,
                                    cls: 'panel-80',
                                    layout: 'column',
                                    style: 'margin-top: 33px;',
                                    items: [
                                        {
                                            cls: 'button-primary',
                                            text: 'Найти',
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
                                }
                            ]
                        }
                    ]
                }
            ]
        });

        wnd.DocsTitleBar = Ext6.create('Ext6.Panel', {
            region: 'north',
            style: {
                'box-shadow': '0px 1px 6px 2px #ccc',
                zIndex: 2
            },
            layout: 'border',
            border: false,
            height: 40,
            bodyStyle: 'background-color: #EEEEEE;',
            items: [
                {
                    region: 'center',
                    border: false,
                    bodyStyle: 'background-color: #EEEEEE;',
                    height: 40,
                    bodyPadding: 10,
                    items: [
                        Ext6.create('Ext6.form.Label', {
                            xtype: 'label',
                            cls: 'no-wrap-ellipsis',
                            style: 'font-size: 16px; padding: 3px 10px;',
                            html: 'Документы'
                        })
                    ]
                },
                Ext6.create('Ext6.Toolbar', {
                    region: 'east',
                    height: 40,
                    border: false,
                    noWrap: true,
                    right: 0,
                    style: 'background: rgb(238, 238, 238) !important;',
                    defaults: {
                        style: {
                            'color': 'transparent'
                        },
                        userCls: 'button-without-frame'
                    },
                    cls: 'grid-toolbar',
                    items: [
                        Ext6.create('Ext6.button.Button', {
                            userCls: 'button-without-frame',
                            style: {
                                'color': 'transparent'
                            },
                            iconCls: 'panicon-doc-sign doc-sign',
                            refId: 'emdButton',
                            tooltip: langs('Подписать документ'),
                            handler: function() {

                                var record = wnd.DocsGrid.getSelectionModel().getSelectedRecord();
                                wnd.doSign(record);
                            }
                        }),{
                            xtype: 'button',
                            cls: 'toolbar-padding',
                            iconCls: 'action_refresh',
                            handler: function () {
                                wnd.doLoad({
                                    grid: wnd.DocsGrid,
                                    loadParams: {}
                                });
                            }
                        },
                        {
                            iconCls: 'panicon-print',
                            tooltip: langs('Печать'),
                            menu: new Ext6.menu.Menu({
                                userCls: 'menuWithoutIcons',
                                items: [{
                                    text: 'Печать текущей страницы',
                                    handler: function () {
                                        Ext6.ux.GridPrinter.print(wnd.DocsGrid);
                                    }
                                }, {
                                    text: 'Печать всего списка',
                                    handler: function() {
                                        Ext6.ux.GridPrinter.print(wnd.DocsGrid);
                                    }
                                }]
                            })
                        }
                    ]
                })],
            xtype: 'panel'
        });

        wnd.VersionGrid = new Ext6.Panel({
            region: 'center',
            scrollable: true,
            layout: {
                type: 'accordion',
                titleCollapse: true,
                animate: true,
                multi: true,
                activeOnTop: false,
                fill: false
            }
        });

        wnd.DocsGrid = Ext6.create('Ext6.grid.Panel', {
            cls: 'grid-common',
            region: 'center',
            xtype: 'grid',
            selModel: {
                mode: 'SINGLE',
                listeners: {
                    select: function(model, record, index) {
                        wnd.loadVersions(record);
                    }
                }
            },

            listeners: {},
            store: {
                fields: [
                    { name: 'EMDRegistry_id', type: 'int' },
                    { name: 'EMDVersion_actualDT', type: 'date', dateFormat: 'd.m.Y H:i:s' },
                    { name: 'EMDRegistry_ObjectName', type: 'string'},
                    { name: 'EMDRegistry_ObjectID', type: 'int'},
                    { name: 'Doc_Num', type: 'string' },
                    { name: 'Evn_insDT', type: 'date', dateFormat: 'd.m.Y H:i:s' },
                    { name: 'Evn_updDT', type: 'date', dateFormat: 'd.m.Y H:i:s' },
                    { name: 'Person_id', type: 'string'},
                    { name: 'Person_FIO', type: 'string'},
                    { name: 'Person_BirthDay', type: 'date', dateFormat: 'd.m.Y' },
                    { name: 'Doc_Comment', type: 'int'},
                    { name: 'MedStaffFact_id', type: 'int'},
                    { name: 'MedPersonal_Fio', type: 'string'},
                    { name: 'EMDDocumentTypeLocal_Name', type: 'string'},
                    { name: 'pmUser_insID', type: 'int'},
                    { name: 'IsSigned', type: 'int'}
                ],
                proxy: {
                    type: 'ajax',
                    actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
                    url: '/?c=EMD&m=searchDocs',
                    reader: {
                        type: 'json',
                        rootProperty: 'data'
                    }
                },
                sorters: [{
                    property: 'Evn_updDT',
                    direction: 'DESC'
                }],
                listeners: {}
            },
            columns: [
                {text: 'Номер', width: 100, dataIndex: 'Doc_Num'},
                {
                    text: 'Статус',
                    width: 60,
                    dataIndex: 'EMDRegistry_Sign',
                    tdCls: 'vertical-middle',
                    xtype: 'widgetcolumn',
                    widget: {
                        xtype: 'swEMDPanel',
                        bind: {
                            EMDRegistry_ObjectName: '{record.EMDRegistry_ObjectName}',
                            EMDRegistry_ObjectID: '{record.EMDRegistry_ObjectID}',
                            IsSigned: '{record.IsSigned}'
                        }
                    }
                },
                {text: 'Вид документа', width: 150, dataIndex: 'EMDDocumentTypeLocal_Name'},
                {text: 'Создан', width: 140, dataIndex: 'Evn_insDT', renderer: Ext6.util.Format.dateRenderer('d.m.Y H:i')},
                {text: 'Изменен', width: 140, dataIndex: 'Evn_updDT', renderer: Ext6.util.Format.dateRenderer('d.m.Y H:i')},
                {text: 'Врач', width: 200, dataIndex: 'MedPersonal_Fio', flex: 1},
                {text: 'Пациент', width: 300, dataIndex: 'Person_FIO', flex: 1},
                {text: 'Дата рождения', width: 120, dataIndex: 'Person_BirthDay', renderer: Ext6.util.Format.dateRenderer('d.m.Y')},
                {text: 'Примечание', width: 120, dataIndex: 'Doc_Comment'}
            ]
        });

        this.VersionTitleBar = Ext6.create('Ext6.Panel', {
            xtype: 'panel',
            region: 'north',
            style: {
                'box-shadow': '0px 1px 6px 2px #ccc',
                zIndex: 2
            },
            layout: 'border',
            border: false,
            height: 40,
            bodyStyle: 'background-color: #EEEEEE;',
            items: [
                {
                    region: 'center',
                    border: false,
                    bodyStyle: 'background-color: #EEEEEE;',
                    height: 40,
                    bodyPadding: 10,
                    items: [
                        Ext6.create('Ext6.form.Label', {
                            xtype: 'label',
                            cls: 'no-wrap-ellipsis version-label',
                            style: 'font-size: 16px; padding: 3px 10px;',
                            html: 'Версии документа'
                        })
                    ]
                },
                Ext6.create('Ext6.Toolbar', {
                    region: 'east',
                    height: 40,
                    border: false,
                    noWrap: true,
                    right: 0,
                    style: 'background: rgb(238, 238, 238) !important;',
                    defaults: {
                        style: {
                            'color': 'transparent'
                        },
                        userCls: 'button-without-frame'
                    },
                    cls: 'grid-toolbar',
                    items: [
                        {
                            xtype: 'button',
                            cls: 'toolbar-padding',
                            iconCls: 'action_refresh',
                            handler: function(){
                                var record = wnd.DocsGrid.getSelectionModel().getSelectedRecord();
                                wnd.loadVersions(record);
                            }
                        },
                        {
                            iconCls: 'panicon-print',
                            tooltip: langs('Печать'),
                            handler: function() {
                                Ext6.Msg.alert('','Функционал в разработке');
                            }
                        }
                    ]
                })]
        });

        Ext6.apply(wnd, {
            layout: 'border',
            referenceHolder: true,
            reference: 'swEMDSearchUnsignedWindow_' + wnd.id,
            items: [
                wnd.FilterPanel,
                new Ext6.Panel({
                    region: 'center',
                    layout: 'border',
                    items:[
                        new Ext6.Panel({
                            region: 'center',
                            layout: 'border',
                            items:[
                                wnd.DocsTitleBar,
                                wnd.DocsGrid
                            ]
                        }),
                        new Ext6.Panel({
                            region: 'east',
                            width: '30%',
                            split: true,
                            layout: 'border',
                            items:[
                                wnd.VersionTitleBar,
                                wnd.VersionGrid
                            ]
                        })
                    ]
                })
            ],
            buttonAlign: 'left'
        });

        wnd.callParent(arguments);
    }
});
