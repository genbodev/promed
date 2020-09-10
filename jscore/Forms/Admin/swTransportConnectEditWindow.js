/**
 * swTransportConnectEditWindow - окно редактирования/добавления связи площадки МО с транспортным усзлом
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

sw.Promed.swTransportConnectEditWindow = Ext.extend(sw.Promed.BaseForm,
    {
        action: null,
        autoHeight: true,
        buttonAlign: 'left',
        callback: Ext.emptyFn,
        closable: true,
        closeAction: 'hide',
        draggable: true,
        split: true,
        width: 600,
        layout: 'form',
        id: 'TransportConnectEditWindow',
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
            var form = this.findById('TransportConnectEditForm');
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
            var form = this.findById('TransportConnectEditForm');
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
                            if (action.result.TransportConnect_id)
                            {
                                current_window.hide();
                                Ext.getCmp('LpuPassportEditWindow').findById('LPEW_TransportConnectGrid').loadData();
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
            var form = this.TransportConnectEditForm.getForm();
            this.lists = [];
            this.editFields = [];

            this.getFieldsLists(form, {
                needConstructComboLists: true,
                needConstructEditFields: true
            });

            if (enable)
            {
                (this.editFields).forEach(function(rec){
                    rec.enable();
                });

                this.buttons[0].enable();
            } else {
                (this.editFields).forEach(function(rec){
                    rec.disable();
                });
                this.buttons[0].disable();
            }
        },
        show: function(){
            
            sw.Promed.swTransportConnectEditWindow.superclass.show.apply(this, arguments);
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
            this.findById('TransportConnectEditForm').getForm().reset();
            this.callback = Ext.emptyFn;
            this.onHide = Ext.emptyFn;

            if (arguments[0].TransportConnect_id)
                this.TransportConnect_id = arguments[0].TransportConnect_id;
            else
                this.TransportConnect_id = null;

            if (arguments[0].Lpu_id)
                this.Lpu_id = arguments[0].Lpu_id;
            else
                this.Lpu_id = null;

            if (arguments[0].formParams.MOArea_id)
                var MOArea_id = arguments[0].formParams.MOArea_id;
            else
                var MOArea_id = null;

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
                if ( ( this.TransportConnect_id ) && ( this.TransportConnect_id > 0 ) )
                    this.action = "edit";
                else
                    this.action = "add";
            }

            var form = this.findById('TransportConnectEditForm'),
                base_form = form.getForm();

            form.getForm().setValues(arguments[0].formParams);

            base_form.findField('MOArea_id').getStore().load({
                callback: function() {
                    var index = base_form.findField('MOArea_id').getStore().findBy(function(rec) {
                        return (rec.get('MOArea_id') == MOArea_id);
                    });

                    if ( index >= 0 ) {
                        base_form.findField('MOArea_id').setValue(MOArea_id);
                    }
                    else {
                        base_form.findField('MOArea_id').clearValue();
                    }
                },
                params: {
                    Lpu_id: this.Lpu_id
                }
            });

            switch (this.action) {
                case 'add':
                    this.setTitle(lang['svyaz_s_transportnyimi_uzlami_dobavlenie']);
                    this.enableEdit(true);
                    //loadMask.hide();
                    form.getForm().clearInvalid();
                    break;
                case 'edit':
                    this.setTitle(lang['svyaz_s_transportnyimi_uzlami_redaktirovanie']);
                    this.enableEdit(true);
                    break;
                case 'view':
                    this.setTitle(lang['svyaz_s_transportnyimi_uzlami_prosmotr']);
                    this.enableEdit(false);
                    break;
            }

            if ( this.action != 'view' )
                base_form.findField('TransportConnect_Station').focus(true, 100);
            else
                this.buttons[3].focus();
        },
        initComponent: function()
        {
			var _this = this;
            // Форма с полями 
            this.TransportConnectEditForm = new Ext.form.FormPanel(
                {
                    autoHeight: true,
                    bodyStyle: 'padding: 5px',
                    border: false,
                    buttonAlign: 'left',
                    frame: true,
                    id: 'TransportConnectEditForm',
                    labelAlign: 'right',
                    labelWidth: 180,
                    items:[{
                            name: 'TransportConnect_id',
                            id: 'TCEW_TransportConnect_id',
                            xtype: 'hidden'
                        },{
                            displayField: 'MOArea_Name',
							allowBlank: false,
                            fieldLabel: lang['naimenovanie_ploschadki'],
                            codeField: 'MOArea_Member',
                            hiddenName: 'MOArea_id',
                            id: 'TCEW_MOArea_id',
                            anchor: '100%',
                            editable: false,
                            mode: 'local',
                            resizable: true,
                            store: new Ext.data.Store({
                                autoLoad: false,
                                reader: new Ext.data.JsonReader({
                                    id: 'MOArea_id'
                                }, [
                                    { name: 'MOArea_id', mapping: 'MOArea_id' },
                                    { name: 'MOArea_Member', mapping: 'MOArea_Member' },
                                    { name: 'MOArea_Name', mapping: 'MOArea_Name' }
                                ]),
                                url:'/?c=LpuPassport&m=loadMOArea'
                            }),
                            tpl: new Ext.XTemplate(
                                '<tpl for="."><div class="x-combo-list-item">',
                                '<font color="red">{MOArea_Member}</font>&nbsp; {MOArea_Name}',
                                '</div></tpl>'
                            ),
                            triggerAction: 'all',
                            valueField: 'MOArea_id',
                            tabIndex: TABINDEX_LPEEW + 2,
                            xtype: 'swbaselocalcombo'
                        },{
                            fieldLabel: lang['blijayshaya_stantsiya'],
                            xtype: 'textfield',
                            name: 'TransportConnect_Station',
                            id: 'TCEW_TransportConnect_Station',
                            tabIndex: TABINDEX_LPEEW + 3
                        },{
                            fieldLabel: lang['rasstoyanie_do_stantsii_km'],
                            xtype: 'numberfield',
                            name: 'TransportConnect_DisStation',
                            id: 'TCEW_TransportConnect_DisStation',
                            tabIndex: TABINDEX_LPEEW + 3
                        },{
                            fieldLabel: lang['blijayshiy_aeroport'],
                            xtype: 'textfield',
                            name: 'TransportConnect_Airport',
                            id: 'TCEW_TransportConnect_Airport',
                            tabIndex: TABINDEX_LPEEW + 3
                        },{
                            fieldLabel: lang['rasstoyanie_do_aeroporta_km'],
                            xtype: 'numberfield',
                            name: 'TransportConnect_DisAirport',
                            id: 'TCEW_TransportConnect_DisAirport',
                            tabIndex: TABINDEX_LPEEW + 3
                        },{
                            fieldLabel: lang['blijayshiy_avtovokzal'],
                            xtype: 'textfield',
                            name: 'TransportConnect_Railway',
                            id: 'TCEW_TransportConnect_Railway',
                            tabIndex: TABINDEX_LPEEW + 3
                        },{
                            fieldLabel: lang['rasstoyanie_do_avtovokzala_km'],
                            xtype: 'numberfield',
                            name: 'TransportConnect_DisRailway',
                            id: 'TCEW_TransportConnect_DisRailway',
                            tabIndex: TABINDEX_LPEEW + 3
                        },{
                            fieldLabel: lang['blijayshaya_vertoletnaya_ploschadka'],
                            xtype: 'textfield',
                            name: 'TransportConnect_Heliport',
                            id: 'TCEW_TransportConnect_Heliport',
                            tabIndex: TABINDEX_LPEEW + 3
                        },{
                            fieldLabel: lang['rasstoyanie_do_vertoletnoy_ploschadki_km'],
                            xtype: 'numberfield',
                            name: 'TransportConnect_DisHeliport',
                            id: 'TCEW_TransportConnect_DisHeliport',
                            tabIndex: TABINDEX_LPEEW + 3
                        },{
                            fieldLabel: lang['blijayshaya_glavnaya_doroga'],
                            xtype: 'textfield',
                            name: 'TransportConnect_MainRoad',
                            id: 'TCEW_TransportConnect_MainRoad',
                            tabIndex: TABINDEX_LPEEW + 3
                        }
                    ],
                //},
                    reader: new Ext.data.JsonReader(
                        {
                            success: function()
                            {
                                //
                            }
                        },
                        [
                            {name: 'TransportConnect_id'},
                            {name: 'MOArea_id'},
                            {name: 'TransportConnect_AreaIdent'},
                            {name: 'TransportConnect_Station'},
                            {name: 'TransportConnect_DisStation'},
                            {name: 'TransportConnect_Airport'},
                            {name: 'TransportConnect_DisAirport'},
                            {name: 'TransportConnect_Railway'},
                            {name: 'TransportConnect_DisRailway'},
                            {name: 'TransportConnect_Heliport'},
                            {name: 'TransportConnect_DisHeliport'},
                            {name: 'TransportConnect_MainRoad'}
                        ]),
                    url: '/?c=LpuPassport&m=saveTransportConnect'
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
                    items: [this.TransportConnectEditForm]
                });
            sw.Promed.swTransportConnectEditWindow.superclass.initComponent.apply(this, arguments);
        }
    });