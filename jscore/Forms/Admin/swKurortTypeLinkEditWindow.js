/**
 * swKurortTypeLinkEditWindow - окно редактирования/добавления статуса курорта.
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

sw.Promed.swKurortTypeLinkEditWindow = Ext.extend(sw.Promed.BaseForm,
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
        id: 'KurortTypeLinkEditWindow',
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
            var form = this.findById('KurortTypeLinkEditForm');
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
            var form = this.findById('KurortTypeLinkEditForm');
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
                            if (action.result.KurortTypeLink_id)
                            {
                                current_window.hide();
                                Ext.getCmp('LpuPassportEditWindow').findById('LPEW_KurortTypeLinkGrid').loadData();
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
                var form = this.findById('KurortTypeLinkEditForm');
                form.getForm().findField('KurortTypeLink_IsKurortTypeLink').enable();
                form.getForm().findField('KurortType_id').enable();
                form.getForm().findField('KurortTypeLink_Doc').enable();
                form.getForm().findField('KurortTypeLink_Num').enable();
                form.getForm().findField('KurortTypeLink_Date').enable();
                this.buttons[0].enable();
            }
            else
            {
                var form = this.findById('KurortTypeLinkEditForm');
                form.getForm().findField('KurortTypeLink_IsKurortTypeLink').disable();
                form.getForm().findField('KurortType_id').disable();
                form.getForm().findField('KurortTypeLink_Doc').disable();
                form.getForm().findField('KurortTypeLink_Num').disable();
                form.getForm().findField('KurortTypeLink_Date').disable();
                this.buttons[0].disable();
            }
        },
        show: function()
        {
            sw.Promed.swKurortTypeLinkEditWindow.superclass.show.apply(this, arguments);
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
            this.findById('KurortTypeLinkEditForm').getForm().reset();
            this.callback = Ext.emptyFn;
            this.onHide = Ext.emptyFn;

            if (arguments[0].KurortType_id)
                this.KurortType_id = arguments[0].KurortType_id;
            else
                this.KurortType_id = null;

            if (arguments[0].KurortTypeLink_IsKurortTypeLink)
                this.KurortTypeLink_IsKurortTypeLink = arguments[0].KurortTypeLink_IsKurortTypeLink;
            else
                this.KurortTypeLink_IsKurortTypeLink = null;

            if (arguments[0].KurortTypeLink_Doc)
                this.KurortTypeLink_Doc = arguments[0].KurortTypeLink_Doc;
            else
                this.KurortTypeLink_Doc = null;

            if (arguments[0].KurortTypeLink_Num)
                this.KurortTypeLink_Num = arguments[0].KurortTypeLink_Num;
            else
                this.KurortTypeLink_Num = null;

            if (arguments[0].KurortTypeLink_Date)
                this.KurortTypeLink_Date = arguments[0].KurortTypeLink_Date;
            else
                this.KurortTypeLink_Date = null;

            if (arguments[0].KurortTypeLink_id)
                this.KurortTypeLink_id = arguments[0].KurortTypeLink_id;
            else
                this.KurortTypeLink_id = null;

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
                if ( ( this.KurortTypeLink_id ) && ( this.KurortTypeLink_id > 0 ) )
                    this.action = "edit";
                else
                    this.action = "add";
            }

            var form = this.findById('KurortTypeLinkEditForm');
            form.getForm().setValues(arguments[0]);

            var loadMask = new Ext.LoadMask(this.getEl(),{msg: LOAD_WAIT});
            loadMask.show();
            switch (this.action)
            {
                case 'add':
                    this.setTitle(lang['tip_kurorta_dobavlenie']);
                    this.enableEdit(true);
                    loadMask.hide();
                    form.getForm().clearInvalid();
                    form.getForm().findField('KurortTypeLink_Date').setValue(new Date());
                    break;
                case 'edit':
                    this.setTitle(lang['tip_kurorta_redaktirovanie']);
                    this.enableEdit(true);
                    break;
                case 'view':
                    this.setTitle(lang['tip_kurorta_prosmotr']);
                    this.enableEdit(false);
                    break;
            }

            if (this.action != 'add')
            {
                form.getForm().load(
                    {
                        params:
                        {
                            KurortTypeLink_id: current_window.KurortTypeLink_id,
                            KurortType_id: current_window.KurortType_id,
                            KurortTypeLink_IsKurortTypeLink: current_window.KurortTypeLink_IsKurortTypeLink,
                            KurortTypeLink_Doc: current_window.KurortTypeLink_Doc,
                            KurortTypeLink_Num: current_window.KurortTypeLink_Num,
                            KurortTypeLink_Date: current_window.KurortTypeLink_Date,
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
                        url: '/?c=LpuPassport&m=loadKurortTypeLink'
                    });
                //this.findById('LPEW_KurortTypeLink_Name').getStore().load();
            }
            if ( this.action != 'view' )
                Ext.getCmp('LPEW_KurortType_Name').focus(true, 100);
            else
                this.buttons[3].focus();
        },
        initComponent: function()
        {
            // Форма с полями 
            var current_window = this;

            this.KurortTypeLinkEditForm = new Ext.form.FormPanel(
                {
                    autoHeight: true,
                    bodyStyle: 'padding: 5px',
                    border: false,
                    buttonAlign: 'left',
                    frame: true,
                    id: 'KurortTypeLinkEditForm',
                    labelAlign: 'right',
                    labelWidth: 180,
                    items:
                        [{
                            id: 'LPEW_Lpu_id',
                            name: 'Lpu_id',
                            value: 0,
                            xtype: 'hidden'
                        },{
                            name: 'KurortTypeLink_id',
                            value: 0,
                            xtype: 'hidden'
                        },{
                            fieldLabel: lang['nalichie_tipa_kurorta'],
                            xtype: 'swcheckbox',
                             anchor: '100%',
                            name: 'KurortTypeLink_IsKurortTypeLink',
                            tabIndex: TABINDEX_LPEEW + 1
                        },{
                            anchor: '100%',
                            allowBlank: false,
                            comboSubject: 'KurortType',
                            fieldLabel: lang['tip_kurorta'],
                            hiddenName: 'KurortType_id',
                            id: 'LPEW_KurortType_Name',
                            tabIndex: TABINDEX_LPEEW + 2,
                            xtype: 'swcommonsprcombo'
                        },{
                            fieldLabel: lang['dokument'],
                            allowBlank: false,
                            xtype: 'textfield',
                            autoCreate: {tag: "input", maxLength: "90", autocomplete: "off"},
                            anchor: '100%',
                            name: 'KurortTypeLink_Doc',
                            tabIndex: TABINDEX_LPEEW + 3
                        },{
                            fieldLabel: lang['nomer_dokumenta'],
                            allowBlank: false,
                            xtype: 'textfield',
                            autoCreate: {tag: "input", maxLength: "60", autocomplete: "off"},
                            anchor: '100%',
                            name: 'KurortTypeLink_Num',
                            tabIndex: TABINDEX_LPEEW + 4
                        },{
                            fieldLabel: lang['data_dokumenta'],
                            allowBlank: false,
                            xtype: 'swdatefield',
                            plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
                            format: 'd.m.Y',
                            name: 'KurortTypeLink_Date',
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
                            {name: 'KurortTypeLink_id'},
                            {name: 'KurortType_id'},
                            {name: 'KurortTypeLink_IsKurortTypeLink'},
                            {name: 'KurortTypeLink_Doc'},
                            {name: 'KurortTypeLink_Num'},
                            {name: 'KurortTypeLink_Date'}
                        ]),
                    url: '/?c=LpuPassport&m=saveKurortTypeLink'
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
                    items: [this.KurortTypeLinkEditForm]
                });
            sw.Promed.swKurortTypeLinkEditWindow.superclass.initComponent.apply(this, arguments);
        }
    });