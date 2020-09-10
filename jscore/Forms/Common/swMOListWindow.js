/**
 * Created with JetBrains PhpStorm.
 * User: Shorev
 * Date: 15.10.14
 * Time: 14:46
 * To change this template use File | Settings | File Templates.
 */
sw.Promed.swMOListWindow = Ext.extend(sw.Promed.BaseForm,
    {
        objectName: 'swMOListWindow',
        objectSrc: '/jscore/Forms/Common/swMOListWindow.js',
        closable: true,
        closeAction: 'hide',
        layout: 'border',
        maximized: true,
        title: lang['pasport_mo_spisok'],
        iconCls: 'admin16',
        id: 'swMOListWindow',
        show: function()
        {
            sw.Promed.swMOListWindow.superclass.show.apply(this, arguments);

            var loadMask = new Ext.LoadMask(Ext.get('swMOListWindow'), {msg: LOAD_WAIT});
            loadMask.show();
            var form = this;

            form.loadGridWithFilter(true);

            loadMask.hide();

        },
        clearFilters: function ()
        {
            this.findById('molwOrg_Nick').setValue('');
            this.findById('molwOrg_Name').setValue('');
        },
        loadGridWithFilter: function(clear)
        {
            var form = this;
            if (clear)
                form.clearFilters();
            var OrgNick = this.findById('molwOrg_Nick').getValue();
            var OrgName = this.findById('molwOrg_Name').getValue();
            var filters = {Nick: OrgNick, Name: OrgName, start: 0, limit: 100, mode: 'lpu'};
            form.LpuGrid.loadData({globalFilters: filters});
        },
        initComponent: function()
        {
            var form = this;
            this.LpuFilterPanel = new Ext.form.FieldSet(
                {
                    bodyStyle:'width:100%;background:#DFE8F6;padding:0px;',
                    border: true,
                    autoHeight: true,
                    region: 'north',
                    layout: 'column',
                    title: lang['filtryi'],
                    id: 'MOilterPanel',
                    items:
                        [{
                            // Левая часть фильтров
                            labelAlign: 'top',
                            layout: 'form',
                            border: false,
                            bodyStyle:'background:#DFE8F6;padding-right:5px;',
                            columnWidth: .44,
                            items:
                                [{
                                    name: 'Org_Name',
                                    anchor: '100%',
                                    disabled: false,
                                    fieldLabel: lang['naimenovanie_organizatsii'],
                                    tabIndex: 0,
                                    xtype: 'textfield',
                                    id: 'molwOrg_Name'
                                },
                                    {
                                        xtype: 'hidden',
                                        anchor: '100%'
                                    }]
                        },
                            {
                                // Средняя часть фильтров
                                labelAlign: 'top',
                                layout: 'form',
                                border: false,
                                bodyStyle:'background:#DFE8F6;padding-left:5px;',
                                columnWidth: .44,
                                items:
                                    [{
                                        name: 'Org_Nick',
                                        anchor: '100%',
                                        disabled: false,
                                        fieldLabel: lang['kratkoe_naimenovanie'],
                                        tabIndex: 0,
                                        xtype: 'textfield',
                                        id: 'molwOrg_Nick'
                                    },
                                        {
                                            xtype: 'hidden',
                                            anchor: '100%'
                                        }]
                            },
                            {
                                // Правая часть фильтров (кнопка)
                                layout: 'form',
                                border: false,
                                bodyStyle:'background:#DFE8F6;padding-left:5px;',
                                columnWidth: .12,
                                items:
                                    [{
                                        xtype: 'button',
                                        text: lang['ustanovit'],
                                        tabIndex: 4217,
                                        minWidth: 110,
                                        disabled: false,
                                        topLevel: true,
                                        allowBlank:true,
                                        id: 'molwButtonSetFilter',
                                        handler: function ()
                                        {
                                            Ext.getCmp('swMOListWindow').loadGridWithFilter();
                                        }
                                    },
                                        {
                                            xtype: 'button',
                                            text: lang['otmenit'],
                                            tabIndex: 4218,
                                            minWidth: 110,
                                            disabled: false,
                                            topLevel: true,
                                            allowBlank:true,
                                            id: 'molwButtonUnSetFilter',
                                            handler: function ()
                                            {
                                                Ext.getCmp('swMOListWindow').loadGridWithFilter(true);
                                            }
                                        }]
                            }],
                    keys: [{
                        key: [
                            Ext.EventObject.ENTER
                        ],
                        fn: function(inp, e) {
                            e.stopEvent();

                            if ( e.browserEvent.stopPropagation )
                                e.browserEvent.stopPropagation();
                            else
                                e.browserEvent.cancelBubble = true;

                            if ( e.browserEvent.preventDefault )
                                e.browserEvent.preventDefault();
                            else
                                e.browserEvent.returnValue = false;

                            e.browserEvent.returnValue = false;
                            e.returnValue = false;

                            if (Ext.isIE)
                            {
                                e.browserEvent.keyCode = 0;
                                e.browserEvent.which = 0;
                            }

                            Ext.getCmp('swMOListWindow').loadGridWithFilter();
                        },
                        stopEvent: true
                    }]
                });

            // Организации
            this.LpuGrid = new sw.Promed.ViewFrame(
                {
                    id: 'molwLpuGridPanel',
                    tbar: this.gridToolbar,
                    region: 'center',
                    layout: 'fit',
                    paging: true,
                    object: 'Org',
                    dataUrl: '/?c=Org&m=getOrgView',
                    keys: [{
                        key: [
                            Ext.EventObject.F6
                        ],
                        fn: function(inp, e) {
                            e.stopEvent();

                            if ( e.browserEvent.stopPropagation )
                                e.browserEvent.stopPropagation();
                            else
                                e.browserEvent.cancelBubble = true;

                            if ( e.browserEvent.preventDefault )
                                e.browserEvent.preventDefault();
                            else
                                e.browserEvent.returnValue = false;

                            e.browserEvent.returnValue = false;
                            e.returnValue = false;

                            if (Ext.isIE)
                            {
                                e.browserEvent.keyCode = 0;
                                e.browserEvent.which = 0;
                            }
                            var grid = Ext.getCmp('molwLpuGridPanel');
                            if (!grid.getAction('action_new').isDisabled()) {
                                if (e.altKey) {
                                    AddRecordToUnion(
                                        grid.getGrid().getSelectionModel().getSelected(),
                                        'Org',
                                        lang['organizatsii'],
                                        function () {
                                            grid.loadData();
                                        }
                                    )
                                }
                            }
                        },
                        stopEvent: true
                    }],
                    //toolbar: true,
                    root: 'data',
                    totalProperty: 'totalCount',
                    autoLoadData: false,
                    stringfields:
                        [
                            // Поля для отображение в гриде
                            {name: 'Org_id', type: 'int', header: 'ID', key: true},
                            {name: 'Lpu_id', type: 'int', header: lang['id_lpu'], key: true},
                            {name: 'Org_IsAccess', type:'checkbox', header: lang['dostup_v_sistemu'], width: 60},
                            {name: 'DLO', type:'checkbox', header: lang['llo'], width: 40},
                            {name: 'OMS', type:'checkbox', header: lang['oms'], width: 40},
                            {id: 'Lpu_Ouz', name: 'Lpu_Ouz', header: lang['kod_ouz'], width: 80},
                            {name: 'Org_Name', id: 'autoexpand', header: lang['polnoe_naimenovanie']},
                            {name: 'Org_Nick', header: lang['kratkoe_naimenovanie'], width: 240},
                            {name: 'KLArea_Name', header: lang['territoriya'], width: 160},
                            {name: 'Org_OGRN', header: lang['ogrn'], width: 120},
                            {name: 'Lpu_begDate', header: lang['data_nachala_deyatelnosti'], width: 80},
                            {name: 'Lpu_endDate', header: lang['data_zakryitiya'], width: 80},
                            // Поля для отображения в дополнительной панели
                            {name: 'UAddress_Address', hidden: true},
                            {name: 'PAddress_Address', hidden: true}
                        ],
                    actions:
                        [
                            {name:'action_add', hidden: true},
                            {name:'action_edit', iconCls : 'x-btn-text', icon: 'img/icons/lpu16.png', text: lang['pasport_mo'], handler: function()
                            {
                                this.Lpu_id = Ext.getCmp('molwLpuGridPanel').ViewGridPanel.getSelectionModel().getSelected().get('Lpu_id');
                                getWnd('swLpuPassportEditWindow').show({
                                    action: 'view',
                                    Lpu_id: this.Lpu_id
                                });
                            }
                            },
                            {name:'action_view', iconCls : 'x-btn-text', icon: 'img/icons/lpu-struc16.png', text: lang['struktura_mo'], handler: function()
								{
									this.Lpu_id = Ext.getCmp('molwLpuGridPanel').ViewGridPanel.getSelectionModel().getSelected().get('Lpu_id');
									getWnd('swLpuStructureViewForm').show({
										Lpu_id: this.Lpu_id,
										action:'view'
									});
								}
							},
                            {name:'action_delete', hidden: true},
                            {name:'action_refresh'},
                            {name:'action_print'}
                        ],
                    onRowSelect: function(sm,index,record)
                    {
                        var win = Ext.getCmp('swMOListWindow');
                        var form = Ext.getCmp('molwLpuGridPanel');
                        if ( win.mode && win.mode == 'lpu')
                        {
                            var Lpu_id = form.ViewGridPanel.getSelectionModel().getSelected().get('Lpu_id');
                            form.getAction('action_edit').setDisabled( Lpu_id != getGlobalOptions().lpu_id && !isSuperAdmin() );
                            form.getAction('action_view').setDisabled( Lpu_id != getGlobalOptions().lpu_id && !isSuperAdmin() );
                        }
                        var UAddress_Address = record.get('UAddress_Address');
                        var PAddress_Address = record.get('PAddress_Address');
                        win.LpuDetailTpl.overwrite(win.LpuDetailPanel.body, {UAddress_Address:UAddress_Address, PAddress_Address:PAddress_Address});
                    }
                });

            this.LpuGrid.getGrid().view = new Ext.grid.GridView(
                {
                    getRowClass : function (row, index)
                    {
                        var cls = '';
                        if (row.get('Lpu_endDate')!=null && row.get('Lpu_endDate').length > 0)
                            cls = cls+'x-grid-rowgray ';
                        return cls;
                    }
                });

            var LpuDetailTplMark =
                [
                    '<div style="height:44px;">'+
                        '<div>Юридический адрес: <b>{UAddress_Address}</b></div>'+
                        '<div>Фактический адрес: <b>{PAddress_Address}</b></div>'+
                        '</div>'
                ];
            this.LpuDetailTpl = new Ext.Template(LpuDetailTplMark);
            this.LpuDetailPanel = new Ext.Panel(
                {
                    id: 'molwLpuDetailPanel',
                    bodyStyle: 'padding:2px',
                    layout: 'fit',
                    region: 'south',
                    border: true,
                    frame: true,
                    height: 44,
                    maxSize: 44,
                    html: ''
                });

            Ext.apply(this,
                {
                    layout: 'border',
                    items:
                        [
                            this.LpuFilterPanel,
                            {
                                layout: 'fit',
                                region: 'center',
                                border: false,
                                items:
                                    [
                                        this.LpuGrid
                                    ]
                            },
                            this.LpuDetailPanel
                        ],
                    buttons:
                        [{
                            text: '-'
                        },
                            HelpButton(this, TABINDEX_MPSCHED + 98),
                            {
                                iconCls: 'cancel16',
                                text: BTN_FRMCLOSE,
                                handler: function() {this.hide();}.createDelegate(this)
                            }]
                });

            sw.Promed.swMOListWindow.superclass.initComponent.apply(this, arguments);
        }
    });