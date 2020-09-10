/**
 * swLpuStaffEditWindow - окно редактирования/добавления штатного расписания.
 */

sw.Promed.swLpuStaffEditWindow = Ext.extend(sw.Promed.BaseForm,
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
        id: 'LpuStaffEditWindow',
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
            var form = this.findById('LpuStaffEditForm');
            var begDate= form.getForm().findField('LpuStaff_begDate').getValue();
            var endDate = form.getForm().findField('LpuStaff_endDate').getValue();
            var errDate_msg = false;
            if(begDate && endDate && endDate < begDate){
                var errDate_msg = 'Дата начала не может быть раньше даты окончания';
            }
            if ( errDate_msg || !form.getForm().isValid() )
            {
                sw.swMsg.show(
                    {
                        buttons: Ext.Msg.OK,
                        fn: function()
                        {
                            form.getFirstInvalidEl().focus(true);
                        },
                        icon: Ext.Msg.WARNING,
                        msg: (errDate_msg) ? errDate_msg : ERR_INVFIELDS_MSG,
                        title: ERR_INVFIELDS_TIT
                    });
                return false;
            }


            this.submit();
            return true;
        },
        submit: function()
        {
            var form = this.findById('LpuStaffEditForm');
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
                            if (action.result.LpuStaff_id )
                            {
                                current_window.hide();
                                current_window.returnFunc(current_window.owner, action.result.LpuStaff_id);

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
            var form = this.findById('LpuStaffEditForm');
            if (enable)
            {
                form.getForm().findField('LpuStaff_Num').enable();
                form.getForm().findField('LpuStaff_Descript').enable();
                form.getForm().findField('LpuStaff_ApprovalDT').enable();
                form.getForm().findField('LpuStaff_begDate').enable();
                form.getForm().findField('LpuStaff_endDate').enable();
                this.buttons[0].enable();
            }
            else
            {
                form.getForm().findField('LpuStaff_Num').disable();
                form.getForm().findField('LpuStaff_Descript').disable();
                form.getForm().findField('LpuStaff_ApprovalDT').disable();
                form.getForm().findField('LpuStaff_begDate').disable();
                form.getForm().findField('LpuStaff_endDate').disable();
                this.buttons[0].disable();
            }
        },
        show: function()
        {
            sw.Promed.swLpuStaffEditWindow.superclass.show.apply(this, arguments);
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

            this.findById('LpuStaffEditForm').getForm().reset();
            this.callback = Ext.emptyFn;
            this.onHide = Ext.emptyFn;

            if (arguments[0].LpuStaff_id)
                this.LpuStaff_id = arguments[0].LpuStaff_id;
            else
                this.LpuStaff_id = null;
            
            if (arguments[0].Lpu_id) {
                this.Lpu_id = arguments[0].Lpu_id;
            }else{
                this.Lpu_id = null;
            }

            if (arguments[0].callback)
            {
                this.callback = arguments[0].callback;
                this.returnFunc = arguments[0].callback;
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
                if ( ( this.LpuStaff_id ) && ( this.LpuStaff_id > 0 ) )
                    this.action = "edit";
                else
                    this.action = "add";
            }

            var form = this.findById('LpuStaffEditForm');
            form.getForm().setValues(arguments[0]);

            var loadMask = new Ext.LoadMask(this.getEl(),{msg: LOAD_WAIT});
            loadMask.show();
            switch (this.action)
            {
                case 'add':
                    this.setTitle(lang['shtatnoe_raspisanie_dobavlenie']);
                    this.enableEdit(true);
                    loadMask.hide();
                    form.getForm().clearInvalid();
                    break;
                case 'edit':
                    this.setTitle(lang['shtatnoe_raspisanie_redaktirovanie']);
                    this.enableEdit(true);
                    break;
                case 'view':
                    this.setTitle(lang['shtatnoe_raspisanie_prosmotr']);
                    this.enableEdit(false);
                    break;
            }

            if (this.action != 'add')
            {
                form.getForm().load(
                    {
                        params:
                        {
                            LpuStaff_id: current_window.LpuStaff_id
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
                        },
                        url: '/?c=LpuStructure&m=getLpuStaffGridDetail'
                    });
            }
            if ( this.action != 'view' ){
                Ext.getCmp('LpuStaff_Num_id').focus(true, 100);
            }else{
                this.buttons[2].focus();
            }
        },
        returnFunc: function(owner, kid) {},
        initComponent: function()
        {
            // Форма с полями 
            this.LpuStaffEditForm = new Ext.form.FormPanel(
                {
                    autoHeight: true,
                    bodyStyle: 'padding: 5px',
                    border: false,
                    buttonAlign: 'left',
                    frame: true,
                    id: 'LpuStaffEditForm',
                    labelAlign: 'right',
                    labelWidth: 130,
                    items:[{
                            id: 'LpuStaff_id',
                            name: 'LpuStaff_id',
                            value: 0,
                            xtype: 'hidden'
                        },{
                            id: 'Lpu_id',
                            name: 'Lpu_id',
                            value: 0,
                            xtype: 'hidden'
                        },{
                            fieldLabel: lang['nomer'],
                            allowBlank: false,
                            xtype: 'textfield',
                            autoCreate: {tag: "input", maxLength: "90", autocomplete: "off"},
                            anchor: '100%',
                            name: 'LpuStaff_Num',
                            id: 'LpuStaff_Num_id',
                            maskRe: /[0-9]/,
                        },{
                            fieldLabel: lang['opisanie'],
                            allowBlank: true,
                            xtype: 'textfield',
                            autoCreate: {tag: "input", autocomplete: "off"},
                            anchor: '100%',
                            id: 'LpuStaff_Descript',
                            name: 'LpuStaff_Descript',
                        },{
                            fieldLabel: lang['data_utverjdeniya'],
                            allowBlank: false,
                            xtype: 'swdatefield',
                            autoCreate: {tag: "input", autocomplete: "off"},
                            name: 'LpuStaff_ApprovalDT',
                            width: 120
                        },{
                            fieldLabel: lang['data_nachala'],
                            allowBlank: false,
                            xtype: 'swdatefield',
                            autoCreate: {tag: "input", autocomplete: "off"},
                            name: 'LpuStaff_begDate',
                            width: 120
                        },{
                            fieldLabel: lang['data_okonchaniya'],
                            allowBlank: true,
                            xtype: 'swdatefield',
                            autoCreate: {tag: "input", autocomplete: "off"},
                            name: 'LpuStaff_endDate',
                            width: 120
                        }
                    ],
                    reader: new Ext.data.JsonReader(
                        {
                            success: function()
                            {
                                //
                            }
                        },
                        [
                            {name: 'LpuStaff_id'},
                            {name: 'LpuStaff_Num'},
                            {name: 'LpuStaff_Descript'},
                            {name: 'LpuStaff_ApprovalDT'},
                            {name: 'LpuStaff_begDate'},
                            {name: 'LpuStaff_endDate'},
                            {name: 'Lpu_id'}
                        ]),
                    url: '/?c=LpuStructure&m=saveLpuStaffGridDetail'
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
                        //HelpButton(this),
                        {
                            handler: function()
                            {
                                this.ownerCt.hide();
                            },
                            iconCls: 'cancel16',
                            tabIndex: TABINDEX_LPEEW + 17,
                            text: BTN_FRMCLOSE
                        }],
                    items: [this.LpuStaffEditForm]
                });
            sw.Promed.swLpuStaffEditWindow.superclass.initComponent.apply(this, arguments);
        }
    });