/**
 * amm_QuikImplVacForm - окно формы Исполнения прививки (для старых прививок - история)
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 *
 * @copyright    Copyright (c) 2012 
 * @author       
 * @version      28.01.2013
 * @comment      
 */

var Person_deadDT;

sw.Promed.amm_QuikImplVacForm = Ext.extend(sw.Promed.BaseForm, {
    id: 'amm_QuikImplVacForm',
    title: "Исполнение прививки",
    border: false,
    width: 800,
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
    objectName: 'amm_QuikImplVacForm',
    objectSrc: '/jscore/Forms/Vaccine/amm_QuikImplVacForm.js',
    onHide: Ext.emptyFn,
    listeners: {
        'show': function(c) {
            //исп-ся вместе с защитой от повторных нажатий
            sw.Promed.vac.utils.resetButsDis(c);
        },
        'hide': function(c) {
            //исп-ся вместе с защитой от повторных нажатий
            sw.Promed.vac.utils.resetButsDis(c);
        }
    },
    initCombo: function(parent, Params) {
        /* Функция инициализации комбобоксов 
         * Параметр parent:
         *  'quikImpl_LpuCombo' - изменение ЛПУ
         *  'quikImpl_LpuBuildingCombo' - изменение подразделения
         *  'quikImpl_MedPersonalCombo' - изменение службы
         */
        if (parent == 'quikImpl_LpuCombo') {
            //  Выводим список подразделений
            Ext.getCmp('quikImpl_LpuBuildingCombo').getStore().load({
                params: Params,
                callback: function() {
                    Ext.getCmp('quikImpl_LpuBuildingCombo').reset();
                }
            });
        }
        ;
        if ((parent == 'quikImpl_LpuCombo') || (parent == 'quikImpl_LpuBuildingCombo')) {
            //  Выводим список служб
            Ext.getCmp('quikImpl_ComboMedServiceVac').getStore().load({
                params: Params,
                callback: function() {
                    Ext.getCmp('quikImpl_ComboMedServiceVac').reset();

                }
            });
        }
        ;
        //  Выводим список сотрудников  
        Ext.getCmp('quikImpl_MedPersonalCombo').getStore().load({
            params: Params,
            callback: function() {
                var objMedService = Ext.getCmp('quikImpl_ComboMedServiceVac');
 
                if ((Params.Plan_id == -2) && (Ext.getCmp('amm_QuikImplVacForm').formParams.MedService_id != undefined))
                {
                    objMedService.setValue(Ext.getCmp('amm_QuikImplVacForm').formParams.MedService_id);

                    var Bulding_id = objMedService.findRecord('MedService_id', objMedService.value).data.LpuBuilding_id;
                    Ext.getCmp('quikImpl_LpuBuildingCombo').setValue(Bulding_id);
                }
                else {
                    Ext.getCmp('quikImpl_MedPersonalCombo').reset();
                }

            }
        });


    },
    initComponent: function() {
        var params = new Object();
        var form = this;
        //объект для контроля дат формы:
        this.validateVacImplementDate = sw.Promed.vac.utils.getValidateObj({
            formId: 'vacQuikImplEditForm',
            fieldName: 'vacImplementDate'
                    //tooltip: '123456',
                    //blankText: '56789' //"Это поле обязательно для заполнения"
        });

        /*
         * хранилище для доп сведений
         */
        this.formStore = new Ext.data.JsonStore({
            fields: ['Plan_id', 'MedPers_id', 'planTmp_id', 'Date_Plan', 'type_name', 'Name',
                'SequenceVac', 'VaccineType_id', 'BirthDay', 'MedService_id', 'StoreKeyList',
                'Vaccine_id', 'VaccinePlace_id', 'Dose', 'Seria'],
            url: '/?c=VaccineCtrl&m=loadPurpFormInfo',
            key: 'Plan_id',
            root: 'data'
        });

        this.gridConfiSimilarRecords = new gridConfiSimilarRecords();

        this.gridSimilarRecords = new Ext.grid.EditorGridPanel({
            id: 'vacObjects_gridSimilarRecords',
            autoExpandColumn: 'autoexpand',
            //region: 'west',
            //width: 200,
            //height: 100,
            autoHeight: true,
            //autoWidth: true,
            split: true,
            //collapsible: true,
            floatable: false,
            store: form.gridConfiSimilarRecords.store, // определили хранилище
            title: 'Прививки:', // Заголовок
            colModel: form.gridConfiSimilarRecords.columnModel,
            tabIndex: TABINDEX_VACIMPFRM + 20,
            listeners: {
                'celldblclick': function(grid, rowNum, columnIndex, e) {
                    if (columnIndex == 0)
                        return;
                    var record = grid.getStore().getAt(rowNum);  // Get the Record
                    //record.set('selRow', !record.get('selRow'));

					if(record.get('selRow') == 'Да')
						record.set('selRow', 'Нет');
					else
						record.set('selRow', 'Да');

                    var selRows = [];
                    var gridStore = grid.getStore();
                    var gridStoreCnt = gridStore.getCount();
                    for (var i = 0; i < gridStoreCnt; i++) {
                        var rec = gridStore.getAt(i);
                        if (rec.get('selRow')=='Да') {
//              selRows.push(rec.get('PlanTmp_id'));
                            selRows.push(rec.get('PlanView_id'));
                        }
                    }
                    grid.store.keyList = selRows;
                },
                'cellclick': function(grid, rowNum, columnIndex, e, noSet) {
                    if (columnIndex != 0)
                        return;
                    var record = grid.getStore().getAt(rowNum);
                    if (noSet === 1) {
                        //record.set('selRow', record.get('selRow'));
						//record.set('selRow', 'Нет');
						if(record.get('selRow') == 'Да')
							record.set('selRow', 'Да');
						else
							record.set('selRow', 'Нет');
                    } else {
                        //record.set('selRow', !record.get('selRow'));
						if(record.get('selRow') == 'Да')
							record.set('selRow', 'Нет');
						else
							record.set('selRow', 'Да');
                    }
//          }
					//log(record.get('selRow'));
					//log(record.get('selRow').getValue);
					//alert('2');
					//record.set('selRow', 'V');
                    var selRows = [];
                    var selRowsplan = [];
                    var gridStore = grid.getStore();
                    var gridStoreCnt = gridStore.getCount();
                    for (var i = 0; i < gridStoreCnt; i++) {
                        var rec = gridStore.getAt(i);
                        //if (rec.get('selRow')) {
						if (rec.get('selRow') == 'Да') {
//              selRows.push(rec.get('PlanTmp_id'));
                            sw.Promed.vac.utils.consoleLog(rec.data);
                            if (rec.get('PlanView_id')) {
                                selRows.push(rec.get('PlanView_id'));
                            }
                            else {
                                selRowsplan.push(rec.get('PlanFinal_id'))
                            }

                        }
                    }
                    grid.store.keyList = selRows;
                    grid.store.keyListPlan = selRowsplan;
                    sw.Promed.vac.utils.consoleLog('keyList');
                    sw.Promed.vac.utils.consoleLog(grid.store.keyList);
                    sw.Promed.vac.utils.consoleLog(grid.store.keyListPlan);
                }
            }
        });

        //this.gridSimilarRecords.getGrid().view = new Ext.grid.GridView(
        this.gridSimilarRecords.view = new Ext.grid.GridView(
                {
                    getRowClass: function(row, index)
                    {
                        var cls = '';
                        //if ()
                        if ((row.get('PlanFinal_id') == Ext.getCmp('amm_QuikImplVacForm').formParams.vacJournalAccount_id)
                                & (Ext.getCmp('amm_QuikImplVacForm').formParams.plan_id == -2))
                            //cls = 'x-grid-rowbold ';	
                            cls = 'x-grid-rowgreen ';
                        return cls;
                    }
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

        Ext.apply(this, {
            formParams: null,
            buttons: [
                {text: 'Сохранить как исполненная',
                    iconCls: 'save16',
                    tabIndex: TABINDEX_VACIMPNPURPFRM + 20,
                    handler: function(b) {
                        b.setDisabled(true);//деактивируем кнопку (исключен повторных нажатий)
                        var quikImplForm = Ext.getCmp('vacQuikImplEditForm');
                        if (!quikImplForm.form.isValid()) {
                            sw.Promed.vac.utils.msgBoxNoValidForm();
                            b.setDisabled(false);
                            return false;
                        }
                        if (!Ext.getCmp('vacObjects_gridSimilarRecords').store.keyList.length) {
                            Ext.Msg.alert('Ошибка', 'Не выбрана прививка! Необходимо выбрать прививку!');
                            b.setDisabled(false);
                            return false;
                        }

                        if ((Person_deadDT != undefined) & (Person_deadDT != "")) {
                            //alert('???');
                            //sw.Promed.vac.utils.consoleLog('response' + Ext.getCmp('vacQuikImplEditForm').form.findField('vacImplementDate').getValue());    
                            //sw.Promed.vac.utils.consoleLog('Person_deadDT = ' + Person_deadDT);
                            if (Ext.getCmp('vacQuikImplEditForm').form.findField('vacImplementDate').getValue() > Person_deadDT) {
                                Ext.Msg.alert('Ошибка', 'Дата вакцинации превышает дату смерти!');
                                b.setDisabled(false);
                                return false;
                            }
                        }



                        var comboVacList = Ext.getCmp('vacObjects_comboVaccineList');

                        var implWin = Ext.getCmp('amm_QuikImplVacForm');
                        var param = quikImplForm.form.findField('vacImplementDate').getValue();
                        if (param != undefined && param != '') {
                            param = param.format('d.m.Y');
                        }
                        implWin.formParams.date_vac = param;
                        implWin.formParams.vacImplementDate = quikImplForm.form.findField('vacImplementDate').getValue();

                        var idx = quikImplForm.form.findField('VaccineSeria_id').getStore().findBy(function(rec) { return rec.get('VacPresence_id') == quikImplForm.form.findField('VaccineSeria_id').getValue(); });
                        var seriaRecord = quikImplForm.form.findField('VaccineSeria_id').getStore().getAt(idx);
                        if (typeof(seriaRecord) == 'object') {
                            comboVacList.generalParams.vac_seria = seriaRecord.get('Seria');
                            comboVacList.generalParams.vac_period = seriaRecord.get('Period');
                        } else {
                            comboVacList.generalParams.vac_seria = quikImplForm.form.findField('VaccineSeria_id').getRawValue();
                        }
                        
                        comboVacList.generalParams.vaccine_way_place_id = quikImplForm.form.findField('VaccineWayPlace_id').getValue();
                        var i = quikImplForm.form.findField('VaccineDoze_id').getStore().findBy(function(rec) { return rec.get('VaccineDose_id') == quikImplForm.form.findField('VaccineDoze_id').getValue(); });
                        var recDoze = quikImplForm.form.findField('VaccineDoze_id').getStore().getAt(i);
                        if (typeof(recDoze) == 'object') {
                            comboVacList.generalParams.vac_doze = recDoze.get('VaccineDose_Name');//quikImplForm.form.findField('VaccineDoze_id').getValue();
                           
                        }
                        
                        implWin.formParams.vac_seria = comboVacList.generalParams.vac_seria;
                        implWin.formParams.vac_doze = comboVacList.generalParams.vac_doze;


                        implWin.formParams.Lpu_id = quikImplForm.form.findField('Lpu_id').getValue(),
                                //implWin.formParams.med_staff_impl_id = quikImplForm.form.findField('MedStaffFact_id').getValue(),  !!!
                        implWin.formParams.med_staff_impl_id = quikImplForm.form.findField('quikImpl_MedPersonalCombo').getValue()
                        implWin.formParams.medservice_id = Ext.getCmp('quikImpl_ComboMedServiceVac').getValue()
                        implWin.formParams.key_list = Ext.getCmp('vacObjects_gridSimilarRecords').store.keyList.join(',');
                        implWin.formParams.key_list_plan = Ext.getCmp('vacObjects_gridSimilarRecords').store.keyListPlan.join(',');
                        //  alert (implWin.formParams.key_list);
                        //return false;
                        implWin.formParams.vacJournalAccountOld_id = Ext.getCmp('amm_QuikImplVacForm').formParams.vacJournalAccount_id;
                        //implWin.formParams.Parent = -2;
                        implWin.formParams.vacJournalAccount_id = 0;
                        if (implWin.formParams.Lpu_id == '') {
                            implWin.formParams.Lpu_id = -1;
                        }
                        
                        implWin.formParams.vaccine_Name = comboVacList.lastSelectionText;
                        implWin.formParams.vac_arr = Ext.getCmp('vacObjects_gridSimilarRecords').store.keyList;
                        
                        /*
                         console.log('params =');
                        console.log(implWin.formParams);
                        console.log(Ext.getCmp('vacObjects_gridSimilarRecords').store.keyList);
                         console.log(implWin.formParams.key_list.indexOf('_1.'));
                         if (implWin.formParams.key_list.indexOf('_1.')>=0) 
                                 console.log('key_list = true');
                             else 
                                  console.log('key_list = false');
                         return false;
                         */
                        
                        

                        Ext.Ajax.request({
                            url: '/?c=VaccineCtrl&m=saveImplWithoutPurp',
                            method: 'POST',
                            params: implWin.formParams,
                            success: function(response, opts) {
                                sw.Promed.vac.utils.consoleLog(response);
                                var obj = Ext.util.JSON.decode(response.responseText);
                                sw.Promed.vac.utils.consoleLog('obj.rows[0].vacJournalAccount_id')
                                sw.Promed.vac.utils.consoleLog(obj.rows[0])

                                if (sw.Promed.vac.utils.msgBoxErrorBd(response) == 0) {

                                    //alert (Ext.getCmp('vacObjects_gridSimilarRecords').store.keyList.length);
                                   implWin.formParams.vacJournalAccount_id = obj.rows[0].vacJournalAccount_id;
                                    if (sw.Promed.vac.utils.msgBoxErrorBd(response) == 0) {
                                        if (Ext.getCmp('amm_VacPlan') != undefined)
                                            Ext.getCmp('amm_VacPlan').fireEvent('success', 'amm_PurposeVacForm', {keys: [Ext.getCmp('amm_QuikImplVacForm').formParams.key_list]});
                                        if (Ext.getCmp('amm_Kard063') != undefined) {
                                             var params = new Object();
                                             
                                            Ext.getCmp('amm_Kard063').fireEvent('success', 'amm_PurposeVacForm', implWin.formParams);
                                        }
                                            
                                    }
                                    //}
                                    form.hide();
                                }else{
                                    this.setDisabled(false);
                                }
                            }.createDelegate(b),
                            failure: function(response, opts) {
                                sw.Promed.vac.utils.consoleLog('server-side failure with status code: ' + response.status);
                            }
                        });
                    }.createDelegate(this)

                },
                {
                    text: '-'
                }, {
                    handler: function() {
                        this.hide();
                    }.createDelegate(this),
                    iconCls: 'close16',
//        id: 'vacPurp_CancelButton',
                    onTabAction: function() {
                        Ext.getCmp('vacQuikImplEditForm').form.findField('vacImplementDate').focus();
                    }.createDelegate(this),
                    tabIndex: TABINDEX_VACIMPFRM + 32,
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
                    id: 'vacQuikImplEditForm',
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
                        },
                        {
                            border: false,
                            layout: 'column',
                            defaults: {
                                //xtype: 'form',
                                columnWidth: 0.5,
                                bodyBorder: false,
                                //labelWidth: 100,
                                anchor: '100%'
                            },
                            bodyStyle: 'padding: 5px',
                            //height: 100,
                            //autohight: true,
                            items: [{
                                    layout: 'form',
                                    items: [{
                                            fieldLabel: 'Тип прививки',
                                            tabIndex: TABINDEX_VACIMPFRM + 10,
                                            xtype: 'textarea',
                                            id: 'vacPurposeType',
                                            name: 'vacPurposeType',
                                            grow: true,
                                            growMax: 60,
                                            growMin: 20,
                                            width: 260,
                                            disabled: true,
                                            readOnly: true
                                                    //editable: false
                                                    // }, {
                                                    //   fieldLabel: 'Дата назначения прививки',
                                                    //   tabIndex: TABINDEX_VACPRPFRM + 11,
                                                    //   allowBlank: false,
                                                    //   xtype: 'swdatefield',
                                                    //   format: 'd.m.Y',
                                                    //   plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
                                                    //   name: 'vacPurposeDate',
                                                    //   listeners: {
                                                    //     'change': function(field, newValue, oldValue) {
                                                    //       var combo = Ext.getCmp('vacObjects_comboVaccineList');
                                                    //       combo.generalParams.date_purpose = newValue.format('d.m.Y');
                                                    //       combo.reset();
                                                    //       //Ext.getCmp('VaccineListCombo').store.load({params: {VaccineType_id:'3', BirthDay:'03.05.2011'}});
                                                    //       combo.store.load({
                                                    //         params: combo.generalParams
                                                    //         });
                                                    //     }
                                                    //   }

                                       },
                                        sw.Promed.vac.objects.vaccineListCombo({
//								id: 'vaccineListCombo',
                                            idPrefix: 'vacObjects',
                                            hiddenName: 'Vaccine_id',
                                            tabIndex: TABINDEX_VACIMPFRM + 12
                                        }),
                                        sw.Promed.vac.objects.comboVaccineSeria({
                                            id: 'vacObjects_comboVaccineSeria',
                                            tabIndex: TABINDEX_VACIMPFRM + 13,
                                            allowTextInput: true,
                                            listeners: {
                                                'blur': function(combo) {
                                                    //alert('blur');
                                                    //return;
                                                }
                                            }
                                        }),
                                        sw.Promed.vac.objects.comboVaccineWay({
                                            id: 'vacObjects_comboVaccineWay',
                                            tabIndex: TABINDEX_VACIMPFRM + 14
                                        }),
                                        sw.Promed.vac.objects.comboVaccineDoze({
                                            id: 'vacObjects_comboVaccineDoze',
                                            tabIndex: TABINDEX_VACIMPFRM + 15
                                        })
                                    ]

                                }, {
                                    layout: 'form',
                                    items: [
                                        //       {
                                        // autoHeight: true,
                                        //         style: 'margin: 5px; padding: 5px;',
                                        //         title: 'Назначил врач:',
                                        //         xtype: 'fieldset',
                                        //         items: [{

                                        //           id: 'purp_LpuBuildingCombo',
                                        //           //lastQuery: '',
                                        //           listWidth: 600,
                                        //           linkedElements: [
                                        //           'purp_LpuSectionCombo'
                                        //           ],
                                        //           tabIndex: TABINDEX_VACPRPFRM + 21,
                                        //           width: 260,
                                        //           xtype: 'swlpubuildingglobalcombo'
                                        //         }, {
                                        //           id: 'purp_LpuSectionCombo',
                                        //           linkedElements: [
                                        //           'purp_MedPersonalCombo'
                                        //           //                  ,'EPLSIF_MedPersonalMidCombo'
                                        //           ],
                                        //           listWidth: 600,
                                        //           parentElementId: 'purp_LpuBuildingCombo',
                                        //           tabIndex: TABINDEX_VACPRPFRM + 22,
                                        //           width: 260,
                                        //           xtype: 'swlpusectionglobalcombo'
                                        //         }, {
                                        //           allowBlank: false,
                                        //           hiddenName: 'MedStaffFact_id',
                                        //           id: 'purp_MedPersonalCombo',
                                        //           parentElementId: 'purp_LpuSectionCombo',
                                        //           listWidth: 600,
                                        //           tabIndex: TABINDEX_VACPRPFRM + 23,
                                        //           width: 260,
                                        //           emptyText: VAC_EMPTY_TEXT,
                                        //           xtype: 'swmedstafffactglobalcombo'
                                        //         }]
                                        //       }

                                        {
                                            autoHeight: true,
//                layout: 'form',
                                            style: 'margin: 5px; padding: 5px;',
                                            title: 'Исполнение прививки',
                                            xtype: 'fieldset',
                                            items: [
                                                sw.Promed.vac.objects.fieldVacImplementDate({
                                                    tabIndex: TABINDEX_VACIMPFRM + 10,
                                                    onChange: function(field, newValue, oldValue) {
//                                                                                                          Обновляем список вакцин в зависимости от возраста пациента
                                                        var combo = Ext.getCmp('vacObjects_comboVaccineList');
                                                        var val = combo.value;
//                                                                                                          var tmp_txt = combo.v

//                                                                                                           alert (combo.lastSelectionText);
//                                                                                                          combo.generalParams = record;
//                                                                                                          alert (oldValue); 
                                                        Ext.getCmp('amm_QuikImplVacForm').formParams.date_purpose = newValue.format('d.m.Y');
                                                        combo.reset();
                                                        combo.store.load({
                                                            params: Ext.getCmp('amm_QuikImplVacForm').formParams,
                                                            callback: function() {
                                                                if (combo.getStore().getCount() > 0) {
                                                                    combo.setValue(val);
//                                                                                                                 callback: function(){
//                                                                                                                     var arr = combo.store.data;
//                                                                                                                      sw.Promed.vac.utils.consoleLog(arr);
                                                                    if (combo.lastSelectionText == val) {
                                                                        combo.setValue(undefined)
                                                                    }
//                                                                                                                 }
                                                                }
//                                                                                                          sw.Promed.vac.utils.consoleLog(combo);
//                                                                                                              if (!!combo.generalParams.row_plan_parent) comboVacWay.setValue(comboVacWay.getStore().getAt(0).get('VaccineWayPlace_id'));
                                                            }
                                                        }
                                                        );
//                                                                                                       alert (newValue);
                                                    }
                                                }),
                                                sw.Promed.vac.objects.comboLpu({
                                                    idPrefix: 'quikImpl',
                                                    id: 'quikImpl_LpuCombo',
                                                    tabIndex: TABINDEX_VACIMPFRM + 11,
                                                    listeners: {
                                                        'select': function(combo, record, index) {
                                                            var Params = new Object();
                                                            Params.Lpu_id = combo.getValue();
                                                            Ext.getCmp('amm_QuikImplVacForm').initCombo('quikImpl_LpuCombo', Params);
                                                        }
                                                    }

                                                }),
                                                sw.Promed.vac.objects.comboLpuBuilding({
                                                    idPrefix: 'quikImpl',
                                                    id: 'quikImpl_LpuBuildingCombo',
                                                    tabIndex: TABINDEX_VACIMPFRM + 11,
                                                    emptyText: VAC_EMPTY_TEXT,
                                                    listeners: {
                                                        'select': function(combo, record, index) {
                                                            var Params = new Object();
                                                            Params.LpuBuilding_id = combo.getValue();
                                                            Ext.getCmp('amm_QuikImplVacForm').initCombo('quikImpl_LpuBuildingCombo', Params);
                                                        }.createDelegate(this)
                                                    }
                                                }),
                                                {fieldLabel: 'Служба',
                                                    id: 'quikImpl_ComboMedServiceVac',
                                                    listWidth: 600,
                                                    tabIndex: TABINDEX_VACIMPFRM + 12,
                                                    width: 260,
                                                    emptyText: VAC_EMPTY_TEXT,
                                                    xtype: 'amm_ComboMedServiceVac',
                                                    //allowBlank: false,
                                                    listeners: {
                                                        'select': function(combo, record, index) {
                                                            var Params = new Object();
                                                            sw.Promed.vac.utils.consoleLog('record');
                                                            sw.Promed.vac.utils.consoleLog(record.data);
                                                            Params.MedService_id = combo.getValue();
                                                            Ext.getCmp('amm_QuikImplVacForm').initCombo('quikImpl_ComboMedServiceVac', Params);
                                                        }.createDelegate(this)
                                                    }


                                                }


                                                , {fieldLabel: 'Врач (исполнил)',
                                                    id: 'quikImpl_MedPersonalCombo',
                                                    listWidth: 600,
                                                    tabIndex: TABINDEX_VACIMPFRM + 13,
                                                    width: 260,
                                                    emptyText: VAC_EMPTY_TEXT,
                                                    xtype: 'amm_ComboVacMedPersonal'
                                                            //									allowBlank: false,
                                                }
                                            ]
                                        }

                                    ]
                                }]

                        },
                        this.gridSimilarRecords
                    ]
                })
            ]
        });
        sw.Promed.amm_QuikImplVacForm.superclass.initComponent.apply(this, arguments);
    },
    openVacPurposeEditWindow: function(action) {
        var current_window = this;
        var params = new Object();
        var vacPurpose_grid = current_window.findById('LTVW_PersonPrivilegeGrid');

    },

    show: function(record) {
        sw.Promed.amm_QuikImplVacForm.superclass.show.apply(this, arguments);

        var vacQuikImplForm = Ext.getCmp('vacQuikImplEditForm');
        vacQuikImplForm.getForm().reset();

        vacQuikImplForm.form.findField('VaccineWayPlace_id').allowBlank = !record.row_plan_parent;
        vacQuikImplForm.form.findField('VaccineDoze_id').allowBlank = !record.row_plan_parent;
        //vacQuikImplForm.form.findField('MedStaffFact_id').allowBlank = !record.row_plan_parent; // !!!
        vacQuikImplForm.form.findField('quikImpl_MedPersonalCombo').allowBlank = !record.row_plan_parent;

        this.formParams = record;

        this.formStore.load({
            params: {
                plan_id: record.plan_id,
                user_id: getGlobalOptions().pmuser_id,
                Person_id: record.person_id,
                Vac_Scheme_id: record.vac_scheme_id,
                vacJournalAccount_id: record.vacJournalAccount_id
            },
            callback: function() {
                var vacQuikImplForm = Ext.getCmp('vacQuikImplEditForm');
                var formStoreRecord = this.formStore.getAt(0);

                sw.Promed.vac.utils.consoleLog('formStoreRecord');

                sw.Promed.vac.utils.consoleLog(formStoreRecord.data);

                if (formStoreRecord.get('Plan_id') == undefined) {
                    Ext.Msg.alert('Внимание', 'Назначение по выбранной прививке уже было выполнено!');
                    this.hide();
                    return false;
                }

                var comboVaccineList = Ext.getCmp('vacObjects_comboVaccineList');
                comboVaccineList.generalParams = record;

                this.formParams.date_purpose = formStoreRecord.get('Date_Plan');
                this.formParams.Plan_id = formStoreRecord.get('Plan_id');
                this.formParams.MedService_id = formStoreRecord.get('MedService_id');
                this.formParams.vaccine_id = formStoreRecord.get('Vaccine_id');

                this.formParams.vac_type_id = formStoreRecord.get('VaccineType_id');
                this.formParams.birthday = formStoreRecord.get('BirthDay');
                if (formStoreRecord.get('Person_id') != undefined) {
                    this.formParams.person_id = formStoreRecord.get('Person_id');
                    record.person_id = this.formParams.person_id;
                }

                Ext.getCmp('vacObjects_gridSimilarRecords').store.keyList = [record.plan_id];
//        Ext.getCmp('vacPurpEditForm').form.findField('vacPurposeDate').setValue(record.date_purpose);
                if (!!record.row_plan_parent) {
                    sw.Promed.vac.utils.consoleLog('vacImplementDate');
                    vacQuikImplForm.form.findField('vacImplementDate').setValue(new Date);
                }

                //контроль диапазона дат:
                this.validateVacImplementDate.init(function(o) {

                    var resObj = {};
                    if (o.birthday != undefined)
                        resObj.personBirthday = o.birthday;
//					if (o.vacAgeBegin != undefined) resObj.dateRangeBegin = dateRangeBegin;
//					if (o.vacAgeEnd != undefined) resObj.dateRangeEnd = dateRangeEnd;
                    return resObj;
                }(this.formParams));
                this.validateVacImplementDate.getMinDate();
                this.validateVacImplementDate.getMaxDate();

                this.PersonInfoPanel.load({
                    callback: function() {
                        this.PersonInfoPanel.setPersonTitle();
                        Person_deadDT = Ext.getCmp('amm_Kard063').PersonInfoPanel.getFieldValue('Person_deadDT');
                        if (Person_deadDT != undefined)
                            Ext.getCmp('vacQuikImplEditForm').form.findField('vacImplementDate').setMaxValue(Person_deadDT);

                    }.createDelegate(this),
                    loadFromDB: true,
                    Person_id: record.person_id
                            , Server_id: record.Server_id
                });

                this.gridSimilarRecords.store.load({
//				Ext.getCmp('vacObjects_gridSimilarRecords').store.load({
                    params: record
                });

                var Params = new Object();
                Params.Lpu_id = getGlobalOptions().lpu_id;
                Params.Plan_id = formStoreRecord.get('Plan_id');
                Ext.getCmp('amm_QuikImplVacForm').initCombo('quikImpl_LpuCombo', Params);

                //var comboMedStaff = vacQuikImplForm.form.findField('MedStaffFact_id');  !!!
                //id: 'quikImpl_LpuCombo',
                sw.Promed.vac.utils.consoleLog('quikImpl_LpuCombo');
                vacQuikImplForm.form.findField('quikImpl_LpuCombo').setValue(getGlobalOptions().lpu_id);
                //  alert ('record = ' + record.name);
                //alert (this.formParams.Plan_id);
                if (formStoreRecord.get('Plan_id') == -2) {
                    //  Редактирование вакцины 
                    Ext.getCmp('vacQuikImplEditForm').form.findField('vacImplementDate').setValue(this.formParams.date_purpose)
                    this.formParams.vac_info = record.name
                    this.formParams.StoreKeyList = formStoreRecord.get('StoreKeyList');
                    this.formParams.VaccinePlace_id = formStoreRecord.get('VaccinePlace_id');
                    this.formParams.Dose = formStoreRecord.get('Dose');
                    this.formParams.Seria = formStoreRecord.get('Seria');


                    //Ext.getCmp('quikImpl_ComboMedServiceVac').setValue(this.formParams.MedService_id)
                    //alert (this.formParams.MedService_id);
                    //this.formParams.date_purpose)
                }
                else {
                    this.formParams.vac_info = formStoreRecord.get('type_name');
                    this.formParams.vac_info += '\n' + formStoreRecord.get('Name').replace('<br />', '');
                    this.formParams.vac_info = formStoreRecord.get('Name').replace('<br />', '');
                    if (formStoreRecord.get('SequenceVac')) {//если 0, то не пишем (одиночная прививка)
                        this.formParams.vac_info += '\n' + 'Очередность: ' + formStoreRecord.get('SequenceVac');
                    }
                    //alert (this.formParams.vac_info);
                }
                //sw.Promed.vac.utils.consoleLog('vacPurposeType');
                //vacQuikImplForm.form.findField('vacPurposeType').setValue(this.formParams.vac_info);
                Ext.getCmp('vacPurposeType').setValue(this.formParams.vac_info);

                comboVaccineList.reset();
                var params = this.formParams;
                comboVaccineList.store.load({
                    //params: record,
                    params: params,
                    callback: function() {
                        //alert ('params.vaccine_id = ' + params.vaccine_id);
                        if (formStoreRecord.get('Plan_id') == -2) {
                            comboVaccineList.setValue(params.vaccine_id);
                            //var comboVacWay = Ext.getCmp(obj.idComboVaccineWay); 
                            var comboVacWay = Ext.getCmp('vacObjects_comboVaccineWay');
                            comboVacWay.reset();
                            comboVacWay.store.load({
                                params: params,
                                callback: function() {
                                    comboVacWay.setValue(params.VaccinePlace_id);
                                }
                            });

                            var comboVacDoze = Ext.getCmp('vacObjects_comboVaccineDoze');
                            var comboVacSeria = Ext.getCmp('vacObjects_comboVaccineSeria');
                            comboVacSeria.setValue(params.Seria);
                            comboVacDoze.reset();
                            comboVacDoze.store.load({
                                params: params,
                                callback: function() {
                                    comboVacDoze.setValue(params.Dose);

                                }

                            });
                        }
                    }
                });



                // Передергиваем фокус для корректной подсказки
                //Ext.getCmp('vacQuikImplEditForm').form.findField('vacImplementDate').blankText ='56789' //"Это поле обязательно для заполнения"
                Ext.getCmp('vacQuikImplEditForm').form.findField('vacImplementDate').focus();
                Ext.getCmp('quikImpl_LpuCombo').focus();
                Ext.getCmp('vacQuikImplEditForm').form.findField('vacImplementDate').focus();//фокус на первый элемент формы

                //Ext.getCmp('gridSimilarRecords').startEditing(0, 0);
            }.createDelegate(this)
        });
    }
});

