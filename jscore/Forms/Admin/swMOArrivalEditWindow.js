/**
 * swMOArrivalEditWindow - окно редактирования/добавления заезда.
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

sw.Promed.swMOArrivalEditWindow = Ext.extend(sw.Promed.BaseForm,
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
        id: 'MOArrivalEditWindow',
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
            var form = this.findById('MOArrivalEditForm');
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
            var form = this.findById('MOArrivalEditForm');
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
                            if (action.result.MOArrival_id || action.result.MOArrivalPacs_id)
                            {
                                current_window.hide();
                                Ext.getCmp('LpuPassportEditWindow').findById('LPEW_MOArrivalGrid').loadData();
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
            var form = this;
            if (enable)
            {
                var form = this.findById('MOArrivalEditForm');
                form.getForm().findField('MOArrival_id').enable();
                form.getForm().findField('MOArrival_CountPerson').enable();
                form.getForm().findField('MOArrival_TreatDis').enable();
                form.getForm().findField('MOArrival_EndDT').enable();
                this.buttons[0].enable();
            }
            else
            {
                var form = this.findById('MOArrivalEditForm');
                form.getForm().findField('MOArrival_id').disable();
                form.getForm().findField('MOArrival_CountPerson').disable();
                form.getForm().findField('MOArrival_TreatDis').disable();
                form.getForm().findField('MOArrival_EndDT').disable();
                this.buttons[0].disable();
            }
        },
        show: function()
        {
            sw.Promed.swMOArrivalEditWindow.superclass.show.apply(this, arguments);
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
            this.findById('MOArrivalEditForm').getForm().reset();
            this.callback = Ext.emptyFn;
            this.onHide = Ext.emptyFn;


            if (arguments[0].MOArrival_id)
                this.MOArrival_id = arguments[0].MOArrival_id;
            else
                this.MOArrival_id = null;

            if (arguments[0].Lpu_id)
                this.Lpu_id = arguments[0].Lpu_id;
            else
                this.Lpu_id = null;

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
                if ( ( this.MOArrival_id ) && ( this.MOArrival_id > 0 ) )
                    this.action = "edit";
                else
                    this.action = "add";
            }

            var form = this.findById('MOArrivalEditForm');
            form.getForm().setValues(arguments[0]);

            var loadMask = new Ext.LoadMask(this.getEl(),{msg: LOAD_WAIT});
            loadMask.show();
            switch (this.action)
            {
                case 'add':
                    this.setTitle(lang['zaezd_dobavlenie']);
                    this.enableEdit(true);
                    loadMask.hide();
                    form.getForm().clearInvalid();
                    form.getForm().findField('MOArrival_EndDT').setValue(new Date());
                    break;
                case 'edit':
                    this.setTitle(lang['zaezd_redaktirovanie']);
                    this.enableEdit(true);
                    break;
                case 'view':
                    this.setTitle(lang['zaezd_prosmotr']);
                    this.enableEdit(false);
                    break;
            }

            if (this.action != 'add')
            {
                form.getForm().load(
                    {
                        params:
                        {
                            MOArrival_id: current_window.MOArrival_id,
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
                        url: '/?c=LpuPassport&m=loadMOArrival'
                    });
                //this.findById('LPEW_MOArrival_Name').getStore().load();
            }
            if ( this.action != 'view' )
                Ext.getCmp('LPEW_MOArrival_id').focus(true, 100);
            else
                this.buttons[3].focus();
        },
        initComponent: function()
        {
            // Форма с полями 
            var current_window = this;

            this.MOArrivalEditForm = new Ext.form.FormPanel(
                {
                    autoHeight: true,
                    bodyStyle: 'padding: 5px',
                    border: false,
                    buttonAlign: 'left',
                    frame: true,
                    id: 'MOArrivalEditForm',
                    labelAlign: 'right',
                    labelWidth: 180,
                    items:
                        [{
                            id: 'LPEW_Lpu_id',
                            name: 'Lpu_id',
                            value: 0,
                            xtype: 'hidden'
                        },{
                            id: 'LPEW_MOArrival_id',
                            name: 'MOArrival_id',
                            value: 0,
                            xtype: 'hidden'
                        },{
                            fieldLabel: lang['data_okonchaniya_zaezda'],
                            allowBlank: false,
                            xtype: 'swdatefield',
                            plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
                            format: 'd.m.Y',
                            //disabled: true,
                            name: 'MOArrival_EndDT',
                            tabIndex: TABINDEX_LPEEW + 5
                        },{
                            fieldLabel: lang['kolichestvo_chelovek'],
                            xtype: 'textfield',
                            //disabled: true,
                            autoCreate: {tag: "input", maxLength: "90", autocomplete: "off"},
                            anchor: '100%',
                            name: 'MOArrival_CountPerson',
                            tabIndex: TABINDEX_LPEEW + 3
                        },{
                            fieldLabel: lang['dlitelnost_lecheniya'],
                            allowBlank: false,
                            xtype: 'textfield',
                            //disabled: true,
                            autoCreate: {tag: "input", maxLength: "60", autocomplete: "off"},
                            anchor: '100%',
                            name: 'MOArrival_TreatDis',
                            tabIndex: TABINDEX_LPEEW + 4
                        }],
                //},
                    reader: new Ext.data.JsonReader(
                        {
                            success: function()
                            {
                                //
                            }
                        },
                        [
                            {name: 'Lpu_id'},
                            {name: 'MOArrival_id'},
                            {name: 'MOArrival_CountPerson'},
                            {name: 'MOArrival_TreatDis'},
                            {name: 'MOArrival_EndDT'}
                        ]),
                    url: '/?c=LpuPassport&m=saveMOArrival'
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
                    items: [this.MOArrivalEditForm]
                });
            sw.Promed.swMOArrivalEditWindow.superclass.initComponent.apply(this, arguments);
        }
    });