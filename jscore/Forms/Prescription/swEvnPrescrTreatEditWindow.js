/**
 * swEvnPrescrTreatEditWindow - окно добавления/редактирования назначения c типом Лекарственное лечение.
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Prescription
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltd.
 * @version      0.001-15.03.2012
 * @comment      Префикс для id компонентов EPRTREF (EvnPrescrTreatEditForm)
 */
/*NO PARSE JSON*/

sw.Promed.swEvnPrescrTreatEditWindow = Ext.extend(sw.Promed.BaseForm, {
    codeRefresh: true,
    objectName: 'swEvnPrescrTreatEditWindow',
    objectSrc: '/jscore/Forms/Prescription/swEvnPrescrTreatEditWindow.js',

    action: null,
    autoHeight: true,
    buttonAlign: 'left',
    callback: Ext.emptyFn,
    closable: true,
    closeAction: 'hide',
    collapsible: false,
    doSave: function(options) {
        if ( this.formStatus == 'save' ) {
            return false;
        }

        if ( typeof options != 'object' ) {
            options = {};
        }

        this.formStatus = 'save';

        var base_form = this.FormPanel.getForm();
        var thas = this;
        if ( !base_form.isValid() ) {
            sw.swMsg.show({
                buttons: Ext.Msg.OK,
                fn: function() {
                    thas.formStatus = 'edit';
                    thas.FormPanel.getFirstInvalidEl().focus(true);
                },
                icon: Ext.Msg.WARNING,
                msg: ERR_INVFIELDS_MSG,
                title: ERR_INVFIELDS_TIT
            });
            return false;
        }

        var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT_SAVE });
        loadMask.show();

        var params = {};

        params.parentEvnClass_SysNick = this.parentEvnClass_SysNick;
        if(options.signature) {
            params.signature = 1;
        } else {
            params.signature = 0;
        }

        if (base_form.findField('EvnPrescrTreat_setDate').disable()) {
            params.EvnPrescrTreat_setDate = base_form.findField('EvnPrescrTreat_setDate').getRawValue();
        }

        var DrugListData = this.TreatDrugListPanel.getDrugListData();
        //log(DrugListData);
        if (DrugListData.length==0) {
            this.formStatus = 'edit';
            sw.swMsg.alert(lang['oshibka'], lang['v_naznachenii_doljnyi_byit_zapolnenyi_polya_hotya_byi_odnogo_medikamenta']);
            return false;
        }
        DrugListData = Ext.util.JSON.encode(DrugListData);
        base_form.findField('DrugListData').setValue(DrugListData);

        base_form.submit({
            failure: function(result_form, action) {
                thas.formStatus = 'edit';
                loadMask.hide();

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
                thas.formStatus = 'edit';
                loadMask.hide();

                if ( action.result ) {
                    var data = base_form.getValues();
                    data.EvnPrescrTreat_id = action.result.EvnPrescrTreat_id;
                    thas.callback(data);
                    thas.hide();
                } else {
                    sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_2]']);
                }
            }
        });
        return true;
    },
    draggable: true,
    enableEdit: function(enable) {
        var base_form = this.FormPanel.getForm();
        var formFields = [
            ,'EvnPrescrTreat_PrescrCount'
            ,'EvnPrescrTreat_IsCito'
            ,'EvnPrescrTreat_Descr'
        ];
        for (var i = 0; i < formFields.length; i++ ) {
            if ( enable ) {
                base_form.findField(formFields[i]).enable();
            }
            else {
                base_form.findField(formFields[i]).disable();
            }
        }

        this.TreatDrugListPanel.setEnableEdit(enable);

        if ( enable ) {
            this.buttons[0].show();
        }
        else {
            this.buttons[0].hide();
        }
    },
    formStatus: 'edit',
    id: 'EvnPrescrTreatEditWindow',
    initComponent: function() {
        var thas = this;

        this.itemBodyStyle = 'padding: 5px 5px 0';
        this.labelAlign = 'right';
        this.labelWidth = 130;

        this.TreatDrugListPanel = new sw.Promed.TreatDrugListPanel({
            win: this,
            form_id: 'EvnPrescrTreatEditForm',
            objectDrug: 'EvnPrescrTreatDrug',
            disabledAddDrug: true,
            itemBodyStyle: this.itemBodyStyle ,
            labelAlign: this.labelAlign,
            labelWidth: this.labelWidth,
            getRegimeFormParams: function() {
                var base_form = thas.FormPanel.getForm();
                return {
                    setDate: base_form.findField('EvnPrescrTreat_setDate').getRawValue(),
                    CountDay: base_form.findField('EvnPrescrTreat_PrescrCount').getValue(),
                    Duration: 1,
                    DurationType_id: 1,
                    DurationType_Nick: lang['dn'],
                    ContReception: 1,
                    DurationType_recid: 1,
                    Interval: 0,
                    DurationType_intid: 1
                };
            }
        });

        this.FormPanel = new Ext.form.FormPanel({
            autoHeight: true,
            bodyBorder: false,
            border: false,
            frame: false,
            id: 'EvnPrescrTreatEditForm',
            reader: new Ext.data.JsonReader({
                success: Ext.emptyFn
            },  [
                { name: 'accessType' },
                { name: 'DrugListData' },

                { name: 'EvnPrescrTreat_id' },
                { name: 'EvnPrescrTreat_pid' },
                { name: 'EvnCourse_id' },
                { name: 'EvnPrescrTreat_setDate' },
                { name: 'EvnPrescrTreat_PrescrCount' },
                { name: 'PrescriptionStatusType_id' },
                { name: 'EvnPrescrTreat_IsCito' },
                { name: 'EvnPrescrTreat_Descr' },
                { name: 'PersonEvn_id' },
                { name: 'Server_id' }
            ]),
            region: 'center',
            url: '/?c=EvnPrescr&m=saveEvnPrescrTreat',
            items: [{
                name: 'accessType', // Режим доступа
                value: '',
                xtype: 'hidden'
            }, {
                name: 'DrugListData',
                value: '',
                xtype: 'hidden'
            }, {
                name: 'EvnPrescrTreat_id',
                value: null,
                xtype: 'hidden'
            }, {
                name: 'EvnPrescrTreat_pid', // Идентификатор события
                value: null,
                xtype: 'hidden'
            }, {
                name: 'EvnCourse_id', // Идентификатор курса
                value: null,
                xtype: 'hidden'
            }, {
                name: 'PersonEvn_id', // Идентификатор состояния человека
                value: null,
                xtype: 'hidden'
            }, {
                name: 'Server_id', // Идентификатор сервера
                value: null,
                xtype: 'hidden'
            }, {
                name: 'PrescriptionStatusType_id', // Идентификатор (Рабочее,Подписанное,Отмененное)
                value: null,
                xtype: 'hidden'
            },
            this.TreatDrugListPanel,
            {
                autoHeight: true,
                bodyBorder: false,
                bodyStyle: this.itemBodyStyle,
                border: false,
                frame: false,
                labelAlign: this.labelAlign,
                labelWidth: this.labelWidth,
                layout: 'form',
                items: [{
                    fieldLabel: lang['data'],
                    format: 'd.m.Y',
                    allowBlank: false,
                    disabled: true,
                    name: 'EvnPrescrTreat_setDate',
                    plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
                    selectOnFocus: true,
                    width: 100,
                    listeners: {
                        change: function() {
                            thas.TreatDrugListPanel.reCountAll();
                        }
                    },
                    xtype: 'swdatefield'
                }, {
                    allowDecimals: false,
                    allowNegative: false,
                    fieldLabel: lang['priemov_v_sutki'],
                    value: 1,
                    minValue: 1,
                    style: 'text-align: right;',
                    name: 'EvnPrescrTreat_PrescrCount',
                    width: 100,
                    listeners: {
                        change: function() {
                            thas.TreatDrugListPanel.reCountAll();
                        }
                    },
                    xtype: 'numberfield'
                }, {
                    boxLabel: 'Cito',
                    checked: false,
                    fieldLabel: '',
                    labelSeparator: '',
                    name: 'EvnPrescrTreat_IsCito',
                    xtype: 'checkbox'
                }, {
                    fieldLabel: lang['kommentariy'],
                    height: 70,
                    name: 'EvnPrescrTreat_Descr',
                    width: 370,
                    xtype: 'textarea'
                }]
            }]
        });

        Ext.apply(this, {
            buttons: [{
                handler: function() {
                    thas.doSave();
                },
                iconCls: 'save16',
                text: BTN_FRMSAVE
            }, {
                hidden: true,
                handler: function() {
                    thas.doSave({signature: true});
                },
                iconCls: 'signature16',
                text: BTN_FRMSIGN
            }, {
                text: '-'
            },
                //HelpButton(this, -1),
                {
                    handler: function() {
                        thas.hide();
                    },
                    iconCls: 'cancel16',
                    onTabAction: function () {
                        //thas.FormPanel.getForm().findField('MethodInputDrug_id').focus(true, 250);
                    },
                    text: BTN_FRMCANCEL
                }],
            items: [
                this.FormPanel
            ],
            layout: 'form'
        });

        sw.Promed.swEvnPrescrTreatEditWindow.superclass.initComponent.apply(this, arguments);
    },
    keys: [{
        alt: true,
        fn: function(inp, e) {
            var current_window = Ext.getCmp('EvnPrescrTreatEditWindow');

            switch ( e.getKey() ) {
                case Ext.EventObject.C:
                    current_window.doSave();
                    break;

                case Ext.EventObject.J:
                    current_window.hide();
                    break;
            }
        },
        key: [
            Ext.EventObject.C,
            Ext.EventObject.J
        ],
        scope: this,
        stopEvent: false
    }],
    layout: 'form',
    listeners: {
        hide: function(win) {
            win.onHide();
        }
    },
    loadMask: null,
    maximizable: false,
    maximized: false,
    modal: true,
    onHide: Ext.emptyFn,
    plain: true,
    resizable: false,
    show: function() {
        sw.Promed.swEvnPrescrTreatEditWindow.superclass.show.apply(this, arguments);

        var thas = this;

        var base_form = this.FormPanel.getForm();
        base_form.reset();
        this.TreatDrugListPanel.reset();

        this.parentEvnClass_SysNick = null;
        this.action = null;
        this.callback = Ext.emptyFn;
        this.formStatus = 'edit';
        this.onHide = Ext.emptyFn;

        if ( !arguments[0] || !arguments[0].formParams ) {
            sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() { thas.hide(); } );
            return false;
        }

        base_form.setValues(arguments[0].formParams);
        var formParams = arguments[0].formParams;

        if ( arguments[0].action && typeof arguments[0].action == 'string' ) {
            this.action = arguments[0].action;
        }

        if ( arguments[0].parentEvnClass_SysNick && typeof arguments[0].parentEvnClass_SysNick == 'string' ) {
            this.parentEvnClass_SysNick = arguments[0].parentEvnClass_SysNick;
        }
        this.LpuSection_id = arguments[0].LpuSection_id || null;

        if ( arguments[0].callback && typeof arguments[0].callback == 'function' ) {
            this.callback = arguments[0].callback;
        }

        if ( arguments[0].onHide && typeof arguments[0].onHide == 'function' ) {
            this.onHide = arguments[0].onHide;
        }

        this.getLoadMask(LOAD_WAIT).show();

        switch ( this.action ) {
            case 'add':
                this.getLoadMask().hide();
                base_form.clearInvalid();
                this.setTitle(lang['naznachenie_lekarstvennogo_lecheniya_dobavlenie']);
                this.enableEdit(true);
                //чтобы выбирать с остатков отделения
                this.TreatDrugListPanel.parentEvnClass_SysNick = this.parentEvnClass_SysNick;
                this.TreatDrugListPanel.LpuSection_id = this.LpuSection_id;
                this.TreatDrugListPanel.onLoadForm();
                if (base_form.findField('MethodInputDrug_id0')) {
                    base_form.findField('MethodInputDrug_id0').focus(true, 250);
                }
                break;
            case 'copy':
            case 'edit':
            case 'view':
                base_form.load({
                    failure: function() {
                        thas.getLoadMask().hide();
                        sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_dannyih_formyi'], function() { thas.hide(); } );
                    },
                    params: {
                        EvnPrescrTreat_id: base_form.findField('EvnPrescrTreat_id').getValue(),
                        parentEvnClass_SysNick: this.parentEvnClass_SysNick
                    },
                    success: function() {
                        thas.getLoadMask().hide();
                        base_form.clearInvalid();
                        if ( thas.action != 'copy' && base_form.findField('accessType').getValue() == 'view' ) {
                            thas.action = 'view';
                        }
                        if ( thas.action == 'edit' ) {
                            thas.setTitle(lang['naznachenie_lekarstvennogo_lecheniya_redaktirovanie']);
                            thas.enableEdit(true);
                        } else if ( thas.action == 'copy' ) {
                            thas.setTitle(lang['naznachenie_lekarstvennogo_lecheniya_dobavlenie']);
                            base_form.findField('EvnPrescrTreat_id').setValue(null);
                            base_form.findField('EvnPrescrTreat_setDate').setValue(formParams.EvnPrescrTreat_setDate||null);
                            var DrugListData = base_form.findField('DrugListData').getValue();
                            try {
                                DrugListData = Ext.util.JSON.decode(DrugListData);
                            } catch (e) {
                                thas.getLoadMask().hide();
                                thas.hide();
                                return false;
                            }
                            if (!Ext.isArray(DrugListData)) {
                                thas.getLoadMask().hide();
                                thas.hide();
                                return false;
                            }
                            for (var i=0; i<DrugListData.length; i++) {
                                DrugListData[i]['id'] = null;
                                DrugListData[i]['status'] = 'new';
                            }
                            DrugListData = Ext.util.JSON.encode(DrugListData);
                            base_form.findField('DrugListData').setValue(DrugListData);
                            thas.enableEdit(true);
                        } else {
                            thas.setTitle(lang['naznachenie_lekarstvennogo_lecheniya_prosmotr']);
                            thas.enableEdit(false);
                        }

                        //чтобы выбирать с остатков отделения
                        thas.TreatDrugListPanel.parentEvnClass_SysNick = thas.parentEvnClass_SysNick;
                        thas.TreatDrugListPanel.LpuSection_id = thas.LpuSection_id;
                        thas.TreatDrugListPanel.onLoadForm(base_form.findField('DrugListData').getValue());

                        if ( thas.action == 'view' ) {
                            thas.buttons[thas.buttons.length - 1].focus();
                        } else {
                            if ( base_form.findField('MethodInputDrug_id0') ) {
                                base_form.findField('MethodInputDrug_id0').focus(true, 250);
                            }
                        }
                    },
                    url: '/?c=EvnPrescr&m=loadEvnPrescrTreatEditForm'
                });
                break;

            default:
                this.getLoadMask().hide();
                this.hide();
                break;
        }

        this.center();
        return true;
    },
    width: 550
});