/**
 * amm_MantuPurposeForm - окно ввода информации о манту
 *
 * @copyright    Copyright (c) 2012 
 * @author       
 * @version      02.07.2012
 * @comment      
 */

sw.Promed.amm_MantuPurposeForm = Ext.extend(sw.Promed.BaseForm, {
    title: "Назначение Манту",
    id: 'mantuForm',
    border: false,
    width: 900,
    height: 500,
    //  maximized: true,
    maximizable: false,
    closeAction: 'hide',
    //  layout:'fit',
    layout: 'border',
    codeRefresh: true,
    autoScroll: true,
    modal: true,
    planTmpId: null,
    //  vacComboParams: null,
    objectName: 'amm_MantuPurposeForm',
    objectSrc: '/jscore/Forms/Vaccine/amm_MantuPurposeForm.js',
    onHide: Ext.emptyFn,
    initComponent: function() {
        var params = new Object();
        var form = this;
        //объект для контроля дат формы:
        this.validateMantuPurposeDate = sw.Promed.vac.utils.getValidateObj({
            formId: 'vacMantuEditForm',
            fieldName: 'vacMantuDateAssign'
        });


        /*
         * хранилище для доп сведений
         */
        this.formStore = new Ext.data.JsonStore({
            fields: ['PlanTuberkulin_id', 'MedPers_id', 'Person_id', 'person_BirthDay'],
            url: '/?c=VaccineCtrl&m=loadMantuFormInfo',
            key: 'PlanTuberkulin_id',
            root: 'data'
        });

        this.PersonInfoPanel = new sw.Promed.PersonInfoPanel({
            titleCollapse: true,
            floatable: false,
            collapsible: true,
            bodyStyle: 'padding: 0px',
            collapsed: true,
            plugins: [Ext.ux.PanelCollapsedTitle],
            region: 'north'
        });

        this.checkFormType = {
            autoHeight: true,
            style: 'margin: 0,0,5,0; padding: 0px;',
            title: 'Режим сохранения:',
            xtype: 'fieldset',
            labelWidth: 1,
            items: [{
                    xtype: 'checkbox',
                    tabIndex: TABINDEX_MANTUPURPFRM + 10,
                    labelSeparator: '',
                    name: 'vacMantuStatus',
                    boxLabel: 'Исполнить минуя назначение'
                            , listeners: {
                        'render': function(field) {
                            this.setFormObj(field.getValue());
                        }.createDelegate(this),
                        'check': function(field, newValue) {
                            alert('check!!!');
                            this.setFormObj(newValue);
                        }.createDelegate(this)
                    }
                }]
        };

//    this.mantuObj = function(obj){
//      return {
//              itemsMedStaff: [{
//                  
////                id: 'mantu_LpuCombo',
//                listWidth: 600,
//                tabIndex: TABINDEX_VACIMPFRM + 11,
//                width: 260,
////                      xtype: 'swlpucombo'
//                xtype: 'amm_LpuListCombo',
//                listeners: {
//                  'select': function(combo)  {
//                    Ext.getCmp('mantu_LpuBuildingCombo').reset();
//                    Ext.getCmp('mantu_LpuSectionCombo').reset();
//                    Ext.getCmp('mantu_MedPersonalCombo').reset();
//                    var vacMantuForm = Ext.getCmp('vacMantuEditForm');
//                    vacMantuForm.form.findField('LpuBuilding_id').getStore().load({
//                      params: {Lpu_id: combo.getValue()}
//                    });
//                  }.createDelegate(this)
//                }
//
//              }, {
//
//                autoHeight: true,
//                style: 'margin: 5px; padding: 5px;',
//                title: 'Назначил врач:',
//                xtype: 'fieldset',
//                items: [{
//
//                  id: 'mantu_LpuBuildingCombo',
//                  //lastQuery: '',
//                  listWidth: 600,
//                  linkedElements: [
//                  'mantu_LpuSectionCombo'
//                  ],
//                  tabIndex: TABINDEX_VACPRPFRM + 21,
//                  width: 260,
//                  xtype: 'swlpubuildingglobalcombo'
//                }, {
//                  id: 'mantu_LpuSectionCombo',
//                  linkedElements: [
//                  'mantu_MedPersonalCombo'
//                  //                  ,'EPLSIF_MedPersonalMidCombo'
//                  ],
//                  listWidth: 600,
//                  parentElementId: 'mantu_LpuBuildingCombo',
//                  tabIndex: TABINDEX_VACPRPFRM + 22,
//                  width: 260,
//                  xtype: 'swlpusectionglobalcombo'
//                }, {
//                  allowBlank: false,
//                  hiddenName: 'MedStaffFact_id',
//                  id: 'mantu_MedPersonalCombo',
//                  parentElementId: 'mantu_LpuSectionCombo',
//                  listWidth: 600,
//                  tabIndex: TABINDEX_VACPRPFRM + 23,
//                  width: 260,
//                  emptyText: VAC_EMPTY_TEXT,
//                  xtype: 'swmedstafffactglobalcombo'
//                }]
//
//              }],
//        
//        initForm: function(pars){
//          
//        }
//      }
//    }();

        this.prpObj = function() {

            return {
                initForm: function(pars) {
                    var prpForm = Ext.getCmp('prpForm');
//          prpForm.find('name','vacMantuDateAssign')[0].setValue(new Date);
                    prpForm.find('name', 'vacMantuDateAssign')[0].setValue(pars.date_purpose);

                    var comboLpuMedServise = Ext.getCmp('mantu_LpuListComboServiceVac');
                    comboLpuMedServise.reset();

                    var comboLpu = prpForm.find('id', 'mantu_LpuCombo')[0]
                    var comboLpuMedServise = Ext.getCmp('mantu_LpuListComboServiceVac');
                    comboLpu.getStore().load({
                        callback: function() {
                            comboLpu.setValue(getGlobalOptions().lpu_id);
                            //****************
                            comboLpuMedServise.getStore().load(
                                    {
                                        callback: function() {
                                            comboLpuMedServise.setValue(comboLpu.getValue());
                                            //(getGlobalOptions().lpu);
                                            if (comboLpuMedServise.value == comboLpuMedServise.lastSelectionText) {
                                                comboLpuMedServise.setValue(null);
                                            }
                                        }
                                    })
                            //****************
                        }
                    });
                    /*
                     var comboLpuMedServise = Ext.getCmp('mantu_LpuListComboServiceVac');
                     comboLpuMedServise.reset();      
                     comboLpuMedServise.getStore().load(
                     {
                     callback: function() {
                     comboLpuMedServise.setValue (getGlobalOptions().lpu);
                     if (comboLpuMedServise.value == comboLpuMedServise.lastSelectionText) {
                     comboLpuMedServise.setValue (null);
                     }
                     }
                     } 
                     );
                     */
                    var combobuilding = Ext.getCmp('mantu_buildingComboServiceVac');
                    combobuilding.reset();
                    combobuilding.getStore().load();


                    var comboMedService = Ext.getCmp('mantu_ComboMedServiceVac');
                    comboMedService.reset();

                    //***************

                    var comboMedStaff = prpForm.find('hiddenName', 'MedStaffFact_id')[0];
                    prpForm.find('hiddenName', 'LpuBuilding_id')[0].getStore().load({
//            params: {Lpu_id: comboLpu.getValue()}
                        params: {Lpu_id: getGlobalOptions().lpu_id}
                    });
                    prpForm.find('hiddenName', 'LpuSection_id')[0].getStore().load({
                        callback: function() {
                            comboMedStaff.getStore().load({
                                callback: function() {
                                    comboMedStaff.setValue(pars.medPersId);

                                    combobuilding.setValue(Ext.getCmp('mantu_LpuBuildingCombo').getValue());
                                    comboMedService.getStore().load({
                                        params: {
                                            LpuBuilding_id: combobuilding.getValue()
                                        }
                                    }
                                    )
                                }
                            });
                        }
                    });
                },
                form: {
                    id: 'prpForm',
                    border: false,
                    layout: 'column',
                    labelWidth: 120,
                    defaults: {
                        //xtype: 'form',
                        columnWidth: 0.5,
                        bodyBorder: false,
                        anchor: '100%'
                    },
                    bodyStyle: 'padding: 5px',
                    //height: 100,
                    //autohight: true,
                    items: [{
                            layout: 'form',
//               labelWidth: 300,
                            items: [{
//                fieldLabel: 'Дата проведения',
                                    layout: 'form',
                                    labelWidth: 120,
                                    width: 400,
                                    border: false,
                                    items: [{
                                            fieldLabel: 'Дата проведения',
                                            tabIndex: TABINDEX_MANTUPURPFRM + 11,
                                            allowBlank: false,
                                            xtype: 'swdatefield',
                                            format: 'd.m.Y',
                                            plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
                                            name: 'vacMantuDateAssign',
                                            id: 'vacMantuDateAssign'
//                ,listeners: {
//                  'change': function(field, newValue, oldValue) {
//                    var combo = Ext.getCmp('mantu_VaccineListCombo');
//                    combo.vaccineParams.date_mantu = newValue.format('d.m.Y');
//                    combo.reset();
//                    //Ext.getCmp('VaccineListCombo').store.load({params: {VaccineType_id:'3', BirthDay:'03.05.2011'}});
//                    combo.store.load({
//                      params: combo.vaccineParams
//                      });
//                  }
//                }
//              } // !!!
//          ] // !!!
//                 }]
                                        },
                                        {
                                            height: 20,
                                            border: false
                                        },
                                        {
//              layout: 'form', ///!!!
//              items: [{ ///!!!

                                            id: 'mantu_LpuCombo',
                                            listWidth: 400,
                                            tabIndex: TABINDEX_MANTUPURPFRM + 12,
                                            width: 260,
//                      xtype: 'swlpucombo'
                                            xtype: 'amm_LpuListCombo',
                                            listeners: {
                                                'select': function(combo) {
                                                    Ext.getCmp('mantu_LpuBuildingCombo').reset();
                                                    Ext.getCmp('mantu_LpuSectionCombo').reset();
                                                    Ext.getCmp('mantu_MedPersonalCombo').reset();
                                                    var vacMantuForm = Ext.getCmp('vacMantuEditForm');
                                                    vacMantuForm.form.findField('LpuBuilding_id').getStore().load({
                                                        params: {Lpu_id: combo.getValue()}
                                                    });
                                                }.createDelegate(this)
                                            }
                                        }]
                                }
                                , {
                                    autoHeight: true,
                                    style: 'margin: 5px; padding: 5px;',
                                    title: 'Назначил врач:',
                                    xtype: 'fieldset',
                                    labelWidth: 110,
//                name: 'assignedBy',
                                    items: [{
                                            id: 'mantu_LpuBuildingCombo',
                                            //lastQuery: '',
                                            listWidth: 400,
                                            linkedElements: [
                                                'mantu_LpuSectionCombo'
                                            ],
                                            tabIndex: TABINDEX_MANTUPURPFRM + 13,
                                            width: 260,
                                            xtype: 'swlpubuildingglobalcombo'
                                        }, {
                                            id: 'mantu_LpuSectionCombo',
                                            linkedElements: [
                                                'mantu_MedPersonalCombo'
                                                        //                  ,'EPLSIF_MedPersonalMidCombo'
                                            ],
                                            listWidth: 400,
                                            parentElementId: 'mantu_LpuBuildingCombo',
                                            tabIndex: TABINDEX_MANTUPURPFRM + 14,
                                            width: 260,
                                            xtype: 'swlpusectionglobalcombo'
                                        }, {
                                            allowBlank: false,
                                            hiddenName: 'MedStaffFact_id',
                                            id: 'mantu_MedPersonalCombo',
                                            parentElementId: 'mantu_LpuSectionCombo',
                                            listWidth: 400,
                                            tabIndex: TABINDEX_MANTUPURPFRM + 15,
                                            width: 260,
                                            emptyText: VAC_EMPTY_TEXT,
                                            xtype: 'swmedstafffactglobalcombo'
                                        }]

                                }//,  !!!
                            ]}, // !!!

                        {
                            layout: 'form', ///!!!
                            items: [
                                {
                                    height: 72,
                                    border: false
//                    cls: 'tg-label'
                                },
                                {///!!!
                                    autoHeight: true,
                                    style: 'margin: 5px; padding: 5px;',
                                    title: 'Направляется:',
                                    xtype: 'fieldset',
                                    labelWidth: 110,
                                    items: [{
                                            id: 'mantu_LpuListComboServiceVac',
                                            //lastQuery: '',
                                            listWidth: 600,
                                            fieldLabel: 'ЛПУ',
                                            labelWidth: 500,
                                            tabIndex: TABINDEX_MANTUPURPFRM + 16,
                                            width: 260,
                                            xtype: 'amm_LpuListComboServiceVac'
                                                    ,
                                            listeners: {
                                                'select': function(combo, record, index) {
                                                    Ext.getCmp('mantu_buildingComboServiceVac').getStore().load({
                                                        params: {
                                                            Lpu_id: combo.getValue()
                                                        }
                                                        ,
                                                        callback: function() {
                                                            Ext.getCmp('mantu_buildingComboServiceVac').reset();
                                                            Ext.getCmp('mantu_ComboMedServiceVac').reset();
                                                        }
                                                    })
                                                }.createDelegate(this)
                                            }
                                        },
                                        {
                                            id: 'mantu_buildingComboServiceVac',
                                            fieldLabel: 'Подразделение',
                                            listWidth: 600,
                                            tabIndex: TABINDEX_MANTUPURPFRM + 17,
                                            width: 260,
                                            xtype: 'amm_BuildingComboServiceVac',
                                            listeners: {
                                                'select': function(combo, record, index) {
                                                    Ext.getCmp('mantu_ComboMedServiceVac').getStore().load({
                                                        params: {
                                                            LpuBuilding_id: combo.getValue()
                                                        }
                                                        ,
                                                        callback: function() {
                                                            Ext.getCmp('mantu_ComboMedServiceVac').reset();
                                                        }
                                                    })
                                                }.createDelegate(this)
                                            }
                                        },
                                        {
                                            allowBlank: false,
                                            fieldLabel: 'Служба',
                                            id: 'mantu_ComboMedServiceVac',
                                            listWidth: 600,
                                            tabIndex: TABINDEX_MANTUPURPFRM + 18,
                                            width: 260,
                                            emptyText: VAC_EMPTY_TEXT,
                                            xtype: 'amm_ComboMedServiceVac'
                                        }

                                    ]
                                }

                            ]
                        }]

                }
            }
        }();

//    this.implForm = {
        this.implObj = function() {
            return {
                initForm: function(pars) {
                    var implForm = Ext.getCmp('implForm');
                    implForm.find('name', 'vacMantuDateImpl')[0].setValue(new Date);
                    implForm.find('name', 'vacMantuDateReact')[0].setValue(new Date);

                    implForm.find('id', 'mantu_implLpuCombo')[0].getStore().load({
                        callback: function() {
                            implForm.find('id', 'mantu_implLpuCombo')[0].setValue(getGlobalOptions().lpu_id);
                        }
                    });

//          this.vacEditForm.form.findField('LpuBuilding_id').getStore().load({
                    implForm.find('hiddenName', 'LpuBuilding_id')[0].getStore().load({
                        params: {Lpu_id: getGlobalOptions().lpu_id}
                    });
                    /*
                     var comboLpu = vacPurpForm.form.findField('mantu_LpuListComboServiceVac');
                     comboLpu.reset();      
                     comboLpu.getStore().load({
                     callback: function() {
                     comboLpu.setValue (getGlobalOptions().lpu);
                     if (comboLpu.value == comboLpu.lastSelectionText) {
                     comboLpu.setValue (null);
                     }
                     }
                     } );
                     
                     var combobuilding = vacPurpForm.form.findField('mantu_buildingComboServiceVac');
                     combobuilding.reset();      
                     combobuilding.getStore().load();
                     
                     
                     var comboMedService = vacPurpForm.form.findField('mantu_ComboMedServiceVac');
                     comboMedService.reset();  
                     */
                    var comboMedStaff = implForm.find('hiddenName', 'MedStaffFact_id')[0];
//          implForm.find('hiddenName', 'LpuBuilding_id')[0].getStore().load();
                    implForm.find('hiddenName', 'LpuSection_id')[0].getStore().load({
                        callback: function() {
                            comboMedStaff.getStore().load({
                                callback: function() {
                                    comboMedStaff.setValue(pars.medPersId);
                                }
                            });
                        }
                    });

                    implForm.find('hiddenName', 'TypeReaction_id')[0].getStore().load();
//          implForm.find('name', 'TypeReaction_name')[0].getStore().load();

                    var comboVacWay = implForm.find('hiddenName', 'VaccineWayPlace_id')[0];
                    comboVacWay.reset();
                    comboVacWay.getStore().load({
                        params: pars //this.formParams
                                //          ,callback: function(){
                                //            comboVacWay.setValue(comboVacWay.getStore().getAt(0).get('VaccineWayPlace_id'));
                                //          }
                    });

                    var comboVacSeria = implForm.find('hiddenName', 'VaccineSeria_id')[0];
                    comboVacSeria.getStore().load({
                        params: pars //this.formParams
                                //          ,callback: function(){
                                //            comboVacSeria.setValue(comboVacSeria.getStore().getAt(0).get('VacPresence_id'));
                                //          }
                    });

                },
                form: {
                    id: 'implForm',
                    border: false,
                    layout: 'column',
                    defaults: {
                        columnWidth: 0.5,
                        bodyBorder: false,
                        labelWidth: 100,
                        anchor: '100%'
                    },
                    bodyStyle: 'padding: 5px',
                    //autohight: true,
                    items: [{
                            layout: 'form',
                            items: [{
                                    fieldLabel: 'Дата исполнения',
                                    tabIndex: TABINDEX_MANTUPURPFRM + 20,
                                    allowBlank: false,
                                    xtype: 'swdatefield',
                                    format: 'd.m.Y',
                                    plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
                                    name: 'vacMantuDateImpl'
                                }, {
                                    fieldLabel: 'Дата проверки',
                                    tabIndex: TABINDEX_MANTUPURPFRM + 21,
                                    allowBlank: false,
                                    xtype: 'swdatefield',
                                    format: 'd.m.Y',
                                    plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
                                    name: 'vacMantuDateReact'
                                }, {
                                    autoLoad: false,
                                    fieldLabel: 'Доза введения',
                                    name: 'VaccineDose_Name',
                                    width: 278,
                                    tabIndex: TABINDEX_MANTUPURPFRM + 22,
                                    xtype: 'textfield'
                                }, {
                                    allowBlank: false,
                                    id: 'mantu_implVaccineSeriaCombo',
                                    autoLoad: true,
                                    fieldLabel: 'Серия и срок годности',
                                    tabIndex: TABINDEX_MANTUPURPFRM + 23,
                                    hiddenName: 'VaccineSeria_id',
                                    width: 260,
                                    listWidth: 260,
                                    xtype: 'amm_VaccineSeriaCombo'
                                }, {
                                    fieldLabel: 'Изготовитель',
                                    tabIndex: TABINDEX_MANTUPURPFRM + 24,
                                    name: 'cityManufacturer',
                                    width: 278,
                                    readOnly: true,
                                    xtype: 'textfield'
                                }, {
//                allowBlank: false,
                                    id: 'mantu_implVaccineWayCombo',
                                    autoLoad: true,
                                    fieldLabel: 'Способ и место введения',
                                    hiddenName: 'VaccineWayPlace_id',
                                    tabIndex: TABINDEX_MANTUPURPFRM + 25,
                                    width: 260,
                                    xtype: 'amm_VacWayPlaceCombo'
                                }, {
                                    allowBlank: false,
                                    listWidth: 260,
                                    id: 'mantu_implTypeReactionCombo',
                                    autoLoad: true,
                                    fieldLabel: 'Тип реакции',
                                    tabIndex: TABINDEX_MANTUPURPFRM + 26,
                                    hiddenName: 'TypeReaction_id',
//                name: 'TypeReaction_name',
                                    width: 260,
                                    xtype: 'amm_TypeReactionCombo'
                                }]

                        }, {
                            layout: 'form',
                            items: [{
                                    id: 'mantu_implLpuCombo',
                                    listWidth: 400,
                                    tabIndex: TABINDEX_MANTUPURPFRM + 27,
                                    width: 260,
//                      xtype: 'swlpucombo'
                                    xtype: 'amm_LpuListCombo',
                                    listeners: {
                                        'select': function(combo) {
                                            Ext.getCmp('mantu_implLpuBuildingCombo').reset();
                                            Ext.getCmp('mantu_implLpuSectionCombo').reset();
                                            Ext.getCmp('mantu_implMedPersonalCombo').reset();
//                    var vacMantuForm = Ext.getCmp('vacMantuEditForm');
//                    var vacMantuForm = Ext.getCmp('implForm').find('hiddenName', 'MedStaffFact_id')[0];
//                    vacMantuForm.form.findField('LpuBuilding_id').getStore().load({
                                            Ext.getCmp('implForm').find('hiddenName', 'LpuBuilding_id')[0].getStore().load({
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
                                            id: 'mantu_implLpuBuildingCombo',
                                            //lastQuery: '',
                                            listWidth: 400,
                                            linkedElements: [
                                                'mantu_implLpuSectionCombo'
                                            ],
                                            tabIndex: TABINDEX_MANTUPURPFRM + 28,
                                            width: 260,
                                            xtype: 'swlpubuildingglobalcombo'
                                        }, {
                                            id: 'mantu_implLpuSectionCombo',
                                            linkedElements: [
                                                'mantu_implMedPersonalCombo'
                                                        //                  ,'EPLSIF_MedPersonalMidCombo'
                                            ],
                                            listWidth: 400,
                                            parentElementId: 'mantu_implLpuBuildingCombo',
                                            tabIndex: TABINDEX_MANTUPURPFRM + 29,
                                            width: 260,
                                            xtype: 'swlpusectionglobalcombo'
                                        }, {
                                            allowBlank: false,
                                            hiddenName: 'MedStaffFact_id',
                                            id: 'mantu_implMedPersonalCombo',
                                            parentElementId: 'mantu_implLpuSectionCombo',
                                            listWidth: 400,
                                            tabIndex: TABINDEX_MANTUPURPFRM + 30,
                                            width: 260,
                                            emptyText: VAC_EMPTY_TEXT,
                                            xtype: 'swmedstafffactglobalcombo'
                                        }]

                                }, {
                                    fieldLabel: 'Описание реакции',
                                    tabIndex: TABINDEX_MANTUPURPFRM + 31,
                                    xtype: 'textarea',
                                    name: 'vacMantuReactDesc',
                                    grow: true,
                                    growMax: 100,
                                    growMin: 60,
                                    width: 260
                                }]
                        }]
                }
            }
        }();//  };

        Ext.apply(this, {
            formParams: null,
            _formObj: 'PRP_RORM', //по-умолчанию - тип "Назначение"
            setFormObj: function(checkboxStatus) {
                if (checkboxStatus) {
                    this._formObj = 'IMPL_RORM';
                    Ext.getCmp('implForm').show();
                    if (Ext.getCmp('implForm').el)
                        Ext.getCmp('implForm').el.dom.style.display = '';
                    Ext.getCmp('prpForm').hide();
                    if (Ext.getCmp('prpForm').el)
                        Ext.getCmp('prpForm').el.dom.style.display = 'none';
                } else {
                    this._formObj = 'PRP_RORM';
                    Ext.getCmp('implForm').hide();
                    if (Ext.getCmp('implForm').el)
                        Ext.getCmp('implForm').el.dom.style.display = 'none';
                    Ext.getCmp('prpForm').show();
                    if (Ext.getCmp('prpForm').el)
                        Ext.getCmp('prpForm').el.dom.style.display = '';
                }
            }.createDelegate(this),
            getFormObj: function() {
                if (this._formObj != 'IMPL_RORM') {
                    return this.prpObj.form;
                } else {
                    return this.implObj.form;
                }
            }.createDelegate(this)
                    , buttons: [
                {
                    text: 'Перейти к исполнению',
                    tabIndex: TABINDEX_MANTUPURPFRM + 39,
                    handler: function() {
                        sw.Promed.vac.utils.consoleLog('Перейти к исполнению...');
                        var Params =  this.formParams;
                        if (Ext.getCmp('vacMantuDateAssign').isValid()) {
                            Params.date_purpose = Ext.getCmp('vacMantuDateAssign').getValue().format('d.m.Y');
                        }
                        sw.Promed.vac.utils.callVacWindow({
                            record: Params, //this.formParams,
                            type1: 'btnForm',
                            type2: 'btnGoToImplMantu'
                        }, this);
                    }.createDelegate(this)
                },
//      HelpButton(this),        
                {
                    text: 'Сохранить',
                    iconCls: 'save16',
                    id: 'mantu_ButtonSave',
                    tabIndex: TABINDEX_MANTUPURPFRM + 40,
                    handler: function(b) {
                        b.setDisabled(true);//деактивируем кнопку (исключен повторных нажатий)
                        //return false;
                        var vacMantuForm = Ext.getCmp('vacMantuEditForm');
                        if (!vacMantuForm.form.isValid()) {
                            sw.Promed.vac.utils.msgBoxNoValidForm();
                            b.setDisabled(false);
                            return false;
                        }

                        var pars = new Object();
                        pars.lpu_id = Ext.getCmp('mantu_LpuCombo').getValue();
                        pars.plan_tub_id = this.formParams.plan_tub_id;
                        pars.person_id = this.formParams.person_id;
                        pars.status_type_id = 0;//(vacMantuForm.form.findField('vacMantuStatus').getValue() ? 1 : 0);
                        pars.date_purpose = vacMantuForm.form.findField('vacMantuDateAssign').getValue().format('d.m.Y');
                        pars.med_staff_fact_id = vacMantuForm.form.findField('MedStaffFact_id').getValue();
                        pars.medService_id = Ext.getCmp('mantu_ComboMedServiceVac').getValue();

                        var arrKeys = [];
                        arrKeys.push(pars.plan_tub_id);

                        Ext.Ajax.request({
                            url: '/?c=VaccineCtrl&m=saveMantu',
                            method: 'POST',
                            params: pars,
                            success: function(response, opts) {
                                sw.Promed.vac.utils.consoleLog(response);

                                if (sw.Promed.vac.utils.msgBoxErrorBd(response) == 0) {
//								alert(this.formParams.parent_id);
//								alert(this.formParams.source);
//              Ext.getCmp(this.formParams.parent_id).fireEvent('success', 'amm_MantuPurposeForm', {
                                    Ext.getCmp(this.formParams.parent_id).fireEvent('success', 'TubPlan', {
                                        //                keys: Ext.getCmp('gridSimilarRecords').store.keyList
                                        keys: arrKeys
                                    });
                                }
                                form.hide();
                            }.createDelegate(this)
                        });
                    }.createDelegate(this)
                }, {
                    text: '-'
                }, {
                    handler: function() {
                        this.hide();
                    }.createDelegate(this),
                    iconCls: 'close16',
                    tabIndex: TABINDEX_MANTUPURPFRM + 41,
                    onTabAction: function() {
                        Ext.getCmp('vacMantuEditForm').form.findField('vacMantuDateAssign').focus();
                    }.createDelegate(this),
                    text: '<u>З</u>акрыть'
                }],
            items: [
                this.PersonInfoPanel,
                new Ext.form.FormPanel({
                    autoScroll: true,
                    bodyBorder: false,
                    bodyStyle: 'padding: 5px',
                    border: false,
                    frame: false,
                    id: 'vacMantuEditForm',
                    region: 'center',
                    labelAlign: 'right',
                    autohight: true,
                    //height: 100,
                    labelWidth: 100,
                    layout: 'form',
                    items: [
//          this.PersonInfoPanel,
                        {
                            height: 5,
                            border: false
                        }
//          ,this.checkFormType
                        , {
                            autoHeight: true,
                            id: 'fieldsetForm',
                            style: 'margin: 0,0,5,0; padding: 0px;',
                            title: '',
                            xtype: 'fieldset',
                            labelWidth: 1
                                    , items: [
                                this.prpObj.form
//              ,this.implObj.form
                            ]
                        }
                    ]
                })
            ]
        });
        sw.Promed.amm_MantuPurposeForm.superclass.initComponent.apply(this, arguments);
    },
//  openVacMantuEditWindow: function(action) {
//    var current_window = this;
//    var params = new Object();
//    var vacMantu_grid = current_window.findById('LTVW_PersonPrivilegeGrid');
//    
//  },

    /*
     * Ф-ция сборки параметров (TODO!!!)
     */
//  buildParams: function(params){
////          var vacPurpForm = Ext.getCmp('vacPurpEditForm');
////          var comboVacList = Ext.getCmp('purp_VaccineListCombo');
//          params.vaccine_way_place_id = this.form.findField('VaccineWayPlace_id').getValue();
//          
//          var i = this.form.findField('VaccineDoze_id').getStore().find('VaccineDose_id', this.form.findField('VaccineDoze_id').getValue());
//          var recDoze = this.form.findField('VaccineDoze_id').getStore().getAt(i);
//          if (typeof(recDoze) == 'object') {
//            params.vac_doze = recDoze.get('VaccineDose_Name');
//          }
//          
//          
//    return params;
//  },

    show: function(record) {
        sw.Promed.amm_MantuPurposeForm.superclass.show.apply(this, arguments);
        var vacMantuForm = Ext.getCmp('vacMantuEditForm');
        Ext.getCmp('mantu_ButtonSave').setDisabled(false);
        vacMantuForm.getForm().reset();
        sw.Promed.vac.utils.consoleLog('show(amm_MantuPurposeForm - record):');
        sw.Promed.vac.utils.consoleLog(record);
        this.formParams = record;

//    Ext.getCmp('test').items.add(this.testObject);
//    Ext.getCmp('vacMantuEditForm').add(Ext.getCmp('mantuForm').testObject);

//    Ext.getCmp('fieldsetForm').add(this.prpForm);
//    Ext.getCmp('fieldsetForm').doLayout();

        this.formStore.load({
            params: {
                plan_tub_id: record.plan_tub_id,
                user_id: getGlobalOptions().pmuser_id
            },
            callback: function() {
                var formStoreRecord = this.formStore.getAt(0);
                //проверка на повторную попытку назначения прививки:
                sw.Promed.vac.utils.consoleLog('formStoreRecord:');
                sw.Promed.vac.utils.consoleLog(formStoreRecord);
                sw.Promed.vac.utils.consoleLog('PlanTuberkulin_id=' + formStoreRecord.get('PlanTuberkulin_id'));
                if (formStoreRecord.get('PlanTuberkulin_id') == undefined) {
                    Ext.Msg.alert('Внимание', 'Назначение по выбранной прививке уже было выполнено!');
                    this.hide();
                    return false;
                }
//        var combo = Ext.getCmp('mantu_VaccineListCombo');
//        combo.vaccineParams = record;
                sw.Promed.vac.utils.consoleLog('record:');
                sw.Promed.vac.utils.consoleLog(record);

                this.formParams.medPersId = formStoreRecord.get('MedPers_id');
                this.formParams.person_id = formStoreRecord.get('Person_id');
                this.formParams.birthday = formStoreRecord.get('person_BirthDay');
//        this.implObj.initForm(this.formParams);
                this.prpObj.initForm(this.formParams);

//        vacMantuForm.form.findField('vacMantuDate1').setValue(new Date);
//        vacMantuForm.form.findField('vacMantuDate2').setValue(new Date);
//        vacMantuForm.form.findField('vacMantuType').setValue(record.vac_info);
                //params.Person_id = arguments[0].person_id;

                //контроль диапазона дат:
                this.validateMantuPurposeDate.init(function(o) {
//					var dateRangeBegin = sw.Promed.vac.utils.strToDate(o.birthday);
//					var dateRangeEnd = sw.Promed.vac.utils.strToDate(o.birthday);
//					dateRangeBegin.setFullYear(dateRangeBegin.getFullYear() + o.vacAgeBegin);
//					dateRangeEnd.setFullYear(dateRangeEnd.getFullYear() + o.vacAgeEnd);
                    var resObj = {};
                    if (o.birthday != undefined)
                        resObj.personBirthday = o.birthday;
//					if (o.vacAgeBegin != undefined) resObj.dateRangeBegin = dateRangeBegin;
//					if (o.vacAgeEnd != undefined) resObj.dateRangeEnd = dateRangeEnd;
                    return resObj;
                }(this.formParams));
                this.validateMantuPurposeDate.getMinDate();
                this.validateMantuPurposeDate.getMaxDate();

                this.PersonInfoPanel.load({
                    callback: function() {
                        this.PersonInfoPanel.setPersonTitle();
                        this.PersonInfoPanel.setPersonTitle();
                        var Person_deadDT = Ext.getCmp('mantuForm').PersonInfoPanel.getFieldValue('Person_deadDT');
                        if (Person_deadDT != undefined) {
//                  alert('Person_deadDT = ' + Person_deadDT);
                            Ext.getCmp('vacMantuDateAssign').setMaxValue(Person_deadDT);
                        }
                    }.createDelegate(this),
                    loadFromDB: true,
                    Person_id: this.formParams.person_id //record.person_id
                            , Server_id: this.formParams.Server_id
                });

                Ext.getCmp('vacMantuEditForm').form.findField('vacMantuDateAssign').focus(true, 100);

                //**************
                /*
                 var comboLpu = Ext.getCmp('mantu_LpuListComboServiceVac');
                 comboLpu.reset();      
                 comboLpu.getStore().load(
                 {
                 callback: function() {
                 comboLpu.setValue (getGlobalOptions().lpu);
                 if (comboLpu.value == comboLpu.lastSelectionText) {
                 comboLpu.setValue (null);
                 }
                 }
                 } 
                 );
                 
                 var combobuilding =  Ext.getCmp('mantu_buildingComboServiceVac');
                 combobuilding.reset();      
                 combobuilding.getStore().load();
                 
                 
                 var comboMedService =  Ext.getCmp('mantu_ComboMedServiceVac');
                 comboMedService.reset(); 
                 
                 */

                //***********
//                combobuilding.setValue (Ext.getCmp('mantu_LpuBuildingCombo').getValue());
//          combobuilding.setValue (Ext.getCmp('prpForm').find('hiddenName', 'LpuBuilding_id')[0].value); 

//        vacPurpForm.form.findField('LpuBuilding_id').getStore().load();
//          
//          Ext.getCmp('LpuBuilding_id').getStore().load();

//                 if (combobuilding.value == combobuilding.lastSelectionText) {
//                    combobuilding.reset();
//                }
//                
//                               comboMedService.getStore().load ({
//                                             params:{
//                                                 LpuBuilding_id: combobuilding.getValue()
//                                             }
//                                           }) 
//                //**********



                //**************

//        combo.reset();
//        combo.store.load({
//          params: record,
//          callback: function(){
//            var comboVac = vacMantuForm.form.findField('Vaccine_id');
//            consoleLog('comboVac:');
//            consoleLog(comboVac.getStore().getAt(0));
//            comboVac.setValue(comboVac.getStore().getAt(0).get('Vaccine_id'));
//            comboVac.fireEvent('select', comboVac);
//          }
//        });

//        var comboMedStaff = vacMantuForm.form.findField('MedStaffFact_id');
//        vacMantuForm.form.findField('LpuBuilding_id').getStore().load();
//        vacMantuForm.form.findField('LpuSection_id').getStore().load({
//          callback: function() {
//            comboMedStaff.getStore().load({
//              callback: function() {
//                comboMedStaff.setValue(formStoreRecord.get('MedPers_id'));
//              }
//            });
//          }
//        });

//        vacMantuForm.form.findField('TypeReaction_id').getStore().load();
//        
//        var comboVacWay = Ext.getCmp('mantu_VaccineWayCombo');
//        comboVacWay.reset();
//        comboVacWay.store.load({
//          params: this.formParams
////          ,callback: function(){
////            comboVacWay.setValue(comboVacWay.getStore().getAt(0).get('VaccineWayPlace_id'));
////          }
//        });

//        var comboVacSeria = Ext.getCmp('mantu_VaccineSeriaCombo');
//        comboVacSeria.store.load({
//          params: this.formParams
////          ,callback: function(){
////            comboVacSeria.setValue(comboVacSeria.getStore().getAt(0).get('VacPresence_id'));
////          }
//        });

            }.createDelegate(this)
        });
    }
});
