/**
 * amm_SprNacCalEditWindow - окно просмотра и редактирования справочника "вакцин "Национальный Календарь".
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      VAC
 * @access       public
 * @copyright    Copyright (c) 2011 Swan Ltd.
 * @author       Nigmatullin Tagir (Ufa)
 * @version      24.06.2013
 */

sw.Promed.amm_SprNacCalEditWindow = Ext.extend(sw.Promed.BaseForm, {
    id: 'amm_SprNacCalEditWindow',
    title: "Национальный календарь: Редактирование",
    titleBase: "Национальный календарь прививок: Добавление",
    width: 610,
    height: 680,
    autoScroll: true,
    autohight: true,
    maximizable: true,
    codeRefresh: true,
    modal: true,
    layout: 'border',
    border: false,
    closeAction: 'hide',
    objectName: 'amm_SprNacCalEditWindow',
    objectSrc: '/jscore/Forms/Vaccine/amm_SprNacCalEditWindow.js',
    onHide: Ext.emptyFn,
    signsOfAppointment: {
        'NC_Work_Check': 0,
        'NC_NoInfo_Check': 1,
        'NC_TubOtr_Check': 2,
        'NC_Priv1_Check': 3,
        'NC_NeBol_Check': 4,
        'NC_NePrivR_Check': 5,
        'NC_NeInfiс_Check': 6,
        'NC_Students_Check': 7,
        'NC_Pregnant_Check': 8,
        'NC_Conscripts_Check': 9,
        'NC_PersonsWithChronicDiseases_Check': 10,
        'NC_GroupRisc_Check': 11,
    },
    buttons:
            [
                {
                    text: BTN_FRMSAVE,
                    iconCls: 'save16',
                    id: 'NC_BtnSave',
                    tabIndex: TABINDEX_PRESVACEDITFRM + 31,
                    handler: function() {
                        var EditForm = Ext.getCmp('sprNacCalEditFormPanel');
                        if (!EditForm.form.isValid()) {
                            sw.Promed.vac.utils.msgBoxNoValidForm();
                            return false;
                        } //else {alert('???')}
//            return false; 
                        var params = new Object();
                        //Ext.getCmp('amm_SprNacCalEditWindow').formParams;
//            var paramsrec = new Object ();
                        params.NationalCalendarVac_id = params.NationalCalendarVac_id;
//            var SignPurpose = params.SignPurpose;
                        var form = Ext.getCmp('amm_SprNacCalEditWindow');
                        params.NationalCalendarVac_id = Ext.getCmp('amm_SprNacCalEditWindow').formParams.NationalCalendarVac_id;

                        // собираем поля из группы "Признаки назначения"
                        /*
                        params.SignPurpose = form.convertBool2int(Ext.getCmp('NC_Work_Check').checked) + form.convertBool2int(Ext.getCmp('NC_NoInfo_Check').checked)
                                + form.convertBool2int(Ext.getCmp('NC_TubOtr_Check').checked) + form.convertBool2int(Ext.getCmp('NC_Priv1_Check').checked)
                                + form.convertBool2int(Ext.getCmp('NC_NeBol_Check').checked) + form.convertBool2int(Ext.getCmp('NC_NePrivR_Check').checked)
                                + form.convertBool2int(Ext.getCmp('NC_NeInfiс_Check').checked) + form.convertBool2int(Ext.getCmp('NC_GroupRisc_Check').checked)
                                + form.convertBool2int(Ext.getCmp('NC_Students_Check').checked) + form.convertBool2int(Ext.getCmp('NC_Pregnant_Check').checked)
                                + form.convertBool2int(Ext.getCmp('NC_Conscripts_Check').checked) + form.convertBool2int(Ext.getCmp('NC_PersonsWithChronicDiseases_Check').checked);
                        */
                        var objSignsOfAppointment = form.signsOfAppointment;
                        var signPurpose = '';
                        for(var item in objSignsOfAppointment){
                            signPurpose = signPurpose + form.convertBool2int(Ext.getCmp(item).checked);
                        }
                        params.SignPurpose = signPurpose;
                        

                        params.Additional =  form.convertBool2int(Ext.getCmp('NC_Additional').checked);
                        sw.Promed.vac.utils.consoleLog('params.Additional:');
                        sw.Promed.vac.utils.consoleLog(params.Additional);
                        params.AgeTypeS = Ext.getCmp('NC_AgeTypeCombo').getValue();
                        params.AgeS = Ext.getCmp('NC_AreaRange1').getValue();
                        params.AgeTypeE = Ext.getCmp('NC_AgeTypeCombo').getValue();
                        params.AgeE = Ext.getCmp('NC_AreaRange2').getValue();


                        if (Ext.getCmp('NC_Period_Check').checked == true) {
//                Если периодичность зависит от предыдущей прививки
                            params.PeriodVac = Ext.getCmp('NC_Period').getValue();
                            params.PeriodVacType = Ext.getCmp('NC_AgeTypePeriodCombo').getValue();

                        } else {
                            params.PeriodVac = -1;
                            params.PeriodVacType = -1;

                        }



//            alert ( ' params.AgeE0 = ' + params.AgeE);

                        if (params.Edit == true) {
//                Контроль изменений при редактировании
                            if (params.SignPurpose == SignPurpose) {

                                params.SignPurpose = null;
                            }
                            ;
                            if (params.AgeTypeS == Ext.getCmp('NC_AgeTypeCombo').getValue()) {
                                params.AgeTypeS = null;
                            }
                            ;
                            if (params.AgeS == Ext.getCmp('NC_AreaRange1').getValue()) {
                                params.AgeS = null;
                            }
                            if (params.AgeE == Ext.getCmp('NC_AreaRange2').getValue()) {
                                params.AgeE = null;
//                      alert ( ' params.AgeE2 = ' + params.AgeE);
                            }
                        } else {
                            params.VaccineType_id = Ext.getCmp('NC_SprInoculationCombo').getValue();
                            params.Scheme_id = null;
                            params.Type_id = Ext.getCmp('NC_TypeImmunizationCombo').getValue();
                            params.SequenceVac = null;
                            if (Ext.getCmp('NC_ShemeNew_Check').checked == true) {
                                //Новая схема
                                params.Scheme_Num = null;
                            } else {
                                params.Scheme_Num = Ext.getCmp('NC_NumSchemeCombo').getValue();
                            }
                        }
                        Ext.Ajax.request({
                            url: '/?c=Vaccine_List&m=Vac_saveSprNC',
                            method: 'POST',
                            params: params,
                            success: function(response, opts) {
                                if (response.responseText.length > 0) {
                                    var result = Ext.util.JSON.decode(response.responseText);
                                    sw.Promed.vac.utils.consoleLog(result.rows[0]);
                                }
                                if (sw.Promed.vac.utils.msgBoxErrorBd(response) == 0) {
//								sw.Promed.vac.utils.consoleLog('this:');
//								sw.Promed.vac.utils.consoleLog(this);
                                    Ext.getCmp(Ext.getCmp('amm_SprNacCalEditWindow').formParams.parent_id).fireEvent('success', 'amm_SprNacCalEditWindow', {});
                                }
                                Ext.getCmp('amm_SprNacCalEditWindow').hide();
                            }.createDelegate(this)

                        });
                    }
                },
                {
                    text: '-'
                },
                //HelpButton(this),
                {text: BTN_FRMHELP,
                    iconCls: 'help16',
                    handler: function(button, event)
                    {
                        ShowHelp(this.ownerCt.titleBase);
                    }
                },
                {
                    text: BTN_FRMCLOSE,
                    tabIndex: -1,
                    tooltip: 'Закрыть структуру',
                    iconCls: 'cancel16',
                    handler: function()
                    {
                        this.ownerCt.hide();
                    }
                }
            ],
    initVisialObject: function(params) {
//                Ext.getCmp('NC_ShemeNew_Check').hide();
        if (params.Additional == 1) {
            Ext.getCmp('NC_Additional').setValue(true);
        } else {
            Ext.getCmp('NC_Additional').setValue(false);
        }
        //alert ('params.isnull = ' + params.isnull);
        //  undefined
        if (params.isnull == 1) {
            //alert (params.max_SequenceVac);
            if (params.max_SequenceVac >= 2) {
               Ext.getCmp('NC_Additional').show();
            } else {
                 Ext.getCmp('NC_Additional').hide();
            }
            //alert (params.max_Additional);
            if (params.max_Additional == 1) {
                Ext.getCmp('NC_BtnSave').disable(); 
                sw.swMsg.alert('Внимание','По данной схеме есть дополнительная вакцинация! <br/><br/> Новую запись добавить невозможно!');
               } else {
                Ext.getCmp('NC_BtnSave').enable();
            }  
        }
        Ext.getCmp('NC_SprInoculationCombo').setValue(params.VaccineType_id);
        Ext.getCmp('NC_SprInoculationCombo').fireEvent('change',Ext.getCmp('NC_SprInoculationCombo'),Ext.getCmp('NC_SprInoculationCombo').getValue());
//                Ext.getCmp('NC_SprInoculationCombo').disable ();
        Ext.getCmp('NC_TypeImmunizationCombo').setValue(params.Type_id);

//                Ext.getCmp('NC_SequenceVac').setValue( params.SequenceVac);
        Ext.getCmp('NC_SequenceVac').setValue("");

        Ext.getCmp('NC_AreaRange1').setValue(params.AgeS);
        Ext.getCmp('NC_AreaRange2').setValue(params.AgeE);
        Ext.getCmp('NC_AgeTypeCombo').setValue(params.AgeTypeS);

        Ext.getCmp('NC_Period').setValue(params.PeriodVac);
//                 alert (params.PeriodVacType);
        Ext.getCmp('NC_AgeTypePeriodCombo').setValue(params.PeriodVacType);
        Ext.getCmp('amm_SprNacCalEditWindow').loadAgeTypePeriodCombo(params.PeriodVac, params.PeriodVacType);


        if (params.PeriodVac == undefined) {
            Ext.getCmp('NC_Period_Check').setValue(false)
        } else {
            Ext.getCmp('NC_Period_Check').setValue(true)
        }
//                      if (params.SequenceVac != 1) {
//                           Ext.getCmp('NC_Period_Check').enable ();
//                      } else {
//                           Ext.getCmp('NC_Period_Check').disable ();
//                      }
        /*
        Ext.getCmp('NC_Work_Check').setValue(params.SignPurpose [0]);
        Ext.getCmp('NC_NoInfo_Check').setValue(params.SignPurpose [1]);
        Ext.getCmp('NC_TubOtr_Check').setValue(params.SignPurpose [2]);
        Ext.getCmp('NC_Priv1_Check').setValue(params.SignPurpose [3]);
        Ext.getCmp('NC_NeBol_Check').setValue(params.SignPurpose [4]);
        Ext.getCmp('NC_NePrivR_Check').setValue(params.SignPurpose [5]);
        Ext.getCmp('NC_NeInfiс_Check').setValue(params.SignPurpose [6])
        Ext.getCmp('NC_GroupRisc_Check').setValue(params.SignPurpose [7]);

        Ext.getCmp('NC_Students_Check').setValue(params.SignPurpose [8]);
        Ext.getCmp('NC_Pregnant_Check').setValue(params.SignPurpose [9]);
        Ext.getCmp('NC_Conscripts_Check').setValue(params.SignPurpose [10])
        Ext.getCmp('NC_PersonsWithChronicDiseases_Check').setValue(params.SignPurpose [11]);
        */
		
        var objSignsOfAppointment = this.signsOfAppointment;
        for(var item in objSignsOfAppointment){
            var param = params.SignPurpose[objSignsOfAppointment[item]];
            if(param){
                Ext.getCmp(item).setValue(param);
            }
        }

    },
    loadData4add: function(vaccineType_id, Scheme_Num) {
        var Scheme_id = vaccineType_id + '.' + Scheme_Num + '.1'
        var baseParams = new Object();
        baseParams.Scheme_id = Scheme_id;
        Ext.Ajax.request({
            url: '/?c=VaccineCtrl&m=loadSprNCFormInfo',
            method: 'POST',
            params: baseParams,
            success: function(response, opts) {

                if (response.responseText.length > 0) {
                    var result = Ext.util.JSON.decode(response.responseText);
                    Ext.getCmp('amm_SprNacCalEditWindow').formParams.result = result;
//                                                sw.Promed.vac.utils.consoleLog(result.rows[0]);
                    if (result.data.length > 0) {
                        result.data [0].isnull = true;
                        Ext.getCmp('amm_SprNacCalEditWindow').initVisialObject(result.data [0]);
                    
                    } else {Ext.getCmp('NC_Additional').hide();}
//                                                    else {return null}

                }

//if (sw.Promed.vac.utils.msgBoxErrorBd(response) == 0) {
//								sw.Promed.vac.utils.consoleLog('this:');
//								sw.Promed.vac.utils.consoleLog(this);
//                                        Ext.getCmp(Ext.getCmp('amm_SprNacCalEditWindow').formParams.parent_id).fireEvent('success', 'amm_SprNacCalEditWindow', {});
//                                            }
//                                Ext.getCmp('amm_SprNacCalEditWindow').hide();
            }.createDelegate(this)

        });
//                  alert ('baseParams.Sceme_id = ' + baseParams.Sceme_id);
//                  var record = new Ext.data.JsonStore({
//                        fields: ['VaccineType_id'
//                                , 'Scheme_id'
//                                , 'Type_id'
//                                , 'SequenceVac',
//                                , 'Scheme_Num',
//                                , 'SignPurpose' 
//                                , 'AgeTypeS' 
//                                , 'AgeS' 
//                                , 'AgeTypeE' 
//                                , 'AgeE' 
//                                , 'PeriodVac'
//                                , 'PeriodVacType'
//                                                        ],
//                        url: '/?c=VaccineCtrl&m=loadSprNCFormInfo',
//                        baseParams: baseParams,
//                        root: 'data'
//                });
//                return record;
    },
    convertBool2int: function(val) {
        if (val == true) {
            return '1'
        } else {
            return '0';
        }

    },
//        loadNumSchemeCombo: function (v_vaccineType_id) {
//                       Ext.getCmp('NC_NumSchemeCombo').getStore().load ({
//             params:{
//                 VaccineType_id: this.formParams.v_vaccineType_id 
//             }
////             ,
////             callback: function() {
////                 Ext.getCmp('NC_NumSchemeCombo').setValue(formStoreRecord.get('Scheme_Num'));
////             }           
//           })
//        },
    loadAgeTypePeriodCombo: function(kol, type) {
//            alert (Ext.getCmp('NC_Period').getValue());
        var DopStr = '';
        if ((type == 1) | (Ext.getCmp('NC_Additional').checked == true)) {
            DopStr = 'от предыдущей прививки';
        } else if (type == 2) {
            DopStr = 'от начала иммунизации';
        }
        if (type == 1) {
//                    Ext.getCmp('NC_Period2').setValue("дн. от предыдущей прививки")
            if (kol % 10 == 1) {
                Ext.getCmp('NC_Period2').setValue("день от предыдущей прививки")
            } else if (kol % 10 <= 4 & kol % 10 != 0) {
                Ext.getCmp('NC_Period2').setValue("дня " + DopStr)
            } else {
                Ext.getCmp('NC_Period2').setValue("дней " + DopStr)
            }
        } else if (type == 2) {
//                    Ext.getCmp('NC_Period2').setValue("мес. от начала иммунизации")
            if (kol % 10 == 1) {
                Ext.getCmp('NC_Period2').setValue("месяц " + DopStr)
            } else if (kol % 10 <= 4 & kol % 10 != 0) {
                Ext.getCmp('NC_Period2').setValue("месяца  " + DopStr)
            } else {
                Ext.getCmp('NC_Period2').setValue("месяцев  " + DopStr)
            }
        } else {
            if (kol % 10 == 1) {
                Ext.getCmp('NC_Period2').setValue("год")
            } else if (kol % 10 <= 4 & kol % 10 != 0) {
                Ext.getCmp('NC_Period2').setValue("года")
            } else {
                Ext.getCmp('NC_Period2').setValue("лет")
            }

        }
    },
//        initNC_Period2: function (day, type) {
//            if (type == 3 ) {
//                if (kol % 10) == 1 {
//                    
//                }
//            }
//        }, 

    initComponent: function() {

        var vEdit = false;
//             var formStoreRecord = new Object();


        /*
         * хранилище для доп сведений
         */
        this.formStore = new Ext.data.JsonStore({
            fields: [
//				  'NationalCalendarVac_id'
//                                ,
                'VaccineType_id'
                        , 'Scheme_id'
                        , 'Type_id'
                        , 'SequenceVac',
                , 'Scheme_Num',
                , 'SignPurpose'
                        , 'AgeTypeS'
                        , 'AgeS'
                        , 'AgeTypeE'
                        , 'AgeE'
                        , 'PeriodVac'
                        , 'PeriodVacType'
                        , 'Additional'
                        , 'SequenceVac'
                        , 'max_SequenceVac'
                        , 'max_Additional'
            ],
            url: '/?c=VaccineCtrl&m=loadSprNCFormInfo',
            key: 'NationalCalendarVac_id',
//                            'xxx_id',
            root: 'data'
        });

        this.FormPanel = new Ext.form.FormPanel({
            bodyStyle: 'padding: 5px',
            border: true,
            frame: true,
            autoScroll: true,
            id: 'sprNacCalEditFormPanel',
            autohight: true,
            region: 'center',
            items: [{
                    fieldLabel: 'Прививка',
                    id: 'NC_SprInoculationCombo',
                    autoLoad: false,
                    name: 'NC_SprInoculationCombo',
                    listWidth: 300,
                    labelSeparator: '',
                    width: 300,
                    editable: false,
                    default1Row: true, allowBlank: false,
                    //					hidden: true,
                    xtype: 'amm_SprInoculationCombo',
                    listeners: {
                        'select': function(combo, record, index) {
                            Ext.getCmp('NC_Additional').hide();    
                            Ext.getCmp('NC_NumSchemeCombo').getStore().load({
                                params: {
                                    VaccineType_id: combo.getValue()
                                },
                                callback: function() {
                                    if (Ext.getCmp('NC_ShemeNew_Check').getValue() == false) {
                                        if (Ext.getCmp('NC_NumSchemeCombo').store.getCount() > 0)
                                        {
                                            Ext.getCmp('NC_NumSchemeCombo').setValue(Ext.getCmp('NC_NumSchemeCombo').store.data.items [0].data.Scheme_Num);
                                        }


                                        //select (0, true);

                                    }
                                }
                            })
                        }.createDelegate(this),
                        'change': function(combo,value) {
                            if(value.inlist(['1','2','10']))
                            {
                                Ext.getCmp('NC_GroupRisc_Check').show();
                            }
                            else
                            {
                                Ext.getCmp('NC_GroupRisc_Check').setValue(0);
                                Ext.getCmp('NC_GroupRisc_Check').hide();
                            }

                            // доступен для прививок против гриппа
                            this.availableAgainstTheFlu = (value == 100) ? false : true;
                            var arrSignsOfAppointment = ['NC_Students_Check', 'NC_Pregnant_Check', 'NC_Conscripts_Check', 'NC_PersonsWithChronicDiseases_Check'];
                            arrSignsOfAppointment.forEach(function(item){
                                var elem = Ext.getCmp(item);
                                if(elem){
                                    elem.setDisabled(this.availableAgainstTheFlu);
                                    if(this.availableAgainstTheFlu) elem.setValue();
                                }
                            }, this);
                        }
                    }
                },
                {
                    height: 10,
                    border: false,
                    cls: 'tg-label'
                }
                , {
                    fieldLabel: 'Тип вакцинации',
                    id: 'NC_TypeImmunizationCombo',
//							autoLoad: false,
                    name: 'NC_TypeImmunizationCombo',
                    listWidth: 300,
                    labelSeparator: '',
                    width: 200,
//							editable: false,
                    xtype: 'ammTypeImmunizationCombo'
                },
                {
                    border: false,
                    layout: 'column',
                    labelWidth: 100,
                    items: [{
                            border: false,
                            layout: 'form',
                            width: 180,
                            items: [{
                                    fieldLabel: 'Номер схемы',
                                    id: 'NC_NumSchemeCombo',
                                    autoLoad: false,
                                    name: 'NC_NumSchemeCombo',
                                    listWidth: 300,
                                    labelSeparator: '',
                                    width: 50,
                                    disabled: true,
                                    editable: false,
                                    xtype: 'amm_NumSchemeCombo',
                                    listeners: {
                                        'select': function(combo, record, index) {

//                                                                loadAgeTypePeriodCombo (Ext.getCmp('NC_Period').getValue(), combo.getValue())

//                                                                  var rec =  
                                            Ext.getCmp('amm_SprNacCalEditWindow').loadData4add(Ext.getCmp('NC_SprInoculationCombo').getValue(), combo.getValue())
//                                                                  alert (rec.SignPurpose);    
//                                                                  sw.Promed.vac.utils.consoleLog('rec');
//                                                                  sw.Promed.vac.utils.consoleLog(rec);
                                        }.createDelegate(this)
                                    }
                                }]},
                        {
                            xtype: 'checkbox',
                            labelWidth: 100,
                            height: 24,
                            tabIndex: TABINDEX_EPLSIF + 1,
                            name: 'NC_ShemeNew_Check',
                            id: 'NC_ShemeNew_Check',
                            checked: true,
                            labelSeparator: '   ',
                            boxLabel: 'Новая схема'
                                    , listeners: {
                                'check': function(checkbox, checked) {
                                    if (checked == true) {
                                        Ext.getCmp('NC_NumSchemeCombo').disable()
                                    } else {
                                        Ext.getCmp('NC_NumSchemeCombo').enable()
                                    }

                                }.createDelegate(this)
                            }
                        }
//                                 { border: false,
//                                    layout: 'form',
//                                     labelWidth: 10,
//                                    items: [
//                                        { xtype: 'button',
//                                          text: 'Новый'
//                                        } 
//                                    ]
//                                    }

                    ]}
                , {
                    fieldLabel: 'Очередность прививки',
                    name: 'NC_SequenceVac',
                    id: 'NC_SequenceVac',
                    width: 50,
                    xtype: 'textfield',
                    value: '',
                    disabled: true

                },
                {
                    autoHeight: true,
                    autoScroll: true,
                    style: 'padding: 0px 5px;',
                    title: 'Возрастной диапазон применения',
                    //                                                        width: 755,
                    height: 80,
                    labelWidth: 100,
                    xtype: 'fieldset',
                    id: 'autoexpand',
                    items: [
                        {
                            height: 10,
                            border: false,
                            cls: 'tg-label'
                        },
                        {
//                                                fieldLabel: 'Номер схемы',
                            id: 'NC_AgeTypeCombo',
                            autoLoad: false,
                            name: 'NC_AgeTypeCombo',
                            listWidth: 300,
                            labelSeparator: '',
                            width: 150,
                            editable: false,
                            allowBlank: false,
                            xtype: 'amm_AgeTypeCombo'
                        },
                        {
                            border: false,
                            layout: 'column',
                            labelWidth: 70,
                            items: [{
                                    border: false,
                                    layout: 'form',
                                    items: [{
//							labelWidth: 100,
                                            fieldLabel: 'возраст от ',
                                            layout: 'form',
                                            id: 'NC_AreaRange1',
                                            name: 'NC_AreaRange1',
                                            width: 30,
                                            labelSeparator: '',
                                            allowBlank: false,
                                            tabIndex: TABINDEX_EMHPEF + 7,
                                            xtype: 'textfield'
                                        }]
                                },
                                {
                                    border: false,
                                    layout: 'form',
                                    labelWidth: 50,
                                    items: [{
                                            fieldLabel: '  до',
                                            labelStyle: 'text-align: center', // left
                                            layout: 'form',
                                            id: 'NC_AreaRange2',
                                            name: 'NC_AreaRange2',
                                            width: 30,
                                            labelSeparator: '',
                                            allowBlank: false, tabIndex: TABINDEX_EMHPEF + 8,
                                            xtype: 'textfield'
                                        }]
                                }
                            ]
                        }
                    ]
                }

                ,
                {
                    autoHeight: true,
                    autoScroll: true,
                    style: 'padding: 0px 5px;',
                    title: 'Периодичность',
                    //                                                        width: 755,
                    height: 80,
                    labelWidth: 100,
                    xtype: 'fieldset',
                    id: 'NC_Period_group',
                    items: [
                        {
                            height: 10,
                            border: false,
                            cls: 'tg-label'
                        },
                        {labelWidth: 10,
                            layout: 'form',
                            items: [
                        //*********
                        {
                            border: false,
                            layout: 'column',
                            labelWidth: 70,
                            items: [
                        //*********
                                {
                                    xtype: 'checkbox',
                                    height: 24,
                                    tabIndex: TABINDEX_EPLSIF + 1,
                                    labelWidth: 10,
                                    width: 350,
                                    name: 'NC_Period_Check',
                                    id: 'NC_Period_Check',
                                    checked: true,
                                    labelSeparator: '',
                                    boxLabel: 'Зависит от предыдущей прививки',
                                    listeners: {
                                        'check': function(checkbox, checked) {
                                            Ext.getCmp('NC_AgeTypePeriodCombo').setContainerVisible(checked);
                                            Ext.getCmp('NC_AgeTypePeriodCombo').allowBlank = !checked;
                                            Ext.getCmp('NC_Period').setContainerVisible(checked);
                                            Ext.getCmp('NC_Period').allowBlank = !checked;
                                            Ext.getCmp('NC_Period2').setContainerVisible(checked);


                                        }.createDelegate(this)
                                    }
                                },
                                {
                                    xtype: 'checkbox',
                                    height: 24,
                                    tabIndex: TABINDEX_EPLSIF + 1,
                                    labelWidth: 10,
                                    name: 'NC_Additional',
                                    id: 'NC_Additional',
                                    checked: true,
                                    hidden: true,
                                    labelSeparator: '',
                                    boxLabel: 'Дополнительная вакцинация'
                                    , listeners: {
                                        'check': function(checkbox, checked) {
                                              Ext.getCmp('amm_SprNacCalEditWindow').loadAgeTypePeriodCombo(
                                                      Ext.getCmp('NC_Period').getValue(), Ext.getCmp('NC_AgeTypePeriodCombo').getValue()
                                              )
                                        }.createDelegate(this)
                                    }
                                }
                        ]}

                            ]},
                        {
//                                                fieldLabel: 'Номер схемы',
                            id: 'NC_AgeTypePeriodCombo',
                            autoLoad: false,
                            name: 'NC_AgeTypePeriodCombo',
                            listWidth: 300,
                            labelSeparator: '',
                            width: 150,
                            editable: false,
                            xtype: 'amm_AgeTypeCombo',
                            listeners: {
                                'select': function(combo, record, index) {
                                    Ext.getCmp('amm_SprNacCalEditWindow').loadAgeTypePeriodCombo(Ext.getCmp('NC_Period').getValue(), combo.getValue())
                                }.createDelegate(this)
                            }
                        },
                        {
                            border: false,
                            layout: 'column',
                            labelWidth: 70,
                            items: [{
                                    border: false,
                                    layout: 'form',
                                    items: [
                                        {
                                            fieldLabel: 'через ',
                                            layout: 'form',
                                            id: 'NC_Period',
                                            name: 'NC_Period',
                                            width: 45,
                                            labelSeparator: '',
                                            //                                                allowBlank: false,                                                                     tabIndex: TABINDEX_EMHPEF + 8,
                                            xtype: 'textfield',
                                            listeners: {
                                                'change': function(text, newValue, oldValue) {
                                                    Ext.getCmp('amm_SprNacCalEditWindow').loadAgeTypePeriodCombo(newValue, Ext.getCmp('NC_AgeTypePeriodCombo').getValue())
                                                }.createDelegate(this)
                                            }
                                        }
                                    ]},
                                {border: false,
                                    layout: 'form',
                                    labelWidth: 10,
                                    items: [
                                        {
                                            fieldLabel: '', // дней ',
//                                                          labelStyle:'text-align: center',
                                            disabled: true,
                                            layout: 'form',
                                            id: 'NC_Period2',
                                            name: 'NC_Period2',
//                                                            disable: true,
                                            width: 210,
                                            labelSeparator: '',
//                                                            allowBlank: false,                                                                     tabIndex: TABINDEX_EMHPEF + 8,
                                            xtype: 'textfield'
                                        }
                                    ]}
                            ]}
                    ]},
                {
                    autoHeight: true,
                    autoScroll: true,
                    style: 'padding: 0px 5px;',
                    title: 'Признаки назначения',
                    //                                                        width: 755,
                    height: 80,
                    labelWidth: 75,
                    xtype: 'fieldset',
                    id: 'NC_SignPurpose',
                    items: [
                        {
                            height: 10,
                            border: false,
                            cls: 'tg-label'
                        },
                        {
                            xtype: 'checkbox',
                            height: 24,
                            tabIndex: TABINDEX_EPLSIF + 1,
                            name: 'NC_Work_Check',
                            id: 'NC_Work_Check',
                            checked: true,
                            labelSeparator: '',
                            boxLabel: 'Работающие по отдельным профессиям'
                        },
                        {
                            xtype: 'checkbox',
                            height: 24,
                            tabIndex: TABINDEX_EPLSIF + 1,
                            name: 'NC_NoInfo_Check',
                            id: 'NC_NoInfo_Check',
                            checked: true,
                            labelSeparator: '',
                            boxLabel: 'Нет сведений о прививках'
                        },
                        {
                            xtype: 'checkbox',
                            height: 24,
                            tabIndex: TABINDEX_EPLSIF + 1,
                            name: 'NC_TubOtr_Check',
                            id: 'NC_TubOtr_Check',
                            checked: true,
                            labelSeparator: '',
                            boxLabel: 'Туберкулиноотрицательные'
                        },
                        {
                            xtype: 'checkbox',
                            height: 24,
                            tabIndex: TABINDEX_EPLSIF + 1,
                            name: 'NC_Priv1_Check',
                            id: 'NC_Priv1_Check',
                            checked: true,
                            labelSeparator: '',
                            boxLabel: 'Привитые однократно'
                        },
                        {
                            xtype: 'checkbox',
                            height: 24,
                            tabIndex: TABINDEX_EPLSIF + 1,
                            name: 'NC_NeBol_Check',
                            id: 'NC_NeBol_Check',
                            checked: true,
                            labelSeparator: '',
                            boxLabel: 'Не болевшие'
                        },
                        {
                            xtype: 'checkbox',
                            height: 24,
                            tabIndex: TABINDEX_EPLSIF + 1,
                            name: 'NC_NePrivR_Check',
                            id: 'NC_NePrivR_Check',
                            checked: true,
                            labelSeparator: '',
                            boxLabel: 'Не привитые раннее'
                        },
                        {
                            xtype: 'checkbox',
                            height: 24,
                            tabIndex: TABINDEX_EPLSIF + 1,
                            name: 'NC_NeInfiс_Check',
                            id: 'NC_NeInfiс_Check',
                            checked: true,
                            labelSeparator: '',
                            boxLabel: 'Не инфицированные'
                        },
                        {
                            xtype: 'checkbox',
                            height: 24,
                            tabIndex: TABINDEX_EPLSIF + 1,
                            name: 'NC_Students_Check',
                            id: 'NC_Students_Check',
                            checked: false,
                            labelSeparator: '',
                            boxLabel: 'Учащиеся'
                        },
                        {
                            xtype: 'checkbox',
                            height: 24,
                            tabIndex: TABINDEX_EPLSIF + 1,
                            name: 'NC_Pregnant_Check',
                            id: 'NC_Pregnant_Check',
                            checked: false,
                            labelSeparator: '',
                            boxLabel: 'Беременные'
                        },
                        {
                            xtype: 'checkbox',
                            height: 24,
                            tabIndex: TABINDEX_EPLSIF + 1,
                            name: 'NC_Conscripts_Check',
                            id: 'NC_Conscripts_Check',
                            checked: false,
                            labelSeparator: '',
                            boxLabel: 'Призывники'
                        },
                        {
                            xtype: 'checkbox',
                            height: 24,
                            tabIndex: TABINDEX_EPLSIF + 1,
                            name: 'NC_PersonsWithChronicDiseases_Check',
                            id: 'NC_PersonsWithChronicDiseases_Check',
                            checked: false,
                            labelSeparator: '',
                            boxLabel: 'Лица с хроническими заболеваниями'
                        },
                        {
                            xtype: 'checkbox',
                            height: 24,
                            tabIndex: TABINDEX_EPLSIF + 1,
                            name: 'NC_GroupRisc_Check',
                            id: 'NC_GroupRisc_Check',
                            checked: true,
                            labelSeparator: '',
                            boxLabel: 'Группа риска'
                        }

                    ]
                }

            ]
        });

        Ext.apply(this, {
            formParams: null,
            items: [
                this.FormPanel
            ]
        });




        sw.Promed.amm_SprNacCalEditWindow.superclass.initComponent.apply(this, arguments);
    },
    show: function(record) {
        Ext.getCmp('NC_BtnSave').enable();
        sw.Promed.amm_SprNacCalEditWindow.superclass.show.apply(this, arguments);
        Ext.getCmp('NC_TypeImmunizationCombo').getStore().reload();
        Ext.getCmp('NC_SprInoculationCombo').store.load({
            params: {
                Trunc: 1
            }
        });
        this.formParams = record;


        sw.Promed.vac.utils.consoleLog('record.NationalCalendarVac_id = ' + record.NationalCalendarVac_id);
        var vEdit
        if (record.NationalCalendarVac_id == undefined) {
            Ext.getCmp('amm_SprNacCalEditWindow').setTitle('Национальный календарь прививок: Добавление');
            vEdit = false;
        }
        else {
            Ext.getCmp('amm_SprNacCalEditWindow').setTitle('Национальный календарь прививок: Редактирование');
            vEdit = true;

        }
        this.formStore.load({
            params: {
                NationalCalendarVac_id: record.NationalCalendarVac_id
            },
            callback: function() {
//                                    alert ('vEdit = ' + vEdit);
                var formStoreCount = this.formStore.getCount() > 0;
                if (formStoreCount) {
                    var formStoreRecord = this.formStore.getAt(0);
//                                    if (record.NationalCalendarVac_id == undefined)  {
//                                       this.formParams.Edit = vEdit;
                    if (vEdit == true) {
//                                        var form =  Ext.getCmp('amm_SprNacCalEditWindow')
                        this.formParams.VaccineType_id = formStoreRecord.get('VaccineType_id');
                        this.formParams.Scheme_id = formStoreRecord.get('Scheme_id');
                        this.formParams.Type_id = formStoreRecord.get('Type_id');
                        this.formParams.SequenceVac = formStoreRecord.get('SequenceVac');
                        this.formParams.Scheme_Num = formStoreRecord.get('Scheme_Num');
                        this.formParams.SignPurpose = formStoreRecord.get('SignPurpose'); 
                        this.formParams.Additional = formStoreRecord.get('Additional');
                         this.formParams.SequenceVac = formStoreRecord.get('SequenceVac'); 
                        this.formParams.max_SequenceVac = formStoreRecord.get('max_SequenceVac');

                        this.formParams.AgeTypeS = formStoreRecord.get('AgeTypeS');
                        this.formParams.AgeS = formStoreRecord.get('AgeS');
                        this.formParams.AgeTypeE = formStoreRecord.get('AgeTypeE');
                        this.formParams.AgeE = formStoreRecord.get('AgeE');

                        this.formParams.PeriodVac = formStoreRecord.get('PeriodVac');
                        this.formParams.PeriodVacType = formStoreRecord.get('PeriodVacType');
                    }
                }
//                       }.createDelegate(this)
//                           
//            }) ;                           

                if (vEdit == true) {
//                                    if (record.NationalCalendarVac_id != undefined){
                                       

//                                    Ext.getCmp('NC_ShemeNew_Check').setContainerVisible (false);


                    Ext.getCmp('amm_SprNacCalEditWindow').initVisialObject(this.formParams);
                    Ext.getCmp('NC_SequenceVac').setValue(this.formParams.SequenceVac);
                    Ext.getCmp('NC_ShemeNew_Check').hide();
                    Ext.getCmp('NC_SprInoculationCombo').disable();
                    Ext.getCmp('NC_NumSchemeCombo').disable();
                    Ext.getCmp('NC_Period2').disable();
                    if ((this.formParams.SequenceVac < 3) | (this.formParams.max_SequenceVac != this.formParams.SequenceVac)) {
                         Ext.getCmp('NC_Additional').hide();
                    } else {
                        Ext.getCmp('NC_Additional').show();
                    }
                   
                        
                    
//                     Ext.getCmp('NC_Additional').setValue(this.formParams.Additional);

                    if (this.formParams.PeriodVac == undefined) {
                        Ext.getCmp('NC_Period_Check').setValue(false)
                    } else {
                        Ext.getCmp('NC_Period_Check').setValue(true)
                    }
//                                              if (this.formParams.SequenceVac != 1) {
//                                                   Ext.getCmp('NC_Period_Check').enable ();
//                                              } else {
//                                                   Ext.getCmp('NC_Period_Check').disable ();
//                                              };
                    Ext.getCmp('NC_NumSchemeCombo').getStore().load({
                        params: {
                            VaccineType_id: this.formParams.VaccineType_id
                        }
                        ,
                        callback: function() {
//                                             Ext.getCmp('NC_NumSchemeCombo').setValue(this.formParams.Scheme_Num);
                            Ext.getCmp('NC_NumSchemeCombo').setValue(formStoreRecord.get('Scheme_Num'));
                        }
                    });


                } else {
//                                          Добавление


                    Ext.getCmp('NC_SprInoculationCombo').enable();
                    Ext.getCmp('NC_SprInoculationCombo').setValue(1);
                    Ext.getCmp('NC_SprInoculationCombo').fireEvent('change',Ext.getCmp('NC_SprInoculationCombo'),Ext.getCmp('NC_SprInoculationCombo').getValue());
                    Ext.getCmp('NC_TypeImmunizationCombo').setValue(1);

                    Ext.getCmp('NC_SequenceVac').setValue('');


                }

            }.createDelegate(this)

        });
//             alert (vEdit);
        if (vEdit == true) {
            var form = Ext.getCmp('amm_SprNacCalEditWindow');
            Ext.getCmp('NC_ShemeNew_Check').hide();
        } else {
            Ext.getCmp('NC_ShemeNew_Check').show();
            Ext.getCmp('NC_ShemeNew_Check').enable();
//                    if ( Ext.getCmp('NC_ShemeNew_Check').checked == false) {
//                        Ext.getCmp('NC_ShemeNew_Check').checked = true;
//                    }


            Ext.getCmp('NC_ShemeNew_Check').checked = true;
            Ext.getCmp('NC_ShemeNew_Check').setValue(true);
//                     alert (Ext.getCmp('NC_ShemeNew_Check').checked);

            Ext.getCmp('NC_Period_Check').enable();
            Ext.getCmp('NC_NumSchemeCombo').disable();


            Ext.getCmp('NC_Additional').setValue(0);
            Ext.getCmp('NC_Work_Check').setValue(0);
            Ext.getCmp('NC_NoInfo_Check').setValue(0);
            Ext.getCmp('NC_TubOtr_Check').setValue(0);
            Ext.getCmp('NC_Priv1_Check').setValue(0);
            Ext.getCmp('NC_NeBol_Check').setValue(0);
            Ext.getCmp('NC_NePrivR_Check').setValue(0);
            Ext.getCmp('NC_NeInfiс_Check').setValue(0)
            Ext.getCmp('NC_GroupRisc_Check').setValue(0);
            if (Ext.getCmp('NC_AgeTypePeriodCombo').getValue() == 0) {
                Ext.getCmp('NC_Period_Check').checked = false;
                Ext.getCmp('NC_Period_Check').setValue(false);
                var checked = false;
                Ext.getCmp('NC_AgeTypePeriodCombo').setContainerVisible(checked);
                Ext.getCmp('NC_AgeTypePeriodCombo').allowBlank = !checked;
                Ext.getCmp('NC_Period').setContainerVisible(checked);
                Ext.getCmp('NC_Period').allowBlank = !checked;
                Ext.getCmp('NC_Period2').setContainerVisible(checked);

//                        Ext.getCmp('NC_AgeTypePeriodCombo').setValue('Дни');
//                         Ext.getCmp('amm_SprNacCalEditWindow').loadAgeTypePeriodCombo (Ext.getCmp('NC_Period').getValue(), combo.getValue())
            }
//                    if (Ext.getCmp('NC_AgeTypeCombo').getValue() == 0) {
//                        Ext.getCmp('NC_AgeTypeCombo').setValue('Дни');
//                    }

        }
        Ext.getCmp('NC_SprInoculationCombo').isValid();
        var flag = Ext.getCmp('NC_Period_Check').checked
        Ext.getCmp('NC_AgeTypePeriodCombo').allowBlank = !flag;
        Ext.getCmp('NC_Period').allowBlank = !flag;

    }

});