/*
 * класс описания конфигурации таблицы для выбора вакцины
 */
function gridConfiSimilarRecords() {
    var isLoad = false;

    this.store = new Ext.data.JsonStore({
//    fields: ['selRow', 'PlanTmp_id', 'Date_Plan', 'Name', 'type_name'],//, 'SequenceVac'],
        fields: ['selRow', 'PlanView_id', 'PlanFinal_id', 'Date_Plan', 'Name', 'type_name'], //, 'SequenceVac'],
        url: '/?c=VaccineCtrl&m=loadSimilarRecords',
        //key: '',
        root: 'rows',
        keyList: [],
        keyListPlan: [],
        listeners: {
            'load': function(obj, records, options) {
                var recordsCnt = records.length;
                var isSet = 0;

                sw.Promed.vac.utils.consoleLog('records');
                sw.Promed.vac.utils.consoleLog(records);
                //sw.Promed.vac.utils.consoleLog(records.get('selRow')); 
                sw.Promed.vac.utils.consoleLog(obj.data);
                var selRows = [];
                for (var i = 0; i < recordsCnt; i++) {
                    sw.Promed.vac.utils.consoleLog(i);
                    for (var j = 0; j < obj.keyList.length; j++) {
                        var rec = obj.getAt(i);
                        if (rec.get('PlanFinal_id') == Ext.getCmp('amm_QuikImplVacForm').formParams.vacJournalAccount_id) {
                            rec.set('selRow', 'Да');
                            selRows.push(rec.get('PlanView_id'));
                        }
//                                    if (rec.get('selRow')) {
//					selRows.push(rec.get('PlanView_id'));
//                                    }
                    }
                    //grid.store.keyList = selRows;
                }
                obj.keyList = selRows;
                /*
                 var selRows = [];
                 var gridStore = grid.getStore();
                 var gridStoreCnt = gridStore.getCount();
                 for(var i = 0; i < gridStoreCnt; i++) {
                 var rec = gridStore.getAt(i);
                 if (rec.get('selRow')) {
                 //              selRows.push(rec.get('PlanTmp_id'));
                 selRows.push(rec.get('PlanView_id'));
                 }
                 }
                 grid.store.keyList = selRows;
                 */
            }
        }
    });

    this.columnModel = new Ext.grid.ColumnModel({
        columns: [
            {
                header: 'Выбор',
                width: 55,
                dataIndex: 'selRow'
            },
//    {
//      header: 'PlanTmp_id', 
//      dataIndex: 'PlanTmp_id', 
//      sortable: true
//    },

            {
                header: VAC_TIT_DATE_PLAN,
                dataIndex: 'Date_Plan',
                width: 100,
                sortable: true
            },
            {
                header: VAC_TIT_NAME_TYPE_VAC,
                id: 'autoexpand',
                dataIndex: 'Name',
                sortable: true,
                width: 200
            },
            //VAC_TIT_VAC_NAME

            {
                header: VAC_TIT_VACTYPE_NAME,
                dataIndex: 'type_name',
                sortable: true
            }

            //VAC_TIT_NAME_TYPE_VAC

//    ,{
//      header: VAC_TIT_SEQUENCE_VAC,
//      dataIndex: 'SequenceVac', 
//      sortable: true
//    }
        ]
    });
    this.columnModel.setEditor(
            0,
            new Ext.grid.GridEditor(
            new Ext.form.Checkbox({
        //allowBlank: false
        //,store: store
    })
            )
            );

	/*
    this.columnModel.setRenderer(0,
            function(value) {
                if (value) {
                    return '<div class="x-form-check-wrap x-form-check-checked"><div class="x-form-check-wrap-inner" tabindex="0"><img class="x-form-check" src="extjs/resources/images/default/s.gif"></div></div>';
                } else {
                    //return '<img class="x-form-check" src="extjs/resources/images/default/s.gif">';
                    return '<div class="x-form-check-wrap"><div class="x-form-check-wrap-inner" tabindex="0"><img class="x-form-check" src="extjs/resources/images/default/s.gif"></div></div>';
                }
            }
    );*/
    //Ext.getCmp('gridSimilarRecords').getColumnModel().setRenderer(0, 2)
    this.columnModel.setEditable(0, false); //false - нередактируемое поле, true - редактируемое

    this.load = function() {
        if (isLoad)
            return; // уже загружено
        this.store.load();
        isLoad = true;
    };
}