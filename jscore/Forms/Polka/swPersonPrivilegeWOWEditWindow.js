/**
 * PersonPrivilegeWOWEditWindow - окно Регистр ВОВ: Добавление / Редактирование"
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Polka
 * @access       public
 * @copyright    Copyright (c) 2013 Swan Ltd.
 * @author
 * @version      21.05.2013
 */

sw.Promed.swPersonPrivilegeWOWEditWindow = Ext.extend(sw.Promed.BaseForm, {
    action: null,
    buttonAlign: 'left',
    callback: Ext.emptyFn,
    closable: true,
    closeAction: 'hide',
    height: 280,
    id: 'PersonPrivilegeWOWEditWindow',
    layout: 'border',
    listeners:	{
        'hide':	function() {
            this.onHide();
        }
    },
    maximizable: true,
    modal: true,
    onHide: Ext.emptyFn,
    params: null,
    plain: true,
    resizable: true,
    width: 850,
    doSave: function(options) {
        // options @Object
        if ( this.action == 'view' ) {
            return false;
        }

        var win = this;
        var base_form = this.FormPanel.getForm();

        if ( !base_form.isValid() ) {
            sw.swMsg.show({
                buttons: Ext.Msg.OK,
                fn: function() {
                    this.FormPanel.getFirstInvalidEl().focus(false);
                }.createDelegate(this),
                icon: Ext.Msg.WARNING,
                msg: ERR_INVFIELDS_MSG,
                title: ERR_INVFIELDS_TIT
            });
            return false;
        }

        var params = {};

        win.getLoadMask("Подождите, идет сохранение...").show();
        base_form.submit({
            failure: function(result_form, action) {
                win.getLoadMask().hide();

                if ( action.result ) {
                    if ( action.result.Error_Msg ) {
                        sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
                    }
                    else {
                        sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_1]']);
                    }
                }
            },
            params: params,
            success: function(result_form, action) {
                win.getLoadMask().hide();

                if ( action.result ) {
                    if ( action.result.PersonPrivilegeWOW_id ) {
                        win.callback();
                        win.hide();
                    }
                    else {
                        if ( action.result.Error_Msg ) {
                            sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
                        }
                        else {
                            sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_3]']);
                        }
                    }
                }
                else {
                    sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_2]']);
                }

            }
        });
    },
    initComponent: function() {
        var win = this;

        this.FormPanel = new Ext.form.FormPanel({
            autoScroll: true,
            bodyBorder: false,
            bodyStyle: 'padding: 5px 5px 0',
            border: false,
            frame: false,
            id: 'PersonPrivilegeWOWEditForm',
            labelAlign: 'right',
            labelWidth: 250,
            reader: new Ext.data.JsonReader({
                success: function() {
                }
            }, [
                { name: 'Server_id' },
                { name: 'Person_id' },
                { name: 'PersonPrivilegeWOW_begDate' },
                { name: 'PersonPrivilegeWOW_id' },
                { name: 'PrivilegeTypeWOW_id' }
            ]),
            region: 'center',
            url: '/?c=PersonPrivilegeWOW&m=savePersonPrivilegeWOW',
            items: [
                {
                    name: 'PersonPrivilegeWOW_id',
                    value: '',
                    xtype: 'hidden'
                },
                {
                    name: 'Person_id',
                    value: 0,
                    xtype: 'hidden'
                },
                {
                    name: 'Server_id',
                    value: 0,
                    xtype: 'hidden'
                },
                {
                    allowBlank: false,
                    fieldLabel: lang['data_vklyucheniya_v_registr'],
                    name: 'PersonPrivilegeWOW_begDate',
                    plugins: [
                        new Ext.ux.InputTextMask('99.99.9999', false)
                    ],
                    tabIndex: TABINDEX_PRIVSF + 5,
                    width: 100,
                    xtype: 'swdatefield'
                },((!getRegionNick().inlist(['ufa','ekb','penza','astra']))?(
                {
                    allowBlank: (getRegionNick().inlist(['ufa','ekb','penza','astra'])),
                    valueField: 'PrivilegeTypeWOW_id',
                    hiddenName: 'PrivilegeTypeWOW_id',
                    fieldLabel: lang['vid_lgotyi'],
                    comboSubject: 'PrivilegeTypeWOW',
                    width: 550,
                    xtype: 'swcommonsprcombo'
                }):{xtype: 'hidden', name: 'PrivilegeTypeWOW_id', value: 8})
            ]
        });
        this.PersonInfo = new sw.Promed.PersonInfoPanel({
            button1OnHide: function() {
                if (this.action == 'view') {
                    this.buttons[this.buttons.length - 1].focus();
                } else {
                    this.FormPanel.getForm().findField('PrivilegeTypeWOW_id').focus(true);
                }
            }.createDelegate(this),
            button2Callback: function(callback_data) {
                this.FormPanel.getForm().findField('Server_id').setValue(callback_data.Server_id);
                this.PersonInfo.load({ Person_id: callback_data.Person_id, Server_id: callback_data.Server_id });
            }.createDelegate(this),
            button2OnHide: function() {
                this.PersonInfo.button1OnHide();
            }.createDelegate(this),
            button3OnHide: function() {
                this.PersonInfo.button1OnHide();
            }.createDelegate(this),
            button4OnHide: function() {
                this.PersonInfo.button1OnHide();
            }.createDelegate(this),
            button5OnHide: function() {
                this.PersonInfo.button1OnHide();
            }.createDelegate(this),
            collapsible: true,
            collapsed: false,
            floatable: false,
            id: 'PDEF_PersonInformationFrame',
            plugins: [ Ext.ux.PanelCollapsedTitle ],
            region: 'north',
            title: lang['zagruzka'],
            titleCollapse: true
        });

        Ext.apply(this, {
            buttons: [
                {
                    handler: function() {
                        this.doSave();
                    }.createDelegate(this),
                    iconCls: 'save16',
                    onShiftTabAction: function () {
                        var base_form = this.FormPanel.getForm();
                    }.createDelegate(this),
                    onTabAction: function () {
                        this.buttons[this.buttons.length - 1].focus(true);
                    }.createDelegate(this),
                    tabIndex: 12613,
                    text: BTN_FRMSAVE
                },
                '-',
                HelpButton(this, -1),
                {
                    handler: function() {
                        this.hide();
                    }.createDelegate(this),
                    iconCls: 'cancel16',
                    onShiftTabAction: function () {
                        // this.buttons[1].focus(true);
                        this.buttons[0].focus(true);
                    }.createDelegate(this),
                    onTabAction: function () {
                        if (this.action != 'view') {
                            this.FormPanel.getForm().findField('PrivilegeTypeWOW_id').focus(true);
                        } else {
                            this.buttons[1].focus(true);
                        }
                    }.createDelegate(this),
                    tabIndex: 12615,//todo
                    text: BTN_FRMCANCEL
                }
            ],
            items: [
                this.PersonInfo,
                this.FormPanel
            ],
            layout: 'border'
        });
        sw.Promed.swPersonPrivilegeWOWEditWindow.superclass.initComponent.apply(this, arguments);
    },
    show: function() {
        sw.Promed.swPersonPrivilegeWOWEditWindow.superclass.show.apply(this, arguments);
        this.restore();
        this.center();
        var base_form = this.FormPanel.getForm();
        base_form.reset();
        this.action = null;
        this.callback = Ext.emptyFn;
        this.onHide = Ext.emptyFn;
        this.PersonInfo.setTitle('...');
        this.wintitle = (getRegionNick().inlist(['ufa','ekb','penza','astra'])) ? langs('Регистр инвалидов, подлежащих ДВН') : langs('Регистр ВОВ');

        setCurrentDateTime({
            callback: Ext.emptyFn,
            dateField: base_form.findField('PersonPrivilegeWOW_begDate'),
            loadMask: false,
            setDate: true,
            setTime:false,
            setDateMaxValue: true,
            windowId: this.id
        });

        if ( !arguments[0] || !arguments[0].formParams ) {
            sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi']);
            return false;
        }
        base_form.setValues(arguments[0].formParams);

        this.PersonInfo.load({
            callback: function(params) {
                this.PersonInfo.setPersonTitle();
            }.createDelegate(this),
            Person_id: base_form.findField('Person_id').getValue()
            //Server_id: base_form.findField('Server_id').getValue()
        });
        if (arguments[0].action) {
            this.action = arguments[0].action;
        }
        if (arguments[0].callback) {
            this.callback = arguments[0].callback;
        }
        if (arguments[0].onHide) {
            this.onHide = arguments[0].onHide;
        }
        var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
        loadMask.show();
        switch (this.action) {
            case 'add':
                this.setTitle(this.wintitle + lang['_dobavlenie']);
                this.enableEdit(true);
                loadMask.hide();
                break;
            case 'view':
            case 'edit':
                var person_priv_wow_id = base_form.findField('PersonPrivilegeWOW_id').getValue();
                if (!person_priv_wow_id) {
                    loadMask.hide();
                    this.hide();
                    return false;
                }
                base_form.load({
                    failure: function() {
                        loadMask.hide();
                        sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_dannyih_formyi'], function() {
                            this.hide();
                        }.createDelegate(this));
                    }.createDelegate(this),
                    params: {
                        'PersonPrivilegeWOW_id': person_priv_wow_id
                    },
                    success: function() {
                        loadMask.hide();
                        if (this.action == 'edit') {
                            this.setTitle(this.wintitle + lang['_redaktirovanie']);
                            this.enableEdit(true);
                        } else {
                            this.setTitle(this.wintitle + lang['_prosmotr']);
                            this.enableEdit(false);
                        }
                        base_form.clearInvalid();
                        if (this.action == 'edit') {
                            base_form.findField('PrivilegeTypeWOW_id').focus(true, 250);
                        } else {
                            this.buttons[this.buttons.length - 1].focus();
                        }
                    }.createDelegate(this),
                    url: '/?c=PersonPrivilegeWOW&m=loadPersonPrivilegeWOWEditForm'
                });
                break;
            default:
                loadMask.hide();
                this.hide();
                break;
        }
    }
});