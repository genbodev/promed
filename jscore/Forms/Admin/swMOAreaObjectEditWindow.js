/**
 * swMOAreaObjectEditWindow - окно редактирования/добавления объекта инфраструктуры
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

sw.Promed.swMOAreaObjectEditWindow = Ext.extend(sw.Promed.BaseForm,
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
        id: 'MOAreaObjectEditWindow',
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
        resizable: true,
        doSave: function()
        {
            var form = this.findById('MOAreaObjectEditForm');
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
            var form = this.findById('MOAreaObjectEditForm');
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
                            if (action.result.MOAreaObject_id || action.result.MOAreaObjectPacs_id)
                            {
                                current_window.hide();
                                Ext.getCmp('LpuPassportEditWindow').findById('LPEW_MOAreaObjectGrid').loadData();
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
                var form = this.findById('MOAreaObjectEditForm');
                form.getForm().findField('MOAreaObject_id').enable();
                form.getForm().findField('DObjInfrastructure_id').enable();
                form.getForm().findField('MOAreaObject_Count').enable();
                form.getForm().findField('MOAreaObject_Member').enable();
                this.buttons[0].enable();
            }
            else
            {
                var form = this.findById('MOAreaObjectEditForm');
                form.getForm().findField('MOAreaObject_id').disable();
                form.getForm().findField('DObjInfrastructure_id').disable();
                form.getForm().findField('MOAreaObject_Count').disable();
                form.getForm().findField('MOAreaObject_Member').disable();
                this.buttons[0].disable();
            }
        },
        show: function()
        {
            sw.Promed.swMOAreaObjectEditWindow.superclass.show.apply(this, arguments);
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
            this.findById('MOAreaObjectEditForm').getForm().reset();
            this.callback = Ext.emptyFn;
            this.onHide = Ext.emptyFn;

            if (arguments[0].MOAreaObject_id)
                this.MOAreaObject_id = arguments[0].MOAreaObject_id;
            else
                this.MOAreaObject_id = null;

            if (arguments[0].DObjInfrastructure_id)
                this.DObjInfrastructure_id = arguments[0].DObjInfrastructure_id;
            else
                this.DObjInfrastructure_id = null;

            if (arguments[0].Lpu_id)
                this.Lpu_id = arguments[0].Lpu_id;
            else
                this.Lpu_id = null;

            if (arguments[0].MOAreaObject_Count)
                this.MOAreaObject_Count = arguments[0].MOAreaObject_Count;
            else
                this.MOAreaObject_Count = null;

            if (arguments[0].MOAreaObject_Member)
                this.MOAreaObject_Member = arguments[0].MOAreaObject_Member;
            else
                this.MOAreaObject_Member = null;

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
                if ( ( this.MOAreaObject_id ) && ( this.MOAreaObject_id > 0 ) )
                    this.action = "edit";
                else
                    this.action = "add";
            }

            var form = this.findById('MOAreaObjectEditForm');
            form.getForm().setValues(arguments[0]);

            var loadMask = new Ext.LoadMask(this.getEl(),{msg: LOAD_WAIT});
            loadMask.show();
            switch (this.action)
            {
                case 'add':
                    this.setTitle(lang['obyekt_infrastrukturyi_dobavlenie']);
                    this.enableEdit(true);
                    loadMask.hide();
                    form.getForm().clearInvalid();
                    break;
                case 'edit':
                    this.setTitle(lang['obyekt_infrastrukturyi_redaktirovanie']);
                    this.enableEdit(true);
                    break;
                case 'view':
                    this.setTitle(lang['obyekt_infrastrukturyi_prosmotr']);
                    this.enableEdit(false);
                    break;
            }

            if (this.action != 'add')
            {
                form.getForm().load(
                    {
                        params:
                        {
                            MOAreaObject_id: current_window.MOAreaObject_id,
                            DObjInfrastructure_id: current_window.DObjInfrastructure_id,
                            MOAreaObject_Count: current_window.MOAreaObject_Count,
                            MOAreaObject_Member: current_window.MOAreaObject_Member,
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
                        url: '/?c=LpuPassport&m=loadMOAreaObject'
                    });
            }
            if ( this.action != 'view' )
                Ext.getCmp('LPEW_DObjInfrastructure_id').focus(true, 100);
            else
                this.buttons[3].focus();
        },
        initComponent: function()
        {
            // Форма с полями 
            this.MOAreaObjectEditForm = new Ext.form.FormPanel(
                {
                    autoHeight: true,
                    bodyStyle: 'padding: 5px',
                    border: false,
                    buttonAlign: 'left',
                    frame: true,
                    id: 'MOAreaObjectEditForm',
                    labelAlign: 'right',
                    labelWidth: 180,
                    items:
                        [{
                            id: 'LPEW_Lpu_id',
                            name: 'Lpu_id',
                            value: 0,
                            xtype: 'hidden'
                        },{
                            name: 'MOAreaObject_id',
                            value: 0,
                            xtype: 'hidden'
                        },{
                            fieldLabel: lang['naimenovanie_obyekta'],
                            allowBlank: false,
                            xtype: 'swcommonsprcombo',
                            hiddenName: 'DObjInfrastructure_id',
                            comboSubject: 'DObjInfrastructure',
                            id: 'LPEW_DObjInfrastructure_id',
                            anchor: '100%',
                            tabIndex: TABINDEX_LPEEW + 3
                        },{
                            fieldLabel: lang['kolichestvo_obyektov'],
                            xtype: 'textfield',
                            anchor: '100%',
                            name: 'MOAreaObject_Count',
                            tabIndex: TABINDEX_LPEEW + 3
                        },{
                            fieldLabel: lang['identifikator_uchastka'],
                            allowBlank: false,
                            xtype: 'textfield',
                            anchor: '100%',
                            name: 'MOAreaObject_Member',
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
                            {name: 'Lpu_id'},
                            {name: 'MOAreaObject_id'},
                            {name: 'DObjInfrastructure_id'},
                            {name: 'MOAreaObject_Count'},
                            {name: 'MOAreaObject_Member'}
                        ]),
                    url: '/?c=LpuPassport&m=saveMOAreaObject'
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
                    items: [this.MOAreaObjectEditForm]
                });
            sw.Promed.swMOAreaObjectEditWindow.superclass.initComponent.apply(this, arguments);
        }
    });