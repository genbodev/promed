/**
 * swStaffEditWindow - окно редактирования/добавления мероприятия.
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

sw.Promed.swStaffEditWindow = Ext.extend(sw.Promed.BaseForm,
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
        id: 'StaffEditWindow',
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
            var form = this.findById('StaffEditForm');
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
            var form = this.findById('StaffEditForm');
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
                            if (action.result.Staff_id )
                            {
                                current_window.hide();
                                //Ext.getCmp('LpuSectionBedStateEditForm').findById('LPEW_StaffGrid').loadData();
                                //getWnd('swSectionBedStateForm').swStaffOSMPanel.loadData();
                                current_window.returnFunc(current_window.owner, action.result.Staff_id);

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
            var form = this.findById('StaffEditForm');
            if (enable)
            {
                form.getForm().findField('Staff_Num').enable();
                form.getForm().findField('Staff_OrgName').enable();
                form.getForm().findField('Staff_OrgDT').enable();
                form.getForm().findField('Staff_OrgBasis').enable();
                this.buttons[0].enable();
            }
            else
            {
                form.getForm().findField('Staff_Num').disable();
                form.getForm().findField('Staff_OrgName').disable();
                form.getForm().findField('Staff_OrgDT').disable();
                form.getForm().findField('Staff_OrgBasis').disable();
                this.buttons[0].disable();
            }
        },
        show: function()
        {
            sw.Promed.swStaffEditWindow.superclass.show.apply(this, arguments);
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
            this.findById('StaffEditForm').getForm().reset();
            this.callback = Ext.emptyFn;
            this.onHide = Ext.emptyFn;

            if (arguments[0].Staff_id)
                this.Staff_id = arguments[0].Staff_id;
            else
                this.Staff_id = null;
			
			if (arguments[0].Lpu_id) {
					this.Lpu_id = arguments[0].Lpu_id;
				}
            else
				{
					this.Lpu_id = null;
				}

            if (arguments[0].Staff_Num)
                this.Staff_Num = arguments[0].Staff_Num;
            else
                this.Staff_Num = null;

            if (arguments[0].Staff_OrgName)
                this.Staff_OrgName = arguments[0].Staff_OrgName;
            else
                this.Staff_OrgName = null;

            if (arguments[0].Staff_OrgDT)
                this.Staff_OrgDT = arguments[0].Staff_OrgDT;
            else
                this.Staff_OrgDT = null;

            if (arguments[0].Staff_OrgBasis)
                this.Staff_OrgBasis = arguments[0].Staff_OrgBasis;
            else
                this.Staff_OrgBasis = null;

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
                if ( ( this.Staff_id ) && ( this.Staff_id > 0 ) )
                    this.action = "edit";
                else
                    this.action = "add";
            }

            var form = this.findById('StaffEditForm');
            form.getForm().setValues(arguments[0]);

            var loadMask = new Ext.LoadMask(this.getEl(),{msg: LOAD_WAIT});
            loadMask.show();
            switch (this.action)
            {
                case 'add':
                    this.setTitle(lang['meropriyatie_dobavlenie']);
                    this.enableEdit(true);
                    loadMask.hide();
                    form.getForm().clearInvalid();
                    break;
                case 'edit':
                    this.setTitle(lang['meropriyatie_redaktirovanie']);
                    this.enableEdit(true);
                    break;
                case 'view':
                    this.setTitle(lang['meropriyatie_prosmotr']);
                    this.enableEdit(false);
                    break;
            }

            if (this.action != 'add')
            {
                form.getForm().load(
                    {
                        params:
                        {
                            Staff_id: current_window.Staff_id
                            /*Staff_Num: current_window.Staff_Num,
                            Staff_OrgName: current_window.Staff_OrgName,
                            Staff_OrgDT: current_window.Staff_OrgDT,
                            Staff_OrgBasis: current_window.Staff_OrgBasis*/
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
                            //current_window.findById('Staff_id').setValue(current_window.Staff_id);
                        },
                        url: '/?c=LpuStructure&m=getStaffOSMGridDetail'
                    });
            }
            if ( this.action != 'view' )
                Ext.getCmp('Staff_Num_id').focus(true, 100);
            else
                this.buttons[3].focus();
        },
        returnFunc: function(owner, kid) {},
        initComponent: function()
        {
            // Форма с полями 

            this.StaffEditForm = new Ext.form.FormPanel(
                {
                    autoHeight: true,
                    bodyStyle: 'padding: 5px',
                    border: false,
                    buttonAlign: 'left',
                    frame: true,
                    id: 'StaffEditForm',
                    labelAlign: 'right',
                    labelWidth: 180,
                    items:
                        [{
                            id: 'Staff_id',
                            name: 'Staff_id',
                            value: 0,
                            xtype: 'hidden'
                        },{
                            id: 'Lpu_id',
                            name: 'Lpu_id',
                            value: 0,
                            xtype: 'hidden'
                        },{
                            fieldLabel: lang['nomer_shtata'],
                            allowBlank: false,
                            xtype: 'textfield',
                            autoCreate: {tag: "input", maxLength: "90", autocomplete: "off"},
                            anchor: '100%',
                            name: 'Staff_Num',
                            id: 'Staff_Num_id',
                            maskRe: /[0-9]/,
                            tabIndex: TABINDEX_LPEEW + 3
                        },{
                            fieldLabel: lang['naimenovanie_oshm'],
                            allowBlank: false,
                            xtype: 'textfield',
                            autoCreate: {tag: "input", maxLength: "90", autocomplete: "off"},
                            anchor: '100%',
                            name: 'Staff_OrgName',
                            tabIndex: TABINDEX_LPEEW + 3
                        },{
                            fieldLabel: lang['data_oshm'],
                            allowBlank: false,
                            xtype: 'swdatefield',
                            autoCreate: {tag: "input", maxLength: "90", autocomplete: "off"},
                            name: 'Staff_OrgDT',
                            tabIndex: TABINDEX_LPEEW + 3
                        },{
                            fieldLabel: lang['osnovanie_oshm'],
                            xtype: 'textfield',
                            autoCreate: {tag: "input", maxLength: "90", autocomplete: "off"},
                            anchor: '100%',
                            name: 'Staff_OrgBasis',
                            tabIndex: TABINDEX_LPEEW + 3
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
                            {name: 'Staff_id'},
                            {name: 'Staff_Num'},
                            {name: 'Staff_OrgName'},
                            {name: 'Staff_OrgDT'},
                            {name: 'Staff_OrgBasis'},
                            {name: 'Lpu_id'}
                        ]),
                    url: '/?c=LpuStructure&m=saveStaffOSMGridDetail'
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
                    items: [this.StaffEditForm]
                });
            sw.Promed.swStaffEditWindow.superclass.initComponent.apply(this, arguments);
        }
    });