/**
 * amm_QuikImplVacOtherForm - окно формы Исполнения прочих прививок
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 *
 * @copyright    Copyright (c) 2012 
 * @author       
 * @version      09.07.2014
 * @comment      
 */



sw.Promed.amm_QuikImplVacOtherForm = Ext.extend(sw.Promed.BaseForm, {
    id: 'amm_QuikImplVacOtherForm',
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

    objectName: 'amm_QuikImplVacOtherForm',
    objectSrc: '/jscore/Forms/Vaccine/amm_QuikImplVacOtherForm.js',
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
    Save_Rec: function(vStatus_id) {
        var quikImplForm = Ext.getCmp('amm_QuikImplVacOtherEditForm');
        if (!quikImplForm.form.isValid()) {
            sw.Promed.vac.utils.msgBoxNoValidForm();
            b.setDisabled(false);
            return false;
        }
        if (!Ext.getCmp('vacOtherObj_gridSimilarRecords').store.keyList.length) {
            Ext.Msg.alert('Ошибка', 'Не выбрана прививка! Необходимо выбрать прививку!');
            b.setDisabled(false);
            return false;
        }
        var comboVacList = Ext.getCmp('vacOtherObj_vaccineListCombo');

        var implWin = Ext.getCmp('amm_QuikImplVacOtherForm');
        var param = quikImplForm.form.findField('vacImplementDate').getValue();
        if (param != undefined && param != '') {
            param = param.format('d.m.Y');
        }
        implWin.formParams.date_vac = param;

        var idx = quikImplForm.form.findField('VaccineSeria_id').getStore().findBy(function(rec) { return rec.get('VacPresence_id') == quikImplForm.form.findField('VaccineSeria_id').getValue(); });
        var seriaRecord = quikImplForm.form.findField('VaccineSeria_id').getStore().getAt(idx);
        if (typeof(seriaRecord) == 'object') {
            implWin.formParams.vac_seria = seriaRecord.get('Seria');
            implWin.formParams.vac_period = seriaRecord.get('Period');

        } else {
            implWin.formParams.vac_seria = quikImplForm.form.findField('VaccineSeria_id').getRawValue();
        }
        ;
        implWin.formParams.vaccine_way_place_id = quikImplForm.form.findField('VaccineWayPlace_id').getValue();
        var i = quikImplForm.form.findField('VaccineDoze_id').getStore().findBy(function(rec) { return rec.get('VaccineDose_id') == quikImplForm.form.findField('VaccineDoze_id').getValue(); });
        var recDoze = quikImplForm.form.findField('VaccineDoze_id').getStore().getAt(i);
        if (typeof(recDoze) == 'object') {
            implWin.formParams.vac_doze = recDoze.get('VaccineDose_Name');//quikImplForm.form.findField('VaccineDoze_id').getValue();
        }


        implWin.formParams.Lpu_id = quikImplForm.form.findField('Lpu_id').getValue(),
                //implWin.formParams.med_staff_impl_id = quikImplForm.form.findField('MedStaffFact_id').getValue(),  !!!
                //alert('med_staff_impl_id');
                implWin.formParams.med_staff_impl_id = quikImplForm.form.findField('quikImpl_MedPersonalCombo').getValue()
        //alert('medservice_id');    
        implWin.formParams.medservice_id = Ext.getCmp('quikImpl_ComboMedServiceVac').getValue()
        implWin.formParams.key_list = Ext.getCmp('vacOtherObj_gridSimilarRecords').store.keyList.join(',');
        implWin.formParams.vacJournalAccountOld_id = Ext.getCmp('amm_QuikImplVacOtherForm').formParams.vacJournalAccount_id;
        implWin.formParams.Parent = -1;
        implWin.formParams.vacJournalAccount_id = 0;
        if (implWin.formParams.Lpu_id == '') {
            implWin.formParams.Lpu_id = -1;
        }
        implWin.formParams.vacOther = 1;  //  ПРизнак прочих приввивок
        implWin.formParams.statustype_id = vStatus_id;

        Ext.Ajax.request({
            url: '/?c=VaccineCtrl&m=saveImplWithoutPurp',
            method: 'POST',
            params: implWin.formParams,
            success: function(response, opts) {
                sw.Promed.vac.utils.consoleLog(response);
                var obj = Ext.util.JSON.decode(response.responseText);
                //                                sw.Promed.vac.utils.consoleLog('obj.rows[0].vacJournalAccount_id')
                //                                sw.Promed.vac.utils.consoleLog(obj.rows[0])
                if (sw.Promed.vac.utils.msgBoxErrorBd(response) == 0) {
                    if (sw.Promed.vac.utils.msgBoxErrorBd(response) == 0) {
                        if (Ext.getCmp('amm_Kard063') != undefined) {
                            Ext.getCmp('amm_Kard063').fireEvent('success', 'amm_QuikImplVacOtherForm');
                        }
                    }
                    Ext.getCmp('amm_QuikImplVacOtherForm').hide();
                    //form.hide();
                }
            }
        })
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
                if ((Params.Plan_id == -2) && (Ext.getCmp('amm_QuikImplVacOtherForm').formParams.MedService_id != undefined))
                {
                    objMedService.setValue(Ext.getCmp('amm_QuikImplVacOtherForm').formParams.MedService_id);

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
            formId: 'amm_QuikImplVacOtherEditForm',
            fieldName: 'vacImplementDate'
        });

        /*
         * хранилище для доп сведений
         */
        /*
         this.formStore = new Ext.data.JsonStore({
         fields: ['Plan_id', 'MedPers_id', 'planTmp_id', 'Date_Plan', 'type_name', 'Name',
         'SequenceVac', 'VaccineType_id', 'BirthDay', 'MedService_id', 'StoreKeyList',
         'Vaccine_id', 'VaccinePlace_id', 'Dose', 'Seria'],
         url: '/?c=VaccineCtrl&m=loadPurpFormInfo',
         key: 'Plan_id',
         root: 'data'
         });
         */
        this.gridConfiSimilarRecords = new gridConfiSimilarRecords();

        this.gridSimilarRecords = new Ext.grid.EditorGridPanel({
            id: 'vacOtherObj_gridSimilarRecords',
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
                    record.set('selRow', !record.get('selRow'));


                    var selRows = [];
                    var gridStore = grid.getStore();
                    var gridStoreCnt = gridStore.getCount();
                    for (var i = 0; i < gridStoreCnt; i++) {
                        var rec = gridStore.getAt(i);
                        if (rec.get('selRow')) {
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
                        record.set('selRow', record.get('selRow'));
                    } else {
                        record.set('selRow', !record.get('selRow'));
                    }
//          }

                    var selRows = [];
                    var selRowsplan = [];
                    var gridStore = grid.getStore();
                    var gridStoreCnt = gridStore.getCount();
                    for (var i = 0; i < gridStoreCnt; i++) {
                        var rec = gridStore.getAt(i);
                        if (rec.get('selRow')) {
                            sw.Promed.vac.utils.consoleLog(rec.data);
                            selRows.push(rec.get('VaccineType_id'));

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
                        if ((row.get('PlanFinal_id') == Ext.getCmp('amm_QuikImplVacOtherForm').formParams.vacJournalAccount_id)
                                & (Ext.getCmp('amm_QuikImplVacOtherForm').formParams.plan_id == -2))
                            //cls = 'x-grid-rowbold ';	
                            cls = 'x-grid-rowgreen ';
                        //cls = cls+'x-grid-rowred ';
//				else if (row.get('RecStatus') == 1)
//					cls = 'x-grid-rowbold ';
//				else if (row.get('RecStatus') == 2)
//                                        cls = 'x-grid-panel';    
//                                else if (row.get('RecStatus') == 4)
//					cls =  'x-grid-rowdeleted';
//				else    
//					cls = 'x-grid-rowblue ';

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
                {text: 'Назначить прививку',
                    iconCls: 'save16',
                    tabIndex: TABINDEX_VACIMPNPURPFRM + 20,
                    handler: function(b) {
                        var quikImplForm = Ext.getCmp('amm_QuikImplVacOtherEditForm');
                        if (!quikImplForm.form.isValid()) {
                            sw.Promed.vac.utils.msgBoxNoValidForm();
                            b.setDisabled(false);
                            return false;
                        }
                        if (!Ext.getCmp('vacOtherObj_gridSimilarRecords').store.keyList.length) {
                            Ext.Msg.alert('Ошибка', 'Не выбрана прививка! Необходимо выбрать прививку!');
                            b.setDisabled(false);
                            return false;
                        }
                        b.setDisabled(true);//деактивируем кнопку (исключен повторных нажатий)
                        Ext.getCmp('amm_QuikImplVacOtherForm').Save_Rec(0);
                    }

                },
                {text: 'Сохранить как исполненная',
                    iconCls: 'save16',
                    tabIndex: TABINDEX_VACIMPNPURPFRM + 20,
                    handler: function(b) {


                        b.setDisabled(true);//деактивируем кнопку (исключен повторных нажатий)
                        var quikImplForm = Ext.getCmp('amm_QuikImplVacOtherEditForm');
                        if (!quikImplForm.form.isValid()) {
                            sw.Promed.vac.utils.msgBoxNoValidForm();
                            b.setDisabled(false);
                            return false;
                        }
                        if (!Ext.getCmp('vacOtherObj_gridSimilarRecords').store.keyList.length) {
                            Ext.Msg.alert('Ошибка', 'Не выбрана прививка! Необходимо выбрать прививку!');
                            b.setDisabled(false);
                            return false;
                        }
                        //var comboVacList = Ext.getCmp('vacOtherObj_comboVaccineList');  
                        var comboVacList = Ext.getCmp('vacOtherObj_vaccineListCombo');

                        var implWin = Ext.getCmp('amm_QuikImplVacOtherForm');
                        var param = quikImplForm.form.findField('vacImplementDate').getValue();
                        if (param != undefined && param != '') {
                            param = param.format('d.m.Y');
                        }
                        implWin.formParams.date_vac = param;

                        var idx = quikImplForm.form.findField('VaccineSeria_id').getStore().findBy(function(rec) { return rec.get('VacPresence_id') == quikImplForm.form.findField('VaccineSeria_id').getValue(); });
                        var seriaRecord = quikImplForm.form.findField('VaccineSeria_id').getStore().getAt(idx);
                        //alert('2');
                        if (typeof(seriaRecord) == 'object') {

                            implWin.formParams.vac_seria = seriaRecord.get('Seria');
                            implWin.formParams.vac_period = seriaRecord.get('Period');

                        } else {
                            implWin.formParams.vac_seria = quikImplForm.form.findField('VaccineSeria_id').getRawValue();
                        }
                        ;
                        implWin.formParams.vaccine_way_place_id = quikImplForm.form.findField('VaccineWayPlace_id').getValue();
                        var i = quikImplForm.form.findField('VaccineDoze_id').getStore().findBy(function(rec) { return rec.get('VaccineDose_id') == quikImplForm.form.findField('VaccineDoze_id').getValue(); });
                        var recDoze = quikImplForm.form.findField('VaccineDoze_id').getStore().getAt(i);
                        if (typeof(recDoze) == 'object') {
                            implWin.formParams.vac_doze = recDoze.get('VaccineDose_Name');//quikImplForm.form.findField('VaccineDoze_id').getValue();
                        }


                        implWin.formParams.Lpu_id = quikImplForm.form.findField('Lpu_id').getValue(),
                                implWin.formParams.med_staff_impl_id = quikImplForm.form.findField('quikImpl_MedPersonalCombo').getValue()
                        implWin.formParams.medservice_id = Ext.getCmp('quikImpl_ComboMedServiceVac').getValue()
                        implWin.formParams.key_list = Ext.getCmp('vacOtherObj_gridSimilarRecords').store.keyList.join(',');
                        implWin.formParams.vacJournalAccountOld_id = Ext.getCmp('amm_QuikImplVacOtherForm').formParams.vacJournalAccount_id;
                        implWin.formParams.Parent = -1;
                        implWin.formParams.vacJournalAccount_id = 0;
                        if (implWin.formParams.Lpu_id == '') {
                            implWin.formParams.Lpu_id = -1;
                        }
                        implWin.formParams.vacOther = 1;  //  ПРизнак прочих приввивок
                        Ext.Ajax.request({
                            url: '/?c=VaccineCtrl&m=saveImplWithoutPurp',
                            method: 'POST',
                            params: implWin.formParams,
                            success: function(response, opts) {
                                sw.Promed.vac.utils.consoleLog(response);
                                var obj = Ext.util.JSON.decode(response.responseText);
                                if (sw.Promed.vac.utils.msgBoxErrorBd(response) == 0) {
                                    if (sw.Promed.vac.utils.msgBoxErrorBd(response) == 0) {
                                        if (Ext.getCmp('amm_Kard063') != undefined) {
                                            Ext.getCmp('amm_Kard063').fireEvent('success', 'amm_QuikImplVacOtherForm');
                                        }
                                    }
                                    form.hide();
                                }
                            }.createDelegate(this),
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
                        Ext.getCmp('amm_QuikImplVacOtherEditForm').form.findField('vacImplementDate').focus();
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
                    id: 'amm_QuikImplVacOtherEditForm',
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
                                    items: [
                                        {
                                            fieldLabel: 'Прививка',
                                            id: 'vacOtherObj_comboVaccineOther',
                                            autoLoad: true,
                                            tabIndex: TABINDEX_VACIMPFRM + 10,
                                            width: 260,
                                            name: 'vacOtherObj_comboVaccineOther',
                                            allowBlank: false,
                                            xtype: 'amm_GetListVaccineTypeOther',
                                            listeners: {
                                                'select': function(combo, record, index) {
                                                    //alert('.reset');
                                                    amm_QuikImplVacOtherForm.formParams.vac_type_id = record.data.VaccineType_id;
                                                    //Ext.getCmp('amm_QuikImplVacOtherForm').VacInitCombo( 'vacOtherObj_comboVaccineOther');

                                                    var Params = new Object();
                                                    Params = amm_QuikImplVacOtherForm.formParams;

                                                    var comboVacList = Ext.getCmp('vacOtherObj_vaccineListCombo');
                                                    comboVacList.reset();
                                                    comboVacList.getStore().load({
                                                        params: Params,
                                                        callback: function() {
                                                            //alert(comboVacList.getStore().getCount());
                                                            if (comboVacList.getStore().getCount() > 0) {
                                                                comboVacList.setValue(comboVacList.getStore().getAt(0).get('Vaccine_id'));
                                                                amm_QuikImplVacOtherForm.formParams.Vaccine_id = comboVacList.getStore().getAt(0).get('Vaccine_id');
                                                                //                            comboVacList.fireEvent('select', comboVacList);
                                                            }
                                                            comboVacList.fireEvent('select', comboVacList);
                                                        }
                                                    });
                                                }

                                            }

                                        },
                                        {
                                            height: 20,
                                            border: false,
                                            cls: 'tg-label'
                                        },
                                        {
                                            allowBlank: false,
                                            autoLoad: true,
                                            fieldLabel: 'Вакцина',
                                            hiddenName: 'Vaccine_id',
                                            width: 260,
                                            xtype: 'amm_VaccineOherListCombo',
                                            id: 'vacOtherObj_vaccineListCombo',
                                            tabIndex: TABINDEX_VACIMPFRM + 12,
                                            listeners: {
                                                'select': function(combo, record, index) {
                                                    var comboVacSeria = Ext.getCmp('vacOtherObj_comboVaccineSeria');
                                                    comboVacSeria.reset();
                                                    var Params = new Object();
                                                    if (combo.getValue()) {  //  Если есть хоть один элемент
                                                        amm_QuikImplVacOtherForm.formParams.vaccine_id = combo.getValue();
                                                    }
                                                    else {
                                                        amm_QuikImplVacOtherForm.formParams.vaccine_id = 0;
                                                    }
                                                    Params = amm_QuikImplVacOtherForm.formParams;

                                                    Ext.getCmp('vacOtherObj_gridSimilarRecords').store.load({
                                                        params: Params
                                                    });


                                                    comboVacSeria.store.load({
                                                        params: Params,
                                                        callback: function() {
                                                            if (comboVacSeria.getStore().getCount() > 0)
                                                                //if (!!combo.generalParams.row_plan_parent)
                                                                comboVacSeria.setValue(comboVacSeria.getStore().getAt(0).get('VacPresence_id'));
                                                            comboVacSeria.fireEvent('select', comboVacSeria);
                                                        }
                                                    });
                                                }.createDelegate(this)
                                            }
                                        },
                                        {
                                            autoLoad: true,
                                            allowTextInput: true,
                                            fieldLabel: 'Серия и срок годности',
                                            hiddenName: 'VaccineSeria_id',
                                            width: 260,
                                            xtype: 'amm_VaccineSeriaCombo',
                                            //sw.Promed.vac.objects.comboVaccineSeria({
                                            id: 'vacOtherObj_comboVaccineSeria',
                                            tabIndex: TABINDEX_VACIMPFRM + 13,
                                            allowTextInput: true,
                                                    listeners: {
                                                'select': function(combo, record, index) {
                                                    var Params = new Object();
                                                    amm_QuikImplVacOtherForm.formParams.VacPresence_id = combo.value;
                                                    Params = amm_QuikImplVacOtherForm.formParams;
                                                    var comboVacWay = Ext.getCmp('vacOtherObj_comboVaccineWay');
                                                    comboVacWay.reset();
                                                    //sw.Promed.vac.utils.consoleLog(Params); //gridConfiSimilarRecords

                                                    comboVacWay.store.load({
                                                        params: Params,
                                                        callback: function() {
                                                            if (comboVacWay.getStore().getCount() > 0)
                                                                comboVacWay.setValue(comboVacWay.getStore().getAt(0).get('VaccineWayPlace_id'));
                                                        }
                                                    });

                                                    var comboVacDoze = Ext.getCmp('vacOtherObj_comboVaccineDoze');
                                                    comboVacDoze.reset();
                                                    comboVacDoze.store.load({
                                                        params: Params,
                                                        callback: function() {
                                                            if (comboVacDoze.getStore().getCount() > 0)
                                                                //if (!!combo.generalParams.row_plan_parent)
                                                                comboVacDoze.setValue(comboVacDoze.getStore().getAt(0).get('VaccineDose_id'));
                                                        }
                                                    });

                                                }
                                            }
                                        },
                                        sw.Promed.vac.objects.comboVaccineWay({
                                            id: 'vacOtherObj_comboVaccineWay',
                                            tabIndex: TABINDEX_VACIMPFRM + 14
                                        }),
                                        sw.Promed.vac.objects.comboVaccineDoze({
                                            id: 'vacOtherObj_comboVaccineDoze',
                                            tabIndex: TABINDEX_VACIMPFRM + 15
                                        })
                                    ]

                                }, {
                                    layout: 'form',
                                    items: [
                                        {
                                            autoHeight: true,
                                            style: 'margin: 5px; padding: 5px;',
                                            title: 'Исполнение прививки',
                                            xtype: 'fieldset',
                                            items: [
                                                sw.Promed.vac.objects.fieldVacImplementDate({
                                                    tabIndex: TABINDEX_VACIMPFRM + 10,
                                                    id: 'vacOtherObj_ImplementDate',
                                                    onChange: function(field, newValue, oldValue) {
                                                        if (Ext.getCmp('vacOtherObj_vaccineListCombo') != undefined) 
                                                            if ((Ext.getCmp('vacOtherObj_comboVaccineOther').getValue() != '') 
                                                                && (Ext.getCmp('vacOtherObj_comboVaccineOther').getValue() != undefined))  {
                                                                //alert(Ext.getCmp('vacOtherObj_comboVaccineOther').getValue());

                                                            //Обновляем список вакцин в зависимости от возраста пациента
                                                            
                                                            //var combo = Ext.getCmp('vacOtherObj_vaccineListCombo');
                                                            var comboVacList = Ext.getCmp('vacOtherObj_vaccineListCombo');
                                                            var val = comboVacList.value;
                                                            if (val != undefined) {
//                                                                var tmp_txt = combo.v
                                                                /*
                                                                 alert (combo.lastSelectionText);
                                                                 combo.generalParams = record;
                                                                 alert (oldValue);
                                                                 */
                                                                Ext.getCmp('amm_QuikImplVacOtherForm').formParams.date_purpose = newValue.format('d.m.Y');
                                                                comboVacList.reset();
                                                                
                                                                var Params = new Object();
                                                                Params = amm_QuikImplVacOtherForm.formParams;

                                                                
                                                                comboVacList.reset();
                                                                comboVacList.getStore().load({
                                                                    params: Params,
                                                                    callback: function() {
                                                                        //alert(comboVacList.getStore().getCount());
                                                                        if (comboVacList.getStore().getCount() > 0) {
                                                                            comboVacList.setValue(val);
                                                                            if (comboVacList.lastSelectionText == val) {
                                                                                comboVacList.setValue(undefined)
                                                                            }
                                                                        }
                                                                        comboVacList.fireEvent('select', comboVacList);
                                                                    }
                                                                });
                                                                /*
                                                                sw.Promed.vac.utils.consoleLog(combo.getStore().getCount());
                                                                 var Params = new Object();
                                                                    Params = amm_QuikImplVacOtherForm.formParams;
                                                                    combo.store.load({
                                                                    params: Params, //Ext.getCmp('amm_QuikImplVacOtherForm').formParams,
                                                                    callback: function() {
//                                                                         //var $combo = Ext.getCmp('vacOtherObj_vaccineListCombo');
//                                                                        sw.Promed.vac.utils.consoleLog('1');
//                                                                        sw.Promed.vac.utils.consoleLog(combo.getStore().getCount());
//                                                                        if (combo.getStore().getCount() > 0) {
//                                                                            combo.setValue(val);                                                                                                                     sw.Promed.vac.utils.consoleLog(arr);
//                                                                            if (combo.lastSelectionText == val) {
//                                                                                combo.setValue(undefined)
//                                                                            }
//                                                                            //                                                                                                                 }
//                                                                        }
                                                                   }
                                                                }
                                                                )
                                                                    */
                                                            }
                                                        }
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
                                                            Ext.getCmp('amm_QuikImplVacOtherForm').initCombo('quikImpl_LpuCombo', Params);
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
                                                            Ext.getCmp('amm_QuikImplVacOtherForm').initCombo('quikImpl_LpuBuildingCombo', Params);
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
                                                            Ext.getCmp('amm_QuikImplVacOtherForm').initCombo('quikImpl_ComboMedServiceVac', Params);
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
                                                },
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
        sw.Promed.amm_QuikImplVacOtherForm.superclass.initComponent.apply(this, arguments);
    },
    openVacPurposeEditWindow: function(action) {
        var current_window = this;
        var params = new Object();
        var vacPurpose_grid = current_window.findById('LTVW_PersonPrivilegeGrid');

    },
    show: function(record) {
//		var record0 = record;
        sw.Promed.amm_QuikImplVacOtherForm.superclass.show.apply(this, arguments);

        var vacQuikImplForm = Ext.getCmp('amm_QuikImplVacOtherEditForm');
        ;
        vacQuikImplForm.getForm().reset();
        Ext.getCmp('amm_QuikImplVacOtherForm').gridConfiSimilarRecords.store.removeAll();
        /*
         vacQuikImplForm.form.findField('VaccineWayPlace_id').allowBlank = !record.row_plan_parent;
         vacQuikImplForm.form.findField('VaccineDoze_id').allowBlank = !record.row_plan_parent;
         vacQuikImplForm.form.findField('quikImpl_MedPersonalCombo').allowBlank = !record.row_plan_parent;
         */
        this.formParams = record;
        this.PersonInfoPanel.load({
            callback: function() {
                this.PersonInfoPanel.setPersonTitle();
                this.formParams.birthday = this.PersonInfoPanel.getFieldValue('Person_Birthday').format('d.m.Y');
                    this.PersonInfoPanel.setPersonTitle();
                    var Person_deadDT = Ext.getCmp('amm_QuikImplVacOtherForm').PersonInfoPanel.getFieldValue('Person_deadDT');                           
//alert(Person_deadDT);
                            if (Person_deadDT != undefined) {
                                Ext.getCmp('vacOtherObj_ImplementDate').setMaxValue
                                    (Ext.getCmp('amm_QuikImplVacOtherForm').PersonInfoPanel.getFieldValue('Person_deadDT'));
                                }
            }.createDelegate(this),
            loadFromDB: true,
            Person_id: record.person_id
                    , Server_id: record.Server_id
        });

        //Ext.getCmp('vacOtherObj_comboVaccineOther').reset();
        Ext.getCmp('vacOtherObj_comboVaccineOther').getStore().load();
        Ext.getCmp('vacOtherObj_ImplementDate').setValue(new Date);
        this.formParams.date_purpose = Ext.getCmp('vacOtherObj_ImplementDate').value;
        var Params = new Object();
        Params.Lpu_id = getGlobalOptions().lpu_id;
        //Params.parent = 'quikImpl_LpuCombo'
        Ext.getCmp('amm_QuikImplVacOtherForm').initCombo('quikImpl_LpuCombo', Params);
        sw.Promed.vac.utils.consoleLog('Params');
        sw.Promed.vac.utils.consoleLog(Params);
        Ext.getCmp('quikImpl_LpuCombo').setValue(Params.Lpu_id);
        Ext.getCmp('vacOtherObj_comboVaccineSeria').reset();
        Ext.getCmp('vacOtherObj_vaccineListCombo').reset();

    }
});

///*
// * класс описания конфигурации таблицы для выбора вакцины
// */
function gridConfiSimilarRecords() {
    var isLoad = false;

    this.store = new Ext.data.JsonStore({
        fields: ['selRow', 'VaccineType_id', 'VaccineType_Name', 'Date_Plan', 'Type_Name'], //, 'SequenceVac'],
        url: '/?c=VaccineCtrl&m=loadVaccine4Other',
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
                    var rec = obj.getAt(i);
                    rec.set('selRow', true);
                    selRows.push(rec.get('VaccineType_id'));
                    // grid.store.keyList = selRows;
                }
                obj.keyList = selRows;
            }
        }
    });

    this.columnModel = new Ext.grid.ColumnModel({
        columns: [
            {header: 'Выбор', width: 55, dataIndex: 'selRow'},
            {header: VAC_TIT_DATE_PLAN, dataIndex: 'Date_Plan', width: 100, sortable: true},
            {header: VAC_TIT_NAME_TYPE_VAC, id: 'autoexpand', dataIndex: 'VaccineType_Name', sortable: true, width: 200},
            {header: VAC_TIT_VACTYPE_NAME, dataIndex: 'Type_Name', sortable: true}
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
    this.columnModel.setRenderer(0,
            function(value) {
                if (value) {
                    return '<div class="x-form-check-wrap x-form-check-checked"><div class="x-form-check-wrap-inner" tabindex="0"><img class="x-form-check" src="extjs/resources/images/default/s.gif"></div></div>';
                } else {
                    //return '<img class="x-form-check" src="extjs/resources/images/default/s.gif">';
                    return '<div class="x-form-check-wrap"><div class="x-form-check-wrap-inner" tabindex="0"><img class="x-form-check" src="extjs/resources/images/default/s.gif"></div></div>';
                }
            }
    );
    this.columnModel.setEditable(0, false); //false - нередактируемое поле, true - редактируемое

    this.load = function() {
        if (isLoad)
            return; // уже загружено
        this.store.load();
        isLoad = true;
    };
}