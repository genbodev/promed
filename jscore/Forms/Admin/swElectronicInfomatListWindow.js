/**
 * swElectronicInfomatListWindow - инфомат
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
sw.Promed.swElectronicInfomatListWindow = Ext.extend(sw.Promed.BaseForm,
    {
        maximizable: false,
        maximized: true,
        height: 600,
        width: 900,
        id: 'swElectronicInfomatListWindow',
        title: 'Справочник инфоматов',
        layout: 'border',
        resizable: true,
        deleteElectronicInfomat: function() {
            var wnd = this,
                grid = this.ElectronicInfomatGrid.getGrid();

            if (!grid.getSelectionModel().getSelected()
                || !grid.getSelectionModel().getSelected().get('ElectronicInfomat_id')) {
                return false;
            }

            var ElectronicInfomat_id = grid.getSelectionModel().getSelected().get('ElectronicInfomat_id');

            sw.swMsg.show({
                buttons: Ext.Msg.YESNO,
                fn: function (buttonId, text, obj) {
                    if (buttonId == 'yes') {
                        Ext.Ajax.request({
                            callback: function(opt, scs, response) {
                                wnd.doSearch();
                            },
                            params: {ElectronicInfomat_id: ElectronicInfomat_id},
                            url: '/?c=ElectronicInfomat&m=delete'
                        });
                    }
                },
                icon: Ext.MessageBox.QUESTION,
                msg: lang['udalit_vyibrannuyu_zapis'],
                title: lang['vopros']
            });

        },
        openElectronicInfomatEditWindow: function(action) {
            var wnd = this,
                grid = this.ElectronicInfomatGrid.getGrid();

            var params = new Object();
            params.action = action;

            if (action != 'add') {
                if (!grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('ElectronicInfomat_id')) {
                    return false;
                }

                params.ElectronicInfomat_id = grid.getSelectionModel().getSelected().get('ElectronicInfomat_id');
            }

            params.callback = function() {
                wnd.doSearch();
            };

            getWnd('swElectronicInfomatEditWindow').show(params);
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
            wnd.ElectronicInfomatGrid.loadData({globalFilters: params});
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
                                                name: 'ElectronicInfomat_Code',
                                                fieldLabel: 'Код'
                                            }, {
                                                xtype: 'textfield',
                                                name: 'ElectronicInfomat_Name',
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
                                                    url:'/?c=ElectronicInfomat&m=loadAllRelatedLpu'
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
                                                name: 'ElectronicInfomat_WorkRange',
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

            this.ElectronicInfomatGrid = new sw.Promed.ViewFrame({
                id: wnd.id+'ElectronicInfomatGrid',
                title:'',
                object: 'ElectronicInfomat',
                dataUrl: '/?c=ElectronicInfomat&m=loadList',
                autoLoadData: false,
                paging: true,
                root: 'data',
                totalProperty: 'totalCount',
                region: 'center',
                toolbar: true,
                useEmptyRecord: false,
                onRowSelect: function (sm,index,record) {

                },
                stringfields: [
                    {name: 'ElectronicInfomat_id', type: 'int', header: 'ID', key: true, hidden: false},
                    {name: 'ElectronicInfomat_Code', header: 'Код', width: 100},
                    {name: 'ElectronicInfomat_Name', header: 'Наименование', width: 200, id: 'autoexpand'},
                    {name: 'ElectronicQueues', header: 'ЭО', width: 200},
                    {name: 'Lpu_Nick', header: 'МО', width: 200},
                    {name: 'LpuBuilding_Name', header: 'Подразделение', width: 200},
                    {name: 'Infomat_Addr', header: 'Адрес', width: 160},
                    {name: 'ElectronicInfomat_begDate', header: 'Дата начала',type: 'date',  width: 100},
                    {name: 'ElectronicInfomat_endDate', header: 'Дата окончания',type: 'date', width: 100}
                ],
                actions: [
                    {name:'action_add', handler: function() { wnd.openElectronicInfomatEditWindow('add'); }},
                    {name:'action_edit', handler: function() { wnd.openElectronicInfomatEditWindow('edit'); }},
                    {name:'action_view', handler: function() { wnd.openElectronicInfomatEditWindow('view'); }},
                    {name:'action_delete', handler: function() { wnd.deleteElectronicInfomat(); }},
                    {name:'action_print', disabled: true, hidden: true}
                ]
            });

            Ext.apply(this, {
                items: [
                    wnd.FilterPanel,
                    wnd.ElectronicInfomatGrid
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

            sw.Promed.swElectronicInfomatListWindow.superclass.initComponent.apply(this, arguments);
        },
        show: function() {

            sw.Promed.swElectronicInfomatListWindow.superclass.show.apply(this, arguments);
            this.doReset();
        }
    });