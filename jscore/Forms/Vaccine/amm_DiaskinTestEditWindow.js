/**
 * Created with JetBrains PhpStorm.
 * User: Shorev
 * Date: 21.07.14
 * Time: 16:30
 * To change this template use File | Settings | File Templates.
 */

sw.Promed.amm_DiaskinTestEditWindow = Ext.extend(sw.Promed.BaseForm, {
    id: 'amm_DiaskinTestEditWindow',
    title: "Исполнение Диаскинтест",
    border: false,
    width: 800,
    height: 500,
    maximizable: false,
    closeAction: 'hide',
    layout: 'border',
    codeRefresh: true,
    modal:true,
    objectName: 'amm_DiaskinTestEditWindow',
    objectSrc: '/jscore/Forms/Vaccine/amm_DiaskinTestEditWindow.js',
    onHide: Ext.emptyFn,
    initComponent: function (){
        var params = new Object();
        var form = this;
        this.formStore = new Ext.data.JsonStore({
            fields: ['Diaskintest_id', 'VacPresence_id', 'Dose', 'WayPlace_id', 'React_id', 'DatePurpose'
                , 'DateVac', 'DateReact', 'Lpu_id', 'MedPersonal_id', 'StatusType_id'
                , 'VacPresence_Seria', 'VacPresence_Period', 'VacPresence_Manufacturer'
                , 'Diaskintest_Ser'
                , 'Person_id', 'person_BirthDay'
                , 'Reaction30min','ExpressionType_id'
            ],
            url: '/?c=VaccineCtrl&m=loadDiaskinTestFormInfo',
            key: 'Diaskintest_id',
            root: 'data'
        });
        this.PersonInfoPanel  = new sw.Promed.PersonInfoPanel({
            titleCollapse: true,
            floatable: false,
            collapsible: true,
            collapsed: true,
            border: true,
            plugins: [ Ext.ux.PanelCollapsedTitle ],
            region: 'north'
        });
        this.setAllowBlank  = function(allowBlank) {
            this.dtEditForm.getForm().findField('MedStaffFact_id').allowBlank = allowBlank;
            this.dtEditForm.getForm().findField('MedStaffFact_id').isValid();
            this.dtEditForm.getForm().findField('DiaskinSeria_id').allowBlank = allowBlank;
            this.dtEditForm.getForm().findField('DiaskinSeria_id').isValid();
        };
        Ext.apply(this,{
            formparams: null,
            buttons: [
                {text: 'Сохранить',
                    iconCls: 'save16',
                    handler: function(b) {
                        var bForm = Ext.getCmp('diaskinTestEditForm');
                        if (!bForm.form.isValid()) {
                            sw.Promed.vac.utils.msgBoxNoValidForm();
                            b.setDisabled(false);
                            return false;
                        }
                        var bWin = Ext.getCmp('amm_DiaskinTestEditWindow');
                        var pars = new Object();
                        pars.diaskintest_vacdate = bForm.getForm().findField('vacDiaskinDateImpl').getValue().format('d.m.Y');
                        pars.diaskintest_reactdate = bForm.getForm().findField('vacDiaskinDateReact').getValue() ? bForm.getForm().findField('vacDiaskinDateReact').getValue().format('d.m.Y') : '';
                        pars.person_id = this.formparams.person_id;
                        pars.diaskintest_statuscode = 1;

                        var idx = bForm.form.findField('DiaskinSeria_id').getStore().findBy(function(rec) { return rec.get('VacPresence_id') == bForm.form.findField('DiaskinSeria_id').getValue(); });
                        var seriaRecord = bForm.form.findField('DiaskinSeria_id').getStore().getAt(idx);
                        if (typeof(seriaRecord) == 'object') {
                            pars.vacpresence_id = bForm.form.findField('DiaskinSeria_id').getValue();
                            pars.diaskintest_ser = seriaRecord.get('Seria');
                            pars.diaskintest_period = seriaRecord.get('Period');
                        } else {
                            pars.diaskintest_ser = bForm.form.findField('DiaskinSeria_id').getRawValue();
                        }
                        pars.fix_tub_id = this.formparams.fix_tub_id;
                        pars.vaccineplace_id = bForm.form.findField('VaccineWayPlace_id').getValue();
                        pars.diaskintest_dose = bForm.form.findField('DiaskinDose_Name').getValue();
                        pars.med_staff_fact_id = bForm.form.findField('MedStaffFact_id').getValue();
                        pars.lpu_id = bForm.getForm().findField('diaskin_implLpuCombo').getValue();
                        pars.mantureactiontype_id = bForm.getForm().findField('TypeReaction_id').getValue();
                        pars.diaskintest_isreaction = bForm.getForm().findField('Reaction30min').getValue();
                        pars.expressiontype_id = bForm.getForm().findField('ExpressionType_id').getValue();
                        pars.date_purpose = pars.diaskintest_vacdate;
                        var arrKeys = [];
                        arrKeys.push(pars.fix_tub_id);
                        Ext.Ajax.request({
                            url: '/?c=VaccineCtrl&m=saveDiaskintest',
                            method: 'POST',
                            params: pars,
                            success: function(response, opts) {
                                if (sw.Promed.vac.utils.msgBoxErrorBd(response) == 0) {
                                    Ext.getCmp(this.formparams.parent_id).fireEvent('success', this.formparams.source, {
                                        keys: arrKeys
                                    });
                                }
                                form.hide();
                            }.createDelegate(this),
                            failure: function(response, opts) {
                                sw.Promed.vac.utils.consoleLog('server-side failure with status code: ' + response.status);
                            }
                        });
                    }.createDelegate(this)

                },{
                    text: '-'
                },
                HelpButton(this),
                {handler: function() {
                    this.hide();
                }.createDelegate(this),
                    iconCls: 'close16',
                    onTabAction: function () {
                    }.createDelegate(this),
                    text: 'Закрыть'

                }],
            items: [
                this.PersonInfoPanel,
                this.dtEditForm = new Ext.form.FormPanel({
                    autoScroll: true,
                    region: 'center',
                    bodyBorder: false,
                    bodyStyle: 'padding: 5px 5px 0',
                    border: false,
                    frame: false,
                    id: 'diaskinTestEditForm',
                    name: 'dtEditForm',
                    labelAlign: 'right',
                    labelWidth: 100,
                    layout: 'form',
                    items: [
                        {
                            height:5,
                            border: false
                        },
                        {
                            id: 'dtform',
                            border: false,
                            layout: 'column',
                            defaults: {
                                columnWidth: 0.5,
                                bodyBorder: false,
                                labelWidth: 100,
                                anchor: '100%'
                            },
                            bodyStyle: 'padding: 5px',
                            items: [{

                                layout: 'form',
                                items: [{
                                    fieldLabel: 'Дата исполнения',
                                    allowBlank: false,
                                    xtype: 'swdatefield',
                                    format: 'd.m.Y',
                                    plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
                                    name: 'vacDiaskinDateImpl',
                                    id: 'vacDiaskinDateImpl'
                                    ,listeners: {
                                        'blur': function(dt) {
                                            if (sw.Promed.vac.utils.strToDate(dt.value) < new Date()) {
                                                this.setAllowBlank(true);
                                            } else {
                                                this.setAllowBlank(false);
                                            }
                                        }.createDelegate(this)
                                    }
                                }, {
                                    fieldLabel: 'Дата проверки',
                                    xtype: 'swdatefield',
                                    format: 'd.m.Y',
                                    plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
                                    name: 'vacDiaskinDateReact'
                                }, {
                                    autoLoad: false,
                                    fieldLabel: 'Доза введения',
                                    name: 'DiaskinDose_Name',
                                    width: 260,
                                    xtype: 'textfield'
                                }, {
                                    allowBlank: false,
                                    id: 'diaskin_implVaccineSeriaCombo',
                                    autoLoad: true,
                                    fieldLabel: 'Серия и срок годности',
                                    hiddenName: 'DiaskinSeria_id',
                                    width: 260,
                                    listWidth: 260,
                                    xtype: 'amm_VaccineSeriaCombo'
                                    ,listeners: {
                                        'select': function(combo, record, index)  {
                                            this.dtEditForm.form.findField('cityManufacturer').setValue(
                                                record.get('Manufacturer')
                                            );
                                        }.createDelegate(this)
                                    }
                                }, {
                                    fieldLabel: 'Изготовитель',
                                    name: 'cityManufacturer',
                                    width: 260,
                                    readOnly: true,
                                    xtype: 'textfield'
                                }, {
                                    allowBlank: false,
                                    id: 'diaskin_implVaccineWayCombo',
                                    autoLoad: true,
                                    fieldLabel: 'Способ и место введения',
                                    hiddenName: 'VaccineWayPlace_id',
                                    width: 260,
                                    listWidth: 400,
                                    xtype: 'amm_VacWayPlaceCombo'
                                }, {
                                    listWidth: 260,
                                    id: 'diaskin_implTypeReactionCombo',
                                    autoLoad: true,
                                    fieldLabel: 'Тип реакции',
                                    hiddenName: 'TypeReaction_id',
                                    width: 260,
                                    xtype: 'amm_TypeReactionCombo'
                                },{
                                    listWidth: 250,
                                    id: 'diaskin_ExpressionType',
                                    autoload: true,
                                    fieldLabel: 'Степень выраженности',
                                    hiddenName: 'ExpressionType_id',
                                    width: 260,
                                    xtype: 'amm_ExpressionTypeCombo'
                                }
                                ]

                            },{

                                layout: 'form',
                                items: [{

                                    id: 'diaskin_implLpuCombo',
                                    listWidth: 400,
                                    width: 260,
                                    xtype: 'amm_LpuListCombo',
                                    listeners: {
                                        'select': function(combo)  {
                                            Ext.getCmp('diaskin_implLpuBuildingCombo').reset();
                                            Ext.getCmp('diaskin_implLpuSectionCombo').reset();
                                            Ext.getCmp('diaskin_implMedPersonalCombo').reset();
                                            Ext.getCmp('dtform').find('hiddenName', 'LpuBuilding_id')[0].getStore().load({
                                                params: {Lpu_id: combo.getValue()}
                                            });
                                        }.createDelegate(this)
                                    }

                                }, {

                                    autoHeight: true,
                                    style: 'margin: 5px; padding: 5px;',
                                    title: 'Назначил врач:',
                                    xtype: 'fieldset',
                                    width: 400,
                                    items: [{

                                        id: 'diaskin_implLpuBuildingCombo',
                                        listWidth: 400,
                                        linkedElements: [
                                            'diaskin_implLpuSectionCombo'
                                        ],
                                        width: 260,
                                        xtype: 'swlpubuildingglobalcombo'
                                    }, {
                                        id: 'diaskin_implLpuSectionCombo',
                                        linkedElements: [
                                            'diaskin_implMedPersonalCombo'
                                        ],
                                        listWidth: 400,
                                        parentElementId: 'diaskin_implLpuBuildingCombo',
                                        width: 260,
                                        xtype: 'swlpusectionglobalcombo'
                                    }, {
                                        allowBlank: false,
                                        hiddenName: 'MedStaffFact_id',
                                        id: 'diaskin_implMedPersonalCombo',
                                        parentElementId: 'diaskin_implLpuSectionCombo',
                                        listWidth: 400,
                                        width: 260,
                                        emptyText: VAC_EMPTY_TEXT,
                                        xtype: 'swmedstafffactglobalcombo'
                                    }]

                                },{
                                    xtype: 'checkbox',
                                    name: 'Reaction30min',
                                    labelSeparator: '',
                                    checked: false,
                                    boxLabel: 'Реакция на прививку (ч/з 30 мин)'

                                }]
                            }
                            ]
                        }

                    ]
                })
            ]
        });
        sw.Promed.amm_DiaskinTestEditWindow.superclass.initComponent.apply(this, arguments);
    },

    show: function(params){

        sw.Promed.amm_DiaskinTestEditWindow.superclass.show.apply(this, arguments);
        this.formparams = params;

        var allowBlank = false;
        if ((params.add_new_mantu == 1)||
            (sw.Promed.vac.utils.strToDate(this.dtEditForm.form.findField('vacDiaskinDateImpl').value) <
                new Date()))
        {
            allowBlank = true;
        }
        this.setAllowBlank(allowBlank);
        this.formStore.load({
            params: {
                fix_tub_id: params.fix_tub_id
            },
            callback: function(){
                var formStoreCount = this.formStore.getCount() > 0;
                if(formStoreCount){
                    var formStoreRecord = this.formStore.getAt(0);
                    if (params.add_new_mantu != 1) {
                        this.formparams.vac_presence_id = formStoreRecord.get('VacPresence_id');
                        this.formparams.med_staff_fact_id = formStoreRecord.get('MedPersonal_id');
                        this.formparams.vac_doze = formStoreRecord.get('Dose');
                        this.formparams.reaction_type = formStoreRecord.get('React_id');
                        this.formparams.checkbox_reaction30min = formStoreRecord.get('Reaction30min');
                        this.formparams.vaccine_way_place_id = formStoreRecord.get('WayPlace_id');
                        this.formparams.date_react = formStoreRecord.get('DateReact');
                        this.formparams.lpu_id = formStoreRecord.get('Lpu_id');
                        this.formparams.vac_presence_seria = formStoreRecord.get('VacPresence_Seria');
                        this.formparams.vac_presence_period = formStoreRecord.get('VacPresence_Period');
                        this.formparams.vac_presence_manufacturer = formStoreRecord.get('VacPresence_Manufacturer');
                        this.formparams.vac_seria_txt = formStoreRecord.get('JournalMantu_Seria');
                        this.formparams.expressiontype_id = formStoreRecord.get('ExpressionType_id');
                    }
                    else {
                        delete params.fix_tub_id;
                        delete this.formparams.plan_tub_id;
                        delete this.formparams.fix_tub_id;
                    }
                    this.formparams.status_type_id = formStoreRecord.get('StatusType_id');
                    if (formStoreRecord.get('DatePurpose'))
                        this.formparams.date_purpose = formStoreRecord.get('DatePurpose');
                    this.formparams.date_impl = formStoreRecord.get('DateVac');
                    this.formparams.person_id = formStoreRecord.get('Person_id');
                    this.formparams.birthday = formStoreRecord.get('person_BirthDay');
                }
                if (params.add_new_mantu == 1) this.formparams.add_new_mantu = params.add_new_mantu;
                this.PersonInfoPanel.load({
                    callback: function() {
                        this.PersonInfoPanel.setPersonTitle();
                        var Person_deadDT = Ext.getCmp('amm_DiaskinTestEditWindow').PersonInfoPanel.getFieldValue('Person_deadDT');
                        if (Person_deadDT != undefined) {
                            Ext.getCmp('vacDiaskinDateImpl').setMaxValue (Person_deadDT);
                        }
                    }.createDelegate(this),
                    loadFromDB: true,
                    Person_id: this.formparams.person_id
                    ,Server_id: this.formparams.Server_id
                });
                this.dtEditForm.getForm().findField('DiaskinDose_Name').setValue(this.formparams.vac_doze);
                this.dtEditForm.getForm().findField('Reaction30min').setValue(this.formparams.checkbox_reaction30min);

                this.dtEditForm.getForm().findField('vacDiaskinDateReact').reset();
                this.dtEditForm.getForm().findField('vacDiaskinDateReact').setValue(this.formparams.date_react);

                this.dtEditForm.form.findField('diaskin_implLpuCombo').getStore().load({
                    callback: function() {
                        this.dtEditForm.form.findField('diaskin_implLpuCombo').setValue(getGlobalOptions().lpu_id);
                    }.createDelegate(this)
                });
                this.dtEditForm.form.findField('LpuBuilding_id').getStore().load({
                    params: {Lpu_id: getGlobalOptions().lpu_id}
                });
                this.dtEditForm.form.findField('LpuSection_id').getStore().load({
                    callback: function() {
                        this.dtEditForm.form.findField('MedStaffFact_id').getStore().load({
                            callback: function() {
                                if (this.formparams.status_type_id == 1) {
                                    this.dtEditForm.form.findField('MedStaffFact_id').setValue(this.formparams.med_staff_fact_id);
                                }
                                else{
                                    if (getGlobalOptions().medstafffact[0]) {
                                        this.dtEditForm.form.findField('MedStaffFact_id').setValue(getGlobalOptions().medstafffact[0]);
                                    }
                                }
                            }.createDelegate(this)
                        });
                    }.createDelegate(this)
                });

                var newDateVal;
                if (this.formparams.status_type_id == 0) {
                    if (params.add_new_mantu == 1) newDateVal = sw.Promed.vac.utils.yearAdd(this.formparams.date_purpose, 1)
                    else newDateVal = this.formparams.date_purpose;
                } else if (this.formparams.status_type_id == 1) {
                    if (params.add_new_mantu == 1) newDateVal = sw.Promed.vac.utils.yearAdd(this.formparams.date_impl, 1)
                    else newDateVal = this.formparams.date_impl;
                } else {
                    newDateVal = new Date;
                }
                this.dtEditForm.form.findField('vacDiaskinDateImpl').setValue(newDateVal);

                var expressionCombo = this.dtEditForm.form.findField('ExpressionType_id');
                this.dtEditForm.form.findField('ExpressionType_id').getStore().load({
                    callback: function(){
                        if ((this.formparams.expressiontype_id != undefined)&&(this.formparams.expressiontype_id != 0)) {
                            expressionCombo.setValue( this.formparams.expressiontype_id );
                        }
                    }.createDelegate(this)
                });

                var comboReact = this.dtEditForm.form.findField('TypeReaction_id');
                this.dtEditForm.form.findField('TypeReaction_id').getStore().load({
                    callback: function(){
                        if ((this.formparams.reaction_type != undefined)&&(this.formparams.reaction_type != 0)) {
                            comboReact.setValue( this.formparams.reaction_type );
                        }
                    }.createDelegate(this)
                });


                var comboVacWay = this.dtEditForm.form.findField('VaccineWayPlace_id');
                comboVacWay.reset();
                var parsVacWay = new Object();
                parsVacWay.birthday = this.formparams.birthday;
                if (this.formparams.date_purpose != undefined) {
                    parsVacWay.date_purpose = this.formparams.date_purpose;
                } else if (this.formparams.date_impl != undefined) {
                    parsVacWay.date_purpose = this.formparams.date_impl;
                } else {
                    parsVacWay.date_purpose = (new Date).format('d.m.Y');
                }
                parsVacWay.vaccine_id = this.formparams.vaccine_id;
                comboVacWay.getStore().load({
                    params: parsVacWay
                    ,callback: function(){
                        var val = '';
                        if ((comboVacWay.getStore().getCount() > 0)&&(this.formparams.status_type_id == 0 || this.formparams.status_type_id == undefined)){
                            val = comboVacWay.getStore().getAt(0).get('VaccineWayPlace_id');
                        } else if (this.formparams.status_type_id == 1){
                            val = this.formparams.vaccine_way_place_id;
                        }
                        comboVacWay.setValue(val);
                    }.createDelegate(this)
                });

                var comboVacSeria = this.dtEditForm.form.findField('DiaskinSeria_id');
                comboVacSeria.getStore().load({
                    params: this.formparams
                    ,callback: function(){
                        comboVacSeria.reset();
                        var val = '';
                        var notInList = 0;
                        if (comboVacSeria.getStore().getCount() > 0) {
                            if (this.formparams.status_type_id == 0 || this.formparams.status_type_id == undefined){
                                val = comboVacSeria.getStore().getAt(0).get('VacPresence_id');
                            } else if (this.formparams.status_type_id == 1){
                                val = this.formparams.vac_presence_id;
                            }
                            var indx = comboVacSeria.getStore().findBy(function(rec) { return rec.get('VacPresence_id') == val; });
                            if (indx != -1) {//-1 если не найдено
                                comboVacSeria.setValue(val);
                            } else {
                                notInList = 1;
                            }
                        } else {
                            notInList = 1;
                        }
                        if (notInList) {
                            var arr = [];
                            if (this.formparams.vac_presence_seria != undefined)
                                arr.push(this.formparams.vac_presence_seria);
                            if (this.formparams.vac_presence_period != undefined)
                                arr.push(this.formparams.vac_presence_period);
                            val = arr.join(' - ');
                            if (val == '') val = this.formparams.vac_seria_txt;
                            comboVacSeria.setValue(val);
                        }
                        if (this.formparams.vac_presence_manufacturer != undefined) {
                            this.dtEditForm.form.findField('cityManufacturer').setValue(this.formparams.vac_presence_manufacturer);

                        } else if ((val != undefined) && (Ext.getCmp('diaskin_implVaccineSeriaCombo').getStore().getAt(0).json != undefined) ){
                            this.dtEditForm.form.findField('cityManufacturer').setValue(Ext.getCmp('diaskin_implVaccineSeriaCombo').getStore().getAt(0).json.Manufacturer);
                        }
                    }.createDelegate(this)
                });
            }.createDelegate(this)
        });

    }
});