/**
 * swPlfObjectCountEditWindow - окно редактирования/добавления объекта/места использования природных лечебных факторов.
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

sw.Promed.swPlfObjectCountEditWindow = Ext.extend(sw.Promed.BaseForm,
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
        id: 'swPlfObjectCountEditWindow',
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
            var form = this.findById('PlfObjectCountForm');
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
            var form = this.findById('PlfObjectCountForm');
            var current_window = this;
            var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
            loadMask.show();
            form.getForm().submit(
                {
                    params:
                    {
                        action: current_window.action
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
                            if (action.result.PlfObjectCount_id)
                            {
                                current_window.hide();
                                Ext.getCmp('LpuPassportEditWindow').findById('LPEW_PlfObjectCountGrid').loadData();
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
        enableEdit: function(enable)
        {
            var form = this.findById('PlfObjectCountForm');
            if (enable)
            {
                form.getForm().findField('PlfObjects_id').enable();
                form.getForm().findField('PlfObjectCount_Count').enable();
                this.buttons[0].enable();
            }
            else
            {
                form.getForm().findField('PlfObjects_id').disable();
                form.getForm().findField('PlfObjectCount_Count').disable();
                this.buttons[0].disable();
            }
        },
        show: function()
        {
            sw.Promed.swPlfObjectCountEditWindow.superclass.show.apply(this, arguments);
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
            this.findById('PlfObjectCountForm').getForm().reset();
            this.callback = Ext.emptyFn;
            this.onHide = Ext.emptyFn;

            if (arguments[0].PlfObjectCount_id)
                this.PlfObjectCount_id = arguments[0].PlfObjectCount_id;
            else
                this.PlfObjectCount_id = null;

            if (arguments[0].Lpu_id)
                this.Lpu_id = arguments[0].Lpu_id;
            else
                this.Lpu_id = null;

            if (arguments[0].PlfObjects_id)
                this.PlfObjects_id = arguments[0].PlfObjects_id;
            else
                this.PlfObjects_id = null;

            if (arguments[0].PlfObjectCount_Count)
                this.PlfObjectCount_Count = arguments[0].PlfObjectCount_Count;
            else
                this.PlfObjectCount_Count = null;

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
                if ( ( this.PlfObjectCount_id ) && ( this.PlfObjectCount_id > 0 ) )
                    this.action = "edit";
                else
                    this.action = "add";
            }

            var form = this.findById('PlfObjectCountForm');
            form.getForm().setValues(arguments[0]);

            var loadMask = new Ext.LoadMask(this.getEl(),{msg: LOAD_WAIT});
            loadMask.show();
            switch (this.action)
            {
                case 'add':
                    this.setTitle(lang['obyekt_mesto_ispolzovaniya_prirodnogo_lechebnogo_faktora_dobavlenie']);
                    this.enableEdit(true);
                    loadMask.hide();
                    form.getForm().clearInvalid();
                    break;
                case 'edit':
                    this.setTitle(lang['obyekt_mesto_ispolzovaniya_prirodnogo_lechebnogo_faktora_redaktirovanie']);
                    this.enableEdit(true);
                    break;
                case 'view':
                    this.setTitle(lang['obyekt_mesto_ispolzovaniya_prirodnogo_lechebnogo_faktora_prosmotr']);
                    this.enableEdit(false);
                    break;
            }

            if (this.action != 'add')
            {
                form.getForm().load(
                    {
                        params:
                        {
                            PlfObjectCount_id: current_window.PlfObjectCount_id,
                            PlfObjects_id: current_window.PlfObjects_id,
                            PlfObjectCount_Count: current_window.PlfObjectCount_Count,
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
                        url: '/?c=LpuPassport&m=loadPlfObjectCount'
                    });
                this.findById('LPEW_PlfObjects_id').getStore().load();
            }
            /*if ( this.action != 'view' )
                Ext.getCmp('LPEW_PlfObjects_id').focus(true, 100);
            else*/
                this.buttons[3].focus();
        },
        initComponent: function()
        {
            // Форма с полями 
            this.PlfObjectCountForm = new Ext.form.FormPanel(
                {
                    autoHeight: true,
                    bodyStyle: 'padding: 5px',
                    border: false,
                    buttonAlign: 'left',
                    frame: true,
                    id: 'PlfObjectCountForm',
                    labelAlign: 'right',
                    labelWidth: 180,
                    items:
                        [{
                            id: 'LPEW_Lpu_id',
                            name: 'Lpu_id',
                            value: 0,
                            xtype: 'hidden'
                        },{
                            name: 'PlfObjectCount_id',
                            value: 0,
                            xtype: 'hidden'
                        },{
                            anchor: '100%',
                            allowBlank: false,
                            comboSubject: 'PlfObjects',
                            fieldLabel: lang['naimenovanie_obyekta'],
                            hiddenName: 'PlfObjects_id',
                            id: 'LPEW_PlfObjects_id',
                            tabIndex: TABINDEX_LPEEW + 1,
                            xtype: 'swcommonsprcombo'
                        },{
                            fieldLabel: lang['kolichestvo_obyektov_po_ispolzovaniyu'],
                            allowBlank: false,
                            xtype: 'textfield',
                            autoCreate: {tag: "input", maxLength: "60", autocomplete: "off"},
                            anchor: '100%',
                            name: 'PlfObjectCount_Count',
                            tabIndex: TABINDEX_LPEEW + 4
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
                            {name: 'PlfObjectCount_id'},
                            {name: 'PlfObjects_id'},
                            {name: 'PlfObjectCount_Count'}
                        ]),
                    url: '/?c=LpuPassport&m=savePlfObjectCount'
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
                    items: [this.PlfObjectCountForm]
                });
            sw.Promed.swPlfObjectCountEditWindow.superclass.initComponent.apply(this, arguments);
        }
    });