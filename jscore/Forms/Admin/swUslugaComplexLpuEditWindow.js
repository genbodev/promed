/**
 * swUslugaComplexLpuEditWindow - окно редактирования/добавления направления оказания медициской помощи.
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Admin
 * @access       public
 * @copyright    Copyright (c) 2009-2011 Swan Ltd.
 * @version      19.08.2014
 */

sw.Promed.swUslugaComplexLpuEditWindow = Ext.extend(sw.Promed.BaseForm,
    {
        action: null,
        autoHeight: true,
        buttonAlign: 'left',
        callback: Ext.emptyFn,
        closable: true,
        closeAction: 'hide',
        draggable: true,
        split: true,
        width: 550,
        layout: 'form',
        id: 'UslugaComplexLpuEditWindow',
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
            var form = this.findById('UslugaComplexLpuEditForm');
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
            var form = this.findById('UslugaComplexLpuEditForm');
            var _this = this;
            var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
            loadMask.show();
            form.getForm().submit(
                {
                    params:
                    {
                        action: _this.action
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
                            if (action.result.UslugaComplexLpu_id)
                            {
                                _this.hide();
                                Ext.getCmp('LpuPassportEditWindow').findById('LPEW_UslugaComplexLpuGrid').loadData();
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
            sw.Promed.swUslugaComplexLpuEditWindow.superclass.show.apply(this, arguments);
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
            this.findById('UslugaComplexLpuEditForm').getForm().reset();
            this.callback = Ext.emptyFn;
            this.onHide = Ext.emptyFn;


            if (arguments[0].UslugaComplexLpu_id)
                this.UslugaComplexLpu_id = arguments[0].UslugaComplexLpu_id;
            else
                this.UslugaComplexLpu_id = null;

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
                if ( ( this.UslugaComplexLpu_id ) && ( this.UslugaComplexLpu_id > 0 ) )
                    this.action = "edit";
                else
                    this.action = "add";
            }

            var form = this.findById('UslugaComplexLpuEditForm'),
                base_form = form.getForm();

            form.getForm().setValues(arguments[0]);
            var loadMask = new Ext.LoadMask(this.getEl(),{msg: LOAD_WAIT});
            loadMask.show();
            switch (this.action)
            {
                case 'add':
                    this.setTitle(lang['usluga_dobavlenie']);
                    this.enableEdit(true);
                    loadMask.hide();
                    form.getForm().clearInvalid();
                    break;
                case 'edit':
                    this.setTitle(lang['usluga_redaktirovanie']);
                    this.enableEdit(true);
                    break;
                case 'view':
                    this.setTitle(lang['usluga_prosmotr']);
                    this.enableEdit(false);
                    break;
            }

            if (this.action != 'add'){
                base_form.findField('UslugaComplex_id').getStore().load({callback: function(){
                    form.getForm().load({
                        params:
                        {
                            UslugaComplexLpu_id: _this.UslugaComplexLpu_id,
                            Lpu_id: _this.Lpu_id
                        },
                        failure: function(f, o, a)
                        {
                            loadMask.hide();
                            sw.swMsg.show(
                                {
                                    buttons: Ext.Msg.OK,
                                    fn: function()
                                    {
                                        _this.hide();
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
                        url: '/?c=LpuPassport&m=loadUslugaComplexLpu'
                    });
                }});
            } else if ( this.action == 'view' ){
                this.buttons[3].focus();
            }

        },
        initComponent: function()
        {
            // Форма с полями 
            var _this = this;

            this.UslugaComplexLpuEditForm = new Ext.form.FormPanel(
                {
                    autoHeight: true,
                    bodyStyle: 'padding: 5px',
                    border: false,
                    buttonAlign: 'left',
                    frame: true,
                    id: 'UslugaComplexLpuEditForm',
                    labelAlign: 'right',
                    labelWidth: 180,
                    items:
                        [{
                            id: 'UCL_Lpu_id',
                            name: 'Lpu_id',
                            value: 0,
                            xtype: 'hidden'
                        },{
                            id: 'UCL_UslugaComplexLpu_id',
                            name: 'UslugaComplexLpu_id',
                            value: 0,
                            xtype: 'hidden'
                        },{
                            fieldLabel: lang['usluga_gost'],
                            name: 'UslugaComplex_id',
                            tabindex: TABINDEX_ATAEW + 0,
                            allowBlank:false,
                            xtype: 'swuslugacomplexgostcombo',
                            showUslugaComplexLpuSection: false,
                            listWidth: 450,
                            anchor:'100%'
                        },{
                            fieldLabel: lang['data_nachala_okazaniya_uslugi'],
                            allowBlank: false,
                            xtype: 'swdatefield',
                            plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
                            format: 'd.m.Y',
                            //disabled: true,
                            name: 'UslugaComplexLpu_begDate',
                            tabIndex: TABINDEX_LPEEW + 5
                        },{
                            fieldLabel: lang['data_okonchaniya_okazaniya_uslugi'],
                            xtype: 'swdatefield',
                            plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
                            format: 'd.m.Y',
                            //disabled: true,
                            name: 'UslugaComplexLpu_endDate',
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
                            {name: 'UslugaComplexLpu_id'},
                            {name: 'UslugaComplex_id'},
                            {name: 'Lpu_id'},
                            {name: 'UslugaComplexLpu_begDate'},
                            {name: 'UslugaComplexLpu_endDate'}
                        ]),
                    url: '/?c=LpuPassport&m=saveUslugaComplexLpu'
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
                    items: [this.UslugaComplexLpuEditForm]
                });
            sw.Promed.swUslugaComplexLpuEditWindow.superclass.initComponent.apply(this, arguments);
        }
    });