/**
 * swElectronicScoreboardListWindow - электронное табло
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package     Admin
 * @access      public
 * @copyright	Copyright (c) 2017 Swan Ltd.
 */
/*NO PARSE JSON*/
sw.Promed.swElectronicScoreboardListWindow = Ext.extend(sw.Promed.BaseForm,
    {
        maximizable: false,
        maximized: true,
        height: 600,
        width: 900,
        id: 'swElectronicScoreboardListWindow',
        title: 'Справочник электронных табло',
        layout: 'border',
        resizable: true,
        deleteElectronicScoreboard: function() {
            var wnd = this,
                grid = this.ElectronicScoreboardGrid.getGrid();

            if (!grid.getSelectionModel().getSelected()
                || !grid.getSelectionModel().getSelected().get('ElectronicScoreboard_id')) {
                return false;
            }

            var ElectronicScoreboard_id = grid.getSelectionModel().getSelected().get('ElectronicScoreboard_id');

            sw.swMsg.show({
                buttons: Ext.Msg.YESNO,
                fn: function (buttonId, text, obj) {
                    if (buttonId == 'yes') {
                        Ext.Ajax.request({
                            callback: function(opt, scs, response) {
                                wnd.doSearch();
                            },
                            params: {ElectronicScoreboard_id: ElectronicScoreboard_id},
                            url: '/?c=ElectronicScoreboard&m=delete'
                        });
                    }
                },
                icon: Ext.MessageBox.QUESTION,
                msg: lang['udalit_vyibrannuyu_zapis'],
                title: lang['vopros']
            });

        },
        openElectronicScoreboardEditWindow: function(action) {
            var wnd = this,
                grid = this.ElectronicScoreboardGrid.getGrid();

            var params = new Object();
            params.action = action;

            if (action != 'add') {

                if (!grid.getSelectionModel().getSelected()
                    || !grid.getSelectionModel().getSelected().get('ElectronicScoreboard_id')
                ) {
                    return false;
                }

                params.ElectronicScoreboard_id = grid.getSelectionModel().getSelected().get('ElectronicScoreboard_id');
            }

            params.callback = function() {wnd.doSearch()};
            getWnd('swElectronicScoreboardEditWindow').show(params);
        },
        doSearch: function() {

            var wnd = this,
                filterForm = wnd.FilterPanel.getForm();

            var params = filterForm.getValues();

            params.start = 0;
            params.limit = 100;

            // т.к. поле c ЛПУ дизаблится, lpu_id в параметры передаем принудительно
            if (isLpuAdmin() && !isSuperAdmin()) {

                params.f_Lpu_id = getGlobalOptions().lpu_id;

                var lpuCombo = this.FilterPanel.getForm().findField('f_Lpu_id');
                var buildingCombo = this.FilterPanel.getForm().findField('LpuBuilding_id');

                if (buildingCombo.getValue() > 0)
                    lpuCombo.fireEvent('change', lpuCombo, params.f_Lpu_id, params.f_Lpu_id);
                else {
                    lpuCombo.fireEvent('change', lpuCombo, params.f_Lpu_id);
                }
            }

            // Ставим заголовок фильтра
            this.setTitleFieldSet();
            wnd.ElectronicScoreboardGrid.loadData({globalFilters: params});
        },

        doReset: function() {
            this.FilterPanel.getForm().reset();

            var lpuCombo = this.FilterPanel.getForm().findField('f_Lpu_id');
            var buildingCombo = this.FilterPanel.getForm().findField('LpuBuilding_id');

            buildingCombo.getStore().removeAll();

            lpuCombo.getStore().load({
                callback: function () {
                    if (isLpuAdmin() && !isSuperAdmin()) {
                        lpuCombo.setValue(getGlobalOptions().lpu_id);
                        lpuCombo.setDisabled(true);
                    }
                }
            });

            this.doSearch();
        },

        setTitleFieldSet: function() {
            var fieldSet = this.FilterPanel.find('xtype', 'fieldset')[0],
                enableFilter = false,
                title = lang['poisk_filtr'];

            fieldSet.findBy(function(f) {
                if( f.xtype && f.xtype.inlist(['textfield', 'swlpusearchcombo', 'swlpubuildingcombo', 'daterangefield']) ) {
                    if( f.getValue() != '' && f.getValue() != null ) {
                        enableFilter = true;
                    }
                }
            });

            fieldSet.setTitle( title + ( enableFilter == true ? '' : 'не ' ) + 'установлен' );
        },

        initComponent: function()
        {
            var wnd = this;

            this.FilterPanel = new Ext.form.FormPanel({
                autoHeight: true,
                region: 'north',
                frame: true,
                items: [
                    {
                        layout: 'form',
                        xtype: 'fieldset',
                        autoHeight: true,
                        collapsible: true,
                        listeners: {
                            collapse: function() {
                                this.FilterPanel.doLayout();
                                this.doLayout();
                            }.createDelegate(this),
                            expand: function() {
                                this.FilterPanel.doLayout();
                                this.doLayout();
                            }.createDelegate(this)
                        },
                        labelAlign: 'right',
                        title: lang['poisk_filtr_ne_ustanovlen'],
                        items: [
                            {
                                layout: 'column',
                                items: [
                                    {
                                        layout: 'form',
                                        defaults: {
                                            anchor: '100%'
                                        },
                                        labelWidth: 100,
                                        width: 250,
                                        items: [
                                            {
                                                xtype: 'textfield',
                                                name: 'ElectronicScoreboard_Code',
                                                fieldLabel: 'Код'
                                            }, {
                                                xtype: 'textfield',
                                                name: 'ElectronicScoreboard_Name',
                                                fieldLabel: 'Наименование'
                                            },
                                        ]
                                    }, {
                                        layout: 'form',
                                        width: 320,
                                        defaults: {
                                            anchor: '100%'
                                        },
                                        items: [
                                            new sw.Promed.SwBaseLocalCombo ({
                                                hiddenName: 'f_Lpu_id',
                                                listWidth: 320,
                                                width: 320,
                                                displayField: 'Lpu_Nick',
                                                valueField: 'Lpu_id',
                                                editable: true,
                                                fieldLabel: lang['mo'],
                                                tpl: new Ext.XTemplate(
                                                    '<tpl for="."><div class="x-combo-list-item">',
                                                    '{Lpu_Nick}&nbsp;',
                                                    '</div></tpl>'
                                                ),
                                                store: new Ext.data.SimpleStore({
                                                    autoLoad: false,
                                                    fields: [
                                                        {name: 'Lpu_id', mapping: 'Lpu_id'},
                                                        {name: 'Lpu_Name', mapping: 'Lpu_Name'},
                                                        {name: 'Lpu_Nick', mapping: 'Lpu_Nick'},
                                                    ],
                                                    key: 'Lpu_id',
                                                    sortInfo: { field: 'Lpu_Nick' },
                                                    url:'/?c=ElectronicScoreboard&m=loadAllRelatedLpu'
                                                }),
                                                listeners: {
                                                    'change': function (combo, newValue, oldValue) {

                                                        var buildingCombo = wnd.FilterPanel.getForm().findField('LpuBuilding_id');

                                                        if (!newValue) {
                                                            buildingCombo.clearValue();
                                                            buildingCombo.getStore().removeAll();
                                                        }
                                                        else if (newValue != oldValue) {

                                                            buildingCombo.clearValue();
                                                            buildingCombo.getStore().baseParams.Lpu_id = newValue;
                                                            buildingCombo.getStore().load();
                                                            //buildingCombo.getStore().load({
                                                            //    params: {Lpu_id:newValue}
                                                            //});

                                                        } else {
                                                            return false;
                                                        }
                                                    }
                                                }

                                            }),
                                            {
                                                xtype: 'swlpubuildingcombo',
                                                fieldLabel: 'Подразделение',
                                                hiddenName: 'LpuBuilding_id',
                                                listWidth: 320,
                                                width: 320
                                            }
                                        ]
                                    }, {
                                        layout: 'form',
                                        width: 300,
                                        labelWidth: 100,
                                        defaults: {
                                            anchor: '100%'
                                        },
                                        items: [
                                            {
                                                name: 'ElectronicScoreboard_WorkRange',
                                                fieldLabel: 'Период работы',
                                                xtype: 'daterangefield',
                                                plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
                                            }
                                        ]
                                    }
                                ]
                            }, {
                                layout: 'column',
                                style: 'padding: 3px;',
                                items: [
                                    {
                                        layout: 'form',
                                        items: [
                                            {
                                                handler: function() {
                                                    this.doSearch();
                                                }.createDelegate(this),
                                                xtype: 'button',
                                                iconCls: 'search16',
                                                text: BTN_FRMSEARCH
                                            }
                                        ]
                                    }, {
                                        layout: 'form',
                                        style: 'margin-left: 5px;',
                                        items: [
                                            {
                                                handler: function() {
                                                    this.doReset();
                                                }.createDelegate(this),
                                                xtype: 'button',
                                                iconCls: 'resetsearch16',
                                                text: lang['sbros']
                                            }
                                        ]
                                    }
                                ]
                            }
                        ]
                    }
                ],
                keys: [{
                    fn: function(inp, e) {
                        this.doSearch();
                    },
                    key: [ Ext.EventObject.ENTER ],
                    scope: this,
                    stopEvent: true
                }]
            });

            this.ElectronicScoreboardGrid = new sw.Promed.ViewFrame({
                id: wnd.id+'ElectronicScoreboardGrid',
                title:'',
                object: 'ElectronicScoreboard',
                dataUrl: '/?c=ElectronicScoreboard&m=loadList',
                autoLoadData: false,
                paging: true,
                root: 'data',
                totalProperty: 'totalCount',
                region: 'center',
                toolbar: true,
                useEmptyRecord: false,
                onRowSelect: function (sm,index,record) {
                    if (record.get('ElectronicScoreboard_IsLED') === '2') {
                        this.setActionDisabled('action_browser_refresh', true);
                    } else {
                        this.setActionDisabled('action_browser_refresh', false);
                    }
                },
                stringfields: [
                    {name: 'ElectronicScoreboard_id', type: 'int', header: 'ID', key: true, hidden: false},
                    {name: 'ElectronicScoreboard_Code', header: 'Код', width: 100},
                    {name: 'ElectronicScoreboard_IsLED', header: 'Светодиодное', width: 100, hidden: true},
                    {name: 'ElectronicScoreboard_Type', header: 'Тип', width: 100},
                    {name: 'ElectronicScoreboard_Name', header: 'Наименование', width: 200, id: 'autoexpand'},
                    {name: 'ElectronicQueues', header: 'ЭО', width: 200},
                    {name: 'Lpu_Nick', header: 'МО', width: 200},
                    {name: 'LpuBuilding_Name', header: 'Подразделение', width: 200},
                    {name: 'ElectronicScoreboard_IPaddress', header: 'IP-Адрес', width: 100},
                    {name: 'ElectronicScoreboard_Port', header: 'Порт', width: 50},
                    {name: 'Scoreboard_Addr', header: 'Адрес', width: 160},
                    {name: 'ElectronicScoreboard_begDate', header: 'Дата начала', width: 100},
                    {name: 'ElectronicScoreboard_endDate', header: 'Дата окончания', width: 100}
                ],
                actions: [
                    {name:'action_add', handler: function() { wnd.openElectronicScoreboardEditWindow('add'); }},
                    {name:'action_edit', handler: function() { wnd.openElectronicScoreboardEditWindow('edit'); }},
                    {name:'action_view', handler: function() { wnd.openElectronicScoreboardEditWindow('view'); }},
                    {name:'action_delete', handler: function() { wnd.deleteElectronicScoreboard(); }},
                    {name:'action_print', disabled: true, hidden: true}
                ]
            });

            Ext.apply(this, {
                items: [
                    wnd.FilterPanel,
                    wnd.ElectronicScoreboardGrid
                ],
                buttons: [{
                    text: '-'
                },
                    HelpButton(this, TABINDEX_RRLW + 13),
                    {
                        iconCls: 'close16',
                        tabIndex: TABINDEX_RRLW + 14,
                        handler: function() {
                            wnd.hide();
                        },
                        text: BTN_FRMCLOSE
                    }]
            });

            sw.Promed.swElectronicScoreboardListWindow.superclass.initComponent.apply(this, arguments);
        },
        show: function() {

            var win = this;

            this.ElectronicScoreboardGrid.addActions({
                name:'action_browser_refresh',
                text: 'Обновить экран',
                handler: function() {
                    win.refreshScoreboardBrowserPage();
                }
            });

            sw.Promed.swElectronicScoreboardListWindow.superclass.show.apply(this, arguments);
            this.doReset();
        },
        refreshScoreboardBrowserPage: function(){

            var win = this,
                grid = this.ElectronicScoreboardGrid.getGrid();

            var record = grid.getSelectionModel().getSelected();
            if (!record || !record.get('ElectronicScoreboard_id')) {
                return false;
            }

            var ElectronicScoreboard_id = record.get('ElectronicScoreboard_id');

            Ext.Ajax.request({
                callback: function(opt, scs, response) {
                    log('scoreboard browser page is updated', response);
                },
                params: {
                    ElectronicScoreboard_id: ElectronicScoreboard_id
                },
                url: '/?c=ElectronicScoreboard&m=refreshScoreboardBrowserPage'
            });
        }
    });