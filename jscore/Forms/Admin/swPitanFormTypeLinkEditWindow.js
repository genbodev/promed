/**
 * swPitanFormTypeLinkEditWindow - окно редактирования/добавления питания.
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

sw.Promed.swPitanFormTypeLinkEditWindow = Ext.extend(sw.Promed.BaseForm,
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
        id: 'PitanFormTypeLinkEditWindow',
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
            var form = this.findById('PitanFormTypeLinkEditForm');
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
            var form = this.findById('PitanFormTypeLinkEditForm');
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
                            if (action.result.PitanFormTypeLink_id)
                            {
                                current_window.hide();
                                Ext.getCmp('LpuPassportEditWindow').findById('LPEW_PitanFormTypeLinkGrid').loadData();
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
                var form = this.findById('PitanFormTypeLinkEditForm');
                form.getForm().findField('VidPitan_id').enable();
                form.getForm().findField('PitanCnt_id').enable();
                form.getForm().findField('PitanForm_id').enable();
                this.buttons[0].enable();
            }
            else
            {
                var form = this.findById('PitanFormTypeLinkEditForm');
                form.getForm().findField('VidPitan_id').disable();
                form.getForm().findField('PitanCnt_id').disable();
                form.getForm().findField('PitanForm_id').disable();
                this.buttons[0].disable();
            }
        },
        show: function()
        {
            sw.Promed.swPitanFormTypeLinkEditWindow.superclass.show.apply(this, arguments);
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
            this.findById('PitanFormTypeLinkEditForm').getForm().reset();
            this.callback = Ext.emptyFn;
            this.onHide = Ext.emptyFn;

            if (arguments[0].PitanFormTypeLink_id)
                this.PitanFormTypeLink_id = arguments[0].PitanFormTypeLink_id;
            else
                this.PitanFormTypeLink_id = null;

            if (arguments[0].Lpu_id)
                this.Lpu_id = arguments[0].Lpu_id;
            else
                this.Lpu_id = null;

            if (arguments[0].VidPitan_id)
                this.VidPitan_id = arguments[0].VidPitan_id;
            else
                this.VidPitan_id = null;

            if (arguments[0].PitanCnt_id)
                this.PitanCnt_id = arguments[0].PitanCnt_id;
            else
                this.PitanCnt_id = null;

            if (arguments[0].PitanForm_id)
                this.PitanForm_id = arguments[0].PitanForm_id;
            else
                this.PitanForm_id = null;

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
                if ( ( this.PitanFormTypeLink_id ) && ( this.PitanFormTypeLink_id > 0 ) )
                    this.action = "edit";
                else
                    this.action = "add";
            }

            var form = this.findById('PitanFormTypeLinkEditForm');
            form.getForm().setValues(arguments[0]);

            var loadMask = new Ext.LoadMask(this.getEl(),{msg: LOAD_WAIT});
            loadMask.show();
            switch (this.action)
            {
                case 'add':
                    this.setTitle(lang['pitanie_dobavlenie']);
                    this.enableEdit(true);
                    loadMask.hide();
                    form.getForm().clearInvalid();
                    break;
                case 'edit':
                    this.setTitle(lang['pitanie_redaktirovanie']);
                    this.enableEdit(true);
                    break;
                case 'view':
                    this.setTitle(lang['pitanie_prosmotr']);
                    this.enableEdit(false);
                    break;
            }

            if (this.action != 'add')
            {
                form.getForm().load(
                    {
                        params:
                        {
                            PitanFormTypeLink_id: current_window.PitanFormTypeLink_id,
                            VidPitan_id: current_window.VidPitan_id,
                            PitanCnt_id: current_window.PitanCnt_id,
                            PitanForm_id: current_window.PitanForm_id,
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
                        url: '/?c=LpuPassport&m=loadPitanFormTypeLink'
                    });
                //this.findById('LPEW_PitanFormTypeLink_Name').getStore().load();
            }
           /* if ( this.action != 'view' )
                Ext.getCmp('LPEW_PitanFormTypeLink_Name').focus(true, 100);
            else*/
                this.buttons[3].focus();
        },
        initComponent: function()
        {
            // Форма с полями 
            var current_window = this;

            this.PitanFormTypeLinkEditForm = new Ext.form.FormPanel(
                {
                    autoHeight: true,
                    bodyStyle: 'padding: 5px',
                    border: false,
                    buttonAlign: 'left',
                    frame: true,
                    id: 'PitanFormTypeLinkEditForm',
                    labelAlign: 'right',
                    labelWidth: 180,
                    items:
                        [{
                            id: 'LPEW_Lpu_id',
                            name: 'Lpu_id',
                            value: 0,
                            xtype: 'hidden'
                        },{
                            name: 'PitanFormTypeLink_id',
                            value: 0,
                            xtype: 'hidden'
                        },{
                            anchor: '100%',
                            allowBlank: false,
                            comboSubject: 'VidPitan',
                            fieldLabel: lang['vid_pitaniya'],
                            hiddenName: 'VidPitan_id',
                            tabIndex: TABINDEX_LPEEW + 1,
                            xtype: 'swcommonsprcombo'
                        },{
                            anchor: '100%',
                            allowBlank: false,
                            comboSubject: 'PitanCnt',
                            fieldLabel: lang['kratnost_pitaniya'],
                            hiddenName: 'PitanCnt_id',
                            tabIndex: TABINDEX_LPEEW + 2,
                            xtype: 'swcommonsprcombo'
                        },{
                            anchor: '100%',
                            allowBlank: false,
                            comboSubject: 'PitanForm',
                            fieldLabel: lang['forma_pitaniya'],
                            hiddenName: 'PitanForm_id',
                            tabIndex: TABINDEX_LPEEW + 3,
                            xtype: 'swcommonsprcombo'
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
                            {name: 'PitanFormTypeLink_id'},
                            {name: 'VidPitan_id'},
                            {name: 'PitanCnt_id'},
                            {name: 'PitanForm_id'}
                        ]),
                    url: '/?c=LpuPassport&m=savePitanFormTypeLink'
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
                    items: [this.PitanFormTypeLinkEditForm]
                });
            sw.Promed.swPitanFormTypeLinkEditWindow.superclass.initComponent.apply(this, arguments);
        }
    });