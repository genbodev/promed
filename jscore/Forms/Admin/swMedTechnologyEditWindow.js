/**
 * swMedTechnologyEditWindow - окно редактирования/добавления медицинской технологии.
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Admin
 * @access       public
 * @copyright    Copyright (c) 2009-2011 Swan Ltd.
 * @version      05.10.2011
 */

sw.Promed.swMedTechnologyEditWindow = Ext.extend(sw.Promed.BaseForm,
    {
        action: null,
        autoHeight: true,
        buttonAlign: 'left',
        callback: Ext.emptyFn,
        closable: true,
        closeAction: 'hide',
        draggable: true,
        split: true,
        width: 400,
        layout: 'form',
        id: 'MedTechnologyEditWindow',
        listeners:
        {
            hide: function()
            {
                this.onHide();
            }
        },
        modal: true,
        onHide: Ext.emptyFn,
        plain: true,
        resizable: false,
        doSave: function()
        {
            var form = this.findById('MedTechnologyEditForm');
            if ( !form.getForm().isValid() )
            {
                sw.swMsg.show(
                    {
                        buttons: Ext.Msg.OK,
                        fn: function()
                        {
                            form.getFirstInvalidEl().focus(true);
                        },
                        icon: Ext.Msg.WARNING,
                        msg: ERR_INVFIELDS_MSG,
                        title: ERR_INVFIELDS_TIT
                    });
                return false;
            }
            this.submit();
            return true;
        },
        submit: function()
        {
            var form = this.findById('MedTechnologyEditForm');
            var current_window = this;
            var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
            loadMask.show();
            form.getForm().submit(
                {
                    params:
                    {
                        action: current_window.action,
                        Lpu_id: current_window.Lpu_id,
                        MedTechnology_id: current_window.MedTechnology_id
                    },
                    failure: function(result_form, action)
                    {
                        loadMask.hide();
                        if (action.result)
                        {
                            if (action.result.Error_Code)
                            {
                                Ext.Msg.alert(lang['oshibka_#']+action.result.Error_Code, action.result.Error_Message);
                            }
                        }
                    },
                    success: function(result_form, action)
                    {
                        loadMask.hide();
                        if (action.result)
                        {
                            if (action.result.MedTechnology_id || action.result.MedTechnologyPacs_id)
                            {
                                current_window.hide();
                                Ext.getCmp('LpuPassportEditWindow').findById('LPEW_MedTechnologyGrid').loadData();
                            }
                            else
                            {
                                sw.swMsg.show(
                                    {
                                        buttons: Ext.Msg.OK,
                                        fn: function()
                                        {
                                            form.hide();
                                        },
                                        icon: Ext.Msg.ERROR,
                                        msg: lang['pri_vyipolnenii_operatsii_sohraneniya_proizoshla_oshibka_pojaluysta_povtorite_popyitku_chut_pozje'],
                                        title: lang['oshibka']
                                    });
                            }
                        }
                    }
                });
        },
        show: function()
        {
            sw.Promed.swMedTechnologyEditWindow.superclass.show.apply(this, arguments);
            var current_window = this;
            if (!arguments[0])
            {
                sw.swMsg.show({
                    buttons: Ext.Msg.OK,
                    icon: Ext.Msg.ERROR,
                    msg: lang['oshibka_otkryitiya_formyi_ne_ukazanyi_nujnyie_vhodnyie_parametryi'],
                    title: lang['oshibka'],
                    fn: function() {
                        this.hide();
                    }
                });
            }

            this.focus();
            this.findById('MedTechnologyEditForm').getForm().reset();
            this.callback = Ext.emptyFn;
            this.onHide = Ext.emptyFn;

            if (arguments[0].MedTechnology_id)
                this.MedTechnology_id = arguments[0].MedTechnology_id;
            else
                this.MedTechnology_id = null;

            if (arguments[0].Lpu_id)
                this.Lpu_id = arguments[0].Lpu_id;
            else
                this.Lpu_id = null;

            if (arguments[0].MedTechnology_Name)
                this.MedTechnology_Name = arguments[0].MedTechnology_Name;
            else
                this.MedTechnology_Name = null;

            if (arguments[0].TechnologyClass_id)
                this.TechnologyClass_id = arguments[0].TechnologyClass_id;
            else
                this.TechnologyClass_id = null;

            if (arguments[0].LpuBuildingPass_id)
                this.LpuBuildingPass_id = arguments[0].LpuBuildingPass_id;
            else
                this.LpuBuildingPass_id = null;

            if (arguments[0].callback)
            {
                this.callback = arguments[0].callback;
            }
            if (arguments[0].owner)
            {
                this.owner = arguments[0].owner;
            }
            if (arguments[0].onHide)
            {
                this.onHide = arguments[0].onHide;
            }
            if (arguments[0].action)
            {
                this.action = arguments[0].action;
            }
            else
            {
                if ( ( this.MedTechnology_id ) && ( this.MedTechnology_id > 0 ) )
                    this.action = "edit";
                else
                    this.action = "add";
            }

            var form = this.findById('MedTechnologyEditForm');
            form.getForm().setValues(arguments[0]);

            var loadMask = new Ext.LoadMask(this.getEl(),{msg: LOAD_WAIT});
            loadMask.show();
            switch (this.action)
            {
                case 'add':
                    this.setTitle(lang['meditsinskaya_tehnologiya_dobavlenie']);
                    this.enableEdit(true);
                    loadMask.hide();
                    form.getForm().clearInvalid();
                    break;
                case 'edit':
                    this.setTitle(lang['meditsinskaya_tehnologiya_redaktirovanie']);
                    this.enableEdit(true);
                    break;
                case 'view':
                    this.setTitle(lang['meditsinskaya_tehnologiya_prosmotr']);
                    this.enableEdit(false);
                    break;
            }

            if (this.action != 'add')
            {
                form.getForm().load(
                    {
                        params:
                        {
                            MedTechnology_id: current_window.MedTechnology_id,
                            MedTechnology_Name: current_window.MedTechnology_Name,
                            TechnologyClass_id: current_window.TechnologyClass_id,
                            LpuBuildingPass_id: current_window.LpuBuildingPass_id,
                            Lpu_id: current_window.Lpu_id
                        },
                        failure: function(f, o, a)
                        {
                            loadMask.hide();
                            sw.swMsg.show(
                                {
                                    buttons: Ext.Msg.OK,
                                    fn: function()
                                    {
                                        current_window.hide();
                                    },
                                    icon: Ext.Msg.ERROR,
                                    msg: lang['oshibka_zaprosa_k_serveru_poprobuyte_povtorit_operatsiyu'],
                                    title: lang['oshibka']
                                });
                        },
                        success: function()
                        {
                            loadMask.hide();
                            current_window.findById('LPEW_Lpu_id').setValue(current_window.Lpu_id);
                        },
                        url: '/?c=LpuPassport&m=loadMedTechnology'
                    });
                //this.findById('LPEW_MedTechnology_Name').getStore().load();
            }
            if ( this.action != 'view' )
                Ext.getCmp('LPEW_MedTechnology_Name').focus(true, 100);
            else
                this.buttons[3].focus();

            form.findById('LPEW_LpuBuildingPass_id').store.load({params: {Lpu_id: this.Lpu_id}});
        },
        initComponent: function()
        {
            // Форма с полями 

            this.MedTechnologyEditForm = new Ext.form.FormPanel(
                {
                    autoHeight: true,
                    bodyStyle: 'padding: 5px',
                    border: false,
                    buttonAlign: 'left',
                    frame: true,
                    id: 'MedTechnologyEditForm',
                    labelAlign: 'right',
                    labelWidth: 180,
                    items:
                        [{
                            id: 'LPEW_Lpu_id',
                            name: 'Lpu_id',
                            value: 0,
                            xtype: 'hidden'
                        },{
                            name: 'MedTechnology_id',
                            value: 0,
                            xtype: 'hidden'
                        },{
                            fieldLabel: lang['naimenovanie_meditsinskoy_tehnologii'],
                            allowBlank: false,
                            xtype: 'textfield',
                            autoCreate: {tag: "input", maxLength: "90", autocomplete: "off"},
                            anchor: '100%',
                            id: 'LPEW_MedTechnology_Name',
                            name: 'MedTechnology_Name',
                            tabIndex: TABINDEX_LPEEW + 3
                        },{
                            anchor: '100%',
                            allowBlank: false,
                            listWidth: 600,
                            comboSubject: 'TechnologyClass',
                            fieldLabel: lang['klass_tehnologii'],
                            hiddenName: 'TechnologyClass_id',
                            tabIndex: TABINDEX_LPEEW + 4,
							tpl: '<tpl for="."><div class="x-combo-list-item" style="white-space:normal;">'+
								'{TechnologyClass_Name}&nbsp;'+
								'</div></tpl>',
                            xtype: 'swcommonsprcombo'
                        },{
                            fieldLabel: lang['identifikator_zdaniya'],
                            allowBlank: false,
                            displayField: 'LpuBuildingPass_Name',
                            hiddenName: 'LpuBuildingPass_id',
                            id: 'LPEW_LpuBuildingPass_id',
                            enableKeyEvents: true,
                            editable: false,
                            //listWidth: 220,
                            mode: 'local',
                            typeCode: 'string',
                            orderBy: 'id',
                            resizable: true,
                            store: new Ext.data.Store({
                                autoLoad: false,
                                reader: new Ext.data.JsonReader({
                                    id: 'LpuBuildingPass_id'
                                },[
                                    { name: 'LpuBuildingPass_id', mapping: 'LpuBuildingPass_id' },
                                    { name: 'LpuBuildingPass_Name', mapping: 'LpuBuildingPass_Name' }
                                ]),
                                url:'/?c=LpuPassport&m=loadLpuBuildingMedTechnology'
                            }),
                            tpl: new Ext.XTemplate(
                                '<tpl for="."><div class="x-combo-list-item">',
                                '<font color="red">{LpuBuildingPass_id}</font>&nbsp;{LpuBuildingPass_Name}',
                                '</div></tpl>'
                            ),
                            triggerAction: 'all',
                            valueField: 'LpuBuildingPass_id',
                            width : 181,
                            tabIndex: TABINDEX_LPEEW + 2,
                            xtype: 'swcombo'
                        }],
                    reader: new Ext.data.JsonReader(
                        {
                            success: function()
                            {
                                //
                            }
                        },
                        [
                            {name: 'Lpu_id'},
                            {name: 'MedTechnology_id'},
                            {name: 'MedTechnology_Name'},
                            {name: 'TechnologyClass_id'},
                            {name: 'LpuBuildingPass_id'}
                        ]),
                    url: '/?c=LpuPassport&m=saveMedTechnology'
                });
            Ext.apply(this,
                {
                    buttons:
                        [{
                            handler: function()
                            {
                                this.ownerCt.doSave();
                            },
                            iconCls: 'save16',
                            tabIndex: TABINDEX_LPEEW + 16,
                            text: BTN_FRMSAVE
                        },
                        {
                            text: '-'
                        },
                        HelpButton(this),
                        {
                            handler: function()
                            {
                                this.ownerCt.hide();
                            },
                            iconCls: 'cancel16',
                            tabIndex: TABINDEX_LPEEW + 17,
                            text: BTN_FRMCANCEL
                        }],
                    items: [this.MedTechnologyEditForm]
                });
            sw.Promed.swMedTechnologyEditWindow.superclass.initComponent.apply(this, arguments);
        }
    });