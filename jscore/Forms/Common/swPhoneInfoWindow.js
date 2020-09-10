/**
* swPhoneInfoWindow - Окно отображения информации о полисе
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2013 Swan Ltd.
* @author       Samir Abakhri
* @version      22.12.2013
*/
sw.Promed.swPhoneInfoWindow = Ext.extend(sw.Promed.BaseForm,
    {
        action: null,
        autoHeight: true,
        buttonAlign: 'left',
        callback: Ext.emptyFn,
        closable: true,
        title:lang['telefon_patsienta'],
        closeAction: 'hide',
        draggable: true,
        split: true,
        width: 400,
        layout: 'form',
        id: 'PhoneInfoWindow',
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
            var form = this.findById('PhoneInfoForm');

            var phone_field = form.getForm().findField('Phone_Promed');
            var phone = String(phone_field.getValue()).replace(/[-\(\)_]/g,'');
            if(phone.length < 10){
                sw.swMsg.show(
                    {
                        buttons: Ext.Msg.OK,
                        fn: function()
                        {
                            form.getFirstInvalidEl().focus(true);
                        },
                        icon: Ext.Msg.WARNING,
                        msg: 'заполните номер телефона',
                        title: ERR_INVFIELDS_TIT
                    });
                return false;
            }
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
            var form = this.findById('PhoneInfoForm');
            var _this = this;
            var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
            //loadMask.show();
            form.getForm().submit(
                {
                    params:
                    {
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
                            if (action.result.Person_id)
                            {
                                sw.swMsg.show(
                                {
                                    buttons: Ext.Msg.OK,
                                    fn: function()
                                    {
                                        if (!Ext.isEmpty(Ext.getCmp('swWorkPlacePolkaRegWindowPolkaRegWorkPlacePanel'))) {
                                            Ext.getCmp('swWorkPlacePolkaRegWindowPolkaRegWorkPlacePanel').getGrid().getStore().reload();
                                        }

                                        if (!Ext.isEmpty(Ext.getCmp('swWorkPlaceCallCenterWindowPolkaRegWorkPlacePanel'))) {
                                            Ext.getCmp('swWorkPlaceCallCenterWindowPolkaRegWorkPlacePanel').getGrid().getStore().reload();
                                        }

                                        _this.hide();

                                    },
                                    icon: Ext.Msg.INFO,
                                    msg: lang['telefon_uspeshno_sohranen'],
                                    title: lang['soobschenie']
                                });
                            }
                            else
                            {
                                sw.swMsg.show(
                                    {
                                        buttons: Ext.Msg.OK,
                                        fn: function()
                                        {
                                            _this.hide();
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
            sw.Promed.swPhoneInfoWindow.superclass.show.apply(this, arguments);
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
            this.findById('PhoneInfoForm').getForm().reset();
            this.callback = Ext.emptyFn;
            this.onHide = Ext.emptyFn;

            if (arguments[0].Person_id)
                this.Person_id = arguments[0].Person_id;
            else
                this.Person_id = null;

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

            var form = this.findById('PhoneInfoForm');
            form.getForm().setValues(arguments[0]);

            var loadMask = new Ext.LoadMask(this.getEl(),{msg: LOAD_WAIT});
            loadMask.show();

            this.enableEdit(true);
            loadMask.hide();

            form.getForm().load({
                    params:
                    {
                        Person_id: _this.Person_id
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
                        _this.setTitle(langs('Телефон пациента') + ' ' + form.getForm().findField('Person_FIO').getValue());
                        form.getForm().findField('Phone_Site').disable();

                        // var phone_field = form.getForm().findField('Phone_Promed');
                        // if(phone_field.getValue()) phone_field.fireEvent('change', phone_field, phone_field.getValue());
                    },
                    url: '/?c=Person&m=getPersonPhoneInfo'
            });

        },
        initComponent: function()
        {
            var _this = this;
            // Форма с полями 
            this.PhoneInfoForm = new Ext.form.FormPanel(
            {
                autoHeight: true,
                bodyStyle: 'padding: 5px',
                border: false,
                buttonAlign: 'left',
                frame: true,
                id: 'PhoneInfoForm',
                labelAlign: 'right',
                labelWidth: 180,
                items:
                    [{
                        xtype: 'hidden',
                        name:'Person_id',
                        id: 'Person_id'
                    },{
                        xtype: 'hidden',
                        name:'Server_id',
                        id: 'Server_id'
                    },{
                        xtype: 'hidden',
                        name:'PersonPhone_id',
                        id: 'PersonPhone_id'
                    },{
                        xtype: 'hidden',
                        name:'Person_FIO',
                        id: 'Person_FIO'
                    },{
                        fieldLabel: lang['telefon_s_sayta'],
                        xtype: 'textfield',
                        anchor: '100%',
                        name: 'Phone_Site',
                        tabIndex: TABINDEX_LPEEW + 3
                    },{
                        fieldLabel: langs('Телефон в МИС') + ': +7',
                        labelSeparator: '',
                        xtype: 'swphonefield',
                        //xtype: 'textfield',
						// maxLength:(getRegionNick() != 'ekb')?undefined:11,
						// maskRe: (getRegionNick() != 'ekb')?/\d|-/: new RegExp('[0-9]'),
                        anchor: '100%',
                        name: 'Phone_Promed',
                        tabIndex: TABINDEX_LPEEW + 3
                    }],
                reader: new Ext.data.JsonReader(
                    {
                        success: function()
                        {
                            //
                        }
                    },
                    [
                        {name: 'Person_id'},
                        {name: 'Server_id'},
                        {name: 'PersonPhone_id'},
                        {name: 'Phone_Site'},
                        {name: 'Person_FIO'},
                        {name: 'Phone_Promed'}
                    ]),
                url: '/?c=Person&m=savePersonPhoneInfo'
            });
            Ext.apply(this,
                {
                    buttons:
                        [{
                            handler: function()
                            {
                                _this.doSave();
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
                            text: BTN_FRMCANCEL
                        }],
                    items: [this.PhoneInfoForm]
                });
            sw.Promed.swPhoneInfoWindow.superclass.initComponent.apply(this, arguments);
        }
    });