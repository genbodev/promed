/**
 * swCardioRiskCalcEditWindow - Сердечно-сосудистый риск
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Person
 * @access       public
 * @comment      Префикс для id компонентов PCRCEF (PersonCardioRiskCalcEditForm)
 */

sw.Promed.swCardioRiskCalcEditWindow = Ext.extend(sw.Promed.BaseForm, {
    action: null,
    autoHeight: true,
    buttonAlign: 'left',
    callback: Ext.emptyFn,
    closable: true,
    closeAction: 'hide',
    collapsible: false,
    doSave: function () {
        if (this.formStatus == 'save') {
            return false;
        }

        this.formStatus = 'save';

        var form = this.FormPanel;
        var base_form = form.getForm();

        if (!base_form.isValid()) {
            sw.swMsg.show({
                buttons: Ext.Msg.OK,
                fn: function () {
                    this.formStatus = 'edit';
                    form.getFirstInvalidEl().focus(false);
                }.createDelegate(this),
                icon: Ext.Msg.WARNING,
                msg: ERR_INVFIELDS_MSG,
                title: ERR_INVFIELDS_TIT
            });
            return false;
        }

        var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
        loadMask.show();


        base_form.submit({
            params: {
                CardioRiskCalc_Percent: base_form.findField('CardioRiskCalc_Percent').getValue(),
                RiskType_id: base_form.findField('RiskType_id').getValue()
            },
            success: function (result_form, action) {
                this.formStatus = 'edit';
                loadMask.hide();

                if (action.result) {

                    if (action.result.Error_Msg) {
                        sw.swMsg.alert(langs('Ошибка'), action.result.Error_Msg);
                    }
                    else {
                        this.callback();
                        this.hide()
                    }

                }
                else {
                    sw.swMsg.alert(langs('Ошибка'), langs('При сохранении произошли ошибки [Тип ошибки: 2]'));
                }
            }.createDelegate(this)
        });

    },
    draggable: true,
    enableEdit: function(enable) {
        var base_form = this.FormPanel.getForm();
        var form_fields = new Array(
            'CardioRiskCalc_SistolPress',
            'CardioRiskCalc_setDT',
            'CardioRiskCalc_IsSmoke',
            'CardioRiskCalc_Chol'
        );
        var i = 0;

        for ( i = 0; i < form_fields.length; i++ ) {
            if ( enable ) {
                base_form.findField(form_fields[i]).enable();
            }
            else {
                base_form.findField(form_fields[i]).disable();
            }
        }

        if ( enable ) {
            this.buttons[0].show();
        }
        else {
            this.buttons[0].hide();
        }
    },
    formMode: 'remote',
    formStatus: 'edit',
    id: 'CardioRiskCalcEditWindow',
    initComponent: function() {
        var me = this;
        this.FormPanel = new Ext.form.FormPanel({
            autoHeight: true,
            bodyBorder: false,
            bodyStyle: 'padding: 5px 5px 0',
            border: false,
            frame: false,
            id: 'CardioRiskCalcEditForm',
            labelAlign: 'right',
            labelWidth: 250,
            reader: new Ext.data.JsonReader({
                success: Ext.emptyFn
            },  [
                { name: 'accessType' },
                { name: 'CardioRiskCalc_id' },
                { name: 'CardioRiskCalc_setDT' },
                { name: 'CardioRiskCalc_SistolPress' },
                { name: 'CardioRiskCalc_Chol' },
                { name: 'CardioRiskCalc_IsSmoke' },
                { name: 'Person_id' },
                { name: 'CardioRiskCalc_Percent' },
                { name: 'RiskType_id' }
            ]),
            url: '/?c=CardioRiskCalc&m=saveCardioRiskCalc',

            items: [{
                name: 'accessType',
                value: '',
                xtype: 'hidden'
            }, {
                name: 'CardioRiskCalc_id',
                value: 0,
                xtype: 'hidden'
            }, {
                name: 'Person_id',
                value: 0,
                xtype: 'hidden'
            }, {
                name: 'Server_id',
                value: -1,
                xtype: 'hidden'
            }, {
                allowBlank: false,
                fieldLabel: langs('Дата измерения'),
                format: 'd.m.Y',
                name: 'CardioRiskCalc_setDT',
                maxValue: Date.parseDate(getGlobalOptions().date, 'd.m.Y'),
                plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
                tabIndex: TABINDEX_PCRCEF + 1,
                width: 100,
                value: new Date(),
                xtype: 'swdatefield'
            }, {
                allowNegative: false,
                allowDecimals: false,
                allowBlank: false,
                fieldLabel: langs('Систолическое давление (мм рт.ст.)'),
                name: 'CardioRiskCalc_SistolPress',
                tabIndex: TABINDEX_PCRCEF + 2,
                minValue: 60,
                maxValue: 260,
                width: 60,
                xtype: 'numberfield',
                listeners: {
                    change: function(){
                        me.calcPercent();
                    }
                }
            }, {
                allowNegative: false,
                allowBlank: false,
                fieldLabel: langs('Общий холестерин (ммоль/л)'),
                name: 'CardioRiskCalc_Chol',
                tabIndex: TABINDEX_PCRCEF + 3,
                width: 60,
                minValue: 0,
                maxValue: 10,
                xtype: 'numberfield',
                listeners: {
                    change: function(){
                        me.calcPercent();
                    }
                }
            }, {
                comboSubject: 'YesNo',
                allowBlank: false,
                fieldLabel: langs('Курение'),
                hiddenName: 'CardioRiskCalc_IsSmoke',
                name: 'CardioRiskCalc_IsSmoke',
                tabIndex: TABINDEX_PCRCEF + 4,
                width: 100,
                xtype: 'swcommonsprcombo',
                listeners: {
                    change: function(){
                        me.calcPercent();
                    }
                }
            }, {
                fieldLabel: langs('Процент'),
                name: 'CardioRiskCalc_Percent',
                tabIndex: TABINDEX_PCRCEF + 5,
                width: 100,
                disabled: true,
                xtype: 'numberfield'
            }, {
                fieldLabel: langs('Тип риска'),
                hiddenName: 'RiskType_id',
                name: 'RiskType_id',
                comboSubject: 'RiskType',
                tabIndex: TABINDEX_PCRCEF + 6,
                width: 100,
                disabled:true,
                xtype: 'swcommonsprcombo'
            }]
        });

        Ext.apply(this, {
            buttons: [{
                handler: function() {
                    this.doSave();
                }.createDelegate(this),
                iconCls: 'save16',
                id: me.id + '_saveBtn',
                onTabAction: function () {
                    this.buttons[this.buttons.length - 1].focus(true);
                }.createDelegate(this),
                tabIndex: TABINDEX_PCRCEF + 4,
                text: BTN_FRMSAVE
            }, {
                text: '-'
            },
                HelpButton(this, -1),
                {
                    handler: function() {
                        this.hide();
                    }.createDelegate(this),
                    iconCls: 'cancel16',
                    tabIndex: TABINDEX_PCRCEF + 5,
                    text: BTN_FRMCANCEL
                }],
            items: [
                this.FormPanel
            ],
            layout: 'form'
        });

        sw.Promed.swCardioRiskCalcEditWindow.superclass.initComponent.apply(this, arguments);
    },
    keys: [{
        alt: true,
        fn: function(inp, e) {
            var current_window = Ext.getCmp('CardioRiskCalcEditWindow');

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
        stopEvent: true
    }],
    layout: 'form',
    listeners: {
        'hide': function(win) {
            win.onHide();
        }
    },
    maximizable: false,
    modal: true,
    onHide: Ext.emptyFn,
    plain: true,
    resizable: false,
    calcPercent: function(){
        var base_form = this.FormPanel.getForm(),
            SistolPressField = base_form.findField('CardioRiskCalc_SistolPress'),
            CholField = base_form.findField('CardioRiskCalc_Chol'),
            IsSmokeField = base_form.findField('CardioRiskCalc_IsSmoke'),
            Person_id = base_form.findField('Person_id').getValue(),
            PercentField = base_form.findField('CardioRiskCalc_Percent'),
            RiskTypeField = base_form.findField('RiskType_id'),
            saveBtn = Ext.getCmp(this.id + '_saveBtn');

        if(Ext.isEmpty(SistolPressField.getValue()) || Ext.isEmpty(CholField.getValue()) || Ext.isEmpty(IsSmokeField.getValue())){
            return false;
        }


        saveBtn.disable();
        Ext6.Ajax.request({
            url: '/?c=CardioRiskCalc&m=calcCargioRiskPercent',
            params: {
                Person_id: Person_id,
                CardioRiskCalc_SistolPress: SistolPressField.getValue(),
                CardioRiskCalc_Chol: CholField.getValue(),
                CardioRiskCalc_IsSmoke: IsSmokeField.getValue()
            },
            callback: function(options, success, response) {
                var responseData = Ext6.JSON.decode(response.responseText);
                if(responseData.ScoreValues_Values){
                    PercentField.setValue(responseData.ScoreValues_Values);
                    switch(true){
                        case responseData.ScoreValues_Values == 0:
                            RiskTypeField.setValue(1);
                            break;
                        case responseData.ScoreValues_Values > 0 && responseData.ScoreValues_Values < 5:
                            RiskTypeField.setValue(2);
                            break;
                        case responseData.ScoreValues_Values > 4 && responseData.ScoreValues_Values < 10:
                            RiskTypeField.setValue(3);
                            break;
                        case responseData.ScoreValues_Values > 9:
                            RiskTypeField.setValue(4);
                            break;
                    }
                }
                saveBtn.enable();
            }
        })

    },
    show: function() {
        sw.Promed.swCardioRiskCalcEditWindow.superclass.show.apply(this, arguments);

        this.center();

        var base_form = this.FormPanel.getForm();
        base_form.reset();

        this.action = null;
        this.callback = Ext.emptyFn;
        this.formMode = 'remote';
        this.formStatus = 'edit';
        this.onHide = Ext.emptyFn;

        if ( !arguments[0] || !arguments[0].formParams ) {
            sw.swMsg.alert(langs('Сообщение'), langs('Неверные параметры'), function() { this.hide(); }.createDelegate(this) );
            return false;
        }

        base_form.setValues(arguments[0].formParams);

        if ( arguments[0].action ) {
            this.action = arguments[0].action;
        }

        if ( arguments[0].callback ) {
            this.callback = arguments[0].callback;
        }

        if ( arguments[0].formMode && typeof arguments[0].formMode == 'string' && arguments[0].formMode.inlist([ 'local', 'remote' ]) ) {
            this.formMode = arguments[0].formMode;
        }

        if ( arguments[0].onHide ) {
            this.onHide = arguments[0].onHide;
        }


        var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
        loadMask.show();

        var index;
        var record;

        switch ( this.action ) {
            case 'add':
                this.setTitle(WND_PERSON_PCRCEFADD);
                this.enableEdit(true);

                loadMask.hide();

                base_form.clearInvalid();
                base_form.isValid();
                break;

            case 'edit':
            case 'view':
                if ( this.formMode == 'local' ) {
                    if ( this.action == 'edit' ) {
                        this.setTitle(WND_PERSON_PCRCEFEDIT);
                        this.enableEdit(true);
                    }
                    else {
                        this.setTitle(WND_PERSON_PCRCEFVIEW);
                        this.enableEdit(false);
                    }

                    loadMask.hide();

                    base_form.clearInvalid();

                }
                else {
                    var person_cardio_risk_calc_id = base_form.findField('CardioRiskCalc_id').getValue();

                    if ( !person_cardio_risk_calc_id ) {
                        loadMask.hide();
                        this.hide();
                        return false;
                    }

                    base_form.load({
                        failure: function() {
                            loadMask.hide();
                            sw.swMsg.alert(langs('Ошибка'), langs('Ошибка при загрузке данных формы'), function() { this.hide(); }.createDelegate(this) );
                        }.createDelegate(this),
                        params: {
                            'CardioRiskCalc_id': person_cardio_risk_calc_id
                        },
                        success: function() {
                            if ( base_form.findField('accessType').getValue() == 'view' ) {
                                this.action = 'view';
                            }

                            if ( this.action == 'edit' ) {
                                this.setTitle(WND_PERSON_PCRCEFEDIT);
                                this.enableEdit(true);
                            }
                            else {
                                this.setTitle(WND_PERSON_PCRCEFVIEW);
                                this.enableEdit(false);
                            }

                            loadMask.hide();

                            base_form.clearInvalid();

                        }.createDelegate(this),
                        url: '/?c=CardioRiskCalc&m=loadCardioRiskCalcEditForm'
                    });
                }
                break;

            default:
                loadMask.hide();
                this.hide();
                break;
        }
    },
    width: 700
});