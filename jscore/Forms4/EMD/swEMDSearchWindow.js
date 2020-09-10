/**
 * swEMDSearchWindow - Форма поиска ЭМД и версий ЭМД в РЭМД
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2018 Swan Ltd.
 */
Ext6.define('emd.swEMDSearchWindow', {
    requires: [
        'sw.frames.EMD.swEMDPanel'
    ],
    extend: 'base.BaseForm',
    alias: 'widget.swEMDSearchWindow',
    autoShow: false,
    maximized: true,
    cls: 'arm-window-new emd-search',
    title: 'Региональный РЭМД',
    constrain: true,
    header: getGlobalOptions().client == 'ext2',
    show: function(data) {

        var wnd = this;
        wnd.callParent(arguments);

        if (data) {

            // присваиваем все пришедшие переменные окну
            Object.keys(data).forEach(function(obj){
                wnd[obj] = data[obj];
            });

        } else data = {};

        log('data', data);

        if (data.callback) wnd.callback = data.callback;
        else wnd.callback = Ext6.emptyFn;

        var store = wnd.lookup('EMDDocumentTypeLocal_id').getStore();
        store.load({
        	params: { isGlobalAndActive: true },

        	callback: function(records, operation, success)
        	{
        		if (success)
        			store.filterBy((record) => record.get('EMDDocumentTypeLocal_id') != 28);  // Протокол телемедицинской консультации
        	}
        });

        wnd.initFilter();
    },
    onRecordSelect: function() {

    },
    onLoadGrid: function() {
        //var wnd = this;
        //wnd.filterGrid();
        //wnd.onRecordSelect();
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

        filter.findField('EMDVersion_RegistrationDate_period').clear();
        filter.findField('EMDRegistry_EMDDate_period').loadDefaultValue();

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
        var grid = wnd.RegistryGrid;

        var filter = wnd.FilterPanel.getForm();
        var extraParams = filter.getValues();
		extraParams.isGlobalAndActive = true;
		extraParams.limit = 100;
        wnd.clearVersions();
		grid.getStore().proxy.extraParams = extraParams;
		grid.getStore().currentPage = 1;
        grid.getStore().removeAll();
        grid.getStore().load();
    },
    clearVersions: function(){

        var wnd = this;

        var label = Ext6.ComponentQuery.query('label', wnd.VersionTitleBar);
        if (label[0]) label[0].setHtml('Версии документа');

        wnd.VersionGrid.removeAll();
    },
    doReset: function () {

        var wnd = this;
        var grid = wnd.RegistryGrid;


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
                EMDRegistry_id: record.get('EMDRegistry_id'),
                isWithoutRegistration: filter.findField('isWithoutRegistration').getValue(),
                EMDVersion_RegistrationDate_period: filter.findField('EMDVersion_RegistrationDate_period').getValue()
            };
            wnd.getLoadMask("Загрузка...").show();

            // подгружаем версии EMD
            wnd.ajaxRequestPromise('/?c=EMD&m=loadEMDVersions', params).then(function(response){

                wnd.getLoadMask().hide();
                if (response.length > 0) {

                    var label = Ext6.ComponentQuery.query('label', wnd.VersionTitleBar);
                    if (label[0]) label[0].setHtml('Версии документа' + '<i class="emd-version-counter"> '+response.length+'</i>');

                    wnd.VersionGrid.removeAll();

                    response.forEach(function(doc, num){
                        wnd.VersionGrid.add({
                            xtype: 'swPanel',
                            threeDotMenu:
                                Ext6.create('Ext6.menu.Menu', {
                                    userCls: 'menuWithoutIcons',
                                    EMDVersion_id: doc.EMDVersion_id,
									items: [{
										text: 'Подписать',
										disabled: !Ext6.isEmpty(doc.RegistrationInfo),
										handler: function (sender) {
											var record = wnd.RegistryGrid.getSelectionModel().getSelectedRecord();
											wnd.doSign(
												record,
												{
													isMOSign: false,
													// по шляпному конечно, но пока так
													EMDVersion_id: sender.ownerCt.EMDVersion_id
												}
											);
										}
									}, {
                                        text: 'Подписать от МО',
										disabled: !Ext6.isEmpty(doc.RegistrationInfo),
                                        handler: function(sender) {
                                            var record = wnd.RegistryGrid.getSelectionModel().getSelectedRecord();
                                            wnd.doSign(
                                                record,
                                                {
                                                    isMOSign: true,
                                                    // по шляпному конечно, но пока так
                                                    EMDVersion_id: sender.ownerCt.EMDVersion_id
                                                }
                                            );
                                        }
                                    }]
                                }),
                            cls: 'accordion-panel-window',
                            tpl: new Ext6.XTemplate(
                                '<ul class="emdv-wrapper collapsible-list-item">',
                                '<li><span>Дата версии:</span><span>{EMDVersion_insDT}</span></li>',
                                '<li><span>ЭМД:</span><span><a href="{EMDVersion_FilePath}" target="_blank">{EMDVersion_FilePath}</a></span></li>',
                                '<li><span>Подписи:</span><span>{signs}</span></li>',
                                '<li><span>Подпись МО:</span><span>{mosigns}</span></li>',
                                '<li><span>Регистрация ЕГИСЗ:</span><span>{RegistrationInfo}</span></li>',
                                '</ul>'
                            ),
                            data: doc,
                            title: ((doc.EMDVersion_VersionNum) ? 'Версия ' + doc.EMDVersion_VersionNum : 'Без версии'),
                            collapsed: (num > 0)
                        });
                    })
                }
            });
        }
    },
    doSign: function(record, data) {

        var wnd = this;

        if (record && data && data.EMDVersion_id) {

            var signParams = {
                backgroundProcessing: false, // чтобы прелодер был нормальный
                EMDRegistry_ObjectName: record.get('EMDRegistry_ObjectName'),
                EMDRegistry_ObjectID: record.get('EMDRegistry_ObjectID'),
                EMDVersion_id: data.EMDVersion_id,
                callback: function(){
                    wnd.loadVersions(record);
                }
            };

            if (data.isMOSign) signParams.isMOSign = data.isMOSign;
            getWnd('swEMDSignWindow').show(signParams);
        }
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
                xtype: 'panel',
                width: 250,
                layout: 'anchor'
            },
            layout: 'hbox',
            items: [
                {
                    width: 230,
                    items: [{
                        xtype: 'swLpuCombo',
                        fieldLabel: 'МО',
                        name: 'Lpu_id',
                        reference: 'Lpu_id',
                        allowBlank: false,
                        disabled: !isUserGroup('SuperAdmin'),
                        anchor: '-5',
                        plugins: [ new Ext6.ux.Translit(true, false) ],
                        listeners: {
                            select: function(model, record, index) {
                                if (record && record.get('Lpu_id')) {

                                    var filter = wnd.FilterPanel.getForm();

                                    var LpuBuildingCombo = filter.findField('LpuBuilding_id');
                                    LpuBuildingCombo.getStore().load({
                                        params: {
                                            where: 'where Lpu_id = ' + record.get('Lpu_id')
                                        },
                                        callback: function(){
                                            if (wnd.LpuBuilding_id) {
                                                LpuBuildingCombo.select(wnd.LpuBuilding_id);
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
                        anchor: '-5'
                    }]
                },
                {
                    width: 220,
                    items: [
                        Ext6.create('Ext6.date.RangeField', {
                            fieldLabel: 'Дата документа',
                            name: 'EMDRegistry_EMDDate_period',
                            anchor: '-5',
                            listeners: {},
                            loadDefaultValue: function(){

                                var menu = this.getMenu(true);

                                var today = getGlobalOptions().date;
                                menu.fireEvent('apply', this, /*menu.getDates()*/[today,today]);
                            }
                        }),
                        Ext6.create('Ext6.date.RangeField', {
                            fieldLabel: 'Дата регистрации',
                            name: 'EMDVersion_RegistrationDate_period',
                            anchor: '-5'
                        })]
                },
                {
                    width: 230,
                    items: [{
                        xtype: 'textfield',
                        fieldLabel: 'Номер',
                        name: 'EMDRegistry_Num',
                        anchor: '-5',
                        width: 150
                    },{
                        xtype: 'textfield',
                        fieldLabel: 'ФИО пациента',
                        plugins: [ new Ext6.ux.Translit(true, false) ],
                        name: 'Person_FIO',
                        anchor: '-5'
                    }]
                },{
                    width: 300,
                        items: [{
                            xtype: 'swEMDDocumentTypeLocal',
                            name: 'EMDDocumentTypeLocal_id',
                            reference: 'EMDDocumentTypeLocal_id',
                            anchor: '-5'
                        }, {
                        border: false,
                        cls: 'panel-80',
                        layout: 'column',
                        style: 'margin-top: 35px;',
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
                }, {
                    width: 300,
                    items: [
                        {
                            border: false,
                            cls: 'panel-80',
                            layout: 'column',
                            style: 'margin-top: 27px;',
                            anchor: '-20',
                            items: [
                                {
                                    xtype: 'checkbox',
                                    boxLabel: 'Нужна подпись МО',
                                    fieldLabel: 'Нужна подпись МО',
                                    hideLabel: true,
                                    name: 'isLpuSignNeeded',
                                    style: 'margin-right: 10px;'
                                },
                                {
                                    xtype: 'checkbox',
                                    boxLabel: 'Без регистрации',
                                    fieldLabel: 'Без регистрации',
                                    hideLabel: true,
                                    listeners: {
										'change': function(checkbox, checked) {
											var filter = wnd.FilterPanel.getForm();
											if (checked) {
												filter.findField('EMDVersion_RegistrationDate_period').clear();
												filter.findField('EMDVersion_RegistrationDate_period').disable();
											} else {
												filter.findField('EMDVersion_RegistrationDate_period').enable();
											}
										}
                                    },
                                    name: 'isWithoutRegistration'
                                }
                            ]
                        }]
                }
            ]
        });

        wnd.RegistryTitleBar = Ext6.create('Ext6.Panel', {
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
                            html: 'Электронные медицинские документы'
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
                            handler: function () {
                                wnd.doLoad({
                                    grid: wnd.RegistryGrid,
                                    loadParams: {}
                                });
                            }
                        },
                        {
                            iconCls: 'panicon-print',
                            tooltip: langs('Печать'),
							handler: function() {
								Ext6.ux.GridPrinter.print(wnd.RegistryGrid);
							}
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

        wnd.RegistryGrid = Ext6.create('Ext6.grid.Panel', {
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
			bbar: {
				xtype: 'pagingtoolbar',
				displayInfo: true
			},
            listeners: {},
            store: {
                fields: [
                    { name: 'EMDRegistry_id', type: 'int' },
                    { name: 'EMDRegistry_ObjectName', type: 'string' },
                    { name: 'EMDRegistry_ObjectID', type: 'int' },
                    { name: 'EMDDocumentTypeLocal_Name', type: 'string' },
                    { name: 'EMDRegistry_Num', type: 'string' },
                    { name: 'EMDRegistry_EMDDate', type: 'date', dateFormat: 'd.m.Y' },
                    { name: 'Person_id', type: 'string'},
                    { name: 'Person_FIO', type: 'string'},
                    { name: 'Person_BirthDay', type: 'date', dateFormat: 'd.m.Y' },
                    { name: 'pmUser_insID', type: 'int'},
                    { name: 'IsSigned', type: 'int'}
                ],
				pageSize: 100,
                proxy: {
                    type: 'ajax',
                    actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
                    url: '/?c=EMD&m=EMDSearch',
                    reader: {
                        type: 'json',
						rootProperty: 'data',
						totalProperty: 'totalCount'
                    }
                },
                sorters: [{
                    property: 'EMDRegistry_EMDDate',
                    direction: 'DESC'
                }],
                listeners: {}
            },
            columns: [
                {text: 'Номер', width: 200, dataIndex: 'EMDRegistry_Num'},
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
                {text: 'Вид документа', width: 200, dataIndex: 'EMDDocumentTypeLocal_Name'},
                {text: 'Дата', width: 120, dataIndex: 'EMDRegistry_EMDDate', renderer: Ext6.util.Format.dateRenderer('d.m.Y')},
                {text: 'Пациент', minWidth: 300, dataIndex: 'Person_FIO', flex: 1},
                {text: 'Дата рождения', width: 120, dataIndex: 'Person_BirthDay', renderer: Ext6.util.Format.dateRenderer('d.m.Y')},
				{text: 'Врач', width: 200, dataIndex: 'MedPersonal_Fio'},
				{text: 'Место работы', width: 200, dataIndex: 'LpuSection_Name'},
				{text: 'Примечание', width: 200, dataIndex: 'EMDRegistry_Descr'}
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
                            'color': 'transparent;background: rgb(238, 238, 238) !important;'
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
                                var record = wnd.RegistryGrid.getSelectionModel().getSelectedRecord();
                                wnd.loadVersions(record);
                            }
                        },
                        {
                            iconCls: 'panicon-print',
                            tooltip: langs('Печать'),
                            handler: function() {
								var win_id = 'printEMDVersions' + Math.floor(Math.random() * 10000);
								var win = window.open('', win_id);
								win.document.write(wnd.VersionGrid.body.dom.innerHTML);
								win.document.close();
                            }
                        }
                    ]
                })]
        });

        Ext6.apply(wnd, {
            layout: 'border',
            referenceHolder: true,
            reference: 'swEMDSearchWindow_' + wnd.id,
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
                                wnd.RegistryTitleBar,
                                wnd.RegistryGrid
                            ]
                        }),
                        new Ext6.Panel({
                            region: 'east',
                            width: '40%',
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

