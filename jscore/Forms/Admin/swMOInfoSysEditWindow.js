/**
 * swMOInfoSysEditWindow - окно редактирования/добавления инфорамционной системы.
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

sw.Promed.swMOInfoSysEditWindow = Ext.extend(sw.Promed.BaseForm,
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
        id: 'MOInfoSysEditWindow',
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
		checkCost: function(field){
			var base_form = this.MOInfoSysEditForm.getForm(),
				value = base_form.findField(field).getValue();
			if (!Ext.isEmpty(value) && Ext.isEmpty(filterFloat(value))){
				base_form.findField(field).markInvalid();
				addToolTip(base_form.findField(field), lang['znachenie_ne_yavlyaetsya_chislom']);
				return false;
			} else {
				base_form.findField(field).clearInvalid();
				return true;
			}
		},
        doSave: function()
        {
            var form = this.findById('MOInfoSysEditForm');
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

			if (!this.checkCost('MOInfoSys_Cost')){
				Ext.Msg.alert(lang['oshibka'], lang['znachenie_v_pole_stoimost_is_ne_yavlyaetsya_chislom']);
				return false;
			}

			if (!this.checkCost('MOInfoSys_CostYear')){
				Ext.Msg.alert(lang['oshibka'], lang['znachenie_v_pole_stoimost_soprovojdeniya_is_ne_yavlyaetsya_chislom']);
				return false;
			}

            this.submit();
            return true;
        },
        submit: function()
        {
            var form = this.findById('MOInfoSysEditForm');
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
                            if (action.result.MOInfoSys_id)
                            {
                                current_window.hide();
                                Ext.getCmp('LpuPassportEditWindow').findById('LPEW_MOInfoSysGrid').loadData();
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
            sw.Promed.swMOInfoSysEditWindow.superclass.show.apply(this, arguments);
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
            this.findById('MOInfoSysEditForm').getForm().reset();
            this.callback = Ext.emptyFn;
            this.onHide = Ext.emptyFn;


            if (arguments[0].MOInfoSys_id)
                this.MOInfoSys_id = arguments[0].MOInfoSys_id;
            else
                this.MOInfoSys_id = null;

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
                if ( ( this.MOInfoSys_id ) && ( this.MOInfoSys_id > 0 ) )
                    this.action = "edit";
                else
                    this.action = "add";
            }

            var form = this.findById('MOInfoSysEditForm');
            form.getForm().setValues(arguments[0]);

            var loadMask = new Ext.LoadMask(this.getEl(),{msg: LOAD_WAIT});
            loadMask.show();
            switch (this.action)
            {
                case 'add':
                    this.setTitle(lang['informatsionnaya_sistema_dobavlenie']);
                    this.enableEdit(true);
                    loadMask.hide();
                    //form.getForm().clearInvalid();
                    form.getForm().findField('MOInfoSys_IntroDT').setValue(new Date());
                    break;
                case 'edit':
                    this.setTitle(lang['informatsionnaya_sistema_redaktirovanie']);
                    this.enableEdit(true);
                    break;
                case 'view':
                    this.setTitle(lang['informatsionnaya_sistema_prosmotr']);
                    this.enableEdit(false);
                    break;
            }

            if (this.action != 'add')
            {
                form.getForm().load(
                    {
                        params:
                        {
                            MOInfoSys_id: current_window.MOInfoSys_id,
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
                            current_window.findById('LPEW_MOInfoSys_id').setValue(current_window.MOInfoSys_id);
                        },
                        url: '/?c=LpuPassport&m=loadMOInfoSys'
                    });
                //this.findById('LPEW_MOInfoSys_Name').getStore().load();
            }
            if ( this.action != 'view' )
                Ext.getCmp('LPEW_MOInfoSys_Name').focus(true, 100);
            else
                this.buttons[3].focus();
        },
        initComponent: function()
        {
            // Форма с полями 
            var current_window = this;

            this.MOInfoSysEditForm = new Ext.form.FormPanel(
                {
                    autoHeight: true,
                    bodyStyle: 'padding: 5px',
                    border: false,
                    buttonAlign: 'left',
                    frame: true,
                    id: 'MOInfoSysEditForm',
                    labelAlign: 'right',
                    labelWidth: 180,
                    items:
                        [{
                            id: 'LPEW_Lpu_id',
                            name: 'Lpu_id',
                            value: 0,
                            xtype: 'hidden'
                        },{
                            id: 'LPEW_MOInfoSys_id',
                            name: 'MOInfoSys_id',
                            value: 0,
                            xtype: 'hidden'
                        },{
                            fieldLabel: lang['priznak_soprovojdeniya'],
                            xtype: 'swcheckbox',
                            anchor: '100%',
                            name: 'MOInfoSys_IsMainten',
                            tabIndex: TABINDEX_LPEEW + 1
                        },{
                            fieldLabel: lang['naimenovanie_is'],
                            allowBlank: false,
                            xtype: 'textfield',
                            //disabled: true,
                            autoCreate: {tag: "input", maxLength: "90", autocomplete: "off"},
                            anchor: '100%',
                            id: 'LPEW_MOInfoSys_Name',
                            name: 'MOInfoSys_Name',
                            tabIndex: TABINDEX_LPEEW + 5
                        },{
                            anchor: '100%',
                            comboSubject: 'DInfSys',
                            allowBlank: false,
                            //disabled: true,
                            fieldLabel: lang['tip_is'],
                            hiddenName: 'DInfSys_id',
                            tabIndex: TABINDEX_LPEEW + 10,
                            xtype: 'swcommonsprcombo'
                        },{
                            fieldLabel: lang['naimenovanie_razrabotchika'],
                            allowBlank: false,
                            xtype: 'textfield',
                            //disabled: true,
                            autoCreate: {tag: "input", maxLength: "60", autocomplete: "off"},
                            anchor: '100%',
                            name: 'MOInfoSys_NameDeveloper',
                            tabIndex: TABINDEX_LPEEW + 15
                        },{
                            fieldLabel: lang['data_vnedreniya'],
                            allowBlank: false,
                            xtype: 'swdatefield',
                            plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
                            format: 'd.m.Y',
                            //disabled: true,
                            name: 'MOInfoSys_IntroDT',
                            tabIndex: TABINDEX_LPEEW + 20
                        },{
							allowNegative: false,
							allowDecimals: true,
							enableKeyEvents: true,
                            fieldLabel: lang['stoimost_is'] +getCurrencyType(),
                            xtype: 'textfield',
							listeners: {
								'change': function(){
									current_window.checkCost('MOInfoSys_Cost');
								}
							},
							maskRe: /[\d]|\./,
                            maxLength:16,
                            anchor: '100%',
                            name: 'MOInfoSys_Cost',
                            tabIndex: TABINDEX_LPEEW + 25
                        },{
							allowNegative: false,
							allowDecimals: true,
                            fieldLabel: lang['stoimost_soprovojdeniya_is_v_god'] +getCurrencyType(),
                            xtype: 'textfield',
							listeners: {
								'change': function(){
									current_window.checkCost('MOInfoSys_CostYear');
								}
							},
							maskRe: /[\d]|\./,
                            maxLength:16,
                            anchor: '100%',
                            name: 'MOInfoSys_CostYear',
                            tabIndex: TABINDEX_LPEEW + 30
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
                            {name: 'MOInfoSys_id'},
                            {name: 'MOInfoSys_Name'},
                            {name: 'DInfSys_id'},
                            {name: 'MOInfoSys_Cost'},
                            {name: 'MOInfoSys_CostYear'},
                            {name: 'MOInfoSys_IntroDT'},
                            {name: 'MOInfoSys_IsMainten'},
                            {name: 'MOInfoSys_NameDeveloper'}
                        ]),
                    url: '/?c=LpuPassport&m=saveMOInfoSys'
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
                            tabIndex: TABINDEX_LPEEW + 35,
                            text: BTN_FRMSAVE
                        },
                        {
                            text: '-'
                        },
                        HelpButton(this, TABINDEX_LPEEW + 37),
                        {
                            handler: function()
                            {
                                this.ownerCt.hide();
                            },
                            iconCls: 'cancel16',
                            tabIndex: TABINDEX_LPEEW + 40,
                            text: BTN_FRMCANCEL
                        }],
                    items: [this.MOInfoSysEditForm]
                });
            sw.Promed.swMOInfoSysEditWindow.superclass.initComponent.apply(this, arguments);
        }
    });