/**
 * swPlfDocTypeLinkEditWindow - окно редактирования/добавления природного лечебного фактора.
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

sw.Promed.swPlfDocTypeLinkEditWindow = Ext.extend(sw.Promed.BaseForm,
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
        id: 'swPlfDocTypeLinkEditWindow',
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
            var form = this.findById('PlfDocTypeLinkForm');
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
            var form = this.findById('PlfDocTypeLinkForm');
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
                            if (action.result.PlfDocTypeLink_id || action.result.PlfDocTypeLinkPacs_id)
                            {
                                current_window.hide();
                                Ext.getCmp('LpuPassportEditWindow').findById('LPEW_PlfDocTypeLinkGrid').loadData();
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
                var form = this.findById('PlfDocTypeLinkForm');
                form.getForm().findField('PlfDocTypeLink_id').enable();
                form.getForm().findField('Plf_id').enable();
                form.getForm().findField('PlfType_id').enable();
                form.getForm().findField('DocTypeUsePlf_id').enable();
                form.getForm().findField('PlfDocTypeLink_Num').enable();
                form.getForm().findField('PlfDocTypeLink_GetDT').enable();
                form.getForm().findField('PlfDocTypeLink_BegDT').enable();
                form.getForm().findField('PlfDocTypeLink_EndDT').enable();
                this.buttons[0].enable();
            }
            else
            {
                var form = this.findById('PlfDocTypeLinkForm');
                form.getForm().findField('PlfDocTypeLink_id').disable();
                form.getForm().findField('Plf_id').disable();
                form.getForm().findField('PlfType_id').disable();
                form.getForm().findField('DocTypeUsePlf_id').disable();
                form.getForm().findField('PlfDocTypeLink_Num').disable();
                form.getForm().findField('PlfDocTypeLink_GetDT').disable();
                form.getForm().findField('PlfDocTypeLink_BegDT').disable();
                form.getForm().findField('PlfDocTypeLink_EndDT').disable();
                this.buttons[0].disable();
            }
        },
        show: function()
        {
            sw.Promed.swPlfDocTypeLinkEditWindow.superclass.show.apply(this, arguments);
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
            this.findById('PlfDocTypeLinkForm').getForm().reset();
            this.callback = Ext.emptyFn;
            this.onHide = Ext.emptyFn;

            if (arguments[0].PlfDocTypeLink_id)
                this.PlfDocTypeLink_id = arguments[0].PlfDocTypeLink_id;
            else
                this.PlfDocTypeLink_id = null;

            if (arguments[0].Lpu_id)
                this.Lpu_id = arguments[0].Lpu_id;
            else
                this.Lpu_id = null;

            if (arguments[0].Plf_id)
                this.Plf_id = arguments[0].Plf_id;
            else
                this.Plf_id = null;

            if (arguments[0].PlfType_id)
                this.PlfType_id = arguments[0].PlfType_id;
            else
                this.PlfType_id = null;

            if (arguments[0].DocTypeUsePlf_id)
                this.DocTypeUsePlf_id = arguments[0].DocTypeUsePlf_id;
            else
                this.DocTypeUsePlf_id = null;

            if (arguments[0].PlfDocTypeLink_Num)
                this.PlfDocTypeLink_Num = arguments[0].PlfDocTypeLink_Num;
            else
                this.PlfDocTypeLink_Num = null;

            if (arguments[0].PlfDocTypeLink_GetDT)
                this.PlfDocTypeLink_GetDT = arguments[0].PlfDocTypeLink_GetDT;
            else
                this.PlfDocTypeLink_GetDT = null;

            if (arguments[0].PlfDocTypeLink_BegDT)
                this.PlfDocTypeLink_BegDT = arguments[0].PlfDocTypeLink_BegDT;
            else
                this.PlfDocTypeLink_BegDT = null;

            if (arguments[0].PlfDocTypeLink_EndDT)
                this.PlfDocTypeLink_EndDT = arguments[0].PlfDocTypeLink_EndDT;
            else
                this.PlfDocTypeLink_EndDT = null;

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
                if ( ( this.PlfDocTypeLink_id ) && ( this.PlfDocTypeLink_id > 0 ) )
                    this.action = "edit";
                else
                    this.action = "add";
            }

            var form = this.findById('PlfDocTypeLinkForm');
            form.getForm().setValues(arguments[0]);

            var loadMask = new Ext.LoadMask(this.getEl(),{msg: LOAD_WAIT});
            loadMask.show();
            switch (this.action)
            {
                case 'add':
                    this.setTitle(lang['prirodnyiy_lechebnyiy_faktor_dobavlenie']);
                    this.enableEdit(true);
                    loadMask.hide();
                    form.getForm().clearInvalid();
                    form.getForm().findField('PlfDocTypeLink_GetDT').setValue(new Date());
                    form.getForm().findField('PlfDocTypeLink_BegDT').setValue(new Date());
                    form.getForm().findField('PlfDocTypeLink_EndDT').setValue(new Date());
                    break;
                case 'edit':
                    this.setTitle(lang['prirodnyiy_lechebnyiy_faktor_redaktirovanie']);
                    this.enableEdit(true);
                    break;
                case 'view':
                    this.setTitle(lang['prirodnyiy_lechebnyiy_faktor_prosmotr']);
                    this.enableEdit(false);
                    break;
            }

            if (this.action != 'add')
            {
                form.getForm().load(
                    {
                        params:
                        {
                            Lpu_id: current_window.Lpu_id,
                            PlfDocTypeLink_id: current_window.PlfDocTypeLink_id,
                            Plf_id: current_window.Plf_id,
                            PlfType_id: current_window.PlfType_id,
                            DocTypeUsePlf_id: current_window.DocTypeUsePlf_id,
                            PlfDocTypeLink_Num: current_window.PlfDocTypeLink_Num,
                            PlfDocTypeLink_GetDT: current_window.PlfDocTypeLink_GetDT,
                            PlfDocTypeLink_BegDT: current_window.PlfDocTypeLink_BegDT,
                            PlfDocTypeLink_EndDT: current_window.PlfDocTypeLink_EndDT

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
                        url: '/?c=LpuPassport&m=loadPlfDocTypeLink'
                    });
                //this.findById('LPEW_PlfDocTypeLink_Name').getStore().load();
            }
           /* if ( this.action != 'view' )
                Ext.getCmp('LPEW_PlfDocTypeLink_Name').focus(true, 100);
            else*/
                this.buttons[3].focus();
        },
        initComponent: function()
        {
            // Форма с полями 
            var current_window = this;

            this.PlfDocTypeLinkForm = new Ext.form.FormPanel(
                {
                    autoHeight: true,
                    bodyStyle: 'padding: 5px',
                    border: false,
                    buttonAlign: 'left',
                    frame: true,
                    id: 'PlfDocTypeLinkForm',
                    labelAlign: 'right',
                    labelWidth: 180,
                    items:
                        [{
                            id: 'LPEW_Lpu_id',
                            name: 'Lpu_id',
                            value: 0,
                            xtype: 'hidden'
                        },{
                            name: 'PlfDocTypeLink_id',
                            value: 0,
                            xtype: 'hidden'
                        },{
                            anchor: '100%',
                            allowBlank: false,
                            comboSubject: 'Plf',
                            fieldLabel: lang['naimenovanie_faktora'],
                            hiddenName: 'Plf_id',
                            tabIndex: TABINDEX_LPEEW + 1,
                            xtype: 'swcommonsprcombo'
                        },{
                            anchor: '100%',
                            allowBlank: false,
                            comboSubject: 'PlfType',
                            fieldLabel: lang['tip_faktora'],
                            hiddenName: 'PlfType_id',
                            tabIndex: TABINDEX_LPEEW + 2,
                            xtype: 'swcommonsprcombo'
                        },{
                            anchor: '100%',
                            allowBlank: false,
                            comboSubject: 'DocTypeUsePlf',
                            fieldLabel: lang['dokument'],
                            hiddenName: 'DocTypeUsePlf_id',
                            tabIndex: TABINDEX_LPEEW + 3,
                            xtype: 'swcommonsprcombo'
                        },{
                            fieldLabel: lang['nomer_dokumenta'],
                            allowBlank: false,
                            xtype: 'textfield',
                            //disabled: true,
                            autoCreate: {tag: "input", maxLength: "60", autocomplete: "off"},
                            anchor: '100%',
                            name: 'PlfDocTypeLink_Num',
                            tabIndex: TABINDEX_LPEEW + 4
                        },{
                            fieldLabel: lang['data_vyidachi_dokumenta'],
                            allowBlank: false,
                            xtype: 'swdatefield',
                            plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
                            format: 'd.m.Y',
                            //disabled: true,
                            name: 'PlfDocTypeLink_GetDT',
                            tabIndex: TABINDEX_LPEEW + 5
                        },{
                            fieldLabel: lang['data_nachala_deystviya_faktora'],
                            xtype: 'swdatefield',
                            plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
                            format: 'd.m.Y',
                            //disabled: true,
                            name: 'PlfDocTypeLink_BegDT',
                            tabIndex: TABINDEX_LPEEW + 5
                        },{
                            fieldLabel: lang['data_okonchaniya_deystviya_faktora'],
                            xtype: 'swdatefield',
                            plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
                            format: 'd.m.Y',
                            //disabled: true,
                            name: 'PlfDocTypeLink_EndDT',
                            tabIndex: TABINDEX_LPEEW + 5
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
                            {name: 'PlfDocTypeLink_id'},
                            {name: 'Plf_id'},
                            {name: 'PlfType_id'},
                            {name: 'DocTypeUsePlf_id'},
                            {name: 'PlfDocTypeLink_Num'},
                            {name: 'PlfDocTypeLink_GetDT'},
                            {name: 'PlfDocTypeLink_BegDT'},
                            {name: 'PlfDocTypeLink_EndDT'}
                        ]),
                    url: '/?c=LpuPassport&m=savePlfDocTypeLink'
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
                    items: [this.PlfDocTypeLinkForm]
                });
            sw.Promed.swPlfDocTypeLinkEditWindow.superclass.initComponent.apply(this, arguments);
        }
    });