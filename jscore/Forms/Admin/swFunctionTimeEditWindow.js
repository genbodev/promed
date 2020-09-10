/**
 * swFunctionTimeEditWindow - окно редактирования/добавления периода функционирования.
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Admin
 * @access       public
 * @copyright    Copyright (c) 2009-2014 Swan Ltd.
 * @version      21.08.2014
 */

sw.Promed.swFunctionTimeEditWindow = Ext.extend(sw.Promed.BaseForm,
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
        id: 'swFunctionTimeEditWindow',
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
            var form = this.findById('FunctionTimeForm');
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
            var form = this.findById('FunctionTimeForm');
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
                            if (action.result.FunctionTime_id || action.result.FunctionTimePacs_id)
                            {
                                current_window.hide();
                                Ext.getCmp('LpuPassportEditWindow').findById('LPEW_FunctionTimeGrid').loadData();
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
            sw.Promed.swFunctionTimeEditWindow.superclass.show.apply(this, arguments);
            var _this = this;
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
            this.findById('FunctionTimeForm').getForm().reset();
            this.callback = Ext.emptyFn;
            this.onHide = Ext.emptyFn;

            if (arguments[0].FunctionTime_id)
                this.FunctionTime_id = arguments[0].FunctionTime_id;
            else
                this.FunctionTime_id = null;

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
                if ( ( this.FunctionTime_id ) && ( this.FunctionTime_id > 0 ) )
                    this.action = "edit";
                else
                    this.action = "add";
            }

            var form = this.findById('FunctionTimeForm'),
                base_form = form.getForm();
            form.getForm().setValues(arguments[0]);

            var loadMask = new Ext.LoadMask(this.getEl(),{msg: LOAD_WAIT});
            loadMask.show();
            switch ( this.action ) {
                case 'add':
                    this.setTitle(lang['period_funktsionirovaniya_dobavlenie']);
                    this.enableEdit(true);
                    loadMask.hide();
                    form.getForm().clearInvalid();
                break;
    
                case 'edit':
                case 'view':
                    if ( this.action == 'edit' ) {
                        this.setTitle(lang['period_funktsionirovaniya_redaktirovanie']);
                        this.enableEdit(true);
                    }
                    else {
                        this.setTitle(lang['period_funktsionirovaniya_prosmotr']);
                        this.enableEdit(false);
                    }
                    
                    if (Ext.isEmpty(_this.FunctionTime_id)) {
                        sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() { _this.hide(); });
                        return false;
                    } else {
                        base_form.load({
                            params: {
                                FunctionTime_id: _this.FunctionTime_id,
                                Lpu_id: _this.Lpu_id
                            },
                            failure: function() {
                                _this.getLoadMask().hide();
                                sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_periodov_funktsionirovaniya'], function() { _this.hide(); } );
                            },
                            success: function() {
                                _this.getLoadMask().hide();
                                base_form.clearInvalid();
                            },
                            url: '/?c=LpuPassport&m=loadFunctionTime'
                        });			
                    }
                break;
    
                default:
                    this.getLoadMask().hide();
                    this.hide();
                break;
            }

            this.buttons[3].focus();
        },
        initComponent: function()
        {
            // Форма с полями 
            var current_window = this;

            this.FunctionTimeForm = new Ext.form.FormPanel(
                {
                    autoHeight: true,
                    bodyStyle: 'padding: 5px',
                    border: false,
                    buttonAlign: 'left',
                    frame: true,
                    id: 'FunctionTimeForm',
                    labelAlign: 'right',
                    labelWidth: 180,
                    items:
                        [{
                            name: 'Lpu_id',
                            value: 0,
                            xtype: 'hidden'
                        },{
                            name: 'FunctionTime_id',
                            value: 0,
                            xtype: 'hidden'
                        },{
                            anchor: '100%',
                            allowBlank: false,
                            comboSubject: 'InstitutionFunction',
                            fieldLabel: lang['period_funktsionirovaniya'],
                            hiddenName: 'InstitutionFunction_id',
                            tabIndex: TABINDEX_LPEEW + 1,
                            xtype: 'swcommonsprcombo'
                        },{
                            fieldLabel: lang['data_nachala_perioda'],
                            xtype: 'swdatefield',
                            allowBlank: false,
                            plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
                            format: 'd.m.Y',
                            //disabled: true,
                            name: 'FunctionTime_begDate',
                            tabIndex: TABINDEX_LPEEW + 5
                        },{
                            fieldLabel: lang['data_okonchaniya_perioda'],
                            xtype: 'swdatefield',
                            plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
                            format: 'd.m.Y',
                            //disabled: true,
                            name: 'FunctionTime_endDate',
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
                            {name: 'FunctionTime_id'},
                            {name: 'InstitutionFunction_id'},
                            {name: 'FunctionTime_begDate'},
                            {name: 'FunctionTime_endDate'}
                        ]),
                    url: '/?c=LpuPassport&m=saveFunctionTime'
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
                    items: [this.FunctionTimeForm]
                });
            sw.Promed.swFunctionTimeEditWindow.superclass.initComponent.apply(this, arguments);
        }
    });