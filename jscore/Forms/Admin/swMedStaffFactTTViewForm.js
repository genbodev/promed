/**
* swMedStaffFactTTViewForm - окно просмотра штатного расписания
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright © 2009 Swan Ltd.
* @version      4.04.2012
*/

sw.Promed.swMedStaffFactTTViewForm = Ext.extend(sw.Promed.BaseForm, {
	title:lang['shtatnoe_raspisanie'],
	layout: 'border',
	id: 'MedStaffFactTTViewForm',
	maximized: true,
	maximizable: true,
	buttonAlign : "right",
	buttons:
	[
		{
			text: BTN_FRMHELP,
			iconCls: 'help16',
			handler: function(button, event)
			{
				ShowHelp(this.ownerCt.title);
			}
		},
		{
			text      : BTN_FRMCLOSE,
			tabIndex  : -1,
			tooltip   : lang['zakryit'],
			iconCls   : 'cancel16',
			handler   : function()
			{
				this.ownerCt.hide();
			}
		}
	],
	show: function()
	{
		sw.Promed.swMedStaffFactTTViewForm.superclass.show.apply(this, arguments);

		
	},
    addCloseFilterMenu: function(gridCmp){
        var form = this;
        var grid = gridCmp;

        if ( !grid.getAction('action_isclosefilter_'+grid.id) ) {
            var menuIsCloseFilter = new Ext.menu.Menu({
                items: [
                    new Ext.Action({
                        text: lang['vse'],
                        handler: function() {
                            if (grid.gFilters) {
                                grid.gFilters.isClose = null;
                            }
                            grid.getAction('action_isclosefilter_'+grid.id).setText(lang['pokazyivat_vse']);
                            grid.getGrid().getStore().baseParams.isClose = null;
                            grid.getGrid().getStore().reload();
                        }
                    }),
                    new Ext.Action({
                        text: lang['otkryityie'],
                        handler: function() {
                            if (grid.gFilters) {
                                grid.gFilters.isClose = 1;
                            }
                            grid.getAction('action_isclosefilter_'+grid.id).setText(lang['pokazyivat_otkryityie']);
                            grid.getGrid().getStore().baseParams.isClose = 1;
                            grid.getGrid().getStore().reload();
                        }
                    }),
                    new Ext.Action({
                        text: lang['zakryityie'],
                        handler: function() {
                            if (grid.gFilters) {
                                grid.gFilters.isClose = 2;
                            }
                            grid.getAction('action_isclosefilter_'+grid.id).setText(lang['pokazyivat_zakryityie']);
                            grid.getGrid().getStore().baseParams.isClose = 2;
                            grid.getGrid().getStore().reload();
                        }
                    })
                ]
            });

            grid.addActions({
                isClose: 1,
                name: 'action_isclosefilter_'+grid.id,
                text: lang['pokazyivat_otkryityie'],
                menu: menuIsCloseFilter
            });
            grid.getGrid().getStore().baseParams.isClose = 1;
            grid.getGrid().getStore().reload();
        }

        return true;
    },
	initComponent: function()
	{
		var form = this;
        var swStaffTTFilterPanel = new Ext.form.FormPanel({
            id: 'MedStaffFactTTFilter',
            labelAlign: 'right',
            region: 'north',
            height: 30,
            frame: true,
            validateSearchForm: function() {
                var form = this;
                var base_form = this.getForm();
                var msg = ERR_INVFIELDS_MSG;

                if (!base_form.isValid()) {
                    if (!base_form.findField('Staff_Date_range').validate() || !base_form.findField('Staff_endDate_range').validate()) {
                        msg = lang['trebuetsya_ukazat_period'];
                    }
                    sw.swMsg.show({
                        buttons: Ext.Msg.OK,
                        fn: function()
                        {
                            form.getFirstInvalidEl().focus(false);
                        },
                        icon: Ext.Msg.WARNING,
                        msg: msg,
                        title: ERR_INVFIELDS_TIT
                    });
                    return false;
                }
                return true;
            },
            doReset: function() {
                this.getForm().reset();
            },
            doSearch: function() {
                var gridPanel = Ext.getCmp('MedStaffFactTT');
                var base_form = Ext.getCmp('MedStaffFactTTFilter').getForm();

                var params = base_form.getValues();
                if (gridPanel.getGrid().getStore().baseParams.isClose) {
                    params.isClose = gridPanel.getGrid().getStore().baseParams.isClose;
                }
                gridPanel.getGrid().getStore().baseParams = params;

                gridPanel.getGrid().getStore().load();
            },
            items: [{
                xtype: 'fieldset',
                title: lang['filtr'],
                style: 'padding: 5px;',
                autoHeight: true,
                collapsible: true,
                collapsed: true,
                keys: [{
                    key: Ext.EventObject.ENTER,
                    fn: function(e) {
                        var form = this.findById('MedStaffFactTTFilter');
                        if (form.validateSearchForm()) {
                            form.doSearch();
                        }
                    }.createDelegate(this),
                    stopEvent: true
                }, {
                    ctrl: true,
                    fn: function(inp, e) {
                        var form = this.findById('MedStaffFactTTFilter');
                        form.doReset();
                    }.createDelegate(this),
                    key: 188,
                    scope: this,
                    stopEvent: true
                }],
                listeners:{
                    expand:function () {
                        this.ownerCt.setHeight(140);
                        this.ownerCt.ownerCt.syncSize();
                    },
                    collapse:function () {
                        this.ownerCt.setHeight(30);
                        this.ownerCt.ownerCt.syncSize();
                    }
                },
                items: [{
                    name: 'LpuBuilding_id',
                    xtype: 'hidden'
                }, {
                    name: 'LpuUnit_id',
                    xtype: 'hidden'
                }, {
                    name: 'LpuSection_id',
                    xtype: 'hidden'
                }, {
                    layout: 'column',
                    items: [{
                        layout: 'form',
                        width: 285,
                        labelWidth: 80,
                        items: [{
                            fieldLabel: lang['uroven_lpu'],
                            hiddenName: 'LpuStructure_id',
                            width: 200,
                            xtype: 'swlpustructureelementcombo',
                            listeners: {
                                'select': function(combo,record,value) {
                                    var base_form = Ext.getCmp('MedStaffFactTTFilter').getForm();

                                    var object = record.get('LpuStructure_Nick');
                                    var object_id = record.get('LpuStructureElement_id');

                                    if (!object.inlist(['LpuBuilding','LpuUnit','LpuSection'])) {
                                        return;
                                    }

                                    base_form.findField('LpuBuilding_id').setValue(null);
                                    base_form.findField('LpuUnit_id').setValue(null);
                                    base_form.findField('LpuSection_id').setValue(null);

                                    base_form.findField(object+'_id').setValue(object_id);
                                }
                            }
                        }]
                    }, {
                        layout: 'form',
                        width: 330,
                        labelWidth: 95,
                        items: [{
                            fieldLabel: lang['doljnost'],
                            hiddenName: 'PostMed_id',
                            width: 230,
                            xtype: 'swpostmedlocalcombo'
                        }]
                    }, {
                        layout: 'form',
                        width: 320,
                        labelWidth: 80,
                        items: [{
                            hiddenName: 'MedicalCareKind_id',
                            valueField: 'MedicalCareKind_id',
                            displayField: 'MedicalCareKind_Name',
                            fieldLabel: lang['vid_mp'],
                            store: new Ext.data.SimpleStore({
                                autoLoad: true,
                                data: [
                                    [ 1, 1, lang['pervichnaya_mediko-sanitarnaya_pomosch'] ],
                                    [ 2, 2, lang['spetsializirovannaya_meditsinskaya_pomosch'] ],
                                    [ 3, 3, lang['skoraya_meditsinskaya_pomosch'] ],
                                    [ 4, 4, lang['reabilitatsionnaya_meditsinskaya_pomosch'] ],
                                    [ 5, 5, lang['inoe'] ]
                                ],
                                fields: [
                                    { name: 'MedicalCareKind_id', type: 'int'},
                                    { name: 'MedicalCareKind_Code', type: 'int'},
                                    { name: 'MedicalCareKind_Name', type: 'string'}
                                ],
                                key: 'MedicalCareKind_id',
                                sortInfo: { field: 'MedicalCareKind_Code' }
                            }),
                            editable: false,
                            width: 230,
                            xtype: 'swbaselocalcombo'
                        }]
                    }]
                }, {
                    layout: 'column',
                    items: [{
                        xtype: 'fieldset',
                        autoHeight: true,
                        title: lang['doljnost'],
                        style: "padding: 5px 0;",
                        items: [{
                            layout: 'column',
                            width: 440,
                            items: [{
                                layout: 'form',
                                labelWidth: 10,
                                items: [{
                                    labelSeparator: '',
                                    name: 'medStaffFactDateRange',
                                    xtype: 'checkbox',
                                    listeners: {
                                        'check': function(checkbox, checked) {
                                            Ext.getCmp('MedStaffFactTTFilter').getForm().findField('Staff_Date_range').setAllowBlank(!checked);
                                        }.createDelegate(this)
                                    }
                                }]
                            }, {
                                layout: 'form',
                                labelWidth: 120,
                                items: [{
                                    fieldLabel: lang['sozdana_v_period'],
                                    xtype: 'daterangefield',
                                    plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false) ],
                                    width: 180,
                                    name: 'Staff_Date_range'
                                }]
                            }]
                        }, {
                            layout: 'column',
                            width: 440,
                            items: [{
                                layout: 'form',
                                labelWidth: 10,
                                items: [{
                                    labelSeparator: '',
                                    name: 'medStaffFactEndDateRange',
                                    xtype: 'checkbox',
                                    listeners: {
                                        'check': function(checkbox, checked) {
                                            Ext.getCmp('MedStaffFactTTFilter').getForm().findField('Staff_endDate_range').setAllowBlank(!checked);
                                        }.createDelegate(this)
                                    }
                                }]
                            }, {
                                layout: 'form',
                                labelWidth: 120,
                                items: [{
                                    fieldLabel: lang['zakryita_v_period'],
                                    xtype: 'daterangefield',
                                    plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false) ],
                                    width: 180,
                                    name: 'Staff_endDate_range'
                                }]
                            }]
                        }]
                    }, {
                        layout: 'form',
                        items: [{
                            style: "padding-left: 30px; padding-top: 10px;",
                            xtype: 'button',
                            id: 'swStaffBtnSearch',
                            text: lang['nayti'],
                            iconCls: 'search16',
                            handler: function() {
                                var form = Ext.getCmp('MedStaffFactTTFilter');
                                if (form.validateSearchForm()) {
                                    form.doSearch();
                                }
                            }
                        }, {
                            style: "padding-left: 30px; padding-top: 10px;",
                            xtype: 'button',
                            id: 'swStaffBtnClean',
                            text: lang['sbros'],
                            iconCls: 'reset16',
                            handler: function() {
                                var form = Ext.getCmp('MedStaffFactTTFilter');
                                form.doReset();
                                if (form.validateSearchForm()) {
                                    form.doSearch();
                                }
                            }
                        }]
                    }]
                }]
            }]
        });
        var swStaffOSMPanel = new sw.Promed.ViewFrame(
            {
                title: lang['organizatsionno-shtatnyie_meropriyatiya'],
                id: 'Staff',
                object: 'Staff',
                editformclassname: 'swStaffEditWindow',
                //dataUrl: '/?c=LpuStructure&m=saveStaff',
                dataUrl: '/?c=LpuStructure&m=getStaffOSMGridDetail',
                height: 303,
                toolbar: true,
                scheme: 'fed',
                autoLoadData: false,
                stringfields:
                    [
                        {name: 'Staff_id', type: 'int', header: 'ID', key: true},
                        {/*id: 'autoexpand',*/ name: 'Staff_Num', type: 'int', header: lang['nomer_shtata'], width: 200},
                        {name: 'Staff_OrgName',  type: 'string', header: lang['naimenovanie_oshm'], width: 150},
                        {name: 'Staff_OrgDT',  type: 'date', header: lang['data_oshm'], width: 75},
                        {name: 'Staff_OrgBasis',  type: 'string', header: lang['osnovanie_oshm'], width: 150}
                    ],
                actions:
                    [
                        {name: 'action_add', disabled: isMedPersView()},
                        {name: 'action_edit', disabled: isMedPersView()},
                        {name:'action_view'},
                        {name:'action_delete'},
                        {name:'action_refresh'},
                        {name:'action_print', hidden: isMedPersView()}
                    ]
            });

        this.swStaffOSMPanel = swStaffOSMPanel;

        var swStaffTTPanel = new sw.Promed.ViewFrame(
            {
                title: lang['stroki_shtatnogo_raspisaniya'],
                id: 'MedStaffFactTT',
                object: 'MedStaffFactTT',
                editformclassname: 'swMedStaffFactEditWindow',
                dataUrl: '/?c=MedPersonal&m=getStaffTTGridDetail',
                height:303,
                toolbar: true,
                autoLoadData: false,
                region: 'center',
                stringfields:
                    [
                        {name: 'Staff_id', type: 'int', header: 'ID', key: true},
                        {name: 'Lpu_id', type: 'int', hidden: true, isparams: true},
                        {name: 'LpuBuilding_id', type: 'int', hidden: true, isparams: true},
                        {name: 'LpuUnit_id', type: 'int', hidden: true, isparams: true},
                        {name: 'LpuSection_id', type: 'int', hidden: true, isparams: true},
                        {id: 'autoexpand', name: 'StructElement_Name', type: 'string', header: lang['strukturnyiy_element_mo'], width: 200},
                        {name: 'Post_Name',  type: 'string', header: lang['doljnost'], width: 150},
                        {name: 'MedicalCareKind_Name',  type: 'string', header: lang['vid_mp'], width: 150},
                        {name: 'BeginDate',  type: 'date', header: lang['data_sozdaniya'], width: 75},
                        {name: 'Staff_Comment',  type: 'string', header: lang['kommentariy'], width: 200},
                        {name: 'Staff_Rate',  type: 'float', header: lang['kolichestvo_stavok'], width: 55},
                        {name: 'Staff_RateSum',  type: 'float', header: lang['iz_nih_zanyato'], width: 55},
                        {name: 'Staff_RateCount',  type: 'float', header: lang['kolichestvo_sotrudnikov'], width: 55}
                    ],
                actions:
                    [
                        {name: 'action_add', disabled: isMedPersView(),
                            handler: function() {
                                if ( this.swStaffTTPanel.ViewGridPanel.getSelectionModel().getSelected() )
                                {
                                    //var node = swLpuStructureFrame.getSelectionModel().getSelectedNode();
                                    var Lpu_id = null;
                                    var LpuUnit_id = null;
                                    var LpuSection_id = null;
                                    var LpuBuilding_id = null;

                                    /*if ( node.attributes.object == 'LpuUnit' )
                                    {
                                        Lpu_id = node.parentNode.parentNode.parentNode.attributes.object_value;
                                        LpuBuilding_id = node.parentNode.parentNode.attributes.object_value;
                                        LpuUnit_id = node.attributes.object_value;
                                    }
                                    else if ( node.attributes.object == 'LpuSection' )
                                    {
                                        if (node.getDepth()==6)
                                        {
                                            Lpu_id = node.parentNode.parentNode.parentNode.parentNode.parentNode.attributes.object_value;
                                            LpuBuilding_id = node.parentNode.parentNode.parentNode.parentNode.attributes.object_value;
                                            LpuUnit_id = node.parentNode.parentNode.attributes.object_value;
                                            LpuSection_id = node.attributes.object_value;

                                        }
                                        else
                                        {
                                            Lpu_id = node.parentNode.parentNode.parentNode.parentNode.attributes.object_value;
                                            LpuBuilding_id = node.parentNode.parentNode.parentNode.attributes.object_value;
                                            LpuUnit_id = node.parentNode.attributes.object_value;
                                            LpuSection_id = node.attributes.object_value;
                                        }
                                    }
                                    else if ( node.attributes.object == 'Lpu' )
                                    {
                                        Lpu_id = node.attributes.object_value;
                                    }
                                    else if ( node.attributes.object == 'LpuBuilding' )
                                    {
                                        Lpu_id = node.parentNode.attributes.object_value;
                                        LpuBuilding_id = node.attributes.object_value;
                                    }
                                    var lpuStruct = {};
                                    lpuStruct.Lpu_id = String(Lpu_id) == 'null' ? null : String(Lpu_id);
                                    lpuStruct.LpuBuilding_id = String(LpuBuilding_id) == 'null' ? null : String(LpuBuilding_id);
                                    lpuStruct.LpuUnit_id = String(LpuUnit_id) == 'null' ? null : String(LpuUnit_id);
                                    lpuStruct.LpuSection_id = String(LpuSection_id) == 'null' ? null : String(LpuSection_id);
                                    lpuStruct.description = '';//node.text;
                                    window.gwtBridge.runStaffEditor(getPromedUserInfo(), null, lpuStruct, function(result) {
                                        if ( Number(result) > 0 )
                                            this.swStaffTTPanel.ViewGridPanel.getStore().reload();
                                    }.createDelegate(this));*/
                                    var lpuStruct = {};
                                    lpuStruct.Lpu_id = String(getGlobalOptions().lpu_id);
                                    lpuStruct.LpuBuilding_id = null;
                                    lpuStruct.LpuUnit_id = null;
                                    lpuStruct.LpuSection_id = null;
                                    lpuStruct.description = '';
                                    window.gwtBridge.runStaffEditor(getPromedUserInfo(), null, lpuStruct, function(result) {
                                        if ( Number(result) > 0 )
                                            this.swStaffTTPanel.ViewGridPanel.getStore().reload();
                                    }.createDelegate(this));
                                    //frms.MedStaffFactEditWindow.show({callback: frms.findById('MedStaffFact').refreshRecords, owner: frms.findById('MedStaffFact'), fields: {action: 'addinstructure', Lpu_id: Lpu_id, LpuUnit_id: LpuUnit_id, LpuSection_id: LpuSection_id}});
                                }
                            }.createDelegate(this)
                        },
                        {name: 'action_edit', text: (isMedPersView())?lang['prosmotr']:lang['izmenit'],
                            handler: function() {
                                if ( this.swStaffTTPanel.ViewGridPanel.getSelectionModel().getSelected() )
                                {
                                    var row = this.swStaffTTPanel.ViewGridPanel.getSelectionModel().getSelected();
                                    var staff_id = row.get('Staff_id');
                                    var lpuStruct = {};
                                    lpuStruct.Lpu_id = String(row.get('Lpu_id')) == 'null' ? null : String(row.get('Lpu_id'));
                                    lpuStruct.LpuBuilding_id = String(row.get('LpuBuilding_id')) == 'null' ? null : String(row.get('LpuBuilding_id'));
                                    lpuStruct.LpuUnit_id = String(row.get('LpuUnit_id')) == 'null' ? null : String(row.get('LpuUnit_id'));
                                    lpuStruct.LpuSection_id = String(row.get('LpuSection_id')) == 'null' ? null : String(row.get('LpuSection_id'));
                                    lpuStruct.description = '';
                                    lpuStruct.action = 'view';
                                    window.gwtBridge.runStaffEditor(getPromedUserInfo(), String(staff_id), lpuStruct, function(result) {
                                        if ( Number(result) > 0 )
                                            this.swStaffTTPanel.ViewGridPanel.getStore().reload();
                                    }.createDelegate(this));
                                }
                            }.createDelegate(this)
                        },
                        {name:'action_view', disabled: true, hidden: true, handler: function() {}},
                        {name:'action_delete', disabled: isMedPersView(),
                            handler: function() {
                                if ( this.swStaffTTPanel.ViewGridPanel.getSelectionModel().getSelected() )
                                {
                                    sw.swMsg.show({
                                        icon: Ext.MessageBox.QUESTION,
                                        msg: lang['vyi_hotite_udalit_zapis'],
                                        title: lang['podtverjdenie'],
                                        buttons: Ext.Msg.YESNO,
                                        fn: function(buttonId, text, obj)
                                        {
                                            if ('yes' == buttonId)
                                            {
                                                var row = this.swStaffTTPanel.ViewGridPanel.getSelectionModel().getSelected();
                                                var staff_id = row.get('Staff_id');
                                                window.gwtBridge.deleteStaff(getPromedUserInfo(), String(staff_id), function(result) {
                                                    this.swStaffTTPanel.ViewGridPanel.getStore().reload();
                                                }.createDelegate(this));
                                            }
                                        }.createDelegate(this)
                                    });
                                }
                            }.createDelegate(this)
                        },
                        {name:'action_refresh'},
                        {name:'action_print', hidden: isMedPersView()}
                    ]
            });
        swStaffTTPanel.ViewToolbar.on('render', function(vt){return this.addCloseFilterMenu(swStaffTTPanel);}.createDelegate(this));

        this.swStaffTTPanel = swStaffTTPanel;
        var swStaffTTPanel_tabs = new Ext.TabPanel(
            {
                id: 'StaffTTPanel-tabs-panel',
                autoScroll: true,
                plain: true,
                activeTab: 0,
                resizeTabs: true,
                region: 'center',
                enableTabScroll: true,
                minTabWidth: 120,
                //autoWidth: true,
                tabWidth: 'auto',
                defaults: {bodyStyle:'background:#DFE8F6;width:100%;'},
                layoutOnTabChange: true,
                //plugins: new Ext.ux.TabCloseMenu(),
                listeners:
                {
                    tabchange: function(tab, panel)
                    {
						var Lpu_id = getGlobalOptions().lpu_id;
						if(panel){
							switch(panel.id){
								case"tab_MedStaffFact":
									form.swStaffTTPanel.loadData({params:{Lpu_Name: null, Lpu_id: Lpu_id, LpuBuilding_id: null, LpuUnit_id: null, LpuSection_id: null}, globalFilters: {Lpu_id: Lpu_id, LpuBuilding_id: null, LpuUnit_id: null, LpuSection_id: null}});
									break;
								case "tab_OrgStaff":
									form.swStaffOSMPanel.loadData({params:{Lpu_id: getGlobalOptions().lpu_id, LpuBuilding_id: null, LpuUnit_id: null, LpuSection_id: null}, globalFilters: {Lpu_id:getGlobalOptions().lpu_id, LpuBuilding_id: null, LpuUnit_id: null, LpuSection_id: null}});
									break;
							}
						}
                    }
                },
                items:[{
                    title: lang['stroki_shtatnogo_raspisaniya'],
                    layout: 'border',
                    id: 'tab_MedStaffFact',
                    iconCls: 'info16',
                    //header:false,
                    border:false,
                    items: [swStaffTTFilterPanel,swStaffTTPanel]
                },{
                    title: lang['organizatsionno-shtatnyie_meropriyatiya'],
                    layout: 'fit',
                    id: 'tab_OrgStaff',
                    iconCls: 'info16',
                    //header:false,
                    border:false,
                    items: [swStaffOSMPanel]
                }]
            });
        this.swStaffTTPanel_tabs = swStaffTTPanel_tabs;
		/*this.swStaffTTPanel = new sw.Promed.ViewFrame(
		{
			id: 'MedStaffFactTT',
			object: 'MedStaffFactTT',
			editformclassname: 'swMedStaffFactEditWindow',
			dataUrl: '/?c=MedPersonal&m=getStaffTTGridDetail',
			height:303,
			toolbar: true,
			autoLoadData: false,
			stringfields:
			[
				{name: 'Staff_id', type: 'int', header: 'ID', key: true},
				{name: 'Lpu_id', type: 'int', hidden: true, isparams: true},
				{name: 'LpuBuilding_id', type: 'int', hidden: true, isparams: true},
				{name: 'LpuUnit_id', type: 'int', hidden: true, isparams: true},
				{name: 'LpuSection_id', type: 'int', hidden: true, isparams: true},
				{id: 'autoexpand', name: 'StructElement_Name', type: 'string', header: lang['strukturnyiy_element_lpu'], width: 200},
				{name: 'Post_Name',  type: 'string', header: lang['doljnost'], width: 150},
				{name: 'MedicalCareKind_Name',  type: 'string', header: lang['vid_mp'], width: 150},
				{name: 'BeginDate',  type: 'date', header: lang['data_sozdaniya'], width: 75},
				{name: 'Staff_Comment',  type: 'string', header: lang['kommentariy'], width: 200},
				{name: 'Staff_Rate',  type: 'float', header: lang['kolichestvo_stavok'], width: 55},
				{name: 'Staff_RateSum',  type: 'float', header: lang['iz_nih_zanyato'], width: 55},
				{name: 'Staff_RateCount',  type: 'float', header: lang['kolichestvo_sotrudnikov'], width: 55}
			],
			actions:
			[
				{name: 'action_add', disabled: isMedPersView(), 
					handler: function() {
						if ( this.swStaffTTPanel.ViewGridPanel.getSelectionModel().getSelected() )
						{
							var lpuStruct = {};
							lpuStruct.Lpu_id = String(getGlobalOptions().lpu_id);
							lpuStruct.LpuBuilding_id = null;
							lpuStruct.LpuUnit_id = null;
							lpuStruct.LpuSection_id = null;
							lpuStruct.description = '';
							window.gwtBridge.runStaffEditor(getPromedUserInfo(), null, lpuStruct, function(result) {
								if ( Number(result) > 0 )
									this.swStaffTTPanel.ViewGridPanel.getStore().reload();
							}.createDelegate(this));
						}
					}.createDelegate(this)
				},
				{name: 'action_edit', text: (isMedPersView())?lang['prosmotr']:lang['izmenit'],
					handler: function() {
						if ( this.swStaffTTPanel.ViewGridPanel.getSelectionModel().getSelected() )
						{
							var row = this.swStaffTTPanel.ViewGridPanel.getSelectionModel().getSelected();
							var staff_id = row.get('Staff_id');
							var lpuStruct = {};
							lpuStruct.Lpu_id = String(row.get('Lpu_id')) == 'null' ? null : String(row.get('Lpu_id'));
							lpuStruct.LpuBuilding_id = String(row.get('LpuBuilding_id')) == 'null' ? null : String(row.get('LpuBuilding_id'));
							lpuStruct.LpuUnit_id = String(row.get('LpuUnit_id')) == 'null' ? null : String(row.get('LpuUnit_id'));
							lpuStruct.LpuSection_id = String(row.get('LpuSection_id')) == 'null' ? null : String(row.get('LpuSection_id'));
							lpuStruct.description = '';
							lpuStruct.action = 'view';
							window.gwtBridge.runStaffEditor(getPromedUserInfo(), String(staff_id), lpuStruct, function(result) {
								if ( Number(result) > 0 )
									this.swStaffTTPanel.ViewGridPanel.getStore().reload();
							}.createDelegate(this));
						}
					}.createDelegate(this)
				},
				{name:'action_view', disabled: true, hidden: true},
				{name:'action_delete', disabled: isMedPersView(), 
					handler: function() {
						if ( this.swStaffTTPanel.ViewGridPanel.getSelectionModel().getSelected() )
						{
							sw.swMsg.show({
								icon: Ext.MessageBox.QUESTION,
								msg: lang['vyi_hotite_udalit_zapis'],
								title: lang['podtverjdenie'],
								buttons: Ext.Msg.YESNO,
								fn: function(buttonId, text, obj)
								{
									if ('yes' == buttonId)
									{
										var row = this.swStaffTTPanel.ViewGridPanel.getSelectionModel().getSelected();
										var staff_id = row.get('Staff_id');
										window.gwtBridge.deleteStaff(getPromedUserInfo(), String(staff_id), function(result) {
											this.swStaffTTPanel.ViewGridPanel.getStore().reload();
										}.createDelegate(this));
									}
								}.createDelegate(this)
							});
						}
					}.createDelegate(this)
				},
				{name:'action_refresh'},
				{name:'action_print', hidden: isMedPersView()}
			]
		});*/
		
		Ext.apply(this,
		{
			xtype: 'panel',
			region: 'center',
			layout:'border',
			items: 
			[
				/*{
					border: false,
					region: 'center',
					layout: 'fit',
					items: [form.swStaffTTPanel_tabs]
				}*/
                form.swStaffTTPanel_tabs
			]
		});
		sw.Promed.swMedStaffFactTTViewForm.superclass.initComponent.apply(this, arguments);
		
	}


});