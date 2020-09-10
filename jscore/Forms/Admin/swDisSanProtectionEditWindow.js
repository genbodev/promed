/**
 * swDisSanProtectionEditWindow - окно редактирования/добавления округа горно-санитарной охраны.
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

sw.Promed.swDisSanProtectionEditWindow = Ext.extend(sw.Promed.BaseForm,
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
        id: 'DisSanProtectionEditWindow',
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
            var form = this.findById('DisSanProtectionEditForm');
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
            var form = this.findById('DisSanProtectionEditForm');
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
                            if (action.result.DisSanProtection_id)
                            {
                                current_window.hide();
                                Ext.getCmp('LpuPassportEditWindow').findById('LPEW_DisSanProtectionGrid').loadData();
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
            var form = this.findById('DisSanProtectionEditForm');
            if (enable)
            {
                form.getForm().findField('DisSanProtection_IsProtection').enable();
                form.getForm().findField('DisSanProtection_Doc').enable();
                form.getForm().findField('DisSanProtection_Num').enable();
                form.getForm().findField('DisSanProtection_Date').enable();
                this.buttons[0].enable();
            }
            else
            {
                form.getForm().findField('DisSanProtection_IsProtection').disable();
                form.getForm().findField('DisSanProtection_Doc').disable();
                form.getForm().findField('DisSanProtection_Num').disable();
                form.getForm().findField('DisSanProtection_Date').disable();
                this.buttons[0].disable();
            }
        },
        show: function()
        {
            sw.Promed.swDisSanProtectionEditWindow.superclass.show.apply(this, arguments);
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
            this.findById('DisSanProtectionEditForm').getForm().reset();
            this.callback = Ext.emptyFn;
            this.onHide = Ext.emptyFn;

            if (arguments[0].DisSanProtection_id)
                this.DisSanProtection_id = arguments[0].DisSanProtection_id;
            else
                this.DisSanProtection_id = null;

            if (arguments[0].Lpu_id)
                this.Lpu_id = arguments[0].Lpu_id;
            else
                this.Lpu_id = null;

            if (arguments[0].DisSanProtection_IsProtection)
                this.DisSanProtection_IsProtection = arguments[0].DisSanProtection_IsProtection;
            else
                this.DisSanProtection_IsProtection = null;

            if (arguments[0].DisSanProtection_Doc)
                this.DisSanProtection_Doc = arguments[0].DisSanProtection_Doc;
            else
                this.DisSanProtection_Doc = null;

            if (arguments[0].DisSanProtection_Num)
                this.DisSanProtection_Num = arguments[0].DisSanProtection_Num;
            else
                this.DisSanProtection_Num = null;

            if (arguments[0].DisSanProtection_Date)
                this.DisSanProtection_Date = arguments[0].DisSanProtection_Date;
            else
                this.DisSanProtection_Date = null;

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
                if ( ( this.DisSanProtection_id ) && ( this.DisSanProtection_id > 0 ) )
                    this.action = "edit";
                else
                    this.action = "add";
            }

            var form = this.findById('DisSanProtectionEditForm');
            form.getForm().setValues(arguments[0]);

            var loadMask = new Ext.LoadMask(this.getEl(),{msg: LOAD_WAIT});
            loadMask.show();
            switch (this.action)
            {
                case 'add':
                    this.setTitle(lang['okrug_gorno-sanitarnoy_ohranyi_dobavlenie']);
                    this.enableEdit(true);
                    loadMask.hide();
                    form.getForm().clearInvalid();
                    form.getForm().findField('DisSanProtection_Date').setValue(new Date());
                    break;
                case 'edit':
                    this.setTitle(lang['okrug_gorno-sanitarnoy_ohranyi_redaktirovanie']);
                    this.enableEdit(true);
                    break;
                case 'view':
                    this.setTitle(lang['okrug_gorno-sanitarnoy_ohranyi_prosmotr']);
                    this.enableEdit(false);
                    break;
            }

            if (this.action != 'add')
            {
                form.getForm().load(
                    {
                        params:
                        {
                            DisSanProtection_id: current_window.DisSanProtection_id,
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
                        url: '/?c=LpuPassport&m=loadDisSanProtection'
                    });
                //this.findById('LPEW_DisSanProtection_Name').getStore().load();
            }
            if ( this.action != 'view' )
                Ext.getCmp('DisSanProtection_Doc_id').focus(true, 100);
            else
                this.buttons[3].focus();
        },
        initComponent: function()
        {
            // Форма с полями 
            var current_window = this;

            this.DisSanProtectionEditForm = new Ext.form.FormPanel(
                {
                    autoHeight: true,
                    bodyStyle: 'padding: 5px',
                    border: false,
                    buttonAlign: 'left',
                    frame: true,
                    id: 'DisSanProtectionEditForm',
                    labelAlign: 'right',
                    labelWidth: 180,
                    items:
                        [{
                            id: 'LPEW_Lpu_id',
                            name: 'Lpu_id',
                            value: 0,
                            xtype: 'hidden'
                        },{
                            name: 'DisSanProtection_id',
                            value: 0,
                            xtype: 'hidden'
                        },{
                            fieldLabel: lang['priznak_nalichiya_okruga'],
                            xtype: 'checkbox',
                            //disabled: true,
                            //autoCreate: {tag: "input", maxLength: "90", autocomplete: "off"},
                            anchor: '100%',
                            name: 'DisSanProtection_IsProtection',
                            tabIndex: TABINDEX_LPEEW + 1
                        },{
                            fieldLabel: lang['dokument'],
                            xtype: 'textfield',
                            allowBlank: false,
                            autoCreate: {tag: "input", maxLength: "90", autocomplete: "off"},
                            anchor: '100%',
                            id:'DisSanProtection_Doc_id',
                            name: 'DisSanProtection_Doc',
                            tabIndex: TABINDEX_LPEEW + 3
                        },{
                            fieldLabel: lang['nomer_dokumenta'],
                            xtype: 'textfield',
                            allowBlank: false,
                            autoCreate: {tag: "input", maxLength: "60", autocomplete: "off"},
                            anchor: '100%',
                            name: 'DisSanProtection_Num',
                            tabIndex: TABINDEX_LPEEW + 4
                        },{
                            fieldLabel: lang['data_dokumenta'],
                            xtype: 'swdatefield',
                            allowBlank: false,
                            plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
                            format: 'd.m.Y',
                            //disabled: true,
                            name: 'DisSanProtection_Date',
                            tabIndex: TABINDEX_LPEEW + 5
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
                            {name: 'DisSanProtection_id'},
                            {name: 'DisSanProtection_IsProtection'},
                            {name: 'DisSanProtection_Doc'},
                            {name: 'DisSanProtection_Num'},
                            {name: 'DisSanProtection_Date'}
                        ]),
                    url: '/?c=LpuPassport&m=saveDisSanProtection'
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
                    items: [this.DisSanProtectionEditForm]
                });
            sw.Promed.swDisSanProtectionEditWindow.superclass.initComponent.apply(this, arguments);
        }
    });